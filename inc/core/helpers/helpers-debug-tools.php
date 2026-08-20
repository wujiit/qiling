<?php
/**
 * Debug and misc frontend tool helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 脚本加载策略（defer）
 */
function developer_starter_defer_scripts( $tag, $handle, $src ) {
    if ( is_admin() || empty( $src ) ) {
        return $tag;
    }

    $strategy = (string) developer_starter_get_option( 'js_loading_strategy', '' );
    if ( $strategy === '' ) {
        $strategy = 'safe_defer';
    }
    if ( $strategy === 'none' ) {
        return $tag;
    }

    $exclude_raw = (string) developer_starter_get_option( 'js_defer_exclude_handles', '' );
    $exclude = preg_split( '/[\s,]+/', $exclude_raw );
    $exclude = array_values(
        array_filter(
            array_map(
                static function( $item ) {
                    return sanitize_key( (string) $item );
                },
                (array) $exclude
            )
        )
    );
    if ( in_array( sanitize_key( $handle ), $exclude, true ) ) {
        return $tag;
    }

    $critical_handles = array(
        'jquery',
        'jquery-core',
        'jquery-migrate',
        'wp-hooks',
        'wp-i18n',
        'wp-polyfill',
        'heartbeat',
    );

    $safe_handles = array(
        'developer-starter-footer-effects',
        'translate-js',
        'comment-reply',
    );

    $should_defer = false;
    if ( $strategy === 'aggressive_defer' ) {
        $should_defer = ! in_array( $handle, $critical_handles, true );
    } elseif ( $strategy === 'safe_defer' ) {
        $should_defer = in_array( $handle, $safe_handles, true );
    }

    if ( ! $should_defer ) {
        return $tag;
    }

    if ( strpos( $tag, ' defer' ) !== false || strpos( $tag, ' async' ) !== false ) {
        return $tag;
    }

    return preg_replace( '/<script\b/i', '<script defer', $tag, 1 );
}
add_filter( 'script_loader_tag', 'developer_starter_defer_scripts', 10, 3 );

/**
 * 页面/选项更新时清除相关缓存
 */
function developer_starter_clear_theme_cache( $post_id = null ) {
    delete_transient( 'developer_starter_account_url' );
}
add_action( 'save_post_page', 'developer_starter_clear_theme_cache' );
add_action( 'update_option_developer_starter_options', 'developer_starter_clear_theme_cache' );

/**
 * 清除选项缓存（当选项更新时重置静态变量）
 */
function developer_starter_reset_options_cache() {
    if ( function_exists( 'developer_starter_refresh_options_cache' ) ) {
        developer_starter_refresh_options_cache();
    }
}
add_action( 'update_option_developer_starter_options', 'developer_starter_reset_options_cache' );

/**
 * 开发调试模式 - 在前台底部显示调试信息（仅管理员可见）
 */
