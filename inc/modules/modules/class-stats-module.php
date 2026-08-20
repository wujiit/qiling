<?php
/**
 * Stats Module - 数据统计
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Stats_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-chart-bar';
        $this->description = __( '数据统计展示', 'developer-starter' );
    }

    public function get_id() {
        return 'stats';
    }

    public function get_name() {
        return __( '数据统计', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // Title & Subtitle
            array( 'id' => 'stats_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => '' ),
            array(
                'id' => 'stats_subtitle',
                'type' => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
                'description' => __( '支持允许的HTML标签，例如 &lt;span style="color:red"&gt;强调文本&lt;/span&gt;', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id' => 'stats_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'stats_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认(白色/自适应)', 'developer-starter' ),
            ),
            
            array(
                'id' => 'stats_text_align',
                'label' => __( '内容对齐', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'left' => __( '左对齐', 'developer-starter' ),
                    'center' => __( '居中', 'developer-starter' ),
                    'right' => __( '右对齐', 'developer-starter' ),
                ),
                'default' => 'center',
            ),
            
            array(
                'id' => 'stats_items',
                'label' => __( '统计项', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'number', 'label' => __( '数字', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'label', 'label' => __( '标签', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'number_color', 'label' => __( '数字颜色', 'developer-starter' ), 'type' => 'color', 'description' => __( '留空使用模块默认颜色', 'developer-starter' ) ),
                    array( 'id' => 'label_color', 'label' => __( '标签颜色', 'developer-starter' ), 'type' => 'color', 'description' => __( '留空使用模块默认颜色', 'developer-starter' ) ),
                ),
            ),
            
            // Background Settings
            array(
                'id' => 'stats_bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '颜色/渐变', 'developer-starter' ),
                    'image' => __( '背景图片', 'developer-starter' ),
                ),
                'default' => 'image', // Default to image to match previous behavior if possible, or color
            ),
            array(
                'id' => 'stats_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
                'dependency' => array( 'stats_bg_type', '==', 'color' ),
            ),
            array(
                'id' => 'stats_bg_image', // Keeping ID same as before for potential backward compat if basic image was used
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'stats_bg_type', '==', 'image' ),
            ),
            array(
                'id' => 'stats_bg_overlay',
                'label' => __( '图片遮罩透明度', 'developer-starter' ),
                'type' => 'text',
                'default' => '0.6',
                'desc' => __( '0-1之间的小数，如 0.6 表示60%黑遮罩', 'developer-starter' ),
                'dependency' => array( 'stats_bg_type', '==', 'image' ),
            ),

            // Featured Layout Settings (Side Image)
            array(
                'id' => 'stats_side_image',
                'label' => __( '侧边配图 (3项数据时生效)', 'developer-starter' ),
                'type' => 'image',
                'description' => __( '仅当统计项刚好为3个且上传了此图片时，会启用特殊的"左图右数据"布局。', 'developer-starter' ),
            ),
            
            // Spacing
            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 80px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 80px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，统计数字将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function get_demo_data() {
        return array(
            'stats_subtitle' => __( '这里是副标题演示，支持 <span style="color: var(--qiling-color-ff4757);">HTML标签</span>', 'developer-starter' ),
            'stats_bg_image' => '',
            'stats_text_align' => 'center',
            'stats_items' => array(
                array( 'number' => '500', 'label' => __( '服务客户', 'developer-starter' ) ),
                array( 'number' => '10', 'label' => __( '年行业经验', 'developer-starter' ) ),
                array( 'number' => '50', 'label' => __( '专业团队', 'developer-starter' ) ),
                array( 'number' => '99', 'label' => __( '客户满意度', 'developer-starter' ) ),
            ),
            'module_margin_top' => '60px',
            'module_margin_bottom' => '60px',
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['stats_title'] ) ? $data['stats_title'] : '';
        $subtitle = isset( $data['stats_subtitle'] ) ? $data['stats_subtitle'] : '';
        $text_align = isset( $data['stats_text_align'] ) ? $data['stats_text_align'] : 'center';
        $items = isset( $data['stats_items'] ) ? $data['stats_items'] : array();
        
        // Typography
        $title_size = isset( $data['stats_title_size'] ) ? $data['stats_title_size'] : '';
        $title_color = isset( $data['stats_title_color'] ) ? $data['stats_title_color'] : '';
        
        // Background
        $bg_type = isset( $data['stats_bg_type'] ) ? $data['stats_bg_type'] : 'image'; // Default to image if not set (legacy)
        $bg_color = isset( $data['stats_bg_color'] ) ? $data['stats_bg_color'] : '';
        $bg_image = isset( $data['stats_bg_image'] ) ? $data['stats_bg_image'] : '';
        $overlay_opacity = isset( $data['stats_bg_overlay'] ) && $data['stats_bg_overlay'] !== '' ? $data['stats_bg_overlay'] : '0.6';
        
        // Spacing
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '80px';
        
        if ( empty( $items ) ) {
            $items = array(
                array( 'number' => '500', 'label' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '服务客户', 'Clients Served' ) : __( '服务客户', 'developer-starter' ) ),
                array( 'number' => '10', 'label' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '年行业经验', 'Years of Experience' ) : __( '年行业经验', 'developer-starter' ) ),
                array( 'number' => '50', 'label' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业团队', 'Team Members' ) : __( '专业团队', 'developer-starter' ) ),
                array( 'number' => '99', 'label' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '客户满意度', 'Client Satisfaction' ) : __( '客户满意度', 'developer-starter' ) ),
            );
        }
        
        // Dynamic Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb}; text-align: {$text_align};";
        
        if ( $bg_type === 'color' && $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        } elseif ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('{$bg_image}');";
        } elseif ( $bg_type === 'image' && ! $bg_image ) {
             // Fallback default gradient if image mode selected but no image (or legacy fallback)
             $section_style .= "background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark, var(--color-primary-dark)) 100%);";
        }
        
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        // Grid Justify
        $justify = 'center';
        if ( $text_align === 'left' ) $justify = 'flex-start';
        if ( $text_align === 'right' ) $justify = 'flex-end';

        // Check for Featured Layout
        $side_image = isset( $data['stats_side_image'] ) ? $data['stats_side_image'] : '';
        $is_featured_layout = ( count($items) === 3 && ! empty($side_image) );

        // Check for Featured Layout
        $side_image = isset( $data['stats_side_image'] ) ? $data['stats_side_image'] : '';
        $is_featured_layout = ( count($items) === 3 && ! empty($side_image) );
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        if ( $is_featured_layout ) {
            $this->render_featured_layout( $items, $side_image, $title, $subtitle, $title_style, $section_style, $text_align, $module_id = 'stats-' . uniqid(), $enable_anim );
        } else {
            // Default Layout
            ?>
            <section class="module module-stats <?php echo $bg_type === 'image' && $bg_image ? 'has-bg-image' : ''; ?>" style="<?php echo esc_attr( $section_style ); ?>">
                <?php if ( $bg_type === 'image' && $bg_image ) : ?>
                    <div class="stats-overlay" style="opacity: <?php echo esc_attr( $overlay_opacity ); ?>;"></div>
                <?php endif; ?>

                <!-- Decor: Left (Network Nodes) -->
                <div class="module-decor d-left" style="width: 300px; height: 300px; left: -50px; opacity: 0.6;">
                    <svg width="300" height="300" viewBox="0 0 300 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="150" r="4" fill="currentColor" fill-opacity="0.4"/>
                        <circle cx="120" cy="80" r="6" fill="currentColor" fill-opacity="0.4"/>
                        <circle cx="180" cy="180" r="5" fill="currentColor" fill-opacity="0.4"/>
                        <circle cx="250" cy="100" r="8" fill="currentColor" fill-opacity="0.4"/>
                        <path d="M50 150L120 80L180 180L250 100" stroke="currentColor" stroke-width="2" stroke-opacity="0.2"/>
                        <circle cx="50" cy="150" r="100" stroke="currentColor" stroke-width="1" stroke-opacity="0.05" stroke-dasharray="8 8"/>
                        <circle cx="250" cy="100" r="80" stroke="currentColor" stroke-width="1" stroke-opacity="0.05" stroke-dasharray="4 4"/>
                    </svg>
                </div>

                <!-- Decor: Right (Growth/Ascending) -->
                <div class="module-decor d-right" style="width: 260px; height: 260px; right: -30px; opacity: 0.6;">
                    <svg width="260" height="260" viewBox="0 0 260 260" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="40" y="160" width="30" height="60" rx="4" fill="currentColor" fill-opacity="0.1"/>
                        <rect x="90" y="120" width="30" height="100" rx="4" fill="currentColor" fill-opacity="0.15"/>
                        <rect x="140" y="80" width="30" height="140" rx="4" fill="currentColor" fill-opacity="0.2"/>
                        <rect x="190" y="40" width="30" height="180" rx="4" fill="currentColor" fill-opacity="0.25"/>
                        <path d="M30 240H230" stroke="currentColor" stroke-width="2" stroke-opacity="0.2" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <div class="container">
                    <?php if ( $title ) : ?>
                        <h2 class="stats-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    
                    <?php if ( $subtitle ) : ?>
                        <div class="stats-subtitle" style="margin-top: var(--qiling-space-10); font-size: calc(var(--qiling-font-size-base) * 1.1); opacity: 0.9; <?php echo $title_color ? 'color:' . esc_attr($title_color) . ';' : ''; ?>">
                            <?php echo wp_kses_post( $subtitle ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="stats-grid" style="justify-content: <?php echo esc_attr( $justify ); ?>;">
                        <?php foreach ( $items as $index => $item ) : 
                            $number = isset( $item['number'] ) ? $item['number'] : '0';
                            $label = isset( $item['label'] ) ? $item['label'] : '';
                            $number_style = $this->build_color_style_attr( isset( $item['number_color'] ) ? $item['number_color'] : '' );
                            $label_style = $this->build_color_style_attr( isset( $item['label_color'] ) ? $item['label_color'] : '' );
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="stat-item" <?php echo $anim_attr; ?>>
                                <div class="stat-number"<?php echo $number_style; ?>><?php echo esc_html( $number ); ?><span class="stat-plus">+</span></div>
                                <div class="stat-label"<?php echo $label_style; ?>><?php echo esc_html( $label ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
        }
    }

    /**
     * Render the special Featured Layout (Left Image + Right Stats Card)
     */
    /**
     * Render the special Featured Layout (Left Image + Right Stats Card)
     */
    private function render_featured_layout( $items, $side_image, $title, $subtitle, $title_style, $section_style, $text_align, $module_id, $enable_anim = 'yes' ) {
        $section_style .= '--stats-featured-text-align:' . sanitize_text_field( $text_align ) . ';';
        ?>
        <section class="module module-stats-featured" id="<?php echo esc_attr($module_id); ?>" style="<?php echo esc_attr( $section_style ); ?>">
             <!-- Background Glow Effect -->
             <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; height: 100%; opacity: 0.5; overflow: hidden; pointer-events: none;">
                <div style="position: absolute; top: 20%; left: 20%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(var(--qiling-rgb-16-185-129), 0.1) 0%, var(--qiling-color-rgba-255-255-255-0) 70%); border-radius: 50%;"></div>
            </div>

            <div class="container stats-featured-container">
                <div class="stats-header-wrapper" style="margin-bottom: var(--qiling-space-60); text-align: center;">
                    <?php if ( $title ) : ?>
                        <h2 class="stats-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>

                    <?php if ( $subtitle ) : ?>
                        <div class="stats-subtitle" style="margin-top: var(--qiling-space-15); font-size: var(--qiling-text-rem-1p25); color: var(--qiling-color-4b5563); max-width: var(--qiling-measure-800); margin-left: auto; margin-right: auto;">
                            <?php echo wp_kses_post( $subtitle ); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="stats-featured-inner">
                    <div class="stats-featured-image">
                        <img src="<?php echo esc_url($side_image); ?>" alt="Stats Illustration">
                    </div>
                    
                    <div class="stats-featured-content">
                        <?php foreach ( $items as $index => $item ) : 
                            $raw_number = isset( $item['number'] ) ? $item['number'] : '0';
                            $label = isset( $item['label'] ) ? $item['label'] : '';
                            $number_style = $this->build_color_style_attr( isset( $item['number_color'] ) ? $item['number_color'] : '' );
                            $label_style = $this->build_color_style_attr( isset( $item['label_color'] ) ? $item['label_color'] : '' );
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="featured-stat-item" <?php echo $anim_attr; ?>>
                                <div class="featured-stat-number"<?php echo $number_style; ?>>
                                    <?php echo esc_html( $raw_number ); ?><span class="featured-stat-unit">+</span>
                                </div>
                                <div class="featured-stat-label"<?php echo $label_style; ?>><?php echo esc_html( $label ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 构建安全的单项颜色 style。
     *
     * @param mixed $value 颜色值。
     * @return string
     */
    private function build_color_style_attr( $value ) {
        $color = $this->sanitize_css_color_value( $value, '' );
        if ( '' === $color ) {
            return '';
        }

        return ' style="' . esc_attr( 'color:' . $color . ';' ) . '"';
    }

    /**
     * 清洗 CSS 颜色值（允许 hex、rgb/rgba、hsl/hsla 与设计令牌变量）。
     *
     * @param mixed  $value   颜色值。
     * @param string $default 默认值。
     * @return string
     */
    private function sanitize_css_color_value( $value, $default = '' ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return $default;
        }

        $hex_color = sanitize_hex_color( $value );
        if ( $hex_color ) {
            return $hex_color;
        }

        if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9\.\s,%]+\s*\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }
}
