import {
  assertContains,
  assertFileExists,
  assertMatches,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Frontend builder contracts';

export async function run() {
  [
    'inc/core/class-frontend-builder.php',
    'inc/core/class-builder-data-service.php',
    'inc/core/class-frontend-builder-assets-service.php',
    'inc/core/class-frontend-builder-library-service.php',
    'inc/core/class-frontend-builder-modules-service.php',
    'inc/core/class-frontend-builder-qilingshop-service.php',
    'inc/core/class-design-tokens.php',
    'inc/core/class-template-manager.php',
    'assets/js/frontend-builder.js',
    'assets/js/ai-builder-service.js',
    'inc/admin/class-meta-boxes.php',
  ].forEach((file) => {
    assertFileExists(file, `Builder chain missing required file: ${file}`);
  });

  const frontendBuilderPhp = readThemeFile('inc/core/class-frontend-builder.php');
  [
    "add_action( 'wp_ajax_qiling_frontend_builder_render_module_preview', array( $this, 'ajax_render_module_preview' ) );",
    "add_action( 'wp_ajax_qiling_frontend_builder_render_preview', array( $this, 'ajax_render_preview' ) );",
    "add_action( 'wp_ajax_qiling_frontend_builder_save_modules', array( $this, 'ajax_save_modules' ) );",
    "add_action( 'wp_ajax_qiling_frontend_builder_get_snapshots', array( $this, 'ajax_get_snapshots' ) );",
    "add_action( 'wp_ajax_qiling_frontend_builder_restore_snapshot', array( $this, 'ajax_restore_snapshot' ) );",
    'MAX_BUILDER_PAYLOAD_BYTES',
    'MAX_BUILDER_MODULES',
    'MAX_BUILDER_MODULE_DATA_BYTES',
    'validate_builder_modules_payload(',
    'ajax_restore_snapshot(',
    "'qiling-ai-builder-service'",
    "'designSystem'     => $design_system",
    "'limits'           => array(",
    'id="qfb-preview-tools"',
    'id="qfb-design-summary"',
    'id="qfb-snapshots-toggle"',
    'id="qfb-snapshots-panel"',
    'id="qfb-library-filters"',
    "normalize_modules_for_storage(",
    "build_module_data_schema_map(",
    'get_assets_service()',
    'get_library_service()',
    'get_modules_service()',
    'get_qilingshop_service()',
  ].forEach((needle) => {
    assertContains(frontendBuilderPhp, needle, `Frontend builder PHP contract changed unexpectedly: ${needle}`);
  });

  const builderDataServicePhp = readThemeFile('inc/core/class-builder-data-service.php');
  [
    'class Builder_Data_Service',
    'sanitize_module_data(',
    'normalize_modules_for_storage(',
    'build_module_data_schema_map(',
    'prepare_module_data_for_editor(',
    'normalize_select_values_for_editor(',
    'migrate_banner_module_data(',
    'normalize_banner_stats_items(',
    'get_legacy_common_style_field_ids(',
  ].forEach((needle) => {
    assertContains(builderDataServicePhp, needle, `Builder data service contract changed unexpectedly: ${needle}`);
  });

  const bannerModulePhp = readThemeFile('inc/modules/modules/class-banner-module.php');
  [
    "'title_align'",
    'get_slide_title_align(',
    'text-align: <?php echo esc_attr( $title_align ); ?>;',
    'get_stats_bar_items(',
    "'stats_data', 'stats_items', 'items'",
    'is_stats_bar_enabled(',
  ].forEach((needle) => {
    assertContains(bannerModulePhp, needle, `Banner stats bar compatibility changed unexpectedly: ${needle}`);
  });

  const heroCss = readThemeFile('assets/css/modules-hero.css');
  [
    '.module-banner .banner-title',
    '.app-hero-content .hero-title',
    '.module-dynamic-banner .db-title',
    '.module-dynamic-banner .db-subtitle',
    '.module-hero-search .hs-title',
    '.module-resume-hero .rh-name',
    '.module-fullscreen-video .fsv-title',
    'max-width: var(--qiling-measure-pct-100);',
    'overflow-wrap: normal;',
    'word-break: normal;',
    'text-wrap: pretty;',
  ].forEach((needle) => {
    assertContains(heroCss, needle, `Hero title layout CSS changed unexpectedly: ${needle}`);
  });

  [
    '.module-fullscreen-video .fsv-content',
    'width: min(calc(100% - 48px), 1100px);',
    'max-width: 100%;',
    'text-wrap: balance;',
  ].forEach((needle) => {
    assertContains(heroCss, needle, `Fullscreen video content width contract changed unexpectedly: ${needle}`);
  });

  const dynamicBannerPhp = readThemeFile('inc/modules/modules/class-dynamic-banner-module.php');
  [
    "'id'      => 'db_subtitle'",
    "'id'      => 'db_text_color'",
    "'id'      => 'db_desc'",
    "'id'      => 'db_desc_color'",
    '--db-subtitle-color:',
    '--db-desc-color:',
    'class="db-desc" style="color: <?php echo esc_attr( $desc_color ); ?>;"',
  ].forEach((needle) => {
    assertContains(dynamicBannerPhp, needle, `Dynamic Banner text color contract changed unexpectedly: ${needle}`);
  });
  [
    'color: var(--db-subtitle-color);',
    'color: var(--db-desc-color);',
  ].forEach((needle) => {
    assertContains(heroCss, needle, `Dynamic Banner subtitle/description CSS contract changed unexpectedly: ${needle}`);
  });

  const dynamicBannerMetaBoxesPhp = readThemeFile('inc/admin/class-meta-boxes.php');
  [
    "array( 'db_subtitle', 'db_text_color', 'db_desc', 'db_desc_color' )",
    'wp_cache_delete( $cache_key, $cache_group );',
    'delete_transient( $cache_key );',
  ].forEach((needle) => {
    assertContains(dynamicBannerMetaBoxesPhp, needle, `Dynamic Banner frontend schema cache contract changed unexpectedly: ${needle}`);
  });

  const blogPresetCss = [
    'assets/css/blog-presets.css',
    'assets/css/blog-presets-minimal.css',
    'assets/css/blog-presets-artist.css',
  ].map(readThemeFile).join('\n');
  [
    'body.qiling-blog-preset-developer .category-title',
    'body.qiling-blog-preset-developer .single-post-title',
    'body.qiling-blog-preset-minimal .single-post-title',
    'body.qiling-blog-preset-minimal .category-title',
    'body.qiling-blog-preset-artist .single-post-title',
    'body.qiling-blog-preset-artist .category-title',
    'max-width: min(var(--qiling-measure-pct-100), var(--qiling-measure-960));',
    'max-width: min(var(--qiling-measure-pct-100), var(--qiling-measure-900));',
    'overflow-wrap: normal;',
    'word-break: normal;',
    'text-wrap: pretty;',
  ].forEach((needle) => {
    assertContains(blogPresetCss, needle, `Blog preset title layout CSS changed unexpectedly: ${needle}`);
  });

  const frontendBuilderAssetsService = readThemeFile('inc/core/class-frontend-builder-assets-service.php');
  [
    'class Frontend_Builder_Assets_Service',
    'get_external_asset_urls(',
    'get_external_asset_versions(',
    'developer_starter_get_third_party_asset( $asset_key, $context_filter )',
    'get_module_dependencies(',
    'get_required_external_assets_for_modules(',
  ].forEach((needle) => {
    assertContains(frontendBuilderAssetsService, needle, `Frontend builder assets service contract changed unexpectedly: ${needle}`);
  });

  [
    '$external_asset_versions',
    '$swiper_asset_version',
    '$chart_asset_version',
  ].forEach((needle) => {
    assertContains(frontendBuilderPhp, needle, `Frontend builder external asset version contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    frontendBuilderPhp,
    /^(?:(?!wp_enqueue_script\(\s*'chart-js'[^\n]*null).)*$/s,
    'Frontend builder must enqueue Chart.js with a fixed version instead of null'
  );

  const frontendBuilderLibraryService = readThemeFile('inc/core/class-frontend-builder-library-service.php');
  [
    'class Frontend_Builder_Library_Service',
    'get_available_modules(',
    'get_my_library_templates(',
    'get_my_library_template_detail(',
    'filter_templates_by_source(',
    'Template_Manager::build_visible_template_query_args(',
    'Template_Manager::current_user_can_access_template_post(',
  ].forEach((needle) => {
    assertContains(frontendBuilderLibraryService, needle, `Frontend builder library service contract changed unexpectedly: ${needle}`);
  });

  const templateManagerPhp = readThemeFile('inc/core/class-template-manager.php');
  [
    'class Template_Manager',
    "const POST_TYPE = 'ql_module_template';",
    'build_visible_template_query_args(',
    'current_user_can_access_template_post(',
    "'post_author'  => get_current_user_id(),",
    'self::get_template_post( $id )',
  ].forEach((needle) => {
    assertContains(templateManagerPhp, needle, `Template manager contract changed unexpectedly: ${needle}`);
  });

  const frontendBuilderModulesService = readThemeFile('inc/core/class-frontend-builder-modules-service.php');
  [
    'class Frontend_Builder_Modules_Service',
    'build_module_schema_payload(',
    'normalize_modules_for_storage(',
    'sanitize_module_data_for_preview(',
    'build_module_preview_html(',
    'prepare_module_data_for_editor(',
    'legacyCommonStyleFieldIds',
    'module_has_native_button_controls(',
    "$design_capabilities['buttons'] = false;",
    'sort_builder_module_fields(',
    'builderGroupLabel',
    "get_builder_field_group_label(",
  ].forEach((needle) => {
    assertContains(frontendBuilderModulesService, needle, `Frontend builder modules service contract changed unexpectedly: ${needle}`);
  });
  const frontendBuilderSingleCardJs = readThemeFile('assets/js/frontend-builder.js');
  [
    'qfb-module-settings-card',
    'qfb-module-settings-card__body',
    'html += renderFieldControl(field, value);',
    'if (fieldGroup !== currentFieldGroup)',
  ].forEach((needle) => {
    assertContains(frontendBuilderSingleCardJs, needle, `Frontend builder single module card contract changed unexpectedly: ${needle}`);
  });
  [
    "'placeholder'",
    "'rows'",
    "'add_button'",
    "'item_label'",
    "'item_title'",
  ].forEach((needle) => {
    assertContains(frontendBuilderModulesService, needle, `Frontend builder field metadata parity changed unexpectedly: ${needle}`);
  });
  assertMatches(
    frontendBuilderModulesService,
    /^(?:(?!is_legacy_common_style_field_id\( \$id \)).)*$/s,
    'Frontend builder must not hide module-declared legacy fields from the visual settings panel'
  );

  const frontendBuilderQilingShopService = readThemeFile('inc/core/class-frontend-builder-qilingshop-service.php');
  [
    'class Frontend_Builder_QilingShop_Service',
    'is_builder_available(',
    'is_builder_page(',
    'bootstrap_modules(',
    'persist_modules_for_source(',
  ].forEach((needle) => {
    assertContains(frontendBuilderQilingShopService, needle, `Frontend builder qilingshop service contract changed unexpectedly: ${needle}`);
  });

  const frontendBuilderSnapshotService = readThemeFile('inc/core/class-frontend-builder-snapshot-service.php');
  [
    'class Frontend_Builder_Snapshot_Service',
    'create_pre_save_snapshot(',
    'get_snapshot_summaries(',
    'get_snapshot(',
    'strip_sensitive_values(',
  ].forEach((needle) => {
    assertContains(frontendBuilderSnapshotService, needle, `Frontend builder snapshot service contract changed unexpectedly: ${needle}`);
  });

  const designTokensPhp = readThemeFile('inc/core/class-design-tokens.php');
  [
    'get_page_design_field_definitions(',
    "'typography' => array(",
    'get_empty_page_typography_overrides(',
    'build_options_with_page_design_overrides(',
  ].forEach((needle) => {
    assertContains(designTokensPhp, needle, `Design token page override contract changed unexpectedly: ${needle}`);
  });

  const frontendBuilderJs = readThemeFile('assets/js/frontend-builder.js');
  [
    "action: 'qiling_frontend_builder_render_preview'",
    "action: 'qiling_frontend_builder_render_module_preview'",
    "action: 'qiling_frontend_builder_save_modules'",
    "action: 'qiling_frontend_builder_get_snapshots'",
    "action: 'qiling_frontend_builder_restore_snapshot'",
    'validateModulesForTransport',
    'renderSnapshotsPanel',
    'restoreSnapshot',
    "render_module_preview",
    'window.QilingAiBuilderService',
    'renderLibraryFilters',
    'renderDesignSystemSummary',
    'renderDesignTokenPicker',
    'applyTokenChipValue',
    'normalizeBannerModuleData',
    'prepareModulesForTransport',
    'syncVisibleRepeatersToSelectedModuleData',
    'renderGovernanceCard',
    'renderPageTypographySection',
    'serializePageDesignForPackage',
    'renderAiPromptRecipesHtml',
    'renderAiPromptHistory',
    'renderAiModuleBundlesHtml',
    'renderAiReadiness',
    'renderAiReviewChecklistHtml',
    'setPreviewMode',
  ].forEach((needle) => {
    assertContains(frontendBuilderJs, needle, `Frontend builder JS contract changed unexpectedly: ${needle}`);
  });
  [
    'fieldDependencyMatches',
    'refreshFieldDependencies',
    'data-field-dependency',
    'data-sub-field-dependency',
    'data-item-data',
    'data-max-items',
    'switcherUsesNumeric',
    'switchUsesNumeric',
  ].forEach((needle) => {
    assertContains(frontendBuilderJs, needle, `Frontend builder field round-trip contract changed unexpectedly: ${needle}`);
  });

  const editableActionModules = {
    'inc/modules/modules/class-news-module.php': ['news_button_text', 'news_button_bg_color', 'news_button_text_color'],
    'inc/modules/modules/class-category-tabs-module.php': ['more_link_text', 'more_ajax_text', 'more_button_bg_color', 'more_button_text_color'],
    'inc/modules/modules/class-blog-module.php': ['blog_read_more_text', 'blog_read_more_bg_color', 'blog_read_more_text_color'],
    'inc/modules/modules/class-cases-module.php': ['cases_detail_text'],
    'inc/modules/modules/class-certificate-honors-module.php': ['ch_link_text'],
    'inc/modules/modules/class-media-list-module.php': ['ml_link_text'],
    'inc/modules/modules/class-micro-journal-stream-module.php': ['mjs_link_text'],
    'inc/modules/modules/class-about-me-card-module.php': ['about_website_text'],
    'inc/modules/modules/class-work-detail-module.php': ['wd_external_label'],
  };
  Object.entries(editableActionModules).forEach(([file, fields]) => {
    const source = readThemeFile(file);
    fields.forEach((field) => {
      assertContains(source, field, `Frontend builder action field missing from ${file}: ${field}`);
    });
  });

  const frontendBuilderCss = readThemeFile('assets/css/frontend-builder.css');
  [
    '.qfb-preview-bar',
    '.qfb-design-summary',
    '.qfb-snapshots-btn',
    '.qfb-snapshots-card',
    '.qfb-library-filters',
    '.qfb-token-picker',
    '.qfb-governance-card',
    '.qfb-page-typography-group',
    '.qfb-ai-design-context',
    '.qfb-ai-prompt-recipes',
    '.qfb-ai-prompt-history-card',
    '.qfb-ai-module-bundles',
    '.qfb-ai-readiness-card',
    '.qfb-ai-review-checklist',
    'qfb-preview-tablet',
    'qfb-preview-mobile',
  ].forEach((needle) => {
    assertContains(frontendBuilderCss, needle, `Frontend builder CSS contract changed unexpectedly: ${needle}`);
  });

  const metaBoxesPhp = readThemeFile('inc/admin/class-meta-boxes.php');
  [
    'Meta_Boxes_Module_Renderer',
    'Meta_Boxes_Editor_Service',
    'Meta_Boxes_Post_Settings_Service',
    'Meta_Boxes_Modules_Save_Service',
    'Meta_Boxes_Modules_View_Service',
    "'qiling-ai-builder-service'",
    'get_builder_data_service()',
    'get_single_page_package_service()',
    'Template_Manager::current_user_can_access_template_post',
  ].forEach((needle) => {
    assertContains(metaBoxesPhp, needle, `Meta Boxes shared builder contract changed unexpectedly: ${needle}`);
  });

  const singlePagePackagePhp = readThemeFile('inc/core/class-single-page-package-service.php');
  [
    'MAX_PACKAGE_BYTES',
    'MAX_MODULES_PER_PAGE',
    'MAX_MODULE_DATA_BYTES',
    'page_package_too_large',
    'page_package_too_many_modules',
    'page_package_module_too_large',
  ].forEach((needle) => {
    assertContains(singlePagePackagePhp, needle, `Single page package import limits changed unexpectedly: ${needle}`);
  });
}
