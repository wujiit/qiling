<?php
/**
 * 向后兼容的域名 URL 迁移辅助函数入口。
 *
 * @deprecated 2.5.8 Use helpers-url-i18n-routing.php instead.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( '_deprecated_file' ) ) {
    _deprecated_file( __FILE__, '2.5.8', 'inc/core/helpers/helpers-url-i18n-routing.php' );
}

require_once __DIR__ . '/helpers-url-i18n-routing.php';
