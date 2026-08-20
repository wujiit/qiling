<?php
/**
 * The template for displaying archive pages
 *
 * @package Developer_Starter
 */

get_header();

global $wp_query;

$archive_theme_options = function_exists( 'developer_starter_get_options_cache' ) ? developer_starter_get_options_cache() : array();
$archive_option_enabled = static function ( $key, $default = true ) use ( $archive_theme_options ) {
    if ( is_array( $archive_theme_options ) && array_key_exists( $key, $archive_theme_options ) ) {
        return '1' === (string) $archive_theme_options[ $key ];
    }

    return (bool) $default;
};
$archive_header_enabled = $archive_option_enabled( 'archive_header_enable', true );
$archive_breadcrumb_enabled = $archive_option_enabled( 'archive_breadcrumb_enable', true );
$archive_kicker_enabled = $archive_option_enabled( 'archive_show_kicker', true );
$archive_description_enabled = $archive_option_enabled( 'archive_show_description', true );
$archive_count_enabled = $archive_option_enabled( 'archive_show_count', true );
$archive_empty_title = isset( $archive_theme_options['archive_empty_title'] ) ? trim( wp_strip_all_tags( (string) $archive_theme_options['archive_empty_title'] ) ) : '';
if ( '' === $archive_empty_title ) {
    $archive_empty_title = __( '暂时没有内容', 'developer-starter' );
}
$archive_empty_text = isset( $archive_theme_options['archive_empty_text'] ) ? trim( wp_strip_all_tags( (string) $archive_theme_options['archive_empty_text'] ) ) : '';
if ( '' === $archive_empty_text ) {
    $archive_empty_text = __( '这个归档下暂时还没有公开文章，可以换个关键词或返回首页继续浏览。', 'developer-starter' );
}

$loop_settings = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
    ? \Developer_Starter\Core\Blog_Visual_Manager::get_native_loop_settings()
    : array(
        'grid_classes' => 'news-grid grid-cols-3 qiling-native-blog-grid qiling-native-blog-grid-default',
    );

$queried_object      = get_queried_object();
$archive_title       = wp_strip_all_tags( get_the_archive_title() );
$archive_description = get_the_archive_description();
$archive_kicker      = __( '内容归档', 'developer-starter' );
$archive_count       = ( $wp_query instanceof WP_Query ) ? (int) $wp_query->found_posts : 0;
$archive_meta_items  = array();

