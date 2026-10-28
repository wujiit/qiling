<?php
/**
 * 启灵主题现代化 API 与向后兼容别名层 (QiLing Compatibility & API Layer)
 *
 * 为高频核心函数提供标准 qiling_* API 别名，同时确保与原有 developer_starter_* 函数双向无损兼容。
 *
 * @package Qiling
 * @since 2.6.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取启灵主题选项值 (标准 API)
 *
 * @param string $key 选项键名。
 * @param mixed  $default 默认值。
 * @return mixed
 */
if ( ! function_exists( 'qiling_get_option' ) ) {
    function qiling_get_option( $key, $default = '' ) {
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return developer_starter_get_option( $key, $default );
        }
        return $default;
    }
}

/**
 * 获取启灵主题选项全量内存缓存
 *
 * @param bool $force_refresh 是否强制重新读取。
 * @return array<string,mixed>
 */
if ( ! function_exists( 'qiling_get_options_cache' ) ) {
    function qiling_get_options_cache( $force_refresh = false ) {
        if ( function_exists( 'developer_starter_get_options_cache' ) ) {
            return developer_starter_get_options_cache( $force_refresh );
        }
        return array();
    }
}

/**
 * 多语言多语种本地化文本获取
 *
 * @param mixed  $text 原始文本或语言映射数组。
 * @param string $locale 目标语种。
 * @return string
 */
if ( ! function_exists( 'qiling_get_locale_text' ) ) {
    function qiling_get_locale_text( $text, $locale = '' ) {
        if ( function_exists( 'developer_starter_get_locale_text' ) ) {
            return developer_starter_get_locale_text( $text, $locale );
        }
        return is_string( $text ) ? $text : '';
    }
}

/**
 * 获取客户端真实 IP 地址
 *
 * @return string
 */
if ( ! function_exists( 'qiling_get_client_ip' ) ) {
    function qiling_get_client_ip() {
        if ( function_exists( 'developer_starter_get_client_ip' ) ) {
            return developer_starter_get_client_ip();
        }
        return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    }
}

/**
 * 获取图标 HTML 矢量输出
 *
 * @param string $icon_key 图标标识。
 * @param string $class_name 附加类名。
 * @param string $style 内联样式。
 * @return string
 */
if ( ! function_exists( 'qiling_get_icon_html' ) ) {
    function qiling_get_icon_html( $icon_key, $class_name = '', $style = '' ) {
        if ( function_exists( 'developer_starter_get_icon_html' ) ) {
            return developer_starter_get_icon_html( $icon_key, $class_name, $style );
        }
        return '';
    }
}

/**
 * 获取启灵静态资产版本号
 *
 * @return string
 */
if ( ! function_exists( 'qiling_get_assets_version' ) ) {
    function qiling_get_assets_version() {
        if ( function_exists( 'developer_starter_get_assets_version' ) ) {
            return developer_starter_get_assets_version();
        }
        return defined( 'QILING_VERSION' ) ? QILING_VERSION : '2.6.9';
    }
}

/**
 * 获取页面模块化排版数据
 *
 * @param int $post_id 页面 ID。
 * @return array<int,mixed>
 */
if ( ! function_exists( 'qiling_get_page_modules_data' ) ) {
    function qiling_get_page_modules_data( $post_id = 0 ) {
        if ( function_exists( 'developer_starter_get_page_modules_data' ) ) {
            return developer_starter_get_page_modules_data( $post_id );
        }
        return array();
    }
}

/**
 * 渲染页面模块组
 *
 * @param array<int,mixed> $modules 模块配置数组。
 * @return void
 */
if ( ! function_exists( 'qiling_render_page_modules' ) ) {
    function qiling_render_page_modules( $modules = array() ) {
        if ( function_exists( 'developer_starter_render_page_modules' ) ) {
            developer_starter_render_page_modules( $modules );
        }
    }
}

/**
 * 获取文章特色图片 URL
 *
 * @param int|null $post_id 文章 ID。
 * @param string   $size 图片尺寸。
 * @return string
 */
if ( ! function_exists( 'qiling_get_featured_image_url' ) ) {
    function qiling_get_featured_image_url( $post_id = null, $size = 'full' ) {
        if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
            return developer_starter_get_featured_image_url( $post_id, $size );
        }
        return '';
    }
}

/**
 * 获取文章缩略图 URL
 *
 * @param int|null $post_id 文章 ID。
 * @param string   $size 图片尺寸。
 * @return string
 */
if ( ! function_exists( 'qiling_get_thumbnail_url' ) ) {
    function qiling_get_thumbnail_url( $post_id = null, $size = 'thumbnail' ) {
        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
            return developer_starter_get_thumbnail_url( $post_id, $size );
        }
        return '';
    }
}

/**
 * 运行带缓存的数据库查询
 *
 * @param string   $cache_key 缓存键名。
 * @param callable $callback 提取回调。
 * @param int      $expire 过期时间（秒）。
 * @param string   $group 缓存分组。
 * @return mixed
 */
if ( ! function_exists( 'qiling_run_cached_query' ) ) {
    function qiling_run_cached_query( $cache_key, $callback, $expire = 3600, $group = 'qiling_queries' ) {
        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            return developer_starter_run_cached_query( $cache_key, $callback, $expire, $group );
        }
        return is_callable( $callback ) ? call_user_func( $callback ) : null;
    }
}
