<?php
/**
 * Cache-control helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_has_logged_in_cookie_hint' ) ) {
    /**
     * 通过 Cookie 名快速判断当前请求是否携带登录态线索（无需依赖头部登录按钮）。
     *
     * @return bool
     */
    function developer_starter_has_logged_in_cookie_hint() {
        if ( empty( $_COOKIE ) || ! is_array( $_COOKIE ) ) {
            return false;
        }

        foreach ( $_COOKIE as $cookie_name => $cookie_value ) {
            if ( strpos( (string) $cookie_name, 'wordpress_logged_in_' ) === 0 && (string) $cookie_value !== '' ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_is_cache_control_runtime_request' ) ) {
    /**
     * 当前请求是否适合执行前台 no-cache 判定。
     *
     * @return bool
     */
    function developer_starter_is_cache_control_runtime_request() {
        if ( is_admin() ) {
            return false;
        }
        if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
            return false;
        }
        if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
            return false;
        }
        if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
            return false;
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_define_no_cache_constants' ) ) {
    /**
     * 统一声明 no-cache 常量。
     *
     * @return void
     */
    function developer_starter_define_no_cache_constants() {
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
        if ( ! defined( 'DONOTCACHEMINIFY' ) ) {
            define( 'DONOTCACHEMINIFY', true );
        }
        if ( ! defined( 'DONOTCACHEDB' ) ) {
            define( 'DONOTCACHEDB', true );
        }
        if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
            define( 'DONOTCACHEOBJECT', true );
        }
        if ( ! defined( 'DONOTMINIFY' ) ) {
            define( 'DONOTMINIFY', true );
        }
    }
}

if ( ! function_exists( 'developer_starter_bootstrap_no_cache_by_cookie' ) ) {
    /**
     * 在主题加载早期就声明“登录请求禁止页面缓存”。
     *
     * @return void
     */
    function developer_starter_bootstrap_no_cache_by_cookie() {
        if ( ! developer_starter_is_cache_control_runtime_request() ) {
            return;
        }

        if ( ! developer_starter_has_logged_in_cookie_hint() ) {
            return;
        }

        developer_starter_define_no_cache_constants();
    }
}
developer_starter_bootstrap_no_cache_by_cookie();

if ( ! function_exists( 'developer_starter_is_auth_template_page' ) ) {
    /**
     * 判断当前是否为认证模板页。
     *
     * @return bool
     */
    function developer_starter_is_auth_template_page() {
        if ( is_admin() || wp_doing_ajax() ) {
            return false;
        }
        if ( ! function_exists( 'is_page_template' ) ) {
            return false;
        }

        return is_page_template( 'templates/template-login.php' )
            || is_page_template( 'templates/template-register.php' )
            || is_page_template( 'templates/template-forgot-password.php' );
    }
}

