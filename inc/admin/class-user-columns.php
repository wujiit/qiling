<?php
/**
 * User Columns Class - 用户列表自定义列
 *
 * 在后台用户列表中显示注册时间、最后活跃时间、注册IP、最后活跃IP
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class User_Columns {

    /**
     * 构造函数
     */
    public function __construct() {
        // 记录用户活动信息
        add_action( 'wp_login', array( $this, 'record_login_info' ), 10, 2 );
        add_action( 'user_register', array( $this, 'record_signup_ip' ) );

        // 添加自定义列
        add_filter( 'manage_users_columns', array( $this, 'add_columns' ) );
        add_filter( 'manage_users_custom_column', array( $this, 'manage_columns' ), 10, 3 );
        
        // 使列可排序
        add_filter( 'manage_users_sortable_columns', array( $this, 'sortable_columns' ) );
        add_action( 'pre_get_users', array( $this, 'handle_sorting' ) );
    }

    /**
     * 记录登录时间和IP
     */
    public function record_login_info( $user_login, $user ) {
        update_user_meta( $user->ID, 'ds_last_login', current_time( 'timestamp' ) );
        update_user_meta( $user->ID, 'ds_last_login_ip', developer_starter_get_client_ip() );
    }

    /**
     * 记录注册IP
     */
    public function record_signup_ip( $user_id ) {
        update_user_meta( $user_id, 'ds_signup_ip', developer_starter_get_client_ip() );
    }

    /**
     * 添加列头
     */
    public function add_columns( $columns ) {
        $columns['phone']           = __( '手机', 'developer-starter' );
        $columns['weixin']          = __( '微信', 'developer-starter' );
        $columns['user_registered'] = __( '注册时间', 'developer-starter' );
        $columns['last_login']      = __( '最后活跃', 'developer-starter' );
        $columns['signup_ip']       = __( '注册IP', 'developer-starter' );
        $columns['last_login_ip']   = __( '最后活跃IP', 'developer-starter' );
        return $columns;
    }

    /**
     * 渲染列内容
     */
    public function manage_columns( $value, $column_name, $user_id ) {
        switch ( $column_name ) {
            case 'phone':
                $phone = get_user_meta( $user_id, 'qiling_phone', true );
                if ( ! $phone ) {
                    return '-';
                }

                $location_text = (string) get_user_meta( $user_id, 'qiling_phone_location_label', true );
                if ( '' === $location_text ) {
                    return esc_html( $phone );
                }

                return sprintf(
                    '%s<br><span style="display:inline-block;margin-top:4px;color:#6b7280;font-size:12px;">%s</span>',
                    esc_html( $phone ),
                    esc_html( $location_text )
                );

            case 'weixin':
                $openid = get_user_meta( $user_id, 'qiling_weixin_openid', true );
                if ( ! $openid ) {
                    return '-';
                }
                $flag = get_user_meta( $user_id, 'qiling_weixin_is_wechat_user', true );
                if ( $flag ) {
                    return esc_html__( '微信用户', 'developer-starter' );
                }
                $user_info = get_userdata( $user_id );
                if ( $user_info && 0 === strpos( $user_info->user_login, 'wx' ) ) {
                    $rest = substr( $user_info->user_login, 2 );
                    if ( strlen( $user_info->user_login ) === 14 && ctype_digit( $rest ) ) {
                        return esc_html__( '微信用户', 'developer-starter' );
                    }
                }
                return esc_html__( '已绑定', 'developer-starter' );

            case 'user_registered':
                $user_info = get_userdata( $user_id );
                return function_exists( 'developer_starter_format_date_value' )
                    ? developer_starter_format_date_value( $user_info->user_registered, true, true )
                    : get_date_from_gmt( $user_info->user_registered, 'Y-m-d H:i:s' );
                
            case 'last_login':
                $last_login = get_user_meta( $user_id, 'ds_last_login', true );
                if ( ! empty( $last_login ) ) {
                    return function_exists( 'developer_starter_format_date_value' )
                        ? developer_starter_format_date_value( $last_login, true )
                        : wp_date( 'Y-m-d H:i:s', (int) $last_login );
                }
                return '-';
                
            case 'signup_ip':
                $ip = get_user_meta( $user_id, 'ds_signup_ip', true );
                return $ip ? $ip : '-';
                
            case 'last_login_ip':
                $ip = get_user_meta( $user_id, 'ds_last_login_ip', true );
                return $ip ? $ip : '-';
        }
        return $value;
    }

    /**
     * 注册可排序的列
     */
    public function sortable_columns( $columns ) {
        $columns['phone']           = 'phone';
        $columns['user_registered'] = 'user_registered';
        $columns['last_login']      = 'last_login';
        return $columns;
    }

    /**
     * 处理排序逻辑
     */
    public function handle_sorting( $query ) {
        if ( ! is_admin() || ! ( $query instanceof \WP_User_Query ) ) {
            return;
        }

        global $pagenow;

        // 后台用户列表默认按注册时间倒序（最新注册在前）。
        $requested_orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( (string) $_GET['orderby'] ) ) : '';
        if ( 'users.php' === $pagenow && '' === $requested_orderby ) {
            $query->set( 'orderby', 'registered' );
            $query->set( 'order', 'DESC' );
        }

        $orderby = (string) $query->get( 'orderby' );

        if ( 'last_login' === $orderby ) {
            $query->set( 'meta_key', 'ds_last_login' );
            $query->set( 'orderby', 'meta_value_num' ); // 时间戳是数字
        } elseif ( 'phone' === $orderby ) {
            $query->set( 'meta_key', 'qiling_phone' );
            $query->set( 'orderby', 'meta_value' ); // 字符串排序
        } elseif ( 'user_registered' === $orderby ) {
            $query->set( 'orderby', 'registered' );
        }
    }
}

new User_Columns();
