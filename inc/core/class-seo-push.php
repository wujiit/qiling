<?php
/**
 * SEO Push - Baidu, IndexNow/Bing and optional Google Indexing API.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SEO_Push_Baidu {

    private static $instance = null;
    private $option_name = 'developer_starter_options';
    private const INDEXNOW_ENDPOINT = 'https://api.indexnow.org/indexnow';
    private const GOOGLE_INDEXING_ENDPOINT = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    private const GOOGLE_TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_INDEXING_SCOPE = 'https://www.googleapis.com/auth/indexing';

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'transition_post_status', array( $this, 'handle_transition' ), 10, 3 );
        add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
        add_action( 'wp_ajax_ds_baidu_push_single', array( $this, 'ajax_push_single' ) );
        add_action( 'wp_ajax_ds_baidu_push_custom', array( $this, 'ajax_push_custom' ) );
        add_action( 'wp_ajax_ds_indexnow_push_single', array( $this, 'ajax_indexnow_push_single' ) );
        add_action( 'wp_ajax_ds_indexnow_push_custom', array( $this, 'ajax_indexnow_push_custom' ) );
        add_action( 'wp_ajax_ds_google_indexing_push_single', array( $this, 'ajax_google_push_single' ) );
        add_action( 'wp_ajax_ds_google_indexing_push_custom', array( $this, 'ajax_google_push_custom' ) );
        add_action( 'wp_ajax_ds_seo_push_history', array( $this, 'ajax_push_history' ) );
    }

    private function get_options() {
        $options = get_option( $this->option_name, array() );
        return is_array( $options ) ? $options : array();
    }

    private function is_enabled() {
        $options = $this->get_options();
        return isset( $options['seo_push_baidu_enable'] ) && $options['seo_push_baidu_enable'] === '1';
    }

    private function is_indexnow_enabled() {
        $options = $this->get_options();
        return isset( $options['seo_push_indexnow_enable'] ) && $options['seo_push_indexnow_enable'] === '1';
    }

    private function is_google_enabled() {
        $options = $this->get_options();
        return isset( $options['seo_push_google_enable'] ) && $options['seo_push_google_enable'] === '1';
    }

    private function is_google_auto_enabled() {
        $options = $this->get_options();
        return $this->is_google_enabled() && isset( $options['seo_push_google_auto_enable'] ) && $options['seo_push_google_auto_enable'] === '1';
    }

    private function normalize_domain( $raw_domain ) {
        $domain = trim( (string) $raw_domain );
        if ( $domain === '' ) {
            return '';
        }

        $domain = preg_replace( '#^https?://#i', '', $domain );
        $domain = preg_replace( '#^//#', '', $domain );
        $domain = trim( $domain, " \t\n\r\0\x0B/" );

        if ( $domain === '' ) {
            return '';
        }

        if ( strpos( $domain, '/' ) !== false ) {
            $domain = strtok( $domain, '/' );
        }
        if ( strpos( $domain, '?' ) !== false ) {
            $domain = strtok( $domain, '?' );
        }
        if ( strpos( $domain, '#' ) !== false ) {
            $domain = strtok( $domain, '#' );
        }

        $parsed_host = wp_parse_url( 'https://' . $domain, PHP_URL_HOST );
        if ( is_string( $parsed_host ) && $parsed_host !== '' ) {
            $domain = $parsed_host;
        }

        return strtolower( trim( $domain, '.' ) );
    }

    private function get_home_host() {
        $host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        return is_string( $host ) ? strtolower( trim( $host, '.' ) ) : '';
    }

    private function get_site() {
        $options = $this->get_options();
        $site = isset( $options['seo_push_baidu_site'] ) ? $this->normalize_domain( $options['seo_push_baidu_site'] ) : '';
        if ( $site !== '' ) {
            return $site;
        }
        return $this->get_home_host();
    }

    private function get_token() {
        $options = $this->get_options();
        return isset( $options['seo_push_baidu_token'] ) ? trim( (string) $options['seo_push_baidu_token'] ) : '';
    }

    private function get_indexnow_key() {
        $options = $this->get_options();
        return isset( $options['seo_push_indexnow_key'] ) ? trim( (string) $options['seo_push_indexnow_key'] ) : '';
    }

    private function get_indexnow_endpoint() {
        return self::INDEXNOW_ENDPOINT;
    }

    private function get_indexnow_key_location() {
        $key = $this->get_indexnow_key();
        if ( $key !== '' ) {
            return home_url( '/' . rawurlencode( $key ) . '.txt' );
        }
        return '';
    }

    private function get_google_service_account_json() {
        $options = $this->get_options();
        return isset( $options['seo_push_google_service_account_json'] ) ? trim( (string) $options['seo_push_google_service_account_json'] ) : '';
    }

    private function get_google_service_account() {
        $raw = $this->get_google_service_account_json();
        if ( '' === $raw ) {
            return new \WP_Error( 'missing_google_service_account', __( '请先配置 Google Service Account JSON', 'developer-starter' ) );
        }

        $data = json_decode( $raw, true );
        if ( ! is_array( $data ) || empty( $data['client_email'] ) || empty( $data['private_key'] ) ) {
            return new \WP_Error( 'invalid_google_service_account', __( 'Google Service Account JSON 格式无效', 'developer-starter' ) );
        }

        if ( empty( $data['token_uri'] ) ) {
            $data['token_uri'] = self::GOOGLE_TOKEN_ENDPOINT;
        }

        return $data;
    }

    private function can_push_post( $post ) {
        if ( ! $post || ! is_object( $post ) ) {
            return false;
        }
        if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
            return false;
        }
        return in_array( $post->post_type, array( 'post', 'page' ), true );
    }

    public function handle_transition( $new_status, $old_status, $post ) {
        if ( $new_status !== 'publish' || $old_status === 'publish' ) {
            return;
        }
        if ( ! $this->can_push_post( $post ) ) {
            return;
        }
        $url = get_permalink( $post );
        if ( ! $url ) {
            return;
        }
        if ( $this->is_enabled() ) {
            $token = $this->get_token();
            $site = $this->get_site();
            if ( $token === '' || $site === '' ) {
                $this->save_push_meta( $post->ID, 'skipped', array(
                    'message' => 'missing_token_or_site',
                ) );
            } else {
                $result = $this->push_urls( array( $url ), $site, $token, 'auto' );
                $this->save_push_meta( $post->ID, $result['status'], $result );
            }
        }
        if ( $this->is_indexnow_enabled() ) {
            $key = $this->get_indexnow_key();
            if ( $key === '' ) {
                $this->save_indexnow_meta( $post->ID, 'skipped', array(
                    'message' => 'missing_key',
                ) );
            } else {
                $result = $this->push_indexnow_urls( array( $url ), 'auto' );
                $this->save_indexnow_meta( $post->ID, $result['status'], $result );
            }
        }
        if ( $this->is_google_auto_enabled() ) {
            $service_account = $this->get_google_service_account();
            if ( is_wp_error( $service_account ) ) {
                $this->save_google_meta( $post->ID, 'skipped', array(
                    'message' => $service_account->get_error_message(),
                ) );
            } else {
                $result = $this->push_google_urls( array( $url ), 'auto' );
                $this->save_google_meta( $post->ID, $result['status'], $result );
            }
        }
    }

    private function push_urls( array $urls, $site, $token, $trigger = 'manual' ) {
        $api = 'https://data.zz.baidu.com/urls?site=' . rawurlencode( $site ) . '&token=' . rawurlencode( $token );
        $body = implode( "\n", array_filter( $urls ) );

        $response = wp_remote_post( $api, array(
            'headers' => array(
                'Content-Type' => 'text/plain',
            ),
            'body'    => $body,
            'timeout' => 10,
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'status'  => 'error',
                'message' => $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $raw_body, true );

        if ( (int) $code === 200 && is_array( $data ) ) {
            return array(
                'status'  => 'success',
                'message' => 'ok',
                'response_code' => $code,
                'trigger' => $trigger,
                'data'    => $data,
            );
        }

        $message = is_array( $data ) && isset( $data['message'] ) ? $data['message'] : ( $raw_body ?: 'unknown_error' );
        return array(
            'status'  => 'error',
            'message' => $message,
            'response_code' => $code,
            'trigger' => $trigger,
            'data'    => $data,
        );
    }

    private function save_push_meta( $post_id, $status, $result ) {
        update_post_meta( $post_id, '_ds_baidu_push_status', $status );
        update_post_meta( $post_id, '_ds_baidu_push_time', current_time( 'mysql' ) );
        update_post_meta( $post_id, '_ds_baidu_push_result', wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) );
    }

    private function save_indexnow_meta( $post_id, $status, $result ) {
        update_post_meta( $post_id, '_ds_indexnow_push_status', $status );
        update_post_meta( $post_id, '_ds_indexnow_push_time', current_time( 'mysql' ) );
        update_post_meta( $post_id, '_ds_indexnow_push_result', wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) );
    }

    private function save_google_meta( $post_id, $status, $result ) {
        update_post_meta( $post_id, '_ds_google_indexing_push_status', $status );
        update_post_meta( $post_id, '_ds_google_indexing_push_time', current_time( 'mysql' ) );
        update_post_meta( $post_id, '_ds_google_indexing_push_result', wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) );
    }

    private function get_provider_status_meta_key( $provider ) {
        $provider = sanitize_key( (string) $provider );
        if ( 'indexnow' === $provider ) {
            return '_ds_indexnow_push_status';
        }
        if ( 'google' === $provider ) {
            return '_ds_google_indexing_push_status';
        }
        return '_ds_baidu_push_status';
    }

    private function push_indexnow_urls( array $urls, $trigger = 'manual' ) {
        $key = $this->get_indexnow_key();
        $endpoint = $this->get_indexnow_endpoint();
        $host = $this->get_home_host();
        $key_location = $this->get_indexnow_key_location();

        $payload = array(
            'host' => $host,
            'key' => $key,
            'urlList' => array_values( array_filter( $urls ) ),
        );
        if ( $key_location ) {
            $payload['keyLocation'] = $key_location;
        }

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body'    => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ),
            'timeout' => 10,
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'status'  => 'error',
                'message' => $response->get_error_message(),
                'trigger' => $trigger,
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $raw_body, true );

        if ( (int) $code === 200 ) {
            return array(
                'status'  => 'success',
                'message' => 'ok',
                'response_code' => $code,
                'trigger' => $trigger,
                'data'    => $data,
            );
        }

        $message = is_array( $data ) && isset( $data['message'] ) ? $data['message'] : ( $raw_body ?: 'unknown_error' );
        return array(
            'status'  => 'error',
            'message' => $message,
            'response_code' => $code,
            'trigger' => $trigger,
            'data'    => $data,
        );
    }

    private function base64url_encode( $value ) {
        return rtrim( strtr( base64_encode( (string) $value ), '+/', '-_' ), '=' );
    }

    private function get_google_access_token() {
        $service_account = $this->get_google_service_account();
        if ( is_wp_error( $service_account ) ) {
            return $service_account;
        }

        if ( ! function_exists( 'openssl_sign' ) ) {
            return new \WP_Error( 'openssl_unavailable', __( '当前 PHP 环境缺少 openssl_sign，无法签发 Google 访问令牌', 'developer-starter' ) );
        }

        $cache_key = 'ds_google_indexing_token_' . md5( (string) $service_account['client_email'] . '|' . (string) $service_account['private_key'] );
        $cached = get_transient( $cache_key );
        if ( is_string( $cached ) && '' !== $cached ) {
            return $cached;
        }

        $now = time();
        $claims = array(
            'iss'   => (string) $service_account['client_email'],
            'scope' => self::GOOGLE_INDEXING_SCOPE,
            'aud'   => (string) $service_account['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600,
        );
        $jwt_header = $this->base64url_encode( wp_json_encode( array( 'alg' => 'RS256', 'typ' => 'JWT' ) ) );
        $jwt_claims = $this->base64url_encode( wp_json_encode( $claims ) );
        $jwt_body = $jwt_header . '.' . $jwt_claims;
        $signature = '';

        $algorithm = defined( 'OPENSSL_ALGO_SHA256' ) ? OPENSSL_ALGO_SHA256 : 'sha256WithRSAEncryption';
        $signed = openssl_sign( $jwt_body, $signature, (string) $service_account['private_key'], $algorithm );
        if ( ! $signed ) {
            return new \WP_Error( 'google_jwt_sign_failed', __( 'Google 访问令牌签名失败，请检查 private_key 是否完整', 'developer-starter' ) );
        }

        $assertion = $jwt_body . '.' . $this->base64url_encode( $signature );
        $response = wp_remote_post(
            (string) $service_account['token_uri'],
            array(
                'body'    => array(
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $assertion,
                ),
                'timeout' => 12,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $raw_body, true );

        if ( 200 !== (int) $code || ! is_array( $data ) || empty( $data['access_token'] ) ) {
            $message = is_array( $data ) && isset( $data['error_description'] ) ? $data['error_description'] : ( $raw_body ?: 'google_token_error' );
            return new \WP_Error( 'google_token_error', $message );
        }

        $ttl = isset( $data['expires_in'] ) ? max( 60, (int) $data['expires_in'] - 120 ) : 3300;
        set_transient( $cache_key, (string) $data['access_token'], $ttl );

        return (string) $data['access_token'];
    }

    private function push_google_single_url( $url, $trigger = 'manual' ) {
        $token = $this->get_google_access_token();
        if ( is_wp_error( $token ) ) {
            return array(
                'status'  => 'error',
                'message' => $token->get_error_message(),
                'trigger' => $trigger,
            );
        }

        $response = wp_remote_post(
            self::GOOGLE_INDEXING_ENDPOINT,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => wp_json_encode(
                    array(
                        'url'  => $url,
                        'type' => 'URL_UPDATED',
                    ),
                    JSON_UNESCAPED_SLASHES
                ),
                'timeout' => 12,
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'status'  => 'error',
                'message' => $response->get_error_message(),
                'trigger' => $trigger,
                'url'     => $url,
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $raw_body, true );

        if ( 200 === (int) $code ) {
            return array(
                'status'        => 'success',
                'message'       => 'ok',
                'response_code' => $code,
                'trigger'       => $trigger,
                'url'           => $url,
                'data'          => $data,
            );
        }

        $message = is_array( $data ) && isset( $data['error']['message'] ) ? $data['error']['message'] : ( $raw_body ?: 'google_indexing_error' );
        return array(
            'status'        => 'error',
            'message'       => $message,
            'response_code' => $code,
            'trigger'       => $trigger,
            'url'           => $url,
            'data'          => $data,
        );
    }

    private function push_google_urls( array $urls, $trigger = 'manual' ) {
        $urls = array_values( array_unique( array_filter( $urls ) ) );
        if ( empty( $urls ) ) {
            return array(
                'status'  => 'error',
                'message' => 'empty_urls',
                'trigger' => $trigger,
            );
        }

        $success = 0;
        $error = 0;
        $items = array();
        foreach ( $urls as $url ) {
            $result = $this->push_google_single_url( $url, $trigger );
            if ( isset( $result['status'] ) && 'success' === $result['status'] ) {
                $success++;
            } else {
                $error++;
            }
            if ( count( $items ) < 20 ) {
                $items[] = $result;
            }
        }

        $status = $success > 0 && 0 === $error ? 'success' : ( $success > 0 ? 'partial' : 'error' );
        $message = sprintf(
            /* translators: 1: success count, 2: error count */
            __( 'Google 推送完成：成功 %1$d 条，失败 %2$d 条', 'developer-starter' ),
            $success,
            $error
        );

        return array(
            'status'  => $status,
            'message' => $message,
            'trigger' => $trigger,
            'data'    => array(
                'success' => $success,
                'error'   => $error,
                'items'   => $items,
            ),
        );
    }

    public function register_meta_box() {
        add_meta_box(
            'ds_baidu_push_status',
            __( 'SEO 推送状态', 'developer-starter' ),
            array( $this, 'render_meta_box' ),
            array( 'post', 'page' ),
            'side',
            'default'
        );
    }

    public function render_meta_box( $post ) {
        $enabled = $this->is_enabled();
        $index_enabled = $this->is_indexnow_enabled();
        $status = get_post_meta( $post->ID, '_ds_baidu_push_status', true );
        $time = get_post_meta( $post->ID, '_ds_baidu_push_time', true );
        $raw = get_post_meta( $post->ID, '_ds_baidu_push_result', true );
        $data = $raw ? json_decode( $raw, true ) : null;
        $message = is_array( $data ) && isset( $data['message'] ) ? $data['message'] : '';
        $success = is_array( $data ) && isset( $data['data']['success'] ) ? (int) $data['data']['success'] : null;
        $remain = is_array( $data ) && isset( $data['data']['remain'] ) ? (int) $data['data']['remain'] : null;
        $trigger = is_array( $data ) && isset( $data['trigger'] ) ? $data['trigger'] : '';

        $index_status = get_post_meta( $post->ID, '_ds_indexnow_push_status', true );
        $index_time = get_post_meta( $post->ID, '_ds_indexnow_push_time', true );
        $index_raw = get_post_meta( $post->ID, '_ds_indexnow_push_result', true );
        $index_data = $index_raw ? json_decode( $index_raw, true ) : null;
        $index_message = is_array( $index_data ) && isset( $index_data['message'] ) ? $index_data['message'] : '';
        $index_trigger = is_array( $index_data ) && isset( $index_data['trigger'] ) ? $index_data['trigger'] : '';

        $google_enabled = $this->is_google_enabled();
        $google_auto_enabled = $this->is_google_auto_enabled();
        $google_status = get_post_meta( $post->ID, '_ds_google_indexing_push_status', true );
        $google_time = get_post_meta( $post->ID, '_ds_google_indexing_push_time', true );
        $google_raw = get_post_meta( $post->ID, '_ds_google_indexing_push_result', true );
        $google_data = $google_raw ? json_decode( $google_raw, true ) : null;
        $google_message = is_array( $google_data ) && isset( $google_data['message'] ) ? $google_data['message'] : '';
        $google_trigger = is_array( $google_data ) && isset( $google_data['trigger'] ) ? $google_data['trigger'] : '';

        echo '<p><strong>' . esc_html__( '百度推送：', 'developer-starter' ) . '</strong> ' . ( $enabled ? esc_html__( '已启用', 'developer-starter' ) : esc_html__( '未启用', 'developer-starter' ) ) . '</p>';

        if ( ! $status ) {
            echo '<p>' . esc_html__( '尚未推送（仅首次发布时推送）', 'developer-starter' ) . '</p>';
        } else {
            echo '<p><strong>' . esc_html__( '推送结果：', 'developer-starter' ) . '</strong> ' . esc_html( $status ) . '</p>';
            if ( $trigger ) {
                echo '<p><strong>' . esc_html__( '推送方式：', 'developer-starter' ) . '</strong> ' . esc_html( $trigger === 'auto' ? __( '自动', 'developer-starter' ) : __( '手动', 'developer-starter' ) ) . '</p>';
            }
            if ( $time ) {
                echo '<p><strong>' . esc_html__( '推送时间：', 'developer-starter' ) . '</strong> ' . esc_html( $time ) . '</p>';
            }
            if ( $message ) {
                echo '<p><strong>' . esc_html__( '返回信息：', 'developer-starter' ) . '</strong> ' . esc_html( $message ) . '</p>';
            }
            if ( $success !== null ) {
                echo '<p><strong>' . esc_html__( '成功条数：', 'developer-starter' ) . '</strong> ' . esc_html( $success ) . '</p>';
            }
            if ( $remain !== null ) {
                echo '<p><strong>' . esc_html__( '剩余配额：', 'developer-starter' ) . '</strong> ' . esc_html( $remain ) . '</p>';
            }
        }

        if ( $enabled && $post->post_status === 'publish' ) {
            $nonce = wp_create_nonce( 'ds_baidu_push_single_' . $post->ID );
            echo '<p><button type="button" class="button" id="ds-baidu-push-btn" data-post-id="' . esc_attr( $post->ID ) . '" data-nonce="' . esc_attr( $nonce ) . '">' . esc_html__( '手动推送', 'developer-starter' ) . '</button></p>';
            echo '<p class="description" id="ds-baidu-push-msg"></p>';
            ?>
            <script>
                (function() {
                    var pushingText = '<?php echo esc_js( __( '正在推送...', 'developer-starter' ) ); ?>';
                    var successText = '<?php echo esc_js( __( '推送成功', 'developer-starter' ) ); ?>';
                    var failText = '<?php echo esc_js( __( '推送失败', 'developer-starter' ) ); ?>';
                    var failRetryText = '<?php echo esc_js( __( '推送失败，请稍后再试', 'developer-starter' ) ); ?>';
                    var btn = document.getElementById('ds-baidu-push-btn');
                    if (!btn || typeof ajaxurl === 'undefined') return;
                    btn.addEventListener('click', function() {
                        var postId = btn.getAttribute('data-post-id');
                        var nonce = btn.getAttribute('data-nonce');
                        var msg = document.getElementById('ds-baidu-push-msg');
                        btn.disabled = true;
                        if (msg) msg.textContent = pushingText;
                        var data = new FormData();
                        data.append('action', 'ds_baidu_push_single');
                        data.append('post_id', postId);
                        data.append('nonce', nonce);
                        fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
                            .then(function(res) { return res.json(); })
                            .then(function(res) {
                                if (msg) {
                                    msg.textContent = res && res.data && res.data.message ? res.data.message : (res && res.success ? successText : failText);
                                }
                                if (res && res.success) {
                                    setTimeout(function(){ window.location.reload(); }, 600);
                                }
                            })
                            .catch(function() {
                                if (msg) msg.textContent = failRetryText;
                            })
                            .finally(function() {
                                btn.disabled = false;
                            });
                    });
                })();
            </script>
            <?php
        }

        echo '<hr style="margin:10px 0;border:none;border-top:1px solid #e5e7eb;">';
        echo '<p><strong>' . esc_html__( 'IndexNow：', 'developer-starter' ) . '</strong> ' . ( $index_enabled ? esc_html__( '已启用', 'developer-starter' ) : esc_html__( '未启用', 'developer-starter' ) ) . '</p>';

        if ( ! $index_status ) {
            echo '<p>' . esc_html__( '尚未推送（仅首次发布时推送）', 'developer-starter' ) . '</p>';
        } else {
            echo '<p><strong>' . esc_html__( '推送结果：', 'developer-starter' ) . '</strong> ' . esc_html( $index_status ) . '</p>';
            if ( $index_trigger ) {
                echo '<p><strong>' . esc_html__( '推送方式：', 'developer-starter' ) . '</strong> ' . esc_html( $index_trigger === 'auto' ? __( '自动', 'developer-starter' ) : __( '手动', 'developer-starter' ) ) . '</p>';
            }
            if ( $index_time ) {
                echo '<p><strong>' . esc_html__( '推送时间：', 'developer-starter' ) . '</strong> ' . esc_html( $index_time ) . '</p>';
            }
            if ( $index_message ) {
                echo '<p><strong>' . esc_html__( '返回信息：', 'developer-starter' ) . '</strong> ' . esc_html( $index_message ) . '</p>';
            }
        }

        if ( $index_enabled && $post->post_status === 'publish' ) {
            $nonce = wp_create_nonce( 'ds_indexnow_push_single_' . $post->ID );
            echo '<p><button type="button" class="button" id="ds-indexnow-push-btn" data-post-id="' . esc_attr( $post->ID ) . '" data-nonce="' . esc_attr( $nonce ) . '">' . esc_html__( '手动推送 IndexNow', 'developer-starter' ) . '</button></p>';
            echo '<p class="description" id="ds-indexnow-push-msg"></p>';
            ?>
            <script>
                (function() {
                    var pushingText = '<?php echo esc_js( __( '正在推送...', 'developer-starter' ) ); ?>';
                    var successText = '<?php echo esc_js( __( '推送成功', 'developer-starter' ) ); ?>';
                    var failText = '<?php echo esc_js( __( '推送失败', 'developer-starter' ) ); ?>';
                    var failRetryText = '<?php echo esc_js( __( '推送失败，请稍后再试', 'developer-starter' ) ); ?>';
                    var btn = document.getElementById('ds-indexnow-push-btn');
                    if (!btn || typeof ajaxurl === 'undefined') return;
                    btn.addEventListener('click', function() {
                        var postId = btn.getAttribute('data-post-id');
                        var nonce = btn.getAttribute('data-nonce');
                        var msg = document.getElementById('ds-indexnow-push-msg');
                        btn.disabled = true;
                        if (msg) msg.textContent = pushingText;
                        var data = new FormData();
                        data.append('action', 'ds_indexnow_push_single');
                        data.append('post_id', postId);
                        data.append('nonce', nonce);
                        fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
                            .then(function(res) { return res.json(); })
                            .then(function(res) {
                                if (msg) {
                                    msg.textContent = res && res.data && res.data.message ? res.data.message : (res && res.success ? successText : failText);
                                }
                                if (res && res.success) {
                                    setTimeout(function(){ window.location.reload(); }, 600);
                                }
                            })
                            .catch(function() {
                                if (msg) msg.textContent = failRetryText;
                            })
                            .finally(function() {
                                btn.disabled = false;
                            });
                    });
                })();
            </script>
            <?php
        }

        echo '<hr style="margin:10px 0;border:none;border-top:1px solid #e5e7eb;">';
        echo '<p><strong>' . esc_html__( 'Google Indexing：', 'developer-starter' ) . '</strong> ' . ( $google_enabled ? esc_html__( '已启用', 'developer-starter' ) : esc_html__( '未启用', 'developer-starter' ) ) . '</p>';
        if ( $google_enabled ) {
            echo '<p class="description">' . esc_html( $google_auto_enabled ? __( '发布时自动推送已开启。', 'developer-starter' ) : __( '仅手动推送；适合 Google 无法稳定访问的服务器。', 'developer-starter' ) ) . '</p>';
        }

        if ( ! $google_status ) {
            echo '<p>' . esc_html__( '尚未推送', 'developer-starter' ) . '</p>';
        } else {
            echo '<p><strong>' . esc_html__( '推送结果：', 'developer-starter' ) . '</strong> ' . esc_html( $google_status ) . '</p>';
            if ( $google_trigger ) {
                echo '<p><strong>' . esc_html__( '推送方式：', 'developer-starter' ) . '</strong> ' . esc_html( $google_trigger === 'auto' ? __( '自动', 'developer-starter' ) : __( '手动', 'developer-starter' ) ) . '</p>';
            }
            if ( $google_time ) {
                echo '<p><strong>' . esc_html__( '推送时间：', 'developer-starter' ) . '</strong> ' . esc_html( $google_time ) . '</p>';
            }
            if ( $google_message ) {
                echo '<p><strong>' . esc_html__( '返回信息：', 'developer-starter' ) . '</strong> ' . esc_html( $google_message ) . '</p>';
            }
        }

        if ( $google_enabled && $post->post_status === 'publish' ) {
            $nonce = wp_create_nonce( 'ds_google_indexing_push_single_' . $post->ID );
            echo '<p><button type="button" class="button" id="ds-google-indexing-push-btn" data-post-id="' . esc_attr( $post->ID ) . '" data-nonce="' . esc_attr( $nonce ) . '">' . esc_html__( '手动推送 Google', 'developer-starter' ) . '</button></p>';
            echo '<p class="description" id="ds-google-indexing-push-msg"></p>';
            ?>
            <script>
                (function() {
                    var pushingText = '<?php echo esc_js( __( '正在推送...', 'developer-starter' ) ); ?>';
                    var successText = '<?php echo esc_js( __( '推送成功', 'developer-starter' ) ); ?>';
                    var failText = '<?php echo esc_js( __( '推送失败', 'developer-starter' ) ); ?>';
                    var failRetryText = '<?php echo esc_js( __( '推送失败，请稍后再试', 'developer-starter' ) ); ?>';
                    var btn = document.getElementById('ds-google-indexing-push-btn');
                    if (!btn || typeof ajaxurl === 'undefined') return;
                    btn.addEventListener('click', function() {
                        var postId = btn.getAttribute('data-post-id');
                        var nonce = btn.getAttribute('data-nonce');
                        var msg = document.getElementById('ds-google-indexing-push-msg');
                        btn.disabled = true;
                        if (msg) msg.textContent = pushingText;
                        var data = new FormData();
                        data.append('action', 'ds_google_indexing_push_single');
                        data.append('post_id', postId);
                        data.append('nonce', nonce);
                        fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
                            .then(function(res) { return res.json(); })
                            .then(function(res) {
                                if (msg) {
                                    msg.textContent = res && res.data && res.data.message ? res.data.message : (res && res.success ? successText : failText);
                                }
                                if (res && res.success) {
                                    setTimeout(function(){ window.location.reload(); }, 600);
                                }
                            })
                            .catch(function() {
                                if (msg) msg.textContent = failRetryText;
                            })
                            .finally(function() {
                                btn.disabled = false;
                            });
                    });
                })();
            </script>
            <?php
        }
    }

    public function ajax_push_single() {
        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! $post_id || ! wp_verify_nonce( $nonce, 'ds_baidu_push_single_' . $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '安全校验失败', 'developer-starter' ) ) );
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        if ( ! $this->is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '未启用推送功能', 'developer-starter' ) ) );
        }
        $post = get_post( $post_id );
        if ( ! $this->can_push_post( $post ) ) {
            wp_send_json_error( array( 'message' => __( '不支持的文章类型', 'developer-starter' ) ) );
        }
        if ( $post->post_status !== 'publish' ) {
            wp_send_json_error( array( 'message' => __( '仅支持已发布内容', 'developer-starter' ) ) );
        }
        $token = $this->get_token();
        $site = $this->get_site();
        if ( $token === '' || $site === '' ) {
            wp_send_json_error( array( 'message' => __( '请先配置站点域名与Token', 'developer-starter' ) ) );
        }
        $url = get_permalink( $post );
        if ( ! $url ) {
            wp_send_json_error( array( 'message' => __( '获取链接失败', 'developer-starter' ) ) );
        }
        $result = $this->push_urls( array( $url ), $site, $token, 'manual' );
        $this->save_push_meta( $post_id, $result['status'], $result );
        if ( $result['status'] === 'success' ) {
            wp_send_json_success( array( 'message' => __( '推送成功', 'developer-starter' ), 'result' => $result ) );
        }
        $message = isset( $result['message'] ) ? $result['message'] : __( '推送失败', 'developer-starter' );
        wp_send_json_error( array( 'message' => $message, 'result' => $result ) );
    }

    public function ajax_push_custom() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'ds_baidu_push_custom' ) ) {
            wp_send_json_error( array( 'message' => __( '安全校验失败', 'developer-starter' ) ) );
        }
        if ( ! $this->is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '未启用推送功能', 'developer-starter' ) ) );
        }
        $token = $this->get_token();
        $site = $this->get_site();
        if ( $token === '' || $site === '' ) {
            wp_send_json_error( array( 'message' => __( '请先配置站点域名与Token', 'developer-starter' ) ) );
        }

        $raw = isset( $_POST['urls'] ) ? (string) wp_unslash( $_POST['urls'] ) : '';
        $lines = preg_split( '/\\r\\n|\\r|\\n/', $raw );
        $urls = array();
        $invalid = 0;
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( $line === '' ) {
                continue;
            }
            if ( ! preg_match( '#^https?://#i', $line ) ) {
                $invalid++;
                continue;
            }
            $clean = esc_url_raw( $line );
            if ( $clean === '' ) {
                $invalid++;
                continue;
            }
            $urls[] = $clean;
        }
        $urls = array_values( array_unique( $urls ) );

        if ( empty( $urls ) ) {
            wp_send_json_error( array( 'message' => __( '未检测到有效URL', 'developer-starter' ) ) );
        }
        if ( count( $urls ) > 2000 ) {
            wp_send_json_error( array( 'message' => __( '单次最多提交 2000 条链接', 'developer-starter' ) ) );
        }

        $result = $this->push_urls( $urls, $site, $token, 'manual' );

        if ( $result['status'] === 'success' ) {
            $data = isset( $result['data'] ) && is_array( $result['data'] ) ? $result['data'] : array();
            $success = isset( $data['success'] ) ? (int) $data['success'] : 0;
            $remain = isset( $data['remain'] ) ? (int) $data['remain'] : 0;
            $not_same = isset( $data['not_same_site'] ) && is_array( $data['not_same_site'] ) ? count( $data['not_same_site'] ) : 0;
            $not_valid = isset( $data['not_valid'] ) && is_array( $data['not_valid'] ) ? count( $data['not_valid'] ) : 0;

            $message = sprintf(
                /* translators: 1: success, 2: remain */
                __( '推送成功：%1$d 条，剩余配额：%2$d', 'developer-starter' ),
                $success,
                $remain
            );
            if ( $not_same || $not_valid || $invalid ) {
                $message .= '。' . sprintf(
                    /* translators: 1: not_same, 2: not_valid, 3: invalid */
                    __( '过滤/失败：非本站 %1$d 条，无效 %2$d 条，格式错误 %3$d 条', 'developer-starter' ),
                    $not_same,
                    $not_valid,
                    $invalid
                );
            }
            wp_send_json_success( array( 'message' => $message, 'result' => $result ) );
        }

        $message = isset( $result['message'] ) ? $result['message'] : __( '推送失败', 'developer-starter' );
        wp_send_json_error( array( 'message' => $message, 'result' => $result ) );
    }

    public function ajax_indexnow_push_single() {
        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! $post_id || ! wp_verify_nonce( $nonce, 'ds_indexnow_push_single_' . $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '安全校验失败', 'developer-starter' ) ) );
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        if ( ! $this->is_indexnow_enabled() ) {
            wp_send_json_error( array( 'message' => __( '未启用推送功能', 'developer-starter' ) ) );
        }
        $post = get_post( $post_id );
        if ( ! $this->can_push_post( $post ) ) {
            wp_send_json_error( array( 'message' => __( '不支持的文章类型', 'developer-starter' ) ) );
        }
        if ( $post->post_status !== 'publish' ) {
            wp_send_json_error( array( 'message' => __( '仅支持已发布内容', 'developer-starter' ) ) );
        }
        $key = $this->get_indexnow_key();
        if ( $key === '' ) {
            wp_send_json_error( array( 'message' => __( '请先配置 IndexNow Key', 'developer-starter' ) ) );
        }
        $url = get_permalink( $post );
        if ( ! $url ) {
            wp_send_json_error( array( 'message' => __( '获取链接失败', 'developer-starter' ) ) );
        }
        $result = $this->push_indexnow_urls( array( $url ), 'manual' );
        $this->save_indexnow_meta( $post_id, $result['status'], $result );
        if ( $result['status'] === 'success' ) {
            wp_send_json_success( array( 'message' => __( '推送成功', 'developer-starter' ), 'result' => $result ) );
        }
        $message = isset( $result['message'] ) ? $result['message'] : __( '推送失败', 'developer-starter' );
        wp_send_json_error( array( 'message' => $message, 'result' => $result ) );
    }

    public function ajax_indexnow_push_custom() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'ds_indexnow_push_custom' ) ) {
            wp_send_json_error( array( 'message' => __( '安全校验失败', 'developer-starter' ) ) );
        }
        if ( ! $this->is_indexnow_enabled() ) {
            wp_send_json_error( array( 'message' => __( '未启用推送功能', 'developer-starter' ) ) );
        }
        $key = $this->get_indexnow_key();
        if ( $key === '' ) {
            wp_send_json_error( array( 'message' => __( '请先配置 IndexNow Key', 'developer-starter' ) ) );
        }

        $raw = isset( $_POST['urls'] ) ? (string) wp_unslash( $_POST['urls'] ) : '';
        $lines = preg_split( '/\\r\\n|\\r|\\n/', $raw );
        $urls = array();
        $invalid = 0;
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( $line === '' ) {
                continue;
            }
            if ( ! preg_match( '#^https?://#i', $line ) ) {
                $invalid++;
                continue;
            }
            $clean = esc_url_raw( $line );
            if ( $clean === '' ) {
                $invalid++;
                continue;
            }
            $urls[] = $clean;
        }
        $urls = array_values( array_unique( $urls ) );

        if ( empty( $urls ) ) {
            wp_send_json_error( array( 'message' => __( '未检测到有效URL', 'developer-starter' ) ) );
        }
        if ( count( $urls ) > 10000 ) {
            wp_send_json_error( array( 'message' => __( '单次最多提交 10000 条链接', 'developer-starter' ) ) );
        }

        $result = $this->push_indexnow_urls( $urls, 'manual' );

        if ( $result['status'] === 'success' ) {
            $message = __( '推送成功', 'developer-starter' );
            if ( $invalid ) {
                $message .= '。' . sprintf( __( '格式错误 %d 条', 'developer-starter' ), $invalid );
            }
            wp_send_json_success( array( 'message' => $message, 'result' => $result ) );
        }

        $message = isset( $result['message'] ) ? $result['message'] : __( '推送失败', 'developer-starter' );
        wp_send_json_error( array( 'message' => $message, 'result' => $result ) );
    }

    public function ajax_google_push_single() {
        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! $post_id || ! wp_verify_nonce( $nonce, 'ds_google_indexing_push_single_' . $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '安全校验失败', 'developer-starter' ) ) );
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        if ( ! $this->is_google_enabled() ) {
            wp_send_json_error( array( 'message' => __( '未启用 Google Indexing API', 'developer-starter' ) ) );
        }
        $post = get_post( $post_id );
        if ( ! $this->can_push_post( $post ) ) {
            wp_send_json_error( array( 'message' => __( '不支持的文章类型', 'developer-starter' ) ) );
        }
        if ( $post->post_status !== 'publish' ) {
            wp_send_json_error( array( 'message' => __( '仅支持已发布内容', 'developer-starter' ) ) );
        }
        $url = get_permalink( $post );
        if ( ! $url ) {
            wp_send_json_error( array( 'message' => __( '获取链接失败', 'developer-starter' ) ) );
        }
        $result = $this->push_google_urls( array( $url ), 'manual' );
        $this->save_google_meta( $post_id, $result['status'], $result );
        if ( in_array( $result['status'], array( 'success', 'partial' ), true ) ) {
            wp_send_json_success( array( 'message' => isset( $result['message'] ) ? $result['message'] : __( '推送成功', 'developer-starter' ), 'result' => $result ) );
        }
        $message = isset( $result['message'] ) ? $result['message'] : __( '推送失败', 'developer-starter' );
        wp_send_json_error( array( 'message' => $message, 'result' => $result ) );
    }

    public function ajax_google_push_custom() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'ds_google_indexing_push_custom' ) ) {
            wp_send_json_error( array( 'message' => __( '安全校验失败', 'developer-starter' ) ) );
        }
        if ( ! $this->is_google_enabled() ) {
            wp_send_json_error( array( 'message' => __( '未启用 Google Indexing API', 'developer-starter' ) ) );
        }

        $parsed = $this->parse_urls_from_request();
        if ( empty( $parsed['urls'] ) ) {
            wp_send_json_error( array( 'message' => __( '未检测到有效URL', 'developer-starter' ) ) );
        }
        if ( count( $parsed['urls'] ) > 200 ) {
            wp_send_json_error( array( 'message' => __( 'Google 单次最多提交 200 条链接，请分批操作', 'developer-starter' ) ) );
        }

        $result = $this->push_google_urls( $parsed['urls'], 'manual' );
        $message = isset( $result['message'] ) ? $result['message'] : __( '推送完成', 'developer-starter' );
        if ( ! empty( $parsed['invalid'] ) ) {
            $message .= '。' . sprintf( __( '格式错误 %d 条', 'developer-starter' ), (int) $parsed['invalid'] );
        }

        if ( in_array( $result['status'], array( 'success', 'partial' ), true ) ) {
            wp_send_json_success( array( 'message' => $message, 'result' => $result ) );
        }
        wp_send_json_error( array( 'message' => $message, 'result' => $result ) );
    }

    private function parse_urls_from_request() {
        $raw = isset( $_POST['urls'] ) ? (string) wp_unslash( $_POST['urls'] ) : '';
        $lines = preg_split( '/\\r\\n|\\r|\\n/', $raw );
        $urls = array();
        $invalid = 0;
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( '' === $line ) {
                continue;
            }
            if ( ! preg_match( '#^https?://#i', $line ) ) {
                $invalid++;
                continue;
            }
            $clean = esc_url_raw( $line );
            if ( '' === $clean ) {
                $invalid++;
                continue;
            }
            $urls[] = $clean;
        }

        return array(
            'urls'    => array_values( array_unique( $urls ) ),
            'invalid' => $invalid,
        );
    }

    private function get_provider_label( $provider ) {
        if ( 'indexnow' === $provider ) {
            return __( 'IndexNow / Bing', 'developer-starter' );
        }
        if ( 'google' === $provider ) {
            return __( 'Google Indexing', 'developer-starter' );
        }
        return __( '百度', 'developer-starter' );
    }

    private function normalize_push_provider( $provider ) {
        $provider = sanitize_key( (string) $provider );
        return in_array( $provider, array( 'baidu', 'indexnow', 'google' ), true ) ? $provider : '';
    }

    private function push_post_to_provider( $post_id, $provider, $trigger = 'history' ) {
        $post = get_post( $post_id );
        if ( ! $this->can_push_post( $post ) || 'publish' !== $post->post_status ) {
            return array(
                'status'  => 'skipped',
                'message' => 'unsupported_post',
            );
        }

        $url = get_permalink( $post );
        if ( ! $url ) {
            return array(
                'status'  => 'error',
                'message' => 'empty_permalink',
            );
        }

        if ( 'indexnow' === $provider ) {
            if ( ! $this->is_indexnow_enabled() ) {
                return array( 'status' => 'skipped', 'message' => 'indexnow_disabled' );
            }
            if ( '' === $this->get_indexnow_key() ) {
                return array( 'status' => 'error', 'message' => 'missing_indexnow_key' );
            }
            $result = $this->push_indexnow_urls( array( $url ), $trigger );
            $this->save_indexnow_meta( $post_id, $result['status'], $result );
            return $result;
        }

        if ( 'google' === $provider ) {
            if ( ! $this->is_google_enabled() ) {
                return array( 'status' => 'skipped', 'message' => 'google_disabled' );
            }
            $result = $this->push_google_urls( array( $url ), $trigger );
            $this->save_google_meta( $post_id, $result['status'], $result );
            return $result;
        }

        if ( ! $this->is_enabled() ) {
            return array( 'status' => 'skipped', 'message' => 'baidu_disabled' );
        }
        $token = $this->get_token();
        $site = $this->get_site();
        if ( '' === $token || '' === $site ) {
            return array( 'status' => 'error', 'message' => 'missing_baidu_token_or_site' );
        }
        $result = $this->push_urls( array( $url ), $site, $token, $trigger );
        $this->save_push_meta( $post_id, $result['status'], $result );
        return $result;
    }

    public function ajax_push_history() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'ds_seo_push_history' ) ) {
            wp_send_json_error( array( 'message' => __( '安全校验失败', 'developer-starter' ) ) );
        }

        $provider = $this->normalize_push_provider( isset( $_POST['provider'] ) ? wp_unslash( (string) $_POST['provider'] ) : '' );
        if ( '' === $provider ) {
            wp_send_json_error( array( 'message' => __( '请选择推送通道', 'developer-starter' ) ) );
        }

        $mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( (string) $_POST['mode'] ) ) : 'pending';
        if ( ! in_array( $mode, array( 'pending', 'failed' ), true ) ) {
            $mode = 'pending';
        }
        $post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['post_type'] ) ) : 'post';
        if ( ! in_array( $post_type, array( 'post', 'page', 'any' ), true ) ) {
            $post_type = 'post';
        }
        $limit = isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 20;
        $limit = max( 1, min( 50, $limit ) );
        $status_key = $this->get_provider_status_meta_key( $provider );

        $meta_query = array(
            array(
                'key'     => $status_key,
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => $status_key,
                'value'   => 'success',
                'compare' => '!=',
            ),
        );
        if ( 'failed' === $mode ) {
            $meta_query = array(
                array(
                    'key'   => $status_key,
                    'value' => 'error',
                ),
            );
        }

        $query = new \WP_Query(
            array(
                'post_type'           => 'any' === $post_type ? array( 'post', 'page' ) : $post_type,
                'post_status'         => 'publish',
                'posts_per_page'      => $limit,
                'fields'              => 'ids',
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
                'meta_query'          => array_merge( array( 'relation' => 'OR' ), $meta_query ),
            )
        );
        $post_ids = is_array( $query->posts ) ? array_map( 'absint', $query->posts ) : array();

        if ( empty( $post_ids ) ) {
            wp_send_json_success(
                array(
                    'message' => __( '没有找到需要推送的历史内容', 'developer-starter' ),
                    'counts'  => array(),
                )
            );
        }

        $counts = array(
            'success' => 0,
            'partial' => 0,
            'error'   => 0,
            'skipped' => 0,
        );
        foreach ( $post_ids as $post_id ) {
            $result = $this->push_post_to_provider( $post_id, $provider, 'history' );
            $status = isset( $result['status'] ) ? (string) $result['status'] : 'error';
            if ( ! isset( $counts[ $status ] ) ) {
                $counts['error']++;
            } else {
                $counts[ $status ]++;
            }
        }

        $message = sprintf(
            /* translators: 1: provider, 2: processed, 3: success, 4: failed */
            __( '%1$s 批量推送完成：处理 %2$d 条，成功 %3$d 条，失败 %4$d 条。', 'developer-starter' ),
            $this->get_provider_label( $provider ),
            count( $post_ids ),
            $counts['success'] + $counts['partial'],
            $counts['error']
        );
        if ( $counts['skipped'] > 0 ) {
            $message .= sprintf( __( '跳过 %d 条。', 'developer-starter' ), $counts['skipped'] );
        }

        wp_send_json_success(
            array(
                'message' => $message,
                'counts'  => $counts,
            )
        );
    }
}

SEO_Push_Baidu::instance();
