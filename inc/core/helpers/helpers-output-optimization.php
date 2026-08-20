<?php
/**
 * Output optimization helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 移除资源版本号
 */
function developer_starter_remove_version_query( $src ) {
    if ( strpos( $src, 'ver=' ) === false ) {
        return $src;
    }

    $parsed_url = wp_parse_url( $src );
    if ( empty( $parsed_url['query'] ) ) {
        return $src;
    }

    parse_str( $parsed_url['query'], $query_args );
    if ( ! isset( $query_args['ver'] ) ) {
        return $src;
    }

    $ver = $query_args['ver'];
    if ( is_array( $ver ) ) {
        $ver = reset( $ver );
    }
    $ver = (string) $ver;

    $allowed_remove_versions = array(
        get_bloginfo( 'version' ),
        defined( 'DEVELOPER_STARTER_VERSION' ) ? DEVELOPER_STARTER_VERSION : '',
    );
    if ( function_exists( 'developer_starter_get_assets_version' ) ) {
        $allowed_remove_versions[] = (string) developer_starter_get_assets_version();
    }

    $allowed_remove_versions = array_filter( array_unique( $allowed_remove_versions ) );
    if ( in_array( $ver, $allowed_remove_versions, true ) ) {
        return remove_query_arg( 'ver', $src );
    }

    return $src;
}

/**
 * 移除所有资源文件的版本号（独立选项）
 */
function developer_starter_remove_assets_version() {
    if ( developer_starter_get_option( 'remove_assets_version', '' ) ) {
        add_filter( 'style_loader_src', 'developer_starter_remove_version_query', 9999 );
        add_filter( 'script_loader_src', 'developer_starter_remove_version_query', 9999 );
    }
}
add_action( 'init', 'developer_starter_remove_assets_version', 1 );

/**
 * HTML 压缩功能
 */
function developer_starter_html_minify_start() {
    if ( ! developer_starter_get_option( 'html_minify', '' ) ) {
        return;
    }

    if ( is_admin() || defined( 'DOING_AJAX' ) || defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) ) {
        return;
    }

    if ( function_exists( 'developer_starter_is_sitemap_request' ) && developer_starter_is_sitemap_request() ) {
        return;
    }

    if ( function_exists( 'developer_starter_is_multilingual_content_mode' ) && developer_starter_is_multilingual_content_mode() ) {
        return;
    }

    if ( is_user_logged_in() || ( function_exists( 'developer_starter_has_logged_in_cookie_hint' ) && developer_starter_has_logged_in_cookie_hint() ) ) {
        return;
    }

    if ( is_feed() ) {
        return;
    }

    ob_start( 'developer_starter_html_minify_callback' );
}
add_action( 'template_redirect', 'developer_starter_html_minify_start', 1 );

/**
 * HTML 压缩回调函数
 */
function developer_starter_html_minify_callback( $html ) {
    if ( empty( $html ) ) {
        return $html;
    }

    if (
        false === strpos( $html, '<!--' )
        && false === strpos( $html, '> ' )
        && false === strpos( $html, "\n" )
        && false === strpos( $html, "\r" )
        && false === strpos( $html, "\t" )
    ) {
        return $html;
    }

    $protected = array();
    $index = 0;

    $html = preg_replace_callback( '/<script[^>]*>.*?<\/script>/is', function( $matches ) use ( &$protected, &$index ) {
        $key = '<!--PROTECTED_SCRIPT_' . $index . '-->';
        $protected[ $key ] = $matches[0];
        $index++;
        return $key;
    }, $html );

    $html = preg_replace_callback( '/<style[^>]*>.*?<\/style>/is', function( $matches ) use ( &$protected, &$index ) {
        $key = '<!--PROTECTED_STYLE_' . $index . '-->';
        $protected[ $key ] = $matches[0];
        $index++;
        return $key;
    }, $html );

    $html = preg_replace_callback( '/<(pre|textarea)[^>]*>.*?<\/\1>/is', function( $matches ) use ( &$protected, &$index ) {
        $key = '<!--PROTECTED_PRE_' . $index . '-->';
        $protected[ $key ] = $matches[0];
        $index++;
        return $key;
    }, $html );

    $html = preg_replace( '/<!--(?!\[|PROTECTED).*?-->/s', '', $html );
    $html = preg_replace( '/\s+/', ' ', $html );
    $html = preg_replace( '/>\s+</', '><', $html );

    foreach ( $protected as $key => $value ) {
        $html = str_replace( $key, $value, $html );
    }

    return $html;
}

