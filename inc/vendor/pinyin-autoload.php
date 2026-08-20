<?php
/**
 * Lightweight autoloader for the bundled overtrue/pinyin library.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PINYIN_DEFAULT' ) ) {
    require_once __DIR__ . '/overtrue/pinyin/src/const.php';
}

spl_autoload_register(
    static function( $class_name ) {
        $prefix = 'Overtrue\\Pinyin\\';

        if ( strpos( $class_name, $prefix ) !== 0 ) {
            return;
        }

        $relative_class = substr( $class_name, strlen( $prefix ) );
        $path           = __DIR__ . '/overtrue/pinyin/src/' . str_replace( '\\', '/', $relative_class ) . '.php';

        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
);
