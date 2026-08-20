<?php
/**
 * Admin Settings Class
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Developer_Starter\Core\Theme_License;
use Developer_Starter\Core\Category_Manager;
use Developer_Starter\Core\ID_Verification_Manager;
use Developer_Starter\Core\Post_Interaction_Manager;
use Developer_Starter\Admin\Traits\Admin_Settings_Repair_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Domain_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Backup_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Helpers_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Ajax_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Field_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Field_Render_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Page_Render_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Admin_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Config_Trait;
use Developer_Starter\Admin\Traits\Admin_Settings_Sanitize_Trait;

class Admin_Settings {

    private $option_name = 'developer_starter_options';
    private $favorite_settings = array();

    use Admin_Settings_Repair_Trait;
    use Admin_Settings_Domain_Trait;
    use Admin_Settings_Backup_Trait;
    use Admin_Settings_Helpers_Trait;
    use Admin_Settings_Ajax_Trait;
    use Admin_Settings_Field_Trait;
    use Admin_Settings_Field_Render_Trait;
    use Admin_Settings_Page_Render_Trait;
    use Admin_Settings_Admin_Trait;
    use Admin_Settings_Config_Trait;
    use Admin_Settings_Sanitize_Trait;

    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'handle_reset' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        // 管理栏入口由 admin-bootstrap.php 的轻量入口统一注册，避免在设置页二次注册导致排序异常。
        add_action( 'wp_ajax_developer_starter_refresh_version', array( $this, 'ajax_refresh_version' ) );
        add_action( 'wp_ajax_developer_starter_db_cleanup', array( $this, 'ajax_db_cleanup' ) );
        add_action( 'wp_ajax_developer_starter_run_theme_cleanup', array( $this, 'ajax_run_theme_cleanup' ) );
        add_action( 'wp_ajax_developer_starter_regenerate_cleanup_cron_token', array( $this, 'ajax_regenerate_cleanup_cron_token' ) );
        add_action( 'wp_ajax_developer_starter_clear_cleanup_rest_audit_log', array( $this, 'ajax_clear_cleanup_rest_audit_log' ) );
        add_action( 'wp_ajax_developer_starter_db_stats', array( $this, 'ajax_db_stats' ) );
        add_action( 'wp_ajax_developer_starter_poster_cache_stats', array( $this, 'ajax_poster_cache_stats' ) );
         add_action( 'wp_ajax_developer_starter_clear_poster_cache', array( $this, 'ajax_clear_poster_cache' ) );
         add_action( 'wp_ajax_developer_starter_thumbnail_cache_stats', array( $this, 'ajax_thumbnail_cache_stats' ) );
         add_action( 'wp_ajax_developer_starter_clear_thumbnail_cache', array( $this, 'ajax_clear_thumbnail_cache' ) );
         add_action( 'wp_ajax_developer_starter_clear_unused_thumbnail_cache', array( $this, 'ajax_clear_unused_thumbnail_cache' ) );
        add_action( 'wp_ajax_developer_starter_github_activity_cache_stats', array( $this, 'ajax_github_activity_cache_stats' ) );
        add_action( 'wp_ajax_developer_starter_clear_github_activity_cache', array( $this, 'ajax_clear_github_activity_cache' ) );
        add_action( 'wp_ajax_developer_starter_generate_css', array( $this, 'ajax_generate_css' ) );
        add_action( 'wp_ajax_developer_starter_check_gzip_status', array( $this, 'ajax_check_gzip_status' ) );
        add_action( 'wp_ajax_developer_starter_check_split_css_integrity', array( $this, 'ajax_check_split_css_integrity' ) );
        add_action( 'wp_ajax_developer_starter_clear_ip_cache', array( $this, 'ajax_clear_ip_cache' ) );
        add_action( 'wp_ajax_developer_starter_reset_ip_usermeta', array( $this, 'ajax_reset_ip_usermeta' ) );
        add_action( 'wp_ajax_developer_starter_toggle_favorite_setting', array( $this, 'ajax_toggle_favorite_setting' ) );
        add_action( 'wp_ajax_developer_starter_send_smtp_test_email', array( $this, 'ajax_send_smtp_test_email' ) );
        add_action( 'wp_ajax_developer_starter_test_ai_connection', array( $this, 'ajax_test_ai_connection' ) );
        add_action( 'wp_ajax_developer_starter_seo_health_scan', array( $this, 'ajax_seo_health_scan' ) );
        add_action( 'wp_ajax_developer_starter_seo_health_clear', array( $this, 'ajax_seo_health_clear' ) );
        add_action( 'wp_ajax_developer_starter_detect_ecosystem_plugins', array( $this, 'ajax_detect_ecosystem_plugins' ) );

        // 备份与恢复
        add_action( 'admin_post_ds_export_settings', array( $this, 'handle_export_settings' ) );
        add_action( 'admin_post_ds_import_settings', array( $this, 'handle_import_settings' ) );
        // Repair admin-post actions are registered by admin-bootstrap.php so they are available before settings lazy-load.
        add_action( 'admin_post_ds_create_theme_table', array( $this, 'handle_create_theme_table' ) );
        add_action( 'admin_post_developer_starter_export_i18n_seo_report', array( $this, 'handle_international_seo_report_export' ) );
        add_action( 'admin_post_developer_starter_generate_i18n_seo_meta', array( $this, 'handle_international_seo_meta_generation' ) );
    }
}
