<?php
/**
 * Helpers grouped split from class-helpers.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取主题选项内存缓存。
 *
 * @param bool $force_refresh 是否强制重新读取数据库/对象缓存。
 * @return array<string,mixed>
 */
if ( ! function_exists( 'developer_starter_get_options_cache' ) ) {
    function developer_starter_get_options_cache( $force_refresh = false ) {
        static $options = null;

        if ( null === $options || $force_refresh ) {
            $stored_options = get_option( 'developer_starter_options', array() );
            $options        = is_array( $stored_options ) ? $stored_options : array();
        }

        return $options;
    }
}

/**
 * 在主题初始化阶段预热主题选项缓存。
 *
 * @return void
 */
if ( ! function_exists( 'developer_starter_preload_options_cache' ) ) {
    function developer_starter_preload_options_cache() {
        developer_starter_get_options_cache();
    }
}

/**
 * 主题选项更新后刷新同请求内存缓存。
 *
 * @return void
 */
if ( ! function_exists( 'developer_starter_refresh_options_cache' ) ) {
    function developer_starter_refresh_options_cache() {
        developer_starter_get_options_cache( true );
    }
}
add_action( 'update_option_developer_starter_options', 'developer_starter_refresh_options_cache', 1 );

/**
 * 获取主题选项值
 *
 * @param string $key 选项键名。
 * @param mixed  $default 默认值。
 * @return mixed
 */
if ( ! function_exists( 'developer_starter_get_option' ) ) {
    function developer_starter_get_option( $key, $default = '' ) {
        $options = developer_starter_get_options_cache();
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }
}

if ( ! function_exists( 'developer_starter_resolve_auth_page_background_state' ) ) {
    /**
     * 解析独立登录/注册/找回密码页面的自定义背景状态。
     *
     * @return array{classes:array<int,string>,color:string,image_url:string,opacity:string}
     */
    function developer_starter_resolve_auth_page_background_state() {
        $mode = sanitize_key( (string) developer_starter_get_option( 'auth_page_background_mode', 'auto' ) );
        if ( ! in_array( $mode, array( 'auto', 'preset', 'color', 'image' ), true ) ) {
            $mode = 'auto';
        }
        if ( 'preset' === $mode ) {
            return array(
                'classes'   => array(),
                'color'     => '',
                'image_url' => '',
                'opacity'   => '',
            );
        }

        $color = '';
        $raw_color = (string) developer_starter_get_option( 'auth_page_background_color', '' );
        if ( '' !== $raw_color ) {
            $sanitized_color = function_exists( 'sanitize_hex_color' )
                ? sanitize_hex_color( $raw_color )
                : ( preg_match( '/^#(?:[0-9A-Fa-f]{3}){1,2}$/', $raw_color ) ? $raw_color : '' );
            $color = is_string( $sanitized_color ) ? $sanitized_color : '';
        }

        $image_url = '';
        $raw_image = developer_starter_get_option( 'auth_page_background_image', '' );
        if ( ! empty( $raw_image ) && function_exists( 'developer_starter_get_media_url' ) ) {
            $image_url = esc_url_raw( developer_starter_get_media_url( $raw_image ) );
        }

        $use_image = false;
        $use_color = false;
        if ( 'image' === $mode ) {
            $use_image = '' !== $image_url;
            $use_color = '' !== $color;
        } elseif ( 'color' === $mode ) {
            $use_color = '' !== $color;
        } elseif ( '' !== $image_url ) {
            $use_image = true;
            $use_color = '' !== $color;
        } elseif ( '' !== $color ) {
            $use_color = true;
        }

        $classes = array();
        if ( $use_color ) {
            $classes[] = 'has-auth-custom-bg-color';
        }
        $opacity = '';
        if ( $use_image ) {
            $opacity_percent = absint( developer_starter_get_option( 'auth_page_background_image_opacity', '80' ) );
            if ( $opacity_percent > 100 ) {
                $opacity_percent = 100;
            }
            $classes[] = 'has-auth-custom-bg-image';
            $opacity   = number_format( $opacity_percent / 100, 2, '.', '' );
        }

        return array(
            'classes'   => array_values( array_map( 'sanitize_html_class', $classes ) ),
            'color'     => $use_color ? $color : '',
            'image_url' => $use_image ? $image_url : '',
            'opacity'   => $opacity,
        );
    }
}

