<?php
/**
 * Template Name: 联系我们
 * Template Post Type: page
 *
 * @package Developer_Starter
 */

get_header();

$show_form     = developer_starter_get_option( 'contact_show_form', '1' );
$show_info     = developer_starter_get_option( 'contact_show_info', '1' );
$contact_image = developer_starter_get_option( 'contact_image', '' );
$contact_form_id = function_exists( 'developer_starter_get_explicit_contact_form_id' )
    ? absint( developer_starter_get_explicit_contact_form_id() )
    : 0;
$contact_login_required = developer_starter_get_option( 'contact_message_login_required', '' ) === '1';
$custom_login_page = (int) developer_starter_get_option( 'login_page_id', '' );
$contact_login_url = $custom_login_page ? get_permalink( $custom_login_page ) : wp_login_url( get_permalink() );
if ( ! $contact_login_url ) {
    $contact_login_url = wp_login_url( home_url( '/' ) );
}
$show_contact_login_hint = $contact_login_required && ! is_user_logged_in();

$company_name  = developer_starter_get_option( 'company_name', '' );
$phone         = developer_starter_get_option( 'company_phone', '' );
$qq            = developer_starter_get_option( 'company_qq', '' );
$qq_link       = function_exists( 'developer_starter_get_qq_contact_link' ) ? developer_starter_get_qq_contact_link( $qq ) : '';
$wechat_qrcode = developer_starter_get_option( 'company_wechat_qrcode', '' );
$email         = developer_starter_get_option( 'company_email', '' );
$address       = developer_starter_get_option( 'company_address', '' );
$working_hours = developer_starter_get_option( 'company_working_hours', '' );
?>

<?php \Developer_Starter\Core\Page_Header::render( 'default' ); ?>

