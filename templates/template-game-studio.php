<?php
/**
 * Template Name: 独立游戏工作室官网
 * Template Post Type: page
 *
 * 独立游戏工作室与游戏发行官网预设模板
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
    $package_service->apply_package_to_page( get_the_ID(), 'templates/template-game-studio.php' );
    $modules     = function_exists( 'developer_starter_get_page_modules_data' )
        ? developer_starter_get_page_modules_data( get_the_ID() )
        : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
}
$has_modules = ! empty( $modules ) && is_array( $modules );
$wrapper_classes = function_exists( 'developer_starter_get_page_visual_skin_wrapper_classes' )
    ? developer_starter_get_page_visual_skin_wrapper_classes(
        array( 'page-template', 'template-game-studio' ),
        'templates/template-game-studio.php'
    )
    : 'page-template template-game-studio';
?>

<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0b0f19 0%, #f59e0b 100%);">
            <div class="container text-center">
                <div style="font-size: 4rem; margin-bottom: 20px;">🕹️</div>
                <h2 style="color: #fff; font-size: 2rem; margin-bottom: 20px;"><?php esc_html_e( '独立游戏工作室官网', 'developer-starter' ); ?></h2>
                <p style="color: rgba(255,255,255,0.85); margin-bottom: 24px; font-size: 1.05rem;">
                    <?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="background: #ffffff; color: #d97706; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                        <?php esc_html_e( '编辑页面', 'developer-starter' ); ?>
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
