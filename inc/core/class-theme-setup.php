<?php
/**
 * 主题设置类
 *
 * 处理主题初始化、功能支持和基础配置。
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 主题设置类
 */
class Theme_Setup {

    /**
     * 构造函数
     */
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
        add_action( 'init', array( $this, 'register_menus' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'init', array( $this, 'restore_default_page_editor_support' ), 100 );
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_action( 'init', array( $this, 'maybe_flush_theme_rewrite_rules' ), 1000 );
        add_action( 'admin_init', array( $this, 'restore_default_page_editor_support' ), 100 );
        add_filter( 'rest_prepare_post_type', array( $this, 'restore_page_editor_support_in_rest' ), 10, 3 );
        add_filter( 'redirect_canonical', array( $this, 'disable_canonical_for_gallery' ), 10, 2 );
        add_filter( 'body_class', array( $this, 'body_classes' ) );
        add_action( 'wp_head', array( $this, 'add_preconnect_links' ), 1 );
        add_filter( 'plupload_default_settings', array( $this, 'configure_plupload_client_resize' ) );
        Trace_ID::init();
    }

    /**
     * 添加自定义查询参数
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'gallery_page';
        $vars[] = 'qiling_builder';
        return $vars;
    }

    /**
     * 添加自定义重写规则
     */
    public function add_rewrite_rules() {
        // 相册模式分页规则：ID_页码.html -> p=ID&gallery_page=页码
        add_rewrite_rule(
            '([0-9]+)_([0-9]+)\.html$',
            'index.php?p=$matches[1]&gallery_page=$matches[2]',
            'top'
        );
    }

    /**
     * 主题切换后，在 init 已注册主题 rewrite rules 后软刷新一次。
     *
     * @return void
     */
    public function maybe_flush_theme_rewrite_rules() {
        if ( wp_installing() ) {
            return;
        }

        $pending_version = (string) get_option( 'developer_starter_theme_rewrite_flush_version', '' );
        if ( '' === $pending_version ) {
            return;
        }

        flush_rewrite_rules( false );
        update_option( 'developer_starter_theme_rewrite_flushed_version', $pending_version, false );
        delete_option( 'developer_starter_theme_rewrite_flush_version' );
    }

    /**
     * 保留 WordPress 页面默认的正文编辑能力。
     *
     * Gutenberg 的文章类型信息来自 REST 预加载，REST 请求不经过 admin_init。
     * 因此这里同时挂在 init 和 admin_init，确保后台页面与 REST 响应看到一致的默认支持项。
     *
     * @return void
     */
    public function restore_default_page_editor_support() {
        if ( ! post_type_exists( 'page' ) || post_type_supports( 'page', 'editor' ) ) {
            return;
        }

        add_post_type_support( 'page', 'editor' );
    }

    /**
     * 确保 Gutenberg 预加载的 page 类型信息保留正文编辑支持。
     *
     * @param mixed $response  REST 响应对象。
     * @param mixed $post_type 文章类型对象。
     * @param mixed $request   REST 请求对象。
     * @return mixed
     */
    public function restore_page_editor_support_in_rest( $response, $post_type, $request ) {
        unset( $request );

        if (
            ! ( $post_type instanceof \WP_Post_Type )
            || 'page' !== $post_type->name
            || ! ( $response instanceof \WP_REST_Response )
        ) {
            return $response;
        }

        $this->restore_default_page_editor_support();

        $data = $response->get_data();
        if ( ! is_array( $data ) ) {
            return $response;
        }

        if ( ! isset( $data['supports'] ) || ! is_array( $data['supports'] ) ) {
            $data['supports'] = array();
        }

        $data['supports']['editor'] = true;
        $response->set_data( $data );

        return $response;
    }

