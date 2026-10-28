import {
  assertContains,
  assertFileExists,
  readAdminSettingsFieldRenderSources,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Cookie consent 2.0 contracts';

export async function run() {
  [
    'inc/international/class-cookie-consent-manager.php',
    'inc/international/class-third-party-code-manager.php',
    'inc/admin/traits/class-admin-settings-config-trait.php',
    'inc/admin/traits/class-admin-settings-field-render-trait.php',
    'inc/admin/traits/class-admin-settings-sanitize-trait.php',
    'assets/js/international-cookie-consent.js',
    'assets/css/international-cookie-consent.css',
  ].forEach((file) => {
    assertFileExists(file, `Cookie consent 2.0 chain missing required file: ${file}`);
  });

  const consentPhp = readThemeFile('inc/international/class-cookie-consent-manager.php');
  [
    "const CONSENT_VERSION = '2.0';",
    'public static function get_categories()',
    'add_shortcode( \'qiling_cookie_settings\'',
    'render_footer_settings_button',
    'data-qiling-open-cookie-settings',
    'get_consent_version()',
    'get_region_preset()',
    "'necessary'",
    "'statistics'",
    "'marketing'",
    "'advertising'",
    "'custom'",
    "'categories' => $this->get_frontend_categories()",
    "'regionPreset' => $this->get_region_preset()",
    "'defaultOptionalConsent' => $this->should_default_optional_consent()",
    'data-qiling-cookie-customize',
    'data-qiling-cookie-save',
    'data-qiling-cookie-category',
    'build_legacy_payload(',
    'is_category_allowed(',
    'return ! $this->is_category_allowed( $category, $this->get_consent_payload() );',
    'normalize_notice_position(',
    'developer_starter_international_third_party_code_allowed',
    'developer_starter_international_third_party_code_should_defer',
  ].forEach((needle) => {
    assertContains(consentPhp, needle, `Cookie consent manager lost 2.0 behavior: ${needle}`);
  });

  const codeManagerPhp = readThemeFile('inc/international/class-third-party-code-manager.php');
  [
    "'category'         => 'international_code_analytics_category'",
    "'category'         => 'international_code_ads_category'",
    "'category'         => 'international_code_custom_category'",
    "'default_category' => 'statistics'",
    "'default_category' => 'advertising'",
    "'default_category' => 'custom'",
    'resolve_group_category(',
    'category_requires_consent(',
    'data-category="',
    'developer_starter_international_third_party_code_allowed',
    'developer_starter_international_third_party_code_should_defer',
  ].forEach((needle) => {
    assertContains(codeManagerPhp, needle, `Third-party code manager lost category gating contract: ${needle}`);
  });

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    '$international_cookie_category_choices',
    '$international_cookie_region_choices',
    'international_code_head_category',
    'international_code_analytics_category',
    'international_code_ads_category',
    'international_code_custom_category',
    'international_cookie_customize_text',
    'international_cookie_save_text',
    'international_cookie_region_preset',
    'international_cookie_consent_version',
    'international_cookie_footer_button_enable',
    'international_cookie_footer_button_text',
    'international_cookie_notice_position',
    '[qiling_cookie_settings]',
    '接受全部按钮文字',
    '按 Cookie 分类控制新增第三方代码',
    '风险提示：这里粘贴的第三方代码会按配置在前台页面执行',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Admin settings lost Cookie 2.0 field contract: ${needle}`);
  });

  const fieldRenderPhp = readAdminSettingsFieldRenderSources();
  [
    'render_international_cookie_diagnostic_list(',
    'get_international_code_group_category_meta(',
    'analyze_international_code_group_risk(',
    '会被拦截的代码',
    '无分类代码',
    '高风险代码',
    '未授权前会被拦截',
  ].forEach((needle) => {
    assertContains(fieldRenderPhp, needle, `Admin Cookie diagnostics lost enhanced contract: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  [
    "'international_cookie_customize_text'",
    "'international_cookie_save_text'",
    "'international_cookie_region_preset'",
    "'international_cookie_consent_version'",
    "'international_cookie_footer_button_enable'",
    "'international_cookie_footer_button_text'",
    "'international_cookie_notice_position'",
    '$international_code_category_fields',
    "'statistics'",
    "'marketing'",
    "'advertising'",
  ].forEach((needle) => {
    assertContains(sanitizePhp, needle, `Cookie 2.0 settings sanitization contract changed unexpectedly: ${needle}`);
  });

  const frontendJs = readThemeFile('assets/js/international-cookie-consent.js');
  [
    'JSON.parse(value)',
    'JSON.stringify(consent)',
    'defaultOptionalConsent',
    'payload.version !== consentVersion',
    'acceptedValue',
    'rejectedValue',
    'buildCategories(false)',
    "category === 'necessary'",
    'data-category',
    "template.getAttribute('data-loaded') === '1'",
    'data-qiling-cookie-category',
    'qilingOpenCookieSettings',
    'qiling:openCookieSettings',
    'data-qiling-open-cookie-settings',
    'data-cookie-bound',
  ].forEach((needle) => {
    assertContains(frontendJs, needle, `Cookie consent frontend lost 2.0 behavior: ${needle}`);
  });

  const frontendCss = readThemeFile('assets/css/international-cookie-consent.css');
  [
    '.qiling-cookie-consent[data-position="bottom_left"]',
    '.qiling-cookie-consent[data-position="bottom_right"]',
    '.qiling-cookie-consent__settings',
    '.qiling-cookie-consent__category',
    '.qiling-cookie-consent__button--primary',
    '.qiling-cookie-settings-footer',
    '.qiling-cookie-settings-button',
  ].forEach((needle) => {
    assertContains(frontendCss, needle, `Cookie consent frontend CSS lost expected selector: ${needle}`);
  });
}
