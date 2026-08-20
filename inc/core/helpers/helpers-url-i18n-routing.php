<?php
/**
 * Helpers grouped split from class-helpers.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_is_relative_asset_path' ) ) {
    /**
     * 判断是否为站内资源相对路径。
     *
     * 支持：
     * - /wp-content/uploads/xxx.css
     * - wp-content/uploads/xxx.css
     * - assets/custom/app.js
     *
     * @param string $value 原始资源地址。
     * @return bool
     */
    function developer_starter_is_relative_asset_path( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return false;
        }

        if ( 0 === strpos( $value, '//' ) ) {
            return false;
        }

        if ( preg_match( '#^[a-z][a-z0-9+\-.]*:#i', $value ) ) {
            return false;
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_asset_url' ) ) {
    /**
     * 清洗资源地址，兼容完整 URL 与站内相对路径。
     *
     * @param string $value 原始资源地址。
     * @return string
     */
    function developer_starter_sanitize_asset_url( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( developer_starter_is_relative_asset_path( $value ) ) {
            return ltrim( preg_replace( '#/+#', '/', $value ), ' ' );
        }

        return esc_url_raw( $value );
    }
}

if ( ! function_exists( 'developer_starter_normalize_asset_url' ) ) {
    /**
     * 规范化资源地址，确保前台输出时可用于 script/link/img。
     *
     * @param string $value 原始资源地址。
     * @return string
     */
    function developer_starter_normalize_asset_url( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( 0 === strpos( $value, '//' ) ) {
            return is_ssl() ? 'https:' . $value : 'http:' . $value;
        }

        if ( developer_starter_is_relative_asset_path( $value ) ) {
            if ( 0 !== strpos( $value, '/' ) ) {
                $value = '/' . ltrim( $value, '/' );
            }

            return home_url( $value );
        }

        return esc_url_raw( $value );
    }
}

