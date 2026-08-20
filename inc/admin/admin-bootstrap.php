<?php
/**
 * Admin bootstrap helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_register_blog_sidebar' ) ) {
    /**
     * 注册博客布局侧边栏。
     *
     * @return void
     */
    function developer_starter_register_blog_sidebar() {
        register_sidebar(
            array(
                'name'          => __( '博客布局侧边栏', 'developer-starter' ),
                'id'            => 'blog-module-sidebar',
                'description'   => __( '用于博客布局模块的侧边栏小工具区域', 'developer-starter' ),
                'before_widget' => '<div id="%1$s" class="sidebar-widget widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="widget-title">',
                'after_title'   => '</h4>',
            )
        );
    }
}
add_action( 'widgets_init', 'developer_starter_register_blog_sidebar' );

if ( ! function_exists( 'developer_starter_get_admin_settings_instance' ) ) {
    /**
     * 获取后台主题设置单例。
     *
     * @return Developer_Starter\Admin\Admin_Settings
     */
    function developer_starter_get_admin_settings_instance() {
        static $instance = null;
        if ( null === $instance ) {
            $instance = new Developer_Starter\Admin\Admin_Settings();
        }
        return $instance;
    }
}

if ( ! function_exists( 'developer_starter_admin_post_ds_repair_modules_meta' ) ) {
    /**
     * admin-post 修复入口（避免设置页懒加载导致 action 未注册而被 WP Core 拒绝）。
     *
     * @return void
     */
    function developer_starter_admin_post_ds_repair_modules_meta() {
        if ( ! function_exists( 'developer_starter_get_admin_settings_instance' ) ) {
            wp_die( esc_html__( '主题设置组件未就绪。', 'developer-starter' ) );
        }

        developer_starter_get_admin_settings_instance()->handle_repair_modules_meta();
    }
}
add_action( 'admin_post_ds_repair_modules_meta', 'developer_starter_admin_post_ds_repair_modules_meta', 0 );

if ( ! function_exists( 'developer_starter_admin_post_ds_repair_theme_options' ) ) {
    /**
     * admin-post 主题设置修复入口（避免设置页懒加载导致 action 未注册而被 WP Core 拒绝）。
     *
     * @return void
     */
    function developer_starter_admin_post_ds_repair_theme_options() {
        if ( ! function_exists( 'developer_starter_get_admin_settings_instance' ) ) {
            wp_die( esc_html__( '主题设置组件未就绪。', 'developer-starter' ) );
        }

        developer_starter_get_admin_settings_instance()->handle_repair_theme_options();
    }
}
add_action( 'admin_post_ds_repair_theme_options', 'developer_starter_admin_post_ds_repair_theme_options', 0 );

if ( ! function_exists( 'developer_starter_should_boot_admin_settings' ) ) {
    /**
     * 当前后台请求是否需要完整初始化主题设置页逻辑。
     *
     * @return bool
     */
    function developer_starter_should_boot_admin_settings() {
        if ( ! is_admin() ) {
            return false;
        }

        $page = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['page'] ) ) : '';
        if ( $page === 'developer-starter-settings' ) {
            return true;
        }

        global $pagenow;

        if ( $pagenow === 'options.php' ) {
            $option_page = isset( $_POST['option_page'] ) ? sanitize_key( wp_unslash( (string) $_POST['option_page'] ) ) : '';
            if ( $option_page === 'developer_starter_settings' ) {
                return true;
            }
        }

        if ( $pagenow === 'admin-post.php' ) {
            $admin_post_action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';
            $allowed_admin_post_actions = array(
                'ds_export_settings',
                'ds_import_settings',
                'ds_repair_modules_meta',
                'ds_repair_theme_options',
                'ds_create_theme_table',
                'developer_starter_export_i18n_seo_report',
                'developer_starter_generate_i18n_seo_meta',
            );
            if ( in_array( $admin_post_action, $allowed_admin_post_actions, true ) ) {
                return true;
            }
        }

        if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
            $ajax_action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';
            $allowed_ajax_actions = array(
                'developer_starter_refresh_version',
                'developer_starter_send_smtp_test_email',
                'developer_starter_db_cleanup',
                'developer_starter_run_theme_cleanup',
                'developer_starter_regenerate_cleanup_cron_token',
                'developer_starter_db_stats',
                'developer_starter_clear_cleanup_rest_audit_log',
                'developer_starter_poster_cache_stats',
                'developer_starter_clear_poster_cache',
                'developer_starter_thumbnail_cache_stats',
                'developer_starter_clear_thumbnail_cache',
                'developer_starter_clear_unused_thumbnail_cache',
                'developer_starter_github_activity_cache_stats',
                'developer_starter_clear_github_activity_cache',
                'developer_starter_generate_css',
                'developer_starter_check_gzip_status',
                'developer_starter_check_split_css_integrity',
                'developer_starter_clear_ip_cache',
                'developer_starter_toggle_favorite_setting',
                'developer_starter_test_ai_connection',
                'developer_starter_seo_health_scan',
                'developer_starter_seo_health_clear',
                'developer_starter_detect_ecosystem_plugins',
                'developer_starter_reset_ip_usermeta',
            );
            if ( in_array( $ajax_action, $allowed_ajax_actions, true ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_render_admin_settings_page' ) ) {
    /**
     * 主题设置页回调（懒加载）。
     *
     * @return void
     */
    function developer_starter_render_admin_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'developer-starter' ) );
        }

        developer_starter_get_admin_settings_instance()->render_settings_page();
    }
}

