<?php
/**
 * 婚纱摄影官网页创建器类
 *
 * 当用户选择"婚纱摄影官网（预约版）"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 婚纱摄影官网页创建器类
 */
class Wedding_Photography_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-wedding-photography.php';
    protected const AJAX_ACTION = 'fill_wedding_photography_modules';
    protected const FILLED_META_KEY = '_wedding_photography_modules_filled';

    /**
     * 获取婚纱摄影官网页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '婚纱摄影官网（预约版）', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：首屏
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'       => 'slider',
                    'banner_height'       => 'large',
                    'banner_bg_color'     => 'linear-gradient(135deg, #4a044e 0%, #be185d 100%)',
                    'banner_slides'       => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '婚纱照、旅拍、婚礼跟拍一站式服务，档期可在线预约咨询。', 'developer-starter' ),
                            'btn_text'       => __( '预约档期', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#be185d',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '真实客片，风格清晰', 'developer-starter' ),
                            'subtitle'       => __( '从前期沟通到成片交付，全流程明确可追踪。', 'developer-starter' ),
                            'btn_text'       => __( '查看客片风格', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#be185d',
                        ),
                    ),
                    'show_stats_bar'      => '1',
                    'stats_data'          => array(
                        array(
                            'icon'   => '📸',
                            'number' => '5000+',
                            'label'  => __( '服务新人', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '🌍',
                            'number' => '30+',
                            'label'  => __( '旅拍城市', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '⭐',
                            'number' => '4.9',
                            'label'  => __( '综合评分', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '🎞️',
                            'number' => '7天',
                            'label'  => __( '初片交付', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                    ),
                    'banner_wave_enable'  => '0',
                ),
            ),

            // 模块2：搭配画册
            array(
                'type' => 'lookbook',
                'data' => array(
                    'lookbook_title'             => __( '婚纱风格画册', 'developer-starter' ),
                    'lookbook_columns'           => '3',
                    'module_bg_color'            => '#fff6fa',
                    'module_padding_top'         => '80px',
                    'module_padding_bottom'      => '80px',
                    'enable_staggered_animation' => 'yes',
                    'lookbook_items'             => array(
                        array(
                            'cover_image'      => '',
                            'video_360'        => '',
                            'name'             => __( '法式轻奢 · LOOK 01', 'developer-starter' ),
                            'desc'             => __( '强调光影层次和高级感的经典婚纱风格。', 'developer-starter' ),
                            'btn_text'         => __( '查看详情', 'developer-starter' ),
                            'item_1_img'       => '',
                            'item_1_title'     => __( '主纱造型', 'developer-starter' ),
                            'item_1_price'     => '¥6999',
                            'item_1_link'      => '#',
                            'item_1_spec_name' => __( '经典白纱', 'developer-starter' ),
                            'item_1_btn_bg'    => '#be185d',
                            'item_2_img'       => '',
                            'item_2_title'     => __( '晚宴礼服', 'developer-starter' ),
                            'item_2_price'     => '¥2999',
                            'item_2_link'      => '#',
                            'item_2_spec_name' => __( '酒红礼服', 'developer-starter' ),
                            'item_2_btn_bg'    => '#9d174d',
                        ),
                        array(
                            'cover_image'      => '',
                            'video_360'        => '',
                            'name'             => __( '森系自然 · LOOK 02', 'developer-starter' ),
                            'desc'             => __( '轻松自然的情绪表达，适合外景旅拍。', 'developer-starter' ),
                            'btn_text'         => __( '查看详情', 'developer-starter' ),
                            'item_1_img'       => '',
                            'item_1_title'     => __( '轻婚纱造型', 'developer-starter' ),
                            'item_1_price'     => '¥5999',
                            'item_1_link'      => '#',
                            'item_1_spec_name' => __( '森系纱裙', 'developer-starter' ),
                            'item_1_btn_bg'    => '#be185d',
                            'item_2_img'       => '',
                            'item_2_title'     => __( '妆发设计', 'developer-starter' ),
                            'item_2_price'     => '¥1200',
                            'item_2_link'      => '#',
                            'item_2_spec_name' => __( '清透妆面', 'developer-starter' ),
                            'item_2_btn_bg'    => '#9d174d',
                        ),
                        array(
                            'cover_image'      => '',
                            'video_360'        => '',
                            'name'             => __( '电影纪实 · LOOK 03', 'developer-starter' ),
                            'desc'             => __( '偏故事化的纪实表达，强调互动与情绪。', 'developer-starter' ),
                            'btn_text'         => __( '查看详情', 'developer-starter' ),
                            'item_1_img'       => '',
                            'item_1_title'     => __( '双机位拍摄', 'developer-starter' ),
                            'item_1_price'     => '¥8999',
                            'item_1_link'      => '#',
                            'item_1_spec_name' => __( '全天跟拍', 'developer-starter' ),
                            'item_1_btn_bg'    => '#be185d',
                            'item_2_img'       => '',
                            'item_2_title'     => __( '短片剪辑', 'developer-starter' ),
                            'item_2_price'     => '¥1800',
                            'item_2_link'      => '#',
                            'item_2_spec_name' => __( '电影感调色', 'developer-starter' ),
                            'item_2_btn_bg'    => '#9d174d',
                        ),
                    ),
                ),
            ),

            // 模块3：作品相册
            array(
                'type' => 'gallery',
                'data' => array(
                    'gallery_title'             => __( '客片精选相册', 'developer-starter' ),
                    'gallery_subtitle'          => __( '支持按拍摄场景筛选展示，便于快速浏览风格', 'developer-starter' ),
                    'gallery_columns'           => '3',
                    'gallery_style'             => 'masonry',
                    'gallery_gap'               => '16',
                    'enable_filter'             => 'yes',
                    'filter_categories'         => __( '仪式现场,外景旅拍,室内棚拍,婚礼跟拍', 'developer-starter' ),
                    'gallery_lightbox'          => '1',
                    'show_counter'              => 'yes',
                    'module_bg_color'           => '#ffffff',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                    'gallery_images'            => array(
                        array(
                            'image'    => '',
                            'title'    => __( '落日晚霞外景', 'developer-starter' ),
                            'desc'     => __( '海边落日氛围感', 'developer-starter' ),
                            'category' => __( '外景旅拍', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '教堂仪式抓拍', 'developer-starter' ),
                            'desc'     => __( '纪实情绪瞬间', 'developer-starter' ),
                            'category' => __( '仪式现场', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '法式棚拍造型', 'developer-starter' ),
                            'desc'     => __( '高质感光影布景', 'developer-starter' ),
                            'category' => __( '室内棚拍', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '城市夜景旅拍', 'developer-starter' ),
                            'desc'     => __( '电影感夜景叙事', 'developer-starter' ),
                            'category' => __( '外景旅拍', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '婚礼迎宾记录', 'developer-starter' ),
                            'desc'     => __( '亲友互动自然抓拍', 'developer-starter' ),
                            'category' => __( '婚礼跟拍', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '室内极简风客片', 'developer-starter' ),
                            'desc'     => __( '高级简约构图', 'developer-starter' ),
                            'category' => __( '室内棚拍', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块4：案例展示
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'             => __( '服务案例', 'developer-starter' ),
                    'cases_count'             => '6',
                    'cases_columns'           => '3',
                    'cases_show_image'        => '1',
                    'cases_image_height'      => '220px',
                    'cases_padding_top'       => '80px',
                    'cases_padding_bottom'    => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块5：评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '新人评价', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '从沟通到交片，全流程口碑反馈', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '3,500+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#fff6fa',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'      => '',
                            'name'        => __( '林小姐', 'developer-starter' ),
                            'position'    => __( '旅拍新人', 'developer-starter' ),
                            'content'     => __( '摄影团队引导非常自然，成片比预期更有故事感。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'xiaohongshu',
                            'date'        => '2026-02-10',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '周先生', 'developer-starter' ),
                            'position'    => __( '婚礼跟拍客户', 'developer-starter' ),
                            'content'     => __( '重要时刻都被完整记录，后期交付速度也很快。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'dianping',
                            'date'        => '2026-03-02',
                            'verified'    => 'vip',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '何女士', 'developer-starter' ),
                            'position'    => __( '室内棚拍客户', 'developer-starter' ),
                            'content'     => __( '妆造和拍摄节奏配合很好，整体体验很轻松。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'meituan',
                            'date'        => '2026-03-15',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块6：预约入口
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'           => __( '预约档期咨询', 'developer-starter' ),
                    'booking_subtitle'        => __( '提交需求后由顾问回访确认档期与套餐，当前不接入在线支付。', 'developer-starter' ),
                    'form_id'                 => '',
                    'booking_layout'          => 'sidebar',
                    'sidebar_title'           => __( '预约说明', 'developer-starter' ),
                    'sidebar_content'         => __( "请备注婚期与预算区间\n可先选择意向拍摄风格\n旺季档期建议至少提前 30 天\n支持门店面谈与线上沟通", 'developer-starter' ),
                    'contact_phone'           => '400-998-5200',
                    'contact_hours'           => __( '咨询时间 10:00-21:00', 'developer-starter' ),
                    'module_bg_color'         => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                ),
            ),

            // 模块7：FAQ
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '拍摄常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '关于档期、套餐、交付与加选内容说明', 'developer-starter' ),
                    'module_bg_color'        => '#fff6fa',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array(
                            'question' => __( '一般需要提前多久预约？', 'developer-starter' ),
                            'answer'   => __( '旺季建议提前 1-2 个月，淡季建议提前 2-4 周。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '外景旅拍是否包含交通住宿？', 'developer-starter' ),
                            'answer'   => __( '可按套餐包含或单独核算，顾问会在确认方案时说明。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '初片与精修交付周期多久？', 'developer-starter' ),
                            'answer'   => __( '通常 7 天内提供初片，精修交付周期按套餐约定执行。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '可以自带服装和道具吗？', 'developer-starter' ),
                            'answer'   => __( '支持自带，建议提前与造型师沟通整体搭配与拍摄场景。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块8：行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '想先锁定喜欢的拍摄档期？', 'developer-starter' ),
                    'cta_subtitle'          => __( '现在预约咨询，获取适合你们的风格建议与套餐配置。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即预约档期', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #4a044e 0%, #be185d 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
