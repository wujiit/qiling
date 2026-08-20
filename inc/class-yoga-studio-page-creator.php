<?php
/**
 * 瑜伽馆官网页创建器类
 *
 * 当用户选择"瑜伽馆官网（体验课）"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 瑜伽馆官网页创建器类
 */
class Yoga_Studio_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-yoga-studio.php';
    protected const AJAX_ACTION = 'fill_yoga_studio_modules';
    protected const FILLED_META_KEY = '_yoga_studio_modules_filled';

    /**
     * 获取瑜伽馆官网页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '瑜伽馆官网（体验课）', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'      => 'slider',
                    'banner_height'      => 'large',
                    'banner_bg_color'    => 'linear-gradient(135deg, #312e81 0%, #0f766e 100%)',
                    'banner_slides'      => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '基础哈他、流瑜伽、普拉提与冥想课程，支持新客体验课预约。', 'developer-starter' ),
                            'btn_text'       => __( '预约体验课', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#312e81',
                        ),
                    ),
                    'show_stats_bar'     => '1',
                    'stats_data'         => array(
                        array( 'icon' => '🧘', 'number' => '80+', 'label' => __( '每周课程', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '👩‍🏫', 'number' => '18+', 'label' => __( '认证导师', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '🏠', 'number' => '3家', 'label' => __( '城市门店', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '⭐', 'number' => '4.9', 'label' => __( '学员评分', 'developer-starter' ), 'color' => '#ffffff' ),
                    ),
                    'banner_wave_enable' => '0',
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '课程体系', 'developer-starter' ),
                    'services_subtitle'         => __( '兼顾初学者入门与进阶训练需求', 'developer-starter' ),
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array( 'icon' => '🌿', 'title' => __( '哈他瑜伽', 'developer-starter' ), 'desc' => __( '呼吸与体式基础训练，适合新手入门。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🔥', 'title' => __( '流瑜伽', 'developer-starter' ), 'desc' => __( '节奏更强，提升核心与体能耐力。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🧍', 'title' => __( '普拉提', 'developer-starter' ), 'desc' => __( '改善体态与深层肌群控制能力。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🌙', 'title' => __( '冥想修复', 'developer-starter' ), 'desc' => __( '缓解压力，提升专注和睡眠质量。', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'curriculum',
                'data' => array(
                    'curriculum_title'           => __( '本周课程安排', 'developer-starter' ),
                    'curriculum_subtitle'        => __( '可按馆内排课和导师安排灵活调整', 'developer-starter' ),
                    'curriculum_primary_color'   => '#312e81',
                    'curriculum_bg_color'        => '#eef2ff',
                    'curriculum_items'           => array(
                        array( 'title' => __( '晨间唤醒流瑜伽', 'developer-starter' ), 'meta' => __( '周一至周五 07:30-08:30', 'developer-starter' ), 'content' => __( '<p>提升晨间状态，帮助身体快速进入工作节奏。</p>', 'developer-starter' ), 'open' => 'yes' ),
                        array( 'title' => __( '午间舒展修复课', 'developer-starter' ), 'meta' => __( '周一至周五 12:30-13:15', 'developer-starter' ), 'content' => __( '<p>针对久坐人群的肩颈与髋部放松训练。</p>', 'developer-starter' ), 'open' => 'no' ),
                        array( 'title' => __( '晚间减压冥想课', 'developer-starter' ), 'meta' => __( '周一/周三/周五 20:00-21:00', 'developer-starter' ), 'content' => __( '<p>呼吸训练与冥想结合，缓解高压状态。</p>', 'developer-starter' ), 'open' => 'no' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'team',
                'data' => array(
                    'team_title'                => __( '导师团队', 'developer-starter' ),
                    'team_subtitle'             => __( '专业认证导师，关注每位学员的动作安全与持续进步', 'developer-starter' ),
                    'team_columns'              => '3',
                    'team_members'              => array(
                        array( 'avatar' => '', 'name' => __( '安娜', 'developer-starter' ), 'position' => __( '首席瑜伽导师', 'developer-starter' ), 'desc' => __( '10年教学经验，擅长流瑜伽和正位训练。', 'developer-starter' ), 'wechat' => '', 'email' => 'yoga1@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '苏晴', 'developer-starter' ), 'position' => __( '普拉提导师', 'developer-starter' ), 'desc' => __( '专注体态矫正与核心激活训练。', 'developer-starter' ), 'wechat' => '', 'email' => 'yoga2@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '林琪', 'developer-starter' ), 'position' => __( '冥想导师', 'developer-starter' ), 'desc' => __( '帮助高压人群建立稳定的呼吸和放松节奏。', 'developer-starter' ), 'wechat' => '', 'email' => 'yoga3@example.com', 'phone' => '' ),
                    ),
                    'module_bg_type'            => 'color',
                    'module_bg_color'           => '#ffffff',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'pricing',
                'data' => array(
                    'pricing_title'              => __( '会籍与课包', 'developer-starter' ),
                    'pricing_subtitle'           => __( '按上课频次和目标选择方案，支持体验后升级', 'developer-starter' ),
                    'pricing_columns'            => '3',
                    'module_bg_type'             => 'color',
                    'module_bg_color'            => '#eef2ff',
                    'module_padding_top'         => '80px',
                    'module_padding_bottom'      => '80px',
                    'pricing_items'              => array(
                        array(
                            'name'     => __( '新客体验包', 'developer-starter' ),
                            'price'    => '¥199',
                            'period'   => __( '/3节', 'developer-starter' ),
                            'desc'     => __( '适合首次体验课程节奏', 'developer-starter' ),
                            'features' => __( "✓ 任选基础课程\n✓ 首次体态评估\n✓ 课后建议", 'developer-starter' ),
                            'btn_text' => __( '立即预约', 'developer-starter' ),
                            'btn_link' => '#',
                            'card_bg'  => '#ffffff',
                        ),
                        array(
                            'name'          => __( '月度会籍', 'developer-starter' ),
                            'price'         => '¥599',
                            'period'        => __( '/月', 'developer-starter' ),
                            'desc'          => __( '适合每周2-3次稳定练习', 'developer-starter' ),
                            'features'      => __( "✓ 不限基础课程\n✓ 每月导师复盘\n✓ 冥想课通用", 'developer-starter' ),
                            'btn_text'      => __( '立即开通', 'developer-starter' ),
                            'btn_link'      => '#',
                            'card_bg'       => '#ffffff',
                            'featured'      => '1',
                            'featured_text' => __( '推荐', 'developer-starter' ),
                            'featured_bg'   => '#312e81',
                        ),
                        array(
                            'name'     => __( '季度进阶包', 'developer-starter' ),
                            'price'    => '¥1499',
                            'period'   => __( '/季', 'developer-starter' ),
                            'desc'     => __( '适合需要体态和体能提升人群', 'developer-starter' ),
                            'features' => __( "✓ 进阶课程优先\n✓ 私教评估1次\n✓ 阶段训练计划", 'developer-starter' ),
                            'btn_text' => __( '咨询顾问', 'developer-starter' ),
                            'btn_link' => '#',
                            'card_bg'  => '#ffffff',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '学员反馈', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实学习体验，帮助新学员快速判断适配度', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '3,600+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array( 'avatar' => '', 'name' => __( '许女士', 'developer-starter' ), 'position' => __( '上班族学员', 'developer-starter' ), 'content' => __( '课程强度分层明确，新手跟练也不会有压力。', 'developer-starter' ), 'rating' => '5', 'source' => 'dianping', 'date' => '2026-02-24', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '赵先生', 'developer-starter' ), 'position' => __( '普拉提学员', 'developer-starter' ), 'content' => __( '久坐肩颈问题改善明显，导师纠正动作很细致。', 'developer-starter' ), 'rating' => '5', 'source' => 'meituan', 'date' => '2026-03-05', 'verified' => 'vip', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '何小姐', 'developer-starter' ), 'position' => __( '冥想课程学员', 'developer-starter' ), 'content' => __( '晚间冥想课特别适合放松，睡眠质量提升明显。', 'developer-starter' ), 'rating' => '5', 'source' => 'xiaohongshu', 'date' => '2026-03-14', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'           => __( '预约瑜伽体验课', 'developer-starter' ),
                    'booking_subtitle'        => __( '提交后由课程顾问确认时段与场馆安排。', 'developer-starter' ),
                    'form_id'                 => '',
                    'booking_layout'          => 'sidebar',
                    'sidebar_title'           => __( '体验课说明', 'developer-starter' ),
                    'sidebar_content'         => __( "建议穿着轻便运动服\n可备注练习目标和身体情况\n首次到店建议提前10分钟\n支持新手体验友好课程", 'developer-starter' ),
                    'contact_phone'           => '400-820-6633',
                    'contact_hours'           => __( '咨询时间 08:00-22:00', 'developer-starter' ),
                    'module_bg_color'         => '#eef2ff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '入门常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '先了解课程规则，再安排适合自己的练习计划', 'developer-starter' ),
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array( 'question' => __( '零基础可以直接上课吗？', 'developer-starter' ), 'answer' => __( '可以，新客可优先选择基础课程与体验班。', 'developer-starter' ) ),
                        array( 'question' => __( '课程需要自带瑜伽垫吗？', 'developer-starter' ), 'answer' => __( '馆内可提供基础器材，也支持自带个人用品。', 'developer-starter' ) ),
                        array( 'question' => __( '可以跨门店上课吗？', 'developer-starter' ), 'answer' => __( '支持跨店预约，具体以会籍规则和排班为准。', 'developer-starter' ) ),
                        array( 'question' => __( '上课前多久停止进食？', 'developer-starter' ), 'answer' => __( '建议课前1-2小时避免饱食，保持舒适练习状态。', 'developer-starter' ) ),
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '从一节体验课开始', 'developer-starter' ),
                    'cta_subtitle'          => __( '选择适合你的课程节奏，稳定建立身体与情绪状态。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即预约体验课', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #312e81 0%, #0f766e 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
