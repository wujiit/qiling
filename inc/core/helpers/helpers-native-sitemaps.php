<?php
/**
 * Native sitemap helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_force_native_wp_sitemaps' ) ) {
    /**
     * 主题未内置站点地图时，启用 WordPress 原生 Sitemap。
     *
     * 若站点已启用主流 SEO 插件，则尊重第三方插件的控制权，避免重复。
     *
     * @param bool $enabled 当前原生 Sitemap 是否启用。
     * @return bool
     */
    function developer_starter_force_native_wp_sitemaps( $enabled ) {
        if ( function_exists( 'developer_starter_has_external_seo_plugin' ) && developer_starter_has_external_seo_plugin() ) {
            return $enabled;
        }

        return true;
    }
}
add_filter( 'wp_sitemaps_enabled', 'developer_starter_force_native_wp_sitemaps', 999 );

if ( ! function_exists( 'developer_starter_maybe_flush_sitemap_rewrite_rules' ) ) {
    /**
     * 启用原生 Sitemap 支持后，确保固定链接规则刷新一次。
     *
     * @return void
     */
    function developer_starter_maybe_flush_sitemap_rewrite_rules() {
        if ( ! function_exists( 'wp_sitemaps_get_server' ) || wp_installing() ) {
            return;
        }

        if ( function_exists( 'developer_starter_has_external_seo_plugin' ) && developer_starter_has_external_seo_plugin() ) {
            return;
        }

        $version       = '1';
        $option_name   = 'developer_starter_native_sitemap_rewrite_version';
        $saved_version = (string) get_option( $option_name, '' );

        if ( $saved_version === $version ) {
            return;
        }

        flush_rewrite_rules( false );
        update_option( $option_name, $version, false );
    }
}
add_action( 'init', 'developer_starter_maybe_flush_sitemap_rewrite_rules', 99 );
