<?php
/**
 * Page creator bootstrap helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Page creator class to file map.
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
 * Load the page creator base class.
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
 * Load all page creator files when a request needs them.
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
 * Load one page creator file by class name.
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
 * Whether the current request needs page creator objects.
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
 * Initialize page creator objects only when needed.
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
