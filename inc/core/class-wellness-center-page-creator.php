<?php
/**
 * 康养中心官网页创建器类
 *
 * 当用户选择"康养中心官网页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 康养中心官网页创建器类
 */
class Wellness_Center_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-wellness-center.php';
    protected const AJAX_ACTION = 'fill_wellness_center_modules';
    protected const FILLED_META_KEY = '_wellness_center_modules_filled';

    /**
     * 获取康养中心官网页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '康养中心官网页', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'      => 'slider',
                    'banner_height'      => 'large',
                    'banner_bg_color'    => 'linear-gradient(135deg, #14532d 0%, #0f766e 100%)',
                    'banner_slides'      => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '中医调理、康复理疗、睡眠管理与营养干预，提供持续跟踪服务。', 'developer-starter' ),
                            'btn_text'       => __( '预约健康评估', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#0f766e',
                        ),
                    ),
                    'show_stats_bar'     => '1',
                    'stats_data'         => array(
                        array( 'icon' => '🩺', 'number' => '15年+', 'label' => __( '康养服务经验', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '👩‍⚕️', 'number' => '40+', 'label' => __( '康复医护团队', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '🏥', 'number' => '3家', 'label' => __( '服务中心', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '⭐', 'number' => '4.9', 'label' => __( '用户评价', 'developer-starter' ), 'color' => '#ffffff' ),
                    ),
                    'banner_wave_enable' => '0',
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '核心康养服务', 'developer-starter' ),
                    'services_subtitle'         => __( '围绕评估、干预、复评形成长期健康管理闭环', 'developer-starter' ),
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array( 'icon' => '🌿', 'title' => __( '中医调理', 'developer-starter' ), 'desc' => __( '体质辨识、亚健康调理与周期性干预。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🦴', 'title' => __( '康复理疗', 'developer-starter' ), 'desc' => __( '颈肩腰腿痛、术后康复与功能训练支持。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🥗', 'title' => __( '营养管理', 'developer-starter' ), 'desc' => __( '按健康目标制定饮食建议与执行计划。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '😴', 'title' => __( '睡眠干预', 'developer-starter' ), 'desc' => __( '睡眠评估、行为调整与习惯建立方案。', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'team',
                'data' => array(
                    'team_title'                => __( '康养专家团队', 'developer-starter' ),
                    'team_subtitle'             => __( '多学科协作，提供更稳定的长期健康管理体验', 'developer-starter' ),
                    'team_columns'              => '3',
                    'team_members'              => array(
                        array( 'avatar' => '', 'name' => __( '孙医生', 'developer-starter' ), 'position' => __( '康复医学主任', 'developer-starter' ), 'desc' => __( '擅长运动损伤康复与慢性疼痛管理。', 'developer-starter' ), 'wechat' => '', 'email' => 'wellness1@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '李医师', 'developer-starter' ), 'position' => __( '中医调理师', 'developer-starter' ), 'desc' => __( '长期服务亚健康与体质调理人群。', 'developer-starter' ), 'wechat' => '', 'email' => 'wellness2@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '郑营养师', 'developer-starter' ), 'position' => __( '营养管理顾问', 'developer-starter' ), 'desc' => __( '聚焦体重管理与慢病膳食干预。', 'developer-starter' ), 'wechat' => '', 'email' => 'wellness3@example.com', 'phone' => '' ),
                    ),
                    'module_bg_type'            => 'color',
                    'module_bg_color'           => '#f0fdfa',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'menu',
                'data' => array(
                    'menu_title'               => __( '康养<span style="color:#0f766e">套餐参考</span>', 'developer-starter' ),
                    'menu_subtitle'            => __( '以下为常见服务包示例，可按门店实际项目调整', 'developer-starter' ),
                    'menu_layout'              => 'grid',
                    'menu_accent_color'        => '#0f766e',
                    'menu_items'               => array(
                        array( 'image' => '', 'title' => __( '基础评估包', 'developer-starter' ), 'desc' => __( '体态/睡眠/饮食初评 + 报告解读', 'developer-starter' ), 'price' => '¥299', 'badge' => __( '新客推荐', 'developer-starter' ), 'link' => '#' ),
                        array( 'image' => '', 'title' => __( '康复理疗包', 'developer-starter' ), 'desc' => __( '8次理疗 + 训练指导 + 周度复盘', 'developer-starter' ), 'price' => '¥1680', 'badge' => __( '热门', 'developer-starter' ), 'link' => '#' ),
                        array( 'image' => '', 'title' => __( '中医调理月度包', 'developer-starter' ), 'desc' => __( '辨证调理 + 周期跟踪建议', 'developer-starter' ), 'price' => '¥1280', 'badge' => __( '慢病管理', 'developer-starter' ), 'link' => '#' ),
                        array( 'image' => '', 'title' => __( '睡眠修复计划', 'developer-starter' ), 'desc' => __( '睡眠干预 + 行为训练 + 持续反馈', 'developer-starter' ), 'price' => '¥980', 'badge' => __( '周期方案', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'              => __( '服务流程', 'developer-starter' ),
                    'process_subtitle'           => __( '从初评到复评，全流程可视化管理', 'developer-starter' ),
                    'process_mode'               => 'standard',
                    'process_items'              => array(
                        array( 'icon' => '01', 'title' => __( '健康评估', 'developer-starter' ), 'desc' => __( '收集身体状态、作息与目标，建立个人档案。', 'developer-starter' ), 'stage_tag' => __( '初评', 'developer-starter' ) ),
                        array( 'icon' => '02', 'title' => __( '方案制定', 'developer-starter' ), 'desc' => __( '由医师与顾问联合输出康养干预计划。', 'developer-starter' ), 'stage_tag' => __( '计划', 'developer-starter' ) ),
                        array( 'icon' => '03', 'title' => __( '执行干预', 'developer-starter' ), 'desc' => __( '按周执行理疗/训练/营养建议并持续记录。', 'developer-starter' ), 'stage_tag' => __( '执行', 'developer-starter' ) ),
                        array( 'icon' => '04', 'title' => __( '阶段复评', 'developer-starter' ), 'desc' => __( '评估效果并迭代下一阶段健康目标。', 'developer-starter' ), 'stage_tag' => __( '复评', 'developer-starter' ) ),
                    ),
                    'module_bg_type'             => 'color',
                    'module_bg_color'            => '#ffffff',
                    'module_padding_top'         => '80px',
                    'module_padding_bottom'      => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '用户反馈', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '长期康养效果来自持续执行与专业跟踪', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '2,300+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array( 'avatar' => '', 'name' => __( '陈女士', 'developer-starter' ), 'position' => __( '睡眠管理用户', 'developer-starter' ), 'content' => __( '执行两个月后入睡更稳定，白天精神状态改善明显。', 'developer-starter' ), 'rating' => '5', 'source' => 'dianping', 'date' => '2026-02-18', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '林先生', 'developer-starter' ), 'position' => __( '康复理疗用户', 'developer-starter' ), 'content' => __( '腰背不适缓解明显，训练动作也有人持续纠正。', 'developer-starter' ), 'rating' => '5', 'source' => 'meituan', 'date' => '2026-03-02', 'verified' => 'vip', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '吴女士', 'developer-starter' ), 'position' => __( '营养管理用户', 'developer-starter' ), 'content' => __( '饮食方案可执行性强，体重和作息都更可控。', 'developer-starter' ), 'rating' => '5', 'source' => 'xiaohongshu', 'date' => '2026-03-10', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'           => __( '预约健康评估', 'developer-starter' ),
                    'booking_subtitle'        => __( '提交后由康养顾问联系确认时间，支持到店面诊。', 'developer-starter' ),
                    'form_id'                 => '',
                    'booking_layout'          => 'sidebar',
                    'sidebar_title'           => __( '预约须知', 'developer-starter' ),
                    'sidebar_content'         => __( "请备注当前健康困扰\n首次到店建议携带体检资料\n可填写可预约时间段\n支持家属陪同咨询", 'developer-starter' ),
                    'contact_phone'           => '400-860-1188',
                    'contact_hours'           => __( '咨询时间 09:00-21:00', 'developer-starter' ),
                    'module_bg_color'         => '#f0fdfa',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '康养常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '先了解服务边界，再安排适合你的干预计划', 'developer-starter' ),
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array( 'question' => __( '首次评估需要空腹吗？', 'developer-starter' ), 'answer' => __( '基础评估一般不需要空腹，特殊项目会提前告知。', 'developer-starter' ) ),
                        array( 'question' => __( '康养方案一般持续多久？', 'developer-starter' ), 'answer' => __( '常见周期为4-12周，会根据你的执行情况动态调整。', 'developer-starter' ) ),
                        array( 'question' => __( '可以只做单次理疗吗？', 'developer-starter' ), 'answer' => __( '支持单次体验，但建议结合评估后做阶段管理。', 'developer-starter' ) ),
                        array( 'question' => __( '是否支持异地线上复评？', 'developer-starter' ), 'answer' => __( '支持线上复评和计划跟进，到店项目另行预约。', 'developer-starter' ) ),
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '现在开始你的健康管理计划', 'developer-starter' ),
                    'cta_subtitle'          => __( '从一次评估开始，建立可长期执行的康养路径。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即预约评估', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #14532d 0%, #0f766e 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
