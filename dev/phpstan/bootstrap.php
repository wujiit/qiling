<?php
/**
 * Minimal bootstrap for theme-level PHPStan runs.
 */

declare(strict_types=1);

$themeRoot = dirname(__DIR__, 2);

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', $themeRoot . '/' );
}

$constants = array(
    'DEVELOPER_STARTER_VERSION'    => '2.5.7',
    'DEVELOPER_STARTER_DIR'        => $themeRoot,
    'DEVELOPER_STARTER_URI'        => 'https://example.test/wp-content/themes/qiling',
    'DEVELOPER_STARTER_INC'        => $themeRoot . '/inc',
    'DEVELOPER_STARTER_ASSETS'     => 'https://example.test/wp-content/themes/qiling/assets',
    'DEVELOPER_STARTER_DB_VERSION' => '2.5.7',
);

foreach ( $constants as $name => $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

$pinyinAutoload = DEVELOPER_STARTER_INC . '/vendor/pinyin-autoload.php';
if ( file_exists( $pinyinAutoload ) ) {
    require_once $pinyinAutoload;
}
