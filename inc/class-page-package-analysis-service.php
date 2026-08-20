<?php
/**
 * 页面数据包分析服务
 *
 * 负责多页面 JSON 数据包的解析、页面条目规范化与单页预检报告生成。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Analysis_Service {

    /**
     * 多页包模块导入服务。
     *
     * @var Page_Package_Module_Service
     */
    private $module_service;

    /**
     * 多页包页面辅助服务。
     *
     * @var Page_Package_Page_Service
     */
    private $page_service;

    /**
     * 多页包诊断服务。
     *
     * @var Page_Package_Diagnostics_Service
     */
    private $diagnostics_service;

    /**
     * 服务配置。
     *
     * @var array<string,mixed>
     */
    private $config = array();

    /**
     * 构造函数。
     *
     * @param Page_Package_Module_Service      $module_service      模块服务。
     * @param Page_Package_Page_Service        $page_service        页面服务。
     * @param Page_Package_Diagnostics_Service $diagnostics_service 诊断服务。
     * @param array<string,mixed>              $config              运行配置。
     */
    public function __construct( $module_service, $page_service, $diagnostics_service, $config = array() ) {
        $this->module_service      = $module_service;
        $this->page_service        = $page_service;
        $this->diagnostics_service = $diagnostics_service;
        $this->config              = wp_parse_args(
            is_array( $config ) ? $config : array(),
            array(
                'package_type'                => 'developer_starter_site_package',
                'package_version'             => 1,
                'max_package_bytes'           => 2097152,
                'max_package_pages'           => 50,
                'conflict_strategy_skip'      => 'skip_existing',
                'conflict_strategy_duplicate' => 'create_with_new_slug',
                'conflict_strategy_update'    => 'update_same_package',
                'reserved_page_definitions'   => array(),
            )
        );
    }

    /**
     * 解析 JSON 并做基础结构校验。
     *
     * @param string $raw_json 原始 JSON。
     * @return array<string,mixed>|\WP_Error
     */
    public function parse_site_package( $raw_json ) {
        $raw_json = is_string( $raw_json ) ? trim( $raw_json ) : '';
        if ( '' === $raw_json ) {
            return new \WP_Error( 'empty_json', __( 'JSON 内容为空。', 'developer-starter' ) );
        }

        $payload_bytes = strlen( $raw_json );
        if ( $payload_bytes > absint( $this->config['max_package_bytes'] ) ) {
            return new \WP_Error(
                'package_too_large',
                sprintf(
                    /* translators: %s: max size */
                    __( '当前数据包体积超过安全上限（最大 %s），请拆分后再导入。', 'developer-starter' ),
                    size_format( absint( $this->config['max_package_bytes'] ) )
                )
            );
        }

        $decoded = json_decode( $raw_json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_json', __( 'JSON 格式错误，请检查文件内容。', 'developer-starter' ) );
        }

        $package_type = isset( $decoded['package_type'] ) ? sanitize_key( (string) $decoded['package_type'] ) : '';
        if ( '' !== $package_type && $package_type !== (string) $this->config['package_type'] ) {
            return new \WP_Error( 'invalid_package_type', __( '当前文件不是启灵主题支持的多页面数据包。', 'developer-starter' ) );
        }

        $package_version = absint( $this->config['package_version'] );
        $version         = isset( $decoded['version'] ) ? absint( $decoded['version'] ) : $package_version;
        if ( $version !== $package_version ) {
            return new \WP_Error(
                'unsupported_package_version',
                sprintf(
                    /* translators: %d: version number */
                    __( '当前仅支持 V%d 协议的多页面数据包。', 'developer-starter' ),
                    $package_version
                )
            );
        }

        $theme = isset( $decoded['theme'] ) ? sanitize_key( (string) $decoded['theme'] ) : '';
        if ( '' !== $theme && ! in_array( $theme, $this->page_service->get_supported_theme_identifiers(), true ) ) {
            return new \WP_Error( 'theme_mismatch', __( '该数据包不是为当前启灵主题构建的。', 'developer-starter' ) );
        }

        $min_theme_version = isset( $decoded['min_theme_version'] ) ? sanitize_text_field( (string) $decoded['min_theme_version'] ) : '';
        if ( '' !== $min_theme_version && defined( 'DEVELOPER_STARTER_VERSION' ) && version_compare( (string) DEVELOPER_STARTER_VERSION, $min_theme_version, '<' ) ) {
            return new \WP_Error(
                'theme_version_too_low',
                sprintf(
                    /* translators: 1: required version 2: current version */
                    __( '当前主题版本过低。数据包要求 %1$s，当前版本为 %2$s。', 'developer-starter' ),
                    $min_theme_version,
                    (string) DEVELOPER_STARTER_VERSION
                )
            );
        }

        $scope = $this->diagnostics_service->normalize_package_scope( isset( $decoded['scope'] ) ? $decoded['scope'] : 'page' );
        $pages = isset( $decoded['pages'] ) && is_array( $decoded['pages'] ) ? $decoded['pages'] : array();
        if ( empty( $pages ) ) {
            return new \WP_Error( 'empty_pages', __( '数据包中没有可导入的页面。', 'developer-starter' ) );
        }

        if ( count( $pages ) > absint( $this->config['max_package_pages'] ) ) {
            return new \WP_Error(
                'too_many_pages',
                sprintf(
                    /* translators: %d: page limit */
                    __( '当前数据包包含的页面数超过安全上限（最多 %d 页），请拆分后再导入。', 'developer-starter' ),
                    absint( $this->config['max_package_pages'] )
                )
            );
        }

        $normalized_pages = array();
        foreach ( $pages as $index => $page ) {
            $normalized_page = $this->normalize_page_entry( $page, $index );
            if ( is_wp_error( $normalized_page ) ) {
                return $normalized_page;
            }

            $normalized_pages[] = $normalized_page;
        }

        $raw_design_system = isset( $decoded['design_system'] ) && is_array( $decoded['design_system'] )
            ? $decoded['design_system']
            : ( isset( $decoded['designSystem'] ) && is_array( $decoded['designSystem'] ) ? $decoded['designSystem'] : array() );
        if ( isset( $decoded['design_system_v2'] ) && is_array( $decoded['design_system_v2'] ) ) {
            $raw_design_system['design_system_v2'] = $decoded['design_system_v2'];
        } elseif ( isset( $decoded['designSystemV2'] ) && is_array( $decoded['designSystemV2'] ) ) {
            $raw_design_system['design_system_v2'] = $decoded['designSystemV2'];
        }
        $design_system = $this->diagnostics_service->normalize_design_system_payload( $raw_design_system );
        $content_models = $this->diagnostics_service->normalize_content_models_payload(
            isset( $decoded['content_models'] ) ? $decoded['content_models'] : ( isset( $decoded['contentModels'] ) ? $decoded['contentModels'] : array() )
        );
        $site_assets = $this->diagnostics_service->normalize_site_assets_payload(
            isset( $decoded['site_assets'] ) ? $decoded['site_assets'] : ( isset( $decoded['siteAssets'] ) ? $decoded['siteAssets'] : array() )
        );
        $features = array( 'pages' );
        if ( ! empty( $design_system ) ) {
            $features[] = 'design_system';
            if ( ! empty( $design_system['design_system_v2'] ) ) {
                $features[] = 'design_system_v2';
            }
        }
        if ( ! empty( $content_models ) ) {
            $features[] = 'content_models';
        }
        if ( ! empty( $site_assets ) ) {
            $features[] = 'site_assets';
        }
        if ( ! empty( $decoded['navigation'] ) || ! empty( $decoded['menus'] ) ) {
            $features[] = 'navigation';
        }

        return array(
            'package_type'      => (string) $this->config['package_type'],
            'version'           => $package_version,
            'module_schema_version' => isset( $decoded['module_schema_version'] ) && is_scalar( $decoded['module_schema_version'] )
                ? sanitize_text_field( (string) $decoded['module_schema_version'] )
                : '',
            'builder_protocol_version' => isset( $decoded['builder_protocol_version'] ) && is_scalar( $decoded['builder_protocol_version'] )
                ? sanitize_text_field( (string) $decoded['builder_protocol_version'] )
                : '',
            'scope'             => $scope,
            'package_id'        => $this->page_service->normalize_package_id(
                isset( $decoded['package_id'] ) ? $decoded['package_id'] : '',
                $raw_json
            ),
            'title'             => isset( $decoded['title'] ) ? sanitize_text_field( (string) $decoded['title'] ) : __( '未命名页面数据包', 'developer-starter' ),
            'theme'             => $theme,
            'min_theme_version' => $min_theme_version,
            'author'            => $this->diagnostics_service->normalize_package_author( isset( $decoded['author'] ) ? $decoded['author'] : '' ),
            'description'       => isset( $decoded['description'] ) && is_scalar( $decoded['description'] ) ? sanitize_textarea_field( (string) $decoded['description'] ) : '',
            'cover'             => isset( $decoded['cover'] ) && is_scalar( $decoded['cover'] ) ? esc_url_raw( (string) $decoded['cover'] ) : '',
            'categories'        => $this->diagnostics_service->normalize_string_list( isset( $decoded['categories'] ) ? $decoded['categories'] : array() ),
            'tags'              => $this->diagnostics_service->normalize_string_list( isset( $decoded['tags'] ) ? $decoded['tags'] : array() ),
            'dependencies'      => $this->diagnostics_service->normalize_package_dependencies( isset( $decoded['dependencies'] ) ? $decoded['dependencies'] : array() ),
            'manifest'          => $this->diagnostics_service->normalize_package_manifest(
                isset( $decoded['manifest'] ) ? $decoded['manifest'] : array(),
                $scope,
                $features
            ),
            'design_system'     => $design_system,
            'design_system_v2'  => ! empty( $design_system['design_system_v2'] ) && is_array( $design_system['design_system_v2'] )
                ? $design_system['design_system_v2']
                : array(),
            'content_models'    => $content_models,
            'site_assets'       => $site_assets,
            'navigation'        => isset( $decoded['navigation'] ) && is_array( $decoded['navigation'] )
                ? $decoded['navigation']
                : ( isset( $decoded['menus'] ) && is_array( $decoded['menus'] ) ? array( 'menus' => $decoded['menus'] ) : array() ),
            'payload_bytes'     => $payload_bytes,
            'pages'             => $normalized_pages,
            'site_options'      => isset( $decoded['site_options'] ) && is_array( $decoded['site_options'] ) ? $decoded['site_options'] : array(),
        );
    }

    /**
     * 构建单页预检报告。
     *
     * @param array<string,mixed> $page             单页条目。
     * @param int                 $index            页面索引。
     * @param array<string,int>   $page_key_index   包内 page_key 去重索引。
     * @param array<string,int>   $page_slug_index  包内 slug 去重索引。
     * @param array<string,mixed> $analysis_context 预检上下文。
     * @return array<string,mixed>
     */
    public function build_page_report( $page, $index, &$page_key_index, &$page_slug_index, $analysis_context = array() ) {
        $page_key          = isset( $page['page_key'] ) ? sanitize_key( (string) $page['page_key'] ) : '';
        $slug              = isset( $page['slug'] ) ? sanitize_title( (string) $page['slug'] ) : '';
        $title             = isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : '';
        $template          = isset( $page['template'] ) ? $this->page_service->normalize_page_template( $page['template'] ) : 'default';
        $package_id        = isset( $analysis_context['package_id'] ) ? sanitize_key( (string) $analysis_context['package_id'] ) : '';
        $conflict_strategy = $this->normalize_conflict_strategy(
            isset( $analysis_context['conflict_strategy'] ) ? $analysis_context['conflict_strategy'] : $this->config['conflict_strategy_skip']
        );

        $report = array(
            'page_key'          => $page_key,
            'title'             => $title,
            'slug'              => $slug,
            'target_slug'       => $slug,
            'template'          => $template,
            'template_label'    => $this->page_service->get_template_label( $template ),
            'module_count'      => 0,
            'existing_page_id'  => 0,
            'action'            => 'blocked',
            'errors'            => array(),
            'warnings'          => array(),
            'style_warnings'    => array(),
            'security_warnings' => array(),
            'prepared_page'     => null,
        );

        if ( isset( $page_key_index[ $page_key ] ) ) {
            $report['errors'][] = sprintf(
                /* translators: %s: page key */
                __( '页面标识 %s 在数据包中重复。', 'developer-starter' ),
                $page_key
            );
            return $report;
        }
        $page_key_index[ $page_key ] = $index;

        if ( isset( $page_slug_index[ $slug ] ) ) {
            $report['errors'][] = sprintf(
                /* translators: %s: slug */
                __( '页面 URL 标识 %s 在数据包中重复。', 'developer-starter' ),
                $slug
            );
            return $report;
        }
        $page_slug_index[ $slug ] = $index;

        if ( $this->page_service->is_reserved_page_slug( $slug, $this->get_reserved_page_definitions() ) ) {
            $report['errors'][] = sprintf(
                /* translators: %s: slug */
                __( '页面 %s 使用了系统保留 URL，当前数据包不允许导入登录/注册/个人中心等系统页。', 'developer-starter' ),
                $slug
            );
            return $report;
        }

        if ( $this->page_service->is_reserved_page_template( $template, $this->get_reserved_page_definitions() ) ) {
            $report['errors'][] = sprintf(
                /* translators: %s: template */
                __( '模板 %s 属于系统保留模板，当前数据包不允许导入。', 'developer-starter' ),
                $template
            );
            return $report;
        }

        if ( ! $this->page_service->is_allowed_public_page_template( $template ) ) {
            $report['errors'][] = sprintf(
                /* translators: %s: template */
                __( '模板 %s 在当前主题中不存在。', 'developer-starter' ),
                $template
            );
            return $report;
        }

        $existing_page = get_page_by_path( $slug, OBJECT, 'page' );
        if ( $existing_page instanceof \WP_Post ) {
            $report['existing_page_id'] = absint( $existing_page->ID );
            if ( $this->page_service->is_reserved_page_template( get_post_meta( $existing_page->ID, '_wp_page_template', true ), $this->get_reserved_page_definitions() ) ) {
                $report['errors'][] = __( '该 URL 已被系统保留页面占用，当前数据包不会覆盖它。', 'developer-starter' );
                return $report;
            }
        }

        $prepared_action = 'create';
        $target_slug     = $slug;

        if ( $existing_page instanceof \WP_Post ) {
            if ( $this->config['conflict_strategy_duplicate'] === $conflict_strategy ) {
                $prepared_action      = 'duplicate';
                $target_slug          = $this->generate_unique_page_slug( $slug );
                $report['warnings'][] = sprintf(
                    /* translators: %s: slug */
                    __( '该页面 URL 已存在，导入时将自动改为新 URL %s 后创建副本。', 'developer-starter' ),
                    $target_slug
                );
            } elseif ( $this->config['conflict_strategy_update'] === $conflict_strategy && $this->can_update_existing_package_page( $existing_page->ID, $package_id, $page_key ) ) {
                $prepared_action      = 'update';
                $target_slug          = $existing_page->post_name;
                $report['warnings'][] = __( '已识别到同一数据包的历史页面，导入时将执行安全更新。', 'developer-starter' );
            } else {
                $report['action']   = 'skip';
                $report['warnings'][] = $this->config['conflict_strategy_update'] === $conflict_strategy
                    ? __( '该页面 URL 已存在，但不是当前数据包的历史页面，已按安全策略跳过。', 'developer-starter' )
                    : __( '该页面 URL 已存在，将按当前策略跳过。', 'developer-starter' );
                $report['prepared_page'] = array(
                    'page_key'         => $page_key,
                    'existing_page_id' => absint( $existing_page->ID ),
                );
                return $report;
            }
        }

        $modules_result = $this->module_service->prepare_modules_for_import(
            isset( $page['raw_modules'] ) ? $page['raw_modules'] : array(),
            $title
        );

        if ( is_wp_error( $modules_result ) ) {
            $report['errors'][] = $modules_result->get_error_message();
            return $report;
        }

        $report['module_count'] = count( $modules_result['modules'] );
        if ( ! empty( $modules_result['warnings'] ) ) {
            $report['warnings'] = array_merge( $report['warnings'], $modules_result['warnings'] );
        }
        if ( ! empty( $modules_result['style_warnings'] ) ) {
            $report['style_warnings'] = array_merge( $report['style_warnings'], $modules_result['style_warnings'] );
        }
        if ( ! empty( $modules_result['security_warnings'] ) ) {
            $report['security_warnings'] = array_merge( $report['security_warnings'], $modules_result['security_warnings'] );
        }

        $report['action']      = $prepared_action;
        $report['target_slug'] = $target_slug;
        $report['prepared_page'] = array(
            'page_key'         => $page_key,
            'title'            => $title,
            'slug'             => $slug,
            'target_slug'      => $target_slug,
            'template'         => $template,
            'post_status'      => isset( $page['post_status'] ) ? sanitize_key( (string) $page['post_status'] ) : 'publish',
            'settings'         => isset( $page['settings'] ) && is_array( $page['settings'] ) ? $page['settings'] : array(),
            'modules'          => $modules_result['modules'],
            'module_types'     => $modules_result['module_types'],
            'import_action'    => $prepared_action,
            'existing_page_id' => $existing_page instanceof \WP_Post ? absint( $existing_page->ID ) : 0,
        );

        return $report;
    }

    /**
     * 规范化单页条目。
     *
     * @param mixed $page  页面条目。
     * @param int   $index 页面索引。
     * @return array<string,mixed>|\WP_Error
     */
    private function normalize_page_entry( $page, $index ) {
        if ( ! is_array( $page ) ) {
            return new \WP_Error(
                'invalid_page_entry',
                sprintf(
                    /* translators: %d: page index */
                    __( '第 %d 个页面条目格式无效。', 'developer-starter' ),
                    $index + 1
                )
            );
        }

        $title = '';
        foreach ( array( 'title', 'page_title', 'name' ) as $title_key ) {
            if ( ! empty( $page[ $title_key ] ) && is_scalar( $page[ $title_key ] ) ) {
                $title = sanitize_text_field( (string) $page[ $title_key ] );
                break;
            }
        }
        if ( '' === $title ) {
            $title = sprintf(
                /* translators: %d: page number */
                __( '导入页面 %d', 'developer-starter' ),
                $index + 1
            );
        }

        $slug = '';
        foreach ( array( 'slug', 'post_name', 'path' ) as $slug_key ) {
            if ( ! empty( $page[ $slug_key ] ) && is_scalar( $page[ $slug_key ] ) ) {
                $slug = sanitize_title( (string) $page[ $slug_key ] );
                break;
            }
        }
        if ( '' === $slug ) {
            $slug = sanitize_title( $title );
        }
        if ( '' === $slug ) {
            $slug = 'page-' . ( $index + 1 );
        }

        $page_key = '';
        foreach ( array( 'page_key', 'key', 'id' ) as $key_name ) {
            if ( ! empty( $page[ $key_name ] ) && is_scalar( $page[ $key_name ] ) ) {
                $page_key = sanitize_key( (string) $page[ $key_name ] );
                break;
            }
        }
        if ( '' === $page_key ) {
            $page_key = sanitize_key( str_replace( '-', '_', $slug ) );
        }
        if ( '' === $page_key ) {
            $page_key = 'page_' . ( $index + 1 );
        }

        $template = 'default';
        foreach ( array( 'template', 'page_template' ) as $template_key ) {
            if ( isset( $page[ $template_key ] ) ) {
                $template = $this->page_service->normalize_page_template( $page[ $template_key ] );
                break;
            }
        }
        if ( '' === $template ) {
            $template = 'default';
        }

        $settings = isset( $page['settings'] ) && is_array( $page['settings'] ) ? $page['settings'] : array();
        $hide_page_header_default = false;
        $region_decoration = array();
        if ( class_exists( '\Developer_Starter\Core\Page_Region_Decoration' ) && isset( $settings['region_decoration'] ) && is_array( $settings['region_decoration'] ) ) {
            $region_decoration = Page_Region_Decoration::sanitize_settings( $settings['region_decoration'] );
        }

        $modules = array();
        if ( isset( $page['modules'] ) && is_array( $page['modules'] ) ) {
            $modules = $page['modules'];
        } elseif ( isset( $page['page_modules'] ) && is_array( $page['page_modules'] ) ) {
            $modules = $page['page_modules'];
        } elseif ( isset( $page['data'] ) && is_array( $page['data'] ) && isset( $page['data']['modules'] ) && is_array( $page['data']['modules'] ) ) {
            $modules = $page['data']['modules'];
        }

        return array(
            'page_key'    => $page_key,
            'title'       => $title,
            'slug'        => $slug,
            'template'    => $template,
            'post_status' => isset( $page['post_status'] ) ? sanitize_key( (string) $page['post_status'] ) : ( isset( $page['status'] ) ? sanitize_key( (string) $page['status'] ) : 'publish' ),
            'settings'    => array(
                'hide_page_header'     => $this->page_service->normalize_bool_value(
                    $this->page_service->get_first_defined_value( $page, $settings, 'hide_page_header' ),
                    $hide_page_header_default
                ),
                'transparent_header'   => $this->page_service->normalize_bool_value(
                    $this->page_service->get_first_defined_value( $page, $settings, 'transparent_header' ),
                    false
                ),
                'enable_scroll_reveal' => $this->page_service->normalize_bool_value(
                    $this->page_service->get_first_defined_value( $page, $settings, 'enable_scroll_reveal' ),
                    false
                ),
                'region_decoration'    => $region_decoration,
            ),
            'raw_modules'  => $modules,
        );
    }

    /**
     * 判断当前页面是否可按安全规则更新。
     *
     * @param int    $page_id    页面 ID。
     * @param string $package_id 数据包 ID。
     * @param string $page_key   页面标识。
     * @return bool
     */
    private function can_update_existing_package_page( $page_id, $package_id, $page_key ) {
        $page_id    = absint( $page_id );
        $package_id = sanitize_key( (string) $package_id );
        $page_key   = sanitize_key( (string) $page_key );

        if ( $page_id <= 0 || '' === $package_id || '' === $page_key ) {
            return false;
        }

        return $package_id === sanitize_key( (string) get_post_meta( $page_id, '_qiling_site_package_id', true ) )
            && $page_key === sanitize_key( (string) get_post_meta( $page_id, '_qiling_site_package_page_key', true ) );
    }

    /**
     * 为副本页面生成安全且唯一的 slug。
     *
     * @param string $slug 原始 slug。
     * @return string
     */
    private function generate_unique_page_slug( $slug ) {
        $slug = sanitize_title( (string) $slug );
        if ( '' === $slug ) {
            return '';
        }

        if ( function_exists( 'wp_unique_post_slug' ) ) {
            return wp_unique_post_slug( $slug, 0, 'publish', 'page', 0 );
        }

        $unique_slug = $slug;
        $suffix      = 2;
        while ( get_page_by_path( $unique_slug, OBJECT, 'page' ) instanceof \WP_Post ) {
            $unique_slug = sanitize_title( $slug . '-' . $suffix );
            $suffix++;
        }

        return $unique_slug;
    }

    /**
     * 规范化预检使用的冲突策略。
     *
     * @param mixed $strategy 原始策略。
     * @return string
     */
    private function normalize_conflict_strategy( $strategy ) {
        $strategy = sanitize_key( (string) $strategy );
        if ( in_array( $strategy, array( $this->config['conflict_strategy_skip'], $this->config['conflict_strategy_duplicate'], $this->config['conflict_strategy_update'] ), true ) ) {
            return $strategy;
        }

        return (string) $this->config['conflict_strategy_skip'];
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
}
