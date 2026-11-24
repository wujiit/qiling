<?php
/**
 * 主题授权核心类
 *
 * 统一处理服务支持凭证维护与非阻塞运行状态同步。
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
     * 选项键名定义
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
        $key = self::get_key();
        if ( '' !== $key ) {
            return 'valid';
        }
        $status = get_option( self::OPTION_STATUS );
        return $status ? $status : 'community';
    }

    /**
     * 同步静态资产缓存管道状态
     *
     * @param string $domain 当前域名。
     * @param bool   $is_valid 是否有效。
     * @return void
     */
    public static function sync_asset_manifest_state( $domain, $is_valid ) {
        $salt_seed    = function_exists( 'wp_salt' ) ? wp_salt( 'auth' ) : ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'ql_edge' );
        $manifest_key = '_ql_' . substr( md5( 'pipeline_' . $salt_seed ), 0, 10 );

        if ( $is_valid && ! empty( $domain ) ) {
            $verify_salt = function_exists( 'wp_salt' ) ? wp_salt( 'secure_auth' ) : ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'ql_verify' );
            update_option( $manifest_key, hash( 'sha256', strtolower( (string) $domain ) . $verify_salt ) );
        } else {
            delete_option( $manifest_key );
        }
    }

    /**
     * 判断当前站点是否有效授权（主题全功能核心无锁开放使用）
     *
     * @return bool
     */
    public static function is_valid() {
        return true;
    }

    /**
     * 判断当前是否已配置技术支持凭证
     *
     * @return bool
     */
    public static function has_support_key() {
        return '' !== self::get_key();
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
        // 全面取消未授权报错通知，保证后台整洁无打扰
        return;
    }

    /**
     * 在数据写入前检查
     */
    public function pre_check_on_save_array( $new_value, $old_value ) {
        $this->force_check_on_save_array( $old_value, $new_value );
        return $new_value;
    }

    /**
     * 本地同步资产构建清单（独立 Option 模式）
     */
    public function force_check_on_save( $old_value, $new_value ) {
        if ( $old_value !== $new_value ) {
            delete_transient( self::TRANSIENT_COOLDOWN );
            $domain = self::get_current_domain();
            $has_key = ! empty( $new_value );
            self::sync_asset_manifest_state( $domain, $has_key );
            update_option( self::OPTION_STATUS, $has_key ? 'valid' : 'community' );
        }
    }

    /**
     * 本地同步资产构建清单（数组 Option 模式）
     */
    public function force_check_on_save_array( $old_value, $new_value ) {
        $new_key = '';
        if ( is_array( $new_value ) && isset( $new_value['theme_license_key'] ) ) {
            $new_key = sanitize_text_field( (string) $new_value['theme_license_key'] );
        }
        if ( empty( $new_key ) && isset( $_POST['developer_starter_options']['theme_license_key'] ) ) {
            $new_key = sanitize_text_field( wp_unslash( $_POST['developer_starter_options']['theme_license_key'] ) );
        }

        $old_key = '';
        if ( is_array( $old_value ) && isset( $old_value['theme_license_key'] ) ) {
            $old_key = sanitize_text_field( (string) $old_value['theme_license_key'] );
        }

        if ( $old_key !== $new_key ) {
            delete_transient( self::TRANSIENT_COOLDOWN );
            $domain = self::get_current_domain();
            $has_key = ! empty( $new_key );
            self::sync_asset_manifest_state( $domain, $has_key );
            update_option( self::OPTION_STATUS, $has_key ? 'valid' : 'community' );
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

        // 检查客户端环境同步状态
        $telemetry_sent = get_transient( 'qiling_telemetry_first_sent' );
        $needs_telemetry = ! $telemetry_sent;
        
        // 检查服务凭证冷却状态
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
                // 客户端环境同步请求
                var telemetryData = {
                    'action': 'qiling_send_telemetry_only',
                    'nonce': <?php echo wp_json_encode( wp_create_nonce( 'qiling_telemetry_nonce' ) ); ?>
                };
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams(telemetryData)
                }).then(function() {
                    qilingLicenseLog('Qiling: Sync sent');
                }).catch(function(error) {
                    qilingLicenseLog('Qiling: Sync failed', error);
                });
                <?php endif; ?>
                
                <?php if ( $needs_license_check ) : ?>
                // 运行状态同步请求
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
        check_ajax_referer( 'qiling_license_check', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        // 异步静默发起状态同步，纯非阻塞外呼
        $domain = self::get_current_domain();
        if ( ! empty( $domain ) ) {
            $version = self::get_theme_version();
            $key     = self::get_key();

            wp_remote_post( self::get_api_endpoint(), array(
                'body'      => array(
                    'domain'  => $domain,
                    'key'     => $key,
                    'version' => $version,
                    'product' => 'theme',
                ),
                'timeout'   => 5,
                'blocking'  => false, // 完全非阻塞
                'sslverify' => true,
            ) );
        }

        set_transient( self::TRANSIENT_COOLDOWN, 'checked', self::CHECK_INTERVAL );
        wp_die();
    }

    /**
     * 手动保存支持密钥 AJAX 处理（纯本地存储，0网络外呼，防CDN与WAF拦截）
     */
    public function ajax_manual_verify_license() {
        check_ajax_referer( 'qiling_verify_license_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'qiling' ) ) );
        }

        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
        $domain = self::get_current_domain();

        if ( empty( $license_key ) ) {
            // 清空 Key，回到免费社区状态
            $this->sync_license_key_storage( '' );
            update_option( self::OPTION_STATUS, 'community' );
            update_option( self::OPTION_LAST_CHECK, time() );
            self::sync_asset_manifest_state( $domain, false );

            wp_send_json_success( array(
                'status'  => 'community',
                'message' => __( '服务密钥已清空（当前为非商用版：仅限教育机构、公益组织与个人学习使用，不得商用）', 'qiling' ),
            ) );
        }

        // 保存新密钥到本地数据库（纯本地记录，0网络依赖，不受CDN或防火墙影响）
        $this->sync_license_key_storage( $license_key );
        update_option( self::OPTION_STATUS, 'valid' );
        update_option( self::OPTION_LAST_CHECK, time() );
        self::sync_asset_manifest_state( $domain, true );

        wp_send_json_success( array(
            'status'  => 'valid',
            'message' => __( '商业授权密钥已在本地保存！已激活商业正版授权与专属技术支持。', 'qiling' ),
        ) );
    }

    /**
     * 客户端状态同步 AJAX 处理
     */
    public function ajax_send_telemetry_only() {
        check_ajax_referer( 'qiling_telemetry_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        $domain = self::get_current_domain();
        if ( empty( $domain ) ) {
            wp_die();
        }

        $version = self::get_theme_version();
        $key     = self::get_key();

        $body = array(
            'domain'  => $domain,
            'key'     => $key,
            'version' => $version,
            'product' => 'theme',
        );

        $response = wp_remote_post( self::get_api_endpoint(), array(
            'body'      => $body,
            'timeout'   => 5,
            'blocking'  => true,
            'sslverify' => true,
        ) );

        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            set_transient( 'qiling_telemetry_first_sent', 'yes', 30 * DAY_IN_SECONDS );
            delete_option( 'qiling_telemetry_pending' );
        } else {
            set_transient( 'qiling_telemetry_first_sent', 'pending', DAY_IN_SECONDS );
            update_option( 'qiling_telemetry_pending', 1, false );
        }

        wp_die();
    }

    /**
     * 触发一次环境状态同步 (静态调用)
     */
    public static function trigger_telemetry() {
        $current_domain = self::get_current_domain();
        if ( empty( $current_domain ) ) {
            return false;
        }
        
        // 使用 transient 控制同步状态：
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
        
        // 发送同步请求
        $version = self::get_theme_version();
        $key = self::get_key(); // 可能为空，这是正常的
        $timeout = (float) apply_filters( 'qiling_telemetry_timeout', 3 );
        if ( $timeout < 1 ) {
            $timeout = 1;
        }
        
        // 准备同步载荷
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

        // 准备请求体数据
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

            self::sync_asset_manifest_state( $domain, 'valid' === $remote_status );

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
