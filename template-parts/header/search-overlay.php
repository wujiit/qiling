<?php
/**
 * Header search overlay.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'show_search'               => true,
        'header_search_action'      => home_url( '/' ),
        'header_search_use_rewrite' => false,
    )
);

if ( empty( $args['show_search'] ) ) {
    return;
}
$header_search_mode = function_exists( 'developer_starter_get_search_mode_form_value' ) ? developer_starter_get_search_mode_form_value() : 'all';
?>
<div class="search-overlay" id="search-overlay">
    <div class="search-overlay-inner">
        <form role="search" method="get" class="search-form qiling-search-enhanced" data-qiling-search-form="1" action="<?php echo esc_url( (string) $args['header_search_action'] ); ?>"<?php if ( ! empty( $args['header_search_use_rewrite'] ) ) : ?> onsubmit="return dsSearchRedirect(this);"<?php endif; ?>>
            <input type="search" name="s" placeholder="<?php esc_attr_e( '请输入关键词搜索...', 'developer-starter' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" autocomplete="off" data-qiling-search-input="1" />
            <input type="hidden" name="search_scope" value="all" />
            <input type="hidden" name="qiling_search_mode" value="<?php echo esc_attr( $header_search_mode ); ?>" />
            <button type="submit" aria-label="<?php esc_attr_e( '搜索', 'developer-starter' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </button>
        </form>
        <button type="button" class="search-close" id="search-close" aria-label="<?php esc_attr_e( '关闭搜索', 'developer-starter' ); ?>">&times;</button>
    </div>
</div>
