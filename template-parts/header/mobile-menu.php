<?php
/**
 * 页头移动端导航抽屉菜单模板。
 *
 * 支持在主题后台自由配置抽屉滑出方向、顶部欢迎语、用户状态卡片、微信客服二维码卡片与行动转化大按钮。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args    = is_array( $args ) ? $args : array();
$menu_id = isset( $args['menu_id'] ) ? (int) $args['menu_id'] : 0;

$site_logo   = developer_starter_get_option( 'site_logo', '' );
$mobile_logo = developer_starter_get_option( 'mobile_logo', '' );

$normalize_media_url = static function ( $value ) {
    if ( empty( $value ) ) {
        return '';
    }
    if ( is_array( $value ) ) {
        return ! empty( $value['url'] ) ? $value['url'] : '';
    }
    if ( is_numeric( $value ) ) {
        $url = wp_get_attachment_url( (int) $value );
        if ( $url ) {
            return $url;
        }
    }
    return (string) $value;
};

if ( function_exists( 'developer_starter_get_media_url' ) ) {
    $site_logo   = developer_starter_get_media_url( $site_logo );
    $mobile_logo = developer_starter_get_media_url( $mobile_logo );
} else {
    $site_logo   = $normalize_media_url( $site_logo );
    $mobile_logo = $normalize_media_url( $mobile_logo );
}

$mobile_menu_logo = $mobile_logo ? $mobile_logo : $site_logo;
$mobile_logo_dims = array( 'width' => 140, 'height' => 36 );
if ( $mobile_menu_logo && function_exists( 'developer_starter_get_image_dimensions_by_url' ) ) {
    $mobile_logo_dims = developer_starter_get_image_dimensions_by_url(
        $mobile_menu_logo,
        array( 'width' => 140, 'height' => 36 )
    );
}

// 抽屉个性化配置
$drawer_direction = developer_starter_get_option( 'mobile_drawer_direction', 'left' );
$drawer_direction = in_array( $drawer_direction, array( 'left', 'right' ), true ) ? $drawer_direction : 'left';
$welcome_text     = trim( (string) developer_starter_get_option( 'mobile_drawer_welcome_text', '' ) );
$show_user        = '0' !== (string) developer_starter_get_option( 'mobile_drawer_user_enable', '1' );

// 微信/客服二维码卡片配置
$qrcode_enable = (bool) developer_starter_get_option( 'mobile_drawer_qrcode_enable', '' );
$qrcode_img    = $normalize_media_url( developer_starter_get_option( 'mobile_drawer_qrcode_img', '' ) );
$qrcode_title  = trim( (string) developer_starter_get_option( 'mobile_drawer_qrcode_title', '' ) );
$qrcode_title  = '' !== $qrcode_title ? $qrcode_title : __( '添加官方微信', 'qiling' );
$qrcode_desc   = trim( (string) developer_starter_get_option( 'mobile_drawer_qrcode_desc', '' ) );
$qrcode_desc   = '' !== $qrcode_desc ? $qrcode_desc : __( '长按二维码识别添加客服微信', 'qiling' );

// 行动转化大按钮配置
$cta_enable = (bool) developer_starter_get_option( 'mobile_drawer_cta_enable', '' );
$cta_text   = trim( (string) developer_starter_get_option( 'mobile_drawer_cta_text', '' ) );
$cta_text   = '' !== $cta_text ? $cta_text : __( '立即在线咨询', 'qiling' );
$cta_link   = trim( (string) developer_starter_get_option( 'mobile_drawer_cta_link', '' ) );
$cta_style  = developer_starter_get_option( 'mobile_drawer_cta_style', 'primary' );
$cta_style  = in_array( $cta_style, array( 'primary', 'outline' ), true ) ? $cta_style : 'primary';

// 电话与工具配置
$show_phone = '0' !== (string) developer_starter_get_option( 'mobile_drawer_show_phone', '1' );
$show_tools = '0' !== (string) developer_starter_get_option( 'mobile_drawer_show_tools', '1' );
$phone      = developer_starter_get_option( 'company_phone', '' );
$phone_tel  = preg_replace( '/[^0-9+]/', '', (string) $phone );

// 用户信息
$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;
$account_url  = function_exists( 'developer_starter_get_account_page_url' )
    ? developer_starter_get_account_page_url()
    : home_url( '/account' );

$drawer_classes   = array( 'mobile-menu' );
$drawer_classes[] = 'mobile-menu--' . sanitize_html_class( $drawer_direction );
?>
<div class="<?php echo esc_attr( implode( ' ', $drawer_classes ) ); ?>" id="mobile-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( '移动端菜单', 'qiling' ); ?>" aria-hidden="true" tabindex="-1">
    <div class="mobile-menu-header">
        <div class="mobile-menu-brand-wrap">
            <div class="mobile-menu-logo">
                <?php if ( $mobile_menu_logo ) : ?>
                    <img src="<?php echo esc_url( $mobile_menu_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="<?php echo esc_attr( (int) $mobile_logo_dims['width'] ); ?>" height="<?php echo esc_attr( (int) $mobile_logo_dims['height'] ); ?>" loading="lazy" decoding="async" />
                <?php elseif ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <span class="site-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
                <?php endif; ?>
            </div>
            <?php if ( '' !== $welcome_text ) : ?>
                <div class="mobile-menu-welcome"><?php echo esc_html( $welcome_text ); ?></div>
            <?php endif; ?>
        </div>
        <button type="button" class="mobile-menu-close" id="mobile-menu-close" aria-label="<?php esc_attr_e( '关闭菜单', 'qiling' ); ?>" aria-controls="mobile-menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <?php if ( $show_user ) : ?>
        <div class="mobile-drawer-user-card">
            <?php if ( $is_logged_in && $current_user ) : ?>
                <a href="<?php echo esc_url( $account_url ); ?>" class="mobile-user-link">
                    <div class="mobile-user-avatar">
                        <?php echo get_avatar( $current_user->ID, 44, '', esc_attr( $current_user->display_name ) ); ?>
                    </div>
                    <div class="mobile-user-info">
                        <span class="mobile-user-name"><?php echo esc_html( $current_user->display_name ); ?></span>
                        <span class="mobile-user-sub"><?php esc_html_e( '个人中心', 'qiling' ); ?> &rarr;</span>
                    </div>
                </a>
            <?php else : ?>
                <div class="mobile-user-guest">
                    <div class="mobile-guest-avatar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="mobile-guest-action">
                        <span class="mobile-guest-tip"><?php esc_html_e( '欢迎访问网站', 'qiling' ); ?></span>
                        <button type="button" class="mobile-guest-login-btn ds-login-modal-toggle" id="mobile-drawer-login-btn">
                            <?php esc_html_e( '立即登录 / 注册', 'qiling' ); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="mobile-drawer-scroll-body">
        <nav class="mobile-menu-nav" aria-label="<?php esc_attr_e( '移动端导航', 'qiling' ); ?>">
            <?php
            if ( $menu_id ) {
                wp_nav_menu(
                    array(
                        'menu'        => $menu_id,
                        'menu_id'     => 'mobile-nav-menu',
                        'menu_class'  => 'mobile-nav-menu menu',
                        'container'   => false,
                        'depth'       => 3,
                        'fallback_cb' => false,
                    )
                );
            } else {
                $fallback_items = '<li class="menu-item menu-item-home"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( '首页', 'qiling' ) . '</a></li>';
                $fallback_pages = wp_list_pages(
                    array(
                        'depth'    => 2,
                        'echo'     => false,
                        'title_li' => '',
                    )
                );

                if ( $fallback_pages ) {
                    $fallback_pages = str_replace( 'class="children"', 'class="children sub-menu"', $fallback_pages );
                    $fallback_pages = preg_replace( '/\bpage_item_has_children\b/', 'page_item_has_children menu-item-has-children', $fallback_pages );
                    $fallback_pages = preg_replace( '/\bpage_item\b/', 'page_item menu-item', $fallback_pages );
                    $fallback_items .= $fallback_pages;
                }

                echo '<ul id="mobile-nav-menu" class="mobile-nav-menu menu mobile-nav-menu--fallback">' . wp_kses_post( $fallback_items ) . '</ul>';
            }
            ?>
        </nav>

        <?php if ( $qrcode_enable && '' !== $qrcode_img ) : ?>
            <div class="mobile-drawer-qrcode-card">
                <div class="mobile-qrcode-img-wrap">
                    <img src="<?php echo esc_url( $qrcode_img ); ?>" alt="<?php echo esc_attr( $qrcode_title ); ?>" class="mobile-qrcode-img" loading="lazy" />
                </div>
                <div class="mobile-qrcode-meta">
                    <div class="mobile-qrcode-title"><?php echo esc_html( $qrcode_title ); ?></div>
                    <div class="mobile-qrcode-desc"><?php echo esc_html( $qrcode_desc ); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $cta_enable && '' !== $cta_link ) : ?>
            <div class="mobile-drawer-cta-wrap">
                <a href="<?php echo esc_url( $cta_link ); ?>" class="mobile-drawer-cta-btn mobile-drawer-cta--<?php echo esc_attr( $cta_style ); ?>">
                    <span><?php echo esc_html( $cta_text ); ?></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="mobile-menu-footer">
        <?php if ( $show_phone && $phone && $phone_tel ) : ?>
            <a href="tel:<?php echo esc_attr( $phone_tel ); ?>" class="mobile-phone-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                <span><?php echo esc_html( $phone ); ?></span>
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-phone-btn mobile-home-btn">
                <?php esc_html_e( '返回首页', 'qiling' ); ?>
            </a>
        <?php endif; ?>

        <?php if ( $show_tools ) : ?>
            <div class="mobile-drawer-tools">
                <button type="button" class="mobile-tool-btn darkmode-toggle theme-toggle-btn" aria-label="<?php esc_attr_e( '切换夜间模式', 'qiling' ); ?>" title="<?php esc_attr_e( '切换夜间模式', 'qiling' ); ?>">
                    <span class="icon-sun" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </span>
                    <span class="icon-moon" style="display:none;" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="mobile-menu-overlay" id="mobile-menu-overlay" aria-hidden="true"></div>
