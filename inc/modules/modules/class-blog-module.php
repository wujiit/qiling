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

    public function get_fields() {
        $visual_preset_choices = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
            ? \Developer_Starter\Core\Blog_Visual_Manager::get_module_preset_choices()
            : array(
                'inherit'   => __( '继承页面/全局', 'developer-starter' ),
                'default'   => __( '默认企业内容', 'developer-starter' ),
                'developer' => __( '技术开发者', 'developer-starter' ),
                'minimal'   => __( '极简', 'developer-starter' ),
                'artist'    => __( '艺术家', 'developer-starter' ),
            );

        return array(
            array( 'id' => 'blog_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
            array( 'id' => 'blog_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array( 'id' => 'blog_bg_color', 'type' => 'color', 'label' => __( '背景颜色', 'developer-starter' ) ),
            array( 'id' => 'blog_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            array( 'id' => 'blog_badge_bg', 'type' => 'color', 'label' => __( '标签/徽章背景颜色', 'developer-starter' ), 'desc' => __( '控制分类、文章标签和资源徽章，留空时跟随页面预设/全局徽章颜色。', 'developer-starter' ) ),
            array( 'id' => 'blog_visual_preset', 'type' => 'select', 'label' => __( '博客视觉风格', 'developer-starter' ), 'options' => $visual_preset_choices, 'default' => 'inherit' ),
            array( 'id' => 'blog_page_layout', 'type' => 'select', 'label' => __( '页面布局', 'developer-starter' ), 'options' => array(
                'full' => __( '全宽', 'developer-starter' ),
                'sidebar-left' => __( '左侧边栏', 'developer-starter' ),
                'sidebar-right' => __( '右侧边栏', 'developer-starter' ),
            ), 'default' => 'full' ),
            array( 'id' => 'blog_sidebar_source', 'type' => 'select', 'label' => __( '侧边栏内容来源', 'developer-starter' ), 'options' => array(
                'widget' => __( 'WordPress 小工具侧边栏', 'developer-starter' ),
                'module' => __( '模块内置侧边栏', 'developer-starter' ),
            ), 'default' => 'widget', 'dependency' => array( 'blog_page_layout', '!=', 'full' ) ),
            array(
                'id'      => 'padding_top',
                'type'    => 'select',
                'label'   => __( '顶部间距', 'developer-starter' ),
                'options' => array(
                    'default' => __( '默认 (80px)', 'developer-starter' ),
                    'none'    => __( '无 (0px)', 'developer-starter' ),
                    'small'   => __( '小 (40px)', 'developer-starter' ),
                    'large'   => __( '大 (120px)', 'developer-starter' ),
                ),
                'default' => 'default',
            ),
            array(
                'id'      => 'padding_bottom',
                'type'    => 'select',
                'label'   => __( '底部间距', 'developer-starter' ),
                'options' => array(
                    'default' => __( '默认 (80px)', 'developer-starter' ),
                    'none'    => __( '无 (0px)', 'developer-starter' ),
                    'small'   => __( '小 (40px)', 'developer-starter' ),
                    'large'   => __( '大 (120px)', 'developer-starter' ),
                ),
                'default' => 'default',
            ),
            array( 'id' => 'blog_layout_style', 'type' => 'select', 'label' => __( '列表样式', 'developer-starter' ), 'options' => array(
                'card' => __( '卡片', 'developer-starter' ),
                'grid' => __( '网格', 'developer-starter' ),
                'list' => __( '列表', 'developer-starter' ),
                'large' => __( '大图', 'developer-starter' ),
                'magazine' => __( '杂志流（头条+分栏）', 'developer-starter' ),
            ), 'default' => 'card' ),
            array( 'id' => 'blog_resource_skin', 'type' => 'select', 'label' => __( '资源卡风格', 'developer-starter' ), 'options' => array(
                'default' => __( '默认', 'developer-starter' ),
                'resource_soft' => __( '资源风格（清爽）', 'developer-starter' ),
                'resource_pro' => __( '资源风格（强化）', 'developer-starter' ),
            ), 'default' => 'default' ),
            array( 'id' => 'blog_resource_show_price', 'type' => 'select', 'label' => __( '资源卡显示价格', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_resource_show_updated', 'type' => 'select', 'label' => __( '资源卡显示更新时间', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_resource_show_download_count', 'type' => 'select', 'label' => __( '资源卡显示下载项数量', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_columns', 'type' => 'select', 'label' => __( '列数', 'developer-starter' ), 'options' => array(
                '2' => __( '2列', 'developer-starter' ),
                '3' => __( '3列', 'developer-starter' ),
                '4' => __( '4列', 'developer-starter' ),
            ), 'default' => '3' ),
            array( 'id' => 'blog_data_source', 'type' => 'select', 'label' => __( '数据来源', 'developer-starter' ), 'options' => array(
                'latest' => __( '最新文章', 'developer-starter' ),
                'category' => __( '指定分类', 'developer-starter' ),
                'tag' => __( '指定标签', 'developer-starter' ),
            ), 'default' => 'latest' ),
            array( 'id' => 'blog_categories', 'type' => 'text', 'label' => __( '分类ID (逗号分隔)', 'developer-starter' ), 'dependency' => array( 'blog_data_source', '==', 'category' ) ),
            array( 'id' => 'blog_tags', 'type' => 'text', 'label' => __( '标签 (逗号分隔)', 'developer-starter' ), 'dependency' => array( 'blog_data_source', '==', 'tag' ) ),
            array( 'id' => 'blog_count', 'type' => 'number', 'label' => __( '文章数量', 'developer-starter' ), 'default' => '6' ),
            array( 'id' => 'blog_orderby', 'type' => 'select', 'label' => __( '排序方式', 'developer-starter' ), 'options' => array(
                'date' => __( '日期', 'developer-starter' ),
                'modified' => __( '更新时间', 'developer-starter' ),
                'random' => __( '随机', 'developer-starter' ),
                'comment_count' => __( '评论数', 'developer-starter' ),
                'views' => __( '浏览量', 'developer-starter' ),
            ), 'default' => 'date' ),
            array( 'id' => 'blog_exclude_ids', 'type' => 'text', 'label' => __( '排除文章ID (逗号分隔)', 'developer-starter' ) ),
            array( 'id' => 'blog_ignore_sticky', 'type' => 'select', 'label' => __( '忽略置顶文章权重', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_show_image', 'type' => 'select', 'label' => __( '显示缩略图', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_image_height', 'type' => 'text', 'label' => __( '缩略图高度', 'developer-starter' ), 'default' => '200px' ),
            array( 'id' => 'blog_show_excerpt', 'type' => 'select', 'label' => __( '显示摘要', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_excerpt_length', 'type' => 'number', 'label' => __( '摘要长度', 'developer-starter' ), 'default' => '80' ),
            array( 'id' => 'blog_show_author', 'type' => 'select', 'label' => __( '显示作者', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'blog_show_date', 'type' => 'select', 'label' => __( '显示日期', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_show_category', 'type' => 'select', 'label' => __( '显示分类', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'blog_show_tags', 'type' => 'select', 'label' => __( '显示标签', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'blog_show_views', 'type' => 'select', 'label' => __( '显示浏览量', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'blog_show_reading_time', 'type' => 'select', 'label' => __( '显示阅读时长', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'blog_show_comments', 'type' => 'select', 'label' => __( '显示评论数', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'blog_read_more_text', 'type' => 'text', 'label' => __( '阅读更多文案', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '阅读全文', 'Read More' ) : __( '阅读全文', 'developer-starter' ) ),
            array( 'id' => 'blog_read_more_bg_color', 'type' => 'color', 'label' => __( '阅读更多按钮背景颜色', 'developer-starter' ) ),
            array( 'id' => 'blog_read_more_text_color', 'type' => 'color', 'label' => __( '阅读更多按钮文字颜色', 'developer-starter' ) ),
            array( 'id' => 'blog_read_more_hover_bg_color', 'type' => 'color', 'label' => __( '阅读更多按钮悬停背景颜色', 'developer-starter' ) ),
            array( 'id' => 'blog_read_more_hover_text_color', 'type' => 'color', 'label' => __( '阅读更多按钮悬停文字颜色', 'developer-starter' ) ),
            array( 'id' => 'blog_enable_pagination', 'type' => 'select', 'label' => __( '启用分页', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'no' ),
        );
    }

    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        // 基础配置
        $title = isset( $data['blog_title'] ) ? $data['blog_title'] : '';
        $subtitle = isset( $data['blog_subtitle'] ) ? $data['blog_subtitle'] : '';
        $bg_color = isset( $data['blog_bg_color'] ) ? $data['blog_bg_color'] : '';
        $title_color = isset( $data['blog_title_color'] ) ? $data['blog_title_color'] : '';
        $badge_bg = isset( $data['blog_badge_bg'] ) ? $clean_css_value( $data['blog_badge_bg'] ) : '';
        
        // 布局配置
        $page_layout = isset( $data['blog_page_layout'] ) ? $data['blog_page_layout'] : 'full';
        $layout_style = isset( $data['blog_layout_style'] ) ? $data['blog_layout_style'] : 'card';
        $columns = isset( $data['blog_columns'] ) ? $data['blog_columns'] : '3';
        $visual_preset = isset( $data['blog_visual_preset'] ) ? sanitize_key( $data['blog_visual_preset'] ) : 'inherit';
        if ( class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' ) ) {
            $visual_preset = \Developer_Starter\Core\Blog_Visual_Manager::resolve_module_preset( $visual_preset );
        } else {
            $visual_preset = in_array( $visual_preset, array( 'default', 'developer', 'minimal', 'artist' ), true ) ? $visual_preset : 'default';
        }
        $resource_skin = isset( $data['blog_resource_skin'] ) ? sanitize_key( $data['blog_resource_skin'] ) : 'default';
        if ( ! in_array( $resource_skin, array( 'default', 'resource_soft', 'resource_pro' ), true ) ) {
            $resource_skin = 'default';
        }
        $resource_show_price = ! isset( $data['blog_resource_show_price'] ) || $data['blog_resource_show_price'] !== 'no';
        $resource_show_updated = ! isset( $data['blog_resource_show_updated'] ) || $data['blog_resource_show_updated'] !== 'no';
        $resource_show_download_count = ! isset( $data['blog_resource_show_download_count'] ) || $data['blog_resource_show_download_count'] !== 'no';
        
        // 数据来源
        $data_source = isset( $data['blog_data_source'] ) ? $data['blog_data_source'] : 'latest';
        $categories = isset( $data['blog_categories'] ) ? $data['blog_categories'] : '';
        $tags = isset( $data['blog_tags'] ) ? $data['blog_tags'] : '';
        $count = isset( $data['blog_count'] ) && $data['blog_count'] !== '' ? intval( $data['blog_count'] ) : 6;
        $orderby = isset( $data['blog_orderby'] ) ? $data['blog_orderby'] : 'date';
        $exclude_ids = isset( $data['blog_exclude_ids'] ) ? $data['blog_exclude_ids'] : '';
        $ignore_sticky = ! isset( $data['blog_ignore_sticky'] ) || $data['blog_ignore_sticky'] !== 'no';
        
        // 显示控制 - 使用 'yes'/'no' 值判断
        $show_image = ! isset( $data['blog_show_image'] ) || $data['blog_show_image'] !== 'no';
        $image_height = isset( $data['blog_image_height'] ) && $data['blog_image_height'] !== '' ? $data['blog_image_height'] : '200px';
        $show_excerpt = ! isset( $data['blog_show_excerpt'] ) || $data['blog_show_excerpt'] !== 'no';
        $excerpt_length = isset( $data['blog_excerpt_length'] ) && $data['blog_excerpt_length'] !== '' ? intval( $data['blog_excerpt_length'] ) : 80;
        $show_author = isset( $data['blog_show_author'] ) && $data['blog_show_author'] === 'yes';
        $show_date = ! isset( $data['blog_show_date'] ) || $data['blog_show_date'] !== 'no';
        $show_category = isset( $data['blog_show_category'] ) && $data['blog_show_category'] === 'yes';
        $show_tags = isset( $data['blog_show_tags'] ) && $data['blog_show_tags'] === 'yes';
        $show_views = isset( $data['blog_show_views'] ) && $data['blog_show_views'] === 'yes';
        $show_reading_time = isset( $data['blog_show_reading_time'] ) && $data['blog_show_reading_time'] === 'yes';
        $show_comments = isset( $data['blog_show_comments'] ) && $data['blog_show_comments'] === 'yes';
        if ( function_exists( '\developer_starter_comments_feature_enabled' ) && ! \developer_starter_comments_feature_enabled() ) {
            $show_comments = false;
        }
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
        if ( $ignore_sticky ) {
            $args['ignore_sticky_posts'] = true;
        }
        
        // 排序
        switch ( $orderby ) {
            case 'random':
                $args['orderby'] = 'rand';
                break;
            case 'modified':
                $args['orderby'] = 'modified';
                $args['order'] = 'DESC';
                break;
            case 'comment_count':
                $args['orderby'] = 'comment_count';
                break;
            case 'views':
                $args['meta_key'] = 'ds_post_views_count';
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

        if ( ! empty( $exclude_ids ) ) {
            $exclude_id_list = array_map( 'intval', array_filter( explode( ',', $exclude_ids ) ) );
            if ( ! empty( $exclude_id_list ) ) {
                $args['post__not_in'] = $exclude_id_list;
            }
        }
        
        $query = \developer_starter_run_cached_query(
            $args,
            'module_blog',
            array(
                'needs_pagination' => $enable_pagination,
            )
        );
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
        if ( $badge_bg ) {
            $section_style .= '--qiling-component-badge-bg: ' . $badge_bg . ';';
            $section_style .= '--blog-resource-price-bg: ' . $badge_bg . ';';
            $section_style .= '--blog-resource-price-text: var(--qiling-component-badge-text);';
            $section_style .= '--blog-resource-price-border: var(--qiling-color-transparent);';
        }
        $blog_button_style_map = array(
            'blog_read_more_bg_color'         => '--blog-read-more-bg',
            'blog_read_more_text_color'       => '--blog-read-more-text',
            'blog_read_more_hover_bg_color'   => '--blog-read-more-hover-bg',
            'blog_read_more_hover_text_color' => '--blog-read-more-hover-text',
        );
        foreach ( $blog_button_style_map as $field_id => $css_var ) {
            $value = isset( $data[ $field_id ] ) ? $clean_css_value( $data[ $field_id ] ) : '';
            if ( '' !== $value ) {
                $section_style .= $css_var . ':' . $value . ';';
            }
        }
        
        // 间距控制
        $padding_top = isset( $data['padding_top'] ) ? $data['padding_top'] : 'default';
        $padding_bottom = isset( $data['padding_bottom'] ) ? $data['padding_bottom'] : 'default';
        
        $padding_styles = array(
            'none' => '0',
            'small' => '40px',
            'large' => '120px',
            'default' => ''
        );
        
        $pt_style = isset($padding_styles[$padding_top]) ? $padding_styles[$padding_top] : '';
        $pb_style = isset($padding_styles[$padding_bottom]) ? $padding_styles[$padding_bottom] : '';
        
        if ( $pt_style !== '' ) {
            $section_style .= ' padding-top: ' . $pt_style . ';';
        }
        if ( $pb_style !== '' ) {
            $section_style .= ' padding-bottom: ' . $pb_style . ';';
        }
        
        // 布局类名
        $layout_class = 'blog-layout-' . $layout_style;
        if ( $layout_style === 'card' || $layout_style === 'grid' || $layout_style === 'magazine' ) {
            $layout_class .= ' grid-cols-' . $columns;
        }
        $section_classes = array( 'module', 'module-blog', 'section-padding' );
        $section_classes[] = 'blog-preset-' . sanitize_html_class( $visual_preset );
        if ( $resource_skin !== 'default' ) {
            $section_classes[] = 'module-blog-resource';
            $section_classes[] = 'module-blog-resource-' . $resource_skin;
        }
        ?>
        <section class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>" id="<?php echo esc_attr( $module_id ); ?>" <?php echo $section_style ? 'style="' . esc_attr( $section_style ) . '"' : ''; ?>>
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center" style="margin-bottom: var(--qiling-space-40);">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title" <?php echo $title_color ? 'style="color:' . esc_attr( $title_color ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="blog-layout-wrapper <?php echo $show_sidebar ? 'has-sidebar sidebar-' . esc_attr( $sidebar_position ) : 'no-sidebar'; ?>" style="<?php echo $show_sidebar ? 'display: grid; grid-template-columns: ' . ($sidebar_position === 'left' ? '300px 1fr' : '1fr 300px') . '; gap: var(--qiling-space-40);' : ''; ?>">
                    
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
                                        'show_views' => $show_views,
                                        'show_reading_time' => $show_reading_time,
                                        'show_comments' => $show_comments,
                                        'read_more_text' => $read_more_text,
                                        'resource_skin' => $resource_skin,
                                        'resource_show_price' => $resource_show_price,
                                        'resource_show_updated' => $resource_show_updated,
                                        'resource_show_download_count' => $resource_show_download_count,
                                    ) );
                                endwhile; wp_reset_postdata(); ?>
                            </div>
                            
                            <?php if ( $enable_pagination && $query->max_num_pages > 1 ) : ?>
                                <?php $this->render_pagination( $query, $paged ); ?>
                            <?php endif; ?>
                            
                        <?php else : ?>
                            <div class="blog-no-posts" style="text-align: center; padding: var(--qiling-space-60) var(--qiling-space-20); background: var(--color-neutral-50); border-radius: 12px;">
                                <span style="font-size: var(--qiling-text-rem-3); display: block; margin-bottom: var(--qiling-space-16);">📝</span>
                                <p style="color: var(--color-text-muted); font-size: var(--qiling-text-rem-1p1);"><?php esc_html_e( '暂无文章', 'developer-starter' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ( $show_sidebar && $sidebar_position === 'right' ) : ?>
                        <?php $this->render_sidebar( $data, $sidebar_source ); ?>
                    <?php endif; ?>
                    
                </div>
            </div>
        </section>
        

        <?php
    }
    
    /**
     * 获取网格样式
     */
    private function get_grid_style( $layout_style, $columns ) {
        if ( $layout_style === 'card' || $layout_style === 'grid' ) {
            return 'grid-template-columns: repeat(' . intval( $columns ) . ', 1fr);';
        }
        if ( $layout_style === 'magazine' ) {
            return 'grid-template-columns: repeat(12, minmax(0, 1fr));';
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
        $show_views = $options['show_views'];
        $show_reading_time = $options['show_reading_time'];
        $show_comments = $options['show_comments'];
        $read_more_text = $options['read_more_text'];
        $resource_skin = isset( $options['resource_skin'] ) ? sanitize_key( $options['resource_skin'] ) : 'default';
        $resource_show_price = ! empty( $options['resource_show_price'] );
        $resource_show_updated = ! empty( $options['resource_show_updated'] );
        $resource_show_download_count = ! empty( $options['resource_show_download_count'] );
        
        // 获取主题设置
        $theme_options = get_option( 'developer_starter_options', array() );
        $video_cover_enable = ! empty( $theme_options['video_cover_enable'] );
        
        // 检测是否有视频封面
        $video_data = false;
        $has_video_cover = false;
        $has_video = false;
        if ( $show_image && $video_cover_enable && function_exists( 'developer_starter_get_first_video' ) ) {
            $video_data = developer_starter_get_first_video( get_the_ID() );
            // 只有直接视频类型才支持悬停播放（不支持iframe嵌入如B站、优酷等）
            if ( $video_data && $video_data['type'] === 'video' ) {
                $has_video_cover = true;
                $has_video = true;
            } elseif ( $video_data ) {
                // 有视频但是iframe类型，只显示标签
                $has_video = true;
            }
        } elseif ( function_exists( 'developer_starter_get_first_video' ) ) {
            // 即使不启用视频封面，也检测是否有视频用于显示标签
            $video_data = developer_starter_get_first_video( get_the_ID() );
            if ( $video_data ) {
                $has_video = true;
            }
        }
        
        $has_qilingshop_resource = false;
        $resource_points_price = 0.0;
        $resource_download_count = 0;
        $resource_is_free = false;
        $resource_is_vip = false;
        $qilingshop_resource = array();
        if ( function_exists( 'developer_starter_get_qilingshop_resource_snapshot' ) ) {
            $qilingshop_resource = developer_starter_get_qilingshop_resource_snapshot( get_the_ID() );
            if ( ! empty( $qilingshop_resource['has_resource'] ) ) {
                $has_qilingshop_resource = true;
                $resource_points_price = isset( $qilingshop_resource['points_price'] ) ? (float) $qilingshop_resource['points_price'] : 0.0;
                $resource_download_count = isset( $qilingshop_resource['download_count'] ) ? (int) $qilingshop_resource['download_count'] : 0;
                $resource_is_free = ! empty( $qilingshop_resource['is_free'] );
                $resource_is_vip = ! empty( $qilingshop_resource['is_vip'] );
            }
        }
        $resource_price_text = $this->build_qilingshop_price_text(
            $has_qilingshop_resource,
            $resource_is_free,
            $resource_is_vip,
            $resource_points_price
        );
        $cover_badges = function_exists( 'developer_starter_get_post_cover_badges' )
            ? developer_starter_get_post_cover_badges(
                get_the_ID(),
                array(
                    'context'             => 'blog_module',
                    'theme_options'       => $theme_options,
                    'has_video'           => $has_video,
                    'video_data'          => $video_data,
                    'qilingshop_resource' => $qilingshop_resource,
                )
            )
            : array();
        $cover_badge_types = wp_list_pluck( $cover_badges, 'type' );
        $show_qilingshop_free_badge = in_array( 'free', $cover_badge_types, true );
        $show_qilingshop_vip_badge = in_array( 'vip', $cover_badge_types, true );
        $has_cover_badges = ! empty( $cover_badges );
        
        // 获取封面图片（作为视频封面的poster或普通封面）
        $image_url = '';
        if ( $show_image ) {
            // 使用主题的缩略图优化函数
            if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
                $image_url = developer_starter_get_thumbnail_url( get_the_ID(), 'medium' );
            } elseif ( has_post_thumbnail() ) {
                $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
            }
            if ( empty( $image_url ) ) {
                $post_content = get_the_content();
                if ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_content, $matches ) ) {
                    $image_url = $matches[1];
                }
            }
            // 如果有视频的poster，优先使用
            if ( $has_video_cover && ! empty( $video_data['poster'] ) ) {
                $image_url = $video_data['poster'];
            }

            // [New] 启灵播放器封面优先 (即使不是主题识别的视频也可以显示封面)
            if ( class_exists( 'ArtPlayer_Video_Frontend' ) ) {
                $video_meta = \ArtPlayer_Video_Frontend::get_instance()->get_video_meta_public( get_the_ID() );
                if ( $video_meta && ! empty( $video_meta->cover_image ) ) {
                    $image_url = $video_meta->cover_image;
                }
            }
        }
        
        // 设置封面高度 - 列表式固定高度，其他使用配置值
        if ( $layout_style === 'list' ) {
            $thumb_style = 'min-height: 100%;'; // 使用 min-height 配合 flex stretch
        } elseif ( $layout_style === 'large' ) {
            $thumb_style = 'height: 400px;';
        } else {
            $thumb_style = 'height: ' . esc_attr( $image_height ) . ';';
        }
        
        // 文章类名
        $article_class = 'blog-post-item';
        if ( $has_video_cover ) {
            $article_class .= ' has-video-cover';
        }
        if ( $resource_skin !== 'default' && $has_qilingshop_resource ) {
            $article_class .= ' is-resource-card';
            $article_class .= ' ' . sanitize_html_class( 'resource-style-' . $resource_skin );
            if ( $show_qilingshop_free_badge ) {
                $article_class .= ' is-resource-free';
            } elseif ( $show_qilingshop_vip_badge ) {
                $article_class .= ' is-resource-vip';
            } elseif ( $resource_points_price > 0 ) {
                $article_class .= ' is-resource-paid';
            }
        }
        $video_preview_src = ( $has_video_cover && ! empty( $video_data['preview_src'] ) ) ? $video_data['preview_src'] : ( $video_data['url'] ?? '' );
        ?>
        <article class="<?php echo esc_attr( $article_class ); ?>">
            <?php if ( $show_image && ( $image_url || $has_video_cover ) ) : ?>
                <?php if ( $has_video_cover ) : ?>
                    <!-- 视频封面 -->
                    <div class="post-thumbnail post-video-cover" style="<?php echo $thumb_style; ?>">
                        <a href="<?php echo esc_url( get_permalink() ); ?>" class="video-cover-link">
                            <?php if ( $image_url ) : ?>
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="video-poster" />
                            <?php endif; ?>
                        </a>
                        <video 
                            class="video-cover-player" 
                            src="<?php echo esc_url( $video_preview_src ); ?>" 
                            muted 
                            loop 
                            playsinline 
                            preload="<?php echo $image_url ? 'metadata' : 'auto'; ?>"
                            <?php if ( $image_url ) : ?>poster="<?php echo esc_url( $image_url ); ?>"<?php endif; ?>
                        ></video>
                        <div class="video-play-overlay">
                            <svg class="play-icon" width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                        <?php if ( $has_cover_badges && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                            <?php echo developer_starter_get_post_cover_badges_html( $cover_badges, array( 'context' => 'blog_module' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( get_permalink() ); ?>" class="video-cover-overlay-link"></a>
                    </div>
                <?php else : ?>
                    <!-- 普通图片封面 -->
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-thumbnail" style="<?php echo $thumb_style; ?>">
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                        <?php if ( $has_cover_badges && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                            <?php echo developer_starter_get_post_cover_badges_html( $cover_badges, array( 'context' => 'blog_module' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="post-content">
                <?php if ( $show_category ) : 
                    $cats = get_the_category();
                    if ( ! empty( $cats ) ) : ?>
                        <div class="post-categories" style="margin-bottom: var(--qiling-space-10);">
                            <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="post-category"><?php echo esc_html( $cats[0]->name ); ?></a>
                        </div>
                    <?php endif;
                endif; ?>
                
                <h3 class="post-title">
                    <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
                </h3>

                <?php if ( $resource_skin !== 'default' && $has_qilingshop_resource ) : ?>
                    <div class="post-resource-meta">
                        <?php if ( $resource_show_price && $resource_price_text !== '' ) : ?>
                            <span class="resource-chip resource-price"><?php echo esc_html( $resource_price_text ); ?></span>
                        <?php endif; ?>
                        <?php if ( $resource_show_updated ) : ?>
                            <span class="resource-chip resource-updated"><?php echo esc_html( sprintf( __( '更新 %s', 'developer-starter' ), get_the_modified_date( 'Y-m-d' ) ) ); ?></span>
                        <?php endif; ?>
                        <?php if ( $resource_show_download_count && $resource_download_count > 0 ) : ?>
                            <span class="resource-chip resource-downloads">
                                <?php echo esc_html( sprintf( _n( '%d 项资源', '%d 项资源', $resource_download_count, 'developer-starter' ), $resource_download_count ) ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="post-meta">
                    <?php if ( $show_author ) : ?>
                        <span class="meta-author">
                            <?php echo get_avatar( get_the_author_meta( 'ID' ), 20, '', '', array( 'style' => 'border-radius: 50%; margin-right: var(--qiling-space-4);' ) ); ?>
                            <?php the_author(); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $show_date ) : ?>
                        <span class="meta-date">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo get_the_date(); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $show_views ) : ?>
                        <span class="meta-views">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <?php echo esc_html( $this->get_post_views_text( get_the_ID() ) ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $show_reading_time ) : ?>
                        <span class="meta-reading-time">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?php echo esc_html( $this->get_post_reading_time_text( get_the_ID() ) ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $show_comments ) : ?>
                        <span class="meta-comments">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <?php echo esc_html( get_comments_number() ); ?>
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
                                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">#<?php echo esc_html( $tag->name ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif;
                endif; ?>
                
                <?php if ( $read_more_text ) : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-read-more">
                        <?php echo esc_html( $read_more_text ); ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }

    /**
     * 获取浏览量文案
     */
    private function get_post_views_text( $post_id ) {
        $views = 0;
        if ( class_exists( '\Developer_Starter\Core\Post_Enhancer' ) ) {
            $views = \Developer_Starter\Core\Post_Enhancer::get_post_views( $post_id );
        }
        return sprintf( __( '%s 次浏览', 'developer-starter' ), number_format_i18n( (int) $views ) );
    }

    /**
     * 获取预计阅读时长文案
     */
    private function get_post_reading_time_text( $post_id ) {
        $minutes = 1;
        if ( class_exists( '\Developer_Starter\Core\Post_Enhancer' ) ) {
            $minutes = \Developer_Starter\Core\Post_Enhancer::get_reading_time( $post_id );
        }
        return sprintf( __( '%d 分钟阅读', 'developer-starter' ), max( 1, (int) $minutes ) );
    }

    /**
     * 构建资源卡价格文案（展示层专用，不涉及支付流程）。
     *
     * @param bool  $has_resource 是否为资源文章。
     * @param bool  $is_free      是否免费资源。
     * @param bool  $is_vip       是否VIP权益资源。
     * @param float $points_price 积分价格。
     * @return string
     */
    private function build_qilingshop_price_text( $has_resource, $is_free, $is_vip, $points_price ) {
        if ( ! $has_resource ) {
            return '';
        }

        if ( $is_free ) {
            return __( '免费获取', 'developer-starter' );
        }

        if ( $points_price > 0 ) {
            return sprintf( __( '%s 积分', 'developer-starter' ), number_format_i18n( $points_price, 0 ) );
        }

        if ( $is_vip ) {
            return __( 'VIP权益', 'developer-starter' );
        }

        return __( '资源内容', 'developer-starter' );
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
                <div class="sidebar-widget widget-notice" style="text-align: center; padding: var(--qiling-space-30) var(--qiling-space-20); background: var(--color-neutral-50);">
                    <span style="font-size: var(--qiling-text-rem-2); display: block; margin-bottom: var(--qiling-space-10);">📝</span>
                    <p style="color: var(--color-text-muted); font-size: var(--qiling-text-rem-0p9); margin: 0;">
                        <?php esc_html_e( '请在 外观 > 小工具 中配置博客布局侧边栏', 'developer-starter' ); ?>
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
        <nav class="blog-pagination" role="navigation" aria-label="<?php esc_attr_e( '文章分页导航', 'developer-starter' ); ?>">
            <?php if ( $paged > 1 ) : ?>
                <a href="<?php echo esc_url( $get_page_url( $paged - 1 ) ); ?>" class="page-numbers prev">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <?php esc_html_e( '上一页', 'developer-starter' ); ?>
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
                    <?php esc_html_e( '下一页', 'developer-starter' ); ?>
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
