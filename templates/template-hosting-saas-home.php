<?php
/**
 * Template Name: 云主机SaaS官网（一体式）页
 * Template Post Type: page
 *
 * 云主机与托管服务一体式官网预设模板。
 *
 * @package Developer_Starter
 * @since 1.0.8
 */

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
$wrapper_classes = function_exists( 'developer_starter_get_page_visual_skin_wrapper_classes' )
    ? developer_starter_get_page_visual_skin_wrapper_classes(
        array( 'page-template', 'template-hosting-saas-home' ),
        'templates/template-hosting-saas-home.php'
    )
    : 'page-template template-hosting-saas-home';
?>

<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: transparent;">
            <div class="container text-center">
                <div style="font-size: 3rem; margin-bottom: 20px; color: #059669; font-weight: 700; letter-spacing: 0;">HOST</div>
                <h2 style="color: #134e4a; font-size: 2rem; margin-bottom: 20px;"><?php esc_html_e( '云主机SaaS官网（一体式）页', 'developer-starter' ); ?></h2>
                <p style="color: rgba(19,78,74,0.72); margin-bottom: 24px; font-size: 1.05rem;">
                    <?php esc_html_e( '保存页面后会自动填充云主机、托管、CDN、安全、套餐和转化模块，并使用连续画布视觉，可继续替换图片、价格与服务入口。', 'developer-starter' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="background: linear-gradient(135deg, #059669 0%, #2563eb 100%); color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
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
