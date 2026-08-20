<?php
/**
 * Template Name: 个人简历
 * Template Post Type: page
 *
 * 使用页面模块系统构建的个人简历模板
 * 适合作品集、个人介绍、简历展示网站
 *
 * @package Developer_Starter
 */

get_header( 'resume' );
?>

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
<section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: #0f172a;">
    <div class="container text-center">
        <h2 style="color: #fff;"><?php esc_html_e( '请配置简历模块', 'developer-starter' ); ?></h2>
        <p style="color: rgba(255,255,255,0.7); margin-top: 20px;">
            <?php esc_html_e( '在后台编辑此页面，使用「页面模块配置」添加简历相关模块来构建页面内容。', 'developer-starter' ); ?><br>
            <?php esc_html_e( '推荐模块：个人简历首屏、技能进度条、经历时间线、作品集、客户评价、联系表单', 'developer-starter' ); ?>
        </p>
        <?php if ( current_user_can( 'edit_pages' ) ) : ?>
            <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="margin-top: 20px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); border: none; padding: 12px 28px; border-radius: 8px; color: #fff; text-decoration: none; display: inline-block;">
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

<?php get_footer(); ?>
