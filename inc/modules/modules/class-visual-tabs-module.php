<?php
/**
 * Visual Tabs Module - 图卡切换
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Visual_Tabs_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-screenoptions';
        $this->description = __( '顶部图卡导航联动下方内容面板，支持切换、跳转或纯展示。', 'developer-starter' );
    }

    public function get_id() {
        return 'visual_tabs';
    }

    public function get_name() {
        return __( '图卡切换', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id' => 'visual_tabs_title',
                'label' => __( '标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '图卡切换展示', 'Visual Tab Showcase' ) : __( '图卡切换展示', 'developer-starter' ),
            ),
            array(
                'id' => 'visual_tabs_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合做主图导航、专题入口、模块化内容切换。', 'Use image cards as content tabs, feature entries, or visual navigation.' ) : __( '适合做主图导航、专题入口、模块化内容切换。', 'developer-starter' ),
            ),
            array(
                'id' => 'visual_tabs_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'visual_tabs_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'visual_tabs_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.05rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'visual_tabs_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'visual_tabs_content_layout',
                'label' => __( '内容区域布局', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'single' => __( '单面板', 'developer-starter' ),
                    'dual'   => __( '双面板', 'developer-starter' ),
                ),
                'default' => 'dual',
            ),
            array(
                'id' => 'visual_tabs_columns',
                'label' => __( '每行图卡数量', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                    '5' => __( '5列', 'developer-starter' ),
                ),
                'default' => '4',
            ),
            array(
                'id' => 'visual_tabs_card_image_height',
                'label' => __( '图卡图片高度', 'developer-starter' ),
                'type' => 'text',
                'default' => '160px',
                'description' => __( '如 160px、12rem', 'developer-starter' ),
            ),
            array(
                'id' => 'visual_tabs_accent_color',
                'label' => __( '强调色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-primary)',
            ),
            array(
                'id' => 'visual_tabs_card_bg',
                'label' => __( '图卡背景色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id' => 'visual_tabs_card_active_bg',
                'label' => __( '激活图卡背景色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--qiling-color-eff6ff)',
            ),
            array(
                'id' => 'visual_tabs_panel_bg',
                'label' => __( '内容面板背景色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id' => 'visual_tabs_btn_bg_color',
                'label' => __( '面板按钮背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响下方内容面板里的按钮，留空时跟随全局设计', 'developer-starter' ),
            ),
            array(
                'id' => 'visual_tabs_btn_text_color',
                'label' => __( '面板按钮文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响下方内容面板里的按钮，留空时跟随全局设计', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'visual_tabs_btn_border_color', __( '面板按钮边框颜色', 'developer-starter' ) ),
            array(
                'id' => 'visual_tabs_btn_hover_bg_color',
                'label' => __( '面板按钮悬停背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响下方内容面板里的按钮悬停状态，不影响上方图卡高亮色', 'developer-starter' ),
            ),
            array(
                'id' => 'visual_tabs_btn_hover_text_color',
                'label' => __( '面板按钮悬停文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响下方内容面板里的按钮悬停状态，不影响上方图卡高亮色', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'visual_tabs_btn_hover_border_color', __( '面板按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随面板按钮悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id' => 'visual_tabs_items',
                'label' => __( '图卡列表', 'developer-starter' ),
                'type' => 'repeater',
                'description' => __( '每张图卡都可以设置为切换内容、跳转链接或纯展示。', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id' => 'card_title',
                        'label' => __( '图卡标题', 'developer-starter' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'card_subtitle',
                        'label' => __( '图卡副标题', 'developer-starter' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'card_badge',
                        'label' => __( '角标/标签', 'developer-starter' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'card_image',
                        'label' => __( '图卡图片', 'developer-starter' ),
                        'type' => 'image',
                    ),
                    array(
                        'id' => 'card_action',
                        'label' => __( '点击行为', 'developer-starter' ),
                        'type' => 'select',
                        'options' => array(
                            'switch' => __( '切换下方内容', 'developer-starter' ),
                            'link'   => __( '跳转到链接', 'developer-starter' ),
                            'static' => __( '纯展示不点击', 'developer-starter' ),
                        ),
                        'default' => 'switch',
                    ),
                    array(
                        'id' => 'card_link',
                        'label' => __( '跳转链接', 'developer-starter' ),
                        'type' => 'text',
                        'default' => '',
                    ),
                    array(
                        'id' => 'card_link_target',
                        'label' => __( '链接打开方式', 'developer-starter' ),
                        'type' => 'select',
                        'options' => array(
                            '_self'  => __( '当前窗口', 'developer-starter' ),
                            '_blank' => __( '新窗口', 'developer-starter' ),
                        ),
                        'default' => '_self',
                    ),
                    array(
                        'id' => 'content_title',
                        'label' => __( '主内容标题', 'developer-starter' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'content_text',
                        'label' => __( '主内容文本', 'developer-starter' ),
                        'type' => 'textarea',
                    ),
                    array(
                        'id' => 'content_secondary_title',
                        'label' => __( '次内容标题', 'developer-starter' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'content_secondary_text',
                        'label' => __( '次内容文本', 'developer-starter' ),
                        'type' => 'textarea',
                    ),
                    array(
                        'id' => 'content_button_text',
                        'label' => __( '内容按钮文字', 'developer-starter' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'content_button_url',
                        'label' => __( '内容按钮链接', 'developer-starter' ),
                        'type' => 'text',
                    ),
                ),
            ),
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
                'desc' => __( '支持 CSS 颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id' => 'module_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'default' => '',
            ),
            array(
                'id' => 'module_bg_overlay',
                'label' => __( '背景遮罩浓度', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '0'   => __( '无遮罩', 'developer-starter' ),
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
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };
        $title = isset( $data['visual_tabs_title'] ) && $data['visual_tabs_title'] !== ''
            ? $data['visual_tabs_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '图卡切换展示', 'Visual Tab Showcase' ) : __( '图卡切换展示', 'developer-starter' ) );
        $subtitle = isset( $data['visual_tabs_subtitle'] ) ? $data['visual_tabs_subtitle'] : '';
        $content_layout = isset( $data['visual_tabs_content_layout'] ) && in_array( $data['visual_tabs_content_layout'], array( 'single', 'dual' ), true )
            ? $data['visual_tabs_content_layout']
            : 'dual';
        $columns = isset( $data['visual_tabs_columns'] ) ? max( 2, min( 5, intval( $data['visual_tabs_columns'] ) ) ) : 4;
        $card_image_height = isset( $data['visual_tabs_card_image_height'] ) && $data['visual_tabs_card_image_height'] !== ''
            ? $data['visual_tabs_card_image_height']
            : '160px';
        $accent_color = isset( $data['visual_tabs_accent_color'] ) && $data['visual_tabs_accent_color'] !== ''
            ? $data['visual_tabs_accent_color']
            : 'var(--color-primary)';
        $card_bg = isset( $data['visual_tabs_card_bg'] ) && $data['visual_tabs_card_bg'] !== ''
            ? $data['visual_tabs_card_bg']
            : 'var(--color-neutral-0)';
        $card_active_bg = isset( $data['visual_tabs_card_active_bg'] ) && $data['visual_tabs_card_active_bg'] !== ''
            ? $data['visual_tabs_card_active_bg']
            : 'var(--qiling-color-eff6ff)';
        $panel_bg = isset( $data['visual_tabs_panel_bg'] ) && $data['visual_tabs_panel_bg'] !== ''
            ? $data['visual_tabs_panel_bg']
            : 'var(--color-neutral-0)';
        $btn_bg_color = isset( $data['visual_tabs_btn_bg_color'] ) ? $clean_css_value( $data['visual_tabs_btn_bg_color'] ) : '';
        $btn_text_color = isset( $data['visual_tabs_btn_text_color'] ) ? $clean_css_value( $data['visual_tabs_btn_text_color'] ) : '';
        $btn_border_color = isset( $data['visual_tabs_btn_border_color'] ) ? $clean_css_value( $data['visual_tabs_btn_border_color'] ) : '';
        $btn_hover_bg_color = isset( $data['visual_tabs_btn_hover_bg_color'] ) ? $clean_css_value( $data['visual_tabs_btn_hover_bg_color'] ) : '';
        $btn_hover_text_color = isset( $data['visual_tabs_btn_hover_text_color'] ) ? $clean_css_value( $data['visual_tabs_btn_hover_text_color'] ) : '';
        $btn_hover_border_color = isset( $data['visual_tabs_btn_hover_border_color'] ) ? $clean_css_value( $data['visual_tabs_btn_hover_border_color'] ) : '';
        $items = isset( $data['visual_tabs_items'] ) ? $data['visual_tabs_items'] : array();

        if ( empty( $items ) ) {
            $items = array(
                array(
                    'card_title'              => 'S版主图',
                    'card_subtitle'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合品牌主入口', 'Best for primary hero entry' ) : __( '适合品牌主入口', 'developer-starter' ),
                    'card_badge'              => 'S',
                    'card_action'             => 'switch',
                    'content_title'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '展示内容 1', 'Display Block 1' ) : __( '展示内容 1', 'developer-starter' ),
                    'content_text'            => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "这里可以放 S 版主图对应的文案、按钮、功能说明。\n支持做主图说明、专题介绍、产品卖点摘要。", "Use this area for the S-card headline, CTA, and supporting copy." ) : __( "这里可以放 S 版主图对应的文案、按钮、功能说明。\n支持做主图说明、专题介绍、产品卖点摘要。", 'developer-starter' ),
                    'content_secondary_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '展示内容 2', 'Display Block 2' ) : __( '展示内容 2', 'developer-starter' ),
                    'content_secondary_text'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "第二块可放补充说明、参数摘要、推荐入口。\n如果不需要，可以留空，模块会自动只显示一栏。", "Use the second panel for specs, quick links, or supplemental content." ) : __( "第二块可放补充说明、参数摘要、推荐入口。\n如果不需要，可以留空，模块会自动只显示一栏。", 'developer-starter' ),
                    'content_button_text'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看详情', 'Learn More' ) : __( '查看详情', 'developer-starter' ),
                    'content_button_url'      => '#',
                ),
                array(
                    'card_title'              => 'T版主图',
                    'card_subtitle'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合专题聚合', 'Good for topic collections' ) : __( '适合专题聚合', 'developer-starter' ),
                    'card_badge'              => 'T',
                    'card_action'             => 'switch',
                    'content_title'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'T 版内容区', 'T Panel' ) : __( 'T 版内容区', 'developer-starter' ),
                    'content_text'            => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "点击这张图卡后，可以切换到另一套说明内容。\n适合做频道介绍、入口导航、专题推荐。", "Switch to another content set for topic navigation or grouped highlights." ) : __( "点击这张图卡后，可以切换到另一套说明内容。\n适合做频道介绍、入口导航、专题推荐。", 'developer-starter' ),
                    'content_secondary_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '补充面板', 'Secondary Panel' ) : __( '补充面板', 'developer-starter' ),
                    'content_secondary_text'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '这里可以放次级说明、短列表、或者 CTA 之前的补充文案。', 'Use this area for extra notes, quick lists, or supporting CTA copy.' ) : __( '这里可以放次级说明、短列表、或者 CTA 之前的补充文案。', 'developer-starter' ),
                ),
                array(
                    'card_title'              => 'E版主图',
                    'card_subtitle'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '也可以设置成跳转', 'Can also work as a link card' ) : __( '也可以设置成跳转', 'developer-starter' ),
                    'card_badge'              => 'E',
                    'card_action'             => 'link',
                    'card_link'               => '#',
                    'card_link_target'        => '_self',
                    'content_title'           => '',
                    'content_text'            => '',
                ),
                array(
                    'card_title'              => 'M版主图',
                    'card_subtitle'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '也可以只做展示', 'Can remain display-only' ) : __( '也可以只做展示', 'developer-starter' ),
                    'card_badge'              => 'M',
                    'card_action'             => 'static',
                    'content_title'           => '',
                    'content_text'            => '',
                ),
            );
        }

        $title_size = isset( $data['visual_tabs_title_size'] ) ? $data['visual_tabs_title_size'] : '';
        $title_color = isset( $data['visual_tabs_title_color'] ) ? $data['visual_tabs_title_color'] : '';
        $subtitle_size = isset( $data['visual_tabs_subtitle_size'] ) ? $data['visual_tabs_subtitle_size'] : '';
        $subtitle_color = isset( $data['visual_tabs_subtitle_color'] ) ? $data['visual_tabs_subtitle_color'] : '';

        $title_style = '';
        if ( $title_size ) {
            $title_style .= "font-size: {$title_size};";
        }
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        $subtitle_style = '';
        if ( $subtitle_size ) {
            $subtitle_style .= "font-size: {$subtitle_size};";
        }
        if ( $subtitle_color ) {
            $subtitle_style .= "color: {$subtitle_color};";
        }

        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }

        $style_vars = array(
            "--vt-columns: {$columns}",
            "--vt-card-image-height: {$card_image_height}",
            "--vt-accent: {$accent_color}",
            "--vt-card-bg: {$card_bg}",
            "--vt-card-active-bg: {$card_active_bg}",
            "--vt-panel-bg: {$panel_bg}",
        );

        if ( '' !== $btn_bg_color ) {
            $style_vars[] = '--vt-btn-bg: ' . $btn_bg_color;
            $style_vars[] = '--vt-btn-border: ' . $btn_bg_color;
        }

        if ( '' !== $btn_text_color ) {
            $style_vars[] = '--vt-btn-text: ' . $btn_text_color;
        }

        if ( '' !== $btn_border_color ) {
            $style_vars[] = '--vt-btn-border: ' . $btn_border_color;
        }

        if ( '' !== $btn_hover_bg_color ) {
            $style_vars[] = '--vt-btn-hover-bg: ' . $btn_hover_bg_color;
            $style_vars[] = '--vt-btn-hover-border: ' . $btn_hover_bg_color;
        }

        if ( '' !== $btn_hover_text_color ) {
            $style_vars[] = '--vt-btn-hover-text: ' . $btn_hover_text_color;
        }

        if ( '' !== $btn_hover_border_color ) {
            $style_vars[] = '--vt-btn-hover-border: ' . $btn_hover_border_color;
        }

        $module_id = 'visual-tabs-' . uniqid();
        $active_index = -1;
        $has_switch_items = false;

        foreach ( $items as $index => $item ) {
            $action = isset( $item['card_action'] ) ? $item['card_action'] : 'switch';
            if ( ! in_array( $action, array( 'switch', 'link', 'static' ), true ) ) {
                $action = 'switch';
            }

            if ( $action === 'switch' ) {
                $has_switch_items = true;
                if ( $active_index < 0 ) {
                    $active_index = (int) $index;
                }
            }
        }
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-visual-tabs" style="<?php echo esc_attr( trim( $section_style . ' ' . implode( '; ', $style_vars ) . ';' ) ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>

            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo wp_kses_post( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo wp_kses_post( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="visual-tabs-shell visual-tabs-layout-<?php echo esc_attr( $content_layout ); ?>">
                    <div class="visual-tabs-grid">
                        <?php foreach ( $items as $index => $item ) : ?>
                            <?php
                            $card_title = isset( $item['card_title'] ) && $item['card_title'] !== '' ? $item['card_title'] : __( '图卡标题', 'developer-starter' );
                            $card_subtitle = isset( $item['card_subtitle'] ) ? $item['card_subtitle'] : '';
                            $card_badge = isset( $item['card_badge'] ) ? $item['card_badge'] : '';
                            $card_image = isset( $item['card_image'] ) ? $item['card_image'] : '';
                            $card_action = isset( $item['card_action'] ) ? $item['card_action'] : 'switch';
                            $card_link = isset( $item['card_link'] ) ? trim( (string) $item['card_link'] ) : '';
                            $card_link_target = isset( $item['card_link_target'] ) && in_array( $item['card_link_target'], array( '_self', '_blank' ), true )
                                ? $item['card_link_target']
                                : '_self';
                            $is_active = (int) $index === $active_index;
                            $pane_id = $module_id . '-pane-' . $index;
                            $placeholder_text = function_exists( 'mb_substr' ) ? mb_substr( wp_strip_all_tags( $card_title ), 0, 1 ) : substr( wp_strip_all_tags( $card_title ), 0, 1 );

                            if ( ! in_array( $card_action, array( 'switch', 'link', 'static' ), true ) ) {
                                $card_action = 'switch';
                            }
                            if ( $card_action === 'link' && $card_link === '' ) {
                                $card_action = 'static';
                            }
                            ?>

                            <?php if ( $card_action === 'switch' ) : ?>
                                <button
                                    type="button"
                                    class="visual-tabs-card <?php echo $is_active ? 'is-active' : ''; ?>"
                                    data-vt-card
                                    data-action="switch"
                                    data-index="<?php echo esc_attr( $index ); ?>"
                                    aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
                                    aria-controls="<?php echo esc_attr( $pane_id ); ?>"
                                >
                                    <div class="visual-tabs-card-media">
                                        <?php if ( $card_image ) : ?>
                                            <img src="<?php echo esc_url( $card_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $card_title ) ); ?>">
                                        <?php else : ?>
                                            <span class="visual-tabs-card-placeholder"><?php echo esc_html( $placeholder_text !== '' ? $placeholder_text : '图' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $card_badge ) : ?>
                                            <span class="visual-tabs-card-badge"><?php echo esc_html( $card_badge ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="visual-tabs-card-body">
                                        <h3 class="visual-tabs-card-title"><?php echo esc_html( $card_title ); ?></h3>
                                        <?php if ( $card_subtitle ) : ?>
                                            <p class="visual-tabs-card-subtitle"><?php echo esc_html( $card_subtitle ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </button>
                            <?php elseif ( $card_action === 'link' ) : ?>
                                <a
                                    class="visual-tabs-card visual-tabs-card-link"
                                    data-action="link"
                                    href="<?php echo esc_url( $card_link ); ?>"
                                    target="<?php echo esc_attr( $card_link_target ); ?>"
                                    <?php echo $card_link_target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                                >
                                    <div class="visual-tabs-card-media">
                                        <?php if ( $card_image ) : ?>
                                            <img src="<?php echo esc_url( $card_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $card_title ) ); ?>">
                                        <?php else : ?>
                                            <span class="visual-tabs-card-placeholder"><?php echo esc_html( $placeholder_text !== '' ? $placeholder_text : '图' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $card_badge ) : ?>
                                            <span class="visual-tabs-card-badge"><?php echo esc_html( $card_badge ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="visual-tabs-card-body">
                                        <h3 class="visual-tabs-card-title"><?php echo esc_html( $card_title ); ?></h3>
                                        <?php if ( $card_subtitle ) : ?>
                                            <p class="visual-tabs-card-subtitle"><?php echo esc_html( $card_subtitle ); ?></p>
                                        <?php endif; ?>
                                        <span class="visual-tabs-card-mode"><?php esc_html_e( '点击跳转', 'developer-starter' ); ?></span>
                                    </div>
                                </a>
                            <?php else : ?>
                                <div class="visual-tabs-card visual-tabs-card-static" data-action="static">
                                    <div class="visual-tabs-card-media">
                                        <?php if ( $card_image ) : ?>
                                            <img src="<?php echo esc_url( $card_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $card_title ) ); ?>">
                                        <?php else : ?>
                                            <span class="visual-tabs-card-placeholder"><?php echo esc_html( $placeholder_text !== '' ? $placeholder_text : '图' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $card_badge ) : ?>
                                            <span class="visual-tabs-card-badge"><?php echo esc_html( $card_badge ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="visual-tabs-card-body">
                                        <h3 class="visual-tabs-card-title"><?php echo esc_html( $card_title ); ?></h3>
                                        <?php if ( $card_subtitle ) : ?>
                                            <p class="visual-tabs-card-subtitle"><?php echo esc_html( $card_subtitle ); ?></p>
                                        <?php endif; ?>
                                        <span class="visual-tabs-card-mode"><?php esc_html_e( '纯展示', 'developer-starter' ); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if ( $has_switch_items ) : ?>
                        <div class="visual-tabs-stage">
                            <?php foreach ( $items as $index => $item ) : ?>
                                <?php
                                $card_action = isset( $item['card_action'] ) ? $item['card_action'] : 'switch';
                                if ( $card_action !== 'switch' ) {
                                    continue;
                                }

                                $card_title = isset( $item['card_title'] ) && $item['card_title'] !== '' ? $item['card_title'] : __( '图卡标题', 'developer-starter' );
                                $content_title = isset( $item['content_title'] ) && $item['content_title'] !== '' ? $item['content_title'] : $card_title;
                                $content_text = isset( $item['content_text'] ) ? $item['content_text'] : '';
                                $secondary_title = isset( $item['content_secondary_title'] ) ? $item['content_secondary_title'] : '';
                                $secondary_text = isset( $item['content_secondary_text'] ) ? $item['content_secondary_text'] : '';
                                $button_text = isset( $item['content_button_text'] ) ? $item['content_button_text'] : '';
                                $button_url = isset( $item['content_button_url'] ) ? trim( (string) $item['content_button_url'] ) : '';
                                $pane_has_secondary = $content_layout === 'dual' && ( $secondary_title !== '' || $secondary_text !== '' );
                                $is_active = (int) $index === $active_index;
                                $pane_id = $module_id . '-pane-' . $index;
                                ?>
                                <div
                                    id="<?php echo esc_attr( $pane_id ); ?>"
                                    class="visual-tabs-pane <?php echo $is_active ? 'is-active' : ''; ?>"
                                    data-vt-pane
                                    data-index="<?php echo esc_attr( $index ); ?>"
                                    <?php echo $is_active ? '' : 'hidden'; ?>
                                >
                                    <div class="visual-tabs-pane-grid <?php echo $pane_has_secondary ? 'has-secondary' : 'is-single'; ?>">
                                        <div class="visual-tabs-panel visual-tabs-panel-primary">
                                            <span class="visual-tabs-panel-meta"><?php echo esc_html( $card_title ); ?></span>
                                            <h3 class="visual-tabs-panel-title"><?php echo esc_html( $content_title ); ?></h3>
                                            <?php if ( $content_text ) : ?>
                                                <div class="visual-tabs-panel-content">
                                                    <?php echo wp_kses_post( wpautop( $content_text ) ); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ( $button_text && $button_url ) : ?>
                                                <a class="visual-tabs-panel-cta" href="<?php echo esc_url( $button_url ); ?>">
                                                    <?php echo esc_html( $button_text ); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ( $pane_has_secondary ) : ?>
                                            <div class="visual-tabs-panel visual-tabs-panel-secondary">
                                                <?php if ( $secondary_title ) : ?>
                                                    <h4 class="visual-tabs-panel-subtitle"><?php echo esc_html( $secondary_title ); ?></h4>
                                                <?php endif; ?>
                                                <?php if ( $secondary_text ) : ?>
                                                    <div class="visual-tabs-panel-content">
                                                        <?php echo wp_kses_post( wpautop( $secondary_text ) ); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="visual-tabs-empty">
                            <p><?php esc_html_e( '当前图卡全部设置为跳转或纯展示，所以这里不会显示联动内容。', 'developer-starter' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if ( $has_switch_items ) : ?>
            <script>
            (function() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root) return;

                var cards = root.querySelectorAll('[data-vt-card][data-action="switch"]');
                var panes = root.querySelectorAll('[data-vt-pane]');
                if (!cards.length || !panes.length) return;

                function activatePane(index) {
                    cards.forEach(function(card) {
                        var isActive = card.getAttribute('data-index') === index;
                        card.classList.toggle('is-active', isActive);
                        card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });

                    panes.forEach(function(pane) {
                        var isActive = pane.getAttribute('data-index') === index;
                        pane.classList.toggle('is-active', isActive);
                        if (isActive) {
                            pane.removeAttribute('hidden');
                        } else {
                            pane.setAttribute('hidden', 'hidden');
                        }
                    });
                }

                cards.forEach(function(card) {
                    card.addEventListener('click', function() {
                        activatePane(card.getAttribute('data-index'));
                    });
                });
            })();
            </script>
        <?php endif; ?>
        <?php
    }
}
