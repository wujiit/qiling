<?php
/**
 * Frontend Builder library and catalog service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Frontend_Builder_Library_Service {

    /**
     * @var Frontend_Builder_QilingShop_Service
     */
    private $qilingshop_service;

    /**
     * @param Frontend_Builder_QilingShop_Service|null $qilingshop_service 商城适配服务。
     */
    public function __construct( $qilingshop_service = null ) {
        $this->qilingshop_service = $qilingshop_service instanceof Frontend_Builder_QilingShop_Service
            ? $qilingshop_service
            : new Frontend_Builder_QilingShop_Service();
    }

    /**
     * 获取模块列表（用于左侧模块库）。
     *
     * @param bool $shop_only 是否仅保留商城模块。
     * @return array<int,array<string,mixed>>
     */
    public function get_available_modules( $shop_only = false ) {
        $manager = Module_Manager::get_instance();
        $module_catalog = method_exists( $manager, 'get_module_catalog' )
            ? $manager->get_module_catalog( true )
            : array();
        $available = array();

        foreach ( $module_catalog as $module_item ) {
            if ( ! is_array( $module_item ) || empty( $module_item['id'] ) ) {
                continue;
            }

            $module_id = (string) $module_item['id'];
            $is_shop_module = $this->qilingshop_service->is_shop_module_type( $module_id );
            if ( $shop_only && ! $is_shop_module ) {
                continue;
            }
            if ( ! $shop_only && $is_shop_module ) {
                continue;
            }

            $available[] = array(
                'id'                   => (string) $module_id,
                'name'                 => isset( $module_item['name'] ) ? (string) $module_item['name'] : (string) $module_id,
                'category'             => isset( $module_item['category'] ) ? (string) $module_item['category'] : 'general',
                'group'                => isset( $module_item['group'] ) ? (string) $module_item['group'] : 'general',
                'groupLabel'           => isset( $module_item['groupLabel'] ) ? (string) $module_item['groupLabel'] : '',
                'keywords'             => isset( $module_item['keywords'] ) && is_array( $module_item['keywords'] ) ? array_values( array_map( 'strval', $module_item['keywords'] ) ) : array(),
                'aiEnabled'            => ! isset( $module_item['aiEnabled'] ) || (bool) $module_item['aiEnabled'],
                'catalogSchemaVersion' => isset( $module_item['catalogSchemaVersion'] ) ? (string) $module_item['catalogSchemaVersion'] : '',
                'version'              => isset( $module_item['version'] ) ? (string) $module_item['version'] : '',
                'status'               => isset( $module_item['status'] ) ? (string) $module_item['status'] : 'stable',
                'catalogRole'          => isset( $module_item['catalogRole'] ) ? (string) $module_item['catalogRole'] : 'extension',
                'metadataSource'       => isset( $module_item['metadataSource'] ) ? (string) $module_item['metadataSource'] : 'inferred',
                'metadataCompleteness' => isset( $module_item['metadataCompleteness'] ) ? absint( $module_item['metadataCompleteness'] ) : 0,
                'industryTags'         => isset( $module_item['industryTags'] ) && is_array( $module_item['industryTags'] ) ? array_values( array_map( 'strval', $module_item['industryTags'] ) ) : array(),
                'pageTags'             => isset( $module_item['pageTags'] ) && is_array( $module_item['pageTags'] ) ? array_values( array_map( 'strval', $module_item['pageTags'] ) ) : array(),
                'intentTags'           => isset( $module_item['intentTags'] ) && is_array( $module_item['intentTags'] ) ? array_values( array_map( 'strval', $module_item['intentTags'] ) ) : array(),
                'contentModels'        => isset( $module_item['contentModels'] ) && is_array( $module_item['contentModels'] ) ? array_values( array_map( 'strval', $module_item['contentModels'] ) ) : array(),
                'schemaTypes'          => isset( $module_item['schemaTypes'] ) && is_array( $module_item['schemaTypes'] ) ? array_values( array_map( 'strval', $module_item['schemaTypes'] ) ) : array(),
                'assetHints'           => isset( $module_item['assetHints'] ) && is_array( $module_item['assetHints'] ) ? array_values( array_map( 'strval', $module_item['assetHints'] ) ) : array(),
                'aiHints'              => isset( $module_item['aiHints'] ) && is_array( $module_item['aiHints'] ) ? $module_item['aiHints'] : array(),
            );
        }

        return $available;
    }

    /**
     * 获取后台“我的模版库”列表（ql_module_template），供前台装修复用。
     *
     * @return array<int,array<string,mixed>>
     */
    public function get_my_library_templates() {
        $manager = Module_Manager::get_instance();
        $query = new \WP_Query( Template_Manager::build_visible_template_query_args() );

        $templates = array();
        if ( ! $query->have_posts() ) {
            return $templates;
        }

        while ( $query->have_posts() ) {
            $query->the_post();

            $template_id = get_the_ID();
            if ( ! Template_Manager::current_user_can_access_template_post( $template_id, 'read_post' ) ) {
                continue;
            }
            $module_type = sanitize_key( (string) get_post_meta( $template_id, '_ql_template_type', true ) );
            if ( '' === $module_type ) {
                continue;
            }

            $module_obj = $manager->get_module( $module_type );
            $templates[] = array(
                'id'       => $template_id,
                'title'    => (string) get_the_title( $template_id ),
                'type'     => $module_type,
                'typeName' => $module_obj && method_exists( $module_obj, 'get_name' ) ? (string) $module_obj->get_name() : $module_type,
                'date'     => (string) get_the_date( get_option( 'date_format' ), $template_id ),
            );
        }
        wp_reset_postdata();

        return $templates;
    }

    /**
     * 获取单个“我的模版库”详情。
     *
     * @param int $template_id 模板 ID。
     * @param int $post_id 当前页面 ID。
     * @return array<string,mixed>|\WP_Error
     */
    public function get_my_library_template_detail( $template_id, $post_id = 0 ) {
        $template_id = absint( $template_id );
        if ( $template_id <= 0 ) {
            return new \WP_Error( 'invalid_template_id', __( '模板ID无效', 'developer-starter' ) );
        }

        $template = Template_Manager::get_template_post( $template_id );
        if ( ! $template || 'publish' !== $template->post_status ) {
            return new \WP_Error( 'template_not_found', __( '模板不存在或已删除', 'developer-starter' ) );
        }

        if ( ! Template_Manager::current_user_can_access_template_post( $template, 'read_post' ) ) {
            return new \WP_Error( 'template_forbidden', __( '无权访问该模板', 'developer-starter' ) );
        }

        $module_type = sanitize_key( (string) get_post_meta( $template_id, '_ql_template_type', true ) );
        if ( '' === $module_type ) {
            return new \WP_Error( 'template_type_missing', __( '模板对应模块无效', 'developer-starter' ) );
        }

        $shop_only = $post_id > 0 ? $this->qilingshop_service->is_builder_page( $post_id ) : false;
        $is_shop_module = $this->qilingshop_service->is_shop_module_type( $module_type );
        if ( $shop_only && ! $is_shop_module ) {
            return new \WP_Error( 'template_source_mismatch', __( '当前页面不能使用该模板', 'developer-starter' ) );
        }
        if ( ! $shop_only && $is_shop_module ) {
            return new \WP_Error( 'template_source_mismatch', __( '当前页面不能使用该模板', 'developer-starter' ) );
        }

        if ( $is_shop_module ) {
            $this->qilingshop_service->bootstrap_modules( $post_id );
        }

        $manager = Module_Manager::get_instance();
        $module_obj = $manager->get_module( $module_type );
        if ( ! $module_obj ) {
            return new \WP_Error( 'template_module_missing', __( '该模版对应的模块已不存在，无法添加。', 'developer-starter' ) );
        }

        $raw_json = (string) get_post_field( 'post_content', $template_id, 'raw' );
        $data = json_decode( $raw_json, true );
        if ( ! is_array( $data ) ) {
            $data = array();
        }

        return array(
            'id'       => $template_id,
            'title'    => (string) get_the_title( $template_id ),
            'type'     => $module_type,
            'typeName' => method_exists( $module_obj, 'get_name' ) ? (string) $module_obj->get_name() : $module_type,
            'data'     => $data,
            'date'     => (string) get_the_date( get_option( 'date_format' ), $template_id ),
        );
    }

    /**
     * 过滤模板列表：按来源保留主题或商城模块模板。
     *
     * @param array<int,array<string,mixed>> $templates 模板列表。
     * @param bool                            $shop_only 是否仅保留商城模板。
     * @return array<int,array<string,mixed>>
     */
    public function filter_templates_by_source( $templates, $shop_only = false ) {
        $filtered = array();
        foreach ( $templates as $item ) {
            if ( ! is_array( $item ) || empty( $item['type'] ) ) {
                continue;
            }
            $is_shop_module = $this->qilingshop_service->is_shop_module_type( (string) $item['type'] );
            if ( $shop_only && ! $is_shop_module ) {
                continue;
            }
            if ( ! $shop_only && $is_shop_module ) {
                continue;
            }
            $filtered[] = $item;
        }
        return $filtered;
    }
}
