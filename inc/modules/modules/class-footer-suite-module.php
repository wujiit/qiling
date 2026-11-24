<?php
/**
 * 组合式页脚套件模块。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Footer_Suite_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'footer';
        $this->icon        = 'dashicons-layout';
        $this->description = __( '整合品牌、链接、联系、二维码、友情链接和版权备案的页脚模块', 'qiling' );
    }

    public function get_id() {
        return 'footer_suite';
    }

    public function get_name() {
        return __( '页脚风格', 'qiling' );
    }

    public function get_fields() {
        return array(
            // 品牌设置
            array(
                'id'          => 'qfs_logo',
                'type'        => 'image',
                'label'       => __( 'Logo', 'qiling' ),
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_brand_title',
                'type'        => 'text',
                'label'       => __( '品牌名称', 'qiling' ),
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_brand_desc',
                'type'        => 'textarea',
                'label'       => __( '品牌简介', 'qiling' ),
                'group'       => 'brand',
                'group_label' => __( '品牌设置', 'qiling' ),
            ),

            // 链接分组设置
            array(
                'id'            => 'qfs_link_groups',
                'type'          => 'repeater',
                'label'         => __( '链接分组', 'qiling' ),
                'group'         => 'links',
                'group_label'   => __( '链接分组设置', 'qiling' ),
                'default_items' => array(
                    array(
                        'title' => __( '快速链接', 'qiling' ),
                        'links' => __( "首页|/\n关于我们|#\n联系我们|#", 'qiling' ),
                    ),
                ),
                'fields'        => array(
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '分组标题', 'qiling' ),
                    ),
                    array(
                        'id'          => 'links',
                        'type'        => 'textarea',
                        'label'       => __( '链接', 'qiling' ),
                        'description' => __( '每行：文字|链接', 'qiling' ),
                    ),
                ),
            ),
            array(
                'id'          => 'qfs_link_columns',
                'type'        => 'select',
                'label'       => __( '链接列数', 'qiling' ),
                'group'       => 'links',
                'group_label' => __( '链接分组设置', 'qiling' ),
                'options'     => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ),
                'default'     => '2',
            ),

            // 联系方式设置
            array(
                'id'          => 'qfs_contact_title',
                'type'        => 'text',
                'label'       => __( '联系标题', 'qiling' ),
                'group'       => 'contact',
                'group_label' => __( '联系方式设置', 'qiling' ),
                'default'     => __( '联系方式', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_contact_items',
                'type'        => 'textarea',
                'label'       => __( '联系内容', 'qiling' ),
                'group'       => 'contact',
                'group_label' => __( '联系方式设置', 'qiling' ),
                'description' => __( '每行：名称|内容|链接', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_badge_bg',
                'type'        => 'color',
                'label'       => __( '联系标签背景颜色', 'qiling' ),
                'group'       => 'contact',
                'group_label' => __( '联系方式设置', 'qiling' ),
                'default'     => '',
                'description' => __( '控制联系内容前的小标签，留空时跟随页面预设/全局徽章颜色。', 'qiling' ),
            ),

            // 社交与互动设置
            array(
                'id'          => 'qfs_social_title',
                'type'        => 'text',
                'label'       => __( '社交标题', 'qiling' ),
                'group'       => 'social',
                'group_label' => __( '社交与互动设置', 'qiling' ),
                'default'     => __( '关注我们', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_social_links',
                'type'        => 'textarea',
                'label'       => __( '社交链接', 'qiling' ),
                'group'       => 'social',
                'group_label' => __( '社交与互动设置', 'qiling' ),
                'description' => __( '每行：文字|链接', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_show_qr',
                'type'        => 'select',
                'label'       => __( '显示二维码', 'qiling' ),
                'group'       => 'social',
                'group_label' => __( '社交与互动设置', 'qiling' ),
                'options'     => array(
                    'yes' => __( '显示', 'qiling' ),
                    'no'  => __( '隐藏', 'qiling' ),
                ),
                'default'     => 'yes',
            ),
            array(
                'id'            => 'qfs_qr_items',
                'type'          => 'repeater',
                'label'         => __( '二维码列表', 'qiling' ),
                'group'         => 'social',
                'group_label'   => __( '社交与互动设置', 'qiling' ),
                'default_items' => array(),
                'fields'        => array(
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题', 'qiling' ),
                    ),
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '图片', 'qiling' ),
                    ),
                    array(
                        'id'    => 'text',
                        'type'  => 'text',
                        'label' => __( '说明', 'qiling' ),
                    ),
                ),
            ),

            // 友情链接设置
            array(
                'id'          => 'qfs_friend_mode',
                'type'        => 'select',
                'label'       => __( '友情链接模式', 'qiling' ),
                'group'       => 'friend',
                'group_label' => __( '友情链接设置', 'qiling' ),
                'options'     => array(
                    'theme_home'  => __( '使用主题友链，仅首页', 'qiling' ),
                    'custom_home' => __( '自定义，仅首页', 'qiling' ),
                    'custom_all'  => __( '自定义，全站', 'qiling' ),
                    'none'        => __( '不显示', 'qiling' ),
                ),
                'default'     => 'theme_home',
            ),
            array(
                'id'          => 'qfs_friend_title',
                'type'        => 'text',
                'label'       => __( '友链标题', 'qiling' ),
                'group'       => 'friend',
                'group_label' => __( '友情链接设置', 'qiling' ),
                'default'     => __( '友情链接：', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_friend_links',
                'type'        => 'textarea',
                'label'       => __( '自定义友链', 'qiling' ),
                'group'       => 'friend',
                'group_label' => __( '友情链接设置', 'qiling' ),
                'description' => __( '每行：文字|链接', 'qiling' ),
            ),

            // 底部版权与备案
            array(
                'id'          => 'qfs_bottom_text',
                'type'        => 'editor',
                'label'       => __( '版权内容', 'qiling' ),
                'group'       => 'bottom',
                'group_label' => __( '底部版权与备案', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_bottom_links',
                'type'        => 'textarea',
                'label'       => __( '底部链接', 'qiling' ),
                'group'       => 'bottom',
                'group_label' => __( '底部版权与备案', 'qiling' ),
                'description' => __( '每行：文字|链接', 'qiling' ),
            ),
            array(
                'id'          => 'qfs_show_filings',
                'type'        => 'select',
                'label'       => __( '显示备案', 'qiling' ),
                'group'       => 'bottom',
                'group_label' => __( '底部版权与备案', 'qiling' ),
                'options'     => array(
                    'yes' => __( '显示', 'qiling' ),
                    'no'  => __( '隐藏', 'qiling' ),
                ),
                'default'     => 'yes',
            ),

            // 布局与外观样式
            array(
                'id'          => 'qfs_layout',
                'type'        => 'select',
                'label'       => __( '布局', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    'columns' => __( '全宽多栏横向平铺 (按板块数自动 3~5 栏自适应展开，推荐)', 'qiling' ),
                    'compact' => __( '双栏紧凑极简', 'qiling' ),
                ),
                'default'     => 'columns',
            ),
            array(
                'id'          => 'qfs_width',
                'type'        => 'select',
                'label'       => __( '宽度', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    'contained' => __( '常规', 'qiling' ),
                    'wide'      => __( '宽屏', 'qiling' ),
                    'full'      => __( '通栏', 'qiling' ),
                ),
                'default'     => 'contained',
            ),
            array(
                'id'          => 'qfs_variant',
                'type'        => 'select',
                'label'       => __( '视觉风格', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'options'     => array(
                    'classic' => __( '经典沉稳 (标准商务与政务)', 'qiling' ),
                    'cinema'  => __( '深色影院 (沉浸式深蓝)', 'qiling' ),
                    'luxury'  => __( '黑金尊享 (Luxury Gold - 黑曜金与温润光晕)', 'qiling' ),
                    'glass'   => __( '未来微光 (Cyber Glass - 赛博渐变与透光玻璃卡片)', 'qiling' ),
                    'minimal' => __( '极简留白 (Minimal Atelier - 高级灰与克制线框)', 'qiling' ),
                ),
                'default'     => 'classic',
            ),
            array(
                'id'          => 'qfs_bg',
                'type'        => 'color',
                'label'       => __( '背景颜色', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'default'     => 'var(--qiling-color-111111)',
            ),
            array(
                'id'          => 'qfs_border_color',
                'type'        => 'color',
                'label'       => __( '边线颜色', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'default'     => 'var(--qiling-color-rgba-255-255-255-014)',
            ),
            array(
                'id'          => 'qfs_heading_color',
                'type'        => 'color',
                'label'       => __( '标题颜色', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'default'     => '',
            ),
            array(
                'id'          => 'qfs_text_color',
                'type'        => 'color',
                'label'       => __( '正文文字颜色', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'default'     => '',
            ),
            array(
                'id'          => 'qfs_muted_color',
                'type'        => 'color',
                'label'       => __( '辅助文字颜色', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'default'     => '',
            ),
            array(
                'id'          => 'qfs_accent_color',
                'type'        => 'color',
                'label'       => __( '强调色', 'qiling' ),
                'group'       => 'style',
                'group_label' => __( '布局与外观样式', 'qiling' ),
                'default'     => '',
            ),

            // 间距与高级设置
            array(
                'id'          => 'module_padding_top',
                'type'        => 'text',
                'label'       => __( '上内边距 (Padding Top)', 'qiling' ),
                'group'       => 'advanced',
                'group_label' => __( '间距与高级设置', 'qiling' ),
                'default'     => '64px',
                'description' => __( '页脚模块内容与顶部边框的距离，支持 px/rem 等单位', 'qiling' ),
            ),
            array(
                'id'          => 'module_padding_bottom',
                'type'        => 'text',
                'label'       => __( '下内边距 (Padding Bottom)', 'qiling' ),
                'group'       => 'advanced',
                'group_label' => __( '间距与高级设置', 'qiling' ),
                'default'     => '28px',
                'description' => __( '页脚模块底部版权与底部的距离，支持 px/rem 等单位', 'qiling' ),
            ),
            array(
                'id'          => 'module_margin_top',
                'type'        => 'text',
                'label'       => __( '上外边距 (Margin Top)', 'qiling' ),
                'group'       => 'advanced',
                'group_label' => __( '间距与高级设置', 'qiling' ),
                'default'     => '0px',
                'description' => __( '页脚与上方模块之间的间距，支持 px/rem 等单位', 'qiling' ),
            ),
            array(
                'id'          => 'module_margin_bottom',
                'type'        => 'text',
                'label'       => __( '下外边距 (Margin Bottom)', 'qiling' ),
                'group'       => 'advanced',
                'group_label' => __( '间距与高级设置', 'qiling' ),
                'default'     => '0px',
            ),
            array(
                'id'          => 'qfs_custom_css',
                'type'        => 'textarea',
                'label'       => __( '自定义 CSS', 'qiling' ),
                'group'       => 'advanced',
                'group_label' => __( '间距与高级设置', 'qiling' ),
                'description' => __( '可用 {{WRAPPER}} 表示当前模块', 'qiling' ),
            ),
        );
    }

    private function get_color_luminance( $color ) {
        $color = strtolower( trim( (string) $color ) );
        if ( preg_match( '/^#([a-f0-9]{6})$/i', $color, $m ) ) {
            $hex = $m[1];
            return ( 0.2126 * hexdec( substr( $hex, 0, 2 ) ) + 0.7152 * hexdec( substr( $hex, 2, 2 ) ) + 0.0722 * hexdec( substr( $hex, 4, 2 ) ) ) / 255;
        }
        if ( preg_match( '/^#([a-f0-9]{3})$/i', $color, $m ) ) {
            $hex = $m[1];
            $r = hexdec( str_repeat( $hex[0], 2 ) );
            $g = hexdec( str_repeat( $hex[1], 2 ) );
            $b = hexdec( str_repeat( $hex[2], 2 ) );
            return ( 0.2126 * $r + 0.7152 * $g + 0.0722 * $b ) / 255;
        }
        return null;
    }

    public function render( $data = array() ) {
        $data = is_array( $data ) ? $data : array();

        $layout = $this->sanitize_choice( $this->string_value( $data, 'qfs_layout', 'columns' ), array( 'columns', 'compact' ), 'columns' );
        $width  = $this->sanitize_choice( $this->string_value( $data, 'qfs_width', 'contained' ), array( 'contained', 'wide', 'full' ), 'contained' );
        $variant = $this->sanitize_choice( $this->string_value( $data, 'qfs_variant', 'classic' ), array( 'classic', 'cinema', 'luxury', 'glass', 'minimal' ), 'classic' );
        $is_video_portal = defined( 'QILING_VIDEO_PORTAL_PAGE' ) && QILING_VIDEO_PORTAL_PAGE;
        if ( $is_video_portal ) {
            $layout  = 'columns';
            $width   = 'wide';
            $variant = 'cinema';
        }

        $brand_title = $this->string_value( $data, 'qfs_brand_title', '' );
        if ( '' === $brand_title ) {
            $brand_title = $this->option_text( 'company_name', get_bloginfo( 'name' ) );
        }

        $brand_desc = $this->string_value( $data, 'qfs_brand_desc', '' );
        if ( '' === $brand_desc ) {
            $brand_desc = $this->option_text( 'company_brief', '' );
        }

        $logo = $this->resolve_media_url( $this->string_value( $data, 'qfs_logo', '' ) );
        if ( '' === $logo ) {
            $logo = $this->resolve_media_url( $this->option( 'site_logo', '' ) );
        }

        $link_groups   = $this->get_link_groups( $data );
        $link_groups   = array_values(
            array_filter(
                $link_groups,
                static function ( $group ) {
                    $title = isset( $group['title'] ) ? trim( (string) $group['title'] ) : '';
                    return ! in_array( $title, array( '内容频道', 'Content Channels' ), true );
                }
            )
        );
        $groups_count  = count( $link_groups );
        $default_cols  = $groups_count > 0 ? (string) min( 4, max( 1, $groups_count ) ) : '2';
        $link_columns  = max( 1, min( 4, absint( $this->string_value( $data, 'qfs_link_columns', $default_cols ) ) ) );
        $contact_items = $this->get_contact_items( $data );
        $social_links  = $this->parse_link_lines( $this->string_value( $data, 'qfs_social_links', '' ) );
        $qr_items      = 'no' === $this->string_value( $data, 'qfs_show_qr', 'yes' ) ? array() : $this->get_qr_items( $data );
        if ( $is_video_portal ) {
            $qr_items = array();
        }
        $friend_links  = $this->get_friend_links( $data );
        $bottom_links  = $this->parse_link_lines( $this->string_value( $data, 'qfs_bottom_links', '' ) );

        $contact_title = $this->string_value( $data, 'qfs_contact_title', __( '联系方式', 'qiling' ) );
        $social_title  = $this->string_value( $data, 'qfs_social_title', __( '关注我们', 'qiling' ) );
        $friend_title  = $this->string_value( $data, 'qfs_friend_title', __( '友情链接：', 'qiling' ) );
        $bottom_text   = $this->get_bottom_text( $data );
        $show_filings  = 'no' !== $this->string_value( $data, 'qfs_show_filings', 'yes' );

        $raw_bg = $this->string_value( $data, 'qfs_bg', '#080e1a' );
        if ( '' === trim( $raw_bg ) || 'transparent' === strtolower( trim( $raw_bg ) ) ) {
            $raw_bg = '#080e1a';
        }
        $is_light_bg = false;
        $clean_bg = strtolower( trim( $raw_bg ) );
        if ( strpos( $clean_bg, 'gradient' ) !== false ) {
            if ( preg_match_all( '/#([a-f0-9]{6})/i', $clean_bg, $bg_matches ) && ! empty( $bg_matches[1] ) ) {
                $total_lum = 0;
                foreach ( $bg_matches[1] as $hex ) {
                    $total_lum += ( 0.2126 * hexdec( substr( $hex, 0, 2 ) ) + 0.7152 * hexdec( substr( $hex, 2, 2 ) ) + 0.0722 * hexdec( substr( $hex, 4, 2 ) ) ) / 255;
                }
                $is_light_bg = ( ( $total_lum / count( $bg_matches[1] ) ) > 0.55 );
            }
        } elseif ( preg_match( '/^#([a-f0-9]{6})$/i', $clean_bg, $bg_m ) ) {
            $hex = $bg_m[1];
            $lum = ( 0.2126 * hexdec( substr( $hex, 0, 2 ) ) + 0.7152 * hexdec( substr( $hex, 2, 2 ) ) + 0.0722 * hexdec( substr( $hex, 4, 2 ) ) ) / 255;
            $is_light_bg = ( $lum > 0.55 );
        } elseif ( preg_match( '/^#([a-f0-9]{3})$/i', $clean_bg, $bg_m ) ) {
            $hex = $bg_m[1];
            $r = hexdec( str_repeat( $hex[0], 2 ) );
            $g = hexdec( str_repeat( $hex[1], 2 ) );
            $b = hexdec( str_repeat( $hex[2], 2 ) );
            $lum = ( 0.2126 * $r + 0.7152 * $g + 0.0722 * $b ) / 255;
            $is_light_bg = ( $lum > 0.55 );
        }
        $tone_class = $is_light_bg ? 'qfs-tone-light' : 'qfs-tone-dark';

        $module_id = 'footer-suite-' . uniqid();
        $classes   = array(
            'module',
            'module-footer-suite',
            'qfs-layout-' . $layout,
            'qfs-width-' . $width,
            'qfs-variant-' . $variant,
            $tone_class,
        );

        $accent_color = $this->string_value( $data, 'qfs_accent_color', '' );
        if ( '' === trim( $accent_color ) ) {
            $accent_color = 'var(--color-accent)';
        }

        $has_brand          = (bool) ( $logo || $brand_title || $brand_desc );
        $valid_link_groups  = array();
        if ( is_array( $link_groups ) ) {
            foreach ( $link_groups as $group ) {
                $gt = isset( $group['title'] ) ? trim( (string) $group['title'] ) : '';
                $gl = isset( $group['links'] ) ? $group['links'] : '';
                if ( '' !== $gt || ! empty( $gl ) ) {
                    $valid_link_groups[] = $group;
                }
            }
        }
        $link_cols_count    = count( $valid_link_groups );
        $has_contact        = (bool) ( ! empty( $contact_items ) );
        $has_social         = (bool) ( ! empty( $social_links ) );
        $has_qr             = (bool) ( ! empty( $qr_items ) );
        $has_social_col     = (bool) ( $has_social || $has_qr );

        // 真实独立板块计数：有1个板块就1栏，有2个就2栏，有3个就3栏，有4个就4栏，有5个就5栏！每个板块独占一栏，绝不挤压重叠！
        $total_cols = ( $has_brand ? 1 : 0 ) + $link_cols_count + ( $has_contact ? 1 : 0 ) + ( $has_social_col ? 1 : 0 );
        $total_cols = max( 1, min( 6, $total_cols ) );

        $grid_tracks = array();
        if ( 'compact' === $layout ) {
            // 双栏极简紧凑布局：品牌版权 + 快捷联络与导航
            $grid_tracks = array(
                'minmax(280px, 1.2fr)',
                'minmax(240px, 1fr)',
            );
        } else {
            // 严丝合缝的 1~5 栏自适应展开，每个板块独占一整栏
            if ( 1 === $total_cols ) {
                $grid_tracks[] = '1fr';
            } elseif ( 2 === $total_cols ) {
                $grid_tracks[] = 'minmax(280px, 1.3fr)';
                $grid_tracks[] = 'minmax(240px, 1fr)';
            } elseif ( 3 === $total_cols ) {
                if ( $has_brand ) {
                    $grid_tracks[] = 'minmax(260px, 1.4fr)';
                }
                for ( $i = 0; $i < $link_cols_count; $i++ ) {
                    $grid_tracks[] = 'minmax(180px, 1fr)';
                }
                if ( $has_contact ) {
                    $grid_tracks[] = 'minmax(220px, 1.15fr)';
                }
                if ( $has_social_col ) {
                    $grid_tracks[] = 'minmax(180px, 1fr)';
                }
            } elseif ( 4 === $total_cols ) {
                if ( $has_brand ) {
                    $grid_tracks[] = 'minmax(260px, 1.35fr)';
                }
                for ( $i = 0; $i < $link_cols_count; $i++ ) {
                    $grid_tracks[] = 'minmax(160px, 1fr)';
                }
                if ( $has_contact ) {
                    $grid_tracks[] = 'minmax(220px, 1.15fr)';
                }
                if ( $has_social_col ) {
                    $grid_tracks[] = 'minmax(180px, 1fr)';
                }
            } else {
                if ( $has_brand ) {
                    $grid_tracks[] = 'minmax(220px, 1.25fr)';
                }
                for ( $i = 0; $i < $link_cols_count; $i++ ) {
                    $grid_tracks[] = 'minmax(140px, 1fr)';
                }
                if ( $has_contact ) {
                    $grid_tracks[] = 'minmax(180px, 1.1fr)';
                }
                if ( $has_social_col ) {
                    $grid_tracks[] = 'minmax(160px, 1fr)';
                }
            }
        }
        $grid_template = implode( ' ', $grid_tracks );

        $fallback_text    = $is_light_bg ? '#334155' : '#94a3b8';
        $fallback_muted   = $is_light_bg ? '#64748b' : 'rgba(148, 163, 184, 0.75)';
        $fallback_heading = $is_light_bg ? '#0f172a' : '#ffffff';

        $resolved_heading = $this->inherit_footer_color_value( $data, 'qfs_heading_color', $fallback_heading, array( '#ffffff', '#fef08a', '#fef3c7', 'var(--qiling-component-footer-heading)' ) );
        $resolved_text    = $this->inherit_footer_color_value( $data, 'qfs_text_color', $fallback_text, array( '#f7f7f7', '#d6d3d1' ) );
        $resolved_muted   = $this->inherit_footer_color_value( $data, 'qfs_muted_color', $fallback_muted, array( 'rgba(255,255,255,0.68)', 'rgba(255,255,255,0.6)' ) );

        // 强对比度物理防御：浅色背景严禁白字/浅字，深色背景严禁纯黑/深黑字
        $heading_lum = $this->get_color_luminance( $resolved_heading );
        $text_lum    = $this->get_color_luminance( $resolved_text );
        $muted_lum   = $this->get_color_luminance( $resolved_muted );

        $white_like = array( '#fff', '#ffffff', 'white', '#f8fafc', '#f1f5f9', '#e2e8f0', '#f7f7f7', '#d6d3d1', '#cbd5e1', 'rgba(255,255,255,1)', 'rgb(255,255,255)' );
        $dark_like  = array( '#000', '#000000', 'black', '#0f172a', '#1e293b', '#334155', '#475569', 'rgba(0,0,0,1)', 'rgb(0,0,0)' );

        if ( $is_light_bg ) {
            if ( ( null !== $heading_lum && $heading_lum > 0.55 ) || in_array( strtolower( trim( (string) $resolved_heading ) ), $white_like, true ) ) {
                $resolved_heading = '#0f172a';
            }
            if ( ( null !== $text_lum && $text_lum > 0.55 ) || in_array( strtolower( trim( (string) $resolved_text ) ), $white_like, true ) ) {
                $resolved_text = '#334155';
            }
            if ( ( null !== $muted_lum && $muted_lum > 0.55 ) || in_array( strtolower( trim( (string) $resolved_muted ) ), $white_like, true ) ) {
                $resolved_muted = '#64748b';
            }
        } else {
            if ( ( null !== $heading_lum && $heading_lum < 0.45 ) || in_array( strtolower( trim( (string) $resolved_heading ) ), $dark_like, true ) ) {
                $resolved_heading = '#ffffff';
            }
            if ( ( null !== $text_lum && $text_lum < 0.45 ) || in_array( strtolower( trim( (string) $resolved_text ) ), $dark_like, true ) ) {
                $resolved_text = '#cbd5e1';
            }
        }

        $raw_pt = $this->string_value( $data, 'module_padding_top', '' );
        if ( '' === $raw_pt ) {
            $raw_pt = $this->string_value( $data, 'padding_top', '' );
        }
        if ( '' === $raw_pt ) {
            $raw_pt = $this->string_value( $data, 'qfs_padding_top', '64px' );
        }
        $pt = $this->sanitize_spacing_value( $raw_pt, '64px' );

        $raw_pb = $this->string_value( $data, 'module_padding_bottom', '' );
        if ( '' === $raw_pb ) {
            $raw_pb = $this->string_value( $data, 'padding_bottom', '' );
        }
        if ( '' === $raw_pb ) {
            $raw_pb = $this->string_value( $data, 'qfs_padding_bottom', '28px' );
        }
        $pb = $this->sanitize_spacing_value( $raw_pb, '28px' );

        $raw_mt = $this->string_value( $data, 'module_margin_top', '' );
        if ( '' === $raw_mt ) {
            $raw_mt = $this->string_value( $data, 'margin_top', '' );
        }
        $mt = '' !== $raw_mt ? $this->sanitize_spacing_value( $raw_mt, '0px' ) : '';

        $raw_mb = $this->string_value( $data, 'module_margin_bottom', '' );
        if ( '' === $raw_mb ) {
            $raw_mb = $this->string_value( $data, 'margin_bottom', '' );
        }
        $mb = '' !== $raw_mb ? $this->sanitize_spacing_value( $raw_mb, '0px' ) : '';

        $spacing_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( '' !== $mt ) {
            $spacing_style .= " margin-top: {$mt};";
        }
        if ( '' !== $mb ) {
            $spacing_style .= " margin-bottom: {$mb};";
        }

        $style_attr = $this->build_css_var_style(
            array(
                '--qfs-bg'            => $this->sanitize_css_color_value( $raw_bg, '#080e1a' ),
                '--qfs-text'          => $this->sanitize_css_color_value( $resolved_text, $fallback_text ),
                '--qfs-muted'         => $this->sanitize_css_color_value( $resolved_muted, $fallback_muted ),
                '--qfs-heading'       => $this->sanitize_css_color_value( $resolved_heading, $fallback_heading ),
                '--qfs-accent'        => $this->sanitize_css_color_value( $accent_color, 'var(--color-accent)' ),
                '--qiling-component-badge-bg' => $this->sanitize_css_color_value( $this->string_value( $data, 'qfs_badge_bg', '' ), '' ),
                '--qfs-border'        => $this->sanitize_css_color_value( $this->string_value( $data, 'qfs_border_color', $is_light_bg ? 'rgba(15, 23, 42, 0.08)' : 'rgba(255, 255, 255, 0.08)' ), 'rgba(255, 255, 255, 0.08)' ),
                '--qfs-pt'            => $pt,
                '--qfs-pb'            => $pb,
                '--qfs-cols'          => (string) $link_cols_count,
                '--qfs-total-cols'    => (string) $total_cols,
                '--qfs-grid-template' => $grid_template,
            )
        );

        $custom_css = $this->prepare_custom_css( $this->string_value( $data, 'qfs_custom_css', '' ), $module_id );
        if ( '' !== $custom_css ) {
            echo '<style id="' . esc_attr( $module_id . '-css' ) . '">' . "\n" . $custom_css . "\n" . '</style>';
        }
        $bg_style = strpos( $raw_bg, 'gradient' ) !== false ? "background: {$raw_bg};" : "background-color: {$raw_bg};";
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $spacing_style . ' ' . $bg_style ); ?> <?php echo esc_attr( $style_attr ); ?>">
            <div class="qfs-inner">
                <div class="qfs-main">
                    <?php if ( $has_brand ) : ?>
                        <div class="qfs-col qfs-col-brand qfs-brand">
                            <?php if ( $logo ) : ?>
                                <a class="qfs-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                                    <img class="qfs-logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
                                </a>
                            <?php endif; ?>
                            <?php if ( $brand_title ) : ?>
                                <div class="qfs-brand-title"><?php echo esc_html( $brand_title ); ?></div>
                            <?php endif; ?>
                            <?php if ( $brand_desc ) : ?>
                                <div class="qfs-brand-desc"><?php echo wp_kses_post( wpautop( $brand_desc ) ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $valid_link_groups ) ) : ?>
                        <?php foreach ( $valid_link_groups as $group_idx => $group ) : ?>
                            <?php
                            $group_title = isset( $group['title'] ) ? (string) $group['title'] : '';
                            $links       = isset( $group['links'] ) ? $group['links'] : array();
                            if ( '' === $group_title && empty( $links ) ) {
                                continue;
                            }
                            ?>
                            <nav class="qfs-col qfs-col-links qfs-link-group qfs-link-group-<?php echo esc_attr( $group_idx + 1 ); ?>"<?php echo $group_title ? ' aria-label="' . esc_attr( $group_title ) . '"' : ''; ?>>
                                <?php if ( $group_title ) : ?>
                                    <h3 class="qfs-section-title"><?php echo esc_html( $group_title ); ?></h3>
                                <?php endif; ?>
                                <?php $this->render_link_list( $links, 'qfs-list qfs-link-list', '_self' ); ?>
                            </nav>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ( $has_contact ) : ?>
                        <div class="qfs-col qfs-col-contact qfs-connect-main">
                            <?php if ( $contact_title ) : ?>
                                <h3 class="qfs-section-title"><?php echo esc_html( $contact_title ); ?></h3>
                            <?php endif; ?>
                            <ul class="qfs-list qfs-contact-list">
                                <?php foreach ( $contact_items as $item ) : ?>
                                    <li>
                                        <?php if ( ! empty( $item['label'] ) ) : ?>
                                            <span class="qfs-contact-label"><?php echo esc_html( $item['label'] ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $item['url'] ) ) : ?>
                                            <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
                                        <?php else : ?>
                                            <span><?php echo esc_html( $item['text'] ); ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( $has_social_col ) : ?>
                        <div class="qfs-col qfs-col-social qfs-connect-social">
                            <?php if ( $social_title ) : ?>
                                <h3 class="qfs-section-title"><?php echo esc_html( $social_title ); ?></h3>
                            <?php endif; ?>
                            <?php if ( ! empty( $social_links ) ) : ?>
                                <?php $this->render_link_list( $social_links, 'qfs-list qfs-social-list', '_blank' ); ?>
                            <?php endif; ?>
                            <?php if ( ! empty( $qr_items ) ) : ?>
                                <div class="qfs-qr-grid">
                                    <?php foreach ( $qr_items as $item ) : ?>
                                        <div class="qfs-qr-card">
                                            <?php if ( ! empty( $item['image'] ) ) : ?>
                                                <img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( ! empty( $item['title'] ) ? $item['title'] : get_bloginfo( 'name' ) ); ?>" />
                                            <?php endif; ?>
                                            <?php if ( ! empty( $item['title'] ) ) : ?>
                                                <strong><?php echo esc_html( $item['title'] ); ?></strong>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $item['text'] ) ) : ?>
                                                <span><?php echo esc_html( $item['text'] ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $friend_links ) ) : ?>
                    <div class="qfs-friend-links">
                        <?php if ( $friend_title ) : ?>
                            <span class="qfs-friend-title"><?php echo esc_html( $friend_title ); ?></span>
                        <?php endif; ?>
                        <div class="qfs-friend-list">
                            <?php foreach ( $friend_links as $link ) : ?>
                                <?php $this->render_anchor( $link, '_blank', true ); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="qfs-bottom">
                    <div class="qfs-copyright"><?php echo wp_kses_post( $bottom_text ); ?></div>
                    <?php if ( ! empty( $bottom_links ) || $show_filings ) : ?>
                        <div class="qfs-bottom-extra">
                            <?php if ( ! empty( $bottom_links ) ) : ?>
                                <div class="qfs-bottom-links">
                                    <?php foreach ( $bottom_links as $link ) : ?>
                                        <?php $this->render_anchor( $link, '_self', false ); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $show_filings && class_exists( '\Developer_Starter\China\China_Features' ) ) : ?>
                                <div class="qfs-filings">
                                    <?php \Developer_Starter\China\China_Features::render_footer_filings(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function get_link_groups( $data ) {
        $groups = isset( $data['qfs_link_groups'] ) && is_array( $data['qfs_link_groups'] ) ? $data['qfs_link_groups'] : array();
        $normalized = array();

        foreach ( $groups as $group ) {
            if ( ! is_array( $group ) ) {
                continue;
            }

            $title = isset( $group['title'] ) ? trim( (string) $group['title'] ) : '';
            $links = $this->parse_link_lines( isset( $group['links'] ) ? (string) $group['links'] : '' );
            if ( '' === $title && empty( $links ) ) {
                continue;
            }

            $normalized[] = array(
                'title' => $title,
                'links' => $links,
            );
        }

        if ( ! empty( $normalized ) ) {
            return $normalized;
        }

        $quick_links = $this->option( 'footer_quick_links', array() );
        if ( is_array( $quick_links ) && ! empty( $quick_links ) ) {
            $links = array();
            foreach ( $quick_links as $link ) {
                if ( ! is_array( $link ) ) {
                    continue;
                }
                $label = isset( $link['text'] ) ? $this->translate_text( (string) $link['text'] ) : '';
                $url   = isset( $link['url'] ) ? (string) $link['url'] : '#';
                if ( '' !== trim( $label ) ) {
                    $links[] = $this->normalize_link( $label, $url );
                }
            }

            if ( ! empty( $links ) ) {
                return array(
                    array(
                        'title' => $this->option_text( 'footer_links_title', __( '快速链接', 'qiling' ) ),
                        'links' => $links,
                    ),
                );
            }
        }

        return array(
            array(
                'title' => __( '快速链接', 'qiling' ),
                'links' => array(
                    $this->normalize_link( __( '首页', 'qiling' ), home_url( '/' ) ),
                ),
            ),
        );
    }

    private function get_contact_items( $data ) {
        $raw = $this->string_value( $data, 'qfs_contact_items', '' );
        if ( '' !== trim( $raw ) ) {
            return $this->parse_contact_lines( $raw );
        }

        $items = array();
        $phone = $this->option_text( 'company_phone', '' );
        if ( '' !== $phone ) {
            $items[] = array(
                'label' => __( '电话', 'qiling' ),
                'text'  => $phone,
                'url'   => 'tel:' . preg_replace( '/[^0-9\+]/', '', $phone ),
            );
        }

        $qq = $this->option_text( 'company_qq', '' );
        if ( '' !== $qq ) {
            $items[] = array(
                'label' => 'QQ',
                'text'  => $qq,
                'url'   => function_exists( 'developer_starter_get_qq_contact_link' ) ? developer_starter_get_qq_contact_link( $qq ) : '',
            );
        }

        $email = $this->option_text( 'company_email', '' );
        if ( '' !== $email ) {
            $items[] = array(
                'label' => __( '邮箱', 'qiling' ),
                'text'  => $email,
                'url'   => 'mailto:' . $email,
            );
        }

        $address = $this->option_text( 'company_address', '' );
        if ( '' !== $address ) {
            $items[] = array(
                'label' => __( '地址', 'qiling' ),
                'text'  => $address,
                'url'   => '',
            );
        }

        $hours = $this->option_text( 'company_working_hours', '' );
        if ( '' !== $hours ) {
            $items[] = array(
                'label' => __( '时间', 'qiling' ),
                'text'  => $hours,
                'url'   => '',
            );
        }

        return $items;
    }

    private function get_qr_items( $data ) {
        $items = isset( $data['qfs_qr_items'] ) && is_array( $data['qfs_qr_items'] ) ? $data['qfs_qr_items'] : array();
        $normalized = array();

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $image = $this->resolve_media_url( isset( $item['image'] ) ? (string) $item['image'] : '' );
            if ( '' === $image ) {
                continue;
            }

            $normalized[] = array(
                'title' => isset( $item['title'] ) ? sanitize_text_field( (string) $item['title'] ) : '',
                'image' => $image,
                'text'  => isset( $item['text'] ) ? sanitize_text_field( (string) $item['text'] ) : '',
            );
        }

        if ( ! empty( $normalized ) ) {
            return $normalized;
        }

        $contact_qr = $this->resolve_media_url( $this->option( 'company_wechat_qrcode', '' ) );
        if ( '' !== $contact_qr ) {
            $normalized[] = array(
                'title' => __( '联系微信', 'qiling' ),
                'image' => $contact_qr,
                'text'  => __( '扫码添加微信', 'qiling' ),
            );
        }

        $wechat_qr = $this->resolve_media_url( $this->option( 'wechat_qrcode', '' ) );
        if ( $wechat_qr === $contact_qr ) {
            $wechat_qr = '';
        }
        if ( '' !== $wechat_qr ) {
            $normalized[] = array(
                'title' => __( '微信公众号', 'qiling' ),
                'image' => $wechat_qr,
                'text'  => $this->option_text( 'wechat_qr_text', __( '扫码关注公众号', 'qiling' ) ),
            );
        }

        $douyin_qr = $this->resolve_media_url( $this->option( 'douyin_qrcode', '' ) );
        if ( '' !== $douyin_qr ) {
            $normalized[] = array(
                'title' => __( '抖音', 'qiling' ),
                'image' => $douyin_qr,
                'text'  => $this->option_text( 'douyin_qr_text', __( '扫码关注抖音', 'qiling' ) ),
            );
        }

        return $normalized;
    }

    private function get_friend_links( $data ) {
        $mode = $this->sanitize_choice( $this->string_value( $data, 'qfs_friend_mode', 'theme_home' ), array( 'theme_home', 'custom_home', 'custom_all', 'none' ), 'theme_home' );
        if ( 'none' === $mode ) {
            return array();
        }

        if ( in_array( $mode, array( 'theme_home', 'custom_home' ), true ) && function_exists( 'is_front_page' ) && ! is_front_page() ) {
            return array();
        }

        if ( 'theme_home' === $mode ) {
            if ( ! $this->option( 'friend_links_enable', '' ) ) {
                return array();
            }

            $theme_links = $this->option( 'friend_links', array() );
            if ( ! is_array( $theme_links ) ) {
                return array();
            }

            $links = array();
            foreach ( $theme_links as $link ) {
                if ( ! is_array( $link ) ) {
                    continue;
                }
                $label = isset( $link['text'] ) ? $this->translate_text( (string) $link['text'] ) : '';
                $url   = isset( $link['url'] ) ? (string) $link['url'] : '#';
                if ( '' !== trim( $label ) ) {
                    $links[] = $this->normalize_link( $label, $url, '_blank' );
                }
            }
            return $links;
        }

        return $this->parse_link_lines( $this->string_value( $data, 'qfs_friend_links', '' ), '_blank' );
    }

    private function get_bottom_text( $data ) {
        $bottom_text = $this->string_value( $data, 'qfs_bottom_text', '' );
        if ( '' !== trim( $bottom_text ) ) {
            return $bottom_text;
        }

        $copyright = $this->option_text( 'footer_copyright', '' );
        if ( '' !== $copyright ) {
            return $copyright;
        }

        return '&copy; ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '. ' . esc_html__( '版权所有', 'qiling' ) . '.';
    }

    private function parse_link_lines( $value, $target = '_self' ) {
        $links = array();
        $lines = preg_split( '/\r\n|\r|\n|\|\|/', (string) $value );
        if ( ! is_array( $lines ) ) {
            return $links;
        }

        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( '' === $line ) {
                continue;
            }

            $parts = array_map( 'trim', explode( '|', $line ) );
            $label = isset( $parts[0] ) ? $parts[0] : '';
            $url   = isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] : '#';
            $item_target = isset( $parts[2] ) && '_blank' === $parts[2] ? '_blank' : $target;
            if ( '' !== $label ) {
                $links[] = $this->normalize_link( $label, $url, $item_target );
            }
        }

        return $links;
    }

    private function parse_contact_lines( $value ) {
        $items = array();
        $lines = preg_split( '/\r\n|\r|\n|\|\|/', (string) $value );
        if ( ! is_array( $lines ) ) {
            return $items;
        }

        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( '' === $line ) {
                continue;
            }

            $parts = array_map( 'trim', explode( '|', $line ) );
            $label = isset( $parts[0] ) ? $parts[0] : '';
            $text  = isset( $parts[1] ) ? $parts[1] : '';
            $url   = isset( $parts[2] ) ? $parts[2] : '';
            if ( '' === $text && '' !== $label ) {
                $text  = $label;
                $label = '';
            }
            if ( '' !== $text ) {
                $items[] = array(
                    'label' => sanitize_text_field( $label ),
                    'text'  => sanitize_text_field( $text ),
                    'url'   => esc_url_raw( $url, array( 'http', 'https', 'mailto', 'tel' ) ),
                );
            }
        }

        return $items;
    }

    private function render_link_list( $links, $class, $default_target ) {
        if ( empty( $links ) || ! is_array( $links ) ) {
            return;
        }

        echo '<ul class="' . esc_attr( $class ) . '">';
        foreach ( $links as $link ) {
            echo '<li>';
            $this->render_anchor( $link, $default_target, false );
            echo '</li>';
        }
        echo '</ul>';
    }

    private function render_anchor( $link, $default_target = '_self', $friend = false ) {
        if ( ! is_array( $link ) || empty( $link['label'] ) ) {
            return;
        }

        $url = isset( $link['url'] ) && '' !== (string) $link['url'] ? (string) $link['url'] : '#';
        $target = isset( $link['target'] ) && '_blank' === $link['target'] ? '_blank' : $default_target;
        $rel = '_blank' === $target ? ( $friend ? 'external nofollow noopener noreferrer' : 'noopener noreferrer' ) : '';

        echo '<a href="' . esc_url( $url ) . '"';
        if ( '_blank' === $target ) {
            echo ' target="_blank"';
        }
        if ( '' !== $rel ) {
            echo ' rel="' . esc_attr( $rel ) . '"';
        }
        echo '>' . esc_html( (string) $link['label'] ) . '</a>';
    }

    private function normalize_link( $label, $url, $target = '_self' ) {
        $target = '_blank' === $target ? '_blank' : '_self';

        return array(
            'label'  => sanitize_text_field( (string) $label ),
            'url'    => '' !== trim( (string) $url ) ? trim( (string) $url ) : '#',
            'target' => $target,
        );
    }

    private function string_value( $data, $key, $default = '' ) {
        if ( ! is_array( $data ) || ! isset( $data[ $key ] ) || ! is_scalar( $data[ $key ] ) ) {
            return (string) $default;
        }

        return (string) $data[ $key ];
    }

    private function option( $key, $default = '' ) {
        return function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( $key, $default ) : $default;
    }

    private function option_text( $key, $default = '' ) {
        $value = $this->option( $key, $default );
        if ( ! is_scalar( $value ) ) {
            return (string) $default;
        }

        return $this->translate_text( (string) $value );
    }

    private function translate_text( $text ) {
        return function_exists( 'developer_starter_translate_theme_option_text' )
            ? developer_starter_translate_theme_option_text( (string) $text )
            : (string) $text;
    }

    private function resolve_media_url( $value ) {
        if ( function_exists( 'developer_starter_get_media_url' ) ) {
            return (string) developer_starter_get_media_url( $value );
        }

        return is_scalar( $value ) ? (string) $value : '';
    }

    private function sanitize_choice( $value, $allowed, $default ) {
        $value = sanitize_key( (string) $value );
        return in_array( $value, $allowed, true ) ? $value : $default;
    }

    private function sanitize_spacing_value( $value, $default ) {
        if ( class_exists( Module_Manager::class ) ) {
            $value = Module_Manager::sanitize_spacing_value( $value );
            return '' !== $value ? $value : $default;
        }

        $value = trim( wp_strip_all_tags( (string) $value ) );
        return preg_match( '/[;{}<>]/', $value ) ? $default : $value;
    }

    private function sanitize_css_color_value( $value, $default ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return $default;
        }

        $lower = strtolower( $value );
        if ( in_array( $lower, array( 'transparent', 'currentcolor', 'inherit', 'initial' ), true ) ) {
            return $lower;
        }

        if ( function_exists( 'sanitize_hex_color' ) ) {
            $hex = sanitize_hex_color( $value );
            if ( '' !== $hex && null !== $hex ) {
                return $hex;
            }
        } elseif ( preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\([0-9\.\,\s%]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(?:linear-gradient|radial-gradient|conic-gradient)\([a-z0-9#\-\+\*\/%\.,\s\(\)]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9\-_]+\)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    private function inherit_footer_color_value( $data, $key, $inherit_value, array $legacy_defaults ) {
        $value = trim( $this->string_value( $data, $key, '' ) );
        if ( '' === $value ) {
            return $inherit_value;
        }

        $normalized_value    = preg_replace( '/\s+/', '', strtolower( $value ) );
        $normalized_defaults = array_map(
            static function ( $item ) {
                return preg_replace( '/\s+/', '', strtolower( (string) $item ) );
            },
            $legacy_defaults
        );

        return in_array( $normalized_value, $normalized_defaults, true ) ? $inherit_value : $value;
    }

    private function build_css_var_style( $variables ) {
        $declarations = array();
        foreach ( (array) $variables as $property => $value ) {
            $property = trim( (string) $property );
            $value    = trim( (string) $value );
            if ( '' === $property || '' === $value || ! preg_match( '/^--(?:qfs|qiling-component)-[a-z0-9-]+$/', $property ) || preg_match( '/[;{}<>]/', $value ) ) {
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
        $clean_css = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $css );
        if ( is_string( $clean_css ) ) {
            $css = $clean_css;
        }
        $css = str_replace( '{{WRAPPER}}', '#' . $module_id, (string) $css );

        return trim( $css );
    }
}
