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
 * 运行环境安全检查：严格检测 PHP 最低安全版本要求（防范 PHP 核心高危漏洞 PHP2Shell: CVE-2026-109618 / CVE-2026-109619，CVSS 9.2~9.8）
 * 严格安全补丁矩阵：PHP < 8.2 彻底淘汰；PHP 8.2 需 >= 8.2.35；PHP 8.3 需 >= 8.3.36；PHP 8.4 需 >= 8.4.27。
 * 若环境不满足安全基线，立即加载兼容守护逻辑并中止后续所有代码的加载，防止后台卡死与服务器未授权 RCE 风险。
 */
if ( ! function_exists( 'developer_starter_is_php_secure' ) ) {
    function developer_starter_is_php_secure() {
        $version = PHP_VERSION;

        // 8.2 以下分支官方均已停止维护且未获官方核心安全补丁，全面阻断
        if ( version_compare( $version, '8.2.0', '<' ) ) {
            return false;
        }

        // 8.2 分支：必须升级至安全补丁版本 8.2.35 或更高
        if ( version_compare( $version, '8.3.0', '<' ) ) {
            return version_compare( $version, '8.2.35', '>=' );
        }

        // 8.3 分支：必须升级至安全补丁版本 8.3.36 或更高
        if ( version_compare( $version, '8.4.0', '<' ) ) {
            return version_compare( $version, '8.3.36', '>=' );
        }

        // 8.4 分支：必须升级至安全补丁版本 8.4.27 或更高
        if ( version_compare( $version, '8.5.0', '<' ) ) {
            return version_compare( $version, '8.4.27', '>=' );
        }

        // 8.5 分支：必须升级至安全补丁版本 8.5.12 或更高
        if ( version_compare( $version, '8.6.0', '<' ) ) {
            return version_compare( $version, '8.5.12', '>=' );
        }

        return true;
    }
}

if ( ! developer_starter_is_php_secure() ) {
    require_once get_template_directory() . '/inc/back-compat.php';
    return;
}

/**
 * 启灵主题核心常量 (QiLing Constants)
 */
define( 'QILING_VERSION', '2.6.9' );
define( 'QILING_DIR', get_template_directory() );
define( 'QILING_URI', get_template_directory_uri() );
define( 'QILING_INC', QILING_DIR . '/inc' );
$qiling_assets_base = apply_filters( 'qiling_assets_base_url', apply_filters( 'developer_starter_assets_base_url', QILING_URI . '/assets' ) );
if ( ! is_string( $qiling_assets_base ) || trim( $qiling_assets_base ) === '' ) {
    $qiling_assets_base = QILING_URI . '/assets';
}
define( 'QILING_ASSETS', rtrim( (string) $qiling_assets_base, '/' ) );
define( 'QILING_DB_VERSION', '2.6.9' );

/**
 * 历史常量向后兼容垫片（Backwards-Compatibility Shim）
 * 确保任何调用 DEVELOPER_STARTER_* 的子主题、第三方扩展或历史组件 100% 正常运行
 */
if ( ! defined( 'DEVELOPER_STARTER_VERSION' ) ) {
    define( 'DEVELOPER_STARTER_VERSION', QILING_VERSION );
}
if ( ! defined( 'DEVELOPER_STARTER_DIR' ) ) {
    define( 'DEVELOPER_STARTER_DIR', QILING_DIR );
}
if ( ! defined( 'DEVELOPER_STARTER_URI' ) ) {
    define( 'DEVELOPER_STARTER_URI', QILING_URI );
}
if ( ! defined( 'DEVELOPER_STARTER_INC' ) ) {
    define( 'DEVELOPER_STARTER_INC', QILING_INC );
}
if ( ! defined( 'DEVELOPER_STARTER_ASSETS' ) ) {
    define( 'DEVELOPER_STARTER_ASSETS', QILING_ASSETS );
}
if ( ! defined( 'DEVELOPER_STARTER_DB_VERSION' ) ) {
    define( 'DEVELOPER_STARTER_DB_VERSION', QILING_DB_VERSION );
}

/**
 * 辅助函数
 */
require_once QILING_INC . '/core/class-helpers.php';
require_once QILING_INC . '/core/class-ip-location.php';
require_once QILING_INC . '/core/helpers/helpers-contact-form.php';
require_once QILING_INC . '/core/helpers/helpers-qiling-forms-bridge.php';
require_once QILING_INC . '/core/helpers/helpers-multilingual-frontend.php';
require_once QILING_INC . '/core/helpers/helpers-native-sitemaps.php';
require_once QILING_INC . '/core/helpers/helpers-cache-control.php';
require_once QILING_INC . '/core/helpers/helpers-left-nav.php';
require_once QILING_INC . '/core/helpers/helpers-child-theme-compat.php';
require_once QILING_INC . '/core/helpers/helpers-content-models.php';
require_once QILING_INC . '/core/helpers/helpers-qiling-compat.php';

/**
 * 自动加载 (Autoloader)
 */
require_once QILING_INC . '/class-autoloader.php';

/**
 * 组件实例化统一在 init 钩子阶段执行（developer_starter_init / developer_starter_boot_admin_services），避免在 functions.php 加载期提前初始化。
 */

/**
 * 国内网络与合规优化功能
 */
require_once QILING_INC . '/china/class-china-features.php';

