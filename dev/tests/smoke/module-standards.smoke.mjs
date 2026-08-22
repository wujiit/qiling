import {
  assertContains,
  assertFileExists,
  assertMatches,
  readThemeFile,
} from './_helpers.mjs';
import { readdirSync } from 'node:fs';
import { join } from 'node:path';

export const name = 'Module standards contracts';

export async function run() {
  [
    'inc/modules/class-module-standards.php',
    'inc/modules/module-registry.php',
    'inc/modules/class-module-manager.php',
    'inc/core/class-builder-data-service.php',
    'inc/core/class-frontend-builder-library-service.php',
    'inc/core/class-frontend-builder-modules-service.php',
    'inc/core/class-page-package-module-service.php',
    'inc/core/class-ai-decorator.php',
    'inc/core/ai/class-prompt-builder.php',
    'inc/core/class-category-tabs-ajax.php',
    'inc/modules/modules/class-category-tabs-module.php',
    'inc/modules/modules/class-resource-stats-module.php',
  ].forEach((file) => {
    assertFileExists(file, `Module standards chain missing required file: ${file}`);
  });

  const standardsPhp = readThemeFile('inc/modules/class-module-standards.php');
  [
    'class Module_Standards',
    'CATALOG_SCHEMA_VERSION',
    'MODULE_DATA_SCHEMA_VERSION',
    'get_metadata_taxonomy(',
    'get_industry_standards(',
    'get_industry_aliases(',
    'normalize_industry_key(',
    'normalize_industry_key_list(',
    'normalize_manifest_metadata(',
    'infer_module_metadata(',
    'merge_module_metadata(',
    'build_catalog_audit(',
    'metadataCompleteness',
    'catalogRole',
    'industryTags',
    'schemaTypes',
  ].forEach((needle) => {
    assertContains(standardsPhp, needle, `Module standards contract changed unexpectedly: ${needle}`);
  });

  const moduleDir = join(process.cwd(), '..', 'inc', 'modules', 'modules');
  const registeredIds = readdirSync(moduleDir)
    .filter((file) => /^class-.+-module\.php$/.test(file))
    .map((file) => readThemeFile(`inc/modules/modules/${file}`).match(/function\s+get_id\s*\(\s*\)[\s\S]*?return\s+['"]([^'"]+)['"]/i)?.[1] || '')
    .filter(Boolean)
    .sort();
  const capabilityIds = Array.from(standardsPhp.matchAll(/'([a-z0-9_-]+)'\s*=>\s*'[01]{5}'/g), (match) => match[1]).sort();
  if (registeredIds.length !== 86 || capabilityIds.length !== 86 || registeredIds.join('\n') !== capabilityIds.join('\n')) {
    throw new Error(`All 86 rendered modules must have one explicit design capability row (registered ${registeredIds.length}, capabilities ${capabilityIds.length})`);
  }

  const visualStylePhp = readThemeFile('inc/core/class-module-visual-style-service.php');
  const advancedStylePhp = readThemeFile('inc/core/class-module-advanced-style-service.php');
  const builderModulesPhpForCapabilities = readThemeFile('inc/core/class-frontend-builder-modules-service.php');
  const builderJs = readThemeFile('assets/js/frontend-builder.js');
  [
    'Module_Standards::get_design_capabilities( $module_id )',
    "unset( $groups['buttons'] )",
    "unset( $groups['cards'] )",
  ].forEach((needle) => assertContains(visualStylePhp, needle, `Module visual capability filtering changed unexpectedly: ${needle}`));
  assertContains(advancedStylePhp, "'capabilities' =>", 'Advanced style schema must expose module capabilities');
  assertContains(builderModulesPhpForCapabilities, 'is_legacy_common_style_field_id( $field_id )', 'Frontend builder must hide migrated legacy common style fields');
  assertContains(builderJs, 'if (capabilities.buttons)', 'Frontend builder must conditionally render action-button typography');
  assertContains(builderJs, 'getModuleVisualAdvancedFields(field && field.groups ? field.groups : {})', 'Frontend builder must render only server-declared visual groups');

  const advancedCss = readThemeFile('assets/css/module-advanced-styles.css');
  assertMatches(advancedCss, /^(?:(?!\[class\*="-btn"\]).)*$/s, 'Module styling must not target functional controls through a broad -btn class substring');
  assertMatches(advancedCss, /^(?:(?!button\[type="submit"\]).)*$/s, 'Module styling must not capture arbitrary plugin submit buttons');
  assertMatches(advancedCss, /^(?:(?!--qds-button-color\)\s*!important).)*$/s, 'Button typography must not force an unset color variable');
  [
    'data-qds-button-color="1"',
    'data-qds-button-hover-color="1"',
    'data-qds-button-size="1"',
    'data-qds-button-weight="1"',
    'data-qds-button-line-height="1"',
  ].forEach((needle) => assertContains(advancedCss, needle, `Advanced button property state is missing: ${needle}`));
  [
    "$vars['--qiling-module-button-text'] = $role_color;",
    "$vars['--qiling-component-button-text'] = $role_color;",
    "$vars['--qiling-module-button-hover-text'] = $button_hover_color;",
  ].forEach((needle) => assertContains(advancedStylePhp, needle, `Advanced button color must flow through module variables: ${needle}`));

  const contactModulePhp = readThemeFile('inc/modules/modules/class-contact-module.php');
  const contactFormHelperPhp = readThemeFile('inc/core/helpers/helpers-contact-form.php');
  const contactCss = readThemeFile('assets/css/modules-split/contact.css');
  [
    'contact_submit_text',
    'contact_submit_bg_color',
    'contact_submit_text_color',
    'contact_submit_border_color',
    'contact_submit_hover_bg_color',
    'contact_submit_hover_text_color',
    'contact_submit_hover_border_color',
    "'submit_class' => 'btn-submit contact-submit-btn'",
  ].forEach((needle) => assertContains(contactModulePhp, needle, `Built-in contact submit customization chain is missing: ${needle}`));
  assertContains(contactFormHelperPhp, "'submit_class' => 'btn-submit'", 'Built-in contact form must support an explicit semantic submit class');
  [
    '.module-contact.module-contact .contact-form .contact-submit-btn',
    '--contact-submit-bg',
    '--contact-submit-text',
    '--contact-submit-hover-bg',
    '--contact-submit-hover-text',
  ].forEach((needle) => assertContains(contactCss, needle, `Built-in contact submit CSS chain is missing: ${needle}`));
  assertMatches(contactCss, /^(?:(?!\.developer-form-wrap\s+\.contact-submit-btn).)*$/s, 'Contact private button settings must not target the Qiling Form plugin branch');

  const buttonContracts = [
    ['assets/css/modules-split/blog.css', '--blog-read-more-bg'],
    ['assets/css/modules-split/category_tabs.css', '--category-tabs-more-bg'],
    ['assets/css/modules-split/qiling_universal_recommend.css', '--qur-more-btn-bg'],
    ['assets/css/modules-split/qiling_main_category_content.css', '--qmcc-more-btn-border'],
    ['assets/css/modules-split/app_hero.css', '--app-btn-border'],
    ['assets/css/modules-split/compliance_trust.css', '--ct-btn-border'],
  ];
  buttonContracts.forEach(([file, cssVariable]) => {
    assertContains(readThemeFile(file), `var(${cssVariable}`, `Module-specific button variable is not consumed: ${file} ${cssVariable}`);
  });

  const moduleManagerPhp = readThemeFile('inc/modules/class-module-manager.php');
  [
    'Module_Standards::normalize_manifest_metadata',
    'Module_Standards::infer_module_metadata',
    'Module_Standards::merge_module_metadata',
    'get_module_metadata_taxonomy(',
    'get_module_catalog_audit(',
  ].forEach((needle) => {
    assertContains(moduleManagerPhp, needle, `Module manager v2 catalog contract changed unexpectedly: ${needle}`);
  });

  const builderDataServicePhp = readThemeFile('inc/core/class-builder-data-service.php');
  [
    'MODULE_DATA_SCHEMA_VERSION',
    'get_module_data_schema_version(',
    'migrate_module_data(',
    'developer_starter_migrate_module_data',
    'get_builtin_module_data_schema_map(',
    'sanitize_select_value(',
    'normalize_select_values_for_editor(',
    'normalize_field_options( $field[\'options\'], $field )',
    "Numeric-looking option keys like '0'/'1' become integers in PHP",
    'sanitize_css_color_value(',
    "'schemaVersion' =>",
  ].forEach((needle) => {
    assertContains(builderDataServicePhp, needle, `Builder data standards contract changed unexpectedly: ${needle}`);
  });

  const registryPhp = readThemeFile('inc/modules/module-registry.php');
  [
    'industry_tags',
    'page_tags',
    'intent_tags',
    'content_models',
    'schema_types',
    'ai_hints',
  ].forEach((needle) => {
    assertContains(registryPhp, needle, `Default module registry metadata contract changed unexpectedly: ${needle}`);
  });

  const builderLibraryPhp = readThemeFile('inc/core/class-frontend-builder-library-service.php');
  [
    'catalogSchemaVersion',
    'industryTags',
    'catalogRole',
    'metadataSource',
    'metadataCompleteness',
    'pageTags',
    'intentTags',
    'contentModels',
    'schemaTypes',
    'aiHints',
  ].forEach((needle) => {
    assertContains(builderLibraryPhp, needle, `Frontend builder catalog metadata contract changed unexpectedly: ${needle}`);
  });

  const builderModulesPhp = readThemeFile('inc/core/class-frontend-builder-modules-service.php');
  [
    'dataSchema',
    'dataSchemaVersion',
    'build_module_data_schema_map(',
    'get_module_data_schema_version(',
  ].forEach((needle) => {
    assertContains(builderModulesPhp, needle, `Frontend builder schema payload contract changed unexpectedly: ${needle}`);
  });

  const pagePackageModulesPhp = readThemeFile('inc/core/class-page-package-module-service.php');
  [
    'get_builder_data_service(',
    'migrate_module_data(',
    "'schemaVersion' =>",
    'build_module_data_schema_map(',
  ].forEach((needle) => {
    assertContains(pagePackageModulesPhp, needle, `Page package module standards contract changed unexpectedly: ${needle}`);
  });

  const aiDecoratorPhp = readThemeFile('inc/core/class-ai-decorator.php');
  [
    'build_module_prompt_metadata(',
    "'metadata'    =>",
    'metadataCompleteness',
    'class-module-standards.php',
    'module-registry.php',
  ].forEach((needle) => {
    assertContains(aiDecoratorPhp, needle, `AI module metadata contract changed unexpectedly: ${needle}`);
  });

  const promptBuilderPhp = readThemeFile('inc/core/ai/class-prompt-builder.php');
  [
    "'metadata'    => isset( $current_module_schema['metadata'] )",
    "'metadata'  => isset( $module_schema['metadata'] )",
  ].forEach((needle) => {
    assertContains(promptBuilderPhp, needle, `AI prompt metadata contract changed unexpectedly: ${needle}`);
  });

  const categoryTabsAjaxPhp = readThemeFile('inc/core/class-category-tabs-ajax.php');
  [
    "add_action( 'wp_ajax_ds_load_category_tabs_posts', array( $this, 'load_posts' ) );",
    "add_action( 'wp_ajax_nopriv_ds_load_category_tabs_posts', array( $this, 'load_posts' ) );",
    '公开读取分类 Tabs 文章列表。',
    '不把 nonce 当成权限边界',
    'private function is_rate_limited()',
    'ds_cat_tabs_rate_',
    'developer_starter_category_tabs_rate_limit_max',
    'sanitize_config(',
    'build_response_cache_key(',
    'ds_cat_tabs_resp_',
    'developer_starter_category_tabs_response_cache_ttl',
  ].forEach((needle) => {
    assertContains(categoryTabsAjaxPhp, needle, `Category tabs public-read AJAX contract changed unexpectedly: ${needle}`);
  });
  [
    'ds_get_category_tabs_nonce',
    'ds_category_tabs_nonce',
    'developer_starter_category_tabs_require_guest_nonce',
    'wp_verify_nonce',
    'wp_create_nonce',
    'invalid_nonce',
  ].forEach((needle) => {
    assertMatches(
      categoryTabsAjaxPhp,
      new RegExp(`^(?:(?!${needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}).)*$`, 's'),
      `Category tabs AJAX should stay a public read endpoint without nonce refresh/protection: ${needle}`
    );
  });

  const categoryTabsModulePhp = readThemeFile('inc/modules/modules/class-category-tabs-module.php');
  [
    'ds_get_category_tabs_nonce',
    'ds_category_tabs_nonce',
    'invalid_nonce',
    'refreshNonceAndRetry',
    'isRefreshingNonce',
    'nonce: config.nonce',
  ].forEach((needle) => {
    assertMatches(
      categoryTabsModulePhp,
      new RegExp(`^(?:(?!${needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}).)*$`, 's'),
      `Category tabs frontend should not request or refresh public nonces: ${needle}`
    );
  });

  const resourceStatsModulePhp = readThemeFile('inc/modules/modules/class-resource-stats-module.php');
  [
    'private function get_allowed_qilingshop_user_info_tables( $wpdb )',
    'private function quote_table_identifier( $table_name )',
    "preg_match( '/^[A-Za-z0-9_]+$/', $table_name )",
    "return '`' . $table_name . '`';",
    'private function resolve_qilingshop_user_info_table( $wpdb )',
    'private function table_exists( $wpdb, $table_name )',
    "$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )",
    '$quoted_table = $this->quote_table_identifier( $table_name );',
    'SELECT COUNT(*) FROM {$quoted_table} WHERE `vip_level` > %d AND `vip_expires` > %s',
  ].forEach((needle) => {
    assertContains(resourceStatsModulePhp, needle, `Resource stats SQL hardening contract changed unexpectedly: ${needle}`);
  });
  [
    'SHOW TABLES LIKE \'$table_name\'',
    'FROM $table_name',
  ].forEach((needle) => {
    assertMatches(
      resourceStatsModulePhp,
      new RegExp(`^(?:(?!${needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}).)*$`, 's'),
      `Resource stats module should not interpolate raw table names into SQL: ${needle}`
    );
  });

  const ctaModulePhp = readThemeFile('inc/modules/modules/class-cta-module.php');
  [
    'cta_button_bg_color',
    'cta_button_text_color',
    'cta_button_hover_bg_color',
    'cta_button_hover_text_color',
    '--qiling-cta-button-bg',
  ].forEach((needle) => {
    assertContains(ctaModulePhp, needle, `CTA module button override contract changed unexpectedly: ${needle}`);
  });

  const modulesCss = readThemeFile('assets/css/modules.css');
  [
    '--qiling-cta-button-bg',
    '--qiling-cta-button-text',
    '--qiling-cta-button-hover-bg',
    '--qiling-cta-button-hover-text',
  ].forEach((needle) => {
    assertContains(modulesCss, needle, `CTA module button CSS override contract changed unexpectedly: ${needle}`);
  });
}
