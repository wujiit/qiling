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
    /**
     * 当前请求页面模块缓存
     *
     * @var array|null
     */
    private $modules_cache = null;

    /**
     * 当前请求页面对象缓存
     *
     * @var \WP_Post|null
     */
    private $current_post_cache = null;

    /**
     * 当前请求是否需要 Swiper 缓存
     *
     * @var bool|null
     */
    private $needs_swiper_cache = null;

    /**
     * 当前请求是否需要模块样式缓存
     *
     * @var bool|null
     */
    private $needs_modules_cache = null;

    /**
     * 当前请求是否需要博客视觉预设样式缓存
     *
     * @var bool|null
     */
    private $needs_blog_presets_cache = null;

    /**
     * 当前请求需要加载的博客视觉预设缓存
     *
     * @var array<int,string>|null
     */
    private $blog_presets_to_enqueue_cache = null;

    /**
     * 当前请求页面模块类型缓存
     *
     * @var array<int,string>|null
     */
    private $module_types_cache = null;

    /**
     * 当前请求是否需要 jQuery（模块依赖）缓存
     *
     * @var bool|null
     */
    private $needs_jquery_cache = null;

    /**
     * 当前请求是否需要左侧导航缓存
     *
     * @var bool|null
     */
    private $needs_left_nav_cache = null;

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_comments_styles' ), 30 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'optimize_woocommerce_scripts' ), 100 );
    }
    
    /**
     * 获取资源版本号
     */
    private function get_version() {
        $version = (string) DEVELOPER_STARTER_VERSION;

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $main_css = DEVELOPER_STARTER_DIR . '/assets/css/main.css';
            if ( file_exists( $main_css ) ) {
                $version = (string) filemtime( $main_css );
            }
        }

        if ( function_exists( 'developer_starter_get_assets_version' ) && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
            $version = (string) developer_starter_get_assets_version();
        }

        return (string) apply_filters( 'developer_starter_assets_version', $version );
    }

    /**
     * 获取主题内置 Swiper 样式地址
     */
    private function get_default_swiper_style_url() {
        return DEVELOPER_STARTER_ASSETS . '/css/vendor/swiper-bundle.min.css';
    }

    /**
     * 获取主题内置 Swiper 脚本地址
     */
    private function get_default_swiper_script_url() {
        return DEVELOPER_STARTER_ASSETS . '/js/vendor/swiper-bundle.min.js';
    }

    /**
     * 获取主题内置 Chart.js 地址
     */
    private function get_default_chart_script_url() {
        return DEVELOPER_STARTER_ASSETS . '/js/vendor/chart.min.js';
    }

    /**
     * 规范化资源地址，兼容完整 URL 与站内相对路径。
     *
     * @param string $url 原始资源地址。
     * @return string
     */
    private function normalize_asset_url( $url ) {
        if ( function_exists( 'developer_starter_normalize_asset_url' ) ) {
            return (string) developer_starter_normalize_asset_url( $url );
        }

        return (string) $url;
    }

    /**
     * 规范化资源地址，并统一套用外部资源白名单。
     *
     * @param string $url 原始资源地址。
     * @return string
     */
    private function normalize_allowed_asset_url( $url ) {
        $url = $this->normalize_asset_url( $url );
        if ( '' === $url ) {
            return '';
        }

        if ( function_exists( 'developer_starter_is_external_asset_url_allowed' ) ) {
            return developer_starter_is_external_asset_url_allowed( $url ) ? $url : '';
        }

        $parts = wp_parse_url( $url );
        if ( false === $parts || empty( $parts['host'] ) ) {
            return '';
        }

        $scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
        if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
            return '';
        }

        $host       = strtolower( (string) $parts['host'] );
        $site_hosts = array();
        foreach ( array( home_url( '/' ), site_url( '/' ) ) as $site_url ) {
            $site_parts = wp_parse_url( $site_url );
            if ( false !== $site_parts && ! empty( $site_parts['host'] ) ) {
                $site_hosts[] = strtolower( (string) $site_parts['host'] );
            }
        }

        return in_array( $host, array_unique( $site_hosts ), true ) ? $url : '';
    }

    /**
     * 获取受白名单保护的第三方库资源。
     *
     * @param string      $asset_key      资源键名。
     * @param string|null $filter_version 兼容旧过滤器的版本参数。
     * @return array<string,string>
     */
    private function get_third_party_asset( $asset_key, $filter_version = null ) {
        if ( function_exists( 'developer_starter_get_third_party_asset' ) ) {
            return developer_starter_get_third_party_asset( $asset_key, '', $filter_version );
        }

        $fallbacks = array(
            'swiper_css' => array(
                'url'     => $this->get_default_swiper_style_url(),
                'version' => '12.0.3',
            ),
            'swiper_js'  => array(
                'url'     => $this->get_default_swiper_script_url(),
                'version' => '12.0.3',
            ),
            'chart_js'   => array(
                'url'     => $this->get_default_chart_script_url(),
                'version' => '2.7.2',
            ),
        );

        $fallback = isset( $fallbacks[ $asset_key ] ) ? $fallbacks[ $asset_key ] : array( 'url' => '', 'version' => '' );

        return array(
            'url'        => $this->normalize_asset_url( $fallback['url'] ),
            'version'    => (string) $fallback['version'],
            'local_url'  => $this->normalize_asset_url( $fallback['url'] ),
            'option_key' => '',
        );
    }

    /**
     * 获取 translate.js 的页面原始语言简码。
     *
     * @return string
     */
    private function get_translate_local_language() {
        $theme_language = (string) developer_starter_get_option( 'theme_language', 'zh_CN' );

        switch ( $theme_language ) {
            case 'en_US':
                return 'english';
            case 'zh_CN':
            default:
                return 'chinese_simplified';
        }
    }

    /**
     * 获取前台语言切换模式。
     *
     * @return string
     */
    private function get_frontend_language_switch_mode() {
        if ( function_exists( 'developer_starter_get_frontend_language_switch_mode' ) ) {
            return (string) developer_starter_get_frontend_language_switch_mode();
        }

        return '';
    }

    /**
     * 获取 iconfont 通用样式
     */
    private function get_iconfont_inline_css() {
        return "
        .qs-icon {
           width: 1em;
           height: 1em;
           vertical-align: -0.15em;
           fill: currentColor;
           overflow: hidden;
        }";
    }

    /**
     * 解析自定义 JS 中的外链 script 标签和内联脚本
     *
     * @param string $custom_js 原始自定义 JS.
     * @return array{external: array<int, array{src: string, async: bool, defer: bool}>, inline: array<int, string>}
     */
    private function parse_custom_script_fragments( $custom_js ) {
        $fragments = array(
            'external' => array(),
            'inline'   => array(),
        );

        $custom_js = trim( (string) $custom_js );
        if ( '' === $custom_js ) {
            return $fragments;
        }

        if ( false === stripos( $custom_js, '<script' ) ) {
            $fragments['inline'][] = $custom_js;
            return $fragments;
        }

        if ( preg_match_all( '/<script\b([^>]*)>(.*?)<\/script>/is', $custom_js, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $attrs = isset( $match[1] ) ? (string) $match[1] : '';
                $code  = isset( $match[2] ) ? trim( (string) $match[2] ) : '';
                $src   = '';

                if ( preg_match( '/\ssrc\s*=\s*([\'"])(.*?)\1/i', $attrs, $src_match ) ) {
                    $src = esc_url_raw( (string) $src_match[2] );
                } elseif ( preg_match( '/\ssrc\s*=\s*([^\s>]+)/i', $attrs, $src_match ) ) {
                    $src = esc_url_raw( trim( (string) $src_match[1], "\"'" ) );
                }

                if ( '' !== $src ) {
                    $fragments['external'][] = array(
                        'src'   => $src,
                        'async' => false !== stripos( $attrs, ' async' ),
                        'defer' => false !== stripos( $attrs, ' defer' ),
                    );
                }

                if ( '' !== $code ) {
                    $fragments['inline'][] = $code;
                }
            }
        }

        $remaining = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $custom_js );
        $remaining = preg_replace( '/<noscript\b[^>]*>.*?<\/noscript>/is', '', (string) $remaining );
        $remaining = trim( (string) $remaining );

        if ( '' !== $remaining ) {
            $fragments['inline'][] = $remaining;
        }

        return $fragments;
    }

    /**
     * 按标准 script 标签方式加载自定义 JS，避免前端再动态插入外链脚本
     *
     * @param string $custom_js 自定义 JS.
     * @return void
     */
    private function enqueue_custom_js_option( $custom_js ) {
        $fragments = $this->parse_custom_script_fragments( $custom_js );
        $last_handle = 'developer-starter-main';

        foreach ( $fragments['external'] as $index => $script_data ) {
            $src = isset( $script_data['src'] ) ? $this->normalize_allowed_asset_url( (string) $script_data['src'] ) : '';
            if ( '' === $src ) {
                continue;
            }

            $handle = 'developer-starter-custom-js-' . substr( md5( $src . '|' . $index ), 0, 12 );
            if ( ! wp_script_is( $handle, 'registered' ) ) {
                wp_register_script( $handle, $src, array( $last_handle ), null, true );
            }

            if ( ! empty( $script_data['defer'] ) && function_exists( 'wp_script_add_data' ) ) {
                wp_script_add_data( $handle, 'defer', true );
            }

            if ( empty( $fragments['inline'] ) && ! empty( $script_data['async'] ) && function_exists( 'wp_script_add_data' ) ) {
                wp_script_add_data( $handle, 'async', true );
            }

            wp_enqueue_script( $handle );
            $last_handle = $handle;
        }

        $inline_chunks = array_filter( array_map( 'trim', $fragments['inline'] ), 'strlen' );
        if ( empty( $inline_chunks ) ) {
            return;
        }

        wp_add_inline_script( $last_handle, implode( "\n", $inline_chunks ), 'after' );
    }

    /**
     * 判断一段后台配置代码是否显式依赖 jQuery。
     *
     * @param mixed $code 后台填写的 JS / 第三方代码。
     * @return bool
     */
    private function code_snippet_needs_jquery( $code ) {
        $code = (string) $code;
        if ( '' === trim( $code ) ) {
            return false;
        }

        return (bool) preg_match( '/(?:\bjQuery\b|\$\s*(?:\(|\.))/i', $code );
    }

    /**
     * 自定义 JS 与第三方代码可能是内联输出，若里面使用 jQuery，需要提前加载 WP jQuery。
     *
     * @return bool
     */
    private function needs_jquery_for_configured_code() {
        $snippets = array(
            developer_starter_get_option( 'custom_js', '' ),
        );

        if ( '1' === (string) developer_starter_get_option( 'international_third_party_code_enable', '' ) ) {
            foreach ( array( 'head', 'footer', 'analytics', 'ads', 'custom' ) as $group_id ) {
                if ( '1' !== (string) developer_starter_get_option( 'international_code_' . $group_id . '_enable', '' ) ) {
                    continue;
                }

                $snippets[] = developer_starter_get_option( 'international_code_' . $group_id . '_content', '' );
            }
        }

        $needs_jquery = false;
        foreach ( $snippets as $snippet ) {
            if ( $this->code_snippet_needs_jquery( $snippet ) ) {
                $needs_jquery = true;
                break;
            }
        }

        return (bool) apply_filters( 'developer_starter_configured_code_needs_jquery', $needs_jquery, $snippets );
    }

    /**
     * 登录弹窗常被第三方插件用内联 jQuery 触发器接入，需要在前台提前准备 jQuery。
     *
     * @return bool
     */
    private function needs_jquery_for_login_compatibility() {
        $login_modal_enabled = developer_starter_get_option( 'header_login_enable', '' ) && ! is_user_logged_in();
        $needs_jquery = (bool) $login_modal_enabled;

        return (bool) apply_filters( 'developer_starter_login_compatibility_needs_jquery', $needs_jquery, $login_modal_enabled );
    }

    /**
     * 将非核心前端配置作为按需片段合并到全局数据，避免 wp_localize_script 常驻大对象。
     *
     * @param array $data 运行时配置片段。
     */
    private function add_main_script_data_fragment( $data ) {
        if ( empty( $data ) || ! is_array( $data ) ) {
            return;
        }

        $json = wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
        if ( ! is_string( $json ) || '' === $json || 'null' === $json ) {
            return;
        }

        wp_add_inline_script(
            'developer-starter-main',
            'window.developerStarterData=Object.assign(window.developerStarterData||{},' . $json . ');',
            'after'
        );
    }

    /**
     * Enqueue a feature chunk that depends on the small main runtime.
     *
     * @param string       $slug    Feature slug.
     * @param string       $file    JS file name in assets/js.
     * @param string       $version Fallback theme version.
     * @param array|string $deps    Extra dependencies.
     * @return void
     */
    private function enqueue_main_feature_script( $slug, $file, $version, $deps = array() ) {
        $file = ltrim( (string) $file, '/\\' );
        $path = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/js/' . $file;
        if ( ! file_exists( $path ) ) {
            return;
        }

        $deps = is_array( $deps ) ? $deps : array( $deps );
        array_unshift( $deps, 'developer-starter-main' );
        $deps = array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_key', $deps ),
                    'strlen'
                )
            )
        );

        wp_enqueue_script(
            'developer-starter-feature-' . sanitize_key( $slug ),
            DEVELOPER_STARTER_ASSETS . '/js/' . $file,
            $deps,
            (string) filemtime( $path ),
            true
        );
    }


    public function enqueue_styles() {
        $version = $this->get_version();
        $needs_modules = $this->needs_modules();
        
        // CSS 压缩判断
        $use_min = developer_starter_get_option( 'css_minify_enable', '' );
        do_action( 'developer_starter_before_enqueue_styles', $version, $use_min );
        
        $main_css = DEVELOPER_STARTER_ASSETS . '/css/main.css';
        if ( $use_min && file_exists( DEVELOPER_STARTER_DIR . '/assets/css/main.min.css' ) ) {
            $main_css = DEVELOPER_STARTER_ASSETS . '/css/main.min.css';
        }
        $main_css = apply_filters( 'developer_starter_main_css_url', $main_css, $version, $use_min );

        // 主样式 (不使用Google Fonts，使用系统字体)
        wp_enqueue_style( 'developer-starter-main', $main_css, array(), $version );
    
        // 功能模块样式（支持整包 / 按需两种加载方式）
        if ( $needs_modules ) {
            $this->enqueue_modules_styles( $version, (bool) $use_min );
            $advanced_css_file = DEVELOPER_STARTER_DIR . '/assets/css/module-advanced-styles.css';
            if ( file_exists( $advanced_css_file ) ) {
                wp_enqueue_style(
                    'developer-starter-module-advanced-styles',
                    DEVELOPER_STARTER_ASSETS . '/css/module-advanced-styles.css',
                    array( 'developer-starter-main' ),
                    (string) filemtime( $advanced_css_file )
                );
            }
        }

        // Swiper CSS: 先注册句柄，避免其他位置 enqueue 时句柄缺失；仅在需要轮播时真正加载。
        $needs_swiper = $this->needs_swiper();
        $this->register_swiper_style( $version );
        // 默认仅在判定需要轮播时加载；可通过过滤器按需放宽。
        $enqueue_swiper_style = (bool) apply_filters(
            'developer_starter_enqueue_swiper_style',
            $needs_swiper,
            $needs_swiper,
            $needs_modules
        );
        if ( $enqueue_swiper_style ) {
            wp_enqueue_style( 'swiper' );

            // 本地 Swiper CSS 兜底：默认仅处理非法外链回退；如需兼容历史双加载，可通过过滤器开启。
            $local_swiper_css = $this->get_default_swiper_style_url();
            $local_swiper_css = $this->normalize_asset_url( $local_swiper_css );
            $local_swiper_css = apply_filters( 'developer_starter_swiper_css_local_fallback_url', $local_swiper_css, $version );

            $registered_swiper_src = '';
            if ( function_exists( 'wp_styles' ) ) {
                $wp_styles = wp_styles();
                if ( $wp_styles && isset( $wp_styles->registered['swiper'] ) ) {
                    $registered_swiper_src = (string) $wp_styles->registered['swiper']->src;
                }
            }

            $enqueue_css_fallback = (bool) apply_filters(
                'developer_starter_enqueue_swiper_css_local_fallback',
                false,
                $registered_swiper_src,
                $local_swiper_css,
                $version
            );

            if ( $enqueue_css_fallback && $local_swiper_css && $local_swiper_css !== $registered_swiper_src ) {
                if ( ! wp_style_is( 'developer-starter-swiper-local-fallback', 'registered' ) ) {
                    wp_register_style( 'developer-starter-swiper-local-fallback', $local_swiper_css, array(), '12.0.3' );
                }
                wp_enqueue_style( 'developer-starter-swiper-local-fallback' );
            }
        }

        // 动态 CSS（只包含真正动态的变量）
        wp_add_inline_style( 'developer-starter-main', $this->get_dynamic_css() );

        if ( function_exists( 'developer_starter_lazy_load_placeholder_enabled' ) && developer_starter_lazy_load_placeholder_enabled() ) {
            wp_enqueue_style(
                'developer-starter-lazy-image-placeholder',
                DEVELOPER_STARTER_ASSETS . '/css/lazy-image-placeholder.css',
                array( 'developer-starter-main' ),
                $version
            );
        }

        // 自定义 CSS
        $custom_css = developer_starter_get_option( 'custom_css', '' );
        $custom_css = apply_filters( 'developer_starter_custom_css', $custom_css, $version );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'developer-starter-main', $custom_css );
        }
        
        // Iconfont 资源
        $iconfont_js = developer_starter_get_option( 'iconfont_js_url', '' );
        $iconfont_js = $this->normalize_allowed_asset_url( $iconfont_js );
        if ( ! empty( $iconfont_js ) ) {
            wp_add_inline_style( 'developer-starter-main', $this->get_iconfont_inline_css() );
            wp_enqueue_script( 'iconfont', $iconfont_js, array(), DEVELOPER_STARTER_VERSION, true );
        }

        // 认证页面样式（按需加载）
        if ( developer_starter_get_option( 'custom_auth_enable', '' ) ) {
            // 检查是否是认证页面模板
            if ( is_page_template( 'templates/template-login.php' ) || 
                 is_page_template( 'templates/template-register.php' ) || 
                 is_page_template( 'templates/template-forgot-password.php' ) ) {
                wp_enqueue_style( 'developer-starter-auth', DEVELOPER_STARTER_ASSETS . '/css/auth.css', array(), $version );
                if ( function_exists( 'developer_starter_get_auth_page_background_css' ) ) {
                    $auth_page_background_css = developer_starter_get_auth_page_background_css();
                    if ( '' !== $auth_page_background_css ) {
                        wp_add_inline_style( 'developer-starter-auth', $auth_page_background_css );
                    }
                }
            }
        }
        
        // FAQ页面样式（按需加载）
        if ( is_page_template( 'templates/template-faq.php' ) ) {
            wp_enqueue_style( 'developer-starter-faq', DEVELOPER_STARTER_ASSETS . '/css/faq.css', array(), $version );
        }

        if ( function_exists( 'developer_starter_enqueue_current_page_visual_skin_styles' ) ) {
            developer_starter_enqueue_current_page_visual_skin_styles( $version );
        }
        
        // 侧边栏样式（精确加载 - 仅在启用侧边栏的文章页）
        if ( $this->needs_sidebar() ) {
            wp_enqueue_style( 'developer-starter-sidebar', DEVELOPER_STARTER_ASSETS . '/css/sidebar.css', array(), $version );
        }

        if ( $this->needs_blog_presets() ) {
            $this->enqueue_blog_preset_styles( $version );
        }

        if ( is_search() ) {
            $search_page_css_file = DEVELOPER_STARTER_DIR . '/assets/css/search-page.css';
            if ( file_exists( $search_page_css_file ) ) {
                wp_enqueue_style(
                    'developer-starter-search-page',
                    DEVELOPER_STARTER_ASSETS . '/css/search-page.css',
                    array( 'developer-starter-main' ),
                    (string) filemtime( $search_page_css_file )
                );
            }
        }

        if ( is_404() ) {
            $error_404_css_file = DEVELOPER_STARTER_DIR . '/assets/css/error-404.css';
            if ( file_exists( $error_404_css_file ) ) {
                wp_enqueue_style(
                    'developer-starter-error-404',
                    DEVELOPER_STARTER_ASSETS . '/css/error-404.css',
                    array( 'developer-starter-main' ),
                    (string) filemtime( $error_404_css_file )
                );
            }
        }

        // 左侧导航样式（仅在配置且设置了左侧菜单时加载）
        if ( $this->needs_left_nav() ) {
            wp_enqueue_style( 'developer-starter-left-nav', DEVELOPER_STARTER_ASSETS . '/css/left-nav.css', array(), $version );
        }

        do_action( 'developer_starter_after_enqueue_styles', $version );
    }

    /**
     * 加载评论区样式。
     *
     * 评论模板原本在 comments.php 底部输出内联 CSS，拆成文件后保持较晚加载，
     * 避免被文章增强样式里的通用评论选择器覆盖。
     */
    public function enqueue_comments_styles() {
        if ( ! is_singular() || ( ! comments_open() && ! get_comments_number() ) ) {
            return;
        }

        wp_enqueue_style(
            'developer-starter-comments',
            DEVELOPER_STARTER_ASSETS . '/css/comments.css',
            array( 'developer-starter-main' ),
            $this->get_version()
        );
    }

    public function enqueue_scripts() {
        $version = $this->get_version();
        $needs_swiper = $this->needs_swiper();
        $needs_jquery = $this->needs_jquery();
        $needs_modules = $this->needs_modules();
        do_action( 'developer_starter_before_enqueue_scripts', $version, $needs_swiper );

        // 仅在存在 jQuery 依赖模块时加载，避免游客端脚本因 jQuery 缺失而失效。
        if ( $needs_jquery ) {
            wp_enqueue_script( 'jquery' );
            if ( function_exists( 'wp_script_add_data' ) ) {
                wp_script_add_data( 'jquery', 'group', 0 );
                wp_script_add_data( 'jquery-core', 'group', 0 );
                wp_script_add_data( 'jquery-migrate', 'group', 0 );
            }
        }
        
        // Swiper JS: 先注册句柄，按需加载。
        $this->register_swiper_script( $version );
        $enqueue_swiper_script = (bool) apply_filters(
            'developer_starter_enqueue_swiper_script',
            $needs_swiper,
            $needs_swiper,
            $needs_modules
        );
        if ( $enqueue_swiper_script ) {
            wp_enqueue_script( 'swiper' );

            // 本地 Swiper JS 兜底：外链配置异常时自动补载本地脚本。
            $local_swiper_js = $this->get_default_swiper_script_url();
            $local_swiper_js = $this->normalize_asset_url( $local_swiper_js );
            $local_swiper_js = apply_filters( 'developer_starter_swiper_js_local_fallback_url', $local_swiper_js, $version );

            if ( ! empty( $local_swiper_js ) ) {
                $fallback_swiper_loader = '(function(){'
                    . 'var fallbackSrc=' . wp_json_encode( $local_swiper_js ) . ';'
                    . 'if(!fallbackSrc){return;}'
                    . 'var hasSwiper=function(){return typeof window.Swiper!=="undefined";};'
                    . 'var hasFallbackScript=function(){'
                        . 'return !!document.querySelector("script[data-ds-swiper-fallback=\'1\']");'
                    . '};'
                    . 'var loadFallback=function(){'
                        . 'if(hasSwiper()||hasFallbackScript()){return;}'
                        . 'var s=document.createElement("script");'
                        . 's.src=fallbackSrc;'
                        . 's.defer=true;'
                        . 's.setAttribute("data-ds-swiper-fallback","1");'
                        . 'document.head.appendChild(s);'
                    . '};'
                    . 'if(hasSwiper()){return;}'
                    . 'if(document.readyState==="loading"){'
                        . 'document.addEventListener("DOMContentLoaded",function(){setTimeout(loadFallback,150);});'
                    . '}else{'
                        . 'setTimeout(loadFallback,150);'
                    . '}'
                    . 'setTimeout(loadFallback,1200);'
                . '})();';
                wp_add_inline_script( 'swiper', $fallback_swiper_loader, 'after' );
            }
        }

        // 主脚本依赖根据实际需要动态调整。
        $main_deps = array();
        if ( $needs_jquery ) {
            $main_deps[] = 'jquery';
        }
        if ( $needs_swiper ) {
            $main_deps[] = 'swiper';
        }
        $main_deps = apply_filters( 'developer_starter_main_script_deps', $main_deps, $needs_swiper );
        $main_script = apply_filters( 'developer_starter_main_script_url', DEVELOPER_STARTER_ASSETS . '/js/main.js', $version );
        // $main_deps = $needs_swiper ? array( 'jquery', 'swiper' ) : array( 'jquery' );
        wp_enqueue_script( 'developer-starter-main', $main_script, 
            $main_deps, $version, true );

        $search_captcha_enabled = (bool) developer_starter_get_option( 'search_captcha_enable', '' );
        $search_captcha_lazy_load = $search_captcha_enabled;
        $search_captcha_wait = absint( developer_starter_get_option( 'search_captcha_wait', 0 ) );
        $captcha_font_size = absint( developer_starter_get_option( 'captcha_font_size', 0 ) );
        $captcha_line_number = absint( developer_starter_get_option( 'captcha_line_number', 0 ) );
        $captcha_provider = (string) developer_starter_get_option( 'captcha_provider', 'theme' );
        if ( ! in_array( $captcha_provider, array( 'theme', 'aliyun' ), true ) ) {
            $captcha_provider = 'theme';
        }
        $captcha_aliyun_prefix = trim( (string) developer_starter_get_option( 'aliyun_captcha_prefix', '' ) );
        $captcha_aliyun_scene_auth = trim( (string) developer_starter_get_option( 'aliyun_captcha_scene_auth', '' ) );
        $captcha_aliyun_scene_search = trim( (string) developer_starter_get_option( 'aliyun_captcha_scene_search', '' ) );
        $captcha_aliyun_client_region = trim( (string) developer_starter_get_option( 'aliyun_captcha_client_region', '' ) );
        if ( ! in_array( $captcha_aliyun_client_region, array( 'cn', 'sgp' ), true ) ) {
            $region_raw = strtolower( trim( (string) developer_starter_get_option( 'aliyun_captcha_region', '' ) ) );
            $endpoint_raw = strtolower( trim( (string) developer_starter_get_option( 'aliyun_captcha_endpoint', '' ) ) );
            if ( false !== strpos( $region_raw, 'sgp' ) || false !== strpos( $endpoint_raw, 'ap-southeast-1' ) ) {
                $captcha_aliyun_client_region = 'sgp';
            } else {
                $captcha_aliyun_client_region = 'cn';
            }
        }

        $jquery_lazy_url = includes_url( 'js/jquery/jquery.min.js' );
        $jquery_lazy_ver = get_bloginfo( 'version' );
        if ( function_exists( 'wp_scripts' ) ) {
            $wp_scripts = wp_scripts();
            if ( $wp_scripts && isset( $wp_scripts->registered['jquery-core'] ) ) {
                $jquery_core = $wp_scripts->registered['jquery-core'];
                if ( ! empty( $jquery_core->src ) ) {
                    $jquery_src = $jquery_core->src;
                    if ( 0 === strpos( $jquery_src, '//' ) ) {
                        $jquery_src = is_ssl() ? 'https:' . $jquery_src : 'http:' . $jquery_src;
                    } elseif ( 0 === strpos( $jquery_src, '/' ) ) {
                        $jquery_src = home_url( $jquery_src );
                    } elseif ( ! preg_match( '#^https?://#i', $jquery_src ) ) {
                        $jquery_src = site_url( $jquery_src );
                    }
                    $jquery_lazy_url = $jquery_src;
                }

                if ( isset( $jquery_core->ver ) && '' !== (string) $jquery_core->ver ) {
                    $jquery_lazy_ver = (string) $jquery_core->ver;
                }
            }
        }

        $language_switch_mode = $this->get_frontend_language_switch_mode();
        $translate_enabled = ( 'translate_js' === $language_switch_mode );
        $translate_js_url = '';
        if ( $translate_enabled ) {
            $default_translate_js_url    = DEVELOPER_STARTER_URI . '/translate/translate.js';
            $configured_translate_js_url = trim( (string) developer_starter_get_option( 'translate_js_url', '' ) );
            $translate_js_url            = '' === $configured_translate_js_url ? $default_translate_js_url : $configured_translate_js_url;
            $translate_js_url            = $this->normalize_allowed_asset_url( $translate_js_url );
            if ( '' === $translate_js_url && '' !== $configured_translate_js_url ) {
                $translate_js_url = $this->normalize_allowed_asset_url( $default_translate_js_url );
            }
            if ( '' !== $translate_js_url ) {
                $translate_js_url = add_query_arg(
                    'ver',
                    rawurlencode( (string) DEVELOPER_STARTER_VERSION ),
                    $translate_js_url
                );
            }
        }

        $frontend_home_url = function_exists( 'developer_starter_get_frontend_home_url' )
            ? developer_starter_get_frontend_home_url()
            : home_url( '/' );
        $raw_home_path = function_exists( 'developer_starter_get_raw_home_path' )
            ? developer_starter_get_raw_home_path()
            : trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
        $lang_cookie_name = function_exists( 'developer_starter_get_multilingual_lang_cookie_name' )
            ? developer_starter_get_multilingual_lang_cookie_name()
            : 'developer_starter_front_lang';
        $lang_cookie_version_name = function_exists( 'developer_starter_get_multilingual_lang_cookie_version_name' )
            ? developer_starter_get_multilingual_lang_cookie_version_name()
            : 'developer_starter_front_lang_ver';
        $lang_cookie_version = function_exists( 'developer_starter_get_multilingual_lang_cookie_version' )
            ? developer_starter_get_multilingual_lang_cookie_version()
            : 'v1';

        $should_enqueue_mobile_menu = has_nav_menu( 'mobile' ) || has_nav_menu( 'primary' ) || has_nav_menu( 'mobile_bottom' );
        $should_enqueue_mobile_menu = (bool) apply_filters( 'developer_starter_enqueue_mobile_menu_script', $should_enqueue_mobile_menu );

        $header_login_enabled      = (bool) developer_starter_get_option( 'header_login_enable', '' );
        $login_modal_enabled       = $header_login_enabled && ! is_user_logged_in();
        $should_enqueue_header_auth = function_exists( 'developer_starter_is_auth_template_page' ) && developer_starter_is_auth_template_page();
        $has_header_search         = ! developer_starter_get_option( 'hide_search_button', '' );
        $needs_search_runtime      = $has_header_search || is_search() || is_404();
        $needs_auth_runtime        = $should_enqueue_header_auth || ( $search_captcha_enabled && $needs_search_runtime );
        $language_switcher_enabled = function_exists( 'developer_starter_get_frontend_language_switcher_enabled' )
            ? developer_starter_get_frontend_language_switcher_enabled()
            : false;
        $dark_mode_config          = function_exists( 'developer_starter_get_dark_mode_runtime_config' )
            ? developer_starter_get_dark_mode_runtime_config()
            : array( 'enabled' => (bool) developer_starter_get_option( 'darkmode_enable', '' ) );
        $dark_mode_payload         = array(
            'darkMode' => function_exists( 'developer_starter_get_dark_mode_runtime_config' )
                ? $dark_mode_config
                : array( 'enabled' => (bool) developer_starter_get_option( 'darkmode_enable', '' ) ),
        );
        $dark_mode_enabled         = ! empty( $dark_mode_payload['darkMode']['enabled'] );
        $current_module_types      = $this->get_current_page_module_types();
        $module_search_matches     = array_intersect( $current_module_types, array( 'hero_search', 'qiling_video_portal_hero' ) );
        $needs_search_runtime      = $needs_search_runtime || ! empty( $module_search_matches );
        $needs_auth_runtime        = $should_enqueue_header_auth || ( $search_captcha_enabled && $needs_search_runtime );
        $needs_contact_form_config = is_page_template( 'templates/template-contact.php' ) || in_array( 'contact', $current_module_types, true );
        $needs_contact_form_config = (bool) apply_filters( 'developer_starter_contact_form_script_data_needed', $needs_contact_form_config, $current_module_types );
        $needs_search_enhance_script = (bool) apply_filters( 'developer_starter_needs_search_enhance_script', $needs_search_runtime, $current_module_types );
        $lazy_embed_module_matches = array_intersect( $current_module_types, array( 'video', 'product_showcase' ) );
        $needs_lazy_embed_script = is_singular()
            || ! empty( $lazy_embed_module_matches );
        $needs_lazy_embed_script = (bool) apply_filters( 'developer_starter_needs_lazy_embed_script', $needs_lazy_embed_script, $current_module_types );
        $needs_faq_script = is_page_template( 'templates/template-faq.php' ) || in_array( 'faq', $current_module_types, true );
        $needs_faq_script = (bool) apply_filters( 'developer_starter_needs_faq_script', $needs_faq_script, $current_module_types );
        $stats_counter_module_matches = array_intersect( $current_module_types, array( 'stats', 'banner' ) );
        $needs_stats_counter_script = is_page_template( 'templates/template-careers.php' )
            || ! empty( $stats_counter_module_matches );
        $needs_stats_counter_script = (bool) apply_filters( 'developer_starter_needs_stats_counter_script', $needs_stats_counter_script, $current_module_types );
        $needs_back_to_top_script = (bool) developer_starter_get_option( 'float_widget_enable', '1' );
        $needs_back_to_top_script = (bool) apply_filters( 'developer_starter_needs_back_to_top_script', $needs_back_to_top_script, $current_module_types );
        $needs_language_switcher_script = $translate_enabled || $language_switcher_enabled;
        $needs_language_switcher_script = (bool) apply_filters( 'developer_starter_needs_language_switcher_script', $needs_language_switcher_script, $current_module_types );

        $script_data = array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'developer_starter_nonce' ),
            'authNonce' => wp_create_nonce( 'developer_starter_auth' ),
            'homeUrl'   => $frontend_home_url,
            'themeUrl'  => DEVELOPER_STARTER_URI,
        );

        if ( $header_login_enabled || $should_enqueue_header_auth ) {
            $script_data['userStatusNonce'] = wp_create_nonce( 'developer_starter_user_status' );
        }

        if ( is_user_logged_in() ) {
            $script_data['ipLookupNonce'] = wp_create_nonce( 'developer_starter_ip_lookup' );
        }

        if ( $login_modal_enabled ) {
            $script_data['loginModal'] = array(
                'enabled' => (bool) $login_modal_enabled,
                'endpoint' => add_query_arg(
                    'action',
                    'developer_starter_get_login_modal',
                    admin_url( 'admin-ajax.php' )
                ),
                'style' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $version ),
                    DEVELOPER_STARTER_ASSETS . '/css/login-modal.css'
                ),
                'authFlowScript' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $version ),
                    DEVELOPER_STARTER_ASSETS . '/js/auth-flow.js'
                ),
                'script' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $version ),
                    DEVELOPER_STARTER_ASSETS . '/js/login-modal.js'
                ),
                'fallbackUrl' => wp_login_url(),
            );
        }

        if ( $needs_auth_runtime ) {
            $auth_runtime_payload = array(
                'authFlow' => array(
                    'captchaChallengeAction' => 'developer_starter_captcha_challenge',
                    'captchaVerifyAction'    => 'developer_starter_captcha_verify',
                    'i18n'                   => array(
                        'captchaInitFailedText' => __( '验证初始化失败，请重试', 'developer-starter' ),
                        'dragText'              => __( '向右滑动完成验证', 'developer-starter' ),
                        'networkErrorShort'     => __( '网络错误', 'developer-starter' ),
                        'networkErrorText'      => __( '网络错误，请稍后再试', 'developer-starter' ),
                        'sendCodeText'          => __( '获取验证码', 'developer-starter' ),
                    ),
                ),
                'captchaProviderScript' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $version ),
                    DEVELOPER_STARTER_ASSETS . '/js/captcha-provider.js'
                ),
                'captcha' => array(
                    'provider' => $captcha_provider,
                    'verifyAction' => 'developer_starter_captcha_verify',
                    'verifyNonce' => wp_create_nonce( 'developer_starter_auth' ),
                    'aliyunScript' => 'https://o.alicdn.com/captcha-frontend/aliyunCaptcha/AliyunCaptcha.js',
                    'aliyunPrefix' => $captcha_aliyun_prefix,
                    'aliyunRegion' => $captcha_aliyun_client_region,
                    'sceneAuth' => $captcha_aliyun_scene_auth,
                    'sceneSearch' => $captcha_aliyun_scene_search,
                    'i18n' => array(
                        'configErrorText' => __( '验证码配置不完整', 'developer-starter' ),
                        'waitingText' => __( '点击完成验证', 'developer-starter' ),
                        'successText' => __( '验证成功', 'developer-starter' ),
                        'failedText' => __( '验证失败，请重试', 'developer-starter' ),
                        'verifyingText' => __( '正在验证...', 'developer-starter' ),
                        'buttonText' => __( '点击验证', 'developer-starter' ),
                        'loadFailedText' => __( '验证码脚本加载失败，请检查网络', 'developer-starter' ),
                    ),
                ),
            );
            $script_data = array_merge( $script_data, $auth_runtime_payload );
        }

        if ( $has_header_search ) {
            $script_data['searchOverlayScript'] = add_query_arg(
                'ver',
                rawurlencode( (string) $version ),
                DEVELOPER_STARTER_ASSETS . '/js/search-overlay.js'
            );
        }

        if ( $search_captcha_enabled && $needs_search_runtime ) {
            $script_data['searchCaptchaLazyLoad'] = $search_captcha_lazy_load;
            $script_data['searchCaptchaAssets'] = array(
                'style' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $version ),
                    DEVELOPER_STARTER_ASSETS . '/css/search-captcha.css'
                ),
                'script' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $version ),
                    DEVELOPER_STARTER_ASSETS . '/js/search-captcha.js'
                ),
                'jquery' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $jquery_lazy_ver ),
                    $jquery_lazy_url
                ),
            );
        }

        if ( $needs_search_runtime ) {
            $search_autocomplete_config = class_exists( '\Developer_Starter\Core\Search_Autocomplete' )
                ? \Developer_Starter\Core\Search_Autocomplete::get_client_config()
                : array(
                    'autocompleteEnabled' => true,
                    'autocompleteAction'  => 'developer_starter_search_autocomplete',
                    'autocompleteNonce'   => wp_create_nonce( 'developer_starter_search_autocomplete' ),
                    'minChars'            => 2,
                    'maxResults'          => 6,
                    'debounce'            => 250,
                );
            $search_enhance_payload = array(
                'searchEnhance' => array(
                    'enabled' => true,
                    'storageKey' => 'qiling-search-history',
                    'maxHistory' => 12,
                    'maxSuggestions' => 6,
                    'currentQuery' => is_search() ? rawurldecode( (string) get_search_query( false ) ) : '',
                    'currentScope' => function_exists( 'developer_starter_get_current_search_scope' ) ? developer_starter_get_current_search_scope() : 'all',
                    'strings' => array(
                        'suggestionsTitle' => __( '搜索建议', 'developer-starter' ),
                        'historyTitle' => __( '搜索历史', 'developer-starter' ),
                        'clearHistory' => __( '清空', 'developer-starter' ),
                        'emptyHistory' => __( '暂无搜索历史', 'developer-starter' ),
                        'loading' => __( '正在搜索...', 'developer-starter' ),
                        'noResults' => __( '没有找到相关内容', 'developer-starter' ),
                        'networkError' => __( '搜索加载失败，请稍后再试', 'developer-starter' ),
                        'viewAll' => __( '查看全部结果', 'developer-starter' ),
                    ),
                ),
            );
            $search_enhance_payload['searchEnhance'] = array_merge( $search_enhance_payload['searchEnhance'], $search_autocomplete_config );
            $script_data = array_merge( $script_data, $search_enhance_payload );
        }

        if ( $needs_contact_form_config ) {
            $script_data['strings'] = array(
                'sending'        => __( '发送中...', 'developer-starter' ),
                'contactSuccess' => __( '感谢您的留言，我们会尽快与您联系！', 'developer-starter' ),
                'contactNameRequired' => __( '请填写姓名', 'developer-starter' ),
                'contactMessageRequired' => __( '请填写留言内容', 'developer-starter' ),
                'contactPhoneOrEmailRequired' => __( '请填写联系电话或邮箱', 'developer-starter' ),
                'contactLoginNow' => __( '立即登录', 'developer-starter' ),
                'error'          => __( '网络错误，请稍后再试', 'developer-starter' ),
                'dateRangeInvalid' => __( '结束日期不能早于开始日期', 'developer-starter' ),
            );
        }

        if ( $search_captcha_enabled && $needs_search_runtime ) {
            $script_data['searchCaptcha'] = array(
                'provider' => $captcha_provider,
                'verifyNonce' => wp_create_nonce( 'developer_starter_auth' ),
                'verifyAction' => 'developer_starter_captcha_verify',
                'aliyunScript' => 'https://o.alicdn.com/captcha-frontend/aliyunCaptcha/AliyunCaptcha.js',
                'aliyunPrefix' => $captcha_aliyun_prefix,
                'aliyunRegion' => $captcha_aliyun_client_region,
                'sceneSearch' => $captcha_aliyun_scene_search,
                'closeLabel' => __( '关闭', 'developer-starter' ),
                'title' => __( '安全验证', 'developer-starter' ),
                'dragText' => __( '按住滑块 拖动到最右侧', 'developer-starter' ),
                'successText' => __( '验证通过', 'developer-starter' ),
                'aliyunWaitingText' => __( '点击完成验证', 'developer-starter' ),
                'aliyunButtonText' => __( '点击验证', 'developer-starter' ),
                'aliyunVerifyingText' => __( '正在验证...', 'developer-starter' ),
                'aliyunFailedText' => __( '验证失败，请重试', 'developer-starter' ),
                'aliyunConfigErrorText' => __( '验证码配置不完整，请联系管理员', 'developer-starter' ),
                'waitSeconds' => $search_captcha_wait,
                'waitText' => __( '请稍候 %d 秒', 'developer-starter' ),
            );
        }

        if ( $translate_enabled || $language_switcher_enabled ) {
            $script_data['translate'] = array(
                'enabled' => $translate_enabled,
                'mode' => $language_switch_mode,
                'scriptUrl' => $translate_js_url,
                'local' => $this->get_translate_local_language(),
            );

            $script_data['languageSwitcher'] = array(
                'enabled' => $language_switcher_enabled,
                'mode' => $language_switch_mode,
                'currentLang' => function_exists( 'developer_starter_get_current_frontend_lang' ) ? developer_starter_get_current_frontend_lang() : '',
                'defaultLang' => function_exists( 'developer_starter_get_multilingual_default_lang' ) ? developer_starter_get_multilingual_default_lang() : '',
                'languages' => function_exists( 'developer_starter_get_multilingual_languages' ) ? wp_list_pluck( developer_starter_get_multilingual_languages(), 'code' ) : array(),
                'homeUrl' => $frontend_home_url,
                'homePath' => $raw_home_path,
                'cookieName' => $lang_cookie_name,
                'cookieVersionName' => $lang_cookie_version_name,
                'cookieVersion' => $lang_cookie_version,
            );
        }

        if ( $dark_mode_enabled ) {
            $script_data['darkMode'] = $dark_mode_payload['darkMode'];
        }

        if ( $should_enqueue_mobile_menu ) {
            $script_data['mobileMenu'] = array(
                'enabled' => $should_enqueue_mobile_menu,
                'script' => add_query_arg(
                    'ver',
                    rawurlencode( (string) $version ),
                    DEVELOPER_STARTER_ASSETS . '/js/mobile-menu.js'
                ),
                'breakpoint' => 992,
            );
        }

        $script_data = apply_filters( 'developer_starter_main_script_data', $script_data );
        $core_script_keys = array_fill_keys(
            array(
                'ajaxUrl',
                'nonce',
                'authNonce',
                'userStatusNonce',
                'ipLookupNonce',
                'homeUrl',
                'themeUrl',
            ),
            true
        );
        $core_script_data = array_intersect_key( $script_data, $core_script_keys );
        $runtime_script_data = array_diff_key( $script_data, $core_script_keys );
        $runtime_script_data = apply_filters( 'developer_starter_main_script_runtime_data', $runtime_script_data, $script_data );

        wp_localize_script( 'developer-starter-main', 'developerStarterData', $core_script_data );
        $this->add_main_script_data_fragment( $runtime_script_data );

        if ( $needs_search_enhance_script ) {
            $this->enqueue_main_feature_script( 'search-enhance', 'feature-search-enhance.js', $version );
        }

        if ( $needs_lazy_embed_script ) {
            $this->enqueue_main_feature_script( 'lazy-embeds', 'feature-lazy-embeds.js', $version );
        }

        if ( $needs_faq_script ) {
            $this->enqueue_main_feature_script( 'faq', 'feature-faq.js', $version );
        }

        if ( $needs_stats_counter_script ) {
            $this->enqueue_main_feature_script( 'stats-counter', 'feature-stats-counter.js', $version );
        }

        if ( $needs_back_to_top_script ) {
            $this->enqueue_main_feature_script( 'back-to-top', 'feature-back-to-top.js', $version );
        }

        if ( $needs_contact_form_config ) {
            $this->enqueue_main_feature_script( 'contact-form', 'feature-contact-form.js', $version );
        }

        if ( $needs_language_switcher_script ) {
            $this->enqueue_main_feature_script( 'language-switcher', 'feature-language-switcher.js', $version );
        }

        // 认证页需要始终同步登录态并在已登录时执行跳转。

        if ( $should_enqueue_header_auth ) {
            wp_enqueue_script(
                'developer-starter-auth-flow',
                DEVELOPER_STARTER_ASSETS . '/js/auth-flow.js',
                array( 'developer-starter-main' ),
                $version,
                true
            );

            wp_enqueue_script(
                'developer-starter-auth-pages',
                DEVELOPER_STARTER_ASSETS . '/js/auth-pages.js',
                array( 'developer-starter-auth-flow' ),
                $version,
                true
            );

            wp_enqueue_script(
                'developer-starter-header-auth',
                DEVELOPER_STARTER_ASSETS . '/js/header-auth.js',
                array( 'developer-starter-auth-pages' ),
                $version,
                true
            );
        }

        // 移动端菜单脚本改为前端按视口懒加载，避免桌面端无效下载。

        if ( $this->needs_left_nav() ) {
            wp_enqueue_script(
                'developer-starter-left-nav',
                DEVELOPER_STARTER_ASSETS . '/js/left-nav.js',
                array(),
                $version,
                true
            );
        }

        if ( is_404() ) {
            $error_404_js_file = DEVELOPER_STARTER_DIR . '/assets/js/error-404.js';
            wp_enqueue_script(
                'developer-starter-error-404',
                DEVELOPER_STARTER_ASSETS . '/js/error-404.js',
                array(),
                file_exists( $error_404_js_file ) ? (string) filemtime( $error_404_js_file ) : $version,
                true
            );
        }

        // 文章相关增强逻辑（视频封面悬停播放等）
        $needs_post_enhance = is_singular( 'post' )
            || is_home()
            || is_archive()
            || is_search()
            || is_page_template( 'templates/template-blog.php' )
            || is_page_template( 'templates/template-topic.php' )
            || is_page_template( 'templates/template-latest-posts.php' );
        $needs_post_enhance = (bool) apply_filters( 'developer_starter_needs_post_enhance_script', $needs_post_enhance );
        if ( $needs_post_enhance ) {
            wp_enqueue_script(
                'developer-starter-post-enhance',
                DEVELOPER_STARTER_ASSETS . '/js/post-enhance.js',
                array(),
                $version,
                true
            );
        }

        if ( function_exists( 'developer_starter_lazy_load_placeholder_enabled' ) && developer_starter_lazy_load_placeholder_enabled() ) {
            wp_enqueue_script(
                'developer-starter-lazy-image-placeholder',
                DEVELOPER_STARTER_ASSETS . '/js/lazy-image-placeholder.js',
                array( 'developer-starter-main' ),
                $version,
                true
            );

            wp_localize_script( 'developer-starter-lazy-image-placeholder', 'qilingLazyImagePlaceholderConfig', array(
                'selector' => 'img[loading="lazy"], img.qiling-progressive-image',
            ) );
        }

        $custom_js = developer_starter_get_option( 'custom_js', '' );
        $custom_js = apply_filters( 'developer_starter_custom_js', $custom_js, $version );
        if ( ! empty( $custom_js ) ) {
            $this->enqueue_custom_js_option( $custom_js );
        }

        // 页脚动画特效脚本
        $effect_enabled = developer_starter_get_option( 'footer_effect_enable', '' );
        if ( $effect_enabled ) {
            wp_enqueue_script( 'developer-starter-footer-effects', DEVELOPER_STARTER_ASSETS . '/js/footer-effects.js', 
                array(), DEVELOPER_STARTER_VERSION, true );
        }

        if ( is_singular() && ( comments_open() || get_comments_number() ) ) {
            $comments_js_file = DEVELOPER_STARTER_DIR . '/assets/js/comments.js';
            wp_enqueue_script(
                'developer-starter-comments',
                DEVELOPER_STARTER_ASSETS . '/js/comments.js',
                array(),
                file_exists( $comments_js_file ) ? (string) filemtime( $comments_js_file ) : $version,
                true
            );
        }

        if ( is_singular() && comments_open() ) {
            wp_enqueue_script( 'comment-reply' );
        }

        do_action( 'developer_starter_after_enqueue_scripts', $version, $needs_swiper );
    }

    public function admin_assets( $hook ) {
        $is_theme_settings_page = strpos( (string) $hook, 'developer-starter' ) !== false;

        if ( ! $is_theme_settings_page ) {
            return;
        }

        $version = $this->get_version();
        wp_enqueue_style( 'developer-starter-admin', DEVELOPER_STARTER_ASSETS . '/css/admin.css',
            array(), $version );

        // admin.css 只服务启灵主题后台页，不注入文章/页面编辑器。
    }

    /**
     * 检测页面是否使用了功能模块
     */
    private function needs_modules() {
        // 后台始终不加载前端模块样式
        if ( is_admin() ) {
            return false;
        }

        if ( null !== $this->needs_modules_cache ) {
            return (bool) $this->needs_modules_cache;
        }

        $modules = $this->get_current_page_modules();
        $needs_modules = ! empty( $modules ) && is_array( $modules );
        $post = $this->get_current_page_post();
        $this->needs_modules_cache = (bool) apply_filters( 'developer_starter_needs_modules', $needs_modules, $post );

        return (bool) $this->needs_modules_cache;
    }

    /**
     * 检测当前请求是否属于原生博客/文章场景。
     *
     * @return bool
     */
    private function is_native_blog_context() {
        return is_singular( 'post' )
            || is_home()
            || is_category()
            || is_tag()
            || is_author()
            || is_date()
            || is_page_template( 'templates/template-blog.php' )
            || is_page_template( 'templates/template-topic.php' )
            || is_page_template( 'templates/template-latest-posts.php' );
    }

    /**
     * 获取可触发博客视觉预设样式的模块类型。
     *
     * @return array<int,string>
     */
    private function get_blog_preset_module_types() {
        $blog_module_types = apply_filters(
            'developer_starter_blog_preset_module_types',
            array( 'blog' )
        );
        if ( ! is_array( $blog_module_types ) ) {
            $blog_module_types = array( 'blog' );
        }

        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ( $module_type ) {
                            return sanitize_key( (string) $module_type );
                        },
                        $blog_module_types
                    ),
                    'strlen'
                )
            )
        );
    }

    /**
     * 检测当前请求是否需要博客视觉预设样式。
     *
     * 拆分后的博客预设样式只服务原生博客/文章场景和博客模块，避免普通装修页面加载整包博客视觉预设。
     *
     * @return bool
     */
    private function needs_blog_presets() {
        if ( null !== $this->needs_blog_presets_cache ) {
            return (bool) $this->needs_blog_presets_cache;
        }

        $native_blog_context = $this->is_native_blog_context();
        $module_types = array();
        $needs_blog_presets = (bool) $native_blog_context;

        if ( ! $needs_blog_presets ) {
            $module_types = $this->get_current_page_module_types();
            $needs_blog_presets = ! empty( array_intersect( $module_types, $this->get_blog_preset_module_types() ) );
        }

        $this->needs_blog_presets_cache = (bool) apply_filters(
            'developer_starter_needs_blog_presets',
            $needs_blog_presets,
            $module_types,
            $native_blog_context
        );

        return (bool) $this->needs_blog_presets_cache;
    }

    /**
     * 加载当前请求实际需要的博客视觉预设样式。
     *
     * @param string $version 样式版本号。
     * @return void
     */
    private function enqueue_blog_preset_styles( $version ) {
        $style_files = array(
            'developer' => '/css/blog-presets.css',
            'minimal'   => '/css/blog-presets-minimal.css',
            'artist'    => '/css/blog-presets-artist.css',
        );

        $loaded_handles = array();
        foreach ( $this->get_blog_presets_to_enqueue() as $preset ) {
            if ( empty( $style_files[ $preset ] ) ) {
                continue;
            }

            $handle = Blog_Visual_Manager::get_preset_style_handle( $preset );
            if ( '' === $handle ) {
                continue;
            }

            wp_enqueue_style(
                $handle,
                DEVELOPER_STARTER_ASSETS . $style_files[ $preset ],
                array( 'developer-starter-main' ),
                $version
            );
            $loaded_handles[] = $handle;
        }

        $loaded_handles = array_values( array_unique( $loaded_handles ) );
        if ( empty( $loaded_handles ) ) {
            return;
        }

        wp_register_style(
            Blog_Visual_Manager::get_preset_custom_css_handle(),
            false,
            $loaded_handles,
            $version
        );
        wp_enqueue_style( Blog_Visual_Manager::get_preset_custom_css_handle() );
    }

    /**
     * 获取当前请求需要加载的博客视觉预设。
     *
     * @return array<int,string>
     */
    private function get_blog_presets_to_enqueue() {
        if ( null !== $this->blog_presets_to_enqueue_cache ) {
            return $this->blog_presets_to_enqueue_cache;
        }

        $native_blog_context = $this->is_native_blog_context();
        $presets = array();

        if ( $native_blog_context ) {
            $presets[] = Blog_Visual_Manager::get_current_preset();
        }

        $blog_module_types = $this->get_blog_preset_module_types();
        foreach ( $this->get_current_page_modules() as $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                continue;
            }

            $module_type = sanitize_key( (string) $module['type'] );
            if ( ! in_array( $module_type, $blog_module_types, true ) ) {
                continue;
            }

            $presets[] = Blog_Visual_Manager::resolve_module_preset( $this->get_blog_module_visual_preset( $module ) );
        }

        $presets = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ( $preset ) {
                            $preset = Blog_Visual_Manager::sanitize_preset( $preset );
                            return in_array( $preset, array( 'developer', 'minimal', 'artist' ), true ) ? $preset : '';
                        },
                        $presets
                    ),
                    'strlen'
                )
            )
        );

        $module_types = $this->get_current_page_module_types();
        $this->blog_presets_to_enqueue_cache = (array) apply_filters(
            'developer_starter_blog_presets_to_enqueue',
            $presets,
            $native_blog_context,
            $module_types
        );

        $this->blog_presets_to_enqueue_cache = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ( $preset ) {
                            $preset = Blog_Visual_Manager::sanitize_preset( $preset );
                            return in_array( $preset, array( 'developer', 'minimal', 'artist' ), true ) ? $preset : '';
                        },
                        $this->blog_presets_to_enqueue_cache
                    ),
                    'strlen'
                )
            )
        );

        return $this->blog_presets_to_enqueue_cache;
    }

    /**
     * 从博客模块配置中读取视觉预设。
     *
     * 模块保存结构通常是 array( 'type' => 'blog', 'data' => array( ... ) )，
     * 但历史数据/过滤器也可能把字段平铺到模块顶层，这里同时兼容。
     *
     * @param array<string,mixed> $module 模块配置。
     * @return string
     */
    private function get_blog_module_visual_preset( $module ) {
        if ( isset( $module['data'] ) && is_array( $module['data'] ) && array_key_exists( 'blog_visual_preset', $module['data'] ) ) {
            return (string) $module['data']['blog_visual_preset'];
        }

        if ( array_key_exists( 'blog_visual_preset', $module ) ) {
            return (string) $module['blog_visual_preset'];
        }

        return 'inherit';
    }

    /**
     * 获取模块 CSS 加载模式
     *
     * @return string
     */
    private function get_modules_css_load_mode() {
        $mode = sanitize_key( (string) developer_starter_get_option( 'module_css_load_mode', 'single' ) );
        return in_array( $mode, array( 'single', 'split' ), true ) ? $mode : 'single';
    }

    /**
     * 获取首屏模块类型白名单（统一由 modules-hero.css 覆盖）
     *
     * @return array<int,string>
     */
    private function get_hero_module_types() {
        return class_exists( '\Developer_Starter\Modules\Module_Manager' )
            ? \Developer_Starter\Modules\Module_Manager::get_hero_module_ids( 'assets' )
            : array();
    }

    /**
     * 获取当前页面模块类型列表
     *
     * @return array<int,string>
     */
    private function get_current_page_module_types() {
        if ( null !== $this->module_types_cache ) {
            return $this->module_types_cache;
        }

        $module_types = array();
        $modules = $this->get_current_page_modules();
        if ( is_array( $modules ) ) {
            foreach ( $modules as $module ) {
                if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                    continue;
                }
                $module_type = sanitize_key( (string) $module['type'] );
                if ( '' !== $module_type ) {
                    $module_types[] = $module_type;
                }
            }
        }

        $this->module_types_cache = array_values( array_unique( $module_types ) );
        return $this->module_types_cache;
    }

    /**
     * 功能模块样式加载入口
     *
     * @param string $version 版本号
     * @param bool   $use_min 是否优先使用 min 文件
     * @return void
     */
    private function enqueue_modules_styles( $version, $use_min ) {
        if ( 'split' !== $this->get_modules_css_load_mode() ) {
            $this->enqueue_modules_single_style( $version, $use_min );
            return;
        }

        if ( ! $this->enqueue_modules_split_styles( $version ) ) {
            $this->enqueue_modules_single_style( $version, $use_min );
        }
    }

    /**
     * 加载整包 modules.css（兼容模式）
     *
     * @param string $version 版本号
     * @param bool   $use_min 是否优先使用 min 文件
     * @return void
     */
    private function enqueue_modules_single_style( $version, $use_min ) {
        $modules_css = DEVELOPER_STARTER_ASSETS . '/css/modules.css';
        if ( $use_min && file_exists( DEVELOPER_STARTER_DIR . '/assets/css/modules.min.css' ) ) {
            $modules_css = DEVELOPER_STARTER_ASSETS . '/css/modules.min.css';
        }
        $modules_css = apply_filters( 'developer_starter_modules_css_url', $modules_css, $version, $use_min );
        wp_enqueue_style( 'developer-starter-modules', $modules_css, array(), $version );
    }

    /**
     * 加载首屏统一样式 modules-hero.css
     *
     * @param string $version 版本号
     * @param bool   $use_min 是否优先使用 min 文件
     * @return void
     */
    private function enqueue_modules_hero_style( $version, $use_min ) {
        $hero_file = DEVELOPER_STARTER_DIR . '/assets/css/modules-hero.css';
        $hero_css  = DEVELOPER_STARTER_ASSETS . '/css/modules-hero.css';

        if ( $use_min && file_exists( DEVELOPER_STARTER_DIR . '/assets/css/modules-hero.min.css' ) ) {
            $hero_file = DEVELOPER_STARTER_DIR . '/assets/css/modules-hero.min.css';
            $hero_css  = DEVELOPER_STARTER_ASSETS . '/css/modules-hero.min.css';
        }

        if ( ! file_exists( $hero_file ) ) {
            return;
        }

        $hero_css = apply_filters( 'developer_starter_modules_hero_css_url', $hero_css, $version, $use_min );
        wp_enqueue_style( 'developer-starter-modules-hero', $hero_css, array(), $version );
    }

    /**
     * 仅加载当前页面各模块自己的独立 CSS。
     *
     * @param string $version 版本号
     * @return bool 是否加载成功
     */
    private function enqueue_modules_split_styles( $version, $preloaded_types = array() ) {
        $modules = $this->get_current_page_modules();
        if ( empty( $modules ) || ! is_array( $modules ) ) {
            return false;
        }

        $split_dir = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/modules-split/';
        $split_uri = trailingslashit( DEVELOPER_STARTER_ASSETS ) . 'css/modules-split/';
        if ( ! is_dir( $split_dir ) ) {
            return false;
        }

        $module_types = $this->get_current_page_module_types();
        if ( empty( $module_types ) ) {
            return false;
        }

        unset( $preloaded_types );

        $module_files = array();
        foreach ( $module_types as $module_type ) {
            $candidate_keys = array(
                $module_type,
                str_replace( '-', '_', $module_type ),
                str_replace( '_', '-', $module_type ),
            );
            $candidate_keys = array_values( array_unique( array_filter( $candidate_keys, 'strlen' ) ) );

            $matched_file = '';
            foreach ( $candidate_keys as $candidate_key ) {
                $candidate_file = $split_dir . $candidate_key . '.css';
                if ( file_exists( $candidate_file ) ) {
                    $matched_file = $candidate_file;
                    break;
                }
            }

            if ( '' === $matched_file ) {
                continue;
            }

            if ( file_exists( $matched_file ) && 0 === (int) filesize( $matched_file ) ) {
                continue;
            }

            $module_files[ $module_type ] = $matched_file;
        }

        if ( count( $module_files ) !== count( $module_types ) ) {
            return false;
        }

        foreach ( $module_files as $module_type => $file_path ) {
            $file_name = basename( $file_path );
            $file_ver = (string) filemtime( $file_path );
            $handle = 'developer-starter-module-' . sanitize_key( $module_type );

            wp_enqueue_style(
                $handle,
                $split_uri . $file_name,
                array(),
                $file_ver ? $file_ver : $version
            );
        }

        return true;
    }

    /**
     * 注册 Swiper 样式句柄（不自动加载）
     *
     * @param string $version 资源版本
     * @return void
     */
    private function register_swiper_style( $version ) {
        if ( wp_style_is( 'swiper', 'registered' ) ) {
            return;
        }

        $swiper_asset = $this->get_third_party_asset( 'swiper_css', $version );
        wp_register_style(
            'swiper',
            $swiper_asset['url'],
            array(),
            $swiper_asset['version'] ? $swiper_asset['version'] : '12.0.3'
        );
    }

    /**
     * 注册 Swiper 脚本句柄（不自动加载）
     *
     * @param string $version 资源版本
     * @return void
     */
    private function register_swiper_script( $version ) {
        if ( wp_script_is( 'swiper', 'registered' ) ) {
            return;
        }

        $swiper_asset = $this->get_third_party_asset( 'swiper_js', $version );
        wp_register_script(
            'swiper',
            $swiper_asset['url'],
            array(),
            $swiper_asset['version'] ? $swiper_asset['version'] : '12.0.3',
            true
        );
    }

    /**
     * 获取当前请求中的页面对象
     *
     * @return \WP_Post|null
     */
    private function get_current_page_post() {
        if ( null !== $this->current_post_cache ) {
            return $this->current_post_cache;
        }

        $resolved_post = null;
        global $post;
        if ( $post && is_a( $post, 'WP_Post' ) ) {
            $resolved_post = $post;
        } elseif ( is_singular() ) {
            $queried = get_queried_object();
            if ( $queried && is_a( $queried, 'WP_Post' ) ) {
                $resolved_post = $queried;
            }
        }

        $this->current_post_cache = $resolved_post;
        return $this->current_post_cache;
    }

    /**
     * 获取当前页面配置模块
     *
     * @return array
     */
    private function get_current_page_modules() {
        if ( null !== $this->modules_cache ) {
            return $this->modules_cache;
        }

        $modules = array();
        $post = $this->get_current_page_post();
        if ( $post && is_a( $post, 'WP_Post' ) ) {
            if ( function_exists( 'developer_starter_get_page_modules_data' ) ) {
                // 与模板渲染保持同一数据源，避免“页面已渲染模块但资源判定未命中”。
                $resolved = developer_starter_get_page_modules_data( $post->ID );
                if ( is_array( $resolved ) ) {
                    $modules = $resolved;
                }
            }

            if ( empty( $modules ) ) {
                $stored = function_exists( 'developer_starter_get_raw_page_modules_meta' )
                    ? developer_starter_get_raw_page_modules_meta( $post->ID )
                    : get_post_meta( $post->ID, '_developer_starter_modules', true );
                if ( is_array( $stored ) ) {
                    $modules = $stored;
                }
            }
        }

        $footer_modules = $this->get_footer_builder_modules_for_assets();
        if ( ! empty( $footer_modules ) ) {
            $modules = array_merge( is_array( $modules ) ? $modules : array(), $footer_modules );
        }

        $page_region_modules = $this->get_page_region_decoration_modules_for_assets();
        if ( ! empty( $page_region_modules ) ) {
            $modules = array_merge( is_array( $modules ) ? $modules : array(), $page_region_modules );
        }

        $this->modules_cache = $modules;
        return $this->modules_cache;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function get_footer_builder_modules_for_assets() {
        if ( is_admin() || '1' !== (string) developer_starter_get_option( 'footer_builder_enable', '' ) ) {
            return array();
        }

        $footer_page_ids = array_unique( array_filter( array_map( 'absint', array(
            developer_starter_get_option( 'footer_builder_page_id', '' ),
            developer_starter_get_option( 'footer_builder_main_page_id', '' ),
            developer_starter_get_option( 'footer_builder_friend_page_id', '' ),
            developer_starter_get_option( 'footer_builder_bottom_page_id', '' ),
        ) ) ) );
        $queried_id = function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
        $modules = array();

        foreach ( $footer_page_ids as $footer_page_id ) {
            if ( $footer_page_id === $queried_id || 'page' !== get_post_type( $footer_page_id ) ) {
                continue;
            }
            $footer_page = get_post( $footer_page_id );
            if ( ! $footer_page instanceof \WP_Post || 'publish' !== $footer_page->post_status ) {
                continue;
            }
            $page_modules = function_exists( 'developer_starter_get_page_modules_data' )
                ? developer_starter_get_page_modules_data( $footer_page_id )
                : get_post_meta( $footer_page_id, '_developer_starter_modules', true );
            if ( is_array( $page_modules ) ) {
                $modules = array_merge( $modules, $page_modules );
            }
        }

        return $modules;
    }

    /**
     * 收集当前页面顶部和底部独立装修区域使用的模块，确保对应 CSS 与 JS 按需加载。
     *
     * @return array<int,array<string,mixed>>
     */
    private function get_page_region_decoration_modules_for_assets() {
        if ( is_admin() || ! function_exists( 'developer_starter_get_current_page_region_decoration_source_ids' ) ) {
            return array();
        }

        $modules = array();
        foreach ( developer_starter_get_current_page_region_decoration_source_ids() as $source_id ) {
            $source_modules = function_exists( 'developer_starter_get_page_modules_data' )
                ? developer_starter_get_page_modules_data( $source_id )
                : get_post_meta( $source_id, '_developer_starter_modules', true );
            if ( is_array( $source_modules ) ) {
                $modules = array_merge( $modules, $source_modules );
            }
        }

        return $modules;
    }

    /**
     * 判断单个模块是否需要 Swiper
     *
     * @param mixed $module 模块配置
     * @return bool
     */
    private function module_needs_swiper( $module ) {
        if ( ! is_array( $module ) || empty( $module['type'] ) ) {
            return false;
        }

        $type = sanitize_key( (string) $module['type'] );
        $data = ( isset( $module['data'] ) && is_array( $module['data'] ) ) ? $module['data'] : array();
        $needs_swiper = false;

        switch ( $type ) {
            case 'banner':
                $layout = isset( $data['banner_layout'] ) ? (string) $data['banner_layout'] : 'slider';
                $slides = ( isset( $data['banner_slides'] ) && is_array( $data['banner_slides'] ) ) ? $data['banner_slides'] : array();
                $needs_swiper = ( $layout !== 'image_text' && count( $slides ) > 1 );
                break;

            case 'products':
                $items = ( isset( $data['items'] ) && is_array( $data['items'] ) ) ? $data['items'] : array();
                $columns = isset( $data['columns'] ) ? max( 1, (int) $data['columns'] ) : 4;
                $needs_swiper = count( $items ) > $columns;
                break;

            case 'hero_search':
                $bg_items = ( isset( $data['hs_bg_items'] ) && is_array( $data['hs_bg_items'] ) ) ? $data['hs_bg_items'] : array();
                $needs_swiper = count( $bg_items ) > 1;
                break;

            case 'double_column_carousel':
                $slides = ( isset( $data['dcc_slides'] ) && is_array( $data['dcc_slides'] ) ) ? $data['dcc_slides'] : array();
                $needs_swiper = count( $slides ) > 1;
                break;

            case 'product_showcase':
                $media_items = ( isset( $data['ps_media_items'] ) && is_array( $data['ps_media_items'] ) ) ? $data['ps_media_items'] : array();
                $needs_swiper = count( $media_items ) > 1;
                break;

            case 'qiling_shop_showcase':
            case 'tabbed_carousel':
                $needs_swiper = true;
                break;
        }

        // 保留旧筛选器兼容性：仅用于“非内置模块类型”的扩展判断，避免覆盖上面的精细条件。
        $core_swiper_types = array(
            'banner',
            'products',
            'hero_search',
            'double_column_carousel',
            'product_showcase',
            'qiling_shop_showcase',
            'tabbed_carousel',
        );
        if ( ! $needs_swiper && ! in_array( $type, $core_swiper_types, true ) ) {
            $post = $this->get_current_page_post();
            $module_types = array();
            $module_types = apply_filters( 'developer_starter_swiper_module_types', $module_types, $post );
            $module_types = is_array( $module_types ) ? array_map( 'sanitize_key', $module_types ) : array();
            if ( in_array( $type, $module_types, true ) ) {
                $needs_swiper = true;
            }
        }

        return (bool) apply_filters( 'developer_starter_module_needs_swiper', $needs_swiper, $type, $data, $module );
    }

    /**
     * 判断单个模块是否依赖 jQuery
     *
     * @param mixed $module 模块配置
     * @return bool
     */
    private function module_needs_jquery( $module ) {
        if ( ! is_array( $module ) || empty( $module['type'] ) ) {
            return false;
        }

        $type = sanitize_key( (string) $module['type'] );
        $module_types = array();
        $post = $this->get_current_page_post();
        $module_types = apply_filters( 'developer_starter_jquery_module_types', $module_types, $post );
        $module_types = is_array( $module_types ) ? array_map( 'sanitize_key', $module_types ) : array();

        $needs_jquery = in_array( $type, $module_types, true );

        return (bool) apply_filters( 'developer_starter_module_needs_jquery', $needs_jquery, $type, $module );
    }

    /**
     * WooCommerce 商店首页 Banner 是否需要 Swiper
     *
     * @return bool
     */
    private function has_wc_shop_banner_swiper() {
        if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'is_shop' ) || ! is_shop() ) {
            return false;
        }

        $options = function_exists( 'developer_starter_get_wc_options' )
            ? developer_starter_get_wc_options()
            : array();
        if ( ! is_array( $options ) ) {
            $options = array();
        }
        $slide_count = 0;

        for ( $i = 1; $i <= 3; $i++ ) {
            $url_key = 'wc_shop_banner_url_' . $i;
            $url = isset( $options[ $url_key ] ) ? trim( (string) $options[ $url_key ] ) : '';
            if ( '' !== $url ) {
                $slide_count++;
            }
        }

        return $slide_count > 1;
    }

    /**
     * 检测 WooCommerce 场景是否需要 Swiper
     *
     * @return bool
     */
    private function needs_swiper_for_woocommerce() {
        $needs_swiper = $this->has_wc_shop_banner_swiper();
        return (bool) apply_filters( 'developer_starter_wc_needs_swiper', $needs_swiper );
    }

    /**
     * 检测模块场景是否需要 Swiper
     *
     * @param array $modules 模块配置
     * @return bool
     */
    private function needs_swiper_for_modules( $modules ) {
        if ( empty( $modules ) || ! is_array( $modules ) ) {
            return false;
        }

        foreach ( $modules as $module ) {
            if ( $this->module_needs_swiper( $module ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检测页面是否需要 Swiper
     * 按模块配置精细判断（例如单张 Banner 不加载）。
     */
    private function needs_swiper() {
        if ( null !== $this->needs_swiper_cache ) {
            return (bool) $this->needs_swiper_cache;
        }

        $needs_swiper = false;
        $post = $this->get_current_page_post();
        $modules = $this->get_current_page_modules();

        if ( $this->needs_modules() ) {
            $needs_swiper = $this->needs_swiper_for_modules( $modules );
        }

        if ( ! $needs_swiper ) {
            $needs_swiper = $this->needs_swiper_for_woocommerce();
        }

        $this->needs_swiper_cache = (bool) apply_filters( 'developer_starter_needs_swiper', $needs_swiper, $modules, $post );
        return (bool) $this->needs_swiper_cache;
    }

    /**
     * 检测页面是否需要 jQuery（模块脚本依赖）
     *
     * @return bool
     */
    private function needs_jquery() {
        if ( null !== $this->needs_jquery_cache ) {
            return (bool) $this->needs_jquery_cache;
        }

        $needs_jquery = false;
        $post = $this->get_current_page_post();
        $modules = $this->get_current_page_modules();

        if ( $this->needs_modules() && is_array( $modules ) ) {
            foreach ( $modules as $module ) {
                if ( $this->module_needs_jquery( $module ) ) {
                    $needs_jquery = true;
                    break;
                }
            }
        }

        if ( ! $needs_jquery && $this->needs_jquery_for_configured_code() ) {
            $needs_jquery = true;
        }

        if ( ! $needs_jquery && $this->needs_jquery_for_login_compatibility() ) {
            $needs_jquery = true;
        }

        $this->needs_jquery_cache = (bool) apply_filters( 'developer_starter_needs_jquery', $needs_jquery, $modules, $post );
        return (bool) $this->needs_jquery_cache;
    }
    
    /**
     * 检测页面是否需要侧边栏样式
     * 优化策略：
     * 1. 与模板实际侧边栏渲染条件保持一致
     * 2. 提供 filter 钩子供第三方插件控制
     */
    private function needs_sidebar() {
        $should_load = false;

        $options = get_option( 'developer_starter_options', array() );

        // 1) 文章详情页（single.php）
        if ( is_singular( 'post' ) ) {
            $post_id = (int) get_queried_object_id();
            $full_width_mode = $post_id > 0 && get_post_meta( $post_id, '_qiling_full_width_mode', true ) === '1';
            $hide_sidebar = ( ! empty( $options['hide_post_sidebar'] ) && $options['hide_post_sidebar'] === '1' ) || $full_width_mode;

            $has_sidebar = ! $hide_sidebar && is_active_sidebar( 'sidebar-post' );
            $should_load = (bool) apply_filters( 'qiling_show_sidebar', $has_sidebar, 'single' );
        }
        // 2) 默认页面模板（page.php）
        elseif ( is_page() && ! get_page_template_slug() ) {
            $modules = $this->get_current_page_modules();
            $has_modules = ! empty( $modules ) && is_array( $modules );

            // page.php 中：有模块配置时页面全宽，不渲染页面侧边栏。
            $has_sidebar = ! $has_modules && is_active_sidebar( 'sidebar-page' );
            $should_load = (bool) apply_filters( 'qiling_show_sidebar', $has_sidebar, 'page' );
        }
        // 3) 主索引列表（index.php）
        elseif ( is_home() ) {
            $has_sidebar = is_active_sidebar( 'sidebar-main' );
            $should_load = (bool) apply_filters( 'qiling_show_sidebar', $has_sidebar, 'index' );
        }

        // 4) 允许第三方最终覆盖 CSS 加载结果
        return (bool) apply_filters( 'developer_starter_load_sidebar_css', $should_load );
    }

    /**
     * 检测页面是否需要左侧导航资源
     *
     * @return bool
     */
    private function needs_left_nav() {
        if ( null !== $this->needs_left_nav_cache ) {
            return (bool) $this->needs_left_nav_cache;
        }

        $should_load = false;
        if ( function_exists( 'developer_starter_should_render_left_nav' ) ) {
            $should_load = (bool) developer_starter_should_render_left_nav();
        }

        $this->needs_left_nav_cache = (bool) apply_filters( 'developer_starter_load_left_nav_assets', $should_load );
        return (bool) $this->needs_left_nav_cache;
    }

    /**
     * 优化 WooCommerce 资源加载
     * 仅在 WooCommerce 相关页面加载特定脚本
     */
    public function optimize_woocommerce_scripts() {
        // 如果 WooCommerce 未激活，无需处理
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        // 定义 WooCommerce 标准页面
        // is_woocommerce() 包含商店、分类、标签、单个产品页面
        $is_woo_page = is_woocommerce() || 
                       is_cart() || 
                       is_checkout() || 
                       is_account_page() || 
                       is_add_payment_method_page() || 
                       ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url() );

        // 检查非标准页面的短代码或内容
        if ( ! $is_woo_page && is_singular() ) {
            global $post;
            if ( $post && is_a( $post, 'WP_Post' ) && strpos( $post->post_content, 'woocommerce' ) !== false ) {
                $is_woo_page = true;
            }
        }

        // 如果不是 WooCommerce 页面，移除跟踪脚本
        if ( ! $is_woo_page ) {
            wp_dequeue_script( 'wc-order-attribution' );
            wp_dequeue_script( 'sourcebuster-js' );
        }
    }

    private function get_dynamic_css() {
        $css = '';
        if ( class_exists( __NAMESPACE__ . '\\Design_Tokens' ) ) {
            $css = Design_Tokens::build_root_css();
        }

        if ( '' === trim( $css ) ) {
            $primary = developer_starter_get_option( 'primary_color', '#2563eb' );
            $primary_dark = $this->darken_color( $primary, 15 );
            $primary_light = $this->lighten_color( $primary, 10 );

            // 仅输出动态CSS变量（静态样式已移至 dynamic-components.css）
            $css = ":root{
                --color-primary:{$primary};
                --color-primary-dark:{$primary_dark};
                --color-primary-light:{$primary_light};
                --color-bg-dark: #111827;
                --color-card-dark: #1f2937;
                --color-text-light: #f3f4f6;
            }";
        }

        $css .= $this->get_badge_style_variables_css();

        $css .= "
        [data-theme='dark'] .author-profile-header,
        .dark-mode .author-profile-header,
        [data-theme='dark'] .account-header,
        .dark-mode .account-header {
            background: var(--color-bg-dark) !important;
            background-image: none !important;
            color: var(--color-text-light);
        }
        
        [data-theme='dark'] .author-profile-header::before,
        .dark-mode .author-profile-header::before,
        [data-theme='dark'] .account-header::before,
        .dark-mode .account-header::before {
            opacity: 0.05;
        }";
        
        // 顶部导航的极简模式依然保留独立交互样式，但颜色改为走组件样式解析结果。
        $minimalist_menu = developer_starter_get_option( 'minimalist_menu_enable', '' );
        
        if ( $minimalist_menu ) {
            // 简洁模式：下划线 + 文字颜色
            $css .= "
            .primary-navigation>ul>li>a {
                position: relative;
            }
            .primary-navigation>ul>li>a::before {
                content: '';
                position: absolute;
                bottom: 0px;
                left: 50%;
                width: 0;
                height: 2px;
                background-color: var(--qiling-header-nav-hover-current, var(--qiling-component-nav-hover-text, #ffffff));
                transition: all 0.3s ease;
                transform: translateX(-50%);
                z-index: 10;
            }
            .primary-navigation>ul>li:hover>a::before,
            .primary-navigation>ul>li.current-menu-item>a::before,
            .primary-navigation>ul>li.current-menu-ancestor>a::before,
            .primary-navigation>ul>li.current-menu-parent>a::before,
            .primary-navigation>ul>li.current_page_item>a::before,
            .primary-navigation>ul>li.current_page_parent>a::before,
            .primary-navigation>ul>li.current_page_ancestor>a::before {
                width: 80%;
            }
            .primary-navigation>ul>li>a:hover,
            .primary-navigation>ul>li.current-menu-item>a,
            .primary-navigation>ul>li.current-menu-ancestor>a,
            .primary-navigation>ul>li.current-menu-parent>a,
            .primary-navigation>ul>li.current_page_item>a,
            .primary-navigation>ul>li.current_page_parent>a,
            .primary-navigation>ul>li.current_page_ancestor>a {
                background: transparent !important;
                color: var(--qiling-header-nav-hover-current, var(--qiling-component-nav-hover-text, #ffffff)) !important;
            }";
        }

        // 验证码样式（字体大小 / 分割线）
        $captcha_font_size = absint( developer_starter_get_option( 'captcha_font_size', 0 ) );
        if ( $captcha_font_size > 0 ) {
            $css .= "
            .login-modal .modal-captcha .captcha-text,
            .ds-search-slider-text {
                font-size: {$captcha_font_size}px;
            }";
        }

        $captcha_line_number = absint( developer_starter_get_option( 'captcha_line_number', 0 ) );
        if ( $captcha_line_number > 0 ) {
            $line_count = min( max( $captcha_line_number, 1 ), 20 );
            $css .= "
            .login-modal .modal-captcha .captcha-track,
            .ds-search-slider-container {
                background-image: linear-gradient(90deg, rgba(255, 255, 255, 0.35) 1px, transparent 1px);
                background-size: calc(100% / {$line_count}) 100%;
                background-position: 0 0;
            }";
        }

        $css .= "
        .ds-search-slider-container.is-waiting {
            opacity: 0.6;
        }";

        // Menu Badge Styles (Allows <t> tag in navigation label)
        $css .= "
        .primary-navigation > ul > li.qiling-menu-item-has-badge {
            z-index: 30;
        }
        .primary-navigation > ul > li.qiling-menu-item-has-badge > a {
            z-index: 30;
        }
        .primary-navigation > ul > li:has(> a t),
        .primary-navigation > ul > li:has(> a .menu-badge) {
            z-index: 30;
        }
        .primary-navigation > ul > li:has(> a t) > a,
        .primary-navigation > ul > li:has(> a .menu-badge) > a {
            z-index: 30;
        }
        .primary-navigation > ul > li > a {
            position: relative;
            overflow: visible !important;
        }
        .primary-navigation > ul > li > a t,
        .primary-navigation > ul > li > a .menu-badge {
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(30%, -50%);
            font-size: 10px;
            line-height: 16px;
            padding: 0 5px;
            border-radius: 3px;
            color: #fff;
            background: #ff4d4f; /* Default fallback color */
            white-space: nowrap;
            font-style: normal;
            display: inline-block;
            text-decoration: none;
            z-index: 31;
            font-weight: normal;
            pointer-events: none;
        }
        /* Fix for mobile menu */
        .mobile-menu-nav li a t,
        .mobile-menu-nav li a .menu-badge {
            display: inline-block;
            margin-left: 5px;
            font-size: 10px;
            padding: 0 4px;
            border-radius: 2px;
            color: #fff;
            background: #ff4d4f;
            font-style: normal;
            line-height: 1.4;
            vertical-align: middle;
            transform: none;
            position: static;
        }
        ";

        return (string) apply_filters( 'developer_starter_dynamic_css', $css );
    }

    /**
     * Build optional CSS variables for post cover badges and QiApp badges.
     *
     * @return string
     */
    private function get_badge_style_variables_css() {
        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            return '';
        }

        $variable_map = array(
            'cover_badge_video_bg'      => '--qiling-cover-badge-video-bg',
            'cover_badge_video_text'    => '--qiling-cover-badge-video-text',
            'cover_badge_app_bg'        => '--qiling-cover-badge-app-bg',
            'cover_badge_app_text'      => '--qiling-cover-badge-app-text',
            'cover_badge_free_bg'       => '--qiling-cover-badge-free-bg',
            'cover_badge_free_text'     => '--qiling-cover-badge-free-text',
            'cover_badge_vip_bg'        => '--qiling-cover-badge-vip-bg',
            'cover_badge_vip_text'      => '--qiling-cover-badge-vip-text',
            'cover_badge_album_bg'      => '--qiling-cover-badge-album-bg',
            'cover_badge_album_text'    => '--qiling-cover-badge-album-text',
            'cover_badge_category_bg'   => '--qiling-cover-badge-category-bg',
            'cover_badge_category_text' => '--qiling-cover-badge-category-text',
            'cover_badge_sticky_bg'     => '--qiling-cover-badge-sticky-bg',
            'cover_badge_sticky_text'   => '--qiling-cover-badge-sticky-text',
            'cover_badge_hd_bg'         => '--qiling-cover-badge-hd-bg',
            'cover_badge_hd_text'       => '--qiling-cover-badge-hd-text',
            'cover_badge_rating_bg'     => '--qiling-cover-badge-rating-bg',
            'cover_badge_rating_text'   => '--qiling-cover-badge-rating-text',
            'qiapp_badge_free_bg'       => '--qiling-qiapp-badge-free-bg',
            'qiapp_badge_free_text'     => '--qiling-qiapp-badge-free-text',
            'qiapp_badge_paid_bg'       => '--qiling-qiapp-badge-paid-bg',
            'qiapp_badge_paid_text'     => '--qiling-qiapp-badge-paid-text',
            'qiapp_badge_trial_bg'      => '--qiling-qiapp-badge-trial-bg',
            'qiapp_badge_trial_text'    => '--qiling-qiapp-badge-trial-text',
            'qiapp_badge_neutral_bg'    => '--qiling-qiapp-badge-neutral-bg',
            'qiapp_badge_neutral_text'  => '--qiling-qiapp-badge-neutral-text',
        );

        $variables = array();
        foreach ( $variable_map as $option_key => $css_variable ) {
            if ( ! isset( $options[ $option_key ] ) ) {
                continue;
            }

            $value = $this->sanitize_badge_css_paint_value( $options[ $option_key ] );
            if ( '' === $value ) {
                continue;
            }

            $variables[] = '    ' . $css_variable . ': ' . $value . ';';
        }

        if ( empty( $variables ) ) {
            return '';
        }

        return "\n:root{\n" . implode( "\n", $variables ) . "\n}\n";
    }

    /**
     * Sanitize a CSS color/paint value before writing it into custom properties.
     *
     * @param mixed $value Raw setting value.
     * @return string
     */
    private function sanitize_badge_css_paint_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value || strlen( $value ) > 240 || preg_match( '/[;{}<>\r\n]/', $value ) ) {
            return '';
        }

        if ( in_array( strtolower( $value ), array( 'transparent', 'currentcolor' ), true ) ) {
            return strtolower( $value );
        }

        if ( function_exists( 'sanitize_hex_color' ) ) {
            $hex = sanitize_hex_color( $value );
            if ( $hex ) {
                return $hex;
            }
        }

        if ( preg_match( '/^var\(--[a-zA-Z0-9_-]+(?:\s*,\s*[a-zA-Z0-9#().,%\s\/_-]+)?\)$/', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(rgba?|hsla?)\([a-zA-Z0-9#(),.%\s\/_+-]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(linear-gradient|radial-gradient|conic-gradient|color-mix)\([a-zA-Z0-9#(),.%\s\/_+-]+\)$/i', $value ) ) {
            return $value;
        }

        return '';
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
