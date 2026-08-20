<?php
/**
 * Default search result list.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'wp_query'                     => null,
        'search_result_show_thumb'     => true,
        'search_result_show_type'      => true,
        'search_result_show_date'      => true,
        'search_result_show_excerpt'   => true,
        'search_result_excerpt_length' => 40,
        'search_terms'                 => array(),
        'search_highlight_allowed'     => array( 'mark' => array( 'class' => true ) ),
    )
);
$wp_query                    = $args['wp_query'];
$search_result_show_thumb    = (bool) $args['search_result_show_thumb'];
$search_result_show_type     = (bool) $args['search_result_show_type'];
$search_result_show_date     = (bool) $args['search_result_show_date'];
$search_result_show_excerpt  = (bool) $args['search_result_show_excerpt'];
$search_result_excerpt_length = absint( $args['search_result_excerpt_length'] );
$search_terms                = (array) $args['search_terms'];
$search_highlight_allowed    = (array) $args['search_highlight_allowed'];

if ( $search_result_show_thumb && function_exists( 'update_post_thumbnail_cache' ) && $wp_query instanceof WP_Query ) {
    update_post_thumbnail_cache( $wp_query );
}
?>
<div class="search-results-list">
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'search-result-item' ); ?>>
            <div class="search-result-item__inner">
                <?php
                $thumb_url        = '';
                $thumb_is_default = false;
                if ( $search_result_show_thumb ) {
                    if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                        $thumb_url = developer_starter_get_featured_image_url( get_the_ID(), 'thumbnail' );
                    } elseif ( has_post_thumbnail() ) {
                        $thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
                    }
                    $thumb_dims = array( 'width' => 300, 'height' => 200 );
                    if ( function_exists( 'developer_starter_get_post_image_dimensions' ) ) {
                        $thumb_dims = developer_starter_get_post_image_dimensions(
                            get_the_ID(),
                            'thumbnail',
                            array( 'width' => 300, 'height' => 200 )
                        );
                    }

                    if ( ! $thumb_url ) {
                        $thumb_url        = get_template_directory_uri() . '/assets/images/default-thumbnail.svg';
                        $thumb_is_default = true;
                    }
                }

                $result_title = trim( get_the_title() );
                if ( '' === $result_title ) {
                    $result_title = __( '（无标题）', 'developer-starter' );
                }

                $result_type_label = '';
                if ( $search_result_show_type ) {
                    $post_type_obj = get_post_type_object( get_post_type() );
                    $result_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : '';
                }
                $result_excerpt = '';
                if ( $search_result_show_excerpt ) {
                    $result_excerpt = wp_trim_words( get_the_excerpt(), $search_result_excerpt_length );
                    if ( '' === trim( $result_excerpt ) ) {
                        $result_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content() ), $search_result_excerpt_length );
                    }
                }
                $has_result_meta = ( $search_result_show_type && $result_type_label ) || $search_result_show_date;
                ?>
                <?php if ( $search_result_show_thumb ) : ?>
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="search-result-thumb<?php echo $thumb_is_default ? ' search-result-thumb--default' : ''; ?>">
                    <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $result_title ); ?>" width="<?php echo esc_attr( (int) $thumb_dims['width'] ); ?>" height="<?php echo esc_attr( (int) $thumb_dims['height'] ); ?>" loading="lazy" decoding="async" />
                </a>
                <?php endif; ?>

                <div class="search-result-content">
                    <?php if ( $has_result_meta ) : ?>
                    <span class="search-result-meta">
                        <?php if ( $search_result_show_type && $result_type_label ) : ?>
                            <?php echo esc_html( $result_type_label ); ?>
                            <?php if ( $search_result_show_date ) : ?><span aria-hidden="true"> · </span><?php endif; ?>
                        <?php endif; ?>
                        <?php if ( $search_result_show_date ) : ?>
                            <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>

                    <h2 class="search-result-title">
                        <a href="<?php echo esc_url( get_permalink() ); ?>">
                            <?php
                            echo function_exists( 'developer_starter_highlight_search_terms' )
                                ? wp_kses( developer_starter_highlight_search_terms( $result_title, $search_terms ), $search_highlight_allowed )
                                : esc_html( $result_title );
                            ?>
                        </a>
                    </h2>

                    <?php if ( $search_result_show_excerpt && '' !== trim( $result_excerpt ) ) : ?>
                        <p class="search-result-excerpt">
                            <?php
                            echo function_exists( 'developer_starter_highlight_search_terms' )
                                ? wp_kses( developer_starter_highlight_search_terms( $result_excerpt, $search_terms ), $search_highlight_allowed )
                                : esc_html( $result_excerpt );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endwhile; ?>
</div>