if ( ! function_exists( 'developer_starter_get_no_cache_reason' ) ) {
    /**
     * 统一判断当前请求是否需要 no-cache。
     *
     * @param array $args 配置。
     * @return string 空字符串表示不需要；非空返回原因标识。
     */
    function developer_starter_get_no_cache_reason( $args = array() ) {
        $args = wp_parse_args(
            is_array( $args ) ? $args : array(),
            array(
                'include_auth_template' => false,
                'include_builder_mode'  => true,
            )
        );

        if ( ! developer_starter_is_cache_control_runtime_request() ) {
            return '';
        }

        if ( ! empty( $args['include_builder_mode'] ) ) {
            if ( class_exists( '\Developer_Starter\Core\Frontend_Builder' ) && method_exists( '\Developer_Starter\Core\Frontend_Builder', 'is_builder_mode' ) ) {
                if ( \Developer_Starter\Core\Frontend_Builder::is_builder_mode() ) {
                    return 'builder';
                }
            }
        }

        if ( is_singular( 'post' ) ) {
            return 'single-post';
        }

        if ( is_user_logged_in() || developer_starter_has_logged_in_cookie_hint() ) {
            return 'logged-in';
        }

        if ( function_exists( 'qls_shop_public' ) && method_exists( qls_shop_public(), 'is_shop_page' ) && qls_shop_public()->is_shop_page() ) {
            return 'shop-page';
        }

        if ( ! empty( $args['include_auth_template'] ) && function_exists( 'developer_starter_is_auth_template_page' ) && developer_starter_is_auth_template_page() ) {
            return 'auth-page';
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_get_no_cache_header_values' ) ) {
    /**
     * 统一 no-cache 头部值。
     *
     * @return array<string,string>
     */
    function developer_starter_get_no_cache_header_values() {
        return array(
            'Cache-Control'     => 'private, no-store, no-cache, must-revalidate, max-age=0, s-maxage=0',
            'Pragma'            => 'no-cache',
            'Expires'           => '0',
            'X-Accel-Expires'   => '0',
            'Surrogate-Control' => 'no-store',
            'CDN-Cache-Control' => 'no-store',
            'Vary'              => 'Cookie, Authorization',
        );
    }
}

if ( ! function_exists( 'developer_starter_merge_no_cache_vary_header' ) ) {
    /**
     * 合并 Vary 头，避免覆盖其他组件已声明项。
     *
     * @param string $existing       现有 Vary 值。
     * @param array  $required_parts 必需 Vary 项。
     * @return string
     */
    function developer_starter_merge_no_cache_vary_header( $existing, $required_parts = array() ) {
        $merged = array();
        $parts  = array();

        if ( is_string( $existing ) && '' !== trim( $existing ) ) {
            $parts = array_merge( $parts, explode( ',', $existing ) );
        }

        if ( is_array( $required_parts ) && ! empty( $required_parts ) ) {
            $parts = array_merge( $parts, $required_parts );
        }

        foreach ( $parts as $part ) {
            $part = trim( (string) $part );
            if ( '' === $part ) {
                continue;
            }

            $key = strtolower( $part );
            if ( isset( $merged[ $key ] ) ) {
                continue;
            }
            $merged[ $key ] = $part;
        }

        return implode( ', ', array_values( $merged ) );
    }
}

if ( ! function_exists( 'developer_starter_normalize_cache_bypass_reason' ) ) {
    /**
     * 清洗 no-cache 原因标识，用于响应头调试。
     *
     * @param string $reason 原始原因。
     * @return string
     */
    function developer_starter_normalize_cache_bypass_reason( $reason ) {
        $reason = strtolower( trim( (string) $reason ) );
        $reason = preg_replace( '/[^a-z0-9._-]+/', '-', $reason );
        $reason = is_string( $reason ) ? trim( $reason, '-.' ) : '';

        return '' !== $reason ? $reason : 'nocache';
    }
}

if ( ! function_exists( 'developer_starter_set_logged_in_nocache_cookie' ) ) {
    /**
     * 已登录请求写入 no-cache 标记 Cookie。
     *
     * @return void
     */
    function developer_starter_set_logged_in_nocache_cookie() {
        if ( headers_sent() ) {
            return;
        }

        $path   = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
        $domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
        $secure = is_ssl();

        if ( PHP_VERSION_ID >= 70300 ) {
            setcookie( 'ds_nocache_logged_in', '1', array(
                'expires'  => 0,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ) );
        } else {
            setcookie( 'ds_nocache_logged_in', '1', 0, $path, $domain, $secure, true );
        }

        $_COOKIE['ds_nocache_logged_in'] = '1';
    }
}

if ( ! function_exists( 'developer_starter_maybe_set_no_cache_cookie_for_reason' ) ) {
    /**
     * 按 no-cache 场景写入登录态 no-cache 标记 Cookie。
     *
     * @param string $reason no-cache 原因。
     * @return void
     */
    function developer_starter_maybe_set_no_cache_cookie_for_reason( $reason ) {
        if ( in_array( $reason, array( 'logged-in', 'shop-page', 'builder', 'single-post' ), true ) ) {
            developer_starter_set_logged_in_nocache_cookie();
        }
    }
}

if ( ! function_exists( 'developer_starter_apply_no_cache_headers_array' ) ) {
    /**
     * 将统一 no-cache 头写入 headers 数组（用于 wp_headers 过滤器）。
     *
     * @param array  $headers 原始头数组。
     * @param string $reason  no-cache 原因。
     * @return array
     */
    function developer_starter_apply_no_cache_headers_array( $headers, $reason ) {
        if ( ! is_array( $headers ) ) {
            $headers = array();
        }

        $no_cache_headers = developer_starter_get_no_cache_header_values();
        foreach ( $no_cache_headers as $name => $value ) {
            if ( 'Vary' === $name ) {
                $headers['Vary'] = developer_starter_merge_no_cache_vary_header(
                    isset( $headers['Vary'] ) ? (string) $headers['Vary'] : '',
                    array( 'Cookie', 'Authorization' )
                );
                continue;
            }

            $headers[ $name ] = $value;
        }

        $headers['X-DS-Cache-Bypass'] = developer_starter_normalize_cache_bypass_reason( $reason );

        return $headers;
    }
}

if ( ! function_exists( 'developer_starter_has_no_cache_bypass_header' ) ) {
    /**
     * 检查当前响应队列是否已包含主题 no-cache 调试头。
     *
     * @return bool
     */
    function developer_starter_has_no_cache_bypass_header() {
        if ( ! function_exists( 'headers_list' ) ) {
            return false;
        }

        $headers = headers_list();
        if ( ! is_array( $headers ) || empty( $headers ) ) {
            return false;
        }

        foreach ( $headers as $line ) {
            if ( stripos( (string) $line, 'X-DS-Cache-Bypass:' ) === 0 ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_apply_no_cache_headers' ) ) {
    /**
     * 统一输出 no-cache 头。
     *
     * @param string $reason no-cache 原因。
     * @return void
     */
    function developer_starter_apply_no_cache_headers( $reason ) {
        $reason = developer_starter_normalize_cache_bypass_reason( $reason );

        developer_starter_define_no_cache_constants();

        developer_starter_maybe_set_no_cache_cookie_for_reason( $reason );

        if ( headers_sent() ) {
            return;
        }

        if ( developer_starter_has_no_cache_bypass_header() ) {
            return;
        }

        nocache_headers();
        $no_cache_headers = developer_starter_get_no_cache_header_values();
        foreach ( $no_cache_headers as $name => $value ) {
            header( $name . ': ' . $value, true );
        }
        header( 'X-DS-Cache-Bypass: ' . $reason, true );
    }
}

if ( ! function_exists( 'developer_starter_mark_no_cache_constants' ) ) {
    /**
     * 统一 no-cache 常量前置声明。
     *
     * @return void
     */
    function developer_starter_mark_no_cache_constants() {
        $reason = developer_starter_get_no_cache_reason(
            array(
                'include_auth_template' => false,
            )
        );
        if ( '' === $reason ) {
            return;
        }

        developer_starter_define_no_cache_constants();
    }
}
add_action( 'init', 'developer_starter_mark_no_cache_constants', 1 );

if ( ! function_exists( 'developer_starter_filter_no_cache_headers' ) ) {
    /**
     * 统一 no-cache 头过滤器入口。
     *
     * @param array $headers 响应头数组。
     * @return array
     */
    function developer_starter_filter_no_cache_headers( $headers ) {
        $reason = developer_starter_get_no_cache_reason(
            array(
                'include_auth_template' => true,
            )
        );
        if ( '' === $reason ) {
            return $headers;
        }

        developer_starter_define_no_cache_constants();
        developer_starter_maybe_set_no_cache_cookie_for_reason( $reason );

        return developer_starter_apply_no_cache_headers_array( $headers, $reason );
    }
}
add_filter( 'wp_headers', 'developer_starter_filter_no_cache_headers', 9999 );

if ( ! function_exists( 'developer_starter_enforce_no_cache_headers' ) ) {
    /**
     * 统一 no-cache 头最终兜底入口。
     *
     * @return void
     */
    function developer_starter_enforce_no_cache_headers() {
        $reason = developer_starter_get_no_cache_reason(
            array(
                'include_auth_template' => true,
            )
        );
        if ( '' === $reason ) {
            return;
        }

        developer_starter_apply_no_cache_headers( $reason );
    }
}
add_action( 'send_headers', 'developer_starter_enforce_no_cache_headers', 9999 );
add_action( 'template_redirect', 'developer_starter_enforce_no_cache_headers', 0 );
