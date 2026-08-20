<?php
/**
 * 软件首页页面创建器类
 *
 * 当用户选择"软件首页"模板创建页面时，自动填充预设模块内容
 * 使用搜索区、软件轮播、软件分类展示、软件排行榜等模块组合
 * 适合软件下载网站、应用商店首页使用
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
 * 软件首页页面创建器类
 */
class Software_Home_Page_Creator extends Page_Creator_Base {

    /**
     * 页面模板路径。
     *
     * @var string
     */
    protected const TEMPLATE = 'templates/template-software-home.php';

    /**
     * 后台手动填充模块的 AJAX action。
     *
     * @var string
     */
    protected const AJAX_ACTION = 'fill_software_home_modules';

    /**
     * 模块已填充标记 meta key。
     *
     * @var string
     */
    protected const FILLED_META_KEY = '_software_home_modules_filled';

    /**
     * 获取软件首页的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            
            // ========== 模块1：顶部搜索区域 ==========
            array(
                'type' => 'hero_search',
                'data' => array(
                    'hs_height'             => 'medium',
                    'hs_title'              => __( '发现精品软件', 'developer-starter' ),
                    'hs_subtitle'           => __( '安全、免费、快速下载，一站式软件下载平台', 'developer-starter' ),
                    'hs_bg_items'           => array(
                        array(
                            'type'      => 'image',
                            'image'     => '',
                            'video_url' => '',
                        ),
                    ),
                    'hs_bg_overlay'         => 'yes',
                    'hs_overlay_opacity'    => '60',
                    'hs_search_placeholder' => __( '搜索软件名称、分类或关键词...', 'developer-starter' ),
                    'hs_search_btn_bg'      => 'linear-gradient(135deg, #3b82f6 0%, #6366f1 100%)',
                    'hs_tags'               => array(
                        array( 'text' => __( '办公软件', 'developer-starter' ) ),
                        array( 'text' => __( '系统工具', 'developer-starter' ) ),
                        array( 'text' => __( '影音播放', 'developer-starter' ) ),
                        array( 'text' => __( '图形设计', 'developer-starter' ) ),
                        array( 'text' => __( '开发工具', 'developer-starter' ) ),
                    ),
                    'hs_title_color'        => '#ffffff',
                    'hs_subtitle_color'     => 'rgba(255,255,255,0.85)',
                    'hs_tags_bg'            => 'rgba(255,255,255,0.15)',
                    'hs_tags_color'         => '#ffffff',
                ),
            ),

            // ========== 模块2：软件轮播 - 推荐软件 ==========
            array(
                'type' => 'software_carousel',
                'data' => array(
                    'software_carousel_title'       => __( '🌟 精选推荐', 'developer-starter' ),
                    'software_carousel_subtitle'    => __( '编辑精选的优质软件', 'developer-starter' ),
                    'software_carousel_bg_color'    => '#f8fafc',
                    'software_carousel_categories'  => '',
                    'software_carousel_count'       => '10',
                    'software_carousel_speed'       => '35',
                    'software_carousel_card_bg'     => '#ffffff',
                    'software_carousel_icon_size'   => '56px',
                    'software_carousel_show_btn'    => '1',
                    'software_carousel_btn_text'    => __( '全部软件', 'developer-starter' ),
                    'software_carousel_btn_link'    => '/software/',
                    'module_margin_top'             => '-60px',
                ),
            ),

            // ========== 模块3：软件分类展示 - 网格布局 ==========
            array(
                'type' => 'software_category',
                'data' => array(
                    'sc_title'          => __( '💻 热门软件', 'developer-starter' ),
                    'sc_subtitle'       => __( '最受欢迎的软件下载', 'developer-starter' ),
                    'sc_bg_color'       => '',
                    'sc_layout'         => 'grid',
                    'sc_columns'        => '4',
                    'sc_categories'     => '',
                    'sc_count'          => '8',
                    'sc_orderby'        => 'downloads',
                    'sc_card_bg'        => '#ffffff',
                    'sc_icon_size'      => '56px',
                    'sc_show_version'   => '1',
                    'sc_show_date'      => '1',
                    'sc_show_downloads' => '1',
                    'sc_show_desc'      => '0',
                    'sc_show_btn'       => '1',
                    'sc_btn_text'       => __( '查看更多', 'developer-starter' ),
                    'sc_btn_link'       => '/software/',
                ),
            ),

            // ========== 模块4：数据统计 ==========
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_bg_image'   => '',
                    'stats_bg_color'   => 'linear-gradient(135deg, #1e293b 0%, #334155 100%)',
                    'stats_text_align' => 'center',
                    'stats_items'      => array(
                        array( 'number' => '1000+', 'label' => __( '软件资源', 'developer-starter' ) ),
                        array( 'number' => '50万+', 'label' => __( '累计下载', 'developer-starter' ) ),
                        array( 'number' => '100%', 'label' => __( '安全检测', 'developer-starter' ) ),
                        array( 'number' => '24H', 'label' => __( '更新维护', 'developer-starter' ) ),
                    ),
                ),
            ),

            // ========== 模块5：软件排行榜 - 卡片布局 ==========
            array(
                'type' => 'software_ranking',
                'data' => array(
                    'sr_title'           => __( '🏆 下载排行榜', 'developer-starter' ),
                    'sr_subtitle'        => __( '用户下载最多的软件', 'developer-starter' ),
                    'sr_bg_color'        => '#f8fafc',
                    'sr_ranking_type'    => 'downloads',
                    'sr_layout'          => 'cards',
                    'sr_columns'         => '2',
                    'sr_categories'      => '',
                    'sr_count'           => '10',
                    'sr_card_bg'         => '#ffffff',
                    'sr_icon_size'       => '48px',
                    'sr_show_rank_badge' => '1',
                    'sr_show_stats'      => '1',
                    'sr_show_version'    => '1',
                    'sr_show_btn'        => '0',
                ),
            ),

            // ========== 模块6：软件分类展示 - 列表布局（最新更新）==========
            array(
                'type' => 'software_category',
                'data' => array(
                    'sc_title'          => __( '🆕 最新更新', 'developer-starter' ),
                    'sc_subtitle'       => __( '最近更新的软件版本', 'developer-starter' ),
                    'sc_bg_color'       => '',
                    'sc_layout'         => 'list',
                    'sc_columns'        => '4',
                    'sc_categories'     => '',
                    'sc_count'          => '6',
                    'sc_orderby'        => 'modified',
                    'sc_card_bg'        => '#ffffff',
                    'sc_icon_size'      => '48px',
                    'sc_show_version'   => '1',
                    'sc_show_date'      => '1',
                    'sc_show_downloads' => '0',
                    'sc_show_desc'      => '1',
                    'sc_show_btn'       => '1',
                    'sc_btn_text'       => __( '更多更新', 'developer-starter' ),
                    'sc_btn_link'       => '/software/',
                ),
            ),

            // ========== 模块7：软件分类展示 - 紧凑布局（更多软件）==========
            array(
                'type' => 'software_category',
                'data' => array(
                    'sc_title'          => __( '📦 更多软件', 'developer-starter' ),
                    'sc_subtitle'       => __( '浏览更多软件分类', 'developer-starter' ),
                    'sc_bg_color'       => '#f1f5f9',
                    'sc_layout'         => 'compact',
                    'sc_columns'        => '5',
                    'sc_categories'     => '',
                    'sc_count'          => '15',
                    'sc_orderby'        => 'random',
                    'sc_card_bg'        => '#ffffff',
                    'sc_icon_size'      => '36px',
                    'sc_show_version'   => '0',
                    'sc_show_date'      => '0',
                    'sc_show_downloads' => '0',
                    'sc_show_desc'      => '0',
                    'sc_show_btn'       => '1',
                    'sc_btn_text'       => __( '浏览全部', 'developer-starter' ),
                    'sc_btn_link'       => '/software/',
                ),
            ),

            // ========== 模块8：FAQ常见问题 ==========
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'       => __( '常见问题', 'developer-starter' ),
                    'faq_subtitle'    => __( '关于软件下载的常见疑问解答', 'developer-starter' ),
                    'faq_bg_color'    => '',
                    'faq_items'       => array(
                        array(
                            'question' => '软件下载是否安全？',
                            'answer'   => '本站所有软件均经过严格的安全检测，确保无病毒、无木马、无恶意代码。我们承诺提供100%安全的软件下载服务。',
                        ),
                        array(
                            'question' => '下载的软件是免费的吗？',
                            'answer'   => '我们提供大量免费软件下载，部分付费软件会明确标注。免费软件可永久免费使用，无隐藏收费。',
                        ),
                        array(
                            'question' => '如何安装下载的软件？',
                            'answer'   => '下载完成后，双击安装包按照提示操作即可。如遇到问题，可参考软件详情页的安装说明或联系客服获取帮助。',
                        ),
                        array(
                            'question' => '软件更新后如何升级？',
                            'answer'   => '当软件有新版本发布时，您可以在本站重新下载最新版本进行覆盖安装，或使用软件内置的自动更新功能。',
                        ),
                    ),
                ),
            ),

            // ========== 模块9：CTA行动召唤 ==========
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'       => __( '找不到需要的软件？', 'developer-starter' ),
                    'cta_subtitle'    => __( '告诉我们您需要什么软件，我们会尽快添加', 'developer-starter' ),
                    'cta_button_text' => __( '提交软件需求', 'developer-starter' ),
                    'cta_button_url'  => '/contact/',
                    'cta_bg_type'     => 'gradient',
                ),
            ),
        );

        return $default_modules;
    }
}
