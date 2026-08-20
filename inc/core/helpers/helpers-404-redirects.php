<?php
/**
 * 404 redirect helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_404_redirect_get_option' ) ) {
    /**
     * Read a theme option with a fallback for unusual helper load order.
     *
     * @param string $key     Option key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    function developer_starter_404_redirect_get_option( $key, $default = '' ) {
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return developer_starter_get_option( $key, $default );
        }

        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            return $default;
        }

        return array_key_exists( $key, $options ) ? $options[ $key ] : $default;
    }
}

if ( ! function_exists( 'developer_starter_404_redirect_get_home_host' ) ) {
    /**
     * Get normalized site host.
     *
     * @return string
     */
    function developer_starter_404_redirect_get_home_host() {
        $host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        return is_string( $host ) ? strtolower( $host ) : '';
    }
}

if ( ! function_exists( 'developer_starter_404_redirect_host_matches_site' ) ) {
    /**
     * Check whether a host belongs to the current site.
     *
     * @param string $host Host to check.
     * @return bool
     */
    function developer_starter_404_redirect_host_matches_site( $host ) {
        $host      = strtolower( trim( (string) $host ) );
        $site_host = developer_starter_404_redirect_get_home_host();

        $matches = '' !== $host && '' !== $site_host && $host === $site_host;

        return (bool) apply_filters( 'developer_starter_404_redirect_host_matches_site', $matches, $host, $site_host );
    }
}

if ( ! function_exists( 'developer_starter_404_redirect_strip_home_path' ) ) {
    /**
     * Strip the WordPress home path from a request path on subdirectory installs.
     *
     * @param string $path Raw path.
     * @return string
     */
    function developer_starter_404_redirect_strip_home_path( $path ) {
        $path = '/' . ltrim( (string) $path, '/' );

        $home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
        $home_path = is_string( $home_path ) ? '/' . trim( $home_path, '/' ) : '/';

        if ( '/' !== $home_path && 0 === strpos( $path . '/', $home_path . '/' ) ) {
            $path = substr( $path, strlen( $home_path ) );
            if ( ! is_string( $path ) || '' === $path ) {
                $path = '/';
            }
        }

        return $path;
    }
}