    /**
     * 禁用相册模式的规范重定向
     * 解决 WordPress 认为 page 参数无效而重定向回第一页的问题
     */
    public function disable_canonical_for_gallery( $redirect_url, $requested_url ) {
        unset( $requested_url );

        $builder_flag = isset( $_GET['qiling_builder'] )
            ? sanitize_text_field( wp_unslash( (string) $_GET['qiling_builder'] ) )
            : '';
        if ( '1' === $builder_flag ) {
            return false;
        }

        if ( is_singular( 'post' ) && get_query_var( 'gallery_page' ) ) {
            $post_id = get_queried_object_id();
            if ( $post_id && get_post_meta( $post_id, '_qiling_gallery_mode', true ) === '1' ) {
                return false;
            }
        }
        return $redirect_url;
    }

    /**
     * 设置主题默认值并注册WordPress功能支持
     */
    public function setup_theme() {
        // 主题 UI 翻译是基础能力，不受前台语言切换模式控制。
        load_theme_textdomain( 'qiling', QILING_DIR . '/languages' );

        // 添加默认文章和评论RSS订阅链接到head
        add_theme_support( 'automatic-feed-links' );

        // 让WordPress管理文档标题
        add_theme_support( 'title-tag' );

        // 启用文章缩略图支持
        add_theme_support( 'post-thumbnails' );

        // 设置默认缩略图尺寸
        set_post_thumbnail_size( 1200, 630, true );

        // 添加自定义图片尺寸
        add_image_size( 'developer-starter-hero', 1920, 1080, true );
        add_image_size( 'developer-starter-card', 600, 400, true );
        add_image_size( 'developer-starter-thumbnail', 300, 200, true );
        add_image_size( 'developer-starter-logo', 200, 100, false );

        // 切换默认核心标记为HTML5
        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
            'navigation-widgets',
        ) );

        // 添加自定义Logo支持
        add_theme_support( 'custom-logo', array(
            'height'      => 100,
            'width'       => 300,
            'flex-height' => true,
            'flex-width'  => true,
        ) );

        // 添加自定义背景支持
        add_theme_support( 'custom-background', array(
            'default-color' => 'ffffff',
        ) );

        // 添加宽度和全宽对齐支持
        add_theme_support( 'align-wide' );

        // 添加响应式嵌入支持
        add_theme_support( 'responsive-embeds' );

        // 添加自定义行高支持
        add_theme_support( 'custom-line-height' );

        // 添加自定义间距支持
        add_theme_support( 'custom-spacing' );

        // 可选：站长手动启用主题编辑器样式时，才让 Gutenberg 加载主题的编辑画布样式。
        if ( function_exists( 'developer_starter_get_option' ) && '1' === (string) developer_starter_get_option( 'enable_gutenberg_editor_style', '' ) ) {
            add_theme_support( 'editor-styles' );
            add_editor_style( 'assets/css/editor-style.css' );
        }


        // 添加小工具选择性刷新支持
        add_theme_support( 'customize-selective-refresh-widgets' );

        // 设置内容宽度
        global $content_width;
        if ( ! isset( $content_width ) ) {
            $content_width = 1200;
        }
    }

    /**
     * 注册导航菜单
     */
    public function register_menus() {
        register_nav_menus( array(
            'primary' => esc_html__( '主导航菜单', 'qiling' ),
            'mobile'  => esc_html__( '移动端导航菜单（可选，默认使用主导航）', 'qiling' ),
            'mobile_bottom' => esc_html__( '移动端底部菜单', 'qiling' ),
            'left_sidebar' => esc_html__( '左侧导航菜单（桌面端）', 'qiling' ),
        ) );
    }

    /**
     * 注册小工具区域
     */
    public function register_sidebars() {
        // 主侧边栏
        register_sidebar( array(
            'name'          => esc_html__( '主侧边栏', 'qiling' ),
            'id'            => 'sidebar-main',
            'description'   => esc_html__( '在页面侧边栏显示的小工具', 'qiling' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );

        register_sidebar( array(
            'name'          => esc_html__( '页脚联系我们区域', 'qiling' ),
            'id'            => 'footer-contact',
            'description'   => esc_html__( '用于页脚联系方式板块的小工具内容', 'qiling' ),
            'before_widget' => '<div id="%1$s" class="footer-contact-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-contact-widget-title">',
            'after_title'   => '</h4>',
        ) );

        register_sidebar( array(
            'name'          => esc_html__( '页脚快速链接区域', 'qiling' ),
            'id'            => 'footer-quick-links',
            'description'   => esc_html__( '用于页脚快速链接板块的小工具内容（已设置时优先显示）', 'qiling' ),
            'before_widget' => '<div id="%1$s" class="footer-quick-links-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-quick-links-widget-title">',
            'after_title'   => '</h4>',
        ) );


        // 文章侧边栏
        register_sidebar( array(
            'name'          => esc_html__( '文章侧边栏', 'qiling' ),
            'id'            => 'sidebar-post',
            'description'   => esc_html__( '在文章详情页侧边栏显示的小工具', 'qiling' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
        
        // 页面侧边栏
        register_sidebar( array(
            'name'          => esc_html__( '页面侧边栏', 'qiling' ),
            'id'            => 'sidebar-page',
            'description'   => esc_html__( '在默认页面侧边栏显示的小工具', 'qiling' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }

    /**
     * 添加自定义body类
     *
     * @param array $classes 现有body类。
     * @return array 修改后的body类。
     */
    public function body_classes( $classes ) {
        // 添加页面别名作为类
        if ( is_singular() ) {
            global $post;
            if ( $post instanceof \WP_Post && '' !== (string) $post->post_name ) {
                $classes[] = sanitize_html_class( 'page-' . $post->post_name );
            }
        }

        // 添加固定头部选项的类
        if ( developer_starter_get_option( 'header_sticky', true ) ) {
            $classes[] = 'has-sticky-header';
        }

        // 添加头部样式的类
        $header_style = developer_starter_get_option( 'header_style', 'default' );
        $classes[] = sanitize_html_class( 'header-style-' . $header_style, 'header-style-default' );

        // 添加侧边栏布局的类
        if ( is_active_sidebar( 'sidebar-main' ) && ! is_page_template( 'templates/template-fullwidth.php' ) ) {
            $classes[] = 'has-sidebar';
        } else {
            $classes[] = 'no-sidebar';
        }

        if ( has_nav_menu( 'mobile_bottom' ) ) {
            $classes[] = 'has-mobile-bottom-nav';
        }

        return $classes;
    }

    /**
     * 添加预连接链接以提升性能
     */
    public function add_preconnect_links() {
        // 如果使用CDN则预连接
        $cdn_url = developer_starter_get_option( 'cdn_url', '' );
        if ( ! empty( $cdn_url ) ) {
            echo '<link rel="preconnect" href="' . esc_url( $cdn_url ) . '">' . "\n";
        }
    }

    /**
     * 配置 Plupload 原生前端客户端缩放（防大图打爆带宽与存储）。
     *
     * 规范第 37 项：在前台投稿插图或后台上传媒体时，浏览器前端通过 Plupload 自动等比缩放至最大 1920px，
     * 避免访客/作者直接上传 15MB 原始相机生图导致服务器带宽和存储打爆。
     *
     * @param array $settings Plupload 配置。
     * @return array
     */
    public function configure_plupload_client_resize( $settings ) {
        if ( ! is_array( $settings ) ) {
            $settings = array();
        }

        $settings['resize'] = array(
            'width'            => (int) apply_filters( 'developer_starter_upload_max_width', 1920 ),
            'height'           => (int) apply_filters( 'developer_starter_upload_max_height', 1920 ),
            'quality'          => (int) apply_filters( 'developer_starter_upload_quality', 85 ),
            'crop'             => false,
            'preserve_headers' => false,
        );

        return $settings;
    }
}
