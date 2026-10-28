import {
  assertContains,
  assertFileExists,
  assertMatches,
  readThemeFile,
} from './_helpers.mjs';

export const name = '404 redirect contracts';

export async function run() {
  assertFileExists('inc/core/helpers/helpers-404-redirects.php', '404 redirect helper should exist');

  const functionsPhp = readThemeFile('functions.php');
  assertContains(
    functionsPhp,
    "require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-404-redirects.php';",
    '404 redirect helper should load from functions.php'
  );

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'id' => 'error_404_redirect_enable'",
    "'id' => 'error_404_redirect_status'",
    "'id' => 'error_404_redirect_rules'",
    "/shop => /aishop\\n/old-page => /new-page",
    '正常存在的页面不会受影响',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `404 redirect setting missing: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  [
    "isset( $sanitized['error_404_redirect_enable'] )",
    "isset( $sanitized['error_404_redirect_status'] )",
    "array( '301', '302' )",
    "developer_starter_sanitize_404_redirect_rules( $sanitized['error_404_redirect_rules'] )",
  ].forEach((needle) => {
    assertContains(sanitizePhp, needle, `404 redirect sanitize guard missing: ${needle}`);
  });

  const helperPhp = readThemeFile('inc/core/helpers/helpers-404-redirects.php');
  [
    'function developer_starter_maybe_redirect_404()',
    "add_action( 'template_redirect', 'developer_starter_maybe_redirect_404', 0 );",
    'is_404()',
    "developer_starter_404_redirect_get_option( 'error_404_redirect_enable', '' )",
    'developer_starter_parse_404_redirect_rules',
    'developer_starter_sanitize_404_redirect_rules',
    'developer_starter_normalize_404_redirect_path',
    'developer_starter_sanitize_404_redirect_target',
    'developer_starter_404_redirect_host_matches_site',
    "wp_safe_redirect( $target_url, $status, 'QiLing 404 Redirect' );",
  ].forEach((needle) => {
    assertContains(helperPhp, needle, `404 redirect helper contract changed unexpectedly: ${needle}`);
  });

  assertMatches(
    helperPhp,
    /if \( is_admin\(\) \|\| headers_sent\(\) \|\| ! is_404\(\) \|\| is_feed\(\) \|\| is_preview\(\) \) \{/,
    '404 redirects should only run on frontend 404 requests before output'
  );
  assertMatches(
    helperPhp,
    /if \( ! empty\( \$parts\['host'\] \) && ! developer_starter_404_redirect_host_matches_site/,
    '404 redirect targets should reject off-site URLs'
  );
  assertMatches(
    helperPhp,
    /if \( \$target_path === \$current_path \) \{/,
    '404 redirects should guard against self-redirect loops'
  );
}
