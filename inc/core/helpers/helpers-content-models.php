<?php
/**
 * Content model center helper functions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_content_model_center' ) ) {
    /**
     * Get the universal content model center instance.
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
     * Get all content model definitions.
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
     * Get content model payload for builders.
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
     * Get compact content model context for generation requests.
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
     * Query published items for one content model.
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
