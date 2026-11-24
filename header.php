<?php
/**
 * 启灵主题通用页头模板
 *
 * 页头结构由可被子主题覆盖的模板片段组成，便于子主题按需自定义局部区域。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$header_menu_layout        = developer_starter_get_option( 'header_menu_layout', 'default' );
$transparent_home          = developer_starter_get_option( 'header_transparent_home', '' );
$hide_search               = developer_starter_get_option( 'hide_search_button', '' );
$hide_phone                = developer_starter_get_option( 'hide_phone_header', '' );
$header_search_mode        = (string) developer_starter_get_option( 'header_search_mode', 'icon' );
$header_search_mode        = in_array( $header_search_mode, array( 'icon', 'form' ), true ) ? $header_search_mode : 'icon';
$show_search               = ! $hide_search;
$show_phone                = ! $hide_phone;
$show_search_form          = $show_search && 'form' === $header_search_mode;
$header_search_action      = function_exists( 'developer_starter_get_search_form_action_url' ) ? developer_starter_get_search_form_action_url() : home_url( '/' );
$header_search_use_rewrite = developer_starter_get_option( 'search_rewrite', '' );
$pc_logo_slogan_text       = trim( (string) developer_starter_get_option( 'pc_logo_slogan_text', '' ) );
$show_pc_logo_divider      = ( '' !== $pc_logo_slogan_text ) && ( '1' === (string) developer_starter_get_option( 'pc_logo_slogan_show_divider', '1' ) );
$header_style_vars         = '';
if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
    $header_style_vars = \Developer_Starter\Core\Design_Tokens::get_header_runtime_style_vars();
}
if ( function_exists( 'developer_starter_get_current_page_visual_skin' ) && function_exists( 'developer_starter_get_page_visual_skin_style_vars' ) ) {
    $page_skin_header_vars = developer_starter_get_page_visual_skin_style_vars( developer_starter_get_current_page_visual_skin(), 'header' );
    if ( '' !== $page_skin_header_vars ) {
        $header_style_vars .= $page_skin_header_vars;
    }
}
if ( function_exists( 'developer_starter_get_current_page_visual_style_vars' ) ) {
    $page_visual_style_vars = developer_starter_get_current_page_visual_style_vars( 'header' );
    if ( '' !== $page_visual_style_vars ) {
        $header_style_vars .= $page_visual_style_vars;
    }
}

$allowed_header_menu_layouts = array( 'default', 'menu_center', 'logo_center' );
$header_menu_layout          = in_array( $header_menu_layout, $allowed_header_menu_layouts, true ) ? $header_menu_layout : 'default';
$header_classes              = array( 'site-header' );
$header_classes[]            = 'header-layout-' . sanitize_html_class( $header_menu_layout );
$header_menu_layout_class    = sanitize_html_class( str_replace( '_', '-', $header_menu_layout ) );
if ( 'header-layout-' . sanitize_html_class( $header_menu_layout ) !== 'header-layout-' . $header_menu_layout_class ) {
    $header_classes[] = 'header-layout-' . $header_menu_layout_class;
}
$is_home                     = is_front_page();

$queried_object_id                 = ( is_page() || is_singular() ) ? absint( get_queried_object_id() ) : 0;
$page_transparent_header           = $queried_object_id > 0 ? get_post_meta( $queried_object_id, '_qiling_transparent_header', true ) : '';
$page_transparent_header_enabled   = '1' === (string) $page_transparent_header;
$page_transparent_header_disabled  = '0' === (string) $page_transparent_header;
$has_transparent_header_hero_first = false;
$transparent_header_tone           = '';

// 预先探测页面首屏模块类型与色调
if ( is_singular( 'page' ) && $queried_object_id > 0 ) {
    $hero_module_ids = class_exists( '\Developer_Starter\Modules\Module_Manager' )
        ? \Developer_Starter\Modules\Module_Manager::get_hero_module_ids( 'transparent_header' )
        : array();

    $page_modules = function_exists( 'developer_starter_get_page_modules_data' )
        ? developer_starter_get_page_modules_data( $queried_object_id )
        : get_post_meta( $queried_object_id, '_developer_starter_modules', true );

    if ( is_array( $page_modules ) && ! empty( $page_modules ) ) {
        foreach ( $page_modules as $module_entry ) {
            if ( ! is_array( $module_entry ) || empty( $module_entry['type'] ) ) {
                continue;
            }

            $candidate_data = isset( $module_entry['data'] ) && is_array( $module_entry['data'] ) ? $module_entry['data'] : array();
            if (
                class_exists( '\Developer_Starter\Core\Module_Advanced_Style_Service' )
                && \Developer_Starter\Core\Module_Advanced_Style_Service::get_instance()->module_is_hidden( $candidate_data )
            ) {
                continue;
            }

            $first_module_type                 = sanitize_key( (string) $module_entry['type'] );
            $has_transparent_header_hero_first = in_array( $first_module_type, $hero_module_ids, true );
            $first_module_data                 = $candidate_data;

            $title_color_fields = array(
                'brand_banner_pro' => 'bb_title_color',
                'dynamic_banner'   => 'db_title_color',
                'hero_search'      => 'hs_title_color',
                'resource_hero_pro'=> 'rh_title_color',
                'resume_hero'      => 'rh_title_color',
                'interact_hero'    => 'text_color',
            );
            $hero_title_color = isset( $title_color_fields[ $first_module_type ], $first_module_data[ $title_color_fields[ $first_module_type ] ] )
                ? trim( (string) $first_module_data[ $title_color_fields[ $first_module_type ] ] )
                : '';
            if ( preg_match( '/^#([a-f0-9]{6})$/i', $hero_title_color, $hero_title_match ) ) {
                $hero_title_hex = $hero_title_match[1];
                $hero_title_rgb = array(
                    hexdec( substr( $hero_title_hex, 0, 2 ) ),
                    hexdec( substr( $hero_title_hex, 2, 2 ) ),
                    hexdec( substr( $hero_title_hex, 4, 2 ) ),
                );
                $hero_title_luminance = ( 0.2126 * $hero_title_rgb[0] + 0.7152 * $hero_title_rgb[1] + 0.0722 * $hero_title_rgb[2] ) / 255;
                $transparent_header_tone = $hero_title_luminance < 0.58 ? 'light' : 'dark';
            } elseif ( 'brand_banner_pro' === $first_module_type ) {
                $bb_bg_type = isset( $first_module_data['bb_bg_type'] ) ? sanitize_key( (string) $first_module_data['bb_bg_type'] ) : 'gradient';
                $bb_overlay = isset( $first_module_data['bb_bg_overlay'] ) && is_numeric( $first_module_data['bb_bg_overlay'] ) ? (float) $first_module_data['bb_bg_overlay'] : 0.12;
                if ( 'image' === $bb_bg_type && $bb_overlay >= 0.4 ) {
                    $transparent_header_tone = 'dark';
                } elseif ( 'color' === $bb_bg_type && ! empty( $first_module_data['bb_bg_color'] ) && preg_match( '/#([a-f0-9]{6})/i', (string) $first_module_data['bb_bg_color'], $bb_c_match ) ) {
                    $bb_hex = $bb_c_match[1];
                    $bb_lum = ( 0.2126 * hexdec( substr( $bb_hex, 0, 2 ) ) + 0.7152 * hexdec( substr( $bb_hex, 2, 2 ) ) + 0.0722 * hexdec( substr( $bb_hex, 4, 2 ) ) ) / 255;
                    $transparent_header_tone = $bb_lum > 0.55 ? 'light' : 'dark';
                } else {
                    $transparent_header_tone = 'light';
                }
            } elseif ( 'resource_hero_pro' === $first_module_type ) {
                $resource_bg_type = isset( $first_module_data['rh_bg_type'] ) ? sanitize_key( (string) $first_module_data['rh_bg_type'] ) : '';
                $resource_overlay = isset( $first_module_data['rh_bg_overlay'] ) && is_numeric( $first_module_data['rh_bg_overlay'] ) ? (float) $first_module_data['rh_bg_overlay'] : 0;
                $resource_gradient = isset( $first_module_data['rh_bg_gradient'] ) ? (string) $first_module_data['rh_bg_gradient'] : '';
                if ( 'image' === $resource_bg_type && $resource_overlay >= 0.4 ) {
                    $transparent_header_tone = 'dark';
                } elseif ( preg_match( '/#([a-f0-9]{6})/i', $resource_gradient, $resource_color_match ) ) {
                    $resource_hex = $resource_color_match[1];
                    $resource_luminance = (
                        0.2126 * hexdec( substr( $resource_hex, 0, 2 ) )
                        + 0.7152 * hexdec( substr( $resource_hex, 2, 2 ) )
                        + 0.0722 * hexdec( substr( $resource_hex, 4, 2 ) )
                    ) / 255;
                    $transparent_header_tone = $resource_luminance < 0.58 ? 'light' : 'dark';
                }
            } elseif ( in_array( $first_module_type, array( 'banner', 'app_hero', 'fullscreen_video', 'qiling_video_portal_hero' ), true ) ) {
                $transparent_header_tone = 'dark';
            }
            break;
        }
    }
}

// 严谨计算透明头部开关：
// 1. 若当前页面显式设置为 '0'（禁用透明），绝对不开启透明头部
// 2. 若页面首屏不是 Hero 横幅模块，且页面未手动强制开启透明，则不开启透明悬浮，避免压盖非横幅内容
if ( $page_transparent_header_disabled ) {
    $enable_transparent_header = false;
} elseif ( $page_transparent_header_enabled ) {
    $enable_transparent_header = true;
} elseif ( $is_home && $transparent_home ) {
    // 首页自动模式：仅在首屏确实为 Hero 模块时才开启透明
    $enable_transparent_header = $has_transparent_header_hero_first;
} else {
    $enable_transparent_header = false;
}

if ( $enable_transparent_header ) {
    $header_classes[] = 'header-transparent';

    $page_visual_settings = $queried_object_id > 0 && function_exists( 'developer_starter_get_post_page_visual_style' )
        ? developer_starter_get_post_page_visual_style( $queried_object_id )
        : array();
    $has_custom_transparent_text = ! empty( $page_visual_settings['header']['transparent_text'] );
    
    // 如果页面显式配置了 transparent_text，自动推断其色调是深字还是浅字
    if ( $has_custom_transparent_text ) {
        $custom_text_val = trim( (string) $page_visual_settings['header']['transparent_text'] );
        if ( preg_match( '/^#([a-f0-9]{6})$/i', $custom_text_val, $ct_match ) ) {
            $ct_hex = $ct_match[1];
            $ct_lum = ( 0.2126 * hexdec( substr( $ct_hex, 0, 2 ) ) + 0.7152 * hexdec( substr( $ct_hex, 2, 2 ) ) + 0.0722 * hexdec( substr( $ct_hex, 4, 2 ) ) ) / 255;
            $transparent_header_tone = $ct_lum < 0.55 ? 'light' : 'dark';
        }
    }

    // 核心安全兜底：如果无法判定为深色背景（dark），必须强制兜底为 light 高对比度浅底深字！
    // 绝不留空，防止 CSS 降级为看不见的纯白色文字
    if ( 'dark' !== $transparent_header_tone ) {
        $transparent_header_tone = 'light';
    }

    $header_classes[] = 'header-tone-' . $transparent_header_tone;

    $transparent_foreground = $has_custom_transparent_text
        ? (string) $page_visual_settings['header']['transparent_text']
        : ( 'light' === $transparent_header_tone ? '#0f172a' : '#ffffff' );
    $transparent_surface    = 'light' === $transparent_header_tone ? 'rgba(255, 255, 255, 0.88)' : 'rgba(15, 23, 42, 0.18)';
    $custom_header_values   = isset( $page_visual_settings['header'] ) && is_array( $page_visual_settings['header'] ) ? $page_visual_settings['header'] : array();
    
    $header_style_vars .= '--qiling-header-transparent-text:' . $transparent_foreground . ';';
    $header_style_vars .= '--qiling-header-transparent-nav-link:' . $transparent_foreground . ';';
    
    if ( 'light' === $transparent_header_tone ) {
        $header_style_vars .= '--qiling-header-transparent-bg:linear-gradient(180deg, rgba(255, 255, 255, 0.94) 0%, rgba(255, 255, 255, 0.65) 65%, transparent 100%);';
    } else {
        $header_style_vars .= '--qiling-header-transparent-bg:linear-gradient(180deg, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.35) 65%, transparent 100%);';
    }

    if ( empty( $custom_header_values['search_bg'] ) ) {
        $header_style_vars .= '--qiling-header-search-transparent-bg:' . $transparent_surface . ';';
    }
    if ( empty( $custom_header_values['search_text'] ) ) {
        $header_style_vars .= '--qiling-header-search-transparent-text:' . $transparent_foreground . ';';
    }
    if ( empty( $custom_header_values['search_placeholder'] ) ) {
        $header_style_vars .= '--qiling-header-search-transparent-placeholder:color-mix(in srgb, ' . $transparent_foreground . ' 62%, transparent);';
    }
    if ( empty( $custom_header_values['search_icon'] ) ) {
        $header_style_vars .= '--qiling-header-search-transparent-icon:' . $transparent_foreground . ';';
    }
    if ( empty( $custom_header_values['phone_bg'] ) ) {
        $header_style_vars .= '--qiling-header-phone-transparent-bg:' . $transparent_surface . ';';
    }
    if ( empty( $custom_header_values['phone_text'] ) ) {
        $header_style_vars .= '--qiling-header-phone-transparent-text:' . $transparent_foreground . ';';
    }
}

$mobile_classes = array();
if ( developer_starter_get_option( 'mobile_hide_search', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-search';
}
if ( developer_starter_get_option( 'mobile_hide_phone', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-phone';
}
if ( developer_starter_get_option( 'mobile_hide_login', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-login';
}
if ( developer_starter_get_option( 'mobile_hide_translate', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-translate';
}
if ( developer_starter_get_option( 'mobile_hide_darkmode', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-darkmode';
}
if ( developer_starter_get_option( 'mobile_hide_vip', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-vip';
}
if ( developer_starter_get_option( 'mobile_hide_cart', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-cart';
}
if ( developer_starter_get_option( 'mobile_hide_history', 0 ) ) {
    $mobile_classes[] = 'mobile-hide-history';
}

$body_classes_array = array_merge(
    $mobile_classes,
    $enable_transparent_header ? array( 'has-transparent-header' ) : array(),
    $page_transparent_header_enabled ? array( 'has-transparent-header-manual' ) : array(),
    $has_transparent_header_hero_first ? array( 'has-transparent-header-hero' ) : array(),
    '' !== $transparent_header_tone ? array( 'header-tone-' . $transparent_header_tone ) : array()
);

$has_inpage_header = function_exists( 'developer_starter_page_has_header_module' ) && developer_starter_page_has_header_module( $queried_object_id );
if ( $has_inpage_header ) {
    // 页面内自带专属 header_suite，剥离全局旧版透明页头类，避免负边距与全局样式干扰
    $body_classes_array = array_values( array_filter( $body_classes_array, function( $class_name ) {
        return 0 !== strpos( (string) $class_name, 'has-transparent-header' ) && 0 !== strpos( (string) $class_name, 'header-tone-' );
    } ) );
    $body_classes_array[] = 'has-header-suite';
}

$site_branding_classes = array( 'site-branding' );
if ( '' !== $pc_logo_slogan_text ) {
    $site_branding_classes[] = 'site-branding-with-slogan';
}

$frontend_home_url = function_exists( 'developer_starter_get_frontend_home_url' )
    ? developer_starter_get_frontend_home_url()
    : home_url( '/' );
$menu_locations    = get_nav_menu_locations();
$mobile_menu_id    = isset( $menu_locations['mobile'] ) ? (int) $menu_locations['mobile'] : 0;
$primary_menu_id   = isset( $menu_locations['primary'] ) ? (int) $menu_locations['primary'] : 0;
$menu_id           = $mobile_menu_id ?: $primary_menu_id;

$header_template_args = array(
    'body_classes'                      => $body_classes_array,
    'enable_transparent_header'         => (bool) $enable_transparent_header,
    'has_transparent_header_hero_first' => (bool) $has_transparent_header_hero_first,
    'header_classes'                    => $header_classes,
    'header_style_vars'                 => $header_style_vars,
    'header_menu_layout'                => $header_menu_layout,
    'show_search'                       => (bool) $show_search,
    'show_search_form'                  => (bool) $show_search_form,
    'show_phone'                        => (bool) $show_phone,
    'header_search_action'              => $header_search_action,
    'header_search_use_rewrite'         => (bool) $header_search_use_rewrite,
    'pc_logo_slogan_text'               => $pc_logo_slogan_text,
    'show_pc_logo_divider'              => (bool) $show_pc_logo_divider,
    'site_branding_classes'             => $site_branding_classes,
    'frontend_home_url'                 => $frontend_home_url,
    'primary_menu_id'                   => $primary_menu_id,
    'mobile_menu_id'                    => $mobile_menu_id,
    'menu_id'                           => $menu_id,
);
$header_template_args = (array) apply_filters( 'developer_starter_header_template_args', $header_template_args );
$page_header_decoration = function_exists( 'developer_starter_resolve_current_page_region_decoration' )
    ? developer_starter_resolve_current_page_region_decoration( 'header' )
    : array( 'mode' => 'inherit', 'page_id' => 0, 'modules' => array() );
if ( 'hidden' === $page_header_decoration['mode'] ) {
    $header_template_args['body_classes'] = array_values( array_filter( $header_template_args['body_classes'], function( $class_name ) {
        return 0 !== strpos( (string) $class_name, 'has-transparent-header' ) && 0 !== strpos( (string) $class_name, 'header-tone-' );
    } ) );
    $header_template_args['body_classes'][] = 'qiling-page-header-hidden';
} elseif ( 'custom' === $page_header_decoration['mode'] ) {
    $header_template_args['body_classes'] = array_values( array_filter( $header_template_args['body_classes'], function( $class_name ) {
        return 0 !== strpos( (string) $class_name, 'has-transparent-header' ) && 0 !== strpos( (string) $class_name, 'header-tone-' );
    } ) );
    $header_template_args['body_classes'][] = 'qiling-page-header-custom';
}

get_template_part( 'template-parts/header/document-head', null, $header_template_args );
?>

<body <?php body_class( isset( $header_template_args['body_classes'] ) ? (array) $header_template_args['body_classes'] : array() ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <?php if ( 'custom' === $page_header_decoration['mode'] ) : ?>
        <?php
        get_template_part(
            'template-parts/page-region/decoration',
            null,
            array(
                'region'  => 'header',
                'page_id' => $page_header_decoration['page_id'],
                'modules' => $page_header_decoration['modules'],
            )
        );
        ?>
    <?php elseif ( 'hidden' !== $page_header_decoration['mode'] ) : ?>
        <?php get_template_part( 'template-parts/header/site-header', null, $header_template_args ); ?>
    <?php endif; ?>

    <main id="primary" class="site-main">

<?php if ( 'inherit' === $page_header_decoration['mode'] ) : ?>
    <?php get_template_part( 'template-parts/header/transparent-scroll-script', null, $header_template_args ); ?>
<?php endif; ?>
