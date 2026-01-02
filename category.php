<?php
/**
 * 分类归档页面模板
 * 
 * 支持自定义布局、背景色、图标等设置
 *
 * @package Developer_Starter
 */

get_header();

// 获取当前分类信息
$category = get_queried_object();
$cat_id = $category->term_id;

// 获取分类设置
$settings = Developer_Starter\Core\Category_Manager::get_category_settings( $cat_id );
$layout = $settings['layout'];
$bg_color = $settings['bg_color'];
$icon = $settings['icon'];
$custom_posts_per_page = $settings['posts_per_page'];

// 背景颜色处理
if ( empty( $bg_color ) ) {
    $primary_color = developer_starter_get_option( 'primary_color', '#2563eb' );
    $bg_style = "background: linear-gradient(135deg, {$primary_color} 0%, " . developer_starter_darken_color( $primary_color, 20 ) . " 100%);";
} else {
    $bg_style = "background: linear-gradient(135deg, {$bg_color} 0%, " . developer_starter_darken_color( $bg_color, 20 ) . " 100%);";
}

// 每页文章数量
if ( ! empty( $custom_posts_per_page ) ) {
    global $wp_query;
    $wp_query->set( 'posts_per_page', $custom_posts_per_page );
}

// 根据布局确定CSS类
$layout_class = 'category-layout-' . esc_attr( $layout );
$grid_class = '';
switch ( $layout ) {
    case 'grid':
        $grid_class = 'grid-cols-4';
        break;
    case 'card':
        $grid_class = 'grid-cols-3';
        break;
    case 'magazine':
        $grid_class = 'magazine-layout';
        break;
    case 'list':
    default:
        $grid_class = 'list-layout';
        break;
}
?>

<!-- 分类页面头部 -->
<div class="category-header" style="<?php echo esc_attr( $bg_style ); ?>">
    <div class="container">
        <!-- 面包屑导航 -->
        <nav class="category-breadcrumb" aria-label="<?php _e( '面包屑导航', 'developer-starter' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( '首页', 'developer-starter' ); ?></a>
            <span class="breadcrumb-separator">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
            <span class="breadcrumb-current">
                <?php if ( ! empty( $icon ) ) : ?>
                    <span class="category-icon">
                        <?php if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) : ?>
                            <img src="<?php echo esc_url( $icon ); ?>" alt="" />
                        <?php else : ?>
                            <?php echo esc_html( $icon ); ?>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                <?php single_cat_title(); ?>
            </span>
        </nav>
        
        <!-- 分类标题 -->
        <h1 class="category-title">
            <?php if ( ! empty( $icon ) ) : ?>
                <span class="category-icon-large">
                    <?php if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) : ?>
                        <img src="<?php echo esc_url( $icon ); ?>" alt="" />
                    <?php else : ?>
                        <?php echo esc_html( $icon ); ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
            <?php single_cat_title(); ?>
        </h1>
        
        <?php if ( category_description() ) : ?>
            <p class="category-description"><?php echo category_description(); ?></p>
        <?php endif; ?>
        
        <!-- 文章统计 -->
        <div class="category-meta">
            <span class="category-count">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <?php printf( _n( '%s 篇文章', '%s 篇文章', $category->count, 'developer-starter' ), $category->count ); ?>
            </span>
        </div>
    </div>
</div>

<!-- 文章列表 -->
<section class="category-content section-padding">
    <div class="container">
        <?php if ( have_posts() ) : ?>
            
            <?php if ( $layout === 'magazine' && have_posts() ) : ?>
                <!-- 杂志布局：首篇大图 -->
                <?php the_post(); ?>
                <article class="magazine-featured" data-aos="fade-up">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>" class="magazine-featured-thumb">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </a>
                    <?php endif; ?>
                    <div class="magazine-featured-content">
                        <span class="post-date"><?php echo get_the_date(); ?></span>
                        <h2 class="post-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <p class="post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 60 ); ?></p>
                        <a href="<?php the_permalink(); ?>" class="read-more-btn">
                            <?php _e( '阅读全文', 'developer-starter' ); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                </article>
            <?php endif; ?>
            
            <div class="posts-<?php echo esc_attr( $layout ); ?> <?php echo esc_attr( $grid_class ); ?>">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php if ( $layout === 'list' ) : ?>
                        <!-- 列表布局 -->
                        <article class="post-item-list" data-aos="fade-up">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>" class="post-thumb">
                                    <?php the_post_thumbnail( 'medium' ); ?>
                                </a>
                            <?php elseif ( function_exists( 'developer_starter_get_first_image' ) && $first_img = developer_starter_get_first_image( get_the_ID() ) ) : ?>
                                <a href="<?php the_permalink(); ?>" class="post-thumb">
                                    <img src="<?php echo esc_url( $first_img ); ?>" alt="<?php the_title_attribute(); ?>" />
                                </a>
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <div class="post-meta">
                                    <span class="post-date"><?php echo get_the_date(); ?></span>
                                    <span class="post-author"><?php the_author(); ?></span>
                                </div>
                                <h2 class="post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <p class="post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 40 ); ?></p>
                                <a href="<?php the_permalink(); ?>" class="read-more">
                                    <?php _e( '阅读更多', 'developer-starter' ); ?> →
                                </a>
                            </div>
                        </article>
                        
                    <?php elseif ( $layout === 'grid' ) : ?>
                        <!-- 网格布局 -->
                        <article class="post-item-grid" data-aos="fade-up">
                            <a href="<?php the_permalink(); ?>" class="post-thumb">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                <?php elseif ( function_exists( 'developer_starter_get_first_image' ) && $first_img = developer_starter_get_first_image( get_the_ID() ) ) : ?>
                                    <img src="<?php echo esc_url( $first_img ); ?>" alt="<?php the_title_attribute(); ?>" />
                                <?php else : ?>
                                    <div class="no-thumb-placeholder"></div>
                                <?php endif; ?>
                            </a>
                            <h3 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <span class="post-date"><?php echo get_the_date(); ?></span>
                        </article>
                        
                    <?php else : ?>
                        <!-- 卡片布局 (默认) / 杂志布局剩余文章 -->
                        <article class="post-item-card" data-aos="fade-up">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>" class="post-thumb">
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                </a>
                            <?php elseif ( function_exists( 'developer_starter_get_first_image' ) && $first_img = developer_starter_get_first_image( get_the_ID() ) ) : ?>
                                <a href="<?php the_permalink(); ?>" class="post-thumb">
                                    <img src="<?php echo esc_url( $first_img ); ?>" alt="<?php the_title_attribute(); ?>" />
                                </a>
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <h2 class="post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <p class="post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 25 ); ?></p>
                            </div>
                        </article>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>
            
            <!-- 分页 -->
            <nav class="ds-pagination" role="navigation" aria-label="<?php _e( '文章分页导航', 'developer-starter' ); ?>">
                <?php
                echo paginate_links( array(
                    'mid_size'  => 2,
                    'prev_text' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg><span>' . __( '上一页', 'developer-starter' ) . '</span>',
                    'next_text' => '<span>' . __( '下一页', 'developer-starter' ) . '</span><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                    'type'      => 'list',
                ) );
                ?>
            </nav>
            
        <?php else : ?>
            <div class="no-posts">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <p><?php _e( '该分类下暂无文章', 'developer-starter' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
