<?php
/**
 * Dynamic Banner Module - 动态SaaS风格首屏
 *
 * 包含打字特效、悬浮卡片、动态背景等
 * 参考风格：https://www.dcxh7.com/
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dynamic_Banner_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-superhero-alt'; // 使用一个看起来比较酷的图标
        $this->description = __( 'SaaS风格动态首屏，支持打字特效、悬浮卡片、动态背景', 'developer-starter' );
    }

    public function get_id() {
        return 'dynamic_banner';
    }

    public function get_name() {
        return __( '动态Banner模块', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // === 布局与背景 ===
            array(
                'id'      => 'db_height',
                'type'    => 'select',
                'label'   => __( '模块高度', 'developer-starter' ),
                'options' => array(
                    'auto'  => __( '自适应内容', 'developer-starter' ),
                    '100vh' => __( '全屏高度', 'developer-starter' ),
                    '80vh'  => __( '80%屏幕高度', 'developer-starter' ),
                    '70vh'  => __( '70%屏幕高度', 'developer-starter' ),
                    '600px' => __( '600px固定高度', 'developer-starter' ),
                ),
                'default' => 'auto',
            ),
            array(
                'id'      => 'db_bg_type',
                'type'    => 'select',
                'label'   => __( '背景类型', 'developer-starter' ),
                'options' => array(
                    'color'    => __( '纯色背景', 'developer-starter' ),
                    'gradient' => __( '渐变背景', 'developer-starter' ),
                    'image'    => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'gradient',
            ),
            array(
                'id'      => 'db_bg_color',
                'type'    => 'color',
                'label'   => __( '背景颜色（纯色模式）', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'db_bg_gradient',
                'type'    => 'text',
                'label'   => __( 'CSS渐变代码（渐变模式，如 linear-gradient(...)）', 'developer-starter' ),
                'default' => 'linear-gradient(135deg, var(--color-neutral-50) 0%, var(--color-neutral-300) 100%)',
            ),
            array(
                'id'      => 'db_bg_image',
                'type'    => 'image',
                'label'   => __( '背景图片', 'developer-starter' ),
            ),
            array(
                'id'          => 'db_bg_overlay',
                'type'        => 'text',
                'label'       => __( '图片遮罩透明度', 'developer-starter' ),
                'default'     => '',
                'description' => __( '0-1 之间的小数，留空不添加遮罩。', 'developer-starter' ),
            ),

            // === 标题内容 ===
            array(
                'id'      => 'db_title_prefix',
                'type'    => 'text',
                'label'   => __( '固定标题前缀', 'developer-starter' ),
                'default' => __( '启灵主题', 'developer-starter' ),
            ),
            array(
                'id'      => 'db_typing_mode',
                'type'    => 'select',
                'label'   => __( '打字特效模式', 'developer-starter' ),
                'options' => array(
                    'loop'  => __( '循环播放（每行文字轮播）', 'developer-starter' ),
                    'block' => __( '多行整段（一次性打出整段文字）', 'developer-starter' ),
                ),
                'default' => 'loop',
                'description' => __( '循环播放：每行文字作为一个独立的幻灯片播放。<br>多行整段：所有文字作为一个整体打出，回车键换行。', 'developer-starter' ),
            ),
            array(
                'id'      => 'db_typing_text',
                'type'    => 'textarea',
                'label'   => __( '打字特效文字（每行一个，循环播放）', 'developer-starter' ),
                'default' => __( "企业建站首选\nAI赋能创作\n高度模块化设计", 'developer-starter' ),
                'description' => __( '每一行文字将作为一个打字片段循环播放', 'developer-starter' ),
            ),
            array(
                'id'      => 'db_highlight_color',
                'type'    => 'color',
                'label'   => __( '打字文字颜色（高亮色）', 'developer-starter' ),
                'default' => 'var(--color-error)',
            ),
            array(
                'id'      => 'db_title_color',
                'type'    => 'color',
                'label'   => __( '固定标题颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-900)',
            ),
            
            // === 副标题与描述 ===
            array(
                'id'      => 'db_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题（加粗显示的口号）', 'developer-starter' ),
                'default' => sprintf( __( '<strong>功能强大</strong> 且 <strong style="color: var(--color-primary);">高度可定制</strong> 的%s主题', 'developer-starter' ), 'WordPress' ),
            ),
            array(
                'id'      => 'db_desc',
                'type'    => 'textarea',
                'label'   => __( '描述文案', 'developer-starter' ),
                'default' => __( '专为资源站、素材站、软件站打造。内置积分商城、VIP会员系统、推广返佣等核心功能，助您快速搭建商业化网站。支持全站模块化配置，像搭积木一样建设您的网站。', 'developer-starter' ),
            ),
            array(
                'id'      => 'db_text_color',
                'type'    => 'color',
                'label'   => __( '副标题颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-600)',
            ),
            array(
                'id'      => 'db_desc_color',
                'type'    => 'color',
                'label'   => __( '描述文案颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-600)',
            ),

            // === 按钮组 ===
            array(
                'id'         => 'db_buttons',
                'type'       => 'repeater',
                'label'      => __( '操作按钮', 'developer-starter' ),
                'add_button' => __( '添加按钮', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'text',
                        'type'  => 'text',
                        'label' => __( '按钮文字', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'link',
                        'type'  => 'text',
                        'label' => __( '按钮链接', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'style',
                        'type'    => 'select',
                        'label'   => __( '按钮样式', 'developer-starter' ),
                        'options' => array(
                            'primary'   => __( '主要按钮（主题色）', 'developer-starter' ),
                            'secondary' => __( '次要按钮（浅灰色）', 'developer-starter' ),
                            'outline'   => __( '描边按钮', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'    => 'icon',
                        'type'  => 'text',
                        'label' => __( '按钮图标class（支持 Symbol类名 或 Emoji）', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'          => 'db_primary_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '主按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认主按钮样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'db_primary_btn_text_color',
                'type'        => 'color',
                'label'       => __( '主按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认主按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'db_primary_btn_border_color', __( '主按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'          => 'db_primary_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '主按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认主按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'db_primary_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '主按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认主按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'db_primary_btn_hover_border_color', __( '主按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随主按钮悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id'          => 'db_secondary_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '次按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认次按钮样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'db_secondary_btn_text_color',
                'type'        => 'color',
                'label'       => __( '次按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认次按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'db_secondary_btn_border_color', __( '次按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'          => 'db_secondary_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '次按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认次按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'db_secondary_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '次按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用动态Banner默认次按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'db_secondary_btn_hover_border_color', __( '次按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随次按钮悬停背景颜色。', 'developer-starter' ) ),

            // === 右侧视觉区 ===
            array(
                'id'      => 'db_media_type',
                'type'    => 'select',
                'label'   => __( '右侧展示类型', 'developer-starter' ),
                'options' => array(
                    'image' => __( '静态图片', 'developer-starter' ),
                    'video' => __( '视频播放', 'developer-starter' ),
                ),
                'default' => 'image',
            ),
            array(
                'id'    => 'db_main_image',
                'type'  => 'image',
                'label' => __( '右侧主图（建议透明背景PNG或WebP）', 'developer-starter' ),
                'dependency' => array( 'db_media_type', '==', 'image' ),
            ),
            array(
                'id'    => 'db_video_url',
                'type'  => 'text',
                'label' => __( '视频地址 (MP4)', 'developer-starter' ),
                'dependency' => array( 'db_media_type', '==', 'video' ),
            ),
            array(
                'id'      => 'db_image_shadow',
                'type'    => 'select',
                'label'   => __( '主图/视频阴影效果', 'developer-starter' ),
                'options' => array(
                    'none'   => __( '无阴影', 'developer-starter' ),
                    'soft'   => __( '柔和阴影', 'developer-starter' ),
                    'strong' => __( '强烈阴影', 'developer-starter' ),
                    'glow'   => __( '发光效果', 'developer-starter' ),
                ),
                'default' => 'soft',
            ),

            // === 悬浮卡片 ===
            array(
                'id'         => 'db_floating_cards',
                'type'       => 'repeater',
                'label'      => __( '悬浮装饰卡片', 'developer-starter' ),
                'add_button' => __( '添加卡片', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'content_type',
                        'type'  => 'select',
                        'label' => __( '内容类型', 'developer-starter' ),
                        'options' => array(
                            'icon_text'  => __( '图标+文字', 'developer-starter' ),
                            'image'      => __( '纯图片', 'developer-starter' ),
                            'badge'      => __( '小徽章/胶囊', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题/主文字', 'developer-starter' ),
                        'dependency' => array( 'id' => 'content_type', 'value' => array( 'icon_text', 'badge' ) ),
                    ),
                    array(
                        'id'    => 'subtitle',
                        'type'  => 'text',
                        'label' => __( '副标题/说明', 'developer-starter' ),
                        'dependency' => array( 'id' => 'content_type', 'value' => 'icon_text' ),
                    ),
                    array(
                        'id'    => 'icon',
                        'type'  => 'text',
                        'label' => __( '图标class（支持 Symbol类名 或 Emoji）', 'developer-starter' ),
                        'dependency' => array( 'id' => 'content_type', 'value' => 'icon_text' ),
                    ),
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '图片', 'developer-starter' ),
                        'dependency' => array( 'id' => 'content_type', 'value' => 'image' ),
                    ),
                    array(
                        'id'    => 'pos_top',
                        'type'  => 'text',
                        'label' => __( 'Top位置（如 10% 或 -20px，留空则不设）', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'pos_left',
                        'type'  => 'text',
                        'label' => __( 'Left位置（如 10% 或 -20px，留空则不设）', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'pos_right',
                        'type'  => 'text',
                        'label' => __( 'Right位置（如 10% 或 -20px，留空则不设）', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'pos_bottom',
                        'type'  => 'text',
                        'label' => __( 'Bottom位置（如 10% 或 -20px，留空则不设）', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'animation',
                        'type'  => 'select',
                        'label' => __( '动画效果', 'developer-starter' ),
                        'options' => array(
                            'float' => __( '上下悬浮', 'developer-starter' ),
                            'pulse' => __( '呼吸脉冲', 'developer-starter' ),
                            'shake' => __( '轻微晃动', 'developer-starter' ),
                            'none'  => __( '静止', 'developer-starter' ),
                        ),
                        'default' => 'float',
                    ),
                    array(
                        'id'    => 'delay',
                        'type'  => 'text',
                        'label' => __( '动画延迟（如 0.5s）', 'developer-starter' ),
                        'default' => '0s',
                    ),
                ),
            ),
        );
    }

    private function sanitize_color_value( $value, $default = '' ) {
        $value = is_string( $value ) ? trim( $value ) : '';

        if ( '' === $value ) {
            return $default;
        }

        if ( preg_match( '/^var\(--[a-zA-Z0-9_-]+\)$/', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(rgba?|hsla?)\([^)]+\)$/', $value ) ) {
            return $value;
        }

        $hex = sanitize_hex_color( $value );

        return $hex ? $hex : $default;
    }

    public function render( $data = array() ) {
        // 获取配置
        $height = isset( $data['db_height'] ) ? $data['db_height'] : 'auto';
        $bg_type = isset( $data['db_bg_type'] ) ? $data['db_bg_type'] : 'gradient';
        $bg_value = '';
        if ( $bg_type === 'color' ) {
            $bg_value = isset( $data['db_bg_color'] ) ? $data['db_bg_color'] : 'var(--color-neutral-50)';
        } elseif ( $bg_type === 'gradient' ) {
            $bg_value = isset( $data['db_bg_gradient'] ) ? $data['db_bg_gradient'] : 'linear-gradient(135deg, var(--color-neutral-50) 0%, var(--color-neutral-300) 100%)';
        } elseif ( $bg_type === 'image' ) {
            $bg_image = isset( $data['db_bg_image'] ) ? $data['db_bg_image'] : '';
            $overlay_opacity = isset( $data['db_bg_overlay'] ) && is_numeric( $data['db_bg_overlay'] )
                ? max( 0, min( 1, (float) $data['db_bg_overlay'] ) )
                : 0;
            $bg_value = $overlay_opacity > 0
                ? 'linear-gradient(rgba(0, 0, 0, ' . $overlay_opacity . '), rgba(0, 0, 0, ' . $overlay_opacity . ')), url(' . $bg_image . ') center/cover no-repeat'
                : 'url(' . $bg_image . ') center/cover no-repeat';
        }

        // 文本内容
        $title_prefix = isset( $data['db_title_prefix'] ) ? $data['db_title_prefix'] : '';
        $typing_text = isset( $data['db_typing_text'] ) ? $data['db_typing_text'] : ''; // 换行符分隔
        $typing_mode = isset( $data['db_typing_mode'] ) ? $data['db_typing_mode'] : 'loop';
        
        $typing_strings = array();
        if ( ! empty( $typing_text ) ) {
            if ( $typing_mode === 'block' ) {
                // 多行整段模式：将所有文本作为一个整体，保留换行符（转换为<br>）
                // 允许HTML标签被 wp_kses_post 保留
                $typing_strings[] = nl2br( $typing_text );
            } else {
                // 循环播放模式：按行分割
                $lines = explode( "\n", $typing_text );
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( ! empty( $line ) ) {
                        // 兼容旧逻辑，如果是纯文本则直接用，如果有HTML标签也保留
                        $typing_strings[] = $line;
                    }
                }
            }
        }
        $typing_json = htmlspecialchars( json_encode( $typing_strings ), ENT_QUOTES, 'UTF-8' );
        
        $subtitle = isset( $data['db_subtitle'] ) ? $data['db_subtitle'] : '';
        $desc = isset( $data['db_desc'] ) ? $data['db_desc'] : '';
        
        // 颜色
        $highlight_color = isset( $data['db_highlight_color'] ) ? $data['db_highlight_color'] : 'var(--color-error)';
        $title_color = isset( $data['db_title_color'] ) ? $data['db_title_color'] : 'var(--color-neutral-900)';
        $text_color = isset( $data['db_text_color'] ) ? $data['db_text_color'] : 'var(--color-neutral-600)';
        $desc_color = isset( $data['db_desc_color'] ) && '' !== trim( (string) $data['db_desc_color'] )
            ? $data['db_desc_color']
            : $text_color;

        // 按钮
        $buttons = isset( $data['db_buttons'] ) ? $data['db_buttons'] : array();
        $primary_btn_bg_color         = $this->sanitize_color_value( isset( $data['db_primary_btn_bg_color'] ) ? $data['db_primary_btn_bg_color'] : '', '' );
        $primary_btn_text_color       = $this->sanitize_color_value( isset( $data['db_primary_btn_text_color'] ) ? $data['db_primary_btn_text_color'] : '', '' );
        $primary_btn_border_color     = $this->sanitize_color_value( isset( $data['db_primary_btn_border_color'] ) ? $data['db_primary_btn_border_color'] : '', '' );
        $primary_btn_hover_bg_color   = $this->sanitize_color_value( isset( $data['db_primary_btn_hover_bg_color'] ) ? $data['db_primary_btn_hover_bg_color'] : '', '' );
        $primary_btn_hover_text_color = $this->sanitize_color_value( isset( $data['db_primary_btn_hover_text_color'] ) ? $data['db_primary_btn_hover_text_color'] : '', '' );
        $primary_btn_hover_border_color = $this->sanitize_color_value( isset( $data['db_primary_btn_hover_border_color'] ) ? $data['db_primary_btn_hover_border_color'] : '', '' );
        $secondary_btn_bg_color         = $this->sanitize_color_value( isset( $data['db_secondary_btn_bg_color'] ) ? $data['db_secondary_btn_bg_color'] : '', '' );
        $secondary_btn_text_color       = $this->sanitize_color_value( isset( $data['db_secondary_btn_text_color'] ) ? $data['db_secondary_btn_text_color'] : '', '' );
        $secondary_btn_border_color     = $this->sanitize_color_value( isset( $data['db_secondary_btn_border_color'] ) ? $data['db_secondary_btn_border_color'] : '', '' );
        $secondary_btn_hover_bg_color   = $this->sanitize_color_value( isset( $data['db_secondary_btn_hover_bg_color'] ) ? $data['db_secondary_btn_hover_bg_color'] : '', '' );
        $secondary_btn_hover_text_color = $this->sanitize_color_value( isset( $data['db_secondary_btn_hover_text_color'] ) ? $data['db_secondary_btn_hover_text_color'] : '', '' );
        $secondary_btn_hover_border_color = $this->sanitize_color_value( isset( $data['db_secondary_btn_hover_border_color'] ) ? $data['db_secondary_btn_hover_border_color'] : '', '' );

        // 视觉区
        $media_type = isset( $data['db_media_type'] ) ? $data['db_media_type'] : 'image';
        $main_image = isset( $data['db_main_image'] ) ? $data['db_main_image'] : '';
        $video_url = isset( $data['db_video_url'] ) ? $data['db_video_url'] : '';
        // $video_poster removed
        
        $image_shadow = isset( $data['db_image_shadow'] ) ? $data['db_image_shadow'] : 'soft';
        $floating_cards = isset( $data['db_floating_cards'] ) ? $data['db_floating_cards'] : array();

        $module_id = 'db-' . uniqid();
        $section_classes = 'module module-dynamic-banner';
        $section_style = "--db-bg: {$bg_value}; --db-title-color: {$title_color}; --db-subtitle-color: {$text_color}; --db-desc-color: {$desc_color}; --db-outline-color: {$title_color};";
        if ( '' !== $primary_btn_bg_color ) {
            $section_style .= "--db-primary-btn-bg: {$primary_btn_bg_color}; --db-primary-btn-border: {$primary_btn_bg_color};";
        }
        if ( '' !== $primary_btn_text_color ) {
            $section_style .= "--db-primary-btn-text: {$primary_btn_text_color};";
        }
        if ( '' !== $primary_btn_border_color ) {
            $section_style .= "--db-primary-btn-border: {$primary_btn_border_color};";
        }
        if ( '' !== $primary_btn_hover_bg_color ) {
            $section_style .= "--db-primary-btn-hover-bg: {$primary_btn_hover_bg_color}; --db-primary-btn-hover-border: {$primary_btn_hover_bg_color};";
        }
        if ( '' !== $primary_btn_hover_text_color ) {
            $section_style .= "--db-primary-btn-hover-text: {$primary_btn_hover_text_color};";
        }
        if ( '' !== $primary_btn_hover_border_color ) {
            $section_style .= "--db-primary-btn-hover-border: {$primary_btn_hover_border_color};";
        }
        if ( '' !== $secondary_btn_bg_color ) {
            $section_style .= "--db-secondary-btn-bg: {$secondary_btn_bg_color}; --db-secondary-btn-border: {$secondary_btn_bg_color};";
        }
        if ( '' !== $secondary_btn_text_color ) {
            $section_style .= "--db-secondary-btn-text: {$secondary_btn_text_color};";
        }
        if ( '' !== $secondary_btn_border_color ) {
            $section_style .= "--db-secondary-btn-border: {$secondary_btn_border_color};";
        }
        if ( '' !== $secondary_btn_hover_bg_color ) {
            $section_style .= "--db-secondary-btn-hover-bg: {$secondary_btn_hover_bg_color}; --db-secondary-btn-hover-border: {$secondary_btn_hover_bg_color};";
        }
        if ( '' !== $secondary_btn_hover_text_color ) {
            $section_style .= "--db-secondary-btn-hover-text: {$secondary_btn_hover_text_color};";
        }
        if ( '' !== $secondary_btn_hover_border_color ) {
            $section_style .= "--db-secondary-btn-hover-border: {$secondary_btn_hover_border_color};";
        }
        if ( $height !== 'auto' ) {
            $section_classes .= ' db-has-fixed-height';
            $section_style .= " --db-min-height: {$height};";
        }
        ?>
        <section class="<?php echo esc_attr( $section_classes ); ?>" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container db-container">
                <div class="db-grid">
                    <!-- 左侧内容 -->
                    <div class="db-content">
                        <h1 class="db-title">
                            <span class="db-title-static"><?php echo esc_html( $title_prefix ); ?></span>
                            <br class="db-mobile-break">
                            <span class="db-title-typing-wrap">
                                <span class="db-title-typing" data-strings="<?php echo $typing_json; ?>"></span>
                                <span class="db-cursor">|</span>
                            </span>
                        </h1>
                        
                        <?php if ( $subtitle ) : ?>
                            <h2 class="db-subtitle"><?php echo wp_kses_post( $subtitle ); ?></h2>
                        <?php endif; ?>
                        
                        <?php if ( $desc ) : ?>
                            <p class="db-desc"><?php echo nl2br( wp_kses_post( $desc ) ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! empty( $buttons ) ) : ?>
                            <div class="db-actions">
                                <?php foreach ( $buttons as $btn ) : 
                                    $btn_class = 'db-btn db-btn-' . ( isset( $btn['style'] ) ? $btn['style'] : 'primary' );
                                    $btn_icon = isset( $btn['icon'] ) ? trim( $btn['icon'] ) : '';
                                ?>
                                    <a href="<?php echo esc_url( $btn['link'] ); ?>" class="<?php echo esc_attr( $btn_class ); ?>">
                                        <?php if ( ! empty( $btn_icon ) ) : 
                                            echo developer_starter_get_icon_html( $btn_icon );
                                        endif; ?>
                                        <?php echo esc_html( $btn['text'] ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 右侧视觉 -->
                    <div class="db-visual">
                        <div class="db-visual-inner">
                            <?php if ( $media_type === 'video' && $video_url ) : ?>
                                <video 
                                    src="<?php echo esc_url( $video_url ); ?>" 
                                    autoplay muted loop playsinline
                                    class="db-main-image shadow-<?php echo esc_attr( $image_shadow ); ?>"
                                ></video>
                            <?php elseif ( $media_type === 'image' && $main_image ) : ?>
                                <img src="<?php echo esc_url( $main_image ); ?>" class="db-main-image shadow-<?php echo esc_attr( $image_shadow ); ?>" alt="Banner Image">
                            <?php endif; ?>

                            <!-- 悬浮卡片 -->
                            <?php foreach ( $floating_cards as $index => $card ) : 
                                $card_style = '';
                                if ( ! empty( $card['pos_top'] ) ) $card_style .= "top: {$card['pos_top']};";
                                if ( ! empty( $card['pos_left'] ) ) $card_style .= "left: {$card['pos_left']};";
                                if ( ! empty( $card['pos_right'] ) ) $card_style .= "right: {$card['pos_right']};";
                                if ( ! empty( $card['pos_bottom'] ) ) $card_style .= "bottom: {$card['pos_bottom']};";
                                $card_style .= "animation-delay: " . ( isset( $card['delay'] ) ? $card['delay'] : '0s' ) . ";";
                                
                                $anim_class = 'anim-' . ( isset( $card['animation'] ) ? $card['animation'] : 'float' );
                                $card_type = isset( $card['content_type'] ) ? $card['content_type'] : 'icon_text';
                            ?>
                                <div class="db-float-card <?php echo esc_attr( $anim_class ); ?> type-<?php echo esc_attr( $card_type ); ?>" style="<?php echo esc_attr( $card_style ); ?>">
                                    <?php if ( $card_type === 'icon_text' ) : ?>
                                        <?php 
                                        $card_icon = isset( $card['icon'] ) ? trim( $card['icon'] ) : '';
                                        if ( ! empty( $card_icon ) ) : ?>
                                            <div class="db-card-icon">
                                                <?php echo developer_starter_get_icon_html( $card_icon ); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="db-card-content">
                                            <?php if ( ! empty( $card['title'] ) ) : ?>
                                                <div class="db-card-title"><?php echo esc_html( $card['title'] ); ?></div>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $card['subtitle'] ) ) : ?>
                                                <div class="db-card-subtitle"><?php echo esc_html( $card['subtitle'] ); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ( $card_type === 'image' && ! empty( $card['image'] ) ) : ?>
                                        <img src="<?php echo esc_url( $card['image'] ); ?>" alt="Float" class="db-card-image">
                                    <?php elseif ( $card_type === 'badge' ) : ?>
                                         <span class="db-badge-dot"></span>
                                         <span class="db-badge-text"><?php echo esc_html( $card['title'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script>
            (function() {
            function boot() {
                const moduleId = '<?php echo $module_id; ?>';
                const container = document.getElementById(moduleId);
                if (!container || container.dataset.dynamicBannerInitialized) return;
                container.dataset.dynamicBannerInitialized = 'true';

                // 打字特效逻辑
                const typingEl = container.querySelector('.db-title-typing');
                if (typingEl) {
                    const strings = JSON.parse(typingEl.getAttribute('data-strings') || '[]');
                    if (strings.length > 0) {
                        let strIndex = 0;
                        let charIndex = 0;
                        let isDeleting = false;
                        let typeSpeed = 100; // 打字速度
                        let waitTime = 2000; // 停留时间

                        function type() {
                            if (!container.isConnected) return;
                            const currentStr = strings[strIndex];
                            
                            if (isDeleting) {
                                charIndex--;
                                // 如果删除了一个闭合标签 '>'，则连同整个标签一起删除
                                if (currentStr[charIndex] === '>') {
                                    const openIndex = currentStr.lastIndexOf('<', charIndex);
                                    if (openIndex !== -1) {
                                        charIndex = openIndex;
                                    }
                                }
                                typingEl.innerHTML = currentStr.substring(0, charIndex);
                                typeSpeed = 30;
                            } else {
                                // 如果遇到标签开始 '<'，则跳过整个标签直接显示
                                if (currentStr[charIndex] === '<') {
                                    const closeIndex = currentStr.indexOf('>', charIndex);
                                    if (closeIndex !== -1) {
                                        charIndex = closeIndex + 1;
                                    } else {
                                        charIndex++;
                                    }
                                } else {
                                    charIndex++;
                                }
                                typingEl.innerHTML = currentStr.substring(0, charIndex);
                                typeSpeed = 100;
                            }

                            if (!isDeleting && charIndex >= currentStr.length) {
                                isDeleting = true;
                                typeSpeed = waitTime;
                            } else if (isDeleting && charIndex <= 0) {
                                isDeleting = false;
                                strIndex = (strIndex + 1) % strings.length;
                                typeSpeed = 500;
                                charIndex = 0; // Reset logic ensure clean start
                            }

                            setTimeout(type, typeSpeed);
                        }
                        
                        // 启动打字
                        setTimeout(type, 1000);
                    }
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot, { once: true });
            } else {
                boot();
            }
            })();
        </script>
        <?php
    }
}
