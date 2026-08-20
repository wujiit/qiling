<?php
/**
 * Template Name: 新闻中心
 * Template Post Type: page
 *
 * @package Developer_Starter
 */

get_header();

$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );
?>

<?php if ( $has_modules ) : ?>
    <?php developer_starter_render_page_modules(); ?>
<?php else : ?>
    <?php
    // 获取设置
    $category = developer_starter_get_option( 'news_category', '' );
    $per_page = developer_starter_get_option( 'news_per_page', 10 );
    $thumb_height = developer_starter_get_option( 'news_thumb_height', 150 );
    $hide_title = developer_starter_get_option( 'hide_news_title', '' );
    $hide_date = developer_starter_get_option( 'hide_news_date', '' );
    $hide_excerpt = developer_starter_get_option( 'hide_news_excerpt', '' );
    $hide_thumb = developer_starter_get_option( 'hide_news_thumb', '' );
    $show_title = empty( $hide_title );
    $show_date = empty( $hide_date );
    $show_excerpt = empty( $hide_excerpt );
    $show_thumb = empty( $hide_thumb );

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
    $args = apply_filters( 'developer_starter_template_news_query_args', $args, get_the_ID(), $category, intval( $per_page ), intval( $paged ) );

    $news_query = developer_starter_run_cached_query(
        $args,
        'template_news',
        array(
            'needs_pagination' => true,
        )
    );
    $news_query = apply_filters( 'developer_starter_template_news_query_result', $news_query, $args, get_the_ID() );
    ?>

    <?php
    $hide_header = get_post_meta( get_the_ID(), '_qiling_hide_page_header', true );
    $hide_header = apply_filters( 'developer_starter_template_news_hide_header', $hide_header, get_the_ID() );
    if ( '1' !== (string) $hide_header ) :
        \Developer_Starter\Core\Page_Header::render( 'default' );
    endif;
    ?>

    <div class="page-content section-padding">
        <div class="container">
            
            <?php if ( $news_query->have_posts() ) : ?>
                <?php do_action( 'developer_starter_template_news_before_loop', $news_query, $args, get_the_ID() ); ?>
                
                <div class="news-list" style="max-width: var(--qiling-measure-900); margin: 0 auto;">
                    
                    <?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
                        <article class="news-item" style="display: flex; gap: var(--qiling-space-30); padding: var(--qiling-space-30) 0; border-bottom: 1px solid var(--color-neutral-200); align-items: flex-start;">
                            
                            <?php
                            $thumb_url = '';
                            if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                                $thumb_url = developer_starter_get_featured_image_url( get_the_ID(), 'medium' );
                            } elseif ( has_post_thumbnail() ) {
                                $thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                            }
                            ?>
                            <?php if ( $show_thumb && $thumb_url ) : ?>
                                <div class="news-image" style="flex-shrink: 0; width: 200px;">
                                    <a href="<?php echo esc_url( get_permalink() ); ?>" style="display: block; border-radius: 12px; overflow: hidden;">
                                        <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: <?php echo intval( $thumb_height ); ?>px; object-fit: cover;" />
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="news-content" style="flex: 1;">
                                <?php if ( $show_date ) : ?>
                                    <div class="news-meta" style="font-size: calc(var(--qiling-font-size-base) * 0.85); color: var(--color-neutral-400); margin-bottom: var(--qiling-space-8);">
                                        <span><?php echo get_the_date(); ?></span>
                                        <?php 
                                        $categories = get_the_category();
                                        if ( $categories ) : ?>
                                            <span style="margin: 0 var(--qiling-space-8);">·</span>
                                            <span><?php echo esc_html( $categories[0]->name ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ( $show_title ) : ?>
                                    <h3 style="font-size: calc(var(--qiling-font-size-base) * 1.25); margin-bottom: var(--qiling-space-10); line-height: 1.4;">
                                        <a href="<?php echo esc_url( get_permalink() ); ?>" style="color: var(--color-neutral-800);"><?php echo esc_html( get_the_title() ); ?></a>
                                    </h3>
                                <?php endif; ?>
                                
                                <?php if ( $show_excerpt ) : ?>
                                    <p style="color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.95); line-height: 1.7; margin-bottom: var(--qiling-space-15);">
                                        <?php echo wp_trim_words( get_the_excerpt(), 40 ); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <a href="<?php echo esc_url( get_permalink() ); ?>" style="color: var(--color-primary); font-weight: 500; font-size: calc(var(--qiling-font-size-base) * 0.9);">
                                    <?php esc_html_e( '阅读全文 →', 'developer-starter' ); ?>
                                </a>
                            </div>
                            
                        </article>
                    <?php endwhile; ?>
                    
                </div>
                
                <nav class="pagination-nav" style="margin-top: var(--qiling-space-50); text-align: center;">
                    <nav class="navigation pagination" role="navigation" aria-label="<?php esc_attr_e( '分页', 'developer-starter' ); ?>">
                        <div class="nav-links">
                            <?php
                            echo paginate_links( array(
                                'total'     => $news_query->max_num_pages,
                                'current'   => $paged,
                                'mid_size'  => 2,
                                'prev_text' => '&laquo; ' . __( '上一页', 'developer-starter' ),
                                'next_text' => __( '下一页', 'developer-starter' ) . ' &raquo;',
                            ) );
                            ?>
                        </div>
                    </nav>
                </nav>
                <style>
                .pagination-nav .page-numbers {
                    margin: 0 var(--qiling-space-4); /* Add spacing between buttons */
                    display: inline-flex; /* Use flex for centering */
                    align-items: center;
                    justify-content: center;
                    min-width: var(--qiling-measure-32);
                    height: 32px;
                    padding: 0 var(--qiling-space-6);
                    line-height: 1;
                    text-align: center;
                    border: 1px solid var(--color-neutral-200);
                    border-radius: 6px;
                    color: var(--color-neutral-500);
                    transition: all 0.3s;
                    text-decoration: none;
                }
                .pagination-nav .page-numbers:hover,
                .pagination-nav .page-numbers.current {
                    background: var(--color-primary);
                    border-color: var(--color-primary);
                    color: var(--color-neutral-0);
                    text-decoration: none;
                }
                .pagination-nav .page-numbers.dots {
                    border: none;
                    background: transparent;
                }
                </style>
                
                <?php wp_reset_postdata(); ?>
                <?php do_action( 'developer_starter_template_news_after_loop', $news_query, $args, get_the_ID() ); ?>
                
            <?php else : ?>
                
                <div style="text-align: center; padding: var(--qiling-space-80) var(--qiling-space-20);">
                    <div style="font-size: calc(var(--qiling-font-size-base) * 4); margin-bottom: var(--qiling-space-20);">📰</div>
                    <h2 style="color: var(--color-neutral-500); font-weight: 400;"><?php esc_html_e( '暂无新闻', 'developer-starter' ); ?></h2>
                    <p style="color: var(--color-neutral-400);"><?php esc_html_e( '请先在后台添加新闻内容', 'developer-starter' ); ?><?php echo $category ? sprintf( '（%s%s）', __( '分类：', 'developer-starter' ), esc_html( $category ) ) : ''; ?></p>
                </div>
                
            <?php endif; ?>
            
        </div>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