function developer_starter_debug_output() {
    if ( ! developer_starter_get_option( 'debug_mode', '' ) ) {
        return;
    }

    if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;

    $load_time = timer_stop( 0, 4 );
    $query_count = get_num_queries();
    $memory_usage = size_format( memory_get_peak_usage( true ) );
    $object_cache = wp_using_ext_object_cache() ? '✅ 已启用' : '❌ 未启用';
    $cache_type = wp_using_ext_object_cache() ? '（Redis/Memcached）' : '（使用数据库）';
    $page_cache = defined( 'WP_CACHE' ) && WP_CACHE ? '✅ 已启用' : '❌ 未启用';
    $php_version = phpversion();
    $wp_version = get_bloginfo( 'version' );
    $theme_version = DEVELOPER_STARTER_VERSION;

    ?>
    <div id="developer-debug-bar" style="
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, var(--color-neutral-800) 0%, var(--color-neutral-900) 100%);
        color: var(--color-neutral-200);
        font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
        font-size: var(--qiling-text-rem-0p75);
        z-index: 99999;
        box-shadow: 0 -4px 20px rgba(var(--qiling-rgb-0-0-0), 0.3);
        max-height: 80vh;
        overflow-y: auto;
    ">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: var(--qiling-space-8) var(--qiling-space-20);">
            <div style="display: flex; align-items: center; gap: var(--qiling-space-25); flex-wrap: wrap;">
                <span style="color: var(--color-warning); font-weight: 600;">🛠️ 调试模式</span>

                <span title="SQL查询次数" style="cursor: pointer;" onclick="document.getElementById('debug-sql-details').style.display = document.getElementById('debug-sql-details').style.display === 'none' ? 'block' : 'none'">
                    <span style="color: var(--color-neutral-500);">SQL</span>
                    <span style="color: <?php echo $query_count > 50 ? 'var(--color-error)' : ( $query_count > 20 ? 'var(--color-warning)' : 'var(--color-success)' ); ?>; font-weight: 600; margin-left: var(--qiling-space-5);">
                        <?php echo $query_count; ?>
                    </span>
                    <span style="font-size: calc(var(--qiling-font-size-base) * 0.625); color: var(--color-neutral-400);">(点击查看)</span>
                </span>

                <span title="页面加载时间">
                    <span style="color: var(--color-neutral-500);">加载</span>
                    <span style="color: <?php echo $load_time > 1 ? 'var(--color-error)' : ( $load_time > 0.5 ? 'var(--color-warning)' : 'var(--color-success)' ); ?>; font-weight: 600; margin-left: var(--qiling-space-5);">
                        <?php echo $load_time; ?>s
                    </span>
                </span>

                <span title="内存峰值">
                    <span style="color: var(--color-neutral-500);">内存</span>
                    <span style="color: var(--qiling-color-8b5cf6); font-weight: 600; margin-left: var(--qiling-space-5);"><?php echo $memory_usage; ?></span>
                </span>

                <span title="对象缓存状态">
                    <span style="color: var(--color-neutral-500);">对象缓存</span>
                    <span style="margin-left: var(--qiling-space-5);"><?php echo $object_cache; ?></span>
                    <span style="color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.625);"><?php echo $cache_type; ?></span>
                </span>

                <span title="页面缓存状态">
                    <span style="color: var(--color-neutral-500);">页面缓存</span>
                    <span style="margin-left: var(--qiling-space-5);"><?php echo $page_cache; ?></span>
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: var(--qiling-space-15);">
                <span style="color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.625);">
                    PHP <?php echo $php_version; ?> | WP <?php echo $wp_version; ?> | 主题 <?php echo $theme_version; ?>
                </span>
                <button onclick="document.getElementById('developer-debug-bar').style.display='none'" style="
                    background: var(--color-neutral-700);
                    border: none;
                    color: var(--color-neutral-400);
                    padding: var(--qiling-space-4) var(--qiling-space-10);
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: calc(var(--qiling-font-size-base) * 0.6875);
                ">关闭</button>
            </div>
        </div>

        <div id="debug-sql-details" style="display: none; border-top: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.1); padding: var(--qiling-space-20); background: rgba(var(--qiling-rgb-0-0-0), 0.2);">
            <?php if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) : ?>
                <table style="width: 100%; border-collapse: collapse; color: var(--color-neutral-300); font-size: calc(var(--qiling-font-size-base) * 0.6875);">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.1);">
                            <th style="padding: var(--qiling-space-8); width: var(--qiling-measure-60);">#</th>
                            <th style="padding: var(--qiling-space-8); width: var(--qiling-measure-80);">耗时 (ms)</th>
                            <th style="padding: var(--qiling-space-8);">SQL 语句</th>
                            <th style="padding: var(--qiling-space-8); width: var(--qiling-measure-200);">调用栈</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_time = 0;
                        foreach ( $wpdb->queries as $index => $query ) :
                            $sql = $query[0];
                            $time = $query[1] * 1000;
                            $stack = $query[2];
                            $total_time += $time;

                            $color = 'var(--color-neutral-300)';
                            if ( $time > 50 ) {
                                $color = 'var(--color-error)';
                            } elseif ( $time > 10 ) {
                                $color = 'var(--color-warning)';
                            }
                            ?>
                        <tr style="border-bottom: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.05);">
                            <td style="padding: var(--qiling-space-6) var(--qiling-space-8); color: var(--color-neutral-500);"><?php echo $index + 1; ?></td>
                            <td style="padding: var(--qiling-space-6) var(--qiling-space-8); color: <?php echo $color; ?>; font-weight: bold;"><?php echo number_format( $time, 2 ); ?></td>
                            <td style="padding: var(--qiling-space-6) var(--qiling-space-8); font-family: monospace; word-break: break-all; white-space: pre-wrap; line-height: 1.4;"><?php echo esc_html( $sql ); ?></td>
                            <td style="padding: var(--qiling-space-6) var(--qiling-space-8); color: var(--color-neutral-400); font-size: calc(var(--qiling-font-size-base) * 0.625);"><?php echo esc_html( $stack ); ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="4" style="padding: var(--qiling-space-10); text-align: right; font-weight: bold; color: var(--color-neutral-200);">
                                总耗时: <?php echo number_format( $total_time, 2 ); ?> ms
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php else : ?>
                <div style="padding: var(--qiling-space-20); text-align: center; color: var(--color-neutral-400);">
                    <p style="font-size: calc(var(--qiling-font-size-base) * 0.875); margin-bottom: var(--qiling-space-10);">⚠️ 未启用 SQL 记录</p>
                    <p>请在 <code style="background: rgba(var(--qiling-rgb-255-255-255), 0.1); padding: var(--qiling-space-2) var(--qiling-space-4); border-radius: var(--qiling-space-3);">wp-config.php</code> 文件中添加以下代码以查看详细查询：</p>
                    <pre style="background: rgba(var(--qiling-rgb-0-0-0), 0.3); padding: var(--qiling-space-10); display: inline-block; border-radius: var(--qiling-space-4); color: var(--color-warning);">define( 'SAVEQUERIES', true );</pre>
                    <p style="margin-top: var(--qiling-space-10); color: var(--color-error); font-size: calc(var(--qiling-font-size-base) * 0.6875);">(注意：启用此选项会影响网站性能，调试完成后请务必关闭)</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'developer_starter_debug_output', 999 );

