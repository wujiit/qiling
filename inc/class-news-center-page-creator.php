<?php
/**
 * 新闻中心页面创建器类
 *
 * 当用户选择"新闻中心"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 2.1.3
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 新闻中心页面创建器类
 */
class News_Center_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-news.php';
    protected const AJAX_ACTION = 'fill_qiling_news_center_modules';
    protected const FILLED_META_KEY = '_qiling_news_center_modules_filled';

    /**
     * 获取新闻中心页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 模块1：首屏 Banner
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout' => 'slider',
                    'banner_height' => 'medium',
                    'banner_bg_color' => 'linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%)',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image' => '',
                            'title' => __( '新闻中心', 'developer-starter' ),
                            'subtitle' => __( '聚合最新动态与行业洞察', 'developer-starter' ),
                            'btn_text' => __( '查看最新', 'developer-starter' ),
                            'btn_url' => '#',
                            'btn_bg_color' => '#ffffff',
                            'btn_text_color' => '#0f172a',
                        ),
                    ),
                ),
            ),

            // 模块2：新闻列表
            array(
                'type' => 'news',
                'data' => array(
                    'news_title' => __( '最新新闻', 'developer-starter' ),
                    'news_count' => '9',
                    'news_columns' => '3',
                    'news_categories' => '',
                    'news_show_image' => '1',
                    'news_image_height' => '220px',
                    'news_show_excerpt' => '1',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块3：CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title' => __( '订阅我们的动态', 'developer-starter' ),
                    'cta_subtitle' => __( '获取产品更新、活动与行业趋势', 'developer-starter' ),
                    'cta_button_text' => __( '联系我们', 'developer-starter' ),
                    'cta_button_url' => '/contact',
                    'cta_bg_type' => 'color',
                    'cta_bg_color' => 'linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%)',
                ),
            ),
        );

        return $default_modules;
    }
}
