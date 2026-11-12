import {
  assertContains,
  assertMatches,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Theme settings default contracts';

export async function run() {
  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'author_show_avatar', 'type' => 'checkbox', 'label' => __( '显示头像', 'developer-starter' ), 'desc' => __( '显示作者的头像', 'developer-starter' ), 'default' => '1'",
    "'author_show_name', 'type' => 'checkbox', 'label' => __( '显示昵称', 'developer-starter' ), 'desc' => __( '显示作者的显示名称', 'developer-starter' ), 'default' => '1'",
    "'author_show_bio', 'type' => 'checkbox', 'label' => __( '显示简介', 'developer-starter' ), 'desc' => __( '显示作者的个人简介', 'developer-starter' ), 'default' => '1'",
    "'contact_show_form', 'type' => 'checkbox', 'label' => __( '显示联系表单', 'developer-starter' ), 'desc' => __( '在联系我们页面显示主题内置在线留言。', 'developer-starter' ), 'default' => '1'",
    "'contact_show_info', 'type' => 'checkbox', 'label' => __( '显示基础信息', 'developer-starter' ), 'desc' => __( '显示企业名称、电话、QQ、微信二维码、邮箱、地址', 'developer-starter' ), 'default' => '1'",
    "'float_widget_enable', 'type' => 'checkbox', 'label' => __( '启用浮动栏', 'developer-starter' ), 'desc' => __( '开启后在前台显示右侧浮动栏', 'developer-starter' ), 'default' => '1'",
    "'query_cache_enable', 'type' => 'checkbox', 'label' => __( '启用查询结果缓存', 'developer-starter' ), 'desc' => __( '缓存高频 WP_Query 结果，减少重复查询（推荐开启）', 'developer-starter' ), 'default' => '1'",
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Admin setting default changed unexpectedly: ${needle}`);
  });

  const queryCachePhp = readThemeFile('inc/core/helpers/helpers-query-cache.php');
  assertContains(
    queryCachePhp,
    "developer_starter_get_option( 'query_cache_enable', '1' )",
    'Generic cached query helper should share the same default as the admin checkbox and search cache'
  );

  const settingsHelpersPhp = readThemeFile('inc/admin/traits/class-admin-settings-helpers-trait.php');
  [
    'private function get_ecosystem_plugin_groups()',
    "'slug' => 'qilingcontentsecurity'",
    "'name' => '启灵内容安全'",
    "'fee' => '免费'",
    "'title'   => '主题联动增强'",
    "'slug' => 'qiling-events'",
    "'slug' => 'qiling-site-agent'",
    "'slug' => 'qiling-aijianli'",
    "'title'   => 'AI 创作与内容工具'",
    "'slug' => 'xb-aifanyi-translator'",
    "'file' => 'xb-aifanyi-translator/xb-aifanyi-translator.php'",
    "'title'   => '文档、媒体与素材处理'",
    "'slug' => 'qilingsecurity'",
    "'title'   => '业务系统与行业插件'",
    "'slug' => 'qilingcoupon'",
    "'title'   => '运营、增长与运维'",
    "'slug' => 'qilingwebhook'",
    "'file' => 'qilingwebhook/qilingwebhook.php'",
    "'relation' => '可作为主题通知、表单消息和运维提醒的推送通道。'",
    'private function get_standalone_open_source_plugins()',
    "'slug' => 'wp-ai-chat'",
    "'name' => '启灵AI助手'",
    "'slug' => 'qilingcoupon'",
    "'name' => '启灵淘宝客'",
    '不属于启灵主题生态，也不在启灵主题售后范围内',
    'private function get_plugin_guide_plugin_index()',
    'private function detect_ecosystem_plugin_statuses()',
    "get_option( 'active_plugins', array() )",
    'file_exists( trailingslashit( WP_PLUGIN_DIR ) . $main_file )',
  ].forEach((needle) => {
    assertContains(settingsHelpersPhp, needle, `Ecosystem plugin guide contract changed unexpectedly: ${needle}`);
  });

  const ecosystemRegistryPhp = settingsHelpersPhp.match(/private function get_ecosystem_plugin_groups[\s\S]*?private function get_ecosystem_plugin_index/)?.[0] || '';
  assertMatches(
    ecosystemRegistryPhp,
    /^(?:(?!'fee'\s*=>\s*'\d).)*$/s,
    'Paid ecosystem plugin suggestions should display 收费 instead of concrete prices'
  );
  assertMatches(
    ecosystemRegistryPhp,
    /^(?:(?!'slug'\s*=>\s*'xb-aijianli').)*$/s,
    'Ecosystem plugin suggestions should use qiling-aijianli rather than the legacy xb-aijianli slug'
  );
  assertMatches(
    ecosystemRegistryPhp,
    /^(?:(?!'slug'\s*=>\s*'wp-ai-chat'|'slug'\s*=>\s*'qilingcoupon').)*$/s,
    'Standalone open-source plugins should not appear in ecosystem groups'
  );
  const internalPluginPattern = [
    'qiling' + 'editor',
    'lj-copy' + '-arena',
    'qiling' + 'cut',
    'xb-video' + '-parser',
    'qiling' + 'verify',
    '启灵编辑器' + '排版',
    '灵简AI商用文案' + '盲测',
    '启灵' + '抠图',
    '启灵视频' + '解析',
    '启灵主题' + '授权',
  ].join('|');
  assertMatches(
    ecosystemRegistryPhp,
    new RegExp(`^(?:(?!${internalPluginPattern}).)*$`, 's'),
    'Internal ecosystem plugins should not appear in the public plugin guide'
  );

  const ecosystemDetectPhp = settingsHelpersPhp.match(/private function detect_ecosystem_plugin_statuses[\s\S]*?private function get_ecosystem_plugin_status_label/)?.[0] || '';
  assertContains(
    ecosystemDetectPhp,
    'get_plugin_guide_plugin_index()',
    'Plugin guide detection should include ecosystem and standalone open-source plugins'
  );
  assertMatches(
    ecosystemDetectPhp,
    /^(?:(?!get_plugins\s*\().)*$/s,
    'Manual ecosystem plugin detection should not scan plugin headers with get_plugins()'
  );

  const settingsPageRenderPhp = readThemeFile('inc/admin/traits/class-admin-settings-page-render-trait.php');
  [
    '启灵生态插件指南',
    '整理启灵生态插件的定位、适用场景与主题搭配关系，方便站点按业务需要选择。',
    '共收录 %1$d 个生态插件，另列 %2$d 个开源免费插件。',
    '开源免费插件（不属于主题生态）',
    '以下插件完全开源免费，不属于启灵主题生态，也不在启灵主题售后范围内。',
    '检测插件状态',
    '点击按钮可查看当前站点状态。',
    '正在检测插件状态...',
    'developer_starter_detect_ecosystem_plugins',
    'data-ecosystem-plugin-status',
    "addEventListener('click'",
  ].forEach((needle) => {
    assertContains(settingsPageRenderPhp, needle, `Ecosystem plugin guide UI contract changed unexpectedly: ${needle}`);
  });
  const removedImplementationNotes = [
    '只做' + '说明',
    '按用途解释' + '插件适合什么站点',
    '点击按钮后才检查' + '主文件和启用状态',
    '不做' + '安装',
    '没有自动' + '安装、启用、更新和配置动作',
  ];
  assertMatches(
    settingsPageRenderPhp,
    new RegExp(`^(?:(?!${removedImplementationNotes.join('|')}).)*$`, 's'),
    'Ecosystem plugin guide should not render implementation-note cards'
  );

  const settingsAjaxPhp = readThemeFile('inc/admin/traits/class-admin-settings-ajax-trait.php');
  [
    'ajax_detect_ecosystem_plugins',
    "check_ajax_referer( 'developer_starter_ecosystem_plugins', 'nonce' )",
    'wp_send_json_success( $this->detect_ecosystem_plugin_statuses() );',
  ].forEach((needle) => {
    assertContains(settingsAjaxPhp, needle, `Ecosystem plugin detection AJAX contract changed unexpectedly: ${needle}`);
  });

  const settingsAdminPhp = readThemeFile('inc/admin/class-admin-settings.php');
  [
    'wp_ajax_developer_starter_detect_ecosystem_plugins',
    'wp_ajax_developer_starter_seo_health_scan',
    'wp_ajax_developer_starter_seo_health_clear',
  ].forEach((needle) => {
    assertContains(settingsAdminPhp, needle, `Manual settings AJAX action should stay registered: ${needle}`);
  });

  const adminBootstrapPhp = readThemeFile('inc/admin/admin-bootstrap.php');
  [
    "'developer_starter_detect_ecosystem_plugins'",
    "'developer_starter_seo_health_scan'",
    "'developer_starter_seo_health_clear'",
  ].forEach((needle) => {
    assertContains(adminBootstrapPhp, needle, `Manual settings AJAX action should stay in the admin lazy-load whitelist: ${needle}`);
  });
}