/**
 * 轻量性能监测浮层（仅管理员可见）
 */
function developer_starter_performance_monitor_output() {
    if ( ! developer_starter_get_option( 'performance_monitor_enable', '' ) ) {
        return;
    }
    if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( developer_starter_get_option( 'debug_mode', '' ) ) {
        return;
    }

    $load_time = (float) timer_stop( 0, 4 );
    $query_count = (int) get_num_queries();
    $memory_usage = size_format( memory_get_peak_usage( true ) );
    $object_cache = wp_using_ext_object_cache() ? __( '对象缓存: 开', 'developer-starter' ) : __( '对象缓存: 关', 'developer-starter' );
    $status_color = 'var(--color-success)';
    if ( $load_time > 1.2 || $query_count > 120 ) {
        $status_color = 'var(--color-error)';
    } elseif ( $load_time > 0.7 || $query_count > 70 ) {
        $status_color = 'var(--color-warning)';
    }
    ?>
    <div style="position:fixed;right:var(--qiling-space-14);bottom:var(--qiling-space-14);z-index:99998;background:rgba(var(--qiling-rgb-15-23-42),.92);color:var(--color-neutral-200);padding:var(--qiling-space-8) var(--qiling-space-10);border-radius:var(--qiling-space-8);font-size:var(--qiling-text-rem-0p75);line-height:1.5;box-shadow:0 6px 20px rgba(var(--qiling-rgb-0-0-0),.3);border-left:3px solid <?php echo esc_attr( $status_color ); ?>;">
        <div><?php echo esc_html( sprintf( __( '加载: %ss', 'developer-starter' ), number_format( $load_time, 3 ) ) ); ?></div>
        <div><?php echo esc_html( sprintf( __( 'SQL: %d', 'developer-starter' ), $query_count ) ); ?></div>
        <div><?php echo esc_html( sprintf( __( '内存: %s', 'developer-starter' ), $memory_usage ) ); ?></div>
        <div><?php echo esc_html( $object_cache ); ?></div>
    </div>
    <?php
}
add_action( 'wp_footer', 'developer_starter_performance_monitor_output', 998 );
