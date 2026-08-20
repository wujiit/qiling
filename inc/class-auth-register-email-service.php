<?php
/**
 * Auth Register Email Service
 *
 * 负责注册邮箱验证码的配置、存储、校验与邮件发送。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auth_Register_Email_Service {

    /**
     * @var callable|null
     */
    private $option_callback = null;

    /**
     * @var callable|null
     */
    private $client_ip_callback = null;

    /**
     * @param array<string,mixed> $args 运行参数。
     */
    public function __construct( $args = array() ) {
        if ( isset( $args['option_callback'] ) && is_callable( $args['option_callback'] ) ) {
            $this->option_callback = $args['option_callback'];
        }

        if ( isset( $args['client_ip_callback'] ) && is_callable( $args['client_ip_callback'] ) ) {
            $this->client_ip_callback = $args['client_ip_callback'];
        }
    }

    /**
     * 是否启用邮箱注册验证码。
     *
     * @return bool
     */
    public function is_enabled() {
        if ( function_exists( 'developer_starter_is_register_email_code_enabled' ) ) {
            return developer_starter_is_register_email_code_enabled();
        }

        return $this->get_option( 'register_email_code_enable', '' ) === '1';
    }

    /**
     * 获取邮箱注册验证码有效期（分钟）。
     *
     * @return int
     */
    public function get_expire_minutes() {
        if ( function_exists( 'developer_starter_get_register_email_code_expire_minutes' ) ) {
            return (int) developer_starter_get_register_email_code_expire_minutes();
        }

        $minutes = absint( $this->get_option( 'register_email_code_expire', 10 ) );
        if ( $minutes < 1 ) {
            $minutes = 10;
        } elseif ( $minutes > 60 ) {
            $minutes = 60;
        }

        return $minutes;
    }

    /**
     * 获取邮箱注册验证码有效期（秒）。
     *
     * @return int
     */
    public function get_expire_seconds() {
        if ( function_exists( 'developer_starter_get_register_email_code_expire_seconds' ) ) {
            return (int) developer_starter_get_register_email_code_expire_seconds();
        }

        return $this->get_expire_minutes() * MINUTE_IN_SECONDS;
    }

    /**
     * 获取邮箱注册验证码每日 IP 发送限制。
     *
     * @return int
     */
    public function get_daily_ip_limit() {
        if ( function_exists( 'developer_starter_get_register_email_code_daily_ip_limit' ) ) {
            return (int) developer_starter_get_register_email_code_daily_ip_limit();
        }

        $limit = absint( $this->get_option( 'register_email_code_daily_ip_limit', 30 ) );
        if ( $limit < 1 ) {
            $limit = 30;
        } elseif ( $limit > 500 ) {
            $limit = 500;
        }

        return $limit;
    }

    /**
     * 获取邮箱注册验证码单邮箱每日发送限制。
     *
     * @return int
     */
    public function get_daily_email_limit() {
        if ( function_exists( 'developer_starter_get_register_email_code_daily_email_limit' ) ) {
            return (int) developer_starter_get_register_email_code_daily_email_limit();
        }

        $limit = absint( $this->get_option( 'register_email_code_daily_email_limit', 10 ) );
        if ( $limit < 1 ) {
            $limit = 10;
        } elseif ( $limit > 200 ) {
            $limit = 200;
        }

        return $limit;
    }

    /**
     * 标准化邮箱字符串。
     *
     * @param string $email 邮箱。
     * @return string
     */
    public function normalize_email( $email ) {
        return strtolower( trim( sanitize_email( (string) $email ) ) );
    }

    /**
     * 检查 SMTP 配置是否完整。
     *
     * @return bool
     */
    public function is_smtp_ready() {
        $smtp_host = trim( (string) $this->get_option( 'smtp_host', '' ) );
        $smtp_username = trim( (string) $this->get_option( 'smtp_username', '' ) );
        $smtp_password_raw = (string) $this->get_option( 'smtp_password', '' );
        $smtp_password = '';

        if ( class_exists( __NAMESPACE__ . '\\SMTP_Manager' ) ) {
            $smtp_password = (string) SMTP_Manager::decrypt_password( $smtp_password_raw );
        } else {
            $smtp_password = $smtp_password_raw;
        }

        return $smtp_host !== '' && $smtp_username !== '' && trim( $smtp_password ) !== '';
    }

    /**
     * 生成固定长度数字验证码。
     *
     * @return string
     */
    public function generate_code() {
        $max = (int) str_repeat( '9', Auth_Manager::REGISTER_EMAIL_CODE_DIGITS );
        try {
            $code = random_int( 0, $max );
        } catch ( \Exception $e ) {
            $code = mt_rand( 0, $max );
        }

        return str_pad( (string) $code, Auth_Manager::REGISTER_EMAIL_CODE_DIGITS, '0', STR_PAD_LEFT );
    }

    /**
     * 设置邮箱验证码发送间隔锁。
     *
     * @param string $email 邮箱。
     * @param string $ip    IP 地址。
     * @return int 锁定截止时间戳。
     */
    public function set_send_lock( $email, $ip ) {
        $interval = $this->get_interval_seconds();
        $lock_until = time() + $interval;
        $keys = $this->get_send_lock_keys( $email, $ip );
        set_transient( $keys['email'], $lock_until, $interval + 5 );
        set_transient( $keys['ip'], $lock_until, $interval + 5 );

        return $lock_until;
    }

    /**
     * 检查邮箱验证码发送间隔锁。
     *
     * @param string $email 邮箱。
     * @param string $ip    IP 地址。
     * @return true|\WP_Error
     */
    public function check_send_lock( $email, $ip ) {
        $keys = $this->get_send_lock_keys( $email, $ip );
        $now = time();

        foreach ( $keys as $key ) {
            $lock_until = (int) get_transient( $key );
            if ( $lock_until > $now ) {
                return new \WP_Error(
                    'register_email_code_send_lock',
                    sprintf(
                        __( '发送过于频繁，请 %s 后再试', 'developer-starter' ),
                        human_time_diff( $now, $lock_until )
                    )
                );
            }
            if ( $lock_until > 0 ) {
                delete_transient( $key );
            }
        }

        return true;
    }

    /**
     * 检查每日发送限制。
     *
     * @param string $scope      作用域。
     * @param string $identifier 标识。
     * @param int    $limit      限额。
     * @return bool
     */
    public function check_daily_limit( $scope, $identifier, $limit ) {
        if ( $limit <= 0 ) {
            return true;
        }

        $key = $this->get_daily_counter_key( $scope, $identifier );
        $count = (int) get_transient( $key );
        return $count < $limit;
    }

    /**
     * 增加每日发送计数。
     *
     * @param string $scope      作用域。
     * @param string $identifier 标识。
     * @return void
     */
    public function increment_daily_counter( $scope, $identifier ) {
        $key = $this->get_daily_counter_key( $scope, $identifier );
        $count = (int) get_transient( $key );
        set_transient( $key, $count + 1, 2 * DAY_IN_SECONDS );
    }

    /**
     * 存储邮箱注册验证码（哈希）。
     *
     * @param string $email 邮箱。
     * @param string $code  验证码。
     * @return void
     */
    public function store_code( $email, $code ) {
        $payload = array(
            'hash'       => wp_hash_password( $code ),
            'created_at' => time(),
        );
        set_transient( $this->get_code_key( $email ), $payload, $this->get_expire_seconds() );
    }

    /**
     * 清理邮箱注册验证码相关状态。
     *
     * @param string $email 邮箱。
     * @return void
     */
    public function clear_code( $email ) {
        delete_transient( $this->get_code_key( $email ) );
        delete_transient( $this->get_verify_attempts_key( $email ) );
        delete_transient( $this->get_verify_lock_key( $email ) );
    }

    /**
     * 校验邮箱注册验证码。
     *
     * @param string $email 邮箱。
     * @param string $code  验证码。
     * @return true|\WP_Error
     */
    public function verify_code( $email, $code ) {
        $email = $this->normalize_email( $email );
        $code = preg_replace( '/\D+/', '', (string) $code );

        if ( ! is_email( $email ) ) {
            return new \WP_Error( 'register_email_invalid', __( '请输入有效的邮箱地址', 'developer-starter' ) );
        }
        if ( strlen( $code ) !== Auth_Manager::REGISTER_EMAIL_CODE_DIGITS ) {
            return new \WP_Error( 'register_email_code_invalid_format', __( '请输入6位邮箱验证码', 'developer-starter' ) );
        }

        $now = time();
        $lock_key = $this->get_verify_lock_key( $email );
        $locked_until = (int) get_transient( $lock_key );
        if ( $locked_until > $now ) {
            return new \WP_Error(
                'register_email_code_verify_locked',
                sprintf(
                    __( '验证码输入错误过多，请 %s 后再试', 'developer-starter' ),
                    human_time_diff( $now, $locked_until )
                )
            );
        }
        if ( $locked_until > 0 ) {
            delete_transient( $lock_key );
        }

        $stored = get_transient( $this->get_code_key( $email ) );
        if ( ! is_array( $stored ) || empty( $stored['hash'] ) ) {
            return new \WP_Error( 'register_email_code_expired', __( '邮箱验证码已过期，请重新获取', 'developer-starter' ) );
        }

        $stored_hash = (string) $stored['hash'];
        $is_valid = wp_check_password( $code, $stored_hash );
        if ( ! $is_valid && isset( $stored['code'] ) ) {
            $legacy_code = (string) $stored['code'];
            $is_valid = ( $legacy_code !== '' ) && hash_equals( $legacy_code, $code );
        }

        if ( $is_valid ) {
            delete_transient( $this->get_verify_attempts_key( $email ) );
            delete_transient( $lock_key );
            return true;
        }

        $attempts_key = $this->get_verify_attempts_key( $email );
        $attempts = (int) get_transient( $attempts_key );
        $attempts++;

        if ( $attempts >= Auth_Manager::REGISTER_EMAIL_CODE_VERIFY_MAX_ATTEMPTS ) {
            $locked_until = $now + Auth_Manager::REGISTER_EMAIL_CODE_VERIFY_LOCK_SECONDS;
            set_transient( $lock_key, $locked_until, Auth_Manager::REGISTER_EMAIL_CODE_VERIFY_LOCK_SECONDS );
            delete_transient( $attempts_key );

            return new \WP_Error(
                'register_email_code_verify_locked',
                sprintf(
                    __( '验证码输入错误过多，请 %s 后再试', 'developer-starter' ),
                    human_time_diff( $now, $locked_until )
                )
            );
        }

        set_transient( $attempts_key, $attempts, max( MINUTE_IN_SECONDS, $this->get_expire_seconds() ) );
        return new \WP_Error( 'register_email_code_invalid', __( '邮箱验证码错误或已过期', 'developer-starter' ) );
    }

    /**
     * 发送注册邮箱验证码邮件。
     *
     * @param string $email 邮箱。
     * @param string $code  验证码。
     * @return true|\WP_Error
     */
    public function send_code_email( $email, $code ) {
        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
        $expire_minutes = $this->get_expire_minutes();
        $subject = sprintf( __( '[%s] 注册验证码', 'developer-starter' ), $site_name );
        $message = sprintf( __( "您好！\n\n您正在进行账号注册，验证码为：%s\n\n验证码 %d 分钟内有效，请勿泄露给他人。\n如果这不是您的操作，请忽略本邮件。", 'developer-starter' ), $code, $expire_minutes );

        $headers = array();
        if ( function_exists( 'developer_starter_build_html_email_template' ) ) {
            $html_message = developer_starter_build_html_email_template(
                array(
                    'title'  => __( '注册验证码', 'developer-starter' ),
                    'intro'  => __( '您正在进行账号注册，请使用以下验证码完成校验。', 'developer-starter' ),
                    'lines'  => array(
                        __( '验证码', 'developer-starter' ) => $code,
                        __( '有效期', 'developer-starter' ) => sprintf( __( '%d 分钟', 'developer-starter' ), $expire_minutes ),
                    ),
                    'notice' => __( '如果这不是您的操作，请忽略此邮件并检查账户安全。', 'developer-starter' ),
                )
            );
            if ( is_string( $html_message ) && trim( $html_message ) !== '' ) {
                $message = $html_message;
                $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            }
        }

        $sent = wp_mail( $email, $subject, $message, $headers );
        if ( ! $sent ) {
            return new \WP_Error( 'register_email_code_send_failed', __( '邮件发送失败，请稍后重试', 'developer-starter' ) );
        }

        return true;
    }

    /**
     * @param string $key     配置键。
     * @param mixed  $default 默认值。
     * @return mixed
     */
    private function get_option( $key, $default = '' ) {
        if ( $this->option_callback ) {
            return call_user_func( $this->option_callback, $key, $default );
        }

        return $default;
    }

    /**
     * 获取邮箱验证码发送间隔（秒）。
     *
     * @return int
     */
    private function get_interval_seconds() {
        if ( function_exists( 'developer_starter_get_register_email_code_interval_seconds' ) ) {
            return (int) developer_starter_get_register_email_code_interval_seconds();
        }

        $seconds = absint( $this->get_option( 'register_email_code_interval', 60 ) );
        if ( $seconds < 30 ) {
            $seconds = 60;
        } elseif ( $seconds > 600 ) {
            $seconds = 600;
        }

        return $seconds;
    }

    /**
     * @param string $email 邮箱。
     * @return string
     */
    private function get_code_key( $email ) {
        $email = $this->normalize_email( $email );
        return 'ds_register_email_code_' . md5( $email );
    }

    /**
     * @param string $email 邮箱。
     * @param string $ip    IP 地址。
     * @return array<string,string>
     */
    private function get_send_lock_keys( $email, $ip ) {
        $email = $this->normalize_email( $email );
        return array(
            'email' => 'ds_register_email_send_lock_email_' . md5( $email ),
            'ip'    => 'ds_register_email_send_lock_ip_' . md5( $ip ),
        );
    }

    /**
     * @param string $scope      作用域。
     * @param string $identifier 标识。
     * @return string
     */
    private function get_daily_counter_key( $scope, $identifier ) {
        $date_key = wp_date( 'Ymd', current_time( 'timestamp' ) );
        return 'ds_register_email_daily_' . sanitize_key( $scope ) . '_' . md5( (string) $identifier ) . '_' . $date_key;
    }

    /**
     * @param string $email 邮箱。
     * @return string
     */
    private function get_verify_attempts_key( $email ) {
        $email = $this->normalize_email( $email );
        return 'ds_register_email_verify_attempts_' . md5( $email . '|' . developer_starter_resolve_client_ip( $this->client_ip_callback ) );
    }

    /**
     * @param string $email 邮箱。
     * @return string
     */
    private function get_verify_lock_key( $email ) {
        $email = $this->normalize_email( $email );
        return 'ds_register_email_verify_lock_' . md5( $email . '|' . developer_starter_resolve_client_ip( $this->client_ip_callback ) );
    }
}
