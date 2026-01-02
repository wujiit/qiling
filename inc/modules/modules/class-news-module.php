<?php
/**
 * News Module - 新闻动态模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class News_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-admin-post';
        $this->description = __( '企业动态新闻展示', 'developer-starter' );
    }

    public function get_id() {
        return 'news';
    }

    public function get_name() {
        return __( '新闻列表', 'developer-starter' );
    }

    public function render( $data = array() ) {
        $title = isset( $data['news_title'] ) && $data['news_title'] !== '' ? $data['news_title'] : __( '新闻动态', 'developer-starter' );
        $count = isset( $data['news_count'] ) && $data['news_count'] !== '' ? intval( $data['news_count'] ) : 6;
        $columns = isset( $data['news_columns'] ) && $data['news_columns'] !== '' ? $data['news_columns'] : '3';
        $categories = isset( $data['news_categories'] ) ? $data['news_categories'] : '';
        
        // 显示开关 - 默认显示图片，只有明确设置为0时才隐藏
        $show_image = ! isset( $data['news_show_image'] ) || $data['news_show_image'] !== '0';
        $image_height = isset( $data['news_image_height'] ) && $data['news_image_height'] !== '' ? $data['news_image_height'] : '200px';
        $show_excerpt = ! isset( $data['news_show_excerpt'] ) || $data['news_show_excerpt'] !== '0';
        
        // 解析分类（支持多个，逗号分隔）
        $cat_ids = array();
        if ( ! empty( $categories ) ) {
            $cat_ids = array_map( 'intval', array_filter( explode( ',', $categories ) ) );
        }
        
        // 获取分类信息用于前台切换
        $category_list = array();
        if ( ! empty( $cat_ids ) ) {
            foreach ( $cat_ids as $cat_id ) {
                $cat = get_category( $cat_id );
                if ( $cat && ! is_wp_error( $cat ) ) {
                    $category_list[] = array(
                        'id'   => $cat_id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                    );
                }
            }
        }
        
        // 查询参数
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
        );
        
        // 如果有分类，默认显示第一个分类
        if ( ! empty( $cat_ids ) ) {
            $args['cat'] = $cat_ids[0];
        }
        
        $query = new \WP_Query( $args );
        $module_id = 'news-module-' . uniqid();
        ?>
        <section class="module module-news section-padding" id="<?php echo esc_attr( $module_id ); ?>">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                </div>
                
                <?php if ( count( $category_list ) > 1 ) : ?>
                    <div class="category-tabs" style="text-align: center; margin-bottom: 30px;">
                        <?php foreach ( $category_list as $index => $cat ) : ?>
                            <button type="button" 
                                    class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                    data-category="<?php echo esc_attr( $cat['id'] ); ?>"
                                    data-module="<?php echo esc_attr( $module_id ); ?>"
                                    style="padding: 8px 20px; margin: 5px; border: 1px solid var(--color-primary); background: <?php echo $index === 0 ? 'var(--color-primary)' : 'transparent'; ?>; color: <?php echo $index === 0 ? '#fff' : 'var(--color-primary)'; ?>; border-radius: 20px; cursor: pointer;">
                                <?php echo esc_html( $cat['name'] ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ( $query->have_posts() ) : ?>
                    <div class="news-grid grid-cols-<?php echo esc_attr( $columns ); ?>">
                        <?php while ( $query->have_posts() ) : $query->the_post(); 
                            // 获取封面图片 - 优先特色图片，其次文章第一张图片
                            $image_url = '';
                            if ( has_post_thumbnail() ) {
                                $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
                            }
                            if ( empty( $image_url ) ) {
                                // 从文章内容中获取第一张图片
                                $post_content = get_the_content();
                                if ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_content, $matches ) ) {
                                    $image_url = $matches[1];
                                }
                            }
                            if ( empty( $image_url ) && function_exists( 'developer_starter_get_first_image' ) ) {
                                $image_url = developer_starter_get_first_image( get_the_ID() );
                            }
                        ?>
                            <article class="news-card" style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                <?php if ( $show_image ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="news-thumb" style="display: block; height: <?php echo esc_attr( $image_height ); ?>; overflow: hidden;">
                                        <?php if ( $image_url ) : ?>
                                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
                                        <?php else : ?>
                                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%); display: flex; align-items: center; justify-content: center; color: #999;">
                                                <span class="dashicons dashicons-format-image" style="font-size: 3rem;"></span>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="news-content" style="padding: 15px;">
                                    <span class="news-date" style="color: #999; font-size: 0.85rem;"><?php echo get_the_date(); ?></span>
                                    
                                    <h3 class="news-title" style="margin: 8px 0; font-size: 1rem;">
                                        <a href="<?php the_permalink(); ?>" style="color: #333; text-decoration: none;"><?php the_title(); ?></a>
                                    </h3>
                                    
                                    <?php if ( $show_excerpt ) : ?>
                                        <p class="news-excerpt" style="margin: 0; color: #666; font-size: 0.9rem; line-height: 1.5;"><?php echo wp_trim_words( get_the_excerpt(), 30 ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                    
                    <div class="text-center mt-lg" style="margin-top: 30px;">
                        <?php
                        // 确定"查看更多"链接
                        $more_link = '';
                        
                        // 如果设置了分类，链接到第一个分类
                        if ( ! empty( $cat_ids ) ) {
                            $more_link = get_category_link( $cat_ids[0] );
                        }
                        
                        // 如果没有分类，尝试获取博客页面
                        if ( empty( $more_link ) ) {
                            // 1. 先查找使用博客模板的页面
                            $blog_pages = get_posts( array(
                                'post_type'      => 'page',
                                'posts_per_page' => 1,
                                'meta_key'       => '_wp_page_template',
                                'meta_value'     => 'templates/template-blog.php',
                                'fields'         => 'ids',
                            ) );
                            
                            if ( ! empty( $blog_pages ) ) {
                                $more_link = get_permalink( $blog_pages[0] );
                            } else {
                                // 2. 获取WordPress设置的博客页面（设置 > 阅读 > 文章页）
                                $blog_page_id = get_option( 'page_for_posts' );
                                if ( $blog_page_id ) {
                                    $more_link = get_permalink( $blog_page_id );
                                }
                            }
                        }
                        
                        // 3. 最终fallback：使用WordPress默认的文章归档链接
                        if ( empty( $more_link ) ) {
                            // 如果首页显示最新文章，则链接到首页，否则用默认归档规则
                            $show_on_front = get_option( 'show_on_front' );
                            if ( $show_on_front === 'posts' ) {
                                $more_link = home_url( '/' );
                            } else {
                                // 使用WordPress默认归档链接格式
                                $more_link = home_url( '/page/1/' );
                            }
                        }
                        ?>
                        <a href="<?php echo esc_url( $more_link ); ?>" class="btn btn-outline" style="display: inline-block; padding: 10px 30px; border: 2px solid var(--color-primary); color: var(--color-primary); text-decoration: none; border-radius: 25px;">
                            <?php _e( '查看更多', 'developer-starter' ); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <p class="text-center text-muted"><?php _e( '暂无新闻', 'developer-starter' ); ?></p>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
