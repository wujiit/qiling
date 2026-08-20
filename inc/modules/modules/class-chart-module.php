<?php
namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Chart_Module extends Module_Base {

    public function get_id() {
        return 'chart';
    }

    public function get_name() {
        return __( '数据图表', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'heading_basic', 'type' => 'heading', 'label' => __( '基础设置', 'developer-starter' ) ),
            array( 'id' => 'chart_type', 'type' => 'select', 'label' => __( '图表类型', 'developer-starter' ), 'options' => array(
                'bar'      => __( '柱状图 (Bar)', 'developer-starter' ),
                'line'     => __( '折线图 (Line)', 'developer-starter' ),
                'pie'      => __( '饼图 (Pie)', 'developer-starter' ),
                'doughnut' => __( '环形图 (Doughnut)', 'developer-starter' ),
            ), 'default' => 'bar' ),
            array( 'id' => 'chart_title', 'type' => 'text', 'label' => __( '图表标题', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'chart_height', 'type' => 'number', 'label' => __( '图表高度 (px)', 'developer-starter' ), 'default' => '400' ),
            
            array( 'id' => 'heading_data', 'type' => 'heading', 'label' => __( '数据设置', 'developer-starter' ) ),
            array( 
                'id' => 'chart_data', 
                'type' => 'repeater', 
                'label' => __( '数据项', 'developer-starter' ), 
                'fields' => array(
                    array( 'id' => 'label', 'type' => 'text', 'label' => __( '标签 (X轴)', 'developer-starter' ) ),
                    array( 'id' => 'value', 'type' => 'number', 'label' => __( '数值 (Y轴)', 'developer-starter' ) ),
                    array( 'id' => 'color', 'type' => 'color', 'label' => __( '颜色 (可选)', 'developer-starter' ), 'desc' => __( '留空则自动生成颜色', 'developer-starter' ) ),
                )
            ),
        );
    }

    public function get_demo_data( $type = 'bar' ) {
        $data = array(
            'chart_type' => $type,
            'chart_height' => '400',
            'module_margin_bottom' => '60px',
        );

        switch ( $type ) {
            case 'pie':
                $data['chart_title'] = __( '市场份额分布', 'developer-starter' );
                $data['chart_data'] = array(
                    array( 'label' => __( '品牌 A', 'developer-starter' ), 'value' => '300', 'color' => 'var(--qiling-color-ef4444)' ),
                    array( 'label' => __( '品牌 B', 'developer-starter' ), 'value' => '50', 'color' => 'var(--color-primary-light)' ),
                    array( 'label' => __( '品牌 C', 'developer-starter' ), 'value' => '100', 'color' => 'var(--qiling-color-eab308)' ),
                );
                break;
            
            case 'bar':
            default:
                $data['chart_title'] = __( '季度业绩增长 (2024)', 'developer-starter' );
                $data['chart_data'] = array(
                    array( 'label' => 'Q1', 'value' => '1200', 'color' => 'var(--color-primary-light)' ),
                    array( 'label' => 'Q2', 'value' => '1900', 'color' => 'var(--qiling-color-8b5cf6)' ),
                    array( 'label' => 'Q3', 'value' => '3000', 'color' => 'var(--qiling-color-10b981)' ),
                    array( 'label' => 'Q4', 'value' => '5000', 'color' => 'var(--color-warning)' ),
                );
                break;
        }

        return $data;
    }

    public function render( $data = array() ) {
        $chart_id = 'ds_chart_' . uniqid();
        $type = isset( $data['chart_type'] ) ? $data['chart_type'] : 'bar';
        $title = isset( $data['chart_title'] ) ? $data['chart_title'] : '';
        $height = isset( $data['chart_height'] ) ? absint( $data['chart_height'] ) : 400;
        $items = isset( $data['chart_data'] ) ? $data['chart_data'] : array();

        if ( empty( $items ) || ! is_array( $items ) ) {
            return;
        }

        // Prepare data for Chart.js
        $labels = array();
        $values = array();
        $colors = array();
        $border_colors = array();

        $default_colors = array(
            'var(--color-primary)', 'var(--color-violet-600)', 'var(--color-accent)', 'var(--color-error)', 'var(--color-warning-dark)', 'var(--color-success)', 'var(--color-info-dark)', 'var(--qiling-color-4f46e5)'
        );

        foreach ( $items as $index => $item ) {
            $labels[] = isset( $item['label'] ) ? $item['label'] : 'Item ' . ($index + 1);
            $values[] = isset( $item['value'] ) ? floatval( $item['value'] ) : 0;
            
            // Handle Color
            if ( ! empty( $item['color'] ) ) {
                $bg_color = $item['color'];
            } else {
                $bg_color = $default_colors[ $index % count( $default_colors ) ];
            }
            $colors[] = $bg_color;
            // Add some transparency/variation if needed, or keep simple
        }

        // Get Chart.js from the centralized resolver so CDN URLs keep whitelist and version rules.
        $chart_asset = function_exists( 'developer_starter_get_third_party_asset' )
            ? developer_starter_get_third_party_asset( 'chart_js' )
            : array(
                'url'     => DEVELOPER_STARTER_ASSETS . '/js/vendor/chart.min.js',
                'version' => '2.7.2',
            );

        $chart_config = array(
            'type' => $type,
            'data' => array(
                'labels' => $labels,
                'datasets' => array(
                    array(
                        'label' => $title ? $title : __( '数据', 'developer-starter' ),
                        'data' => $values,
                        'backgroundColor' => $colors,
                        'borderColor' => $colors, // consistent border
                        'borderWidth' => 1,
                    )
                )
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                // 同时兼容 Chart.js 2.x 与 3.x/4.x 的 legend/title 配置结构。
                'legend' => array(
                    'position' => 'bottom',
                ),
                'title' => array(
                    'display' => ! empty( $title ),
                    'text' => $title,
                ),
                'plugins' => array(
                    'legend' => array(
                        'position' => 'bottom',
                    ),
                    'title' => array(
                        'display' => ! empty( $title ),
                        'text' => $title
                    )
                )
            )
        );
        
        // Remove legend for Bar/Line if it's just one dataset usually (optional UI choice)
        // But for consistency let's keep it or hide it if no title.
        
        // 优化: 使用 wp_enqueue_script 按需加载资源，避免重复输出 script 标签
        if ( ! wp_script_is( 'chart-js', 'registered' ) ) {
            wp_register_script( 'chart-js', $chart_asset['url'], array(), $chart_asset['version'], true );
        }
        wp_enqueue_script( 'chart-js' );
        ?>
        <div class="developer-starter-module module-chart" style="margin: var(--qiling-space-30) 0;">
            <div class="chart-container" style="position: relative; height:<?php echo esc_attr( $height ); ?>px; width:100%;">
                <canvas id="<?php echo esc_attr( $chart_id ); ?>"></canvas>
            </div>

            <script>
            (function(){
                var attempts = 0;
                function initChart() {
                    var canvas = document.getElementById('<?php echo esc_js( $chart_id ); ?>');
                    if (!canvas || !canvas.isConnected) {
                        return;
                    }
                    // 检测 Chart 对象是否存在
                    if (typeof Chart === 'undefined') {
                        // 如果超过 10 秒 (200次 * 50ms) 还没加载完，停止尝试，避免死循环
                        if (attempts > 200) {
                            console.warn('Chart.js failed to load.');
                            return;
                        }
                        attempts++;
                        setTimeout(initChart, 50);
                        return;
                    }
                    var ctx = canvas.getContext('2d');
                    new Chart(ctx, <?php echo wp_json_encode( $chart_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>);
                }
                
                // 启动检查
                initChart();
            })();
            </script>
        </div>
        <?php
    }
}
