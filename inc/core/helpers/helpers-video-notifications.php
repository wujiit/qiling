<?php
/**
 * 向后兼容的视频通知辅助函数入口。
 *
 * @deprecated 2.5.8 Use helpers-media-security-video.php instead.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( '_deprecated_file' ) ) {
    _deprecated_file( __FILE__, '2.5.8', 'inc/core/helpers/helpers-media-security-video.php' );
}

require_once __DIR__ . '/helpers-media-security-video.php';
