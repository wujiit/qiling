import {
  assertContains,
  assertFileExists,
  assertMatches,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'AI decoration contracts';

export async function run() {
  const requiredFiles = [
    'inc/core/ai/class-connection-manager.php',
    'inc/core/ai/class-prompt-builder.php',
    'inc/core/ai/class-response-parser.php',
    'inc/core/ai/class-generation-orchestrator.php',
    'inc/core/class-ai-decorator.php',
    'assets/js/ai-builder-service.js',
  ];

  requiredFiles.forEach((file) => {
    assertFileExists(file, `AI chain missing required file: ${file}`);
  });

  const functionsPhp = readThemeFile('functions.php');
  [
    "require_once DEVELOPER_STARTER_INC . '/core/ai/class-response-parser.php';",
    "require_once DEVELOPER_STARTER_INC . '/core/ai/class-prompt-builder.php';",
    "require_once DEVELOPER_STARTER_INC . '/core/ai/class-connection-manager.php';",
    "require_once DEVELOPER_STARTER_INC . '/core/ai/class-generation-orchestrator.php';",
    "require_once DEVELOPER_STARTER_INC . '/core/class-ai-decorator.php';",
  ].forEach((needle) => {
    assertContains(functionsPhp, needle, `functions.php is no longer loading AI layer file: ${needle}`);
  });

  const connectionManagerPhp = readThemeFile('inc/core/ai/class-connection-manager.php');
  [
    'public static function normalize_allowed_endpoint_hosts',
    'public static function sanitize_endpoint_url',
    'public static function validate_endpoint_url',
    "esc_url_raw( trim( (string) $endpoint ), array( 'https' ) )",
    "'https' !== strtolower( (string) $parts['scheme'] )",
    'is_valid_endpoint_host',
    'endpoint_host_is_public',
    'resolve_endpoint_host_ips',
    'FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE',
    "'developer_starter_ai_endpoint_allowed_hosts'",
    "'redirection'         => 0,",
    "'reject_unsafe_urls'  => true,",
    "'sslverify'           => true,",
  ].forEach((needle) => {
    assertContains(connectionManagerPhp, needle, `AI endpoint SSRF guard changed unexpectedly: ${needle}`);
  });
  assertMatches(
    connectionManagerPhp,
    /empty\(\s*\$ips\s*\)\s*\)\s*\{\s*return false;/s,
    'AI endpoint DNS guard should fail closed when no public IP can be resolved'
  );
  assertMatches(
    connectionManagerPhp,
    /^(?:(?!array\( 'http', 'https' \)).)*$/s,
    'AI connection manager should not allow HTTP endpoints'
  );

  const adminSettingsConfigPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'ai_endpoint_allowlist'",
    '每行一个允许的公网域名',
    'localhost、内网、保留地址和链路本地地址始终禁止',
  ].forEach((needle) => {
    assertContains(adminSettingsConfigPhp, needle, `AI endpoint allowlist setting changed unexpectedly: ${needle}`);
  });

  const adminSettingsSanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  [
    'normalize_allowed_endpoint_hosts( $ai_endpoint_allowlist )',
    'sanitize_endpoint_url( $raw_endpoint, $allowed_hosts )',
    '仅允许公网 HTTPS 地址，且必须符合 allowlist',
  ].forEach((needle) => {
    assertContains(adminSettingsSanitizePhp, needle, `AI endpoint admin sanitization guard changed unexpectedly: ${needle}`);
  });

  const adminSettingsAjaxPhp = readThemeFile('inc/admin/traits/class-admin-settings-ajax-trait.php');
  assertContains(
    adminSettingsAjaxPhp,
    "$endpoint = isset( $_POST['endpoint'] ) ? trim( (string) wp_unslash( $_POST['endpoint'] ) ) : '';",
    'AI connection test should pass the raw endpoint to the central validator'
  );
  assertMatches(
    adminSettingsAjaxPhp,
    /^(?:(?!esc_url_raw\( trim\( \(string\) wp_unslash\( \$_POST\['endpoint'\] \) \), array\( 'https' \) \)).)*$/s,
    'AI connection test should not silently drop invalid endpoint input before validation'
  );

  const decoratorPhp = readThemeFile('inc/core/class-ai-decorator.php');
  [
    "const AJAX_ACTION_GENERATE = 'qiling_ai_generate_page_package';",
    "const AI_SCOPE_MODULE = 'module';",
    "const AI_SCOPE_SITE = 'site';",
    "'site_generation_allowed' => false,",
    "'max_pages_per_request'   => self::MAX_PAGES_PER_AI_REQUEST,",
    "'online_whole_site_builder' => false,",
    "is_disallowed_site_generation_prompt(",
    "sanitize_ai_seo_payload(",
    "add_action( 'wp_ajax_' . self::AJAX_ACTION_GENERATE, array( $this, 'ajax_generate_page_package' ) );",
    "add_action( 'wp_ajax_' . self::AJAX_ACTION_PLAN, array( $this, 'ajax_plan_page_package' ) );",
    "add_action( 'wp_ajax_' . self::AJAX_ACTION_GENERATE_MODULE, array( $this, 'ajax_generate_page_module' ) );",
    "'ajaxAction'             => self::AJAX_ACTION_GENERATE,",
    "'scopePolicy'            => $this->get_ai_scope_policy( 'client' ),",
  ].forEach((needle) => {
    assertContains(decoratorPhp, needle, `AI_Decorator lost expected contract: ${needle}`);
  });

  const promptBuilderPhp = readThemeFile('inc/core/ai/class-prompt-builder.php');
  [
    "'seo 必须是当前单页的 SEO 建议对象",
    "'existing_modules' => $this->decorator->get_existing_modules_context( $post_id ),",
    "'ai_scope_policy' => $this->decorator->get_ai_scope_policy( 'prompt' ),",
    "'existingData'=> is_array( $current_module_data ) ? $current_module_data : array(),",
    "'不要生成整站、多页面、站点包、页面包市场内容或任何当前页面以外的页面清单'",
  ].forEach((needle) => {
    assertContains(promptBuilderPhp, needle, `Prompt builder lost Phase 6 guardrail/context: ${needle}`);
  });

  const orchestratorPhp = readThemeFile('inc/core/ai/class-generation-orchestrator.php');
  [
    'get_disallowed_site_generation_error()',
    "isset( $args['current_module_data'] ) ? $args['current_module_data'] : array()",
    "'seo'                  => isset( $plan['seo'] ) && is_array( $plan['seo'] ) ? $plan['seo'] : array(),",
  ].forEach((needle) => {
    assertContains(orchestratorPhp, needle, `Generation orchestrator lost Phase 6 behavior: ${needle}`);
  });

  const responseParserPhp = readThemeFile('inc/core/ai/class-response-parser.php');
  [
    "'seo'                  => array(),",
    "$this->decorator->sanitize_ai_seo_payload( $decoded['seo'] )",
  ].forEach((needle) => {
    assertContains(responseParserPhp, needle, `Response parser lost SEO package contract: ${needle}`);
  });

  const aiBuilderService = readThemeFile('assets/js/ai-builder-service.js');
  [
    "qiling_ai_generate_page_package",
    "qiling_ai_generate_page_module",
    "site_generation_allowed: false",
    "looksLikeDisallowedSiteGeneration",
    "generatePageModule",
    "connection_id",
    "scope: 'page'",
    "scope: 'module'",
    "current_module_data",
    "module_ids",
    "post_id",
    "warnings",
    "package",
  ].forEach((needle) => {
    assertContains(aiBuilderService, needle, `Shared AI builder service lost expected payload contract: ${needle}`);
  });

  const frontendBuilderJs = readThemeFile('assets/js/frontend-builder.js');
  [
    'renderAiScopeNoticeHtml',
    'qfb-ai-optimize-module',
    'optimizeCurrentModuleWithAi',
    'buildCurrentModuleAiPlan',
    'renderAiPromptRecipesHtml',
    'applyAiPromptRecipe',
    'data-ai-prompt-recipe',
    'aiPromptRecipeInternationalText',
    'loadAiPromptHistory',
    'pushAiPromptHistory',
    'renderAiPromptHistory',
    'applyAiPromptHistory',
    'data-ai-prompt-history',
    'aiPromptHistoryRestored',
    'getAiModuleBundles',
    'scoreAiModuleForBundle',
    'renderAiModuleBundlesHtml',
    'applyAiModuleBundle',
    'data-ai-module-bundle',
    'aiModuleBundleApplied',
    'getAiReadinessItems',
    'renderAiReadiness',
    'qfb-ai-readiness',
    'aiReadinessPendingExists',
    'getAiReviewItems',
    'renderAiReviewChecklistHtml',
    'qfb-ai-review-checklist',
    'aiReviewGuardrailOk',
    'targetModuleSignature',
    'isPendingAiModuleTargetCurrent',
    'restoreLatestAiSnapshot({ clearPending: false })',
    'state.pageSettings.seo = deepClone(packageData.seo);',
  ].forEach((needle) => {
    assertContains(frontendBuilderJs, needle, `Frontend builder lost Phase 6 AI UX behavior: ${needle}`);
  });

  const frontendBuilderCss = readThemeFile('assets/css/frontend-builder.css');
  [
    '.qfb-ai-scope-notice',
    '#qfb-ai-optimize-module',
    '.qfb-ai-prompt-recipes',
    '.qfb-ai-prompt-recipe',
    '.qfb-ai-prompt-history-card',
    '.qfb-ai-module-bundles',
    '.qfb-ai-module-bundle',
    '.qfb-ai-readiness-card',
    '.qfb-ai-review-checklist',
  ].forEach((needle) => {
    assertContains(frontendBuilderCss, needle, `Frontend builder CSS lost Phase 6 AI controls: ${needle}`);
  });

  const modulesMetaBox = readThemeFile('inc/admin/views/modules-meta-box.php');
  [
    'dsm-ai-scope-notice',
    'applyImportedPagePackageSeo',
    '在线 AI 整站生成已关闭',
    '生成/优化当前单页',
  ].forEach((needle) => {
    assertContains(modulesMetaBox, needle, `Admin AI modal lost Phase 6 guardrail/SEO behavior: ${needle}`);
  });

}
