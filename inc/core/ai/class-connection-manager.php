<?php
/**
 * Generation connection manager.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\AI;

use Developer_Starter\Core\AI_Decorator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Connection_Manager {

    /**
     * @var object
     */
    private $decorator;

    /**
     * @var Response_Parser
     */
    private $response_parser;

    /**
     * @param object         $decorator       装修服务门面。
     * @param Response_Parser $response_parser 响应解析器。
     */
    public function __construct( AI_Decorator $decorator, Response_Parser $response_parser ) {
        $this->decorator = $decorator;
        $this->response_parser = $response_parser;
    }

    /**
     * 规范化 AI endpoint allowlist。
     *
     * @param string|array $raw 原始 allowlist。
     * @return array<int,string>
     */
    public static function normalize_allowed_endpoint_hosts( $raw ) {
        if ( is_array( $raw ) ) {
            $raw = implode( "\n", $raw );
        }

        $entries = preg_split( '/[\s,;]+/', (string) $raw );
        if ( ! is_array( $entries ) ) {
            return array();
        }

        $hosts = array();
        foreach ( $entries as $entry ) {
            $host = self::normalize_allowed_endpoint_host( $entry );
            if ( '' !== $host ) {
                $hosts[] = $host;
            }
        }

        return array_values( array_unique( $hosts ) );
    }

    /**
     * 清理 endpoint 地址，保存配置时使用。
     *
     * @param string            $endpoint      Endpoint 地址。
     * @param string|array|null $allowed_hosts Allowlist 覆盖值。
     * @return string
     */
    public static function sanitize_endpoint_url( $endpoint, $allowed_hosts = null ) {
        $endpoint = esc_url_raw( trim( (string) $endpoint ), array( 'https' ) );
        if ( '' === $endpoint ) {
            return '';
        }

        $validation = self::validate_endpoint_url( $endpoint, $allowed_hosts, false );
        return is_wp_error( $validation ) ? '' : $endpoint;
    }

    /**
     * 校验 endpoint 是否可被 AI 连接使用。
     *
     * @param string            $endpoint      Endpoint 地址。
     * @param string|array|null $allowed_hosts Allowlist 覆盖值。
     * @param bool              $resolve_dns   是否解析 DNS 并拦截解析到内网的主机。
     * @return true|\WP_Error
     */
    public static function validate_endpoint_url( $endpoint, $allowed_hosts = null, $resolve_dns = true ) {
        $endpoint = trim( (string) $endpoint );
        if ( '' === $endpoint ) {
            return new \WP_Error( 'ai_endpoint_empty', __( '请先填写 AI 接口地址。', 'developer-starter' ) );
        }

        $parts = wp_parse_url( $endpoint );
        if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
            return new \WP_Error( 'ai_endpoint_invalid', __( 'AI 接口地址无效。', 'developer-starter' ) );
        }

        if ( 'https' !== strtolower( (string) $parts['scheme'] ) ) {
            return new \WP_Error( 'ai_endpoint_https_required', __( 'AI 接口地址必须使用 HTTPS。', 'developer-starter' ) );
        }

        $host = self::normalize_endpoint_host( (string) $parts['host'] );
        if ( '' === $host ) {
            return new \WP_Error( 'ai_endpoint_invalid_host', __( 'AI 接口域名无效。', 'developer-starter' ) );
        }

        if ( ! self::is_valid_endpoint_host( $host ) ) {
            return new \WP_Error( 'ai_endpoint_invalid_host', __( 'AI 接口域名无效。', 'developer-starter' ) );
        }

        $configured_allowed_hosts = self::get_allowed_endpoint_hosts( $allowed_hosts );
        if ( ! self::endpoint_host_matches_allowed_hosts( $host, $configured_allowed_hosts ) ) {
            return new \WP_Error( 'ai_endpoint_not_allowed', __( 'AI 接口域名不在 allowlist 中。', 'developer-starter' ) );
        }

        if ( ! self::endpoint_host_is_public( $host, $resolve_dns ) ) {
            return new \WP_Error( 'ai_endpoint_private_host', __( 'AI 接口地址不能指向 localhost、内网、保留地址或链路本地地址。', 'developer-starter' ) );
        }

        return true;
    }

    /**
     * 规范化单个 allowlist 主机。
     *
     * @param string $entry 原始条目。
     * @return string
     */
    private static function normalize_allowed_endpoint_host( $entry ) {
        $entry = trim( wp_strip_all_tags( (string) $entry ) );
        if ( '' === $entry ) {
            return '';
        }

        $wildcard = false;
        if ( 0 === strpos( $entry, '*.' ) ) {
            $wildcard = true;
            $entry = substr( $entry, 2 );
        }

        $parts = wp_parse_url( $entry );
        if ( is_array( $parts ) && ! empty( $parts['host'] ) ) {
            $entry = (string) $parts['host'];
        }

        $host = self::normalize_endpoint_host( $entry );
        if ( '' === $host ) {
            return '';
        }

        if ( $wildcard && filter_var( $host, FILTER_VALIDATE_IP ) ) {
            return '';
        }

        if ( ! self::is_valid_endpoint_host( $host ) || ! self::endpoint_host_is_public( $host, false ) ) {
            return '';
        }

        return $wildcard ? '*.' . $host : $host;
    }

    /**
     * 获取已配置的 endpoint allowlist。
     *
     * @param string|array|null $allowed_hosts Allowlist 覆盖值。
     * @return array<int,string>
     */
    private static function get_allowed_endpoint_hosts( $allowed_hosts = null ) {
        if ( null === $allowed_hosts ) {
            $options = get_option( 'developer_starter_options', array() );
            $allowed_hosts = is_array( $options ) && isset( $options['ai_endpoint_allowlist'] )
                ? $options['ai_endpoint_allowlist']
                : '';
        }

        $hosts = self::normalize_allowed_endpoint_hosts( $allowed_hosts );
        $filtered_hosts = apply_filters( 'developer_starter_ai_endpoint_allowed_hosts', $hosts );
        if ( $filtered_hosts !== $hosts ) {
            $hosts = self::normalize_allowed_endpoint_hosts( $filtered_hosts );
        }

        return array_values( array_unique( $hosts ) );
    }

    /**
     * 规范化主机名。
     *
     * @param string $host 主机名。
     * @return string
     */
    private static function normalize_endpoint_host( $host ) {
        $host = strtolower( trim( (string) $host ) );
        $host = trim( $host, " \t\n\r\0\x0B.[]" );
        if ( '' === $host ) {
            return '';
        }

        if ( function_exists( 'idn_to_ascii' ) && ! filter_var( $host, FILTER_VALIDATE_IP ) ) {
            $ascii = idn_to_ascii( $host, 0, defined( 'INTL_IDNA_VARIANT_UTS46' ) ? INTL_IDNA_VARIANT_UTS46 : 1 );
            if ( is_string( $ascii ) && '' !== $ascii ) {
                $host = strtolower( $ascii );
            }
        }

        return $host;
    }

    /**
     * 判断主机名是否是可接受的 IP 或 DNS 主机名。
     *
     * @param string $host 主机名。
     * @return bool
     */
    private static function is_valid_endpoint_host( $host ) {
        if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
            return true;
        }

        if ( strlen( $host ) > 253 || ! preg_match( '/^[a-z0-9.-]+$/', $host ) ) {
            return false;
        }

        $labels = explode( '.', $host );
        foreach ( $labels as $label ) {
            if ( '' === $label || strlen( $label ) > 63 ) {
                return false;
            }
            if ( '-' === $label[0] || '-' === substr( $label, -1 ) ) {
                return false;
            }
        }

        $last_label = end( $labels );
        if ( is_string( $last_label ) && preg_match( '/^\d+$/', $last_label ) ) {
            return false;
        }

        return true;
    }

    /**
     * 判断主机是否命中 allowlist。
     *
     * @param string            $host 主机名。
     * @param array<int,string> $allowed_hosts Allowlist。
     * @return bool
     */
    private static function endpoint_host_matches_allowed_hosts( $host, $allowed_hosts ) {
        if ( empty( $allowed_hosts ) ) {
            return true;
        }

        foreach ( $allowed_hosts as $allowed_host ) {
            if ( $allowed_host === $host ) {
                return true;
            }

            if ( 0 === strpos( $allowed_host, '*.' ) ) {
                $suffix = substr( $allowed_host, 1 );
                if ( strlen( $host ) > strlen( $suffix ) && substr( $host, -strlen( $suffix ) ) === $suffix ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 判断主机是否为公网目标。
     *
     * @param string $host 主机名。
     * @param bool   $resolve_dns 是否解析 DNS。
     * @return bool
     */
    private static function endpoint_host_is_public( $host, $resolve_dns ) {
        if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
            return self::is_public_ip_address( $host );
        }

        if ( self::is_disallowed_local_hostname( $host ) ) {
            return false;
        }

        if ( ! $resolve_dns ) {
            return true;
        }

        $ips = self::resolve_endpoint_host_ips( $host );
        if ( empty( $ips ) ) {
            return false;
        }

        foreach ( $ips as $ip ) {
            if ( ! self::is_public_ip_address( $ip ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * 判断 IP 是否为公网地址。
     *
     * @param string $ip IP 地址。
     * @return bool
     */
    private static function is_public_ip_address( $ip ) {
        return (bool) filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    }

    /**
     * 判断主机名是否明显为本地或内网命名。
     *
     * @param string $host 主机名。
     * @return bool
     */
    private static function is_disallowed_local_hostname( $host ) {
        if ( 'localhost' === $host || 'localhost.localdomain' === $host ) {
            return true;
        }

        foreach ( array( '.localhost', '.local', '.home.arpa' ) as $suffix ) {
            if ( strlen( $host ) > strlen( $suffix ) && substr( $host, -strlen( $suffix ) ) === $suffix ) {
                return true;
            }
        }

        return false === strpos( $host, '.' );
    }

    /**
     * 解析主机对应 IP。
     *
     * @param string $host 主机名。
     * @return array<int,string>
     */
    private static function resolve_endpoint_host_ips( $host ) {
        $ips = array();

        if ( function_exists( 'gethostbynamel' ) ) {
            $records = gethostbynamel( $host );
            if ( is_array( $records ) ) {
                $ips = array_merge( $ips, $records );
            }
        }

        if ( function_exists( 'dns_get_record' ) && defined( 'DNS_AAAA' ) ) {
            $aaaa_records = @dns_get_record( $host, DNS_AAAA );
            if ( is_array( $aaaa_records ) ) {
                foreach ( $aaaa_records as $record ) {
                    if ( isset( $record['ipv6'] ) ) {
                        $ips[] = (string) $record['ipv6'];
                    }
                }
            }
        }

        return array_values(
            array_unique(
                array_filter(
                    $ips,
                    static function( $ip ) {
                        return (bool) filter_var( $ip, FILTER_VALIDATE_IP );
                    }
                )
            )
        );
    }

    /**
     * 获取连接配置。
     *
     * @param string $connection_id 连接 ID。
     * @return array<string,mixed>|null
     */
    public function get_connection( $connection_id ) {
        $connections = $this->get_connections();
        if ( '' === $connection_id ) {
            $connection_id = $this->get_default_connection_id();
        }

        if ( '' === $connection_id || ! isset( $connections[ $connection_id ] ) ) {
            return null;
        }

        return $connections[ $connection_id ];
    }

    /**
     * 获取启用的连接列表。
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_connections() {
        $options = $this->decorator->get_theme_options();
        $raw_connections = isset( $options['ai_connections'] ) && is_array( $options['ai_connections'] )
            ? $options['ai_connections']
            : array();

        $connections = array();
        foreach ( $raw_connections as $connection ) {
            if ( ! is_array( $connection ) ) {
                continue;
            }

            $id = isset( $connection['id'] ) ? sanitize_key( (string) $connection['id'] ) : '';
            if ( '' === $id ) {
                continue;
            }

            $enabled = ! empty( $connection['enabled'] ) && (string) $connection['enabled'] === '1';
            $endpoint = isset( $connection['endpoint'] ) ? self::sanitize_endpoint_url( (string) $connection['endpoint'] ) : '';
            $api_key = isset( $connection['api_key'] ) ? sanitize_text_field( (string) $connection['api_key'] ) : '';
            $default_model = isset( $connection['default_model'] ) ? sanitize_text_field( (string) $connection['default_model'] ) : '';
            $name = isset( $connection['name'] ) ? sanitize_text_field( (string) $connection['name'] ) : $id;

            if ( ! $enabled || '' === $endpoint || '' === $api_key ) {
                continue;
            }

            $connections[ $id ] = array(
                'id'            => $id,
                'name'          => $name,
                'endpoint'      => $endpoint,
                'default_model' => $default_model,
                'models'        => isset( $connection['models'] ) && is_array( $connection['models'] )
                    ? array_values( array_filter( array_map( 'sanitize_text_field', $connection['models'] ) ) )
                    : array(),
                'json_mode'     => ! empty( $connection['json_mode'] ) && (string) $connection['json_mode'] === '1',
                'api_key'       => $api_key,
            );
        }

        return $connections;
    }

    /**
     * 获取默认连接 ID。
     *
     * @return string
     */
    public function get_default_connection_id() {
        $connections = $this->get_connections();
        if ( empty( $connections ) ) {
            return '';
        }

        $configured = sanitize_key( (string) $this->decorator->get_option_value( 'ai_default_connection', '' ) );
        if ( '' !== $configured && isset( $connections[ $configured ] ) ) {
            return $configured;
        }

        $keys = array_keys( $connections );
        return isset( $keys[0] ) ? (string) $keys[0] : '';
    }

    /**
     * 测试生成连接是否可用。
     *
     * @param array<string,mixed> $args 测试参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function test_connection( $args ) {
        $connection_name = isset( $args['name'] ) ? sanitize_text_field( (string) $args['name'] ) : '';
        $raw_endpoint = isset( $args['endpoint'] ) ? trim( (string) $args['endpoint'] ) : '';
        $endpoint = self::sanitize_endpoint_url( $raw_endpoint );
        $connection = array(
            'id'            => isset( $args['id'] ) ? sanitize_key( (string) $args['id'] ) : '',
            'name'          => '' !== $connection_name ? $connection_name : __( '未命名连接', 'developer-starter' ),
            'endpoint'      => $endpoint,
            'default_model' => '',
            'models'        => array(),
            'json_mode'     => $this->decorator->normalize_bool( isset( $args['json_mode'] ) ? $args['json_mode'] : false, false ),
            'api_key'       => isset( $args['api_key'] ) ? sanitize_text_field( (string) $args['api_key'] ) : '',
        );
        $model = isset( $args['model'] ) ? sanitize_text_field( (string) $args['model'] ) : '';

        if ( '' === $raw_endpoint ) {
            return new \WP_Error( 'empty_endpoint', __( '请先填写接口地址。', 'developer-starter' ) );
        }

        if ( '' === $connection['endpoint'] ) {
            $validation = self::validate_endpoint_url( $raw_endpoint, null, false );
            return is_wp_error( $validation )
                ? $validation
                : new \WP_Error( 'invalid_endpoint', __( 'AI 连接接口地址无效。', 'developer-starter' ) );
        }

        if ( '' === $connection['api_key'] ) {
            return new \WP_Error( 'empty_api_key', __( '请先填写 API Key。', 'developer-starter' ) );
        }

        if ( '' === $model ) {
            return new \WP_Error( 'empty_model', __( '请先填写默认模型后再测试。', 'developer-starter' ) );
        }

        $messages = array(
            array(
                'role'    => 'system',
                'content' => '你是接口连通性测试助手。',
            ),
        );

        if ( ! empty( $connection['json_mode'] ) ) {
            $messages[] = array(
                'role'    => 'user',
                'content' => '这是一次 json 模式连通性测试。请只返回一个 json 对象，例如 {"message":"连接正常"}，不要输出其他内容。',
            );
        } else {
            $messages[] = array(
                'role'    => 'user',
                'content' => '你好，请只回复：连接正常',
            );
        }

        $response = $this->request_chat_completion(
            $connection,
            $model,
            $messages,
            array(
                'request_type' => 'connection_test',
                'timeout'      => $this->decorator->get_default_request_timeout(),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $reply = trim( (string) $response['content'] );
        if ( '' === $reply ) {
            return new \WP_Error( 'empty_test_reply', __( '接口已响应，但没有返回可读文本。', 'developer-starter' ) );
        }

        if ( ! empty( $connection['json_mode'] ) ) {
            $json_reply = $this->response_parser->extract_json_string( $reply );
            $decoded_reply = json_decode( $json_reply, true );
            if ( is_array( $decoded_reply ) && isset( $decoded_reply['message'] ) && is_scalar( $decoded_reply['message'] ) ) {
                $reply = (string) $decoded_reply['message'];
            }
        }

        return array(
            'message'  => __( '连接测试成功，接口已正常返回内容。', 'developer-starter' ),
            'reply'    => $this->decorator->truncate_text_for_log( $reply, 220 ),
            'model'    => $model,
            'endpoint' => $this->normalize_chat_endpoint( $connection['endpoint'] ),
        );
    }

    /**
     * 发起生成请求。
     *
     * @param array<string,mixed>             $connection 连接配置。
     * @param string                          $model 连接目标名称。
     * @param array<int,array<string,string>> $messages 消息体。
     * @param array<string,mixed>             $args 附加参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function request_chat_completion( $connection, $model, $messages, $args = array() ) {
        $endpoint = $this->prepare_chat_endpoint( $connection['endpoint'] );
        if ( is_wp_error( $endpoint ) ) {
            return $endpoint;
        }

        $timeout = isset( $args['timeout'] ) ? absint( $args['timeout'] ) : $this->decorator->get_default_request_timeout();
        if ( $timeout < 10 ) {
            $timeout = $this->decorator->get_default_request_timeout();
        }
        $request_type = isset( $args['request_type'] ) ? sanitize_key( (string) $args['request_type'] ) : 'chat_completion';
        $module_count = isset( $args['module_count'] ) ? absint( $args['module_count'] ) : 0;

        $max_output_tokens = isset( $args['max_output_tokens'] )
            ? min( $this->decorator->get_default_max_output_tokens(), max( 256, absint( $args['max_output_tokens'] ) ) )
            : $this->decorator->get_request_max_output_tokens( $request_type, $module_count );

        $request_context = array(
            'connection_id'     => isset( $connection['id'] ) ? sanitize_key( (string) $connection['id'] ) : '',
            'connection_name'   => isset( $connection['name'] ) ? sanitize_text_field( (string) $connection['name'] ) : '',
            'endpoint'          => $endpoint,
            'model'             => sanitize_text_field( (string) $model ),
            'json_mode'         => ! empty( $connection['json_mode'] ),
            'request_type'      => $request_type,
            'timeout'           => $timeout,
            'module_count'      => $module_count,
            'max_output_tokens' => $max_output_tokens,
        );

        $payload = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $this->decorator->get_default_temperature(),
        );

        if ( $max_output_tokens > 0 ) {
            $payload['max_tokens'] = $max_output_tokens;
        }

        if ( ! empty( $connection['json_mode'] ) ) {
            $payload['response_format'] = array(
                'type' => 'json_object',
            );
        }

        $response = wp_remote_post(
            $endpoint,
            array(
                'timeout' => $timeout,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $connection['api_key'],
                    'Content-Type'  => 'application/json',
                ),
                'body'    => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
                'redirection'         => 0,
                'reject_unsafe_urls'  => true,
                'sslverify'           => true,
            )
        );

        if ( is_wp_error( $response ) ) {
            $this->decorator->log_debug_message(
                'AI request transport error',
                array_merge(
                    $request_context,
                    array(
                        'error' => $response->get_error_message(),
                    )
                )
            );

            if ( $this->decorator->is_timeout_error( $response ) ) {
                return new \WP_Error(
                    'request_timeout',
                    sprintf(
                        /* translators: %d: timeout seconds */
                        __( 'AI 请求超时（%d 秒）。当前模型返回较慢，建议减少候选模块数量、降低输出长度，或在主题设置里把请求超时调大后再试。', 'developer-starter' ),
                        $timeout
                    )
                );
            }

            return new \WP_Error(
                'request_failed',
                sprintf(
                    /* translators: %s: error message */
                    __( 'AI 请求失败：%s', 'developer-starter' ),
                    $response->get_error_message()
                )
            );
        }

        $status_code = (int) wp_remote_retrieve_response_code( $response );
        $body = (string) wp_remote_retrieve_body( $response );
        $decoded = json_decode( $body, true );

        if ( $status_code < 200 || $status_code >= 300 ) {
            $message = $this->extract_error_message_from_response( $decoded, $body );
            $this->decorator->log_debug_message(
                'AI request http error',
                array_merge(
                    $request_context,
                    array(
                        'status_code'      => $status_code,
                        'error_message'    => $message,
                        'response_excerpt' => $this->decorator->truncate_text_for_log( $body, 400 ),
                    )
                )
            );
            return new \WP_Error(
                'http_error',
                sprintf(
                    /* translators: 1: status code 2: message */
                    __( 'AI 接口返回异常（HTTP %1$d）：%2$s', 'developer-starter' ),
                    $status_code,
                    $message
                )
            );
        }

        if ( ! is_array( $decoded ) ) {
            $this->decorator->log_debug_message(
                'AI request invalid json response',
                array_merge(
                    $request_context,
                    array(
                        'status_code'      => $status_code,
                        'response_excerpt' => $this->decorator->truncate_text_for_log( $body, 400 ),
                    )
                )
            );
            return new \WP_Error( 'invalid_response', __( 'AI 接口返回了无法解析的响应。', 'developer-starter' ) );
        }

        $content = $this->extract_assistant_content( $decoded );
        if ( '' === $content ) {
            $this->decorator->log_debug_message(
                'AI request empty content response',
                array_merge(
                    $request_context,
                    array(
                        'status_code'      => $status_code,
                        'response_excerpt' => $this->decorator->truncate_text_for_log( $body, 400 ),
                    )
                )
            );
            return new \WP_Error( 'empty_response', __( '接口没有返回可解析的文本内容。', 'developer-starter' ) );
        }

        return array(
            'content' => $content,
            'raw'     => $decoded,
        );
    }

    /**
     * 规范化接口地址。
     *
     * @param string $endpoint 接口地址。
     * @return string
     */
    private function normalize_chat_endpoint( $endpoint ) {
        $endpoint = $this->prepare_chat_endpoint( $endpoint );
        return is_wp_error( $endpoint ) ? '' : $endpoint;
    }

    /**
     * 生成最终 Chat Completions endpoint。
     *
     * @param string $endpoint 接口地址。
     * @return string|\WP_Error
     */
    private function prepare_chat_endpoint( $endpoint ) {
        $endpoint = trim( (string) $endpoint );
        if ( '' === $endpoint ) {
            return new \WP_Error( 'invalid_endpoint', __( 'AI 连接接口地址无效。', 'developer-starter' ) );
        }

        $endpoint = rtrim( $endpoint, '/' );
        if ( preg_match( '#/chat/completions$#i', $endpoint ) ) {
            $endpoint = esc_url_raw( $endpoint, array( 'https' ) );
        } else {
            $endpoint = esc_url_raw( $endpoint . '/chat/completions', array( 'https' ) );
        }

        if ( '' === $endpoint ) {
            return new \WP_Error( 'invalid_endpoint', __( 'AI 连接接口地址无效。', 'developer-starter' ) );
        }

        $validation = self::validate_endpoint_url( $endpoint, null, true );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        return $endpoint;
    }

    /**
     * 提取响应错误信息。
     *
     * @param mixed  $decoded 解析后的响应。
     * @param string $fallback 原始响应。
     * @return string
     */
    private function extract_error_message_from_response( $decoded, $fallback ) {
        if ( is_array( $decoded ) ) {
            if ( isset( $decoded['error']['message'] ) && is_scalar( $decoded['error']['message'] ) ) {
                return sanitize_text_field( (string) $decoded['error']['message'] );
            }
            if ( isset( $decoded['message'] ) && is_scalar( $decoded['message'] ) ) {
                return sanitize_text_field( (string) $decoded['message'] );
            }
        }

        $fallback = trim( wp_strip_all_tags( (string) $fallback ) );
        if ( '' === $fallback ) {
            return __( '未知错误', 'developer-starter' );
        }

        if ( function_exists( 'mb_strlen' ) && mb_strlen( $fallback, 'UTF-8' ) > 180 ) {
            return mb_substr( $fallback, 0, 180, 'UTF-8' ) . '...';
        }

        if ( strlen( $fallback ) > 180 ) {
            return substr( $fallback, 0, 180 ) . '...';
        }

        return $fallback;
    }

    /**
     * 提取消息正文。
     *
     * @param array<string,mixed> $decoded 响应。
     * @return string
     */
    private function extract_assistant_content( $decoded ) {
        if ( empty( $decoded['choices'][0]['message']['content'] ) ) {
            return '';
        }

        $content = $decoded['choices'][0]['message']['content'];
        if ( is_string( $content ) ) {
            return trim( $content );
        }

        if ( is_array( $content ) ) {
            $parts = array();
            foreach ( $content as $item ) {
                if ( is_array( $item ) && isset( $item['text'] ) && is_scalar( $item['text'] ) ) {
                    $parts[] = (string) $item['text'];
                }
            }
            return trim( implode( "\n", $parts ) );
        }

        return '';
    }
}
