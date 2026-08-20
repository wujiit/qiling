<?php
/**
 * Native blog post card.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'settings' => array(),
    )
);

$incoming_settings = is_array( $args['settings'] ) ? $args['settings'] : array();
$fallback_settings = array(
    'preset'            => 'default',
    'show_thumb'        => true,
    'show_excerpt'      => true,
    'show_date'         => true,
    'show_author'       => true,
    'show_category'     => true,
    'show_reading_time' => false,
    'show_views'        => function_exists( 'developer_starter_get_option' ) ? (bool) developer_starter_get_option( 'post_views_enable', '' ) : false,
    'excerpt_length'    => 25,
    'thumb_height'      => 220,
    'thumb_fit'         => function_exists( 'developer_starter_get_thumbnail_display_mode' ) ? developer_starter_get_thumbnail_display_mode() : 'cover',
    'card_classes'      => 'news-card qiling-native-blog-card qiling-native-blog-card-default',
);
$default_settings = empty( $incoming_settings ) && class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
    ? wp_parse_args( \Developer_Starter\Core\Blog_Visual_Manager::get_native_loop_settings(), $fallback_settings )
    : $fallback_settings;

$settings = wp_parse_args( $incoming_settings, $default_settings );
$preset = isset( $settings['preset'] ) ? sanitize_key( (string) $settings['preset'] ) : 'default';
$show_thumb = ! empty( $settings['show_thumb'] );
$show_excerpt = ! empty( $settings['show_excerpt'] );
$show_date = ! empty( $settings['show_date'] );
$show_author = ! empty( $settings['show_author'] );
$show_category = ! empty( $settings['show_category'] );
$show_reading_time = ! empty( $settings['show_reading_time'] ) && class_exists( '\Developer_Starter\Core\Post_Enhancer' );
$show_views = ! empty( $settings['show_views'] ) && class_exists( '\Developer_Starter\Core\Post_Enhancer' );
$excerpt_length = isset( $settings['excerpt_length'] ) ? max( 1, absint( $settings['excerpt_length'] ) ) : 25;
$thumb_height = isset( $settings['thumb_height'] ) ? max( 1, absint( $settings['thumb_height'] ) ) : 220;
$thumb_fit = isset( $settings['thumb_fit'] ) ? sanitize_html_class( (string) $settings['thumb_fit'] ) : 'cover';
$card_classes = isset( $settings['card_classes'] ) ? trim( (string) $settings['card_classes'] ) : 'news-card';

$post_id = get_the_ID();
$is_sticky = is_sticky( $post_id );
if ( $is_sticky ) {
    $card_classes .= ' qiling-post-card-is-sticky';
}

$thumb_url = '';
if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
    $thumb_url = developer_starter_get_thumbnail_url( $post_id, 'medium' );
} elseif ( has_post_thumbnail() ) {
    $thumb_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
} elseif ( function_exists( 'developer_starter_get_first_image' ) ) {
    $thumb_url = developer_starter_get_first_image( $post_id );
}

$thumb_is_placeholder = false;
if ( $show_thumb && '' === trim( (string) $thumb_url ) ) {
    $thumb_url = get_theme_file_uri( 'assets/images/default-thumbnail.svg' );
    $thumb_is_placeholder = true;
}

$thumb_dims = array(
    'width'  => 600,
    'height' => 400,
);
if ( ! $thumb_is_placeholder && function_exists( 'developer_starter_get_post_image_dimensions' ) ) {
    $thumb_dims = developer_starter_get_post_image_dimensions(
        $post_id,
        'medium',
        array(
            'width'  => 600,
            'height' => 400,
        )
    );
}

$thumb_alt = get_the_title( $post_id );
$thumbnail_id = get_post_thumbnail_id( $post_id );
if ( $thumbnail_id ) {
    $attachment_alt = trim( (string) get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) );
    if ( '' !== $attachment_alt ) {
        $thumb_alt = $attachment_alt;
    }
}

$categories = $show_category ? get_the_category() : array();
$author_id = (int) get_post_field( 'post_author', $post_id );
$author_name = $author_id > 0 ? get_the_author_meta( 'display_name', $author_id ) : get_the_author();
$author_url = $author_id > 0 ? get_author_posts_url( $author_id ) : '';
$reading_time = 0;
if ( $show_reading_time ) {
    $reading_time = max( 1, (int) \Developer_Starter\Core\Post_Enhancer::get_reading_time( $post_id ) );
}
$views_count = 0;
if ( $show_views ) {
    $views_count = max( 0, (int) \Developer_Starter\Core\Post_Enhancer::get_post_views( $post_id ) );
}

$has_meta_row = ( $show_category && ! empty( $categories ) ) || $show_date || $show_author || $show_reading_time || $show_views;
$sticky_cover_badges = function_exists( 'developer_starter_get_post_cover_badges' )
    ? developer_starter_get_post_cover_badges(
        $post_id,
        array(
            'context'                 => 'native_blog_card',
            'include_types'           => array( 'sticky' ),
            'include_app_badge'       => false,
            'include_album_badge'     => false,
            'include_resource_badges' => false,
            'include_sticky_badge'    => true,
            'ignore_max_count'        => true,
            'sticky_badge_class'      => 'qiling-post-card-badge qiling-post-card-badge-sticky',
        )
    )
    : array();
$sticky_inline_badges = function_exists( 'developer_starter_get_post_cover_badges' )
    ? developer_starter_get_post_cover_badges(
        $post_id,
        array(
            'context'                 => 'native_blog_card',
            'include_types'           => array( 'sticky' ),
            'include_app_badge'       => false,
            'include_album_badge'     => false,
            'include_resource_badges' => false,
            'include_sticky_badge'    => true,
            'ignore_max_count'        => true,
            'sticky_badge_class'      => 'qiling-post-card-badge qiling-post-card-badge-inline',
        )
    )
    : array();
?>
<article <?php post_class( $card_classes ); ?> data-aos="fade-up">
    <?php if ( $show_thumb && $thumb_url ) : ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>" class="news-thumb<?php echo $thumb_is_placeholder ? ' qiling-news-thumb-placeholder' : ''; ?>">
            <img
                src="<?php echo esc_url( $thumb_url ); ?>"
                alt="<?php echo esc_attr( $thumb_alt ); ?>"
                width="<?php echo esc_attr( (int) $thumb_dims['width'] ); ?>"
                height="<?php echo esc_attr( (int) $thumb_height ); ?>"
                loading="lazy"
                decoding="async"
                sizes="(max-width: 768px) 100vw, 33vw"
                style="height: <?php echo esc_attr( (int) $thumb_height ); ?>px; object-fit: <?php echo esc_attr( $thumb_fit ); ?>;"
            />
            <?php if ( ! empty( $sticky_cover_badges ) && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                <?php echo developer_starter_get_post_cover_badges_html( $sticky_cover_badges, array( 'context' => 'native_blog_card', 'wrapper' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <div class="news-content">
        <?php if ( $is_sticky && ! $show_thumb && ! empty( $sticky_inline_badges ) && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
            <?php echo developer_starter_get_post_cover_badges_html( $sticky_inline_badges, array( 'context' => 'native_blog_card', 'wrapper' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php endif; ?>

        <?php if ( $has_meta_row ) : ?>
            <div class="qiling-blog-card-meta qiling-blog-card-meta-<?php echo esc_attr( $preset ); ?>">
                <?php if ( $show_category && ! empty( $categories ) ) : ?>
                    <a class="qiling-blog-meta-chip qiling-blog-category-chip" href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
                        <?php echo esc_html( $categories[0]->name ); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $show_date ) : ?>
                    <span class="news-date qiling-blog-meta-chip qiling-blog-date-chip"><?php echo esc_html( get_the_date() ); ?></span>
                <?php endif; ?>

                <?php if ( $show_author && '' !== trim( (string) $author_name ) ) : ?>
                    <?php if ( $author_url ) : ?>
                        <a class="news-author qiling-blog-meta-chip qiling-blog-author-chip" href="<?php echo esc_url( $author_url ); ?>">
                            <?php echo esc_html( $author_name ); ?>
                        </a>
                    <?php else : ?>
                        <span class="news-author qiling-blog-meta-chip qiling-blog-author-chip"><?php echo esc_html( $author_name ); ?></span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ( $show_reading_time && $reading_time > 0 ) : ?>
                    <span class="qiling-blog-meta-chip qiling-blog-reading-chip">
                        <?php printf( esc_html__( '%d 分钟阅读', 'developer-starter' ), $reading_time ); ?>
                    </span>
                <?php endif; ?>

                <?php if ( $show_views ) : ?>
                    <span class="qiling-blog-meta-chip qiling-blog-views-chip">
                        <?php printf( esc_html__( '%s 次浏览', 'developer-starter' ), esc_html( number_format_i18n( $views_count ) ) ); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h2 class="news-title">
            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
        </h2>

        <?php if ( $show_excerpt ) : ?>
            <p class="news-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $excerpt_length ) ); ?></p>
        <?php endif; ?>
    </div>
</article>
