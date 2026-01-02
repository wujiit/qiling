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
        add_filter( 'body_class', array( $this, 'body_classes' ) );
        add_action( 'wp_head', array( $this, 'add_preconnect_links' ), 1 );
    }

    /**
     * 设置主题默认值并注册WordPress功能支持
     */
    public function setup_theme() {
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

        // 添加编辑器样式支持
        add_theme_support( 'editor-styles' );
        add_editor_style( 'assets/css/editor-style.css' );

        // 添加WooCommerce支持（如需要）
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );

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
            'primary' => esc_html__( '主导航菜单', 'developer-starter' ),
            'mobile'  => esc_html__( '移动端导航菜单（可选，默认使用主导航）', 'developer-starter' ),
        ) );
    }

    /**
     * 注册小工具区域
     */
    public function register_sidebars() {
        // 主侧边栏
        register_sidebar( array(
            'name'          => esc_html__( '主侧边栏', 'developer-starter' ),
            'id'            => 'sidebar-main',
            'description'   => esc_html__( '在页面侧边栏显示的小工具', 'developer-starter' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );

        // 页脚小工具区域（4列）
        for ( $i = 1; $i <= 4; $i++ ) {
            register_sidebar( array(
                'name'          => sprintf( esc_html__( '页脚区域 %d', 'developer-starter' ), $i ),
                'id'            => 'footer-' . $i,
                'description'   => sprintf( esc_html__( '页脚第 %d 列小工具', 'developer-starter' ), $i ),
                'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="footer-widget-title">',
                'after_title'   => '</h4>',
            ) );
        }

        // 商店侧边栏（用于WooCommerce）
        register_sidebar( array(
            'name'          => esc_html__( '商店侧边栏', 'developer-starter' ),
            'id'            => 'sidebar-shop',
            'description'   => esc_html__( '商店页面侧边栏小工具', 'developer-starter' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
        
        // 文章侧边栏
        register_sidebar( array(
            'name'          => esc_html__( '文章侧边栏', 'developer-starter' ),
            'id'            => 'sidebar-post',
            'description'   => esc_html__( '在文章详情页侧边栏显示的小工具', 'developer-starter' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
        
        // 页面侧边栏
        register_sidebar( array(
            'name'          => esc_html__( '页面侧边栏', 'developer-starter' ),
            'id'            => 'sidebar-page',
            'description'   => esc_html__( '在默认页面侧边栏显示的小工具', 'developer-starter' ),
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
            $classes[] = 'page-' . $post->post_name;
        }

        // 添加固定头部选项的类
        if ( developer_starter_get_option( 'header_sticky', true ) ) {
            $classes[] = 'has-sticky-header';
        }

        // 添加头部样式的类
        $header_style = developer_starter_get_option( 'header_style', 'default' );
        $classes[] = 'header-style-' . $header_style;

        // 添加侧边栏布局的类
        if ( is_active_sidebar( 'sidebar-main' ) && ! is_page_template( 'templates/template-fullwidth.php' ) ) {
            $classes[] = 'has-sidebar';
        } else {
            $classes[] = 'no-sidebar';
        }

        // 添加移动设备的类
        if ( wp_is_mobile() ) {
            $classes[] = 'is-mobile';
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
}
