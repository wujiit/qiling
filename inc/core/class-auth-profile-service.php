<?php
/**
 * Auth profile and ajax response service.
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auth_Profile_Service {

    /**
     * @var callable|null
     */
    private $option_callback;

    /**
     * @param array<string,mixed> $args 配置项。
     */
    public function __construct( $args = array() ) {
        if ( isset( $args['option_callback'] ) && is_callable( $args['option_callback'] ) ) {
            $this->option_callback = $args['option_callback'];
        }
    }

    /**
     * 发送 no-store 响应头。
     *
     * @return void
     */
    public function send_no_store_headers() {
        nocache_headers();
        header( 'Cache-Control: no-cache, no-store, must-revalidate, private, max-age=0' );
        header( 'Pragma: no-cache' );
        header( 'Expires: Thu, 01 Jan 1970 00:00:00 GMT' );
        header( 'X-Accel-Expires: 0' );
        header( 'Vary: Cookie, Authorization' );
    }

    /**
     * 已登录用户尝试登录/注册时的响应。
     *
     * @return array<string,string>
     */
    public function get_already_logged_in_payload() {
        return array(
            'message'  => __( '您已经登录，正在刷新页面...', 'developer-starter' ),
            'redirect' => home_url(),
        );
    }

    /**
     * 获取用户状态响应。
     *
     * @return array<string,mixed>
     */
    public function get_user_status_payload() {
        if ( ! is_user_logged_in() ) {
            return array(
                'logged_in' => false,
            );
        }

        $current_user = wp_get_current_user();
        $account_page_id = $this->resolve_account_page_id();
        $account_url = $account_page_id ? get_permalink( $account_page_id ) : admin_url( 'profile.php' );

        return array(
            'logged_in'        => true,
            'display_name'     => (string) $current_user->display_name,
            'avatar_32'        => get_avatar_url( $current_user->ID, array( 'size' => 32 ) ),
            'avatar_48'        => get_avatar_url( $current_user->ID, array( 'size' => 48 ) ),
            'account_url'      => $account_url,
            'admin_url'        => current_user_can( 'read' ) ? admin_url() : '',
            'logout_url'       => function_exists( 'developer_starter_get_front_logout_url' ) ? developer_starter_get_front_logout_url() : wp_logout_url( home_url() ),
            'can_access_admin' => current_user_can( 'read' ),
        );
    }

    /**
     * 刷新 nonce 的响应载荷。
     *
     * @return array<string,string>
     */
    public function get_refresh_nonce_payload() {
        return array(
            'message'    => __( 'Token 已刷新', 'developer-starter' ),
            'auth_nonce' => wp_create_nonce( 'developer_starter_auth' ),
            'sms_nonce'  => wp_create_nonce( 'sms_nonce' ),
        );
    }

    /**
     * 处理头像上传。
     *
     * @param array<string,mixed> $request 请求数据。
     * @param array<string,mixed> $files 上传文件。
     * @return array<string,string>|\WP_Error
     */
    public function handle_avatar_upload( $request, $files ) {
        if ( ! is_user_logged_in() ) {
            return new \WP_Error( 'avatar_auth_required', __( '请先登录', 'developer-starter' ) );
        }

        if ( ! $this->get_option( 'user_avatar_upload_enable', '' ) ) {
            return new \WP_Error( 'avatar_upload_disabled', __( '头像上传功能未启用', 'developer-starter' ) );
        }

        $nonce = isset( $request['nonce'] ) ? (string) $request['nonce'] : '';
        if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'developer_starter_avatar_upload' ) ) {
            return new \WP_Error( 'avatar_nonce_invalid', __( '安全验证失败，请刷新页面重试', 'developer-starter' ) );
        }

        if ( empty( $files['avatar'] ) || ! is_array( $files['avatar'] ) ) {
            return new \WP_Error( 'avatar_file_missing', __( '文件上传失败，请重试', 'developer-starter' ) );
        }

        $file = $files['avatar'];
        $error_code = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
        if ( UPLOAD_ERR_OK !== $error_code ) {
            return new \WP_Error( 'avatar_file_upload_failed', __( '文件上传失败，请重试', 'developer-starter' ) );
        }

        $allowed_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif' );
        $file_type = $this->detect_uploaded_mime_type( $file );
        $tmp_name = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
        $image_size = ( '' !== $tmp_name && file_exists( $tmp_name ) ) ? getimagesize( $tmp_name ) : false;

        if ( false === $image_size ) {
            return new \WP_Error( 'avatar_image_invalid', __( '上传的文件不是有效的图片', 'developer-starter' ) );
        }

        $image_mime = isset( $image_size['mime'] ) ? strtolower( (string) $image_size['mime'] ) : '';
        if ( '' === $file_type ) {
            $file_type = $image_mime;
        }

        if ( ! in_array( $file_type, $allowed_types, true ) ) {
            return new \WP_Error( 'avatar_type_invalid', __( '只允许上传 JPG、PNG、GIF、WebP、AVIF 格式的图片', 'developer-starter' ) );
        }

        if ( '' !== $image_mime && '' !== $file_type && $image_mime !== $file_type ) {
            return new \WP_Error( 'avatar_mime_mismatch', __( '文件类型验证失败', 'developer-starter' ) );
        }

        $max_width = 4096;
        $max_height = 4096;
        if ( $image_size[0] > $max_width || $image_size[1] > $max_height ) {
            return new \WP_Error( 'avatar_dimensions_exceeded', sprintf( __( '图片尺寸不能超过 %dx%d 像素', 'developer-starter' ), $max_width, $max_height ) );
        }

        $min_size = 10;
        if ( $image_size[0] < $min_size || $image_size[1] < $min_size ) {
            return new \WP_Error( 'avatar_dimensions_too_small', __( '图片尺寸过小', 'developer-starter' ) );
        }

        $max_size = 2 * 1024 * 1024;
        $file_size = isset( $file['size'] ) ? (int) $file['size'] : 0;
        if ( $file_size > $max_size ) {
            return new \WP_Error( 'avatar_size_exceeded', __( '图片大小不能超过 2MB', 'developer-starter' ) );
        }

        $user_id = get_current_user_id();
        $rate_key = 'ds_avatar_upload_' . $user_id;
        $upload_count = (int) get_transient( $rate_key );
        if ( $upload_count >= 3 ) {
            return new \WP_Error( 'avatar_rate_limited', __( '上传过于频繁，请稍后再试', 'developer-starter' ) );
        }
        set_transient( $rate_key, $upload_count + 1, 60 );

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $mime_to_ext = array(
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
        );
        $ext = isset( $mime_to_ext[ $file_type ] ) ? $mime_to_ext[ $file_type ] : 'jpg';

        $new_filename = 'avatar-' . $user_id . '-' . time() . '.' . $ext;
        $file['name'] = $new_filename;

        $attachment_id = media_handle_sideload(
            $file,
            0,
            null,
            array(
                'test_form' => false,
                'test_size' => true,
            )
        );

        if ( is_wp_error( $attachment_id ) ) {
            return new \WP_Error(
                'avatar_upload_failed',
                sprintf( __( '头像上传失败：%s', 'developer-starter' ), $attachment_id->get_error_message() )
            );
        }

        $avatar_url = wp_get_attachment_url( $attachment_id );
        $old_avatar_id = get_user_meta( $user_id, 'custom_avatar_attachment_id', true );
        if ( $old_avatar_id ) {
            wp_delete_attachment( $old_avatar_id, true );
        }

        update_user_meta( $user_id, 'ds_custom_avatar', $avatar_url );
        update_user_meta( $user_id, 'custom_avatar_attachment_id', $attachment_id );

        return array(
            'message'    => __( '头像上传成功！', 'developer-starter' ),
            'avatar_url' => (string) $avatar_url,
        );
    }

    /**
     * 获取个人中心页面 ID。
     *
     * @return int
     */
    private function resolve_account_page_id() {
        $account_page_id = (int) get_option( 'developer_starter_account_page_id', 0 );
        if ( $account_page_id > 0 && get_post( $account_page_id ) ) {
            return $account_page_id;
        }

        $account_page = get_pages(
            array(
                'meta_key'   => '_wp_page_template',
                'meta_value' => 'templates/template-account.php',
                'number'     => 1,
            )
        );

        if ( ! empty( $account_page ) && isset( $account_page[0]->ID ) ) {
            $account_page_id = (int) $account_page[0]->ID;
            update_option( 'developer_starter_account_page_id', $account_page_id );
            return $account_page_id;
        }

        return 0;
    }

    /**
     * 检测上传文件 MIME（兼容 finfo 缺失环境）。
     *
     * @param array<string,mixed> $file 上传文件数组。
     * @return string
     */
    private function detect_uploaded_mime_type( $file ) {
        if ( ! is_array( $file ) ) {
            return '';
        }

        $tmp_name = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
        if ( '' === $tmp_name || ! file_exists( $tmp_name ) ) {
            return '';
        }

        $detected_mime = '';

        if ( function_exists( 'finfo_open' ) && function_exists( 'finfo_file' ) ) {
            $finfo_mode = defined( 'FILEINFO_MIME_TYPE' ) ? FILEINFO_MIME_TYPE : 0;
            $finfo = @finfo_open( $finfo_mode );
            if ( $finfo ) {
                $mime = @finfo_file( $finfo, $tmp_name );
                if ( function_exists( 'finfo_close' ) ) {
                    @finfo_close( $finfo );
                }
                if ( is_string( $mime ) && '' !== $mime ) {
                    $detected_mime = strtolower( trim( (string) explode( ';', $mime )[0] ) );
                }
            }
        }

        if ( '' === $detected_mime && function_exists( 'mime_content_type' ) ) {
            $mime = @mime_content_type( $tmp_name );
            if ( is_string( $mime ) && '' !== $mime ) {
                $detected_mime = strtolower( trim( (string) explode( ';', $mime )[0] ) );
            }
        }

        if ( '' === $detected_mime && function_exists( 'wp_check_filetype_and_ext' ) ) {
            $filename = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
            $checked = wp_check_filetype_and_ext(
                $tmp_name,
                $filename,
                array(
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'png'          => 'image/png',
                    'gif'          => 'image/gif',
                    'webp'         => 'image/webp',
                    'avif'         => 'image/avif',
                )
            );
            if ( isset( $checked['type'] ) && is_string( $checked['type'] ) && '' !== $checked['type'] ) {
                $detected_mime = strtolower( (string) $checked['type'] );
            }
        }

        return $detected_mime;
    }

    /**
     * 获取主题设置。
     *
     * @param string $key 键名。
     * @param mixed  $default 默认值。
     * @return mixed
     */
    private function get_option( $key, $default = '' ) {
        if ( is_callable( $this->option_callback ) ) {
            return call_user_func( $this->option_callback, $key, $default );
        }

        return function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( $key, $default )
            : $default;
    }
}
