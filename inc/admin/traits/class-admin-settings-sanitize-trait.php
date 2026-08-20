<?php
/**
 * Admin Settings Sanitize Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Sanitize_Trait {

    public function sanitize_options( $input, $context = array() ) {
        if ( ! is_array( $input ) ) return array();

        $context = is_array( $context ) ? $context : array();
        $is_import_restore = ! empty( $context['import_restore'] );

        if ( ! $is_import_restore && function_exists( 'developer_starter_is_option_serialization_broken' ) && developer_starter_is_option_serialization_broken( $this->option_name ) ) {
            add_settings_error(
                'developer_starter_settings',
                'ds_options_broken_save_blocked',
                __( '检测到主题设置序列化损坏，已阻止保存以避免覆盖数据。请先修复或从备份恢复后再保存。', 'developer-starter' ),
                'error'
            );
            $existing_options = get_option( $this->option_name, array() );
            return is_array( $existing_options ) ? $existing_options : array();
        }

        // 0. 解析前端 JSON 序列化数据（突破 max_input_vars 限制）
        if ( isset( $input['__json_payload'] ) && is_string( $input['__json_payload'] ) ) {
            $json_payload = $input['__json_payload'];
            $decoded      = json_decode( $json_payload, true );

            if ( ! is_array( $decoded ) ) {
                $decoded = json_decode( wp_unslash( $json_payload ), true );
            }

            if ( is_array( $decoded ) ) {
                $input = array_merge( $input, $decoded );
                unset( $input['__json_payload'] );
            }
        }

        // 1. 同步处理 License Key (新增逻辑)
        if ( isset( $input['theme_license_key'] ) ) {
            $old_key = get_option( 'theme_license_key' );
            $new_key = sanitize_text_field( $input['theme_license_key'] );

            if ( $old_key !== $new_key ) {
                update_option( 'theme_license_key', $new_key );
            }
        }

        // 获取现有选项，确保其他选项卡的设置不会被清空
        $existing_options = get_option( $this->option_name, array() );
        if ( ! is_array( $existing_options ) ) {
            $existing_options = array();
        }

        // 兼容旧字段：将新版本保存的字段映射回主题实际使用的字段
        if ( ! isset( $input['auth_captcha_enable'] ) ) {
            if ( isset( $input['captcha_enable'] ) ) {
                $input['auth_captcha_enable'] = $input['captcha_enable'];
            } elseif ( isset( $existing_options['captcha_enable'] ) && ! isset( $existing_options['auth_captcha_enable'] ) ) {
                $input['auth_captcha_enable'] = $existing_options['captcha_enable'];
            }
        }
        if ( ! isset( $input['id_verification_appcode'] ) ) {
            if ( isset( $input['id_verification_app_code'] ) ) {
                $input['id_verification_appcode'] = $input['id_verification_app_code'];
            } elseif ( isset( $existing_options['id_verification_app_code'] ) && ! isset( $existing_options['id_verification_appcode'] ) ) {
                $input['id_verification_appcode'] = $existing_options['id_verification_app_code'];
            }
        }

        if ( $is_import_restore && isset( $input['ai_connections'] ) && ! isset( $input['ai_connections_present'] ) ) {
            $input['ai_connections_present'] = '1';
        }

        // 站内资源字段：优先保存为相对路径，避免更换域名后主题设置失效
        $internal_asset_fields = array(
            'site_logo', 'mobile_logo', 'footer_logo', 'footer_bg_image',
            'announcement_image', 'lcp_preload_custom_url', 'default_thumbnail',
            'auth_page_background_image', 'auth_page_side_image', 'auth_modal_side_image', 'company_wechat_qrcode',
            'error_404_background_image',
        );

        // 纯资源地址字段（CDN/脚本/样式）
        $asset_url_fields = array(
            'swiper_css_url', 'swiper_js_url', 'chart_js_cdn',
            'prism_css_cdn', 'prism_js_cdn',
            'iconfont_js_url', 'translate_js_url',
            'custom_font_woff2_url', 'custom_font_woff_url',
            'custom_font_ttf_url', 'custom_font_otf_url',
        );

        // 普通链接字段：保留完整 URL
        $url_fields = array(
            'announcement_btn_url',
            'cdn_url',
            'id_verification_api_url',
        );

        // SVG 代码字段（需要安全清洗）
        $svg_fields = array(
            'site_logo_svg',
            'mobile_logo_svg',
        );

        $script_fields = array( 'baidu_analytics', 'custom_js' );
        $international_script_fields = array(
            'international_code_head_content',
            'international_code_footer_content',
            'international_code_analytics_content',
            'international_code_ads_content',
            'international_code_custom_content',
        );
        $allowed_script_tags = array(
            'script' => array(
                'src' => true,
                'type' => true,
                'async' => true,
                'defer' => true,
            ),
            'noscript' => array(),
        );

        $can_save_unfiltered_settings = current_user_can( 'manage_options' );

        // 清理新提交的数据。后台主题设置由管理员维护，默认只做轻量文本规范化；需要结构约束的字段在下方单独校验。
        $sanitized = array();
        foreach ( $input as $key => $value ) {
            if ( is_array( $value ) ) {
                $sanitized[ $key ] = $this->sanitize_array_recursive( $value );
            } elseif ( in_array( $key, $internal_asset_fields, true ) || in_array( $key, $asset_url_fields, true ) ) {
                $sanitized[ $key ] = function_exists( 'developer_starter_sanitize_asset_url' )
                    ? developer_starter_sanitize_asset_url( $value )
                    : esc_url_raw( $value );
            } elseif ( 'third_party_asset_allowed_hosts' === $key ) {
                $sanitized[ $key ] = function_exists( 'developer_starter_sanitize_external_asset_allowed_hosts' )
                    ? developer_starter_sanitize_external_asset_allowed_hosts( $value )
                    : sanitize_textarea_field( (string) $value );
            } elseif ( in_array( $key, $url_fields, true ) ) {
                // 普通链接字段保留完整 URL
                $sanitized[ $key ] = esc_url_raw( $value );
            } elseif ( in_array( $key, $svg_fields, true ) ) {
                if ( function_exists( 'developer_starter_sanitize_svg' ) ) {
                    $sanitized[ $key ] = developer_starter_sanitize_svg( $value );
                } else {
                    $sanitized[ $key ] = wp_kses_post( $value );
                }
            } elseif ( in_array( $key, $international_script_fields, true ) ) {
                $sanitized[ $key ] = $this->sanitize_international_third_party_code( $value );
            } elseif ( 'baidu_analytics' === $key ) {
                $sanitized[ $key ] = $this->normalize_baidu_analytics_value( $value, $allowed_script_tags );
            } elseif ( in_array( $key, $script_fields, true ) ) {
                $sanitized[ $key ] = $can_save_unfiltered_settings
                    ? $this->normalize_admin_setting_text( $value )
                    : wp_kses( $value, $allowed_script_tags );
            } else {
                $sanitized[ $key ] = $can_save_unfiltered_settings
                    ? $this->normalize_admin_setting_text( $value )
                    : wp_kses_post( $value );
            }
        }

        $custom_font_url_fields = array(
            'custom_font_woff2_url' => 'woff2',
            'custom_font_woff_url'  => 'woff',
            'custom_font_ttf_url'   => 'ttf',
            'custom_font_otf_url'   => 'otf',
        );
        foreach ( $custom_font_url_fields as $field_key => $extension ) {
            if ( isset( $sanitized[ $field_key ] ) ) {
                $sanitized[ $field_key ] = $this->sanitize_custom_font_url( $sanitized[ $field_key ], $extension );
            }
        }

        if ( isset( $sanitized['custom_font_enable'] ) ) {
            $sanitized['custom_font_enable'] = ( '1' === (string) $sanitized['custom_font_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['custom_font_family'] ) ) {
            $sanitized['custom_font_family'] = $this->sanitize_custom_font_family( $sanitized['custom_font_family'] );
        }
        if ( isset( $sanitized['custom_font_weight'] ) ) {
            $custom_font_weight = trim( (string) $sanitized['custom_font_weight'] );
            if ( ! preg_match( '/^(?:[1-9]00|[1-9]00\s+[1-9]00)$/', $custom_font_weight ) ) {
                $custom_font_weight = '400';
            }
            $sanitized['custom_font_weight'] = $custom_font_weight;
        }
        if ( isset( $sanitized['custom_font_style'] ) && ! in_array( (string) $sanitized['custom_font_style'], array( 'normal', 'italic' ), true ) ) {
            $sanitized['custom_font_style'] = 'normal';
        }
        if ( isset( $sanitized['custom_font_display'] ) && ! in_array( (string) $sanitized['custom_font_display'], array( 'auto', 'block', 'swap', 'fallback', 'optional' ), true ) ) {
            $sanitized['custom_font_display'] = 'swap';
        }

        if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            $sanitized = \Developer_Starter\Core\Design_Tokens::sanitize_options( $sanitized, $existing_options );
        }
        if ( class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            $sanitized = \Developer_Starter\Core\Content_Model_Center::sanitize_options( $sanitized, $existing_options );
        }
        if ( class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' ) ) {
            $sanitized = \Developer_Starter\Core\Blog_Visual_Manager::sanitize_options( $sanitized, $existing_options );
        }
        if ( class_exists( '\Developer_Starter\SEO\Industry_Schema_Engine' ) ) {
            $sanitized = \Developer_Starter\SEO\Industry_Schema_Engine::sanitize_options( $sanitized, $existing_options );
        }
        if ( class_exists( '\Developer_Starter\Core\Page_Performance_A11y_Auditor' ) ) {
            $sanitized = \Developer_Starter\Core\Page_Performance_A11y_Auditor::sanitize_options( $sanitized, $existing_options );
        }

        foreach ( array( 'company_name', 'company_phone', 'company_working_hours' ) as $schema_text_field ) {
            if ( isset( $sanitized[ $schema_text_field ] ) ) {
                $sanitized[ $schema_text_field ] = sanitize_text_field( (string) $sanitized[ $schema_text_field ] );
            }
        }
        foreach ( array( 'company_address', 'company_brief' ) as $schema_textarea_field ) {
            if ( isset( $sanitized[ $schema_textarea_field ] ) ) {
                $sanitized[ $schema_textarea_field ] = sanitize_textarea_field( (string) $sanitized[ $schema_textarea_field ] );
            }
        }
        if ( isset( $sanitized['company_email'] ) ) {
            $sanitized['company_email'] = sanitize_email( (string) $sanitized['company_email'] );
        }

        $ai_endpoint_allowlist = isset( $sanitized['ai_endpoint_allowlist'] )
            ? (string) $sanitized['ai_endpoint_allowlist']
            : ( isset( $existing_options['ai_endpoint_allowlist'] ) ? (string) $existing_options['ai_endpoint_allowlist'] : '' );
        if ( isset( $sanitized['ai_endpoint_allowlist'] ) ) {
            if ( class_exists( '\Developer_Starter\Core\AI\Connection_Manager' ) ) {
                $ai_endpoint_allowlist = implode(
                    "\n",
                    \Developer_Starter\Core\AI\Connection_Manager::normalize_allowed_endpoint_hosts( $ai_endpoint_allowlist )
                );
            } else {
                $ai_endpoint_allowlist = trim( sanitize_textarea_field( $ai_endpoint_allowlist ) );
            }
            $sanitized['ai_endpoint_allowlist'] = $ai_endpoint_allowlist;
        }

        if ( isset( $input['ai_connections_present'] ) ) {
            $sanitized['ai_connections'] = $this->sanitize_ai_connections(
                isset( $input['ai_connections'] ) ? $input['ai_connections'] : array(),
                $existing_options,
                $ai_endpoint_allowlist
            );
        }

        if ( isset( $sanitized['ai_builder_enable'] ) ) {
            $sanitized['ai_builder_enable'] = ( '1' === (string) $sanitized['ai_builder_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['ai_debug_log_enable'] ) ) {
            $sanitized['ai_debug_log_enable'] = ( '1' === (string) $sanitized['ai_debug_log_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['ai_default_system_prompt'] ) ) {
            $sanitized['ai_default_system_prompt'] = sanitize_textarea_field( (string) $sanitized['ai_default_system_prompt'] );
        }
        if ( isset( $sanitized['ai_default_temperature'] ) ) {
            $temperature = is_numeric( $sanitized['ai_default_temperature'] ) ? (float) $sanitized['ai_default_temperature'] : 0.4;
            if ( $temperature < 0 ) {
                $temperature = 0.0;
            } elseif ( $temperature > 2 ) {
                $temperature = 2.0;
            }
            $sanitized['ai_default_temperature'] = (string) $temperature;
        }
        if ( isset( $sanitized['ai_default_max_output_tokens'] ) ) {
            $tokens = absint( $sanitized['ai_default_max_output_tokens'] );
            if ( $tokens < 256 ) {
                $tokens = 4000;
            } elseif ( $tokens > 16000 ) {
                $tokens = 16000;
            }
            $sanitized['ai_default_max_output_tokens'] = (string) $tokens;
        }
        if ( isset( $sanitized['ai_default_request_timeout'] ) ) {
            $timeout = absint( $sanitized['ai_default_request_timeout'] );
            if ( $timeout < 10 ) {
                $timeout = 120;
            } elseif ( $timeout > 300 ) {
                $timeout = 300;
            }
            $sanitized['ai_default_request_timeout'] = (string) $timeout;
        }
        if ( isset( $sanitized['ai_default_max_modules'] ) ) {
            $max_modules = absint( $sanitized['ai_default_max_modules'] );
            if ( $max_modules < 1 ) {
                $max_modules = 8;
            } elseif ( $max_modules > 10 ) {
                $max_modules = 10;
            }
            $sanitized['ai_default_max_modules'] = (string) $max_modules;
        }

        if ( isset( $sanitized['ai_default_connection'] ) || isset( $sanitized['ai_connections'] ) ) {
            $connection_pool = isset( $sanitized['ai_connections'] ) && is_array( $sanitized['ai_connections'] )
                ? $sanitized['ai_connections']
                : ( isset( $existing_options['ai_connections'] ) && is_array( $existing_options['ai_connections'] ) ? $existing_options['ai_connections'] : array() );
            $enabled_connection_ids = array();
            foreach ( $connection_pool as $connection ) {
                if ( ! is_array( $connection ) || empty( $connection['id'] ) || empty( $connection['enabled'] ) ) {
                    continue;
                }
                $enabled_connection_ids[] = sanitize_key( (string) $connection['id'] );
            }
            $enabled_connection_ids = array_values( array_unique( array_filter( $enabled_connection_ids ) ) );

            $default_connection = isset( $sanitized['ai_default_connection'] )
                ? sanitize_key( (string) $sanitized['ai_default_connection'] )
                : ( isset( $existing_options['ai_default_connection'] ) ? sanitize_key( (string) $existing_options['ai_default_connection'] ) : '' );

            if ( '' !== $default_connection && ! in_array( $default_connection, $enabled_connection_ids, true ) ) {
                $default_connection = isset( $enabled_connection_ids[0] ) ? $enabled_connection_ids[0] : '';
            }

            if ( '' === $default_connection && isset( $enabled_connection_ids[0] ) ) {
                $default_connection = $enabled_connection_ids[0];
            }

            $sanitized['ai_default_connection'] = $default_connection;
        }

        // 实名接口地址：仅允许 http/https，非法值回退为默认官方地址。
        if ( isset( $sanitized['id_verification_api_url'] ) ) {
            $api_url = trim( (string) $sanitized['id_verification_api_url'] );
            $api_url = esc_url_raw( $api_url, array( 'http', 'https' ) );
            if ( '' === $api_url ) {
                $api_url = 'https://slytransf.market.alicloudapi.com/mobile_transfer';
            }
            $sanitized['id_verification_api_url'] = $api_url;
        }
        if ( isset( $sanitized['id_verification_ssl_verify'] ) && '1' !== (string) $sanitized['id_verification_ssl_verify'] ) {
            add_settings_error(
                'developer_starter_settings',
                'id_verification_ssl_verify_disabled',
                __( '实名认证 API SSL 验证已关闭。请仅在临时排查服务器根证书问题时关闭，处理完成后立即重新开启。', 'developer-starter' ),
                'warning'
            );
        }

        // 统一通知方式字段：白名单校验，避免保存异常值
        $notify_mode_fields = array(
            'notify_message_method',
            'notify_form_method',
            'notify_careers_method',
            'notify_submit_post_method',
            'notify_comment_method',
            'notify_account_deletion_method',
        );
        foreach ( $notify_mode_fields as $mode_field ) {
            if ( ! isset( $sanitized[ $mode_field ] ) ) {
                continue;
            }
            if ( ! in_array( $sanitized[ $mode_field ], array( 'none', 'email', 'push', 'both' ), true ) ) {
                $sanitized[ $mode_field ] = 'none';
            }
        }

        // 推送通道字段：仅保留安全字符（支持多选数组）
        $notify_channel_fields = array(
            'notify_message_push_channel',
            'notify_form_push_channel',
            'notify_careers_push_channel',
            'notify_submit_post_push_channel',
            'notify_comment_push_channel',
            'notify_account_deletion_push_channel',
            // 兼容过渡字段（多通道）
            'notify_message_push_channels',
            'notify_form_push_channels',
            'notify_careers_push_channels',
            'notify_submit_post_push_channels',
            'notify_comment_push_channels',
            'notify_account_deletion_push_channels',
        );
        $notify_multi_channel_fields = array(
            'notify_message_push_channel',
            'notify_form_push_channel',
            'notify_careers_push_channel',
            'notify_submit_post_push_channel',
            'notify_comment_push_channel',
            'notify_account_deletion_push_channel',
            'notify_message_push_channels',
            'notify_form_push_channels',
            'notify_careers_push_channels',
            'notify_submit_post_push_channels',
            'notify_comment_push_channels',
            'notify_account_deletion_push_channels',
        );
        foreach ( $notify_channel_fields as $channel_field ) {
            if ( ! isset( $sanitized[ $channel_field ] ) ) {
                continue;
            }
            $is_multi_field = in_array( $channel_field, $notify_multi_channel_fields, true );
            if ( is_array( $sanitized[ $channel_field ] ) ) {
                $clean_channels = array();
                foreach ( $sanitized[ $channel_field ] as $raw_channel_id ) {
                    $channel_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $raw_channel_id );
                    if ( $channel_id !== '' ) {
                        $clean_channels[] = $channel_id;
                    }
                }
                $sanitized[ $channel_field ] = array_values( array_unique( $clean_channels ) );
            } else {
                $channel_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $sanitized[ $channel_field ] );
                if ( $is_multi_field ) {
                    $sanitized[ $channel_field ] = $channel_id === '' ? array() : array( $channel_id );
                } else {
                    $sanitized[ $channel_field ] = $channel_id;
                }
            }
        }
        if ( isset( $sanitized['notify_comment_scope'] ) ) {
            $comment_scope = sanitize_key( (string) $sanitized['notify_comment_scope'] );
            $sanitized['notify_comment_scope'] = in_array( $comment_scope, array( 'pending', 'all' ), true ) ? $comment_scope : 'pending';
        }

        if ( isset( $sanitized['seo_push_baidu_site'] ) ) {
            $sanitized['seo_push_baidu_site'] = $this->normalize_site_domain( $sanitized['seo_push_baidu_site'] );
        }
        foreach ( array( 'seo_push_google_enable', 'seo_push_google_auto_enable' ) as $seo_push_checkbox_field ) {
            if ( isset( $sanitized[ $seo_push_checkbox_field ] ) ) {
                $sanitized[ $seo_push_checkbox_field ] = ( '1' === (string) $sanitized[ $seo_push_checkbox_field ] ) ? '1' : '';
            }
        }
        if ( empty( $sanitized['seo_push_google_enable'] ) ) {
            $sanitized['seo_push_google_auto_enable'] = '';
        }
        if ( isset( $input['seo_push_google_service_account_json'] ) ) {
            $raw_google_service_account = trim( $this->normalize_admin_setting_text( $input['seo_push_google_service_account_json'] ) );
            if ( '' === $raw_google_service_account ) {
                $sanitized['seo_push_google_service_account_json'] = '';
            } else {
                $decoded_google_service_account = json_decode( $raw_google_service_account, true );
                if ( is_array( $decoded_google_service_account ) && ! empty( $decoded_google_service_account['client_email'] ) && ! empty( $decoded_google_service_account['private_key'] ) ) {
                    $sanitized['seo_push_google_service_account_json'] = $can_save_unfiltered_settings
                        ? $raw_google_service_account
                        : wp_json_encode( $decoded_google_service_account, JSON_UNESCAPED_SLASHES );
                } else {
                    $sanitized['seo_push_google_service_account_json'] = isset( $existing_options['seo_push_google_service_account_json'] ) ? (string) $existing_options['seo_push_google_service_account_json'] : '';
                    add_settings_error(
                        'developer_starter_settings',
                        'ds_google_service_account_invalid',
                        __( 'Google Service Account JSON 格式无效，已保留原配置。', 'developer-starter' ),
                        'error'
                    );
                }
            }
        }

        if ( isset( $sanitized['js_loading_strategy'] ) && ! in_array( $sanitized['js_loading_strategy'], array( '', 'none', 'safe_defer', 'aggressive_defer' ), true ) ) {
            $sanitized['js_loading_strategy'] = '';
        }
        if ( isset( $sanitized['header_menu_layout'] ) && ! in_array( $sanitized['header_menu_layout'], array( 'default', 'menu_center', 'logo_center' ), true ) ) {
            $sanitized['header_menu_layout'] = 'default';
        }
        if ( isset( $sanitized['header_search_mode'] ) && ! in_array( $sanitized['header_search_mode'], array( 'icon', 'form' ), true ) ) {
            $sanitized['header_search_mode'] = 'icon';
        }
        foreach ( array( 'darkmode_auto_enable', 'darkmode_transition_enable', 'darkmode_image_dim_enable' ) as $darkmode_checkbox_field ) {
            if ( isset( $sanitized[ $darkmode_checkbox_field ] ) ) {
                $sanitized[ $darkmode_checkbox_field ] = ( '1' === (string) $sanitized[ $darkmode_checkbox_field ] ) ? '1' : '';
            }
        }
        if ( isset( $sanitized['darkmode_auto_mode'] ) ) {
            $darkmode_auto_mode = sanitize_key( (string) $sanitized['darkmode_auto_mode'] );
            $sanitized['darkmode_auto_mode'] = in_array( $darkmode_auto_mode, array( 'system_schedule', 'system', 'schedule' ), true ) ? $darkmode_auto_mode : 'system_schedule';
        }
        foreach ( array(
            'darkmode_sunrise_time' => '06:00',
            'darkmode_sunset_time'  => '18:00',
        ) as $darkmode_time_field => $darkmode_time_default ) {
            if ( isset( $sanitized[ $darkmode_time_field ] ) ) {
                $darkmode_time_value = trim( sanitize_text_field( (string) $sanitized[ $darkmode_time_field ] ) );
                if ( preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $darkmode_time_value, $darkmode_time_match ) ) {
                    $sanitized[ $darkmode_time_field ] = sprintf( '%02d:%02d', (int) $darkmode_time_match[1], (int) $darkmode_time_match[2] );
                } else {
                    $sanitized[ $darkmode_time_field ] = $darkmode_time_default;
                }
            }
        }
        if ( isset( $sanitized['blog_visual_preset'] ) && ! in_array( $sanitized['blog_visual_preset'], array( 'default', 'developer', 'minimal', 'artist' ), true ) ) {
            $sanitized['blog_visual_preset'] = 'default';
        }
        if ( isset( $sanitized['archive_loading_mode'] ) ) {
            $archive_loading_mode = sanitize_key( (string) $sanitized['archive_loading_mode'] );
            $sanitized['archive_loading_mode'] = in_array( $archive_loading_mode, array( 'regular', 'infinite' ), true ) ? $archive_loading_mode : 'regular';
        }
        if ( isset( $sanitized['category_default_sort'] ) ) {
            $category_default_sort = sanitize_key( (string) $sanitized['category_default_sort'] );
            $sanitized['category_default_sort'] = in_array( $category_default_sort, array( 'latest', 'random', 'hot', 'like', 'favorite' ), true ) ? $category_default_sort : 'latest';
        }
        if ( isset( $sanitized['search_result_excerpt_length'] ) ) {
            $search_excerpt_length = absint( $sanitized['search_result_excerpt_length'] );
            if ( $search_excerpt_length <= 0 ) {
                $search_excerpt_length = 40;
            }
            $sanitized['search_result_excerpt_length'] = (string) min( 120, max( 10, $search_excerpt_length ) );
        }
        foreach ( array( 'author_page_header_enable', 'author_page_show_avatar', 'author_page_show_bio', 'author_page_show_actions', 'author_page_show_social', 'author_page_show_stats', 'author_page_posts_summary_enable' ) as $author_checkbox_field ) {
            if ( isset( $sanitized[ $author_checkbox_field ] ) ) {
                $sanitized[ $author_checkbox_field ] = ( '1' === (string) $sanitized[ $author_checkbox_field ] ) ? '1' : '';
            }
        }
        if ( isset( $sanitized['author_page_stat_items'] ) ) {
            $author_stat_items_raw = is_array( $sanitized['author_page_stat_items'] ) ? $sanitized['author_page_stat_items'] : array_filter( preg_split( '/[\s,]+/', (string) $sanitized['author_page_stat_items'] ) );
            $author_stat_items = array();
            foreach ( $author_stat_items_raw as $author_stat_item ) {
                $author_stat_item = sanitize_key( (string) $author_stat_item );
                if ( in_array( $author_stat_item, array( 'posts', 'views', 'comments', 'joined' ), true ) ) {
                    $author_stat_items[] = $author_stat_item;
                }
            }
            $sanitized['author_page_stat_items'] = array_values( array_unique( $author_stat_items ) );
        }
        if ( isset( $sanitized['author_page_posts_columns'] ) ) {
            $author_posts_columns = (string) absint( $sanitized['author_page_posts_columns'] );
            $sanitized['author_page_posts_columns'] = in_array( $author_posts_columns, array( '2', '3', '4' ), true ) ? $author_posts_columns : '3';
        }
        foreach ( array( 'basic_page_header_enable', 'basic_page_header_description_enable', 'basic_page_content_padding_enable', 'basic_page_sidebar_enable', 'basic_page_featured_image_enable', 'basic_page_links_enable', 'basic_page_comments_enable' ) as $basic_page_checkbox_field ) {
            if ( isset( $sanitized[ $basic_page_checkbox_field ] ) ) {
                $sanitized[ $basic_page_checkbox_field ] = ( '1' === (string) $sanitized[ $basic_page_checkbox_field ] ) ? '1' : '';
            }
        }
        foreach ( array( 'careers_admin_menu_enable', 'woocommerce_admin_menu_enable' ) as $feature_entry_checkbox_field ) {
            if ( isset( $sanitized[ $feature_entry_checkbox_field ] ) ) {
                $sanitized[ $feature_entry_checkbox_field ] = ( '1' === (string) $sanitized[ $feature_entry_checkbox_field ] ) ? '1' : '';
            }
        }
        foreach ( array( 'post_speech_enable', 'comment_speech_enable', 'speech_pause_on_hidden' ) as $speech_checkbox_field ) {
            if ( isset( $sanitized[ $speech_checkbox_field ] ) ) {
                $sanitized[ $speech_checkbox_field ] = ( '1' === (string) $sanitized[ $speech_checkbox_field ] ) ? '1' : '';
            }
        }
        foreach ( array( 'comments_header_enable', 'comments_show_count', 'comments_show_empty_hint', 'comments_show_logged_in_as' ) as $comments_checkbox_field ) {
            if ( isset( $sanitized[ $comments_checkbox_field ] ) ) {
                $sanitized[ $comments_checkbox_field ] = ( '1' === (string) $sanitized[ $comments_checkbox_field ] ) ? '1' : '';
            }
        }
        if ( isset( $sanitized['comments_avatar_size'] ) ) {
            $comments_avatar_size = absint( $sanitized['comments_avatar_size'] );
            if ( $comments_avatar_size <= 0 ) {
                $comments_avatar_size = 48;
            }
            $sanitized['comments_avatar_size'] = (string) min( 96, max( 24, $comments_avatar_size ) );
        }
        foreach ( array( 'post_modified_date_enable' ) as $single_post_checkbox_field ) {
            if ( isset( $sanitized[ $single_post_checkbox_field ] ) ) {
                $sanitized[ $single_post_checkbox_field ] = ( '1' === (string) $sanitized[ $single_post_checkbox_field ] ) ? '1' : '';
            }
        }
        if ( isset( $sanitized['speech_language'] ) && ! in_array( (string) $sanitized['speech_language'], array( 'zh-CN', 'en-US' ), true ) ) {
            $sanitized['speech_language'] = 'zh-CN';
        }
        if ( isset( $sanitized['speech_voice_preference'] ) && ! in_array( (string) $sanitized['speech_voice_preference'], array( 'auto', 'female', 'male' ), true ) ) {
            $sanitized['speech_voice_preference'] = 'auto';
        }
        if ( isset( $sanitized['speech_rate'] ) ) {
            $speech_rate = is_numeric( $sanitized['speech_rate'] ) ? (float) $sanitized['speech_rate'] : 1.0;
            if ( $speech_rate < 0.6 ) {
                $speech_rate = 0.6;
            } elseif ( $speech_rate > 1.4 ) {
                $speech_rate = 1.4;
            }
            $sanitized['speech_rate'] = rtrim( rtrim( number_format( $speech_rate, 1, '.', '' ), '0' ), '.' );
        }
        if ( isset( $sanitized['speech_pitch'] ) ) {
            $speech_pitch = is_numeric( $sanitized['speech_pitch'] ) ? (float) $sanitized['speech_pitch'] : 1.0;
            if ( $speech_pitch < 0.6 ) {
                $speech_pitch = 0.6;
            } elseif ( $speech_pitch > 1.4 ) {
                $speech_pitch = 1.4;
            }
            $sanitized['speech_pitch'] = rtrim( rtrim( number_format( $speech_pitch, 1, '.', '' ), '0' ), '.' );
        }
        if ( isset( $sanitized['speech_volume'] ) ) {
            $speech_volume = is_numeric( $sanitized['speech_volume'] ) ? (float) $sanitized['speech_volume'] : 1.0;
            if ( $speech_volume < 0 ) {
                $speech_volume = 0.0;
            } elseif ( $speech_volume > 1 ) {
                $speech_volume = 1.0;
            }
            $sanitized['speech_volume'] = rtrim( rtrim( number_format( $speech_volume, 1, '.', '' ), '0' ), '.' );
        }
        foreach ( array( 'speech_voice_name', 'speech_voice_uri' ) as $speech_voice_text_field ) {
            if ( isset( $sanitized[ $speech_voice_text_field ] ) ) {
                $sanitized[ $speech_voice_text_field ] = trim( sanitize_text_field( (string) $sanitized[ $speech_voice_text_field ] ) );
            }
        }
        if ( isset( $sanitized['mobile_bottom_label_mode'] ) && ! in_array( $sanitized['mobile_bottom_label_mode'], array( 'icon_text', 'icon_only', 'text_only' ), true ) ) {
            $sanitized['mobile_bottom_label_mode'] = 'icon_text';
        }
        if ( isset( $sanitized['mobile_bottom_recommended_items'] ) && ! in_array( (string) $sanitized['mobile_bottom_recommended_items'], array( '3', '4', '5' ), true ) ) {
            $sanitized['mobile_bottom_recommended_items'] = '5';
        }
        if ( isset( $sanitized['footer_builder_enable'] ) ) {
            $sanitized['footer_builder_enable'] = ( '1' === (string) $sanitized['footer_builder_enable'] ) ? '1' : '';
        }
        foreach ( array( 'footer_builder_page_id', 'footer_builder_main_page_id', 'footer_builder_friend_page_id', 'footer_builder_bottom_page_id' ) as $footer_builder_page_field ) {
            if ( ! isset( $sanitized[ $footer_builder_page_field ] ) ) {
                continue;
            }
            $footer_builder_page_id = absint( $sanitized[ $footer_builder_page_field ] );
            $sanitized[ $footer_builder_page_field ] = ( $footer_builder_page_id > 0 && 'page' === get_post_type( $footer_builder_page_id ) ) ? (string) $footer_builder_page_id : '';
        }
        if ( isset( $sanitized['footer_builder_position'] ) && ! in_array( $sanitized['footer_builder_position'], array( 'replace_widgets', 'replace_friend_links', 'replace_bottom', 'replace_all' ), true ) ) {
            $sanitized['footer_builder_position'] = 'replace_widgets';
        }
        foreach ( array( 'footer_about_enable', 'footer_links_enable', 'footer_contact_enable', 'footer_follow_enable' ) as $footer_section_toggle ) {
            if ( isset( $sanitized[ $footer_section_toggle ] ) ) {
                $sanitized[ $footer_section_toggle ] = ( '1' === (string) $sanitized[ $footer_section_toggle ] ) ? '1' : '';
            }
        }
        if ( function_exists( 'developer_starter_sanitize_footer_visual_options' ) ) {
            $sanitized = developer_starter_sanitize_footer_visual_options( $sanitized );
        }
        if ( isset( $sanitized['search_builder_enable'] ) ) {
            $sanitized['search_builder_enable'] = ( '1' === (string) $sanitized['search_builder_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['search_builder_page_id'] ) ) {
            $search_builder_page_id = absint( $sanitized['search_builder_page_id'] );
            $sanitized['search_builder_page_id'] = ( $search_builder_page_id > 0 && 'page' === get_post_type( $search_builder_page_id ) ) ? (string) $search_builder_page_id : '';
        }
        if ( isset( $sanitized['search_builder_position'] ) && ! in_array( $sanitized['search_builder_position'], array( 'prepend_results', 'replace_header' ), true ) ) {
            $sanitized['search_builder_position'] = 'prepend_results';
        }
        $search_manager = class_exists( '\Developer_Starter\Core\Search_Mode_Manager' ) ? \Developer_Starter\Core\Search_Mode_Manager::get_instance() : null;
        if ( isset( $sanitized['search_default_mode'] ) ) {
            $sanitized['search_default_mode'] = $search_manager ? $search_manager->normalize_mode( $sanitized['search_default_mode'] ) : 'all';
        }
        if ( isset( $sanitized['search_mode_switch_enable'] ) ) {
            $sanitized['search_mode_switch_enable'] = ( '1' === (string) $sanitized['search_mode_switch_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['search_frontend_modes'] ) ) {
            $available_modes = $search_manager ? array_keys( $search_manager->get_modes( true ) ) : array( 'all', 'post' );
            $frontend_modes  = array_values( array_intersect( array_unique( array_map( 'sanitize_key', (array) $sanitized['search_frontend_modes'] ) ), $available_modes ) );
            $sanitized['search_frontend_modes'] = empty( $frontend_modes ) ? array( 'all' ) : $frontend_modes;
        }
        if ( isset( $sanitized['search_results_per_page'] ) ) {
            $per_page = absint( $sanitized['search_results_per_page'] );
            $sanitized['search_results_per_page'] = (string) ( in_array( $per_page, array( 12, 18, 24, 30 ), true ) ? $per_page : 18 );
        }
        if ( isset( $sanitized['search_hot_keywords'] ) ) {
            $sanitized['search_hot_keywords'] = sanitize_text_field( (string) $sanitized['search_hot_keywords'] );
        }
        foreach ( array( 'search_autocomplete_enable', 'search_autocomplete_include_pages', 'search_autocomplete_include_products', 'search_autocomplete_show_thumbnail', 'search_autocomplete_show_excerpt', 'search_autocomplete_show_price' ) as $search_autocomplete_toggle ) {
            if ( isset( $sanitized[ $search_autocomplete_toggle ] ) ) {
                $sanitized[ $search_autocomplete_toggle ] = ( '1' === (string) $sanitized[ $search_autocomplete_toggle ] ) ? '1' : '';
            }
        }
        if ( isset( $sanitized['search_autocomplete_min_chars'] ) ) {
            $search_autocomplete_min_chars = absint( $sanitized['search_autocomplete_min_chars'] );
            $sanitized['search_autocomplete_min_chars'] = (string) max( 1, min( 10, $search_autocomplete_min_chars ) );
        }
        if ( isset( $sanitized['search_autocomplete_max_results'] ) ) {
            $search_autocomplete_max_results = absint( $sanitized['search_autocomplete_max_results'] );
            $sanitized['search_autocomplete_max_results'] = (string) max( 1, min( 12, $search_autocomplete_max_results ) );
        }
        if ( isset( $sanitized['error_404_builder_enable'] ) ) {
            $sanitized['error_404_builder_enable'] = ( '1' === (string) $sanitized['error_404_builder_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['error_404_builder_page_id'] ) ) {
            $error_404_builder_page_id = absint( $sanitized['error_404_builder_page_id'] );
            $sanitized['error_404_builder_page_id'] = ( $error_404_builder_page_id > 0 && 'page' === get_post_type( $error_404_builder_page_id ) ) ? (string) $error_404_builder_page_id : '';
        }
        if ( isset( $sanitized['error_404_redirect_enable'] ) ) {
            $sanitized['error_404_redirect_enable'] = ( '1' === (string) $sanitized['error_404_redirect_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['error_404_redirect_status'] ) && ! in_array( (string) $sanitized['error_404_redirect_status'], array( '301', '302' ), true ) ) {
            $sanitized['error_404_redirect_status'] = '301';
        }
        if ( isset( $sanitized['error_404_redirect_rules'] ) ) {
            $sanitized['error_404_redirect_rules'] = function_exists( 'developer_starter_sanitize_404_redirect_rules' )
                ? developer_starter_sanitize_404_redirect_rules( $sanitized['error_404_redirect_rules'] )
                : sanitize_textarea_field( (string) $sanitized['error_404_redirect_rules'] );
        }
        if ( isset( $sanitized['error_404_preset'] ) && ! in_array( $sanitized['error_404_preset'], array( 'guide', 'clean', 'bold', 'image' ), true ) ) {
            $sanitized['error_404_preset'] = 'guide';
        }
        foreach ( array( 'error_404_code', 'error_404_title', 'error_404_primary_label', 'error_404_secondary_label', 'error_404_search_hint' ) as $error_404_text_field ) {
            if ( isset( $sanitized[ $error_404_text_field ] ) ) {
                $sanitized[ $error_404_text_field ] = sanitize_text_field( (string) $sanitized[ $error_404_text_field ] );
            }
        }
        if ( isset( $sanitized['error_404_description'] ) ) {
            $sanitized['error_404_description'] = sanitize_textarea_field( (string) $sanitized['error_404_description'] );
        }
        if ( isset( $sanitized['error_404_back_enable'] ) ) {
            $sanitized['error_404_back_enable'] = ( '1' === (string) $sanitized['error_404_back_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['error_404_search_enable'] ) ) {
            $sanitized['error_404_search_enable'] = ( '1' === (string) $sanitized['error_404_search_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['error_404_background_color'] ) ) {
            $sanitized['error_404_background_color'] = $this->sanitize_hex_color_value( $sanitized['error_404_background_color'], '#f8fafc' );
        }
        if ( isset( $sanitized['error_404_accent_color'] ) ) {
            $sanitized['error_404_accent_color'] = $this->sanitize_hex_color_value( $sanitized['error_404_accent_color'], '#2563eb' );
        }
        if ( isset( $sanitized['footer_text_color'] ) ) {
            $sanitized['footer_text_color'] = $this->sanitize_hex_color_value( $sanitized['footer_text_color'], '#ffffff' );
        }
        if ( isset( $sanitized['footer_heading_color'] ) ) {
            $fallback_heading_color = isset( $sanitized['footer_text_color'] ) ? $sanitized['footer_text_color'] : '#ffffff';
            $sanitized['footer_heading_color'] = $this->sanitize_hex_color_value( $sanitized['footer_heading_color'], $fallback_heading_color );
        }
        if ( isset( $sanitized['footer_heading_font_size'] ) ) {
            $footer_heading_font_size = absint( $sanitized['footer_heading_font_size'] );
            if ( $footer_heading_font_size < 12 ) {
                $footer_heading_font_size = 18;
            } elseif ( $footer_heading_font_size > 48 ) {
                $footer_heading_font_size = 48;
            }
            $sanitized['footer_heading_font_size'] = (string) $footer_heading_font_size;
        }
        if ( isset( $sanitized['module_css_load_mode'] ) && ! in_array( $sanitized['module_css_load_mode'], array( 'single', 'split' ), true ) ) {
            $sanitized['module_css_load_mode'] = 'single';
        }
        if ( isset( $sanitized['left_nav_display_mode'] ) && ! in_array( $sanitized['left_nav_display_mode'], array( 'all', 'except_home' ), true ) ) {
            $sanitized['left_nav_display_mode'] = 'all';
        }
        if ( isset( $sanitized['left_nav_excluded_page_ids'] ) ) {
            $left_nav_excluded_page_ids = array();
            preg_match_all( '/\d+/', (string) $sanitized['left_nav_excluded_page_ids'], $left_nav_excluded_page_id_matches );

            if ( ! empty( $left_nav_excluded_page_id_matches[0] ) ) {
                foreach ( $left_nav_excluded_page_id_matches[0] as $left_nav_excluded_page_id ) {
                    $left_nav_excluded_page_id = absint( $left_nav_excluded_page_id );

                    if ( $left_nav_excluded_page_id > 0 ) {
                        $left_nav_excluded_page_ids[ $left_nav_excluded_page_id ] = $left_nav_excluded_page_id;
                    }
                }
            }

            $sanitized['left_nav_excluded_page_ids'] = implode( ',', $left_nav_excluded_page_ids );
        }
        if ( isset( $sanitized['lcp_preload_mode'] ) && ! in_array( $sanitized['lcp_preload_mode'], array( 'featured', 'custom' ), true ) ) {
            $sanitized['lcp_preload_mode'] = 'featured';
        }
        if ( isset( $sanitized['security_headers_referrer_policy'] ) && ! in_array( $sanitized['security_headers_referrer_policy'], array( 'strict-origin-when-cross-origin', 'no-referrer', 'same-origin', 'origin-when-cross-origin', 'strict-origin' ), true ) ) {
            $sanitized['security_headers_referrer_policy'] = 'strict-origin-when-cross-origin';
        }
        if ( isset( $sanitized['security_headers_permissions_policy'] ) ) {
            $permissions_policy = (string) $sanitized['security_headers_permissions_policy'];
            $sanitized['security_headers_permissions_policy'] = function_exists( 'developer_starter_sanitize_permissions_policy_header' )
                ? developer_starter_sanitize_permissions_policy_header( $permissions_policy )
                : trim( (string) preg_replace( '/[\r\n\x00-\x1F\x7F]+/', '', $permissions_policy ) );
        }
        $runtime_whitelist_fields = array(
            'runtime_rest_whitelist_prefixes',
            'runtime_application_passwords_allowlist',
            'runtime_auto_update_allowlist',
            'runtime_block_editor_allowlist',
            'runtime_style_output_allowlist',
        );
        foreach ( $runtime_whitelist_fields as $runtime_whitelist_field ) {
            if ( ! isset( $sanitized[ $runtime_whitelist_field ] ) ) {
                continue;
            }

            $sanitized[ $runtime_whitelist_field ] = function_exists( 'developer_starter_sanitize_runtime_whitelist_field' )
                ? developer_starter_sanitize_runtime_whitelist_field( $runtime_whitelist_field, $sanitized[ $runtime_whitelist_field ] )
                : trim( sanitize_textarea_field( (string) $sanitized[ $runtime_whitelist_field ] ) );
        }

        $runtime_checkbox_fields = array( 'runtime_compat_safe_mode', 'admin_disable_wp7_blue_scheme' );
        if ( function_exists( 'developer_starter_get_dangerous_runtime_optimization_keys' ) ) {
            $runtime_checkbox_fields = array_merge( $runtime_checkbox_fields, developer_starter_get_dangerous_runtime_optimization_keys() );
        }
        $runtime_checkbox_fields = array_values( array_unique( $runtime_checkbox_fields ) );
        foreach ( $runtime_checkbox_fields as $runtime_checkbox_field ) {
            if ( isset( $sanitized[ $runtime_checkbox_field ] ) ) {
                $sanitized[ $runtime_checkbox_field ] = ( '1' === (string) $sanitized[ $runtime_checkbox_field ] ) ? '1' : '';
            }
        }
        if ( isset( $sanitized['pinyin_slug_enable'] ) ) {
            $sanitized['pinyin_slug_enable'] = ( '1' === (string) $sanitized['pinyin_slug_enable'] ) ? '1' : '';
        }
        if ( isset( $sanitized['pinyin_slug_mode'] ) && ! in_array( (string) $sanitized['pinyin_slug_mode'], array( 'full', 'abbr' ), true ) ) {
            $sanitized['pinyin_slug_mode'] = 'full';
        }
        if ( isset( $sanitized['pinyin_slug_divider'] ) && ! in_array( (string) $sanitized['pinyin_slug_divider'], array( '-', '_', '.', '' ), true ) ) {
            $sanitized['pinyin_slug_divider'] = '-';
        }
        if ( isset( $sanitized['pinyin_slug_max_length'] ) ) {
            $sanitized['pinyin_slug_max_length'] = (string) min( 200, absint( $sanitized['pinyin_slug_max_length'] ) );
        }
        if ( isset( $sanitized['enable_gutenberg_editor_style'] ) ) {
            $sanitized['enable_gutenberg_editor_style'] = ( '1' === (string) $sanitized['enable_gutenberg_editor_style'] ) ? '1' : '';
        }

        if ( ! empty( $sanitized['runtime_compat_safe_mode'] ) ) {
            add_settings_error(
                'developer_starter_settings',
                'runtime_compat_safe_mode_enabled',
                __( '兼容回滚模式已开启：高兼容风险的运行优化会暂时停用，原开关值仍会保留。', 'developer-starter' ),
                'warning'
            );
        } elseif ( function_exists( 'developer_starter_get_dangerous_runtime_optimization_keys' ) ) {
            $enabled_runtime_risky_keys = array();
            foreach ( developer_starter_get_dangerous_runtime_optimization_keys() as $runtime_risky_key ) {
                if ( ! empty( $sanitized[ $runtime_risky_key ] ) ) {
                    $enabled_runtime_risky_keys[] = $runtime_risky_key;
                }
            }
            if ( ! empty( $enabled_runtime_risky_keys ) ) {
                add_settings_error(
                    'developer_starter_settings',
                    'runtime_dangerous_optimizations_enabled',
                    sprintf(
                        /* translators: %d: enabled risky runtime optimization count */
                        __( '已启用 %d 个高兼容风险运行优化。请确认白名单已配置，并保留“兼容回滚模式”作为排障入口。', 'developer-starter' ),
                        count( $enabled_runtime_risky_keys )
                    ),
                    'warning'
                );
            }
        }
        if ( isset( $sanitized['registration_mode'] ) && ! in_array( $sanitized['registration_mode'], array( 'all', 'realname', 'email_only' ), true ) ) {
            $sanitized['registration_mode'] = 'all';
        }
        if ( isset( $sanitized['register_username_chinese_policy'] ) && ! in_array( $sanitized['register_username_chinese_policy'], array( 'allow', 'deny', 'scan' ), true ) ) {
            $sanitized['register_username_chinese_policy'] = 'allow';
        }
        if ( isset( $sanitized['register_email_domain_whitelist'] ) ) {
            $whitelist = wp_strip_all_tags( (string) $sanitized['register_email_domain_whitelist'] );
            $whitelist = preg_replace( "/\r\n|\r/u", "\n", $whitelist );
            $sanitized['register_email_domain_whitelist'] = trim( (string) $whitelist );
        }
        foreach ( array(
            'social_login_qq_app_id',
            'social_login_qq_app_key',
            'social_login_qq_icon',
            'social_login_github_client_id',
            'social_login_github_client_secret',
            'social_login_github_icon',
            'social_login_google_client_id',
            'social_login_google_client_secret',
            'social_login_google_icon',
        ) as $social_login_text_field ) {
            if ( isset( $sanitized[ $social_login_text_field ] ) ) {
                $sanitized[ $social_login_text_field ] = sanitize_text_field( (string) $sanitized[ $social_login_text_field ] );
            }
        }
        foreach ( array( 'social_login_qq_enable', 'social_login_github_enable', 'social_login_google_enable' ) as $social_login_checkbox_field ) {
            if ( isset( $sanitized[ $social_login_checkbox_field ] ) ) {
                $sanitized[ $social_login_checkbox_field ] = ( '1' === (string) $sanitized[ $social_login_checkbox_field ] ) ? '1' : '';
            }
        }
        if ( isset( $sanitized['multilingual_default_lang'] ) ) {
            $default_lang = sanitize_title( (string) $sanitized['multilingual_default_lang'] );
            if ( 'ja' === $default_lang ) {
                $default_lang = 'jp';
            }
            if ( $default_lang === '' ) {
                $default_lang = 'zh';
            }
            $sanitized['multilingual_default_lang'] = $default_lang;
        }
        if ( isset( $sanitized['multilingual_languages'] ) && is_array( $sanitized['multilingual_languages'] ) ) {
            $locale_map = array(
                'zh' => 'zh_CN',
                'cn' => 'zh_CN',
                'zh-tw' => 'zh_TW',
                'zh_tw' => 'zh_TW',
                'tw' => 'zh_TW',
                'en' => 'en_US',
                'jp' => 'ja_JP',
                'ja' => 'ja_JP',
                'ko' => 'ko_KR',
                'fr' => 'fr_FR',
                'de' => 'de_DE',
                'es' => 'es_ES',
                'ru' => 'ru_RU',
            );
            $normalized_languages = array();
            $seen_codes = array();

            foreach ( $sanitized['multilingual_languages'] as $language ) {
                if ( ! is_array( $language ) ) {
                    continue;
                }

                $name = isset( $language['name'] ) ? sanitize_text_field( (string) $language['name'] ) : '';
                $code = isset( $language['code'] ) ? sanitize_title( (string) $language['code'] ) : '';
                $locale = isset( $language['locale'] ) ? sanitize_text_field( (string) $language['locale'] ) : '';
                $icon = isset( $language['icon'] ) ? sanitize_text_field( (string) $language['icon'] ) : '';

                if ( 'ja' === $code ) {
                    $code = 'jp';
                }

                if ( '' === $name || '' === $code || isset( $seen_codes[ $code ] ) ) {
                    continue;
                }

                if ( '' === $locale && isset( $locale_map[ $code ] ) ) {
                    $locale = $locale_map[ $code ];
                }
                if ( '' === $locale ) {
                    $locale = 'zh_CN';
                }

                $seen_codes[ $code ] = true;
                $normalized_languages[] = array(
                    'name'   => $name,
                    'code'   => $code,
                    'locale' => $locale,
                    'icon'   => $icon,
                );
            }

            if ( ! empty( $normalized_languages ) ) {
                $sanitized['multilingual_languages'] = $normalized_languages;
            } else {
                unset( $sanitized['multilingual_languages'] );
            }
        }
        if ( isset( $sanitized['register_email_code_expire'] ) ) {
            $expire_minutes = absint( $sanitized['register_email_code_expire'] );
            if ( $expire_minutes < 1 ) {
                $expire_minutes = 10;
            } elseif ( $expire_minutes > 60 ) {
                $expire_minutes = 60;
            }
            $sanitized['register_email_code_expire'] = (string) $expire_minutes;
        }
        if ( isset( $sanitized['register_email_code_interval'] ) ) {
            $interval_seconds = absint( $sanitized['register_email_code_interval'] );
            if ( $interval_seconds < 30 ) {
                $interval_seconds = 60;
            } elseif ( $interval_seconds > 600 ) {
                $interval_seconds = 600;
            }
            $sanitized['register_email_code_interval'] = (string) $interval_seconds;
        }
        if ( isset( $sanitized['register_email_code_daily_ip_limit'] ) ) {
            $daily_ip_limit = absint( $sanitized['register_email_code_daily_ip_limit'] );
            if ( $daily_ip_limit < 1 ) {
                $daily_ip_limit = 30;
            } elseif ( $daily_ip_limit > 500 ) {
                $daily_ip_limit = 500;
            }
            $sanitized['register_email_code_daily_ip_limit'] = (string) $daily_ip_limit;
        }
        if ( isset( $sanitized['register_email_code_daily_email_limit'] ) ) {
            $daily_email_limit = absint( $sanitized['register_email_code_daily_email_limit'] );
            if ( $daily_email_limit < 1 ) {
                $daily_email_limit = 10;
            } elseif ( $daily_email_limit > 200 ) {
                $daily_email_limit = 200;
            }
            $sanitized['register_email_code_daily_email_limit'] = (string) $daily_email_limit;
        }
        if ( isset( $sanitized['auth_page_background_mode'] ) ) {
            $auth_page_background_mode = sanitize_key( (string) $sanitized['auth_page_background_mode'] );
            if ( ! in_array( $auth_page_background_mode, array( 'auto', 'preset', 'color', 'image' ), true ) ) {
                $auth_page_background_mode = 'auto';
            }
            $sanitized['auth_page_background_mode'] = $auth_page_background_mode;
        }
        if ( isset( $sanitized['auth_page_background_color'] ) ) {
            $auth_page_background_color = trim( (string) $sanitized['auth_page_background_color'] );
            if ( function_exists( 'sanitize_hex_color' ) ) {
                $hex_background_color = sanitize_hex_color( $auth_page_background_color );
                if ( is_string( $hex_background_color ) && '' !== $hex_background_color ) {
                    $auth_page_background_color = $hex_background_color;
                }
            }
            $sanitized['auth_page_background_color'] = $this->sanitize_admin_css_value( $auth_page_background_color, 220 );
        }
        if ( isset( $sanitized['auth_page_background_image_opacity'] ) ) {
            $auth_page_background_image_opacity = absint( $sanitized['auth_page_background_image_opacity'] );
            if ( $auth_page_background_image_opacity > 100 ) {
                $auth_page_background_image_opacity = 100;
            }
            $sanitized['auth_page_background_image_opacity'] = (string) $auth_page_background_image_opacity;
        }

        if ( isset( $sanitized['pc_logo_slogan_text'] ) ) {
            $slogan = trim( sanitize_text_field( (string) $sanitized['pc_logo_slogan_text'] ) );
            if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
                if ( mb_strlen( $slogan ) > 24 ) {
                    $slogan = mb_substr( $slogan, 0, 24 );
                }
            } elseif ( strlen( $slogan ) > 24 ) {
                $slogan = substr( $slogan, 0, 24 );
            }
            $sanitized['pc_logo_slogan_text'] = $slogan;
        }
        if ( isset( $sanitized['pc_logo_slogan_show_divider'] ) ) {
            $sanitized['pc_logo_slogan_show_divider'] = ( '1' === (string) $sanitized['pc_logo_slogan_show_divider'] ) ? '1' : '';
        }

        // 国际化基础工具箱：只处理新增字段，不接管现有中文区功能。
        $international_checkbox_fields = array(
            'international_third_party_code_enable',
            'international_cookie_notice_enable',
            'international_cookie_footer_button_enable',
            'international_typography_enable',
            'international_code_head_enable',
            'international_code_head_require_consent',
            'international_code_footer_enable',
            'international_code_footer_require_consent',
            'international_code_analytics_enable',
            'international_code_analytics_require_consent',
            'international_code_ads_enable',
            'international_code_ads_require_consent',
            'international_code_custom_enable',
            'international_code_custom_require_consent',
        );
        foreach ( $international_checkbox_fields as $international_checkbox_field ) {
            if ( isset( $sanitized[ $international_checkbox_field ] ) ) {
                $sanitized[ $international_checkbox_field ] = ( '1' === (string) $sanitized[ $international_checkbox_field ] ) ? '1' : '';
            }
        }

        $international_text_fields = array(
            'international_cookie_accept_text',
            'international_cookie_reject_text',
            'international_cookie_customize_text',
            'international_cookie_save_text',
            'international_cookie_policy_link_text',
            'international_cookie_footer_button_text',
        );
        foreach ( $international_text_fields as $international_text_field ) {
            if ( isset( $sanitized[ $international_text_field ] ) ) {
                $sanitized[ $international_text_field ] = trim( sanitize_text_field( (string) $sanitized[ $international_text_field ] ) );
            }
        }

        if ( isset( $sanitized['international_cookie_notice_text'] ) ) {
            $sanitized['international_cookie_notice_text'] = $can_save_unfiltered_settings
                ? trim( $this->normalize_admin_setting_text( $sanitized['international_cookie_notice_text'] ) )
                : trim( sanitize_textarea_field( (string) $sanitized['international_cookie_notice_text'] ) );
        }
        if ( isset( $sanitized['international_cookie_policy_url'] ) ) {
            $sanitized['international_cookie_policy_url'] = esc_url_raw( (string) $sanitized['international_cookie_policy_url'], array( 'http', 'https' ) );
        }
        if ( isset( $sanitized['international_cookie_region_preset'] ) && ! in_array( $sanitized['international_cookie_region_preset'], array( 'cn', 'eu', 'uk', 'us', 'cross_border' ), true ) ) {
            $sanitized['international_cookie_region_preset'] = 'cross_border';
        }
        if ( isset( $sanitized['international_cookie_consent_version'] ) ) {
            $version = preg_replace( '/[^A-Za-z0-9._-]/', '', (string) $sanitized['international_cookie_consent_version'] );
            $sanitized['international_cookie_consent_version'] = '' !== $version ? substr( $version, 0, 32 ) : '2.0';
        }
        if ( isset( $sanitized['international_cookie_notice_position'] ) && ! in_array( $sanitized['international_cookie_notice_position'], array( 'bottom_center', 'bottom_left', 'bottom_right' ), true ) ) {
            $sanitized['international_cookie_notice_position'] = 'bottom_center';
        }
        if ( isset( $sanitized['international_typography_mode'] ) && ! in_array( $sanitized['international_typography_mode'], array( 'auto', 'zh', 'en', 'ja', 'ko', 'rtl' ), true ) ) {
            $sanitized['international_typography_mode'] = 'auto';
        }
        $international_code_category_fields = array(
            'international_code_head_category',
            'international_code_footer_category',
            'international_code_analytics_category',
            'international_code_ads_category',
            'international_code_custom_category',
        );
        foreach ( $international_code_category_fields as $international_code_category_field ) {
            if ( ! isset( $sanitized[ $international_code_category_field ] ) ) {
                continue;
            }
            $category = sanitize_key( (string) $sanitized[ $international_code_category_field ] );
            $sanitized[ $international_code_category_field ] = in_array( $category, array( 'necessary', 'statistics', 'marketing', 'advertising', 'custom' ), true )
                ? $category
                : 'custom';
        }
        $international_code_position_fields = array(
            'international_code_head_position',
            'international_code_footer_position',
            'international_code_analytics_position',
            'international_code_ads_position',
            'international_code_custom_position',
        );
        foreach ( $international_code_position_fields as $international_code_position_field ) {
            if ( isset( $sanitized[ $international_code_position_field ] ) && ! in_array( $sanitized[ $international_code_position_field ], array( 'head', 'footer' ), true ) ) {
                $sanitized[ $international_code_position_field ] = 'footer';
            }
        }

        // 验证码配置：白名单 + 文本清洗
        if ( isset( $sanitized['captcha_provider'] ) && ! in_array( $sanitized['captcha_provider'], array( 'theme', 'aliyun' ), true ) ) {
            $sanitized['captcha_provider'] = 'theme';
        }
        if ( isset( $sanitized['aliyun_captcha_client_region'] ) && ! in_array( $sanitized['aliyun_captcha_client_region'], array( 'cn', 'sgp' ), true ) ) {
            $sanitized['aliyun_captcha_client_region'] = 'cn';
        }
        $captcha_text_fields = array(
            'aliyun_captcha_prefix',
            'aliyun_captcha_scene_auth',
            'aliyun_captcha_scene_search',
            'aliyun_captcha_region',
            'aliyun_captcha_endpoint',
            'aliyun_captcha_access_key_id',
            'aliyun_captcha_access_key_secret',
        );
        foreach ( $captcha_text_fields as $captcha_field ) {
            if ( ! isset( $sanitized[ $captcha_field ] ) ) {
                continue;
            }
            $sanitized[ $captcha_field ] = trim( sanitize_text_field( (string) $sanitized[ $captcha_field ] ) );
        }
        if ( isset( $sanitized['aliyun_captcha_region'] ) && '' === $sanitized['aliyun_captcha_region'] ) {
            $sanitized['aliyun_captcha_region'] = 'cn-shanghai';
        }
        if ( isset( $sanitized['aliyun_captcha_access_key_secret'] ) && '' === $sanitized['aliyun_captcha_access_key_secret'] && isset( $existing_options['aliyun_captcha_access_key_secret'] ) ) {
            $sanitized['aliyun_captcha_access_key_secret'] = (string) $existing_options['aliyun_captcha_access_key_secret'];
        }

        // 处理SMTP密码加密
        if ( isset( $sanitized['smtp_password'] ) ) {
            if ( '' === (string) $sanitized['smtp_password'] && isset( $input['smtp_password_existing'] ) ) {
                // 密码留空但有旧密码，保留并升级旧密码格式
                $existing_smtp_password = isset( $existing_options['smtp_password'] ) ? (string) $existing_options['smtp_password'] : '';
                $sanitized['smtp_password'] = \Developer_Starter\Core\SMTP_Manager::maybe_upgrade_password( $existing_smtp_password );
            } elseif ( '' !== (string) $sanitized['smtp_password'] ) {
                // 有新密码，加密存储
                $encrypted_smtp_password = \Developer_Starter\Core\SMTP_Manager::encrypt_password( $sanitized['smtp_password'] );
                if ( '' !== $encrypted_smtp_password ) {
                    $sanitized['smtp_password'] = $encrypted_smtp_password;
                } else {
                    $sanitized['smtp_password'] = isset( $existing_options['smtp_password'] ) ? (string) $existing_options['smtp_password'] : '';
                    add_settings_error(
                        'developer_starter_settings',
                        'smtp_password_encryption_failed',
                        __( 'SMTP 密码未保存：当前服务器缺少可用的 OpenSSL AES-GCM 或 sodium 加密能力。', 'developer-starter' ),
                        'error'
                    );
                }
            }
        }
        // 移除临时字段
        unset( $sanitized['smtp_password_existing'] );

        if ( isset( $sanitized['cleanup_cron_token'] ) ) {
            $cleanup_cron_token = function_exists( 'developer_starter_sanitize_cleanup_cron_token' )
                ? developer_starter_sanitize_cleanup_cron_token( $sanitized['cleanup_cron_token'] )
                : preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $sanitized['cleanup_cron_token'] );
            $cleanup_cron_token = is_string( $cleanup_cron_token ) ? substr( $cleanup_cron_token, 0, 128 ) : '';

            $existing_cleanup_cron_token = isset( $existing_options['cleanup_cron_token'] ) && function_exists( 'developer_starter_sanitize_cleanup_cron_token' )
                ? developer_starter_sanitize_cleanup_cron_token( $existing_options['cleanup_cron_token'] )
                : ( isset( $existing_options['cleanup_cron_token'] ) ? preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $existing_options['cleanup_cron_token'] ) : '' );
            $existing_cleanup_cron_token = is_string( $existing_cleanup_cron_token ) ? substr( $existing_cleanup_cron_token, 0, 128 ) : '';

            $cleanup_cron_enabled = ! empty( $sanitized['cleanup_cron_enable'] );
            if ( '' === $cleanup_cron_token ) {
                $cleanup_cron_token = strlen( $existing_cleanup_cron_token ) >= 16 ? $existing_cleanup_cron_token : '';
                if ( '' === $cleanup_cron_token && $cleanup_cron_enabled && function_exists( 'wp_generate_password' ) ) {
                    $cleanup_cron_token = wp_generate_password( 48, false, false );
                }
            }

            if ( '' !== $cleanup_cron_token && strlen( $cleanup_cron_token ) < 16 ) {
                add_settings_error(
                    'developer_starter_settings',
                    'cleanup_cron_token_too_short',
                    __( '外部定时清理密钥至少需要 16 位；已保留原密钥或自动生成新密钥。', 'developer-starter' ),
                    'error'
                );
                $cleanup_cron_token = strlen( $existing_cleanup_cron_token ) >= 16 ? $existing_cleanup_cron_token : '';
                if ( '' === $cleanup_cron_token && $cleanup_cron_enabled && function_exists( 'wp_generate_password' ) ) {
                    $cleanup_cron_token = wp_generate_password( 48, false, false );
                }
            }

            $sanitized['cleanup_cron_token'] = $cleanup_cron_token;
        }

        if ( isset( $sanitized['cleanup_cron_allowed_ips'] ) ) {
            $sanitized['cleanup_cron_allowed_ips'] = function_exists( 'developer_starter_sanitize_cleanup_cron_allowed_ips' )
                ? developer_starter_sanitize_cleanup_cron_allowed_ips( $sanitized['cleanup_cron_allowed_ips'] )
                : sanitize_textarea_field( (string) $sanitized['cleanup_cron_allowed_ips'] );
        }

        if ( ! empty( $sanitized['cleanup_cron_enable'] ) && empty( $sanitized['cleanup_cron_token'] ) ) {
            $existing_cleanup_cron_token = isset( $existing_options['cleanup_cron_token'] ) && function_exists( 'developer_starter_sanitize_cleanup_cron_token' )
                ? developer_starter_sanitize_cleanup_cron_token( $existing_options['cleanup_cron_token'] )
                : ( isset( $existing_options['cleanup_cron_token'] ) ? preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $existing_options['cleanup_cron_token'] ) : '' );
            $existing_cleanup_cron_token = is_string( $existing_cleanup_cron_token ) ? substr( $existing_cleanup_cron_token, 0, 128 ) : '';
            $sanitized['cleanup_cron_token'] = strlen( $existing_cleanup_cron_token ) >= 16
                ? $existing_cleanup_cron_token
                : ( function_exists( 'developer_starter_generate_cleanup_cron_token' ) ? developer_starter_generate_cleanup_cron_token() : substr( hash( 'sha256', mt_rand() . '|' . microtime( true ) ), 0, 48 ) );
        }

        // 合并：用新数据覆盖现有数据
        $merged = array_merge( $existing_options, $sanitized );

        // 兼容旧版本：移除已废弃配置（IndexNow 固定官方接口 + 自动 keyLocation）
        unset( $merged['seo_push_indexnow_key_location'] );
        unset( $merged['seo_push_indexnow_endpoint'] );
        unset( $merged['id_verification_request_method'] );
        unset( $merged['id_verification_allow_get_fallback'] );
        unset( $merged['ai_connections_present'] );
        unset( $merged['cleanup_rest_token'] );
        unset( $merged['cleanup_rest_allowed_ips'] );

        return $merged;
    }

    /**
     * Sanitize international third-party code snippets.
     *
     * Administrators with unfiltered_html can save platform-provided snippets
     * unchanged. Other users are limited to common non-event third-party tags.
     *
     * @param mixed $value Raw snippet.
     * @return string
     */
    private function sanitize_international_third_party_code( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( current_user_can( 'unfiltered_html' ) ) {
            return $value;
        }

        $allowed_tags = array(
            'script'   => array(
                'src'            => true,
                'type'           => true,
                'async'          => true,
                'defer'          => true,
                'id'             => true,
                'class'          => true,
                'crossorigin'    => true,
                'integrity'      => true,
                'referrerpolicy' => true,
                'nonce'          => true,
                'data-*'         => true,
            ),
            'noscript' => array(),
            'iframe'   => array(
                'src'            => true,
                'width'          => true,
                'height'         => true,
                'style'          => true,
                'id'             => true,
                'class'          => true,
                'title'          => true,
                'loading'        => true,
                'referrerpolicy' => true,
                'allow'          => true,
                'allowfullscreen' => true,
                'data-*'         => true,
            ),
            'img'      => array(
                'src'            => true,
                'width'          => true,
                'height'         => true,
                'style'          => true,
                'alt'            => true,
                'id'             => true,
                'class'          => true,
                'referrerpolicy' => true,
                'data-*'         => true,
            ),
            'link'     => array(
                'rel'            => true,
                'href'           => true,
                'id'             => true,
                'class'          => true,
                'crossorigin'    => true,
                'integrity'      => true,
                'referrerpolicy' => true,
                'data-*'         => true,
            ),
            'meta'     => array(
                'name'     => true,
                'content'  => true,
                'property' => true,
                'http-equiv' => true,
            ),
            'div'      => array(
                'id'     => true,
                'class'  => true,
                'style'  => true,
                'data-*' => true,
            ),
            'span'     => array(
                'id'     => true,
                'class'  => true,
                'style'  => true,
                'data-*' => true,
            ),
            'a'        => array(
                'href'   => true,
                'target' => true,
                'rel'    => true,
                'id'     => true,
                'class'  => true,
                'style'  => true,
                'data-*' => true,
            ),
            'style'    => array(
                'type' => true,
                'id'   => true,
            ),
        );

        return trim( wp_kses( $value, $allowed_tags ) );
    }

    /**
     * @param mixed               $connections raw connections
     * @param array<string,mixed> $existing_options existing options
     * @return array<int,array<string,mixed>>
     */
    private function sanitize_ai_connections( $connections, $existing_options, $allowed_hosts = null ) {
        $existing_connections = isset( $existing_options['ai_connections'] ) && is_array( $existing_options['ai_connections'] )
            ? $existing_options['ai_connections']
            : array();
        $existing_map = array();

        foreach ( $existing_connections as $existing_connection ) {
            if ( ! is_array( $existing_connection ) ) {
                continue;
            }
            $existing_id = isset( $existing_connection['id'] ) ? sanitize_key( (string) $existing_connection['id'] ) : '';
            if ( '' !== $existing_id ) {
                $existing_map[ $existing_id ] = $existing_connection;
            }
        }

        if ( ! is_array( $connections ) ) {
            return array();
        }

        $normalized = array();
        $seen_ids = array();

        foreach ( $connections as $connection ) {
            if ( ! is_array( $connection ) ) {
                continue;
            }

            $id = isset( $connection['id'] ) ? sanitize_key( (string) $connection['id'] ) : '';
            if ( '' === $id || isset( $seen_ids[ $id ] ) ) {
                continue;
            }
            $seen_ids[ $id ] = true;

            $name = isset( $connection['name'] ) ? sanitize_text_field( (string) $connection['name'] ) : '';
            $raw_endpoint = isset( $connection['endpoint'] ) ? trim( (string) $connection['endpoint'] ) : '';
            if ( class_exists( '\Developer_Starter\Core\AI\Connection_Manager' ) ) {
                $endpoint = \Developer_Starter\Core\AI\Connection_Manager::sanitize_endpoint_url( $raw_endpoint, $allowed_hosts );
                if ( '' !== $raw_endpoint && '' === $endpoint ) {
                    add_settings_error(
                        'developer_starter_settings',
                        'ai_endpoint_blocked_' . $id,
                        sprintf(
                            /* translators: %s: connection name */
                            __( 'AI 连接“%s”的接口地址已被拦截：仅允许公网 HTTPS 地址，且必须符合 allowlist。', 'developer-starter' ),
                            '' !== $name ? $name : $id
                        ),
                        'error'
                    );
                }
            } else {
                $endpoint = esc_url_raw( $raw_endpoint, array( 'https' ) );
            }
            $default_model = isset( $connection['default_model'] ) ? sanitize_text_field( (string) $connection['default_model'] ) : '';
            $api_key = isset( $connection['api_key'] ) ? sanitize_text_field( (string) $connection['api_key'] ) : '';
            $enabled = ! empty( $connection['enabled'] ) && (string) $connection['enabled'] === '1' ? '1' : '';
            $json_mode = ! isset( $connection['json_mode'] ) || (string) $connection['json_mode'] === '1' ? '1' : '';
            $models = $this->sanitize_ai_connection_models( isset( $connection['models'] ) ? $connection['models'] : '' );

            if ( '' === $api_key && ! empty( $connection['api_key_existing'] ) && isset( $existing_map[ $id ]['api_key'] ) ) {
                $api_key = sanitize_text_field( (string) $existing_map[ $id ]['api_key'] );
            }

            if ( '' === $name ) {
                $name = $id;
            }

            if ( '' !== $default_model && ! in_array( $default_model, $models, true ) ) {
                array_unshift( $models, $default_model );
            }

            $normalized[] = array(
                'id'            => $id,
                'name'          => $name,
                'endpoint'      => $endpoint,
                'default_model' => $default_model,
                'models'        => array_values( array_unique( array_filter( $models ) ) ),
                'api_key'       => $api_key,
                'enabled'       => $enabled,
                'json_mode'     => $json_mode,
            );
        }

        return $normalized;
    }

    /**
     * @param mixed $models raw models
     * @return array<int,string>
     */
    private function sanitize_ai_connection_models( $models ) {
        if ( is_array( $models ) ) {
            $items = $models;
        } else {
            $models = wp_strip_all_tags( (string) $models );
            $models = preg_replace( "/\r\n|\r/u", "\n", $models );
            $items = preg_split( '/[\n,]+/', (string) $models );
        }

        if ( ! is_array( $items ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $items as $item ) {
            $item = sanitize_text_field( (string) $item );
            $item = trim( $item );
            if ( '' !== $item ) {
                $normalized[] = $item;
            }
        }

        return array_values( array_unique( $normalized ) );
    }

    private function normalize_baidu_analytics_value( $value, $allowed_script_tags ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        $analytics_id = $this->extract_baidu_analytics_id( $value );
        if ( '' !== $analytics_id ) {
            return $analytics_id;
        }

        return current_user_can( 'manage_options' )
            ? $this->normalize_admin_setting_text( $value )
            : wp_kses( $value, $allowed_script_tags );
    }

    private function extract_baidu_analytics_id( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '#hm\\.js\\?([a-zA-Z0-9]+)#i', $value, $matches ) ) {
            return (string) $matches[1];
        }

        if ( preg_match( '/^[a-zA-Z0-9]+$/', $value ) ) {
            return $value;
        }

        return '';
    }

    private function sanitize_hex_color_value( $value, $fallback = '#ffffff' ) {
        $fallback = (string) $fallback;
        if ( function_exists( 'sanitize_hex_color' ) ) {
            $color = sanitize_hex_color( (string) $value );
            if ( is_string( $color ) && '' !== $color ) {
                return $color;
            }
            $fallback_color = sanitize_hex_color( $fallback );
            return is_string( $fallback_color ) && '' !== $fallback_color ? $fallback_color : '#ffffff';
        }

        $value = trim( (string) $value );
        if ( preg_match( '/^#(?:[0-9A-Fa-f]{3}){1,2}$/', $value ) ) {
            return $value;
        }

        return preg_match( '/^#(?:[0-9A-Fa-f]{3}){1,2}$/', $fallback ) ? $fallback : '#ffffff';
    }

    private function sanitize_custom_font_family( $value ) {
        $value = $this->normalize_admin_setting_text( $value );
        $value = trim( $value, " \t\n\r\0\x0B\"'" );

        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        return $value;
    }

    private function normalize_admin_setting_text( $value ) {
        $value = is_scalar( $value ) ? (string) $value : '';
        if ( function_exists( 'wp_check_invalid_utf8' ) ) {
            $value = wp_check_invalid_utf8( $value );
        }
        $value = str_replace( array( "\r\n", "\r" ), "\n", $value );
        $value = str_replace( "\0", '', $value );

        return $value;
    }

    private function sanitize_admin_css_value( $value, $max_length = 320 ) {
        $value = trim( $this->normalize_admin_setting_text( $value ) );
        if ( '' === $value ) {
            return '';
        }
        if ( strlen( $value ) > $max_length || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        return $value;
    }

    private function sanitize_custom_font_url( $value, $expected_extension ) {
        $value = function_exists( 'developer_starter_sanitize_asset_url' )
            ? developer_starter_sanitize_asset_url( $value )
            : esc_url_raw( $value );
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        $path = (string) wp_parse_url(
            function_exists( 'developer_starter_normalize_asset_url' ) ? developer_starter_normalize_asset_url( $value ) : $value,
            PHP_URL_PATH
        );
        $extension = strtolower( (string) pathinfo( $path, PATHINFO_EXTENSION ) );

        return strtolower( (string) $expected_extension ) === $extension ? $value : '';
    }

    private function sanitize_array_recursive( $arr ) {
        $result = array();
        foreach ( $arr as $k => $v ) {
            if ( is_array( $v ) ) {
                $result[ $k ] = $this->sanitize_array_recursive( $v );
            } else {
                // icon 字段允许 HTML 标签（如 <span>🔥</span>）
                if ( current_user_can( 'manage_options' ) ) {
                    $result[ $k ] = $this->normalize_admin_setting_text( $v );
                } elseif ( $k === 'icon' ) {
                    $result[ $k ] = wp_kses_post( $v );
                } else {
                    $result[ $k ] = sanitize_text_field( $v );
                }
            }
        }
        return $result;
    }
}
