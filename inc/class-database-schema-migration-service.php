<?php
/**
 * Database schema migration service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shared service for theme-owned custom table migrations.
 */
class Database_Schema_Migration_Service {

    /**
     * Default migration lock TTL in seconds.
     */
    const DEFAULT_LOCK_TTL = 300;

    /**
     * Whether the current admin request may run schema migrations.
     *
     * @return bool
     */
    public static function can_run_admin_migration() {
        return ! wp_doing_ajax() && current_user_can( 'manage_options' );
    }

    /**
     * Run a locked schema migration and persist the target schema version.
     *
     * @param array<string,mixed> $args Migration arguments.
     * @return bool Whether the migration callback ran.
     */
    public static function run( $args ) {
        if ( ! is_array( $args ) ) {
            return false;
        }

        $version_option = self::normalize_option_name( isset( $args['version_option'] ) ? $args['version_option'] : '' );
        $target_version = isset( $args['target_version'] ) && is_scalar( $args['target_version'] ) ? (string) $args['target_version'] : '';
        $lock_option    = self::normalize_option_name( isset( $args['lock_option'] ) ? $args['lock_option'] : '' );

        if ( '' === $version_option || '' === $target_version || '' === $lock_option ) {
            return false;
        }

        $force          = ! empty( $args['force'] );
        $default        = isset( $args['default_version'] ) && is_scalar( $args['default_version'] ) ? (string) $args['default_version'] : '';
        $compare        = isset( $args['version_compare'] ) && is_scalar( $args['version_compare'] ) ? sanitize_key( (string) $args['version_compare'] ) : 'strict';
        $lock_ttl       = isset( $args['lock_ttl'] ) ? absint( $args['lock_ttl'] ) : self::DEFAULT_LOCK_TTL;
        $migration      = isset( $args['migration_callback'] ) ? $args['migration_callback'] : null;
        $can_set_version = isset( $args['can_update_version_callback'] ) ? $args['can_update_version_callback'] : null;

        if ( ! is_callable( $migration ) ) {
            return false;
        }

        $installed_version = get_option( $version_option, $default );
        if ( ! $force && ! self::needs_migration( $installed_version, $target_version, $compare ) ) {
            return false;
        }

        if ( ! self::acquire_lock( $lock_option, $lock_ttl ) ) {
            return false;
        }

        try {
            $installed_version = get_option( $version_option, $default );
            if ( ! $force && ! self::needs_migration( $installed_version, $target_version, $compare ) ) {
                return false;
            }

            call_user_func( $migration );

            if ( is_callable( $can_set_version ) && ! call_user_func( $can_set_version ) ) {
                return true;
            }

            update_option( $version_option, $target_version, false );
            return true;
        } finally {
            self::release_lock( $lock_option );
        }
    }

    /**
     * Apply one or more dbDelta table schemas.
     *
     * @param string|array<int,string> $schemas SQL schema string(s).
     * @return void
     */
    public static function apply_schema( $schemas ) {
        $schemas = is_array( $schemas ) ? $schemas : array( $schemas );
        $schemas = array_values(
            array_filter(
                array_map( 'strval', $schemas ),
                static function ( $schema ) {
                    return '' !== trim( $schema );
                }
            )
        );

        if ( empty( $schemas ) ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ( $schemas as $schema ) {
            dbDelta( $schema );
        }
    }

    /**
     * Decide whether a stored schema version should be migrated.
     *
     * @param mixed  $installed_version Installed version.
     * @param string $target_version    Target version.
     * @param string $compare           Compare mode.
     * @return bool
     */
    private static function needs_migration( $installed_version, $target_version, $compare ) {
        $installed_version = is_scalar( $installed_version ) ? (string) $installed_version : '';

        if ( 'version_less' === $compare ) {
            return version_compare( $installed_version, $target_version, '<' );
        }

        return $installed_version !== $target_version;
    }

    /**
     * Acquire a non-autoloaded option lock.
     *
     * @param string $lock_option Lock option name.
     * @param int    $lock_ttl    Lock TTL in seconds.
     * @return bool
     */
    private static function acquire_lock( $lock_option, $lock_ttl ) {
        $lock_ttl = $lock_ttl > 0 ? $lock_ttl : self::DEFAULT_LOCK_TTL;
        if ( add_option( $lock_option, time(), '', false ) ) {
            return true;
        }

        $locked_at = (int) get_option( $lock_option, 0 );
        if ( $locked_at > 0 && ( time() - $locked_at ) > $lock_ttl ) {
            delete_option( $lock_option );
            return (bool) add_option( $lock_option, time(), '', false );
        }

        return false;
    }

    /**
     * Release a migration lock.
     *
     * @param string $lock_option Lock option name.
     * @return void
     */
    private static function release_lock( $lock_option ) {
        delete_option( $lock_option );
    }

    /**
     * Normalize option names used by the migration service.
     *
     * @param mixed $option Option name.
     * @return string
     */
    private static function normalize_option_name( $option ) {
        if ( ! is_scalar( $option ) ) {
            return '';
        }

        $option = trim( (string) $option );
        return '' !== $option ? sanitize_key( $option ) : '';
    }
}