if ( ! function_exists( 'developer_starter_get_auth_page_background_attrs' ) ) {
    /**
     * 获取独立登录/注册/找回密码页面的背景 class。
     *
     * @return array{class:string}
     */
    function developer_starter_get_auth_page_background_attrs() {
        $state = developer_starter_resolve_auth_page_background_state();

        return array(
            'class' => empty( $state['classes'] ) ? '' : ' ' . implode( ' ', $state['classes'] ),
        );
    }
}

if ( ! function_exists( 'developer_starter_get_auth_page_background_css' ) ) {
    /**
     * 获取独立认证页面背景的动态 CSS 变量。
     *
     * @return string
     */
    function developer_starter_get_auth_page_background_css() {
        $state = developer_starter_resolve_auth_page_background_state();
        $css   = '';

        if ( ! empty( $state['color'] ) ) {
            $css .= '.auth-page.has-auth-custom-bg-color{--qiling-auth-custom-page-bg:' . $state['color'] . ';}';
        }
        if ( ! empty( $state['image_url'] ) ) {
            $image_url = str_replace(
                array( '\\', '"', "'", ')' ),
                array( '\\\\', '%22', '%27', '%29' ),
                esc_url( $state['image_url'] )
            );
            $css .= '.auth-page.has-auth-custom-bg-image{--qiling-auth-bg-image:url("' . $image_url . '");--qiling-auth-bg-image-opacity:' . $state['opacity'] . ';}';
        }

        return $css;
    }
}

if ( ! function_exists( 'developer_starter_get_qq_contact_link' ) ) {
    /**
     * 根据 QQ 联系方式生成前台可点击链接。
     *
     * 支持直接填写纯数字 QQ 号，也支持填写 http/https 联系链接。
     *
     * @param string $value QQ 号或链接。
     * @return string
     */
    function developer_starter_get_qq_contact_link( $value ) {
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '/^[0-9]+$/', $value ) ) {
            return 'https://wpa.qq.com/msgrd?v=3&uin=' . rawurlencode( $value ) . '&site=qq&menu=yes';
        }

        return esc_url_raw( $value, array( 'http', 'https' ) );
    }
}

if ( ! function_exists( 'developer_starter_get_registration_mode' ) ) {
    /**
     * 获取注册方式模式。
     *
     * @return string all|realname|email_only
     */
    function developer_starter_get_registration_mode() {
        $mode = developer_starter_get_option( 'registration_mode', null );
        if ( null === $mode ) {
            return 'all';
        }

        $mode = sanitize_key( (string) $mode );
        if ( ! in_array( $mode, array( 'all', 'realname', 'email_only' ), true ) ) {
            return 'all';
        }

        return $mode;
    }
}

if ( ! function_exists( 'developer_starter_is_legacy_sms_phone_only_enabled' ) ) {
    /**
     * 读取历史“仅允许手机号登录”开关。
     *
     * @return bool
     */
    function developer_starter_is_legacy_sms_phone_only_enabled() {
        return developer_starter_get_option( 'sms_phone_only', '' ) === '1';
    }
}

if ( ! function_exists( 'developer_starter_is_sms_phone_only_effective' ) ) {
    /**
     * 计算当前站点是否启用“仅手机号登录”效果。
     *
     * - 实名注册：始终启用（手机号 + 微信）
     * - 仅邮箱注册：始终禁用
     * - 全部注册：沿用历史 sms_phone_only 开关
     *
     * @return bool
     */
    function developer_starter_is_sms_phone_only_effective() {
        $mode = developer_starter_get_registration_mode();
        if ( 'realname' === $mode ) {
            return true;
        }
        if ( 'email_only' === $mode ) {
            return false;
        }

        return developer_starter_is_legacy_sms_phone_only_enabled();
    }
}

