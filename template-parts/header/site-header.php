<?php
/**
 * Site header shell.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'header_classes'    => array( 'site-header' ),
        'header_style_vars' => '',
    )
);

$header_classes    = array_filter( array_map( 'sanitize_html_class', (array) $args['header_classes'] ) );
$header_style_vars = (string) $args['header_style_vars'];
?>
<?php do_action( 'developer_starter_before_site_header', $args ); ?>
<header id="masthead" class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>"<?php echo '' !== $header_style_vars ? ' style="' . esc_attr( $header_style_vars ) . '"' : ''; ?>>
    <div class="header-inner">
        <div class="container header-flex">
            <?php get_template_part( 'template-parts/header/branding', null, $args ); ?>
            <?php get_template_part( 'template-parts/header/navigation', null, $args ); ?>
            <div class="header-actions">
                <?php get_template_part( 'template-parts/header/actions', null, $args ); ?>
            </div>
        </div>
    </div>

    <?php get_template_part( 'template-parts/header/search-overlay', null, $args ); ?>
    <?php get_template_part( 'template-parts/header/mobile-menu', null, $args ); ?>
</header>
<?php do_action( 'developer_starter_after_site_header', $args ); ?>
