<?php
/**
 * 页面数据包导出服务
 *
 * 负责导出页列表过滤与多页包 JSON 组装。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Export_Service {

    /**
     * 页面辅助服务。
     *
     * @var Page_Package_Page_Service
     */
    private $page_service;

    /**
     * 诊断服务。
     *
     * @var Page_Package_Diagnostics_Service
     */
    private $diagnostics_service;

    /**
     * 预览服务。
     *
     * @var Page_Package_Preview_Service|null
     */
    private $preview_service;

    /**
     * 服务配置。
     *
     * @var array<string,mixed>
     */
    private $config = array();

    /**
     * Builder 数据服务。
     *
     * @var Builder_Data_Service|null
     */
    private $builder_data_service = null;

    /**
     * 构造函数。
     *
     * @param Page_Package_Page_Service              $page_service        页面服务。
     * @param Page_Package_Diagnostics_Service       $diagnostics_service 诊断服务。
     * @param Page_Package_Preview_Service|null      $preview_service     预览服务。
     * @param array<string,mixed>                    $config              运行配置。
     */
    public function __construct( $page_service, $diagnostics_service, $preview_service = null, $config = array() ) {
        $this->page_service        = $page_service;
        $this->diagnostics_service = $diagnostics_service;
        $this->preview_service     = $preview_service;
        $this->config              = wp_parse_args(
            is_array( $config ) ? $config : array(),
            array(
                'package_type'              => 'developer_starter_site_package',
                'package_version'           => 1,
                'theme_identifier'          => 'qiling',
                'reserved_page_definitions' => array(),
                'callbacks'                 => array(),
            )
        );
    }

    /**
     * 获取可导出的装修页面列表。
     *
     * @return array<int,array<string,mixed>>
     */
    public function get_exportable_pages() {
        if ( $this->preview_service ) {
            $this->preview_service->cleanup_expired_preview_pages();
        }

        $pages = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            )
        );

        $exportable_pages = array();
        foreach ( $pages as $page ) {
            if ( ! $page instanceof \WP_Post || 'page' !== $page->post_type ) {
                continue;
            }

            if ( '1' === (string) get_post_meta( $page->ID, '_qiling_site_package_preview', true ) ) {
                continue;
            }

            if ( $this->page_service->is_reserved_page_slug( $page->post_name, $this->get_reserved_page_definitions() ) ) {
                continue;
            }

            $template = function_exists( 'get_page_template_slug' ) ? get_page_template_slug( $page->ID ) : '';
            if ( ! is_string( $template ) || '' === trim( $template ) ) {
                $template = (string) get_post_meta( $page->ID, '_wp_page_template', true );
            }

            $template = $this->page_service->normalize_page_template( $template );
            if ( $this->page_service->is_reserved_page_template( $template, $this->get_reserved_page_definitions() ) ) {
                continue;
            }

            if ( ! $this->page_service->is_allowed_public_page_template( $template ) ) {
                continue;
            }

            $modules = $this->get_page_modules_for_package( $page->ID );
            if ( empty( $modules ) ) {
                continue;
            }

            $exportable_pages[] = array(
                'id'             => absint( $page->ID ),
                'title'          => get_the_title( $page->ID ),
                'slug'           => $page->post_name,
                'post_status'    => $page->post_status,
                'template'       => $template,
                'template_label' => $this->page_service->get_template_label( $template ),
                'module_count'   => count( $modules ),
            );
        }

        return $exportable_pages;
    }

    /**
     * 导出多页面数据包。
     *
     * @param array<int,mixed>    $page_ids 页面 ID 列表。
     * @param array<string,mixed> $options  导出配置。
     * @return array<string,mixed>|\WP_Error
     */
    public function export_site_package( $page_ids, $options = array() ) {
        $page_ids = array_values( array_unique( array_filter( array_map( 'absint', is_array( $page_ids ) ? $page_ids : array() ) ) ) );
        if ( empty( $page_ids ) ) {
            return new \WP_Error( 'empty_export_pages', __( '请先选择至少一个页面再导出数据包。', 'developer-starter' ) );
        }

        $options            = $this->normalize_export_options( $options );
        $exported_pages     = array();
        $page_key_index     = array();
        $export_warnings    = array();

        foreach ( $page_ids as $page_id ) {
            $page_payload = $this->build_export_page_payload( $page_id, $page_key_index, $export_warnings );
            if ( is_wp_error( $page_payload ) ) {
                $export_warnings[] = $page_payload->get_error_message();
                continue;
            }

            if ( empty( $page_payload ) || ! is_array( $page_payload ) ) {
                continue;
            }

            $exported_pages[] = $page_payload;
        }

        if ( empty( $exported_pages ) ) {
            return new \WP_Error( 'empty_export_payload', __( '所选页面没有可导出的模块数据。', 'developer-starter' ) );
        }

        $site_options  = array();
        $front_page_id = absint( get_option( 'page_on_front', 0 ) );
        if ( get_option( 'show_on_front', 'posts' ) === 'page' && $front_page_id > 0 ) {
            foreach ( $exported_pages as $page_payload ) {
                if ( ! empty( $page_payload['_source_post_id'] ) && absint( $page_payload['_source_post_id'] ) === $front_page_id && ! empty( $page_payload['page_key'] ) ) {
                    $site_options['front_page'] = sanitize_key( (string) $page_payload['page_key'] );
                    break;
                }
            }
        }
        $posts_page_id = absint( get_option( 'page_for_posts', 0 ) );
        if ( $posts_page_id > 0 ) {
            foreach ( $exported_pages as $page_payload ) {
                if ( ! empty( $page_payload['_source_post_id'] ) && absint( $page_payload['_source_post_id'] ) === $posts_page_id && ! empty( $page_payload['page_key'] ) ) {
                    $site_options['posts_page'] = sanitize_key( (string) $page_payload['page_key'] );
                    break;
                }
            }
        }

        if ( ! empty( $options['include_site_identity'] ) ) {
            $site_options['site_title'] = get_bloginfo( 'name' );
            $site_options['tagline']    = get_bloginfo( 'description' );
        }

        $page_id_to_key = array();
        foreach ( $exported_pages as $page_payload ) {
            if ( ! empty( $page_payload['_source_post_id'] ) && ! empty( $page_payload['page_key'] ) ) {
                $page_id_to_key[ absint( $page_payload['_source_post_id'] ) ] = sanitize_key( (string) $page_payload['page_key'] );
            }
        }

        $design_system  = ! empty( $options['include_design_system'] ) ? $this->build_export_design_system_payload() : array();
        $content_models = ! empty( $options['include_content_models'] ) ? $this->build_export_content_models_payload() : array();
        $navigation     = ! empty( $options['include_navigation'] ) ? $this->build_export_navigation_payload( $page_id_to_key, $export_warnings ) : array();
        $site_assets    = ! empty( $options['include_site_assets'] ) ? $this->build_export_site_assets_payload() : array();

        foreach ( $exported_pages as $index => $page_payload ) {
            unset( $exported_pages[ $index ]['_source_post_id'] );
        }
        $exported_pages = array_values( $exported_pages );

        $features = array( 'pages' );
        if ( ! empty( $design_system ) ) {
            $features[] = 'design_system';
            if ( ! empty( $design_system['design_system_v2'] ) && is_array( $design_system['design_system_v2'] ) ) {
                $features[] = 'design_system_v2';
            }
        }
        if ( ! empty( $content_models ) ) {
            $features[] = 'content_models';
        }
        if ( ! empty( $navigation ) ) {
            $features[] = 'navigation';
        }
        if ( ! empty( $site_assets ) ) {
            $features[] = 'site_assets';
        }

        $payload = array(
            'package_type'      => (string) $this->config['package_type'],
            'version'           => absint( $this->config['package_version'] ),
            'module_schema_version' => $this->get_builder_data_service()->get_module_data_schema_version(),
            'builder_protocol_version' => $this->get_builder_data_service()->get_builder_protocol_version(),
            'scope'             => $options['scope'],
            'manifest'          => $this->diagnostics_service->normalize_package_manifest(
                array(
                    'generated_at' => gmdate( 'c' ),
                    'features'     => $features,
                ),
                $options['scope'],
                $features
            ),
            'package_id'        => $this->page_service->normalize_package_id( $options['package_id'], $options['title'] ),
            'title'             => $options['title'],
            'theme'             => (string) $this->config['theme_identifier'],
            'min_theme_version' => $options['min_theme_version'],
            'author'            => $options['author'],
            'description'       => $options['description'],
            'cover'             => $options['cover'],
            'categories'        => $options['categories'],
            'tags'              => $options['tags'],
            'dependencies'      => $options['dependencies'],
            'pages'             => $exported_pages,
            'site_options'      => $site_options,
        );

        if ( ! empty( $design_system ) ) {
            $payload['design_system'] = $design_system;
            if ( ! empty( $design_system['design_system_v2'] ) && is_array( $design_system['design_system_v2'] ) ) {
                $payload['design_system_v2'] = $design_system['design_system_v2'];
            }
        }
        if ( ! empty( $content_models ) ) {
            $payload['content_models'] = $content_models;
        }
        if ( ! empty( $navigation ) ) {
            $payload['navigation'] = $navigation;
        }
        if ( ! empty( $site_assets ) ) {
            $payload['site_assets'] = $site_assets;
        }

        $json = wp_json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ( ! is_string( $json ) || '' === $json ) {
            return new \WP_Error( 'export_encode_failed', __( '数据包导出失败，JSON 编码未成功。', 'developer-starter' ) );
        }

        return array(
            'payload'        => $payload,
            'json'           => $json,
            'warnings'       => array_values( array_unique( array_filter( array_map( 'strval', $export_warnings ) ) ) ),
            'selected_count' => count( $page_ids ),
            'exported_count' => count( $exported_pages ),
            'scope'          => $payload['scope'],
            'filename'       => sanitize_file_name( $payload['package_id'] . '.json' ),
        );
    }

    /**
     * 规范化导出参数。
     *
     * @param array<string,mixed> $options 原始参数。
     * @return array<string,mixed>
     */
    private function normalize_export_options( $options ) {
        $title = isset( $options['title'] ) && is_scalar( $options['title'] )
            ? sanitize_text_field( (string) $options['title'] )
            : __( '未命名页面数据包', 'developer-starter' );

        if ( '' === $title ) {
            $title = __( '未命名页面数据包', 'developer-starter' );
        }

        $scope = isset( $options['scope'] ) ? $this->diagnostics_service->normalize_package_scope( $options['scope'] ) : 'page';

        return array(
            'title'             => $title,
            'scope'             => $scope,
            'package_id'        => $this->page_service->normalize_package_id(
                isset( $options['package_id'] ) ? $options['package_id'] : '',
                $title
            ),
            'min_theme_version' => isset( $options['min_theme_version'] ) && is_scalar( $options['min_theme_version'] ) && trim( (string) $options['min_theme_version'] ) !== ''
                ? sanitize_text_field( (string) $options['min_theme_version'] )
                : ( defined( 'DEVELOPER_STARTER_VERSION' ) ? (string) DEVELOPER_STARTER_VERSION : '1.0.0' ),
            'author'            => $this->diagnostics_service->normalize_package_author( isset( $options['author'] ) ? $options['author'] : '' ),
            'description'       => isset( $options['description'] ) && is_scalar( $options['description'] ) ? sanitize_textarea_field( (string) $options['description'] ) : '',
            'cover'             => isset( $options['cover'] ) && is_scalar( $options['cover'] ) ? esc_url_raw( (string) $options['cover'] ) : '',
            'categories'        => $this->diagnostics_service->normalize_string_list( isset( $options['categories'] ) ? $options['categories'] : array() ),
            'tags'              => $this->diagnostics_service->normalize_string_list( isset( $options['tags'] ) ? $options['tags'] : array() ),
            'dependencies'      => $this->diagnostics_service->normalize_package_dependencies( isset( $options['dependencies'] ) ? $options['dependencies'] : array() ),
            'include_design_system'  => 'site' === $scope && ! empty( $options['include_design_system'] ),
            'include_content_models' => 'site' === $scope && ! empty( $options['include_content_models'] ),
            'include_navigation'     => 'site' === $scope && ! empty( $options['include_navigation'] ),
            'include_site_identity'  => 'site' === $scope && ! empty( $options['include_site_identity'] ),
            'include_site_assets'    => 'site' === $scope && ! empty( $options['include_site_assets'] ),
        );
    }

    /**
     * 构建导出页面条目。
     *
     * @param int                $page_id        页面 ID。
     * @param array<string,bool> $page_key_index 页面 key 去重映射。
     * @param array<int,string>  $warnings       预警收集器。
     * @return array<string,mixed>|\WP_Error
     */
    private function build_export_page_payload( $page_id, &$page_key_index, &$warnings ) {
        $post = get_post( $page_id );
        if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
            return new \WP_Error(
                'invalid_export_page',
                sprintf(
                    /* translators: %d: page id */
                    __( '页面 ID %d 不存在或不是有效页面。', 'developer-starter' ),
                    $page_id
                )
            );
        }

        if ( $this->page_service->is_reserved_page_slug( $post->post_name, $this->get_reserved_page_definitions() ) ) {
            return new \WP_Error(
                'reserved_export_page',
                sprintf(
                    /* translators: %s: page title */
                    __( '页面“%s”属于系统保留页，不参与多页包导出。', 'developer-starter' ),
                    get_the_title( $page_id )
                )
            );
        }

        $template = function_exists( 'get_page_template_slug' ) ? get_page_template_slug( $page_id ) : '';
        if ( ! is_string( $template ) || '' === trim( $template ) ) {
            $template = (string) get_post_meta( $page_id, '_wp_page_template', true );
        }
        $template = $this->page_service->normalize_page_template( $template );

        if ( $this->page_service->is_reserved_page_template( $template, $this->get_reserved_page_definitions() ) ) {
            return new \WP_Error(
                'reserved_export_template',
                sprintf(
                    /* translators: %s: page title */
                    __( '页面“%s”使用了系统模板，不参与多页包导出。', 'developer-starter' ),
                    get_the_title( $page_id )
                )
            );
        }

        if ( ! $this->page_service->is_allowed_public_page_template( $template ) ) {
            return new \WP_Error(
                'unsupported_export_template',
                sprintf(
                    /* translators: %s: page title */
                    __( '页面“%s”不是启灵主题的模块装修模板，不参与多页包导出。', 'developer-starter' ),
                    get_the_title( $page_id )
                )
            );
        }

        $modules = $this->get_page_modules_for_package( $page_id );
        if ( empty( $modules ) ) {
            return new \WP_Error(
                'empty_export_modules',
                sprintf(
                    /* translators: %s: page title */
                    __( '页面“%s”不是纯模块装修页，或没有可导出的模块数据。', 'developer-starter' ),
                    get_the_title( $page_id )
                )
            );
        }

        $page_key = sanitize_key( (string) get_post_meta( $page_id, '_qiling_site_package_page_key', true ) );
        if ( '' === $page_key ) {
            $page_key = sanitize_key( str_replace( '-', '_', $post->post_name ) );
        }
        if ( '' === $page_key ) {
            $page_key = 'page_' . $page_id;
        }
        $page_key = $this->build_unique_export_page_key( $page_key, $page_key_index );

        return array(
            '_source_post_id' => $page_id,
            'page_key'        => $page_key,
            'title'           => get_the_title( $page_id ),
            'slug'            => $post->post_name,
            'template'        => $template,
            'post_status'     => $post->post_status,
            'settings'        => array(
                'hide_page_header'     => $this->page_service->normalize_bool_value( get_post_meta( $page_id, '_qiling_hide_page_header', true ), false ),
                'transparent_header'   => $this->page_service->normalize_bool_value( get_post_meta( $page_id, '_qiling_transparent_header', true ), false ),
                'enable_scroll_reveal' => $this->page_service->normalize_bool_value( get_post_meta( $page_id, '_developer_starter_enable_scroll_reveal', true ), false ),
                'region_decoration'    => $this->build_page_region_decoration_payload( $page_id ),
                'page_design'          => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? Design_Tokens::get_page_design_overrides( $page_id, 'package' )
                    : array(),
            ),
            'modules'         => $modules,
        );
    }

    /**
     * 将装修源页面 ID 转为可跨站迁移的页面键。
     *
     * @param int $page_id 页面 ID。
     * @return array<string,mixed>
     */
    private function build_page_region_decoration_payload( $page_id ) {
        if ( ! function_exists( 'developer_starter_get_post_page_region_decoration' ) ) {
            return array();
        }

        $settings = developer_starter_get_post_page_region_decoration( $page_id );
        foreach ( $settings as $region => $region_settings ) {
            if ( 'custom' !== $region_settings['mode'] || empty( $region_settings['page_id'] ) ) {
                continue;
            }
            $source_id = absint( $region_settings['page_id'] );
            $source = get_post( $source_id );
            $source_key = sanitize_key( (string) get_post_meta( $source_id, '_qiling_site_package_page_key', true ) );
            if ( '' === $source_key && $source instanceof \WP_Post ) {
                $source_key = sanitize_key( str_replace( '-', '_', $source->post_name ) );
            }
            $settings[ $region ]['page_id'] = 0;
            $settings[ $region ]['page_key'] = $source_key;
        }

        return $settings;
    }

    /**
     * 构建全局样式导出片段。
     *
     * @return array<string,mixed>
     */
    private function build_export_design_system_payload() {
        $theme_options = get_option( 'developer_starter_options', array() );
        $theme_options = is_array( $theme_options ) ? $theme_options : array();
        $storage_key   = class_exists( '\Developer_Starter\Core\Design_Tokens' )
            ? Design_Tokens::STORAGE_OPTION_KEY
            : 'design_system_v2';
        $design_system_v2 = class_exists( '\Developer_Starter\Core\Design_Tokens' )
            ? Design_Tokens::get_storage_payload( $theme_options )
            : (
                isset( $theme_options[ $storage_key ] ) && is_array( $theme_options[ $storage_key ] )
                    ? $theme_options[ $storage_key ]
                    : array()
            );
        $design_options = class_exists( '\Developer_Starter\Core\Design_Tokens' )
            ? Design_Tokens::get_compatibility_option_payload( $design_system_v2, $theme_options )
            : $this->pick_theme_options(
                $theme_options,
                array(
                    'design_enable_global_tokens',
                    'design_preset',
                    'design_primary_color',
                    'design_secondary_color',
                    'design_accent_color',
                    'design_text_color',
                    'design_text_muted_color',
                    'design_heading_color',
                    'design_background_color',
                    'design_surface_color',
                    'design_surface_alt_color',
                    'design_border_color',
                    'design_font_family',
                    'design_font_size_base',
                    'design_line_height_base',
                    'design_container_width',
                    'design_section_padding',
                    'design_card_radius',
                    'design_button_radius',
                    'design_input_radius',
                    'design_animation_speed',
                    'design_shadow_sm',
                    'design_shadow_md',
                    'design_shadow_lg',
                    'design_dark_bg',
                    'design_dark_surface',
                    'design_dark_text',
                    'design_dark_text_muted',
                    'design_dark_border',
                    'design_component_button_bg',
                    'design_component_button_text',
                    'design_component_button_border',
                    'design_component_button_hover_bg',
                    'design_component_button_hover_text',
                    'design_component_button_secondary_bg',
                    'design_component_button_secondary_text',
                    'design_component_button_secondary_border',
                    'design_component_button_secondary_hover_bg',
                    'design_component_button_shadow',
                    'design_component_button_padding',
                    'design_component_heading_weight',
                    'design_component_heading_letter_spacing',
                    'design_component_card_bg',
                    'design_component_card_border',
                    'design_component_card_shadow',
                    'design_component_form_input_bg',
                    'design_component_form_input_text',
                    'design_component_form_input_border',
                    'design_component_form_focus_border',
                    'design_component_auth_action_bg',
                    'design_component_auth_action_text',
                    'design_component_auth_code_bg',
                    'design_component_auth_code_text',
                    'design_component_auth_slider_track_bg',
                    'design_component_auth_slider_handle_bg',
                    'design_component_auth_slider_progress_bg',
                    'design_component_auth_verified_color',
                    'design_component_module_title_color',
                    'design_component_module_title_size',
                    'design_component_module_title_align',
                    'design_component_post_card_bg',
                    'design_component_post_card_border',
                    'design_component_post_card_shadow',
                    'design_component_post_card_title_color',
                    'design_component_post_card_meta_color',
                    'primary_color',
                )
            );

        $payload = class_exists( '\Developer_Starter\Core\Design_Tokens' )
            ? Design_Tokens::get_client_payload( $theme_options )
            : array();

        return array(
            'storage_key'    => $storage_key,
            'schema_version' => isset( $payload['schemaVersion'] ) ? (string) $payload['schemaVersion'] : '',
            'schemaVersion'  => isset( $payload['schemaVersion'] ) ? (string) $payload['schemaVersion'] : '',
            'enabled'        => isset( $payload['enabled'] ) ? (bool) $payload['enabled'] : true,
            'preset'         => isset( $payload['preset'] ) ? (string) $payload['preset'] : '',
            'preset_label'   => isset( $payload['presetLabel'] ) ? (string) $payload['presetLabel'] : '',
            'tokens'         => isset( $payload['tokens'] ) && is_array( $payload['tokens'] ) ? $payload['tokens'] : array(),
            'typography_system' => isset( $design_system_v2['typography_system'] ) && is_array( $design_system_v2['typography_system'] ) ? $design_system_v2['typography_system'] : array(),
            'layout_system'  => isset( $design_system_v2['layout_system'] ) && is_array( $design_system_v2['layout_system'] ) ? $design_system_v2['layout_system'] : array(),
            'component_styles' => isset( $payload['componentStyles'] ) && is_array( $payload['componentStyles'] ) ? $payload['componentStyles'] : array(),
            'component_css_variables' => isset( $payload['componentCssVariables'] ) && is_array( $payload['componentCssVariables'] ) ? $payload['componentCssVariables'] : array(),
            'custom_presets' => isset( $design_system_v2['custom_presets'] ) && is_array( $design_system_v2['custom_presets'] ) ? $design_system_v2['custom_presets'] : array(),
            'design_system_v2' => $design_system_v2,
            'options'        => $design_options,
        );
    }

    /**
     * 构建内容模型中心导出片段。
     *
     * @return array<string,mixed>
     */
    private function build_export_content_models_payload() {
        $theme_options = get_option( 'developer_starter_options', array() );
        $theme_options = is_array( $theme_options ) ? $theme_options : array();
        $model_options = $this->pick_theme_options(
            $theme_options,
            array(
                'content_model_center_enable',
                'local_business_features_enable',
                'content_model_enabled_models',
                'content_model_archive_base',
                'content_model_archive_enable',
                'content_model_rest_enable',
                'content_model_meta_box_enable',
            )
        );

        $payload = class_exists( '\Developer_Starter\Core\Content_Model_Center' )
            ? Content_Model_Center::get_client_payload( $theme_options )
            : array();

        return array(
            'schema_version'    => isset( $payload['schemaVersion'] ) ? (string) $payload['schemaVersion'] : '',
            'enabled'           => isset( $payload['enabled'] ) ? (bool) $payload['enabled'] : true,
            'enabled_model_ids' => isset( $payload['enabledModelIds'] ) && is_array( $payload['enabledModelIds'] ) ? array_values( $payload['enabledModelIds'] ) : array(),
            'archive_base'      => isset( $payload['archiveBase'] ) ? (string) $payload['archiveBase'] : '',
            'models'            => isset( $payload['models'] ) && is_array( $payload['models'] ) ? $payload['models'] : array(),
            'options'           => $model_options,
        );
    }

    /**
     * 构建导航菜单导出片段。
     *
     * @param array<int,string> $page_id_to_key 页面 ID 到 page_key。
     * @param array<int,string> $warnings       导出预警。
     * @return array<string,mixed>
     */
    private function build_export_navigation_payload( $page_id_to_key, &$warnings ) {
        if ( empty( $page_id_to_key ) || ! function_exists( 'get_nav_menu_locations' ) ) {
            return array();
        }

        $locations = get_nav_menu_locations();
        if ( empty( $locations ) || ! is_array( $locations ) ) {
            return array();
        }

        $menus = array();
        foreach ( $locations as $location => $menu_id ) {
            $location = sanitize_key( (string) $location );
            $menu_id  = absint( $menu_id );
            if ( '' === $location || $menu_id <= 0 ) {
                continue;
            }

            $menu_items = wp_get_nav_menu_items( $menu_id );
            if ( empty( $menu_items ) || ! is_array( $menu_items ) ) {
                continue;
            }

            $items = array();
            foreach ( $menu_items as $menu_item ) {
                if ( ! $menu_item instanceof \WP_Post ) {
                    continue;
                }

                $item = array(
                    'item_key' => sanitize_key( 'item_' . $menu_item->ID ),
                    'label'    => sanitize_text_field( $menu_item->title ),
                    'target'   => '_blank' === (string) $menu_item->target ? '_blank' : '',
                    'classes'  => is_array( $menu_item->classes ) ? array_values( array_filter( array_map( 'sanitize_html_class', $menu_item->classes ) ) ) : array(),
                );

                $object_id = absint( $menu_item->object_id );
                if ( 'post_type' === $menu_item->type && 'page' === $menu_item->object && isset( $page_id_to_key[ $object_id ] ) ) {
                    $item['page_key'] = $page_id_to_key[ $object_id ];
                } elseif ( 'custom' === $menu_item->type && ! empty( $menu_item->url ) ) {
                    $item['url'] = esc_url_raw( (string) $menu_item->url );
                } else {
                    $warnings[] = sprintf(
                        /* translators: %s: menu item title */
                        __( '菜单项“%s”不在当前导出页面范围内，已从整站包导航中跳过。', 'developer-starter' ),
                        sanitize_text_field( $menu_item->title )
                    );
                    continue;
                }

                $items[] = $item;
            }

            if ( empty( $items ) ) {
                continue;
            }

            $menu_object = wp_get_nav_menu_object( $menu_id );
            $menus[] = array(
                'menu_key' => $location,
                'name'     => $menu_object instanceof \WP_Term ? $menu_object->name : $location,
                'location' => $location,
                'items'    => $items,
            );
        }

        return empty( $menus ) ? array() : array( 'menus' => $menus );
    }


    /**
     * 构建主题资源清单。
     *
     * @return array<string,mixed>
     */
    private function build_export_site_assets_payload() {
        $assets = array(
            array(
                'type'     => 'css',
                'handle'   => 'theme-style',
                'path'     => 'style.css',
                'required' => true,
            ),
            array(
                'type'     => 'css',
                'handle'   => 'modules',
                'path'     => 'assets/css/modules.css',
                'required' => true,
            ),
            array(
                'type'     => 'css',
                'handle'   => 'modules-split',
                'path'     => 'assets/css/modules-split/',
                'required' => false,
            ),
        );

        return array(
            'template_assets' => $assets,
            'media'           => array(),
            'notes'           => array(
                __( '本地整站包只记录资源清单，不复制媒体库文件；图片字段保留原 URL 或相对路径。', 'developer-starter' ),
            ),
        );
    }

    /**
     * 从主题设置中摘取白名单字段。
     *
     * @param array<string,mixed> $options 主题设置。
     * @param array<int,string>   $keys    字段。
     * @return array<string,mixed>
     */
    private function pick_theme_options( $options, $keys ) {
        $picked = array();
        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $options ) ) {
                $picked[ $key ] = $options[ $key ];
            }
        }

        return $picked;
    }

    /**
     * 获取页面的模块数据，仅接受启灵主题自己的装修模块。
     *
     * @param int $page_id 页面 ID。
     * @return array<int,array<string,mixed>>
     */
    private function get_page_modules_for_package( $page_id ) {
        $callback = $this->get_callback( 'get_page_modules_for_package' );
        if ( $callback ) {
            $modules = call_user_func( $callback, $page_id );
            return is_array( $modules )
                ? $this->get_builder_data_service()->normalize_modules_for_storage(
                    $modules,
                    array(
                        'sanitize_data' => false,
                    )
                )
                : array();
        }

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $page_id )
            : get_post_meta( $page_id, '_developer_starter_modules', true );

        if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
            $modules = developer_starter_normalize_modules_meta_types( $modules );
        }

        if ( ! is_array( $modules ) ) {
            return array();
        }

        return $this->get_builder_data_service()->normalize_modules_for_storage(
            $modules,
            array(
                'sanitize_data' => false,
            )
        );
    }

    /**
     * 获取 Builder 数据服务。
     *
     * @return Builder_Data_Service
     */
    private function get_builder_data_service() {
        if ( ! $this->builder_data_service instanceof Builder_Data_Service ) {
            $this->builder_data_service = new Builder_Data_Service();
        }

        return $this->builder_data_service;
    }

    /**
     * 为导出页面生成唯一 page_key。
     *
     * @param string             $page_key       原始 key。
     * @param array<string,bool> $page_key_index 已占用映射。
     * @return string
     */
    private function build_unique_export_page_key( $page_key, &$page_key_index ) {
        $page_key = sanitize_key( (string) $page_key );
        if ( '' === $page_key ) {
            $page_key = 'page';
        }

        $base_key = $page_key;
        $suffix   = 2;
        while ( isset( $page_key_index[ $page_key ] ) ) {
            $page_key = sanitize_key( $base_key . '_' . $suffix );
            $suffix++;
        }

        $page_key_index[ $page_key ] = true;
        return $page_key;
    }

    /**
     * 获取系统保留页定义。
     *
     * @return array<int,array<string,mixed>>
     */
    private function get_reserved_page_definitions() {
        return isset( $this->config['reserved_page_definitions'] ) && is_array( $this->config['reserved_page_definitions'] )
            ? $this->config['reserved_page_definitions']
            : array();
    }

    /**
     * 获取回调。
     *
     * @param string $key 回调键名。
     * @return callable|null
     */
    private function get_callback( $key ) {
        if ( empty( $this->config['callbacks'][ $key ] ) || ! is_callable( $this->config['callbacks'][ $key ] ) ) {
            return null;
        }

        return $this->config['callbacks'][ $key ];
    }
}
