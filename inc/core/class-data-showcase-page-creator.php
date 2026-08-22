<?php
/**
 * 数据展示页面创建器类
 *
 * 当用户选择"数据展示"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 数据展示页面创建器类
 */
class Data_Showcase_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-data-showcase.php';
    protected const AJAX_ACTION = 'fill_data_showcase_modules';
    protected const FILLED_META_KEY = '_data_showcase_modules_filled';

    /**
     * 获取数据展示页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            array(
                'type' => 'stats',
                'data' => $this->get_stats_demo_data(),
            ),
            array(
                'type' => 'chart',
                'data' => $this->get_chart_demo_data( 'bar' ),
            ),
            array(
                'type' => 'chart',
                'data' => $this->get_chart_demo_data( 'pie' ),
            ),
            array(
                'type' => 'comparison',
                'data' => $this->get_comparison_demo_data(),
            ),
        );

        return $default_modules;
    }

    /**
     * 获取统计模块演示数据
     *
     * @return array
     */
    private function get_stats_demo_data() {
        if ( class_exists( '\Developer_Starter\Modules\Modules\Stats_Module' ) ) {
            $module = new \Developer_Starter\Modules\Modules\Stats_Module();
            if ( method_exists( $module, 'get_demo_data' ) ) {
                $data = $module->get_demo_data();
                if ( is_array( $data ) ) {
                    return $data;
                }
            }
        }

        return array(
            'stats_title' => __( '关键数据总览', 'developer-starter' ),
            'stats_items' => array(
                array(
                    'number' => '500+',
                    'label'  => __( '服务客户', 'developer-starter' ),
                ),
                array(
                    'number' => '10+',
                    'label'  => __( '年行业经验', 'developer-starter' ),
                ),
                array(
                    'number' => '99%',
                    'label'  => __( '客户满意度', 'developer-starter' ),
                ),
                array(
                    'number' => '24/7',
                    'label'  => __( '在线支持', 'developer-starter' ),
                ),
            ),
        );
    }

    /**
     * 获取图表模块演示数据
     *
     * @param string $type 图表类型。
     * @return array
     */
    private function get_chart_demo_data( $type ) {
        if ( class_exists( '\Developer_Starter\Modules\Modules\Chart_Module' ) ) {
            $module = new \Developer_Starter\Modules\Modules\Chart_Module();
            if ( method_exists( $module, 'get_demo_data' ) ) {
                $data = $module->get_demo_data( $type );
                if ( is_array( $data ) ) {
                    return $data;
                }
            }
        }

        if ( $type === 'pie' ) {
            return array(
                'chart_type'  => 'pie',
                'chart_title' => __( '市场份额分布', 'developer-starter' ),
                'chart_height' => '400',
                'chart_data'  => array(
                    array( 'label' => __( '品牌 A', 'developer-starter' ), 'value' => '300', 'color' => '#ef4444' ),
                    array( 'label' => __( '品牌 B', 'developer-starter' ), 'value' => '50', 'color' => '#3b82f6' ),
                    array( 'label' => __( '品牌 C', 'developer-starter' ), 'value' => '100', 'color' => '#eab308' ),
                ),
            );
        }

        return array(
            'chart_type'   => 'bar',
            'chart_title'  => __( '季度业绩增长 (2024)', 'developer-starter' ),
            'chart_height' => '400',
            'chart_data'   => array(
                array( 'label' => 'Q1', 'value' => '1200', 'color' => '#3b82f6' ),
                array( 'label' => 'Q2', 'value' => '1900', 'color' => '#8b5cf6' ),
                array( 'label' => 'Q3', 'value' => '3000', 'color' => '#10b981' ),
                array( 'label' => 'Q4', 'value' => '5000', 'color' => '#f59e0b' ),
            ),
        );
    }

    /**
     * 获取比较模块演示数据
     *
     * @return array
     */
    private function get_comparison_demo_data() {
        if ( class_exists( '\Developer_Starter\Modules\Modules\Comparison_Module' ) ) {
            $module = new \Developer_Starter\Modules\Modules\Comparison_Module();
            if ( method_exists( $module, 'get_demo_data' ) ) {
                $data = $module->get_demo_data();
                if ( is_array( $data ) ) {
                    return $data;
                }
            }
        }

        return array(
            'comparison_title' => __( '版本对比', 'developer-starter' ),
            'comparison_subtitle' => __( '选择最适合您的方案', 'developer-starter' ),
            'comparison_features' => __( "基础功能\n高级功能\n技术支持\nAPI接口\n数据导出\n自定义域名", 'developer-starter' ),
            'comparison_products' => array(
                array(
                    'name'   => __( '基础版', 'developer-starter' ),
                    'values' => __( "✓\n✗\n邮件支持\n✗\n✗\n✗", 'developer-starter' ),
                ),
                array(
                    'name'   => __( '专业版', 'developer-starter' ),
                    'values' => __( "✓\n✓\n在线客服\n✓\n✓\n✗", 'developer-starter' ),
                ),
                array(
                    'name'   => __( '企业版', 'developer-starter' ),
                    'values' => __( "✓\n✓\n7×24专属\n✓\n✓\n✓", 'developer-starter' ),
                ),
            ),
            'comparison_highlight' => '2',
        );
    }
}
