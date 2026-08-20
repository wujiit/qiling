<?php
/**
 * Backward-compatible content serialization helper entry.
 *
 * @deprecated 2.5.8 Use helpers-content-modules.php instead.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( '_deprecated_file' ) ) {
    _deprecated_file( __FILE__, '2.5.8', 'inc/core/helpers/helpers-content-modules.php' );
}

require_once __DIR__ . '/helpers-content-modules.php';
