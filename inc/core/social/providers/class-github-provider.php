<?php
/**
 * GitHub OAuth social login provider.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\Social\Providers;

use Developer_Starter\Core\Social\Abstract_OAuth2_Provider;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GitHub_Provider extends Abstract_OAuth2_Provider {

    const AUTHORIZE_ENDPOINT = 'https://github.com/login/oauth/authorize';
    const TOKEN_ENDPOINT     = 'https://github.com/login/oauth/access_token';
    const USER_ENDPOINT      = 'https://api.github.com/user';
    const EMAILS_ENDPOINT    = 'https://api.github.com/user/emails';

    /**
     * @return string
     */
    public function get_key() {
        return 'github';
    }

    /**
     * @return string
     */
    public function get_label() {
        return __( 'GitHub', 'developer-starter' );
    }

    /**
     * @return bool
     */
    public function is_available() {
        return $this->get_text_option( 'social_login_github_enable' ) === '1'
            && '' !== $this->get_client_id()
            && '' !== $this->get_client_secret();
    }

    /**
     * @param string $state OAuth state.
     * @return string|\WP_Error
     */
    public function get_authorization_url( $state ) {
        if ( ! $this->is_available() ) {
            return new \WP_Error( 'github_not_configured', __( 'GitHub 登录尚未配置完整。', 'developer-starter' ) );
        }

        return add_query_arg(
            array(
                'client_id'    => $this->get_client_id(),
                'redirect_uri' => $this->get_callback_url(),
                'scope'        => 'read:user user:email',
                'state'        => $state,
                'allow_signup' => 'true',
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
            return new \WP_Error( 'github_missing_code', __( 'GitHub 授权回调缺少 code。', 'developer-starter' ) );
        }

        $token = $this->request_access_token( $code );
        if ( is_wp_error( $token ) ) {
            return $token;
        }

        $access_token = (string) $token['access_token'];
        $user_info    = $this->request_user_info( $access_token );
        if ( is_wp_error( $user_info ) ) {
            return $user_info;
        }

        if ( empty( $user_info['id'] ) ) {
            return new \WP_Error( 'github_user_id_missing', __( 'GitHub 用户资料缺少唯一 ID。', 'developer-starter' ) );
        }

        $email = isset( $user_info['email'] ) ? sanitize_email( (string) $user_info['email'] ) : '';
        if ( '' === $email ) {
            $emails = $this->request_emails( $access_token );
            if ( ! is_wp_error( $emails ) ) {
                $email = $this->pick_email( $emails );
            }
        }

        return $this->normalize_profile(
            array(
                'provider'       => $this->get_key(),
                'identifier'     => (string) $user_info['id'],
                'nickname'       => ! empty( $user_info['name'] ) ? (string) $user_info['name'] : ( isset( $user_info['login'] ) ? (string) $user_info['login'] : '' ),
                'avatar'         => isset( $user_info['avatar_url'] ) ? (string) $user_info['avatar_url'] : '',
                'email'          => $email,
                'email_verified' => '' !== $email,
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
        return $this->get_text_option( 'social_login_github_client_id' );
    }

    /**
     * @return string
     */
    private function get_client_secret() {
        return $this->get_text_option( 'social_login_github_client_secret' );
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
                    'client_id'     => $this->get_client_id(),
                    'client_secret' => $this->get_client_secret(),
                    'code'          => $code,
                    'redirect_uri'  => $this->get_callback_url(),
                ),
            )
        );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( ! empty( $data['error'] ) ) {
            return new \WP_Error( 'github_token_error', $this->get_api_error_message( $data, __( 'GitHub Access Token 获取失败。', 'developer-starter' ) ) );
        }

        if ( empty( $data['access_token'] ) ) {
            return new \WP_Error( 'github_token_missing', __( 'GitHub Access Token 返回缺少 access_token。', 'developer-starter' ) );
        }

        return $data;
    }

    /**
     * @param string $access_token Access token.
     * @return array<string,mixed>|\WP_Error
     */
    private function request_user_info( $access_token ) {
        $data = $this->remote_get_decoded_with_headers( self::USER_ENDPOINT, $this->get_api_headers( $access_token ) );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( ! empty( $data['message'] ) && empty( $data['id'] ) ) {
            return new \WP_Error( 'github_user_info_error', $this->get_api_error_message( $data, __( 'GitHub 用户资料获取失败。', 'developer-starter' ) ) );
        }

        return $data;
    }

    /**
     * @param string $access_token Access token.
     * @return array<int,array<string,mixed>>|\WP_Error
     */
    private function request_emails( $access_token ) {
        return $this->remote_get_decoded_with_headers( self::EMAILS_ENDPOINT, $this->get_api_headers( $access_token ) );
    }

    /**
     * @param string $access_token Access token.
     * @return array<string,string>
     */
    private function get_api_headers( $access_token ) {
        return array(
            'Authorization'        => 'Bearer ' . $access_token,
            'Accept'               => 'application/vnd.github+json',
            'User-Agent'           => $this->get_user_agent(),
            'X-GitHub-Api-Version' => '2022-11-28',
        );
    }

    /**
     * @return string
     */
    private function get_user_agent() {
        $host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        if ( ! is_string( $host ) || '' === $host ) {
            $host = 'WordPress';
        }

        return 'Qiling-Theme-Social-Login (' . $host . ')';
    }

    /**
     * @param array<int,array<string,mixed>> $emails Email list.
     * @return string
     */
    private function pick_email( $emails ) {
        $first_verified = '';

        foreach ( $emails as $email_info ) {
            if ( ! is_array( $email_info ) || empty( $email_info['email'] ) ) {
                continue;
            }

            if ( empty( $email_info['verified'] ) ) {
                continue;
            }

            $email = sanitize_email( (string) $email_info['email'] );
            if ( '' === $email || ! is_email( $email ) ) {
                continue;
            }

            if ( '' === $first_verified ) {
                $first_verified = $email;
            }
            if ( ! empty( $email_info['primary'] ) ) {
                return $email;
            }
        }

        return $first_verified;
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
            return sanitize_text_field( (string) $data['error'] );
        }

        if ( ! empty( $data['message'] ) ) {
            return sanitize_text_field( (string) $data['message'] );
        }

        return $fallback;
    }
}
