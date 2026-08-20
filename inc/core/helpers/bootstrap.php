<?php
/**
 * Helpers bootstrap loader.
 *
 * Maintains legacy load order from class-helpers.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/helpers-multibyte-polyfill.php';
require_once __DIR__ . '/helpers-logging.php';
require_once __DIR__ . '/helpers-filesystem.php';
require_once __DIR__ . '/helpers-auth-options.php';
require_once __DIR__ . '/helpers-client-ip.php';
require_once __DIR__ . '/helpers-url-i18n-routing.php';
require_once __DIR__ . '/helpers-locale-notify.php';
require_once __DIR__ . '/helpers-qilingshop-resource.php';
require_once __DIR__ . '/helpers-content-modules.php';
require_once __DIR__ . '/helpers-page-skins.php';
require_once __DIR__ . '/helpers-footer-visual.php';
require_once __DIR__ . '/helpers-page-region-decoration.php';
require_once __DIR__ . '/helpers-changelog.php';
require_once __DIR__ . '/helpers-dark-mode.php';
require_once __DIR__ . '/helpers-media-security-video.php';
require_once __DIR__ . '/helpers-qiapp-theme.php';
require_once __DIR__ . '/helpers-post-cover-badges.php';
