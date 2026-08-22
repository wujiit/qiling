<?php
/**
 * 博客页面创建器类
 *
 * 当用户选择"博客页面"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 博客页面创建器类
 */
class Blog_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-blog.php';
    protected const AJAX_ACTION = 'fill_blog_page_modules';
    protected const FILLED_META_KEY = '_blog_page_modules_filled';

    /**
     * 获取博客页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_preset = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
            ? \Developer_Starter\Core\Blog_Visual_Manager::get_page_preset( $page_id )
            : '';
        $is_developer_preset = ( 'developer' === $page_preset );
        $is_minimal_preset = ( 'minimal' === $page_preset );
        $is_artist_preset = ( 'artist' === $page_preset );

        $blog_module_data = array(
            'blog_title'          => '',
            'blog_subtitle'       => '',
            'blog_bg_color'       => '',
            'blog_page_layout'    => 'full',
            'blog_visual_preset'  => 'inherit',
            'blog_layout_style'   => 'magazine',
            'blog_columns'        => '3',
            'blog_data_source'    => 'latest',
            'blog_count'          => '12',
            'blog_orderby'        => 'date',
            'blog_show_image'     => 'yes',
            'blog_image_height'   => '200px',
            'blog_show_excerpt'   => 'yes',
            'blog_excerpt_length' => '60',
            'blog_show_author'    => 'no',
            'blog_show_date'      => 'yes',
            'blog_show_category'  => 'yes',
            'blog_show_tags'      => 'no',
            'blog_show_views'     => 'yes',
            'blog_show_reading_time' => 'yes',
            'blog_read_more_text' => __( '阅读全文', 'developer-starter' ),
            'blog_enable_pagination' => 'yes',
        );

        if ( $is_developer_preset ) {
            $blog_module_data['blog_visual_preset'] = 'developer';
            $blog_module_data['blog_layout_style'] = 'list';
            $blog_module_data['blog_image_height'] = '240px';
            $blog_module_data['blog_excerpt_length'] = '36';
        } elseif ( $is_minimal_preset ) {
            $blog_module_data['blog_visual_preset'] = 'minimal';
            $blog_module_data['blog_layout_style'] = 'card';
            $blog_module_data['blog_columns'] = '2';
            $blog_module_data['blog_count'] = '10';
            $blog_module_data['blog_image_height'] = '300px';
            $blog_module_data['blog_excerpt_length'] = '22';
            $blog_module_data['blog_show_category'] = 'no';
            $blog_module_data['blog_show_views'] = 'no';
            $blog_module_data['blog_show_reading_time'] = 'no';
            $blog_module_data['blog_read_more_text'] = __( '继续阅读', 'developer-starter' );
        } elseif ( $is_artist_preset ) {
            $blog_module_data['blog_title'] = __( '作品与札记', 'developer-starter' );
            $blog_module_data['blog_subtitle'] = __( '图像、灵感与创作过程', 'developer-starter' );
            $blog_module_data['blog_visual_preset'] = 'artist';
            $blog_module_data['blog_layout_style'] = 'card';
            $blog_module_data['blog_columns'] = '2';
            $blog_module_data['blog_count'] = '8';
            $blog_module_data['blog_image_height'] = '360px';
            $blog_module_data['blog_excerpt_length'] = '20';
            $blog_module_data['blog_show_views'] = 'no';
            $blog_module_data['blog_show_reading_time'] = 'no';
            $blog_module_data['blog_read_more_text'] = __( '进入作品', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：博客置顶推荐 - 轮播展示精选文章
            array(
                'type' => 'featured_posts',
                'data' => array(
                    'fp_title'           => '',
                    'fp_bg_color'        => '',
                    'fp_layout'          => 'magazine',
                    'fp_slider_ratio'    => '70',
                    'fp_slider_height'   => '420px',
                    'fp_autoplay'        => 'yes',
                    'fp_interval'        => '5000',
                    'fp_effect'          => 'fade',
                    'fp_show_arrows'     => 'yes',
                    'fp_show_dots'       => 'yes',
                    'fp_slider_source'   => 'latest',
                    'fp_slider_count'    => '5',
                    'fp_list_source'     => 'latest',
                    'fp_list_count'      => '6',
                    'fp_deduplicate'     => 'yes',
                    'fp_badge_type'      => 'recommend',
                    'fp_badge_position'  => 'left',
                    'fp_show_category'   => 'yes',
                    'fp_show_date'       => 'yes',
                    'fp_show_excerpt'    => 'yes',
                    'fp_show_views'      => 'yes',
                    'fp_show_reading_time' => 'yes',
                ),
            ),

            // 模块2：博客布局 - 文章列表（支持分页）
            array(
                'type' => 'blog',
                'data' => $blog_module_data,
            ),
        );

        if ( $is_minimal_preset || $is_artist_preset ) {
            $default_modules = array( $default_modules[1] );
        }

        return $default_modules;
    }
}