if ( is_tag() && $queried_object && isset( $queried_object->term_id ) ) {
    $archive_title       = single_tag_title( '', false );
    $archive_description = term_description( (int) $queried_object->term_id, $queried_object->taxonomy );
    $archive_kicker      = __( '标签归档', 'developer-starter' );
    $archive_count       = (int) $queried_object->count;
    $archive_meta_items[] = sprintf(
        /* translators: %s: post count */
        _n( '%s 篇内容', '%s 篇内容', $archive_count, 'developer-starter' ),
        number_format_i18n( $archive_count )
    );
} elseif ( is_date() ) {
    $archive_kicker = __( '日期归档', 'developer-starter' );
    if ( is_day() ) {
        $archive_title = get_the_date();
    } elseif ( is_month() ) {
        $archive_title = get_the_date( _x( 'Y年n月', 'monthly archives date format', 'developer-starter' ) );
    } elseif ( is_year() ) {
        $archive_title = get_the_date( _x( 'Y年', 'yearly archives date format', 'developer-starter' ) );
    }
    if ( '' === trim( (string) $archive_description ) ) {
        $archive_description = sprintf(
            /* translators: %s: archive date */
            __( '这里汇总了 %s 发布的内容。', 'developer-starter' ),
            $archive_title
        );
    }
    $archive_meta_items[] = sprintf(
        /* translators: %s: post count */
        _n( '%s 篇内容', '%s 篇内容', $archive_count, 'developer-starter' ),
        number_format_i18n( $archive_count )
    );
} elseif ( is_tax() && $queried_object && isset( $queried_object->term_id ) ) {
    $taxonomy        = get_taxonomy( $queried_object->taxonomy );
    $archive_title   = single_term_title( '', false );
    $archive_kicker  = $taxonomy && isset( $taxonomy->labels->singular_name ) ? $taxonomy->labels->singular_name : __( '专题归档', 'developer-starter' );
    $archive_count   = (int) $queried_object->count;
    $archive_meta_items[] = sprintf(
        /* translators: %s: post count */
        _n( '%s 篇内容', '%s 篇内容', $archive_count, 'developer-starter' ),
        number_format_i18n( $archive_count )
    );
} elseif ( is_post_type_archive() ) {
    $archive_kicker = __( '类型归档', 'developer-starter' );
    $post_type      = get_query_var( 'post_type' );
    if ( is_array( $post_type ) ) {
        $post_type = reset( $post_type );
    }
    $post_type_object = $post_type ? get_post_type_object( $post_type ) : null;
    if ( $post_type_object && isset( $post_type_object->labels->name ) ) {
        $archive_title = $post_type_object->labels->name;
    }
    $archive_meta_items[] = sprintf(
        /* translators: %s: post count */
        _n( '%s 篇内容', '%s 篇内容', $archive_count, 'developer-starter' ),
        number_format_i18n( $archive_count )
    );
} else {
    $archive_meta_items[] = sprintf(
        /* translators: %s: post count */
        _n( '%s 篇内容', '%s 篇内容', $archive_count, 'developer-starter' ),
        number_format_i18n( $archive_count )
    );
}
?>

<?php if ( $archive_header_enabled ) : ?>
<div class="page-header page-header--hero archive-page-header">
    <div class="container">
        <?php if ( $archive_breadcrumb_enabled ) : ?>
        <nav class="archive-page-header__breadcrumb" aria-label="<?php esc_attr_e( '归档面包屑', 'developer-starter' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '首页', 'developer-starter' ); ?></a>
            <span aria-hidden="true">/</span>
            <span><?php echo esc_html( $archive_title ); ?></span>
        </nav>
        <?php endif; ?>
        <?php if ( $archive_kicker_enabled ) : ?>
        <p class="archive-page-header__kicker"><?php echo esc_html( $archive_kicker ); ?></p>
        <?php endif; ?>
        <h1 class="page-title archive-page-header__title">
            <?php echo esc_html( $archive_title ); ?>
        </h1>
        <?php if ( $archive_description_enabled && '' !== trim( (string) $archive_description ) ) : ?>
            <div class="archive-page-header__description">
                <?php echo wp_kses_post( $archive_description ); ?>
            </div>
        <?php endif; ?>
        <?php if ( $archive_count_enabled && ! empty( $archive_meta_items ) ) : ?>
            <div class="archive-page-header__meta" aria-label="<?php esc_attr_e( '归档统计', 'developer-starter' ); ?>">
                <?php foreach ( $archive_meta_items as $archive_meta_item ) : ?>
                    <span class="archive-page-header__meta-item"><?php echo esc_html( $archive_meta_item ); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<section class="archive-content section-padding">
    <div class="container">
        <?php if ( have_posts() ) : ?>
            <?php get_template_part( 'template-parts/blog/post-loop', null, array( 'settings' => $loop_settings ) ); ?>
            <?php get_template_part( 'template-parts/blog/pagination' ); ?>
        <?php else : ?>
            <div class="qiling-archive-empty text-center">
                <h2><?php echo esc_html( $archive_empty_title ); ?></h2>
                <p><?php echo esc_html( $archive_empty_text ); ?></p>
                <div class="qiling-archive-empty__search">
                    <?php get_search_form(); ?>
                </div>
                <a class="qiling-archive-empty__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( '返回首页', 'developer-starter' ); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
