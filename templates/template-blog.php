<?php
/**
 * Template Name: 博客页面
 * Template Post Type: page
 *
 * 博客页面模板 - 用于创建完整的博客首页
 * 不显示面包屑导航，支持分页功能
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 加载博客页面专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-blog-page',
        DEVELOPER_STARTER_ASSETS . '/css/blog-page.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();

// 获取页面模块
$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );

// 获取当前分页 - 静态页面使用 'page'，归档页面使用 'paged'
$paged = 1;
if ( get_query_var( 'paged' ) ) {
    $paged = absint( get_query_var( 'paged' ) );
} elseif ( get_query_var( 'page' ) ) {
    $paged = absint( get_query_var( 'page' ) );
}
?>

<div class="page-template template-blog-page">
    
    <?php if ( $has_modules ) : ?>
        <?php 
        // 传递分页参数给模块渲染器
        global $blog_page_paged;
        $blog_page_paged = $paged;
        
        developer_starter_render_page_modules(); 
        ?>
    <?php else : ?>
        
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php if ( get_the_content() ) : ?>
                <!-- 如果有页面内容，显示内容 -->
                <div class="blog-page-content section-padding">
                    <div class="container" style="max-width: var(--qiling-measure-1200);">
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <!-- 没有内容时的显示 -->
                <section class="blog-empty-state" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
                    <div class="container text-center">
                        <span style="font-size: var(--qiling-text-rem-4); display: block; margin-bottom: var(--qiling-space-20);">📝</span>
                        <h2 style="font-size: var(--qiling-text-rem-2); margin-bottom: var(--qiling-space-15); color: var(--color-neutral-800);"><?php esc_html_e( '开始构建您的博客', 'developer-starter' ); ?></h2>
                        <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                            <p style="color: var(--color-text-muted); font-size: var(--qiling-text-rem-1p1); max-width: var(--qiling-measure-500); margin: 0 auto var(--qiling-space-30);">
                                <?php esc_html_e( '通过模块构建器添加「博客置顶推荐」和「博客布局」模块，快速创建专业的博客首页', 'developer-starter' ); ?>
                            </p>
                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>" class="btn btn-primary btn-lg">
                                <?php esc_html_e( '编辑页面模块', 'developer-starter' ); ?>
                            </a>
                        <?php else : ?>
                            <p style="color: var(--color-text-muted); font-size: var(--qiling-text-rem-1p1);">
                                <?php esc_html_e( '博客内容正在建设中，敬请期待...', 'developer-starter' ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; endif; ?>
        
    <?php endif; ?>
    
</div>

<?php get_footer(); ?>
