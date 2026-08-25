<?php
/**
 * Frontend Builder - 前台页面模块可视化编辑
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Manager;
use WP_Admin_Bar;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Frontend_Builder {

    const NONCE_ACTION = 'qiling_frontend_builder_nonce';
    const MAX_BUILDER_PAYLOAD_BYTES = 1048576;
    const MAX_BUILDER_MODULES = 60;
    const MAX_BUILDER_MODULE_DATA_BYTES = 131072;

    /**
     * @var Builder_Data_Service|null
     */
    private $builder_data_service = null;

    /**
     * @var Frontend_Builder_Assets_Service|null
     */
    private $assets_service = null;

    /**
     * @var Frontend_Builder_Library_Service|null
     */
    private $library_service = null;

    /**
     * @var Frontend_Builder_QilingShop_Service|null
     */
    private $qilingshop_service = null;

    /**
     * @var Frontend_Builder_Modules_Service|null
     */
    private $modules_service = null;

    /**
     * @var Frontend_Builder_Snapshot_Service|null
     */
    private $snapshot_service = null;

    public function __construct() {
        // no-cache 由 functions.php 的统一策略入口负责，避免多处重复定义/覆盖。
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
        add_filter( 'body_class', array( $this, 'add_builder_body_class' ) );
        add_filter( 'query_vars', array( $this, 'register_builder_query_vars' ) );
        add_filter( 'redirect_canonical', array( $this, 'disable_canonical_redirect_for_builder' ), 10, 2 );
        add_action( 'init', array( $this, 'disable_external_redirects_for_builder_request' ), 1000 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 200 );
        add_action( 'wp_footer', array( $this, 'render_builder_panel' ), 1000 );

        add_action( 'wp_ajax_qiling_frontend_builder_get_schema', array( $this, 'ajax_get_module_schema' ) );
        add_action( 'wp_ajax_qiling_frontend_builder_get_library_template', array( $this, 'ajax_get_library_template' ) );
        add_action( 'wp_ajax_qiling_frontend_builder_render_module_preview', array( $this, 'ajax_render_module_preview' ) );
        add_action( 'wp_ajax_qiling_frontend_builder_render_preview', array( $this, 'ajax_render_preview' ) );
        add_action( 'wp_ajax_qiling_frontend_builder_save_modules', array( $this, 'ajax_save_modules' ) );
        add_action( 'wp_ajax_qiling_frontend_builder_get_snapshots', array( $this, 'ajax_get_snapshots' ) );
        add_action( 'wp_ajax_qiling_frontend_builder_restore_snapshot', array( $this, 'ajax_restore_snapshot' ) );
    }

    /**
     * 注册前台装修查询参数，避免被 WordPress 规范化重定向剥离。
     *
     * @param array<int,string> $vars query vars
     * @return array<int,string>
     */
    public function register_builder_query_vars( $vars ) {
        if ( ! is_array( $vars ) ) {
            $vars = array();
        }

        if ( ! in_array( 'qiling_builder', $vars, true ) ) {
            $vars[] = 'qiling_builder';
        }

        return $vars;
    }

    /**
     * 前台装修模式下禁用 canonical 重定向，防止 ?qiling_builder=1 被 301 清理。
     *
     * @param string|false $redirect_url redirect url
     * @param string       $requested_url requested url
     * @return string|false
     */
    public function disable_canonical_redirect_for_builder( $redirect_url, $requested_url ) {
        unset( $requested_url );

        if ( is_admin() || wp_doing_ajax() ) {
            return $redirect_url;
        }

        $builder_flag = '';
        if ( isset( $_GET['qiling_builder'] ) ) {
            $builder_flag = sanitize_text_field( wp_unslash( (string) $_GET['qiling_builder'] ) );
        } else {
            $builder_flag = (string) get_query_var( 'qiling_builder', '' );
        }

        if ( $builder_flag === '1' ) {
            return false;
        }

        return $redirect_url;
    }

    /**
     * 前台装修请求下禁用外部多语言插件的前台 301 跳转，避免首页参数被清理。
     *
     * @return void
     */
    public function disable_external_redirects_for_builder_request() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        $builder_flag = isset( $_GET['qiling_builder'] )
            ? sanitize_text_field( wp_unslash( (string) $_GET['qiling_builder'] ) )
            : '';
        if ( $builder_flag !== '1' ) {
            return;
        }

        global $xb_aifanyi_translator;
        if ( is_object( $xb_aifanyi_translator ) ) {
            remove_action(
                'template_redirect',
                array( $xb_aifanyi_translator, 'xb_aifanyi_redirect_to_language_url' )
            );
            remove_filter(
                'redirect_canonical',
                array( $xb_aifanyi_translator, 'xb_aifanyi_disable_canonical_redirect_for_multilingual_request' ),
                20
            );
        }
    }

    /**
     * 前台装修模式下显式禁用页面缓存（兼容常见缓存插件约定常量）
     *
     * @return void
     */
    public function disable_page_cache() {
        if ( ! self::is_builder_mode() || ! function_exists( '\developer_starter_define_no_cache_constants' ) ) {
            return;
        }

        \developer_starter_define_no_cache_constants();
    }

    /**
     * 前台装修模式下输出 no-cache 响应头
     *
     * @return void
     */
    public function send_no_cache_headers() {
        if ( ! self::is_builder_mode() ) {
            return;
        }

        if ( function_exists( '\developer_starter_apply_no_cache_headers' ) ) {
            \developer_starter_apply_no_cache_headers( 'builder' );
            return;
        }

        nocache_headers();
    }

    /**
     * 装修 AJAX 返回禁缓存头
     *
     * @return void
     */
    private function send_no_cache_response_headers() {
        if ( function_exists( '\developer_starter_apply_no_cache_headers' ) ) {
            \developer_starter_apply_no_cache_headers( 'builder-ajax' );
            return;
        }

        nocache_headers();
    }

    /**
     * 前台装修模式下给 body 打标，避免依赖 JS 才切换布局
     *
     * @param array<int,string> $classes classes
     * @return array<int,string>
     */
    public function add_builder_body_class( $classes ) {
        if ( self::is_builder_mode() ) {
            $classes[] = 'qfb-builder-mode';
        }
        return $classes;
    }

    /**
     * 当前请求是否为前台装修模式
     *
     * @return bool
     */
    public static function is_builder_mode() {
        if ( is_admin() || wp_doing_ajax() ) {
            return false;
        }

        if ( ! is_singular( 'page' ) ) {
            return false;
        }

        $builder_flag = isset( $_GET['qiling_builder'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['qiling_builder'] ) ) : '';
        if ( $builder_flag !== '1' ) {
            return false;
        }

        $post_id = get_queried_object_id();
        if ( $post_id <= 0 ) {
            return false;
        }

        return self::current_user_can_use_builder( $post_id );
    }

    /**
     * 当前用户是否允许使用前台装修（仅管理员）
     *
     * @param int $post_id post id
     * @return bool
     */
    private static function current_user_can_use_builder( $post_id = 0 ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * @param int|null $post_id post id
     * @return bool
     */
    private function can_edit_current_page( &$post_id = null ) {
        $post_id = get_queried_object_id();
        if ( $post_id <= 0 || ! is_singular( 'page' ) ) {
            return false;
        }
        return self::current_user_can_use_builder( $post_id );
    }

    /**
     * 获取当前前台请求 URL（不依赖 get_permalink，减少首页规范化跳转干扰）。
     *
     * @param string $fallback fallback url
     * @return string
     */
    private function get_current_frontend_request_url( $fallback = '' ) {
        if ( is_admin() ) {
            return (string) $fallback;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '';
        if ( $request_uri !== '' ) {
            if ( function_exists( 'developer_starter_build_raw_home_url' ) ) {
                return (string) developer_starter_build_raw_home_url( $request_uri );
            }

            return (string) home_url( $request_uri );
        }

        return (string) $fallback;
    }

    /**
     * 后台工具条添加「前台装修」入口
     *
     * @param WP_Admin_Bar $admin_bar admin bar
     * @return void
     */
    public function add_admin_bar_menu( $admin_bar ) {
        if ( ! is_user_logged_in() || is_admin() ) {
            return;
        }

        $post_id = 0;
        if ( ! $this->can_edit_current_page( $post_id ) ) {
            return;
        }

        $base_url = get_permalink( $post_id );
        $base_url = $this->get_current_frontend_request_url( $base_url );
        if ( ! $base_url ) {
            return;
        }

        $base_url = remove_query_arg( array( 'qiling_builder', 'page_id' ), $base_url );

        $is_builder_mode = self::is_builder_mode();
        $builder_url = $is_builder_mode
            ? $base_url
            : add_query_arg(
                array(
                    'qiling_builder' => '1',
                    'page_id'        => (int) $post_id,
                ),
                $base_url
            );

        $admin_bar->add_node(
            array(
                'id'    => 'qiling_frontend_builder',
                'title' => $is_builder_mode ? __( '退出前台装修', 'developer-starter' ) : __( '前台装修', 'developer-starter' ),
                'href'  => esc_url( $builder_url ),
                'meta'  => array(
                    'class' => 'qiling-frontend-builder-toolbar',
                ),
            )
        );
    }

    /**
     * 前台装修模式资源
     *
     * @return void
     */
    public function enqueue_assets() {
        if ( ! self::is_builder_mode() ) {
            return;
        }

        $post_id = get_queried_object_id();
        if ( $post_id <= 0 ) {
            return;
        }

        $css_file = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/frontend-builder.css';
        $ai_service_js_file = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/js/ai-builder-service.js';
        $js_file  = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/js/frontend-builder.js';
        $css_ver  = file_exists( $css_file ) ? (string) filemtime( $css_file ) : DEVELOPER_STARTER_VERSION;
        $ai_service_js_ver = file_exists( $ai_service_js_file ) ? (string) filemtime( $ai_service_js_file ) : DEVELOPER_STARTER_VERSION;
        $js_ver   = file_exists( $js_file ) ? (string) filemtime( $js_file ) : DEVELOPER_STARTER_VERSION;

        wp_enqueue_style(
            'qiling-frontend-builder',
            DEVELOPER_STARTER_ASSETS . '/css/frontend-builder.css',
            array(),
            $css_ver
        );

        $advanced_css_file = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/module-advanced-styles.css';
        if ( file_exists( $advanced_css_file ) ) {
            wp_enqueue_style(
                'developer-starter-module-advanced-styles',
                DEVELOPER_STARTER_ASSETS . '/css/module-advanced-styles.css',
                array( 'qiling-frontend-builder' ),
                (string) filemtime( $advanced_css_file )
            );
        }

        $module_css_mode = sanitize_key( (string) developer_starter_get_option( 'module_css_load_mode', 'single' ) );
        $module_css_mode = in_array( $module_css_mode, array( 'single', 'split' ), true ) ? $module_css_mode : 'single';

        if ( 'single' === $module_css_mode ) {
            $modules_css_file = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/modules.css';
            if ( is_file( $modules_css_file ) ) {
                wp_enqueue_style(
                    'developer-starter-modules',
                    DEVELOPER_STARTER_ASSETS . '/css/modules.css',
                    array( 'qiling-frontend-builder' ),
                    (string) filemtime( $modules_css_file )
                );
            }
        } else {
            // Builder 可随时新增任意模块，分包模式需加载全部独立模块文件供即时预览。
            $module_css_dir = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/modules-split/';
            $module_css_uri = trailingslashit( DEVELOPER_STARTER_ASSETS ) . 'css/modules-split/';
            $module_css_files = glob( $module_css_dir . '*.css' );
            if ( is_array( $module_css_files ) ) {
                sort( $module_css_files, SORT_STRING );
                foreach ( $module_css_files as $module_css_file ) {
                    $module_css_name = basename( $module_css_file, '.css' );
                    if ( '' === $module_css_name || '_' === substr( $module_css_name, 0, 1 ) || ! is_file( $module_css_file ) ) {
                        continue;
                    }
                    wp_enqueue_style(
                        'developer-starter-builder-module-' . sanitize_key( $module_css_name ),
                        $module_css_uri . basename( $module_css_file ),
                        array( 'qiling-frontend-builder' ),
                        (string) filemtime( $module_css_file )
                    );
                }
            }
        }


        $data_source = 'theme';
        $modules = $this->get_builder_modules_for_post( $post_id, $data_source );
        $shop_only = ( $data_source === 'qilingshop' );
        $available_modules = $this->get_library_service()->get_available_modules( $shop_only );
        $module_manager = Module_Manager::get_instance();
        $module_metadata_taxonomy = method_exists( $module_manager, 'get_module_metadata_taxonomy' )
            ? $module_manager->get_module_metadata_taxonomy()
            : array();
        $my_library_templates = $this->get_library_service()->filter_templates_by_source(
            $this->get_library_service()->get_my_library_templates(),
            $shop_only
        );
        $module_dependencies = $this->get_assets_service()->get_module_dependencies();
        $required_external_assets = $this->get_assets_service()->get_required_external_assets_for_modules( $modules, $module_dependencies );
        $external_asset_urls = $this->get_assets_service()->get_external_asset_urls();
        $external_asset_versions = method_exists( $this->get_assets_service(), 'get_external_asset_versions' )
            ? $this->get_assets_service()->get_external_asset_versions()
            : array(
                'swiper' => '12.0.3',
                'chart'  => '2.7.2',
            );
        $ai_builder_config = class_exists( '\Developer_Starter\Core\AI_Decorator' )
            ? AI_Decorator::get_instance()->get_client_config( $post_id )
            : array( 'enabled' => false );
        $ai_builder_available = ! empty( $ai_builder_config['enabled'] );
        $ai_builder_supported = ! $shop_only;
        $ai_max_modules = isset( $ai_builder_config['defaultMaxModules'] )
            ? max( 1, min( 10, absint( $ai_builder_config['defaultMaxModules'] ) ) )
            : 10;
        if ( ! $ai_builder_supported ) {
            $ai_builder_config['enabled'] = false;
        }
        $swiper_asset_version = ! empty( $external_asset_versions['swiper'] ) ? (string) $external_asset_versions['swiper'] : '12.0.3';
        $chart_asset_version  = ! empty( $external_asset_versions['chart'] ) ? (string) $external_asset_versions['chart'] : '2.7.2';
        $page_settings = class_exists( '\Developer_Starter\Core\AI_Decorator' )
            ? AI_Decorator::get_instance()->get_post_page_settings( $post_id )
            : array();
        $page_templates = $this->get_page_template_choices();
        $footer_presets = $this->get_footer_preset_choices();
        $page_visual_presets = $this->get_page_visual_preset_choices();
        $page_visual_fields = $this->get_page_visual_field_definitions();
        $page_visual_preset_vars = $this->get_page_visual_preset_vars();
        $page_visual_resolved = $this->get_page_visual_resolved_context( $post_id );
        $design_system = class_exists( '\Developer_Starter\Core\Design_Tokens' )
            ? Design_Tokens::get_client_payload()
            : array();
        $content_models = class_exists( '\Developer_Starter\Core\Content_Model_Center' )
            ? Content_Model_Center::get_client_payload()
            : array();
        $dynamic_data = class_exists( '\Developer_Starter\Core\Dynamic_Data_Manager' )
            ? Dynamic_Data_Manager::get_instance()->get_client_payload()
            : array();

        if ( ! empty( $required_external_assets['swiper'] ) ) {
            if ( ! wp_style_is( 'swiper', 'enqueued' ) && ! wp_style_is( 'swiper', 'done' ) ) {
                wp_enqueue_style( 'swiper', $external_asset_urls['swiperCss'], array(), $swiper_asset_version );
            }
            if ( ! wp_script_is( 'swiper', 'enqueued' ) && ! wp_script_is( 'swiper', 'done' ) ) {
                wp_enqueue_script( 'swiper', $external_asset_urls['swiperJs'], array(), $swiper_asset_version, true );
            }
        }

        if (
            ! empty( $required_external_assets['chart'] )
            && ! wp_script_is( 'chart-js', 'enqueued' )
            && ! wp_script_is( 'chart-js', 'done' )
        ) {
            wp_enqueue_script( 'chart-js', $external_asset_urls['chartJs'], array(), $chart_asset_version, true );
        }

        wp_enqueue_script(
            'qiling-ai-builder-service',
            DEVELOPER_STARTER_ASSETS . '/js/ai-builder-service.js',
            array( 'jquery' ),
            $ai_service_js_ver,
            false
        );

        wp_enqueue_script(
            'qiling-frontend-builder',
            DEVELOPER_STARTER_ASSETS . '/js/frontend-builder.js',
            array( 'qiling-ai-builder-service', 'jquery', 'jquery-ui-sortable' ),
            $js_ver,
            true
        );

        wp_localize_script(
            'qiling-frontend-builder',
            'qilingFrontendBuilderData',
            array(
                'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
                'nonce'            => wp_create_nonce( self::NONCE_ACTION ),
                'postId'           => $post_id,
                'builderMode'      => 1,
                'dataSource'       => $data_source,
                'modules'          => $modules,
                'pageSettings'     => $page_settings,
                'pageTemplates'    => $page_templates,
                'footerPresets'    => $footer_presets,
                'pageVisualPresets'=> $page_visual_presets,
                'pageVisualFields' => $page_visual_fields,
                'pageVisualPresetVars' => $page_visual_preset_vars,
                'pageVisualResolved' => $page_visual_resolved,
                'designSystem'     => $design_system,
                'contentModels'    => $content_models,
                'dynamicData'      => $dynamic_data,
                'availableModules' => $available_modules,
                'moduleMetadataTaxonomy' => $module_metadata_taxonomy,
                'myLibraryTemplates'=> $my_library_templates,
                'templateAction'   => 'qiling_frontend_builder_get_library_template',
                'limits'           => array(
                    'maxPayloadBytes'    => self::MAX_BUILDER_PAYLOAD_BYTES,
                    'maxModules'         => self::MAX_BUILDER_MODULES,
                    'maxModuleDataBytes' => self::MAX_BUILDER_MODULE_DATA_BYTES,
                ),
                'aiBuilder'        => $ai_builder_config,
                'externalAssets'   => array(
                    'urls'               => $external_asset_urls,
                    'moduleDependencies' => $module_dependencies,
                    'required'           => $required_external_assets,
                ),
                'texts'            => array(
                    'loading'            => __( '加载中...', 'developer-starter' ),
                    'selectModuleTip'    => __( '请选择左侧模块后进行设置', 'developer-starter' ),
                    'saveSuccess'        => __( '已保存，正在刷新预览...', 'developer-starter' ),
                    'saveFailed'         => __( '保存失败，请重试', 'developer-starter' ),
                    'schemaLoadFailed'   => __( '模块配置加载失败', 'developer-starter' ),
                    'templateLoading'    => __( '正在加载模板内容...', 'developer-starter' ),
                    'templateLoadFailed' => __( '模板加载失败，请稍后重试', 'developer-starter' ),
                    'invalidRepeaterJson'=> __( 'JSON 格式错误，请检查后再保存', 'developer-starter' ),
                    'previewFailed'      => __( '实时预览失败，请稍后重试', 'developer-starter' ),
                    'unsaved'            => __( '有未保存修改', 'developer-starter' ),
                    'saved'              => __( '当前内容已保存', 'developer-starter' ),
                    'placeholderSuffix'  => __( '（正在生成实时预览）', 'developer-starter' ),
                    'addAction'          => __( '添加', 'developer-starter' ),
                    'libraryEmpty'       => __( '模块库为空，请检查模块是否正常加载。', 'developer-starter' ),
                    'noLibraryMatch'     => __( '没有匹配的模块，请换个关键词。', 'developer-starter' ),
                    'pageEmpty'          => __( '暂无模块，请先从左侧模块库添加。', 'developer-starter' ),
                    'dragSort'           => __( '拖拽排序', 'developer-starter' ),
                    'settingsAction'     => __( '设置', 'developer-starter' ),
                    'duplicateAction'    => __( '复制', 'developer-starter' ),
                    'deleteAction'       => __( '删除', 'developer-starter' ),
                    'switchYes'          => __( '是', 'developer-starter' ),
                    'switchNo'           => __( '否', 'developer-starter' ),
                    'currentValue'       => __( '当前值：', 'developer-starter' ),
                    'enabledLabel'       => __( '启用', 'developer-starter' ),
                    'galleryPlaceholder' => __( '多个URL用英文逗号分隔', 'developer-starter' ),
                    'repeaterItemPrefix' => __( '项目 #', 'developer-starter' ),
                    'repeaterEmpty'      => __( '暂无项目，请点击“添加项目”。', 'developer-starter' ),
                    'repeaterAdd'        => __( '添加项目', 'developer-starter' ),
                    'mediaUrlPlaceholder'=> __( '请输入媒体URL', 'developer-starter' ),
                    'colorPlaceholder'   => __( '#ffffff 或 linear-gradient(...)', 'developer-starter' ),
                    'tokenApply'         => __( '快速颜色选择', 'developer-starter' ),
                    'tokenApplied'       => __( '已选择快捷颜色', 'developer-starter' ),
                    'tokenShadowSmall'   => __( '小阴影', 'developer-starter' ),
                    'tokenShadowMedium'  => __( '中阴影', 'developer-starter' ),
                    'tokenShadowLarge'   => __( '大阴影', 'developer-starter' ),
                    'tokenCardRadius'    => __( '卡片圆角', 'developer-starter' ),
                    'tokenButtonRadius'  => __( '按钮圆角', 'developer-starter' ),
                    'tokenInputRadius'   => __( '输入框圆角', 'developer-starter' ),
                    'tokenSectionPadding'=> __( '区块间距', 'developer-starter' ),
                    'tokenContainerWidth'=> __( '容器宽度', 'developer-starter' ),
                    'tokenPrimaryColor'  => __( '主色', 'developer-starter' ),
                    'tokenSecondaryColor'=> __( '辅助', 'developer-starter' ),
                    'tokenAccentColor'   => __( '点缀', 'developer-starter' ),
                    'tokenTextColor'     => __( '正文', 'developer-starter' ),
                    'tokenMutedColor'    => __( '弱化', 'developer-starter' ),
                    'tokenHeadingColor'  => __( '标题', 'developer-starter' ),
                    'tokenBackgroundColor'=> __( '背景', 'developer-starter' ),
                    'tokenCardColor'     => __( '卡片', 'developer-starter' ),
                    'tokenSurfaceAltColor'=> __( '浅区块', 'developer-starter' ),
                    'tokenBorderColor'   => __( '边框', 'developer-starter' ),
                    'dynamicDataLabel'   => __( '动态数据', 'developer-starter' ),
                    'dynamicDataStatic'  => __( '静态内容', 'developer-starter' ),
                    'dynamicDataApplied' => __( '已绑定动态数据，预览已更新。', 'developer-starter' ),
                    'dynamicDataCleared' => __( '已改回静态内容。', 'developer-starter' ),
                    'designSummaryTitle' => __( '全局样式', 'developer-starter' ),
                    'libraryFilterAll'   => __( '全部', 'developer-starter' ),
                    'myLibraryEmpty'     => __( '后台“我的模版库”暂无数据，请先在后台页面装修中保存模版。', 'developer-starter' ),
                    'templateTypeMissing'=> __( '该模版对应的模块已不存在，无法添加。', 'developer-starter' ),
                    'externalAssetLoadFailed' => __( '模块依赖资源加载失败，部分预览效果可能不可用。', 'developer-starter' ),
                    'settingsPanelTitle' => __( '模块设置', 'developer-starter' ),
                     'settingsPanelDesc'  => __( '调整当前模块的内容和外观，不会影响其他页面。', 'developer-starter' ),
                    'pageSettingsEntry' => __( '页面设置', 'developer-starter' ),
                    'pageSettingsPanelTitle' => __( '页面设置', 'developer-starter' ),
                     'pageSettingsPanelDesc' => __( '管理当前页面的模板、页头和专属风格。', 'developer-starter' ),
                    'pageTitleLabel' => __( '页面标题', 'developer-starter' ),
                    'pageTemplateLabel' => __( '页面模板', 'developer-starter' ),
                    'pageFooterPresetNone' => __( '不指定预设', 'developer-starter' ),
                    'pageHideHeaderLabel' => __( '隐藏页面头部', 'developer-starter' ),
                    'pageTransparentHeaderLabel' => __( '启用透明头部', 'developer-starter' ),
                    'pageTransparentHeaderDesc' => __( '首屏覆盖在 Banner 上，系统会按首屏明暗自动选择菜单文字色；也可在页面视觉预设中手动指定。', 'developer-starter' ),
                    'pageScrollRevealLabel' => __( '启用滚动动效', 'developer-starter' ),
                    'pageBasicSectionTitle' => __( '基础设置', 'developer-starter' ),
                    'pageDesignSectionTitle' => __( '当前页单独风格', 'developer-starter' ),
                    'pagePaletteSectionTitle' => __( '页面配色', 'developer-starter' ),
                    'pageDarkPaletteSectionTitle' => __( '页面暗色模式', 'developer-starter' ),
                    'pageStructureSectionTitle' => __( '页面圆角与动效', 'developer-starter' ),
                    'pageTypographySectionTitle' => __( '页面排版', 'developer-starter' ),
                    'pageLayoutSectionTitle' => __( '页面布局', 'developer-starter' ),
                    'pageComponentSectionTitle' => __( '页面组件样式', 'developer-starter' ),
                     'pageDesignHelp' => __( '设置仅对当前页面生效，留空则使用全站样式。', 'developer-starter' ),
                     'pageDarkPaletteSectionHelp' => __( '设置仅在当前页面的暗色模式下生效，留空则使用全站方案。', 'developer-starter' ),
                     'pageStructureSectionHelp' => __( '设置当前页面的圆角和动效，留空则使用全站方案。', 'developer-starter' ),
                    'pageDesignSummaryTitle' => __( '当前页覆盖概览', 'developer-starter' ),
                    'pageDesignSummaryActive' => __( '当前页已单独调整 %d 项', 'developer-starter' ),
                    'pageDesignSummaryEmpty' => __( '当前页还没有单独设置，正在跟随全站。', 'developer-starter' ),
                    'pageDesignSectionCount' => __( '已改 %d 项', 'developer-starter' ),
                    'pageDesignSectionClean' => __( '当前跟随全站', 'developer-starter' ),
                    'pageDesignResetAll' => __( '恢复本页跟随全站', 'developer-starter' ),
                    'pageDesignResetGroup' => __( '清空本组', 'developer-starter' ),
                    'pageDesignResetAllDone' => __( '当前页已恢复跟随全站，保存后正式生效。', 'developer-starter' ),
                     'pageDesignResetGroupDone' => __( '已清空当前分组，保存后将使用全站样式。', 'developer-starter' ),
                    'pageDesignPreviewApplied' => __( '页面设计已同步到当前预览，保存后会正式生效。', 'developer-starter' ),
                    'pageVisualPresetAuto' => __( '按当前页面模板自动匹配', 'developer-starter' ),
                    'pageVisualStyleSectionTitle' => __( '页面视觉预设', 'developer-starter' ),
                    'pageVisualStyleHelp' => __( '这里控制当前页顶部、底部、波浪和按钮的基础搭配；下方字段可直接在前台细调。', 'developer-starter' ),
                    'pageVisualStyleModeLabel' => __( '视觉模式', 'developer-starter' ),
                    'pageVisualStyleModeInherit' => __( '跟随页面模板预设', 'developer-starter' ),
                    'pageVisualStyleModeGlobal' => __( '强制跟随全站默认', 'developer-starter' ),
                    'pageVisualStyleModeCustom' => __( '启用当前页面视觉预设', 'developer-starter' ),
                    'pageVisualStylePresetLabel' => __( '基础预设', 'developer-starter' ),
                    'pageVisualHydratePreset' => __( '填充预设值', 'developer-starter' ),
                    'pageVisualClearCustom' => __( '清空细调', 'developer-starter' ),
                    'pageVisualPresetValueLabel' => __( '当前预设值', 'developer-starter' ),
                    'pageVisualPreviewApplied' => __( '页面视觉风格已同步到当前预览，保存后会正式生效。', 'developer-starter' ),
                    'pageVisualPresetRequired' => __( '请先选择一个基础预设，再填充预设值。', 'developer-starter' ),
                     'pageSettingInherited' => __( '留空则使用全站设置', 'developer-starter' ),
                     'pageTypographySectionHelp' => __( '设置当前页面的正文、标题、按钮和导航排版，留空则使用全站设置。', 'developer-starter' ),
                    'pageSettingLayoutHelp' => __( '当前页想单独改容器宽度、区块间距和布局模式，就在这里改。', 'developer-starter' ),
                     'pageComponentSectionHelp' => __( '调整当前页面的组件外观，不会影响全站设置。', 'developer-starter' ),
                    'governanceCardTitle' => __( '作用层级', 'developer-starter' ),
                    'governanceGlobalLabel' => __( '站点级', 'developer-starter' ),
                    'governancePageLabel' => __( '页面级', 'developer-starter' ),
                    'governanceModuleLabel' => __( '模块级', 'developer-starter' ),
                    'pageGovernanceGlobalDesc' => __( '全局设计先决定整站默认外观，当前页默认跟它走。', 'developer-starter' ),
                     'pageGovernancePageDesc' => __( '设置仅对当前页面生效，留空则使用全站设置。', 'developer-starter' ),
                    'pageGovernanceModuleDesc' => __( '如果某个模块还要特殊一点，再去模块设置里单独改。', 'developer-starter' ),
                    'pageGovernanceNote' => __( '普通做法：先定全站，再定当前页，最后只对少数模块做局部微调。', 'developer-starter' ),
                    'moduleGovernanceGlobalDesc' => __( '全局设计先给这个模块一个默认基线。', 'developer-starter' ),
                     'moduleGovernancePageDesc' => __( '当前页面的专属风格会应用到此模块。', 'developer-starter' ),
                    'moduleGovernanceModuleDesc' => __( '这里的设置只改当前模块，最适合做局部强调。', 'developer-starter' ),
                     'moduleGovernanceNote' => __( '留空则使用当前页面和全站设置。', 'developer-starter' ),
                     'moduleLegacyBridgeNote' => __( '当前模块的公共样式设置集中在此面板。', 'developer-starter' ),
                     'moduleVisualTip' => __( '设置仅影响当前模块；留空字段则使用页面或全站设置。', 'developer-starter' ),
                    'moduleVisualAdvancedPrimaryDesc' => __( '用于图标、标签、链接和局部高亮，不是标题文字颜色。', 'developer-starter' ),
                    'moduleVisualAdvancedAccentDesc' => __( '用于第二强调色和部分悬停状态。', 'developer-starter' ),
                    'moduleVisualAdvancedBackgroundDesc' => __( '控制整个当前模块区块的背景。', 'developer-starter' ),
                    'moduleVisualAdvancedTitleColor' => __( '标题颜色', 'developer-starter' ),
                    'moduleVisualAdvancedTitleColorDesc' => __( '控制当前模块主标题和兼容的标题链接。', 'developer-starter' ),
                    'moduleVisualAdvancedSubtitleColor' => __( '副标题颜色', 'developer-starter' ),
                    'moduleVisualAdvancedSubtitleColorDesc' => __( '控制主标题下方的副标题和说明。', 'developer-starter' ),
                    'moduleVisualAdvancedTextColor' => __( '正文颜色', 'developer-starter' ),
                    'moduleVisualAdvancedTextColorDesc' => __( '控制普通段落、列表和摘要文字。', 'developer-starter' ),
                    'moduleVisualAdvancedButtonDesc' => __( '控制当前模块实心主按钮的背景。', 'developer-starter' ),
                    'moduleVisualAdvancedButtonText' => __( '按钮文字', 'developer-starter' ),
                    'moduleVisualAdvancedButtonTextDesc' => __( '控制主按钮正常状态的文字颜色。', 'developer-starter' ),
                    'moduleVisualAdvancedButtonHoverDesc' => __( '控制鼠标移到主按钮上时的背景。', 'developer-starter' ),
                    'moduleVisualAdvancedButtonHoverText' => __( '按钮悬停文字', 'developer-starter' ),
                    'moduleVisualAdvancedButtonHoverTextDesc' => __( '控制鼠标移到主按钮上时的文字颜色。', 'developer-starter' ),
                    'moduleVisualAdvancedCardDesc' => __( '控制当前模块内卡片的背景。', 'developer-starter' ),
                    'moduleVisualAdvancedCardBorderDesc' => __( '控制当前模块内卡片的边框。', 'developer-starter' ),
                    'moduleVisualClear' => __( '清空模块视觉', 'developer-starter' ),
                     'moduleVisualCleared' => __( '已清空当前模块视觉设置，保存后将使用页面样式。', 'developer-starter' ),
                    'aiPanelTitle'       => __( 'AI装修', 'developer-starter' ),
                     'aiPanelDesc'        => __( '支持优化当前页面、所选模块和 SEO 信息。', 'developer-starter' ),
                    'aiButton'           => __( 'AI装修', 'developer-starter' ),
                    'aiBackButton'       => __( '返回设置', 'developer-starter' ),
                    'aiScopeNotice'      => __( '当前工具用于当前单页或当前选中的模块，可同步生成当前页面 SEO 建议。', 'developer-starter' ),
                    'aiDesignContextTip' => __( '生成时会优先沿用当前全局样式，保持模块颜色、圆角、阴影和区块间距一致。', 'developer-starter' ),
                    'aiContentModelContextTip' => __( '生成时会参考已启用的内容模型，优先匹配服务、产品、案例、团队、门店、资源等可复用结构。', 'developer-starter' ),
                    'contentModelSummaryTitle' => __( '内容模型', 'developer-starter' ),
                    'aiUnavailable'      => __( 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。', 'developer-starter' ),
                    'aiServiceUnavailable' => __( 'AI 服务未加载，请刷新后重试。', 'developer-starter' ),
                    'aiPromptLabel'      => __( '装修需求', 'developer-starter' ),
                    'aiPromptPlaceholder'=> __( '例如：优化当前首页，面向软件服务公司，风格现代可信。保留可用内容，强化首屏卖点、服务能力、客户案例、CTA 和 SEO 标题描述。', 'developer-starter' ),
                    'aiPromptRecipeLabel'=> __( '快捷需求', 'developer-starter' ),
                    'aiPromptRecipeTip'  => __( '点一下会追加到装修需求里', 'developer-starter' ),
                     'aiPromptRecipeApplied' => __( '快捷需求已添加。', 'developer-starter' ),
                    'aiPromptRecipeHero' => __( '首屏转化', 'developer-starter' ),
                    'aiPromptRecipeHeroText' => __( '重做当前单页的首屏与转化路径：突出核心卖点、适用人群、信任背书和明确 CTA，保留当前可用内容，不要生成整站。', 'developer-starter' ),
                    'aiPromptRecipeConversion' => __( '成交路径', 'developer-starter' ),
                    'aiPromptRecipeConversionText' => __( '优化当前页面的成交路径：强化服务或产品价值、客户案例、常见问题、咨询入口和按钮文案，让模块顺序更利于转化。', 'developer-starter' ),
                    'aiPromptRecipeInternational' => __( '国际化落地', 'developer-starter' ),
                    'aiPromptRecipeInternationalText' => __( '优化成面向海外用户的国际化落地页基础：标题短句、卖点直接、CTA 清晰，SEO 标题和描述适合英文搜索，保留中文品牌调性。', 'developer-starter' ),
                    'aiPromptRecipeVisual' => __( '视觉统一', 'developer-starter' ),
                    'aiPromptRecipeVisualText' => __( '统一页面视觉：沿用当前全局设计令牌，减少杂色，统一按钮、卡片、间距、圆角和阴影，让页面更专业、更利于阅读。', 'developer-starter' ),
                    'aiPromptRecipeModule' => __( '当前模块', 'developer-starter' ),
                     'aiPromptRecipeModuleText' => __( '仅优化当前模块，保留信息结构，强化标题、描述、按钮文案和视觉节奏。', 'developer-starter' ),
                    'aiPromptHistoryLabel' => __( '最近需求', 'developer-starter' ),
                    'aiPromptHistoryTip' => __( '只保存在当前浏览器，方便反复微调。', 'developer-starter' ),
                    'aiPromptHistoryClear' => __( '清空', 'developer-starter' ),
                    'aiPromptHistoryDelete' => __( '删除', 'developer-starter' ),
                    'aiPromptHistoryUntitled' => __( '未命名需求', 'developer-starter' ),
                    'aiPromptHistoryModuleCount' => __( '%d 个候选模块', 'developer-starter' ),
                    'aiPromptHistoryRestored' => __( '已恢复最近需求。', 'developer-starter' ),
                    'aiPromptHistoryDeleted' => __( '已删除该需求。', 'developer-starter' ),
                    'aiPromptHistoryCleared' => __( '已清空最近需求。', 'developer-starter' ),
                    'aiModuleBundleLabel'=> __( '模块组合', 'developer-starter' ),
                    'aiModuleBundleTip'  => __( '按页面目标快速勾选候选模块', 'developer-starter' ),
                    'aiModuleBundleLanding' => __( '落地页', 'developer-starter' ),
                    'aiModuleBundleService' => __( '服务页', 'developer-starter' ),
                    'aiModuleBundleProduct' => __( '产品页', 'developer-starter' ),
                    'aiModuleBundleContent' => __( '内容页', 'developer-starter' ),
                    'aiModuleBundleLocal' => __( '本地门店', 'developer-starter' ),
                    'aiModuleBundleClear' => __( '清空', 'developer-starter' ),
                    'aiModuleBundleApplied' => __( '已选择 %d 个候选模块。', 'developer-starter' ),
                    'aiModuleBundleCleared' => __( '已清空候选模块。', 'developer-starter' ),
                    'aiModuleBundleEmpty' => __( '没有找到适合该组合的候选模块，请手动选择。', 'developer-starter' ),
                    'aiReadinessTitle'  => __( '生成前自检', 'developer-starter' ),
                    'aiReadinessReady'  => __( '可以生成草稿', 'developer-starter' ),
                    'aiReadinessBlocking' => __( '%d 项需要处理', 'developer-starter' ),
                    'aiReadinessWarning'=> __( '%d 项建议确认', 'developer-starter' ),
                    'aiReadinessPrompt' => __( '需求', 'developer-starter' ),
                    'aiReadinessPromptOk' => __( '已输入 %d 字', 'developer-starter' ),
                    'aiReadinessPromptShort' => __( '建议再补充目标、人群和风格。', 'developer-starter' ),
                    'aiReadinessPromptMissing' => __( '还没填写装修需求。', 'developer-starter' ),
                    'aiReadinessModules'=> __( '模块', 'developer-starter' ),
                    'aiReadinessModulesMissing' => __( '还没选择候选模块。', 'developer-starter' ),
                    'aiReadinessConnection' => __( '连接', 'developer-starter' ),
                    'aiReadinessConnectionDefault' => __( '将使用后台默认连接。', 'developer-starter' ),
                    'aiReadinessModel'  => __( '模型', 'developer-starter' ),
                    'aiReadinessModelDefault' => __( '将使用连接默认模型。', 'developer-starter' ),
                    'aiReadinessPending'=> __( '待应用', 'developer-starter' ),
                    'aiReadinessPendingExists' => __( '已有生成结果待应用或放弃。', 'developer-starter' ),
                    'aiReadinessPendingEmpty' => __( '当前没有待应用结果。', 'developer-starter' ),
                    'aiReviewTitle'     => __( '应用前验收', 'developer-starter' ),
                    'aiReviewReady'     => __( '可以应用', 'developer-starter' ),
                    'aiReviewWarning'   => __( '%d 项建议确认', 'developer-starter' ),
                    'aiReviewError'     => __( '%d 项需要处理', 'developer-starter' ),
                    'aiReviewResult'    => __( '结果', 'developer-starter' ),
                    'aiReviewResultOk'  => __( 'AI 结果结构完整。', 'developer-starter' ),
                    'aiReviewResultInvalid' => __( '结果不完整，建议放弃后重试。', 'developer-starter' ),
                    'aiReviewModuleStructure' => __( '模块结构', 'developer-starter' ),
                    'aiReviewModuleCountOk' => __( '已生成 %d 个模块。', 'developer-starter' ),
                    'aiReviewModuleMissing' => __( '未生成可用模块。', 'developer-starter' ),
                    'aiReviewModuleChange' => __( '模块变化', 'developer-starter' ),
                    'aiReviewModuleChangeOk' => __( '已检测到模块变化。', 'developer-starter' ),
                    'aiReviewModuleChangeNone' => __( '模块结构变化很小，请确认是否符合预期。', 'developer-starter' ),
                    'aiReviewSeo'       => __( 'SEO', 'developer-starter' ),
                    'aiReviewSeoOk'     => __( '已包含 SEO 标题或描述。', 'developer-starter' ),
                    'aiReviewSeoMissing'=> __( '未检测到 SEO 建议，可应用后手动补充。', 'developer-starter' ),
                    'aiReviewCta'       => __( '转化', 'developer-starter' ),
                    'aiReviewCtaOk'     => __( '已检测到 CTA 或联系转化词。', 'developer-starter' ),
                    'aiReviewCtaMissing'=> __( '未检测到明显 CTA，应用前建议确认。', 'developer-starter' ),
                    'aiReviewDesign'    => __( '视觉', 'developer-starter' ),
                    'aiReviewDesignOk'  => __( '已包含页面设计建议。', 'developer-starter' ),
                    'aiReviewDesignInherited' => __( '未包含页面设计覆盖，将沿用当前全局视觉。', 'developer-starter' ),
                    'aiReviewModuleType'=> __( '模块类型', 'developer-starter' ),
                    'aiReviewModuleTypeOk' => __( '模块类型保持不变。', 'developer-starter' ),
                    'aiReviewModuleTypeChanged' => __( '模块类型不一致，建议放弃后重试。', 'developer-starter' ),
                    'aiReviewModuleContent' => __( '模块内容', 'developer-starter' ),
                    'aiReviewModuleContentOk' => __( '已检测到字段变化。', 'developer-starter' ),
                    'aiReviewModuleContentSame' => __( '未检测到明显内容变化。', 'developer-starter' ),
                    'aiReviewGuardrail' => __( '安全', 'developer-starter' ),
                    'aiReviewGuardrailOk' => __( '仍处于待应用状态，确认后才会修改页面。', 'developer-starter' ),
                    'aiConnectionLabel'  => __( 'AI 连接', 'developer-starter' ),
                    'aiModelLabel'       => __( '模型', 'developer-starter' ),
                    'aiModuleLabel'      => __( '候选模块', 'developer-starter' ),
                    'aiModuleSearch'     => __( '搜索模块名称...', 'developer-starter' ),
                    'aiGenerate'         => __( '生成/优化当前单页', 'developer-starter' ),
                    'aiOptimizeModule'   => __( '优化当前模块', 'developer-starter' ),
                    'aiGenerating'       => __( '正在生成当前单页草稿，请稍候…', 'developer-starter' ),
                    'aiModuleOptimizing' => __( '正在优化当前模块，请稍候…', 'developer-starter' ),
                    'aiPlanning'         => __( '正在规划页面结构，请稍候…', 'developer-starter' ),
                    'aiPlanFailed'       => __( '页面规划失败，请重试。', 'developer-starter' ),
                    'aiPlanEmpty'        => __( '未规划出可用模块，请调整需求或候选模块后重试。', 'developer-starter' ),
                    'aiPlanSuccess'      => __( '页面规划完成，开始逐模块生成…', 'developer-starter' ),
                    'aiGeneratingStep'   => __( '正在逐模块生成草稿：', 'developer-starter' ),
                    'aiModuleFailed'     => __( '模块生成失败，请重试。', 'developer-starter' ),
                    'aiNoSelectedModule' => __( '请先在页面结构中选中一个模块，再使用当前模块优化。', 'developer-starter' ),
                    'aiModuleApplySuccess' => __( '当前模块已优化，请确认预览后保存。', 'developer-starter' ),
                    'aiModuleRetrying'   => __( '模块生成失败，正在自动重试：', 'developer-starter' ),
                    'aiPartialApply'     => __( '生成过程中出现问题，已先应用当前拿到的草稿内容，请检查后再保存。', 'developer-starter' ),
                    'aiUndoLastChange'   => __( '撤回本次修改', 'developer-starter' ),
                    'aiUndoSuccess'      => __( '已撤回到修改前，请确认预览后保存。', 'developer-starter' ),
                    'aiPendingReady'     => __( '草稿已生成，请查看差异后应用。', 'developer-starter' ),
                    'aiPendingDiscarded' => __( '已放弃本次生成结果，页面未被改动。', 'developer-starter' ),
                    'aiPendingApplyFailed' => __( '结果应用失败，请放弃后重试。', 'developer-starter' ),
                    'aiPendingApplySuccess' => __( '结果已应用，请确认预览后保存。', 'developer-starter' ),
                    'aiDiffModuleTitle'  => __( '模块结果待应用', 'developer-starter' ),
                    'aiDiffPageTitle'    => __( '单页结果待应用', 'developer-starter' ),
                    'aiDiffIntro'        => __( '草稿还没有改动页面，确认后才会应用。', 'developer-starter' ),
                    'aiApplyPending'     => __( '应用 AI 结果', 'developer-starter' ),
                    'aiDiscardPending'   => __( '放弃结果', 'developer-starter' ),
                    'aiDiffPageTemplate' => __( '页面模板', 'developer-starter' ),
                    'aiDiffHideHeader'   => __( '隐藏页面标题', 'developer-starter' ),
                    'aiDiffTransparentHeader' => __( '透明头部', 'developer-starter' ),
                    'aiDiffScrollReveal' => __( '滚动动效', 'developer-starter' ),
                    'aiDiffSeo'          => __( 'SEO 建议', 'developer-starter' ),
                    'aiDiffDesign'       => __( '页面设计', 'developer-starter' ),
                    'aiDiffUnknownModule'=> __( '未知模块', 'developer-starter' ),
                    'aiDiffModuleInvalid'=> __( '模块结果不完整，建议放弃后重试。', 'developer-starter' ),
                    'aiDiffTargetModule' => __( '目标模块：%d', 'developer-starter' ),
                    'aiDiffModuleTypeChanged' => __( '模块类型变化：', 'developer-starter' ),
                    'aiDiffModuleFieldsChanged' => __( '模块字段变化：', 'developer-starter' ),
                    'aiDiffModuleCount'  => __( '模块数量：', 'developer-starter' ),
                    'aiDiffAddedModules' => __( '新增模块：', 'developer-starter' ),
                    'aiDiffRemovedModules' => __( '删除模块：', 'developer-starter' ),
                    'aiDiffReplacedModules' => __( '替换模块：', 'developer-starter' ),
                    'aiDiffChangedModules' => __( '修改模块：', 'developer-starter' ),
                    'aiDiffPageSettings' => __( '页面设置：', 'developer-starter' ),
                    'aiDiffNoMajorChange'=> __( '没有检测到明显字段变化。', 'developer-starter' ),
                    'aiDiffFieldCount'   => __( '(%d 项)', 'developer-starter' ),
                    'aiDiffFieldCountPlain' => __( '%d 项', 'developer-starter' ),
                    'aiMissingPrompt'    => __( '请先输入装修需求。', 'developer-starter' ),
                    'aiMissingModules'   => __( '请先选择候选模块。', 'developer-starter' ),
                    'aiDisallowedSitePrompt' => __( '在线 AI 整站生成已关闭。请改为生成当前单页，或选中单个模块后做模块优化。', 'developer-starter' ),
                    'aiTooManyModules'   => sprintf(
                        /* translators: %d: max module count */
                        __( '候选模块最多选择 %d 个。', 'developer-starter' ),
                        $ai_max_modules
                    ),
                    'aiReplaceConfirm'   => __( 'AI 会先生成待应用草稿，确认应用时会替换当前页面模块列表。是否继续？', 'developer-starter' ),
                    'aiApplySuccess'     => __( '草稿已应用到当前页面，请确认预览后保存。', 'developer-starter' ),
                    'aiGenerateFailed'   => __( '生成失败，请重试。', 'developer-starter' ),
                    'aiSelectedSuffix'   => sprintf(
                        /* translators: %d: max module count */
                        __( '/%d', 'developer-starter' ),
                        $ai_max_modules
                    ),
                    'pageSettingsPending'=> __( '页面模板与头部设置会在保存后一起生效。', 'developer-starter' ),
                    'snapshotHistory'    => __( '保存历史', 'developer-starter' ),
                    'snapshotLoadFailed' => __( '保存历史加载失败', 'developer-starter' ),
                    'snapshotEmpty'      => __( '暂无保存历史。每次保存前都会自动生成一份快照。', 'developer-starter' ),
                    'snapshotRestore'    => __( '恢复', 'developer-starter' ),
                    'snapshotRestoring'  => __( '正在恢复保存历史...', 'developer-starter' ),
                    'snapshotRestoreSuccess' => __( '已恢复保存历史，正在刷新预览...', 'developer-starter' ),
                    'snapshotRestoreFailed' => __( '恢复保存历史失败，请重试', 'developer-starter' ),
                    'snapshotRestoreConfirm' => __( '确定恢复到这次保存前的状态吗？当前状态会先生成一份新快照。', 'developer-starter' ),
                    'builderPayloadTooLarge' => __( '装修数据过大，请减少模块或复杂内容后再试。', 'developer-starter' ),
                    'builderTooManyModules' => sprintf(
                        /* translators: %d: max builder module count */
                        __( '当前页面模块超过上限（最多 %d 个），请减少模块后再保存或预览。', 'developer-starter' ),
                        self::MAX_BUILDER_MODULES
                    ),
                    'builderModuleDataTooLarge' => __( '某个模块的数据过大，请减少该模块中的列表项、图片或长文本后再试。', 'developer-starter' ),
                ),
            )
        );
    }

    /**
     * 渲染前台装修面板
     *
     * @return void
     */
    public function render_builder_panel() {
        if ( ! self::is_builder_mode() ) {
            return;
        }

        $post_id = get_queried_object_id();
        if ( $post_id <= 0 ) {
            return;
        }

        $exit_url = remove_query_arg( 'qiling_builder', get_permalink( $post_id ) );
        $shop_only = $this->get_qilingshop_service()->is_builder_page( $post_id );
        $ai_builder_globally_available = class_exists( '\Developer_Starter\Core\AI_Decorator' )
            && ! empty( AI_Decorator::get_instance()->get_client_config( $post_id )['enabled'] );
        $ai_builder_enabled = $ai_builder_globally_available && ! $shop_only;
        ?>
        <div id="qiling-frontend-builder" class="qfb-root">
            <button type="button" id="qfb-toggle" class="qfb-toggle" aria-label="<?php esc_attr_e( '展开/收起前台装修面板', 'developer-starter' ); ?>">
                <span class="dashicons dashicons-admin-customizer"></span>
            </button>

            <aside class="qfb-panel qfb-panel-left" id="qfb-panel">
                <header class="qfb-header">
                    <div class="qfb-header-title">
                        <strong><?php esc_html_e( '启灵前台装修', 'developer-starter' ); ?></strong>
                        <span class="qfb-post-id">#<?php echo esc_html( (string) $post_id ); ?></span>
                    </div>
                    <div class="qfb-header-actions">
                        <button type="button" class="button button-primary button-small qfb-save-btn" id="qfb-save"><?php esc_html_e( '保存', 'developer-starter' ); ?></button>
                        <button type="button" class="button button-small qfb-snapshots-btn" id="qfb-snapshots-toggle"><?php esc_html_e( '保存历史', 'developer-starter' ); ?></button>
                        <?php if ( $ai_builder_enabled ) : ?>
                            <button type="button" class="button button-small qfb-ai-btn" id="qfb-ai-toggle"><?php esc_html_e( 'AI装修', 'developer-starter' ); ?></button>
                        <?php endif; ?>
                        <a class="button button-small qfb-exit-btn" href="<?php echo esc_url( $exit_url ); ?>"><?php esc_html_e( '退出', 'developer-starter' ); ?></a>
                    </div>
                </header>

                <div class="qfb-status" id="qfb-status"><?php esc_html_e( '当前内容已保存', 'developer-starter' ); ?></div>
                <div class="qfb-snapshots" id="qfb-snapshots-panel" style="display:none;"></div>
                <?php if ( $shop_only && $ai_builder_globally_available ) : ?>
                    <div class="qfb-status is-warning"><?php esc_html_e( '当前积分商城页面暂不支持 AI 装修，请使用模块库手动搭建页面。', 'developer-starter' ); ?></div>
                    <?php endif; ?>

                <div class="qfb-columns">
                    <section class="qfb-column qfb-column-library">
                        <h3><?php esc_html_e( '模块库', 'developer-starter' ); ?></h3>
                        <div class="qfb-preview-bar" id="qfb-preview-tools" aria-label="<?php esc_attr_e( '预览尺寸', 'developer-starter' ); ?>">
                            <span><?php esc_html_e( '预览', 'developer-starter' ); ?></span>
                            <button type="button" class="qfb-preview-mode is-active" data-preview-mode="desktop"><?php esc_html_e( '桌面', 'developer-starter' ); ?></button>
                            <button type="button" class="qfb-preview-mode" data-preview-mode="tablet"><?php esc_html_e( '平板', 'developer-starter' ); ?></button>
                            <button type="button" class="qfb-preview-mode" data-preview-mode="mobile"><?php esc_html_e( '手机', 'developer-starter' ); ?></button>
                        </div>
                        <div id="qfb-design-summary" class="qfb-design-summary"></div>
                        <div class="qfb-library-block qfb-library-block-my">
                            <div class="qfb-block-title"><?php esc_html_e( '我的模版库（后台同步）', 'developer-starter' ); ?></div>
                            <ul id="qfb-my-library-list" class="qfb-list qfb-my-library-list"></ul>
                        </div>
                        <div class="qfb-library-block qfb-library-block-all">
                            <div class="qfb-block-title"><?php esc_html_e( '全部模块', 'developer-starter' ); ?></div>
                            <div id="qfb-library-filters" class="qfb-library-filters"></div>
                            <input type="search" id="qfb-library-search" class="qfb-search" placeholder="<?php esc_attr_e( '搜索模块...', 'developer-starter' ); ?>" />
                            <ul id="qfb-library-list" class="qfb-list qfb-library-list"></ul>
                        </div>
                    </section>

                    <section class="qfb-column qfb-column-structure">
                        <h3><?php esc_html_e( '页面结构（可拖拽）', 'developer-starter' ); ?></h3>
                        <ul id="qfb-page-list" class="qfb-list qfb-page-list"></ul>
                    </section>
                </div>
            </aside>

            <aside class="qfb-panel qfb-panel-right">
                <header class="qfb-header qfb-settings-header">
                    <div class="qfb-header-title">
                        <strong id="qfb-right-title"><?php esc_html_e( '模块设置', 'developer-starter' ); ?></strong>
                        <span class="qfb-post-id" id="qfb-right-desc"><?php esc_html_e( '点击左侧模块或页面模块进行编辑', 'developer-starter' ); ?></span>
                    </div>
                </header>
                <div class="qfb-settings-pane">
                    <section class="qfb-column qfb-column-settings">
                        <div id="qfb-settings" class="qfb-settings-empty">
                            <?php esc_html_e( '请选择左侧模块后进行设置', 'developer-starter' ); ?>
                        </div>
                        <div id="qfb-ai-pane" class="qfb-ai-pane" style="display:none;"></div>
                    </section>
                </div>
            </aside>
        </div>
        <?php
    }

    /**
     * 获取前台装修初始化模块（主题模块或积分商城模块）。
     *
     * @param int    $post_id post id
     * @param string $source  source
     * @return array<int,array<string,mixed>>
     */
    private function get_builder_modules_for_post( $post_id, &$source = 'theme' ) {
        if ( $this->get_qilingshop_service()->is_builder_page( $post_id ) ) {
            $source = 'qilingshop';
            $this->get_qilingshop_service()->bootstrap_modules( $post_id );
            $shop_modules = $this->get_qilingshop_service()->get_layout_modules( $post_id );
            return $this->normalize_modules_for_js( $shop_modules );
        }

        $source = 'theme';
        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );

        if ( empty( $modules ) && function_exists( 'developer_starter_get_page_modules_data' ) ) {
            $modules = developer_starter_get_page_modules_data( $post_id );
        }

        return $this->normalize_modules_for_js( $modules );
    }

    /**
     * @param mixed $modules modules
     * @return array<int,array<string,mixed>>
     */
    private function normalize_modules_for_js( $modules ) {
        if ( ! is_array( $modules ) ) {
            return array();
        }

        $modules = $this->normalize_modules_for_storage( $modules, false );
        $normalized = array();
        foreach ( $modules as $row ) {
            if ( ! is_array( $row ) || empty( $row['type'] ) ) {
                continue;
            }

            $module_id   = sanitize_key( (string) $row['type'] );
            $module_data = isset( $row['data'] ) && is_array( $row['data'] ) ? $row['data'] : array();
            $module_data = $this->get_builder_data_service()->prepare_module_data_for_editor(
                $module_id,
                $module_data,
                $this->get_module_storage_schema( $module_id ),
                isset( $row['schemaVersion'] ) && is_scalar( $row['schemaVersion'] ) ? (string) $row['schemaVersion'] : ''
            );

            $normalized[] = array(
                'type' => $module_id,
                'data' => $module_data,
            );
        }

        return $normalized;
    }

    /**
     * @return array<string,string>
     */
    private function get_page_template_choices() {
        $choices = array(
            'default' => __( '默认模板', 'developer-starter' ),
        );

        $theme_templates = wp_get_theme()->get_page_templates( null, 'page' );
        if ( is_array( $theme_templates ) ) {
            foreach ( $theme_templates as $label => $template ) {
                if ( ! is_string( $template ) || '' === trim( $template ) ) {
                    continue;
                }

                $choices[ $template ] = is_string( $label ) && '' !== trim( $label ) ? $label : $template;
            }
        }

        return $choices;
    }

    /**
     * @return array<string,string>
     */
    private function get_footer_preset_choices() {
        $choices = array(
            '' => __( '不指定预设', 'developer-starter' ),
        );

        if ( ! function_exists( 'developer_starter_get_page_visual_skins' ) ) {
            return $choices;
        }

        foreach ( developer_starter_get_page_visual_skins() as $skin_key => $skin ) {
            $skin_key = sanitize_key( (string) $skin_key );
            if ( '' === $skin_key || ! is_array( $skin ) || empty( $skin['footer'] ) || ! is_array( $skin['footer'] ) ) {
                continue;
            }

            $label = isset( $skin['label'] ) && is_scalar( $skin['label'] )
                ? wp_strip_all_tags( (string) $skin['label'] )
                : $skin_key;
            $choices[ $skin_key ] = sprintf(
                /* translators: %s: visual skin label */
                __( '%s页脚', 'developer-starter' ),
                $label
            );
        }

        if ( function_exists( 'developer_starter_get_page_visual_custom_presets' ) ) {
            foreach ( developer_starter_get_page_visual_custom_presets() as $preset_key => $preset ) {
                $preset_key = sanitize_key( (string) $preset_key );
                if ( '' === $preset_key || ! is_array( $preset ) || empty( $preset['skin']['footer'] ) ) {
                    continue;
                }
                $label = ! empty( $preset['label'] ) ? wp_strip_all_tags( (string) $preset['label'] ) : $preset_key;
                $choices[ $preset_key ] = sprintf(
                    /* translators: %s: user-created visual preset label */
                    __( '我的预设：%s', 'developer-starter' ),
                    $label
                );
            }
        }

        return $choices;
    }

    /**
     * @return array<string,string>
     */
    private function get_page_visual_preset_choices() {
        $choices = array(
            '' => __( '按当前页面模板自动匹配', 'developer-starter' ),
        );

        if ( function_exists( 'developer_starter_get_page_visual_style_presets' ) ) {
            $presets = developer_starter_get_page_visual_style_presets();
            if ( is_array( $presets ) ) {
                foreach ( $presets as $preset_key => $preset_label ) {
                    $preset_key = sanitize_key( (string) $preset_key );
                    if ( '' !== $preset_key ) {
                        $choices[ $preset_key ] = wp_strip_all_tags( (string) $preset_label );
                    }
                }
            }
        }

        return $choices;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_page_visual_field_definitions() {
        if ( ! function_exists( 'developer_starter_get_page_visual_style_fields' ) ) {
            return array();
        }

        $groups = developer_starter_get_page_visual_style_fields();
        if ( ! is_array( $groups ) ) {
            return array();
        }

        $definitions = array();
        foreach ( $groups as $group_key => $group ) {
            $group_key = sanitize_key( (string) $group_key );
            if ( '' === $group_key || ! is_array( $group ) || empty( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
                continue;
            }

            $fields = array();
            foreach ( $group['fields'] as $field_key => $field ) {
                $field_key = sanitize_key( (string) $field_key );
                if ( '' === $field_key || ! is_array( $field ) ) {
                    continue;
                }

                $vars = isset( $field['vars'] ) && is_array( $field['vars'] )
                    ? array_values( array_filter( array_map( 'strval', $field['vars'] ) ) )
                    : array();

                $fields[ $field_key ] = array(
                    'label'       => isset( $field['label'] ) && is_scalar( $field['label'] ) ? wp_strip_all_tags( (string) $field['label'] ) : $field_key,
                    'placeholder' => isset( $field['placeholder'] ) && is_scalar( $field['placeholder'] ) ? wp_strip_all_tags( (string) $field['placeholder'] ) : '',
                    'type'        => isset( $field['type'] ) && is_scalar( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'css',
                    'vars'        => $vars,
                );
            }

            if ( empty( $fields ) ) {
                continue;
            }

            $definitions[ $group_key ] = array(
                'label'       => isset( $group['label'] ) && is_scalar( $group['label'] ) ? wp_strip_all_tags( (string) $group['label'] ) : $group_key,
                'description' => isset( $group['description'] ) && is_scalar( $group['description'] ) ? wp_strip_all_tags( (string) $group['description'] ) : '',
                'fields'      => $fields,
            );
        }

        return $definitions;
    }

    /**
     * @return array<string,array<string,string>>
     */
    private function get_page_visual_preset_vars() {
        if ( ! function_exists( 'developer_starter_get_page_visual_style_presets' ) ) {
            return array();
        }

        $preset_vars = array();
        foreach ( developer_starter_get_page_visual_style_presets() as $preset_key => $preset_label ) {
            $preset_key = sanitize_key( (string) $preset_key );
            if ( '' === $preset_key ) {
                continue;
            }

            if ( function_exists( 'developer_starter_get_page_visual_preset_vars_array' ) ) {
                $vars = developer_starter_get_page_visual_preset_vars_array( $preset_key, 'all' );
            } elseif ( function_exists( 'developer_starter_get_page_visual_skin' ) && function_exists( 'developer_starter_get_page_visual_skin_vars_array' ) ) {
                $vars = developer_starter_get_page_visual_skin_vars_array( developer_starter_get_page_visual_skin( $preset_key ), 'all' );
            } else {
                $vars = array();
            }

            if ( ! empty( $vars ) && is_array( $vars ) ) {
                $preset_vars[ $preset_key ] = $vars;
            }
        }

        return $preset_vars;
    }

    /**
     * @param int $post_id Post ID.
     * @return array<string,mixed>
     */
    private function get_page_visual_resolved_context( $post_id ) {
        if ( ! function_exists( 'developer_starter_resolve_page_visual_style' ) ) {
            return array(
                'skinKey'   => '',
                'presetKey' => '',
                'vars'      => array(),
            );
        }

        $resolved = developer_starter_resolve_page_visual_style( absint( $post_id ) );
        if ( ! is_array( $resolved ) ) {
            return array(
                'skinKey'   => '',
                'presetKey' => '',
                'vars'      => array(),
            );
        }

        return array(
            'skinKey'   => ! empty( $resolved['skin_key'] ) ? sanitize_key( (string) $resolved['skin_key'] ) : '',
            'presetKey' => ! empty( $resolved['preset_key'] ) ? sanitize_key( (string) $resolved['preset_key'] ) : '',
            'vars'      => ! empty( $resolved['vars'] ) && is_array( $resolved['vars'] ) ? $resolved['vars'] : array(),
        );
    }

    /**
     * Ajax: 获取模块 schema
     *
     * @return void
     */
    public function ajax_get_module_schema() {
        $this->send_no_cache_response_headers();
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 || ! self::current_user_can_use_builder( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        $module_id = isset( $_POST['module_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['module_id'] ) ) : '';
        if ( $module_id === '' ) {
            wp_send_json_error( array( 'message' => __( '模块ID无效', 'developer-starter' ) ), 400 );
        }
        if ( $this->get_qilingshop_service()->is_shop_module_type( $module_id ) ) {
            $this->get_qilingshop_service()->bootstrap_modules( $post_id );
        }

        $manager = Module_Manager::get_instance();
        $module = $manager->get_module( $module_id );
        if ( ! $module ) {
            wp_send_json_error( array( 'message' => __( '模块不存在', 'developer-starter' ) ), 404 );
        }

        wp_send_json_success( $this->get_modules_service()->build_module_schema_payload( $module_id, $module ) );
    }

    /**
     * Ajax: 获取“我的模版库”单条模板详情
     *
     * @return void
     */
    public function ajax_get_library_template() {
        $this->send_no_cache_response_headers();
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 || ! self::current_user_can_use_builder( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        $template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( (string) $_POST['template_id'] ) ) : 0;
        $template = $this->get_library_service()->get_my_library_template_detail( $template_id, $post_id );
        if ( is_wp_error( $template ) ) {
            wp_send_json_error( array( 'message' => $template->get_error_message() ), 400 );
        }

        wp_send_json_success(
            array(
                'template' => $template,
            )
        );
    }

    /**
     * Ajax: 保存模块配置
     *
     * @return void
     */
    public function ajax_save_modules() {
        $this->send_no_cache_response_headers();
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 || ! self::current_user_can_use_builder( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        $modules_raw = isset( $_POST['modules'] ) ? wp_unslash( (string) $_POST['modules'] ) : '';
        if ( $modules_raw === '' ) {
            wp_send_json_error( array( 'message' => __( '保存数据为空', 'developer-starter' ) ), 400 );
        }

        $modules = json_decode( $modules_raw, true );
        if ( ! is_array( $modules ) ) {
            wp_send_json_error( array( 'message' => __( '数据格式错误', 'developer-starter' ) ), 400 );
        }

        $payload_check = $this->validate_builder_modules_payload( $modules_raw, $modules );
        if ( is_wp_error( $payload_check ) ) {
            wp_send_json_error( array( 'message' => $payload_check->get_error_message() ), 400 );
        }

        $data_source = $this->get_qilingshop_service()->is_builder_page( $post_id ) ? 'qilingshop' : 'theme';
        if ( $data_source === 'qilingshop' ) {
            $this->get_qilingshop_service()->bootstrap_modules( $post_id );
        }

        $snapshot_source = $data_source;
        $snapshot_modules = $this->get_builder_modules_for_post( $post_id, $snapshot_source );
        $snapshot_page_settings = class_exists( '\Developer_Starter\Core\AI_Decorator' )
            ? AI_Decorator::get_instance()->get_post_page_settings( $post_id )
            : array();
        $snapshot_result = $this->get_snapshot_service()->create_pre_save_snapshot(
            $post_id,
            $snapshot_modules,
            $snapshot_page_settings,
            $snapshot_source
        );

        // 前台装修保存时沿用主题已有字段清洗逻辑，避免脏数据直接落库。
        $clean_modules = $this->normalize_modules_for_storage( $modules, true );
        $this->get_qilingshop_service()->persist_modules_for_source( $post_id, $clean_modules, $data_source );

        $page_settings_raw = isset( $_POST['page_settings'] ) ? wp_unslash( (string) $_POST['page_settings'] ) : '';
        if ( '' !== $page_settings_raw ) {
            $page_settings = json_decode( $page_settings_raw, true );
            if ( is_array( $page_settings ) && class_exists( '\Developer_Starter\Core\AI_Decorator' ) ) {
                AI_Decorator::get_instance()->persist_post_page_settings( $post_id, $page_settings );
            }
        }

        wp_send_json_success(
            array(
                'message'  => __( '模块保存成功', 'developer-starter' ),
                'snapshot' => $snapshot_result,
            )
        );
    }

    /**
     * Ajax: 获取当前页面保存快照。
     *
     * @return void
     */
    public function ajax_get_snapshots() {
        $this->send_no_cache_response_headers();
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 || ! self::current_user_can_use_builder( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        wp_send_json_success(
            array(
                'snapshots' => $this->get_snapshot_service()->get_snapshot_summaries( $post_id ),
            )
        );
    }

    /**
     * Ajax: 恢复当前页面保存快照。
     *
     * @return void
     */
    public function ajax_restore_snapshot() {
        $this->send_no_cache_response_headers();
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 || ! self::current_user_can_use_builder( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        $snapshot_id = isset( $_POST['snapshot_id'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['snapshot_id'] ) ) : '';
        $snapshot = $this->get_snapshot_service()->get_snapshot( $post_id, $snapshot_id );
        if ( is_wp_error( $snapshot ) ) {
            wp_send_json_error( array( 'message' => $snapshot->get_error_message() ), 404 );
        }

        $data_source = isset( $snapshot['data_source'] ) ? sanitize_key( (string) $snapshot['data_source'] ) : 'theme';
        $current_source = $this->get_qilingshop_service()->is_builder_page( $post_id ) ? 'qilingshop' : 'theme';
        if ( $data_source !== $current_source ) {
            wp_send_json_error( array( 'message' => __( '该历史版本与当前页面不匹配，无法恢复。', 'developer-starter' ) ), 400 );
        }

        $current_modules = $this->get_builder_modules_for_post( $post_id, $current_source );
        $current_page_settings = class_exists( '\Developer_Starter\Core\AI_Decorator' )
            ? AI_Decorator::get_instance()->get_post_page_settings( $post_id )
            : array();
        $restore_guard_snapshot = $this->get_snapshot_service()->create_pre_save_snapshot(
            $post_id,
            $current_modules,
            $current_page_settings,
            $current_source
        );

        $modules = isset( $snapshot['modules'] ) && is_array( $snapshot['modules'] ) ? $snapshot['modules'] : array();
        $clean_modules = $this->normalize_modules_for_storage( $modules, true );
        $this->get_qilingshop_service()->persist_modules_for_source( $post_id, $clean_modules, $data_source );

        if ( isset( $snapshot['page_settings'] ) && is_array( $snapshot['page_settings'] ) && class_exists( '\Developer_Starter\Core\AI_Decorator' ) ) {
            AI_Decorator::get_instance()->persist_post_page_settings( $post_id, $snapshot['page_settings'] );
        }

        wp_send_json_success(
            array(
                'message'  => __( '保存历史已恢复', 'developer-starter' ),
                'snapshot' => $restore_guard_snapshot,
            )
        );
    }

    /**
     * Ajax: 渲染单个模块实时预览
     *
     * @return void
     */
    public function ajax_render_module_preview() {
        $this->send_no_cache_response_headers();
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 || ! self::current_user_can_use_builder( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        $module_id = isset( $_POST['module_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['module_id'] ) ) : '';
        if ( $module_id === '' ) {
            wp_send_json_error( array( 'message' => __( '模块ID无效', 'developer-starter' ) ), 400 );
        }
        if ( $this->get_qilingshop_service()->is_shop_module_type( $module_id ) ) {
            $this->get_qilingshop_service()->bootstrap_modules( $post_id );
        }

        if ( ! Module_Manager::get_instance()->get_module( $module_id ) ) {
            wp_send_json_error( array( 'message' => __( '模块不存在', 'developer-starter' ) ), 404 );
        }

        $index = isset( $_POST['index'] ) ? absint( wp_unslash( (string) $_POST['index'] ) ) : 0;

        $module_data_raw = isset( $_POST['module_data'] ) ? wp_unslash( (string) $_POST['module_data'] ) : '';
        if ( strlen( $module_data_raw ) > self::MAX_BUILDER_MODULE_DATA_BYTES ) {
            wp_send_json_error( array( 'message' => __( '模块数据过大，请减少该模块中的列表项、图片或长文本后再试。', 'developer-starter' ) ), 400 );
        }
        $module_data = array();
        if ( $module_data_raw !== '' ) {
            $parsed = json_decode( $module_data_raw, true );
            if ( is_array( $parsed ) ) {
                $module_data = $this->get_modules_service()->sanitize_module_data_for_preview(
                    $parsed,
                    $this->get_module_storage_schema( $module_id ),
                    $module_id
                );
            }
        }

        $html = $this->build_module_preview_html( $post_id, $module_id, $module_data, $index );

        $this->send_no_cache_response_headers();
        if ( ! headers_sent() ) {
            header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        }
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        wp_die();
    }

    /**
     * Ajax: 整页模块实时预览（不保存）
     *
     * @return void
     */
    public function ajax_render_preview() {
        $this->send_no_cache_response_headers();
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id <= 0 || ! self::current_user_can_use_builder( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        $modules_raw = isset( $_POST['modules'] ) ? wp_unslash( (string) $_POST['modules'] ) : '';
        if ( $modules_raw === '' ) {
            wp_send_json_error( array( 'message' => __( '预览数据为空', 'developer-starter' ) ), 400 );
        }

        $modules = json_decode( $modules_raw, true );
        if ( ! is_array( $modules ) ) {
            wp_send_json_error( array( 'message' => __( '预览数据格式错误', 'developer-starter' ) ), 400 );
        }

        $payload_check = $this->validate_builder_modules_payload( $modules_raw, $modules );
        if ( is_wp_error( $payload_check ) ) {
            wp_send_json_error( array( 'message' => $payload_check->get_error_message() ), 400 );
        }

        $clean_modules = $this->normalize_modules_for_storage( $modules, true );
        $html = '';
        foreach ( $clean_modules as $index => $row ) {
            $module_id = isset( $row['type'] ) ? (string) $row['type'] : '';
            $module_data = isset( $row['data'] ) && is_array( $row['data'] ) ? $row['data'] : array();
            if ( $module_id === '' ) {
                continue;
            }
            $html .= $this->build_module_preview_html( $post_id, $module_id, $module_data, (int) $index );
        }

        $this->send_no_cache_response_headers();
        if ( ! headers_sent() ) {
            header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        }
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        wp_die();
    }

    /**
     * 清洗待保存/预览的模块数据
     *
     * @param array<int,mixed> $modules modules
     * @param bool             $sanitize_data whether sanitize data values
     * @return array<int,array<string,mixed>>
     */
    private function normalize_modules_for_storage( $modules, $sanitize_data = true ) {
        return $this->get_modules_service()->normalize_modules_for_storage(
            $modules,
            array(
                'sanitize_data' => $sanitize_data,
                'module_exists_callback' => function ( $module_id ) {
                    return (bool) Module_Manager::get_instance()->get_module( $module_id );
                },
                'schema_for_type_callback' => array( $this, 'get_module_storage_schema' ),
                'is_shop_module_callback' => array( $this->get_qilingshop_service(), 'is_shop_module_type' ),
                'bootstrap_shop_modules_callback' => array( $this->get_qilingshop_service(), 'bootstrap_modules' ),
            )
        );
    }

    /**
     * Validate builder modules payload before preview/save work.
     *
     * @param string           $raw_payload Raw JSON payload.
     * @param array<int,mixed> $modules Parsed modules.
     * @return true|\WP_Error
     */
    private function validate_builder_modules_payload( $raw_payload, $modules ) {
        if ( strlen( (string) $raw_payload ) > self::MAX_BUILDER_PAYLOAD_BYTES ) {
            return new \WP_Error( 'builder_payload_too_large', __( '装修数据过大，请减少模块或复杂内容后再试。', 'developer-starter' ) );
        }

        if ( count( $modules ) > self::MAX_BUILDER_MODULES ) {
            return new \WP_Error(
                'builder_too_many_modules',
                sprintf(
                    /* translators: %d: max builder module count */
                    __( '当前页面模块超过上限（最多 %d 个），请减少模块后再保存或预览。', 'developer-starter' ),
                    self::MAX_BUILDER_MODULES
                )
            );
        }

        foreach ( $modules as $module ) {
            if ( ! is_array( $module ) ) {
                continue;
            }

            if ( isset( $module['data'] ) && is_array( $module['data'] ) ) {
                $module_data = $module['data'];
            } elseif ( isset( $module['settings'] ) && is_array( $module['settings'] ) ) {
                $module_data = $module['settings'];
            } else {
                $module_data = $module;
                unset( $module_data['type'], $module_data['schemaVersion'], $module_data['schema_version'] );
            }

            $module_json = wp_json_encode( $module_data );
            if ( is_string( $module_json ) && strlen( $module_json ) > self::MAX_BUILDER_MODULE_DATA_BYTES ) {
                return new \WP_Error( 'builder_module_data_too_large', __( '某个模块的数据过大，请减少该模块中的列表项、图片或长文本后再试。', 'developer-starter' ) );
            }
        }

        return true;
    }

    /**
     * 构建模块预览 HTML
     *
     * @param int                 $post_id post id
     * @param string              $module_id module id
     * @param array<string,mixed> $module_data module data
     * @param int                 $index builder index
     * @return string
     */
    private function build_module_preview_html( $post_id, $module_id, $module_data, $index ) {
        return $this->get_modules_service()->build_module_preview_html( $post_id, $module_id, $module_data, $index );
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
     * 获取前台装修外部资源服务。
     *
     * @return Frontend_Builder_Assets_Service
     */
    private function get_assets_service() {
        if ( null === $this->assets_service ) {
            $this->assets_service = new Frontend_Builder_Assets_Service();
        }

        return $this->assets_service;
    }

    /**
     * 获取启灵积分商城适配服务。
     *
     * @return Frontend_Builder_QilingShop_Service
     */
    private function get_qilingshop_service() {
        if ( null === $this->qilingshop_service ) {
            $this->qilingshop_service = new Frontend_Builder_QilingShop_Service();
        }

        return $this->qilingshop_service;
    }

    /**
     * 获取模块库/模板库服务。
     *
     * @return Frontend_Builder_Library_Service
     */
    private function get_library_service() {
        if ( null === $this->library_service ) {
            $this->library_service = new Frontend_Builder_Library_Service( $this->get_qilingshop_service() );
        }

        return $this->library_service;
    }

    /**
     * 获取模块 schema / 预览服务。
     *
     * @return Frontend_Builder_Modules_Service
     */
    private function get_modules_service() {
        if ( null === $this->modules_service ) {
            $this->modules_service = new Frontend_Builder_Modules_Service( $this->get_builder_data_service() );
        }

        return $this->modules_service;
    }

    /**
     * 获取前台装修快照服务。
     *
     * @return Frontend_Builder_Snapshot_Service
     */
    private function get_snapshot_service() {
        if ( null === $this->snapshot_service ) {
            $this->snapshot_service = new Frontend_Builder_Snapshot_Service();
        }

        return $this->snapshot_service;
    }

    /**
     * 获取模块保存清洗 schema。
     *
     * @param string $module_id 模块 ID。
     * @return array<string,array<string,mixed>>
     */
    private function get_module_storage_schema( $module_id ) {
        $module_id = sanitize_key( (string) $module_id );
        if ( '' === $module_id ) {
            return array();
        }

        $module = Module_Manager::get_instance()->get_module( $module_id );
        if ( ! $module || ! method_exists( $module, 'get_fields' ) ) {
            return array();
        }

        $fields = $module->get_fields();
        return $this->get_builder_data_service()->build_module_data_schema_map( is_array( $fields ) ? $fields : array() );
    }

}
