<?php
/**
 * Helpers grouped split from class-helpers.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_media_url' ) ) {
    function developer_starter_get_media_url( $value ) {
        if ( empty( $value ) ) {
            return '';
        }
        if ( is_array( $value ) ) {
            $url = ! empty( $value['url'] ) ? (string) $value['url'] : '';
            return function_exists( 'developer_starter_normalize_asset_url' )
                ? developer_starter_normalize_asset_url( $url )
                : $url;
        }
        if ( is_numeric( $value ) ) {
            $url = wp_get_attachment_url( (int) $value );
            if ( $url ) {
                return $url;
            }
        }
        $value = (string) $value;

        return function_exists( 'developer_starter_normalize_asset_url' )
            ? developer_starter_normalize_asset_url( $value )
            : $value;
    }
}

if ( ! function_exists( 'developer_starter_get_wc_options' ) ) {
    /**
     * 获取 WooCommerce 主题设置数组（含损坏序列化自修复）。
     *
     * @return array<string,mixed>
     */
    function developer_starter_get_wc_options() {
        static $wc_options = null;

        if ( null !== $wc_options ) {
            return is_array( $wc_options ) ? $wc_options : array();
        }

        $option_name = 'developer_starter_wc_options';
        $resolved = array();
        $raw = function_exists( 'developer_starter_get_raw_option_value' )
            ? developer_starter_get_raw_option_value( $option_name )
            : null;

        if ( is_string( $raw ) && $raw !== '' ) {
            if ( function_exists( 'is_serialized' ) && is_serialized( $raw ) ) {
                $parsed = function_exists( 'developer_starter_try_unserialize_no_classes' )
                    ? developer_starter_try_unserialize_no_classes( $raw )
                    : null;

                if ( ! is_array( $parsed ) && function_exists( 'developer_starter_fix_serialized_string_lengths' ) ) {
                    $fixed = developer_starter_fix_serialized_string_lengths( $raw );
                    if ( is_string( $fixed ) && $fixed !== '' && $fixed !== $raw ) {
                        $parsed = function_exists( 'developer_starter_try_unserialize_no_classes' )
                            ? developer_starter_try_unserialize_no_classes( $fixed )
                            : null;
                        if ( is_array( $parsed ) ) {
                            update_option( $option_name, $parsed );
                        }
                    }
                }

                if ( is_array( $parsed ) ) {
                    $resolved = $parsed;
                }
            } else {
                $json = json_decode( $raw, true );
                if ( is_array( $json ) ) {
                    $resolved = $json;
                }
            }
        }

        $wc_options = is_array( $resolved ) ? $resolved : array();
        return $wc_options;
    }
}

/**
 * 获取 WooCommerce 主题设置选项
 *
 * @param string $key 选项键名。
 * @param mixed  $default 默认值。
 * @return mixed
 */
if ( ! function_exists( 'developer_starter_get_wc_option' ) ) {
    function developer_starter_get_wc_option( $key, $default = '' ) {
        $wc_options = function_exists( 'developer_starter_get_wc_options' )
            ? developer_starter_get_wc_options()
            : array();

        return isset( $wc_options[ $key ] ) ? $wc_options[ $key ] : $default;
    }
}

/**
 * 渲染模块
 *
 * @param string $module_name 模块名称。
 * @param array  $args 模块参数。
 */
if ( ! function_exists( 'developer_starter_render_module' ) ) {
    function developer_starter_render_module( $module_name, $args = array() ) {
        $manager = \Developer_Starter\Modules\Module_Manager::get_instance();
        $manager->render_module( $module_name, $args );
    }
}

/**
 * 获取面包屑导航
 */
if ( ! function_exists( 'developer_starter_breadcrumb' ) ) {
    function developer_starter_breadcrumb() {
        if ( ! apply_filters( 'qiling_show_breadcrumb', true ) ) {
            return;
        }

        if ( function_exists( 'yoast_breadcrumb' ) ) {
            yoast_breadcrumb( '<nav class="breadcrumb">', '</nav>' );
        } elseif ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
            rank_math_the_breadcrumbs();
        } else {
            developer_starter_custom_breadcrumb();
        }
    }
}

/**
 * 自定义面包屑导航
 */
if ( ! function_exists( 'developer_starter_custom_breadcrumb' ) ) {
    function developer_starter_custom_breadcrumb() {
        $sep = '<span class="breadcrumb-sep">/</span>';
        $home_url = function_exists( 'developer_starter_get_frontend_home_url' )
            ? developer_starter_get_frontend_home_url()
            : home_url( '/' );
        $current_title = function_exists( 'developer_starter_get_translated_post_title' )
            ? developer_starter_get_translated_post_title( get_the_ID() )
            : get_the_title( get_the_ID() );

        echo '<nav class="breadcrumb" aria-label="' . esc_attr__( '面包屑导航', 'developer-starter' ) . '">';
        echo '<a href="' . esc_url( $home_url ) . '">' . esc_html__( '首页', 'developer-starter' ) . '</a>';
        
        if ( is_category() || is_single() ) {
            $cats = get_the_category();
            if ( ! empty( $cats ) ) {
                echo $sep . '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
            }
            if ( is_single() ) {
                echo $sep . '<span class="current">' . esc_html( $current_title ) . '</span>';
            }
        } elseif ( is_page() ) {
            echo $sep . '<span class="current">' . esc_html( $current_title ) . '</span>';
        } elseif ( is_search() ) {
            echo $sep . '<span class="current">' . esc_html__( '搜索结果', 'developer-starter' ) . '</span>';
        } elseif ( is_404() ) {
            echo $sep . '<span class="current">' . esc_html__( '404', 'developer-starter' ) . '</span>';
        }
        echo '</nav>';
    }
}

/**
 * 获取网站Logo
 */
if ( ! function_exists( 'developer_starter_get_logo' ) ) {
    function developer_starter_get_logo() {
        if ( has_custom_logo() ) {
            the_custom_logo();
        } else {
            echo '<a href="' . esc_url( developer_starter_get_frontend_home_url() ) . '" class="site-name">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
        }
    }
}

if ( ! function_exists( 'developer_starter_get_aifanyi_translator_instance' ) ) {
    /**
     * 获取启灵翻译插件实例。
     *
     * @return object|null
     */
    function developer_starter_get_aifanyi_translator_instance() {
        global $xb_aifanyi_translator;

        if ( isset( $xb_aifanyi_translator ) && is_object( $xb_aifanyi_translator ) ) {
            return $xb_aifanyi_translator;
        }

        return null;
    }
}

if ( ! function_exists( 'developer_starter_get_translated_post_title' ) ) {
    /**
     * 获取当前前台语言下的文章标题。
     *
     * @param int|null $post_id 文章/页面 ID。
     * @return string
     */
    function developer_starter_get_translated_post_title( $post_id = null ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return '';
        }

        $fallback   = (string) $post->post_title;
        if ( function_exists( 'xb_aifanyi_get_frontend_translated_post_field' ) ) {
            return (string) xb_aifanyi_get_frontend_translated_post_field( $post->ID, 'title', $fallback );
        }

        $translator = developer_starter_get_aifanyi_translator_instance();
        if ( $translator && method_exists( $translator, 'xb_aifanyi_get_frontend_translated_post_field' ) ) {
            return (string) $translator->xb_aifanyi_get_frontend_translated_post_field( $post->ID, 'title', $fallback );
        }

        return $fallback;
    }
}

if ( ! function_exists( 'developer_starter_get_translated_post_excerpt' ) ) {
    /**
     * 获取当前前台语言下的文章摘要。
     *
     * @param int|null $post_id 文章/页面 ID。
     * @return string
     */
    function developer_starter_get_translated_post_excerpt( $post_id = null ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return '';
        }

        $fallback   = (string) get_the_excerpt( $post );
        if ( function_exists( 'xb_aifanyi_get_frontend_translated_post_field' ) ) {
            return (string) xb_aifanyi_get_frontend_translated_post_field( $post->ID, 'excerpt', $fallback );
        }

        $translator = developer_starter_get_aifanyi_translator_instance();
        if ( $translator && method_exists( $translator, 'xb_aifanyi_get_frontend_translated_post_field' ) ) {
            return (string) $translator->xb_aifanyi_get_frontend_translated_post_field( $post->ID, 'excerpt', $fallback );
        }

        return $fallback;
    }
}

if ( ! function_exists( 'developer_starter_get_translated_post_content' ) ) {
    /**
     * 获取当前前台语言下的文章正文原始内容。
     *
     * @param int|null $post_id 文章/页面 ID。
     * @return string
     */
    function developer_starter_get_translated_post_content( $post_id = null ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return '';
        }

        $fallback   = (string) $post->post_content;
        if ( function_exists( 'xb_aifanyi_get_frontend_translated_post_field' ) ) {
            return (string) xb_aifanyi_get_frontend_translated_post_field( $post->ID, 'content', $fallback );
        }

        $translator = developer_starter_get_aifanyi_translator_instance();
        if ( $translator && method_exists( $translator, 'xb_aifanyi_get_frontend_translated_post_field' ) ) {
            return (string) $translator->xb_aifanyi_get_frontend_translated_post_field( $post->ID, 'content', $fallback );
        }

        return $fallback;
    }
}

