<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_ip_location_http_user_agent' ) ) {
    function developer_starter_ip_location_http_user_agent() {
        $version = defined( 'DEVELOPER_STARTER_VERSION' ) ? DEVELOPER_STARTER_VERSION : '1.0.0';
        $site_url = function_exists( 'home_url' ) ? home_url( '/' ) : '';

        return (string) apply_filters(
            'developer_starter_ip_location_http_user_agent',
            sprintf( 'QiLing/%s; %s', $version, $site_url )
        );
    }
}

if ( ! function_exists( 'developer_starter_ip_location_http_args' ) ) {
    function developer_starter_ip_location_http_args( $args = array() ) {
        $timeout = max( 1, min( 15, (int) apply_filters( 'developer_starter_ip_location_http_timeout', 8 ) ) );
        $redirection = max( 0, min( 2, (int) apply_filters( 'developer_starter_ip_location_http_redirection', 1 ) ) );

        $defaults = array(
            'timeout'              => $timeout,
            'redirection'          => $redirection,
            'user-agent'           => developer_starter_ip_location_http_user_agent(),
            'reject_unsafe_urls'   => true,
            'sslverify'            => true,
            'limit_response_size'  => 1024 * 1024,
            'headers'              => array(
                'Accept' => 'application/json, text/plain, */*',
            ),
        );

        $args = wp_parse_args( $args, $defaults );
        $args['headers'] = wp_parse_args(
            isset( $args['headers'] ) && is_array( $args['headers'] ) ? $args['headers'] : array(),
            $defaults['headers']
        );

        return $args;
    }
}

