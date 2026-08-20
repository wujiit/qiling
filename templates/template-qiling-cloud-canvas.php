<?php
/**
 * Template Name: 一体式官网
 * Template Post Type: page
 *
 * 一体式产品官网预设模板。
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
        array( 'page-template', 'template-qiling-cloud-canvas' ),
        'templates/template-qiling-cloud-canvas.php'
    )
    : 'page-template template-qiling-cloud-canvas';
?>

<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding qiling-cloud-canvas-empty-state">
            <div class="container text-center">
                <div class="qiling-cloud-canvas-empty-state__icon">CC</div>
                <h2 class="qiling-cloud-canvas-empty-state__title"><?php esc_html_e( '一体式官网', 'developer-starter' ); ?></h2>
                <p class="qiling-cloud-canvas-empty-state__desc">
                    <?php esc_html_e( '保存页面后会自动填充一体式官网模块，可继续编辑首屏、能力、流程、价格和转化入口。', 'developer-starter' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary qiling-cloud-canvas-empty-state__button">
                        <?php esc_html_e( '编辑此页面', 'developer-starter' ); ?>
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
