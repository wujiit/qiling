<?php
/**
 * Maintenance and cleanup helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_maintenance_get_option' ) ) {
    /**
     * 维护助手读取主题选项，兼容 helper 加载顺序异常场景。
     *
     * @param string $key     选项键名。
     * @param mixed  $default 默认值。
     * @return mixed
     */
    function developer_starter_maintenance_get_option( $key, $default = '' ) {
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return developer_starter_get_option( $key, $default );
        }

        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            return $default;
        }

        return array_key_exists( $key, $options ) ? $options[ $key ] : $default;
    }
}

/**
 * 数据库优化
 */
function developer_starter_database_optimizations() {
    if ( developer_starter_maintenance_get_option( 'auto_clean_trash', '' ) ) {
        if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
            define( 'EMPTY_TRASH_DAYS', 7 );
        }
    }

    $schedule = developer_starter_maintenance_get_option( 'theme_cron_cleanup_schedule', 'weekly' );
    if ( ! in_array( $schedule, array( 'daily', 'weekly', 'monthly_30', 'disabled' ), true ) ) {
        $schedule = 'weekly';
    }

    if ( $schedule === 'disabled' ) {
        wp_clear_scheduled_hook( 'developer_starter_clean_revisions' );
        wp_clear_scheduled_hook( 'developer_starter_clean_misc_data' );
        return;
    }

    if ( developer_starter_maintenance_get_option( 'auto_clean_revisions', '' ) ) {
        $event = function_exists( 'wp_get_scheduled_event' ) ? wp_get_scheduled_event( 'developer_starter_clean_revisions' ) : null;
        $need_reschedule = ! $event || $event->schedule !== $schedule;

        if ( $need_reschedule ) {
            wp_clear_scheduled_hook( 'developer_starter_clean_revisions' );
            wp_schedule_event( time() + 300, $schedule, 'developer_starter_clean_revisions' );
        }
    } else {
        wp_clear_scheduled_hook( 'developer_starter_clean_revisions' );
    }

    $auto_clean_misc = developer_starter_maintenance_get_option( 'auto_clean_expired_transients', '' ) || developer_starter_maintenance_get_option( 'auto_clean_spam_comments', '' );
    if ( $auto_clean_misc ) {
        $misc_event = function_exists( 'wp_get_scheduled_event' ) ? wp_get_scheduled_event( 'developer_starter_clean_misc_data' ) : null;
        $need_misc_reschedule = ! $misc_event || $misc_event->schedule !== $schedule;
        if ( $need_misc_reschedule ) {
            wp_clear_scheduled_hook( 'developer_starter_clean_misc_data' );
            wp_schedule_event( time() + 420, $schedule, 'developer_starter_clean_misc_data' );
        }
    } else {
        wp_clear_scheduled_hook( 'developer_starter_clean_misc_data' );
    }
}
add_action( 'init', 'developer_starter_database_optimizations', 1 );

/**
 * 追加主题任务的自定义 Cron 周期
 *
 * @param array $schedules 周期列表
 * @return array
 */
function developer_starter_cron_schedules( $schedules ) {
    if ( ! isset( $schedules['monthly_30'] ) ) {
        $schedules['monthly_30'] = array(
            'interval' => 30 * DAY_IN_SECONDS,
            'display'  => __( '每30天', 'developer-starter' ),
        );
    }
    return $schedules;
}
add_filter( 'cron_schedules', 'developer_starter_cron_schedules' );

/**
 * 执行修订版本清理
 */
