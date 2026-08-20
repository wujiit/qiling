<?php
/**
 * Admin Settings Admin Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Admin_Trait {

    public function handle_create_theme_table() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '权限不足', 'developer-starter' ) );
        }

        check_admin_referer( 'ds_create_theme_table' );

        $table_key = isset( $_POST['table_key'] ) ? sanitize_key( wp_unslash( $_POST['table_key'] ) ) : '';

        switch ( $table_key ) {
            case 'messages':
                if ( class_exists( 'Developer_Starter\\Core\\Message_Manager' ) ) {
                    $manager = new \Developer_Starter\Core\Message_Manager();
                    $manager->create_table();
                }
                break;
            case 'careers_positions':
            case 'careers_applications':
                if ( class_exists( 'Developer_Starter\\Core\\Careers_Manager' ) ) {
                    $manager = new \Developer_Starter\Core\Careers_Manager();
                    $manager->create_tables();
                }
                break;
            case 'id_verifications':
                if ( class_exists( 'Developer_Starter\\Core\\ID_Verification_Manager' ) ) {
                    \Developer_Starter\Core\ID_Verification_Manager::create_table();
                }
                break;
            case 'account_deletion_requests':
                if ( class_exists( 'Developer_Starter\\Core\\Account_Deletion_Manager' ) ) {
                    \Developer_Starter\Core\Account_Deletion_Manager::create_table();
                }
                break;
            case 'post_interactions':
                if ( class_exists( 'Developer_Starter\\Core\\Post_Interaction_Manager' ) ) {
                    \Developer_Starter\Core\Post_Interaction_Manager::create_table();
                }
                break;
        }

        $redirect = wp_get_referer();
        if ( ! $redirect ) {
            $redirect = admin_url( 'admin.php?page=developer-starter-settings&tab=documentation' );
        }
        $redirect = add_query_arg( 'ds_table_created', '1', $redirect );
        wp_safe_redirect( $redirect );
        exit;
    }

    public function enqueue_admin_scripts( $hook ) {
        if ( strpos( $hook, 'developer-starter' ) === false ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        $design_preset_snapshot_js     = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/js/admin-design-preset-snapshot.js';
        $design_preset_snapshot_js_ver = file_exists( $design_preset_snapshot_js ) ? (string) filemtime( $design_preset_snapshot_js ) : DEVELOPER_STARTER_VERSION;

        wp_enqueue_script(
            'developer-starter-admin-design-preset-snapshot',
            DEVELOPER_STARTER_ASSETS . '/js/admin-design-preset-snapshot.js',
            array(),
            $design_preset_snapshot_js_ver,
            false
        );

        add_action( 'admin_footer', array( $this, 'admin_footer_js' ) );
    }

    public function admin_footer_js() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            var dsAjaxUrl = typeof window.ajaxurl !== 'undefined' ? window.ajaxurl : '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
            var dsFavoriteNonce = <?php echo wp_json_encode( wp_create_nonce( "favorite_setting_nonce" ) ); ?>;
            $('.ds-color-picker').wpColorPicker();
            
            // 优化颜色选择器体验：利用事件捕获阶段拦截 focus 和 click 事件
            // 这样浏览器原生仍然能让输入框获得焦点并输入，但 wpColorPicker 绑定的弹出事件不会被触发
            document.addEventListener('focus', function(e) {
                if (e.target && e.target.classList && e.target.classList.contains('wp-color-picker')) {
                    e.stopPropagation();
                }
            }, true);
            
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList && e.target.classList.contains('wp-color-picker')) {
                    e.stopPropagation();
                }
            }, true);

            $('.ds-upload-image-btn').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var input = button.siblings('.ds-image-url');
                var preview = button.siblings('.ds-image-preview');

                var frame = wp.media({ title: '<?php echo esc_js( __( '选择图片', 'developer-starter' ) ); ?>', multiple: false });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var selectedUrl = attachment.url;
                    if (attachment && attachment.sizes && attachment.sizes.full && attachment.sizes.full.url) {
                        selectedUrl = attachment.sizes.full.url;
                    }
                    input.val(selectedUrl);
                    if (preview.length) {
                        preview.attr('src', selectedUrl).show();
                    } else {
                        button.after('<img src="' + selectedUrl + '" class="ds-image-preview" style="display:block;max-width:200px;margin-top:10px;"/>');
                    }
                });
                frame.open();
            });

            $('.ds-remove-image-btn').on('click', function(e) {
                e.preventDefault();
                $(this).siblings('.ds-image-url').val('');
                $(this).siblings('.ds-image-preview').attr('src', '').hide();
            });

            $(document).on('click', '.ds-upload-file-btn', function(e) {
                e.preventDefault();
                var button = $(this);
                var input = button.siblings('.ds-file-url');
                var title = button.data('title') || '<?php echo esc_js( __( '选择文件', 'developer-starter' ) ); ?>';

                var frame = wp.media({ title: title, multiple: false });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    if (attachment && attachment.url) {
                        input.val(attachment.url).trigger('change');
                    }
                });
                frame.open();
            });

            $(document).on('click', '.ds-remove-file-btn', function(e) {
                e.preventDefault();
                $(this).siblings('.ds-file-url').val('').trigger('change');
            });

            $(document).on('click', '.ds-repeater-add', function() {
                var $wrap = $(this).closest('.ds-repeater-wrap');
                var $list = $wrap.find('.ds-repeater-list');
                var $tpl = $wrap.find('.ds-repeater-tpl');
                var tpl = $tpl.attr('data-template');
                var idx = $list.children().length;
                tpl = tpl.replace(/__IDX__/g, idx);
                $list.append(tpl);
            });

            $(document).on('click', '.ds-repeater-remove', function(e) {
                e.preventDefault();
                $(this).closest('.ds-repeater-item').remove();
            });

            // 一键刷新版本号
            $('#refresh-assets-version').on('click', function() {
                var button = $(this);
                var result = $('#refresh-version-result');
                var versionInput = $('input[name="developer_starter_options[assets_version]"]');

                button.prop('disabled', true).text('<?php echo esc_js( __( '刷新中...', 'developer-starter' ) ); ?>');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_refresh_version',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "refresh_version_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success) {
                            versionInput.val(response.data.version);
                            result.css('color', '#10b981').text('✓ ' + '<?php echo esc_js( __( '版本号已更新为: ', 'developer-starter' ) ); ?>' + response.data.version);
                            setTimeout(function() { result.text(''); }, 5000);
                        } else {
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '刷新失败: ', 'developer-starter' ) ); ?>' + (response && response.data ? response.data : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>'));
                        }
                        button.prop('disabled', false).text('<?php echo esc_js( __( '一键刷新版本号', 'developer-starter' ) ); ?>');
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', status, error, xhr.responseText);
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败: ', 'developer-starter' ) ); ?>' + error);
                        button.prop('disabled', false).text('<?php echo esc_js( __( '一键刷新版本号', 'developer-starter' ) ); ?>');
                    }
                });
            });

            // SMTP 测试邮件
            $('#send-smtp-test-email').on('click', function() {
                var button = $(this);
                var result = $('#smtp-test-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '发送中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在发送测试邮件...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_send_smtp_test_email',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "smtp_test_email_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success && response.data) {
                            result.css('color', '#10b981').text('✓ ' + response.data.message);
                        } else {
                            var msg = response && response.data && response.data.message
                                ? response.data.message
                                : '<?php echo esc_js( __( '发送失败，请检查 SMTP 配置。', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text(msg);
                        }
                        button.prop('disabled', false).text('<?php echo esc_js( __( '发送测试邮件', 'developer-starter' ) ); ?>');
                    },
                    error: function(xhr, status, error) {
                        var msg = '<?php echo esc_js( __( '请求失败，请稍后重试', 'developer-starter' ) ); ?>';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            msg = xhr.responseJSON.data.message;
                        } else if (error) {
                            msg = msg + ' (' + error + ')';
                        } else if (status) {
                            msg = msg + ' (' + status + ')';
                        }
                        result.css('color', '#ef4444').text(msg);
                        button.prop('disabled', false).text('<?php echo esc_js( __( '发送测试邮件', 'developer-starter' ) ); ?>');
                    }
                });
            });

            $('#ds-ai-save-settings').on('click', function() {
                var $form = $(this).closest('form');
                if ($form.length) {
                    $form.trigger('submit');
                }
            });

            $(document).on('click', '.ds-ai-connection-test', function() {
                var button = $(this);
                var card = button.closest('.ds-ai-connection-card');
                var result = card.find('.ds-ai-connection-test-result');
                var spinner = card.find('.ds-ai-connection-test-spinner');
                var originalText = button.text();

                var payload = {
                    action: 'developer_starter_test_ai_connection',
                    nonce: <?php echo wp_json_encode( wp_create_nonce( "ai_connection_test_nonce" ) ); ?>,
                    connection_id: $.trim(card.find('.ds-ai-connection-input-id').val() || ''),
                    stored_connection_id: $.trim(card.find('.ds-ai-connection-stored-id').val() || ''),
                    connection_name: $.trim(card.find('.ds-ai-connection-input-name').val() || ''),
                    endpoint: $.trim(card.find('.ds-ai-connection-input-endpoint').val() || ''),
                    model: $.trim(card.find('.ds-ai-connection-input-model').val() || ''),
                    api_key: $.trim(card.find('.ds-ai-connection-input-api-key').val() || ''),
                    api_key_existing: card.find('.ds-ai-connection-api-key-existing').length ? '1' : '',
                    json_mode: card.find('.ds-ai-connection-input-json-mode').is(':checked') ? '1' : ''
                };

                button.prop('disabled', true).text('<?php echo esc_js( __( '测试中...', 'developer-starter' ) ); ?>');
                spinner.addClass('is-active');
                result.text('<?php echo esc_js( __( '正在测试连通性...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function(response) {
                        if (response && response.success && response.data) {
                            result.css('color', '#10b981').text('✓ ' + response.data.message);
                        } else {
                            var msg = response && response.data && response.data.message
                                ? response.data.message
                                : '<?php echo esc_js( __( '测试失败，请检查接口地址、模型和 API Key。', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text(msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        var msg = '<?php echo esc_js( __( '请求失败，请稍后重试', 'developer-starter' ) ); ?>';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            msg = xhr.responseJSON.data.message;
                        } else if (error) {
                            msg = msg + ' (' + error + ')';
                        } else if (status) {
                            msg = msg + ' (' + status + ')';
                        }
                        result.css('color', '#ef4444').text(msg);
                    },
                    complete: function() {
                        spinner.removeClass('is-active');
                        button.prop('disabled', false).text(originalText);
                    }
                });
            });

            // 一键数据库清理
            $('#run-db-cleanup').on('click', function() {
                if (!confirm('<?php echo esc_js( __( '确定要清理数据库吗？此操作不可逆，请确保已备份数据库！', 'developer-starter' ) ); ?>')) {
                    return;
                }

                var button = $(this);
                var result = $('#db-cleanup-result');

                // 收集选中的清理项
                var cleanItems = [];
                $('input[name^="db_clean_"]:checked').each(function() {
                    cleanItems.push($(this).attr('name').replace('db_clean_', ''));
                });

                if (cleanItems.length === 0) {
                    result.css('color', '#f59e0b').text('<?php echo esc_js( __( '请至少选择一个清理项', 'developer-starter' ) ); ?>');
                    return;
                }

                button.prop('disabled', true).text('<?php echo esc_js( __( '清理中...', 'developer-starter' ) ); ?>');
                result.css('color', '#64748b').text('<?php echo esc_js( __( '正在清理数据库（分批执行）...', 'developer-starter' ) ); ?>');

                var totalDeleted = 0;
                var cleanupBatchSize = 200;

                function runCleanupBatch(cursor) {
                    $.ajax({
                        url: dsAjaxUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'developer_starter_db_cleanup',
                            nonce: <?php echo wp_json_encode( wp_create_nonce( "db_cleanup_nonce" ) ); ?>,
                            items: cleanItems,
                            cursor: cursor,
                            batch_size: cleanupBatchSize
                        },
                        success: function(response) {
                            if (!response || !response.success || !response.data) {
                                var failMsg = response && response.data && response.data.message
                                    ? response.data.message
                                    : '<?php echo esc_js( __( '清理失败', 'developer-starter' ) ); ?>';
                                result.css('color', '#ef4444').text('<?php echo esc_js( __( '清理失败: ', 'developer-starter' ) ); ?>' + failMsg);
                                button.prop('disabled', false).text('🧹 <?php echo esc_js( __( '一键清理数据库', 'developer-starter' ) ); ?>');
                                return;
                            }

                            var data = response.data;
                            var deleted = parseInt(data.deleted || 0, 10);
                            if (!isNaN(deleted) && deleted > 0) {
                                totalDeleted += deleted;
                            }

                            result.css('color', '#64748b').text(data.message || '<?php echo esc_js( __( '正在清理中...', 'developer-starter' ) ); ?>');

                            if (data.has_more) {
                                runCleanupBatch(parseInt(data.next_cursor || cursor, 10));
                                return;
                            }

                            if (totalDeleted > 0) {
                                result.css('color', '#10b981').text('✓ <?php echo esc_js( __( '清理完成，本次共清理', 'developer-starter' ) ); ?> ' + totalDeleted + ' <?php echo esc_js( __( '条数据', 'developer-starter' ) ); ?>');
                            } else {
                                result.css('color', '#10b981').text('✓ ' + (data.message || '<?php echo esc_js( __( '数据库已经很干净，没有需要清理的数据', 'developer-starter' ) ); ?>'));
                            }

                            loadDbStats();
                            button.prop('disabled', false).text('🧹 <?php echo esc_js( __( '一键清理数据库', 'developer-starter' ) ); ?>');
                        },
                        error: function() {
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>');
                            button.prop('disabled', false).text('🧹 <?php echo esc_js( __( '一键清理数据库', 'developer-starter' ) ); ?>');
                        }
                    });
                }

                runCleanupBatch(0);
            });

            // 数据库统计加载
            function loadDbStats() {
                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_db_stats',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "db_stats_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            var stats = response.data;
                            updateStatDisplay('#stat-revisions', stats.revisions);
                            updateStatDisplay('#stat-drafts', stats.drafts);
                            updateStatDisplay('#stat-trash', stats.trash);
                            updateStatDisplay('#stat-spam', stats.spam);
                            updateStatDisplay('#stat-orphan-postmeta', stats.orphan_postmeta);
                            updateStatDisplay('#stat-orphan-commentmeta', stats.orphan_commentmeta);
                            updateStatDisplay('#stat-orphan-relationships', stats.orphan_relationships);
                            updateStatDisplay('#stat-pingbacks', stats.pingbacks);
                            updateStatDisplay('#stat-unused-tags', stats.unused_tags);
                            updateStatDisplay('#stat-transients', stats.transients);
                            updateStatDisplay('#stat-post-views', stats.post_views);
                            updateStatDisplay('#stat-package-pages', stats.package_pages);
                            updateStatDisplay('#stat-package-trash-pages', stats.package_trash_pages);
                        }
                    }
                });
            }

            function updateStatDisplay(selector, count) {
                var $el = $(selector);
                $el.text(count + ' <?php echo esc_js( __( '条', 'developer-starter' ) ); ?>');
                if (count > 0) {
                    $el.removeClass('ds-stat-ok').addClass('ds-stat-warn');
                } else {
                    $el.removeClass('ds-stat-warn').addClass('ds-stat-ok');
                }
            }

            // 页面加载时获取统计
            if ($('#db-stats-container').length) {
                loadDbStats();
            }

            $('#clear-cleanup-rest-audit-log').on('click', function() {
                if (!confirm('<?php echo esc_js( __( '确定要清空清理审计日志吗？此操作不可逆。', 'developer-starter' ) ); ?>')) {
                    return;
                }

                var button = $(this);
                var result = $('#cleanup-rest-audit-log-result');
                var list = $('#cleanup-rest-audit-log-list');

                button.prop('disabled', true).text('<?php echo esc_js( __( '清空中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在清空日志...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_clear_cleanup_rest_audit_log',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "cleanup_rest_audit_log_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success) {
                            list.html('<p class="description" style="margin:0 0 8px;"><?php echo esc_js( __( '暂无清理日志。', 'developer-starter' ) ); ?></p>');
                            result.css('color', '#10b981').text('✓ ' + (response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '清理日志已清空。', 'developer-starter' ) ); ?>'));
                        } else {
                            var msg = response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '清空失败: ', 'developer-starter' ) ); ?>' + msg);
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : '<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>';
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '执行失败: ', 'developer-starter' ) ); ?>' + msg);
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php echo esc_js( __( '清空清理日志', 'developer-starter' ) ); ?>');
                    }
                });
            });

            $('#run-theme-scheduled-cleanup').on('click', function() {
                var button = $(this);
                var result = $('#theme-scheduled-cleanup-result');
                var scope = button.data('scope') || 'auto';

                button.prop('disabled', true).text('<?php echo esc_js( __( '执行中...', 'developer-starter' ) ); ?>');
                result.css('color', '#64748b').text('<?php echo esc_js( __( '正在执行主题清理...', 'developer-starter' ) ); ?>');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_run_theme_cleanup',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "theme_cleanup_nonce" ) ); ?>,
                        scope: scope
                    },
                    success: function(response) {
                        if (response && response.success) {
                            result.css('color', '#10b981').text('✓ ' + (response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '清理完成。', 'developer-starter' ) ); ?>'));
                        } else {
                            var msg = response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '执行失败: ', 'developer-starter' ) ); ?>' + msg);
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : '<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>';
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '执行失败: ', 'developer-starter' ) ); ?>' + msg);
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php echo esc_js( __( '立即执行主题清理', 'developer-starter' ) ); ?>');
                    }
                });
            });

            $('#regenerate-cleanup-cron-token').on('click', function() {
                if (!confirm('<?php echo esc_js( __( '重新生成后，旧的外部定时任务 URL 会失效。确定继续吗？', 'developer-starter' ) ); ?>')) {
                    return;
                }

                var button = $(this);
                var result = $('#cleanup-cron-token-result');
                var token = $('#cleanup-cron-token');
                var url = $('#cleanup-cron-url');
                var headerUrl = $('#cleanup-cron-header-url');
                var header = $('#cleanup-cron-header');
                var headerCurl = $('#cleanup-cron-header-curl');

                button.prop('disabled', true).text('<?php echo esc_js( __( '生成中...', 'developer-starter' ) ); ?>');
                result.css('color', '#64748b').text('<?php echo esc_js( __( '正在生成...', 'developer-starter' ) ); ?>');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_regenerate_cleanup_cron_token',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "cleanup_cron_token_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success && response.data) {
                            if (response.data.token) {
                                token.text(response.data.token);
                            }
                            if (response.data.url) {
                                url.text(response.data.url);
                            }
                            if (response.data.header_url) {
                                headerUrl.text(response.data.header_url);
                            }
                            if (response.data.header) {
                                header.text(response.data.header);
                            }
                            if (response.data.header_curl) {
                                headerCurl.text(response.data.header_curl);
                            }
                            result.css('color', '#10b981').text('✓ ' + (response.data.message || '<?php echo esc_js( __( '已重新生成。', 'developer-starter' ) ); ?>'));
                        } else {
                            var msg = response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '生成失败: ', 'developer-starter' ) ); ?>' + msg);
                        }
                    },
                    error: function() {
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php echo esc_js( __( '重新生成', 'developer-starter' ) ); ?>');
                    }
                });
            });

            // 刷新统计按钮
            $('#refresh-db-stats').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('<?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?>');
                $('#db-stats-grid .db-stat-item span:last-child')
                    .text('<?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?>')
                    .removeClass('ds-stat-ok ds-stat-warn');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_db_stats',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "db_stats_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            var stats = response.data;
                            updateStatDisplay('#stat-revisions', stats.revisions);
                            updateStatDisplay('#stat-drafts', stats.drafts);
                            updateStatDisplay('#stat-trash', stats.trash);
                            updateStatDisplay('#stat-spam', stats.spam);
                            updateStatDisplay('#stat-orphan-postmeta', stats.orphan_postmeta);
                            updateStatDisplay('#stat-orphan-commentmeta', stats.orphan_commentmeta);
                            updateStatDisplay('#stat-orphan-relationships', stats.orphan_relationships);
                            updateStatDisplay('#stat-pingbacks', stats.pingbacks);
                            updateStatDisplay('#stat-unused-tags', stats.unused_tags);
                            updateStatDisplay('#stat-transients', stats.transients);
                            updateStatDisplay('#stat-post-views', stats.post_views);
                            updateStatDisplay('#stat-package-pages', stats.package_pages);
                            updateStatDisplay('#stat-package-trash-pages', stats.package_trash_pages);
                        }
                        button.prop('disabled', false).text('🔄 <?php echo esc_js( __( '刷新统计', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        button.prop('disabled', false).text('🔄 <?php echo esc_js( __( '刷新统计', 'developer-starter' ) ); ?>');
                    }
                });
            });

            function applyPosterCacheStats(stats) {
                if (!stats) {
                    return;
                }

                var count = parseInt(stats.count, 10);
                var bytes = parseInt(stats.bytes, 10);
                var sizeText = stats.size_human || '0 B';

                count = isNaN(count) ? 0 : count;
                bytes = isNaN(bytes) ? 0 : bytes;

                var countEl = $('#poster-cache-count');
                var sizeEl = $('#poster-cache-size');
                var dirEl = $('#poster-cache-dir');

                countEl.text(count + ' <?php echo esc_js( __( '张', 'developer-starter' ) ); ?>');
                sizeEl.text(sizeText);
                dirEl.text(stats.dir || '--');

                if (count > 0) {
                    countEl.removeClass('ds-stat-ok').addClass('ds-stat-warn');
                } else {
                    countEl.removeClass('ds-stat-warn').addClass('ds-stat-ok');
                }

                if (bytes > 0) {
                    sizeEl.removeClass('ds-stat-ok').addClass('ds-stat-warn');
                } else {
                    sizeEl.removeClass('ds-stat-warn').addClass('ds-stat-ok');
                }
            }

            function loadPosterCacheStats() {
                return $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_poster_cache_stats',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "poster_cache_stats_nonce" ) ); ?>
                    }
                }).done(function(response) {
                    if (response && response.success) {
                        applyPosterCacheStats(response.data);
                    }
                });
            }

            if ($('#poster-cache-stats-container').length) {
                loadPosterCacheStats();
            }

            $('#refresh-poster-cache-stats').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('<?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?>');
                $('#poster-cache-count, #poster-cache-size')
                    .text('<?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?>')
                    .removeClass('ds-stat-ok ds-stat-warn');

                loadPosterCacheStats().always(function() {
                    button.prop('disabled', false).text('🔄 <?php echo esc_js( __( '刷新统计', 'developer-starter' ) ); ?>');
                });
            });

            $('#clear-poster-cache').on('click', function() {
                if (!confirm('<?php echo esc_js( __( '确定要清理文章海报缓存吗？此操作只会删除 uploads/qiling-posters 下的海报 PNG 缓存。', 'developer-starter' ) ); ?>')) {
                    return;
                }

                var button = $(this);
                var result = $('#clear-poster-cache-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '清理中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在清理缓存...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_clear_poster_cache',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "clear_poster_cache_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success) {
                            var failed = response.data && response.data.failed_files ? parseInt(response.data.failed_files, 10) : 0;
                            result.css('color', failed > 0 ? '#f59e0b' : '#10b981').text(response.data.message || '<?php echo esc_js( __( '清理完成', 'developer-starter' ) ); ?>');
                            if (response.data && response.data.stats) {
                                applyPosterCacheStats(response.data.stats);
                            } else {
                                loadPosterCacheStats();
                            }
                        } else {
                            var msg = response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '清理失败: ', 'developer-starter' ) ); ?>' + msg);
                        }
                        button.prop('disabled', false).text('<?php echo esc_js( __( '清理海报缓存', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>');
                        button.prop('disabled', false).text('<?php echo esc_js( __( '清理海报缓存', 'developer-starter' ) ); ?>');
                    }
                });
            });

            function applyThumbnailCacheStats(stats) {
                stats = stats || {};
                $('#thumbnail-cache-count').text((parseInt(stats.count || 0, 10) || 0).toLocaleString() + ' <?php echo esc_js( __( '张', 'developer-starter' ) ); ?>');
                $('#thumbnail-cache-size').text(stats.size_human || '0 B');
                $('#thumbnail-cache-dir').text(stats.dir || '');
            }

            function loadThumbnailCacheStats() {
                return $.post(dsAjaxUrl, {
                    action: 'developer_starter_thumbnail_cache_stats',
                    nonce: <?php echo wp_json_encode( wp_create_nonce( 'thumbnail_cache_nonce' ) ); ?>
                }).done(function(response) {
                    if (response && response.success) {
                        applyThumbnailCacheStats(response.data);
                    }
                });
            }

            function runThumbnailCacheAction(action, confirmText, workingText) {
                if (!confirm(confirmText)) {
                    return;
                }
                var button = action === 'developer_starter_clear_thumbnail_cache' ? $('#clear-thumbnail-cache') : $('#clear-unused-thumbnail-cache');
                var result = $('#thumbnail-cache-result');
                button.prop('disabled', true).text(workingText);
                result.css('color', '#64748b').text('<?php echo esc_js( __( '正在处理，请稍候...', 'developer-starter' ) ); ?>');
                $.post(dsAjaxUrl, {
                    action: action,
                    nonce: <?php echo wp_json_encode( wp_create_nonce( 'thumbnail_cache_nonce' ) ); ?>
                }).done(function(response) {
                    if (response && response.success) {
                        result.css('color', '#10b981').text(response.data.message || '<?php echo esc_js( __( '操作完成', 'developer-starter' ) ); ?>');
                        applyThumbnailCacheStats(response.data.stats);
                    } else {
                        result.css('color', '#ef4444').text((response && response.data && response.data.message) || '<?php echo esc_js( __( '操作失败', 'developer-starter' ) ); ?>');
                    }
                }).fail(function() {
                    result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>');
                }).always(function() {
                    button.prop('disabled', false).text(action === 'developer_starter_clear_thumbnail_cache' ? '<?php echo esc_js( __( '清空全部缓存', 'developer-starter' ) ); ?>' : '<?php echo esc_js( __( '扫描并清理未使用缓存', 'developer-starter' ) ); ?>');
                });
            }

            if ($('#thumbnail-cache-manager').length) {
                loadThumbnailCacheStats();
            }
            $('#refresh-thumbnail-cache-stats').on('click', loadThumbnailCacheStats);
            $('#clear-thumbnail-cache').on('click', function() {
                runThumbnailCacheAction('developer_starter_clear_thumbnail_cache', '<?php echo esc_js( __( '确定清空全部缩略图缓存吗？前台访问时会重新生成。', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '清空中...', 'developer-starter' ) ); ?>');
            });
            $('#clear-unused-thumbnail-cache').on('click', function() {
                runThumbnailCacheAction('developer_starter_clear_unused_thumbnail_cache', '<?php echo esc_js( __( '确定扫描并删除未使用的缩略图缓存吗？', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '扫描中...', 'developer-starter' ) ); ?>');
            });

            function applyGitHubActivityCacheStats(stats) {
                stats = stats || {};
                var count = parseInt(stats.count || 0, 10) || 0;
                var bytes = parseInt(stats.bytes || 0, 10) || 0;
                var sizeText = stats.size_human || '0 B';
                var countEl = $('#github-activity-cache-count');
                var sizeEl = $('#github-activity-cache-size');
                var dirEl = $('#github-activity-cache-dir');

                countEl.text(count + ' <?php echo esc_js( __( '个', 'developer-starter' ) ); ?>');
                sizeEl.text(sizeText);
                dirEl.text(stats.dir || '--');

                if (count > 0) {
                    countEl.removeClass('ds-stat-ok').addClass('ds-stat-warn');
                } else {
                    countEl.removeClass('ds-stat-warn').addClass('ds-stat-ok');
                }

                if (bytes > 0) {
                    sizeEl.removeClass('ds-stat-ok').addClass('ds-stat-warn');
                } else {
                    sizeEl.removeClass('ds-stat-warn').addClass('ds-stat-ok');
                }
            }

            function loadGitHubActivityCacheStats() {
                return $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_github_activity_cache_stats',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "github_activity_cache_stats_nonce" ) ); ?>
                    }
                }).done(function(response) {
                    if (response && response.success) {
                        applyGitHubActivityCacheStats(response.data);
                    }
                });
            }

            if ($('#github-activity-cache-stats-container').length) {
                loadGitHubActivityCacheStats();
            }

            $('#refresh-github-activity-cache-stats').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('<?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?>');
                $('#github-activity-cache-count, #github-activity-cache-size')
                    .text('<?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?>')
                    .removeClass('ds-stat-ok ds-stat-warn');

                loadGitHubActivityCacheStats().always(function() {
                    button.prop('disabled', false).text('🔄 <?php echo esc_js( __( '刷新统计', 'developer-starter' ) ); ?>');
                });
            });

            $('#clear-github-activity-cache').on('click', function() {
                if (!confirm('<?php echo esc_js( __( '确定要清理 GitHub 项目动态缓存吗？此操作只会删除 uploads/qiling/github-activity 下的 JSON 缓存文件。', 'developer-starter' ) ); ?>')) {
                    return;
                }

                var button = $(this);
                var result = $('#clear-github-activity-cache-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '清理中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在清理缓存...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_clear_github_activity_cache',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "clear_github_activity_cache_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success) {
                            var failed = response.data && response.data.failed_files ? parseInt(response.data.failed_files, 10) : 0;
                            result.css('color', failed > 0 ? '#f59e0b' : '#10b981').text(response.data.message || '<?php echo esc_js( __( '清理完成', 'developer-starter' ) ); ?>');
                            if (response.data && response.data.stats) {
                                applyGitHubActivityCacheStats(response.data.stats);
                            } else {
                                loadGitHubActivityCacheStats();
                            }
                        } else {
                            var msg = response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '清理失败: ', 'developer-starter' ) ); ?>' + msg);
                        }
                        button.prop('disabled', false).text('<?php echo esc_js( __( '清理 GitHub 缓存', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>');
                        button.prop('disabled', false).text('<?php echo esc_js( __( '清理 GitHub 缓存', 'developer-starter' ) ); ?>');
                    }
                });
            });

            $('#clear-ip-cache').on('click', function() {
                if (!confirm('<?php echo esc_js( __( '确定要清理 IP 缓存吗？此操作只会删除主题 IP 归属地临时缓存文件。', 'developer-starter' ) ); ?>')) {
                    return;
                }

                var button = $(this);
                var result = $('#clear-ip-cache-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '清理中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在清理缓存...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_clear_ip_cache',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "clear_ip_cache_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success) {
                            result.css('color', '#10b981').text('✓ ' + response.data.message);
                        } else {
                            var msg = response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '清理失败: ', 'developer-starter' ) ); ?>' + msg);
                        }
                        button.prop('disabled', false).text('<?php echo esc_js( __( '清理 IP 接口缓存', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>');
                        button.prop('disabled', false).text('<?php echo esc_js( __( '清理 IP 接口缓存', 'developer-starter' ) ); ?>');
                    }
                });
            });

            $('#reset-ip-usermeta').on('click', function() {
                if (!confirm('<?php echo esc_js( __( '确定要重置用户 IP 归属地数据吗？\n\n此操作不会删除用户账号，只会清空已保存的旧 IP 归属地，用户下次访问时重新获取并写入数据库。', 'developer-starter' ) ); ?>')) {
                    return;
                }

                var button = $(this);
                var result = $('#reset-ip-usermeta-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '重置中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在重置数据库中的遗留 IP 数据...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_reset_ip_usermeta',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "reset_ip_usermeta_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success) {
                            result.css('color', '#10b981').text('✓ ' + response.data.message);
                        } else {
                            var msg = response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.css('color', '#ef4444').text('<?php echo esc_js( __( '重置失败: ', 'developer-starter' ) ); ?>' + msg);
                        }
                        button.prop('disabled', false).text('<?php echo esc_js( __( '重置用户 IP 归属地数据', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        result.css('color', '#ef4444').text('<?php echo esc_js( __( '请求失败', 'developer-starter' ) ); ?>');
                        button.prop('disabled', false).text('<?php echo esc_js( __( '重置用户 IP 归属地数据', 'developer-starter' ) ); ?>');
                    }
                });
            });

            // 一键生成压缩CSS
            $('#generate-min-css').on('click', function() {
                var button = $(this);
                var result = $('#generate-css-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '生成中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在处理...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_generate_css',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "generate_css_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            result.html(response.data.message).css('color', '#10b981');
                        } else {
                            result.text('<?php echo esc_js( __( '失败: ', 'developer-starter' ) ); ?>' + (response.data ? response.data.message : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>')).css('color', '#ef4444');
                        }
                        button.prop('disabled', false).text('⚡ <?php echo esc_js( __( '立即生成压缩文件 (.min.css)', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        result.text('<?php echo esc_js( __( '请求失败，请稍后重试', 'developer-starter' ) ); ?>').css('color', '#ef4444');
                        button.prop('disabled', false).text('⚡ <?php echo esc_js( __( '立即生成压缩文件 (.min.css)', 'developer-starter' ) ); ?>');
                    }
                });
            });

            // 拆分 CSS 完整性检查
            $('#check-split-css-integrity').on('click', function() {
                var button = $(this);
                var result = $('#check-split-css-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '检测中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在校验拆分文件...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_check_split_css_integrity',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "check_split_css_integrity_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success && response.data) {
                            var data = response.data;
                            var color = '#10b981';
                            var prefix = '✓ ';
                            if (data.overall === 'warning') {
                                color = '#f59e0b';
                                prefix = '⚠ ';
                            } else if (data.overall === 'error') {
                                color = '#ef4444';
                                prefix = '✕ ';
                            }

                            var summary = data.summary ? String(data.summary) : '';
                            var html = '<div>' + prefix + $('<div/>').text(summary).html() + '</div>';
                            if (Array.isArray(data.items) && data.items.length) {
                                html += '<ul style="margin:6px 0 0 18px;">';
                                data.items.forEach(function(item) {
                                    var level = item && item.level ? String(item.level) : 'ok';
                                    var text = item && item.text ? String(item.text) : '';
                                    var icon = '✓ ';
                                    if (level === 'warn') {
                                        icon = '⚠ ';
                                    } else if (level === 'error') {
                                        icon = '✕ ';
                                    }
                                    html += '<li>' + $('<div/>').text(icon + text).html() + '</li>';
                                });
                                html += '</ul>';
                            }
                            result.html(html).css('color', color);
                        } else {
                            var msg = response && response.data && response.data.message
                                ? response.data.message
                                : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.text('<?php echo esc_js( __( '检测失败: ', 'developer-starter' ) ); ?>' + msg).css('color', '#ef4444');
                        }
                        button.prop('disabled', false).text('🧪 <?php echo esc_js( __( '检查拆分 CSS 完整性', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        result.text('<?php echo esc_js( __( '请求失败，请稍后重试', 'developer-starter' ) ); ?>').css('color', '#ef4444');
                        button.prop('disabled', false).text('🧪 <?php echo esc_js( __( '检查拆分 CSS 完整性', 'developer-starter' ) ); ?>');
                    }
                });
            });

            // Gzip/Brotli 状态检测
            $('#check-gzip-status').on('click', function() {
                var button = $(this);
                var result = $('#check-gzip-result');

                button.prop('disabled', true).text('<?php echo esc_js( __( '检测中...', 'developer-starter' ) ); ?>');
                result.text('<?php echo esc_js( __( '正在检测响应头...', 'developer-starter' ) ); ?>').css('color', '#64748b');

                $.ajax({
                    url: dsAjaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'developer_starter_check_gzip_status',
                        nonce: <?php echo wp_json_encode( wp_create_nonce( "check_gzip_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response && response.success && response.data) {
                            var data = response.data;
                            var color = '#64748b';
                            var prefix = '• ';
                            if (data.overall === 'enabled') {
                                color = '#10b981';
                                prefix = '✓ ';
                            } else if (data.overall === 'disabled') {
                                color = '#ef4444';
                                prefix = '✕ ';
                            } else if (data.overall === 'partial') {
                                color = '#f59e0b';
                                prefix = '⚠ ';
                            }

                            var html = '<div>' + prefix + (data.summary || '') + '</div>';
                            if (Array.isArray(data.items) && data.items.length) {
                                html += '<ul style="margin:6px 0 0 18px;">';
                                data.items.forEach(function(item) {
                                    var code = item && item.code ? item.code : 'n/a';
                                    var encoding = item && item.encoding ? item.encoding : 'none';
                                    var line = (item && item.label ? item.label : 'target')
                                        + ' [' + code + '] Content-Encoding: ' + encoding;
                                    if (item && item.error) {
                                        line += ' (' + item.error + ')';
                                    }
                                    html += '<li>' + $('<div/>').text(line).html() + '</li>';
                                });
                                html += '</ul>';
                            }
                            result.html(html).css('color', color);
                        } else {
                            var msg = response && response.data && response.data.message
                                ? response.data.message
                                : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>';
                            result.text('<?php echo esc_js( __( '检测失败: ', 'developer-starter' ) ); ?>' + msg).css('color', '#ef4444');
                        }
                        button.prop('disabled', false).text('🔍 <?php echo esc_js( __( '检测 Gzip/Brotli 是否开启', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        result.text('<?php echo esc_js( __( '请求失败，请稍后重试', 'developer-starter' ) ); ?>').css('color', '#ef4444');
                        button.prop('disabled', false).text('🔍 <?php echo esc_js( __( '检测 Gzip/Brotli 是否开启', 'developer-starter' ) ); ?>');
                    }
                });
            });

            function initFavorites() {
                var $wrap = $('.ds-settings-wrap');
                if ($wrap.data('search-enabled') !== 1) {
                    return;
                }
                var $table = $wrap.find('table.form-table').first();
                if (!$table.length) {
                    return;
                }

                var $tbody = $table.children('tbody').first();
                if (!$tbody.length) {
                    $tbody = $table;
                }

                function getTopLevelRows() {
                    return $tbody.children('tr');
                }

                getTopLevelRows().each(function(index) {
                    var $row = $(this);
                    if (typeof $row.data('origin-index') === 'undefined') {
                        $row.attr('data-origin-index', index);
                    }
                });

                function buildFavoritesSection() {
                    var $section = $tbody.children('tr.ds-favorites-section');
                    if ($section.length) {
                        return $section;
                    }
                    return $('<tr class="ds-section-row ds-favorites-section" data-search="<?php echo esc_attr( __( '常用设置 收藏', 'developer-starter' ) ); ?>">' +
                        '<th colspan="2"><h2 class="ds-section-title"><?php echo esc_html( __( '常用设置', 'developer-starter' ) ); ?></h2>' +
                        '<p class="description"><?php echo esc_html( __( '已收藏的设置会在这里置顶展示。', 'developer-starter' ) ); ?></p></th></tr>');
                }

                function reorderFavorites() {
                    var $rows = getTopLevelRows().not('.ds-favorites-section');
                    if (!$rows.length) {
                        return;
                    }

                    var rows = $rows.get().sort(function(a, b) {
                        var ai = parseInt(a.getAttribute('data-origin-index') || '0', 10);
                        var bi = parseInt(b.getAttribute('data-origin-index') || '0', 10);
                        return ai - bi;
                    });

                    var favorites = [];
                    var others = [];
                    rows.forEach(function(row) {
                        if (row.classList.contains('ds-favorite')) {
                            favorites.push(row);
                        } else {
                            others.push(row);
                        }
                    });

                    var $section = $tbody.children('tr.ds-favorites-section');
                    if (favorites.length) {
                        if (!$section.length) {
                            $section = buildFavoritesSection();
                        }
                        $section.detach();
                    } else if ($section.length) {
                        $section.remove();
                    }

                    $(rows).detach();

                    if (favorites.length) {
                        $tbody.append($section);
                        favorites.forEach(function(row) {
                            $tbody.append(row);
                        });
                    }

                    others.forEach(function(row) {
                        $tbody.append(row);
                    });
                }

                $(document).on('click', '.ds-favorite-toggle', function() {
                    var $btn = $(this);
                    var $row = $btn.closest('tr[data-setting-id]');
                    var settingId = $row.data('setting-id');
                    if (!settingId) {
                        return;
                    }

                    var willFavorite = !$row.hasClass('ds-favorite');
                    $row.toggleClass('ds-favorite', willFavorite);
                    $btn.attr('aria-pressed', willFavorite ? 'true' : 'false');
                    $btn.attr('title', willFavorite ? '<?php echo esc_js( __( '取消收藏', 'developer-starter' ) ); ?>' : '<?php echo esc_js( __( '收藏此设置', 'developer-starter' ) ); ?>');
                    $btn.attr('aria-label', willFavorite ? '<?php echo esc_js( __( '取消收藏', 'developer-starter' ) ); ?>' : '<?php echo esc_js( __( '收藏此设置', 'developer-starter' ) ); ?>');

                    reorderFavorites();

                    $.ajax({
                        url: dsAjaxUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'developer_starter_toggle_favorite_setting',
                            nonce: dsFavoriteNonce,
                            setting: settingId,
                            enabled: willFavorite ? 1 : 0
                        },
                        success: function(response) {
                            if (!response || !response.success) {
                                $row.toggleClass('ds-favorite', !willFavorite);
                                $btn.attr('aria-pressed', !willFavorite ? 'true' : 'false');
                                reorderFavorites();
                            }
                        },
                        error: function() {
                            $row.toggleClass('ds-favorite', !willFavorite);
                            $btn.attr('aria-pressed', !willFavorite ? 'true' : 'false');
                            reorderFavorites();
                        }
                    });
                });

                reorderFavorites();
            }

            function initThemeToggle() {
                var $wrap = $('.ds-settings-wrap');
                var $btn = $('.ds-theme-toggle');
                if (!$wrap.length || !$btn.length) {
                    return;
                }

                var storageKey = 'ds-admin-theme';
                var saved = localStorage.getItem(storageKey);
                if (saved === 'dark') {
                    $wrap.addClass('ds-theme-dark');
                    $btn.attr('aria-pressed', 'true');
                } else {
                    $wrap.removeClass('ds-theme-dark');
                    $btn.attr('aria-pressed', 'false');
                }

                $btn.on('click', function() {
                    var isDark = $wrap.toggleClass('ds-theme-dark').hasClass('ds-theme-dark');
                    $btn.attr('aria-pressed', isDark ? 'true' : 'false');
                    localStorage.setItem(storageKey, isDark ? 'dark' : 'light');
                });
            }

            function initSettingsSearch() {
                var $search = $('#ds-settings-search');
                if (!$search.length) {
                    return;
                }

                if ($search.val()) {
                    $search.val('');
                }

                var $toolbar = $search.closest('.ds-settings-toolbar');
                var $count = $toolbar.find('.ds-search-count');
                var $clear = $toolbar.find('.ds-search-clear');
                var $table = $search.closest('.ds-settings-wrap').find('table.form-table').first();
                var $wrap = $search.closest('.ds-settings-wrap');

                if (!$table.length) {
                    return;
                }

                var $tbody = $table.children('tbody').first();
                if (!$tbody.length) {
                    $tbody = $table;
                }

                var $rows = $tbody.children('tr');
                var $empty = $('<div class="ds-search-empty" hidden><strong><?php echo esc_js( __( '未找到匹配设置', 'developer-starter' ) ); ?></strong><p><?php echo esc_js( __( '可尝试以下关键词。', 'developer-starter' ) ); ?></p><div class="ds-search-empty__chips"></div></div>');
                $toolbar.after($empty);

                function getSearchSuggestions() {
                    var tab = ($wrap.data('current-tab') || '').toString();
                    var suggestions = {
                        design: ['<?php echo esc_js( __( '按钮颜色', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '卡片圆角', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '页脚背景', 'developer-starter' ) ); ?>'],
                        header: ['<?php echo esc_js( __( '菜单位置', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '透明头部', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '移动端顶部入口', 'developer-starter' ) ); ?>'],
                        footer: ['<?php echo esc_js( __( '公司名称', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '联系电话', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '备案信息', 'developer-starter' ) ); ?>'],
                        pages: ['<?php echo esc_js( __( '搜索页装修', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '404 页面装修', 'developer-starter' ) ); ?>']
                    };
                    return suggestions[tab] || ['<?php echo esc_js( __( '按钮颜色', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '页脚背景', 'developer-starter' ) ); ?>', '<?php echo esc_js( __( '菜单位置', 'developer-starter' ) ); ?>'];
                }

                function renderEmptySuggestions() {
                    var $chips = $empty.find('.ds-search-empty__chips');
                    if ($chips.children().length) {
                        return;
                    }
                    getSearchSuggestions().forEach(function(label) {
                        $('<button type="button" class="button-link ds-settings-shortcut ds-search-empty__chip"></button>')
                            .text(label)
                            .attr('data-query', label)
                            .appendTo($chips);
                    });
                }

                function normalizeSearchText(value) {
                    return $.trim((value || '').toString().toLowerCase()).replace(/\s+/g, ' ');
                }

                function applyFilter() {
                    var term = normalizeSearchText($search.val());

                    if (!term) {
                        $rows.removeClass('ds-filter-hidden');
                        $count.text('');
                        $empty.prop('hidden', true);
                        return;
                    }

                    var terms = term.split(/\s+/).filter(Boolean);
                    var visibleCount = 0;
                    $rows.each(function() {
                        var $row = $(this);
                        var haystack = normalizeSearchText($row.data('search') || '');
                        if (!haystack) {
                            haystack = normalizeSearchText($row.text());
                        }
                        var compactHaystack = haystack.replace(/\s+/g, '');
                        var matched = terms.every(function(part) {
                            var compactPart = part.replace(/\s+/g, '');
                            return haystack.indexOf(part) !== -1 || (compactPart && compactHaystack.indexOf(compactPart) !== -1);
                        });
                        if (matched) {
                            $row.removeClass('ds-filter-hidden');
                            if (!$row.hasClass('ds-section-row') && $row.is(':visible')) {
                                visibleCount++;
                            }
                        } else {
                            $row.addClass('ds-filter-hidden');
                        }
                    });

                    $tbody.children('tr.ds-section-row').each(function() {
                        var $section = $(this);
                        var $next = $section.next();
                        var hasVisible = false;
                        while ($next.length && !$next.hasClass('ds-section-row')) {
                            if (!$next.hasClass('ds-filter-hidden')) {
                                hasVisible = true;
                                break;
                            }
                            $next = $next.next();
                        }
                        if (hasVisible) {
                            $section.removeClass('ds-filter-hidden');
                        } else {
                            $section.addClass('ds-filter-hidden');
                        }
                    });

                    $count.text('<?php echo esc_js( __( '找到', 'developer-starter' ) ); ?> ' + visibleCount + ' <?php echo esc_js( __( '项', 'developer-starter' ) ); ?>');
                    if (visibleCount > 0) {
                        $empty.prop('hidden', true);
                    } else {
                        renderEmptySuggestions();
                        $empty.prop('hidden', false);
                    }
                }

                $search.on('input', applyFilter);
                function clearSearch(shouldFocus) {
                    $search.val('');
                    $search.trigger('input');
                    if (shouldFocus) {
                        $search.trigger('focus');
                    }
                }

                $clear.on('click', function() {
                    clearSearch(true);
                });

                $table.closest('form').on('click', '.ds-settings-shortcut', function() {
                    var query = normalizeSearchText($(this).data('query') || '');
                    var targetId = $.trim(($(this).data('target') || '').toString());
                    var resetSearch = $(this).is('[data-reset-search]');

                    if (resetSearch) {
                        clearSearch(false);
                        window.requestAnimationFrame(function() {
                            var toolbar = document.querySelector('.ds-settings-toolbar');
                            if (toolbar && typeof toolbar.scrollIntoView === 'function') {
                                toolbar.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        });
                        return;
                    }

                    if (targetId) {
                        clearSearch(false);
                    } else if (query) {
                        $search.val(query);
                        $search.trigger('input');
                    }

                    if (targetId) {
                        window.requestAnimationFrame(function() {
                            var target = document.getElementById(targetId);
                            if (!target) {
                                return;
                            }
                            if (window.location.hash === '#' + targetId && window.history && window.history.replaceState) {
                                window.history.replaceState(null, '', window.location.pathname + window.location.search);
                            }
                            window.location.hash = targetId;
                            if (typeof target.scrollIntoView === 'function') {
                                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        });
                    }
                });

                applyFilter();
            }

            $('form[action="options.php"]').on('submit', function(e) {
                var form = $(this);
                if (form.find('input[name="developer_starter_options[__json_payload]"]').length > 0) {
                    return true;
                }
                
                var data = {};
                var formData = form.serializeArray();
                var isSettingsForm = false;
                
                $.each(formData, function(i, field) {
                    var match = field.name.match(/^developer_starter_options\[(.*?)\](\[\])?$/);
                    if (match) {
                        var key = match[1];
                        var isArray = match[2] !== undefined;
                        if (isArray) {
                            if (!Array.isArray(data[key])) data[key] = [];
                            data[key].push(field.value);
                        } else {
                            data[key] = field.value; // 后出现的值覆盖先出现的值，完美兼容复选框的隐藏域机制
                        }
                        isSettingsForm = true;
                    }
                });

                if (isSettingsForm) {
                    // Disable original inputs to prevent max_input_vars truncation
                    form.find(':input[name^="developer_starter_options["]').prop('disabled', true);
                    
                    // Re-enable or append the JSON payload
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'developer_starter_options[__json_payload]',
                        value: JSON.stringify(data)
                    }).appendTo(form);
                }
            });

            initFavorites();
            initThemeToggle();
            initSettingsSearch();
        });
        </script>
        <?php
    }

    public function register_settings() {
        register_setting( 'developer_starter_settings', $this->option_name, array(
            'sanitize_callback' => array( $this, 'sanitize_options' ),
        ) );
    }

    public function handle_reset() {
        if ( isset( $_POST['ds_reset_settings'] ) && isset( $_POST['ds_reset_nonce'] ) ) {
            $reset_nonce = sanitize_text_field( wp_unslash( $_POST['ds_reset_nonce'] ) );
            if ( wp_verify_nonce( $reset_nonce, 'ds_reset_action' ) && current_user_can( 'manage_options' ) ) {
                delete_option( $this->option_name );
                add_settings_error( 'developer_starter_settings', 'reset', __( '主题设置已恢复默认！', 'developer-starter' ), 'updated' );
            }
        }
    }
}