<div class="page-content section-padding">
    <div class="container">

        <div class="contact-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--qiling-space-60); max-width: var(--qiling-measure-1100); margin: 0 auto;">

            <!-- 左侧：联系信息 -->
            <?php if ( $show_info ) : ?>
            <div class="contact-info-section">
                <h2 class="section-heading"><?php esc_html_e( '联系方式', 'developer-starter' ); ?></h2>

                <div class="contact-info-list" style="display: flex; flex-direction: column; gap: var(--qiling-space-25);">
                    <?php if ( $company_name ) : ?>
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: var(--qiling-space-20);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-violet-600) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 var(--qiling-space-5); color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); font-weight: 500;"><?php esc_html_e( '公司名称', 'developer-starter' ); ?></h4>
                                <p style="margin: 0; color: var(--color-neutral-800); font-weight: 600;"><?php echo esc_html( $company_name ); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $phone ) : ?>
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: var(--qiling-space-20);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--color-success) 0%, var(--qiling-color-059669) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 var(--qiling-space-5); color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); font-weight: 500;"><?php esc_html_e( '联系电话', 'developer-starter' ); ?></h4>
                                <p style="margin: 0;"><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', (string) $phone ) ); ?>" style="color: var(--color-neutral-800); font-weight: 600; text-decoration: none;"><?php echo esc_html( $phone ); ?></a></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $qq ) : ?>
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: var(--qiling-space-20);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--qiling-color-0ea5e9) 0%, var(--color-primary) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 10V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-1.5L13 17v-3H10a2 2 0 0 1-2-2Z"/><path d="M8 8H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h1.5L11 21v-3"/></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 var(--qiling-space-5); color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); font-weight: 500;"><?php esc_html_e( 'QQ', 'developer-starter' ); ?></h4>
                                <?php if ( $qq_link ) : ?>
                                    <p style="margin: 0;"><a href="<?php echo esc_url( $qq_link ); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--color-neutral-800); font-weight: 600; text-decoration: none;"><?php echo esc_html( $qq ); ?></a></p>
                                <?php else : ?>
                                    <p style="margin: 0; color: var(--color-neutral-800); font-weight: 600;"><?php echo esc_html( $qq ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $wechat_qrcode ) : ?>
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: var(--qiling-space-20);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--qiling-color-22c55e) 0%, var(--color-success) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" style="color: white;"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zm-2.906 5.983c.642 0 1.162-.528 1.162-1.178 0-.651-.52-1.18-1.162-1.18s-1.162.529-1.162 1.18c0 .65.52 1.178 1.162 1.178zm5.813 0c.642 0 1.162-.528 1.162-1.178 0-.651-.52-1.18-1.162-1.18s-1.162.529-1.162 1.18c0 .65.52 1.178 1.162 1.178zm5.34.72c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.045c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-7.062-6.122zm-2.036 3.812a.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982.536 0 .97.44.97.982a.977.977 0 0 1-.97.983zm4.844 0a.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982.536 0 .97.44.97.982a.977.977 0 0 1-.97.983z"/></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 var(--qiling-space-10); color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); font-weight: 500;"><?php esc_html_e( '微信', 'developer-starter' ); ?></h4>
                                <div style="display: inline-flex; flex-direction: column; align-items: center; gap: var(--qiling-space-8); padding: var(--qiling-space-10); background: var(--color-neutral-50); border: 1px solid rgba(var(--qiling-rgb-148-163-184), 0.2); border-radius: var(--qiling-space-14);">
                                    <img src="<?php echo esc_url( $wechat_qrcode ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" loading="lazy" decoding="async" style="width: 96px; height: 96px; border-radius: 10px; object-fit: cover;" />
                                    <span style="color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.85);"><?php esc_html_e( '扫码添加微信', 'developer-starter' ); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $email ) : ?>
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: var(--qiling-space-20);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--color-warning) 0%, var(--color-warning-dark) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 var(--qiling-space-5); color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); font-weight: 500;"><?php esc_html_e( '电子邮箱', 'developer-starter' ); ?></h4>
                                <p style="margin: 0;"><a href="mailto:<?php echo esc_attr( $email ); ?>" style="color: var(--color-neutral-800); font-weight: 600; text-decoration: none;"><?php echo esc_html( $email ); ?></a></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $address ) : ?>
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: var(--qiling-space-20);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--qiling-color-8b5cf6) 0%, var(--qiling-color-7c3aed) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 var(--qiling-space-5); color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); font-weight: 500;"><?php esc_html_e( '公司地址', 'developer-starter' ); ?></h4>
                                <p style="margin: 0; color: var(--color-neutral-800); font-weight: 600; line-height: 1.6;"><?php echo esc_html( $address ); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $working_hours ) : ?>
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: var(--qiling-space-20);">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--qiling-color-0284c7) 0%, var(--qiling-color-4f46e5) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 var(--qiling-space-5); color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); font-weight: 500;"><?php esc_html_e( '工作时间', 'developer-starter' ); ?></h4>
                                <p style="margin: 0; color: var(--color-neutral-800); font-weight: 600;"><?php echo esc_html( $working_hours ); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 右侧：表单 或 图片 -->
            <div class="contact-form-section">
                <?php if ( $show_form ) : ?>
                    <h2 class="section-heading"><?php esc_html_e( '在线留言', 'developer-starter' ); ?></h2>

                    <?php if ( $show_contact_login_hint ) : ?>
                        <div class="contact-form-login-hint" style="margin-bottom: var(--qiling-space-12); padding: var(--qiling-space-10) var(--qiling-space-12); border-radius: var(--qiling-space-10); background: var(--qiling-color-eff6ff); border: 1px solid var(--qiling-color-bfdbfe); color: var(--qiling-color-1e40af);">
                            <?php
                            printf(
                                /* translators: %s: login URL */
                                wp_kses( __( '当前仅登录用户可提交。<a href="%s">立即登录</a>', 'developer-starter' ), array( 'a' => array( 'href' => array() ) ) ),
                                esc_url( $contact_login_url )
                            );
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $contact_form_id > 0 && ( shortcode_exists( 'qiling_form' ) || shortcode_exists( 'developer_form' ) ) ) : ?>
                        <?php echo do_shortcode( '[qiling_form id="' . $contact_form_id . '"]' ); ?>
                    <?php else : ?>
                        <?php developer_starter_render_builtin_contact_form(); ?>
                    <?php endif; ?>

                <?php elseif ( $contact_image ) : ?>
                    <!-- 显示自定义图片 -->
                    <img src="<?php echo esc_url( $contact_image ); ?>" alt="<?php esc_attr_e( '联系我们', 'developer-starter' ); ?>" style="width: 100%; border-radius: 16px; box-shadow: 0 20px 50px rgba(var(--qiling-rgb-0-0-0), 0.1);" />

                <?php else : ?>
                    <!-- 默认占位 -->
                    <div style="background: linear-gradient(135deg, var(--color-neutral-100) 0%, var(--color-neutral-200) var(--qiling-measure-pct-100)); border-radius: var(--qiling-space-16); padding: var(--qiling-space-60) var(--qiling-space-40); text-align: center;">
                        <div style="font-size: calc(var(--qiling-font-size-base) * 4); margin-bottom: var(--qiling-space-20);">📞</div>
                        <h3 style="color: var(--color-neutral-500); font-weight: 500; margin: 0 0 var(--qiling-space-10);"><?php esc_html_e( '欢迎联系我们', 'developer-starter' ); ?></h3>
                        <p style="color: var(--color-neutral-400); margin: 0;"><?php esc_html_e( '我们期待与您的合作', 'developer-starter' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<style>
@media (max-width: 768px) {
    .contact-grid { grid-template-columns: 1fr !important; gap: var(--qiling-space-40) !important; }
}

.section-heading {
    font-size: calc(var(--qiling-font-size-base) * 1.75);
    margin-bottom: var(--qiling-space-30);
    color: var(--color-neutral-800);
}

.contact-form-section > .contact-form,
.contact-form-section > .developer-form-wrap {
    width: 100%;
    max-width: var(--qiling-measure-pct-100);
    margin: 0;
    padding: var(--qiling-space-28);
    background: linear-gradient(180deg, rgba(var(--qiling-rgb-255-255-255), 0.98) 0%, var(--color-neutral-0) 100%);
    border: 1px solid rgba(var(--qiling-rgb-148-163-184), 0.16);
    border-radius: 24px;
    box-shadow: 0 24px 70px rgba(var(--qiling-rgb-15-23-42), 0.10);
    box-sizing: border-box;
}

.contact-form-section > .contact-form .form-row {
    margin-bottom: var(--qiling-space-16);
}

.contact-form-section > .contact-form .form-row-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--qiling-space-16);
}

