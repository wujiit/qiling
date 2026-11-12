import {
  assertContains,
  assertFileExists,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Page package contracts';

export async function run() {
  [
    'inc/core/class-page-package-manager.php',
    'inc/core/class-page-package-module-service.php',
    'inc/core/class-page-package-page-service.php',
    'inc/core/class-page-package-import-state-service.php',
    'inc/core/class-page-package-diagnostics-service.php',
    'inc/core/class-page-package-analysis-service.php',
    'inc/core/class-page-package-preview-service.php',
    'inc/core/class-page-package-export-service.php',
    'inc/core/class-page-package-import-execution-service.php',
    'inc/admin/class-page-package-admin.php',
  ].forEach((file) => {
    assertFileExists(file, `Page package chain missing required file: ${file}`);
  });

  const managerPhp = readThemeFile('inc/core/class-page-package-manager.php');
  [
    'private function get_module_service()',
    'private function get_page_service()',
    'private function get_import_state_service()',
    'private function get_diagnostics_service()',
    'private function get_analysis_service()',
    'private function get_preview_service()',
    'private function get_export_service()',
    'private function get_import_execution_service()',
    'const PACKAGE_SCOPE_PAGE',
    'const PACKAGE_SCOPE_SITE',
    'public function analyze_site_package( $raw_json, $options = array() )',
    'public function import_site_package( $prepared_package, $options = array() )',
    'public function create_site_package_preview( $prepared_package, $options = array() )',
    'public function get_package_scope_choices()',
    'public function get_exportable_pages()',
    'public function get_import_history( $limit = 20 )',
    'public function rollback_import_history( $run_id )',
    'public function export_site_package( $page_ids, $options = array() )',
    '$this->get_import_execution_service()->import_site_package( $prepared_package, $options );',
    '$this->get_preview_service()->create_site_package_preview( $prepared_package, $options );',
    '$this->get_export_service()->get_exportable_pages();',
    '$this->get_import_state_service()->get_import_history( $limit );',
    '$this->get_import_state_service()->rollback_import_history( $run_id );',
    '$this->get_export_service()->export_site_package( $page_ids, $options );',
    "$raw_site_options['design_system_v2'] = $package['design_system_v2'];",
    "$raw_site_options['design_system_v2'] = $package['design_system']['design_system_v2'];",
  ].forEach((needle) => {
    assertContains(managerPhp, needle, `Page package manager contract changed unexpectedly: ${needle}`);
  });

  const adminPhp = readThemeFile('inc/admin/class-page-package-admin.php');
  [
    '$this->manager->get_exportable_pages();',
    '$this->manager->get_import_history( 20 );',
    '$this->manager->get_package_scope_choices();',
    '$this->manager->rollback_import_history( $run_id );',
    '$this->manager->analyze_site_package(',
    '$this->manager->create_site_package_preview(',
    '$this->manager->import_site_package(',
    '$this->manager->export_site_package(',
  ].forEach((needle) => {
    assertContains(adminPhp, needle, `Page package admin contract changed unexpectedly: ${needle}`);
  });

  const analysisPhp = readThemeFile('inc/core/class-page-package-analysis-service.php');
  [
    "'scope'             => $scope",
    "'manifest'          => $this->diagnostics_service->normalize_package_manifest(",
    "'design_system'     => $design_system",
    "'design_system_v2'  => ! empty( $design_system['design_system_v2'] )",
    "'content_models'    => $content_models",
    "'site_assets'       => $site_assets",
  ].forEach((needle) => {
    assertContains(analysisPhp, needle, `Page/site package analysis contract missing: ${needle}`);
  });

  const exportPhp = readThemeFile('inc/core/class-page-package-export-service.php');
  [
    'build_export_design_system_payload',
    'build_export_content_models_payload',
    'build_export_navigation_payload',
    'build_export_site_assets_payload',
    'Design_Tokens::get_storage_payload( $theme_options )',
    'Design_Tokens::get_compatibility_option_payload( $design_system_v2, $theme_options )',
    "'scope'             => $options['scope']",
    "$payload['design_system'] = $design_system",
    "$payload['design_system_v2'] = $design_system['design_system_v2'];",
    "'storage_key'    => $storage_key,",
    "'design_system_v2' => $design_system_v2,",
    "$payload['content_models'] = $content_models",
    "$payload['navigation'] = $navigation",
  ].forEach((needle) => {
    assertContains(exportPhp, needle, `Page/site package export contract missing: ${needle}`);
  });

  const pageServicePhp = readThemeFile('inc/core/class-page-package-page-service.php');
  [
    'apply_navigation_options',
    'normalize_navigation_options',
    'apply_design_system_v2_payload',
    "Design_Tokens::merge_storage_payload_with_options( $normalized, $design_options, $stored )",
    "$this->apply_design_system_v2_payload(",
    'normalize_design_system_v2_payload',
    "'design_options'",
    "'design_system_v2'",
    "'content_model_options'",
    "'posts_page'",
    '已应用完整 design_system_v2 设计系统。',
  ].forEach((needle) => {
    assertContains(pageServicePhp, needle, `Page/site package apply contract missing: ${needle}`);
  });

  const singlePagePackagePhp = readThemeFile('inc/core/class-single-page-package-service.php');
  [
    'wp_specialchars_decode( $raw_title, ENT_QUOTES )',
    'html_entity_decode( $raw_title, ENT_QUOTES | ENT_HTML5, $charset )',
  ].forEach((needle) => {
    assertContains(singlePagePackagePhp, needle, `Imported page title entity decoding contract changed unexpectedly: ${needle}`);
  });

  const diagnosticsPhp = readThemeFile('inc/core/class-page-package-diagnostics-service.php');
  [
    'normalize_design_system_storage_payload',
    "'storage_key'     => $storage_key,",
    "'design_system_v2' => $design_system_v2,",
  ].forEach((needle) => {
    assertContains(diagnosticsPhp, needle, `Page/site package diagnostics contract missing: ${needle}`);
  });

}
