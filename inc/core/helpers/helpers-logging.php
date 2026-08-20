<?php
/**
 * Central runtime logging helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_is_theme_debug_enabled' ) ) {
    /**
     * 判断主题调试开关是否启用。
     *
     * @return bool
     */
    function developer_starter_is_theme_debug_enabled() {
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return '1' === (string) developer_starter_get_option( 'debug_mode', '' );
        }

        if ( ! function_exists( 'get_option' ) ) {
            return false;
        }

        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            return false;
        }

        return '1' === (string) ( isset( $options['debug_mode'] ) ? $options['debug_mode'] : '' );
    }
}

if ( ! function_exists( 'developer_starter_is_wp_debug_log_enabled' ) ) {
    /**
     * 判断 WordPress debug.log 是否启用。
     *
     * @return bool
     */
    function developer_starter_is_wp_debug_log_enabled() {
        if ( ! defined( 'WP_DEBUG_LOG' ) ) {
            return false;
        }

        if ( true === WP_DEBUG_LOG ) {
            return true;
        }

        if ( ! is_string( WP_DEBUG_LOG ) ) {
            return false;
        }

        $value = strtolower( trim( WP_DEBUG_LOG ) );
        return '' !== $value && ! in_array( $value, array( '0', 'false', 'off', 'no' ), true );
    }
}

if ( ! function_exists( 'developer_starter_log_enabled' ) ) {
    /**
     * 判断指定日志通道是否允许写入。
     *
     * @param string $channel 日志通道。
     * @return bool
     */
    function developer_starter_log_enabled( $channel = 'general' ) {
        $channel = sanitize_key( (string) $channel );
        $enabled = developer_starter_is_wp_debug_log_enabled() || developer_starter_is_theme_debug_enabled();

        /**
         * 是否允许写入主题日志。
         *
         * 过滤器只允许在全局条件已满足时进一步关闭日志，不能在生产环境绕过全局开关强制开启。
         *
         * @param bool   $allowed 是否允许。
         * @param string $channel 日志通道。
         */
        return $enabled && (bool) apply_filters( 'developer_starter_log_allowed', true, $channel );
    }
}

if ( ! function_exists( 'developer_starter_is_sensitive_log_key' ) ) {
    /**
     * 判断上下文字段名是否敏感。
     *
     * @param string $key 字段名。
     * @return bool
     */
    function developer_starter_is_sensitive_log_key( $key ) {
        $key = (string) $key;
        if ( '' === $key ) {
            return false;
        }

        return (bool) preg_match(
            '/(?:pass(?:word)?|pwd|secret|token|nonce|authorization|cookie|api[_-]?key|apikey|access[_-]?key|accesskey|app[_-]?code|credential|private[_-]?key|phone|mobile|email|captcha|verification[_-]?code|verify[_-]?code|sms[_-]?code)/i',
            $key
        );
    }
}

if ( ! function_exists( 'developer_starter_mask_email_for_log' ) ) {
    /**
     * 脱敏邮箱。
     *
     * @param string $email 邮箱。
     * @return string
     */
    function developer_starter_mask_email_for_log( $email ) {
        $email = sanitize_email( (string) $email );
        if ( '' === $email || false === strpos( $email, '@' ) ) {
            return '[redacted-email]';
        }

        list( $local, $domain ) = explode( '@', $email, 2 );
        $prefix = substr( $local, 0, 1 );

        return $prefix . '***@' . $domain;
    }
}

if ( ! function_exists( 'developer_starter_mask_phone_for_log' ) ) {
    /**
     * 脱敏手机号/电话号码。
     *
     * @param string $phone 电话。
     * @return string
     */
    function developer_starter_mask_phone_for_log( $phone ) {
        $phone  = preg_replace( '/\D+/', '', (string) $phone );
        $length = strlen( (string) $phone );

        if ( $length >= 7 ) {
            return substr( (string) $phone, 0, 3 ) . '****' . substr( (string) $phone, -4 );
        }

        return '[redacted-phone]';
    }
}

