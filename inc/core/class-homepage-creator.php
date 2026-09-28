<?php
/**
 * 首页创建器类
 *
 * 当主题激活时自动创建模块化首页
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
 * 首页创建器类
 */
class Homepage_Creator {

    /**
     * 首页标题
     */
    const PAGE_TITLE = '首页';

    /**
     * 首页别名
     */
    const PAGE_SLUG = 'home';

    /**
     * 构造函数
     */
    public function __construct() {
        // 主题激活时创建首页
        add_action( 'after_switch_theme', array( $this, 'on_theme_activation' ) );

        // 页面保存时按模板自动填充首页预设模块
        add_action( 'save_post', array( $this, 'on_page_save' ), 99, 2 );
        
        // 显示管理后台通知
        add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
        
        // 处理通知关闭
        add_action( 'admin_init', array( $this, 'dismiss_notice' ), 5 );
    }

    /**
     * 页面保存时的回调
     *
     * @param int     $post_id 页面ID
     * @param WP_Post $post    页面对象
     */
    public function on_page_save( $post_id, $post ) {
        if ( ! $post instanceof \WP_Post || $post->post_type !== 'page' ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $template = get_post_meta( $post_id, '_wp_page_template', true );
        if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
            $template = developer_starter_normalize_page_template_slug( $template );
        }

        if ( $template !== 'templates/template-home.php' ) {
            return;
        }

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );

        if ( function_exists( 'developer_starter_normalize_legacy_module_data_fields' ) && is_array( $modules ) && ! empty( $modules ) ) {
            $normalized_modules = developer_starter_normalize_legacy_module_data_fields( $modules );
            if ( is_array( $normalized_modules ) && $normalized_modules !== $modules ) {
                update_post_meta( $post_id, '_developer_starter_modules', $normalized_modules );
                $modules = $normalized_modules;
            }
        }

