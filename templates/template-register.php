<?php
/**
 * Template Name: 用户注册
 * Template Post Type: page
 * 
 * @package Developer_Starter
 */

// 已登录用户跳转
if ( is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

// 检查是否开放注册
if ( ! get_option( 'users_can_register' ) ) {
    wp_redirect( home_url() );
    exit;
}

get_header();

$captcha_enable = developer_starter_get_option( 'auth_captcha_enable', '' );
$login_page_id = developer_starter_get_option( 'login_page_id', '' );
$password_strength = developer_starter_get_option( 'password_strength', 'medium' );
$register_username_chinese_policy = developer_starter_get_option( 'register_username_chinese_policy', 'allow' );
if ( ! in_array( $register_username_chinese_policy, array( 'allow', 'deny', 'scan' ), true ) ) {
    $register_username_chinese_policy = 'allow';
}

// SMS设置
$sms_enable = developer_starter_get_option( 'sms_enable', '' ) === '1';
$sms_phone_only = function_exists( 'developer_starter_is_sms_phone_only_effective' )
    ? developer_starter_is_sms_phone_only_effective()
    : ( developer_starter_get_option( 'sms_phone_only', '' ) === '1' );
$email_register_enabled = function_exists( 'developer_starter_is_email_registration_allowed' )
    ? developer_starter_is_email_registration_allowed()
    : ! $sms_phone_only;
$phone_register_enabled = function_exists( 'developer_starter_is_phone_registration_allowed' )
    ? developer_starter_is_phone_registration_allowed()
    : $sms_enable;
$phone_register_only = $phone_register_enabled && ! $email_register_enabled;
$email_domain_whitelist = function_exists( 'developer_starter_get_email_domain_whitelist' )
    ? developer_starter_get_email_domain_whitelist()
    : array();
$email_domain_whitelist_text = function_exists( 'developer_starter_get_email_domain_whitelist_text' )
    ? developer_starter_get_email_domain_whitelist_text( '、' )
    : '';
$register_email_code_enabled = function_exists( 'developer_starter_is_register_email_code_enabled' )
    ? developer_starter_is_register_email_code_enabled()
    : ( developer_starter_get_option( 'register_email_code_enable', '' ) === '1' );
$register_email_code_interval = function_exists( 'developer_starter_get_register_email_code_interval_seconds' )
    ? developer_starter_get_register_email_code_interval_seconds()
    : absint( developer_starter_get_option( 'register_email_code_interval', 60 ) );
if ( $register_email_code_interval < 30 ) {
    $register_email_code_interval = 60;
}
$register_email_code_expire_minutes = function_exists( 'developer_starter_get_register_email_code_expire_minutes' )
    ? developer_starter_get_register_email_code_expire_minutes()
    : absint( developer_starter_get_option( 'register_email_code_expire', 10 ) );
if ( $register_email_code_expire_minutes < 1 ) {
    $register_email_code_expire_minutes = 10;
}

// 自定义提示信息
$auth_custom_notice = developer_starter_get_option( 'auth_custom_notice', '' );
$register_code_enabled = function_exists( 'qilingshop_registration_code_is_enabled' ) && qilingshop_registration_code_is_enabled();
$register_code_obtain_link = function_exists( 'qilingshop_get_registration_code_obtain_link' )
    ? qilingshop_get_registration_code_obtain_link()
    : array();
$register_code_obtain_enabled = ! empty( $register_code_obtain_link['enabled'] ) && ! empty( $register_code_obtain_link['url'] );
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
                <h2 class="auth-title"><?php esc_html_e( '创建账户', 'developer-starter' ); ?></h2>
                <p class="auth-subtitle"><?php esc_html_e( '加入我们，开启精彩之旅', 'developer-starter' ); ?></p>
            </div>
            
            <?php if ( $phone_register_only ) : ?>
            <!-- 手机号注册表单（仅手机号模式） -->
            <form id="phone-register-form" class="auth-form" novalidate>
                <div class="form-group">
                    <label for="reg-phone">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </label>
                    <input type="tel" id="reg-phone" name="phone" placeholder="<?php esc_attr_e( '请输入手机号', 'developer-starter' ); ?>" required autocomplete="tel" maxlength="11" />
                </div>
                
                <div class="form-group sms-code-group">
                    <label for="reg-code">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </label>
                    <input type="text" id="reg-code" name="code" placeholder="<?php esc_attr_e( '请输入验证码', 'developer-starter' ); ?>" required maxlength="6" autocomplete="one-time-code" />
                    <button type="button" class="sms-send-btn" id="reg-send-btn"><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
                </div>

                <?php if ( $register_code_enabled ) : ?>
                <div class="form-group">
                    <label for="phone-registration-code">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="13" y2="13"/></svg>
                    </label>
                    <input type="text" id="phone-registration-code" name="registration_code" placeholder="<?php esc_attr_e( '请输入注册码（新用户必填）', 'developer-starter' ); ?>" autocomplete="off" />
                </div>
                <?php if ( $register_code_obtain_enabled ) : ?>
                <p class="email-whitelist-tip register-code-obtain-tip">
                    <?php if ( ! empty( $register_code_obtain_link['tip_text'] ) ) : ?>
                        <span><?php echo esc_html( $register_code_obtain_link['tip_text'] ); ?></span>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $register_code_obtain_link['url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow">
                        <?php echo esc_html( ! empty( $register_code_obtain_link['link_text'] ) ? $register_code_obtain_link['link_text'] : __( '注册码获取', 'developer-starter' ) ); ?>
                    </a>
                </p>
                <?php endif; ?>
                <?php endif; ?>

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
                
                <?php 
                $agreement_enable = developer_starter_get_option( 'register_agreement_enable', '' );
                if ( $agreement_enable ) :
                    $agreement_text = developer_starter_get_option( 'register_agreement_text', __( '我已阅读并同意', 'developer-starter' ) );
                    $agreement_link_text = developer_starter_get_option( 'register_agreement_link_text', __( '《用户服务协议》', 'developer-starter' ) );
                    $agreement_url = developer_starter_get_option( 'register_agreement_url', '' );
                ?>
                <div class="form-group agreement-group">
                    <label class="agreement-label">
                        <input type="checkbox" name="agreement" id="phone-agreement" value="1" />
                        <span class="checkmark"></span>
                        <span class="agreement-text">
                            <?php echo esc_html( $agreement_text ); ?>
                            <?php if ( $agreement_url ) : ?>
                                <a href="<?php echo esc_url( $agreement_url ); ?>" target="_blank"><?php echo esc_html( $agreement_link_text ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $agreement_link_text ); ?>
                            <?php endif; ?>
                        </span>
                    </label>
                </div>
                <?php endif; ?>
                
                <div class="form-message" id="phone-form-message"></div>
                
                <button type="submit" class="auth-submit" id="phone-submit-btn">
                    <span class="btn-text"><?php esc_html_e( '立即注册', 'developer-starter' ); ?></span>
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
            
            <?php elseif ( $email_register_enabled ) : ?>
            <!-- 普通注册表单 -->
            <form id="register-form" class="auth-form" novalidate>
                <div class="form-group">
                    <label for="username">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </label>
                    <input type="text" id="username" name="username" placeholder="<?php esc_attr_e( '用户名（至少3个字符）', 'developer-starter' ); ?>" required autocomplete="username" />
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </label>
                    <input type="email" id="email" name="email" placeholder="<?php esc_attr_e( '邮箱地址', 'developer-starter' ); ?>" required autocomplete="email" />
                </div>
                <?php if ( $email_domain_whitelist_text !== '' ) : ?>
                <p class="email-whitelist-tip">
                    <?php echo esc_html( sprintf( __( '仅支持以下邮箱后缀：%s', 'developer-starter' ), $email_domain_whitelist_text ) ); ?>
                </p>
                <?php endif; ?>
                <?php if ( $register_email_code_enabled ) : ?>
                <div class="form-group sms-code-group">
                    <label for="email_code">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </label>
                    <input type="text" id="email_code" name="email_code" placeholder="<?php esc_attr_e( '请输入邮箱验证码', 'developer-starter' ); ?>" required maxlength="6" autocomplete="one-time-code" inputmode="numeric" />
                    <button type="button" class="sms-send-btn" id="email-send-btn"><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
                </div>
                <p class="email-whitelist-tip">
                    <?php echo esc_html( sprintf( __( '验证码有效期 %d 分钟', 'developer-starter' ), (int) $register_email_code_expire_minutes ) ); ?>
                </p>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="password">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </label>
                    <input type="password" id="password" name="password" placeholder="<?php esc_attr_e( '密码', 'developer-starter' ); ?>" required autocomplete="new-password" />
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
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="<?php esc_attr_e( '确认密码', 'developer-starter' ); ?>" required autocomplete="new-password" />
                </div>

                <?php if ( $register_code_enabled ) : ?>
                <div class="form-group">
                    <label for="registration_code">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="13" y2="13"/></svg>
                    </label>
                    <input type="text" id="registration_code" name="registration_code" placeholder="<?php esc_attr_e( '请输入注册码', 'developer-starter' ); ?>" autocomplete="off" />
                </div>
                <?php if ( $register_code_obtain_enabled ) : ?>
                <p class="email-whitelist-tip register-code-obtain-tip">
                    <?php if ( ! empty( $register_code_obtain_link['tip_text'] ) ) : ?>
                        <span><?php echo esc_html( $register_code_obtain_link['tip_text'] ); ?></span>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $register_code_obtain_link['url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow">
                        <?php echo esc_html( ! empty( $register_code_obtain_link['link_text'] ) ? $register_code_obtain_link['link_text'] : __( '注册码获取', 'developer-starter' ) ); ?>
                    </a>
                </p>
                <?php endif; ?>
                <?php endif; ?>
                
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
                
                <?php 
                $agreement_enable = developer_starter_get_option( 'register_agreement_enable', '' );
                if ( $agreement_enable ) :
                    $agreement_text = developer_starter_get_option( 'register_agreement_text', __( '我已阅读并同意', 'developer-starter' ) );
                    $agreement_link_text = developer_starter_get_option( 'register_agreement_link_text', __( '《用户服务协议》', 'developer-starter' ) );
                    $agreement_url = developer_starter_get_option( 'register_agreement_url', '' );
                ?>
                <div class="form-group agreement-group">
                    <label class="agreement-label">
                        <input type="checkbox" name="agreement" id="agreement" value="1" />
                        <span class="checkmark"></span>
                        <span class="agreement-text">
                            <?php echo esc_html( $agreement_text ); ?>
                            <?php if ( $agreement_url ) : ?>
                                <a href="<?php echo esc_url( $agreement_url ); ?>" target="_blank"><?php echo esc_html( $agreement_link_text ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $agreement_link_text ); ?>
                            <?php endif; ?>
                        </span>
                    </label>
                </div>
                <?php endif; ?>
                
                <div class="form-message" id="form-message"></div>
                
                <button type="submit" class="auth-submit" id="submit-btn">
                    <span class="btn-text"><?php esc_html_e( '立即注册', 'developer-starter' ); ?></span>
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
            <?php else : ?>
            <div class="form-message error">
                <?php esc_html_e( '当前注册方式未启用邮箱或手机号注册，请通过微信登录或联系管理员调整设置。', 'developer-starter' ); ?>
            </div>
            <?php endif; ?>
            
            <?php if ( $login_page_id ) : ?>
            <div class="auth-footer">
                <p><?php esc_html_e( '已有账户？', 'developer-starter' ); ?><a href="<?php echo esc_url( get_permalink( $login_page_id ) ); ?>"><?php esc_html_e( '立即登录', 'developer-starter' ); ?></a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$auth_page_config = array(
    'page'                      => 'register',
    'isPhoneOnly'               => (bool) $phone_register_only,
    'registerCodeEnabled'       => (bool) $register_code_enabled,
    'registerUsernameChinesePolicy' => (string) $register_username_chinese_policy,
    'registerEmailCodeEnabled'  => (bool) $register_email_code_enabled,
    'registerEmailCodeInterval' => (int) $register_email_code_interval,
    'emailDomainWhitelist'      => array_values( $email_domain_whitelist ),
    'emailDomainWhitelistText'  => (string) $email_domain_whitelist_text,
    'ajaxUrl'                   => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
    'authNonce'                 => wp_create_nonce( 'developer_starter_auth' ),
    'smsNonce'                  => $auth_page_sms_nonce,
    'i18n'                      => array(
        'sendCode'                 => __( '获取验证码', 'developer-starter' ),
        'sending'                  => __( '发送中...', 'developer-starter' ),
        'phoneInvalid'             => __( '请输入正确的手机号', 'developer-starter' ),
        'smsCodeInvalid'           => __( '请输入6位验证码', 'developer-starter' ),
        'emailInvalid'             => __( '请输入正确的邮箱地址', 'developer-starter' ),
        'emailWhitelistPrefix'     => __( '当前仅支持以下邮箱后缀：', 'developer-starter' ),
        'emailNotAllowed'          => __( '当前邮箱后缀不在允许范围内', 'developer-starter' ),
        'emailCodeInvalid'         => __( '请输入6位邮箱验证码', 'developer-starter' ),
        'registrationCodeRequired' => __( '请输入注册码', 'developer-starter' ),
        'usernameChineseDisallowed' => __( '用户名不支持中文，请使用字母、数字或下划线', 'developer-starter' ),
        'captchaRequired'          => __( '请先完成验证，再获取验证码', 'developer-starter' ),
        'sendFailed'               => __( '发送失败，请稍后重试', 'developer-starter' ),
        'networkErrorShort'        => __( '网络错误', 'developer-starter' ),
        'networkErrorText'         => __( '网络错误，请稍后再试', 'developer-starter' ),
        'passwordPerfect'          => __( '完美', 'developer-starter' ),
        'passwordMedium'           => __( '一般', 'developer-starter' ),
        'passwordWeak'             => __( '太弱', 'developer-starter' ),
    ),
);
$auth_page_config_json = wp_json_encode( $auth_page_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
if ( ! is_string( $auth_page_config_json ) ) {
    $auth_page_config_json = '{}';
}
?>
<script type="application/json" id="ds-auth-page-config"><?php echo $auth_page_config_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is hex-encoded for safe script data output. ?></script>

<?php get_footer(); ?>
