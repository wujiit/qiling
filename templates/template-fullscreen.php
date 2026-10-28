<?php
/**
 * Template Name: 全屏模式
 * Template Post Type: page
 * 
 * 全屏页面模板 - 无侧边栏，无内容宽度限制，100% 浏览器宽度
 *
 * @package Developer_Starter
 */

get_header();
?>



<article class="page-content fullscreen-page">
    <?php
    // 检查是否有模块配置
    $modules = function_exists( 'developer_starter_get_page_modules_data' )
        ? developer_starter_get_page_modules_data( get_the_ID() )
        : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
    
    if ( ! empty( $modules ) && is_array( $modules ) ) :
        // 渲染模块 - 全屏模式
        developer_starter_render_page_modules();
    else :
        // 显示普通页面内容 - 全屏无容器限制
    ?>
        <div class="entry-content fullscreen-content">
            <?php
            while ( have_posts() ) :
                the_post();
                the_content();
                
                wp_link_pages( array(
                    'before' => '<div class="page-links">',
                    'after'  => '</div>',
                ) );
            endwhile;
            ?>
        </div>
    <?php endif; ?>
</article>

<?php get_footer(); ?>
