<?php
/**
 * 用于主题级 PHPStan 静态检查分析的最小化引导文件。
 */

declare(strict_types=1);

$themeRoot = dirname(__DIR__, 2);

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', $themeRoot . '/' );
}

$constants = array(
    'QILING_VERSION'    => '2.6.8',
    'QILING_DIR'        => $themeRoot,
    'QILING_URI'        => 'https://example.test/wp-content/themes/qiling',
    'QILING_INC'        => $themeRoot . '/inc',
    'QILING_ASSETS'     => 'https://example.test/wp-content/themes/qiling/assets',
    'QILING_DB_VERSION' => '2.6.8',
);

foreach ( $constants as $name => $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

$pinyinAutoload = QILING_INC . '/vendor/pinyin-autoload.php';
if ( file_exists( $pinyinAutoload ) ) {
    require_once $pinyinAutoload;
}
