<?php
/**
 * Template Name: 功能清单
 * Template Post Type: page
 *
 * 功能清单展示页面模板 - 展示主题的功能特性
 * 使用功能清单列表模块为核心，配合其他模块丰富页面内容
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 加载功能清单展示页面专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-features-showcase',
        DEVELOPER_STARTER_ASSETS . '/css/features-showcase.css',
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

<div class="page-template template-features-showcase">
    <?php \Developer_Starter\Core\Page_Header::render( 'default' ); ?>


    <!-- 模块内容区域 -->
    <div id="features-content" class="page-content">
        <?php if ( $has_modules ) : ?>
            <?php developer_starter_render_page_modules(); ?>
        <?php else : ?>
            <!-- 没有模块时显示提示 -->
            <section class="section-padding" style="min-height: 40vh; display: flex; align-items: center; justify-content: center;">
                <div class="container text-center">
                    <h2><?php esc_html_e( '请配置页面模块', 'developer-starter' ); ?></h2>
                    <p style="color: var(--color-text-muted); margin-top: var(--qiling-space-20);">
                        <?php esc_html_e( '在后台编辑此页面，使用「页面模块配置」添加模块来构建页面内容。', 'developer-starter' ); ?>
                    </p>
                    <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                        <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="btn btn-primary" style="margin-top: var(--qiling-space-20);">
                            <?php esc_html_e( '编辑此页面', 'developer-starter' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

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