        if ( empty( $modules ) || ! is_array( $modules ) || count( $modules ) === 0 ) {
            $this->set_default_modules( $post_id );
            update_post_meta( $post_id, '_homepage_modules_filled', '1' );
        }
    }

    /**
     * 主题激活时的回调
     */
    public function on_theme_activation() {
        $this->create_modular_homepage();
    }

    /**
     * 创建模块化首页
     */
    public function create_modular_homepage() {
        // 检查是否已存在首页
        $existing_page = get_page_by_path( self::PAGE_SLUG );
        
        if ( $existing_page ) {
            // 页面已存在，更新模板和模块
            update_post_meta( $existing_page->ID, '_wp_page_template', 'templates/template-home.php' );
            $this->set_integrated_homepage_visual_defaults( $existing_page->ID );
            
            // 如果没有模块，设置默认模块
            $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
                ? developer_starter_get_raw_page_modules_meta( $existing_page->ID )
                : get_post_meta( $existing_page->ID, '_developer_starter_modules', true );
            if ( empty( $modules ) ) {
                $this->set_default_modules( $existing_page->ID );
            }
            
            // 设置为静态首页
            $this->set_as_frontpage( $existing_page->ID );
            set_transient( 'developer_starter_homepage_notice', 'existing', 300 );
            return $existing_page->ID;
        }

        // 检查是否已存在标题为"首页"的页面
        $pages = get_posts( array(
            'post_type'      => 'page',
            'title'          => __( '首页', 'developer-starter' ),
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ) );
        
        if ( ! empty( $pages ) ) {
            $existing_by_title = $pages[0];
            update_post_meta( $existing_by_title->ID, '_wp_page_template', 'templates/template-home.php' );
            $this->set_integrated_homepage_visual_defaults( $existing_by_title->ID );
            
            $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
                ? developer_starter_get_raw_page_modules_meta( $existing_by_title->ID )
                : get_post_meta( $existing_by_title->ID, '_developer_starter_modules', true );
            if ( empty( $modules ) ) {
                $this->set_default_modules( $existing_by_title->ID );
            }
            
            $this->set_as_frontpage( $existing_by_title->ID );
            set_transient( 'developer_starter_homepage_notice', 'existing', 300 );
            return $existing_by_title->ID;
        }

        // 创建新首页
        $page_data = array(
            'post_title'   => __( '首页', 'developer-starter' ),
            'post_name'    => self::PAGE_SLUG,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id() ?: 1,
        );

        $page_id = wp_insert_post( $page_data );
        
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            // 设置页面模板为"模块化首页"
            update_post_meta( $page_id, '_wp_page_template', 'templates/template-home.php' );
            $this->set_integrated_homepage_visual_defaults( $page_id );
            
            // 设置默认模块
            $this->set_default_modules( $page_id );
            
            // 设置为静态首页
            $this->set_as_frontpage( $page_id );
            
            // 设置通知
            set_transient( 'developer_starter_homepage_notice', 'created', 300 );
            
            return $page_id;
        }

        return false;
    }

    /**
     * 设置默认模块（优先应用启灵官方最新完整首页页面包，失败时平滑降级至既有预设）。
     *
     * @param int $page_id 页面ID
     * @return void
     */
    public function set_default_modules( $page_id ) {
        if ( function_exists( 'developer_starter_maybe_fill_official_template_package_for_page_template' ) ) {
            $applied = developer_starter_maybe_fill_official_template_package_for_page_template( $page_id, 'templates/template-home.php' );
            if ( $applied ) {
                return;
            }
        }

        Page_Creator_Base::persist_default_modules_for_creator(
            $page_id,
            $this->get_default_modules( $page_id ),
            __CLASS__,
            'templates/template-home.php'
        );
    }

    /**
     * 模块化首页默认使用科技蓝绿一体化画布。
     *
     * 只在首页没有明确页面皮肤时写入，避免覆盖用户已经装修好的首页风格。
     *
     * @param int $page_id 首页 ID。
     * @return void
     */
    private function set_integrated_homepage_visual_defaults( $page_id ) {
        $page_id = absint( $page_id );
        if ( $page_id <= 0 ) {
            return;
        }

        $existing = function_exists( 'developer_starter_get_post_page_visual_style' )
            ? developer_starter_get_post_page_visual_style( $page_id )
            : get_post_meta( $page_id, '_qiling_page_visual_style', true );
        if ( is_array( $existing ) && ! empty( $existing['preset'] ) && 'technology_company' !== (string) $existing['preset'] ) {
            return;
        }

        if ( function_exists( 'developer_starter_persist_post_page_visual_style' ) ) {
            developer_starter_persist_post_page_visual_style(
                $page_id,
                array(
                    'mode'   => 'custom',
                    'preset' => 'tech_canvas',
                )
            );
        }
        if ( function_exists( 'developer_starter_persist_post_footer_visual_settings' ) ) {
            developer_starter_persist_post_footer_visual_settings(
                $page_id,
                array(
                    'mode'                => 'page_skin',
                    'wave'                => 'on',
                    'preset'              => 'tech_canvas',
                    'inherit_skin_colors' => true,
                )
            );
        }
    }

    /**
     * 获取默认模块
     *
     * 优先直接读取启灵官方首页 JSON 页面包（SSOT 单一事实源），避免代码硬编码冗余与双轨漂移。
     *
     * @param int $page_id 页面ID
     * @return array
     */
    private function get_default_modules( $page_id ) {
        unset( $page_id );

        if ( class_exists( '\Developer_Starter\Core\Official_Template_Package_Service' ) ) {
            $service = new Official_Template_Package_Service();
            $package = $service->load_package_for_template( 'templates/template-home.php' );
            if ( ! is_wp_error( $package ) && ! empty( $package['modules'] ) && is_array( $package['modules'] ) ) {
                return $package['modules'];
            }
        }

        return array();
    }

    /**
     * 设置为静态首页
     *
     * @param int $page_id 页面ID
     */
    private function set_as_frontpage( $page_id ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_id );
    }

    /**
     * 显示管理后台通知
     */
    public function show_admin_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $notice_type = get_transient( 'developer_starter_homepage_notice' );
        
        if ( ! $notice_type ) {
            return;
        }

        $dismiss_url = wp_nonce_url(
            add_query_arg( 'developer_starter_dismiss_notice', '1' ),
            'developer_starter_dismiss_notice_action',
            '_ds_notice_nonce'
        );
        $page_id = get_option( 'page_on_front' );

        if ( $notice_type === 'created' ) {
            $message = sprintf(
                __( '🎉 <strong>启灵主题</strong> 已自动为您创建了模块化首页！<a href="%s">编辑首页</a> | <a href="%s">查看网站</a>', 'developer-starter' ),
                admin_url( 'post.php?post=' . $page_id . '&action=edit' ),
                home_url( '/' )
            );
        } else {
            $message = sprintf(
                __( '✅ <strong>启灵主题</strong> 已将现有首页设置为网站主页！<a href="%s">编辑首页</a> | <a href="%s">查看网站</a>', 'developer-starter' ),
                admin_url( 'post.php?post=' . $page_id . '&action=edit' ),
                home_url( '/' )
            );
        }

        echo '<div class="notice notice-success is-dismissible" style="padding: 12px 15px;">';
        echo wp_kses_post( $message );
        echo ' <a href="' . esc_url( $dismiss_url ) . '" style="margin-left: 15px; color: #666;">' . __( '不再显示', 'developer-starter' ) . '</a>';
        echo '</div>';
    }

    /**
     * 处理通知关闭
     */
    public function dismiss_notice() {
        $dismiss_flag = isset( $_GET['developer_starter_dismiss_notice'] )
            ? sanitize_text_field( wp_unslash( (string) $_GET['developer_starter_dismiss_notice'] ) )
            : '';
        if ( '1' !== $dismiss_flag ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $nonce = isset( $_GET['_ds_notice_nonce'] )
            ? sanitize_text_field( wp_unslash( (string) $_GET['_ds_notice_nonce'] ) )
            : '';
        if ( ! wp_verify_nonce( $nonce, 'developer_starter_dismiss_notice_action' ) ) {
            return;
        }

        delete_transient( 'developer_starter_homepage_notice' );
        
        // 重定向回当前页面
        wp_safe_redirect( remove_query_arg( array( 'developer_starter_dismiss_notice', '_ds_notice_nonce' ) ) );
        exit;
    }
}
