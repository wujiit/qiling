import {
  assertContains,
  assertFileExists,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Content model center contracts';

export async function run() {
  [
    'inc/core/class-content-model-center.php',
    'inc/core/helpers/helpers-content-models.php',
    'inc/admin/traits/class-admin-settings-config-trait.php',
    'inc/admin/traits/class-admin-settings-field-render-trait.php',
    'inc/admin/traits/class-admin-settings-sanitize-trait.php',
    'inc/core/class-frontend-builder.php',
    'inc/core/class-ai-decorator.php',
    'inc/core/ai/class-prompt-builder.php',
  ].forEach((file) => {
    assertFileExists(file, `Content model center chain missing required file: ${file}`);
  });

  const centerPhp = readThemeFile('inc/core/class-content-model-center.php');
  [
    'class Content_Model_Center',
    'SCHEMA_VERSION',
    'DEFAULT_ENABLED_MODELS',
    'get_model_choices(',
    'sanitize_options(',
    'register_content_types(',
    'register_model_post_type(',
    'register_model_taxonomies(',
    'register_model_meta_fields(',
    'get_client_payload(',
    'get_prompt_context(',
    'developer_starter_content_model_definitions',
    'developer_starter_content_model_post_type_args',
    'developer_starter_content_model_taxonomy_args',
  ].forEach((needle) => {
    assertContains(centerPhp, needle, `Content model center contract changed unexpectedly: ${needle}`);
  });

  const helpersPhp = readThemeFile('inc/core/helpers/helpers-content-models.php');
  [
    'developer_starter_get_content_model_center',
    'developer_starter_get_content_model_definitions',
    'developer_starter_get_content_model_client_payload',
    'developer_starter_get_content_model_prompt_context',
    'developer_starter_query_content_model_items',
  ].forEach((needle) => {
    assertContains(helpersPhp, needle, `Content model helper contract changed unexpectedly: ${needle}`);
  });

  const bootstrapPhp = [
    readThemeFile('functions.php'),
    readThemeFile('inc/core/bootstrap-services.php'),
  ].join('\n');
  [
    'helpers-content-models.php',
    'Content_Model_Center::get_instance();',
  ].forEach((needle) => {
    assertContains(bootstrapPhp, needle, `Content model bootstrap contract changed unexpectedly: ${needle}`);
  });

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'models'       => __( '模型中心'",
    'content_model_center_enable',
    'content_model_enabled_models',
    'content_model_archive_base',
    'render_content_model_center_overview_field',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Content model settings contract changed unexpectedly: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  assertContains(sanitizePhp, 'Content_Model_Center::sanitize_options', 'Content model options are not sanitized centrally.');

  const frontendPhp = readThemeFile('inc/core/class-frontend-builder.php');
  [
    "'contentModels'    => $content_models",
    'Content_Model_Center::get_client_payload()',
    'contentModelSummaryTitle',
  ].forEach((needle) => {
    assertContains(frontendPhp, needle, `Frontend builder content model contract changed unexpectedly: ${needle}`);
  });

  const aiPhp = readThemeFile('inc/core/class-ai-decorator.php');
  [
    "'contentModels'          => $this->get_content_model_context( 'client' )",
    'get_content_model_context(',
    'Content_Model_Center::get_prompt_context()',
  ].forEach((needle) => {
    assertContains(aiPhp, needle, `AI content model contract changed unexpectedly: ${needle}`);
  });

  const promptPhp = readThemeFile('inc/core/ai/class-prompt-builder.php');
  assertContains(promptPhp, "'content_models' => $this->decorator->get_content_model_context( 'prompt' )", 'AI prompt is missing content model context.');

  const frontendJs = readThemeFile('assets/js/frontend-builder.js');
  [
    'contentModels: {}',
    'getContentModelPayload',
    'renderAiContentModelContextHtml',
    'qfb-ai-content-context',
  ].forEach((needle) => {
    assertContains(frontendJs, needle, `Frontend JS content model contract changed unexpectedly: ${needle}`);
  });

  const frontendCss = readThemeFile('assets/css/frontend-builder.css');
  [
    '.qfb-ai-content-context',
    '.qfb-content-model-pills',
    '.qfb-content-model-pill',
  ].forEach((needle) => {
    assertContains(frontendCss, needle, `Frontend CSS content model contract changed unexpectedly: ${needle}`);
  });

}
