<?php
/**
 * 全链路 Trace-ID 贯穿管理
 *
 * 为全站 HTTP 请求、AJAX 接口、REST API、外部服务调用与日志注入唯一追踪标识。
 * 规范第 36 项：全链路 Trace-ID 贯穿。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trace_ID {

    /**
     * 当前请求的唯一 Trace-ID 缓存。
     *
     * @var string|null
     */
    private static $trace_id = null;

    /**
     * 获取或生成当前请求的唯一 Trace-ID。
     *
     * 优先复用上游网关或客户端传入的合法 X-Trace-ID / X-Request-ID 请求头，
     * 若无则生成全局唯一的紧凑型时间戳标识符（例如 tr_20261006_8f3a9e1201）。
     *
     * @return string
     */
    public static function get() {
        if ( null !== self::$trace_id ) {
            return self::$trace_id;
        }

        $incoming = '';
        if ( ! empty( $_SERVER['HTTP_X_TRACE_ID'] ) ) {
            $incoming = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_X_TRACE_ID'] ) );
        } elseif ( ! empty( $_SERVER['HTTP_X_REQUEST_ID'] ) ) {
            $incoming = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_X_REQUEST_ID'] ) );
        }

        // 仅允许安全的 8~64 字符字母数字及下划线短横线，防止头注入
        if ( '' !== $incoming && preg_match( '/^[A-Za-z0-9_-]{8,64}$/', $incoming ) ) {
            self::$trace_id = $incoming;
        } else {
            self::$trace_id = 'tr_' . gmdate( 'Ymd' ) . '_' . substr( md5( uniqid( (string) mt_rand(), true ) ), 0, 10 );
        }

        return self::$trace_id;
    }

    /**
     * 发送 HTTP 响应头中的 Trace-ID。
     *
     * @param array<string,string> $headers 可选过滤的响应头数组。
     * @return array<string,string>
     */
    public static function send_headers( $headers = array() ) {
        $trace_id = self::get();

        if ( ! headers_sent() ) {
            header( 'X-Trace-ID: ' . $trace_id );
            header( 'X-Request-ID: ' . $trace_id );
        }

        if ( is_array( $headers ) ) {
            $headers['X-Trace-ID']   = $trace_id;
            $headers['X-Request-ID'] = $trace_id;
        }

        return $headers;
    }

    /**
     * 初始化全局生命周期钩子。
     */
    public static function init() {
        add_action( 'send_headers', array( __CLASS__, 'send_headers' ), 1 );
        add_filter( 'wp_headers', array( __CLASS__, 'send_headers' ), 1 );
        add_filter( 'rest_pre_serve_request', function( $served, $result, $request, $server ) {
            unset( $result, $request );
            if ( is_object( $server ) && method_exists( $server, 'send_header' ) ) {
                $server->send_header( 'X-Trace-ID', self::get() );
                $server->send_header( 'X-Request-ID', self::get() );
            }
            return $served;
        }, 1, 4 );

        // 为全站 wp_send_json_error 默认注入 trace_id 方便排障
        add_filter( 'wp_send_json_error_data', function( $data ) {
            if ( is_array( $data ) && ! isset( $data['trace_id'] ) ) {
                $data['trace_id'] = self::get();
            }
            return $data;
        } );
    }
}

if ( ! function_exists( 'developer_starter_get_trace_id' ) ) {
    /**
     * 获取当前请求的全局唯一 Trace-ID。
     *
     * @return string
     */
    function developer_starter_get_trace_id() {
        return \Developer_Starter\Core\Trace_ID::get();
    }
}
