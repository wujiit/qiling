<?php
/**
 * Client IP helper functions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_ip_in_cidr' ) ) {
    /**
     * Check whether an IP address is inside a CIDR range.
     *
     * @param string $ip   IP address.
     * @param string $cidr CIDR range or exact IP.
     * @return bool
     */
    function developer_starter_ip_in_cidr( $ip, $cidr ) {
        $ip   = trim( (string) $ip );
        $cidr = trim( (string) $cidr );
        if ( '' === $ip || '' === $cidr ) {
            return false;
        }

        if ( false === strpos( $cidr, '/' ) ) {
            return $ip === $cidr;
        }

        list( $subnet, $mask ) = array_pad( explode( '/', $cidr, 2 ), 2, '' );
        $mask       = (int) $mask;
        $ip_bin     = @inet_pton( $ip );
        $subnet_bin = @inet_pton( $subnet );

        if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
            return false;
        }

        $max_bits = strlen( $ip_bin ) * 8;
        if ( $mask < 0 || $mask > $max_bits ) {
            return false;
        }

        $bytes = (int) floor( $mask / 8 );
        $bits  = $mask % 8;

        if ( $bytes > 0 && substr( $ip_bin, 0, $bytes ) !== substr( $subnet_bin, 0, $bytes ) ) {
            return false;
        }

        if ( 0 === $bits ) {
            return true;
        }

        $mask_byte = ~( 255 >> $bits ) & 255;
        return ( ord( $ip_bin[ $bytes ] ) & $mask_byte ) === ( ord( $subnet_bin[ $bytes ] ) & $mask_byte );
    }
}

if ( ! function_exists( 'developer_starter_get_trusted_proxy_list' ) ) {
    /**
     * Get trusted proxy IP/CIDR ranges.
     *
     * @return array<int,string>
     */
    function developer_starter_get_trusted_proxy_list() {
        $trusted = array();

        if ( defined( 'DEVELOPER_STARTER_TRUSTED_PROXIES' ) ) {
            $const_value = constant( 'DEVELOPER_STARTER_TRUSTED_PROXIES' );
            if ( is_array( $const_value ) ) {
                $trusted = array_merge( $trusted, $const_value );
            } elseif ( is_string( $const_value ) && '' !== $const_value ) {
                $trusted = array_merge( $trusted, preg_split( '/[\s,]+/', $const_value ) );
            }
        }

        $trusted = apply_filters( 'developer_starter_trusted_proxies', $trusted );
        if ( ! is_array( $trusted ) ) {
            return array();
        }

        $trusted = array_map( 'trim', $trusted );
        $trusted = array_filter(
            $trusted,
            static function ( $item ) {
                return '' !== $item;
            }
        );

        return array_values( array_unique( $trusted ) );
    }
}

