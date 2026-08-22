<?php
/**
 * Auth captcha service.
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

use AlibabaCloud\Dara\Models\RuntimeOptions;
use Darabonba\OpenApi\Models\Config;
use Darabonba\OpenApi\Models\OpenApiRequest;
use Darabonba\OpenApi\Models\Params;
use Darabonba\OpenApi\OpenApiClient;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auth_Captcha_Service {

    /**
     * @var callable|null
     */
    private $option_callback;

    /**
     * @var callable|null
     */
    private $client_ip_callback;

    /**
     * @var int
     */
    private $challenge_ttl = 180;

    /**
     * @var int
     */
    private $token_ttl = 300;

    /**
     * @param array<string,mixed> $args 配置项。
     */
    public function __construct( $args = array() ) {
        $this->option_callback = isset( $args['option_callback'] ) && is_callable( $args['option_callback'] )
            ? $args['option_callback']
            : null;
        $this->client_ip_callback = isset( $args['client_ip_callback'] ) && is_callable( $args['client_ip_callback'] )
            ? $args['client_ip_callback']
            : null;
        $this->challenge_ttl = isset( $args['challenge_ttl'] ) ? max( 1, absint( $args['challenge_ttl'] ) ) : $this->challenge_ttl;
        $this->token_ttl = isset( $args['token_ttl'] ) ? max( 1, absint( $args['token_ttl'] ) ) : $this->token_ttl;
    }

    /**
     * 获取验证码提供商。
     *
     * @return string
     */
    public function get_provider() {
        $provider = (string) $this->get_option( 'captcha_provider', 'theme' );
        return in_array( $provider, array( 'theme', 'aliyun' ), true ) ? $provider : 'theme';
    }

    /**
     * 生成一次性验证码 Token。
     *
     * @return array<string,int|string>
     */
    public function issue_token() {
        $token = wp_generate_uuid4();
        $ip = developer_starter_resolve_client_ip( $this->client_ip_callback );

        set_transient( 'ds_captcha_' . $token, 1, $this->token_ttl );
        set_transient( 'ds_captcha_' . $token . '_' . md5( $ip ), 1, $this->token_ttl );

        return array(
            'token'      => $token,
            'expires_in' => $this->token_ttl,
        );
    }

    /**
     * 调用阿里云验证码 2.0 进行服务端核验。
     *
     * @param string $captcha_verify_param 前端回调返回参数。
     * @param string $scene_id 场景 ID。
     * @return true|\WP_Error
     */
    public function verify_aliyun( $captcha_verify_param, $scene_id ) {
        $captcha_verify_param = trim( (string) $captcha_verify_param );
        $scene_id = trim( (string) $scene_id );

        if ( '' === $captcha_verify_param ) {
            return new \WP_Error( 'captcha_param_missing', __( '验证失败，请重试', 'developer-starter' ) );
        }

        if ( '' === $scene_id ) {
            $scene_id = trim( (string) $this->get_option( 'aliyun_captcha_scene_auth', '' ) );
        }

        $access_key_id = trim( (string) $this->get_option( 'aliyun_captcha_access_key_id', '' ) );
        $access_key_secret = trim( (string) $this->get_option( 'aliyun_captcha_access_key_secret', '' ) );
        $region_id = trim( (string) $this->get_option( 'aliyun_captcha_region', 'cn-shanghai' ) );
        $endpoint = trim( (string) $this->get_option( 'aliyun_captcha_endpoint', '' ) );
        $client_region = trim( (string) $this->get_option( 'aliyun_captcha_client_region', '' ) );

        if ( '' === $access_key_id || '' === $access_key_secret || '' === $scene_id ) {
            return new \WP_Error( 'captcha_config_missing', __( '验证码配置不完整，请联系管理员', 'developer-starter' ) );
        }

        if ( ! in_array( $client_region, array( 'cn', 'sgp' ), true ) ) {
            $client_region = ( false !== strpos( strtolower( $region_id ), 'sgp' ) || false !== strpos( strtolower( $endpoint ), 'ap-southeast-1' ) ) ? 'sgp' : 'cn';
        }

        if ( 'cn' === strtolower( $region_id ) ) {
            $region_id = 'cn-shanghai';
        } elseif ( 'sgp' === strtolower( $region_id ) ) {
            $region_id = 'ap-southeast-1';
        }

        if ( '' === $region_id || 'cn-hangzhou' === strtolower( $region_id ) ) {
            $region_id = ( 'sgp' === $client_region ) ? 'ap-southeast-1' : 'cn-shanghai';
        }

        if ( ! $this->load_aliyun_sdk() ) {
            return new \WP_Error( 'captcha_sdk_missing', __( '验证码服务未正确安装，请联系管理员', 'developer-starter' ) );
        }

        if ( '' === $endpoint ) {
            $endpoint = ( 'sgp' === $client_region ) ? 'captcha.ap-southeast-1.aliyuncs.com' : 'captcha.cn-shanghai.aliyuncs.com';
        }

        try {
            $config = new Config(
                array(
                    'accessKeyId'     => $access_key_id,
                    'accessKeySecret' => $access_key_secret,
                    'regionId'        => $region_id,
                    'endpoint'        => $endpoint,
                )
            );

            $client = new OpenApiClient( $config );
            $request = new OpenApiRequest(
                array(
                    'body' => array(
                        'CaptchaVerifyParam' => $captcha_verify_param,
                        'SceneId'            => $scene_id,
                    ),
                )
            );
            $params = new Params(
                array(
                    'action'      => 'VerifyIntelligentCaptcha',
                    'version'     => '2023-03-05',
                    'protocol'    => 'HTTPS',
                    'pathname'    => '/',
                    'method'      => 'POST',
                    'authType'    => 'AK',
                    'style'       => 'RPC',
                    'reqBodyType' => 'formData',
                    'bodyType'    => 'json',
                )
            );
            $runtime = new RuntimeOptions( array() );

            $response = $client->callApi( $params, $request, $runtime );
            $body = ( is_array( $response ) && isset( $response['body'] ) && is_array( $response['body'] ) ) ? $response['body'] : array();

            $api_success = ! empty( $body['Success'] ) || ! empty( $body['success'] );
            $result = array();
            if ( isset( $body['Result'] ) && is_array( $body['Result'] ) ) {
                $result = $body['Result'];
            } elseif ( isset( $body['result'] ) && is_array( $body['result'] ) ) {
                $result = $body['result'];
            }
            $verify_result = ! empty( $result['VerifyResult'] ) || ! empty( $result['verifyResult'] );

            if ( $api_success && $verify_result ) {
                return true;
            }

            $message = isset( $body['Message'] ) ? trim( (string) $body['Message'] ) : '';
            if ( '' === $message && isset( $body['message'] ) ) {
                $message = trim( (string) $body['message'] );
            }
            if ( '' === $message ) {
                $message = __( '验证失败，请重试', 'developer-starter' );
            }
            if ( isset( $result['VerifyCode'] ) && '' !== (string) $result['VerifyCode'] ) {
                $message .= ' (' . sanitize_text_field( (string) $result['VerifyCode'] ) . ')';
            } elseif ( isset( $result['verifyCode'] ) && '' !== (string) $result['verifyCode'] ) {
                $message .= ' (' . sanitize_text_field( (string) $result['verifyCode'] ) . ')';
            }

            return new \WP_Error( 'captcha_verify_failed', $message );
        } catch ( \Throwable $e ) {
            developer_starter_log( 'captcha', 'Aliyun verify exception.', array( 'exception' => $e ), 'error' );
            return new \WP_Error( 'captcha_service_error', __( '验证码服务暂不可用，请稍后重试', 'developer-starter' ) );
        }
    }

    /**
     * 生成验证码挑战。
     *
     * @return array<string,int|string>
     */
    public function create_challenge() {
        $challenge_id = wp_generate_uuid4();
        $issued_at = time();

        try {
            $signature = bin2hex( random_bytes( 16 ) );
        } catch ( \Exception $e ) {
            $signature = wp_generate_password( 32, false, false );
        }

        $challenge_data = array(
            'issued_at'      => $issued_at,
            'signature_hash' => hash_hmac( 'sha256', $signature, wp_salt( 'auth' ) ),
            'ua_hash'        => $this->get_client_ua_hash(),
        );

        set_transient( $this->get_challenge_key( $challenge_id ), $challenge_data, $this->challenge_ttl );

        return array(
            'challenge_id'        => $challenge_id,
            'challenge_signature' => $signature,
            'challenge_issued'    => $issued_at,
            'expires_in'          => $this->challenge_ttl,
        );
    }

    /**
     * 验证主题内置滑动挑战。
     *
     * @param array<string,mixed> $payload 挑战载荷。
     * @return true|\WP_Error
     */
    public function verify_theme_challenge( $payload ) {
        $challenge_id = isset( $payload['challenge_id'] ) ? sanitize_text_field( wp_unslash( (string) $payload['challenge_id'] ) ) : '';
        $challenge_signature = isset( $payload['challenge_signature'] ) ? sanitize_text_field( wp_unslash( (string) $payload['challenge_signature'] ) ) : '';
        $challenge_issued = isset( $payload['challenge_issued'] ) ? (int) $payload['challenge_issued'] : 0;
        $drag_duration = isset( $payload['drag_duration'] ) ? (int) $payload['drag_duration'] : 0;
        $move_count = isset( $payload['move_count'] ) ? (int) $payload['move_count'] : 0;
        $drag_distance = isset( $payload['drag_distance'] ) ? (int) $payload['drag_distance'] : 0;

        if ( '' === $challenge_id || '' === $challenge_signature || $challenge_issued <= 0 ) {
            return new \WP_Error( 'captcha_invalid_payload', __( '验证失败，请重试', 'developer-starter' ) );
        }

        $challenge_key = $this->get_challenge_key( $challenge_id );
        $challenge_data = get_transient( $challenge_key );

        delete_transient( $challenge_key );

        if ( ! is_array( $challenge_data ) ) {
            return new \WP_Error( 'captcha_expired', __( '验证已过期，请重新滑动', 'developer-starter' ) );
        }

        $current_ua_hash = $this->get_client_ua_hash();
        $expected_signature_hash = isset( $challenge_data['signature_hash'] ) ? (string) $challenge_data['signature_hash'] : '';

        if ( $challenge_issued !== (int) $challenge_data['issued_at'] ) {
            return new \WP_Error( 'captcha_invalid_payload', __( '验证失败，请重试', 'developer-starter' ) );
        }

        if ( ( time() - (int) $challenge_data['issued_at'] ) > $this->challenge_ttl ) {
            return new \WP_Error( 'captcha_expired', __( '验证已过期，请重新滑动', 'developer-starter' ) );
        }

        if ( ! hash_equals( $expected_signature_hash, hash_hmac( 'sha256', $challenge_signature, wp_salt( 'auth' ) ) ) ) {
            return new \WP_Error( 'captcha_invalid_signature', __( '验证失败，请重试', 'developer-starter' ) );
        }

        $stored_ua_hash = isset( $challenge_data['ua_hash'] ) ? (string) $challenge_data['ua_hash'] : '';
        if ( '' === $stored_ua_hash || ! hash_equals( $stored_ua_hash, $current_ua_hash ) ) {
            return new \WP_Error( 'captcha_invalid_ua', __( '验证失败，请重试', 'developer-starter' ) );
        }

        if ( $drag_duration < 250 || $drag_duration > 20000 || $move_count < 3 || $drag_distance < 60 ) {
            return new \WP_Error( 'captcha_behavior_invalid', __( '请完成滑动验证', 'developer-starter' ) );
        }

        return true;
    }

    /**
     * 消费滑动验证码令牌（一次性，支持跨 IP 场景）。
     *
     * @param string $token 验证令牌。
     * @return bool
     */
    public function consume_token( $token ) {
        $token = sanitize_text_field( wp_unslash( (string) $token ) );
        if ( '' === $token ) {
            return false;
        }

        $ip = developer_starter_resolve_client_ip( $this->client_ip_callback );
        $global_key = 'ds_captcha_' . $token;
        $ip_key = 'ds_captcha_' . $token . '_' . md5( $ip );

        if ( get_transient( $global_key ) ) {
            delete_transient( $global_key );
            delete_transient( $ip_key );
            return true;
        }

        if ( get_transient( $ip_key ) ) {
            delete_transient( $ip_key );
            delete_transient( $global_key );
            return true;
        }

        return false;
    }

    /**
     * 获取主题设置项。
     *
     * @param string $key 选项键名。
     * @param mixed  $default 默认值。
     * @return mixed
     */
    private function get_option( $key, $default = '' ) {
        if ( is_callable( $this->option_callback ) ) {
            return call_user_func( $this->option_callback, $key, $default );
        }

        return function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( $key, $default )
            : $default;
    }

    /**
     * 按需加载阿里云 SDK。
     *
     * @return bool
     */
    private function load_aliyun_sdk() {
        if ( class_exists( OpenApiClient::class ) && class_exists( Config::class ) ) {
            return true;
        }

        $autoload = get_template_directory() . '/sms/vendor/autoload.php';
        if ( file_exists( $autoload ) ) {
            require_once $autoload;
        }

        return class_exists( OpenApiClient::class ) && class_exists( Config::class );
    }

    /**
     * 获取客户端 User-Agent 哈希。
     *
     * @return string
     */
    private function get_client_ua_hash() {
        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) : '';
        $ua = sanitize_text_field( substr( $ua, 0, 255 ) );
        return md5( $ua );
    }

    /**
     * 获取验证码挑战存储键。
     *
     * @param string $challenge_id 挑战 ID。
     * @return string
     */
    private function get_challenge_key( $challenge_id ) {
        return 'ds_captcha_challenge_' . sanitize_text_field( (string) $challenge_id );
    }
}