if ( ! function_exists( 'developer_starter_http_get_json_response' ) ) {
    function developer_starter_http_get_json_response( $url, $args = array() ) {
        $url = esc_url_raw( (string) $url, array( 'https' ) );
        if ( '' === $url ) {
            return null;
        }

        $response = wp_remote_get( $url, developer_starter_ip_location_http_args( $args ) );
        if ( is_wp_error( $response ) ) {
            return null;
        }

        $status_code = (int) wp_remote_retrieve_response_code( $response );
        if ( $status_code < 200 || $status_code >= 300 ) {
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        if ( ! is_string( $body ) || '' === trim( $body ) ) {
            return null;
        }

        $data = json_decode( $body, true );
        if ( ! is_array( $data ) ) {
            return null;
        }

        return array(
            'data'        => $data,
            'body'        => $body,
            'status_code' => $status_code,
        );
    }
}

if ( ! function_exists( 'developer_starter_http_get_json' ) ) {
    function developer_starter_http_get_json( $url, $args = array() ) {
        $result = developer_starter_http_get_json_response( $url, $args );
        return is_array( $result ) && isset( $result['data'] ) ? $result['data'] : null;
    }
}

if ( ! function_exists( 'developer_starter_ip_location_get_cached_json' ) ) {
    function developer_starter_ip_location_get_cached_json( $provider, $ip, $url, $args = array() ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return null;
        }

        $cache_file = developer_starter_ip_cache_file( $provider, $ip );
        $cached = developer_starter_ip_cache_read( $cache_file, developer_starter_ip_cache_ttl() );
        if ( $cached ) {
            return $cached;
        }

        $result = developer_starter_http_get_json_response( $url, $args );
        if ( ! is_array( $result ) || ! isset( $result['data'], $result['body'] ) ) {
            return null;
        }

        developer_starter_ip_cache_write( $cache_file, (string) $result['body'] );
        return $result['data'];
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_dir' ) ) {
    function developer_starter_ip_cache_dir() {
        $uploads = wp_upload_dir( null, false );
        if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
            return '';
        }

        $uploads_dir = (string) $uploads['basedir'];
        if ( ! is_dir( $uploads_dir ) && ! wp_mkdir_p( $uploads_dir ) ) {
            return '';
        }

        $uploads_real = realpath( $uploads_dir );
        if ( false === $uploads_real ) {
            return '';
        }

        $cache_root = trailingslashit( $uploads_dir ) . 'cache';
        $theme_cache_dir = trailingslashit( $cache_root ) . 'developer-starter';
        $cache_dir = trailingslashit( $theme_cache_dir ) . 'ip-location';

        foreach ( array( $cache_root, $theme_cache_dir, $cache_dir ) as $dir ) {
            if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
                return '';
            }
        }

        $cache_real = realpath( $cache_dir );
        if ( false === $cache_real ) {
            return '';
        }

        $uploads_real = rtrim( wp_normalize_path( $uploads_real ), '/' );
        $cache_real_normalized = wp_normalize_path( $cache_real );
        if ( $cache_real_normalized !== $uploads_real && 0 !== strpos( $cache_real_normalized, $uploads_real . '/' ) ) {
            return '';
        }

        developer_starter_ip_cache_ensure_index_files( array( $cache_root, $theme_cache_dir, $cache_dir ) );

        return $cache_real;
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_ensure_index_files' ) ) {
    function developer_starter_ip_cache_ensure_index_files( $dirs ) {
        foreach ( (array) $dirs as $dir ) {
            if ( ! is_dir( $dir ) ) {
                continue;
            }

            $index_file = trailingslashit( $dir ) . 'index.html';
            if ( ! file_exists( $index_file ) ) {
                developer_starter_filesystem_write_file(
                    $index_file,
                    "Silence is golden.\n",
                    array(
                        'operation' => 'write_ip_cache_index',
                        'context'   => array( 'component' => 'ip_location_cache' ),
                    )
                );
            }
        }
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_ttl' ) ) {
    function developer_starter_ip_cache_ttl() {
        return max( MINUTE_IN_SECONDS, (int) apply_filters( 'developer_starter_ip_cache_ttl', HOUR_IN_SECONDS ) );
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_file' ) ) {
    function developer_starter_ip_cache_file( $provider, $ip ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return '';
        }

        $provider = sanitize_key( (string) $provider );
        if ( ! in_array( $provider, array( 'jingxialai', 'ipinfo' ), true ) ) {
            return '';
        }

        $cache_dir = developer_starter_ip_cache_dir();
        if ( '' === $cache_dir ) {
            return '';
        }

        $digest = hash_hmac( 'sha256', $provider . '|' . $ip, wp_salt( 'auth' ) );
        return trailingslashit( $cache_dir ) . $provider . '_' . $digest . '.json';
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_path_allowed' ) ) {
    function developer_starter_ip_cache_path_allowed( $cache_file ) {
        $cache_file = (string) $cache_file;
        if ( '' === $cache_file || ! preg_match( '/^[a-z0-9_-]+_[a-f0-9]{64}\.json$/', basename( $cache_file ) ) ) {
            return false;
        }

        $cache_dir = developer_starter_ip_cache_dir();
        if ( '' === $cache_dir ) {
            return false;
        }

        $cache_dir_real = realpath( $cache_dir );
        $target_dir_real = realpath( dirname( $cache_file ) );
        if ( false === $cache_dir_real || false === $target_dir_real ) {
            return false;
        }

        return wp_normalize_path( $cache_dir_real ) === wp_normalize_path( $target_dir_real );
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_read' ) ) {
    function developer_starter_ip_cache_read( $cache_file, $ttl ) {
        if ( ! developer_starter_ip_cache_path_allowed( $cache_file ) || ! is_file( $cache_file ) ) {
            return null;
        }
        if ( time() - filemtime( $cache_file ) >= $ttl ) {
            developer_starter_filesystem_delete_file(
                $cache_file,
                array(
                    'operation' => 'delete_expired_ip_cache',
                    'context'   => array( 'component' => 'ip_location_cache' ),
                )
            );
            return null;
        }
        $raw = @file_get_contents( $cache_file );
        if ( false === $raw || '' === $raw ) {
            return null;
        }
        $data = json_decode( $raw, true );
        if ( is_array( $data ) ) {
            return $data;
        }
        return null;
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_write' ) ) {
    function developer_starter_ip_cache_write( $cache_file, $raw ) {
        if ( ! developer_starter_ip_cache_path_allowed( $cache_file ) || $raw === '' ) {
            return;
        }
        developer_starter_filesystem_write_file(
            $cache_file,
            $raw,
            array(
                'operation' => 'write_ip_cache',
                'context'   => array( 'component' => 'ip_location_cache' ),
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_delete_files' ) ) {
    function developer_starter_ip_cache_delete_files( $dir, $patterns, $max_age = 0, $require_allowed_path = false ) {
        if ( ! is_dir( $dir ) ) {
            return 0;
        }

        $deleted = 0;
        $now = time();
        foreach ( (array) $patterns as $pattern ) {
            $files = glob( trailingslashit( $dir ) . $pattern );
            if ( ! is_array( $files ) ) {
                continue;
            }

            foreach ( $files as $file ) {
                if ( ! is_file( $file ) ) {
                    continue;
                }

                if ( $require_allowed_path && ! developer_starter_ip_cache_path_allowed( $file ) ) {
                    continue;
                }

                if ( $max_age > 0 && $now - filemtime( $file ) < $max_age ) {
                    continue;
                }

                if ( developer_starter_filesystem_delete_file(
                    $file,
                    array(
                        'operation'     => 'delete_ip_cache',
                        'allowed_roots' => array( $dir ),
                        'context'       => array( 'component' => 'ip_location_cache' ),
                    )
                ) ) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}

if ( ! function_exists( 'developer_starter_ip_cache_clear' ) ) {
    function developer_starter_ip_cache_clear( $max_age = 0 ) {
        $deleted = 0;
        $cache_dir = developer_starter_ip_cache_dir();
        if ( '' !== $cache_dir && is_dir( $cache_dir ) ) {
            $deleted += developer_starter_ip_cache_delete_files( $cache_dir, array( '*.json' ), $max_age, true );
        }

        $legacy_dir = defined( 'WP_CONTENT_DIR' ) ? trailingslashit( WP_CONTENT_DIR ) . 'ip_cache' : '';
        if ( '' !== $legacy_dir && is_dir( $legacy_dir ) ) {
            $deleted += developer_starter_ip_cache_delete_files( $legacy_dir, array( 'jingxialai_*.json', 'meituan_*.json', 'ipinfo_*.json' ), $max_age, false );
        }

        return $deleted;
    }
}

if ( ! function_exists( 'developer_starter_cleanup_ip_cache' ) ) {
    function developer_starter_cleanup_ip_cache() {
        developer_starter_ip_cache_clear( developer_starter_ip_cache_ttl() );
    }
}

if ( ! function_exists( 'developer_starter_schedule_ip_cache_cleanup' ) ) {
    function developer_starter_schedule_ip_cache_cleanup() {
        if ( ! wp_next_scheduled( 'developer_starter_clean_ip_location_cache' ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'developer_starter_clean_ip_location_cache' );
        }
    }
}
add_action( 'init', 'developer_starter_schedule_ip_cache_cleanup', 5 );
add_action( 'developer_starter_clean_ip_location_cache', 'developer_starter_cleanup_ip_cache' );

if ( ! function_exists( 'developer_starter_unschedule_ip_cache_cleanup' ) ) {
    function developer_starter_unschedule_ip_cache_cleanup() {
        wp_clear_scheduled_hook( 'developer_starter_clean_ip_location_cache' );
    }
}
add_action( 'switch_theme', 'developer_starter_unschedule_ip_cache_cleanup' );

if ( ! function_exists( 'developer_starter_jingxialai_proxy_lookup' ) ) {
    function developer_starter_jingxialai_proxy_lookup( $ip ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return null;
        }

        $api_key = developer_starter_get_option( 'jingxialai_ip_api_key' );
        if ( empty( $api_key ) ) {
            return null;
        }

        $api_url = 'https://api.jingxialai.com/api/ip?ip=' . rawurlencode( $ip );
        return developer_starter_ip_location_get_cached_json(
            'jingxialai',
            $ip,
            $api_url,
            array(
                'headers' => array(
                    'X-API-Key'    => $api_key,
                    'Content-Type' => 'application/json',
                ),
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_ipinfo_proxy_lookup' ) ) {
    function developer_starter_ipinfo_proxy_lookup( $ip ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return null;
        }

        $url = 'https://ipinfo.io/' . rawurlencode( $ip ) . '/json';
        return developer_starter_ip_location_get_cached_json( 'ipinfo', $ip, $url );
    }
}

if ( ! function_exists( 'xb_aiip_query_jingxialai' ) ) {
    function xb_aiip_query_jingxialai( $ip ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return null;
        }
        $proxy_result = developer_starter_jingxialai_proxy_lookup( $ip );
        if ( $proxy_result ) {
            return $proxy_result;
        }
        return null;
    }
}

if ( ! function_exists( 'xb_aiip_query_ipinfo' ) ) {
    function xb_aiip_query_ipinfo( $ip ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return null;
        }
        $proxy_result = developer_starter_ipinfo_proxy_lookup( $ip );
        if ( $proxy_result ) {
            return $proxy_result;
        }
        return null;
    }
}

if ( ! function_exists( 'developer_starter_ip_proxy_rate_limited' ) ) {
    function developer_starter_ip_proxy_rate_limited( $action, $limit = 30, $window = 60 ) {
        $ip = developer_starter_get_client_ip();
        $key = 'ds_ip_proxy_rl_' . md5( $action . '|' . $ip );
        $count = (int) get_transient( $key );
        if ( $count >= $limit ) {
            return true;
        }
        set_transient( $key, $count + 1, $window );
        return false;
    }
}

if ( ! function_exists( 'developer_starter_validate_ip_proxy_request' ) ) {
    function developer_starter_validate_ip_proxy_request( $action ) {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'login_required' ), 401 );
        }

        $nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
        if ( $nonce === '' || ! wp_verify_nonce( $nonce, 'developer_starter_ip_lookup' ) ) {
            wp_send_json_error( array( 'message' => 'invalid_nonce' ), 403 );
        }

        if ( developer_starter_ip_proxy_rate_limited( $action ) ) {
            wp_send_json_error( array( 'message' => 'rate_limited' ), 429 );
        }
    }
}

if ( ! function_exists( 'developer_starter_ip_proxy_jingxialai' ) ) {
    function developer_starter_ip_proxy_jingxialai() {
        developer_starter_validate_ip_proxy_request( 'jingxialai' );

        $ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
        if ( $ip === '' || in_array( strtolower( $ip ), array( 'me', 'self', 'current', 'localhost' ), true ) ) {
            $ip = developer_starter_get_client_ip();
        }
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            wp_send_json_error( array( 'message' => 'invalid_ip', 'ip' => $ip ) );
        }
        $data = developer_starter_jingxialai_proxy_lookup( $ip );
        if ( ! $data ) {
            wp_send_json_error( array( 'message' => 'lookup_failed', 'ip' => $ip ) );
        }
        wp_send_json_success( $data );
    }
}
add_action( 'wp_ajax_developer_starter_ip_proxy_jingxialai', 'developer_starter_ip_proxy_jingxialai' );

if ( ! function_exists( 'developer_starter_ip_proxy_ipinfo' ) ) {
    function developer_starter_ip_proxy_ipinfo() {
        developer_starter_validate_ip_proxy_request( 'ipinfo' );

        $ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
        if ( $ip === '' || in_array( strtolower( $ip ), array( 'me', 'self', 'current', 'localhost' ), true ) ) {
            $ip = developer_starter_get_client_ip();
        }
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            wp_send_json_error( array( 'message' => 'invalid_ip', 'ip' => $ip ) );
        }
        $data = developer_starter_ipinfo_proxy_lookup( $ip );
        if ( ! $data ) {
            wp_send_json_error( array( 'message' => 'lookup_failed', 'ip' => $ip ) );
        }
        wp_send_json_success( $data );
    }
}
add_action( 'wp_ajax_developer_starter_ip_proxy_ipinfo', 'developer_starter_ip_proxy_ipinfo' );

if ( ! function_exists( 'developer_starter_format_ip_location_parts' ) ) {
    function developer_starter_format_ip_location_parts( $parts ) {
        $filtered = array();
        foreach ( $parts as $part ) {
            $part = trim( (string) $part );
            if ( $part === '' || $part === '0' || $part === '未知' ) {
                continue;
            }
            if ( ! in_array( $part, $filtered, true ) ) {
                $filtered[] = $part;
            }
        }
        return implode( ' · ', $filtered );
    }
}

if ( ! function_exists( 'developer_starter_extract_jingxialai_location' ) ) {
    function developer_starter_extract_jingxialai_location( $data ) {
        if ( ! is_array( $data ) ) {
            return '';
        }

        $parts = array();
        // 灵简IP返回字段：country, province, city
        if ( ! empty( $data['country'] ) ) {
            $parts[] = $data['country'];
        }
        if ( ! empty( $data['province'] ) ) {
            $parts[] = $data['province'];
        }
        if ( ! empty( $data['city'] ) ) {
            $parts[] = $data['city'];
        }

        return developer_starter_format_ip_location_parts( $parts );
    }
}

if ( ! function_exists( 'developer_starter_jingxialai_country' ) ) {
    function developer_starter_jingxialai_country( $data ) {
        if ( ! is_array( $data ) ) {
            return '';
        }
        if ( isset( $data['country'] ) ) {
            return (string) $data['country'];
        }
        return '';
    }
}

if ( ! function_exists( 'developer_starter_is_domestic_location' ) ) {
    function developer_starter_is_domestic_location( $country ) {
        $country = trim( (string) $country );
        if ( $country === '' ) {
            return false;
        }
        // mbstring 在部分主机环境可能未启用，缺失时回退到 strtolower，避免前台致命错误。
        if ( function_exists( 'mb_strtolower' ) ) {
            $country = mb_strtolower( $country, 'UTF-8' );
        } else {
            $country = strtolower( $country );
        }
        return ( $country === '中国' || $country === 'china' || $country === 'cn' || $country === '中华人民共和国' );
    }
}

if ( ! function_exists( 'developer_starter_extract_ipinfo_location' ) ) {
    function developer_starter_extract_ipinfo_location( $data ) {
        if ( ! is_array( $data ) ) {
            return '';
        }

        $parts = array();
        if ( ! empty( $data['country'] ) ) {
            $parts[] = $data['country'];
        }
        if ( ! empty( $data['region'] ) ) {
            $parts[] = $data['region'];
        }
        if ( ! empty( $data['city'] ) ) {
            $parts[] = $data['city'];
        }

        return developer_starter_format_ip_location_parts( $parts );
    }
}

if ( ! function_exists( 'developer_starter_lookup_location_by_ip' ) ) {
    function developer_starter_lookup_location_by_ip( $ip ) {
        $ip = trim( (string) $ip );
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return array(
                'location' => '',
                'source'   => '',
            );
        }

        $location = '';
        $source = '';

        $jingxialai = xb_aiip_query_jingxialai( $ip );
        $jingxialai_country = developer_starter_jingxialai_country( $jingxialai );
        // 如果成功获取且判断为国内IP，则优先使用灵简IP结果
        if ( $jingxialai && developer_starter_is_domestic_location( $jingxialai_country ) ) {
            $location = developer_starter_extract_jingxialai_location( $jingxialai );
            if ( $location ) {
                $source = 'jingxialai';
            }
        }

        if ( ! $location ) {
            $ipinfo = xb_aiip_query_ipinfo( $ip );
            $location = developer_starter_extract_ipinfo_location( $ipinfo );
            if ( $location ) {
                $source = 'ipinfo';
            }
        }

        return array(
            'location' => $location ? sanitize_text_field( $location ) : '',
            'source'   => $source ? sanitize_text_field( $source ) : '',
        );
    }
}

if ( ! function_exists( 'developer_starter_is_country_part' ) ) {
    function developer_starter_is_country_part( $part, $source ) {
        $part = trim( (string) $part );
        if ( $part === '' ) {
            return false;
        }
        if ( function_exists( 'developer_starter_is_domestic_location' ) && developer_starter_is_domestic_location( $part ) ) {
            return true;
        }
        if ( $source === 'jingxialai' ) {
            return ( $part === '中国' || $part === '中华人民共和国' );
        }
        if ( $source === 'ipinfo' ) {
            if ( strlen( $part ) === 2 && preg_match( '/^[A-Za-z]{2}$/', $part ) ) {
                return true;
            }
        }
        return false;
    }
}

if ( ! function_exists( 'developer_starter_trim_comment_location' ) ) {
    function developer_starter_trim_comment_location( $location, $source ) {
        $location = trim( (string) $location );
        if ( $location === '' ) {
            return '';
        }
        $parts = array_map( 'trim', explode( '·', $location ) );
        $parts = array_values( array_filter( $parts, function( $part ) {
            return $part !== '';
        } ) );
        if ( empty( $parts ) ) {
            return '';
        }
        $original_parts = $parts;
        if ( developer_starter_is_country_part( $parts[0], $source ) ) {
            array_shift( $parts );
        }
        if ( empty( $parts ) ) {
            $parts = $original_parts;
        }
        $parts = array_slice( $parts, 0, 2 );
        return developer_starter_format_ip_location_parts( $parts );
    }
}

if ( ! function_exists( 'developer_starter_get_comment_ip_location' ) ) {
    function developer_starter_get_comment_ip_location( $user_id, $comment = null ) {
        $location = get_user_meta( $user_id, 'ds_ip_location', true );
        $source = get_user_meta( $user_id, 'ds_ip_location_source', true );

        // 用户元信息缺失时，回退到评论 IP 归属地（含缓存）
        if ( ! $location && $comment instanceof WP_Comment ) {
            $author_ip = trim( (string) $comment->comment_author_IP );
            if ( filter_var( $author_ip, FILTER_VALIDATE_IP ) ) {
                $cache_key = 'ds_comment_ip_loc_' . md5( $author_ip );
                $cached = get_transient( $cache_key );
                if ( is_array( $cached ) ) {
                    $location = isset( $cached['location'] ) ? (string) $cached['location'] : '';
                    $source = isset( $cached['source'] ) ? (string) $cached['source'] : '';
                } else {
                    $lookup = developer_starter_lookup_location_by_ip( $author_ip );
                    $location = $lookup['location'];
                    $source = $lookup['source'];
                    if ( $location ) {
                        set_transient( $cache_key, $lookup, DAY_IN_SECONDS );
                    }
                }
            }
        }

        if ( ! $location ) {
            return '';
        }

        return developer_starter_trim_comment_location( $location, $source );
    }
}

if ( ! function_exists( 'developer_starter_get_user_ip_location' ) ) {
    function developer_starter_get_user_ip_location( $user_id ) {
        $location = get_user_meta( $user_id, 'ds_ip_location', true );
        if ( $location ) {
            return $location;
        }
        return '';
    }
}

if ( ! function_exists( 'developer_starter_get_community_ip_location' ) ) {
    function developer_starter_get_community_ip_location( $user_id ) {
        $location = get_user_meta( $user_id, 'ds_ip_location', true );
        if ( ! $location ) {
            return '';
        }
        $source = get_user_meta( $user_id, 'ds_ip_location_source', true );
        
        $location = trim( (string) $location );
        if ( $location === '' ) {
            return '';
        }
        $parts = array_map( 'trim', explode( '·', $location ) );
        $parts = array_values( array_filter( $parts, function( $part ) {
            return $part !== '';
        } ) );
        if ( empty( $parts ) ) {
            return '';
        }
        $original_parts = $parts;
        if ( function_exists( 'developer_starter_is_country_part' ) && developer_starter_is_country_part( $parts[0], $source ) ) {
            array_shift( $parts );
        }
        if ( empty( $parts ) ) {
            $parts = $original_parts;
        }
        // 仅保留第一级（省份）
        $parts = array_slice( $parts, 0, 1 );
        return developer_starter_format_ip_location_parts( $parts );
    }
}

if ( ! function_exists( 'developer_starter_update_user_ip_location_on_login' ) ) {
    function developer_starter_update_user_ip_location_on_login( $user_login, $user ) {
        if ( ! ( $user instanceof WP_User ) || empty( $user->ID ) ) {
            return;
        }

        $show_user_ip = developer_starter_get_option( 'show_user_ip_location', '' );
        $show_comment_ip = developer_starter_get_option( 'comment_ip_location_enable', '' );
        $show_community_ip = developer_starter_get_option( 'community_ip_location_enable', '' );
        if ( ! $show_user_ip && ! $show_comment_ip && ! $show_community_ip ) {
            return;
        }

        $ip = developer_starter_get_client_ip();
        if ( ! $ip || $ip === '0.0.0.0' ) {
            return;
        }

        $cache_ip = get_user_meta( $user->ID, 'ds_ip_location_ip', true );
        $cache_time = (int) get_user_meta( $user->ID, 'ds_ip_location_updated', true );
        $cache_location = get_user_meta( $user->ID, 'ds_ip_location', true );
        if ( $cache_location && $cache_time && ( time() - $cache_time < 7 * DAY_IN_SECONDS ) && $cache_ip === $ip ) {
            return;
        }

        try {
            $lookup = developer_starter_lookup_location_by_ip( $ip );
        } catch ( \Throwable $e ) {
            // 归属地查询失败不应中断登录用户的正常访问流程。
            return;
        }
        $location = isset( $lookup['location'] ) ? $lookup['location'] : '';
        $source = isset( $lookup['source'] ) ? $lookup['source'] : '';

        if ( ! $location ) {
            return;
        }

        update_user_meta( $user->ID, 'ds_ip_location', sanitize_text_field( $location ) );
        update_user_meta( $user->ID, 'ds_ip_location_source', sanitize_text_field( $source ) );
        update_user_meta( $user->ID, 'ds_ip_location_updated', time() );
        update_user_meta( $user->ID, 'ds_ip_location_ip', sanitize_text_field( $ip ) );
    }
}
add_action( 'wp_login', 'developer_starter_update_user_ip_location_on_login', 20, 2 );

if ( ! function_exists( 'developer_starter_maybe_refresh_current_user_ip_location' ) ) {
    function developer_starter_maybe_refresh_current_user_ip_location() {
        if ( is_admin() ) {
            return;
        }
        if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
            return;
        }
        if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
            return;
        }
        if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
            return;
        }
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return;
        }
        $method = isset( $_SERVER['REQUEST_METHOD'] )
            ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) )
            : 'GET';
        if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            return;
        }

        $show_user_ip = developer_starter_get_option( 'show_user_ip_location', '' );
        $show_comment_ip = developer_starter_get_option( 'comment_ip_location_enable', '' );
        $show_community_ip = developer_starter_get_option( 'community_ip_location_enable', '' );
        if ( ! $show_user_ip && ! $show_comment_ip && ! $show_community_ip ) {
            return;
        }

        $user = wp_get_current_user();
        if ( ! $user || empty( $user->ID ) ) {
            return;
        }

        $cache_time = (int) get_user_meta( $user->ID, 'ds_ip_location_updated', true );
        $cache_location = get_user_meta( $user->ID, 'ds_ip_location', true );
        $cache_ip = get_user_meta( $user->ID, 'ds_ip_location_ip', true );
        $current_ip = developer_starter_get_client_ip();

        // 有缓存且 24 小时内不重复刷新；但当当前真实 IP 与缓存 IP 不一致时，立即刷新。
        if ( $cache_location && $cache_time && ( time() - $cache_time < DAY_IN_SECONDS ) && $cache_ip && $current_ip && $cache_ip === $current_ip ) {
            return;
        }

        $lock_key = 'ds_ip_location_refresh_lock_' . (int) $user->ID;
        if ( get_transient( $lock_key ) ) {
            return;
        }

        set_transient( $lock_key, 1, MINUTE_IN_SECONDS );
        try {
            developer_starter_update_user_ip_location_on_login( $user->user_login, $user );
        } catch ( \Throwable $e ) {
            // 前台请求必须可用，任何归属地刷新异常都忽略。
        }
        delete_transient( $lock_key );
    }
}
add_action( 'init', 'developer_starter_maybe_refresh_current_user_ip_location', 30 );
