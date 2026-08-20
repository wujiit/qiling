<?php
/**
 * Data config for token definitions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            array( 'key' => 'primary', 'cssVar' => '--color-primary', 'group' => 'color', 'label' => __( '品牌主色', 'developer-starter' ) ),
            array( 'key' => 'primary_hover', 'cssVar' => '--color-primary-hover', 'group' => 'color', 'label' => __( '主色悬停', 'developer-starter' ) ),
            array( 'key' => 'secondary', 'cssVar' => '--color-secondary', 'group' => 'color', 'label' => __( '辅助色', 'developer-starter' ) ),
            array( 'key' => 'accent', 'cssVar' => '--color-accent', 'group' => 'color', 'label' => __( '点缀色', 'developer-starter' ) ),
            array( 'key' => 'success', 'cssVar' => '--color-success', 'group' => 'semantic', 'label' => __( '成功色', 'developer-starter' ) ),
            array( 'key' => 'info', 'cssVar' => '--color-info', 'group' => 'semantic', 'label' => __( '信息色', 'developer-starter' ) ),
            array( 'key' => 'warning', 'cssVar' => '--color-warning', 'group' => 'semantic', 'label' => __( '警告色', 'developer-starter' ) ),
            array( 'key' => 'error', 'cssVar' => '--color-error', 'group' => 'semantic', 'label' => __( '错误色', 'developer-starter' ) ),
            array( 'key' => 'overlay', 'cssVar' => '--color-overlay', 'group' => 'semantic', 'label' => __( '遮罩色', 'developer-starter' ) ),
            array( 'key' => 'text', 'cssVar' => '--color-text', 'group' => 'color', 'label' => __( '正文颜色', 'developer-starter' ) ),
            array( 'key' => 'text_muted', 'cssVar' => '--color-text-muted', 'group' => 'color', 'label' => __( '弱化文字', 'developer-starter' ) ),
            array( 'key' => 'heading', 'cssVar' => '--color-heading', 'group' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            array( 'key' => 'background', 'cssVar' => '--color-background', 'group' => 'color', 'label' => __( '页面背景', 'developer-starter' ) ),
            array( 'key' => 'surface', 'cssVar' => '--color-surface', 'group' => 'color', 'label' => __( '卡片背景', 'developer-starter' ) ),
            array( 'key' => 'surface_alt', 'cssVar' => '--color-surface-alt', 'group' => 'color', 'label' => __( '浅色区块背景', 'developer-starter' ) ),
            array( 'key' => 'border', 'cssVar' => '--color-border', 'group' => 'color', 'label' => __( '边框颜色', 'developer-starter' ) ),
            array( 'key' => 'neutral_0', 'cssVar' => '--color-neutral-0', 'group' => 'neutral', 'label' => __( '中性色 0', 'developer-starter' ) ),
            array( 'key' => 'neutral_50', 'cssVar' => '--color-neutral-50', 'group' => 'neutral', 'label' => __( '中性色 50', 'developer-starter' ) ),
            array( 'key' => 'neutral_100', 'cssVar' => '--color-neutral-100', 'group' => 'neutral', 'label' => __( '中性色 100', 'developer-starter' ) ),
            array( 'key' => 'neutral_200', 'cssVar' => '--color-neutral-200', 'group' => 'neutral', 'label' => __( '中性色 200', 'developer-starter' ) ),
            array( 'key' => 'neutral_300', 'cssVar' => '--color-neutral-300', 'group' => 'neutral', 'label' => __( '中性色 300', 'developer-starter' ) ),
            array( 'key' => 'neutral_400', 'cssVar' => '--color-neutral-400', 'group' => 'neutral', 'label' => __( '中性色 400', 'developer-starter' ) ),
            array( 'key' => 'neutral_500', 'cssVar' => '--color-neutral-500', 'group' => 'neutral', 'label' => __( '中性色 500', 'developer-starter' ) ),
            array( 'key' => 'neutral_600', 'cssVar' => '--color-neutral-600', 'group' => 'neutral', 'label' => __( '中性色 600', 'developer-starter' ) ),
            array( 'key' => 'neutral_700', 'cssVar' => '--color-neutral-700', 'group' => 'neutral', 'label' => __( '中性色 700', 'developer-starter' ) ),
            array( 'key' => 'neutral_800', 'cssVar' => '--color-neutral-800', 'group' => 'neutral', 'label' => __( '中性色 800', 'developer-starter' ) ),
            array( 'key' => 'neutral_900', 'cssVar' => '--color-neutral-900', 'group' => 'neutral', 'label' => __( '中性色 900', 'developer-starter' ) ),
            array( 'key' => 'dark_bg', 'cssVar' => '--qiling-dark-bg', 'group' => 'dark', 'label' => __( '暗色背景', 'developer-starter' ) ),
            array( 'key' => 'dark_surface', 'cssVar' => '--qiling-dark-surface', 'group' => 'dark', 'label' => __( '暗色表面', 'developer-starter' ) ),
            array( 'key' => 'dark_text', 'cssVar' => '--qiling-dark-text', 'group' => 'dark', 'label' => __( '暗色正文', 'developer-starter' ) ),
            array( 'key' => 'dark_text_muted', 'cssVar' => '--qiling-dark-text-muted', 'group' => 'dark', 'label' => __( '暗色弱化文字', 'developer-starter' ) ),
            array( 'key' => 'dark_border', 'cssVar' => '--qiling-dark-border', 'group' => 'dark', 'label' => __( '暗色边框', 'developer-starter' ) ),
            array( 'key' => 'font_family', 'cssVar' => '--font-sans', 'group' => 'typography', 'label' => __( '全局字体族', 'developer-starter' ) ),
            array( 'key' => 'font_size_base', 'cssVar' => '--qiling-font-size-base', 'group' => 'typography', 'label' => __( '基础字号', 'developer-starter' ) ),
            array( 'key' => 'line_height_base', 'cssVar' => '--qiling-line-height-base', 'group' => 'typography', 'label' => __( '基础行高', 'developer-starter' ) ),
            array( 'key' => 'container_width', 'cssVar' => '--qiling-container-width', 'group' => 'layout', 'label' => __( '内容最大宽度', 'developer-starter' ) ),
            array( 'key' => 'section_padding', 'cssVar' => '--qiling-section-padding', 'group' => 'layout', 'label' => __( '区块上下间距', 'developer-starter' ) ),
            array( 'key' => 'grid_gap', 'cssVar' => '--qiling-grid-gap', 'group' => 'layout', 'label' => __( '栅格间距', 'developer-starter' ) ),
            array( 'key' => 'breakpoint_tablet', 'cssVar' => '--qiling-breakpoint-tablet', 'group' => 'layout', 'label' => __( '平板断点', 'developer-starter' ) ),
            array( 'key' => 'breakpoint_mobile', 'cssVar' => '--qiling-breakpoint-mobile', 'group' => 'layout', 'label' => __( '手机断点', 'developer-starter' ) ),
            array( 'key' => 'layout_mode', 'cssVar' => '--qiling-layout-mode', 'group' => 'layout', 'label' => __( '布局模式', 'developer-starter' ) ),
            array( 'key' => 'card_radius', 'cssVar' => '--qiling-card-radius', 'group' => 'component', 'label' => __( '卡片圆角', 'developer-starter' ) ),
            array( 'key' => 'button_radius', 'cssVar' => '--qiling-button-radius', 'group' => 'component', 'label' => __( '按钮圆角', 'developer-starter' ) ),
            array( 'key' => 'input_radius', 'cssVar' => '--qiling-input-radius', 'group' => 'component', 'label' => __( '输入框圆角', 'developer-starter' ) ),
            array( 'key' => 'shadow_sm', 'cssVar' => '--shadow-sm', 'group' => 'component', 'label' => __( '小阴影', 'developer-starter' ) ),
            array( 'key' => 'shadow_md', 'cssVar' => '--shadow-md', 'group' => 'component', 'label' => __( '中阴影', 'developer-starter' ) ),
            array( 'key' => 'shadow_lg', 'cssVar' => '--shadow-lg', 'group' => 'component', 'label' => __( '大阴影', 'developer-starter' ) ),
        );
