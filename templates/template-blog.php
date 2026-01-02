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
$modules = get_post_meta( get_the_ID(), '_developer_starter_modules', true );
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
                    <div class="container" style="max-width: 1200px;">
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <!-- 没有内容时的显示 -->
                <section class="blog-empty-state" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
                    <div class="container text-center">
                        <span style="font-size: 4rem; display: block; margin-bottom: 20px;">📝</span>
                        <h2 style="font-size: 2rem; margin-bottom: 15px; color: #1e293b;"><?php _e( '开始构建您的博客', 'developer-starter' ); ?></h2>
                        <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                            <p style="color: #64748b; font-size: 1.1rem; max-width: 500px; margin: 0 auto 30px;">
                                <?php _e( '通过模块构建器添加「博客置顶推荐」和「博客布局」模块，快速创建专业的博客首页', 'developer-starter' ); ?>
                            </p>
                            <a href="<?php echo admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ); ?>" class="btn btn-primary btn-lg">
                                <?php _e( '编辑页面模块', 'developer-starter' ); ?>
                            </a>
                        <?php else : ?>
                            <p style="color: #64748b; font-size: 1.1rem;">
                                <?php _e( '博客内容正在建设中，敬请期待...', 'developer-starter' ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; endif; ?>
        
    <?php endif; ?>
    
</div>

<?php get_footer(); ?>
