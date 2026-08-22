<?php
/**
 * Theme service bootstrap helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get the current AJAX action name.
 *
 * @return string
 */
function developer_starter_get_ajax_action_name() {
    if ( ! wp_doing_ajax() ) {
        return '';
    }

    return isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
}

/**
 * Whether the Weixin login manager needs to be initialized.
 *
 * @return bool
 */
function developer_starter_should_init_weixin_manager() {
    $ajax_action = developer_starter_get_ajax_action_name();
    return is_admin()
        || developer_starter_get_option( 'weixin_login_enable', '' ) === '1'
        || $ajax_action === 'qiling_weixin_unbind';
}

/**
 * Whether third-party social login needs to be initialized.
 *
 * @return bool
 */
function developer_starter_should_init_social_login_manager() {
    $request_action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';

    return is_admin()
        || developer_starter_get_option( 'social_login_qq_enable', '' ) === '1'
        || developer_starter_get_option( 'social_login_github_enable', '' ) === '1'
        || developer_starter_get_option( 'social_login_google_enable', '' ) === '1'
        || strpos( $request_action, 'developer_starter_social_login' ) === 0;
}

/**
 * Whether search autocomplete needs to be initialized.
 *
 * @return bool
 */
function developer_starter_should_init_search_autocomplete() {
    $request_action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';

    return ( wp_doing_ajax() && 'developer_starter_search_autocomplete' === $request_action )
        || ! is_admin();
}

/**
 * Whether announcements are enabled for the current request.
 *
 * @return bool
 */
function developer_starter_should_init_announcement_manager() {
    return developer_starter_get_option( 'announcement_enable', '' ) === '1';
}

/**
 * Whether the careers admin menu is enabled.
 *
 * @return bool
 */
function developer_starter_is_careers_admin_menu_enabled() {
    return '1' === (string) developer_starter_get_option( 'careers_admin_menu_enable', '1' );
}

/**
 * Whether the WooCommerce admin menu is enabled.
 *
 * @return bool
 */
function developer_starter_is_woocommerce_admin_menu_enabled() {
    return '1' === (string) developer_starter_get_option( 'woocommerce_admin_menu_enable', '1' );
}

/**
 * Whether the careers manager needs to be initialized.
 *
 * @return bool
 */
function developer_starter_should_init_careers_manager() {
    $ajax_action = developer_starter_get_ajax_action_name();

    if ( wp_doing_ajax() ) {
        return $ajax_action === 'ds_submit_careers_application';
    }

    if ( is_admin() ) {
        return developer_starter_is_careers_admin_menu_enabled();
    }

    return class_exists( 'Developer_Starter\\Core\\Careers_Manager' ) && Developer_Starter\Core\Careers_Manager::is_enabled();
}

/**
 * Hide feature admin menus that are disabled in theme settings.
 *
 * @return void
 */
function developer_starter_maybe_hide_disabled_feature_admin_menus() {
    if ( ! developer_starter_is_careers_admin_menu_enabled() ) {
        foreach ( array(
            'developer-starter-careers-settings',
            'developer-starter-careers-positions',
            'developer-starter-careers-applications',
        ) as $careers_menu_slug ) {
            remove_submenu_page( 'developer-starter-settings', $careers_menu_slug );
        }
    }

    if ( developer_starter_is_woocommerce_admin_menu_enabled() ) {
        return;
    }

    remove_submenu_page( 'developer-starter-settings', \Developer_Starter\WooCommerce\WC_Admin::PAGE_SLUG );

    foreach ( array(
        'woocommerce',
        'edit.php?post_type=product',
        'wc-admin&path=/analytics/overview',
        'woocommerce-marketing',
    ) as $woocommerce_menu_slug ) {
        remove_menu_page( $woocommerce_menu_slug );
    }
}
add_action( 'admin_menu', 'developer_starter_maybe_hide_disabled_feature_admin_menus', 999 );

/**
 * Whether ID verification needs to be initialized.
 *
 * @return bool
 */
function developer_starter_should_init_id_verification_manager() {
    return is_admin() || developer_starter_get_option( 'id_verification_enable', '' ) === '1';
}

/**
 * Whether the SMS manager needs to be initialized.
 *
 * @return bool
 */
function developer_starter_should_init_sms_manager() {
    $ajax_action = developer_starter_get_ajax_action_name();
    return is_admin()
        || ( class_exists( 'Developer_Starter\\Core\\SMS_Manager' ) && Developer_Starter\Core\SMS_Manager::is_enabled() )
        || strpos( $ajax_action, 'sms_' ) === 0;
}

/**
 * Whether theme license checks need to be initialized.
 *
 * @return bool
 */
function developer_starter_should_init_theme_license_manager() {
    return is_admin() || wp_doing_ajax();
}

