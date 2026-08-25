<?php
/**
 * Header actions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'show_search'               => true,
        'show_search_form'          => false,
        'show_phone'                => true,
        'header_search_action'      => home_url( '/' ),
        'header_search_use_rewrite' => false,
    )
);

$show_search               = ! empty( $args['show_search'] );
$show_search_form          = ! empty( $args['show_search_form'] );
$show_phone                = ! empty( $args['show_phone'] );
$header_search_action      = (string) $args['header_search_action'];
$header_search_use_rewrite = ! empty( $args['header_search_use_rewrite'] );
$header_search_mode        = function_exists( 'developer_starter_get_search_mode_form_value' ) ? developer_starter_get_search_mode_form_value() : 'all';
?>
<?php if ( $show_search ) : ?>
    <div class="header-search <?php echo $show_search_form ? 'header-search-mode-form' : 'header-search-mode-icon'; ?>">
        <?php if ( $show_search_form ) : ?>
            <form role="search" method="get" class="header-search-form-inline qiling-search-enhanced" data-qiling-search-form="1" action="<?php echo esc_url( $header_search_action ); ?>"<?php if ( $header_search_use_rewrite ) : ?> onsubmit="return dsSearchRedirect(this);"<?php endif; ?>>
                <label class="screen-reader-text" for="header-search-input"><?php esc_html_e( '搜索', 'developer-starter' ); ?></label>
                <input id="header-search-input" class="header-search-input" type="search" name="s" placeholder="<?php esc_attr_e( '搜索关键词', 'developer-starter' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" autocomplete="off" data-qiling-search-input="1" />
                <input type="hidden" name="search_scope" value="all" />
                <input type="hidden" name="qiling_search_mode" value="<?php echo esc_attr( $header_search_mode ); ?>" />
                <button type="submit" class="header-search-submit" aria-label="<?php esc_attr_e( '提交搜索', 'developer-starter' ); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </form>
            <button type="button" class="search-toggle header-search-mobile-toggle" id="search-toggle" title="<?php esc_attr_e( '搜索', 'developer-starter' ); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </button>
        <?php else : ?>
            <button type="button" class="search-toggle" id="search-toggle" title="<?php esc_attr_e( '搜索', 'developer-starter' ); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$phone = developer_starter_get_option( 'company_phone', '' );
if ( $phone && $show_phone ) :
    $phone_clean  = preg_replace( '/[^0-9+]/', '', $phone );
    ?>
    <a href="tel:<?php echo esc_attr( $phone_clean ); ?>" class="header-phone">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        <span><?php echo esc_html( $phone ); ?></span>
    </a>
<?php endif; ?>

<?php
$header_login_enable = developer_starter_get_option( 'header_login_enable', '' );
$header_account_enable = developer_starter_get_option( 'header_account_enable', '1' );
if ( $header_login_enable || ( $header_account_enable && is_user_logged_in() ) ) :
    $login_text = developer_starter_get_option( 'header_login_text', '' );
    $login_text = ! empty( $login_text ) ? $login_text : __( '登录', 'developer-starter' );
    $is_logged_in = is_user_logged_in();
    $current_user  = wp_get_current_user();
    $unread_notice_count = 0;
    if ( $is_logged_in && function_exists( 'developer_starter_get_unread_notification_count' ) ) {
        $unread_notice_count = (int) developer_starter_get_unread_notification_count( $current_user->ID );
    }

    $allow_account_url_cache = ! $is_logged_in;
    if ( $allow_account_url_cache && function_exists( 'developer_starter_has_logged_in_cookie_hint' ) ) {
        $allow_account_url_cache = ! developer_starter_has_logged_in_cookie_hint();
    }

    $account_url_cache_args = array(
        'audience'       => 'public',
        'surface'        => 'frontend',
        'group'          => 'developer_starter_theme',
        'scope'          => 'header_account_url',
        'blog_scoped'    => true,
        'version_groups' => array( 'content' ),
    );

    if ( function_exists( 'developer_starter_cache_read' ) ) {
        $account_url = developer_starter_cache_read( 'developer_starter_account_url', $account_url_cache_args );
    } else {
        $account_url = $allow_account_url_cache ? get_transient( 'developer_starter_account_url' ) : false;
    }

    if ( false === $account_url ) {
        $account_page = get_pages(
            array(
                'meta_key'   => '_wp_page_template',
                'meta_value' => 'templates/template-account.php',
                'number'     => 1,
            )
        );
        $account_url  = ! empty( $account_page ) ? get_permalink( $account_page[0]->ID ) : admin_url( 'profile.php' );
        if ( function_exists( 'developer_starter_cache_write' ) ) {
            developer_starter_cache_write( 'developer_starter_account_url', $account_url, DAY_IN_SECONDS, $account_url_cache_args );
        } elseif ( $allow_account_url_cache ) {
            set_transient( 'developer_starter_account_url', $account_url, DAY_IN_SECONDS );
        }
    }

    $header_auth_sync_script = DEVELOPER_STARTER_ASSETS . '/js/header-auth.js';
    if ( function_exists( 'developer_starter_get_assets_version' ) ) {
        $header_auth_sync_script = add_query_arg(
            'ver',
            rawurlencode( (string) developer_starter_get_assets_version() ),
            $header_auth_sync_script
        );
    }
    $header_auth_script_enqueued = wp_script_is( 'developer-starter-header-auth', 'enqueued' )
        || wp_script_is( 'developer-starter-header-auth', 'to_do' )
        || wp_script_is( 'developer-starter-header-auth', 'done' );
    ?>
    <div class="header-auth" id="header-auth-wrapper">
        <?php if ( $header_login_enable ) : ?>
            <div class="header-login" id="header-login-area" style="<?php echo $is_logged_in ? 'display:none;' : ''; ?>">
                <button type="button" class="header-login-btn" id="header-login-toggle" title="<?php echo esc_attr( $login_text ); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span><?php echo esc_html( $login_text ); ?></span>
                </button>
            </div>
        <?php endif; ?>
        <?php if ( $header_account_enable ) : ?>
            <div class="header-user-menu" id="header-user-area" style="<?php echo $is_logged_in ? '' : 'display:none;'; ?>">
            <a href="<?php echo esc_url( $account_url ); ?>" class="header-user-btn" id="header-user-toggle" title="<?php esc_attr_e( '个人中心', 'developer-starter' ); ?>">
                <img id="header-user-avatar" src="<?php echo esc_url( get_avatar_url( $current_user->ID, array( 'size' => 32 ) ) ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>" width="32" height="32" loading="lazy" decoding="async" />
                <?php if ( $unread_notice_count > 0 ) : ?>
                    <span class="header-user-badge" aria-label="<?php esc_attr_e( '未读通知', 'developer-starter' ); ?>">
                        <?php echo esc_html( $unread_notice_count > 99 ? '99+' : (string) $unread_notice_count ); ?>
                    </span>
                <?php endif; ?>
            </a>
            <div class="user-dropdown" id="user-dropdown">
                <div class="dropdown-header">
                    <img id="dropdown-user-avatar" src="<?php echo esc_url( get_avatar_url( $current_user->ID, array( 'size' => 48 ) ) ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>" width="48" height="48" loading="lazy" decoding="async" />
                    <div class="dropdown-user-info">
                        <strong id="dropdown-user-name"><?php echo esc_html( $current_user->display_name ); ?></strong>
                        <span id="dropdown-user-email"></span>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="<?php echo esc_url( $account_url ); ?>" id="dropdown-account-link">
                    <?php esc_html_e( '个人中心', 'developer-starter' ); ?>
                </a>
                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <a href="<?php echo esc_url( admin_url() ); ?>" id="dropdown-admin-link">
                        <?php esc_html_e( '管理后台', 'developer-starter' ); ?>
                    </a>
                <?php endif; ?>

                <?php do_action( 'qiling_user_dropdown_items' ); ?>

                <div class="dropdown-divider"></div>
                <a href="<?php echo esc_url( function_exists( 'developer_starter_get_front_logout_url' ) ? developer_starter_get_front_logout_url() : wp_logout_url( home_url() ) ); ?>" class="logout-link" id="dropdown-logout-link">
                    <?php esc_html_e( '退出登录', 'developer-starter' ); ?>
                </a>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <?php if ( ! $header_auth_script_enqueued ) : ?>
        <script>
        (function () {
            var authWrapper = document.getElementById('header-auth-wrapper');
            if (!authWrapper) return;
            var loginArea = document.getElementById('header-login-area');
            var userArea = document.getElementById('header-user-area');
            if (!loginArea && !userArea) return;
            if (!window.__dsHeaderAuthInitialized && !window.__dsHeaderAuthLoading) {
                var existing = document.querySelector('script[src*="/js/header-auth.js"],script[data-ds-header-auth-inline="1"]');
                if (!existing) {
                    window.__dsHeaderAuthLoading = true;
                    var script = document.createElement('script');
                    script.src = '<?php echo esc_js( $header_auth_sync_script ); ?>';
                    script.defer = true;
                    script.setAttribute('data-ds-header-auth-inline', '1');
                    script.onload = function () {
                        window.__dsHeaderAuthLoading = false;
                    };
                    script.onerror = function () {
                        window.__dsHeaderAuthLoading = false;
                    };
                    (document.head || document.documentElement).appendChild(script);
                }
            }
        })();
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php
$language_switch_mode = function_exists( 'developer_starter_get_frontend_language_switch_mode' )
    ? developer_starter_get_frontend_language_switch_mode()
    : '';
$language_switcher_enabled = false;
$lang_count = 0;
if ( function_exists( 'developer_starter_get_multilingual_languages' ) ) {
    $lang_count = count( developer_starter_get_multilingual_languages() );
}
if ( 'translate_js' === $language_switch_mode ) {
    $language_switcher_enabled = true;
} elseif ( 'multilingual_content' === $language_switch_mode ) {
    $language_switcher_enabled = ( $lang_count > 1 );
}

if ( $language_switcher_enabled ) :
    ?>
    <div class="header-translate">
        <button type="button" class="translate-toggle" id="translate-toggle" title="<?php esc_attr_e( '语言切换', 'developer-starter' ); ?>">
            <span style="font-size: var(--qiling-text-rem-1p125); line-height: 1;">🌐</span>
        </button>
    </div>
<?php endif; ?>

<?php
$darkmode_enable = developer_starter_get_option( 'darkmode_enable', '' );
if ( $darkmode_enable ) :
    ?>
    <div class="header-darkmode">
        <button type="button" class="darkmode-toggle" id="darkmode-toggle" title="<?php esc_attr_e( '切换暗黑模式', 'developer-starter' ); ?>">
            <span class="icon-sun" style="font-size: var(--qiling-text-rem-1p125); line-height: 1;">☀️</span>
            <span class="icon-moon" style="font-size: var(--qiling-text-rem-1p125); line-height: 1; display:none">🌙</span>
        </button>
    </div>
<?php endif; ?>

<?php do_action( 'qiling_header_actions' ); ?>

<button type="button" class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="<?php esc_attr_e( '打开菜单', 'developer-starter' ); ?>" aria-haspopup="dialog" aria-controls="mobile-menu" aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
</button>