if ( ! function_exists( 'developer_starter_get_translated_post_meta_value' ) ) {
    /**
     * 获取当前前台语言下的文章元数据。
     *
     * @param int    $post_id  文章/页面 ID。
     * @param string $meta_key 元数据键名。
     * @param mixed  $default  默认值。
     * @return mixed
     */
    function developer_starter_get_translated_post_meta_value( $post_id, $meta_key, $default = '' ) {
        $post_id  = absint( $post_id );
        $meta_key = (string) $meta_key;

        if ( ! $post_id || '' === $meta_key ) {
            return $default;
        }

        $fallback = get_post_meta( $post_id, $meta_key, true );
        if ( '' === $fallback && '' !== $default ) {
            $fallback = $default;
        }

        if ( function_exists( 'xb_aifanyi_get_frontend_translated_post_meta' ) ) {
            return xb_aifanyi_get_frontend_translated_post_meta( $post_id, $meta_key, $fallback );
        }

        $translator = developer_starter_get_aifanyi_translator_instance();
        if ( $translator && method_exists( $translator, 'xb_aifanyi_get_frontend_translated_post_meta' ) ) {
            return $translator->xb_aifanyi_get_frontend_translated_post_meta( $post_id, $meta_key, $fallback );
        }

        return $fallback;
    }
}

if ( ! function_exists( 'developer_starter_fix_serialized_string_lengths' ) ) {
    /**
     * 修复 PHP serialize 结构中字符串长度不匹配的问题。
     *
     * 常见于直接用 SQL REPLACE 批量替换域名后，导致 s:length:"..." 的 length 与实际不一致。
     *
     * @param string $value 原始序列化字符串。
     * @return string 修复后的字符串（修复失败则返回原值）。
     */
    function developer_starter_fix_serialized_string_lengths( $value ) {
        if ( ! is_string( $value ) || $value === '' ) {
            return $value;
        }

        $fixed = preg_replace_callback(
            '/s:(\\d+):\"(.*?)\";/s',
            static function ( $matches ) {
                return 's:' . strlen( $matches[2] ) . ':"' . $matches[2] . '";';
            },
            $value
        );

        return is_string( $fixed ) ? $fixed : $value;
    }
}

if ( ! function_exists( 'developer_starter_try_unserialize_no_classes' ) ) {
    /**
     * 尝试安全反序列化（禁止加载对象类）。
     *
     * @param string $value 序列化字符串。
     * @return mixed|null 反序列化成功返回值，失败返回 null。
     */
    function developer_starter_try_unserialize_no_classes( $value ) {
        if ( ! is_string( $value ) || $value === '' ) {
            return null;
        }

        if ( $value === 'b:0;' ) {
            return false;
        }

        $had_warning = false;
        set_error_handler(
            static function () use ( &$had_warning ) {
                $had_warning = true;
                return true;
            }
        );

        try {
            $result = unserialize( $value, array( 'allowed_classes' => false ) );
        } finally {
            restore_error_handler();
        }

        if ( $had_warning || $result === false ) {
            return null;
        }

        return $result;
    }
}

if ( ! function_exists( 'developer_starter_maybe_repair_serialized_theme_options' ) ) {
    /**
     * 尝试修复主题设置的序列化损坏（developer_starter_options）。
     *
     * @param string      $option_name 选项名。
     * @param string|null $raw         原始序列化字符串（可选）。
     * @return array|null 修复成功返回数组，否则返回 null。
     */
    function developer_starter_maybe_repair_serialized_theme_options( $option_name = 'developer_starter_options', $raw = null ) {
        $option_name = (string) $option_name;
        if ( '' === $option_name ) {
            return null;
        }

        if ( null === $raw ) {
            if ( ! function_exists( 'developer_starter_get_raw_option_value' ) ) {
                return null;
            }
            $raw = developer_starter_get_raw_option_value( $option_name );
        }

        if ( ! is_string( $raw ) || $raw === '' ) {
            return null;
        }

        if ( ! function_exists( 'is_serialized' ) || ! is_serialized( $raw ) ) {
            return null;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $raw );
        if ( is_array( $unserialized ) ) {
            return $unserialized;
        }

        $fixed = developer_starter_fix_serialized_string_lengths( $raw );
        if ( ! is_string( $fixed ) || $fixed === $raw ) {
            return null;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $fixed );
        if ( ! is_array( $unserialized ) ) {
            return null;
        }

        return $unserialized;
    }
}

if ( ! function_exists( 'developer_starter_maybe_auto_repair_serialized_option_from_raw' ) ) {
    /**
     * 仅在 option 原始值确认序列化损坏时，自动修复并持久化一次。
     *
     * 这样可以避免对已经健康的新设置反复写回，确保“自动修复”只处理
     * 因 SQL 批量替换域名导致的坏数据，而不会覆盖后续正常保存的新内容。
     *
     * @param string $option_name         选项名。
     * @param bool   $backup_before_write 是否在写回前保存原始备份。
     * @return bool
     */
    function developer_starter_maybe_auto_repair_serialized_option_from_raw( $option_name, $backup_before_write = false ) {
        static $processed = array();

        $option_name = (string) $option_name;
        if ( '' === $option_name || isset( $processed[ $option_name ] ) ) {
            return false;
        }

        $processed[ $option_name ] = true;

        if ( ! function_exists( 'is_serialized' ) || ! function_exists( 'developer_starter_get_raw_option_value' ) ) {
            return false;
        }

        $raw = developer_starter_get_raw_option_value( $option_name );
        if ( ! is_string( $raw ) || '' === $raw || ! is_serialized( $raw ) ) {
            return false;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $raw );
        if ( is_array( $unserialized ) ) {
            return false;
        }

        $repaired = developer_starter_maybe_repair_serialized_theme_options( $option_name, $raw );
        if ( ! is_array( $repaired ) ) {
            return false;
        }

        if ( $backup_before_write && function_exists( 'developer_starter_add_theme_options_backup' ) ) {
            developer_starter_add_theme_options_backup( $raw, 'auto' );
        }

        update_option( $option_name, $repaired );
        return true;
    }
}

if ( ! function_exists( 'developer_starter_filter_repair_theme_options' ) ) {
    /**
     * 读取主题设置时自动修复序列化损坏，避免 SQL 替换导致设置“消失”。
     *
     * @param mixed $value get_option 返回值。
     * @return mixed
     */
    function developer_starter_filter_repair_theme_options( $value ) {
        static $in_progress = false;

        if ( $in_progress ) {
            return $value;
        }

        if ( is_array( $value ) ) {
            return $value;
        }

        if ( ! is_string( $value ) || $value === '' ) {
            return $value;
        }

        if ( ! function_exists( 'is_serialized' ) || ! is_serialized( $value ) ) {
            return $value;
        }

        $repaired = developer_starter_maybe_repair_serialized_theme_options( 'developer_starter_options', $value );
        if ( ! is_array( $repaired ) ) {
            return $value;
        }

        $in_progress = true;
        remove_filter( 'option_developer_starter_options', 'developer_starter_filter_repair_theme_options', 1 );
        if ( function_exists( 'developer_starter_add_theme_options_backup' ) ) {
            developer_starter_add_theme_options_backup( $value, 'auto' );
        }
        update_option( 'developer_starter_options', $repaired );
        add_filter( 'option_developer_starter_options', 'developer_starter_filter_repair_theme_options', 1 );
        $in_progress = false;

        return $repaired;
    }
}

if ( ! function_exists( 'developer_starter_register_theme_options_auto_repair' ) ) {
    /**
     * 注册主题设置自动修复过滤器。
     *
     * @return void
     */
    function developer_starter_register_theme_options_auto_repair() {
        add_filter( 'option_developer_starter_options', 'developer_starter_filter_repair_theme_options', 1 );
    }
}

if ( ! function_exists( 'developer_starter_get_theme_options_backup_retention_days' ) ) {
    /**
     * 主题设置备份保留天数。
     *
     * @return int
     */
    function developer_starter_get_theme_options_backup_retention_days() {
        return 30;
    }
}

if ( ! function_exists( 'developer_starter_get_theme_options_backup_max_count' ) ) {
    /**
     * 主题设置备份最大保留条数。
     *
     * @return int
     */
    function developer_starter_get_theme_options_backup_max_count() {
        return 20;
    }
}

if ( ! function_exists( 'developer_starter_get_theme_options_backup_index' ) ) {
    /**
     * 获取主题设置备份索引。
     *
     * @return array
     */
    function developer_starter_get_theme_options_backup_index() {
        $index = get_option( 'developer_starter_options_backup_index', array() );
        return is_array( $index ) ? $index : array();
    }
}

if ( ! function_exists( 'developer_starter_update_theme_options_backup_index' ) ) {
    /**
     * 保存主题设置备份索引。
     *
     * @param array $index 索引数据。
     * @return void
     */
    function developer_starter_update_theme_options_backup_index( $index ) {
        if ( ! is_array( $index ) ) {
            $index = array();
        }
        update_option( 'developer_starter_options_backup_index', array_values( $index ), false );
    }
}

