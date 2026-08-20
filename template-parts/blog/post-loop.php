<?php
/**
 * Shared native blog loop.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'query'    => null,
        'settings' => array(),
    )
);

$use_custom_query = $args['query'] instanceof WP_Query;
$query = $use_custom_query ? $args['query'] : $GLOBALS['wp_query'];

if ( ! $query instanceof WP_Query ) {
    return;
}

$settings = is_array( $args['settings'] ) ? $args['settings'] : array();
if ( empty( $settings ) && class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' ) ) {
    $settings = \Developer_Starter\Core\Blog_Visual_Manager::get_native_loop_settings();
}
$grid_classes = isset( $settings['grid_classes'] ) ? trim( (string) $settings['grid_classes'] ) : 'news-grid grid-cols-3';

if ( $query->have_posts() ) :
    if ( ! empty( $settings['show_thumb'] ) && function_exists( 'update_post_thumbnail_cache' ) ) {
        update_post_thumbnail_cache( $query );
    }
    if ( ! empty( $settings['show_author'] ) && function_exists( 'update_post_author_caches' ) && ! empty( $query->posts ) ) {
        update_post_author_caches( $query->posts );
    }

    $qiling_blog_loop_index = 0;
    ?>
    <div class="<?php echo esc_attr( $grid_classes ); ?>">
        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <?php $qiling_blog_loop_index++; ?>
            <?php get_template_part( 'template-parts/blog/post-card', null, array( 'settings' => $settings ) ); ?>
            <?php do_action( 'qiling_blog_loop_after_item', $qiling_blog_loop_index, $query, $settings ); ?>
        <?php endwhile; ?>
    </div>
    <?php
else :
    get_template_part( 'template-parts/content/content', 'none' );
endif;

if ( $use_custom_query ) {
    wp_reset_postdata();
}
