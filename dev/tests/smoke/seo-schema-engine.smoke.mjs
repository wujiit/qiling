import {
  assertContains,
  assertFileExists,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'SEO industry schema engine contracts';

export async function run() {
  [
    'inc/seo/class-industry-schema-engine.php',
    'inc/seo/class-seo-manager.php',
    'inc/admin/traits/class-admin-settings-config-trait.php',
    'inc/admin/traits/class-admin-settings-sanitize-trait.php',
  ].forEach((file) => {
    assertFileExists(file, `SEO schema engine chain missing required file: ${file}`);
  });

  const enginePhp = readThemeFile('inc/seo/class-industry-schema-engine.php');
  [
    'class Industry_Schema_Engine',
    "const OPTION_ENABLE           = 'schema_engine_enable'",
    "const OPTION_INDUSTRY_TYPE    = 'schema_industry_type'",
    "const OPTION_DEFAULT_CURRENCY = 'schema_default_currency'",
    'get_industry_choices(',
    'sanitize_options(',
    'get_json_ld(',
    'build_context(',
    'build_graph(',
    "'@graph'",
    'developer_starter_schema_engine_context',
    'developer_starter_schema_node',
    'developer_starter_schema_graph',
    'developer_starter_schema_payload',
    'extract_module_schema_entities(',
    'extract_faq_entities(',
    'apply_page_schema_override_to_graph(',
    'get_schema_diagnostics(',
    'Content_Model_Center::get_model_meta_key',
  ].forEach((needle) => {
    assertContains(enginePhp, needle, `Industry schema engine contract changed unexpectedly: ${needle}`);
  });

  [
    'Organization',
    'WebSite',
    'BreadcrumbList',
    'FAQPage',
    'Product',
    'Service',
    'Course',
    'Event',
    'LocalBusiness',
    'Review',
    'Article',
    'HowTo',
    'JobPosting',
    'BlogPosting',
    'NewsArticle',
  ].forEach((needle) => {
    assertContains(enginePhp, needle, `Expected schema type missing from industry schema engine: ${needle}`);
  });

  const seoManagerPhp = readThemeFile('inc/seo/class-seo-manager.php');
  [
    'Industry_Schema_Engine::get_instance()->get_json_ld()',
    'type="application/ld+json"',
    'has_seo_plugin()',
  ].forEach((needle) => {
    assertContains(seoManagerPhp, needle, `SEO manager schema output contract changed unexpectedly: ${needle}`);
  });

  const functionsPhp = readThemeFile('functions.php');
  assertContains(functionsPhp, "class-industry-schema-engine.php", 'Industry schema engine is not loaded before SEO manager.');

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    'schema_engine_enable',
    'schema_industry_type',
    'schema_default_currency',
    'Industry_Schema_Engine::get_industry_choices()',
    '行业 Schema 引擎',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Schema settings contract changed unexpectedly: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  assertContains(sanitizePhp, 'Industry_Schema_Engine::sanitize_options', 'Schema options are not sanitized centrally.');

}
