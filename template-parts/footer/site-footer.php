<?php
/**
 * Site footer shell.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'footer_css_vars' => '',
        'footer_builder_enabled' => false,
        'footer_builder_page_id' => 0,
        'footer_builder_region_page_ids' => array(),
        'footer_builder_position' => 'replace_widgets',
        'footer_builder_modules' => array(),
        'page_footer_region_decoration' => array(),
        'footer_visual_config' => array(),
        'effect_enabled' => false,
        'effect_scope' => 'main',
    )
);

$footer_builder_page_id = absint( $args['footer_builder_page_id'] );
$footer_builder_position = isset( $args['footer_builder_position'] ) ? sanitize_key( (string) $args['footer_builder_position'] ) : 'replace_widgets';
if ( ! in_array( $footer_builder_position, array( 'replace_widgets', 'replace_friend_links', 'replace_bottom', 'replace_all' ), true ) ) {
    $footer_builder_position = 'replace_widgets';
}
$footer_builder_region_page_ids = array_fill_keys( array( 'replace_widgets', 'replace_friend_links', 'replace_bottom' ), 0 );
$configured_region_page_ids = isset( $args['footer_builder_region_page_ids'] ) && is_array( $args['footer_builder_region_page_ids'] )
    ? $args['footer_builder_region_page_ids']
    : array();
foreach ( $footer_builder_region_page_ids as $region => $unused ) {
    $footer_builder_region_page_ids[ $region ] = isset( $configured_region_page_ids[ $region ] ) ? absint( $configured_region_page_ids[ $region ] ) : 0;
}
$has_independent_regions = (bool) array_filter( $footer_builder_region_page_ids );
if ( 'replace_all' !== $footer_builder_position && empty( $footer_builder_region_page_ids[ $footer_builder_position ] ) ) {
    $footer_builder_region_page_ids[ $footer_builder_position ] = $footer_builder_page_id;
}

$footer_builder_regions = array();
if ( ! empty( $args['footer_builder_enabled'] ) ) {
    foreach ( $footer_builder_region_page_ids as $region => $page_id ) {
        if ( $page_id <= 0 || 'page' !== get_post_type( $page_id ) || ( function_exists( 'get_queried_object_id' ) && absint( get_queried_object_id() ) === $page_id ) ) {
            continue;
        }
        $footer_page = get_post( $page_id );
        if ( ! $footer_page instanceof WP_Post || 'publish' !== $footer_page->post_status ) {
            continue;
        }
        $modules = function_exists( 'developer_starter_get_page_modules_data' )
            ? developer_starter_get_page_modules_data( $page_id )
            : get_post_meta( $page_id, '_developer_starter_modules', true );
        if ( ! empty( $modules ) && is_array( $modules ) ) {
            $footer_builder_regions[ $region ] = array( 'page_id' => $page_id, 'modules' => $modules );
        }
    }
}
$replace_all_enabled = ! $has_independent_regions
    && ! empty( $args['footer_builder_enabled'] )
    && 'replace_all' === $footer_builder_position
    && $footer_builder_page_id > 0
    && 'page' === get_post_type( $footer_builder_page_id )
    && ( ! function_exists( 'get_queried_object_id' ) || absint( get_queried_object_id() ) !== $footer_builder_page_id );
if ( $replace_all_enabled ) {
    $footer_page = get_post( $footer_builder_page_id );
    $replace_all_modules = function_exists( 'developer_starter_get_page_modules_data' )
        ? developer_starter_get_page_modules_data( $footer_builder_page_id )
        : get_post_meta( $footer_builder_page_id, '_developer_starter_modules', true );
    $replace_all_enabled = $footer_page instanceof WP_Post && 'publish' === $footer_page->post_status && ! empty( $replace_all_modules ) && is_array( $replace_all_modules );
}
$page_footer_regions = isset( $args['page_footer_region_decoration'] ) && is_array( $args['page_footer_region_decoration'] )
    ? $args['page_footer_region_decoration']
    : array();
$page_region_map = array(
    'replace_widgets'      => 'footer_main',
    'replace_friend_links' => 'footer_friend',
    'replace_bottom'       => 'footer_bottom',
);
$has_page_footer_override = false;
foreach ( $page_region_map as $builder_region => $page_region ) {
    $resolved = isset( $page_footer_regions[ $page_region ] ) && is_array( $page_footer_regions[ $page_region ] )
        ? $page_footer_regions[ $page_region ]
        : array( 'mode' => 'inherit' );
    if ( 'inherit' !== $resolved['mode'] ) {
        $has_page_footer_override = true;
    }
}
// 页面级区域配置优先于全局“替换整个页脚”，否则无法只覆盖其中一个底部区域。
if ( $has_page_footer_override ) {
    $replace_all_enabled = false;
}
$footer_builder_enabled = $replace_all_enabled || ! empty( $footer_builder_regions );
$footer_classes = array( 'site-footer' );
$footer_visual_config = isset( $args['footer_visual_config'] ) && is_array( $args['footer_visual_config'] )
    ? $args['footer_visual_config']
    : array();
$global_footer_hidden = ! empty( $footer_visual_config['hidden'] );
if ( $global_footer_hidden && ! $has_page_footer_override ) {
    do_action( 'developer_starter_before_site_footer', $args );
    do_action( 'developer_starter_after_site_footer', $args );
    return;
}
if ( ! empty( $footer_visual_config['classes'] ) && is_array( $footer_visual_config['classes'] ) ) {
    $footer_classes = array_merge( $footer_classes, $footer_visual_config['classes'] );
}
if ( $footer_builder_enabled ) {
    $footer_classes[] = 'site-footer--builder-enabled';
    if ( $has_independent_regions ) {
        $footer_classes[] = 'site-footer--builder-regions';
        foreach ( array_keys( $footer_builder_regions ) as $builder_region ) {
            $footer_classes[] = 'site-footer--builder-' . sanitize_html_class( $builder_region );
        }
    } else {
        $footer_classes[] = 'site-footer--builder-' . sanitize_html_class( $footer_builder_position );
    }
}
$footer_style_attr = trim( (string) $args['footer_css_vars'] . ';' . ( isset( $footer_visual_config['style'] ) ? (string) $footer_visual_config['style'] : '' ), ';' );
$effect_scope = isset( $args['effect_scope'] ) ? sanitize_key( (string) $args['effect_scope'] ) : 'main';
if ( ! in_array( $effect_scope, array( 'main', 'all', 'decorative' ), true ) ) {
    $effect_scope = 'main';
}
$wave_enabled = ! empty( $footer_visual_config['wave_enabled'] );
$wave_style   = isset( $footer_visual_config['wave_style'] ) ? sanitize_key( (string) $footer_visual_config['wave_style'] ) : 'double';
$wave_paths   = function_exists( 'developer_starter_get_footer_wave_paths' )
    ? developer_starter_get_footer_wave_paths( $wave_style )
    : array(
        'primary' => 'M0,78 C180,28 332,124 534,78 C742,30 882,18 1084,66 C1248,104 1322,58 1440,40 L1440,160 L0,160 Z',
        'soft'    => 'M0,92 C250,130 430,20 704,62 C936,98 1112,130 1440,72 L1440,160 L0,160 Z',
    );
?>
<?php do_action( 'developer_starter_before_site_footer', $args ); ?>
<footer id="colophon" class="<?php echo esc_attr( implode( ' ', array_filter( array_unique( $footer_classes ) ) ) ); ?>" style="<?php echo esc_attr( $footer_style_attr ); ?>">
    <?php if ( $wave_enabled ) : ?>
        <div class="site-footer-wave" aria-hidden="true">
            <svg class="site-footer-wave__svg" viewBox="0 0 1440 160" preserveAspectRatio="none" focusable="false">
                <path class="site-footer-wave__soft" d="<?php echo esc_attr( $wave_paths['soft'] ); ?>"></path>
                <path class="site-footer-wave__fill" d="<?php echo esc_attr( $wave_paths['primary'] ); ?>"></path>
            </svg>
        </div>
    <?php endif; ?>
    <?php if ( ! empty( $args['effect_enabled'] ) && in_array( $effect_scope, array( 'all', 'decorative' ), true ) ) : ?>
        <canvas id="footer-effect-canvas" class="footer-effect-canvas footer-effect-canvas--site"></canvas>
    <?php endif; ?>
    <?php if ( $replace_all_enabled ) : ?>
        <?php get_template_part( 'template-parts/footer/custom-builder', null, array_merge( $args, array( 'footer_builder_modules' => $replace_all_modules ) ) ); ?>
    <?php else : ?>
        <?php $page_region = isset( $page_footer_regions['footer_main'] ) ? $page_footer_regions['footer_main'] : array( 'mode' => 'inherit' ); ?>
        <?php if ( 'custom' === $page_region['mode'] ) : ?>
            <?php get_template_part( 'template-parts/page-region/decoration', null, array( 'region' => 'footer_main', 'page_id' => $page_region['page_id'], 'modules' => $page_region['modules'] ) ); ?>
        <?php elseif ( 'hidden' === $page_region['mode'] ) : ?>
            <?php do_action( 'developer_starter_page_region_decoration_hidden', 'footer_main' ); ?>
        <?php elseif ( $global_footer_hidden ) : ?>
            <?php do_action( 'developer_starter_page_region_decoration_hidden', 'footer_main' ); ?>
        <?php elseif ( isset( $footer_builder_regions['replace_widgets'] ) ) : ?>
            <?php get_template_part( 'template-parts/footer/custom-builder', null, array_merge( $args, array( 'footer_builder_page_id' => $footer_builder_regions['replace_widgets']['page_id'], 'footer_builder_position' => 'replace_widgets', 'footer_builder_modules' => $footer_builder_regions['replace_widgets']['modules'] ) ) ); ?>
        <?php else : ?>
            <?php get_template_part( 'template-parts/footer/widgets', null, $args ); ?>
        <?php endif; ?>
        <?php $page_region = isset( $page_footer_regions['footer_friend'] ) ? $page_footer_regions['footer_friend'] : array( 'mode' => 'inherit' ); ?>
        <?php if ( 'custom' === $page_region['mode'] && is_front_page() ) : ?>
            <?php get_template_part( 'template-parts/page-region/decoration', null, array( 'region' => 'footer_friend', 'page_id' => $page_region['page_id'], 'modules' => $page_region['modules'] ) ); ?>
        <?php elseif ( 'hidden' === $page_region['mode'] ) : ?>
            <?php do_action( 'developer_starter_page_region_decoration_hidden', 'footer_friend' ); ?>
        <?php elseif ( $global_footer_hidden ) : ?>
            <?php do_action( 'developer_starter_page_region_decoration_hidden', 'footer_friend' ); ?>
        <?php elseif ( isset( $footer_builder_regions['replace_friend_links'] ) && is_front_page() ) : ?>
            <?php get_template_part( 'template-parts/footer/custom-builder', null, array_merge( $args, array( 'footer_builder_page_id' => $footer_builder_regions['replace_friend_links']['page_id'], 'footer_builder_position' => 'replace_friend_links', 'footer_builder_modules' => $footer_builder_regions['replace_friend_links']['modules'] ) ) ); ?>
        <?php else : ?>
            <?php get_template_part( 'template-parts/footer/friend-links', null, $args ); ?>
        <?php endif; ?>
        <?php $page_region = isset( $page_footer_regions['footer_bottom'] ) ? $page_footer_regions['footer_bottom'] : array( 'mode' => 'inherit' ); ?>
        <?php if ( 'custom' === $page_region['mode'] ) : ?>
            <?php get_template_part( 'template-parts/page-region/decoration', null, array( 'region' => 'footer_bottom', 'page_id' => $page_region['page_id'], 'modules' => $page_region['modules'] ) ); ?>
        <?php elseif ( 'hidden' === $page_region['mode'] ) : ?>
            <?php do_action( 'developer_starter_page_region_decoration_hidden', 'footer_bottom' ); ?>
        <?php elseif ( $global_footer_hidden ) : ?>
            <?php do_action( 'developer_starter_page_region_decoration_hidden', 'footer_bottom' ); ?>
        <?php elseif ( isset( $footer_builder_regions['replace_bottom'] ) ) : ?>
            <?php get_template_part( 'template-parts/footer/custom-builder', null, array_merge( $args, array( 'footer_builder_page_id' => $footer_builder_regions['replace_bottom']['page_id'], 'footer_builder_position' => 'replace_bottom', 'footer_builder_modules' => $footer_builder_regions['replace_bottom']['modules'] ) ) ); ?>
        <?php else : ?>
            <?php get_template_part( 'template-parts/footer/bottom', null, $args ); ?>
        <?php endif; ?>
    <?php endif; ?>
</footer>
<?php do_action( 'developer_starter_after_site_footer', $args ); ?>
