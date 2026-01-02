<?php
/**
 * Template Name: 产品中心
 *
 * @package Developer_Starter
 */

get_header();

// 获取设置
$category = developer_starter_get_option( 'products_category', '' );
$per_page = developer_starter_get_option( 'products_per_page', 12 );
$layout = developer_starter_get_option( 'products_layout', 'grid' );
$columns = developer_starter_get_option( 'products_columns', '3' );
$thumb_height = developer_starter_get_option( 'products_thumb_height', 200 );
$show_title = developer_starter_get_option( 'products_show_title', '1' );
$show_date = developer_starter_get_option( 'products_show_date', '' );
$show_excerpt = developer_starter_get_option( 'products_show_excerpt', '1' );

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

$products_query = new WP_Query( $args );

// 列数样式
$grid_columns = intval( $columns );
$min_width = $grid_columns == 4 ? '250px' : ( $grid_columns == 2 ? '400px' : '300px' );
?>

<div class="page-header" style="background: linear-gradient(135deg, var(--color-primary) 0%, #7c3aed 100%); padding: 100px 0 60px;">
    <div class="container">
        <h1 class="page-title" style="color: #fff; text-align: center; font-size: 2.5rem; margin: 0;">
            <?php the_title(); ?>
        </h1>
        <p style="text-align: center; color: rgba(255,255,255,0.8); margin-top: 15px; font-size: 1.1rem;">
            查看我们的全部产品和服务
        </p>
    </div>
</div>

<div class="page-content section-padding">
    <div class="container">
        
        <?php if ( $products_query->have_posts() ) : ?>
            
            <div class="products-grid products-layout-<?php echo esc_attr( $layout ); ?>" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(<?php echo $min_width; ?>, 1fr)); gap: 30px;">
                
                <?php while ( $products_query->have_posts() ) : $products_query->the_post(); 
                    // 获取缩略图或文章第一张图片
                    $image_url = '';
                    if ( has_post_thumbnail() ) {
                        $image_url = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
                    } elseif ( function_exists( 'developer_starter_get_first_image' ) ) {
                        $image_url = developer_starter_get_first_image( get_the_ID() );
                    }
                ?>
                    <article class="product-card" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                        <?php if ( $image_url ) : ?>
                            <div class="product-image" style="height: <?php echo intval( $thumb_height ); ?>px; overflow: hidden;">
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" />
                                </a>
                            </div>
                        <?php else : ?>
                            <div style="height: <?php echo intval( $thumb_height ); ?>px; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                暂无图片
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info" style="padding: 24px;">
                            <?php if ( $show_title ) : ?>
                                <h3 style="font-size: 1.1rem; margin-bottom: 10px;">
                                    <a href="<?php the_permalink(); ?>" style="color: #1e293b;"><?php the_title(); ?></a>
                                </h3>
                            <?php endif; ?>
                            
                            <?php if ( $show_date ) : ?>
                                <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 8px;"><?php echo get_the_date(); ?></p>
                            <?php endif; ?>
                            
                            <?php if ( $show_excerpt ) : ?>
                                <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin-bottom: 15px;">
                                    <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                                </p>
                            <?php endif; ?>
                            
                            <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline">查看详情</a>
                        </div>
                    </article>
                <?php endwhile; ?>
                
            </div>
            
            <nav class="ds-pagination" style="margin-top: 50px;">
                <?php
                echo paginate_links( array(
                    'total'     => $products_query->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg> 上一页',
                    'next_text' => '下一页 <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                ) );
                ?>
            </nav>
            
            <?php wp_reset_postdata(); ?>
            
        <?php else : ?>
            
            <div style="text-align: center; padding: 80px 20px;">
                <div style="font-size: 4rem; margin-bottom: 20px;">📦</div>
                <h2 style="color: #64748b; font-weight: 400;">暂无产品</h2>
                <p style="color: #94a3b8;">请先在后台添加产品内容<?php echo $category ? '（分类：' . esc_html( $category ) . '）' : ''; ?></p>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
