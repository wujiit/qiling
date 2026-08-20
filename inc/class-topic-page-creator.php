<?php
/**
 * 专题页面创建器类
 *
 * 当用户选择"专题页面"模板创建页面时，自动填充专题模块组合。
 *
 * @package Developer_Starter
 * @since 2.1.3
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Topic_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-topic.php';
    protected const AJAX_ACTION = 'fill_topic_page_modules';
    protected const FILLED_META_KEY = '_topic_page_modules_filled';

    /**
     * 获取 AJAX 成功提示。
     *
     * @return string
     */
    protected function get_ajax_success_message() {
        return __( '专题模块已填充，请刷新页面', 'developer-starter' );
    }

    /**
     * 默认专题模块组合
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            array(
                'type' => 'featured_posts',
                'data' => array(
                    'fp_title'            => __( '专题导读', 'developer-starter' ),
                    'fp_layout'           => 'magazine',
                    'fp_slider_ratio'     => '70',
                    'fp_slider_height'    => '440px',
                    'fp_autoplay'         => 'yes',
                    'fp_interval'         => '5000',
                    'fp_effect'           => 'fade',
                    'fp_show_arrows'      => 'yes',
                    'fp_show_dots'        => 'yes',
                    'fp_slider_source'    => 'latest',
                    'fp_slider_count'     => '6',
                    'fp_list_source'      => 'latest',
                    'fp_list_count'       => '6',
                    'fp_deduplicate'      => 'yes',
                    'fp_badge_type'       => 'featured',
                    'fp_show_category'    => 'yes',
                    'fp_show_date'        => 'yes',
                    'fp_show_excerpt'     => 'yes',
                    'fp_show_views'       => 'yes',
                    'fp_show_reading_time'=> 'yes',
                ),
            ),
            array(
                'type' => 'category_tabs',
                'data' => array(
                    'tabs_title'               => __( '热门标签聚合', 'developer-starter' ),
                    'tabs_subtitle'            => __( '按标签快速切换浏览本专题内容', 'developer-starter' ),
                    'tabs_source_mode'         => 'auto_hot_tags',
                    'tabs_auto_tag_count'      => '8',
                    'tabs_auto_min_count'      => '1',
                    'post_count'               => '8',
                    'columns'                  => '4',
                    'show_date'                => 'yes',
                    'show_author'              => 'no',
                    'show_views'               => 'yes',
                    'show_category_badge'      => 'no',
                    'more_btn_type'            => 'ajax',
                ),
            ),
            array(
                'type' => 'blog',
                'data' => array(
                    'blog_title'               => __( '专题文章流', 'developer-starter' ),
                    'blog_layout_style'        => 'magazine',
                    'blog_columns'             => '3',
                    'blog_data_source'         => 'latest',
                    'blog_count'               => '12',
                    'blog_orderby'             => 'date',
                    'blog_show_image'          => 'yes',
                    'blog_show_excerpt'        => 'yes',
                    'blog_excerpt_length'      => '60',
                    'blog_show_category'       => 'yes',
                    'blog_show_date'           => 'yes',
                    'blog_show_views'          => 'yes',
                    'blog_show_reading_time'   => 'yes',
                    'blog_read_more_text'      => __( '继续阅读', 'developer-starter' ),
                    'blog_enable_pagination'   => 'yes',
                ),
            ),
        );

        return $default_modules;
    }
}