.contact-form-section > .contact-form .form-group {
    margin-bottom: 0;
}

.contact-form-section > .developer-form-wrap .form-title {
    margin: 0 0 var(--qiling-space-18);
    padding-bottom: var(--qiling-space-14);
    border-bottom: 1px solid rgba(var(--qiling-rgb-148-163-184), 0.16);
}

.contact-form-section > .developer-form-wrap .form-fields {
    gap: var(--qiling-space-16);
}

.contact-form-section > .developer-form-wrap .field-width-50 {
    width: calc(50% - 8px);
}

.contact-form-section > .developer-form-wrap .field-width-33 {
    width: calc(33.333% - 10.67px);
}

.contact-form-section > .developer-form-wrap .form-submit {
    margin-top: var(--qiling-space-18);
    padding-top: var(--qiling-space-16);
}

.contact-form-section > .contact-form input,
.contact-form-section > .contact-form textarea,
.contact-form-section > .developer-form-wrap input:not([type='checkbox']):not([type='radio']):not([type='submit']):not([type='button']):not([type='reset']):not([type='range']):not([type='file']),
.contact-form-section > .developer-form-wrap select,
.contact-form-section > .developer-form-wrap textarea {
    width: 100%;
    padding: var(--qiling-space-14) var(--qiling-space-16);
    border: 1px solid rgba(var(--qiling-rgb-148-163-184), 0.28);
    border-radius: 16px;
    background: var(--color-neutral-0);
    color: var(--color-neutral-800);
    box-shadow: inset 0 1px 2px rgba(var(--qiling-rgb-15-23-42), 0.04);
    box-sizing: border-box;
}