if ( ! function_exists( 'developer_starter_is_email_registration_allowed' ) ) {
    /**
     * 是否允许邮箱注册。
     *
     * @return bool
     */
    function developer_starter_is_email_registration_allowed() {
        $mode = developer_starter_get_registration_mode();
        if ( 'realname' === $mode ) {
            return false;
        }
        if ( 'email_only' === $mode ) {
            return true;
        }

        return ! developer_starter_is_legacy_sms_phone_only_enabled();
    }
}

if ( ! function_exists( 'developer_starter_is_phone_registration_allowed' ) ) {
    /**
     * 是否允许手机号注册。
     *
     * @return bool
     */
    function developer_starter_is_phone_registration_allowed() {
        if ( developer_starter_get_option( 'sms_enable', '' ) !== '1' ) {
            return false;
        }

        return developer_starter_get_registration_mode() !== 'email_only';
    }
}

if ( ! function_exists( 'developer_starter_is_weixin_registration_allowed' ) ) {
    /**
     * 是否允许微信注册入口。
     *
     * @return bool
     */
    function developer_starter_is_weixin_registration_allowed() {
        if ( developer_starter_get_registration_mode() === 'email_only' ) {
            return false;
        }

        return developer_starter_get_option( 'weixin_login_enable', '' ) === '1' && class_exists( 'qiling_weixin_login' );
    }
}

if ( ! function_exists( 'developer_starter_normalize_mobile_phone' ) ) {
    /**
     * 标准化中国大陆手机号。
     *
     * @param string $phone 原始手机号。
     * @return string 合法时返回 11 位手机号，否则返回空字符串。
     */
    function developer_starter_normalize_mobile_phone( $phone ) {
        $phone = preg_replace( '/[^0-9]/', '', (string) $phone );

        if ( strlen( $phone ) === 13 && strpos( $phone, '86' ) === 0 ) {
            $phone = substr( $phone, 2 );
        }

        if ( strlen( $phone ) !== 11 || ! preg_match( '/^1[3-9][0-9]{9}$/', $phone ) ) {
            return '';
        }

        return $phone;
    }
}

if ( ! function_exists( 'developer_starter_lookup_phone_location' ) ) {
    /**
     * 通过启灵安全防护插件查询手机号归属地。
     *
     * @param string $phone 手机号。
     * @param array  $args  查询参数。
     * @return array
     */
    function developer_starter_lookup_phone_location( $phone, $args = array() ) {
        $phone = developer_starter_normalize_mobile_phone( $phone );
        if ( '' === $phone ) {
            return array();
        }

        if ( ! function_exists( 'qilingsecurity_lookup_phone_location' ) ) {
            return array();
        }

        if ( function_exists( 'qilingsecurity_phone_location_lookup_enabled' ) && ! qilingsecurity_phone_location_lookup_enabled() ) {
            return array();
        }

        $result = qilingsecurity_lookup_phone_location( $phone, is_array( $args ) ? $args : array() );
        if ( ! is_array( $result ) || empty( $result['phone_segment'] ) ) {
            return array();
        }

        return array(
            'phone_segment' => isset( $result['phone_segment'] ) ? sanitize_text_field( (string) $result['phone_segment'] ) : '',
            'prefix'        => isset( $result['prefix'] ) ? sanitize_text_field( (string) $result['prefix'] ) : '',
            'province'      => isset( $result['province'] ) ? sanitize_text_field( (string) $result['province'] ) : '',
            'city'          => isset( $result['city'] ) ? sanitize_text_field( (string) $result['city'] ) : '',
            'isp'           => isset( $result['isp'] ) ? sanitize_text_field( (string) $result['isp'] ) : '',
            'tel_code'      => isset( $result['tel_code'] ) ? sanitize_text_field( (string) $result['tel_code'] ) : '',
            'postal_code'   => isset( $result['postal_code'] ) ? sanitize_text_field( (string) $result['postal_code'] ) : '',
            'area_code'     => isset( $result['area_code'] ) ? sanitize_text_field( (string) $result['area_code'] ) : '',
            'location_text' => isset( $result['location_text'] ) ? sanitize_text_field( (string) $result['location_text'] ) : '',
            'dat_version'   => isset( $result['dat_version'] ) ? sanitize_text_field( (string) $result['dat_version'] ) : '',
            'source'        => isset( $result['source'] ) ? sanitize_key( (string) $result['source'] ) : 'dat',
            'cache_hit'     => ! empty( $result['cache_hit'] ),
        );
    }
}

