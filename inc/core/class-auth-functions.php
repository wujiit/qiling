<?php
namespace Developer_Starter\Core {

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 认证相关功能类
 * 处理登录安全、权限控制等
 */
class Auth_Functions {
    
    /**
     * 初始化钩子
     */
    public static function init() {
        add_action( 'init', [ __CLASS__, 'security_enhancements' ], 1 );
    }
    
    /**
     * 安全增强功能
     */
    public static function security_enhancements() {
        // 禁用作者存档页
        if ( developer_starter_get_option( 'disable_author_archive', '' ) ) {
            add_action( 'template_redirect', function() {
                if ( is_author() ) {
                    wp_redirect( home_url(), 301 );
                    exit;
                }
            } );
            
            // 阻止 ?author=1 查询
            add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
                if ( preg_match( '/\?author=([0-9]*)/', $requested_url ) ) {
                    return home_url();
                }
                return $redirect_url;
            }, 10, 2 );
        }
        
        // 禁用文件编辑器
        if ( developer_starter_get_option( 'disable_file_edit', '' ) ) {
            if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
                define( 'DISALLOW_FILE_EDIT', true );
            }
        }
        
        // 隐藏登录错误信息（统一文案，避免暴露用户名/密码状态细节）
        if ( developer_starter_get_option( 'login_error_hide', '' ) ) {
            add_filter( 'login_errors', function() {
                return __( '用户名或密码错误，请重试。', 'developer-starter' );
            }, 999 );
        }
    }
}


}

namespace {
// 兼容全局函数定义
if ( ! function_exists( 'developer_starter_security_enhancements' ) ) {
    function developer_starter_security_enhancements() {
        Developer_Starter\Core\Auth_Functions::security_enhancements();
    }
}
}
