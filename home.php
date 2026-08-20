<?php
/**
 * 最新文章首页模板
 *
 * 当「设置 -> 阅读 -> 您的主页显示」选择「最新文章」时，WordPress 会优先使用本模板。
 *
 * @package Developer_Starter
 */

get_header();

$posts_page_id = (int) get_option( 'page_for_posts' );
$page_title    = '';
$page_desc     = '';

if ( $posts_page_id > 0 ) {
    $page_title = function_exists( 'developer_starter_get_translated_post_title' )
        ? developer_starter_get_translated_post_title( $posts_page_id )
        : get_the_title( $posts_page_id );

    $page_desc = function_exists( 'developer_starter_get_translated_post_excerpt' )
        ? developer_starter_get_translated_post_excerpt( $posts_page_id )
        : get_post_field( 'post_excerpt', $posts_page_id );
}

if ( '' === trim( (string) $page_title ) ) {
    $page_title = is_front_page() ? get_bloginfo( 'name' ) : __( '最新文章', 'developer-starter' );
}

if ( '' === trim( (string) $page_desc ) ) {
    $page_desc = get_bloginfo( 'description' );
}

$has_sidebar = is_active_sidebar( 'sidebar-main' );
$has_sidebar = (bool) apply_filters( 'qiling_show_sidebar', $has_sidebar, 'index' );
$loop_settings = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
    ? \Developer_Starter\Core\Blog_Visual_Manager::get_native_loop_settings()
    : array(
        'grid_classes' => 'news-grid grid-cols-3 qiling-native-blog-grid qiling-native-blog-grid-default',
    );
?>

<style id="qiling-latest-posts-home-inline">
.latest-posts-home .latest-posts-main {
    width: 100%;
}

.latest-posts-home .page-layout.has-sidebar .qiling-native-blog-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 992px) {
    .latest-posts-home .page-layout.has-sidebar .qiling-native-blog-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="page-header latest-posts-header" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); padding: 100px 0 60px;">
    <div class="container">
        <h1 class="page-title" style="color: #fff; text-align: center; font-size: 2.5rem; margin: 0;">
            <?php echo esc_html( $page_title ); ?>
        </h1>
        <?php if ( ! empty( $page_desc ) ) : ?>
            <p style="color: rgba(255,255,255,0.85); text-align: center; margin: 15px auto 0; max-width: 720px;">
                <?php echo esc_html( wp_strip_all_tags( (string) $page_desc ) ); ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<section class="latest-posts-home section-padding">
    <div class="container">
        <div class="page-layout <?php echo $has_sidebar ? 'has-sidebar' : 'no-sidebar'; ?>">
            <div class="page-main-content latest-posts-main">
                <?php if ( have_posts() ) : ?>
                    <?php get_template_part( 'template-parts/blog/post-loop', null, array( 'settings' => $loop_settings ) ); ?>
                    <?php get_template_part( 'template-parts/blog/pagination' ); ?>
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

<?php get_footer(); ?>
