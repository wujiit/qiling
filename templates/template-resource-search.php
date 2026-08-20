<?php
/**
 * Template Name: 资源搜索
 * Template Post Type: page
 *
 * 资源搜索页面模板
 * 适合素材网站、资源站首页使用
 * 首次创建时会自动填充预设可编辑模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 加载资源搜索页面专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-resource-search',
        DEVELOPER_STARTER_ASSETS . '/css/resource-search.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );

    wp_enqueue_style(
        'developer-starter-resource-template-skins',
        DEVELOPER_STARTER_ASSETS . '/css/resource-template-skins.css',
        array( 'developer-starter-resource-search' ),
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

<div class="page-template template-resource-search qiling-resource-template qiling-resource-template--search">
    
    <!-- 模块内容区域 -->
    <div class="resource-search-content">
        <?php if ( $has_modules ) : ?>
            <?php developer_starter_render_page_modules(); ?>
        <?php else : ?>
            <!-- 没有模块时显示提示 -->
            <section class="section-padding qiling-resource-empty-state">
                <div class="container text-center">
                    <div class="qiling-resource-empty-state__icon">SEARCH</div>
                    <h2 class="qiling-resource-empty-state__title"><?php esc_html_e( '资源搜索页面', 'developer-starter' ); ?></h2>
                    <p class="qiling-resource-empty-state__desc">
                        <?php esc_html_e( '保存页面后将自动填充预设模块，您可以在后台编辑修改内容。', 'developer-starter' ); ?>
                    </p>
                    <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                        <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary btn-lg qiling-resource-empty-state__button">
                            <?php esc_html_e( '编辑此页面', 'developer-starter' ); ?>
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