/**
 * 是否阻止外部 Google Fonts 资源。
 *
 * @return bool
 */
function developer_starter_disable_external_google_fonts_enabled() {
    $enabled = function_exists( 'developer_starter_get_option' )
        ? developer_starter_get_option( 'disable_external_google_fonts', '1' )
        : '1';

    if ( '1' !== (string) $enabled ) {
        return false;
    }

    if ( function_exists( 'developer_starter_get_option' ) && developer_starter_get_option( 'runtime_compat_safe_mode', '' ) ) {
        return false;
    }

    return (bool) apply_filters( 'developer_starter_disable_external_google_fonts_enabled', '1' === (string) $enabled );
}

/**
 * 获取需要阻止的 Google Fonts 相关 host。
 *
 * @return array<int,string>
 */
function developer_starter_get_google_fonts_blocked_hosts() {
    $hosts = array(
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'themes.googleusercontent.com',
    );

    $hosts = apply_filters( 'developer_starter_google_fonts_blocked_hosts', $hosts );
    $hosts = array_map( 'strtolower', array_map( 'trim', (array) $hosts ) );

    return array_values( array_unique( array_filter( $hosts ) ) );
}

/**
 * 判断 URL 是否属于外部 Google Fonts / WebFont Loader。
 *
 * @param string $url 资源地址。
 * @return bool
 */
function developer_starter_is_external_google_fonts_url( $url ) {
    $url = trim( html_entity_decode( (string) $url, ENT_QUOTES, 'UTF-8' ) );
    if ( '' === $url ) {
        return false;
    }

    if ( 0 === strpos( $url, '//' ) ) {
        $url = 'https:' . $url;
    }

    if ( ! preg_match( '#^https?://#i', $url ) ) {
        return false;
    }

    $parts = wp_parse_url( $url );
    if ( false === $parts || empty( $parts['host'] ) ) {
        return false;
    }

    $host = strtolower( (string) $parts['host'] );
    $path = isset( $parts['path'] ) ? strtolower( (string) $parts['path'] ) : '';

    if ( in_array( $host, developer_starter_get_google_fonts_blocked_hosts(), true ) ) {
        return true;
    }

    $is_webfont_loader = 'ajax.googleapis.com' === $host && 0 === strpos( $path, '/ajax/libs/webfont/' );

    return (bool) apply_filters( 'developer_starter_is_external_google_fonts_url', $is_webfont_loader, $url, $host, $path );
}

/**
 * 移除已 enqueue 的 Google Fonts 资源。
 *
 * @return void
 */
function developer_starter_dequeue_external_google_fonts() {
    if ( ! developer_starter_disable_external_google_fonts_enabled() ) {
        return;
    }

    if ( function_exists( 'wp_styles' ) ) {
        $styles = wp_styles();
        if ( $styles && ! empty( $styles->registered ) && is_array( $styles->registered ) ) {
            foreach ( $styles->registered as $handle => $style ) {
                $src = isset( $style->src ) ? (string) $style->src : '';
                if ( developer_starter_is_external_google_fonts_url( $src ) ) {
                    wp_dequeue_style( $handle );
                    wp_deregister_style( $handle );
                }
            }
        }
    }

    if ( function_exists( 'wp_scripts' ) ) {
        $scripts = wp_scripts();
        if ( $scripts && ! empty( $scripts->registered ) && is_array( $scripts->registered ) ) {
            foreach ( $scripts->registered as $handle => $script ) {
                $src = isset( $script->src ) ? (string) $script->src : '';
                if ( developer_starter_is_external_google_fonts_url( $src ) ) {
                    wp_dequeue_script( $handle );
                    wp_deregister_script( $handle );
                }
            }
        }
    }
}

/**
 * 从待打印队列中移除 Google Fonts 样式 handle。
 *
 * @param array<int,string> $handles 待打印样式。
 * @return array<int,string>
 */
function developer_starter_filter_google_fonts_style_queue( $handles ) {
    if ( ! developer_starter_disable_external_google_fonts_enabled() || ! is_array( $handles ) || ! function_exists( 'wp_styles' ) ) {
        return $handles;
    }

    $styles = wp_styles();
    if ( ! $styles || empty( $styles->registered ) || ! is_array( $styles->registered ) ) {
        return $handles;
    }

    return array_values(
        array_filter(
            $handles,
            static function( $handle ) use ( $styles ) {
                if ( ! isset( $styles->registered[ $handle ] ) ) {
                    return true;
                }

                $src = isset( $styles->registered[ $handle ]->src ) ? (string) $styles->registered[ $handle ]->src : '';
                return ! developer_starter_is_external_google_fonts_url( $src );
            }
        )
    );
}

