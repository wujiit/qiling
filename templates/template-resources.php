<?php
/**
 * Template Name: 资源下载
 * Template Post Type: page
 *
 * 资源下载页面模板 - 支持内置模块布局
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 加载资源下载页面专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-resources',
        DEVELOPER_STARTER_ASSETS . '/css/resources.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );

    wp_enqueue_style(
        'developer-starter-resource-template-skins',
        DEVELOPER_STARTER_ASSETS . '/css/resource-template-skins.css',
        array( 'developer-starter-resources' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();
?>

<div class="page-template template-resources qiling-resource-template qiling-resource-template--downloads">
    <?php \Developer_Starter\Core\Page_Header::render( 'default' ); ?>

    <div class="page-content">
        <?php developer_starter_render_page_modules(); ?>

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
</div>

<?php
get_footer();
