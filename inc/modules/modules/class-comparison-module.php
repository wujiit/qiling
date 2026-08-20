<?php
/**
 * Comparison Module - 比较表格
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Comparison_Module extends Module_Base {

    /**
     * 拆分多行文本，兼容真实换行与字面量 \n。
     *
     * @param mixed $value 原始值。
     * @return array<int,string>
     */
    private function split_multiline_text( $value ) {
        if ( ! is_scalar( $value ) ) {
            return array();
        }

        $text = (string) $value;
        if ( '' === $text ) {
            return array();
        }

        // 兼容真实换行与被转义成字面量的 \n/\r\n。
        $text = str_replace( array( "\r\n", "\r" ), "\n", $text );
        $text = str_replace( array( '\r\n', '\n', '\r' ), "\n", $text );
        $text = str_ireplace( array( '&#13;', '&#x0d;', '&newline;' ), "\n", $text );
        $text = str_ireplace( array( '&#10;', '&#x0a;' ), "\n", $text );
        $text = preg_replace( '/<br\s*\/?>/i', "\n", $text );
        $text = is_string( $text ) ? $text : (string) $value;

        $lines = array_map( 'trim', explode( "\n", $text ) );

        return array_values(
            array_filter(
                $lines,
                function( $line ) {
                    return $line !== '';
                }
            )
        );
    }

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-editor-table';
        $this->description = __( '产品/服务对比表格', 'developer-starter' );
    }

    public function get_id() {
        return 'comparison';
    }

    public function get_name() {
        return __( '比较表格', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id' => 'comparison_title',
                'label' => __( '标题', 'developer-starter' ),
                'type' => 'text',
                'default' => __( '产品对比', 'developer-starter' ),
            ),
            array(
                'id' => 'comparison_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'text',
            ),
            array(
                'id' => 'comparison_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'comparison_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'comparison_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'comparison_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            
            array(
                'id' => 'comparison_features',
                'label' => __( '特性列表 (每行一个)', 'developer-starter' ),
                'type' => 'textarea',
                'default' => __( "基础功能\n高级功能\n技术支持\nAPI接口\n数据导出\n自定义域名", 'developer-starter' ),
            ),
            array(
                'id' => 'comparison_products',
                'label' => __( '产品/方案', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'name', 'label' => __( '产品名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'values', 'label' => __( '特性值 (每行一个，对应特性列表)', 'developer-starter' ), 'type' => 'textarea', 'desc' => __( '使用 ✓ 或 ✗ 表示支持/不支持', 'developer-starter' ) ),
                ),
            ),
            array(
                'id' => 'comparison_highlight',
                'label' => __( '推荐列索引 (1开始)', 'developer-starter' ),
                'type' => 'number',
                'default' => '0',
            ),
            array(
                'id' => 'comparison_highlight_header_bg',
                'label' => __( '推荐表头背景', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '支持纯色或渐变，留空时使用全局品牌渐变。', 'developer-starter' ),
            ),
            array( 'id' => 'comparison_highlight_header_text', 'label' => __( '推荐表头文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'comparison_highlight_badge_bg', 'label' => __( '推荐标签背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'comparison_highlight_badge_text', 'label' => __( '推荐标签文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array(
                'id' => 'comparison_highlight_column_bg',
                'label' => __( '推荐列内容背景', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '支持十六进制、rgb 或 rgba 颜色。', 'developer-starter' ),
            ),
            array(
                'id' => 'comparison_highlight_column_hover_bg',
                'label' => __( '推荐列悬停背景', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '支持十六进制、rgb 或 rgba 颜色。', 'developer-starter' ),
            ),
            
            // Background Settings
            array(
                'id' => 'module_bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'dependency' => array( 'module_bg_type', '==', 'color' ),
            ),
            array(
                'id' => 'module_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
            array(
                'id' => 'module_bg_overlay',
                'label' => __( '背景遮罩浓度', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '0' => __( '无遮罩', 'developer-starter' ),
                    '0.1' => '10%',
                    '0.2' => '20%',
                    '0.3' => '30%',
                    '0.4' => '40%',
                    '0.5' => '50%',
                    '0.6' => '60%',
                    '0.7' => '70%',
                    '0.8' => '80%',
                    '0.9' => '90%',
                ),
                'default' => '0',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
            
            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
        );
    }

    public function get_demo_data() {
        return array(
            'comparison_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '版本对比', 'Plan Comparison' ) : __( '版本对比', 'developer-starter' ),
            'comparison_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '选择最适合您的方案', 'Choose the option that fits your needs.' ) : __( '选择最适合您的方案', 'developer-starter' ),
            'comparison_features' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "基础功能\n高级功能\n技术支持\nAPI接口\n数据导出\n自定义域名", "Core Features\nAdvanced Features\nSupport\nAPI Access\nData Export\nCustom Domain" ) : __( "基础功能\n高级功能\n技术支持\nAPI接口\n数据导出\n自定义域名", 'developer-starter' ),
            'comparison_products' => array(
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础版', 'Starter' ) : __( '基础版', 'developer-starter' ), 'values' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓\n✗\n邮件支持\n✗\n✗\n✗", "✓\n✗\nEmail support\n✗\n✗\n✗" ) : __( "✓\n✗\n邮件支持\n✗\n✗\n✗", 'developer-starter' ) ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业版', 'Professional' ) : __( '专业版', 'developer-starter' ), 'values' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓\n✓\n在线客服\n✓\n✓\n✗", "✓\n✓\nLive chat support\n✓\n✓\n✗" ) : __( "✓\n✓\n在线客服\n✓\n✓\n✗", 'developer-starter' ) ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '企业版', 'Enterprise' ) : __( '企业版', 'developer-starter' ), 'values' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓\n✓\n7×24专属\n✓\n✓\n✓", "✓\n✓\nDedicated 24/7 support\n✓\n✓\n✓" ) : __( "✓\n✓\n7×24专属\n✓\n✓\n✓", 'developer-starter' ) ),
            ),
            'comparison_highlight' => '2', // Highlight Pro
            'module_margin_bottom' => '60px',
        );
    }

    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return preg_match( '/[;{}<>]/', $value ) ? '' : $value;
        };

        $title = isset( $data['comparison_title'] ) ? $data['comparison_title'] : '';
        $subtitle = isset( $data['comparison_subtitle'] ) ? $data['comparison_subtitle'] : '';
        $highlight_col = isset( $data['comparison_highlight'] ) && ! empty( $data['comparison_highlight'] ) ? intval( $data['comparison_highlight'] ) : 0;
        $features = isset( $data['comparison_features'] ) ? $data['comparison_features'] : '';
        $products = isset( $data['comparison_products'] ) ? $data['comparison_products'] : array();
        
        // 解析特性列表
        $feature_list = array();
        if ( ! empty( $features ) ) {
            $feature_list = $this->split_multiline_text( $features );
        } else {
            $feature_list = array(
                function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础功能', 'Core Features' ) : __( '基础功能', 'developer-starter' ),
                function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '高级功能', 'Advanced Features' ) : __( '高级功能', 'developer-starter' ),
                function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '技术支持', 'Support' ) : __( '技术支持', 'developer-starter' ),
                function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'API接口', 'API Access' ) : __( 'API接口', 'developer-starter' ),
                function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '数据导出', 'Data Export' ) : __( '数据导出', 'developer-starter' ),
                function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '自定义域名', 'Custom Domain' ) : __( '自定义域名', 'developer-starter' ),
            );
        }
        
        // 默认产品数据
        if ( empty( $products ) ) {
            $products = array(
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础版', 'Starter' ) : __( '基础版', 'developer-starter' ), 'values' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓\n✗\n邮件支持\n✗\n✗\n✗", "✓\n✗\nEmail support\n✗\n✗\n✗" ) : __( "✓\n✗\n邮件支持\n✗\n✗\n✗", 'developer-starter' ) ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业版', 'Professional' ) : __( '专业版', 'developer-starter' ), 'values' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓\n✓\n在线客服\n✓\n✓\n✗", "✓\n✓\nLive chat support\n✓\n✓\n✗" ) : __( "✓\n✓\n在线客服\n✓\n✓\n✗", 'developer-starter' ) ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '企业版', 'Enterprise' ) : __( '企业版', 'developer-starter' ), 'values' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓\n✓\n7×24专属\n✓\n✓\n✓", "✓\n✓\nDedicated 24/7 support\n✓\n✓\n✓" ) : __( "✓\n✓\n7×24专属\n✓\n✓\n✓", 'developer-starter' ) ),
            );
        }
        
        // Typography Logic
        $title_size = isset( $data['comparison_title_size'] ) ? $data['comparison_title_size'] : '';
        $title_color = isset( $data['comparison_title_color'] ) ? $data['comparison_title_color'] : '';
        $subtitle_size = isset( $data['comparison_subtitle_size'] ) ? $data['comparison_subtitle_size'] : '';
        $subtitle_color = isset( $data['comparison_subtitle_color'] ) ? $data['comparison_subtitle_color'] : '';

        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";

        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        // Background Logic
        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        // 旧版背景色兼容
        if ( empty( $bg_color ) && isset( $data['comparison_bg_color'] ) ) {
            $bg_color = $data['comparison_bg_color'];
        }

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }

        $highlight_style_fields = array(
            'comparison_highlight_header_bg'       => '--comparison-highlight-header-bg',
            'comparison_highlight_header_text'     => '--comparison-highlight-header-text',
            'comparison_highlight_badge_bg'        => '--comparison-highlight-badge-bg',
            'comparison_highlight_badge_text'      => '--comparison-highlight-badge-text',
            'comparison_highlight_column_bg'       => '--comparison-highlight-column-bg',
            'comparison_highlight_column_hover_bg' => '--comparison-highlight-column-hover-bg',
        );
        foreach ( $highlight_style_fields as $field_id => $css_variable ) {
            $field_value = isset( $data[ $field_id ] ) ? $clean_css_value( $data[ $field_id ] ) : '';
            if ( '' !== $field_value ) {
                $section_style .= $css_variable . ':' . $field_value . ';';
            }
        }
        
        $product_values_map = array();
        foreach ( $products as $col_index => $product ) {
            if ( ! is_array( $product ) ) {
                $product_values_map[ $col_index ] = array();
                continue;
            }
            $values_raw = isset( $product['values'] ) ? $product['values'] : '';
            $product_values_map[ $col_index ] = $this->split_multiline_text( $values_raw );
        }

        ?>
        <section class="module module-comparison bg-type-<?php echo esc_attr( $bg_type ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            
            <div class="container">
                <?php if ( $title ) : ?>
                    <div class="section-header text-center">
                        <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="comparison-table-wrapper">
                    <table class="comparison-table">
                        <!-- Table Header -->
                        <thead>
                            <tr>
                                <th class="th-feature"><?php esc_html_e( '功能特性', 'developer-starter' ); ?></th>
                                <?php foreach ( $products as $col_index => $product ) : 
                                    $is_highlight = ( $highlight_col > 0 && $col_index + 1 === $highlight_col );
                                    $th_class = 'th-product' . ( $is_highlight ? ' is-highlight' : '' );
                                ?>
                                    <th class="<?php echo esc_attr( $th_class ); ?>">
                                        <?php echo esc_html( $product['name'] ); ?>
                                        <?php if ( $is_highlight ) : ?>
                                            <span class="highlight-label"><?php esc_html_e( '推荐', 'developer-starter' ); ?></span>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $feature_list as $row_index => $feature ) : ?>
                                <tr>
                                    <td class="td-feature"><?php echo esc_html( $feature ); ?></td>
                                    <?php foreach ( $products as $col_index => $product ) : 
                                        $values_arr = isset( $product_values_map[ $col_index ] ) && is_array( $product_values_map[ $col_index ] )
                                            ? $product_values_map[ $col_index ]
                                            : array();
                                        $cell_value = isset( $values_arr[ $row_index ] ) ? $values_arr[ $row_index ] : '';
                                        $is_highlight = ( $highlight_col > 0 && $col_index + 1 === $highlight_col );
                                        
                                        // 格式化显示
                                        $display_value = $cell_value;
                                        $cell_class = 'td-value';
                                        
                                        if ( $cell_value === '✓' || strtolower( $cell_value ) === 'yes' || $cell_value === '是' || $cell_value === __( '是', 'developer-starter' ) ) {
                                            $display_value = '✓';
                                            $cell_class .= ' val-yes';
                                        } elseif ( $cell_value === '✗' || strtolower( $cell_value ) === 'no' || $cell_value === '否' || $cell_value === __( '否', 'developer-starter' ) ) {
                                            $display_value = '✗';
                                            $cell_class .= ' val-no';
                                        }
                                        
                                        if ( $is_highlight ) {
                                            $cell_class .= ' is-highlight';
                                        }
                                    ?>
                                        <td class="<?php echo esc_attr( $cell_class ); ?>"><?php echo esc_html( $display_value ); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <?php
    }
}