/**
 * 从待打印队列中移除 Google WebFont Loader 脚本 handle。
 *
 * @param array<int,string> $handles 待打印脚本。
 * @return array<int,string>
 */
function developer_starter_filter_google_fonts_script_queue( $handles ) {
    if ( ! developer_starter_disable_external_google_fonts_enabled() || ! is_array( $handles ) || ! function_exists( 'wp_scripts' ) ) {
        return $handles;
    }

    $scripts = wp_scripts();
    if ( ! $scripts || empty( $scripts->registered ) || ! is_array( $scripts->registered ) ) {
        return $handles;
    }

    return array_values(
        array_filter(
            $handles,
            static function( $handle ) use ( $scripts ) {
                if ( ! isset( $scripts->registered[ $handle ] ) ) {
                    return true;
                }

                $src = isset( $scripts->registered[ $handle ]->src ) ? (string) $scripts->registered[ $handle ]->src : '';
                return ! developer_starter_is_external_google_fonts_url( $src );
            }
        )
    );
}

/**
 * 兜底移除 style_loader 输出的 Google Fonts 标签。
 *
 * @param string $html   标签 HTML。
 * @param string $handle 资源句柄。
 * @param string $href   资源地址。
 * @return string
 */
function developer_starter_filter_google_fonts_style_tag( $html, $handle, $href ) {
    if ( developer_starter_disable_external_google_fonts_enabled() && developer_starter_is_external_google_fonts_url( $href ) ) {
        return '';
    }

    return $html;
}

/**
 * 兜底移除 script_loader 输出的 Google WebFont Loader 标签。
 *
 * @param string $tag    标签 HTML。
 * @param string $handle 资源句柄。
 * @param string $src    资源地址。
 * @return string
 */
function developer_starter_filter_google_fonts_script_tag( $tag, $handle, $src ) {
    if ( developer_starter_disable_external_google_fonts_enabled() && developer_starter_is_external_google_fonts_url( $src ) ) {
        return '';
    }

    return $tag;
}

/**
 * 移除 Google Fonts DNS/preconnect hints。
 *
 * @param array<int|string,mixed> $urls          Resource hints。
 * @param string                  $relation_type 关系类型。
 * @return array<int|string,mixed>
 */
function developer_starter_filter_google_fonts_resource_hints( $urls, $relation_type ) {
    if ( ! developer_starter_disable_external_google_fonts_enabled() || ! in_array( $relation_type, array( 'dns-prefetch', 'preconnect' ), true ) || ! is_array( $urls ) ) {
        return $urls;
    }

    foreach ( $urls as $key => $url ) {
        $href = is_array( $url ) && isset( $url['href'] ) ? (string) $url['href'] : (string) $url;
        if ( developer_starter_is_external_google_fonts_url( $href ) ) {
            unset( $urls[ $key ] );
        }
    }

    return $urls;
}

/**
 * 隐藏 WordPress Font Library 默认 Google Fonts collection。
 *
 * @return void
 */
function developer_starter_unregister_google_fonts_collection() {
    static $removed = false;

    if ( $removed || ! developer_starter_disable_external_google_fonts_enabled() || ! function_exists( 'wp_unregister_font_collection' ) || ! class_exists( 'WP_Font_Library' ) ) {
        return;
    }

    $font_library = WP_Font_Library::get_instance();
    if ( ! is_callable( array( $font_library, 'get_font_collection' ) ) || null === $font_library->get_font_collection( 'google-fonts' ) ) {
        return;
    }

    if ( wp_unregister_font_collection( 'google-fonts' ) ) {
        $removed = true;
    }
}

/**
 * 清理硬编码到页面中的 Google Fonts 标签和 @font-face 片段。
 *
 * @param string $html 页面 HTML。
 * @return string
 */