function developer_starter_do_clean_revisions() {
    global $wpdb;
    $deleted_revisions = 0;
    $deleted_orphan_meta = 0;
    $batch_size = 200;

    $date_threshold = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );

    while ( true ) {
        $revision_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_modified < %s ORDER BY ID ASC LIMIT %d",
                $date_threshold,
                $batch_size
            )
        );

        if ( empty( $revision_ids ) ) {
            break;
        }

        foreach ( $revision_ids as $revision_id ) {
            $revision_id = absint( $revision_id );
            if ( $revision_id <= 0 ) {
                continue;
            }

            $result = wp_delete_post_revision( $revision_id );
            if ( ! $result ) {
                $result = wp_delete_post( $revision_id, true );
            }

            if ( $result ) {
                $deleted_revisions++;
            }
        }

        if ( count( $revision_ids ) < $batch_size ) {
            break;
        }

        usleep( 50000 );
    }

    while ( true ) {
        $orphan_meta_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT pm.meta_id FROM {$wpdb->postmeta} pm
                 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE p.ID IS NULL
                 ORDER BY pm.meta_id ASC
                 LIMIT %d",
                $batch_size
            )
        );

        if ( empty( $orphan_meta_ids ) ) {
            break;
        }

        foreach ( $orphan_meta_ids as $meta_id ) {
            $meta_id = absint( $meta_id );
            if ( $meta_id <= 0 ) {
                continue;
            }

            if ( delete_metadata_by_mid( 'post', $meta_id ) ) {
                $deleted_orphan_meta++;
            }
        }

        if ( count( $orphan_meta_ids ) < $batch_size ) {
            break;
        }

        usleep( 50000 );
    }

    return array(
        'revisions_deleted'   => $deleted_revisions,
        'orphan_meta_deleted' => $deleted_orphan_meta,
    );
}
add_action( 'developer_starter_clean_revisions', 'developer_starter_do_clean_revisions' );

/**
 * 清理已过期的 transient timeout 及其对应 value。
 * 仅删除“确认为已过期 timeout”对应的 value，避免误删无 timeout 的持久 transient。
 *
 * @return array{timeout_deleted:int,value_deleted:int}
 */
function developer_starter_cleanup_expired_transients() {
    global $wpdb;

    $result = array(
        'timeout_deleted' => 0,
        'value_deleted'   => 0,
    );

    $now = time();
    $expired_timeout_names = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT option_name
             FROM {$wpdb->options}
             WHERE option_name LIKE %s
             AND option_value < %d",
            '%_transient_timeout_%',
            $now
        )
    );

    if ( empty( $expired_timeout_names ) ) {
        return $result;
    }

    $delete_options_by_names = static function( $option_names ) use ( $wpdb ) {
        $deleted = 0;
        $chunks = array_chunk( array_values( array_unique( array_filter( array_map( 'strval', $option_names ) ) ) ), 200 );
        foreach ( $chunks as $chunk ) {
            if ( empty( $chunk ) ) {
                continue;
            }
            $placeholders = implode( ',', array_fill( 0, count( $chunk ), '%s' ) );
            $sql = $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name IN ($placeholders)",
                $chunk
            );
            $deleted += (int) $wpdb->query( $sql );
        }
        return $deleted;
    };

    $result['timeout_deleted'] = $delete_options_by_names( $expired_timeout_names );

    $expired_value_names = array();
    foreach ( $expired_timeout_names as $timeout_name ) {
        $timeout_name = (string) $timeout_name;
        if ( 0 === strpos( $timeout_name, '_transient_timeout_' ) ) {
            $expired_value_names[] = '_transient_' . substr( $timeout_name, strlen( '_transient_timeout_' ) );
        } elseif ( 0 === strpos( $timeout_name, '_site_transient_timeout_' ) ) {
            $expired_value_names[] = '_site_transient_' . substr( $timeout_name, strlen( '_site_transient_timeout_' ) );
        }
    }

    if ( ! empty( $expired_value_names ) ) {
        $result['value_deleted'] = $delete_options_by_names( $expired_value_names );
    }

    return array(
        'timeout_deleted' => max( 0, (int) $result['timeout_deleted'] ),
        'value_deleted'   => max( 0, (int) $result['value_deleted'] ),
    );
}

/**
 * 执行过期 transients / 垃圾评论清理
 */
