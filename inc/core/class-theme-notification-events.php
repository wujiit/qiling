<?php
/**
 * Theme business notification event bridge.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Connect theme-owned business events to the unified mail / push notification settings.
 */
class Theme_Notification_Events {

    public function __construct() {
        add_action( 'qiling_submit_post_saved', array( $this, 'handle_submit_post_saved' ), 10, 3 );
        add_action( 'comment_post', array( $this, 'handle_comment_post' ), 20, 3 );
        add_action( 'qiling_account_deletion_requested', array( $this, 'handle_account_deletion_requested' ), 10, 4 );
    }

    /**
     * Notify admins when a frontend submission is saved for review.
     *
     * @param int   $post_id   Post ID.
     * @param array $raw_input Raw submitted payload.
     * @param bool  $is_update Whether this is an update to an existing submission.
     * @return void
     */
    public function handle_submit_post_saved( $post_id, $raw_input = array(), $is_update = false ) {
        unset( $raw_input );

        $post_id = absint( $post_id );
        $post    = $post_id > 0 ? get_post( $post_id ) : null;
        if ( ! $post || 'post' !== $post->post_type ) {
            return;
        }

        $author = get_userdata( (int) $post->post_author );
        $lines  = array(
            __( '文章标题', 'developer-starter' ) => get_the_title( $post_id ),
            __( '提交类型', 'developer-starter' ) => $is_update ? __( '修改投稿', 'developer-starter' ) : __( '新投稿', 'developer-starter' ),
            __( '作者', 'developer-starter' )   => $author ? $author->display_name . ' (#' . (int) $author->ID . ')' : __( '未知用户', 'developer-starter' ),
            __( '分类', 'developer-starter' )   => $this->get_post_category_names( $post_id ),
            __( '状态', 'developer-starter' )   => $this->get_post_status_label( $post->post_status ),
            __( '提交时间', 'developer-starter' ) => current_time( 'Y-m-d H:i:s' ),
        );

        $edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
        $lines[ __( '审核地址', 'developer-starter' ) ] = $edit_url;

        $this->notify_admin(
            'submit_post',
            $is_update ? __( '投稿更新待审核', 'developer-starter' ) : __( '新投稿待审核', 'developer-starter' ),
            __( '前台用户提交的文章已进入待审核状态，请及时处理。', 'developer-starter' ),
            $lines,
            __( '查看投稿', 'developer-starter' ),
            $edit_url,
            'qiling_theme_submit_post'
        );
    }

    /**
     * Notify admins when a new comment is created.
     *
     * @param int        $comment_id       Comment ID.
     * @param int|string $comment_approved Comment approval state.
     * @param array      $commentdata      Raw comment data.
     * @return void
     */
    public function handle_comment_post( $comment_id, $comment_approved = 0, $commentdata = array() ) {
        unset( $commentdata );

        $comment_id = absint( $comment_id );
        $comment    = $comment_id > 0 ? get_comment( $comment_id ) : null;
        if ( ! $comment ) {
            return;
        }

        $comment_type = isset( $comment->comment_type ) ? (string) $comment->comment_type : '';
        if ( ! in_array( $comment_type, array( '', 'comment' ), true ) ) {
            return;
        }

        $approved = (string) $comment_approved;
        if ( ! in_array( $approved, array( '0', '1' ), true ) ) {
            return;
        }

        $scope = $this->get_comment_scope();
        if ( 'pending' === $scope && '0' !== $approved ) {
            return;
        }

        $post_id  = absint( $comment->comment_post_ID );
        $post_url = $post_id > 0 ? get_permalink( $post_id ) : '';
        $edit_url = admin_url( 'comment.php?action=editcomment&c=' . $comment_id );
        $title    = '0' === $approved ? __( '新待审评论通知', 'developer-starter' ) : __( '新评论通知', 'developer-starter' );

        $lines = array(
            __( '评论文章', 'developer-starter' ) => $post_id > 0 ? get_the_title( $post_id ) : __( '未知文章', 'developer-starter' ),
            __( '评论人', 'developer-starter' )   => get_comment_author( $comment ),
            __( '邮箱', 'developer-starter' )     => get_comment_author_email( $comment ),
            __( '状态', 'developer-starter' )     => '0' === $approved ? __( '待审核', 'developer-starter' ) : __( '已发布', 'developer-starter' ),
            __( '内容摘要', 'developer-starter' ) => $this->trim_text( (string) $comment->comment_content, 180 ),
            __( '评论时间', 'developer-starter' ) => current_time( 'Y-m-d H:i:s' ),
            __( '管理地址', 'developer-starter' ) => $edit_url,
        );

        if ( is_string( $post_url ) && '' !== $post_url ) {
            $lines[ __( '文章地址', 'developer-starter' ) ] = $post_url;
        }

        $this->notify_admin(
            'comment',
            $title,
            '0' === $approved
                ? __( '站点收到一条待审核评论，请及时审核。', 'developer-starter' )
                : __( '站点收到一条新评论。', 'developer-starter' ),
            $lines,
            __( '管理评论', 'developer-starter' ),
            $edit_url,
            'qiling_theme_comment'
        );
    }