function developer_starter_strip_external_google_fonts_from_html( $html ) {
    if ( ! is_string( $html ) || '' === $html ) {
        return $html;
    }

    if (
        false === stripos( $html, 'fonts.googleapis.com' )
        && false === stripos( $html, 'fonts.gstatic.com' )
        && false === stripos( $html, 'themes.googleusercontent.com' )
        && false === stripos( $html, 'ajax.googleapis.com/ajax/libs/webfont/' )
    ) {
        return $html;
    }

    $html = preg_replace( '#<link\b[^>]*(?:href|data-href)\s*=\s*([\'"]?)(?:https?:)?//(?:fonts\.googleapis\.com|fonts\.gstatic\.com|themes\.googleusercontent\.com)[^>]*>#i', '', $html );
    $html = preg_replace( '#<script\b[^>]*src\s*=\s*([\'"]?)(?:https?:)?//ajax\.googleapis\.com/ajax/libs/webfont/[^>]*>\s*</script>#i', '', $html );
    $html = preg_replace( '#@import\s+(?:url\(\s*)?[\'"]?(?:https?:)?//fonts\.googleapis\.com/[^\'"\);]+[\'"]?\s*\)?\s*;#i', '', $html );
    $html = preg_replace( '#@font-face\s*\{[^{}]*(?:fonts\.gstatic\.com|themes\.googleusercontent\.com)[^{}]*\}#i', '', $html );

    return is_string( $html ) ? $html : '';
}

/**
 * 开启输出缓冲，兜底清理插件硬编码的 Google Fonts。
 *
 * @return void
 */
function developer_starter_external_google_fonts_buffer_start() {
    if ( ! developer_starter_disable_external_google_fonts_enabled() ) {
        return;
    }

    if (
        ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() )
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
        || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
        || ( defined( 'DOING_CRON' ) && DOING_CRON )
    ) {
        return;
    }

    ob_start( 'developer_starter_strip_external_google_fonts_from_html' );
}

add_action( 'init', 'developer_starter_unregister_google_fonts_collection', 100 );
add_action( 'wp_loaded', 'developer_starter_unregister_google_fonts_collection', 100 );
add_action( 'admin_init', 'developer_starter_unregister_google_fonts_collection', 100 );
add_action( 'wp_enqueue_scripts', 'developer_starter_dequeue_external_google_fonts', 9999 );
add_action( 'admin_enqueue_scripts', 'developer_starter_dequeue_external_google_fonts', 9999 );
add_action( 'login_enqueue_scripts', 'developer_starter_dequeue_external_google_fonts', 9999 );
add_action( 'wp_print_styles', 'developer_starter_dequeue_external_google_fonts', 1 );
add_action( 'wp_print_styles', 'developer_starter_dequeue_external_google_fonts', 9999 );
add_action( 'admin_print_styles', 'developer_starter_dequeue_external_google_fonts', 1 );
add_action( 'admin_print_styles', 'developer_starter_dequeue_external_google_fonts', 9999 );
add_action( 'login_head', 'developer_starter_dequeue_external_google_fonts', 1 );
add_filter( 'print_styles_array', 'developer_starter_filter_google_fonts_style_queue', 9999 );
add_filter( 'print_scripts_array', 'developer_starter_filter_google_fonts_script_queue', 9999 );
add_filter( 'style_loader_tag', 'developer_starter_filter_google_fonts_style_tag', 9999, 3 );
add_filter( 'script_loader_tag', 'developer_starter_filter_google_fonts_script_tag', 9999, 3 );
add_filter( 'wp_resource_hints', 'developer_starter_filter_google_fonts_resource_hints', 9999, 2 );
add_action( 'template_redirect', 'developer_starter_external_google_fonts_buffer_start', 0 );
add_action( 'admin_init', 'developer_starter_external_google_fonts_buffer_start', 0 );
add_action( 'login_init', 'developer_starter_external_google_fonts_buffer_start', 0 );

/**
 * DNS 预解析和预连接
 */
function developer_starter_output_dns_prefetch() {
    $dns_prefetch = developer_starter_get_option( 'dns_prefetch', '' );
    if ( $dns_prefetch ) {
        $domains = array_filter( array_map( 'trim', explode( "\n", $dns_prefetch ) ) );
        foreach ( $domains as $domain ) {
            $domain = str_replace( array( 'http://', 'https://', '//' ), '', $domain );
            if ( developer_starter_disable_external_google_fonts_enabled() && developer_starter_is_external_google_fonts_url( 'https://' . $domain ) ) {
                continue;
            }
            echo '<link rel="dns-prefetch" href="//' . esc_attr( $domain ) . '">' . "\n";
        }
    }

    $preconnect = developer_starter_get_option( 'preconnect_urls', '' );
    if ( $preconnect ) {
        $domains = array_filter( array_map( 'trim', explode( "\n", $preconnect ) ) );
        foreach ( $domains as $domain ) {
            $domain = str_replace( array( 'http://', 'https://', '//' ), '', $domain );
            if ( developer_starter_disable_external_google_fonts_enabled() && developer_starter_is_external_google_fonts_url( 'https://' . $domain ) ) {
                continue;
            }
            echo '<link rel="preconnect" href="https://' . esc_attr( $domain ) . '" crossorigin>' . "\n";
        }
    }
}
add_action( 'wp_head', 'developer_starter_output_dns_prefetch', 1 );

