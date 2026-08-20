<?php
/**
 * Dark mode runtime helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_normalize_dark_mode_time' ) ) {
    /**
     * Normalize a HH:MM time value for the front-end dark mode scheduler.
     *
     * @param mixed  $value Time value.
     * @param string $default Default HH:MM value.
     * @return string
     */
    function developer_starter_normalize_dark_mode_time( $value, $default = '00:00' ) {
        $value = trim( sanitize_text_field( (string) $value ) );

        if ( preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $value, $matches ) ) {
            return sprintf( '%02d:%02d', (int) $matches[1], (int) $matches[2] );
        }

        return preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $default ) ? $default : '00:00';
    }
}

if ( ! function_exists( 'developer_starter_get_dark_mode_runtime_config' ) ) {
    /**
     * Build the shared front-end dark mode configuration.
     *
     * @return array<string,mixed>
     */
    function developer_starter_get_dark_mode_runtime_config() {
        $darkmode_enabled = (bool) developer_starter_get_option( 'darkmode_enable', '' );
        $auto_enabled     = $darkmode_enabled && (bool) developer_starter_get_option( 'darkmode_auto_enable', '' );

        $mode = sanitize_key( (string) developer_starter_get_option( 'darkmode_auto_mode', 'system_schedule' ) );
        if ( ! in_array( $mode, array( 'system_schedule', 'system', 'schedule' ), true ) ) {
            $mode = 'system_schedule';
        }

        $transition_enabled = $auto_enabled && (bool) developer_starter_get_option( 'darkmode_transition_enable', '1' );
        $image_dim_enabled  = $auto_enabled && (bool) developer_starter_get_option( 'darkmode_image_dim_enable', '1' );

        return array(
            'enabled'          => $darkmode_enabled,
            'autoEnabled'      => $auto_enabled,
            'mode'             => $mode,
            'sunriseTime'      => developer_starter_normalize_dark_mode_time( developer_starter_get_option( 'darkmode_sunrise_time', '06:00' ), '06:00' ),
            'sunsetTime'       => developer_starter_normalize_dark_mode_time( developer_starter_get_option( 'darkmode_sunset_time', '18:00' ), '18:00' ),
            'transition'       => $transition_enabled,
            'imageDim'         => $image_dim_enabled,
            'storageKey'       => 'qiling-theme-preference',
            'legacyStorageKey' => 'theme',
        );
    }
}
