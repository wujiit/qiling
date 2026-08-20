<?php
/**
 * Admin Settings Repair Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Repair_Trait {

    public function render_theme_options_repair_field( $options ) {
        $nonce = wp_create_nonce( 'ds_repair_theme_options_nonce' );
        $action_url = add_query_arg( 'action', 'ds_repair_theme_options', admin_url( 'admin-post.php' ) );
        $action_url = wp_nonce_url( $action_url, 'ds_repair_theme_options_nonce', 'ds_repair_theme_options_nonce' );
        $is_broken = function_exists( 'developer_starter_is_option_serialization_broken' )
            ? developer_starter_is_option_serialization_broken( $this->option_name )
            : false;
        $domain_scan_url = method_exists( $this, 'get_advanced_settings_url' )
            ? $this->get_advanced_settings_url( array( 'ds_run_domain_scan' => '1' ) )
            : add_query_arg(
                array(
                    'page'               => 'developer-starter-settings',
                    'tab'                => 'advanced',
                    'ds_run_domain_scan' => '1',
                ),
                admin_url( 'admin.php' )
            );

        $status_label = $is_broken
            ? __( '检测到序列化异常', 'developer-starter' )
            : __( '序列化正常（如仍有旧域名请看下方提示）', 'developer-starter' );
        $status_color = $is_broken ? '#ef4444' : '#10b981';
        $backups = function_exists( 'developer_starter_cleanup_theme_options_backups' )
            ? developer_starter_cleanup_theme_options_backups()
            : array();
        $backup_count = is_array( $backups ) ? count( $backups ) : 0;
        $retention_days = function_exists( 'developer_starter_get_theme_options_backup_retention_days' )
            ? developer_starter_get_theme_options_backup_retention_days()
            : 30;
        $max_backups = function_exists( 'developer_starter_get_theme_options_backup_max_count' )
            ? developer_starter_get_theme_options_backup_max_count()
            : 20;

        echo '<tr><th scope="row">' . esc_html__( '主题设置数据修复', 'developer-starter' ) . '</th><td>';
        echo '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">';
        echo '<p class="description" style="margin:0 0 12px;">' . esc_html__( '用于检测并修复主题设置（developer_starter_options）因 SQL 直接替换域名导致的序列化损坏。', 'developer-starter' ) . '</p>';
        echo '<div style="margin:0 0 12px;"><span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:#f9fafb;border:1px solid #e5e7eb;color:' . esc_attr( $status_color ) . ';font-weight:600;">' . esc_html( $status_label ) . '</span></div>';
        echo '<div class="description" style="margin:0 0 12px;">' . esc_html__( '这里仅检测主题设置的序列化损坏，不判断是否含旧域名。旧域名差异请使用上方“域名设置检查”；该检查现已改为手动触发，不会在后台常驻扫描。', 'developer-starter' ) . '</div>';
        echo '<div style="margin:0 0 12px;"><a class="button button-secondary" href="' . esc_url( $domain_scan_url ) . '">' . esc_html__( '手动执行域名检查', 'developer-starter' ) . '</a></div>';
        echo '<div class="description" style="margin:0 0 12px;">' . sprintf(
            /* translators: 1: retention days, 2: max backups */
            esc_html__( '修复前会自动备份主题设置，默认保留 %1$d 天，最多 %2$d 份（惰性清理，不依赖定时任务）。', 'developer-starter' ),
            absint( $retention_days ),
            absint( $max_backups )
        ) . '</div>';
        echo '<div style="margin:0 0 12px;font-weight:600;">' . sprintf( esc_html__( '当前备份数量：%d', 'developer-starter' ), absint( $backup_count ) ) . '</div>';
        if ( $backup_count > 0 ) {
            echo '<div style="display:flex;flex-direction:column;gap:8px;margin:0 0 12px;">';
            $preview_backups = array_slice( $backups, 0, 5 );
            foreach ( $preview_backups as $backup ) {
                if ( ! is_array( $backup ) ) {
                    continue;
                }
                $created_at = isset( $backup['created_at'] ) ? absint( $backup['created_at'] ) : 0;
                $context    = isset( $backup['context'] ) ? (string) $backup['context'] : '';
                $size       = isset( $backup['size'] ) ? absint( $backup['size'] ) : 0;
                $label_time = $created_at ? date_i18n( 'Y-m-d H:i:s', $created_at ) : esc_html__( '未知时间', 'developer-starter' );
                $label_ctx  = $context === 'manual' ? esc_html__( '手动修复', 'developer-starter' ) : esc_html__( '自动修复', 'developer-starter' );
                $label_size = $size > 0 ? sprintf( esc_html__( '%d KB', 'developer-starter' ), (int) ceil( $size / 1024 ) ) : esc_html__( '未知大小', 'developer-starter' );
                echo '<div style="padding:8px 10px;border:1px dashed #e5e7eb;border-radius:8px;background:#f9fafb;color:#374151;">';
                echo esc_html( $label_time ) . ' · ' . esc_html( $label_ctx ) . ' · ' . esc_html( $label_size );
                echo '</div>';
            }
            if ( $backup_count > 5 ) {
                echo '<div style="color:#6b7280;font-size:12px;">' . sprintf( esc_html__( '还有 %d 条备份未显示。', 'developer-starter' ), $backup_count - 5 ) . '</div>';
            }
            echo '</div>';
        }
        echo '<button type="button" class="button button-primary" id="ds-repair-theme-options-btn">' . esc_html__( '扫描并修复主题设置', 'developer-starter' ) . '</button>';
        echo '<a class="button button-secondary" style="margin-left:8px;" href="' . esc_url( $action_url ) . '" onclick="return confirm(\'' . esc_js( __( '确定要开始扫描并修复主题设置吗？建议先备份数据库。', 'developer-starter' ) ) . '\');">' . esc_html__( '备用入口（无 JS）', 'developer-starter' ) . '</a>';
        echo '<div class="description" style="margin-top:10px;">' . esc_html__( '请求地址：', 'developer-starter' ) . '<code style="margin-left:6px;word-break:break-all;">' . esc_html( $action_url ) . '</code></div>';
        echo '<script>
        (function(){
            var btn = document.getElementById("ds-repair-theme-options-btn");
            if(!btn) return;
            btn.addEventListener("click", function(){
                if(!window.confirm(' . wp_json_encode( __( '确定要开始扫描并修复主题设置吗？建议先备份数据库。', 'developer-starter' ) ) . ')) return;
                var form = document.createElement("form");
                form.method = "POST";
                form.action = ' . wp_json_encode( admin_url( 'admin-post.php' ) ) . ';

                var actionInput = document.createElement("input");
                actionInput.type = "hidden";
                actionInput.name = "action";
                actionInput.value = "ds_repair_theme_options";
                form.appendChild(actionInput);

                var nonceInput = document.createElement("input");
                nonceInput.type = "hidden";
                nonceInput.name = "ds_repair_theme_options_nonce";
                nonceInput.value = ' . wp_json_encode( $nonce ) . ';
                form.appendChild(nonceInput);

                document.body.appendChild(form);
                form.submit();
            });
        })();
        </script>';
        echo '<p class="description" style="margin-top:12px;color:#ef4444;">' . esc_html__( '提示：如果你已经在设置丢失后保存过，原始设置可能已被覆盖，这种情况只能从数据库备份恢复。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '</td></tr>';
    }

    public function render_modules_repair_field( $options ) {
        $nonce = wp_create_nonce( 'ds_repair_modules_meta_nonce' );
        $action_url = add_query_arg( 'action', 'ds_repair_modules_meta', admin_url( 'admin-post.php' ) );
        $action_url = wp_nonce_url( $action_url, 'ds_repair_modules_meta_nonce', 'ds_repair_modules_meta_nonce' );
        $repair_targets = $this->get_modules_repair_targets();
        $target_labels  = array();

        foreach ( $repair_targets as $target ) {
            if ( empty( $target['label'] ) ) {
                continue;
            }

            $target_labels[] = (string) $target['label'];
        }

        $targets_text = ! empty( $target_labels )
            ? implode( '、', array_unique( $target_labels ) )
            : __( '页面模块', 'developer-starter' );

        echo '<tr><th scope="row">' . esc_html__( '模块数据修复', 'developer-starter' ) . '</th><td>';
        echo '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">';
        echo '<p class="description" style="margin:0 0 12px;">' . sprintf(
            esc_html__( '用于修复 %s 因 SQL 直接替换域名导致的序列化损坏（常见表现：页面装修模块在前台/后台看起来被清空）。', 'developer-starter' ),
            esc_html( $targets_text )
        ) . '</p>';
        echo '<div class="description" style="margin:0 0 12px;">' . esc_html__( '当前会扫描主题页面模块 `_developer_starter_modules`、启灵积分商城页面装修 `_qls_shop_layout`，以及商城旧版全局布局 `qls_shop_home_layout`。', 'developer-starter' ) . '</div>';
        echo '<button type="button" class="button button-primary" id="ds-repair-modules-meta-btn">' . esc_html__( '扫描并修复页面模块', 'developer-starter' ) . '</button>';
        echo '<a class="button button-secondary" style="margin-left:8px;" href="' . esc_url( $action_url ) . '" onclick="return confirm(\'' . esc_js( __( '确定要开始扫描并修复页面模块数据吗？建议先备份数据库。', 'developer-starter' ) ) . '\');">' . esc_html__( '备用入口（无 JS）', 'developer-starter' ) . '</a>';
        echo '<div class="description" style="margin-top:10px;">' . esc_html__( '请求地址：', 'developer-starter' ) . '<code style="margin-left:6px;word-break:break-all;">' . esc_html( $action_url ) . '</code></div>';
        echo '<script>
        (function(){
            var btn = document.getElementById("ds-repair-modules-meta-btn");
            if(!btn) return;
            btn.addEventListener("click", function(){
                if(!window.confirm(' . wp_json_encode( __( '确定要开始扫描并修复页面模块数据吗？建议先备份数据库。', 'developer-starter' ) ) . ')) return;
                var form = document.createElement("form");
                form.method = "POST";
                form.action = ' . wp_json_encode( admin_url( 'admin-post.php' ) ) . ';

                var actionInput = document.createElement("input");
                actionInput.type = "hidden";
                actionInput.name = "action";
                actionInput.value = "ds_repair_modules_meta";
                form.appendChild(actionInput);

                var nonceInput = document.createElement("input");
                nonceInput.type = "hidden";
                nonceInput.name = "ds_repair_modules_meta_nonce";
                nonceInput.value = ' . wp_json_encode( $nonce ) . ';
                form.appendChild(nonceInput);

                document.body.appendChild(form);
                form.submit();
            });
        })();
        </script>';
        echo '<p class="description" style="margin-top:12px;color:#ef4444;">' . esc_html__( '提示：如果你已经在模块丢失后保存过页面（保存了空模块），原始模块可能已被覆盖，这种情况只能从数据库备份恢复。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '</td></tr>';
    }

    public function handle_repair_modules_meta() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '权限不足', 'developer-starter' ) );
        }

        check_admin_referer( 'ds_repair_modules_meta_nonce', 'ds_repair_modules_meta_nonce' );

        if (
            ! function_exists( 'developer_starter_fix_serialized_string_lengths' ) ||
            ! function_exists( 'developer_starter_try_unserialize_no_classes' ) ||
            ! function_exists( 'is_serialized' )
        ) {
            wp_die( __( '修复函数未就绪，请确认主题文件完整。', 'developer-starter' ) );
        }

        global $wpdb;

        $batch_size = 200;
        $scanned  = 0;
        $repaired = 0;
        $failed   = 0;
        $targets  = $this->get_modules_repair_targets();

        foreach ( $targets as $target ) {
            $target_type = isset( $target['type'] ) ? (string) $target['type'] : 'post_meta';

            if ( 'option' === $target_type ) {
                $option_name = isset( $target['option_name'] ) ? (string) $target['option_name'] : '';

                if ( '' === $option_name || ! function_exists( 'developer_starter_get_raw_option_value' ) ) {
                    continue;
                }

                $raw = developer_starter_get_raw_option_value( $option_name );
                if ( ! is_string( $raw ) || '' === $raw ) {
                    continue;
                }

                $scanned++;

                if ( ! is_serialized( $raw ) ) {
                    continue;
                }

                $unserialized = developer_starter_try_unserialize_no_classes( $raw );
                if ( is_array( $unserialized ) ) {
                    continue;
                }

                $fixed = developer_starter_fix_serialized_string_lengths( $raw );
                if ( ! is_string( $fixed ) || $fixed === $raw ) {
                    $failed++;
                    continue;
                }

                $unserialized = developer_starter_try_unserialize_no_classes( $fixed );
                if ( ! is_array( $unserialized ) ) {
                    $failed++;
                    continue;
                }

                if ( update_option( $option_name, $unserialized ) ) {
                    $repaired++;
                } else {
                    $failed++;
                }

                continue;
            }

            $meta_key     = isset( $target['meta_key'] ) ? (string) $target['meta_key'] : '';
            $post_type    = isset( $target['post_type'] ) ? (string) $target['post_type'] : 'page';
            $last_meta_id = 0;

            if ( '' === $meta_key ) {
                continue;
            }

            while ( true ) {
                $rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT m.meta_id, m.post_id, m.meta_value
                         FROM {$wpdb->postmeta} AS m
                         INNER JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
                         WHERE m.meta_key = %s
                           AND p.post_type = %s
                           AND m.meta_id > %d
                         ORDER BY m.meta_id ASC
                         LIMIT %d",
                        $meta_key,
                        $post_type,
                        $last_meta_id,
                        $batch_size
                    ),
                    ARRAY_A
                );

                if ( empty( $rows ) ) {
                    break;
                }

                foreach ( $rows as $row ) {
                    $meta_id = isset( $row['meta_id'] ) ? absint( $row['meta_id'] ) : 0;
                    $post_id = isset( $row['post_id'] ) ? absint( $row['post_id'] ) : 0;
                    $raw     = isset( $row['meta_value'] ) ? (string) $row['meta_value'] : '';

                    if ( $meta_id > $last_meta_id ) {
                        $last_meta_id = $meta_id;
                    }

                    $scanned++;

                    if ( '' === $raw || ! is_serialized( $raw ) ) {
                        continue;
                    }

                    $unserialized = developer_starter_try_unserialize_no_classes( $raw );
                    if ( is_array( $unserialized ) ) {
                        continue;
                    }

                    $fixed = developer_starter_fix_serialized_string_lengths( $raw );
                    if ( ! is_string( $fixed ) || $fixed === $raw ) {
                        $failed++;
                        continue;
                    }

                    $unserialized = developer_starter_try_unserialize_no_classes( $fixed );
                    if ( ! is_array( $unserialized ) ) {
                        $failed++;
                        continue;
                    }

                    $ok = false;
                    if ( function_exists( 'update_metadata_by_mid' ) ) {
                        $ok = (bool) update_metadata_by_mid( 'post', $meta_id, $unserialized );
                    } elseif ( $post_id ) {
                        $ok = (bool) update_post_meta( $post_id, $meta_key, $unserialized );
                    }

                    if ( $ok ) {
                        $repaired++;
                    } else {
                        $failed++;
                    }
                }

                if ( count( $rows ) < $batch_size ) {
                    break;
                }
            }
        }

        $redirect = wp_get_referer();
        if ( ! $redirect ) {
            $redirect = admin_url( 'admin.php?page=developer-starter-settings&tab=advanced' );
        }

        $redirect = add_query_arg(
            array(
                'tab'                   => 'advanced',
                'ds_modules_meta_repair' => '1',
                'ds_scanned'             => $scanned,
                'ds_repaired'            => $repaired,
                'ds_failed'              => $failed,
            ),
            $redirect
        );

        wp_safe_redirect( $redirect );
        exit;
    }

    private function get_modules_repair_targets() {
        return array(
            array(
                'type'      => 'post_meta',
                'label'     => __( '主题页面模块', 'developer-starter' ),
                'meta_key'  => '_developer_starter_modules',
                'post_type' => 'page',
            ),
            array(
                'type'      => 'post_meta',
                'label'     => __( '启灵积分商城页面装修', 'developer-starter' ),
                'meta_key'  => '_qls_shop_layout',
                'post_type' => 'page',
            ),
            array(
                'type'        => 'option',
                'label'       => __( '启灵积分商城旧版全局布局', 'developer-starter' ),
                'option_name' => 'qls_shop_home_layout',
            ),
        );
    }

    public function handle_repair_theme_options() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '权限不足', 'developer-starter' ) );
        }

        check_admin_referer( 'ds_repair_theme_options_nonce', 'ds_repair_theme_options_nonce' );

        if (
            ! function_exists( 'developer_starter_get_raw_option_value' ) ||
            ! function_exists( 'developer_starter_fix_serialized_string_lengths' ) ||
            ! function_exists( 'developer_starter_try_unserialize_no_classes' ) ||
            ! function_exists( 'is_serialized' )
        ) {
            wp_die( __( '修复函数未就绪，请确认主题文件完整。', 'developer-starter' ) );
        }

        $result = 'failed';
        $raw = developer_starter_get_raw_option_value( $this->option_name );

        if ( ! is_string( $raw ) || '' === $raw ) {
            $result = 'missing';
        } elseif ( ! is_serialized( $raw ) ) {
            $result = 'not_serialized';
        } else {
            $unserialized = developer_starter_try_unserialize_no_classes( $raw );
            if ( is_array( $unserialized ) ) {
                $result = 'ok';
            } else {
                $fixed = developer_starter_fix_serialized_string_lengths( $raw );
                if ( is_string( $fixed ) && $fixed !== $raw ) {
                    $unserialized = developer_starter_try_unserialize_no_classes( $fixed );
                    if ( is_array( $unserialized ) ) {
                        if ( function_exists( 'developer_starter_add_theme_options_backup' ) ) {
                            developer_starter_add_theme_options_backup( $raw, 'manual' );
                        }
                        $updated = update_option( $this->option_name, $unserialized );
                        $result = $updated ? 'repaired' : 'failed';
                    } else {
                        $result = 'failed';
                    }
                } else {
                    $result = 'failed';
                }
            }
        }

        $redirect = wp_get_referer();
        if ( ! $redirect ) {
            $redirect = admin_url( 'admin.php?page=developer-starter-settings&tab=advanced' );
        }

        $redirect = add_query_arg(
            array(
                'tab'                       => 'advanced',
                'ds_options_repair'         => '1',
                'ds_options_repair_result'  => $result,
            ),
            $redirect
        );

        wp_safe_redirect( $redirect );
        exit;
    }
}
