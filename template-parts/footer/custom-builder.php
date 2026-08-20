<?php
/**
 * Custom footer modules.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'footer_builder_page_id' => 0,
        'footer_builder_position' => 'replace_widgets',
        'footer_builder_modules' => array(),
    )
);

$footer_page_id = absint( $args['footer_builder_page_id'] );
if ( $footer_page_id <= 0 || 'page' !== get_post_type( $footer_page_id ) ) {
    return;
}

if ( function_exists( 'get_queried_object_id' ) && absint( get_queried_object_id() ) === $footer_page_id ) {
    return;
}

$footer_page = get_post( $footer_page_id );
if ( ! $footer_page instanceof WP_Post || 'publish' !== $footer_page->post_status ) {
    return;
}

$footer_modules = isset( $args['footer_builder_modules'] ) && is_array( $args['footer_builder_modules'] )
    ? $args['footer_builder_modules']
    : array();
if ( empty( $footer_modules ) ) {
    $footer_modules = function_exists( 'developer_starter_get_page_modules_data' )
        ? developer_starter_get_page_modules_data( $footer_page_id )
        : get_post_meta( $footer_page_id, '_developer_starter_modules', true );
}
if ( empty( $footer_modules ) || ! is_array( $footer_modules ) ) {
    return;
}

$position = sanitize_key( (string) $args['footer_builder_position'] );
$classes = array(
    'site-footer-custom',
    'site-footer-custom--' . ( '' !== $position ? $position : 'replace_widgets' ),
);
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-footer-template="<?php echo esc_attr( (string) $footer_page_id ); ?>">
    <?php
    do_action( 'developer_starter_before_footer_builder', $footer_page_id, $args );

    if ( function_exists( 'developer_starter_render_page_modules' ) ) {
        developer_starter_render_page_modules(
            $footer_page_id,
            array(
                'builder_mode' => false,
                'context'      => 'footer',
            )
        );
    }

    do_action( 'developer_starter_after_footer_builder', $footer_page_id, $args );
    ?>
</div>
