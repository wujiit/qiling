<?php
/**
 * Assets Class - 无外部依赖版本
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
    }
    
    /**
     * 获取资源版本号
     */
    private function get_version() {
        $custom_version = developer_starter_get_option( 'assets_version', '' );
        return ! empty( $custom_version ) ? $custom_version : DEVELOPER_STARTER_VERSION;
    }

    public function enqueue_styles() {
        $version = $this->get_version();
        
        // 主样式 (不使用Google Fonts，使用系统字体)
        wp_enqueue_style( 'developer-starter-main', DEVELOPER_STARTER_ASSETS . '/css/main.css', 
            array(), $version );

        // Swiper CSS (按需加载 - 仅在需要轮播的页面)
        if ( $this->needs_swiper() ) {
            $swiper_css = developer_starter_get_option( 'swiper_css_url', '' );
            if ( empty( $swiper_css ) ) {
                $swiper_css = 'https://cdn.jsdelivr.net/npm/swiper@12.0.3/swiper-bundle.min.css';
            }
            wp_enqueue_style( 'swiper', $swiper_css, array(), '12.0.3' );
        }

        // 动态 CSS（只包含真正动态的变量）
        wp_add_inline_style( 'developer-starter-main', $this->get_dynamic_css() );

        // 自定义 CSS
        $custom_css = developer_starter_get_option( 'custom_css', '' );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'developer-starter-main', $custom_css );
        }
        
        // Iconfont CSS（如果设置了）
        $iconfont_css = developer_starter_get_option( 'iconfont_css_url', '' );
        if ( ! empty( $iconfont_css ) ) {
            wp_enqueue_style( 'iconfont', $iconfont_css, array(), DEVELOPER_STARTER_VERSION );
        }
        
        // 认证页面样式（按需加载）
        if ( developer_starter_get_option( 'custom_auth_enable', '' ) ) {
            // 检查是否是认证页面模板
            if ( is_page_template( 'templates/template-login.php' ) || 
                 is_page_template( 'templates/template-register.php' ) || 
                 is_page_template( 'templates/template-forgot-password.php' ) ) {
                wp_enqueue_style( 'developer-starter-auth', DEVELOPER_STARTER_ASSETS . '/css/auth.css', array(), $version );
            }
        }
        
        // FAQ页面样式（按需加载）
        if ( is_page_template( 'templates/template-faq.php' ) ) {
            wp_enqueue_style( 'developer-starter-faq', DEVELOPER_STARTER_ASSETS . '/css/faq.css', array(), $version );
        }
        
        // 侧边栏样式（精确加载 - 仅在启用侧边栏的文章页）
        if ( $this->needs_sidebar() ) {
            wp_enqueue_style( 'developer-starter-sidebar', DEVELOPER_STARTER_ASSETS . '/css/sidebar.css', array(), $version );
        }
    }

    public function enqueue_scripts() {
        $version = $this->get_version();
        $needs_swiper = $this->needs_swiper();
        
        // Swiper JS (按需加载)
        if ( $needs_swiper ) {
            $swiper_js = developer_starter_get_option( 'swiper_js_url', '' );
            if ( empty( $swiper_js ) ) {
                $swiper_js = 'https://cdn.jsdelivr.net/npm/swiper@12.0.3/swiper-bundle.min.js';
            }
            wp_enqueue_script( 'swiper', $swiper_js, array(), '12.0.3', true );
        }

        // 主脚本 (依赖根据是否加载Swiper动态调整)
        $main_deps = $needs_swiper ? array( 'swiper' ) : array();
        wp_enqueue_script( 'developer-starter-main', DEVELOPER_STARTER_ASSETS . '/js/main.js', 
            $main_deps, $version, true );

        wp_localize_script( 'developer-starter-main', 'developerStarterData', array(
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'developer_starter_nonce' ),
            'homeUrl'  => home_url(),
            'themeUrl' => DEVELOPER_STARTER_URI,
        ) );

        // 页脚动画特效脚本
        $effect_enabled = developer_starter_get_option( 'footer_effect_enable', '' );
        if ( $effect_enabled ) {
            wp_enqueue_script( 'developer-starter-footer-effects', DEVELOPER_STARTER_ASSETS . '/js/footer-effects.js', 
                array(), DEVELOPER_STARTER_VERSION, true );
        }

        // 语言切换脚本
        $translate_enabled = developer_starter_get_option( 'translate_enable', '' );
        if ( $translate_enabled ) {
            $translate_js_url = developer_starter_get_option( 'translate_js_url', '' );
            if ( empty( $translate_js_url ) ) {
                $translate_js_url = DEVELOPER_STARTER_URI . '/translate/translate.js';
            }
            wp_enqueue_script( 'translate-js', $translate_js_url, array(), DEVELOPER_STARTER_VERSION, true );
            
            // 初始化脚本 - 禁用默认UI，使用手动切换模式
            $init_script = "
                if(typeof translate !== 'undefined'){
                    translate.language.setLocal('chinese_simplified');
                    translate.service.use('client.edge');
                    translate.listener.start();
                    translate.selectLanguageTag.show = false;
                }
            ";
            wp_add_inline_script( 'translate-js', $init_script );
        }

        if ( is_singular() && comments_open() ) {
            wp_enqueue_script( 'comment-reply' );
        }
    }

    public function admin_assets( $hook ) {
        if ( strpos( $hook, 'developer-starter' ) !== false || $hook === 'post.php' || $hook === 'post-new.php' ) {
            wp_enqueue_media();
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_style( 'developer-starter-admin', DEVELOPER_STARTER_ASSETS . '/css/admin.css', 
                array(), DEVELOPER_STARTER_VERSION );
        }
    }
    
    /**
     * 检测页面是否需要 Swiper
     * 基于模块检测，而非硬编码页面
     */
    private function needs_swiper() {
        // 首页通常有轮播Banner
        if ( is_front_page() || is_home() ) {
            return true;
        }
        
        // 检查当前页面是否使用了需要Swiper的模块
        global $post;
        if ( $post && is_a( $post, 'WP_Post' ) ) {
            $modules = get_post_meta( $post->ID, '_developer_starter_modules', true );
            if ( ! empty( $modules ) && is_array( $modules ) ) {
                // 需要Swiper的模块类型
                $swiper_modules = array( 
                    'banner',        // 轮播Banner
                    'clients',       // 客户Logo轮播
                    'testimonials',  // 评价轮播
                    'gallery',       // 图库（可能有轮播模式）
                );
                foreach ( $modules as $module ) {
                    if ( isset( $module['type'] ) && in_array( $module['type'], $swiper_modules ) ) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * 检测页面是否需要侧边栏样式
     * 检查文章/页面是否有活动的侧边栏
     */
    private function needs_sidebar() {
        // 文章详情页：检查是否隐藏侧边栏
        if ( is_singular( 'post' ) ) {
            $options = get_option( 'developer_starter_options', array() );
            $hide_sidebar = ! empty( $options['hide_post_sidebar'] ) && $options['hide_post_sidebar'] === '1';
            // 如果没有隐藏，且侧边栏有小工具
            if ( ! $hide_sidebar && is_active_sidebar( 'sidebar-post' ) ) {
                return true;
            }
        }
        
        // 普通页面：始终加载（页面可能有侧边栏）
        if ( is_page() && ! is_front_page() ) {
            // 检查是否有页面侧边栏
            if ( is_active_sidebar( 'sidebar-page' ) || is_active_sidebar( 'sidebar-1' ) ) {
                return true;
            }
        }
        
        // 博客页面模板
        if ( is_page_template( 'templates/template-blog.php' ) ) {
            $sidebar_position = developer_starter_get_option( 'blog_layout_sidebar', 'none' );
            if ( $sidebar_position && $sidebar_position !== 'none' ) {
                return true;
            }
        }
        
        // 归档页
        if ( is_archive() || is_search() ) {
            return is_active_sidebar( 'sidebar-1' );
        }
        
        return false;
    }

    private function get_dynamic_css() {
        $primary = developer_starter_get_option( 'primary_color', '#2563eb' );
        $primary_dark = $this->darken_color( $primary, 15 );
        $primary_light = $this->lighten_color( $primary, 10 );
        
        // 菜单hover样式
        $nav_hover_bg = developer_starter_get_option( 'nav_hover_bg', '' );
        $nav_hover_text = developer_starter_get_option( 'nav_hover_text', '#ffffff' );

        $css = ":root{
            --color-primary:{$primary};
            --color-primary-dark:{$primary_dark};
            --color-primary-light:{$primary_light};
        }
        
        /* 原生滚动动画样式（替代 AOS 库） */
        [data-aos]{opacity:0;transition:transform .6s ease-out,opacity .6s ease-out}
        [data-aos].aos-animate{opacity:1}
        [data-aos=\"fade-up\"]{transform:translateY(30px)}
        [data-aos=\"fade-up\"].aos-animate{transform:translateY(0)}
        [data-aos=\"fade-down\"]{transform:translateY(-30px)}
        [data-aos=\"fade-down\"].aos-animate{transform:translateY(0)}
        [data-aos=\"fade-left\"]{transform:translateX(30px)}
        [data-aos=\"fade-left\"].aos-animate{transform:translateX(0)}
        [data-aos=\"fade-right\"]{transform:translateX(-30px)}
        [data-aos=\"fade-right\"].aos-animate{transform:translateX(0)}
        [data-aos=\"zoom-in\"]{transform:scale(.9)}
        [data-aos=\"zoom-in\"].aos-animate{transform:scale(1)}
        
        /* ========================================
           Banner轮播图模块
           ======================================== */
        .module-banner.banner-slider {
            position: relative;
            width: 100%;
        }
        .module-banner.banner-slider.banner-height-full {
            min-height: 100vh;
        }
        .module-banner.banner-slider.banner-height-large {
            min-height: 80vh;
        }
        .module-banner.banner-slider.banner-height-medium {
            min-height: 60vh;
        }
        .module-banner.banner-slider.banner-height-small {
            min-height: 50vh;
        }
        
        /* Swiper容器 */
        .banner-swiper {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
        .banner-swiper .swiper-wrapper {
            height: 100%;
        }
        .banner-swiper .swiper-slide {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Banner 单张（非轮播） */
        .banner-single {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* 遮罩层 */
        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        /* 内容区域 */
        .banner-swiper .container,
        .banner-single .container {
            position: relative;
            z-index: 2;
        }
        .banner-content {
            text-align: center;
            color: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .banner-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .banner-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 35px;
            line-height: 1.6;
        }
        .banner-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .banner-buttons .btn {
            padding: 14px 32px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .banner-buttons .btn-primary {
            background: #fff;
            color: var(--color-primary);
            border: none;
        }
        .banner-buttons .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255,255,255,0.3);
        }
        .banner-buttons .btn-outline {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255,255,255,0.5);
        }
        .banner-buttons .btn-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: #fff;
        }
        
        /* Swiper 导航 */
        .banner-swiper .swiper-button-prev,
        .banner-swiper .swiper-button-next {
            color: #fff;
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        .banner-swiper .swiper-button-prev:hover,
        .banner-swiper .swiper-button-next:hover {
            background: rgba(255,255,255,0.3);
        }
        .banner-swiper .swiper-button-prev::after,
        .banner-swiper .swiper-button-next::after {
            font-size: 18px;
            font-weight: bold;
        }
        .banner-swiper .swiper-pagination {
            bottom: 30px !important;
        }
        .banner-swiper .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: rgba(255,255,255,0.5);
            opacity: 1;
        }
        .banner-swiper .swiper-pagination-bullet-active {
            background: #fff;
            transform: scale(1.2);
        }
        
        /* 响应式 */
        @media (max-width: 768px) {
            .banner-title {
                font-size: 2rem;
            }
            .banner-subtitle {
                font-size: 1rem;
            }
            .banner-swiper .swiper-button-prev,
            .banner-swiper .swiper-button-next {
                display: none;
            }
            .module-banner.banner-slider.banner-height-full {
                min-height: 80vh;
            }
        }
        
        /* 暗黑模式切换按钮 */
        .darkmode-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: inherit;
            border-radius: 50%;
            transition: all 0.3s;
        }
        .darkmode-toggle:hover {
            background: rgba(0,0,0,0.05);
        }
        .header-transparent:not(.header-scrolled) .darkmode-toggle {
            color: #fff;
        }
        .header-transparent:not(.header-scrolled) .darkmode-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
        
        /* 暗黑模式颜色变量 */
        html {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        html.dark-mode {
            --dm-bg: #0f172a;
            --dm-bg-secondary: #1e293b;
            --dm-bg-card: #1e293b;
            --dm-text: #e2e8f0;
            --dm-text-muted: #94a3b8;
            --dm-border: #334155;
        }
        
        html.dark-mode body {
            background-color: var(--dm-bg);
            color: var(--dm-text);
        }
        
        /* Header */
        html.dark-mode .site-header {
            background: var(--dm-bg-secondary) !important;
            border-bottom-color: var(--dm-border);
        }
        html.dark-mode .site-header.header-transparent:not(.header-scrolled) {
            background: transparent !important;
        }
        html.dark-mode .site-header.header-scrolled {
            background: var(--dm-bg-secondary) !important;
        }
        html.dark-mode .primary-navigation>ul>li>a,
        html.dark-mode .site-title-link,
        html.dark-mode .search-toggle,
        html.dark-mode .darkmode-toggle {
            color: var(--dm-text);
        }
        html.dark-mode .mobile-menu-toggle span {
            background: var(--dm-text);
        }
        html.dark-mode .user-dropdown,
        html.dark-mode .mobile-menu {
            background: var(--dm-bg-card);
        }
        html.dark-mode .user-dropdown a {
            color: var(--dm-text);
        }
        html.dark-mode .dropdown-header {
            background: var(--dm-bg);
        }
        html.dark-mode .dropdown-user-info strong {
            color: var(--dm-text);
        }
        html.dark-mode .dropdown-divider {
            background: var(--dm-border);
        }
        
        /* Main content */
        html.dark-mode .site-main,
        html.dark-mode .page-content,
        html.dark-mode .account-page {
            background: var(--dm-bg);
        }
        
        /* Cards and sections */
        html.dark-mode .about-intro,
        html.dark-mode .about-tabs-wrapper,
        html.dark-mode .account-section,
        html.dark-mode .account-nav,
        html.dark-mode .about-culture-card,
        html.dark-mode .about-gallery-item,
        html.dark-mode .team-member,
        html.dark-mode .timeline-content,
        html.dark-mode .news-card,
        html.dark-mode .product-card,
        html.dark-mode .case-card,
        html.dark-mode .faq-item,
        html.dark-mode .pricing-card,
        html.dark-mode .module-services .service-item,
        html.dark-mode .module-features .feature-item,
        html.dark-mode .module-contact form,
        html.dark-mode .search-overlay-inner {
            background: var(--dm-bg-card) !important;
            color: var(--dm-text);
        }
        
        /* Text colors */
        html.dark-mode h1, html.dark-mode h2, html.dark-mode h3, 
        html.dark-mode h4, html.dark-mode h5, html.dark-mode h6,
        html.dark-mode .section-title,
        html.dark-mode .about-tab-btn,
        html.dark-mode .account-nav-item,
        html.dark-mode .culture-title {
            color: var(--dm-text);
        }
        html.dark-mode p,
        html.dark-mode .entry-content,
        html.dark-mode .culture-desc,
        html.dark-mode .form-hint {
            color: var(--dm-text-muted);
        }
        
        /* Form inputs */
        html.dark-mode input,
        html.dark-mode textarea,
        html.dark-mode select {
            background: var(--dm-bg);
            border-color: var(--dm-border);
            color: var(--dm-text);
        }
        html.dark-mode input:focus,
        html.dark-mode textarea:focus,
        html.dark-mode select:focus {
            border-color: var(--color-primary);
        }
        html.dark-mode input::placeholder,
        html.dark-mode textarea::placeholder {
            color: var(--dm-text-muted);
        }
        
        /* Tabs */
        html.dark-mode .about-tab-btn:hover {
            background: rgba(255,255,255,0.05);
        }
        
        /* Footer */
        html.dark-mode .site-footer {
            background: var(--dm-bg-secondary);
        }
        
        /* Borders */
        html.dark-mode .section-title,
        html.dark-mode .form-actions,
        html.dark-mode .nav-divider {
            border-color: var(--dm-border);
        }
        
        /* Message boxes */
        html.dark-mode .account-message.success {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
        }
        html.dark-mode .account-message.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }
        
        /* Scrollbar */
        html.dark-mode::-webkit-scrollbar {
            width: 10px;
        }
        html.dark-mode::-webkit-scrollbar-track {
            background: var(--dm-bg);
        }
        html.dark-mode::-webkit-scrollbar-thumb {
            background: var(--dm-border);
            border-radius: 5px;
        }
        html.dark-mode::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }";
        
        // Logo背景颜色
        $logo_bg_color = developer_starter_get_option( 'logo_bg_color', '' );
        if ( ! empty( $logo_bg_color ) ) {
            $css .= "
            .site-title-link {
                background: {$logo_bg_color};
                background-clip: text;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                padding: 8px 16px;
                border-radius: 8px;
                transition: all 0.3s ease;
            }";
        }
        
        // 如果设置了菜单hover背景色
        if ( ! empty( $nav_hover_bg ) ) {
            $css .= "
            .primary-navigation>ul>li>a:hover,
            .primary-navigation li.current-menu-item>a,
            .primary-navigation li.current_page_item>a {
                background: {$nav_hover_bg};
                color: {$nav_hover_text};
            }";
        }
        
        // 滚动后菜单文字颜色
        $scrolled_menu_text_color = developer_starter_get_option( 'scrolled_menu_text_color', '' );
        if ( ! empty( $scrolled_menu_text_color ) && $scrolled_menu_text_color !== '#334155' ) {
            $css .= "
            .site-header.header-transparent.header-scrolled .primary-navigation>ul>li>a,
            .site-header.header-transparent.header-scrolled .site-title-link,
            .site-header.header-transparent.header-scrolled .search-toggle {
                color: {$scrolled_menu_text_color};
            }
            .site-header.header-transparent.header-scrolled .mobile-menu-toggle span {
                background: {$scrolled_menu_text_color};
            }";
        }
        
        // 滚动后菜单悬停文字颜色（使用!important确保覆盖其他样式）
        $scrolled_menu_hover_color = developer_starter_get_option( 'scrolled_menu_hover_color', '' );
        if ( ! empty( $scrolled_menu_hover_color ) ) {
            $css .= "
            .site-header.header-transparent.header-scrolled .primary-navigation>ul>li>a:hover,
            .site-header.header-transparent.header-scrolled .primary-navigation li.current-menu-item>a,
            .site-header.header-transparent.header-scrolled .primary-navigation li.current_page_item>a {
                color: {$scrolled_menu_hover_color} !important;
            }";
        }
        
        // 用户头像菜单样式
        $css .= "
        .header-user-menu {
            position: relative;
        }
        .header-user-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            overflow: hidden;
        }
        .header-user-btn img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid rgba(var(--color-primary-rgb, 37, 99, 235), 0.3);
            transition: border-color 0.3s;
        }
        .header-user-btn:hover img {
            border-color: var(--color-primary);
        }
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            min-width: 240px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow: hidden;
        }
        .header-user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .dropdown-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8fafc;
        }
        .dropdown-header img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
        }
        .dropdown-user-info {
            flex: 1;
            min-width: 0;
        }
        .dropdown-user-info strong {
            display: block;
            font-size: 0.95rem;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dropdown-user-info span {
            font-size: 0.8rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
        }
        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: #334155;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .user-dropdown a:hover {
            background: #f1f5f9;
            color: var(--color-primary);
        }
        .user-dropdown a.logout-link {
            color: #ef4444;
        }
        .user-dropdown a.logout-link:hover {
            background: #fef2f2;
        }
        .user-dropdown svg {
            flex-shrink: 0;
        }";
        
        // 语言切换器样式
        $css .= "
        .header-translate {
            position: relative;
        }
        .translate-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: inherit;
            border-radius: 50%;
            transition: all 0.3s;
        }
        .translate-toggle:hover {
            background: rgba(0,0,0,0.05);
        }
        .header-transparent:not(.header-scrolled) .translate-toggle {
            color: #fff;
        }
        .header-transparent:not(.header-scrolled) .translate-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
        
        /* 语言切换弹窗 - Apple风格 */
        .translate-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .translate-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .translate-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.25);
            z-index: 9999;
            max-width: 500px;
            width: 90%;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .translate-modal.show {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
        }
        .translate-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 25px;
            border-bottom: 1px solid #f1f5f9;
        }
        .translate-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }
        .translate-modal-close {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }
        .translate-modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .translate-modal-body {
            padding: 25px;
        }
        .translate-lang-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .translate-lang-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: #f8fafc;
            border: 2px solid transparent;
            border-radius: 12px;
            text-decoration: none;
            color: #334155;
            transition: all 0.2s ease;
        }
        .translate-lang-item:hover {
            background: #eff6ff;
            border-color: {$primary};
            color: {$primary};
        }
        .translate-lang-item.active {
            background: linear-gradient(135deg, rgba(37,99,235,0.1), rgba(16,185,129,0.1));
            border-color: {$primary};
            color: {$primary};
        }
        .translate-lang-item .lang-icon {
            width: 28px;
            height: 20px;
            object-fit: cover;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .translate-lang-item .lang-icon-emoji {
            font-size: 1.5rem;
        }
        .translate-lang-item .lang-name {
            font-weight: 500;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .header-translate {
                order: -1;
            }
            .translate-lang-grid {
                grid-template-columns: 1fr;
            }
            .translate-modal {
                width: 95%;
                max-width: none;
            }
        }
        
        /* ========================================
           主导航子菜单下拉样式
           ======================================== */
        .primary-navigation ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .primary-navigation>ul {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .primary-navigation>ul>li {
            position: relative;
        }
        .primary-navigation>ul>li>a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            font-weight: 500;
            color: inherit;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .primary-navigation>ul>li.menu-item-has-children>a::after {
            content: '';
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 5px solid currentColor;
            opacity: 0.6;
            transition: transform 0.3s ease;
        }
        .primary-navigation>ul>li.menu-item-has-children:hover>a::after {
            transform: rotate(-180deg);
        }
        
        /* 一级下拉菜单 */
        .primary-navigation .sub-menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 220px;
            padding: 8px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 2px 10px rgba(0,0,0,0.08);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }
        .primary-navigation>ul>li:hover>.sub-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .primary-navigation .sub-menu li {
            position: relative;
        }
        .primary-navigation .sub-menu li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            color: #334155;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .primary-navigation .sub-menu li a:hover {
            background: #f1f5f9;
            color: var(--color-primary);
        }
        .primary-navigation .sub-menu li.current-menu-item>a,
        .primary-navigation .sub-menu li.current_page_item>a {
            background: linear-gradient(135deg, rgba(37,99,235,0.1), rgba(37,99,235,0.05));
            color: var(--color-primary);
        }
        
        /* 二级下拉菜单（子级的子级） */
        .primary-navigation .sub-menu .sub-menu {
            top: -8px;
            left: 100%;
            margin-left: 5px;
        }
        .primary-navigation .sub-menu li.menu-item-has-children>a::after {
            content: '';
            width: 0;
            height: 0;
            border-top: 4px solid transparent;
            border-bottom: 4px solid transparent;
            border-left: 5px solid currentColor;
            opacity: 0.5;
        }
        .primary-navigation .sub-menu li:hover>.sub-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        /* 透明头部时的下拉 */
        .header-transparent:not(.header-scrolled) .primary-navigation .sub-menu {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        /* ========================================
           移动端导航菜单
           ======================================== */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            right: -100%;
            width: 85%;
            max-width: 360px;
            height: 100vh;
            background: #fff;
            box-shadow: -10px 0 40px rgba(0,0,0,0.15);
            z-index: 10000;
            overflow-y: auto;
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .mobile-menu.is-open {
            right: 0;
        }
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .mobile-menu-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }
        .mobile-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .mobile-menu-close {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            min-width: 44px;
            min-height: 44px;
            background: #1e293b;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            color: #ffffff;
            transition: all 0.2s;
            -webkit-tap-highlight-color: transparent;
            -webkit-appearance: none;
            appearance: none;
        }
        .mobile-menu-close svg {
            width: 20px;
            height: 20px;
            stroke-width: 2.5;
        }
        .mobile-menu-close:hover,
        .mobile-menu-close:active {
            background: #0f172a;
            transform: scale(1.05);
        }
        .mobile-menu-nav {
            padding: 15px 0;
        }
        .mobile-menu-nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .mobile-menu-nav>ul>li>a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        .mobile-menu-nav>ul>li>a:hover,
        .mobile-menu-nav>ul>li.current-menu-item>a {
            background: #f8fafc;
            color: var(--color-primary);
        }
        .mobile-menu-nav li.menu-item-has-children>a::after {
            content: '+';
            font-size: 1.2rem;
            font-weight: 400;
            color: #94a3b8;
            transition: transform 0.3s;
        }
        .mobile-menu-nav li.menu-item-has-children.is-open>a::after {
            transform: rotate(45deg);
        }
        /* 移动端子菜单 */
        .mobile-menu-nav .sub-menu {
            display: none;
            background: #f8fafc;
            padding: 5px 0;
        }
        .mobile-menu-nav li.is-open>.sub-menu {
            display: block;
        }
        .mobile-menu-nav .sub-menu li a {
            display: block;
            padding: 12px 20px 12px 35px;
            color: #475569;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .mobile-menu-nav .sub-menu li a:hover,
        .mobile-menu-nav .sub-menu li.current-menu-item a {
            color: var(--color-primary);
            background: rgba(37,99,235,0.05);
        }
        /* 移动端二级子菜单 */
        .mobile-menu-nav .sub-menu .sub-menu li a {
            padding-left: 50px;
            font-size: 0.85rem;
        }
        /* 移动端菜单Logo */
        .mobile-menu-logo {
            display: flex;
            align-items: center;
        }
        .mobile-menu-logo img {
            max-height: 36px;
            width: auto;
        }
        .mobile-menu-logo .site-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }
        .mobile-menu-logo .custom-logo-link img {
            max-height: 36px;
            width: auto;
        }
        /* 移动端菜单底部 */
        .mobile-menu-footer {
            padding: 20px;
            margin-top: auto;
            border-top: 1px solid #e2e8f0;
        }
        .mobile-phone-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, var(--color-primary, #2563eb) 0%, #7c3aed 100%);
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .mobile-phone-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
        }
        
        /* ========================================
           页脚导航
           ======================================== */
        .footer-navigation ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 25px;
        }
        .footer-navigation a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            position: relative;
        }
        .footer-navigation a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--color-primary);
            transition: width 0.3s ease;
        }
        .footer-navigation a:hover {
            color: #fff;
        }
        .footer-navigation a:hover::after {
            width: 100%;
        }
        .footer-navigation .current-menu-item a,
        .footer-navigation .current_page_item a {
            color: #fff;
        }
        
        /* ========================================
           社交媒体菜单
           ======================================== */
        .social-navigation ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .social-navigation a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8);
            border-radius: 50%;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .social-navigation a:hover {
            background: var(--color-primary);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37,99,235,0.4);
        }
        .social-navigation a svg,
        .social-navigation a i {
            width: 18px;
            height: 18px;
        }
        
        /* ========================================
           暗黑模式导航
           ======================================== */
        html.dark-mode .primary-navigation .sub-menu {
            background: var(--dm-bg-card);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        html.dark-mode .primary-navigation .sub-menu li a {
            color: var(--dm-text);
        }
        html.dark-mode .primary-navigation .sub-menu li a:hover {
            background: rgba(255,255,255,0.05);
            color: var(--color-primary);
        }
        html.dark-mode .mobile-menu {
            background: var(--dm-bg-card);
        }
        html.dark-mode .mobile-menu-header {
            border-bottom-color: var(--dm-border);
        }
        html.dark-mode .mobile-menu-close {
            background: var(--dm-bg);
            color: var(--dm-text-muted);
        }
        html.dark-mode .mobile-menu-nav>ul>li>a {
            color: var(--dm-text);
        }
        html.dark-mode .mobile-menu-nav>ul>li>a:hover {
            background: rgba(255,255,255,0.05);
        }
        html.dark-mode .mobile-menu-nav .sub-menu {
            background: rgba(0,0,0,0.2);
        }
        html.dark-mode .mobile-menu-nav .sub-menu li a {
            color: var(--dm-text-muted);
        }
        html.dark-mode .mobile-menu-nav .sub-menu li a:hover {
            color: var(--dm-text);
        }
        html.dark-mode .mobile-menu-logo .site-name {
            color: var(--dm-text);
        }
        html.dark-mode .mobile-menu-footer {
            border-top-color: var(--dm-border);
        }
        
        /* ========================================
           响应式
           ======================================== */
        @media (max-width: 991px) {
            .primary-navigation {
                display: none;
            }
            .mobile-menu {
                display: flex;
                flex-direction: column;
            }
            .mobile-menu-overlay {
                display: block;
            }
        }
        @media (max-width: 768px) {
            .footer-navigation ul {
                justify-content: center;
            }
            .social-navigation ul {
                justify-content: center;
            }
        }
        
        /* ========================================
           分类页面样式
           ======================================== */
        .category-header {
            padding: 100px 0 60px;
            text-align: center;
        }
        .category-breadcrumb {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .category-breadcrumb a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }
        .category-breadcrumb a:hover {
            color: #fff;
        }
        .category-breadcrumb .breadcrumb-separator {
            color: rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
        }
        .category-breadcrumb .breadcrumb-current {
            color: #fff;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .category-icon {
            display: inline-flex;
            align-items: center;
        }
        .category-icon img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
        .category-icon-large {
            display: inline-flex;
            align-items: center;
            font-size: 2.5rem;
            margin-right: 10px;
        }
        .category-icon-large img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        .category-title {
            color: #fff;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .category-description {
            color: rgba(255,255,255,0.8);
            max-width: 600px;
            margin: 0 auto 20px;
            line-height: 1.6;
        }
        .category-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        .category-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
        }
        .category-count svg {
            opacity: 0.7;
        }
        .category-content {
            padding: 60px 0;
        }
        
        /* 文章列表布局 - 卡片 */
        .posts-card {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        .post-item-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .post-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }
        .post-item-card .post-thumb {
            display: block;
            aspect-ratio: 16/10;
            overflow: hidden;
        }
        .post-item-card .post-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .post-item-card:hover .post-thumb img {
            transform: scale(1.05);
        }
        .post-item-card .post-content {
            padding: 20px;
        }
        .post-item-card .post-date {
            font-size: 0.85rem;
            color: #64748b;
            display: block;
            margin-bottom: 8px;
        }
        .post-item-card .post-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 10px;
            line-height: 1.4;
        }
        .post-item-card .post-title a {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.3s;
        }
        .post-item-card .post-title a:hover {
            color: var(--color-primary);
        }
        .post-item-card .post-excerpt {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0;
        }
        
        /* 文章列表布局 - 列表 */
        .posts-list {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .post-item-list {
            display: flex;
            gap: 30px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .post-item-list:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        .post-item-list .post-thumb {
            flex: 0 0 280px;
            aspect-ratio: auto;
            overflow: hidden;
        }
        .post-item-list .post-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            min-height: 200px;
            transition: transform 0.5s ease;
        }
        .post-item-list:hover .post-thumb img {
            transform: scale(1.05);
        }
        .post-item-list .post-content {
            flex: 1;
            padding: 25px 25px 25px 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .post-item-list .post-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 12px;
            font-size: 0.85rem;
            color: #64748b;
        }
        .post-item-list .post-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0 0 12px;
            line-height: 1.4;
        }
        .post-item-list .post-title a {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.3s;
        }
        .post-item-list .post-title a:hover {
            color: var(--color-primary);
        }
        .post-item-list .post-excerpt {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.7;
            margin: 0 0 15px;
        }
        .post-item-list .read-more {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .post-item-list .read-more:hover {
            color: var(--color-primary-dark);
        }
        
        /* 文章列表布局 - 网格 */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .post-item-grid {
            text-align: center;
        }
        .post-item-grid .post-thumb {
            display: block;
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
            position: relative;
        }
        .post-item-grid .post-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s ease;
        }
        .post-item-grid:hover .post-thumb img {
            transform: scale(1.05);
        }
        .post-item-grid .post-thumb::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .post-item-grid:hover .post-thumb::after {
            opacity: 1;
        }
        .post-item-grid .no-thumb-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        }
        .post-item-grid .post-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0 0 6px;
            line-height: 1.4;
        }
        .post-item-grid .post-title a {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.3s;
        }
        .post-item-grid .post-title a:hover {
            color: var(--color-primary);
        }
        .post-item-grid .post-date {
            font-size: 0.8rem;
            color: #94a3b8;
        }
        
        /* 文章列表布局 - 杂志 */
        .magazine-featured {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .magazine-featured-thumb {
            aspect-ratio: 4/3;
            overflow: hidden;
        }
        .magazine-featured-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .magazine-featured:hover .magazine-featured-thumb img {
            transform: scale(1.03);
        }
        .magazine-featured-content {
            padding: 40px 40px 40px 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .magazine-featured-content .post-date {
            font-size: 0.9rem;
            color: var(--color-primary);
            font-weight: 500;
            margin-bottom: 15px;
        }
        .magazine-featured-content .post-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 20px;
            line-height: 1.3;
        }
        .magazine-featured-content .post-title a {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.3s;
        }
        .magazine-featured-content .post-title a:hover {
            color: var(--color-primary);
        }
        .magazine-featured-content .post-excerpt {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.7;
            margin: 0 0 25px;
        }
        .read-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--color-primary);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            align-self: flex-start;
        }
        .read-more-btn:hover {
            background: var(--color-primary-dark);
            transform: translateX(5px);
        }
        .posts-magazine {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        /* 无文章提示 */
        .no-posts {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
        }
        .no-posts svg {
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .no-posts p {
            font-size: 1.1rem;
            margin: 0;
        }
        
        /* ========================================
           分页样式（全站通用）
           ======================================== */
        .ds-pagination {
            margin-top: 50px;
            text-align: center;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .ds-pagination ul,
        .ds-pagination .page-numbers:not(a):not(span) {
            list-style: none;
            margin: 0;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .ds-pagination li {
            display: inline-flex;
        }
        .ds-pagination a,
        .ds-pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 44px;
            padding: 0 16px;
            background: #fff;
            color: #334155;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            gap: 6px;
        }
        .ds-pagination a:hover {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.25);
        }
        .ds-pagination .current,
        .ds-pagination span.current {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.25);
        }
        .ds-pagination .dots {
            background: transparent;
            border: none;
            color: #94a3b8;
            min-width: auto;
            padding: 0 8px;
        }
        .ds-pagination .prev,
        .ds-pagination .next {
            gap: 8px;
        }
        .ds-pagination .prev svg,
        .ds-pagination .next svg {
            flex-shrink: 0;
        }
        
        /* WordPress默认分页美化 */
        .pagination-nav,
        nav.pagination,
        .navigation.pagination {
            margin-top: 50px;
            text-align: center;
        }
        .pagination-nav .nav-links,
        nav.pagination .nav-links,
        .navigation.pagination .nav-links {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .pagination-nav .page-numbers,
        nav.pagination .page-numbers,
        .navigation.pagination .page-numbers {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 44px;
            padding: 0 16px;
            background: #fff;
            color: #334155;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .pagination-nav a.page-numbers:hover,
        nav.pagination a.page-numbers:hover,
        .navigation.pagination a.page-numbers:hover {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.25);
        }
        .pagination-nav .page-numbers.current,
        nav.pagination .page-numbers.current,
        .navigation.pagination .page-numbers.current {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.25);
        }
        .pagination-nav .page-numbers.dots,
        nav.pagination .page-numbers.dots,
        .navigation.pagination .page-numbers.dots {
            background: transparent;
            border: none;
            color: #94a3b8;
            min-width: auto;
            padding: 0 8px;
        }
        
        /* 分类页面暗黑模式 */
        html.dark-mode .post-item-card,
        html.dark-mode .post-item-list,
        html.dark-mode .magazine-featured {
            background: var(--dm-bg-card);
        }
        html.dark-mode .post-item-card .post-title a,
        html.dark-mode .post-item-list .post-title a,
        html.dark-mode .post-item-grid .post-title a,
        html.dark-mode .magazine-featured-content .post-title a {
            color: var(--dm-text);
        }
        html.dark-mode .post-item-card .post-excerpt,
        html.dark-mode .post-item-list .post-excerpt,
        html.dark-mode .magazine-featured-content .post-excerpt {
            color: var(--dm-text-muted);
        }
        html.dark-mode .ds-pagination a,
        html.dark-mode .ds-pagination span,
        html.dark-mode .pagination-nav .page-numbers,
        html.dark-mode nav.pagination .page-numbers {
            background: var(--dm-bg-card);
            border-color: var(--dm-border);
            color: var(--dm-text);
        }
        html.dark-mode .ds-pagination a:hover,
        html.dark-mode .pagination-nav a.page-numbers:hover,
        html.dark-mode nav.pagination a.page-numbers:hover {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: #fff;
        }
        html.dark-mode .no-posts {
            color: var(--dm-text-muted);
        }
        
        /* 分类页面响应式 */
        @media (max-width: 991px) {
            .posts-card {
                grid-template-columns: repeat(2, 1fr);
            }
            .posts-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .magazine-featured {
                grid-template-columns: 1fr;
            }
            .magazine-featured-content {
                padding: 30px;
            }
            .posts-magazine {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .category-header {
                padding: 80px 0 50px;
            }
            .category-title {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 10px;
            }
            .category-icon-large {
                margin-right: 0;
            }
            .posts-card,
            .posts-magazine {
                grid-template-columns: 1fr;
            }
            .posts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .post-item-list {
                flex-direction: column;
            }
            .post-item-list .post-thumb {
                flex: none;
            }
            .post-item-list .post-content {
                padding: 20px;
            }
            .ds-pagination a,
            .ds-pagination span,
            .pagination-nav .page-numbers,
            nav.pagination .page-numbers {
                min-width: 40px;
                height: 40px;
                padding: 0 12px;
                font-size: 0.9rem;
            }
        }";
        
        return $css;
    }

    private function darken_color( $hex, $percent ) {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) !== 6 ) {
            return '#1d4ed8';
        }
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        
        $r = (int) max( 0, $r - ( $r * $percent / 100 ) );
        $g = (int) max( 0, $g - ( $g * $percent / 100 ) );
        $b = (int) max( 0, $b - ( $b * $percent / 100 ) );
        
        return sprintf( '#%02x%02x%02x', $r, $g, $b );
    }

    private function lighten_color( $hex, $percent ) {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) !== 6 ) {
            return '#3b82f6';
        }
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        
        $r = (int) min( 255, $r + ( ( 255 - $r ) * $percent / 100 ) );
        $g = (int) min( 255, $g + ( ( 255 - $g ) * $percent / 100 ) );
        $b = (int) min( 255, $b + ( ( 255 - $b ) * $percent / 100 ) );
        
        return sprintf( '#%02x%02x%02x', $r, $g, $b );
    }
}
