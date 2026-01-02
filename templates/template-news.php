<?php
/**
 * Template Name: 新闻中心
 *
 * @package Developer_Starter
 */

get_header();

// 获取设置
$category = developer_starter_get_option( 'news_category', '' );
$per_page = developer_starter_get_option( 'news_per_page', 10 );
$thumb_height = developer_starter_get_option( 'news_thumb_height', 150 );
$show_title = developer_starter_get_option( 'news_show_title', '1' );
$show_date = developer_starter_get_option( 'news_show_date', '1' );
$show_excerpt = developer_starter_get_option( 'news_show_excerpt', '1' );
$show_thumb = developer_starter_get_option( 'news_show_thumb', '1' );

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

// 查询参数
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => intval( $per_page ),
    'paged'          => $paged,
);
if ( $category ) {
    $args['category_name'] = $category;
}

$news_query = new WP_Query( $args );
?>

<div class="page-header" style="background: linear-gradient(135deg, var(--color-primary) 0%, #7c3aed 100%); padding: 100px 0 60px;">
    <div class="container">
        <h1 class="page-title" style="color: #fff; text-align: center; font-size: 2.5rem; margin: 0;">
            <?php the_title(); ?>
        </h1>
        <p style="text-align: center; color: rgba(255,255,255,0.8); margin-top: 15px; font-size: 1.1rem;">
            了解最新公司动态和行业资讯
        </p>
    </div>
</div>

<div class="page-content section-padding">
    <div class="container">
        
        <?php if ( $news_query->have_posts() ) : ?>
            
            <div class="news-list" style="max-width: 900px; margin: 0 auto;">
                
                <?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
                    <article class="news-item" style="display: flex; gap: 30px; padding: 30px 0; border-bottom: 1px solid #e2e8f0; align-items: flex-start;">
                        
                        <?php if ( $show_thumb && has_post_thumbnail() ) : ?>
                            <div class="news-image" style="flex-shrink: 0; width: 200px;">
                                <a href="<?php the_permalink(); ?>" style="display: block; border-radius: 12px; overflow: hidden;">
                                    <?php the_post_thumbnail( 'medium', array( 'style' => 'width: 100%; height: ' . intval( $thumb_height ) . 'px; object-fit: cover;' ) ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="news-content" style="flex: 1;">
                            <?php if ( $show_date ) : ?>
                                <div class="news-meta" style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 8px;">
                                    <span><?php echo get_the_date(); ?></span>
                                    <?php 
                                    $categories = get_the_category();
                                    if ( $categories ) : ?>
                                        <span style="margin: 0 8px;">·</span>
                                        <span><?php echo esc_html( $categories[0]->name ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ( $show_title ) : ?>
                                <h3 style="font-size: 1.25rem; margin-bottom: 10px; line-height: 1.4;">
                                    <a href="<?php the_permalink(); ?>" style="color: #1e293b;"><?php the_title(); ?></a>
                                </h3>
                            <?php endif; ?>
                            
                            <?php if ( $show_excerpt ) : ?>
                                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.7; margin-bottom: 15px;">
                                    <?php echo wp_trim_words( get_the_excerpt(), 40 ); ?>
                                </p>
                            <?php endif; ?>
                            
                            <a href="<?php the_permalink(); ?>" style="color: var(--color-primary); font-weight: 500; font-size: 0.9rem;">
                                阅读全文 →
                            </a>
                        </div>
                        
                    </article>
                <?php endwhile; ?>
                
            </div>
            
            <nav class="ds-pagination" style="margin-top: 50px;">
                <?php
                echo paginate_links( array(
                    'total'     => $news_query->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg> 上一页',
                    'next_text' => '下一页 <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                ) );
                ?>
            </nav>
            
            <?php wp_reset_postdata(); ?>
            
        <?php else : ?>
            
            <div style="text-align: center; padding: 80px 20px;">
                <div style="font-size: 4rem; margin-bottom: 20px;">📰</div>
                <h2 style="color: #64748b; font-weight: 400;">暂无新闻</h2>
                <p style="color: #94a3b8;">请先在后台添加新闻内容<?php echo $category ? '（分类：' . esc_html( $category ) . '）' : ''; ?></p>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
