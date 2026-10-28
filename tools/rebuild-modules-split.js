#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const modulesCssPath = path.join(root, 'assets/css/modules.css');
const modulesCssSourcePath = path.join(root, 'assets/css/modules.source.css');
const modulesHeroCssPath = path.join(root, 'assets/css/modules-hero.css');
const modulesHeroCssSourcePath = path.join(root, 'assets/css/modules-hero.source.css');
const splitDir = path.join(root, 'assets/css/modules-split');
const standaloneDir = path.join(root, 'assets/css/modules-standalone');
const modulesDir = path.join(root, 'inc/modules/modules');

function themeRelativePath(file) {
  return path.relative(root, file).split(path.sep).join('/');
}

function read(file) {
  return fs.readFileSync(file, 'utf8');
}

function write(file, content) {
  fs.writeFileSync(file, content, 'utf8');
}

function ensureFinalNewline(content) {
  return content === '' || content.endsWith('\n') ? content : `${content}\n`;
}

function shouldPreserveStandaloneSplit(content) {
  const text = String(content || '');
  return /@qiling-keep-standalone-split|拆分占位文件|手工维护的拆分样式/i.test(text);
}

function sanitizeType(input) {
  return String(input || '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

function extractModuleType(php) {
  const m = php.match(/function\s+get_id\s*\(\s*\)\s*\{[\s\S]*?return\s+['"]([^'"]+)['"]/i);
  if (!m) return '';
  return sanitizeType(m[1]);
}

function extractClassTokens(php) {
  const tokens = new Set();
  const generic = new Set([
    'module', 'container', 'section-padding', 'section-title', 'section-header',
    'section-subtitle', 'text-center', 'text-left', 'text-right',
    'btn', 'btn-outline', 'active', 'open', 'show',
    'swiper', 'swiper-wrapper', 'swiper-slide', 'swiper-pagination',
    'custom-scrollbar', 'article-content', 'dashicons',
    'title', 'subtitle', 'content', 'desc', 'description',
    'header', 'footer', 'left', 'right', 'center', 'inner', 'outer',
    'main', 'item', 'items', 'card', 'cards', 'list', 'grid',
    'icon', 'image', 'img', 'text', 'name', 'value', 'label',
    'meta', 'row', 'col', 'body', 'wrapper', 'box', 'module-overlay',
  ]);

  const re = /class\s*=\s*(["'])([\s\S]*?)\1/gi;
  let m;
  while ((m = re.exec(php)) !== null) {
    let raw = m[2] || '';
    raw = raw.replace(/<\?php[\s\S]*?\?>/gi, ' ');

    raw.split(/\s+/).forEach((part) => {
      const p = part.trim().toLowerCase();
      if (!/^[a-z][a-z0-9_-]{2,}$/.test(p)) return;
      if (generic.has(p)) return;
      tokens.add(p);
    });
  }

  return tokens;
}

function collectModules() {
  const files = fs.readdirSync(modulesDir)
    .filter((f) => /^class-.*-module\.php$/i.test(f))
    .sort();

  const modules = [];
  for (const file of files) {
    const full = path.join(modulesDir, file);
    const php = read(full);
    const type = extractModuleType(php);
    if (!type) continue;

    const typeHyphen = type.replace(/_/g, '-');
    const typeUnderscore = type.replace(/-/g, '_');

    const tokens = extractClassTokens(php);
    tokens.add(type);
    tokens.add(typeHyphen);
    tokens.add(typeUnderscore);
    tokens.add(`module-${typeHyphen}`);

    modules.push({
      type,
      file,
      rootSelectors: [
        `module-${typeHyphen}`,
        `module-${typeUnderscore}`,
      ],
      markerTokens: [
        `class-${typeHyphen}-module.php`,
        `class-${typeUnderscore}-module.php`,
      ],
      tokens: Array.from(tokens).sort(),
    });
  }

  return modules;
}

function skipString(text, i, quote) {
  let k = i + 1;
  while (k < text.length) {
    const ch = text[k];
    if (ch === '\\') {
      k += 2;
      continue;
    }
    if (ch === quote) return k + 1;
    k++;
  }
  return text.length;
}

function skipComment(text, i) {
  const end = text.indexOf('*/', i + 2);
  return end === -1 ? text.length : end + 2;
}

function parseTopLevelBlocks(css) {
  const blocks = [];
  const n = css.length;
  let i = 0;

  while (i < n) {
    const preStart = i;

    while (i < n) {
      const ch = css[i];
      if (/\s/.test(ch)) {
        i++;
        continue;
      }
      if (ch === '/' && css[i + 1] === '*') {
        i = skipComment(css, i);
        continue;
      }
      break;
    }

    if (i >= n) {
      const tail = css.slice(preStart);
      if (tail !== '') {
        blocks.push({ text: tail, start: preStart, end: n });
      }
      break;
    }

    const start = preStart;
    let j = i;
    while (j < n) {
      const ch = css[j];
      if (ch === '"' || ch === "'") {
        j = skipString(css, j, ch);
        continue;
      }
      if (ch === '/' && css[j + 1] === '*') {
        j = skipComment(css, j);
        continue;
      }
      if (ch === '{' || ch === ';') break;
      j++;
    }

    if (j >= n) {
      blocks.push({ text: css.slice(start), start, end: n });
      break;
    }

    if (css[j] === ';') {
      const end = j + 1;
      blocks.push({ text: css.slice(start, end), start, end });
      i = end;
      continue;
    }

    let depth = 1;
    let k = j + 1;
    while (k < n && depth > 0) {
      const ch = css[k];
      if (ch === '"' || ch === "'") {
        k = skipString(css, k, ch);
        continue;
      }
      if (ch === '/' && css[k + 1] === '*') {
        k = skipComment(css, k);
        continue;
      }
      if (ch === '{') depth++;
      if (ch === '}') depth--;
      k++;
    }

    blocks.push({ text: css.slice(start, k), start, end: k });
    i = k;
  }

  return blocks;
}

function buildTokenOwners(modules) {
  const owners = new Map();
  for (const m of modules) {
    for (const token of m.tokens) {
      if (!token || token.length < 3) continue;
      if (!owners.has(token)) owners.set(token, new Set());
      owners.get(token).add(m.type);
    }
  }
  return owners;
}

function hasClassOrIdToken(hay, token) {
  if (!token || token.length < 3) return false;
  return hay.includes(`.${token}`) || hay.includes(`#${token}`);
}

function escapeRegex(text) {
  return String(text).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function splitSelectorList(text) {
  const selectors = [];
  let start = 0;
  let roundDepth = 0;
  let squareDepth = 0;
  let quote = '';
  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    if (quote) {
      if (char === '\\') i++;
      else if (char === quote) quote = '';
      continue;
    }
    if (char === '"' || char === "'") quote = char;
    else if (char === '(') roundDepth++;
    else if (char === ')') roundDepth--;
    else if (char === '[') squareDepth++;
    else if (char === ']') squareDepth--;
    else if (char === ',' && roundDepth === 0 && squareDepth === 0) {
      selectors.push(text.slice(start, i).trim());
      start = i + 1;
    }
  }
  selectors.push(text.slice(start).trim());
  return selectors.filter(Boolean);
}

function selectorOwner(selector, modules, tokenOwners, moduleTypeSet) {
  const picked = pickOwner(String(selector).toLowerCase(), modules, tokenOwners, '', moduleTypeSet);
  return moduleTypeSet.has(picked.owner) ? picked.owner : '_shared';
}

function filterBlockForOwner(blockText, targetOwner, modules, tokenOwners, moduleTypeSet) {
  const openIndex = blockText.indexOf('{');
  const closeIndex = blockText.lastIndexOf('}');
  if (openIndex < 0 || closeIndex <= openIndex) {
    return targetOwner === '_shared' ? blockText : '';
  }

  const prelude = blockText.slice(0, openIndex);
  const body = blockText.slice(openIndex + 1, closeIndex);
  const trimmedPrelude = prelude.replace(/\/\*[\s\S]*?\*\//g, '').trim().toLowerCase();
  if (/^@(media|supports|container|layer|document)\b/.test(trimmedPrelude)) {
    const nested = parseTopLevelBlocks(body)
      .map((nestedBlock) => filterBlockForOwner(nestedBlock.text, targetOwner, modules, tokenOwners, moduleTypeSet))
      .filter((item) => item.trim() !== '')
      .join('');
    return nested.trim() === '' ? '' : `${prelude}{${nested}}`;
  }
  if (trimmedPrelude.startsWith('@')) {
    return targetOwner === '_shared' ? blockText : '';
  }

  const selectors = splitSelectorList(prelude);
  const retained = selectors.filter((selector) => selectorOwner(selector, modules, tokenOwners, moduleTypeSet) === targetOwner);
  return retained.length > 0 ? `${retained.join(',\n')} {${body}}` : '';
}

function scopeCssBlock(blockText, scope) {
  const openIndex = blockText.indexOf('{');
  const closeIndex = blockText.lastIndexOf('}');
  if (openIndex < 0 || closeIndex <= openIndex) return blockText;

  const prelude = blockText.slice(0, openIndex).trim();
  const body = blockText.slice(openIndex + 1, closeIndex);
  if (/^@(media|supports|container|layer|document)\b/i.test(prelude)) {
    const nested = parseTopLevelBlocks(body)
      .map((nestedBlock) => scopeCssBlock(nestedBlock.text, scope))
      .join('');
    return `${prelude}{${nested}}`;
  }
  if (/^@(keyframes|-webkit-keyframes|font-face|property|page)\b/i.test(prelude)) {
    return blockText;
  }

  const selectors = splitSelectorList(prelude).map((selector) => {
    const trimmed = selector.trim();
    if (!trimmed || trimmed.startsWith('@')) return trimmed;
    if (trimmed.includes(scope)) return trimmed;

    const documentRootMatch = trimmed.match(/^((?:html|body)(?:[.#:[\]a-z0-9_-]+)?)\s+(.+)$/i);
    if (documentRootMatch) {
      return `${documentRootMatch[1]} ${scope} ${documentRootMatch[2]}`;
    }
    if (/^:root\b/i.test(trimmed)) {
      return trimmed.replace(/^:root\b/i, scope);
    }
    return `${scope} ${trimmed}`;
  }).filter(Boolean);
  return `${selectors.join(',\n')} {${body}}`;
}

function scopeCss(css, type) {
  const scope = `.qiling-module-scope-${String(type || '')}`;
  const normalizedCss = String(css || '').replace(/\.qiling-module-scope-[a-z0-9_-]+\s+/gi, '');
  return parseTopLevelBlocks(normalizedCss)
    .map((block) => scopeCssBlock(block.text, scope))
    .join('');
}

function pickOwner(blockLower, modules, tokenOwners, currentOwner, moduleTypeSet) {
  // 0) section marker owner: /* ===== module_id ===== */
  // 一旦命中模块注释锚点，直接重置 section owner，避免后续无根选择器串位。
  const markerMatch = blockLower.match(/\/\*\s*=+\s*([a-z0-9_-]+)\s*=+\s*\*\//i);
  if (markerMatch && markerMatch[1]) {
    const markerType = String(markerMatch[1]).trim().toLowerCase();
    if (moduleTypeSet.has(markerType)) {
      return { owner: markerType, sectionOwner: markerType, reason: 'section-marker' };
    }
  }

  // Banner 在 modules-hero.css 中只有局部首屏增强块，没有统一的模块注释锚点，
  // 这里显式归属到 banner，确保 banner.css 能稳定由脚本产出。
  if (
    blockLower.includes('.banner-wave')
    || blockLower.includes("banner image-left layout styles")
    || blockLower.includes(".banner-flex[style*='row-reverse'] .banner-text")
  ) {
    return { owner: 'banner', sectionOwner: 'banner', reason: 'hero-banner-special' };
  }

  // 0) generic shared utility selectors (must stay global, not tied to any one module file)
  const hasGenericDecorRoot = /^\s*\.module-decor\b/m.test(blockLower);
  // 只要出现通用根选择器 .module-decor，就必须归入共享样式；
  // 否则会被“当前 section owner”误吸附到某个模块（如 services），导致 CTA/Stats 缺失左右装饰定位。
  if (hasGenericDecorRoot) {
    return { owner: '_shared', sectionOwner: currentOwner, reason: 'shared-generic-decor' };
  }

  const hasGenericAdminNoticeRoot = blockLower.includes('.qiling-module-admin-notice');
  // 主题级后台告警样式会被多个模块复用，不能跟随“当前 section owner”被错误拆到某个模块文件。
  if (hasGenericAdminNoticeRoot) {
    return { owner: '_shared', sectionOwner: currentOwner, reason: 'shared-generic-admin-notice' };
  }

  // 1) exact marker comment owner
  const markerHits = [];
  for (const m of modules) {
    if (m.markerTokens.some((t) => blockLower.includes(t))) markerHits.push(m.type);
  }
  if (markerHits.length === 1) {
    return { owner: markerHits[0], sectionOwner: markerHits[0], reason: 'marker' };
  }
  if (markerHits.length > 1) {
    return { owner: '_shared', sectionOwner: currentOwner, reason: 'marker-ambiguous' };
  }

  // 2) exact root selector owner
  const rootHits = [];
  for (const m of modules) {
    if (m.rootSelectors.some((sel) => new RegExp(`\\.${escapeRegex(sel)}(?:[^a-z0-9_-]|$)`, 'i').test(blockLower))) {
      rootHits.push(m.type);
    }
  }
  if (rootHits.length === 1) {
    return { owner: rootHits[0], sectionOwner: rootHits[0], reason: 'root' };
  }
  if (rootHits.length > 1) {
    return { owner: '_shared', sectionOwner: currentOwner, reason: 'root-ambiguous' };
  }

  // 3) unique token owner
  const uniqueHits = new Set();
  for (const [token, ownerSet] of tokenOwners.entries()) {
    if (!hasClassOrIdToken(blockLower, token)) continue;
    if (ownerSet.size === 1) {
      uniqueHits.add(Array.from(ownerSet)[0]);
    }
  }

  // 只要存在唯一命中的模块，就优先归属该模块。
  // 避免 .grid-cols-* 这类跨模块通用 token 抢占导致误拆分。
  if (uniqueHits.size === 1) {
    const only = Array.from(uniqueHits)[0];
    return { owner: only, sectionOwner: currentOwner || only, reason: 'token' };
  }
  if (uniqueHits.size > 1) {
    return { owner: '_shared', sectionOwner: currentOwner, reason: 'token-multi' };
  }

  // 4) section fallback: do not lose contextual trailing styles
  if (currentOwner) {
    return { owner: currentOwner, sectionOwner: currentOwner, reason: 'section-fallback' };
  }

  // 5) shared fallback
  return { owner: '_shared', sectionOwner: currentOwner, reason: 'shared-fallback' };
}

function main() {
  if (!fs.existsSync(modulesCssSourcePath)) {
    if (!fs.existsSync(modulesCssPath)) throw new Error('modules.css not found');
    write(modulesCssSourcePath, read(modulesCssPath));
  }
  if (!fs.existsSync(modulesHeroCssSourcePath) && fs.existsSync(modulesHeroCssPath)) {
    write(modulesHeroCssSourcePath, read(modulesHeroCssPath));
  }
  if (!fs.existsSync(splitDir)) {
    fs.mkdirSync(splitDir, { recursive: true });
  }

  const modules = collectModules();
  const css = read(modulesCssSourcePath);
  const sourceBytes = Buffer.byteLength(css, 'utf8');
  const blocks = parseTopLevelBlocks(css);
  const tokenOwners = buildTokenOwners(modules);

  const typeSet = new Set(modules.map((m) => m.type));
  const assignedByType = new Map();
  for (const t of typeSet) assignedByType.set(t, []);
  assignedByType.set('_shared', []);

  let sectionOwner = '';
  const assignmentRows = [];
  let assignedMainBytes = 0;
  let removedStaleFiles = 0;

  for (let i = 0; i < blocks.length; i++) {
    const block = blocks[i];
    const lower = block.text.toLowerCase();
    const picked = pickOwner(lower, modules, tokenOwners, sectionOwner, typeSet);

    sectionOwner = picked.sectionOwner || sectionOwner;
    const owner = typeSet.has(picked.owner) ? picked.owner : '_shared';

    assignedByType.get(owner).push(block.text);
    assignedMainBytes += Buffer.byteLength(block.text, 'utf8');

    assignmentRows.push([
      String(i),
      String(block.start),
      String(block.end),
      String(block.text.length),
      owner,
      picked.reason,
      'modules.source.css',
    ].join('\t'));
  }

  let heroCss = '';
  let heroSourceBytes = 0;
  let heroBlocks = [];
  let assignedHeroBytes = 0;

  if (fs.existsSync(modulesHeroCssSourcePath)) {
    heroCss = read(modulesHeroCssSourcePath);
    heroSourceBytes = Buffer.byteLength(heroCss, 'utf8');
    heroBlocks = parseTopLevelBlocks(heroCss);

    let heroSectionOwner = '';
    for (let i = 0; i < heroBlocks.length; i++) {
      const block = heroBlocks[i];
      const lower = block.text.toLowerCase();
      const picked = pickOwner(lower, modules, tokenOwners, heroSectionOwner, typeSet);

      heroSectionOwner = picked.sectionOwner || heroSectionOwner;
      const owner = typeSet.has(picked.owner) ? picked.owner : '_shared';

      assignedByType.get(owner).push(block.text);
      assignedHeroBytes += Buffer.byteLength(block.text, 'utf8');

      assignmentRows.push([
        `hero-${i}`,
        String(block.start),
        String(block.end),
        String(block.text.length),
        owner,
        picked.reason,
        'modules-hero.source.css',
      ].join('\t'));
    }
  }

  const manifestLines = [];
  manifestLines.push('# strict split manifest');
  manifestLines.push(`source=${themeRelativePath(modulesCssSourcePath)}`);
  manifestLines.push(`out_dir=${themeRelativePath(splitDir)}`);
  manifestLines.push(`source_bytes=${sourceBytes}`);
  if (heroCss) {
    manifestLines.push(`hero_source=${themeRelativePath(modulesHeroCssSourcePath)}`);
    manifestLines.push(`hero_source_bytes=${heroSourceBytes}`);
  }
  manifestLines.push('');

  const sharedBlocks = assignedByType.get('_shared') || [];
  const sharedSource = sharedBlocks
    .map((block) => filterBlockForOwner(block, '_shared', modules, tokenOwners, typeSet))
    .filter((block) => block.trim() !== '')
    .join('');
  const generatedModulePaths = [];

  for (const m of modules) {
    const outPath = path.join(splitDir, `${m.type}.css`);
    const ownBlocks = (assignedByType.get(m.type) || []).slice();
    const standalonePath = path.join(standaloneDir, `${m.type}.css`);
    const standaloneSource = fs.existsSync(standalonePath) ? read(standalonePath) : '';

    const allBlocks = [];
    const seen = new Set();
    for (const b of ownBlocks) {
      if (seen.has(b)) continue;
      seen.add(b);
      allBlocks.push(b);
    }

    // 公共基础规则复制进每个模块并使用该模块自己的 scope，不作为跨模块运行时依赖。
    const bundleContractMarker = m === modules[0] ? '/* @qiling-design-surface-bridge */\n' : '';
    const body = ensureFinalNewline(bundleContractMarker + scopeCss(sharedSource + allBlocks.join('') + standaloneSource, m.type));
    if (body.trim() === '') {
      // 仅保留带显式标记的独立/占位 split 文件，其余空归属结果都视为陈旧生成物并清理。
      if (fs.existsSync(outPath)) {
        const existingContent = read(outPath);
        if (existingContent.trim() !== '' && shouldPreserveStandaloneSplit(existingContent)) {
          const scopedStandaloneContent = ensureFinalNewline(scopeCss(existingContent, m.type));
          write(outPath, scopedStandaloneContent);
          manifestLines.push(`${m.type}\t${themeRelativePath(outPath)}\tbytes=${Buffer.byteLength(scopedStandaloneContent, 'utf8')}\tchunks=0\tmodule_file=${m.file}\tpreserved=standalone`);
          continue;
        }
        if (existingContent.trim() !== '') {
          removedStaleFiles += 1;
        }
        fs.unlinkSync(outPath);
      }
      manifestLines.push(`${m.type}\t(skipped-empty)\tbytes=0\tchunks=0\tmodule_file=${m.file}`);
      continue;
    }
    write(outPath, body);
    generatedModulePaths.push(outPath);

    const content = read(outPath);
    const chunks = (assignedByType.get(m.type) || []).length;
    manifestLines.push(`${m.type}\t${themeRelativePath(outPath)}\tbytes=${Buffer.byteLength(content, 'utf8')}\tchunks=${chunks}\tmodule_file=${m.file}`);
  }

  const sharedPath = path.join(splitDir, '_shared.css');
  const sharedBody = ensureFinalNewline(sharedSource);
  write(sharedPath, sharedBody);

  manifestLines.push(`_shared\t${themeRelativePath(sharedPath)}\tbytes=${Buffer.byteLength(sharedBody, 'utf8')}\tchunks=${(assignedByType.get('_shared') || []).length}\tmodule_file=shared`);

  const outManifest = path.join(splitDir, '_manifest.txt');
  write(outManifest, manifestLines.join('\n') + '\n');
  write(path.join(splitDir, '_assignment.tsv'), assignmentRows.join('\n') + '\n');

  const bundleBody = ensureFinalNewline(
    generatedModulePaths
      .sort((a, b) => path.basename(a).localeCompare(path.basename(b)))
      .map((file) => read(file))
      .join('\n')
  );
  write(modulesCssPath, bundleBody);

  let sumBytes = 0;
  for (const [type, arr] of assignedByType.entries()) {
    void type;
    sumBytes += Buffer.byteLength(arr.join(''), 'utf8');
  }

  const allRecombined = blocks.map((b) => b.text).join('');
  const heroRecombined = heroBlocks.map((b) => b.text).join('');
  const strictMainOk = allRecombined === css && assignedMainBytes === sourceBytes;
  const strictHeroOk = !heroCss || (heroRecombined === heroCss && assignedHeroBytes === heroSourceBytes);
  const strictOk = strictMainOk && strictHeroOk;

  console.log(`modules=${modules.length}`);
  console.log(`blocks=${blocks.length}`);
  console.log(`source_bytes=${sourceBytes}`);
  if (heroCss) {
    console.log(`hero_blocks=${heroBlocks.length}`);
    console.log(`hero_source_bytes=${heroSourceBytes}`);
    console.log(`hero_assigned_bytes=${assignedHeroBytes}`);
  }
  console.log(`assigned_bytes=${sumBytes}`);
  console.log(`stale_removed=${removedStaleFiles}`);
  console.log(`strict_equal=${strictOk ? 'yes' : 'no'}`);

  if (!strictOk) {
    process.exitCode = 2;
  }
}

main();