    /**
     * Notify admins when a user requests account deletion.
     *
     * @param int      $request_id Request row ID.
     * @param int      $user_id    User ID.
     * @param \WP_User $user       User object.
     * @param string   $ip_address Request IP.
     * @return void
     */
    public function handle_account_deletion_requested( $request_id, $user_id, $user = null, $ip_address = '' ) {
        $request_id = absint( $request_id );
        $user_id    = absint( $user_id );
        if ( ! $user instanceof \WP_User && $user_id > 0 ) {
            $user = get_userdata( $user_id );
        }

        $admin_url = admin_url( 'admin.php?page=' . $this->get_account_deletion_page_slug() );
        $lines     = array(
            __( '申请编号', 'developer-starter' ) => $request_id > 0 ? '#' . $request_id : __( '未知', 'developer-starter' ),
            __( '用户 ID', 'developer-starter' )  => $user_id,
            __( '用户名', 'developer-starter' )   => $user ? $user->user_login : __( '未知用户', 'developer-starter' ),
            __( '显示名称', 'developer-starter' ) => $user ? $user->display_name : '',
            __( '邮箱', 'developer-starter' )     => $user ? $user->user_email : '',
            __( 'IP 地址', 'developer-starter' )  => sanitize_text_field( (string) $ip_address ),
            __( '申请时间', 'developer-starter' ) => current_time( 'Y-m-d H:i:s' ),
            __( '处理入口', 'developer-starter' ) => $admin_url,
        );

        $this->notify_admin(
            'account_deletion',
            __( '新的账号注销申请', 'developer-starter' ),
            __( '有用户提交了账号注销申请，需要管理员在后台人工审核处理。', 'developer-starter' ),
            $lines,
            __( '查看申请', 'developer-starter' ),
            $admin_url,
            'qiling_theme_account_deletion'
        );
    }

    /**
     * Send notification according to scene settings.
     *
     * @param string $scene       Notification scene.
     * @param string $title       Notification title.
     * @param string $intro       Email intro.
     * @param array  $lines       Detail lines.
     * @param string $button_text Email button text.
     * @param string $button_url  Email button URL.
     * @param string $source      Push source identifier.
     * @return void
     */
    private function notify_admin( $scene, $title, $intro, $lines, $button_text = '', $button_url = '', $source = '' ) {
        $mode = function_exists( 'developer_starter_get_notify_method' )
            ? developer_starter_get_notify_method( $scene, 'none' )
            : 'none';

        $should_send_email = function_exists( 'developer_starter_notify_method_has_email' )
            ? developer_starter_notify_method_has_email( $mode )
            : in_array( $mode, array( 'email', 'both' ), true );
        $should_send_push = function_exists( 'developer_starter_notify_method_has_push' )
            ? developer_starter_notify_method_has_push( $mode )
            : in_array( $mode, array( 'push', 'both' ), true );

        if ( ! $should_send_email && ! $should_send_push ) {
            return;
        }

        $lines = is_array( $lines ) ? $lines : array();

        if ( $should_send_email ) {
            $this->send_admin_email( $title, $intro, $lines, $button_text, $button_url );
        }

        if ( $should_send_push && function_exists( 'developer_starter_send_push_message' ) ) {
            $args = array();
            if ( '' !== $source ) {
                $args['source'] = sanitize_key( $source );
            }
            developer_starter_send_push_message(
                $scene,
                $title,
                $lines,
                array(
                    'args' => $args,
                )
            );
        }
    }

