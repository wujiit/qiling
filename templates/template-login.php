<?php
/**
 * Template Name: 用户登录
 * Template Post Type: page
 * 
 * @package Developer_Starter
 */

// 已登录用户跳转
if ( is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

get_header();

$captcha_enable = developer_starter_get_option( 'auth_captcha_enable', '' );
$remember_me_enable = developer_starter_get_option( 'login_remember_me_enable', '' ) === '1';
$register_page_id = developer_starter_get_option( 'register_page_id', '' );
$forgot_page_id = developer_starter_get_option( 'forgot_password_page_id', '' );

// SMS相关设置
$sms_enable = developer_starter_get_option( 'sms_enable', '' ) === '1';
$sms_default_phone = developer_starter_get_option( 'sms_default_phone_login', '' ) === '1';
$sms_phone_only = function_exists( 'developer_starter_is_sms_phone_only_effective' )
    ? developer_starter_is_sms_phone_only_effective()
    : ( developer_starter_get_option( 'sms_phone_only', '' ) === '1' );
$email_register_enabled = function_exists( 'developer_starter_is_email_registration_allowed' )
    ? developer_starter_is_email_registration_allowed()
    : ! $sms_phone_only;
$phone_register_enabled = function_exists( 'developer_starter_is_phone_registration_allowed' )
    ? developer_starter_is_phone_registration_allowed()
    : $sms_enable;

// 微信登录开关
$weixin_login_enable = developer_starter_get_option( 'weixin_login_enable', '' ) === '1';
$weixin_login_available = function_exists( 'developer_starter_is_weixin_registration_allowed' )
    ? developer_starter_is_weixin_registration_allowed()
    : ( $weixin_login_enable && class_exists( 'qiling_weixin_login' ) );
$weixin_login_default = developer_starter_get_option( 'weixin_login_default', '' ) === '1';
$weixin_default_effective = $weixin_login_available && $weixin_login_default && ! ( $sms_default_phone || $sms_phone_only );
$can_register = get_option( 'users_can_register' ) && ( $email_register_enabled || $phone_register_enabled || $weixin_login_available );

$weixin_icon_raw = developer_starter_get_option( 'weixin_login_icon', '' );
$weixin_icon = trim( $weixin_icon_raw );
if ( ! empty( $weixin_icon ) && strpos( $weixin_icon, ' ' ) !== false ) {
    $parts = preg_split( '/\s+/', $weixin_icon );
    foreach ( $parts as $part ) {
        if ( strpos( $part, 'icon-' ) === 0 || strpos( $part, 'qi-' ) === 0 ) {
            $weixin_icon = $part;
            break;
        }
    }
}
$weixin_icon_html = '';
if ( $weixin_login_available && $weixin_icon && function_exists( 'developer_starter_get_icon_html' ) ) {
    $weixin_icon_html = developer_starter_get_icon_html( $weixin_icon, 'social-icon-svg' );
}
if ( $weixin_login_available && '' === $weixin_icon_html && class_exists( '\Developer_Starter\Core\Social\Manager' ) ) {
    $weixin_icon_html = \Developer_Starter\Core\Social\Manager::get_instance()->get_local_provider_icon_html( 'weixin' );
}

$social_login_buttons_html = '';
if ( class_exists( '\Developer_Starter\Core\Social\Manager' ) ) {
    $social_login_buttons_html = \Developer_Starter\Core\Social\Manager::get_instance()->render_buttons( 'auth-login' );
}
$social_login_available = '' !== $social_login_buttons_html;

// 确定默认显示哪个登录方式
$default_tab = 'account'; // 账号密码登录
if ( $sms_enable && ( $sms_default_phone || $sms_phone_only ) ) {
    $default_tab = 'phone'; // 手机号登录
}

// 自定义提示信息
$auth_custom_notice = developer_starter_get_option( 'auth_custom_notice', '' );
$auth_page_side_image = developer_starter_get_media_url( developer_starter_get_option( 'auth_page_side_image', '' ) );
$has_auth_page_side_image = $auth_page_side_image !== '';
$auth_page_background_attrs = function_exists( 'developer_starter_get_auth_page_background_attrs' )
    ? developer_starter_get_auth_page_background_attrs()
    : array( 'class' => '', 'style' => '' );
$auth_page_sms_nonce = wp_create_nonce( 'sms_nonce' );
?>

<div class="auth-page<?php echo esc_attr( $auth_page_background_attrs['class'] ); ?>">
    <div class="auth-bg">
        <div class="auth-particles"></div>
    </div>
    
    <div class="auth-container<?php echo $has_auth_page_side_image ? ' has-side-media' : ''; ?>">
        <?php if ( $has_auth_page_side_image ) : ?>
        <div class="auth-side-media" aria-hidden="true">
            <img src="<?php echo esc_url( $auth_page_side_image ); ?>" alt="" loading="lazy" />
        </div>
        <?php endif; ?>

        <div class="auth-card">
            <?php if ( $weixin_login_available ) : ?>
            <button type="button" class="auth-weixin-toggle" id="auth-weixin-toggle" aria-label="<?php esc_attr_e( '微信扫码登录', 'developer-starter' ); ?>">
                <span class="corner-icon weixin-icon"></span>
                <span class="corner-icon pc-icon"></span>
            </button>
            <?php endif; ?>
            <div class="auth-header">
                <div class="auth-logo">
                    <?php 
                    $logo = developer_starter_get_media_url( developer_starter_get_option( 'site_logo', '' ) );
                    if ( $logo ) : ?>
                        <img src="<?php echo esc_url( $logo ); ?>" alt="<?php bloginfo( 'name' ); ?>" />
                    <?php else : ?>
                        <h1><?php bloginfo( 'name' ); ?></h1>
                    <?php endif; ?>
                </div>
                <h2 class="auth-title"><?php esc_html_e( '用户登录', 'developer-starter' ); ?></h2>
                <p class="auth-subtitle"><?php esc_html_e( '欢迎回来，请登录您的账户', 'developer-starter' ); ?></p>
            </div>
            
            <?php if ( $sms_enable && ! $sms_phone_only ) : ?>
            <!-- 登录方式切换 -->
            <div class="auth-tabs">
                <button type="button" class="auth-tab <?php echo $default_tab === 'phone' ? 'active' : ''; ?>" data-tab="phone">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <?php esc_html_e( '手机号登录', 'developer-starter' ); ?>
                </button>
                <button type="button" class="auth-tab <?php echo $default_tab === 'account' ? 'active' : ''; ?>" data-tab="account">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?php esc_html_e( '账号密码登录', 'developer-starter' ); ?>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if ( $sms_enable ) : ?>
            <!-- 手机号登录表单 -->
            <form id="phone-login-form" class="auth-form <?php echo ( $sms_phone_only || $default_tab === 'phone' ) ? '' : 'hidden'; ?>" novalidate>
                <div class="form-group">
                    <label for="sms-phone">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </label>
                    <input type="tel" id="sms-phone" name="phone" placeholder="<?php esc_attr_e( '请输入手机号', 'developer-starter' ); ?>" required autocomplete="tel" maxlength="11" />
                </div>
                
                <div class="form-group sms-code-group">
                    <label for="sms-code">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </label>
                    <input type="text" id="sms-code" name="code" placeholder="<?php esc_attr_e( '请输入验证码', 'developer-starter' ); ?>" required maxlength="6" autocomplete="one-time-code" />
                    <button type="button" class="sms-send-btn" id="sms-send-btn"><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
                </div>
                
                <?php if ( $captcha_enable ) : ?>
                <div class="form-group captcha-group">
                    <div class="slider-captcha" id="phone-slider-captcha">
                        <div class="captcha-track">
                            <div class="captcha-slider">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                            <div class="captcha-progress"></div>
                            <span class="captcha-text"><?php esc_html_e( '向右滑动完成验证', 'developer-starter' ); ?></span>
                        </div>
                    </div>
                    <input type="hidden" name="captcha_verified" value="false" class="captcha-verified-input" />
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <?php if ( $forgot_page_id ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $forgot_page_id ) ); ?>" class="forgot-link"><?php esc_html_e( '忘记密码？', 'developer-starter' ); ?></a>
                    <?php endif; ?>
                </div>
                
                <div class="form-message" id="phone-form-message"></div>
                
                <button type="submit" class="auth-submit" id="phone-submit-btn">
                    <span class="btn-text"><?php esc_html_e( '登 录', 'developer-starter' ); ?></span>
                    <span class="btn-loading">
                        <svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                    </span>
                </button>
                
                <?php if ( $auth_custom_notice ) : ?>
                <div class="auth-custom-notice">
                    <?php echo wp_kses_post( $auth_custom_notice ); ?>
                </div>
                <?php endif; ?>
                
                <input type="hidden" name="redirect_to" id="phone-redirect_to" value="" />
            </form>
            <?php endif; ?>
            
            <?php if ( ! $sms_phone_only ) : ?>
            <!-- 账号密码登录表单 -->
            <form id="login-form" class="auth-form <?php echo ( $sms_enable && $default_tab === 'phone' ) ? 'hidden' : ''; ?>" novalidate>
                <div class="form-group">
                    <label for="username">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </label>
                    <input type="text" id="username" name="username" placeholder="<?php esc_attr_e( '用户名或邮箱', 'developer-starter' ); ?>" required autocomplete="username" />
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </label>
                    <input type="password" id="password" name="password" placeholder="<?php esc_attr_e( '密码', 'developer-starter' ); ?>" required autocomplete="current-password" />
                    <button type="button" class="toggle-password" tabindex="-1">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                
                <div class="form-row">
                    <?php if ( $remember_me_enable ) : ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="true" />
                        <span><?php esc_html_e( '记住我', 'developer-starter' ); ?></span>
                    </label>
                    <?php endif; ?>
                    <?php if ( $forgot_page_id ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $forgot_page_id ) ); ?>" class="forgot-link"><?php esc_html_e( '忘记密码？', 'developer-starter' ); ?></a>
                    <?php endif; ?>
                </div>
                
                <?php if ( $captcha_enable ) : ?>
                <div class="form-group captcha-group">
                    <div class="slider-captcha" id="slider-captcha">
                        <div class="captcha-track">
                            <div class="captcha-slider">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                            <div class="captcha-progress"></div>
                            <span class="captcha-text"><?php esc_html_e( '向右滑动完成验证', 'developer-starter' ); ?></span>
                        </div>
                    </div>
                    <input type="hidden" name="captcha_verified" value="false" class="captcha-verified-input" />
                </div>
                <?php endif; ?>
                
                <div class="form-message" id="form-message"></div>
                
                <button type="submit" class="auth-submit" id="submit-btn">
                    <span class="btn-text"><?php esc_html_e( '登 录', 'developer-starter' ); ?></span>
                    <span class="btn-loading">
                        <svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                    </span>
                </button>
                
                <?php if ( $auth_custom_notice ) : ?>
                <div class="auth-custom-notice">
                    <?php echo wp_kses_post( $auth_custom_notice ); ?>
                </div>
                <?php endif; ?>
                
                <?php wp_nonce_field( 'developer_starter_auth', 'auth_nonce' ); ?>
                <input type="hidden" name="redirect_to" id="redirect_to" value="" />
            </form>
            <?php endif; ?>

            <?php if ( $weixin_login_available ) : ?>
            <div id="weixin-login-panel" class="auth-form hidden">
                <div class="weixin-login-wrap">
                    <?php echo do_shortcode( '[qiling_weixin_login mode="auto" autoload="0" layout="embed"]' ); ?>
                </div>
                <button type="button" class="auth-weixin-back" id="auth-weixin-back"><?php esc_html_e( '返回账号登录', 'developer-starter' ); ?></button>
            </div>
            <?php endif; ?>

            <?php if ( $weixin_login_available || $social_login_available ) : ?>
            <div class="auth-social">
                <?php echo $social_login_buttons_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php if ( $weixin_login_available ) : ?>
                    <button type="button" class="social-btn social-weixin" id="auth-weixin-btn">
                        <?php if ( $weixin_icon_html ) : ?>
                            <?php echo $weixin_icon_html; ?>
                        <?php else : ?>
                            <span class="social-icon"></span>
                        <?php endif; ?>
                        <?php esc_html_e( '微信扫码登录', 'developer-starter' ); ?>
                    </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ( $register_page_id && $can_register ) : ?>
            <div class="auth-footer">
                <p><?php esc_html_e( '还没有账户？', 'developer-starter' ); ?><a href="<?php echo esc_url( get_permalink( $register_page_id ) ); ?>"><?php esc_html_e( '立即注册', 'developer-starter' ); ?></a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<input type="hidden" id="sms_nonce_field" value="<?php echo esc_attr( $auth_page_sms_nonce ); ?>" />
<?php
$auth_page_config = array(
    'page'          => 'login',
    'defaultTab'    => $default_tab,
    'defaultWeixin' => (bool) $weixin_default_effective,
    'ajaxUrl'       => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
    'authNonce'     => wp_create_nonce( 'developer_starter_auth' ),
    'smsNonce'      => $auth_page_sms_nonce,
    'i18n'          => array(
        'sendCode'         => __( '获取验证码', 'developer-starter' ),
        'sending'          => __( '发送中...', 'developer-starter' ),
        'phoneInvalid'     => __( '请输入正确的手机号', 'developer-starter' ),
        'smsCodeInvalid'   => __( '请输入6位验证码', 'developer-starter' ),
        'networkErrorText' => __( '网络错误，请稍后再试', 'developer-starter' ),
    ),
);
$auth_page_config_json = wp_json_encode( $auth_page_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
if ( ! is_string( $auth_page_config_json ) ) {
    $auth_page_config_json = '{}';
}
?>
<script type="application/json" id="ds-auth-page-config"><?php echo $auth_page_config_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is hex-encoded for safe script data output. ?></script>

<?php get_footer(); ?>