.contact-form-section > .contact-form input:focus,
.contact-form-section > .contact-form textarea:focus,
.contact-form-section > .developer-form-wrap input:not([type='checkbox']):not([type='radio']):not([type='submit']):not([type='button']):not([type='reset']):not([type='range']):not([type='file']):focus,
.contact-form-section > .developer-form-wrap select:focus,
.contact-form-section > .developer-form-wrap textarea:focus {
    outline: none;
    border-color: rgba(var(--color-primary-rgb), 0.7);
    box-shadow: 0 0 0 4px rgba(var(--color-primary-rgb), 0.10);
}

.contact-form-section > .contact-form textarea,
.contact-form-section > .developer-form-wrap textarea {
    min-height: 160px;
    resize: vertical;
}

.contact-form-section > .contact-form .form-message,
.contact-form-section > .developer-form-wrap .form-message {
    margin-bottom: var(--qiling-space-16);
}

.contact-form-section > .contact-form .btn-submit,
.contact-form-section > .developer-form-wrap .btn-submit {
    width: 100%;
    min-height: 54px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--qiling-space-15) var(--qiling-space-18);
    background: var(--qiling-gradient-brand, linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%));
    color: var(--color-text-inverse);
    border: none;
    border-radius: 16px;
    font-size: calc(var(--qiling-font-size-base) * 1.02);
    font-weight: 600;
    line-height: 1;
    box-shadow: 0 18px 30px rgba(var(--color-primary-rgb), 0.18);
    cursor: pointer;
}

.contact-form-section > .contact-form .btn-submit:hover,
.contact-form-section > .developer-form-wrap .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 34px rgba(var(--color-primary-rgb), 0.24);
}

.contact-form-section > .contact-form .btn-submit:disabled,
.contact-form-section > .developer-form-wrap .btn-submit:disabled {
    cursor: wait;
    opacity: 0.8;
    transform: none;
}

html.dark-mode .page-content {
    background: var(--color-neutral-900);
}

html.dark-mode .section-heading {
    color: var(--color-neutral-100);
}

html.dark-mode .contact-form-section > .contact-form,
html.dark-mode .contact-form-section > .developer-form-wrap {
    background: var(--color-neutral-800);
    border-color: rgba(var(--qiling-rgb-255-255-255), 0.08);
}

html.dark-mode .contact-form-section > .contact-form input,
html.dark-mode .contact-form-section > .contact-form textarea,
html.dark-mode .contact-form-section > .developer-form-wrap input:not([type='checkbox']):not([type='radio']):not([type='submit']):not([type='button']):not([type='reset']):not([type='range']):not([type='file']),
html.dark-mode .contact-form-section > .developer-form-wrap select,
html.dark-mode .contact-form-section > .developer-form-wrap textarea {
    background: var(--color-neutral-900);
    border-color: rgba(var(--qiling-rgb-255-255-255), 0.10);
    color: var(--color-neutral-100);
}

@media (max-width: 768px) {
    .contact-form-section > .contact-form,
    .contact-form-section > .developer-form-wrap {
        padding: var(--qiling-space-20);
    }

    .contact-form-section > .contact-form .form-row-2 {
        grid-template-columns: 1fr;
    }

    .contact-form-section > .developer-form-wrap .field-width-50,
    .contact-form-section > .developer-form-wrap .field-width-33 {
        width: 100%;
    }
}

html.dark-mode .contact-info-item h4 {
    color: var(--color-neutral-400) !important;
}

html.dark-mode .contact-info-item p,
html.dark-mode .contact-info-item a {
    color: var(--color-neutral-100) !important;
}
</style>

<?php get_footer(); ?>
