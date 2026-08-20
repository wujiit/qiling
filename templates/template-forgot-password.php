<?php
/**
 * Template Name: 找回密码
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
$login_page_id = developer_starter_get_option( 'login_page_id', '' );
$password_strength = developer_starter_get_option( 'password_strength', 'medium' );

// SMS设置
$sms_enable = developer_starter_get_option( 'sms_enable', '' ) === '1';

// 检查是否是重置密码模式
$is_reset_mode = isset( $_GET['action'] ) && wp_unslash( (string) $_GET['action'] ) === 'reset';
$reset_key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$reset_login = isset( $_GET['login'] ) ? sanitize_user( wp_unslash( $_GET['login'] ) ) : '';
$auth_page_background_attrs = function_exists( 'developer_starter_get_auth_page_background_attrs' )
    ? developer_starter_get_auth_page_background_attrs()
    : array( 'class' => '', 'style' => '' );
$auth_page_sms_nonce = wp_create_nonce( 'sms_nonce' );
?>

<div class="auth-page<?php echo esc_attr( $auth_page_background_attrs['class'] ); ?>">
    <div class="auth-bg">
        <div class="auth-particles"></div>
    </div>
    
    <div class="auth-container">
        <div class="auth-card">
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
                
                <?php if ( $is_reset_mode ) : ?>
                    <h2 class="auth-title"><?php esc_html_e( '设置新密码', 'developer-starter' ); ?></h2>
                    <p class="auth-subtitle"><?php esc_html_e( '请输入您的新密码', 'developer-starter' ); ?></p>
                <?php else : ?>
                    <h2 class="auth-title"><?php esc_html_e( '找回密码', 'developer-starter' ); ?></h2>
                    <p class="auth-subtitle" id="forgot-subtitle"><?php esc_html_e( '选择找回方式重置您的密码', 'developer-starter' ); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ( $is_reset_mode ) : ?>
            <!-- 重置密码表单 -->
            <form id="reset-form" class="auth-form" novalidate>
                <input type="hidden" name="key" value="<?php echo esc_attr( $reset_key ); ?>" />
                <input type="hidden" name="login" value="<?php echo esc_attr( $reset_login ); ?>" />
                
                <div class="form-group">
                    <label for="password">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </label>
                    <input type="password" id="password" name="password" placeholder="<?php esc_attr_e( '新密码', 'developer-starter' ); ?>" required autocomplete="new-password" />
                    <button type="button" class="toggle-password" tabindex="-1">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                
                <div class="password-strength" id="password-strength">
                    <div class="strength-bar"><span></span></div>
                    <div class="strength-text"></div>
                </div>

                <div class="password-check-list" id="password-check-list">
                    <p><?php esc_html_e( '密码必须满足以下条件：', 'developer-starter' ); ?></p>
                    <ul class="requirements-list">
                        <?php if ( $password_strength === 'weak' ) : ?>
                            <li data-rule="min-6" class="pending"><span class="icon">○</span> <?php esc_html_e( '至少6个字符', 'developer-starter' ); ?></li>
                        <?php elseif ( $password_strength === 'medium' ) : ?>
                            <li data-rule="min-8" class="pending"><span class="icon">○</span> <?php esc_html_e( '至少8个字符', 'developer-starter' ); ?></li>
                            <li data-rule="letter" class="pending"><span class="icon">○</span> <?php esc_html_e( '包含字母', 'developer-starter' ); ?></li>
                            <li data-rule="number" class="pending"><span class="icon">○</span> <?php esc_html_e( '包含数字', 'developer-starter' ); ?></li>
                        <?php elseif ( $password_strength === 'strong' ) : ?>
                            <li data-rule="min-10" class="pending"><span class="icon">○</span> <?php esc_html_e( '至少10个字符', 'developer-starter' ); ?></li>
                            <li data-rule="upper" class="pending"><span class="icon">○</span> <?php esc_html_e( '大写字母', 'developer-starter' ); ?></li>
                            <li data-rule="lower" class="pending"><span class="icon">○</span> <?php esc_html_e( '小写字母', 'developer-starter' ); ?></li>
                            <li data-rule="number" class="pending"><span class="icon">○</span> <?php esc_html_e( '数字', 'developer-starter' ); ?></li>
                            <li data-rule="special" class="pending"><span class="icon">○</span> <?php esc_html_e( '特殊字符', 'developer-starter' ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="<?php esc_attr_e( '确认新密码', 'developer-starter' ); ?>" required autocomplete="new-password" />
                </div>
                
                <div class="form-message" id="form-message"></div>
                
                <button type="submit" class="auth-submit" id="submit-btn">
                    <span class="btn-text"><?php esc_html_e( '重置密码', 'developer-starter' ); ?></span>
                    <span class="btn-loading">
                        <svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                    </span>
                </button>
                
                <?php wp_nonce_field( 'developer_starter_auth', 'auth_nonce' ); ?>
            </form>
            
            <?php else : ?>
            
            <?php if ( $sms_enable ) : ?>
            <!-- 找回方式切换 -->
            <div class="auth-tabs">
                <button type="button" class="auth-tab active" data-tab="email">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?php esc_html_e( '邮箱找回', 'developer-starter' ); ?>
                </button>
                <button type="button" class="auth-tab" data-tab="phone">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <?php esc_html_e( '手机号找回', 'developer-starter' ); ?>
                </button>
            </div>
            <?php endif; ?>
            
            <!-- 邮箱找回密码表单 -->
            <form id="forgot-form" class="auth-form" novalidate>
                <div class="form-group">
                    <label for="email">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </label>
                    <input type="email" id="email" name="email" placeholder="<?php esc_attr_e( '请输入注册邮箱', 'developer-starter' ); ?>" required autocomplete="email" />
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
                    <span class="btn-text"><?php esc_html_e( '发送重置链接', 'developer-starter' ); ?></span>
                    <span class="btn-loading">
                        <svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                    </span>
                </button>
                
                <?php wp_nonce_field( 'developer_starter_auth', 'auth_nonce' ); ?>
            </form>
            
            <?php if ( $sms_enable ) : ?>
            <!-- 手机号找回密码表单 -->
            <form id="phone-forgot-form" class="auth-form hidden" novalidate>
                <div class="form-group">
                    <label for="forgot-phone">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </label>
                    <input type="tel" id="forgot-phone" name="phone" placeholder="<?php esc_attr_e( '请输入绑定的手机号', 'developer-starter' ); ?>" required autocomplete="tel" maxlength="11" />
                </div>
                
                <div class="phone-reset-notice">
                    <div class="phone-reset-notice__inner">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span><?php esc_html_e( '注意：只有已绑定手机号的账户才能通过手机号找回密码。如果您的账户未绑定手机号，请使用邮箱找回方式。', 'developer-starter' ); ?></span>
                    </div>
                </div>
                
                <div class="form-group sms-code-group">
                    <label for="forgot-code">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </label>
                    <input type="text" id="forgot-code" name="code" placeholder="<?php esc_attr_e( '请输入验证码', 'developer-starter' ); ?>" required maxlength="6" autocomplete="one-time-code" />
                    <button type="button" class="sms-send-btn" id="forgot-send-btn"><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
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
                
                <div class="form-group">
                    <label for="forgot-new-password">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </label>
                    <input type="password" id="forgot-new-password" name="new_password" placeholder="<?php esc_attr_e( '请输入新密码', 'developer-starter' ); ?>" required autocomplete="new-password" />
                    <button type="button" class="toggle-password" tabindex="-1">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>

                <div class="password-strength" id="phone-password-strength">
                    <div class="strength-bar"><span></span></div>
                    <div class="strength-text"></div>
                </div>

                <div class="password-check-list" id="phone-password-check-list">
                    <p><?php esc_html_e( '密码必须满足以下条件：', 'developer-starter' ); ?></p>
                    <ul class="requirements-list">
                        <?php if ( $password_strength === 'weak' ) : ?>
                            <li data-rule="min-6" class="pending"><span class="icon">○</span> <?php esc_html_e( '至少6个字符', 'developer-starter' ); ?></li>
                        <?php elseif ( $password_strength === 'medium' ) : ?>
                            <li data-rule="min-8" class="pending"><span class="icon">○</span> <?php esc_html_e( '至少8个字符', 'developer-starter' ); ?></li>
                            <li data-rule="letter" class="pending"><span class="icon">○</span> <?php esc_html_e( '包含字母', 'developer-starter' ); ?></li>
                            <li data-rule="number" class="pending"><span class="icon">○</span> <?php esc_html_e( '包含数字', 'developer-starter' ); ?></li>
                        <?php elseif ( $password_strength === 'strong' ) : ?>
                            <li data-rule="min-10" class="pending"><span class="icon">○</span> <?php esc_html_e( '至少10个字符', 'developer-starter' ); ?></li>
                            <li data-rule="upper" class="pending"><span class="icon">○</span> <?php esc_html_e( '大写字母', 'developer-starter' ); ?></li>
                            <li data-rule="lower" class="pending"><span class="icon">○</span> <?php esc_html_e( '小写字母', 'developer-starter' ); ?></li>
                            <li data-rule="number" class="pending"><span class="icon">○</span> <?php esc_html_e( '数字', 'developer-starter' ); ?></li>
                            <li data-rule="special" class="pending"><span class="icon">○</span> <?php esc_html_e( '特殊字符', 'developer-starter' ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="form-message" id="phone-form-message"></div>
                
                <button type="submit" class="auth-submit" id="phone-submit-btn">
                    <span class="btn-text"><?php esc_html_e( '重置密码', 'developer-starter' ); ?></span>
                    <span class="btn-loading">
                        <svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                    </span>
                </button>
            </form>
            <?php endif; ?>
            
            <?php endif; ?>
            
            <?php if ( $login_page_id ) : ?>
            <div class="auth-footer">
                <p><a href="<?php echo esc_url( get_permalink( $login_page_id ) ); ?>"><?php esc_html_e( '← 返回登录', 'developer-starter' ); ?></a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$auth_page_config = array(
    'page'        => 'forgot',
    'isResetMode' => (bool) $is_reset_mode,
    'smsEnable'   => (bool) $sms_enable,
    'ajaxUrl'     => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
    'authNonce'   => wp_create_nonce( 'developer_starter_auth' ),
    'smsNonce'    => $auth_page_sms_nonce,
    'loginUrl'    => $login_page_id ? esc_url_raw( get_permalink( $login_page_id ) ) : esc_url_raw( home_url() ),
    'homeUrl'     => esc_url_raw( home_url() ),
    'i18n'        => array(
        'sendCode'            => __( '获取验证码', 'developer-starter' ),
        'sending'             => __( '发送中...', 'developer-starter' ),
        'phoneInvalid'        => __( '请输入正确的手机号', 'developer-starter' ),
        'smsCodeInvalid'      => __( '请输入6位验证码', 'developer-starter' ),
        'phonePasswordMin'    => __( '密码至少6位', 'developer-starter' ),
        'networkErrorShort'   => __( '网络错误', 'developer-starter' ),
        'networkErrorText'    => __( '网络错误，请稍后再试', 'developer-starter' ),
        'passwordPerfect'     => __( '完美', 'developer-starter' ),
        'passwordMedium'      => __( '一般', 'developer-starter' ),
        'passwordWeak'        => __( '太弱', 'developer-starter' ),
        'forgotPhoneSubtitle' => __( '通过手机验证码重置密码', 'developer-starter' ),
        'forgotEmailSubtitle' => __( '输入您的注册邮箱，我们将发送重置链接', 'developer-starter' ),
    ),
);
$auth_page_config_json = wp_json_encode( $auth_page_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
if ( ! is_string( $auth_page_config_json ) ) {
    $auth_page_config_json = '{}';
}
?>
<script type="application/json" id="ds-auth-page-config"><?php echo $auth_page_config_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is hex-encoded for safe script data output. ?></script>

<?php get_footer(); ?>
