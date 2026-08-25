<?php
/**
 * Qi Ling 主题函数和定义
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 主题常量
 */
define( 'DEVELOPER_STARTER_VERSION', '2.6.6' );
define( 'DEVELOPER_STARTER_DIR', get_template_directory() );
define( 'DEVELOPER_STARTER_URI', get_template_directory_uri() );
define( 'DEVELOPER_STARTER_INC', DEVELOPER_STARTER_DIR . '/inc' );
$developer_starter_assets_base = apply_filters( 'developer_starter_assets_base_url', DEVELOPER_STARTER_URI . '/assets' );
if ( ! is_string( $developer_starter_assets_base ) || trim( $developer_starter_assets_base ) === '' ) {
    $developer_starter_assets_base = DEVELOPER_STARTER_URI . '/assets';
}
define( 'DEVELOPER_STARTER_ASSETS', rtrim( (string) $developer_starter_assets_base, '/' ) );
define( 'DEVELOPER_STARTER_DB_VERSION', '2.6.5' );

/**
 * 辅助函数
 */
require_once DEVELOPER_STARTER_INC . '/core/class-helpers.php';
require_once DEVELOPER_STARTER_INC . '/core/class-ip-location.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-contact-form.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-qiling-forms-bridge.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-multilingual-frontend.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-native-sitemaps.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-cache-control.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-left-nav.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-child-theme-compat.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-content-models.php';

/**
 * 自动加载 (Autoloader)
 */
require_once DEVELOPER_STARTER_INC . '/class-autoloader.php';

// 页面级区域装修使用独立后台类，避免与全局页头、页脚设置保存逻辑混用。
if ( is_admin() ) {
    new Developer_Starter\Admin\Page_Region_Decoration_Meta_Box();
    new Developer_Starter\Admin\Builder_Revision_Manager();
}

/**
 * 组件实例化改为钩子阶段执行，避免在 functions.php 加载期提前初始化。
 */

/**
 * 检查后发现 class-china-features.php 应该是类 Developer_Starter\China\China_Features
 */
require_once DEVELOPER_STARTER_INC . '/china/class-china-features.php';

/**
 * SEO功能
 */
require_once DEVELOPER_STARTER_INC . '/seo/class-industry-schema-engine.php';
require_once DEVELOPER_STARTER_INC . '/seo/class-seo-manager.php';
require_once DEVELOPER_STARTER_INC . '/seo/class-seo-health-check.php';

/**
 * 小工具
 */
require_once DEVELOPER_STARTER_INC . '/widgets/class-widget-contact.php';
require_once DEVELOPER_STARTER_INC . '/widgets/class-widget-social.php';
require_once DEVELOPER_STARTER_INC . '/widgets/class-widget-site-owner.php';
require_once DEVELOPER_STARTER_INC . '/widgets/class-widget-post-list.php';

/**
 * 模块模版管理器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-template-manager.php';
require_once DEVELOPER_STARTER_INC . '/core/class-builder-data-service.php';
require_once DEVELOPER_STARTER_INC . '/core/class-single-page-package-service.php';
$developer_starter_module_advanced_style_service_file = DEVELOPER_STARTER_INC . '/core/class-module-advanced-style-service.php';
if ( file_exists( $developer_starter_module_advanced_style_service_file ) ) {
    require_once $developer_starter_module_advanced_style_service_file;
}
$developer_starter_module_visual_style_service_file = DEVELOPER_STARTER_INC . '/core/class-module-visual-style-service.php';
if ( file_exists( $developer_starter_module_visual_style_service_file ) ) {
    require_once $developer_starter_module_visual_style_service_file;
}

/**
 * 前台可视化装修
 */
