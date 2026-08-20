<?php
/**
 * 汽车4S/汽修官网页创建器类
 *
 * 当用户选择"汽车4S/汽修官网页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auto_Service_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-auto-service.php';
    protected const AJAX_ACTION = 'fill_auto_service_modules';
    protected const FILLED_META_KEY = '_auto_service_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '汽车4S/汽修官网页', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'      => 'slider',
                    'banner_height'      => 'large',
                    'banner_bg_color'    => 'linear-gradient(135deg, #111827 0%, #1d4ed8 100%)',
                    'banner_slides'      => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '新车展示、维修保养、事故钣喷与道路救援一体化服务。', 'developer-starter' ),
                            'btn_text'       => __( '预约到店', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#1d4ed8',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '透明报价，标准流程', 'developer-starter' ),
                            'subtitle'       => __( '车辆检测、维修方案和进度节点全程可视化。', 'developer-starter' ),
                            'btn_text'       => __( '查看服务项目', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#1d4ed8',
                        ),
                    ),
                    'show_stats_bar'     => '1',
                    'stats_data'         => array(
                        array( 'icon' => '🚗', 'number' => '5000+', 'label' => __( '服务车主', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '🔧', 'number' => '40+', 'label' => __( '认证技师', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '🏪', 'number' => '12家', 'label' => __( '直营网点', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '⭐', 'number' => '4.8', 'label' => __( '车主评分', 'developer-starter' ), 'color' => '#ffffff' ),
                    ),
                    'banner_wave_enable' => '0',
                ),
            ),
            array(
                'type' => 'products',
                'data' => array(
                    'products_title'       => __( '热门车型与服务包', 'developer-starter' ),
                    'products_subtitle'    => __( 'HOT PRODUCTS', 'developer-starter' ),
                    'columns'              => '4',
                    'modal_inquire_text'   => __( '预约咨询', 'developer-starter' ),
                    'modal_inquire_url'    => '#',
                    'items'                => array(
                        array(
                            'image'    => '',
                            'title'    => __( '城市SUV 2026款', 'developer-starter' ),
                            'desc'     => __( '智能辅助驾驶 + 高效混动', 'developer-starter' ),
                            'specs'    => __( "排量：2.0T\n变速箱：8AT\n质保：3年/10万公里", 'developer-starter' ),
                            'post_id'  => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '年度保养套餐', 'developer-starter' ),
                            'desc'     => __( '基础保养 + 全车检测', 'developer-starter' ),
                            'specs'    => __( "机油机滤更换\n刹车系统检查\n空调滤芯检测", 'developer-starter' ),
                            'post_id'  => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '事故钣喷修复', 'developer-starter' ),
                            'desc'     => __( '快速评估 + 原厂工艺', 'developer-starter' ),
                            'specs'    => __( "钣金修复\n车漆匹配\n交付质检", 'developer-starter' ),
                            'post_id'  => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '道路救援服务', 'developer-starter' ),
                            'desc'     => __( '拖车 + 应急搭电 + 轮胎支持', 'developer-starter' ),
                            'specs'    => __( "24小时接入\n同城就近派单\n进度实时通知", 'developer-starter' ),
                            'post_id'  => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '门店服务项目', 'developer-starter' ),
                    'services_subtitle'         => __( '从新车到售后，覆盖车主核心用车场景', 'developer-starter' ),
                    'services_bg_color'         => '#ffffff',
                    'services_padding'          => 'normal',
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array( 'icon' => '🚘', 'title' => __( '新车销售', 'developer-starter' ), 'desc' => __( '热门车型讲解、金融方案与试驾服务。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🛠️', 'title' => __( '维修保养', 'developer-starter' ), 'desc' => __( '标准维保流程，关键项目透明报价。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🎨', 'title' => __( '钣喷美容', 'developer-starter' ), 'desc' => __( '事故修复、局部补漆与精细美容养护。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🆘', 'title' => __( '道路救援', 'developer-starter' ), 'desc' => __( '拖车、电瓶应急和故障到场处理支持。', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'branches',
                'data' => array(
                    'branches_title'         => __( '门店网点', 'developer-starter' ),
                    'branches_subtitle'      => __( '支持城市筛选与导航到店，快速匹配最近服务点', 'developer-starter' ),
                    'branches_columns'       => '3',
                    'enable_city_filter'     => 'yes',
                    'map_provider'           => 'gaode',
                    'show_booking_button'    => 'yes',
                    'navigation_button_text' => __( '导航到店', 'developer-starter' ),
                    'booking_button_text'    => __( '预约服务', 'developer-starter' ),
                    'branches_list'          => array(
                        array(
                            'name'        => __( '上海浦东旗舰店', 'developer-starter' ),
                            'city'        => __( '上海', 'developer-starter' ),
                            'status'      => 'open',
                            'address'     => __( '上海市浦东新区XX路88号', 'developer-starter' ),
                            'phone'       => '021-69990001',
                            'email'       => 'shanghai@example.com',
                            'hours'       => __( '周一至周日 09:00-20:00', 'developer-starter' ),
                            'services'    => __( '新车销售, 维保, 钣喷', 'developer-starter' ),
                            'transport'   => __( '地铁站步行约8分钟', 'developer-starter' ),
                            'lat'         => '31.2304',
                            'lng'         => '121.4737',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                        array(
                            'name'        => __( '杭州城西服务店', 'developer-starter' ),
                            'city'        => __( '杭州', 'developer-starter' ),
                            'status'      => 'open',
                            'address'     => __( '杭州市西湖区XX路66号', 'developer-starter' ),
                            'phone'       => '0571-89990002',
                            'email'       => 'hangzhou@example.com',
                            'hours'       => __( '周一至周日 09:00-19:30', 'developer-starter' ),
                            'services'    => __( '维保, 事故快修, 保险代办', 'developer-starter' ),
                            'transport'   => __( '高架出口 5 分钟', 'developer-starter' ),
                            'lat'         => '30.2741',
                            'lng'         => '120.1551',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                        array(
                            'name'        => __( '南京江北快修中心', 'developer-starter' ),
                            'city'        => __( '南京', 'developer-starter' ),
                            'status'      => 'busy',
                            'address'     => __( '南京市浦口区XX大道99号', 'developer-starter' ),
                            'phone'       => '025-89990003',
                            'email'       => 'nanjing@example.com',
                            'hours'       => __( '周一至周日 08:30-19:00', 'developer-starter' ),
                            'services'    => __( '快修快保, 道路救援', 'developer-starter' ),
                            'transport'   => __( '园区内停车便利', 'developer-starter' ),
                            'lat'         => '32.0584',
                            'lng'         => '118.7965',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                    ),
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '车主评价', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实车主反馈，帮助你更快决策', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '7,800+',
                    'average_rating'          => '4.8',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array( 'avatar' => '', 'name' => __( '赵先生', 'developer-starter' ), 'position' => __( '保养车主', 'developer-starter' ), 'content' => __( '报价透明，流程规范，保养过程看得见。', 'developer-starter' ), 'rating' => '5', 'source' => 'dianping', 'date' => '2026-02-20', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '陈女士', 'developer-starter' ), 'position' => __( '事故维修车主', 'developer-starter' ), 'content' => __( '事故修复效率高，交车时间和预估基本一致。', 'developer-starter' ), 'rating' => '5', 'source' => 'meituan', 'date' => '2026-03-03', 'verified' => 'vip', 'card_bg' => '#ffffff' ),
                        array( 'avatar' => '', 'name' => __( '吴先生', 'developer-starter' ), 'position' => __( '新车客户', 'developer-starter' ), 'content' => __( '销售和售后衔接顺畅，提车后服务跟进及时。', 'developer-starter' ), 'rating' => '5', 'source' => 'xiaohongshu', 'date' => '2026-03-16', 'verified' => 'verified', 'card_bg' => '#ffffff' ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'          => __( '预约到店服务', 'developer-starter' ),
                    'booking_subtitle'       => __( '提交后由门店客服回访确认时间，当前不接入在线支付。', 'developer-starter' ),
                    'form_id'                => '',
                    'booking_layout'         => 'sidebar',
                    'sidebar_title'          => __( '预约须知', 'developer-starter' ),
                    'sidebar_content'        => __( "请备注车牌号和车型\n可填写故障现象和历史保养记录\n事故车建议上传现场照片说明\n到店前请确认联系方式畅通", 'developer-starter' ),
                    'contact_phone'          => '400-667-8899',
                    'contact_hours'          => __( '服务时间 08:30-20:30', 'developer-starter' ),
                    'module_bg_color'        => '#f8fafc',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '用车服务常见问题', 'developer-starter' ),
                    'faq_subtitle'          => __( '关于保养、维修和救援服务的常见疑问', 'developer-starter' ),
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array( 'question' => __( '保养需要提前预约吗？', 'developer-starter' ), 'answer' => __( '建议提前预约，热门时段可减少排队等待时间。', 'developer-starter' ) ),
                        array( 'question' => __( '事故车能代办保险吗？', 'developer-starter' ), 'answer' => __( '支持保险理赔协助，具体流程由门店顾问一对一说明。', 'developer-starter' ) ),
                        array( 'question' => __( '道路救援覆盖哪些范围？', 'developer-starter' ), 'answer' => __( '同城范围内支持拖车和应急服务，偏远区域按实际距离评估。', 'developer-starter' ) ),
                        array( 'question' => __( '维修后有质保吗？', 'developer-starter' ), 'answer' => __( '维修项目按服务标准提供质保，详见交付单据说明。', 'developer-starter' ) ),
                    ),
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '现在预约，减少等待时间', 'developer-starter' ),
                    'cta_subtitle'          => __( '在线提交需求，门店顾问会尽快确认你的到店安排。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即预约到店', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #111827 0%, #1d4ed8 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
