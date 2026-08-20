<?php
/**
 * Security header helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取默认 Permissions-Policy。
 *
 * @return string
 */
function developer_starter_get_default_permissions_policy_header() {
    return 'geolocation=(), camera=(), microphone=()';
}

/**
 * Permissions-Policy 允许的指令。
 *
 * @return array<string,bool>
 */
function developer_starter_get_allowed_permissions_policy_directives() {
    $directives = array(
        'accelerometer',
        'ambient-light-sensor',
        'attribution-reporting',
        'autoplay',
        'battery',
        'bluetooth',
        'browsing-topics',
        'camera',
        'ch-device-memory',
        'ch-downlink',
        'ch-dpr',
        'ch-ect',
        'ch-prefers-color-scheme',
        'ch-prefers-reduced-motion',
        'ch-prefers-reduced-transparency',
        'ch-rtt',
        'ch-save-data',
        'ch-ua',
        'ch-ua-arch',
        'ch-ua-bitness',
        'ch-ua-full-version',
        'ch-ua-full-version-list',
        'ch-ua-mobile',
        'ch-ua-model',
        'ch-ua-platform',
        'ch-ua-platform-version',
        'ch-ua-wow64',
        'ch-viewport-height',
        'ch-viewport-width',
        'ch-width',
        'clipboard-read',
        'clipboard-write',
        'conversion-measurement',
        'cross-origin-isolated',
        'display-capture',
        'document-domain',
        'encrypted-media',
        'execution-while-not-rendered',
        'execution-while-out-of-viewport',
        'focus-without-user-activation',
        'fullscreen',
        'gamepad',
        'geolocation',
        'gyroscope',
        'hid',
        'identity-credentials-get',
        'idle-detection',
        'interest-cohort',
        'join-ad-interest-group',
        'keyboard-map',
        'layout-animations',
        'local-fonts',
        'magnetometer',
        'microphone',
        'midi',
        'otp-credentials',
        'payment',
        'picture-in-picture',
        'private-state-token-issuance',
        'private-state-token-redemption',
        'publickey-credentials-create',
        'publickey-credentials-get',
        'run-ad-auction',
        'screen-wake-lock',
        'serial',
        'shared-autofill',
        'speaker-selection',
        'storage-access',
        'sync-xhr',
        'unload',
        'usb',
        'vertical-scroll',
        'web-share',
        'window-management',
        'xr-spatial-tracking',
    );

    /**
     * Filter the Permissions-Policy directive allowlist.
     *
     * @param array<int,string> $directives Directive names.
     */
    $directives = apply_filters( 'developer_starter_allowed_permissions_policy_directives', $directives );
    $directives = array_map( 'sanitize_key', (array) $directives );
    $directives = array_filter( $directives );

    return array_fill_keys( array_values( array_unique( $directives ) ), true );
}

/**
 * 标准化 Permissions-Policy source token。
 *
 * @param string $source Source token.
 * @return string
 */
function developer_starter_normalize_permissions_policy_source( $source ) {
    $source = trim( (string) $source );
    if ( '' === $source ) {
        return '';
    }

    $unquoted_source = trim( $source, "\"'" );
    $special_sources = array(
        '*'    => '*',
        'self' => 'self',
        'src'  => 'src',
        'none' => 'none',
    );
    $source_key = strtolower( $unquoted_source );
    if ( isset( $special_sources[ $source_key ] ) ) {
        return $special_sources[ $source_key ];
    }

    if ( preg_match( '#\Ahttps?://(?:\*\.)?[A-Za-z0-9.-]+(?::[0-9]{1,5})?\z#', $unquoted_source ) ) {
        return '"' . $unquoted_source . '"';
    }

    return '';
}

/**
 * 清洗 Permissions-Policy 响应头值。
 *
 * @param string $policy Header value.
 * @return string
 */
function developer_starter_sanitize_permissions_policy_header( $policy ) {
    $policy = preg_replace( '/[\r\n]+/', '', (string) $policy );
    $policy = preg_replace( '/[\x00-\x1F\x7F]+/', '', (string) $policy );
    $policy = trim( (string) $policy );
    if ( '' === $policy ) {
        return '';
    }

    if ( ! preg_match( '/\A[A-Za-z0-9\s=(),.:\/_\-*\'"]+\z/', $policy ) ) {
        return '';
    }

    $allowed_directives = developer_starter_get_allowed_permissions_policy_directives();
    $normalized = array();
    $directive_values = preg_split( '/\s*,\s*/', $policy );
    if ( ! is_array( $directive_values ) ) {
        return '';
    }

    foreach ( $directive_values as $directive_value ) {
        $directive_value = trim( (string) $directive_value );
        if ( '' === $directive_value ) {
            continue;
        }

        if ( ! preg_match( '/\A([a-z][a-z0-9-]*)\s*=\s*\(([^()]*)\)\z/i', $directive_value, $matches ) ) {
            continue;
        }

        $directive = sanitize_key( $matches[1] );
        if ( '' === $directive || ! isset( $allowed_directives[ $directive ] ) ) {
            continue;
        }

        $sources = array();
        $source_list = trim( (string) $matches[2] );
        if ( '' !== $source_list ) {
            $source_values = preg_split( '/\s+/', $source_list );
            if ( ! is_array( $source_values ) ) {
                continue;
            }

            foreach ( $source_values as $source ) {
                $source = developer_starter_normalize_permissions_policy_source( $source );
                if ( '' !== $source ) {
                    $sources[] = $source;
                }
            }
        }

        $normalized[ $directive ] = $directive . '=(' . implode( ' ', array_values( array_unique( $sources ) ) ) . ')';
    }

    return implode( ', ', array_values( $normalized ) );
}

/**
 * 输出安全响应头
 */
function developer_starter_security_headers() {
    if ( ! developer_starter_get_option( 'security_headers_enable', '' ) || headers_sent() ) {
        return;
    }

    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'X-Content-Type-Options: nosniff' );

    if ( is_ssl() && apply_filters( 'developer_starter_security_hsts_enabled', true ) ) {
        $hsts_header = (string) apply_filters(
            'developer_starter_security_hsts_header',
            'max-age=31536000; includeSubDomains'
        );
        $hsts_header = trim( preg_replace( '/[\r\n]+/', '', $hsts_header ) );
        if ( '' !== $hsts_header ) {
            header( 'Strict-Transport-Security: ' . $hsts_header );
        }
    }

    $referrer_policy = (string) developer_starter_get_option( 'security_headers_referrer_policy', 'strict-origin-when-cross-origin' );
    $allowed_referrer_policies = array(
        'strict-origin-when-cross-origin',
        'no-referrer',
        'same-origin',
        'origin-when-cross-origin',
        'strict-origin',
    );
    if ( ! in_array( $referrer_policy, $allowed_referrer_policies, true ) ) {
        $referrer_policy = 'strict-origin-when-cross-origin';
    }
    header( 'Referrer-Policy: ' . $referrer_policy );

    $permissions_policy = developer_starter_sanitize_permissions_policy_header(
        (string) developer_starter_get_option( 'security_headers_permissions_policy', '' )
    );
    if ( $permissions_policy === '' ) {
        $permissions_policy = developer_starter_get_default_permissions_policy_header();
    }
    header( 'Permissions-Policy: ' . $permissions_policy );
}
add_action( 'send_headers', 'developer_starter_security_headers', 20 );