    /**
     * Send an HTML admin email with a plain text fallback.
     *
     * @param string $title       Notification title.
     * @param string $intro       Intro text.
     * @param array  $lines       Detail lines.
     * @param string $button_text Button text.
     * @param string $button_url  Button URL.
     * @return void
     */
    private function send_admin_email( $title, $intro, $lines, $button_text = '', $button_url = '' ) {
        $admin_email = get_option( 'admin_email' );
        if ( ! is_email( $admin_email ) ) {
            return;
        }

        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
        $subject   = sprintf( '[%1$s] %2$s', $site_name, wp_strip_all_tags( (string) $title ) );

        if ( function_exists( 'developer_starter_build_html_email_template' ) ) {
            $body = developer_starter_build_html_email_template(
                array(
                    'title'       => $title,
                    'intro'       => $intro,
                    'lines'       => $lines,
                    'button_text' => $button_text,
                    'button_url'  => $button_url,
                )
            );
            wp_mail( $admin_email, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
            return;
        }

        $body = wp_strip_all_tags( (string) $intro ) . "\n\n";
        foreach ( $lines as $label => $value ) {
            if ( is_array( $value ) ) {
                $value = implode( ', ', array_map( 'strval', $value ) );
            }
            $value = trim( wp_strip_all_tags( (string) $value ) );
            if ( '' === $value ) {
                continue;
            }
            $body .= wp_strip_all_tags( (string) $label ) . ': ' . $value . "\n";
        }

        wp_mail( $admin_email, $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
    }

    /**
     * Get configured comment notification scope.
     *
     * @return string
     */
    private function get_comment_scope() {
        $scope = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( 'notify_comment_scope', 'pending' )
            : 'pending';
        $scope = sanitize_key( (string) $scope );

        return in_array( $scope, array( 'pending', 'all' ), true ) ? $scope : 'pending';
    }

    /**
     * Get category names for a post.
     *
     * @param int $post_id Post ID.
     * @return string
     */
    private function get_post_category_names( $post_id ) {
        $terms = get_the_terms( $post_id, 'category' );
        if ( ! is_array( $terms ) || empty( $terms ) || is_wp_error( $terms ) ) {
            return __( '未分类', 'developer-starter' );
        }

        return implode( ', ', wp_list_pluck( $terms, 'name' ) );
    }

    /**
     * Resolve a post status label.
     *
     * @param string $status Post status.
     * @return string
     */
    private function get_post_status_label( $status ) {
        $status_object = get_post_status_object( $status );
        if ( $status_object && ! empty( $status_object->label ) ) {
            return (string) $status_object->label;
        }

        return sanitize_key( (string) $status );
    }

    /**
     * Trim text safely for push messages.
     *
     * @param string $text      Source text.
     * @param int    $max_chars Max chars.
     * @return string
     */
    private function trim_text( $text, $max_chars = 160 ) {
        $text       = wp_strip_all_tags( (string) $text );
        $normalized = preg_replace( '/\s+/u', ' ', $text );
        $text       = trim( is_string( $normalized ) ? $normalized : $text );
        if ( '' === $text ) {
            return '';
        }

        $max_chars = max( 20, absint( $max_chars ) );
        $length    = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
        if ( $length <= $max_chars ) {
            return $text;
        }

        $slice = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max_chars ) : substr( $text, 0, $max_chars );
        return rtrim( (string) $slice ) . '...';
    }

    /**
     * Get account deletion admin page slug.
     *
     * @return string
     */
    private function get_account_deletion_page_slug() {
        if ( class_exists( __NAMESPACE__ . '\\Account_Deletion_Manager' ) ) {
            return Account_Deletion_Manager::PAGE_SLUG;
        }

        return 'developer-starter-account-deletion-requests';
    }
}