function developer_starter_do_clean_misc_data() {
    global $wpdb;
    $transient_timeout_deleted = 0;
    $transient_value_deleted = 0;
    $spam_deleted = 0;
    $batch_size = 200;

    if ( developer_starter_maintenance_get_option( 'auto_clean_expired_transients', '' ) ) {
        $transient_cleanup = developer_starter_cleanup_expired_transients();
        $transient_timeout_deleted = isset( $transient_cleanup['timeout_deleted'] ) ? (int) $transient_cleanup['timeout_deleted'] : 0;
        $transient_value_deleted = isset( $transient_cleanup['value_deleted'] ) ? (int) $transient_cleanup['value_deleted'] : 0;
    }

    if ( developer_starter_maintenance_get_option( 'auto_clean_spam_comments', '' ) ) {
        $threshold = gmdate( 'Y-m-d H:i:s', time() - ( 7 * DAY_IN_SECONDS ) );
        while ( true ) {
            $spam_comment_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT comment_ID FROM {$wpdb->comments}
                     WHERE comment_approved = 'spam'
                     AND comment_date_gmt < %s
                     ORDER BY comment_ID ASC
                     LIMIT %d",
                    $threshold,
                    $batch_size
                )
            );

            if ( empty( $spam_comment_ids ) ) {
                break;
            }

            foreach ( $spam_comment_ids as $comment_id ) {
                $comment_id = absint( $comment_id );
                if ( $comment_id <= 0 ) {
                    continue;
                }

                if ( wp_delete_comment( $comment_id, true ) ) {
                    $spam_deleted++;
                }
            }

            if ( count( $spam_comment_ids ) < $batch_size ) {
                break;
            }

            usleep( 50000 );
        }
    }

    return array(
        'transient_timeout_deleted' => max( 0, $transient_timeout_deleted ),
        'transient_value_deleted'   => max( 0, $transient_value_deleted ),
        'spam_deleted'              => max( 0, $spam_deleted ),
    );
}
add_action( 'developer_starter_clean_misc_data', 'developer_starter_do_clean_misc_data' );

/**
 * 获取清理 REST 请求客户端 IP。
 *
 * @return string
 */
function developer_starter_get_cleanup_rest_client_ip() {
    $remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';
    if ( ! filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
        return '';
    }

    if (
        developer_starter_is_trusted_proxy_ip( $remote_addr )
    ) {
        $ip = (string) developer_starter_get_client_ip();
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return $ip;
        }
    }

    return $remote_addr;
}

/**
 * 获取管理员清理 REST nonce。
 *
 * @param WP_REST_Request $request 请求对象
 * @return string
 */
function developer_starter_get_cleanup_rest_nonce( $request ) {
    $nonce = (string) $request->get_header( 'x-wp-nonce' );
    if ( '' === $nonce ) {
        $nonce = (string) $request->get_param( '_wpnonce' );
    }

    return sanitize_text_field( $nonce );
}

/**
 * 清理外部 Cron 密钥清洗。
 *
 * @param mixed                $token   密钥。
 * @param WP_REST_Request|null $request 请求对象，兼容 REST sanitize_callback 签名。
 * @param string               $param   参数名，兼容 REST sanitize_callback 签名。
 * @return string
 */
function developer_starter_sanitize_cleanup_cron_token( $token, $request = null, $param = '' ) {
    unset( $request, $param );

    $token = preg_replace( '/[\r\n\t\s]+/', '', (string) $token );
    $token = is_string( $token ) ? $token : '';
    $token = preg_replace( '/[^A-Za-z0-9_-]/', '', $token );
    $token = is_string( $token ) ? $token : '';

    return substr( $token, 0, 128 );
}

/**
 * 生成外部 Cron 清理密钥。
 *
 * @return string
 */
function developer_starter_generate_cleanup_cron_token() {
    if ( function_exists( 'wp_generate_password' ) ) {
        return wp_generate_password( 48, false, false );
    }

    if ( function_exists( 'wp_rand' ) ) {
        return substr( hash( 'sha256', wp_rand() . '|' . microtime( true ) . '|' . home_url( '/' ) ), 0, 48 );
    }

    return substr( hash( 'sha256', mt_rand() . '|' . microtime( true ) ), 0, 48 );
}

/**
 * 确保外部 Cron 清理密钥存在，可用于后台首次打开页面和手动重新生成。
 *
 * @param bool $force 是否强制重新生成。
 * @return string
 */
