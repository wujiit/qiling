<?php
/**
 * Template Name: 潮流球鞋与机能跑鞋站
 * Template Post Type: page
 *
 * 专为运动鞋、竞速跑鞋、机能潮鞋及潮流鞋靴品牌独立站设计的官方旗舰模板。
 *
 * @package Developer_Starter
 * @since 2.3.0
 */

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<div class="page-template template-sneakers-footwear">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #090e17 0%, #0d1b2a 100%);">
            <div class="container text-center">
                <div style="margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 18px; background: rgba(2, 132, 199, 0.15); color: #38bdf8; backdrop-filter: blur(8px); border: 1px solid rgba(2, 132, 199, 0.3);">
                    <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 17l6-6 4 4 6-6"></path>
                        <path d="M14 5h6v6"></path>
                    </svg>
                </div>
                <h2 style="color: #fff; font-size: 2rem; margin-bottom: 20px; font-weight: 800; letter-spacing: -0.02em;">
                    <?php esc_html_e( '潮流球鞋与机能跑鞋站', 'qiling' ); ?>
                </h2>
                <p style="color: rgba(255,255,255,0.7); margin-bottom: 24px; font-size: 1.05rem;">
                    <?php esc_html_e( '暂无可展示内容。请在后台为该页面添加商品展示与科技解构模块，或从模板中心一键导入预设。', 'qiling' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="background: #0284c7; color: #ffffff; padding: 12px 28px; border-radius: 999px; text-decoration: none; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);">
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
