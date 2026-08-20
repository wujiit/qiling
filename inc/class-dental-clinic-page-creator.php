<?php
/**
 * 口腔诊所官网页创建器类
 *
 * 当用户选择"口腔诊所官网（预约版）"模板创建页面时，自动填充预设模块内容
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
 * 口腔诊所官网页创建器类
 */
class Dental_Clinic_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-dental-clinic.php';
    protected const AJAX_ACTION = 'fill_dental_clinic_modules';
    protected const FILLED_META_KEY = '_dental_clinic_modules_filled';

    /**
     * 获取口腔诊所官网页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '口腔诊所官网（预约版）', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：首屏
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'       => 'slider',
                    'banner_height'       => 'large',
                    'banner_bg_color'     => 'linear-gradient(135deg, #0f172a 0%, #2563eb 100%)',
                    'banner_slides'       => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '正畸、种植、修复与儿童齿科一站式诊疗，支持在线预约初诊。', 'developer-starter' ),
                            'btn_text'       => __( '预约初诊', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#1d4ed8',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '数字化诊疗流程', 'developer-starter' ),
                            'subtitle'       => __( '透明沟通方案与费用，帮助患者更安心地完成诊疗决策。', 'developer-starter' ),
                            'btn_text'       => __( '查看诊疗项目', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#1d4ed8',
                        ),
                    ),
                    'show_stats_bar'      => '1',
                    'stats_data'          => array(
                        array(
                            'icon'   => '🦷',
                            'number' => '10年+',
                            'label'  => __( '临床经验', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '👨‍⚕️',
                            'number' => '20+',
                            'label'  => __( '执业医生', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '⭐',
                            'number' => '4.9',
                            'label'  => __( '患者评分', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '📅',
                            'number' => '当天',
                            'label'  => __( '可预约时段', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                    ),
                    'banner_wave_enable'  => '0',
                ),
            ),

            // 模块2：诊疗项目
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '口腔诊疗项目', 'developer-starter' ),
                    'services_subtitle'         => __( '覆盖常见口腔问题，提供分阶段治疗与复诊跟踪', 'developer-starter' ),
                    'services_bg_color'         => '#ffffff',
                    'services_padding'          => 'normal',
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array(
                            'icon'  => '🦷',
                            'title' => __( '种植修复', 'developer-starter' ),
                            'desc'  => __( '数字化种植评估与修复，兼顾功能与美观。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '😁',
                            'title' => __( '牙齿正畸', 'developer-starter' ),
                            'desc'  => __( '金属托槽/隐形矫正方案，按阶段复诊调整。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🧒',
                            'title' => __( '儿童齿科', 'developer-starter' ),
                            'desc'  => __( '涂氟、窝沟封闭与早期干预，关注成长发育。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🛡️',
                            'title' => __( '牙周治疗', 'developer-starter' ),
                            'desc'  => __( '基础治疗与维护计划，降低炎症复发风险。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块3：医师团队
            array(
                'type' => 'team',
                'data' => array(
                    'team_title'               => __( '核心医师团队', 'developer-starter' ),
                    'team_subtitle'            => __( '持证执业，分科协作，保障诊疗质量与体验', 'developer-starter' ),
                    'team_columns'             => '3',
                    'team_members'             => array(
                        array(
                            'avatar'   => '',
                            'name'     => __( '王主任', 'developer-starter' ),
                            'position' => __( '种植中心负责人', 'developer-starter' ),
                            'desc'     => __( '专注复杂缺牙修复与种植方案设计，临床经验丰富。', 'developer-starter' ),
                            'wechat'   => '',
                            'email'    => 'doctor1@example.com',
                            'phone'    => '',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '赵医生', 'developer-starter' ),
                            'position' => __( '正畸医生', 'developer-starter' ),
                            'desc'     => __( '擅长青少年与成人正畸，提供阶段化复诊管理。', 'developer-starter' ),
                            'wechat'   => '',
                            'email'    => 'doctor2@example.com',
                            'phone'    => '',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '陈医生', 'developer-starter' ),
                            'position' => __( '儿童齿科医生', 'developer-starter' ),
                            'desc'     => __( '关注儿童口腔发育，强调舒适化就诊与家长沟通。', 'developer-starter' ),
                            'wechat'   => '',
                            'email'    => 'doctor3@example.com',
                            'phone'    => '',
                        ),
                    ),
                    'module_bg_type'           => 'color',
                    'module_bg_color'          => '#f8fafc',
                    'module_padding_top'       => '80px',
                    'module_padding_bottom'    => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块4：价目参考
            array(
                'type' => 'menu',
                'data' => array(
                    'menu_title'        => __( '诊疗<span style="color:#2563eb">价目参考</span>', 'developer-starter' ),
                    'menu_subtitle'     => __( '以下为常见项目参考区间，具体以面诊评估为准', 'developer-starter' ),
                    'menu_layout'       => 'grid',
                    'menu_accent_color' => '#2563eb',
                    'menu_items'        => array(
                        array(
                            'image' => '',
                            'title' => __( '洁牙与口腔检查', 'developer-starter' ),
                            'desc'  => __( '含基础检查与洁治建议', 'developer-starter' ),
                            'price' => '¥188',
                            'badge' => __( '基础项目', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => __( '儿童窝沟封闭', 'developer-starter' ),
                            'desc'  => __( '按牙位计费，含口腔评估', 'developer-starter' ),
                            'price' => '¥320起',
                            'badge' => __( '儿牙推荐', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => __( '隐形矫正方案评估', 'developer-starter' ),
                            'desc'  => __( '含初诊拍片与方案沟通', 'developer-starter' ),
                            'price' => '¥1200起',
                            'badge' => __( '热门', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => __( '单颗种植修复', 'developer-starter' ),
                            'desc'  => __( '依据骨量与修复材料评估', 'developer-starter' ),
                            'price' => '¥6800起',
                            'badge' => __( '需面诊', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块5：案例展示
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'             => __( '矫正与修复案例', 'developer-starter' ),
                    'cases_count'             => '6',
                    'cases_columns'           => '3',
                    'cases_show_image'        => '1',
                    'cases_image_height'      => '220px',
                    'cases_padding_top'       => '80px',
                    'cases_padding_bottom'    => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块6：患者评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '患者反馈', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实就诊评价，帮助新患者建立信任', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '6,800+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'      => '',
                            'name'        => __( '刘女士', 'developer-starter' ),
                            'position'    => __( '正畸患者', 'developer-starter' ),
                            'content'     => __( '面诊讲解很细致，复诊安排清楚，整个过程很安心。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'dianping',
                            'date'        => '2026-02-25',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '郑先生', 'developer-starter' ),
                            'position'    => __( '种植患者', 'developer-starter' ),
                            'content'     => __( '方案透明，医生会解释每一步，术后跟进也很及时。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'meituan',
                            'date'        => '2026-03-06',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '孙女士', 'developer-starter' ),
                            'position'    => __( '儿童齿科家长', 'developer-starter' ),
                            'content'     => __( '医生和护士对孩子很耐心，孩子配合度明显提升。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'xiaohongshu',
                            'date'        => '2026-03-14',
                            'verified'    => 'guest',
                            'card_bg'     => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块7：预约入口
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'           => __( '预约初诊', 'developer-starter' ),
                    'booking_subtitle'        => __( '提交后由诊所客服回访确认时间，当前不接入在线支付。', 'developer-starter' ),
                    'form_id'                 => '',
                    'booking_layout'          => 'sidebar',
                    'sidebar_title'           => __( '就诊须知', 'developer-starter' ),
                    'sidebar_content'         => __( "请尽量填写症状与既往史\n可上传既往检查结果说明\n初诊建议提前 10 分钟到院\n夜间门诊请提前预约", 'developer-starter' ),
                    'contact_phone'           => '400-820-3699',
                    'contact_hours'           => __( '门诊时间 09:00-20:00', 'developer-starter' ),
                    'module_bg_color'         => '#f8fafc',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                ),
            ),

            // 模块8：FAQ
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '就诊常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '提前解答高频疑问，减少来回沟通成本', 'developer-starter' ),
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array(
                            'question' => __( '首次就诊需要准备什么？', 'developer-starter' ),
                            'answer'   => __( '建议携带身份证件、既往检查资料与用药信息，便于医生评估。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '线上预约后多久确认？', 'developer-starter' ),
                            'answer'   => __( '通常在工作时段 15-30 分钟内由客服电话或短信确认。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '是否支持分期或医保咨询？', 'developer-starter' ),
                            'answer'   => __( '可在面诊时了解适用政策与支付方式，具体以门店说明为准。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '儿童看牙可以家长陪同吗？', 'developer-starter' ),
                            'answer'   => __( '可以，建议由固定监护人陪同，便于病史沟通和后续护理指导。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块9：行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '需要医生评估方案？', 'developer-starter' ),
                    'cta_subtitle'          => __( '立即提交初诊预约，获取适合你的诊疗建议与时间安排。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即预约初诊', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0f172a 0%, #2563eb 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
