<?php
/**
 * Meta Boxes - Page Modules Builder
 * 
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

use Developer_Starter\Core\Builder_Data_Service;
use Developer_Starter\Core\Single_Page_Package_Service;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes {
    private $module_fields = array();
    private $builder_data_service = null;
    private $single_page_package_service = null;
    private $module_renderer = null;
    private $editor_service = null;
    private $post_settings_service = null;
    private $modules_save_service = null;
    private $modules_view_service = null;

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        // Template System AJAX
        add_action( 'wp_ajax_qiling_load_template_html', array( $this, 'ajax_load_template_html' ) );
        add_action( 'wp_ajax_qiling_render_module_item', array( $this, 'ajax_render_module_item' ) );
        add_action( 'wp_ajax_qiling_load_modules_editor_ui', array( $this, 'ajax_load_modules_editor_ui' ) );
        add_action( 'wp_ajax_qiling_save_modules_editor_state', array( $this, 'ajax_save_modules_editor_state' ) );
        add_action( 'wp_ajax_qiling_import_page_json_preview', array( $this, 'ajax_import_page_json_preview' ) );
        add_action( 'wp_ajax_qiling_export_page_json', array( $this, 'ajax_export_page_json' ) );
    }

    /**
     * 获取共用 builder 数据服务。
     *
     * @return Builder_Data_Service
     */
    private function get_builder_data_service() {
        if ( null === $this->builder_data_service ) {
            $this->builder_data_service = new Builder_Data_Service();
        }

        return $this->builder_data_service;
    }

    /**
     * 获取单页页面包服务。
     *
     * @return Single_Page_Package_Service
     */
    private function get_single_page_package_service() {
        if ( null === $this->single_page_package_service ) {
            $this->single_page_package_service = new Single_Page_Package_Service();
        }

        return $this->single_page_package_service;
    }

    /**
     * 获取模块表单渲染器。
     *
     * @return Meta_Boxes_Module_Renderer
     */
    private function get_module_renderer() {
        if ( null === $this->module_renderer ) {
            $this->module_renderer = new Meta_Boxes_Module_Renderer();
        }

        return $this->module_renderer;
    }

    /**
     * 获取后台模块编辑器服务。
     *
     * @return Meta_Boxes_Editor_Service
     */
    private function get_editor_service() {
        if ( null === $this->editor_service ) {
            $this->editor_service = new Meta_Boxes_Editor_Service();
        }

        return $this->editor_service;
    }

    /**
     * 获取文章/页面设置 Meta Box 服务。
     *
     * @return Meta_Boxes_Post_Settings_Service
     */
    private function get_post_settings_service() {
        if ( null === $this->post_settings_service ) {
            $this->post_settings_service = new Meta_Boxes_Post_Settings_Service();
        }

        return $this->post_settings_service;
    }

    /**
     * 获取模块保存服务。
     *
     * @return Meta_Boxes_Modules_Save_Service
     */
    private function get_modules_save_service() {
        if ( null === $this->modules_save_service ) {
            $this->modules_save_service = new Meta_Boxes_Modules_Save_Service();
        }

        return $this->modules_save_service;
    }

    /**
     * 获取模块编辑器视图服务。
     *
     * @return Meta_Boxes_Modules_View_Service
     */
    private function get_modules_view_service() {
        if ( null === $this->modules_view_service ) {
            $this->modules_view_service = new Meta_Boxes_Modules_View_Service();
        }

        return $this->modules_view_service;
    }

    /**
     * 模块字段缓存键
     *
     * @return string
     */
    private function get_module_fields_cache_key() {
        $version = defined( 'DEVELOPER_STARTER_VERSION' ) ? (string) DEVELOPER_STARTER_VERSION : '1.0.0';
        return 'developer_starter_module_fields_' . md5( $version . '|' . get_locale() );
    }

    /**
     * 校验模块字段缓存是否完整。
     *
     * 目的：避免旧缓存/异常缓存导致“部分预设模板模块看起来不存在”。
     *
     * @param mixed $fields 缓存内容。
     * @return bool
     */
    private function is_module_fields_cache_valid( $fields ) {
        if ( ! is_array( $fields ) || empty( $fields ) ) {
            return false;
        }

        if ( class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
            $module_catalog = method_exists( $module_manager, 'get_module_catalog' )
                ? $module_manager->get_module_catalog( true )
                : array();

            if ( is_array( $module_catalog ) && ! empty( $module_catalog ) ) {
                foreach ( $module_catalog as $module_entry ) {
                    if ( ! is_array( $module_entry ) || empty( $module_entry['id'] ) ) {
                        continue;
                    }

                    $module_id = sanitize_key( (string) $module_entry['id'] );
                    if ( '' !== $module_id && ! isset( $fields[ $module_id ] ) ) {
                        return false;
                    }
                }
            }
        }

        $required_module_ids = array(
            'banner',
            'services',
            'stats',
            'contact',
            'product_showcase',
            'resume_hero',
            'menu',
            'booking-entry',
            'visual_tabs',
            'tabbed_carousel',
            'brand_banner_pro',
            'resource_hero_pro',
            'footer_suite',
        );

        foreach ( $required_module_ids as $module_id ) {
            if (
                ! isset( $fields[ $module_id ] )
                || ! is_array( $fields[ $module_id ] )
                || empty( $fields[ $module_id ]['fields'] )
            ) {
                return false;
            }
        }

        // 品牌旗舰 Banner 字段版本校验：用于清理旧缓存中的废弃字段/缺失字段
        if ( isset( $fields['brand_banner_pro']['fields'] ) && is_array( $fields['brand_banner_pro']['fields'] ) ) {
            $brand_field_ids = array();
            foreach ( $fields['brand_banner_pro']['fields'] as $field ) {
                if ( is_array( $field ) && ! empty( $field['id'] ) ) {
                    $brand_field_ids[] = (string) $field['id'];
                }
            }

            $brand_required_fields = array(
                'bb_title_mode',
                'bb_title_size_max',
                'bb_title_image_max_width',
            );

            foreach ( $brand_required_fields as $field_id ) {
                if ( ! in_array( $field_id, $brand_field_ids, true ) ) {
                    return false;
                }
            }

            if ( in_array( 'bb_badge', $brand_field_ids, true ) ) {
                return false;
            }
        }

        // 动态 Banner 字段版本校验：新增内容字段后自动清理对象缓存和 Transient。
        if ( isset( $fields['dynamic_banner']['fields'] ) && is_array( $fields['dynamic_banner']['fields'] ) ) {
            $dynamic_banner_field_ids = array();
            foreach ( $fields['dynamic_banner']['fields'] as $field ) {
                if ( is_array( $field ) && ! empty( $field['id'] ) ) {
                    $dynamic_banner_field_ids[] = (string) $field['id'];
                }
            }

            foreach ( array( 'db_subtitle', 'db_text_color', 'db_desc', 'db_desc_color' ) as $field_id ) {
                if ( ! in_array( $field_id, $dynamic_banner_field_ids, true ) ) {
                    return false;
                }
            }
        }

        // 博客模块字段版本校验：确保“资源卡风格”字段已进入缓存。
        if ( isset( $fields['blog']['fields'] ) && is_array( $fields['blog']['fields'] ) ) {
            $blog_field_ids = array();
            foreach ( $fields['blog']['fields'] as $field ) {
                if ( is_array( $field ) && ! empty( $field['id'] ) ) {
                    $blog_field_ids[] = (string) $field['id'];
                }
            }

            if ( ! in_array( 'blog_resource_skin', $blog_field_ids, true ) ) {
                return false;
            }
        }

        // 双栏轮播文章来源字段上线后，清理仍缺少新字段的 24 小时缓存。
        if ( ! isset( $fields['double_column_carousel']['fields'] ) || ! is_array( $fields['double_column_carousel']['fields'] ) ) {
            return false;
        }
        $double_carousel_field_ids = array();
        foreach ( $fields['double_column_carousel']['fields'] as $field ) {
            if ( is_array( $field ) && ! empty( $field['id'] ) ) {
                $double_carousel_field_ids[] = (string) $field['id'];
            }
        }
        foreach ( array( 'dcc_slide_source', 'dcc_post_count', 'dcc_exclude_categories', 'dcc_post_ids' ) as $field_id ) {
            if ( ! in_array( $field_id, $double_carousel_field_ids, true ) ) {
                return false;
            }
        }

        // 图片视频搜索模块字段版本校验：宽度模式上线后清理旧的 24 小时字段缓存。
        if ( ! isset( $fields['hero_search']['fields'] ) || ! is_array( $fields['hero_search']['fields'] ) ) {
            return false;
        }
        $hero_search_field_ids = array();
        foreach ( $fields['hero_search']['fields'] as $field ) {
            if ( is_array( $field ) && ! empty( $field['id'] ) ) {
                $hero_search_field_ids[] = (string) $field['id'];
            }
        }
        foreach ( array( 'hs_width_mode', 'hs_custom_width', 'hs_border_radius', 'hs_custom_radius' ) as $field_id ) {
            if ( ! in_array( $field_id, $hero_search_field_ids, true ) ) {
                return false;
            }
        }

        // 通用推荐模块已简化为普通文章分类 ID，清理仍暴露旧通用查询字段的缓存。
        if ( ! isset( $fields['qiling_universal_recommend']['fields'] ) || ! is_array( $fields['qiling_universal_recommend']['fields'] ) ) {
            return false;
        }
        $recommend_field_ids = array();
        foreach ( $fields['qiling_universal_recommend']['fields'] as $field ) {
            if ( is_array( $field ) && ! empty( $field['id'] ) ) {
                $recommend_field_ids[] = (string) $field['id'];
            }
        }
        if ( ! in_array( 'qur_category_ids', $recommend_field_ids, true ) ) {
            return false;
        }
        foreach ( array( 'qur_auto_post_type', 'qur_auto_taxonomy', 'qur_auto_terms', 'qur_auto_orderby', 'qur_auto_order', 'qur_auto_exclude_ids' ) as $legacy_field_id ) {
            if ( in_array( $legacy_field_id, $recommend_field_ids, true ) ) {
                return false;
            }
        }

        return true;
    }

    private function init_module_fields() {
        if ( ! empty( $this->module_fields ) ) {
            return;
        }

        $cache_key = $this->get_module_fields_cache_key();
        $cache_group = 'developer_starter';

        $cached_fields = wp_cache_get( $cache_key, $cache_group );
        if ( $this->is_module_fields_cache_valid( $cached_fields ) ) {
            $this->module_fields = $cached_fields;
            return;
        } elseif ( false !== $cached_fields ) {
            wp_cache_delete( $cache_key, $cache_group );
            delete_transient( $cache_key );
        }

        $cached_fields = get_transient( $cache_key );
        if ( $this->is_module_fields_cache_valid( $cached_fields ) ) {
            $this->module_fields = $cached_fields;
            wp_cache_set( $cache_key, $cached_fields, $cache_group, DAY_IN_SECONDS );
            return;
        } elseif ( false !== $cached_fields ) {
            wp_cache_delete( $cache_key, $cache_group );
            delete_transient( $cache_key );
        }

        // 确保 Module_Manager 类存在
        if ( ! class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            return;
        }

        $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
        $modules = $module_manager->get_all_modules();
        
        foreach ( $modules as $module_id => $module ) {
             if ( ! is_object( $module ) ) continue; // Safety check
             
             $this->module_fields[ $module_id ] = array(
                'title'  => $module->get_name(),
                'fields' => $module->get_fields(),
            );
        }

        if ( ! empty( $this->module_fields ) ) {
            wp_cache_set( $cache_key, $this->module_fields, $cache_group, DAY_IN_SECONDS );
            set_transient( $cache_key, $this->module_fields, DAY_IN_SECONDS );
        }
    }

    /**
     * 获取当前应在添加模块工具栏中显示的模块字段。
     *
     * @param array<string,array<string,mixed>> $module_fields 模块字段。
     * @return array<string,array<string,mixed>>
     */
    private function get_visible_module_fields_for_toolbar( $module_fields ) {
        if ( ! class_exists( '\Developer_Starter\Modules\Module_Manager' ) || ! is_array( $module_fields ) ) {
            return is_array( $module_fields ) ? $module_fields : array();
        }

        $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
        if ( ! method_exists( $module_manager, 'is_module_visible_in_catalog' ) ) {
            return $module_fields;
        }

        $visible_fields = array();
        foreach ( $module_fields as $module_id => $field_config ) {
            if ( $module_manager->is_module_visible_in_catalog( (string) $module_id ) ) {
                $visible_fields[ $module_id ] = $field_config;
            }
        }

        return $visible_fields;
    }

    /**
     * 当前环境是否启用了启灵积分商城前台装修模块。
     *
     * @return bool
     */
    private function is_qilingshop_builder_available() {
        if ( class_exists( '\QLS_FrontendBuilder_Registrar' ) ) {
            return true;
        }

        if ( function_exists( 'qls_shop_public' ) || class_exists( '\QLS_Shop_Public' ) ) {
            return true;
        }

        return defined( 'QILINGSHOP_PATH' );
    }

    /**
     * 当前页面是否为启灵积分商城页面。
     *
     * @param \WP_Post|int|null $post 页面对象或 ID。
     * @return bool
     */
    private function is_qilingshop_builder_page( $post ) {
        if ( is_numeric( $post ) ) {
            $post = get_post( absint( $post ) );
        }

        if ( ! ( $post instanceof \WP_Post ) || 'page' !== $post->post_type ) {
            return false;
        }

        if ( ! $this->is_qilingshop_builder_available() ) {
            return false;
        }

        return strpos( (string) $post->post_content, '[qls_shop' ) !== false;
    }

    /**
     * 当前页面是否支持装修生成。
     *
     * @param \WP_Post|int|null $post 页面对象或 ID。
     * @return bool
     */
    private function is_ai_builder_supported_for_post( $post ) {
        return ! $this->is_qilingshop_builder_page( $post );
    }

    public function enqueue_scripts( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
            return;
        }
        if ( ! function_exists( 'get_current_screen' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || ! isset( $screen->post_type ) || $screen->post_type !== 'page' ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-sortable' );

        $ai_service_js_file = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/js/ai-builder-service.js';
        $ai_service_js_ver  = file_exists( $ai_service_js_file ) ? (string) filemtime( $ai_service_js_file ) : DEVELOPER_STARTER_VERSION;

        wp_enqueue_script(
            'qiling-ai-builder-service',
            DEVELOPER_STARTER_ASSETS . '/js/ai-builder-service.js',
            array( 'jquery' ),
            $ai_service_js_ver,
            false
        );
    }

    public function add_meta_boxes( $post_type = '', $post = null ) {
        add_meta_box(
            'developer_starter_modules',
            __( '页面模块配置', 'developer-starter' ),
            array( $this, 'render_modules_meta_box' ),
            'page',
            'normal',
            'high'
        );

        add_meta_box(
            'developer_starter_seo',
            __( 'SEO设置', 'developer-starter' ),
            array( $this->get_post_settings_service(), 'render_seo_meta_box' ),
            array( 'post', 'page', 'qilingdoc_doc' ),
            'normal',
            'default'
        );

        // 页面头部设置 - 仅对页面生效
        add_meta_box(
            'qiling_page_header_settings',
            __( '页面头部设置', 'developer-starter' ),
            array( $this->get_post_settings_service(), 'render_page_header_meta_box' ),
            'page',
            'side',
            'default'
        );

        add_meta_box(
            'qiling_page_visual_style_settings',
            __( '页面视觉风格', 'developer-starter' ),
            array( $this->get_post_settings_service(), 'render_page_visual_style_meta_box' ),
            'page',
            'normal',
            'default'
        );

        $featured_image_screens = array( 'post' );
        if ( post_type_exists( 'product' ) ) {
            $featured_image_screens[] = 'product';
        }
        add_meta_box(
            'developer_starter_featured_image_url',
            __( '特色图URL', 'developer-starter' ),
            array( $this->get_post_settings_service(), 'render_featured_image_url_meta_box' ),
            $featured_image_screens,
            'side',
            'default'
        );

        // 文章相册模式设置
        add_meta_box(
            'qiling_gallery_mode_settings',
            __( '文章相册模式', 'developer-starter' ),
            array( $this->get_post_settings_service(), 'render_gallery_mode_meta_box' ),
            'post',
            'side',
            'default'
        );

        // 文章布局设置
        add_meta_box(
            'qiling_post_layout_settings',
            __( '文章布局设置', 'developer-starter' ),
            array( $this->get_post_settings_service(), 'render_post_layout_meta_box' ),
            'post',
            'side',
            'default'
        );

        add_meta_box(
            'qiling_post_comments_settings',
            __( '评论设置', 'developer-starter' ),
            array( $this, 'render_post_comments_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render the per-post comment visibility switch.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    public function render_post_comments_meta_box( $post ) {
        wp_nonce_field( 'qiling_post_comments_settings', 'qiling_post_comments_nonce' );
        $setting = get_post_meta( $post->ID, '_qiling_comments_enabled', true );
        $global_closed = 'closed' === get_option( 'default_comment_status', 'open' );
        ?>
        <p>
            <label>
                <input type="checkbox" name="qiling_comments_enabled" value="1" <?php checked( '1', (string) $setting ); ?> />
                <?php esc_html_e( '允许此文章显示并接收评论', 'developer-starter' ); ?>
            </label>
        </p>
        <p class="description">
            <?php echo esc_html( $global_closed ? __( '全站默认已关闭评论；勾选后仅此文章开启评论。', 'developer-starter' ) : __( '未勾选时沿用 WordPress 的文章评论状态。', 'developer-starter' ) ); ?>
        </p>
        <?php
    }

    public function render_modules_meta_box( $post ) {
        wp_nonce_field( 'developer_starter_modules_nonce', 'modules_nonce' );
        echo '<input type="hidden" id="developer-starter-modules-ui-loaded" name="developer_starter_modules_ui_loaded" value="0" />';
        echo '<input type="hidden" id="developer-starter-modules-payload" name="developer_starter_modules_payload" value="" />';
        echo '<input type="hidden" id="developer-starter-page-package-imported" name="developer_starter_page_package_imported" value="0" />';
        echo '<input type="hidden" id="developer-starter-page-package-template" name="developer_starter_page_package_template" value="" />';
        echo '<input type="hidden" id="developer-starter-page-package-hide-header" name="developer_starter_page_package_hide_header" value="" />';
        echo '<input type="hidden" id="developer-starter-page-package-hide-header-defined" name="developer_starter_page_package_hide_header_defined" value="0" />';
        echo '<input type="hidden" id="developer-starter-page-package-transparent-header" name="developer_starter_page_package_transparent_header" value="" />';
        echo '<input type="hidden" id="developer-starter-page-package-design" name="developer_starter_page_package_design" value="" />';
        echo '<input type="hidden" id="developer-starter-page-package-footer" name="developer_starter_page_package_footer" value="" />';
        echo '<input type="hidden" id="developer-starter-page-package-region-decoration" name="developer_starter_page_package_region_decoration" value="" />';
        echo '<input type="hidden" id="developer-starter-page-package-visual-style" name="developer_starter_page_package_visual_style" value="" />';
        echo '<input type="file" id="developer-starter-page-json-file" accept=".json,application/json" style="display:none;" />';

        $enable_scroll_reveal = get_post_meta( $post->ID, '_developer_starter_enable_scroll_reveal', true );
        $this->init_module_fields();
        $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
        $module_catalog = method_exists( $module_manager, 'get_module_catalog' )
            ? $module_manager->get_module_catalog( true )
            : array();
        $available_module_count = is_array( $module_catalog ) && ! empty( $module_catalog )
            ? count( $module_catalog )
            : ( ! empty( $this->module_fields ) && is_array( $this->module_fields ) ? count( $this->module_fields ) : 0 );
        $ai_builder_config = array(
            'enabled' => false,
        );
        if ( current_user_can( 'manage_options' ) && class_exists( '\\Developer_Starter\\Core\\AI_Decorator' ) ) {
            $ai_builder_config = \Developer_Starter\Core\AI_Decorator::get_instance()->get_client_config( $post->ID );
        }
        $ai_builder_available = ! empty( $ai_builder_config['enabled'] );
        $ai_builder_supported = $this->is_ai_builder_supported_for_post( $post );
        $ai_max_modules = isset( $ai_builder_config['defaultMaxModules'] )
            ? max( 1, min( 10, absint( $ai_builder_config['defaultMaxModules'] ) ) )
            : 10;
        if ( ! $ai_builder_supported ) {
            $ai_builder_config['enabled'] = false;
        }

        $ai_module_groups = array();
        foreach ( $module_catalog as $module_entry ) {
            if ( ! is_array( $module_entry ) || empty( $module_entry['id'] ) ) {
                continue;
            }

            $module_type = (string) $module_entry['id'];
            if ( ! isset( $this->module_fields[ $module_type ] ) || empty( $module_entry['aiEnabled'] ) ) {
                continue;
            }

            $group_key = ! empty( $module_entry['group'] ) ? sanitize_key( (string) $module_entry['group'] ) : 'general';
            if ( ! isset( $ai_module_groups[ $group_key ] ) ) {
                $ai_module_groups[ $group_key ] = array(
                    'key'   => $group_key,
                    'label' => isset( $module_entry['groupLabel'] ) ? (string) $module_entry['groupLabel'] : ucfirst( $group_key ),
                    'items' => array(),
                );
            }

            $keywords = isset( $module_entry['keywords'] ) && is_array( $module_entry['keywords'] )
                ? $module_entry['keywords']
                : array();

            $ai_module_groups[ $group_key ]['items'][] = array(
                'id'        => $module_type,
                'title'     => isset( $module_entry['name'] ) ? (string) $module_entry['name'] : $module_type,
                'group'     => $group_key,
                'groupLabel'=> $ai_module_groups[ $group_key ]['label'],
                'keywords'  => $keywords,
            );
        }

        $this->get_modules_view_service()->render(
            array(
                'post' => $post,
                'enable_scroll_reveal' => $enable_scroll_reveal,
                'available_module_count' => $available_module_count,
                'ai_builder_config' => $ai_builder_config,
                'ai_builder_available' => $ai_builder_available,
                'ai_builder_supported' => $ai_builder_supported,
                'ai_max_modules' => $ai_max_modules,
                'ai_module_groups' => array_values( $ai_module_groups ),
                'default_page_package_template' => $this->get_default_page_package_template(),
            )
        );
    }

    public function ajax_load_template_html() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );
        
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        
        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $idx = isset( $_POST['idx'] ) ? intval( $_POST['idx'] ) : 9999;
        
        $post = class_exists( '\Developer_Starter\Core\Template_Manager' )
            ? \Developer_Starter\Core\Template_Manager::get_template_post( $id )
            : get_post( $id );
        if ( ! $post || $post->post_type !== 'ql_module_template' ) {
            wp_send_json_error( __( '无此模版', 'developer-starter' ) );
        }

        if (
            class_exists( '\Developer_Starter\Core\Template_Manager' )
            && ! \Developer_Starter\Core\Template_Manager::current_user_can_access_template_post( $post, 'read_post' )
        ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }
        
        $json = $post->post_content;
        $data = json_decode( $json, true );
        $type = get_post_meta( $id, '_ql_template_type', true );
        
        // Ensure fields are loaded
        $this->init_module_fields();

        $html = $this->get_editor_service()->render_module_item_html(
            $idx,
            $type,
            is_array( $data ) ? $data : array(),
            $this->module_fields,
            $this->get_module_renderer(),
            false
        );
        if ( is_wp_error( $html ) ) {
            wp_send_json_error( $html->get_error_message() );
        }

        wp_send_json_success( $html );
    }

    public function ajax_render_module_item() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_pages' ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
        $idx = isset( $_POST['idx'] ) ? absint( $_POST['idx'] ) : 0;

        if ( '' === $type ) {
            wp_send_json_error( __( '模块类型无效', 'developer-starter' ) );
        }

        $this->init_module_fields();
        if ( ! isset( $this->module_fields[ $type ] ) ) {
            wp_send_json_error( __( '模块不存在或未注册', 'developer-starter' ) );
        }
        if ( class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
            if ( method_exists( $module_manager, 'is_module_visible_in_catalog' ) && ! $module_manager->is_module_visible_in_catalog( $type ) ) {
                wp_send_json_error( __( '模块未启用', 'developer-starter' ) );
            }
        }

        $html = $this->get_editor_service()->render_module_item_html(
            $idx,
            $type,
            array(),
            $this->module_fields,
            $this->get_module_renderer(),
            true
        );
        if ( is_wp_error( $html ) ) {
            wp_send_json_error( $html->get_error_message() );
        }

        wp_send_json_success( $html );
    }

    public function ajax_load_modules_editor_ui() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_pages' ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( $post_id <= 0 ) {
            wp_send_json_error( __( '页面参数无效', 'developer-starter' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'page' ) {
            wp_send_json_error( __( '仅支持页面类型', 'developer-starter' ) );
        }

        $this->init_module_fields();
        if ( empty( $this->module_fields ) ) {
            wp_send_json_error( __( '模块系统未初始化成功', 'developer-starter' ) );
        }

        $ai_builder_globally_available = current_user_can( 'manage_options' )
            && class_exists( '\Developer_Starter\Core\AI_Decorator' )
            && \Developer_Starter\Core\AI_Decorator::get_instance()->is_enabled()
            && ! empty( \Developer_Starter\Core\AI_Decorator::get_instance()->get_client_config( $post_id )['enabled'] );
        $ai_builder_supported = $this->is_ai_builder_supported_for_post( $post );
        $ai_builder_available = $ai_builder_globally_available && $ai_builder_supported;
        $toolbar_module_fields = $this->get_visible_module_fields_for_toolbar( $this->module_fields );
        $toolbar_html = $this->get_editor_service()->render_toolbar_html(
            $toolbar_module_fields,
            array(
                'ai_builder_available'          => $ai_builder_available,
                'ai_builder_globally_available' => $ai_builder_globally_available,
                'ai_builder_supported'          => $ai_builder_supported,
            )
        );

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );
        if ( empty( $modules ) && function_exists( 'developer_starter_maybe_fill_default_modules_for_page_template' ) ) {
            $template = function_exists( 'get_page_template_slug' ) ? get_page_template_slug( $post_id ) : '';
            if ( ! is_string( $template ) || '' === trim( $template ) ) {
                $template = get_post_meta( $post_id, '_wp_page_template', true );
            }
            if ( is_string( $template ) && '' !== $template && 'default' !== $template ) {
                developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template );
                $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
                    ? developer_starter_get_raw_page_modules_meta( $post_id )
                    : get_post_meta( $post_id, '_developer_starter_modules', true );
            }
        }
        $modules = is_array( $modules ) ? $modules : array();
        $modules = $this->normalize_modules_for_editor( $modules );
        $list_payload = $this->get_editor_service()->render_modules_list_payload(
            $modules,
            $this->module_fields,
            $this->get_module_renderer()
        );

        wp_send_json_success(
            array(
                'toolbar'     => $toolbar_html,
                'list'        => $list_payload['html'],
                'moduleCount' => $list_payload['moduleCount'],
            )
        );
    }

    public function ajax_import_page_json_preview() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_pages' ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $raw_json = isset( $_POST['json'] ) ? wp_unslash( $_POST['json'] ) : '';
        $package  = $this->parse_page_json_package( $raw_json );

        if ( is_wp_error( $package ) ) {
            wp_send_json_error( $package->get_error_message() );
        }

        $this->init_module_fields();
        if ( empty( $this->module_fields ) ) {
            wp_send_json_error( __( '模块系统未初始化成功', 'developer-starter' ) );
        }
        $preview_payload = $this->get_editor_service()->build_import_preview_payload(
            $package,
            $this->module_fields,
            $this->get_module_renderer(),
            array( $this, 'get_page_template_label' )
        );
        if ( is_wp_error( $preview_payload ) ) {
            wp_send_json_error( $preview_payload->get_error_message() );
        }

        wp_send_json_success(
            $preview_payload
        );
    }

    public function ajax_export_page_json() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( $post_id <= 0 ) {
            wp_send_json_error( __( '页面参数无效', 'developer-starter' ) );
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $payload = $this->build_page_json_export_payload( $post_id );
        if ( is_wp_error( $payload ) ) {
            wp_send_json_error( $payload->get_error_message() );
        }
        $response_payload = $this->get_editor_service()->build_export_payload_response( $payload, $post_id );
        if ( is_wp_error( $response_payload ) ) {
            wp_send_json_error( $response_payload->get_error_message() );
        }

        wp_send_json_success( $response_payload );
    }

    public function ajax_save_modules_editor_state() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 ) {
            wp_send_json_error( __( '页面参数无效', 'developer-starter' ) );
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $raw_payload = isset( $_POST['modules'] ) ? wp_unslash( (string) $_POST['modules'] ) : '';
        $modules = $this->normalize_modules_payload_for_storage( $raw_payload );

        $modules = apply_filters( 'developer_starter_modules_before_save', $modules, $post_id, $_POST );
        update_post_meta( $post_id, '_developer_starter_modules', $modules );
        do_action( 'developer_starter_modules_saved', $post_id, $modules );

        $enable_scroll_reveal = isset( $_POST['enable_scroll_reveal'] ) && '1' === wp_unslash( (string) $_POST['enable_scroll_reveal'] ) ? '1' : '0';
        update_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', $enable_scroll_reveal );

        wp_send_json_success(
            array(
                'moduleCount' => count( $modules ),
            )
        );
    }

    private function get_default_page_package_template() {
        return $this->get_single_page_package_service()->get_default_page_template();
    }

    private function normalize_page_package_template( $template ) {
        return $this->get_single_page_package_service()->normalize_page_template( $template );
    }

    public function get_page_template_label( $template ) {
        return $this->get_single_page_package_service()->get_page_template_label( $template );
    }

    private function normalize_page_package_bool( $value, $default = false ) {
        return $this->get_single_page_package_service()->normalize_bool( $value, $default );
    }

    private function parse_page_json_package( $raw_json ) {
        return $this->get_single_page_package_service()->parse_package( $raw_json );
    }

    private function build_page_json_export_payload( $post_id ) {
        return $this->get_single_page_package_service()->build_export_payload( $post_id );
    }

    /**
     * 将旧页面模块数据整理成后台编辑器可渲染的标准结构。
     *
     * @param mixed $modules 模块数组。
     * @return array<int,array<string,mixed>>
     */
    private function normalize_modules_for_editor( $modules ) {
        if ( ! is_array( $modules ) ) {
            return array();
        }

        $this->init_module_fields();

        return $this->get_builder_data_service()->normalize_modules_for_storage(
            $modules,
            array(
                'sanitize_data' => false,
                'module_exists_callback' => array( $this, 'has_registered_module_type' ),
                'schema_for_type_callback' => array( $this, 'get_module_data_schema_by_type' ),
            )
        );
    }

    /**
     * 将模块 JSON payload 转成可保存结构。
     *
     * @param string $raw_payload JSON 字符串。
     * @return array<int,array<string,mixed>>
     */
    private function normalize_modules_payload_for_storage( $raw_payload ) {
        $modules = json_decode( (string) $raw_payload, true );
        if ( ! is_array( $modules ) ) {
            return array();
        }

        $this->init_module_fields();

        return $this->get_builder_data_service()->normalize_modules_for_storage(
            $modules,
            array(
                'sanitize_data' => true,
                'module_exists_callback' => array( $this, 'has_registered_module_type' ),
                'schema_for_type_callback' => array( $this, 'get_module_data_schema_by_type' ),
            )
        );
    }

    // fix_newlines_recursive removed

    public function save_meta_boxes( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( 'post' === get_post_type( $post_id ) && isset( $_POST['qiling_post_comments_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qiling_post_comments_nonce'] ) ), 'qiling_post_comments_settings' ) ) {
            if ( isset( $_POST['qiling_comments_enabled'] ) && '1' === wp_unslash( (string) $_POST['qiling_comments_enabled'] ) ) {
                update_post_meta( $post_id, '_qiling_comments_enabled', '1' );
            } else {
                update_post_meta( $post_id, '_qiling_comments_enabled', '0' );
            }
        }

        $this->get_modules_save_service()->handle_modules_save(
            $post_id,
            $_POST,
            $_REQUEST,
            array(
                'normalize_modules_callback' => function( $raw_modules ) {
                    $this->init_module_fields();

                    return $this->get_builder_data_service()->normalize_modules_for_storage(
                        $raw_modules,
                        array(
                            'sanitize_data' => true,
                            'module_exists_callback' => array( $this, 'has_registered_module_type' ),
                            'schema_for_type_callback' => array( $this, 'get_module_data_schema_by_type' ),
                        )
                    );
                },
                'normalize_modules_payload_callback' => function( $raw_payload ) {
                    return $this->normalize_modules_payload_for_storage( $raw_payload );
                },
            )
        );

        $this->get_modules_save_service()->persist_imported_page_package_settings(
            $post_id,
            $_POST,
            array(
                'normalize_page_package_template_callback' => array( $this, 'normalize_page_package_template' ),
                'default_page_package_template_callback' => array( $this, 'get_default_page_package_template' ),
                'normalize_page_package_bool_callback' => array( $this, 'normalize_page_package_bool' ),
            )
        );

        $this->get_post_settings_service()->save_post_meta_boxes( $post_id, $_POST );

        // 模板页面包最后应用，避免旧页面设置覆盖新模板的模块和视觉配置。
        $this->get_modules_save_service()->maybe_replace_modules_from_selected_template( $post_id, $_POST, $_REQUEST );
    }

    /**
     * 获取指定模块的数据字段 schema（仅包含 id/type/repeater 子字段）。
     *
     * @param string $type 模块类型。
     * @return array<string,array<string,mixed>>
     */
    private function get_module_data_schema_by_type( $type ) {
        $type = sanitize_text_field( (string) $type );
        if ( '' === $type || empty( $this->module_fields[ $type ] ) || empty( $this->module_fields[ $type ]['fields'] ) || ! is_array( $this->module_fields[ $type ]['fields'] ) ) {
            return array();
        }

        return $this->get_builder_data_service()->build_module_data_schema_map( $this->module_fields[ $type ]['fields'] );
    }

    /**
     * 检查模块类型是否已注册。
     *
     * @param string $type 模块类型。
     * @return bool
     */
    private function has_registered_module_type( $type ) {
        $type = sanitize_key( (string) $type );
        return '' !== $type && isset( $this->module_fields[ $type ] );
    }

}
