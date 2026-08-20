<?php
/**
 * QQ Connect social login provider.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\Social\Providers;

use Developer_Starter\Core\Social\Abstract_OAuth2_Provider;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class QQ_Provider extends Abstract_OAuth2_Provider {

    const AUTHORIZE_ENDPOINT = 'https://graph.qq.com/oauth2.0/authorize';
    const TOKEN_ENDPOINT     = 'https://graph.qq.com/oauth2.0/token';
    const OPENID_ENDPOINT    = 'https://graph.qq.com/oauth2.0/me';
    const USERINFO_ENDPOINT  = 'https://graph.qq.com/user/get_user_info';

    /**
     * @return string
     */
    public function get_key() {
        return 'qq';
    }

    /**
     * @return string
     */
    public function get_label() {
        return __( 'QQ', 'developer-starter' );
    }

    /**
     * @return bool
     */
    public function is_available() {
        return $this->get_text_option( 'social_login_qq_enable' ) === '1'
            && '' !== $this->get_app_id()
            && '' !== $this->get_app_key();
    }

    /**
     * @param string $state OAuth state.
     * @return string|\WP_Error
     */
    public function get_authorization_url( $state ) {
        if ( ! $this->is_available() ) {
            return new \WP_Error( 'qq_not_configured', __( 'QQ 登录尚未配置完整。', 'developer-starter' ) );
        }

        return add_query_arg(
            array(
                'response_type' => 'code',
                'client_id'     => $this->get_app_id(),
                'redirect_uri'  => $this->get_callback_url(),
                'state'         => $state,
                'scope'         => 'get_user_info',
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
            return new \WP_Error( 'qq_missing_code', __( 'QQ 授权回调缺少 code。', 'developer-starter' ) );
        }

        $token = $this->request_access_token( $code );
        if ( is_wp_error( $token ) ) {
            return $token;
        }

        $openid = $this->request_openid( (string) $token['access_token'] );
        if ( is_wp_error( $openid ) ) {
            return $openid;
        }

        $user_info = $this->request_user_info( (string) $token['access_token'], (string) $openid['openid'] );
        if ( is_wp_error( $user_info ) ) {
            return $user_info;
        }

        $avatar = '';
        foreach ( array( 'figureurl_qq_2', 'figureurl_qq_1', 'figureurl_2', 'figureurl_1', 'figureurl' ) as $avatar_key ) {
            if ( ! empty( $user_info[ $avatar_key ] ) ) {
                $avatar = (string) $user_info[ $avatar_key ];
                break;
            }
        }

        return $this->normalize_profile(
            array(
                'provider'   => $this->get_key(),
                'identifier' => (string) $openid['openid'],
                'nickname'   => isset( $user_info['nickname'] ) ? (string) $user_info['nickname'] : '',
                'avatar'     => $avatar,
                'raw'        => array(
                    'openid'    => $openid,
                    'user_info' => $user_info,
                ),
            )
        );
    }

    /**
     * @return string
     */
    private function get_app_id() {
        return $this->get_text_option( 'social_login_qq_app_id' );
    }

    /**
     * @return string
     */
    private function get_app_key() {
        return $this->get_text_option( 'social_login_qq_app_key' );
    }

    /**
     * @param string $code Authorization code.
     * @return array<string,mixed>|\WP_Error
     */
    private function request_access_token( $code ) {
        $url = add_query_arg(
            array(
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->get_app_id(),
                'client_secret' => $this->get_app_key(),
                'code'          => $code,
                'redirect_uri'  => $this->get_callback_url(),
                'fmt'           => 'json',
            ),
            self::TOKEN_ENDPOINT
        );

        $data = $this->remote_get_decoded( $url );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( ! empty( $data['error'] ) ) {
            return new \WP_Error( 'qq_token_error', $this->get_api_error_message( $data, __( 'QQ Access Token 获取失败。', 'developer-starter' ) ) );
        }

        if ( empty( $data['access_token'] ) ) {
            return new \WP_Error( 'qq_token_missing', __( 'QQ Access Token 返回缺少 access_token。', 'developer-starter' ) );
        }

        return $data;
    }

    /**
     * @param string $access_token Access token.
     * @return array<string,mixed>|\WP_Error
     */
    private function request_openid( $access_token ) {
        $url = add_query_arg(
            array(
                'access_token' => $access_token,
                'fmt'          => 'json',
            ),
            self::OPENID_ENDPOINT
        );

        $data = $this->remote_get_decoded( $url );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( ! empty( $data['error'] ) ) {
            return new \WP_Error( 'qq_openid_error', $this->get_api_error_message( $data, __( 'QQ OpenID 获取失败。', 'developer-starter' ) ) );
        }

        if ( empty( $data['openid'] ) ) {
            return new \WP_Error( 'qq_openid_missing', __( 'QQ OpenID 返回缺少 openid。', 'developer-starter' ) );
        }

        if ( ! empty( $data['client_id'] ) && (string) $data['client_id'] !== $this->get_app_id() ) {
            return new \WP_Error( 'qq_client_id_mismatch', __( 'QQ OpenID 返回的 App ID 与当前配置不一致。', 'developer-starter' ) );
        }

        return $data;
    }

    /**
     * @param string $access_token Access token.
     * @param string $openid OpenID.
     * @return array<string,mixed>|\WP_Error
     */
    private function request_user_info( $access_token, $openid ) {
        $url = add_query_arg(
            array(
                'access_token'       => $access_token,
                'oauth_consumer_key' => $this->get_app_id(),
                'openid'             => $openid,
                'fmt'                => 'json',
            ),
            self::USERINFO_ENDPOINT
        );

        $data = $this->remote_get_decoded( $url );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( isset( $data['ret'] ) && 0 !== (int) $data['ret'] ) {
            return new \WP_Error( 'qq_user_info_error', $this->get_api_error_message( $data, __( 'QQ 用户资料获取失败。', 'developer-starter' ) ) );
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

        if ( ! empty( $data['msg'] ) ) {
            return sanitize_text_field( (string) $data['msg'] );
        }

        return $fallback;
    }
}