/**
 * 预加载首屏关键图片（LCP）
 */
function developer_starter_normalize_lcp_image_url( $url ) {
    $url = trim( html_entity_decode( (string) $url, ENT_QUOTES, 'UTF-8' ) );
    if ( '' === $url ) {
        return '';
    }

    if ( preg_match( '/url\(\s*([\'"]?)(.*?)\1\s*\)/i', $url, $matches ) ) {
        $url = isset( $matches[2] ) ? (string) $matches[2] : '';
    }

    $url = trim( $url, " \t\n\r\0\x0B'\"" );
    if ( '' === $url || false !== strpos( $url, '${' ) || 0 === strpos( $url, '#' ) || 0 === stripos( $url, 'data:' ) ) {
        return '';
    }

    if ( 0 === strpos( $url, '//' ) ) {
        $url = ( is_ssl() ? 'https:' : 'http:' ) . $url;
    } elseif ( 0 === strpos( $url, '/' ) && function_exists( 'home_url' ) ) {
        $url = home_url( $url );
    } elseif ( ! preg_match( '#^https?://#i', $url ) && function_exists( 'home_url' ) ) {
        $url = home_url( '/' . ltrim( $url, '/' ) );
    }

    $url = esc_url_raw( $url );
    if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
        return '';
    }

    return $url;
}

function developer_starter_add_lcp_image_candidate( $value, array &$candidates ) {
    $url = developer_starter_normalize_lcp_image_url( $value );
    if ( '' !== $url && ! in_array( $url, $candidates, true ) ) {
        $candidates[] = $url;
    }
}

function developer_starter_get_first_lcp_image_from_items( $items, $preferred_keys = array() ) {
    if ( ! is_array( $items ) || empty( $items ) ) {
        return '';
    }

    if ( empty( $preferred_keys ) ) {
        $preferred_keys = array( 'image', 'url', 'src', 'poster', 'video_poster', 'thumb', 'thumbnail' );
    }

    foreach ( $items as $item ) {
        if ( is_string( $item ) ) {
            $url = developer_starter_normalize_lcp_image_url( $item );
            if ( '' !== $url ) {
                return $url;
            }
            continue;
        }

        if ( ! is_array( $item ) ) {
            continue;
        }

        foreach ( $preferred_keys as $key ) {
            if ( ! array_key_exists( $key, $item ) ) {
                continue;
            }

            $url = developer_starter_normalize_lcp_image_url( $item[ $key ] );
            if ( '' !== $url ) {
                return $url;
            }
        }
    }

    return '';
}

