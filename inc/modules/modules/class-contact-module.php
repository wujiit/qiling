<?php
/**
 * Contact Module - 联系我们
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Contact_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'general';
        $this->icon        = 'dashicons-email';
        $this->description = __( '联系我们模块', 'developer-starter' );
    }

    public function get_id() {
        return 'contact';
    }

    public function get_name() {
        return __( '联系我们', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'contact_title',
                'label'   => __( '标题', 'developer-starter' ),
                'type'    => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '联系我们', 'Contact Us' ) : __( '联系我们', 'developer-starter' ),
            ),
            array(
                'id'          => 'contact_title_size',
                'label'       => __( '标题字体大小', 'developer-starter' ),
                'type'        => 'text',
                'default'     => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id'          => 'contact_title_color',
                'label'       => __( '标题颜色', 'developer-starter' ),
                'type'        => 'color',
                'default'     => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'contact_subtitle',
                'label'   => __( '副标题', 'developer-starter' ),
                'type'    => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '有任何问题？请随时与我们联系', 'Have a question? We would love to hear from you.' ) : __( '有任何问题？请随时与我们联系', 'developer-starter' ),
            ),
            array(
                'id'          => 'contact_subtitle_size',
                'label'       => __( '副标题字体大小', 'developer-starter' ),
                'type'        => 'text',
                'default'     => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id'          => 'contact_subtitle_color',
                'label'       => __( '副标题颜色', 'developer-starter' ),
                'type'        => 'color',
                'default'     => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'contact_show_form',
                'label'   => __( '显示联系表单', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    '1' => __( '显示', 'developer-starter' ),
                    '0' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'          => 'contact_form_id',
                'label'       => __( '启灵表单ID', 'developer-starter' ),
                'type'        => 'text',
                'default'     => '',
                'description' => __( '留空时使用主题内置在线留言；填写后改用启灵表单插件。', 'developer-starter' ),
            ),
            array( 'id' => 'contact_submit_text', 'type' => 'text', 'label' => __( '内置表单提交按钮文案', 'developer-starter' ), 'default' => __( '提交留言', 'developer-starter' ), 'description' => __( '只控制主题内置在线留言，不影响启灵表单插件。', 'developer-starter' ) ),
            array( 'id' => 'contact_submit_bg_color', 'type' => 'color', 'label' => __( '内置表单提交按钮背景颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'contact_submit_text_color', 'type' => 'color', 'label' => __( '内置表单提交按钮文字颜色', 'developer-starter' ), 'default' => '' ),
            $this->get_button_border_color_field( 'contact_submit_border_color', __( '内置表单提交按钮边框颜色', 'developer-starter' ) ),
            array( 'id' => 'contact_submit_hover_bg_color', 'type' => 'color', 'label' => __( '内置表单提交按钮悬停背景颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'contact_submit_hover_text_color', 'type' => 'color', 'label' => __( '内置表单提交按钮悬停文字颜色', 'developer-starter' ), 'default' => '' ),
            $this->get_button_border_color_field( 'contact_submit_hover_border_color', __( '内置表单提交按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随内置提交按钮悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id'          => 'contact_image',
                'label'       => __( '右侧图片', 'developer-starter' ),
                'type'        => 'image',
                'description' => __( '不显示表单时显示此图片', 'developer-starter' ),
            ),
            array(
                'id'      => 'module_bg_type',
                'label'   => __( '背景类型', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id'         => 'module_bg_color',
                'label'      => __( '背景颜色', 'developer-starter' ),
                'type'       => 'color',
                'desc'       => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default'    => 'var(--color-neutral-50)',
                'dependency' => array( 'module_bg_type', '==', 'color' ),
            ),
            array(
                'id'         => 'module_bg_image',
                'label'      => __( '背景图片', 'developer-starter' ),
                'type'       => 'image',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
            array(
                'id'         => 'module_bg_overlay',
                'label'      => __( '背景遮罩浓度', 'developer-starter' ),
                'type'       => 'select',
                'options'    => array(
                    '0'   => __( '无遮罩', 'developer-starter' ),
                    '0.1' => '10%',
                    '0.2' => '20%',
                    '0.3' => '30%',
                    '0.4' => '40%',
                    '0.5' => '50%',
                    '0.6' => '60%',
                    '0.7' => '70%',
                    '0.8' => '80%',
                    '0.9' => '90%',
                ),
                'default'    => '0',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'label'   => __( '上边距 (如 60px)', 'developer-starter' ),
                'type'    => 'text',
                'default' => '60px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'label'   => __( '下边距 (如 60px)', 'developer-starter' ),
                'type'    => 'text',
                'default' => '60px',
            ),
        );
    }

    /**
     * 获取当前模块表单 ID。
     *
     * @param array $data 模块数据。
     * @return int
     */
    private function get_contact_form_id( $data ) {
        $form_id = isset( $data['contact_form_id'] ) ? absint( $data['contact_form_id'] ) : 0;
        if ( $form_id > 0 ) {
            return $form_id;
        }

        if ( function_exists( 'developer_starter_get_explicit_contact_form_id' ) ) {
            return absint( developer_starter_get_explicit_contact_form_id() );
        }

        return 0;
    }

    public function render( $data = array() ) {
        $title = isset( $data['contact_title'] ) && $data['contact_title']
            ? $data['contact_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '联系我们', 'Contact Us' ) : __( '联系我们', 'developer-starter' ) );
        $subtitle = isset( $data['contact_subtitle'] )
            ? $data['contact_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '有任何问题？请随时与我们联系', 'Have questions? Reach out and we will get back to you soon.' ) : __( '有任何问题？请随时与我们联系', 'developer-starter' ) );
        $show_form   = isset( $data['contact_show_form'] ) ? (string) $data['contact_show_form'] : '1';
        $show_form_enabled = in_array( strtolower( $show_form ), array( '1', 'yes', 'true', 'on' ), true );
        $form_id     = $this->get_contact_form_id( $data );
        $submit_text = isset( $data['contact_submit_text'] ) && '' !== trim( (string) $data['contact_submit_text'] ) ? (string) $data['contact_submit_text'] : __( '提交留言', 'developer-starter' );
        $right_image = isset( $data['contact_image'] ) ? $data['contact_image'] : '';

        // 标题/副标题样式
        $title_size     = isset( $data['contact_title_size'] ) && $data['contact_title_size'] ? $data['contact_title_size'] : '';
        $title_color    = isset( $data['contact_title_color'] ) && $data['contact_title_color'] ? $data['contact_title_color'] : '';
        $subtitle_size  = isset( $data['contact_subtitle_size'] ) && $data['contact_subtitle_size'] ? $data['contact_subtitle_size'] : '';
        $subtitle_color = isset( $data['contact_subtitle_color'] ) && $data['contact_subtitle_color'] ? $data['contact_subtitle_color'] : '';

        $company_name  = developer_starter_get_option( 'company_name', '' );
        $phone         = developer_starter_get_option( 'company_phone', '' );
        $qq            = developer_starter_get_option( 'company_qq', '' );
        $qq_link       = function_exists( 'developer_starter_get_qq_contact_link' ) ? developer_starter_get_qq_contact_link( $qq ) : '';
        $wechat_qrcode = developer_starter_get_option( 'company_wechat_qrcode', '' );
        $email         = developer_starter_get_option( 'company_email', '' );
        $address       = developer_starter_get_option( 'company_address', '' );
        $working_hours = developer_starter_get_option( 'company_working_hours', '' );
        $login_required = developer_starter_get_option( 'contact_message_login_required', '' ) === '1';
        $custom_login_page = (int) developer_starter_get_option( 'login_page_id', '' );
        $login_url = $custom_login_page ? get_permalink( $custom_login_page ) : wp_login_url( get_permalink() );
        if ( ! $login_url ) {
            $login_url = wp_login_url( home_url( '/' ) );
        }
        $show_login_hint = $login_required && ! is_user_logged_in();

        // 样式参数
        $bg_type    = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color   = isset( $data['module_bg_color'] ) && '' !== trim( (string) $data['module_bg_color'] )
            ? $data['module_bg_color']
            : ( isset( $data['contact_bg_color'] ) && '' !== trim( (string) $data['contact_bg_color'] ) ? $data['contact_bg_color'] : 'var(--color-neutral-50)' );
        $bg_image   = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        $pt         = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb         = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';

        // 动态样式（仅包含用户自定义的部分）
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};--contact-submit-text:#ffffff;--contact-submit-hover-text:#ffffff;";
        $button_var_map = array(
            'contact_submit_bg_color'           => '--contact-submit-bg',
            'contact_submit_text_color'         => '--contact-submit-text',
            'contact_submit_border_color'       => '--contact-submit-border',
            'contact_submit_hover_bg_color'     => '--contact-submit-hover-bg',
            'contact_submit_hover_text_color'   => '--contact-submit-hover-text',
            'contact_submit_hover_border_color' => '--contact-submit-hover-border',
        );
        foreach ( $button_var_map as $field_id => $css_var ) {
            $value = isset( $data[ $field_id ] ) ? trim( wp_strip_all_tags( (string) $data[ $field_id ] ) ) : '';
            if ( function_exists( 'developer_starter_sanitize_page_visual_style_css_value' ) ) {
                $value = developer_starter_sanitize_page_visual_style_css_value( $value );
            } elseif ( preg_match( '/[;{}<>]/', $value ) ) {
                $value = '';
            }
            if ( '' !== $value ) {
                $section_style .= $css_var . ':' . $value . ';';
            }
        }

        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }

        // 标题动态样式
        $title_style = '';
        if ( $title_size ) {
            $title_style .= "font-size: {$title_size};";
        }
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        // 副标题动态样式
        $subtitle_style = '';
        if ( $subtitle_size ) {
            $subtitle_style .= "font-size: {$subtitle_size};";
        }
        if ( $subtitle_color ) {
            $subtitle_style .= "color: {$subtitle_color};";
        }
        ?>
        <section class="module module-contact" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            <div class="container module-contact-container">
                <div class="section-header text-center">
                    <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>

                <div class="contact-grid">

                    <!-- 左侧：联系信息 -->
                    <div class="contact-info">
                        <?php if ( $company_name ) : ?>
                            <div class="contact-item">
                                <div class="contact-icon icon-company">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>
                                </div>
                                <div class="contact-item-content">
                                    <h4><?php esc_html_e( '公司名称', 'developer-starter' ); ?></h4>
                                    <p><?php echo esc_html( $company_name ); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $phone ) : ?>
                            <div class="contact-item">
                                <div class="contact-icon icon-phone">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                                </div>
                                <div class="contact-item-content">
                                    <h4><?php esc_html_e( '联系电话', 'developer-starter' ); ?></h4>
                                    <p><a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $qq ) : ?>
                            <div class="contact-item">
                                <div class="contact-icon icon-qq">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 10V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-1.5L13 17v-3H10a2 2 0 0 1-2-2Z"></path><path d="M8 8H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h1.5L11 21v-3"></path></svg>
                                </div>
                                <div class="contact-item-content">
                                    <h4><?php esc_html_e( 'QQ', 'developer-starter' ); ?></h4>
                                    <?php if ( $qq_link ) : ?>
                                        <p><a href="<?php echo esc_url( $qq_link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $qq ); ?></a></p>
                                    <?php else : ?>
                                        <p><?php echo esc_html( $qq ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $wechat_qrcode ) : ?>
                            <div class="contact-item">
                                <div class="contact-icon icon-wechat">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zm-2.906 5.983c.642 0 1.162-.528 1.162-1.178 0-.651-.52-1.18-1.162-1.18s-1.162.529-1.162 1.18c0 .65.52 1.178 1.162 1.178zm5.813 0c.642 0 1.162-.528 1.162-1.178 0-.651-.52-1.18-1.162-1.18s-1.162.529-1.162 1.18c0 .65.52 1.178 1.162 1.178zm5.34.72c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.045c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-7.062-6.122zm-2.036 3.812a.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982.536 0 .97.44.97.982a.977.977 0 0 1-.97.983zm4.844 0a.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982.536 0 .97.44.97.982a.977.977 0 0 1-.97.983z"></path></svg>
                                </div>
                                <div class="contact-item-content">
                                    <h4><?php esc_html_e( '微信', 'developer-starter' ); ?></h4>
                                    <div class="contact-wechat-qr">
                                        <img src="<?php echo esc_url( $wechat_qrcode ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" loading="lazy" decoding="async" />
                                        <span><?php esc_html_e( '扫码添加微信', 'developer-starter' ); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $email ) : ?>
                            <div class="contact-item">
                                <div class="contact-icon icon-email">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                </div>
                                <div class="contact-item-content">
                                    <h4><?php esc_html_e( '电子邮箱', 'developer-starter' ); ?></h4>
                                    <p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $address ) : ?>
                            <div class="contact-item">
                                <div class="contact-icon icon-address">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div class="contact-item-content">
                                    <h4><?php esc_html_e( '联系地址', 'developer-starter' ); ?></h4>
                                    <p><?php echo esc_html( $address ); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $working_hours ) : ?>
                            <div class="contact-item">
                                <div class="contact-icon icon-hours">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <div class="contact-item-content">
                                    <h4><?php esc_html_e( '工作时间', 'developer-starter' ); ?></h4>
                                    <p><?php echo esc_html( $working_hours ); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 右侧：表单或图片 -->
                    <div class="contact-right">
                        <?php if ( $show_form_enabled ) : ?>
                            <?php if ( $show_login_hint ) : ?>
                                <div class="contact-form-login-hint">
                                    <?php
                                    printf(
                                        /* translators: %s: login URL */
                                        wp_kses( __( '当前仅登录用户可提交。<a href="%s">立即登录</a>', 'developer-starter' ), array( 'a' => array( 'href' => array() ) ) ),
                                        esc_url( $login_url )
                                    );
                                    ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $form_id > 0 && ( shortcode_exists( 'qiling_form' ) || shortcode_exists( 'developer_form' ) ) ) : ?>
                                <?php echo do_shortcode( '[qiling_form id="' . $form_id . '"]' ); ?>
                            <?php else : ?>
                                <?php developer_starter_render_builtin_contact_form( array( 'submit_text' => $submit_text, 'submit_class' => 'btn-submit contact-submit-btn' ) ); ?>
                            <?php endif; ?>
                        <?php elseif ( $right_image ) : ?>
                            <img src="<?php echo esc_url( $right_image ); ?>" alt="<?php esc_attr_e( '联系我们', 'developer-starter' ); ?>" class="contact-image" />
                        <?php else : ?>
                            <div class="contact-placeholder">
                                <div class="contact-placeholder-icon">💬</div>
                                <h3><?php esc_html_e( '期待与您合作', 'developer-starter' ); ?></h3>
                                <p><?php esc_html_e( '我们将竭诚为您服务', 'developer-starter' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </section>
        <?php
    }
}
