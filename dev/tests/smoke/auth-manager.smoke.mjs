import {
  assertContains,
  assertFileExists,
  assertMatches,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Auth manager contracts';

export async function run() {
  [
    'inc/core/class-auth-manager.php',
    'inc/core/class-auth-register-email-service.php',
    'inc/core/class-auth-captcha-service.php',
    'inc/core/class-auth-pages-service.php',
    'inc/core/class-auth-flow-service.php',
    'inc/core/class-auth-profile-service.php',
    'inc/core/class-sms-manager.php',
  ].forEach((file) => {
    assertFileExists(file, `Auth chain missing required file: ${file}`);
  });

  const authManagerPhp = readThemeFile('inc/core/class-auth-manager.php');
  [
    "public function ajax_login()",
    "public function ajax_send_register_email_code()",
    "public function ajax_register()",
    "public function ajax_forgot_password()",
    "public function ajax_reset_password()",
    "public function ajax_captcha_challenge()",
    "public function ajax_verify_captcha()",
    'get_register_email_service()',
    'get_captcha_service()',
    'get_pages_service()',
    'get_flow_service()',
    'get_profile_service()',
    'send_no_store_ajax_headers()',
    'guard_public_ajax_rate_limit(',
    "developer_starter_is_public_ajax_rate_limited",
    "$this->guard_public_ajax_rate_limit( 'auth_refresh_nonce', 30, 60 );",
    "$this->guard_public_ajax_rate_limit( 'auth_user_status', 60, 60 );",
    "public function create_auth_pages()",
    "public function maybe_backfill_account_page()",
    "public function ajax_upload_avatar()",
    "public function ajax_refresh_nonce()",
    "public function ajax_get_user_status()",
  ].forEach((needle) => {
    assertContains(authManagerPhp, needle, `Auth manager contract changed unexpectedly: ${needle}`);
  });

  [
    /'option_callback'\s*=>\s*function\s*\(/,
    /'client_ip_callback'\s*=>\s*function\s*\(/,
    /'safe_payload_callback'\s*=>\s*function\s*\(/,
    /'rate_limit_callback'\s*=>\s*function\s*\(/,
    /'captcha_consume_callback'\s*=>\s*function\s*\(/,
    /'register_email_enabled_callback'\s*=>\s*function\s*\(/,
    /'verify_register_email_code_callback'\s*=>\s*function\s*\(/,
    /'clear_register_email_code_callback'\s*=>\s*function\s*\(/,
  ].forEach((pattern) => {
    assertMatches(authManagerPhp, pattern, `Auth manager callback bridge regressed: ${pattern}`);
  });

  const captchaServicePhp = readThemeFile('inc/core/class-auth-captcha-service.php');
  [
    'class Auth_Captcha_Service',
    'get_provider(',
    'issue_token(',
    'verify_aliyun(',
    'create_challenge(',
    'verify_theme_challenge(',
    'consume_token(',
  ].forEach((needle) => {
    assertContains(captchaServicePhp, needle, `Auth captcha service contract changed unexpectedly: ${needle}`);
  });

  const pagesServicePhp = readThemeFile('inc/core/class-auth-pages-service.php');
  [
    'class Auth_Pages_Service',
    'update_account_page_option(',
    'redirect_default_auth_pages(',
    'filter_register_url(',
    'create_auth_pages(',
    'maybe_backfill_account_page(',
    "case 'rp':",
    "case 'resetpass':",
    "'action' => 'reset'",
  ].forEach((needle) => {
    assertContains(pagesServicePhp, needle, `Auth pages service contract changed unexpectedly: ${needle}`);
  });

  const flowServicePhp = readThemeFile('inc/core/class-auth-flow-service.php');
  [
    'class Auth_Flow_Service',
    'handle_login(',
    'handle_register(',
    'handle_forgot_password(',
    'handle_reset_password(',
    'build_password_reset_url(',
    'network_site_url(',
    "wp-login.php?action=rp&key=",
    'wp_lostpassword_url()',
    'return wp_login_url();',
    'check_password_strength(',
    'validate_register_username_chinese_policy(',
    'get_register_username_chinese_policy(',
    'qiling_content_security_scan_text(',
  ].forEach((needle) => {
    assertContains(flowServicePhp, needle, `Auth flow service contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    flowServicePhp,
    /\$reset_url\s*=\s*\$this->build_password_reset_url\(\s*\$user,\s*\$key\s*\);/,
    'Forgot password email should use a non-empty reset URL builder'
  );
  assertContains(
    flowServicePhp,
    '$register_email_code_enabled = $this->is_register_email_code_enabled();',
    'Register flow should cache the email-code setting before captcha handling'
  );
  assertMatches(
    flowServicePhp,
    /if\s*\(\s*!\s*\$register_email_code_enabled\s*\)\s*{[\s\S]*?\$captcha_check\s*=\s*\$this->consume_captcha_if_required\(\s*\$request\s*\);/,
    'Email-code protected registration should not require a second captcha token'
  );
  assertMatches(
    flowServicePhp,
    /if\s*\(\s*\$register_email_code_enabled\s*\)\s*{[\s\S]*?\$verify_email_code\s*=\s*\$this->verify_register_email_code\(\s*\$email,\s*\$email_code\s*\);/,
    'Email-code protected registration should verify the email code in the register flow'
  );
  assertMatches(
    flowServicePhp,
    /private function build_password_reset_url[\s\S]*wp_lostpassword_url\(\)[\s\S]*return wp_login_url\(\);/,
    'Auth flow reset URL builder should end with WordPress lost-password/login fallbacks'
  );

  const profileServicePhp = readThemeFile('inc/core/class-auth-profile-service.php');
  [
    'class Auth_Profile_Service',
    'send_no_store_headers(',
    'get_already_logged_in_payload(',
    'get_user_status_payload(',
    'get_refresh_nonce_payload(',
    'handle_avatar_upload(',
  ].forEach((needle) => {
    assertContains(profileServicePhp, needle, `Auth profile service contract changed unexpectedly: ${needle}`);
  });

  const registerTemplatePhp = readThemeFile('templates/template-register.php');
  [
    'qilingshop_get_registration_code_obtain_link',
    '$register_code_obtain_enabled',
    'register-code-obtain-tip',
    'registerUsernameChinesePolicy',
    'usernameChineseDisallowed',
  ].forEach((needle) => {
    assertContains(registerTemplatePhp, needle, `Register page code-obtain link contract changed unexpectedly: ${needle}`);
  });

  const loginModalPhp = readThemeFile('inc/core/class-login-modal.php');
  [
    'qilingshop_get_registration_code_obtain_link',
    '$register_code_obtain_enabled',
    'register-code-obtain-tip',
    'registerUsernameChinesePolicy',
    'usernameChineseDisallowed',
  ].forEach((needle) => {
    assertContains(loginModalPhp, needle, `Login modal code-obtain link contract changed unexpectedly: ${needle}`);
  });

  const authPagesJs = readThemeFile('assets/js/auth-pages.js');
  [
    'hasHanCharacters(',
    'registerUsernameChinesePolicy',
    'usernameChineseDisallowed',
  ].forEach((needle) => {
    assertContains(authPagesJs, needle, `Register page username policy contract changed unexpectedly: ${needle}`);
  });

  const loginModalJs = readThemeFile('assets/js/login-modal.js');
  [
    'hasHanCharacters(',
    'registerUsernameChinesePolicy',
    'usernameChineseDisallowed',
  ].forEach((needle) => {
    assertContains(loginModalJs, needle, `Login modal username policy contract changed unexpectedly: ${needle}`);
  });

  const settingsConfigPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  assertContains(
    settingsConfigPhp,
    'register_username_chinese_policy',
    'Auth settings should expose the username Chinese policy'
  );

  const contentSecurityMainPhp = readThemeFile('../plugins/qilingcontentsecurity/qilingcontentsecurity.php');
  assertContains(
    contentSecurityMainPhp,
    'function qiling_content_security_scan_text(',
    'Content security plugin should expose a public text scan helper for integrations'
  );
  const contentSecurityPluginPhp = readThemeFile('../plugins/qilingcontentsecurity/includes/class-qiling-content-security-plugin.php');
  assertContains(
    contentSecurityPluginPhp,
    'public function scan_text(',
    'Content security plugin class should support public text scans for integrations'
  );

  const smsManagerPhp = readThemeFile('inc/core/class-sms-manager.php');
  [
    'class SMS_Manager',
    "public function ajax_send_code()",
    "public function ajax_phone_login()",
    "public function ajax_phone_register()",
    "private function consume_captcha_token( $token )",
    "developer_starter_get_option( 'auth_captcha_enable', '' ) && ! is_user_logged_in()",
  ].forEach((needle) => {
    assertContains(smsManagerPhp, needle, `SMS manager contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    smsManagerPhp,
    /public function ajax_send_code\(\)[\s\S]*?developer_starter_get_option\(\s*'auth_captcha_enable',\s*''\s*\)\s*&&\s*!\s*is_user_logged_in\(\)[\s\S]*?\$this->consume_captcha_token\(\s*\$captcha\s*\)/,
    'Guest SMS code sending should require captcha before a phone code can be sent'
  );
}
