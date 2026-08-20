<?php
/**
 * 页面级区域装修内容容器。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'region'  => '',
        'page_id' => 0,
        'modules' => array(),
    )
);
$region  = sanitize_key( (string) $args['region'] );
$page_id = absint( $args['page_id'] );
$modules = is_array( $args['modules'] ) ? $args['modules'] : array();
if ( '' === $region || $page_id <= 0 || empty( $modules ) ) {
    return;
}

$classes = array(
    'qiling-page-region-decoration',
    'qiling-page-region-decoration--' . sanitize_html_class( str_replace( '_', '-', $region ) ),
);
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-page-region="<?php echo esc_attr( $region ); ?>" data-decoration-page="<?php echo esc_attr( (string) $page_id ); ?>">
    <?php
    do_action( 'developer_starter_before_page_region_decoration', $region, $page_id, $args );
    if ( function_exists( 'developer_starter_render_page_modules' ) ) {
        developer_starter_render_page_modules(
            $page_id,
            array(
                'builder_mode' => false,
                'context'      => 'page_region_' . $region,
            )
        );
    }
    do_action( 'developer_starter_after_page_region_decoration', $region, $page_id, $args );
    ?>
</div>