function developer_starter_ensure_cleanup_cron_token( $force = false ) {
    $options = get_option( 'developer_starter_options', array() );
    if ( ! is_array( $options ) ) {
        $options = array();
    }

    $token = developer_starter_sanitize_cleanup_cron_token( isset( $options['cleanup_cron_token'] ) ? $options['cleanup_cron_token'] : '' );
    if ( ! $force && strlen( $token ) >= 16 ) {
        return $token;
    }

    $token = developer_starter_generate_cleanup_cron_token();
    $options['cleanup_cron_token'] = $token;
    update_option( 'developer_starter_options', $options );

    return $token;
}

/**
 * 获取外部 Cron 清理密钥。
 *
 * @param WP_REST_Request $request 请求对象
 * @return string
 */
function developer_starter_get_cleanup_cron_token( $request ) {
    $token = (string) $request->get_header( 'x-qiling-cleanup-token' );
    if ( '' === $token ) {
        $token = (string) $request->get_param( 'token' );
    }

    return developer_starter_sanitize_cleanup_cron_token( $token );
}

/**
 * 清洗外部 Cron 允许访问的 IP 列表。
 *
 * @param mixed $value 原始列表。
 * @return string
 */
function developer_starter_sanitize_cleanup_cron_allowed_ips( $value ) {
    $items = preg_split( '/[\r\n,\s]+/', (string) $value );
    if ( ! is_array( $items ) ) {
        return '';
    }

    $allowed_ips = array();
    foreach ( $items as $item ) {
        $ip = trim( (string) $item );
        if ( '' === $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            continue;
        }
        $allowed_ips[] = $ip;
    }

    return implode( "\n", array_values( array_unique( $allowed_ips ) ) );
}

/**
 * 获取外部 Cron 允许访问的 IP 列表。
 *
 * @return array<int,string>
 */
function developer_starter_get_cleanup_cron_allowed_ips() {
    $value = developer_starter_sanitize_cleanup_cron_allowed_ips(
        developer_starter_maintenance_get_option( 'cleanup_cron_allowed_ips', '' )
    );

    if ( '' === $value ) {
        return array();
    }

    $items = preg_split( '/\r\n|\r|\n/', $value );
    return is_array( $items ) ? array_values( array_filter( array_map( 'trim', $items ) ) ) : array();
}

/**
 * 判断外部 Cron 请求 IP 是否允许。
 *
 * @return bool
 */
function developer_starter_cleanup_cron_ip_is_allowed() {
    $allowed_ips = developer_starter_get_cleanup_cron_allowed_ips();
    if ( empty( $allowed_ips ) ) {
        return true;
    }

    $client_ip = developer_starter_get_cleanup_rest_client_ip();
    if ( '' === $client_ip ) {
        return false;
    }

    return in_array( $client_ip, $allowed_ips, true );
}

/**
 * 获取清理 REST 审计日志。
 *
 * @return array<int,array<string,mixed>>
 */
function developer_starter_get_cleanup_rest_audit_log() {
    $log = get_option( 'developer_starter_cleanup_rest_audit_log', array() );
    return is_array( $log ) ? array_values( $log ) : array();
}

/**
 * 写入清理 REST 审计日志，保留最近有限条，避免 options 膨胀。
 *
 * @param string               $event   事件类型。
 * @param WP_REST_Request|null $request 请求对象。
 * @param array<string,mixed>  $context 上下文。
 * @return void
 */
function developer_starter_add_cleanup_rest_audit_log( $event, $request = null, $context = array() ) {
    $context = is_array( $context ) ? $context : array();
    $scope = '';
    if ( $request && is_callable( array( $request, 'get_param' ) ) ) {
        $scope = sanitize_key( (string) $request->get_param( 'scope' ) );
    }

    $entry = array(
        'time'    => current_time( 'mysql' ),
        'event'   => sanitize_key( (string) $event ),
        'user_id' => get_current_user_id(),
        'ip'      => developer_starter_get_cleanup_rest_client_ip(),
        'scope'   => $scope,
        'status'  => isset( $context['status'] ) ? absint( $context['status'] ) : 0,
        'message' => isset( $context['message'] ) ? sanitize_text_field( (string) $context['message'] ) : '',
    );

    if ( isset( $context['deleted'] ) ) {
        $entry['deleted'] = absint( $context['deleted'] );
    }

    $log = developer_starter_get_cleanup_rest_audit_log();
    array_unshift( $log, $entry );

    $max_entries = (int) apply_filters( 'developer_starter_cleanup_rest_audit_log_max_entries', 30 );
    $max_entries = max( 5, min( 100, $max_entries ) );
    update_option( 'developer_starter_cleanup_rest_audit_log', array_slice( $log, 0, $max_entries ), false );
}

