<?php
/**
 * 资源搜索页面创建器类
 *
 * 当用户选择"资源搜索"模板创建页面时，自动填充预设模块内容
 * 使用图片视频搜索、服务标签卡片、博客布局等模块组合
 * 适合素材网站、资源站首页使用
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
 * 资源搜索页面创建器类
 */
class Resource_Search_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-resource-search.php';
    protected const AJAX_ACTION = 'fill_resource_search_modules';
    protected const FILLED_META_KEY = '_resource_search_modules_filled';

    /**
     * 获取资源搜索页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 模块1：图片视频搜索 - 首屏搜索区域
            array(
                'type' => 'hero_search',
                'data' => array(
                    'hs_height'      => 'medium',
                    'hs_title'       => __( '发现优质资源', 'developer-starter' ),
                    'hs_subtitle'    => __( '海量素材、模板、工具，一键搜索，即刻获取', 'developer-starter' ),
                    'hs_bg_items'    => array(
                        array(
                            'type'      => 'image',
                            'image'     => '',
                            'video_url' => '',
                        ),
                    ),
                    'hs_bg_overlay'      => 'yes',
                    'hs_overlay_opacity' => '50',
                    'hs_search_placeholder' => __( '搜索你想要的资源...', 'developer-starter' ),
                    'hs_search_btn_bg'   => 'linear-gradient(135deg, #3b82f6 0%, #10b981 100%)',
                    'hs_tags'            => array(
                        array( 'text' => __( 'WordPress主题', 'developer-starter' ) ),
                        array( 'text' => __( '网站模板', 'developer-starter' ) ),
                        array( 'text' => __( 'UI素材', 'developer-starter' ) ),
                        array( 'text' => __( '图标资源', 'developer-starter' ) ),
                        array( 'text' => __( '免费资源', 'developer-starter' ) ),
                    ),
                    'hs_title_color'     => '#ffffff',
                    'hs_subtitle_color'  => 'rgba(255,255,255,0.8)',
                    'hs_tags_bg'         => 'rgba(255,255,255,0.15)',
                    'hs_tags_color'      => '#ffffff',
                ),
            ),

            // 模块2：服务标签卡片 - 分类导航（负间距覆盖首屏）
            array(
                'type' => 'service_cards',
                'data' => array(
                    'sc_bg_color'    => '',
                    'sc_padding'     => '20',
                    'sc_columns'     => '4',
                    'sc_gap'         => '20px',
                    'sc_card_bg'     => 'rgba(255, 255, 255, 1)',
                    'sc_card_radius' => '16px',
                    'sc_card_shadow' => 'strong',
                    'sc_icon_size'   => '48px',
                    'sc_icon_bg'     => 'linear-gradient(135deg, #3b82f6 0%, #10b981 100%)',
                    'sc_icon_radius' => '12px',
                    'sc_badge_bg'    => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
                    'sc_cards'       => array(
                        array(
                            'icon'        => '🎨',
                            'icon_image'  => '',
                            'title'       => __( 'WordPress主题', 'developer-starter' ),
                            'badge'       => 'Hot',
                            'description' => __( '精选企业级主题模板', 'developer-starter' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '🧩',
                            'icon_image'  => '',
                            'title'       => __( '插件扩展', 'developer-starter' ),
                            'badge'       => '',
                            'description' => __( '功能增强插件合集', 'developer-starter' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '📦',
                            'icon_image'  => '',
                            'title'       => __( 'UI素材包', 'developer-starter' ),
                            'badge'       => 'New',
                            'description' => __( '设计师必备素材库', 'developer-starter' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '📚',
                            'icon_image'  => '',
                            'title'       => __( '教程文档', 'developer-starter' ),
                            'badge'       => '',
                            'description' => __( '从入门到精通指南', 'developer-starter' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                    ),
                    'module_margin_top'    => '-80px',
                    'module_margin_bottom' => '40px',
                ),
            ),

            // 模块3：博客布局 - 最新资源展示
            array(
                'type' => 'blog',
                'data' => array(
                    'blog_title'         => __( '最新资源', 'developer-starter' ),
                    'blog_subtitle'      => __( '精选优质内容，持续更新', 'developer-starter' ),
                    'blog_bg_color'      => '#f8fafc',
                    'blog_page_layout'   => 'full',
                    'blog_layout_style'  => 'card',
                    'blog_columns'       => '4',
                    'blog_data_source'   => 'latest',
                    'blog_count'         => '8',
                    'blog_orderby'       => 'date',
                    'blog_show_image'    => 'yes',
                    'blog_image_height'  => '180px',
                    'blog_show_excerpt'  => 'yes',
                    'blog_excerpt_length' => '40',
                    'blog_show_author'   => 'no',
                    'blog_show_date'     => 'yes',
                    'blog_show_category' => 'yes',
                    'blog_show_tags'     => 'no',
                    'blog_read_more_text' => '',
                    'blog_enable_pagination' => 'no',
                ),
            ),

            // 模块4：数据统计
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_bg_image'   => '',
                    'stats_text_align' => 'center',
                    'stats_items'      => array(
                        array( 'number' => '1000+', 'label' => __( '优质资源', 'developer-starter' ) ),
                        array( 'number' => '50+', 'label' => __( '资源分类', 'developer-starter' ) ),
                        array( 'number' => '10000+', 'label' => __( '下载次数', 'developer-starter' ) ),
                        array( 'number' => '24/7', 'label' => __( '在线服务', 'developer-starter' ) ),
                    ),
                ),
            ),

            // 模块5：特色资源 - 另一个博客布局（热门资源）
            array(
                'type' => 'blog',
                'data' => array(
                    'blog_title'         => __( '热门推荐', 'developer-starter' ),
                    'blog_subtitle'      => __( '用户最喜爱的资源合集', 'developer-starter' ),
                    'blog_bg_color'      => '',
                    'blog_page_layout'   => 'full',
                    'blog_layout_style'  => 'list',
                    'blog_columns'       => '1',
                    'blog_data_source'   => 'latest',
                    'blog_count'         => '5',
                    'blog_orderby'       => 'comment_count',
                    'blog_show_image'    => 'yes',
                    'blog_image_height'  => '200px',
                    'blog_show_excerpt'  => 'yes',
                    'blog_excerpt_length' => '80',
                    'blog_show_author'   => 'yes',
                    'blog_show_date'     => 'yes',
                    'blog_show_category' => 'yes',
                    'blog_show_tags'     => 'yes',
                    'blog_read_more_text' => __( '查看详情', 'developer-starter' ),
                    'blog_enable_pagination' => 'no',
                ),
            ),

            // 模块6：CTA行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'       => __( '找不到想要的资源？', 'developer-starter' ),
                    'cta_subtitle'    => __( '告诉我们您的需求，我们帮您找到或定制', 'developer-starter' ),
                    'cta_button_text' => __( '提交需求', 'developer-starter' ),
                    'cta_button_url'  => '/contact/',
                    'cta_bg_type'     => 'gradient',
                ),
            ),
        );

        return $default_modules;
    }
}
