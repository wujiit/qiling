<?php
/**
 * Single post meta statistics and interaction buttons.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'post_id'               => get_the_ID(),
        'post_views_enable'     => false,
        'reading_time_enable'   => false,
        'post_modified_date_enable' => false,
        'post_modified_date'    => '',
        'post_modified_timestamp' => 0,
        'post_like_enable'      => false,
        'post_favorite_enable'  => false,
        'post_views'            => 0,
        'reading_time'          => 0,
        'like_count'            => 0,
        'favorite_count'        => 0,
        'like_active'           => false,
        'favorite_active'       => false,
        'post_header_meta_style'=> 'rgba(var(--qiling-rgb-255-255-255), 0.6)',
        'class'                 => '',
        'hide_post_publish_date'=> false,
        'hide_post_author'      => false,
        'hide_post_comment_count' => false,
    )
);

$comments_enabled = function_exists( 'developer_starter_comments_feature_enabled' ) ? developer_starter_comments_feature_enabled() : true;
$nonce = wp_create_nonce( 'ds_post_interaction' );
$class_names = trim( 'post-meta-stats single-post-meta-stats ' . sanitize_html_class( (string) $args['class'] ) );
$author_id = (int) get_post_field( 'post_author', (int) $args['post_id'] );
$author_url = $author_id > 0 ? get_author_posts_url( $author_id ) : '';
$show_publish_date = empty( $args['hide_post_publish_date'] );
$show_author = empty( $args['hide_post_author'] );
$show_modified_date = $args['post_modified_date_enable'] && '' !== trim( (string) $args['post_modified_date'] );
$show_comment_count = empty( $args['hide_post_comment_count'] ) && $comments_enabled && ( comments_open() || get_comments_number() );
$has_meta_items = $show_publish_date
    || $show_author
    || $show_modified_date
    || ! empty( $args['post_views_enable'] )
    || ! empty( $args['reading_time_enable'] )
    || ! empty( $args['post_like_enable'] )
    || ! empty( $args['post_favorite_enable'] );

if ( ! $has_meta_items ) {
    return;
}
?>
<div class="<?php echo esc_attr( $class_names ); ?>">
    <?php if ( $show_publish_date ) : ?>
        <span class="meta-stat">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <?php echo esc_html( get_the_date() ); ?>
        </span>
    <?php endif; ?>

    <?php if ( $show_author ) : ?>
        <span class="meta-stat">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <?php if ( $author_url ) : ?>
                <a class="meta-stat__link" href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( get_the_author() ); ?></a>
            <?php else : ?>
                <?php echo esc_html( get_the_author() ); ?>
            <?php endif; ?>
        </span>
    <?php endif; ?>

    <?php if ( $show_modified_date ) : ?>
        <span class="meta-stat">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
            <?php
            printf(
                /* translators: %s: modified date */
                esc_html__( '更新于 %s', 'developer-starter' ),
                esc_html( $args['post_modified_date'] )
            );
            ?>
        </span>
    <?php endif; ?>

    <?php if ( $args['post_views_enable'] ) : ?>
        <span class="meta-stat">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <?php printf( esc_html__( '%s 阅读', 'developer-starter' ), esc_html( number_format_i18n( (int) $args['post_views'] ) ) ); ?>
        </span>
    <?php endif; ?>

    <?php if ( $args['reading_time_enable'] ) : ?>
        <span class="meta-stat">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <?php printf( esc_html__( '%d 分钟阅读', 'developer-starter' ), absint( $args['reading_time'] ) ); ?>
        </span>
    <?php endif; ?>

    <?php if ( $show_comment_count ) : ?>
        <span class="meta-stat">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <?php printf( esc_html( _n( '%s 评论', '%s 评论', get_comments_number(), 'developer-starter' ) ), esc_html( number_format_i18n( get_comments_number() ) ) ); ?>
        </span>
    <?php endif; ?>

    <?php if ( $args['post_like_enable'] ) : ?>
        <button type="button" class="<?php echo esc_attr( 'meta-stat ds-interaction-btn' . ( $args['like_active'] ? ' is-active' : '' ) ); ?>" data-post-id="<?php echo esc_attr( (int) $args['post_id'] ); ?>" data-type="like" data-nonce="<?php echo esc_attr( $nonce ); ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6c-1.9-1.9-5-1.9-6.9 0L12 6.5l-1.9-1.9c-1.9-1.9-5-1.9-6.9 0s-1.9 5 0 6.9L12 21.2l8.8-9.7c1.9-1.9 1.9-5 0-6.9z"></path></svg>
            <span><?php esc_html_e( '点赞', 'developer-starter' ); ?></span>
            <span class="ds-interaction-count"><?php echo esc_html( number_format_i18n( (int) $args['like_count'] ) ); ?></span>
        </button>
    <?php endif; ?>

    <?php if ( $args['post_favorite_enable'] ) : ?>
        <button type="button" class="<?php echo esc_attr( 'meta-stat ds-interaction-btn' . ( $args['favorite_active'] ? ' is-active' : '' ) ); ?>" data-post-id="<?php echo esc_attr( (int) $args['post_id'] ); ?>" data-type="favorite" data-nonce="<?php echo esc_attr( $nonce ); ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
            <span><?php esc_html_e( '收藏', 'developer-starter' ); ?></span>
            <span class="ds-interaction-count"><?php echo esc_html( number_format_i18n( (int) $args['favorite_count'] ) ); ?></span>
        </button>
    <?php endif; ?>
</div>
