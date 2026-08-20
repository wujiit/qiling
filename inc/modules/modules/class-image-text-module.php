<?php
/**
 * Image Text Module - 图文模块
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
        $this->description = __( '图文组合布局', 'developer-starter' );
    }

    public function get_id() {
        return 'image_text';
    }

    public function get_name() {
        return __( '图文模块', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'image_text_layout', 'type' => 'select', 'label' => __( '图片位置', 'developer-starter' ), 'options' => array( 'left' => __( '左侧', 'developer-starter' ), 'right' => __( '右侧', 'developer-starter' ) ), 'default' => 'left' ),
            array( 'id' => 'image_text_image', 'type' => 'image', 'label' => __( '图片', 'developer-starter' ) ),
            
            array( 'id' => 'image_text_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '关于我们', 'About Us' ) : __( '关于我们', 'developer-starter' ) ),
            array(
                'id' => 'image_text_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'image_text_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            
            array(
                'id' => 'image_text_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
            ),
            array(
                'id' => 'image_text_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'image_text_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            
            array( 'id' => 'image_text_content', 'type' => 'editor', 'label' => __( '内容', 'developer-starter' ) ),
            array( 'id' => 'image_text_button', 'type' => 'text', 'label' => __( '按钮文字', 'developer-starter' ) ),
            array( 'id' => 'image_text_url', 'type' => 'text', 'label' => __( '按钮链接', 'developer-starter' ) ),
            array(
                'id'          => 'image_text_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'image_text_btn_text_color',
                'type'        => 'color',
                'label'       => __( '按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'image_text_btn_border_color' ),
            array(
                'id'          => 'image_text_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'image_text_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'image_text_btn_hover_border_color', __( '按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随按钮悬停背景颜色。', 'developer-starter' ) ),
            
            // Style Settings
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
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

    public function render( $data = array() ) {
        $layout = isset( $data['image_text_layout'] ) ? $data['image_text_layout'] : 'left';
        $image = isset( $data['image_text_image'] ) ? $data['image_text_image'] : '';
        $title = isset( $data['image_text_title'] ) ? $data['image_text_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '关于我们', 'About Us' ) : __( '关于我们', 'developer-starter' ) );
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
        
        // Dynamic Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
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
        <section class="module module-image-text" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <div class="<?php echo esc_attr( $grid_class ); ?>">
                    <div class="image-text-image">
                        <?php if ( $image ) : ?>
                            <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                        <?php else : ?>
                            <div class="placeholder-image"><?php esc_html_e( '请上传图片', 'developer-starter' ); ?></div>
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
        <?php
    }
}