if ( ! function_exists( 'developer_starter_cleanup_theme_options_backups' ) ) {
    /**
     * 清理过期或超量的主题设置备份（惰性清理，不依赖 cron）。
     *
     * @param int|null $max_age_days 保留天数。
     * @param int|null $max_count    最大保留条数。
     * @return array 清理后的索引。
     */
    function developer_starter_cleanup_theme_options_backups( $max_age_days = null, $max_count = null ) {
        $max_age_days = is_null( $max_age_days ) ? developer_starter_get_theme_options_backup_retention_days() : absint( $max_age_days );
        $max_count    = is_null( $max_count ) ? developer_starter_get_theme_options_backup_max_count() : absint( $max_count );
        $now          = time();
        $max_age      = $max_age_days > 0 ? $max_age_days * DAY_IN_SECONDS : 0;

        $index = developer_starter_get_theme_options_backup_index();
        $kept  = array();

        foreach ( $index as $entry ) {
            if ( ! is_array( $entry ) ) {
                continue;
            }
            $key = isset( $entry['key'] ) ? (string) $entry['key'] : '';
            if ( '' === $key ) {
                continue;
            }

            $created_at = isset( $entry['created_at'] ) ? absint( $entry['created_at'] ) : 0;
            if ( $max_age > 0 && $created_at > 0 && ( $now - $created_at ) > $max_age ) {
                delete_option( $key );
                continue;
            }

            $kept[] = $entry;
        }

        usort(
            $kept,
            static function ( $a, $b ) {
                $a_time = isset( $a['created_at'] ) ? (int) $a['created_at'] : 0;
                $b_time = isset( $b['created_at'] ) ? (int) $b['created_at'] : 0;
                return $b_time <=> $a_time;
            }
        );

        if ( $max_count > 0 && count( $kept ) > $max_count ) {
            $extra = array_slice( $kept, $max_count );
            foreach ( $extra as $entry ) {
                if ( ! is_array( $entry ) ) {
                    continue;
                }
                $key = isset( $entry['key'] ) ? (string) $entry['key'] : '';
                if ( '' !== $key ) {
                    delete_option( $key );
                }
            }
            $kept = array_slice( $kept, 0, $max_count );
        }

        developer_starter_update_theme_options_backup_index( $kept );
        return $kept;
    }
}

if ( ! function_exists( 'developer_starter_add_theme_options_backup' ) ) {
    /**
     * 保存主题设置修复前的原始备份。
     *
     * @param string $raw     原始序列化字符串。
     * @param string $context 触发场景。
     * @return string|null 备份 option 名称。
     */
    function developer_starter_add_theme_options_backup( $raw, $context = '' ) {
        if ( ! is_string( $raw ) || $raw === '' ) {
            return null;
        }

        $index = developer_starter_cleanup_theme_options_backups();
        $hash  = md5( $raw );

        foreach ( $index as $entry ) {
            if ( ! is_array( $entry ) ) {
                continue;
            }
            if ( isset( $entry['hash'] ) && $entry['hash'] === $hash ) {
                return isset( $entry['key'] ) ? (string) $entry['key'] : null;
            }
        }

        if ( function_exists( 'wp_generate_password' ) ) {

            $suffix = wp_generate_password( 4, false, false );
        } else {
            $suffix = substr( md5( uniqid( '', true ) ), 0, 4 );
        }

        $option_key = 'developer_starter_options_backup_' . gmdate( 'Ymd_His' ) . '_' . $suffix;
        update_option( $option_key, $raw, false );

        $index[] = array(
            'key'        => $option_key,
            'created_at' => time(),
            'context'    => (string) $context,
            'hash'       => $hash,
            'size'       => strlen( $raw ),
        );

        developer_starter_update_theme_options_backup_index( $index );
        return $option_key;
    }
}



if ( ! function_exists( 'developer_starter_get_raw_option_value' ) ) {
    /**
     * 直接从数据库读取 option 原始值（不经过 option 过滤器）。
     *
     * @param string $option_name 选项名。
     * @return string|null
     */
    function developer_starter_get_raw_option_value( $option_name ) {
        global $wpdb;

        $option_name = (string) $option_name;
        if ( '' === $option_name ) {
            return null;
        }

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                $option_name
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_is_option_serialization_broken' ) ) {
    /**
     * 检测指定 option 是否存在序列化损坏。
     *
     * @param string $option_name 选项名。
     * @return bool
     */
    function developer_starter_is_option_serialization_broken( $option_name ) {
        if ( ! function_exists( 'is_serialized' ) ) {
            return false;
        }

        $raw = developer_starter_get_raw_option_value( $option_name );
        if ( ! is_string( $raw ) || $raw === '' ) {
            return false;
        }

        if ( ! is_serialized( $raw ) ) {
            return false;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $raw );
        if ( is_array( $unserialized ) ) {
            return false;
        }

        $fixed = developer_starter_fix_serialized_string_lengths( $raw );
        if ( ! is_string( $fixed ) || $fixed === $raw ) {
            return true;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $fixed );
        return ! is_array( $unserialized );
    }
}

if ( ! function_exists( 'developer_starter_maybe_repair_serialized_modules_meta' ) ) {
    /**
     * 尝试修复页面模块元数据（_developer_starter_modules）的序列化损坏。
     *
     * @param int    $post_id 页面 ID。
     * @param string $raw     get_post_meta 返回的原始值（序列化字符串）。
     * @return array|null 修复成功返回数组，否则返回 null。
     */
    function developer_starter_maybe_repair_serialized_modules_meta( $post_id, $raw ) {
        $post_id = absint( $post_id );
        if ( ! $post_id || ! is_string( $raw ) || $raw === '' ) {
            return null;
        }

        if ( ! function_exists( 'is_serialized' ) || ! is_serialized( $raw ) ) {
            return null;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $raw );
        if ( is_array( $unserialized ) ) {
            return $unserialized;
        }

        $fixed = developer_starter_fix_serialized_string_lengths( $raw );
        if ( ! is_string( $fixed ) || $fixed === $raw ) {
            return null;
        }

        $unserialized = developer_starter_try_unserialize_no_classes( $fixed );
        if ( ! is_array( $unserialized ) ) {
            return null;
        }

        update_post_meta( $post_id, '_developer_starter_modules', $unserialized );
        return $unserialized;
    }
}

if ( ! function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
    /**
     * 标准化页面模板 slug。
     *
     * @param mixed $value 模板原始值。
     * @return string
     */
    function developer_starter_normalize_page_template_slug( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $template = trim( sanitize_text_field( (string) $value ) );
        if ( '' === $template ) {
            return '';
        }

        if ( 'default' === $template ) {
            return 'default';
        }

        $template = str_replace( '\\', '/', ltrim( $template, '/' ) );
        $template = strtok( $template, '?#' );
        if ( ! is_string( $template ) || '' === $template ) {
            return '';
        }

        if ( strpos( $template, 'templates/' ) === 0 ) {
            $basename = basename( $template );
            if ( strpos( $basename, 'template-' ) === 0 && substr( $basename, -4 ) !== '.php' ) {
                return 'templates/' . $basename . '.php';
            }
            return $template;
        }

        $basename = basename( $template );
        if ( strpos( $basename, 'template-' ) === 0 ) {
            if ( substr( $basename, -4 ) !== '.php' ) {
                $basename .= '.php';
            }
            return 'templates/' . $basename;
        }

        return $template;
    }
}

if ( ! function_exists( 'developer_starter_postmeta_supports_4byte_chars' ) ) {
    /**
     * 检查 postmeta.meta_value 列是否支持 utf8mb4（4字节字符）。
     *
     * @return bool
     */
    function developer_starter_postmeta_supports_4byte_chars() {
        static $supports = null;
        if ( null !== $supports ) {
            return $supports;
        }

        $supports = false;

        global $wpdb;
        if ( isset( $wpdb->postmeta ) && method_exists( $wpdb, 'get_col_charset' ) ) {
            $charset = $wpdb->get_col_charset( $wpdb->postmeta, 'meta_value' );
            if ( is_string( $charset ) && stripos( $charset, 'utf8mb4' ) !== false ) {
                $supports = true;
            }
        }

        if ( ! $supports && defined( 'DB_CHARSET' ) ) {
            $db_charset = strtolower( trim( (string) DB_CHARSET ) );
            if ( $db_charset !== '' && strpos( $db_charset, 'utf8mb4' ) !== false ) {
                $supports = true;
            }
        }

        return $supports;
    }
}

if ( ! function_exists( 'developer_starter_strip_non_bmp_chars_deep' ) ) {
    /**
     * 递归移除 4 字节字符（emoji 等）及相关连接符，兼容 utf8(3-byte) 数据库写入。
     *
     * @param mixed $value 任意值。
     * @return mixed
     */
    function developer_starter_strip_non_bmp_chars_deep( $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                $value[ $key ] = developer_starter_strip_non_bmp_chars_deep( $item );
            }
            return $value;
        }

        if ( ! is_string( $value ) || $value === '' ) {
            return $value;
        }

        $cleaned = preg_replace( '/[\x{200D}\x{FE0F}\x{10000}-\x{10FFFF}]/u', '', $value );
        return is_string( $cleaned ) ? $cleaned : $value;
    }
}

