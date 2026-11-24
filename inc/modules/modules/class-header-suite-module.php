<?php
/**
 * 顶部导航全功能套件模块 (Header Suite)。
 *
 * 允许每一个页面完全独立自定义顶部导航栏的一切：
 * 品牌Logo/标语、导航菜单项（标题、图标、链接、徽标、下拉列表）、
 * 搜索、电话、夜间模式、独立行动号召按钮(CTA)、
 * 独立背景色/文字色/悬停色、吸顶固定与首屏透明模式。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Header_Suite_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'header';
        $this->icon        = 'dashicons-menu-alt3';
        $this->description = __( '独立自定义顶部导航栏模块，支持品牌Logo/标语、独立菜单项、徽标、搜索、电话、夜间切换、CTA按钮及专属配色', 'qiling' );
    }

    public function get_id() {
        return 'header_suite';
    }

    public function get_name() {
        return __( '顶部导航套件 (Header Suite)', 'qiling' );
    }

    public function get_fields() {
        $menus = array( '' => __( '-- 使用独立自定义导航项 --', 'qiling' ) );
        if ( function_exists( 'wp_get_nav_menus' ) ) {
            $nav_menus = wp_get_nav_menus();
            if ( ! empty( $nav_menus ) && ! is_wp_error( $nav_menus ) ) {
                foreach ( $nav_menus as $menu_obj ) {
                    $menus[ (string) $menu_obj->term_id ] = $menu_obj->name;
                }
            }
        }

        return array(
            // 1. 品牌设置
            array(
                'id'          => 'qhs_logo',
                'type'        => 'image',
                'label'       => __( '品牌 Logo 图片', 'qiling' ),
                'description' => __( '留空自动使用主题全局设置的 Logo', 'qiling' ),
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_logo_dark',
                'type'        => 'image',
                'label'       => __( '透明/深色模式 Logo (备用)', 'qiling' ),
                'description' => __( '在首屏透明或深色背景下显示的备选 Logo，留空使用默认 Logo', 'qiling' ),
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_logo_height',
                'type'        => 'text',
                'label'       => __( 'Logo 高度 (px)', 'qiling' ),
                'default'     => '40',
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_brand_title',
                'type'        => 'text',
                'label'       => __( '品牌名称 / 标题', 'qiling' ),
                'description' => __( '留空自动使用站点标题', 'qiling' ),
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_brand_slogan',
                'type'        => 'text',
                'label'       => __( '品牌副标题 / Slogan', 'qiling' ),
                'description' => __( '显示在 Logo/名称旁边的宣传文案，留空不显示', 'qiling' ),
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_brand_url',
                'type'        => 'text',
                'label'       => __( 'Logo 点击链接', 'qiling' ),
                'default'     => '/',
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),

            // 2. 导航菜单设置
            array(
                'id'          => 'qhs_menu_source',
                'type'        => 'select',
                'label'       => __( '菜单数据来源', 'qiling' ),
                'group'       => 'nav',
                'group_label' => __( '导航菜单设置', 'qiling' ),
                'options'     => array(
                    'custom' => __( '独立自定义导航项 (推荐，自由配置一切)', 'qiling' ),
                    'menu'   => __( '读取 WordPress 系统菜单', 'qiling' ),
                ),
                'default'     => 'custom',
            ),
            array(
                'id'          => 'qhs_menu_id',
                'type'        => 'select',
                'label'       => __( '选择系统菜单', 'qiling' ),
                'description' => __( '仅在数据来源为“读取系统菜单”时有效', 'qiling' ),
                'group'       => 'nav',
                'group_label' => __( '导航菜单设置', 'qiling' ),
                'options'     => $menus,
                'default'     => '',
            ),
            array(
                'id'            => 'qhs_nav_items',
                'type'          => 'repeater',
                'label'         => __( '独立导航项列表', 'qiling' ),
                'group'         => 'nav',
                'group_label'   => __( '导航菜单设置', 'qiling' ),
                'default_items' => array(
                    array(
                        'title'       => __( '首页', 'qiling' ),
                        'link'        => '/',
                        'icon'        => '',
                        'badge'       => '',
                        'badge_color' => '#ef4444',
                        'target'      => '_self',
                    ),
                    array(
                        'title'       => __( '产品中心', 'qiling' ),
                        'link'        => '/products/',
                        'icon'        => '',
                        'badge'       => 'HOT',
                        'badge_color' => '#ef4444',
                        'target'      => '_self',
                    ),
                    array(
                        'title'       => __( '解决方案', 'qiling' ),
                        'link'        => '/solutions/',
                        'icon'        => '',
                        'badge'       => '',
                        'badge_color' => '#3b82f6',
                        'target'      => '_self',
                    ),
                    array(
                        'title'       => __( '新闻中心', 'qiling' ),
                        'link'        => '/news/',
                        'icon'        => '',
                        'badge'       => '',
                        'badge_color' => '#10b981',
                        'target'      => '_self',
                    ),
                    array(
                        'title'       => __( '关于我们', 'qiling' ),
                        'link'        => '/about/',
                        'icon'        => '',
                        'badge'       => '',
                        'badge_color' => '#6366f1',
                        'target'      => '_self',
                    ),
                ),
                'fields'        => array(
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '菜单文字', 'qiling' ),
                    ),
                    array(
                        'id'    => 'link',
                        'type'  => 'text',
                        'label' => __( '跳转链接 (支持 URL 或 #锚点)', 'qiling' ),
                    ),
                    array(
                        'id'          => 'icon',
                        'type'        => 'text',
                        'label'       => __( '前置图标 (Class 或 Emoji)', 'qiling' ),
                        'description' => __( '例如 dashicons-star-filled 或 🚀', 'qiling' ),
                    ),
                    array(
                        'id'          => 'badge',
                        'type'        => 'text',
                        'label'       => __( '徽标角标 (如 HOT / NEW)', 'qiling' ),
                        'description' => __( '留空不显示角标', 'qiling' ),
                    ),
                    array(
                        'id'      => 'badge_color',
                        'type'    => 'color',
                        'label'   => __( '徽标背景色', 'qiling' ),
                        'default' => '#ef4444',
                    ),
                    array(
                        'id'      => 'target',
                        'type'    => 'select',
                        'label'   => __( '打开方式', 'qiling' ),
                        'options' => array(
                            '_self'  => __( '当前窗口打开', 'qiling' ),
                            '_blank' => __( '新窗口打开', 'qiling' ),
                        ),
                        'default' => '_self',
                    ),
                    array(
                        'id'          => 'dropdown_items',
                        'type'        => 'textarea',
                        'label'       => __( '二级子菜单 (每行: 名称|链接)', 'qiling' ),
                        'description' => __( '例如：企业软件|/software/
云原生平台|/cloud/', 'qiling' ),
                    ),
                ),
            ),
            array(
                'id'          => 'qhs_menu_align',
                'type'        => 'select',
                'label'       => __( '导航菜单对齐位置', 'qiling' ),
                'group'       => 'nav',
                'group_label' => __( '导航菜单设置', 'qiling' ),
                'options'     => array(
                    'left'   => __( '居左 (紧随 Logo)', 'qiling' ),
                    'center' => __( '居中 (版心居中)', 'qiling' ),
                    'right'  => __( '居右 (靠拢右侧部件)', 'qiling' ),
                ),
                'default'     => 'left',
            ),

            // 3. 右侧功能部件
            array(
                'id'          => 'qhs_show_search',
                'type'        => 'select',
                'label'       => __( '搜索入口', 'qiling' ),
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
                'options'     => array(
                    '1' => __( '显示', 'qiling' ),
                    '0' => __( '隐藏', 'qiling' ),
                ),
                'default'     => '1',
            ),
            array(
                'id'          => 'qhs_search_mode',
                'type'        => 'select',
                'label'       => __( '搜索展示形态', 'qiling' ),
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
                'options'     => array(
                    'popup'  => __( '弹层搜索 (点击放大镜弹出)', 'qiling' ),
                    'inline' => __( '内嵌搜索框 (常驻输入框)', 'qiling' ),
                ),
                'default'     => 'popup',
            ),
            array(
                'id'          => 'qhs_search_placeholder',
                'type'        => 'text',
                'label'       => __( '搜索框提示词', 'qiling' ),
                'default'     => __( '搜索关键词...', 'qiling' ),
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_show_phone',
                'type'        => 'select',
                'label'       => __( '服务电话', 'qiling' ),
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
                'options'     => array(
                    '0' => __( '隐藏', 'qiling' ),
                    '1' => __( '显示', 'qiling' ),
                ),
                'default'     => '0',
            ),
            array(
                'id'          => 'qhs_phone_number',
                'type'        => 'text',
                'label'       => __( '电话号码', 'qiling' ),
                'default'     => '400-888-8888',
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_phone_link',
                'type'        => 'text',
                'label'       => __( '电话点击拨打链接', 'qiling' ),
                'description' => __( '留空自动生成 tel:号码', 'qiling' ),
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_show_theme_toggle',
                'type'        => 'select',
                'label'       => __( '夜间/明暗主题切换按钮', 'qiling' ),
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
                'options'     => array(
                    '1' => __( '显示', 'qiling' ),
                    '0' => __( '隐藏', 'qiling' ),
                ),
                'default'     => '1',
            ),
            array(
                'id'          => 'qhs_show_user_action',
                'type'        => 'select',
                'label'       => __( '用户中心 / 登录入口', 'qiling' ),
                'group'       => 'actions',
                'group_label' => __( '功能部件设置', 'qiling' ),
                'options'     => array(
                    '1' => __( '显示', 'qiling' ),
                    '0' => __( '隐藏', 'qiling' ),
                ),
                'default'     => '1',
            ),

            // 4. 行动号召按钮 (CTA)
            array(
                'id'          => 'qhs_show_cta',
                'type'        => 'select',
                'label'       => __( '启用专属 CTA 按钮', 'qiling' ),
                'group'       => 'cta',
                'group_label' => __( '行动按钮设置', 'qiling' ),
                'options'     => array(
                    '1' => __( '启用', 'qiling' ),
                    '0' => __( '关闭', 'qiling' ),
                ),
                'default'     => '1',
            ),
            array(
                'id'          => 'qhs_cta_text',
                'type'        => 'text',
                'label'       => __( '按钮文案', 'qiling' ),
                'default'     => __( '免费咨询', 'qiling' ),
                'group'       => 'cta',
                'group_label' => __( '行动按钮设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_cta_link',
                'type'        => 'text',
                'label'       => __( '按钮链接', 'qiling' ),
                'default'     => '/contact/',
                'group'       => 'cta',
                'group_label' => __( '行动按钮设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_cta_target',
                'type'        => 'select',
                'label'       => __( '按钮打开方式', 'qiling' ),
                'group'       => 'cta',
                'group_label' => __( '行动按钮设置', 'qiling' ),
                'options'     => array(
                    '_self'  => __( '当前窗口', 'qiling' ),
                    '_blank' => __( '新窗口打开', 'qiling' ),
                ),
                'default'     => '_self',
            ),
            array(
                'id'          => 'qhs_cta_style',
                'type'        => 'select',
                'label'       => __( '按钮设计风格', 'qiling' ),
                'group'       => 'cta',
                'group_label' => __( '行动按钮设置', 'qiling' ),
                'options'     => array(
                    'primary'  => __( '实色强调 (高对比焦点)', 'qiling' ),
                    'outline'  => __( '精细线框 (轻量高雅)', 'qiling' ),
                    'gradient' => __( '微质感渐变', 'qiling' ),
                ),
                'default'     => 'primary',
            ),
            array(
                'id'          => 'qhs_cta_bg',
                'type'        => 'color',
                'label'       => __( '按钮背景色 (留空跟随主题色)', 'qiling' ),
                'group'       => 'cta',
                'group_label' => __( '行动按钮设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_cta_color',
                'type'        => 'color',
                'label'       => __( '按钮文字色 (留空自动计算)', 'qiling' ),
                'group'       => 'cta',
                'group_label' => __( '行动按钮设置', 'qiling' ),
            ),

            // 5. 布局与外观样式
            array(
                'id'          => 'qhs_layout',
                'type'        => 'select',
                'label'       => __( '导航结构版式', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    'default' => __( '标准经典 (Logo - 导航 - 功能区/CTA)', 'qiling' ),
                    'center'  => __( '导航居中 (Logo左侧，导航版心居中)', 'qiling' ),
                    'split'   => __( '对称分布 (Logo居中，导航在两侧)', 'qiling' ),
                    'compact' => __( '精简紧凑 (电商/个人站轻量)', 'qiling' ),
                ),
                'default'     => 'default',
            ),
            array(
                'id'          => 'qhs_container_width',
                'type'        => 'select',
                'label'       => __( '版心容器宽度', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    'contained' => __( '标准容器 (居中宽屏 1280px/1440px)', 'qiling' ),
                    'full'      => __( '全宽铺满 (两侧弹性边距)', 'qiling' ),
                ),
                'default'     => 'contained',
            ),
            array(
                'id'          => 'qhs_height',
                'type'        => 'text',
                'label'       => __( '导航栏高度 (px)', 'qiling' ),
                'default'     => '72',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_sticky',
                'type'        => 'select',
                'label'       => __( '吸顶悬浮固定 (Sticky)', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    '1' => __( '开启吸顶 (滚动时固定在顶部)', 'qiling' ),
                    '0' => __( '关闭 (随页面正常向上滚动)', 'qiling' ),
                ),
                'default'     => '1',
            ),
            array(
                'id'          => 'qhs_sticky_blur',
                'type'        => 'select',
                'label'       => __( '毛玻璃磨砂质感 (Blur Filter)', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    '1' => __( '开启毛玻璃透光质感', 'qiling' ),
                    '0' => __( '关闭 (纯色实底)', 'qiling' ),
                ),
                'default'     => '1',
            ),
            array(
                'id'          => 'qhs_transparent',
                'type'        => 'select',
                'label'       => __( '首屏透明悬浮模式 (Hero Transparent)', 'qiling' ),
                'description' => __( '开启后导航绝对定位浮于首屏Banner之上，向下滚动后自动变实色', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    '0' => __( '关闭 (正常实体导航背景)', 'qiling' ),
                    '1' => __( '开启 (首屏透明悬浮)', 'qiling' ),
                ),
                'default'     => '0',
            ),
            array(
                'id'          => 'qhs_bg_color',
                'type'        => 'color',
                'label'       => __( '导航栏背景色', 'qiling' ),
                'default'     => '#ffffff',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_text_color',
                'type'        => 'color',
                'label'       => __( '菜单文字主颜色', 'qiling' ),
                'default'     => '#1e293b',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_hover_color',
                'type'        => 'color',
                'label'       => __( '菜单悬停 / 激活颜色', 'qiling' ),
                'default'     => '#2563eb',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_hover_bg',
                'type'        => 'color',
                'label'       => __( '菜单悬停胶囊背景色', 'qiling' ),
                'default'     => 'rgba(37,99,235,0.06)',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_border_color',
                'type'        => 'color',
                'label'       => __( '底部分割线颜色 (留空透明)', 'qiling' ),
                'default'     => 'rgba(0,0,0,0.06)',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_transparent_text_color',
                'type'        => 'color',
                'label'       => __( '首屏透明状态下的文字颜色', 'qiling' ),
                'description' => __( '仅在首屏透明悬浮模式未滚动时生效', 'qiling' ),
                'default'     => '#ffffff',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_dark_bg_color',
                'type'        => 'color',
                'label'       => __( '暗黑模式导航栏背景色', 'qiling' ),
                'default'     => '#0f172a',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_dark_text_color',
                'type'        => 'color',
                'label'       => __( '暗黑模式菜单文字颜色', 'qiling' ),
                'default'     => '#f8fafc',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_dark_hover_color',
                'type'        => 'color',
                'label'       => __( '暗黑模式悬停高亮颜色', 'qiling' ),
                'default'     => '#60a5fa',
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
            ),

            // 6. 高级自定义设置
            array(
                'id'          => 'qhs_custom_class',
                'type'        => 'text',
                'label'       => __( '额外自定义 CSS 类名', 'qiling' ),
                'group'       => 'advanced',
                'group_label' => __( '间距与高级设置', 'qiling' ),
            ),
            array(
                'id'          => 'qhs_custom_css',
                'type'        => 'textarea',
                'label'       => __( '模块专属自定义 CSS', 'qiling' ),
                'description' => __( '支持 {{WRAPPER}} 代表当前模块外层', 'qiling' ),
                'group'       => 'advanced',
                'group_label' => __( '间距与高级设置', 'qiling' ),
            ),
        );
    }

    /**
     * 模块前端渲染入口。
     *
     * @param array $data 模块数据。
     * @return void
     */
    public function render( $data = array() ) {
        $data = is_array( $data ) ? $data : array();

        $module_uid = 'qhs-' . substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 8 );

        // 品牌数据
        $logo       = $this->resolve_media_url( $this->string_value( $data, 'qhs_logo', '' ) );
        if ( '' === $logo ) {
            $logo = $this->resolve_media_url( $this->option( 'site_logo', '' ) );
        }
        $logo_dark  = $this->resolve_media_url( $this->string_value( $data, 'qhs_logo_dark', '' ) );
        if ( '' === $logo_dark ) {
            $logo_dark = $this->resolve_media_url( $this->option( 'site_logo_dark', '' ) );
        }
        $logo_height = absint( $this->string_value( $data, 'qhs_logo_height', '40' ) );
        if ( $logo_height <= 0 || $logo_height > 120 ) {
            $logo_height = 40;
        }

        $brand_title = $this->string_value( $data, 'qhs_brand_title', '' );
        if ( '' === $brand_title ) {
            $brand_title = $this->option_text( 'company_name', get_bloginfo( 'name' ) );
        }
        $brand_slogan = $this->string_value( $data, 'qhs_brand_slogan', '' );
        $brand_url    = $this->string_value( $data, 'qhs_brand_url', '/' );
        if ( '' === $brand_url ) {
            $brand_url = home_url( '/' );
        }

        // 导航数据
        $menu_source = $this->sanitize_choice( $this->string_value( $data, 'qhs_menu_source', 'custom' ), array( 'custom', 'menu' ), 'custom' );
        $menu_id     = absint( $this->string_value( $data, 'qhs_menu_id', '0' ) );
        $nav_items   = $this->get_nav_items( $data, $menu_source, $menu_id );
        $menu_align  = $this->sanitize_choice( $this->string_value( $data, 'qhs_menu_align', 'left' ), array( 'left', 'center', 'right' ), 'left' );

        // 功能部件
        $show_search        = '0' !== $this->string_value( $data, 'qhs_show_search', '1' );
        $search_mode        = $this->sanitize_choice( $this->string_value( $data, 'qhs_search_mode', 'popup' ), array( 'popup', 'inline' ), 'popup' );
        $search_placeholder = $this->string_value( $data, 'qhs_search_placeholder', __( '搜索关键词...', 'qiling' ) );

        $show_phone   = '1' === $this->string_value( $data, 'qhs_show_phone', '0' );
        $phone_number = $this->string_value( $data, 'qhs_phone_number', '' );
        $phone_link   = $this->string_value( $data, 'qhs_phone_link', '' );
        if ( '' === $phone_link && '' !== $phone_number ) {
            $phone_link = 'tel:' . preg_replace( '/[^0-9+]/', '', $phone_number );
        }

        $show_theme_toggle = '0' !== $this->string_value( $data, 'qhs_show_theme_toggle', '1' );
        $show_user_action  = '0' !== $this->string_value( $data, 'qhs_show_user_action', '1' );

        // CTA 按钮
        $show_cta   = '0' !== $this->string_value( $data, 'qhs_show_cta', '1' );
        $cta_text   = $this->string_value( $data, 'qhs_cta_text', __( '免费咨询', 'qiling' ) );
        $cta_link   = $this->string_value( $data, 'qhs_cta_link', '/contact/' );
        $cta_target = $this->sanitize_choice( $this->string_value( $data, 'qhs_cta_target', '_self' ), array( '_self', '_blank' ), '_self' );
        $cta_style  = $this->sanitize_choice( $this->string_value( $data, 'qhs_cta_style', 'primary' ), array( 'primary', 'outline', 'gradient' ), 'primary' );
        $cta_bg     = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_cta_bg', '' ) );
        $cta_color  = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_cta_color', '' ) );

        // 样式布局参数
        $layout          = $this->sanitize_choice( $this->string_value( $data, 'qhs_layout', 'default' ), array( 'default', 'center', 'split', 'compact' ), 'default' );
        $container_width = $this->sanitize_choice( $this->string_value( $data, 'qhs_container_width', 'contained' ), array( 'contained', 'full' ), 'contained' );
        $height          = absint( $this->string_value( $data, 'qhs_height', '72' ) );
        if ( $height <= 40 || $height > 150 ) {
            $height = 72;
        }
        $is_sticky       = '0' !== $this->string_value( $data, 'qhs_sticky', '1' );
        $is_sticky_blur  = '0' !== $this->string_value( $data, 'qhs_sticky_blur', '1' );
        $is_transparent  = '1' === $this->string_value( $data, 'qhs_transparent', '0' );

        $bg_color        = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_bg_color', '#ffffff' ), '#ffffff' );
        $text_color      = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_text_color', '#1e293b' ), '#1e293b' );
        $hover_color     = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_hover_color', '#2563eb' ), '#2563eb' );
        $hover_bg        = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_hover_bg', 'rgba(37,99,235,0.06)' ), 'rgba(37,99,235,0.06)' );
        $border_color    = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_border_color', 'rgba(0,0,0,0.06)' ), 'rgba(0,0,0,0.06)' );
        $trans_text_col  = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_transparent_text_color', '#ffffff' ), '#ffffff' );
        $dark_bg_color   = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_dark_bg_color', '#0f172a' ), '#0f172a' );
        $dark_text_color = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_dark_text_color', '#f8fafc' ), '#f8fafc' );
        $dark_hover_col  = $this->sanitize_css_color_value( $this->string_value( $data, 'qhs_dark_hover_color', '#60a5fa' ), '#60a5fa' );

        $custom_class = trim( sanitize_text_field( $this->string_value( $data, 'qhs_custom_class', '' ) ) );
        $custom_css   = $this->prepare_custom_css( $this->string_value( $data, 'qhs_custom_css', '' ), $module_uid );

        // 组装 CSS 变量
        $css_vars = array(
            '--qhs-height'             => $height . 'px',
            '--qhs-logo-height'        => $logo_height . 'px',
            '--qhs-bg-color'           => $bg_color,
            '--qhs-text-color'         => $text_color,
            '--qhs-hover-color'        => $hover_color,
            '--qhs-hover-bg'           => $hover_bg,
            '--qhs-border-color'       => $border_color,
            '--qhs-trans-text'         => $trans_text_col,
            '--qhs-dark-bg-color'      => $dark_bg_color,
            '--qhs-dark-text-color'    => $dark_text_color,
            '--qhs-dark-hover-color'   => $dark_hover_col,
        );
        if ( '' !== $cta_bg ) {
            $css_vars['--qhs-cta-bg'] = $cta_bg;
        }
        if ( '' !== $cta_color ) {
            $css_vars['--qhs-cta-color'] = $cta_color;
        }

        $style_attr = $this->build_css_var_style( $css_vars );

        $wrapper_classes = array(
            'qhs-wrapper',
            'qhs-layout-' . $layout,
            'qhs-align-' . $menu_align,
            'qhs-width-' . $container_width,
        );
        if ( $is_sticky ) {
            $wrapper_classes[] = 'qhs-is-sticky';
        }
        if ( $is_sticky_blur ) {
            $wrapper_classes[] = 'qhs-has-blur';
        }
        if ( $is_transparent ) {
            $wrapper_classes[] = 'qhs-is-transparent';
        }
        if ( '' !== $custom_class ) {
            $wrapper_classes[] = $custom_class;
        }

        $search_action = function_exists( 'developer_starter_get_search_form_action_url' )
            ? developer_starter_get_search_form_action_url()
            : home_url( '/' );
        ?>

        <?php if ( '' !== $custom_css ) : ?>
            <style id="<?php echo esc_attr( $module_uid ); ?>-css"><?php echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
        <?php endif; ?>

        <header id="<?php echo esc_attr( $module_uid ); ?>" class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
            <div class="qhs-inner qhs-container-<?php echo esc_attr( $container_width ); ?>">

                <?php if ( 'split' === $layout ) : ?>
                    <?php
                    $split_mid = (int) ceil( count( $nav_items ) / 2 );
                    $nav_left  = array_slice( $nav_items, 0, $split_mid );
                    $nav_right = array_slice( $nav_items, $split_mid );
                    ?>
                    <nav class="qhs-nav qhs-nav-split qhs-nav-split-left" aria-label="<?php esc_attr_e( '主导航左侧', 'qiling' ); ?>">
                        <?php $this->render_nav_list( $nav_left ); ?>
                    </nav>

                    <div class="qhs-brand">
                        <?php $this->render_brand_content( $brand_url, $logo, $logo_dark, $brand_title, $brand_slogan, $is_transparent ); ?>
                    </div>

                    <nav class="qhs-nav qhs-nav-split qhs-nav-split-right" aria-label="<?php esc_attr_e( '主导航右侧', 'qiling' ); ?>">
                        <?php $this->render_nav_list( $nav_right ); ?>
                    </nav>
                <?php else : ?>
                    <div class="qhs-brand">
                        <?php $this->render_brand_content( $brand_url, $logo, $logo_dark, $brand_title, $brand_slogan, $is_transparent ); ?>
                    </div>

                    <nav class="qhs-nav" aria-label="<?php esc_attr_e( '主导航', 'qiling' ); ?>">
                        <?php $this->render_nav_list( $nav_items ); ?>
                    </nav>
                <?php endif; ?>

                <div class="qhs-actions">
                    <?php if ( $show_search && 'inline' === $search_mode ) : ?>
                        <form role="search" method="get" class="qhs-search-inline" action="<?php echo esc_url( $search_action ); ?>">
                            <input type="search" class="qhs-search-input" placeholder="<?php echo esc_attr( $search_placeholder ); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
                            <button type="submit" class="qhs-search-submit" aria-label="<?php esc_attr_e( '搜索', 'qiling' ); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </button>
                        </form>
                    <?php elseif ( $show_search ) : ?>
                        <button type="button" class="qhs-action-btn qhs-search-toggle" aria-label="<?php esc_attr_e( '打开搜索', 'qiling' ); ?>" data-header-search-trigger="1">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </button>
                    <?php endif; ?>

                    <?php if ( $show_phone && '' !== $phone_number ) : ?>
                        <a href="<?php echo esc_url( $phone_link ); ?>" class="qhs-phone-link" title="<?php echo esc_attr( $phone_number ); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <span class="qhs-phone-text"><?php echo esc_html( $phone_number ); ?></span>
                        </a>
                    <?php endif; ?>

                    <?php if ( $show_theme_toggle ) : ?>
                        <button type="button" class="qhs-action-btn darkmode-toggle theme-toggle-btn" id="darkmode-toggle" aria-label="<?php esc_attr_e( '切换夜间模式', 'qiling' ); ?>" title="<?php esc_attr_e( '切换夜间模式', 'qiling' ); ?>">
                            <span class="icon-sun theme-toggle-icon theme-toggle-icon--light" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                            </span>
                            <span class="icon-moon theme-toggle-icon theme-toggle-icon--dark" aria-hidden="true" style="display:none;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                            </span>
                        </button>
                    <?php endif; ?>

                    <?php if ( $show_user_action ) : ?>
                        <?php
                        $user_url = is_user_logged_in()
                            ? ( function_exists( 'developer_starter_get_user_center_url' ) ? developer_starter_get_user_center_url() : admin_url( 'profile.php' ) )
                            : wp_login_url();
                        ?>
                        <a href="<?php echo esc_url( $user_url ); ?>" class="qhs-action-btn qhs-user-btn" aria-label="<?php esc_attr_e( '个人中心', 'qiling' ); ?>" title="<?php echo is_user_logged_in() ? esc_attr__( '个人中心', 'qiling' ) : esc_attr__( '登录 / 注册', 'qiling' ); ?>">
                            <?php if ( is_user_logged_in() ) : ?>
                                <?php echo get_avatar( get_current_user_id(), 28, '', '', array( 'class' => 'qhs-user-avatar' ) ); ?>
                            <?php else : ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( $show_cta && '' !== $cta_text ) : ?>
                        <a href="<?php echo esc_url( $cta_link ); ?>" target="<?php echo esc_attr( $cta_target ); ?>" class="qhs-cta-btn qhs-cta-style-<?php echo esc_attr( $cta_style ); ?>">
                            <?php echo esc_html( $cta_text ); ?>
                        </a>
                    <?php endif; ?>

                    <button type="button" class="qhs-mobile-toggle" aria-label="<?php esc_attr_e( '打开菜单', 'qiling' ); ?>" aria-expanded="false" data-qhs-target="#<?php echo esc_attr( $module_uid ); ?>-drawer">
                        <span class="qhs-mobile-icon-bar"></span>
                        <span class="qhs-mobile-icon-bar"></span>
                        <span class="qhs-mobile-icon-bar"></span>
                    </button>
                </div>
            </div>

            <!-- 移动端侧滑抽屉 -->
            <div id="<?php echo esc_attr( $module_uid ); ?>-drawer" class="qhs-mobile-drawer" aria-hidden="true">
                <div class="qhs-drawer-backdrop" data-qhs-close></div>
                <div class="qhs-drawer-content">
                    <div class="qhs-drawer-header">
                        <div class="qhs-drawer-brand">
                            <?php if ( '' !== $logo ) : ?>
                                <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $brand_title ); ?>" class="qhs-drawer-logo" />
                            <?php else : ?>
                                <span class="qhs-drawer-title"><?php echo esc_html( $brand_title ); ?></span>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="qhs-drawer-close" aria-label="<?php esc_attr_e( '关闭菜单', 'qiling' ); ?>" data-qhs-close>&times;</button>
                    </div>

                    <div class="qhs-drawer-body">
                        <ul class="qhs-mobile-nav-list">
                            <?php foreach ( $nav_items as $item ) : ?>
                                <?php
                                $title      = isset( $item['title'] ) ? $item['title'] : '';
                                $link       = isset( $item['link'] ) ? $item['link'] : '#';
                                $icon       = isset( $item['icon'] ) ? trim( (string) $item['icon'] ) : '';
                                $badge      = isset( $item['badge'] ) ? trim( (string) $item['badge'] ) : '';
                                $badge_col  = isset( $item['badge_color'] ) ? trim( (string) $item['badge_color'] ) : '#ef4444';
                                $target     = isset( $item['target'] ) ? $item['target'] : '_self';
                                $dropdowns  = isset( $item['dropdown'] ) && is_array( $item['dropdown'] ) ? $item['dropdown'] : array();
                                ?>
                                <li class="qhs-mobile-nav-item<?php echo ! empty( $dropdowns ) ? ' has-dropdown' : ''; ?>">
                                    <div class="qhs-mobile-link-row">
                                        <a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="qhs-mobile-nav-link">
                                            <?php if ( '' !== $icon ) : ?>
                                                <?php if ( 0 === strpos( $icon, 'dashicons-' ) ) : ?>
                                                    <i class="dashicons <?php echo esc_attr( $icon ); ?>"></i>
                                                <?php else : ?>
                                                    <span class="qhs-icon-custom"><?php echo esc_html( $icon ); ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <span><?php echo esc_html( $title ); ?></span>
                                            <?php if ( '' !== $badge ) : ?>
                                                <span class="qhs-badge" style="background-color: <?php echo esc_attr( $badge_col ); ?>"><?php echo esc_html( $badge ); ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <?php if ( ! empty( $dropdowns ) ) : ?>
                                            <button type="button" class="qhs-mobile-expand-btn" aria-label="<?php esc_attr_e( '展开子菜单', 'qiling' ); ?>">+</button>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ( ! empty( $dropdowns ) ) : ?>
                                        <ul class="qhs-mobile-sub-list">
                                            <?php foreach ( $dropdowns as $sub ) : ?>
                                                <li>
                                                    <a href="<?php echo esc_url( $sub['link'] ); ?>"><?php echo esc_html( $sub['title'] ); ?></a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php
                        // 移动端微信二维码卡片与转化按钮（继承全局后台配置）
                        $mobile_qr_enable = (bool) developer_starter_get_option( 'mobile_drawer_qrcode_enable', '' );
                        $mobile_qr_img    = developer_starter_get_option( 'mobile_drawer_qrcode_img', '' );
                        if ( is_array( $mobile_qr_img ) ) {
                            $mobile_qr_img = ! empty( $mobile_qr_img['url'] ) ? $mobile_qr_img['url'] : '';
                        }
                        $mobile_qr_title   = developer_starter_get_option( 'mobile_drawer_qrcode_title', __( '添加官方微信', 'qiling' ) );
                        $mobile_qr_desc    = developer_starter_get_option( 'mobile_drawer_qrcode_desc', __( '长按二维码识别添加客服微信', 'qiling' ) );
                        $mobile_cta_enable = (bool) developer_starter_get_option( 'mobile_drawer_cta_enable', '' );
                        $mobile_cta_text   = developer_starter_get_option( 'mobile_drawer_cta_text', __( '立即在线咨询', 'qiling' ) );
                        $mobile_cta_link   = developer_starter_get_option( 'mobile_drawer_cta_link', '' );
                        $mobile_cta_style  = developer_starter_get_option( 'mobile_drawer_cta_style', 'primary' );
                        ?>
                        <?php if ( $mobile_qr_enable && '' !== $mobile_qr_img ) : ?>
                            <div class="mobile-drawer-qrcode-card qhs-drawer-qrcode-card">
                                <div class="mobile-qrcode-img-wrap">
                                    <img src="<?php echo esc_url( $mobile_qr_img ); ?>" alt="<?php echo esc_attr( $mobile_qr_title ); ?>" class="mobile-qrcode-img" loading="lazy" />
                                </div>
                                <div class="mobile-qrcode-meta">
                                    <div class="mobile-qrcode-title"><?php echo esc_html( $mobile_qr_title ); ?></div>
                                    <div class="mobile-qrcode-desc"><?php echo esc_html( $mobile_qr_desc ); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $mobile_cta_enable && '' !== $mobile_cta_link ) : ?>
                            <div class="mobile-drawer-cta-wrap qhs-drawer-cta-wrap">
                                <a href="<?php echo esc_url( $mobile_cta_link ); ?>" class="mobile-drawer-cta-btn mobile-drawer-cta--<?php echo esc_attr( $mobile_cta_style ); ?>">
                                    <span><?php echo esc_html( $mobile_cta_text ); ?></span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ( ( $show_phone && '' !== $phone_number ) || ( $show_cta && '' !== $cta_text ) ) : ?>
                        <div class="qhs-drawer-footer">
                            <?php if ( $show_phone && '' !== $phone_number ) : ?>
                                <a href="<?php echo esc_url( $phone_link ); ?>" class="qhs-drawer-phone">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <span><?php echo esc_html( $phone_number ); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ( $show_cta && '' !== $cta_text ) : ?>
                                <a href="<?php echo esc_url( $cta_link ); ?>" target="<?php echo esc_attr( $cta_target ); ?>" class="qhs-cta-btn qhs-cta-btn--block qhs-cta-style-<?php echo esc_attr( $cta_style ); ?>">
                                    <?php echo esc_html( $cta_text ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <script>
            (function() {
                var headerEl = document.getElementById(<?php echo wp_json_encode( $module_uid ); ?>);
                if (!headerEl) return;

                var isSticky = headerEl.classList.contains('qhs-is-sticky');
                var isTrans = headerEl.classList.contains('qhs-is-transparent');

                if (isSticky || isTrans) {
                    var onScroll = function() {
                        var top = window.pageYOffset || document.documentElement.scrollTop;
                        if (top > 24) {
                            headerEl.classList.add('qhs-is-scrolled');
                        } else {
                            headerEl.classList.remove('qhs-is-scrolled');
                        }
                    };
                    window.addEventListener('scroll', onScroll, { passive: true });
                    onScroll();
                }

                var toggleBtn = headerEl.querySelector('.qhs-mobile-toggle');
                var drawer = document.getElementById(<?php echo wp_json_encode( $module_uid . '-drawer' ); ?>);
                if (toggleBtn && drawer) {
                    var openDrawer = function() {
                        drawer.classList.add('is-active');
                        drawer.setAttribute('aria-hidden', 'false');
                        toggleBtn.setAttribute('aria-expanded', 'true');
                        document.body.style.overflow = 'hidden';
                    };
                    var closeDrawer = function() {
                        drawer.classList.remove('is-active');
                        drawer.setAttribute('aria-hidden', 'true');
                        toggleBtn.setAttribute('aria-expanded', 'false');
                        document.body.style.overflow = '';
                    };
                    toggleBtn.addEventListener('click', openDrawer);
                    drawer.querySelectorAll('[data-qhs-close]').forEach(function(el) {
                        el.addEventListener('click', closeDrawer);
                    });
                }

                // 移动端折叠子菜单展开/收起
                if (drawer) {
                    drawer.querySelectorAll('.qhs-mobile-expand-btn').forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            var item = this.closest('.qhs-mobile-nav-item');
                            if (item) {
                                item.classList.toggle('is-expanded');
                                this.textContent = item.classList.contains('is-expanded') ? '−' : '+';
                            }
                        });
                    });
                }
            })();
            </script>
        </header>
        <?php
    }

    /**
     * 渲染品牌信息（Logo、标题、Slogan）。
     */
    private function render_brand_content( $brand_url, $logo, $logo_dark, $brand_title, $brand_slogan, $is_transparent ) {
        ?>
        <a href="<?php echo esc_url( $brand_url ); ?>" class="qhs-logo-link" rel="home">
            <?php if ( '' !== $logo ) : ?>
                <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $brand_title ); ?>" class="qhs-logo-img qhs-logo-default" />
                <?php if ( '' !== $logo_dark ) : ?>
                    <img src="<?php echo esc_url( $logo_dark ); ?>" alt="<?php echo esc_attr( $brand_title ); ?>" class="qhs-logo-img qhs-logo-dark" />
                <?php endif; ?>
            <?php endif; ?>
            <div class="qhs-brand-text">
                <?php if ( '' !== $brand_title ) : ?>
                    <span class="qhs-brand-title"><?php echo esc_html( $brand_title ); ?></span>
                <?php endif; ?>
                <?php if ( '' !== $brand_slogan ) : ?>
                    <span class="qhs-brand-slogan"><?php echo esc_html( $brand_slogan ); ?></span>
                <?php endif; ?>
            </div>
        </a>
        <?php
    }

    /**
     * 渲染桌面端导航菜单列表。
     */
    private function render_nav_list( $items ) {
        if ( empty( $items ) || ! is_array( $items ) ) {
            return;
        }
        $current_url = function_exists( 'developer_starter_get_current_url' )
            ? developer_starter_get_current_url()
            : ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/' );
        ?>
        <ul class="qhs-nav-list">
            <?php foreach ( $items as $item ) : ?>
                <?php
                $title      = isset( $item['title'] ) ? $item['title'] : '';
                $link       = isset( $item['link'] ) ? $item['link'] : '#';
                $icon       = isset( $item['icon'] ) ? trim( (string) $item['icon'] ) : '';
                $badge      = isset( $item['badge'] ) ? trim( (string) $item['badge'] ) : '';
                $badge_col  = isset( $item['badge_color'] ) ? trim( (string) $item['badge_color'] ) : '#ef4444';
                $target     = isset( $item['target'] ) ? $item['target'] : '_self';
                $dropdowns  = isset( $item['dropdown'] ) && is_array( $item['dropdown'] ) ? $item['dropdown'] : array();

                $is_active = ( '/' !== $link && false !== strpos( $current_url, $link ) );
                if ( '/' === $link && ( '/' === $current_url || '' === $current_url ) ) {
                    $is_active = true;
                }
                ?>
                <li class="qhs-nav-item<?php echo ! empty( $dropdowns ) ? ' has-dropdown' : ''; ?><?php echo $is_active ? ' is-active' : ''; ?>">
                    <a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="qhs-nav-link">
                        <?php if ( '' !== $icon ) : ?>
                            <?php if ( 0 === strpos( $icon, 'dashicons-' ) ) : ?>
                                <i class="dashicons <?php echo esc_attr( $icon ); ?>"></i>
                            <?php else : ?>
                                <span class="qhs-icon-custom"><?php echo esc_html( $icon ); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <span class="qhs-nav-text"><?php echo esc_html( $title ); ?></span>
                        <?php if ( '' !== $badge ) : ?>
                            <span class="qhs-badge" style="background-color: <?php echo esc_attr( $badge_col ); ?>"><?php echo esc_html( $badge ); ?></span>
                        <?php endif; ?>
                        <?php if ( ! empty( $dropdowns ) ) : ?>
                            <svg class="qhs-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?php endif; ?>
                    </a>

                    <?php if ( ! empty( $dropdowns ) ) : ?>
                        <ul class="qhs-dropdown-menu">
                            <?php foreach ( $dropdowns as $sub ) : ?>
                                <li class="qhs-dropdown-item">
                                    <a href="<?php echo esc_url( $sub['link'] ); ?>" class="qhs-dropdown-link"><?php echo esc_html( $sub['title'] ); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    /**
     * 获取导航项列表（支持自定义 repeater 与 WordPress 菜单双模态）。
     */
    private function get_nav_items( $data, $menu_source, $menu_id ) {
        if ( 'menu' === $menu_source && $menu_id > 0 ) {
            $menu_items = wp_get_nav_menu_items( $menu_id );
            if ( ! empty( $menu_items ) && ! is_wp_error( $menu_items ) ) {
                $items = array();
                $id_map = array();
                foreach ( $menu_items as $mi ) {
                    $entry = array(
                        'id'          => $mi->ID,
                        'title'       => $mi->title,
                        'link'        => $mi->url,
                        'icon'        => '',
                        'badge'       => '',
                        'badge_color' => '#ef4444',
                        'target'      => $mi->target ? $mi->target : '_self',
                        'dropdown'    => array(),
                    );
                    if ( empty( $mi->menu_item_parent ) ) {
                        $items[ $mi->ID ] = $entry;
                    } else {
                        if ( isset( $items[ $mi->menu_item_parent ] ) ) {
                            $items[ $mi->menu_item_parent ]['dropdown'][] = array(
                                'title' => $mi->title,
                                'link'  => $mi->url,
                            );
                        }
                    }
                }
                return array_values( $items );
            }
        }

        // 自定义 repeater 项
        $raw_items = isset( $data['qhs_nav_items'] ) && is_array( $data['qhs_nav_items'] )
            ? $data['qhs_nav_items']
            : array();

        if ( empty( $raw_items ) ) {
            $fields = $this->get_fields();
            foreach ( $fields as $f ) {
                if ( isset( $f['id'] ) && 'qhs_nav_items' === $f['id'] && ! empty( $f['default_items'] ) ) {
                    $raw_items = $f['default_items'];
                    break;
                }
            }
        }

        $items = array();
        foreach ( $raw_items as $item ) {
            if ( ! is_array( $item ) || empty( $item['title'] ) ) {
                continue;
            }
            $title      = trim( (string) $item['title'] );
            $link       = isset( $item['link'] ) ? trim( (string) $item['link'] ) : '#';
            $icon       = isset( $item['icon'] ) ? trim( (string) $item['icon'] ) : '';
            $badge      = isset( $item['badge'] ) ? trim( (string) $item['badge'] ) : '';
            $badge_col  = isset( $item['badge_color'] ) ? trim( (string) $item['badge_color'] ) : '#ef4444';
            $target     = isset( $item['target'] ) ? trim( (string) $item['target'] ) : '_self';
            $dropdown   = array();

            if ( ! empty( $item['dropdown_items'] ) ) {
                $lines = explode( "
", (string) $item['dropdown_items'] );
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( '' === $line ) continue;
                    $parts = explode( '|', $line, 2 );
                    $sub_title = trim( $parts[0] );
                    $sub_link  = isset( $parts[1] ) ? trim( $parts[1] ) : '#';
                    if ( '' !== $sub_title ) {
                        $dropdown[] = array(
                            'title' => $sub_title,
                            'link'  => $sub_link,
                        );
                    }
                }
            }

            $items[] = array(
                'title'       => $title,
                'link'        => $link,
                'icon'        => $icon,
                'badge'       => $badge,
                'badge_color' => $badge_col,
                'target'      => $target,
                'dropdown'    => $dropdown,
            );
        }

        return $items;
    }

    private function string_value( $data, $key, $default = '' ) {
        if ( ! is_array( $data ) || ! array_key_exists( $key, $data ) ) {
            return $default;
        }
        $val = $data[ $key ];
        return is_scalar( $val ) ? trim( (string) $val ) : $default;
    }

    private function option( $name, $default = '' ) {
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return developer_starter_get_option( $name, $default );
        }
        return $default;
    }

    private function option_text( $name, $default = '' ) {
        $val = $this->option( $name, $default );
        if ( function_exists( 'developer_starter_translate_theme_option_text' ) ) {
            $val = developer_starter_translate_theme_option_text( $val );
        }
        return is_scalar( $val ) ? (string) $val : $default;
    }

    private function resolve_media_url( $value ) {
        if ( empty( $value ) ) {
            return '';
        }
        if ( is_numeric( $value ) && function_exists( 'wp_get_attachment_image_url' ) ) {
            $url = wp_get_attachment_image_url( absint( $value ), 'full' );
            if ( $url ) {
                return $url;
            }
        }
        if ( is_string( $value ) && preg_match( '#^(https?:)?//#i', $value ) ) {
            return esc_url( $value );
        }
        if ( is_string( $value ) && 0 === strpos( $value, '/' ) ) {
            return esc_url( home_url( $value ) );
        }
        return is_string( $value ) ? esc_url( $value ) : '';
    }

    private function sanitize_choice( $value, $allowed, $default ) {
        return in_array( $value, $allowed, true ) ? $value : $default;
    }

    private function sanitize_css_color_value( $value, $default = '' ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return $default;
        }
        if ( preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6}|[a-f0-9]{8})$/i', $value ) ) {
            return $value;
        }
        if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\(\s*[0-9.% ,\/]+\s*\)$/i', $value ) ) {
            return $value;
        }
        if ( 'transparent' === strtolower( $value ) ) {
            return 'transparent';
        }
        return $default;
    }

    private function build_css_var_style( $variables ) {
        $declarations = array();
        foreach ( (array) $variables as $property => $value ) {
            $property = trim( (string) $property );
            $value    = trim( (string) $value );
            if ( '' === $property || '' === $value || ! preg_match( '/^--qhs-[a-z0-9-]+$/', $property ) || preg_match( '/[;{}<>]/', $value ) ) {
                continue;
            }
            $declarations[] = $property . ': ' . $value;
        }
        return implode( '; ', $declarations );
    }

    private function prepare_custom_css( $css, $module_id ) {
        $css = trim( wp_strip_all_tags( (string) $css ) );
        if ( '' === $css ) {
            return '';
        }
        $css = str_ireplace( array( '</style', '<style', '</script', '<script' ), '', $css );
        $clean_css = preg_replace( '/[\x00--]/', '', $css );
        if ( is_string( $clean_css ) ) {
            $css = $clean_css;
        }
        $css = str_replace( '{{WRAPPER}}', '#' . $module_id, (string) $css );
        return trim( $css );
    }
}
