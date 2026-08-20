<?php
/**
 * SMS Manager Class - 阿里云短信服务管理
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Darabonba\OpenApi\Models\Config;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SMS_Manager {

    /**
     * 验证码有效期（秒）
     */
    const CODE_EXPIRE = 300; // 5分钟

    /**
     * 验证码存储格式版本。
     */
    const CODE_STORAGE_VERSION = 2;

    /**
     * 发送间隔（秒）
     */
    const SEND_INTERVAL = 60; // 60秒

    /**
     * 验证失败最大次数
     */
    const VERIFY_MAX_ATTEMPTS = 6;

    /**
     * 验证失败统计窗口（秒）
     */
    const VERIFY_WINDOW = 600; // 10分钟

    /**
     * 验证失败锁定时间（秒）
     */
    const VERIFY_LOCK_SECONDS = 900; // 15分钟

    /**
     * 设备指纹最短长度
     */
    const DEVICE_FINGERPRINT_MIN_LENGTH = 12;

    /**
     * 设备指纹最长长度
     */
    const DEVICE_FINGERPRINT_MAX_LENGTH = 128;

    /**
     * 构造函数
     */
    public function __construct() {
        // 加载阿里云SDK
        $this->load_sdk();
        
        // 注册AJAX处理器
        add_action( 'wp_ajax_sms_send_code', array( $this, 'ajax_send_code' ) );
        add_action( 'wp_ajax_nopriv_sms_send_code', array( $this, 'ajax_send_code' ) );
        
        add_action( 'wp_ajax_sms_phone_login', array( $this, 'ajax_phone_login' ) );
        add_action( 'wp_ajax_nopriv_sms_phone_login', array( $this, 'ajax_phone_login' ) );
        
        add_action( 'wp_ajax_sms_phone_register', array( $this, 'ajax_phone_register' ) );
        add_action( 'wp_ajax_nopriv_sms_phone_register', array( $this, 'ajax_phone_register' ) );
        
        add_action( 'wp_ajax_sms_phone_reset_password', array( $this, 'ajax_phone_reset_password' ) );
        add_action( 'wp_ajax_nopriv_sms_phone_reset_password', array( $this, 'ajax_phone_reset_password' ) );
        
        add_action( 'wp_ajax_sms_bind_phone', array( $this, 'ajax_bind_phone' ) );
        add_action( 'wp_ajax_sms_unbind_phone', array( $this, 'ajax_unbind_phone' ) );
    }

    /**
     * 加载阿里云SDK
     */
    private function load_sdk() {
        $autoload = get_template_directory() . '/sms/vendor/autoload.php';
        if ( file_exists( $autoload ) ) {
            require_once $autoload;
        }
    }

    /**
     * 检查短信功能是否启用
     */
    public static function is_enabled() {
        return developer_starter_get_option( 'sms_enable', '' ) === '1';
    }

    /**
     * 检查是否默认手机号登录
     */
    public static function is_default_phone_login() {
        return developer_starter_get_option( 'sms_default_phone_login', '' ) === '1';
    }

    /**
     * 检查是否仅允许手机号登录
     */
    public static function is_phone_only() {
        if ( function_exists( 'developer_starter_is_sms_phone_only_effective' ) ) {
            return developer_starter_is_sms_phone_only_effective();
        }

        return developer_starter_get_option( 'sms_phone_only', '' ) === '1';
    }

    /**
     * 检查是否允许手机号注册
     */
    public static function is_phone_registration_allowed() {
        if ( function_exists( 'developer_starter_is_phone_registration_allowed' ) ) {
            return developer_starter_is_phone_registration_allowed();
        }

        return self::is_enabled();
    }

    /**
     * 验证手机号格式（中国手机号）
     */
    public static function validate_phone( $phone ) {
        $phone = preg_replace( '/[^0-9]/', '', $phone );
        // 去掉国际区号前缀
        if ( strpos( $phone, '86' ) === 0 && strlen( $phone ) === 13 ) {
            $phone = substr( $phone, 2 );
        }
        return preg_match( '/^1[3-9]\d{9}$/', $phone ) ? $phone : false;
    }

    /**
     * 生成6位数字验证码
     */
    public static function generate_code() {
        try {
            return str_pad( random_int( 0, 999999 ), 6, '0', STR_PAD_LEFT );
        } catch ( \Exception $e ) {
            // Fallback to mt_rand if random_int fails (unlikely on modern PHP)
            return str_pad( mt_rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );
        }
    }

    /**
     * 存储验证码
     */
    public static function store_code( $phone, $code, $expiration = null ) {
        $code = preg_replace( '/\D+/', '', (string) $code );
        if ( '' === $code ) {
            return;
        }
        $expiration = null === $expiration ? self::CODE_EXPIRE : max( 1, min( self::CODE_EXPIRE, absint( $expiration ) ) );

        set_transient(
            self::get_code_key( $phone ),
            array(
                'version'    => self::CODE_STORAGE_VERSION,
                'hash'       => wp_hash_password( $code ),
                'created_at' => time(),
            ),
            $expiration
        );
    }

    /**
     * 获取验证码 transient key。
     *
     * @param string $phone 手机号。
     * @return string
     */
    private static function get_code_key( $phone ) {
        return 'sms_code_' . $phone;
    }

    /**
     * 获取旧明文验证码剩余有效期，迁移时尽量不延长原过期时间。
     *
     * @param string $phone 手机号。
     * @return int
     */
    private static function get_code_remaining_ttl( $phone ) {
        if ( function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache() ) {
            return self::CODE_EXPIRE;
        }

        $timeout = function_exists( 'get_option' ) ? (int) get_option( '_transient_timeout_' . self::get_code_key( $phone ) ) : 0;
        if ( $timeout > time() ) {
            return max( 1, min( self::CODE_EXPIRE, $timeout - time() ) );
        }

        return self::CODE_EXPIRE;
    }

    /**
     * 将旧明文验证码 transient 迁移为哈希载荷。
     *
     * @param string $phone       手机号。
     * @param string $legacy_code 旧明文验证码。
     * @return string 规范化后的旧验证码，空字符串表示无效。
     */
    private static function migrate_legacy_code( $phone, $legacy_code ) {
        $legacy_code = preg_replace( '/\D+/', '', (string) $legacy_code );
        if ( '' === $legacy_code ) {
            return '';
        }

        self::store_code( $phone, $legacy_code, self::get_code_remaining_ttl( $phone ) );
        return $legacy_code;
    }

    /**
     * 获取存储的验证码载荷
     */
    public static function get_stored_code( $phone ) {
        return get_transient( self::get_code_key( $phone ) );
    }

    /**
     * 删除验证码
     */
    public static function delete_code( $phone ) {
        delete_transient( self::get_code_key( $phone ) );
    }

    /**
     * 验证验证码
     */
    public static function verify_code( $phone, $code ) {
        $stored_code = self::get_stored_code( $phone );
        if ( ! $stored_code ) {
            return false;
        }

        $code = preg_replace( '/\D+/', '', (string) $code );
        if ( '' === $code ) {
            return false;
        }

        if ( is_array( $stored_code ) ) {
            $stored_hash = isset( $stored_code['hash'] ) ? (string) $stored_code['hash'] : '';
            if ( '' !== $stored_hash && wp_check_password( $code, $stored_hash ) ) {
                return true;
            }

            // 兼容极早期可能保存为数组明文字段的缓存，首次读取即迁移为哈希格式。
            $legacy_code = isset( $stored_code['code'] ) ? self::migrate_legacy_code( $phone, $stored_code['code'] ) : '';
            if ( '' !== $legacy_code && hash_equals( $legacy_code, $code ) ) {
                return true;
            }

            return false;
        }

        // 兼容旧版本明文 transient，首次读取即迁移为哈希格式。
        $legacy_code = self::migrate_legacy_code( $phone, $stored_code );
        if ( '' !== $legacy_code && hash_equals( $legacy_code, $code ) ) {
            return true;
        }

        return false;
    }

    /**
     * 获取验证码校验失败相关 key
     */
    private function get_verify_keys( $phone, $context ) {
        $fingerprint = md5( $context . '|' . $phone . '|' . developer_starter_get_client_ip() . '|' . $this->get_device_fingerprint() );
        return array(
            'attempts' => 'sms_verify_attempts_' . $fingerprint,
            'lock'     => 'sms_verify_lock_' . $fingerprint,
        );
    }

    /**
     * 检查验证码是否处于锁定期
     */
    private function check_verify_lock( $phone, $context ) {
        $keys = $this->get_verify_keys( $phone, $context );
        $locked_until = (int) get_transient( $keys['lock'] );
        if ( $locked_until > time() ) {
            return sprintf(
                __( '验证码输入错误过多，请 %s 后再试', 'developer-starter' ),
                human_time_diff( time(), $locked_until )
            );
        }

        if ( $locked_until > 0 ) {
            delete_transient( $keys['lock'] );
            delete_transient( $keys['attempts'] );
        }

        return true;
    }

    /**
     * 记录验证码校验失败并返回提示语
     */
    private function record_verify_failure( $phone, $context ) {
        $keys = $this->get_verify_keys( $phone, $context );
        $attempts = (int) get_transient( $keys['attempts'] );
        $attempts++;

        if ( $attempts >= self::VERIFY_MAX_ATTEMPTS ) {
            $locked_until = time() + self::VERIFY_LOCK_SECONDS;
            set_transient( $keys['lock'], $locked_until, self::VERIFY_LOCK_SECONDS );
            delete_transient( $keys['attempts'] );

            return sprintf(
                __( '验证码输入错误过多，请 %s 后再试', 'developer-starter' ),
                human_time_diff( time(), $locked_until )
            );
        }

        set_transient( $keys['attempts'], $attempts, self::VERIFY_WINDOW );
        return __( '验证码错误或已过期', 'developer-starter' );
    }

    /**
     * 清理验证码校验失败计数
     */
    private function clear_verify_failures( $phone, $context ) {
        $keys = $this->get_verify_keys( $phone, $context );
        delete_transient( $keys['attempts'] );
        delete_transient( $keys['lock'] );
    }

    /**
     * 带失败限流的验证码校验
     */
    private function verify_code_with_rate_limit( $phone, $code, $context ) {
        $lock_check = $this->check_verify_lock( $phone, $context );
        if ( $lock_check !== true ) {
            return new \WP_Error( 'sms_verify_locked', $lock_check );
        }

        if ( ! self::verify_code( $phone, $code ) ) {
            return new \WP_Error( 'sms_verify_invalid', $this->record_verify_failure( $phone, $context ) );
        }

        $this->clear_verify_failures( $phone, $context );
        return true;
    }

    /**
     * 消费滑动验证码令牌（一次性，支持跨 IP 场景）
     */
    private function consume_captcha_token( $token ) {
        $token = sanitize_text_field( wp_unslash( (string) $token ) );
        if ( $token === '' ) {
            return false;
        }

        $ip = developer_starter_get_client_ip();
        $global_key = 'ds_captcha_' . $token;
        $ip_key = 'ds_captcha_' . $token . '_' . md5( $ip );

        if ( get_transient( $global_key ) ) {
            delete_transient( $global_key );
            delete_transient( $ip_key );
            return true;
        }

        // 兼容历史 token（老版本仅按 IP 存储）
        if ( get_transient( $ip_key ) ) {
            delete_transient( $ip_key );
            delete_transient( $global_key );
            return true;
        }

        return false;
    }

    /**
     * 规范化设备指纹
     */
    private static function normalize_device_fingerprint_value( $value ) {
        $value = sanitize_text_field( (string) $value );
        $value = trim( strtolower( $value ) );
        if ( $value === '' ) {
            return '';
        }
        if ( strlen( $value ) < self::DEVICE_FINGERPRINT_MIN_LENGTH ) {
            return '';
        }
        if ( strlen( $value ) > self::DEVICE_FINGERPRINT_MAX_LENGTH ) {
            $value = substr( $value, 0, self::DEVICE_FINGERPRINT_MAX_LENGTH );
        }
        if ( ! preg_match( '/^[a-z0-9._:-]+$/', $value ) ) {
            return '';
        }

        return $value;
    }

    /**
     * 获取设备指纹（优先取前端上传，其次 Cookie，最后回退到 UA 指纹）
     */
    private function get_device_fingerprint() {
        $raw_post = isset( $_POST['device_fingerprint'] ) ? wp_unslash( $_POST['device_fingerprint'] ) : '';
        $fingerprint = self::normalize_device_fingerprint_value( $raw_post );
        if ( $fingerprint !== '' ) {
            return $fingerprint;
        }

        $raw_cookie = isset( $_COOKIE['ds_device_fingerprint'] ) ? wp_unslash( $_COOKIE['ds_device_fingerprint'] ) : '';
        $fingerprint = self::normalize_device_fingerprint_value( $raw_cookie );
        if ( $fingerprint !== '' ) {
            return $fingerprint;
        }

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) : '';
        $lang = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? wp_unslash( (string) $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) : '';
        $ua = sanitize_text_field( substr( $ua, 0, 255 ) );
        $lang = sanitize_text_field( substr( $lang, 0, 128 ) );
        if ( $ua === '' && $lang === '' ) {
            return 'ua_unknown';
        }

        return 'ua_' . substr( hash( 'sha256', $ua . '|' . $lang ), 0, 32 );
    }

    /**
     * 检查是否可以发送（60秒限制）
     */
    public static function can_send( $phone, $device_fingerprint = '' ) {
        if ( get_transient( 'sms_lock_' . $phone ) ) {
            return false;
        }

        $device_fingerprint = self::normalize_device_fingerprint_value( $device_fingerprint );
        if ( $device_fingerprint === '' ) {
            return true;
        }

        if ( get_transient( 'sms_lock_device_' . md5( $device_fingerprint ) ) ) {
            return false;
        }
        if ( get_transient( 'sms_lock_phone_device_' . md5( $phone . '|' . $device_fingerprint ) ) ) {
            return false;
        }

        return true;
    }

    /**
     * 设置发送锁
     */
    public static function set_send_lock( $phone, $device_fingerprint = '' ) {
        set_transient( 'sms_lock_' . $phone, 1, self::SEND_INTERVAL );

        $device_fingerprint = self::normalize_device_fingerprint_value( $device_fingerprint );
        if ( $device_fingerprint !== '' ) {
            set_transient( 'sms_lock_device_' . md5( $device_fingerprint ), 1, self::SEND_INTERVAL );
            set_transient( 'sms_lock_phone_device_' . md5( $phone . '|' . $device_fingerprint ), 1, self::SEND_INTERVAL );
        }
    }

    /**
     * 发送自定义模板短信（如通知类）
     */
    public function send_custom_sms( $phone, $template_code, $template_params = array() ) {
        $access_key_id = developer_starter_get_option( 'sms_access_key_id', '' );
        $access_key_secret = developer_starter_get_option( 'sms_access_key_secret', '' );
        $sign_name = developer_starter_get_option( 'sms_sign_name', '' );

        if ( empty( $access_key_id ) || empty( $access_key_secret ) || empty( $sign_name ) || empty( $template_code ) ) {
            developer_starter_log(
                'sms',
                'Configuration incomplete or template code missing.',
                array( 'template_code' => $template_code ),
                'warning'
            );
            return array(
                'success' => false,
                'message' => __( '短信服务配置不完整', 'developer-starter' ),
            );
        }

        try {
            $config = new Config([
                'accessKeyId' => $access_key_id,
                'accessKeySecret' => $access_key_secret,
                'endpoint' => 'dysmsapi.aliyuncs.com',
            ]);

            $client = new Dysmsapi( $config );

            $request = new SendSmsRequest([
                'phoneNumbers' => $phone,
                'signName' => $sign_name,
                'templateCode' => $template_code,
                'templateParam' => json_encode( $template_params, JSON_UNESCAPED_UNICODE ),
            ]);

            $response = $client->sendSms( $request );
            $body = $response->body;

            if ( $body->code === 'OK' ) {
                return array(
                    'success' => true,
                    'message' => __( '短信发送成功', 'developer-starter' ),
                );
            } else {
                developer_starter_log(
                    'sms',
                    'Custom send failed.',
                    array(
                        'provider_code'    => isset( $body->code ) ? $body->code : '',
                        'provider_message' => isset( $body->message ) ? $body->message : '',
                    ),
                    'error'
                );
                return array(
                    'success' => false,
                    'message' => $this->get_error_message( $body->code ),
                );
            }
        } catch ( \Exception $e ) {
            developer_starter_log( 'sms', 'Custom send exception.', array( 'exception' => $e ), 'error' );
            return array(
                'success' => false,
                'message' => __( '短信发送失败，请稍后重试', 'developer-starter' ),
            );
        }
    }


    /**
     * 发送短信验证码
     */
    public function send_sms( $phone, $code ) {
        $access_key_id = developer_starter_get_option( 'sms_access_key_id', '' );
        $access_key_secret = developer_starter_get_option( 'sms_access_key_secret', '' );
        $sign_name = developer_starter_get_option( 'sms_sign_name', '' );
        $template_code = developer_starter_get_option( 'sms_template_code', '' );

        if ( empty( $access_key_id ) || empty( $access_key_secret ) || empty( $sign_name ) || empty( $template_code ) ) {
            developer_starter_log( 'sms', 'Configuration incomplete.', array(), 'warning' );
            return array(
                'success' => false,
                'message' => __( '短信服务配置不完整', 'developer-starter' ),
            );
        }

        try {
            $config = new Config([
                'accessKeyId' => $access_key_id,
                'accessKeySecret' => $access_key_secret,
                'endpoint' => 'dysmsapi.aliyuncs.com',
            ]);

            $client = new Dysmsapi( $config );

            $request = new SendSmsRequest([
                'phoneNumbers' => $phone,
                'signName' => $sign_name,
                'templateCode' => $template_code,
                'templateParam' => json_encode( array( 'code' => $code ) ),
            ]);

            $response = $client->sendSms( $request );
            $body = $response->body;

            if ( $body->code === 'OK' ) {
                return array(
                    'success' => true,
                    'message' => __( '验证码发送成功', 'developer-starter' ),
                );
            } else {
                developer_starter_log(
                    'sms',
                    'Send failed.',
                    array(
                        'provider_code'    => isset( $body->code ) ? $body->code : '',
                        'provider_message' => isset( $body->message ) ? $body->message : '',
                    ),
                    'error'
                );
                return array(
                    'success' => false,
                    'message' => $this->get_error_message( $body->code ),
                );
            }
        } catch ( \Exception $e ) {
            developer_starter_log( 'sms', 'Send exception.', array( 'exception' => $e ), 'error' );
            return array(
                'success' => false,
                'message' => __( '短信发送失败，请稍后重试', 'developer-starter' ),
            );
        }
    }

    /**
     * 获取错误信息
     */
    private function get_error_message( $code ) {
        $messages = array(
            'isv.BUSINESS_LIMIT_CONTROL' => __( '发送频率过快，请稍后重试', 'developer-starter' ),
            'isv.MOBILE_NUMBER_ILLEGAL' => __( '手机号格式错误', 'developer-starter' ),
            'isv.SMS_SIGNATURE_SCENE_ILLEGAL' => __( '签名和模板类型不一致', 'developer-starter' ),
            'isv.TEMPLATE_MISSING_PARAMETERS' => __( '模板参数缺失', 'developer-starter' ),
            'isv.INVALID_PARAMETERS' => __( '参数错误', 'developer-starter' ),
            'isv.AMOUNT_NOT_ENOUGH' => __( '账户余额不足', 'developer-starter' ),
        );
        return isset( $messages[ $code ] ) ? $messages[ $code ] : sprintf( __( '短信发送失败（%s）', 'developer-starter' ), $code );
    }

    /**
     * AJAX: 发送验证码
     */
    public function ajax_send_code() {
        check_ajax_referer( 'sms_nonce', 'nonce' );

        if ( ! self::is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '短信服务未启用', 'developer-starter' ) ) );
        }

        // 游客场景：先消费验证码 token，确保任意失败后都必须重新验证
        if ( developer_starter_get_option( 'auth_captcha_enable', '' ) && ! is_user_logged_in() ) {
            $captcha = isset( $_POST['captcha_verified'] ) ? sanitize_text_field( wp_unslash( $_POST['captcha_verified'] ) ) : '';
            if ( ! $this->consume_captcha_token( $captcha ) ) {
                wp_send_json_error( array( 'message' => __( '请完成滑动验证', 'developer-starter' ) ) );
            }
        }

        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $phone = self::validate_phone( $phone );

        if ( ! $phone ) {
            wp_send_json_error( array( 'message' => __( '请输入正确的手机号', 'developer-starter' ) ) );
        }

        $ip = developer_starter_get_client_ip();
        $device_fingerprint = $this->get_device_fingerprint();

        if ( ! self::can_send( $phone, $device_fingerprint ) ) {
            wp_send_json_error( array( 'message' => __( '发送过于频繁，请稍后重试', 'developer-starter' ) ) );
        }

        // 检查IP限制
        if ( ! $this->check_ip_limit( $ip ) ) {
            wp_send_json_error( array( 'message' => __( '今日发送次数已达上限，请明天再试', 'developer-starter' ) ) );
        }

        // 检查设备限制
        if ( ! $this->check_device_limit( $device_fingerprint ) ) {
            wp_send_json_error( array( 'message' => __( '当前设备今日发送次数已达上限，请明天再试', 'developer-starter' ) ) );
        }

        $code = self::generate_code();
        $result = $this->send_sms( $phone, $code );

        if ( $result['success'] ) {
            self::store_code( $phone, $code );
            self::set_send_lock( $phone, $device_fingerprint );
            $this->increment_ip_count( $ip ); // 增加IP计数
            $this->increment_device_count( $device_fingerprint );
            wp_send_json_success( array( 'message' => __( '验证码已发送', 'developer-starter' ) ) );
        } else {
            wp_send_json_error( array( 'message' => $result['message'] ) );
        }
    }

    /**
     * AJAX: 手机号登录
     */
    public function ajax_phone_login() {
        check_ajax_referer( 'sms_nonce', 'nonce' );

        if ( ! self::is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '短信服务未启用', 'developer-starter' ) ) );
        }

        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
        $registration_code = isset( $_POST['registration_code'] ) ? sanitize_text_field( wp_unslash( $_POST['registration_code'] ) ) : '';
        $phone = self::validate_phone( $phone );

        if ( ! $phone ) {
            wp_send_json_error( array( 'message' => __( '请输入正确的手机号', 'developer-starter' ) ) );
        }

        if ( empty( $code ) ) {
            wp_send_json_error( array( 'message' => __( '请输入验证码', 'developer-starter' ) ) );
        }

        $verify_result = $this->verify_code_with_rate_limit( $phone, $code, 'phone_login' );
        if ( is_wp_error( $verify_result ) ) {
            wp_send_json_error( array( 'message' => $verify_result->get_error_message() ) );
        }

        // 验证成功，删除验证码
        self::delete_code( $phone );

        // 查找用户
        $user = $this->get_user_by_phone( $phone );

        if ( ! $user ) {
            if ( ! self::is_phone_registration_allowed() ) {
                wp_send_json_error( array( 'message' => __( '当前站点未开启手机号注册', 'developer-starter' ) ) );
            }

            if ( ! get_option( 'users_can_register' ) ) {
                wp_send_json_error( array( 'message' => __( '网站已关闭注册功能，请使用已注册账号登录', 'developer-starter' ) ) );
            }

            // 自动注册新用户
            $user_id = $this->create_user_by_phone( $phone, $registration_code, 'sms_login_auto_register' );
            if ( is_wp_error( $user_id ) ) {
                wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
            }
            $user = get_user_by( 'ID', $user_id );
        }

        // 登录用户
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );
        do_action( 'wp_login', $user->user_login, $user );

        $redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
        $redirect_to = wp_validate_redirect( $redirect_to, home_url() );

        wp_send_json_success( array(
            'message' => __( '登录成功', 'developer-starter' ),
            'redirect' => $redirect_to,
        ) );
    }

    /**
     * AJAX: 手机号注册
     */
    public function ajax_phone_register() {
        check_ajax_referer( 'sms_nonce', 'nonce' );

        if ( ! self::is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '短信服务未启用', 'developer-starter' ) ) );
        }
        if ( ! self::is_phone_registration_allowed() ) {
            wp_send_json_error( array( 'message' => __( '当前站点未开启手机号注册', 'developer-starter' ) ) );
        }

        if ( ! get_option( 'users_can_register' ) ) {
            wp_send_json_error( array( 'message' => __( '注册功能已关闭', 'developer-starter' ) ) );
        }

        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
        $registration_code = isset( $_POST['registration_code'] ) ? sanitize_text_field( wp_unslash( $_POST['registration_code'] ) ) : '';
        $phone = self::validate_phone( $phone );

        if ( ! $phone ) {
            wp_send_json_error( array( 'message' => __( '请输入正确的手机号', 'developer-starter' ) ) );
        }

        $verify_result = $this->verify_code_with_rate_limit( $phone, $code, 'phone_register' );
        if ( is_wp_error( $verify_result ) ) {
            wp_send_json_error( array( 'message' => $verify_result->get_error_message() ) );
        }

        // 检查手机号是否已注册
        if ( $this->get_user_by_phone( $phone ) ) {
            wp_send_json_error( array( 'message' => __( '该手机号已注册，请直接登录', 'developer-starter' ) ) );
        }

        // 删除验证码
        self::delete_code( $phone );

        // 创建用户
        $user_id = $this->create_user_by_phone( $phone, $registration_code, 'sms_register' );
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }

        // 自动登录
        $user = get_user_by( 'ID', $user_id );
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );
        do_action( 'wp_login', $user->user_login, $user );

        $redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
        $redirect_to = wp_validate_redirect( $redirect_to, home_url() );

        wp_send_json_success( array(
            'message' => __( '注册成功', 'developer-starter' ),
            'redirect' => $redirect_to,
        ) );
    }

    /**
     * AJAX: 手机号找回密码
     */
    public function ajax_phone_reset_password() {
        check_ajax_referer( 'sms_nonce', 'nonce' );

        if ( ! self::is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '短信服务未启用', 'developer-starter' ) ) );
        }

        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
        // 密码需要按原文设置，只做 wp_unslash()，不要做文本 sanitize。
        $new_password = isset( $_POST['new_password'] ) && ! is_array( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
        $phone = self::validate_phone( $phone );

        if ( ! $phone ) {
            wp_send_json_error( array( 'message' => __( '请输入正确的手机号', 'developer-starter' ) ) );
        }

        $verify_result = $this->verify_code_with_rate_limit( $phone, $code, 'phone_reset_password' );
        if ( is_wp_error( $verify_result ) ) {
            wp_send_json_error( array( 'message' => $verify_result->get_error_message() ) );
        }

        if ( strlen( $new_password ) < 6 ) {
            wp_send_json_error( array( 'message' => __( '密码长度至少6位', 'developer-starter' ) ) );
        }

        $user = $this->get_user_by_phone( $phone );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => __( '该手机号未绑定任何账户', 'developer-starter' ) ) );
        }

        // 删除验证码
        self::delete_code( $phone );

        // 重置密码
        wp_set_password( $new_password, $user->ID );
        
        // 销毁用户的所有会话，确保所有设备需要重新登录
        $sessions = \WP_Session_Tokens::get_instance( $user->ID );
        $sessions->destroy_all();

        wp_send_json_success( array( 'message' => __( '密码重置成功，请使用新密码登录', 'developer-starter' ) ) );
    }

    /**
     * AJAX: 绑定手机号
     */
    public function ajax_bind_phone() {
        check_ajax_referer( 'sms_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( '请先登录', 'developer-starter' ) ) );
        }

        if ( ! self::is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '短信服务未启用', 'developer-starter' ) ) );
        }

        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
        $phone = self::validate_phone( $phone );

        if ( ! $phone ) {
            wp_send_json_error( array( 'message' => __( '请输入正确的手机号', 'developer-starter' ) ) );
        }

        $verify_result = $this->verify_code_with_rate_limit( $phone, $code, 'bind_phone' );
        if ( is_wp_error( $verify_result ) ) {
            wp_send_json_error( array( 'message' => $verify_result->get_error_message() ) );
        }

        // 检查手机号是否被其他用户绑定
        $existing_user = $this->get_user_by_phone( $phone );
        if ( $existing_user && $existing_user->ID !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( '该手机号已被其他账户绑定', 'developer-starter' ) ) );
        }

        // 删除验证码
        self::delete_code( $phone );

        // 绑定手机号
        $user_id = get_current_user_id();
        update_user_meta( $user_id, 'qiling_phone', $phone );
        update_user_meta( $user_id, 'qiling_phone_verified', '1' );

        if ( function_exists( 'developer_starter_update_user_phone_location_meta' ) ) {
            developer_starter_update_user_phone_location_meta(
                $user_id,
                $phone,
                array(
                    'context' => 'sms_bind_phone',
                )
            );
        }

        wp_send_json_success( array( 'message' => __( '手机号绑定成功', 'developer-starter' ) ) );
    }

    /**
     * AJAX: 解绑手机号
     */
    public function ajax_unbind_phone() {
        check_ajax_referer( 'sms_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( '请先登录', 'developer-starter' ) ) );
        }

        // 安全验证：需要密码确认
        // 密码需要按原文校验，只做 wp_unslash()，不要做文本 sanitize。
        $password = isset( $_POST['password'] ) && ! is_array( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
        if ( empty( $password ) ) {
            wp_send_json_error( array( 'message' => __( '为了您的账户安全，请输入密码进行验证', 'developer-starter' ) ) );
        }

        $user = wp_get_current_user();
        if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
            wp_send_json_error( array( 'message' => __( '密码错误，无法解绑', 'developer-starter' ) ) );
        }

        $user_id = $user->ID;
        delete_user_meta( $user_id, 'qiling_phone' );
        delete_user_meta( $user_id, 'qiling_phone_verified' );
        if ( function_exists( 'developer_starter_clear_user_phone_location_meta' ) ) {
            developer_starter_clear_user_phone_location_meta( $user_id );
        }

        wp_send_json_success( array( 'message' => __( '手机号已解绑', 'developer-starter' ) ) );
    }

    /**
     * 根据手机号获取用户
     */
    public function get_user_by_phone( $phone ) {
        $users = get_users( array(
            'meta_key' => 'qiling_phone',
            'meta_value' => $phone,
            'number' => 1,
        ) );
        return ! empty( $users ) ? $users[0] : null;
    }

    /**
     * 通过手机号创建用户
     */
    public function create_user_by_phone( $phone, $registration_code = '', $register_source = 'sms_register' ) {
        $registration_code_log_id = 0;

        // 注册码校验（新用户创建前）
        if ( function_exists( 'qilingshop_registration_code_is_enabled' ) && qilingshop_registration_code_is_enabled() ) {
            if ( empty( $registration_code ) ) {
                return new \WP_Error( 'registration_code_required', __( '请输入注册码', 'developer-starter' ) );
            }

            if ( ! function_exists( 'qilingshop_registration_code_consume_for_registration' ) ) {
                return new \WP_Error( 'registration_code_service_missing', __( '注册码服务未加载，请联系管理员', 'developer-starter' ) );
            }

            $consume = qilingshop_registration_code_consume_for_registration( $registration_code, array(
                'register_source' => sanitize_key( $register_source ),
                'message'         => __( '手机号注册流程预占用', 'developer-starter' ),
            ) );

            if ( empty( $consume['success'] ) ) {
                $msg = ! empty( $consume['message'] ) ? $consume['message'] : __( '注册码校验失败', 'developer-starter' );
                return new \WP_Error( 'registration_code_invalid', $msg );
            }

            $registration_code_log_id = ! empty( $consume['log_id'] ) ? absint( $consume['log_id'] ) : 0;
        }

        // 生成用户名：su + 随机数
        $username = 'su' . substr( md5( $phone . time() ), 0, 8 );
        
        // 确保用户名唯一
        $base_username = $username;
        $counter = 1;
        while ( username_exists( $username ) ) {
            $username = $base_username . $counter;
            $counter++;
        }

        // 生成随机密码
        $password = wp_generate_password( 12, true, true );

        // 创建用户
        $user_id = wp_create_user( $username, $password );

        if ( is_wp_error( $user_id ) ) {
            if ( $registration_code_log_id > 0 && function_exists( 'qilingshop_registration_code_rollback_consumption' ) ) {
                qilingshop_registration_code_rollback_consumption( $registration_code_log_id, __( '手机号注册失败，注册码已回滚', 'developer-starter' ) );
            }
            return $user_id;
        }

        // 绑定手机号
        update_user_meta( $user_id, 'qiling_phone', $phone );
        update_user_meta( $user_id, 'qiling_phone_verified', '1' );

        if ( function_exists( 'developer_starter_update_user_phone_location_meta' ) ) {
            developer_starter_update_user_phone_location_meta(
                $user_id,
                $phone,
                array(
                    'context' => 'sms_register_create_user',
                )
            );
        }

        // 设置昵称为手机号（脱敏）
        $masked_phone = substr( $phone, 0, 3 ) . '****' . substr( $phone, -4 );
        wp_update_user( array(
            'ID' => $user_id,
            'display_name' => $masked_phone,
            'nickname' => $masked_phone,
        ) );

        if ( $registration_code_log_id > 0 && function_exists( 'qilingshop_registration_code_confirm_consumption' ) ) {
            qilingshop_registration_code_confirm_consumption( $registration_code_log_id, $user_id, __( '手机号注册成功，注册码生效', 'developer-starter' ) );
        }

        return $user_id;
    }

    /**
     * 获取用户的手机号（脱敏）
     */
    public static function get_masked_phone( $user_id ) {
        $phone = get_user_meta( $user_id, 'qiling_phone', true );
        if ( ! $phone ) {
            return '';
        }
        return substr( $phone, 0, 3 ) . '****' . substr( $phone, -4 );
    }

    /**
     * 获取用户的手机号（原始）
     */
    public static function get_phone( $user_id ) {
        return get_user_meta( $user_id, 'qiling_phone', true );
    }

    /**
     * 检查IP限制 (每日固定窗口)
     */
    private function check_ip_limit( $ip ) {
        $key = 'sms_ip_count_' . md5( $ip );
        $data = get_transient( $key );
        // 从主题设置读取每日IP限制，默认10次
        $daily_limit = (int) developer_starter_get_option( 'sms_daily_ip_limit', 10 );
        if ( $daily_limit < 1 ) {
            $daily_limit = 10;
        }
        
        if ( $data === false || ! is_array( $data ) ) {
            return true;
        }
        
        // 检查是否是同一天
        $today = gmdate( 'Y-m-d' );
        if ( isset( $data['date'] ) && $data['date'] === $today ) {
            return $data['count'] < $daily_limit;
        }
        
        // 新的一天，重置计数
        return true;
    }

    /**
     * 增加IP计数 (固定日窗口)
     */
    private function increment_ip_count( $ip ) {
        $key = 'sms_ip_count_' . md5( $ip );
        $data = get_transient( $key );
        $today = gmdate( 'Y-m-d' );
        
        if ( $data === false || ! is_array( $data ) || $data['date'] !== $today ) {
            // 新的一天或不存在，初始化
            $data = array(
                'count' => 1,
                'date'  => $today,
            );
        } else {
            // 同一天，增加计数
            $data['count']++;
        }
        
        // 设置过期时间为到明天子夜
        $midnight = strtotime( 'tomorrow midnight' ) - time();
        set_transient( $key, $data, $midnight );
    }

    /**
     * 检查设备限制（每日固定窗口）
     */
    private function check_device_limit( $device_fingerprint ) {
        $device_fingerprint = self::normalize_device_fingerprint_value( $device_fingerprint );
        if ( $device_fingerprint === '' ) {
            return true;
        }

        $key = 'sms_device_count_' . md5( $device_fingerprint );
        $data = get_transient( $key );
        $daily_limit = (int) developer_starter_get_option( 'sms_daily_device_limit', 20 );
        if ( $daily_limit < 1 ) {
            $daily_limit = 20;
        }

        if ( $data === false || ! is_array( $data ) ) {
            return true;
        }

        $today = gmdate( 'Y-m-d' );
        if ( isset( $data['date'] ) && $data['date'] === $today ) {
            return $data['count'] < $daily_limit;
        }

        return true;
    }

    /**
     * 增加设备计数（每日固定窗口）
     */
    private function increment_device_count( $device_fingerprint ) {
        $device_fingerprint = self::normalize_device_fingerprint_value( $device_fingerprint );
        if ( $device_fingerprint === '' ) {
            return;
        }

        $key = 'sms_device_count_' . md5( $device_fingerprint );
        $data = get_transient( $key );
        $today = gmdate( 'Y-m-d' );

        if ( $data === false || ! is_array( $data ) || $data['date'] !== $today ) {
            $data = array(
                'count' => 1,
                'date'  => $today,
            );
        } else {
            $data['count']++;
        }

        $midnight = strtotime( 'tomorrow midnight' ) - time();
        set_transient( $key, $data, $midnight );
    }
}