if ( ! function_exists( 'developer_starter_is_trusted_proxy_ip' ) ) {
    /**
     * Whether an address belongs to a trusted proxy.
     *
     * @param string $remote_addr Remote address.
     * @return bool
     */
    function developer_starter_is_trusted_proxy_ip( $remote_addr ) {
        if ( ! filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
            return false;
        }

        $trusted = developer_starter_get_trusted_proxy_list();
        if ( empty( $trusted ) ) {
            return false;
        }

        foreach ( $trusted as $range ) {
            if ( developer_starter_ip_in_cidr( $remote_addr, $range ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_should_trust_forwarded_headers' ) ) {
    /**
     * Decide whether forwarded IP headers should be trusted.
     *
     * @param string $mode        Trust mode.
     * @param string $remote_addr Remote address.
     * @return bool
     */
    function developer_starter_should_trust_forwarded_headers( $mode, $remote_addr ) {
        $mode = sanitize_key( (string) $mode );
        if ( ! filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
            return false;
        }

        if ( 'remote_only' === $mode ) {
            return false;
        }

        if ( 'strict_proxy' === $mode ) {
            return developer_starter_is_trusted_proxy_ip( $remote_addr );
        }

        if ( 'cdn_compatible' === $mode ) {
            return true;
        }

        $trusted = developer_starter_get_trusted_proxy_list();
        if ( ! empty( $trusted ) ) {
            return developer_starter_is_trusted_proxy_ip( $remote_addr );
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_parse_ip_candidates' ) ) {
    /**
     * Parse IP candidates from a proxy header.
     *
     * @param string $raw_value Header value.
     * @return array<int,string>
     */
    function developer_starter_parse_ip_candidates( $raw_value ) {
        $raw_value = trim( (string) $raw_value );
        if ( '' === $raw_value ) {
            return array();
        }

        $parts = preg_split( '/[,;]/', $raw_value );
        if ( ! is_array( $parts ) ) {
            $parts = array( $raw_value );
        }

        $ips = array();
        foreach ( $parts as $part ) {
            $token = trim( (string) $part );
            if ( '' === $token ) {
                continue;
            }

            if ( 0 === stripos( $token, 'for=' ) ) {
                $token = trim( substr( $token, 4 ) );
            } elseif ( false !== strpos( $token, '=' ) ) {
                continue;
            }

            $token = trim( $token, " \t\n\r\0\x0B\"'" );
            if ( '' === $token || 'unknown' === strtolower( $token ) ) {
                continue;
            }

            if ( preg_match( '/^\[([a-f0-9:.]+)\](?::\d+)?$/i', $token, $matches ) ) {
                $token = $matches[1];
            } elseif ( preg_match( '/^(\d{1,3}(?:\.\d{1,3}){3})(?::\d+)?$/', $token, $matches ) ) {
                $token = $matches[1];
            }

            if ( filter_var( $token, FILTER_VALIDATE_IP ) ) {
                $ips[] = $token;
            }
        }

        return array_values( array_unique( $ips ) );
    }
}

if ( ! function_exists( 'developer_starter_is_public_ip' ) ) {
    /**
     * Whether an IP is public routable.
     *
     * @param string $ip IP address.
     * @return bool
     */
    function developer_starter_is_public_ip( $ip ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return false;
        }

        return (bool) filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    }
}

if ( ! function_exists( 'developer_starter_get_client_ip' ) ) {
    /**
     * Get the normalized client IP address for the current request.
     *
     * @return string
     */
    function developer_starter_get_client_ip() {
        $remote_addr = isset( $_SERVER['REMOTE_ADDR'] )
            ? trim( sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) )
            : '';
        $mode        = apply_filters( 'developer_starter_client_ip_mode', 'auto_proxy' );

        if ( ! developer_starter_should_trust_forwarded_headers( $mode, $remote_addr ) ) {
            return filter_var( $remote_addr, FILTER_VALIDATE_IP ) ? $remote_addr : '0.0.0.0';
        }

        $candidates = array();

        $forwarded_header_keys = array(
            'HTTP_TRUE_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
        );
        foreach ( $forwarded_header_keys as $header_key ) {
            $header_value = isset( $_SERVER[ $header_key ] ) ? trim( wp_unslash( (string) $_SERVER[ $header_key ] ) ) : '';
            if ( '' === $header_value ) {
                continue;
            }

            $candidates = array_merge( $candidates, developer_starter_parse_ip_candidates( $header_value ) );
        }

        if ( filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
            $candidates[] = $remote_addr;
        }

        $candidates = array_values( array_unique( array_filter( array_map( 'trim', $candidates ) ) ) );

        foreach ( $candidates as $candidate ) {
            if ( developer_starter_is_public_ip( $candidate ) ) {
                return $candidate;
            }
        }

        foreach ( $candidates as $candidate ) {
            if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
                return $candidate;
            }
        }

        return '0.0.0.0';
    }
}

if ( ! function_exists( 'developer_starter_resolve_client_ip' ) ) {
    /**
     * Resolve a client IP, optionally honoring a test/integration callback.
     *
     * @param callable|null $callback Optional IP callback.
     * @return string
     */
    function developer_starter_resolve_client_ip( $callback = null ) {
        if ( is_callable( $callback ) ) {
            $ip = call_user_func( $callback );
            $ip = is_scalar( $ip ) ? trim( sanitize_text_field( (string) $ip ) ) : '';
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }

        return developer_starter_get_client_ip();
    }
}