/**
 * Get the thumbnail optimizer instance.
 *
 * @return Developer_Starter\Core\Thumbnail_Optimizer
 */
function developer_starter_get_thumbnail_optimizer_instance() {
    static $thumbnail_optimizer = null;

    if ( null === $thumbnail_optimizer ) {
        $thumbnail_optimizer = new Developer_Starter\Core\Thumbnail_Optimizer();
    }

    return $thumbnail_optimizer;
}

/**
 * Get setup wizard state storage.
 *
 * @return Developer_Starter\Core\Setup_Wizard_State
 */
function developer_starter_get_setup_wizard_state_service() {
    return Developer_Starter\Core\Setup_Wizard_State::get_instance();
}

/**
 * Get setup wizard optional plugin detector.
 *
 * @return Developer_Starter\Core\Setup_Wizard_Plugin_Detector
 */
function developer_starter_get_setup_wizard_plugin_detector() {
    static $detector = null;

    if ( null === $detector ) {
        $detector = new Developer_Starter\Core\Setup_Wizard_Plugin_Detector();
    }

    return $detector;
}

/**
 * Get setup wizard reuse checks.
 *
 * @return Developer_Starter\Core\Setup_Wizard_Reuse_Service
 */
function developer_starter_get_setup_wizard_reuse_service() {
    static $reuse_service = null;

    if ( null === $reuse_service ) {
        $reuse_service = new Developer_Starter\Core\Setup_Wizard_Reuse_Service();
    }

    return $reuse_service;
}

/**
 * Get setup wizard recommendation presets.
 *
 * @return Developer_Starter\Core\Setup_Wizard_Presets
 */
function developer_starter_get_setup_wizard_presets() {
    return Developer_Starter\Core\Setup_Wizard_Presets::get_instance();
}

/**
 * Get setup wizard page generation service.
 *
 * @return Developer_Starter\Core\Setup_Wizard_Import_Service
 */
function developer_starter_get_setup_wizard_import_service() {
    static $import_service = null;

    if ( null === $import_service ) {
        $import_service = new Developer_Starter\Core\Setup_Wizard_Import_Service(
            developer_starter_get_setup_wizard_state_service(),
            developer_starter_get_setup_wizard_reuse_service(),
            developer_starter_get_setup_wizard_presets()
        );
    }

    return $import_service;
}

/**
 * Get setup wizard menu and basic settings service.
 *
 * @return Developer_Starter\Core\Setup_Wizard_Settings_Service
 */
function developer_starter_get_setup_wizard_settings_service() {
    static $settings_service = null;

    if ( null === $settings_service ) {
        $settings_service = new Developer_Starter\Core\Setup_Wizard_Settings_Service(
            developer_starter_get_setup_wizard_state_service(),
            developer_starter_get_setup_wizard_reuse_service(),
            developer_starter_get_setup_wizard_presets()
        );
    }

    return $settings_service;
}

/**
 * Get setup wizard safe cleanup service.
 *
 * @return Developer_Starter\Core\Setup_Wizard_Cleanup_Service
 */
function developer_starter_get_setup_wizard_cleanup_service() {
    static $cleanup_service = null;

    if ( null === $cleanup_service ) {
        $cleanup_service = new Developer_Starter\Core\Setup_Wizard_Cleanup_Service(
            developer_starter_get_setup_wizard_state_service()
        );
    }

    return $cleanup_service;
}

/**
 * Bootstrap foundational frontend and shared services.
 *
 * @return void
 */
function developer_starter_boot_foundation_services() {
    Developer_Starter\Core\Search_Mode_Manager::get_instance();
    Developer_Starter\Core\Content_Model_Center::get_instance();
    Developer_Starter\Core\User_Manager::init();
    Developer_Starter\Core\Auth_Functions::init();
    Developer_Starter\Core\Blog_Visual_Manager::bootstrap();

    if ( class_exists( '\Developer_Starter\Core\Module_Advanced_Style_Service' ) ) {
        Developer_Starter\Core\Module_Advanced_Style_Service::get_instance();
    }
    if ( class_exists( '\Developer_Starter\Core\Module_Visual_Style_Service' ) ) {
        Developer_Starter\Core\Module_Visual_Style_Service::get_instance();
    }

    new Developer_Starter\Core\Assets();
    new Developer_Starter\International\Third_Party_Code_Manager();
    new Developer_Starter\International\Cookie_Consent_Manager();
    new Developer_Starter\International\Typography_Engine();

    if ( developer_starter_should_init_weixin_manager() ) {
        new Developer_Starter\Core\Weixin_Manager();
    }

    if ( developer_starter_should_init_social_login_manager() ) {
        Developer_Starter\Core\Social\Manager::get_instance()->init();
    }

    if ( developer_starter_should_init_search_autocomplete() ) {
        Developer_Starter\Core\Search_Autocomplete::get_instance()->init();
    }

    Developer_Starter\Core\Notification_Manager::instance();
    new Developer_Starter\Core\SMTP_Manager();
    new Developer_Starter\Core\Theme_Notification_Events();
}

