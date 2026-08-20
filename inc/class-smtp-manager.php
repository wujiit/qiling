<?php
/**
 * SMTP Manager Class - 邮件发送配置
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SMTP_Manager {

    /**
     * 旧版固定盐值，仅用于解密历史 enc: 数据。
     */
    private static $salt = 'developer_starter_smtp_v1';

    const PASSWORD_PREFIX_V2 = 'enc:v2:';
    const PASSWORD_PREFIX_LEGACY = 'enc:';
    const PASSWORD_CIPHER = 'aes-256-gcm';
    const PASSWORD_ALG_OPENSSL = 'AES-256-GCM';
    const PASSWORD_ALG_SODIUM = 'XCHACHA20-POLY1305-IETF';
    const PASSWORD_AAD = 'developer_starter_smtp_password_v2';

    public function __construct() {
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        add_filter( 'wp_mail_from', array( $this, 'set_from_email' ) );
        add_filter( 'wp_mail_from_name', array( $this, 'set_from_name' ) );
    }

    /**
     * 加密密码
     *
     * @param string $password 明文密码
     * @return string 加密后的密码
     */
    public static function encrypt_password( $password ) {
        $password = (string) $password;
        if ( '' === $password ) {
            return '';
        }

        if ( self::is_encrypted_password( $password ) ) {
            return $password;
        }

        $encrypted = self::encrypt_password_v2( $password );
        if ( '' !== $encrypted ) {
            return $encrypted;
        }

        return '';
    }

    /**
     * 解密密码
     *
     * @param string $encrypted 加密的密码
     * @return string 明文密码
     */
    public static function decrypt_password( $encrypted ) {
        $encrypted = (string) $encrypted;
        if ( '' === $encrypted ) {
            return '';
        }

        if ( 0 === strpos( $encrypted, self::PASSWORD_PREFIX_V2 ) ) {
            return self::decrypt_password_v2( $encrypted );
        }

        if ( 0 === strpos( $encrypted, self::PASSWORD_PREFIX_LEGACY ) ) {
            return self::decrypt_password_legacy( $encrypted );
        }

        // 兼容早期明文数据。
        return $encrypted;
    }

    /**
     * 将历史密码格式升级到 v2 认证加密格式。
     *
     * @param string $stored_password 已保存的密码值。
     * @return string
     */
    public static function maybe_upgrade_password( $stored_password ) {
        $stored_password = (string) $stored_password;
        if ( '' === $stored_password || 0 === strpos( $stored_password, self::PASSWORD_PREFIX_V2 ) ) {
            return $stored_password;
        }

        $plain_password = self::decrypt_password( $stored_password );
        if ( '' === $plain_password ) {
            return $stored_password;
        }

        $upgraded = self::encrypt_password_v2( $plain_password );
        return '' !== $upgraded ? $upgraded : $stored_password;
    }

    /**
     * 检查是否是主题已知的加密格式。
     *
     * @param string $password 密码值。
     * @return bool
     */
    private static function is_encrypted_password( $password ) {
        return 0 === strpos( $password, self::PASSWORD_PREFIX_V2 )
            || 0 === strpos( $password, self::PASSWORD_PREFIX_LEGACY );
    }

    /**
     * 使用认证加密格式加密 SMTP 密码。
     *
     * @param string $password 明文密码。
     * @return string
     */
    private static function encrypt_password_v2( $password ) {
        $encrypted = self::encrypt_password_v2_openssl( $password );
        if ( '' !== $encrypted ) {
            return $encrypted;
        }

        return self::encrypt_password_v2_sodium( $password );
    }

    /**
     * 使用 OpenSSL AES-256-GCM 加密 SMTP 密码。
     *
     * @param string $password 明文密码。
     * @return string
     */
    private static function encrypt_password_v2_openssl( $password ) {
        if ( ! function_exists( 'openssl_encrypt' ) || ! self::supports_password_cipher() ) {
            return '';
        }

        $key = self::get_password_key();
        if ( '' === $key ) {
            return '';
        }

        $iv = self::get_random_bytes( 12 );
        if ( 12 !== strlen( $iv ) ) {
            return '';
        }

        $tag = '';
        $ciphertext = openssl_encrypt(
            (string) $password,
            self::PASSWORD_CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::PASSWORD_AAD,
            16
        );

        if ( ! is_string( $ciphertext ) || '' === $ciphertext || ! is_string( $tag ) || 16 !== strlen( $tag ) ) {
            return '';
        }

        $payload = array(
            'alg' => self::PASSWORD_ALG_OPENSSL,
            'iv'  => base64_encode( $iv ),
            'tag' => base64_encode( $tag ),
            'ct'  => base64_encode( $ciphertext ),
        );

        return self::encode_password_payload_v2( $payload );
    }

    /**
     * 使用 sodium XChaCha20-Poly1305 AEAD 加密 SMTP 密码。
     *
     * @param string $password 明文密码。
     * @return string
     */
    private static function encrypt_password_v2_sodium( $password ) {
        if ( ! function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' ) ) {
            return '';
        }

        $key = self::get_password_key();
        if ( '' === $key ) {
            return '';
        }

        $nonce_length = defined( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES' )
            ? SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES
            : 24;
        $nonce = self::get_random_bytes( $nonce_length );
        if ( $nonce_length !== strlen( $nonce ) ) {
            return '';
        }

        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            (string) $password,
            self::PASSWORD_AAD,
            $nonce,
            $key
        );

        if ( ! is_string( $ciphertext ) || '' === $ciphertext ) {
            return '';
        }

        $payload = array(
            'alg'   => self::PASSWORD_ALG_SODIUM,
            'nonce' => base64_encode( $nonce ),
            'ct'    => base64_encode( $ciphertext ),
        );

        return self::encode_password_payload_v2( $payload );
    }

    /**
     * 解密 v2 认证加密格式密码。
     *
     * @param string $encrypted 加密后的密码。
     * @return string
     */
    private static function decrypt_password_v2( $encrypted ) {
        $data = self::decode_password_payload_v2( $encrypted );
        if ( empty( $data['alg'] ) ) {
            return '';
        }

        if ( self::PASSWORD_ALG_OPENSSL === $data['alg'] ) {
            return self::decrypt_password_v2_openssl( $data );
        }

        if ( self::PASSWORD_ALG_SODIUM === $data['alg'] ) {
            return self::decrypt_password_v2_sodium( $data );
        }

        return '';
    }

    /**
     * 解密 OpenSSL AES-256-GCM 格式密码。
     *
     * @param array<string,string> $data 密文载荷。
     * @return string
     */
    private static function decrypt_password_v2_openssl( $data ) {
        if ( ! function_exists( 'openssl_decrypt' ) || ! self::supports_password_cipher() ) {
            return '';
        }

        $key = self::get_password_key();
        if ( '' === $key ) {
            return '';
        }

        if ( ! isset( $data['iv'], $data['tag'], $data['ct'] ) ) {
            return '';
        }

        $iv = base64_decode( (string) $data['iv'], true );
        $tag = base64_decode( (string) $data['tag'], true );
        $ciphertext = base64_decode( (string) $data['ct'], true );
        if ( ! is_string( $iv ) || 12 !== strlen( $iv ) || ! is_string( $tag ) || 16 !== strlen( $tag ) || ! is_string( $ciphertext ) || '' === $ciphertext ) {
            return '';
        }

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::PASSWORD_CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::PASSWORD_AAD
        );

        return is_string( $decrypted ) ? $decrypted : '';
    }

    /**
     * 解密 sodium XChaCha20-Poly1305 AEAD 格式密码。
     *
     * @param array<string,string> $data 密文载荷。
     * @return string
     */
    private static function decrypt_password_v2_sodium( $data ) {
        if ( ! function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_decrypt' ) ) {
            return '';
        }

        $key = self::get_password_key();
        if ( '' === $key ) {
            return '';
        }

        if ( ! isset( $data['nonce'], $data['ct'] ) ) {
            return '';
        }

        $nonce_length = defined( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES' )
            ? SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES
            : 24;
        $nonce = base64_decode( (string) $data['nonce'], true );
        $ciphertext = base64_decode( (string) $data['ct'], true );
        if ( ! is_string( $nonce ) || $nonce_length !== strlen( $nonce ) || ! is_string( $ciphertext ) || '' === $ciphertext ) {
            return '';
        }

        $decrypted = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            self::PASSWORD_AAD,
            $nonce,
            $key
        );

        return is_string( $decrypted ) ? $decrypted : '';
    }

    /**
     * 编码 v2 密文载荷。
     *
     * @param array<string,string> $payload 密文载荷。
     * @return string
     */
    private static function encode_password_payload_v2( $payload ) {
        $json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $payload ) : json_encode( $payload );
        if ( ! is_string( $json ) || '' === $json ) {
            return '';
        }

        return self::PASSWORD_PREFIX_V2 . base64_encode( $json );
    }

    /**
     * 解码 v2 密文载荷。
     *
     * @param string $encrypted 加密后的密码。
     * @return array<string,string>
     */
    private static function decode_password_payload_v2( $encrypted ) {
        $payload = substr( $encrypted, strlen( self::PASSWORD_PREFIX_V2 ) );
        $json = base64_decode( $payload, true );
        if ( ! is_string( $json ) || '' === $json ) {
            return array();
        }

        $data = json_decode( $json, true );
        return is_array( $data ) ? $data : array();
    }

    /**
     * 解密旧版 enc:base64 格式。
     *
     * @param string $encrypted 加密后的密码。
     * @return string
     */
    private static function decrypt_password_legacy( $encrypted ) {
        if ( 0 !== strpos( $encrypted, self::PASSWORD_PREFIX_LEGACY ) ) {
            return $encrypted;
        }

        $payload = substr( $encrypted, strlen( self::PASSWORD_PREFIX_LEGACY ) );
        $decoded = base64_decode( $payload, true );
        if ( ! is_string( $decoded ) || '' === $decoded ) {
            return '';
        }

        $parts = explode( '|', $decoded, 2 );
        if ( count( $parts ) !== 2 || $parts[0] !== self::$salt ) {
            return '';
        }

        return $parts[1];
    }

    /**
     * 判断当前环境是否支持密码加密算法。
     *
     * @return bool
     */
    private static function supports_password_cipher() {
        if ( ! function_exists( 'openssl_get_cipher_methods' ) ) {
            return false;
        }

        $methods = openssl_get_cipher_methods();
        if ( ! is_array( $methods ) ) {
            return false;
        }

        return in_array( self::PASSWORD_CIPHER, array_map( 'strtolower', $methods ), true );
    }

    /**
     * 派生 SMTP 密码加密密钥。
     *
     * @return string
     */
    private static function get_password_key() {
        if ( ! function_exists( 'wp_salt' ) ) {
            return '';
        }

        $salt_material = wp_salt( 'auth' ) . '|' . wp_salt( 'secure_auth' );
        if ( '' === trim( str_replace( '|', '', $salt_material ) ) ) {
            return '';
        }

        return hash( 'sha256', $salt_material . '|qiling|smtp|password|v2', true );
    }

    /**
     * 获取安全随机字节。
     *
     * @param int $length 字节长度。
     * @return string
     */
    private static function get_random_bytes( $length ) {
        $length = absint( $length );
        if ( $length <= 0 ) {
            return '';
        }

        if ( function_exists( 'random_bytes' ) ) {
            try {
                return random_bytes( $length );
            } catch ( \Exception $e ) {
                // Fallback below.
            }
        }

        if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
            $strong = false;
            $bytes = openssl_random_pseudo_bytes( $length, $strong );
            if ( is_string( $bytes ) && $strong ) {
                return $bytes;
            }
        }

        return '';
    }

    /**
     * 如果当前数据库仍保存旧格式密码，则静默升级。
     *
     * @param string $current_value 当前读取到的值。
     * @param string $upgraded_value 升级后的值。
     * @return void
     */
    private static function maybe_update_stored_password( $current_value, $upgraded_value ) {
        if ( $current_value === $upgraded_value || ! function_exists( 'get_option' ) || ! function_exists( 'update_option' ) ) {
            return;
        }

        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) || ! isset( $options['smtp_password'] ) || (string) $options['smtp_password'] !== (string) $current_value ) {
            return;
        }

        $options['smtp_password'] = $upgraded_value;
        update_option( 'developer_starter_options', $options );

        if ( function_exists( 'developer_starter_refresh_options_cache' ) ) {
            developer_starter_refresh_options_cache();
        }
    }

    public function configure_smtp( $phpmailer ) {
        $smtp_host = developer_starter_get_option( 'smtp_host', '' );
        
        if ( empty( $smtp_host ) ) {
            return;
        }
        
        $smtp_port = developer_starter_get_option( 'smtp_port', '465' );
        $smtp_secure = developer_starter_get_option( 'smtp_secure', 'ssl' );
        $smtp_username = developer_starter_get_option( 'smtp_username', '' );
        $smtp_password_encrypted = developer_starter_get_option( 'smtp_password', '' );

        $smtp_password_upgraded = self::maybe_upgrade_password( $smtp_password_encrypted );
        self::maybe_update_stored_password( $smtp_password_encrypted, $smtp_password_upgraded );
        $smtp_password_encrypted = $smtp_password_upgraded;
        
        // 解密密码
        $smtp_password = self::decrypt_password( $smtp_password_encrypted );
        
        $phpmailer->isSMTP();
        $phpmailer->Host = $smtp_host;
        $phpmailer->Port = intval( $smtp_port );
        $phpmailer->SMTPSecure = $smtp_secure;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $smtp_username;
        $phpmailer->Password = $smtp_password;
        $phpmailer->CharSet = 'UTF-8';
    }

    public function set_from_email( $email ) {
        $smtp_username = developer_starter_get_option( 'smtp_username', '' );
        return ! empty( $smtp_username ) ? $smtp_username : $email;
    }

    public function set_from_name( $name ) {
        $sender_name = developer_starter_get_option( 'smtp_sender_name', '' );
        return ! empty( $sender_name ) ? $sender_name : $name;
    }
}
