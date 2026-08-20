<?php
/**
 * Theme lifecycle helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_theme_rewrite_flush_version' ) ) {
    /**
     * 获取主题自身 rewrite rules 刷新标记版本。
     *
     * @return string
     */
    function developer_starter_get_theme_rewrite_flush_version() {
        return defined( 'DEVELOPER_STARTER_VERSION' ) ? (string) DEVELOPER_STARTER_VERSION : '1';
    }
}

if ( ! function_exists( 'developer_starter_queue_theme_rewrite_rules_flush' ) ) {
    /**
     * 标记主题自身 rewrite rules 需要在 init 注册规则后刷新。
     *
     * @param string $version 标记版本，留空使用当前主题版本。
     * @return void
     */
    function developer_starter_queue_theme_rewrite_rules_flush( $version = '' ) {
        $version = '' !== (string) $version ? (string) $version : developer_starter_get_theme_rewrite_flush_version();
        update_option( 'developer_starter_theme_rewrite_flush_version', $version, false );
    }
}

if ( ! function_exists( 'developer_starter_run_locked_table_migrations' ) ) {
    /**
     * 运行带锁的数据表迁移入口，避免生命周期 helper 直接绕过 Manager 内部迁移锁。
     *
     * @param bool $force 是否按安装期强制执行迁移。
     * @return void
     */
    function developer_starter_run_locked_table_migrations( $force = false ) {
        if ( class_exists( 'Developer_Starter\Core\ID_Verification_Manager' ) ) {
            Developer_Starter\Core\ID_Verification_Manager::run_locked_migration( $force );
        }

        if ( class_exists( 'Developer_Starter\Core\Post_Interaction_Manager' ) ) {
            Developer_Starter\Core\Post_Interaction_Manager::run_locked_migration( $force );
        }
    }
}

if ( ! function_exists( 'developer_starter_activation' ) ) {
    /**
     * 主题激活时创建数据表并补齐系统页面。
     *
     * @return void
     */
    function developer_starter_activation() {
        developer_starter_run_locked_table_migrations( true );
        developer_starter_queue_theme_rewrite_rules_flush();

        $auth_manager = new Developer_Starter\Core\Auth_Manager();
        $auth_manager->create_auth_pages();

        if ( class_exists( 'Developer_Starter\Core\Setup_Wizard_State' ) ) {
            Developer_Starter\Core\Setup_Wizard_State::get_instance()->mark_activation_redirect_pending();
        }

        update_option( 'qiling_telemetry_pending', 1, false );
    }
}
add_action( 'after_switch_theme', 'developer_starter_activation' );

if ( ! function_exists( 'developer_starter_check_db_tables' ) ) {
    /**
     * 在后台请求中检测数据表版本并按需升级。
     *
     * @return void
     */
    function developer_starter_check_db_tables() {
        if ( wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $db_version = get_option( 'developer_starter_db_version', '0' );
        if ( version_compare( $db_version, DEVELOPER_STARTER_DB_VERSION, '<' ) ) {
            developer_starter_run_locked_table_migrations();
            update_option( 'developer_starter_db_version', DEVELOPER_STARTER_DB_VERSION );
        }
    }
}
add_action( 'admin_init', 'developer_starter_check_db_tables' );
