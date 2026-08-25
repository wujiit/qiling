<?php
/**
 * Admin settings operational tools field render trait.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_Tools_Trait {

    private function render_thumbnail_cache_section( $options ) {
        $optimizer = function_exists( 'developer_starter_get_thumbnail_optimizer_instance' )
            ? developer_starter_get_thumbnail_optimizer_instance()
            : null;
        $stats = $optimizer ? $optimizer->get_cache_stats() : array( 'count' => 0, 'bytes' => 0, 'size_human' => '0 B', 'dir' => '' );

        echo '<tr><th scope="row">' . esc_html__( '缩略图缓存管理', 'developer-starter' ) . '</th><td>';
        echo '<div id="thumbnail-cache-manager" class="ds-db-stats">';
        echo '<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;max-width:720px;">';
        echo '<div class="db-stat-item ds-db-stat-item"><span>' . esc_html__( '缓存文件', 'developer-starter' ) . '</span><strong id="thumbnail-cache-count">' . esc_html( number_format_i18n( (int) $stats['count'] ) ) . ' ' . esc_html__( '张', 'developer-starter' ) . '</strong></div>';
        echo '<div class="db-stat-item ds-db-stat-item"><span>' . esc_html__( '占用空间', 'developer-starter' ) . '</span><strong id="thumbnail-cache-size">' . esc_html( (string) $stats['size_human'] ) . '</strong></div>';
        echo '</div>';
        echo '<p><strong>' . esc_html__( '缓存目录：', 'developer-starter' ) . '</strong><code id="thumbnail-cache-dir">' . esc_html( (string) $stats['dir'] ) . '</code></p>';
        echo '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">';
        echo '<button type="button" class="button" id="refresh-thumbnail-cache-stats">' . esc_html__( '刷新统计', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button button-secondary" id="clear-unused-thumbnail-cache">' . esc_html__( '扫描并清理未使用缓存', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button button-link-delete" id="clear-thumbnail-cache">' . esc_html__( '清空全部缓存', 'developer-starter' ) . '</button>';
        echo '<span id="thumbnail-cache-result" style="font-weight:600;"></span>';
        echo '</div>';
        echo '<p class="description" style="margin-top:8px;">' . esc_html__( '未使用扫描会保留当前文章、页面及其他内容类型引用过的特色图、自定义封面和正文首图缓存；清空后会在前台访问时按需重新生成。', 'developer-starter' ) . '</p>';
        echo '<p class="description">' . esc_html__( '这里只清理服务器本地 uploads/thumbnail-cache。对象存储中的历史文件不会自动删除，避免误删其他站点或旧配置共用的对象。', 'developer-starter' ) . '</p>';
        echo '</div></td></tr>';
    }

    /**
     * 页面装修修订管理入口。
     *
     * @param array<string,mixed> $options 当前主题设置。
     * @return void
     */
    private function render_builder_revisions_section( $options ) {
        $manager_url = admin_url( 'admin.php?page=qiling-builder-revisions' );
        echo '<tr><th colspan="2"><h3>' . esc_html__( '页面装修修订管理', 'developer-starter' ) . '</h3>';
        echo '<p class="description">' . esc_html__( '统一查看所有页面前台装修器生成的保存快照。', 'developer-starter' ) . '</p></th></tr>';
        echo '<tr><th scope="row">' . esc_html__( '管理入口', 'developer-starter' ) . '</th><td>';
        echo '<p style="margin-top:0;">' . esc_html__( '可以按页面查看修订时间、操作者和模块数量，也可以删除单条修订、清空某个页面或批量清理。恢复操作会打开对应页面的前台装修器，由你确认后执行。', 'developer-starter' ) . '</p>';
        echo '<a class="button button-primary" href="' . esc_url( $manager_url ) . '">' . esc_html__( '打开装修修订管理', 'developer-starter' ) . '</a>';
        echo '</td></tr>';
    }

    private function render_content_model_center_overview_field( $options ) {
        echo '<tr><th scope="row">' . esc_html__( '模型结构预览', 'developer-starter' ) . '</th><td>';

        if ( ! class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            echo '<p class="description">' . esc_html__( '内容模型中心服务未加载。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $payload = \Developer_Starter\Core\Content_Model_Center::get_client_payload( is_array( $options ) ? $options : array() );
        $models = isset( $payload['models'] ) && is_array( $payload['models'] ) ? $payload['models'] : array();
        $all_models = isset( $payload['allModels'] ) && is_array( $payload['allModels'] ) ? $payload['allModels'] : array();
        $enabled_count = count( $models );
        $total_count = count( $all_models );

        echo '<div class="ds-content-model-overview">';
        echo '<p class="description">' . esc_html(
            sprintf(
                /* translators: 1: enabled model count, 2: total model count */
                __( '当前启用 %1$d / %2$d 个模型。保存后，启用的可注册模型会出现在后台菜单，并向前台装修器与 AI 装修同步结构。', 'developer-starter' ),
                $enabled_count,
                $total_count
            )
        ) . '</p>';

        if ( empty( $all_models ) ) {
            echo '<p class="description">' . esc_html__( '暂无模型定义。', 'developer-starter' ) . '</p></div></td></tr>';
            return;
        }

        echo '<table class="widefat striped" style="max-width:960px;margin-top:10px;">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( '模型', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '内容类型', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '字段', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '模块提示', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '状态', 'developer-starter' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $all_models as $model_id => $model ) {
            if ( ! is_array( $model ) ) {
                continue;
            }

            $is_enabled = isset( $models[ $model_id ] );
            $fields = isset( $model['fields'] ) && is_array( $model['fields'] ) ? $model['fields'] : array();
            $field_labels = array();
            foreach ( array_slice( $fields, 0, 6 ) as $field ) {
                if ( is_array( $field ) && ! empty( $field['label'] ) ) {
                    $field_labels[] = (string) $field['label'];
                }
            }
            if ( count( $fields ) > 6 ) {
                $field_labels[] = sprintf( __( '等 %d 项', 'developer-starter' ), count( $fields ) );
            }

            $module_hints = isset( $model['moduleHints'] ) && is_array( $model['moduleHints'] )
                ? implode( ', ', array_slice( array_map( 'strval', $model['moduleHints'] ), 0, 5 ) )
                : '';
            $post_type = isset( $model['postType'] ) ? (string) $model['postType'] : '';
            $admin_url = isset( $model['adminUrl'] ) ? (string) $model['adminUrl'] : '';

            echo '<tr>';
            echo '<td><strong>' . esc_html( isset( $model['label'] ) ? (string) $model['label'] : (string) $model_id ) . '</strong><br><span class="description">' . esc_html( isset( $model['description'] ) ? (string) $model['description'] : '' ) . '</span></td>';
            echo '<td>';
            if ( $post_type ) {
                if ( $admin_url && $is_enabled ) {
                    echo '<a href="' . esc_url( $admin_url ) . '">' . esc_html( $post_type ) . '</a>';
                } else {
                    echo esc_html( $post_type );
                }
            } else {
                echo '<span class="description">' . esc_html__( '外部/虚拟模型', 'developer-starter' ) . '</span>';
            }
            echo '</td>';
            echo '<td>' . esc_html( implode( '、', $field_labels ) ) . '</td>';
            echo '<td><code>' . esc_html( $module_hints ) . '</code></td>';
            echo '<td>' . ( $is_enabled ? '<span style="color:#047857;font-weight:600;">' . esc_html__( '已启用', 'developer-starter' ) . '</span>' : '<span class="description">' . esc_html__( '未启用', 'developer-starter' ) . '</span>' ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</td></tr>';
    }

    private function render_header_menu_locations_overview_field( $options ) {
        $options = is_array( $options ) ? $options : array();
        $locations = get_nav_menu_locations();
        $registered_locations = get_registered_nav_menus();
        $menu_admin_url = admin_url( 'nav-menus.php' );
        $location_admin_url = admin_url( 'nav-menus.php?action=locations' );
        $recommended_mobile_bottom = isset( $options['mobile_bottom_recommended_items'] )
            ? (int) $options['mobile_bottom_recommended_items']
            : 5;
        if ( ! in_array( (string) $recommended_mobile_bottom, array( '3', '4', '5' ), true ) ) {
            $recommended_mobile_bottom = 5;
        }

        $location_notes = array(
            'primary'       => __( '桌面主导航', 'developer-starter' ),
            'mobile'        => __( '移动抽屉菜单', 'developer-starter' ),
            'mobile_bottom' => __( '移动底部菜单', 'developer-starter' ),
            'left_sidebar'  => __( '桌面左侧导航', 'developer-starter' ),
        );

        echo '<tr><th scope="row">' . esc_html__( '菜单位置概览', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-menu-location-overview">';
        echo '<div class="ds-menu-location-overview__head">';
        echo '<div>';
        echo '<strong>' . esc_html__( '当前 WordPress 菜单位置', 'developer-starter' ) . '</strong>';
        echo '</div>';
        echo '<p class="ds-menu-location-overview__actions">';
        echo '<a class="button" href="' . esc_url( $menu_admin_url ) . '">' . esc_html__( '编辑菜单内容', 'developer-starter' ) . '</a>';
        echo '<a class="button" href="' . esc_url( $location_admin_url ) . '">' . esc_html__( '分配菜单位置', 'developer-starter' ) . '</a>';
        echo '</p>';
        echo '</div>';

        echo '<table class="widefat striped ds-menu-location-overview__table">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( '位置', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '当前菜单', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '菜单项', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '用途', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '状态', 'developer-starter' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $location_notes as $location => $note ) {
            $location_label = isset( $registered_locations[ $location ] ) ? $registered_locations[ $location ] : $location;
            $menu_id = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;
            $menu_name = __( '未分配', 'developer-starter' );
            $item_count = 0;
            $status_label = __( '未分配', 'developer-starter' );
            $status_class = 'ds-menu-location-overview__status ds-menu-location-overview__status--warning';

            if ( $menu_id > 0 ) {
                $menu_object = wp_get_nav_menu_object( $menu_id );
                if ( $menu_object ) {
                    $menu_name = $menu_object->name;
                    $menu_items = wp_get_nav_menu_items(
                        $menu_id,
                        array(
                            'update_post_term_cache' => false,
                        )
                    );
                    $item_count = 0;
                    if ( is_array( $menu_items ) ) {
                        foreach ( $menu_items as $menu_item ) {
                            if ( 'mobile_bottom' === $location && isset( $menu_item->menu_item_parent ) && '0' !== (string) $menu_item->menu_item_parent ) {
                                continue;
                            }
                            $item_count++;
                        }
                    }
                    $status_label = __( '已分配', 'developer-starter' );
                    $status_class = 'ds-menu-location-overview__status ds-menu-location-overview__status--ok';

                    if ( 'mobile_bottom' === $location && $item_count > $recommended_mobile_bottom ) {
                        $status_label = sprintf(
                            /* translators: %d: recommended item count */
                            __( '超过建议 %d 项', 'developer-starter' ),
                            $recommended_mobile_bottom
                        );
                        $status_class = 'ds-menu-location-overview__status ds-menu-location-overview__status--warning';
                    }
                }
            }

            echo '<tr>';
            echo '<td><strong>' . esc_html( $location_label ) . '</strong><br><code>' . esc_html( $location ) . '</code></td>';
            echo '<td>' . esc_html( $menu_name ) . '</td>';
            echo '<td>' . esc_html( (string) $item_count ) . '</td>';
            echo '<td>' . esc_html( $note ) . '</td>';
            echo '<td><strong class="' . esc_attr( $status_class ) . '">' . esc_html( $status_label ) . '</strong></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</td></tr>';
    }

    private function render_ip_cache_clear_field( $options ) {
        echo '<tr><th scope="row">' . __( '清理 IP 接口缓存', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<button type="button" id="clear-ip-cache" class="button button-secondary">' . __( '清理 IP 接口缓存', 'developer-starter' ) . '</button>';
        echo '<p id="clear-ip-cache-result" style="margin-top: 8px; font-weight: 600;"></p>';
        echo '<p class="description">' . __( '清理 uploads/cache/developer-starter/ip-location 内的 IP 归属地临时缓存文件。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
    }

    private function render_ip_usermeta_reset_field( $options ) {
        echo '<tr><th scope="row">' . __( '重置用户 IP 归属地数据', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<button type="button" id="reset-ip-usermeta" class="button button-secondary">' . __( '重置用户 IP 归属地数据', 'developer-starter' ) . '</button>';
        echo '<p id="reset-ip-usermeta-result" style="margin-top: 8px; font-weight: 600;"></p>';
        echo '<p class="description">' . __( '此操作不会删除用户账号，只会清空已保存的旧 IP 归属地，用户下次访问时重新获取并写入数据库。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
    }

    private function render_assets_refresh_field( $options ) {
        echo '<tr><th scope="row">' . __( '刷新缓存', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<button type="button" class="button button-secondary" id="refresh-assets-version">' . __( '一键刷新版本号', 'developer-starter' ) . '</button>';
        echo '<span id="refresh-version-result" style="margin-left: 10px; color: #10b981;"></span>';
        echo '<p class="description">' . __( '点击后将自动生成新的版本号，强制浏览器重新加载所有 CSS/JS 文件', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
    }

    private function render_smtp_test_field( $options ) {
        $current_user = wp_get_current_user();
        $target_email = '';
        if ( $current_user instanceof \WP_User && ! empty( $current_user->user_email ) ) {
            $target_email = sanitize_email( (string) $current_user->user_email );
        }
        if ( $target_email === '' ) {
            $target_email = sanitize_email( (string) get_option( 'admin_email' ) );
        }

        echo '<tr><th scope="row">' . __( 'SMTP 测试', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<button type="button" id="send-smtp-test-email" class="button button-secondary">' . __( '发送测试邮件', 'developer-starter' ) . '</button>';
        echo '<p id="smtp-test-result" style="margin-top: 8px; font-weight: 600;"></p>';
        if ( $target_email !== '' ) {
            echo '<p class="description">' . sprintf( __( '测试邮件将发送到当前管理员邮箱：%s（请先保存设置后再测试）。', 'developer-starter' ), esc_html( $target_email ) ) . '</p>';
        } else {
            echo '<p class="description">' . __( '未检测到管理员邮箱，请先在 WordPress 后台设置管理员邮箱。', 'developer-starter' ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function render_generate_min_css_field( $options ) {
        echo '<tr><th scope="row">' . __( '手动生成压缩文件', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<button type="button" id="generate-min-css" class="button button-secondary">' . __( '⚡ 立即生成压缩文件 (.min.css)', 'developer-starter' ) . '</button>';
        echo '<p id="generate-css-result" style="margin-top: 8px; font-weight: 600;"></p>';
        echo '<p class="description">' . __( '点击后生成前台所需的 CSS 压缩文件，可用于更新正式环境中的样式资源；原始样式文件不会被修改。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
    }

    private function render_split_css_integrity_field( $options ) {
        echo '<tr><th scope="row">' . __( '拆分 CSS 完整性', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<button type="button" id="check-split-css-integrity" class="button button-secondary">' . __( '🧪 检查拆分 CSS 完整性', 'developer-starter' ) . '</button>';
        echo '<p id="check-split-css-result" style="margin-top: 8px; font-weight: 600;"></p>';
        echo '<p class="description">' . __( '检查拆分后的 CSS 资源是否完整一致，可快速发现文件缺失、内容过期或空文件。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
    }

    private function render_gzip_status_field( $options ) {
        echo '<tr><th scope="row">' . __( 'Gzip 压缩状态', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<button type="button" id="check-gzip-status" class="button button-secondary">' . __( '🔍 检测 Gzip/Brotli 是否开启', 'developer-starter' ) . '</button>';
        echo '<p id="check-gzip-result" style="margin-top: 8px; font-weight: 600;"></p>';
        echo '<p class="description">' . __( '检测首页和主题 CSS 的响应头，查看 Content-Encoding 是否返回 gzip 或 br。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
    }

    private function render_wp_cron_status_hint( $options ) {
        $wp_cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
        $cron_url = add_query_arg( 'doing_wp_cron', '1', site_url( 'wp-cron.php' ) );
        $curl_cmd = '*/5 * * * * curl -fsS "' . esc_url_raw( $cron_url ) . '" >/dev/null 2>&1';
        $wp_cron_file = defined( 'ABSPATH' ) ? trailingslashit( ABSPATH ) . 'wp-cron.php' : '';
        $wp_cron_file_status = 'missing';

        if ( $wp_cron_file && file_exists( $wp_cron_file ) && is_readable( $wp_cron_file ) ) {
            $wp_cron_size = (int) filesize( $wp_cron_file );
            $wp_cron_sample = $wp_cron_size > 0 ? (string) file_get_contents( $wp_cron_file, false, null, 0, 65536 ) : '';
            if ( $wp_cron_size < 200 || false === strpos( $wp_cron_sample, 'wp-load.php' ) || false === strpos( $wp_cron_sample, 'wp_cron' ) ) {
                $wp_cron_file_status = 'invalid';
            } else {
                $wp_cron_file_status = 'valid';
            }
        }

        echo '<tr><th scope="row">' . __( 'WP-Cron 运行状态', 'developer-starter' ) . '</th>';
        echo '<td>';

        if ( $wp_cron_disabled ) {
            echo '<p style="margin:0 0 8px;color:#ef4444;font-weight:600;">' . esc_html__( '当前检测到 DISABLE_WP_CRON=true。请务必配置系统定时任务触发 wp-cron.php。', 'developer-starter' ) . '</p>';
            echo '<p class="description" style="margin:0 0 6px;">' . esc_html__( '推荐触发地址：', 'developer-starter' ) . '<code style="margin-left:6px;">' . esc_html( $cron_url ) . '</code></p>';
            echo '<p class="description" style="margin:0;">' . esc_html__( 'Linux Crontab 示例（每5分钟）：', 'developer-starter' ) . '<code style="display:inline-block;margin-left:6px;">' . esc_html( $curl_cmd ) . '</code></p>';
        } elseif ( 'valid' !== $wp_cron_file_status ) {
            echo '<p style="margin:0 0 8px;color:#ef4444;font-weight:600;">' . esc_html__( '检测到 wp-cron.php 文件不可用或疑似已被清空。', 'developer-starter' ) . '</p>';
            echo '<p class="description" style="margin:0 0 6px;">' . esc_html__( '如果你已经禁用默认 wp-cron.php，请使用下方“外部定时任务清理”入口触发主题清理。', 'developer-starter' ) . '</p>';
            echo '<p class="description" style="margin:0;">' . esc_html__( '需要恢复 WordPress 内置任务时，请恢复官方 wp-cron.php 文件内容。', 'developer-starter' ) . '</p>';
        } else {
            echo '<p style="margin:0;color:#10b981;font-weight:600;">' . esc_html__( 'wp-cron.php 文件可用，且未检测到 DISABLE_WP_CRON=true。', 'developer-starter' ) . '</p>';
            echo '<p class="description" style="margin:6px 0 0;">' . esc_html__( '若网站访问量较低，建议改用系统 Cron 提升定时任务稳定性。', 'developer-starter' ) . '</p>';
        }

        echo '</td></tr>';
    }

    private function render_cleanup_rest_endpoint( $options ) {
        $cron_enabled = ! empty( $options['cleanup_cron_enable'] );
        $cron_endpoint = rest_url( 'qiling/v1/maintenance/cleanup/cron' );
        $cron_token = function_exists( 'developer_starter_ensure_cleanup_cron_token' )
            ? developer_starter_ensure_cleanup_cron_token()
            : ( isset( $options['cleanup_cron_token'] ) ? (string) $options['cleanup_cron_token'] : '' );
        $cron_url = add_query_arg(
            array(
                'scope' => 'auto',
                'token' => $cron_token,
            ),
            $cron_endpoint
        );
        $cron_header_url = add_query_arg( 'scope', 'auto', $cron_endpoint );
        $cron_header = 'X-Qiling-Cleanup-Token: ' . $cron_token;
        $cron_header_curl = 'curl -fsS -X POST -H "' . $cron_header . '" "' . $cron_header_url . '"';
        $cron_last_run = get_option( 'developer_starter_cleanup_cron_last_run', '' );
        $cron_allowed_ips = isset( $options['cleanup_cron_allowed_ips'] ) ? trim( (string) $options['cleanup_cron_allowed_ips'] ) : '';
        $last_run = get_option( 'developer_starter_cleanup_rest_last_run', '' );
        $audit_log = function_exists( 'developer_starter_get_cleanup_rest_audit_log' )
            ? developer_starter_get_cleanup_rest_audit_log()
            : array();

        echo '<tr><th scope="row">' . esc_html__( '主题定时清理', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<p style="margin:0 0 8px;font-weight:600;">' . esc_html__( '后台手动执行', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '管理员可在此直接执行清理，无需配置外部任务。', 'developer-starter' ) . '</p>';
        echo '<button type="button" class="button button-secondary" id="run-theme-scheduled-cleanup" data-scope="all">' . esc_html__( '立即执行主题清理', 'developer-starter' ) . '</button>';
        echo '<span id="theme-scheduled-cleanup-result" style="margin-left:8px;color:#64748b;"></span>';

        if ( $last_run ) {
            echo '<p class="description" style="margin:8px 0 0;">' . esc_html__( '最近后台手动触发：', 'developer-starter' ) . '<strong style="margin-left:6px;">' . esc_html( $last_run ) . '</strong></p>';
        }

        echo '<hr style="max-width:760px;margin:14px 0;border:0;border-top:1px solid #e2e8f0;" />';
        echo '<p style="margin:0 0 8px;font-weight:600;">' . esc_html__( '外部定时任务清理', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '给第三方任务平台使用，支持完整链接和请求头两种模式。', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin:0 0 8px;font-weight:600;">' . esc_html__( '模式一：完整链接', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '访问地址：', 'developer-starter' ) . '<code id="cleanup-cron-url" style="display:inline-block;margin-left:6px;max-width:100%;white-space:normal;word-break:break-all;">' . esc_html( $cron_url ) . '</code></p>';
        echo '<p class="description" style="margin:0 0 8px;font-weight:600;">' . esc_html__( '模式二：Header Token', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '访问地址：', 'developer-starter' ) . '<code id="cleanup-cron-header-url" style="display:inline-block;margin-left:6px;max-width:100%;white-space:normal;word-break:break-all;">' . esc_html( $cron_header_url ) . '</code></p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '请求头：', 'developer-starter' ) . '<code id="cleanup-cron-header" style="display:inline-block;margin-left:6px;max-width:100%;white-space:normal;word-break:break-all;">' . esc_html( $cron_header ) . '</code></p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( 'cURL 示例：', 'developer-starter' ) . '<code id="cleanup-cron-header-curl" style="display:inline-block;margin-left:6px;max-width:100%;white-space:normal;word-break:break-all;">' . esc_html( $cron_header_curl ) . '</code></p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '当前访问密钥：', 'developer-starter' ) . '<code id="cleanup-cron-token" style="margin-left:6px;">' . esc_html( $cron_token ) . '</code> ';
        echo '<button type="button" class="button button-small" id="regenerate-cleanup-cron-token">' . esc_html__( '重新生成', 'developer-starter' ) . '</button>';
        echo '<span id="cleanup-cron-token-result" style="margin-left:8px;color:#64748b;"></span></p>';
        echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '支持自动清理、全部清理、修订记录和其他临时数据等范围。', 'developer-starter' ) . '</p>';

        if ( $cron_last_run ) {
            echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '最近外部任务触发：', 'developer-starter' ) . '<strong style="margin-left:6px;">' . esc_html( $cron_last_run ) . '</strong></p>';
        }

        if ( $cron_enabled && $cron_token ) {
            echo '<p class="description" style="margin:0 0 8px;color:#10b981;">' . esc_html__( '外部任务状态：已启用，密钥已配置。', 'developer-starter' ) . '</p>';
        } elseif ( $cron_enabled ) {
            echo '<p class="description" style="margin:0 0 8px;color:#f59e0b;">' . esc_html__( '外部任务状态：已启用，但还没有密钥；保存设置后会自动生成。', 'developer-starter' ) . '</p>';
        } else {
            echo '<p class="description" style="margin:0 0 8px;color:#64748b;">' . esc_html__( '外部任务状态：未启用（第三方平台请求不会执行）', 'developer-starter' ) . '</p>';
        }

        if ( $cron_allowed_ips ) {
            echo '<p class="description" style="margin:0 0 10px;">' . esc_html__( 'IP 白名单已配置，请确认第三方任务平台出口 IP 固定。', 'developer-starter' ) . '</p>';
        }

        echo '<div id="cleanup-rest-audit-log-container" style="margin-top:12px;">';
        echo '<p style="margin:0 0 8px;font-weight:600;">' . esc_html__( '清理审计日志', 'developer-starter' ) . '</p>';
        echo '<div id="cleanup-rest-audit-log-list">';

        if ( empty( $audit_log ) ) {
            echo '<p class="description" style="margin:0 0 8px;">' . esc_html__( '暂无清理日志。', 'developer-starter' ) . '</p>';
        } else {
            echo '<table class="widefat striped" style="max-width:760px;margin:0 0 8px;"><thead><tr>';
            echo '<th>' . esc_html__( '时间', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '事件', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '用户', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '范围', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '状态', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '清理量', 'developer-starter' ) . '</th>';
            echo '</tr></thead><tbody>';

            foreach ( array_slice( $audit_log, 0, 10 ) as $entry ) {
                $entry = is_array( $entry ) ? $entry : array();
                echo '<tr>';
                echo '<td>' . esc_html( isset( $entry['time'] ) ? (string) $entry['time'] : '' ) . '</td>';
                echo '<td>' . esc_html( isset( $entry['event'] ) ? (string) $entry['event'] : '' ) . '</td>';
                echo '<td>' . esc_html( isset( $entry['user_id'] ) ? (string) absint( $entry['user_id'] ) : '0' ) . '</td>';
                echo '<td>' . esc_html( isset( $entry['scope'] ) ? (string) $entry['scope'] : '' ) . '</td>';
                echo '<td>' . esc_html( isset( $entry['status'] ) ? (string) absint( $entry['status'] ) : '0' ) . '</td>';
                echo '<td>' . esc_html( isset( $entry['deleted'] ) ? (string) absint( $entry['deleted'] ) : '-' ) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }

        echo '</div>';
        echo '<button type="button" class="button" id="clear-cleanup-rest-audit-log">' . esc_html__( '清空清理日志', 'developer-starter' ) . '</button>';
        echo '<span id="cleanup-rest-audit-log-result" style="margin-left:8px;color:#64748b;"></span>';
        echo '<p class="description" style="margin:8px 0 0;">' . esc_html__( '日志仅保留最近记录，可随时清空，避免长期占用数据库。', 'developer-starter' ) . '</p>';
        echo '</div>';

        echo '</td></tr>';
    }

    private function render_db_cleanup_section( $options ) {
        echo '<tr><th colspan="2"><h3>' . __( '一键数据库清理', 'developer-starter' ) . '</h3><p class="description">' . __( '手动清理数据库中的冗余数据，请先备份数据库', 'developer-starter' ) . '</p></th></tr>';
        
        echo '<tr><th scope="row">' . __( '数据统计', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<div id="db-stats-container" class="ds-db-stats">';
        echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;" id="db-stats-grid">';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '📝 文章修订版本', 'developer-starter' ) . '</span><span id="stat-revisions" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '📋 自动草稿', 'developer-starter' ) . '</span><span id="stat-drafts" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '🗑️ 回收站文章', 'developer-starter' ) . '</span><span id="stat-trash" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '🚫 垃圾评论', 'developer-starter' ) . '</span><span id="stat-spam" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '📎 孤立文章元数据', 'developer-starter' ) . '</span><span id="stat-orphan-postmeta" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '💬 孤立评论元数据', 'developer-starter' ) . '</span><span id="stat-orphan-commentmeta" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '🔗 孤立关系数据', 'developer-starter' ) . '</span><span id="stat-orphan-relationships" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '🔔 Pingback/Trackback', 'developer-starter' ) . '</span><span id="stat-pingbacks" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '🏷️ 未使用标签', 'developer-starter' ) . '</span><span id="stat-unused-tags" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '⏳ 过期 Transients', 'developer-starter' ) . '</span><span id="stat-transients" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '👁 文章浏览量记录', 'developer-starter' ) . '</span><span id="stat-post-views" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '📦 数据包页面', 'developer-starter' ) . '</span><span id="stat-package-pages" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item">';
        echo '<span>' . __( '🗑️ 数据包回收站页面', 'developer-starter' ) . '</span><span id="stat-package-trash-pages" style="font-weight: 600; color: #64748b;">' . __( '加载中...', 'developer-starter' ) . '</span></div>';
        echo '</div>';
        echo '<div style="margin-top: 12px; text-align: right;">';
        echo '<button type="button" class="button" id="refresh-db-stats">' . __( '🔄 刷新统计', 'developer-starter' ) . '</button>';
        echo '</div>';
        echo '</div>';
        echo '</td></tr>';
        
        echo '<tr><th scope="row">' . __( '选择清理项', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 15px;">';
        echo '<label><input type="checkbox" name="db_clean_revisions" value="1" checked /> ' . __( '文章修订版本', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_drafts" value="1" checked /> ' . __( '自动草稿', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_trash" value="1" checked /> ' . __( '回收站文章', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_spam" value="1" checked /> ' . __( '垃圾评论', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_orphan_postmeta" value="1" checked /> ' . __( '孤立的文章元数据', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_orphan_commentmeta" value="1" checked /> ' . __( '孤立的评论元数据', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_orphan_relationships" value="1" checked /> ' . __( '孤立的关系数据', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_pingbacks" value="1" /> ' . __( 'Pingback/Trackback 记录', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_unused_tags" value="1" /> ' . __( '未使用的标签', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_transients" value="1" /> ' . __( '过期的 Transients 缓存', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_post_views" value="1" /> ' . __( '清空全站文章浏览量', 'developer-starter' ) . '</label>';
        echo '<label><input type="checkbox" name="db_clean_package_trash_pages" value="1" /> ' . __( '彻底删除回收站中的数据包页面', 'developer-starter' ) . '</label>';
        echo '</div>';
        echo '<button type="button" class="button button-secondary" id="run-db-cleanup" style="margin-right: 10px;">' . __( '🧹 一键清理数据库', 'developer-starter' ) . '</button>';
        echo '<span id="db-cleanup-result" style="color: #10b981;"></span>';
        echo '<p class="description" style="margin-top: 10px; color: #ef4444;">' . __( '⚠️ 此操作不可逆，请确保已备份数据库！', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin-top: 8px;">' . __( '清空全站文章浏览量会删除所有文章的浏览量统计记录。', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin-top: 8px;">' . __( '数据包页面只统计和处理由站点数据包创建的页面。彻底删除回收站中的数据包页面时，对应页面模块数据也会一并清理。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
    }

    private function render_poster_cache_section( $options ) {
        $stats = $this->collect_poster_cache_stats();

        echo '<tr><th colspan="2"><h3>' . __( '文章海报缓存', 'developer-starter' ) . '</h3><p class="description">' . __( '统计并清理主题生成的文章海报缓存文件', 'developer-starter' ) . '</p></th></tr>';

        echo '<tr><th scope="row">' . __( '缓存统计', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<div id="poster-cache-stats-container" class="ds-db-stats">';
        echo '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;" id="poster-cache-stats-grid">';
        echo '<div class="db-stat-item ds-db-stat-item"><span>' . __( '🖼️ 已生成海报', 'developer-starter' ) . '</span><span id="poster-cache-count" style="font-weight:600;color:#64748b;">' . esc_html( (int) $stats['count'] ) . ' ' . __( '张', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item"><span>' . __( '💾 占用空间', 'developer-starter' ) . '</span><span id="poster-cache-size" style="font-weight:600;color:#64748b;">' . esc_html( (string) $stats['size_human'] ) . '</span></div>';
        echo '</div>';
        echo '<p style="margin:10px 0 0;"><strong>' . __( '缓存目录：', 'developer-starter' ) . '</strong><code id="poster-cache-dir" style="margin-left:6px;">' . esc_html( (string) $stats['dir'] ) . '</code></p>';
        echo '<div style="margin-top:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">';
        echo '<button type="button" class="button" id="refresh-poster-cache-stats">🔄 ' . __( '刷新统计', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button button-secondary" id="clear-poster-cache">' . __( '清理海报缓存', 'developer-starter' ) . '</button>';
        echo '<span id="clear-poster-cache-result" style="font-weight:600;"></span>';
        echo '</div>';
        echo '<p class="description" style="margin-top:8px;">' . __( '说明：该统计基于本地 uploads/qiling-posters 目录，若使用远程对象存储，请以对象存储端数据为准。', 'developer-starter' ) . '</p>';
        echo '<p class="description" style="margin-top:6px;">' . __( '清理操作仅允许删除本地 uploads/qiling-posters 白名单目录内的海报缓存文件；如果使用远程对象存储，请同时到远程存储端清理。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '</td></tr>';

        $github_stats = class_exists( '\Developer_Starter\Core\GitHub_Repository_Activity_Service' )
            ? ( new \Developer_Starter\Core\GitHub_Repository_Activity_Service() )->collect_cache_stats()
            : array(
                'count'      => 0,
                'bytes'      => 0,
                'size_human' => '0 B',
                'dir'        => '',
            );

        echo '<tr><th colspan="2"><h3>' . __( 'GitHub 项目动态缓存', 'developer-starter' ) . '</h3><p class="description">' . __( '统计并清理 GitHub 项目动态模块生成的本地 JSON 缓存文件。', 'developer-starter' ) . '</p></th></tr>';
        echo '<tr><th scope="row">' . __( '缓存统计', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<div id="github-activity-cache-stats-container" class="ds-db-stats">';
        echo '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;" id="github-activity-cache-stats-grid">';
        echo '<div class="db-stat-item ds-db-stat-item"><span>' . __( 'GitHub 缓存文件', 'developer-starter' ) . '</span><span id="github-activity-cache-count" style="font-weight:600;color:#64748b;">' . esc_html( (int) $github_stats['count'] ) . ' ' . __( '个', 'developer-starter' ) . '</span></div>';
        echo '<div class="db-stat-item ds-db-stat-item"><span>' . __( '占用空间', 'developer-starter' ) . '</span><span id="github-activity-cache-size" style="font-weight:600;color:#64748b;">' . esc_html( (string) $github_stats['size_human'] ) . '</span></div>';
        echo '</div>';
        echo '<p style="margin:10px 0 0;"><strong>' . __( '缓存目录：', 'developer-starter' ) . '</strong><code id="github-activity-cache-dir" style="margin-left:6px;">' . esc_html( (string) $github_stats['dir'] ) . '</code></p>';
        echo '<div style="margin-top:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">';
        echo '<button type="button" class="button" id="refresh-github-activity-cache-stats">🔄 ' . __( '刷新统计', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button button-secondary" id="clear-github-activity-cache">' . __( '清理 GitHub 缓存', 'developer-starter' ) . '</button>';
        echo '<span id="clear-github-activity-cache-result" style="font-weight:600;"></span>';
        echo '</div>';
        echo '<p class="description" style="margin-top:8px;">' . __( '说明：关闭模块的“启用 GitHub 项目展示”时不会访问 GitHub，也不会生成这些缓存文件。清理后，下次启用并访问对应页面时会重新拉取公开仓库数据。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '</td></tr>';
    }

    private function render_announcement_display_script( $options ) {
        echo '<script>
        jQuery(function($){
            function toggleAnnouncementRows(){
                var val = $("#announcement_display_on").val();
                $(".ann-pages-row, .ann-posts-row, .ann-cats-row").hide();
                if(val === "pages") $(".ann-pages-row").show();
                if(val === "posts") $(".ann-posts-row").show();
                if(val === "categories") $(".ann-cats-row").show();
            }
            $("#announcement_display_on").on("change", toggleAnnouncementRows);
            toggleAnnouncementRows();
        });
        </script>';
    }

    private function render_blog_preset_customization_field( $options ) {
        echo '<tr><th scope="row">' . esc_html__( '风格定制面板', 'developer-starter' ) . '</th><td>';

        if ( ! class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' ) ) {
            echo '<p class="description">' . esc_html__( '博客风格管理器未加载，暂时无法渲染该面板。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $options = is_array( $options ) ? $options : array();
        $preset_choices = \Developer_Starter\Core\Blog_Visual_Manager::get_customizable_preset_choices();
        $schema = \Developer_Starter\Core\Blog_Visual_Manager::get_preset_customization_schema();

        echo '<style>
        .ds-blog-preset-customizer{display:grid;gap:18px;max-width:1100px;}
        .ds-blog-preset-customizer__card{border:1px solid #dbe3f0;border-radius:16px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.04);overflow:hidden;}
        .ds-blog-preset-customizer__head{padding:18px 20px;border-bottom:1px solid #eef2f7;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
        .ds-blog-preset-customizer__head h4{margin:0;font-size:15px;color:#0f172a;}
        .ds-blog-preset-customizer__head p{margin:6px 0 0;color:#64748b;}
        .ds-blog-preset-customizer__groups{padding:18px 20px;display:grid;gap:16px;}
        .ds-blog-preset-customizer__group{border:1px solid #edf2f7;border-radius:12px;padding:14px 16px;background:#fcfdff;}
        .ds-blog-preset-customizer__group h5{margin:0 0 6px;font-size:13px;color:#0f172a;}
        .ds-blog-preset-customizer__group > p{margin:0 0 12px;color:#64748b;}
        .ds-blog-preset-customizer__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px 14px;}
        .ds-blog-preset-customizer__field{display:flex;flex-direction:column;gap:6px;}
        .ds-blog-preset-customizer__field--full{grid-column:1 / -1;}
        .ds-blog-preset-customizer__field span{font-weight:600;color:#1e293b;}
        .ds-blog-preset-customizer__field small{color:#64748b;line-height:1.5;}
        .ds-blog-preset-customizer__field input,
        .ds-blog-preset-customizer__field select,
        .ds-blog-preset-customizer__field textarea{width:100%;}
        .ds-blog-preset-customizer__suffix{color:#64748b;font-size:12px;}
        .ds-blog-preset-customizer__tip{margin:0 0 14px;color:#475569;line-height:1.65;max-width:980px;}
        .ds-blog-preset-customizer__tip code{font-size:12px;}
        </style>';

        echo '<p class="ds-blog-preset-customizer__tip">';
        echo esc_html__( '为“技术开发者 / 极简 / 艺术家”3 套风格设置默认布局、信息显隐和分类页样式；分类单独设置时，以分类设置为准。', 'developer-starter' );
        echo '</p>';

        echo '<div class="ds-blog-preset-customizer">';

        foreach ( $preset_choices as $preset => $label ) {
            echo '<section class="ds-blog-preset-customizer__card">';
            echo '<div class="ds-blog-preset-customizer__head">';
            echo '<h4>' . esc_html( $label ) . '</h4>';
            echo '<p>' . esc_html__( '留空时继续沿用当前风格内置默认值；填写后仅覆盖这个风格本身。', 'developer-starter' ) . '</p>';
            echo '</div>';
            echo '<div class="ds-blog-preset-customizer__groups">';

            foreach ( $schema as $group ) {
                $group_title = isset( $group['title'] ) ? (string) $group['title'] : '';
                $group_desc = isset( $group['description'] ) ? (string) $group['description'] : '';
                $fields = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : array();

                echo '<div class="ds-blog-preset-customizer__group">';
                if ( '' !== $group_title ) {
                    echo '<h5>' . esc_html( $group_title ) . '</h5>';
                }
                if ( '' !== $group_desc ) {
                    echo '<p>' . esc_html( $group_desc ) . '</p>';
                }
                echo '<div class="ds-blog-preset-customizer__grid">';

                foreach ( $fields as $field_key => $field_schema ) {
                    $field_schema = is_array( $field_schema ) ? $field_schema : array();
                    $field_type = isset( $field_schema['type'] ) ? (string) $field_schema['type'] : 'text';
                    $field_label = isset( $field_schema['label'] ) ? (string) $field_schema['label'] : $field_key;
                    $field_desc = isset( $field_schema['description'] ) ? (string) $field_schema['description'] : '';
                    $field_id = \Developer_Starter\Core\Blog_Visual_Manager::get_preset_customization_option_key( $preset, $field_key );
                    $field_name = $this->option_name . '[' . $field_id . ']';
                    $field_value = isset( $options[ $field_id ] ) ? $options[ $field_id ] : '';
                    $field_class = 'ds-blog-preset-customizer__field';
                    if ( 'textarea' === $field_type ) {
                        $field_class .= ' ds-blog-preset-customizer__field--full';
                    }

                    echo '<label class="' . esc_attr( $field_class ) . '" for="' . esc_attr( $field_id ) . '">';
                    echo '<span>' . esc_html( $field_label ) . '</span>';

                    if ( 'select' === $field_type ) {
                        $choices = isset( $field_schema['choices'] ) && is_array( $field_schema['choices'] ) ? $field_schema['choices'] : array();
                        echo '<select id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '">';
                        foreach ( $choices as $choice_value => $choice_label ) {
                            echo '<option value="' . esc_attr( $choice_value ) . '"' . selected( (string) $field_value, (string) $choice_value, false ) . '>' . esc_html( $choice_label ) . '</option>';
                        }
                        echo '</select>';
                    } elseif ( 'number' === $field_type ) {
                        $min = isset( $field_schema['min'] ) ? ' min="' . esc_attr( (string) $field_schema['min'] ) . '"' : '';
                        $max = isset( $field_schema['max'] ) ? ' max="' . esc_attr( (string) $field_schema['max'] ) . '"' : '';
                        $placeholder = isset( $field_schema['placeholder'] ) ? ' placeholder="' . esc_attr( (string) $field_schema['placeholder'] ) . '"' : '';
                        echo '<input type="number" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( (string) $field_value ) . '"' . $min . $max . $placeholder . ' />';
                        if ( ! empty( $field_schema['suffix'] ) ) {
                            echo '<span class="ds-blog-preset-customizer__suffix">' . esc_html( (string) $field_schema['suffix'] ) . '</span>';
                        }
                    } elseif ( 'textarea' === $field_type ) {
                        $rows = isset( $field_schema['rows'] ) ? max( 4, absint( $field_schema['rows'] ) ) : 8;
                        echo '<textarea id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" rows="' . esc_attr( (string) $rows ) . '">' . esc_textarea( (string) $field_value ) . '</textarea>';
                    } else {
                        echo '<input type="text" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( (string) $field_value ) . '" />';
                    }

                    if ( '' !== $field_desc ) {
                        echo '<small>' . esc_html( $field_desc ) . '</small>';
                    }
                    echo '</label>';
                }

                echo '</div>';
                echo '</div>';
            }

            echo '</div>';
            echo '</section>';
        }

        echo '</div>';
        echo '</td></tr>';
    }

    public function render_seo_push_manual_field( $options ) {
        $baidu_enabled = isset( $options['seo_push_baidu_enable'] ) && $options['seo_push_baidu_enable'] === '1';
        $index_enabled = isset( $options['seo_push_indexnow_enable'] ) && $options['seo_push_indexnow_enable'] === '1';
        $google_enabled = isset( $options['seo_push_google_enable'] ) && $options['seo_push_google_enable'] === '1';
        $baidu_nonce = wp_create_nonce( 'ds_baidu_push_custom' );
        $index_nonce = wp_create_nonce( 'ds_indexnow_push_custom' );
        $google_nonce = wp_create_nonce( 'ds_google_indexing_push_custom' );
        $history_nonce = wp_create_nonce( 'ds_seo_push_history' );

        echo '<tr><th scope="row">' . esc_html__( '手动/批量推送', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-seo-push-tools" style="display:grid;gap:16px;max-width:760px;">';

        echo '<section style="border:1px solid #dcdcde;border-radius:8px;padding:14px;background:#fff;">';
        echo '<h4 style="margin:0 0 8px;">' . esc_html__( '手动推送指定 URL', 'developer-starter' ) . '</h4>';
        echo '<textarea id="ds-baidu-push-urls" rows="4" style="width:100%;max-width:720px;" placeholder="https://example.com/a\nhttps://example.com/b"></textarea>';
        echo '<p style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;">';
        echo '<button type="button" class="button" id="ds-baidu-push-custom-btn"' . ( $baidu_enabled ? '' : ' disabled' ) . '>' . esc_html__( '推送到百度', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button" id="ds-indexnow-push-custom-btn"' . ( $index_enabled ? '' : ' disabled' ) . '>' . esc_html__( '推送到 IndexNow / Bing', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button" id="ds-google-push-custom-btn"' . ( $google_enabled ? '' : ' disabled' ) . '>' . esc_html__( '推送到 Google', 'developer-starter' ) . '</button>';
        echo '</p>';
        echo '<p class="description" id="ds-baidu-push-custom-msg">' . esc_html__( '一行一个 URL。百度单次最多 2000 条，IndexNow 单次最多 10000 条，Google 单次最多 200 条。未启用的通道按钮会保持禁用。', 'developer-starter' ) . '</p>';
        echo '</section>';

        echo '<section style="border:1px solid #dcdcde;border-radius:8px;padding:14px;background:#fff;">';
        echo '<h4 style="margin:0 0 8px;">' . esc_html__( '批量推送历史内容 / 失败重试', 'developer-starter' ) . '</h4>';
        echo '<p style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:0 0 10px;">';
        echo '<label>' . esc_html__( '通道', 'developer-starter' ) . ' <select id="ds-seo-push-history-provider">';
        echo '<option value="baidu">' . esc_html__( '百度', 'developer-starter' ) . '</option>';
        echo '<option value="indexnow">' . esc_html__( 'IndexNow / Bing', 'developer-starter' ) . '</option>';
        echo '<option value="google">' . esc_html__( 'Google Indexing', 'developer-starter' ) . '</option>';
        echo '</select></label>';
        echo '<label>' . esc_html__( '内容', 'developer-starter' ) . ' <select id="ds-seo-push-history-post-type">';
        echo '<option value="post">' . esc_html__( '文章', 'developer-starter' ) . '</option>';
        echo '<option value="page">' . esc_html__( '页面', 'developer-starter' ) . '</option>';
        echo '<option value="any">' . esc_html__( '文章 + 页面', 'developer-starter' ) . '</option>';
        echo '</select></label>';
        echo '<label>' . esc_html__( '每批', 'developer-starter' ) . ' <input type="number" id="ds-seo-push-history-limit" class="small-text" min="1" max="50" value="20" /></label>';
        echo '</p>';
        echo '<p style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;">';
        echo '<button type="button" class="button" id="ds-seo-push-history-pending-btn">' . esc_html__( '推送未成功历史内容', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button" id="ds-seo-push-history-failed-btn">' . esc_html__( '只重试失败内容', 'developer-starter' ) . '</button>';
        echo '</p>';
        echo '<p class="description" id="ds-seo-push-history-msg">' . esc_html__( '批量操作会小批量执行并记录每篇文章/页面的通道状态；如果服务器无法连接某个搜索引擎，请关闭该通道或改用手动重试。', 'developer-starter' ) . '</p>';
        echo '</section>';

        echo '</div>';
        echo '<input type="hidden" id="ds-baidu-push-custom-nonce" value="' . esc_attr( $baidu_nonce ) . '" />';
        echo '<input type="hidden" id="ds-indexnow-push-custom-nonce" value="' . esc_attr( $index_nonce ) . '" />';
        echo '<input type="hidden" id="ds-google-push-custom-nonce" value="' . esc_attr( $google_nonce ) . '" />';
        echo '<input type="hidden" id="ds-seo-push-history-nonce" value="' . esc_attr( $history_nonce ) . '" />';
        echo '</td></tr>';
        ?>
        <script>
            (function() {
                var baiduBtn = document.getElementById('ds-baidu-push-custom-btn');
                var indexBtn = document.getElementById('ds-indexnow-push-custom-btn');
                var googleBtn = document.getElementById('ds-google-push-custom-btn');
                var textarea = document.getElementById('ds-baidu-push-urls');
                var msg = document.getElementById('ds-baidu-push-custom-msg');
                var baiduNonce = document.getElementById('ds-baidu-push-custom-nonce');
                var indexNonce = document.getElementById('ds-indexnow-push-custom-nonce');
                var googleNonce = document.getElementById('ds-google-push-custom-nonce');
                var historyNonce = document.getElementById('ds-seo-push-history-nonce');
                var historyProvider = document.getElementById('ds-seo-push-history-provider');
                var historyPostType = document.getElementById('ds-seo-push-history-post-type');
                var historyLimit = document.getElementById('ds-seo-push-history-limit');
                var historyPendingBtn = document.getElementById('ds-seo-push-history-pending-btn');
                var historyFailedBtn = document.getElementById('ds-seo-push-history-failed-btn');
                var historyMsg = document.getElementById('ds-seo-push-history-msg');
                if (!textarea || typeof ajaxurl === 'undefined') return;

                function doPush(action, nonceEl, button) {
                    if (!textarea.value.trim()) {
                        if (msg) msg.textContent = '请先输入要推送的URL';
                        return;
                    }
                    if (button) button.disabled = true;
                    if (msg) msg.textContent = '正在推送...';
                    var data = new FormData();
                    data.append('action', action);
                    data.append('nonce', nonceEl ? nonceEl.value : '');
                    data.append('urls', textarea.value);
                    fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
                        .then(function(res) { return res.json(); })
                        .then(function(res) {
                            if (msg) {
                                msg.textContent = res && res.data && res.data.message ? res.data.message : (res && res.success ? '推送成功' : '推送失败');
                            }
                            if (res && res.success) {
                                textarea.value = '';
                            }
                        })
                        .catch(function() {
                            if (msg) msg.textContent = '推送失败，请稍后再试';
                        })
                        .finally(function() {
                            if (button) button.disabled = false;
                        });
                }

                function doHistory(mode, button) {
                    if (button) button.disabled = true;
                    if (historyMsg) historyMsg.textContent = mode === 'failed' ? '正在重试失败内容...' : '正在推送历史内容...';
                    var data = new FormData();
                    data.append('action', 'ds_seo_push_history');
                    data.append('nonce', historyNonce ? historyNonce.value : '');
                    data.append('provider', historyProvider ? historyProvider.value : 'baidu');
                    data.append('post_type', historyPostType ? historyPostType.value : 'post');
                    data.append('limit', historyLimit ? historyLimit.value : '20');
                    data.append('mode', mode);
                    fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
                        .then(function(res) { return res.json(); })
                        .then(function(res) {
                            if (historyMsg) {
                                historyMsg.textContent = res && res.data && res.data.message ? res.data.message : (res && res.success ? '批量推送完成' : '批量推送失败');
                            }
                        })
                        .catch(function() {
                            if (historyMsg) historyMsg.textContent = '批量推送失败，请稍后再试';
                        })
                        .finally(function() {
                            if (button) button.disabled = false;
                        });
                }

                if (baiduBtn) {
                    baiduBtn.addEventListener('click', function() {
                        doPush('ds_baidu_push_custom', baiduNonce, baiduBtn);
                    });
                }
                if (indexBtn) {
                    indexBtn.addEventListener('click', function() {
                        doPush('ds_indexnow_push_custom', indexNonce, indexBtn);
                    });
                }
                if (googleBtn) {
                    googleBtn.addEventListener('click', function() {
                        doPush('ds_google_indexing_push_custom', googleNonce, googleBtn);
                    });
                }
                if (historyPendingBtn) {
                    historyPendingBtn.addEventListener('click', function() {
                        doHistory('pending', historyPendingBtn);
                    });
                }
                if (historyFailedBtn) {
                    historyFailedBtn.addEventListener('click', function() {
                        doHistory('failed', historyFailedBtn);
                    });
                }
            })();
        </script>
        <?php
    }
}