if ( ! function_exists( 'developer_starter_register_admin_settings_menu' ) ) {
    /**
     * 注册后台主题设置菜单（轻量入口）。
     *
     * @return void
     */
    function developer_starter_register_admin_settings_menu() {
        add_menu_page(
            __( '企业主题设置', 'developer-starter' ),
            __( '企业主题设置', 'developer-starter' ),
            'manage_options',
            'developer-starter-settings',
            'developer_starter_render_admin_settings_page',
            'dashicons-building',
            60
        );
    }
}
add_action( 'admin_menu', 'developer_starter_register_admin_settings_menu', 10 );

if ( ! function_exists( 'developer_starter_add_admin_settings_bar_menu' ) ) {
    /**
     * 注册后台顶部工具栏入口（轻量入口）。
     *
     * @param WP_Admin_Bar $wp_admin_bar 工具栏实例。
     * @return void
     */
    function developer_starter_add_admin_settings_bar_menu( $wp_admin_bar ) {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $wp_admin_bar->add_node(
            array(
                'id'     => 'developer-starter-settings',
                'title'  => '<span class="ab-icon dashicons dashicons-admin-generic" style="margin-top:2px;"></span><span class="ab-label">' . __( '启灵主题设置', 'developer-starter' ) . '</span>',
                'href'   => admin_url( 'admin.php?page=developer-starter-settings' ),
                'parent' => false,
            )
        );
    }
}
add_action( 'admin_bar_menu', 'developer_starter_add_admin_settings_bar_menu', 80 );

if ( ! function_exists( 'developer_starter_boot_meta_boxes' ) ) {
    /**
     * 后台模块面板延迟初始化。
     * 等待 WordPress 核心和其他基础组件加载后再挂载相关钩子。
     *
     * @return void
     */
    function developer_starter_boot_meta_boxes() {
        if ( ! is_admin() ) {
            return;
        }

        static $bootstrapped = false;
        if ( $bootstrapped ) {
            return;
        }
        $bootstrapped = true;

        new Developer_Starter\Admin\Meta_Boxes();
    }
}
add_action( 'admin_init', 'developer_starter_boot_meta_boxes', 30 );

if ( ! function_exists( 'developer_starter_set_default_page_admin_order' ) ) {
    /**
     * 后台页面列表默认按创建时间倒序（最新创建在前）。
     *
     * @param WP_Query $query 查询对象。
     * @return void
     */
    function developer_starter_set_default_page_admin_order( $query ) {
        if ( ! ( $query instanceof WP_Query ) || ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        global $pagenow;
        if ( 'edit.php' !== $pagenow ) {
            return;
        }

        $post_type = (string) $query->get( 'post_type' );
        if ( $post_type === '' && isset( $_GET['post_type'] ) ) {
            $post_type = sanitize_key( wp_unslash( (string) $_GET['post_type'] ) );
        }
        if ( 'page' !== $post_type ) {
            return;
        }

        $requested_orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( (string) $_GET['orderby'] ) ) : '';
        if ( $requested_orderby !== '' ) {
            return;
        }

        $query->set( 'orderby', 'date' );
        $query->set( 'order', 'DESC' );
    }
}
add_action( 'pre_get_posts', 'developer_starter_set_default_page_admin_order', 20 );
