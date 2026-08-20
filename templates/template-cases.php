<?php
/**
 * Template Name: 案例展示
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
    $category = developer_starter_get_option( 'cases_category', '' );
    $per_page = developer_starter_get_option( 'cases_per_page', 9 );
    $columns = developer_starter_get_option( 'cases_columns', '3' );
    $thumb_height = developer_starter_get_option( 'cases_thumb_height', 220 );
    $hide_title = developer_starter_get_option( 'hide_cases_title', '' );
    $show_title = empty( $hide_title );

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
    $args = apply_filters( 'developer_starter_template_cases_query_args', $args, get_the_ID(), $category, intval( $per_page ), intval( $paged ) );

    $cases_query = developer_starter_run_cached_query(
        $args,
        'template_cases',
        array(
            'needs_pagination' => true,
        )
    );
    $cases_query = apply_filters( 'developer_starter_template_cases_query_result', $cases_query, $args, get_the_ID() );

    // 列数样式
    $grid_columns = intval( $columns );
    $grid_columns = intval( apply_filters( 'developer_starter_template_cases_columns', $grid_columns, get_the_ID(), $args ) );
    $min_width = $grid_columns == 4 ? '250px' : ( $grid_columns == 2 ? '450px' : '350px' );
    $min_width = (string) apply_filters( 'developer_starter_template_cases_min_width', $min_width, $grid_columns, get_the_ID(), $args );
    ?>
    <?php
    $hide_header = get_post_meta( get_the_ID(), '_qiling_hide_page_header', true );
    $hide_header = apply_filters( 'developer_starter_template_cases_hide_header', $hide_header, get_the_ID() );
    if ( '1' !== (string) $hide_header ) :
        \Developer_Starter\Core\Page_Header::render( 'default' );
    endif;
    ?>

    <div class="page-content section-padding">
        <div class="container">
            
            <?php if ( $cases_query->have_posts() ) : ?>
                <?php do_action( 'developer_starter_template_cases_before_loop', $cases_query, $args, get_the_ID() ); ?>
                
                <div class="cases-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(<?php echo $min_width; ?>, 1fr)); gap: var(--qiling-space-30);">
                    
                    <?php while ( $cases_query->have_posts() ) : $cases_query->the_post(); 
                        // 获取缩略图或文章第一张图片
                        $image_url = '';
                        if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                            $image_url = developer_starter_get_featured_image_url( get_the_ID(), 'large' );
                        } elseif ( has_post_thumbnail() ) {
                            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
                        }
                        if ( empty( $image_url ) && function_exists( 'developer_starter_get_first_image' ) ) {
                            $image_url = developer_starter_get_first_image( get_the_ID() );
                        }
                    ?>
                        <article class="case-card" style="position: relative; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px var(--qiling-color-rgba-0-0-0-01);">
                            
                            <?php if ( $image_url ) : ?>
                                <div class="case-image" style="height: <?php echo intval( $thumb_height ); ?>px; overflow: hidden;">
                                    <a href="<?php echo esc_url( get_permalink() ); ?>">
                                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;" />
                                    </a>
                                </div>
                            <?php else : ?>
                                <div style="height: <?php echo intval( $thumb_height ); ?>px; background: linear-gradient(135deg, var(--color-info) 0%, var(--color-success) 100%);"></div>
                            <?php endif; ?>
                            
                            <div class="case-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; padding: var(--qiling-space-30); background: linear-gradient(to top, var(--qiling-color-rgba-0-0-0-09) 0%, transparent 100%);">
                                <?php if ( $show_title ) : ?>
                                    <h3 style="font-size: var(--qiling-text-rem-1p25); color: var(--color-neutral-0); margin-bottom: var(--qiling-space-10);">
                                        <a href="<?php echo esc_url( get_permalink() ); ?>" style="color: var(--color-neutral-0);"><?php echo esc_html( get_the_title() ); ?></a>
                                    </h3>
                                <?php endif; ?>
                                
                            </div>

                            <?php if ( ! $show_title ) : ?>
                                <a href="<?php echo esc_url( get_permalink() ); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>" style="position: absolute; inset: 0; z-index: 2;"></a>
                            <?php endif; ?>
                            
                        </article>
                    <?php endwhile; ?>
                    
                </div>
                
                <nav class="pagination-nav" style="margin-top: var(--qiling-space-50); text-align: center;">
                    <nav class="navigation pagination" role="navigation" aria-label="<?php esc_attr_e( '分页', 'developer-starter' ); ?>">
                        <div class="nav-links">
                            <?php
                            echo paginate_links( array(
                                'total'     => $cases_query->max_num_pages,
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
                    color: var(--color-text-muted);
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
                <?php do_action( 'developer_starter_template_cases_after_loop', $cases_query, $args, get_the_ID() ); ?>
                
            <?php else : ?>
                
                <div style="text-align: center; padding: var(--qiling-space-80) var(--qiling-space-20);">
                    <div style="font-size: var(--qiling-text-rem-4); margin-bottom: var(--qiling-space-20);">🏆</div>
                    <h2 style="color: var(--color-text-muted); font-weight: 400;"><?php esc_html_e( '暂无案例', 'developer-starter' ); ?></h2>
                    <p style="color: var(--color-neutral-400);">
                        <?php 
                        if ( $category ) {
                            printf( esc_html__( '请先在后台添加案例内容（分类：%s）', 'developer-starter' ), esc_html( $category ) );
                        } else {
                            esc_html_e( '请先在后台添加案例内容', 'developer-starter' ); 
                        }
                        ?>
                    </p>
                </div>
                
            <?php endif; ?>
            
        </div>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