if ( ! function_exists( 'developer_starter_get_legacy_module_type_aliases' ) ) {
    /**
     * 历史模块 type 到当前有效模块 ID 的映射。
     *
     * @return array<string,string>
     */
    function developer_starter_get_legacy_module_type_aliases() {
        return (array) apply_filters(
            'developer_starter_legacy_module_type_aliases',
            array(
                'image' => 'image_text',
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
    /**
     * 仅归一化顶层模块 type，避免历史模块 ID 导致前台不渲染。
     *
     * 注意：这里不会处理模块 data 内部的 `type` 字段（例如媒体项 image/video）。
     *
     * @param mixed $modules 模块数组。
     * @return mixed
     */
    function developer_starter_normalize_modules_meta_types( $modules ) {
        if ( ! is_array( $modules ) || empty( $modules ) ) {
            return $modules;
        }

        $aliases = developer_starter_get_legacy_module_type_aliases();
        if ( empty( $aliases ) || ! is_array( $aliases ) ) {
            return $modules;
        }

        foreach ( $modules as $index => $module ) {
            if ( ! is_array( $module ) || ! isset( $module['type'] ) || ! is_string( $module['type'] ) ) {
                continue;
            }

            $module_type = trim( $module['type'] );
            if ( $module_type === '' || ! isset( $aliases[ $module_type ] ) ) {
                continue;
            }

            $mapped_type = is_string( $aliases[ $module_type ] ) ? trim( $aliases[ $module_type ] ) : '';
            if ( $mapped_type === '' ) {
                continue;
            }

            $module['type'] = $mapped_type;
            $modules[ $index ] = $module;
        }

        return $modules;
    }
}

if ( ! function_exists( 'developer_starter_normalize_banner_stats_items' ) ) {
    /**
     * 统一首屏 Banner 数据展示条的历史字段结构。
     *
     * @param mixed $items 数据展示条项目。
     * @return array<int,array<string,string>>
     */
    function developer_starter_normalize_banner_stats_items( $items ) {
        if ( ! is_array( $items ) || empty( $items ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $icon = '';
            foreach ( array( 'icon', 'stat_icon' ) as $key ) {
                if ( array_key_exists( $key, $item ) && is_scalar( $item[ $key ] ) && '' !== (string) $item[ $key ] ) {
                    $icon = (string) $item[ $key ];
                    break;
                }
            }

            $number = '';
            foreach ( array( 'number', 'stat_number', 'stat_value', 'value' ) as $key ) {
                if ( array_key_exists( $key, $item ) && is_scalar( $item[ $key ] ) && '' !== (string) $item[ $key ] ) {
                    $number = (string) $item[ $key ];
                    break;
                }
            }

            $label = '';
            foreach ( array( 'label', 'stat_label', 'text' ) as $key ) {
                if ( array_key_exists( $key, $item ) && is_scalar( $item[ $key ] ) && '' !== (string) $item[ $key ] ) {
                    $label = (string) $item[ $key ];
                    break;
                }
            }

            $color = '';
            foreach ( array( 'color', 'stat_color' ) as $key ) {
                if ( array_key_exists( $key, $item ) && is_scalar( $item[ $key ] ) && '' !== (string) $item[ $key ] ) {
                    $color = (string) $item[ $key ];
                    break;
                }
            }

            if ( '' === trim( $icon ) && '' === trim( $number ) && '' === trim( $label ) ) {
                continue;
            }

            $normalized[] = array(
                'icon'   => $icon,
                'number' => $number,
                'label'  => $label,
                'color'  => $color,
            );
        }

        return $normalized;
    }
}

if ( ! function_exists( 'developer_starter_normalize_legacy_module_data_fields' ) ) {
    /**
     * 兼容历史模块 data 字段键名，确保旧数据可在当前编辑器中直接编辑。
     *
     * 仅在对应新键不存在或为空时补齐，避免覆盖用户已保存的新结构。
     *
     * @param mixed $modules 模块数组。
     * @return mixed
     */
    function developer_starter_normalize_legacy_module_data_fields( $modules ) {
        if ( ! is_array( $modules ) || empty( $modules ) ) {
            return $modules;
        }

        foreach ( $modules as $index => $module ) {
            if ( ! is_array( $module ) || ! isset( $module['type'] ) || ! is_string( $module['type'] ) ) {
                continue;
            }

            $type = trim( $module['type'] );
            $data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array();

            if ( '' === $type || empty( $data ) ) {
                continue;
            }

            $changed = false;

            switch ( $type ) {
                case 'banner':
                    if ( ( empty( $data['banner_slides'] ) || ! is_array( $data['banner_slides'] ) ) ) {
                        $legacy_title = isset( $data['title'] ) ? (string) $data['title'] : '';
                        $legacy_subtitle = isset( $data['subtitle'] ) ? (string) $data['subtitle'] : '';
                        $legacy_desc = isset( $data['description'] ) ? (string) $data['description'] : '';
                        $legacy_btn_text = isset( $data['btn_text'] ) ? (string) $data['btn_text'] : '';
                        $legacy_btn_url = isset( $data['btn_url'] ) ? (string) $data['btn_url'] : '';

                        if ( $legacy_title !== '' || $legacy_subtitle !== '' || $legacy_desc !== '' || $legacy_btn_text !== '' || $legacy_btn_url !== '' ) {
                            $slide_subtitle = $legacy_subtitle;
                            if ( '' === $slide_subtitle ) {
                                $slide_subtitle = $legacy_desc;
                            }

                            $data['banner_slides'] = array(
                                array(
                                    'title'    => $legacy_title,
                                    'subtitle' => $slide_subtitle,
                                    'btn_text' => $legacy_btn_text,
                                    'btn_url'  => $legacy_btn_url,
                                ),
                            );
                            $changed = true;
                        }
                    }

                    if ( ( ! isset( $data['banner_bg_color'] ) || $data['banner_bg_color'] === '' ) && isset( $data['bg_color'] ) && $data['bg_color'] !== '' ) {
                        $data['banner_bg_color'] = $data['bg_color'];
                        $changed = true;
                    }

                    if ( isset( $data['show_stats_bar'] ) ) {
                        $show_stats_bar = is_scalar( $data['show_stats_bar'] )
                            ? strtolower( trim( (string) $data['show_stats_bar'] ) )
                            : '0';
                        $normalized_show_stats_bar = in_array( $show_stats_bar, array( '1', 'yes', 'true', 'on' ), true ) ? '1' : '0';
                        if ( $data['show_stats_bar'] !== $normalized_show_stats_bar ) {
                            $data['show_stats_bar'] = $normalized_show_stats_bar;
                            $changed = true;
                        }
                    }

                    $has_normalized_stats_data = false;
                    if ( isset( $data['stats_data'] ) && is_array( $data['stats_data'] ) && ! empty( $data['stats_data'] ) ) {
                        $normalized_stats_data = developer_starter_normalize_banner_stats_items( $data['stats_data'] );
                        if ( ! empty( $normalized_stats_data ) ) {
                            $has_normalized_stats_data = true;
                        }
                        if ( ! empty( $normalized_stats_data ) && $normalized_stats_data !== $data['stats_data'] ) {
                            $data['stats_data'] = $normalized_stats_data;
                            $changed = true;
                        }
                    }

                    if ( ! $has_normalized_stats_data ) {
                        foreach ( array( 'stats_items', 'items' ) as $legacy_stats_key ) {
                            if ( empty( $data[ $legacy_stats_key ] ) || ! is_array( $data[ $legacy_stats_key ] ) ) {
                                continue;
                            }

                            $legacy_stats_data = developer_starter_normalize_banner_stats_items( $data[ $legacy_stats_key ] );
                            if ( ! empty( $legacy_stats_data ) ) {
                                $data['stats_data'] = $legacy_stats_data;
                                $changed = true;
                                break;
                            }
                        }
                    }
                    break;

                case 'services':
                    if ( ( ! isset( $data['services_title'] ) || $data['services_title'] === '' ) && isset( $data['title'] ) && $data['title'] !== '' ) {
                        $data['services_title'] = $data['title'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['services_subtitle'] ) || $data['services_subtitle'] === '' ) && isset( $data['subtitle'] ) && $data['subtitle'] !== '' ) {
                        $data['services_subtitle'] = $data['subtitle'];
                        $changed = true;
                    }

                    if ( ( empty( $data['services_items'] ) || ! is_array( $data['services_items'] ) ) && isset( $data['items'] ) && is_array( $data['items'] ) ) {
                        $data['services_items'] = $data['items'];
                        $changed = true;
                    }
                    break;

                case 'features':
                    if ( ( ! isset( $data['features_title'] ) || $data['features_title'] === '' ) && isset( $data['title'] ) && $data['title'] !== '' ) {
                        $data['features_title'] = $data['title'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['features_subtitle'] ) || $data['features_subtitle'] === '' ) && isset( $data['subtitle'] ) && $data['subtitle'] !== '' ) {
                        $data['features_subtitle'] = $data['subtitle'];
                        $changed = true;
                    }

                    if ( ( empty( $data['features_items'] ) || ! is_array( $data['features_items'] ) ) && isset( $data['items'] ) && is_array( $data['items'] ) ) {
                        $data['features_items'] = $data['items'];
                        $changed = true;
                    }
                    break;

                case 'stats':
                    if ( ( empty( $data['stats_items'] ) || ! is_array( $data['stats_items'] ) ) && isset( $data['items'] ) && is_array( $data['items'] ) ) {
                        $data['stats_items'] = $data['items'];
                        $changed = true;
                    }
                    break;

                case 'cta':
                    if ( ( ! isset( $data['cta_title'] ) || $data['cta_title'] === '' ) && isset( $data['title'] ) && $data['title'] !== '' ) {
                        $data['cta_title'] = $data['title'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['cta_subtitle'] ) || $data['cta_subtitle'] === '' ) && isset( $data['subtitle'] ) && $data['subtitle'] !== '' ) {
                        $data['cta_subtitle'] = $data['subtitle'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['cta_button_text'] ) || $data['cta_button_text'] === '' ) && isset( $data['btn_text'] ) && $data['btn_text'] !== '' ) {
                        $data['cta_button_text'] = $data['btn_text'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['cta_button_url'] ) || $data['cta_button_url'] === '' ) && isset( $data['btn_url'] ) && $data['btn_url'] !== '' ) {
                        $data['cta_button_url'] = $data['btn_url'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['cta_bg_color'] ) || $data['cta_bg_color'] === '' ) && isset( $data['bg_color'] ) && $data['bg_color'] !== '' ) {
                        $data['cta_bg_color'] = $data['bg_color'];
                        $changed = true;
                    }

                    if ( isset( $data['cta_bg_color'] ) && $data['cta_bg_color'] !== '' && ( ! isset( $data['cta_bg_type'] ) || $data['cta_bg_type'] === '' ) ) {
                        $data['cta_bg_type'] = 'color';
                        $changed = true;
                    }
                    break;

                case 'news':
                    if ( ( ! isset( $data['news_title'] ) || $data['news_title'] === '' ) && isset( $data['title'] ) && $data['title'] !== '' ) {
                        $data['news_title'] = $data['title'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['news_count'] ) || $data['news_count'] === '' ) && isset( $data['count'] ) && $data['count'] !== '' ) {
                        $data['news_count'] = (string) $data['count'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['news_columns'] ) || $data['news_columns'] === '' ) && isset( $data['columns'] ) && $data['columns'] !== '' ) {
                        $data['news_columns'] = (string) $data['columns'];
                        $changed = true;
                    }
                    break;

                case 'contact':
                    if ( ( ! isset( $data['contact_title'] ) || $data['contact_title'] === '' ) && isset( $data['title'] ) && $data['title'] !== '' ) {
                        $data['contact_title'] = $data['title'];
                        $changed = true;
                    }

                    if ( ( ! isset( $data['contact_subtitle'] ) || $data['contact_subtitle'] === '' ) && isset( $data['subtitle'] ) && $data['subtitle'] !== '' ) {
                        $data['contact_subtitle'] = $data['subtitle'];
                        $changed = true;
                    }

                    if ( ! isset( $data['contact_show_form'] ) && isset( $data['show_form'] ) ) {
                        $show_form = $data['show_form'];
                        $show_form_normalized = strtolower( trim( (string) $show_form ) );
                        $data['contact_show_form'] = in_array( $show_form_normalized, array( '1', 'true', 'yes', 'on' ), true ) ? '1' : '0';
                        $changed = true;
                    }
                    break;
            }

            if ( $changed ) {
                $module['data'] = $data;
                $modules[ $index ] = $module;
            }
        }

        return $modules;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_modules_meta_for_storage' ) ) {
    /**
     * 保存页面模块元数据时，按数据库字符集自动做兼容清洗。
     *
     * 仅在非 utf8mb4 站点处理，避免因 emoji 导致整包序列化写入失败。
     *
     * @param mixed  $meta_value     元数据值。
     * @param string $meta_key       元数据键。
     * @param string $object_type    对象类型。
     * @param string|null $object_subtype 对象子类型（旧版 WP 可能不传）。
     * @return mixed
     */
    function developer_starter_sanitize_modules_meta_for_storage( $meta_value, $meta_key, $object_type, $object_subtype = null ) {
        unset( $meta_key, $object_type, $object_subtype );

        if ( ! is_array( $meta_value ) ) {
            return $meta_value;
        }

        $meta_value = developer_starter_normalize_modules_meta_types( $meta_value );
        $meta_value = developer_starter_normalize_legacy_module_data_fields( $meta_value );

        if ( developer_starter_postmeta_supports_4byte_chars() ) {
            return $meta_value;
        }

        return developer_starter_strip_non_bmp_chars_deep( $meta_value );
    }
}
add_filter( 'sanitize_post_meta__developer_starter_modules', 'developer_starter_sanitize_modules_meta_for_storage', 20, 4 );

