<?php
/**
 * 案例中心页面创建器类
 *
 * 当用户选择"案例展示"模板创建页面时，自动填充预设模块内容
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
 * 案例中心页面创建器类
 */
class Cases_Center_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-cases.php';
    protected const AJAX_ACTION = 'fill_qiling_cases_center_modules';
    protected const FILLED_META_KEY = '_qiling_cases_center_modules_filled';

    /**
     * 获取案例中心页面的默认模块
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
                    'banner_bg_color' => 'linear-gradient(135deg, #10b981 0%, #0ea5e9 100%)',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image' => '',
                            'title' => __( '案例中心', 'developer-starter' ),
                            'subtitle' => __( '真实案例见证成效与口碑', 'developer-starter' ),
                            'btn_text' => __( '咨询合作', 'developer-starter' ),
                            'btn_url' => '/contact',
                            'btn_bg_color' => '#ffffff',
                            'btn_text_color' => '#0f172a',
                        ),
                    ),
                ),
            ),

            // 模块2：案例展示
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title' => __( '成功案例', 'developer-starter' ),
                    'cases_count' => '9',
                    'cases_columns' => '3',
                    'cases_categories' => '',
                    'cases_show_image' => '1',
                    'cases_image_height' => '240px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块3：数据统计
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_title' => __( '案例成果', 'developer-starter' ),
                    'stats_subtitle' => __( '持续交付可量化的增长结果', 'developer-starter' ),
                    'stats_text_align' => 'center',
                    'stats_items' => array(
                        array( 'number' => '120+', 'label' => __( '行业客户', 'developer-starter' ) ),
                        array( 'number' => '300%', 'label' => __( '平均转化提升', 'developer-starter' ) ),
                        array( 'number' => '98%', 'label' => __( '客户满意度', 'developer-starter' ) ),
                        array( 'number' => '24h', 'label' => __( '响应时间', 'developer-starter' ) ),
                    ),
                    'stats_bg_type' => 'color',
                    'stats_bg_color' => 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
                    'module_padding_top' => '80px',
                    'module_padding_bottom' => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块4：CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title' => __( '想打造下一个标杆案例？', 'developer-starter' ),
                    'cta_subtitle' => __( '联系我们，获取行业定制方案', 'developer-starter' ),
                    'cta_button_text' => __( '开始咨询', 'developer-starter' ),
                    'cta_button_url' => '/contact',
                    'cta_bg_type' => 'color',
                    'cta_bg_color' => 'linear-gradient(135deg, #10b981 0%, #0ea5e9 100%)',
                ),
            ),
        );

        return $default_modules;
    }
}