if ( ! function_exists( 'developer_starter_normalize_404_redirect_path' ) ) {
    /**
     * Normalize a source path for exact 404 redirect matching.
     *
     * @param mixed $value Raw source path or same-site URL.
     * @return string
     */
    function developer_starter_normalize_404_redirect_path( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        $value = str_replace( '\\', '/', $value );
        if ( '' === $value || '#' === $value[0] || '?' === $value[0] ) {
            return '';
        }

        if ( 0 === strpos( $value, '//' ) ) {
            $scheme = wp_parse_url( home_url( '/' ), PHP_URL_SCHEME );
            $value  = ( is_string( $scheme ) && '' !== $scheme ? $scheme : 'https' ) . ':' . $value;
        }

        $path = '';
        if ( preg_match( '#^https?://#i', $value ) ) {
            $parts = wp_parse_url( $value );
            if ( ! is_array( $parts ) ) {
                return '';
            }

            $host = isset( $parts['host'] ) ? (string) $parts['host'] : '';
            if ( '' !== $host && ! developer_starter_404_redirect_host_matches_site( $host ) ) {
                return '';
            }

            $path = isset( $parts['path'] ) ? (string) $parts['path'] : '/';
        } elseif ( preg_match( '#^[a-z][a-z0-9+.-]*:#i', $value ) ) {
            return '';
        } else {
            $parts = preg_split( '/[?#]/', $value, 2 );
            $path  = is_array( $parts ) && isset( $parts[0] ) ? (string) $parts[0] : $value;
        }

        $path = trim( sanitize_text_field( $path ) );
        if ( '' === $path ) {
            return '';
        }

        $path = '/' . ltrim( $path, '/' );
        $path = (string) preg_replace( '#/+#', '/', $path );
        $path = developer_starter_404_redirect_strip_home_path( $path );

        return '/' === $path ? '/' : rtrim( $path, '/' );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_404_redirect_target' ) ) {
    /**
     * Sanitize a redirect target. Only same-site absolute URLs and relative paths are accepted.
     *
     * @param mixed $value Raw target.
     * @return string
     */
    function developer_starter_sanitize_404_redirect_target( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        $value = str_replace( '\\', '/', $value );
        if ( '' === $value || '#' === $value[0] || '?' === $value[0] ) {
            return '';
        }

        if ( 0 === strpos( $value, '//' ) ) {
            $scheme = wp_parse_url( home_url( '/' ), PHP_URL_SCHEME );
            $value  = ( is_string( $scheme ) && '' !== $scheme ? $scheme : 'https' ) . ':' . $value;
        }

        if ( preg_match( '#^[a-z][a-z0-9+.-]*:#i', $value ) && ! preg_match( '#^https?://#i', $value ) ) {
            return '';
        }

        $parts = wp_parse_url( $value );
        if ( ! is_array( $parts ) ) {
            return '';
        }

        if ( ! empty( $parts['host'] ) && ! developer_starter_404_redirect_host_matches_site( (string) $parts['host'] ) ) {
            return '';
        }

        $path = isset( $parts['path'] ) ? (string) $parts['path'] : '';
        if ( '' === $path && empty( $parts['host'] ) ) {
            $path = $value;
        }

        $path = developer_starter_normalize_404_redirect_path( $path );
        if ( '' === $path ) {
            return '';
        }

        $target = $path;
        if ( isset( $parts['query'] ) && '' !== (string) $parts['query'] ) {
            $target .= '?' . sanitize_text_field( (string) $parts['query'] );
        }
        if ( isset( $parts['fragment'] ) && '' !== (string) $parts['fragment'] ) {
            $target .= '#' . sanitize_text_field( (string) $parts['fragment'] );
        }

        return esc_url_raw( $target );
    }
}

if ( ! function_exists( 'developer_starter_parse_404_redirect_rules' ) ) {
    /**
     * Parse 404 redirect rules from textarea content.
     *
     * @param mixed $raw Raw rules.
     * @return array<int,array{source:string,target:string}>
     */
    function developer_starter_parse_404_redirect_rules( $raw ) {
        $lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
        if ( ! is_array( $lines ) ) {
            return array();
        }

        $rules = array();
        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( '' === $line || '#' === $line[0] || false === strpos( $line, '=>' ) ) {
                continue;
            }

            list( $source_raw, $target_raw ) = array_map( 'trim', explode( '=>', $line, 2 ) );
            $source = developer_starter_normalize_404_redirect_path( $source_raw );
            $target = developer_starter_sanitize_404_redirect_target( $target_raw );
            if ( '' === $source || '/' === $source || '' === $target ) {
                continue;
            }

            $target_path = developer_starter_normalize_404_redirect_path( $target );
            if ( '' === $target_path || $target_path === $source ) {
                continue;
            }

            $rules[ $source ] = array(
                'source' => $source,
                'target' => $target,
            );
        }

        return array_values( $rules );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_404_redirect_rules' ) ) {
    /**
     * Sanitize redirect rules for storage.
     *
     * @param mixed $raw Raw rules.
     * @return string
     */
    function developer_starter_sanitize_404_redirect_rules( $raw ) {
        $rules = developer_starter_parse_404_redirect_rules( $raw );
        if ( empty( $rules ) ) {
            return '';
        }

        $lines = array();
        foreach ( $rules as $rule ) {
            $lines[] = $rule['source'] . ' => ' . $rule['target'];
        }

        return implode( "\n", $lines );
    }
}

if ( ! function_exists( 'developer_starter_get_current_404_redirect_path' ) ) {
    /**
     * Get normalized current request path.
     *
     * @return string
     */
    function developer_starter_get_current_404_redirect_path() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';
        return developer_starter_normalize_404_redirect_path( $request_uri );
    }
}

if ( ! function_exists( 'developer_starter_build_404_redirect_target_url' ) ) {
    /**
     * Build a safe absolute URL for a sanitized target.
     *
     * @param string $target Sanitized target path.
     * @return string
     */
    function developer_starter_build_404_redirect_target_url( $target ) {
        $target = developer_starter_sanitize_404_redirect_target( $target );
        if ( '' === $target ) {
            return '';
        }

        return wp_validate_redirect( home_url( $target ), '' );
    }
}

if ( ! function_exists( 'developer_starter_maybe_redirect_404' ) ) {
    /**
     * Redirect known deleted URLs before rendering the 404 template.
     *
     * @return void
     */
    function developer_starter_maybe_redirect_404() {
        if ( is_admin() || headers_sent() || ! is_404() || is_feed() || is_preview() ) {
            return;
        }

        if ( '1' !== (string) developer_starter_404_redirect_get_option( 'error_404_redirect_enable', '' ) ) {
            return;
        }

        $rules = developer_starter_parse_404_redirect_rules(
            developer_starter_404_redirect_get_option( 'error_404_redirect_rules', '' )
        );
        if ( empty( $rules ) ) {
            return;
        }

        $current_path = developer_starter_get_current_404_redirect_path();
        if ( '' === $current_path || '/' === $current_path ) {
            return;
        }

        foreach ( $rules as $rule ) {
            if ( $current_path !== $rule['source'] ) {
                continue;
            }

            $target_url = developer_starter_build_404_redirect_target_url( $rule['target'] );
            if ( '' === $target_url ) {
                return;
            }

            $target_path = developer_starter_normalize_404_redirect_path( $target_url );
            if ( $target_path === $current_path ) {
                return;
            }

            $status = absint( developer_starter_404_redirect_get_option( 'error_404_redirect_status', '301' ) );
            if ( ! in_array( $status, array( 301, 302 ), true ) ) {
                $status = 301;
            }

            wp_safe_redirect( $target_url, $status, 'QiLing 404 Redirect' );
            exit;
        }
    }
}
add_action( 'template_redirect', 'developer_starter_maybe_redirect_404', 0 );
