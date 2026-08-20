<?php
/**
 * Meta Boxes - Modules view service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes_Modules_View_Service {
    public function render( $args = array() ) {
        $args = is_array( $args ) ? $args : array();

        $post = isset( $args['post'] ) ? $args['post'] : null;
        $enable_scroll_reveal = isset( $args['enable_scroll_reveal'] ) ? $args['enable_scroll_reveal'] : '';
        $available_module_count = isset( $args['available_module_count'] ) ? (int) $args['available_module_count'] : 0;
        $ai_builder_config = isset( $args['ai_builder_config'] ) && is_array( $args['ai_builder_config'] ) ? $args['ai_builder_config'] : array( 'enabled' => false );
        $ai_builder_available = ! empty( $args['ai_builder_available'] );
        $ai_builder_supported = ! empty( $args['ai_builder_supported'] );
        $ai_max_modules = isset( $args['ai_max_modules'] ) ? (int) $args['ai_max_modules'] : 10;
        $ai_module_groups = isset( $args['ai_module_groups'] ) && is_array( $args['ai_module_groups'] ) ? $args['ai_module_groups'] : array();
        $default_page_package_template = isset( $args['default_page_package_template'] ) ? (string) $args['default_page_package_template'] : '';

        require __DIR__ . '/views/modules-meta-box.php';
    }
}
