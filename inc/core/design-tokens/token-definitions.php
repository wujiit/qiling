<?php
/**
 * 令牌定义数据配置。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            array( 'key' => 'primary', 'cssVar' => '--color-primary', 'group' => 'color', 'label' => __( '品牌主色', 'qiling' ) ),
            array( 'key' => 'primary_hover', 'cssVar' => '--color-primary-hover', 'group' => 'color', 'label' => __( '主色悬停', 'qiling' ) ),
            array( 'key' => 'secondary', 'cssVar' => '--color-secondary', 'group' => 'color', 'label' => __( '辅助色', 'qiling' ) ),
            array( 'key' => 'accent', 'cssVar' => '--color-accent', 'group' => 'color', 'label' => __( '点缀色', 'qiling' ) ),
            array( 'key' => 'success', 'cssVar' => '--color-success', 'group' => 'semantic', 'label' => __( '成功色', 'qiling' ) ),
            array( 'key' => 'info', 'cssVar' => '--color-info', 'group' => 'semantic', 'label' => __( '信息色', 'qiling' ) ),
            array( 'key' => 'warning', 'cssVar' => '--color-warning', 'group' => 'semantic', 'label' => __( '警告色', 'qiling' ) ),
            array( 'key' => 'error', 'cssVar' => '--color-error', 'group' => 'semantic', 'label' => __( '错误色', 'qiling' ) ),
            array( 'key' => 'overlay', 'cssVar' => '--color-overlay', 'group' => 'semantic', 'label' => __( '遮罩色', 'qiling' ) ),
            array( 'key' => 'text', 'cssVar' => '--color-text', 'group' => 'color', 'label' => __( '正文颜色', 'qiling' ) ),
            array( 'key' => 'text_muted', 'cssVar' => '--color-text-muted', 'group' => 'color', 'label' => __( '弱化文字', 'qiling' ) ),
            array( 'key' => 'heading', 'cssVar' => '--color-heading', 'group' => 'color', 'label' => __( '标题颜色', 'qiling' ) ),
            array( 'key' => 'background', 'cssVar' => '--color-background', 'group' => 'color', 'label' => __( '页面背景', 'qiling' ) ),
            array( 'key' => 'surface', 'cssVar' => '--color-surface', 'group' => 'color', 'label' => __( '卡片背景', 'qiling' ) ),
            array( 'key' => 'surface_alt', 'cssVar' => '--color-surface-alt', 'group' => 'color', 'label' => __( '浅色区块背景', 'qiling' ) ),
            array( 'key' => 'border', 'cssVar' => '--color-border', 'group' => 'color', 'label' => __( '边框颜色', 'qiling' ) ),
            array( 'key' => 'neutral_0', 'cssVar' => '--color-neutral-0', 'group' => 'neutral', 'label' => __( '中性色 0', 'qiling' ) ),
            array( 'key' => 'neutral_50', 'cssVar' => '--color-neutral-50', 'group' => 'neutral', 'label' => __( '中性色 50', 'qiling' ) ),
            array( 'key' => 'neutral_100', 'cssVar' => '--color-neutral-100', 'group' => 'neutral', 'label' => __( '中性色 100', 'qiling' ) ),
            array( 'key' => 'neutral_200', 'cssVar' => '--color-neutral-200', 'group' => 'neutral', 'label' => __( '中性色 200', 'qiling' ) ),
            array( 'key' => 'neutral_300', 'cssVar' => '--color-neutral-300', 'group' => 'neutral', 'label' => __( '中性色 300', 'qiling' ) ),
            array( 'key' => 'neutral_400', 'cssVar' => '--color-neutral-400', 'group' => 'neutral', 'label' => __( '中性色 400', 'qiling' ) ),
            array( 'key' => 'neutral_500', 'cssVar' => '--color-neutral-500', 'group' => 'neutral', 'label' => __( '中性色 500', 'qiling' ) ),
            array( 'key' => 'neutral_600', 'cssVar' => '--color-neutral-600', 'group' => 'neutral', 'label' => __( '中性色 600', 'qiling' ) ),
            array( 'key' => 'neutral_700', 'cssVar' => '--color-neutral-700', 'group' => 'neutral', 'label' => __( '中性色 700', 'qiling' ) ),
            array( 'key' => 'neutral_800', 'cssVar' => '--color-neutral-800', 'group' => 'neutral', 'label' => __( '中性色 800', 'qiling' ) ),
            array( 'key' => 'neutral_900', 'cssVar' => '--color-neutral-900', 'group' => 'neutral', 'label' => __( '中性色 900', 'qiling' ) ),
            array( 'key' => 'dark_bg', 'cssVar' => '--qiling-dark-bg', 'group' => 'dark', 'label' => __( '暗色背景', 'qiling' ) ),
            array( 'key' => 'dark_surface', 'cssVar' => '--qiling-dark-surface', 'group' => 'dark', 'label' => __( '暗色表面', 'qiling' ) ),
            array( 'key' => 'dark_text', 'cssVar' => '--qiling-dark-text', 'group' => 'dark', 'label' => __( '暗色正文', 'qiling' ) ),
            array( 'key' => 'dark_text_muted', 'cssVar' => '--qiling-dark-text-muted', 'group' => 'dark', 'label' => __( '暗色弱化文字', 'qiling' ) ),
            array( 'key' => 'dark_border', 'cssVar' => '--qiling-dark-border', 'group' => 'dark', 'label' => __( '暗色边框', 'qiling' ) ),
            array( 'key' => 'font_family', 'cssVar' => '--font-sans', 'group' => 'typography', 'label' => __( '全局字体族', 'qiling' ) ),
            array( 'key' => 'font_size_base', 'cssVar' => '--qiling-font-size-base', 'group' => 'typography', 'label' => __( '基础字号', 'qiling' ) ),
            array( 'key' => 'line_height_base', 'cssVar' => '--qiling-line-height-base', 'group' => 'typography', 'label' => __( '基础行高', 'qiling' ) ),
            array( 'key' => 'container_width', 'cssVar' => '--qiling-container-width', 'group' => 'layout', 'label' => __( '内容最大宽度', 'qiling' ) ),
            array( 'key' => 'section_padding', 'cssVar' => '--qiling-section-padding', 'group' => 'layout', 'label' => __( '区块上下间距', 'qiling' ) ),
            array( 'key' => 'grid_gap', 'cssVar' => '--qiling-grid-gap', 'group' => 'layout', 'label' => __( '栅格间距', 'qiling' ) ),
            array( 'key' => 'breakpoint_tablet', 'cssVar' => '--qiling-breakpoint-tablet', 'group' => 'layout', 'label' => __( '平板断点', 'qiling' ) ),
            array( 'key' => 'breakpoint_mobile', 'cssVar' => '--qiling-breakpoint-mobile', 'group' => 'layout', 'label' => __( '手机断点', 'qiling' ) ),
            array( 'key' => 'layout_mode', 'cssVar' => '--qiling-layout-mode', 'group' => 'layout', 'label' => __( '布局模式', 'qiling' ) ),
            array( 'key' => 'card_radius', 'cssVar' => '--qiling-card-radius', 'group' => 'component', 'label' => __( '卡片圆角', 'qiling' ) ),
            array( 'key' => 'button_radius', 'cssVar' => '--qiling-button-radius', 'group' => 'component', 'label' => __( '按钮圆角', 'qiling' ) ),
            array( 'key' => 'input_radius', 'cssVar' => '--qiling-input-radius', 'group' => 'component', 'label' => __( '输入框圆角', 'qiling' ) ),
            array( 'key' => 'shadow_sm', 'cssVar' => '--shadow-sm', 'group' => 'component', 'label' => __( '小阴影', 'qiling' ) ),
            array( 'key' => 'shadow_md', 'cssVar' => '--shadow-md', 'group' => 'component', 'label' => __( '中阴影', 'qiling' ) ),
            array( 'key' => 'shadow_lg', 'cssVar' => '--shadow-lg', 'group' => 'component', 'label' => __( '大阴影', 'qiling' ) ),
        );
