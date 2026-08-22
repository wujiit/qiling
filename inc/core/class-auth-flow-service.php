<?php
/**
 * Auth flow service.
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auth_Flow_Service {

    /**
     * @var callable|null
     */
    private $option_callback;

    /**
     * @var callable|null
     */
    private $client_ip_callback;

    /**
     * @var callable|null
     */
    private $safe_payload_callback;

    /**
     * @var callable|null
     */
    private $rate_limit_callback;

    /**
     * @var callable|null
     */
    private $captcha_consume_callback;

    /**
     * @var callable|null
     */
    private $register_email_enabled_callback;

    /**
     * @var callable|null
     */
    private $verify_register_email_code_callback;

    /**
     * @var callable|null
     */
    private $clear_register_email_code_callback;

    /**
     * @param array<string,mixed> $args 配置项。
     */
    public function __construct( $args = array() ) {
        $callback_keys = array(
            'option_callback',
            'client_ip_callback',
            'safe_payload_callback',
            'rate_limit_callback',
            'captcha_consume_callback',
            'register_email_enabled_callback',
            'verify_register_email_code_callback',
            'clear_register_email_code_callback',
        );

        foreach ( $callback_keys as $key ) {
            if ( isset( $args[ $key ] ) && is_callable( $args[ $key ] ) ) {
                $this->{$key} = $args[ $key ];
            }
        }
    }

    /**
     * 处理登录流程。
     *
     * @param array<string,mixed> $request 请求数据。
     * @return array<string,mixed>|\WP_Error
     */
    public function handle_login( $request ) {
        $raw_username = isset( $request['username'] ) ? trim( wp_unslash( (string) $request['username'] ) ) : '';
        $username = '' !== $raw_username ? sanitize_user( $raw_username ) : '';
        $password = isset( $request['password'] ) ? $request['password'] : '';
        $remember = isset( $request['remember'] ) && $request['remember'] === 'true';

        do_action( 'developer_starter_auth_before_login', $username, $remember, developer_starter_resolve_client_ip( $this->client_ip_callback ), $this->get_safe_hook_request_payload() );

        $captcha_check = $this->consume_captcha_if_required( $request );
        if ( is_wp_error( $captcha_check ) ) {
            return $captcha_check;
        }

        if ( '' === $username || '' === (string) $password ) {
            return new \WP_Error( 'login_required_fields_missing', __( '请填写用户名和密码', 'developer-starter' ) );
        }

        $rate_check = $this->check_rate_limit( 'login', 5, 60 );
        if ( $rate_check !== true ) {
            return new \WP_Error( 'login_rate_limited', (string) $rate_check );
        }

        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        );
        $credentials = apply_filters( 'developer_starter_auth_login_credentials', $credentials, $this->get_safe_hook_request_payload() );
        $user = wp_signon( $credentials );

        if ( is_wp_error( $user ) ) {
            do_action( 'developer_starter_auth_login_failed', $username, $user, developer_starter_resolve_client_ip( $this->client_ip_callback ) );
            return new \WP_Error( 'login_failed', __( '用户名或密码错误', 'developer-starter' ) );
        }

        $redirect = $this->get_option( 'login_redirect_url', '' );
        if ( empty( $redirect ) ) {
            $redirect_to = isset( $request['redirect_to'] ) ? esc_url_raw( wp_unslash( (string) $request['redirect_to'] ) ) : '';
            $redirect = wp_validate_redirect( $redirect_to, home_url() );
        }
        $redirect = apply_filters( 'developer_starter_auth_login_redirect', $redirect, $user, $this->get_safe_hook_request_payload() );

        do_action( 'developer_starter_auth_login_success', $user, $redirect, developer_starter_resolve_client_ip( $this->client_ip_callback ) );

        return array(
            'message'  => __( '登录成功，正在跳转...', 'developer-starter' ),
            'redirect' => $redirect,
        );
    }

    /**
     * 处理注册流程。
     *
     * @param array<string,mixed> $request 请求数据。
     * @return array<string,mixed>|\WP_Error
     */
    public function handle_register( $request ) {
        if ( ! get_option( 'users_can_register' ) ) {
            return new \WP_Error( 'registration_closed', __( '网站已关闭注册功能', 'developer-starter' ) );
        }
        if ( function_exists( 'developer_starter_is_email_registration_allowed' ) && ! developer_starter_is_email_registration_allowed() ) {
            return new \WP_Error( 'registration_email_disabled', __( '当前站点未开启邮箱注册', 'developer-starter' ) );
        }

        $raw_username = isset( $request['username'] ) ? trim( wp_unslash( (string) $request['username'] ) ) : '';
        $username = '' !== $raw_username ? sanitize_user( $raw_username ) : '';
        $email = isset( $request['email'] ) ? sanitize_email( wp_unslash( (string) $request['email'] ) ) : '';
        $email_code = isset( $request['email_code'] ) ? sanitize_text_field( wp_unslash( (string) $request['email_code'] ) ) : '';
        $password = isset( $request['password'] ) ? $request['password'] : '';
        $password_confirm = isset( $request['password_confirm'] ) ? $request['password_confirm'] : '';
        $registration_code = isset( $request['registration_code'] ) ? sanitize_text_field( wp_unslash( (string) $request['registration_code'] ) ) : '';
        $registration_code_log_id = 0;
        $register_email_code_enabled = $this->is_register_email_code_enabled();

        do_action( 'developer_starter_auth_before_register', $username, $email, developer_starter_resolve_client_ip( $this->client_ip_callback ), $this->get_safe_hook_request_payload() );

        if ( ! $register_email_code_enabled ) {
            $captcha_check = $this->consume_captcha_if_required( $request );
            if ( is_wp_error( $captcha_check ) ) {
                return $captcha_check;
            }
        }

        if ( '' === $username || '' === $email || '' === (string) $password ) {
            return new \WP_Error( 'register_required_fields_missing', __( '请填写所有必填项', 'developer-starter' ) );
        }

        $rate_check = $this->check_rate_limit( 'register', 3, 3600 );
        if ( $rate_check !== true ) {
            return new \WP_Error( 'register_rate_limited', (string) $rate_check );
        }

        if ( strlen( $username ) < 3 ) {
            return new \WP_Error( 'register_username_short', __( '用户名至少需要3个字符', 'developer-starter' ) );
        }

        $username_policy_check = $this->validate_register_username_chinese_policy( $username, $raw_username );
        if ( is_wp_error( $username_policy_check ) ) {
            return $username_policy_check;
        }

        if ( ! is_email( $email ) ) {
            return new \WP_Error( 'register_invalid_email', __( '请输入有效的邮箱地址', 'developer-starter' ) );
        }

        if ( function_exists( 'developer_starter_is_email_domain_allowed' ) && ! developer_starter_is_email_domain_allowed( $email ) ) {
            $allowed_suffixes = function_exists( 'developer_starter_get_email_domain_whitelist_text' )
                ? developer_starter_get_email_domain_whitelist_text( '、' )
                : '';

            $message = $allowed_suffixes !== ''
                ? sprintf( __( '当前仅支持以下邮箱后缀：%s', 'developer-starter' ), $allowed_suffixes )
                : __( '当前邮箱后缀不在允许范围内', 'developer-starter' );

            return new \WP_Error( 'register_email_domain_disallowed', $message );
        }

        if ( $register_email_code_enabled ) {
            $verify_email_code = $this->verify_register_email_code( $email, $email_code );
            if ( is_wp_error( $verify_email_code ) ) {
                return $verify_email_code;
            }
        }

        if ( $password !== $password_confirm ) {
            return new \WP_Error( 'register_password_mismatch', __( '两次输入的密码不一致', 'developer-starter' ) );
        }

        $strength = $this->get_option( 'password_strength', 'medium' );
        $strength_check = $this->check_password_strength( $password, $strength );
        if ( ! $strength_check['valid'] ) {
            return new \WP_Error( 'register_password_strength_failed', (string) $strength_check['message'] );
        }

        if ( $this->get_option( 'register_agreement_enable', '' ) ) {
            $agreement = isset( $request['agreement'] ) ? $request['agreement'] : '';
            if ( empty( $agreement ) ) {
                return new \WP_Error( 'register_agreement_required', __( '请阅读并同意用户服务协议', 'developer-starter' ) );
            }
        }

        if ( username_exists( $username ) || email_exists( $email ) ) {
            return new \WP_Error( 'register_user_exists', __( '该用户名或邮箱已被注册', 'developer-starter' ) );
        }

        if ( function_exists( 'qilingshop_registration_code_is_enabled' ) && qilingshop_registration_code_is_enabled() ) {
            if ( '' === $registration_code ) {
                return new \WP_Error( 'register_code_required', __( '请输入注册码', 'developer-starter' ) );
            }

            if ( ! function_exists( 'qilingshop_registration_code_consume_for_registration' ) ) {
                return new \WP_Error( 'register_code_service_missing', __( '注册码服务未加载，请联系管理员', 'developer-starter' ) );
            }

            $consume = qilingshop_registration_code_consume_for_registration(
                $registration_code,
                array(
                    'register_source' => 'auth_ajax',
                    'message'         => __( '账号注册流程预占用', 'developer-starter' ),
                )
            );

            if ( empty( $consume['success'] ) ) {
                $msg = ! empty( $consume['message'] ) ? $consume['message'] : __( '注册码校验失败', 'developer-starter' );
                return new \WP_Error( 'register_code_invalid', $msg );
            }

            $registration_code_log_id = ! empty( $consume['log_id'] ) ? absint( $consume['log_id'] ) : 0;
        }

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            if ( $registration_code_log_id > 0 && function_exists( 'qilingshop_registration_code_rollback_consumption' ) ) {
                qilingshop_registration_code_rollback_consumption( $registration_code_log_id, __( '账号注册失败，注册码已回滚', 'developer-starter' ) );
            }
            do_action( 'developer_starter_auth_register_failed', $username, $email, $user_id, developer_starter_resolve_client_ip( $this->client_ip_callback ) );
            return new \WP_Error( 'register_failed', sprintf( __( '注册失败：%s', 'developer-starter' ), $user_id->get_error_message() ) );
        }

        if ( $registration_code_log_id > 0 && function_exists( 'qilingshop_registration_code_confirm_consumption' ) ) {
            qilingshop_registration_code_confirm_consumption( $registration_code_log_id, $user_id, __( '账号注册成功，注册码生效', 'developer-starter' ) );
        }
        if ( $register_email_code_enabled ) {
            $this->clear_register_email_code( $email );
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        $redirect = $this->get_option( 'register_redirect_url', '' );
        if ( empty( $redirect ) ) {
            $redirect_to = isset( $request['redirect_to'] ) ? esc_url_raw( wp_unslash( (string) $request['redirect_to'] ) ) : '';
            $redirect = wp_validate_redirect( $redirect_to, home_url() );
        }
        $redirect = apply_filters( 'developer_starter_auth_register_redirect', $redirect, $user_id, $this->get_safe_hook_request_payload() );

        do_action( 'developer_starter_auth_register_success', $user_id, $redirect, developer_starter_resolve_client_ip( $this->client_ip_callback ) );

        return array(
            'message'  => __( '注册成功，正在跳转...', 'developer-starter' ),
            'redirect' => $redirect,
        );
    }

    /**
     * 处理找回密码流程。
     *
     * @param array<string,mixed> $request 请求数据。
     * @return array<string,mixed>|\WP_Error
     */
    public function handle_forgot_password( $request ) {
        $email = isset( $request['email'] ) ? sanitize_email( wp_unslash( (string) $request['email'] ) ) : '';
        do_action( 'developer_starter_auth_before_forgot_password', $email, developer_starter_resolve_client_ip( $this->client_ip_callback ), $this->get_safe_hook_request_payload() );

        $captcha_check = $this->consume_captcha_if_required( $request );
        if ( is_wp_error( $captcha_check ) ) {
            return $captcha_check;
        }

        if ( '' === $email || ! is_email( $email ) ) {
            return new \WP_Error( 'forgot_password_invalid_email', __( '请输入有效的邮箱地址', 'developer-starter' ) );
        }

        $rate_check = $this->check_rate_limit( 'forgot_pass', 3, 3600 );
        if ( $rate_check !== true ) {
            return new \WP_Error( 'forgot_password_rate_limited', (string) $rate_check );
        }

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            return array( 'message' => __( '如果该邮箱已注册，您将收到重置密码的邮件', 'developer-starter' ) );
        }

        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            return new \WP_Error( 'forgot_password_key_failed', __( '发送重置邮件失败，请稍后再试', 'developer-starter' ) );
        }

        $reset_url = $this->build_password_reset_url( $user, $key );

        $site_name = get_bloginfo( 'name' );
        $subject = sprintf( __( '[%s] 密码重置请求', 'developer-starter' ), $site_name );
        $message = sprintf( __( '您好，%s！', 'developer-starter' ), $user->display_name ) . "\n\n";
        $message .= __( "我们收到了您的密码重置请求。如果这不是您本人的操作，请忽略此邮件。\n\n", 'developer-starter' );
        $message .= __( "点击以下链接重置您的密码：\n", 'developer-starter' );
        $message .= $reset_url . "\n\n";
        $message .= __( "此链接将在24小时后失效。\n\n", 'developer-starter' );
        $message .= sprintf( __( '—— %s', 'developer-starter' ), $site_name );
        $subject = apply_filters( 'developer_starter_auth_forgot_password_subject', $subject, $user );
        $message = apply_filters( 'developer_starter_auth_forgot_password_message', $message, $user, $reset_url );

        $headers = array();
        if ( function_exists( 'developer_starter_build_html_email_template' ) ) {
            $html_message = developer_starter_build_html_email_template(
                array(
                    'title'       => __( '密码重置请求', 'developer-starter' ),
                    'intro'       => $message,
                    'lines'       => array(
                        __( '账号', 'developer-starter' )     => $user->user_login,
                        __( '重置链接', 'developer-starter' ) => $reset_url,
                        __( '有效期', 'developer-starter' )   => __( '24 小时', 'developer-starter' ),
                    ),
                    'button_text' => __( '立即重置密码', 'developer-starter' ),
                    'button_url'  => $reset_url,
                    'notice'      => __( '若按钮无法点击，请复制上方链接到浏览器打开。', 'developer-starter' ),
                )
            );
            $html_message = apply_filters( 'developer_starter_auth_forgot_password_html_message', $html_message, $user, $reset_url, $message );
            if ( is_string( $html_message ) && trim( $html_message ) !== '' ) {
                $message = $html_message;
                $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            }
        }

        $sent = wp_mail( $email, $subject, $message, $headers );

        if ( $sent ) {
            do_action( 'developer_starter_auth_forgot_password_sent', $user, $email, $reset_url );
            return array( 'message' => __( '重置密码邮件已发送，请查收您的邮箱', 'developer-starter' ) );
        }

        return new \WP_Error( 'forgot_password_mail_failed', __( '邮件发送失败，请稍后再试', 'developer-starter' ) );
    }

    /**
     * 构造密码重置链接，避免找回密码页缺失时发送空链接。
     *
     * @param \WP_User $user 用户对象。
     * @param string   $key  密码重置 key。
     * @return string
     */
    private function build_password_reset_url( $user, $key ) {
        $reset_page_id = absint( $this->get_option( 'forgot_password_page_id', '' ) );
        if ( $reset_page_id > 0 ) {
            $reset_page_url = get_permalink( $reset_page_id );
            if ( is_string( $reset_page_url ) && '' !== $reset_page_url ) {
                return add_query_arg(
                    array(
                        'action' => 'reset',
                        'key'    => $key,
                        'login'  => $user->user_login,
                    ),
                    $reset_page_url
                );
            }
        }

        $core_reset_url = network_site_url(
            'wp-login.php?action=rp&key=' . rawurlencode( (string) $key ) . '&login=' . rawurlencode( (string) $user->user_login ),
            'login'
        );
        if ( is_string( $core_reset_url ) && '' !== $core_reset_url ) {
            return $core_reset_url;
        }

        $lostpassword_url = wp_lostpassword_url();
        if ( is_string( $lostpassword_url ) && '' !== $lostpassword_url ) {
            return $lostpassword_url;
        }

        $login_page_id = absint( $this->get_option( 'login_page_id', '' ) );
        if ( $login_page_id > 0 ) {
            $login_page_url = get_permalink( $login_page_id );
            if ( is_string( $login_page_url ) && '' !== $login_page_url ) {
                return $login_page_url;
            }
        }

        return wp_login_url();
    }

    /**
     * 处理重置密码流程。
     *
     * @param array<string,mixed> $request 请求数据。
     * @return array<string,mixed>|\WP_Error
     */
    public function handle_reset_password( $request ) {
        $key = isset( $request['key'] ) ? sanitize_text_field( wp_unslash( (string) $request['key'] ) ) : '';
        $login = isset( $request['login'] ) ? sanitize_user( wp_unslash( (string) $request['login'] ) ) : '';
        $password = isset( $request['password'] ) ? $request['password'] : '';
        $password_confirm = isset( $request['password_confirm'] ) ? $request['password_confirm'] : '';

        do_action( 'developer_starter_auth_before_reset_password', $login, developer_starter_resolve_client_ip( $this->client_ip_callback ), $this->get_safe_hook_request_payload() );

        if ( '' === $key || '' === $login ) {
            return new \WP_Error( 'reset_password_invalid_link', __( '无效的重置链接', 'developer-starter' ) );
        }

        if ( '' === (string) $password ) {
            return new \WP_Error( 'reset_password_required', __( '请输入新密码', 'developer-starter' ) );
        }

        if ( $password !== $password_confirm ) {
            return new \WP_Error( 'reset_password_mismatch', __( '两次输入的密码不一致', 'developer-starter' ) );
        }

        $strength = $this->get_option( 'password_strength', 'medium' );
        $strength_check = $this->check_password_strength( $password, $strength );
        if ( ! $strength_check['valid'] ) {
            return new \WP_Error( 'reset_password_strength_failed', (string) $strength_check['message'] );
        }

        $user = check_password_reset_key( $key, $login );
        if ( is_wp_error( $user ) ) {
            return new \WP_Error( 'reset_password_key_invalid', __( '重置链接已失效，请重新申请', 'developer-starter' ) );
        }

        reset_password( $user, $password );

        $login_page_id = $this->get_option( 'login_page_id', '' );
        $redirect = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
        $redirect = apply_filters( 'developer_starter_auth_reset_password_redirect', $redirect, $user );

        do_action( 'developer_starter_auth_reset_password_success', $user, developer_starter_resolve_client_ip( $this->client_ip_callback ) );

        return array(
            'message'  => __( '密码重置成功，请使用新密码登录', 'developer-starter' ),
            'redirect' => $redirect,
        );
    }

    /**
     * 检查密码强度。
     *
     * @param string $password 密码。
     * @param string $required_strength 强度级别。
     * @return array<string,mixed>
     */
    public function check_password_strength( $password, $required_strength ) {
        $length = strlen( (string) $password );

        if ( 'weak' === $required_strength ) {
            if ( $length < 6 ) {
                return array( 'valid' => false, 'message' => __( '密码至少需要6个字符', 'developer-starter' ) );
            }
        } elseif ( 'medium' === $required_strength ) {
            if ( $length < 8 ) {
                return array( 'valid' => false, 'message' => __( '密码至少需要8个字符', 'developer-starter' ) );
            }
            if ( ! preg_match( '/[A-Za-z]/', (string) $password ) || ! preg_match( '/[0-9]/', (string) $password ) ) {
                return array( 'valid' => false, 'message' => __( '密码必须包含字母和数字', 'developer-starter' ) );
            }
        } elseif ( 'strong' === $required_strength ) {
            if ( $length < 10 ) {
                return array( 'valid' => false, 'message' => __( '密码至少需要10个字符', 'developer-starter' ) );
            }
            if ( ! preg_match( '/[A-Z]/', (string) $password ) ) {
                return array( 'valid' => false, 'message' => __( '密码必须包含大写字母', 'developer-starter' ) );
            }
            if ( ! preg_match( '/[a-z]/', (string) $password ) ) {
                return array( 'valid' => false, 'message' => __( '密码必须包含小写字母', 'developer-starter' ) );
            }
            if ( ! preg_match( '/[0-9]/', (string) $password ) ) {
                return array( 'valid' => false, 'message' => __( '密码必须包含数字', 'developer-starter' ) );
            }
            if ( ! preg_match( '/[!@#$%^&*(),.?":{}|<>]/', (string) $password ) ) {
                return array( 'valid' => false, 'message' => __( '密码必须包含特殊字符', 'developer-starter' ) );
            }
        }

        return array( 'valid' => true, 'message' => '' );
    }

    /**
     * 校验注册用户名中的中文策略。
     *
     * @param string $username 清洗后的用户名。
     * @param string $raw_username 原始用户名。
     * @return true|\WP_Error
     */
    private function validate_register_username_chinese_policy( $username, $raw_username = '' ) {
        $policy = $this->get_register_username_chinese_policy();
        $text = '' !== (string) $raw_username ? (string) $raw_username : (string) $username;

        if ( ! $this->contains_han_characters( $text ) && ! $this->contains_han_characters( (string) $username ) ) {
            return true;
        }

        if ( 'deny' === $policy ) {
            return new \WP_Error( 'register_username_chinese_disallowed', __( '用户名不支持中文，请使用字母、数字或下划线', 'developer-starter' ) );
        }

        if ( 'scan' !== $policy || ! function_exists( 'qiling_content_security_scan_text' ) ) {
            return true;
        }

        $scan = qiling_content_security_scan_text(
            $text,
            array(
                'object_type'  => 'username',
                'object_title' => __( '注册用户名', 'developer-starter' ),
                'source'       => 'qiling_theme_register_username',
                'use_remote'   => true,
            )
        );
        if ( is_wp_error( $scan ) || ! is_array( $scan ) ) {
            return true;
        }

        $status = isset( $scan['status'] ) ? (string) $scan['status'] : '';
        if ( in_array( $status, array( 'review', 'blocked' ), true ) ) {
            return new \WP_Error( 'register_username_content_risk', __( '用户名未通过内容安全检测，请更换后重试', 'developer-starter' ) );
        }

        return true;
    }

    /**
     * 获取注册用户名中文策略。
     *
     * @return string
     */
    private function get_register_username_chinese_policy() {
        $policy = (string) $this->get_option( 'register_username_chinese_policy', 'allow' );
        return in_array( $policy, array( 'allow', 'deny', 'scan' ), true ) ? $policy : 'allow';
    }

    /**
     * 判断文本是否包含中文汉字。
     *
     * @param string $text 文本。
     * @return bool
     */
    private function contains_han_characters( $text ) {
        if ( '' === (string) $text ) {
            return false;
        }

        return 1 === preg_match( '/\p{Han}/u', (string) $text );
    }

    /**
     * 按需消费滑动验证码令牌。
     *
     * @param array<string,mixed> $request 请求数据。
     * @return true|\WP_Error
     */
    private function consume_captcha_if_required( $request ) {
        if ( ! $this->get_option( 'auth_captcha_enable', '' ) ) {
            return true;
        }

        $captcha = isset( $request['captcha_verified'] ) ? $request['captcha_verified'] : '';
        if ( ! $this->consume_captcha_token( $captcha ) ) {
            return new \WP_Error( 'captcha_required', __( '请完成滑动验证', 'developer-starter' ) );
        }

        return true;
    }

    /**
     * 调用速率限制检查。
     *
     * @param string $action 动作名。
     * @param int    $limit 次数限制。
     * @param int    $duration 窗口秒数。
     * @return bool|string
     */
    private function check_rate_limit( $action, $limit, $duration ) {
        if ( is_callable( $this->rate_limit_callback ) ) {
            return call_user_func( $this->rate_limit_callback, $action, $limit, $duration );
        }

        return true;
    }

    /**
     * 消费验证码 token。
     *
     * @param mixed $token 令牌。
     * @return bool
     */
    private function consume_captcha_token( $token ) {
        if ( is_callable( $this->captcha_consume_callback ) ) {
            return (bool) call_user_func( $this->captcha_consume_callback, $token );
        }

        return false;
    }

    /**
     * 是否启用邮箱注册验证码。
     *
     * @return bool
     */
    private function is_register_email_code_enabled() {
        if ( is_callable( $this->register_email_enabled_callback ) ) {
            return (bool) call_user_func( $this->register_email_enabled_callback );
        }

        return false;
    }

    /**
     * 校验邮箱验证码。
     *
     * @param string $email 邮箱。
     * @param string $code  验证码。
     * @return true|\WP_Error
     */
    private function verify_register_email_code( $email, $code ) {
        if ( is_callable( $this->verify_register_email_code_callback ) ) {
            return call_user_func( $this->verify_register_email_code_callback, $email, $code );
        }

        return true;
    }

    /**
     * 清除邮箱验证码。
     *
     * @param string $email 邮箱。
     * @return void
     */
    private function clear_register_email_code( $email ) {
        if ( is_callable( $this->clear_register_email_code_callback ) ) {
            call_user_func( $this->clear_register_email_code_callback, $email );
        }
    }

    /**
     * 获取安全请求载荷。
     *
     * @return array<string,mixed>
     */
    private function get_safe_hook_request_payload() {
        if ( is_callable( $this->safe_payload_callback ) ) {
            $payload = call_user_func( $this->safe_payload_callback );
            return is_array( $payload ) ? $payload : array();
        }

        return array();
    }

    /**
     * 获取主题设置。
     *
     * @param string $key 键名。
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
}