/**
 * 清空清理 REST 审计日志。
 *
 * @return void
 */
function developer_starter_clear_cleanup_rest_audit_log() {
    delete_option( 'developer_starter_cleanup_rest_audit_log' );
}

/**
 * 清理 REST 独立速率限制。
 *
 * @param WP_REST_Request $request 请求对象
 * @return bool
 */
function developer_starter_cleanup_rest_is_rate_limited( $request ) {
    $window = (int) apply_filters( 'developer_starter_cleanup_rest_rate_limit_window', 5 * MINUTE_IN_SECONDS );
    $max_requests = (int) apply_filters( 'developer_starter_cleanup_rest_rate_limit_max_requests', 5 );
    $window = max( MINUTE_IN_SECONDS, min( HOUR_IN_SECONDS, $window ) );
    $max_requests = max( 1, min( 30, $max_requests ) );

    $key_seed = implode(
        '|',
        array(
            get_current_user_id(),
            developer_starter_get_cleanup_rest_client_ip(),
            isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ) : '',
        )
    );
    $key = 'developer_starter_cleanup_rest_rl_' . md5( $key_seed );
    $payload = get_transient( $key );
    if ( ! is_array( $payload ) ) {
        $payload = array( 'count' => 0 );
    }

    $count = isset( $payload['count'] ) ? absint( $payload['count'] ) : 0;
    if ( $count >= $max_requests ) {
        return true;
    }

    $payload['count'] = $count + 1;
    set_transient( $key, $payload, $window );

    return false;
}

/**
 * 判断本窗口内是否需要记录一次 REST 清理限流日志。
 *
 * @return bool
 */
function developer_starter_cleanup_rest_should_log_rate_limit() {
    $key_seed = implode(
        '|',
        array(
            get_current_user_id(),
            developer_starter_get_cleanup_rest_client_ip(),
            isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ) : '',
        )
    );
    $key = 'developer_starter_cleanup_rest_rl_log_' . md5( $key_seed );

    if ( get_transient( $key ) ) {
        return false;
    }

    set_transient( $key, '1', MINUTE_IN_SECONDS );
    return true;
}

/**
 * 校验清理 REST scope 参数。
 *
 * @param mixed $value 参数值。
 * @return true|WP_Error
 */
function developer_starter_validate_cleanup_rest_scope( $value, $request = null, $param = '' ) {
    $scope = sanitize_key( (string) $value );
    if ( ! in_array( $scope, array( 'auto', 'all', 'revisions', 'misc' ), true ) ) {
        return new WP_Error( 'cleanup_rest_invalid_scope', 'Invalid cleanup scope.', array( 'status' => 400 ) );
    }

    return true;
}

/**
 * 构造清理 REST 响应，避免 GET 外部任务响应被中间层缓存。
 *
 * @param array<string,mixed> $data   响应数据。
 * @param int                 $status HTTP 状态码。
 * @return WP_REST_Response
 */
function developer_starter_cleanup_rest_response( $data, $status = 200 ) {
    $response = new WP_REST_Response( $data, $status );
    $response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
    $response->header( 'Pragma', 'no-cache' );

    return $response;
}

/**
 * 清理模块轻量双语文案。
 *
 * @param string $zh 中文文案。
 * @param string $en 英文文案。
 * @return string
 */
function developer_starter_cleanup_text( $zh, $en ) {
    $locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
    $locale = is_string( $locale ) ? strtolower( $locale ) : '';

    return 0 === strpos( $locale, 'en' ) ? $en : $zh;
}

