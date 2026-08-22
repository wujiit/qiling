<?php
/**
 * WordPress admin color scheme compatibility.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin_Color_Scheme_Manager {

    /**
     * Register hooks once.
     *
     * @return void
     */
    public static function bootstrap() {
        static $bootstrapped = false;

        if ( $bootstrapped ) {
            return;
        }

        $bootstrapped = true;

        add_filter( 'get_user_option_admin_color', array( __CLASS__, 'filter_admin_color' ), 20, 3 );
    }

    /**
     * Keep the classic admin color when WordPress defaults to the new blue scheme.
     *
     * @param mixed   $result Stored user option value.
     * @param string  $option User option name.
     * @param WP_User $user   Current user object.
     * @return mixed
     */
    public static function filter_admin_color( $result, $option = '', $user = null ) {
        if ( ! self::is_enabled() ) {
            return $result;
        }

        $scheme = is_string( $result ) ? sanitize_key( $result ) : '';

        if ( '' === $scheme || 'modern' === $scheme ) {
            return 'fresh';
        }

        return $result;
    }

    /**
     * Whether the compatibility switch is enabled.
     *
     * @return bool
     */
    private static function is_enabled() {
        if ( ! is_admin() ) {
            return false;
        }

        return '1' === (string) developer_starter_get_option( 'admin_disable_wp7_blue_scheme', '1' );
    }
}
