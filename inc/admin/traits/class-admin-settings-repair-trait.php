<?php
/**
 * 主题后台设置数据修复 Trait
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
        echo '<p class="description" style="margin:0 0 12px;">' . esc_html__( '用于检测并修复因直接替换数据库域名导致的主题设置数据损坏。', 'developer-starter' ) . '</p>';
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
        echo '<div class="description" style="margin:0 0 12px;">' . esc_html__( '当前会扫描主题页面模块和启灵积分商城页面装修数据。', 'developer-starter' ) . '</div>';
        echo '<button type="button" class="button button-primary" id="ds-repair-modules-meta-btn">' . esc_html__( '扫描并修复页面模块', 'developer-starter' ) . '</button>';
        echo '<a class="button button-secondary" style="margin-left:8px;" href="' . esc_url( $action_url ) . '" onclick="return confirm(\'' . esc_js( __( '确定要开始扫描并修复页面模块数据吗？建议先备份数据库。', 'developer-starter' ) ) . '\');">' . esc_html__( '备用入口（无 JS）', 'developer-starter' ) . '</a>';
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

    /**
     * 渲染系统认证页面去重与修复字段。
     *
     * @param array<string,mixed> $options Theme options.
     * @return void
     */
    public function render_auth_pages_cleanup_field( $options ) {
        unset( $options );

        if ( ! class_exists( '\Developer_Starter\Core\Auth_Pages_Service' ) ) {
            $service_file = DEVELOPER_STARTER_INC . '/core/class-auth-pages-service.php';
            if ( file_exists( $service_file ) ) {
                require_once $service_file;
            }
        }

        $auth_service = class_exists( '\Developer_Starter\Core\Auth_Pages_Service' )
            ? new \Developer_Starter\Core\Auth_Pages_Service()
            : null;

        $duplicate_count = $auth_service ? $auth_service->count_duplicate_auth_pages() : 0;
        $nonce = wp_create_nonce( 'developer_starter_auth_cleanup_nonce' );

        echo '<tr><th scope="row">' . esc_html__( '系统认证页面状态', 'developer-starter' ) . '</th><td>';
        echo '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;max-width:820px;">';
        echo '<p class="description" style="margin:0 0 12px;">' . esc_html__( '检测并一键清理因高并发或异常重复生成的“用户登录 / 用户注册 / 找回密码 / 个人中心”页面。系统会自动保留最早创建的主页面，将多余的重复项安全移至回收站并重新校准设置绑定。', 'developer-starter' ) . '</p>';

        if ( $duplicate_count > 0 ) {
            echo '<div style="margin:0 0 12px;">';
            echo '<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-weight:600;font-size:13px;">';
            echo '<span class="dashicons dashicons-warning" style="font-size:18px;width:18px;height:18px;"></span>';
            echo sprintf( esc_html__( '检测到 %d 个冗余重复的系统认证页面', 'developer-starter' ), $duplicate_count );
            echo '</span>';
            echo '</div>';

            echo '<div style="margin:0 0 12px;">';
            echo '<button type="button" class="button button-primary" id="ds-cleanup-auth-pages-btn">' . esc_html__( '一键清理冗余重复页面', 'developer-starter' ) . '</button>';
            echo '<span id="ds-cleanup-auth-pages-msg" style="margin-left:10px;font-weight:600;"></span>';
            echo '</div>';
            echo '<p class="description" style="margin:0;font-size:12px;color:#64748b;">' . esc_html__( '注意：清理操作会将多余的重复页面移入 WordPress 回收站，不会彻底物理删除，如有需要随时可从回收站恢复。', 'developer-starter' ) . '</p>';
        } else {
            echo '<div style="margin:0 0 12px;">';
            echo '<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-weight:600;font-size:13px;">';
            echo '<span class="dashicons dashicons-yes-alt" style="font-size:18px;width:18px;height:18px;"></span>';
            echo esc_html__( '系统认证页面状态正常，未检测到重复页面', 'developer-starter' );
            echo '</span>';
            echo '</div>';

            echo '<button type="button" class="button button-secondary" id="ds-cleanup-auth-pages-btn">' . esc_html__( '重新扫描并校准认证页面', 'developer-starter' ) . '</button>';
            echo '<span id="ds-cleanup-auth-pages-msg" style="margin-left:10px;font-weight:600;"></span>';
        }

        echo '<input type="hidden" id="ds-cleanup-auth-pages-nonce" value="' . esc_attr( $nonce ) . '" />';
        ?>
        <script>
        (function() {
            var btn = document.getElementById('ds-cleanup-auth-pages-btn');
            var msg = document.getElementById('ds-cleanup-auth-pages-msg');
            var nonceInput = document.getElementById('ds-cleanup-auth-pages-nonce');
            if (!btn) return;
            btn.addEventListener('click', function() {
                if (btn.disabled) return;
                btn.disabled = true;
                if (msg) {
                    msg.style.color = '#2563eb';
                    msg.textContent = '正在执行安全清理与校准，请稍候...';
                }
                var data = new FormData();
                data.append('action', 'developer_starter_cleanup_duplicate_auth_pages');
                data.append('nonce', nonceInput ? nonceInput.value : '');

                fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
                    .then(function(res) { return res.json(); })
                    .then(function(res) {
                        if (res && res.success) {
                            if (msg) {
                                msg.style.color = '#16a34a';
                                msg.textContent = (res.data && res.data.message) ? res.data.message : '清理完成！';
                            }
                            window.setTimeout(function() { window.location.reload(); }, 1200);
                        } else {
                            if (msg) {
                                msg.style.color = '#dc2626';
                                var errMsg = '清理失败，请重试';
                                if (res && res.data) {
                                    if (typeof res.data === 'string') {
                                        errMsg = res.data;
                                    } else if (res.data.message) {
                                        errMsg = res.data.message;
                                    }
                                }
                                msg.textContent = errMsg;
                            }
                            btn.disabled = false;
                        }
                    })
                    .catch(function() {
                        if (msg) {
                            msg.style.color = '#dc2626';
                            msg.textContent = '请求异常，请刷新后重试';
                        }
                        btn.disabled = false;
                    });
            });
        })();
        </script>
        <?php
        echo '</div></td></tr>';
    }

    /**
     * AJAX 处理清理重复系统认证页面请求。
     *
     * @return void
     */
    public function ajax_cleanup_duplicate_auth_pages() {
        check_ajax_referer( 'developer_starter_auth_cleanup_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        if ( ! class_exists( '\Developer_Starter\Core\Auth_Pages_Service' ) ) {
            $service_file = DEVELOPER_STARTER_INC . '/core/class-auth-pages-service.php';
            if ( file_exists( $service_file ) ) {
                require_once $service_file;
            }
        }

        if ( ! class_exists( '\Developer_Starter\Core\Auth_Pages_Service' ) ) {
            wp_send_json_error( array( 'message' => __( '认证服务类未加载', 'developer-starter' ) ) );
        }

        $service = new \Developer_Starter\Core\Auth_Pages_Service();
        $result  = $service->cleanup_duplicate_auth_pages();

        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( $result );
        } else {
            $message = ! empty( $result['message'] ) ? $result['message'] : __( '清理失败，请重试', 'developer-starter' );
            wp_send_json_error( array( 'message' => $message ) );
        }
    }
}
