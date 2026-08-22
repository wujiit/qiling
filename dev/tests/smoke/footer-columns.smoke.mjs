import {
  assertContains,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Footer default column toggles';

export async function run() {
  const footerPhp = readThemeFile('footer.php');
  [
    "developer_starter_get_option( 'footer_about_enable', '1' )",
    "developer_starter_get_option( 'footer_links_enable', '1' )",
    "developer_starter_get_option( 'footer_contact_enable', '1' )",
    "developer_starter_get_option( 'footer_follow_enable', '1' )",
    'developer_starter_get_footer_visual_config',
    "'footer_visual_config'    => $footer_visual_config",
    "'effect_scope'            => $footer_effect_scope",
    "'footer_section_visibility' => $footer_section_visibility",
  ].forEach((needle) => {
    assertContains(footerPhp, needle, `Footer template args missing default column toggle: ${needle}`);
  });

  const footerVisualPhp = readThemeFile('inc/core/helpers/helpers-footer-visual.php');
  [
    'developer_starter_get_footer_visual_config',
    'developer_starter_sanitize_footer_visual_options',
    'developer_starter_sanitize_footer_visual_page_settings',
    'developer_starter_get_post_footer_visual_settings',
    'developer_starter_persist_post_footer_visual_settings',
    'developer_starter_get_current_footer_visual_page_settings',
    'developer_starter_get_footer_wave_paths',
    'developer_starter_get_page_visual_skin( $requested_preset_key )',
    '_qiling_footer_visual_mode',
    '_qiling_footer_wave_mode',
    '_qiling_footer_inherit_skin_colors',
    'footer_visual_main_bg',
    'footer_visual_friend_bg',
    'footer_visual_bottom_bg',
    'footer_visual_wave_enable',
    'footer_visual_wave_palette',
    'footer_visual_wave_backdrop',
    'footer_visual_wave_transition_from',
    'footer_visual_wave_transition_height',
    'footer_effect_scope',
    'site-footer--visual',
    'site-footer--page-skin',
    'site-footer--hidden-by-page',
  ].forEach((needle) => {
    assertContains(footerVisualPhp, needle, `Footer visual helper contract changed unexpectedly: ${needle}`);
  });

  assertContains(
    readThemeFile('inc/core/helpers/bootstrap.php'),
    'helpers-footer-visual.php',
    'Footer visual helpers are not loaded by helper bootstrap.'
  );

  const siteFooterPhp = readThemeFile('template-parts/footer/site-footer.php');
  [
    'footer_visual_config',
    'site-footer-wave',
    'site-footer-wave__soft',
    'site-footer-wave__fill',
    'footer-effect-canvas--site',
    "if ( ! empty( $footer_visual_config['hidden'] ) )",
  ].forEach((needle) => {
    assertContains(siteFooterPhp, needle, `Footer shell visual contract changed unexpectedly: ${needle}`);
  });

  const widgetsPhp = readThemeFile('template-parts/footer/widgets.php');
  [
    '$show_about',
    '$show_links',
    '$show_contact',
    '$show_follow',
    'footer-widgets-grid--columns-',
    'data-footer-columns',
    'if ( $visible_footer_columns <= 0 )',
    'footer-section--main',
    'footer-effect-canvas--main',
  ].forEach((needle) => {
    assertContains(widgetsPhp, needle, `Footer widget rendering contract changed unexpectedly: ${needle}`);
  });

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'footer_about_enable'",
    "'footer_links_enable'",
    "'footer_contact_enable'",
    "'footer_follow_enable'",
    "'footer_visual_main_bg'",
    "'footer_visual_friend_bg'",
    "'footer_visual_bottom_bg'",
    "'footer_visual_wave_enable'",
    "'footer_visual_wave_palette'",
    "'footer_visual_wave_backdrop'",
    "'footer_visual_wave_transition_from'",
    "'footer_visual_wave_layer_opacity'",
    "'step' => 'any'",
    '请输入 0 到 1 之间的任意小数',
    "'footer_visual_wave_transition_height'",
    "'footer_effect_scope'",
    '默认栏目显示',
    '页脚三段式视觉装修',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Footer settings toggle field missing: ${needle}`);
  });

  const mainCss = readThemeFile('assets/css/main.css');
  [
    '.footer-widgets-grid--columns-1',
    '.footer-widgets-grid--columns-2',
    '.footer-widgets-grid--columns-3',
    '.footer-widgets-grid--columns-4',
    '.site-footer--visual .footer-widgets',
    '.site-footer--visual .footer-friend-links',
    '.site-footer--visual .footer-bottom',
    '#page:has(> .site-footer--wave-enabled) > .site-main > .page-content--builder.section-padding',
    '.site-footer--wave-enabled::before',
    '.site-footer-wave__fill',
    '.site-footer--hidden-by-page',
    '.site-footer--integrated-canvas',
  ].forEach((needle) => {
    assertContains(mainCss, needle, `Footer column CSS missing: ${needle}`);
  });

  const pageSkinsPhp = readThemeFile('inc/core/helpers/helpers-page-skins.php');
  [
    "'footer'          => array(",
    "'wave_style'   => 'soft'",
    "'effect_scope' => 'decorative'",
    'site-footer--integrated-canvas',
    '--qiling-footer-wave-color',
    '--qiling-footer-wave-transition-from',
    '--qiling-footer-wave-transition-height',
    'developer_starter_get_page_visual_skin',
    '#ff836f',
  ].forEach((needle) => {
    assertContains(pageSkinsPhp, needle, `Page skin footer bridge missing: ${needle}`);
  });

  const aiDecoratorPhp = readThemeFile('inc/core/class-ai-decorator.php');
  [
    "'footer'             => function_exists( 'developer_starter_get_post_footer_visual_settings' )",
    'developer_starter_persist_post_footer_visual_settings',
  ].forEach((needle) => {
    assertContains(aiDecoratorPhp, needle, `Page footer settings persistence missing: ${needle}`);
  });

  const frontendBuilderJs = readThemeFile('assets/js/frontend-builder.js');
  [
    'normalizePageFooterSettings',
    'normalizePageFooterBool',
    'getPageFooterPresetChoices',
    'footer.mode',
    'pageFooterSectionTitle',
    "renderPageSelectControl(getText('pageFooterPresetLabel', '页脚预设'), 'footer.preset'",
    'footer: normalizePageFooterSettings',
  ].forEach((needle) => {
    assertContains(frontendBuilderJs, needle, `Frontend builder page footer controls missing: ${needle}`);
  });

  const frontendBuilderPhp = readThemeFile('inc/core/class-frontend-builder.php');
  [
    '$footer_presets = $this->get_footer_preset_choices();',
    "'footerPresets'    => $footer_presets",
    'private function get_footer_preset_choices()',
    'developer_starter_get_page_visual_skins()',
  ].forEach((needle) => {
    assertContains(frontendBuilderPhp, needle, `Frontend builder footer preset choices missing: ${needle}`);
  });
}
