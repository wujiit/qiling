import {
  assertContains,
  assertFileExists,
  readAdminSettingsFieldRenderSources,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Schema preview MVP contracts';

export async function run() {
  [
    'inc/seo/class-industry-schema-engine.php',
    'inc/seo/class-seo-manager.php',
    'inc/admin/class-meta-boxes-post-settings-service.php',
    'inc/admin/traits/class-admin-settings-config-trait.php',
    'inc/admin/traits/class-admin-settings-field-render-trait.php',
    'inc/admin/traits/class-admin-settings-sanitize-trait.php',
  ].forEach((file) => {
    assertFileExists(file, `Schema preview MVP missing required file: ${file}`);
  });

  const enginePhp = readThemeFile('inc/seo/class-industry-schema-engine.php');
  [
    'get_preview_data(',
    'build_schema_payload(',
    'encode_schema_payload(',
    'get_preview_node_status(',
    'get_schema_preview_required_warnings(',
    'find_graph_node_by_id_suffix(',
    'META_SCHEMA_OVERRIDE_ENABLE',
    'get_page_schema_type_choices(',
    'sanitize_page_schema_override(',
    'get_page_schema_override(',
    'persist_page_schema_override(',
    'apply_page_schema_override_to_graph(',
    'build_page_schema_override_node(',
    'build_schema_diagnostics(',
    'get_schema_conflict_warnings(',
    "count_top_level_schema_types( $graph, array( 'Product' ) )",
    "'@id' => untrailingslashit( $page_url ) . '#webpage'",
    '多个顶层 Product',
    "'node_status'",
    "'missing_required'",
    "'diagnostics'",
    "'primary_type'",
    'Organization',
    'WebSite',
    'BreadcrumbList',
    'HowTo',
    'JobPosting',
  ].forEach((needle) => {
    assertContains(enginePhp, needle, `Schema preview engine contract missing: ${needle}`);
  });

  const seoManagerPhp = readThemeFile('inc/seo/class-seo-manager.php');
  assertContains(
    seoManagerPhp,
    'Industry_Schema_Engine::get_instance()->get_json_ld()',
    'Front-end Schema output must continue to go through Industry_Schema_Engine.'
  );

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    '行业 Schema 引擎',
    'schema_engine_enable',
    'company_name',
    'site_logo',
    'company_phone',
    'company_email',
    'company_address',
    'company_working_hours',
    'schema_default_currency',
    'schema_industry_type',
    'render_schema_preview_field',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Schema site-level settings contract missing: ${needle}`);
  });

  const renderPhp = readAdminSettingsFieldRenderSources();
  [
    'render_schema_preview_field',
    'Industry_Schema_Engine::get_instance()->get_preview_data( 0 )',
    'format_schema_preview_json',
    'render_schema_preview_assets_once',
    'schema_jsonld_preview',
    'JSON-LD 预览',
    'Organization',
    'WebSite',
    'Breadcrumb',
    '当前页面主类型',
    '必填字段缺失提示',
    'ds-schema-preview-json',
    'ds-schema-preview-issues',
    'Schema 可视化诊断',
  ].forEach((needle) => {
    assertContains(renderPhp, needle, `Schema preview render contract missing: ${needle}`);
  });

  const metaBoxPhp = readThemeFile('inc/admin/class-meta-boxes-post-settings-service.php');
  [
    'render_schema_override_meta_box',
    'qiling_schema_override',
    '页面级 Schema 覆盖',
    'FAQ 问答',
    'HowTo',
    'JobPosting',
    'Schema 可视化诊断',
    'Industry_Schema_Engine::persist_page_schema_override',
  ].forEach((needle) => {
    assertContains(metaBoxPhp, needle, `Schema page-level override editor contract missing: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  [
    'company_name',
    'company_phone',
    'company_working_hours',
    'company_address',
    'company_brief',
    'company_email',
    'sanitize_email',
    'Industry_Schema_Engine::sanitize_options',
  ].forEach((needle) => {
    assertContains(sanitizePhp, needle, `Schema preview sanitization contract missing: ${needle}`);
  });

  if (configPhp.includes('schema_custom_json') || renderPhp.includes('schema_custom_json')) {
    throw new Error('Schema MVP should not add free-form custom JSON editing.');
  }
}
