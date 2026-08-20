<?php
/**
 * 后台白标（本土化）钩子。
 *
 * 所有操作仅在后台生效，且需总开关 qiling_admin_whitelabel_enable 开启。
 * 适配 WordPress 6.x / 7.x。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 注册后台白标相关钩子。
 *
 * 注意：这些开关不纳入 developer_starter_get_dangerous_runtime_optimization_keys()，
 * 不受"兼容回滚模式"影响——它们只做视觉隐藏，不存在破坏性风险。
 *
 * @return void
 */
function developer_starter_admin_whitelabel_init() {
	if ( ! is_admin() && ! is_network_admin() ) {
		return;
	}

	if ( ! function_exists( 'developer_starter_get_option' ) ) {
		return;
	}

	// 总开关
	if ( ! developer_starter_get_option( 'qiling_admin_whitelabel_enable', '' ) ) {
		return;
	}

	// 1. 移除后台标题 " — WordPress" 后缀
	if ( developer_starter_get_option( 'qiling_admin_remove_wp_title_suffix', '' ) ) {
		add_filter( 'admin_title', function ( $admin_title ) {
			return str_replace( ' &#8212; WordPress', '', $admin_title );
		}, 999 );
	}

	// 2. 移除 Admin Bar 左上角 WP Logo
	if ( developer_starter_get_option( 'qiling_admin_hide_wp_logo', '' ) ) {
		add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_node( 'wp-logo' );
		}, 999 );
	}

	// 3. 清空底部版权文字和版本号
	if ( developer_starter_get_option( 'qiling_admin_hide_footer_text', '' ) ) {
		add_filter( 'admin_footer_text', '__return_empty_string', 999 );
		add_filter( 'update_footer', '__return_empty_string', 999 );
	}

	// 4. 移除侧边栏"工具"菜单 + "仪表盘 → 更新"子菜单
	if ( developer_starter_get_option( 'qiling_admin_hide_tools_menu', '' ) ) {
		add_action( 'admin_menu', function () {
			remove_menu_page( 'tools.php' );                       // 顶级菜单：工具
			remove_submenu_page( 'index.php', 'update-core.php' ); // 子菜单：仪表盘 → 更新
		}, 999 );
	}

	// 5. 隐藏安装相关入口：移除"插件 → 安装插件"子菜单 + CSS 隐藏按钮/横幅/帮助面板
	if ( developer_starter_get_option( 'qiling_admin_hide_install_buttons', '' ) ) {
		add_action( 'admin_menu', function () {
			remove_submenu_page( 'plugins.php', 'plugin-install.php' ); // 侧边栏：插件 → 安装插件
		}, 999 );

		add_action( 'admin_head', function () {
			echo '<style id="ds-whitelabel-css">'
				. '/* 后台白标 CSS（适配 WP 6.x / 7.x） */'
				// 插件页 "安装插件" 按钮
				. '.wrap .page-title-action[href*="plugin-install.php"],'
				// 主题页 "安装主题" / "添加新主题" 按钮
				. '.wrap .page-title-action[href*="theme-install.php"],'
				// 主题列表中的 "添加新主题" 卡片
				. '.theme-browser .add-new-theme,'
				// 顶部更新通知横幅
				. '.update-nag,'
				. 'div.updated.notice[data-dismissible],'
				// 右上角 "帮助" 选项卡
				. '#contextual-help-link-wrap,'
				. '#contextual-help-link'
				. '{display:none!important}'
				. '</style>';
		}, 999 );
	}
}
add_action( 'init', 'developer_starter_admin_whitelabel_init', 2 );
