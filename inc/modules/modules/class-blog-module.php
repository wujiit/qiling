<?php
/**
 * Blog Module - 博客布局模块
 *
 * 支持多种布局样式、数据来源配置的博客展示模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Blog_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-layout';
        $this->description = __( '博客布局展示，支持多种样式和布局', 'developer-starter' );
        // 侧边栏已在 functions.php 中通过 widgets_init 注册
    }

    public function get_id() {
        return 'blog';
    }

    public function get_name() {
        return __( '博客布局', 'developer-starter' );
    }

    public function render( $data = array() ) {
        // 基础配置
        $title = isset( $data['blog_title'] ) ? $data['blog_title'] : '';
        $subtitle = isset( $data['blog_subtitle'] ) ? $data['blog_subtitle'] : '';
        $bg_color = isset( $data['blog_bg_color'] ) ? $data['blog_bg_color'] : '';
        $title_color = isset( $data['blog_title_color'] ) ? $data['blog_title_color'] : '';
        
        // 布局配置
        $page_layout = isset( $data['blog_page_layout'] ) ? $data['blog_page_layout'] : 'full';
        $layout_style = isset( $data['blog_layout_style'] ) ? $data['blog_layout_style'] : 'card';
        $columns = isset( $data['blog_columns'] ) ? $data['blog_columns'] : '3';
        
        // 数据来源
        $data_source = isset( $data['blog_data_source'] ) ? $data['blog_data_source'] : 'latest';
        $categories = isset( $data['blog_categories'] ) ? $data['blog_categories'] : '';
        $tags = isset( $data['blog_tags'] ) ? $data['blog_tags'] : '';
        $count = isset( $data['blog_count'] ) && $data['blog_count'] !== '' ? intval( $data['blog_count'] ) : 6;
        $orderby = isset( $data['blog_orderby'] ) ? $data['blog_orderby'] : 'date';
        
        // 显示控制 - 使用 'yes'/'no' 值判断
        $show_image = ! isset( $data['blog_show_image'] ) || $data['blog_show_image'] !== 'no';
        $image_height = isset( $data['blog_image_height'] ) && $data['blog_image_height'] !== '' ? $data['blog_image_height'] : '200px';
        $show_excerpt = ! isset( $data['blog_show_excerpt'] ) || $data['blog_show_excerpt'] !== 'no';
        $excerpt_length = isset( $data['blog_excerpt_length'] ) && $data['blog_excerpt_length'] !== '' ? intval( $data['blog_excerpt_length'] ) : 80;
        $show_author = isset( $data['blog_show_author'] ) && $data['blog_show_author'] === 'yes';
        $show_date = ! isset( $data['blog_show_date'] ) || $data['blog_show_date'] !== 'no';
        $show_category = isset( $data['blog_show_category'] ) && $data['blog_show_category'] === 'yes';
        $show_tags = isset( $data['blog_show_tags'] ) && $data['blog_show_tags'] === 'yes';
        $read_more_text = isset( $data['blog_read_more_text'] ) && $data['blog_read_more_text'] !== '' ? $data['blog_read_more_text'] : '';
        
        // 分页配置
        $enable_pagination = isset( $data['blog_enable_pagination'] ) && $data['blog_enable_pagination'] === 'yes';
        
        // 侧边栏配置
        $show_sidebar = $page_layout !== 'full';
        $sidebar_position = $page_layout === 'sidebar-left' ? 'left' : 'right';
        $sidebar_source = isset( $data['blog_sidebar_source'] ) ? $data['blog_sidebar_source'] : 'widget';
        
        // 获取当前分页 - 支持静态页面和归档页面
        $paged = 1;
        if ( $enable_pagination ) {
            global $blog_page_paged;
            if ( isset( $blog_page_paged ) && $blog_page_paged > 0 ) {
                $paged = $blog_page_paged;
            } elseif ( get_query_var( 'paged' ) ) {
                $paged = absint( get_query_var( 'paged' ) );
            } elseif ( get_query_var( 'page' ) ) {
                // 静态页面使用 'page' 而不是 'paged'
                $paged = absint( get_query_var( 'page' ) );
            }
        }
        
        // 查询参数
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'paged'          => $paged,
        );
        
        // 排序
        switch ( $orderby ) {
            case 'random':
                $args['orderby'] = 'rand';
                break;
            case 'comment_count':
                $args['orderby'] = 'comment_count';
                break;
            case 'views':
                $args['meta_key'] = 'post_views_count';
                $args['orderby'] = 'meta_value_num';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }
        
        // 数据来源过滤
        if ( $data_source === 'category' && ! empty( $categories ) ) {
            $cat_ids = array_map( 'intval', array_filter( explode( ',', $categories ) ) );
            if ( ! empty( $cat_ids ) ) {
                $args['category__in'] = $cat_ids;
            }
        } elseif ( $data_source === 'tag' && ! empty( $tags ) ) {
            $tag_list = array_map( 'trim', explode( ',', $tags ) );
            if ( ! empty( $tag_list ) ) {
                if ( is_numeric( $tag_list[0] ) ) {
                    $args['tag__in'] = array_map( 'intval', $tag_list );
                } else {
                    $args['tag_slug__in'] = $tag_list;
                }
            }
        }
        
        $query = new \WP_Query( $args );
        $module_id = 'blog-module-' . uniqid();
        
        // 背景样式
        $section_style = '';
        if ( ! empty( $bg_color ) ) {
            if ( strpos( $bg_color, 'gradient' ) !== false ) {
                $section_style = 'background: ' . $bg_color . ';';
            } else {
                $section_style = 'background-color: ' . $bg_color . ';';
            }
        }
        
        // 布局类名
        $layout_class = 'blog-layout-' . $layout_style;
        if ( $layout_style === 'card' || $layout_style === 'grid' ) {
            $layout_class .= ' grid-cols-' . $columns;
        }
        ?>
        <section class="module module-blog section-padding" id="<?php echo esc_attr( $module_id ); ?>" <?php echo $section_style ? 'style="' . esc_attr( $section_style ) . '"' : ''; ?>>
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center" style="margin-bottom: 40px;">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title" <?php echo $title_color ? 'style="color:' . esc_attr( $title_color ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="blog-layout-wrapper <?php echo $show_sidebar ? 'has-sidebar sidebar-' . esc_attr( $sidebar_position ) : 'no-sidebar'; ?>" style="<?php echo $show_sidebar ? 'display: grid; grid-template-columns: ' . ($sidebar_position === 'left' ? '300px 1fr' : '1fr 300px') . '; gap: 40px;' : ''; ?>">
                    
                    <?php if ( $show_sidebar && $sidebar_position === 'left' ) : ?>
                        <?php $this->render_sidebar( $data, $sidebar_source ); ?>
                    <?php endif; ?>
                    
                    <div class="blog-main-content">
                        <?php if ( $query->have_posts() ) : ?>
                            <div class="blog-posts <?php echo esc_attr( $layout_class ); ?>" style="<?php echo $this->get_grid_style( $layout_style, $columns ); ?>">
                                <?php while ( $query->have_posts() ) : $query->the_post();
                                    $this->render_post_item( array(
                                        'layout_style' => $layout_style,
                                        'show_image' => $show_image,
                                        'image_height' => $image_height,
                                        'show_excerpt' => $show_excerpt,
                                        'excerpt_length' => $excerpt_length,
                                        'show_author' => $show_author,
                                        'show_date' => $show_date,
                                        'show_category' => $show_category,
                                        'show_tags' => $show_tags,
                                        'read_more_text' => $read_more_text,
                                    ) );
                                endwhile; wp_reset_postdata(); ?>
                            </div>
                            
                            <?php if ( $enable_pagination && $query->max_num_pages > 1 ) : ?>
                                <?php $this->render_pagination( $query, $paged ); ?>
                            <?php endif; ?>
                            
                        <?php else : ?>
                            <div class="blog-no-posts" style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px;">
                                <span style="font-size: 3rem; display: block; margin-bottom: 16px;">📝</span>
                                <p style="color: #64748b; font-size: 1.1rem;"><?php _e( '暂无文章', 'developer-starter' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ( $show_sidebar && $sidebar_position === 'right' ) : ?>
                        <?php $this->render_sidebar( $data, $sidebar_source ); ?>
                    <?php endif; ?>
                    
                </div>
            </div>
        </section>
        
        <style>
        #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-card,
        #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-grid {
            display: grid;
            gap: 24px;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-large {
            display: flex;
            flex-direction: column;
            gap: 40px;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-post-item {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-post-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        /* 列表式布局 - 缩略图固定在侧边 */
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-list .blog-post-item {
            display: flex;
            flex-direction: row;
            align-items: stretch;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-list .post-thumbnail {
            flex-shrink: 0;
            width: 260px;
            min-height: 180px;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-list .post-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        /* 卡片式 - 强调卡片效果，阴影圆角 */
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-card .blog-post-item {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-card .blog-post-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-card .post-thumbnail {
            border-radius: 16px 16px 0 0;
        }
        /* 网格式 - 简洁紧凑，边框风格 */
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-grid .blog-post-item {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: none;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-grid .blog-post-item:hover {
            transform: none;
            border-color: var(--color-primary, #2563eb);
            box-shadow: 0 0 0 1px var(--color-primary, #2563eb);
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-grid .post-content {
            padding: 16px;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-grid .post-title {
            font-size: 1rem;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-layout-grid .post-excerpt {
            font-size: 0.9rem;
            margin-bottom: 12px;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-thumbnail {
            display: block;
            overflow: hidden;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        #<?php echo esc_attr( $module_id ); ?> .blog-post-item:hover .post-thumbnail img {
            transform: scale(1.05);
        }
        #<?php echo esc_attr( $module_id ); ?> .post-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 10px;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-category {
            background: linear-gradient(135deg, var(--color-primary, #2563eb), #7c3aed);
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-title {
            margin: 0 0 10px;
            font-size: 1.2rem;
            font-weight: 600;
            line-height: 1.4;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-title a {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.2s;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-title a:hover {
            color: var(--color-primary, #2563eb);
        }
        #<?php echo esc_attr( $module_id ); ?> .post-excerpt {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 16px;
            flex: 1;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-tags a {
            background: #f1f5f9;
            color: #475569;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-tags a:hover {
            background: var(--color-primary, #2563eb);
            color: #fff;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-read-more {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--color-primary, #2563eb);
            font-weight: 500;
            text-decoration: none;
            font-size: 0.9rem;
            transition: gap 0.2s;
        }
        #<?php echo esc_attr( $module_id ); ?> .post-read-more:hover {
            gap: 10px;
        }
        /* 侧边栏样式 */
        #<?php echo esc_attr( $module_id ); ?> .blog-sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        #<?php echo esc_attr( $module_id ); ?> .sidebar-widget {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        #<?php echo esc_attr( $module_id ); ?> .widget-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f1f5f9;
            position: relative;
        }
        #<?php echo esc_attr( $module_id ); ?> .widget-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 40px;
            height: 2px;
            background: linear-gradient(135deg, var(--color-primary, #2563eb), #7c3aed);
        }
        /* 响应式 */
        @media (max-width: 992px) {
            #<?php echo esc_attr( $module_id ); ?> .blog-layout-wrapper.has-sidebar {
                grid-template-columns: 1fr !important;
            }
            #<?php echo esc_attr( $module_id ); ?> .blog-sidebar {
                order: 2;
            }
        }
        @media (max-width: 768px) {
            #<?php echo esc_attr( $module_id ); ?> .blog-layout-list .blog-post-item {
                flex-direction: column;
            }
            #<?php echo esc_attr( $module_id ); ?> .blog-layout-list .post-thumbnail {
                width: 100%;
                min-height: 200px;
            }
            #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-grid.grid-cols-3,
            #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-card.grid-cols-3 {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 576px) {
            #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-grid,
            #<?php echo esc_attr( $module_id ); ?> .blog-posts.blog-layout-card {
                grid-template-columns: 1fr !important;
            }
        }
        </style>
        <?php
    }
    
    /**
     * 获取网格样式
     */
    private function get_grid_style( $layout_style, $columns ) {
        if ( $layout_style === 'card' || $layout_style === 'grid' ) {
            return 'grid-template-columns: repeat(' . intval( $columns ) . ', 1fr);';
        }
        return '';
    }
    
    /**
     * 渲染单个文章项
     */
    private function render_post_item( $options ) {
        $layout_style = $options['layout_style'];
        $show_image = $options['show_image'];
        $image_height = $options['image_height'];
        $show_excerpt = $options['show_excerpt'];
        $excerpt_length = $options['excerpt_length'];
        $show_author = $options['show_author'];
        $show_date = $options['show_date'];
        $show_category = $options['show_category'];
        $show_tags = $options['show_tags'];
        $read_more_text = $options['read_more_text'];
        
        // 获取封面图片
        $image_url = '';
        if ( $show_image ) {
            if ( has_post_thumbnail() ) {
                $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
            }
            if ( empty( $image_url ) ) {
                $post_content = get_the_content();
                if ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_content, $matches ) ) {
                    $image_url = $matches[1];
                }
            }
        }
        
        // 设置图片高度 - 列表式固定高度，其他使用配置值
        if ( $layout_style === 'list' ) {
            $thumb_style = 'height: 100%;';
        } elseif ( $layout_style === 'large' ) {
            $thumb_style = 'height: 400px;';
        } else {
            $thumb_style = 'height: ' . esc_attr( $image_height ) . ';';
        }
        ?>
        <article class="blog-post-item">
            <?php if ( $show_image && $image_url ) : ?>
                <a href="<?php the_permalink(); ?>" class="post-thumbnail" style="<?php echo $thumb_style; ?>">
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                </a>
            <?php endif; ?>
            
            <div class="post-content">
                <?php if ( $show_category ) : 
                    $cats = get_the_category();
                    if ( ! empty( $cats ) ) : ?>
                        <div class="post-categories" style="margin-bottom: 10px;">
                            <a href="<?php echo get_category_link( $cats[0]->term_id ); ?>" class="post-category"><?php echo esc_html( $cats[0]->name ); ?></a>
                        </div>
                    <?php endif;
                endif; ?>
                
                <h3 class="post-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                
                <div class="post-meta">
                    <?php if ( $show_author ) : ?>
                        <span class="meta-author">
                            <?php echo get_avatar( get_the_author_meta( 'ID' ), 20, '', '', array( 'style' => 'border-radius: 50%; margin-right: 4px;' ) ); ?>
                            <?php the_author(); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $show_date ) : ?>
                        <span class="meta-date">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo get_the_date(); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ( $show_excerpt ) : ?>
                    <p class="post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), $excerpt_length ); ?></p>
                <?php endif; ?>
                
                <?php if ( $show_tags ) :
                    $post_tags = get_the_tags();
                    if ( ! empty( $post_tags ) ) : ?>
                        <div class="post-tags">
                            <?php foreach ( array_slice( $post_tags, 0, 3 ) as $tag ) : ?>
                                <a href="<?php echo get_tag_link( $tag->term_id ); ?>">#<?php echo esc_html( $tag->name ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif;
                endif; ?>
                
                <?php if ( $read_more_text ) : ?>
                    <a href="<?php the_permalink(); ?>" class="post-read-more">
                        <?php echo esc_html( $read_more_text ); ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
    
    /**
     * 渲染侧边栏
     */
    private function render_sidebar( $data, $sidebar_source = 'widget' ) {
        ?>
        <aside class="blog-sidebar">
            <?php 
            // 使用WordPress小工具侧边栏
            if ( is_active_sidebar( 'blog-module-sidebar' ) ) {
                dynamic_sidebar( 'blog-module-sidebar' );
            } else {
                // 如果没有配置小工具，显示提示
                ?>
                <div class="sidebar-widget widget-notice" style="text-align: center; padding: 30px 20px; background: #f8fafc;">
                    <span style="font-size: 2rem; display: block; margin-bottom: 10px;">📝</span>
                    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
                        <?php _e( '请在 外观 > 小工具 中配置博客布局侧边栏', 'developer-starter' ); ?>
                    </p>
                </div>
                <?php
            }
            ?>
        </aside>
        <?php
    }
    
    /**
     * 渲染分页导航
     *
     * @param WP_Query $query  查询对象
     * @param int      $paged  当前页码
     */
    private function render_pagination( $query, $paged ) {
        $total_pages = $query->max_num_pages;
        
        if ( $total_pages <= 1 ) {
            return;
        }
        
        // 获取当前页面ID用于生成分页URL
        $page_id = get_queried_object_id();
        $page_url = get_permalink( $page_id );
        
        // 生成分页URL的辅助函数
        $get_page_url = function( $page_num ) use ( $page_url ) {
            if ( $page_num <= 1 ) {
                return $page_url;
            }
            // 使用 trailingslashit 确保URL格式正确
            return trailingslashit( $page_url ) . 'page/' . $page_num . '/';
        };
        
        ?>
        <nav class="blog-pagination" role="navigation" aria-label="<?php _e( '文章分页导航', 'developer-starter' ); ?>">
            <?php if ( $paged > 1 ) : ?>
                <a href="<?php echo esc_url( $get_page_url( $paged - 1 ) ); ?>" class="page-numbers prev">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <?php _e( '上一页', 'developer-starter' ); ?>
                </a>
            <?php endif; ?>
            
            <?php
            // 显示页码
            $range = 2; // 当前页两边显示的页码数
            
            if ( $total_pages > 1 ) {
                // 第一页
                if ( $paged > $range + 1 ) {
                    echo '<a href="' . esc_url( $get_page_url( 1 ) ) . '" class="page-numbers">1</a>';
                    if ( $paged > $range + 2 ) {
                        echo '<span class="page-numbers dots">...</span>';
                    }
                }
                
                // 中间页码
                for ( $i = max( 1, $paged - $range ); $i <= min( $total_pages, $paged + $range ); $i++ ) {
                    if ( $i == $paged ) {
                        echo '<span class="page-numbers current" aria-current="page">' . $i . '</span>';
                    } else {
                        echo '<a href="' . esc_url( $get_page_url( $i ) ) . '" class="page-numbers">' . $i . '</a>';
                    }
                }
                
                // 最后一页
                if ( $paged < $total_pages - $range ) {
                    if ( $paged < $total_pages - $range - 1 ) {
                        echo '<span class="page-numbers dots">...</span>';
                    }
                    echo '<a href="' . esc_url( $get_page_url( $total_pages ) ) . '" class="page-numbers">' . $total_pages . '</a>';
                }
            }
            ?>
            
            <?php if ( $paged < $total_pages ) : ?>
                <a href="<?php echo esc_url( $get_page_url( $paged + 1 ) ); ?>" class="page-numbers next">
                    <?php _e( '下一页', 'developer-starter' ); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="pagination-info">
            <?php printf( __( '第 %1$d 页，共 %2$d 页', 'developer-starter' ), $paged, $total_pages ); ?>
        </div>
        <?php
    }
}
