<?php
/**
 * Template Name: 最新文章页
 * Template Post Type: page
 *
 * 可手动选择的最新文章聚合模板。
 * 用于在普通页面中展示最新文章流（含分页）。
 *
 * @package Developer_Starter
 * @since 2.2.0
 */

add_action(
    'wp_enqueue_scripts',
    function() {
        wp_enqueue_style(
            'developer-starter-sidebar',
            DEVELOPER_STARTER_ASSETS . '/css/sidebar.css',
            array( 'developer-starter-main' ),
            developer_starter_get_assets_version()
        );
    },
    20
);

get_header();

$page_id = get_the_ID();

$page_title = function_exists( 'developer_starter_get_translated_post_title' )
    ? developer_starter_get_translated_post_title( $page_id )
    : get_the_title( $page_id );

$page_desc = function_exists( 'developer_starter_get_translated_post_excerpt' )
    ? developer_starter_get_translated_post_excerpt( $page_id )
    : get_post_field( 'post_excerpt', $page_id );

if ( '' === trim( (string) $page_title ) ) {
    $page_title = __( '最新文章', 'developer-starter' );
}

$paged = 1;
if ( get_query_var( 'paged' ) ) {
    $paged = absint( get_query_var( 'paged' ) );
} elseif ( get_query_var( 'page' ) ) {
    $paged = absint( get_query_var( 'page' ) );
}
if ( $paged <= 0 ) {
    $paged = 1;
}

$per_page = absint( get_option( 'posts_per_page' ) );
if ( $per_page <= 0 ) {
    $per_page = 10;
}

$loop_settings = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
    ? \Developer_Starter\Core\Blog_Visual_Manager::get_native_loop_settings()
    : array(
        'grid_classes' => 'news-grid grid-cols-3 qiling-native-blog-grid qiling-native-blog-grid-default',
    );

$posts_query = new WP_Query(
    array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $per_page,
        'paged'               => $paged,
        'ignore_sticky_posts' => false,
    )
);

$has_sidebar = is_active_sidebar( 'sidebar-main' );
$has_sidebar = (bool) apply_filters( 'qiling_show_sidebar', $has_sidebar, 'index' );
?>

<style id="qiling-latest-posts-template-inline">
.template-latest-posts .latest-posts-main {
    width: 100%;
}

.template-latest-posts .page-layout.has-sidebar .qiling-native-blog-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 992px) {
    .template-latest-posts .page-layout.has-sidebar .qiling-native-blog-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="page-template template-latest-posts">
    <?php
    $hide_header = get_post_meta( $page_id, '_qiling_hide_page_header', true );
    if ( '1' !== $hide_header ) :
    ?>
        <div class="page-header" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); padding: var(--qiling-space-100) 0 var(--qiling-space-60);">
            <div class="container">
                <h1 class="page-title" style="color: var(--color-neutral-0); text-align: center; font-size: var(--qiling-text-rem-2p5); margin: 0;">
                    <?php echo esc_html( $page_title ); ?>
                </h1>
                <?php if ( ! empty( $page_desc ) ) : ?>
                    <p style="color: rgba(var(--qiling-rgb-255-255-255), 0.85); text-align: center; margin: var(--qiling-space-15) auto 0; max-width: var(--qiling-measure-720);">
                        <?php echo esc_html( wp_strip_all_tags( (string) $page_desc ) ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <section class="latest-posts-home section-padding">
        <div class="container">
            <div class="page-layout <?php echo $has_sidebar ? 'has-sidebar' : 'no-sidebar'; ?>">
                <div class="page-main-content latest-posts-main">
                    <?php if ( $posts_query->have_posts() ) : ?>
                        <?php get_template_part( 'template-parts/blog/post-loop', null, array( 'query' => $posts_query, 'settings' => $loop_settings ) ); ?>
                        <?php get_template_part( 'template-parts/blog/pagination', null, array( 'query' => $posts_query, 'current' => $paged ) ); ?>
                    <?php else : ?>
                        <?php get_template_part( 'template-parts/content/content', 'none' ); ?>
                    <?php endif; ?>
                </div>

                <?php if ( $has_sidebar ) : ?>
                    <div class="page-sidebar">
                        <?php get_sidebar(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php
get_footer();
