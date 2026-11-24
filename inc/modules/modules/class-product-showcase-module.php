<?php
/**
 * 产品图文与视频轮播展示模块
 *
 * 左侧：图片/视频轮播 + 小按钮
 * 右侧：标题、副标题、价格、大按钮、多行描述
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Product_Showcase_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-cart';
        $this->description = __( '左图右文产品展示，支持图片视频轮播', 'qiling' );
    }

    public function get_id() {
        return 'product_showcase';
    }

    public function get_name() {
        return __( '图文视频轮播', 'qiling' );
    }

    public function get_fields() {
        return array(
            // === 模块整体设置 ===
            array(
                'id'      => 'ps_bg_color',
                'type'    => 'text',
                'label'   => __( '背景颜色(支持渐变)', 'qiling' ),
                'default' => '',
            ),
            array(
                'id'      => 'ps_padding',
                'type'    => 'select',
                'label'   => __( '上下内边距', 'qiling' ),
                'options' => array(
                    '40'  => '40px',
                    '60'  => '60px',
                    '80'  => __( '80px（推荐）', 'qiling' ),
                    '100' => '100px',
                    '120' => '120px',
                ),
                'default' => '80',
            ),
            array(
                'id'      => 'ps_layout',
                'type'    => 'select',
                'label'   => __( '布局方向', 'qiling' ),
                'options' => array(
                    'left'  => __( '图片在左（默认）', 'qiling' ),
                    'right' => __( '图片在右', 'qiling' ),
                ),
                'default' => 'left',
            ),
            
            // === 左侧媒体设置 ===
            array(
                'id'         => 'ps_media_items',
                'type'       => 'repeater',
                'label'      => __( '轮播媒体（图片或视频）', 'qiling' ),
                'add_button' => __( '添加图片/视频', 'qiling' ),
                'fields'     => array(
                    array(
                        'id'      => 'type',
                        'type'    => 'select',
                        'label'   => __( '类型', 'qiling' ),
                        'options' => array(
                            'image' => __( '图片', 'qiling' ),
                            'video' => __( '视频', 'qiling' ),
                        ),
                    ),
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '图片（图片类型时使用）', 'qiling' ),
                    ),
                    array(
                        'id'    => 'video_url',
                        'type'  => 'text',
                        'label' => __( '视频URL（视频类型时使用）', 'qiling' ),
                    ),
                    array(
                        'id'    => 'video_poster',
                        'type'  => 'image',
                        'label' => __( '视频封面（可选）', 'qiling' ),
                    ),
                ),
            ),
            array(
                'id'      => 'ps_media_height',
                'type'    => 'text',
                'label' => __( '媒体区域高度', 'qiling' ),
                'default' => '450px',
            ),
            array(
                'id'      => 'ps_media_radius',
                'type'    => 'text',
                'label' => __( '媒体区域圆角', 'qiling' ),
                'default' => '16px',
            ),
            
            // === 左侧小按钮 ===
            array(
                'id'         => 'ps_left_buttons',
                'type'       => 'repeater',
                'label'      => __( '轮播下方小按钮', 'qiling' ),
                'add_button' => __( '添加按钮', 'qiling' ),
                'fields'     => array(
                    array(
                        'id'    => 'text',
                        'type'  => 'text',
                        'label' => __( '按钮文字', 'qiling' ),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'label' => __( '按钮链接', 'qiling' ),
                    ),
                    array(
                        'id'    => 'icon',
                        'type'  => 'text',
                        'label' => __( '按钮图标（emoji或留空）', 'qiling' ),
                    ),
                    array(
                        'id'      => 'target',
                        'type'    => 'select',
                        'label'   => __( '打开方式', 'qiling' ),
                        'options' => array(
                            '_self'  => __( '当前窗口', 'qiling' ),
                            '_blank' => __( '新窗口', 'qiling' ),
                        ),
                    ),
                ),
            ),
            
            // === 右侧内容设置 ===
            array(
                'id'      => 'ps_title',
                'type'    => 'text',
                'label'   => __( '大标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题', 'Qiling Theme' ) : __( '启灵主题', 'qiling' ),
            ),
            array(
                'id'      => 'ps_title_color',
                'type'    => 'text',
                'label'   => __( '大标题颜色', 'qiling' ),
                'default' => '',
            ),
            array(
                'id'      => 'ps_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '开源模块化主题', 'An open modular theme for modern websites' ) : __( '开源模块化主题', 'qiling' ),
            ),
            array(
                'id'      => 'ps_subtitle_color',
                'type'    => 'text',
                'label'   => __( '副标题颜色', 'qiling' ),
                'default' => '',
            ),
            
            // === 价格设置 ===
            array(
                'id'      => 'ps_show_price',
                'type'    => 'select',
                'label'   => __( '显示价格', 'qiling' ),
                'options' => array(
                    'yes' => __( '是', 'qiling' ),
                    'no'  => __( '否', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'ps_price',
                'type'    => 'text',
                'label'   => __( '优惠价格', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 199 ) : '¥199',
            ),
            array(
                'id'      => 'ps_price_color',
                'type'    => 'text',
                'label'   => __( '优惠价颜色', 'qiling' ),
                'default' => '',
            ),
            array(
                'id'      => 'ps_original_price',
                'type'    => 'text',
                'label'   => __( '原价（有删除线）', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 299 ) : '¥299',
            ),
            
            // === 右侧大按钮 ===
            array(
                'id'      => 'ps_cta_text',
                'type'    => 'text',
                'label'   => __( 'CTA按钮文字', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '前往购买', 'Buy Now' ) : __( '前往购买', 'qiling' ),
            ),
            array(
                'id'      => 'ps_cta_url',
                'type'    => 'text',
                'label'   => __( 'CTA按钮链接', 'qiling' ),
                'default' => '#',
            ),
            array(
                'id'      => 'ps_cta_bg',
                'type'    => 'text',
                'label'   => __( 'CTA按钮背景色(支持渐变)', 'qiling' ),
                'default' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)',
            ),
            array(
                'id'      => 'ps_cta_color',
                'type'    => 'text',
                'label'   => __( 'CTA按钮文字颜色', 'qiling' ),
                'default' => 'var(--color-neutral-0)',
            ),
            $this->get_button_border_color_field( 'ps_cta_border_color', __( 'CTA按钮边框颜色', 'qiling' ) ),
            array(
                'id'      => 'ps_cta_target',
                'type'    => 'select',
                'label'   => __( 'CTA按钮打开方式', 'qiling' ),
                'options' => array(
                    '_self'  => __( '当前窗口', 'qiling' ),
                    '_blank' => __( '新窗口', 'qiling' ),
                ),
                'default' => '_self',
            ),
            
            // === 描述信息 ===
            array(
                'id'      => 'ps_description',
                'type'    => 'textarea',
                'label'   => __( '描述信息（支持换行，每行一条）', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼用PHP：7.4及以上，推荐8.0版本\n授权时间：永久授权使用，免费更新", "Latest version: V2.5\nUpdated: 2026-01-01\nWP support: 6.0-6.9, latest recommended\nPHP support: 7.4+, 8.0 recommended\nLicense: lifetime access with free updates" ) : __( "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼用PHP：7.4及以上，推荐8.0版本\n授权时间：永久授权使用，免费更新", 'qiling' ),
            ),
            array(
                'id'      => 'ps_desc_color',
                'type'    => 'text',
                'label'   => __( '描述文字颜色', 'qiling' ),
                'default' => '',
            ),

            // === 配色/材质色块切换联动 (Single Category / DTC Swatches) ===
            array(
                'id'      => 'ps_enable_swatches',
                'type'    => 'select',
                'label'   => __( '启用配色/材质色块联动', 'qiling' ),
                'options' => array(
                    'no'  => __( '不启用', 'qiling' ),
                    'yes' => __( '启用', 'qiling' ),
                ),
                'default' => 'no',
            ),
            array(
                'id'      => 'ps_swatches_title',
                'type'    => 'text',
                'label'   => __( '色块选项标题', 'qiling' ),
                'default' => __( '可选款式/材质/配色', 'qiling' ),
            ),
            array(
                'id'      => 'ps_swatches_list',
                'type'    => 'repeater',
                'label'   => __( '款式/配色列表', 'qiling' ),
                'fields'  => array(
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '款式名称（如：星空灰 / 18K黄金 / 意式植鞣棕）', 'qiling' ),
                    ),
                    array(
                        'id'      => 'color_code',
                        'type'    => 'text',
                        'label'   => __( '色值代码（如：#374151 或渐变CSS）', 'qiling' ),
                        'default' => '#2563eb',
                    ),
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '对应的主图/展示图（点击色块后切换到此图）', 'qiling' ),
                    ),
                    array(
                        'id'    => 'price',
                        'type'  => 'text',
                        'label' => __( '款式价格（可选，不填默认展示主商品价格）', 'qiling' ),
                    ),
                ),
            ),
        );
    }

    public function render( $data = array() ) {
        $default_title = function_exists( 'developer_starter_get_locale_text' )
            ? developer_starter_get_locale_text( '启灵主题', 'Qiling Theme' )
            : __( '启灵主题', 'qiling' );
        $default_subtitle = function_exists( 'developer_starter_get_locale_text' )
            ? developer_starter_get_locale_text( '开源模块化主题', 'An open modular theme for modern websites' )
            : __( '开源模块化主题', 'qiling' );
        $default_price = function_exists( 'developer_starter_get_demo_price_text' )
            ? developer_starter_get_demo_price_text( 199 )
            : '¥199';
        $default_original_price = function_exists( 'developer_starter_get_demo_price_text' )
            ? developer_starter_get_demo_price_text( 299 )
            : '¥299';
        $default_cta_text = function_exists( 'developer_starter_get_locale_text' )
            ? developer_starter_get_locale_text( '前往购买', 'Buy Now' )
            : __( '前往购买', 'qiling' );
        $default_description = function_exists( 'developer_starter_get_locale_text' )
            ? developer_starter_get_locale_text(
                "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼用PHP：7.4及以上，推荐8.0版本\n授权时间：永久授权使用，免费更新",
                "Latest version: V2.5\nUpdated: 2026-01-01\nWP support: 6.0-6.9, latest recommended\nPHP support: 7.4+, 8.0 recommended\nLicense: lifetime access with free updates"
            )
            : __( "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼用PHP：7.4及以上，推荐8.0版本\n授权时间：永久授权使用，免费更新", 'qiling' );

        $legacy_layout = isset( $data['ps_layout'] ) ? (string) $data['ps_layout'] : 'left';
        if ( 'left_media' === $legacy_layout ) {
            $legacy_layout = 'left';
        } elseif ( 'right_media' === $legacy_layout ) {
            $legacy_layout = 'right';
        }

        $legacy_bg_type = isset( $data['ps_bg_type'] ) ? (string) $data['ps_bg_type'] : '';
        $legacy_bg_gradient = isset( $data['ps_bg_gradient'] ) ? trim( (string) $data['ps_bg_gradient'] ) : '';
        $legacy_buttons = ( isset( $data['ps_buttons'] ) && is_array( $data['ps_buttons'] ) ) ? $data['ps_buttons'] : array();
        $legacy_primary_button = ! empty( $legacy_buttons[0] ) && is_array( $legacy_buttons[0] ) ? $legacy_buttons[0] : array();

        // 获取配置
        $bg_color = isset( $data['ps_bg_color'] ) && '' !== trim( (string) $data['ps_bg_color'] ) ? $data['ps_bg_color'] : '';
        if ( '' === $bg_color && 'gradient' === $legacy_bg_type && '' !== $legacy_bg_gradient ) {
            $bg_color = $legacy_bg_gradient;
        }

        $padding = isset( $data['ps_padding'] ) && '' !== (string) $data['ps_padding'] ? intval( $data['ps_padding'] ) : 80;
        if ( $padding <= 0 ) {
            $padding = 80;
        }
        $layout = '' !== $legacy_layout ? $legacy_layout : 'left';

        $media_items = isset( $data['ps_media_items'] ) && is_array( $data['ps_media_items'] ) ? $data['ps_media_items'] : array();
        $media_height = isset( $data['ps_media_height'] ) && '' !== trim( (string) $data['ps_media_height'] ) ? $data['ps_media_height'] : '450px';
        $media_radius = isset( $data['ps_media_radius'] ) && '' !== trim( (string) $data['ps_media_radius'] ) ? $data['ps_media_radius'] : '16px';

        $left_buttons = isset( $data['ps_left_buttons'] ) && is_array( $data['ps_left_buttons'] ) ? $data['ps_left_buttons'] : array();

        $title = isset( $data['ps_title'] ) && '' !== $data['ps_title'] ? $data['ps_title'] : $default_title;
        $title_color = isset( $data['ps_title_color'] ) ? $data['ps_title_color'] : '';
        $subtitle = isset( $data['ps_subtitle'] ) && '' !== $data['ps_subtitle'] ? $data['ps_subtitle'] : $default_subtitle;
        $subtitle_color = isset( $data['ps_subtitle_color'] ) ? $data['ps_subtitle_color'] : '';

        $show_price = isset( $data['ps_show_price'] ) && in_array( $data['ps_show_price'], array( 'yes', 'no' ), true ) ? $data['ps_show_price'] : 'yes';
        $price = isset( $data['ps_price'] ) && '' !== $data['ps_price'] ? $data['ps_price'] : $default_price;
        $price_color = isset( $data['ps_price_color'] ) ? $data['ps_price_color'] : '';
        $original_price = isset( $data['ps_original_price'] ) && '' !== $data['ps_original_price'] ? $data['ps_original_price'] : $default_original_price;

        $cta_text = isset( $data['ps_cta_text'] ) && '' !== $data['ps_cta_text'] ? $data['ps_cta_text'] : '';
        if ( '' === $cta_text && ! empty( $legacy_primary_button['text'] ) ) {
            $cta_text = $legacy_primary_button['text'];
        }
        if ( '' === $cta_text ) {
            $cta_text = $default_cta_text;
        }

        $cta_url = isset( $data['ps_cta_url'] ) && '' !== $data['ps_cta_url'] ? $data['ps_cta_url'] : '';
        if ( '' === $cta_url && ! empty( $legacy_primary_button['url'] ) ) {
            $cta_url = $legacy_primary_button['url'];
        }
        if ( '' === $cta_url ) {
            $cta_url = '#';
        }

        $cta_bg = isset( $data['ps_cta_bg'] ) && '' !== trim( (string) $data['ps_cta_bg'] )
            ? $data['ps_cta_bg']
            : 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)';
        $cta_color = isset( $data['ps_cta_color'] ) && '' !== trim( (string) $data['ps_cta_color'] ) ? $data['ps_cta_color'] : 'var(--color-neutral-0)';
        $cta_border_color = isset( $data['ps_cta_border_color'] ) && '' !== trim( (string) $data['ps_cta_border_color'] ) ? $data['ps_cta_border_color'] : $cta_bg;
        $cta_target = isset( $data['ps_cta_target'] ) && '' !== $data['ps_cta_target'] ? $data['ps_cta_target'] : '';
        if ( '' === $cta_target && ! empty( $legacy_primary_button['target'] ) ) {
            $cta_target = $legacy_primary_button['target'];
        }
        if ( '' === $cta_target ) {
            $cta_target = '_self';
        }

        $description = isset( $data['ps_description'] ) && '' !== $data['ps_description'] ? $data['ps_description'] : $default_description;
        $desc_color = isset( $data['ps_desc_color'] ) ? $data['ps_desc_color'] : '';

        // 配色/材质色块联动 (Swatches)
        $enable_swatches = isset( $data['ps_enable_swatches'] ) && in_array( $data['ps_enable_swatches'], array( 'yes', 'no' ), true ) ? $data['ps_enable_swatches'] : 'no';
        $swatches_title  = isset( $data['ps_swatches_title'] ) && '' !== trim( (string) $data['ps_swatches_title'] ) ? $data['ps_swatches_title'] : __( '可选款式/配色', 'qiling' );
        $swatches_list   = isset( $data['ps_swatches_list'] ) && is_array( $data['ps_swatches_list'] ) ? $data['ps_swatches_list'] : array();
        
        $module_id = 'ps-' . uniqid();
        
        // 背景样式
        $section_style = "padding: {$padding}px 0;";
        if ( $bg_color ) {
            if ( strpos( $bg_color, 'gradient' ) !== false ) {
                $section_style .= " background: {$bg_color};";
            } else {
                $section_style .= " background-color: {$bg_color};";
            }
        }
        
        // 解析描述信息
        $desc_lines = array_filter( array_map( 'trim', explode( "\n", $description ) ) );
        ?>
        <style>
            #<?php echo esc_attr( $module_id ); ?> .ps-swatches-section {
                margin: 18px 0 22px;
                padding: 12px 14px;
                background: rgba(0, 0, 0, 0.02);
                border: 1px solid rgba(0, 0, 0, 0.06);
                border-radius: 12px;
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatches-label-bar {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 10px;
                font-size: 13px;
                font-weight: 600;
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatches-title {
                color: var(--color-neutral-600, #475569);
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatches-selected {
                color: var(--color-primary, #2563eb);
                font-weight: 700;
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatches-list {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatch-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 6px 14px 6px 8px;
                background: var(--color-surface, #ffffff);
                border: 1.5px solid var(--color-border, #e2e8f0);
                border-radius: 9999px;
                cursor: pointer;
                font-size: 13px;
                color: var(--color-text, #1e293b);
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                outline: none;
                user-select: none;
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatch-btn:hover {
                border-color: var(--color-border-hover, #94a3b8);
                transform: translateY(-1px);
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatch-btn.is-active {
                border-color: var(--color-primary, #2563eb);
                background: color-mix(in srgb, var(--color-primary, #2563eb) 10%, transparent);
                color: var(--color-primary, #2563eb);
                box-shadow: 0 0 0 1px var(--color-primary, #2563eb);
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatch-color {
                width: 16px;
                height: 16px;
                border-radius: 50%;
                border: 1px solid rgba(0, 0, 0, 0.12);
                flex-shrink: 0;
                display: inline-block;
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-swatch-name {
                font-weight: 600;
                line-height: 1;
            }
            #<?php echo esc_attr( $module_id ); ?> .ps-media-carousel img {
                transition: opacity 0.25s ease-in-out;
            }
        </style>
        <section class="module module-product-showcase ps-fullwidth" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <div class="ps-wrapper <?php echo $layout === 'right' ? 'ps-reverse' : ''; ?>">
                    <!-- 左侧：媒体轮播 + 小按钮 -->
                    <div class="ps-media-side">
                        <div class="ps-media-carousel" style="height: <?php echo esc_attr( $media_height ); ?>; border-radius: <?php echo esc_attr( $media_radius ); ?>;">
                            <?php if ( ! empty( $media_items ) && count( $media_items ) > 1 ) : ?>
                                <div class="swiper ps-swiper">
                                    <div class="swiper-wrapper">
                                        <?php foreach ( $media_items as $item ) : ?>
                                            <div class="swiper-slide">
                                                <?php $this->render_media_item( $item ); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                </div>
                            <?php elseif ( ! empty( $media_items ) ) : ?>
                                <?php $this->render_media_item( $media_items[0] ); ?>
                            <?php else : ?>
                                <div class="ps-media-placeholder">
                                    <span><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg></span>
                                    <p><?php esc_html_e( '请添加图片或视频', 'qiling' ); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ( ! empty( $left_buttons ) ) : ?>
                            <div class="ps-left-buttons">
                                <?php foreach ( $left_buttons as $btn ) : ?>
                                    <?php if ( ! empty( $btn['text'] ) ) : ?>
                                        <a href="<?php echo esc_url( $btn['url'] ?: '#' ); ?>" 
                                           class="ps-small-btn"
                                           target="<?php echo esc_attr( $btn['target'] ?: '_self' ); ?>">
                                            <?php if ( ! empty( $btn['icon'] ) ) : ?>
                                                <span class="ps-btn-icon"><?php echo esc_html( $btn['icon'] ); ?></span>
                                            <?php endif; ?>
                                            <span><?php echo wp_kses_post( $btn['text'] ); ?></span>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 右侧：产品信息 -->
                    <div class="ps-info-side">
                        <?php if ( $title ) : ?>
                            <h2 class="ps-title" <?php echo $title_color ? 'style="color:' . esc_attr( $title_color ) . ';"' : ''; ?>>
                                <?php echo wp_kses_post( $title ); ?>
                            </h2>
                        <?php endif; ?>
                        
                        <?php if ( $subtitle ) : ?>
                            <div class="ps-subtitle" <?php echo $subtitle_color ? 'style="color:' . esc_attr( $subtitle_color ) . ';"' : ''; ?>>
                                <?php echo wp_kses_post( $subtitle ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $show_price === 'yes' && ( $price || $original_price ) ) : ?>
                            <div class="ps-price-row">
                                <?php if ( $price ) : ?>
                                    <span class="ps-price" <?php echo $price_color ? 'style="color:' . esc_attr( $price_color ) . ';"' : ''; ?>>
                                        <?php echo wp_kses_post( $price ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( $original_price ) : ?>
                                    <span class="ps-original-price"><?php echo esc_html( $original_price ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( 'yes' === $enable_swatches && ! empty( $swatches_list ) ) : ?>
                            <div class="ps-swatches-section">
                                <div class="ps-swatches-label-bar">
                                    <span class="ps-swatches-title"><?php echo esc_html( $swatches_title ); ?>：</span>
                                    <span class="ps-swatches-selected" data-ps-selected-swatch><?php echo esc_html( $swatches_list[0]['name'] ?? '' ); ?></span>
                                </div>
                                <div class="ps-swatches-list" role="radiogroup" aria-label="<?php echo esc_attr( $swatches_title ); ?>">
                                    <?php foreach ( $swatches_list as $s_idx => $swatch ) :
                                        $s_name  = isset( $swatch['name'] ) ? sanitize_text_field( (string) $swatch['name'] ) : '';
                                        $s_color = isset( $swatch['color_code'] ) && '' !== trim( (string) $swatch['color_code'] ) ? sanitize_text_field( (string) $swatch['color_code'] ) : '#2563eb';
                                        $s_img   = isset( $swatch['image'] ) ? esc_url_raw( (string) $swatch['image'] ) : '';
                                        $s_price = isset( $swatch['price'] ) ? sanitize_text_field( (string) $swatch['price'] ) : '';
                                    ?>
                                        <button type="button" 
                                                class="ps-swatch-btn <?php echo 0 === $s_idx ? 'is-active' : ''; ?>"
                                                data-swatch-name="<?php echo esc_attr( $s_name ); ?>"
                                                data-swatch-img="<?php echo esc_url( $s_img ); ?>"
                                                data-swatch-price="<?php echo esc_attr( $s_price ); ?>"
                                                title="<?php echo esc_attr( $s_name ); ?>"
                                                aria-label="<?php echo esc_attr( $s_name ); ?>">
                                            <span class="ps-swatch-color" style="background: <?php echo esc_attr( $s_color ); ?>;"></span>
                                            <span class="ps-swatch-name"><?php echo esc_html( $s_name ); ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $cta_text ) : ?>
                            <div class="ps-cta-wrapper">
                                <a href="<?php echo esc_url( $cta_url ); ?>" 
                                   class="ps-cta-btn"
                                   target="<?php echo esc_attr( $cta_target ); ?>"
                                   style="background: <?php echo esc_attr( $cta_bg ); ?>; color: <?php echo esc_attr( $cta_color ); ?>; border-color: <?php echo esc_attr( $cta_border_color ); ?>;">
                                    <?php echo wp_kses_post( $cta_text ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $desc_lines ) ) : ?>
                            <div class="ps-description" <?php echo $desc_color ? 'style="color:' . esc_attr( $desc_color ) . ';"' : ''; ?>>
                                <?php foreach ( $desc_lines as $line ) : ?>
                                    <div class="ps-desc-line"><?php echo wp_kses_post( $line ); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
<script>
            (function() {
            function boot() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root || root.dataset.productShowcaseInitialized) return;
                root.dataset.productShowcaseInitialized = 'true';
                var psSwiper = root.querySelector('.ps-swiper');
                if (psSwiper && typeof Swiper !== 'undefined') {
                    new Swiper(psSwiper, {
                        loop: true,
                        autoplay: {
                            delay: 5000,
                            disableOnInteraction: false,
                        },
                        pagination: {
                            el: root.querySelector('.swiper-pagination'),
                            clickable: true,
                        },
                        navigation: {
                            nextEl: root.querySelector('.swiper-button-next'),
                            prevEl: root.querySelector('.swiper-button-prev'),
                        },
                        effect: 'fade',
                        fadeEffect: {
                            crossFade: true
                        }
                    });
                }

                // Swatches interaction
                var swatchBtns = root.querySelectorAll('.ps-swatch-btn');
                var selectedLabel = root.querySelector('[data-ps-selected-swatch]');
                var priceEl = root.querySelector('.ps-price');
                var mediaContainer = root.querySelector('.ps-media-carousel');

                if (swatchBtns.length > 0) {
                    swatchBtns.forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            swatchBtns.forEach(function(b) { b.classList.remove('is-active'); });
                            btn.classList.add('is-active');
                            var sName = btn.getAttribute('data-swatch-name') || '';
                            var sPrice = btn.getAttribute('data-swatch-price') || '';
                            var sImg = btn.getAttribute('data-swatch-img') || '';

                            if (selectedLabel && sName) {
                                selectedLabel.textContent = sName;
                            }
                            if (priceEl && sPrice) {
                                priceEl.textContent = sPrice;
                            }
                            if (sImg && mediaContainer) {
                                var mainImg = mediaContainer.querySelector('img');
                                if (mainImg) {
                                    mainImg.style.opacity = '0.35';
                                    setTimeout(function() {
                                        mainImg.src = sImg;
                                        mainImg.style.opacity = '1';
                                    }, 140);
                                }
                            }
                        });
                    });
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
    
    /**
     * 渲染单个媒体项
     */
    private function render_media_item( $item ) {
        $type = isset( $item['type'] ) ? $item['type'] : 'image';
        ?>
        <div class="ps-media-item">
            <?php if ( $type === 'video' && ! empty( $item['video_url'] ) ) : ?>
                <?php 
                $video_url = $item['video_url'];
                $poster = isset( $item['video_poster'] ) ? $item['video_poster'] : '';
                
                // 检查是否是嵌入式视频（B站、优酷等）
                if ( strpos( $video_url, 'bilibili.com' ) !== false || 
                     strpos( $video_url, 'youku.com' ) !== false ||
                     strpos( $video_url, 'youtube.com' ) !== false ||
                     strpos( $video_url, 'youtu.be' ) !== false ) :
                ?>
                    <?php $embed_url = $this->convert_video_embed_url( $video_url ); ?>
                    <div class="ds-lazy-embed ds-lazy-embed-product" data-src="<?php echo esc_url( $embed_url ); ?>" data-autoplay="1">
                        <button type="button" class="ds-lazy-embed-trigger" aria-label="<?php esc_attr_e( '播放视频', 'qiling' ); ?>">
                            <?php if ( $poster ) : ?>
                                <img src="<?php echo esc_url( $poster ); ?>" alt="" class="ds-lazy-embed-poster" loading="lazy" />
                            <?php endif; ?>
                            <span class="ds-lazy-embed-play" aria-hidden="true">▶</span>
                        </button>
                    </div>
                <?php else : ?>
                    <video controls 
                           <?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>
                           preload="metadata">
                        <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
                    </video>
                <?php endif; ?>
            <?php elseif ( ! empty( $item['image'] ) ) : ?>
                <img src="<?php echo esc_url( $item['image'] ); ?>" alt="">
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * 转换视频URL为嵌入格式
     */
    private function convert_video_embed_url( $url ) {
        // B站
        if ( preg_match( '/bilibili\.com\/video\/(BV[a-zA-Z0-9]+)/', $url, $matches ) ) {
            return 'https://player.bilibili.com/player.html?bvid=' . $matches[1] . '&high_quality=1';
        }
        
        // YouTube
        if ( preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches ) ) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        if ( preg_match( '/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches ) ) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        
        return $url;
    }
}