require_once DEVELOPER_STARTER_INC . '/core/class-frontend-builder.php';
require_once DEVELOPER_STARTER_INC . '/core/ai/class-response-parser.php';
require_once DEVELOPER_STARTER_INC . '/core/ai/class-prompt-builder.php';
require_once DEVELOPER_STARTER_INC . '/core/ai/class-connection-manager.php';
require_once DEVELOPER_STARTER_INC . '/core/ai/class-generation-orchestrator.php';
require_once DEVELOPER_STARTER_INC . '/core/class-ai-decorator.php';

/**
 * 文章增强器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-post-enhancer.php';

/**
 * 菜单保护器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-menu-protector.php';

/**
 * 公告管理器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-announcement-manager.php';

/**
 * 招聘管理
 */
require_once DEVELOPER_STARTER_INC . '/core/class-careers-manager.php';

/**
 * 超级菜单管理
 */
require_once DEVELOPER_STARTER_INC . '/core/class-mega-menu-manager.php';

/**
 * 分类管理器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-category-manager.php';

/**
 * WooCommerce 集成
 * 始终加载文件，类内部会检查 WooCommerce 是否激活
 */
require_once DEVELOPER_STARTER_INC . '/woocommerce/class-wc-admin.php';
require_once DEVELOPER_STARTER_INC . '/woocommerce/class-wc-setup.php';
require_once DEVELOPER_STARTER_INC . '/admin/admin-bootstrap.php';

/**
 * 主题核心初始化（优先于其他业务组件）
 * Theme_Setup 需要在 after_setup_theme 早期注册主题支持能力。
 */
function developer_starter_bootstrap_theme_core() {
    static $bootstrapped = false;
    if ( $bootstrapped ) {
        return;
    }
    $bootstrapped = true;

    new Developer_Starter\Core\Theme_Setup();
}
add_action( 'after_setup_theme', 'developer_starter_bootstrap_theme_core', 1 );

/**
 * 页面创建器注册表与按需加载。
 */
require_once DEVELOPER_STARTER_INC . '/core/bootstrap-page-creators.php';

/**
 * 主题业务服务初始化。
 */
require_once DEVELOPER_STARTER_INC . '/core/bootstrap-services.php';

require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-thumbnail.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-theme-lifecycle.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-comments.php';

/**
 * 主题模板标签函数
 */
require_once DEVELOPER_STARTER_INC . '/template-tags.php';
require_once DEVELOPER_STARTER_INC . '/template-tags-icon.php';
require_once DEVELOPER_STARTER_INC . '/template-tags-menu-icons.php';

/**
 * 自定义器扩展
 */
require_once DEVELOPER_STARTER_INC . '/customizer/class-customizer.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-country-flag.php';

/**
 * 输出语言切换弹窗到页面底部
 */
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-language-switcher-ui.php';

/**
 * 登录弹窗功能（独立文件）
 * 提供顶部登录弹窗的HTML、CSS和JavaScript输出
 */
require_once DEVELOPER_STARTER_INC . '/core/class-login-modal.php';

/**
 * 内容可见性限制功能
 * 提供登录可见、回复可见短代码和文章整体设置
 */
require_once DEVELOPER_STARTER_INC . '/core/class-content-restriction.php';

/**
 * 游客访问控制功能
 * 支持全站登录访问与分类登录可见
 */
require_once DEVELOPER_STARTER_INC . '/core/class-guest-access.php';

/**
 * VIP 访问控制功能
 * 支持分类和菜单项基于 VIP 级别的控制
 */
require_once DEVELOPER_STARTER_INC . '/core/class-vip-access.php';

/**
 * SEO 推送功能（百度普通收录）
 */
require_once DEVELOPER_STARTER_INC . '/core/class-seo-push.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-advanced-category-filter.php';

/**
 * WordPress 优化功能
 */
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-runtime-optimizations.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-admin-whitelabel.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-security-headers.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-query-cache.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-search.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-404-redirects.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-output-optimization.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-front-protection.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-maintenance.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-blog-pagination.php';
require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-category-base.php';

require_once DEVELOPER_STARTER_INC . '/core/helpers/helpers-debug-tools.php';
