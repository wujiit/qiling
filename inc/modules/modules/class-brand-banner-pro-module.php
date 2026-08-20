<?php
/**
 * Brand Banner Pro Module - 品牌旗舰 Banner
 *
 * 支持上下/左右/反向左右/叠加四种布局，适配品牌首屏视觉。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Brand_Banner_Pro_Module extends Module_Base {

    /**
     * 当前请求中是否已输出过模块静态样式与脚本。
     *
     * @var bool
     */
    private static $assets_printed = false;

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-format-image';
        $this->description = __( '现代品牌首屏模块，支持上下/左右/叠加布局与动效。', 'developer-starter' );
    }

    public function get_id() {
        return 'brand_banner_pro';
    }

    public function get_name() {
        return __( '品牌旗舰Banner', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'bb_layout',
                'type'    => 'select',
                'label'   => __( '布局模式', 'developer-starter' ),
                'options' => array(
                    'top_bottom'     => __( '上文下图（苹果风）', 'developer-starter' ),
                    'left_right'     => __( '左文右图（品牌展示）', 'developer-starter' ),
                    'right_left'     => __( '右文左图（反向展示）', 'developer-starter' ),
                    'overlay_center' => __( '居中叠加（海报风）', 'developer-starter' ),
                ),
                'default' => 'top_bottom',
            ),
            array(
                'id'      => 'bb_style_mode',
                'type'    => 'select',
                'label'   => __( '视觉风格', 'developer-starter' ),
                'options' => array(
                    'minimal'   => __( '简洁（Apple 风）', 'developer-starter' ),
                    'glow'      => __( '氛围光感（ATMOS 风）', 'developer-starter' ),
                    'editorial' => __( '大片（运动品牌风）', 'developer-starter' ),
                ),
                'default' => 'minimal',
            ),
            array(
                'id'      => 'bb_overlay_content_box',
                'type'    => 'select',
                'label'   => __( '居中叠加内容容器', 'developer-starter' ),
                'options' => array(
                    'none'  => __( '无（参考图风格）', 'developer-starter' ),
                    'glass' => __( '磨砂玻璃卡片', 'developer-starter' ),
                ),
                'default' => 'none',
            ),
            array(
                'id'      => 'bb_height',
                'type'    => 'select',
                'label'   => __( '模块高度', 'developer-starter' ),
                'options' => array(
                    '70vh'   => __( '70vh', 'developer-starter' ),
                    '85vh'   => __( '85vh', 'developer-starter' ),
                    '100vh'  => __( '100vh（全屏）', 'developer-starter' ),
                    'custom' => __( '自定义', 'developer-starter' ),
                ),
                'default' => '85vh',
            ),
            array(
                'id'         => 'bb_height_custom',
                'type'       => 'text',
                'label'      => __( '自定义高度', 'developer-starter' ),
                'default'    => '760px',
                'dependency' => array( 'bb_height', '==', 'custom' ),
            ),
            array(
                'id'      => 'bb_bg_type',
                'type'    => 'select',
                'label'   => __( '背景类型', 'developer-starter' ),
                'options' => array(
                    'gradient' => __( '渐变背景', 'developer-starter' ),
                    'color'    => __( '纯色背景', 'developer-starter' ),
                    'image'    => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'gradient',
            ),
            array(
                'id'         => 'bb_bg_gradient',
                'type'       => 'text',
                'label'      => __( '渐变 CSS', 'developer-starter' ),
                'default'    => 'linear-gradient(180deg, var(--qiling-color-eef2ff) 0%, var(--qiling-color-dbeafe) 50%, var(--color-neutral-50) 100%)',
                'dependency' => array( 'bb_bg_type', '==', 'gradient' ),
            ),
            array(
                'id'         => 'bb_bg_color',
                'type'       => 'color',
                'label'      => __( '背景颜色', 'developer-starter' ),
                'default'    => '',
                'dependency' => array( 'bb_bg_type', '==', 'color' ),
            ),
            array(
                'id'         => 'bb_bg_image',
                'type'       => 'image',
                'label'      => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'bb_bg_type', '==', 'image' ),
            ),
            array(
                'id'         => 'bb_bg_overlay',
                'type'       => 'text',
                'label'      => __( '图片背景遮罩（0-0.9）', 'developer-starter' ),
                'default'    => '0.12',
                'dependency' => array( 'bb_bg_type', '==', 'image' ),
            ),

            array(
                'id'      => 'bb_title_mode',
                'type'    => 'select',
                'label'   => __( '主视觉标题类型', 'developer-starter' ),
                'options' => array(
                    'text'  => __( '文字（可放大）', 'developer-starter' ),
                    'image' => __( '图片（可放大）', 'developer-starter' ),
                ),
                'default' => 'text',
            ),
            array(
                'id'      => 'bb_title',
                'type'    => 'textarea',
                'label'   => __( '主标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '让品牌首屏，<br><strong>更高级、更现代</strong>', 'A premium hero for modern digital brands.' )
                    : __( '让品牌首屏，<br><strong>更高级、更现代</strong>', 'developer-starter' ),
            ),
            array(
                'id'         => 'bb_title_image',
                'type'       => 'image',
                'label'      => __( '主视觉标题图片', 'developer-starter' ),
                'dependency' => array( 'bb_title_mode', '==', 'image' ),
            ),
            array(
                'id'      => 'bb_title_size_max',
                'type'    => 'text',
                'label'   => __( '主标题最大字号', 'developer-starter' ),
                'default' => '88px',
            ),
            array(
                'id'      => 'bb_title_image_max_width',
                'type'    => 'text',
                'label'   => __( '主视觉标题图片最大宽度', 'developer-starter' ),
                'default' => '760px',
            ),
            array(
                'id'      => 'bb_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '不是普通横幅，而是有品牌气场的第一屏。', 'Not just a banner, but your brand statement above the fold.' )
                    : __( '不是普通横幅，而是有品牌气场的第一屏。', 'developer-starter' ),
            ),
            array(
                'id'      => 'bb_desc',
                'type'    => 'textarea',
                'label'   => __( '描述文案', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '支持上文下图、左右图文、居中叠加等布局，适合官网、产品页、活动页首屏展示。', 'Supports top-bottom, left-right and overlay layouts for official sites, product launches, and campaign pages.' )
                    : __( '支持上文下图、左右图文、居中叠加等布局，适合官网、产品页、活动页首屏展示。', 'developer-starter' ),
            ),
            array(
                'id'      => 'bb_orbit_text',
                'type'    => 'text',
                'label'   => __( '环形文字（可选）', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'bb_orbit_anchor',
                'type'    => 'select',
                'label'   => __( '环形文字位置', 'developer-starter' ),
                'options' => array(
                    'title'  => __( '标题右上（推荐）', 'developer-starter' ),
                    'corner' => __( '模块右上角', 'developer-starter' ),
                ),
                'default' => 'title',
            ),
            array(
                'id'      => 'bb_orbit_size',
                'type'    => 'text',
                'label'   => __( '环形文字尺寸', 'developer-starter' ),
                'default' => '120px',
            ),
            array(
                'id'      => 'bb_orbit_top',
                'type'    => 'text',
                'label'   => __( '环形文字垂直偏移（可负值）', 'developer-starter' ),
                'default' => '0px',
            ),
            array(
                'id'      => 'bb_orbit_right',
                'type'    => 'text',
                'label'   => __( '环形文字水平偏移（可负值）', 'developer-starter' ),
                'default' => '0px',
            ),
            array(
                'id'      => 'bb_orbit_color',
                'type'    => 'color',
                'label'   => __( '环形文字颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'bb_orbit_center_type',
                'type'    => 'select',
                'label'   => __( '环形中心内容类型', 'developer-starter' ),
                'options' => array(
                    'text'  => __( '文字/符号', 'developer-starter' ),
                    'image' => __( '图片', 'developer-starter' ),
                ),
                'default' => 'text',
            ),
            array(
                'id'      => 'bb_orbit_center_symbol',
                'type'    => 'text',
                'label'   => __( '环形中心符号/文字', 'developer-starter' ),
                'default' => '↻',
                'dependency' => array( 'bb_orbit_center_type', '==', 'text' ),
            ),
            array(
                'id'      => 'bb_orbit_center_image',
                'type'    => 'image',
                'label'   => __( '环形中心图片', 'developer-starter' ),
                'dependency' => array( 'bb_orbit_center_type', '==', 'image' ),
            ),
            array(
                'id'      => 'bb_text_align',
                'type'    => 'select',
                'label'   => __( '文字对齐', 'developer-starter' ),
                'options' => array(
                    'auto'   => __( '自动', 'developer-starter' ),
                    'left'   => __( '左对齐', 'developer-starter' ),
                    'center' => __( '居中', 'developer-starter' ),
                ),
                'default' => 'auto',
            ),
            array(
                'id'      => 'bb_title_color',
                'type'    => 'color',
                'label'   => __( '标题颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'bb_title_effect',
                'type'    => 'select',
                'label'   => __( '标题特效', 'developer-starter' ),
                'options' => array(
                    'normal'   => __( '普通', 'developer-starter' ),
                    'outline'  => __( '描边字', 'developer-starter' ),
                    'gradient' => __( '渐变字', 'developer-starter' ),
                    'glow'     => __( '发光字', 'developer-starter' ),
                ),
                'default' => 'normal',
            ),
            array(
                'id'      => 'bb_title_stroke_color',
                'type'    => 'color',
                'label'   => __( '描边颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'bb_title_stroke_width',
                'type'    => 'text',
                'label'   => __( '描边宽度', 'developer-starter' ),
                'default' => '1.5px',
            ),
            array(
                'id'      => 'bb_title_gradient',
                'type'    => 'text',
                'label'   => __( '标题渐变 CSS', 'developer-starter' ),
                'default' => 'linear-gradient(180deg, var(--color-neutral-0) 0%, var(--qiling-color-dbeafe) 65%, var(--qiling-color-93c5fd) 100%)',
            ),
            array(
                'id'      => 'bb_text_color',
                'type'    => 'color',
                'label'   => __( '正文颜色', 'developer-starter' ),
                'default' => '',
            ),

            array(
                'id'         => 'bb_buttons',
                'type'       => 'repeater',
                'label'      => __( '按钮组', 'developer-starter' ),
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
                            'primary' => __( '主按钮', 'developer-starter' ),
                            'light'   => __( '浅色按钮', 'developer-starter' ),
                            'outline' => __( '描边按钮', 'developer-starter' ),
                        ),
                        'default' => 'primary',
                    ),
                    array(
                        'id'    => 'icon',
                        'type'  => 'text',
                        'label' => __( '图标类名 / Emoji（可选）', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'          => 'bb_primary_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '主按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认主按钮样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'bb_primary_btn_text_color',
                'type'        => 'color',
                'label'       => __( '主按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认主按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'bb_primary_btn_border_color', __( '主按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'          => 'bb_primary_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '主按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认主按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'bb_primary_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '主按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认主按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'bb_primary_btn_hover_border_color', __( '主按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随主按钮悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id'          => 'bb_light_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '浅色按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认浅色按钮样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'bb_light_btn_text_color',
                'type'        => 'color',
                'label'       => __( '浅色按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认浅色按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'bb_light_btn_border_color', __( '浅色按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'          => 'bb_light_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '浅色按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认浅色按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'bb_light_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '浅色按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用品牌旗舰Banner默认浅色按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'bb_light_btn_hover_border_color', __( '浅色按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随浅色按钮悬停背景颜色。', 'developer-starter' ) ),

            array(
                'id'      => 'bb_media_type',
                'type'    => 'select',
                'label'   => __( '主视觉类型', 'developer-starter' ),
                'options' => array(
                    'image' => __( '图片', 'developer-starter' ),
                    'video' => __( '视频', 'developer-starter' ),
                    'none'  => __( '不显示主视觉', 'developer-starter' ),
                ),
                'default' => 'image',
            ),
            array(
                'id'         => 'bb_main_image',
                'type'       => 'image',
                'label'      => __( '主视觉图片', 'developer-starter' ),
                'dependency' => array( 'bb_media_type', '==', 'image' ),
            ),
            array(
                'id'         => 'bb_video_url',
                'type'       => 'text',
                'label'      => __( '视频地址（MP4）', 'developer-starter' ),
                'dependency' => array( 'bb_media_type', '==', 'video' ),
            ),
            array(
                'id'      => 'bb_media_shadow',
                'type'    => 'select',
                'label'   => __( '主视觉阴影', 'developer-starter' ),
                'options' => array(
                    'none'   => __( '无阴影', 'developer-starter' ),
                    'soft'   => __( '柔和', 'developer-starter' ),
                    'lifted' => __( '悬浮', 'developer-starter' ),
                    'glow'   => __( '发光', 'developer-starter' ),
                ),
                'default' => 'soft',
            ),
            array(
                'id'      => 'bb_media_radius',
                'type'    => 'text',
                'label'   => __( '主视觉圆角', 'developer-starter' ),
                'default' => '26px',
            ),
            array(
                'id'      => 'bb_media_max_width',
                'type'    => 'text',
                'label'   => __( '主视觉最大宽度', 'developer-starter' ),
                'default' => '980px',
            ),

            array(
                'id'      => 'bb_intro_anim',
                'type'    => 'select',
                'label'   => __( '文案入场动画', 'developer-starter' ),
                'options' => array(
                    'fade_up'  => __( '淡入上移', 'developer-starter' ),
                    'slide_up' => __( '滑入上移', 'developer-starter' ),
                    'zoom'     => __( '轻微缩放', 'developer-starter' ),
                    'none'     => __( '无', 'developer-starter' ),
                ),
                'default' => 'fade_up',
            ),
            array(
                'id'      => 'bb_media_anim',
                'type'    => 'select',
                'label'   => __( '主视觉动画', 'developer-starter' ),
                'options' => array(
                    'float' => __( '上下悬浮', 'developer-starter' ),
                    'pulse' => __( '呼吸动效', 'developer-starter' ),
                    'tilt'  => __( '鼠标倾斜', 'developer-starter' ),
                    'none'  => __( '无', 'developer-starter' ),
                ),
                'default' => 'float',
            ),
            array(
                'id'      => 'bb_show_glow',
                'type'    => 'select',
                'label'   => __( '显示氛围光效', 'developer-starter' ),
                'options' => array(
                    '1' => __( '显示', 'developer-starter' ),
                    '0' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'bb_padding_top',
                'type'    => 'text',
                'label'   => __( '上内边距', 'developer-starter' ),
                'default' => '72px',
            ),
            array(
                'id'      => 'bb_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下内边距', 'developer-starter' ),
                'default' => '72px',
            ),
        );
    }

    public function render( $data = array() ) {
        $layout = isset( $data['bb_layout'] ) ? sanitize_key( $data['bb_layout'] ) : 'top_bottom';
        if ( ! in_array( $layout, array( 'top_bottom', 'left_right', 'right_left', 'overlay_center' ), true ) ) {
            $layout = 'top_bottom';
        }

        $style_mode = isset( $data['bb_style_mode'] ) ? sanitize_key( $data['bb_style_mode'] ) : 'minimal';
        if ( ! in_array( $style_mode, array( 'minimal', 'glow', 'editorial' ), true ) ) {
            $style_mode = 'minimal';
        }
        $overlay_content_box = isset( $data['bb_overlay_content_box'] ) ? sanitize_key( $data['bb_overlay_content_box'] ) : 'none';
        if ( ! in_array( $overlay_content_box, array( 'none', 'glass' ), true ) ) {
            $overlay_content_box = 'none';
        }

        $height_mode = isset( $data['bb_height'] ) ? sanitize_key( $data['bb_height'] ) : '85vh';
        $height_value = in_array( $height_mode, array( '70vh', '85vh', '100vh' ), true ) ? $height_mode : '85vh';
        if ( 'custom' === $height_mode ) {
            $height_value = $this->sanitize_length( isset( $data['bb_height_custom'] ) ? $data['bb_height_custom'] : '', '760px' );
        }

        $bg_type = isset( $data['bb_bg_type'] ) ? sanitize_key( $data['bb_bg_type'] ) : 'gradient';
        if ( ! in_array( $bg_type, array( 'gradient', 'color', 'image' ), true ) ) {
            $bg_type = 'gradient';
        }

        $stage_style = '';
        if ( 'color' === $bg_type ) {
            $color = $this->sanitize_hex( isset( $data['bb_bg_color'] ) ? $data['bb_bg_color'] : '', 'var(--qiling-color-f3f4f6)' );
            $stage_style = 'background:' . $color . ';';
        } elseif ( 'image' === $bg_type ) {
            $bg_image = isset( $data['bb_bg_image'] ) ? esc_url_raw( $data['bb_bg_image'] ) : '';
            if ( $bg_image ) {
                $stage_style = 'background-image:url(' . esc_url_raw( $bg_image ) . ');background-size:cover;background-position:center;background-repeat:no-repeat;';
            } else {
                $stage_style = 'background:var(--color-neutral-900);';
            }
        } else {
            $gradient = $this->sanitize_gradient(
                isset( $data['bb_bg_gradient'] ) ? $data['bb_bg_gradient'] : '',
                'linear-gradient(180deg, var(--qiling-color-eef2ff) 0%, var(--qiling-color-dbeafe) 50%, var(--color-neutral-50) 100%)'
            );
            $stage_style = 'background:' . $gradient . ';';
        }

        $overlay_opacity = ( 'image' === $bg_type )
            ? $this->sanitize_opacity( isset( $data['bb_bg_overlay'] ) ? $data['bb_bg_overlay'] : '0.12', '0.12' )
            : '0';

        $title = isset( $data['bb_title'] ) ? (string) $data['bb_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '让品牌首屏，<br><strong>更高级、更现代</strong>', 'A premium hero for modern digital brands.' ) : __( '让品牌首屏，<br><strong>更高级、更现代</strong>', 'developer-starter' ) );
        $subtitle = isset( $data['bb_subtitle'] ) ? (string) $data['bb_subtitle'] : '';
        $desc = isset( $data['bb_desc'] ) ? (string) $data['bb_desc'] : '';
        $title_mode = isset( $data['bb_title_mode'] ) ? sanitize_key( $data['bb_title_mode'] ) : 'text';
        if ( ! in_array( $title_mode, array( 'text', 'image' ), true ) ) {
            $title_mode = 'text';
        }
        $title_image = isset( $data['bb_title_image'] ) ? esc_url_raw( $data['bb_title_image'] ) : '';
        if ( 'image' === $title_mode && empty( $title_image ) ) {
            $title_mode = 'text';
        }
        $default_title_size_max = ( 'glow' === $style_mode && 'overlay_center' === $layout ) ? '220px' : '88px';
        $title_size_max = $this->sanitize_length( isset( $data['bb_title_size_max'] ) ? $data['bb_title_size_max'] : '', $default_title_size_max );
        $default_title_image_max_width = ( 'glow' === $style_mode && 'overlay_center' === $layout ) ? '860px' : '760px';
        $title_image_max_width = $this->sanitize_length( isset( $data['bb_title_image_max_width'] ) ? $data['bb_title_image_max_width'] : '', $default_title_image_max_width );
        $orbit_text = isset( $data['bb_orbit_text'] ) ? (string) $data['bb_orbit_text'] : '';
        $orbit_anchor = isset( $data['bb_orbit_anchor'] ) ? sanitize_key( $data['bb_orbit_anchor'] ) : 'title';
        if ( ! in_array( $orbit_anchor, array( 'title', 'corner' ), true ) ) {
            $orbit_anchor = 'title';
        }
        $orbit_size = $this->sanitize_length( isset( $data['bb_orbit_size'] ) ? $data['bb_orbit_size'] : '', '120px' );
        $orbit_top = $this->sanitize_length( isset( $data['bb_orbit_top'] ) ? $data['bb_orbit_top'] : '', '0px' );
        $orbit_right = $this->sanitize_length( isset( $data['bb_orbit_right'] ) ? $data['bb_orbit_right'] : '', '0px' );
        $orbit_color = $this->sanitize_hex( isset( $data['bb_orbit_color'] ) ? $data['bb_orbit_color'] : '', 'var(--color-border)' );
        $orbit_center_type = isset( $data['bb_orbit_center_type'] ) ? sanitize_key( $data['bb_orbit_center_type'] ) : 'text';
        if ( ! in_array( $orbit_center_type, array( 'text', 'image' ), true ) ) {
            $orbit_center_type = 'text';
        }
        $orbit_center_symbol = isset( $data['bb_orbit_center_symbol'] ) ? trim( (string) $data['bb_orbit_center_symbol'] ) : '↻';
        if ( '' === $orbit_center_symbol ) {
            $orbit_center_symbol = '↻';
        }
        $orbit_center_image = isset( $data['bb_orbit_center_image'] ) ? esc_url_raw( $data['bb_orbit_center_image'] ) : '';

        $title_color = $this->sanitize_hex( isset( $data['bb_title_color'] ) ? $data['bb_title_color'] : '', 'var(--color-neutral-900)' );
        $title_effect = isset( $data['bb_title_effect'] ) ? sanitize_key( $data['bb_title_effect'] ) : 'normal';
        if ( ! in_array( $title_effect, array( 'normal', 'outline', 'gradient', 'glow' ), true ) ) {
            $title_effect = 'normal';
        }
        $title_stroke_color = $this->sanitize_hex( isset( $data['bb_title_stroke_color'] ) ? $data['bb_title_stroke_color'] : '', 'var(--color-neutral-0)' );
        $title_stroke_width = $this->sanitize_length( isset( $data['bb_title_stroke_width'] ) ? $data['bb_title_stroke_width'] : '', '1.5px' );
        $title_gradient = $this->sanitize_gradient(
            isset( $data['bb_title_gradient'] ) ? $data['bb_title_gradient'] : '',
            'linear-gradient(180deg, var(--color-neutral-0) 0%, var(--qiling-color-dbeafe) 65%, var(--qiling-color-93c5fd) 100%)'
        );
        $text_color = $this->sanitize_hex( isset( $data['bb_text_color'] ) ? $data['bb_text_color'] : '', 'var(--color-neutral-700)' );

        $align_mode = isset( $data['bb_text_align'] ) ? sanitize_key( $data['bb_text_align'] ) : 'auto';
        if ( ! in_array( $align_mode, array( 'auto', 'left', 'center' ), true ) ) {
            $align_mode = 'auto';
        }
        $content_align = $align_mode;
        if ( 'auto' === $content_align ) {
            $content_align = in_array( $layout, array( 'top_bottom', 'overlay_center' ), true ) ? 'center' : 'left';
        }

        $buttons = isset( $data['bb_buttons'] ) && is_array( $data['bb_buttons'] )
            ? $data['bb_buttons']
            : $this->get_default_buttons();
        if ( empty( $buttons ) ) {
            $buttons = $this->get_default_buttons();
        }

        $primary_btn_bg_color         = $this->sanitize_color_value( isset( $data['bb_primary_btn_bg_color'] ) ? $data['bb_primary_btn_bg_color'] : '', '' );
        $primary_btn_text_color       = $this->sanitize_color_value( isset( $data['bb_primary_btn_text_color'] ) ? $data['bb_primary_btn_text_color'] : '', '' );
        $primary_btn_border_color     = $this->sanitize_color_value( isset( $data['bb_primary_btn_border_color'] ) ? $data['bb_primary_btn_border_color'] : '', '' );
        $primary_btn_hover_bg_color   = $this->sanitize_color_value( isset( $data['bb_primary_btn_hover_bg_color'] ) ? $data['bb_primary_btn_hover_bg_color'] : '', '' );
        $primary_btn_hover_text_color = $this->sanitize_color_value( isset( $data['bb_primary_btn_hover_text_color'] ) ? $data['bb_primary_btn_hover_text_color'] : '', '' );
        $primary_btn_hover_border_color = $this->sanitize_color_value( isset( $data['bb_primary_btn_hover_border_color'] ) ? $data['bb_primary_btn_hover_border_color'] : '', '' );
        $light_btn_bg_color           = $this->sanitize_color_value( isset( $data['bb_light_btn_bg_color'] ) ? $data['bb_light_btn_bg_color'] : '', '' );
        $light_btn_text_color         = $this->sanitize_color_value( isset( $data['bb_light_btn_text_color'] ) ? $data['bb_light_btn_text_color'] : '', '' );
        $light_btn_border_color       = $this->sanitize_color_value( isset( $data['bb_light_btn_border_color'] ) ? $data['bb_light_btn_border_color'] : '', '' );
        $light_btn_hover_bg_color     = $this->sanitize_color_value( isset( $data['bb_light_btn_hover_bg_color'] ) ? $data['bb_light_btn_hover_bg_color'] : '', '' );
        $light_btn_hover_text_color   = $this->sanitize_color_value( isset( $data['bb_light_btn_hover_text_color'] ) ? $data['bb_light_btn_hover_text_color'] : '', '' );
        $light_btn_hover_border_color = $this->sanitize_color_value( isset( $data['bb_light_btn_hover_border_color'] ) ? $data['bb_light_btn_hover_border_color'] : '', '' );

        $media_type = isset( $data['bb_media_type'] ) ? sanitize_key( $data['bb_media_type'] ) : 'image';
        if ( ! in_array( $media_type, array( 'image', 'video', 'none' ), true ) ) {
            $media_type = 'image';
        }
        $main_image = isset( $data['bb_main_image'] ) ? esc_url_raw( $data['bb_main_image'] ) : '';
        $video_url = isset( $data['bb_video_url'] ) ? esc_url_raw( $data['bb_video_url'] ) : '';
        $has_media = ( 'image' === $media_type && ! empty( $main_image ) ) || ( 'video' === $media_type && ! empty( $video_url ) );

        $media_shadow = isset( $data['bb_media_shadow'] ) ? sanitize_key( $data['bb_media_shadow'] ) : 'soft';
        if ( ! in_array( $media_shadow, array( 'none', 'soft', 'lifted', 'glow' ), true ) ) {
            $media_shadow = 'soft';
        }

        $media_radius = $this->sanitize_length( isset( $data['bb_media_radius'] ) ? $data['bb_media_radius'] : '', '26px' );
        $media_max_width = $this->sanitize_length( isset( $data['bb_media_max_width'] ) ? $data['bb_media_max_width'] : '', '980px' );

        $intro_anim = isset( $data['bb_intro_anim'] ) ? sanitize_key( $data['bb_intro_anim'] ) : 'fade_up';
        if ( ! in_array( $intro_anim, array( 'fade_up', 'slide_up', 'zoom', 'none' ), true ) ) {
            $intro_anim = 'fade_up';
        }

        $media_anim = isset( $data['bb_media_anim'] ) ? sanitize_key( $data['bb_media_anim'] ) : 'float';
        if ( ! in_array( $media_anim, array( 'float', 'pulse', 'tilt', 'none' ), true ) ) {
            $media_anim = 'float';
        }

        $show_glow = isset( $data['bb_show_glow'] ) ? (string) $data['bb_show_glow'] : '1';
        $show_glow = ( '0' === $show_glow ) ? '0' : '1';

        $padding_top = $this->sanitize_length( isset( $data['bb_padding_top'] ) ? $data['bb_padding_top'] : '', '72px' );
        $padding_bottom = $this->sanitize_length( isset( $data['bb_padding_bottom'] ) ? $data['bb_padding_bottom'] : '', '72px' );

        $uid = 'bbp_' . wp_unique_id();

        $instance_style = '--bbp-height:' . $height_value . ';';
        $instance_style .= '--bbp-title-color:' . $title_color . ';';
        $instance_style .= '--bbp-title-stroke-color:' . $title_stroke_color . ';';
        $instance_style .= '--bbp-title-stroke-width:' . $title_stroke_width . ';';
        $instance_style .= '--bbp-title-gradient:' . $title_gradient . ';';
        $instance_style .= '--bbp-title-size-max:' . $title_size_max . ';';
        $instance_style .= '--bbp-title-image-max-width:' . $title_image_max_width . ';';
        $instance_style .= '--bbp-text-color:' . $text_color . ';';
        $instance_style .= '--bbp-bg-overlay:' . $overlay_opacity . ';';
        $instance_style .= '--bbp-media-radius:' . $media_radius . ';';
        $instance_style .= '--bbp-media-max-width:' . $media_max_width . ';';
        $instance_style .= '--bbp-orbit-size:' . $orbit_size . ';';
        $instance_style .= '--bbp-orbit-top:' . $orbit_top . ';';
        $instance_style .= '--bbp-orbit-right:' . $orbit_right . ';';
        $instance_style .= '--bbp-orbit-color:' . $orbit_color . ';';
        $instance_style .= '--bbp-pt:' . $padding_top . ';';
        $instance_style .= '--bbp-pb:' . $padding_bottom . ';';
        if ( '' !== $primary_btn_bg_color ) {
            $instance_style .= '--bbp-primary-btn-bg:' . $primary_btn_bg_color . ';';
            $instance_style .= '--bbp-primary-btn-border:' . $primary_btn_bg_color . ';';
        }
        if ( '' !== $primary_btn_text_color ) {
            $instance_style .= '--bbp-primary-btn-text:' . $primary_btn_text_color . ';';
        }
        if ( '' !== $primary_btn_border_color ) {
            $instance_style .= '--bbp-primary-btn-border:' . $primary_btn_border_color . ';';
        }
        if ( '' !== $primary_btn_hover_bg_color ) {
            $instance_style .= '--bbp-primary-btn-hover-bg:' . $primary_btn_hover_bg_color . ';';
            $instance_style .= '--bbp-primary-btn-hover-border:' . $primary_btn_hover_bg_color . ';';
        }
        if ( '' !== $primary_btn_hover_text_color ) {
            $instance_style .= '--bbp-primary-btn-hover-text:' . $primary_btn_hover_text_color . ';';
        }
        if ( '' !== $primary_btn_hover_border_color ) {
            $instance_style .= '--bbp-primary-btn-hover-border:' . $primary_btn_hover_border_color . ';';
        }
        if ( '' !== $light_btn_bg_color ) {
            $instance_style .= '--bbp-light-btn-bg:' . $light_btn_bg_color . ';';
            $instance_style .= '--bbp-light-btn-border:' . $light_btn_bg_color . ';';
        }
        if ( '' !== $light_btn_text_color ) {
            $instance_style .= '--bbp-light-btn-text:' . $light_btn_text_color . ';';
        }
        if ( '' !== $light_btn_border_color ) {
            $instance_style .= '--bbp-light-btn-border:' . $light_btn_border_color . ';';
        }
        if ( '' !== $light_btn_hover_bg_color ) {
            $instance_style .= '--bbp-light-btn-hover-bg:' . $light_btn_hover_bg_color . ';';
            $instance_style .= '--bbp-light-btn-hover-border:' . $light_btn_hover_bg_color . ';';
        }
        if ( '' !== $light_btn_hover_text_color ) {
            $instance_style .= '--bbp-light-btn-hover-text:' . $light_btn_hover_text_color . ';';
        }
        if ( '' !== $light_btn_hover_border_color ) {
            $instance_style .= '--bbp-light-btn-hover-border:' . $light_btn_hover_border_color . ';';
        }

        $section_classes = array(
            'module',
            'module-brand-banner-pro',
            'bbp-layout-' . $layout,
            'bbp-style-' . $style_mode,
            'bbp-overlay-box-' . $overlay_content_box,
            'bbp-title-effect-' . $title_effect,
            'bbp-align-' . $content_align,
            'bbp-intro-' . $intro_anim,
            'bbp-media-anim-' . $media_anim,
            'bbp-shadow-' . $media_shadow,
        );
        if ( ! $has_media || 'none' === $media_type ) {
            $section_classes[] = 'bbp-no-media';
        }

        $this->print_assets_once();
        $has_orbit = '' !== trim( $orbit_text );
        $orbit_path_id = $uid . '_orbit_path';
        $orbit_text_render = $has_orbit ? $this->build_orbit_text( $orbit_text ) : '';
        $has_title_anchor_target = ( 'image' === $title_mode && ! empty( $title_image ) ) || '' !== trim( $title );
        $render_corner_orbit = $has_orbit && ( 'corner' === $orbit_anchor || ( 'title' === $orbit_anchor && ! $has_title_anchor_target ) );
        ?>
        <section id="<?php echo esc_attr( $uid ); ?>" class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>" style="<?php echo esc_attr( $instance_style ); ?>">
            <div class="bbp-stage" style="<?php echo esc_attr( $stage_style ); ?>">
                <?php if ( '1' === $show_glow ) : ?>
                    <span class="bbp-glow bbp-glow-a" aria-hidden="true"></span>
                    <span class="bbp-glow bbp-glow-b" aria-hidden="true"></span>
                <?php endif; ?>

                <span class="bbp-bg-overlay" aria-hidden="true"></span>

                <?php if ( $render_corner_orbit ) : ?>
                    <?php
                    echo $this->render_orbit_markup(
                        $orbit_path_id,
                        $orbit_text_render,
                        $orbit_center_type,
                        $orbit_center_symbol,
                        $orbit_center_image,
                        'bbp-orbit bbp-orbit-corner'
                    );
                    ?>
                <?php endif; ?>

                <?php if ( 'overlay_center' === $layout && $has_media ) : ?>
                    <div class="bbp-overlay-media" aria-hidden="true">
                        <?php echo $this->get_media_markup( $media_type, $main_image, $video_url, $title, true ); ?>
                    </div>
                <?php endif; ?>

                <div class="bbp-inner">
                    <div class="bbp-content">
                        <?php if ( $has_title_anchor_target ) : ?>
                            <div class="bbp-title-wrap">
                                <div class="bbp-title-line">
                                    <?php if ( 'image' === $title_mode && ! empty( $title_image ) ) : ?>
                                        <img class="bbp-title-image" src="<?php echo esc_url( $title_image ); ?>" alt="<?php echo esc_attr( '' !== trim( wp_strip_all_tags( $title ) ) ? wp_strip_all_tags( $title ) : __( '主视觉标题图', 'developer-starter' ) ); ?>">
                                    <?php else : ?>
                                        <h2 class="bbp-title"><?php echo wp_kses_post( $title ); ?></h2>
                                    <?php endif; ?>
                                </div>
                                <?php if ( $has_orbit && 'title' === $orbit_anchor ) : ?>
                                    <?php
                                    echo $this->render_orbit_markup(
                                        $orbit_path_id,
                                        $orbit_text_render,
                                        $orbit_center_type,
                                        $orbit_center_symbol,
                                        $orbit_center_image,
                                        'bbp-orbit bbp-orbit-title'
                                    );
                                    ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( '' !== trim( $subtitle ) ) : ?>
                            <p class="bbp-subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
                        <?php endif; ?>

                        <?php if ( '' !== trim( $desc ) ) : ?>
                            <div class="bbp-desc"><?php echo wp_kses_post( $desc ); ?></div>
                        <?php endif; ?>

                        <div class="bbp-actions">
                            <?php foreach ( $buttons as $btn ) : ?>
                                <?php
                                $btn_text = isset( $btn['text'] ) ? trim( (string) $btn['text'] ) : '';
                                if ( '' === $btn_text ) {
                                    continue;
                                }
                                $btn_link = isset( $btn['link'] ) ? (string) $btn['link'] : '#';
                                $btn_link = '' !== trim( $btn_link ) ? $btn_link : '#';
                                $btn_style = isset( $btn['style'] ) ? sanitize_key( $btn['style'] ) : 'primary';
                                if ( ! in_array( $btn_style, array( 'primary', 'light', 'outline' ), true ) ) {
                                    $btn_style = 'primary';
                                }
                                $btn_icon = isset( $btn['icon'] ) ? trim( (string) $btn['icon'] ) : '';
                                ?>
                                <a href="<?php echo esc_url( $btn_link ); ?>" class="bbp-btn bbp-btn-<?php echo esc_attr( $btn_style ); ?>">
                                    <?php if ( '' !== $btn_icon ) : ?>
                                        <?php echo developer_starter_get_icon_html( $btn_icon, 'bbp-btn-icon' ); ?>
                                    <?php endif; ?>
                                    <span><?php echo esc_html( $btn_text ); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ( 'overlay_center' !== $layout && $has_media ) : ?>
                        <div class="bbp-media-col">
                            <div class="bbp-media-wrap" data-bbp-tilt="<?php echo esc_attr( 'tilt' === $media_anim ? '1' : '0' ); ?>">
                                <?php echo $this->get_media_markup( $media_type, $main_image, $video_url, $title, false ); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 输出模块静态样式与脚本（每个请求仅一次）。
     *
     * @return void
     */
    private function print_assets_once() {
        if ( self::$assets_printed ) {
            return;
        }
        self::$assets_printed = true;
        ?>
        <style>
            .module-brand-banner-pro {
                position: relative;
                overflow: hidden;
                --bbp-primary-btn-default-bg: var(--qiling-component-button-bg, var(--qiling-color-0b6cff));
                --bbp-primary-btn-default-text: var(--qiling-component-button-text, var(--color-text-inverse));
                --bbp-primary-btn-default-border: var(--qiling-component-button-border, transparent);
                --bbp-light-btn-default-bg: var(--qiling-component-card-bg, rgba(var(--qiling-rgb-255-255-255), 0.88));
                --bbp-light-btn-default-text: var(--color-neutral-900);
                --bbp-light-btn-default-border: var(--qiling-component-card-border, rgba(var(--qiling-rgb-148-163-184), 0.32));
            }

            .module-brand-banner-pro .bbp-stage {
                position: relative;
                isolation: isolate;
                min-height: var(--bbp-height, 85vh);
                padding-top: var(--bbp-pt, 72px);
                padding-bottom: var(--bbp-pb, 72px);
                display: flex;
                align-items: center;
            }

            .module-brand-banner-pro .bbp-bg-overlay {
                position: absolute;
                inset: 0;
                background: var(--qiling-color-000000);
                opacity: var(--bbp-bg-overlay, 0);
                z-index: 1;
                pointer-events: none;
            }

            .module-brand-banner-pro .bbp-inner {
                width: min(1240px, 92vw);
                margin: 0 auto;
                position: relative;
                z-index: 3;
                display: grid;
                gap: clamp(24px, 4vw, 64px);
                align-items: center;
            }

            .module-brand-banner-pro .bbp-content {
                position: relative;
                color: var(--bbp-text-color, var(--color-neutral-700));
                max-width: var(--qiling-measure-760);
            }

            .module-brand-banner-pro .bbp-title {
                margin: 0;
                color: var(--bbp-title-color, var(--color-neutral-900));
                font-size: clamp(36px, 7vw, var(--bbp-title-size-max, 88px));
                line-height: 1.08;
                letter-spacing: var(--qiling-letter-spacing-tight, 0);
                font-weight: 700;
            }

            .module-brand-banner-pro .bbp-title strong {
                font-weight: 800;
            }

            .module-brand-banner-pro .bbp-title-wrap {
                position: relative;
                display: inline-block;
                max-width: min(100%, var(--qiling-measure-760));
            }

            .module-brand-banner-pro .bbp-title-line {
                display: inline-flex;
                align-items: baseline;
                justify-content: flex-start;
                max-width: min(100%, var(--qiling-measure-760));
            }

            .module-brand-banner-pro .bbp-title-image {
                display: block;
                width: min(100%, var(--bbp-title-image-max-width, 760px));
                height: auto;
            }

            .module-brand-banner-pro.bbp-title-effect-outline .bbp-title {
                color: transparent;
                -webkit-text-stroke: var(--bbp-title-stroke-width, 1.5px) var(--bbp-title-stroke-color, var(--color-neutral-0));
                text-shadow: none;
            }

            .module-brand-banner-pro.bbp-title-effect-gradient .bbp-title {
                background: var(--bbp-title-gradient, var(--gradient-brand-glow));
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }

            .module-brand-banner-pro.bbp-title-effect-glow .bbp-title {
                text-shadow: 0 0 var(--qiling-space-26) rgba(var(--qiling-rgb-255-255-255), 0.45), 0 var(--qiling-space-14) var(--qiling-space-40) rgba(var(--color-primary-rgb), 0.35);
            }

            .module-brand-banner-pro .bbp-subtitle {
                margin: var(--qiling-space-18) 0 0;
                font-size: clamp(18px, 2.2vw, 34px);
                line-height: 1.35;
                color: var(--bbp-title-color, var(--color-neutral-900));
            }

            .module-brand-banner-pro .bbp-desc {
                margin-top: var(--qiling-space-16);
                font-size: clamp(14px, 1.2vw, 18px);
                line-height: 1.75;
                opacity: 0.9;
                max-width: var(--qiling-measure-720);
            }

            .module-brand-banner-pro .bbp-actions {
                margin-top: var(--qiling-space-28);
                display: flex;
                gap: var(--qiling-space-12);
                flex-wrap: wrap;
            }

            .module-brand-banner-pro .bbp-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: var(--qiling-space-8);
                min-height: 48px;
                padding: 0 var(--qiling-space-22);
                border-radius: 999px;
                text-decoration: none;
                font-size: var(--qiling-text-rem-0p95);
                font-weight: 600;
                transition: all 0.28s ease;
                border: 1px solid transparent;
            }

            .module-brand-banner-pro .bbp-btn-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: var(--qiling-text-em-1p05, 1.05em);
            }

            .module-brand-banner-pro .bbp-btn-primary {
                background: var(--bbp-primary-btn-bg, var(--bbp-primary-btn-default-bg));
                color: var(--bbp-primary-btn-text, var(--bbp-primary-btn-default-text));
                border-color: var(--bbp-primary-btn-border, var(--bbp-primary-btn-default-border));
                box-shadow: 0 var(--qiling-space-18) var(--qiling-space-34) calc(var(--qiling-space-18) * -1) rgba(var(--qiling-rgb-11-108-255), 0.8);
            }

            .module-brand-banner-pro .bbp-btn-primary:hover {
                transform: translateY(-2px);
                background: var(--bbp-primary-btn-hover-bg, var(--bbp-primary-btn-bg, var(--bbp-primary-btn-default-bg)));
                color: var(--bbp-primary-btn-hover-text, var(--bbp-primary-btn-text, var(--bbp-primary-btn-default-text)));
                border-color: var(--bbp-primary-btn-hover-border, var(--bbp-primary-btn-border, var(--bbp-primary-btn-default-border)));
                box-shadow: 0 var(--qiling-space-24) var(--qiling-space-38) calc(var(--qiling-space-18) * -1) rgba(var(--qiling-rgb-11-108-255), 0.9);
            }

            .module-brand-banner-pro .bbp-btn-light {
                background: var(--bbp-light-btn-bg, var(--bbp-light-btn-default-bg));
                color: var(--bbp-light-btn-text, var(--bbp-light-btn-default-text));
                border-color: var(--bbp-light-btn-border, var(--bbp-light-btn-default-border));
            }

            .module-brand-banner-pro .bbp-btn-light:hover {
                background: var(--bbp-light-btn-hover-bg, var(--bbp-light-btn-bg, var(--bbp-light-btn-default-bg)));
                color: var(--bbp-light-btn-hover-text, var(--bbp-light-btn-text, var(--bbp-light-btn-default-text)));
                border-color: var(--bbp-light-btn-hover-border, var(--bbp-light-btn-border, var(--bbp-light-btn-default-border)));
                transform: translateY(-2px);
            }

            .module-brand-banner-pro .bbp-btn-outline {
                background: transparent;
                color: var(--bbp-title-color, var(--color-neutral-900));
                border-color: rgba(var(--qiling-rgb-148-163-184), 0.45);
            }

            .module-brand-banner-pro .bbp-btn-outline:hover {
                background: rgba(var(--qiling-rgb-255-255-255), 0.2);
                transform: translateY(-2px);
            }

            .module-brand-banner-pro .bbp-media-col {
                width: 100%;
                display: flex;
                justify-content: center;
            }

            .module-brand-banner-pro .bbp-media-wrap {
                width: min(100%, var(--bbp-media-max-width, 980px));
                border-radius: var(--bbp-media-radius, 26px);
                overflow: hidden;
                transform-style: preserve-3d;
                transition: transform 0.35s ease, box-shadow 0.35s ease;
                background: rgba(var(--qiling-rgb-255-255-255), 0.16);
                backdrop-filter: blur(4px);
            }

            .module-brand-banner-pro .bbp-media-wrap img,
            .module-brand-banner-pro .bbp-media-wrap video {
                width: 100%;
                height: auto;
                display: block;
                object-fit: cover;
            }

            .module-brand-banner-pro.bbp-shadow-none .bbp-media-wrap {
                box-shadow: none;
            }

            .module-brand-banner-pro.bbp-shadow-soft .bbp-media-wrap {
                box-shadow: 0 var(--qiling-space-18) var(--qiling-space-42) rgba(var(--qiling-rgb-15-23-42), 0.18);
            }

            .module-brand-banner-pro.bbp-shadow-lifted .bbp-media-wrap {
                box-shadow: 0 var(--qiling-space-30) var(--qiling-space-60) rgba(var(--qiling-rgb-15-23-42), 0.26);
            }

            .module-brand-banner-pro.bbp-shadow-glow .bbp-media-wrap {
                box-shadow: 0 0 0 1px rgba(var(--qiling-rgb-255-255-255), 0.36), 0 var(--qiling-space-28) var(--qiling-space-80) rgba(var(--qiling-rgb-59-130-246), 0.35);
            }

            .module-brand-banner-pro .bbp-media-placeholder {
                min-height: 260px;
                border-radius: var(--bbp-media-radius, 26px);
                border: 1px dashed rgba(var(--qiling-rgb-148-163-184), 0.45);
                display: flex;
                align-items: center;
                justify-content: center;
                color: rgba(var(--qiling-rgb-15-23-42), 0.55);
                background: rgba(var(--qiling-rgb-255-255-255), 0.35);
                padding: var(--qiling-space-24);
                text-align: center;
                font-size: var(--qiling-text-rem-0p875);
            }

            .module-brand-banner-pro .bbp-overlay-media {
                position: absolute;
                inset: 0;
                z-index: 0;
                overflow: hidden;
            }

            .module-brand-banner-pro .bbp-overlay-media img,
            .module-brand-banner-pro .bbp-overlay-media video {
                width: 100%;
                height: 100%;
                object-fit: cover;
                opacity: 0.66;
                transform: scale(1.02);
            }

            .module-brand-banner-pro.bbp-layout-top_bottom .bbp-inner {
                grid-template-columns: 1fr;
                justify-items: center;
            }

            .module-brand-banner-pro.bbp-layout-top_bottom .bbp-media-wrap {
                max-height: 60vh;
                border-radius: max(var(--bbp-media-radius, 26px), 18px);
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-inner,
            .module-brand-banner-pro.bbp-layout-right_left .bbp-inner {
                width: min(1540px, 98vw);
                grid-template-columns: minmax(220px, 0.34fr) minmax(720px, 0.66fr);
                gap: clamp(16px, 2.4vw, 36px);
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-content,
            .module-brand-banner-pro.bbp-layout-right_left .bbp-content {
                max-width: var(--qiling-measure-420);
                width: 100%;
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-title,
            .module-brand-banner-pro.bbp-layout-right_left .bbp-title {
                font-size: clamp(34px, 4.6vw, min(var(--bbp-title-size-max, 72px), 72px));
                line-height: 1.06;
                letter-spacing: var(--qiling-letter-spacing-tight, 0);
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-subtitle,
            .module-brand-banner-pro.bbp-layout-right_left .bbp-subtitle {
                font-size: clamp(18px, 2.4vw, 30px);
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-desc,
            .module-brand-banner-pro.bbp-layout-right_left .bbp-desc {
                max-width: var(--qiling-measure-430);
                font-size: clamp(14px, 1.05vw, 16px);
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-media-col {
                justify-content: flex-end;
                overflow: visible;
            }

            .module-brand-banner-pro.bbp-layout-right_left .bbp-media-col {
                justify-content: flex-start;
                overflow: visible;
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-media-wrap,
            .module-brand-banner-pro.bbp-layout-right_left .bbp-media-wrap {
                width: min(132%, calc(var(--bbp-media-max-width, 980px) + 360px));
                max-width: none;
                border-radius: max(var(--bbp-media-radius, 26px), 18px);
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-media-wrap {
                margin-right: clamp(calc(var(--qiling-space-56) * -1), -4vw, calc(var(--qiling-space-20) * -1));
            }

            .module-brand-banner-pro.bbp-layout-right_left .bbp-media-wrap {
                margin-left: clamp(calc(var(--qiling-space-56) * -1), -4vw, calc(var(--qiling-space-20) * -1));
            }

            .module-brand-banner-pro.bbp-layout-left_right .bbp-content {
                justify-self: start;
                margin-left: clamp(calc(var(--qiling-space-30) * -1), -2vw, calc(var(--qiling-space-10) * -1));
            }

            .module-brand-banner-pro.bbp-layout-right_left .bbp-content {
                justify-self: end;
                margin-right: clamp(calc(var(--qiling-space-30) * -1), -2vw, calc(var(--qiling-space-10) * -1));
            }

            .module-brand-banner-pro.bbp-layout-right_left .bbp-content {
                order: 2;
            }

            .module-brand-banner-pro.bbp-layout-right_left .bbp-media-col {
                order: 1;
            }

            .module-brand-banner-pro.bbp-layout-overlay_center .bbp-inner {
                grid-template-columns: 1fr;
                justify-items: center;
                text-align: center;
            }

            .module-brand-banner-pro.bbp-layout-overlay_center .bbp-content {
                max-width: var(--qiling-measure-860);
                padding: 0;
                border-radius: 0;
                background: transparent;
                border: 0;
                backdrop-filter: none;
            }

            .module-brand-banner-pro.bbp-layout-overlay_center.bbp-overlay-box-glass .bbp-content {
                padding: clamp(20px, 4vw, 44px);
                border-radius: var(--qiling-space-24);
                background: rgba(var(--qiling-rgb-255-255-255), 0.2);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.35);
            }

            .module-brand-banner-pro.bbp-align-center .bbp-content {
                text-align: center;
                margin-inline: auto;
            }

            .module-brand-banner-pro.bbp-align-center .bbp-actions {
                justify-content: center;
            }

            .module-brand-banner-pro.bbp-no-media .bbp-inner {
                grid-template-columns: 1fr !important;
                justify-items: center;
            }

            .module-brand-banner-pro.bbp-style-glow .bbp-title {
                font-family: "Times New Roman", Georgia, serif;
                font-weight: 500;
                letter-spacing: var(--qiling-letter-spacing-wide, 0.02em);
            }

            .module-brand-banner-pro.bbp-style-glow.bbp-layout-overlay_center .bbp-title-line {
                justify-content: center;
            }

            .module-brand-banner-pro.bbp-style-glow.bbp-layout-overlay_center .bbp-title {
                font-size: clamp(72px, 15vw, var(--bbp-title-size-max, 220px));
                line-height: 0.96;
                letter-spacing: var(--qiling-letter-spacing-wide-sm, 0.015em);
            }

            .module-brand-banner-pro.bbp-style-glow.bbp-layout-overlay_center .bbp-title-image {
                width: min(100%, var(--bbp-title-image-max-width, 860px));
            }

            .module-brand-banner-pro.bbp-style-glow .bbp-subtitle {
                opacity: 0.93;
            }

            .module-brand-banner-pro.bbp-style-editorial .bbp-title {
                letter-spacing: var(--qiling-letter-spacing-tight, 0);
                text-transform: none;
                font-weight: 800;
            }

            .module-brand-banner-pro.bbp-style-editorial .bbp-btn {
                border-radius: var(--qiling-space-12);
            }

            .module-brand-banner-pro.bbp-style-editorial.bbp-layout-left_right .bbp-title,
            .module-brand-banner-pro.bbp-style-editorial.bbp-layout-right_left .bbp-title {
                font-size: clamp(32px, 4.2vw, 64px);
                letter-spacing: var(--qiling-letter-spacing-tight, 0);
            }

            .module-brand-banner-pro.bbp-style-editorial.bbp-layout-left_right .bbp-media-wrap img,
            .module-brand-banner-pro.bbp-style-editorial.bbp-layout-right_left .bbp-media-wrap img {
                object-fit: contain;
            }

            .module-brand-banner-pro .bbp-glow {
                position: absolute;
                z-index: 0;
                pointer-events: none;
                width: clamp(240px, 35vw, 600px);
                height: clamp(240px, 35vw, 600px);
                border-radius: 50%;
                filter: blur(48px);
                opacity: 0.36;
                animation: bbpGlowMove 11s ease-in-out infinite alternate;
            }

            .module-brand-banner-pro .bbp-glow-a {
                background: radial-gradient(circle, rgba(var(--qiling-rgb-59-130-246), 0.75) 0%, rgba(var(--qiling-rgb-59-130-246), 0) 72%);
                top: -10%;
                right: -8%;
            }

            .module-brand-banner-pro .bbp-glow-b {
                background: radial-gradient(circle, rgba(var(--qiling-rgb-14-165-233), 0.65) 0%, rgba(var(--qiling-rgb-14-165-233), 0) 72%);
                bottom: -18%;
                left: -12%;
                animation-delay: 1.5s;
            }

            .module-brand-banner-pro .bbp-orbit {
                position: absolute;
                width: var(--bbp-orbit-size, var(--qiling-measure-120));
                height: var(--bbp-orbit-size, var(--qiling-measure-120));
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--bbp-orbit-color, var(--color-border));
                z-index: 7;
                pointer-events: none;
            }

            .module-brand-banner-pro .bbp-orbit.bbp-orbit-corner {
                right: var(--bbp-orbit-right, var(--qiling-space-24));
                top: var(--bbp-orbit-top, var(--qiling-space-24));
            }

            .module-brand-banner-pro .bbp-orbit.bbp-orbit-title {
                right: 0;
                top: 0;
                transform: translate(
                    calc(60% + var(--bbp-orbit-right, 0px)),
                    calc(-22% + var(--bbp-orbit-top, 0px))
                );
            }

            .module-brand-banner-pro .bbp-orbit svg {
                width: 100%;
                height: 100%;
                overflow: visible;
                transform-origin: 50% 50%;
                animation: bbpRotate 16s linear infinite;
            }

            .module-brand-banner-pro .bbp-orbit text {
                font-size: calc(var(--qiling-font-size-base) * 0.45);
                letter-spacing: var(--qiling-orbit-letter-spacing, 0.16em);
                text-transform: uppercase;
                fill: currentColor;
                opacity: 0.9;
                font-weight: 400;
            }

            .module-brand-banner-pro .bbp-orbit-center {
                position: absolute;
                inset: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: var(--qiling-text-rem-0p875);
                line-height: 1;
                color: currentColor;
                opacity: 0.92;
            }

            .module-brand-banner-pro .bbp-orbit-center-image img {
                width: 22px;
                height: 22px;
                object-fit: contain;
                display: block;
                border-radius: 999px;
            }

            .module-brand-banner-pro .bbp-orbit-center-image {
                animation: none;
            }

            .module-brand-banner-pro.bbp-intro-fade_up .bbp-content {
                animation: bbpFadeUp 0.75s cubic-bezier(0.2, 0.72, 0.25, 1) both;
            }

            .module-brand-banner-pro.bbp-intro-slide_up .bbp-content {
                animation: bbpSlideUp 0.8s cubic-bezier(0.2, 0.72, 0.25, 1) both;
            }

            .module-brand-banner-pro.bbp-intro-zoom .bbp-content {
                animation: bbpZoomIn 0.7s ease both;
            }

            .module-brand-banner-pro.bbp-media-anim-float .bbp-media-wrap {
                animation: bbpFloat 5.4s ease-in-out infinite;
            }

            .module-brand-banner-pro.bbp-media-anim-pulse .bbp-media-wrap {
                animation: bbpPulse 4.2s ease-in-out infinite;
            }

            @keyframes bbpFadeUp {
                from {
                    opacity: 0;
                    transform: translateY(16px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes bbpSlideUp {
                from {
                    opacity: 0;
                    transform: translateY(28px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes bbpZoomIn {
                from {
                    opacity: 0;
                    transform: scale(0.96);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            @keyframes bbpFloat {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-10px);
                }
            }

            @keyframes bbpPulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.018);
                }
            }

            @keyframes bbpGlowMove {
                from {
                    transform: translate3d(0, 0, 0) scale(1);
                }
                to {
                    transform: translate3d(24px, -18px, 0) scale(1.08);
                }
            }

            @keyframes bbpRotate {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            @keyframes bbpRotateReverse {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(-360deg);
                }
            }

            @media (max-width: 1024px) {
                .module-brand-banner-pro.bbp-layout-left_right .bbp-inner,
                .module-brand-banner-pro.bbp-layout-right_left .bbp-inner {
                    grid-template-columns: 1fr;
                }

                .module-brand-banner-pro.bbp-layout-right_left .bbp-content,
                .module-brand-banner-pro.bbp-layout-right_left .bbp-media-col {
                    order: initial;
                }

                .module-brand-banner-pro .bbp-content {
                    max-width: min(100%, var(--qiling-measure-760));
                }

                .module-brand-banner-pro .bbp-orbit {
                    width: min(var(--bbp-orbit-size, var(--qiling-measure-120)), calc(var(--qiling-space-110) - var(--qiling-space-12)));
                    height: min(var(--bbp-orbit-size, var(--qiling-measure-120)), calc(var(--qiling-space-110) - var(--qiling-space-12)));
                }

                .module-brand-banner-pro .bbp-orbit.bbp-orbit-corner {
                    right: max(var(--bbp-orbit-right, 0px), 12px);
                    top: max(var(--bbp-orbit-top, 0px), 12px);
                }

                .module-brand-banner-pro .bbp-orbit.bbp-orbit-title {
                    transform: translate(
                        calc(44% + var(--bbp-orbit-right, 0px)),
                        calc(-20% + var(--bbp-orbit-top, 0px))
                    );
                }
            }

            @media (max-width: 767px) {
                .module-brand-banner-pro .bbp-stage {
                    padding-top: min(var(--bbp-pt, var(--qiling-space-72)), var(--qiling-space-56));
                    padding-bottom: min(var(--bbp-pb, var(--qiling-space-72)), var(--qiling-space-56));
                }

                .module-brand-banner-pro .bbp-actions {
                    width: 100%;
                }

                .module-brand-banner-pro .bbp-btn {
                    width: 100%;
                }

                .module-brand-banner-pro.bbp-layout-overlay_center.bbp-overlay-box-glass .bbp-content {
                    padding: var(--qiling-space-18);
                    border-radius: var(--qiling-space-16);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .module-brand-banner-pro .bbp-glow,
                .module-brand-banner-pro .bbp-orbit svg,
                .module-brand-banner-pro .bbp-orbit-center,
                .module-brand-banner-pro .bbp-content,
                .module-brand-banner-pro .bbp-media-wrap {
                    animation: none !important;
                    transition: none !important;
                }
            }
        </style>

        <script>
            (function () {
                if (window.__qilingBrandBannerProInitDone) {
                    return;
                }
                window.__qilingBrandBannerProInitDone = true;

                function playHeroVideos(scope) {
                    var root = scope || document;
                    var videos = root.querySelectorAll('.module-brand-banner-pro video[autoplay]');
                    videos.forEach(function (video) {
                        video.muted = true;
                        var playPromise = video.play();
                        if (playPromise && typeof playPromise.catch === 'function') {
                            playPromise.catch(function () {});
                        }
                    });
                }

                function bindTilt(scope) {
                    var root = scope || document;
                    var cards = root.querySelectorAll('.module-brand-banner-pro .bbp-media-wrap[data-bbp-tilt="1"]');
                    cards.forEach(function (card) {
                        if (card.dataset.bbpTiltBound === '1') {
                            return;
                        }
                        card.dataset.bbpTiltBound = '1';

                        card.addEventListener('mousemove', function (e) {
                            var rect = card.getBoundingClientRect();
                            if (!rect.width || !rect.height) {
                                return;
                            }
                            var px = (e.clientX - rect.left) / rect.width;
                            var py = (e.clientY - rect.top) / rect.height;
                            var rx = (0.5 - py) * 8;
                            var ry = (px - 0.5) * 10;
                            card.style.transform = 'perspective(960px) rotateX(' + rx.toFixed(2) + 'deg) rotateY(' + ry.toFixed(2) + 'deg)';
                        });

                        card.addEventListener('mouseleave', function () {
                            card.style.transform = '';
                        });
                    });
                }

                function boot(scope) {
                    playHeroVideos(scope);
                    bindTilt(scope);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function () {
                        boot(document);
                    });
                }

                boot(document);
            })();
        </script>
        <?php
    }

    /**
     * 输出媒体 HTML。
     *
     * @param string $media_type 媒体类型
     * @param string $main_image 图片地址
     * @param string $video_url  视频地址
     * @param string $title      标题（用于 alt）
     * @param bool   $is_overlay 是否叠加背景模式
     * @return string
     */
    private function get_media_markup( $media_type, $main_image, $video_url, $title, $is_overlay = false ) {
        ob_start();
        if ( 'video' === $media_type && ! empty( $video_url ) ) :
            ?>
            <video class="<?php echo esc_attr( $is_overlay ? 'bbp-overlay-video' : 'bbp-main-video' ); ?>" autoplay muted loop playsinline preload="metadata">
                <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
            </video>
            <?php
        elseif ( 'image' === $media_type && ! empty( $main_image ) ) :
            ?>
            <img class="<?php echo esc_attr( $is_overlay ? 'bbp-overlay-image' : 'bbp-main-image' ); ?>" src="<?php echo esc_url( $main_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>">
            <?php
        else :
            ?>
            <div class="bbp-media-placeholder"><?php esc_html_e( '请添加主视觉图片或视频', 'developer-starter' ); ?></div>
            <?php
        endif;
        return (string) ob_get_clean();
    }

    /**
     * 渲染环形文字组件。
     *
     * @param string $path_id            SVG path id
     * @param string $orbit_text_render  环形文本
     * @param string $orbit_center_type  中心内容类型
     * @param string $orbit_center_symbol 中心文字
     * @param string $orbit_center_image 中心图片
     * @param string $extra_class        额外类名
     * @return string
     */
    private function render_orbit_markup( $path_id, $orbit_text_render, $orbit_center_type, $orbit_center_symbol, $orbit_center_image, $extra_class = '' ) {
        if ( '' === trim( (string) $orbit_text_render ) ) {
            return '';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr( $extra_class ); ?>" aria-hidden="true">
            <svg viewBox="0 0 120 120" role="presentation" focusable="false" aria-hidden="true">
                <defs>
                    <path id="<?php echo esc_attr( $path_id ); ?>" d="M60,60 m-44,0 a44,44 0 1,1 88,0 a44,44 0 1,1 -88,0"></path>
                </defs>
                <text>
                    <textPath href="#<?php echo esc_attr( $path_id ); ?>" xlink:href="#<?php echo esc_attr( $path_id ); ?>" startOffset="0%">
                        <?php echo esc_html( $orbit_text_render ); ?>
                    </textPath>
                </text>
            </svg>
            <?php if ( 'image' === $orbit_center_type && ! empty( $orbit_center_image ) ) : ?>
                <span class="bbp-orbit-center bbp-orbit-center-image" aria-hidden="true">
                    <img src="<?php echo esc_url( $orbit_center_image ); ?>" alt="">
                </span>
            <?php else : ?>
                <span class="bbp-orbit-center" aria-hidden="true"><?php echo esc_html( $orbit_center_symbol ); ?></span>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * 获取默认按钮组。
     *
     * @return array
     */
    private function get_default_buttons() {
        return array(
            array(
                'text'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '了解更多', 'Learn More' ) : __( '了解更多', 'developer-starter' ),
                'link'  => '#',
                'style' => 'primary',
                'icon'  => '',
            ),
            array(
                'text'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看产品', 'Explore' ) : __( '查看产品', 'developer-starter' ),
                'link'  => '#',
                'style' => 'outline',
                'icon'  => '',
            ),
        );
    }

    /**
     * 构建环形文字，自动补全重复以填满圆周。
     *
     * @param string $text 原始文案
     * @return string
     */
    private function build_orbit_text( $text ) {
        $text = trim( wp_strip_all_tags( (string) $text ) );
        $text = preg_replace( '/\s+/u', ' ', $text );
        if ( '' === $text ) {
            return '';
        }

        $parts = array();
        $result = '';
        $max_rounds = 6;
        $target_chars = 58;
        for ( $i = 0; $i < $max_rounds; $i++ ) {
            $parts[] = $text;
            $result = implode( ' • ', $parts );
            $len = function_exists( 'mb_strlen' ) ? mb_strlen( $result, 'UTF-8' ) : strlen( $result );
            if ( $len >= $target_chars ) {
                break;
            }
        }

        return $result;
    }

    /**
     * 清洗颜色值，允许 hex / rgba / hsla / CSS var。
     *
     * @param mixed  $value   原始值
     * @param string $default 默认值
     * @return string
     */
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

        return $hex ? (string) $hex : $default;
    }

    /**
     * 安全清洗十六进制色值。
     *
     * @param string $value   原始色值
     * @param string $default 默认值
     * @return string
     */
    private function sanitize_hex( $value, $default ) {
        $clean = $this->sanitize_color_value( $value, '' );
        if ( '' !== $clean ) {
            return $clean;
        }

        $fallback = $this->sanitize_color_value( $default, '' );
        return '' !== $fallback ? $fallback : 'currentColor';
    }

    /**
     * 清洗渐变表达式。
     *
     * @param string $value   原始值
     * @param string $default 默认值
     * @return string
     */
    private function sanitize_gradient( $value, $default ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return (string) $default;
        }

        $value = preg_replace( '/[\r\n\t]+/', ' ', $value );
        $value = str_replace( array( ';', '{', '}' ), '', $value );
        if ( false === stripos( $value, 'gradient(' ) ) {
            return (string) $default;
        }

        return (string) $value;
    }

    /**
     * 清洗透明度。
     *
     * @param mixed  $value   原始值
     * @param string $default 默认值
     * @return string
     */
    private function sanitize_opacity( $value, $default = '0.12' ) {
        $opacity = is_numeric( $value ) ? (float) $value : (float) $default;
        if ( $opacity < 0 ) {
            $opacity = 0;
        }
        if ( $opacity > 0.9 ) {
            $opacity = 0.9;
        }
        return rtrim( rtrim( sprintf( '%.2F', $opacity ), '0' ), '.' );
    }

    /**
     * 清洗 CSS 长度值。
     *
     * @param mixed  $value   原始值
     * @param string $default 默认值
     * @return string
     */
    private function sanitize_length( $value, $default ) {
        $value = is_string( $value ) ? trim( $value ) : '';
        if ( '' === $value ) {
            return (string) $default;
        }

        if ( class_exists( '\Developer_Starter\Modules\Module_Manager' ) && method_exists( '\Developer_Starter\Modules\Module_Manager', 'sanitize_spacing_value' ) ) {
            $clean = Module_Manager::sanitize_spacing_value( $value );
            if ( ! empty( $clean ) ) {
                return (string) $clean;
            }
        }

        if ( preg_match( '/^-?(?:\d+|\d*\.\d+)(px|%|vh|vw|rem|em)$/i', $value ) ) {
            return (string) $value;
        }

        return (string) $default;
    }
}
