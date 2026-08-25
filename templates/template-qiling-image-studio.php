<?php
/**
 * Template Name: AI 图像处理官网页
 * Template Post Type: page
 *
 * AI 图像处理官网预设模板。
 *
 * @package Developer_Starter
 * @since 1.0.8
 */

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-product-template-skins',
        DEVELOPER_STARTER_ASSETS . '/css/product-template-skins.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();

$modules     = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<div class="page-template template-qiling-image-studio qiling-product-template qiling-product-template--image">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php else : ?>
        <section class="section-padding qiling-resource-empty-state">
            <div class="container text-center">
                <div class="qiling-resource-empty-state__icon">IMG</div>
                <h2 class="qiling-resource-empty-state__title"><?php esc_html_e( 'AI 图像处理官网页', 'developer-starter' ); ?></h2>
                <p class="qiling-resource-empty-state__desc">
                    <?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?>
                </p>
                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary qiling-resource-empty-state__button">
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