if ( ! function_exists( 'developer_starter_normalize_external_asset_allowed_hosts' ) ) {
    /**
     * 规范化第三方资源允许域名列表。
     *
     * @param string|array<int,string> $hosts 域名、URL、逗号/换行分隔字符串。
     * @return array<int,string>
     */
    function developer_starter_normalize_external_asset_allowed_hosts( $hosts ) {
        if ( is_string( $hosts ) ) {
            $hosts = str_replace( array( '\\r\\n', '\\n', '\\r' ), "\n", $hosts );
            $repaired_hosts = preg_replace( '/([a-z0-9-]\.[a-z]{2,})rn((?:\*?\.)?[a-z0-9-])/i', '$1' . "\n" . '$2', $hosts );
            if ( is_string( $repaired_hosts ) ) {
                $hosts = $repaired_hosts;
            }
            $items = preg_split( '/[\r\n,\s]+/', (string) $hosts );
            $items = is_array( $items ) ? $items : array();
        } elseif ( is_array( $hosts ) ) {
            $items = $hosts;
        } else {
            $items = array();
        }

        $normalized = array();

        foreach ( (array) $items as $item ) {
            $host = trim( (string) $item );
            if ( '' === $host ) {
                continue;
            }

            if ( 0 === strpos( $host, '//' ) ) {
                $host = 'https:' . $host;
            }

            if ( false !== strpos( $host, '://' ) ) {
                $parts = wp_parse_url( $host );
                $host  = ( false !== $parts && ! empty( $parts['host'] ) ) ? (string) $parts['host'] : '';
            } else {
                $host_without_path = preg_replace( '#/.*$#', '', $host );
                $host              = is_string( $host_without_path ) ? $host_without_path : '';
            }

            $host = strtolower( trim( (string) $host ) );
            $host = preg_replace( '/:\d+$/', '', $host );
            $host = ltrim( (string) $host, '.' );
            if ( '' === $host ) {
                continue;
            }

            $wildcard = 0 === strpos( $host, '*.' );
            $base     = $wildcard ? substr( $host, 2 ) : $host;
            if ( '' === $base || ! preg_match( '/^[a-z0-9.-]+$/', $base ) ) {
                continue;
            }

            $normalized[] = $wildcard ? '*.' . $base : $base;
        }

        return array_values( array_unique( $normalized ) );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_external_asset_allowed_hosts' ) ) {
    /**
     * 清洗第三方资源允许域名设置。
     *
     * @param string|array<int,string> $hosts 原始域名列表。
     * @return string
     */
    function developer_starter_sanitize_external_asset_allowed_hosts( $hosts ) {
        return implode( "\n", developer_starter_normalize_external_asset_allowed_hosts( $hosts ) );
    }
}

if ( ! function_exists( 'developer_starter_get_default_external_asset_allowed_hosts' ) ) {
    /**
     * 获取主题默认允许的公共开源库 CDN 域名。
     *
     * @return array<int,string>
     */
    function developer_starter_get_default_external_asset_allowed_hosts() {
        $hosts = array(
            'cdn.jsdelivr.net',
            'unpkg.com',
            'cdnjs.cloudflare.com',
        );

        $hosts = apply_filters( 'developer_starter_external_asset_default_allowed_hosts', $hosts );

        return developer_starter_normalize_external_asset_allowed_hosts( $hosts );
    }
}

if ( ! function_exists( 'developer_starter_get_external_asset_allowed_hosts' ) ) {
    /**
     * 获取第三方资源允许域名列表。
     *
     * @return array<int,string>
     */
    function developer_starter_get_external_asset_allowed_hosts() {
        $configured_hosts = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( 'third_party_asset_allowed_hosts', '' )
            : '';

        $hosts = array_merge(
            developer_starter_get_default_external_asset_allowed_hosts(),
            developer_starter_normalize_external_asset_allowed_hosts( $configured_hosts )
        );

        $hosts = apply_filters( 'developer_starter_external_asset_allowed_hosts', $hosts );

        return developer_starter_normalize_external_asset_allowed_hosts( $hosts );
    }
}

if ( ! function_exists( 'developer_starter_external_asset_host_matches_allowed_hosts' ) ) {
    /**
     * 判断 host 是否命中允许域名列表。
     *
     * @param string            $host          当前资源 host。
     * @param array<int,string> $allowed_hosts 允许域名列表。
     * @return bool
     */
    function developer_starter_external_asset_host_matches_allowed_hosts( $host, $allowed_hosts ) {
        $host = strtolower( trim( (string) $host ) );
        if ( '' === $host ) {
            return false;
        }

        foreach ( developer_starter_normalize_external_asset_allowed_hosts( $allowed_hosts ) as $allowed_host ) {
            if ( $host === $allowed_host ) {
                return true;
            }

            if ( 0 === strpos( $allowed_host, '*.' ) ) {
                $suffix = substr( $allowed_host, 1 );
                if ( substr( $host, -strlen( $suffix ) ) === $suffix ) {
                    return true;
                }
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_get_site_asset_hosts' ) ) {
    /**
     * 获取当前站点自身资源 host。
     *
     * @return array<int,string>
     */
    function developer_starter_get_site_asset_hosts() {
        $hosts = array();

        foreach ( array( home_url( '/' ), site_url( '/' ) ) as $url ) {
            $parts = wp_parse_url( $url );
            if ( false !== $parts && ! empty( $parts['host'] ) ) {
                $hosts[] = (string) $parts['host'];
            }
        }

        return developer_starter_normalize_external_asset_allowed_hosts( $hosts );
    }
}

if ( ! function_exists( 'developer_starter_is_external_asset_url_allowed' ) ) {
    /**
     * 判断第三方库资源 URL 是否允许输出。
     *
     * @param string                 $url           资源 URL。
     * @param array<int,string>|null $allowed_hosts 允许域名列表；null 表示读取主题设置。
     * @return bool
     */
    function developer_starter_is_external_asset_url_allowed( $url, $allowed_hosts = null ) {
        $url = developer_starter_normalize_asset_url( $url );
        if ( '' === $url ) {
            return false;
        }

        $parts = wp_parse_url( $url );
        if ( false === $parts || empty( $parts['host'] ) ) {
            return false;
        }

        $scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
        if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
            return false;
        }

        $host = strtolower( (string) $parts['host'] );
        if ( developer_starter_external_asset_host_matches_allowed_hosts( $host, developer_starter_get_site_asset_hosts() ) ) {
            return true;
        }

        if ( 'https' !== $scheme ) {
            return false;
        }

        $allowed_hosts = null === $allowed_hosts
            ? developer_starter_get_external_asset_allowed_hosts()
            : developer_starter_normalize_external_asset_allowed_hosts( $allowed_hosts );

        return developer_starter_external_asset_host_matches_allowed_hosts( $host, $allowed_hosts );
    }
}

if ( ! function_exists( 'developer_starter_get_third_party_asset_registry' ) ) {
    /**
     * 获取主题内置第三方库资源表。
     *
     * @return array<string,array<string,mixed>>
     */
    function developer_starter_get_third_party_asset_registry() {
        $assets_base = defined( 'DEVELOPER_STARTER_ASSETS' ) ? DEVELOPER_STARTER_ASSETS : '';

        $registry = array(
            'swiper_css' => array(
                'option'    => 'swiper_css_url',
                'local_url' => $assets_base . '/css/vendor/swiper-bundle.min.css',
                'version'   => '12.0.3',
                'filters'   => array( 'developer_starter_swiper_css_url' ),
            ),
            'swiper_js'  => array(
                'option'    => 'swiper_js_url',
                'local_url' => $assets_base . '/js/vendor/swiper-bundle.min.js',
                'version'   => '12.0.3',
                'filters'   => array( 'developer_starter_swiper_js_url' ),
            ),
            'chart_js'   => array(
                'option'    => 'chart_js_cdn',
                'local_url' => $assets_base . '/js/vendor/chart.min.js',
                'version'   => '2.7.2',
                'filters'   => array( 'developer_starter_chart_js_url' ),
            ),
            'prism_css'  => array(
                'option'    => 'prism_css_cdn',
                'local_url' => $assets_base . '/css/vendor/prism.css',
                'version'   => '1.29.0',
                'filters'   => array( 'developer_starter_prism_css_url' ),
            ),
            'prism_js'   => array(
                'option'    => 'prism_js_cdn',
                'local_url' => $assets_base . '/js/vendor/prism.js',
                'version'   => '1.29.0',
                'filters'   => array( 'developer_starter_prism_js_url' ),
            ),
        );

        $registry = apply_filters( 'developer_starter_third_party_asset_registry', $registry );

        return is_array( $registry ) ? $registry : array();
    }
}

if ( ! function_exists( 'developer_starter_get_third_party_asset' ) ) {
    /**
     * 解析第三方库资源：固定版本、外链白名单、非法回退本地。
     *
     * @param string      $asset_key      资源键名。
     * @param string      $context_filter 可选的上下文过滤器。
     * @param string|null $filter_version 传给兼容过滤器的版本参数。
     * @return array<string,string>
     */
    function developer_starter_get_third_party_asset( $asset_key, $context_filter = '', $filter_version = null ) {
        $registry = developer_starter_get_third_party_asset_registry();
        if ( empty( $registry[ $asset_key ] ) || ! is_array( $registry[ $asset_key ] ) ) {
            return array(
                'url'        => '',
                'version'    => '',
                'local_url'  => '',
                'option_key' => '',
            );
        }

        $config     = $registry[ $asset_key ];
        $version    = isset( $config['version'] ) ? (string) $config['version'] : '';
        $option_key = isset( $config['option'] ) ? (string) $config['option'] : '';
        $local_url  = isset( $config['local_url'] ) ? developer_starter_normalize_asset_url( (string) $config['local_url'] ) : '';

        $configured_url = ( $option_key && function_exists( 'developer_starter_get_option' ) )
            ? (string) developer_starter_get_option( $option_key, '' )
            : '';

        $url = '' !== trim( $configured_url )
            ? developer_starter_normalize_asset_url( $configured_url )
            : $local_url;

        $filter_version = null === $filter_version ? $version : (string) $filter_version;
        $filters        = isset( $config['filters'] ) && is_array( $config['filters'] ) ? $config['filters'] : array();
        foreach ( $filters as $filter_name ) {
            if ( is_string( $filter_name ) && '' !== $filter_name ) {
                $url = apply_filters( $filter_name, $url, $filter_version, $asset_key, $version );
            }
        }

        if ( is_string( $context_filter ) && '' !== $context_filter ) {
            $url = apply_filters( $context_filter, $url, $filter_version, $asset_key, $version );
        }

        $url = developer_starter_normalize_asset_url( $url );
        if ( '' === $url || ( $url !== $local_url && ! developer_starter_is_external_asset_url_allowed( $url ) ) ) {
            $url = $local_url;
        }

        return array(
            'url'        => (string) $url,
            'version'    => (string) $version,
            'local_url'  => (string) $local_url,
            'option_key' => (string) $option_key,
        );
    }
}

if ( ! function_exists( 'developer_starter_get_raw_home_base_url' ) ) {
    /**
     * 获取未经过 home_url 过滤器处理的站点首页基础地址。
     *
     * @return string
     */
    function developer_starter_get_raw_home_base_url() {
        static $base_url = null;

        if ( null !== $base_url ) {
            return $base_url;
        }

        $home = (string) get_option( 'home' );
        if ( '' === $home ) {
            $base_url = untrailingslashit( home_url( '/' ) );
            return $base_url;
        }

        $parts = wp_parse_url( $home );
        if ( false === $parts || empty( $parts['host'] ) ) {
            $base_url = untrailingslashit( home_url( '/' ) );
            return $base_url;
        }

        $scheme = ! empty( $parts['scheme'] ) ? (string) $parts['scheme'] : ( is_ssl() ? 'https' : 'http' );
        $host   = (string) $parts['host'];
        $port   = isset( $parts['port'] ) ? ':' . (string) $parts['port'] : '';
        $path   = isset( $parts['path'] ) ? untrailingslashit( (string) $parts['path'] ) : '';

        $base_url = $scheme . '://' . $host . $port . $path;

        return $base_url;
    }
}

if ( ! function_exists( 'developer_starter_get_raw_home_path' ) ) {
    /**
     * 获取站点首页原始路径，避免语言前缀过滤干扰路径判断。
     *
     * @return string
     */
    function developer_starter_get_raw_home_path() {
        return trim( (string) wp_parse_url( developer_starter_get_raw_home_base_url(), PHP_URL_PATH ), '/' );
    }
}

if ( ! function_exists( 'developer_starter_build_raw_home_url' ) ) {
    /**
     * 基于原始站点首页地址构建站内 URL，避免被前台语言过滤再次改写。
     *
     * @param string $path 站内路径或请求 URI。
     * @return string
     */
    function developer_starter_build_raw_home_url( $path = '/' ) {
        $path = (string) $path;

        if ( '' === $path || '/' === $path ) {
            return trailingslashit( developer_starter_get_raw_home_base_url() );
        }

        $parts = wp_parse_url( $path );
        if ( false !== $parts && ! empty( $parts['scheme'] ) && ! empty( $parts['host'] ) ) {
            return $path;
        }

        $query    = '';
        $fragment = '';
        if ( false !== $parts ) {
            $query    = isset( $parts['query'] ) ? (string) $parts['query'] : '';
            $fragment = isset( $parts['fragment'] ) ? (string) $parts['fragment'] : '';
            $path     = isset( $parts['path'] ) ? (string) $parts['path'] : $path;
        }

        $url = trailingslashit( developer_starter_get_raw_home_base_url() ) . ltrim( $path, '/' );

        if ( '' !== $query ) {
            $url .= '?' . $query;
        }

        if ( '' !== $fragment ) {
            $url .= '#' . $fragment;
        }

        return $url;
    }
}

if ( ! function_exists( 'developer_starter_get_normalized_host_from_value' ) ) {
    /**
     * 从域名或 URL 中提取标准化 host。
     *
     * @param string $value 域名或 URL。
     * @return string
     */
    function developer_starter_get_normalized_host_from_value( $value ) {
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        $host = wp_parse_url( $value, PHP_URL_HOST );
        if ( is_string( $host ) && '' !== $host ) {
            return strtolower( trim( $host, '.' ) );
        }

        $value = preg_replace( '#^https?://#i', '', $value );
        $value = preg_replace( '#^//#', '', $value );
        $value = trim( (string) $value, " \t\n\r\0\x0B/" );

        if ( '' === $value ) {
            return '';
        }

        if ( false !== strpos( $value, '/' ) ) {
            $value = strtok( $value, '/' );
        }
        if ( false !== strpos( $value, '?' ) ) {
            $value = strtok( $value, '?' );
        }
        if ( false !== strpos( $value, '#' ) ) {
            $value = strtok( $value, '#' );
        }

        return strtolower( trim( (string) $value, '.' ) );
    }
}

if ( ! function_exists( 'developer_starter_normalize_home_base_candidate' ) ) {
    /**
     * 将用户输入的域名或 URL 规范化为站点基准地址。
     *
     * 支持：
     * - https://old.example.com
     * - old.example.com
     * - http://old.example.com/subdir
     *
     * @param string $value         域名或 URL。
     * @param string $fallback_base 当前站点基准地址，用于补充 scheme/path。
     * @return string
     */
    function developer_starter_normalize_home_base_candidate( $value, $fallback_base = '' ) {
        $value         = trim( (string) $value );
        $fallback_base = untrailingslashit( trim( (string) $fallback_base ) );

        if ( '' === $value ) {
            return '';
        }

        $parts = wp_parse_url( $value );
        if ( false !== $parts && ! empty( $parts['host'] ) ) {
            $scheme = ! empty( $parts['scheme'] ) ? (string) $parts['scheme'] : '';
            $host   = (string) $parts['host'];
            $port   = isset( $parts['port'] ) ? ':' . (string) $parts['port'] : '';
            $path   = isset( $parts['path'] ) ? untrailingslashit( (string) $parts['path'] ) : '';

            if ( '' === $scheme ) {
                $scheme = (string) wp_parse_url( $fallback_base, PHP_URL_SCHEME );
                if ( '' === $scheme ) {
                    $scheme = is_ssl() ? 'https' : 'http';
                }
            }

            return untrailingslashit( $scheme . '://' . $host . $port . $path );
        }

        $host = developer_starter_get_normalized_host_from_value( $value );
        if ( '' === $host ) {
            return '';
        }

        $scheme = (string) wp_parse_url( $fallback_base, PHP_URL_SCHEME );
        if ( '' === $scheme ) {
            $scheme = is_ssl() ? 'https' : 'http';
        }

        $port = wp_parse_url( $fallback_base, PHP_URL_PORT );
        $path = trim( (string) wp_parse_url( $fallback_base, PHP_URL_PATH ), '/' );

        $normalized = $scheme . '://' . $host;
        if ( ! empty( $port ) ) {
            $normalized .= ':' . (string) $port;
        }
        if ( '' !== $path ) {
            $normalized .= '/' . $path;
        }

        return untrailingslashit( $normalized );
    }
}

if ( ! function_exists( 'developer_starter_remap_internal_url_to_new_home' ) ) {
    /**
     * 将旧站点绝对 URL 重写为当前站点域名。
     *
     * 仅在 URL 明确指向旧站点时重写，外链保持不变。
     *
     * @param string $value        原始 URL。
     * @param string $old_base_url 旧站点基准地址。
     * @param string $new_base_url 新站点基准地址。
     * @return string
     */
    function developer_starter_remap_internal_url_to_new_home( $value, $old_base_url, $new_base_url ) {
        $value        = trim( (string) $value );
        $old_base_url = untrailingslashit( trim( (string) $old_base_url ) );
        $new_base_url = untrailingslashit( trim( (string) $new_base_url ) );

        if ( '' === $value || '' === $old_base_url || '' === $new_base_url || $old_base_url === $new_base_url ) {
            return $value;
        }

        if ( 0 === strpos( $value, $old_base_url ) ) {
            return $new_base_url . substr( $value, strlen( $old_base_url ) );
        }

        $value_parts = wp_parse_url( $value );
        $old_parts   = wp_parse_url( $old_base_url );
        $new_parts   = wp_parse_url( $new_base_url );

        if (
            false === $value_parts || false === $old_parts || false === $new_parts
            || empty( $value_parts['host'] ) || empty( $old_parts['host'] ) || empty( $new_parts['host'] )
        ) {
            return $value;
        }

        $value_host = strtolower( trim( (string) $value_parts['host'], '.' ) );
        $old_host   = strtolower( trim( (string) $old_parts['host'], '.' ) );
        if ( $value_host !== $old_host ) {
            return $value;
        }

        $value_path = isset( $value_parts['path'] ) ? (string) $value_parts['path'] : '';
        $old_path   = untrailingslashit( isset( $old_parts['path'] ) ? (string) $old_parts['path'] : '' );
        $new_path   = untrailingslashit( isset( $new_parts['path'] ) ? (string) $new_parts['path'] : '' );

        if ( '' !== $old_path ) {
            if ( $value_path === $old_path ) {
                $value_path = '';
            } elseif ( 0 === strpos( $value_path, $old_path . '/' ) ) {
                $value_path = substr( $value_path, strlen( $old_path ) );
            } else {
                return $value;
            }
        }

        $scheme   = isset( $new_parts['scheme'] ) ? (string) $new_parts['scheme'] : ( is_ssl() ? 'https' : 'http' );
        $host     = (string) $new_parts['host'];
        $port     = isset( $new_parts['port'] ) ? ':' . (string) $new_parts['port'] : '';
        $path     = '' === $value_path ? $new_path : $new_path . '/' . ltrim( $value_path, '/' );
        $remapped = $scheme . '://' . $host . $port;

        if ( '' !== $path ) {
            $remapped .= '/' . ltrim( $path, '/' );
        }

        if ( ! empty( $value_parts['query'] ) ) {
            $remapped .= '?' . (string) $value_parts['query'];
        }

        if ( ! empty( $value_parts['fragment'] ) ) {
            $remapped .= '#' . (string) $value_parts['fragment'];
        }

        return $remapped;
    }
}

if ( ! function_exists( 'developer_starter_get_domain_scan_whitelist_hosts' ) ) {
    /**
     * 解析域名检查白名单。
     *
     * @param array<int|string, mixed> $options 主题设置数组。
     * @return array<int, string>
     */
    function developer_starter_get_domain_scan_whitelist_hosts( $options ) {
        $raw_value = '';

        if ( is_array( $options ) && isset( $options['domain_check_whitelist'] ) ) {
            $raw_value = (string) $options['domain_check_whitelist'];
        }

        if ( '' === trim( $raw_value ) ) {
            return array();
        }

        $items = preg_split( '/[\r\n,，]+/', $raw_value );
        if ( ! is_array( $items ) ) {
            return array();
        }

        $hosts = array();

        foreach ( $items as $item ) {
            $item = trim( (string) $item );
            if ( '' === $item ) {
                continue;
            }

            $host = wp_parse_url( $item, PHP_URL_HOST );
            if ( ! is_string( $host ) || '' === $host ) {
                $host = wp_parse_url( 'https://' . ltrim( $item, '/' ), PHP_URL_HOST );
            }
            if ( ! is_string( $host ) || '' === $host ) {
                $host = $item;
            }

            $host = strtolower( trim( (string) $host, " \t\n\r\0\x0B." ) );
            if ( '' !== $host ) {
                $hosts[ $host ] = $host;
            }
        }

        return array_values( $hosts );
    }
}

if ( ! function_exists( 'developer_starter_is_domain_scan_host_whitelisted' ) ) {
    /**
     * 判断域名是否在检查白名单内。
     *
     * @param string              $host            待检查域名。
     * @param array<int, string>  $whitelist_hosts 白名单域名列表。
     * @return bool
     */
    function developer_starter_is_domain_scan_host_whitelisted( $host, $whitelist_hosts ) {
        $host = strtolower( trim( (string) $host, " \t\n\r\0\x0B." ) );
        if ( '' === $host || empty( $whitelist_hosts ) || ! is_array( $whitelist_hosts ) ) {
            return false;
        }

        foreach ( $whitelist_hosts as $whitelist_host ) {
            $whitelist_host = strtolower( trim( (string) $whitelist_host, " \t\n\r\0\x0B." ) );
            if ( '' === $whitelist_host ) {
                continue;
            }

            if ( $host === $whitelist_host ) {
                return true;
            }

            if ( 0 === strpos( $whitelist_host, '*.' ) ) {
                $base_host = substr( $whitelist_host, 2 );
                if ( '' !== $base_host && preg_match( '/(^|\.)' . preg_quote( $base_host, '/' ) . '$/i', $host ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_is_domain_scan_base_whitelisted' ) ) {
    /**
     * 判断站点基准地址是否在检查白名单内。
     *
     * @param string             $base_url         站点基准地址。
     * @param array<int, string> $whitelist_hosts  白名单域名列表。
     * @return bool
     */
    function developer_starter_is_domain_scan_base_whitelisted( $base_url, $whitelist_hosts ) {
        $host = developer_starter_get_normalized_host_from_value( $base_url );
        return developer_starter_is_domain_scan_host_whitelisted( $host, $whitelist_hosts );
    }
}

if ( ! function_exists( 'developer_starter_collect_theme_option_domain_candidates_recursive' ) ) {
    /**
     * 递归收集主题设置中疑似旧域名的候选站点地址。
     *
     * @param mixed                 $value        设置值。
     * @param string                $current_base 当前站点基准地址。
     * @param array<string, string> $candidates   候选列表（引用）。
     * @param string                $key          当前键名。
     * @param array<int, string>    $whitelist_hosts 白名单域名列表。
     * @return void
     */
    function developer_starter_collect_theme_option_domain_candidates_recursive( $value, $current_base, &$candidates, $key = '', $whitelist_hosts = array() ) {
        $key = (string) $key;

        if ( is_array( $value ) ) {
            foreach ( $value as $child_key => $child_value ) {
                developer_starter_collect_theme_option_domain_candidates_recursive( $child_value, $current_base, $candidates, (string) $child_key, $whitelist_hosts );
            }
            return;
        }

        if ( ! is_string( $value ) || '' === trim( $value ) ) {
            return;
        }

        $candidate_base = developer_starter_guess_home_base_from_internal_url( $value );
        if ( '' !== $candidate_base ) {
            $candidate_base = developer_starter_normalize_home_base_candidate( $candidate_base, $current_base );
            if (
                '' !== $candidate_base
                && untrailingslashit( $candidate_base ) !== untrailingslashit( $current_base )
                && ! developer_starter_is_domain_scan_base_whitelisted( $candidate_base, $whitelist_hosts )
            ) {
                $candidates[ $candidate_base ] = $candidate_base;
            }
        }

        if ( 'seo_push_baidu_site' === $key ) {
            $candidate_base = developer_starter_normalize_home_base_candidate( $value, $current_base );
            if (
                '' !== $candidate_base
                && untrailingslashit( $candidate_base ) !== untrailingslashit( $current_base )
                && ! developer_starter_is_domain_scan_base_whitelisted( $candidate_base, $whitelist_hosts )
            ) {
                $candidates[ $candidate_base ] = $candidate_base;
            }
        }
    }
}

if ( ! function_exists( 'developer_starter_get_theme_option_domain_candidates' ) ) {
    /**
     * 获取主题设置中可用于比对的旧域名候选值。
     *
     * @param array<int|string, mixed> $options 主题设置数组。
     * @return array<int, string>
     */
    function developer_starter_get_theme_option_domain_candidates( $options ) {
        $current_base = untrailingslashit( developer_starter_get_raw_home_base_url() );
        $candidates   = array();
        $whitelist_hosts = developer_starter_get_domain_scan_whitelist_hosts( $options );

        $stored_base = untrailingslashit( (string) get_option( 'developer_starter_last_known_home_base_url', '' ) );
        if (
            '' !== $stored_base
            && $stored_base !== $current_base
            && ! developer_starter_is_domain_scan_base_whitelisted( $stored_base, $whitelist_hosts )
        ) {
            $candidates[ $stored_base ] = $stored_base;
        }

        $guessed_base = developer_starter_guess_previous_home_base_url_from_theme_options( $options );
        if ( '' !== $guessed_base ) {
            $guessed_base = developer_starter_normalize_home_base_candidate( $guessed_base, $current_base );
            if (
                '' !== $guessed_base
                && $guessed_base !== $current_base
                && ! developer_starter_is_domain_scan_base_whitelisted( $guessed_base, $whitelist_hosts )
            ) {
                $candidates[ $guessed_base ] = $guessed_base;
            }
        }

        if ( is_array( $options ) && ! empty( $options ) ) {
            developer_starter_collect_theme_option_domain_candidates_recursive( $options, $current_base, $candidates, '', $whitelist_hosts );
        }

        ksort( $candidates, SORT_NATURAL );

        return array_values( $candidates );
    }
}

if ( ! function_exists( 'developer_starter_migrate_theme_option_urls_recursive' ) ) {
    /**
     * 递归修复主题设置中的旧域名 URL。
     *
     * @param mixed  $value        原始值。
     * @param string $old_base_url 旧站点基准地址。
     * @param string $new_base_url 新站点基准地址。
     * @param string $key          当前数组键名。
     * @return mixed
     */
    function developer_starter_migrate_theme_option_urls_recursive( $value, $old_base_url, $new_base_url, $key = '' ) {
        $key = (string) $key;

        if ( is_array( $value ) ) {
            foreach ( $value as $child_key => $child_value ) {
                $value[ $child_key ] = developer_starter_migrate_theme_option_urls_recursive( $child_value, $old_base_url, $new_base_url, (string) $child_key );
            }

            return $value;
        }

        if ( ! is_string( $value ) || '' === trim( $value ) ) {
            return $value;
        }

        $skip_keys = array(
            'site_logo_svg',
            'mobile_logo_svg',
            'baidu_analytics',
            'custom_js',
        );

        if ( in_array( $key, $skip_keys, true ) ) {
            return $value;
        }

        if ( 'seo_push_baidu_site' === $key ) {
            $old_host = developer_starter_get_normalized_host_from_value( $old_base_url );
            $new_host = developer_starter_get_normalized_host_from_value( $new_base_url );
            $host     = developer_starter_get_normalized_host_from_value( $value );

            if ( '' !== $old_host && '' !== $new_host && $host === $old_host ) {
                return $new_host;
            }

            return $value;
        }

        return developer_starter_remap_internal_url_to_new_home( $value, $old_base_url, $new_base_url );
    }
}

if ( ! function_exists( 'developer_starter_collect_theme_option_domain_compare_rows_recursive' ) ) {
    /**
     * 递归生成主题设置域名比对结果。
     *
     * @param mixed                             $value        设置值。
     * @param string                            $old_base_url 旧站点基准地址。
     * @param string                            $new_base_url 新站点基准地址。
     * @param array<int, array<string, string>> $rows         比对结果（引用）。
     * @param string                            $path         当前路径。
     * @param string                            $key          当前键名。
     * @param array<int, string>             $whitelist_hosts 白名单域名列表。
     * @return void
     */
    function developer_starter_collect_theme_option_domain_compare_rows_recursive( $value, $old_base_url, $new_base_url, &$rows, $path = '', $key = '', $whitelist_hosts = array() ) {
        $key = (string) $key;

        if ( is_array( $value ) ) {
            foreach ( $value as $child_key => $child_value ) {
                $child_key  = (string) $child_key;
                $child_path = '' === $path
                    ? ( ctype_digit( $child_key ) ? '[' . $child_key . ']' : $child_key )
                    : ( ctype_digit( $child_key ) ? $path . '[' . $child_key . ']' : $path . '.' . $child_key );

                developer_starter_collect_theme_option_domain_compare_rows_recursive( $child_value, $old_base_url, $new_base_url, $rows, $child_path, $child_key, $whitelist_hosts );
            }
            return;
        }

        if ( ! is_string( $value ) || '' === trim( $value ) ) {
            return;
        }

        $suggested = developer_starter_migrate_theme_option_urls_recursive( $value, $old_base_url, $new_base_url, $key );
        if ( $suggested === $value ) {
            return;
        }

        $rows[] = array(
            'path'      => $path,
            'current'   => $value,
            'suggested' => $suggested,
        );
    }
}

if ( ! function_exists( 'developer_starter_get_theme_option_domain_compare_rows' ) ) {
    /**
     * 获取主题设置域名差异预览。
     *
     * @param array<int|string, mixed> $options       主题设置数组。
     * @param string                   $old_base_url  旧站点基准地址。
     * @param string                   $new_base_url  新站点基准地址。
     * @return array<int, array<string, string>>
     */
    function developer_starter_get_theme_option_domain_compare_rows( $options, $old_base_url, $new_base_url ) {
        $rows = array();
        $whitelist_hosts = developer_starter_get_domain_scan_whitelist_hosts( $options );

        if ( ! is_array( $options ) || empty( $options ) ) {
            return $rows;
        }

        if ( developer_starter_is_domain_scan_base_whitelisted( $old_base_url, $whitelist_hosts ) ) {
            return $rows;
        }

        developer_starter_collect_theme_option_domain_compare_rows_recursive( $options, $old_base_url, $new_base_url, $rows, '', '', $whitelist_hosts );

        return $rows;
    }
}

if ( ! function_exists( 'developer_starter_convert_internal_absolute_url_to_relative' ) ) {
    /**
     * 将当前站内绝对 URL 转换为相对路径。
     *
     * @param string $url          绝对 URL。
     * @param string $current_base 当前站点基准地址。
     * @return string
     */
    function developer_starter_convert_internal_absolute_url_to_relative( $url, $current_base = '' ) {
        $url          = trim( (string) $url );
        $current_base = untrailingslashit( trim( (string) $current_base ) );

        if ( '' === $url ) {
            return '';
        }
        if ( '' === $current_base ) {
            $current_base = untrailingslashit( developer_starter_get_raw_home_base_url() );
        }
        if ( '' === $current_base ) {
            return '';
        }

        $url_parts     = wp_parse_url( $url );
        $current_parts = wp_parse_url( $current_base );

        if (
            false === $url_parts || false === $current_parts
            || empty( $url_parts['host'] ) || empty( $current_parts['host'] )
        ) {
            return '';
        }

        $url_host     = strtolower( trim( (string) $url_parts['host'], '.' ) );
        $current_host = strtolower( trim( (string) $current_parts['host'], '.' ) );
        if ( $url_host !== $current_host ) {
            return '';
        }

        $url_path      = isset( $url_parts['path'] ) ? (string) $url_parts['path'] : '';
        $current_path  = untrailingslashit( isset( $current_parts['path'] ) ? (string) $current_parts['path'] : '' );
        $relative_path = $url_path;

        if ( '' !== $current_path ) {
            if ( $url_path === $current_path ) {
                $relative_path = '/';
            } elseif ( 0 === strpos( $url_path, $current_path . '/' ) ) {
                $relative_path = substr( $url_path, strlen( $current_path ) );
            } else {
                return '';
            }
        }

        if ( '' === $relative_path ) {
            $relative_path = '/';
        }

        $relative = '/' . ltrim( $relative_path, '/' );

        if ( ! empty( $url_parts['query'] ) ) {
            $relative .= '?' . (string) $url_parts['query'];
        }
        if ( ! empty( $url_parts['fragment'] ) ) {
            $relative .= '#' . (string) $url_parts['fragment'];
        }

        return $relative;
    }
}

if ( ! function_exists( 'developer_starter_replace_internal_absolute_urls_with_relative' ) ) {
    /**
     * 将字符串中的站内绝对 URL 批量替换为相对路径。
     *
     * @param string $value        原始字符串。
     * @param string $current_base 当前站点基准地址。
     * @return string
     */
    function developer_starter_replace_internal_absolute_urls_with_relative( $value, $current_base = '' ) {
        $value = (string) $value;
        if ( '' === trim( $value ) ) {
            return $value;
        }

        return preg_replace_callback(
            '#https?://[^\s"\'<>]+#i',
            static function ( $matches ) use ( $current_base ) {
                $candidate = isset( $matches[0] ) ? (string) $matches[0] : '';
                $relative  = developer_starter_convert_internal_absolute_url_to_relative( $candidate, $current_base );

                return '' !== $relative ? $relative : $candidate;
            },
            $value
        );
    }
}

if ( ! function_exists( 'developer_starter_collect_theme_option_domain_risk_rows_recursive' ) ) {
    /**
     * 递归扫描主题设置中的域名依赖风险。
     *
     * @param mixed                             $value        设置值。
     * @param string                            $current_base 当前站点基准地址。
     * @param array<int, array<string, string>> $rows         风险结果（引用）。
     * @param string                            $path         当前路径。
     * @param string                            $key          当前键名。
     * @return void
     */
    function developer_starter_collect_theme_option_domain_risk_rows_recursive( $value, $current_base, &$rows, $path = '', $key = '' ) {
        $key = (string) $key;

        if ( is_array( $value ) ) {
            foreach ( $value as $child_key => $child_value ) {
                $child_key  = (string) $child_key;
                $child_path = '' === $path
                    ? ( ctype_digit( $child_key ) ? '[' . $child_key . ']' : $child_key )
                    : ( ctype_digit( $child_key ) ? $path . '[' . $child_key . ']' : $path . '.' . $child_key );

                developer_starter_collect_theme_option_domain_risk_rows_recursive( $child_value, $current_base, $rows, $child_path, $child_key );
            }
            return;
        }

        if ( ! is_string( $value ) || '' === trim( $value ) ) {
            return;
        }

        $skip_keys = array(
            'site_logo_svg',
            'mobile_logo_svg',
            'baidu_analytics',
            'custom_js',
            'seo_push_baidu_site',
        );

        if ( in_array( $key, $skip_keys, true ) ) {
            return;
        }

        $current_base = untrailingslashit( trim( (string) $current_base ) );
        if ( '' === $current_base ) {
            return;
        }

        $direct_relative = developer_starter_convert_internal_absolute_url_to_relative( $value, $current_base );
        if ( '' !== $direct_relative && $direct_relative !== $value ) {
            $rows[] = array(
                'path'      => $path,
                'risk_type' => __( '站内绝对地址', 'developer-starter' ),
                'current'   => $value,
                'suggested' => $direct_relative,
            );
            return;
        }

        $replaced_value = developer_starter_replace_internal_absolute_urls_with_relative( $value, $current_base );
        if ( $replaced_value !== $value ) {
            $rows[] = array(
                'path'      => $path,
                'risk_type' => __( '文本/HTML 内嵌站内绝对地址', 'developer-starter' ),
                'current'   => $value,
                'suggested' => $replaced_value,
            );
        }
    }
}

if ( ! function_exists( 'developer_starter_get_theme_option_domain_risk_rows' ) ) {
    /**
     * 获取主题设置中的域名依赖风险列表。
     *
     * @param array<int|string, mixed> $options 主题设置数组。
     * @return array<int, array<string, string>>
     */
    function developer_starter_get_theme_option_domain_risk_rows( $options ) {
        $rows         = array();
        $current_base = untrailingslashit( developer_starter_get_raw_home_base_url() );

        if ( ! is_array( $options ) || empty( $options ) || '' === $current_base ) {
            return $rows;
        }

        developer_starter_collect_theme_option_domain_risk_rows_recursive( $options, $current_base, $rows );

        return $rows;
    }
}

if ( ! function_exists( 'developer_starter_guess_home_base_from_internal_url' ) ) {
    /**
     * 从站内资源 URL 猜测站点基础地址。
     *
     * @param string $url 资源 URL。
     * @return string
     */
    function developer_starter_guess_home_base_from_internal_url( $url ) {
        $url = trim( (string) $url );
        if ( '' === $url ) {
            return '';
        }

        $parts = wp_parse_url( $url );
        if ( false === $parts || empty( $parts['host'] ) ) {
            return '';
        }

        $scheme = ! empty( $parts['scheme'] ) ? (string) $parts['scheme'] : 'https';
        $host   = (string) $parts['host'];
        $port   = isset( $parts['port'] ) ? ':' . (string) $parts['port'] : '';
        $path   = isset( $parts['path'] ) ? (string) $parts['path'] : '';

        $markers = array(
            '/wp-content/',
            '/wp-includes/',
            '/wp-admin/',
        );

        $base_path = '';
        foreach ( $markers as $marker ) {
            $marker_pos = strpos( $path, $marker );
            if ( false !== $marker_pos ) {
                $base_path = substr( $path, 0, $marker_pos );
                break;
            }
        }

        return untrailingslashit( $scheme . '://' . $host . $port . $base_path );
    }
}

if ( ! function_exists( 'developer_starter_find_first_theme_option_value_by_keys' ) ) {
    /**
     * 在主题设置数组中递归查找指定键名的首个字符串值。
     *
     * @param array<int|string, mixed> $values 主题设置数组。
     * @param array<int, string>       $keys   目标键名。
     * @return string
     */
    function developer_starter_find_first_theme_option_value_by_keys( $values, $keys ) {
        foreach ( $values as $key => $value ) {
            if ( is_array( $value ) ) {
                $found = developer_starter_find_first_theme_option_value_by_keys( $value, $keys );
                if ( '' !== $found ) {
                    return $found;
                }
                continue;
            }

            if ( is_string( $value ) && in_array( (string) $key, $keys, true ) && '' !== trim( $value ) ) {
                return trim( $value );
            }
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_guess_previous_home_base_url_from_theme_options' ) ) {
    /**
     * 从主题设置中猜测旧站点基础地址。
     *
     * 主要用于老版本主题首次引入域名迁移功能时，兼容旧数据库。
     *
     * @param array<int|string, mixed> $options 主题设置数组。
     * @return string
     */
    function developer_starter_guess_previous_home_base_url_from_theme_options( $options ) {
        if ( ! is_array( $options ) || empty( $options ) ) {
            return '';
        }

        $candidate_keys = array(
            'site_logo',
            'mobile_logo',
            'footer_logo',
            'footer_bg_image',
            'announcement_image',
            'lcp_preload_custom_url',
            'auth_page_background_image',
            'auth_page_side_image',
            'auth_modal_side_image',
        );

        $candidate_url = developer_starter_find_first_theme_option_value_by_keys( $options, $candidate_keys );
        if ( '' === $candidate_url ) {
            return '';
        }

        return developer_starter_guess_home_base_from_internal_url( $candidate_url );
    }
}

if ( ! function_exists( 'developer_starter_maybe_migrate_domain_dependent_theme_settings' ) ) {
    /**
     * 当站点域名变更时，自动修复主题设置里依赖旧域名的内部 URL。
     *
     * @return void
     */
    function developer_starter_maybe_migrate_domain_dependent_theme_settings() {
        static $migrated = false;

        if ( $migrated || wp_installing() ) {
            return;
        }
        $migrated = true;

        $current_base = untrailingslashit( developer_starter_get_raw_home_base_url() );
        if ( '' === $current_base ) {
            return;
        }

        $tracker_option = 'developer_starter_last_known_home_base_url';
        $stored_base    = untrailingslashit( (string) get_option( $tracker_option, '' ) );

        // 域名已稳定后直接跳过，避免每个请求都为自动修复再做额外检查。
        if ( '' !== $stored_base && $stored_base === $current_base ) {
            return;
        }

        // 仅在首次建立域名跟踪，或检测到域名变化时，尝试修复一次 SQL 替换造成的坏数据。
        if ( function_exists( 'developer_starter_maybe_auto_repair_serialized_option_from_raw' ) ) {
            developer_starter_maybe_auto_repair_serialized_option_from_raw( 'developer_starter_options', true );
            developer_starter_maybe_auto_repair_serialized_option_from_raw( 'developer_starter_careers_options' );
        }

        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        if ( '' === $stored_base ) {
            $stored_base = developer_starter_guess_previous_home_base_url_from_theme_options( $options );
            if ( '' === $stored_base || $stored_base === $current_base ) {
                update_option( $tracker_option, $current_base, false );
                return;
            }
        }

        if ( is_array( $options ) && ! empty( $options ) ) {
            $migrated_options = developer_starter_migrate_theme_option_urls_recursive( $options, $stored_base, $current_base );

            if ( $migrated_options !== $options ) {
                update_option( 'developer_starter_options', $migrated_options );
            }
        }

        update_option( $tracker_option, $current_base, false );
    }
}




/**
 * 获取某个业务场景的通知方式
 *
 * @param string $scene   业务场景：message/form/careers。
 * @param string $default 默认通知方式。
 * @return string
 */
if ( ! function_exists( 'developer_starter_get_notify_method' ) ) {
    function developer_starter_get_notify_method( $scene, $default = 'none' ) {
        $scene = sanitize_key( $scene );
        $mode  = developer_starter_get_option( 'notify_' . $scene . '_method', '' );

        if ( ! in_array( $mode, array( 'none', 'email', 'push', 'both' ), true ) ) {
            $mode = $default;
        }

        return $mode;
    }
}

if ( ! function_exists( 'developer_starter_get_current_locale' ) ) {
    /**
     * 获取当前请求的 locale。
     *
     * @return string
     */
    function developer_starter_get_current_locale() {
        if ( function_exists( 'determine_locale' ) ) {
            return (string) determine_locale();
        }

        return (string) get_locale();
    }
}

if ( ! function_exists( 'developer_starter_get_date_time_format' ) ) {
    /**
     * 获取本地化日期时间格式。
     *
     * @param bool $include_time 是否包含时间。
     * @return string
     */
    function developer_starter_get_date_time_format( $include_time = false ) {
        $date_format = (string) get_option( 'date_format' );
        $time_format = (string) get_option( 'time_format' );

        if ( '' === $date_format ) {
            $date_format = 'Y-m-d';
        }

        if ( ! $include_time ) {
            return $date_format;
        }

        if ( '' === $time_format ) {
            $time_format = 'H:i';
        }

        return trim( $date_format . ' ' . $time_format );
    }
}

if ( ! function_exists( 'developer_starter_is_chinese_locale' ) ) {
    /**
     * 当前 locale 是否为中文。
     *
     * @return bool
     */
    function developer_starter_is_chinese_locale() {
        return 0 === strpos( developer_starter_get_current_locale(), 'zh_' );
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_language_switch_mode' ) ) {
    /**
     * 获取前台语言切换模式。
     *
     * @return string
     */
    function developer_starter_get_frontend_language_switch_mode() {
        $mode = (string) developer_starter_get_option( 'frontend_language_switch_mode', '' );

        if ( ! in_array( $mode, array( '', 'translate_js', 'multilingual_content' ), true ) ) {
            $mode = '';
        }

        return $mode;
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_languages' ) ) {
    /**
     * 获取多语言内容模式的语言列表。
     *
     * @return array<int, array{name: string, code: string, locale: string, icon: string}>
     */
    function developer_starter_get_multilingual_languages() {
        $languages = developer_starter_get_option( 'multilingual_languages', array() );

        if ( empty( $languages ) || ! is_array( $languages ) ) {
            $languages = array(
                array(
                    'name'   => __( '简体中文', 'developer-starter' ),
                    'code'   => 'zh',
                    'locale' => 'zh_CN',
                    'icon'   => 'CN',
                ),
                array(
                    'name'   => __( '繁体中文', 'developer-starter' ),
                    'code'   => 'zh-tw',
                    'locale' => 'zh_TW',
                    'icon'   => 'TW',
                ),
                array(
                    'name'   => 'English',
                    'code'   => 'en',
                    'locale' => 'en_US',
                    'icon'   => 'US',
                ),
                array(
                    'name'   => __( '日文', 'developer-starter' ),
                    'code'   => 'jp',
                    'locale' => 'ja_JP',
                    'icon'   => 'JP',
                ),
                array(
                    'name'   => __( '韩文', 'developer-starter' ),
                    'code'   => 'ko',
                    'locale' => 'ko_KR',
                    'icon'   => 'KR',
                ),
                array(
                    'name'   => __( '法文', 'developer-starter' ),
                    'code'   => 'fr',
                    'locale' => 'fr_FR',
                    'icon'   => 'FR',
                ),
                array(
                    'name'   => __( '德文', 'developer-starter' ),
                    'code'   => 'de',
                    'locale' => 'de_DE',
                    'icon'   => 'DE',
                ),
                array(
                    'name'   => __( '西班牙文', 'developer-starter' ),
                    'code'   => 'es',
                    'locale' => 'es_ES',
                    'icon'   => 'ES',
                ),
            );
        }

        $normalized = array();
        $seen_codes = array();

        foreach ( $languages as $language ) {
            if ( ! is_array( $language ) ) {
                continue;
            }

            $name   = isset( $language['name'] ) ? trim( (string) $language['name'] ) : '';
            $code   = isset( $language['code'] ) ? sanitize_title( (string) $language['code'] ) : '';
            $locale = isset( $language['locale'] ) ? trim( (string) $language['locale'] ) : '';
            $icon   = isset( $language['icon'] ) ? trim( (string) $language['icon'] ) : '';

            if ( 'ja' === $code ) {
                $code = 'jp';
            }

            if ( '' === $name || '' === $code ) {
                continue;
            }
            if ( isset( $seen_codes[ $code ] ) ) {
                continue;
            }

            if ( '' === $locale ) {
                $locale_map = array(
                    'zh' => 'zh_CN',
                    'cn' => 'zh_CN',
                    'zh-tw' => 'zh_TW',
                    'zh_tw' => 'zh_TW',
                    'tw' => 'zh_TW',
                    'en' => 'en_US',
                    'jp' => 'ja_JP',
                    'ja' => 'ja_JP',
                    'ko' => 'ko_KR',
                    'fr' => 'fr_FR',
                    'de' => 'de_DE',
                    'es' => 'es_ES',
                    'ru' => 'ru_RU',
                );
                $locale = isset( $locale_map[ $code ] ) ? $locale_map[ $code ] : 'zh_CN';
            }

            $seen_codes[ $code ] = true;
            $normalized[] = array(
                'name'   => $name,
                'code'   => $code,
                'locale' => $locale,
                'icon'   => $icon,
            );
        }

        return $normalized;
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_default_lang' ) ) {
    /**
     * 获取多语言内容模式默认语言。
     *
     * @return string
     */
    function developer_starter_get_multilingual_default_lang() {
        $languages     = developer_starter_get_multilingual_languages();
        $default_lang  = sanitize_title( (string) developer_starter_get_option( 'multilingual_default_lang', 'zh' ) );
        if ( 'ja' === $default_lang ) {
            $default_lang = 'jp';
        }
        $language_codes = wp_list_pluck( $languages, 'code' );

        if ( in_array( $default_lang, $language_codes, true ) ) {
            return $default_lang;
        }

        return ! empty( $language_codes[0] ) ? (string) $language_codes[0] : 'zh';
    }
}

if ( ! function_exists( 'developer_starter_is_multilingual_content_mode' ) ) {
    /**
     * 当前是否启用多语言内容模式。
     *
     * @return bool
     */
    function developer_starter_is_multilingual_content_mode() {
        return 'multilingual_content' === developer_starter_get_frontend_language_switch_mode();
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_language_switcher_enabled' ) ) {
    /**
     * 当前前台是否启用语言切换按钮。
     *
     * @return bool
     */
    function developer_starter_get_frontend_language_switcher_enabled() {
        $mode = developer_starter_get_frontend_language_switch_mode();

        if ( 'translate_js' === $mode ) {
            return true;
        }

        if ( 'multilingual_content' === $mode ) {
            return count( developer_starter_get_multilingual_languages() ) > 1;
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_detect_lang_from_request' ) ) {
    /**
     * 直接从 URL 路径检测语言（不依赖模式设置）。
     *
     * @return string
     */
    function developer_starter_detect_lang_from_request() {
        $request_parts = developer_starter_get_request_path_parts();
        $path          = $request_parts['path'];
        $segments      = array_values( array_filter( explode( '/', (string) $path ), 'strlen' ) );
        $first         = isset( $segments[0] ) ? sanitize_title( (string) $segments[0] ) : '';

        if ( '' !== $first ) {
            foreach ( developer_starter_get_multilingual_languages() as $language ) {
                if ( $language['code'] === $first ) {
                    return $language['code'];
                }
            }
        }

        return developer_starter_get_multilingual_default_lang();
    }
}

if ( ! function_exists( 'developer_starter_get_request_path_parts' ) ) {
    /**
     * 获取当前请求路径和查询字符串。
     *
     * @return array{path: string, query: string}
     */
    function developer_starter_get_request_path_parts() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '';
        $path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
        $query       = (string) wp_parse_url( $request_uri, PHP_URL_QUERY );

        $path      = trim( $path, '/' );
        $home_path = function_exists( 'developer_starter_get_raw_home_path' )
            ? developer_starter_get_raw_home_path()
            : trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

        if ( '' !== $home_path ) {
            if ( $path === $home_path ) {
                $path = '';
            } elseif ( 0 === strpos( $path, $home_path . '/' ) ) {
                $path = substr( $path, strlen( $home_path ) + 1 );
            }
        }

        return array(
            'path'  => $path,
            'query' => $query,
        );
    }
}

if ( ! function_exists( 'developer_starter_is_sitemap_request_path' ) ) {
    /**
     * 判断给定路径是否为 WordPress Sitemap 请求。
     *
     * @param string $path 请求路径。
     * @return bool
     */
    function developer_starter_is_sitemap_request_path( $path ) {
        $path = trim( (string) $path, '/' );

        if ( '' === $path ) {
            return false;
        }

        if ( in_array( $path, array( 'wp-sitemap.xml', 'wp-sitemap.xsl', 'wp-sitemap-index.xsl' ), true ) ) {
            return true;
        }

        return (bool) preg_match( '/^wp-sitemap(?:-[a-z0-9._-]+)+\.(xml|xsl)$/i', $path );
    }
}

if ( ! function_exists( 'developer_starter_is_sitemap_request' ) ) {
    /**
     * 当前请求是否为 WordPress Sitemap 请求。
     *
     * @return bool
     */
    function developer_starter_is_sitemap_request() {
        $request_parts = developer_starter_get_request_path_parts();
        if ( developer_starter_is_sitemap_request_path( $request_parts['path'] ) ) {
            return true;
        }

        if ( function_exists( 'get_query_var' ) ) {
            if ( '' !== (string) get_query_var( 'sitemap' ) || '' !== (string) get_query_var( 'sitemap-stylesheet' ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_has_external_seo_plugin' ) ) {
    /**
     * 检测是否启用了主流 SEO 插件。
     *
     * @return bool
     */
    function developer_starter_has_external_seo_plugin() {
        return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );
    }
}

if ( ! function_exists( 'developer_starter_strip_multilingual_prefix' ) ) {
    /**
     * 去掉多语言路径前缀。
     *
     * @param string $path 路径。
     * @return string
     */
    function developer_starter_strip_multilingual_prefix( $path ) {
        $path           = trim( (string) $path, '/' );
        $language_codes = wp_list_pluck( developer_starter_get_multilingual_languages(), 'code' );

        if ( '' === $path || empty( $language_codes ) ) {
            return $path;
        }

        $pattern = '#^(' . implode( '|', array_map( 'preg_quote', $language_codes ) ) . ')(/|$)#';

        return trim( (string) preg_replace( $pattern, '', $path ), '/' );
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_lang_from_url' ) ) {
    /**
     * 从站内 URL 中识别显式语言前缀或 query var。
     *
     * @param string $url 站内 URL。
     * @return string
     */
    function developer_starter_get_multilingual_lang_from_url( $url ) {
        $url = trim( (string) $url );

        if ( '' === $url || ! developer_starter_is_site_internal_url( $url ) ) {
            return '';
        }

        $query = (string) wp_parse_url( $url, PHP_URL_QUERY );
        if ( '' !== $query ) {
            parse_str( $query, $query_args );
            if ( ! empty( $query_args['xb_lang'] ) ) {
                $query_lang = sanitize_title( (string) $query_args['xb_lang'] );
                foreach ( developer_starter_get_multilingual_languages() as $language ) {
                    if ( $language['code'] === $query_lang ) {
                        return $query_lang;
                    }
                }
            }
        }

        $relative  = wp_make_link_relative( $url );
        $path      = trim( (string) wp_parse_url( $relative, PHP_URL_PATH ), '/' );
        $home_path = function_exists( 'developer_starter_get_raw_home_path' )
            ? developer_starter_get_raw_home_path()
            : trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

        if ( '' !== $home_path ) {
            if ( $path === $home_path ) {
                $path = '';
            } elseif ( 0 === strpos( $path, $home_path . '/' ) ) {
                $path = substr( $path, strlen( $home_path ) + 1 );
            }
        }

        if ( '' === $path ) {
            return '';
        }

        $segments = explode( '/', $path );
        $first    = isset( $segments[0] ) ? sanitize_title( (string) $segments[0] ) : '';

        foreach ( developer_starter_get_multilingual_languages() as $language ) {
            if ( $language['code'] === $first ) {
                return $language['code'];
            }
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_get_current_frontend_lang' ) ) {
    /**
     * 获取当前前台语言简码。
     *
     * @return string
     */
    function developer_starter_get_current_frontend_lang() {
        if ( ! developer_starter_is_multilingual_content_mode() ) {
            return '';
        }

        $default_lang = developer_starter_get_multilingual_default_lang();
        $explicit_lang = developer_starter_get_explicit_frontend_lang_from_request();
        if ( '' !== $explicit_lang ) {
            return $explicit_lang;
        }

        $persisted_lang = developer_starter_get_persisted_frontend_lang();
        if ( '' !== $persisted_lang ) {
            return $persisted_lang;
        }

        return $default_lang;
    }
}

if ( ! function_exists( 'developer_starter_get_explicit_frontend_lang_from_request' ) ) {
    /**
     * 获取当前请求 URL 中显式声明的语言前缀。
     *
     * @return string
     */
    function developer_starter_get_explicit_frontend_lang_from_request() {
        $request_parts = developer_starter_get_request_path_parts();
        $path          = trim( (string) $request_parts['path'], '/' );

        if ( '' === $path ) {
            return '';
        }

        $segments = explode( '/', $path );
        $first    = isset( $segments[0] ) ? sanitize_title( (string) $segments[0] ) : '';

        foreach ( developer_starter_get_multilingual_languages() as $language ) {
            if ( $language['code'] === $first ) {
                return $language['code'];
            }
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_lang_cookie_name' ) ) {
    /**
     * 获取前台多语言持久化 Cookie 名称。
     *
     * @return string
     */
    function developer_starter_get_multilingual_lang_cookie_name() {
        return 'developer_starter_front_lang';
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_lang_cookie_version_name' ) ) {
    /**
     * 获取前台多语言持久化 Cookie 版本标记名称。
     *
     * @return string
     */
    function developer_starter_get_multilingual_lang_cookie_version_name() {
        return 'developer_starter_front_lang_ver';
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_lang_cookie_version' ) ) {
    /**
     * 获取当前多语言配置对应的 Cookie 版本值。
     *
     * 语言列表或默认语言变更后，该值会变化，用于让旧 Cookie 自动失效。
     *
     * @return string
     */
    function developer_starter_get_multilingual_lang_cookie_version() {
        $languages = developer_starter_get_multilingual_languages();
        $default   = developer_starter_get_multilingual_default_lang();
        $payload   = array(
            'default'   => $default,
            'languages' => array(),
        );

        foreach ( $languages as $language ) {
            if ( ! is_array( $language ) ) {
                continue;
            }

            $payload['languages'][] = array(
                'code'   => isset( $language['code'] ) ? sanitize_title( (string) $language['code'] ) : '',
                'locale' => isset( $language['locale'] ) ? (string) $language['locale'] : '',
            );
        }

        $encoded_payload = wp_json_encode( $payload );
        if ( ! is_string( $encoded_payload ) || '' === $encoded_payload ) {
            $encoded_payload = 'developer_starter_multilingual_v1';
        }

        return substr( md5( $encoded_payload ), 0, 12 );
    }
}

if ( ! function_exists( 'developer_starter_get_persisted_frontend_lang' ) ) {
    /**
     * 获取前台多语言持久化语言偏好。
     *
     * @return string
     */
    function developer_starter_get_persisted_frontend_lang() {
        $cookie_name         = developer_starter_get_multilingual_lang_cookie_name();
        $cookie_version_name = developer_starter_get_multilingual_lang_cookie_version_name();
        $expected_version    = developer_starter_get_multilingual_lang_cookie_version();
        $saved_version       = isset( $_COOKIE[ $cookie_version_name ] ) ? sanitize_text_field( wp_unslash( (string) $_COOKIE[ $cookie_version_name ] ) ) : '';
        $lang                = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_title( wp_unslash( (string) $_COOKIE[ $cookie_name ] ) ) : '';

        if ( '' === $lang || '' === $saved_version || $saved_version !== $expected_version ) {
            return '';
        }

        foreach ( developer_starter_get_multilingual_languages() as $language ) {
            if ( $language['code'] === $lang ) {
                return $lang;
            }
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_locale_by_lang' ) ) {
    /**
     * 根据前台语言简码获取 locale。
     *
     * @param string $lang 语言简码。
     * @return string
     */
    function developer_starter_get_frontend_locale_by_lang( $lang ) {
        $lang = sanitize_title( (string) $lang );

        foreach ( developer_starter_get_multilingual_languages() as $language ) {
            if ( $language['code'] === $lang ) {
                return (string) $language['locale'];
            }
        }

        $locale_map = array(
            'zh' => 'zh_CN',
            'cn' => 'zh_CN',
            'zh-tw' => 'zh_TW',
            'zh_tw' => 'zh_TW',
            'tw' => 'zh_TW',
            'en' => 'en_US',
            'jp' => 'ja_JP',
            'ja' => 'ja_JP',
            'ko' => 'ko_KR',
            'fr' => 'fr_FR',
            'de' => 'de_DE',
            'es' => 'es_ES',
            'ru' => 'ru_RU',
        );

        return isset( $locale_map[ $lang ] ) ? $locale_map[ $lang ] : 'zh_CN';
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_url' ) ) {
    /**
     * 将 URL 转换为指定语言的前台地址。
     *
     * @param string $lang 目标语言简码。
     * @param string $url  原始 URL，留空使用当前请求。
     * @return string
     */
    function developer_starter_get_multilingual_url( $lang, $url = '' ) {
        $lang         = sanitize_title( (string) $lang );
        $default_lang = developer_starter_get_multilingual_default_lang();
        $language_codes = wp_list_pluck( developer_starter_get_multilingual_languages(), 'code' );
        $fragment     = '';

        if ( ! in_array( $lang, $language_codes, true ) ) {
            return (string) $url;
        }

        if ( '' === $url ) {
            $request_parts = developer_starter_get_request_path_parts();
            $path          = developer_starter_strip_multilingual_prefix( $request_parts['path'] );
            $query         = $request_parts['query'];
        } else {
            $relative = wp_make_link_relative( $url );
            $path     = (string) wp_parse_url( $relative, PHP_URL_PATH );
            $query    = (string) wp_parse_url( $url, PHP_URL_QUERY );
            $fragment = (string) wp_parse_url( $url, PHP_URL_FRAGMENT );
            $path     = developer_starter_strip_multilingual_prefix( $path );
        }

        $prefixed_path = trim( (string) $path, '/' );
        if ( $lang !== $default_lang ) {
            $prefixed_path = '' === $prefixed_path ? $lang : $lang . '/' . $prefixed_path;
        }

        $target_url = function_exists( 'developer_starter_build_raw_home_url' )
            ? developer_starter_build_raw_home_url( '' === $prefixed_path ? '/' : user_trailingslashit( $prefixed_path ) )
            : home_url( '' === $prefixed_path ? '/' : user_trailingslashit( $prefixed_path ) );
        if ( '' !== $query ) {
            $target_url .= '?' . $query;
        }

        if ( '' !== $fragment ) {
            $target_url .= '#' . $fragment;
        }

        return $target_url;
    }
}

if ( ! function_exists( 'developer_starter_is_site_internal_url' ) ) {
    /**
     * 判断 URL 是否为当前站点内部地址。
     *
     * @param string $url 目标 URL。
     * @return bool
     */
    function developer_starter_is_site_internal_url( $url ) {
        $url = trim( (string) $url );

        if ( '' === $url ) {
            return false;
        }

        if (
            0 === strpos( $url, '#' )
            || 0 === strpos( $url, 'mailto:' )
            || 0 === strpos( $url, 'tel:' )
            || 0 === strpos( $url, 'javascript:' )
        ) {
            return false;
        }

        $parsed_url = wp_parse_url( $url );
        if ( false === $parsed_url ) {
            return false;
        }

        if ( empty( $parsed_url['host'] ) ) {
            return true;
        }

        $home_parts = wp_parse_url(
            function_exists( 'developer_starter_build_raw_home_url' )
                ? developer_starter_build_raw_home_url( '/' )
                : home_url( '/' )
        );
        if ( false === $home_parts || empty( $home_parts['host'] ) ) {
            return false;
        }

        $url_host  = strtolower( (string) $parsed_url['host'] );
        $home_host = strtolower( (string) $home_parts['host'] );

        if ( $url_host !== $home_host ) {
            return false;
        }

        $url_port  = isset( $parsed_url['port'] ) ? (string) $parsed_url['port'] : '';
        $home_port = isset( $home_parts['port'] ) ? (string) $home_parts['port'] : '';

        return $url_port === $home_port;
    }
}

if ( ! function_exists( 'developer_starter_is_document_relative_url' ) ) {
    /**
     * 判断 URL 是否为基于当前文档解析的相对链接。
     *
     * 例如：?tab=profile、./foo、../bar、orders、messages/list。
     * 这类链接应交给浏览器按当前页面路径解析，不能按站点首页 URL 去翻译。
     *
     * @param string $url 原始 URL。
     * @return bool
     */
    function developer_starter_is_document_relative_url( $url ) {
        $url = trim( (string) $url );

        if ( '' === $url ) {
            return false;
        }

        if ( preg_match( '#^[a-z][a-z0-9+\-.]*:#i', $url ) || 0 === strpos( $url, '//' ) ) {
            return false;
        }

        return '/' !== $url[0];
    }
}

if ( ! function_exists( 'developer_starter_append_query_string_to_url' ) ) {
    /**
     * 向 URL 追加查询字符串。
     *
     * @param string $url   基础 URL。
     * @param string $query 查询字符串。
     * @return string
     */
    function developer_starter_append_query_string_to_url( $url, $query = '' ) {
        $url   = (string) $url;
        $query = ltrim( (string) $query, '?&' );

        if ( '' === $url || '' === $query ) {
            return $url;
        }

        return $url . ( false !== strpos( $url, '?' ) ? '&' : '?' ) . $query;
    }
}

if ( ! function_exists( 'developer_starter_append_fragment_to_url' ) ) {
    /**
     * 向 URL 追加片段标识。
     *
     * @param string $url      基础 URL。
     * @param string $fragment 片段标识。
     * @return string
     */
    function developer_starter_append_fragment_to_url( $url, $fragment = '' ) {
        $url      = (string) $url;
        $fragment = ltrim( (string) $fragment, '#' );

        if ( '' === $url || '' === $fragment ) {
            return $url;
        }

        return $url . '#' . $fragment;
    }
}

if ( ! function_exists( 'developer_starter_register_frontend_route' ) ) {
    /**
     * 注册前台虚拟路由，供多语言内容模式统一识别与构建链接。
     *
     * @param string $route_key 路由键。
     * @param array  $args      路由配置。
     * @return bool
     */
    function developer_starter_register_frontend_route( $route_key, $args = array() ) {
        $route_key = sanitize_key( str_replace( array( ':', '/', '\\' ), '_', (string) $route_key ) );
        if ( '' === $route_key || ! is_array( $args ) ) {
            return false;
        }

        if ( ! isset( $GLOBALS['developer_starter_frontend_routes'] ) || ! is_array( $GLOBALS['developer_starter_frontend_routes'] ) ) {
            $GLOBALS['developer_starter_frontend_routes'] = array();
        }

        $defaults = array(
            'match'      => null,
            'url'        => null,
            'query_vars' => null,
        );

        $GLOBALS['developer_starter_frontend_routes'][ $route_key ] = wp_parse_args( $args, $defaults );

        return true;
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_routes' ) ) {
    /**
     * 获取已注册的前台虚拟路由。
     *
     * @return array<string, array>
     */
    function developer_starter_get_frontend_routes() {
        $routes = isset( $GLOBALS['developer_starter_frontend_routes'] ) && is_array( $GLOBALS['developer_starter_frontend_routes'] )
            ? $GLOBALS['developer_starter_frontend_routes']
            : array();

        $routes = apply_filters( 'developer_starter_frontend_routes', $routes );

        return is_array( $routes ) ? $routes : array();
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_route' ) ) {
    /**
     * 获取单个前台虚拟路由配置。
     *
     * @param string $route_key 路由键。
     * @return array|null
     */
    function developer_starter_get_frontend_route( $route_key ) {
        $route_key = sanitize_key( str_replace( array( ':', '/', '\\' ), '_', (string) $route_key ) );
        $routes    = developer_starter_get_frontend_routes();

        return isset( $routes[ $route_key ] ) && is_array( $routes[ $route_key ] )
            ? $routes[ $route_key ]
            : null;
    }
}

if ( ! function_exists( 'developer_starter_normalize_frontend_route_input' ) ) {
    /**
     * 规范前台虚拟路由匹配输入。
     *
     * @param string $path_or_url 路径或站内 URL。
     * @param string $query       可选查询字符串。
     * @return array{original:string,path:string,content_path:string,query:string}
     */
    function developer_starter_normalize_frontend_route_input( $path_or_url = '', $query = '' ) {
        $path_or_url = trim( (string) $path_or_url );
        $query       = ltrim( (string) $query, '?&' );

        if ( '' === $path_or_url ) {
            $request_parts = developer_starter_get_request_path_parts();

            return array(
                'original'     => '',
                'path'         => trim( (string) $request_parts['path'], '/' ),
                'content_path' => trim( developer_starter_strip_multilingual_prefix( (string) $request_parts['path'] ), '/' ),
                'query'        => (string) $request_parts['query'],
            );
        }

        if (
            preg_match( '#^[a-z][a-z0-9+\-.]*:#i', $path_or_url )
            && ! developer_starter_is_site_internal_url( $path_or_url )
        ) {
            return array(
                'original'     => $path_or_url,
                'path'         => '',
                'content_path' => '',
                'query'        => $query,
            );
        }

        $parts = wp_parse_url( $path_or_url );
        $path  = '';

        if ( false !== $parts ) {
            $path = isset( $parts['path'] ) ? (string) $parts['path'] : '';
            if ( '' === $query && isset( $parts['query'] ) ) {
                $query = (string) $parts['query'];
            }
        }

        if ( '' === $path ) {
            $path = $path_or_url;
        }

        $path = trim( (string) $path, '/' );

        $home_path = function_exists( 'developer_starter_get_raw_home_path' )
            ? developer_starter_get_raw_home_path()
            : trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
        if ( '' !== $home_path ) {
            if ( $path === $home_path ) {
                $path = '';
            } elseif ( 0 === strpos( $path, $home_path . '/' ) ) {
                $path = substr( $path, strlen( $home_path ) + 1 );
            }
        }

        return array(
            'original'     => $path_or_url,
            'path'         => trim( $path, '/' ),
            'content_path' => trim( developer_starter_strip_multilingual_prefix( $path ), '/' ),
            'query'        => $query,
        );
    }
}

if ( ! function_exists( 'developer_starter_match_frontend_route' ) ) {
    /**
     * 匹配已注册的前台虚拟路由。
     *
     * @param string $path_or_url 路径或站内 URL，留空使用当前请求。
     * @param string $query       可选查询字符串。
     * @return array{route:string,path:string,params:array,query_vars:array,query:string,content_path:string}
     */
    function developer_starter_match_frontend_route( $path_or_url = '', $query = '' ) {
        $input = developer_starter_normalize_frontend_route_input( $path_or_url, $query );

        $empty_context = array(
            'route'        => '',
            'path'         => $input['content_path'],
            'params'       => array(),
            'query_vars'   => array(),
            'query'        => $input['query'],
            'content_path' => $input['content_path'],
        );

        if ( '' === $input['content_path'] ) {
            return $empty_context;
        }

        foreach ( developer_starter_get_frontend_routes() as $route_key => $route ) {
            if ( empty( $route['match'] ) || ! is_callable( $route['match'] ) ) {
                continue;
            }

            $matched = call_user_func(
                $route['match'],
                $input['content_path'],
                $input,
                $route_key,
                $route
            );

            if ( ! is_array( $matched ) || empty( $matched ) ) {
                continue;
            }

            $context = array(
                'route'        => (string) $route_key,
                'path'         => isset( $matched['path'] ) ? trim( (string) $matched['path'], '/' ) : $input['content_path'],
                'params'       => isset( $matched['params'] ) && is_array( $matched['params'] ) ? $matched['params'] : array(),
                'query_vars'   => isset( $matched['query_vars'] ) && is_array( $matched['query_vars'] ) ? $matched['query_vars'] : array(),
                'query'        => $input['query'],
                'content_path' => $input['content_path'],
            );

            if ( empty( $context['query_vars'] ) && ! empty( $route['query_vars'] ) && is_callable( $route['query_vars'] ) ) {
                $query_vars = call_user_func( $route['query_vars'], $context, $route_key, $route );
                if ( is_array( $query_vars ) ) {
                    $context['query_vars'] = $query_vars;
                }
            }

            return $context;
        }

        return $empty_context;
    }
}

if ( ! function_exists( 'developer_starter_build_frontend_route_url' ) ) {
    /**
     * 根据匹配上下文构建前台虚拟路由 URL。
     *
     * @param array       $context 路由上下文。
     * @param string|null $lang    目标语言。
     * @return string
     */
    function developer_starter_build_frontend_route_url( $context, $lang = null ) {
        if ( ! is_array( $context ) ) {
            return '';
        }

        $route_key = isset( $context['route'] ) ? sanitize_key( (string) $context['route'] ) : '';
        if ( '' === $route_key ) {
            return '';
        }

        $route = developer_starter_get_frontend_route( $route_key );
        if ( ! is_array( $route ) ) {
            return '';
        }

        $url = '';
        if ( ! empty( $route['url'] ) && is_callable( $route['url'] ) ) {
            $url = (string) call_user_func( $route['url'], $context, $lang, $route_key, $route );
        } elseif ( ! empty( $context['path'] ) ) {
            $url = function_exists( 'developer_starter_build_raw_home_url' )
                ? developer_starter_build_raw_home_url( user_trailingslashit( (string) $context['path'] ) )
                : home_url( user_trailingslashit( (string) $context['path'] ) );
        }

        if ( '' === $url ) {
            return '';
        }

        if ( ! developer_starter_is_multilingual_content_mode() || ! developer_starter_is_site_internal_url( $url ) ) {
            return $url;
        }

        $lang = null === $lang ? developer_starter_get_current_frontend_lang() : sanitize_title( (string) $lang );
        if ( '' === $lang ) {
            $lang = developer_starter_get_multilingual_default_lang();
        }

        return developer_starter_get_multilingual_url( $lang, $url );
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_route_url' ) ) {
    /**
     * 通过命名路由构建前台 URL。
     *
     * @param string      $route_key 路由键。
     * @param array       $params    路由参数。
     * @param string|null $lang      目标语言。
     * @return string
     */
    function developer_starter_get_frontend_route_url( $route_key, $params = array(), $lang = null ) {
        $route_key = sanitize_key( str_replace( array( ':', '/', '\\' ), '_', (string) $route_key ) );
        if ( '' === $route_key ) {
            return '';
        }

        $context = array(
            'route'      => $route_key,
            'params'     => is_array( $params ) ? $params : array(),
            'path'       => '',
            'query_vars' => array(),
        );

        return developer_starter_build_frontend_route_url( $context, $lang );
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_account_page_id' ) ) {
    /**
     * 获取前台个人中心页面 ID。
     *
     * @return int
     */
    function developer_starter_get_frontend_account_page_id() {
        static $account_page_id = null;

        if ( null !== $account_page_id ) {
            return (int) $account_page_id;
        }

        $account_page_id = (int) get_option( 'developer_starter_account_page_id', 0 );
        if ( $account_page_id > 0 && get_post_status( $account_page_id ) === 'publish' ) {
            return (int) $account_page_id;
        }

        $account_page = get_pages(
            array(
                'meta_key'   => '_wp_page_template',
                'meta_value' => 'templates/template-account.php',
                'number'     => 1,
            )
        );

        if ( ! empty( $account_page ) ) {
            $account_page_id = (int) $account_page[0]->ID;
            update_option( 'developer_starter_account_page_id', $account_page_id );
            return (int) $account_page_id;
        }

        $account_page_id = 0;
        return 0;
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_account_url' ) ) {
    /**
     * 获取前台个人中心 URL，并按需附加 tab / query。
     *
     * @param string      $tab  可选 tab。
     * @param array       $args 额外 query 参数。
     * @param string|null $lang 目标语言。
     * @return string
     */
    function developer_starter_get_frontend_account_url( $tab = '', $args = array(), $lang = null ) {
        $account_page_id = developer_starter_get_frontend_account_page_id();
        $query_args      = is_array( $args ) ? $args : array();

        if ( $account_page_id > 0 ) {
            $url = developer_starter_get_post_url_for_frontend_lang( $account_page_id, $lang );
        } else {
            $url = function_exists( 'developer_starter_build_raw_home_url' )
                ? developer_starter_build_raw_home_url( '/user' )
                : home_url( '/user' );

            if ( developer_starter_is_multilingual_content_mode() ) {
                $lang = null === $lang ? developer_starter_get_current_frontend_lang() : sanitize_title( (string) $lang );
                if ( '' === $lang ) {
                    $lang = developer_starter_get_multilingual_default_lang();
                }
                $url = developer_starter_get_multilingual_url( $lang, $url );
            }
        }

        $tab = sanitize_key( (string) $tab );
        if ( '' !== $tab ) {
            $query_args['tab'] = $tab;
        }

        if ( ! empty( $query_args ) ) {
            $url = add_query_arg( $query_args, $url );
        }

        return $url;
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_account_tab_url' ) ) {
    /**
     * 获取前台个人中心指定 tab URL。
     *
     * @param string      $tab  目标 tab。
     * @param array       $args 额外 query 参数。
     * @param string|null $lang 目标语言。
     * @return string
     */
    function developer_starter_get_frontend_account_tab_url( $tab = '', $args = array(), $lang = null ) {
        return developer_starter_get_frontend_account_url( $tab, $args, $lang );
    }
}

if ( ! function_exists( 'developer_starter_translate_internal_url_for_frontend_lang' ) ) {
    /**
     * 将站内 URL 转换为目标前台语言地址。
     *
     * @param string      $url  站内地址。
     * @param string|null $lang 目标语言，留空使用当前前台语言。
     * @return string
     */
    function developer_starter_translate_internal_url_for_frontend_lang( $url, $lang = null ) {
        $url = trim( (string) $url );
        $fragment = (string) wp_parse_url( $url, PHP_URL_FRAGMENT );

        // 当前文档相对链接保持原样，避免被误翻译成首页或根路径链接。
        if ( '' === $url || developer_starter_is_document_relative_url( $url ) ) {
            return $url;
        }

        if ( ! developer_starter_is_multilingual_content_mode() || ! developer_starter_is_site_internal_url( $url ) ) {
            return $url;
        }

        $lang = null === $lang ? developer_starter_get_current_frontend_lang() : sanitize_title( (string) $lang );
        if ( '' === $lang ) {
            $lang = developer_starter_get_multilingual_default_lang();
        }

        $explicit_lang = developer_starter_get_multilingual_lang_from_url( $url );
        if ( '' !== $explicit_lang ) {
            return $url;
        }

        $relative = wp_make_link_relative( $url );
        $path     = developer_starter_strip_multilingual_prefix( (string) wp_parse_url( $relative, PHP_URL_PATH ) );
        $query    = (string) wp_parse_url( $url, PHP_URL_QUERY );

        if ( '' === trim( $path, '/' ) && '' === $query ) {
            return developer_starter_append_fragment_to_url( developer_starter_get_frontend_home_url( $lang ), $fragment );
        }

        $route_context = developer_starter_match_frontend_route( $url );
        if ( ! empty( $route_context['route'] ) ) {
            $translated_route_url = developer_starter_build_frontend_route_url( $route_context, $lang );
            if ( '' !== $translated_route_url ) {
                $translated_route_url = developer_starter_append_query_string_to_url( $translated_route_url, $query );
                return developer_starter_append_fragment_to_url( $translated_route_url, $fragment );
            }
        }

        if ( function_exists( 'url_to_postid' ) ) {
            $lookup_url = $url;
            if ( false === strpos( $lookup_url, '://' ) ) {
                $lookup_url = function_exists( 'developer_starter_build_raw_home_url' )
                    ? developer_starter_build_raw_home_url( '/' . ltrim( $lookup_url, '/' ) )
                    : home_url( '/' . ltrim( $lookup_url, '/' ) );
            }

            $post_id = url_to_postid( $lookup_url );
            if ( $post_id > 0 ) {
                $translated_url = developer_starter_get_post_url_for_frontend_lang( $post_id, $lang );
                if ( '' !== $translated_url ) {
                    $translated_url = developer_starter_append_query_string_to_url( $translated_url, $query );
                    return developer_starter_append_fragment_to_url( $translated_url, $fragment );
                }
            }
        }

        return developer_starter_get_multilingual_url( $lang, $url );
    }
}

if ( ! function_exists( 'developer_starter_get_frontend_home_url' ) ) {
    /**
     * 获取当前前台语言对应的首页地址。
     *
     * @param string|null $lang 目标语言，留空使用当前前台语言。
     * @return string
     */
    function developer_starter_get_frontend_home_url( $lang = null ) {
        $home_url = function_exists( 'developer_starter_build_raw_home_url' )
            ? developer_starter_build_raw_home_url( '/' )
            : home_url( '/' );

        if ( ! developer_starter_is_multilingual_content_mode() ) {
            return $home_url;
        }

        $lang = null === $lang ? developer_starter_get_current_frontend_lang() : sanitize_title( (string) $lang );
        if ( '' === $lang ) {
            $lang = developer_starter_get_multilingual_default_lang();
        }

        return developer_starter_get_multilingual_url( $lang, $home_url );
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_switch_target_url' ) ) {
    /**
     * 获取切换到目标语言后的前台地址。
     *
     * @param string $target_lang 目标语言简码。
     * @return string
     */
    function developer_starter_get_multilingual_switch_target_url( $target_lang ) {
        $target_lang = sanitize_title( (string) $target_lang );
        if ( '' === $target_lang ) {
            return developer_starter_get_frontend_home_url();
        }

        if ( is_singular() ) {
            $post_id = get_queried_object_id();
            if ( $post_id > 0 ) {
                $post_url = developer_starter_get_post_url_for_frontend_lang( $post_id, $target_lang );
                if ( '' !== $post_url ) {
                    return $post_url;
                }
            }
        }

        return developer_starter_get_multilingual_url( $target_lang );
    }
}

if ( ! function_exists( 'developer_starter_get_post_url_for_frontend_lang' ) ) {
    /**
     * 获取指定文章在目标前台语言下的 URL。
     *
     * @param int         $post_id 文章 ID。
     * @param string|null $lang    目标语言，留空使用当前前台语言。
     * @return string
     */
    function developer_starter_get_post_url_for_frontend_lang( $post_id, $lang = null ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return '';
        }

        $lang = null === $lang ? developer_starter_get_current_frontend_lang() : sanitize_title( (string) $lang );
        if ( '' === $lang ) {
            return get_permalink( $post_id );
        }

        $post_lang = function_exists( 'xb_aifanyi_get_post_language' )
            ? sanitize_title( (string) xb_aifanyi_get_post_language( $post_id ) )
            : sanitize_title( (string) get_post_meta( $post_id, '_xb_aifanyi_lang', true ) );
        if ( '' === $post_lang ) {
            $post_lang = developer_starter_get_multilingual_default_lang();
        }

        if ( $post_lang === $lang ) {
            return developer_starter_get_multilingual_url( $lang, get_permalink( $post_id ) );
        }

        if ( function_exists( 'xb_aifanyi_get_translation_url' ) ) {
            $translation_url = xb_aifanyi_get_translation_url( $post_id, $lang );
            if ( is_string( $translation_url ) && '' !== $translation_url ) {
                return developer_starter_get_multilingual_url( $lang, $translation_url );
            }
        }

        global $xb_aifanyi_translator;
        if ( isset( $xb_aifanyi_translator ) && is_object( $xb_aifanyi_translator ) && method_exists( $xb_aifanyi_translator, 'xb_aifanyi_get_translation_url' ) ) {
            $translation_url = $xb_aifanyi_translator->xb_aifanyi_get_translation_url( $post_id, $lang );
            if ( is_string( $translation_url ) && '' !== $translation_url ) {
                return developer_starter_get_multilingual_url( $lang, $translation_url );
            }
        }

        return developer_starter_get_multilingual_url( $lang, get_permalink( $post_id ) );
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_switcher_items' ) ) {
    /**
     * 获取前台多语言切换器项目。
     *
     * @return array<int, array{name: string, code: string, locale: string, icon: string, url: string, active: bool}>
     */
    function developer_starter_get_multilingual_switcher_items() {
        $items        = array();
        $current_lang = developer_starter_get_current_frontend_lang();

        foreach ( developer_starter_get_multilingual_languages() as $language ) {
            $language['url']    = developer_starter_get_multilingual_switch_target_url( $language['code'] );
            $language['active'] = ( $language['code'] === $current_lang );
            $items[]            = $language;
        }

        return $items;
    }
}

if ( ! function_exists( 'developer_starter_get_multilingual_toggle_url' ) ) {
    /**
     * 获取多语言模式下的主切换目标 URL（用于一键切换）。
     *
     * @return string
     */
    function developer_starter_get_multilingual_toggle_url() {
        $items = developer_starter_get_multilingual_switcher_items();
        if ( empty( $items ) ) {
            return developer_starter_get_frontend_home_url();
        }

        foreach ( $items as $item ) {
            if ( empty( $item['active'] ) && ! empty( $item['url'] ) ) {
                return (string) $item['url'];
            }
        }

        return developer_starter_get_frontend_home_url();
    }
}

if ( ! function_exists( 'developer_starter_get_force_lang_toggle_url' ) ) {
    /**
     * 不依赖模式设置的兜底切换 URL。
     *
     * @return string
     */
    function developer_starter_get_force_lang_toggle_url() {
        $languages = developer_starter_get_multilingual_languages();
        if ( empty( $languages ) ) {
            return home_url( '/' );
        }

        $current_lang = developer_starter_detect_lang_from_request();
        $target_lang  = '';

        foreach ( $languages as $language ) {
            if ( $language['code'] !== $current_lang ) {
                $target_lang = $language['code'];
                break;
            }
        }

        if ( '' === $target_lang ) {
            $target_lang = developer_starter_get_multilingual_default_lang();
        }

        return developer_starter_get_multilingual_url( $target_lang );
    }
}