function developer_starter_get_module_lcp_image_candidate( $module_id, $data ) {
    $module_id = sanitize_key( (string) $module_id );
    $data = is_array( $data ) ? $data : array();
    if ( '' === $module_id || empty( $data ) ) {
        return '';
    }

    $candidates = array();

    switch ( $module_id ) {
        case 'resource_hero_pro':
            if ( isset( $data['rh_bg_type'] ) && 'image' === sanitize_key( (string) $data['rh_bg_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['rh_bg_image'] ) ? $data['rh_bg_image'] : '', $candidates );
            }
            $resource_layout = isset( $data['rh_layout'] ) ? sanitize_key( (string) $data['rh_layout'] ) : '';
            if ( 'text_only' !== $resource_layout ) {
                developer_starter_add_lcp_image_candidate( developer_starter_get_first_lcp_image_from_items( isset( $data['rh_visual_slides'] ) ? $data['rh_visual_slides'] : array() ), $candidates );
                if ( ! empty( $data['rh_visual_gallery'] ) ) {
                    $gallery_urls = array_filter( array_map( 'trim', explode( ',', (string) $data['rh_visual_gallery'] ) ) );
                    developer_starter_add_lcp_image_candidate( ! empty( $gallery_urls ) ? reset( $gallery_urls ) : '', $candidates );
                }
                developer_starter_add_lcp_image_candidate( developer_starter_get_first_lcp_image_from_items( isset( $data['rh_visual_images'] ) ? $data['rh_visual_images'] : array() ), $candidates );
                developer_starter_add_lcp_image_candidate( isset( $data['rh_visual_image'] ) ? $data['rh_visual_image'] : '', $candidates );
            }
            break;

        case 'banner':
            developer_starter_add_lcp_image_candidate( developer_starter_get_first_lcp_image_from_items( isset( $data['banner_slides'] ) ? $data['banner_slides'] : array() ), $candidates );
            break;

        case 'hero_search':
            developer_starter_add_lcp_image_candidate( developer_starter_get_first_lcp_image_from_items( isset( $data['hs_bg_items'] ) ? $data['hs_bg_items'] : array() ), $candidates );
            break;

        case 'brand_banner_pro':
            if ( isset( $data['bb_bg_type'] ) && 'image' === sanitize_key( (string) $data['bb_bg_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['bb_bg_image'] ) ? $data['bb_bg_image'] : '', $candidates );
            }
            if ( ! isset( $data['bb_media_type'] ) || 'image' === sanitize_key( (string) $data['bb_media_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['bb_main_image'] ) ? $data['bb_main_image'] : '', $candidates );
            }
            developer_starter_add_lcp_image_candidate( isset( $data['bb_title_image'] ) ? $data['bb_title_image'] : '', $candidates );
            developer_starter_add_lcp_image_candidate( isset( $data['bb_orbit_center_image'] ) ? $data['bb_orbit_center_image'] : '', $candidates );
            break;

        case 'app_hero':
            if ( isset( $data['bg_type'] ) && 'image' === sanitize_key( (string) $data['bg_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['bg_image'] ) ? $data['bg_image'] : '', $candidates );
            }
            if ( ! isset( $data['media_type'] ) || 'image' === sanitize_key( (string) $data['media_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['hero_image'] ) ? $data['hero_image'] : '', $candidates );
            }
            break;

        case 'dynamic_banner':
            if ( isset( $data['db_bg_type'] ) && 'image' === sanitize_key( (string) $data['db_bg_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['db_bg_image'] ) ? $data['db_bg_image'] : '', $candidates );
            }
            if ( ! isset( $data['db_media_type'] ) || 'image' === sanitize_key( (string) $data['db_media_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['db_main_image'] ) ? $data['db_main_image'] : '', $candidates );
            }
            break;

        case 'interact_hero':
            if ( ! isset( $data['media_type'] ) || 'image' === sanitize_key( (string) $data['media_type'] ) ) {
                developer_starter_add_lcp_image_candidate( isset( $data['hero_image'] ) ? $data['hero_image'] : '', $candidates );
            }
            break;

        case 'resume_hero':
            developer_starter_add_lcp_image_candidate( isset( $data['rh_bg_image'] ) ? $data['rh_bg_image'] : '', $candidates );
            developer_starter_add_lcp_image_candidate( isset( $data['rh_avatar'] ) ? $data['rh_avatar'] : '', $candidates );
            break;

        case 'double_column_carousel':
            developer_starter_add_lcp_image_candidate( developer_starter_get_first_lcp_image_from_items( isset( $data['dcc_slides'] ) ? $data['dcc_slides'] : array() ), $candidates );
            developer_starter_add_lcp_image_candidate( isset( $data['dcc_right_1_image'] ) ? $data['dcc_right_1_image'] : '', $candidates );
            break;

        case 'product_showcase':
            developer_starter_add_lcp_image_candidate( developer_starter_get_first_lcp_image_from_items( isset( $data['ps_media_items'] ) ? $data['ps_media_items'] : array(), array( 'image', 'video_poster', 'poster', 'url', 'src' ) ), $candidates );
            break;
    }

    if ( empty( $candidates ) ) {
        $top_level_keys = array(
            'bg_image',
            'hero_image',
            'main_image',
            'background_image',
            'poster',
            'video_poster',
        );
        foreach ( $top_level_keys as $key ) {
            if ( array_key_exists( $key, $data ) ) {
                developer_starter_add_lcp_image_candidate( $data[ $key ], $candidates );
            }
        }
    }

    if ( empty( $candidates ) ) {
        foreach ( $data as $key => $value ) {
            if ( ! is_array( $value ) || ! preg_match( '/(?:slides|items|media|images|gallery)/i', (string) $key ) ) {
                continue;
            }

            developer_starter_add_lcp_image_candidate( developer_starter_get_first_lcp_image_from_items( $value ), $candidates );
            if ( ! empty( $candidates ) ) {
                break;
            }
        }
    }

    $candidate = ! empty( $candidates ) ? $candidates[0] : '';

    return (string) apply_filters( 'developer_starter_module_lcp_image_candidate', $candidate, $module_id, $data, $candidates );
}

function developer_starter_get_current_page_modules_for_lcp_preload() {
    $post_id = get_queried_object_id();
    if ( ! $post_id ) {
        return array();
    }

    $post = get_post( $post_id );
    if ( ! $post instanceof WP_Post ) {
        return array();
    }

    if ( function_exists( 'developer_starter_get_page_modules_data' ) ) {
        $modules = developer_starter_get_page_modules_data( $post_id );
    } else {
        $modules = get_post_meta( $post_id, '_developer_starter_modules', true );
    }

    $modules = apply_filters( 'developer_starter_page_modules_data', $modules, $post_id );
    return is_array( $modules ) ? $modules : array();
}

function developer_starter_get_first_hero_module_lcp_image() {
    $modules = developer_starter_get_current_page_modules_for_lcp_preload();
    if ( empty( $modules ) ) {
        return array();
    }

    $hero_modules = class_exists( '\Developer_Starter\Modules\Module_Manager' )
        ? \Developer_Starter\Modules\Module_Manager::get_hero_module_ids( 'lcp_preload' )
        : array( 'banner', 'product_showcase', 'hero_search', 'double_column_carousel', 'dynamic_banner', 'resource_hero_pro', 'brand_banner_pro', 'app_hero', 'qiling_shop_showcase', 'fullscreen_video', 'qiling_video_portal_hero', 'interact_hero', 'resume_hero' );

    foreach ( $modules as $module_data ) {
        if ( ! is_array( $module_data ) ) {
            continue;
        }

        $module_id = isset( $module_data['type'] ) ? sanitize_key( (string) $module_data['type'] ) : '';
        if ( '' === $module_id || ! in_array( $module_id, $hero_modules, true ) ) {
            continue;
        }

        $data = isset( $module_data['data'] ) && is_array( $module_data['data'] ) ? $module_data['data'] : array();
        $image_url = developer_starter_get_module_lcp_image_candidate( $module_id, $data );
        if ( '' !== $image_url ) {
            return array(
                'url'       => $image_url,
                'module_id' => $module_id,
                'source'    => 'module',
            );
        }
    }

    return array();
}

function developer_starter_get_attachment_lcp_preload_data( $image_url ) {
    $data = array(
        'url'    => developer_starter_normalize_lcp_image_url( $image_url ),
        'srcset' => '',
        'sizes'  => '',
    );

    if ( '' === $data['url'] || ! function_exists( 'attachment_url_to_postid' ) ) {
        return $data;
    }

    $attachment_id = attachment_url_to_postid( $data['url'] );
    if ( $attachment_id <= 0 ) {
        return $data;
    }

    $data['srcset'] = function_exists( 'wp_get_attachment_image_srcset' )
        ? (string) wp_get_attachment_image_srcset( $attachment_id, 'full' )
        : '';
    $data['sizes'] = function_exists( 'wp_get_attachment_image_sizes' )
        ? (string) wp_get_attachment_image_sizes( $attachment_id, 'full' )
        : '';

    return $data;
}

function developer_starter_output_lcp_preload() {
    if ( is_admin() || is_feed() || ! developer_starter_get_option( 'lcp_preload_enable', '' ) ) {
        return;
    }

    $image_url = '';
    $srcset = '';
    $sizes = '';
    $source = '';
    $mode = (string) developer_starter_get_option( 'lcp_preload_mode', 'featured' );

    if ( $mode === 'custom' ) {
        $image_url = function_exists( 'developer_starter_normalize_asset_url' )
            ? developer_starter_normalize_asset_url( (string) developer_starter_get_option( 'lcp_preload_custom_url', '' ) )
            : esc_url_raw( (string) developer_starter_get_option( 'lcp_preload_custom_url', '' ) );
        $image_url = developer_starter_normalize_lcp_image_url( $image_url );
        $source = 'custom';
    } else {
        $module_image = developer_starter_get_first_hero_module_lcp_image();
        if ( ! empty( $module_image['url'] ) ) {
            $image_url = developer_starter_normalize_lcp_image_url( $module_image['url'] );
            $source = 'module';
        }
    }

    if ( '' === $image_url && is_singular() ) {
        $post_id = get_queried_object_id();
        if ( $post_id && has_post_thumbnail( $post_id ) ) {
            $thumb_id = get_post_thumbnail_id( $post_id );
            $thumb_src = wp_get_attachment_image_src( $thumb_id, 'full' );
            if ( is_array( $thumb_src ) && ! empty( $thumb_src[0] ) ) {
                $image_url = $thumb_src[0];
                $srcset = (string) wp_get_attachment_image_srcset( $thumb_id, 'full' );
                $sizes = (string) wp_get_attachment_image_sizes( $thumb_id, 'full' );
                $source = 'featured';
            }
        }
    }

    if ( $image_url === '' ) {
        return;
    }

    if ( '' === $srcset && '' === $sizes ) {
        $attachment_preload = developer_starter_get_attachment_lcp_preload_data( $image_url );
        $image_url = $attachment_preload['url'];
        $srcset = $attachment_preload['srcset'];
        $sizes = $attachment_preload['sizes'];
    }

    $preload = apply_filters(
        'developer_starter_lcp_preload_image',
        array(
            'url'    => $image_url,
            'srcset' => $srcset,
            'sizes'  => $sizes,
            'source' => $source,
            'mode'   => $mode,
        )
    );

    if ( ! is_array( $preload ) || empty( $preload['url'] ) ) {
        return;
    }

    $image_url = developer_starter_normalize_lcp_image_url( $preload['url'] );
    $srcset = isset( $preload['srcset'] ) ? (string) $preload['srcset'] : '';
    $sizes = isset( $preload['sizes'] ) ? (string) $preload['sizes'] : '';

    if ( '' === $image_url ) {
        return;
    }

    echo '<link rel="preload" as="image" href="' . esc_url( $image_url ) . '" fetchpriority="high"';
    if ( $srcset !== '' ) {
        echo ' imagesrcset="' . esc_attr( $srcset ) . '"';
    }
    if ( $sizes !== '' ) {
        echo ' imagesizes="' . esc_attr( $sizes ) . '"';
    }
    echo ' />' . "\n";
}
add_action( 'wp_head', 'developer_starter_output_lcp_preload', 2 );

/**
 * 心跳控制
 */
function developer_starter_heartbeat_control() {
    $heartbeat = developer_starter_get_option( 'heartbeat_control', '' );
    $editor_interval = developer_starter_get_option( 'heartbeat_editor_interval', '' );
    $admin_interval = developer_starter_get_option( 'heartbeat_admin_interval', '' );

    if ( ! $heartbeat && ! $editor_interval && ! $admin_interval ) {
        return;
    }

    if ( $heartbeat === 'disable_all' ) {
        wp_deregister_script( 'heartbeat' );
        return;
    }

    if ( $heartbeat === 'disable_frontend' && ! is_admin() ) {
        wp_deregister_script( 'heartbeat' );
        return;
    }

    $base_interval = is_numeric( $heartbeat ) ? intval( $heartbeat ) : 0;
    $editor_interval = is_numeric( $editor_interval ) ? intval( $editor_interval ) : 0;
    $admin_interval = is_numeric( $admin_interval ) ? intval( $admin_interval ) : 0;

    add_filter( 'heartbeat_settings', function( $settings ) use ( $base_interval, $editor_interval, $admin_interval ) {
        $target_interval = 0;

        if ( is_admin() ) {
            $is_editor_screen = false;
            if ( function_exists( 'get_current_screen' ) ) {
                $screen = get_current_screen();
                if ( $screen ) {
                    if ( method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
                        $is_editor_screen = true;
                    } elseif ( isset( $screen->base ) && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
                        $is_editor_screen = true;
                    }
                }
            }

            if ( $is_editor_screen && $editor_interval > 0 ) {
                $target_interval = $editor_interval;
            } elseif ( ! $is_editor_screen && $admin_interval > 0 ) {
                $target_interval = $admin_interval;
            }
        }

        if ( $target_interval <= 0 && $base_interval > 0 ) {
            $target_interval = $base_interval;
        }

        if ( $target_interval > 0 ) {
            $settings['interval'] = $target_interval;
        }

        return $settings;
    } );
}
add_action( 'init', 'developer_starter_heartbeat_control', 1 );

/**
 * 后台自动保存间隔控制
 */
function developer_starter_autosave_interval_control() {
    $interval = intval( developer_starter_get_option( 'autosave_interval', 60 ) );
    if ( $interval < 30 || $interval > 600 ) {
        return;
    }

    add_filter( 'autosave_interval', function() use ( $interval ) {
        return $interval;
    } );
}
add_action( 'init', 'developer_starter_autosave_interval_control', 1 );
