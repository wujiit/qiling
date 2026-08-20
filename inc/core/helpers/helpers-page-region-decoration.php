<?php
/**
 * 页面级区域装修公共函数。
 *
 * 独立函数作为模板和第三方扩展的稳定入口，具体规则集中在 Page_Region_Decoration 类中。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function developer_starter_get_post_page_region_decoration( $post_id ) {
    return \Developer_Starter\Core\Page_Region_Decoration::get_post_settings( $post_id );
}

function developer_starter_persist_post_page_region_decoration( $post_id, $settings ) {
    \Developer_Starter\Core\Page_Region_Decoration::persist_post_settings( $post_id, $settings );
}

function developer_starter_resolve_current_page_region_decoration( $region ) {
    return \Developer_Starter\Core\Page_Region_Decoration::resolve_current_region( $region );
}

function developer_starter_get_current_page_region_decoration_source_ids() {
    return \Developer_Starter\Core\Page_Region_Decoration::get_current_source_page_ids();
}

function developer_starter_resolve_imported_page_region_decoration( $post_id, $page_key_map ) {
    \Developer_Starter\Core\Page_Region_Decoration::resolve_imported_source_keys( $post_id, $page_key_map );
}
