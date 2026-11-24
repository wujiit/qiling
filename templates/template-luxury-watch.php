<?php
/**
 * Template Name: 奢华腕表与名表旗舰站
 * Template Post Type: page
 *
 * 专为瑞士制表、机械腕表、典藏名表及高端钟表独立站设计的官方旗舰模板。
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

<div class="page-template template-luxury-watch">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #090d16 0%, #1e1b18 100%);">
            <div class="container text-center">
                <div style="margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 18px; background: rgba(217, 119, 6, 0.15); color: #f59e0b; backdrop-filter: blur(8px); border: 1px solid rgba(217, 119, 6, 0.3);">
                    <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="7"></circle>
                        <polyline points="12 9 12 12 13.5 13.5"></polyline>
                        <path d="M16.51 17.35l-.35 3.83a2 2 0 0 1-2 1.82H9.83a2 2 0 0 1-2-1.82l-.35-3.83m.01-10.7l.35-3.83A2 2 0 0 1 9.83 1h4.35a2 2 0 0 1 2 1.82l.35 3.83"></path>
                    </svg>
                </div>
                <h2 style="color: #fff; font-size: 2rem; margin-bottom: 20px; font-weight: 800; letter-spacing: -0.02em;">
                    <?php esc_html_e( '奢华腕表与名表旗舰站', 'qiling' ); ?>
                </h2>
                <p style="color: rgba(255,255,255,0.7); margin-bottom: 24px; font-size: 1.05rem;">
                    <?php esc_html_e( '暂无可展示内容。请在后台为该页面添加商品展示与工艺模块，或从模板中心一键导入预设。', 'qiling' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="background: #d97706; color: #ffffff; padding: 12px 28px; border-radius: 999px; text-decoration: none; font-weight: 700; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.4);">
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
