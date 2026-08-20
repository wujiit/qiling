<?php
/**
 * CTA Module - 行动号召
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CTA_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-megaphone';
        $this->description = __( '行动号召模块', 'developer-starter' );
    }

    public function get_id() {
        return 'cta';
    }

    public function get_name() {
        return __( 'CTA按钮', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'cta_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '准备好开始了吗？', 'Ready to get started?' ) : __( '准备好开始了吗？', 'developer-starter' ) ),
            array(
                'id' => 'cta_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2.5rem 或 40px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'cta_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认(白色)', 'developer-starter' ),
            ),
            
            array( 'id' => 'cta_subtitle', 'type' => 'textarea', 'label' => __( '副标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即联系我们，获取专业方案和报价', 'Contact us today for a tailored solution and quote.' ) : __( '立即联系我们，获取专业方案和报价', 'developer-starter' ) ),
            array(
                'id' => 'cta_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.25rem 或 20px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'cta_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认(半透明白)', 'developer-starter' ),
            ),
            
            array( 'id' => 'cta_button_text', 'type' => 'text', 'label' => __( '按钮文字', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费咨询', 'Get a Free Quote' ) : __( '免费咨询', 'developer-starter' ) ),
            array( 'id' => 'cta_button_url', 'type' => 'text', 'label' => __( '按钮链接', 'developer-starter' ), 'default' => '#' ),
            array(
                'id'          => 'cta_button_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '留空使用默认浅色按钮，只影响当前 CTA 模块。', 'developer-starter' ),
            ),
            array(
                'id'          => 'cta_button_text_color',
                'type'        => 'color',
                'label'       => __( '按钮文字颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '留空使用主题主色文字。', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'cta_button_border_color' ),
            array(
                'id'          => 'cta_button_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '留空使用浅灰悬停背景。', 'developer-starter' ),
            ),
            array(
                'id'          => 'cta_button_hover_text_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停文字颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '留空使用主题主色悬停文字。', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'cta_button_hover_border_color', __( '按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随按钮悬停背景颜色。', 'developer-starter' ) ),
            
            // Background Settings
            array(
                'id' => 'cta_bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '颜色/渐变', 'developer-starter' ),
                    'image' => __( '背景图片', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id' => 'cta_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => 'linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary-dark) 100%)',
                'dependency' => array( 'cta_bg_type', '==', 'color' ),
            ),
            array(
                'id' => 'cta_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'cta_bg_type', '==', 'image' ),
            ),
            array(
                'id' => 'cta_bg_overlay',
                'label' => __( '图片遮罩透明度', 'developer-starter' ),
                'type' => 'text',
                'default' => '0.5',
                'desc' => __( '0-1之间的小数，如 0.5 表示50%黑遮罩', 'developer-starter' ),
                'dependency' => array( 'cta_bg_type', '==', 'image' ),
            ),

            // Layout.
            array(
                'id'      => 'cta_layout_mode',
                'label'   => __( '板块样式', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'standard'        => __( '标准通栏', 'developer-starter' ),
                    'card'            => __( '卡片式', 'developer-starter' ),
                    'seamless_footer' => __( '衔接底部波浪', 'developer-starter' ),
                ),
                'default' => 'standard',
            ),
            array(
                'id'          => 'cta_footer_bridge',
                'label'       => __( '底部衔接方式', 'developer-starter' ),
                'type'        => 'select',
                'options'     => array(
                    'auto' => __( '自动', 'developer-starter' ),
                    'on'   => __( '强制衔接页脚', 'developer-starter' ),
                    'off'  => __( '不衔接', 'developer-starter' ),
                ),
                'default'     => 'auto',
                'description' => __( '放在页脚上方时，建议选择“强制衔接页脚”，避免和底部波浪断层。', 'developer-starter' ),
            ),
            array(
                'id'          => 'cta_footer_overlap',
                'label'       => __( '底部衔接重叠量', 'developer-starter' ),
                'type'        => 'text',
                'default'     => '',
                'description' => __( '如 2px、4px。控制与底部波浪的重叠像素，值越大越不容易出现白线。留空使用默认(2px)。', 'developer-starter' ),
            ),
            array(
                'id'          => 'cta_border_radius',
                'label'       => __( '板块圆角', 'developer-starter' ),
                'type'        => 'text',
                'default'     => '',
                'description' => __( '如 0、18px、28px。留空跟随板块样式。', 'developer-starter' ),
            ),
            array(
                'id'          => 'cta_content_max_width',
                'label'       => __( '内容最大宽度', 'developer-starter' ),
                'type'        => 'text',
                'default'     => '',
                'description' => __( '如 760px、980px。留空使用默认。', 'developer-starter' ),
            ),
            array(
                'id'      => 'cta_button_style',
                'label'   => __( '按钮样式', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'default' => __( '默认', 'developer-starter' ),
                    'pill'    => __( '胶囊按钮', 'developer-starter' ),
                    'soft'    => __( '柔和按钮', 'developer-starter' ),
                ),
                'default' => 'default',
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
        );
    }

    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        $title = isset( $data['cta_title'] ) && $data['cta_title'] !== ''
            ? $data['cta_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '准备好开始了吗？', 'Ready to get started?' ) : __( '准备好开始了吗？', 'developer-starter' ) );
        $subtitle = isset( $data['cta_subtitle'] )
            ? $data['cta_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即联系我们，获取专业方案和报价', 'Talk to us for a tailored plan and quotation.' ) : __( '立即联系我们，获取专业方案和报价', 'developer-starter' ) );
        $btn_text = isset( $data['cta_button_text'] ) && $data['cta_button_text'] !== ''
            ? $data['cta_button_text']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费咨询', 'Request a Quote' ) : __( '免费咨询', 'developer-starter' ) );
        $btn_url = isset( $data['cta_button_url'] ) ? $data['cta_button_url'] : '#';
        
        // Typography
        $title_size = isset( $data['cta_title_size'] ) ? $clean_css_value( $data['cta_title_size'] ) : '';
        $title_color = isset( $data['cta_title_color'] ) ? $clean_css_value( $data['cta_title_color'] ) : '';
        $subtitle_size = isset( $data['cta_subtitle_size'] ) ? $clean_css_value( $data['cta_subtitle_size'] ) : '';
        $subtitle_color = isset( $data['cta_subtitle_color'] ) ? $clean_css_value( $data['cta_subtitle_color'] ) : '';
        $button_bg_color = isset( $data['cta_button_bg_color'] ) ? $clean_css_value( $data['cta_button_bg_color'] ) : '';
        $button_text_color = isset( $data['cta_button_text_color'] ) ? $clean_css_value( $data['cta_button_text_color'] ) : '';
        $button_border_color = isset( $data['cta_button_border_color'] ) ? $clean_css_value( $data['cta_button_border_color'] ) : '';
        $button_hover_bg_color = isset( $data['cta_button_hover_bg_color'] ) ? $clean_css_value( $data['cta_button_hover_bg_color'] ) : '';
        $button_hover_text_color = isset( $data['cta_button_hover_text_color'] ) ? $clean_css_value( $data['cta_button_hover_text_color'] ) : '';
        $button_hover_border_color = isset( $data['cta_button_hover_border_color'] ) ? $clean_css_value( $data['cta_button_hover_border_color'] ) : '';
        
        // Background
        $bg_type = isset( $data['cta_bg_type'] ) ? $data['cta_bg_type'] : 'color';
        $bg_color = isset( $data['cta_bg_color'] ) && '' !== trim( (string) $data['cta_bg_color'] )
            ? $clean_css_value( $data['cta_bg_color'] )
            : ( isset( $data['cta_bg_gradient'] ) ? $clean_css_value( $data['cta_bg_gradient'] ) : '' );
        $bg_image = isset( $data['cta_bg_image'] ) ? $data['cta_bg_image'] : '';
        $overlay_opacity = isset( $data['cta_bg_overlay'] ) && $data['cta_bg_overlay'] !== '' ? $data['cta_bg_overlay'] : '0.5';

        // Layout.
        $layout_mode = isset( $data['cta_layout_mode'] ) ? sanitize_key( (string) $data['cta_layout_mode'] ) : 'standard';
        if ( ! in_array( $layout_mode, array( 'standard', 'card', 'seamless_footer' ), true ) ) {
            $layout_mode = 'standard';
        }
        $footer_bridge = isset( $data['cta_footer_bridge'] ) ? sanitize_key( (string) $data['cta_footer_bridge'] ) : 'auto';
        if ( ! in_array( $footer_bridge, array( 'auto', 'on', 'off' ), true ) ) {
            $footer_bridge = 'auto';
        }
        $button_style = isset( $data['cta_button_style'] ) ? sanitize_key( (string) $data['cta_button_style'] ) : 'default';
        if ( ! in_array( $button_style, array( 'default', 'pill', 'soft' ), true ) ) {
            $button_style = 'default';
        }
        $border_radius = isset( $data['cta_border_radius'] ) ? $clean_css_value( $data['cta_border_radius'] ) : '';
        $content_max_width = isset( $data['cta_content_max_width'] ) ? $clean_css_value( $data['cta_content_max_width'] ) : '';
        $footer_overlap = isset( $data['cta_footer_overlap'] ) ? $clean_css_value( $data['cta_footer_overlap'] ) : '';
        
        // Spacing
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $clean_css_value( $data['module_padding_top'] ) : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $clean_css_value( $data['module_padding_bottom'] ) : '80px';
        
        // CSS Generation
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( in_array( $bg_type, array( 'color', 'gradient' ), true ) && $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        } elseif ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('{$bg_image}');";
        }

        if ( $button_bg_color ) {
            $section_style .= "--qiling-cta-button-bg: {$button_bg_color};--qiling-cta-button-border: {$button_bg_color};";
        }
        if ( $button_text_color ) {
            $section_style .= "--qiling-cta-button-text: {$button_text_color};";
        }
        if ( $button_border_color ) {
            $section_style .= "--qiling-cta-button-border: {$button_border_color};";
        }
        if ( $button_hover_bg_color ) {
            $section_style .= "--qiling-cta-button-hover-bg: {$button_hover_bg_color};--qiling-cta-button-hover-border: {$button_hover_bg_color};";
        }
        if ( $button_hover_text_color ) {
            $section_style .= "--qiling-cta-button-hover-text: {$button_hover_text_color};";
        }
        if ( $button_hover_border_color ) {
            $section_style .= "--qiling-cta-button-hover-border: {$button_hover_border_color};";
        }
        if ( $border_radius ) {
            $section_style .= "--qiling-cta-radius: {$border_radius};";
        }
        if ( $content_max_width ) {
            $section_style .= "--qiling-cta-content-max: {$content_max_width};";
        }
        if ( $footer_overlap ) {
            $section_style .= "--qiling-cta-footer-overlap: -{$footer_overlap};";
        }
        
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        $classes = array(
            'module',
            'module-cta',
            'module-cta--layout-' . $layout_mode,
            'module-cta--bridge-' . $footer_bridge,
            'module-cta--button-' . $button_style,
        );
        if ( $bg_type === 'image' ) {
            $classes[] = 'has-bg-image';
        }
        ?>
        <section class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image ) : ?>
                <div class="cta-overlay" style="opacity: <?php echo esc_attr( $overlay_opacity ); ?>;"></div>
            <?php endif; ?>
            
            <!-- Decor: Left (Fluid Wave) -->
            <div class="module-decor d-left" style="width: 400px; height: 400px; left: -100px; bottom: -50px; top: auto; transform: none; opacity: 0.5;">
                <svg width="400" height="400" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M199.5 0C309.68 0 399 89.32 399 199.5C399 309.68 309.68 399 199.5 399C89.32 399 0 309.68 0 199.5C0 89.32 89.32 0 199.5 0ZM199.5 45C114.17 45 45 114.17 45 199.5C45 233.19 56.49 264.36 75.87 289.43C102.77 248.65 148.62 221.5 200.5 221.5C252.38 221.5 298.23 248.65 325.13 289.43C344.51 264.36 356 233.19 356 199.5C356 114.17 284.83 45 199.5 45Z" fill="currentColor" fill-opacity="0.05"/>
                    <path d="M0 250C50 220 120 280 180 250C240 220 300 180 400 220V400H0V250Z" fill="currentColor" fill-opacity="0.1"/>
                </svg>
            </div>

            <!-- Decor: Right (Geometric Burst) -->
            <div class="module-decor d-right" style="width: 300px; height: 300px; right: -50px; top: -50px; opacity: 0.5;">
                <svg width="300" height="300" viewBox="0 0 300 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="150" cy="150" r="100" stroke="currentColor" stroke-width="40" stroke-opacity="0.05"/>
                    <path d="M150 0L170 110L280 130L170 150L150 260L130 150L20 130L130 110L150 0Z" fill="currentColor" fill-opacity="0.1"/>
                    <circle cx="250" cy="50" r="20" fill="currentColor" fill-opacity="0.2"/>
                    <circle cx="50" cy="250" r="15" fill="currentColor" fill-opacity="0.15"/>
                    <rect x="220" y="220" width="30" height="30" transform="rotate(45 235 235)" stroke="currentColor" stroke-width="2" stroke-opacity="0.2"/>
                </svg>
            </div>

            <div class="container text-center">
                <div class="cta-content">
                    <h2 class="cta-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo wp_kses_post( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="cta-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo wp_kses_post( $subtitle ); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $btn_url ?: '#' ); ?>" class="btn btn-cta">
                        <?php echo wp_kses_post( $btn_text ); ?>
                    </a>
                </div>
            </div>
        </section>
        <?php
    }
}
