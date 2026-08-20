<?php
/**
 * Template Name: 专题页面
 * Template Post Type: page
 *
 * 专题聚合页模板：用于专题策划、标签聚合和文章流编排。
 *
 * @package Developer_Starter
 * @since 2.1.3
 */

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-blog-page',
        DEVELOPER_STARTER_ASSETS . '/css/blog-page.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();

$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );

$paged = 1;
if ( get_query_var( 'paged' ) ) {
    $paged = absint( get_query_var( 'paged' ) );
} elseif ( get_query_var( 'page' ) ) {
    $paged = absint( get_query_var( 'page' ) );
}
?>

<div class="page-template template-topic-page">
    <?php if ( $has_modules ) : ?>
        <?php
        global $blog_page_paged;
        $blog_page_paged = $paged;
        developer_starter_render_page_modules();
        ?>
    <?php else : ?>
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php if ( get_the_content() ) : ?>
                <div class="topic-page-content section-padding">
                    <div class="container" style="max-width: var(--qiling-measure-1200);">
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <section class="topic-empty-state" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
                    <div class="container text-center">
                        <span style="font-size: var(--qiling-text-rem-4); display: block; margin-bottom: var(--qiling-space-20);">📚</span>
                        <h2 style="font-size: var(--qiling-text-rem-2); margin-bottom: var(--qiling-space-15); color: var(--color-neutral-800);"><?php esc_html_e( '开始构建专题页', 'developer-starter' ); ?></h2>
                        <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                            <p style="color: var(--color-text-muted); font-size: var(--qiling-text-rem-1p1); max-width: var(--qiling-measure-560); margin: 0 auto var(--qiling-space-30);">
                                <?php esc_html_e( '可组合「博客置顶推荐」「分类标签切换」「博客布局」模块，快速搭建专题聚合页面。', 'developer-starter' ); ?>
                            </p>
                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>" class="btn btn-primary btn-lg">
                                <?php esc_html_e( '编辑专题模块', 'developer-starter' ); ?>
                            </a>
                        <?php else : ?>
                            <p style="color: var(--color-text-muted); font-size: var(--qiling-text-rem-1p1);">
                                <?php esc_html_e( '专题内容正在建设中，敬请期待...', 'developer-starter' ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; endif; ?>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
