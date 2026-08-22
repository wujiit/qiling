import {
  assertContains,
  assertFileExists,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Performance mobile a11y contracts';

export async function run() {
  [
    'inc/core/class-page-performance-a11y-auditor.php',
    'inc/modules/class-module-manager.php',
    'inc/admin/traits/class-admin-settings-config-trait.php',
    'inc/admin/traits/class-admin-settings-sanitize-trait.php',
    'functions.php',
    'inc/core/bootstrap-services.php',
  ].forEach((file) => {
    assertFileExists(file, `Performance/a11y phase missing required file: ${file}`);
  });

  const auditorPhp = readThemeFile('inc/core/class-page-performance-a11y-auditor.php');
  [
    'class Page_Performance_A11y_Auditor',
    "const OPTION_ENABLE     = 'page_quality_audit_enable';",
    "const QUERY_VAR         = 'qiling_page_audit';",
    'build_resource_manifest(',
    'audit_lcp(',
    'audit_images(',
    'audit_headings(',
    'audit_forms(',
    'audit_color_contrast(',
    'audit_mobile_overflow(',
    'audit_cls(',
    "developer_starter_page_resource_manifest",
    "developer_starter_page_quality_report",
    'qiling-page-resource-manifest',
    'qiling-page-quality-audit',
  ].forEach((needle) => {
    assertContains(auditorPhp, needle, `Performance/a11y auditor contract changed unexpectedly: ${needle}`);
  });

  const moduleManagerPhp = readThemeFile('inc/modules/class-module-manager.php');
  [
    'get_attachment_image_dimensions_for_src(',
    'normalize_image_src_for_attachment_lookup(',
    'width="',
    'height="',
  ].forEach((needle) => {
    assertContains(moduleManagerPhp, needle, `Module image dimension contract missing: ${needle}`);
  });

  const settingsConfigPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    '页面资源与质量审计',
    'page_quality_audit_enable',
    'page_quality_audit_embed_json',
  ].forEach((needle) => {
    assertContains(settingsConfigPhp, needle, `Performance/a11y settings missing: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  assertContains(
    sanitizePhp,
    'Page_Performance_A11y_Auditor::sanitize_options',
    'Performance/a11y settings sanitizer is not registered.'
  );

  const bootstrapServicesPhp = readThemeFile('inc/core/bootstrap-services.php');
  assertContains(
    bootstrapServicesPhp,
    'Page_Performance_A11y_Auditor::get_instance();',
    'Performance/a11y auditor is not initialized.'
  );

}