/**
 * REST 管理员清理权限校验
 *
 * @param WP_REST_Request $request 请求对象
 * @return true|WP_Error
 */
function developer_starter_rest_cleanup_permission( $request ) {
    if ( developer_starter_cleanup_rest_is_rate_limited( $request ) ) {
        if ( developer_starter_cleanup_rest_should_log_rate_limit() ) {
            developer_starter_add_cleanup_rest_audit_log( 'rate_limited', $request, array( 'status' => 429, 'message' => 'Too many cleanup requests.' ) );
        }
        return new WP_Error( 'cleanup_rest_rate_limited', __( 'Too many cleanup requests.', 'developer-starter' ), array( 'status' => 429 ) );
    }

    if ( ! developer_starter_maintenance_get_option( 'cleanup_rest_enable', '' ) ) {
        developer_starter_add_cleanup_rest_audit_log( 'disabled', $request, array( 'status' => 403, 'message' => 'REST cleanup endpoint is disabled.' ) );
        return new WP_Error( 'cleanup_rest_disabled', __( 'REST cleanup endpoint is disabled.', 'developer-starter' ), array( 'status' => 403 ) );
    }

    $nonce = developer_starter_get_cleanup_rest_nonce( $request );
    if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
        developer_starter_add_cleanup_rest_audit_log( 'invalid_nonce', $request, array( 'status' => 401, 'message' => 'A valid administrator REST nonce is required.' ) );
        return new WP_Error( 'cleanup_rest_nonce_required', __( 'A valid administrator REST nonce is required.', 'developer-starter' ), array( 'status' => 401 ) );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        developer_starter_add_cleanup_rest_audit_log( 'forbidden', $request, array( 'status' => 403, 'message' => 'Current user cannot manage options.' ) );
        return new WP_Error( 'cleanup_rest_forbidden', __( 'Current user cannot trigger cleanup.', 'developer-starter' ), array( 'status' => 403 ) );
    }

    return true;
}

/**
 * 外部 Cron 清理权限校验。
 *
 * @param WP_REST_Request $request 请求对象
 * @return true|WP_Error
 */
function developer_starter_cleanup_cron_permission( $request ) {
    if ( developer_starter_cleanup_rest_is_rate_limited( $request ) ) {
        if ( developer_starter_cleanup_rest_should_log_rate_limit() ) {
            developer_starter_add_cleanup_rest_audit_log( 'cron_rate_limited', $request, array( 'status' => 429, 'message' => 'Too many cleanup cron requests.' ) );
        }
        return new WP_Error( 'cleanup_cron_rate_limited', __( 'Too many cleanup requests.', 'developer-starter' ), array( 'status' => 429 ) );
    }

    if ( ! developer_starter_maintenance_get_option( 'cleanup_cron_enable', '' ) ) {
        developer_starter_add_cleanup_rest_audit_log( 'cron_disabled', $request, array( 'status' => 403, 'message' => 'External cleanup cron endpoint is disabled.' ) );
        return new WP_Error( 'cleanup_cron_disabled', __( 'External cleanup cron endpoint is disabled.', 'developer-starter' ), array( 'status' => 403 ) );
    }

    if ( ! developer_starter_cleanup_cron_ip_is_allowed() ) {
        developer_starter_add_cleanup_rest_audit_log( 'cron_forbidden_ip', $request, array( 'status' => 403, 'message' => 'Current IP is not allowed.' ) );
        return new WP_Error( 'cleanup_cron_forbidden_ip', __( 'Current IP is not allowed to trigger cleanup.', 'developer-starter' ), array( 'status' => 403 ) );
    }

    $configured_token = developer_starter_sanitize_cleanup_cron_token(
        developer_starter_maintenance_get_option( 'cleanup_cron_token', '' )
    );
    if ( strlen( $configured_token ) < 16 ) {
        developer_starter_add_cleanup_rest_audit_log( 'cron_token_missing', $request, array( 'status' => 403, 'message' => 'External cleanup cron token is not configured.' ) );
        return new WP_Error( 'cleanup_cron_token_missing', __( 'External cleanup cron token is not configured.', 'developer-starter' ), array( 'status' => 403 ) );
    }

    $provided_token = developer_starter_get_cleanup_cron_token( $request );
    if ( strlen( $provided_token ) < 16 || ! hash_equals( $configured_token, $provided_token ) ) {
        developer_starter_add_cleanup_rest_audit_log( 'cron_invalid_token', $request, array( 'status' => 401, 'message' => 'Invalid external cleanup cron token.' ) );
        return new WP_Error( 'cleanup_cron_invalid_token', __( 'Invalid external cleanup cron token.', 'developer-starter' ), array( 'status' => 401 ) );
    }

    return true;
}

