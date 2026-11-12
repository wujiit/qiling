#!/usr/bin/env node

import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const args = new Set(process.argv.slice(2));
const jsonMode = args.has('--json');
const markdownMode = args.has('--markdown');
const strictMode = args.has('--strict');

const commonModules = [
  { slug: 'contact', label: '联系我们', roles: ['fixed_text', 'form', 'button'] },
  { slug: 'features', label: '特点/优势', roles: ['fixed_text', 'card', 'button'] },
  { slug: 'features_list', label: '特点列表', roles: ['fixed_text', 'card', 'tag'] },
  { slug: 'services', label: '服务', roles: ['fixed_text', 'card', 'button'] },
  { slug: 'service_cards', label: '服务卡片', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'products', label: '产品', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'product_showcase', label: '产品展示', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'cases', label: '案例', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'work_library', label: '作品库', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'blog', label: '博客', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'news', label: '新闻', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'featured_posts', label: '精选文章', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'media_list', label: '媒体列表', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'faq', label: '常见问题', roles: ['fixed_text', 'card', 'tag'] },
  { slug: 'accordion', label: '折叠面板', roles: ['fixed_text', 'card', 'tag'] },
  { slug: 'tabs', label: '标签页', roles: ['card', 'tag'] },
  { slug: 'category_tabs', label: '分类标签页', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'pricing', label: '价格套餐', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'testimonials', label: '客户评价', roles: ['fixed_text', 'card', 'tag'] },
  { slug: 'team', label: '团队', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'cta', label: '行动号召', roles: ['fixed_text', 'button'] },
  { slug: 'process', label: '流程', roles: ['fixed_text', 'card', 'tag'] },
  { slug: 'branches', label: '门店/分支', roles: ['fixed_text', 'card', 'button', 'tag'] },
  { slug: 'footer_suite', label: '页脚套件', roles: ['fixed_text', 'button', 'tag'] },
];

