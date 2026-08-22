import { readdirSync } from 'node:fs';
import {
  assertContains,
  assertFileExists,
  assertMatches,
  readAdminSettingsFieldRenderSources,
  readThemeFile,
  themePath,
} from './_helpers.mjs';

export const name = 'Global design token contracts';

export async function run() {
  [
    'inc/core/class-design-tokens.php',
    'inc/core/design-tokens/component-group-labels.php',
    'inc/core/design-tokens/component-style-definitions.php',
    'inc/core/design-tokens/default-layout-system.php',
    'inc/core/design-tokens/foundation-style-aliases.php',
    'inc/core/design-tokens/default-palette-tokens.php',
    'inc/core/design-tokens/default-typography-system.php',
    'inc/core/design-tokens/design-component-option-map.php',
    'inc/core/design-tokens/design-component-schema.php',
    'inc/core/design-tokens/design-token-option-map.php',
    'inc/core/design-tokens/design-token-schema.php',
    'inc/core/design-tokens/layout-field-definitions.php',
    'inc/core/design-tokens/page-structure-field-aliases.php',
    'inc/core/design-tokens/page-structure-field-definitions.php',
    'inc/core/design-tokens/palette-preset-token-keys.php',
    'inc/core/design-tokens/responsive-device-definitions.php',
    'inc/core/design-tokens/system-style-presets.php',
    'inc/core/design-tokens/token-definitions.php',
    'inc/core/design-tokens/typography-property-definitions.php',
    'inc/core/design-tokens/typography-style-definitions.php',
    'inc/core/class-assets.php',
    'inc/core/class-ai-decorator.php',
    'inc/core/class-frontend-builder.php',
    'inc/core/class-page-package-page-service.php',
    'inc/core/class-single-page-package-service.php',
    'inc/core/ai/class-prompt-builder.php',
    'inc/admin/traits/class-admin-settings-config-trait.php',
    'inc/admin/traits/class-admin-settings-admin-trait.php',
    'inc/admin/traits/class-admin-settings-field-trait.php',
    'inc/admin/traits/class-admin-settings-field-render-trait.php',
    'inc/admin/traits/class-admin-settings-sanitize-trait.php',
    'inc/admin/traits/class-admin-settings-page-render-trait.php',
    'assets/js/admin-design-preset-snapshot.js',
    'inc/customizer/class-customizer.php',
    'inc/modules/modules/class-breaking-news-ticker-module.php',
    'inc/modules/modules/class-features-module.php',
    'inc/modules/modules/class-pet-profile-module.php',
    'inc/modules/modules/class-pricing-module.php',
    'inc/modules/modules/class-process-module.php',
    'inc/modules/modules/class-promotion-module.php',
    'inc/modules/modules/class-skills-module.php',
    'inc/modules/modules/class-footer-suite-module.php',
    'assets/js/frontend-builder.js',
    'assets/css/frontend-builder.css',
    'assets/css/main.css',
    'assets/css/account.css',
    'assets/css/modules.css',
    'assets/css/modules-split/_manifest.txt',
    'assets/css/woocommerce.css',
    'dev/tools/audit-design-token-coverage.mjs',
    'dev/tools/audit-module-design-surfaces.mjs',
  ].forEach((file) => {
    assertFileExists(file, `Design token chain missing required file: ${file}`);
  });

  const designTokensPhp = readThemeFile('inc/core/class-design-tokens.php');
  const designTokenDataPhp = [
    'inc/core/design-tokens/component-group-labels.php',
    'inc/core/design-tokens/component-style-definitions.php',
    'inc/core/design-tokens/default-layout-system.php',
    'inc/core/design-tokens/foundation-style-aliases.php',
    'inc/core/design-tokens/default-palette-tokens.php',
    'inc/core/design-tokens/default-typography-system.php',
    'inc/core/design-tokens/design-component-option-map.php',
    'inc/core/design-tokens/design-component-schema.php',
    'inc/core/design-tokens/design-token-option-map.php',
    'inc/core/design-tokens/design-token-schema.php',
    'inc/core/design-tokens/layout-field-definitions.php',
    'inc/core/design-tokens/page-structure-field-aliases.php',
    'inc/core/design-tokens/page-structure-field-definitions.php',
    'inc/core/design-tokens/palette-preset-token-keys.php',
    'inc/core/design-tokens/responsive-device-definitions.php',
    'inc/core/design-tokens/system-style-presets.php',
    'inc/core/design-tokens/token-definitions.php',
    'inc/core/design-tokens/typography-property-definitions.php',
    'inc/core/design-tokens/typography-style-definitions.php',
  ].map((file) => readThemeFile(file)).join('\n');
  const designTokenContractPhp = `${designTokensPhp}\n${designTokenDataPhp}`;

  [
    'get_design_token_data',
    'static $cache = array();',
    "require $files[ $key ];",
    '/design-tokens/system-style-presets.php',
    '/design-tokens/design-component-schema.php',
    '/design-tokens/default-typography-system.php',
    '/design-tokens/foundation-style-aliases.php',
  ].forEach((needle) => {
    assertContains(designTokensPhp, needle, `Design token extracted data loader changed unexpectedly: ${needle}`);
  });

  [
    'class Design_Tokens',
    'TOKEN_SCHEMA_VERSION',
    'STORAGE_OPTION_KEY',
    'design_system_v2',
    'custom_presets',
    'typography_system',
    'layout_system',
    'primary_hover',
    'neutral_900',
    'success',
    'dark_text_muted',
    'PAGE_DESIGN_META_KEY',
    '_qiling_page_design_overrides',
    'PAGE_DESIGN_PRESET_META_KEY',
    '_qiling_page_design_preset',
    'CATEGORY_DESIGN_PRESET_META_KEY',
    'ds_category_design_preset',
    'get_style_presets',
    'get_preset_choices',
    'get_context_preset_choices',
    'get_context_preset_rules',
    'sanitize_context_preset_rules',
    'design_preset_context_rules',
    'design_preset_context_rules_present',
    'get_preset_token_values',
    'get_component_group_labels',
    'get_token_definitions',
    'get_client_payload',
    'get_storage_payload',
    'get_compatibility_option_payload',
    'merge_storage_payload_with_options',
    'get_prompt_context',
    'pageDesignDefinitions',
    'pageDesignDefaults',
    'tokenOptionMap',
    'tokenSchema',
    'get_page_design_meta_key',
    'sanitize_page_design_overrides',
    'get_page_design_overrides',
    'persist_page_design_overrides',
    'get_page_design_preset',
    'persist_page_design_preset',
    'get_category_design_preset',
    'persist_category_design_preset',
    'build_context_design_preset_css',
    'get_current_tokens_for_page',
    'build_page_override_css',
    'get_current_typography_system',
    'get_current_layout_system',
    'get_default_typography_system_values',
    'get_default_layout_system_values',
    'get_default_component_styles',
    'get_current_tokens',
    'normalize_runtime_theme_options',
    'migrate_legacy_component_styles_to_single_source',
    'get_css_variables',
    'foundation_style_aliases',
    'componentOptionMap',
    'componentSchema',
    'header_bg',
    'header_logo_transparent_fill',
    'header_logo_scrolled_fill',
    'header_phone_bg',
    'header_phone_text',
    'header_phone_transparent_bg',
    'header_phone_transparent_text',
    'header_scrolled_text',
    'nav_scrolled_link',
    'nav_scrolled_hover_text',
    'dropdown_bg',
    'tabs_active_bg',
    'footer_bg',
    'footer_heading',
    'footer_heading_size',
    'woo_card_bg',
    'build_root_css',
    'sanitize_options',
    'developer_starter_design_token_presets',
    'developer_starter_design_token_definitions',
    'developer_starter_design_tokens',
    'developer_starter_design_token_css_variables',
    'developer_starter_design_tokens_css',
    'recommended_css_variables',
    '--color-primary-rgb',
    '--color-success-rgb',
    '--color-neutral-400-rgb',
    '--dm-bg',
    '--qiling-gradient-brand',
    '--qiling-body-font-size',
    '--qiling-h1-font-size',
    '--qiling-header-scrolled-text',
    '--qiling-header-scrolled-nav-hover-text',
    '--qiling-component-header-logo-transparent-fill',
    '--qiling-component-header-logo-scrolled-fill',
    '--qiling-component-header-phone-bg',
    '--qiling-component-header-phone-transparent-bg',
    'success_gradient',
    'color-scheme: dark;',
    '--qiling-container-width',
    '--qiling-grid-gap',
    'breakpoint_tablet',
    '--qiling-button-radius',
    '--qiling-color-transparent',
    '--qiling-space-12',
    '--qiling-text-rem-0p95',
  ].forEach((needle) => {
    assertContains(designTokenContractPhp, needle, `Design token service contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    designTokensPhp,
    /^(?:(?!apply_legacy_component_style_compatibility).)*$/s,
    'Design token service should no longer layer a legacy component compatibility pass at runtime'
  );

  const assetsPhp = readThemeFile('inc/core/class-assets.php');
  [
    "class_exists( __NAMESPACE__ . '\\\\Design_Tokens' )",
    'Design_Tokens::build_root_css()',
    '--color-primary:{$primary}',
  ].forEach((needle) => {
    assertContains(assetsPhp, needle, `Assets dynamic CSS contract changed unexpectedly: ${needle}`);
  });

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'design'       => __( '全局样式'",
    'Design_Tokens::get_preset_choices()',
    "'design_enable_global_tokens'",
    "'design_preset'",
    "render_design_tokens_preview_field",
    "render_design_preset_manager_field",
    "render_design_preset_scope_manager_field",
    "render_design_typography_system_field",
    "render_design_layout_system_field",
    "'design_primary_color'",
    "'design_primary_hover_color'",
    "'design_success_color'",
    "'design_card_radius'",
    "'design_component_header_bg'",
    "'design_component_header_logo_transparent_fill'",
    "'design_component_header_logo_scrolled_fill'",
    "'design_component_header_phone_bg'",
    "'design_component_header_phone_text'",
    "'design_component_header_phone_transparent_bg'",
    "'design_component_header_phone_transparent_text'",
    "'design_component_header_scrolled_text'",
    "'design_component_nav_scrolled_link'",
    "'design_component_nav_scrolled_hover_text'",
    "'design_component_mobile_nav_bg'",
    "'design_component_dropdown_bg'",
    "'design_component_footer_bg'",
    "'design_component_footer_heading'",
    "'design_component_footer_heading_size'",
    "'design_component_woo_card_bg'",
    "'design_dark_surface'",
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Admin design settings contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    configPhp,
    /^(?:(?!兼容：顶部背景色).)*$/s,
    'Header legacy color rows should no longer render in admin settings'
  );
  assertMatches(
    configPhp,
    /^(?:(?!兼容：页脚顶部背景).)*$/s,
    'Footer legacy color rows should no longer render in admin settings'
  );

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  assertContains(
    sanitizePhp,
    'Design_Tokens::sanitize_options( $sanitized, $existing_options )',
    'Admin settings must sanitize global design token fields explicitly'
  );

  const fieldTraitPhp = readThemeFile('inc/admin/traits/class-admin-settings-field-trait.php');
  [
    'render_design_quick_values',
    'get_design_quick_value_items',
    'ds-design-quick-values',
    '快捷选择',
    '0 16px 40px rgba(15, 23, 42, 0.12)',
    '999px',
    '12px 24px',
    '0.25s',
  ].forEach((needle) => {
    assertContains(fieldTraitPhp, needle, `Admin global design quick value contract changed unexpectedly: ${needle}`);
  });

  const fieldRenderPhp = readAdminSettingsFieldRenderSources();
  const adminTraitPhp = readThemeFile('inc/admin/traits/class-admin-settings-admin-trait.php');
  const adminDesignPresetSnapshotJs = readThemeFile('assets/js/admin-design-preset-snapshot.js');
  [
    'render_design_tokens_preview_field',
    'render_design_preset_manager_field',
    'render_design_preset_scope_manager_field',
    'render_design_typography_system_field',
    'render_design_layout_system_field',
    'Design_Tokens::get_client_payload',
    '当前样式预览',
    '全局设计工作台',
    '设置层级',
    '桌面工作台快照',
    '手机与暗色快照',
    '颜色写法',
    '暗色提醒',
    'npm --prefix dev run audit:design',
    'data-ds-design-workbench-seed',
    'data-ds-preview-node',
    '覆盖明细',
    '缺项与空值提醒',
    '重点检查区域',
    'renderWorkbench',
    'renderRiskZones',
    'renderOverrideGroups',
    'renderMissingDiagnostics',
    'buildRiskZoneSummary',
    'data-ds-workbench-focus-selector',
    'data-ds-workbench-risk-zones',
    'data-ds-workbench-risk-zone',
    'getLegacyLocatorByOptionId',
    'design_font_size_base',
    'data-ds-typography-input',
    'data-ds-layout-input',
    'toPreviewSpacing',
    'focusFieldBySelector',
    'impactHigh',
    '响应式排版体系',
    '响应式布局尺度',
    '保存当前站点为新预设',
    '新增自定义预设',
    '复制当前预设',
    '收下当前站点效果',
    'typography_json',
    'layout_json',
    'components_json',
    '页头 / 文字 Logo / 桌面导航 / 电话按钮',
    '页脚 / Woo 卡片',
    'Woo 卡片',
    '生成可分享内容',
    '导入到当前列表',
    '用导入内容替换当前列表',
    '多品牌应用范围',
    '页面配色规则',
    '分类配色规则',
    'data-design-scope-manager',
    'design_preset_context_rules',
    'dark_text_muted',
  ].forEach((needle) => {
    assertContains(fieldRenderPhp, needle, `Admin design token preview contract changed unexpectedly: ${needle}`);
  });
  [
    'developer-starter-admin-design-preset-snapshot',
    "DEVELOPER_STARTER_ASSETS . '/js/admin-design-preset-snapshot.js'",
    'assets/js/admin-design-preset-snapshot.js',
    'filemtime( $design_preset_snapshot_js )',
  ].forEach((needle) => {
    assertContains(adminTraitPhp, needle, `Admin design preset snapshot asset enqueue changed unexpectedly: ${needle}`);
  });
  assertMatches(
    adminTraitPhp,
    /wp_enqueue_script\(\s*'developer-starter-admin-design-preset-snapshot'[\s\S]*DEVELOPER_STARTER_ASSETS \. '\/js\/admin-design-preset-snapshot\.js'[\s\S]*false\s*\);/,
    'Admin design preset snapshot helper must load before admin_footer inline scripts'
  );
  [
    'window.DSDesignPresetSnapshot',
    'safeParseJsonObject',
    'countValues',
    'buildSummary',
    'readSnapshot',
    'applySnapshotToCard',
  ].forEach((needle) => {
    assertContains(adminDesignPresetSnapshotJs, needle, `Admin design preset snapshot helper contract changed unexpectedly: ${needle}`);
  });
  [
    'window.DSDesignPresetSnapshot || {}',
    'presetSnapshotHelper.readSnapshot(card)',
    'presetSnapshotHelper.applySnapshotToCard(card, snapshot, messages)',
  ].forEach((needle) => {
    assertContains(fieldRenderPhp, needle, `Inline preset snapshot scripts should use shared helper: ${needle}`);
  });
  [
    'function safeParseJsonObject',
    'function countPresetSnapshotValues',
    'function buildPresetSnapshotSummary',
  ].forEach((needle) => {
    if (fieldRenderPhp.includes(needle)) {
      throw new Error(`Preset snapshot helper should live in assets/js/admin-design-preset-snapshot.js, not inline PHP: ${needle}`);
    }
  });
  const inlineSnapshotHelperBindings = fieldRenderPhp.match(/window\.DSDesignPresetSnapshot \|\| \{\}/g) || [];
  if (inlineSnapshotHelperBindings.length !== 2) {
    throw new Error('Both inline preset snapshot surfaces must bind the shared DSDesignPresetSnapshot helper');
  }

  const customizerPhp = readThemeFile('inc/customizer/class-customizer.php');
  [
    'customize_save_after',
    'sync_legacy_primary_color_to_theme_options',
    "'design_primary_color' => $primary",
  ].forEach((needle) => {
    assertContains(customizerPhp, needle, `Legacy customizer compatibility contract changed unexpectedly: ${needle}`);
  });

  const pageRenderPhp = readThemeFile('inc/admin/traits/class-admin-settings-page-render-trait.php');
  assertContains(
    pageRenderPhp,
    "array( 'design', 'header'",
    'Admin page render contract changed unexpectedly'
  );
  assertMatches(
    pageRenderPhp,
    /^(?:(?!maybe_persist_legacy_component_style_migration).)*$/s,
    'Admin page render should no longer persist legacy component migration on page load'
  );

  const frontendBuilderPhp = readThemeFile('inc/core/class-frontend-builder.php');
  [
    'Design_Tokens::get_client_payload()',
    "'designSystem'     => $design_system",
    "'pageTemplates'    => $page_templates",
    'pageSettingsPanelTitle',
    'pageDarkPaletteSectionTitle',
    'pageStructureSectionTitle',
    'pageDesignSummaryTitle',
    'pageDesignResetAll',
    '如果某个模块还要特殊一点，再去模块设置里单独改。',
    '快速颜色选择',
  ].forEach((needle) => {
    assertContains(frontendBuilderPhp, needle, `Frontend builder design system payload contract changed unexpectedly: ${needle}`);
  });

  const aiDecoratorPhp = readThemeFile('inc/core/class-ai-decorator.php');
  [
    "'designSystem'           => $this->get_design_system_context( 'client' )",
    'get_design_system_context',
    'Design_Tokens::get_prompt_context()',
    'Design_Tokens::get_page_design_overrides',
    'Design_Tokens::persist_page_design_overrides',
    'Design_Tokens::get_page_design_preset',
    'Design_Tokens::persist_page_design_preset',
  ].forEach((needle) => {
    assertContains(aiDecoratorPhp, needle, `AI design system context contract changed unexpectedly: ${needle}`);
  });

  const mainHeaderCss = readThemeFile('assets/css/main.css');
  [
    '--qiling-header-nav-hover-current',
    '--qiling-header-scrolled-text',
    '--qiling-header-scrolled-nav-link',
    '--qiling-header-logo-current-fill',
    '--qiling-header-logo-scrolled-fill',
  ].forEach((needle) => {
    assertContains(mainHeaderCss, needle, `Main CSS header state contract changed unexpectedly: ${needle}`);
  });

  const pagePackagePageServicePhp = readThemeFile('inc/core/class-page-package-page-service.php');
  [
    'page_design',
    'design_preset',
    'Design_Tokens::persist_page_design_overrides',
    'Design_Tokens::persist_page_design_preset',
  ].forEach((needle) => {
    assertContains(pagePackagePageServicePhp, needle, `Page package page settings contract changed unexpectedly: ${needle}`);
  });

  const singlePagePackagePhp = readThemeFile('inc/core/class-single-page-package-service.php');
  [
    'page_design',
    'design_preset',
    'Design_Tokens::sanitize_page_design_overrides',
    'Design_Tokens::get_page_design_overrides',
    'Design_Tokens::get_page_design_preset',
  ].forEach((needle) => {
    assertContains(singlePagePackagePhp, needle, `Single page package page-design contract changed unexpectedly: ${needle}`);
  });

  const frontendBuilderJs = readThemeFile('assets/js/frontend-builder.js');
  [
    'selectedScope',
    'pageSettingsEntry',
    'pageSettingsPanelTitle',
    'pageDesignSectionTitle',
    'pageDarkPaletteSectionTitle',
    'pageStructureSectionTitle',
    'renderPageDesignSummary',
    'renderPageDesignSectionToolbar',
    'refreshPageDesignSummaryUI',
    'resetPageDesignGroup',
    'applyPageDesignPreview',
    'serializePageDesignForPackage',
    'darkBg',
    'darkTextMuted',
    'cardRadius',
    'animationSpeed',
    'component_styles',
    'structure',
    'page_settings: JSON.stringify(state.pageSettings || {})',
    'page_design:',
    'design_preset:',
    "getText('tokenApply', '快速颜色选择')",
    "getText('tokenApplied', '已选择快捷颜色')",
    "getText('pageGovernanceModuleDesc', '如果某个模块还要特殊一点，再去模块设置里单独改。')",
  ].forEach((needle) => {
    assertContains(frontendBuilderJs, needle, `Frontend builder page-design contract changed unexpectedly: ${needle}`);
  });

  const categoryManagerPhp = readThemeFile('inc/core/class-category-manager.php');
  assertMatches(
    categoryManagerPhp,
    /^(?:(?!ds_category_design_preset).)*$/s,
    'Category editor should not render the multi-brand preset selector; it belongs in theme settings'
  );

  const postSettingsServicePhp = readThemeFile('inc/admin/class-meta-boxes-post-settings-service.php');
  assertMatches(
    postSettingsServicePhp,
    /^(?:(?!qiling_page_design_preset).)*$/s,
    'Page editor should not render the multi-brand preset selector; it belongs in theme settings'
  );

  const frontendBuilderCss = readThemeFile('assets/css/frontend-builder.css');
  [
    '.qfb-page-item-page',
    '.qfb-page-responsive-grid',
    '.qfb-drag-static',
  ].forEach((needle) => {
    assertContains(frontendBuilderCss, needle, `Frontend builder page-design CSS contract changed unexpectedly: ${needle}`);
  });

  const promptBuilderPhp = readThemeFile('inc/core/ai/class-prompt-builder.php');
  [
    'build_page_context',
    "'design_system' => $this->decorator->get_design_system_context( 'prompt' )",
  ].forEach((needle) => {
    assertContains(promptBuilderPhp, needle, `AI prompt design context contract changed unexpectedly: ${needle}`);
  });

  const petProfilePhp = readThemeFile('inc/modules/modules/class-pet-profile-module.php');
  [
    'var(--pet-primary, var(--color-accent))',
    "'pet_primary_color'",
  ].forEach((needle) => {
    assertContains(petProfilePhp, needle, `Pet profile design token inheritance contract changed unexpectedly: ${needle}`);
  });

  const skillsPhp = readThemeFile('inc/modules/modules/class-skills-module.php');
  [
    "'skills_bar_color'",
    'var(--color-primary) 0%, var(--color-accent) 100%',
  ].forEach((needle) => {
    assertContains(skillsPhp, needle, `Skills module design token inheritance contract changed unexpectedly: ${needle}`);
  });

  const footerSuitePhp = readThemeFile('inc/modules/modules/class-footer-suite-module.php');
  [
    "'qfs_accent_color'",
    'var(--color-accent)',
    'inherit_footer_color_value',
    'var(--qiling-component-footer-text)',
    'var(--qiling-component-footer-heading)',
    'var(--qiling-component-footer-link)',
  ].forEach((needle) => {
    assertContains(footerSuitePhp, needle, `Footer suite design token inheritance contract changed unexpectedly: ${needle}`);
  });

  const processPhp = readThemeFile('inc/modules/modules/class-process-module.php');
  assertContains(
    processPhp,
    'linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%)',
    'Process module should inherit brand/accent gradient defaults'
  );

  const pricingPhp = readThemeFile('inc/modules/modules/class-pricing-module.php');
  [
    'var(--color-success)',
    'var(--color-error)',
    'var(--color-accent)',
  ].forEach((needle) => {
    assertContains(pricingPhp, needle, `Pricing module design token contract changed unexpectedly: ${needle}`);
  });

  const promotionPhp = readThemeFile('inc/modules/modules/class-promotion-module.php');
  [
    'var(--color-error)',
    'var(--color-success)',
    'var(--color-warning)',
  ].forEach((needle) => {
    assertContains(promotionPhp, needle, `Promotion module design token contract changed unexpectedly: ${needle}`);
  });

  const tickerPhp = readThemeFile('inc/modules/modules/class-breaking-news-ticker-module.php');
  assertContains(
    tickerPhp,
    'var(--color-error)',
    'Breaking news ticker should default to the global error token'
  );

  const featuresPhp = readThemeFile('inc/modules/modules/class-features-module.php');
  assertContains(
    featuresPhp,
    'var(--color-primary)',
    'Features module icon fallback should continue to use the global primary token'
  );

  const mainCss = readThemeFile('assets/css/main.css');
  [
    '--color-primary-hover',
    '--color-success',
    '--color-success-rgb',
    '--color-neutral-900',
    '--color-neutral-400-rgb',
    '--dm-bg',
    '--qiling-gradient-brand',
    '--qiling-body-font-size-desktop',
    '--qiling-h1-font-size-desktop',
    '--qiling-container-width-tablet',
    '--qiling-grid-gap-desktop',
    '--qiling-component-header-phone-bg',
    '--qiling-component-header-phone-transparent-bg',
    '[data-theme="dark"]',
  ].forEach((needle) => {
    assertContains(mainCss, needle, `Main CSS fallback token contract changed unexpectedly: ${needle}`);
  });

  const modulesCss = readThemeFile('assets/css/modules.css');
  [
    'var(--qiling-gradient-brand)',
    'var(--qiling-gradient-accent)',
    'var(--qiling-gradient-success)',
    'var(--qiling-gradient-info)',
    'var(--qiling-gradient-error)',
    'var(--qiling-gradient-warning)',
    'var(--pet-primary, var(--color-accent))',
    'var(--qfs-accent, var(--color-accent))',
    '@qiling-design-surface-bridge',
    '--qiling-component-card-bg',
    '--qiling-component-post-card-bg',
    '--qiling-component-button-bg',
    '--qiling-component-form-input-bg',
    '--qiling-component-badge-bg',
    '--qiling-component-tabs-active-bg',
    'padding: var(--qiling-space-36) var(--qiling-space-24) var(--qiling-space-28);',
    'top: 72px;',
    'var(--color-warning)',
    'rgba(var(--color-success-rgb), 0.12)',
    'rgba(var(--color-error-rgb), 0.4)',
    'rgba(var(--color-accent-rgb), 0.12)',
    'rgba(var(--color-neutral-400-rgb), 0.18)',
  ].forEach((needle) => {
    assertContains(modulesCss, needle, `Modules CSS tokenized gradient contract changed unexpectedly: ${needle}`);
  });

  const tokenCoverageTargets = [
    'assets/css/modules.css',
    'assets/css/blog-presets.css',
    'assets/css/blog-presets-minimal.css',
    'assets/css/blog-presets-artist.css',
    'assets/css/modules-hero.css',
    'assets/css/frontend-builder.css',
    'assets/css/main.css',
    'assets/css/woocommerce.css',
    'assets/css/article-enhance.css',
    'templates/template-account.php',
    'assets/css/login-modal.css',
    'assets/css/account.css',
    'assets/css/comments.css',
    'assets/css/sidebar.css',
    'assets/css/post-speech.css',
    'assets/css/mega-menu.css',
    'assets/css/submit-post.css',
    'assets/css/auth.css',
    'assets/css/category.css',
    'assets/css/software-intro.css',
    'assets/css/author-profile.css',
    'assets/css/faq.css',
    'templates/template-careers.php',
    'templates/template-contact.php',
    'templates/template-changelog.php',
    'assets/css/announcement.css',
    'assets/css/advanced-filter.css',
    'assets/css/features-showcase.css',
    'assets/css/left-nav.css',
    'assets/css/blog-page.css',
    'assets/css/about.css',
    'assets/css/admin-account-deletion.css',
    'assets/css/infinite-scroll.css',
    'assets/css/international-cookie-consent.css',
    'assets/css/search-captcha.css',
    'templates/template-forgot-password.php',
    'templates/template-about.php',
    'assets/css/resources.css',
    'templates/template-register.php',
    'assets/css/software-home.css',
    'assets/css/solutions.css',
    'templates/template-news.php',
    'templates/template-home.php',
    'templates/template-blog.php',
    'templates/template-topic.php',
    'templates/template-latest-posts.php',
    'templates/template-login.php',
    'templates/template-submit-post.php',
    'templates/template-resource-search.php',
    'templates/template-products.php',
    'templates/template-cases.php',
    'templates/template-landing.php',
    'templates/template-software-home.php',
    'templates/template-software-intro.php',
    'templates/template-features-showcase.php',
    'assets/css/resource-search.css',
    'assets/css/module-advanced-styles.css',
    'assets/css/reading-progress.css',
    'assets/css/lazy-image-placeholder.css',
    'template-parts/footer/privacy-banner.php',
    'template-parts/header/actions.php',
    'template-parts/blog/pagination.php',
    'template-parts/single/meta-stats.php',
    'inc/core/helpers/helpers-advanced-category-filter.php',
    'inc/core/helpers/helpers-changelog.php',
    'inc/core/helpers/helpers-debug-tools.php',
    'inc/core/class-login-modal.php',
    'inc/modules/modules/class-banner-module.php',
    'inc/modules/modules/class-compliance-trust-module.php',
    'inc/modules/modules/class-brand-banner-pro-module.php',
    'inc/core/class-module-advanced-style-service.php',
    'inc/core/helpers/helpers-front-protection.php',
    'inc/core/helpers/helpers-locale-notify.php',
    'inc/modules/modules/class-about-me-card-module.php',
    'inc/modules/modules/class-accordion-module.php',
    'inc/modules/modules/class-app-hero-module.php',
    'inc/modules/modules/class-author-matrix-module.php',
    'inc/modules/modules/class-blog-module.php',
    'inc/modules/modules/class-booking-entry-module.php',
    'inc/modules/modules/class-branches-module.php',
    'inc/modules/modules/class-breaking-news-ticker-module.php',
    'inc/modules/modules/class-cases-module.php',
    'inc/modules/modules/class-category-tabs-module.php',
    'inc/modules/modules/class-certificate-honors-module.php',
    'inc/modules/modules/class-chart-module.php',
    'inc/modules/modules/class-circle-wheel-module.php',
    'inc/modules/modules/class-clients-module.php',
    'inc/modules/modules/class-columns-module.php',
    'inc/modules/modules/class-comparison-module.php',
    'inc/modules/modules/class-contact-module.php',
    'inc/modules/modules/class-countdown-module.php',
    'inc/modules/modules/class-cta-module.php',
    'inc/modules/modules/class-curriculum-module.php',
    'inc/modules/modules/class-double-column-carousel-module.php',
    'inc/modules/modules/class-downloads-module.php',
    'inc/modules/modules/class-dynamic-banner-module.php',
    'inc/modules/modules/class-experience-timeline-module.php',
    'inc/modules/modules/class-faq-module.php',
    'inc/modules/modules/class-featured-posts-module.php',
    'inc/modules/modules/class-features-list-module.php',
    'inc/modules/modules/class-features-module.php',
    'inc/modules/modules/class-footer-suite-module.php',
    'inc/modules/modules/class-friendly-links-module.php',
    'inc/modules/modules/class-fullscreen-video-module.php',
    'inc/modules/modules/class-gallery-module.php',
    'inc/modules/modules/class-hero-search-module.php',
    'inc/modules/modules/class-hotel-amenities-module.php',
    'inc/modules/modules/class-image-comparison-module.php',
    'inc/modules/modules/class-image-text-module.php',
    'inc/modules/modules/class-interact-hero-module.php',
    'inc/modules/modules/class-itinerary-module.php',
    'inc/modules/modules/class-knowledge-cards-module.php',
    'inc/modules/modules/class-lookbook-module.php',
    'inc/modules/modules/class-magic-layout-module.php',
    'inc/modules/modules/class-main-category-content-module.php',
    'inc/modules/modules/class-media-list-module.php',
    'inc/modules/modules/class-menu-module.php',
    'inc/modules/modules/class-micro-journal-stream-module.php',
    'inc/modules/modules/class-multi-image-text-module.php',
    'inc/modules/modules/class-news-module.php',
    'inc/modules/modules/class-pet-profile-module.php',
    'inc/modules/modules/class-pricing-module.php',
    'inc/modules/modules/class-process-module.php',
    'inc/modules/modules/class-product-showcase-module.php',
    'inc/modules/modules/class-products-module.php',
    'inc/modules/modules/class-promotion-module.php',
    'inc/modules/modules/class-qiling-image-guide-module.php',
    'inc/modules/modules/class-qiling-shop-showcase-module.php',
    'inc/modules/modules/class-qiling-universal-recommend-module.php',
    'inc/modules/modules/class-query-loop-module.php',
    'inc/modules/modules/class-reader-wall-module.php',
    'inc/modules/modules/class-resource-hero-pro-module.php',
    'inc/modules/modules/class-resource-stats-module.php',
    'inc/modules/modules/class-resume-hero-module.php',
    'inc/modules/modules/class-room-showcase-module.php',
    'inc/modules/modules/class-service-cards-module.php',
    'inc/modules/modules/class-services-module.php',
    'inc/modules/modules/class-skills-module.php',
    'inc/modules/modules/class-software-carousel-module.php',
    'inc/modules/modules/class-software-category-module.php',
    'inc/modules/modules/class-software-ranking-module.php',
    'inc/modules/modules/class-stats-module.php',
    'inc/modules/modules/class-tabbed-carousel-module.php',
    'inc/modules/modules/class-tabs-module.php',
    'inc/modules/modules/class-team-module.php',
    'inc/modules/modules/class-testimonials-module.php',
    'inc/modules/modules/class-ticket-showcase-module.php',
    'inc/modules/modules/class-timeline-module.php',
    'inc/modules/modules/class-tour-package-module.php',
    'inc/modules/modules/class-video-module.php',
    'inc/modules/modules/class-video-portal-hero-module.php',
    'inc/modules/modules/class-visual-tabs-module.php',
    'inc/modules/modules/class-work-detail-module.php',
    'inc/modules/modules/class-work-library-module.php',
  ];
  const highRiskHeroSplitTargets = [
    'assets/css/modules-split/app_hero.css',
    'assets/css/modules-split/brand_banner_pro.css',
    'assets/css/modules-split/dynamic_banner.css',
    'assets/css/modules-split/fullscreen_video.css',
    'assets/css/modules-split/hero_search.css',
    'assets/css/modules-split/interact_hero.css',
    'assets/css/modules-split/qiling_main_category_content.css',
    'assets/css/modules-split/qiling_video_portal_hero.css',
    'assets/css/modules-split/resource_hero_pro.css',
    'assets/css/modules-split/resume_hero.css',
  ];
  const highRiskBusinessSplitTargets = [
    'assets/css/modules-split/product_showcase.css',
    'assets/css/modules-split/products.css',
    'assets/css/modules-split/qiling_shop_showcase.css',
    'assets/css/modules-split/qiling_universal_recommend.css',
    'assets/css/modules-split/resource_stats.css',
    'assets/css/modules-split/software_carousel.css',
    'assets/css/modules-split/software_category.css',
    'assets/css/modules-split/software_ranking.css',
    'assets/css/modules-split/work_detail.css',
    'assets/css/modules-split/work_library.css',
  ];
  const highRiskContentSplitTargets = [
    'assets/css/modules-split/blog.css',
    'assets/css/modules-split/breaking_news_ticker.css',
    'assets/css/modules-split/featured_posts.css',
    'assets/css/modules-split/media_list.css',
    'assets/css/modules-split/micro_journal_stream.css',
    'assets/css/modules-split/news.css',
    'assets/css/modules-split/query_loop.css',
    'assets/css/modules-split/reader_wall.css',
  ];
  const highRiskInteractiveSplitTargets = [
    'assets/css/modules-split/accordion.css',
    'assets/css/modules-split/booking-entry.css',
    'assets/css/modules-split/category_tabs.css',
    'assets/css/modules-split/circle_wheel.css',
    'assets/css/modules-split/double_column_carousel.css',
    'assets/css/modules-split/gallery.css',
    'assets/css/modules-split/image_comparison.css',
    'assets/css/modules-split/itinerary.css',
    'assets/css/modules-split/lookbook.css',
    'assets/css/modules-split/magic_layout.css',
    'assets/css/modules-split/room-showcase.css',
    'assets/css/modules-split/tabbed_carousel.css',
    'assets/css/modules-split/tabs.css',
    'assets/css/modules-split/testimonials.css',
    'assets/css/modules-split/ticket-showcase.css',
    'assets/css/modules-split/tour-package.css',
    'assets/css/modules-split/visual_tabs.css',
  ];
  const lowFrequencySplitTargets = [
    'assets/css/modules-split/about_me_card.css',
    'assets/css/modules-split/author_matrix.css',
    'assets/css/modules-split/banner.css',
    'assets/css/modules-split/branches.css',
    'assets/css/modules-split/cases.css',
    'assets/css/modules-split/certificate_honors.css',
    'assets/css/modules-split/chart.css',
    'assets/css/modules-split/clients.css',
    'assets/css/modules-split/columns.css',
    'assets/css/modules-split/comparison.css',
    'assets/css/modules-split/compliance_trust.css',
    'assets/css/modules-split/contact.css',
    'assets/css/modules-split/countdown.css',
    'assets/css/modules-split/cta.css',
    'assets/css/modules-split/curriculum.css',
    'assets/css/modules-split/downloads.css',
    'assets/css/modules-split/experience_timeline.css',
    'assets/css/modules-split/faq.css',
    'assets/css/modules-split/features.css',
    'assets/css/modules-split/features_list.css',
    'assets/css/modules-split/footer_suite.css',
    'assets/css/modules-split/friendly_links.css',
    'assets/css/modules-split/hotel-amenities.css',
    'assets/css/modules-split/image_text.css',
    'assets/css/modules-split/knowledge_cards.css',
    'assets/css/modules-split/menu.css',
    'assets/css/modules-split/multi_image_text.css',
    'assets/css/modules-split/pet_profile.css',
    'assets/css/modules-split/pricing.css',
    'assets/css/modules-split/process.css',
    'assets/css/modules-split/promotion.css',
    'assets/css/modules-split/qiling_image_guide.css',
    'assets/css/modules-split/service_cards.css',
    'assets/css/modules-split/services.css',
    'assets/css/modules-split/skills.css',
    'assets/css/modules-split/stats.css',
    'assets/css/modules-split/team.css',
    'assets/css/modules-split/timeline.css',
    'assets/css/modules-split/video.css',
  ];
  const requiredSplitTargets = [
    ...highRiskHeroSplitTargets,
    ...highRiskBusinessSplitTargets,
    ...highRiskContentSplitTargets,
    ...highRiskInteractiveSplitTargets,
    ...lowFrequencySplitTargets,
  ];
  const splitCssTargets = readdirSync(themePath('assets/css/modules-split'))
    .filter((file) => file.endsWith('.css'))
    .sort()
    .map((file) => `assets/css/modules-split/${file}`);
  if (!splitCssTargets.includes('assets/css/modules-split/_shared.css')) {
    throw new Error('Modules split shared CSS must stay covered by design-token smoke tests');
  }
  requiredSplitTargets.forEach((file) => {
    if (!splitCssTargets.includes(file)) {
      throw new Error(`Required split CSS must stay generated and token-covered: ${file}`);
    }
  });

  const colorLiteralPattern = /#(?:[0-9a-f]{3}|[0-9a-f]{6})\b|(?:rgba?|hsla?)\([^)]*\)/gi;
  const sizeLiteralPattern = /\b(font-size|line-height|letter-spacing|max-width|min-width|gap|column-gap|row-gap|padding(?:-[a-z]+)?|margin(?:-[a-z]+)?)\s*:\s*([^;]+)/gi;
  for (const file of [...tokenCoverageTargets, ...splitCssTargets]) {
    let colors = 0;
    let sizes = 0;
    readThemeFile(file).split(/\r?\n/).forEach((line) => {
      colorLiteralPattern.lastIndex = 0;
      for (const match of line.matchAll(colorLiteralPattern)) {
        if (!match[0].includes('var(')) {
          colors += 1;
        }
      }

      if (/^@(media|container|supports)\b/i.test(line.trim())) {
        return;
      }

      sizeLiteralPattern.lastIndex = 0;
      for (const match of line.matchAll(sizeLiteralPattern)) {
        const rawValue = (match[2] || '').trim();
        if (
          rawValue &&
          !/(?:var|clamp|calc|min|max)\(/i.test(rawValue) &&
          /-?(?:\d+|\d*\.\d+)(?:px|rem|em|%|vh|vw)/i.test(rawValue)
        ) {
          sizes += 1;
        }
      }
    });

    if (colors + sizes > 0) {
      throw new Error(`Top design-token coverage target still has hardcoded literals: ${file} (${colors} colors, ${sizes} sizes)`);
    }
  }

  const auditorPhp = readThemeFile('inc/core/class-page-performance-a11y-auditor.php');
  [
    'build_design_system_diagnostics',
    'resolve_diagnostic_color_value',
    'contains_literal_design_color',
    'is_component_color_like_definition',
    'blend_rgb_over_background',
  ].forEach((needle) => {
    assertContains(auditorPhp, needle, `Design diagnostics contract changed unexpectedly: ${needle}`);
  });

  const adminCss = readThemeFile('assets/css/admin.css');
  [
    '.ds-settings-wrap .ds-design-workbench',
    '.ds-settings-wrap .ds-design-workbench__inheritance',
    '.ds-settings-wrap .ds-design-workbench__canvas',
    '.ds-settings-wrap .ds-design-workbench__diagnostics',
    'tr[id^="setting-row-design_"] input.small-text',
    '.ds-settings-wrap .wp-picker-container .wp-color-result.button',
    '.ds-settings-wrap .wp-picker-container .wp-color-result-text',
  ].forEach((needle) => {
    assertContains(adminCss, needle, `Admin workbench CSS contract changed unexpectedly: ${needle}`);
  });

  const packageJson = readThemeFile('dev/package.json');
  [
    '"audit:design": "node ./tools/audit-design-token-coverage.mjs"',
    '"audit:modules-design": "node ./tools/audit-module-design-surfaces.mjs"',
    '"audit:customization": "node ./tools/audit-customization-readiness.mjs"',
    '"check:eol": "node ./tools/check-eol.mjs"',
    '"fix:eol": "node ./tools/check-eol.mjs --fix"',
    '"check": "npm run check:eol && npm run check:js && npm run audit:customization && npm run test:smoke"',
  ].forEach((needle) => {
    assertContains(packageJson, needle, `Design audit script must stay exposed through package.json: ${needle}`);
  });

  const customizationAudit = readThemeFile('dev/tools/audit-customization-readiness.mjs');
  [
    'runModuleSurfaceAudit',
    'assertPageOverrideNetwork',
    'assertAdminOperatorFlow',
    'audit-module-design-surfaces.mjs',
    'partial=',
    'render_settings_recommended_action',
    'render_header_settings_governance_field',
    'render_footer_settings_governance_field',
    '支持关键词搜索，仅查当前选项卡',
    'Transition-era hidden settings UX',
    'resetPageDesignGroup',
    'persist_page_design_overrides',
  ].forEach((needle) => {
    assertContains(customizationAudit, needle, `Customization readiness audit contract changed unexpectedly: ${needle}`);
  });

  const moduleDesignAudit = readThemeFile('dev/tools/audit-module-design-surfaces.mjs');
  [
    'commonModules',
    'roleStandards',
    'moduleScopeAliases',
    'selectorBelongsToModule',
    'stripCssVarFunctions',
    'sharedCssPath',
    'fixed_text',
    '--color-text-muted',
    '--qiling-component-card-bg',
    '--qiling-component-button-bg',
    '--qiling-component-form-input-bg',
    '--qiling-component-tabs-active-bg',
    '--qiling-component-footer-heading',
    '--qiling-component-footer-text',
    '--qiling-component-footer-link',
    '--color-text-inverse',
    '--color-accent',
    'contact',
    'service_cards',
    'footer_suite',
    '--strict',
  ].forEach((needle) => {
    assertContains(moduleDesignAudit, needle, `Module design surface audit contract changed unexpectedly: ${needle}`);
  });

  const splitScript = readThemeFile('tools/rebuild-modules-split.js');
  const splitManifest = readThemeFile('assets/css/modules-split/_manifest.txt');
  [
    'shouldPreserveStandaloneSplit',
    'ensureFinalNewline',
    'themeRelativePath',
    "path.relative(root, file).split(path.sep).join('/')",
    '@qiling-keep-standalone-split',
    'preserved=standalone',
    '@qiling-design-surface-bridge',
    'stale_removed=',
  ].forEach((needle) => {
    assertContains(splitScript, needle, `Modules split rebuild safety contract changed unexpectedly: ${needle}`);
  });
  [
    'source=assets/css/modules.css',
    'out_dir=assets/css/modules-split',
    'hero_source=assets/css/modules-hero.css',
    '_shared\tassets/css/modules-split/_shared.css',
  ].forEach((needle) => {
    assertContains(splitManifest, needle, `Modules split manifest should use portable theme-relative paths: ${needle}`);
  });
  requiredSplitTargets.forEach((file) => {
    assertContains(
      splitManifest,
      `\t${file}`,
      `Required split CSS must stay listed in the strict split manifest: ${file}`
    );
  });
  assertMatches(
    splitManifest,
    /^(?:(?!\/Users\/|[A-Za-z]:\\|Desktop\/001|启灵主题\/qiling).)*$/s,
    'Modules split manifest must not contain local absolute filesystem paths'
  );
}
