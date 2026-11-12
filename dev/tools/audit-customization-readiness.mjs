#!/usr/bin/env node

import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const rootDir = resolve(scriptDir, '../..');

function readThemeFile(relativePath) {
  return readFileSync(join(rootDir, relativePath), 'utf8');
}

function readAdminSettingsFieldRenderSources() {
  const aggregator = readThemeFile('inc/admin/traits/class-admin-settings-field-render-trait.php');
  const splitDir = join(rootDir, 'inc/admin/traits/field-render');
  const splitSources = existsSync(splitDir)
    ? readdirSync(splitDir)
      .filter((file) => file.endsWith('.php'))
      .sort()
      .map((file) => readThemeFile(`inc/admin/traits/field-render/${file}`))
    : [];

  return [aggregator, ...splitSources].join('\n');
}

function assertContains(source, needle, label) {
  if (!source.includes(needle)) {
    throw new Error(`${label} is missing: ${needle}`);
  }
}

function assertNotContains(source, needle, label) {
  if (source.includes(needle)) {
    throw new Error(`${label} should not contain: ${needle}`);
  }
}

function runModuleSurfaceAudit() {
  const result = spawnSync(process.execPath, [join(scriptDir, 'audit-module-design-surfaces.mjs'), '--json', '--strict'], {
    cwd: rootDir,
    encoding: 'utf8',
  });

  if (result.status !== 0) {
    throw new Error((result.stderr || result.stdout || 'Module design surface audit failed.').trim());
  }

  const payload = JSON.parse(result.stdout || '{}');
  const summary = payload.summary || {};
  const problems = [];

  if (Number(summary.partial || 0) > 0) {
    problems.push(`partial=${summary.partial}`);
  }
  if (Number(summary.needs_review || 0) > 0) {
    problems.push(`needs_review=${summary.needs_review}`);
  }
  if (Number(summary.missing_css || 0) > 0) {
    problems.push(`missing_css=${summary.missing_css}`);
  }

  if (problems.length > 0) {
    throw new Error(`Common module design surfaces are not fully ready: ${problems.join(', ')}`);
  }

  return summary;
}

function assertPageOverrideNetwork() {
  const designTokens = readThemeFile('inc/core/class-design-tokens.php');
  const frontendBuilder = readThemeFile('assets/js/frontend-builder.js');

  [
    "'palette'",
    "'typography'",
    "'layout'",
    "'component_styles'",
    "'structure'",
    "'animation_speed'",
    "'dark_bg'",
    "'dark_surface'",
    'sanitize_page_design_overrides',
    'persist_page_design_overrides',
    'format_page_design_overrides_for_builder',
    'compact_page_design_overrides',
  ].forEach((needle) => assertContains(designTokens, needle, 'Page design override network'));

  [
    'pageDesign',
    'page_design',
    'resetPageDesignGroup',
    'data-page-design-reset',
    'data-qfb-page-design-summary',
    'componentStyles',
  ].forEach((needle) => assertContains(frontendBuilder, needle, 'Frontend builder page override UX'));
}

function assertAdminOperatorFlow() {
  const pageRender = readThemeFile('inc/admin/traits/class-admin-settings-page-render-trait.php');
  const adminTrait = readThemeFile('inc/admin/traits/class-admin-settings-admin-trait.php');
  const fieldRender = readAdminSettingsFieldRenderSources();
  const configTrait = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');

  [
    'render_settings_recommended_action',
    'render_settings_context_summary',
    'render_settings_quick_shortcuts',
    'ds-settings-save-hint',
    '支持关键词搜索，仅查当前选项卡',
  ].forEach((needle) => assertContains(pageRender, needle, 'Admin ordinary-user guidance'));

  [
    'ds-search-empty',
    'getSearchSuggestions',
    'ds-settings-shortcut',
    'resetSearch',
    'clearSearch(false)',
    'visibleCount',
  ].forEach((needle) => assertContains(adminTrait, needle, 'Admin settings search UX'));

  [
    'data-reset-search="1"',
    'ds-settings-shortcut--all',
  ].forEach((needle) => assertContains(pageRender, needle, 'Admin quick shortcut reset UX'));

  [
    'render_header_settings_governance_field',
    'render_footer_settings_governance_field',
    'render_header_menu_locations_overview_field',
    'ds-settings-governance--clean',
  ].forEach((needle) => assertContains(fieldRender, needle, 'Header/footer settings guidance UX'));

  [
    '头部变体设置',
    '移动端顶部入口',
    '默认栏目显示',
    '备案信息',
  ].forEach((needle) => assertContains(configTrait, needle, 'Header/footer settings grouping'));

  [
    'render_settings_focus_notice',
    'render_header_secondary_settings_field',
    'render_footer_secondary_settings_field',
    'render_header_advanced_compatibility_field',
    'render_footer_builder_scope_field',
    '搜索时临时展开隐藏项',
  ].forEach((needle) => assertNotContains(`${pageRender}\n${fieldRender}\n${configTrait}`, needle, 'Transition-era hidden settings UX'));
}