/**
 * Bootstrap admin-only services.
 *
 * @return void
 */
function developer_starter_boot_admin_services() {
    if ( ! is_admin() ) {
        return;
    }

    Developer_Starter\Core\Admin_Color_Scheme_Manager::bootstrap();

    if ( developer_starter_should_boot_admin_settings() ) {
        developer_starter_get_admin_settings_instance();
    }

    new Developer_Starter\Admin\Advanced_Category_Metabox();
    new Developer_Starter\Admin\Setup_Wizard_Admin();
    new Developer_Starter\Admin\Dashboard_User_Stats();
    new Developer_Starter\Admin\Template_Center_Admin();
    new Developer_Starter\Admin\Page_Package_Admin();
    new Developer_Starter\Admin\User_Columns();
    new Developer_Starter\Admin\Site_Notification_Admin();
}

/**
 * Bootstrap builder, module, and page composition services.
 *
 * @return void
 */
function developer_starter_boot_builder_services() {
    Developer_Starter\Modules\Module_Manager::get_instance();
    Developer_Starter\Core\Template_Manager::get_instance();
    Developer_Starter\Core\Dynamic_Data_Manager::get_instance();

    new Developer_Starter\Core\Frontend_Builder();
    Developer_Starter\Core\AI_Decorator::get_instance();
}

/**
 * Bootstrap SEO, region, and quality tools.
 *
 * @return void
 */
function developer_starter_boot_discovery_services() {
    new Developer_Starter\China\China_Features();
    new Developer_Starter\SEO\SEO_Manager();

    Developer_Starter\Core\Page_Performance_A11y_Auditor::get_instance();
}

/**
 * Bootstrap content, account, and interaction services.
 *
 * @return void
 */
function developer_starter_boot_content_services() {
    Developer_Starter\Core\Pinyin_Slug_Manager::get_instance()->init();

    Developer_Starter\Core\Post_Enhancer::get_instance();

    new Developer_Starter\Core\Menu_Protector();

    if ( developer_starter_should_init_announcement_manager() ) {
        new Developer_Starter\Core\Announcement_Manager();
    }

    if ( developer_starter_should_init_careers_manager() ) {
        new Developer_Starter\Core\Careers_Manager();
    }

    new Developer_Starter\Core\Mega_Menu_Manager();
    new Developer_Starter\Core\Auth_Manager();
    new Developer_Starter\Core\FAQ_Manager();
    new Developer_Starter\Core\Message_Manager();

    if ( developer_starter_should_init_id_verification_manager() ) {
        new Developer_Starter\Core\ID_Verification_Manager();
    }

    new Developer_Starter\Core\Account_Deletion_Manager();
    new Developer_Starter\Core\Post_Interaction_Manager();

    if ( developer_starter_should_init_sms_manager() ) {
        new Developer_Starter\Core\SMS_Manager();
    }

    developer_starter_get_thumbnail_optimizer_instance();

    if ( developer_starter_should_init_theme_license_manager() ) {
        new Developer_Starter\Core\Theme_License();
    }

    if ( is_admin() && developer_starter_should_init_id_verification_manager() ) {
        new Developer_Starter\Admin\ID_Verification_Records();
    }
}

/**
 * Bootstrap taxonomy, AJAX, and commerce integrations.
 *
 * @return void
 */
function developer_starter_boot_taxonomy_and_commerce_services() {
    new Developer_Starter\Core\Category_Manager();

    require_once DEVELOPER_STARTER_INC . '/core/class-category-tabs-ajax.php';

    if ( is_admin() && developer_starter_is_woocommerce_admin_menu_enabled() ) {
        new Developer_Starter\WooCommerce\WC_Admin();
    }

    new Developer_Starter\WooCommerce\WC_Setup();
    new Developer_Starter\Core\AJAX_Product_Loader();
}

/**
 * Initialize theme business services after WordPress reaches init.
 *
 * @return void
 */
function developer_starter_init() {
    static $initialized = false;
    if ( $initialized ) {
        return;
    }
    $initialized = true;

    developer_starter_preload_options_cache();

    developer_starter_boot_foundation_services();
    developer_starter_boot_admin_services();
    developer_starter_boot_builder_services();
    developer_starter_boot_discovery_services();

    if ( developer_starter_should_init_page_creators() ) {
        developer_starter_init_page_creators();
    }

    developer_starter_boot_content_services();
    developer_starter_boot_taxonomy_and_commerce_services();
}
add_action( 'init', 'developer_starter_init', 1 );
