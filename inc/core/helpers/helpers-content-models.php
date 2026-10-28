<?php
/**
 * 通用内容模型中心辅助函数。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_content_model_center' ) ) {
    /**
     * 获取通用内容模型中心单例实例。
     *
     * @return \Developer_Starter\Core\Content_Model_Center|null
     */
    function developer_starter_get_content_model_center() {
        if ( ! class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            return null;
        }

        return \Developer_Starter\Core\Content_Model_Center::get_instance();
    }
}

if ( ! function_exists( 'developer_starter_get_content_model_definitions' ) ) {
    /**
     * 获取所有内容模型定义。
     *
     * @return array<string,array<string,mixed>>
     */
    function developer_starter_get_content_model_definitions() {
        $center = developer_starter_get_content_model_center();
        return $center ? $center->get_model_definitions() : array();
    }
}

if ( ! function_exists( 'developer_starter_get_content_model_client_payload' ) ) {
    /**
     * 获取供页面构建器使用的内容模型数据载荷。
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return array<string,mixed>
     */
    function developer_starter_get_content_model_client_payload( $options = null ) {
        if ( ! class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            return array();
        }

        return \Developer_Starter\Core\Content_Model_Center::get_client_payload( $options );
    }
}

if ( ! function_exists( 'developer_starter_get_content_model_prompt_context' ) ) {
    /**
     * 获取用于生成请求的紧凑内容模型上下文。
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return array<string,mixed>
     */
    function developer_starter_get_content_model_prompt_context( $options = null ) {
        if ( ! class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            return array();
        }

        return \Developer_Starter\Core\Content_Model_Center::get_prompt_context( $options );
    }
}

if ( ! function_exists( 'developer_starter_query_content_model_items' ) ) {
    /**
     * 查询指定内容模型下已发布的文章项。
     *
     * @param string              $model_id Content model id.
     * @param array<string,mixed> $args WP_Query args.
     * @return array<int,\WP_Post>
     */
    function developer_starter_query_content_model_items( $model_id, $args = array() ) {
        if ( ! class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            return array();
        }

        return \Developer_Starter\Core\Content_Model_Center::query_model_items( $model_id, $args );
    }
}