/**
 * 执行清理 scope。
 *
 * @param string               $scope   清理范围。
 * @param WP_REST_Request|null $request 请求对象。
 * @param string               $source  来源。
 * @return array{status:int,data:array<string,mixed>}
 */
function developer_starter_run_cleanup_scope_data( $scope, $request = null, $source = 'admin_rest' ) {
    $lock_key = 'developer_starter_cleanup_rest_lock';
    $source = sanitize_key( (string) $source );
    $event_prefix = 'cron' === $source ? 'cron_' : '';

    if ( get_transient( $lock_key ) ) {
        developer_starter_add_cleanup_rest_audit_log( $event_prefix . 'locked', $request, array( 'status' => 429, 'message' => 'Cleanup is already running.' ) );
        return array(
            'status' => 429,
            'data'   => array(
                'success' => false,
                'message' => developer_starter_cleanup_text( '清理任务正在执行中。', 'Cleanup is already running.' ),
            ),
        );
    }
    set_transient( $lock_key, '1', MINUTE_IN_SECONDS );

    $scope = sanitize_key( (string) $scope );
    if ( ! in_array( $scope, array( 'auto', 'all', 'revisions', 'misc' ), true ) ) {
        $scope = 'auto';
    }

    $run_revisions = false;
    $run_misc = false;

    if ( $scope === 'all' ) {
        $run_revisions = true;
        $run_misc = true;
    } elseif ( $scope === 'revisions' ) {
        $run_revisions = true;
    } elseif ( $scope === 'misc' ) {
        $run_misc = true;
    } else {
        $run_revisions = (bool) developer_starter_maintenance_get_option( 'auto_clean_revisions', '' );
        $run_misc = (bool) developer_starter_maintenance_get_option( 'auto_clean_expired_transients', '' ) || (bool) developer_starter_maintenance_get_option( 'auto_clean_spam_comments', '' );
    }

    if ( ! $run_revisions && ! $run_misc ) {
        delete_transient( $lock_key );
        developer_starter_add_cleanup_rest_audit_log( $event_prefix . 'skipped', $request, array( 'status' => 400, 'message' => 'No cleanup items are enabled.' ) );
        return array(
            'status' => 400,
            'data'   => array(
                'success' => false,
                'message' => developer_starter_cleanup_text( '没有启用可清理的项目。', 'No cleanup items are enabled.' ),
                'scope'   => $scope,
                'source'  => $source,
            ),
        );
    }

    $result = array(
        'scope'  => $scope,
        'source' => $source,
    );

    if ( $run_revisions ) {
        $result['revisions'] = developer_starter_do_clean_revisions();
    }
    if ( $run_misc ) {
        $result['misc'] = developer_starter_do_clean_misc_data();
    }

    update_option( 'cron' === $source ? 'developer_starter_cleanup_cron_last_run' : 'developer_starter_cleanup_rest_last_run', current_time( 'mysql' ), false );
    delete_transient( $lock_key );

    $deleted_total = 0;
    foreach ( $result as $result_group ) {
        if ( ! is_array( $result_group ) ) {
            continue;
        }
        foreach ( $result_group as $value ) {
            if ( is_numeric( $value ) ) {
                $deleted_total += max( 0, (int) $value );
            }
        }
    }
    developer_starter_add_cleanup_rest_audit_log(
        $event_prefix . 'success',
        $request,
        array(
            'status'  => 200,
            'message' => 'Cleanup completed.',
            'deleted' => $deleted_total,
        )
    );

    return array(
        'status' => 200,
        'data'   => array(
            'success' => true,
            'message' => developer_starter_cleanup_text( '清理完成。', 'Cleanup completed.' ),
            'data'    => $result,
        ),
    );
}

