<?php
/**
 * Template Name: 全宽模式
 * Template Post Type: page
 * 
 * 全宽页面模板 - 无侧边栏的全宽页面
 *
 * @package Developer_Starter
 */

get_header();
?>

<?php \Developer_Starter\Core\Page_Header::render( 'fullwidth' ); ?>

<article class="page-content section-padding fullwidth-page">
    <div class="container">
        <?php
        // 检查是否有模块配置
        $modules = function_exists( 'developer_starter_get_page_modules_data' )
            ? developer_starter_get_page_modules_data( get_the_ID() )
            : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
        
        if ( ! empty( $modules ) && is_array( $modules ) ) :
            // 渲染模块
            developer_starter_render_page_modules();
        else :
            // 显示普通页面内容
        ?>
            <div class="entry-content fullwidth-content">
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
    </div>
</article>

<?php get_footer(); ?>