if ( ! function_exists( 'developer_starter_sanitize_log_text' ) ) {
    /**
     * 清洗日志文本并移除常见敏感片段。
     *
     * @param mixed $value 原始值。
     * @param int   $limit 最大长度。
     * @return string
     */
    function developer_starter_sanitize_log_text( $value, $limit = 1200 ) {
        if ( is_bool( $value ) ) {
            return $value ? 'true' : 'false';
        }

        if ( is_scalar( $value ) || null === $value ) {
            $text = (string) $value;
        } else {
            $encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            $text    = is_string( $encoded ) ? $encoded : '';
        }

        $text = wp_strip_all_tags( $text );
        $text = preg_replace( '/[\r\n\t]+/', ' ', $text );
        $text = preg_replace( '/\s{2,}/', ' ', (string) $text );
        $text = trim( (string) $text );

        $text = preg_replace( '/\b(Bearer|Basic)\s+[A-Za-z0-9._~+\/=-]+/i', '$1 [redacted]', $text );
        $text = preg_replace( '/([?&](?:password|pass|pwd|secret|token|nonce|api[_-]?key|access[_-]?key|app[_-]?code|code)=)[^&\s]+/i', '$1[redacted]', (string) $text );
        $text = preg_replace( '/([A-Za-z0-9._%+\-])[A-Za-z0-9._%+\-]*@([A-Za-z0-9.\-]+\.[A-Za-z]{2,})/', '$1***@$2', (string) $text );
        $text = preg_replace( '/(?<!\d)(1[3-9]\d)\d{4}(\d{4})(?!\d)/', '$1****$2', (string) $text );

        $limit = absint( $limit );
        if ( $limit > 0 && strlen( (string) $text ) > $limit ) {
            $text = substr( (string) $text, 0, $limit ) . '...';
        }

        return sanitize_text_field( (string) $text );
    }
}

if ( ! function_exists( 'developer_starter_redact_log_value' ) ) {
    /**
     * 递归清洗日志上下文。
     *
     * @param mixed  $value 原始值。
     * @param string $key   字段名。
     * @param int    $depth 递归深度。
     * @return mixed
     */
    function developer_starter_redact_log_value( $value, $key = '', $depth = 0 ) {
        $key = (string) $key;

        if ( developer_starter_is_sensitive_log_key( $key ) ) {
            if ( preg_match( '/email/i', $key ) ) {
                return developer_starter_mask_email_for_log( (string) $value );
            }
            if ( preg_match( '/(?:phone|mobile)/i', $key ) ) {
                return developer_starter_mask_phone_for_log( (string) $value );
            }
            return '[redacted]';
        }

        if ( $value instanceof \Throwable ) {
            return array(
                'type'    => get_class( $value ),
                'message' => developer_starter_sanitize_log_text( $value->getMessage() ),
            );
        }

        if ( function_exists( 'is_wp_error' ) && is_wp_error( $value ) ) {
            return array(
                'code'    => developer_starter_sanitize_log_text( $value->get_error_code(), 160 ),
                'message' => developer_starter_sanitize_log_text( $value->get_error_message() ),
            );
        }

        if ( is_array( $value ) ) {
            if ( $depth >= 4 ) {
                return '[truncated]';
            }

            $clean = array();
            $count = 0;
            foreach ( $value as $child_key => $child_value ) {
                $count++;
                if ( $count > 50 ) {
                    $clean['__truncated__'] = true;
                    break;
                }

                $clean_key           = is_scalar( $child_key ) ? sanitize_key( (string) $child_key ) : 'item';
                $clean[ $clean_key ] = developer_starter_redact_log_value( $child_value, (string) $child_key, $depth + 1 );
            }

            return $clean;
        }

        if ( is_object( $value ) ) {
            return array( 'class' => get_class( $value ) );
        }

        return developer_starter_sanitize_log_text( $value );
    }
}

if ( ! function_exists( 'developer_starter_log' ) ) {
    /**
     * 写入主题日志。
     *
     * @param string              $channel 日志通道。
     * @param string              $message 消息。
     * @param array<string,mixed> $context 上下文。
     * @param string              $level   级别。
     * @return void
     */
    function developer_starter_log( $channel, $message, $context = array(), $level = 'error' ) {
        $channel = sanitize_key( (string) $channel );
        $channel = '' !== $channel ? $channel : 'general';

        if ( ! developer_starter_log_enabled( $channel ) ) {
            return;
        }

        $level = strtolower( sanitize_key( (string) $level ) );
        if ( ! in_array( $level, array( 'debug', 'info', 'warning', 'error', 'critical' ), true ) ) {
            $level = 'error';
        }

        $message = developer_starter_sanitize_log_text( $message );
        $context = is_array( $context ) ? developer_starter_redact_log_value( $context ) : array();

        $suffix = '';
        if ( ! empty( $context ) ) {
            $encoded = wp_json_encode( $context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            if ( is_string( $encoded ) && '' !== $encoded ) {
                $suffix = ' ' . $encoded;
            }
        }

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Central gated logger, enabled only for debug contexts.
        error_log( '[developer_starter ' . $channel . '] ' . strtoupper( $level ) . ': ' . $message . $suffix );
    }
}
