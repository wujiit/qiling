<?php
/**
 * 产品中心页面创建器类
 *
 * 当用户选择"产品中心"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 2.1.3
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 产品中心页面创建器类
 */
class Products_Center_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-products.php';
    protected const AJAX_ACTION = 'fill_qiling_products_center_modules';
    protected const FILLED_META_KEY = '_qiling_products_center_modules_filled';

    /**
     * 获取产品中心页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 模块1：首屏 Banner
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout' => 'slider',
                    'banner_height' => 'medium',
                    'banner_bg_color' => 'linear-gradient(135deg, #0f766e 0%, #2563eb 100%)',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image' => '',
                            'title' => __( '产品中心', 'developer-starter' ),
                            'subtitle' => __( '精选产品与解决方案展示', 'developer-starter' ),
                            'btn_text' => __( '获取报价', 'developer-starter' ),
                            'btn_url' => '/contact',
                            'btn_bg_color' => '#ffffff',
                            'btn_text_color' => '#111827',
                        ),
                    ),
                ),
            ),

            // 模块2：产品列表（手动版）
            array(
                'type' => 'products',
                'data' => array(
                    'products_title' => __( '精选产品', 'developer-starter' ),
                    'products_subtitle' => 'PRODUCT CENTER',
                    'items' => array(
                        array(
                            'image' => '',
                            'title' => __( '启灵 Pro', 'developer-starter' ),
                            'desc' => __( '企业级官网解决方案', 'developer-starter' ),
                            'specs' => __( "定位：企业官网\n模块：60+\n支持：暗黑模式", 'developer-starter' ),
                            'post_id' => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                        array(
                            'image' => '',
                            'title' => __( '启灵 Commerce', 'developer-starter' ),
                            'desc' => __( '营销转化与商品展示', 'developer-starter' ),
                            'specs' => __( "定位：营销转化\n模块：产品弹窗\n支持：询盘按钮", 'developer-starter' ),
                            'post_id' => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                        array(
                            'image' => '',
                            'title' => __( '启灵 Cloud', 'developer-starter' ),
                            'desc' => __( 'SaaS 软件与下载中心', 'developer-starter' ),
                            'specs' => __( "定位：软件介绍\n模块：下载/排行\n支持：多媒体", 'developer-starter' ),
                            'post_id' => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                        array(
                            'image' => '',
                            'title' => __( '启灵 Studio', 'developer-starter' ),
                            'desc' => __( '品牌视觉与案例展示', 'developer-starter' ),
                            'specs' => __( "定位：品牌展示\n模块：案例/画廊\n支持：视频首屏", 'developer-starter' ),
                            'post_id' => '',
                            'btn_text' => __( '查看详情', 'developer-starter' ),
                        ),
                    ),
                    'columns' => '4',
                    'modal_inquire_text' => __( '立即咨询', 'developer-starter' ),
                    'modal_inquire_url' => '/contact',
                ),
            ),

            // 模块3：CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title' => __( '需要更详细的方案？', 'developer-starter' ),
                    'cta_subtitle' => __( '告诉我们您的业务目标，我们将提供专属建议', 'developer-starter' ),
                    'cta_button_text' => __( '预约演示', 'developer-starter' ),
                    'cta_button_url' => '/contact',
                    'cta_bg_type' => 'color',
                    'cta_bg_color' => 'linear-gradient(135deg, #0f766e 0%, #2563eb 100%)',
                ),
            ),
        );

        return $default_modules;
    }
}
