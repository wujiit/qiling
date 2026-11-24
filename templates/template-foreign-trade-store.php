<?php
/**
 * Template Name: 外贸出海独立商城
 * Template Post Type: page
 *
 * 专为工贸一体、出口制造与跨境独立站打造的外贸官网+商城首页模板
 * 首次保存页面后自动填充全套国际出海与电商模块
 *
 * @package Developer_Starter
 * @since 2.5.18
 */

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<div class="page-template template-foreign-trade-store">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0b1528 0%, #1e293b 100%);">
            <div class="container text-center">
                <div style="margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 18px; background: rgba(37, 99, 235, 0.15); color: #60a5fa; backdrop-filter: blur(8px); border: 1px solid rgba(96, 165, 250, 0.3);">
                    <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </div>
                <h2 style="color: #ffffff; font-size: 2rem; margin-bottom: 20px; font-weight: 800; letter-spacing: -0.02em;">
                    <?php esc_html_e( '外贸出海独立商城', 'qiling' ); ?>
                </h2>
                <p style="color: rgba(255,255,255,0.75); margin-bottom: 24px; font-size: 1.05rem;">
                    <?php esc_html_e( '暂无可展示内容。请在后台为该页面添加商品与出海模块，或从模板中心一键导入预设。', 'qiling' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="background: #2563eb; color: #ffffff; padding: 12px 28px; border-radius: 999px; text-decoration: none; font-weight: 700; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);">
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
