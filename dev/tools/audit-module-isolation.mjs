#!/usr/bin/env node

import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const modulesDir = join(rootDir, 'inc/modules/modules');
const splitDir = join(rootDir, 'assets/css/modules-split');
const bundlePath = join(rootDir, 'assets/css/modules.css');
const failures = [];

function moduleIdFromPhp(source) {
  const match = source.match(/function\s+get_id\s*\(\s*\)\s*\{[\s\S]*?return\s+['"]([^'"]+)['"]/i);
  return match ? match[1].trim() : '';
}

function splitSelectorList(text) {
  const selectors = [];
  let start = 0;
  let roundDepth = 0;
  let squareDepth = 0;
  let quote = '';
  for (let index = 0; index < text.length; index += 1) {
    const char = text[index];
    if (quote) {
      if (char === '\\') index += 1;
      else if (char === quote) quote = '';
      continue;
    }
    if (char === '"' || char === "'") quote = char;
    else if (char === '(') roundDepth += 1;
    else if (char === ')') roundDepth -= 1;
    else if (char === '[') squareDepth += 1;
    else if (char === ']') squareDepth -= 1;
    else if (char === ',' && roundDepth === 0 && squareDepth === 0) {
      selectors.push(text.slice(start, index).trim());
      start = index + 1;
    }
  }
  selectors.push(text.slice(start).trim());
  return selectors.filter(Boolean);
}

function findUnscopedSelectors(css, scope, insideKeyframes = false) {
  const unscoped = [];
  let cursor = 0;
  while (cursor < css.length) {
    const openIndex = css.indexOf('{', cursor);
    if (openIndex < 0) break;

    const prelude = css.slice(cursor, openIndex).replace(/\/\*[\s\S]*?\*\//g, '').trim();
    let depth = 1;
    let closeIndex = openIndex + 1;
    let quote = '';
    for (; closeIndex < css.length && depth > 0; closeIndex += 1) {
      const char = css[closeIndex];
      if (quote) {
        if (char === '\\') closeIndex += 1;
        else if (char === quote) quote = '';
        continue;
      }
      if (char === '"' || char === "'") quote = char;
      else if (char === '{') depth += 1;
      else if (char === '}') depth -= 1;
    }

    const body = css.slice(openIndex + 1, closeIndex - 1);
    const isKeyframes = /^@(?:-webkit-)?keyframes\b/i.test(prelude);
    if (/^@(media|supports|container|layer|document)\b/i.test(prelude)) {
      unscoped.push(...findUnscopedSelectors(body, scope, false));
    } else if (isKeyframes) {
      unscoped.push(...findUnscopedSelectors(body, scope, true));
    } else if (!insideKeyframes && !prelude.startsWith('@')) {
      for (const selector of splitSelectorList(prelude)) {
        if (!selector.includes(scope)) unscoped.push(selector);
      }
    }
    cursor = closeIndex;
  }

  return unscoped;
}

const moduleFiles = readdirSync(modulesDir)
  .filter((file) => /^class-.*-module\.php$/i.test(file))
  .sort();
const ids = new Map();
const moduleCssPaths = [];

for (const file of moduleFiles) {
  const id = moduleIdFromPhp(readFileSync(join(modulesDir, file), 'utf8'));
  if (!id) {
    failures.push(`${file}: missing literal get_id()`);
    continue;
  }
  if (ids.has(id)) failures.push(`${file}: duplicate module ID ${id} (also ${ids.get(id)})`);
  ids.set(id, file);

  const cssPath = join(splitDir, `${id}.css`);
  if (!existsSync(cssPath)) {
    failures.push(`${id}: missing independent stylesheet`);
    continue;
  }

  const scope = `.qiling-module-scope-${id}`;
  const unscoped = findUnscopedSelectors(readFileSync(cssPath, 'utf8'), scope);
  if (unscoped.length > 0) {
    failures.push(`${id}: unscoped selectors: ${unscoped.slice(0, 3).join(', ')}`);
  }
  moduleCssPaths.push(cssPath);
}

if (moduleFiles.length !== 86 || ids.size !== 86) {
  failures.push(`expected 86 independent modules, found files=${moduleFiles.length}, ids=${ids.size}`);
}

const expectedBundle = `${moduleCssPaths
  .sort((a, b) => a.localeCompare(b))
  .map((file) => readFileSync(file, 'utf8'))
  .join('\n').replace(/\n*$/, '')}\n`;
const actualBundle = existsSync(bundlePath) ? readFileSync(bundlePath, 'utf8') : '';
if (actualBundle !== expectedBundle) {
  failures.push('modules.css is not the exact bundle of the 86 independent module stylesheets');
}

const defaultButtonTextContracts = {
  contact: ['var(--contact-submit-text, #ffffff)', 'var(--contact-submit-hover-text, #ffffff)'],
  news: ['var(--news-button-text, #ffffff)', 'var(--news-button-hover-text, #ffffff)'],
  pricing: ['var(--pricing-button-text, #ffffff)', 'var(--pricing-button-hover-text, #ffffff)'],
  products: ['var(--products-inquire-btn-text, #ffffff)', 'var(--products-inquire-btn-hover-text, #ffffff)'],
  author_matrix: ['var(--am-btn-text, #ffffff)', 'var(--am-btn-hover-text, #ffffff)'],
  blog: ['var(--blog-read-more-text, #ffffff)', 'var(--blog-read-more-hover-text, #ffffff)'],
  category_tabs: ['var(--category-tabs-more-text, #ffffff)', 'var(--category-tabs-more-hover-text, #ffffff)'],
  certificate_honors: ['var(--ch-link-text, #ffffff)', 'var(--ch-link-hover-text, #ffffff)'],
  cta: ['var(--qiling-cta-button-text, #ffffff)', 'var(--qiling-cta-button-hover-text, #ffffff)'],
  downloads: ['var(--downloads-btn-text, #ffffff)', 'var(--downloads-btn-hover-text, #ffffff)'],
  image_text: ['var(--image-text-btn-text, #ffffff)', 'var(--image-text-btn-hover-text, #ffffff)'],
  lookbook: ['var(--lookbook-item-btn-text, #ffffff)', 'var(--lookbook-item-btn-hover-text, #ffffff)'],
  promotion: ['var(--ql-promo-btn-text, #ffffff)', 'var(--ql-promo-btn-hover-text, #ffffff)'],
  'room-showcase': ['var(--ql-room-btn-text, #ffffff)', 'var(--ql-room-btn-hover-text, #ffffff)'],
  software_carousel: ['var(--sc-btn-text, #ffffff)', 'var(--sc-btn-hover-text, #ffffff)'],
  software_category: ['var(--software-category-btn-text, #ffffff)', 'var(--software-category-btn-hover-text, #ffffff)'],
  software_ranking: ['var(--sr-btn-text, #ffffff)', 'var(--sr-btn-hover-text, #ffffff)'],
  'ticket-showcase': ['var(--ql-ticket-btn-text, #ffffff)', 'var(--ql-ticket-btn-hover-text, #ffffff)'],
  compliance_trust: ['var(--ct-btn-text, #ffffff)', 'var(--ct-btn-hover-text, #ffffff)'],
  countdown: ['var(--countdown-btn-text, #ffffff)', 'var(--countdown-btn-hover-text, #ffffff)'],
  qiling_main_category_content: ['var(--qmcc-more-btn-text, #ffffff)', 'var(--qmcc-more-btn-hover-text, #ffffff)'],
  qiling_universal_recommend: ['var(--qur-more-btn-text, #ffffff)', 'var(--qur-more-btn-hover-text, #ffffff)'],
  work_detail: ['var(--work-detail-cta-text, #ffffff)', 'var(--work-detail-cta-hover-text, #ffffff)'],
  magic_layout: ['var(--magic-layout-primary-btn-text, #ffffff)', 'var(--magic-layout-primary-btn-hover-text, #ffffff)'],
  brand_banner_pro: ['var(--bbp-primary-btn-text, #ffffff)', 'var(--bbp-primary-btn-hover-text, #ffffff)'],
  dynamic_banner: ['var(--db-primary-btn-text, #ffffff)', 'var(--db-primary-btn-hover-text, #ffffff)'],
  hero_search: ['var(--hs-search-btn-text, #ffffff)', 'var(--hs-search-btn-hover-text, #ffffff)'],
  resume_hero: ['var(--rh-solid-btn-text, #ffffff)', 'var(--rh-solid-btn-hover-text, #ffffff)'],
  interact_hero: ['var(--ih-primary-btn-text, #ffffff)', 'var(--ih-primary-btn-hover-text, #ffffff)'],
  qiling_video_portal_hero: ['var(--qvph-play-btn-text, #ffffff)', 'var(--qvph-play-btn-hover-text, #ffffff)'],
};
for (const [id, contracts] of Object.entries(defaultButtonTextContracts)) {
  const cssPath = join(splitDir, `${id}.css`);
  const css = existsSync(cssPath) ? readFileSync(cssPath, 'utf8') : '';
  for (const contract of contracts) {
    if (!css.includes(contract)) failures.push(`${id}: missing white default button text contract ${contract}`);
  }
}

const productsPhpPath = join(rootDir, 'inc/modules/modules/class-products-module.php');
const productsPhp = existsSync(productsPhpPath) ? readFileSync(productsPhpPath, 'utf8') : '';
const productsContracts = [
  'qiling-products-modal-portal qiling-module-scope qiling-module-scope-products',
  'data-qiling-module-scope="products"',
  'modal.hidden = true',
  'modal.hidden = false',
  'section.isConnected',
  'portal.isConnected',
  'cleanupModule()',
];
for (const contract of productsContracts) {
  if (!productsPhp.includes(contract)) failures.push(`products: missing interaction isolation contract ${contract}`);
}
if (productsPhp.includes('document.body.appendChild(modal)')) {
  failures.push('products: modal must not be moved to body without its module scope portal');
}

const dynamicallyInitializedModules = [
  'banner',
  'category-tabs',
  'double-column-carousel',
  'dynamic-banner',
  'fullscreen-video',
  'hero-search',
  'product-showcase',
  'qiling-shop-showcase',
  'tabbed-carousel',
  'video-portal-hero',
];
for (const slug of dynamicallyInitializedModules) {
  const phpPath = join(rootDir, 'inc/modules/modules', `class-${slug}-module.php`);
  const php = existsSync(phpPath) ? readFileSync(phpPath, 'utf8') : '';
  if (!php.includes("document.readyState === 'loading'") || !php.includes('boot();')) {
    failures.push(`${slug}: dynamic Builder initialization contract is missing`);
  }
}

if (failures.length > 0) {
  console.error(failures.join('\n'));
  process.exit(1);
}

console.log(`module_isolation=ok modules=${ids.size} styles=${ids.size}`);
