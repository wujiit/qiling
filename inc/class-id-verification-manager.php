<?php
/**
 * ID Verification Manager Class - 身份证实名制管理
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ID_Verification_Manager {

    const DB_VERSION = '1.2.0';
    const DB_VERSION_OPTION = 'qiling_id_verification_db_version';
    const DB_MIGRATION_LOCK = 'qiling_id_verification_db_migration_lock';

    /**
     * 数据表名
     */
    private $table_name;

    /**
     * 构造函数。
     *
     * @param bool $register_hooks 是否注册运行时钩子。
     */
    public function __construct( $register_hooks = true ) {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'qiling_id_verifications';

        if ( ! $register_hooks ) {
            return;
        }

        // 注册 REST API 路由
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // 强制实名验证跳转
        add_action( 'template_redirect', array( $this, 'force_verification_redirect' ), 1 );

        // 检查并更新数据表结构（支持加密字段）
        add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );
        add_action( 'admin_init', array( $this, 'check_upgrade_schema' ) );
    }

    /**
     * 主题启用时创建/升级数据表。
     */
    public function install_table() {
        $this->run_schema_migration( true );
    }

    /**
     * 运行带锁的表结构迁移，不注册前台/REST 运行时钩子。
     *
     * @param bool $force 是否强制执行安装期迁移。
     * @return void
     */
    public static function run_locked_migration( $force = false ) {
        $manager = new self( false );
        $manager->run_schema_migration( $force );
    }

    /**
     * 检查更新数据表结构
     */
    public function check_upgrade_schema() {
        if ( ! Database_Schema_Migration_Service::can_run_admin_migration() ) {
            return;
        }

        $this->run_schema_migration();
    }

    private function run_schema_migration( $force = false ) {
        Database_Schema_Migration_Service::run(
            array(
                'version_option'     => self::DB_VERSION_OPTION,
                'target_version'     => self::DB_VERSION,
                'default_version'    => '1.0.0',
                'version_compare'    => 'version_less',
                'lock_option'        => self::DB_MIGRATION_LOCK,
                'force'              => $force,
                'migration_callback' => function () {
                    self::create_table();
                    $this->migrate_plaintext_mobile_storage();
                },
            )
        );
    }

    /**
     * 加密 PII 数据
     */
    private function encrypt_pii( $data ) {
        $data = (string) $data;
        if ( $data === '' ) {
            return '';
        }

        if ( ! function_exists( 'openssl_encrypt' ) ) {
            return '';
        }

        $method = 'AES-256-CBC';
        $iv = '';
        if ( function_exists( 'random_bytes' ) ) {
            try {
                $iv = random_bytes( 16 );
            } catch ( \Exception $e ) {
                $iv = '';
            }
        }
        if ( $iv === '' && function_exists( 'openssl_random_pseudo_bytes' ) ) {
            $iv = (string) openssl_random_pseudo_bytes( 16 );
        }

        if ( strlen( $iv ) !== 16 ) {
            return $this->encrypt_pii_legacy( $data );
        }

        $enc_key = hash( 'sha256', wp_salt( 'auth' ) . '|qiling|pii|enc', true );
        $mac_key = hash( 'sha256', wp_salt( 'secure_auth' ) . '|qiling|pii|mac', true );
        $cipher_raw = openssl_encrypt( $data, $method, $enc_key, OPENSSL_RAW_DATA, $iv );
        if ( ! is_string( $cipher_raw ) || $cipher_raw === '' ) {
            return $this->encrypt_pii_legacy( $data );
        }

        $mac = hash_hmac( 'sha256', $iv . $cipher_raw, $mac_key, true );
        return 'qpii2:' . base64_encode( $iv . $mac . $cipher_raw );
    }

    /**
     * 兼容旧格式的 PII 加密（固定 IV，历史数据保留解密能力）。
     */
    private function encrypt_pii_legacy( $data ) {
        $data = (string) $data;
        if ( $data === '' || ! function_exists( 'openssl_encrypt' ) ) {
            return '';
        }

        $method = 'AES-256-CBC';
        $key = substr( wp_salt( 'auth' ), 0, 32 );
        $iv = substr( wp_salt( 'secure_auth' ), 0, 16 );
        $encrypted = openssl_encrypt( $data, $method, $key, 0, $iv );
        if ( ! is_string( $encrypted ) || $encrypted === '' ) {
            return '';
        }

        return base64_encode( $encrypted );
    }

    /**
     * 解密 PII 数据（支持 qpii2 新格式 + 历史固定 IV 格式）。
     *
     * @param string $data 密文或明文
     * @return string
     */
    public static function decrypt_pii_value( $data ) {
        $data = is_string( $data ) ? $data : '';
        if ( $data === '' ) {
            return '';
        }

        if ( strpos( $data, 'qpii2:' ) === 0 ) {
            if ( ! function_exists( 'openssl_decrypt' ) ) {
                return '';
            }

            $payload = substr( $data, 6 );
            $bin = base64_decode( $payload, true );
            if ( ! is_string( $bin ) || strlen( $bin ) <= 48 ) {
                return '';
            }

            $iv = substr( $bin, 0, 16 );
            $mac = substr( $bin, 16, 32 );
            $cipher_raw = substr( $bin, 48 );
            if ( strlen( $iv ) !== 16 || $cipher_raw === '' ) {
                return '';
            }

            $mac_key = hash( 'sha256', wp_salt( 'secure_auth' ) . '|qiling|pii|mac', true );
            $calc_mac = hash_hmac( 'sha256', $iv . $cipher_raw, $mac_key, true );
            $mac_ok = function_exists( 'hash_equals' ) ? hash_equals( $mac, $calc_mac ) : ( $mac === $calc_mac );
            if ( ! $mac_ok ) {
                return '';
            }

            $enc_key = hash( 'sha256', wp_salt( 'auth' ) . '|qiling|pii|enc', true );
            $decrypted = openssl_decrypt( $cipher_raw, 'AES-256-CBC', $enc_key, OPENSSL_RAW_DATA, $iv );
            return is_string( $decrypted ) ? $decrypted : '';
        }

        // 历史明文数据：非 base64 外观则按明文返回。
        if ( ! preg_match( '/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $data ) ) {
            return $data;
        }

        // 历史格式（固定 IV + 双层 base64 包裹）
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return $data;
        }
        $legacy_cipher = base64_decode( $data, true );
        if ( ! is_string( $legacy_cipher ) || $legacy_cipher === '' ) {
            return $data;
        }

        $legacy_key = substr( wp_salt( 'auth' ), 0, 32 );
        $legacy_iv = substr( wp_salt( 'secure_auth' ), 0, 16 );
        $legacy_plain = openssl_decrypt( $legacy_cipher, 'AES-256-CBC', $legacy_key, 0, $legacy_iv );
        return ( $legacy_plain !== false && is_string( $legacy_plain ) ) ? $legacy_plain : $data;
    }

    /**
     * 解密 PII 数据
     */
    private function decrypt_pii( $data ) {
        return self::decrypt_pii_value( $data );
    }

    /**
     * 判断是否是中国大陆手机号明文。
     *
     * @param string $mobile 手机号。
     * @return bool
     */
    private static function is_plain_mobile_value( $mobile ) {
        return is_string( $mobile ) && preg_match( '/^1[3-9]\d{9}$/', $mobile );
    }

    /**
     * 手机号脱敏显示。
     *
     * @param string $mobile 手机号。
     * @return string
     */
    public static function mask_mobile_value( $mobile ) {
        $mobile = (string) $mobile;
        return strlen( $mobile ) >= 7 ? substr_replace( $mobile, '******', 3, 6 ) : $mobile;
    }

    /**
     * 从验证记录中获取可供管理员查看的手机号。
     *
     * @param object|array $record 验证记录。
     * @return string
     */
    public static function get_record_mobile_value( $record ) {
        $encrypted_mobile = '';
        $legacy_mobile    = '';

        if ( is_array( $record ) ) {
            $encrypted_mobile = isset( $record['mobile_encrypted'] ) ? (string) $record['mobile_encrypted'] : '';
            $legacy_mobile    = isset( $record['mobile'] ) ? (string) $record['mobile'] : '';
        } elseif ( is_object( $record ) ) {
            $encrypted_mobile = isset( $record->mobile_encrypted ) ? (string) $record->mobile_encrypted : '';
            $legacy_mobile    = isset( $record->mobile ) ? (string) $record->mobile : '';
        }

        $mobile = self::decrypt_pii_value( $encrypted_mobile );
        if ( '' !== $mobile ) {
            return $mobile;
        }

        return self::decrypt_pii_value( $legacy_mobile );
    }

    /**
     * 迁移旧版本明文手机号存储。
     *
     * @return void
     */
    private function migrate_plaintext_mobile_storage() {
        global $wpdb;

        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM {$this->table_name} LIKE %s",
                'mobile_encrypted'
            )
        );
        if ( ! $column_exists ) {
            return;
        }

        $records = $wpdb->get_results( "SELECT id, mobile, mobile_encrypted FROM {$this->table_name} WHERE mobile <> '' ORDER BY id ASC" );
        if ( is_array( $records ) ) {
            foreach ( $records as $record ) {
                $mobile = self::get_record_mobile_value( $record );
                if ( ! self::is_plain_mobile_value( $mobile ) ) {
                    continue;
                }

                $update = array();
                if ( empty( $record->mobile_encrypted ) ) {
                    $encrypted_mobile = $this->encrypt_pii( $mobile );
                    if ( '' !== $encrypted_mobile ) {
                        $update['mobile_encrypted'] = $encrypted_mobile;
                    }
                }

                if ( (string) $record->mobile === $mobile ) {
                    $update['mobile'] = self::mask_mobile_value( $mobile );
                }

                if ( ! empty( $update ) ) {
                    $wpdb->update( $this->table_name, $update, array( 'id' => absint( $record->id ) ) );
                }
            }
        }

        $meta_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value <> ''",
                'qiling_id_mobile'
            )
        );
        if ( ! is_array( $meta_rows ) ) {
            return;
        }

        foreach ( $meta_rows as $meta_row ) {
            $mobile = (string) $meta_row->meta_value;
            if ( ! self::is_plain_mobile_value( $mobile ) ) {
                continue;
            }

            $encrypted_mobile = $this->encrypt_pii( $mobile );
            if ( '' !== $encrypted_mobile ) {
                update_user_meta( absint( $meta_row->user_id ), 'qiling_id_mobile', $encrypted_mobile );
            }
        }
    }

    /**
     * 获取选项
     */
    private function get_option( $key, $default = '' ) {
        $options = get_option( 'developer_starter_options', array() );
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    /**
     * 归一化实名姓名参数，避免已编码值在请求链路中被再次编码。
     *
     * @param mixed $name 姓名输入。
     * @return string
     */
    private function normalize_verification_name( $name ) {
        $name = trim( sanitize_text_field( (string) $name ) );
        if ( '' === $name ) {
            return '';
        }

        if ( preg_match( '/%[0-9A-Fa-f]{2}/', $name ) ) {
            // 处理已编码或双重编码姓名（例如 %E5... / %25E5...）。
            $decoded = rawurldecode( $name );
            if ( is_string( $decoded ) && $decoded !== '' ) {
                $name = $decoded;
            }

            if ( preg_match( '/%[0-9A-Fa-f]{2}/', $name ) ) {
                $decoded_again = rawurldecode( $name );
                if ( is_string( $decoded_again ) && $decoded_again !== '' ) {
                    $name = $decoded_again;
                }
            }
        }

        return trim( sanitize_text_field( $name ) );
    }

    /**
     * 校验并加密实名认证需要持久化的敏感字段。
     *
     * @param string $name   实名姓名。
     * @param string $idcard 身份证号。
     * @param string $mobile 手机号。
     * @return array<string,string>|\WP_Error
     */
    private function encrypt_verification_pii_payload( $name, $idcard, $mobile ) {
        $name   = (string) $name;
        $idcard = (string) $idcard;
        $mobile = (string) $mobile;

        $encrypted_name   = $this->encrypt_pii( $name );
        $encrypted_idcard = $this->encrypt_pii( $idcard );
        $encrypted_mobile = $this->encrypt_pii( $mobile );

        if (
            ( '' !== $name && '' === $encrypted_name ) ||
            ( '' !== $idcard && '' === $encrypted_idcard ) ||
            ( '' !== $mobile && '' === $encrypted_mobile )
        ) {
            return new \WP_Error(
                'pii_storage_unavailable',
                __( '服务器暂时无法安全保存实名资料，请联系管理员检查 OpenSSL 配置后重试。', 'developer-starter' ),
                array( 'status' => 500 )
            );
        }

        return array(
            'name'   => $encrypted_name,
            'idcard' => $encrypted_idcard,
            'mobile' => $encrypted_mobile,
        );
    }

    /**
     * 检查功能是否启用
     */
    public function is_enabled() {
        return $this->get_option( 'id_verification_enable', '' ) === '1';
    }

    /**
     * 创建数据表
     */
    public static function create_table() {
        Database_Schema_Migration_Service::apply_schema( self::get_table_schema() );
    }

    private static function get_table_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'qiling_id_verifications';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            name TEXT NOT NULL,
            mobile VARCHAR(20) NOT NULL,
            mobile_encrypted TEXT NOT NULL,
            idcard TEXT NOT NULL,
            channel VARCHAR(20) DEFAULT '',
            sex VARCHAR(10) DEFAULT '',
            birthday VARCHAR(10) DEFAULT '',
            address VARCHAR(255) DEFAULT '',
            ip_address VARCHAR(45) DEFAULT '',
            result VARCHAR(20) NOT NULL,
            verification_time DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY verification_time (verification_time)
        ) $charset_collate;";
    }

    /**
     * 注册 REST API 路由
     */
    public function register_rest_routes() {
        $controller = new ID_Verification_REST_Controller( $this );
        $controller->register_routes();
    }

    /**
     * 清洗实名认证姓名参数。
     */
    public function sanitize_verification_name_arg( $value, $request = null, $param = '' ) {
        return $this->normalize_verification_name( $value );
    }

    /**
     * 校验实名认证姓名参数。
     */
    public function validate_verification_name_arg( $value, $request = null, $param = '' ) {
        $name = $this->normalize_verification_name( $value );
        $length = function_exists( 'mb_strlen' ) ? mb_strlen( $name ) : strlen( $name );

        if ( '' === $name ) {
            return new \WP_Error( 'invalid_name', __( '姓名不能为空', 'developer-starter' ), array( 'status' => 400 ) );
        }

        if ( $length > 60 ) {
            return new \WP_Error( 'invalid_name_length', __( '姓名长度过长', 'developer-starter' ), array( 'status' => 400 ) );
        }

        return true;
    }

    /**
     * 清洗手机号参数。
     */
    public function sanitize_verification_mobile_arg( $value, $request = null, $param = '' ) {
        $mobile = preg_replace( '/\D+/', '', sanitize_text_field( (string) $value ) );
        return is_string( $mobile ) ? $mobile : '';
    }

    /**
     * 校验手机号参数。
     */
    public function validate_verification_mobile_arg( $value, $request = null, $param = '' ) {
        $mobile = $this->sanitize_verification_mobile_arg( $value );
        if ( ! preg_match( '/^1[3-9]\d{9}$/', $mobile ) ) {
            return new \WP_Error( 'invalid_mobile', __( '手机号格式不正确', 'developer-starter' ), array( 'status' => 400 ) );
        }

        return true;
    }

    /**
     * 清洗身份证号参数。
     */
    public function sanitize_verification_idcard_arg( $value, $request = null, $param = '' ) {
        $idcard = strtoupper( sanitize_text_field( (string) $value ) );
        $idcard = preg_replace( '/\s+/', '', $idcard );
        return is_string( $idcard ) ? $idcard : '';
    }

    /**
     * 校验身份证号参数。
     */
    public function validate_verification_idcard_arg( $value, $request = null, $param = '' ) {
        $idcard = $this->sanitize_verification_idcard_arg( $value );
        if ( ! preg_match( '/^\d{17}[\dX]$/', $idcard ) ) {
            return new \WP_Error( 'invalid_idcard', __( '身份证号格式不正确', 'developer-starter' ), array( 'status' => 400 ) );
        }

        return true;
    }

    /**
     * 校验正整数 REST 参数。
     */
    public function validate_positive_integer_arg( $value, $request = null, $param = '' ) {
        if ( absint( $value ) <= 0 ) {
            return new \WP_Error( 'invalid_record_id', __( '记录 ID 无效', 'developer-starter' ), array( 'status' => 400 ) );
        }

        return true;
    }

    /**
     * 处理验证请求
     */
    public function handle_verification( $request ) {
        if ( ! $this->is_enabled() ) {
            return new \WP_Error( 'disabled', __( '实名认证功能未启用', 'developer-starter' ), array( 'status' => 403 ) );
        }

        $user_id = get_current_user_id();
        $name = $this->normalize_verification_name( $request->get_param( 'name' ) );
        $mobile = $this->sanitize_verification_mobile_arg( $request->get_param( 'mobile' ) );
        $idcard = $this->sanitize_verification_idcard_arg( $request->get_param( 'idcard' ) );
        $ip_address = developer_starter_get_client_ip();

        // 验证输入
        if ( empty( $name ) || empty( $mobile ) || empty( $idcard ) ) {
            return new \WP_Error( 'invalid_input', __( '所有字段均为必填', 'developer-starter' ), array( 'status' => 400 ) );
        }

        // 验证手机号格式
        if ( ! preg_match( '/^1[3-9]\d{9}$/', $mobile ) ) {
            return new \WP_Error( 'invalid_mobile', __( '手机号格式不正确', 'developer-starter' ), array( 'status' => 400 ) );
        }

        // 验证身份证格式（简单验证18位）
        if ( ! preg_match( '/^\d{17}[\dXx]$/', $idcard ) ) {
            return new \WP_Error( 'invalid_idcard', __( '身份证号格式不正确', 'developer-starter' ), array( 'status' => 400 ) );
        }

        // 检查每日尝试次数（优化查询）
        $max_attempts = (int) $this->get_option( 'id_verification_max_attempts', 3 );
        $current_timestamp = current_time( 'timestamp' );
        if ( function_exists( 'wp_date' ) ) {
            $today_start = wp_date( 'Y-m-d 00:00:00', $current_timestamp );
            $today_end   = wp_date( 'Y-m-d 23:59:59', $current_timestamp );
        } else {
            // 兼容兜底：date_i18n 使用 WordPress 时区，不走服务器默认时区。
            $today_start = date_i18n( 'Y-m-d 00:00:00', $current_timestamp );
            $today_end   = date_i18n( 'Y-m-d 23:59:59', $current_timestamp );
        }
        global $wpdb;
        $attempts = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND verification_time BETWEEN %s AND %s",
            $user_id,
            $today_start,
            $today_end
        ) );

        if ( $attempts >= $max_attempts ) {
            return new \WP_Error( 'max_attempts', __( '今日验证次数已达上限，请明天再试', 'developer-starter' ), array( 'status' => 429 ) );
        }

        // 加密处理 PII
        $encrypted_payload = $this->encrypt_verification_pii_payload( $name, $idcard, $mobile );
        if ( is_wp_error( $encrypted_payload ) ) {
            return $encrypted_payload;
        }

        $encrypted_name   = $encrypted_payload['name'];
        $encrypted_idcard = $encrypted_payload['idcard'];
        $encrypted_mobile = $encrypted_payload['mobile'];

        // 调用API验证
        $result = $this->call_api( $name, $mobile, $idcard );
        $verification_result = $result['success'] && isset( $result['data']['result'] ) && $result['data']['result'] === '0' ? '成功' : '失败';

        // 插入验证记录
        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'user_id'           => $user_id,
                'name'              => $encrypted_name,
                'mobile'            => self::mask_mobile_value( $mobile ),
                'mobile_encrypted'  => $encrypted_mobile,
                'idcard'            => $encrypted_idcard,
                'channel'           => $result['data']['channel'] ?? '',
                'sex'               => $result['data']['sex'] ?? '',
                'birthday'          => $result['data']['birthday'] ?? '',
                'address'           => $result['data']['address'] ?? '',
                'ip_address'        => $ip_address,
                'result'            => $verification_result,
                'verification_time' => current_time( 'mysql' ),
            )
        );

        if ( false === $inserted ) {
            return new \WP_Error(
                'verification_store_failed',
                __( '实名认证结果已返回，但验证记录保存失败，请稍后重试或联系管理员。', 'developer-starter' ),
                array( 'status' => 500 )
            );
        }

        // 更新用户 meta
        if ( $verification_result === '成功' ) {
            update_user_meta( $user_id, 'qiling_id_verified', true );
            update_user_meta( $user_id, 'qiling_id_name', $encrypted_name );
            update_user_meta( $user_id, 'qiling_id_mobile', $encrypted_mobile );
            update_user_meta( $user_id, 'qiling_id_idcard', $encrypted_idcard );
        }

        $response_success = $result['success'] && $verification_result === '成功';
        return array(
            'success' => $response_success,
            'message' => $response_success
                ? __( '验证成功', 'developer-starter' )
                : __( '验证失败，请检查信息是否正确或稍后重试', 'developer-starter' ),
            'data'    => array(
                'verified' => $response_success,
                'status'   => $response_success ? 'success' : 'failed',
            ),
            'time'    => current_time( 'mysql' ),
        );
    }

    /**
     * 调用数链云API
     */
    private function call_api( $name, $mobile, $idcard ) {
        $api_url = trim( (string) $this->get_option( 'id_verification_api_url', 'https://slytransf.market.alicloudapi.com/mobile_transfer' ) );
        $appcode = trim( (string) $this->get_option( 'id_verification_appcode', '' ) );
        $ssl_verify = $this->get_option( 'id_verification_ssl_verify', '1' ) === '1';

        if ( empty( $appcode ) ) {
            return array(
                'success' => false,
                'msg'     => __( 'AppCode 未配置', 'developer-starter' ),
                'code'    => 500,
                'data'    => array(),
            );
        }

        if ( empty( $api_url ) ) {
            return array(
                'success' => false,
                'msg'     => __( 'API 地址未配置', 'developer-starter' ),
                'code'    => 500,
                'data'    => array(),
            );
        }

        $payload = array(
            'idcard' => $idcard,
            'mobile' => $mobile,
            'name'   => $name,
        );

        // 数链云三要素接口按官方文档固定走 GET。
        return $this->send_api_request( 'get', $api_url, $appcode, $ssl_verify, $payload );
    }

    /**
     * 发送实名认证 API 请求
     */
    private function send_api_request( $method, $api_url, $appcode, $ssl_verify, $payload ) {
        $method = strtolower( (string) $method );
        $headers = array(
            'Authorization' => 'APPCODE ' . $appcode,
            'Accept'        => 'application/json',
        );
        $args = array(
            'headers'   => $headers,
            'timeout'   => 30,
            'sslverify' => $ssl_verify,
        );

        if ( 'post' === $method ) {
            $args['headers']['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            $args['body'] = $payload;
            $response = wp_remote_post( $api_url, $args );
        } else {
            $url = add_query_arg( $payload, $api_url );
            $response = wp_remote_get( $url, $args );
        }

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'msg'     => __( 'API请求失败：', 'developer-starter' ) . $response->get_error_message(),
                'code'    => 500,
                'data'    => array(),
            );
        }

        $status_code = (int) wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! is_array( $data ) ) {
            return array(
                'success' => false,
                'msg'     => __( 'API响应解析失败', 'developer-starter' ),
                'code'    => $status_code > 0 ? $status_code : 500,
                'data'    => array(),
            );
        }

        if ( ! isset( $data['code'] ) && $status_code > 0 ) {
            $data['code'] = $status_code;
        }
        if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
            $data['data'] = array();
        }
        if ( ! isset( $data['success'] ) ) {
            $data['success'] = array_key_exists( 'result', $data['data'] ) && $status_code < 400;
        }

        return $data;
    }

    /**
     * 获取用户验证状态
     */
    public function get_user_status( $request ) {
        $user_id = get_current_user_id();
        $verified = get_user_meta( $user_id, 'qiling_id_verified', true );

        if ( $verified ) {
            // 获取并解密数据
            $name_enc = get_user_meta( $user_id, 'qiling_id_name', true );
            $mobile_enc = get_user_meta( $user_id, 'qiling_id_mobile', true );
            $idcard_enc = get_user_meta( $user_id, 'qiling_id_idcard', true );
            
            $name = $this->decrypt_pii( $name_enc );
            $mobile = $this->decrypt_pii( $mobile_enc );
            $idcard = $this->decrypt_pii( $idcard_enc );

            if ( self::is_plain_mobile_value( (string) $mobile_enc ) && self::is_plain_mobile_value( $mobile ) ) {
                $encrypted_mobile = $this->encrypt_pii( $mobile );
                if ( '' !== $encrypted_mobile ) {
                    update_user_meta( $user_id, 'qiling_id_mobile', $encrypted_mobile );
                }
            }

            $name_display = mb_strlen( $name ) > 0 ? mb_substr( $name, 0, 1 ) . str_repeat( '*', max( 0, mb_strlen( $name ) - 1 ) ) : '';
            $mobile_display = self::mask_mobile_value( $mobile );
            $idcard_display = strlen( $idcard ) >= 14 ? substr_replace( $idcard, '**********', 4, 10 ) : $idcard;

            return array(
                'verified'    => true,
                'name'        => $name_display,
                'mobile'      => $mobile_display,
                'idcard'      => $idcard_display,
            );
        }

        return array( 'verified' => false );
    }

    /**
     * 删除验证记录
     */
    public function delete_record( $request ) {
        global $wpdb;
        $id = absint( $request->get_param( 'id' ) );

        $deleted = $wpdb->delete( $this->table_name, array( 'id' => $id ) );

        return array( 'success' => $deleted !== false );
    }

    /**
     * 强制实名验证跳转
     */
    public function force_verification_redirect() {
        // 未登录、后台、首页不处理
        if ( ! is_user_logged_in() || is_admin() || is_front_page() ) {
            return;
        }

        // 检查是否启用强制验证
        $force_verification = $this->get_option( 'id_verification_force', '' );
        if ( $force_verification !== '1' ) {
            return;
        }

        // 功能未启用也不处理
        if ( ! $this->is_enabled() ) {
            return;
        }

        $user_id = get_current_user_id();
        $verified = get_user_meta( $user_id, 'qiling_id_verified', true );

        if ( ! $verified ) {
            // 获取个人中心页面
            $account_page_id = $this->get_account_page_id();
            
            if ( $account_page_id ) {
                // 如果当前页面就是个人中心页面，不进行跳转，防止死循环
                if ( is_page( $account_page_id ) ) {
                    return;
                }
                
                $account_url = get_permalink( $account_page_id );
                // 跳转到实名认证页面
                wp_redirect( add_query_arg( 'tab', 'verification', $account_url ) );
                exit;
            }
        }
    }

    /**
     * 获取个人中心页面ID
     */
    private function get_account_page_id() {
        $pages = get_posts( array(
            'post_type'   => 'page',
            'post_status' => 'publish',
            'meta_key'    => '_wp_page_template',
            'meta_value'  => 'templates/template-account.php',
            'numberposts' => 1,
            'fields'      => 'ids',
        ) );

        return ! empty( $pages ) ? $pages[0] : 0;
    }

    /**
     * 获取验证记录
     */
    public function get_records( $per_page = 20, $paged = 1 ) {
        global $wpdb;
        $offset = ( $paged - 1 ) * $per_page;

        $total_records = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
        $records = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY verification_time DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ) );
        
        // 解密记录中的数据
        foreach ( $records as $key => $record ) {
            $records[$key]->name = $this->decrypt_pii( $record->name );
            $records[$key]->mobile = self::get_record_mobile_value( $record );
            $records[$key]->idcard = $this->decrypt_pii( $record->idcard );
        }

        return array(
            'records' => $records,
            'total'   => $total_records,
            'pages'   => ceil( $total_records / $per_page ),
        );
    }

    /**
     * 检查用户是否已验证
     */
    public static function is_user_verified( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        return (bool) get_user_meta( $user_id, 'qiling_id_verified', true );
    }
}
