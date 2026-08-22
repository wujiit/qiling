<?php
/**
 * 美容美发美甲官网页创建器类
 *
 * 当用户选择"美容美发美甲官网（预约版）"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 美容美发美甲官网页创建器类
 */
class Beauty_Salon_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-beauty-salon.php';
    protected const AJAX_ACTION = 'fill_beauty_salon_modules';
    protected const FILLED_META_KEY = '_beauty_salon_modules_filled';

    /**
     * 获取美容美发美甲官网页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '美容美发美甲官网（预约版）', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'      => 'slider',
                    'banner_height'      => 'large',
                    'banner_bg_color'    => 'linear-gradient(135deg, #831843 0%, #c026d3 100%)',
                    'banner_slides'      => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '美容护理、发型设计、美甲美睫一站式服务，支持在线预约到店。', 'developer-starter' ),
                            'btn_text'       => __( '预约到店', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#831843',
                        ),
                    ),
                    'show_stats_bar'     => '1',
                    'stats_data'         => array(
                        array( 'icon' => '💇', 'number' => '30+', 'label' => __( '服务项目', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '💅', 'number' => '20+', 'label' => __( '资深造型师', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '🏪', 'number' => '8家', 'label' => __( '城市门店', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '⭐', 'number' => '4.9', 'label' => __( '平台评分', 'developer-starter' ), 'color' => '#ffffff' ),
                    ),
                    'banner_wave_enable' => '0',
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '热门服务项目', 'developer-starter' ),
                    'services_subtitle'         => __( '覆盖日常护理、造型设计与节日焕新场景', 'developer-starter' ),
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array( 'icon' => '✨', 'title' => __( '面部护理', 'developer-starter' ), 'desc' => __( '清洁补水、舒缓修护与肌肤管理方案。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '💇‍♀️', 'title' => __( '发型设计', 'developer-starter' ), 'desc' => __( '剪发、染烫、造型定制与日常打理建议。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '💅', 'title' => __( '美甲美睫', 'developer-starter' ), 'desc' => __( '款式设计、甲面护理与睫毛精细化服务。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🎉', 'title' => __( '节日焕新', 'developer-starter' ), 'desc' => __( '妆造搭配与形象焕新组合服务。', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'menu',
                'data' => array(
                    'menu_title'               => __( '人气<span style="color:#db2777">价目参考</span>', 'developer-starter' ),
                    'menu_subtitle'            => __( '可按门店等级、造型师级别和活动档期自由调整', 'developer-starter' ),
                    'menu_layout'              => 'grid',
                    'menu_accent_color'        => '#db2777',
                    'menu_items'               => array(
                        array( 'image' => '', 'title' => __( '基础洗剪吹', 'developer-starter' ), 'desc' => __( '含头皮清洁与发型打理建议', 'developer-starter' ), 'price' => '¥128', 'badge' => __( '日常热门', 'developer-starter' ), 'link' => '#' ),
                        array( 'image' => '', 'title' => __( '染发护理套餐', 'developer-starter' ), 'desc' => __( '发色定制 + 护理修护', 'developer-starter' ), 'price' => '¥598起', 'badge' => __( '形象升级', 'developer-starter' ), 'link' => '#' ),
                        array( 'image' => '', 'title' => __( '轻奢美甲套餐', 'developer-starter' ), 'desc' => __( '款式设计 + 手部护理', 'developer-starter' ), 'price' => '¥298起', 'badge' => __( '节日推荐', 'developer-starter' ), 'link' => '#' ),
                        array( 'image' => '', 'title' => __( '面部深层护理', 'developer-starter' ), 'desc' => __( '清洁补水 + 舒缓导入', 'developer-starter' ), 'price' => '¥368', 'badge' => __( '回购高', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'team',
                'data' => array(
                    'team_title'                => __( '明星造型团队', 'developer-starter' ),
                    'team_subtitle'             => __( '按风格和需求匹配造型师，提升到店体验与复购率', 'developer-starter' ),
                    'team_columns'              => '3',
                    'team_members'              => array(
                        array( 'avatar' => '', 'name' => __( 'Mia', 'developer-starter' ), 'position' => __( '发型总监', 'developer-starter' ), 'desc' => __( '擅长轻奢染烫与脸型修饰设计。', 'developer-starter' ), 'wechat' => '', 'email' => 'beauty1@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( 'Luna', 'developer-starter' ), 'position' => __( '护肤顾问', 'developer-starter' ), 'desc' => __( '专注敏感肌舒缓与周期护理管理。', 'developer-starter' ), 'wechat' => '', 'email' => 'beauty2@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( 'Yuki', 'developer-starter' ), 'position' => __( '美甲主理人', 'developer-starter' ), 'desc' => __( '擅长节日和婚礼场景款式设计。', 'developer-starter' ), 'wechat' => '', 'email' => 'beauty3@example.com', 'phone' => '' ),
                    ),
                    'module_bg_type'            => 'color',
                    'module_bg_color'           => '#fdf2f8',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '到店评价', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实用户体验，帮助新客快速建立信任', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '9,500+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array( 'avatar' => '', 'name' => __( '周女士', 'developer-starter' ), 'position' => __( '染发用户', 'developer-starter' ), 'content' => __( '发色设计很贴合气质，后续打理建议也很实用。', 'developer-starter' ), 'rating' => '5', 'source' => 'dianping', 'date' => '2026-02-22', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '唐小姐', 'developer-starter' ), 'position' => __( '美甲用户', 'developer-starter' ), 'content' => __( '款式还原度高，做完细节很精致，维持时间也长。', 'developer-starter' ), 'rating' => '5', 'source' => 'meituan', 'date' => '2026-03-01', 'verified' => 'vip', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '何女士', 'developer-starter' ), 'position' => __( '护理用户', 'developer-starter' ), 'content' => __( '店内流程很顺畅，护理后皮肤状态稳定很多。', 'developer-starter' ), 'rating' => '5', 'source' => 'xiaohongshu', 'date' => '2026-03-12', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                    ),
                ),
            ),
            array(
                'type' => 'branches',
                'data' => array(
                    'branches_title'         => __( '门店网点', 'developer-starter' ),
                    'branches_subtitle'      => __( '就近预约到店，支持导航和顾问回访确认', 'developer-starter' ),
                    'branches_columns'       => '3',
                    'enable_city_filter'     => 'yes',
                    'map_provider'           => 'gaode',
                    'show_booking_button'    => 'yes',
                    'navigation_button_text' => __( '导航到店', 'developer-starter' ),
                    'booking_button_text'    => __( '立即预约', 'developer-starter' ),
                    'branches_list'          => array(
                        array(
                            'name'        => __( '上海静安旗舰店', 'developer-starter' ),
                            'city'        => __( '上海', 'developer-starter' ),
                            'status'      => 'open',
                            'address'     => __( '上海市静安区XX路108号', 'developer-starter' ),
                            'phone'       => '021-62226688',
                            'email'       => 'shanghai-beauty@example.com',
                            'hours'       => __( '周一至周日 10:00-21:30', 'developer-starter' ),
                            'services'    => __( '美容, 美发, 美甲', 'developer-starter' ),
                            'transport'   => __( '地铁站步行5分钟', 'developer-starter' ),
                            'lat'         => '31.2304',
                            'lng'         => '121.4737',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                        array(
                            'name'        => __( '杭州万象城店', 'developer-starter' ),
                            'city'        => __( '杭州', 'developer-starter' ),
                            'status'      => 'open',
                            'address'     => __( '杭州市上城区XX路66号', 'developer-starter' ),
                            'phone'       => '0571-86668899',
                            'email'       => 'hangzhou-beauty@example.com',
                            'hours'       => __( '周一至周日 10:00-22:00', 'developer-starter' ),
                            'services'    => __( '美发, 美甲, 皮肤护理', 'developer-starter' ),
                            'transport'   => __( '商场停车场直达', 'developer-starter' ),
                            'lat'         => '30.2741',
                            'lng'         => '120.1551',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                        array(
                            'name'        => __( '南京新街口店', 'developer-starter' ),
                            'city'        => __( '南京', 'developer-starter' ),
                            'status'      => 'busy',
                            'address'     => __( '南京市秦淮区XX广场3层', 'developer-starter' ),
                            'phone'       => '025-83336677',
                            'email'       => 'nanjing-beauty@example.com',
                            'hours'       => __( '周一至周日 10:30-21:30', 'developer-starter' ),
                            'services'    => __( '美容护理, 妆造, 美睫', 'developer-starter' ),
                            'transport'   => __( '地铁口步行3分钟', 'developer-starter' ),
                            'lat'         => '32.0603',
                            'lng'         => '118.7969',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                    ),
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#fdf2f8',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'           => __( '预约到店服务', 'developer-starter' ),
                    'booking_subtitle'        => __( '提交后由门店顾问回访确认时段和服务项目。', 'developer-starter' ),
                    'form_id'                 => '',
                    'booking_layout'          => 'sidebar',
                    'sidebar_title'           => __( '预约须知', 'developer-starter' ),
                    'sidebar_content'         => __( "请备注意向服务项目\n可上传参考图片便于沟通\n节假日建议提前预约\n到店请预留充足服务时间", 'developer-starter' ),
                    'contact_phone'           => '400-850-5566',
                    'contact_hours'           => __( '咨询时间 10:00-22:00', 'developer-starter' ),
                    'module_bg_color'         => '#fdf2f8',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '到店常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '关于预约、时长和售后规则的常见疑问', 'developer-starter' ),
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array( 'question' => __( '预约后可以改时间吗？', 'developer-starter' ), 'answer' => __( '支持改期，请尽量提前与门店顾问确认。', 'developer-starter' ) ),
                        array( 'question' => __( '染发前需要提前沟通发质吗？', 'developer-starter' ), 'answer' => __( '建议提前告知过往染烫情况，便于方案评估。', 'developer-starter' ) ),
                        array( 'question' => __( '美甲款式可以现场改吗？', 'developer-starter' ), 'answer' => __( '可在可执行范围内调整，复杂款式建议提前沟通。', 'developer-starter' ) ),
                        array( 'question' => __( '是否提供售后修补？', 'developer-starter' ), 'answer' => __( '部分项目支持售后服务，具体以门店规则为准。', 'developer-starter' ) ),
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '想做本周造型焕新？', 'developer-starter' ),
                    'cta_subtitle'          => __( '在线提交预约信息，门店顾问会尽快与您确认到店时间。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即预约到店', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #831843 0%, #c026d3 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
