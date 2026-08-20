<?php
/**
 * 主题授权核心类
 *
 * 处理授权验证、异步检查和遥测心跳。
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Theme_License {

    /**
     * 获取授权接口地址
     */
    private static function get_api_endpoint() {
        return 'https://www.jingxialai.com/wp-json/qiling-verify/v1/check';
    }

    /**
     * 获取当前主题版本号（始终获取父主题版本）
     * 兼容子主题模式
     */
    public static function get_theme_version() {
        $theme = wp_get_theme();
        if ( $theme->parent() ) {
            $theme = $theme->parent();
        }
        $version = $theme->get( 'Version' );
        return $version ? $version : '1.0.0';
    }

    /**
     * 检查冷却时间 (秒)
     * 15天 = 15 * 24 * 3600
     */
    const CHECK_INTERVAL = 1296000;

    /**
     * Option Keys
     */
    const OPTION_KEY = 'theme_license_key';
    const OPTION_STATUS = 'theme_license_status';
    const OPTION_LAST_CHECK = 'theme_license_last_check';
    const TRANSIENT_COOLDOWN = 'theme_license_check_cooldown';
    const SETTINGS_OPTION = 'developer_starter_options';

    /**
     * 构造函数
     */
    public function __construct() {
        // 注册 AJAX 处理
        add_action( 'wp_ajax_qiling_check_license_async', array( $this, 'ajax_check_license' ) );
        add_action( 'wp_ajax_qiling_manual_verify_license', array( $this, 'ajax_manual_verify_license' ) );
        add_action( 'wp_ajax_qiling_send_telemetry_only', array( $this, 'ajax_send_telemetry_only' ) );
        
        // 后台脚部注入 JS
        add_action( 'admin_footer', array( $this, 'admin_footer_js' ) );

        // 保存设置时立即触发检查
        // 在 sanitize_options 中同步更新了 theme_license_key
        add_action( 'update_option_' . self::OPTION_KEY, array( $this, 'force_check_on_save' ), 10, 2 );

        // 添加管理员通知
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // 检查版本升级，必要时重置冷却时间
        add_action( 'admin_init', array( $this, 'check_upgrade_event' ) );
    }

    /**
     * 检查是否刚升级了主题
     * 如果是，则强制重置冷却时间，确保 admin_footer_js 能触发检查
     */
    public function check_upgrade_event() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $current_version = self::get_theme_version();
        $full_option_key = 'qiling_license_last_checked_version';
        $last_checked = get_option( $full_option_key, '0.0.0' );

        if ( version_compare( $last_checked, $current_version, '<' ) ) {
            // 版本更新了，清除冷却标记
            delete_transient( self::TRANSIENT_COOLDOWN );
            
            // 更新记录的版本号
            update_option( $full_option_key, $current_version );
            
            // 记录日志
            // if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            //     error_log( "Qiling License: Detected upgrade from $last_checked to $current_version. Cooldown reset." );
            // }
        }
    }

    /**
     * 获取授权状态
     * 
     * @return string valid|invalid|unknown
     */
    public static function get_status() {
        $status = get_option( self::OPTION_STATUS );
        return $status ? $status : 'unknown';
    }

    /**
     * 获取授权码
     */
    public static function get_key() {
        $key = trim( (string) get_option( self::OPTION_KEY, '' ) );
        if ( '' !== $key ) {
            return $key;
        }

        $options = get_option( self::SETTINGS_OPTION, array() );
        if ( is_array( $options ) && ! empty( $options['theme_license_key'] ) ) {
            return trim( (string) $options['theme_license_key'] );
        }

        return '';
    }

    /**
     * 同步授权密钥到主题设置数组和独立 option。
     *
     * 双写是兼容设计：设置页依赖 developer_starter_options，授权运行时依赖独立 option。
     *
     * @param string $license_key 授权密钥。
     * @return void
     */
    private function sync_license_key_storage( $license_key ) {
        $license_key = sanitize_text_field( (string) $license_key );

        $options = get_option( self::SETTINGS_OPTION, array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        if ( ! isset( $options['theme_license_key'] ) || (string) $options['theme_license_key'] !== $license_key ) {
            $options['theme_license_key'] = $license_key;
            update_option( self::SETTINGS_OPTION, $options );
        }

        if ( (string) get_option( self::OPTION_KEY, '' ) === $license_key ) {
            return;
        }

        remove_action( 'update_option_' . self::OPTION_KEY, array( $this, 'force_check_on_save' ), 10 );
        update_option( self::OPTION_KEY, $license_key );
        add_action( 'update_option_' . self::OPTION_KEY, array( $this, 'force_check_on_save' ), 10, 2 );
    }

    /**
     * 是否允许输出授权相关调试日志。
     *
     * @return bool
     */
    private static function is_debug_logging_enabled() {
        if ( function_exists( 'developer_starter_get_option' ) && developer_starter_get_option( 'debug_mode', '' ) === '1' ) {
            return true;
        }

        return defined( 'WP_DEBUG' ) && WP_DEBUG;
    }

    /**
     * 管理员通知
     */
    public function admin_notices() {
        // 仅管理员可见
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $status = self::get_status();

        // 只有明确为 invalid 时才报错
        if ( $status === 'invalid' ) {
            $debug_error = get_option( 'qiling_license_debug_error' );
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong><?php esc_html_e( '主题授权验证失败：', 'developer-starter' ); ?></strong>
                    <?php esc_html_e( '检测到当前域名与授权码绑定的域名不一致。请检查设置或联系客服。', 'developer-starter' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=developer-starter-settings' ) ); ?>"><?php esc_html_e( '前往设置', 'developer-starter' ); ?></a>
                </p>
                <?php if ( $debug_error && self::is_debug_logging_enabled() ) : ?>
                <p style="color: #d63638;">
                    <strong><?php esc_html_e( '调试信息：', 'developer-starter' ); ?></strong>
                    <?php echo esc_html( $debug_error ); ?>
                </p>
                <?php endif; ?>
                <p><small><?php esc_html_e( '注：您的网站前台功能未受影响。', 'developer-starter' ); ?></small></p>
            </div>
            <?php
        }
    }

    /**
     * 在数据写入前检查
     */
    public function pre_check_on_save_array( $new_value, $old_value ) {
        // 调试
        // update_option( 'qiling_license_debug_pre_update', 'triggered' );
        
        // 复用检查逻辑
        $this->force_check_on_save_array( $old_value, $new_value );
        
        // 必须返回新值，否则数据无法保存
        return $new_value;
    }

    /**
     * 强制检查（当Key更新时 - 独立 Option 模式）
     */
    public function force_check_on_save( $old_value, $new_value ) {
        if ( $old_value !== $new_value ) {
            delete_transient( self::TRANSIENT_COOLDOWN );
            $this->remote_check( $new_value );
        }
    }

    /**
     * 强制检查（当Key更新时 - 数组 Option 模式）
     */
    public function force_check_on_save_array( $old_value, $new_value ) {
        // 调试：记录接收到的数据类型
        // update_option( 'qiling_license_debug_hook_data_type', gettype( $new_value ) );
        
        // 尝试从 new_value 提取
        $new_key = '';
        if ( is_array( $new_value ) && isset( $new_value['theme_license_key'] ) ) {
            $new_key = $new_value['theme_license_key'];
        }
        
        // 如果提取失败，尝试从 $_POST 获取
        if ( empty( $new_key ) && isset( $_POST['developer_starter_options']['theme_license_key'] ) ) {
            $new_key = sanitize_text_field( wp_unslash( $_POST['developer_starter_options']['theme_license_key'] ) );
            // update_option( 'qiling_license_debug_source', 'POST' );
        } else {
            // update_option( 'qiling_license_debug_source', 'HOOK_ARGS' );
        }

        // 提取旧 Key
        $old_key = '';
        if ( is_array( $old_value ) && isset( $old_value['theme_license_key'] ) ) {
            $old_key = $old_value['theme_license_key'];
        }

        // 记录提取到的 Key 用于调试
        // update_option( 'qiling_license_debug_extracted_key', $new_key );

        // 只有 Key 真正发生变化时才触发
        if ( $new_key && $old_key !== $new_key ) {
            delete_transient( self::TRANSIENT_COOLDOWN );
            $this->remote_check( $new_key );
        }
    }

    /**
     * 后台脚部注入异步检查 JS
     */
    public function admin_footer_js() {
        // 1. 必须是管理员后台
        // 2. 必须没有通过 AJAX 调用此函数
        // 3. 必须是管理员用户
        if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // 检查是否已发送过遥测（使用独立的遥测标记，与授权检查冷却分开）
        $telemetry_sent = get_transient( 'qiling_telemetry_first_sent' );
        $needs_telemetry = ! $telemetry_sent;
        
        // 检查授权检查是否在冷却期内
        $license_cooldown = get_transient( self::TRANSIENT_COOLDOWN );
        $needs_license_check = ! $license_cooldown;

        // 如果两者都不需要，直接返回
        if ( ! $needs_telemetry && ! $needs_license_check ) {
            return;
        }

        // 输出 JS
        $debug_logging_enabled = self::is_debug_logging_enabled();
        ?>
        <script type="text/javascript">
        (function() {
            var qilingLicenseDebug = <?php echo wp_json_encode( $debug_logging_enabled ); ?>;
            var qilingLicenseLog = function(message, error) {
                if (!qilingLicenseDebug || !window.console || typeof window.console.debug !== 'function') {
                    return;
                }
                if (error) {
                    window.console.debug(message, error);
                    return;
                }
                window.console.debug(message);
            };

            // 延迟执行，不阻塞页面渲染
            setTimeout(function() {
                <?php if ( $needs_telemetry ) : ?>
                // 首次遥测请求
                var telemetryData = {
                    'action': 'qiling_send_telemetry_only',
                    'nonce': <?php echo wp_json_encode( wp_create_nonce( 'qiling_telemetry_nonce' ) ); ?>
                };
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams(telemetryData)
                }).then(function() {
                    qilingLicenseLog('Qiling: Telemetry sent');
                }).catch(function(error) {
                    qilingLicenseLog('Qiling: Telemetry failed', error);
                });
                <?php endif; ?>
                
                <?php if ( $needs_license_check ) : ?>
                // 授权检查请求
                var licenseData = {
                    'action': 'qiling_check_license_async',
                    'nonce': <?php echo wp_json_encode( wp_create_nonce( 'qiling_license_check' ) ); ?>
                };
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams(licenseData)
                }).then(function() {
                    qilingLicenseLog('Qiling: License check completed');
                }).catch(function(error) {
                    qilingLicenseLog('Qiling: License check failed', error);
                });
                <?php endif; ?>
            }, 3000); // 3秒后执行
        })();
        </script>
        <?php
        
        // 设置临时冷却，防止当前页面多次刷新导致重复输出JS
        if ( $needs_license_check ) {
            set_transient( self::TRANSIENT_COOLDOWN, 'pending', 60 );
        }
    }

    /**
     * AJAX 处理函数
     */
    public function ajax_check_license() {
        // 验证 nonce
        check_ajax_referer( 'qiling_license_check', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        // 执行远程检查
        $this->remote_check();

        // 无论成功失败，都设置冷却时间，避免频繁打扰服务端
        set_transient( self::TRANSIENT_COOLDOWN, 'checked', self::CHECK_INTERVAL );

        wp_die();
    }
    /**
     * 手动授权验证 AJAX 处理
     * 用于前端独立的"授权验证"按钮
     */
    public function ajax_manual_verify_license() {
        // 验证 nonce
        check_ajax_referer( 'qiling_verify_license_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
        
        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => __( '请输入授权密钥', 'developer-starter' ) ) );
        }

        // 保存新密钥到数据库。这里保留双写，避免设置页字段与运行时独立 option 不一致。
        $this->sync_license_key_storage( $license_key );

        // 执行远程验证并获取详细结果
        $domain = self::get_current_domain();
        $version = self::get_theme_version();
        
        $body = array(
            'domain'  => $domain,
            'key'     => $license_key,
            'version' => $version,
        );
        
        $response = wp_remote_post( self::get_api_endpoint(), array(
            'body'      => $body,
            'timeout'   => 15,
            'blocking'  => true,
            'sslverify' => true,
        ) );
        
        // 检查网络错误
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => __( '网络连接失败：', 'developer-starter' ) . $response->get_error_message() ) );
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            wp_send_json_error( array( 'message' => __( '服务器返回错误：HTTP ', 'developer-starter' ) . $response_code ) );
        }
        
        // 解析响应
        $body_raw = wp_remote_retrieve_body( $response );
        // 移除可能的BOM和空白字符
        $body_raw = trim( $body_raw, "\xEF\xBB\xBF \t\n\r\0\x0B" );
        $data = json_decode( $body_raw, true );
        
        // 检查JSON解析是否成功
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => __( 'JSON解析错误：', 'developer-starter' ) . json_last_error_msg() ) );
        }
        
        if ( ! isset( $data['data']['status'] ) ) {
            // 显示原始响应帮助调试
            $preview = mb_substr( $body_raw, 0, 200 );
            wp_send_json_error( array( 'message' => __( '服务器返回格式错误：', 'developer-starter' ) . $preview ) );
        }
        
        $remote_status = $data['data']['status'];
        
        // 更新本地状态
        update_option( self::OPTION_STATUS, $remote_status );
        update_option( self::OPTION_LAST_CHECK, time() );
        
        if ( $remote_status === 'valid' ) {
            wp_send_json_success( array( 'message' => __( '授权验证成功', 'developer-starter' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( '授权验证失败：', 'developer-starter' ) . $remote_status ) );
        }
    }

    /**
     * 仅发送遥测数据 AJAX 处理
     * 不需要授权密钥，用于检测所有使用主题的网站
     */
    public function ajax_send_telemetry_only() {
        // 验证 nonce
        check_ajax_referer( 'qiling_telemetry_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        $domain = self::get_current_domain();
        if ( empty( $domain ) ) {
            wp_die();
        }

        $version = self::get_theme_version();
        $key = self::get_key(); // 可能为空

        // 准备遥测数据
        $body = array(
            'domain'  => $domain,
            'key'     => $key,
            'version' => $version,
        );

        // 发送到服务器
        $response = wp_remote_post( self::get_api_endpoint(), array(
            'body'      => $body,
            'timeout'   => 10,
            'blocking'  => true,
            'sslverify' => true,
        ) );

        // 无论成功失败，设置长期 transient 防止重复发送
        // 30天后才会再次发送遥测
        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            set_transient( 'qiling_telemetry_first_sent', 'yes', 30 * DAY_IN_SECONDS );
            delete_option( 'qiling_telemetry_pending' );
        } else {
            // 失败时设置短期 transient，1天后重试
            set_transient( 'qiling_telemetry_first_sent', 'pending', DAY_IN_SECONDS );
            update_option( 'qiling_telemetry_pending', 1, false );
        }

        wp_die();
    }

    /**
     * 触发一次遥测 (静态调用)
     * 用于非实例化场景，如主题激活或设置页面访问
     */
    public static function trigger_telemetry() {
        $current_domain = self::get_current_domain();
        if ( empty( $current_domain ) ) {
            return false;
        }
        
        // 使用 transient 控制遥测状态：
        // success: 发送成功（30天内跳过）
        // pending: 最近失败，等待冷却后再试
        // in_progress: 当前已有请求在进行，避免并发阻塞
        $telemetry_state = get_transient( 'qiling_telemetry_sent' );
        if ( $telemetry_state === 'success' ) {
            return true;
        }
        if ( $telemetry_state === 'pending' || $telemetry_state === 'in_progress' ) {
            return false;
        }

        // 先加短锁，防止设置页并发请求重复阻塞
        set_transient( 'qiling_telemetry_sent', 'in_progress', 10 * MINUTE_IN_SECONDS );
        
        // 发送遥测请求
        $version = self::get_theme_version();
        $key = self::get_key(); // 可能为空，这是正常的
        $timeout = (float) apply_filters( 'qiling_telemetry_timeout', 3 );
        if ( $timeout < 1 ) {
            $timeout = 1;
        }
        
        // 准备遥测数据
        $body = array(
            'domain'  => $current_domain,
            'key'     => $key,
            'version' => $version,
        );
        
        // 发送到服务器
        $response = wp_remote_post( self::get_api_endpoint(), array(
            'body'      => $body,
            'timeout'   => $timeout,
            'blocking'  => true,
            'sslverify' => true,
        ) );
        
        // 检查响应
        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            // 成功：设置30天的 transient
            set_transient( 'qiling_telemetry_sent', 'success', 30 * DAY_IN_SECONDS );
            // 同时更新 option 以便调试查看
            update_option( 'qiling_telemetry_last_sent', current_time( 'mysql' ) );
            return true;
        } else {
            // 失败：设置冷却状态，避免每次进入设置页都阻塞重试
            set_transient( 'qiling_telemetry_sent', 'pending', 12 * HOUR_IN_SECONDS );
            // 记录错误以便调试
            $error_msg = is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $response );
            update_option( 'qiling_telemetry_last_error', $error_msg );
            return false;
        }
    }

    /**
     * 远程验证逻辑
     * @param string $manual_key 可选，手动传入 Key
     */
    public function remote_check( $manual_key = null ) {
        $key = $manual_key !== null ? $manual_key : self::get_key();
        
        // 调试：记录最后一次尝试发送的 Key
        // update_option( 'qiling_license_debug_last_key_sent', $key );
        
        $domain = self::get_current_domain();
        if ( empty( $domain ) ) {
            return false;
        }
        $version = self::get_theme_version();

        // 准备请求体 (Telemetry Data)
        $body = array(
            'domain' => $domain,
            'key'    => $key,
            'version' => $version,
        );

        // 发送 POST 请求
        $response = wp_remote_post( self::get_api_endpoint(), array(
            'body'    => $body,
            'timeout' => 15, // 设置合理的超时，不要太长
            'blocking' => true,
            'sslverify' => true, // 强制校验证书，避免授权请求被中间人伪造
        ) );

        // 如果连接出错 (WP_Error) 或服务器错误 (500/502/503)，直接返回，不更新本地状态
        // 这样就保持了 "上一次的有效状态"
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            return false;
        }

        // 解析结果
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        // 预期返回格式:
        // { "code": "success", "data": { "status": "valid" } }
        // { "code": "success", "data": { "status": "invalid" } }

        if ( isset( $data['data']['status'] ) ) {
            $remote_status = $data['data']['status'];
            
            update_option( self::OPTION_STATUS, $remote_status );
            update_option( self::OPTION_LAST_CHECK, time() );
            return true;
        }
        return false;
    }

    private static function get_current_domain() {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        if ( empty( $host ) && isset( $_SERVER['HTTP_HOST'] ) ) {
            $host = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_HOST'] ) );
        }
        return $host ? strtolower( $host ) : '';
    }
}
