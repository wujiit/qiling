<?php
/**
 * 页面创建器引导加载辅助函数。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 页面创建器类名与文件路径映射表。
 *
 * @return array<string,string>
 */
function developer_starter_get_page_creator_class_file_map() {
    static $map = null;

    if ( null !== $map ) {
        return $map;
    }

    $registry_file = DEVELOPER_STARTER_INC . '/core/page-creator-registry.php';
    $registry = file_exists( $registry_file ) ? require $registry_file : array();
    if ( ! is_array( $registry ) ) {
        $registry = array();
    }

    $map = array();
    foreach ( $registry as $class => $file ) {
        $class = ltrim( (string) $class, '\\' );
        $file  = is_string( $file ) ? $file : '';

        if ( '' === $class || '' === $file ) {
            continue;
        }

        $map[ $class ] = $file;
    }

    return $map;
}

/**
 * 加载页面创建器基类。
 *
 * @return void
 */
function developer_starter_include_page_creator_base_file() {
    static $included = false;
    if ( $included ) {
        return;
    }

    require_once DEVELOPER_STARTER_INC . '/core/class-page-creator-base.php';
    $included = true;
}

/**
 * 当请求需要时批量加载所有页面创建器类文件。
 *
 * @return void
 */
function developer_starter_include_page_creators_files() {
    static $included = false;
    if ( $included ) {
        return;
    }

    developer_starter_include_page_creator_base_file();

    foreach ( developer_starter_get_page_creator_class_file_map() as $file ) {
        if ( is_string( $file ) && '' !== $file ) {
            require_once $file;
        }
    }

    $included = true;
}

/**
 * 根据类名加载单个页面创建器类文件。
 *
 * @param string $class Fully-qualified class name, with or without a leading slash.
 * @return bool
 */
function developer_starter_maybe_load_page_creator_class( $class ) {
    $class = ltrim( (string) $class, '\\' );
    if ( '' === $class ) {
        return false;
    }

    if ( class_exists( $class, false ) ) {
        return true;
    }

    $map = developer_starter_get_page_creator_class_file_map();
    if ( ! isset( $map[ $class ] ) ) {
        return false;
    }

    $file = $map[ $class ];
    if ( ! is_string( $file ) || '' === $file || ! file_exists( $file ) ) {
        return false;
    }

    developer_starter_include_page_creator_base_file();

    require_once $file;
    return class_exists( $class, false );
}

/**
 * 检测当前请求是否需要实例化页面创建器。
 *
 * @return bool
 */
function developer_starter_should_init_page_creators() {
    if ( is_admin() ) {
        return true;
    }

    if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
        return true;
    }

    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return true;
    }

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        return true;
    }

    return (bool) apply_filters( 'developer_starter_should_init_page_creators', false );
}

/**
 * 仅在按需时初始化页面创建器对象。
 *
 * @return void
 */
function developer_starter_init_page_creators() {
    static $initialized = false;
    if ( $initialized ) {
        return;
    }
    $initialized = true;

    developer_starter_include_page_creators_files();

    foreach ( array_keys( developer_starter_get_page_creator_class_file_map() ) as $class ) {
        if ( class_exists( $class, false ) ) {
            new $class();
        }
    }
}
