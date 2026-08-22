import {
  assertContains,
  assertFileExists,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'AI single-module localization contracts';

export async function run() {
  [
    'inc/core/class-ai-decorator.php',
    'inc/core/ai/class-prompt-builder.php',
    'inc/core/ai/class-generation-orchestrator.php',
    'inc/core/ai/class-response-parser.php',
    'assets/js/ai-builder-service.js',
    'assets/js/frontend-builder.js',
    'assets/css/frontend-builder.css',
    '../plugins/xb-aifanyi-translator/xb-aifanyi-translator.php',
    '../plugins/xb-aifanyi-translator/includes/class-xb-aifanyi-translation-engine-trait.php',
  ].forEach((file) => {
    assertFileExists(file, `AI localization missing required file: ${file}`);
  });

  const decoratorPhp = readThemeFile('inc/core/class-ai-decorator.php');
  [
    "const AI_MODE_LOCALIZATION = 'localization';",
    "const AJAX_ACTION_LOCALIZE_PAGE = 'qiling_ai_localize_page_package';",
    "const AJAX_ACTION_BATCH_LOCALIZE = 'qiling_ai_batch_localize_content';",
    "'localize_single_module'",
    "'localize_current_page'",
    "'create_language_page'",
    "'batch_localize_existing_content'",
    "'localize_template_package'",
    "'localize_page'             => true",
    "'create_language_page'      => true",
    "'batch_localization'        => true",
    "'template_package_localization' => true",
    "'localization'           => $this->get_ai_localization_client_config()",
    'get_ai_localization_language_choices',
    'get_ai_localization_tone_choices',
    'get_ai_localization_industry_tone_packs',
    'get_ai_localization_batch_content_types',
    'normalize_ai_localization_request',
    'normalize_ai_localization_translation_map',
    'normalize_ai_localization_term_list',
    'get_module_text_only_field_schema_map',
    'get_page_package_text_only_schema_map',
    'normalize_ai_page_package_input',
    'sync_localized_package_to_aifanyi',
    'filter_ai_localization_text_field_schema_map',
    'is_ai_localization_text_field',
    'is_ai_localization_blocked_field',
    "'en' => __( '英文'",
    "'ja' => __( '日文'",
    "'ko' => __( '韩文'",
    "'fr' => __( '法文'",
    "'de' => __( '德文'",
    "'es' => __( '西班牙文'",
    "'preserve_layout'       => true",
  ].forEach((needle) => {
    assertContains(decoratorPhp, needle, `AI localization decorator contract missing: ${needle}`);
  });
  const batchTypesBlock = decoratorPhp.match(/public function get_ai_localization_batch_content_types\(\) \{[\s\S]*?\n    \}/);
  if (!batchTypesBlock || batchTypesBlock[0].includes("'template_package'")) {
    throw new Error('AI batch localization must not expose template_package until the batch backend can process template packages.');
  }

  const promptPhp = readThemeFile('inc/core/ai/class-prompt-builder.php');
  [
    'build_module_localization_messages',
    'build_page_localization_messages',
    'build_content_localization_messages',
    "'task' => '本地化启灵主题单个模块的文案字段'",
    "'task' => '本地化启灵主题整页页面包的文案字段'",
    "'text_only_field_whitelist' => $text_only_schema",
    "'module_text_only_field_whitelist' => is_array( $text_schema_map )",
    'localization_review',
    '只改文案字段',
    '禁止修改布局、样式、颜色、间距、图片、图标、链接 URL、文章 ID、分类、taxonomy、数据源、显示开关、数量、排序字段',
    '默认强制保留原布局',
    '目标语言必须与 localization.target_language_label / localization.target_language 一致',
  ].forEach((needle) => {
    assertContains(promptPhp, needle, `AI localization prompt contract missing: ${needle}`);
  });

  const orchestratorPhp = readThemeFile('inc/core/ai/class-generation-orchestrator.php');
  [
    '$is_localization = AI_Decorator::AI_MODE_LOCALIZATION === $mode;',
    'normalize_ai_localization_request',
    'get_module_text_only_field_schema_map',
    'build_module_localization_messages',
    'build_page_localization_messages',
    "'request_type' => 'module_localization'",
    "'request_type' => 'page_localization'",
    "'request_type' => 'content_localization'",
    'extract_localized_module_from_response',
    'extract_localized_page_package_from_response',
    'extract_localized_content_from_response',
    'batch_localize_content',
    'localize_content_record',
    'query_batch_post_ids',
    "'mode'         => AI_Decorator::AI_MODE_LOCALIZATION",
  ].forEach((needle) => {
    assertContains(orchestratorPhp, needle, `AI localization orchestrator contract missing: ${needle}`);
  });

  const parserPhp = readThemeFile('inc/core/ai/class-response-parser.php');
  [
    'extract_localized_module_from_response',
    'extract_localized_page_package_from_response',
    'extract_localized_content_from_response',
    'merge_localized_text_fields',
    'normalize_localization_review',
    'build_localization_score',
    '本地化文案白名单',
    '保留原列表结构',
    'sanitize_module_data_by_schema',
  ].forEach((needle) => {
    assertContains(parserPhp, needle, `AI localization parser contract missing: ${needle}`);
  });

  const serviceJs = readThemeFile('assets/js/ai-builder-service.js');
  [
    'getLocalizationConfig',
    'normalizeLocalizationOptions',
    'validateModuleLocalizationRequest',
    'validatePageLocalizationRequest',
    'localizePagePackage',
    'batchLocalizeContent',
    'industryTonePacks',
    'fixed_translations',
    'forbidden_words',
    'product_terms',
    "mode: normalizeString(args.mode).trim()",
    'localization: JSON.stringify(normalizeLocalizationOptions(args.localization, config))',
  ].forEach((needle) => {
    assertContains(serviceJs, needle, `AI localization shared JS contract missing: ${needle}`);
  });

  const frontendJs = readThemeFile('assets/js/frontend-builder.js');
  [
    'renderAiLocalizationControlsHtml',
    'getAiLocalizationOptions',
    'qfb-ai-localize-module',
    'qfb-ai-localize-page',
    'localizeCurrentModuleWithAi',
    'localizeCurrentPageWithAi',
    'buildCurrentPageAiPackage',
    'qfb-ai-localization-fixed',
    'qfb-ai-localization-forbidden',
    'qfb-ai-localization-products',
    'qfb-ai-localization-create-page',
    "mode: 'localization'",
    '本地化结果待应用',
    '整页本地化结果',
    'text-only 白名单',
    '确认后才会应用',
  ].forEach((needle) => {
    assertContains(frontendJs, needle, `AI localization frontend contract missing: ${needle}`);
  });

  const frontendCss = readThemeFile('assets/css/frontend-builder.css');
  [
    '.qfb-ai-localization-card',
    '.qfb-ai-localization-grid',
    '.qfb-ai-localization-mini',
    '#qfb-ai-localize-module',
    '#qfb-ai-localize-page',
  ].forEach((needle) => {
    assertContains(frontendCss, needle, `AI localization CSS contract missing: ${needle}`);
  });

  const pluginMainPhp = readThemeFile('../plugins/xb-aifanyi-translator/xb-aifanyi-translator.php');
  [
    'function xb_aifanyi_upsert_theme_localization',
    '主题只传结构化 payload，不直接查询或写入插件表',
  ].forEach((needle) => {
    assertContains(pluginMainPhp, needle, `AI localization xb-aifanyi wrapper missing: ${needle}`);
  });

  const pluginEnginePhp = readThemeFile('../plugins/xb-aifanyi-translator/includes/class-xb-aifanyi-translation-engine-trait.php');
  [
    'public function xb_aifanyi_upsert_theme_localization',
    'create_language_page',
    'xb_aifanyi_sync_translation_version_post',
    'translation_editor_url',
  ].forEach((needle) => {
    assertContains(pluginEnginePhp, needle, `AI localization xb-aifanyi save contract missing: ${needle}`);
  });
}
