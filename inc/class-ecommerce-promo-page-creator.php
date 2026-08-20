<?php
/**
 * 电商大促会场页创建器类
 *
 * 当用户选择"电商大促会场页"模板创建页面时，自动填充预设模块内容
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
 * 电商大促会场页创建器类
 */
class Ecommerce_Promo_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-ecommerce-promo.php';
    protected const AJAX_ACTION = 'fill_ecommerce_promo_modules';
    protected const FILLED_META_KEY = '_ecommerce_promo_modules_filled';

    /**
     * 获取电商大促会场页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '电商大促会场页', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：活动首屏
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'       => 'slider',
                    'banner_height'       => 'large',
                    'banner_bg_color'     => 'linear-gradient(135deg, #1e3a8a 0%, #059669 100%)',
                    'banner_slides'       => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '限时直降 + 爆款秒杀 + 满减叠加，活动期间限量放送。', 'developer-starter' ),
                            'btn_text'       => __( '立即抢购', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#dc2626',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '跨店满减，折上再减', 'developer-starter' ),
                            'subtitle'       => __( '热门品类全覆盖，提前加购，开场下单更划算。', 'developer-starter' ),
                            'btn_text'       => __( '查看优惠规则', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#dc2626',
                        ),
                    ),
                    'show_stats_bar'      => '1',
                    'stats_data'          => array(
                        array(
                            'icon'   => '🔥',
                            'number' => '1,200+',
                            'label'  => __( '活动商品', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '💥',
                            'number' => '60%',
                            'label'  => __( '最高直降', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '⚡',
                            'number' => '24h',
                            'label'  => __( '限时抢购', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '🚚',
                            'number' => '包邮',
                            'label'  => __( '指定专区', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                    ),
                    'banner_wave_enable'  => '0',
                ),
            ),

            // 模块2：促销活动
            array(
                'type' => 'promotion',
                'data' => array(
                    'ql_promo_title'            => __( '限时大促专区', 'developer-starter' ),
                    'ql_promo_subtitle'         => __( '倒计时进行中，热门商品折扣实时更新', 'developer-starter' ),
                    'ql_promo_show_countdown'   => 'yes',
                    'ql_promo_end_date'         => '2026-04-30',
                    'ql_promo_end_time'         => '23:59',
                    'ql_promo_expired_text'     => __( '本轮活动已结束', 'developer-starter' ),
                    'ql_promo_layout'           => 'cards',
                    'ql_promo_columns'          => '3',
                    'ql_promo_items'            => array(
                        array(
                            'image'          => '',
                            'name'           => __( '旗舰耳机 Pro', 'developer-starter' ),
                            'desc'           => __( '主动降噪 + 40小时续航', 'developer-starter' ),
                            'original_price' => '¥1299',
                            'sale_price'     => '¥799',
                            'badge'          => __( '限时立减500', 'developer-starter' ),
                            'badge_color'    => '#ef4444',
                            'btn_text'       => __( '立即抢购', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                        array(
                            'image'          => '',
                            'name'           => __( '智能手表 X', 'developer-starter' ),
                            'desc'           => __( '健康监测 + NFC + 长续航', 'developer-starter' ),
                            'original_price' => '¥1599',
                            'sale_price'     => '¥999',
                            'badge'          => __( '爆款5折起', 'developer-starter' ),
                            'badge_color'    => '#f97316',
                            'btn_text'       => __( '立即抢购', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                        array(
                            'image'          => '',
                            'name'           => __( '轻薄笔记本 Air', 'developer-starter' ),
                            'desc'           => __( '16GB内存 + 512GB SSD', 'developer-starter' ),
                            'original_price' => '¥5999',
                            'sale_price'     => '¥4699',
                            'badge'          => __( '券后再减300', 'developer-starter' ),
                            'badge_color'    => '#0ea5e9',
                            'btn_text'       => __( '立即抢购', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                    ),
                    'ql_promo_bg_color'         => '#fff7ed',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块3：主推爆款
            array(
                'type' => 'product_showcase',
                'data' => array(
                    'ps_bg_color'      => '#ffffff',
                    'ps_padding'       => '80',
                    'ps_layout'        => 'left',
                    'ps_media_items'   => array(
                        array(
                            'type'         => 'image',
                            'image'        => '',
                            'video_url'    => '',
                            'video_poster' => '',
                        ),
                        array(
                            'type'         => 'image',
                            'image'        => '',
                            'video_url'    => '',
                            'video_poster' => '',
                        ),
                        array(
                            'type'         => 'image',
                            'image'        => '',
                            'video_url'    => '',
                            'video_poster' => '',
                        ),
                    ),
                    'ps_media_height'  => '460px',
                    'ps_media_radius'  => '16px',
                    'ps_left_buttons'  => array(
                        array(
                            'text'   => __( '参数详情', 'developer-starter' ),
                            'url'    => '#',
                            'icon'   => '📄',
                            'target' => '_self',
                        ),
                        array(
                            'text'   => __( '用户评价', 'developer-starter' ),
                            'url'    => '#',
                            'icon'   => '⭐',
                            'target' => '_self',
                        ),
                    ),
                    'ps_title'         => __( '会场主推：旗舰套装组合', 'developer-starter' ),
                    'ps_subtitle'      => __( '限时优惠价 + 满减券叠加，库存实时减少', 'developer-starter' ),
                    'ps_show_price'    => 'yes',
                    'ps_price'         => '¥1999',
                    'ps_original_price' => '¥2999',
                    'ps_cta_text'      => __( '立即下单', 'developer-starter' ),
                    'ps_cta_url'       => '#',
                    'ps_cta_bg'        => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
                    'ps_cta_color'     => '#ffffff',
                    'ps_cta_target'    => '_self',
                    'ps_description'   => __( "活动时间：4月25日 20:00 - 4月30日 23:59\n限购规则：每人限购2件\n发货说明：48小时内发货\n售后服务：7天无理由退换", 'developer-starter' ),
                ),
            ),

            // 模块4：活动战报
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_title'           => __( '会场实时战报', 'developer-starter' ),
                    'stats_subtitle'        => __( '数据持续更新中，爆款销量与下单热度实时可见', 'developer-starter' ),
                    'stats_text_align'      => 'center',
                    'stats_items'           => array(
                        array(
                            'number' => '86万+',
                            'label'  => __( '累计下单', 'developer-starter' ),
                        ),
                        array(
                            'number' => '3.2亿',
                            'label'  => __( '成交金额', 'developer-starter' ),
                        ),
                        array(
                            'number' => '98%',
                            'label'  => __( '好评率', 'developer-starter' ),
                        ),
                        array(
                            'number' => '15分钟',
                            'label'  => __( '峰值成交周期', 'developer-starter' ),
                        ),
                    ),
                    'stats_bg_type'         => 'color',
                    'stats_bg_color'        => '#fef2f2',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块5：买家反馈
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '买家反馈', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实用户评价，帮助快速决策', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '56,000+',
                    'average_rating'          => '4.8',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'   => '',
                            'name'     => __( '郑先生', 'developer-starter' ),
                            'position' => __( '已购用户', 'developer-starter' ),
                            'content'  => __( '价格比平时优惠很多，发货速度也快，体验很好。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'weibo',
                            'date'     => '2026-03-10',
                            'verified' => 'verified',
                            'card_bg'  => '#ffffff',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '许女士', 'developer-starter' ),
                            'position' => __( '会员买家', 'developer-starter' ),
                            'content'  => __( '叠券后非常划算，客服响应及时，售后说明也清楚。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'xiaohongshu',
                            'date'     => '2026-03-16',
                            'verified' => 'vip',
                            'card_bg'  => '#ffffff',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '孙先生', 'developer-starter' ),
                            'position' => __( '回购用户', 'developer-starter' ),
                            'content'  => __( '活动规则明晰，页面导购信息完整，下单效率高。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'google',
                            'date'     => '2026-03-19',
                            'verified' => 'verified',
                            'card_bg'  => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块6：常见问题
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '大促会场常见问题', 'developer-starter' ),
                    'faq_subtitle'          => __( '关于优惠规则、发货时效和售后保障', 'developer-starter' ),
                    'module_bg_color'       => '#fef2f2',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array(
                            'question' => __( '优惠券和满减可以叠加吗？', 'developer-starter' ),
                            'answer'   => __( '部分商品支持叠加，具体以商品详情页和结算页提示为准。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '活动商品多久发货？', 'developer-starter' ),
                            'answer'   => __( '大多数订单将在 48 小时内发货，预售商品以页面说明为准。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '是否支持7天无理由退换？', 'developer-starter' ),
                            'answer'   => __( '支持。特殊品类除外，具体退换规则请查看商品详情页。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '库存会实时更新吗？', 'developer-starter' ),
                            'answer'   => __( '会。库存与下单数据实时联动，售完将自动下架或提示缺货。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块7：行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '活动进行中，立即锁定你的优惠订单', 'developer-starter' ),
                    'cta_subtitle'          => __( '限时限量，优惠结束后恢复原价。', 'developer-starter' ),
                    'cta_button_text'       => __( '马上抢购', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #1e3a8a 0%, #059669 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
