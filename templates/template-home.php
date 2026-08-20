<?php
/**
 * Template Name: 模块化首页
 * Template Post Type: page
 *
 * 使用页面模块系统构建的首页模板
 *
 * @package Developer_Starter
 */

get_header();
$wrapper_classes = function_exists( 'developer_starter_get_page_visual_skin_wrapper_classes' )
    ? developer_starter_get_page_visual_skin_wrapper_classes(
        array( 'page-template', 'template-home' ),
        'templates/template-home.php'
    )
    : 'page-template template-home';
?>

<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
<?php
// 渲染页面配置的所有模块
$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );

if ( ! empty( $modules ) && is_array( $modules ) ) :
    // 调用模块管理器渲染
    developer_starter_render_page_modules();
else :
    // 没有配置模块时显示提示
?>
<section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="container text-center">
        <h2><?php esc_html_e( '请配置页面模块', 'developer-starter' ); ?></h2>
        <p style="color: var(--color-text-muted); margin-top: var(--qiling-space-20);">
            <?php esc_html_e( '在后台编辑此页面，使用「页面模块配置」添加模块来构建页面内容。', 'developer-starter' ); ?>
        </p>
        <?php if ( current_user_can( 'edit_pages' ) ) : ?>
            <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="margin-top: var(--qiling-space-20);">
                <?php esc_html_e( '编辑此页面', 'developer-starter' ); ?>
            </a>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php
// 如果有页面正文内容，也显示出来
while ( have_posts() ) : the_post();
    if ( get_the_content() ) :
?>
<section class="page-content-section section-padding">
    <div class="container">
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
    </div>
</section>
<?php 
    endif;
endwhile;
?>
</div>

<?php get_footer(); ?>
