<?php
/**
 * Admin Settings Ajax Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Ajax_Trait {

    private function get_thumbnail_cache_optimizer() {
        return function_exists( 'developer_starter_get_thumbnail_optimizer_instance' )
            ? developer_starter_get_thumbnail_optimizer_instance()
            : null;
    }

    public function ajax_thumbnail_cache_stats() {
        check_ajax_referer( 'thumbnail_cache_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }
        $optimizer = $this->get_thumbnail_cache_optimizer();
        if ( ! $optimizer ) {
            wp_send_json_error( array( 'message' => __( '缩略图服务未加载', 'developer-starter' ) ), 500 );
        }
        wp_send_json_success( $optimizer->get_cache_stats() );
    }

    public function ajax_clear_thumbnail_cache() {
        check_ajax_referer( 'thumbnail_cache_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }
        $optimizer = $this->get_thumbnail_cache_optimizer();
        if ( ! $optimizer ) {
            wp_send_json_error( array( 'message' => __( '缩略图服务未加载', 'developer-starter' ) ), 500 );
        }
        $result = $optimizer->clear_all_cache();
        $result['stats'] = $optimizer->get_cache_stats();
        $result['message'] = sprintf( __( '已删除 %1$s 个缓存文件，释放 %2$s。', 'developer-starter' ), number_format_i18n( $result['deleted_files'] ), size_format( $result['freed_bytes'], 2 ) );
        if ( ! empty( $result['failed_files'] ) ) {
            $result['message'] .= ' ' . sprintf( __( '%s 个文件删除失败，请检查目录权限。', 'developer-starter' ), number_format_i18n( $result['failed_files'] ) );
        }
        wp_send_json_success( $result );
    }

    public function ajax_clear_unused_thumbnail_cache() {
        check_ajax_referer( 'thumbnail_cache_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }
        $optimizer = $this->get_thumbnail_cache_optimizer();
        if ( ! $optimizer ) {
            wp_send_json_error( array( 'message' => __( '缩略图服务未加载', 'developer-starter' ) ), 500 );
        }
        $result = $optimizer->clear_unused_cache();
        $result['stats'] = $optimizer->get_cache_stats();
        $result['message'] = sprintf( __( '扫描完成，已清理 %1$s 个未使用缓存，释放 %2$s。', 'developer-starter' ), number_format_i18n( $result['deleted_files'] ), size_format( $result['freed_bytes'], 2 ) );
        if ( ! empty( $result['failed_files'] ) ) {
            $result['message'] .= ' ' . sprintf( __( '%s 个文件删除失败，请检查目录权限。', 'developer-starter' ), number_format_i18n( $result['failed_files'] ) );
        }
        wp_send_json_success( $result );
    }

    /**
     * AJAX 刷新资源版本号
     */
    public function ajax_refresh_version() {
        check_ajax_referer( 'refresh_version_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        
        // 生成新版本号（时间戳）
        $new_version = date( 'ymd.His' );
        
        // 保存到选项
        $options = get_option( $this->option_name, array() );
        $options['assets_version'] = $new_version;
        update_option( $this->option_name, $options );
        
        wp_send_json_success( array( 'version' => $new_version ) );
    }

    /**
     * AJAX 发送 SMTP 测试邮件
     */
    public function ajax_send_smtp_test_email() {
        check_ajax_referer( 'smtp_test_email_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $smtp_host = trim( (string) developer_starter_get_option( 'smtp_host', '' ) );
        if ( $smtp_host === '' ) {
            wp_send_json_error( array( 'message' => __( '请先配置并保存 SMTP 服务器后再测试。', 'developer-starter' ) ) );
        }

        $current_user = wp_get_current_user();
        $to = '';
        if ( $current_user instanceof \WP_User && ! empty( $current_user->user_email ) ) {
            $to = sanitize_email( (string) $current_user->user_email );
        }
        if ( $to === '' ) {
            $to = sanitize_email( (string) get_option( 'admin_email' ) );
        }
        if ( $to === '' ) {
            wp_send_json_error( array( 'message' => __( '未找到可用的收件邮箱。', 'developer-starter' ) ) );
        }

        $subject = sprintf(
            __( '[%s] SMTP 测试邮件', 'developer-starter' ),
            wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
        );

        $message = implode(
            "\n",
            array(
                __( '这是一封来自主题设置页的 SMTP 测试邮件。', 'developer-starter' ),
                '',
                sprintf( __( '站点：%s', 'developer-starter' ), home_url( '/' ) ),
                sprintf( __( '发送时间：%s', 'developer-starter' ), current_time( 'Y-m-d H:i:s' ) ),
                sprintf( __( '接收邮箱：%s', 'developer-starter' ), $to ),
            )
        );

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        if ( function_exists( 'developer_starter_build_html_email_template' ) ) {
            $html_message = developer_starter_build_html_email_template(
                array(
                    'title'  => __( 'SMTP 测试邮件', 'developer-starter' ),
                    'intro'  => __( '这是一封来自主题设置页的 SMTP 测试邮件。', 'developer-starter' ),
                    'lines'  => array(
                        __( '站点', 'developer-starter' )   => home_url( '/' ),
                        __( '发送时间', 'developer-starter' ) => current_time( 'Y-m-d H:i:s' ),
                        __( '接收邮箱', 'developer-starter' ) => $to,
                    ),
                    'notice' => __( '如果您收到本邮件，说明当前 SMTP 配置已生效。', 'developer-starter' ),
                )
            );
            if ( is_string( $html_message ) && trim( $html_message ) !== '' ) {
                $message = $html_message;
                $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            }
        }

        $mail_error_message = '';
        $mail_failed_handler = function( $wp_error ) use ( &$mail_error_message ) {
            if ( is_wp_error( $wp_error ) ) {
                $mail_error_message = $wp_error->get_error_message();
            }
        };

        add_action( 'wp_mail_failed', $mail_failed_handler );
        $sent = wp_mail( $to, $subject, $message, $headers );
        remove_action( 'wp_mail_failed', $mail_failed_handler );

        if ( ! $sent ) {
            $msg = __( '测试邮件发送失败，请检查 SMTP 配置。', 'developer-starter' );
            if ( $mail_error_message !== '' ) {
                $msg .= ' ' . $mail_error_message;
            }
            wp_send_json_error( array( 'message' => $msg ) );
        }

        wp_send_json_success(
            array(
                'message' => sprintf(
                    __( '测试邮件已发送到：%s，请检查收件箱（及垃圾箱）。', 'developer-starter' ),
                    $to
                ),
            )
        );
    }

    /**
     * AJAX 测试生成连接
     *
     * @return void
     */
    public function ajax_test_ai_connection() {
        check_ajax_referer( 'ai_connection_test_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        if ( ! class_exists( '\Developer_Starter\Core\AI_Decorator' ) ) {
            wp_send_json_error( array( 'message' => __( 'AI 服务尚未加载，请稍后重试。', 'developer-starter' ) ), 500 );
        }

        $connection_id = isset( $_POST['connection_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['connection_id'] ) ) : '';
        $stored_connection_id = isset( $_POST['stored_connection_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['stored_connection_id'] ) ) : '';
        $connection_name = isset( $_POST['connection_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['connection_name'] ) ) : '';
        $endpoint = isset( $_POST['endpoint'] ) ? trim( (string) wp_unslash( $_POST['endpoint'] ) ) : '';
        $model = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model'] ) ) : '';
        $api_key = isset( $_POST['api_key'] ) ? trim( (string) wp_unslash( $_POST['api_key'] ) ) : '';
        $api_key_existing = isset( $_POST['api_key_existing'] ) && '1' === (string) wp_unslash( $_POST['api_key_existing'] );
        $json_mode = isset( $_POST['json_mode'] ) && '1' === (string) wp_unslash( $_POST['json_mode'] ) ? '1' : '';

        if ( '' === $stored_connection_id ) {
            $stored_connection_id = $connection_id;
        }

        $saved_connection = $this->get_saved_ai_connection_by_id( $stored_connection_id );
        if ( '' === $connection_name && ! empty( $saved_connection['name'] ) ) {
            $connection_name = (string) $saved_connection['name'];
        }
        if ( '' === $endpoint && ! empty( $saved_connection['endpoint'] ) ) {
            $endpoint = (string) $saved_connection['endpoint'];
        }
        if ( '' === $model && ! empty( $saved_connection['default_model'] ) ) {
            $model = (string) $saved_connection['default_model'];
        }
        if ( '' === $api_key && $api_key_existing && ! empty( $saved_connection['api_key'] ) ) {
            $api_key = (string) $saved_connection['api_key'];
        }
        if ( ! isset( $_POST['json_mode'] ) && isset( $saved_connection['json_mode'] ) ) {
            $json_mode = ! empty( $saved_connection['json_mode'] ) ? '1' : '';
        }

        $result = \Developer_Starter\Core\AI_Decorator::get_instance()->test_connection(
            array(
                'id'        => $connection_id,
                'name'      => $connection_name,
                'endpoint'  => $endpoint,
                'model'     => $model,
                'api_key'   => $api_key,
                'json_mode' => $json_mode,
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error(
                array(
                    'message' => $result->get_error_message(),
                ),
                400
            );
        }

        $message = (string) $result['message'];
        if ( ! empty( $result['reply'] ) ) {
            $message .= ' ' . sprintf(
                /* translators: %s: assistant reply excerpt */
                __( '模型返回：%s', 'developer-starter' ),
                (string) $result['reply']
            );
        }

        wp_send_json_success(
            array(
                'message' => $message,
                'reply'   => isset( $result['reply'] ) ? (string) $result['reply'] : '',
                'model'   => isset( $result['model'] ) ? (string) $result['model'] : '',
            )
        );
    }

    /**
     * AJAX 手动执行轻量 SEO 健康检查。
     */
    public function ajax_seo_health_scan() {
        check_ajax_referer( 'developer_starter_seo_health', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        if ( ! class_exists( '\Developer_Starter\SEO\SEO_Health_Check' ) ) {
            wp_send_json_error( array( 'message' => __( 'SEO 健康检查服务未加载。', 'developer-starter' ) ), 500 );
        }

        $limit = isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 200;
        $snapshot = \Developer_Starter\SEO\SEO_Health_Check::run_scan( $limit );

        wp_send_json_success(
            array(
                'message'  => __( 'SEO 健康检查已完成，结果已作为临时快照保存。', 'developer-starter' ),
                'snapshot' => $snapshot,
            )
        );
    }

    /**
     * AJAX 清除 SEO 健康检查临时快照。
     */
    public function ajax_seo_health_clear() {
        check_ajax_referer( 'developer_starter_seo_health', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        if ( class_exists( '\Developer_Starter\SEO\SEO_Health_Check' ) ) {
            \Developer_Starter\SEO\SEO_Health_Check::clear_snapshot();
        }

        wp_send_json_success(
            array(
                'message' => __( '已清除 SEO 体检临时结果。', 'developer-starter' ),
            )
        );
    }
    
    /**
     * AJAX 数据库清理
     */
    public function ajax_db_cleanup() {
        check_ajax_referer( 'db_cleanup_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $allowed_items = $this->get_db_cleanup_allowed_items();
        $items_raw = isset( $_POST['items'] ) ? (array) wp_unslash( $_POST['items'] ) : array();
        $items = array_values(
            array_intersect(
                array_map(
                    static function( $item ) {
                        return sanitize_key( (string) $item );
                    },
                    $items_raw
                ),
                $allowed_items
            )
        );

        if ( empty( $items ) ) {
            wp_send_json_success(
                array(
                    'message' => __( '请至少选择一个清理项', 'developer-starter' ),
                    'has_more' => false,
                    'deleted'  => 0,
                )
            );
        }

        $cursor = isset( $_POST['cursor'] ) ? absint( wp_unslash( $_POST['cursor'] ) ) : 0;
        $cursor = max( 0, min( $cursor, count( $items ) ) );

        $batch_size = isset( $_POST['batch_size'] ) ? absint( wp_unslash( $_POST['batch_size'] ) ) : 200;
        $batch_size = max( 20, min( 500, $batch_size ) );

        if ( $cursor >= count( $items ) ) {
            wp_send_json_success(
                array(
                    'message'   => __( '清理完成', 'developer-starter' ),
                    'has_more'  => false,
                    'deleted'   => 0,
                    'cursor'    => $cursor,
                    'next_cursor' => $cursor,
                    'total_items' => count( $items ),
                )
            );
        }

        $item = $items[ $cursor ];
        $batch_result = $this->cleanup_db_item_batch( $item, $batch_size );
        if ( is_wp_error( $batch_result ) ) {
            wp_send_json_error(
                array(
                    'message' => $batch_result->get_error_message(),
                )
            );
        }

        $deleted = isset( $batch_result['deleted'] ) ? max( 0, (int) $batch_result['deleted'] ) : 0;
        $item_done = empty( $batch_result['has_more'] );
        $next_cursor = $item_done ? $cursor + 1 : $cursor;
        $has_more = ! $item_done || $next_cursor < count( $items );
        $item_label = $this->get_db_cleanup_item_label( $item );
        $processed_items = $item_done ? min( $next_cursor, count( $items ) ) : $cursor;

        $message = sprintf(
            /* translators: 1: cleanup item label, 2: deleted count */
            __( '已处理 %1$s，本批清理 %2$d 条。', 'developer-starter' ),
            $item_label,
            $deleted
        );
        if ( $has_more ) {
            $message .= ' ' . sprintf(
                /* translators: 1: processed items, 2: total items */
                __( '继续处理中（%1$d/%2$d）…', 'developer-starter' ),
                max( 1, $processed_items + 1 ),
                count( $items )
            );
        } elseif ( 0 === $deleted ) {
            $message = __( '数据库已经很干净，没有需要清理的数据', 'developer-starter' );
        }

        $response = array(
            'message'         => $message,
            'item'            => $item,
            'item_label'      => $item_label,
            'deleted'         => $deleted,
            'cursor'          => $cursor,
            'next_cursor'     => $next_cursor,
            'has_more'        => (bool) $has_more,
            'is_item_done'    => (bool) $item_done,
            'processed_items' => $processed_items,
            'total_items'     => count( $items ),
        );

        if ( ! empty( $batch_result['extra'] ) && is_array( $batch_result['extra'] ) ) {
            $response['extra'] = $batch_result['extra'];
        }

        wp_send_json_success( $response );
    }

    /**
     * AJAX 清空管理员 REST 清理审计日志。
     *
     * @return void
     */
    public function ajax_clear_cleanup_rest_audit_log() {
        check_ajax_referer( 'cleanup_rest_audit_log_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        if ( function_exists( 'developer_starter_clear_cleanup_rest_audit_log' ) ) {
            developer_starter_clear_cleanup_rest_audit_log();
        } else {
            delete_option( 'developer_starter_cleanup_rest_audit_log' );
        }

        wp_send_json_success( array( 'message' => __( '清理日志已清空。', 'developer-starter' ) ) );
    }

    /**
     * AJAX 执行主题定时清理。
     *
     * @return void
     */
    public function ajax_run_theme_cleanup() {
        check_ajax_referer( 'theme_cleanup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        if ( ! function_exists( 'developer_starter_run_cleanup_scope_data' ) ) {
            wp_send_json_error( array( 'message' => __( '主题清理器未加载。', 'developer-starter' ) ), 500 );
        }

        $scope = isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( (string) $_POST['scope'] ) ) : 'auto';
        if ( ! in_array( $scope, array( 'auto', 'all', 'revisions', 'misc' ), true ) ) {
            $scope = 'auto';
        }

        $request = class_exists( '\WP_REST_Request' ) ? new \WP_REST_Request( 'POST', '/qiling/v1/maintenance/cleanup/admin' ) : null;
        if ( $request && is_callable( array( $request, 'set_param' ) ) ) {
            $request->set_param( 'scope', $scope );
        }

        $result = developer_starter_run_cleanup_scope_data( $scope, $request, 'admin_ajax' );
        $status = isset( $result['status'] ) ? absint( $result['status'] ) : 500;
        $data = isset( $result['data'] ) && is_array( $result['data'] ) ? $result['data'] : array( 'message' => __( '清理失败。', 'developer-starter' ) );

        if ( $status >= 400 ) {
            wp_send_json_error( $data, $status );
        }

        wp_send_json_success( $data );
    }

    /**
     * AJAX 重新生成外部定时清理 token。
     *
     * @return void
     */
    public function ajax_regenerate_cleanup_cron_token() {
        check_ajax_referer( 'cleanup_cron_token_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        if ( ! function_exists( 'developer_starter_ensure_cleanup_cron_token' ) ) {
            wp_send_json_error( array( 'message' => __( '外部定时清理 token 生成器未加载。', 'developer-starter' ) ), 500 );
        }

        $token = developer_starter_ensure_cleanup_cron_token( true );
        $endpoint = rest_url( 'qiling/v1/maintenance/cleanup/cron' );
        $url = add_query_arg(
            array(
                'scope' => 'auto',
                'token' => $token,
            ),
            $endpoint
        );
        $header_url = add_query_arg( 'scope', 'auto', $endpoint );
        $header = 'X-Qiling-Cleanup-Token: ' . $token;
        $header_curl = 'curl -fsS -X POST -H "' . $header . '" "' . $header_url . '"';

        wp_send_json_success(
            array(
                'message'     => __( '外部定时清理 token 已重新生成。', 'developer-starter' ),
                'token'       => $token,
                'url'         => $url,
                'header_url'  => $header_url,
                'header'      => $header,
                'header_curl' => $header_curl,
            )
        );
    }

    /**
     * 获取允许清理的数据库项目。
     *
     * @return array<int,string>
     */
    private function get_db_cleanup_allowed_items() {
        return array(
            'revisions',
            'drafts',
            'trash',
            'spam',
            'orphan_postmeta',
            'orphan_commentmeta',
            'orphan_relationships',
            'pingbacks',
            'unused_tags',
            'transients',
            'post_views',
            'package_trash_pages',
        );
    }

    /**
     * 从已保存配置中读取生成连接信息。
     *
     * @param string $connection_id 连接 ID。
     * @return array<string,mixed>
     */
    private function get_saved_ai_connection_by_id( $connection_id ) {
        $connection_id = sanitize_key( (string) $connection_id );
        if ( '' === $connection_id ) {
            return array();
        }

        $options = get_option( $this->option_name, array() );
        $connections = isset( $options['ai_connections'] ) && is_array( $options['ai_connections'] )
            ? $options['ai_connections']
            : array();

        foreach ( $connections as $connection ) {
            if ( ! is_array( $connection ) ) {
                continue;
            }

            $current_id = isset( $connection['id'] ) ? sanitize_key( (string) $connection['id'] ) : '';
            if ( $current_id !== $connection_id ) {
                continue;
            }

            return array(
                'id'            => $current_id,
                'name'          => isset( $connection['name'] ) ? sanitize_text_field( (string) $connection['name'] ) : '',
                'endpoint'      => isset( $connection['endpoint'] ) ? esc_url_raw( (string) $connection['endpoint'], array( 'https' ) ) : '',
                'default_model' => isset( $connection['default_model'] ) ? sanitize_text_field( (string) $connection['default_model'] ) : '',
                'api_key'       => isset( $connection['api_key'] ) ? sanitize_text_field( (string) $connection['api_key'] ) : '',
                'json_mode'     => ! empty( $connection['json_mode'] ) && (string) $connection['json_mode'] === '1',
            );
        }

        return array();
    }

    /**
     * 获取清理项目中文名称。
     *
     * @param string $item 项目标识。
     * @return string
     */
    private function get_db_cleanup_item_label( $item ) {
        $labels = array(
            'revisions'           => __( '修订版本', 'developer-starter' ),
            'drafts'              => __( '自动草稿', 'developer-starter' ),
            'trash'               => __( '回收站文章', 'developer-starter' ),
            'spam'                => __( '垃圾评论', 'developer-starter' ),
            'orphan_postmeta'     => __( '孤立文章元数据', 'developer-starter' ),
            'orphan_commentmeta'  => __( '孤立评论元数据', 'developer-starter' ),
            'orphan_relationships'=> __( '孤立关系数据', 'developer-starter' ),
            'pingbacks'           => __( 'Pingback/Trackback', 'developer-starter' ),
            'unused_tags'         => __( '未使用标签', 'developer-starter' ),
            'transients'          => __( '过期缓存', 'developer-starter' ),
            'package_trash_pages' => __( '数据包回收站页面', 'developer-starter' ),
        );

        $labels['post_views'] = __( '文章浏览量', 'developer-starter' );

        return isset( $labels[ $item ] ) ? $labels[ $item ] : sanitize_text_field( (string) $item );
    }

    /**
     * 按批次清理单个项目，避免一次性大 SQL。
     *
     * @param string $item       清理项目。
     * @param int    $batch_size 批次大小。
     * @return array<string,mixed>|\WP_Error
     */
    private function cleanup_db_item_batch( $item, $batch_size = 200 ) {
        global $wpdb;

        $batch_size = max( 20, min( 500, absint( $batch_size ) ) );
        $deleted = 0;
        $has_more = false;
        $extra = array();

        switch ( $item ) {
            case 'revisions':
                $revision_result = $this->cleanup_revision_posts_batch( $batch_size );
                $deleted = (int) $revision_result['deleted'];
                $has_more = ! empty( $revision_result['has_more'] );
                break;

            case 'drafts':
                $draft_result = $this->cleanup_posts_by_status_batch( 'auto-draft', $batch_size );
                $deleted = (int) $draft_result['deleted'];
                $has_more = ! empty( $draft_result['has_more'] );
                break;

            case 'trash':
                $trash_result = $this->cleanup_posts_by_status_batch( 'trash', $batch_size );
                $deleted = (int) $trash_result['deleted'];
                $has_more = ! empty( $trash_result['has_more'] );
                break;

            case 'spam':
                $spam_result = $this->cleanup_comments_by_status_batch( 'spam', $batch_size );
                $deleted = (int) $spam_result['deleted'];
                $has_more = ! empty( $spam_result['has_more'] );
                break;

            case 'orphan_postmeta':
                $orphan_postmeta_result = $this->cleanup_orphan_postmeta_batch( $batch_size );
                $deleted = (int) $orphan_postmeta_result['deleted'];
                $has_more = ! empty( $orphan_postmeta_result['has_more'] );
                break;

            case 'orphan_commentmeta':
                $orphan_commentmeta_result = $this->cleanup_orphan_commentmeta_batch( $batch_size );
                $deleted = (int) $orphan_commentmeta_result['deleted'];
                $has_more = ! empty( $orphan_commentmeta_result['has_more'] );
                break;

            case 'orphan_relationships':
                $orphan_relationships_result = $this->cleanup_orphan_relationships_batch( $batch_size );
                $deleted = (int) $orphan_relationships_result['deleted'];
                $has_more = ! empty( $orphan_relationships_result['has_more'] );
                break;

            case 'pingbacks':
                $pingback_result = $this->cleanup_comments_by_type_batch( array( 'pingback', 'trackback' ), $batch_size );
                $deleted = (int) $pingback_result['deleted'];
                $has_more = ! empty( $pingback_result['has_more'] );
                break;

            case 'unused_tags':
                $tag_result = $this->cleanup_unused_tags_batch( min( 200, $batch_size ) );
                $deleted = (int) $tag_result['deleted'];
                $has_more = ! empty( $tag_result['has_more'] );
                break;

            case 'transients':
                $transient_result = $this->cleanup_expired_transients_batch( $batch_size );
                $deleted = (int) $transient_result['deleted'];
                $has_more = ! empty( $transient_result['has_more'] );
                $extra = array(
                    'timeout_deleted' => (int) $transient_result['timeout_deleted'],
                    'value_deleted'   => (int) $transient_result['value_deleted'],
                );
                break;

            case 'post_views':
                $post_view_result = $this->cleanup_post_views_batch( $batch_size );
                $deleted = (int) $post_view_result['deleted'];
                $has_more = ! empty( $post_view_result['has_more'] );
                break;

            case 'package_trash_pages':
                $package_result = $this->cleanup_site_package_trash_pages_batch( min( 100, $batch_size ) );
                $deleted = (int) $package_result['deleted'];
                $has_more = ! empty( $package_result['has_more'] );
                break;

            default:
                return new \WP_Error( 'invalid_cleanup_item', __( '无效的清理项', 'developer-starter' ) );
        }

        if ( $deleted < 0 ) {
            $deleted = 0;
        }

        return array(
            'deleted'  => $deleted,
            'has_more' => (bool) $has_more,
            'extra'    => $extra,
        );
    }

    /**
     * 通过 WordPress API 批量删除修订版本，避免绕过核心清理逻辑。
     *
     * @param int $batch_size 批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_revision_posts_batch( $batch_size ) {
        global $wpdb;

        $revision_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' ORDER BY ID ASC LIMIT %d",
                $batch_size
            )
        );

        $deleted = 0;
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
                $deleted++;
            }
        }

        return array(
            'deleted'  => $deleted,
            'has_more' => count( $revision_ids ) >= $batch_size,
        );
    }

    /**
     * 通过 WordPress API 按状态批量删除文章。
     *
     * @param string $post_status 文章状态。
     * @param int    $batch_size  批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_posts_by_status_batch( $post_status, $batch_size ) {
        global $wpdb;

        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s ORDER BY ID ASC LIMIT %d",
                $post_status,
                $batch_size
            )
        );

        $deleted = 0;
        foreach ( $post_ids as $post_id ) {
            $post_id = absint( $post_id );
            if ( $post_id <= 0 ) {
                continue;
            }

            if ( wp_delete_post( $post_id, true ) ) {
                $deleted++;
            }
        }

        return array(
            'deleted'  => $deleted,
            'has_more' => count( $post_ids ) >= $batch_size,
        );
    }

    /**
     * 通过 WordPress API 按状态批量删除评论。
     *
     * @param string $comment_status 评论状态。
     * @param int    $batch_size     批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_comments_by_status_batch( $comment_status, $batch_size ) {
        global $wpdb;

        $comment_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = %s ORDER BY comment_ID ASC LIMIT %d",
                $comment_status,
                $batch_size
            )
        );

        return $this->delete_comments_batch( $comment_ids, $batch_size );
    }

    /**
     * 通过 WordPress API 按类型批量删除评论。
     *
     * @param array<int,string> $comment_types 评论类型。
     * @param int               $batch_size    批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_comments_by_type_batch( $comment_types, $batch_size ) {
        global $wpdb;

        $comment_types = array_values(
            array_filter(
                array_map( 'sanitize_key', (array) $comment_types )
            )
        );

        if ( empty( $comment_types ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $placeholders = implode( ',', array_fill( 0, count( $comment_types ), '%s' ) );
        $query_args = array_merge(
            array( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_type IN ($placeholders) ORDER BY comment_ID ASC LIMIT %d" ),
            $comment_types,
            array( $batch_size )
        );
        $comment_ids = $wpdb->get_col( call_user_func_array( array( $wpdb, 'prepare' ), $query_args ) );

        return $this->delete_comments_batch( $comment_ids, $batch_size );
    }

    /**
     * 批量删除评论 ID 列表。
     *
     * @param array<int,mixed> $comment_ids 评论 ID 列表。
     * @param int              $batch_size  批次大小。
     * @return array<string,mixed>
     */
    private function delete_comments_batch( $comment_ids, $batch_size ) {
        $deleted = 0;

        foreach ( (array) $comment_ids as $comment_id ) {
            $comment_id = absint( $comment_id );
            if ( $comment_id <= 0 ) {
                continue;
            }

            if ( wp_delete_comment( $comment_id, true ) ) {
                $deleted++;
            }
        }

        return array(
            'deleted'  => $deleted,
            'has_more' => count( (array) $comment_ids ) >= $batch_size,
        );
    }

    /**
     * 批量清理孤立的文章元数据（仅一批）。
     *
     * @param int $limit 批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_orphan_postmeta_batch( $limit = 200 ) {
        global $wpdb;

        $limit = max( 20, min( 500, absint( $limit ) ) );
        $meta_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT pm.meta_id
                 FROM {$wpdb->postmeta} pm
                 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE p.ID IS NULL
                 ORDER BY pm.meta_id ASC
                 LIMIT %d",
                $limit
            )
        );

        $meta_ids = array_values(
            array_filter(
                array_map( 'absint', (array) $meta_ids )
            )
        );

        if ( empty( $meta_ids ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $placeholders = implode( ',', array_fill( 0, count( $meta_ids ), '%d' ) );
        $delete_sql = call_user_func_array(
            array( $wpdb, 'prepare' ),
            array_merge(
                array( "DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ({$placeholders})" ),
                $meta_ids
            )
        );
        $deleted = (int) $wpdb->query( $delete_sql );

        return array(
            'deleted'  => max( 0, $deleted ),
            'has_more' => count( $meta_ids ) >= $limit,
        );
    }

    /**
     * 批量清理孤立的评论元数据（仅一批）。
     *
     * @param int $limit 批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_orphan_commentmeta_batch( $limit = 200 ) {
        global $wpdb;

        $limit = max( 20, min( 500, absint( $limit ) ) );
        $meta_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT cm.meta_id
                 FROM {$wpdb->commentmeta} cm
                 LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id
                 WHERE c.comment_ID IS NULL
                 ORDER BY cm.meta_id ASC
                 LIMIT %d",
                $limit
            )
        );

        $meta_ids = array_values(
            array_filter(
                array_map( 'absint', (array) $meta_ids )
            )
        );

        if ( empty( $meta_ids ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $placeholders = implode( ',', array_fill( 0, count( $meta_ids ), '%d' ) );
        $delete_sql = call_user_func_array(
            array( $wpdb, 'prepare' ),
            array_merge(
                array( "DELETE FROM {$wpdb->commentmeta} WHERE meta_id IN ({$placeholders})" ),
                $meta_ids
            )
        );
        $deleted = (int) $wpdb->query( $delete_sql );

        return array(
            'deleted'  => max( 0, $deleted ),
            'has_more' => count( $meta_ids ) >= $limit,
        );
    }

    /**
     * 批量清理孤立的分类关系（仅一批）。
     *
     * @param int $limit 批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_orphan_relationships_batch( $limit = 200 ) {
        global $wpdb;

        $limit = max( 20, min( 500, absint( $limit ) ) );
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT tr.object_id, tr.term_taxonomy_id
                 FROM {$wpdb->term_relationships} tr
                 LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id
                 WHERE p.ID IS NULL
                 ORDER BY tr.object_id ASC, tr.term_taxonomy_id ASC
                 LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        if ( empty( $rows ) || ! is_array( $rows ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $conditions = array();
        $prepare_args = array();

        foreach ( $rows as $row ) {
            $object_id = isset( $row['object_id'] ) ? absint( $row['object_id'] ) : 0;
            $term_taxonomy_id = isset( $row['term_taxonomy_id'] ) ? absint( $row['term_taxonomy_id'] ) : 0;

            if ( $object_id <= 0 || $term_taxonomy_id <= 0 ) {
                continue;
            }

            $conditions[] = '( object_id = %d AND term_taxonomy_id = %d )';
            $prepare_args[] = $object_id;
            $prepare_args[] = $term_taxonomy_id;
        }

        if ( empty( $conditions ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $delete_sql = call_user_func_array(
            array( $wpdb, 'prepare' ),
            array_merge(
                array( "DELETE FROM {$wpdb->term_relationships} WHERE " . implode( ' OR ', $conditions ) ),
                $prepare_args
            )
        );
        $deleted = (int) $wpdb->query( $delete_sql );

        return array(
            'deleted'  => max( 0, $deleted ),
            'has_more' => count( $rows ) >= $limit,
        );
    }

    /**
     * 批量清理过期 transient（仅一批）。
     *
     * @param int $limit 批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_expired_transients_batch( $limit = 200 ) {
        global $wpdb;

        $limit = max( 20, min( 500, absint( $limit ) ) );
        $now = time();
        $timeout_deleted = 0;
        $value_deleted = 0;

        $expired_timeout_names = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name
                 FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                 AND option_value < %d
                 ORDER BY option_id ASC
                 LIMIT %d",
                '%_transient_timeout_%',
                $now,
                $limit
            )
        );

        if ( empty( $expired_timeout_names ) || ! is_array( $expired_timeout_names ) ) {
            return array(
                'deleted'         => 0,
                'timeout_deleted' => 0,
                'value_deleted'   => 0,
                'has_more'        => false,
            );
        }

        $delete_options_by_names = static function( $option_names ) use ( $wpdb ) {
            $option_names = array_values( array_unique( array_filter( array_map( 'strval', (array) $option_names ) ) ) );
            if ( empty( $option_names ) ) {
                return 0;
            }

            $placeholders = implode( ',', array_fill( 0, count( $option_names ), '%s' ) );
            $sql = $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name IN ({$placeholders})",
                $option_names
            );

            return (int) $wpdb->query( $sql );
        };

        $timeout_deleted = $delete_options_by_names( $expired_timeout_names );

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
            $value_deleted = $delete_options_by_names( $expired_value_names );
        }

        return array(
            'deleted'         => max( 0, $timeout_deleted + $value_deleted ),
            'timeout_deleted' => max( 0, $timeout_deleted ),
            'value_deleted'   => max( 0, $value_deleted ),
            'has_more'        => count( $expired_timeout_names ) >= $limit,
        );
    }

    /**
     * 批量清理未使用标签（仅一批）。
     *
     * @param int $limit 批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_unused_tags_batch( $limit = 100 ) {
        global $wpdb;

        $limit = max( 10, min( 200, absint( $limit ) ) );
        $term_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT term_id
                 FROM {$wpdb->term_taxonomy}
                 WHERE taxonomy = 'post_tag' AND count = 0
                 ORDER BY term_id ASC
                 LIMIT %d",
                $limit
            )
        );

        if ( empty( $term_ids ) || ! is_array( $term_ids ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $deleted = 0;
        foreach ( $term_ids as $term_id ) {
            $result = wp_delete_term( absint( $term_id ), 'post_tag' );
            if ( ! is_wp_error( $result ) && ! empty( $result ) ) {
                $deleted++;
            }
        }

        return array(
            'deleted'  => $deleted,
            'has_more' => count( $term_ids ) >= $limit,
        );
    }
    
    /**
     * AJAX 获取数据库统计
     */
    /**
     * @return array<int,string>
     */
    private function get_post_view_meta_keys() {
        return array(
            'ds_post_views_count',
            'post_views_count',
            'post_views',
        );
    }

    /**
     * 批量清空文章浏览量元数据（仅一批）。
     *
     * @param int $limit 批次大小。
     * @return array<string,mixed>
     */
    private function cleanup_post_views_batch( $limit = 200 ) {
        global $wpdb;

        $limit = max( 20, min( 500, absint( $limit ) ) );
        $meta_keys = $this->get_post_view_meta_keys();
        $placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );
        $query_args = array_merge(
            array(
                "SELECT meta_id, post_id
                 FROM {$wpdb->postmeta}
                 WHERE meta_key IN ({$placeholders})
                 ORDER BY meta_id ASC
                 LIMIT %d",
            ),
            $meta_keys,
            array( $limit )
        );
        $rows = $wpdb->get_results(
            call_user_func_array( array( $wpdb, 'prepare' ), $query_args ),
            ARRAY_A
        );

        if ( empty( $rows ) || ! is_array( $rows ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $meta_ids = array_values(
            array_filter(
                array_map( 'absint', wp_list_pluck( $rows, 'meta_id' ) )
            )
        );
        $post_ids = array_values(
            array_filter(
                array_map( 'absint', wp_list_pluck( $rows, 'post_id' ) )
            )
        );

        if ( empty( $meta_ids ) ) {
            return array(
                'deleted'  => 0,
                'has_more' => false,
            );
        }

        $delete_placeholders = implode( ',', array_fill( 0, count( $meta_ids ), '%d' ) );
        $delete_sql = call_user_func_array(
            array( $wpdb, 'prepare' ),
            array_merge(
                array( "DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ({$delete_placeholders})" ),
                $meta_ids
            )
        );
        $deleted = (int) $wpdb->query( $delete_sql );

        $this->invalidate_post_view_related_caches( $post_ids );

        return array(
            'deleted'  => max( 0, $deleted ),
            'has_more' => count( $rows ) >= $limit,
        );
    }

    /**
     * 清空浏览量后刷新相关缓存。
     *
     * @param array<int,mixed> $post_ids 受影响的文章 ID。
     * @return void
     */
    private function invalidate_post_view_related_caches( $post_ids ) {
        global $wpdb;

        $post_ids = array_values(
            array_unique(
                array_filter(
                    array_map( 'absint', (array) $post_ids )
                )
            )
        );

        if ( ! empty( $post_ids ) ) {
            $post_placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
            $author_sql = call_user_func_array(
                array( $wpdb, 'prepare' ),
                array_merge(
                    array( "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE ID IN ({$post_placeholders})" ),
                    $post_ids
                )
            );
            $author_ids = $wpdb->get_col( $author_sql );

            foreach ( (array) $author_ids as $author_id ) {
                $author_id = absint( $author_id );
                if ( $author_id <= 0 ) {
                    continue;
                }

                $cache_key = 'ds_author_total_views_v2_' . $author_id;
                if ( function_exists( 'developer_starter_cache_delete' ) ) {
                    \developer_starter_cache_delete(
                        $cache_key,
                        array(
                            'group'    => 'developer_starter_user',
                            'audience' => 'public',
                            'surface'  => 'any',
                        )
                    );
                } else {
                    delete_transient( $cache_key );
                }
            }

            foreach ( $post_ids as $post_id ) {
                wp_clear_scheduled_hook( 'developer_starter_flush_post_view_buffer', array( $post_id ) );
                wp_cache_delete( 'pv_counter_' . $post_id, 'developer_starter_post_views' );
                wp_cache_delete( 'pv_dirty_' . $post_id, 'developer_starter_post_views' );
                wp_cache_delete( 'pv_lock_' . $post_id, 'developer_starter_post_views' );
            }
        }

        if ( function_exists( 'developer_starter_bump_cache_version' ) ) {
            \developer_starter_bump_cache_version( 'content' );
        }
    }

    public function ajax_db_stats() {
        check_ajax_referer( 'db_stats_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        
        global $wpdb;
        
        $stats = array();
        
        // 文章修订版本
        $stats['revisions'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );
        
        // 自动草稿
        $stats['drafts'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );
        
        // 回收站文章
        $stats['trash'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" );
        
        // 垃圾评论
        $stats['spam'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
        
        // 孤立的文章元数据
        $stats['orphan_postmeta'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" );
        
        // 孤立的评论元数据
        $stats['orphan_commentmeta'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->commentmeta} cm LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id WHERE c.comment_ID IS NULL" );
        
        // 孤立的关系数据
        $stats['orphan_relationships'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id WHERE p.ID IS NULL" );
        
        // Pingback/Trackback
        $stats['pingbacks'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type IN ('pingback', 'trackback')" );
        
        // 未使用的标签
        $stats['unused_tags'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag' AND count = 0" );
        
        // 过期的 Transients
        $stats['transients'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_%' AND option_value < " . time() );

        $view_meta_keys = $this->get_post_view_meta_keys();
        $view_placeholders = implode( ',', array_fill( 0, count( $view_meta_keys ), '%s' ) );
        $stats['post_views'] = (int) $wpdb->get_var(
            call_user_func_array(
                array( $wpdb, 'prepare' ),
                array_merge(
                    array( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key IN ({$view_placeholders})" ),
                    $view_meta_keys
                )
            )
        );

        // 已导入的数据包页面（非回收站）
        $stats['package_pages'] = $this->count_site_package_pages( false );

        // 已导入的数据包页面（回收站）
        $stats['package_trash_pages'] = $this->count_site_package_pages( true );
        
        wp_send_json_success( $stats );
    }

    /**
     * 统计数据包页面数量。
     *
     * 仅统计带有 `_qiling_site_package_id` 标记的页面。
     *
     * @param bool $trash_only 是否仅统计回收站页面。
     * @return int
     */
    private function count_site_package_pages( $trash_only = false ) {
        global $wpdb;

        $status_sql = $trash_only ? "= 'trash'" : "<> 'trash'";
        $count = $wpdb->get_var(
            "SELECT COUNT(DISTINCT p.ID)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm
                 ON pm.post_id = p.ID
                AND pm.meta_key = '_qiling_site_package_id'
                AND pm.meta_value <> ''
             WHERE p.post_type = 'page'
               AND p.post_status {$status_sql}"
        );

        return (int) $count;
    }

    /**
     * 获取带有数据包标记的页面 ID 列表。
     *
     * @param string $post_status 页面状态。
     * @param int    $limit       每批拉取数量。
     * @return array<int,int>
     */
    private function get_site_package_page_ids( $post_status = 'trash', $limit = 100 ) {
        $args = array(
            'post_type'              => 'page',
            'post_status'            => $post_status,
            'posts_per_page'         => max( 1, absint( $limit ) ),
            'fields'                 => 'ids',
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query'             => array(
                array(
                    'key'     => '_qiling_site_package_id',
                    'compare' => 'EXISTS',
                ),
            ),
        );

        $query = new \WP_Query( $args );
        if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
            return array();
        }

        return array_values( array_filter( array_map( 'absint', $query->posts ) ) );
    }

    /**
     * 彻底删除回收站中的数据包页面（全量）。
     *
     * @return int
     */
    private function cleanup_site_package_trash_pages() {
        $deleted = 0;

        while ( true ) {
            $batch = $this->cleanup_site_package_trash_pages_batch( 100 );
            $deleted += (int) $batch['deleted'];

            if ( empty( $batch['has_more'] ) ) {
                break;
            }
            if ( ! empty( $batch['fetched'] ) && empty( $batch['deleted'] ) ) {
                // 防御：避免极端情况下进入死循环。
                break;
            }
        }

        return $deleted;
    }

    /**
     * 彻底删除回收站中的数据包页面（单批）。
     *
     * 这里不走直接 SQL，避免遗漏页面 meta 清理和相关删除钩子。
     *
     * @param int $limit 每批处理数量。
     * @return array<string,mixed>
     */
    private function cleanup_site_package_trash_pages_batch( $limit = 100 ) {
        $limit = max( 10, min( 200, absint( $limit ) ) );
        $deleted = 0;
        $page_ids = $this->get_site_package_page_ids( 'trash', $limit );

        if ( empty( $page_ids ) || ! is_array( $page_ids ) ) {
            return array(
                'deleted'  => 0,
                'fetched'  => 0,
                'has_more' => false,
            );
        }

        foreach ( $page_ids as $page_id ) {
            $post = get_post( $page_id );
            if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || 'trash' !== $post->post_status ) {
                continue;
            }

            if ( '' === (string) get_post_meta( $page_id, '_qiling_site_package_id', true ) ) {
                continue;
            }

            $result = wp_delete_post( $page_id, true );
            if ( $result ) {
                $deleted++;
            }
        }

        return array(
            'deleted'  => $deleted,
            'fetched'  => count( $page_ids ),
            'has_more' => count( $page_ids ) >= $limit,
        );
    }

    /**
     * 获取海报缓存目录
     */
    private function get_poster_cache_dir() {
        $uploads = wp_upload_dir();
        $base_dir = isset( $uploads['basedir'] ) ? (string) $uploads['basedir'] : '';
        return trailingslashit( $base_dir ) . 'qiling-posters';
    }

    /**
     * 统计海报缓存数量和占用空间
     */
    private function collect_poster_cache_stats() {
        $dir = $this->get_poster_cache_dir();
        $count = 0;
        $bytes = 0;

        if ( is_dir( $dir ) ) {
            try {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS )
                );
                foreach ( $iterator as $item ) {
                    if ( $item->isFile() ) {
                        $count++;
                        $bytes += (int) $item->getSize();
                    }
                }
            } catch ( \Exception $e ) {
                // 目录读取异常时返回已统计结果（默认为 0）
            }
        }

        return array(
            'count'      => $count,
            'bytes'      => $bytes,
            'size_human' => function_exists( 'size_format' ) ? size_format( $bytes, 2 ) : ( $bytes . ' B' ),
            'dir'        => $dir,
        );
    }

    /**
     * 清理海报缓存文件
     */
    private function cleanup_poster_cache_files() {
        $dir = $this->get_poster_cache_dir();
        $deleted_files = 0;
        $freed_bytes = 0;
        $failed_files = 0;

        if ( ! is_dir( $dir ) ) {
            return array(
                'deleted_files' => 0,
                'freed_bytes'   => 0,
                'failed_files'  => 0,
            );
        }

        $allowed_roots = array_filter(
            array(
                function_exists( 'developer_starter_filesystem_upload_basedir' ) ? developer_starter_filesystem_upload_basedir() : '',
                $dir,
            )
        );

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ( $iterator as $item ) {
                $path = $item->getPathname();
                if ( $item->isFile() ) {
                    $size = (int) $item->getSize();
                    if ( developer_starter_filesystem_delete_file(
                        $path,
                        array(
                            'operation'     => 'delete_poster_cache_file',
                            'allowed_roots' => $allowed_roots,
                            'context'       => array( 'component' => 'poster_cache' ),
                        )
                    ) ) {
                        $deleted_files++;
                        $freed_bytes += $size;
                    } else {
                        $failed_files++;
                    }
                } elseif ( $item->isDir() ) {
                    developer_starter_filesystem_delete_empty_dir(
                        $path,
                        array(
                            'operation'     => 'delete_poster_cache_dir',
                            'allowed_roots' => $allowed_roots,
                            'context'       => array( 'component' => 'poster_cache' ),
                        )
                    );
                }
            }
        } catch ( \Exception $e ) {
            // 出现异常时返回当前已处理结果
        }

        return array(
            'deleted_files' => $deleted_files,
            'freed_bytes'   => $freed_bytes,
            'failed_files'  => $failed_files,
        );
    }

    /**
     * AJAX 海报缓存统计
     */
    public function ajax_poster_cache_stats() {
        check_ajax_referer( 'poster_cache_stats_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        wp_send_json_success( $this->collect_poster_cache_stats() );
    }

    /**
     * AJAX 清理文章海报缓存。
     */
    public function ajax_clear_poster_cache() {
        check_ajax_referer( 'clear_poster_cache_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $result = $this->cleanup_poster_cache_files();
        $stats  = $this->collect_poster_cache_stats();

        $message = sprintf(
            /* translators: 1: deleted file count, 2: freed bytes. */
            __( '已清理 %1$s 张海报缓存，释放 %2$s。', 'developer-starter' ),
            number_format_i18n( (int) $result['deleted_files'] ),
            function_exists( 'size_format' ) ? size_format( (int) $result['freed_bytes'], 2 ) : ( (int) $result['freed_bytes'] . ' B' )
        );

        if ( ! empty( $result['failed_files'] ) ) {
            $message .= ' ' . sprintf(
                /* translators: %s: failed file count. */
                __( '%s 个文件清理失败，请检查 uploads/qiling-posters 目录权限或远程存储配置。', 'developer-starter' ),
                number_format_i18n( (int) $result['failed_files'] )
            );
        }

        wp_send_json_success(
            array(
                'message'       => $message,
                'deleted_files' => (int) $result['deleted_files'],
                'freed_bytes'   => (int) $result['freed_bytes'],
                'failed_files'  => (int) $result['failed_files'],
                'stats'         => $stats,
            )
        );
    }

    /**
     * AJAX GitHub 项目动态缓存统计。
     */
    public function ajax_github_activity_cache_stats() {
        check_ajax_referer( 'github_activity_cache_stats_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $service = new \Developer_Starter\Core\GitHub_Repository_Activity_Service();
        wp_send_json_success( $service->collect_cache_stats() );
    }

    /**
     * AJAX 清理 GitHub 项目动态缓存。
     */
    public function ajax_clear_github_activity_cache() {
        check_ajax_referer( 'clear_github_activity_cache_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $service = new \Developer_Starter\Core\GitHub_Repository_Activity_Service();
        $result  = $service->clear_cache_files();
        $stats   = $service->collect_cache_stats();

        $message = sprintf(
            /* translators: 1: deleted file count, 2: freed bytes. */
            __( '已清理 %1$s 个 GitHub 项目缓存文件，释放 %2$s。', 'developer-starter' ),
            number_format_i18n( (int) $result['deleted_files'] ),
            function_exists( 'size_format' ) ? size_format( (int) $result['freed_bytes'], 2 ) : ( (int) $result['freed_bytes'] . ' B' )
        );

        if ( ! empty( $result['failed_files'] ) ) {
            $message .= ' ' . sprintf(
                /* translators: %s: failed file count. */
                __( '%s 个文件清理失败，请检查 uploads/qiling/github-activity 目录权限。', 'developer-starter' ),
                number_format_i18n( (int) $result['failed_files'] )
            );
        }

        wp_send_json_success(
            array(
                'message'       => $message,
                'deleted_files' => (int) $result['deleted_files'],
                'freed_bytes'   => (int) $result['freed_bytes'],
                'failed_files'  => (int) $result['failed_files'],
                'stats'         => $stats,
            )
        );
    }

    /**
     * AJAX 生成压缩 CSS 文件
     */
    public function ajax_generate_css() {
        check_ajax_referer( 'generate_css_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $files = array(
            'main' => array(
                'src' => DEVELOPER_STARTER_DIR . '/assets/css/main.css',
                'min' => DEVELOPER_STARTER_DIR . '/assets/css/main.min.css',
            ),
            'modules' => array(
                'src' => DEVELOPER_STARTER_DIR . '/assets/css/modules.css',
                'min' => DEVELOPER_STARTER_DIR . '/assets/css/modules.min.css',
            ),
            'modules-hero' => array(
                'src' => DEVELOPER_STARTER_DIR . '/assets/css/modules-hero.css',
                'min' => DEVELOPER_STARTER_DIR . '/assets/css/modules-hero.min.css',
            ),
        );

        $results = array();
        $total_saved = 0;

        foreach ( $files as $key => $file ) {
            if ( ! file_exists( $file['src'] ) ) {
                $results[] = sprintf(
                    /* translators: %s: CSS file key. */
                    __( '源文件 %s.css 不存在', 'developer-starter' ),
                    $key
                );
                continue;
            }

            $content = file_get_contents( $file['src'] );
            if ( false === $content ) {
                $results[] = sprintf(
                    /* translators: %s: CSS file key. */
                    __( '%s.css 读取失败', 'developer-starter' ),
                    $key
                );
                continue;
            }
            
            // 重新实现安全压缩 (Safe Minify)
            // 简单的正则压缩逻辑: 仅去除注释和换行，保留单个空格，确保 calc() 安全
            $content_safe = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content );
            if ( ! is_string( $content_safe ) ) {
                $results[] = sprintf(
                    /* translators: %s: CSS file key. */
                    __( '%s.css 压缩失败', 'developer-starter' ),
                    $key
                );
                continue;
            }
            $content_safe = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $content_safe );
            // 将多个空格合并为一个
            $content_safe = preg_replace( '/\s+/', ' ', $content_safe );
            if ( ! is_string( $content_safe ) ) {
                $results[] = sprintf(
                    /* translators: %s: CSS file key. */
                    __( '%s.css 压缩失败', 'developer-starter' ),
                    $key
                );
                continue;
            }
            
            if ( developer_starter_filesystem_write_theme_generated_asset( $file['min'], $content_safe ) ) {
                $src_size = filesize( $file['src'] );
                clearstatcache( true, $file['min'] );
                $min_size = filesize( $file['min'] );
                $saved = $src_size > 0 ? round( ( ( $src_size - $min_size ) / $src_size ) * 100, 1 ) : 0;
                $results[] = sprintf(
                    /* translators: 1: CSS file key, 2: source size in bytes, 3: minified size in bytes, 4: percentage saved. */
                    __( '%1$s.css: %2$sB -> %3$sB (节省 %4$s%%)', 'developer-starter' ),
                    $key,
                    $src_size,
                    $min_size,
                    $saved
                );
            } else {
                $results[] = sprintf(
                    /* translators: %s: CSS file key. */
                    __( '%s.min.css 写入失败', 'developer-starter' ),
                    $key
                );
            }
        }
        
        // 自动更新资源版本号，以刷新缓存
        $options = get_option( $this->option_name, array() );
        $options['assets_version'] = date( 'ymd.His' );
        update_option( $this->option_name, $options );

        wp_send_json_success( array( 
            'message' => __( '生成成功：', 'developer-starter' ) . implode( '，', $results ),
            'version' => $options['assets_version']
        ) );
    }

    public function ajax_clear_ip_cache() {
        check_ajax_referer( 'clear_ip_cache_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        if ( ! function_exists( 'developer_starter_ip_cache_clear' ) ) {
            wp_send_json_success( array( 'message' => __( '缓存清理器未加载', 'developer-starter' ), 'deleted' => 0 ) );
        }

        $deleted = developer_starter_ip_cache_clear();

        wp_send_json_success( array( 'message' => sprintf( __( '已清理 %d 个缓存文件', 'developer-starter' ), $deleted ), 'deleted' => $deleted ) );
    }

    public function ajax_reset_ip_usermeta() {
        check_ajax_referer( 'reset_ip_usermeta_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        global $wpdb;
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ('ds_ip_location', 'ds_ip_location_source', 'ds_ip_location_updated', 'ds_ip_location_ip')"
        );

        if ( $deleted === false ) {
            wp_send_json_error( array( 'message' => __( '数据库操作失败', 'developer-starter' ) ) );
        }

        wp_send_json_success( array( 'message' => sprintf( __( '成功清除了 %d 条用户 IP 归属地遗留数据', 'developer-starter' ), $deleted ), 'deleted' => $deleted ) );
    }

    public function ajax_toggle_favorite_setting() {
        check_ajax_referer( 'favorite_setting_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $setting_id = isset( $_POST['setting'] ) ? sanitize_key( wp_unslash( $_POST['setting'] ) ) : '';
        $enabled = isset( $_POST['enabled'] ) ? (bool) absint( wp_unslash( $_POST['enabled'] ) ) : false;

        if ( ! $setting_id ) {
            wp_send_json_error( array( 'message' => __( '无效设置项', 'developer-starter' ) ) );
        }

        $favorites = $this->get_user_favorites();

        if ( $enabled ) {
            if ( ! in_array( $setting_id, $favorites, true ) ) {
                $favorites[] = $setting_id;
            }
        } else {
            $favorites = array_values( array_diff( $favorites, array( $setting_id ) ) );
        }

        update_user_meta( get_current_user_id(), 'developer_starter_favorite_settings', $favorites );

        wp_send_json_success( array( 'favorites' => $favorites ) );
    }

    public function ajax_check_gzip_status() {
        check_ajax_referer( 'check_gzip_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $targets = array(
            __( '首页', 'developer-starter' ) => home_url( '/' ),
            __( '主题主样式', 'developer-starter' ) => DEVELOPER_STARTER_ASSETS . '/css/main.css',
        );

        $items = array();
        $enabled_count = 0;
        $error_count = 0;

        foreach ( $targets as $label => $url ) {
            $response = wp_remote_get(
                $url,
                array(
                    'timeout'     => 10,
                    'redirection' => 3,
                    'headers'     => array(
                        'Accept-Encoding' => 'gzip, deflate, br',
                    ),
                    // 保留原始响应头，便于检测 Content-Encoding。
                    'decompress'  => false,
                )
            );

            if ( is_wp_error( $response ) ) {
                $error_count++;
                $items[] = array(
                    'label'    => (string) $label,
                    'url'      => esc_url_raw( $url ),
                    'code'     => 0,
                    'encoding' => '',
                    'enabled'  => false,
                    'error'    => $response->get_error_message(),
                );
                continue;
            }

            $code = (int) wp_remote_retrieve_response_code( $response );
            $encoding = (string) wp_remote_retrieve_header( $response, 'content-encoding' );
            if ( $encoding === '' ) {
                // 某些环境会将编码头保存在扩展字段中。
                $encoding = (string) wp_remote_retrieve_header( $response, 'x-encoded-content-encoding' );
            }

            $encoding_normalized = strtolower( trim( $encoding ) );
            $enabled = ( $encoding_normalized !== '' ) && (
                strpos( $encoding_normalized, 'gzip' ) !== false ||
                strpos( $encoding_normalized, 'br' ) !== false
            );

            if ( $enabled ) {
                $enabled_count++;
            }

            $items[] = array(
                'label'    => (string) $label,
                'url'      => esc_url_raw( $url ),
                'code'     => $code,
                'encoding' => $encoding_normalized,
                'enabled'  => $enabled,
                'error'    => '',
            );
        }

        $overall = 'disabled';
        if ( $enabled_count > 0 && $enabled_count === count( $items ) ) {
            $overall = 'enabled';
        } elseif ( $enabled_count > 0 ) {
            $overall = 'partial';
        }

        if ( $error_count > 0 && $enabled_count === 0 ) {
            $summary = __( '检测失败：无法完成回环请求，请检查服务器是否允许本机请求。', 'developer-starter' );
        } elseif ( $overall === 'enabled' ) {
            $summary = __( '已开启：检测到 gzip/br 压缩响应。', 'developer-starter' );
        } elseif ( $overall === 'partial' ) {
            $summary = __( '部分开启：有的资源已压缩，有的未压缩。', 'developer-starter' );
        } else {
            $summary = __( '未检测到 gzip/br：请在 Nginx/Apache/CDN 开启压缩。', 'developer-starter' );
        }

        wp_send_json_success(
            array(
                'overall' => $overall,
                'summary' => $summary,
                'items'   => $items,
            )
        );
    }

    public function ajax_check_split_css_integrity() {
        check_ajax_referer( 'check_split_css_integrity_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $source_file   = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/modules.source.css';
        $split_dir     = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/modules-split/';
        $manifest_file = $split_dir . '_manifest.txt';

        $items    = array();
        $errors   = 0;
        $warnings = 0;

        $add_item = static function ( $level, $text ) use ( &$items, &$errors, &$warnings ) {
            if ( $level === 'error' ) {
                $errors++;
            } elseif ( $level === 'warn' ) {
                $warnings++;
            }
            $items[] = array(
                'level' => $level,
                'text'  => (string) $text,
            );
        };

        if ( ! file_exists( $source_file ) ) {
            $add_item( 'error', __( '缺少源文件 assets/css/modules.source.css。', 'developer-starter' ) );
        } elseif ( ! is_readable( $source_file ) ) {
            $add_item( 'error', __( '源文件 modules.source.css 不可读。', 'developer-starter' ) );
        } else {
            $add_item(
                'ok',
                sprintf(
                    __( '源文件存在：%1$s（%2$s，%3$d 字节）', 'developer-starter' ),
                    basename( $source_file ),
                    date_i18n( 'Y-m-d H:i:s', (int) @filemtime( $source_file ) ),
                    (int) @filesize( $source_file )
                )
            );
        }

        if ( ! is_dir( $split_dir ) ) {
            $add_item( 'error', __( '缺少拆分目录 assets/css/modules-split/。', 'developer-starter' ) );
        } else {
            $add_item( 'ok', __( '拆分目录存在。', 'developer-starter' ) );
        }

        if ( ! file_exists( $manifest_file ) ) {
            $add_item( 'error', __( '缺少拆分清单 _manifest.txt。', 'developer-starter' ) );
        } elseif ( ! is_readable( $manifest_file ) ) {
            $add_item( 'error', __( '拆分清单 _manifest.txt 不可读。', 'developer-starter' ) );
        } else {
            $add_item(
                'ok',
                sprintf(
                    __( '已检测到拆分清单 _manifest.txt（%1$s，%2$d 字节）。', 'developer-starter' ),
                    date_i18n( 'Y-m-d H:i:s', (int) @filemtime( $manifest_file ) ),
                    (int) @filesize( $manifest_file )
                )
            );
        }

        $entries = array();
        $manifest_source_bytes = null;

        if ( $errors === 0 ) {
            $lines = file( $manifest_file, FILE_IGNORE_NEW_LINES );
            if ( ! is_array( $lines ) || empty( $lines ) ) {
                $add_item( 'error', __( '拆分清单为空或读取失败。', 'developer-starter' ) );
            } else {
                foreach ( $lines as $line ) {
                    $line = trim( (string) $line );
                    if ( $line === '' || strpos( $line, '#' ) === 0 ) {
                        continue;
                    }

                    if ( strpos( $line, "\t" ) === false && strpos( $line, '=' ) !== false ) {
                        list( $meta_key, $meta_val ) = array_map( 'trim', explode( '=', $line, 2 ) );
                        if ( $meta_key === 'source_bytes' ) {
                            $manifest_source_bytes = (int) $meta_val;
                        }
                        continue;
                    }

                    $parts = explode( "\t", $line );
                    if ( count( $parts ) < 2 ) {
                        continue;
                    }

                    $type = sanitize_key( (string) $parts[0] );
                    if ( $type === '' ) {
                        continue;
                    }

                    $path_or_flag = trim( (string) $parts[1] );
                    $bytes = null;
                    $chunks = null;

                    for ( $i = 2; $i < count( $parts ); $i++ ) {
                        $part = (string) $parts[ $i ];
                        if ( strpos( $part, 'bytes=' ) === 0 ) {
                            $bytes = (int) substr( $part, 6 );
                        } elseif ( strpos( $part, 'chunks=' ) === 0 ) {
                            $chunks = (int) substr( $part, 7 );
                        }
                    }

                    $entries[ $type ] = array(
                        'type'       => $type,
                        'path'       => $path_or_flag,
                        'is_skipped' => ( $path_or_flag === '(skipped-empty)' ),
                        'bytes'      => $bytes,
                        'chunks'     => $chunks,
                    );
                }
            }
        }

        $optional_no_css_types = apply_filters(
            'developer_starter_modules_split_optional_types',
            array(
                'chart',
                'news',
                'work_detail',
                'work_library',
                'banner',
                'dynamic_banner',
                'hero_search',
                'interact_hero',
                'app_hero',
                'fullscreen_video',
                'resume_hero',
                'resource_hero_pro',
                'brand_banner_pro',
                'qiling_video_portal_hero',
            )
        );
        $optional_no_css_types = is_array( $optional_no_css_types ) ? array_values( array_unique( array_map( 'sanitize_key', $optional_no_css_types ) ) ) : array();

        $source_size = file_exists( $source_file ) ? (int) @filesize( $source_file ) : 0;
        $manifest_stale = false;

        if ( $errors === 0 ) {
            if ( empty( $entries ) ) {
                $add_item( 'error', __( '拆分清单中没有可用模块记录。', 'developer-starter' ) );
            }

            if ( ! isset( $entries['_shared'] ) ) {
                $add_item( 'error', __( '拆分清单缺少 _shared 记录。', 'developer-starter' ) );
            }

            if ( $manifest_source_bytes !== null ) {
                if ( $manifest_source_bytes !== $source_size ) {
                    $manifest_stale = true;
                    $add_item(
                        'warn',
                        sprintf(
                            __( 'modules.source.css 已变化：manifest source_bytes=%1$d，当前=%2$d。建议重新执行拆分脚本。', 'developer-starter' ),
                            $manifest_source_bytes,
                            $source_size
                        )
                    );
                } else {
                    $add_item( 'ok', __( 'modules.source.css 与 manifest source_bytes 一致。', 'developer-starter' ) );
                }
            }

            $expected_files = array();

            foreach ( $entries as $entry ) {
                $type       = $entry['type'];
                $is_skipped = ! empty( $entry['is_skipped'] );
                $bytes      = $entry['bytes'];
                $path_token = (string) $entry['path'];
                $expected_file = $split_dir . $type . '.css';

                if ( $is_skipped ) {
                    if ( ! in_array( $type, $optional_no_css_types, true ) ) {
                        $add_item(
                            'error',
                            sprintf(
                                __( '%s 标记为 skipped-empty，但该模块不在可选空文件白名单。', 'developer-starter' ),
                                $type
                            )
                        );
                    } else {
                        $add_item( 'ok', sprintf( __( '%s 已按预期标记为 skipped-empty。', 'developer-starter' ), $type ) );
                    }

                    if ( file_exists( $expected_file ) ) {
                        $size = (int) @filesize( $expected_file );
                        if ( $size > 0 ) {
                            $add_item( 'warn', sprintf( __( '%s 标记为 skipped-empty，但文件仍存在且非空。', 'developer-starter' ), basename( $expected_file ) ) );
                        } else {
                            $add_item( 'warn', sprintf( __( '%s 标记为 skipped-empty，但仍保留了空文件。建议清理。', 'developer-starter' ), basename( $expected_file ) ) );
                        }
                    }
                    continue;
                }

                $real_file = '';
                if ( $path_token !== '' && file_exists( $path_token ) ) {
                    $real_file = $path_token;
                } elseif ( file_exists( $expected_file ) ) {
                    $real_file = $expected_file;
                } else {
                    $basename_file = basename( $path_token );
                    if ( $basename_file !== '' && file_exists( $split_dir . $basename_file ) ) {
                        $real_file = $split_dir . $basename_file;
                    }
                }

                if ( $real_file === '' ) {
                    $add_item( 'error', sprintf( __( '缺少模块样式文件：%s', 'developer-starter' ), $type . '.css' ) );
                    continue;
                }

                $expected_files[] = basename( $real_file );
                $actual_size = (int) @filesize( $real_file );
                if ( $actual_size <= 0 ) {
                    $add_item( 'warn', sprintf( __( '%s 文件大小为 0。', 'developer-starter' ), basename( $real_file ) ) );
                }

                if ( $bytes !== null && $bytes >= 0 && $bytes !== $actual_size ) {
                    if ( $manifest_stale ) {
                        $add_item(
                            'warn',
                            sprintf(
                                __( '%1$s 字节数差异（manifest 过期导致）：manifest=%2$d，当前=%3$d。', 'developer-starter' ),
                                basename( $real_file ),
                                $bytes,
                                $actual_size
                            )
                        );
                    } else {
                        $add_item(
                            'error',
                            sprintf(
                                __( '%1$s 字节数不一致：manifest=%2$d，当前=%3$d。', 'developer-starter' ),
                                basename( $real_file ),
                                $bytes,
                                $actual_size
                            )
                        );
                    }
                }
            }

            $disk_css_files = glob( $split_dir . '*.css' );
            if ( is_array( $disk_css_files ) ) {
                $manifest_files = array_unique(
                    array_merge(
                        array( '_shared.css' ),
                        $expected_files
                    )
                );
                $manifest_files = array_values( array_filter( $manifest_files ) );

                foreach ( $disk_css_files as $css_file ) {
                    $name = basename( $css_file );
                    if ( ! in_array( $name, $manifest_files, true ) ) {
                        $add_item( 'warn', sprintf( __( '发现未在 manifest 声明的样式文件：%s', 'developer-starter' ), $name ) );
                    }
                }
            }
        }

        if ( $errors > 0 ) {
            $overall = 'error';
            $summary = sprintf(
                __( '检查失败：%1$d 个错误，%2$d 个警告。请先修复错误项。', 'developer-starter' ),
                $errors,
                $warnings
            );
        } elseif ( $warnings > 0 ) {
            $overall = 'warning';
            $summary = sprintf(
                __( '检查通过（含警告）：%d 个警告，建议处理。', 'developer-starter' ),
                $warnings
            );
        } else {
            $overall = 'healthy';
            $summary = __( '检查通过：拆分 CSS 清单与实际文件一致。', 'developer-starter' );
        }

        wp_send_json_success(
            array(
                'overall' => $overall,
                'summary' => $summary,
                'items'   => $items,
            )
        );
    }

    /**
     * AJAX 检测启灵生态插件状态。
     *
     * 仅在用户点击按钮时执行，不做目录扫描、不读取插件头，也不安装/启用插件。
     *
     * @return void
     */
    public function ajax_detect_ecosystem_plugins() {
        check_ajax_referer( 'developer_starter_ecosystem_plugins', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ), 403 );
        }

        wp_send_json_success( $this->detect_ecosystem_plugin_statuses() );
    }
}