/**
 * SEO功能
 */
require_once QILING_INC . '/seo/class-industry-schema-engine.php';
require_once QILING_INC . '/seo/class-seo-manager.php';
require_once QILING_INC . '/seo/class-seo-health-check.php';

/**
 * 小工具
 */
require_once QILING_INC . '/widgets/class-widget-contact.php';
require_once QILING_INC . '/widgets/class-widget-social.php';
require_once QILING_INC . '/widgets/class-widget-site-owner.php';
require_once QILING_INC . '/widgets/class-widget-post-list.php';

/**
 * 模块模版管理器
 */
require_once QILING_INC . '/core/class-template-manager.php';
require_once QILING_INC . '/core/class-builder-data-service.php';
require_once QILING_INC . '/core/class-single-page-package-service.php';
$developer_starter_module_advanced_style_service_file = QILING_INC . '/core/class-module-advanced-style-service.php';
if ( file_exists( $developer_starter_module_advanced_style_service_file ) ) {
    require_once $developer_starter_module_advanced_style_service_file;
}
$developer_starter_module_visual_style_service_file = QILING_INC . '/core/class-module-visual-style-service.php';
if ( file_exists( $developer_starter_module_visual_style_service_file ) ) {
    require_once $developer_starter_module_visual_style_service_file;
}

/**
 * 前台可视化装修
 */
require_once QILING_INC . '/core/class-frontend-builder.php';
require_once QILING_INC . '/core/ai/class-response-parser.php';
require_once QILING_INC . '/core/ai/class-prompt-builder.php';
require_once QILING_INC . '/core/ai/class-connection-manager.php';
require_once QILING_INC . '/core/ai/class-generation-orchestrator.php';
require_once QILING_INC . '/core/class-ai-decorator.php';

/**
 * 文章增强器
 */
require_once QILING_INC . '/core/class-post-enhancer.php';

/**
 * 菜单保护器
 */
require_once QILING_INC . '/core/class-menu-protector.php';

/**
 * 公告管理器
 */
require_once QILING_INC . '/core/class-announcement-manager.php';

/**
 * 招聘管理
 */
require_once QILING_INC . '/core/class-careers-manager.php';

/**
 * 超级菜单管理
 */
require_once QILING_INC . '/core/class-mega-menu-manager.php';

/**
 * 分类管理器
 */
require_once QILING_INC . '/core/class-category-manager.php';


require_once QILING_INC . '/admin/admin-bootstrap.php';

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
require_once QILING_INC . '/core/bootstrap-page-creators.php';

/**
 * 主题业务服务初始化。
 */
require_once QILING_INC . '/core/bootstrap-services.php';

require_once QILING_INC . '/core/helpers/helpers-thumbnail.php';
require_once QILING_INC . '/core/helpers/helpers-theme-lifecycle.php';
require_once QILING_INC . '/core/helpers/helpers-comments.php';

/**
 * 主题模板标签函数
 */
require_once QILING_INC . '/template-tags.php';
require_once QILING_INC . '/template-tags-icon.php';
require_once QILING_INC . '/template-tags-menu-icons.php';

/**
 * 自定义器扩展
 */
require_once QILING_INC . '/customizer/class-customizer.php';
require_once QILING_INC . '/core/helpers/helpers-country-flag.php';

/**
 * 输出语言切换弹窗到页面底部
 */
require_once QILING_INC . '/core/helpers/helpers-language-switcher-ui.php';

/**
 * 登录弹窗功能（独立文件）
 * 提供顶部登录弹窗的HTML、CSS和JavaScript输出
 */
require_once QILING_INC . '/core/class-login-modal.php';

/**
 * 内容可见性限制功能
 * 提供登录可见、回复可见短代码和文章整体设置
 */
require_once QILING_INC . '/core/class-content-restriction.php';

/**
 * 游客访问控制功能
 * 支持全站登录访问与分类登录可见
 */
require_once QILING_INC . '/core/class-guest-access.php';

/**
 * VIP 访问控制功能
 * 支持分类和菜单项基于 VIP 级别的控制
 */
require_once QILING_INC . '/core/class-vip-access.php';

/**
 * SEO 推送功能（百度普通收录）
 */
require_once QILING_INC . '/core/class-seo-push.php';
require_once QILING_INC . '/core/helpers/helpers-advanced-category-filter.php';

/**
 * WordPress 优化功能
 */
require_once QILING_INC . '/core/helpers/helpers-runtime-optimizations.php';
require_once QILING_INC . '/core/helpers/helpers-admin-whitelabel.php';
require_once QILING_INC . '/core/helpers/helpers-security-headers.php';
require_once QILING_INC . '/core/helpers/helpers-query-cache.php';
require_once QILING_INC . '/core/helpers/helpers-search.php';
require_once QILING_INC . '/core/helpers/helpers-404-redirects.php';
require_once QILING_INC . '/core/helpers/helpers-output-optimization.php';
require_once QILING_INC . '/core/helpers/helpers-front-protection.php';
require_once QILING_INC . '/core/helpers/helpers-maintenance.php';
require_once QILING_INC . '/core/helpers/helpers-blog-pagination.php';
require_once QILING_INC . '/core/helpers/helpers-category-base.php';

require_once QILING_INC . '/core/helpers/helpers-debug-tools.php';
