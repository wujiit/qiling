<?php
/**
 * 连锁门店官网页创建器类
 *
 * 当用户选择"连锁门店官网页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.5
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 连锁门店官网页创建器类
 */
class Chain_Store_Official_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-chain-store-official.php';
    protected const AJAX_ACTION = 'fill_chain_store_official_modules';
    protected const FILLED_META_KEY = '_chain_store_official_modules_filled';

    /**
     * 获取连锁门店官网页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '连锁门店官网页', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：品牌首屏
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'         => 'slider',
                    'banner_height'         => 'large',
                    'banner_bg_color'       => 'linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%)',
                    'banner_slides'         => array(
                        array(
                            'media_type'      => 'image',
                            'image'           => '',
                            'title'           => $page_title,
                            'subtitle'        => __( '覆盖全国核心城市，提供标准化服务与本地化门店体验。', 'developer-starter' ),
                            'btn_text'        => __( '查看附近门店', 'developer-starter' ),
                            'btn_url'         => '#',
                            'btn_bg_color'    => '#ffffff',
                            'btn_text_color'  => '#1d4ed8',
                        ),
                        array(
                            'media_type'      => 'image',
                            'image'           => '',
                            'title'           => __( '同城极速响应', 'developer-starter' ),
                            'subtitle'        => __( '统一服务标准，门店就近接待，让咨询与到店更高效。', 'developer-starter' ),
                            'btn_text'        => __( '立即预约到店', 'developer-starter' ),
                            'btn_url'         => '#',
                            'btn_bg_color'    => '#ffffff',
                            'btn_text_color'  => '#1d4ed8',
                        ),
                        array(
                            'media_type'      => 'image',
                            'image'           => '',
                            'title'           => __( '会员体系互通', 'developer-starter' ),
                            'subtitle'        => __( '多门店数据互联，积分权益与服务记录全程可追踪。', 'developer-starter' ),
                            'btn_text'        => __( '了解会员权益', 'developer-starter' ),
                            'btn_url'         => '#',
                            'btn_bg_color'    => '#ffffff',
                            'btn_text_color'  => '#1d4ed8',
                        ),
                    ),
                    'show_stats_bar'        => '1',
                    'stats_data'            => array(
                        array(
                            'icon'   => '🏪',
                            'number' => '128',
                            'label'  => __( '全国门店', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '👥',
                            'number' => '50万+',
                            'label'  => __( '会员用户', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '⭐',
                            'number' => '4.9',
                            'label'  => __( '综合评分', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '⚡',
                            'number' => '30分钟',
                            'label'  => __( '平均响应', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                    ),
                    'banner_wave_enable'    => '0',
                ),
            ),

            // 模块2：核心数据
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_title'             => __( '连锁运营关键数据', 'developer-starter' ),
                    'stats_subtitle'          => __( '标准化运营 + 本地化服务，持续提升到店与复购表现', 'developer-starter' ),
                    'stats_text_align'        => 'center',
                    'stats_items'             => array(
                        array(
                            'number' => '95%',
                            'label'  => __( '门店标准执行率', 'developer-starter' ),
                        ),
                        array(
                            'number' => '82%',
                            'label'  => __( '老客复购率', 'developer-starter' ),
                        ),
                        array(
                            'number' => '24h',
                            'label'  => __( '售后闭环时效', 'developer-starter' ),
                        ),
                        array(
                            'number' => '200+',
                            'label'  => __( '城市服务网络', 'developer-starter' ),
                        ),
                    ),
                    'stats_bg_type'           => 'color',
                    'stats_bg_color'          => '#f8fafc',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块3：服务展示
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '门店核心服务', 'developer-starter' ),
                    'services_subtitle'         => __( '围绕到店咨询、履约交付和售后维护的全链路服务', 'developer-starter' ),
                    'services_bg_color'         => '#ffffff',
                    'services_padding'          => 'normal',
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array(
                            'icon'  => '🧭',
                            'title' => __( '到店咨询', 'developer-starter' ),
                            'desc'  => __( '门店顾问一对一需求沟通，快速匹配合适方案。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🛠️',
                            'title' => __( '标准履约', 'developer-starter' ),
                            'desc'  => __( '统一SOP执行，保障服务质量与交付效率。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🚚',
                            'title' => __( '同城支持', 'developer-starter' ),
                            'desc'  => __( '就近门店快速响应，减少等待时间。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🔁',
                            'title' => __( '会员售后', 'developer-starter' ),
                            'desc'  => __( '门店联动售后体系，服务记录全程可查。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块4：门店网络
            array(
                'type' => 'branches',
                'data' => array(
                    'branches_title'          => __( '门店网络覆盖', 'developer-starter' ),
                    'branches_subtitle'       => __( '支持城市筛选、导航到店与在线预约，一页呈现全国网点', 'developer-starter' ),
                    'branches_columns'        => '3',
                    'enable_city_filter'      => 'yes',
                    'map_provider'            => 'gaode',
                    'show_booking_button'     => 'yes',
                    'navigation_button_text'  => __( '导航到店', 'developer-starter' ),
                    'booking_button_text'     => __( '预约到店', 'developer-starter' ),
                    'branches_list'           => array(
                        array(
                            'name'        => __( '上海徐汇旗舰店', 'developer-starter' ),
                            'city'        => __( '上海', 'developer-starter' ),
                            'status'      => 'open',
                            'address'     => __( '上海市徐汇区漕溪北路88号', 'developer-starter' ),
                            'phone'       => '021-68886666',
                            'email'       => 'shanghai@example.com',
                            'hours'       => __( '周一至周日 10:00-22:00', 'developer-starter' ),
                            'services'    => __( '新品体验, 会员服务, 售后受理', 'developer-starter' ),
                            'transport'   => __( '地铁1/9号线徐家汇站步行5分钟', 'developer-starter' ),
                            'lat'         => '31.2002',
                            'lng'         => '121.4379',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                        array(
                            'name'        => __( '北京朝阳中心店', 'developer-starter' ),
                            'city'        => __( '北京', 'developer-starter' ),
                            'status'      => 'open',
                            'address'     => __( '北京市朝阳区建国路99号', 'developer-starter' ),
                            'phone'       => '010-89996666',
                            'email'       => 'beijing@example.com',
                            'hours'       => __( '周一至周日 09:30-21:30', 'developer-starter' ),
                            'services'    => __( '顾问咨询, 企业团购, 配送安装', 'developer-starter' ),
                            'transport'   => __( '地铁1号线国贸站B口步行8分钟', 'developer-starter' ),
                            'lat'         => '39.9086',
                            'lng'         => '116.4618',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                        array(
                            'name'        => __( '深圳南山科技园店', 'developer-starter' ),
                            'city'        => __( '深圳', 'developer-starter' ),
                            'status'      => 'busy',
                            'address'     => __( '深圳市南山区科苑路15号', 'developer-starter' ),
                            'phone'       => '0755-83336666',
                            'email'       => 'shenzhen@example.com',
                            'hours'       => __( '周一至周日 10:00-21:00', 'developer-starter' ),
                            'services'    => __( '门店零售, 到店试用, 售后快修', 'developer-starter' ),
                            'transport'   => __( '地铁1号线高新园站A口步行6分钟', 'developer-starter' ),
                            'lat'         => '22.5413',
                            'lng'         => '113.9565',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                        array(
                            'name'        => __( '杭州滨江体验店', 'developer-starter' ),
                            'city'        => __( '杭州', 'developer-starter' ),
                            'status'      => 'coming',
                            'address'     => __( '杭州市滨江区江南大道66号', 'developer-starter' ),
                            'phone'       => '0571-86668888',
                            'email'       => 'hangzhou@example.com',
                            'hours'       => __( '预计下月开业', 'developer-starter' ),
                            'services'    => __( '新品首发, 会员活动, 咨询服务', 'developer-starter' ),
                            'transport'   => __( '地铁6号线江汉路站附近', 'developer-starter' ),
                            'lat'         => '30.2066',
                            'lng'         => '120.2108',
                            'map_url'     => '',
                            'booking_url' => '#',
                            'image'       => '',
                        ),
                    ),
                    'module_bg_type'         => 'color',
                    'module_bg_color'        => '#f8fafc',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),

            // 模块5：客户评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '用户评价', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '来自真实到店用户的服务反馈', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '18,000+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'      => '',
                            'name'        => __( '李雯', 'developer-starter' ),
                            'position'    => __( '会员用户', 'developer-starter' ),
                            'content'     => __( '跨城出差也能在当地门店享受同样标准服务，体验一致性很好。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'dianping',
                            'date'        => '2026-02-18',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '周晨', 'developer-starter' ),
                            'position'    => __( '企业采购负责人', 'developer-starter' ),
                            'content'     => __( '门店顾问很专业，批量采购流程清晰，交付效率高。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'google',
                            'date'        => '2026-03-03',
                            'verified'    => 'vip',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '王涛', 'developer-starter' ),
                            'position'    => __( '到店客户', 'developer-starter' ),
                            'content'     => __( '线上预约后到店基本不用排队，服务流程很顺。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'meituan',
                            'date'        => '2026-03-12',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块6：常见问题
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '门店服务常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '关于预约、售后、跨城服务和会员权益', 'developer-starter' ),
                    'module_bg_color'        => '#f8fafc',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array(
                            'question' => __( '是否支持跨城门店服务？', 'developer-starter' ),
                            'answer'   => __( '支持。会员可在全国联网门店享受标准化服务，具体以当地门店排班为准。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '如何预约到店？', 'developer-starter' ),
                            'answer'   => __( '可在页面提交预约信息，或拨打门店电话，工作人员会在营业时段内尽快确认。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '售后服务由哪个门店负责？', 'developer-starter' ),
                            'answer'   => __( '默认由最近门店承接售后，也可根据你的历史服务记录协同原门店处理。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '企业客户是否支持统一结算？', 'developer-starter' ),
                            'answer'   => __( '支持。企业客户可申请对公签约与统一结算方案，由专属顾问跟进。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块7：联系我们
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '预约到店 / 商务合作', 'developer-starter' ),
                    'contact_subtitle'       => __( '提交需求后，门店顾问将与您联系并确认服务安排。', 'developer-starter' ),
                    'contact_show_form'      => '1',
                    'contact_image'          => '',
                    'module_bg_type'         => 'color',
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),

            // 模块8：行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '选择就近门店，立即开启高效服务体验', 'developer-starter' ),
                    'cta_subtitle'          => __( '支持线上预约、电话咨询与企业合作对接。', 'developer-starter' ),
                    'cta_button_text'       => __( '马上预约', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