function listModulePhpFiles() {
  const moduleDir = join(rootDir, 'inc/modules/modules');
  if (!existsSync(moduleDir)) {
    return [];
  }

  return readdirSync(moduleDir)
    .filter((file) => file.endsWith('.php'))
    .sort()
    .map((file) => join(moduleDir, file));
}

function assertFrontendBuilderColorCustomizationNetwork() {
  const frontendBuilder = readThemeFile('assets/js/frontend-builder.js');
  const frontendBuilderPhp = readThemeFile('inc/core/class-frontend-builder.php');
  const frontendBuilderCss = readThemeFile('assets/css/frontend-builder.css');
  const bannerModule = readThemeFile('inc/modules/modules/class-banner-module.php');
  const visualStyleService = readThemeFile('inc/core/class-module-visual-style-service.php');
  const advancedCss = readThemeFile('assets/css/module-advanced-styles.css');

  [
    'function getQuickColorSuggestions',
    'function fieldLooksLikeQuickColorTarget',
    "buildQuickColorSuggestion(getText('quickColorRed', '红色'), '#ef4444')",
    "buildQuickColorSuggestion(getText('quickColorBlue', '蓝色'), '#2563eb')",
    "buildQuickColorSuggestion(getText('quickColorBlack', '黑色'), '#111827')",
    "getText('tokenApply', '快速颜色选择')",
    "getText('tokenApplied', '已选择快捷颜色')",
    "renderDesignTokenPicker(subId, subType, 'repeater')",
    "renderDesignTokenPicker(fieldId, fieldType, 'field')",
    "renderDesignTokenPicker(path, field.type || inputType, 'page')",
    'function getPageVisualResolvedVars',
    'function getPageVisualFieldEffectivePresetValue',
    'function renderPageVisualPresetValue',
    "getText('pageVisualPresetValueLabel', '当前预设值')",
    "renderDesignTokenPicker(path, 'color', 'advanced')",
    "scope === 'advanced'",
    "advancedField.querySelector('.qfb-advanced-input[data-advanced-path=\"'",
    'updateRepeaterFieldData(repeaterEl)',
    "label_color: getFirstNonEmptyValue(item, ['label_color', 'description_color', 'desc_color', 'text_color'])",
    "{ path: 'buttons.text'",
    "{ path: 'buttons.hover_background'",
    "{ path: 'buttons.hover_text'",
  ].forEach((needle) => assertContains(frontendBuilder, needle, `Frontend builder quick-color customization contract changed unexpectedly: ${needle}`));

  [
    '快速颜色选择',
    '已选择快捷颜色',
    "'pageVisualResolved' => $page_visual_resolved",
    "'pageVisualPresetValueLabel' => __( '当前预设值'",
  ].forEach((needle) => assertContains(frontendBuilderPhp, needle, `Frontend builder localized quick-color text changed unexpectedly: ${needle}`));

  [
    '.qfb-page-visual-preset-value',
    '.qfb-page-visual-preset-swatch',
  ].forEach((needle) => assertContains(frontendBuilderCss, needle, `Page visual preset value display changed unexpectedly: ${needle}`));

  [
    "应用全局样式",
    '已应用全局样式',
    'var(--qiling-button-bg',
    'var(--color-primary',
  ].forEach((needle) => {
    const quickColorFunction = frontendBuilder.slice(
      frontendBuilder.indexOf('function getQuickColorSuggestions'),
      frontendBuilder.indexOf('function renderDesignTokenPicker')
    );
    assertNotContains(quickColorFunction, needle, `Quick color suggestions must stay as fixed user-entered values, not global-style tokens`);
  });

  [
    'get_stats_bar_items',
    'stats_bar_bg_color',
    'developer_starter_sanitize_page_visual_style_css_value( $stats_bar_bg_color )',
    'var(--qiling-module-card-bg, rgba(var(--qiling-rgb-255-255-255), 0.15))',
    'var(--qiling-module-card-bg, rgba(var(--qiling-rgb-255-255-255), 0.1))',
    'label_color',
    "'description_color', 'desc_color', 'text_color'",
    'color: <?php echo esc_attr( $label_color ); ?>',
    '--qiling-module-button-bg',
    '--qiling-module-button-text',
  ].forEach((needle) => assertContains(bannerModule, needle, `Banner customization rendering contract changed unexpectedly: ${needle}`));

  [
    'filter_wrapper_class',
    'filter_wrapper_style',
    'filter_wrapper_attr',
    'compile_wrapper_css_variables',
    'get_simple_mode_css_variables',
    "'base.primary'",
    "'buttons.text'",
    "'buttons.hover_background'",
    "'buttons.hover_text'",
    "'--qiling-module-button-text'",
    "'--qiling-component-button-text'",
  ].forEach((needle) => assertContains(visualStyleService, needle, `Module visual style service contract changed unexpectedly: ${needle}`));

  [
    '[data-qds-visual="1"]',
    'var(--qiling-module-bg)',
    'var(--qiling-module-title',
    'var(--qiling-module-text',
    'var(--qiling-module-button-bg',
    'var(--qiling-module-button-text',
    'var(--qiling-module-card-bg',
  ].forEach((needle) => assertContains(advancedCss, needle, `Module visual CSS bridge changed unexpectedly: ${needle}`));

  const moduleColorFieldCount = listModulePhpFiles().reduce((count, file) => {
    const source = readFileSync(file, 'utf8');
    return count + (source.match(/['"]type['"]\s*=>\s*['"]color['"]/g) || []).length;
  }, 0);

  if (moduleColorFieldCount < 120) {
    throw new Error(`Expected the module color field inventory to stay broad; found only ${moduleColorFieldCount}`);
  }

  return {
    moduleColorFieldCount,
  };
}

function assertTransparentHeaderMenuDefaults() {
  const mainCss = readThemeFile('assets/css/main.css');
  const cloudCanvasCss = readThemeFile('assets/css/cloud-canvas-skin.css');
  const pageSkinHelpers = readThemeFile('inc/core/helpers/helpers-page-skins.php');
  const designTokens = readThemeFile('inc/core/class-design-tokens.php');
  const postSettingsService = readThemeFile('inc/admin/class-meta-boxes-post-settings-service.php');

  [
    '--qiling-header-text-current: var(--qiling-header-transparent-text, var(--color-text-inverse))',
    '--qiling-header-nav-current: var(--qiling-header-transparent-nav-link, var(--qiling-header-text-current))',
    '--qiling-header-nav-hover-current: var(--qiling-header-transparent-nav-link, var(--qiling-header-text-current))',
    '--qiling-header-phone-current-bg: var(--qiling-header-phone-transparent-bg, var(--qiling-color-rgba-255-255-255-016))',
    '--qiling-header-search-current-bg: var(--qiling-header-search-transparent-bg, var(--qiling-color-rgba-255-255-255-014))',
    '--qiling-header-search-current-text: var(--qiling-header-search-transparent-text, var(--color-text-inverse))',
    '--qiling-header-search-current-icon: var(--qiling-header-search-transparent-icon, var(--color-text-inverse))',
    '.site-header .primary-navigation>ul>li.current-menu-item>a',
    'color: var(--qiling-header-nav-hover-current',
    'html.dark-mode .site-header.header-transparent:not(.header-scrolled) .primary-navigation>ul>li.current-menu-item>a',
    '[data-theme="dark"] .site-header.header-transparent:not(.header-scrolled) .primary-navigation>ul>li.current_page_item>a',
  ].forEach((needle) => assertContains(mainCss, needle, `Transparent header menu default contract changed unexpectedly: ${needle}`));

  [
    '--qiling-header-text-current: var(--qiling-header-transparent-text, #ffffff)',
    '--qiling-header-nav-current: var(--qiling-header-transparent-nav-link, var(--qiling-header-text-current, #ffffff))',
    '--qiling-header-nav-hover-current: var(--qiling-header-transparent-nav-link, var(--qiling-header-text-current, #ffffff))',
    '--qiling-header-phone-current-bg: var(--qiling-header-phone-transparent-bg, rgba(255, 255, 255, 0.16))',
    '--qiling-header-phone-current-text: var(--qiling-header-phone-transparent-text, #ffffff)',
    '--qiling-header-search-current-bg: var(--qiling-header-search-transparent-bg, rgba(255, 255, 255, 0.14))',
    '--qiling-header-search-current-text: var(--qiling-header-search-transparent-text, #ffffff)',
    '--qiling-header-search-current-icon: var(--qiling-header-search-transparent-icon, #ffffff)',
  ].forEach((needle) => assertContains(cloudCanvasCss, needle, `Cloud canvas transparent header must default menu text to white while respecting custom values: ${needle}`));

  [
    '--qiling-header-nav-current: var(--qcc-ink)',
    '--qiling-header-search-current-text: #5a2a16 !important',
    '--qiling-header-search-current-icon: #ff7a45 !important',
    '--qiling-header-phone-current-text: var(--qcc-ink)',
  ].forEach((needle) => assertNotContains(cloudCanvasCss, needle, `Cloud canvas skin must not force transparent header controls to dark ink: ${needle}`));

  [
    "'--qiling-header-transparent-text'              => '#ffffff'",
    "'--qiling-header-transparent-nav-link'          => '#ffffff'",
    "'--qiling-header-phone-transparent-bg'          => 'rgba(255, 255, 255, 0.16)'",
    "'--qiling-header-phone-transparent-text'        => '#ffffff'",
    "'--qiling-header-search-transparent-bg'         => 'rgba(255, 255, 255, 0.14)'",
    "'--qiling-header-search-transparent-text'       => '#ffffff'",
    "'--qiling-header-search-transparent-icon'       => '#ffffff'",
  ].forEach((needle) => assertContains(pageSkinHelpers, needle, `Industry page visual preset transparent header defaults changed unexpectedly: ${needle}`));

  assertContains(
    designTokens,
    "'header_phone_transparent_bg' => 'rgba(255, 255, 255, 0.16)'",
    'Global component transparent phone default should stay subtle on transparent headers'
  );

  [
    "fg: 'header.transparent_text'",
    "'header.transparent_text': ['--qiling-header-transparent-text', '--qiling-header-transparent-nav-link']",
    "setValue('header.transparent_text', '#ffffff')",
  ].forEach((needle) => assertContains(postSettingsService, needle, `Page visual transparent header repair/customization contract changed unexpectedly: ${needle}`));
}

function getPresetSection(source, key, nextKey = '') {
  const startNeedle = `\t'${key}'`;
  const start = source.indexOf(startNeedle);
  if (start === -1) {
    throw new Error(`System style preset is missing: ${key}`);
  }

  if (!nextKey) {
    return source.slice(start);
  }

  const end = source.indexOf(`\t'${nextKey}'`, start + startNeedle.length);
  if (end === -1) {
    throw new Error(`Unable to locate end of system style preset: ${key}`);
  }

  return source.slice(start, end);
}

function assertSystemStylePresetCoverage() {
  const presetsSource = readThemeFile('inc/core/design-tokens/system-style-presets.php');
  const requiredPresetOrder = [
    ['default', '通用官网'],
    ['enterprise', '企业服务'],
    ['technology', '科技产品'],
    ['medical', '医疗健康'],
    ['education', '教育培训'],
    ['restaurant', '餐饮门店'],
    ['magazine', '杂志媒体'],
    ['minimal', '极简内容'],
  ];
  const requiredDirectTokenKeys = [
    'primary',
    'primary_hover',
    'secondary',
    'accent',
    'success',
    'info',
    'warning',
    'error',
    'overlay',
    'text',
    'text_muted',
    'heading',
    'background',
    'surface',
    'surface_alt',
    'border',
    'dark_bg',
    'dark_surface',
    'dark_text',
    'dark_text_muted',
    'dark_border',
  ];
  const requiredNeutralKeys = [
    'neutral_0',
    'neutral_50',
    'neutral_100',
    'neutral_200',
    'neutral_300',
    'neutral_400',
    'neutral_500',
    'neutral_600',
    'neutral_700',
    'neutral_800',
    'neutral_900',
  ];
  const requiredComponentKeys = [
    'button_bg',
    'button_border',
    'button_hover_bg',
    'button_secondary_bg',
    'button_secondary_text',
    'button_secondary_border',
    'button_secondary_hover_bg',
    'border_accent',
    'title_bar_bg',
    'title_bar_border',
    'highlight_bg',
    'highlight_soft_bg',
    'form_focus_border',
    'auth_action_bg',
    'auth_code_bg',
    'badge_bg',
    'badge_border',
    'tabs_active_bg',
    'tabs_active_text',
    'tabs_active_border',
    'alert_bg',
    'alert_border',
    'alert_text',
    'announcement_marketing_bg',
    'announcement_marketing_button_text',
    'footer_bg',
    'footer_bottom_bg',
    'woo_card_price',
  ];
  const announcementBackgrounds = new Set();

  requiredNeutralKeys.forEach((tokenKey) => {
    assertContains(presetsSource, `'${tokenKey}'`, `Shared neutral palette token is missing: ${tokenKey}`);
  });

  [
    "'header_logo_transparent_fill'",
    "'header_phone_transparent_bg'",
    "'header_phone_transparent_text'",
  ].forEach((needle) => {
    assertContains(presetsSource, needle, `Shared transparent header component preset is missing: ${needle}`);
  });

  requiredPresetOrder.forEach(([presetKey, label], index) => {
    const nextKey = requiredPresetOrder[index + 1]?.[0] || '';
    const section = getPresetSection(presetsSource, presetKey, nextKey);

    assertContains(section, `__( '${label}'`, `System style preset label changed unexpectedly: ${presetKey}`);
    assertContains(section, 'array_merge(', `System style preset must merge a complete neutral scale: ${presetKey}`);
    assertContains(section, '$transparent_header_defaults', `System style preset must inherit transparent header defaults: ${presetKey}`);

    requiredDirectTokenKeys.forEach((tokenKey) => {
      assertContains(section, `'${tokenKey}'`, `System style preset ${presetKey} is missing token: ${tokenKey}`);
    });

    requiredComponentKeys.forEach((styleKey) => {
      assertContains(section, `'${styleKey}'`, `System style preset ${presetKey} is missing component style: ${styleKey}`);
    });

    const marketingBgMatch = section.match(/'announcement_marketing_bg'\s*=>\s*'([^']+)'/);
    if (!marketingBgMatch) {
      throw new Error(`System style preset ${presetKey} is missing an announcement marketing background value`);
    }
    announcementBackgrounds.add(marketingBgMatch[1]);
  });

  if (announcementBackgrounds.size < requiredPresetOrder.length) {
    throw new Error('System style presets should use distinct announcement marketing colors per preset');
  }
}

try {
  const moduleSummary = runModuleSurfaceAudit();
  const colorSummary = assertFrontendBuilderColorCustomizationNetwork();
  assertSystemStylePresetCoverage();
  assertTransparentHeaderMenuDefaults();
  assertPageOverrideNetwork();
  assertAdminOperatorFlow();

  console.log('QiLing customization readiness audit passed');
  console.log(`- Common modules ready: ${moduleSummary.ok || 0}/${moduleSummary.total || 0}`);
  console.log(`- Module color fields covered by generic quick-color renderer: ${colorSummary.moduleColorFieldCount}`);
  console.log('- System style presets: complete');
  console.log('- Transparent header menu defaults: ready');
  console.log('- Page-level override network: ready');
  console.log('- Admin ordinary-user flow: ready');
} catch (error) {
  console.error('QiLing customization readiness audit failed');
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
