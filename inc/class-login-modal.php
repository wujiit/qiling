<?php
/**
 * 登录弹窗功能
 * 
 * 提供顶部登录弹窗的 HTML 与前端配置输出
 * 支持账号密码登录、手机短信验证码登录和弹窗注册
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 输出顶部登录弹窗内容
 *
 * 供 Ajax 按需返回登录弹窗的 HTML 与 JSON 配置。
 */
function developer_starter_output_login_modal() {
    $header_login_enable = developer_starter_get_option( 'header_login_enable', '' );
    
    // 只有启用了顶部登录按钮且用户未登录时才输出
    if ( ! $header_login_enable || is_user_logged_in() ) {
        return;
    }
    
    $captcha_enable = developer_starter_get_option( 'auth_captcha_enable', '' );
    $remember_me_enable = developer_starter_get_option( 'login_remember_me_enable', '' ) === '1';
    $register_page_id = developer_starter_get_option( 'register_page_id', '' );
    $forgot_page_id = developer_starter_get_option( 'forgot_password_page_id', '' );
    $can_register = get_option( 'users_can_register' );
    $modal_auth_nonce = wp_create_nonce( 'developer_starter_auth' );
    $modal_sms_nonce = wp_create_nonce( 'sms_nonce' );

    // SMS设置
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
    $modal_default_phone = $sms_enable && ( $sms_default_phone || $sms_phone_only );

    // 微信登录（需启灵微信登录插件）
    $weixin_login_enable = developer_starter_get_option( 'weixin_login_enable', '' ) === '1';
    $weixin_login_available = function_exists( 'developer_starter_is_weixin_registration_allowed' )
        ? developer_starter_is_weixin_registration_allowed()
        : ( $weixin_login_enable && class_exists( 'qiling_weixin_login' ) );
    $weixin_login_default = developer_starter_get_option( 'weixin_login_default', '' ) === '1';
    $weixin_default_effective = $weixin_login_available && $weixin_login_default && ! $modal_default_phone && ! $sms_phone_only;
    $can_register = $can_register && ( $email_register_enabled || $phone_register_enabled || $weixin_login_available );
    $can_email_register = $can_register && $email_register_enabled;
    $can_phone_register = $can_register && $phone_register_enabled;
    $can_weixin_register = $can_register && $weixin_login_available;

    $weixin_icon_raw = developer_starter_get_option( 'weixin_login_icon', '' );
    $weixin_icon = trim( $weixin_icon_raw );
    if ( ! empty( $weixin_icon ) && strpos( $weixin_icon, ' ' ) !== false ) {
        $parts = preg_split( '/\\s+/', $weixin_icon );
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
        $social_login_buttons_html = \Developer_Starter\Core\Social\Manager::get_instance()->render_buttons( 'login-modal' );
    }
    $social_login_available = '' !== $social_login_buttons_html;
    
    // 注册协议设置
    $agreement_enable = developer_starter_get_option( 'register_agreement_enable', '' );
    $agreement_text = developer_starter_get_option( 'register_agreement_text', __( '我已阅读并同意', 'developer-starter' ) );
    $agreement_link_text = developer_starter_get_option( 'register_agreement_link_text', __( '《用户服务协议》', 'developer-starter' ) );
    $agreement_url = developer_starter_get_option( 'register_agreement_url', '' );
    
    // 自定义提示信息
    $auth_custom_notice = developer_starter_get_option( 'auth_custom_notice', '' );
    $register_username_chinese_policy = developer_starter_get_option( 'register_username_chinese_policy', 'allow' );
    if ( ! in_array( $register_username_chinese_policy, array( 'allow', 'deny', 'scan' ), true ) ) {
        $register_username_chinese_policy = 'allow';
    }
    $register_code_enabled = function_exists( 'qilingshop_registration_code_is_enabled' ) && qilingshop_registration_code_is_enabled();
    $register_code_obtain_link = function_exists( 'qilingshop_get_registration_code_obtain_link' )
        ? qilingshop_get_registration_code_obtain_link()
        : array();
    $register_code_obtain_enabled = ! empty( $register_code_obtain_link['enabled'] ) && ! empty( $register_code_obtain_link['url'] );
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
    $email_domain_whitelist = function_exists( 'developer_starter_get_email_domain_whitelist' )
        ? developer_starter_get_email_domain_whitelist()
        : array();
    $email_domain_whitelist_text = function_exists( 'developer_starter_get_email_domain_whitelist_text' )
        ? developer_starter_get_email_domain_whitelist_text( '、' )
        : '';
    $auth_modal_side_image = developer_starter_get_media_url( developer_starter_get_option( 'auth_modal_side_image', '' ) );
    $has_auth_modal_side_image = $auth_modal_side_image !== '';
    ?>
    <!-- 顶部登录注册弹窗 -->
    <div class="login-modal-overlay" id="login-modal-overlay"></div>
    <div class="login-modal<?php echo $has_auth_modal_side_image ? ' with-side-media' : ''; ?>" id="login-modal">
        <div class="login-modal-header<?php echo $weixin_login_available ? ' has-corner-toggle' : ''; ?>">
            <h3 id="modal-title"><?php esc_html_e( '用户登录', 'developer-starter' ); ?></h3>
            <?php if ( $weixin_login_available ) : ?>
            <button type="button" class="login-weixin-toggle" id="login-weixin-toggle" aria-label="<?php esc_attr_e( '微信扫码登录', 'developer-starter' ); ?>">
                <span class="corner-icon weixin-icon"></span>
                <span class="corner-icon pc-icon"></span>
            </button>
            <?php endif; ?>
        </div>
        <div class="login-modal-body<?php echo $has_auth_modal_side_image ? ' has-side-media' : ''; ?>">
            <?php if ( $has_auth_modal_side_image ) : ?>
            <div class="login-modal-side-media" aria-hidden="true">
                <img src="<?php echo esc_url( $auth_modal_side_image ); ?>" alt="" loading="lazy" />
            </div>
            <?php endif; ?>
            <div class="login-modal-content">
            <!-- ========== 登录面板 ========== -->
            <div id="login-panel">
                <?php if ( $sms_enable && ! $sms_phone_only ) : ?>
                <!-- 登录方式切换 -->
                <div class="modal-login-tabs">
                    <button type="button" class="modal-tab <?php echo $modal_default_phone ? 'active' : ''; ?>" data-tab="phone"><?php esc_html_e( '手机号', 'developer-starter' ); ?></button>
                    <button type="button" class="modal-tab <?php echo ! $modal_default_phone ? 'active' : ''; ?>" data-tab="account"><?php esc_html_e( '账号密码', 'developer-starter' ); ?></button>
                </div>
                <?php endif; ?>
                
                <?php if ( $sms_enable ) : ?>
                <!-- 手机号登录表单 -->
                <form id="header-phone-form" class="login-modal-form <?php echo ( $sms_phone_only || $modal_default_phone ) ? '' : 'hidden'; ?>" novalidate>
                    <div class="modal-form-group">
                        <input type="tel" id="header-phone" name="phone" placeholder="<?php esc_attr_e( '请输入手机号', 'developer-starter' ); ?>" required autocomplete="tel" maxlength="11" />
                    </div>
                    <div class="modal-form-group modal-sms-group">
                        <input type="text" id="header-sms-code" name="code" placeholder="<?php esc_attr_e( '请输入验证码', 'developer-starter' ); ?>" required maxlength="6" autocomplete="one-time-code" />
                        <button type="button" class="modal-sms-btn" id="header-sms-btn"><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
                    </div>

                    <?php if ( $register_code_enabled ) : ?>
                    <div class="modal-form-group">
                        <input type="text" id="header-registration-code" name="registration_code" placeholder="<?php esc_attr_e( '注册码（新用户必填）', 'developer-starter' ); ?>" autocomplete="off" />
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
                    <div class="modal-form-group">
                        <div class="slider-captcha modal-captcha" id="header-phone-slider-captcha">
                            <div class="captcha-track">
                                <div class="captcha-slider">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </div>
                                <div class="captcha-progress"></div>
                                <span class="captcha-text"><?php esc_html_e( '向右滑动验证', 'developer-starter' ); ?></span>
                            </div>
                        </div>
                        <input type="hidden" name="captcha_verified" id="header-phone-captcha-verified" value="false" />
                    </div>
                    <?php endif; ?>
                    
                    <div class="modal-form-message" id="header-phone-message"></div>
                    
                    <button type="submit" class="login-modal-submit" id="header-phone-submit">
                        <span class="btn-text" id="header-phone-submit-text"><?php esc_html_e( '登 录', 'developer-starter' ); ?></span>
                        <span class="modal-btn-loading">
                            <svg class="modal-spinner" viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                        </span>
                    </button>
                    
                    <?php if ( $auth_custom_notice ) : ?>
                    <div class="auth-custom-notice">
                        <?php echo wp_kses_post( $auth_custom_notice ); ?>
                    </div>
                    <?php endif; ?>
                    
                    <input type="hidden" name="redirect_to" id="header-phone-redirect-to" value="" />
                </form>
                <?php endif; ?>
                
                <?php if ( ! $sms_phone_only ) : ?>
                <!-- 账号密码登录表单 -->
                <form id="header-login-form" class="login-modal-form <?php echo ( $sms_enable && $modal_default_phone ) ? 'hidden' : ''; ?>" novalidate>
                    <div class="modal-form-group">
                        <input type="text" id="header-username" name="username" placeholder="<?php esc_attr_e( '用户名或邮箱', 'developer-starter' ); ?>" required autocomplete="username" />
                    </div>
                    <div class="modal-form-group">
                        <input type="password" id="header-password" name="password" placeholder="<?php esc_attr_e( '密码', 'developer-starter' ); ?>" required autocomplete="current-password" />
                    </div>
                    
                    <?php if ( $remember_me_enable ) : ?>
                    <div class="modal-form-group modal-remember-group">
                        <label class="modal-remember-label">
                            <input type="checkbox" name="remember" value="true" class="modal-remember-checkbox" />
                            <?php esc_html_e( '记住我', 'developer-starter' ); ?>
                        </label>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $captcha_enable ) : ?>
                    <div class="modal-form-group">
                        <div class="slider-captcha modal-captcha" id="header-slider-captcha">
                            <div class="captcha-track">
                                <div class="captcha-slider">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </div>
                                <div class="captcha-progress"></div>
                                <span class="captcha-text"><?php esc_html_e( '向右滑动验证', 'developer-starter' ); ?></span>
                            </div>
                        </div>
                        <input type="hidden" name="captcha_verified" id="header-captcha-verified" value="false" />
                    </div>
                    <?php endif; ?>
                    
                    <div class="modal-form-message" id="header-form-message"></div>
                    
                    <button type="submit" class="login-modal-submit" id="header-login-submit">
                        <span class="btn-text"><?php esc_html_e( '登 录', 'developer-starter' ); ?></span>
                        <span class="modal-btn-loading">
                            <svg class="modal-spinner" viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                        </span>
                    </button>
                    
                    <?php if ( $auth_custom_notice ) : ?>
                    <div class="auth-custom-notice">
                        <?php echo wp_kses_post( $auth_custom_notice ); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php wp_nonce_field( 'developer_starter_auth', 'header_auth_nonce' ); ?>
                    <input type="hidden" name="redirect_to" id="header-redirect-to" value="" />
                </form>
                <?php endif; ?>
                
                <?php if ( $weixin_login_available || $social_login_available ) : ?>
                <div class="login-modal-social">
                    <?php echo $social_login_buttons_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php if ( $weixin_login_available ) : ?>
                        <button type="button" class="social-btn social-weixin" id="login-weixin-btn">
                            <?php if ( $weixin_icon_html ) : ?>
                                <?php echo $weixin_icon_html; ?>
                            <?php else : ?>
                                <span class="social-icon"></span>
                            <?php endif; ?>
                            <span id="login-weixin-btn-text"><?php esc_html_e( '微信扫码登录', 'developer-starter' ); ?></span>
                        </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="login-modal-footer">
                    <?php if ( $can_register ) : ?>
                        <a href="javascript:;" class="login-modal-register-link" id="show-register-panel"><?php esc_html_e( '注册账号', 'developer-starter' ); ?></a>
                        <a href="javascript:;" class="login-modal-register-link hidden" id="show-login-inline-panel"><?php esc_html_e( '已有账号？立即登录', 'developer-starter' ); ?></a>
                    <?php endif; ?>
                    <?php if ( $forgot_page_id ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $forgot_page_id ) ); ?>" class="login-modal-forgot-link" id="login-modal-forgot-link"><?php esc_html_e( '忘记密码？', 'developer-starter' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ( $weixin_login_available ) : ?>
            <!-- ========== 微信扫码面板 ========== -->
            <div id="weixin-login-panel" class="login-modal-panel-hidden">
                <div class="weixin-login-wrap">
                    <?php echo do_shortcode( '[qiling_weixin_login mode="auto" autoload="0" layout="embed"]' ); ?>
                </div>
                <div class="login-modal-footer">
                    <a href="javascript:;" id="weixin-back-btn"><?php esc_html_e( '返回账号登录', 'developer-starter' ); ?></a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- ========== 注册面板 ========== -->
            <?php if ( $can_email_register ) : ?>
            <div id="register-panel" class="login-modal-panel-hidden">
                <form id="header-register-form" class="login-modal-form" novalidate>
                    <div class="modal-form-group">
                        <input type="text" id="reg-username" name="username" placeholder="<?php esc_attr_e( '用户名（至少3个字符）', 'developer-starter' ); ?>" required autocomplete="username" />
                    </div>
                    <div class="modal-form-group">
                        <input type="email" id="reg-email" name="email" placeholder="<?php esc_attr_e( '邮箱地址', 'developer-starter' ); ?>" required autocomplete="email" />
                    </div>
                    <?php if ( $email_domain_whitelist_text !== '' ) : ?>
                    <p class="email-whitelist-tip">
                        <?php echo esc_html( sprintf( __( '仅支持以下邮箱后缀：%s', 'developer-starter' ), $email_domain_whitelist_text ) ); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ( $register_email_code_enabled ) : ?>
                    <div class="modal-form-group modal-sms-group">
                        <input type="text" id="reg-email-code" name="email_code" placeholder="<?php esc_attr_e( '请输入邮箱验证码', 'developer-starter' ); ?>" required maxlength="6" autocomplete="one-time-code" inputmode="numeric" />
                        <button type="button" class="modal-sms-btn" id="reg-email-send-btn"><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
                    </div>
                    <p class="email-whitelist-tip">
                        <?php echo esc_html( sprintf( __( '验证码有效期 %d 分钟', 'developer-starter' ), (int) $register_email_code_expire_minutes ) ); ?>
                    </p>
                    <?php endif; ?>
                    <div class="modal-form-group">
                        <input type="password" id="reg-password" name="password" placeholder="<?php esc_attr_e( '密码', 'developer-starter' ); ?>" required autocomplete="new-password" />
                    </div>
                    <div class="modal-form-group">
                        <input type="password" id="reg-password-confirm" name="password_confirm" placeholder="<?php esc_attr_e( '确认密码', 'developer-starter' ); ?>" required autocomplete="new-password" />
                    </div>

                    <?php if ( $register_code_enabled ) : ?>
                    <div class="modal-form-group">
                        <input type="text" id="reg-registration-code" name="registration_code" placeholder="<?php esc_attr_e( '请输入注册码', 'developer-starter' ); ?>" autocomplete="off" />
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
                    <div class="modal-form-group">
                        <div class="slider-captcha modal-captcha" id="reg-slider-captcha">
                            <div class="captcha-track">
                                <div class="captcha-slider">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </div>
                                <div class="captcha-progress"></div>
                                <span class="captcha-text"><?php esc_html_e( '向右滑动验证', 'developer-starter' ); ?></span>
                            </div>
                        </div>
                        <input type="hidden" name="captcha_verified" id="reg-captcha-verified" value="false" />
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $agreement_enable ) : ?>
                    <div class="modal-form-group modal-agreement-group">
                        <label class="modal-agreement-label">
                            <input type="checkbox" name="agreement" id="reg-agreement" value="1" />
                            <span class="modal-checkmark"></span>
                            <span class="modal-agreement-text">
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
                    
                    <div class="modal-form-message" id="reg-form-message"></div>
                    
                    <button type="submit" class="login-modal-submit" id="reg-submit-btn">
                        <span class="btn-text"><?php esc_html_e( '立即注册', 'developer-starter' ); ?></span>
                        <span class="modal-btn-loading">
                            <svg class="modal-spinner" viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                        </span>
                    </button>
                    
                    <?php if ( $auth_custom_notice ) : ?>
                    <div class="auth-custom-notice">
                        <?php echo wp_kses_post( $auth_custom_notice ); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php wp_nonce_field( 'developer_starter_auth', 'reg_auth_nonce' ); ?>
                    <input type="hidden" name="redirect_to" id="reg-redirect-to" value="" />
                </form>
                
                <div class="login-modal-footer">
                    <a href="javascript:;" id="show-login-panel"><?php esc_html_e( '已有账号？立即登录', 'developer-starter' ); ?></a>
                </div>
            </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    $assets_version = function_exists( 'developer_starter_get_assets_version' )
        ? (string) developer_starter_get_assets_version()
        : (string) DEVELOPER_STARTER_VERSION;
    $captcha_provider = (string) developer_starter_get_option( 'captcha_provider', 'theme' );
    if ( ! in_array( $captcha_provider, array( 'theme', 'aliyun' ), true ) ) {
        $captcha_provider = 'theme';
    }
    $captcha_aliyun_prefix = trim( (string) developer_starter_get_option( 'aliyun_captcha_prefix', '' ) );
    $captcha_aliyun_scene_auth = trim( (string) developer_starter_get_option( 'aliyun_captcha_scene_auth', '' ) );
    $captcha_aliyun_scene_search = trim( (string) developer_starter_get_option( 'aliyun_captcha_scene_search', '' ) );
    $captcha_aliyun_client_region = trim( (string) developer_starter_get_option( 'aliyun_captcha_client_region', '' ) );
    if ( ! in_array( $captcha_aliyun_client_region, array( 'cn', 'sgp' ), true ) ) {
        $region_raw = strtolower( trim( (string) developer_starter_get_option( 'aliyun_captcha_region', '' ) ) );
        $endpoint_raw = strtolower( trim( (string) developer_starter_get_option( 'aliyun_captcha_endpoint', '' ) ) );
        if ( false !== strpos( $region_raw, 'sgp' ) || false !== strpos( $endpoint_raw, 'ap-southeast-1' ) ) {
            $captcha_aliyun_client_region = 'sgp';
        } else {
            $captcha_aliyun_client_region = 'cn';
        }
    }

    $login_modal_config = array(
        'defaultPhone'              => (bool) $modal_default_phone,
        'smsPhoneOnly'              => (bool) $sms_phone_only,
        'defaultWeixin'             => (bool) $weixin_default_effective,
        'canEmailRegister'          => (bool) $can_email_register,
        'canPhoneRegister'          => (bool) $can_phone_register,
        'canWeixinRegister'         => (bool) $can_weixin_register,
        'authNonce'                 => $modal_auth_nonce,
        'smsNonce'                  => $modal_sms_nonce,
        'registerCodeEnabled'       => (bool) $register_code_enabled,
        'registerUsernameChinesePolicy' => (string) $register_username_chinese_policy,
        'registerEmailCodeEnabled'  => (bool) $register_email_code_enabled,
        'registerEmailCodeInterval' => (int) $register_email_code_interval,
        'emailDomainWhitelist'      => array_values( $email_domain_whitelist ),
        'emailDomainWhitelistText'  => (string) $email_domain_whitelist_text,
        'ajaxUrl'                   => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
        'authFlow'                  => array(
            'captchaChallengeAction' => 'developer_starter_captcha_challenge',
            'captchaVerifyAction'    => 'developer_starter_captcha_verify',
            'i18n'                   => array(
                'captchaInitFailedText' => __( '验证初始化失败，请重试', 'developer-starter' ),
                'dragText'              => __( '向右滑动完成验证', 'developer-starter' ),
                'networkErrorShort'     => __( '网络错误', 'developer-starter' ),
                'networkErrorText'      => __( '网络错误，请稍后再试', 'developer-starter' ),
                'sendCodeText'          => __( '获取验证码', 'developer-starter' ),
            ),
        ),
        'captchaProviderScript'      => add_query_arg(
            'ver',
            rawurlencode( $assets_version ),
            DEVELOPER_STARTER_ASSETS . '/js/captcha-provider.js'
        ),
        'captcha'                   => array(
            'provider'      => $captcha_provider,
            'verifyAction'  => 'developer_starter_captcha_verify',
            'verifyNonce'   => $modal_auth_nonce,
            'aliyunScript'  => 'https://o.alicdn.com/captcha-frontend/aliyunCaptcha/AliyunCaptcha.js',
            'aliyunPrefix'  => $captcha_aliyun_prefix,
            'aliyunRegion'  => $captcha_aliyun_client_region,
            'sceneAuth'     => $captcha_aliyun_scene_auth,
            'sceneSearch'   => $captcha_aliyun_scene_search,
            'i18n'          => array(
                'configErrorText' => __( '验证码配置不完整', 'developer-starter' ),
                'waitingText'     => __( '点击完成验证', 'developer-starter' ),
                'successText'     => __( '验证成功', 'developer-starter' ),
                'failedText'      => __( '验证失败，请重试', 'developer-starter' ),
                'verifyingText'   => __( '正在验证...', 'developer-starter' ),
                'buttonText'      => __( '点击验证', 'developer-starter' ),
                'loadFailedText'  => __( '验证码脚本加载失败，请检查网络', 'developer-starter' ),
            ),
        ),
        'i18n'                      => array(
            'loginSubmit'              => __( '登 录', 'developer-starter' ),
            'registerSubmit'           => __( '立即注册', 'developer-starter' ),
            'loginTitle'               => __( '用户登录', 'developer-starter' ),
            'phoneRegisterTitle'       => __( '手机号注册', 'developer-starter' ),
            'registerTitle'            => __( '创建账户', 'developer-starter' ),
            'weixinLogin'              => __( '微信扫码登录', 'developer-starter' ),
            'weixinRegister'           => __( '微信扫码注册', 'developer-starter' ),
            'sendCode'                 => __( '获取验证码', 'developer-starter' ),
            'sending'                  => __( '发送中...', 'developer-starter' ),
            'captchaRequired'          => __( '请先完成验证，再获取验证码', 'developer-starter' ),
            'phoneInvalid'             => __( '请输入正确的手机号', 'developer-starter' ),
            'smsCodeInvalid'           => __( '请输入6位验证码', 'developer-starter' ),
            'emailInvalid'             => __( '请输入正确的邮箱地址', 'developer-starter' ),
            'emailWhitelistPrefix'     => __( '当前仅支持以下邮箱后缀：', 'developer-starter' ),
            'emailNotAllowed'          => __( '当前邮箱后缀不在允许范围内', 'developer-starter' ),
            'emailCodeInvalid'         => __( '请输入6位邮箱验证码', 'developer-starter' ),
            'passwordLength'           => __( '密码至少需要6个字符', 'developer-starter' ),
            'passwordMismatch'         => __( '两次输入的密码不一致', 'developer-starter' ),
            'registrationCodeRequired' => __( '请输入注册码', 'developer-starter' ),
            'usernameLength'           => __( '用户名至少需要3个字符', 'developer-starter' ),
            'usernameChineseDisallowed' => __( '用户名不支持中文，请使用字母、数字或下划线', 'developer-starter' ),
            'sendFailed'               => __( '发送失败，请稍后重试', 'developer-starter' ),
            'networkError'             => __( '网络错误', 'developer-starter' ),
            'networkErrorText'         => __( '网络错误，请稍后再试', 'developer-starter' ),
        ),
    );
    $login_modal_config_json = wp_json_encode( $login_modal_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
    if ( ! is_string( $login_modal_config_json ) ) {
        $login_modal_config_json = '{}';
    }
    ?>
    <script type="application/json" id="ds-login-modal-config"><?php echo $login_modal_config_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is hex-encoded for safe script data output. ?></script>
    <?php
}

/**
 * 获取登录弹窗 HTML。
 *
 * @return string
 */
function developer_starter_get_login_modal_html() {
    ob_start();
    developer_starter_output_login_modal();
    return (string) ob_get_clean();
}

/**
 * Ajax 获取登录弹窗内容。
 *
 * @return void
 */
function developer_starter_ajax_get_login_modal() {
    if (
        function_exists( 'developer_starter_is_public_ajax_rate_limited' )
        && developer_starter_is_public_ajax_rate_limited( 'login_modal', 30, 60 )
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

    nocache_headers();

    if ( is_user_logged_in() || ! developer_starter_get_option( 'header_login_enable', '' ) ) {
        wp_send_json_error( array( 'message' => 'disabled' ), 404 );
    }

    $html = trim( developer_starter_get_login_modal_html() );
    if ( '' === $html ) {
        wp_send_json_error( array( 'message' => 'empty' ), 404 );
    }

    wp_send_json_success(
        array(
            'html' => $html,
        )
    );
}

add_action( 'wp_ajax_developer_starter_get_login_modal', 'developer_starter_ajax_get_login_modal' );
add_action( 'wp_ajax_nopriv_developer_starter_get_login_modal', 'developer_starter_ajax_get_login_modal' );