if ( ! function_exists( 'developer_starter_get_page_template_default_modules_map' ) ) {
    /**
     * 获取需要自动填充默认模块的页面模板映射。
     *
     * @return array<string,array<string,string>>
     */
    function developer_starter_get_page_template_default_modules_map() {
        return array(
            'templates/template-home.php'               => array(
                'flag'   => '_homepage_modules_filled',
                'class'  => '\Developer_Starter\Core\Homepage_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-blog.php'               => array(
                'flag'   => '_blog_page_modules_filled',
                'class'  => '\Developer_Starter\Core\Blog_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-topic.php'              => array(
                'flag'   => '_topic_page_modules_filled',
                'class'  => '\Developer_Starter\Core\Topic_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-solutions.php'          => array(
                'flag'   => '_solutions_modules_filled',
                'class'  => '\Developer_Starter\Core\Solutions_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-landing.php'            => array(
                'flag'   => '_landing_modules_filled',
                'class'  => '\Developer_Starter\Core\Landing_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-features-showcase.php'  => array(
                'flag'   => '_features_showcase_modules_filled',
                'class'  => '\Developer_Starter\Core\Features_Showcase_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-resources.php'          => array(
                'flag'   => '_resources_modules_filled',
                'class'  => '\Developer_Starter\Core\Resources_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-resource-search.php'    => array(
                'flag'   => '_resource_search_modules_filled',
                'class'  => '\Developer_Starter\Core\Resource_Search_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-software-home.php'      => array(
                'flag'   => '_software_home_modules_filled',
                'class'  => '\Developer_Starter\Core\Software_Home_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-saas-home.php'          => array(
                'flag'   => '_saas_home_modules_filled',
            ),
            'templates/template-hosting-saas-home.php'  => array(
                'flag'   => '_hosting_saas_home_modules_filled',
            ),
            'templates/template-software-intro.php'     => array(
                'flag'   => '_software_intro_modules_filled',
                'class'  => '\Developer_Starter\Core\Software_Intro_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-video-hero.php'         => array(
                'flag'   => '_video_hero_modules_filled',
                'class'  => '\Developer_Starter\Core\Video_Hero_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-video-portal.php'       => array(
                'flag'   => '_video_portal_modules_filled',
            ),
            'templates/template-video-ranking.php'      => array(
                'flag'   => '_video_ranking_modules_filled',
            ),
            'templates/template-saas-pricing.php'       => array(
                'flag'   => '_saas_pricing_modules_filled',
                'class'  => '\Developer_Starter\Core\Saas_Pricing_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-interactive-product-launch.php' => array(
                'flag'   => '_interactive_product_launch_modules_filled',
                'class'  => '\Developer_Starter\Core\Interactive_Product_Launch_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-app-download-landing.php' => array(
                'flag'   => '_app_download_landing_modules_filled',
                'class'  => '\Developer_Starter\Core\App_Download_Landing_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-ai-product-brand.php' => array(
                'flag'   => '_ai_product_brand_modules_filled',
                'class'  => '\Developer_Starter\Core\AI_Product_Brand_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-developer-platform.php' => array(
                'flag'   => '_developer_platform_modules_filled',
                'class'  => '\Developer_Starter\Core\Developer_Platform_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-cybersecurity-brand.php' => array(
                'flag'   => '_cybersecurity_brand_modules_filled',
                'class'  => '\Developer_Starter\Core\Cybersecurity_Brand_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-data-intelligence-bi.php' => array(
                'flag'   => '_data_intelligence_bi_modules_filled',
                'class'  => '\Developer_Starter\Core\Data_Intelligence_BI_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-qiling-ai-writing-studio.php' => array(
                'flag' => '_qiling_ai_writing_studio_modules_filled',
            ),
            'templates/template-qiling-ai-multilingual-seo.php' => array(
                'flag' => '_qiling_ai_multilingual_seo_modules_filled',
            ),
            'templates/template-qiling-doc-ocr-converter.php' => array(
                'flag' => '_qiling_doc_ocr_converter_modules_filled',
            ),
            'templates/template-qiling-image-studio.php' => array(
                'flag' => '_qiling_image_studio_modules_filled',
            ),
            'templates/template-qiling-cloud-storage-hosting.php' => array(
                'flag' => '_qiling_cloud_storage_hosting_modules_filled',
            ),
            'templates/template-qiling-cloud-canvas.php' => array(
                'flag' => '_qiling_cloud_canvas_modules_filled',
            ),
            'templates/template-tech-company-integrated.php' => array(
                'flag' => '_tech_company_integrated_modules_filled',
            ),
            'templates/template-qiling-security-ops.php' => array(
                'flag' => '_qiling_security_ops_modules_filled',
            ),
            'templates/template-qiling-escrow-platform.php' => array(
                'flag' => '_qiling_escrow_platform_modules_filled',
            ),
            'templates/template-qiling-freetask-platform.php' => array(
                'flag' => '_qiling_freetask_platform_modules_filled',
            ),
            'templates/template-qiling-friends-matchmaking.php' => array(
                'flag' => '_qiling_friends_matchmaking_modules_filled',
            ),
            'templates/template-qiling-bbs-support-community.php' => array(
                'flag' => '_qiling_bbs_support_community_modules_filled',
            ),
            'templates/template-open-source-devtools.php' => array(
                'flag'   => '_open_source_devtools_modules_filled',
                'class'  => '\Developer_Starter\Core\Open_Source_DevTools_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-marketing-pr-agency.php' => array(
                'flag'   => '_marketing_pr_agency_modules_filled',
                'class'  => '\Developer_Starter\Core\Marketing_PR_Agency_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-manufacturing-factory.php' => array(
                'flag' => '_manufacturing_factory_modules_filled',
            ),
            'templates/template-foreign-trade-b2b.php' => array(
                'flag' => '_foreign_trade_b2b_modules_filled',
            ),
            'templates/template-finance-consulting.php' => array(
                'flag' => '_finance_consulting_modules_filled',
            ),
            'templates/template-accounting-tax-service.php' => array(
                'flag' => '_accounting_tax_service_modules_filled',
            ),
            'templates/template-intellectual-property-service.php' => array(
                'flag' => '_intellectual_property_service_modules_filled',
            ),
            'templates/template-study-abroad-immigration.php' => array(
                'flag' => '_study_abroad_immigration_modules_filled',
            ),
            'templates/template-early-childhood-education.php' => array(
                'flag' => '_early_childhood_education_modules_filled',
            ),
            'templates/template-vocational-training-school.php' => array(
                'flag' => '_vocational_training_school_modules_filled',
            ),
            'templates/template-psychological-counseling.php' => array(
                'flag' => '_psychological_counseling_modules_filled',
            ),
            'templates/template-senior-care-center.php' => array(
                'flag' => '_senior_care_center_modules_filled',
            ),
            'templates/template-postpartum-care-center.php' => array(
                'flag' => '_postpartum_care_center_modules_filled',
            ),
            'templates/template-architecture-design-studio.php' => array(
                'flag' => '_architecture_design_studio_modules_filled',
            ),
            'templates/template-interior-soft-decoration.php' => array(
                'flag' => '_interior_soft_decoration_modules_filled',
            ),
            'templates/template-landscape-garden-design.php' => array(
                'flag' => '_landscape_garden_design_modules_filled',
            ),
            'templates/template-appliance-repair-service.php' => array(
                'flag' => '_appliance_repair_service_modules_filled',
            ),
            'templates/template-franchise-investment.php' => array(
                'flag' => '_franchise_investment_modules_filled',
            ),
            'templates/template-mcn-live-commerce.php' => array(
                'flag' => '_mcn_live_commerce_modules_filled',
            ),
            'templates/template-conference-event-service.php' => array(
                'flag' => '_conference_event_service_modules_filled',
            ),
            'templates/template-real-estate-service.php' => array(
                'flag' => '_real_estate_service_modules_filled',
            ),
            'templates/template-local-service-official.php' => array(
                'flag' => '_local_service_official_modules_filled',
            ),
            'templates/template-qiling-recycling-official.php' => array(
                'flag' => '_qiling_recycling_official_modules_filled',
            ),
            'templates/template-qiling-housekeeping-official.php' => array(
                'flag' => '_qiling_housekeeping_official_modules_filled',
            ),
            'templates/template-healthcare-clinic.php' => array(
                'flag' => '_healthcare_clinic_modules_filled',
            ),
            'templates/template-logistics-supply-chain.php' => array(
                'flag' => '_logistics_supply_chain_modules_filled',
            ),
            'templates/template-recruitment-hr-service.php' => array(
                'flag' => '_recruitment_hr_service_modules_filled',
            ),
            'templates/template-nonprofit-organization.php' => array(
                'flag' => '_nonprofit_organization_modules_filled',
            ),
            'templates/template-government-public-service.php' => array(
                'flag' => '_government_public_service_modules_filled',
            ),
            'templates/template-agriculture-food.php' => array(
                'flag' => '_agriculture_food_modules_filled',
            ),
            'templates/template-energy-environment.php' => array(
                'flag' => '_energy_environment_modules_filled',
            ),
            'templates/template-event-exhibition.php' => array(
                'flag' => '_event_exhibition_modules_filled',
            ),
            'templates/template-industrial-park.php' => array(
                'flag' => '_industrial_park_modules_filled',
            ),
            'templates/template-property-management.php' => array(
                'flag' => '_property_management_modules_filled',
            ),
            'templates/template-semiconductor-electronics.php' => array(
                'flag' => '_semiconductor_electronics_modules_filled',
            ),
            'templates/template-industrial-automation-robotics.php' => array(
                'flag' => '_industrial_automation_robotics_modules_filled',
            ),
            'templates/template-medical-device.php' => array(
                'flag' => '_medical_device_modules_filled',
            ),
            'templates/template-lab-instrument.php' => array(
                'flag' => '_lab_instrument_modules_filled',
            ),
            'templates/template-solar-storage-equipment.php' => array(
                'flag' => '_solar_storage_equipment_modules_filled',
            ),
            'templates/template-water-treatment-environmental.php' => array(
                'flag' => '_water_treatment_environmental_modules_filled',
            ),
            'templates/template-cross-border-ecommerce-service.php' => array(
                'flag' => '_cross_border_ecommerce_service_modules_filled',
            ),
            'templates/template-overseas-warehouse-supply-chain.php' => array(
                'flag' => '_overseas_warehouse_supply_chain_modules_filled',
            ),
            'templates/template-enterprise-software-integrator.php' => array(
                'flag' => '_enterprise_software_integrator_modules_filled',
            ),
            'templates/template-ai-agent-enterprise.php' => array(
                'flag' => '_ai_agent_enterprise_modules_filled',
            ),
            'templates/template-ev-charging-station.php' => array(
                'flag' => '_ev_charging_station_modules_filled',
            ),
            'templates/template-personal-ip-home.php' => array(
                'flag'   => '_personal_ip_home_modules_filled',
                'class'  => '\Developer_Starter\Core\Personal_IP_Home_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-chain-store-official.php' => array(
                'flag'   => '_chain_store_official_modules_filled',
                'class'  => '\Developer_Starter\Core\Chain_Store_Official_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-course-enrollment.php' => array(
                'flag'   => '_course_enrollment_modules_filled',
                'class'  => '\Developer_Starter\Core\Course_Enrollment_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-ecommerce-promo.php' => array(
                'flag'   => '_ecommerce_promo_modules_filled',
                'class'  => '\Developer_Starter\Core\Ecommerce_Promo_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-dental-clinic.php' => array(
                'flag'   => '_dental_clinic_modules_filled',
                'class'  => '\Developer_Starter\Core\Dental_Clinic_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-renovation-construction.php' => array(
                'flag'   => '_renovation_construction_modules_filled',
                'class'  => '\Developer_Starter\Core\Renovation_Construction_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-wedding-photography.php' => array(
                'flag'   => '_wedding_photography_modules_filled',
                'class'  => '\Developer_Starter\Core\Wedding_Photography_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-law-firm.php' => array(
                'flag'   => '_law_firm_modules_filled',
                'class'  => '\Developer_Starter\Core\Law_Firm_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-gym-fitness.php' => array(
                'flag'   => '_gym_fitness_modules_filled',
                'class'  => '\Developer_Starter\Core\Gym_Fitness_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-auto-service.php' => array(
                'flag'   => '_auto_service_modules_filled',
                'class'  => '\Developer_Starter\Core\Auto_Service_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-wellness-center.php' => array(
                'flag'   => '_wellness_center_modules_filled',
                'class'  => '\Developer_Starter\Core\Wellness_Center_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-yoga-studio.php' => array(
                'flag'   => '_yoga_studio_modules_filled',
                'class'  => '\Developer_Starter\Core\Yoga_Studio_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-beauty-salon.php' => array(
                'flag'   => '_beauty_salon_modules_filled',
                'class'  => '\Developer_Starter\Core\Beauty_Salon_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-resume.php'             => array(
                'flag'   => '_resume_page_modules_filled',
                'class'  => '\Developer_Starter\Core\Resume_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-news.php'               => array(
                'flag'   => '_qiling_news_center_modules_filled',
                'class'  => '\Developer_Starter\Core\News_Center_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-products.php'           => array(
                'flag'   => '_qiling_products_center_modules_filled',
                'class'  => '\Developer_Starter\Core\Products_Center_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-cases.php'              => array(
                'flag'   => '_qiling_cases_center_modules_filled',
                'class'  => '\Developer_Starter\Core\Cases_Center_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-data-showcase.php'      => array(
                'flag'   => '_data_showcase_modules_filled',
                'class'  => '\Developer_Starter\Core\Data_Showcase_Page_Creator',
                'method' => 'set_default_modules',
            ),
            'templates/template-restaurant.php'         => array(
                'flag'   => '_industry_restaurant_modules_filled',
                'class'  => '\Developer_Starter\Core\Industry_Preset_Page_Creator',
                'preset' => 'restaurant',
            ),
            'templates/template-pet.php'                => array(
                'flag'   => '_industry_pet_modules_filled',
                'class'  => '\Developer_Starter\Core\Industry_Preset_Page_Creator',
                'preset' => 'pet',
            ),
            'templates/template-travel.php'             => array(
                'flag'   => '_industry_travel_modules_filled',
                'class'  => '\Developer_Starter\Core\Industry_Preset_Page_Creator',
                'preset' => 'travel',
            ),
            'templates/template-homestay.php'           => array(
                'flag'   => '_industry_homestay_modules_filled',
                'class'  => '\Developer_Starter\Core\Industry_Preset_Page_Creator',
                'preset' => 'homestay',
            ),
            'templates/template-medical-beauty.php'     => array(
                'flag'   => '_industry_medical_beauty_modules_filled',
                'class'  => '\Developer_Starter\Core\Industry_Preset_Page_Creator',
                'preset' => 'medical_beauty',
            ),
        );
    }
}

if ( ! function_exists( 'developer_starter_maybe_fill_default_modules_for_page_template' ) ) {
    /**
     * 根据页面模板自动填充默认模块。
     *
     * @param int         $post_id  页面 ID。
     * @param string|null $template 页面模板。
     * @return bool
     */
    function developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template = null ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
            return false;
        }

        if ( null === $template || '' === $template ) {
            $template = get_post_meta( $post_id, '_wp_page_template', true );
        }

        if ( ! is_string( $template ) || '' === $template ) {
            return false;
        }

        $template = developer_starter_normalize_page_template_slug( $template );
        if ( '' === $template || 'default' === $template ) {
            return false;
        }

        $map = developer_starter_get_page_template_default_modules_map();
        if ( ! isset( $map[ $template ] ) || ! is_array( $map[ $template ] ) ) {
            return false;
        }

        $config = $map[ $template ];
        $flag   = isset( $config['flag'] ) && is_string( $config['flag'] ) ? $config['flag'] : '';
        $class  = isset( $config['class'] ) && is_string( $config['class'] ) ? ltrim( $config['class'], '\\' ) : '';
        $method = isset( $config['method'] ) && is_string( $config['method'] ) ? $config['method'] : '';
        $preset = isset( $config['preset'] ) && is_string( $config['preset'] ) ? $config['preset'] : '';

        $stored_template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        if ( $template !== 'default' && $template !== $stored_template ) {
            update_post_meta( $post_id, '_wp_page_template', $template );
        }

		$modules = developer_starter_get_raw_page_modules_meta( $post_id );
		if ( ! empty( $modules ) ) {
			return false;
		}

		$official_json_filled = developer_starter_maybe_fill_official_template_package_for_page_template( $post_id, $template );
		if ( $official_json_filled ) {
			if ( '' !== $flag && ! get_post_meta( $post_id, $flag, true ) ) {
				update_post_meta( $post_id, $flag, '1' );
			}

			return true;
		}

		if ( 'data_showcase' !== $preset ) {
			if ( '' === $class ) {
				return false;
			}

            if ( ! class_exists( $class ) ) {
                if ( function_exists( 'developer_starter_maybe_load_page_creator_class' ) ) {
                    developer_starter_maybe_load_page_creator_class( $class );
                }
            }

            if ( ! class_exists( $class ) ) {
                return false;
            }
        }

        $invoked_method = $method;
        try {
            if ( 'data_showcase' === $preset ) {
                if ( ! class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
                    return false;
                }

                $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
                if ( ! $module_manager ) {
                    return false;
                }

                $stats_module = array(
                    'type' => 'stats',
                    'data' => $module_manager->get_module( 'stats' ) ? $module_manager->get_module( 'stats' )->get_demo_data() : array(),
                );
                $chart_bar = array(
                    'type' => 'chart',
                    'data' => $module_manager->get_module( 'chart' ) ? $module_manager->get_module( 'chart' )->get_demo_data( 'bar' ) : array(),
                );
                $chart_pie = array(
                    'type' => 'chart',
                    'data' => $module_manager->get_module( 'chart' ) ? $module_manager->get_module( 'chart' )->get_demo_data( 'pie' ) : array(),
                );
                $comparison = array(
                    'type' => 'comparison',
                    'data' => $module_manager->get_module( 'comparison' ) ? $module_manager->get_module( 'comparison' )->get_demo_data() : array(),
                );

                update_post_meta( $post_id, '_developer_starter_modules', array( $stats_module, $chart_bar, $chart_pie, $comparison ) );
                $invoked_method = 'data_showcase_preset';
            } elseif ( '' !== $preset ) {
                $reflection = new ReflectionClass( $class );
                if ( ! $reflection->hasMethod( 'get_preset_modules' ) ) {
                    return false;
                }

                $instance      = $reflection->newInstance();
                $preset_method = $reflection->getMethod( 'get_preset_modules' );
                if ( method_exists( $preset_method, 'setAccessible' ) ) {
                    $preset_method->setAccessible( true );
                }

                $modules = $preset_method->isStatic()
                    ? $preset_method->invokeArgs( null, array( $preset ) )
                    : $preset_method->invokeArgs( $instance, array( $preset ) );
                if ( ! is_array( $modules ) || empty( $modules ) ) {
                    return false;
                }

                update_post_meta( $post_id, '_developer_starter_modules', $modules );
                $invoked_method = 'get_preset_modules';
            } else {
                $reflection = new ReflectionClass( $class );
                if ( '' === $method || ! $reflection->hasMethod( $method ) ) {
                    return false;
                }

                $instance   = $reflection->newInstance();
                $method_ref = $reflection->getMethod( $method );
                if ( method_exists( $method_ref, 'setAccessible' ) ) {
                    $method_ref->setAccessible( true );
                }

                $args = array( $post_id );
                if ( $method_ref->getNumberOfParameters() >= 2 ) {
                    $args[] = $template;
                }

                if ( $method_ref->isStatic() ) {
                    $method_ref->invokeArgs( null, $args );
                } else {
                    $method_ref->invokeArgs( $instance, $args );
                }
                $invoked_method = $method;
            }
        } catch ( \Throwable $e ) {
            developer_starter_log(
                'content_modules',
                'Fill default modules failed.',
                array(
                    'post_id' => (int) $post_id,
                    'class'   => $class,
                    'method'  => $invoked_method,
                    'error'   => $e,
                ),
                'error'
            );
            return false;
        }

        $modules = developer_starter_get_raw_page_modules_meta( $post_id );
        if ( empty( $modules ) ) {
            return false;
        }

        if ( '' !== $flag && ! get_post_meta( $post_id, $flag, true ) ) {
            update_post_meta( $post_id, $flag, '1' );
        }

        return true;
	}
}

if ( ! function_exists( 'developer_starter_maybe_fill_official_template_package_for_page_template' ) ) {
	/**
	 * 使用官方 JSON 页面包填充指定页面模板。
	 *
	 * 当前只覆盖已注册官方 JSON 页面包的模板；其它模板会返回 false 并继续走旧逻辑。
	 *
	 * @param int         $post_id  页面 ID。
	 * @param string|null $template 页面模板。
	 * @return bool
	 */
	function developer_starter_maybe_fill_official_template_package_for_page_template( $post_id, $template = null ) {
		if ( null === $template || '' === $template ) {
			$template = get_post_meta( $post_id, '_wp_page_template', true );
		}

		if ( ! class_exists( '\Developer_Starter\Core\Official_Template_Package_Service' ) ) {
			return false;
		}

		$service = new \Developer_Starter\Core\Official_Template_Package_Service();
		if ( ! $service->has_package_for_template( $template ) ) {
			return false;
		}

		$result = $service->apply_package_to_page( $post_id, $template );
		if ( is_wp_error( $result ) ) {
			developer_starter_log(
				'content_modules',
				'Official template JSON fill failed.',
				array(
					'post_id' => (int) $post_id,
					'error'   => $result,
				),
				'error'
			);
			return false;
		}

		return (bool) $result;
	}
}

if ( ! function_exists( 'developer_starter_maybe_fill_official_template_package_after_save' ) ) {
	/**
	 * 页面保存时优先尝试官方 JSON 页面包，早于旧页面创建器执行。
	 *
	 * @param int     $post_id 页面 ID。
	 * @param WP_Post $post    页面对象。
	 * @return void
	 */
	function developer_starter_maybe_fill_official_template_package_after_save( $post_id, $post ) {
		if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		developer_starter_maybe_fill_official_template_package_for_page_template( $post_id );
	}
}
add_action( 'save_post', 'developer_starter_maybe_fill_official_template_package_after_save', 90, 2 );

if ( ! function_exists( 'developer_starter_maybe_fill_default_modules_after_rest_insert_page' ) ) {
	/**
	 * REST 保存页面后按模板补齐默认模块（兜底）。
     *
     * @param WP_Post|mixed         $post     保存后的页面对象。
     * @param WP_REST_Request|mixed $request  REST 请求对象。
     * @param bool                  $creating 是否创建请求。
     * @return void
     */
    function developer_starter_maybe_fill_default_modules_after_rest_insert_page( $post, $request, $creating ) {
        unset( $creating );

        if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
            return;
        }

        $template = '';

        if ( class_exists( 'WP_REST_Request' ) && $request instanceof WP_REST_Request ) {
            $template = (string) $request->get_param( 'template' );

            if ( '' === trim( $template ) ) {
                $meta_payload = $request->get_param( 'meta' );
                if ( is_array( $meta_payload ) && isset( $meta_payload['_wp_page_template'] ) ) {
                    $template = (string) $meta_payload['_wp_page_template'];
                }
            }
        }

        if ( '' === trim( $template ) ) {
            if ( isset( $_POST['meta_input'] ) && is_array( $_POST['meta_input'] ) && isset( $_POST['meta_input']['_wp_page_template'] ) ) {
                $raw_meta_template = wp_unslash( $_POST['meta_input']['_wp_page_template'] );
                if ( is_scalar( $raw_meta_template ) && '' !== trim( (string) $raw_meta_template ) ) {
                    $template = (string) $raw_meta_template;
                }
            }

            foreach ( array( 'template', 'page_template', '_wp_page_template' ) as $request_key ) {
                if ( '' !== trim( $template ) ) {
                    break;
                }

                if ( ! isset( $_REQUEST[ $request_key ] ) ) {
                    continue;
                }

                $raw_template = wp_unslash( $_REQUEST[ $request_key ] );
                if ( is_scalar( $raw_template ) && '' !== trim( (string) $raw_template ) ) {
                    $template = (string) $raw_template;
                    break;
                }
            }
        }

        if ( '' === trim( $template ) && function_exists( 'get_page_template_slug' ) ) {
            $slug = get_page_template_slug( $post->ID );
            if ( is_string( $slug ) ) {
                $template = $slug;
            }
        }

        if ( '' === trim( $template ) ) {
            $template = (string) get_post_meta( $post->ID, '_wp_page_template', true );
        }

        developer_starter_maybe_fill_default_modules_for_page_template( $post->ID, $template );
    }
}
if ( ! function_exists( 'developer_starter_maybe_fill_default_modules_after_wp_insert_post' ) ) {
    /**
     * 常规保存页面后按模板补齐默认模块（兜底）。
     *
     * @param int               $post_id     页面 ID。
     * @param WP_Post|mixed     $post        保存后的页面对象。
     * @param bool              $update      是否更新请求。
     * @param WP_Post|null|mixed $post_before 保存前页面对象。
     * @return void
     */
    function developer_starter_maybe_fill_default_modules_after_wp_insert_post( $post_id, $post, $update, $post_before ) {
        unset( $update, $post_before );

        if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $template = '';
        if ( function_exists( 'get_page_template_slug' ) ) {
            $slug = get_page_template_slug( $post_id );
            if ( is_string( $slug ) ) {
                $template = $slug;
            }
        }

        if ( '' === trim( $template ) ) {
            if ( isset( $_POST['meta_input'] ) && is_array( $_POST['meta_input'] ) && isset( $_POST['meta_input']['_wp_page_template'] ) ) {
                $raw_meta_template = wp_unslash( $_POST['meta_input']['_wp_page_template'] );
                if ( is_scalar( $raw_meta_template ) && '' !== trim( (string) $raw_meta_template ) ) {
                    $template = (string) $raw_meta_template;
                }
            }

            foreach ( array( 'page_template', '_wp_page_template', 'template' ) as $request_key ) {
                if ( '' !== trim( $template ) ) {
                    break;
                }

                if ( ! isset( $_REQUEST[ $request_key ] ) ) {
                    continue;
                }

                $raw_template = wp_unslash( $_REQUEST[ $request_key ] );
                if ( is_scalar( $raw_template ) && '' !== trim( (string) $raw_template ) ) {
                    $template = (string) $raw_template;
                    break;
                }
            }
        }

        if ( '' === trim( $template ) ) {
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template );
    }
}
if ( ! function_exists( 'developer_starter_maybe_fill_default_modules_after_page_template_meta_change' ) ) {
    /**
     * 页面模板元数据写入后，兜底补齐默认模块。
     *
     * 说明：部分编辑链路会在 save_post 之后才写入 `_wp_page_template`。
     * 这里监听该元数据变更，确保模板一旦落库即可触发预设模块注入。
     *
     * @param int    $meta_id    元数据 ID。
     * @param int    $post_id    页面 ID。
     * @param string $meta_key   元数据键名。
     * @param mixed  $meta_value 元数据值。
     * @return void
     */
    function developer_starter_maybe_fill_default_modules_after_page_template_meta_change( $meta_id, $post_id, $meta_key, $meta_value ) {
        unset( $meta_id );

        if ( '_wp_page_template' !== (string) $meta_key ) {
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $template = is_scalar( $meta_value ) ? (string) $meta_value : '';
        $guard_key = (int) $post_id . '|' . $template;
        static $running = array();

        if ( isset( $running[ $guard_key ] ) ) {
            return;
        }

        $running[ $guard_key ] = true;

        try {
            developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template );
        } finally {
            unset( $running[ $guard_key ] );
        }
    }
}
add_action( 'wp_after_insert_post', 'developer_starter_maybe_fill_default_modules_after_wp_insert_post', 20, 4 );
add_action( 'rest_after_insert_page', 'developer_starter_maybe_fill_default_modules_after_rest_insert_page', 20, 3 );
add_action( 'added_post_meta', 'developer_starter_maybe_fill_default_modules_after_page_template_meta_change', 20, 4 );
add_action( 'updated_post_meta', 'developer_starter_maybe_fill_default_modules_after_page_template_meta_change', 20, 4 );

if ( ! function_exists( 'developer_starter_get_raw_page_modules_meta' ) ) {
    /**
     * 获取页面模块元数据（不做翻译）。
     *
     * 注意：这里不再自动修复损坏的序列化数据，避免在正常请求链路中隐式写库。
     * 如需修复，请使用后台“模块数据修复”手动扫描处理。
     *
     * @param int|null $post_id 页面 ID。
     * @return array
     */
    function developer_starter_get_raw_page_modules_meta( $post_id = null ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return array();
        }

        $modules = get_post_meta( $post->ID, '_developer_starter_modules', true );

        if ( ! is_array( $modules ) ) {
            return array();
        }

        $modules = developer_starter_normalize_modules_meta_types( $modules );
        return developer_starter_normalize_legacy_module_data_fields( $modules );
    }
}

if ( ! function_exists( 'developer_starter_get_page_modules_data' ) ) {
    /**
     * 获取当前前台语言下的页面模块数据。
     *
     * @param int|null $post_id 页面 ID。
     * @return mixed
     */
    function developer_starter_get_page_modules_data( $post_id = null ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return array();
        }

        $modules = developer_starter_get_raw_page_modules_meta( $post->ID );
        if ( empty( $modules ) ) {
            $template = function_exists( 'get_page_template_slug' ) ? get_page_template_slug( $post->ID ) : '';
            if ( ! is_string( $template ) || '' === trim( $template ) ) {
                $template = get_post_meta( $post->ID, '_wp_page_template', true );
            }
            if ( is_string( $template ) && '' !== $template && 'default' !== $template ) {
                developer_starter_maybe_fill_default_modules_for_page_template( $post->ID, $template );
                $modules = developer_starter_get_raw_page_modules_meta( $post->ID );
            }
        }
        if ( function_exists( 'xb_aifanyi_get_frontend_translated_modules' ) ) {
            $translated_modules = xb_aifanyi_get_frontend_translated_modules( $post->ID, $modules );
            if ( is_array( $translated_modules ) && ! empty( $translated_modules ) ) {
                $translated_modules = developer_starter_normalize_modules_meta_types( $translated_modules );
                return developer_starter_normalize_legacy_module_data_fields( $translated_modules );
            }
        }

        $translator = developer_starter_get_aifanyi_translator_instance();
        if ( $translator && method_exists( $translator, 'xb_aifanyi_get_frontend_translated_modules' ) ) {
            $translated_modules = $translator->xb_aifanyi_get_frontend_translated_modules( $post->ID, $modules );
            if ( is_array( $translated_modules ) && ! empty( $translated_modules ) ) {
                $translated_modules = developer_starter_normalize_modules_meta_types( $translated_modules );
                return developer_starter_normalize_legacy_module_data_fields( $translated_modules );
            }
        }

        $modules = developer_starter_normalize_modules_meta_types( $modules );
        return developer_starter_normalize_legacy_module_data_fields( $modules );
    }
}

/**
 * 渲染页面模块
 */
if ( ! function_exists( 'developer_starter_render_page_modules' ) ) {
    function developer_starter_render_page_modules( $post_id = null, $args = array() ) {
        $manager = \Developer_Starter\Modules\Module_Manager::get_instance();
        $manager->render_page_modules( $post_id, $args );
    }
}

if ( ! function_exists( 'developer_starter_resolve_builder_template_page' ) ) {
    /**
     * 校验并解析指定的装修页面。
     *
     * @param int|null $page_id 装修页面 ID。
     * @param array    $args    可选参数。
     * @return array
     */
    function developer_starter_resolve_builder_template_page( $page_id = null, $args = array() ) {
        $args = wp_parse_args(
            is_array( $args ) ? $args : array(),
            array(
                'exclude_current' => true,
            )
        );

        $page_id = absint( $page_id );
        if ( $page_id <= 0 || 'page' !== get_post_type( $page_id ) ) {
            return array();
        }

        if ( ! empty( $args['exclude_current'] ) && function_exists( 'get_queried_object_id' ) && absint( get_queried_object_id() ) === $page_id ) {
            return array();
        }

        $page = get_post( $page_id );
        if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
            return array();
        }

        $modules = developer_starter_get_page_modules_data( $page_id );
        if ( empty( $modules ) || ! is_array( $modules ) ) {
            return array();
        }

        return array(
            'page_id' => $page_id,
            'page'    => $page,
            'modules' => $modules,
        );
    }
}

if ( ! function_exists( 'developer_starter_render_builder_template_page' ) ) {
    /**
     * 渲染指定的装修页面模块。
     *
     * @param int|null $page_id 装修页面 ID。
     * @param array    $args    渲染参数。
     * @return bool
     */
    function developer_starter_render_builder_template_page( $page_id = null, $args = array() ) {
        $args = wp_parse_args(
            is_array( $args ) ? $args : array(),
            array(
                'exclude_current' => true,
                'builder_mode'    => false,
                'context'         => 'context_builder',
                'wrapper_class'   => '',
            )
        );

        $resolved = developer_starter_resolve_builder_template_page(
            $page_id,
            array(
                'exclude_current' => ! empty( $args['exclude_current'] ),
            )
        );

        if ( empty( $resolved ) ) {
            return false;
        }

        $context       = sanitize_key( (string) $args['context'] );
        $wrapper_class = trim( (string) $args['wrapper_class'] );

        if ( '' !== $wrapper_class ) {
            printf(
                '<div class="%1$s" data-builder-template="%2$s">',
                esc_attr( $wrapper_class ),
                esc_attr( (string) $resolved['page_id'] )
            );
        }

        do_action( 'developer_starter_before_context_builder', $resolved['page_id'], $args, $resolved );

        developer_starter_render_page_modules(
            $resolved['page_id'],
            array(
                'builder_mode' => ! empty( $args['builder_mode'] ),
                'context'      => '' !== $context ? $context : 'context_builder',
            )
        );

        do_action( 'developer_starter_after_context_builder', $resolved['page_id'], $args, $resolved );

        if ( '' !== $wrapper_class ) {
            echo '</div>';
        }

        return true;
    }
}