if ( ! function_exists( 'developer_starter_update_user_phone_location_meta' ) ) {
    /**
     * 刷新用户手机号归属地元数据。
     *
     * @param int    $user_id 用户 ID。
     * @param string $phone   手机号（可选，空时读取用户绑定手机号）。
     * @param array  $args    额外参数。
     * @return bool
     */
    function developer_starter_update_user_phone_location_meta( $user_id, $phone = '', $args = array() ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return false;
        }

        if ( '' === $phone ) {
            $phone = get_user_meta( $user_id, 'qiling_phone', true );
        }

        $phone = developer_starter_normalize_mobile_phone( $phone );
        if ( '' === $phone ) {
            return false;
        }

        $segment       = substr( $phone, 0, 7 );
        $current_seg   = (string) get_user_meta( $user_id, 'qiling_phone_location_segment', true );
        $current_label = (string) get_user_meta( $user_id, 'qiling_phone_location_label', true );
        $current_time  = (int) get_user_meta( $user_id, 'qiling_phone_location_updated', true );

        if ( $current_seg === $segment && $current_label !== '' && $current_time > 0 && ( time() - $current_time ) < ( 30 * DAY_IN_SECONDS ) ) {
            return true;
        }

        $lookup_args = is_array( $args ) ? $args : array();
        if ( empty( $lookup_args['context'] ) ) {
            $lookup_args['context'] = 'theme_user_sync';
        }

        $result = developer_starter_lookup_phone_location( $phone, $lookup_args );
        if ( empty( $result ) ) {
            return false;
        }

        update_user_meta( $user_id, 'qiling_phone_location_segment', $segment );
        update_user_meta( $user_id, 'qiling_phone_location_prefix', isset( $result['prefix'] ) ? (string) $result['prefix'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_province', isset( $result['province'] ) ? (string) $result['province'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_city', isset( $result['city'] ) ? (string) $result['city'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_isp', isset( $result['isp'] ) ? (string) $result['isp'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_tel_code', isset( $result['tel_code'] ) ? (string) $result['tel_code'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_postal_code', isset( $result['postal_code'] ) ? (string) $result['postal_code'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_area_code', isset( $result['area_code'] ) ? (string) $result['area_code'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_label', isset( $result['location_text'] ) ? (string) $result['location_text'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_dat_version', isset( $result['dat_version'] ) ? (string) $result['dat_version'] : '' );
        update_user_meta( $user_id, 'qiling_phone_location_source', isset( $result['source'] ) ? (string) $result['source'] : 'dat' );
        update_user_meta( $user_id, 'qiling_phone_location_updated', time() );

        do_action( 'developer_starter_phone_location_synced', $user_id, $phone, $result, $lookup_args );

        return true;
    }
}

if ( ! function_exists( 'developer_starter_clear_user_phone_location_meta' ) ) {
    /**
     * 清理用户手机号归属地元数据。
     *
     * @param int $user_id 用户 ID。
     * @return void
     */
    function developer_starter_clear_user_phone_location_meta( $user_id ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return;
        }

        $meta_keys = array(
            'qiling_phone_location_segment',
            'qiling_phone_location_prefix',
            'qiling_phone_location_province',
            'qiling_phone_location_city',
            'qiling_phone_location_isp',
            'qiling_phone_location_tel_code',
            'qiling_phone_location_postal_code',
            'qiling_phone_location_area_code',
            'qiling_phone_location_label',
            'qiling_phone_location_dat_version',
            'qiling_phone_location_source',
            'qiling_phone_location_updated',
        );

        foreach ( $meta_keys as $meta_key ) {
            delete_user_meta( $user_id, $meta_key );
        }
    }
}

if ( ! function_exists( 'developer_starter_get_user_phone_location_text' ) ) {
    /**
     * 获取用户手机号归属地展示文本（缺失时尝试懒刷新一次）。
     *
     * @param int    $user_id 用户 ID。
     * @param string $phone   手机号（可选）。
     * @return string
     */
    function developer_starter_get_user_phone_location_text( $user_id, $phone = '' ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return '';
        }

        $text = (string) get_user_meta( $user_id, 'qiling_phone_location_label', true );
        if ( '' !== $text ) {
            return $text;
        }

        developer_starter_update_user_phone_location_meta(
            $user_id,
            $phone,
            array(
                'context' => 'theme_account_lazy_refresh',
            )
        );

        return (string) get_user_meta( $user_id, 'qiling_phone_location_label', true );
    }
}

if ( ! function_exists( 'developer_starter_sync_phone_location_for_user_meta' ) ) {
    /**
     * 用户手机号元数据变更时，同步归属地缓存。
     *
     * @param int    $user_id 用户 ID。
     * @param string $phone   手机号。
     * @param string $context 触发上下文。
     * @return void
     */
    function developer_starter_sync_phone_location_for_user_meta( $user_id, $phone, $context ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return;
        }

        $phone = developer_starter_normalize_mobile_phone( $phone );
        if ( '' === $phone ) {
            developer_starter_clear_user_phone_location_meta( $user_id );
            return;
        }

        developer_starter_update_user_phone_location_meta(
            $user_id,
            $phone,
            array(
                'context' => sanitize_key( (string) $context ),
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_handle_added_user_phone_meta' ) ) {
    /**
     * 监听新增用户元数据。
     *
     * @param int    $meta_id    元数据 ID。
     * @param int    $user_id    用户 ID。
     * @param string $meta_key   元数据键。
     * @param mixed  $meta_value 元数据值。
     * @return void
     */
    function developer_starter_handle_added_user_phone_meta( $meta_id, $user_id, $meta_key, $meta_value ) {
        unset( $meta_id );

        if ( 'qiling_phone' !== (string) $meta_key ) {
            return;
        }

        developer_starter_sync_phone_location_for_user_meta( $user_id, (string) $meta_value, 'user_meta_added' );
    }
}

if ( ! function_exists( 'developer_starter_handle_updated_user_phone_meta' ) ) {
    /**
     * 监听更新用户元数据。
     *
     * @param int    $meta_id    元数据 ID。
     * @param int    $user_id    用户 ID。
     * @param string $meta_key   元数据键。
     * @param mixed  $meta_value 元数据值。
     * @return void
     */
    function developer_starter_handle_updated_user_phone_meta( $meta_id, $user_id, $meta_key, $meta_value ) {
        unset( $meta_id );

        if ( 'qiling_phone' !== (string) $meta_key ) {
            return;
        }

        developer_starter_sync_phone_location_for_user_meta( $user_id, (string) $meta_value, 'user_meta_updated' );
    }
}

if ( ! function_exists( 'developer_starter_handle_deleted_user_phone_meta' ) ) {
    /**
     * 监听删除用户元数据。
     *
     * @param array  $meta_ids   元数据 ID 列表。
     * @param int    $user_id    用户 ID。
     * @param string $meta_key   元数据键。
     * @param mixed  $meta_value 元数据值。
     * @return void
     */
    function developer_starter_handle_deleted_user_phone_meta( $meta_ids, $user_id, $meta_key, $meta_value ) {
        unset( $meta_ids, $meta_value );

        if ( 'qiling_phone' !== (string) $meta_key ) {
            return;
        }

        developer_starter_clear_user_phone_location_meta( $user_id );
    }
}

add_action( 'added_user_meta', 'developer_starter_handle_added_user_phone_meta', 10, 4 );
add_action( 'updated_user_meta', 'developer_starter_handle_updated_user_phone_meta', 10, 4 );
add_action( 'deleted_user_meta', 'developer_starter_handle_deleted_user_phone_meta', 10, 4 );

if ( ! function_exists( 'developer_starter_has_available_registration_method' ) ) {
    /**
     * 是否存在可用注册方式。
     *
     * @return bool
     */
    function developer_starter_has_available_registration_method() {
        if ( ! get_option( 'users_can_register' ) ) {
            return false;
        }

        return developer_starter_is_email_registration_allowed()
            || developer_starter_is_phone_registration_allowed()
            || developer_starter_is_weixin_registration_allowed();
    }
}

if ( ! function_exists( 'developer_starter_get_email_domain_whitelist' ) ) {
    /**
     * 获取邮箱后缀白名单（标准化后，统一返回 @domain.tld 形式）。
     *
     * @return array
     */
    function developer_starter_get_email_domain_whitelist() {
        $raw = developer_starter_get_option( 'register_email_domain_whitelist', '' );
        if ( is_array( $raw ) ) {
            $raw = implode( "\n", $raw );
        }

        $raw = wp_strip_all_tags( (string) $raw );
        $raw = strtolower( $raw );

        $parts = preg_split( '/[\s,，;；|]+/u', $raw );
        if ( ! is_array( $parts ) ) {
            return array();
        }

        $domains = array();
        foreach ( $parts as $part ) {
            $part = trim( (string) $part );
            if ( '' === $part ) {
                continue;
            }

            // 允许用户输入 @qq.com、qq.com、user@qq.com，统一提取域名部分。
            if ( false !== strpos( $part, '@' ) ) {
                $part = substr( $part, (int) strrpos( $part, '@' ) + 1 );
            }

            $part = ltrim( $part, '.@' );
            $part = rtrim( $part, '.' );
            $part = preg_replace( '/[^a-z0-9\.-]/', '', $part );

            if ( '' === $part || false === strpos( $part, '.' ) ) {
                continue;
            }

            if ( ! preg_match( '/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)+$/', $part ) ) {
                continue;
            }

            $suffix = '@' . $part;
            $domains[ $suffix ] = $suffix;
        }

        return array_values( $domains );
    }
}

if ( ! function_exists( 'developer_starter_get_email_domain_whitelist_text' ) ) {
    /**
     * 获取邮箱后缀白名单展示文本。
     *
     * @param string $separator 分隔符。
     * @return string
     */
    function developer_starter_get_email_domain_whitelist_text( $separator = '、' ) {
        $list = developer_starter_get_email_domain_whitelist();
        if ( empty( $list ) ) {
            return '';
        }

        return implode( (string) $separator, $list );
    }
}

if ( ! function_exists( 'developer_starter_is_email_domain_allowed' ) ) {
    /**
     * 检查邮箱后缀是否在白名单内。
     *
     * 规则：
     * - 白名单为空：不限制，返回 true。
     * - 白名单非空：仅允许匹配白名单后缀。
     *
     * @param string $email 邮箱。
     * @return bool
     */
    function developer_starter_is_email_domain_allowed( $email ) {
        $whitelist = developer_starter_get_email_domain_whitelist();
        if ( empty( $whitelist ) ) {
            return true;
        }

        $email = strtolower( trim( (string) $email ) );
        if ( ! is_email( $email ) ) {
            return false;
        }

        $at_pos = strrpos( $email, '@' );
        if ( false === $at_pos ) {
            return false;
        }

        $suffix = '@' . substr( $email, $at_pos + 1 );
        return in_array( $suffix, $whitelist, true );
    }
}

if ( ! function_exists( 'developer_starter_is_register_email_code_enabled' ) ) {
    /**
     * 是否启用邮箱注册验证码。
     *
     * @return bool
     */
    function developer_starter_is_register_email_code_enabled() {
        return developer_starter_get_option( 'register_email_code_enable', '' ) === '1';
    }
}

if ( ! function_exists( 'developer_starter_get_register_email_code_expire_minutes' ) ) {
    /**
     * 获取邮箱注册验证码有效期（分钟）。
     *
     * @return int
     */
    function developer_starter_get_register_email_code_expire_minutes() {
        $minutes = absint( developer_starter_get_option( 'register_email_code_expire', 10 ) );
        if ( $minutes < 1 ) {
            $minutes = 10;
        } elseif ( $minutes > 60 ) {
            $minutes = 60;
        }

        return $minutes;
    }
}

if ( ! function_exists( 'developer_starter_get_register_email_code_expire_seconds' ) ) {
    /**
     * 获取邮箱注册验证码有效期（秒）。
     *
     * @return int
     */
    function developer_starter_get_register_email_code_expire_seconds() {
        return developer_starter_get_register_email_code_expire_minutes() * MINUTE_IN_SECONDS;
    }
}

if ( ! function_exists( 'developer_starter_get_register_email_code_interval_seconds' ) ) {
    /**
     * 获取邮箱注册验证码发送间隔（秒）。
     *
     * @return int
     */
    function developer_starter_get_register_email_code_interval_seconds() {
        $seconds = absint( developer_starter_get_option( 'register_email_code_interval', 60 ) );
        if ( $seconds < 30 ) {
            $seconds = 60;
        } elseif ( $seconds > 600 ) {
            $seconds = 600;
        }

        return $seconds;
    }
}

if ( ! function_exists( 'developer_starter_get_register_email_code_daily_ip_limit' ) ) {
    /**
     * 获取邮箱注册验证码每日 IP 发送限制。
     *
     * @return int
     */
    function developer_starter_get_register_email_code_daily_ip_limit() {
        $limit = absint( developer_starter_get_option( 'register_email_code_daily_ip_limit', 30 ) );
        if ( $limit < 1 ) {
            $limit = 30;
        } elseif ( $limit > 500 ) {
            $limit = 500;
        }

        return $limit;
    }
}

if ( ! function_exists( 'developer_starter_get_register_email_code_daily_email_limit' ) ) {
    /**
     * 获取邮箱注册验证码单邮箱每日发送限制。
     *
     * @return int
     */
    function developer_starter_get_register_email_code_daily_email_limit() {
        $limit = absint( developer_starter_get_option( 'register_email_code_daily_email_limit', 10 ) );
        if ( $limit < 1 ) {
            $limit = 10;
        } elseif ( $limit > 200 ) {
            $limit = 200;
        }

        return $limit;
    }
}

if ( ! function_exists( 'developer_starter_maybe_repair_serialized_option_array' ) ) {
    /**
     * 尝试修复因 SQL 直接替换域名导致的 option 序列化损坏（字符串长度不匹配）。
     *
     * 仅在 $value 是序列化字符串且反序列化失败时，尝试修复并持久化保存。
     *
     * @param string $option_name 选项名。
     * @param mixed  $value       get_option 返回的值（可能是数组/字符串）。
     * @return mixed 修复成功返回修复后的反序列化结果，否则返回原值。
     */
    function developer_starter_maybe_repair_serialized_option_array( $option_name, $value ) {
        if ( is_array( $value ) ) {
            return $value;
        }

        if ( ! is_string( $value ) || $value === '' ) {
            return $value;
        }

        if ( ! function_exists( 'is_serialized' ) || ! is_serialized( $value ) ) {
            return $value;
        }

        if ( ! function_exists( 'developer_starter_try_unserialize_no_classes' ) || ! function_exists( 'developer_starter_fix_serialized_string_lengths' ) ) {
            return $value;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $value );
        if ( is_array( $unserialized ) ) {
            return $unserialized;
        }

        $fixed = developer_starter_fix_serialized_string_lengths( $value );
        if ( ! is_string( $fixed ) || $fixed === $value ) {
            return $value;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $fixed );
        if ( ! is_array( $unserialized ) ) {
            return $value;
        }

        // 持久化修复结果，避免每次读取都走修复逻辑。
        update_option( (string) $option_name, $unserialized );
        return $unserialized;
    }
}

if ( ! function_exists( 'developer_starter_filter_developer_starter_options' ) ) {
    /**
     * 读取 developer_starter_options 时自动修复序列化损坏。
     *
     * @param mixed  $value  get_option 返回值。
     * @param string $option 选项名。
     * @return array
     */
    function developer_starter_filter_developer_starter_options( $value, $option ) {
        $fixed = developer_starter_maybe_repair_serialized_option_array( 'developer_starter_options', $value );
        return is_array( $fixed ) ? $fixed : array();
    }
}

if ( ! function_exists( 'developer_starter_filter_developer_starter_careers_options' ) ) {
    /**
     * 读取 developer_starter_careers_options 时自动修复序列化损坏。
     *
     * @param mixed  $value  get_option 返回值。
     * @param string $option 选项名。
     * @return array
     */
    function developer_starter_filter_developer_starter_careers_options( $value, $option ) {
        $fixed = developer_starter_maybe_repair_serialized_option_array( 'developer_starter_careers_options', $value );
        return is_array( $fixed ) ? $fixed : array();
    }
}
