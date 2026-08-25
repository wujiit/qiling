<?php
/**
 * Template Name: 图片素材壁纸站
 * Template Post Type: page
 *
 * 启灵官方图片素材与壁纸图库页面模板。
 *
 * @package Developer_Starter
 * @since 1.0.8
 */

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-resource-template-skins',
        DEVELOPER_STARTER_ASSETS . '/css/resource-template-skins.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );

    wp_enqueue_style(
        'developer-starter-qiling-wallpaper-gallery',
        DEVELOPER_STARTER_ASSETS . '/css/qiling-wallpaper-gallery.css',
        array( 'developer-starter-resource-template-skins' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<div class="page-template template-qiling-wallpaper-gallery qiling-resource-template qiling-resource-template--image">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="qiling-wallpaper-empty-state">
            <div class="container">
                <div class="qiling-wallpaper-empty-state__visual" aria-hidden="true">
                    <span class="qiling-wallpaper-empty-state__tile qiling-wallpaper-empty-state__tile--portrait"></span>
                    <span class="qiling-wallpaper-empty-state__tile qiling-wallpaper-empty-state__tile--landscape"></span>
                    <span class="qiling-wallpaper-empty-state__tile qiling-wallpaper-empty-state__tile--square"></span>
                </div>
                <div class="qiling-wallpaper-empty-state__content">
                    <span class="qiling-wallpaper-empty-state__eyebrow"><?php esc_html_e( '图片素材库', 'developer-starter' ); ?></span>
                    <h1 class="qiling-wallpaper-empty-state__title"><?php esc_html_e( '用清晰分类呈现每一张好图片', 'developer-starter' ); ?></h1>
                    <p class="qiling-wallpaper-empty-state__desc">
                        <?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?>
                    </p>
                    <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                        <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="qiling-wallpaper-empty-state__button">
                            <?php esc_html_e( '编辑页面', 'developer-starter' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
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
