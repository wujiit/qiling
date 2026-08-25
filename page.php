<?php
/**
 * 默认页面模板（带侧边栏）
 *
 * @package Developer_Starter
 */

get_header();
$page_id = absint( get_queried_object_id() );

$options = function_exists( 'developer_starter_get_options_cache' ) ? developer_starter_get_options_cache() : array();
if ( ! is_array( $options ) ) {
    $options = array();
}
$basic_page_option_enabled = static function ( $key, $default = true ) use ( $options ) {
    if ( array_key_exists( $key, $options ) ) {
        return '1' === (string) $options[ $key ];
    }
    return (bool) $default;
};

$basic_page_header_enabled = $basic_page_option_enabled( 'basic_page_header_enable', true );
$basic_page_header_description_enabled = $basic_page_option_enabled( 'basic_page_header_description_enable', true );
$basic_page_content_padding_enabled = $basic_page_option_enabled( 'basic_page_content_padding_enable', true );
$basic_page_sidebar_enabled = $basic_page_option_enabled( 'basic_page_sidebar_enable', true );
$basic_page_featured_image_enabled = $basic_page_option_enabled( 'basic_page_featured_image_enable', false );
$basic_page_links_enabled = $basic_page_option_enabled( 'basic_page_links_enable', true );
$basic_page_comments_enabled = $basic_page_option_enabled( 'basic_page_comments_enable', false );

$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( $page_id )
    : get_post_meta( $page_id, '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );

if ( $basic_page_header_enabled ) {
    $basic_page_header_description_filter = static function ( $description, $template, $filter_post_id ) use ( $page_id, $basic_page_header_description_enabled ) {
        return ( ! $basic_page_header_description_enabled && (int) $filter_post_id === (int) $page_id ) ? '' : $description;
    };

    add_filter( 'qiling_page_header_description', $basic_page_header_description_filter, 10, 3 );
    \Developer_Starter\Core\Page_Header::render( 'default' );
    remove_filter( 'qiling_page_header_description', $basic_page_header_description_filter, 10 );
}

$page_article_classes = array( 'page-content' );
if ( $basic_page_content_padding_enabled ) {
    $page_article_classes[] = 'section-padding';
}
if ( $has_modules ) {
    $page_article_classes[] = 'page-content--builder';
}
?>

<article id="post-<?php echo esc_attr( $page_id ); ?>" <?php post_class( $page_article_classes, $page_id ); ?>>
    <div class="container">
        <?php
        // 检查侧边栏是否激活
        $has_sidebar = ! $has_modules && $basic_page_sidebar_enabled && is_active_sidebar( 'sidebar-page' );
        $has_sidebar = apply_filters( 'qiling_show_sidebar', $has_sidebar, 'page' );

        if ( $has_modules ) :
            // 模块页面始终全宽
            developer_starter_render_page_modules();
        else :
            // 普通页面内容 - 根据侧边栏状态调整布局
        ?>
            <div class="page-layout <?php echo esc_attr( $has_sidebar ? 'has-sidebar' : 'no-sidebar' ); ?>">
                <div class="page-main-content">
                    <div class="entry-content">
                        <?php
                        while ( have_posts() ) :
                            the_post();

                            if ( $basic_page_featured_image_enabled && has_post_thumbnail() ) :
                                ?>
                                <figure class="basic-page-featured-image">
                                    <?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
                                </figure>
                                <?php
                            endif;

                            the_content();

                            if ( $basic_page_links_enabled ) {
                                wp_link_pages(
                                    array(
                                        'before'      => '<nav class="page-links" aria-label="' . esc_attr__( '页面分页', 'developer-starter' ) . '"><span class="page-links__label">' . esc_html__( '分页：', 'developer-starter' ) . '</span>',
                                        'after'       => '</nav>',
                                        'link_before' => '<span class="page-links__item">',
                                        'link_after'  => '</span>',
                                    )
                                );
                            }
                        endwhile;
                        ?>
                    </div>

                    <?php if ( $basic_page_comments_enabled && ( function_exists( 'developer_starter_comments_feature_enabled' ) ? developer_starter_comments_feature_enabled() : true ) && comments_open() ) : ?>
                        <div class="basic-page-comments">
                            <?php comments_template(); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ( $has_sidebar ) : ?>
                    <div class="page-sidebar">
                        <?php get_sidebar(); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>

<?php get_footer(); ?>