/**
 * 执行清理 scope 并构造 REST 响应。
 *
 * @param string               $scope   清理范围。
 * @param WP_REST_Request|null $request 请求对象。
 * @param string               $source  来源。
 * @return WP_REST_Response
 */
function developer_starter_run_cleanup_scope( $scope, $request = null, $source = 'admin_rest' ) {
    $result = developer_starter_run_cleanup_scope_data( $scope, $request, $source );
    $status = isset( $result['status'] ) ? absint( $result['status'] ) : 500;
    $data = isset( $result['data'] ) && is_array( $result['data'] ) ? $result['data'] : array(
        'success' => false,
        'message' => developer_starter_cleanup_text( '清理失败。', 'Cleanup failed.' ),
    );

    return developer_starter_cleanup_rest_response( $data, $status );
}

/**
 * REST 管理员触发清理任务
 *
 * @param WP_REST_Request $request 请求对象
 * @return WP_REST_Response
 */
function developer_starter_rest_cleanup_run( $request ) {
    return developer_starter_run_cleanup_scope( (string) $request->get_param( 'scope' ), $request, 'admin_rest' );
}

/**
 * 外部 Cron 触发清理任务。
 *
 * @param WP_REST_Request $request 请求对象
 * @return WP_REST_Response
 */
function developer_starter_cleanup_cron_run( $request ) {
    return developer_starter_run_cleanup_scope( (string) $request->get_param( 'scope' ), $request, 'cron' );
}

/**
 * 注册管理员清理 REST 路由
 *
 * @return void
 */
function developer_starter_register_cleanup_rest_route() {
    register_rest_route(
        'qiling/v1',
        '/maintenance/cleanup',
        array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => 'developer_starter_rest_cleanup_run',
                'permission_callback' => 'developer_starter_rest_cleanup_permission',
                'args'                => array(
                    'scope' => array(
                        'description'       => __( 'Cleanup scope.', 'developer-starter' ),
                        'type'              => 'string',
                        'default'           => 'auto',
                        'enum'              => array( 'auto', 'all', 'revisions', 'misc' ),
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => 'developer_starter_validate_cleanup_rest_scope',
                    ),
                ),
            ),
        )
    );

    register_rest_route(
        'qiling/v1',
        '/maintenance/cleanup/cron',
        array(
            array(
                'methods'             => array( WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ),
                'callback'            => 'developer_starter_cleanup_cron_run',
                'permission_callback' => 'developer_starter_cleanup_cron_permission',
                'args'                => array(
                    'scope' => array(
                        'description'       => __( 'Cleanup scope.', 'developer-starter' ),
                        'type'              => 'string',
                        'default'           => 'auto',
                        'enum'              => array( 'auto', 'all', 'revisions', 'misc' ),
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => 'developer_starter_validate_cleanup_rest_scope',
                    ),
                    'token' => array(
                        'description'       => __( 'External cleanup cron token.', 'developer-starter' ),
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'developer_starter_sanitize_cleanup_cron_token',
                    ),
                ),
            ),
        )
    );
}
add_action( 'rest_api_init', 'developer_starter_register_cleanup_rest_route' );

/**
 * 图片优化功能
 */
function developer_starter_image_optimizations() {
    if ( developer_starter_maintenance_get_option( 'disable_default_thumbnails', '' ) ) {
        add_filter( 'big_image_size_threshold', '__return_false' );
    }

    if ( developer_starter_maintenance_get_option( 'disable_image_sizes', '' ) ) {
        add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
        add_filter( 'intermediate_image_sizes', '__return_empty_array' );

        add_action( 'init', function() {
            remove_image_size( 'thumbnail' );
            remove_image_size( 'medium' );
            remove_image_size( 'medium_large' );
            remove_image_size( 'large' );
            remove_image_size( '1536x1536' );
            remove_image_size( '2048x2048' );
        } );
    }
}
add_action( 'after_setup_theme', 'developer_starter_image_optimizations', 999 );
