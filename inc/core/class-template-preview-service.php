<?php
/**
 * 模板中心免存库实时虚拟预览服务。
 *
 * 允许管理员在后台直接预览任意官方行业模板，
 * 完全在内存中虚拟渲染，零写库、不占用文章 ID、不产生任何数据库残留。
 *
 * @package Developer_Starter
 * @since 2.5.18
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Template_Preview_Service {

    /**
     * 单例实例。
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * 当前请求是否处于模板预览状态。
     *
     * @var bool
     */
    private $is_preview = false;

    /**
     * 正在预览的模板文件相对路径。
     *
     * @var string
     */
    private $preview_template = '';

    /**
     * 正在预览的模板官方包数据。
     *
     * @var array|null
     */
    private $preview_package_data = null;

    /**
     * 获取服务单例。
     *
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 构造函数。
     */
    public function __construct() {
        add_action( 'init', array( $this, 'maybe_catch_preview_request' ), 10 );
    }

    /**
     * 生成指定模板的安全预览链接。
     *
     * @param string $template 模板相对路径。
     * @return string
     */
    public static function get_preview_url( $template ) {
        $template = sanitize_text_field( (string) $template );
        return add_query_arg(
            array(
                'qiling_template_preview' => $template,
                '_preview_nonce'          => wp_create_nonce( 'qiling_template_preview' ),
            ),
            home_url( '/' )
        );
    }

    /**
     * 拦截并处理模板预览请求。
     *
     * @return void
     */
    public function maybe_catch_preview_request() {
        if ( ! isset( $_GET['qiling_template_preview'] ) ) {
            return;
        }

        // 1. 权限拦截：必须具备管理员权限
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '无权访问模板预览。请先登录管理员账号。', 'qiling' ), 403 );
        }

        // 2. Nonce 安全校验
        $nonce = isset( $_GET['_preview_nonce'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['_preview_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'qiling_template_preview' ) && ! wp_verify_nonce( $nonce, 'developer_starter_template_center_create' ) ) {
            wp_die( esc_html__( '安全校验失败，预览链接已失效，请在模板中心重新点击“实时预览”。', 'qiling' ), 403 );
        }

        $raw_slug = isset( $_GET['qiling_template_preview'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['qiling_template_preview'] ) ) : '';
        $template_slug = str_replace( '\\', '/', ltrim( rawurldecode( $raw_slug ), '/' ) );
        if ( '' === $template_slug ) {
            wp_die( esc_html__( '未指定有效的模板标识。', 'qiling' ), 400 );
        }

        $package_service = new Official_Template_Package_Service();
        if ( ! $package_service->has_package_for_template( $template_slug ) ) {
            wp_die( esc_html__( '该模板没有可预览的官方页面包。', 'qiling' ), 404 );
        }

        $package_data = $package_service->load_package_for_template( $template_slug );
        if ( is_wp_error( $package_data ) || empty( $package_data['modules'] ) || ! is_array( $package_data['modules'] ) ) {
            wp_die( esc_html__( '该模板包暂无可渲染的模块内容。', 'qiling' ), 404 );
        }

        $this->is_preview           = true;
        $this->preview_template     = $template_slug;
        $this->preview_package_data = $package_data;

        // 3. 彻底禁用规范链接跳转（避免 WordPress core 将根路径 query 参数 301 重定向到网站首页）
        remove_action( 'template_redirect', 'redirect_canonical' );
        add_filter( 'redirect_canonical', '__return_false', 999 );

        // 4. 彻底禁用多语言系统拦截跳转
        global $xb_aifanyi_translator;
        if ( is_object( $xb_aifanyi_translator ) ) {
            remove_action( 'template_redirect', array( $xb_aifanyi_translator, 'xb_aifanyi_redirect_to_language_url' ) );
            remove_filter( 'redirect_canonical', array( $xb_aifanyi_translator, 'xb_aifanyi_disable_canonical_redirect_for_multilingual_request' ), 20 );
        }

        // 5. 禁用缓存
        nocache_headers();
        if ( function_exists( 'developer_starter_define_no_cache_constants' ) ) {
            developer_starter_define_no_cache_constants();
        }

        // 6. 挂载内存渲染过滤器
        add_filter( 'developer_starter_pre_get_page_modules_data', array( $this, 'filter_page_modules_data' ), 999 );
        add_filter( 'developer_starter_page_modules_data', array( $this, 'filter_page_modules_data' ), 999 );
        add_filter( 'template_include', array( $this, 'filter_template_include' ), 999 );
        add_filter( 'body_class', array( $this, 'filter_body_class' ), 999 );
        add_filter( 'show_admin_bar', '__return_false' );

        // 7. 处理页头配置（透明页头、主色调、视觉风格）
        add_filter( 'developer_starter_header_template_args', array( $this, 'filter_header_template_args' ), 999 );

        // 8. 允许后台 iframe 同源渲染，移除阻止内嵌的响应头
        add_action( 'send_headers', function() {
            header( 'X-Frame-Options: SAMEORIGIN' );
        }, 999 );

        // 9. 核心拦截：在 template_redirect 阶段直接渲染模板并退出，杜绝任何第三方重定向或首页回退
        add_action( 'template_redirect', array( $this, 'handle_template_redirect_preview' ), 1 );
    }

    /**
     * 在 template_redirect 阶段直接渲染指定模板并干净退出。
     *
     * @return void
     */
    public function handle_template_redirect_preview() {
        if ( ! $this->is_preview || empty( $this->preview_template ) ) {
            return;
        }

        $found_template = locate_template( array( $this->preview_template ) );
        if ( empty( $found_template ) || ! file_exists( $found_template ) ) {
            wp_die( esc_html__( '未找到对应的模板文件。', 'qiling' ), 404 );
        }

        $this->setup_virtual_query();

        // 允许 iframe 嵌入
        header( 'X-Frame-Options: SAMEORIGIN' );

        // 直接执行页面模板并安全退出
        include $found_template;
        exit;
    }

    /**
     * 过滤模块数据：从内存中返回官方包的模块数组，无需查询数据库！
     *
     * @param mixed $modules 原模块数据。
     * @return array
     */
    public function filter_page_modules_data( $modules = null ) {
        if ( $this->is_preview && ! empty( $this->preview_package_data['modules'] ) ) {
            return $this->preview_package_data['modules'];
        }

        return is_array( $modules ) ? $modules : array();
    }

    /**
     * 注入预览专用页头参数（透明页头、品牌色、间距等）。
     *
     * @param array $args 原页头参数。
     * @return array
     */
    public function filter_header_template_args( $args ) {
        if ( ! $this->is_preview || ! is_array( $args ) ) {
            return $args;
        }

        if ( ! empty( $this->preview_package_data['transparent_header'] ) ) {
            $args['enable_transparent_header']         = true;
            $args['has_transparent_header_hero_first'] = true;
            if ( ! in_array( 'header-transparent', $args['header_classes'], true ) ) {
                $args['header_classes'][] = 'header-transparent';
            }
            if ( ! in_array( 'has-transparent-header', $args['body_classes'], true ) ) {
                $args['body_classes'][] = 'has-transparent-header';
            }
        }

        return $args;
    }

    /**
     * 配置纯内存虚拟 WP_Query 和 Post 对象。
     *
     * @return void
     */
    private function setup_virtual_query() {
        global $post, $wp_query, $wp_the_query, $pages;

        $title = ! empty( $this->preview_package_data['title'] )
            ? (string) $this->preview_package_data['title']
            : __( '模板预览', 'qiling' );

        $virtual_post = (object) array(
            'ID'                    => 0,
            'post_author'           => get_current_user_id(),
            'post_date'             => current_time( 'mysql' ),
            'post_date_gmt'         => current_time( 'mysql', 1 ),
            'post_title'            => $title,
            'post_content'          => '',
            'post_status'           => 'publish',
            'comment_status'        => 'closed',
            'ping_status'           => 'closed',
            'post_name'             => 'template-preview',
            'post_type'             => 'page',
            'filter'                => 'raw',
        );

        $post  = new \WP_Post( $virtual_post );
        $pages = array( '' );

        if ( $wp_query instanceof \WP_Query ) {
            $wp_query->post              = $post;
            $wp_query->posts             = array( $post );
            $wp_query->post_count        = 1;
            $wp_query->is_page           = true;
            $wp_query->is_singular       = true;
            $wp_query->is_home           = false;
            $wp_query->is_front_page     = false;
            $wp_query->is_404            = false;
            $wp_query->queried_object    = $post;
            $wp_query->queried_object_id = 0;
        }

        if ( $wp_the_query instanceof \WP_Query ) {
            $wp_the_query->post              = $post;
            $wp_the_query->posts             = array( $post );
            $wp_the_query->post_count        = 1;
            $wp_the_query->is_page           = true;
            $wp_the_query->is_singular       = true;
            $wp_the_query->is_home           = false;
            $wp_the_query->is_front_page     = false;
            $wp_the_query->is_404            = false;
            $wp_the_query->queried_object    = $post;
            $wp_the_query->queried_object_id = 0;
        }

        setup_postdata( $post );
    }

    /**
     * 拦截模板加载：构建纯内存虚拟 WP_Post，不写入数据库、不占用 ID！
     *
     * @param string $template 原模板路径。
     * @return string
     */
    public function filter_template_include( $template ) {
        if ( ! $this->is_preview || empty( $this->preview_template ) ) {
            return $template;
        }

        $found_template = locate_template( array( $this->preview_template ) );
        if ( empty( $found_template ) ) {
            return $template;
        }

        $this->setup_virtual_query();

        return $found_template;
    }

    /**
     * 注入预览专用 body class。
     *
     * @param array $classes CSS 类名。
     * @return array
     */
    public function filter_body_class( $classes ) {
        if ( $this->is_preview ) {
            $classes[] = 'is-qiling-template-preview';
        }

        return $classes;
    }
}
