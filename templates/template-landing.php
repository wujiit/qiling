<?php
/**
 * Template Name: 落地页
 * Template Post Type: page
 *
 * 落地页模板 - 用于营销活动、产品推广等专题页面
 * 不显示页头页脚导航，专注于转化
 *
 * @package Developer_Starter
 */

get_header();

// 获取页面模块
$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<div class="page-template template-landing">
    
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php if ( get_the_content() ) : ?>
                <!-- 如果有页面内容，显示内容 -->
                <section class="landing-hero" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-violet-600) 100%); padding: var(--qiling-space-100) 0; text-align: center; color: var(--color-neutral-0);">
                    <div class="container">
                        <h1 style="font-size: var(--qiling-text-rem-3); margin-bottom: var(--qiling-space-20);"><?php echo esc_html( get_the_title() ); ?></h1>
                    </div>
                </section>
                <div class="landing-content section-padding">
                    <div class="container" style="max-width: var(--qiling-measure-900);">
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <!-- 没有内容时的显示 -->
                <section class="landing-hero" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-violet-600) 100%); padding: calc(var(--qiling-space-60) * 2) 0; text-align: center; color: var(--color-neutral-0);">
                    <div class="container">
                        <h1 style="font-size: var(--qiling-text-rem-3); margin-bottom: var(--qiling-space-20);"><?php echo esc_html( get_the_title() ); ?></h1>
                        <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                            <p style="font-size: var(--qiling-text-rem-1p25); opacity: 0.9; max-width: var(--qiling-measure-600); margin: 0 auto var(--qiling-space-40);">
                                <?php esc_html_e( '通过模块构建器为此页面添加内容模块，创建专业的落地页', 'developer-starter' ); ?>
                            </p>
                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>" class="btn btn-light btn-lg">
                                <?php esc_html_e( '编辑页面', 'developer-starter' ); ?>
                            </a>
                        <?php else : ?>
                            <p style="font-size: var(--qiling-text-rem-1p25); opacity: 0.9; max-width: var(--qiling-measure-600); margin: 0 auto;">
                                <?php esc_html_e( '暂无内容。', 'developer-starter' ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; endif; ?>
        
        <!-- 默认CTA（仅在有编辑权限时显示提示） -->
        <?php 
        $phone = developer_starter_get_option( 'company_phone', '' );
        if ( $phone ) : 
        ?>
        <section style="background: linear-gradient(135deg, var(--color-neutral-800) 0%, var(--color-neutral-900) 100%); padding: var(--qiling-space-80) 0; text-align: center; color: var(--color-neutral-0);">
            <div class="container">
                <h2 style="font-size: var(--qiling-text-rem-2); margin-bottom: var(--qiling-space-15);"><?php esc_html_e( '准备开始？', 'developer-starter' ); ?></h2>
                <p style="opacity: 0.8; margin-bottom: var(--qiling-space-30);"><?php esc_html_e( '立即联系我们，获取专业咨询', 'developer-starter' ); ?></p>
                <div style="font-size: var(--qiling-text-rem-1p5); font-weight: 600; margin-bottom: var(--qiling-space-20);">
                    📞 <?php echo esc_html( $phone ); ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

<?php get_footer(); ?>
