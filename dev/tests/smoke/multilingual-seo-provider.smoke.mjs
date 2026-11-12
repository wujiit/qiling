import { existsSync, readFileSync } from 'node:fs';

import {
  assertContains,
  assertFileExists,
  readAdminSettingsFieldRenderSources,
  readThemeFile,
  themePath,
} from './_helpers.mjs';

export const name = 'Multilingual SEO provider contracts';

function readWorkspaceFile(relativePath) {
  const fullPath = themePath(`../${relativePath}`);
  if (!existsSync(fullPath)) {
    throw new Error(`Expected workspace file to exist: ${relativePath}`);
  }

  return readFileSync(fullPath, 'utf8');
}

function assertNotContains(haystack, needle, message) {
  if (haystack.includes(needle)) {
    throw new Error(message || `Expected content not to include: ${needle}`);
  }
}

export async function run() {
  [
    'inc/seo/class-seo-manager.php',
    'inc/admin/traits/class-admin-settings-field-render-trait.php',
    'inc/admin/class-meta-boxes-post-settings-service.php',
  ].forEach((file) => {
    assertFileExists(file, `Theme multilingual SEO provider chain missing required file: ${file}`);
  });

  const pluginMain = readWorkspaceFile('plugins/xb-aifanyi-translator/xb-aifanyi-translator.php');
  const pluginFrontendTrait = readWorkspaceFile('plugins/xb-aifanyi-translator/includes/class-xb-aifanyi-frontend-language-trait.php');
  const pluginTranslationTrait = readWorkspaceFile('plugins/xb-aifanyi-translator/includes/class-xb-aifanyi-translation-engine-trait.php');
  const themeContentHelpers = readThemeFile('inc/core/helpers/helpers-content-modules.php');
  const themeUrlHelpers = readThemeFile('inc/core/helpers/helpers-url-i18n-routing.php');
  const schemaEnginePhp = readThemeFile('inc/seo/class-industry-schema-engine.php');

  [
    "define('XB_AIFANYI_THEME_PROVIDER_CONTRACT_VERSION'",
    "function xb_aifanyi_get_theme_provider_capabilities()",
    "function xb_aifanyi_get_translation_url($post_id, $target_lang)",
    "function xb_aifanyi_get_post_language($post_id)",
    "function xb_aifanyi_get_frontend_translated_post_field($post_id, $field, $fallback = '', $lang = '')",
    "function xb_aifanyi_get_frontend_translated_post_meta($post_id, $meta_key, $fallback = '', $lang = '')",
    "function xb_aifanyi_get_frontend_translated_modules($post_id, $fallback = array(), $lang = '')",
    "function xb_aifanyi_get_post_seo_diagnostics($post_id)",
    "function xb_aifanyi_scan_site_seo_diagnostics($args = array())",
    "function xb_aifanyi_generate_site_seo_meta($args = array())",
    "function xb_aifanyi_export_site_seo_diagnostics($args = array())",
    "function xb_aifanyi_get_translation_editor_url($post_id, $target_lang = '')",
    "method_exists($xb_aifanyi_translator, 'xb_aifanyi_get_post_seo_diagnostics')",
    "method_exists($xb_aifanyi_translator, 'xb_aifanyi_scan_site_seo_diagnostics')",
    "method_exists($xb_aifanyi_translator, 'xb_aifanyi_generate_site_seo_meta')",
    "method_exists($xb_aifanyi_translator, 'xb_aifanyi_export_site_seo_diagnostics')",
    "'provider' => 'xb-aifanyi-translator'",
    "'provider_label' => '启灵AI多语言'",
  ].forEach((needle) => {
    assertContains(pluginMain, needle, `启灵AI多语言 plugin wrapper contract changed unexpectedly: ${needle}`);
  });

  [
    'public function xb_aifanyi_get_theme_provider_capabilities()',
    "'contract_version' => defined('XB_AIFANYI_THEME_PROVIDER_CONTRACT_VERSION') ? XB_AIFANYI_THEME_PROVIDER_CONTRACT_VERSION : ''",
    "'theme_contract' => 'qiling_i18n_provider'",
    "'head_output_disabled' => true",
    "'frontend_translation_url' => true",
    "'frontend_translated_fields' => true",
    "'frontend_translated_meta' => true",
    "'frontend_translated_modules' => true",
    'public function xb_aifanyi_get_post_seo_diagnostics($post_id)',
    "'seo_output_owner' => 'qiling_theme_seo_manager'",
    "'plugin_outputs_head_tags' => false",
    "'canonical_source' => 'plugin_url_rules'",
    "'canonical_url' => $url",
    "'hreflang_url' => $url",
    "'has_public_translation'",
    "'_developer_starter_seo_title'",
    "'_developer_starter_seo_description'",
    "'_developer_starter_seo_keywords'",
    "'_developer_starter_og_title'",
    "'_developer_starter_og_description'",
    "'sitemap' => $sitemap",
    'xb_aifanyi_get_sitemap_overview()',
    'public function xb_aifanyi_scan_site_seo_diagnostics($args = array())',
    "'seo_title_missing' => 0",
    "'seo_description_missing' => 0",
    "'og_title_missing' => 0",
    "'og_description_missing' => 0",
    "'hreflang_missing' => 0",
    "'hreflang_reciprocal_issues' => 0",
    "'x_default_issues' => 0",
    "'sitemap_missing_urls' => 0",
    'xb_aifanyi_build_sitemap_url_index_for_diagnostics',
    'xb_aifanyi_get_sitemap_diagnostics_cache_key',
    'xb_aifanyi_touch_sitemap_diagnostics_cache',
    'provider URL 规则未覆盖所有公开语言，非实时页面抓取结果。',
    'xb_aifanyi_generate_site_seo_meta',
    'xb_aifanyi_export_site_seo_diagnostics',
    'xb_aifanyi_get_translation_editor_url',
    'xb_aifanyi_apply_generated_seo_meta_for_language',
    'public function xb_aifanyi_get_public_post_language($post_id)',
  ].forEach((needle) => {
    assertContains(pluginFrontendTrait, needle, `启灵AI多语言 provider payload contract changed unexpectedly: ${needle}`);
  });

  [
    "'plugin_version' => defined('XB_AIFANYI_VERSION') ? XB_AIFANYI_VERSION : ''",
    "'contract_version' => defined('XB_AIFANYI_THEME_PROVIDER_CONTRACT_VERSION') ? XB_AIFANYI_THEME_PROVIDER_CONTRACT_VERSION : ''",
    "'source_language' => $source_lang",
    "'mode' => isset($result['translation_mode'])",
    "'created_or_updated' => isset($result['action'])",
    "'translation_record_id' => isset($result['translation_record_id'])",
  ].forEach((needle) => {
    assertContains(pluginTranslationTrait, needle, `启灵AI多语言 AI localization upsert contract changed unexpectedly: ${needle}`);
  });

  const pluginCombined = `${pluginMain}\n${pluginFrontendTrait}`;
  [
    "add_action('wp_head'",
    'add_action("wp_head"',
  ].forEach((needle) => {
    assertNotContains(pluginCombined, needle, `启灵AI多语言 must not hook direct SEO head output in MVP: ${needle}`);
  });

  const seoManagerPhp = readThemeFile('inc/seo/class-seo-manager.php');
  [
    'xb_aifanyi_get_frontend_canonical_url()',
    'xb_aifanyi_get_frontend_hreflang_map()',
    'xb_aifanyi_get_frontend_og_locale_data()',
  ].forEach((needle) => {
    assertContains(seoManagerPhp, needle, `Theme SEO manager lost 启灵AI多语言 frontend wrapper usage: ${needle}`);
  });
  const canonicalProviderIndex = seoManagerPhp.indexOf('xb_aifanyi_get_frontend_canonical_url()');
  const canonicalMetaIndex = seoManagerPhp.indexOf("_developer_starter_seo_canonical");
  if (canonicalProviderIndex === -1 || canonicalMetaIndex === -1 || canonicalProviderIndex > canonicalMetaIndex) {
    throw new Error('Theme SEO manager must prefer 启灵AI多语言 canonical URL rules before per-page canonical meta.');
  }

  const schemaCanonicalProviderIndex = schemaEnginePhp.indexOf('xb_aifanyi_get_frontend_canonical_url()');
  const schemaCanonicalMetaIndex = schemaEnginePhp.indexOf("_developer_starter_seo_canonical");
  if (schemaCanonicalProviderIndex === -1 || schemaCanonicalMetaIndex === -1 || schemaCanonicalProviderIndex > schemaCanonicalMetaIndex) {
    throw new Error('Theme Schema engine must prefer 启灵AI多语言 canonical URL rules before per-page canonical meta.');
  }

  [
    'xb_aifanyi_get_frontend_translated_post_field',
    'xb_aifanyi_get_frontend_translated_post_meta',
    'xb_aifanyi_get_frontend_translated_modules',
  ].forEach((needle) => {
    assertContains(themeContentHelpers, needle, `Theme content helpers must prefer 启灵AI多语言 frontend wrapper: ${needle}`);
  });

  [
    'xb_aifanyi_get_post_language',
    'xb_aifanyi_get_translation_url',
  ].forEach((needle) => {
    assertContains(themeUrlHelpers, needle, `Theme URL helpers must prefer 启灵AI多语言 URL wrapper: ${needle}`);
  });

  const settingsTraitPhp = readAdminSettingsFieldRenderSources();
  [
    'xb_aifanyi_get_theme_provider_capabilities',
    'get_aifanyi_multilingual_seo_provider_diagnostics',
    'xb_aifanyi_get_post_seo_diagnostics',
    '启灵AI多语言协作状态',
    '第一 provider',
    "'has_aifanyi_provider'",
    "'aifanyi_provider' => $aifanyi_provider",
    'render_international_seo_site_scan_field',
    'get_aifanyi_multilingual_seo_site_scan',
    'xb_aifanyi_scan_site_seo_diagnostics',
    'xb_aifanyi_generate_site_seo_meta',
    'xb_aifanyi_export_site_seo_diagnostics',
    '多语言 SEO 增强扫描',
    '缺失 %1$d / 规则 %2$d',
    '批量补齐 SEO 标题/描述',
    '导出诊断报告 CSV',
  ].forEach((needle) => {
    assertContains(settingsTraitPhp, needle, `Theme international SEO center lost provider diagnostics contract: ${needle}`);
  });

  const adminSettingsPhp = readThemeFile('inc/admin/class-admin-settings.php');
  [
    'admin_post_developer_starter_export_i18n_seo_report',
    'admin_post_developer_starter_generate_i18n_seo_meta',
    'handle_international_seo_report_export',
    'handle_international_seo_meta_generation',
  ].forEach((needle) => {
    assertContains(adminSettingsPhp, needle, `Theme multilingual SEO admin action contract changed unexpectedly: ${needle}`);
  });

  const postSettingsPhp = readThemeFile('inc/admin/class-meta-boxes-post-settings-service.php');
  [
    'render_multilingual_seo_status_matrix',
    '多语言 SEO 状态矩阵',
    'xb_aifanyi_get_post_seo_diagnostics',
    'canonical 与 hreflang 优先按启灵AI多语言 URL 规则判断',
    'OG image、noindex、nofollow 第一阶段继承原页面',
    'qiling-ml-seo-matrix__table',
    'get_multilingual_seo_status_label',
  ].forEach((needle) => {
    assertContains(postSettingsPhp, needle, `Theme editor multilingual SEO matrix contract changed unexpectedly: ${needle}`);
  });
}
