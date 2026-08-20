<?php
/**
 * Auth Manager Class - 自定义注册登录管理
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auth_Manager {

    private $option_name = 'developer_starter_options';
    private $register_email_service = null;
    private $captcha_service = null;
    private $pages_service = null;
    private $flow_service = null;
    private $profile_service = null;
    const CAPTCHA_CHALLENGE_TTL = 180;
    const CAPTCHA_TOKEN_TTL = 300;
    const REGISTER_EMAIL_CODE_DIGITS = 6;
    const REGISTER_EMAIL_CODE_VERIFY_MAX_ATTEMPTS = 6;
    const REGISTER_EMAIL_CODE_VERIFY_LOCK_SECONDS = 900;

    /**
     * 获取传递给 hooks/filters 的安全请求载荷（脱敏后）。
     *
     * @return array
     */
    private function get_safe_hook_request_payload() {
        static $safe_payload = null;
        if ( is_array( $safe_payload ) ) {
            return $safe_payload;
        }

        $raw = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : array();
        if ( ! is_array( $raw ) ) {
            $safe_payload = array();
            return $safe_payload;
        }

        $safe_payload = $this->mask_sensitive_request_fields( $raw );
        return $safe_payload;
    }

    /**
     * 脱敏请求中的敏感字段，避免密码/重置密钥等进入外部 hooks 日志。
     *
     * @param mixed  $data     请求数据。
     * @param string $field_key 当前字段名。
     * @return mixed
     */
    private function mask_sensitive_request_fields( $data, $field_key = '' ) {
        if ( is_array( $data ) ) {
            $masked = array();
            foreach ( $data as $key => $value ) {
                $masked[ $key ] = $this->mask_sensitive_request_fields( $value, (string) $key );
            }
            return $masked;
        }

        $normalized_key = strtolower( sanitize_key( (string) $field_key ) );
        $sensitive_keys = array(
            'password',
            'password_confirm',
            'current_password',
            'new_password',
            'new_password_confirm',
            'user_password',
            'user_pass',
            'pass',
            'pass1',
            'pass2',
            'pwd',
            'reset_key',
            'key',
            'captcha_verified',
        );

        if ( in_array( $normalized_key, $sensitive_keys, true ) || strpos( $normalized_key, 'password' ) !== false ) {
            return '[REDACTED]';
        }

        return $data;
    }

    /**
     * 速率限制检查 (固定窗口算法)
     *
     * 使用固定时间窗口算法，避免滑动窗口导致的永久锁定问题。
     * 存储结构: array( 'count' => int, 'window_start' => int )
     *
     * @param string $action 动作标识
     * @param int $limit 限制次数
     * @param int $duration 时间窗口（秒）
     * @return bool|string true表示通过，string表示错误信息
     */
    private function check_rate_limit( $action, $limit, $duration ) {
        $ip = developer_starter_get_client_ip();
        $transient_key = 'ds_auth_limit_' . $action . '_' . md5( $ip );
        $lock_key = 'ds_auth_lock_' . $action . '_' . md5( $ip );
        $current_time = time();
        
        // 1. 首先检查是否处于锁定状态
        $lock_data = get_transient( $lock_key );
        if ( $lock_data !== false ) {
            $remaining = $lock_data - $current_time;
            if ( $remaining > 0 ) {
                return sprintf( 
                    __( '操作过于频繁，请%s后再试', 'developer-starter' ), 
                    human_time_diff( $current_time, $lock_data ) 
                );
            }
            // 锁定已过期，删除它
            delete_transient( $lock_key );
        }
        
        // 2. 获取当前窗口数据
        $data = get_transient( $transient_key );
        
        if ( $data === false || ! is_array( $data ) ) {
            // 新窗口开始
            $data = array(
                'count'        => 1,
                'window_start' => $current_time,
            );
            set_transient( $transient_key, $data, $duration );
            return true;
        }
        
        // 3. 检查窗口是否已过期（固定窗口）
        $window_end = $data['window_start'] + $duration;
        if ( $current_time >= $window_end ) {
            // 窗口已过期，重新开始
            $data = array(
                'count'        => 1,
                'window_start' => $current_time,
            );
            set_transient( $transient_key, $data, $duration );
            return true;
        }
        
        // 4. 在当前窗口内，检查是否超过限制
        if ( $data['count'] >= $limit ) {
            // 触发锁定，锁定时间 = 剩余窗口时间 + 额外惩罚时间
            $lock_until = $current_time + $duration;
            set_transient( $lock_key, $lock_until, $duration );
            delete_transient( $transient_key );
            return sprintf( 
                __( '操作过于频繁，请%s后再试', 'developer-starter' ), 
                human_time_diff( $current_time, $lock_until ) 
            );
        }
        
        // 5. 增加计数，保持原窗口起始时间
        $remaining_time = $window_end - $current_time;
        $data['count']++;
        set_transient( $transient_key, $data, $remaining_time );
        
        return true;
    }

    /**
     * 获取注册邮箱验证码服务。
     *
     * @return Auth_Register_Email_Service
     */
    private function get_register_email_service() {
        if ( null === $this->register_email_service ) {
            $this->register_email_service = new Auth_Register_Email_Service(
                array(
                    'option_callback'    => function ( $key, $default = '' ) {
                        return $this->get_option( $key, $default );
                    },
                    'client_ip_callback' => function () {
                        return developer_starter_get_client_ip();
                    },
                )
            );
        }

        return $this->register_email_service;
    }

    /**
     * 获取验证码服务。
     *
     * @return Auth_Captcha_Service
     */
    private function get_captcha_service() {
        if ( null === $this->captcha_service ) {
            $this->captcha_service = new Auth_Captcha_Service(
                array(
                    'option_callback'    => function ( $key, $default = '' ) {
                        return $this->get_option( $key, $default );
                    },
                    'client_ip_callback' => function () {
                        return developer_starter_get_client_ip();
                    },
                    'challenge_ttl'      => self::CAPTCHA_CHALLENGE_TTL,
                    'token_ttl'          => self::CAPTCHA_TOKEN_TTL,
                )
            );
        }

        return $this->captcha_service;
    }

    /**
     * 获取认证页面生命周期服务。
     *
     * @return Auth_Pages_Service
     */
    private function get_pages_service() {
        if ( null === $this->pages_service ) {
            $this->pages_service = new Auth_Pages_Service(
                array(
                    'option_callback' => function ( $key, $default = '' ) {
                        return $this->get_option( $key, $default );
                    },
                    'option_name'     => $this->option_name,
                )
            );
        }

        return $this->pages_service;
    }

    /**
     * 获取认证业务流程服务。
     *
     * @return Auth_Flow_Service
     */
    private function get_flow_service() {
        if ( null === $this->flow_service ) {
            $this->flow_service = new Auth_Flow_Service(
                array(
                    'option_callback'                     => function ( $key, $default = '' ) {
                        return $this->get_option( $key, $default );
                    },
                    'client_ip_callback'                  => function () {
                        return developer_starter_get_client_ip();
                    },
                    'safe_payload_callback'               => function () {
                        return $this->get_safe_hook_request_payload();
                    },
                    'rate_limit_callback'                 => function ( $action, $limit, $duration ) {
                        return $this->check_rate_limit( $action, $limit, $duration );
                    },
                    'captcha_consume_callback'            => function ( $token ) {
                        return $this->consume_captcha_token( $token );
                    },
                    'register_email_enabled_callback'     => function () {
                        return $this->is_register_email_code_enabled();
                    },
                    'verify_register_email_code_callback' => function ( $email, $code ) {
                        return $this->verify_register_email_code( $email, $code );
                    },
                    'clear_register_email_code_callback'  => function ( $email ) {
                        $this->clear_register_email_code( $email );
                    },
                )
            );
        }

        return $this->flow_service;
    }

    /**
     * 获取认证资料与响应服务。
     *
     * @return Auth_Profile_Service
     */
    private function get_profile_service() {
        if ( null === $this->profile_service ) {
            $this->profile_service = new Auth_Profile_Service(
                array(
                    'option_callback' => function ( $key, $default = '' ) {
                        return $this->get_option( $key, $default );
                    },
                )
            );
        }

        return $this->profile_service;
    }

    /**
     * 是否启用邮箱注册验证码。
     *
     * @return bool
     */
    private function is_register_email_code_enabled() {
        return $this->get_register_email_service()->is_enabled();
    }

    /**
     * 获取邮箱注册验证码有效期（分钟）。
     *
     * @return int
     */
    private function get_register_email_code_expire_minutes() {
        return $this->get_register_email_service()->get_expire_minutes();
    }

    /**
     * 获取邮箱注册验证码每日 IP 发送限制。
     *
     * @return int
     */
    private function get_register_email_code_daily_ip_limit() {
        return $this->get_register_email_service()->get_daily_ip_limit();
    }

    /**
     * 获取邮箱注册验证码单邮箱每日发送限制。
     *
     * @return int
     */
    private function get_register_email_code_daily_email_limit() {
        return $this->get_register_email_service()->get_daily_email_limit();
    }

    /**
     * 标准化邮箱字符串。
     *
     * @param string $email 邮箱。
     * @return string
     */
    private function normalize_register_email( $email ) {
        return $this->get_register_email_service()->normalize_email( $email );
    }

    /**
     * 检查 SMTP 配置是否完整。
     *
     * @return bool
     */
    private function is_register_email_smtp_ready() {
        return $this->get_register_email_service()->is_smtp_ready();
    }

    /**
     * 生成固定长度数字验证码。
     *
     * @return string
     */
    private function generate_register_email_code() {
        return $this->get_register_email_service()->generate_code();
    }

    /**
     * 设置邮箱验证码发送间隔锁。
     *
     * @param string $email 邮箱。
     * @param string $ip    IP 地址。
     * @return int 锁定截止时间戳。
     */
    private function set_register_email_send_lock( $email, $ip ) {
        return $this->get_register_email_service()->set_send_lock( $email, $ip );
    }

    /**
     * 检查邮箱验证码发送间隔锁。
     *
     * @param string $email 邮箱。
     * @param string $ip    IP 地址。
     * @return true|\WP_Error
     */
    private function check_register_email_send_lock( $email, $ip ) {
        return $this->get_register_email_service()->check_send_lock( $email, $ip );
    }

    /**
     * 检查每日发送限制。
     *
     * @param string $scope      作用域。
     * @param string $identifier 标识。
     * @param int    $limit      限额。
     * @return bool
     */
    private function check_register_email_daily_limit( $scope, $identifier, $limit ) {
        return $this->get_register_email_service()->check_daily_limit( $scope, $identifier, $limit );
    }

    /**
     * 增加每日发送计数。
     *
     * @param string $scope      作用域。
     * @param string $identifier 标识。
     * @return void
     */
    private function increment_register_email_daily_counter( $scope, $identifier ) {
        $this->get_register_email_service()->increment_daily_counter( $scope, $identifier );
    }

    /**
     * 存储邮箱注册验证码（哈希）。
     *
     * @param string $email 邮箱。
     * @param string $code  验证码。
     * @return void
     */
    private function store_register_email_code( $email, $code ) {
        $this->get_register_email_service()->store_code( $email, $code );
    }

    /**
     * 清理邮箱注册验证码相关状态。
     *
     * @param string $email 邮箱。
     * @return void
     */
    private function clear_register_email_code( $email ) {
        $this->get_register_email_service()->clear_code( $email );
    }

    /**
     * 校验邮箱注册验证码。
     *
     * @param string $email 邮箱。
     * @param string $code  验证码。
     * @return true|\WP_Error
     */
    private function verify_register_email_code( $email, $code ) {
        return $this->get_register_email_service()->verify_code( $email, $code );
    }

    /**
     * 发送注册邮箱验证码邮件。
     *
     * @param string $email 邮箱。
     * @param string $code  验证码。
     * @return true|\WP_Error
     */
    private function send_register_email_code_email( $email, $code ) {
        return $this->get_register_email_service()->send_code_email( $email, $code );
    }


    public function __construct() {
        // AJAX 处理 - 未登录用户
        add_action( 'wp_ajax_nopriv_developer_starter_login', array( $this, 'ajax_login' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_register', array( $this, 'ajax_register' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_send_register_email_code', array( $this, 'ajax_send_register_email_code' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_forgot_password', array( $this, 'ajax_forgot_password' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_reset_password', array( $this, 'ajax_reset_password' ) );
        
        // 刷新 Nonce
        add_action( 'wp_ajax_developer_starter_refresh_nonce', array( $this, 'ajax_refresh_nonce' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_refresh_nonce', array( $this, 'ajax_refresh_nonce' ) );
        
        // AJAX 处理 - 已登录用户（防止已登录用户访问认证页面时的问题）
        add_action( 'wp_ajax_developer_starter_login', array( $this, 'ajax_already_logged_in' ) );
        add_action( 'wp_ajax_developer_starter_register', array( $this, 'ajax_already_logged_in' ) );
        add_action( 'wp_ajax_developer_starter_send_register_email_code', array( $this, 'ajax_send_register_email_code' ) );
        
        // AJAX 获取用户状态（用于缓存兼容 - 已登录用户不受缓存影响）
        add_action( 'wp_ajax_developer_starter_user_status', array( $this, 'ajax_get_user_status' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_user_status', array( $this, 'ajax_get_user_status' ) );

        
        // 重定向默认登录页面
        add_action( 'init', array( $this, 'redirect_default_auth_pages' ) );
        add_filter( 'register_url', array( $this, 'filter_register_url' ), 10, 1 );
        
        // 兜底：已启用站点若缺少个人中心页面，在后台自动补建
        add_action( 'admin_init', array( $this, 'maybe_backfill_account_page' ) );
        
        // 监听页面保存，如果更新了会员中心页面，同步更新 Option ID
        add_action( 'save_post', array( $this, 'update_account_page_option' ), 10, 2 );
        
        // AJAX 头像上传
        add_action( 'wp_ajax_developer_starter_upload_avatar', array( $this, 'ajax_upload_avatar' ) );

        // AJAX 验证码获取令牌
        add_action( 'wp_ajax_nopriv_developer_starter_captcha_challenge', array( $this, 'ajax_captcha_challenge' ) );
        add_action( 'wp_ajax_developer_starter_captcha_challenge', array( $this, 'ajax_captcha_challenge' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_captcha_verify', array( $this, 'ajax_verify_captcha' ) );
        add_action( 'wp_ajax_developer_starter_captcha_verify', array( $this, 'ajax_verify_captcha' ) );
    }
    
    /**
     * 更新会员中心页面 ID Option
     */
    public function update_account_page_option( $post_id, $post ) {
        $this->get_pages_service()->update_account_page_option( $post_id, $post );
    }
    
    /**
     * 已登录用户尝试登录/注册时的响应
     */
    public function ajax_already_logged_in() {
        wp_send_json_success( $this->get_profile_service()->get_already_logged_in_payload() );
    }
    
    /**
     * AJAX 获取用户状态（用于缓存兼容）
     * 已登录用户不受任何缓存影响，始终返回真实状态
     */
    public function ajax_get_user_status() {
        $this->get_profile_service()->send_no_store_headers();
        $this->guard_public_ajax_rate_limit( 'auth_user_status', 60, 60 );
        wp_send_json_success( $this->get_profile_service()->get_user_status_payload() );
    }


    /**
     * 获取选项
     */
    private function get_option( $key, $default = '' ) {
        return developer_starter_get_option( $key, $default );
    }

    /**
     * 兼容消费滑动验证码令牌回调。
     *
     * @param mixed $token 令牌。
     * @return bool
     */
    private function consume_captcha_token( $token ) {
        return $this->get_captcha_service()->consume_token( $token );
    }

    /**
     * 发送禁止缓存响应头（用于认证相关 AJAX）
     */
    private function send_no_store_ajax_headers() {
        $this->get_profile_service()->send_no_store_headers();
    }

    /**
     * 统一公共认证 AJAX 限流。
     *
     * @param string $scope 作用域。
     * @param int    $max_requests 窗口请求数。
     * @param int    $window_seconds 窗口秒数。
     * @return void
     */
    private function guard_public_ajax_rate_limit( $scope, $max_requests, $window_seconds = 60 ) {
        if (
            function_exists( 'developer_starter_is_public_ajax_rate_limited' )
            && developer_starter_is_public_ajax_rate_limited( $scope, $max_requests, $window_seconds )
        ) {
            if ( function_exists( 'developer_starter_send_public_ajax_rate_limited' ) ) {
                developer_starter_send_public_ajax_rate_limited();
            }

            wp_send_json_error(
                array(
                    'message' => __( '请求过于频繁，请稍后再试', 'developer-starter' ),
                    'code'    => 'rate_limited',
                ),
                429
            );
        }
    }

    /**
     * 重定向默认登录注册页面
     */
    public function redirect_default_auth_pages() {
        $this->get_pages_service()->redirect_default_auth_pages();
    }

    /**
     * 将系统注册 URL 统一替换为主题注册页。
     *
     * @param string $register_url WordPress 默认注册 URL。
     * @return string
     */
    public function filter_register_url( $register_url ) {
        return $this->get_pages_service()->filter_register_url( $register_url );
    }

    /**
     * 自动创建认证页面
     */
    public function create_auth_pages() {
        $this->get_pages_service()->create_auth_pages();
    }

    /**
     * 兜底检查：缺少个人中心页面时自动补建。
     * 说明：用于兼容“主题已启用但历史版本未自动创建个人中心”的站点。
     */
    public function maybe_backfill_account_page() {
        $this->get_pages_service()->maybe_backfill_account_page();
    }

    /**
     * AJAX 登录
     */
    public function ajax_login() {
        check_ajax_referer( 'developer_starter_auth', 'nonce' );

        $result = $this->get_flow_service()->handle_login( $_POST );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX 发送邮箱注册验证码。
     */
    public function ajax_send_register_email_code() {
        $this->send_no_store_ajax_headers();
        check_ajax_referer( 'developer_starter_auth', 'nonce' );

        if ( is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( '您已登录，无需获取注册验证码', 'developer-starter' ) ) );
        }
        if ( ! $this->is_register_email_code_enabled() ) {
            wp_send_json_error( array( 'message' => __( '邮箱注册验证码未开启', 'developer-starter' ) ) );
        }
        if ( ! get_option( 'users_can_register' ) ) {
            wp_send_json_error( array( 'message' => __( '网站已关闭注册功能', 'developer-starter' ) ) );
        }
        if ( function_exists( 'developer_starter_is_email_registration_allowed' ) && ! developer_starter_is_email_registration_allowed() ) {
            wp_send_json_error( array( 'message' => __( '当前站点未开启邮箱注册', 'developer-starter' ) ) );
        }

        $email = isset( $_POST['email'] ) ? $this->normalize_register_email( wp_unslash( $_POST['email'] ) ) : '';
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( '请输入有效的邮箱地址', 'developer-starter' ) ) );
        }

        if ( function_exists( 'developer_starter_is_email_domain_allowed' ) && ! developer_starter_is_email_domain_allowed( $email ) ) {
            $allowed_suffixes = function_exists( 'developer_starter_get_email_domain_whitelist_text' )
                ? developer_starter_get_email_domain_whitelist_text( '、' )
                : '';
            $message = $allowed_suffixes !== ''
                ? sprintf( __( '当前仅支持以下邮箱后缀：%s', 'developer-starter' ), $allowed_suffixes )
                : __( '当前邮箱后缀不在允许范围内', 'developer-starter' );
            wp_send_json_error( array( 'message' => $message ) );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => __( '该邮箱已被注册，请直接登录', 'developer-starter' ) ) );
        }

        if ( $this->get_option( 'auth_captcha_enable', '' ) ) {
            $captcha = isset( $_POST['captcha_verified'] ) ? sanitize_text_field( wp_unslash( $_POST['captcha_verified'] ) ) : '';
            if ( ! $this->consume_captcha_token( $captcha ) ) {
                wp_send_json_error( array( 'message' => __( '请先完成验证，再获取验证码', 'developer-starter' ) ) );
            }
        }

        if ( ! $this->is_register_email_smtp_ready() ) {
            wp_send_json_error( array( 'message' => __( '邮箱服务未配置完整，请先在主题设置中完成 SMTP 配置', 'developer-starter' ) ) );
        }

        $rate_check = $this->check_rate_limit( 'register_email_code_send', 20, 3600 );
        if ( $rate_check !== true ) {
            wp_send_json_error( array( 'message' => $rate_check ) );
        }

        $ip = developer_starter_get_client_ip();
        $send_lock_check = $this->check_register_email_send_lock( $email, $ip );
        if ( is_wp_error( $send_lock_check ) ) {
            wp_send_json_error( array( 'message' => $send_lock_check->get_error_message() ) );
        }

        $daily_ip_limit = $this->get_register_email_code_daily_ip_limit();
        if ( ! $this->check_register_email_daily_limit( 'ip', $ip, $daily_ip_limit ) ) {
            wp_send_json_error( array( 'message' => __( '当前 IP 今日发送次数已达上限，请明天再试', 'developer-starter' ) ) );
        }

        $daily_email_limit = $this->get_register_email_code_daily_email_limit();
        if ( ! $this->check_register_email_daily_limit( 'email', $email, $daily_email_limit ) ) {
            wp_send_json_error( array( 'message' => __( '该邮箱今日验证码发送次数已达上限，请明天再试', 'developer-starter' ) ) );
        }

        $code = $this->generate_register_email_code();
        $send_result = $this->send_register_email_code_email( $email, $code );
        if ( is_wp_error( $send_result ) ) {
            wp_send_json_error( array( 'message' => $send_result->get_error_message() ) );
        }

        $this->store_register_email_code( $email, $code );
        $lock_until = $this->set_register_email_send_lock( $email, $ip );
        $this->increment_register_email_daily_counter( 'ip', $ip );
        $this->increment_register_email_daily_counter( 'email', $email );

        wp_send_json_success( array(
            'message'       => __( '验证码已发送，请查收邮箱（含垃圾邮箱）', 'developer-starter' ),
            'retry_after'   => max( 1, $lock_until - time() ),
            'expire_minutes'=> $this->get_register_email_code_expire_minutes(),
        ) );
    }

    /**
     * AJAX 注册
     */
    public function ajax_register() {
        check_ajax_referer( 'developer_starter_auth', 'nonce' );

        $result = $this->get_flow_service()->handle_register( $_POST );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX 找回密码
     */
    public function ajax_forgot_password() {
        check_ajax_referer( 'developer_starter_auth', 'nonce' );

        $result = $this->get_flow_service()->handle_forgot_password( $_POST );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX 重置密码
     */
    public function ajax_reset_password() {
        check_ajax_referer( 'developer_starter_auth', 'nonce' );

        $result = $this->get_flow_service()->handle_reset_password( $_POST );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }
    
    /**
     * AJAX 上传用户头像
     */
    public function ajax_upload_avatar() {
        $result = $this->get_profile_service()->handle_avatar_upload( $_POST, $_FILES );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX 刷新 Nonce
     */
    public function ajax_refresh_nonce() {
        $this->send_no_store_ajax_headers();
        $this->guard_public_ajax_rate_limit( 'auth_refresh_nonce', 30, 60 );
        wp_send_json_success( $this->get_profile_service()->get_refresh_nonce_payload() );
    }

    /**
     * AJAX: 获取滑动验证码挑战
     */
    public function ajax_captcha_challenge() {
        $this->send_no_store_ajax_headers();
        check_ajax_referer( 'developer_starter_auth', 'nonce' );

        $rate_check = $this->check_rate_limit( 'captcha_challenge', 60, 60 );
        if ( $rate_check !== true ) {
            wp_send_json_error( array( 'message' => $rate_check ) );
        }

        wp_send_json_success( $this->get_captcha_service()->create_challenge() );
    }

    /**
     * AJAX: 验证滑动验证码并生成令牌
     */
    public function ajax_verify_captcha() {
        $this->send_no_store_ajax_headers();
        check_ajax_referer( 'developer_starter_auth', 'nonce' );

        $rate_check = $this->check_rate_limit( 'captcha_verify', 60, 60 );
        if ( $rate_check !== true ) {
            wp_send_json_error( array( 'message' => $rate_check ) );
        }

        if ( 'aliyun' === $this->get_captcha_service()->get_provider() ) {
            $captcha_verify_param = isset( $_POST['captcha_verify_param'] ) ? wp_unslash( (string) $_POST['captcha_verify_param'] ) : '';
            $scene_id = isset( $_POST['scene'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['scene'] ) ) : '';
            $verify_result = $this->get_captcha_service()->verify_aliyun( $captcha_verify_param, $scene_id );
            if ( is_wp_error( $verify_result ) ) {
                wp_send_json_error( array( 'message' => $verify_result->get_error_message() ) );
            }

            wp_send_json_success( $this->get_captcha_service()->issue_token() );
        }

        $verify_result = $this->get_captcha_service()->verify_theme_challenge(
            array(
                'challenge_id'        => isset( $_POST['challenge_id'] ) ? sanitize_text_field( wp_unslash( $_POST['challenge_id'] ) ) : '',
                'challenge_signature' => isset( $_POST['challenge_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['challenge_signature'] ) ) : '',
                'challenge_issued'    => isset( $_POST['challenge_issued'] ) ? absint( wp_unslash( $_POST['challenge_issued'] ) ) : 0,
                'drag_duration'       => isset( $_POST['drag_duration'] ) ? absint( wp_unslash( $_POST['drag_duration'] ) ) : 0,
                'move_count'          => isset( $_POST['move_count'] ) ? absint( wp_unslash( $_POST['move_count'] ) ) : 0,
                'drag_distance'       => isset( $_POST['drag_distance'] ) ? absint( wp_unslash( $_POST['drag_distance'] ) ) : 0,
            )
        );

        if ( is_wp_error( $verify_result ) ) {
            wp_send_json_error( array( 'message' => $verify_result->get_error_message() ) );
        }

        wp_send_json_success( $this->get_captcha_service()->issue_token() );
    }
}
