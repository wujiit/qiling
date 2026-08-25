<?php
/**
 * Hero Search Module - 图片视频搜索模块
 *
 * 全屏首屏模块，支持背景图片/视频轮播
 * 包含大标题、副标题、搜索框、关键词标签
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Hero_Search_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-search';
        $this->description = __( '全屏搜索首屏，支持背景图片/视频轮播', 'developer-starter' );
    }

    public function get_id() {
        return 'hero_search';
    }

    public function get_name() {
        return __( '图片视频搜索模块', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // === 模块整体设置 ===
            array(
                'id'      => 'hs_height',
                'type'    => 'select',
                'label'   => __( '模块高度', 'developer-starter' ),
                'options' => array(
                    '100vh' => __( '全屏高度', 'developer-starter' ),
                    '80vh'  => __( '80%屏幕高度', 'developer-starter' ),
                    '70vh'  => __( '70%屏幕高度', 'developer-starter' ),
                    '60vh'  => __( '60%屏幕高度', 'developer-starter' ),
                    '500px' => __( '500px固定高度', 'developer-starter' ),
                    '600px' => __( '600px固定高度', 'developer-starter' ),
                ),
                'default' => '80vh',
            ),
            array(
                'id'      => 'hs_width_mode',
                'type'    => 'select',
                'label'   => __( '前台显示宽度', 'developer-starter' ),
                'options' => array(
                    'full'      => __( '浏览器全屏宽度', 'developer-starter' ),
                    'container' => __( '跟随页面内容宽度', 'developer-starter' ),
                    'custom'    => __( '自定义最大宽度', 'developer-starter' ),
                ),
                'default' => 'full',
                'description' => __( '只控制模块整体宽度，不影响下方搜索框最大宽度设置。', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_custom_width',
                'type'    => 'text',
                'label'   => __( '模块自定义最大宽度', 'developer-starter' ),
                'default' => '1400px',
                'description' => __( '例如 1400px、90vw 或 80rem。移动端会自动保留安全边距。', 'developer-starter' ),
                'dependency' => array( 'id' => 'hs_width_mode', 'value' => 'custom' ),
            ),
            array(
                'id'      => 'hs_border_radius',
                'type'    => 'select',
                'label'   => __( '模块图片/视频圆角', 'developer-starter' ),
                'options' => array(
                    'none'   => __( '直角', 'developer-starter' ),
                    'small'  => __( '小圆角（8px）', 'developer-starter' ),
                    'medium' => __( '中圆角（16px）', 'developer-starter' ),
                    'large'  => __( '大圆角（24px）', 'developer-starter' ),
                    'custom' => __( '自定义圆角', 'developer-starter' ),
                ),
                'default' => 'none',
                'description' => __( '背景图片、背景视频和遮罩会随整个模块一起裁切。', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_custom_radius',
                'type'    => 'text',
                'label'   => __( '自定义圆角大小', 'developer-starter' ),
                'default' => '16px',
                'description' => __( '例如 12px、1rem 或 2vw。', 'developer-starter' ),
                'dependency' => array( 'id' => 'hs_border_radius', 'value' => 'custom' ),
            ),
            array(
                'id'      => 'hs_overlay_color',
                'type'    => 'text',
                'label'   => __( '背景遮罩颜色（如 var(--qiling-color-rgba-0-0-0-05)）', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-0-0-0-04)',
            ),
            array(
                'id'          => 'hs_overlay_opacity',
                'type'        => 'number',
                'label'       => __( '背景遮罩透明度', 'developer-starter' ),
                'default'     => '',
                'description' => __( '填写 0-100，设置遮罩颜色时以颜色值为准。', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_content_align',
                'type'    => 'select',
                'label'   => __( '内容对齐方式', 'developer-starter' ),
                'options' => array(
                    'center' => __( '居中', 'developer-starter' ),
                    'left'   => __( '左对齐', 'developer-starter' ),
                ),
                'default' => 'center',
            ),
            
            // === 背景媒体设置 ===
            array(
                'id'         => 'hs_bg_items',
                'type'       => 'repeater',
                'label'      => __( '背景媒体（图片或视频，多个则轮播）', 'developer-starter' ),
                'add_button' => __( '添加背景', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'      => 'type',
                        'type'    => 'select',
                        'label'   => __( '媒体类型', 'developer-starter' ),
                        'options' => array(
                            'image' => __( '🖼️ 图片 (image)', 'developer-starter' ),
                            'video' => __( '🎬 视频 (video)', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '背景图片（类型为图片时使用）', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'video_url',
                        'type'  => 'text',
                        'label' => __( '视频URL（类型为视频时使用，支持mp4直链）', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'hs_bg_autoplay',
                'type'    => 'select',
                'label'   => __( '背景轮播自动播放', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '是', 'developer-starter' ),
                    'no'  => __( '否', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'hs_bg_interval',
                'type'    => 'number',
                'label'   => __( '轮播间隔（毫秒）', 'developer-starter' ),
                'default' => '5000',
            ),
            
            // === 标题设置 ===
            array(
                'id'      => 'hs_title',
                'type'    => 'text',
                'label'   => __( '大标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '模块化企业主题', 'Modular Business Theme' ) : __( '模块化企业主题', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_title_color',
                'type'    => 'text',
                'label'   => __( '大标题颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'hs_title_size',
                'type'    => 'text',
                'label'   => __( '大标题字号', 'developer-starter' ),
                'default' => '3rem',
            ),
            array(
                'id'      => 'hs_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '高度自定义中文企业主题', 'A highly customizable theme for content-rich business websites.' ) : __( '高度自定义中文企业主题', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_subtitle_color',
                'type'    => 'text',
                'label'   => __( '副标题颜色', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-255-255-255-08)',
            ),
            
            // === 搜索框设置 ===
            array(
                'id'      => 'hs_show_search',
                'type'    => 'select',
                'label'   => __( '显示搜索框', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '是', 'developer-starter' ),
                    'no'  => __( '否', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'hs_search_placeholder',
                'type'    => 'text',
                'label'   => __( '搜索框占位符', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '搜索文章、资源、教程...', 'Search articles, resources, or guides...' ) : __( '搜索文章、资源、教程...', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_search_mode',
                'type'    => 'select',
                'label'   => __( '搜索模式', 'developer-starter' ),
                'options' => array_merge(
                    array( 'inherit' => __( '跟随主题设置', 'developer-starter' ) ),
                    function_exists( 'developer_starter_get_search_mode_choices' )
                        ? developer_starter_get_search_mode_choices()
                        : array( 'all' => __( '综合搜索', 'developer-starter' ), 'post' => __( '文章搜索', 'developer-starter' ) )
                ),
                'default' => 'inherit',
            ),
            array(
                'id'      => 'hs_search_btn_text',
                'type'    => 'text',
                'label'   => __( '搜索按钮文字', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '搜索', 'Search' ) : __( '搜索', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_search_btn_bg',
                'type'    => 'text',
                'label'   => __( '搜索按钮背景色(支持渐变)', 'developer-starter' ),
                'default' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)',
            ),
            array(
                'id'          => 'hs_search_btn_text_color',
                'type'        => 'color',
                'label'       => __( '搜索按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用搜索首屏默认按钮文字色', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'hs_search_btn_border_color', __( '搜索按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'          => 'hs_search_btn_hover_bg',
                'type'        => 'text',
                'label'       => __( '搜索按钮悬停背景色(支持渐变)', 'developer-starter' ),
                'description' => __( '留空时使用搜索首屏默认悬停背景', 'developer-starter' ),
            ),
            array(
                'id'          => 'hs_search_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '搜索按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用搜索首屏默认悬停文字色', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'hs_search_btn_hover_border_color', __( '搜索按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随搜索按钮悬停背景色。', 'developer-starter' ) ),
            array(
                'id'      => 'hs_search_width',
                'type'    => 'text',
                'label'   => __( '搜索框最大宽度', 'developer-starter' ),
                'default' => '600px',
            ),
            
            // === 关键词标签设置 ===
            array(
                'id'         => 'hs_tags',
                'type'       => 'repeater',
                'label'      => __( '关键词标签（标签文字即为搜索关键词）', 'developer-starter' ),
                'add_button' => __( '添加标签', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'text',
                        'type'  => 'text',
                        'label' => __( '标签文字（点击后搜索此关键词）', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'hs_tags_label',
                'type'    => 'text',
                'label'   => __( '标签前缀文字（如"热门搜索："）', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门搜索：', 'Popular topics:' ) : __( '热门搜索：', 'developer-starter' ),
            ),
            array(
                'id'      => 'hs_tags_bg',
                'type'    => 'text',
                'label'   => __( '标签背景色', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-255-255-255-015)',
            ),
            array(
                'id'      => 'hs_tags_color',
                'type'    => 'text',
                'label'   => __( '标签文字颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
        );
    }

    public function render( $data = array() ) {
        // 获取配置
        $height = isset( $data['hs_height'] ) ? $data['hs_height'] : '80vh';
        $width_mode = isset( $data['hs_width_mode'] ) ? sanitize_key( (string) $data['hs_width_mode'] ) : 'full';
        if ( ! in_array( $width_mode, array( 'full', 'container', 'custom' ), true ) ) {
            $width_mode = 'full';
        }
        $custom_width = $this->sanitize_css_length_value( isset( $data['hs_custom_width'] ) ? $data['hs_custom_width'] : '', '1400px' );
        $border_radius_mode = isset( $data['hs_border_radius'] ) ? sanitize_key( (string) $data['hs_border_radius'] ) : 'none';
        $border_radius_map = array(
            'none'   => '0',
            'small'  => '8px',
            'medium' => '16px',
            'large'  => '24px',
        );
        if ( 'custom' === $border_radius_mode ) {
            $border_radius = $this->sanitize_css_length_value( isset( $data['hs_custom_radius'] ) ? $data['hs_custom_radius'] : '', '16px', true );
        } else {
            $border_radius_mode = isset( $border_radius_map[ $border_radius_mode ] ) ? $border_radius_mode : 'none';
            $border_radius = $border_radius_map[ $border_radius_mode ];
        }
        $overlay_color = isset( $data['hs_overlay_color'] ) && '' !== trim( (string) $data['hs_overlay_color'] )
            ? $data['hs_overlay_color']
            : '';
        if ( '' === $overlay_color && isset( $data['hs_overlay_opacity'] ) && is_numeric( $data['hs_overlay_opacity'] ) ) {
            $overlay_opacity = max( 0, min( 100, (float) $data['hs_overlay_opacity'] ) ) / 100;
            $overlay_color = 'rgba(0, 0, 0, ' . $overlay_opacity . ')';
        }
        if ( '' === $overlay_color ) {
            $overlay_color = 'var(--qiling-color-rgba-0-0-0-04)';
        }
        $content_align = isset( $data['hs_content_align'] ) ? $data['hs_content_align'] : 'center';
        
        $bg_items = isset( $data['hs_bg_items'] ) ? $data['hs_bg_items'] : array();
        $bg_autoplay = isset( $data['hs_bg_autoplay'] ) ? $data['hs_bg_autoplay'] : 'yes';
        $bg_interval = isset( $data['hs_bg_interval'] ) ? intval( $data['hs_bg_interval'] ) : 5000;
        
        $title = isset( $data['hs_title'] ) ? $data['hs_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '模块化企业主题', 'Modular Business Theme' ) : __( '模块化企业主题', 'developer-starter' ) );
        $title_color = isset( $data['hs_title_color'] ) ? $data['hs_title_color'] : 'var(--color-neutral-0)';
        $title_size = isset( $data['hs_title_size'] ) ? $data['hs_title_size'] : '3rem';
        $subtitle = isset( $data['hs_subtitle'] ) ? $data['hs_subtitle'] : '';
        $subtitle_color = isset( $data['hs_subtitle_color'] ) ? $data['hs_subtitle_color'] : 'var(--qiling-color-rgba-255-255-255-08)';
        
        $show_search = isset( $data['hs_show_search'] ) ? $data['hs_show_search'] : 'yes';
        $search_placeholder = isset( $data['hs_search_placeholder'] ) ? $data['hs_search_placeholder'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '搜索文章、资源、教程...', 'Search articles, resources, or guides...' ) : __( '搜索文章、资源、教程...', 'developer-starter' ) );
        $search_mode_setting = isset( $data['hs_search_mode'] ) ? sanitize_key( (string) $data['hs_search_mode'] ) : 'inherit';
        $search_btn_text = isset( $data['hs_search_btn_text'] ) ? $data['hs_search_btn_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '搜索', 'Search' ) : __( '搜索', 'developer-starter' ) );
        $search_btn_bg = $this->sanitize_css_color_value( isset( $data['hs_search_btn_bg'] ) ? $data['hs_search_btn_bg'] : '', 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)' );
        $search_btn_text_color = $this->sanitize_css_color_value( isset( $data['hs_search_btn_text_color'] ) ? $data['hs_search_btn_text_color'] : '', '' );
        $search_btn_border_color = $this->sanitize_css_color_value( isset( $data['hs_search_btn_border_color'] ) ? $data['hs_search_btn_border_color'] : '', '' );
        $search_btn_hover_bg = $this->sanitize_css_color_value( isset( $data['hs_search_btn_hover_bg'] ) ? $data['hs_search_btn_hover_bg'] : '', '' );
        $search_btn_hover_text_color = $this->sanitize_css_color_value( isset( $data['hs_search_btn_hover_text_color'] ) ? $data['hs_search_btn_hover_text_color'] : '', '' );
        $search_btn_hover_border_color = $this->sanitize_css_color_value( isset( $data['hs_search_btn_hover_border_color'] ) ? $data['hs_search_btn_hover_border_color'] : '', '' );
        $search_width = isset( $data['hs_search_width'] ) ? $data['hs_search_width'] : '600px';
        
        $tags = isset( $data['hs_tags'] ) ? $data['hs_tags'] : array();
        $tags_label = isset( $data['hs_tags_label'] ) ? $data['hs_tags_label'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门搜索：', 'Popular topics:' ) : __( '热门搜索：', 'developer-starter' ) );
        $tags_bg = isset( $data['hs_tags_bg'] ) ? $data['hs_tags_bg'] : 'var(--qiling-color-rgba-255-255-255-015)';
        $tags_color = isset( $data['hs_tags_color'] ) ? $data['hs_tags_color'] : 'var(--color-neutral-0)';
        
        $module_id = 'hs-' . uniqid();
        $search_url = function_exists( 'developer_starter_get_search_form_action_url' ) ? developer_starter_get_search_form_action_url() : home_url( '/' );
        $search_use_rewrite = function_exists( 'developer_starter_get_option' ) && developer_starter_get_option( 'search_rewrite', '' );
        $search_mode = function_exists( 'developer_starter_resolve_search_mode' ) ? developer_starter_resolve_search_mode( $search_mode_setting ) : 'all';
        $search_btn_border_default = false === stripos( $search_btn_bg, 'gradient(' ) ? $search_btn_bg : 'var(--color-primary)';
        $section_style = 'height: ' . $height . '; --hs-search-btn-bg: ' . $search_btn_bg . ';';
        $section_style .= ' border-radius: ' . $border_radius . ';';
        if ( 'custom' === $width_mode ) {
            $section_style .= ' --hs-module-max-width: ' . $custom_width . ';';
        }
        $section_style .= ' --hs-search-btn-border: ' . ( '' !== $search_btn_border_color ? $search_btn_border_color : $search_btn_border_default ) . ';';
        if ( '' !== $search_btn_text_color ) {
            $section_style .= ' --hs-search-btn-text: ' . $search_btn_text_color . ';';
        }
        if ( '' !== $search_btn_hover_bg ) {
            $search_btn_hover_border_default = false === stripos( $search_btn_hover_bg, 'gradient(' ) ? $search_btn_hover_bg : $search_btn_border_default;
            $section_style .= ' --hs-search-btn-hover-bg: ' . $search_btn_hover_bg . ';';
            $section_style .= ' --hs-search-btn-hover-border: ' . $search_btn_hover_border_default . ';';
        }
        if ( '' !== $search_btn_hover_text_color ) {
            $section_style .= ' --hs-search-btn-hover-text: ' . $search_btn_hover_text_color . ';';
        }
        if ( '' !== $search_btn_hover_border_color ) {
            $section_style .= ' --hs-search-btn-hover-border: ' . $search_btn_hover_border_color . ';';
        }
        ?>
        <section class="module module-hero-search hs-width-<?php echo esc_attr( $width_mode ); ?>" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <!-- 背景层 -->
            <div class="hs-bg-layer">
                <?php if ( ! empty( $bg_items ) && count( $bg_items ) > 1 ) : ?>
                    <div class="swiper hs-bg-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ( $bg_items as $item ) : ?>
                                <div class="swiper-slide">
                                    <?php $this->render_bg_item( $item ); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif ( ! empty( $bg_items ) ) : ?>
                    <?php $this->render_bg_item( $bg_items[0] ); ?>
                <?php else : ?>
                    <div class="hs-bg-placeholder"></div>
                <?php endif; ?>
            </div>
            
            <!-- 遮罩层 -->
            <div class="hs-overlay" style="background: <?php echo esc_attr( $overlay_color ); ?>;"></div>
            
            <!-- 内容层 -->
            <div class="hs-content hs-align-<?php echo esc_attr( $content_align ); ?>" style="text-align: <?php echo esc_attr( $content_align ); ?>;">
                <div class="container">
                    <?php if ( $title ) : ?>
                        <h1 class="hs-title" style="color: <?php echo esc_attr( $title_color ); ?>; font-size: <?php echo esc_attr( $title_size ); ?>;">
                            <?php echo esc_html( $title ); ?>
                        </h1>
                    <?php endif; ?>
                    
                    <?php if ( $subtitle ) : ?>
                        <p class="hs-subtitle" style="color: <?php echo esc_attr( $subtitle_color ); ?>;">
                            <?php echo esc_html( $subtitle ); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ( $show_search === 'yes' ) : ?>
                        <form role="search" class="hs-search-form qiling-search-enhanced" data-qiling-search-form="1" action="<?php echo esc_url( $search_url ); ?>" method="get"<?php if ( $search_use_rewrite ) : ?> onsubmit="return dsSearchRedirect(this);"<?php endif; ?> style="max-width: <?php echo esc_attr( $search_width ); ?>; <?php echo $content_align === 'center' ? 'margin: 0 auto;' : ''; ?>">
                            <div class="hs-search-box">
                                <input type="search" name="s" class="hs-search-input" placeholder="<?php echo esc_attr( $search_placeholder ); ?>" autocomplete="off" data-qiling-search-input="1">
                                <input type="hidden" name="qiling_search_mode" value="<?php echo esc_attr( $search_mode ); ?>" />
                                <button type="submit" class="hs-search-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="M21 21l-4.35-4.35"/>
                                    </svg>
                                    <span><?php echo esc_html( $search_btn_text ); ?></span>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ( ! empty( $tags ) ) : ?>
                        <div class="hs-tags" style="<?php echo $content_align === 'center' ? 'justify-content: center;' : ''; ?>">
                            <?php if ( $tags_label ) : ?>
                                <span class="hs-tags-label" style="color: <?php echo esc_attr( $tags_color ); ?>;"><?php echo esc_html( $tags_label ); ?></span>
                            <?php endif; ?>
                            <?php foreach ( $tags as $tag ) : ?>
                                <?php if ( ! empty( $tag['text'] ) ) : 
                                    $tag_url = function_exists( 'developer_starter_get_search_pretty_url' ) ? developer_starter_get_search_pretty_url( $tag['text'], array( 'qiling_search_mode' => $search_mode ) ) : add_query_arg( array( 's' => $tag['text'], 'qiling_search_mode' => $search_mode ), home_url( '/' ) );
                                ?>
                                    <a href="<?php echo esc_url( $tag_url ); ?>" class="hs-tag" style="background: <?php echo esc_attr( $tags_bg ); ?>; color: <?php echo esc_attr( $tags_color ); ?>;">
                                        <?php echo esc_html( $tag['text'] ); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
<script>
            (function() {
            function boot() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root || root.dataset.heroSearchInitialized) return;
                root.dataset.heroSearchInitialized = 'true';
                var hsSwiper = root.querySelector('.hs-bg-swiper');
                if (hsSwiper && typeof Swiper !== 'undefined') {
                    new Swiper(hsSwiper, {
                        loop: true,
                        effect: 'fade',
                        fadeEffect: { crossFade: true },
                        <?php if ( $bg_autoplay === 'yes' ) : ?>
                        autoplay: {
                            delay: <?php echo $bg_interval; ?>,
                            disableOnInteraction: false,
                        },
                        <?php endif; ?>
                    });
                }
                
                // 背景视频自动播放
                var videos = root.querySelectorAll('.hs-bg-video');
                videos.forEach(function(video) {
                    video.muted = true;
                    video.play().catch(function() {});
                });
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
     * 渲染单个背景项
     */
    private function render_bg_item( $item ) {
        $type = isset( $item['type'] ) ? $item['type'] : 'image';
        
        if ( $type === 'video' && ! empty( $item['video_url'] ) ) {
            ?>
            <video class="hs-bg-video" autoplay muted loop playsinline>
                <source src="<?php echo esc_url( $item['video_url'] ); ?>" type="video/mp4">
            </video>
            <?php
        } elseif ( ! empty( $item['image'] ) ) {
            ?>
            <div class="hs-bg-image" style="background-image: url('<?php echo esc_url( $item['image'] ); ?>');"></div>
            <?php
        }
    }

    private function sanitize_css_color_value( $value, $default = '' ) {
        $value = is_string( $value ) ? trim( $value ) : '';

        if ( '' === $value ) {
            return $default;
        }

        $value = preg_replace( '/[\r\n\t]+/', ' ', $value );
        $value = str_replace( array( ';', '{', '}' ), '', $value );

        if ( preg_match( '/^var\(--[a-zA-Z0-9_-]+\)$/', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(rgba?|hsla?)\([^)]+\)$/', $value ) ) {
            return $value;
        }

        if ( false !== stripos( $value, 'gradient(' ) ) {
            return $value;
        }

        $hex = sanitize_hex_color( $value );

        return $hex ? $hex : $default;
    }

    private function sanitize_css_length_value( $value, $default, $allow_zero = false ) {
        $value = is_scalar( $value ) ? trim( (string) $value ) : '';
        if ( $allow_zero && '0' === $value ) {
            return '0';
        }
        if ( preg_match( '/^(?:\d+(?:\.\d+)?)(?:px|rem|em|vw|vh|%)$/i', $value ) ) {
            return $value;
        }
        if ( preg_match( '/^var\(--[a-zA-Z0-9_-]+\)$/', $value ) ) {
            return $value;
        }
        return $default;
    }
}