const roleStandards = {
  fixed_text: {
    label: '固定文案',
    description: '默认标签、说明、摘要、元信息、小标题。',
    preferred: [
      '--color-text-muted',
      '--color-heading',
      '--qiling-component-module-title-color',
      '--qiling-component-post-card-meta-color',
      '--qiling-component-post-card-title-color',
      '--qiling-component-accordion-title',
      '--qiling-component-footer-heading',
      '--qiling-component-footer-text',
      '--qiling-component-footer-link',
      '--color-text-inverse',
      '--color-accent',
    ],
    legacy: ['--dm-text-muted', '--color-neutral-600', '--color-neutral-700', '--color-neutral-800', '--color-neutral-900'],
    selector: /(?:^|[-_.\s>])(title|subtitle|label|meta|desc|description|summary|caption|eyebrow|author|creator|date|heading|h[1-6])(?:$|[-_.\s:#>])/i,
    property: /(?:^|[;\s])color\s*:/i,
  },
  card: {
    label: '卡片',
    description: '卡片、列表项、面板、内容盒子。',
    preferred: [
      '--qiling-component-card-bg',
      '--qiling-component-card-border',
      '--qiling-component-card-shadow',
      '--qiling-component-post-card-bg',
      '--qiling-component-post-card-border',
      '--qiling-component-post-card-shadow',
      '--qiling-card-radius',
      '--color-surface',
      '--color-border',
      '--shadow-',
    ],
    legacy: ['--color-neutral-0', '--color-neutral-50', '--color-neutral-100', '--color-neutral-800', '--color-neutral-900'],
    selector: /(card|item|panel|box|tile|entry|post|product|case|plan|member|testimonial|faq|accordion|tab-content)/i,
    property: /\b(background|border|box-shadow|border-radius)\s*:/i,
  },
  button: {
    label: '按钮',
    description: 'CTA、查看详情、提交按钮、链接按钮。',
    preferred: [
      '--qiling-component-button-bg',
      '--qiling-component-button-text',
      '--qiling-component-button-border',
      '--qiling-component-button-hover-bg',
      '--qiling-component-button-hover-text',
      '--qiling-component-button-shadow',
      '--qiling-component-button-padding',
      '--qiling-button-radius',
      '--qiling-gradient-brand',
      '--color-primary',
    ],
    legacy: ['--color-neutral-800', '--color-neutral-900'],
    selector: /(button|btn|submit|more|read-more|load-more|inquire|action)/i,
    property: /\b(color|background|border|box-shadow|border-radius|padding)\s*:/i,
  },
  form: {
    label: '表单',
    description: '输入框、文本域、选择框、表单容器。',
    preferred: [
      '--qiling-component-form-input-bg',
      '--qiling-component-form-input-text',
      '--qiling-component-form-input-border',
      '--qiling-component-form-focus-border',
      '--qiling-input-radius',
      '--color-surface',
      '--color-border',
    ],
    legacy: ['--color-neutral-50', '--color-neutral-100', '--color-neutral-800'],
    selector: /(form|input|textarea|select|field)/i,
    property: /\b(color|background|border|box-shadow|border-radius)\s*:/i,
  },
  tag: {
    label: '标签',
    description: '徽标、分类、pill、tab、状态标签。',
    preferred: [
      '--qiling-component-badge-bg',
      '--qiling-component-badge-text',
      '--qiling-component-tabs-text',
      '--qiling-component-tabs-active-bg',
      '--qiling-component-tabs-active-text',
      '--qiling-component-tabs-active-border',
      '--color-primary',
      '--color-success',
      '--color-warning',
      '--color-error',
      '--color-text-muted',
      '--qiling-gradient-',
    ],
    legacy: ['--color-neutral-100', '--color-neutral-600', '--color-neutral-800'],
    selector: /(tag|badge|pill|tab|category|filter|status|label|chip)/i,
    property: /\b(color|background|border|box-shadow|border-radius)\s*:/i,
  },
};

const hardcodedPattern = /#(?:[0-9a-f]{3}|[0-9a-f]{6})\b|(?:rgba?|hsla?)\([^)]*\)/gi;

const moduleScopeAliases = {
  features_list: ['.module-features-list', '.feature-card'],
  products: ['.module-products-manual', '.pm-'],
  cases: ['.module-cases', '.case-'],
  work_library: ['.module-work-library', '.qw-lib-'],
  blog: ['.module-blog', '.blog-'],
  featured_posts: ['.module-featured-posts', '.fp-'],
  media_list: ['.module-media-list', '.qil-ml-'],
  accordion: ['.module-accordion', '.accordion-'],
  tabs: ['.module-tabs', '.tabs-', '.tab-'],
  pricing: ['.module-pricing', '.pricing-'],
  testimonials: ['.module-testimonials', '.testimonial-', '.ql-testimonial-'],
  team: ['.module-team', '.team-'],
  cta: ['.module-cta', '.cta-', '.btn-cta'],
  process: ['.module-process', '.process-'],
  branches: ['.module-branches', '.branch-', '.branches-'],
  footer_suite: ['.module-footer-suite', '.qfs-'],
};

function scopeTokensForSlug(slug) {
  const typeHyphen = String(slug || '').replace(/_/g, '-');
  const typeUnderscore = String(slug || '').replace(/-/g, '_');
  return [
    `.module-${typeHyphen}`,
    `.module-${typeUnderscore}`,
    ...(moduleScopeAliases[slug] || []),
  ].filter(Boolean);
}

function selectorBelongsToModule(selector, slug) {
  const normalized = String(selector || '').toLowerCase();
  return scopeTokensForSlug(slug).some((token) => normalized.includes(token.toLowerCase()));
}

function stripCssVarFunctions(text) {
  const source = String(text || '');
  let result = '';
  let index = 0;

  while (index < source.length) {
    if (source.slice(index, index + 4).toLowerCase() !== 'var(') {
      result += source[index];
      index += 1;
      continue;
    }

    let depth = 0;
    while (index < source.length) {
      const char = source[index];
      if (char === '(') {
        depth += 1;
      } else if (char === ')') {
        depth -= 1;
        index += 1;
        if (depth <= 0) {
          break;
        }
        continue;
      }
      index += 1;
    }

    result += 'var()';
  }

  return result;
}

function cssPathForSlug(slug) {
  return join(rootDir, 'assets/css/modules-split', `${slug}.css`);
}

function sharedCssPath() {
  return join(rootDir, 'assets/css/modules-split', '_shared.css');
}

function modulePathForSlug(slug) {
  return join(rootDir, 'inc/modules/modules', `class-${slug.replace(/_/g, '-')}-module.php`);
}

function relativePath(fullPath) {
  return fullPath.replace(`${rootDir}/`, '').replace(/\\/g, '/');
}

function readIfExists(fullPath) {
  return existsSync(fullPath) ? readFileSync(fullPath, 'utf8') : '';
}

function listFilesIfExists(fullPath) {
  if (!existsSync(fullPath)) {
    return [];
  }

  return readdirSync(fullPath, { withFileTypes: true })
    .filter((entry) => entry.isFile())
    .map((entry) => entry.name);
}

function buildModuleInventory() {
  const moduleDir = join(rootDir, 'inc/modules/modules');
  const cssDir = join(rootDir, 'assets/css/modules-split');
  const auditedSlugs = new Set(commonModules.map((module) => module.slug));
  const moduleSlugs = listFilesIfExists(moduleDir)
    .map((fileName) => {
      const match = fileName.match(/^class-(.+)-module\.php$/);
      return match ? match[1].replace(/-/g, '_') : '';
    })
    .filter(Boolean)
    .sort();
  const splitCssSlugs = listFilesIfExists(cssDir)
    .map((fileName) => {
      const match = fileName.match(/^(.+)\.css$/);
      return match ? match[1].replace(/-/g, '_') : '';
    })
    .filter((slug) => slug && slug !== '_shared')
    .sort();
  const splitCssSlugSet = new Set(splitCssSlugs);
  const moduleSlugSet = new Set(moduleSlugs);

  return {
    totalModules: moduleSlugs.length,
    auditedCommonModules: commonModules.length,
    inventoryOnlyModules: moduleSlugs.filter((slug) => !auditedSlugs.has(slug)),
    splitCssFiles: splitCssSlugs.length,
    modulesMissingSplitCss: moduleSlugs.filter((slug) => !splitCssSlugSet.has(slug)),
    splitCssWithoutModuleClass: splitCssSlugs.filter((slug) => !moduleSlugSet.has(slug)),
  };
}

function hasAny(text, needles) {
  return needles.some((needle) => text.includes(needle));
}

function extractTextColorValues(body) {
  const values = [];
  const pattern = /(?:^|;)\s*color\s*:\s*([^;]+)/gi;

  for (const match of body.matchAll(pattern)) {
    values.push(match[1].trim());
  }

  return values;
}

function extractRoleRules(css, role, slug) {
  const standard = roleStandards[role];
  if (!standard) {
    return [];
  }

  return css
    .split('}')
    .map((chunk) => {
      const parts = chunk.split('{');
      if (parts.length < 2) {
        return null;
      }

      const selector = parts[0].trim();
      const body = parts.slice(1).join('{').trim();
      const matchingSelectors = selector
        .split(',')
        .map((item) => item.trim())
        .filter((item) => selectorBelongsToModule(item, slug) && standard.selector.test(item));

      if (
        !selector
        || !body
        || matchingSelectors.length === 0
        || !standard.property.test(body)
      ) {
        return null;
      }

      const valueScope = role === 'fixed_text'
        ? extractTextColorValues(body).join('\n')
        : body;
      const hardcodedSource = stripCssVarFunctions(valueScope);
      const hardcoded = [];
      hardcodedPattern.lastIndex = 0;
      for (const match of hardcodedSource.matchAll(hardcodedPattern)) {
        const value = match[0];
        if (value && !value.includes('var(') && !/(?:rgba?|hsla?)\(\s*,/.test(value)) {
          hardcoded.push(value);
        }
      }

      return {
        selector: matchingSelectors.join(',\n'),
        tokenized: hasAny(valueScope, standard.preferred),
        legacy: hasAny(valueScope, standard.legacy),
        hardcoded,
      };
    })
    .filter(Boolean);
}

function summarizeRole(css, role, slug) {
  const rules = extractRoleRules(css, role, slug);
  const tokenizedRules = rules.filter((rule) => rule.tokenized);
  const legacyRules = rules.filter((rule) => rule.legacy);
  const hardcodedRules = rules.filter((rule) => rule.hardcoded.length > 0);

  let status = 'missing';
  if (tokenizedRules.length > 0 && legacyRules.length === 0 && hardcodedRules.length === 0) {
    status = 'ok';
  } else if (tokenizedRules.length > 0) {
    status = 'partial';
  } else if (rules.length > 0) {
    status = 'needs_review';
  }

  return {
    role,
    label: roleStandards[role].label,
    status,
    rules: rules.length,
    tokenized: tokenizedRules.length,
    legacy: legacyRules.length,
    hardcoded: hardcodedRules.length,
    samples: rules.slice(0, 3).map((rule) => rule.selector),
  };
}

function summarizeModule(moduleConfig) {
  const cssPath = cssPathForSlug(moduleConfig.slug);
  const modulePath = modulePathForSlug(moduleConfig.slug);
  const css = [readIfExists(sharedCssPath()), readIfExists(cssPath)].filter(Boolean).join('\n');
  const php = readIfExists(modulePath);
  const roleResults = moduleConfig.roles.map((role) => summarizeRole(css, role, moduleConfig.slug));
  const roleStatuses = roleResults.map((item) => item.status);

  let status = 'ok';
  if (!css) {
    status = 'missing_css';
  } else if (roleStatuses.includes('needs_review')) {
    status = 'needs_review';
  } else if (roleStatuses.includes('partial')) {
    status = 'partial';
  }

  return {
    slug: moduleConfig.slug,
    label: moduleConfig.label,
    status,
    cssFile: existsSync(cssPath) ? relativePath(cssPath) : '',
    moduleFile: existsSync(modulePath) ? relativePath(modulePath) : '',
    hasModuleClass: Boolean(php),
    roles: roleResults,
  };
}

function statusLabel(status) {
  const labels = {
    ok: '已接入',
    partial: '部分接入',
    needs_review: '需梳理',
    missing: '未识别',
    missing_css: '缺少样式文件',
  };

  return labels[status] || status;
}

function roleSummary(roles) {
  return roles
    .map((role) => `${role.label}:${statusLabel(role.status)}`)
    .join('；');
}

function buildSummary(modules) {
  return modules.reduce((summary, module) => {
    summary.total += 1;
    summary[module.status] = (summary[module.status] || 0) + 1;
    return summary;
  }, { total: 0, ok: 0, partial: 0, needs_review: 0, missing_css: 0 });
}

const inventory = buildModuleInventory();
const modules = commonModules.map(summarizeModule);
const summary = buildSummary(modules);
const result = {
  generatedAt: new Date().toISOString(),
  standards: Object.fromEntries(Object.entries(roleStandards).map(([key, value]) => [
    key,
    {
      label: value.label,
      description: value.description,
      preferred: value.preferred,
      legacy: value.legacy,
    },
  ])),
  inventory,
  summary,
  modules,
};

if (jsonMode) {
  console.log(JSON.stringify(result, null, 2));
} else if (markdownMode) {
  console.log('# 常用模块全局设计接入审计');
  console.log('');
  console.log(`- 主题模块库存：${inventory.totalModules}`);
  console.log(`- 常用模块扫描：${summary.total}`);
  console.log(`- 库存未纳入角色审计：${inventory.inventoryOnlyModules.length}`);
  console.log(`- 拆分样式文件：${inventory.splitCssFiles}`);
  console.log(`- 已接入：${summary.ok}`);
  console.log(`- 部分接入：${summary.partial}`);
  console.log(`- 需梳理：${summary.needs_review}`);
  console.log(`- 缺少样式文件：${summary.missing_css}`);
  console.log('');
  console.log('| 模块 | 状态 | 角色覆盖 | 样式文件 |');
  console.log('| --- | --- | --- | --- |');
  for (const module of modules) {
    console.log(`| ${module.label} \`${module.slug}\` | ${statusLabel(module.status)} | ${roleSummary(module.roles)} | ${module.cssFile || '-'} |`);
  }
} else {
  console.log('QiLing common module design surface audit');
  console.log(`- Theme module inventory: ${inventory.totalModules}`);
  console.log(`- Common modules audited: ${summary.total}`);
  console.log(`- Inventory-only modules: ${inventory.inventoryOnlyModules.length}`);
  console.log(`- Split CSS files: ${inventory.splitCssFiles}`);
  console.log(`- OK: ${summary.ok}`);
  console.log(`- Partial: ${summary.partial}`);
  console.log(`- Needs review: ${summary.needs_review}`);
  console.log(`- Missing CSS: ${summary.missing_css}`);
  console.log('');

  for (const module of modules) {
    console.log(`${statusLabel(module.status)} ${module.label} (${module.slug})`);
    for (const role of module.roles) {
      console.log(`  - ${role.label}: ${statusLabel(role.status)} (${role.tokenized}/${role.rules} tokenized, legacy ${role.legacy}, hardcoded ${role.hardcoded})`);
    }
  }
}

if (strictMode && (summary.needs_review > 0 || summary.missing_css > 0)) {
  process.exitCode = 1;
}
