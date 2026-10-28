<?php
/**
 * 主题后台设置配置定义 Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Config_Trait {

    /**
     * 获取生成连接选项。
     *
     * @return array<string,string>
     */
    private function get_ai_connection_choices() {
        $choices = array(
            '' => __( '请选择默认连接', 'qiling' ),
        );

        $options = get_option( 'developer_starter_options', array() );
        $connections = isset( $options['ai_connections'] ) && is_array( $options['ai_connections'] )
            ? $options['ai_connections']
            : array();

        foreach ( $connections as $connection ) {
            if ( ! is_array( $connection ) ) {
                continue;
            }

            $id = isset( $connection['id'] ) ? sanitize_key( (string) $connection['id'] ) : '';
            if ( '' === $id || empty( $connection['enabled'] ) ) {
                continue;
            }

            $name = isset( $connection['name'] ) ? sanitize_text_field( (string) $connection['name'] ) : $id;
            $choices[ $id ] = $name;
        }

        return $choices;
    }

    /**
     * 获取启灵推送通道选项
     *
     * @return array
     */
    private function get_qilinghook_channel_options() {
        $choices = array();

        $settings = get_option( 'qilinghook_settings', array() );
        if ( ! is_array( $settings ) || empty( $settings['channels'] ) || ! is_array( $settings['channels'] ) ) {
            return $choices;
        }

        foreach ( $settings['channels'] as $channel ) {
            if ( ! is_array( $channel ) ) {
                continue;
            }

            $id = isset( $channel['id'] ) ? preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $channel['id'] ) : '';
            if ( $id === '' ) {
                continue;
            }

            if ( empty( $channel['enabled'] ) ) {
                continue;
            }

            $name = isset( $channel['name'] ) && $channel['name'] !== '' ? (string) $channel['name'] : $id;
            $provider = isset( $channel['provider'] ) ? (string) $channel['provider'] : 'feishu';
            $provider_name = ( $provider === 'dingtalk' ) ? __( '钉钉', 'qiling' ) : __( '飞书', 'qiling' );

            $choices[ $id ] = sprintf( __( '%1$s（%2$s）', 'qiling' ), $name, $provider_name );
        }

        return $choices;
    }

    /**
     * 获取多语言默认语言下拉可选项（内置语言 + 已配置语言）。
     *
     * @return array<string, string>
     */
    private function get_multilingual_default_lang_choices() {
        $choices = array(
            'zh' => __( '中文（zh）', 'qiling' ),
            'en' => __( '英文（en）', 'qiling' ),
            'jp' => __( '日文（jp）', 'qiling' ),
            'ko' => __( '韩文（ko）', 'qiling' ),
            'fr' => __( '法文（fr）', 'qiling' ),
            'de' => __( '德文（de）', 'qiling' ),
            'es' => __( '西班牙文（es）', 'qiling' ),
            'ru' => __( '俄文（ru）', 'qiling' ),
        );

        $configured_languages = array();
        if ( function_exists( 'developer_starter_get_multilingual_languages' ) ) {
            $configured_languages = developer_starter_get_multilingual_languages();
        } elseif ( function_exists( 'developer_starter_get_option' ) ) {
            $configured_languages = developer_starter_get_option( 'multilingual_languages', array() );
        }

        if ( ! is_array( $configured_languages ) ) {
            return $choices;
        }

        foreach ( $configured_languages as $language ) {
            if ( ! is_array( $language ) ) {
                continue;
            }

            $code = isset( $language['code'] ) ? sanitize_title( (string) $language['code'] ) : '';
            if ( 'ja' === $code ) {
                $code = 'jp';
            }
            if ( '' === $code || isset( $choices[ $code ] ) ) {
                continue;
            }

            $name = isset( $language['name'] ) ? trim( (string) $language['name'] ) : '';
            if ( '' === $name ) {
                $name = strtoupper( $code );
            }

            $choices[ $code ] = sprintf( __( '%1$s（%2$s）', 'qiling' ), $name, $code );
        }

        return $choices;
    }

    private function get_tabs() {
        return array(
            'design'       => __( '全局样式', 'qiling' ),
            'models'       => __( '模型中心', 'qiling' ),
            'header'       => __( '顶部', 'qiling' ),
            'footer'       => __( '页脚', 'qiling' ),
            'article'      => __( '文章', 'qiling' ),
            'pages'        => __( '页面', 'qiling' ),
            'account_style' => __( '个人中心装修', 'qiling' ),
            'content'      => __( '内容', 'qiling' ),
            'announcement' => __( '公告', 'qiling' ),
            'submit'       => __( '投稿', 'qiling' ),
            'smtp'         => __( '邮件', 'qiling' ),
            'ai'           => __( 'AI装修', 'qiling' ),
            'advanced'     => __( '高级', 'qiling' ),
            'translate'    => __( '语言', 'qiling' ),
            'international' => __( '国际化', 'qiling' ),
            'optimize'     => __( '优化', 'qiling' ),
            'auth'         => __( '认证', 'qiling' ),
            'license'      => __( '授权', 'qiling' ),
            'documentation' => __( '主题说明', 'qiling' ),
            'plugins'       => __( '插件推荐', 'qiling' ),
            'backup'        => __( '备份恢复', 'qiling' ),
            'builder_revisions' => __( '装修修订', 'qiling' ),
        );
    }

    private function get_fields_config() {
        // 获取分类列表（供多个选项卡使用）
        $categories = get_categories( array( 'hide_empty' => false ) );
        $cat_options = array( '' => __( '全部分类', 'qiling' ) );
        $cat_id_options = array();
        foreach ( $categories as $cat ) {
            $cat_options[ $cat->slug ] = $cat->name;
            $cat_id_options[ (string) $cat->term_id ] = $cat->name;
        }

        $page_options = array( '' => __( '自动查找（会员中心）', 'qiling' ) );
        $footer_builder_page_options = array( '' => __( '不选择', 'qiling' ) );
        $pages = get_pages( array(
            'sort_column' => 'post_title',
            'sort_order'  => 'ASC',
            'post_status' => array( 'publish' ),
        ) );
        foreach ( $pages as $page ) {
            $page_options[ (string) $page->ID ] = $page->post_title . ' (#' . $page->ID . ')';
            $footer_builder_page_options[ (string) $page->ID ] = $page->post_title . ' (#' . $page->ID . ')';
        }
        $footer_wave_palette_options = array(
            'auto'        => __( '自动跟随当前页面', 'qiling' ),
            'soft_blue'   => __( '清亮蓝色', 'qiling' ),
            'warm_orange' => __( '暖橙营销', 'qiling' ),
            'fresh_green' => __( '清新绿色', 'qiling' ),
            'rose'        => __( '红粉活动', 'qiling' ),
            'violet'      => __( '柔紫科技', 'qiling' ),
        );
        if ( function_exists( 'developer_starter_get_footer_wave_palette_presets' ) ) {
            $footer_wave_palette_options = array();
            foreach ( developer_starter_get_footer_wave_palette_presets() as $palette_key => $palette ) {
                if ( empty( $palette['label'] ) ) {
                    continue;
                }
                $footer_wave_palette_options[ sanitize_key( (string) $palette_key ) ] = (string) $palette['label'];
            }
        }
        $submit_page_options = array( '' => __( '自动查找（投稿页面模板）', 'qiling' ) ) + $page_options;

        $webp_supported = function_exists( 'imagewebp' );
        $push_channel_options = $this->get_qilinghook_channel_options();
        $notify_method_choices = array(
            'none'  => __( '关闭', 'qiling' ),
            'email' => __( '仅邮件', 'qiling' ),
            'push'  => __( '仅飞书/钉钉推送', 'qiling' ),
            'both'  => __( '邮件 + 飞书/钉钉推送', 'qiling' ),
        );
        $comment_notify_scope_choices = array(
            'pending' => __( '仅待审核评论', 'qiling' ),
            'all'     => __( '所有新评论', 'qiling' ),
        );
        $qilinghook_active = function_exists( 'qilinghook_send' );
        $qilinghook_note = $qilinghook_active
            ? __( '已检测到启灵推送插件。可为不同业务场景分别选择推送通道。', 'qiling' )
            : __( '未检测到启灵推送插件。请先启用“启灵推送”插件后再配置飞书/钉钉推送。', 'qiling' );
        $ai_connection_choices = $this->get_ai_connection_choices();
        $international_cookie_category_choices = array(
            'necessary'   => __( '必要', 'qiling' ),
            'statistics'  => __( '统计', 'qiling' ),
            'marketing'   => __( '营销', 'qiling' ),
            'advertising' => __( '广告', 'qiling' ),
            'custom'      => __( '自定义', 'qiling' ),
        );
        $international_cookie_position_choices = array(
            'bottom_center' => __( '底部居中', 'qiling' ),
            'bottom_left'   => __( '底部左侧', 'qiling' ),
            'bottom_right'  => __( '底部右侧', 'qiling' ),
        );
        $international_cookie_region_choices = array(
            'cn'           => __( '中国', 'qiling' ),
            'eu'           => __( '欧盟', 'qiling' ),
            'uk'           => __( '英国', 'qiling' ),
            'us'           => __( '美国', 'qiling' ),
            'cross_border' => __( '跨境站', 'qiling' ),
        );
        $current_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        if ( ! is_string( $current_host ) ) {
            $current_host = '';
        }
        $design_preset_choices = class_exists( '\Developer_Starter\Core\Design_Tokens' )
            ? \Developer_Starter\Core\Design_Tokens::get_preset_choices()
            : array(
                'default'    => __( '通用官网', 'qiling' ),
                'enterprise' => __( '企业服务', 'qiling' ),
                'technology' => __( '科技产品', 'qiling' ),
                'medical'    => __( '医疗健康', 'qiling' ),
                'education'  => __( '教育培训', 'qiling' ),
                'restaurant' => __( '餐饮门店', 'qiling' ),
                'magazine'   => __( '杂志媒体', 'qiling' ),
                'minimal'    => __( '极简内容', 'qiling' ),
            );
        $blog_visual_preset_choices = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
            ? \Developer_Starter\Core\Blog_Visual_Manager::get_preset_choices()
            : array(
                'default'   => __( '默认企业内容', 'qiling' ),
                'developer' => __( '技术开发者', 'qiling' ),
                'minimal'   => __( '极简', 'qiling' ),
                'artist'    => __( '艺术家', 'qiling' ),
            );
        $content_model_choices = class_exists( '\Developer_Starter\Core\Content_Model_Center' )
            ? \Developer_Starter\Core\Content_Model_Center::get_model_choices()
            : array(
                'service' => __( '服务', 'qiling' ),
                'product' => __( '产品', 'qiling' ),
                'case'    => __( '案例', 'qiling' ),
                'post'    => __( '文章', 'qiling' ),
            );
        unset( $content_model_choices['branch'] );
        $default_content_models = class_exists( '\Developer_Starter\Core\Content_Model_Center' )
            ? \Developer_Starter\Core\Content_Model_Center::get_default_enabled_model_ids()
            : array( 'service', 'product', 'case', 'post' );
        $schema_industry_choices = class_exists( '\Developer_Starter\SEO\Industry_Schema_Engine' )
            ? \Developer_Starter\SEO\Industry_Schema_Engine::get_industry_choices()
            : array(
                'auto'          => __( '自动识别', 'qiling' ),
                'corporate'     => __( '企业官网 / 通用品牌', 'qiling' ),
                'local_service' => __( '本地服务 / 门店', 'qiling' ),
                'ecommerce'     => __( '电商零售 / 产品目录', 'qiling' ),
                'publisher'     => __( '博客 / 杂志 / 媒体', 'qiling' ),
            );
        $header_variant_row_class       = 'ds-settings-header-variant-row';
        $header_variant_section_class   = 'ds-settings-header-variant-row ds-settings-header-variant-section';
        $header_secondary_row_class     = 'ds-settings-header-secondary-row';
        $header_secondary_section_class = 'ds-settings-header-secondary-row ds-settings-header-secondary-section';
        $footer_generated_row_class     = 'ds-footer-generated-row';
        $footer_generated_section_class = 'ds-footer-generated-row ds-footer-generated-section';
        $footer_secondary_row_class     = trim( $footer_generated_row_class . ' ds-footer-secondary-row' );
        $footer_secondary_section_class = trim( $footer_generated_section_class . ' ds-footer-secondary-row ds-footer-secondary-section' );
        $search_mode_choices = function_exists( 'developer_starter_get_search_mode_choices' )
            ? developer_starter_get_search_mode_choices()
            : array( 'all' => __( '综合搜索', 'qiling' ), 'post' => __( '文章搜索', 'qiling' ) );

        return array(
            // ========== 全局样式选项卡 ==========
            'design' => array(
                array( 'type' => 'section', 'title' => __( '全局样式', 'qiling' ) ),
                array( 'id' => 'design_enable_global_tokens', 'type' => 'checkbox', 'label' => __( '启用全局样式', 'qiling' ), 'default' => '1', 'search_terms' => array( '全局设计', '全局装修', '全局样式' ) ),
                array( 'id' => 'design_preset', 'type' => 'select', 'label' => __( '风格预设', 'qiling' ), 'choices' => $design_preset_choices, 'default' => 'default', 'search_terms' => array( '主题色方案', '配色方案', '主题风格', '样式方案' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_design_quick_start_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_design_tokens_preview_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_design_preset_manager_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_design_preset_scope_manager_field' ) ),

                array( 'type' => 'section', 'title' => __( '品牌颜色', 'qiling' ) ),
                array( 'id' => 'design_primary_color', 'type' => 'text', 'label' => __( '品牌主色', 'qiling' ), 'attrs' => array( 'placeholder' => '#2563eb' ), 'search_terms' => array( '主题色', '主题主色', '品牌色' ) ),
                array( 'id' => 'design_primary_hover_color', 'type' => 'text', 'label' => __( '主色悬停', 'qiling' ), 'attrs' => array( 'placeholder' => '#1d4ed8' ) ),
                array( 'id' => 'design_secondary_color', 'type' => 'text', 'label' => __( '辅助色', 'qiling' ), 'attrs' => array( 'placeholder' => '#0f766e' ) ),
                array( 'id' => 'design_accent_color', 'type' => 'text', 'label' => __( '点缀色', 'qiling' ), 'attrs' => array( 'placeholder' => '#059669' ) ),
                array( 'id' => 'design_text_color', 'type' => 'text', 'label' => __( '正文颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#1f2937' ) ),
                array( 'id' => 'design_text_muted_color', 'type' => 'text', 'label' => __( '弱化文字颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#64748b' ) ),
                array( 'id' => 'design_heading_color', 'type' => 'text', 'label' => __( '标题颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#111827' ) ),
                array( 'id' => 'design_background_color', 'type' => 'text', 'label' => __( '页面背景色', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_surface_color', 'type' => 'text', 'label' => __( '卡片背景色', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_surface_alt_color', 'type' => 'text', 'label' => __( '浅色区块背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#f8fafc' ) ),
                array( 'id' => 'design_border_color', 'type' => 'text', 'label' => __( '边框颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#e5e7eb' ) ),

                array( 'type' => 'section', 'title' => __( '语义颜色', 'qiling' ), 'desc' => __( '用于状态提示、通知、标签、遮罩和强调反馈。更细的中性色与暗色映射可在上方自定义预设里统一维护。', 'qiling' ) ),
                array( 'id' => 'design_success_color', 'type' => 'text', 'label' => __( '成功色', 'qiling' ), 'attrs' => array( 'placeholder' => '#16a34a' ) ),
                array( 'id' => 'design_info_color', 'type' => 'text', 'label' => __( '信息色', 'qiling' ), 'attrs' => array( 'placeholder' => '#0ea5e9' ) ),
                array( 'id' => 'design_warning_color', 'type' => 'text', 'label' => __( '警告色', 'qiling' ), 'attrs' => array( 'placeholder' => '#f59e0b' ) ),
                array( 'id' => 'design_error_color', 'type' => 'text', 'label' => __( '错误色', 'qiling' ), 'attrs' => array( 'placeholder' => '#dc2626' ) ),
                array( 'id' => 'design_overlay_color', 'type' => 'text', 'label' => __( '遮罩色', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(15, 23, 42, 0.68)' ) ),

                array( 'type' => 'section', 'title' => __( '字体与布局', 'qiling' ) ),
                array( 'id' => 'design_font_family', 'type' => 'text', 'label' => __( '全局字体族', 'qiling' ), 'attrs' => array( 'placeholder' => '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' ), 'search_terms' => array( '字体', '全站字体', '网站字体' ) ),
                array( 'type' => 'section', 'title' => __( '自定义字体', 'qiling' ), 'desc' => __( '上传站点自有字体文件后，主题会自动生成 @font-face，并把全站基础字体切换到该字体。推荐优先使用 WOFF2。', 'qiling' ) ),
                array( 'id' => 'custom_font_enable', 'type' => 'checkbox', 'label' => __( '启用自定义字体', 'qiling' ), 'desc' => __( '开启后，至少填写一个有效字体文件地址才会生效。', 'qiling' ), 'default' => '', 'search_terms' => array( '字体上传', '自定义字体', '第三方字体', 'font face' ) ),
                array( 'id' => 'custom_font_family', 'type' => 'text', 'label' => __( '字体名称', 'qiling' ), 'desc' => __( '用于 CSS font-family 的名称，例如 My Brand Font；留空时使用 Qiling Custom Font。', 'qiling' ), 'attrs' => array( 'placeholder' => 'My Brand Font' ), 'search_terms' => array( '字体名称', 'font family' ) ),
                array( 'id' => 'custom_font_woff2_url', 'type' => 'file', 'label' => __( 'WOFF2 字体文件', 'qiling' ), 'desc' => __( '推荐格式，体积更小，现代浏览器优先加载。支持 /wp-content/... 或 HTTPS 地址。', 'qiling' ), 'attrs' => array( 'placeholder' => '/wp-content/uploads/fonts/my-font.woff2' ), 'button_label' => __( '选择字体文件', 'qiling' ), 'search_terms' => array( 'woff2', '字体文件', '上传字体' ) ),
                array( 'id' => 'custom_font_woff_url', 'type' => 'file', 'label' => __( 'WOFF 字体文件', 'qiling' ), 'desc' => __( '作为 WOFF2 的兼容后备格式，可留空。', 'qiling' ), 'attrs' => array( 'placeholder' => '/wp-content/uploads/fonts/my-font.woff' ), 'button_label' => __( '选择字体文件', 'qiling' ) ),
                array( 'id' => 'custom_font_ttf_url', 'type' => 'file', 'label' => __( 'TTF 字体文件', 'qiling' ), 'desc' => __( '可作为旧字体文件兼容格式；若已有 WOFF2/WOFF，通常不必填写。', 'qiling' ), 'attrs' => array( 'placeholder' => '/wp-content/uploads/fonts/my-font.ttf' ), 'button_label' => __( '选择字体文件', 'qiling' ) ),
                array( 'id' => 'custom_font_otf_url', 'type' => 'file', 'label' => __( 'OTF 字体文件', 'qiling' ), 'desc' => __( '可作为旧字体文件兼容格式；若已有 WOFF2/WOFF，通常不必填写。', 'qiling' ), 'attrs' => array( 'placeholder' => '/wp-content/uploads/fonts/my-font.otf' ), 'button_label' => __( '选择字体文件', 'qiling' ) ),
                array( 'id' => 'custom_font_weight', 'type' => 'select', 'label' => __( '字体字重', 'qiling' ), 'choices' => array( '400' => '400', '500' => '500', '600' => '600', '700' => '700', '100 900' => __( '可变字体 100-900', 'qiling' ) ), 'default' => '400', 'desc' => __( '应与上传的字体文件实际字重匹配。', 'qiling' ) ),
                array( 'id' => 'custom_font_style', 'type' => 'select', 'label' => __( '字体样式', 'qiling' ), 'choices' => array( 'normal' => __( '正常', 'qiling' ), 'italic' => __( '斜体', 'qiling' ) ), 'default' => 'normal' ),
                array( 'id' => 'custom_font_display', 'type' => 'select', 'label' => __( '加载策略', 'qiling' ), 'choices' => array( 'swap' => 'swap', 'fallback' => 'fallback', 'optional' => 'optional', 'block' => 'block', 'auto' => 'auto' ), 'default' => 'swap', 'desc' => __( '推荐 swap：先显示备用字体，字体加载完成后替换。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_design_typography_system_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_design_layout_system_field' ) ),

                array( 'type' => 'section', 'title' => __( '组件风格', 'qiling' ) ),
                array( 'id' => 'design_card_radius', 'type' => 'text', 'label' => __( '卡片圆角', 'qiling' ), 'attrs' => array( 'placeholder' => '8px', 'class' => 'small-text' ), 'search_terms' => array( '卡片弧度', '卡片圆润' ) ),
                array( 'id' => 'design_button_radius', 'type' => 'text', 'label' => __( '按钮圆角', 'qiling' ), 'attrs' => array( 'placeholder' => '8px', 'class' => 'small-text' ), 'search_terms' => array( '按钮弧度', '按钮圆润' ) ),
                array( 'id' => 'design_input_radius', 'type' => 'text', 'label' => __( '输入框圆角', 'qiling' ), 'attrs' => array( 'placeholder' => '8px', 'class' => 'small-text' ), 'search_terms' => array( '表单圆角', '输入框弧度' ) ),
                array( 'id' => 'design_animation_speed', 'type' => 'text', 'label' => __( '动效速度', 'qiling' ), 'attrs' => array( 'placeholder' => '0.25s', 'class' => 'small-text' ), 'search_terms' => array( '动画速度', '动效快慢', '过渡速度' ) ),
                array( 'id' => 'design_shadow_sm', 'type' => 'text', 'label' => __( '小阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 1px 2px rgba(0, 0, 0, 0.05)' ) ),
                array( 'id' => 'design_shadow_md', 'type' => 'text', 'label' => __( '中阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 4px 6px -1px rgba(0, 0, 0, 0.1)' ) ),
                array( 'id' => 'design_shadow_lg', 'type' => 'text', 'label' => __( '大阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 10px 15px -3px rgba(0, 0, 0, 0.1)' ) ),

                array( 'type' => 'section', 'title' => __( '全局组件样式中心', 'qiling' ), 'desc' => __( '统一控制按钮、卡片、表单、页头导航、下拉层、徽标、分页、面包屑、弹窗、侧栏、页脚和 Woo 卡片等组件的站点级样式。单页装修里的“页面设置 -> 页面组件样式”只覆盖当前页面，模块自身单独设置的优先级更高。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '按钮组件', 'qiling' ) ),
                array( 'id' => 'design_component_button_bg', 'type' => 'text', 'label' => __( '按钮背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)' ), 'desc' => __( '支持纯色、rgba() 或 linear-gradient(...)。', 'qiling' ), 'search_terms' => array( '按钮颜色', '按钮背景色', '主按钮颜色' ) ),
                array( 'id' => 'design_component_button_text', 'type' => 'text', 'label' => __( '按钮文字颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ), 'search_terms' => array( '按钮文字', '按钮字体颜色' ) ),
                array( 'id' => 'design_component_button_border', 'type' => 'text', 'label' => __( '按钮边框颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#2563eb' ) ),
                array( 'id' => 'design_component_button_hover_bg', 'type' => 'text', 'label' => __( '按钮悬停背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#1d4ed8' ) ),
                array( 'id' => 'design_component_button_hover_text', 'type' => 'text', 'label' => __( '按钮悬停文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_button_shadow', 'type' => 'text', 'label' => __( '按钮阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 10px 25px rgba(37, 99, 235, 0.18)' ) ),
                array( 'id' => 'design_component_button_padding', 'type' => 'text', 'label' => __( '按钮内边距', 'qiling' ), 'attrs' => array( 'placeholder' => '12px 24px', 'class' => 'small-text' ), 'search_terms' => array( '按钮大小', '按钮高度', '按钮尺寸' ) ),

                array( 'type' => 'section', 'title' => __( '按钮组件（次要）', 'qiling' ) ),
                array( 'id' => 'design_component_button_secondary_bg', 'type' => 'text', 'label' => __( '次要按钮背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(241, 245, 249, 0.95)' ) ),
                array( 'id' => 'design_component_button_secondary_text', 'type' => 'text', 'label' => __( '次要按钮文字颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#1e293b' ) ),
                array( 'id' => 'design_component_button_secondary_border', 'type' => 'text', 'label' => __( '次要按钮边框颜色', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(203, 213, 225, 0.8)' ) ),
                array( 'id' => 'design_component_button_secondary_hover_bg', 'type' => 'text', 'label' => __( '次要按钮悬停背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#f8fafc' ) ),

                array( 'type' => 'section', 'title' => __( '标题组件', 'qiling' ) ),
                array( 'id' => 'design_component_heading_weight', 'type' => 'select', 'label' => __( '标题字重', 'qiling' ), 'default' => '700', 'choices' => array(
                    '500' => __( '500', 'qiling' ),
                    '600' => __( '600', 'qiling' ),
                    '700' => __( '700（默认）', 'qiling' ),
                    '800' => __( '800', 'qiling' ),
                    '900' => __( '900', 'qiling' ),
                ) ),
                array( 'id' => 'design_component_heading_letter_spacing', 'type' => 'text', 'label' => __( '标题字间距', 'qiling' ), 'attrs' => array( 'placeholder' => '0em', 'class' => 'small-text' ) ),

                array( 'type' => 'section', 'title' => __( '卡片组件', 'qiling' ) ),
                array( 'id' => 'design_component_card_bg', 'type' => 'text', 'label' => __( '卡片背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ), 'search_terms' => array( '卡片颜色', '卡片背景色' ) ),
                array( 'id' => 'design_component_card_border', 'type' => 'text', 'label' => __( '卡片边框颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#e5e7eb' ) ),
                array( 'id' => 'design_component_card_shadow', 'type' => 'text', 'label' => __( '卡片阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 18px 40px rgba(15, 23, 42, 0.08)' ) ),

                array( 'type' => 'section', 'title' => __( '表单组件', 'qiling' ) ),
                array( 'id' => 'design_component_form_input_bg', 'type' => 'text', 'label' => __( '输入框背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ), 'search_terms' => array( '表单背景', '输入框颜色' ) ),
                array( 'id' => 'design_component_form_input_text', 'type' => 'text', 'label' => __( '输入框文字颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#1f2937' ), 'search_terms' => array( '表单文字', '输入框字体颜色' ) ),
                array( 'id' => 'design_component_form_input_border', 'type' => 'text', 'label' => __( '输入框边框颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#d1d5db' ), 'search_terms' => array( '表单边框', '输入框描边' ) ),
                array( 'id' => 'design_component_form_focus_border', 'type' => 'text', 'label' => __( '输入框聚焦边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#2563eb' ), 'search_terms' => array( '聚焦颜色', '输入框高亮', '焦点边框' ) ),

                array( 'type' => 'section', 'title' => __( '模块标题组件', 'qiling' ) ),
                array( 'id' => 'design_component_module_title_color', 'type' => 'text', 'label' => __( '模块标题颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#111827' ) ),
                array( 'id' => 'design_component_module_title_size', 'type' => 'text', 'label' => __( '模块标题字号', 'qiling' ), 'attrs' => array( 'placeholder' => '2rem', 'class' => 'small-text' ) ),
                array( 'id' => 'design_component_module_title_align', 'type' => 'select', 'label' => __( '模块标题对齐', 'qiling' ), 'default' => 'center', 'choices' => array(
                    'left' => __( '左对齐', 'qiling' ),
                    'center' => __( '居中', 'qiling' ),
                    'right' => __( '右对齐', 'qiling' ),
                ) ),

                array( 'type' => 'section', 'title' => __( '文章卡片组件', 'qiling' ) ),
                array( 'id' => 'design_component_post_card_bg', 'type' => 'text', 'label' => __( '文章卡片背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_post_card_border', 'type' => 'text', 'label' => __( '文章卡片边框颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#e5e7eb' ) ),
                array( 'id' => 'design_component_post_card_shadow', 'type' => 'text', 'label' => __( '文章卡片阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 10px 25px rgba(15, 23, 42, 0.08)' ) ),
                array( 'id' => 'design_component_post_card_title_color', 'type' => 'text', 'label' => __( '文章卡片标题颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#111827' ) ),
                array( 'id' => 'design_component_post_card_meta_color', 'type' => 'text', 'label' => __( '文章卡片元信息颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#64748b' ) ),

                array( 'type' => 'section', 'title' => __( '认证组件（登录/注册）', 'qiling' ) ),
                array( 'id' => 'design_component_auth_action_bg', 'type' => 'text', 'label' => __( '认证主按钮背景', 'qiling' ), 'desc' => __( '控制全局所有登录/注册大按钮的背景颜色。', 'qiling' ), 'attrs' => array( 'placeholder' => '支持纯色或 linear-gradient' ), 'search_terms' => array( '登录颜色', '注册按钮颜色', '验证按钮' ) ),
                array( 'id' => 'design_component_auth_action_text', 'type' => 'text', 'label' => __( '认证主按钮文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_auth_code_bg', 'type' => 'text', 'label' => __( '验证码按钮背景', 'qiling' ), 'attrs' => array( 'placeholder' => '如 rgba(...) 或十六进制' ) ),
                array( 'id' => 'design_component_auth_code_text', 'type' => 'text', 'label' => __( '验证码按钮文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_auth_slider_track_bg', 'type' => 'text', 'label' => __( '滑动轨道背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#f1f5f9' ) ),
                array( 'id' => 'design_component_auth_slider_handle_bg', 'type' => 'text', 'label' => __( '滑块按钮背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_auth_slider_progress_bg', 'type' => 'text', 'label' => __( '滑动进度背景', 'qiling' ), 'attrs' => array( 'placeholder' => '支持纯色或渐变' ) ),
                array( 'id' => 'design_component_auth_verified_color', 'type' => 'text', 'label' => __( '验证成功颜色', 'qiling' ), 'desc' => __( '滑块验证成功后的绿色提示状态。', 'qiling' ), 'attrs' => array( 'placeholder' => '#10b981' ) ),

                array( 'type' => 'section', 'title' => __( '页头、Logo、桌面导航与电话按钮组件', 'qiling' ) ),
                array( 'id' => 'design_component_header_bg', 'type' => 'text', 'label' => __( '页头背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ), 'search_terms' => array( '页头颜色', '头部背景', '顶部背景', '导航栏背景' ) ),
                array( 'id' => 'design_component_header_border', 'type' => 'text', 'label' => __( '页头边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e5e7eb' ) ),
                array( 'id' => 'design_component_header_shadow', 'type' => 'text', 'label' => __( '页头阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 2px 20px rgba(15, 23, 42, 0.08)' ) ),
                array( 'id' => 'design_component_header_text', 'type' => 'text', 'label' => __( '页头功能入口颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ), 'search_terms' => array( '页头文字', '头部文字', '顶部文字' ) ),
                array( 'id' => 'design_component_header_scrolled_text', 'type' => 'text', 'label' => __( '滚动后页头功能入口颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ) ),
                array( 'id' => 'design_component_header_logo_transparent_fill', 'type' => 'text', 'label' => __( '透明头部文字 Logo 背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #ffffff 0%, rgba(255,255,255,0.72) 100%)' ), 'desc' => __( '仅作用于站点名文字 Logo；上传图片 / SVG Logo 时不会改变图片本身。', 'qiling' ) ),
                array( 'id' => 'design_component_header_logo_scrolled_fill', 'type' => 'text', 'label' => __( '滚动/内页文字 Logo 背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)' ), 'desc' => __( '仅作用于站点名文字 Logo；用于首页滚动后与内页状态。', 'qiling' ) ),
                array( 'id' => 'design_component_header_phone_bg', 'type' => 'text', 'label' => __( '页头电话按钮背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)' ), 'search_terms' => array( '电话按钮颜色', '页头电话颜色', '顶部电话按钮' ) ),
                array( 'id' => 'design_component_header_phone_text', 'type' => 'text', 'label' => __( '页头电话按钮文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ), 'search_terms' => array( '电话按钮文字', '页头电话文字' ) ),
                array( 'id' => 'design_component_header_phone_transparent_bg', 'type' => 'text', 'label' => __( '透明头部电话按钮背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(255,255,255,0.2)' ) ),
                array( 'id' => 'design_component_header_phone_transparent_text', 'type' => 'text', 'label' => __( '透明头部电话按钮文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_nav_link', 'type' => 'text', 'label' => __( '桌面导航文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ), 'search_terms' => array( '导航颜色', '菜单颜色', '导航文字', '菜单文字' ) ),
                array( 'id' => 'design_component_nav_scrolled_link', 'type' => 'text', 'label' => __( '滚动后桌面导航文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ) ),
                array( 'id' => 'design_component_nav_hover_bg', 'type' => 'text', 'label' => __( '桌面导航激活/悬停背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)' ), 'desc' => __( '用于顶部主菜单 hover、当前项和高亮入口。', 'qiling' ), 'search_terms' => array( '导航悬停背景', '菜单悬停背景', '当前菜单背景' ) ),
                array( 'id' => 'design_component_nav_hover_text', 'type' => 'text', 'label' => __( '桌面导航激活/悬停文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_nav_scrolled_hover_text', 'type' => 'text', 'label' => __( '滚动后桌面导航悬停文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),

                array( 'type' => 'section', 'title' => __( '移动导航与底部导航组件', 'qiling' ) ),
                array( 'id' => 'design_component_mobile_nav_bg', 'type' => 'text', 'label' => __( '移动导航背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_mobile_nav_border', 'type' => 'text', 'label' => __( '移动导航边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e2e8f0' ) ),
                array( 'id' => 'design_component_mobile_nav_link', 'type' => 'text', 'label' => __( '移动导航文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#1e293b' ) ),
                array( 'id' => 'design_component_mobile_nav_hover_bg', 'type' => 'text', 'label' => __( '移动导航悬停背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#f8fafc' ) ),
                array( 'id' => 'design_component_mobile_nav_hover_text', 'type' => 'text', 'label' => __( '移动导航悬停文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#2563eb' ) ),

                array( 'type' => 'section', 'title' => __( '下拉层与徽标组件', 'qiling' ) ),
                array( 'id' => 'design_component_dropdown_bg', 'type' => 'text', 'label' => __( '下拉层背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_dropdown_border', 'type' => 'text', 'label' => __( '下拉层边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e5e7eb' ) ),
                array( 'id' => 'design_component_dropdown_shadow', 'type' => 'text', 'label' => __( '下拉层阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 10px 40px rgba(15, 23, 42, 0.12)' ) ),
                array( 'id' => 'design_component_dropdown_link', 'type' => 'text', 'label' => __( '下拉层文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ) ),
                array( 'id' => 'design_component_dropdown_hover_bg', 'type' => 'text', 'label' => __( '下拉层悬停背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#f8fafc' ) ),
                array( 'id' => 'design_component_dropdown_hover_text', 'type' => 'text', 'label' => __( '下拉层悬停文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#2563eb' ) ),
                array( 'id' => 'design_component_badge_bg', 'type' => 'text', 'label' => __( '徽标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)' ) ),
                array( 'id' => 'design_component_badge_text', 'type' => 'text', 'label' => __( '徽标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),

                array( 'type' => 'section', 'title' => __( '标签页与折叠面板组件', 'qiling' ) ),
                array( 'id' => 'design_component_tabs_border', 'type' => 'text', 'label' => __( '标签页边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e2e8f0' ), 'search_terms' => array( '标签边框' ) ),
                array( 'id' => 'design_component_tabs_text', 'type' => 'text', 'label' => __( '标签页文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#64748b' ), 'search_terms' => array( '标签文字' ) ),
                array( 'id' => 'design_component_tabs_active_bg', 'type' => 'text', 'label' => __( '标签页激活背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#eff6ff' ), 'search_terms' => array( '标签选中背景', '标签激活颜色' ) ),
                array( 'id' => 'design_component_tabs_active_text', 'type' => 'text', 'label' => __( '标签页激活文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#2563eb' ), 'search_terms' => array( '标签选中文字', '标签激活文字颜色' ) ),
                array( 'id' => 'design_component_tabs_active_border', 'type' => 'text', 'label' => __( '标签页激活边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#2563eb' ) ),
                array( 'id' => 'design_component_accordion_bg', 'type' => 'text', 'label' => __( '折叠面板背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_accordion_border', 'type' => 'text', 'label' => __( '折叠面板边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e5e7eb' ) ),
                array( 'id' => 'design_component_accordion_title', 'type' => 'text', 'label' => __( '折叠面板标题', 'qiling' ), 'attrs' => array( 'placeholder' => '#111827' ) ),

                array( 'type' => 'section', 'title' => __( '分页与面包屑组件', 'qiling' ) ),
                array( 'id' => 'design_component_pagination_bg', 'type' => 'text', 'label' => __( '分页背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_pagination_border', 'type' => 'text', 'label' => __( '分页边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e2e8f0' ) ),
                array( 'id' => 'design_component_pagination_text', 'type' => 'text', 'label' => __( '分页文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ) ),
                array( 'id' => 'design_component_pagination_active_bg', 'type' => 'text', 'label' => __( '分页激活背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)' ) ),
                array( 'id' => 'design_component_pagination_active_text', 'type' => 'text', 'label' => __( '分页激活文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_breadcrumb_bg', 'type' => 'text', 'label' => __( '面包屑背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_breadcrumb_text', 'type' => 'text', 'label' => __( '面包屑文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#64748b' ) ),
                array( 'id' => 'design_component_breadcrumb_link', 'type' => 'text', 'label' => __( '面包屑链接', 'qiling' ), 'attrs' => array( 'placeholder' => '#1f2937' ) ),

                array( 'type' => 'section', 'title' => __( '提示框与弹窗组件', 'qiling' ) ),
                array( 'id' => 'design_component_alert_bg', 'type' => 'text', 'label' => __( '提示框背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#f8fafc' ) ),
                array( 'id' => 'design_component_alert_border', 'type' => 'text', 'label' => __( '提示框边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e2e8f0' ) ),
                array( 'id' => 'design_component_alert_text', 'type' => 'text', 'label' => __( '提示框文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ) ),
                array( 'id' => 'design_component_modal_bg', 'type' => 'text', 'label' => __( '弹窗背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_modal_border', 'type' => 'text', 'label' => __( '弹窗边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e2e8f0' ) ),
                array( 'id' => 'design_component_modal_shadow', 'type' => 'text', 'label' => __( '弹窗阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 25px 80px rgba(15, 23, 42, 0.25)' ) ),
                array( 'id' => 'design_component_modal_title', 'type' => 'text', 'label' => __( '弹窗标题', 'qiling' ), 'attrs' => array( 'placeholder' => '#1e293b' ) ),

                array( 'type' => 'section', 'title' => __( '侧栏与页脚组件', 'qiling' ) ),
                array( 'id' => 'design_component_sidebar_bg', 'type' => 'text', 'label' => __( '侧栏背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_sidebar_border', 'type' => 'text', 'label' => __( '侧栏边框', 'qiling' ), 'attrs' => array( 'placeholder' => '#e5e7eb' ) ),
                array( 'id' => 'design_component_sidebar_shadow', 'type' => 'text', 'label' => __( '侧栏阴影', 'qiling' ), 'attrs' => array( 'placeholder' => '0 4px 10px rgba(15, 23, 42, 0.05)' ) ),
                array( 'id' => 'design_component_sidebar_title', 'type' => 'text', 'label' => __( '侧栏标题', 'qiling' ), 'attrs' => array( 'placeholder' => '#111827' ) ),
                array( 'id' => 'design_component_footer_bg', 'type' => 'text', 'label' => __( '页脚主背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#1e293b' ), 'search_terms' => array( '页脚颜色', '底部背景', '页脚背景' ) ),
                array( 'id' => 'design_component_footer_text', 'type' => 'text', 'label' => __( '页脚文字', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(255,255,255,0.78)' ), 'search_terms' => array( '页脚字体颜色', '底部文字', '版权文字颜色' ) ),
                array( 'id' => 'design_component_footer_heading', 'type' => 'text', 'label' => __( '页脚标题', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(255,255,255,0.78)' ), 'search_terms' => array( '页脚标题颜色', '底部标题' ) ),
                array( 'id' => 'design_component_footer_heading_size', 'type' => 'text', 'label' => __( '页脚标题字号', 'qiling' ), 'attrs' => array( 'placeholder' => '18px' ), 'search_terms' => array( '页脚标题大小', '页脚字号' ) ),
                array( 'id' => 'design_component_footer_link', 'type' => 'text', 'label' => __( '页脚链接', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(255,255,255,0.72)' ), 'search_terms' => array( '页脚链接颜色', '底部链接' ) ),
                array( 'id' => 'design_component_footer_link_hover', 'type' => 'text', 'label' => __( '页脚链接悬停', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'design_component_footer_bottom_bg', 'type' => 'text', 'label' => __( '页脚底栏背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(2,6,23,0.82)' ), 'search_terms' => array( '版权栏背景', '页脚底部背景', '底栏背景' ) ),


                array( 'type' => 'section', 'title' => __( '暗色模式', 'qiling' ) ),
                array( 'id' => 'design_dark_bg', 'type' => 'text', 'label' => __( '暗色页面背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#111827' ) ),
                array( 'id' => 'design_dark_surface', 'type' => 'text', 'label' => __( '暗色卡片背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#1f2937' ) ),
                array( 'id' => 'design_dark_text', 'type' => 'text', 'label' => __( '暗色正文颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#f3f4f6' ) ),
                array( 'id' => 'design_dark_text_muted', 'type' => 'text', 'label' => __( '暗色弱化文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#cbd5e1' ) ),
                array( 'id' => 'design_dark_border', 'type' => 'text', 'label' => __( '暗色边框颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#334155' ) ),
            ),

            // ========== 模型中心选项卡 ==========
            'models' => array(
                array( 'type' => 'section', 'title' => __( '通用内容模型中心', 'qiling' ), 'desc' => __( '统一管理服务、产品、案例、团队、资源、课程和活动等内容，供页面装修、AI 装修及内容接口使用。', 'qiling' ) ),
                array( 'id' => 'content_model_center_enable', 'type' => 'checkbox', 'label' => __( '启用模型中心', 'qiling' ), 'desc' => __( '开启后主题会按下方选择注册通用内容类型和字段。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'local_business_features_enable', 'type' => 'checkbox', 'label' => __( '启用本地门店/网点功能', 'qiling' ), 'desc' => __( '开启后显示门店/分支内容类型、门店机构模块和本地商家结构。', 'qiling' ) ),
                array(
                    'id'      => 'content_model_enabled_models',
                    'type'    => 'checkbox_group',
                    'label'   => __( '启用的内容模型', 'qiling' ),
                    'choices' => $content_model_choices,
                    'default' => $default_content_models,
                    'desc'    => __( '按站点行业选择需要的模型。页面、文章、FAQ、软件应用等会复用已有内容类型；服务、产品、案例等会由模型中心注册。', 'qiling' ),
                    'args'    => array(
                        'wrapper_class' => 'ds-content-model-checkboxes',
                        'wrapper_style' => 'display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px 14px;max-width:820px;',
                        'label_style'   => 'display:block;margin:0;',
                    ),
                ),
                array( 'id' => 'content_model_archive_base', 'type' => 'text', 'label' => __( '模型归档基础路径', 'qiling' ), 'default' => 'content', 'desc' => __( '例如 content/services、content/products。修改后主题会在后台自动刷新固定链接规则。', 'qiling' ), 'attrs' => array( 'class' => 'regular-text', 'placeholder' => 'content' ) ),
                array( 'id' => 'content_model_archive_enable', 'type' => 'checkbox', 'label' => __( '启用模型前台归档', 'qiling' ), 'desc' => __( '开启后注册归档页和详情页 URL；关闭后仅作为后台结构化内容库使用。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'content_model_rest_enable', 'type' => 'checkbox', 'label' => __( '开放 REST 结构', 'qiling' ), 'desc' => __( '开启后模型内容、分类和字段可被区块、外部系统、AI 工具和站点包读取。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'content_model_meta_box_enable', 'type' => 'checkbox', 'label' => __( '启用模型字段面板', 'qiling' ), 'desc' => __( '在模型内容编辑页显示统一字段面板，用于维护价格、链接、评分、门店地址等结构化字段。', 'qiling' ), 'default' => '1' ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_content_model_center_overview_field' ) ),
            ),

            // ========== 顶部选项卡 ==========
            'header' => array(
                array( 'type' => 'section', 'title' => __( '网站 Logo', 'qiling' ) ),
                array( 'id' => 'site_logo', 'type' => 'image', 'label' => __( '网站 Logo', 'qiling' ), 'desc' => __( '推荐尺寸: 高度 180-240px（宽度自适应），实际显示高度约60px。为避免发虚，建议上传2x清晰图。', 'qiling' ) ),
                array( 'id' => 'mobile_logo', 'type' => 'image', 'label' => __( '移动端 Logo', 'qiling' ), 'desc' => __( '推荐尺寸: 高度 180-240px（宽度自适应），实际显示高度约60px。为避免发虚，建议上传2x清晰图。', 'qiling' ) ),
                array( 'id' => 'site_logo_svg', 'type' => 'textarea', 'label' => __( '网站 Logo (SVG 代码)', 'qiling' ), 'desc' => __( '可直接粘贴完整 <svg> 代码，保存时会进行安全过滤。填写后优先于图片 Logo。', 'qiling' ), 'attrs' => array( 'rows' => 5 ) ),
                array( 'id' => 'mobile_logo_svg', 'type' => 'textarea', 'label' => __( '移动端 Logo (SVG 代码)', 'qiling' ), 'desc' => __( '仅移动端优先生效，留空则使用网站 Logo 的 SVG/图片。', 'qiling' ), 'attrs' => array( 'rows' => 5 ) ),

                array( 'type' => 'section', 'title' => __( '菜单位置', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_header_menu_locations_overview_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_header_settings_governance_field' ) ),
                array( 'type' => 'note', 'content' => __( '普通站点常看这里的 Logo、菜单位置、固定 / 透明头部和功能开关；颜色、字号、按钮样式统一去“全局组件样式中心”。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '页头结构与行为', 'qiling' ), 'desc' => __( '这里主要控制固定、透明、布局和交互方式；视觉样式继续跟随全局设计中心。', 'qiling' ) ),
                array( 'id' => 'header_sticky', 'type' => 'checkbox', 'label' => __( '固定头部', 'qiling' ), 'default' => '1', 'search_terms' => array( '吸顶头部', '固定导航', '吸顶导航' ) ),
                array( 'id' => 'header_menu_layout', 'type' => 'select', 'label' => __( '桌面端菜单布局', 'qiling' ), 'default' => 'default', 'search_terms' => array( '菜单位置', '导航布局', '页头布局' ), 'choices' => array(
                    'default'     => __( 'Logo 左侧，菜单居左（默认）', 'qiling' ),
                    'menu_center' => __( 'Logo 左侧，菜单居中', 'qiling' ),
                    'logo_center' => __( 'Logo 居中，菜单左侧，功能入口右侧', 'qiling' ),
                ) ),
                array( 'id' => 'header_transparent_home', 'type' => 'checkbox', 'label' => __( '首页顶部透明', 'qiling' ), 'search_terms' => array( '透明头部', '透明导航', '首页透明' ) ),
                array( 'id' => 'minimalist_menu_enable', 'type' => 'checkbox', 'label' => __( '桌面导航极简交互', 'qiling' ), 'desc' => __( '启用后桌面导航改为下划线强调样式；颜色依然跟随全局组件样式中心。', 'qiling' ), 'search_terms' => array( '极简导航', '导航下划线', '简洁菜单' ) ),

                array( 'type' => 'section', 'title' => __( 'Logo 扩展内容', 'qiling' ), 'row_class' => $header_secondary_section_class ),
                array( 'id' => 'pc_logo_slogan_text', 'type' => 'text', 'label' => __( 'PC端Logo右侧标语', 'qiling' ), 'attrs' => array( 'maxlength' => 40 ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'pc_logo_slogan_show_divider', 'type' => 'checkbox', 'label' => __( '显示Logo与标语分割线', 'qiling' ), 'default' => '1', 'row_class' => $header_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '头部功能入口', 'qiling' ) ),
                array( 'id' => 'hide_search_button', 'type' => 'checkbox', 'label' => __( '隐藏搜索入口', 'qiling' ) ),
                array( 'id' => 'header_search_mode', 'type' => 'select', 'label' => __( '搜索显示模式', 'qiling' ), 'default' => 'icon', 'choices' => array(
                    'icon' => __( '搜索图标（默认）', 'qiling' ),
                    'form' => __( '顶部搜索框', 'qiling' ),
                ) ),
                 array( 'id' => 'hide_phone_header', 'type' => 'checkbox', 'label' => __( '隐藏电话号码', 'qiling' ) ),
                 array( 'id' => 'header_login_enable', 'type' => 'checkbox', 'label' => __( '显示登录按钮', 'qiling' ) ),
                 array( 'id' => 'header_login_text', 'type' => 'text', 'label' => __( '登录按钮文字', 'qiling' ) ),
                 array( 'id' => 'header_account_enable', 'type' => 'checkbox', 'label' => __( '显示个人中心', 'qiling' ), 'default' => '1', 'desc' => __( '开启后，用户登录后在顶部显示个人中心头像入口；与“显示登录按钮”相互独立。', 'qiling' ) ),
                 array( 'id' => 'darkmode_enable', 'type' => 'checkbox', 'label' => __( '启用暗黑模式', 'qiling' ) ),
                array( 'id' => 'darkmode_auto_enable', 'type' => 'checkbox', 'label' => __( '启用自动暗黑模式', 'qiling' ), 'desc' => __( '开启后可跟随系统或按日出/日落时间自动切换；用户手动点击暗黑按钮后，以用户偏好优先。', 'qiling' ), 'search_terms' => array( '自动暗黑', '自动深色', '跟随系统', '日落切换' ) ),
                array( 'id' => 'darkmode_auto_mode', 'type' => 'select', 'label' => __( '自动切换方式', 'qiling' ), 'default' => 'system_schedule', 'choices' => array(
                    'system_schedule' => __( '跟随系统，无法检测时按时间', 'qiling' ),
                    'system'          => __( '仅跟随系统偏好', 'qiling' ),
                    'schedule'        => __( '按日出/日落时间', 'qiling' ),
                ), 'desc' => __( '系统偏好使用浏览器 prefers-color-scheme；时间模式使用访客设备本地时间，适合国际化访问场景。', 'qiling' ) ),
                array( 'id' => 'darkmode_sunrise_time', 'type' => 'text', 'input_type' => 'time', 'label' => __( '日出时间（亮色开始）', 'qiling' ), 'default' => '06:00', 'attrs' => array( 'class' => 'small-text', 'step' => '60' ), 'desc' => __( '到达此时间后切回亮色模式。按访客设备本地时间判断。', 'qiling' ) ),
                array( 'id' => 'darkmode_sunset_time', 'type' => 'text', 'input_type' => 'time', 'label' => __( '日落时间（暗色开始）', 'qiling' ), 'default' => '18:00', 'attrs' => array( 'class' => 'small-text', 'step' => '60' ), 'desc' => __( '到达此时间后切换为暗色模式。按访客设备本地时间判断。', 'qiling' ) ),
                array( 'id' => 'darkmode_transition_enable', 'type' => 'checkbox', 'label' => __( '启用暗色切换动画', 'qiling' ), 'default' => '1', 'desc' => __( '自动或手动切换时为背景、文字、边框和图片滤镜添加柔和过渡。', 'qiling' ) ),
                array( 'id' => 'darkmode_image_dim_enable', 'type' => 'checkbox', 'label' => __( '暗色模式图片调暗', 'qiling' ), 'default' => '1', 'desc' => __( '暗色状态下轻微降低正文和模块图片亮度；Logo、头像、二维码等关键识别图片会保持原样。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '移动端顶部入口', 'qiling' ), 'row_class' => $header_secondary_section_class ),
                array( 'id' => 'mobile_hide_search', 'type' => 'checkbox', 'label' => __( '隐藏搜索图标', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'mobile_hide_phone', 'type' => 'checkbox', 'label' => __( '隐藏电话图标', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'mobile_hide_login', 'type' => 'checkbox', 'label' => __( '隐藏用户/登录图标', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'mobile_hide_translate', 'type' => 'checkbox', 'label' => __( '隐藏语言切换', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'mobile_hide_darkmode', 'type' => 'checkbox', 'label' => __( '隐藏暗黑模式', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'mobile_hide_vip', 'type' => 'checkbox', 'label' => __( '隐藏VIP图标 (积分插件)', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'mobile_hide_cart', 'type' => 'checkbox', 'label' => __( '隐藏购物车图标 (积分插件)', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'mobile_hide_history', 'type' => 'checkbox', 'label' => __( '隐藏播放记录 (播放器插件)', 'qiling' ), 'row_class' => $header_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '移动端底部菜单', 'qiling' ), 'row_class' => $header_secondary_section_class ),
                array( 'id' => 'mobile_bottom_label_mode', 'type' => 'select', 'label' => __( '底部菜单显示方式', 'qiling' ), 'default' => 'icon_text', 'row_class' => $header_secondary_row_class, 'choices' => array(
                    'icon_text' => __( '图标 + 文字（默认）', 'qiling' ),
                    'icon_only' => __( '仅图标（保留无障碍文字）', 'qiling' ),
                    'text_only' => __( '仅文字', 'qiling' ),
                ) ),
                array( 'id' => 'mobile_bottom_recommended_items', 'type' => 'select', 'label' => __( '底部菜单建议项数', 'qiling' ), 'default' => '5', 'row_class' => $header_secondary_row_class, 'choices' => array(
                    '3' => __( '建议最多 3 项', 'qiling' ),
                    '4' => __( '建议最多 4 项', 'qiling' ),
                    '5' => __( '建议最多 5 项', 'qiling' ),
                ) ),
                array( 'type' => 'section', 'title' => __( '头部变体设置', 'qiling' ), 'desc' => __( '用于指定少量头部变体。没有特殊需求时，保留 default 或留空即可。', 'qiling' ), 'row_class' => $header_variant_section_class ),
                array( 'id' => 'header_style', 'type' => 'text', 'label' => __( '头部变体标识', 'qiling' ), 'desc' => __( '没有特殊头部变体需求时，保留 default 或留空即可。', 'qiling' ), 'attrs' => array( 'placeholder' => 'default' ), 'row_class' => $header_variant_row_class, 'search_terms' => array( '头部变体', '头部样式', '页头样式' ) ),

                array( 'type' => 'section', 'title' => __( '左侧导航菜单', 'qiling' ), 'row_class' => $header_secondary_section_class ),
                array( 'id' => 'left_nav_display_mode', 'type' => 'select', 'label' => __( '显示范围', 'qiling' ), 'row_class' => $header_secondary_row_class, 'choices' => array(
                    'all'         => __( '全站显示', 'qiling' ),
                    'except_home' => __( '首页不显示', 'qiling' ),
                ), 'default' => 'all' ),
                array( 'id' => 'left_nav_excluded_page_ids', 'type' => 'textarea', 'label' => __( '排除页面 ID', 'qiling' ), 'desc' => __( '填写后，这些页面不会加载左侧导航。多个 ID 可用英文逗号、空格或换行分隔。', 'qiling' ), 'attrs' => array( 'rows' => 2, 'placeholder' => '12, 34, 56' ), 'row_class' => $header_secondary_row_class, 'search_terms' => array( '左侧菜单排除', '不显示左侧导航', '页面ID' ) ),
                array( 'id' => 'left_nav_toggle_default_open', 'type' => 'checkbox', 'label' => __( '默认展开左侧导航', 'qiling' ), 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'left_nav_toggle_open_icon', 'type' => 'text', 'label' => __( '展开图标', 'qiling' ), 'default' => 'icon-menu', 'row_class' => $header_secondary_row_class ),
                array( 'id' => 'left_nav_toggle_close_icon', 'type' => 'text', 'label' => __( '收起图标', 'qiling' ), 'default' => 'icon-close', 'row_class' => $header_secondary_row_class ),
            ),

            // ========== 页脚选项卡 ==========
            'footer' => array(
                array( 'type' => 'section', 'title' => __( '页脚装修', 'qiling' ), 'desc' => __( '这里主要决定哪一个装修页面接管页脚，以及接管范围；页脚颜色、波浪和三段背景在下方“页脚三段式视觉装修”里调整。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_footer_settings_governance_field' ) ),
                array( 'id' => 'footer_builder_enable', 'type' => 'checkbox', 'label' => __( '启用页脚装修', 'qiling' ), 'default' => '', 'search_terms' => array( '自定义页脚', '页脚模板', '页脚装修' ) ),
                array( 'id' => 'footer_builder_page_id', 'type' => 'select', 'label' => __( '装修页面', 'qiling' ), 'choices' => $footer_builder_page_options, 'default' => '', 'search_terms' => array( '页脚页面', '页脚模板页面' ) ),
                array( 'id' => 'footer_builder_position', 'type' => 'select', 'label' => __( '替换区域', 'qiling' ), 'default' => 'replace_widgets', 'search_terms' => array( '替换整个页脚', '页脚接管范围', '页脚区域' ), 'choices' => array(
                    'replace_widgets' => __( '替换上方主区域', 'qiling' ),
                    'replace_friend_links' => __( '替换中间友情链接区域', 'qiling' ),
                    'replace_bottom'  => __( '替换底部版权备案区域', 'qiling' ),
                    'replace_all'     => __( '替换整个页脚', 'qiling' ),
                ) ),
                array( 'type' => 'section', 'title' => __( '页脚独立区域装修', 'qiling' ), 'desc' => __( '三个区域可同时选择不同的装修页面。设置后优先于上方单页面替换配置；留空的区域继续使用主题默认内容或旧配置。', 'qiling' ) ),
                array( 'id' => 'footer_builder_main_page_id', 'type' => 'select', 'label' => __( '关于我们/联系区域装修页面', 'qiling' ), 'choices' => $footer_builder_page_options, 'default' => '', 'search_terms' => array( '页脚主区域装修', '关于我们装修', '联系方式装修' ) ),
                array( 'id' => 'footer_builder_friend_page_id', 'type' => 'select', 'label' => __( '友情链接区域装修页面', 'qiling' ), 'choices' => $footer_builder_page_options, 'default' => '', 'desc' => __( '该区域仍仅在首页显示。', 'qiling' ), 'search_terms' => array( '友情链接装修', '首页友链装修' ) ),
                array( 'id' => 'footer_builder_bottom_page_id', 'type' => 'select', 'label' => __( '版权/ICP 备案区域装修页面', 'qiling' ), 'choices' => $footer_builder_page_options, 'default' => '', 'desc' => __( '选择后完全替换默认版权、ICP 备案和公安备案输出。', 'qiling' ), 'search_terms' => array( 'ICP装修', '备案装修', '版权栏装修' ) ),
                array( 'type' => 'section', 'title' => __( '页脚三段式视觉装修', 'qiling' ), 'desc' => __( '分别控制上方信息区、中间友情链接区和底部版权备案区的背景、文字、波浪衔接与动画范围。颜色字段支持 #fff、rgba(...)、var(...) 和 linear-gradient(...)。', 'qiling' ) ),
                array( 'id' => 'footer_visual_main_bg', 'type' => 'text', 'label' => __( '信息主板块背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-footer-bg)' ), 'search_terms' => array( '页脚上方背景', '底部主背景', '页脚主区域颜色' ) ),
                array( 'id' => 'footer_visual_main_text', 'type' => 'text', 'label' => __( '信息主板块文字', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-footer-text)' ), 'search_terms' => array( '页脚文字颜色', '底部主文字' ) ),
                array( 'id' => 'footer_visual_main_heading', 'type' => 'text', 'label' => __( '信息主板块标题', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-footer-heading)' ), 'search_terms' => array( '页脚标题颜色', '底部标题' ) ),
                array( 'id' => 'footer_visual_main_link', 'type' => 'text', 'label' => __( '信息主板块链接', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-footer-link)' ), 'search_terms' => array( '页脚链接颜色', '底部链接' ) ),
                array( 'id' => 'footer_visual_main_link_hover', 'type' => 'text', 'label' => __( '信息主板块链接悬停', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-footer-link-hover)' ) ),
                array( 'id' => 'footer_visual_main_padding_top', 'type' => 'text', 'label' => __( '信息主板块上间距', 'qiling' ), 'default' => '', 'attrs' => array( 'placeholder' => '60px' ) ),
                array( 'id' => 'footer_visual_main_padding_bottom', 'type' => 'text', 'label' => __( '信息主板块下间距', 'qiling' ), 'default' => '', 'attrs' => array( 'placeholder' => '60px' ) ),
                array( 'id' => 'footer_visual_friend_merge_bg', 'type' => 'checkbox', 'label' => __( '友情链接背景跟随信息区', 'qiling' ), 'default' => '', 'search_terms' => array( '友链合并背景', '页脚中间背景' ) ),
                array( 'id' => 'footer_visual_friend_bg', 'type' => 'text', 'label' => __( '友情链接板块背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-footer-bottom-bg)' ), 'search_terms' => array( '友情链接背景', '页脚中间颜色' ) ),
                array( 'id' => 'footer_visual_friend_text', 'type' => 'text', 'label' => __( '友情链接板块文字', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-footer-main-text)' ) ),
                array( 'id' => 'footer_visual_friend_link', 'type' => 'text', 'label' => __( '友情链接板块链接', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-footer-main-link)' ) ),
                array( 'id' => 'footer_visual_friend_link_hover', 'type' => 'text', 'label' => __( '友情链接板块链接悬停', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-footer-main-link-hover)' ) ),
                array( 'id' => 'footer_visual_friend_padding_y', 'type' => 'text', 'label' => __( '友情链接上下间距', 'qiling' ), 'default' => '', 'attrs' => array( 'placeholder' => '20px' ) ),
                array( 'id' => 'footer_visual_bottom_bg', 'type' => 'text', 'label' => __( '备案版权板块背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-footer-bottom-bg)' ), 'search_terms' => array( '备案背景', '版权背景', '页脚底栏背景' ) ),
                array( 'id' => 'footer_visual_bottom_text', 'type' => 'text', 'label' => __( '备案版权板块文字', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-footer-main-text)' ) ),
                array( 'id' => 'footer_visual_bottom_link', 'type' => 'text', 'label' => __( '备案版权板块链接', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-footer-main-link)' ) ),
                array( 'id' => 'footer_visual_bottom_link_hover', 'type' => 'text', 'label' => __( '备案版权链接悬停', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-footer-main-link-hover)' ) ),
                array( 'id' => 'footer_visual_bottom_border', 'type' => 'text', 'label' => __( '备案版权分割线', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(255,255,255,0.1)' ) ),
                array( 'id' => 'footer_visual_bottom_padding_y', 'type' => 'text', 'label' => __( '备案版权上下间距', 'qiling' ), 'default' => '', 'attrs' => array( 'placeholder' => '20px' ) ),
                array( 'id' => 'footer_visual_wave_enable', 'type' => 'checkbox', 'label' => __( '启用顶部波浪衔接', 'qiling' ), 'default' => '', 'search_terms' => array( '页脚波浪', '底部波浪', '波浪衔接' ) ),
                array( 'id' => 'footer_visual_wave_style', 'type' => 'select', 'label' => __( '波浪样式', 'qiling' ), 'default' => 'double', 'choices' => array(
                    'single' => __( '单层波浪', 'qiling' ),
                    'double' => __( '双层波浪', 'qiling' ),
                    'soft'   => __( '柔和大曲线', 'qiling' ),
                    'slope'  => __( '斜切过渡', 'qiling' ),
                ) ),
                array( 'id' => 'footer_visual_wave_palette', 'type' => 'select', 'label' => __( '波浪配色方案', 'qiling' ), 'default' => 'auto', 'choices' => $footer_wave_palette_options, 'desc' => __( '作为波浪背景、主副波浪和柔化过渡的默认搭配；下方手动颜色留空时生效。', 'qiling' ), 'search_terms' => array( '波浪颜色预设', '页脚波浪配色', '底部波浪颜色' ) ),
                array( 'id' => 'footer_visual_wave_backdrop', 'type' => 'text', 'label' => __( '波浪上方背景', 'qiling' ), 'attrs' => array( 'placeholder' => '自动跟随页面背景/配色方案' ), 'search_terms' => array( '波浪背景', '页脚波浪上方', '底部过渡背景' ) ),
                array( 'id' => 'footer_visual_wave_transition_from', 'type' => 'text', 'label' => __( '波浪柔化起始色', 'qiling' ), 'attrs' => array( 'placeholder' => '自动跟随上一板块/页面背景' ), 'search_terms' => array( '波浪过渡色', '底部柔化', '页脚衔接颜色' ) ),
                array( 'id' => 'footer_visual_wave_transition_height', 'type' => 'text', 'label' => __( '波浪柔化高度', 'qiling' ), 'default' => '', 'attrs' => array( 'placeholder' => '32px' ), 'search_terms' => array( '波浪过渡高度', '底部柔化高度', '页脚衔接高度' ) ),
                array( 'id' => 'footer_visual_wave_height', 'type' => 'text', 'label' => __( '波浪高度', 'qiling' ), 'default' => '', 'attrs' => array( 'placeholder' => '120px' ) ),
                array( 'id' => 'footer_visual_wave_color', 'type' => 'text', 'label' => __( '主波浪颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '自动跟随信息主板块背景' ) ),
                array( 'id' => 'footer_visual_wave_layer_color', 'type' => 'text', 'label' => __( '副波浪颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '自动跟随友情链接背景' ) ),
                array( 'id' => 'footer_visual_wave_layer_opacity', 'type' => 'number', 'label' => __( '副波浪透明度', 'qiling' ), 'default' => '0.38', 'attrs' => array( 'min' => '0', 'max' => '1', 'step' => 'any' ), 'desc' => __( '请输入 0 到 1 之间的任意小数，例如 0.35、0.37 或 0.4；超出范围的值会被限制到有效范围。', 'qiling' ) ),
                array( 'type' => 'section', 'title' => __( '默认栏目显示', 'qiling' ), 'desc' => __( '仅控制主题默认上方主区域的四个栏目；使用页脚装修替换上方主区域或整个页脚时，以装修页面内容为准。', 'qiling' ), 'row_class' => $footer_generated_section_class ),
                array( 'id' => 'footer_about_enable', 'type' => 'checkbox', 'label' => __( '显示关于我们板块', 'qiling' ), 'default' => '1', 'row_class' => $footer_generated_row_class, 'search_terms' => array( '页脚栏目', '默认页脚', '关闭关于我们' ) ),
                array( 'id' => 'footer_links_enable', 'type' => 'checkbox', 'label' => __( '显示快速链接板块', 'qiling' ), 'default' => '1', 'row_class' => $footer_generated_row_class, 'search_terms' => array( '页脚栏目', '默认页脚', '关闭快速链接' ) ),
                array( 'id' => 'footer_contact_enable', 'type' => 'checkbox', 'label' => __( '显示联系方式板块', 'qiling' ), 'default' => '1', 'row_class' => $footer_generated_row_class, 'search_terms' => array( '页脚栏目', '默认页脚', '关闭联系方式' ) ),
                array( 'id' => 'footer_follow_enable', 'type' => 'checkbox', 'label' => __( '显示关注我们板块', 'qiling' ), 'default' => '1', 'row_class' => $footer_generated_row_class, 'search_terms' => array( '页脚栏目', '默认页脚', '关闭关注我们' ) ),

                array( 'type' => 'section', 'title' => __( '网站信息', 'qiling' ), 'row_class' => $footer_generated_section_class ),
                array( 'id' => 'company_name', 'type' => 'text', 'label' => __( '企业名称', 'qiling' ), 'row_class' => $footer_generated_row_class, 'search_terms' => array( '公司名称' ) ),
                array( 'id' => 'company_phone', 'type' => 'text', 'label' => __( '联系电话', 'qiling' ), 'row_class' => $footer_generated_row_class ),
                array( 'id' => 'company_qq', 'type' => 'text', 'label' => __( 'QQ 联系方式', 'qiling' ), 'desc' => __( '填写 QQ 号或 QQ 联系链接', 'qiling' ), 'row_class' => $footer_generated_row_class ),
                array( 'id' => 'company_wechat_qrcode', 'type' => 'image', 'label' => __( '微信二维码', 'qiling' ), 'desc' => __( '上传用于联系的微信二维码图片', 'qiling' ), 'row_class' => $footer_generated_row_class ),
                array( 'id' => 'company_email', 'type' => 'text', 'label' => __( '联系邮箱', 'qiling' ), 'row_class' => $footer_generated_row_class ),
                array( 'id' => 'company_address', 'type' => 'textarea', 'label' => __( '企业地址', 'qiling' ), 'row_class' => $footer_generated_row_class, 'search_terms' => array( '联系地址', '公司地址' ) ),
                array( 'id' => 'company_working_hours', 'type' => 'text', 'label' => __( '工作时间', 'qiling' ), 'desc' => __( '如：周一至周五 9:00-18:00', 'qiling' ), 'row_class' => $footer_generated_row_class, 'search_terms' => array( '营业时间', '上班时间' ) ),
                array( 'id' => 'company_brief', 'type' => 'textarea', 'label' => __( '公司简介', 'qiling' ), 'desc' => __( '显示在页脚', 'qiling' ), 'row_class' => $footer_generated_row_class ),

                array( 'type' => 'section', 'title' => __( '备案信息', 'qiling' ), 'row_class' => $footer_secondary_section_class ),
                array( 'id' => 'icp_number', 'type' => 'text', 'label' => __( 'ICP 备案号', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'police_number', 'type' => 'text', 'label' => __( '公安备案号', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'police_icon', 'type' => 'image', 'label' => __( '公安备案图标', 'qiling' ), 'row_class' => $footer_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '社交媒体', 'qiling' ), 'row_class' => $footer_secondary_section_class ),
                array( 'id' => 'wechat_qrcode', 'type' => 'image', 'label' => __( '微信公众号二维码', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'wechat_qr_text', 'type' => 'text', 'label' => __( '微信二维码文字', 'qiling' ), 'desc' => __( '如：扫码关注公众号', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'douyin_qrcode', 'type' => 'image', 'label' => __( '抖音二维码', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'douyin_qr_text', 'type' => 'text', 'label' => __( '抖音二维码文字', 'qiling' ), 'desc' => __( '如：扫码关注抖音', 'qiling' ), 'row_class' => $footer_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '隐私政策提示（GDPR）', 'qiling' ), 'desc' => __( '在网站底部显示数据收集声明，适用于欧盟等地区的隐私合规要求', 'qiling' ), 'row_class' => $footer_secondary_section_class ),
                array( 'id' => 'privacy_banner_enable', 'type' => 'checkbox', 'label' => __( '启用隐私提示条', 'qiling' ), 'desc' => __( '在网站底部显示隐私政策/Cookie提示条', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'privacy_banner_text', 'type' => 'textarea', 'label' => __( '提示内容', 'qiling' ), 'desc' => __( '如：本网站使用Cookie和类似技术来提升您的体验。继续使用本网站即表示您同意我们的隐私政策。', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'privacy_banner_link_text', 'type' => 'text', 'label' => __( '链接文字', 'qiling' ), 'desc' => __( '如：了解更多', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'privacy_banner_link_url', 'type' => 'text', 'label' => __( '隐私政策链接', 'qiling' ), 'desc' => __( '填写隐私政策页面URL，留空则不显示链接', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'privacy_banner_btn_text', 'type' => 'text', 'label' => __( '接受按钮文字', 'qiling' ), 'desc' => __( '如：全部接受 或 我知道了', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'privacy_banner_decline_text', 'type' => 'text', 'label' => __( '拒绝按钮文字', 'qiling' ), 'desc' => __( '如：仅必要Cookie 或 拒绝非必要，留空则不显示此按钮', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'privacy_banner_bg', 'type' => 'color', 'label' => __( '提示条背景色', 'qiling' ), 'default' => '#1e293b', 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'privacy_banner_text_color', 'type' => 'color', 'label' => __( '提示条文字颜色', 'qiling' ), 'default' => '#ffffff', 'row_class' => $footer_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '页脚文字设置', 'qiling' ), 'row_class' => $footer_secondary_section_class ),
                array( 'id' => 'footer_about_title', 'type' => 'text', 'label' => __( '关于我们标题', 'qiling' ), 'desc' => __( '默认: 关于我们', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'footer_links_title', 'type' => 'text', 'label' => __( '快速链接标题', 'qiling' ), 'desc' => __( '默认: 快速链接', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'footer_contact_title', 'type' => 'text', 'label' => __( '联系方式标题', 'qiling' ), 'desc' => __( '默认: 联系方式', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'footer_follow_title', 'type' => 'text', 'label' => __( '关注我们标题', 'qiling' ), 'desc' => __( '默认: 关注我们', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'footer_copyright', 'type' => 'textarea', 'label' => __( '版权信息（支持HTML）', 'qiling' ), 'row_class' => $footer_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '快速链接（内部产品链接）', 'qiling' ), 'row_class' => $footer_secondary_section_class ),
                array( 'id' => 'footer_quick_links', 'type' => 'repeater', 'label' => __( '链接列表', 'qiling' ), 'fields' => array(
                    array( 'id' => 'text', 'label' => __( '链接文字', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'url', 'label' => __( '链接地址', 'qiling' ), 'type' => 'text' ),
                ), 'row_class' => $footer_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '友情链接（仅首页显示）', 'qiling' ), 'row_class' => $footer_secondary_section_class ),
                array( 'id' => 'friend_links_enable', 'type' => 'checkbox', 'label' => __( '启用友情链接', 'qiling' ), 'desc' => __( '勾选后在首页底部显示友情链接', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'friend_links', 'type' => 'repeater', 'label' => __( '友情链接列表', 'qiling' ), 'fields' => array(
                    array( 'id' => 'text', 'label' => __( '链接文字', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'url', 'label' => __( '链接地址', 'qiling' ), 'type' => 'text' ),
                ), 'row_class' => $footer_secondary_row_class ),

                array( 'type' => 'section', 'title' => __( '页脚动画特效', 'qiling' ), 'row_class' => $footer_secondary_section_class ),
                array( 'id' => 'footer_effect_enable', 'type' => 'checkbox', 'label' => __( '启用背景特效', 'qiling' ), 'desc' => __( '在页脚显示动态背景效果', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'footer_effect_scope', 'type' => 'select', 'label' => __( '特效作用范围', 'qiling' ), 'default' => 'main', 'choices' => array(
                    'main'       => __( '只作用于信息主板块', 'qiling' ),
                    'all'        => __( '覆盖整个页脚', 'qiling' ),
                    'decorative' => __( '作为整段背景装饰层', 'qiling' ),
                ), 'row_class' => $footer_secondary_row_class ),
                array( 'id' => 'footer_effect_type', 'type' => 'select', 'label' => __( '特效类型', 'qiling' ), 'choices' => array(
                    'particles' => __( '粒子飘动', 'qiling' ),
                    'lines'     => __( '线条网络', 'qiling' ),
                    'waves'     => __( '波浪效果', 'qiling' ),
                    'stars'     => __( '星空闪烁', 'qiling' ),
                    'bubbles'   => __( '气泡上升', 'qiling' ),
                    'snow'      => __( '雪花飘落', 'qiling' ),
                    'aurora'    => __( '极光效果', 'qiling' ),
                    'fireflies' => __( '萤火虫', 'qiling' ),
                ), 'desc' => __( '选择动画效果类型', 'qiling' ), 'row_class' => $footer_secondary_row_class ),
            ),

            // ========== SMTP选项卡 ==========
            'smtp' => array(
                array( 'type' => 'section', 'title' => __( 'SMTP 邮件设置', 'qiling' ), 'desc' => __( '配置SMTP后可实现邮件发送功能', 'qiling' ) ),
                array( 'id' => 'smtp_host', 'type' => 'text', 'label' => __( 'SMTP 服务器', 'qiling' ), 'desc' => __( '如: smtp.qq.com, smtp.163.com', 'qiling' ) ),
                array( 'id' => 'smtp_port', 'type' => 'number', 'label' => __( 'SMTP 端口', 'qiling' ), 'desc' => __( '常用: 465(SSL), 587(TLS), 25', 'qiling' ) ),
                array( 'id' => 'smtp_secure', 'type' => 'select', 'label' => __( '加密协议', 'qiling' ), 'choices' => array( 'ssl' => 'SSL', 'tls' => 'TLS', '' => __( '无加密', 'qiling' ) ) ),
                array( 'id' => 'smtp_username', 'type' => 'text', 'label' => __( '邮箱账号', 'qiling' ), 'desc' => __( '发件人邮箱地址', 'qiling' ) ),
                array( 'id' => 'smtp_password', 'type' => 'password', 'label' => __( '邮箱密码/授权码', 'qiling' ), 'desc' => __( 'QQ邮箱需使用授权码，密码将加密存储', 'qiling' ) ),
                array( 'id' => 'smtp_sender_name', 'type' => 'text', 'label' => __( '发送者名称', 'qiling' ), 'desc' => __( '邮件显示的发件人名称', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_smtp_test_field' ) ),

                array( 'type' => 'section', 'title' => __( '统一通知方式（邮件 / 飞书 / 钉钉）', 'qiling' ), 'desc' => __( '按场景选择通知方式。推送能力由“启灵推送”插件提供。', 'qiling' ) ),
                array( 'type' => 'note', 'content' => $qilinghook_note, 'style' => $qilinghook_active ? 'color:#065f46;' : 'color:#b45309;' ),

                array( 'id' => 'notify_message_method', 'type' => 'select', 'label' => __( '留言通知方式', 'qiling' ), 'choices' => $notify_method_choices, 'default' => 'email', 'desc' => __( '用于主题内置留言系统通知。联系表单通知请在启灵表单插件内配置。', 'qiling' ) ),
                array( 'id' => 'notify_message_push_channel', 'type' => 'checkbox_group', 'label' => __( '留言推送通道', 'qiling' ), 'choices' => $push_channel_options, 'desc' => __( '用于主题内置留言系统。联系表单请在 qiling-forms 插件“通知设置”中配置。', 'qiling' ) ),


                array( 'id' => 'notify_careers_method', 'type' => 'select', 'label' => __( '求职申请通知方式', 'qiling' ), 'choices' => $notify_method_choices, 'default' => 'none' ),
                array( 'id' => 'notify_careers_push_channel', 'type' => 'checkbox_group', 'label' => __( '求职申请推送通道（可多选）', 'qiling' ), 'choices' => $push_channel_options, 'desc' => __( '当求职申请通知方式包含“推送”时生效。支持同时推送到多个飞书/钉钉机器人。', 'qiling' ) ),

                array( 'id' => 'notify_submit_post_method', 'type' => 'select', 'label' => __( '投稿通知方式', 'qiling' ), 'choices' => $notify_method_choices, 'default' => 'none', 'desc' => __( '用户通过前台投稿或修改投稿后，通知管理员处理待审核文章。', 'qiling' ) ),
                array( 'id' => 'notify_submit_post_push_channel', 'type' => 'checkbox_group', 'label' => __( '投稿推送通道（可多选）', 'qiling' ), 'choices' => $push_channel_options, 'desc' => __( '当投稿通知方式包含“推送”时生效。', 'qiling' ) ),

                array( 'id' => 'notify_comment_method', 'type' => 'select', 'label' => __( '评论通知方式', 'qiling' ), 'choices' => $notify_method_choices, 'default' => 'none', 'desc' => __( '用于 WordPress 原生评论通知。若已启用 WordPress 自带评论邮件，可保持关闭或仅通知待审核评论。', 'qiling' ) ),
                array( 'id' => 'notify_comment_scope', 'type' => 'select', 'label' => __( '评论通知范围', 'qiling' ), 'choices' => $comment_notify_scope_choices, 'default' => 'pending', 'desc' => __( '“仅待审核评论”适合大多数站点，避免已发布评论重复打扰管理员。', 'qiling' ) ),
                array( 'id' => 'notify_comment_push_channel', 'type' => 'checkbox_group', 'label' => __( '评论推送通道（可多选）', 'qiling' ), 'choices' => $push_channel_options, 'desc' => __( '当评论通知方式包含“推送”时生效。', 'qiling' ) ),

                array( 'id' => 'notify_account_deletion_method', 'type' => 'select', 'label' => __( '账号注销申请通知方式', 'qiling' ), 'choices' => $notify_method_choices, 'default' => 'none', 'desc' => __( '用户提交账号注销申请后，通知管理员到后台审核处理。', 'qiling' ) ),
                array( 'id' => 'notify_account_deletion_push_channel', 'type' => 'checkbox_group', 'label' => __( '账号注销申请推送通道（可多选）', 'qiling' ), 'choices' => $push_channel_options, 'desc' => __( '当账号注销申请通知方式包含“推送”时生效。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '站内通知管理', 'qiling' ), 'desc' => __( '控制站内通知是否写入通知中心（默认开启）。', 'qiling' ) ),
                array( 'id' => 'site_notify_enable', 'type' => 'checkbox', 'label' => __( '启用站内通知', 'qiling' ), 'desc' => __( '关闭后将不会创建任何站内通知记录', 'qiling' ), 'default' => '1' ),

                array( 'type' => 'section', 'title' => __( '积分商城通知（站内）', 'qiling' ), 'desc' => __( '主题侧仅提供总开关，具体通知场景请在“启灵积分商城”插件内配置。', 'qiling' ) ),
                array( 'id' => 'site_notify_qilingshop', 'type' => 'checkbox', 'label' => __( '启用积分商城站内通知', 'qiling' ), 'desc' => __( '关闭后将屏蔽启灵积分商城插件产生的全部站内通知。', 'qiling' ), 'default' => '1' ),
            ),

            // ========== 装修生成选项卡 ==========
            'ai' => array(
                array( 'type' => 'section', 'title' => __( 'AI 装修基础设置', 'qiling' ), 'desc' => __( '用于后台页面编辑器与前台可视化装修器的 AI 页面草稿生成。当前按 OpenAI 兼容 Chat Completions 接口对接，阿里百炼兼容地址可直接使用。', 'qiling' ) ),
                array( 'id' => 'ai_builder_enable', 'type' => 'checkbox', 'label' => __( '启用 AI 装修', 'qiling' ), 'desc' => __( '开启后，后台页面模块编辑器与前台装修器会显示 AI 装修入口。', 'qiling' ) ),
                array( 'id' => 'ai_default_connection', 'type' => 'select', 'label' => __( '默认连接', 'qiling' ), 'choices' => $ai_connection_choices, 'desc' => __( '装修面板默认使用的连接。若下方连接列表刚新增，请先保存一次后这里才能出现选项。', 'qiling' ) ),
                array( 'id' => 'ai_default_temperature', 'type' => 'text', 'label' => __( '默认 Temperature', 'qiling' ), 'default' => '0.4', 'desc' => __( '建议 0.2 - 0.7。数值越低越稳定，越高越发散。', 'qiling' ), 'attrs' => array( 'placeholder' => '0.4', 'class' => 'small-text' ) ),
                array( 'id' => 'ai_default_max_output_tokens', 'type' => 'number', 'label' => __( '默认最大输出 Tokens', 'qiling' ), 'default' => '4000', 'desc' => __( '用于限制单次模型返回长度。', 'qiling' ), 'attrs' => array( 'min' => 256, 'max' => 16000 ) ),
                array( 'id' => 'ai_default_request_timeout', 'type' => 'number', 'label' => __( '请求超时（秒）', 'qiling' ), 'default' => '120', 'desc' => __( '服务端转发请求到 AI 接口时的超时。连通性测试会直接使用该值，正式生成页面时会在此基础上自动放宽到更长时间。', 'qiling' ), 'attrs' => array( 'min' => 10, 'max' => 300 ) ),
                array( 'id' => 'ai_default_max_modules', 'type' => 'number', 'label' => __( '候选模块上限', 'qiling' ), 'default' => '8', 'desc' => __( '建议 5-10 个。数量过多会明显增加模型乱填字段的概率。', 'qiling' ), 'attrs' => array( 'min' => 1, 'max' => 10 ) ),
                array( 'id' => 'ai_debug_log_enable', 'type' => 'checkbox', 'label' => __( '启用 AI 调试日志', 'qiling' ), 'desc' => __( '默认关闭。开启后，仅在 WordPress 已启用 debug.log（WP_DEBUG_LOG）或主题调试模式启用时，AI 装修的接口异常、解析失败等调试信息才会写入日志。', 'qiling' ) ),
                array( 'id' => 'ai_default_system_prompt', 'type' => 'textarea', 'label' => __( '默认系统提示词', 'qiling' ), 'desc' => __( '留空时使用主题内置提示词。建议只写规则，不要写具体页面内容。', 'qiling' ), 'attrs' => array( 'rows' => 10 ) ),
                array( 'id' => 'ai_endpoint_allowlist', 'type' => 'textarea', 'label' => __( 'AI Endpoint Allowlist', 'qiling' ), 'desc' => __( '每行一个允许的公网域名，支持 *.example.com。留空则允许任意公网 HTTPS 域名；localhost、内网、保留地址和链路本地地址始终禁止。', 'qiling' ), 'attrs' => array( 'rows' => 4, 'placeholder' => "dashscope.aliyuncs.com\napi.openai.com\n*.example.com" ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_ai_connections_field' ) ),
                array( 'type' => 'note', 'content' => __( '阿里百炼兼容 OpenAI Chat Completions 的常见地址示例：<code>https://dashscope.aliyuncs.com/compatible-mode/v1</code>。填写基础地址即可，主题会自动补全 <code>/chat/completions</code>。', 'qiling' ) ),
            ),

            // ========== 页面装修修订 ==========
            'builder_revisions' => array(
                array( 'type' => 'custom', 'callback' => array( $this, 'render_builder_revisions_section' ) ),
            ),

            // ========== 高级选项卡 ==========
            'advanced' => array(
                array( 'type' => 'section', 'title' => __( '功能入口', 'qiling' ), 'desc' => __( '控制后台左侧菜单里的可选业务入口，适合精简后台。', 'qiling' ) ),
                array( 'id' => 'careers_admin_menu_enable', 'type' => 'checkbox', 'label' => __( '显示招聘求职入口', 'qiling' ), 'desc' => __( '关闭后隐藏“招聘设置 / 职位管理 / 求职申请”后台入口；不会删除招聘数据。', 'qiling' ), 'default' => '1' ),

                array( 'type' => 'section', 'title' => __( 'IP 归属地', 'qiling' ) ),
                array( 'id' => 'show_user_ip_location', 'type' => 'checkbox', 'label' => __( '显示用户 IP 归属地', 'qiling' ), 'desc' => __( '勾选后在个人中心头部显示当前用户的 IP 归属地', 'qiling' ) ),
                array( 'id' => 'comment_ip_location_enable', 'type' => 'checkbox', 'label' => __( '评论区显示 IP 归属地', 'qiling' ), 'desc' => __( '勾选后在评论区展示评论用户的 IP 归属地', 'qiling' ) ),
                array( 'id' => 'community_ip_location_enable', 'type' => 'checkbox', 'label' => __( '社区显示 IP 归属地', 'qiling' ), 'desc' => __( '勾选后在启灵社区动态列表显示用户的 IP 归属地（仅省份）', 'qiling' ) ),
                array( 'id' => 'jingxialai_ip_api_key', 'type' => 'text', 'label' => __( '灵简IP API Key', 'qiling' ), 'desc' => __( '用于调用灵简IP归属地查询接口（api.jingxialai.com）。请在官网申请并填写 X-API-Key。不填则可能无法查询国内IP归属地。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_ip_cache_clear_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_ip_usermeta_reset_field' ) ),

                array( 'type' => 'section', 'title' => __( 'SEO 设置', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_home_seo_status_notice_field' ) ),
                array(
                    'id'          => 'default_title',
                    'type'        => 'text',
                    'label'       => __( '默认标题', 'qiling' ),
                    'desc'        => __( '全站首页默认标题。在最新文章模式下直接生效；若使用静态页面作为首页且页面未单独设置 SEO 标题时，作为全局回退标题生效。', 'qiling' ),
                ),
                array(
                    'id'          => 'default_description',
                    'type'        => 'textarea',
                    'label'       => __( '默认描述', 'qiling' ),
                    'desc'        => __( '全站首页默认描述。最新文章模式直接生效；静态首页在页面未单独填写 SEO 描述时自动回退使用此处内容。', 'qiling' ),
                ),
                array(
                    'id'          => 'default_keywords',
                    'type'        => 'text',
                    'label'       => __( '默认关键词', 'qiling' ),
                    'desc'        => __( '全站首页默认关键词。最新文章模式直接生效；静态首页在页面未单独填写 SEO 关键词时自动回退使用此处内容。', 'qiling' ),
                ),
                array( 'id' => 'non_home_title_use_tagline', 'type' => 'checkbox', 'label' => __( '非首页标题副标题使用站点副标题', 'qiling' ), 'desc' => __( '开启后，非首页标题将从“页面标题 - 站点标题”改为“页面标题 - 站点副标题”。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_seo_health_check_field' ) ),

                array( 'type' => 'section', 'title' => __( '行业 Schema 引擎', 'qiling' ), 'desc' => __( '统一输出 Organization、WebSite、WebPage、Breadcrumb、Article、FAQ、Product、Service 等 JSON-LD，并自动读取页面模块和内容模型字段。', 'qiling' ) ),
                array( 'id' => 'schema_engine_enable', 'type' => 'checkbox', 'label' => __( '启用行业 Schema 引擎', 'qiling' ), 'desc' => __( '开启后由主题输出一份连接完整的 @graph 结构化数据；启用 Yoast、Rank Math、AIOSEO 时主题会自动避让。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'company_name', 'type' => 'text', 'label' => __( '组织名称', 'qiling' ), 'desc' => __( '复用“页脚与公司资料”的企业名称字段，用于 Organization.name。', 'qiling' ), 'search_terms' => array( '企业名称', '公司名称' ) ),
                array( 'id' => 'site_logo', 'type' => 'image', 'label' => 'Logo', 'desc' => __( '复用网站 Logo 字段，用于 Organization.logo/image；未填写时尝试读取 WordPress 自定义 Logo。', 'qiling' ) ),
                array( 'id' => 'company_phone', 'type' => 'text', 'label' => __( '电话', 'qiling' ), 'desc' => __( '复用联系电话字段，用于 Organization.telephone。', 'qiling' ) ),
                array( 'id' => 'company_email', 'type' => 'text', 'label' => __( '邮箱', 'qiling' ), 'desc' => __( '复用联系邮箱字段，用于 Organization.email。', 'qiling' ) ),
                array( 'id' => 'company_address', 'type' => 'textarea', 'label' => __( '地址', 'qiling' ), 'desc' => __( '复用企业地址字段，用于 PostalAddress.streetAddress。', 'qiling' ), 'search_terms' => array( '联系地址', '公司地址' ) ),
                array( 'id' => 'company_working_hours', 'type' => 'text', 'label' => __( '营业时间', 'qiling' ), 'desc' => __( '复用工作时间字段，LocalBusiness、Store、Restaurant 等类型会输出 openingHours。', 'qiling' ), 'search_terms' => array( '工作时间', '上班时间' ) ),
                array( 'id' => 'schema_default_currency', 'type' => 'text', 'label' => __( '默认币种', 'qiling' ), 'default' => 'CNY', 'desc' => __( '当产品、服务或课程价格为数字时用于 Offer.priceCurrency，填写三位 ISO 货币代码，例如 CNY、USD、EUR。', 'qiling' ), 'attrs' => array( 'placeholder' => 'CNY', 'class' => 'ds-currency-code-input', 'maxlength' => 3, 'autocomplete' => 'off' ) ),
                array( 'id' => 'schema_industry_type', 'type' => 'select', 'label' => __( '行业类型', 'qiling' ), 'choices' => $schema_industry_choices, 'default' => 'auto', 'desc' => __( '选择后会影响组织实体类型；自动识别会参考页面模板、模块、内容模型和文章场景。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_schema_preview_field' ) ),

                array( 'type' => 'section', 'title' => __( 'SEO 推送（多通道）', 'qiling' ), 'desc' => __( '按地区和服务器网络情况选择通道。每个通道都可独立关闭，失败不会阻塞文章发布。', 'qiling' ) ),
                array( 'id' => 'seo_push_baidu_enable', 'type' => 'checkbox', 'label' => __( '启用百度推送', 'qiling' ), 'desc' => __( '适合中文站点。开启后，文章/页面首次发布时自动推送；也支持手动和批量历史推送。', 'qiling' ) ),
                array(
                    'id' => 'seo_push_baidu_site',
                    'type' => 'text',
                    'label' => __( '站点域名', 'qiling' ),
                    'desc' => __( '与百度站长平台验证一致，如：www.example.com。会自动过滤 http(s):// 和首尾斜杠；留空自动使用当前域名。', 'qiling' ),
                    'attrs' => array(
                        'placeholder' => $current_host,
                    ),
                ),
                array( 'id' => 'seo_push_baidu_token', 'type' => 'text', 'label' => __( '推送 Token', 'qiling' ), 'desc' => __( '百度站长平台 API 推送 token', 'qiling' ) ),
                array( 'type' => 'section', 'title' => __( 'IndexNow / Bing 推送', 'qiling' ), 'desc' => __( 'Bing 推荐使用 IndexNow 通道提交 URL；需在站点根目录放置 Key 文件。', 'qiling' ) ),
                array( 'id' => 'seo_push_indexnow_enable', 'type' => 'checkbox', 'label' => __( '启用 IndexNow / Bing', 'qiling' ), 'desc' => __( '开启后，文章/页面首次发布时自动推送；也支持手动和批量历史推送。', 'qiling' ) ),
                array( 'id' => 'seo_push_indexnow_key', 'type' => 'text', 'label' => __( 'IndexNow Key', 'qiling' ), 'desc' => __( '只需填写 Key。插件会自动使用官方接口并按 https://当前域名/KEY.txt 生成 keyLocation。', 'qiling' ) ),
                array( 'type' => 'section', 'title' => __( 'Google Indexing API', 'qiling' ), 'desc' => __( '可选通道。部分服务器或地区无法稳定连接 Google，建议先只开启手动推送，确认可用后再开启自动推送。', 'qiling' ) ),
                array( 'id' => 'seo_push_google_enable', 'type' => 'checkbox', 'label' => __( '启用 Google Indexing API', 'qiling' ), 'desc' => __( '开启后可在文章编辑页、手动 URL 和批量历史推送中使用 Google 通道。', 'qiling' ) ),
                array( 'id' => 'seo_push_google_auto_enable', 'type' => 'checkbox', 'label' => __( '发布时自动推送到 Google', 'qiling' ), 'desc' => __( '仅在 Google 通道启用且 Service Account 配置正确时生效；服务器不能访问 Google 时请保持关闭。', 'qiling' ) ),
                array( 'id' => 'seo_push_google_service_account_json', 'type' => 'textarea', 'label' => __( 'Service Account JSON', 'qiling' ), 'desc' => __( '粘贴 Google Cloud 服务账号 JSON。主题仅使用 client_email、private_key 和 token_uri 签发访问令牌，不加载第三方 SDK。', 'qiling' ), 'attrs' => array( 'rows' => 8, 'placeholder' => '{"type":"service_account","client_email":"...","private_key":"-----BEGIN PRIVATE KEY-----\\n..."}' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_seo_push_manual_field' ) ),
                array( 'type' => 'note', 'content' => __( '提示：推送结果会按通道记录在文章/页面编辑页右侧的“SEO 推送状态”面板；批量历史推送每批最多处理 50 条，适合失败重试和逐步补推旧内容。', 'qiling' ) ),

                array(
                    'type' => 'section',
                    'title' => __( '第三方资源', 'qiling' ),
                    'desc' => __( '留空使用主题内置资源；如需使用 CDN，请先确认下方允许域名。', 'qiling' ),
                ),
                array(
                    'type' => 'note',
                    'content' => __(
                        '隐私与可用性提示：启用外部 CDN 后，访客浏览器会直接请求对应第三方域名，可能向服务商暴露 IP、User-Agent、Referer 等请求信息；CDN 不可用时相关模块可能延迟或回退。主题会固定内置库版本，并在外链域名不在白名单时自动回退到本地资源。',
                        'qiling'
                    ),
                ),
                array(
                    'id' => 'third_party_asset_allowed_hosts',
                    'type' => 'textarea',
                    'label' => __( '外部资源允许域名', 'qiling' ),
                    'desc' => __(
                        '每行一个域名，支持 *.example.com。默认已允许 cdn.jsdelivr.net、unpkg.com、cdnjs.cloudflare.com；外部资源必须使用 HTTPS，自有 CDN 或国内公共 CDN 请在这里添加。',
                        'qiling'
                    ),
                    'attrs' => array(
                        'rows' => 4,
                        'placeholder' => "cdn.example.com\n*.static.example.com",
                    ),
                ),
                array(
                    'id' => 'swiper_css_url',
                    'type' => 'text',
                    'label' => __( 'Swiper CSS 地址', 'qiling' ),
                    'desc' => __(
                        '支持 https://... 或 /wp-content/...；外部域名必须在允许域名中；留空使用主题内置 Swiper CSS',
                        'qiling'
                    ),
                    'attrs' => array(
                        'placeholder' => 'https://cdn.example.com/swiper.css 或 /wp-content/uploads/swiper.css',
                    ),
                ),
                array(
                    'id' => 'swiper_js_url',
                    'type' => 'text',
                    'label' => __( 'Swiper JS 地址', 'qiling' ),
                    'desc' => __(
                        '支持 https://... 或 /wp-content/...；外部域名必须在允许域名中；留空使用主题内置 Swiper JS',
                        'qiling'
                    ),
                    'attrs' => array(
                        'placeholder' => 'https://cdn.example.com/swiper.js 或 /wp-content/uploads/swiper.js',
                    ),
                ),
                array(
                    'id' => 'chart_js_cdn',
                    'type' => 'text',
                    'label' => __( 'Chart.js 地址', 'qiling' ),
                    'desc' => __(
                        '支持 https://... 或 /wp-content/...；外部域名必须在允许域名中；留空使用主题内置 Chart.js 2.7.2',
                        'qiling'
                    ),
                    'attrs' => array(
                        'placeholder' => 'https://cdn.example.com/chart.js 或 /wp-content/uploads/chart.js',
                    ),
                ),

                array( 'type' => 'section', 'title' => __( '图标库', 'qiling' ), 'desc' => __( '仅保留 Iconfont Symbol/JS 方式。支持第三方地址和站内地址，主题会通过 PHP 直接输出标准 script 标签加载', 'qiling' ) ),
                array(
                    'id' => 'iconfont_js_url',
                    'type' => 'text',
                    'label' => __( 'Iconfont JS 地址', 'qiling' ),
                    'desc' => __( '支持 https://... 或 /wp-content/...。用于 Symbol/JS 方式，图标类名如 <code>icon-xxx</code>', 'qiling' ),
                    'attrs' => array(
                        'placeholder' => 'https://at.alicdn.com/t/c/font_xxx.js 或 /wp-content/uploads/iconfont/font_xxx.js',
                    ),
                ),

                array( 'type' => 'section', 'title' => __( '代码设置', 'qiling' ) ),
                array( 'id' => 'baidu_analytics', 'type' => 'textarea', 'label' => __( '百度统计代码/ID', 'qiling' ), 'desc' => __( '推荐只填写 HM 统计 ID。主题会通过 PHP 直接输出标准 script 标签，不再用前端 JS 动态插入', 'qiling' ) ),
                array( 'id' => 'custom_css', 'type' => 'textarea', 'label' => __( '自定义 CSS', 'qiling' ), 'desc' => __( '此处为内联样式。若在里面写远程 @import、字体或背景图地址，安全扫描仍可能提示外链', 'qiling' ) ),
                array( 'id' => 'custom_js', 'type' => 'textarea', 'label' => __( '自定义 JS', 'qiling' ), 'desc' => __( '推荐只写站内业务脚本，不要再写 document.createElement(script) 这类动态外链加载代码；若填写 <script src>，主题会改为 PHP 输出标准脚本标签', 'qiling' ) ),
                array( 'type' => 'section', 'title' => __( '域名设置检查', 'qiling' ), 'desc' => __( '手动检查主题设置里是否还残留旧域名差异，同时识别当前仍依赖绝对站内地址的设置项。不会在后台常驻扫描。', 'qiling' ) ),
                array( 'id' => 'domain_check_whitelist', 'type' => 'textarea', 'label' => __( '检测域名白名单', 'qiling' ), 'desc' => __( '这些域名不会再被当成旧域名候选或异常差异。适合填写你自己保留使用的 CDN / API / 静态资源域名。支持每行一个域名或 URL，也支持 *.example.com 这种通配写法。', 'qiling' ), 'attrs' => array( 'rows' => 5, 'placeholder' => "cdn.example.com\nstatic.example.com\n*.alicdn.com" ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_domain_compare_field' ) ),

                array( 'type' => 'section', 'title' => __( '主题设置数据修复', 'qiling' ), 'desc' => __( '检测并修复 developer_starter_options 的序列化异常（常见于 SQL 直接替换域名）。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_theme_options_repair_field' ) ),

                array( 'type' => 'section', 'title' => __( '页面模块数据修复', 'qiling' ), 'desc' => __( '如果你曾用 SQL 直接批量替换域名，可能会破坏模块配置的序列化数据，导致页面模块“看起来被清空”。这里可以批量修复 _developer_starter_modules。建议先备份数据库。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_modules_repair_field' ) ),

                array( 'type' => 'section', 'title' => __( '系统认证页面修复与去重', 'qiling' ), 'desc' => __( '检测并一键清理因高并发或异常重复生成的“用户登录 / 用户注册 / 找回密码 / 个人中心”页面。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_auth_pages_cleanup_field' ) ),
            ),

            // ========== 文章选项卡 ==========
            'article' => array(
                array( 'type' => 'section', 'title' => __( '默认文章列表设置', 'qiling' ), 'desc' => __( '适用于常规列表首页文章列表', 'qiling' ) ),
                array( 'id' => 'blog_visual_preset', 'type' => 'select', 'label' => __( '博客视觉风格', 'qiling' ), 'choices' => $blog_visual_preset_choices, 'default' => 'default', 'desc' => __( '统一控制原生文章流、博客模板和博客模块的视觉预设。默认企业内容会兼容原有文章列表设置，开发者 / 极简 / 艺术家可在下方做深度定制。', 'qiling' ) ),
                array( 'id' => 'article_thumb_height', 'type' => 'number', 'label' => __( '缩略图高度(px)', 'qiling' ), 'desc' => __( '默认: 180', 'qiling' ) ),
                array( 'id' => 'hide_article_thumb', 'type' => 'checkbox', 'label' => __( '隐藏缩略图', 'qiling' ), 'desc' => __( '勾选后文章列表不显示缩略图', 'qiling' ) ),
                array( 'id' => 'hide_article_excerpt', 'type' => 'checkbox', 'label' => __( '隐藏摘要', 'qiling' ), 'desc' => __( '勾选后文章列表不显示摘要', 'qiling' ) ),
                array( 'id' => 'hide_article_date', 'type' => 'checkbox', 'label' => __( '隐藏日期', 'qiling' ), 'desc' => __( '勾选后文章列表不显示发布日期', 'qiling' ) ),
                array( 'id' => 'hide_article_category', 'type' => 'checkbox', 'label' => __( '隐藏分类', 'qiling' ), 'desc' => __( '勾选后文章列表不显示所属分类', 'qiling' ) ),
                array( 'id' => 'hide_article_author', 'type' => 'checkbox', 'label' => __( '隐藏作者', 'qiling' ), 'desc' => __( '勾选后文章列表不显示文章作者', 'qiling' ) ),
                array( 'id' => 'article_excerpt_length', 'type' => 'number', 'label' => __( '摘要字数', 'qiling' ), 'desc' => __( '默认: 80', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '博客风格深度定制', 'qiling' ), 'desc' => __( '为“技术开发者 / 极简 / 艺术家”分别设置默认布局、信息显隐、分类页节奏和附加 CSS。留空时继续使用当前风格的内置默认值。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_blog_preset_customization_field' ) ),

                array( 'type' => 'section', 'title' => __( '分类页设置', 'qiling' ), 'desc' => __( '控制分类归档页每页显示数量，留空使用 WordPress 默认设置', 'qiling' ) ),
                array( 'id' => 'category_per_page', 'type' => 'number', 'label' => __( '分类页每页数量', 'qiling' ), 'desc' => __( '默认使用 WordPress“阅读设置”中的每页数量', 'qiling' ) ),
                array( 'id' => 'archive_loading_mode', 'type' => 'select', 'label' => __( '分类/搜索页加载模式', 'qiling' ), 'default' => 'regular', 'choices' => array(
                    'regular'  => __( '常规分页（默认）', 'qiling' ),
                    'infinite' => __( '无限滚动', 'qiling' ),
                ), 'desc' => __( '选择“无限滚动”后，分类页和搜索页会在用户滚动到底部时自动加载下一页；默认保持常规分页。', 'qiling' ), 'search_terms' => array( '无限滚动', '加载更多', '分类分页', '搜索分页' ) ),
                array( 'id' => 'category_header_enable', 'type' => 'checkbox', 'label' => __( '显示分类页头部', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后分类页不输出顶部标题区。默认开启。', 'qiling' ), 'search_terms' => array( '分类头部', '分类标题区' ) ),
                array( 'id' => 'category_breadcrumb_enable', 'type' => 'checkbox', 'label' => __( '显示分类面包屑', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后仅隐藏分类页头部里的面包屑，不再影响标题、描述和文章数量。', 'qiling' ), 'search_terms' => array( '分类面包屑', '分类导航' ) ),
                array( 'id' => 'category_show_icon', 'type' => 'checkbox', 'label' => __( '显示分类图标', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后分类页头部不显示分类图标。', 'qiling' ) ),
                array( 'id' => 'category_show_description', 'type' => 'checkbox', 'label' => __( '显示分类描述', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后分类页头部不显示分类描述。', 'qiling' ) ),
                array( 'id' => 'category_show_count', 'type' => 'checkbox', 'label' => __( '显示文章数量', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后分类页头部不显示文章数量统计。', 'qiling' ) ),
                array( 'id' => 'category_count_label', 'type' => 'text', 'label' => __( '文章数量文案', 'qiling' ), 'attrs' => array( 'placeholder' => '%s 篇文章' ), 'desc' => __( '支持 %s 作为数量占位符；留空使用“%s 篇文章”。', 'qiling' ) ),
                array( 'id' => 'category_show_sort_row', 'type' => 'checkbox', 'label' => __( '显示筛选排序行', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后高级筛选栏不显示“排序”这一行。', 'qiling' ), 'search_terms' => array( '高级筛选排序', '分类排序' ) ),
                array( 'id' => 'category_sort_options', 'type' => 'checkbox_group', 'label' => __( '可用排序项', 'qiling' ), 'default' => array( 'latest', 'random', 'hot', 'like', 'favorite' ), 'choices' => array(
                    'latest'   => __( '最新', 'qiling' ),
                    'random'   => __( '随机', 'qiling' ),
                    'hot'      => __( '热门', 'qiling' ),
                    'like'     => __( '点赞', 'qiling' ),
                    'favorite' => __( '收藏', 'qiling' ),
                ), 'desc' => __( '控制分类页高级筛选栏中显示哪些排序按钮；如果全部取消，会自动回退到“最新”。', 'qiling' ) ),
                array( 'id' => 'category_default_sort', 'type' => 'select', 'label' => __( '默认排序', 'qiling' ), 'default' => 'latest', 'choices' => array(
                    'latest'   => __( '最新', 'qiling' ),
                    'random'   => __( '随机', 'qiling' ),
                    'hot'      => __( '热门', 'qiling' ),
                    'like'     => __( '点赞', 'qiling' ),
                    'favorite' => __( '收藏', 'qiling' ),
                ), 'desc' => __( '进入分类页和高级筛选 Ajax 的默认排序方式；如果未包含在可用排序项里，会使用第一个可用排序项。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '基础归档与搜索页设置', 'qiling' ), 'desc' => __( '控制标签、日期、类型归档页和搜索结果页的基础展示项；默认保持当前前台效果。', 'qiling' ) ),
                array( 'id' => 'archive_header_enable', 'type' => 'checkbox', 'label' => __( '显示归档页头部', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后标签、日期、类型等归档页不输出顶部标题区。', 'qiling' ), 'search_terms' => array( '归档头部', '标签页头部', '日期归档头部' ) ),
                array( 'id' => 'archive_breadcrumb_enable', 'type' => 'checkbox', 'label' => __( '显示归档面包屑', 'qiling' ), 'default' => '1', 'desc' => __( '控制归档页头部中的“首页 / 当前归档”。', 'qiling' ) ),
                array( 'id' => 'archive_show_kicker', 'type' => 'checkbox', 'label' => __( '显示归档类型小标题', 'qiling' ), 'default' => '1', 'desc' => __( '如“标签归档”“日期归档”“类型归档”。', 'qiling' ) ),
                array( 'id' => 'archive_show_description', 'type' => 'checkbox', 'label' => __( '显示归档描述', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后归档页不显示标签/分类/日期描述。', 'qiling' ) ),
                array( 'id' => 'archive_show_count', 'type' => 'checkbox', 'label' => __( '显示归档内容数量', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后归档页头部不显示“多少篇内容”。', 'qiling' ) ),
                array( 'id' => 'archive_empty_title', 'type' => 'text', 'label' => __( '归档空状态标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( '暂时没有内容', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'archive_empty_text', 'type' => 'text', 'label' => __( '归档空状态说明', 'qiling' ), 'attrs' => array( 'placeholder' => __( '这个归档下暂时还没有公开文章，可以换个关键词或返回首页继续浏览。', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'search_form_enable', 'type' => 'checkbox', 'label' => __( '显示搜索页搜索框', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后搜索结果页不显示页内搜索表单。', 'qiling' ), 'search_terms' => array( '搜索页表单', '搜索框' ) ),
                array( 'id' => 'search_scope_enable', 'type' => 'checkbox', 'label' => __( '显示搜索范围下拉', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后搜索页表单不显示“全部/标题/正文/标签”范围选择。', 'qiling' ) ),
                array( 'id' => 'search_result_show_thumb', 'type' => 'checkbox', 'label' => __( '搜索结果显示缩略图', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'search_result_show_type', 'type' => 'checkbox', 'label' => __( '搜索结果显示内容类型', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'search_result_show_date', 'type' => 'checkbox', 'label' => __( '搜索结果显示日期', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'search_result_show_excerpt', 'type' => 'checkbox', 'label' => __( '搜索结果显示摘要', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'search_result_excerpt_length', 'type' => 'number', 'label' => __( '搜索结果摘要字数', 'qiling' ), 'default' => '40', 'attrs' => array( 'min' => '10', 'max' => '120' ), 'desc' => __( '默认 40，建议 10-120。', 'qiling' ) ),
                array( 'id' => 'search_empty_title', 'type' => 'text', 'label' => __( '搜索空状态标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( '未找到与“关键词”相关的内容', 'qiling' ) ), 'desc' => __( '留空使用默认文案；有关键词时主题会继续自动带入关键词。', 'qiling' ) ),
                array( 'id' => 'search_empty_text', 'type' => 'text', 'label' => __( '搜索空状态说明', 'qiling' ), 'attrs' => array( 'placeholder' => __( '可以换一个更短的关键词，或从最新内容继续浏览。', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '作者页设置', 'qiling' ), 'desc' => __( '控制作者主页的头部资料、社交链接、统计项和文章列表展示；默认保持当前前台效果。', 'qiling' ) ),
                array( 'id' => 'author_page_header_enable', 'type' => 'checkbox', 'label' => __( '显示作者页头部', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后作者页不输出顶部资料区。', 'qiling' ), 'search_terms' => array( '作者页头部', '作者主页头部' ) ),
                array( 'id' => 'author_page_show_avatar', 'type' => 'checkbox', 'label' => __( '显示作者头像', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_page_show_bio', 'type' => 'checkbox', 'label' => __( '显示作者简介', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_page_empty_bio_text', 'type' => 'text', 'label' => __( '作者空简介文案', 'qiling' ), 'attrs' => array( 'placeholder' => __( '这个人很懒，什么都没有留下...', 'qiling' ) ), 'desc' => __( '留空使用默认文案；关闭“显示作者简介”后不会输出。', 'qiling' ) ),
                array( 'id' => 'author_page_show_actions', 'type' => 'checkbox', 'label' => __( '显示关注/私信按钮', 'qiling' ), 'default' => '1', 'desc' => __( '仅在启灵社区相关功能可用时生效。', 'qiling' ) ),
                array( 'id' => 'author_page_show_social', 'type' => 'checkbox', 'label' => __( '显示作者社交链接', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_page_show_stats', 'type' => 'checkbox', 'label' => __( '显示作者统计', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_page_stat_items', 'type' => 'checkbox_group', 'label' => __( '作者统计项', 'qiling' ), 'default' => array( 'posts', 'views', 'comments', 'joined' ), 'choices' => array(
                    'posts'    => __( '文章', 'qiling' ),
                    'views'    => __( '浏览', 'qiling' ),
                    'comments' => __( '评论', 'qiling' ),
                    'joined'   => __( '加入时间', 'qiling' ),
                ), 'desc' => __( '取消某项后作者页统计区不再显示该数据；如果全部取消，统计区自动隐藏。', 'qiling' ) ),
                array( 'id' => 'author_page_posts_title', 'type' => 'text', 'label' => __( '作者文章区标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( 'TA 的文章', 'qiling' ) ), 'desc' => __( '留空使用默认标题。', 'qiling' ) ),
                array( 'id' => 'author_page_posts_summary_enable', 'type' => 'checkbox', 'label' => __( '显示作者文章数量说明', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_page_empty_posts_text', 'type' => 'text', 'label' => __( '作者无文章文案', 'qiling' ), 'attrs' => array( 'placeholder' => __( '该作者暂未发布任何文章', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'author_page_posts_columns', 'type' => 'select', 'label' => __( '作者文章列表列数', 'qiling' ), 'default' => '3', 'choices' => array(
                    '2' => __( '两列', 'qiling' ),
                    '3' => __( '三列', 'qiling' ),
                    '4' => __( '四列', 'qiling' ),
                ), 'desc' => __( '控制作者页文章网格列数，移动端仍按主题响应式样式展示。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '视频封面设置', 'qiling' ), 'desc' => __( '视频文章在列表中的显示设置', 'qiling' ) ),
                array( 'id' => 'video_cover_enable', 'type' => 'checkbox', 'label' => __( '启用视频封面', 'qiling' ), 'desc' => __( '勾选后视频文章在列表中显示可悬停播放的视频封面', 'qiling' ) ),
                array( 'id' => 'video_badge_enable', 'type' => 'checkbox', 'label' => __( '显示视频标签', 'qiling' ), 'desc' => __( '勾选后视频文章显示红色"视频"标签', 'qiling' ) ),
                array( 'id' => 'app_badge_enable', 'type' => 'checkbox', 'label' => __( '启用软件库APP角标', 'qiling' ), 'desc' => __( '勾选后关联启灵软件库数据的文章显示“APP”角标', 'qiling' ) ),
                array( 'id' => 'qilingshop_vip_badge_enable', 'type' => 'checkbox', 'label' => __( '启用积分插件VIP角标', 'qiling' ), 'desc' => __( '勾选后启灵积分商城付费资源显示"VIP"角标', 'qiling' ) ),
                array( 'id' => 'qilingshop_free_badge_enable', 'type' => 'checkbox', 'label' => __( '启用积分插件免费角标', 'qiling' ), 'desc' => __( '勾选后启灵积分商城免费资源显示"免费"角标', 'qiling' ) ),
                array( 'id' => 'album_badge_enable', 'type' => 'checkbox', 'label' => __( '启用相册模式角标', 'qiling' ), 'desc' => __( '勾选后相册模式文章显示“相册”角标', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '文章封面角标样式', 'qiling' ), 'desc' => __( '控制文章列表、分类页、博客模块和筛选结果中封面角标的颜色。背景支持纯色、渐变、rgba 和 var()；留空使用主题默认。', 'qiling' ) ),
                array( 'id' => 'cover_badge_video_bg', 'type' => 'text', 'label' => __( '视频角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-gradient-error)' ), 'search_terms' => array( '视频标签颜色', '视频角标颜色' ) ),
                array( 'id' => 'cover_badge_video_text', 'type' => 'text', 'label' => __( '视频角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'cover_badge_app_bg', 'type' => 'text', 'label' => __( 'APP角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-gradient-success)' ), 'search_terms' => array( '软件库角标颜色', 'APP标签颜色' ) ),
                array( 'id' => 'cover_badge_app_text', 'type' => 'text', 'label' => __( 'APP角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'cover_badge_free_bg', 'type' => 'text', 'label' => __( '免费角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-gradient-brand)' ), 'search_terms' => array( '启灵商城免费角标颜色', '免费标签颜色' ) ),
                array( 'id' => 'cover_badge_free_text', 'type' => 'text', 'label' => __( '免费角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'cover_badge_vip_bg', 'type' => 'text', 'label' => __( 'VIP角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-gradient-warning)' ), 'search_terms' => array( '启灵商城VIP角标颜色', '付费角标颜色' ) ),
                array( 'id' => 'cover_badge_vip_text', 'type' => 'text', 'label' => __( 'VIP角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'cover_badge_album_bg', 'type' => 'text', 'label' => __( '相册角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-gradient-accent)' ), 'search_terms' => array( '相册标签颜色', '相册角标颜色' ) ),
                array( 'id' => 'cover_badge_album_text', 'type' => 'text', 'label' => __( '相册角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'cover_badge_category_bg', 'type' => 'text', 'label' => __( '分类角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-badge-bg)' ), 'search_terms' => array( '分类标签颜色', '分类角标颜色' ) ),
                array( 'id' => 'cover_badge_category_text', 'type' => 'text', 'label' => __( '分类角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-component-badge-text)' ) ),
                array( 'id' => 'cover_badge_sticky_bg', 'type' => 'text', 'label' => __( '置顶角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--color-primary)' ), 'search_terms' => array( '置顶标签颜色', '置顶角标颜色' ) ),
                array( 'id' => 'cover_badge_sticky_text', 'type' => 'text', 'label' => __( '置顶角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'cover_badge_hd_bg', 'type' => 'text', 'label' => __( '清晰度角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'var(--qiling-gradient-warning)' ), 'search_terms' => array( 'HD角标颜色', '清晰度标签颜色' ) ),
                array( 'id' => 'cover_badge_hd_text', 'type' => 'text', 'label' => __( '清晰度角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),
                array( 'id' => 'cover_badge_rating_bg', 'type' => 'text', 'label' => __( '评分角标背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, #fb7185 0%, #e11d48 100%)' ), 'search_terms' => array( '视频评分角标颜色', '评分标签颜色' ) ),
                array( 'id' => 'cover_badge_rating_text', 'type' => 'text', 'label' => __( '评分角标文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ) ),

                array( 'type' => 'section', 'title' => __( '文章封面角标高级规则', 'qiling' ), 'desc' => __( '控制文章封面角标的显示顺序、数量、位置和常用文案；留空保持默认。', 'qiling' ) ),
                array( 'id' => 'cover_badge_position', 'type' => 'select', 'label' => __( '角标位置', 'qiling' ), 'default' => 'top-right', 'choices' => array(
                    'top-right'    => __( '右上角', 'qiling' ),
                    'top-left'     => __( '左上角', 'qiling' ),
                    'bottom-right' => __( '右下角', 'qiling' ),
                    'bottom-left'  => __( '左下角', 'qiling' ),
                ), 'desc' => __( '适用于分类页、博客模块和筛选结果中的标准封面角标组；分类 Tab 和置顶角标会保留自身布局。', 'qiling' ) ),
                array( 'id' => 'cover_badge_max_count', 'type' => 'number', 'label' => __( '最多显示数量', 'qiling' ), 'desc' => __( '填 0 或留空表示不限制。', 'qiling' ), 'attrs' => array( 'min' => '0', 'step' => '1', 'placeholder' => '0' ) ),
                array( 'id' => 'cover_badge_order', 'type' => 'text', 'label' => __( '角标显示顺序', 'qiling' ), 'desc' => __( '用英文逗号分隔，例如：hd,rating,video,app,album,free,vip。未填写的角标按默认顺序排在后面。', 'qiling' ), 'attrs' => array( 'placeholder' => 'hd,rating,video,app,album,free,vip' ), 'search_terms' => array( '角标排序', '标签顺序', '徽章排序' ) ),
                array( 'id' => 'cover_badge_video_label', 'type' => 'text', 'label' => __( '视频角标文案', 'qiling' ), 'attrs' => array( 'placeholder' => '视频' ) ),
                array( 'id' => 'cover_badge_app_label', 'type' => 'text', 'label' => __( 'APP角标文案', 'qiling' ), 'attrs' => array( 'placeholder' => 'APP' ) ),
                array( 'id' => 'cover_badge_free_label', 'type' => 'text', 'label' => __( '免费角标文案', 'qiling' ), 'attrs' => array( 'placeholder' => '免费' ) ),
                array( 'id' => 'cover_badge_vip_label', 'type' => 'text', 'label' => __( 'VIP角标文案', 'qiling' ), 'attrs' => array( 'placeholder' => 'VIP' ) ),
                array( 'id' => 'cover_badge_album_label', 'type' => 'text', 'label' => __( '相册角标文案', 'qiling' ), 'attrs' => array( 'placeholder' => '相册' ) ),
                array( 'id' => 'cover_badge_sticky_label', 'type' => 'text', 'label' => __( '置顶角标文案', 'qiling' ), 'attrs' => array( 'placeholder' => '置顶' ) ),

                array( 'type' => 'section', 'title' => __( '软件库徽章样式', 'qiling' ), 'desc' => __( '控制启灵软件库归档、详情中许可证和平台徽章的颜色；留空使用主题默认。', 'qiling' ) ),
                array( 'id' => 'qiapp_badge_free_bg', 'type' => 'text', 'label' => __( '免费/开源徽章背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#d1fae5' ), 'search_terms' => array( '软件库免费徽章颜色', '软件库开源徽章颜色' ) ),
                array( 'id' => 'qiapp_badge_free_text', 'type' => 'text', 'label' => __( '免费/开源徽章文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#047857' ) ),
                array( 'id' => 'qiapp_badge_paid_bg', 'type' => 'text', 'label' => __( '收费/订阅徽章背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#dbeafe' ), 'search_terms' => array( '软件库收费徽章颜色', '软件库订阅徽章颜色' ) ),
                array( 'id' => 'qiapp_badge_paid_text', 'type' => 'text', 'label' => __( '收费/订阅徽章文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#1d4ed8' ) ),
                array( 'id' => 'qiapp_badge_trial_bg', 'type' => 'text', 'label' => __( '试用/增值徽章背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffedd5' ), 'search_terms' => array( '软件库试用徽章颜色', '软件库免费增值徽章颜色' ) ),
                array( 'id' => 'qiapp_badge_trial_text', 'type' => 'text', 'label' => __( '试用/增值徽章文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#7c2d12' ) ),
                array( 'id' => 'qiapp_badge_neutral_bg', 'type' => 'text', 'label' => __( '平台/默认徽章背景', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ), 'search_terms' => array( '软件库平台徽章颜色', '软件库默认徽章颜色' ) ),
                array( 'id' => 'qiapp_badge_neutral_text', 'type' => 'text', 'label' => __( '平台/默认徽章文字', 'qiling' ), 'attrs' => array( 'placeholder' => '#475569' ) ),

                array( 'type' => 'section', 'title' => __( '文章详情页设置', 'qiling' ) ),
                array( 'id' => 'hide_post_sidebar', 'type' => 'checkbox', 'label' => __( '隐藏侧边栏', 'qiling' ), 'desc' => __( '勾选后文章详情页不显示侧边栏（默认显示）', 'qiling' ) ),
                array( 'id' => 'reading_progress_enable', 'type' => 'checkbox', 'label' => __( '启用阅读进度条', 'qiling' ), 'desc' => __( '开启后文章详情页顶部显示随滚动变化的阅读进度细条', 'qiling' ) ),
                array( 'id' => 'hide_post_breadcrumb', 'type' => 'checkbox', 'label' => __( '隐藏面包屑导航', 'qiling' ), 'desc' => __( '勾选后文章详情页头部不显示“首页 / 分类”面包屑。', 'qiling' ), 'search_terms' => array( '文章面包屑', '详情页面包屑' ) ),
                array( 'id' => 'hide_post_category', 'type' => 'checkbox', 'label' => __( '隐藏分类名称', 'qiling' ), 'desc' => __( '勾选后文章详情页不显示分类名称', 'qiling' ) ),
                array( 'id' => 'hide_post_publish_date', 'type' => 'checkbox', 'label' => __( '隐藏发布日期', 'qiling' ), 'desc' => __( '勾选后文章详情页元信息中不显示发布日期。', 'qiling' ) ),
                array( 'id' => 'hide_post_author', 'type' => 'checkbox', 'label' => __( '隐藏作者', 'qiling' ), 'desc' => __( '勾选后文章详情页元信息中不显示作者。', 'qiling' ) ),
                array( 'id' => 'hide_post_comment_count', 'type' => 'checkbox', 'label' => __( '隐藏评论数', 'qiling' ), 'desc' => __( '勾选后文章详情页元信息中不显示评论数量。', 'qiling' ) ),
                array( 'id' => 'hide_post_tags', 'type' => 'checkbox', 'label' => __( '隐藏文章标签', 'qiling' ), 'desc' => __( '勾选后文章正文底部不显示标签列表。', 'qiling' ) ),
                array( 'id' => 'hide_post_navigation', 'type' => 'checkbox', 'label' => __( '隐藏上一篇/下一篇', 'qiling' ), 'desc' => __( '勾选后文章底部不显示上一篇和下一篇导航。', 'qiling' ) ),
                array( 'id' => 'hide_post_comments', 'type' => 'checkbox', 'label' => __( '隐藏评论区', 'qiling' ), 'desc' => __( '勾选后文章底部不输出评论列表和评论表单，不影响 WordPress 评论数据。', 'qiling' ) ),
                array( 'id' => 'post_modified_date_enable', 'type' => 'checkbox', 'label' => __( '显示最后更新时间', 'qiling' ), 'desc' => __( '开启后文章详情页元信息中显示最后更新时间；默认关闭，仅在修改时间晚于发布时间时显示。', 'qiling' ) ),
                array( 'id' => 'post_header_bg_color', 'type' => 'text', 'label' => __( '文章头部背景颜色', 'qiling' ), 'desc' => __( '支持纯色或渐变色，如 #111827 或 linear-gradient(135deg, #111827 0%, #1f2937 100%)', 'qiling' ) ),
                array( 'id' => 'post_header_title_color', 'type' => 'color', 'label' => __( '文章头部标题颜色', 'qiling' ), 'default' => '#ffffff' ),
                array( 'id' => 'post_header_category_color', 'type' => 'color', 'label' => __( '文章头部分类名称颜色', 'qiling' ), 'default' => '#c7d2fe' ),
                array( 'id' => 'post_header_meta_color', 'type' => 'color', 'label' => __( '文章头部辅助文字颜色', 'qiling' ), 'default' => '#e2e8f0' ),

                array( 'type' => 'section', 'title' => __( '语音朗读设置', 'qiling' ), 'desc' => __( '调用访客浏览器内置 Web Speech API，不请求服务器生成音频；不同设备可用声音可能不同。', 'qiling' ) ),
                array( 'id' => 'post_speech_enable', 'type' => 'checkbox', 'label' => __( '启用文章朗读', 'qiling' ), 'desc' => __( '开启后文章详情页显示正文朗读控件。', 'qiling' ) ),
                array( 'id' => 'comment_speech_enable', 'type' => 'checkbox', 'label' => __( '启用评论朗读', 'qiling' ), 'desc' => __( '开启后每条评论显示独立朗读按钮，点击时才读取该条评论文本。', 'qiling' ) ),
                array( 'id' => 'speech_language', 'type' => 'select', 'label' => __( '朗读语言', 'qiling' ), 'default' => 'zh-CN', 'choices' => array(
                    'zh-CN' => __( '中文（普通话）', 'qiling' ),
                    'en-US' => __( '英文（美式）', 'qiling' ),
                ), 'desc' => __( '用于筛选浏览器可用语音；找不到匹配语音时会回退到浏览器默认声音。', 'qiling' ) ),
                array( 'id' => 'speech_voice_preference', 'type' => 'select', 'label' => __( '声音偏好', 'qiling' ), 'default' => 'auto', 'choices' => array(
                    'auto'   => __( '自动', 'qiling' ),
                    'female' => __( '偏女声', 'qiling' ),
                    'male'   => __( '偏男声', 'qiling' ),
                ), 'desc' => __( '浏览器没有标准性别字段，主题会根据设备语音名称尽量匹配；匹配不到时自动回退。', 'qiling' ) ),
                array( 'id' => 'speech_rate', 'type' => 'number', 'label' => __( '朗读语速', 'qiling' ), 'default' => '1', 'attrs' => array( 'min' => '0.6', 'max' => '1.4', 'step' => '0.1' ), 'desc' => __( '建议 0.8-1.2，默认 1。', 'qiling' ) ),
                array( 'id' => 'speech_pitch', 'type' => 'number', 'label' => __( '朗读音调', 'qiling' ), 'default' => '1', 'attrs' => array( 'min' => '0.6', 'max' => '1.4', 'step' => '0.1' ), 'desc' => __( '默认 1。部分浏览器或系统语音可能会忽略该设置。', 'qiling' ) ),
                array( 'id' => 'speech_volume', 'type' => 'number', 'label' => __( '默认音量', 'qiling' ), 'default' => '1', 'attrs' => array( 'min' => '0', 'max' => '1', 'step' => '0.1' ), 'desc' => __( '默认 1。访客可在前台播放器临时调整，浏览器会记住本机偏好。', 'qiling' ) ),
                array( 'id' => 'speech_voice_name', 'type' => 'text', 'label' => __( '指定语音名称', 'qiling' ), 'desc' => __( '可填写浏览器 voices 中的语音名称；访客设备不存在该声音时自动回退。留空则按语言和声音偏好匹配。', 'qiling' ) ),
                array( 'id' => 'speech_voice_uri', 'type' => 'text', 'label' => __( '指定语音 URI', 'qiling' ), 'desc' => __( '高级选项。优先按 voiceURI 精确匹配，适合固定浏览器环境；公网访客设备差异较大时建议留空。', 'qiling' ) ),
                array( 'id' => 'speech_pause_on_hidden', 'type' => 'checkbox', 'label' => __( '切换标签页时暂停', 'qiling' ), 'desc' => __( '开启后访客切到其他标签页或应用时自动暂停当前朗读。', 'qiling' ), 'default' => '1' ),

                array( 'type' => 'section', 'title' => __( '文章互动设置', 'qiling' ) ),
                array( 'id' => 'post_like_enable', 'type' => 'checkbox', 'label' => __( '启用文章点赞功能', 'qiling' ), 'desc' => __( '开启后文章页面显示点赞图标', 'qiling' ) ),
                array( 'id' => 'post_favorite_enable', 'type' => 'checkbox', 'label' => __( '启用文章收藏功能', 'qiling' ), 'desc' => __( '开启后文章页面显示收藏图标', 'qiling' ) ),
                array( 'id' => 'post_poster_enable', 'type' => 'checkbox', 'label' => __( '启用文章海报功能', 'qiling' ), 'desc' => __( '开启后文章页面显示“生成海报”按钮', 'qiling' ) ),
                array( 'id' => 'post_poster_button_label', 'type' => 'text', 'label' => __( '文章海报按钮文案', 'qiling' ), 'attrs' => array( 'placeholder' => __( '生成海报', 'qiling' ) ), 'desc' => __( '留空使用默认文案“生成海报”。', 'qiling' ) ),
                array( 'id' => 'post_poster_guest_cache_enable', 'type' => 'checkbox', 'label' => __( '允许游客缓存文章海报', 'qiling' ), 'desc' => __( '开启后未登录游客可将生成的海报写入服务器缓存；关闭时游客仍可生成并本地下载海报，但不会写入 uploads。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '相关文章设置', 'qiling' ) ),
                array( 'id' => 'related_posts_enable', 'type' => 'checkbox', 'label' => __( '显示相关文章', 'qiling' ), 'desc' => __( '文章底部显示相关文章推荐', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'related_posts_title', 'type' => 'text', 'label' => __( '相关文章标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( '相关文章', 'qiling' ) ), 'desc' => __( '留空使用默认标题“相关文章”。', 'qiling' ) ),
                array( 'id' => 'related_posts_count', 'type' => 'number', 'label' => __( '相关文章数量', 'qiling' ), 'default' => '3', 'attrs' => array( 'min' => '1', 'max' => '12' ), 'desc' => __( '默认 3，建议 1-12 篇。', 'qiling' ) ),
                array( 'id' => 'related_posts_columns', 'type' => 'select', 'label' => __( '相关文章列数', 'qiling' ), 'default' => '3', 'choices' => array(
                    '2' => __( '两列', 'qiling' ),
                    '3' => __( '三列', 'qiling' ),
                    '4' => __( '四列', 'qiling' ),
                ) ),
                array( 'id' => 'related_posts_source', 'type' => 'select', 'label' => __( '相关文章来源', 'qiling' ), 'choices' => array(
                    'category' => __( '同分类', 'qiling' ),
                    'random'   => __( '随机', 'qiling' ),
                    'latest'   => __( '最新', 'qiling' ),
                ), 'desc' => __( '选择相关文章的来源规则', 'qiling' ) ),
                array( 'id' => 'related_posts_show_thumb', 'type' => 'checkbox', 'label' => __( '显示缩略图', 'qiling' ), 'desc' => __( '相关文章列表显示缩略图', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'related_posts_show_date', 'type' => 'checkbox', 'label' => __( '显示日期', 'qiling' ), 'desc' => __( '相关文章列表显示发布日期。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'related_posts_show_excerpt', 'type' => 'checkbox', 'label' => __( '显示摘要', 'qiling' ), 'desc' => __( '相关文章列表显示文章摘要。', 'qiling' ) ),
                array( 'id' => 'related_posts_show_category', 'type' => 'checkbox', 'label' => __( '显示分类', 'qiling' ), 'desc' => __( '相关文章列表显示文章主分类。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '正文样式设置', 'qiling' ), 'desc' => __( '自定义文章正文的显示样式', 'qiling' ) ),
                array( 'id' => 'post_content_width', 'type' => 'select', 'label' => __( '正文宽度', 'qiling' ), 'choices' => array( 'narrow' => __( '窄（680px）', 'qiling' ), 'standard' => __( '标准（800px）', 'qiling' ), 'wide' => __( '宽（960px）', 'qiling' ) ), 'desc' => __( '文章正文区域的最大宽度', 'qiling' ) ),
                array( 'id' => 'post_font_size', 'type' => 'select', 'label' => __( '字体大小', 'qiling' ), 'choices' => array( 'small' => __( '小（16px）', 'qiling' ), 'medium' => __( '中（18px）', 'qiling' ), 'large' => __( '大（20px）', 'qiling' ) ), 'desc' => __( '文章正文的字体大小', 'qiling' ) ),
                array( 'id' => 'post_line_height', 'type' => 'select', 'label' => __( '行距', 'qiling' ), 'choices' => array( 'compact' => __( '紧凑（1.6）', 'qiling' ), 'standard' => __( '标准（1.8）', 'qiling' ), 'relaxed' => __( '宽松（2.0）', 'qiling' ) ), 'desc' => __( '文章正文的行高', 'qiling' ) ),
                array( 'id' => 'post_paragraph_spacing', 'type' => 'select', 'label' => __( '段落间距', 'qiling' ), 'choices' => array( 'small' => __( '小（1em）', 'qiling' ), 'medium' => __( '中（1.5em）', 'qiling' ), 'large' => __( '大（2em）', 'qiling' ) ), 'desc' => __( '段落之间的间距', 'qiling' ) ),
                array( 'id' => 'post_image_max_width', 'type' => 'select', 'label' => __( '图片最大宽度', 'qiling' ), 'choices' => array( '100' => '100%（撑满）', '90' => '90%', '80' => '80%' ), 'desc' => __( '文章内图片的最大宽度', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '图片查看', 'qiling' ) ),
                array( 'id' => 'post_image_zoom_enable', 'type' => 'checkbox', 'label' => __( '启用图片点击放大', 'qiling' ), 'desc' => __( '开启后文章正文图片可点击放大查看', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '代码高亮设置', 'qiling' ), 'desc' => __( '使用 PrismJS 为代码块添加语法高亮（仅在文章包含代码时加载）', 'qiling' ) ),
                array( 'id' => 'code_highlight_enable', 'type' => 'checkbox', 'label' => __( '启用代码高亮', 'qiling' ), 'desc' => __( '开启后文章中的代码块将显示语法高亮', 'qiling' ) ),
                array(
                    'id' => 'prism_css_cdn',
                    'type' => 'text',
                    'label' => __( 'PrismJS CSS CDN', 'qiling' ),
                    'desc' => __(
                        '留空使用本地 PrismJS 1.29.0 CSS；填写外链时域名必须在“第三方资源”的允许域名中。',
                        'qiling'
                    ),
                ),
                array(
                    'id' => 'prism_js_cdn',
                    'type' => 'text',
                    'label' => __( 'PrismJS JS CDN', 'qiling' ),
                    'desc' => __(
                        '留空使用本地 PrismJS 1.29.0 JS；填写外链时域名必须在“第三方资源”的允许域名中。',
                        'qiling'
                    ),
                ),

                array( 'type' => 'section', 'title' => __( '评论设置', 'qiling' ), 'desc' => __( '评论区相关功能设置', 'qiling' ) ),
                array( 'id' => 'comment_username_privacy', 'type' => 'checkbox', 'label' => __( '用户名隐私保护', 'qiling' ), 'desc' => __( '开启后评论区用户名只显示首字，其余用*号代替（如：张** 或 J***）', 'qiling' ) ),
                array( 'id' => 'comments_header_enable', 'type' => 'checkbox', 'label' => __( '显示评论区标题栏', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后有评论时不显示“读者评论 / 评论数量”这一栏。', 'qiling' ), 'search_terms' => array( '评论区标题', '评论标题栏' ) ),
                array( 'id' => 'comments_show_count', 'type' => 'checkbox', 'label' => __( '显示评论数量', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'comments_show_empty_hint', 'type' => 'checkbox', 'label' => __( '显示暂无评论提示', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'comments_show_logged_in_as', 'type' => 'checkbox', 'label' => __( '显示当前登录评论身份', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'comments_section_title', 'type' => 'text', 'label' => __( '评论区标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( '读者评论', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_empty_hint_text', 'type' => 'text', 'label' => __( '暂无评论提示', 'qiling' ), 'attrs' => array( 'placeholder' => __( '暂无评论，快来抢沙发吧！', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_closed_text', 'type' => 'text', 'label' => __( '评论关闭提示', 'qiling' ), 'attrs' => array( 'placeholder' => __( '评论已关闭', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_form_logged_in_title', 'type' => 'text', 'label' => __( '已登录表单标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( '发表评论', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_form_guest_title', 'type' => 'text', 'label' => __( '游客表单标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( '参与讨论', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_textarea_placeholder', 'type' => 'text', 'label' => __( '评论输入框占位文案', 'qiling' ), 'attrs' => array( 'placeholder' => __( '写下你的评论...', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_submit_label', 'type' => 'text', 'label' => __( '评论提交按钮文字', 'qiling' ), 'attrs' => array( 'placeholder' => __( '发表评论', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_login_required_text', 'type' => 'text', 'label' => __( '登录后评论提示', 'qiling' ), 'attrs' => array( 'placeholder' => __( '请先登录后发表评论', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_login_button_label', 'type' => 'text', 'label' => __( '评论登录按钮文字', 'qiling' ), 'attrs' => array( 'placeholder' => __( '立即登录', 'qiling' ) ), 'desc' => __( '留空使用默认文案。', 'qiling' ) ),
                array( 'id' => 'comments_avatar_size', 'type' => 'number', 'label' => __( '评论头像尺寸', 'qiling' ), 'default' => '48', 'attrs' => array( 'min' => '24', 'max' => '96' ), 'suffix' => 'px', 'desc' => __( '默认 48，建议 24-96。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '作者信息卡片', 'qiling' ), 'desc' => __( '在文章底部显示作者信息', 'qiling' ) ),
                array( 'id' => 'author_box_enable', 'type' => 'checkbox', 'label' => __( '显示作者信息', 'qiling' ), 'desc' => __( '在文章底部显示作者信息卡片', 'qiling' ) ),
                array( 'id' => 'author_show_avatar', 'type' => 'checkbox', 'label' => __( '显示头像', 'qiling' ), 'desc' => __( '显示作者的头像', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_show_name', 'type' => 'checkbox', 'label' => __( '显示昵称', 'qiling' ), 'desc' => __( '显示作者的显示名称', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_show_bio', 'type' => 'checkbox', 'label' => __( '显示简介', 'qiling' ), 'desc' => __( '显示作者的个人简介', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'author_show_social', 'type' => 'checkbox', 'label' => __( '显示社交链接', 'qiling' ), 'desc' => __( '显示作者的社交媒体链接（需在用户资料中设置）', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '用户社交链接设置', 'qiling' ), 'desc' => __( '控制用户可以在个人资料中设置哪些社交链接', 'qiling' ) ),
                array( 'id' => 'user_social_weibo', 'type' => 'checkbox', 'label' => __( '启用微博', 'qiling' ), 'desc' => __( '允许用户设置微博链接', 'qiling' ) ),
                array( 'id' => 'user_social_twitter', 'type' => 'checkbox', 'label' => __( '启用 X (Twitter)', 'qiling' ), 'desc' => __( '允许用户设置X/Twitter链接', 'qiling' ) ),
                array( 'id' => 'user_social_wechat', 'type' => 'checkbox', 'label' => __( '启用微信', 'qiling' ), 'desc' => __( '允许用户设置微信（二维码，悬停显示）', 'qiling' ) ),
                array( 'id' => 'user_social_github', 'type' => 'checkbox', 'label' => __( '启用 GitHub', 'qiling' ), 'desc' => __( '允许用户设置GitHub链接', 'qiling' ) ),
                array( 'id' => 'user_social_bilibili', 'type' => 'checkbox', 'label' => __( '启用 B站', 'qiling' ), 'desc' => __( '允许用户设置Bilibili链接', 'qiling' ) ),
                array( 'id' => 'user_social_zhihu', 'type' => 'checkbox', 'label' => __( '启用知乎', 'qiling' ), 'desc' => __( '允许用户设置知乎链接', 'qiling' ) ),
                array( 'id' => 'user_social_website', 'type' => 'checkbox', 'label' => __( '启用个人网站', 'qiling' ), 'desc' => __( '允许用户设置个人网站链接', 'qiling' ) ),
                array( 'id' => 'user_social_linkedin', 'type' => 'checkbox', 'label' => __( '启用 LinkedIn', 'qiling' ), 'desc' => __( '允许用户设置LinkedIn链接', 'qiling' ) ),
                array( 'id' => 'user_social_youtube', 'type' => 'checkbox', 'label' => __( '启用 YouTube', 'qiling' ), 'desc' => __( '允许用户设置YouTube链接', 'qiling' ) ),
                array( 'id' => 'user_social_instagram', 'type' => 'checkbox', 'label' => __( '启用 Instagram', 'qiling' ), 'desc' => __( '允许用户设置Instagram链接', 'qiling' ) ),
                array( 'id' => 'user_social_tiktok', 'type' => 'checkbox', 'label' => __( '启用 TikTok', 'qiling' ), 'desc' => __( '允许用户设置TikTok链接', 'qiling' ) ),
                array( 'id' => 'user_social_wechat_mp', 'type' => 'checkbox', 'label' => __( '启用公众号', 'qiling' ), 'desc' => __( '允许用户设置微信公众号二维码', 'qiling' ) ),
                array( 'id' => 'user_social_qq', 'type' => 'checkbox', 'label' => __( '启用 QQ', 'qiling' ), 'desc' => __( '允许用户设置QQ号', 'qiling' ) ),
                array( 'id' => 'user_social_custom', 'type' => 'checkbox', 'label' => __( '启用自定义社交链接', 'qiling' ), 'desc' => __( '允许用户设置自定义社交链接', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '文章目录（TOC）', 'qiling' ), 'desc' => __( '自动生成文章标题目录，方便读者快速导航', 'qiling' ) ),
                array( 'id' => 'toc_enable', 'type' => 'checkbox', 'label' => __( '启用文章目录', 'qiling' ), 'desc' => __( '根据文章中的H2/H3标题自动生成目录', 'qiling' ) ),
                array( 'id' => 'toc_heading_levels', 'type' => 'select', 'label' => __( '解析标题层级', 'qiling' ), 'choices' => array( 'h2' => __( '仅 H2', 'qiling' ), 'h2h3' => __( 'H2 和 H3', 'qiling' ), 'h2h3h4' => __( 'H2、H3 和 H4', 'qiling' ) ), 'desc' => __( '选择要包含在目录中的标题层级', 'qiling' ) ),
                array( 'id' => 'toc_position', 'type' => 'select', 'label' => __( '目录位置', 'qiling' ), 'choices' => array( 'sidebar' => __( '右侧悬浮', 'qiling' ), 'before_content' => __( '正文开头', 'qiling' ) ), 'desc' => __( '目录显示的位置', 'qiling' ) ),
                array( 'id' => 'toc_collapsible', 'type' => 'checkbox', 'label' => __( '可折叠目录', 'qiling' ), 'desc' => __( '允许用户折叠/展开目录', 'qiling' ) ),
                array( 'id' => 'toc_min_headings', 'type' => 'number', 'label' => __( '最少标题数', 'qiling' ), 'desc' => __( '文章至少包含多少个标题才显示目录，默认: 3', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '版权信息', 'qiling' ), 'desc' => __( '在文章底部显示版权声明', 'qiling' ) ),
                array( 'id' => 'copyright_enable', 'type' => 'checkbox', 'label' => __( '显示版权信息', 'qiling' ), 'desc' => __( '在文章底部显示版权声明', 'qiling' ) ),
                array( 'id' => 'copyright_content', 'type' => 'textarea', 'label' => __( '版权内容', 'qiling' ), 'desc' => __( '支持变量: {title}=文章标题, {url}=文章链接, {author}=作者, {date}=发布日期, {site}=网站名称', 'qiling' ) ),
                array( 'id' => 'copyright_reprint_notice', 'type' => 'text', 'label' => __( '转载须知', 'qiling' ), 'desc' => __( '如：转载请注明出处', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '阅读统计', 'qiling' ), 'desc' => __( '文章浏览量和阅读时长统计', 'qiling' ) ),
                array( 'id' => 'post_views_enable', 'type' => 'checkbox', 'label' => __( '启用浏览量统计', 'qiling' ), 'desc' => __( '统计并显示文章的浏览次数', 'qiling' ) ),
                array( 'id' => 'post_views_exclude_admin', 'type' => 'checkbox', 'label' => __( '排除管理员', 'qiling' ), 'desc' => __( '管理员访问不计入浏览量', 'qiling' ) ),
                array( 'id' => 'reading_time_enable', 'type' => 'checkbox', 'label' => __( '显示阅读时长', 'qiling' ), 'desc' => __( '根据文章字数估算阅读时间', 'qiling' ) ),
                array( 'id' => 'reading_speed', 'type' => 'number', 'label' => __( '阅读速度(字/分钟)', 'qiling' ), 'desc' => __( '默认: 400（中文平均阅读速度）', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '默认缩略图', 'qiling' ), 'desc' => __( '当文章或分类没有设置特色图时使用的兜底图片', 'qiling' ) ),
                array( 'id' => 'default_thumbnail', 'type' => 'image', 'label' => __( '默认缩略图', 'qiling' ), 'desc' => __( '建议尺寸≥800px，支持站内相对路径', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '缩略图优化', 'qiling' ), 'desc' => __( '在文章列表等场景加载优化后的小尺寸图片，而非原图，提升加载速度', 'qiling' ) ),
                array( 'id' => 'thumbnail_optimize_enable', 'type' => 'checkbox', 'label' => __( '启用缩略图优化', 'qiling' ), 'desc' => __( '开启后文章列表页将加载裁剪后的小尺寸图片', 'qiling' ) ),
                array( 'id' => 'thumbnail_width', 'type' => 'number', 'label' => __( '缩略图宽度', 'qiling' ), 'desc' => __( '默认: 400（像素）', 'qiling' ) ),
                array( 'id' => 'thumbnail_height', 'type' => 'number', 'label' => __( '缩略图高度', 'qiling' ), 'desc' => __( '默认: 300（像素）', 'qiling' ) ),
                array( 'id' => 'thumbnail_crop_position', 'type' => 'select', 'label' => __( '裁剪位置', 'qiling' ), 'choices' => array( 'center' => __( '居中 (推荐)', 'qiling' ), 'top' => __( '顶部', 'qiling' ), 'bottom' => __( '底部', 'qiling' ), 'left' => __( '左边', 'qiling' ), 'right' => __( '右边', 'qiling' ) ), 'desc' => __( '当图片比例与目标尺寸不同时，从哪个位置裁剪', 'qiling' ) ),
                array( 'id' => 'thumbnail_quality', 'type' => 'number', 'label' => __( '图片质量', 'qiling' ), 'desc' => __( '1-100之间，默认: 85（较高质量且文件较小）', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_thumbnail_cache_section' ) ),

                array( 'type' => 'section', 'title' => __( 'CDN图片处理', 'qiling' ), 'desc' => __( '如果您使用CDN存储图片，可配置CDN的图片处理参数', 'qiling' ) ),
                array( 'id' => 'thumbnail_cdn_domain', 'type' => 'text', 'label' => __( 'CDN域名', 'qiling' ), 'desc' => __( '您的CDN自定义域名，如: cdn.example.com（留空则自动检测常见CDN）', 'qiling' ) ),
                array( 'id' => 'thumbnail_cdn_type', 'type' => 'select', 'label' => __( 'CDN类型', 'qiling' ), 'choices' => array( 'aliyun_oss' => __( '阿里云 OSS', 'qiling' ), 'tencent_cos' => __( '腾讯云 COS', 'qiling' ), 'qiniu' => __( '七牛云', 'qiling' ), 'upyun' => __( '又拍云', 'qiling' ) ), 'desc' => __( '选择您使用的CDN服务商，用于生成正确的图片处理参数', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '特色图片显示方式', 'qiling' ), 'desc' => __( '控制文章列表中特色图片的加载来源和显示效果', 'qiling' ) ),
                array( 'id' => 'thumbnail_source', 'type' => 'select', 'label' => __( '图片来源', 'qiling' ), 'choices' => array( 'cropped' => __( '裁剪后的缩略图（推荐，节省带宽）', 'qiling' ), 'original' => __( '原图（保留完整细节，但加载较慢）', 'qiling' ) ), 'desc' => __( '选择加载原图还是优化裁剪后的缩略图。注意：需启用上方"缩略图优化"功能才能使用裁剪缩略图', 'qiling' ) ),
                array( 'id' => 'thumbnail_display_mode', 'type' => 'select', 'label' => __( '显示方式', 'qiling' ), 'choices' => array( 'cover' => __( '剪切适应（推荐，填满容器并裁剪超出部分）', 'qiling' ), 'contain' => __( '缩放适应（保持比例，完整显示在容器内，可能留白）', 'qiling' ), 'fill' => __( '拉伸适应（拉伸填满容器，可能变形）', 'qiling' ), 'none' => __( '原图显示（不缩放，按原尺寸显示）', 'qiling' ) ), 'desc' => __( '控制图片在容器内的CSS object-fit显示效果', 'qiling' ) ),
            ),

            // ========== 页面选项卡 ==========
            'pages' => array(
                array( 'type' => 'section', 'title' => __( '页面头部设置', 'qiling' ) ),
                array( 'id' => 'page_header_padding', 'type' => 'text', 'label' => __( '默认页面头部高度', 'qiling' ), 'desc' => __( '设置默认页面的面包屑区域高度 (Padding)。<br>默认值：100px 0 60px (上 左右 下)；直接输入数值即可，例如：80px 0 40px', 'qiling' ) ),
                array( 'id' => 'page_header_background', 'type' => 'text', 'label' => __( '统一页面头部背景', 'qiling' ), 'attrs' => array( 'placeholder' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%)' ), 'desc' => __( '控制默认页面头部，以及产品中心、新闻中心、案例展示、关于我们、联系我们、解决方案、资源下载、功能清单、常见问题这些统一模板头部。支持纯色、rgba() 或 linear-gradient(...)；留空使用默认渐变。', 'qiling' ) ),
                array( 'id' => 'page_header_title_color', 'type' => 'text', 'label' => __( '统一页面头部标题颜色', 'qiling' ), 'attrs' => array( 'placeholder' => '#ffffff' ), 'desc' => __( '控制统一页面头部的大标题颜色。', 'qiling' ) ),
                array( 'id' => 'page_header_subtitle_color', 'type' => 'text', 'label' => __( '统一页面头部摘要颜色', 'qiling' ), 'attrs' => array( 'placeholder' => 'rgba(255, 255, 255, 0.82)' ), 'desc' => __( '控制统一页面头部摘要/副标题颜色。留空使用默认值。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '普通页面设置', 'qiling' ), 'desc' => __( '控制默认 page.php 页面结构；不改变已选择专用页面模板的产品、新闻、案例等模板设置。', 'qiling' ) ),
                array( 'id' => 'basic_page_header_enable', 'type' => 'checkbox', 'label' => __( '显示普通页面头部', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后默认页面不输出顶部标题区；单页编辑器里的“隐藏页面头部”仍会优先生效。', 'qiling' ), 'search_terms' => array( '普通页面头部', '页面标题区' ) ),
                array( 'id' => 'basic_page_header_description_enable', 'type' => 'checkbox', 'label' => __( '显示页面头部摘要', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后页面头部不显示页面摘要/副标题。', 'qiling' ) ),
                array( 'id' => 'basic_page_content_padding_enable', 'type' => 'checkbox', 'label' => __( '启用普通页面内容上下间距', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后移除默认 section-padding，适合页面内容自己控制上下留白。', 'qiling' ) ),
                array( 'id' => 'basic_page_sidebar_enable', 'type' => 'checkbox', 'label' => __( '显示普通页面侧栏', 'qiling' ), 'default' => '1', 'desc' => __( '关闭后默认页面即使启用了“页面侧边栏”小工具也会全宽显示。', 'qiling' ) ),
                array( 'id' => 'basic_page_featured_image_enable', 'type' => 'checkbox', 'label' => __( '显示普通页面特色图', 'qiling' ), 'default' => '', 'desc' => __( '开启后在普通页面正文上方显示特色图；默认关闭以保持当前效果。', 'qiling' ) ),
                array( 'id' => 'basic_page_links_enable', 'type' => 'checkbox', 'label' => __( '显示普通页面分页链接', 'qiling' ), 'default' => '1', 'desc' => __( '控制正文中的 <!--nextpage--> 分页导航。', 'qiling' ) ),
                array( 'id' => 'basic_page_comments_enable', 'type' => 'checkbox', 'label' => __( '启用普通页面评论区', 'qiling' ), 'default' => '', 'desc' => __( '开启后普通页面会在正文下方显示评论区；仍受 WordPress 讨论设置和主题“完全禁用评论”限制。默认关闭以保持当前效果。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '搜索页与 404 装修', 'qiling' ), 'desc' => __( '为搜索页和 404 页面指定独立装修页面。搜索页会保留原生搜索结果，404 页面可由装修页面整页接管。', 'qiling' ) ),
                array( 'id' => 'search_builder_enable', 'type' => 'checkbox', 'label' => __( '启用搜索页装修', 'qiling' ), 'default' => '' ),
                array( 'id' => 'search_builder_page_id', 'type' => 'select', 'label' => __( '搜索页装修页面', 'qiling' ), 'choices' => $footer_builder_page_options, 'default' => '', 'desc' => __( '选择一个已做页面装修的普通页面，作为搜索页的自定义区块来源。', 'qiling' ) ),
                array( 'id' => 'search_builder_position', 'type' => 'select', 'label' => __( '搜索页插入位置', 'qiling' ), 'default' => 'prepend_results', 'choices' => array(
                    'prepend_results' => __( '插入到搜索结果上方（推荐）', 'qiling' ),
                    'replace_header'  => __( '替换顶部说明区', 'qiling' ),
                ), 'desc' => __( '不管选择哪种方式，搜索结果列表和分页都会继续保留。', 'qiling' ) ),
                array( 'id' => 'error_404_builder_enable', 'type' => 'checkbox', 'label' => __( '启用 404 完整装修页接管', 'qiling' ), 'default' => '' ),
                array( 'id' => 'error_404_builder_page_id', 'type' => 'select', 'label' => __( '404 完整装修页面', 'qiling' ), 'choices' => $footer_builder_page_options, 'default' => '', 'desc' => __( '选择后，404 页面将直接渲染该装修页面的模块内容。', 'qiling' ) ),
                array( 'type' => 'section', 'title' => __( '404 旧链接跳转', 'qiling' ), 'desc' => __( '当请求已经被 WordPress 判定为 404 时，按规则把旧路径跳转到新地址；正常存在的页面不会受影响。', 'qiling' ) ),
                array( 'id' => 'error_404_redirect_enable', 'type' => 'checkbox', 'label' => __( '启用 404 旧链接跳转', 'qiling' ), 'default' => '', 'desc' => __( '仅在 404 请求中执行精确路径匹配，不会扫描全站页面。', 'qiling' ) ),
                array( 'id' => 'error_404_redirect_status', 'type' => 'select', 'label' => __( '跳转状态码', 'qiling' ), 'default' => '301', 'choices' => array(
                    '301' => __( '301 永久跳转', 'qiling' ),
                    '302' => __( '302 临时跳转', 'qiling' ),
                ), 'desc' => __( '确认规则无误后建议使用 301；调试阶段可先用 302。', 'qiling' ) ),
                array( 'id' => 'error_404_redirect_rules', 'type' => 'textarea', 'label' => __( '跳转规则', 'qiling' ), 'attrs' => array( 'rows' => 7, 'placeholder' => "/shop => /aishop\n/old-page => /new-page" ), 'desc' => __( '每行一条，格式为“旧路径 => 新路径”。旧路径为精确匹配；目标仅允许本站地址。', 'qiling' ) ),
                array( 'type' => 'section', 'title' => __( '404 简易装修', 'qiling' ), 'desc' => __( '不想单独做一个装修页面时，可以在这里快速调整默认 404 页面的文案、配色和搜索入口。开启上方“404 完整装修页接管”并选择页面后，将优先使用完整装修页面。', 'qiling' ) ),
                array( 'id' => 'error_404_preset', 'type' => 'select', 'label' => __( '404 页面风格', 'qiling' ), 'default' => 'guide', 'choices' => array(
                    'guide' => __( '引导卡片', 'qiling' ),
                    'clean' => __( '简洁留白', 'qiling' ),
                    'bold'  => __( '深色聚焦', 'qiling' ),
                    'image' => __( '背景图沉浸', 'qiling' ),
                ), 'search_terms' => array( '404 样式', '404 风格', '404 简易装修' ) ),
                array( 'id' => 'error_404_code', 'type' => 'text', 'label' => __( '404 数字/短标识', 'qiling' ), 'default' => '404', 'attrs' => array( 'class' => 'small-text', 'placeholder' => '404' ) ),
                array( 'id' => 'error_404_title', 'type' => 'text', 'label' => __( '404 标题', 'qiling' ), 'attrs' => array( 'placeholder' => __( '页面未找到', 'qiling' ) ), 'search_terms' => array( '404 文案', '页面未找到' ) ),
                array( 'id' => 'error_404_description', 'type' => 'textarea', 'label' => __( '404 说明文字', 'qiling' ), 'attrs' => array( 'rows' => 3, 'placeholder' => __( '抱歉，您访问的页面不存在或已被移除。请检查网址是否正确，或返回首页继续浏览。', 'qiling' ) ) ),
                array( 'id' => 'error_404_primary_label', 'type' => 'text', 'label' => __( '主按钮文字', 'qiling' ), 'default' => __( '返回首页', 'qiling' ), 'attrs' => array( 'class' => 'regular-text', 'placeholder' => __( '返回首页', 'qiling' ) ) ),
                array( 'id' => 'error_404_back_enable', 'type' => 'checkbox', 'label' => __( '显示返回上页按钮', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'error_404_secondary_label', 'type' => 'text', 'label' => __( '返回上页按钮文字', 'qiling' ), 'default' => __( '返回上页', 'qiling' ), 'attrs' => array( 'class' => 'regular-text', 'placeholder' => __( '返回上页', 'qiling' ) ) ),
                array( 'id' => 'error_404_search_enable', 'type' => 'checkbox', 'label' => __( '显示搜索框', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'error_404_search_hint', 'type' => 'text', 'label' => __( '搜索提示文字', 'qiling' ), 'attrs' => array( 'placeholder' => __( '也许您可以试试搜索：', 'qiling' ) ) ),
                array( 'id' => 'error_404_background_color', 'type' => 'color', 'label' => __( '404 背景色', 'qiling' ), 'default' => '#f8fafc' ),
                array( 'id' => 'error_404_accent_color', 'type' => 'color', 'label' => __( '404 强调色', 'qiling' ), 'default' => '#2563eb' ),
                array( 'id' => 'error_404_background_image', 'type' => 'image', 'label' => __( '404 背景图', 'qiling' ), 'desc' => __( '仅在“背景图沉浸”风格下最明显；其他风格会轻微叠加使用。建议 1600×1000 px 以上。', 'qiling' ), 'preview_style' => 'display:block;max-width:220px;margin-top:10px;border-radius:8px;' ),

                array( 'type' => 'section', 'title' => __( '产品中心设置', 'qiling' ) ),
                array( 'id' => 'products_category', 'type' => 'select', 'label' => __( '调用分类', 'qiling' ), 'choices' => $cat_options, 'desc' => __( '选择要显示的文章分类', 'qiling' ) ),
                array( 'id' => 'products_per_page', 'type' => 'number', 'label' => __( '每页显示数量', 'qiling' ), 'desc' => __( '默认: 10', 'qiling' ) ),
                array( 'id' => 'products_columns', 'type' => 'select', 'label' => __( '每行列数', 'qiling' ), 'choices' => array( '2' => __( '2列', 'qiling' ), '3' => __( '3列', 'qiling' ), '4' => __( '4列', 'qiling' ) ) ),
                array( 'id' => 'products_thumb_height', 'type' => 'number', 'label' => __( '缩略图高度(px)', 'qiling' ), 'desc' => __( '默认: 200', 'qiling' ) ),
                array( 'id' => 'hide_products_title', 'type' => 'checkbox', 'label' => __( '隐藏标题', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '新闻中心设置', 'qiling' ) ),
                array( 'id' => 'news_category', 'type' => 'select', 'label' => __( '调用分类', 'qiling' ), 'choices' => $cat_options ),
                array( 'id' => 'news_per_page', 'type' => 'number', 'label' => __( '每页显示数量', 'qiling' ), 'desc' => __( '默认: 10', 'qiling' ) ),
                array( 'id' => 'news_thumb_height', 'type' => 'number', 'label' => __( '缩略图高度(px)', 'qiling' ), 'desc' => __( '默认: 150', 'qiling' ) ),
                array( 'id' => 'hide_news_title', 'type' => 'checkbox', 'label' => __( '隐藏标题', 'qiling' ) ),
                array( 'id' => 'hide_news_date', 'type' => 'checkbox', 'label' => __( '隐藏日期', 'qiling' ) ),
                array( 'id' => 'hide_news_excerpt', 'type' => 'checkbox', 'label' => __( '隐藏摘要', 'qiling' ) ),
                array( 'id' => 'hide_news_thumb', 'type' => 'checkbox', 'label' => __( '隐藏缩略图', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '案例展示设置', 'qiling' ) ),
                array( 'id' => 'cases_category', 'type' => 'select', 'label' => __( '调用分类', 'qiling' ), 'choices' => $cat_options ),
                array( 'id' => 'cases_per_page', 'type' => 'number', 'label' => __( '每页显示数量', 'qiling' ), 'desc' => __( '默认: 9', 'qiling' ) ),
                array( 'id' => 'cases_columns', 'type' => 'select', 'label' => __( '每行列数', 'qiling' ), 'choices' => array( '2' => __( '2列', 'qiling' ), '3' => __( '3列', 'qiling' ), '4' => __( '4列', 'qiling' ) ) ),
                array( 'id' => 'cases_thumb_height', 'type' => 'number', 'label' => __( '缩略图高度(px)', 'qiling' ), 'desc' => __( '默认: 220', 'qiling' ) ),
                array( 'id' => 'hide_cases_title', 'type' => 'checkbox', 'label' => __( '隐藏标题', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '关于我们设置', 'qiling' ), 'desc' => __( '配置"关于我们"页面Tab栏显示的内容板块', 'qiling' ) ),
                array( 'id' => 'about_show_timeline', 'type' => 'checkbox', 'label' => __( '显示发展历程', 'qiling' ) ),
                array( 'id' => 'about_show_team', 'type' => 'checkbox', 'label' => __( '显示团队成员', 'qiling' ) ),
                array( 'id' => 'about_show_certificates', 'type' => 'checkbox', 'label' => __( '显示资质荣誉', 'qiling' ), 'desc' => __( '展示企业资质证书、荣誉奖项等图片', 'qiling' ) ),
                array( 'id' => 'about_show_environment', 'type' => 'checkbox', 'label' => __( '显示公司环境', 'qiling' ), 'desc' => __( '展示办公环境、生产车间等照片', 'qiling' ) ),
                array( 'id' => 'about_show_culture', 'type' => 'checkbox', 'label' => __( '显示企业文化', 'qiling' ), 'desc' => __( '展示企业价值观、使命愿景等内容', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '联系我们设置', 'qiling' ) ),
                array( 'id' => 'contact_show_form', 'type' => 'checkbox', 'label' => __( '显示联系表单', 'qiling' ), 'desc' => __( '在联系我们页面显示主题内置在线留言。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'contact_form_id', 'type' => 'number', 'label' => __( '联系表单ID', 'qiling' ), 'desc' => __( '留空时使用主题内置在线留言；填写启灵表单插件中的表单ID后改用启灵表单。', 'qiling' ), 'attrs' => array( 'min' => '0' ) ),
                array( 'id' => 'contact_message_login_required', 'type' => 'checkbox', 'label' => __( '仅登录用户可提交联系表单/在线留言', 'qiling' ), 'desc' => __( '开启后，游客提交联系我们表单和主题内置留言时会提示先登录。', 'qiling' ) ),
                array( 'id' => 'contact_show_info', 'type' => 'checkbox', 'label' => __( '显示基础信息', 'qiling' ), 'desc' => __( '显示企业名称、电话、QQ、微信二维码、邮箱、地址', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'contact_image', 'type' => 'image', 'label' => __( '右侧图片', 'qiling' ), 'desc' => __( '联系表单关闭时显示的图片', 'qiling' ) ),
            ),

            // ========== 个人中心装修选项卡 ==========
            'account_style' => array(
                array( 'type' => 'section', 'title' => __( '说明', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '本面板仅控制个人中心页面的样式（颜色/圆角/间距/布局等），不会修改个人中心业务逻辑、表单字段、nonce 校验与第三方插件扩展钩子。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '布局与密度', 'qiling' ) ),
                array( 'id' => 'account_style_layout_mode', 'type' => 'select', 'label' => __( '桌面端布局模式', 'qiling' ), 'choices' => array(
                    'sidebar' => __( '侧栏布局（默认）', 'qiling' ),
                    'top_tabs' => __( '顶部标签布局', 'qiling' ),
                ), 'default' => 'sidebar', 'desc' => __( '仅影响桌面端（≥992px）。移动端仍使用当前抽屉式导航，避免交互冲突。', 'qiling' ) ),
                array( 'id' => 'account_style_density', 'type' => 'select', 'label' => __( '页面密度', 'qiling' ), 'choices' => array(
                    'comfortable' => __( '舒适（默认）', 'qiling' ),
                    'compact' => __( '紧凑', 'qiling' ),
                ), 'default' => 'comfortable', 'desc' => __( '紧凑模式会减少卡片和表单间距，适合信息密度较高的站点。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '配色', 'qiling' ) ),
                array( 'id' => 'account_header_bg_color', 'type' => 'text', 'label' => __( '头部背景', 'qiling' ), 'desc' => __( '支持纯色或渐变（如 linear-gradient(...)）。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_header_name_color', 'type' => 'color', 'label' => __( '头部用户名颜色', 'qiling' ), 'default' => '#0f172a' ),
                array( 'id' => 'account_header_text_color', 'type' => 'color', 'label' => __( '头部正文颜色', 'qiling' ), 'default' => '#334155' ),
                array( 'id' => 'account_header_muted_text_color', 'type' => 'color', 'label' => __( '头部辅助文字颜色', 'qiling' ), 'default' => '#64748b' ),
                array( 'id' => 'account_avatar_border_color', 'type' => 'color', 'label' => __( '头像描边颜色', 'qiling' ), 'default' => '#e2e8f0' ),
                array( 'id' => 'account_page_bg_color', 'type' => 'text', 'label' => __( '页面背景色', 'qiling' ), 'desc' => __( '如：#f8fafc。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_card_bg_color', 'type' => 'color', 'label' => __( '卡片背景色', 'qiling' ), 'default' => '#ffffff' ),
                array( 'id' => 'account_card_text_color', 'type' => 'color', 'label' => __( '卡片正文颜色', 'qiling' ), 'default' => '#334155' ),
                array( 'id' => 'account_muted_text_color', 'type' => 'color', 'label' => __( '弱化文字颜色', 'qiling' ), 'default' => '#64748b' ),
                array( 'id' => 'account_border_color', 'type' => 'color', 'label' => __( '分割线与边框颜色', 'qiling' ), 'default' => '#e2e8f0' ),
                array( 'id' => 'account_public_id_bg', 'type' => 'text', 'label' => __( 'ID 徽章背景', 'qiling' ), 'desc' => __( '支持纯色、rgba 或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_public_id_text_color', 'type' => 'color', 'label' => __( 'ID 徽章文字颜色', 'qiling' ), 'default' => '#334155' ),
                array( 'id' => 'account_public_id_border_color', 'type' => 'color', 'label' => __( 'ID 徽章边框颜色', 'qiling' ), 'default' => '#cbd5e1' ),
                array( 'id' => 'account_public_id_label_bg', 'type' => 'text', 'label' => __( 'ID 标签背景', 'qiling' ), 'desc' => __( '支持纯色、rgba 或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_public_id_label_text_color', 'type' => 'color', 'label' => __( 'ID 标签文字颜色', 'qiling' ), 'default' => '#ffffff' ),
                array( 'id' => 'account_home_button_bg', 'type' => 'text', 'label' => __( '个人主页按钮背景', 'qiling' ), 'desc' => __( '支持纯色、rgba 或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_home_button_text_color', 'type' => 'color', 'label' => __( '个人主页按钮图标颜色', 'qiling' ), 'default' => '#0f172a' ),
                array( 'id' => 'account_home_button_hover_bg', 'type' => 'text', 'label' => __( '个人主页按钮悬停背景', 'qiling' ), 'desc' => __( '支持纯色、rgba 或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_nav_bg', 'type' => 'text', 'label' => __( '导航卡片背景', 'qiling' ), 'desc' => __( '支持纯色或渐变。留空使用卡片背景。', 'qiling' ) ),
                array( 'id' => 'account_nav_text_color', 'type' => 'color', 'label' => __( '导航默认文字颜色', 'qiling' ), 'default' => '#475569' ),
                array( 'id' => 'account_nav_hover_bg', 'type' => 'text', 'label' => __( '导航悬停背景', 'qiling' ), 'desc' => __( '支持纯色、rgba 或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_nav_hover_text_color', 'type' => 'color', 'label' => __( '导航悬停文字颜色', 'qiling' ), 'default' => '#0f172a' ),
                array( 'id' => 'account_nav_active_bg', 'type' => 'text', 'label' => __( '导航激活背景', 'qiling' ), 'desc' => __( '支持纯色或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_nav_active_text_color', 'type' => 'color', 'label' => __( '导航激活文字颜色', 'qiling' ), 'default' => '#ffffff' ),
                array( 'id' => 'account_nav_logout_text_color', 'type' => 'color', 'label' => __( '退出登录文字颜色', 'qiling' ), 'default' => '#dc2626' ),
                array( 'id' => 'account_nav_logout_hover_bg', 'type' => 'text', 'label' => __( '退出登录悬停背景', 'qiling' ), 'desc' => __( '支持纯色、rgba 或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_nav_badge_bg', 'type' => 'text', 'label' => __( '导航角标背景', 'qiling' ), 'desc' => __( '支持纯色或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_nav_badge_text_color', 'type' => 'color', 'label' => __( '导航角标文字颜色', 'qiling' ), 'default' => '#ffffff' ),
                array( 'id' => 'account_button_bg', 'type' => 'text', 'label' => __( '主按钮背景', 'qiling' ), 'desc' => __( '支持纯色或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_button_text_color', 'type' => 'color', 'label' => __( '主按钮文字颜色', 'qiling' ), 'default' => '#ffffff' ),
                array( 'id' => 'account_field_bg', 'type' => 'text', 'label' => __( '表单输入框背景', 'qiling' ), 'desc' => __( '支持纯色或渐变。留空使用默认。', 'qiling' ) ),
                array( 'id' => 'account_field_text_color', 'type' => 'color', 'label' => __( '表单输入文字颜色', 'qiling' ), 'default' => '#0f172a' ),
                array( 'id' => 'account_field_border_color', 'type' => 'color', 'label' => __( '表单输入边框颜色', 'qiling' ), 'default' => '#e2e8f0' ),
                array( 'id' => 'account_field_focus_color', 'type' => 'color', 'label' => __( '表单聚焦边框颜色', 'qiling' ), 'default' => '#2563eb' ),

                array( 'type' => 'section', 'title' => __( '尺寸与间距', 'qiling' ) ),
                array( 'id' => 'account_sidebar_width', 'type' => 'text', 'label' => __( '侧栏宽度', 'qiling' ), 'default' => '260px', 'desc' => __( '建议 220px - 320px。支持 px/rem。', 'qiling' ) ),
                array( 'id' => 'account_content_gap', 'type' => 'text', 'label' => __( '侧栏与主区间距', 'qiling' ), 'default' => '30px', 'desc' => __( '建议 16px - 40px。支持 px/rem。', 'qiling' ) ),
                array( 'id' => 'account_card_radius', 'type' => 'text', 'label' => __( '卡片圆角', 'qiling' ), 'default' => '16px', 'desc' => __( '建议 8px - 24px。支持 px/rem。', 'qiling' ) ),
                array( 'id' => 'account_section_padding', 'type' => 'text', 'label' => __( '卡片内边距', 'qiling' ), 'default' => '30px', 'desc' => __( '建议 16px - 40px。支持 px/rem。', 'qiling' ) ),
                array( 'id' => 'account_nav_item_padding', 'type' => 'text', 'label' => __( '导航项内边距', 'qiling' ), 'default' => '14px 18px', 'desc' => __( '支持双值写法（如 12px 16px）。', 'qiling' ) ),
                array( 'id' => 'account_button_radius', 'type' => 'text', 'label' => __( '主按钮圆角', 'qiling' ), 'default' => '10px', 'desc' => __( '建议 6px - 16px。支持 px/rem。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '阴影与头像', 'qiling' ) ),
                array( 'id' => 'account_card_shadow_preset', 'type' => 'select', 'label' => __( '卡片阴影强度', 'qiling' ), 'choices' => array(
                    'none' => __( '无阴影', 'qiling' ),
                    'soft' => __( '柔和（默认）', 'qiling' ),
                    'medium' => __( '中等', 'qiling' ),
                ), 'default' => 'soft' ),
                array( 'id' => 'account_avatar_size', 'type' => 'text', 'label' => __( '头部头像尺寸', 'qiling' ), 'default' => '100px', 'desc' => __( '建议 72px - 120px。支持 px/rem。', 'qiling' ) ),
                array( 'id' => 'account_avatar_border_width', 'type' => 'text', 'label' => __( '头像描边宽度', 'qiling' ), 'default' => '4px', 'desc' => __( '建议 0px - 6px。支持 px。', 'qiling' ) ),
            ),

            // ========== 内容选项卡 ==========
            'content' => array(
                array( 'type' => 'section', 'title' => __( '发展历程', 'qiling' ), 'desc' => __( '在"关于我们"页面显示（需开启显示发展历程）', 'qiling' ) ),
                array( 'id' => 'timeline_items', 'type' => 'repeater', 'label' => __( '时间节点', 'qiling' ), 'fields' => array(
                    array( 'id' => 'year', 'label' => __( '年份', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'title', 'label' => __( '标题', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'desc', 'label' => __( '描述', 'qiling' ), 'type' => 'textarea' ),
                ) ),

                array( 'type' => 'section', 'title' => __( '团队成员', 'qiling' ), 'desc' => __( '在"关于我们"页面显示（需开启显示团队成员）', 'qiling' ) ),
                array( 'id' => 'team_members', 'type' => 'repeater', 'label' => __( '成员', 'qiling' ), 'fields' => array(
                    array( 'id' => 'name', 'label' => __( '姓名', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'position', 'label' => __( '职位', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'avatar', 'label' => __( '头像URL', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'desc', 'label' => __( '简介', 'qiling' ), 'type' => 'textarea' ),
                ) ),

                array( 'type' => 'section', 'title' => __( '资质荣誉', 'qiling' ), 'desc' => __( '在"关于我们"页面显示（需开启显示资质荣誉）', 'qiling' ) ),
                array( 'id' => 'about_certificates', 'type' => 'repeater', 'label' => __( '证书/荣誉', 'qiling' ), 'fields' => array(
                    array( 'id' => 'image', 'label' => __( '证书图片URL', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'title', 'label' => __( '证书名称', 'qiling' ), 'type' => 'text' ),
                ) ),

                array( 'type' => 'section', 'title' => __( '公司环境', 'qiling' ), 'desc' => __( '在"关于我们"页面显示（需开启显示公司环境）', 'qiling' ) ),
                array( 'id' => 'about_environment', 'type' => 'repeater', 'label' => __( '环境照片', 'qiling' ), 'fields' => array(
                    array( 'id' => 'image', 'label' => __( '照片URL', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'title', 'label' => __( '照片标题', 'qiling' ), 'type' => 'text' ),
                ) ),

                array( 'type' => 'section', 'title' => __( '企业文化', 'qiling' ), 'desc' => __( '在"关于我们"页面显示（需开启显示企业文化）', 'qiling' ) ),
                array( 'id' => 'about_culture', 'type' => 'repeater', 'label' => __( '文化内容', 'qiling' ), 'fields' => array(
                    array( 'id' => 'icon', 'label' => __( '图标(emoji或icon-xxx)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'title', 'label' => __( '标题', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'desc', 'label' => __( '描述', 'qiling' ), 'type' => 'textarea' ),
                ) ),

                array( 'type' => 'section', 'title' => __( '右侧浮动栏', 'qiling' ) ),
                array( 'id' => 'float_widget_enable', 'type' => 'checkbox', 'label' => __( '启用浮动栏', 'qiling' ), 'desc' => __( '开启后在前台显示右侧浮动栏', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'float_phone', 'type' => 'text', 'label' => __( '悬浮电话', 'qiling' ) ),
                array( 'id' => 'float_qq', 'type' => 'text', 'label' => __( '悬浮QQ', 'qiling' ) ),
                array( 'id' => 'float_wechat_qrcode', 'type' => 'image', 'label' => __( '悬浮微信二维码', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '浮动栏自定义项目', 'qiling' ), 'desc' => __( '添加自定义链接到浮动栏（如在线客服）', 'qiling' ) ),
                array( 'id' => 'float_custom_items', 'type' => 'repeater', 'label' => __( '自定义项目', 'qiling' ), 'fields' => array(
                    array( 'id' => 'title', 'label' => __( '标题', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'url', 'label' => __( '链接地址', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'icon', 'label' => __( '图标(emoji或Symbol类名)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'color', 'label' => __( '背景颜色', 'qiling' ), 'type' => 'text' ),
                ) ),
            ),

            // ========== 投稿选项卡 ==========
            'submit' => array(
                array( 'type' => 'section', 'title' => __( '用户投稿设置', 'qiling' ), 'desc' => __( '允许前台用户投稿文章，由管理员审核后发布', 'qiling' ) ),
                array( 'id' => 'submit_post_enable', 'type' => 'checkbox', 'label' => __( '启用用户投稿', 'qiling' ), 'desc' => __( '开启后，登录用户可在前台页面投稿文章', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '投稿页面', 'qiling' ) ),
                array( 'id' => 'submit_post_page_id', 'type' => 'select', 'label' => __( '投稿页面', 'qiling' ), 'choices' => $submit_page_options, 'desc' => __( '指定前台投稿表单页面。留空则自动查找使用“投稿页面模板”的页面', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '投稿限制', 'qiling' ) ),
                array(
                    'id'      => 'submit_post_categories',
                    'type'    => 'checkbox_group',
                    'label'   => __( '允许投稿的分类', 'qiling' ),
                    'choices' => $cat_id_options,
                    'desc'    => __( '选择用户可以投稿的分类，不勾选任何分类则允许投稿到所有分类', 'qiling' ),
                    'args'    => array(
                        'wrapper_style' => 'max-height:200px; overflow-y:auto; padding:10px; border:1px solid #ddd; border-radius:4px; background:#f9f9f9;',
                        'label_style'   => 'display:block;margin-bottom:8px;',
                    ),
                ),
                array( 'id' => 'submit_post_allow_tags', 'type' => 'checkbox', 'label' => __( '允许添加标签', 'qiling' ), 'desc' => __( '允许用户在投稿时添加标签', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'submit_post_max_tags', 'type' => 'number', 'label' => __( '最多标签数', 'qiling' ), 'desc' => __( '默认: 5', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '提示消息', 'qiling' ) ),
                array( 'id' => 'submit_post_success_message', 'type' => 'textarea', 'label' => __( '投稿成功提示', 'qiling' ), 'desc' => __( '默认: 投稿成功！请等待管理员审核。', 'qiling' ) ),
                array( 'id' => 'submit_post_disabled_message', 'type' => 'textarea', 'label' => __( '功能关闭提示', 'qiling' ), 'desc' => __( '默认: 投稿功能暂时关闭，请稍后再试。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '跳转设置', 'qiling' ) ),
                array( 'id' => 'submit_post_redirect_page', 'type' => 'select', 'label' => __( '投稿成功跳转页面', 'qiling' ), 'choices' => $page_options, 'desc' => __( '用户投稿成功后跳转到的页面。留空则自动查找使用"会员中心"模板的页面，并跳转到其投稿管理标签页。', 'qiling' ) ),
                array( 'id' => 'submit_post_redirect_tab', 'type' => 'text', 'label' => __( '跳转Tab参数', 'qiling' ), 'desc' => __( '默认: posts（个人中心的投稿管理标签）。如果跳转页面是个人中心页面，可指定标签参数。', 'qiling' ) ),
            ),

            // ========== 国际化选项卡 ==========
            'international' => array(
                array(
                    'type'     => 'custom',
                    'callback' => array( $this, 'render_international_center_overview_field' ),
                ),
                array(
                    'type'  => 'section',
                    'title' => __( '国际化基础工具箱', 'qiling' ),
                    'desc'  => __( '所有能力默认关闭。关闭状态下不会改变现有中文站、百度统计、阿里云验证码、备案、语言切换或 SEO 输出。', 'qiling' ),
                ),
                array(
                    'type'    => 'note',
                    'content' => __( '第三方代码管理、Cookie 控制、国际排版增强、SEO 基础检查、上线检查清单和配置快照已完成基础接入；所有能力默认按各自开关控制。', 'qiling' ),
                    'style'   => 'color:#2563eb;font-weight:600;',
                ),

                array(
                    'type'  => 'section',
                    'title' => __( '上线前检查', 'qiling' ),
                    'desc'  => __( '只读汇总当前国际化基础配置，帮助确认是否会输出新代码、是否需要 Cookie 同意、是否存在多语言 SEO 复核项。', 'qiling' ),
                ),
                array(
                    'type'     => 'custom',
                    'callback' => array( $this, 'render_international_launch_readiness_field' ),
                ),
                array(
                    'type'  => 'section',
                    'title' => __( '配置快照', 'qiling' ),
                    'desc'  => __( '只读生成一份脱敏交付摘要，便于上线前复制给运营、开发或 AI 排查。不会保存新字段，也不会包含第三方代码正文。', 'qiling' ),
                ),
                array(
                    'type'     => 'custom',
                    'callback' => array( $this, 'render_international_delivery_snapshot_field' ),
                ),

                array(
                    'type'  => 'section',
                    'title' => __( '第三方代码', 'qiling' ),
                    'desc'  => __( '用于统一粘贴海外统计、广告转化、客服和追踪脚本。只影响本页新增字段，不接管原有百度统计或自定义 JS。', 'qiling' ),
                ),
                array(
                    'id'           => 'international_third_party_code_enable',
                    'type'         => 'checkbox',
                    'label'        => __( '启用第三方代码管理', 'qiling' ),
                    'desc'         => __( '默认关闭。开启后才会输出下面已启用的代码组。', 'qiling' ),
                    'default'      => '',
                    'search_terms' => array( 'Google Analytics', 'GTM', 'Google Ads', 'Meta Pixel', 'TikTok Pixel', '统计代码', '广告代码' ),
                ),
                array(
                    'type'    => 'note',
                    'content' => __( '风险提示：这里粘贴的第三方代码会按配置在前台页面执行。请只使用可信平台提供的统计、广告或客服代码，避免粘贴来源不明的脚本。', 'qiling' ),
                    'style'   => 'color:#92400e;background:#fffbeb;border-left:4px solid #f59e0b;padding:10px 12px;border-radius:4px;',
                ),
                array( 'type' => 'note', 'content' => __( '提示：Google Analytics、GTM、Google Ads、Meta Pixel、TikTok Pixel 等可直接粘贴对应平台提供的代码。验证码、支付、短信和邮件服务请在各自设置中配置。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '头部代码', 'qiling' ), 'desc' => __( '适合放站点验证、需要尽早加载的统计基础代码。', 'qiling' ) ),
                array( 'id' => 'international_code_head_enable', 'type' => 'checkbox', 'label' => __( '启用头部代码', 'qiling' ), 'default' => '' ),
                array( 'id' => 'international_code_head_content', 'type' => 'textarea', 'label' => __( '代码内容', 'qiling' ), 'desc' => __( '支持粘贴 script、noscript、iframe、img、link、meta 等常见第三方代码。', 'qiling' ), 'attrs' => array( 'rows' => '8', 'spellcheck' => 'false' ) ),
                array( 'id' => 'international_code_head_position', 'type' => 'select', 'label' => __( '输出位置', 'qiling' ), 'default' => 'head', 'choices' => array( 'head' => 'wp_head', 'footer' => 'wp_footer' ) ),
                array( 'id' => 'international_code_head_category', 'type' => 'select', 'label' => __( 'Cookie 分类', 'qiling' ), 'default' => 'necessary', 'choices' => $international_cookie_category_choices, 'desc' => __( '必要分类会直接输出；其他分类会等待访客授权。头部代码默认按站点验证等必要代码处理。', 'qiling' ) ),
                array( 'id' => 'international_code_head_require_consent', 'type' => 'checkbox', 'label' => __( '访客同意后加载', 'qiling' ), 'desc' => __( '非必要分类会等待访客授权，实际规则以上方 Cookie 分类为准。', 'qiling' ), 'default' => '' ),

                array( 'type' => 'section', 'title' => __( '底部代码', 'qiling' ), 'desc' => __( '适合放延迟加载、客服、热力图或不要求首屏执行的代码。', 'qiling' ) ),
                array( 'id' => 'international_code_footer_enable', 'type' => 'checkbox', 'label' => __( '启用底部代码', 'qiling' ), 'default' => '' ),
                array( 'id' => 'international_code_footer_content', 'type' => 'textarea', 'label' => __( '代码内容', 'qiling' ), 'desc' => __( '默认输出到 wp_footer。', 'qiling' ), 'attrs' => array( 'rows' => '8', 'spellcheck' => 'false' ) ),
                array( 'id' => 'international_code_footer_position', 'type' => 'select', 'label' => __( '输出位置', 'qiling' ), 'default' => 'footer', 'choices' => array( 'head' => 'wp_head', 'footer' => 'wp_footer' ) ),
                array( 'id' => 'international_code_footer_category', 'type' => 'select', 'label' => __( 'Cookie 分类', 'qiling' ), 'default' => 'custom', 'choices' => $international_cookie_category_choices, 'desc' => __( '非必要分类会在访客授权后再执行。', 'qiling' ) ),
                array( 'id' => 'international_code_footer_require_consent', 'type' => 'checkbox', 'label' => __( '访客同意后加载', 'qiling' ), 'desc' => __( '非必要分类会等待访客授权，实际规则以上方 Cookie 分类为准。', 'qiling' ), 'default' => '' ),

                array( 'type' => 'section', 'title' => __( '统计代码', 'qiling' ), 'desc' => __( '适合放 Google Analytics、Microsoft Clarity 等统计代码。', 'qiling' ) ),
                array( 'id' => 'international_code_analytics_enable', 'type' => 'checkbox', 'label' => __( '启用统计代码', 'qiling' ), 'default' => '' ),
                array( 'id' => 'international_code_analytics_content', 'type' => 'textarea', 'label' => __( '代码内容', 'qiling' ), 'attrs' => array( 'rows' => '8', 'spellcheck' => 'false' ) ),
                array( 'id' => 'international_code_analytics_position', 'type' => 'select', 'label' => __( '输出位置', 'qiling' ), 'default' => 'head', 'choices' => array( 'head' => 'wp_head', 'footer' => 'wp_footer' ) ),
                array( 'id' => 'international_code_analytics_category', 'type' => 'select', 'label' => __( 'Cookie 分类', 'qiling' ), 'default' => 'statistics', 'choices' => $international_cookie_category_choices, 'desc' => __( 'GA4、Clarity、Plausible 等默认归入统计。', 'qiling' ) ),
                array( 'id' => 'international_code_analytics_require_consent', 'type' => 'checkbox', 'label' => __( '访客同意后加载', 'qiling' ), 'desc' => __( '统计代码默认归入统计分类，并按 Cookie 分类授权执行。', 'qiling' ), 'default' => '' ),

                array( 'type' => 'section', 'title' => __( '广告转化代码', 'qiling' ), 'desc' => __( '适合放 Google Ads、Meta Pixel、TikTok Pixel 等广告追踪或转化代码。', 'qiling' ) ),
                array( 'id' => 'international_code_ads_enable', 'type' => 'checkbox', 'label' => __( '启用广告转化代码', 'qiling' ), 'default' => '' ),
                array( 'id' => 'international_code_ads_content', 'type' => 'textarea', 'label' => __( '代码内容', 'qiling' ), 'attrs' => array( 'rows' => '8', 'spellcheck' => 'false' ) ),
                array( 'id' => 'international_code_ads_position', 'type' => 'select', 'label' => __( '输出位置', 'qiling' ), 'default' => 'footer', 'choices' => array( 'head' => 'wp_head', 'footer' => 'wp_footer' ) ),
                array( 'id' => 'international_code_ads_category', 'type' => 'select', 'label' => __( 'Cookie 分类', 'qiling' ), 'default' => 'advertising', 'choices' => $international_cookie_category_choices, 'desc' => __( 'Google Ads、Meta Pixel、TikTok Pixel 等默认归入广告。', 'qiling' ) ),
                array( 'id' => 'international_code_ads_require_consent', 'type' => 'checkbox', 'label' => __( '访客同意后加载', 'qiling' ), 'desc' => __( '广告代码默认归入广告分类，并按 Cookie 分类授权执行。', 'qiling' ), 'default' => '' ),

                array( 'type' => 'section', 'title' => __( '自定义代码', 'qiling' ), 'desc' => __( '用于其他平台提供的第三方代码。', 'qiling' ) ),
                array( 'id' => 'international_code_custom_enable', 'type' => 'checkbox', 'label' => __( '启用自定义代码', 'qiling' ), 'default' => '' ),
                array( 'id' => 'international_code_custom_content', 'type' => 'textarea', 'label' => __( '代码内容', 'qiling' ), 'attrs' => array( 'rows' => '8', 'spellcheck' => 'false' ) ),
                array( 'id' => 'international_code_custom_position', 'type' => 'select', 'label' => __( '输出位置', 'qiling' ), 'default' => 'footer', 'choices' => array( 'head' => 'wp_head', 'footer' => 'wp_footer' ) ),
                array( 'id' => 'international_code_custom_category', 'type' => 'select', 'label' => __( 'Cookie 分类', 'qiling' ), 'default' => 'custom', 'choices' => $international_cookie_category_choices, 'desc' => __( '不确定用途的第三方代码默认归入自定义，建议上线前复核。', 'qiling' ) ),
                array( 'id' => 'international_code_custom_require_consent', 'type' => 'checkbox', 'label' => __( '访客同意后加载', 'qiling' ), 'desc' => __( '非必要分类会等待访客授权，实际规则以上方 Cookie 分类为准。', 'qiling' ), 'default' => '' ),

                array(
                    'type'  => 'section',
                    'title' => __( 'Cookie / 隐私', 'qiling' ),
                    'desc'  => __( '用于控制需要同意后加载的第三方代码。不替换现有页脚隐私提示条。', 'qiling' ),
                ),
                array(
                    'id'      => 'international_cookie_notice_enable',
                    'type'    => 'checkbox',
                    'label'   => __( '启用国际化 Cookie 提示', 'qiling' ),
                    'desc'    => __( '默认关闭。开启后显示独立 Cookie 提示，并按 Cookie 分类控制新增第三方代码。', 'qiling' ),
                    'default' => '',
                ),
                array(
                    'id'      => 'international_cookie_notice_text',
                    'type'    => 'textarea',
                    'label'   => __( '提示内容', 'qiling' ),
                    'default' => __( '本网站使用 Cookie 和类似技术来提升访问体验。', 'qiling' ),
                    'desc'    => __( '默认中文文案；只影响国际化 Cookie 提示，不影响原页脚隐私提示条。', 'qiling' ),
                    'attrs'   => array( 'rows' => '3' ),
                ),
                array(
                    'id'      => 'international_cookie_region_preset',
                    'type'    => 'select',
                    'label'   => __( '地区预设', 'qiling' ),
                    'default' => 'cross_border',
                    'choices' => $international_cookie_region_choices,
                    'desc'    => __( '用于后台风险提示和前台默认交互口径。跨境站按欧盟严格模式处理；美国预设在自定义设置中默认勾选非必要分类，但仍允许访客拒绝。', 'qiling' ),
                ),
                array(
                    'id'      => 'international_cookie_consent_version',
                    'type'    => 'text',
                    'label'   => __( '同意版本号', 'qiling' ),
                    'default' => '2.0',
                    'desc'    => __( '修改隐私政策或 Cookie 用途后递增版本号，例如 2.1。前台检测到旧版本授权会重新弹出设置。', 'qiling' ),
                    'attrs'   => array( 'placeholder' => '2.0', 'class' => 'small-text', 'maxlength' => 32 ),
                ),
                array(
                    'id'      => 'international_cookie_accept_text',
                    'type'    => 'text',
                    'label'   => __( '接受全部按钮文字', 'qiling' ),
                    'default' => __( '接受全部', 'qiling' ),
                ),
                array(
                    'id'      => 'international_cookie_reject_text',
                    'type'    => 'text',
                    'label'   => __( '拒绝按钮文字', 'qiling' ),
                    'default' => __( '拒绝非必要', 'qiling' ),
                ),
                array(
                    'id'      => 'international_cookie_customize_text',
                    'type'    => 'text',
                    'label'   => __( '自定义按钮文字', 'qiling' ),
                    'default' => __( '自定义设置', 'qiling' ),
                ),
                array(
                    'id'      => 'international_cookie_save_text',
                    'type'    => 'text',
                    'label'   => __( '保存按钮文字', 'qiling' ),
                    'default' => __( '保存设置', 'qiling' ),
                ),
                array(
                    'id'      => 'international_cookie_policy_link_text',
                    'type'    => 'text',
                    'label'   => __( '隐私政策链接文字', 'qiling' ),
                    'default' => __( '了解更多', 'qiling' ),
                ),
                array(
                    'id'    => 'international_cookie_policy_url',
                    'type'  => 'text',
                    'label' => __( '隐私政策链接', 'qiling' ),
                    'desc'  => __( '可填写隐私政策页面 URL；留空则前台不显示链接。', 'qiling' ),
                    'attrs' => array( 'placeholder' => home_url( '/privacy-policy/' ) ),
                ),
                array(
                    'id'      => 'international_cookie_notice_position',
                    'type'    => 'select',
                    'label'   => __( '展示位置', 'qiling' ),
                    'default' => 'bottom_center',
                    'choices' => $international_cookie_position_choices,
                ),
                array(
                    'id'      => 'international_cookie_footer_button_enable',
                    'type'    => 'checkbox',
                    'label'   => __( '显示页脚 Cookie 设置按钮', 'qiling' ),
                    'desc'    => __( '开启后在页脚输出一个“Cookie 设置”按钮，访客可随时重新打开分类授权面板。', 'qiling' ),
                    'default' => '',
                ),
                array(
                    'id'      => 'international_cookie_footer_button_text',
                    'type'    => 'text',
                    'label'   => __( '页脚按钮文字', 'qiling' ),
                    'default' => __( 'Cookie 设置', 'qiling' ),
                ),
                array(
                    'type'    => 'note',
                    'content' => __( '短代码：[qiling_cookie_settings] 可在任意页面输出重新打开 Cookie 设置的按钮。', 'qiling' ),
                ),

                array(
                    'type'  => 'section',
                    'title' => __( '国际排版', 'qiling' ),
                    'desc'  => __( '用于增强英文长词、日韩正文行高和 RTL 基础方向。默认关闭，不改变现有中文样式。', 'qiling' ),
                ),
                array(
                    'id'      => 'international_typography_enable',
                    'type'    => 'checkbox',
                    'label'   => __( '启用国际排版增强', 'qiling' ),
                    'desc'    => __( '默认关闭。开启后才会加载国际排版增强样式并添加 body class。', 'qiling' ),
                    'default' => '',
                ),
                array(
                    'id'      => 'international_typography_mode',
                    'type'    => 'select',
                    'label'   => __( '排版模式', 'qiling' ),
                    'default' => 'auto',
                    'choices' => array(
                        'auto' => __( '自动', 'qiling' ),
                        'zh'   => __( '中文默认', 'qiling' ),
                        'en'   => __( '英文 / Latin', 'qiling' ),
                        'ja'   => __( '日文', 'qiling' ),
                        'ko'   => __( '韩文', 'qiling' ),
                        'rtl'  => __( 'RTL 基础', 'qiling' ),
                    ),
                    'desc'    => __( '自动模式会优先读取当前前台语言和 locale；也可以手动指定英文、日文、韩文或 RTL 基础。', 'qiling' ),
                ),

                array(
                    'type'  => 'section',
                    'title' => __( 'SEO 基础检查', 'qiling' ),
                    'desc'  => __( '只读检查语言列表、语言包、hreflang、x-default、schema 币种等。不修改 SEO 输出。', 'qiling' ),
                ),
                array(
                    'type'     => 'custom',
                    'callback' => array( $this, 'render_international_seo_diagnostics_field' ),
                ),
                array(
                    'type'     => 'custom',
                    'callback' => array( $this, 'render_international_seo_site_scan_field' ),
                ),
            ),

            // ========== 语言选项卡 ==========
            'translate' => array(
                array( 'type' => 'section', 'title' => __( '前台语言切换模式', 'qiling' ), 'desc' => __( 'translate.js 机翻模式与多语言内容模式二选一。多语言内容模式适合配合多语言文章插件和独立 URL 使用。', 'qiling' ) ),
                array(
                    'id' => 'frontend_language_switch_mode',
                    'type' => 'select',
                    'label' => __( '切换模式', 'qiling' ),
                    'choices' => array(
                        '' => __( '关闭', 'qiling' ),
                        'translate_js' => __( 'translate.js 机翻模式', 'qiling' ),
                        'multilingual_content' => __( '多语言内容模式', 'qiling' ),
                    ),
                    'desc' => __( '关闭时前台不显示语言切换；多语言内容模式会根据 URL 简码切换主题语言包和页面语言上下文。', 'qiling' ),
                ),

                array( 'type' => 'section', 'title' => __( 'translate.js 机翻模式', 'qiling' ), 'desc' => __( '仅在切换模式选择“translate.js 机翻模式”时生效。', 'qiling' ) ),
                array( 'id' => 'theme_language', 'type' => 'select', 'label' => __( '前端翻译源语言', 'qiling' ), 'choices' => array( 'zh_CN' => __( '简体中文', 'qiling' ), 'en_US' => 'English' ), 'desc' => __( '仅用于 translate.js 判断页面原始语言，不会切换 WordPress 或主题语言包。', 'qiling' ) ),
                array(
                    'id' => 'translate_js_url',
                    'type' => 'text',
                    'label' => __( 'translate.js 地址', 'qiling' ),
                    'desc' => __( '留空使用本地 translate/translate.js，也支持填写完整 URL 或站内相对路径', 'qiling' ),
                    'attrs' => array(
                        'placeholder' => 'https://cdn.example.com/translate.js 或 /wp-content/uploads/translate.js',
                    ),
                ),

                array( 'type' => 'section', 'title' => __( '语言列表', 'qiling' ), 'desc' => __( '配置前台可切换的语言，语言简码参考 translate.js 文档', 'qiling' ) ),
                array(
                    'id'     => 'translate_languages',
                    'type'   => 'repeater',
                    'label'  => __( '语言配置', 'qiling' ),
                    'fields' => array(
                        array( 'id' => 'name', 'label' => __( '语言名称', 'qiling' ), 'type' => 'text' ),
                        array( 'id' => 'code', 'label' => __( '语言简码', 'qiling' ), 'type' => 'text' ),
                        array( 'id' => 'icon', 'label' => __( '图标（可选）', 'qiling' ), 'type' => 'text' ),
                    ),
                    'default' => array(
                        array( 'name' => __( '简体中文', 'qiling' ), 'code' => 'chinese_simplified', 'icon' => '' ),
                        array( 'name' => __( '繁体中文', 'qiling' ), 'code' => 'chinese_traditional', 'icon' => '' ),
                        array( 'name' => 'English', 'code' => 'english', 'icon' => '' ),
                    ),
                ),
                array( 'type' => 'note', 'content' => __( '常用语言简码：chinese_simplified（简体中文）、chinese_traditional（繁体中文）、english（英语）、korean（韩语）、japanese（日语）', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '多语言内容模式', 'qiling' ), 'desc' => __( '适合配合真实多语言文章/页面使用。默认语言走根路径，其他语言走 /语言简码/ 路径。', 'qiling' ) ),
                array(
                    'id' => 'multilingual_default_lang',
                    'type' => 'select',
                    'label' => __( '默认前台语言', 'qiling' ),
                    'choices' => $this->get_multilingual_default_lang_choices(),
                    'default' => 'zh',
                    'desc' => __( '默认语言不加前缀，例如 /；其他语言使用 /en/ 这样的前缀。', 'qiling' ),
                ),
                array(
                    'id'     => 'multilingual_languages',
                    'type'   => 'repeater',
                    'label'  => __( '前台语言配置', 'qiling' ),
                    'fields' => array(
                        array( 'id' => 'name', 'label' => __( '语言名称', 'qiling' ), 'type' => 'text' ),
                        array( 'id' => 'code', 'label' => __( 'URL 简码', 'qiling' ), 'type' => 'text' ),
                        array( 'id' => 'locale', 'label' => __( '语言包 Locale', 'qiling' ), 'type' => 'text' ),
                        array( 'id' => 'icon', 'label' => __( '图标（可选）', 'qiling' ), 'type' => 'text' ),
                    ),
                    'default' => array(
                        array( 'name' => __( '简体中文', 'qiling' ), 'code' => 'zh', 'locale' => 'zh_CN', 'icon' => 'CN' ),
                        array( 'name' => __( '繁体中文', 'qiling' ), 'code' => 'zh-tw', 'locale' => 'zh_TW', 'icon' => 'TW' ),
                        array( 'name' => 'English', 'code' => 'en', 'locale' => 'en_US', 'icon' => 'US' ),
                        array( 'name' => __( '日文', 'qiling' ), 'code' => 'jp', 'locale' => 'ja_JP', 'icon' => 'JP' ),
                        array( 'name' => __( '韩文', 'qiling' ), 'code' => 'ko', 'locale' => 'ko_KR', 'icon' => 'KR' ),
                        array( 'name' => __( '法文', 'qiling' ), 'code' => 'fr', 'locale' => 'fr_FR', 'icon' => 'FR' ),
                        array( 'name' => __( '德文', 'qiling' ), 'code' => 'de', 'locale' => 'de_DE', 'icon' => 'DE' ),
                        array( 'name' => __( '西班牙文', 'qiling' ), 'code' => 'es', 'locale' => 'es_ES', 'icon' => 'ES' ),
                    ),
                ),
                array( 'type' => 'note', 'content' => __( '推荐配置：中文使用 zh / zh_CN，英文使用 en / en_US，日文使用 jp / ja_JP，韩文使用 ko / ko_KR，法文使用 fr / fr_FR，德文使用 de / de_DE，西班牙文使用 es / es_ES，俄文使用 ru / ru_RU。主题会根据当前 URL 前缀切换前台语言包。', 'qiling' ) ),
            ),

            // ========== 公告选项卡 ==========
            'announcement' => array(
                array( 'type' => 'section', 'title' => __( '公告设置', 'qiling' ), 'desc' => __( '配置全站公告弹窗，支持多种类型和显示条件', 'qiling' ) ),
                array( 'id' => 'announcement_enable', 'type' => 'checkbox', 'label' => __( '启用公告', 'qiling' ), 'desc' => __( '开启后前台将显示公告弹窗', 'qiling' ) ),
                array( 'id' => 'announcement_type', 'type' => 'select', 'label' => __( '公告类型', 'qiling' ), 'choices' => array(
                    'normal'        => __( '普通公告（弹窗）', 'qiling' ),
                    'marketing'     => __( '营销活动（弹窗）', 'qiling' ),
                    'image'         => __( '图片公告（弹窗）', 'qiling' ),
                    'image_text'    => __( '图文混排（弹窗）', 'qiling' ),
                    'bottom_banner' => __( '底部横幅（固定在底部）', 'qiling' ),
                ), 'desc' => __( '弹窗类型居中显示，底部横幅固定在页面底部', 'qiling' ), 'default' => 'normal' ),

                array( 'type' => 'section', 'title' => __( '公告内容', 'qiling' ) ),
                array( 'id' => 'announcement_title', 'type' => 'text', 'label' => __( '公告标题', 'qiling' ) ),
                array( 'id' => 'announcement_content', 'type' => 'textarea', 'label' => __( '公告内容', 'qiling' ), 'desc' => __( '支持HTML标签', 'qiling' ) ),
                array( 'id' => 'announcement_image', 'type' => 'image', 'label' => __( '公告图片', 'qiling' ), 'desc' => __( '图片公告、图文混排和底部横幅类型可上传图片', 'qiling' ) ),
                array( 'id' => 'announcement_btn_text', 'type' => 'text', 'label' => __( '按钮文字', 'qiling' ), 'desc' => __( '底部横幅类型无需填写按钮文字', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如：立即查看', 'qiling' ) ) ),
                array( 'id' => 'announcement_btn_url', 'type' => 'text', 'label' => __( '公告/按钮链接', 'qiling' ), 'desc' => __( '底部横幅类型填写后整个横幅可点击跳转', 'qiling' ), 'attrs' => array( 'placeholder' => 'https://' ) ),

                array( 'type' => 'section', 'title' => __( '普通/图片/图文公告按钮样式', 'qiling' ), 'desc' => __( '自定义普通公告、图片公告、图文混排公告的按钮颜色，支持渐变色', 'qiling' ) ),
                array( 'id' => 'announcement_normal_btn_bg', 'type' => 'text', 'label' => __( '按钮背景色', 'qiling' ), 'desc' => __( '留空使用主题主色调，支持纯色（如 #2563eb）或渐变色（如 linear-gradient(135deg, #667eea 0%, #764ba2 100%)）', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如: #2563eb 或 linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'qiling' ) ) ),
                array( 'id' => 'announcement_normal_btn_color', 'type' => 'text', 'label' => __( '按钮文字颜色', 'qiling' ), 'desc' => __( '留空使用白色 #fff', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如: #ffffff', 'qiling' ) ) ),
                array( 'id' => 'announcement_normal_btn_hover_bg', 'type' => 'text', 'label' => __( '按钮悬停背景色', 'qiling' ), 'desc' => __( '留空自动使用背景色的深色版本，支持纯色或渐变色', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如: #1d4ed8 或渐变色', 'qiling' ) ) ),

                array( 'type' => 'section', 'title' => __( '营销活动公告样式', 'qiling' ), 'desc' => __( '自定义营销活动公告的窗口背景和按钮颜色，支持渐变色', 'qiling' ) ),
                array( 'id' => 'announcement_marketing_modal_bg', 'type' => 'text', 'label' => __( '窗口背景色', 'qiling' ), 'desc' => __( '留空使用当前页面/模板预设的公告配色；无预设时使用明亮红色系兜底，支持纯色或渐变色', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如: linear-gradient(135deg, #ef4444 0%, #f43f5e 100%)', 'qiling' ) ) ),
                array( 'id' => 'announcement_marketing_btn_bg', 'type' => 'text', 'label' => __( '按钮背景色', 'qiling' ), 'desc' => __( '留空使用白色 #fff', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如: #ffffff', 'qiling' ) ) ),
                array( 'id' => 'announcement_marketing_btn_color', 'type' => 'text', 'label' => __( '按钮文字颜色', 'qiling' ), 'desc' => __( '留空跟随当前页面/模板预设；无预设时使用醒目的红色 #dc2626', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如: #dc2626', 'qiling' ) ) ),
                array( 'id' => 'announcement_marketing_btn_hover_bg', 'type' => 'text', 'label' => __( '按钮悬停背景色', 'qiling' ), 'desc' => __( '留空使用浅灰色 #f8fafc', 'qiling' ), 'attrs' => array( 'placeholder' => __( '如: #f8fafc', 'qiling' ) ) ),

                array( 'type' => 'section', 'title' => __( '显示设置', 'qiling' ) ),
                array( 'id' => 'announcement_display_on', 'type' => 'select', 'label' => __( '显示页面', 'qiling' ), 'choices' => array(
                    'all'        => __( '全站显示', 'qiling' ),
                    'homepage'   => __( '仅首页', 'qiling' ),
                    'pages'      => __( '指定页面', 'qiling' ),
                    'posts'      => __( '指定文章', 'qiling' ),
                    'categories' => __( '指定分类', 'qiling' ),
                ), 'default' => 'all' ),
                array( 'id' => 'announcement_page_ids', 'type' => 'text', 'label' => __( '页面ID', 'qiling' ), 'row_class' => 'ann-pages-row', 'row_style' => 'display:none;', 'attrs' => array( 'placeholder' => __( '多个ID用英文逗号分隔，如: 1,2,3', 'qiling' ) ) ),
                array( 'id' => 'announcement_post_ids', 'type' => 'text', 'label' => __( '文章ID', 'qiling' ), 'row_class' => 'ann-posts-row', 'row_style' => 'display:none;', 'attrs' => array( 'placeholder' => __( '多个ID用英文逗号分隔，如: 1,2,3', 'qiling' ) ) ),
                array(
                    'id'        => 'announcement_category_ids',
                    'type'      => 'checkbox_group',
                    'label'     => __( '选择分类', 'qiling' ),
                    'choices'   => $cat_id_options,
                    'row_class' => 'ann-cats-row',
                    'row_style' => 'display:none;',
                    'args'      => array(
                        'label_style' => 'display:inline-block;margin-right:15px;margin-bottom:5px;',
                    ),
                ),
                array( 'id' => 'announcement_frequency', 'type' => 'select', 'label' => __( '显示频率', 'qiling' ), 'choices' => array(
                    'always'   => __( '每次访问都显示', 'qiling' ),
                    'once_day' => __( '每天只显示一次', 'qiling' ),
                ), 'default' => 'always' ),
                array( 'id' => 'announcement_allow_dismiss', 'type' => 'checkbox', 'label' => __( '“今日不再显示”选项', 'qiling' ), 'desc' => __( '允许用户勾选“今日不再显示”（仅在“每次访问都显示”模式下有效）', 'qiling' ), 'default' => '1' ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_announcement_display_script' ) ),
            ),

            // ========== 优化选项卡 ==========
            'optimize' => array(
                array( 'type' => 'section', 'title' => __( '开发调试', 'qiling' ), 'desc' => __( '临时调试功能，用于分析网站性能', 'qiling' ) ),
                array( 'id' => 'debug_mode', 'type' => 'checkbox', 'label' => __( '启用调试模式', 'qiling' ), 'desc' => __( '在前台底部显示调试信息（SQL查询次数、页面加载时间、内存使用、缓存状态），并允许受控主题日志写入（敏感字段会脱敏）。', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '提示：开启后所有访客均可见！调试完毕后请立即关闭', 'qiling' ), 'style' => 'color:#ef4444;' ),

                array( 'type' => 'section', 'title' => __( '缓存管理', 'qiling' ), 'desc' => __( '管理主题资源文件的版本号，解决浏览器缓存问题', 'qiling' ) ),
                array( 'id' => 'assets_version', 'type' => 'text', 'label' => __( '资源版本号', 'qiling' ), 'desc' => sprintf( __( '自定义 CSS/JS 文件的版本号，修改后浏览器将重新加载资源文件。留空使用主题版本号 (%s)', 'qiling' ), QILING_VERSION ), 'attrs' => array( 'placeholder' => __( '留空使用主题版本号', 'qiling' ) ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_assets_refresh_field' ) ),

                array( 'type' => 'section', 'title' => __( 'WordPress 优化设置', 'qiling' ), 'desc' => __( '常用的 WordPress 性能和安全优化选项；涉及 REST、编辑器、自动更新等核心生态能力的开关默认关闭，请按需启用。', 'qiling' ) ),
                array( 'id' => 'runtime_compat_safe_mode', 'type' => 'checkbox', 'label' => __( '兼容回滚模式', 'qiling' ), 'desc' => __( '临时停用高兼容风险优化（REST 限制、应用密码、自动更新控制、区块编辑器/样式移除等），保留原设置值，排障后可取消。', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '兼容提示：如果小程序、区块编辑器、第三方插件或外部 API 客户端异常，先开启“兼容回滚模式”并保存，即可快速恢复 WordPress 默认行为。', 'qiling' ), 'style' => 'color:#b45309;' ),
                array( 'id' => 'disable_emoji', 'type' => 'checkbox', 'label' => __( '禁用 Emoji 脚本', 'qiling' ), 'desc' => __( '移除 WordPress 自带的 Emoji 表情脚本，提升页面加载速度', 'qiling' ) ),
                array( 'id' => 'disable_embeds', 'type' => 'checkbox', 'label' => __( '禁用 oEmbed', 'qiling' ), 'desc' => __( '禁用 WordPress 自动嵌入功能；可能影响文章嵌入、区块嵌入和依赖 oEmbed 的插件。', 'qiling' ) ),
                array( 'id' => 'disable_xmlrpc', 'type' => 'checkbox', 'label' => __( '禁用 XML-RPC', 'qiling' ), 'desc' => __( '禁用 XML-RPC 接口；可能影响旧版移动端客户端、Jetpack 或远程发布工具。', 'qiling' ) ),
                array( 'id' => 'remove_wp_version', 'type' => 'checkbox', 'label' => __( '隐藏 WP 版本号', 'qiling' ), 'desc' => __( '从页面源码中移除 WordPress 版本信息，提升安全性', 'qiling' ) ),
                array( 'id' => 'disable_rest_api', 'type' => 'checkbox', 'label' => __( '限制 REST API', 'qiling' ), 'desc' => __( '仅允许登录用户访问 REST API；可能影响小程序、区块编辑器、站点健康和外部 API 客户端，请先配置白名单。', 'qiling' ) ),
                array( 'id' => 'runtime_rest_whitelist_prefixes', 'type' => 'textarea', 'label' => __( 'REST API 白名单', 'qiling' ), 'desc' => __( '每行一个允许匿名访问的 REST 路径前缀，必须以 /wp-json/ 开头。默认已保留 /wp-json/qivoting/ 和 /wp-json/qibbs/。', 'qiling' ), 'attrs' => array( 'rows' => 4, 'placeholder' => "/wp-json/qivoting/\n/wp-json/qibbs/\n/wp-json/my-plugin/v1/" ) ),
                array( 'id' => 'restrict_rest_api_important', 'type' => 'checkbox', 'label' => __( '仅屏蔽重要接口', 'qiling' ), 'desc' => __( '对外开放 REST API，但屏蔽默认敏感接口（wp/v2、oembed/1.0）；可能影响依赖默认内容接口的前端应用。', 'qiling' ) ),
                array( 'id' => 'restrict_rest_users', 'type' => 'checkbox', 'label' => __( '仅屏蔽 REST 用户端点', 'qiling' ), 'desc' => __( '保留大部分 REST 接口，仅禁止游客访问 /wp/v2/users* 端点', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '如果同时开启“限制 REST API”，将以“仅登录访问”为准', 'qiling' ) ),
                array( 'id' => 'remove_shortlink', 'type' => 'checkbox', 'label' => __( '移除短链接', 'qiling' ), 'desc' => __( '从 head 中移除 shortlink 标签', 'qiling' ) ),
                array( 'id' => 'remove_rsd_wlw', 'type' => 'checkbox', 'label' => __( '移除 RSD/WLW', 'qiling' ), 'desc' => __( '移除 RSD 和 Windows Live Writer 链接', 'qiling' ) ),
                array( 'id' => 'disable_pingback', 'type' => 'checkbox', 'label' => __( '禁用 Pingback/Trackback', 'qiling' ), 'desc' => __( '禁用 Pingback 和 Trackback 功能，减少垃圾评论和 DDoS 攻击风险', 'qiling' ) ),
                array( 'id' => 'disable_revisions', 'type' => 'checkbox', 'label' => __( '限制修订版本', 'qiling' ), 'desc' => __( '限制文章修订版本数量为 3 个，减少数据库占用', 'qiling' ) ),
                array( 'id' => 'disable_gutenberg', 'type' => 'checkbox', 'label' => __( '禁用 Gutenberg', 'qiling' ), 'desc' => __( '禁用区块编辑器并恢复经典编辑器；如需保留某些文章类型，请在下方白名单中添加。', 'qiling' ) ),
                array( 'id' => 'disable_wp_core_ai', 'type' => 'checkbox', 'label' => __( '禁用 WP 7.0 原生 AI', 'qiling' ), 'desc' => __( '彻底禁用 WordPress 7.0 核心自带的 AI 功能并拦截对应的 Connectors 后台页面，提升后台访问速度。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'disable_block_widgets', 'type' => 'checkbox', 'label' => __( '禁用区块小工具', 'qiling' ), 'desc' => __( '使用经典小工具界面；如果插件依赖区块小工具，请在下方白名单保留 widgets。', 'qiling' ) ),
                array( 'id' => 'runtime_block_editor_allowlist', 'type' => 'textarea', 'label' => __( '区块编辑器白名单', 'qiling' ), 'desc' => __( '每行一个允许继续使用区块编辑器的上下文。可填写 post_type:product、post_type:page、post_type:* 或 widgets。留空时文章和页面也会使用经典编辑器。', 'qiling' ), 'attrs' => array( 'rows' => 3, 'placeholder' => "post_type:product\nwidgets" ) ),
                array( 'id' => 'hide_admin_bar_for_users', 'type' => 'checkbox', 'label' => __( '隐藏普通用户管理栏', 'qiling' ), 'desc' => __( '普通用户登录后不显示前台顶部 Admin Bar 工具栏，管理员不受影响', 'qiling' ) ),
                array( 'id' => 'admin_disable_wp7_blue_scheme', 'type' => 'checkbox', 'label' => __( '禁用 WordPress 7.0 蓝色后台配色', 'qiling' ), 'default' => '1', 'desc' => __( '开启后把 WordPress 7.0 默认的 Modern/蓝色后台配色回退为经典 Fresh；用户主动选择的其他后台配色不会被覆盖。', 'qiling' ) ),
                array( 'id' => 'search_rewrite', 'type' => 'checkbox', 'label' => __( '搜索伪静态', 'qiling' ), 'desc' => __( '将搜索链接改为伪静态格式，如 /search/关键词/ 替代 /?s=关键词', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '启用后自动刷新固定链接规则，有利于 SEO 优化', 'qiling' ), 'style' => 'color:#10b981;' ),

                array( 'type' => 'section', 'title' => __( '原生智能搜索增强', 'qiling' ), 'desc' => __( '接管前台搜索输入体验，通过 Ajax 返回文章、页面和产品卡片，并保留完整搜索结果页。', 'qiling' ) ),
                array( 'id' => 'search_default_mode', 'type' => 'select', 'label' => __( '默认搜索模式', 'qiling' ), 'choices' => $search_mode_choices, 'default' => 'all', 'desc' => __( '影视搜索仅在启灵播放器及视频元数据表可用时显示；能力失效后自动回退到综合搜索。', 'qiling' ) ),
                array( 'id' => 'search_mode_switch_enable', 'type' => 'checkbox', 'label' => __( '允许访客切换搜索模式', 'qiling' ), 'default' => '1', 'desc' => __( '开启后，搜索结果页允许访客在管理员启用的模式之间切换。', 'qiling' ) ),
                array( 'id' => 'search_frontend_modes', 'type' => 'checkbox_group', 'label' => __( '前台可用搜索模式', 'qiling' ), 'choices' => $search_mode_choices, 'default' => array_keys( $search_mode_choices ), 'desc' => __( '插件注册的新搜索模式会自动加入候选列表；至少保留一种可用模式。', 'qiling' ) ),
                array( 'id' => 'search_results_per_page', 'type' => 'select', 'label' => __( '搜索结果数量', 'qiling' ), 'choices' => array( '12' => __( '每页 12 条', 'qiling' ), '18' => __( '每页 18 条', 'qiling' ), '24' => __( '每页 24 条', 'qiling' ), '30' => __( '每页 30 条', 'qiling' ) ), 'default' => '18' ),
                array( 'id' => 'search_hot_keywords', 'type' => 'text', 'label' => __( '热门搜索词', 'qiling' ), 'default' => '', 'attrs' => array( 'placeholder' => __( '热播电影,最新电视剧,热门动漫', 'qiling' ) ), 'desc' => __( '使用半角或全角逗号分隔，供搜索结果和影视搜索模块调用。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_enable', 'type' => 'checkbox', 'label' => __( '启用实时搜索下拉', 'qiling' ), 'default' => '1', 'desc' => __( '开启后，头部搜索框、搜索弹层和搜索结果页搜索框会显示实时搜索结果。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_min_chars', 'type' => 'number', 'label' => __( '最小触发字数', 'qiling' ), 'default' => '2', 'suffix' => __( '字', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '10' ), 'desc' => __( '输入达到该字数后才请求服务器，建议中文站点保持 2。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_max_results', 'type' => 'number', 'label' => __( '返回结果数量', 'qiling' ), 'default' => '6', 'suffix' => __( '条', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '12' ), 'desc' => __( '下拉面板最多展示的实时结果数量，数量越大查询成本越高。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_include_pages', 'type' => 'checkbox', 'label' => __( '包含页面', 'qiling' ), 'default' => '1', 'desc' => __( '实时结果中包含 WordPress 页面。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_include_products', 'type' => 'checkbox', 'label' => __( '包含产品', 'qiling' ), 'default' => '1', 'desc' => __( '存在产品类型（product）时，实时结果中包含产品。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_show_thumbnail', 'type' => 'checkbox', 'label' => __( '显示缩略图', 'qiling' ), 'default' => '1', 'desc' => __( '在实时结果卡片左侧显示文章或产品缩略图。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_show_excerpt', 'type' => 'checkbox', 'label' => __( '显示摘要', 'qiling' ), 'default' => '1', 'desc' => __( '在实时结果卡片中显示命中内容摘要，并高亮搜索词。', 'qiling' ) ),
                array( 'id' => 'search_autocomplete_show_price', 'type' => 'checkbox', 'label' => __( '显示产品价格', 'qiling' ), 'default' => '', 'desc' => __( '产品结果显示价格文本；无产品价格元数据时自动忽略。', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '接口已内置 nonce、游客限流和短缓存；更细缓存时间可通过 developer_starter_search_autocomplete_cache_ttl 过滤器调整。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '输出优化（Head 清理）', 'qiling' ), 'desc' => __( '移除 WordPress 在页面头部输出的多余信息，精简 HTML 代码', 'qiling' ) ),
                array( 'id' => 'remove_adjacent_posts', 'type' => 'checkbox', 'label' => __( '移除相邻文章链接', 'qiling' ), 'desc' => __( '移除 head 中的 prev/next 相邻文章链接标签', 'qiling' ) ),
                array( 'id' => 'remove_feed_links', 'type' => 'checkbox', 'label' => __( '移除 Feed 链接', 'qiling' ), 'desc' => __( '移除 head 中的 RSS/Atom 订阅链接', 'qiling' ) ),
                array( 'id' => 'remove_json_api_link', 'type' => 'checkbox', 'label' => __( '移除 JSON API 链接', 'qiling' ), 'desc' => __( '移除 head 中的 REST API 发现链接；可能影响依赖自动发现 REST 地址的外部客户端。', 'qiling' ) ),
                array( 'id' => 'remove_dns_prefetch_hints', 'type' => 'checkbox', 'label' => __( '移除 DNS 预取提示', 'qiling' ), 'desc' => __( '移除 WordPress 自动添加的 DNS 预取提示（如 s.w.org）', 'qiling' ) ),
                array( 'id' => 'remove_gutenberg_css', 'type' => 'checkbox', 'label' => __( '移除 Gutenberg 样式', 'qiling' ), 'desc' => __( '移除前端块样式；可能导致区块内容或第三方区块样式缺失，请用白名单保留关键样式。', 'qiling' ) ),
                array( 'id' => 'remove_global_styles', 'type' => 'checkbox', 'label' => __( '移除全局样式', 'qiling' ), 'desc' => __( '移除 WordPress 全局样式和 SVG 滤镜；可能影响 theme.json、区块间距、双色调和站点编辑器样式。', 'qiling' ) ),
                array( 'id' => 'runtime_style_output_allowlist', 'type' => 'textarea', 'label' => __( '前端样式白名单', 'qiling' ), 'desc' => __( '每行一个保留规则：style:global-styles、style:wp-block-library、style:*，或 path:/shop/ 这类路径前缀。', 'qiling' ), 'attrs' => array( 'rows' => 4, 'placeholder' => "style:global-styles\nstyle:wp-block-library\npath:/shop/" ) ),

                array( 'type' => 'section', 'title' => __( '安全增强', 'qiling' ), 'desc' => __( '增强网站安全性，防止常见攻击', 'qiling' ) ),
                array( 'id' => 'disable_author_archive', 'type' => 'checkbox', 'label' => __( '禁用作者存档页', 'qiling' ), 'desc' => __( '禁用 ?author=1 等作者存档页面，防止用户名枚举（个人主页需要这个）', 'qiling' ) ),
                array( 'id' => 'disable_file_edit', 'type' => 'checkbox', 'label' => __( '禁用文件编辑器', 'qiling' ), 'desc' => __( '禁用后台主题和插件的文件编辑功能，防止误操作导致网站崩溃', 'qiling' ) ),
                array( 'id' => 'login_error_hide', 'type' => 'checkbox', 'label' => __( '隐藏登录错误信息', 'qiling' ), 'desc' => __( '登录失败时不提示具体原因（用户名或密码），防止暴力破解', 'qiling' ) ),
                array( 'id' => 'disable_application_passwords', 'type' => 'checkbox', 'label' => __( '禁用应用密码', 'qiling' ), 'desc' => __( '关闭 WordPress Application Passwords；可能影响移动端、自动化脚本、外部集成和 REST Basic Auth，请为必要账号加白名单。', 'qiling' ) ),
                array( 'id' => 'runtime_application_passwords_allowlist', 'type' => 'textarea', 'label' => __( '应用密码白名单', 'qiling' ), 'desc' => __( '每行一个 user:用户ID 或 role:角色，例如 user:1、role:administrator。开启“禁用应用密码”后，仅白名单用户继续可用。', 'qiling' ), 'attrs' => array( 'rows' => 3, 'placeholder' => "user:1\nrole:administrator" ) ),
                array( 'id' => 'security_headers_enable', 'type' => 'checkbox', 'label' => __( '启用安全响应头', 'qiling' ), 'desc' => __( '自动输出 X-Frame-Options / X-Content-Type-Options / Referrer-Policy / Permissions-Policy；HTTPS 下同时输出 HSTS', 'qiling' ) ),
                array( 'id' => 'security_headers_referrer_policy', 'type' => 'select', 'label' => __( 'Referrer-Policy 策略', 'qiling' ), 'choices' => array(
                    'strict-origin-when-cross-origin' => 'strict-origin-when-cross-origin',
                    'no-referrer'                     => 'no-referrer',
                    'same-origin'                     => 'same-origin',
                    'origin-when-cross-origin'        => 'origin-when-cross-origin',
                    'strict-origin'                   => 'strict-origin',
                ), 'default' => 'strict-origin-when-cross-origin' ),
                array( 'id' => 'security_headers_permissions_policy', 'type' => 'text', 'label' => __( 'Permissions-Policy', 'qiling' ), 'desc' => __( '留空使用推荐值。示例：geolocation=(), camera=(), microphone=()', 'qiling' ) ),
                array( 'id' => 'request_rate_limit_enable', 'type' => 'checkbox', 'label' => __( '启用游客请求限流', 'qiling' ), 'desc' => __( '限制游客高频搜索和留言请求，降低被刷风险', 'qiling' ) ),
                array( 'id' => 'request_rate_limit_window', 'type' => 'number', 'label' => __( '限流统计窗口', 'qiling' ), 'default' => '60', 'suffix' => __( '秒', 'qiling' ), 'attrs' => array( 'min' => '10', 'max' => '3600' ), 'desc' => __( '统计周期窗口，建议 60 秒', 'qiling' ) ),
                array( 'id' => 'request_rate_limit_search_max', 'type' => 'number', 'label' => __( '搜索频率上限', 'qiling' ), 'default' => '30', 'suffix' => __( '次/窗口', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '500' ) ),
                array( 'id' => 'request_rate_limit_adv_filter_max', 'type' => 'number', 'label' => __( '高级筛选频率上限', 'qiling' ), 'default' => '30', 'suffix' => __( '次/窗口', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '300' ) ),
                array( 'id' => 'request_rate_limit_product_max', 'type' => 'number', 'label' => __( '产品加载频率上限', 'qiling' ), 'default' => '60', 'suffix' => __( '次/窗口', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '500' ) ),
                array( 'id' => 'request_rate_limit_message_max', 'type' => 'number', 'label' => __( '留言频率上限', 'qiling' ), 'default' => '3', 'suffix' => __( '次/窗口', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '50' ) ),
                array( 'id' => 'request_rate_limit_poster_max', 'type' => 'number', 'label' => __( '游客海报缓存频率上限', 'qiling' ), 'default' => '12', 'suffix' => __( '次/窗口', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '120' ), 'desc' => __( '仅在允许游客缓存文章海报时生效；独立于“启用游客请求限流”开关，始终保护服务器写入。', 'qiling' ) ),
                array( 'id' => 'post_poster_guest_daily_write_max', 'type' => 'number', 'label' => __( '游客海报每日写入上限', 'qiling' ), 'default' => '60', 'suffix' => __( '次/IP/天', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '1000' ) ),
                array( 'id' => 'post_poster_guest_daily_bytes_max_mb', 'type' => 'number', 'label' => __( '游客海报每日流量上限', 'qiling' ), 'default' => '96', 'suffix' => __( 'MB/IP/天', 'qiling' ), 'attrs' => array( 'min' => '1', 'max' => '1024' ) ),

                array( 'type' => 'section', 'title' => __( '内容保护', 'qiling' ), 'desc' => __( '保护网站内容防止被轻易复制', 'qiling' ) ),
                array( 'id' => 'disable_right_click', 'type' => 'checkbox', 'label' => __( '禁用右键菜单', 'qiling' ), 'desc' => __( '禁止访客右键菜单（登录用户不受影响）', 'qiling' ) ),
                array( 'id' => 'disable_text_select', 'type' => 'checkbox', 'label' => __( '禁止文本选择', 'qiling' ), 'desc' => __( '禁止访客选择复制文本（登录用户不受影响）', 'qiling' ) ),
                array( 'id' => 'wechat_browser_block_enable', 'type' => 'checkbox', 'label' => __( '微信内访问蒙层提示', 'qiling' ), 'desc' => __( '开启后，访客在微信内置浏览器打开网站时，只输出空白提示页，不加载主题页面内容。', 'qiling' ) ),
                array( 'id' => 'wechat_browser_block_title', 'type' => 'text', 'label' => __( '微信提示标题', 'qiling' ), 'default' => __( '请在浏览器中打开', 'qiling' ), 'attrs' => array( 'placeholder' => __( '请在浏览器中打开', 'qiling' ) ) ),
                array( 'id' => 'wechat_browser_block_desc', 'type' => 'textarea', 'label' => __( '微信提示补充说明', 'qiling' ), 'default' => __( '当前页面在微信内可能无法正常操作，请按提示切换到系统浏览器继续访问。', 'qiling' ), 'attrs' => array( 'rows' => 3 ) ),

                array( 'type' => 'section', 'title' => __( '评论优化', 'qiling' ), 'desc' => __( '减少垃圾评论，优化评论功能', 'qiling' ) ),
                array( 'id' => 'disable_comments', 'type' => 'checkbox', 'label' => __( '完全禁用评论', 'qiling' ), 'desc' => __( '禁用整个网站的评论功能（适合企业官网）', 'qiling' ) ),
                array( 'id' => 'comment_honeypot', 'type' => 'checkbox', 'label' => __( '评论蜜罐陷阱', 'qiling' ), 'desc' => __( '添加隐藏字段检测机器人垃圾评论（无需验证码）', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '链接优化（SEO）', 'qiling' ), 'desc' => __( '优化网站链接结构，提升搜索引擎友好度', 'qiling' ) ),
                array( 'id' => 'remove_category_base', 'type' => 'checkbox', 'label' => __( '分类去 category', 'qiling' ), 'desc' => __( '分类链接去除 /category/ 前缀，如 /category/news/ 变为 /news/', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '启用后自动刷新固定链接规则，有利于 SEO 优化', 'qiling' ), 'style' => 'color:#10b981;' ),

                array( 'type' => 'section', 'title' => __( '后台编辑器优化（Gutenberg）', 'qiling' ), 'desc' => __( '在不禁用区块编辑器的前提下，减少后台编辑页不必要请求与功能负担', 'qiling' ) ),
                array( 'id' => 'admin_disable_remote_block_patterns', 'type' => 'checkbox', 'label' => __( '关闭远程区块 Patterns', 'qiling' ), 'desc' => __( '禁用 WordPress.org 远程 Patterns 拉取，减少编辑器初始化请求', 'qiling' ) ),
                array( 'id' => 'admin_disable_block_directory', 'type' => 'checkbox', 'label' => __( '关闭区块目录搜索', 'qiling' ), 'desc' => __( '禁用“添加区块”时的在线区块目录检索，提升编辑器响应', 'qiling' ) ),
                array( 'id' => 'admin_disable_openverse', 'type' => 'checkbox', 'label' => __( '关闭 Openverse 媒体面板', 'qiling' ), 'desc' => __( '隐藏编辑器中的 Openverse 在线媒体入口，减少后台远程依赖', 'qiling' ) ),
                array( 'id' => 'admin_reduce_editor_preload', 'type' => 'checkbox', 'label' => __( '精简编辑器预加载接口', 'qiling' ), 'desc' => __( '移除区块目录/Pattern 目录等重型预加载接口，降低初始化请求压力（默认不截断必要端点）', 'qiling' ) ),
                array( 'id' => 'enable_gutenberg_editor_style', 'type' => 'checkbox', 'label' => __( '启用主题编辑器样式', 'qiling' ), 'desc' => __( '可选加载 assets/css/editor-style.css 到 Gutenberg 编辑画布。默认关闭，只有觉得 WordPress 默认编辑体验不适合当前站点时再启用。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '自动更新控制', 'qiling' ), 'desc' => __( '按需控制 WordPress 自动更新行为，默认保持官方策略；禁用更新会增加安全维护成本。', 'qiling' ) ),
                array( 'id' => 'disable_core_auto_update', 'type' => 'checkbox', 'label' => __( '禁用核心自动更新', 'qiling' ), 'desc' => __( '阻止 WordPress 核心自动更新；可能错过安全小版本，可在白名单保留 core:minor。', 'qiling' ) ),
                array( 'id' => 'disable_plugin_auto_update', 'type' => 'checkbox', 'label' => __( '禁用插件自动更新', 'qiling' ), 'desc' => __( '阻止插件自动更新；可能影响安全修复和兼容补丁，可按插件 slug 加白名单。', 'qiling' ) ),
                array( 'id' => 'disable_theme_auto_update', 'type' => 'checkbox', 'label' => __( '禁用主题自动更新', 'qiling' ), 'desc' => __( '阻止主题自动更新；如果有子主题或官方主题依赖自动更新，可按主题 stylesheet 加白名单。', 'qiling' ) ),
                array( 'id' => 'disable_translation_auto_update', 'type' => 'checkbox', 'label' => __( '禁用翻译包自动更新', 'qiling' ), 'desc' => __( '阻止语言包自动更新；可能导致后台和插件翻译落后，可按语言代码加白名单。', 'qiling' ) ),
                array( 'id' => 'disable_update_emails', 'type' => 'checkbox', 'label' => __( '禁用自动更新通知邮件', 'qiling' ), 'desc' => __( '关闭核心/插件/主题自动更新结果邮件通知；可能让维护人员错过失败或成功状态。', 'qiling' ) ),
                array( 'id' => 'runtime_auto_update_allowlist', 'type' => 'textarea', 'label' => __( '自动更新白名单', 'qiling' ), 'desc' => __( '每行一个保留规则：core:minor、core:*、plugin:my-plugin、theme:twentytwentysix、translation:zh_cn。仅在上方禁用项开启时生效。', 'qiling' ), 'attrs' => array( 'rows' => 4, 'placeholder' => "core:minor\nplugin:my-plugin\ntheme:twentytwentysix\ntranslation:zh_cn" ) ),
                array( 'type' => 'note', 'content' => __( '建议至少保留核心安全更新。若全部禁用，请建立手动更新流程（如每周检查一次）；遇到兼容事故可开启“兼容回滚模式”临时恢复。', 'qiling' ), 'style' => 'color:#f59e0b;' ),

                array( 'type' => 'section', 'title' => __( '性能优化', 'qiling' ), 'desc' => __( '前端资源加载优化', 'qiling' ) ),
                array( 'id' => 'css_minify_enable', 'type' => 'checkbox', 'label' => __( '启用 CSS 压缩', 'qiling' ), 'desc' => __( '前台优先加载 .min.css 版本（如果存在）', 'qiling' ) ),
                array( 'id' => 'remove_assets_version', 'type' => 'checkbox', 'label' => __( '移除资源版本号', 'qiling' ), 'desc' => __( '移除 CSS/JS 资源链接中的 ?ver= 参数', 'qiling' ) ),
                array( 'id' => 'disable_external_google_fonts', 'type' => 'checkbox', 'label' => __( '禁用外部 Google Fonts', 'qiling' ), 'desc' => __( '阻止前台、后台和登录页加载 fonts.googleapis.com / fonts.gstatic.com 及 Google WebFont Loader，并隐藏 Font Library 的 Google Fonts 来源；如个别插件后台图标异常，可临时关闭或开启兼容回滚模式排查。', 'qiling' ), 'default' => '1' ),
                array( 'type' => 'note', 'content' => __( '可提升浏览器缓存命中率，但更新后可能需要手动清除浏览器缓存', 'qiling' ) ),
                array( 'id' => 'html_minify', 'type' => 'checkbox', 'label' => __( 'HTML 代码压缩', 'qiling' ), 'desc' => __( '压缩 HTML 输出，移除多余空白和换行', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '提示：实验性功能：可能影响内联 JS/CSS，如遇问题请关闭此选项', 'qiling' ), 'style' => 'color:#f59e0b;' ),
                array( 'id' => 'module_css_load_mode', 'type' => 'select', 'label' => __( '功能模块 CSS 加载方式', 'qiling' ), 'choices' => array(
                    'single' => __( '单文件模式（modules.css 整包，默认）', 'qiling' ),
                    'split'  => __( '按需模式（当前页面独立模块）', 'qiling' ),
                ), 'default' => 'single', 'desc' => __( '两种模式使用同一批模块独立作用域样式：单文件模式加载合并后的 modules.css；按需模式只加载当前页面对应的 modules-split 模块文件。', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '开启前请先点击下方按钮生成压缩文件，如果已经有了就不用再次生成。生成的压缩文件位于 assets/css/ 目录下。', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_generate_min_css_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_split_css_integrity_field' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_gzip_status_field' ) ),
                array( 'id' => 'lazy_load_images', 'type' => 'checkbox', 'label' => __( '图片延迟加载', 'qiling' ), 'desc' => __( '启用图片懒加载，图片进入视口时才加载（使用原生 loading="lazy"）', 'qiling' ) ),
                array( 'id' => 'lazy_load_placeholder_enable', 'type' => 'checkbox', 'label' => __( '图片渐进式占位', 'qiling' ), 'desc' => __( '需同时开启图片延迟加载。开启后懒加载图片显示骨架占位，并在加载完成后淡入', 'qiling' ) ),
                array( 'id' => 'lazy_load_iframes', 'type' => 'checkbox', 'label' => __( '视频/iframe 延迟加载', 'qiling' ), 'desc' => __( '启用 iframe 和嵌入视频的懒加载', 'qiling' ) ),
                array( 'id' => 'disable_jquery_migrate', 'type' => 'checkbox', 'label' => __( '禁用 jQuery Migrate', 'qiling' ), 'desc' => __( '移除 jQuery Migrate 脚本；可能影响仍依赖旧 jQuery API 的插件或自定义脚本，异常时先开启兼容回滚模式。', 'qiling' ) ),
                array( 'id' => 'query_optimize_enable', 'type' => 'checkbox', 'label' => __( '启用查询参数优化', 'qiling' ), 'desc' => __( '自动为非分页查询启用 no_found_rows 等低风险优化，减少 SQL 开销', 'qiling' ) ),
                array( 'id' => 'query_cache_enable', 'type' => 'checkbox', 'label' => __( '启用查询结果缓存', 'qiling' ), 'desc' => __( '缓存高频 WP_Query 结果，减少重复查询（推荐开启）', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'query_cache_ttl', 'type' => 'number', 'label' => __( '查询缓存时长', 'qiling' ), 'default' => '300', 'suffix' => __( '秒', 'qiling' ), 'attrs' => array( 'min' => '30', 'max' => (string) DAY_IN_SECONDS ), 'desc' => __( '建议 120-600 秒；内容更新后会自动失效', 'qiling' ) ),
                array( 'id' => 'fragment_cache_enable', 'type' => 'checkbox', 'label' => __( '启用页面片段缓存', 'qiling' ), 'desc' => __( '缓存相关文章等可复用 HTML 片段，降低渲染与查询成本', 'qiling' ) ),
                array( 'id' => 'fragment_cache_ttl', 'type' => 'number', 'label' => __( '片段缓存时长', 'qiling' ), 'default' => '180', 'suffix' => __( '秒', 'qiling' ), 'attrs' => array( 'min' => '30', 'max' => (string) DAY_IN_SECONDS ), 'desc' => __( '建议 120-600 秒；内容更新后会自动失效', 'qiling' ) ),
                array( 'id' => 'performance_monitor_enable', 'type' => 'checkbox', 'label' => __( '启用性能监测浮层', 'qiling' ), 'desc' => __( '仅管理员前台可见，显示当前页面加载时间、SQL 次数、内存等指标', 'qiling' ) ),
                array( 'id' => 'js_loading_strategy', 'type' => 'select', 'label' => __( 'JS 加载策略', 'qiling' ), 'choices' => array(
                    ''                  => __( '跟随主题默认（安全 defer）', 'qiling' ),
                    'none'              => __( '不优化', 'qiling' ),
                    'safe_defer'        => __( '仅延迟非关键脚本（推荐）', 'qiling' ),
                    'aggressive_defer'  => __( '激进：尽可能 defer（需自行验证）', 'qiling' ),
                ), 'desc' => __( '用于减少阻塞渲染脚本。激进模式可能影响部分依赖顺序严格的插件。', 'qiling' ) ),
                array( 'id' => 'js_defer_exclude_handles', 'type' => 'textarea', 'label' => __( 'JS 排除句柄', 'qiling' ), 'desc' => __( '每行一个脚本 handle（也支持逗号分隔），例如：my-slider-script', 'qiling' ) ),
                array( 'id' => 'lcp_preload_enable', 'type' => 'checkbox', 'label' => __( '启用 LCP 图片预加载', 'qiling' ), 'desc' => __( '优先预加载首屏关键图片，改善 LCP 指标', 'qiling' ) ),
                array( 'id' => 'lcp_preload_mode', 'type' => 'select', 'label' => __( 'LCP 预加载来源', 'qiling' ), 'choices' => array(
                    'featured' => __( '优先文章特色图（自动）', 'qiling' ),
                    'custom'   => __( '使用自定义图片 URL', 'qiling' ),
                ), 'default' => 'featured' ),
                array( 'id' => 'lcp_preload_custom_url', 'type' => 'text', 'label' => __( 'LCP 自定义图片 URL', 'qiling' ), 'desc' => __( '当上方选择“自定义图片 URL”时生效', 'qiling' ), 'attrs' => array( 'placeholder' => 'https://example.com/hero.jpg' ) ),

                array( 'type' => 'section', 'title' => __( '页面资源与质量审计', 'qiling' ), 'desc' => __( '为管理员生成当前页面资源清单，并检查 LCP、图片、移动端溢出、颜色对比度、标题层级、表单可访问性和 CLS 风险。', 'qiling' ) ),
                array( 'id' => 'page_quality_audit_enable', 'type' => 'checkbox', 'label' => __( '启用页面质量审计', 'qiling' ), 'desc' => __( '仅管理员前台访问时运行，不影响普通访客；也可在前台 URL 添加 ?qiling_page_audit=1 临时查看。', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'page_quality_audit_embed_json', 'type' => 'checkbox', 'label' => __( '在页面底部输出 JSON 清单', 'qiling' ), 'desc' => __( '开启后管理员前台可在源码中看到 qiling-page-resource-manifest 和 qiling-page-quality-audit；调试完成后建议关闭。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '内容别名优化', 'qiling' ), 'desc' => __( '发布文章、页面、分类和标签时，将中文别名自动转换为拼音。', 'qiling' ) ),
                array( 'id' => 'pinyin_slug_enable', 'type' => 'checkbox', 'label' => __( '启用中文别名转拼音', 'qiling' ), 'desc' => __( '开启后仅处理文章、页面、分类和标签；不会修改上传文件名，也不会批量改写历史内容。', 'qiling' ) ),
                array( 'id' => 'pinyin_slug_mode', 'type' => 'select', 'label' => __( '拼音转换方式', 'qiling' ), 'choices' => array(
                    'full' => __( '全拼', 'qiling' ),
                    'abbr' => __( '首字母', 'qiling' ),
                ), 'default' => 'full' ),
                array( 'id' => 'pinyin_slug_divider', 'type' => 'select', 'label' => __( '拼音分隔符', 'qiling' ), 'choices' => array(
                    '-' => '-',
                    '_' => '_',
                    '.' => '.',
                    ''  => __( '无分隔符', 'qiling' ),
                ), 'default' => '-' ),
                array( 'id' => 'pinyin_slug_max_length', 'type' => 'number', 'label' => __( '别名长度限制', 'qiling' ), 'default' => '60', 'attrs' => array( 'min' => '0', 'max' => '200' ), 'desc' => __( '设置为 0 表示不截断；使用分隔符时会尽量在完整拼音片段后截断。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '媒体优化', 'qiling' ), 'desc' => __( '文件上传与头像设置', 'qiling' ) ),
                array( 'id' => 'disable_gravatar', 'type' => 'checkbox', 'label' => __( '禁用 Gravatar 头像', 'qiling' ), 'desc' => __( '彻底禁用 Gravatar 远程请求，使用本地默认头像代替（解决 Gravatar 被墙或加载慢的问题）', 'qiling' ) ),
                array( 'id' => 'upload_file_rename', 'type' => 'checkbox', 'label' => __( '上传文件名重命名', 'qiling' ), 'desc' => __( '上传时自动将中文等特殊文件名重命名为随机字母数字组合（防止乱码和路径问题）', 'qiling' ) ),
                array( 'id' => 'svg_upload_enable', 'type' => 'checkbox', 'label' => __( '允许上传 SVG 图片', 'qiling' ), 'desc' => __( '开启后管理员可在媒体库上传 .svg 文件，主题会在上传前清洗 SVG 代码；不支持 .svgz。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( 'WebP 图片转换', 'qiling' ), 'desc' => __( '将图片自动转换为 WebP 格式以减少文件大小', 'qiling' ) ),
                array( 'id' => 'webp_enable', 'type' => 'checkbox', 'label' => __( '启用 WebP 转换', 'qiling' ), 'desc' => __( '上传图片时自动转换并替换为 WebP（仅 JPG/JPEG/PNG，GIF 保持原格式）', 'qiling' ) ),
                array( 'type' => 'note', 'content' => $webp_supported ? __( '服务器支持 WebP（GD 库已启用）', 'qiling' ) : __( '提示：服务器不支持 WebP，请安装 GD 库的 WebP 模块', 'qiling' ), 'style' => $webp_supported ? 'color:#10b981;' : 'color:#f59e0b;' ),
                array( 'id' => 'webp_quality', 'type' => 'number', 'label' => __( 'WebP 质量', 'qiling' ), 'desc' => __( 'WebP 图片压缩质量（1-100），建议 75-85', 'qiling' ), 'default' => '80', 'attrs' => array( 'min' => '1', 'max' => '100' ), 'suffix' => '%' ),

                array( 'type' => 'section', 'title' => __( '图片尺寸优化', 'qiling' ), 'desc' => __( '控制 WordPress 自动生成的图片缩略图，节省服务器空间', 'qiling' ) ),
                array( 'id' => 'disable_default_thumbnails', 'type' => 'checkbox', 'label' => __( '禁用大图压缩', 'qiling' ), 'desc' => __( '禁用 WordPress 自动缩放大于 2560px 的图片', 'qiling' ) ),
                array( 'id' => 'disable_image_sizes', 'type' => 'checkbox', 'label' => __( '禁用多尺寸缩略图', 'qiling' ), 'desc' => __( '禁止 WordPress 上传时自动生成多个尺寸的缩略图，节省服务器空间', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '提示：启用后新上传的图片只保留原图，可能影响依赖特定尺寸的功能', 'qiling' ), 'style' => 'color:#f59e0b;' ),

                array( 'type' => 'section', 'title' => __( '资源预加载', 'qiling' ), 'desc' => __( '提前解析和连接外部资源，加速页面加载', 'qiling' ) ),
                array( 'id' => 'cdn_url', 'type' => 'text', 'label' => __( '主 CDN 地址', 'qiling' ), 'desc' => __( '用于输出 preconnect 标签（如 https://cdn.example.com）。如已在下方“预连接域名”中配置可留空', 'qiling' ), 'attrs' => array( 'placeholder' => 'https://cdn.example.com' ) ),
                array( 'id' => 'dns_prefetch', 'type' => 'textarea', 'label' => __( 'DNS 预解析域名', 'qiling' ), 'desc' => __( '每行一个域名（不含 http://），如：fonts.googleapis.com、cdn.jsdelivr.net', 'qiling' ) ),
                array( 'id' => 'preconnect_urls', 'type' => 'textarea', 'label' => __( '预连接域名', 'qiling' ), 'desc' => __( '每行一个域名（不含 http://），如：fonts.gstatic.com。预连接比预解析更快但消耗更多资源', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '心跳控制', 'qiling' ), 'desc' => __( '优化 WordPress Admin 后台心跳频率，减少服务器负载', 'qiling' ) ),
                array( 'id' => 'heartbeat_control', 'type' => 'select', 'label' => __( '心跳优化', 'qiling' ), 'choices' => array(
                    ''                 => __( '不修改（默认 15 秒）', 'qiling' ),
                    '30'               => __( '减慢至 30 秒', 'qiling' ),
                    '60'               => __( '减慢至 60 秒', 'qiling' ),
                    '120'              => __( '减慢至 120 秒', 'qiling' ),
                    'disable_frontend' => __( '仅禁用前台', 'qiling' ),
                    'disable_all'      => __( '完全禁用（不推荐）', 'qiling' ),
                ), 'desc' => __( '心跳 API 用于自动保存和在线状态检测，频繁请求会增加服务器负担', 'qiling' ) ),
                array( 'id' => 'heartbeat_editor_interval', 'type' => 'select', 'label' => __( '编辑页心跳间隔', 'qiling' ), 'choices' => array(
                    ''   => __( '跟随上方“心跳优化”设置', 'qiling' ),
                    '15' => __( '15 秒（WordPress 默认）', 'qiling' ),
                    '30' => __( '30 秒（推荐）', 'qiling' ),
                    '60' => __( '60 秒（更省资源）', 'qiling' ),
                    '120'=> __( '120 秒（最低频）', 'qiling' ),
                ), 'desc' => __( '仅作用于文章/页面编辑器（Gutenberg/经典编辑器）', 'qiling' ) ),
                array( 'id' => 'heartbeat_admin_interval', 'type' => 'select', 'label' => __( '后台其他页面心跳间隔', 'qiling' ), 'choices' => array(
                    ''   => __( '跟随上方“心跳优化”设置', 'qiling' ),
                    '15' => __( '15 秒（WordPress 默认）', 'qiling' ),
                    '30' => __( '30 秒', 'qiling' ),
                    '60' => __( '60 秒（推荐）', 'qiling' ),
                    '120'=> __( '120 秒（更省资源）', 'qiling' ),
                ), 'desc' => __( '作用于仪表盘、文章列表、插件列表等后台页面', 'qiling' ) ),
                array( 'id' => 'autosave_interval', 'type' => 'number', 'label' => __( '自动保存间隔', 'qiling' ), 'default' => '60', 'suffix' => __( '秒', 'qiling' ), 'attrs' => array( 'min' => '30', 'max' => '600' ), 'desc' => __( '仅后台编辑页生效。建议 60-120 秒，降低写入频率', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '仪表盘减负', 'qiling' ), 'desc' => __( '减少后台首页默认组件，降低初次加载与渲染开销', 'qiling' ) ),
                array( 'id' => 'admin_disable_welcome_panel', 'type' => 'checkbox', 'label' => __( '关闭欢迎面板', 'qiling' ), 'desc' => __( '移除后台首页顶部欢迎模块（可随时重新开启）', 'qiling' ) ),
                array( 'id' => 'admin_disable_default_dashboard_widgets', 'type' => 'checkbox', 'label' => __( '关闭默认仪表盘组件', 'qiling' ), 'desc' => __( '移除站点健康、活动、快速草稿、WordPress 活动与新闻等默认组件', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '本土化与白标', 'qiling' ), 'desc' => __( '隐藏后台中的 WordPress 品牌标识，打造更干净的管理界面。所有隐藏仅为视觉层面，不影响任何功能和权限。', 'qiling' ) ),
                array( 'id' => 'qiling_admin_whitelabel_enable', 'type' => 'checkbox', 'label' => __( '启用后台白标', 'qiling' ), 'desc' => __( '开启后可逐项控制下方各隐藏选项；关闭则全部恢复默认。', 'qiling' ), 'default' => '', 'search_terms' => array( '白标', '去WordPress', '隐藏WP', '本土化', 'whitelabel' ) ),
                array( 'id' => 'qiling_admin_remove_wp_title_suffix', 'type' => 'checkbox', 'label' => __( '移除后台标题 WP 后缀', 'qiling' ), 'desc' => __( '移除浏览器标签页标题末尾的 " — WordPress" 字样。', 'qiling' ) ),
                array( 'id' => 'qiling_admin_hide_wp_logo', 'type' => 'checkbox', 'label' => __( '隐藏左上角 WP Logo', 'qiling' ), 'desc' => __( '移除全局 Admin Bar 左上角的 WordPress 标志及其下拉菜单。', 'qiling' ) ),
                array( 'id' => 'qiling_admin_hide_footer_text', 'type' => 'checkbox', 'label' => __( '隐藏底部版权与版本号', 'qiling' ), 'desc' => __( '清空后台页面底部的 WordPress 感谢语（左下角）和版本号（右下角）。', 'qiling' ) ),
                array( 'id' => 'qiling_admin_hide_tools_menu', 'type' => 'checkbox', 'label' => __( '移除工具与更新菜单', 'qiling' ), 'desc' => __( '从侧边栏移除"工具"顶级菜单和"仪表盘 → 更新"子菜单。页面本身仍可通过 URL 直接访问。', 'qiling' ) ),
                array( 'id' => 'qiling_admin_hide_install_buttons', 'type' => 'checkbox', 'label' => __( '隐藏安装按钮与帮助面板', 'qiling' ), 'desc' => __( '纯 CSS 隐藏主题/插件页面的"安装"按钮、顶部更新横幅和右上角"帮助"选项卡。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '数据库优化', 'qiling' ), 'desc' => __( '清理冗余数据，保持数据库精简', 'qiling' ) ),
                array( 'id' => 'auto_clean_revisions', 'type' => 'checkbox', 'label' => __( '自动清理修订版本', 'qiling' ), 'desc' => __( '每周自动清理超过 30 天的文章修订版本', 'qiling' ) ),
                array( 'id' => 'auto_clean_trash', 'type' => 'checkbox', 'label' => __( '自动清空回收站', 'qiling' ), 'desc' => __( '设置回收站自动清空时间为 7 天（默认 30 天）', 'qiling' ) ),
                array( 'id' => 'auto_clean_expired_transients', 'type' => 'checkbox', 'label' => __( '自动清理过期 Transients', 'qiling' ), 'desc' => __( '定期清理过期缓存键，降低 options 表膨胀风险', 'qiling' ) ),
                array( 'id' => 'auto_clean_spam_comments', 'type' => 'checkbox', 'label' => __( '自动清理垃圾评论', 'qiling' ), 'desc' => __( '定期清理垃圾评论记录，降低数据库体积', 'qiling' ) ),
                array( 'id' => 'theme_cron_cleanup_schedule', 'type' => 'select', 'label' => __( '主题清理任务周期', 'qiling' ), 'default' => 'weekly', 'choices' => array(
                    'daily'      => __( '每天', 'qiling' ),
                    'weekly'     => __( '每周（推荐）', 'qiling' ),
                    'monthly_30' => __( '每30天', 'qiling' ),
                    'disabled'   => __( '关闭任务', 'qiling' ),
                ), 'desc' => __( '仅控制主题自己的数据库清理任务，不影响其他插件或系统任务', 'qiling' ) ),
                array( 'id' => 'cleanup_cron_enable', 'type' => 'checkbox', 'label' => __( '启用外部定时清理入口', 'qiling' ), 'desc' => __( '允许第三方任务平台通过独立密钥触发主题清理；默认关闭，开启后仍受独立频率限制保护。', 'qiling' ) ),
                array( 'id' => 'cleanup_cron_allowed_ips', 'type' => 'textarea', 'label' => __( '外部定时清理 IP 白名单', 'qiling' ), 'desc' => __( '可选。每行一个固定 IP；留空表示不限制。使用第三方平台且 IP 不固定时建议留空，仅依赖密钥。', 'qiling' ), 'attrs' => array( 'rows' => 3, 'placeholder' => "203.0.113.10\n2001:db8::10" ) ),

                array( 'type' => 'section', 'title' => __( '后台任务', 'qiling' ), 'desc' => __( '控制 WP Cron 和清理任务', 'qiling' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_wp_cron_status_hint' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_cleanup_rest_endpoint' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_db_cleanup_section' ) ),
                array( 'type' => 'custom', 'callback' => array( $this, 'render_poster_cache_section' ) ),
            ),

            // ========== 认证选项卡 ==========
            'auth' => array(
                array( 'type' => 'section', 'title' => __( '自定义登录注册', 'qiling' ), 'desc' => __( '启用主题自带的现代化登录注册页面，替代 WordPress 默认页面', 'qiling' ) ),
                array( 'id' => 'custom_auth_enable', 'type' => 'checkbox', 'label' => __( '启用自定义页面', 'qiling' ), 'desc' => __( '使用主题自定义的登录、注册、找回密码页面', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '验证码设置', 'qiling' ), 'desc' => __( '登录、注册、找回密码等场景的验证码配置', 'qiling' ) ),
                array( 'id' => 'auth_captcha_enable', 'type' => 'checkbox', 'label' => __( '滑动验证码', 'qiling' ), 'desc' => __( '在登录、注册、找回密码表单中启用滑动验证码', 'qiling' ) ),
                array( 'id' => 'captcha_provider', 'type' => 'select', 'label' => __( '验证码提供商', 'qiling' ), 'choices' => array(
                    'theme'  => __( '主题内置滑动验证码', 'qiling' ),
                    'aliyun' => __( '阿里云验证码 2.0', 'qiling' ),
                ), 'default' => 'theme', 'desc' => __( '可按需在主题滑动验证码与阿里云验证码之间切换', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '主题内置验证码适用于小网站；高访问量站点建议使用阿里云验证码服务。', 'qiling' ) ),
                array( 'id' => 'captcha_line_number', 'type' => 'number', 'label' => __( '验证码线条数量', 'qiling' ), 'desc' => __( '默认: 2', 'qiling' ) ),
                array( 'id' => 'captcha_font_size', 'type' => 'number', 'label' => __( '验证码字体大小', 'qiling' ), 'desc' => __( '默认: 18', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '阿里云智能验证码', 'qiling' ), 'desc' => __( '配置阿里云验证码服务（需要实名认证）', 'qiling' ) ),
                array( 'id' => 'aliyun_captcha_access_key_id', 'type' => 'text', 'label' => 'AccessKeyId', 'desc' => __( '阿里云 AccessKeyId，在阿里云控制台获取', 'qiling' ), 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'type' => 'note', 'content' => __( '控制台地址：', 'qiling' ) . '<a href="https://ram.console.aliyun.com/manage/ak" target="_blank">ram.console.aliyun.com</a>' ),
                array( 'id' => 'aliyun_captcha_access_key_secret', 'type' => 'text', 'label' => 'AccessKeySecret', 'desc' => __( '阿里云 AccessKeySecret，请妥善保管', 'qiling' ), 'input_type' => 'password', 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'id' => 'aliyun_captcha_region', 'type' => 'text', 'label' => __( '服务端 RegionId', 'qiling' ), 'default' => 'cn-shanghai', 'desc' => __( '用于服务端 VerifyIntelligentCaptcha 调用。默认 cn-shanghai（中国站）。', 'qiling' ) ),
                array( 'id' => 'aliyun_captcha_endpoint', 'type' => 'text', 'label' => __( '阿里云 Endpoint（可选）', 'qiling' ), 'desc' => __( '留空将自动使用 captcha.{region}.aliyuncs.com', 'qiling' ) ),
                array( 'id' => 'aliyun_captcha_client_region', 'type' => 'select', 'label' => __( '前端验证码 Region', 'qiling' ), 'choices' => array(
                    'cn'  => __( '中国站（cn）', 'qiling' ),
                    'sgp' => __( '新加坡站（sgp）', 'qiling' ),
                ), 'default' => 'cn', 'desc' => __( '对应文档中的 AliyunCaptchaConfig.region。中国站选 cn，新加坡站选 sgp。', 'qiling' ) ),
                array( 'id' => 'aliyun_captcha_scene_auth', 'type' => 'text', 'label' => __( '认证场景 SceneId', 'qiling' ), 'desc' => __( '用于登录/注册/找回密码/短信发送等认证流程', 'qiling' ) ),
                array( 'id' => 'aliyun_captcha_scene_search', 'type' => 'text', 'label' => __( '搜索场景 SceneId', 'qiling' ), 'desc' => __( '用于前台搜索验证', 'qiling' ) ),
                array( 'id' => 'aliyun_captcha_prefix', 'type' => 'text', 'label' => __( '阿里云 Prefix', 'qiling' ), 'desc' => __( '控制台中对应业务场景的 Prefix', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '搜索防刷', 'qiling' ) ),
                array( 'id' => 'search_captcha_enable', 'type' => 'checkbox', 'label' => __( '启用搜索防刷', 'qiling' ), 'desc' => __( '启用后，在搜索结果页会要求输入验证码', 'qiling' ) ),
                array( 'id' => 'search_captcha_wait', 'type' => 'number', 'label' => __( '等待时间(秒)', 'qiling' ), 'desc' => __( '默认: 3', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '登录设置', 'qiling' ) ),
                array( 'id' => 'auth_modal_login_enable', 'type' => 'checkbox', 'label' => __( '启用默认弹窗登录', 'qiling' ), 'desc' => __( '开启后，评论、内容限制、投稿等位置的登录入口使用弹窗登录；与“显示登录按钮”相互独立。', 'qiling' ) ),
                array( 'id' => 'login_remember_me_enable', 'type' => 'checkbox', 'label' => __( '记住登录', 'qiling' ), 'desc' => __( '允许用户勾选“记住我”（有效期 14 天、仅限用户名登录）', 'qiling' ) ),
                array( 'id' => 'weixin_login_enable', 'type' => 'checkbox', 'label' => __( '微信登录', 'qiling' ), 'desc' => __( '在登录弹窗与登录页面显示微信扫码登录入口（需启用启灵微信登录插件）', 'qiling' ) ),
                array( 'id' => 'weixin_login_icon', 'type' => 'text', 'label' => __( '微信登录图标', 'qiling' ), 'desc' => __( '支持阿里巴巴 Iconfont Symbol 类名（如：icon-wechat）。留空则使用 assets/images/weixin.png。', 'qiling' ), 'attrs' => array( 'placeholder' => 'icon-wechat' ) ),
                array( 'id' => 'weixin_login_default', 'type' => 'checkbox', 'label' => __( '默认微信登录', 'qiling' ), 'desc' => __( '进入登录弹窗/登录页时默认展示微信二维码（若已启用“默认手机号登录”，手机号优先）', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '第三方社交登录', 'qiling' ), 'desc' => __( '微信登录需要启用对应插件；QQ、GitHub 和 Google 登录可在下方分别配置。', 'qiling' ) ),
                array( 'id' => 'social_login_qq_enable', 'type' => 'checkbox', 'label' => __( 'QQ 登录', 'qiling' ), 'desc' => __( '在登录弹窗与登录页面显示 QQ 登录入口。', 'qiling' ) ),
                array( 'id' => 'social_login_qq_app_id', 'type' => 'text', 'label' => __( 'QQ App ID', 'qiling' ), 'desc' => __( 'QQ互联网站应用的 appid / oauth_consumer_key。', 'qiling' ), 'attrs' => array( 'autocomplete' => 'off', 'placeholder' => '100000000' ) ),
                array( 'id' => 'social_login_qq_app_key', 'type' => 'text', 'label' => __( 'QQ App Key', 'qiling' ), 'desc' => __( 'QQ互联网站应用的 appkey / client_secret，请妥善保管。', 'qiling' ), 'input_type' => 'password', 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'id' => 'social_login_qq_icon', 'type' => 'text', 'label' => __( 'QQ 登录图标', 'qiling' ), 'desc' => __( '支持 Iconfont Symbol 类名（如：icon-qq）。留空则使用 assets/images/qq.png。', 'qiling' ), 'attrs' => array( 'placeholder' => 'icon-qq' ) ),
                array( 'type' => 'note', 'content' => sprintf( __( 'QQ互联后台回调地址：%s', 'qiling' ), admin_url( 'admin-post.php?action=developer_starter_social_login_callback&provider=qq' ) ) ),
                array( 'id' => 'social_login_github_enable', 'type' => 'checkbox', 'label' => __( 'GitHub 登录', 'qiling' ), 'desc' => __( '在登录弹窗与登录页面显示 GitHub 登录入口。', 'qiling' ) ),
                array( 'id' => 'social_login_github_client_id', 'type' => 'text', 'label' => __( 'GitHub Client ID', 'qiling' ), 'desc' => __( 'GitHub OAuth App 的 Client ID。', 'qiling' ), 'attrs' => array( 'autocomplete' => 'off', 'placeholder' => 'Ov23li...' ) ),
                array( 'id' => 'social_login_github_client_secret', 'type' => 'text', 'label' => __( 'GitHub Client Secret', 'qiling' ), 'desc' => __( 'GitHub OAuth App 的 Client Secret，请妥善保管。', 'qiling' ), 'input_type' => 'password', 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'id' => 'social_login_github_icon', 'type' => 'text', 'label' => __( 'GitHub 登录图标', 'qiling' ), 'desc' => __( '支持 Iconfont Symbol 类名（如：icon-github）。留空则使用 assets/images/github.png。', 'qiling' ), 'attrs' => array( 'placeholder' => 'icon-github' ) ),
                array( 'type' => 'note', 'content' => sprintf( __( 'GitHub OAuth App 回调地址：%s', 'qiling' ), admin_url( 'admin-post.php?action=developer_starter_social_login_callback&provider=github' ) ) ),
                array( 'id' => 'social_login_google_enable', 'type' => 'checkbox', 'label' => __( 'Google 登录', 'qiling' ), 'desc' => __( '在登录弹窗与登录页面显示 Google 登录入口。', 'qiling' ) ),
                array( 'id' => 'social_login_google_client_id', 'type' => 'text', 'label' => __( 'Google Client ID', 'qiling' ), 'desc' => __( 'Google Cloud OAuth 2.0 Web 应用的 Client ID。', 'qiling' ), 'attrs' => array( 'autocomplete' => 'off', 'placeholder' => 'xxx.apps.googleusercontent.com' ) ),
                array( 'id' => 'social_login_google_client_secret', 'type' => 'text', 'label' => __( 'Google Client Secret', 'qiling' ), 'desc' => __( 'Google Cloud OAuth 2.0 Web 应用的 Client Secret，请妥善保管。', 'qiling' ), 'input_type' => 'password', 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'id' => 'social_login_google_icon', 'type' => 'text', 'label' => __( 'Google 登录图标', 'qiling' ), 'desc' => __( '支持 Iconfont Symbol 类名（如：icon-google）。留空则使用 assets/images/google.png。', 'qiling' ), 'attrs' => array( 'placeholder' => 'icon-google' ) ),
                array( 'type' => 'note', 'content' => sprintf( __( 'Google Cloud 授权重定向 URI：%s', 'qiling' ), admin_url( 'admin-post.php?action=developer_starter_social_login_callback&provider=google' ) ) ),

                array( 'type' => 'section', 'title' => __( '登录注册视觉图', 'qiling' ), 'desc' => __( '支持独立登录/注册页面与顶部弹窗分别设置展示图；留空则不显示左侧图片区域。', 'qiling' ) ),
                array( 'id' => 'auth_page_background_mode', 'type' => 'select', 'label' => __( '独立页背景模式', 'qiling' ), 'default' => 'auto', 'choices' => array(
                    'auto'   => __( '自动：有图优先，无图用颜色', 'qiling' ),
                    'preset' => __( '跟随主题风格预设', 'qiling' ),
                    'color'  => __( '仅使用自定义背景色', 'qiling' ),
                    'image'  => __( '优先使用自定义背景图', 'qiling' ),
                ), 'desc' => __( '默认自动模式：填写背景图时显示图片，未填写图片时使用背景色，二者都为空时继续跟随主题风格预设。', 'qiling' ) ),
                array( 'id' => 'auth_page_background_color', 'type' => 'color', 'label' => __( '独立页背景色', 'qiling' ), 'default' => '', 'desc' => __( '用于登录、注册、找回密码独立页面的整页背景；留空时使用当前主题风格预设背景。', 'qiling' ) ),
                array( 'id' => 'auth_page_background_image', 'type' => 'image', 'label' => __( '独立页背景图', 'qiling' ), 'desc' => __( '用于登录、注册、找回密码独立页面的整页背景。推荐尺寸：1920×1200 px 或更高。', 'qiling' ), 'preview_style' => 'display:block;max-width:220px;margin-top:10px;border-radius:12px;' ),
                array( 'id' => 'auth_page_background_image_opacity', 'type' => 'number', 'label' => __( '背景图透明度', 'qiling' ), 'default' => '80', 'attrs' => array( 'min' => '0', 'max' => '100', 'step' => '5' ), 'suffix' => '%', 'desc' => __( '仅在显示背景图时生效；数值越低，底下的背景色或预设渐变越明显。', 'qiling' ) ),
                array( 'id' => 'auth_page_side_image', 'type' => 'image', 'label' => __( '独立登录注册页左侧图', 'qiling' ), 'desc' => __( '用于登录页和注册页左侧展示（长方形横向效果）。推荐尺寸：1600×1000 px（16:10），最小建议 1280×800 px。', 'qiling' ), 'preview_style' => 'display:block;max-width:180px;margin-top:10px;border-radius:12px;' ),
                array( 'id' => 'auth_modal_side_image', 'type' => 'image', 'label' => __( '弹窗登录左侧图', 'qiling' ), 'desc' => __( '用于顶部登录/注册弹窗左侧展示。推荐尺寸：900×1200 px（3:4），最小建议 600×800 px。', 'qiling' ), 'preview_style' => 'display:block;max-width:180px;margin-top:10px;border-radius:12px;' ),
                array( 'id' => 'password_strength', 'type' => 'select', 'label' => __( '密码强度要求', 'qiling' ), 'choices' => array(
                    'weak'   => __( '弱（至少6位）', 'qiling' ),
                    'medium' => __( '中（至少8位，含字母和数字）', 'qiling' ),
                    'strong' => __( '强（至少10位，含大小写、数字、特殊字符）', 'qiling' ),
                ), 'desc' => __( '注册和重置密码时的密码强度要求', 'qiling' ), 'default' => 'medium' ),
                array( 'id' => 'registration_mode', 'type' => 'select', 'label' => __( '注册方式', 'qiling' ), 'choices' => array(
                    'all'        => __( '全部注册', 'qiling' ),
                    'realname'   => __( '实名注册（仅手机号/微信）', 'qiling' ),
                    'email_only' => __( '仅邮箱注册', 'qiling' ),
                ), 'desc' => __( '控制注册入口：全部注册=邮箱+手机号+微信（插件可用时）；实名注册=仅手机号+微信；仅邮箱注册=仅邮箱。', 'qiling' ), 'default' => 'all' ),
                array( 'id' => 'register_username_chinese_policy', 'type' => 'select', 'label' => __( '中文用户名策略', 'qiling' ), 'choices' => array(
                    'allow' => __( '允许中文用户名', 'qiling' ),
                    'deny'  => __( '禁止中文用户名', 'qiling' ),
                    'scan'  => __( '允许中文并调用启灵内容安全检测', 'qiling' ),
                ), 'desc' => __( '控制邮箱注册时用户名是否可包含中文；选择检测时，仅在启灵内容安全插件已启用检测的情况下拦截风险用户名。后端会同步校验。', 'qiling' ), 'default' => 'allow' ),
                array( 'id' => 'register_email_domain_whitelist', 'type' => 'textarea', 'label' => __( '邮箱后缀白名单', 'qiling' ), 'desc' => __( '每行或用逗号分隔填写一个后缀，例如：@gmail.com, @qq.com, @163.com。留空表示不限制邮箱后缀。', 'qiling' ), 'attrs' => array( 'rows' => 5, 'placeholder' => "@gmail.com\n@qq.com\n@163.com" ) ),
                array( 'id' => 'register_email_code_enable', 'type' => 'checkbox', 'label' => __( '邮箱注册验证码', 'qiling' ), 'desc' => __( '开启后，邮箱注册需要先发送并填写邮箱验证码。邮件通过主题 SMTP 配置发送。', 'qiling' ) ),
                array( 'id' => 'register_email_code_expire', 'type' => 'number', 'label' => __( '验证码有效期', 'qiling' ), 'default' => '10', 'attrs' => array( 'min' => '1', 'max' => '60' ), 'suffix' => __( '分钟', 'qiling' ), 'desc' => __( '邮箱验证码的有效时间，默认 10 分钟。', 'qiling' ) ),
                array( 'id' => 'register_email_code_interval', 'type' => 'number', 'label' => __( '发送间隔', 'qiling' ), 'default' => '60', 'attrs' => array( 'min' => '30', 'max' => '600' ), 'suffix' => __( '秒', 'qiling' ), 'desc' => __( '同一邮箱/同一 IP 的最小发送间隔，默认 60 秒。', 'qiling' ) ),
                array( 'id' => 'register_email_code_daily_ip_limit', 'type' => 'number', 'label' => __( '每日 IP 发送限制', 'qiling' ), 'default' => '30', 'attrs' => array( 'min' => '1', 'max' => '500' ), 'suffix' => __( '次', 'qiling' ), 'desc' => __( '每个 IP 每天最多可发送的邮箱验证码次数。', 'qiling' ) ),
                array( 'id' => 'register_email_code_daily_email_limit', 'type' => 'number', 'label' => __( '单邮箱每日发送限制', 'qiling' ), 'default' => '10', 'attrs' => array( 'min' => '1', 'max' => '200' ), 'suffix' => __( '次', 'qiling' ), 'desc' => __( '同一个邮箱每天最多可接收的验证码次数。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '实名认证设置', 'qiling' ), 'desc' => __( '配置实名认证接口相关参数', 'qiling' ) ),
                array( 'id' => 'id_verification_enable', 'type' => 'checkbox', 'label' => __( '启用实名认证', 'qiling' ), 'desc' => __( '开启后，用户可在个人中心进行身份证实名认证', 'qiling' ) ),
                array( 'id' => 'id_verification_api_url', 'type' => 'text', 'label' => __( '数链云API地址', 'qiling' ), 'desc' => __( '阿里云市场数链云三要素验证API地址', 'qiling' ), 'default' => 'https://slytransf.market.alicloudapi.com/mobile_transfer' ),
                array( 'id' => 'id_verification_appcode', 'type' => 'text', 'label' => 'AppCode', 'desc' => __( '阿里云市场购买API后获取的 AppCode', 'qiling' ), 'input_type' => 'password', 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'type' => 'note', 'content' => __( '实名认证接口已按官方文档固定为 GET 模式。', 'qiling' ), 'style' => 'color:#166534;' ),
                array( 'type' => 'note', 'content' => __( '购买地址：', 'qiling' ) . '<a href="https://market.aliyun.com/apimarket/detail/cmapi00050268" target="_blank">market.aliyun.com</a>' ),
                array( 'id' => 'id_verification_ssl_verify', 'type' => 'checkbox', 'label' => __( '启用 API SSL 验证', 'qiling' ), 'desc' => __( '默认开启实名认证接口的 SSL 证书验证，以保护姓名、手机号和身份证号传输安全。仅在服务器根证书异常排查时临时关闭。', 'qiling' ), 'default' => '1' ),
                array(
                    'type'      => 'note',
                    'content'   => __( '<strong>安全提醒：</strong>当前已关闭实名认证 API SSL 验证，姓名、手机号和身份证号可能在外部接口请求中失去证书校验保护。请仅在临时排查证书链问题时关闭，处理完成后立即重新开启。', 'qiling' ),
                    'style'     => 'color:#b45309;font-weight:500;',
                    'row_style' => static function( $options ) {
                        $options = is_array( $options ) ? $options : array();
                        $ssl_verify = array_key_exists( 'id_verification_ssl_verify', $options )
                            ? (string) $options['id_verification_ssl_verify']
                            : '1';

                        return '1' === $ssl_verify ? 'display:none;' : '';
                    },
                ),
                array( 'id' => 'id_verification_max_attempts', 'type' => 'number', 'label' => __( '每日最大尝试次数', 'qiling' ), 'desc' => __( '每个用户每天最多可尝试验证的次数，避免恶意消耗API调用', 'qiling' ), 'default' => '3', 'attrs' => array( 'min' => '1', 'max' => '10' ), 'suffix' => __( '次', 'qiling' ) ),
                array( 'id' => 'id_verification_force', 'type' => 'checkbox', 'label' => __( '强制实名验证', 'qiling' ), 'desc' => __( '未实名用户只能访问首页，其他页面将跳转到个人中心进行实名认证', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '提示：开启后，未完成实名认证的登录用户将被强制跳转到个人中心实名认证页面', 'qiling' ), 'style' => 'color:#f59e0b;' ),

                array( 'type' => 'section', 'title' => __( '跳转设置', 'qiling' ) ),
                array( 'id' => 'login_redirect_url', 'type' => 'text', 'label' => __( '登录成功跳转', 'qiling' ), 'desc' => __( '自定义登录成功后的跳转地址。留空则返回用户登录前所在的页面', 'qiling' ), 'attrs' => array( 'placeholder' => __( '留空将返回用户来源页面', 'qiling' ) ) ),
                array( 'id' => 'register_redirect_url', 'type' => 'text', 'label' => __( '注册成功跳转', 'qiling' ), 'desc' => __( '自定义注册成功后的跳转地址。留空则返回用户注册前所在的页面', 'qiling' ), 'attrs' => array( 'placeholder' => __( '留空将返回用户来源页面', 'qiling' ) ) ),

                array( 'type' => 'section', 'title' => __( '登录安全', 'qiling' ), 'desc' => __( '防止暴力破解和恶意登录尝试', 'qiling' ) ),
                array(
                    'type' => 'note',
                    'content' => __(
                        'CDN 兼容说明：主题默认会信任常见真实 IP 请求头（如 CF-Connecting-IP、True-Client-IP、X-Real-IP、X-Forwarded-For），以兼容 Cloudflare、阿里云 CDN、腾讯云 CDN 等反向代理环境。若站点需要阻断源站直连、校验回源请求、拦截伪造请求头或使用更严格的 WAF 规则，请使用启灵安全防护或其他专业安全插件处理。',
                        'qiling'
                    ),
                    'style' => 'color:#92400e;',
                ),
                array( 'id' => 'login_limit_enable', 'type' => 'checkbox', 'label' => __( '启用登录限制', 'qiling' ), 'desc' => __( '限制登录失败次数，防止暴力破解', 'qiling' ) ),
                array( 'id' => 'login_max_attempts', 'type' => 'number', 'label' => __( '最大尝试次数', 'qiling' ), 'desc' => __( '密码错误达到此次数后将暂时锁定登录', 'qiling' ), 'default' => '5', 'attrs' => array( 'min' => '1', 'max' => '20' ), 'suffix' => __( '次', 'qiling' ) ),
                array( 'id' => 'login_lockout_duration', 'type' => 'number', 'label' => __( '锁定时间', 'qiling' ), 'desc' => __( '登录被锁定后需要等待的时间', 'qiling' ), 'default' => '15', 'attrs' => array( 'min' => '1', 'max' => '1440' ), 'suffix' => __( '分钟', 'qiling' ) ),
                array( 'id' => 'login_notify_admin', 'type' => 'checkbox', 'label' => __( '失败通知管理员', 'qiling' ), 'desc' => __( '当账户被锁定时发送邮件通知管理员', 'qiling' ) ),
                array( 'id' => 'login_show_remaining', 'type' => 'checkbox', 'label' => __( '显示剩余次数', 'qiling' ), 'desc' => __( '登录失败时提示用户剩余尝试次数', 'qiling' ), 'default' => '1' ),

                array( 'type' => 'section', 'title' => __( '游客访问控制', 'qiling' ), 'desc' => __( '支持全站登录访问或指定分类登录可见', 'qiling' ) ),
                array( 'id' => 'guest_access_enable', 'type' => 'checkbox', 'label' => __( '启用游客访问控制', 'qiling' ), 'desc' => __( '开启后对游客生效（登录用户不受影响）', 'qiling' ) ),
                array( 'id' => 'guest_access_sitewide', 'type' => 'checkbox', 'label' => __( '全站必须登录', 'qiling' ), 'desc' => __( '开启后除登录/注册/找回密码页面外，其他页面仅登录可访问', 'qiling' ) ),
                array( 'id' => 'guest_access_categories', 'type' => 'checkbox_group', 'label' => __( '受限分类（可多选）', 'qiling' ), 'choices' => $cat_id_options, 'desc' => __( '游客将看不到这些分类及其文章，直接访问会显示提示框', 'qiling' ), 'args' => array(
                    'wrapper_style' => 'max-height:220px;overflow:auto;border:1px solid #e5e7eb;padding:10px;border-radius:8px;',
                    'label_style' => 'display:block;margin:4px 0;',
                ) ),
                array( 'id' => 'guest_access_prompt_title', 'type' => 'text', 'label' => __( '提示标题', 'qiling' ), 'default' => __( '该内容仅登录用户可见', 'qiling' ) ),
                array( 'id' => 'guest_access_prompt_desc', 'type' => 'textarea', 'label' => __( '提示说明', 'qiling' ), 'default' => __( '请登录后继续浏览', 'qiling' ) ),
                array( 'id' => 'guest_access_login_button_enable', 'type' => 'checkbox', 'label' => __( '显示登录按钮', 'qiling' ), 'default' => '1' ),
                array( 'id' => 'guest_access_login_button_text', 'type' => 'text', 'label' => __( '登录按钮文字', 'qiling' ), 'default' => __( '立即登录', 'qiling' ) ),
                array( 'id' => 'guest_access_extra_button_text', 'type' => 'text', 'label' => __( '自定义按钮文字', 'qiling' ), 'desc' => __( '留空则不显示', 'qiling' ) ),
                array( 'id' => 'guest_access_extra_button_url', 'type' => 'text', 'label' => __( '自定义按钮链接', 'qiling' ), 'desc' => __( '配合上面的按钮文字使用', 'qiling' ) ),
                array( 'id' => 'guest_access_extra_button_newtab', 'type' => 'checkbox', 'label' => __( '自定义按钮新窗口打开', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '提示页默认返回 200 状态，不做跳转，避免影响页面结构。', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( 'VIP 访问控制', 'qiling' ), 'desc' => __( '处理设置了最低 VIP 权限的分类和内容，当权限不足时的处理动作', 'qiling' ) ),
                array( 'id' => 'vip_denied_action', 'type' => 'select', 'label' => __( '拦截处理方式', 'qiling' ), 'desc' => __( '当 VIP 权限不足时的响应方式', 'qiling' ), 'choices' => array( 'prompt' => __( '显示提示界面', 'qiling' ), 'redirect' => __( '跳转指定页面', 'qiling' ) ), 'default' => 'prompt' ),
                array( 'id' => 'vip_denied_redirect_url', 'type' => 'text', 'label' => __( '拦截跳转链接', 'qiling' ), 'desc' => __( '仅在选择"跳转指定页面"时有效，请填写带 http 的完整网址', 'qiling' ), 'attrs' => array( 'placeholder' => 'https://' ) ),
                array( 'id' => 'vip_denied_prompt_title', 'type' => 'text', 'label' => __( '提示标题', 'qiling' ), 'default' => __( '该内容仅限 VIP 会员查看', 'qiling' ) ),
                array( 'id' => 'vip_denied_prompt_desc', 'type' => 'textarea', 'label' => __( '提示说明', 'qiling' ), 'default' => __( '您的权限不足，请开通或升级 VIP 后继续浏览', 'qiling' ) ),
                array( 'id' => 'vip_denied_btn_text', 'type' => 'text', 'label' => __( '升级按钮文字', 'qiling' ), 'default' => __( '立即升级 VIP', 'qiling' ) ),
                array( 'id' => 'vip_denied_btn_url', 'type' => 'text', 'label' => __( '升级按钮链接', 'qiling' ), 'desc' => __( '默认自动识别商城 VIP 开通页，您也可在此处自定义填写', 'qiling' ), 'attrs' => array( 'placeholder' => 'https://' ) ),

                array( 'type' => 'section', 'title' => __( '注册协议', 'qiling' ), 'desc' => __( '用户注册时需要同意的服务条款设置', 'qiling' ) ),
                array( 'id' => 'register_agreement_enable', 'type' => 'checkbox', 'label' => __( '启用注册协议', 'qiling' ), 'desc' => __( '用户注册时必须勾选同意协议复选框才能注册', 'qiling' ) ),
                array( 'id' => 'register_agreement_text', 'type' => 'text', 'label' => __( '协议前置文字', 'qiling' ), 'desc' => __( '显示在复选框后面的文字，如：我已阅读并同意', 'qiling' ), 'default' => __( '我已阅读并同意', 'qiling' ) ),
                array( 'id' => 'register_agreement_link_text', 'type' => 'text', 'label' => __( '协议链接文字', 'qiling' ), 'desc' => __( '可点击的协议链接文字', 'qiling' ), 'default' => __( '《用户服务协议》', 'qiling' ) ),
                array( 'id' => 'register_agreement_url', 'type' => 'text', 'label' => __( '协议页面链接', 'qiling' ), 'desc' => __( '用户服务协议页面的完整URL地址', 'qiling' ), 'attrs' => array( 'placeholder' => 'https://example.com/terms' ) ),

                array( 'type' => 'section', 'title' => __( '页面ID', 'qiling' ), 'desc' => __( '主题激活时自动创建，一般无需修改', 'qiling' ) ),
                array( 'id' => 'login_page_id', 'type' => 'page_id', 'label' => __( '登录页面', 'qiling' ) ),
                array( 'id' => 'register_page_id', 'type' => 'page_id', 'label' => __( '注册页面', 'qiling' ) ),
                array( 'id' => 'forgot_password_page_id', 'type' => 'page_id', 'label' => __( '找回密码页面', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '用户头像设置', 'qiling' ), 'desc' => __( '自定义所有用户的默认头像，替代WordPress默认的Gravatar头像服务', 'qiling' ) ),
                array( 'id' => 'default_avatar', 'type' => 'image', 'label' => __( '默认用户头像', 'qiling' ), 'desc' => __( '设置后，所有未自定义头像的用户都将显示此头像，不再使用Gravatar头像服务', 'qiling' ), 'preview_style' => 'max-width:100px;margin-top:8px;border-radius:50%;' ),
                array( 'id' => 'user_avatar_upload_enable', 'type' => 'checkbox', 'label' => __( '允许用户上传头像', 'qiling' ), 'desc' => __( '启用后，用户可以在个人中心上传自己的头像图片', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '资格设置', 'qiling' ), 'desc' => __( '控制用户账号资格相关功能。', 'qiling' ) ),
                array( 'id' => 'account_deletion_request_enable', 'type' => 'checkbox', 'label' => __( '启用用户注销申请', 'qiling' ), 'desc' => __( '开启后，用户可在个人中心提交账号注销申请，后台人工审核处理。', 'qiling' ) ),
                array( 'id' => 'account_deletion_request_agreement', 'type' => 'textarea', 'label' => __( '注销协议说明', 'qiling' ), 'desc' => __( '用户提交申请前展示的协议说明内容，支持基础 HTML。', 'qiling' ), 'default' => __( '提交注销申请后，账号不会立即删除。管理员将在后台审核后人工处理删除。请确认已备份个人数据。', 'qiling' ), 'attrs' => array( 'rows' => 8 ) ),

                array( 'type' => 'section', 'title' => __( '阿里云短信服务', 'qiling' ), 'desc' => __( '通过阿里云SMS实现手机号验证码登录、注册、找回密码功能', 'qiling' ) ),
                array( 'id' => 'sms_enable', 'type' => 'checkbox', 'label' => __( '启用短信验证', 'qiling' ), 'desc' => __( '开启后，支持通过手机号验证码登录、注册、找回密码', 'qiling' ) ),
                array( 'id' => 'sms_access_key_id', 'type' => 'text', 'label' => 'AccessKeyId', 'desc' => __( '阿里云 AccessKeyId，在阿里云控制台获取', 'qiling' ), 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'type' => 'note', 'content' => __( '控制台地址：', 'qiling' ) . '<a href="https://ram.console.aliyun.com/manage/ak" target="_blank">ram.console.aliyun.com</a>' ),
                array( 'id' => 'sms_access_key_secret', 'type' => 'text', 'label' => 'AccessKeySecret', 'desc' => __( '阿里云 AccessKeySecret，请妥善保管', 'qiling' ), 'input_type' => 'password', 'attrs' => array( 'autocomplete' => 'off' ) ),
                array( 'id' => 'sms_sign_name', 'type' => 'text', 'label' => __( '短信签名名称', 'qiling' ), 'desc' => __( '阿里云短信签名，例如：阿里云', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '签名申请：', 'qiling' ) . '<a href="https://dysms.console.aliyun.com/domestic/text/sign" target="_blank">dysms.console.aliyun.com</a>' ),
                array( 'id' => 'sms_template_code', 'type' => 'text', 'label' => __( '短信模板CODE', 'qiling' ), 'desc' => __( '验证码短信模板CODE，模板内容需包含 ${code} 变量，例如：您的验证码为${code}，5分钟内有效', 'qiling' ), 'attrs' => array( 'placeholder' => 'SMS_xxxxx' ) ),
                array( 'id' => 'sms_default_phone_login', 'type' => 'checkbox', 'label' => __( '默认手机号登录', 'qiling' ), 'desc' => __( '登录页面默认显示手机号验证码登录，用户可切换到账号密码登录', 'qiling' ) ),
                array( 'id' => 'sms_phone_only', 'type' => 'checkbox', 'label' => __( '仅允许手机号登录', 'qiling' ), 'desc' => __( '只允许通过手机号验证码登录/注册，隐藏账号密码登录方式。若上方“注册方式”选择“实名注册”，此项会自动生效。', 'qiling' ) ),
                array( 'type' => 'note', 'content' => __( '提示：开启后，用户只能使用手机号登录，无法使用用户名/邮箱密码登录，管理员一定要先绑定手机号！', 'qiling' ), 'style' => 'color:#f59e0b;' ),
                array( 'id' => 'sms_daily_ip_limit', 'type' => 'number', 'label' => __( '每日IP发送限制', 'qiling' ), 'desc' => __( '每个IP地址每天最多可发送的短信验证码次数，防止恶意请求消耗短信配额', 'qiling' ), 'default' => '10', 'attrs' => array( 'min' => '1', 'max' => '100' ), 'suffix' => __( '次', 'qiling' ) ),
                array( 'id' => 'sms_daily_device_limit', 'type' => 'number', 'label' => __( '每日设备发送限制', 'qiling' ), 'desc' => __( '每个设备每天最多可发送的短信验证码次数，防止批量设备刷短信配额', 'qiling' ), 'default' => '20', 'attrs' => array( 'min' => '1', 'max' => '200' ), 'suffix' => __( '次', 'qiling' ) ),

                array( 'type' => 'section', 'title' => __( '自定义提示信息', 'qiling' ), 'desc' => __( '在登录/注册按钮下方显示的额外提示信息', 'qiling' ) ),
                array( 'id' => 'auth_custom_notice', 'type' => 'textarea', 'label' => __( '提示内容', 'qiling' ), 'desc' => __( '支持HTML标签。将显示在登录/注册页及弹窗的提交按钮下方。', 'qiling' ) ),
            ),

            // ========== 授权选项卡 ==========
            'license' => array(
                array( 'type' => 'custom', 'callback' => array( $this, 'render_license_tab' ) ),
            ),

            // ========== 主题说明选项卡 ==========
            'documentation' => array(
                array( 'type' => 'custom', 'callback' => array( $this, 'render_documentation_tab' ) ),
            ),

            // ========== 插件推荐选项卡 ==========
            'plugins' => array(
                array( 'type' => 'custom', 'callback' => array( $this, 'render_plugins_tab' ) ),
            ),

            // ========== 备份恢复选项卡 ==========
            'backup' => array(
                array( 'type' => 'custom', 'callback' => array( $this, 'render_backup_tab' ) ),
            ),
        );
    }
}
