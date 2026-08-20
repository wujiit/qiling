<?php
/**
 * Google OAuth social login provider.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\Social\Providers;

use Developer_Starter\Core\Social\Abstract_OAuth2_Provider;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Google_Provider extends Abstract_OAuth2_Provider {

    const AUTHORIZE_ENDPOINT = 'https://accounts.google.com/o/oauth2/v2/auth';
    const TOKEN_ENDPOINT     = 'https://oauth2.googleapis.com/token';
    const USERINFO_ENDPOINT  = 'https://openidconnect.googleapis.com/v1/userinfo';

    /**
     * @return string
     */
    public function get_key() {
        return 'google';
    }

    /**
     * @return string
     */
    public function get_label() {
        return __( 'Google', 'developer-starter' );
    }

    /**
     * @return bool
     */
    public function is_available() {
        return $this->get_text_option( 'social_login_google_enable' ) === '1'
            && '' !== $this->get_client_id()
            && '' !== $this->get_client_secret();
    }

    /**
     * @param string $state OAuth state.
     * @return string|\WP_Error
     */
    public function get_authorization_url( $state ) {
        if ( ! $this->is_available() ) {
            return new \WP_Error( 'google_not_configured', __( 'Google 登录尚未配置完整。', 'developer-starter' ) );
        }

        return add_query_arg(
            array(
                'response_type' => 'code',
                'client_id'     => $this->get_client_id(),
                'redirect_uri'  => $this->get_callback_url(),
                'scope'         => 'openid email profile',
                'state'         => $state,
                'access_type'   => 'online',
                'prompt'        => 'select_account',
            ),
            self::AUTHORIZE_ENDPOINT
        );
    }

    /**
     * @param array<string,mixed> $request Callback request.
     * @return array<string,mixed>|\WP_Error
     */
    public function get_profile_from_callback( $request ) {
        $code = isset( $request['code'] ) ? sanitize_text_field( wp_unslash( (string) $request['code'] ) ) : '';
        if ( '' === $code ) {
            return new \WP_Error( 'google_missing_code', __( 'Google 授权回调缺少 code。', 'developer-starter' ) );
        }

        $token = $this->request_access_token( $code );
        if ( is_wp_error( $token ) ) {
            return $token;
        }

        $user_info = $this->request_user_info( (string) $token['access_token'] );
        if ( is_wp_error( $user_info ) ) {
            return $user_info;
        }

        if ( empty( $user_info['sub'] ) ) {
            return new \WP_Error( 'google_user_id_missing', __( 'Google 用户资料缺少唯一 ID。', 'developer-starter' ) );
        }

        $email = isset( $user_info['email'] ) ? sanitize_email( (string) $user_info['email'] ) : '';

        return $this->normalize_profile(
            array(
                'provider'       => $this->get_key(),
                'identifier'     => (string) $user_info['sub'],
                'nickname'       => isset( $user_info['name'] ) ? (string) $user_info['name'] : '',
                'avatar'         => isset( $user_info['picture'] ) ? (string) $user_info['picture'] : '',
                'email'          => $email,
                'email_verified' => ! empty( $user_info['email_verified'] ),
                'raw'            => array(
                    'user_info' => $user_info,
                ),
            )
        );
    }

    /**
     * @return string
     */
    private function get_client_id() {
        return $this->get_text_option( 'social_login_google_client_id' );
    }

    /**
     * @return string
     */
    private function get_client_secret() {
        return $this->get_text_option( 'social_login_google_client_secret' );
    }

    /**
     * @param string $code Authorization code.
     * @return array<string,mixed>|\WP_Error
     */
    private function request_access_token( $code ) {
        $data = $this->remote_post_decoded(
            self::TOKEN_ENDPOINT,
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                ),
                'body'    => array(
                    'code'          => $code,
                    'client_id'     => $this->get_client_id(),
                    'client_secret' => $this->get_client_secret(),
                    'redirect_uri'  => $this->get_callback_url(),
                    'grant_type'    => 'authorization_code',
                ),
            )
        );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( ! empty( $data['error'] ) ) {
            return new \WP_Error( 'google_token_error', $this->get_api_error_message( $data, __( 'Google Access Token 获取失败。', 'developer-starter' ) ) );
        }

        if ( empty( $data['access_token'] ) ) {
            return new \WP_Error( 'google_token_missing', __( 'Google Access Token 返回缺少 access_token。', 'developer-starter' ) );
        }

        return $data;
    }

    /**
     * @param string $access_token Access token.
     * @return array<string,mixed>|\WP_Error
     */
    private function request_user_info( $access_token ) {
        $data = $this->remote_get_decoded_with_headers(
            self::USERINFO_ENDPOINT,
            array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            )
        );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( ! empty( $data['error'] ) && empty( $data['sub'] ) ) {
            return new \WP_Error( 'google_user_info_error', $this->get_api_error_message( $data, __( 'Google 用户资料获取失败。', 'developer-starter' ) ) );
        }

        return $data;
    }

    /**
     * @param array<string,mixed> $data API response.
     * @param string              $fallback Fallback message.
     * @return string
     */
    private function get_api_error_message( $data, $fallback ) {
        if ( ! empty( $data['error_description'] ) ) {
            return sanitize_text_field( (string) $data['error_description'] );
        }

        if ( ! empty( $data['error'] ) ) {
            if ( is_array( $data['error'] ) && ! empty( $data['error']['message'] ) ) {
                return sanitize_text_field( (string) $data['error']['message'] );
            }

            if ( is_array( $data['error'] ) ) {
                return $fallback;
            }

            return sanitize_text_field( (string) $data['error'] );
        }

        return $fallback;
    }
}
