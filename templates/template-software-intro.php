<?php
/**
 * Template Name: 软件介绍
 * Template Post Type: page
 *
 * 软件/主题/插件介绍页面模板
 * 适合作为产品官网首页使用，展示产品特性、功能清单等
 * 首次创建时会自动填充预设可编辑模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 加载软件介绍页面专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-software-intro',
        DEVELOPER_STARTER_ASSETS . '/css/software-intro.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );

    wp_enqueue_style(
        'developer-starter-product-template-skins',
        DEVELOPER_STARTER_ASSETS . '/css/product-template-skins.css',
        array( 'developer-starter-software-intro' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();

// 获取页面模块
$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<div class="page-template template-software-intro qiling-product-template qiling-product-template--software-intro">
    
    <!-- 模块内容区域 -->
    <div id="software-features" class="software-content">
        <?php if ( $has_modules ) : ?>
            <?php developer_starter_render_page_modules(); ?>
        <?php else : ?>
            <!-- 没有模块时显示提示 -->
            <section class="section-padding qiling-resource-empty-state">
                <div class="container text-center">
                    <div class="qiling-resource-empty-state__icon">APP</div>
                    <h2 class="qiling-resource-empty-state__title"><?php esc_html_e( '软件介绍页面', 'developer-starter' ); ?></h2>
                    <p class="qiling-resource-empty-state__desc">
                        <?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?>
                    </p>
                    <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                        <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary btn-lg qiling-resource-empty-state__button">
                            <?php esc_html_e( '编辑页面', 'developer-starter' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- 页面内容（如果有） -->
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
