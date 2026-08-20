<?php
/**
 * The main template file
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

get_header();

$loop_settings = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
    ? \Developer_Starter\Core\Blog_Visual_Manager::get_native_loop_settings()
    : array(
        'grid_classes'      => 'news-grid grid-cols-3 qiling-native-blog-grid qiling-native-blog-grid-default',
        'card_classes'      => 'news-card qiling-native-blog-card qiling-native-blog-card-default',
        'show_thumb'        => true,
        'show_excerpt'      => true,
        'show_date'         => true,
        'show_author'       => true,
        'show_category'     => true,
        'show_reading_time' => false,
        'show_views'        => function_exists( 'developer_starter_get_option' ) ? (bool) developer_starter_get_option( 'post_views_enable', '' ) : false,
        'excerpt_length'    => 25,
        'thumb_height'      => 220,
        'thumb_fit'         => function_exists( 'developer_starter_get_thumbnail_display_mode' ) ? developer_starter_get_thumbnail_display_mode() : 'cover',
    );
?>

<div class="content-area">
    <div class="container">
        <div class="content-wrapper <?php echo apply_filters( 'qiling_show_sidebar', is_active_sidebar( 'sidebar-main' ), 'index' ) ? 'has-sidebar' : ''; ?>">
            <div class="main-content">
                <?php if ( have_posts() ) : ?>
                    
                    <?php if ( is_home() && ! is_front_page() ) : ?>
                        <header class="page-header">
                            <h1 class="page-title"><?php single_post_title(); ?></h1>
                        </header>
                    <?php endif; ?>

                    <?php get_template_part( 'template-parts/blog/post-loop', null, array( 'settings' => $loop_settings ) ); ?>
                    <?php get_template_part( 'template-parts/blog/pagination' ); ?>

                <?php else : ?>
                    <?php get_template_part( 'template-parts/content/content', 'none' ); ?>
                <?php endif; ?>
            </div>

            <?php if ( apply_filters( 'qiling_show_sidebar', is_active_sidebar( 'sidebar-main' ), 'index' ) ) : ?>
                <aside id="secondary" class="widget-area">
                    <?php dynamic_sidebar( 'sidebar-main' ); ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();
