<?php
/**
 * Template Name: 科技公司官网（一体式）
 * Template Post Type: page
 *
 * 科技公司一体式官网预设模板。
 *
 * @package Developer_Starter
 * @since 2.5.17
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
$wrapper_classes = function_exists( 'developer_starter_get_page_visual_skin_wrapper_classes' )
    ? developer_starter_get_page_visual_skin_wrapper_classes(
        array( 'page-template', 'template-tech-company-integrated' ),
        'templates/template-tech-company-integrated.php'
    )
    : 'page-template template-tech-company-integrated';
?>

<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding qiling-cloud-canvas-empty-state">
            <div class="container text-center">
                <div class="qiling-cloud-canvas-empty-state__icon">TC</div>
                <h2 class="qiling-cloud-canvas-empty-state__title"><?php esc_html_e( '科技公司官网（一体式）', 'developer-starter' ); ?></h2>
                <p class="qiling-cloud-canvas-empty-state__desc">
                    <?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary qiling-cloud-canvas-empty-state__button">
                        <?php esc_html_e( '编辑页面', 'developer-starter' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php
get_footer();
