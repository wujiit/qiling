<?php
/**
 * Base helpers for OAuth2 social login providers.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\Social;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Abstract_OAuth2_Provider implements Provider_Interface {

    /**
     * Read a theme option.
     *
     * @param string $key Option key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    protected function get_option( $key, $default = '' ) {
        return function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( $key, $default )
            : $default;
    }

    /**
     * Sanitize a scalar option.
     *
     * @param string $key Option key.
     * @return string
     */
    protected function get_text_option( $key ) {
        return trim( (string) $this->get_option( $key, '' ) );
    }

    /**
     * Build the shared admin-post callback URL.
     *
     * @return string
     */
    public function get_callback_url() {
        return add_query_arg(
            array(
                'action'   => 'developer_starter_social_login_callback',
                'provider' => $this->get_key(),
            ),
            admin_url( 'admin-post.php' )
        );
    }

    /**
     * Perform a remote GET request and decode JSON, query string, or QQ JSONP payloads.
     *
     * @param string $url Request URL.
     * @return array<string,mixed>|\WP_Error
     */
    protected function remote_get_decoded( $url ) {
        return $this->remote_get_decoded_with_headers( $url );
    }

    /**
     * Perform a remote GET request with custom headers.
     *
     * @param string               $url Request URL.
     * @param array<string,string> $headers Request headers.
     * @return array<string,mixed>|\WP_Error
     */
    protected function remote_get_decoded_with_headers( $url, $headers = array() ) {
        $headers = array_merge(
            array(
                'Accept' => 'application/json,text/plain,*/*',
            ),
            is_array( $headers ) ? $headers : array()
        );

        $response = wp_remote_get(
            $url,
            array(
                'timeout'     => 12,
                'redirection' => 2,
                'sslverify'   => true,
                'headers'     => $headers,
            )
        );

        return $this->decode_remote_response( $response );
    }

    /**
     * Perform a remote POST request and decode the provider response.
     *
     * @param string              $url Request URL.
     * @param array<string,mixed> $args wp_remote_post arguments.
     * @return array<string,mixed>|\WP_Error
     */
    protected function remote_post_decoded( $url, $args = array() ) {
        $default_headers = array(
            'Accept' => 'application/json,text/plain,*/*',
        );
        $headers = isset( $args['headers'] ) && is_array( $args['headers'] )
            ? array_merge( $default_headers, $args['headers'] )
            : $default_headers;

        $request_args = wp_parse_args(
            $args,
            array(
                'timeout'     => 12,
                'redirection' => 2,
                'sslverify'   => true,
                'headers'     => $headers,
                'body'        => array(),
            )
        );
        $request_args['headers'] = $headers;

        $response = wp_remote_post( $url, $request_args );

        return $this->decode_remote_response( $response );
    }

    /**
     * Decode a WordPress HTTP API response.
     *
     * @param array<string,mixed>|\WP_Error $response HTTP response.
     * @return array<string,mixed>|\WP_Error
     */
    protected function decode_remote_response( $response ) {
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code( $response );
        $body   = trim( (string) wp_remote_retrieve_body( $response ) );

        if ( $status < 200 || $status >= 300 ) {
            return new \WP_Error( 'social_login_http_error', __( '第三方授权接口暂时不可用，请稍后再试。', 'developer-starter' ) );
        }

        if ( '' === $body ) {
            return new \WP_Error( 'social_login_empty_response', __( '第三方授权接口返回为空。', 'developer-starter' ) );
        }

        $decoded = json_decode( $body, true );
        if ( is_array( $decoded ) ) {
            return $decoded;
        }

        if ( preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]*\s*\((.*)\)\s*;?$/s', $body, $matches ) ) {
            $decoded = json_decode( trim( $matches[1] ), true );
            if ( is_array( $decoded ) ) {
                return $decoded;
            }
        }

        $parsed = array();
        parse_str( $body, $parsed );
        if ( is_array( $parsed ) && ! empty( $parsed ) ) {
            return $parsed;
        }

        return new \WP_Error( 'social_login_bad_response', __( '第三方授权接口返回格式无法识别。', 'developer-starter' ) );
    }

    /**
     * Normalize a profile array.
     *
     * @param array<string,mixed> $profile Raw profile.
     * @return array<string,mixed>
     */
    protected function normalize_profile( $profile ) {
        return array(
            'provider'   => sanitize_key( isset( $profile['provider'] ) ? (string) $profile['provider'] : $this->get_key() ),
            'identifier' => sanitize_text_field( isset( $profile['identifier'] ) ? (string) $profile['identifier'] : '' ),
            'nickname'   => sanitize_text_field( isset( $profile['nickname'] ) ? (string) $profile['nickname'] : '' ),
            'avatar'     => esc_url_raw( isset( $profile['avatar'] ) ? (string) $profile['avatar'] : '' ),
            'email'      => sanitize_email( isset( $profile['email'] ) ? (string) $profile['email'] : '' ),
            'email_verified' => ! empty( $profile['email_verified'] ),
            'raw'        => isset( $profile['raw'] ) && is_array( $profile['raw'] ) ? $profile['raw'] : array(),
        );
    }
}
