<?php
/**
 * Weixin Manager - 绑定/解绑微信
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Weixin_Manager {

    public function __construct() {
        add_action( 'wp_ajax_qiling_weixin_unbind', array( $this, 'ajax_unbind' ) );
    }

    public function ajax_unbind() {
        check_ajax_referer( 'qiling_weixin_unbind_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( '请先登录', 'developer-starter' ) ) );
        }

        // 密码需要按原文校验，只做 wp_unslash()，不要做文本 sanitize。
        $password = isset( $_POST['password'] ) && ! is_array( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
        if ( empty( $password ) ) {
            wp_send_json_error( array( 'message' => __( '为了账户安全，请输入登录密码', 'developer-starter' ) ) );
        }

        $user = wp_get_current_user();
        if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
            wp_send_json_error( array( 'message' => __( '密码错误，无法解绑', 'developer-starter' ) ) );
        }

        $user_id = $user->ID;
        delete_user_meta( $user_id, 'qiling_weixin_openid' );
        delete_user_meta( $user_id, 'qiling_weixin_unionid' );
        delete_user_meta( $user_id, 'qiling_weixin_avatar' );

        wp_send_json_success( array( 'message' => __( '微信已解绑', 'developer-starter' ) ) );
    }
}
