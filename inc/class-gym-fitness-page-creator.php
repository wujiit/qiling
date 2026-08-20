<?php
/**
 * 健身房官网页创建器类
 *
 * 当用户选择"健身房官网（体验课）"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Gym_Fitness_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-gym-fitness.php';
    protected const AJAX_ACTION = 'fill_gym_fitness_modules';
    protected const FILLED_META_KEY = '_gym_fitness_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '健身房官网（体验课）', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'      => 'slider',
                    'banner_height'      => 'large',
                    'banner_bg_color'    => 'linear-gradient(135deg, #111827 0%, #dc2626 100%)',
                    'banner_slides'      => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '力量训练、减脂塑形、团课训练一站式支持，预约免费体验课。', 'developer-starter' ),
                            'btn_text'       => __( '预约体验课', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#dc2626',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '教练带练 + 训练计划', 'developer-starter' ),
                            'subtitle'       => __( '按体测结果制定训练路径，目标更清晰，打卡更稳定。', 'developer-starter' ),
                            'btn_text'       => __( '查看课程安排', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#dc2626',
                        ),
                    ),
                    'show_stats_bar'     => '1',
                    'stats_data'         => array(
                        array( 'icon' => '🏋️', 'number' => '2000㎡', 'label' => __( '训练空间', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '👟', 'number' => '50+', 'label' => __( '团课课程', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '👨‍🏫', 'number' => '25+', 'label' => __( '专业教练', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '⭐', 'number' => '4.9', 'label' => __( '会员评分', 'developer-starter' ), 'color' => '#ffffff' ),
                    ),
                    'banner_wave_enable' => '0',
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '健身服务', 'developer-starter' ),
                    'services_subtitle'         => __( '围绕增肌、减脂和体态改善的训练支持体系', 'developer-starter' ),
                    'services_bg_color'         => '#ffffff',
                    'services_padding'          => 'normal',
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array( 'icon' => '🔥', 'title' => __( '减脂训练', 'developer-starter' ), 'desc' => __( '体能评估 + 有氧/力量结合，持续跟踪体脂变化。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '💪', 'title' => __( '增肌塑形', 'developer-starter' ), 'desc' => __( '按阶段提升力量与肌肉围度，优化训练动作。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🧘', 'title' => __( '团体课程', 'developer-starter' ), 'desc' => __( '瑜伽、搏击、动感单车等多类型课程可选。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '📊', 'title' => __( '体测与营养指导', 'developer-starter' ), 'desc' => __( '结合饮食建议与训练计划，提升执行效果。', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'curriculum',
                'data' => array(
                    'curriculum_title'             => __( '本周课程安排', 'developer-starter' ),
                    'curriculum_subtitle'          => __( '可根据门店时段和教练排班灵活调整', 'developer-starter' ),
                    'curriculum_primary_color'     => '#dc2626',
                    'curriculum_bg_color'          => '#f8fafc',
                    'curriculum_items'             => array(
                        array( 'title' => __( '晨间燃脂训练营', 'developer-starter' ), 'meta' => __( '周一至周五 07:00-08:00', 'developer-starter' ), 'content' => __( '<p>适合通勤前训练，提升心肺与基础代谢。</p>', 'developer-starter' ), 'open' => 'yes' ),
                        array( 'title' => __( '下班后力量提升课', 'developer-starter' ), 'meta' => __( '周一/周三/周五 19:00-20:00', 'developer-starter' ), 'content' => __( '<p>以复合动作为主，强化力量与体态稳定性。</p>', 'developer-starter' ), 'open' => 'no' ),
                        array( 'title' => __( '周末团课联训', 'developer-starter' ), 'meta' => __( '周六/周日 10:00-12:00', 'developer-starter' ), 'content' => __( '<p>搏击+HIIT 组合课程，提高训练趣味和坚持率。</p>', 'developer-starter' ), 'open' => 'no' ),
                    ),
                    'enable_staggered_animation'   => 'yes',
                ),
            ),
            array(
                'type' => 'team',
                'data' => array(
                    'team_title'                => __( '明星教练团队', 'developer-starter' ),
                    'team_subtitle'             => __( '按目标分配教练，训练过程可持续复盘', 'developer-starter' ),
                    'team_columns'              => '3',
                    'team_members'              => array(
                        array( 'avatar' => '', 'name' => __( '张教练', 'developer-starter' ), 'position' => __( '体能训练教练', 'developer-starter' ), 'desc' => __( '专注减脂与体能提升，擅长新手阶段计划制定。', 'developer-starter' ), 'wechat' => '', 'email' => 'coach1@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '刘教练', 'developer-starter' ), 'position' => __( '力量训练教练', 'developer-starter' ), 'desc' => __( '深耕增肌与力量训练，注重动作细节与进阶安全。', 'developer-starter' ), 'wechat' => '', 'email' => 'coach2@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '王教练', 'developer-starter' ), 'position' => __( '团课教练', 'developer-starter' ), 'desc' => __( '擅长团课氛围管理，提升会员训练参与度。', 'developer-starter' ), 'wechat' => '', 'email' => 'coach3@example.com', 'phone' => '' ),
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
                    'pricing_title'              => __( '会员方案', 'developer-starter' ),
                    'pricing_subtitle'           => __( '按训练频次选择套餐，支持体验后升级', 'developer-starter' ),
                    'pricing_columns'            => '3',
                    'module_bg_type'             => 'color',
                    'module_bg_color'            => '#f8fafc',
                    'module_padding_top'         => '80px',
                    'module_padding_bottom'      => '80px',
                    'pricing_items'              => array(
                        array(
                            'name'          => __( '基础月卡', 'developer-starter' ),
                            'price'         => '¥399',
                            'period'        => __( '/月', 'developer-starter' ),
                            'desc'          => __( '适合每周训练 2-3 次', 'developer-starter' ),
                            'features'      => __( "✓ 基础器械区使用\n✓ 团课预约\n✓ 体测一次\n✗ 私教课程", 'developer-starter' ),
                            'btn_text'      => __( '立即开卡', 'developer-starter' ),
                            'btn_link'      => '#',
                            'card_bg'       => '#ffffff',
                            'featured'      => '',
                        ),
                        array(
                            'name'          => __( '进阶季卡', 'developer-starter' ),
                            'price'         => '¥999',
                            'period'        => __( '/季', 'developer-starter' ),
                            'desc'          => __( '适合稳定训练与目标管理', 'developer-starter' ),
                            'features'      => __( "✓ 全部基础权益\n✓ 赠送私教 2 节\n✓ 饮食建议\n✓ 阶段复盘", 'developer-starter' ),
                            'btn_text'      => __( '立即开卡', 'developer-starter' ),
                            'btn_link'      => '#',
                            'card_bg'       => '#ffffff',
                            'featured'      => '1',
                            'featured_text' => __( '推荐', 'developer-starter' ),
                            'featured_bg'   => '#dc2626',
                        ),
                        array(
                            'name'          => __( '私教年卡', 'developer-starter' ),
                            'price'         => '¥6999',
                            'period'        => __( '/年', 'developer-starter' ),
                            'desc'          => __( '适合明确增肌/减脂目标用户', 'developer-starter' ),
                            'features'      => __( "✓ 全部器械和团课权益\n✓ 私教定制计划\n✓ 每月体测\n✓ 1v1 饮食跟踪", 'developer-starter' ),
                            'btn_text'      => __( '咨询顾问', 'developer-starter' ),
                            'btn_link'      => '#',
                            'card_bg'       => '#ffffff',
                            'featured'      => '',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '会员反馈', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实训练效果与服务体验评价', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '4,200+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array( 'avatar' => '', 'name' => __( '黄女士', 'developer-starter' ), 'position' => __( '减脂会员', 'developer-starter' ), 'content' => __( '3个月体脂下降明显，教练督导非常到位。', 'developer-starter' ), 'rating' => '5', 'source' => 'dianping', 'date' => '2026-02-12', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '郑先生', 'developer-starter' ), 'position' => __( '力量训练会员', 'developer-starter' ), 'content' => __( '动作纠正很细，训练后恢复建议也很实用。', 'developer-starter' ), 'rating' => '5', 'source' => 'meituan', 'date' => '2026-03-04', 'verified' => 'vip', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '杨小姐', 'developer-starter' ), 'position' => __( '团课会员', 'developer-starter' ), 'content' => __( '团课氛围好，排课稳定，打卡坚持更容易。', 'developer-starter' ), 'rating' => '5', 'source' => 'xiaohongshu', 'date' => '2026-03-15', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'            => __( '预约免费体验课', 'developer-starter' ),
                    'booking_subtitle'         => __( '提交后由顾问确认时段，当前不接入在线支付。', 'developer-starter' ),
                    'form_id'                  => '',
                    'booking_layout'           => 'sidebar',
                    'sidebar_title'            => __( '体验课说明', 'developer-starter' ),
                    'sidebar_content'          => __( "请备注你的训练目标\n可选择意向课程时段\n首次到店建议提前 10 分钟\n携带运动鞋和水杯", 'developer-starter' ),
                    'contact_phone'            => '400-889-2233',
                    'contact_hours'            => __( '营业时间 07:00-23:00', 'developer-starter' ),
                    'module_bg_color'          => '#f8fafc',
                    'module_padding_top'       => '80px',
                    'module_padding_bottom'    => '80px',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '体验与入会常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '先了解规则，再安排体验和课程', 'developer-starter' ),
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array( 'question' => __( '体验课需要收费吗？', 'developer-starter' ), 'answer' => __( '新用户可预约免费体验课，具体时段以门店安排为准。', 'developer-starter' ) ),
                        array( 'question' => __( '入会后可以暂停吗？', 'developer-starter' ), 'answer' => __( '支持按会籍规则申请暂停，详细政策可咨询门店顾问。', 'developer-starter' ) ),
                        array( 'question' => __( '可以只买私教课吗？', 'developer-starter' ), 'answer' => __( '支持私教课包，建议先体测后制定训练方案。', 'developer-starter' ) ),
                        array( 'question' => __( '团课如何预约？', 'developer-starter' ), 'answer' => __( '可在门店前台或会员渠道按周预约，热门时段建议提前锁定。', 'developer-starter' ) ),
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '准备开始你的训练计划？', 'developer-starter' ),
                    'cta_subtitle'          => __( '先预约体验课，再由教练给出个性化建议。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即预约体验', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #111827 0%, #dc2626 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
