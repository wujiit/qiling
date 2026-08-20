<?php
/**
 * Theme social login manager.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\Social;

use Developer_Starter\Core\Social\Providers\GitHub_Provider;
use Developer_Starter\Core\Social\Providers\Google_Provider;
use Developer_Starter\Core\Social\Providers\QQ_Provider;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Manager {

    const STATE_TRANSIENT_PREFIX = 'ds_social_login_state_';

    /**
     * Singleton instance.
     *
     * @var Manager|null
     */
    private static $instance = null;

    /**
     * Provider cache.
     *
     * @var array<string,Provider_Interface>|null
     */
    private $providers = null;

    /**
     * @return Manager
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function init() {
        add_action( 'admin_post_nopriv_developer_starter_social_login', array( $this, 'handle_login_start' ) );
        add_action( 'admin_post_developer_starter_social_login', array( $this, 'handle_login_start' ) );
        add_action( 'admin_post_nopriv_developer_starter_social_login_callback', array( $this, 'handle_login_callback' ) );
        add_action( 'admin_post_developer_starter_social_login_callback', array( $this, 'handle_login_callback' ) );
    }

    /**
     * @return array<string,Provider_Interface>
     */
    public function get_providers() {
        if ( null !== $this->providers ) {
            return $this->providers;
        }

        $providers = array(
            'qq'     => new QQ_Provider(),
            'github' => new GitHub_Provider(),
            'google' => new Google_Provider(),
        );

        $providers = apply_filters( 'developer_starter_social_login_providers', $providers );

        $normalized = array();
        foreach ( $providers as $provider ) {
            if ( $provider instanceof Provider_Interface ) {
                $normalized[ sanitize_key( $provider->get_key() ) ] = $provider;
            }
        }

        $this->providers = $normalized;
        return $this->providers;
    }

    /**
     * @return array<string,Provider_Interface>
     */
    public function get_available_providers() {
        $available = array();

        foreach ( $this->get_providers() as $key => $provider ) {
            if ( $provider->is_available() ) {
                $available[ $key ] = $provider;
            }
        }

        return $available;
    }

    /**
     * @return bool
     */
    public function has_available_providers() {
        $providers = $this->get_available_providers();
        return ! empty( $providers );
    }

    /**
     * Render enabled social login buttons.
     *
     * @param string              $context Render context.
     * @param array<string,mixed> $args Arguments.
     * @return string
     */
    public function render_buttons( $context = 'login', $args = array() ) {
        $providers = $this->get_available_providers();
        if ( empty( $providers ) ) {
            return '';
        }

        $redirect_to = isset( $args['redirect_to'] ) ? (string) $args['redirect_to'] : $this->get_current_redirect_url();
        $class       = isset( $args['class'] ) ? sanitize_html_class( (string) $args['class'] ) : '';
        $context     = sanitize_key( (string) $context );

        ob_start();
        ?>
        <div class="social-login-buttons <?php echo esc_attr( $class ); ?>" data-social-login-context="<?php echo esc_attr( $context ); ?>">
            <?php foreach ( $providers as $provider ) : ?>
                <?php echo $this->render_button( $provider, $redirect_to ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endforeach; ?>
        </div>
        <?php
        return trim( (string) ob_get_clean() );
    }

    /**
     * Start OAuth login.
     *
     * @return void
     */
    public function handle_login_start() {
        if ( is_user_logged_in() ) {
            wp_safe_redirect( $this->get_safe_redirect_from_request() );
            exit;
        }

        $provider = $this->get_provider_from_request();
        if ( is_wp_error( $provider ) ) {
            $this->fail( $provider );
        }

        if ( ! $provider->is_available() ) {
            $this->fail( new \WP_Error( 'social_provider_unavailable', __( '该第三方登录尚未启用或配置不完整。', 'developer-starter' ) ) );
        }

        if ( ! $this->check_rate_limit( 'start', 30, 5 * MINUTE_IN_SECONDS ) ) {
            $this->fail( new \WP_Error( 'social_login_rate_limited', __( '请求过于频繁，请稍后再试。', 'developer-starter' ) ), 429 );
        }

        $state = $this->create_state(
            $provider->get_key(),
            array(
                'redirect_to' => $this->get_safe_redirect_from_request(),
            )
        );

        if ( is_wp_error( $state ) ) {
            $this->fail( $state );
        }

        $url = $provider->get_authorization_url( $state );
        if ( is_wp_error( $url ) ) {
            $this->fail( $url );
        }

        wp_redirect( $url );
        exit;
    }

    /**
     * Complete OAuth login.
     *
     * @return void
     */
    public function handle_login_callback() {
        $provider = $this->get_provider_from_request();
        if ( is_wp_error( $provider ) ) {
            $this->fail( $provider );
        }

        if ( ! $provider->is_available() ) {
            $this->fail( new \WP_Error( 'social_provider_unavailable', __( '该第三方登录尚未启用或配置不完整。', 'developer-starter' ) ) );
        }

        $state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['state'] ) ) : '';
        $state_data = $this->consume_state( $provider->get_key(), $state );
        if ( is_wp_error( $state_data ) ) {
            $this->fail( $state_data );
        }

        if ( ! $this->check_rate_limit( 'callback', 30, 5 * MINUTE_IN_SECONDS ) ) {
            $this->fail( new \WP_Error( 'social_login_rate_limited', __( '请求过于频繁，请稍后再试。', 'developer-starter' ) ), 429 );
        }

        if ( ! empty( $_GET['error'] ) ) {
            $this->fail( new \WP_Error( 'social_login_denied', __( '第三方授权已取消或失败。', 'developer-starter' ) ) );
        }

        $profile = $provider->get_profile_from_callback( $_GET );
        if ( is_wp_error( $profile ) ) {
            $this->fail( $profile );
        }

        $result = $this->login_or_create_user( $provider, $profile, $state_data );
        if ( is_wp_error( $result ) ) {
            $this->fail( $result );
        }

        wp_safe_redirect( $result['redirect'] );
        exit;
    }

    /**
     * @param Provider_Interface $provider Provider.
     * @param string             $redirect_to Redirect URL.
     * @return string
     */
    private function render_button( Provider_Interface $provider, $redirect_to ) {
        $key       = sanitize_key( $provider->get_key() );
        $label     = $provider->get_label();
        $icon_html = $this->get_provider_icon_html( $key );
        $url       = add_query_arg(
            array(
                'action'      => 'developer_starter_social_login',
                'provider'    => $key,
                'redirect_to' => $redirect_to,
            ),
            admin_url( 'admin-post.php' )
        );

        return sprintf(
            '<a class="social-btn social-%1$s" href="%2$s" rel="nofollow"><span class="social-login-icon-wrap">%3$s</span><span>%4$s</span></a>',
            esc_attr( $key ),
            esc_url( $url ),
            $icon_html,
            esc_html( sprintf( __( '%s 登录', 'developer-starter' ), $label ) )
        );
    }

    /**
     * @param string $provider_key Provider key.
     * @return string
     */
    private function get_provider_icon_html( $provider_key ) {
        $provider_key = sanitize_key( $provider_key );
        $option_key = 'social_login_' . $provider_key . '_icon';
        $icon       = trim( (string) ( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( $option_key, '' ) : '' ) );

        if ( '' !== $icon && false !== strpos( $icon, ' ' ) ) {
            $parts = preg_split( '/\s+/', $icon );
            foreach ( $parts as $part ) {
                if ( strpos( $part, 'icon-' ) === 0 || strpos( $part, 'qi-' ) === 0 ) {
                    $icon = $part;
                    break;
                }
            }
        }

        if ( '' !== $icon && function_exists( 'developer_starter_get_icon_html' ) ) {
            $icon_html = developer_starter_get_icon_html( $icon, 'social-icon-svg' );
            if ( '' !== $icon_html ) {
                return $icon_html;
            }
        }

        $local_icon_html = $this->get_local_provider_icon_html( $provider_key );
        if ( '' !== $local_icon_html ) {
            return $local_icon_html;
        }

        return '<span class="social-icon social-icon-' . esc_attr( $provider_key ) . '" aria-hidden="true"></span>';
    }

    /**
     * Resolve a local provider icon from assets/images/{provider}.{ext}.
     *
     * @param string $provider_key Provider key.
     * @return string
     */
    public function get_local_provider_icon_html( $provider_key ) {
        if ( ! defined( 'DEVELOPER_STARTER_DIR' ) || ! defined( 'DEVELOPER_STARTER_ASSETS' ) ) {
            return '';
        }

        $provider_key = sanitize_key( $provider_key );
        if ( '' === $provider_key ) {
            return '';
        }

        foreach ( array( 'png', 'svg', 'webp', 'jpg', 'jpeg' ) as $extension ) {
            $relative_path = 'images/' . $provider_key . '.' . $extension;
            $local_path    = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/' . $relative_path;
            if ( file_exists( $local_path ) ) {
                return sprintf(
                    '<img class="social-icon-img social-icon-img-%1$s" src="%2$s" alt="" width="18" height="18" loading="lazy" decoding="async" aria-hidden="true">',
                    esc_attr( $provider_key ),
                    esc_url( trailingslashit( DEVELOPER_STARTER_ASSETS ) . $relative_path )
                );
            }
        }

        return '';
    }

    /**
     * @return Provider_Interface|\WP_Error
     */
    private function get_provider_from_request() {
        $provider_key = isset( $_REQUEST['provider'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['provider'] ) ) : '';
        $providers    = $this->get_providers();

        if ( '' === $provider_key || empty( $providers[ $provider_key ] ) ) {
            return new \WP_Error( 'social_provider_missing', __( '未知的第三方登录方式。', 'developer-starter' ) );
        }

        return $providers[ $provider_key ];
    }

    /**
     * @param string              $provider_key Provider key.
     * @param array<string,mixed> $data State payload.
     * @return string|\WP_Error
     */
    private function create_state( $provider_key, $data ) {
        $state = function_exists( 'wp_generate_uuid4' )
            ? str_replace( '-', '', wp_generate_uuid4() )
            : wp_generate_password( 32, false, false );

        $payload = array(
            'provider'   => sanitize_key( $provider_key ),
            'redirect_to'=> isset( $data['redirect_to'] ) ? esc_url_raw( (string) $data['redirect_to'] ) : home_url( '/' ),
            'ip_hash'    => $this->get_ip_hash(),
            'created_at' => time(),
        );

        $stored = set_transient( $this->get_state_transient_key( $provider_key, $state ), $payload, 10 * MINUTE_IN_SECONDS );
        if ( ! $stored ) {
            return new \WP_Error( 'social_state_failed', __( '无法创建第三方登录会话，请稍后再试。', 'developer-starter' ) );
        }

        return $state;
    }

    /**
     * @param string $provider_key Provider key.
     * @param string $state OAuth state.
     * @return array<string,mixed>|\WP_Error
     */
    private function consume_state( $provider_key, $state ) {
        if ( '' === $state || ! preg_match( '/^[A-Za-z0-9_-]{20,80}$/', $state ) ) {
            return new \WP_Error( 'social_state_missing', __( '第三方登录状态参数无效，请重新发起登录。', 'developer-starter' ) );
        }

        $key  = $this->get_state_transient_key( $provider_key, $state );
        $data = get_transient( $key );
        delete_transient( $key );

        if ( ! is_array( $data ) || empty( $data['provider'] ) || sanitize_key( $data['provider'] ) !== sanitize_key( $provider_key ) ) {
            return new \WP_Error( 'social_state_expired', __( '第三方登录会话已过期，请重新发起登录。', 'developer-starter' ) );
        }

        $enforce_ip = (bool) apply_filters( 'developer_starter_social_login_enforce_state_ip', false, $provider_key, $data );
        if ( $enforce_ip && ! empty( $data['ip_hash'] ) && $data['ip_hash'] !== $this->get_ip_hash() ) {
            return new \WP_Error( 'social_state_ip_mismatch', __( '第三方登录环境发生变化，请重新发起登录。', 'developer-starter' ) );
        }

        return $data;
    }

    /**
     * @param string $provider_key Provider key.
     * @param string $state OAuth state.
     * @return string
     */
    private function get_state_transient_key( $provider_key, $state ) {
        return self::STATE_TRANSIENT_PREFIX . sanitize_key( $provider_key ) . '_' . md5( (string) $state );
    }

    /**
     * @param Provider_Interface  $provider Provider.
     * @param array<string,mixed> $profile Profile.
     * @param array<string,mixed> $state_data State data.
     * @return array<string,mixed>|\WP_Error
     */
    private function login_or_create_user( Provider_Interface $provider, $profile, $state_data ) {
        $provider_key = sanitize_key( $provider->get_key() );
        $identifier   = isset( $profile['identifier'] ) ? sanitize_text_field( (string) $profile['identifier'] ) : '';
        if ( '' === $identifier ) {
            return new \WP_Error( 'social_profile_missing_id', __( '第三方账号缺少唯一身份标识。', 'developer-starter' ) );
        }

        $user_id = $this->find_user_id( $provider_key, $identifier );
        $created = false;

        if ( ! $user_id ) {
            if ( ! $this->can_auto_register( $provider_key, $profile ) ) {
                return new \WP_Error( 'social_account_not_bound', __( '该第三方账号尚未绑定本站账号，请先使用其他方式注册或联系管理员。', 'developer-starter' ) );
            }

            $user_id = $this->create_user( $provider_key, $profile );
            if ( is_wp_error( $user_id ) ) {
                return $user_id;
            }
            $created = true;
        }

        $this->update_user_meta( $user_id, $provider_key, $profile );

        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return new \WP_Error( 'social_user_missing', __( '用户不存在，无法完成登录。', 'developer-starter' ) );
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true, is_ssl() );
        do_action( 'wp_login', $user->user_login, $user );

        $redirect = $this->resolve_success_redirect( $state_data, $created, $user );

        do_action( 'developer_starter_social_login_success', $user_id, $provider_key, $profile, $created, $redirect );

        return array(
            'user_id'  => $user_id,
            'created'  => $created,
            'redirect' => $redirect,
        );
    }

    /**
     * @param string $provider_key Provider key.
     * @param string $identifier Provider identifier.
     * @return int
     */
    private function find_user_id( $provider_key, $identifier ) {
        $users = get_users(
            array(
                'meta_key'     => $this->get_identifier_meta_key( $provider_key ),
                'meta_value'   => $identifier,
                'fields'       => 'ID',
                'number'       => 1,
                'count_total'  => false,
            )
        );

        return ! empty( $users ) ? absint( $users[0] ) : 0;
    }

    /**
     * @param string              $provider_key Provider key.
     * @param array<string,mixed> $profile Profile.
     * @return bool
     */
    private function can_auto_register( $provider_key, $profile ) {
        $allowed = (bool) get_option( 'users_can_register' );

        if (
            $allowed
            && function_exists( 'developer_starter_get_registration_mode' )
            && 'email_only' === developer_starter_get_registration_mode()
        ) {
            $allowed = false;
        }

        if ( $allowed && function_exists( 'qilingshop_registration_code_is_enabled' ) && qilingshop_registration_code_is_enabled() ) {
            $allowed = false;
        }

        return (bool) apply_filters( 'developer_starter_social_login_can_auto_register', $allowed, $provider_key, $profile );
    }

    /**
     * @param string              $provider_key Provider key.
     * @param array<string,mixed> $profile Profile.
     * @return int|\WP_Error
     */
    private function create_user( $provider_key, $profile ) {
        $login_name = $this->generate_login_name( $provider_key );
        $nickname   = isset( $profile['nickname'] ) ? trim( (string) $profile['nickname'] ) : '';
        $display    = '' !== $nickname ? $nickname : $login_name;
        $email      = $this->get_profile_email( $profile );

        $userdata = array(
            'user_login'   => $login_name,
            'display_name' => $display,
            'nickname'     => $display,
            'first_name'   => $display,
            'user_pass'    => wp_generate_password( 24, true, true ),
        );
        if ( '' !== $email && ! email_exists( $email ) ) {
            $userdata['user_email'] = $email;
        }

        $user_id = wp_insert_user( $userdata );
        if ( is_wp_error( $user_id ) ) {
            return new \WP_Error( 'social_user_create_failed', sprintf( __( '创建用户失败：%s', 'developer-starter' ), $user_id->get_error_message() ) );
        }

        update_user_meta( $user_id, 'qiling_social_login_provider', sanitize_key( $provider_key ) );
        return absint( $user_id );
    }

    /**
     * @param int                 $user_id User ID.
     * @param string              $provider_key Provider key.
     * @param array<string,mixed> $profile Profile.
     * @return void
     */
    private function update_user_meta( $user_id, $provider_key, $profile ) {
        update_user_meta( $user_id, $this->get_identifier_meta_key( $provider_key ), (string) $profile['identifier'] );
        update_user_meta( $user_id, 'qiling_social_' . $provider_key . '_last_login', current_time( 'mysql' ) );

        if ( ! empty( $profile['nickname'] ) ) {
            update_user_meta( $user_id, 'qiling_social_' . $provider_key . '_nickname', sanitize_text_field( (string) $profile['nickname'] ) );
        }

        if ( ! empty( $profile['avatar'] ) ) {
            update_user_meta( $user_id, 'qiling_social_' . $provider_key . '_avatar', esc_url_raw( (string) $profile['avatar'] ) );
        }

        $email = $this->get_profile_email( $profile );
        if ( '' !== $email ) {
            update_user_meta( $user_id, 'qiling_social_' . $provider_key . '_email', $email );
        }

        if ( isset( $profile['email_verified'] ) ) {
            update_user_meta( $user_id, 'qiling_social_' . $provider_key . '_email_verified', ! empty( $profile['email_verified'] ) ? '1' : '' );
        }
    }

    /**
     * @param array<string,mixed> $profile Profile.
     * @return string
     */
    private function get_profile_email( $profile ) {
        if ( empty( $profile['email'] ) ) {
            return '';
        }

        $email = sanitize_email( (string) $profile['email'] );
        return is_email( $email ) ? $email : '';
    }

    /**
     * @param string $provider_key Provider key.
     * @return string
     */
    private function get_identifier_meta_key( $provider_key ) {
        return 'qiling_social_' . sanitize_key( $provider_key ) . '_openid';
    }

    /**
     * @param string $provider_key Provider key.
     * @return string
     */
    private function generate_login_name( $provider_key ) {
        $prefix = sanitize_user( substr( sanitize_key( $provider_key ), 0, 8 ), true );
        if ( '' === $prefix ) {
            $prefix = 'social';
        }

        for ( $i = 0; $i < 8; $i++ ) {
            $login = $prefix . wp_rand( 1000, 9999 ) . wp_rand( 1000, 9999 ) . wp_rand( 1000, 9999 );
            if ( ! username_exists( $login ) ) {
                return $login;
            }
        }

        return $prefix . substr( md5( microtime( true ) . wp_rand() ), 0, 16 );
    }

    /**
     * @param array<string,mixed> $state_data State data.
     * @param bool                $created Whether a new user was created.
     * @param \WP_User            $user User.
     * @return string
     */
    private function resolve_success_redirect( $state_data, $created, $user ) {
        $option_key = $created ? 'register_redirect_url' : 'login_redirect_url';
        $redirect   = function_exists( 'developer_starter_get_option' ) ? trim( (string) developer_starter_get_option( $option_key, '' ) ) : '';

        if ( '' === $redirect && ! empty( $state_data['redirect_to'] ) ) {
            $redirect = (string) $state_data['redirect_to'];
        }

        $redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

        return (string) apply_filters( 'developer_starter_social_login_redirect', $redirect, $user, $created, $state_data );
    }

    /**
     * @return string
     */
    private function get_current_redirect_url() {
        if ( ! empty( $_GET['redirect_to'] ) ) {
            return wp_validate_redirect( esc_url_raw( wp_unslash( (string) $_GET['redirect_to'] ) ), home_url( '/' ) );
        }

        $referer = wp_get_referer();
        if ( $referer ) {
            return wp_validate_redirect( $referer, home_url( '/' ) );
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '/';
        $current_url = home_url( $request_uri );

        return wp_validate_redirect( $current_url, home_url( '/' ) );
    }

    /**
     * @return string
     */
    private function get_safe_redirect_from_request() {
        $redirect_to = isset( $_GET['redirect_to'] ) ? wp_unslash( (string) $_GET['redirect_to'] ) : '';
        if ( '' === $redirect_to ) {
            $redirect_to = $this->get_current_redirect_url();
        }

        return wp_validate_redirect( esc_url_raw( $redirect_to ), home_url( '/' ) );
    }

    /**
     * @return string
     */
    private function get_client_ip() {
        if ( function_exists( 'developer_starter_get_client_ip' ) ) {
            return (string) developer_starter_get_client_ip();
        }

        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            return preg_replace( '/[^0-9a-fA-F:\.]/', '', wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) );
        }

        return '';
    }

    /**
     * @return string
     */
    private function get_ip_hash() {
        return hash( 'sha256', $this->get_client_ip() . '|' . wp_salt( 'nonce' ) );
    }

    /**
     * @param string $scope Scope.
     * @param int    $limit Max hits.
     * @param int    $ttl TTL.
     * @return bool
     */
    private function check_rate_limit( $scope, $limit, $ttl ) {
        $key   = 'ds_social_login_rate_' . sanitize_key( $scope ) . '_' . md5( $this->get_client_ip() );
        $count = get_transient( $key );
        $count = false === $count ? 0 : absint( $count );
        $count++;

        set_transient( $key, $count, $ttl );
        return $count <= $limit;
    }

    /**
     * @param \WP_Error $error Error object.
     * @param int       $status HTTP status.
     * @return void
     */
    private function fail( \WP_Error $error, $status = 400 ) {
        $message = $error->get_error_message();
        if ( '' === $message ) {
            $message = __( '第三方登录失败，请稍后再试。', 'developer-starter' );
        }

        wp_die(
            esc_html( $message ),
            esc_html__( '第三方登录失败', 'developer-starter' ),
            array(
                'response' => absint( $status ),
                'back_link'=> true,
            )
        );
    }
}
