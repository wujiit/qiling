<?php
/**
 * Qiling forms bridge and frontend logout helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_qiling_form_manager' ) ) {
    /**
     * 获取启灵表单插件管理器实例。
     *
     * @return object|null
     */
    function developer_starter_get_qiling_form_manager() {
        if ( class_exists( 'Qiling_Forms\\Qiling_Form_Manager' ) ) {
            return \Qiling_Forms\Qiling_Form_Manager::get_instance();
        }

        return null;
    }
}

if ( ! function_exists( 'developer_starter_get_qiling_form_id_by_slug' ) ) {
    /**
     * 通过 slug 获取启灵表单 ID。
     *
     * @param string $slug 表单 slug。
     * @return int
     */
    function developer_starter_get_qiling_form_id_by_slug( $slug ) {
        $slug = sanitize_key( (string) $slug );
        if ( '' === $slug ) {
            return 0;
        }

        $manager = developer_starter_get_qiling_form_manager();
        if ( ! $manager || ! method_exists( $manager, 'get_form_by_slug' ) ) {
            return 0;
        }

        $form = $manager->get_form_by_slug( $slug );
        if ( ! $form || empty( $form->id ) ) {
            return 0;
        }

        return absint( $form->id );
    }
}

if ( ! function_exists( 'developer_starter_get_default_contact_form_id' ) ) {
    /**
     * 获取默认“联系我们”表单 ID。
     *
     * 优先读取主题设置中的 contact_form_id，未设置时回退到 qiling-forms 预设 slug(contact)。
     *
     * @return int
     */
    function developer_starter_get_default_contact_form_id() {
        $configured_id = function_exists( 'developer_starter_get_option' )
            ? absint( developer_starter_get_option( 'contact_form_id', 0 ) )
            : 0;

        if ( $configured_id > 0 ) {
            return $configured_id;
        }

        return developer_starter_get_qiling_form_id_by_slug( 'contact' );
    }
}

if ( ! function_exists( 'developer_starter_restrict_contact_form_to_logged_in' ) ) {
    /**
     * 配合 qiling-forms：按主题设置限制“联系我们”表单仅登录用户可提交。
     *
     * @param array $errors     校验错误列表。
     * @param int   $form_id    当前表单 ID。
     * @param mixed $form       当前表单对象。
     * @param array $fields     字段配置。
     * @param array $entry_data 提交数据。
     * @return array
     */
    function developer_starter_restrict_contact_form_to_logged_in( $errors, $form_id, $form, $fields, $entry_data ) {
        unset( $fields, $entry_data );

        if ( ! function_exists( 'developer_starter_get_option' ) ) {
            return $errors;
        }

        $login_required = developer_starter_get_option( 'contact_message_login_required', '' ) === '1';
        if ( ! $login_required || is_user_logged_in() ) {
            return $errors;
        }

        $configured_form_id = absint( developer_starter_get_option( 'contact_form_id', 0 ) );
        $target_form_id     = $configured_form_id > 0 ? $configured_form_id : developer_starter_get_qiling_form_id_by_slug( 'contact' );
        $form_slug          = ( is_object( $form ) && isset( $form->slug ) ) ? sanitize_key( (string) $form->slug ) : '';

        $is_contact_form = false;
        if ( $target_form_id > 0 ) {
            $is_contact_form = absint( $form_id ) === $target_form_id;
        } else {
            $is_contact_form = $form_slug === 'contact';
        }

        if ( $is_contact_form ) {
            $errors[] = __( '请先登录后再留言', 'developer-starter' );
        }

        return $errors;
    }
}
add_filter( 'qiling_forms_validation_errors', 'developer_starter_restrict_contact_form_to_logged_in', 20, 5 );

if ( ! function_exists( 'developer_starter_handle_logout_redirect' ) ) {
    /**
     * 处理前台快捷退出登录。
     *
     * @return void
     */
    function developer_starter_handle_logout_redirect() {
        if ( isset( $_GET['ds_logout'] ) && '1' === (string) wp_unslash( $_GET['ds_logout'] ) ) {
            $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
            if ( ! wp_verify_nonce( $nonce, 'developer_starter_front_logout' ) ) {
                return;
            }

            wp_logout();
            wp_safe_redirect( home_url() );
            exit;
        }
    }
}
add_action( 'init', 'developer_starter_handle_logout_redirect', 9 );

if ( ! function_exists( 'developer_starter_force_logout_without_confirm' ) ) {
    /**
     * 兼容默认注销入口：仅在 nonce 合法时直接退出。
     *
     * 无 nonce 或 nonce 非法时，回退到 WordPress 核心默认流程，
     * 以保留官方确认链路并避免被动登出（CSRF）。
     *
     * @return void
     */
    function developer_starter_force_logout_without_confirm() {
        $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'log-out' ) ) {
            return;
        }

        $requested_redirect = isset( $_REQUEST['redirect_to'] ) ? wp_unslash( $_REQUEST['redirect_to'] ) : '';
        $requested_redirect = is_string( $requested_redirect ) ? trim( $requested_redirect ) : '';
        $redirect_to = $requested_redirect !== '' ? $requested_redirect : home_url();
        $redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect, wp_get_current_user() );

        wp_logout();
        wp_safe_redirect( $redirect_to );
        exit;
    }
}
add_action( 'login_form_logout', 'developer_starter_force_logout_without_confirm', 1 );
