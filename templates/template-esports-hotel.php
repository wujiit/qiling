<?php
/**
 * Template Name: 电竞酒店与场馆官网
 * Template Post Type: page
 *
 * 电竞酒店与高端场馆官网预设模板
 * 首次保存页面后自动填充可编辑模块
 *
 * @package Developer_Starter
 * @since 1.0.8
 */

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
if ( ( empty( $modules ) || ! is_array( $modules ) ) && class_exists( '\Developer_Starter\Core\Official_Template_Package_Service' ) ) {
    $package_service = new \Developer_Starter\Core\Official_Template_Package_Service();
    $package_service->apply_package_to_page( get_the_ID(), 'templates/template-esports-hotel.php' );
    $modules     = function_exists( 'developer_starter_get_page_modules_data' )
        ? developer_starter_get_page_modules_data( get_the_ID() )
        : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
}
$has_modules = ! empty( $modules ) && is_array( $modules );
$wrapper_classes = function_exists( 'developer_starter_get_page_visual_skin_wrapper_classes' )
    ? developer_starter_get_page_visual_skin_wrapper_classes(
        array( 'page-template', 'template-esports-hotel' ),
        'templates/template-esports-hotel.php'
    )
    : 'page-template template-esports-hotel';
?>

<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #090e17 0%, #0284c7 100%);">
            <div class="container text-center">
                <div style="margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 18px; background: rgba(255, 255, 255, 0.12); color: #ffffff; backdrop-filter: blur(8px);"><svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"></rect><path d="M6 12h4m-2-2v4m10-2h.01m-3 0h.01"></path></svg></div>
                <h2 style="color: #fff; font-size: 2rem; margin-bottom: 20px;"><?php esc_html_e( '电竞酒店与场馆官网', 'qiling' ); ?></h2>
                <p style="color: rgba(255,255,255,0.85); margin-bottom: 24px; font-size: 1.05rem;">
                    <?php esc_html_e( '暂无可展示内容。', 'qiling' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="background: #ffffff; color: #0284c7; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                        <?php esc_html_e( '编辑页面', 'qiling' ); ?>
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
