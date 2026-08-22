import {
  assertContains,
  assertFileExists,
  assertMatches,
  readAdminSettingsFieldRenderSources,
  readThemeFile,
  themePath,
} from './_helpers.mjs';
import { existsSync, readdirSync } from 'node:fs';

export const name = 'Runtime safety contracts';

function collectPhpFiles(relativeDir = '') {
  const files = [];
  const entries = readdirSync(themePath(relativeDir || '.'), { withFileTypes: true });

  for (const entry of entries) {
    if (entry.name === 'sms') {
      continue;
    }

    const relativePath = relativeDir ? `${relativeDir}/${entry.name}` : entry.name;
    if (entry.isDirectory()) {
      files.push(...collectPhpFiles(relativePath));
    } else if (entry.isFile() && entry.name.endsWith('.php')) {
      files.push(relativePath);
    }
  }

  return files;
}

export async function run() {
  [
    '.editorconfig',
    '.github/workflows/theme-quality.yml',
    'dev/composer.json',
    'dev/phpcs.xml.dist',
    'dev/phpstan.neon.dist',
    'dev/phpstan-baseline.neon',
    'dev/tools/check-eol.mjs',
    'dev/tools/minify-css.mjs',
    'dev/tools/build-release.mjs',
    '.gitignore',
    'inc/admin/admin-bootstrap.php',
    'inc/core/bootstrap-page-creators.php',
    'inc/core/bootstrap-services.php',
    'inc/core/page-creator-registry.php',
    'inc/admin/traits/class-admin-settings-admin-trait.php',
    'inc/core/helpers/bootstrap.php',
    'inc/core/helpers/helpers-multibyte-polyfill.php',
    'inc/core/helpers/helpers-filesystem.php',
    'inc/core/helpers/helpers-client-ip.php',
    'inc/core/helpers/helpers-country-flag.php',
    'inc/core/helpers/helpers-blog-pagination.php',
    'inc/core/helpers/helpers-category-base.php',
    'inc/core/helpers/helpers-dark-mode.php',
    'inc/core/helpers/helpers-maintenance.php',
    'inc/core/helpers/helpers-media-security-video.php',
    'inc/core/helpers/helpers-media-webp.php',
    'inc/core/helpers/helpers-security-ip-login.php',
    'inc/core/helpers/helpers-video-notifications.php',
    'inc/core/helpers/helpers-content-modules.php',
    'inc/core/helpers/helpers-content-serialization.php',
    'inc/core/helpers/helpers-page-modules.php',
    'inc/core/helpers/helpers-url-i18n-routing.php',
    'inc/core/helpers/helpers-domain-url-migration.php',
    'inc/core/helpers/helpers-i18n-routing.php',
    'inc/core/class-page-creator-base.php',
    'inc/core/class-ip-location.php',
    'inc/core/class-thumbnail-optimizer.php',
    'inc/core/class-id-verification-manager.php',
    'inc/core/class-id-verification-rest-controller.php',
    'inc/core/class-message-manager.php',
    'inc/admin/traits/field-render/class-admin-settings-field-render-ai-connections-trait.php',
    'inc/admin/traits/field-render/class-admin-settings-field-render-design-presets-trait.php',
    'inc/admin/traits/field-render/class-admin-settings-field-render-design-tokens-trait.php',
    'inc/admin/traits/field-render/class-admin-settings-field-render-governance-trait.php',
    'inc/admin/traits/field-render/class-admin-settings-field-render-international-center-trait.php',
    'inc/admin/traits/field-render/class-admin-settings-field-render-international-diagnostics-trait.php',
    'inc/admin/traits/field-render/class-admin-settings-field-render-tools-trait.php',
    'templates/template-blog.php',
    'template-parts/single/header.php',
    'template-parts/single/meta-stats.php',
    'template-parts/single/content.php',
    'template-parts/single/modals.php',
    'template-parts/single/related.php',
    'template-parts/single/interaction-script.php',
    'assets/css/article-enhance.css',
    'assets/js/article-enhance.js',
    'assets/css/comments.css',
    'assets/css/reading-progress.css',
    'assets/js/reading-progress.js',
    'assets/css/lazy-image-placeholder.css',
    'assets/js/lazy-image-placeholder.js',
    'assets/js/auth-flow.js',
  ].forEach((file) => {
    assertFileExists(file, `Runtime safety chain missing required file: ${file}`);
  });

  const editorConfig = readThemeFile('.editorconfig');
  [
    'root = true',
    'end_of_line = lf',
    'insert_final_newline = true',
    'charset = utf-8',
  ].forEach((needle) => {
    assertContains(editorConfig, needle, `EditorConfig EOL contract changed unexpectedly: ${needle}`);
  });

  const eolCheck = readThemeFile('dev/tools/check-eol.mjs');
  [
    "const ignoredDirs = new Set([",
    "'sms'",
    "'translate'",
    'inspectLineEndings',
    'normalizeText',
    'Run `npm run fix:eol` from qiling/dev',
  ].forEach((needle) => {
    assertContains(eolCheck, needle, `EOL checker contract changed unexpectedly: ${needle}`);
  });

  const releaseBuilder = readThemeFile('dev/tools/build-release.mjs');
  [
    'function runPreReleaseChecks()',
    "[join(scriptDir, 'check-eol.mjs')]",
    'runPreReleaseChecks();',
    'function resolveReleaseOutputRoot()',
    "return resolve(themeRoot, '..', 'dist', themeSlug);",
    'function assertDistRootOutsideTheme()',
    'assertDistRootOutsideTheme();',
  ].forEach((needle) => {
    assertContains(releaseBuilder, needle, `Release builder EOL preflight changed unexpectedly: ${needle}`);
  });

  const gitIgnore = readThemeFile('.gitignore');
  [
    '/dist/',
    '*.zip',
    '.DS_Store',
  ].forEach((needle) => {
    assertContains(gitIgnore, needle, `Release artifact ignore rule missing: ${needle}`);
  });
  if (existsSync(themePath('dist'))) {
    throw new Error('Theme source tree should not contain dist/ release artifacts');
  }

  const phpcsConfig = readThemeFile('dev/phpcs.xml.dist');
  [
    '<file>../inc</file>',
    '<file>../template-parts</file>',
    '<file>../templates</file>',
    '<exclude-pattern>*/assets/*</exclude-pattern>',
    '<exclude-pattern>*/sms/vendor/*</exclude-pattern>',
    '<exclude-pattern>*/translate/*</exclude-pattern>',
  ].forEach((needle) => {
    assertContains(phpcsConfig, needle, `PHPCS coverage contract changed unexpectedly: ${needle}`);
  });
  [
    '*/template-parts/*',
    '*/templates/*',
    '*/inc/admin/views/*',
    '*/inc/core/helpers/helpers-content-modules.php',
    '*/inc/modules/modules/*',
  ].forEach((needle) => {
    assertMatches(
      phpcsConfig,
      new RegExp(`^(?:(?!${needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}).)*$`, 's'),
      `PHPCS should not exclude first-party runtime surface: ${needle}`
    );
  });

  const phpstanConfig = readThemeFile('dev/phpstan.neon.dist');
  [
    '- phpstan-baseline.neon',
    'level: 3',
    '- ../inc',
    '- ../template-parts',
    '- ../templates',
    '- ../sms/vendor/*',
  ].forEach((needle) => {
    assertContains(phpstanConfig, needle, `PHPStan coverage contract changed unexpectedly: ${needle}`);
  });
  [
    '- ../template-parts/*',
    '- ../templates/*',
    '- ../inc/admin/views/*',
    '- ../inc/core/helpers/helpers-content-modules.php',
    '- ../inc/modules/modules/*',
    '- ../inc/woocommerce/*',
  ].forEach((needle) => {
    assertMatches(
      phpstanConfig,
      new RegExp(`^(?:(?!${needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}).)*$`, 's'),
      `PHPStan should not exclude first-party runtime surface: ${needle}`
    );
  });
  assertContains(
    readThemeFile('dev/phpstan-baseline.neon'),
    'ignoreErrors: []',
    'PHPStan baseline should exist as the migration file'
  );

  const themeQualityWorkflow = readThemeFile('.github/workflows/theme-quality.yml');
  [
    'static-analysis:',
    'composer run lint:php',
    'composer run analyse:php',
    'dev/phpstan-baseline.neon',
  ].forEach((needle) => {
    assertContains(themeQualityWorkflow, needle, `Theme quality workflow static-analysis contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    themeQualityWorkflow,
    /^(?:(?!continue-on-error:\s*true).)*$/s,
    'Theme quality static analysis job must fail CI instead of being allowed to fail'
  );

  const composerJson = readThemeFile('dev/composer.json');
  [
    '"analyse:php": "phpstan analyse --configuration=phpstan.neon.dist --memory-limit=1G"',
    '"analyse:php:baseline": "phpstan analyse --configuration=phpstan.neon.dist --memory-limit=1G --generate-baseline=phpstan-baseline.neon"',
  ].forEach((needle) => {
    assertContains(composerJson, needle, `Composer static-analysis script contract changed unexpectedly: ${needle}`);
  });

  const themeSetupPhp = readThemeFile('inc/core/class-theme-setup.php');
  assertContains(
    themeSetupPhp,
    "load_theme_textdomain( 'developer-starter', DEVELOPER_STARTER_DIR . '/languages' );",
    'Theme UI textdomain must load unconditionally in setup_theme()'
  );
  assertMatches(
    themeSetupPhp,
    /^(?:(?!\$should_load_textdomain).)*$/s,
    'Theme UI textdomain loading must not be gated by frontend language switch mode'
  );
  [
    "add_action( 'init', array( $this, 'restore_default_page_editor_support' ), 100 );",
    "add_action( 'admin_init', array( $this, 'restore_default_page_editor_support' ), 100 );",
    "add_filter( 'rest_prepare_post_type', array( $this, 'restore_page_editor_support_in_rest' ), 10, 3 );",
    "public function restore_default_page_editor_support()",
    "public function restore_page_editor_support_in_rest( $response, $post_type, $request )",
    "add_post_type_support( 'page', 'editor' );",
    "\$data['supports']['editor'] = true;",
  ].forEach((needle) => {
    assertContains(themeSetupPhp, needle, `Theme setup must preserve the default page content editor: ${needle}`);
  });
  [
    "developer_starter_get_option( 'enable_gutenberg_editor_style', '' )",
    "add_theme_support( 'editor-styles' );",
    "add_editor_style( 'assets/css/editor-style.css' );",
  ].forEach((needle) => {
    assertContains(themeSetupPhp, needle, `Gutenberg editor style must remain opt-in: ${needle}`);
  });
  [
    "sanitize_html_class( 'page-' . $post->post_name )",
    "sanitize_html_class( 'header-style-' . $header_style, 'header-style-default' )",
  ].forEach((needle) => {
    assertContains(themeSetupPhp, needle, `Theme setup body classes should sanitize dynamic class segments: ${needle}`);
  });

  const blogVisualManagerPhp = readThemeFile('inc/core/class-blog-visual-manager.php');
  assertContains(
    blogVisualManagerPhp,
    "sanitize_html_class( 'qiling-blog-preset-' . self::get_current_preset(), 'qiling-blog-preset-default' )",
    'Blog visual preset body class should sanitize the option-derived preset slug'
  );

  const wcSetupPhp = readThemeFile('inc/woocommerce/class-wc-setup.php');
  assertContains(
    wcSetupPhp,
    "sanitize_html_class( 'wc-layout-' . $this->get_layout(), 'wc-layout-full-width' )",
    'WooCommerce layout body class should sanitize the option-derived layout slug'
  );

  const adminSettingsConfigPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'id' => 'enable_gutenberg_editor_style'",
    "'label' => __( '启用主题编辑器样式'",
    'assets/css/editor-style.css',
    '默认关闭',
  ].forEach((needle) => {
    assertContains(adminSettingsConfigPhp, needle, `Gutenberg editor style option contract changed unexpectedly: ${needle}`);
  });
  assertContains(
    readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php'),
    "isset( $sanitized['enable_gutenberg_editor_style'] )",
    'Gutenberg editor style option must be sanitized as an opt-in checkbox'
  );
  assertFileExists('assets/css/editor-style.css', 'Optional Gutenberg editor style file must exist');

  const runtimeOptimizationsPhp = readThemeFile('inc/core/helpers/helpers-runtime-optimizations.php');
  [
    'function developer_starter_get_default_runtime_block_editor_allowlist()',
    '$entries = array();',
    "developer_starter_get_default_runtime_block_editor_allowlist()",
    "developer_starter_get_runtime_whitelist_entries(\n            'runtime_block_editor_allowlist'",
    "return in_array( 'post_type:*', $entries, true ) || in_array( 'post_type:' . $context, $entries, true );",
  ].forEach((needle) => {
    assertContains(runtimeOptimizationsPhp, needle, `Runtime block editor allowlist default contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    runtimeOptimizationsPhp,
    /^(?:(?!'post_type:post'|'post_type:page').)*$/s,
    'Disabling Gutenberg should not keep posts or pages on the block editor by default'
  );

  const editorAssetsPhp = readThemeFile('inc/core/class-assets.php');
  [
    "public function admin_assets( $hook )",
    "$is_theme_settings_page = strpos( (string) $hook, 'developer-starter' ) !== false;",
    "if ( ! $is_theme_settings_page ) {",
    "wp_enqueue_style( 'developer-starter-admin', DEVELOPER_STARTER_ASSETS . '/css/admin.css'",
  ].forEach((needle) => {
    assertContains(editorAssetsPhp, needle, `Theme admin CSS must only load on QiLing theme admin pages: ${needle}`);
  });
  assertMatches(
    editorAssetsPhp,
    /public function admin_assets\( \$hook \)[\s\S]*?\n    }\n\n    \/\*\*/s,
    'Theme admin CSS enqueue method should remain tightly scoped'
  );
  const adminAssetsMethod = editorAssetsPhp.match(/public function admin_assets\( \$hook \)[\s\S]*?\n    }\n\n    \/\*\*/s)?.[0] || '';
  assertMatches(
    adminAssetsMethod,
    /^(?:(?!is_page_editor|is_block_editor|post-new\.php|post\.php).)*$/s,
    'Theme admin CSS must not special-case post/page editors'
  );
  const modulesMetaBoxViewPhp = readThemeFile('inc/admin/views/modules-meta-box.php');
  [
    'z-index: 1000000;',
    'function showDsmModal(selector)',
    '$modal.appendTo(document.body);',
    "showDsmModal('#dsm-ai-modal');",
    "showDsmModal('#dsm-template-modal');",
  ].forEach((needle) => {
    assertContains(modulesMetaBoxViewPhp, needle, `Page module modals must stay above Gutenberg editor layers: ${needle}`);
  });

  const multilingualFrontendPhp = readThemeFile('inc/core/helpers/helpers-multilingual-frontend.php');
  assertContains(
    multilingualFrontendPhp,
    "add_action( 'after_setup_theme', 'developer_starter_load_multilingual_theme_textdomain', 11 );",
    'Multilingual content textdomain reload must run after the base theme textdomain registration'
  );

  const fieldRenderAggregatorPhp = readThemeFile('inc/admin/traits/class-admin-settings-field-render-trait.php');
  [
    "require_once __DIR__ . '/field-render/class-admin-settings-field-render-international-center-trait.php';",
    "require_once __DIR__ . '/field-render/class-admin-settings-field-render-ai-connections-trait.php';",
    "require_once __DIR__ . '/field-render/class-admin-settings-field-render-governance-trait.php';",
    "require_once __DIR__ . '/field-render/class-admin-settings-field-render-international-diagnostics-trait.php';",
    "require_once __DIR__ . '/field-render/class-admin-settings-field-render-design-tokens-trait.php';",
    "require_once __DIR__ . '/field-render/class-admin-settings-field-render-design-presets-trait.php';",
    "require_once __DIR__ . '/field-render/class-admin-settings-field-render-tools-trait.php';",
    'use Admin_Settings_Field_Render_International_Center_Trait;',
    'use Admin_Settings_Field_Render_AI_Connections_Trait;',
    'use Admin_Settings_Field_Render_Governance_Trait;',
    'use Admin_Settings_Field_Render_International_Diagnostics_Trait;',
    'use Admin_Settings_Field_Render_Design_Tokens_Trait;',
    'use Admin_Settings_Field_Render_Design_Presets_Trait;',
    'use Admin_Settings_Field_Render_Tools_Trait;',
  ].forEach((needle) => {
    assertContains(fieldRenderAggregatorPhp, needle, `Field render trait aggregator contract changed unexpectedly: ${needle}`);
  });
  if (fieldRenderAggregatorPhp.split(/\r?\n/).length > 120) {
    throw new Error('Admin_Settings_Field_Render_Trait should stay as a small aggregator');
  }

  const devPackageJson = readThemeFile('dev/package.json');
  assertContains(
    devPackageJson,
    '"build:css": "node ./tools/minify-css.mjs"',
    'CSS minification should have a build-time npm entry instead of relying only on admin runtime writes'
  );

  const helperBootstrap = readThemeFile('inc/core/helpers/bootstrap.php');
  assertContains(
    helperBootstrap,
    "require_once __DIR__ . '/helpers-filesystem.php';",
    'Filesystem helper should load before helpers that sanitize uploads or manage runtime caches'
  );

  const filesystemHelper = readThemeFile('inc/core/helpers/helpers-filesystem.php');
  [
    'developer_starter_filesystem_path_is_allowed',
    'developer_starter_filesystem_write_theme_generated_asset',
    'developer_starter_filesystem_write_temp_file',
    'developer_starter_filesystem_delete_file',
    'developer_starter_filesystem_delete_empty_dir',
    'developer_starter_filesystem_delete_temp_file',
    'developer_starter_filesystem_move_file',
    'developer_starter_filesystem_log_failure',
    'developer_starter_filesystem_theme_generated_asset_files',
  ].forEach((needle) => {
    assertContains(filesystemHelper, needle, `Runtime filesystem helper contract changed unexpectedly: ${needle}`);
  });

  collectPhpFiles().forEach((file) => {
    if (file === 'inc/core/helpers/helpers-filesystem.php') {
      return;
    }

    const source = readThemeFile(file);
    if ( /(^|[^A-Za-z0-9_])(?:file_put_contents|unlink|rmdir|rename|copy)\s*\(/.test(source) ) {
      throw new Error(`${file} should route direct filesystem writes/deletes through helpers-filesystem.php`);
    }
  });

  [
    ['inc/core/helpers/helpers-media-webp.php', 'inc/core/helpers/helpers-media-security-video.php'],
    ['inc/core/helpers/helpers-security-ip-login.php', 'inc/core/helpers/helpers-media-security-video.php'],
    ['inc/core/helpers/helpers-video-notifications.php', 'inc/core/helpers/helpers-media-security-video.php'],
    ['inc/core/helpers/helpers-content-serialization.php', 'inc/core/helpers/helpers-content-modules.php'],
    ['inc/core/helpers/helpers-page-modules.php', 'inc/core/helpers/helpers-content-modules.php'],
    ['inc/core/helpers/helpers-domain-url-migration.php', 'inc/core/helpers/helpers-url-i18n-routing.php'],
    ['inc/core/helpers/helpers-i18n-routing.php', 'inc/core/helpers/helpers-url-i18n-routing.php'],
  ].forEach(([wrapperFile, implementationFile]) => {
    const wrapperSource = readThemeFile(wrapperFile);
    const implementationBase = implementationFile.split('/').pop();
    [
      '@deprecated 2.5.8',
      '_deprecated_file( __FILE__,',
      implementationFile,
      `require_once __DIR__ . '/${implementationBase}';`,
    ].forEach((needle) => {
      assertContains(wrapperSource, needle, `Compatibility helper wrapper contract changed unexpectedly: ${wrapperFile} -> ${implementationFile}`);
    });
    if (/function\s+developer_starter_/i.test(wrapperSource)) {
      throw new Error(`Compatibility helper wrapper should not contain real helper implementations: ${wrapperFile}`);
    }
  });

  const urlRoutingHelper = readThemeFile('inc/core/helpers/helpers-url-i18n-routing.php');
  [
    'developer_starter_normalize_external_asset_allowed_hosts',
    'developer_starter_get_external_asset_allowed_hosts',
    'developer_starter_is_external_asset_url_allowed',
    'developer_starter_get_third_party_asset_registry',
    'developer_starter_get_third_party_asset',
    "'chart_js'",
    "'version'   => '2.7.2'",
    "'prism_js'",
  ].forEach((needle) => {
    assertContains(urlRoutingHelper, needle, `Third-party asset resolver contract changed unexpectedly: ${needle}`);
  });

  const adminSettingsConfig = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    'third_party_asset_allowed_hosts',
    '外部资源允许域名',
    '隐私与可用性提示',
    'CDN 兼容说明',
    '主题默认会信任常见真实 IP 请求头',
    '请使用启灵安全防护或其他专业安全插件处理',
  ].forEach((needle) => {
    assertContains(adminSettingsConfig, needle, `Admin settings notice contract changed unexpectedly: ${needle}`);
  });

  const adminSettingsSanitize = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  [
    'prism_css_cdn',
    'prism_js_cdn',
    'developer_starter_sanitize_external_asset_allowed_hosts',
  ].forEach((needle) => {
    assertContains(adminSettingsSanitize, needle, `External asset sanitization contract changed unexpectedly: ${needle}`);
  });

  const themeLicenseStoragePhp = readThemeFile('inc/core/class-theme-license.php');
  [
    "const OPTION_KEY = 'theme_license_key';",
    "const SETTINGS_OPTION = 'developer_starter_options';",
    'sync_license_key_storage( $license_key )',
    "get_option( self::SETTINGS_OPTION, array() )",
    "remove_action( 'update_option_' . self::OPTION_KEY",
    "update_option( self::OPTION_KEY, $license_key );",
    '$this->sync_license_key_storage( $license_key );',
  ].forEach((needle) => {
    assertContains(themeLicenseStoragePhp, needle, `Theme license key storage sync contract changed unexpectedly: ${needle}`);
  });

  collectPhpFiles().forEach((file) => {
    const source = readThemeFile(file);
    if ( /(^|[^A-Za-z0-9_])_e\s*\(/.test(source) ) {
      throw new Error(`${file} should use escaped translation output helpers instead of _e()`);
    }
    if ( /echo\s+(?:admin_url|rest_url|get_edit_post_link|get_permalink|get_category_link|get_tag_link|get_term_link|get_author_posts_url|home_url|site_url|wp_nonce_url|wp_create_nonce)\s*\(/.test(source) ) {
      throw new Error(`${file} should escape URL and nonce output before echoing it`);
    }
    if ( /\bthe_permalink\s*\(/.test(source) ) {
      throw new Error(`${file} should use esc_url( get_permalink() ) instead of direct the_permalink() output`);
    }
    if ( /\bthe_title\s*\(/.test(source) ) {
      throw new Error(`${file} should use esc_html( get_the_title() ) or get_the_title() instead of direct the_title() output`);
    }
    if ( /fetch\('\s*<\?php echo esc_url\(\s*(?:admin_url|rest_url)\s*\(/.test(source) ) {
      throw new Error(`${file} should JSON-encode URLs printed into JavaScript fetch() calls`);
    }
    if ( /\$[A-Za-z_]*(?:pass|password)[A-Za-z_]*\s*=\s*(?:(?!wp_unslash)[^;])*\$_POST\[['"](?:current_password|new_password|confirm_password|password|old_password|user_pass|pass1|pass2)['"]\](?:(?!wp_unslash)[^;])*;/s.test(source) ) {
      throw new Error(`${file} should wp_unslash() password POST fields before comparing or setting them`);
    }
    if ( /sanitize_[a-z_]+\(\s*(?:\(string\)\s*)?wp_unslash\(\s*\$_POST\[['"](?:current_password|new_password|confirm_password|password|old_password|user_pass|pass1|pass2)['"]\]/.test(source) ) {
      throw new Error(`${file} should not sanitize password POST fields with text sanitizers`);
    }
  });

  const singlePhp = readThemeFile('single.php');
  [
    "get_template_part( 'template-parts/single/header', null, $single_context );",
    "get_template_part( 'template-parts/single/content', null, $single_context );",
    "get_template_part( 'template-parts/single/modals', null, $single_context );",
    "get_template_part( 'template-parts/single/related', null, $single_context );",
    "get_template_part( 'template-parts/single/interaction-script', null, $single_context );",
    '$options = developer_starter_get_options_cache();',
  ].forEach((needle) => {
    assertContains(singlePhp, needle, `Single template orchestration changed unexpectedly: ${needle}`);
  });
  if (singlePhp.split(/\r?\n/).length > 240) {
    throw new Error('single.php should stay slim; move large markup into template-parts/single/');
  }
  assertMatches(
    singlePhp,
    /^(?:(?!<style>).)*$/s,
    'single.php should not reintroduce inline CSS blocks'
  );

  const commentsPhp = readThemeFile('comments.php');
  if (commentsPhp.split(/\r?\n/).length > 260) {
    throw new Error('comments.php should stay slim; keep comment styling in assets/css/comments.css');
  }
  assertMatches(
    commentsPhp,
    /^(?:(?!<style>).)*$/s,
    'comments.php should keep comment styling in assets/css/comments.css'
  );

  const headerPhp = readThemeFile('header.php');
  [
    "$header_search_use_rewrite = developer_starter_get_option( 'search_rewrite', '' );",
    "$header_menu_layout        = developer_starter_get_option( 'header_menu_layout', 'default' );",
  ].forEach((needle) => {
    assertContains(headerPhp, needle, `Header option access contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    headerPhp,
    /^(?:(?!function_exists\( 'developer_starter_get_option' \)).)*$/s,
    'header.php should rely on the theme bootstrap loading developer_starter_get_option() consistently'
  );

  const searchPhp = readThemeFile('search.php');
  [
    "$search_builder_enabled  = developer_starter_get_option( 'search_builder_enable', '' );",
    "$search_builder_page_id  = absint( developer_starter_get_option( 'search_builder_page_id', '' ) );",
    "$search_builder_position = sanitize_key( (string) developer_starter_get_option( 'search_builder_position', 'prepend_results' ) );",
    "$search_query_display = rawurldecode( (string) get_search_query( false ) );",
    '$search_results_count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;',
    '$search_scope = function_exists( \'developer_starter_get_current_search_scope\' ) ? developer_starter_get_current_search_scope() : \'all\';',
    '$search_scope_choices = function_exists( \'developer_starter_get_search_scope_choices\' ) ? developer_starter_get_search_scope_choices()',
    'esc_html( $search_query_display )',
    'esc_attr( $search_query_display )',
    'number_format_i18n( $search_results_count )',
    'name="search_scope"',
    'data-qiling-search-history',
    'data-qiling-search-input="1"',
    'developer_starter_highlight_search_terms',
    'search-highlight',
    "if ( developer_starter_get_option( 'search_rewrite', '' ) )",
  ].forEach((needle) => {
    assertContains(searchPhp, needle, `Search template option access contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    searchPhp,
    /^(?:(?!rawurldecode\( get_search_query\(\) \)).)*$/s,
    'search.php should not decode and render get_search_query() without context escaping'
  );

  const blogTemplatePhp = readThemeFile('templates/template-blog.php');
  assertContains(
    blogTemplatePhp,
    "echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) );",
    'Blog page template edit link should escape admin_url() before rendering'
  );
  assertMatches(
    blogTemplatePhp,
    /^(?:(?!echo admin_url\().)*$/s,
    'Blog page template should not echo raw admin_url() values'
  );
  assertMatches(
    blogTemplatePhp,
    /^(?:(?!echo get_permalink\().)*$/s,
    'Blog page template should not echo raw get_permalink() values'
  );
  assertMatches(
    blogTemplatePhp,
    /^(?:(?!echo get_(?:category|tag|term)_link\().)*$/s,
    'Blog page template taxonomy links should be escaped before rendering'
  );

  const careersTemplatePhp = readThemeFile('templates/template-careers.php');
  [
    "sanitize_text_field( (string) $careers_options['stat_1_label'] )",
    "sanitize_text_field( (string) $careers_options['stat_2_label'] )",
    "sanitize_text_field( (string) $careers_options['stat_3_label'] )",
    "echo esc_html( $job_types[ $pos->job_type ] ?? __( '全职', 'developer-starter' ) );",
    "echo esc_attr( wp_create_nonce( 'ds_careers_application_nonce' ) );",
  ].forEach((needle) => {
    assertContains(careersTemplatePhp, needle, `Careers template dynamic text escaping contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    careersTemplatePhp,
    /^(?:(?!__\(\s*\$).)*$/s,
    'Careers template should not pass database/config dynamic values directly into translation functions'
  );

  const notFoundPhp = readThemeFile('404.php');
  [
    'return developer_starter_get_option( $key, $default );',
    "if ( $get_error_404_option( 'error_404_builder_enable', '' ) && function_exists( 'developer_starter_render_builder_template_page' ) )",
    "if ( developer_starter_get_option( 'search_rewrite', '' ) )",
  ].forEach((needle) => {
    assertContains(notFoundPhp, needle, `404 template option access contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    notFoundPhp,
    /^(?:(?!function_exists\( 'developer_starter_get_option' \)).)*$/s,
    '404.php should use the shared theme option helper directly'
  );

  const authorPhp = readThemeFile('author.php');
  assertContains(
    authorPhp,
    "if ( developer_starter_get_option( 'post_views_enable', '' ) )",
    'Author template should use the shared theme option helper directly'
  );

  const assetsPhp = readThemeFile('inc/core/class-assets.php');
  [
    "add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_comments_styles' ), 30 );",
    'public function enqueue_comments_styles()',
    "'developer-starter-comments'",
    "DEVELOPER_STARTER_ASSETS . '/css/comments.css'",
    "if ( ! is_singular() || ( ! comments_open() && ! get_comments_number() ) )",
    "developer_starter_lazy_load_placeholder_enabled()",
    "'developer-starter-lazy-image-placeholder'",
    "DEVELOPER_STARTER_ASSETS . '/css/lazy-image-placeholder.css'",
    "DEVELOPER_STARTER_ASSETS . '/js/lazy-image-placeholder.js'",
    "wp_localize_script( 'developer-starter-lazy-image-placeholder', 'qilingLazyImagePlaceholderConfig'",
    "'darkMode' => function_exists( 'developer_starter_get_dark_mode_runtime_config' )",
    "'searchEnhance' => array(",
    "'storageKey' => 'qiling-search-history'",
    "'currentScope' => function_exists( 'developer_starter_get_current_search_scope' )",
    "'authFlowScript' => add_query_arg(",
    "'script' => add_query_arg(",
    "'authFlow' => array(",
    "'developer-starter-auth-flow'",
    "'developer-starter-auth-pages'",
    "DEVELOPER_STARTER_ASSETS . '/js/auth-flow.js'",
    "DEVELOPER_STARTER_ASSETS . '/js/auth-pages.js'",
    "DEVELOPER_STARTER_ASSETS . '/js/login-modal.js'",
    "array( 'developer-starter-auth-flow' )",
    "array( 'developer-starter-auth-pages' )",
  ].forEach((needle) => {
    assertContains(assetsPhp, needle, `Comment stylesheet enqueue contract changed unexpectedly: ${needle}`);
  });
  [
    'private $needs_blog_presets_cache = null;',
    'private $blog_presets_to_enqueue_cache = null;',
    'private function needs_blog_presets()',
    'private function enqueue_blog_preset_styles( $version )',
    'private function get_blog_presets_to_enqueue()',
    'private function get_blog_module_visual_preset( $module )',
    "$module['data']['blog_visual_preset']",
    "'developer_starter_blog_preset_module_types'",
    "'developer_starter_needs_blog_presets'",
    "'developer_starter_blog_presets_to_enqueue'",
    "array( 'blog' )",
  ].forEach((needle) => {
    assertContains(assetsPhp, needle, `Blog preset stylesheet should only be controlled by the dedicated blog preset gate: ${needle}`);
  });
  assertMatches(
    assetsPhp,
    /if \( \$this->needs_blog_presets\(\) \) {\s+\$this->enqueue_blog_preset_styles\( \$version \);/,
    'blog preset styles should not be tied to the broad module stylesheet gate'
  );

  const authFlowJs = readThemeFile('assets/js/auth-flow.js');
  [
    'window.DSAuthFlow = {',
    'refreshNonces: refreshNonces',
    'getDeviceFingerprint: getDeviceFingerprint',
    'ensureCaptcha: ensureCaptcha',
    'resetFormCaptcha: resetFormCaptcha',
  ].forEach((needle) => {
    assertContains(authFlowJs, needle, `Shared auth flow helper contract changed unexpectedly: ${needle}`);
  });

  const authMainJs = readThemeFile('assets/js/main.js');
  [
    'var loginModalAuthFlowScript = String(loginModalConfig.authFlowScript || \'\');',
    'var loginModalScript = String(loginModalConfig.script || \'\');',
    'function loadLoginModalAuthFlow()',
    'function loadLoginModalScript()',
    "window.DSLoadScriptOnce(loginModalAuthFlowScript, 'data-ds-auth-flow-script')",
    "window.DSLoadScriptOnce(loginModalScript, 'data-ds-login-modal-script')",
  ].forEach((needle) => {
    assertContains(authMainJs, needle, `Login modal auth-flow lazy loading contract changed unexpectedly: ${needle}`);
  });

  [
    'assets/js/login-modal.js',
    'assets/js/auth-pages.js',
  ].forEach((file) => {
    const source = readThemeFile(file);
    assertContains(source, 'window.DSAuthFlow.ensureCaptcha', `${file} should initialize auth captcha through shared auth-flow.js`);
    assertContains(source, 'window.DSAuthFlow.reset', `${file} should reset auth captcha through shared auth-flow.js`);
    assertMatches(
      source,
      /^(?:(?!formData\.append\('action', 'developer_starter_captcha_(?:challenge|verify)'\)).)*$/s,
      `${file} should not reintroduce duplicated captcha AJAX actions outside assets/js/auth-flow.js`
    );
    assertMatches(
      source,
      /^(?:(?!window\.DSProviderCaptcha\.attachAliyunCaptcha).)*$/s,
      `${file} should not directly attach Aliyun captcha outside assets/js/auth-flow.js`
    );
  });

  const loginModalPhp = readThemeFile('inc/core/class-login-modal.php');
  [
    'function developer_starter_ajax_get_login_modal()',
    "developer_starter_is_public_ajax_rate_limited( 'login_modal', 30, 60 )",
    'developer_starter_send_public_ajax_rate_limited()',
    'nocache_headers();',
    'type="application/json" id="ds-login-modal-config"',
  ].forEach((needle) => {
    assertContains(loginModalPhp, needle, `Login modal public AJAX guard changed unexpectedly: ${needle}`);
  });
  assertMatches(
    loginModalPhp,
    /^(?:(?!<style\b).)*$/s,
    'Login modal PHP should keep CSS in assets/css/login-modal.css'
  );
  const executableLoginModalScripts = loginModalPhp.match(/<script\b(?![^>]*type="application\/json"[^>]*id="ds-login-modal-config")[^>]*>/g) || [];
  if (executableLoginModalScripts.length) {
    throw new Error('Login modal PHP should not output executable inline scripts');
  }
  [
    'inc/core/class-login-modal.php',
    'templates/template-login.php',
    'templates/template-register.php',
    'templates/template-forgot-password.php',
  ].forEach((file) => {
    const source = readThemeFile(file);
    assertMatches(source, /^(?:(?!<style\b).)*$/s, `${file} should keep auth CSS in assets/css/auth.css or assets/css/login-modal.css`);
    assertMatches(source, /^(?:(?!\sstyle=).)*$/s, `${file} should not reintroduce inline style attributes`);
  });
  [
    'templates/template-login.php',
    'templates/template-register.php',
    'templates/template-forgot-password.php',
  ].forEach((file) => {
    const source = readThemeFile(file);
    assertContains(source, 'type="application/json" id="ds-auth-page-config"', `${file} should expose auth page config as inert JSON`);
    const executableScripts = source.match(/<script\b(?![^>]*type="application\/json"[^>]*id="ds-auth-page-config")[^>]*>/g) || [];
    if (executableScripts.length) {
      throw new Error(`${file} should not output executable inline auth scripts`);
    }
  });

  const categoryPhp = readThemeFile('category.php');
  assertMatches(
    categoryPhp,
    /^(?:(?!<style\b).)*$/s,
    'category.php should keep category styling in assets/css/category.css and wp_add_inline_style variables'
  );
  assertMatches(
    categoryPhp,
    /^(?:(?!\sstyle=).)*$/s,
    'category.php should not reintroduce inline style attributes'
  );
  [
    "wp_add_inline_style( 'developer-starter-category', $category_dynamic_css );",
    "'qiling-category-dynamic-grid'",
    "'qiling-category-has-header-bg'",
    "$post_adv_levels = get_post_meta( get_the_ID(), '_ds_adv_levels', true );",
    '$theme_options = developer_starter_get_options_cache();',
    "get_header();",
  ].forEach((needle) => {
    assertContains(categoryPhp, needle, `Category dynamic style contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    categoryPhp,
    /^(?:(?!\$adv_levels = get_post_meta\( get_the_ID\(\), '_ds_adv_levels', true \);).)*$/s,
    'category.php should not reuse $adv_levels for per-post advanced category metadata'
  );

  const categoryManagerPhp = readThemeFile('inc/core/class-category-manager.php');
  [
    '<input type="hidden" name="ds_category_fields_submitted" value="1" />',
    '<input type="hidden" name="ds_category_hide_breadcrumb" value="0" />',
    '<input type="hidden" name="ds_category_hide_thumb" value="0" />',
    '<input type="hidden" name="ds_category_hide_excerpt" value="0" />',
    '<input type="hidden" name="ds_category_hide_date" value="0" />',
    '<input type="hidden" name="ds_category_hide_category" value="0" />',
    '<input type="hidden" name="ds_category_hide_author" value="0" />',
    '<input type="hidden" name="ds_adv_filter_enabled" value="0" />',
    "add_action( 'created_term', array( $this, 'save_category_term_fields' ), 10, 3 );",
    "add_action( 'edited_term', array( $this, 'save_category_term_fields' ), 10, 3 );",
    "add_action( 'admin_init', array( $this, 'maybe_save_category_fields_from_admin_request' ) );",
    'public function save_category_term_fields( $term_id, $tt_id, $taxonomy )',
    'public function maybe_save_category_fields_from_admin_request()',
    "'editedtag' !== $action",
    "isset( $_POST['tag_ID'] )",
    "if ( 'category' !== $taxonomy ) {",
    'private function has_category_settings_payload()',
    "if ( isset( $_POST[ $field ] ) ) {",
    "update_term_meta( $term_id, 'ds_category_hide_breadcrumb', '0' );",
    "update_term_meta( $term_id, $field, '0' );",
    "update_term_meta( $term_id, 'ds_adv_filter_enabled', '0' );",
  ].forEach((needle) => {
    assertContains(categoryManagerPhp, needle, `Category term boolean persistence contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    categoryManagerPhp,
    /^(?:(?!delete_term_meta\( \$term_id, 'ds_category_hide_breadcrumb').)*$/s,
    'Category breadcrumb checkbox off state must persist as explicit 0, not deleted meta'
  );
  assertMatches(
    categoryManagerPhp,
    /^(?:(?!delete_term_meta\( \$term_id, \$field \)).)*$/s,
    'Category hide option checkbox off states must persist as explicit 0, not deleted meta'
  );

  const advancedCategoryMetaboxPhp = readThemeFile('inc/admin/class-advanced-category-metabox.php');
  [
    "$meta_prefix = '_ds_adv_';",
    'substr( $meta_key, strlen( $meta_prefix ) );',
    '! array_key_exists( $level_key, $levels )',
  ].forEach((needle) => {
    assertContains(advancedCategoryMetaboxPhp, needle, `Advanced category metabox cleanup contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    advancedCategoryMetaboxPhp,
    /^(?:(?!_ds_adv_level_).)*$/s,
    'Advanced category metabox should clean per-level meta using the same _ds_adv_ prefix used for writes'
  );
  assertMatches(
    advancedCategoryMetaboxPhp,
    /^(?:(?!substr\( \$meta_key, 8 \)).)*$/s,
    'Advanced category metabox cleanup should not use hard-coded prefix lengths'
  );

  const runtimePrefixMatchingPhp = readThemeFile('inc/core/helpers/helpers-runtime-optimizations.php');
  [
    'function developer_starter_runtime_normalize_path',
    'function developer_starter_runtime_request_path_candidates',
    "home_url( '/' )",
    "site_url( '/' )",
    '$prefix = developer_starter_runtime_normalize_path( $prefix );',
    'developer_starter_runtime_request_path_candidates( $request_path )',
    '0 === strpos( $candidate, $match_prefix )',
  ].forEach((needle) => {
    assertContains(runtimePrefixMatchingPhp, needle, `Runtime prefix matching contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    runtimePrefixMatchingPhp,
    /^(?:(?!\$position\s*=\s*strpos\( \$request_path, \$prefix \);).)*$/s,
    'Runtime prefix matching should not search for allowlist prefixes in arbitrary path segments'
  );
  assertMatches(
    runtimePrefixMatchingPhp,
    /^(?:(?!substr\( \$request_path, \$position - 1, 1 \)).)*$/s,
    'Runtime prefix matching should not accept middle-path matches by checking only the preceding slash'
  );

  const singleTemplateFiles = [
    'template-parts/single/header.php',
    'template-parts/single/meta-stats.php',
    'template-parts/single/content.php',
    'template-parts/single/related.php',
  ];
  singleTemplateFiles.forEach((file) => {
    const source = readThemeFile(file);
    assertMatches(
      source,
      /^(?:(?!\sstyle=).)*$/s,
      `${file} should not reintroduce inline style attributes`
    );
  });

  const relatedPhp = readThemeFile('template-parts/single/related.php');
  [
    "'single_related_v2_'",
    "'fields'                 => 'ids'",
    "'no_found_rows'          => true",
    "'tag__in'        => array_map( 'absint', $related_tags )",
    "'category__in'   => wp_list_pluck( $related_categories, 'term_id' )",
    "'single_related_random_candidate_ids'",
    'usort(',
    "gmdate( 'Ymd' )",
    "'post__in'            => ! empty( $related_post_ids ) ? $related_post_ids : array( 0 )",
    "'orderby'             => 'post__in'",
    "'single_related_posts'",
  ].forEach((needle) => {
    assertContains(relatedPhp, needle, `Related posts query contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    relatedPhp,
    /^(?:(?!\$related_args\['orderby'\]\s*=\s*'rand').)*$/s,
    'Single related posts should avoid expensive database ORDER BY RAND()'
  );

  const postEnhancerPhp = readThemeFile('inc/core/class-post-enhancer.php');
  [
    'private static function get_single_post_dynamic_css()',
    "wp_add_inline_style( 'developer-starter-article-enhance', $article_dynamic_css );",
    "add_action( 'wp_body_open', array( $this, 'render_reading_progress_bar' ) );",
    "private static function is_reading_progress_enabled()",
    "developer_starter_get_option( 'reading_progress_enable', '' )",
    "wp_enqueue_style(\n                'developer-starter-reading-progress'",
    "wp_enqueue_script(\n                'developer-starter-reading-progress'",
    "wp_localize_script( 'developer-starter-reading-progress', 'qilingReadingProgressConfig'",
    "'tocExpandLabel'",
    "'tocCollapseLabel'",
    "'tocMobileOpenLabel'",
    "'tocMobileCloseLabel'",
    "'tocMobileButtonText'",
    'private function guard_public_ajax_rate_limit(',
    "$this->guard_public_ajax_rate_limit( 'post_view_track', 120, 60 );",
    "$this->guard_public_ajax_rate_limit( 'post_poster_get_cache', 120, 60 );",
    "$this->guard_public_ajax_rate_limit( 'post_poster_save_cache', $poster_rate_max, $this->get_public_ajax_rate_limit_window() );",
    "$this->guard_public_ajax_rate_limit( 'post_poster_download', 60, 60, false );",
    'public function render_reading_progress_bar()',
    'id="qiling-reading-progress"',
    'class="article-toc article-toc--enhanced"',
    'id="article-toc-list"',
    'aria-controls="article-toc-list"',
    'data-toc-level=',
    'data-toc-target=',
    'class="copyright-icon-mark"',
    "'--qiling-single-header-bg'",
    'private static function sanitize_css_value(',
  ].forEach((needle) => {
    assertContains(postEnhancerPhp, needle, `Single post dynamic style contract changed unexpectedly: ${needle}`);
  });

  const articleEnhanceJs = readThemeFile('assets/js/article-enhance.js');
  [
    'createMobileToc: function ()',
    "document.getElementById('qiling-mobile-toc-panel')",
    'setMobilePanelOpen: function (open)',
    'setTocCollapsed: function (collapsed)',
    'setActiveHref: function (currentId)',
    "link.setAttribute('aria-current', 'true')",
    "document.body.classList.add('qiling-mobile-toc-ready')",
    "button.className = 'qiling-mobile-toc-toggle'",
    "panel.id = 'qiling-mobile-toc-panel'",
  ].forEach((needle) => {
    assertContains(articleEnhanceJs, needle, `Article TOC enhancement script contract changed unexpectedly: ${needle}`);
  });

  const articleEnhanceCss = readThemeFile('assets/css/article-enhance.css');
  [
    '.toc-item.toc-level-3::before',
    '.toc-item.toc-level-4::after',
    '.toc-link[aria-current="true"]',
    '.qiling-mobile-toc-toggle',
    '.qiling-mobile-toc-backdrop',
    '.qiling-mobile-toc-panel',
    'body.qiling-mobile-toc-ready .toc-before-content .article-toc',
    'body.qiling-mobile-toc-ready .qiling-mobile-toc-panel.is-open',
    '.copyright-icon-mark',
    '@media (prefers-reduced-motion: reduce)',
  ].forEach((needle) => {
    assertContains(articleEnhanceCss, needle, `Article TOC enhancement style contract changed unexpectedly: ${needle}`);
  });

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  assertContains(
    configPhp,
    "'reading_progress_enable', 'type' => 'checkbox', 'label' => __( '启用阅读进度条'",
    'Article settings should expose the reading progress feature toggle'
  );
  assertContains(
    configPhp,
    "'lazy_load_placeholder_enable', 'type' => 'checkbox', 'label' => __( '图片渐进式占位'",
    'Optimize settings should expose the progressive lazy-image placeholder toggle'
  );
  [
    "'darkmode_auto_enable', 'type' => 'checkbox', 'label' => __( '启用自动暗黑模式'",
    "'darkmode_auto_mode', 'type' => 'select', 'label' => __( '自动切换方式'",
    "'system_schedule' => __( '跟随系统，无法检测时按时间'",
    "'darkmode_sunrise_time', 'type' => 'text', 'input_type' => 'time'",
    "'darkmode_sunset_time', 'type' => 'text', 'input_type' => 'time'",
    "'darkmode_transition_enable', 'type' => 'checkbox', 'label' => __( '启用暗色切换动画'",
    "'darkmode_image_dim_enable', 'type' => 'checkbox', 'label' => __( '暗色模式图片调暗'",
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Auto dark mode settings contract changed unexpectedly: ${needle}`);
  });

  const readingProgressJs = readThemeFile('assets/js/reading-progress.js');
  [
    'window.qilingReadingProgressConfig',
    "document.getElementById('qiling-reading-progress')",
    "document.querySelector('.single-post')",
    "root.setAttribute('aria-valuenow', rounded)",
    "window.addEventListener('scroll', requestUpdate, { passive: true })",
  ].forEach((needle) => {
    assertContains(readingProgressJs, needle, `Reading progress script contract changed unexpectedly: ${needle}`);
  });

  const readingProgressCss = readThemeFile('assets/css/reading-progress.css');
  [
    '.qiling-reading-progress',
    '--qiling-reading-progress-percent',
    '.admin-bar .qiling-reading-progress',
    '.qiling-reading-progress__fill',
    '@media (prefers-reduced-motion: reduce)',
  ].forEach((needle) => {
    assertContains(readingProgressCss, needle, `Reading progress style contract changed unexpectedly: ${needle}`);
  });

  const mediaSecurityPhp = readThemeFile('inc/core/helpers/helpers-media-security-video.php');
  [
    'function developer_starter_lazy_load_placeholder_enabled()',
    "developer_starter_get_option( 'lazy_load_images', '' )",
    "developer_starter_get_option( 'lazy_load_placeholder_enable', '' )",
    "add_filter( 'body_class', 'developer_starter_lazy_load_placeholder_body_class' );",
    "developer_starter_add_image_tag_class( $tag, 'qiling-progressive-image' );",
    "developer_starter_add_image_tag_attribute( $tag, 'data-qiling-progressive-image', '1' );",
  ].forEach((needle) => {
    assertContains(mediaSecurityPhp, needle, `Lazy image placeholder PHP contract changed unexpectedly: ${needle}`);
  });

  const lazyImagePlaceholderJs = readThemeFile('assets/js/lazy-image-placeholder.js');
  [
    'window.qilingLazyImagePlaceholderConfig',
    'qiling-progressive-image',
    'qiling-image-pending',
    'qiling-image-loaded',
    'MutationObserver',
    "img.addEventListener('load'",
  ].forEach((needle) => {
    assertContains(lazyImagePlaceholderJs, needle, `Lazy image placeholder script contract changed unexpectedly: ${needle}`);
  });

  const lazyImagePlaceholderCss = readThemeFile('assets/css/lazy-image-placeholder.css');
  [
    'body.qiling-lazy-image-placeholders img.qiling-progressive-image',
    'qiling-lazy-image-shimmer',
    'qiling-image-pending',
    'qiling-image-loaded',
    '@media (prefers-reduced-motion: reduce)',
  ].forEach((needle) => {
    assertContains(lazyImagePlaceholderCss, needle, `Lazy image placeholder style contract changed unexpectedly: ${needle}`);
  });

  const functionsPhp = readThemeFile('functions.php');
  const pageCreatorBootstrapPhp = readThemeFile('inc/core/bootstrap-page-creators.php');
  const serviceBootstrapPhp = readThemeFile('inc/core/bootstrap-services.php');
  const bootstrapSources = [
    functionsPhp,
    pageCreatorBootstrapPhp,
    serviceBootstrapPhp,
  ].join('\n');
  const helperBootstrapPhp = readThemeFile('inc/core/helpers/bootstrap.php');
  [
    'function developer_starter_get_page_creator_class_file_map()',
    'function developer_starter_include_page_creator_base_file()',
    "require_once DEVELOPER_STARTER_INC . '/core/class-page-creator-base.php';",
    'function developer_starter_init_page_creators()',
    'foreach ( array_keys( developer_starter_get_page_creator_class_file_map() ) as $class )',
    'new $class();',
    'function developer_starter_get_thumbnail_optimizer_instance()',
    'static $thumbnail_optimizer = null;',
    'developer_starter_get_thumbnail_optimizer_instance();',
    "require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-country-flag.php';",
    "require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-blog-pagination.php';",
    "require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-category-base.php';",
  ].forEach((needle) => {
    assertContains(bootstrapSources, needle, `Page creator bootstrap contract changed unexpectedly: ${needle}`);
  });
  if (functionsPhp.split(/\r?\n/).length > 620) {
    throw new Error('functions.php should stay slim; move rewrite and helper logic into inc/core/helpers/');
  }
  assertContains(
    bootstrapSources,
    'developer_starter_preload_options_cache();',
    'Theme init should preload developer_starter_options into the shared in-request cache'
  );

  const clientIpHelperPhp = readThemeFile('inc/core/helpers/helpers-client-ip.php');
  [
    "require_once __DIR__ . '/helpers-client-ip.php';",
  ].forEach((needle) => {
    assertContains(helperBootstrapPhp, needle, `Client IP helper bootstrap contract changed unexpectedly: ${needle}`);
  });
  [
    'function developer_starter_get_client_ip()',
    'function developer_starter_resolve_client_ip( $callback = null )',
    'developer_starter_should_trust_forwarded_headers',
    'developer_starter_parse_ip_candidates',
    "'developer_starter_client_ip_mode'",
    'HTTP_X_FORWARDED_FOR',
  ].forEach((needle) => {
    assertContains(clientIpHelperPhp, needle, `Client IP helper contract changed unexpectedly: ${needle}`);
  });

  const contentModulesHelperPhp = readThemeFile('inc/core/helpers/helpers-content-modules.php');
  assertMatches(
    contentModulesHelperPhp,
    /^(?:(?!function developer_starter_get_icon\().)*$/s,
    'Dead SVG icon helper should not be restored without a real assets/images/icons manifest'
  );
  assertMatches(
    contentModulesHelperPhp,
    /^(?:(?!assets\/images\/icons).)*$/s,
    'Content module helpers should not read the removed assets/images/icons directory'
  );
  [
    'inc/core/class-auth-manager.php',
    'inc/core/class-auth-flow-service.php',
    'inc/core/class-auth-captcha-service.php',
    'inc/core/class-auth-register-email-service.php',
    'inc/core/class-message-manager.php',
    'inc/core/class-careers-manager.php',
    'inc/core/class-account-deletion-manager.php',
    'inc/core/class-sms-manager.php',
    'inc/core/class-category-tabs-ajax.php',
    'inc/admin/class-user-columns.php',
  ].forEach((file) => {
    const source = readThemeFile(file);
    assertMatches(
      source,
      /^(?:(?!private function get_client_ip\(\)).)*$/s,
      `${file} should use the shared client IP helper instead of a private wrapper`
    );
    assertMatches(
      source,
      /^(?:(?!function_exists\(\s*'developer_starter_get_client_ip'\s*\)).)*$/s,
      `${file} should not keep local developer_starter_get_client_ip() fallbacks`
    );
  });

  const pageCreatorBasePhp = readThemeFile('inc/core/class-page-creator-base.php');
  [
    'abstract class Page_Creator_Base',
    "protected const TEMPLATE = '';",
    "protected const AJAX_ACTION = '';",
    "protected const FILLED_META_KEY = '';",
    "add_action( 'save_post', array( $this, 'on_page_save' ), 99, 2 );",
    "add_action( 'wp_ajax_' . $ajax_action, array( $this, 'ajax_fill_modules' ) );",
    "check_ajax_referer( $this->get_nonce_action(), 'nonce' );",
    'protected function page_has_modules( $post_id )',
    'protected function fill_page_modules( $post_id )',
    'public function set_default_modules( $page_id )',
    'protected function get_default_modules( $page_id )',
    'protected function persist_default_modules( $page_id, $modules )',
    'public static function persist_default_modules_for_creator',
    'developer_starter_page_creator_default_modules',
  ].forEach((needle) => {
    assertContains(pageCreatorBasePhp, needle, `Page creator base contract changed unexpectedly: ${needle}`);
  });

  const pageCreatorFiles = readdirSync(themePath('inc/core'))
    .filter((file) => /-page-creator\.php$/.test(file));
  const singleTemplateCreatorFiles = pageCreatorFiles
    .filter((file) => file !== 'class-industry-preset-page-creator.php');
  const migratedCreators = singleTemplateCreatorFiles.filter((file) => {
    const content = readThemeFile(`inc/core/${file}`);
    return content.includes('extends Page_Creator_Base')
      && content.includes('protected const TEMPLATE =')
      && content.includes('protected const AJAX_ACTION =')
      && content.includes('protected const FILLED_META_KEY =')
      && !content.includes("add_action( 'save_post'")
      && !content.includes('wp_ajax_');
  });
  if (migratedCreators.length !== singleTemplateCreatorFiles.length) {
    throw new Error('Single-template page creators should use Page_Creator_Base constants instead of duplicating save/AJAX hooks');
  }
  const baseBackedCreatorsWithDirectDefaultMetaWrites = singleTemplateCreatorFiles.filter((file) => {
    const content = readThemeFile(`inc/core/${file}`);
    return content.includes('extends Page_Creator_Base')
      && content.includes("update_post_meta( $page_id, '_developer_starter_modules'");
  });
  if (baseBackedCreatorsWithDirectDefaultMetaWrites.length > 0) {
    throw new Error(`Page creators should return default module manifests and let Page_Creator_Base persist them: ${baseBackedCreatorsWithDirectDefaultMetaWrites.join(', ')}`);
  }
  const baseBackedCreatorsWithoutDefaultGetter = singleTemplateCreatorFiles.filter((file) => {
    const content = readThemeFile(`inc/core/${file}`);
    return content.includes('extends Page_Creator_Base')
      && !content.includes('protected function get_default_modules( $page_id )');
  });
  if (baseBackedCreatorsWithoutDefaultGetter.length > 0) {
    throw new Error(`Page creators should expose default modules through get_default_modules(): ${baseBackedCreatorsWithoutDefaultGetter.join(', ')}`);
  }
  const defaultModuleCreatorFiles = [...pageCreatorFiles, 'class-homepage-creator.php'];
  const creatorsWithDirectDefaultModuleWrites = defaultModuleCreatorFiles.filter((file) => {
    const content = readThemeFile(`inc/core/${file}`);
    return content.includes("update_post_meta( $page_id, '_developer_starter_modules', $default_modules")
      || content.includes("update_post_meta( $post_id, '_developer_starter_modules', $this->get_preset_modules");
  });
  if (creatorsWithDirectDefaultModuleWrites.length > 0) {
    throw new Error(`Page creators should use the shared default module persister instead of direct default meta writes: ${creatorsWithDirectDefaultModuleWrites.join(', ')}`);
  }

  const expectedThemeVersion = '2.5.7';
  [
    ['functions.php', `define( 'DEVELOPER_STARTER_VERSION', '${expectedThemeVersion}' );`],
    ['functions.php', `define( 'DEVELOPER_STARTER_DB_VERSION', '${expectedThemeVersion}' );`],
    ['style.css', `Version: ${expectedThemeVersion}`],
    ['dev/phpstan/bootstrap.php', `'DEVELOPER_STARTER_VERSION'    => '${expectedThemeVersion}',`],
    ['dev/phpstan/bootstrap.php', `'DEVELOPER_STARTER_DB_VERSION' => '${expectedThemeVersion}',`],
    ['languages/developer-starter.pot', `Project-Id-Version: Qi Ling Theme ${expectedThemeVersion}`],
    ['languages/developer-starter-zh_CN.po', `Project-Id-Version: Qi Ling Theme ${expectedThemeVersion}`],
    ['languages/developer-starter-en_US.po', `Project-Id-Version: Qi Ling Theme ${expectedThemeVersion}`],
    ['../启灵主题文档/README.md', `主题版本：\`${expectedThemeVersion}\``],
    ['../启灵主题文档/04-发布与运维/兼容性与发布检查.md', `Version: ${expectedThemeVersion}`],
    ['../启灵主题文档/04-发布与运维/发布维护手册.md', `主题版本：\`${expectedThemeVersion}\``],
    ['../启灵主题文档/03-开发文档/开发入口与架构.md', `主题版本：\`${expectedThemeVersion}\``],
    ['../启灵主题文档/03-开发文档/页面与数据包开发文档.md', `当前主题版本为 \`${expectedThemeVersion}\``],
    ['../启灵主题文档/03-开发文档/主题开发文档.md', `主题版本: \`${expectedThemeVersion}\``],
  ].forEach(([file, needle]) => {
    assertContains(readThemeFile(file), needle, `${file} should advertise Qi Ling theme version ${expectedThemeVersion}`);
  });
  [
    'functions.php',
    'style.css',
    'dev/phpstan/bootstrap.php',
    'languages/developer-starter.pot',
    'languages/developer-starter-zh_CN.po',
    'languages/developer-starter-en_US.po',
    '../启灵主题文档/README.md',
    '../启灵主题文档/04-发布与运维/兼容性与发布检查.md',
    '../启灵主题文档/04-发布与运维/发布维护手册.md',
    '../启灵主题文档/03-开发文档/开发入口与架构.md',
    '../启灵主题文档/03-开发文档/页面与数据包开发文档.md',
    '../启灵主题文档/03-开发文档/主题开发文档.md',
  ].forEach((file) => {
    assertMatches(
      readThemeFile(file),
      /^(?:(?!2\.5\.(?:0|5|6)).)*$/s,
      `${file} should not keep stale Qi Ling theme version stamps`
    );
  });
  const enUsPo = readThemeFile('languages/developer-starter-en_US.po');
  let activeMsgstr = '';
  const assertEnglishMsgstr = () => {
    if (activeMsgstr && /[\u3400-\u9fff]/.test(activeMsgstr)) {
      throw new Error('developer-starter-en_US.po should not contain Chinese text inside msgstr entries');
    }
  };
  enUsPo.split(/\r?\n/).forEach((line) => {
    if (/^msgstr(?:\[[0-9]+\])? /.test(line)) {
      assertEnglishMsgstr();
      activeMsgstr = line;
      return;
    }
    if (activeMsgstr && line.startsWith('"')) {
      activeMsgstr += `\n${line}`;
      return;
    }
    assertEnglishMsgstr();
    activeMsgstr = '';
  });
  assertEnglishMsgstr();
  assertMatches(
    functionsPhp,
    /^(?:(?!global \$developer_starter_thumbnail_optimizer).)*$/s,
    'Thumbnail optimizer should be accessed through developer_starter_get_thumbnail_optimizer_instance(), not a global variable'
  );
  assertMatches(
    functionsPhp,
    /^(?:(?!new Developer_Starter\\Core\\(?:[A-Za-z]+_)*[A-Za-z]+(?:_Page)?_Creator\(\);).)*$/s,
    'Page creators should be instantiated from developer_starter_get_page_creator_class_file_map(), not duplicated as manual new statements'
  );
  assertMatches(
    functionsPhp,
    /^(?:(?!\$wp_rewrite->flush_rules\(\);).)*$/s,
    'Category and option updates should queue rewrite flushes instead of calling $wp_rewrite->flush_rules() synchronously'
  );
  [
    'function developer_starter_country_to_flag(',
    'function developer_starter_get_blog_page_pagination_slugs(',
    'function developer_starter_remove_category_base_init(',
  ].forEach((needle) => {
    assertMatches(
      functionsPhp,
      new RegExp(`^(?:(?!${needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}).)*$`, 's'),
      `functions.php should load ${needle} from helper files rather than defining it inline`
    );
  });

  const countryFlagPhp = readThemeFile('inc/core/helpers/helpers-country-flag.php');
  [
    'function developer_starter_country_to_flag( $country_code )',
    "html_entity_decode( '&#' . $first . ';&#' . $second . ';', ENT_NOQUOTES, 'UTF-8' );",
  ].forEach((needle) => {
    assertContains(countryFlagPhp, needle, `Country flag conversion contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    countryFlagPhp,
    /^(?:(?!HTML-ENTITIES).)*$/s,
    'Country flag conversion should avoid the deprecated HTML-ENTITIES mb_convert_encoding source encoding'
  );

  const blogPaginationPhp = readThemeFile('inc/core/helpers/helpers-blog-pagination.php');
  [
    'function developer_starter_get_blog_page_pagination_slugs( $force_refresh = false )',
    "add_action( 'init', 'developer_starter_blog_page_pagination_support', 1 );",
    'function developer_starter_queue_rewrite_rules_flush()',
    "update_option( 'developer_starter_flush_rules', '1', false );",
    "add_action( 'save_post', 'developer_starter_flush_blog_page_rules' );",
    "add_action( 'before_delete_post', 'developer_starter_flush_blog_page_rules_on_delete' );",
    'function developer_starter_delayed_flush_rules()',
    'flush_rewrite_rules();',
    "add_action( 'init', 'developer_starter_delayed_flush_rules', 999 );",
  ].forEach((needle) => {
    assertContains(blogPaginationPhp, needle, `Blog pagination helper contract changed unexpectedly: ${needle}`);
  });

  const categoryBasePhp = readThemeFile('inc/core/helpers/helpers-category-base.php');
  [
    'function developer_starter_remove_category_base_init()',
    "add_action( 'init', 'developer_starter_remove_category_base_init', 1 );",
    'function developer_starter_category_rewrite_rules( $category_rewrite )',
    "add_filter( 'category_rewrite_rules', 'developer_starter_category_rewrite_rules' );",
    'function developer_starter_category_query_vars( $public_query_vars )',
    "add_filter( 'query_vars', 'developer_starter_category_query_vars' );",
    'function developer_starter_category_redirect( $query_vars )',
    "add_filter( 'request', 'developer_starter_category_redirect' );",
    'function developer_starter_refresh_category_rules()',
    "add_action( 'created_category', 'developer_starter_refresh_category_rules' );",
    "add_action( 'delete_category', 'developer_starter_refresh_category_rules' );",
    "add_action( 'edited_category', 'developer_starter_refresh_category_rules' );",
    'function developer_starter_flush_rewrite_on_save( $old_value, $new_value )',
    "add_action( 'update_option_developer_starter_options', 'developer_starter_flush_rewrite_on_save', 10, 2 );",
  ].forEach((needle) => {
    assertContains(categoryBasePhp, needle, `Category base helper contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    categoryBasePhp,
    /^(?:(?!\$wp_rewrite->flush_rules\(\);).)*$/s,
    'Category helper should queue rewrite flushes instead of calling $wp_rewrite->flush_rules() synchronously'
  );
  assertMatches(
    functionsPhp,
    /^(?:(?!页面创建器文件按需加载（见 developer_starter_include_page_creators_files）。).)*$/s,
    'functions.php should not reintroduce the stale duplicate page-creator load comments'
  );
  assertMatches(
    functionsPhp,
    /^(?:(?!作者页面辅助函数).)*$/s,
    'functions.php should not keep the empty author helper section'
  );
  assertMatches(
    functionsPhp,
    /^(?:(?!\r?\n[ \t]*\r?\n[ \t]*\r?\n).)*$/s,
    'functions.php should avoid excessive consecutive blank lines'
  );

  const thumbnailHelpersPhp = readThemeFile('inc/core/helpers/helpers-thumbnail.php');
  assertContains(
    thumbnailHelpersPhp,
    'developer_starter_get_thumbnail_optimizer_instance()',
    'Thumbnail helper should resolve the optimizer through the controlled accessor'
  );
  assertMatches(
    thumbnailHelpersPhp,
    /^(?:(?!global \$developer_starter_thumbnail_optimizer).)*$/s,
    'Thumbnail helper should not read the optimizer from a global variable'
  );

  const thumbnailOptimizerPhp = readThemeFile('inc/core/class-thumbnail-optimizer.php');
  [
    'private function get_upload_relative_path( $url, $base_url )',
    'private function is_local_image( $url )',
    'return false !== $this->get_upload_relative_path( $url, (string) $upload_dir[\'baseurl\'] );',
    'private function url_to_path( $url )',
    '$relative_path = $this->get_upload_relative_path( $url, $base_url );',
    'wp_parse_url( $url )',
    'wp_parse_url( $base_url )',
    'rawurldecode',
    'realpath( $base_dir )',
    'realpath( $candidate_path )',
    'wp_normalize_path',
    'strncmp( $candidate_real, $uploads_real . \'/\', strlen( $uploads_real ) + 1 )',
  ].forEach((needle) => {
    assertContains(thumbnailOptimizerPhp, needle, `Thumbnail optimizer local URL mapping contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    thumbnailOptimizerPhp,
    /^(?:(?!strpos\( \$url, \$site_url \)).)*$/s,
    'Thumbnail optimizer should not classify local images by searching for the site URL substring'
  );
  assertMatches(
    thumbnailOptimizerPhp,
    /^(?:(?!strpos\( \$url, \$base_url \)).)*$/s,
    'Thumbnail optimizer should parse and compare upload URL parts instead of searching for the base URL substring'
  );
  assertMatches(
    thumbnailOptimizerPhp,
    /^(?:(?!wp-content\\\/uploads\\\/\(\.\+\)).)*$/s,
    'Thumbnail optimizer should not map arbitrary URLs by a loose wp-content/uploads regex'
  );

  const bootstrapPhp = readThemeFile('inc/core/helpers/bootstrap.php');
  [
    "require_once __DIR__ . '/helpers-multibyte-polyfill.php';",
    "require_once __DIR__ . '/helpers-dark-mode.php';",
  ].forEach((needle) => {
    assertContains(bootstrapPhp, needle, `Helper bootstrap contract changed unexpectedly: ${needle}`);
  });

  const darkModeHelperPhp = readThemeFile('inc/core/helpers/helpers-dark-mode.php');
  [
    'function developer_starter_normalize_dark_mode_time( $value, $default = \'00:00\' )',
    'function developer_starter_get_dark_mode_runtime_config()',
    "developer_starter_get_option( 'darkmode_auto_enable', '' )",
    "developer_starter_get_option( 'darkmode_auto_mode', 'system_schedule' )",
    "developer_starter_get_option( 'darkmode_sunrise_time', '06:00' )",
    "developer_starter_get_option( 'darkmode_sunset_time', '18:00' )",
    "'storageKey'       => 'qiling-theme-preference'",
  ].forEach((needle) => {
    assertContains(darkModeHelperPhp, needle, `Dark mode runtime helper contract changed unexpectedly: ${needle}`);
  });

  const authOptionsPhp = readThemeFile('inc/core/helpers/helpers-auth-options.php');
  [
    'function developer_starter_get_options_cache( $force_refresh = false )',
    "get_option( 'developer_starter_options', array() )",
    'is_array( $stored_options ) ? $stored_options : array()',
    'function developer_starter_preload_options_cache()',
    'developer_starter_get_options_cache();',
    'function developer_starter_refresh_options_cache()',
    'developer_starter_get_options_cache( true );',
    "add_action( 'update_option_developer_starter_options', 'developer_starter_refresh_options_cache', 1 );",
    '$options = developer_starter_get_options_cache();',
  ].forEach((needle) => {
    assertContains(authOptionsPhp, needle, `Theme option cache contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    authOptionsPhp,
    /^(?:(?!static \$options = null;\s*if \( \$options === null \)).)*$/s,
    'developer_starter_get_option() should use developer_starter_get_options_cache(), not its own isolated static cache'
  );

  const debugToolsPhp = readThemeFile('inc/core/helpers/helpers-debug-tools.php');
  assertContains(
    debugToolsPhp,
    'developer_starter_refresh_options_cache();',
    'Theme option reset hook should refresh the shared in-request option cache'
  );

  [
    'inc/widgets/class-widget-contact.php',
    'inc/widgets/class-widget-social.php',
  ].forEach((file) => {
    const source = readThemeFile(file);
    assertContains(
      source,
      '\\developer_starter_get_options_cache();',
      `${file} should read theme options from the shared in-request cache`
    );
  });

  const polyfillPhp = readThemeFile('inc/core/helpers/helpers-multibyte-polyfill.php');
  [
    'developer_starter_mb_polyfill_chars',
    'function mb_strlen(',
    'function mb_substr(',
    'function mb_strtolower(',
    "preg_split( '//u', $string, -1, PREG_SPLIT_NO_EMPTY )",
  ].forEach((needle) => {
    assertContains(polyfillPhp, needle, `mbstring polyfill contract changed unexpectedly: ${needle}`);
  });

  const maintenancePhp = readThemeFile('inc/core/helpers/helpers-maintenance.php');
  [
    'developer_starter_maintenance_get_option',
    "function_exists( 'developer_starter_get_option' )",
    "get_option( 'developer_starter_options', array() )",
    "developer_starter_maintenance_get_option( 'disable_default_thumbnails', '' )",
    "developer_starter_maintenance_get_option( 'disable_image_sizes', '' )",
    'developer_starter_get_cleanup_rest_nonce',
    'developer_starter_get_cleanup_cron_token',
    'developer_starter_sanitize_cleanup_cron_token',
    'developer_starter_cleanup_cron_permission',
    'wp_verify_nonce( $nonce, \'wp_rest\' )',
    "current_user_can( 'manage_options' )",
    'developer_starter_cleanup_rest_is_rate_limited',
    'developer_starter_add_cleanup_rest_audit_log',
    'developer_starter_clear_cleanup_rest_audit_log',
    'hash_equals( $configured_token, $provided_token )',
    'developer_starter_is_trusted_proxy_ip( $remote_addr )',
    "'methods'             => WP_REST_Server::CREATABLE",
    "'/maintenance/cleanup/cron'",
    "'methods'             => array( WP_REST_Server::READABLE, WP_REST_Server::CREATABLE )",
  ].forEach((needle) => {
    assertContains(maintenancePhp, needle, `Maintenance option fallback contract changed unexpectedly: ${needle}`);
  });

  const ajaxProductLoaderPhp = readThemeFile('inc/core/class-ajax-product-loader.php');
  [
    'developer_starter_ajax_product_content_allowed_post_types',
    "array( 'post', 'ql_product' )",
    'is_allowed_product_for_module_source',
    'get_allowed_product_ids_for_request',
    'extract_allowed_post_ids_from_products_data',
    'build_module_key',
    'hash_equals( $expected_key, $module_key )',
    "sanitize_key( (string) $module_data['type'] )",
    "'products' !== $module_type",
    'is_post_publicly_viewable',
    'post_password_required( $source )',
  ].forEach((needle) => {
    assertContains(ajaxProductLoaderPhp, needle, `Product AJAX content loader boundary changed unexpectedly: ${needle}`);
  });

  const productsModulePhp = readThemeFile('inc/modules/modules/class-products-module.php');
  [
    'extract_allowed_post_ids_from_products_data( $data )',
    'build_module_key( $source_post_id, $allowed_product_post_ids )',
    "payload.set('source_id', String(productSourceId || ''));",
    "payload.set('module_key', productModuleKey || '');",
    'private function resolve_source_post_id()',
  ].forEach((needle) => {
    assertContains(productsModulePhp, needle, `Products module should bind AJAX requests to its source config: ${needle}`);
  });

  const ipLocationPhp = readThemeFile('inc/core/class-ip-location.php');
  [
    'function developer_starter_ip_location_http_user_agent()',
    'function developer_starter_ip_location_http_args( $args = array() )',
    "apply_filters( 'developer_starter_ip_location_http_timeout', 8 )",
    "apply_filters( 'developer_starter_ip_location_http_redirection', 1 )",
    "'redirection'          => $redirection",
    "'user-agent'           => developer_starter_ip_location_http_user_agent()",
    "'reject_unsafe_urls'   => true",
    "'limit_response_size'  => 1024 * 1024",
    "'Accept' => 'application/json, text/plain, */*'",
    'function developer_starter_http_get_json_response( $url, $args = array() )',
    'wp_remote_get( $url, developer_starter_ip_location_http_args( $args ) )',
    'wp_remote_retrieve_response_code( $response )',
    '$status_code < 200 || $status_code >= 300',
    'function developer_starter_ip_location_get_cached_json( $provider, $ip, $url, $args = array() )',
    'function developer_starter_ip_cache_dir()',
    'wp_upload_dir( null, false )',
    "trailingslashit( $uploads_dir ) . 'cache'",
    "trailingslashit( $theme_cache_dir ) . 'ip-location'",
    'developer_starter_ip_cache_ensure_index_files',
    "'index.html'",
    'function developer_starter_ip_cache_ttl()',
    "apply_filters( 'developer_starter_ip_cache_ttl', HOUR_IN_SECONDS )",
    'function developer_starter_ip_cache_file( $provider, $ip )',
    "hash_hmac( 'sha256', $provider . '|' . $ip, wp_salt( 'auth' ) )",
    'function developer_starter_ip_cache_path_allowed( $cache_file )',
    'function developer_starter_ip_cache_clear( $max_age = 0 )',
    'function developer_starter_cleanup_ip_cache()',
    'wp_schedule_event( time() + HOUR_IN_SECONDS, \'hourly\', \'developer_starter_clean_ip_location_cache\' )',
    "add_action( 'developer_starter_clean_ip_location_cache', 'developer_starter_cleanup_ip_cache' );",
    'developer_starter_ip_cache_read( $cache_file, developer_starter_ip_cache_ttl() )',
    "developer_starter_ip_cache_write( $cache_file, (string) $result['body'] );",
    'return developer_starter_ip_location_get_cached_json(',
    "'Referer' => 'https://apimobile.meituan.com/'",
    "return developer_starter_ip_location_get_cached_json( 'ipinfo', $ip, $url );",
  ].forEach((needle) => {
    assertContains(ipLocationPhp, needle, `IP location cache contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    ipLocationPhp,
    /^(?:(?!ABSPATH \. 'wp-content\/ip_cache').)*$/s,
    'IP location cache should not write to the public wp-content/ip_cache directory'
  );
  assertMatches(
    ipLocationPhp,
    /^(?:(?!md5\( \$ip \)).)*$/s,
    'IP location cache filenames should not be guessable md5(IP) values'
  );
  assertMatches(
    ipLocationPhp,
    /^(?:(?!curl_(?:init|setopt|exec|getinfo|close|errno)).)*$/s,
    'IP location lookups should use the WordPress HTTP API instead of native cURL'
  );
  assertMatches(
    ipLocationPhp,
    /^(?:(?!CURLOPT_).)*$/s,
    'IP location lookups should not use CURLOPT_* options'
  );

  const messageManagerPhp = readThemeFile('inc/core/class-message-manager.php');
  [
    'private function get_post_value',
    'private function get_query_value',
    'private function get_server_value',
    'private function get_request_value',
    'private function sanitize_request_array',
    'wp_unslash( $source[ $key ] )',
    "case 'nonce':",
    "case 'url':",
    "if ( 'array' === $type )",
    "$nonce = $this->get_post_value( 'nonce', 'nonce' );",
    "$name = $this->get_post_value( 'name', 'text' );",
    "$email = $this->get_post_value( 'email', 'email' );",
    "$message = $this->get_post_value( 'message', 'textarea' );",
    "$action = $this->get_query_value( 'action', 'key' );",
    "$paged = max( 1, (int) $this->get_query_value( 'paged', 'absint', 1 ) );",
  ].forEach((needle) => {
    assertContains(messageManagerPhp, needle, `Message manager request helper contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    messageManagerPhp,
    /^(?:(?!wp_verify_nonce\(\s*\$_POST).)*$/s,
    'Message manager nonce checks should go through the request helper'
  );
  assertMatches(
    messageManagerPhp,
    /^(?:(?!sanitize_text_field\(\s*isset\(\s*\$_POST).)*$/s,
    'Message manager POST text fields should go through the request helper'
  );
  assertMatches(
    messageManagerPhp,
    /^(?:(?!\$_GET\['(?:action|_wpnonce|id|paged)'\]).)*$/s,
    'Message manager admin query fields should go through the request helper'
  );

  const databaseSchemaMigrationServicePhp = readThemeFile('inc/core/class-database-schema-migration-service.php');
  [
    'class Database_Schema_Migration_Service',
    'public static function can_run_admin_migration()',
    "return ! wp_doing_ajax() && current_user_can( 'manage_options' );",
    'public static function run( $args )',
    "add_option( $lock_option, time(), '', false )",
    'update_option( $version_option, $target_version, false );',
    "require_once ABSPATH . 'wp-admin/includes/upgrade.php';",
    'dbDelta( $schema );',
    "'version_less' === $compare",
  ].forEach((needle) => {
    assertContains(databaseSchemaMigrationServicePhp, needle, `Database schema migration service contract changed unexpectedly: ${needle}`);
  });

  [
    {
      file: 'inc/core/class-message-manager.php',
      switchHook: "add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );",
      adminHook: "add_action( 'admin_init', array( $this, 'maybe_create_table' ) );",
      lockConstant: "const TABLE_MIGRATION_LOCK = 'developer_starter_message_table_migration_lock';",
      versionOption: "'version_option'     => self::TABLE_VERSION_OPTION,",
      targetVersion: "'target_version'     => self::TABLE_VERSION,",
      applySchema: 'Database_Schema_Migration_Service::apply_schema( $this->get_table_schema() );',
      initHookPattern: /add_action\(\s*'init'\s*,\s*array\(\s*\$this\s*,\s*'maybe_create_table'\s*\)\s*\)/,
    },
    {
      file: 'inc/core/class-careers-manager.php',
      switchHook: "add_action( 'after_switch_theme', array( $this, 'install_tables' ), 10, 0 );",
      adminHook: "add_action( 'admin_init', array( $this, 'maybe_create_tables' ) );",
      lockConstant: "const TABLE_MIGRATION_LOCK = 'developer_starter_careers_table_migration_lock';",
      versionOption: "'version_option'     => self::TABLE_VERSION_OPTION,",
      targetVersion: "'target_version'     => self::TABLE_VERSION,",
      applySchema: 'Database_Schema_Migration_Service::apply_schema( $this->get_table_schemas() );',
      initHookPattern: /add_action\(\s*'init'\s*,\s*array\(\s*\$this\s*,\s*'maybe_create_tables'\s*\)\s*\)/,
    },
    {
      file: 'inc/core/class-post-interaction-manager.php',
      switchHook: "add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );",
      adminHook: "add_action( 'admin_init', array( $this, 'maybe_create_table' ) );",
      lockConstant: "const TABLE_MIGRATION_LOCK = 'developer_starter_post_interaction_table_migration_lock';",
      versionOption: "'version_option'     => self::TABLE_VERSION_OPTION,",
      targetVersion: "'target_version'     => self::TABLE_VERSION,",
      applySchema: 'Database_Schema_Migration_Service::apply_schema( self::get_table_schema() );',
      initHookPattern: /add_action\(\s*'init'\s*,\s*array\(\s*\$this\s*,\s*'maybe_create_table'\s*\)\s*\)/,
    },
    {
      file: 'inc/core/class-notification-manager.php',
      switchHook: "add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );",
      adminHook: "add_action( 'admin_init', array( $this, 'maybe_create_table' ) );",
      lockConstant: "const TABLE_MIGRATION_LOCK = 'developer_starter_notification_table_migration_lock';",
      versionOption: "'version_option'     => self::TABLE_VERSION_OPTION,",
      targetVersion: "'target_version'     => $this->db_version,",
      applySchema: 'Database_Schema_Migration_Service::apply_schema( $this->get_table_schema() );',
      initHookPattern: /add_action\(\s*'init'\s*,\s*array\(\s*\$this\s*,\s*'maybe_create_table'\s*\)\s*\)/,
    },
    {
      file: 'inc/core/class-id-verification-manager.php',
      switchHook: "add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );",
      adminHook: "add_action( 'admin_init', array( $this, 'check_upgrade_schema' ) );",
      lockConstant: "const DB_MIGRATION_LOCK = 'qiling_id_verification_db_migration_lock';",
      versionOption: "'version_option'     => self::DB_VERSION_OPTION,",
      targetVersion: "'target_version'     => self::DB_VERSION,",
      applySchema: 'Database_Schema_Migration_Service::apply_schema( self::get_table_schema() );',
      extraNeedles: ["'version_compare'    => 'version_less',"],
      initHookPattern: /add_action\(\s*'init'\s*,\s*array\(\s*\$this\s*,\s*'check_upgrade_schema'\s*\)\s*\)/,
      directSchemaPattern: /\$this->check_upgrade_schema\(\);/,
    },
    {
      file: 'inc/core/class-account-deletion-manager.php',
      switchHook: "add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );",
      adminHook: "add_action( 'admin_init', array( $this, 'maybe_create_table' ) );",
      lockConstant: "const DB_MIGRATION_LOCK = 'qiling_account_deletion_request_db_migration_lock';",
      versionOption: "'version_option'              => self::DB_VERSION_OPTION,",
      targetVersion: "'target_version'              => self::DB_VERSION,",
      applySchema: 'Database_Schema_Migration_Service::apply_schema( self::get_table_schema() );',
      extraNeedles: ["'can_update_version_callback' => function ()"],
      initHookPattern: /add_action\(\s*'init'\s*,\s*array\(\s*\$this\s*,\s*'maybe_create_table'\s*\)\s*\)/,
    },
  ].forEach(({ file, switchHook, adminHook, lockConstant, versionOption, targetVersion, applySchema, extraNeedles = [], initHookPattern, directSchemaPattern }) => {
    const source = readThemeFile(file);
    assertContains(source, switchHook, `${file} should run schema migration on theme switch`);
    assertContains(source, adminHook, `${file} should run schema migration from admin_init`);
    assertContains(source, 'Database_Schema_Migration_Service::can_run_admin_migration()', `${file} admin_init migration should use the shared admin guard`);
    assertContains(source, lockConstant, `${file} should define a dedicated schema migration lock`);
    assertContains(source, 'Database_Schema_Migration_Service::run(', `${file} should use the shared schema migration service`);
    assertContains(source, versionOption, `${file} should pass its schema version option to the migration service`);
    assertContains(source, targetVersion, `${file} should pass its target schema version to the migration service`);
    assertContains(source, "'lock_option'", `${file} should pass its dedicated migration lock to the migration service`);
    assertContains(source, "'migration_callback'", `${file} should pass a migration callback to the migration service`);
    assertContains(source, applySchema, `${file} should apply dbDelta through the shared schema migration service`);
    extraNeedles.forEach((needle) => {
      assertContains(source, needle, `${file} shared migration option missing: ${needle}`);
    });
    assertMatches(
      source,
      /^(?:(?!add_option\(\s*self::(?:TABLE|DB)_MIGRATION_LOCK).)*$/s,
      `${file} should not duplicate migration lock acquisition`
    );
    assertMatches(
      source,
      /^(?:(?!dbDelta\().)*$/s,
      `${file} should not call dbDelta() directly`
    );
    assertMatches(
      source,
      /^(?:(?!update_option\(\s*self::(?:TABLE|DB)_VERSION_OPTION).)*$/s,
      `${file} should let the shared service persist schema versions`
    );
    if (initHookPattern.test(source)) {
      throw new Error(`${file} should not run schema migration on init`);
    }
    if (directSchemaPattern && directSchemaPattern.test(source)) {
      throw new Error(`${file} should not run schema migration from a frontend request handler`);
    }
  });

  const securityHeadersPhp = readThemeFile('inc/core/helpers/helpers-security-headers.php');
  [
    "header( 'X-Frame-Options: SAMEORIGIN' );",
    "header( 'X-Content-Type-Options: nosniff' );",
    'is_ssl()',
    "'developer_starter_security_hsts_enabled'",
    "'developer_starter_security_hsts_header'",
    "'max-age=31536000; includeSubDomains'",
    "header( 'Strict-Transport-Security: ' . $hsts_header );",
    "preg_replace( '/[\\r\\n]+/', '', $hsts_header )",
  ].forEach((needle) => {
    assertContains(securityHeadersPhp, needle, `Security header contract changed unexpectedly: ${needle}`);
  });

  const verificationPhp = readThemeFile('inc/core/class-id-verification-manager.php');
  const verificationRestControllerPhp = readThemeFile('inc/core/class-id-verification-rest-controller.php');
  [
    'encrypt_verification_pii_payload',
    'pii_storage_unavailable',
    'verification_store_failed',
    'mobile_encrypted TEXT NOT NULL',
    "'mobile'            => self::mask_mobile_value( $mobile )",
    "'mobile_encrypted'  => $encrypted_mobile",
    "update_user_meta( $user_id, 'qiling_id_mobile', $encrypted_mobile )",
    'get_record_mobile_value',
    '$inserted = $wpdb->insert(',
    'if ( false === $inserted )',
  ].forEach((needle) => {
    assertContains(verificationPhp, needle, `ID verification storage guard changed unexpectedly: ${needle}`);
  });
  assertMatches(
    verificationPhp,
    /'data'\s*=>\s*array\(\s*'verified'\s*=>\s*\$response_success,\s*'status'\s*=>\s*\$response_success \? 'success' : 'failed',\s*\)/s,
    'ID verification response should not return raw upstream data to the frontend'
  );
  [
    'class ID_Verification_REST_Controller extends \\WP_REST_Controller',
    "register_rest_route(",
    "'permission_callback' => array( $this, 'verify_permissions_check' )",
    "'permission_callback' => array( $this, 'status_permissions_check' )",
    "'permission_callback' => array( $this, 'delete_permissions_check' )",
    "'schema' => array( $this, 'get_verify_response_schema' )",
    "'schema' => array( $this, 'get_status_response_schema' )",
    "'schema' => array( $this, 'get_delete_response_schema' )",
    "rest_ensure_response( $this->manager->handle_verification( $request ) )",
    "rest_ensure_response( $this->manager->get_user_status( $request ) )",
    "rest_ensure_response( $this->manager->delete_record( $request ) )",
  ].forEach((needle) => {
    assertContains(verificationRestControllerPhp, needle, `ID verification REST controller contract changed unexpectedly: ${needle}`);
  });
  assertContains(
    verificationPhp,
    'new ID_Verification_REST_Controller( $this );',
    'ID verification manager should delegate route registration to the REST controller'
  );
  assertMatches(
    verificationPhp,
    /public function register_rest_routes\(\)[\s\S]*\$controller->register_routes\(\);[\s\S]*?\n    \}/,
    'ID verification manager register_rest_routes() should keep only the controller bridge'
  );
  assertMatches(
    verificationPhp,
    /^(?:(?!register_rest_route\().)*$/s,
    'ID verification manager should not directly register REST routes after controller extraction'
  );

  const adminBootstrapPhp = readThemeFile('inc/admin/admin-bootstrap.php');
  [
    'developer_starter_should_boot_admin_settings',
    'developer_starter_register_admin_settings_menu',
    'developer_starter_add_admin_settings_bar_menu',
    'developer_starter_clear_cleanup_rest_audit_log',
    'developer_starter_clear_poster_cache',
    'developer_starter_export_i18n_seo_report',
    'developer_starter_generate_i18n_seo_meta',
    "add_action( 'admin_menu', 'developer_starter_register_admin_settings_menu', 10 );",
    "add_action( 'admin_bar_menu', 'developer_starter_add_admin_settings_bar_menu', 80 );",
  ].forEach((needle) => {
    assertContains(adminBootstrapPhp, needle, `Admin settings bootstrap contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    adminBootstrapPhp,
    /^(?:(?!developer_starter_migrate_legacy_component_styles).)*$/s,
    'Admin bootstrap should no longer whitelist the legacy component migration AJAX action'
  );

  const adminSettingsPhp = readThemeFile('inc/admin/class-admin-settings.php');
  const adminSettingsAdminTraitPhp = readThemeFile('inc/admin/traits/class-admin-settings-admin-trait.php');
  const adminSettingsAjaxTraitPhp = readThemeFile('inc/admin/traits/class-admin-settings-ajax-trait.php');
  const adminSettingsFieldRenderTraitPhp = readAdminSettingsFieldRenderSources();
  const registeredSettingsAjaxActions = [...adminSettingsPhp.matchAll(/add_action\(\s*['"]wp_ajax_([^'"]+)/g)]
    .map((match) => match[1])
    .sort();
  const registeredSettingsAdminPostActions = [...adminSettingsPhp.matchAll(/add_action\(\s*['"]admin_post_([^'"]+)/g)]
    .map((match) => match[1])
    .sort();
  const registeredBootstrapAdminPostActions = [...adminBootstrapPhp.matchAll(/add_action\(\s*['"]admin_post_([^'"]+)/g)]
    .map((match) => match[1])
    .sort();
  const registeredAdminPostActions = [...new Set([
    ...registeredSettingsAdminPostActions,
    ...registeredBootstrapAdminPostActions,
  ])].sort();
  const allowedSettingsAjaxActionsBlock = (adminBootstrapPhp.match(/\$allowed_ajax_actions\s*=\s*array\(([^]*?)\);/) || [])[1] || '';
  const allowedSettingsAdminPostActionsBlock = (adminBootstrapPhp.match(/\$allowed_admin_post_actions\s*=\s*array\(([^]*?)\);/) || [])[1] || '';
  const allowedSettingsAjaxActions = [...allowedSettingsAjaxActionsBlock.matchAll(/['"]([^'"]+)['"]/g)]
    .map((match) => match[1])
    .sort();
  const allowedSettingsAdminPostActions = [...allowedSettingsAdminPostActionsBlock.matchAll(/['"]([^'"]+)['"]/g)]
    .map((match) => match[1])
    .sort();
  const missingSettingsAjaxActions = registeredSettingsAjaxActions.filter((action) => !allowedSettingsAjaxActions.includes(action));
  const staleSettingsAjaxActions = allowedSettingsAjaxActions.filter((action) => !registeredSettingsAjaxActions.includes(action));
  const missingSettingsAdminPostActions = registeredAdminPostActions.filter((action) => !allowedSettingsAdminPostActions.includes(action));
  const staleSettingsAdminPostActions = allowedSettingsAdminPostActions.filter((action) => !registeredAdminPostActions.includes(action));
  if (
    missingSettingsAjaxActions.length ||
    staleSettingsAjaxActions.length ||
    missingSettingsAdminPostActions.length ||
    staleSettingsAdminPostActions.length
  ) {
    throw new Error(
      [
        `Settings AJAX actions missing from lazy-load whitelist: ${missingSettingsAjaxActions.join(', ') || 'none'}`,
        `Settings AJAX whitelist entries without registered handlers: ${staleSettingsAjaxActions.join(', ') || 'none'}`,
        `Settings admin-post actions missing from lazy-load whitelist: ${missingSettingsAdminPostActions.join(', ') || 'none'}`,
        `Settings admin-post whitelist entries without registered handlers: ${staleSettingsAdminPostActions.join(', ') || 'none'}`,
      ].join('\n')
    );
  }
  [
    "add_action( 'wp_ajax_developer_starter_clear_poster_cache'",
    'public function ajax_clear_poster_cache()',
    "check_ajax_referer( 'clear_poster_cache_nonce', 'nonce' );",
    '$this->cleanup_poster_cache_files()',
    "'allowed_roots' => $allowed_roots",
  ].forEach((needle) => {
    assertContains(
      needle.includes('add_action') ? adminSettingsPhp : adminSettingsAjaxTraitPhp,
      needle,
      `Poster cache cleanup admin contract changed unexpectedly: ${needle}`
    );
  });
  [
    "add_action( 'wp_ajax_developer_starter_clear_cleanup_rest_audit_log'",
    "check_ajax_referer( 'cleanup_rest_audit_log_nonce', 'nonce' );",
    "add_action( 'admin_post_developer_starter_export_i18n_seo_report'",
    "add_action( 'admin_post_developer_starter_generate_i18n_seo_meta'",
    "check_admin_referer( 'developer_starter_export_i18n_seo_report' );",
    "check_admin_referer( 'developer_starter_generate_i18n_seo_meta' );",
  ].forEach((needle) => {
    const source = needle.includes('wp_ajax') || needle.includes('admin_post')
      ? adminSettingsPhp
      : needle.includes('cleanup_rest')
        ? adminSettingsAjaxTraitPhp
        : adminSettingsFieldRenderTraitPhp;
    assertContains(source, needle, `Admin settings lazy-load whitelist target changed unexpectedly: ${needle}`);
  });
  assertContains(
    adminSettingsPhp,
    'admin-bootstrap.php 的轻量入口统一注册',
    'Admin settings should document the lightweight menu/bootstrap owner'
  );
  [
    "add_action( 'admin_post_ds_repair_modules_meta', 'developer_starter_admin_post_ds_repair_modules_meta', 0 );",
    "add_action( 'admin_post_ds_repair_theme_options', 'developer_starter_admin_post_ds_repair_theme_options', 0 );",
    'developer_starter_get_admin_settings_instance()->handle_repair_modules_meta();',
    'developer_starter_get_admin_settings_instance()->handle_repair_theme_options();',
  ].forEach((needle) => {
    assertContains(adminBootstrapPhp, needle, `Repair admin-post bootstrap contract changed unexpectedly: ${needle}`);
  });
  [
    "add_action( 'admin_post_ds_repair_modules_meta'",
    "add_action( 'admin_post_ds_repair_theme_options'",
  ].forEach((needle) => {
    assertMatches(
      adminSettingsPhp,
      new RegExp(`^(?:(?!${needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}).)*$`, 's'),
      `Repair admin-post handlers should be owned by admin-bootstrap.php only: ${needle}`
    );
  });
  assertMatches(
    adminSettingsPhp,
    /^(?:(?!ajax_migrate_legacy_component_styles).)*$/s,
    'Admin settings should no longer register the legacy component migration AJAX handler'
  );
  assertMatches(
    adminSettingsAdminTraitPhp,
    /^(?:(?!public function add_menu_page|public function add_admin_bar_menu).)*$/s,
    'Admin settings trait should not keep dead menu/admin-bar registration methods'
  );

  const themeLicensePhp = readThemeFile('inc/core/class-theme-license.php');
  [
    "admin_url( 'admin.php?page=developer-starter-settings' )",
    'private static function is_debug_logging_enabled()',
    'qilingLicenseLog',
    'window.console.debug',
  ].forEach((needle) => {
    assertContains(themeLicensePhp, needle, `Theme license logging contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    themeLicensePhp,
    /^(?:(?!admin\.php\?page=theme-settings).)*$/s,
    'Theme license notices should link to the real developer-starter-settings page'
  );
  assertMatches(
    themeLicensePhp,
    /^(?:(?!console\.(?:log|error)\().)*$/s,
    'Automatic license and telemetry checks should only use debug console logging'
  );

  const fieldRenderPhp = readAdminSettingsFieldRenderSources();
  [
    '页脚内容和外观分开维护。',
    '页脚内容在当前页维护；页脚背景、标题和文字样式在全局样式中统一调整。',
    'uploads/cache/developer-starter/ip-location 内的 IP 归属地缓存文件',
  ].forEach((needle) => {
    assertContains(fieldRenderPhp, needle, `Settings governance copy changed unexpectedly: ${needle}`);
  });
  assertMatches(
    fieldRenderPhp,
    /^(?:(?!wp-content\/ip_cache).)*$/s,
    'Admin IP cache copy should describe the uploads/cache location, not the legacy wp-content/ip_cache path'
  );
  assertMatches(
    fieldRenderPhp,
    /^(?:(?!developer_starter_migrate_legacy_component_styles).)*$/s,
    'Admin field renderer should no longer contain the legacy migration AJAX client hook'
  );

  const designTokensPhp = readThemeFile('inc/core/class-design-tokens.php');
  [
    'self::normalize_runtime_theme_options( $options ) : self::get_theme_options()',
    "'--qiling-header-scrolled-text'          => 'var(--qiling-component-header-scrolled-text, var(--qiling-header-text))'",
    "'--qiling-footer-heading-size'  => 'var(--qiling-component-footer-heading-size)'",
  ].forEach((needle) => {
    assertContains(designTokensPhp, needle, `Design token runtime single-source contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    designTokensPhp,
    /^(?:(?!apply_legacy_component_style_compatibility).)*$/s,
    'Design token runtime should no longer use the legacy component compatibility overlay'
  );

  const configuredCodeAssetsPhp = readThemeFile('inc/core/class-assets.php');
  [
    'private function code_snippet_needs_jquery( $code )',
    'private function needs_jquery_for_configured_code()',
    'private function needs_jquery_for_login_compatibility()',
    "developer_starter_get_option( 'custom_js', '' )",
    "'international_third_party_code_enable'",
    "'developer_starter_configured_code_needs_jquery'",
    "'developer_starter_login_compatibility_needs_jquery'",
    '$this->needs_jquery_for_configured_code()',
    '$this->needs_jquery_for_login_compatibility()',
    "if ( $needs_jquery ) {\n            $main_deps[] = 'jquery';",
  ].forEach((needle) => {
    assertContains(configuredCodeAssetsPhp, needle, `Configured-code jQuery dependency guard changed unexpectedly: ${needle}`);
  });

  const mainJs = readThemeFile('assets/js/main.js');
  [
    'window.DSLoadScriptOnce = function (url, marker) {',
    "window.DSLoadScriptOnce(mobileMenuScript, 'data-ds-mobile-menu-script')",
    'window.qilingDarkModeConfig || getGlobalData().darkMode || {}',
    "window.matchMedia('(prefers-color-scheme: dark)')",
    'function scheduleWantsDark()',
    'function autoWantsDark()',
    'function resolveTheme()',
    "writeThemeStorage(storageKey, isDark ? 'dark' : 'light')",
    "root.classList.toggle('qiling-dark-image-dim', !!darkModeConfig.imageDim)",
  ].forEach((needle) => {
    assertContains(mainJs, needle, `Frontend runtime loader contract changed unexpectedly: ${needle}`);
  });
  assertMatches(
    mainJs,
    /document\.addEventListener\('DOMContentLoaded', function \(\) \{\s+'use strict';\s+function getGlobalData\(\) \{/,
    'main.js must define getGlobalData() inside the DOMContentLoaded runtime scope'
  );

  const searchEnhanceJs = readThemeFile('assets/js/feature-search-enhance.js');
  [
    'function initSearchEnhance()',
    "storageKey = searchEnhance.storageKey || 'qiling-search-history'",
    'function saveSearchTerm(term)',
    'function renderSuggestions(form, input)',
    'function renderHistoryBlocks()',
    "document.querySelectorAll('form[role=\"search\"], form.search-form, .qiling-search-enhanced')",
  ].forEach((needle) => {
    assertContains(searchEnhanceJs, needle, `Search enhance chunk contract changed unexpectedly: ${needle}`);
  });

  const searchCaptchaJs = readThemeFile('assets/js/search-captcha.js');
  [
    'if (!$ || !$.fn) {',
    '})(window.jQuery);',
  ].forEach((needle) => {
    assertContains(searchCaptchaJs, needle, `Search captcha jQuery guard changed unexpectedly: ${needle}`);
  });
  assertMatches(
    searchCaptchaJs,
    /^(?:(?!\}\)\(jQuery\);).)*$/s,
    'search-captcha.js must not call the global jQuery symbol directly before it is available'
  );

  const mainCss = readThemeFile('assets/css/main.css');
  [
    '.qiling-search-suggestions',
    '.qiling-search-suggestions__item',
    '.qiling-search-history__chip',
    '.search-highlight',
    'html.qiling-theme-transitioning',
    'html.dark-mode.qiling-dark-image-dim img',
    ':not([data-no-dark-dim]):not(.no-dark-dim)',
    'html.dark-mode.qiling-dark-image-dim .site-branding img',
    '@media (prefers-reduced-motion: reduce)',
  ].forEach((needle) => {
    assertContains(mainCss, needle, `Auto dark mode CSS contract changed unexpectedly: ${needle}`);
  });

  const searchHelperPhp = readThemeFile('inc/core/helpers/helpers-search.php');
  [
    'function developer_starter_normalize_search_scope( $scope )',
    'function developer_starter_get_search_scope_choices()',
    'function developer_starter_get_current_search_scope()',
    'function developer_starter_search_query_vars( $public_query_vars )',
    "add_filter( 'query_vars', 'developer_starter_search_query_vars' );",
    "array( 'all', 'title', 'content', 'tag' )",
    'function developer_starter_get_search_tag_match_sql( $term )',
    "tt.taxonomy = 'post_tag'",
    'function developer_starter_highlight_search_terms( $text, $terms = null )',
    'function developer_starter_get_public_ajax_rate_limit_config(',
    'function developer_starter_is_public_ajax_rate_limited(',
    'function developer_starter_send_public_ajax_rate_limited(',
    'function developer_starter_die_public_ajax_rate_limited(',
    "apply_filters( 'developer_starter_public_ajax_rate_limit_config'",
    "'public_ajax_' . $scope",
    "'scope'          => developer_starter_normalize_search_scope",
    "$query->set( 'search_scope', $scope );",
    "$query->set( 'developer_starter_search_scope', $scope );",
    "in_array( 'tag', $match_fields, true )",
  ].forEach((needle) => {
    assertContains(searchHelperPhp, needle, `Search helper enhancement contract changed unexpectedly: ${needle}`);
  });

  const headerActionsPhp = readThemeFile('template-parts/header/actions.php');
  [
    'class="header-search-form-inline qiling-search-enhanced"',
    'data-qiling-search-form="1"',
    'data-qiling-search-input="1"',
    'name="search_scope" value="all"',
  ].forEach((needle) => {
    assertContains(headerActionsPhp, needle, `Header search enhancement contract changed unexpectedly: ${needle}`);
  });

  const searchOverlayPhp = readThemeFile('template-parts/header/search-overlay.php');
  [
    'class="search-form qiling-search-enhanced"',
    'data-qiling-search-form="1"',
    'data-qiling-search-input="1"',
    'name="search_scope" value="all"',
  ].forEach((needle) => {
    assertContains(searchOverlayPhp, needle, `Search overlay enhancement contract changed unexpectedly: ${needle}`);
  });

  const documentHeadPhp = readThemeFile('template-parts/header/document-head.php');
  [
    'developer_starter_get_dark_mode_runtime_config()',
    'window.qilingDarkModeConfig = <?php echo wp_json_encode( $dark_mode_config ); ?>;',
    "window.matchMedia('(prefers-color-scheme: dark)').matches",
    "root.classList.toggle('qiling-dark-image-dim', !!config.imageDim)",
    "root.setAttribute('data-theme-source', source)",
  ].forEach((needle) => {
    assertContains(documentHeadPhp, needle, `Header dark mode preflight contract changed unexpectedly: ${needle}`);
  });
}
