<?php
/**
 * Shared native blog pagination.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'query'   => null,
        'current' => 0,
    )
);

$query = $args['query'] instanceof WP_Query ? $args['query'] : $GLOBALS['wp_query'];
if ( ! $query instanceof WP_Query || $query->max_num_pages <= 1 ) {
    return;
}

$current = absint( $args['current'] );
if ( $current <= 0 ) {
    $current = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
}
?>
<nav class="pagination-nav qiling-blog-pagination" style="margin-top: var(--qiling-space-50); text-align: center;">
    <?php if ( $args['query'] instanceof WP_Query ) : ?>
        <?php
        $pagination = paginate_links(
            array(
                'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'format'    => '?paged=%#%',
                'current'   => $current,
                'total'     => max( 1, (int) $query->max_num_pages ),
                'mid_size'  => 2,
                'prev_text' => sprintf( '&laquo; %s', __( '上一页', 'developer-starter' ) ),
                'next_text' => sprintf( '%s &raquo;', __( '下一页', 'developer-starter' ) ),
            )
        );

        if ( $pagination ) {
            echo wp_kses_post( $pagination );
        }
        ?>
    <?php else : ?>
        <?php
        the_posts_pagination(
            array(
                'mid_size'  => 2,
                'prev_text' => sprintf( '&laquo; %s', __( '上一页', 'developer-starter' ) ),
                'next_text' => sprintf( '%s &raquo;', __( '下一页', 'developer-starter' ) ),
            )
        );
        ?>
    <?php endif; ?>
</nav>
