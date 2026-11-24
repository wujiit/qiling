<?php
/**
 * Template Name: 大品牌DTC独立商城
 * Template Post Type: page
 *
 * 媲美 Apple / 大疆等国际大牌与外贸独立站的旗舰商城首页模板
 * 首次保存页面后自动填充全套大品牌与电商模块
 *
 * @package Developer_Starter
 * @since 2.2.8
 */

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<div class="page-template template-brand-dtc-store">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0a0a0c 0%, #111827 100%);">
            <div class="container text-center">
                <div style="margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 18px; background: rgba(14, 165, 233, 0.15); color: #38bdf8; backdrop-filter: blur(8px); border: 1px solid rgba(56, 189, 248, 0.3);">
                    <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <h2 style="color: #fff; font-size: 2rem; margin-bottom: 20px; font-weight: 800; letter-spacing: -0.02em;">
                    <?php esc_html_e( '大品牌DTC独立商城', 'qiling' ); ?>
                </h2>
                <p style="color: rgba(255,255,255,0.7); margin-bottom: 24px; font-size: 1.05rem;">
                    <?php esc_html_e( '暂无可展示内容。请在后台为该页面添加商品与品牌模块，或从模板中心一键导入预设。', 'qiling' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="background: #0ea5e9; color: #ffffff; padding: 12px 28px; border-radius: 999px; text-decoration: none; font-weight: 700; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4);">
                        <?php esc_html_e( '编辑页面 / 装修模块', 'qiling' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <?php if ( get_the_content() ) : ?>
            <section class="content-section section-padding">
                <div class="container">
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endwhile; endif; ?>
</div>

<?php
get_footer();
