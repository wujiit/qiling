<?php
/**
 * Related posts for single post pages.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'options' => array(),
        'post_id' => get_the_ID(),
    )
);

$options = is_array( $args['options'] ) ? $args['options'] : array();
$post_id = absint( $args['post_id'] );
$related_enable = ! isset( $options['related_posts_enable'] ) || '1' === $options['related_posts_enable'];
$related_source = isset( $options['related_posts_source'] ) && '' !== $options['related_posts_source'] ? $options['related_posts_source'] : 'category';
$related_source = in_array( $related_source, array( 'category', 'latest', 'random' ), true ) ? $related_source : 'category';
$related_show_thumb = ! isset( $options['related_posts_show_thumb'] ) || '1' === $options['related_posts_show_thumb'];
$related_show_date = ! isset( $options['related_posts_show_date'] ) || '1' === (string) $options['related_posts_show_date'];
$related_show_excerpt = ! empty( $options['related_posts_show_excerpt'] ) && '1' === (string) $options['related_posts_show_excerpt'];
$related_show_category = ! empty( $options['related_posts_show_category'] ) && '1' === (string) $options['related_posts_show_category'];
$related_title = isset( $options['related_posts_title'] ) ? trim( wp_strip_all_tags( (string) $options['related_posts_title'] ) ) : '';
if ( '' === $related_title ) {
    $related_title = __( '相关文章', 'developer-starter' );
}
$related_limit = isset( $options['related_posts_count'] ) ? absint( $options['related_posts_count'] ) : 3;
if ( $related_limit <= 0 ) {
    $related_limit = 3;
}
$related_limit = min( 12, max( 1, $related_limit ) );
$related_columns = isset( $options['related_posts_columns'] ) ? absint( $options['related_posts_columns'] ) : 3;
if ( ! in_array( $related_columns, array( 2, 3, 4 ), true ) ) {
    $related_columns = 3;
}
$related_grid_class = 'news-grid grid-cols-' . $related_columns;

if ( ! $related_enable || $post_id <= 0 ) {
    return;
}

$related_fragment_key = 'single_related_v2_' . $post_id
    . '_' . $related_source
    . '_' . $related_limit
    . '_' . $related_columns
    . '_' . ( $related_show_thumb ? '1' : '0' )
    . '_' . ( $related_show_date ? '1' : '0' )
    . '_' . ( $related_show_excerpt ? '1' : '0' )
    . '_' . ( $related_show_category ? '1' : '0' )
    . '_' . md5( $related_title );
$related_fragment = function_exists( 'developer_starter_get_fragment_cache' ) ? developer_starter_get_fragment_cache( $related_fragment_key ) : false;
if ( false !== $related_fragment ) {
    echo $related_fragment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

ob_start();

$related_post_ids = array();
$related_exclude_ids = array( $post_id );
$related_ids_cache_hit = false;
$related_ids_cache_enabled = function_exists( 'developer_starter_get_option' ) ? (bool) developer_starter_get_option( 'query_cache_enable', '1' ) : true;
$related_ids_cache_enabled = (bool) apply_filters( 'developer_starter_related_ids_cache_enable', $related_ids_cache_enabled, $post_id, $related_source );
$related_ids_cache_variant = 'random' === $related_source ? gmdate( 'Ymd' ) : 'stable';
$related_ids_cache_key = 'single_related_ids_v2_' . $post_id . '_' . $related_source . '_' . $related_limit . '_' . $related_ids_cache_variant;
$related_ids_cache_args = array(
    'audience'               => 'public',
    'surface'                => 'frontend',
    'group'                  => 'developer_starter_related',
    'scope'                  => 'related:' . $related_ids_cache_key,
    'version_groups'         => array( 'content', 'settings' ),
    'blog_scoped'            => true,
    'respect_content_bypass' => true,
);

if ( $related_ids_cache_enabled && function_exists( 'developer_starter_cache_read' ) ) {
    $related_cached_ids = developer_starter_cache_read( $related_ids_cache_key, $related_ids_cache_args );
    if ( is_array( $related_cached_ids ) ) {
        $related_ids_cache_hit = true;
        $related_post_ids = array_slice( array_values( array_filter( array_map( 'absint', $related_cached_ids ) ) ), 0, $related_limit );
        $related_exclude_ids = array_merge( array( $post_id ), $related_post_ids );
    }
}

$related_ids_query = function( $query_args, $context ) {
    $query_args = wp_parse_args(
        $query_args,
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'fields'                 => 'ids',
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby'                => 'date',
            'order'                  => 'DESC',
        )
    );

    $query = function_exists( 'developer_starter_run_cached_query' )
        ? developer_starter_run_cached_query( $query_args, $context, array( 'needs_pagination' => false ) )
        : new WP_Query( $query_args );

    return array_values( array_filter( array_map( 'absint', (array) $query->posts ) ) );
};

$append_related_ids = function( $candidate_ids ) use ( &$related_post_ids, &$related_exclude_ids, $related_limit ) {
    foreach ( $candidate_ids as $candidate_id ) {
        $candidate_id = absint( $candidate_id );
        if ( $candidate_id <= 0 || in_array( $candidate_id, $related_exclude_ids, true ) ) {
            continue;
        }

        $related_post_ids[] = $candidate_id;
        $related_exclude_ids[] = $candidate_id;

        if ( count( $related_post_ids ) >= $related_limit ) {
            break;
        }
    }
};

if ( ! $related_ids_cache_hit && 'category' === $related_source ) {
    $related_tags = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
    if ( ! empty( $related_tags ) ) {
        $append_related_ids(
            $related_ids_query(
                array(
                    'posts_per_page' => $related_limit,
                    'post__not_in'   => $related_exclude_ids,
                    'tag__in'        => array_map( 'absint', $related_tags ),
                ),
                'single_related_tag_ids'
            )
        );
    }

    if ( count( $related_post_ids ) < $related_limit ) {
        $related_categories = get_the_category( $post_id );
        if ( ! empty( $related_categories ) ) {
            $append_related_ids(
                $related_ids_query(
                    array(
                        'posts_per_page' => $related_limit - count( $related_post_ids ),
                        'post__not_in'   => $related_exclude_ids,
                        'category__in'   => wp_list_pluck( $related_categories, 'term_id' ),
                    ),
                    'single_related_category_ids'
                )
            );
        }
    }
}

if ( ! $related_ids_cache_hit && 'random' === $related_source ) {
    $random_candidate_ids = $related_ids_query(
        array(
            'posts_per_page' => 12,
            'post__not_in'   => $related_exclude_ids,
        ),
        'single_related_random_candidate_ids'
    );
    $random_seed = absint( crc32( $post_id . '|' . gmdate( 'Ymd' ) ) );
    usort(
        $random_candidate_ids,
        function( $left_id, $right_id ) use ( $random_seed ) {
            return strcmp( md5( $random_seed . '|' . $left_id ), md5( $random_seed . '|' . $right_id ) );
        }
    );
    $append_related_ids( $random_candidate_ids );
}

if ( ! $related_ids_cache_hit && ( 'latest' === $related_source || count( $related_post_ids ) < $related_limit ) ) {
    $append_related_ids(
        $related_ids_query(
            array(
                'posts_per_page' => $related_limit - count( $related_post_ids ),
                'post__not_in'   => $related_exclude_ids,
            ),
            'single_related_latest_ids'
        )
    );
}

if ( $related_ids_cache_enabled && ! $related_ids_cache_hit && function_exists( 'developer_starter_cache_write' ) ) {
    $related_ids_cache_ttl = function_exists( 'developer_starter_get_query_cache_ttl' ) ? developer_starter_get_query_cache_ttl() : 300;
    developer_starter_cache_write( $related_ids_cache_key, $related_post_ids, $related_ids_cache_ttl, $related_ids_cache_args );
}

$related_args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $related_limit,
    'post__in'            => ! empty( $related_post_ids ) ? $related_post_ids : array( 0 ),
    'orderby'             => 'post__in',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
    'update_post_term_cache' => $related_show_category,
);

$related = function_exists( 'developer_starter_run_cached_query' )
    ? developer_starter_run_cached_query( $related_args, 'single_related_posts', array( 'needs_pagination' => false ) )
    : new WP_Query( $related_args );

if ( $related->have_posts() ) :
    ?>
    <section class="related-posts section-padding bg-light">
        <div class="container">
            <h2 class="section-title text-center related-posts__title"><?php echo esc_html( $related_title ); ?></h2>
            <div class="<?php echo esc_attr( $related_grid_class ); ?>">
                <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                    <article class="news-card">
                        <?php
                        $related_thumb = '';
                        if ( $related_show_thumb ) {
                            if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                                $related_thumb = developer_starter_get_featured_image_url( get_the_ID(), 'medium_large' );
                            } elseif ( has_post_thumbnail() ) {
                                $related_thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
                            }

                            if ( empty( $related_thumb ) && has_post_thumbnail( get_the_ID() ) ) {
                                $related_thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
                            }
                            if ( empty( $related_thumb ) ) {
                                $related_post_obj = get_post( get_the_ID() );
                                if ( $related_post_obj && preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $related_post_obj->post_content, $matches ) ) {
                                    $related_thumb = esc_url_raw( $matches[1] );
                                }
                            }
                        }

                        $related_thumb_dims = array( 'width' => 600, 'height' => 400 );
                        if ( function_exists( 'developer_starter_get_post_image_dimensions' ) ) {
                            $related_thumb_dims = developer_starter_get_post_image_dimensions( get_the_ID(), 'medium_large', $related_thumb_dims );
                        }
                        ?>
                        <?php if ( $related_show_thumb && $related_thumb ) : ?>
                            <a href="<?php echo esc_url( get_permalink() ); ?>" class="news-thumb">
                                <img src="<?php echo esc_url( $related_thumb ); ?>" alt="<?php the_title_attribute(); ?>" width="<?php echo esc_attr( (int) $related_thumb_dims['width'] ); ?>" height="<?php echo esc_attr( (int) $related_thumb_dims['height'] ); ?>" loading="lazy" decoding="async" />
                            </a>
                        <?php endif; ?>
                        <div class="news-content">
                            <?php if ( $related_show_category ) : ?>
                                <?php
                                $related_categories = get_the_category();
                                $related_category = ! empty( $related_categories ) ? $related_categories[0] : null;
                                ?>
                                <?php if ( $related_category ) : ?>
                                    <a class="news-category" href="<?php echo esc_url( get_category_link( $related_category->term_id ) ); ?>"><?php echo esc_html( $related_category->name ); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ( $related_show_date ) : ?>
                                <span class="news-date"><?php echo esc_html( get_the_date() ); ?></span>
                            <?php endif; ?>
                            <h3 class="news-title">
                                <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
                            </h3>
                            <?php if ( $related_show_excerpt ) : ?>
                                <?php $related_excerpt = wp_trim_words( get_the_excerpt(), 24 ); ?>
                                <?php if ( '' !== trim( $related_excerpt ) ) : ?>
                                    <p class="news-excerpt"><?php echo esc_html( $related_excerpt ); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php
$related_html = ob_get_clean();
echo $related_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
if ( function_exists( 'developer_starter_set_fragment_cache' ) ) {
    developer_starter_set_fragment_cache( $related_fragment_key, $related_html );
}
