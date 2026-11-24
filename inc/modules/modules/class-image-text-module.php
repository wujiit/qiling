<?php
/**
 * 图文排版展示模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Image_Text_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-align-left';
        $this->description = __( '图文组合布局', 'qiling' );
    }

    public function get_id() {
        return 'image_text';
    }

    public function get_name() {
        return __( '图文模块', 'qiling' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'image_text_layout', 'type' => 'select', 'label' => __( '图片位置', 'qiling' ), 'options' => array( 'left' => __( '左侧', 'qiling' ), 'right' => __( '右侧', 'qiling' ) ), 'default' => 'left' ),
            array( 'id' => 'image_text_image', 'type' => 'image', 'label' => __( '图片', 'qiling' ) ),
            
            array( 'id' => 'image_text_title', 'type' => 'text', 'label' => __( '标题', 'qiling' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '关于我们', 'About Us' ) : __( '关于我们', 'qiling' ) ),
            array(
                'id' => 'image_text_title_size',
                'label' => __( '标题字体大小', 'qiling' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'qiling' ),
            ),
            array(
                'id' => 'image_text_title_color',
                'label' => __( '标题颜色', 'qiling' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'qiling' ),
            ),
            
            array(
                'id' => 'image_text_subtitle',
                'label' => __( '副标题', 'qiling' ),
                'type' => 'text',
                'default' => '',
            ),
            array(
                'id' => 'image_text_subtitle_size',
                'label' => __( '副标题字体大小', 'qiling' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'qiling' ),
            ),
            array(
                'id' => 'image_text_subtitle_color',
                'label' => __( '副标题颜色', 'qiling' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'qiling' ),
            ),
            
            array( 'id' => 'image_text_content', 'type' => 'editor', 'label' => __( '内容', 'qiling' ) ),
            array( 'id' => 'image_text_button', 'type' => 'text', 'label' => __( '按钮文字', 'qiling' ) ),
            array( 'id' => 'image_text_url', 'type' => 'text', 'label' => __( '按钮链接', 'qiling' ) ),
            array(
                'id'          => 'image_text_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮背景颜色', 'qiling' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'qiling' ),
            ),
            array(
                'id'          => 'image_text_btn_text_color',
                'type'        => 'color',
                'label'       => __( '按钮文字颜色', 'qiling' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'qiling' ),
            ),
            $this->get_button_border_color_field( 'image_text_btn_border_color' ),
            array(
                'id'          => 'image_text_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停背景颜色', 'qiling' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'qiling' ),
            ),
            array(
                'id'          => 'image_text_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停文字颜色', 'qiling' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'qiling' ),
            ),
            $this->get_button_border_color_field( 'image_text_btn_hover_border_color', __( '按钮悬停边框颜色', 'qiling' ), __( '留空时跟随按钮悬停背景颜色。', 'qiling' ) ),
            
            // 样式配置
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'qiling' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'qiling' ),
                'default' => '',
            ),
            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 60px)', 'qiling' ),
                'type' => 'text',
                'default' => '60px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'qiling' ),
                'type' => 'text',
                'default' => '60px',
            ),

            // === 交互热点标记 (Interactive Hotspots for Tech Specs / Details) ===
            array(
                'id'      => 'image_text_enable_hotspots',
                'type'    => 'select',
                'label'   => __( '启用图片交互热点 (Hotspots)', 'qiling' ),
                'options' => array(
                    'no'  => __( '不启用', 'qiling' ),
                    'yes' => __( '启用', 'qiling' ),
                ),
                'default' => 'no',
            ),
            array(
                'id'      => 'image_text_hotspots',
                'type'    => 'repeater',
                'label'   => __( '图片热点标注列表', 'qiling' ),
                'fields'  => array(
                    array(
                        'id'          => 'x',
                        'type'        => 'text',
                        'label'       => __( '水平位置百分比 (0-100，如 45)', 'qiling' ),
                        'default'     => '50',
                    ),
                    array(
                        'id'          => 'y',
                        'type'        => 'text',
                        'label'       => __( '垂直位置百分比 (0-100，如 30)', 'qiling' ),
                        'default'     => '50',
                    ),
                    array(
                        'id'          => 'title',
                        'type'        => 'text',
                        'label'       => __( '热点标题（如：蓝宝石水晶镜面 / 碳纤维抗扭片）', 'qiling' ),
                    ),
                    array(
                        'id'          => 'desc',
                        'type'        => 'textarea',
                        'label'       => __( '热点详细说明（如：莫氏9级硬度，双面防眩光涂层）', 'qiling' ),
                    ),
                ),
            ),
        );
    }

    public function render( $data = array() ) {
        $layout = isset( $data['image_text_layout'] ) ? $data['image_text_layout'] : 'left';
        $image = isset( $data['image_text_image'] ) ? $data['image_text_image'] : '';
        $title = isset( $data['image_text_title'] ) ? $data['image_text_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '关于我们', 'About Us' ) : __( '关于我们', 'qiling' ) );
        $subtitle = isset( $data['image_text_subtitle'] ) ? $data['image_text_subtitle'] : '';
        $content = isset( $data['image_text_content'] ) ? $data['image_text_content'] : '';
        $button = isset( $data['image_text_button'] ) ? $data['image_text_button'] : '';
        $url = isset( $data['image_text_url'] ) ? $data['image_text_url'] : '';

        $clean_css_value = static function( $value ) {
            $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
            return $value;
        };

        $button_bg_color = isset( $data['image_text_btn_bg_color'] ) ? $clean_css_value( $data['image_text_btn_bg_color'] ) : '';
        $button_text_color = isset( $data['image_text_btn_text_color'] ) ? $clean_css_value( $data['image_text_btn_text_color'] ) : '';
        $button_border_color = isset( $data['image_text_btn_border_color'] ) ? $clean_css_value( $data['image_text_btn_border_color'] ) : '';
        $button_hover_bg_color = isset( $data['image_text_btn_hover_bg_color'] ) ? $clean_css_value( $data['image_text_btn_hover_bg_color'] ) : '';
        $button_hover_text_color = isset( $data['image_text_btn_hover_text_color'] ) ? $clean_css_value( $data['image_text_btn_hover_text_color'] ) : '';
        $button_hover_border_color = isset( $data['image_text_btn_hover_border_color'] ) ? $clean_css_value( $data['image_text_btn_hover_border_color'] ) : '';
        
        // Typography
        $title_size = isset( $data['image_text_title_size'] ) ? $data['image_text_title_size'] : '';
        $title_color = isset( $data['image_text_title_color'] ) ? $data['image_text_title_color'] : '';
        $subtitle_size = isset( $data['image_text_subtitle_size'] ) ? $data['image_text_subtitle_size'] : '';
        $subtitle_color = isset( $data['image_text_subtitle_color'] ) ? $data['image_text_subtitle_color'] : '';
        
        // Background & Spacing
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        // 动态样式计算
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        // 交互热点 (Hotspots)
        $enable_hotspots = isset( $data['image_text_enable_hotspots'] ) && in_array( $data['image_text_enable_hotspots'], array( 'yes', 'no' ), true ) ? $data['image_text_enable_hotspots'] : 'no';
        $hotspots = isset( $data['image_text_hotspots'] ) && is_array( $data['image_text_hotspots'] ) ? $data['image_text_hotspots'] : array();
        $module_id = 'it-' . uniqid();
        
        if ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }

        if ( '' !== $button_bg_color ) {
            $section_style .= "--image-text-btn-bg: {$button_bg_color};--image-text-btn-border: {$button_bg_color};";
        }

        if ( '' !== $button_text_color ) {
            $section_style .= "--image-text-btn-text: {$button_text_color};";
        }

        if ( '' !== $button_border_color ) {
            $section_style .= "--image-text-btn-border: {$button_border_color};";
        }

        if ( '' !== $button_hover_bg_color ) {
            $section_style .= "--image-text-btn-hover-bg: {$button_hover_bg_color};--image-text-btn-hover-border: {$button_hover_bg_color};";
        }

        if ( '' !== $button_hover_text_color ) {
            $section_style .= "--image-text-btn-hover-text: {$button_hover_text_color};";
        }

        if ( '' !== $button_hover_border_color ) {
            $section_style .= "--image-text-btn-hover-border: {$button_hover_border_color};";
        }
        
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        $grid_class = 'image-text-grid';
        if ( $layout === 'right' ) {
            $grid_class .= ' layout-right';
        }
        ?>
        <style>
            #<?php echo esc_attr( $module_id ); ?> .it-media-stage {
                position: relative;
                display: block;
                width: 100%;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-media-stage img {
                display: block;
                width: 100%;
                height: auto;
                border-radius: 12px;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspots-container {
                position: absolute;
                inset: 0;
                pointer-events: none;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pin {
                position: absolute;
                transform: translate(-50%, -50%);
                width: 28px;
                height: 28px;
                pointer-events: auto;
                cursor: pointer;
                z-index: 10;
                outline: none;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pulse {
                position: absolute;
                inset: -6px;
                border-radius: 50%;
                background: rgba(37, 99, 235, 0.35);
                animation: itHotspotPulse 2s cubic-bezier(0.16, 1, 0.3, 1) infinite;
            }
            @keyframes itHotspotPulse {
                0% { transform: scale(0.8); opacity: 0.8; }
                70% { transform: scale(1.6); opacity: 0; }
                100% { transform: scale(1.6); opacity: 0; }
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-dot {
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #2563eb;
                color: #ffffff;
                font-size: 16px;
                font-weight: 700;
                line-height: 1;
                box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
                transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background 0.2s ease;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pin:hover .it-hotspot-dot,
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pin:focus .it-hotspot-dot,
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pin.is-active .it-hotspot-dot {
                transform: scale(1.15) rotate(45deg);
                background: #1d4ed8;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-popover {
                position: absolute;
                bottom: calc(100% + 10px);
                left: 50%;
                transform: translateX(-50%) translateY(6px);
                min-width: 180px;
                max-width: 260px;
                padding: 10px 14px;
                background: rgba(15, 23, 42, 0.92);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 10px;
                color: #ffffff;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
                z-index: 20;
                text-align: left;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-popover::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 6px solid transparent;
                border-top-color: rgba(15, 23, 42, 0.92);
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pin:hover .it-hotspot-popover,
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pin:focus .it-hotspot-popover,
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-pin.is-active .it-hotspot-popover {
                opacity: 1;
                visibility: visible;
                transform: translateX(-50%) translateY(0);
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-title {
                display: block;
                font-size: 13px;
                font-weight: 700;
                color: #ffffff;
                margin-bottom: 3px;
            }
            #<?php echo esc_attr( $module_id ); ?> .it-hotspot-desc {
                font-size: 11px;
                line-height: 1.5;
                color: #cbd5e1;
                margin: 0;
            }
        </style>
        <section class="module module-image-text" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <div class="<?php echo esc_attr( $grid_class ); ?>">
                    <div class="image-text-image">
                        <?php if ( $image ) : ?>
                            <div class="it-media-stage <?php echo ( 'yes' === $enable_hotspots && ! empty( $hotspots ) ) ? 'has-hotspots' : ''; ?>">
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                                <?php if ( 'yes' === $enable_hotspots && ! empty( $hotspots ) ) : ?>
                                    <div class="it-hotspots-container">
                                        <?php foreach ( $hotspots as $h_idx => $hp ) :
                                            $hx = isset( $hp['x'] ) ? floatval( $hp['x'] ) : 50;
                                            $hy = isset( $hp['y'] ) ? floatval( $hp['y'] ) : 50;
                                            $htitle = isset( $hp['title'] ) ? sanitize_text_field( (string) $hp['title'] ) : '';
                                            $hdesc  = isset( $hp['desc'] ) ? sanitize_textarea_field( (string) $hp['desc'] ) : '';
                                            if ( empty( $htitle ) && empty( $hdesc ) ) continue;
                                        ?>
                                            <div class="it-hotspot-pin" style="left: <?php echo esc_attr( $hx ); ?>%; top: <?php echo esc_attr( $hy ); ?>%;" tabindex="0" role="button" aria-label="<?php echo esc_attr( $htitle ?: __( '热点标注', 'qiling' ) ); ?>">
                                                <span class="it-hotspot-pulse"></span>
                                                <span class="it-hotspot-dot">+</span>
                                                <div class="it-hotspot-popover">
                                                    <?php if ( $htitle ) : ?>
                                                        <strong class="it-hotspot-title"><?php echo esc_html( $htitle ); ?></strong>
                                                    <?php endif; ?>
                                                    <?php if ( $hdesc ) : ?>
                                                        <p class="it-hotspot-desc"><?php echo wp_kses_post( $hdesc ); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <div class="placeholder-image"><?php esc_html_e( '请上传图片', 'qiling' ); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="image-text-content">
                        <?php if ( $title ) : ?>
                            <h2 class="module-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo wp_kses_post( $title ); ?></h2>
                        <?php endif; ?>
                        
                        <?php if ( $subtitle ) : ?>
                            <p class="module-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo wp_kses_post( $subtitle ); ?></p>
                        <?php endif; ?>
                        
                        <?php if ( $content ) : ?>
                            <div class="module-desc"><?php echo wp_kses_post( $content ); ?></div>
                        <?php endif; ?>
                        
                        <?php if ( $button ) : ?>
                            <div class="module-action">
                                <a href="<?php echo $url ? esc_url( $url ) : '#'; ?>" class="image-text-btn"><?php echo esc_html( $button ); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php if ( 'yes' === $enable_hotspots && ! empty( $hotspots ) ) : ?>
            <script>
            (function() {
                var itRoot = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!itRoot) return;
                var pins = itRoot.querySelectorAll('.it-hotspot-pin');
                pins.forEach(function(pin) {
                    pin.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var wasActive = pin.classList.contains('is-active');
                        pins.forEach(function(p) { p.classList.remove('is-active'); });
                        if (!wasActive) {
                            pin.classList.add('is-active');
                        }
                    });
                });
                document.addEventListener('click', function() {
                    pins.forEach(function(p) { p.classList.remove('is-active'); });
                });
            })();
            </script>
        <?php endif; ?>
        <?php
    }
}
