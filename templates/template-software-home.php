<?php
/**
 * Template Name: 软件首页
 * Template Post Type: page
 *
 * 软件下载站首页模板
 * 适合软件下载网站、应用商店首页使用
 * 首次创建时会自动填充预设可编辑模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 加载软件首页专用样式（仅在此页面加载）
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-software-home',
        DEVELOPER_STARTER_ASSETS . '/css/software-home.css',
        array( 'developer-starter-main' ),
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

<div class="page-template template-software-home qiling-product-template qiling-product-template--software">
    
    <!-- 模块内容区域 -->
    <div class="software-home-content">
        <?php if ( $has_modules ) : ?>
            <?php developer_starter_render_page_modules(); ?>
        <?php else : ?>
            <!-- 没有模块时显示提示 -->
            <section class="section-padding software-home-empty-state">
                <div class="container text-center">
                    <div class="software-home-empty-state__icon">📦</div>
                    <h2 class="software-home-empty-state__title"><?php esc_html_e( '软件下载站首页', 'developer-starter' ); ?></h2>
                    <p class="software-home-empty-state__desc">
                        <?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?>
                    </p>
                    <div class="software-home-empty-state__actions">
                        <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                            <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary btn-lg software-home-empty-state__button">
                                <?php esc_html_e( '编辑页面', 'developer-starter' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="software-home-empty-state__hint">
                        <?php esc_html_e( '💡 提示：需要先安装并激活「启灵App」插件才能显示软件数据', 'developer-starter' ); ?>
                    </p>
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
