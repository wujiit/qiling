import {
  assertContains,
  readAdminSettingsFieldRenderSources,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'SEO push contracts';

export async function run() {
  const seoPushPhp = readThemeFile('inc/core/class-seo-push.php');
  [
    'SEO Push - Baidu, IndexNow/Bing and optional Google Indexing API.',
    'GOOGLE_INDEXING_ENDPOINT',
    'GOOGLE_TOKEN_ENDPOINT',
    'GOOGLE_INDEXING_SCOPE',
    'seo_push_google_enable',
    'seo_push_google_auto_enable',
    'seo_push_google_service_account_json',
    'ds_google_indexing_push_single',
    'ds_google_indexing_push_custom',
    'ds_seo_push_history',
    'get_google_access_token',
    'push_google_urls',
    'save_google_meta',
    '_ds_google_indexing_push_status',
    'SEO 推送状态',
    'IndexNow / Bing',
    'Google Indexing',
    '批量推送完成',
  ].forEach((needle) => {
    assertContains(seoPushPhp, needle, `SEO push service contract changed unexpectedly: ${needle}`);
  });

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    'SEO 推送（多通道）',
    'IndexNow / Bing 推送',
    'Google Indexing API',
    "'seo_push_google_enable'",
    "'seo_push_google_auto_enable'",
    "'seo_push_google_service_account_json'",
    '发布时自动推送到 Google',
    '批量历史推送每批最多处理 50 条',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `SEO push admin setting contract changed unexpectedly: ${needle}`);
  });

  const fieldRenderPhp = readAdminSettingsFieldRenderSources();
  [
    '手动/批量推送',
    'ds-google-push-custom-btn',
    'ds-seo-push-history-provider',
    'ds-seo-push-history-pending-btn',
    'ds-seo-push-history-failed-btn',
    'ds_google_indexing_push_custom',
    'ds_seo_push_history',
    '推送未成功历史内容',
    '只重试失败内容',
  ].forEach((needle) => {
    assertContains(fieldRenderPhp, needle, `SEO push manual UI contract changed unexpectedly: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  [
    'seo_push_google_service_account_json',
    'seo_push_google_auto_enable',
    'ds_google_service_account_invalid',
    'Google Service Account JSON 格式无效，已保留原配置。',
  ].forEach((needle) => {
    assertContains(sanitizePhp, needle, `SEO push sanitize contract changed unexpectedly: ${needle}`);
  });
}
