<?php
/**
 * Header mobile menu.
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
    return $value;
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

$phone = developer_starter_get_option( 'company_phone', '' );
$phone_tel = preg_replace( '/[^0-9+]/', '', (string) $phone );
?>
<div class="mobile-menu" id="mobile-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( '移动端菜单', 'developer-starter' ); ?>" aria-hidden="true" tabindex="-1">
    <div class="mobile-menu-header">
        <div class="mobile-menu-logo">
            <?php if ( $mobile_menu_logo ) : ?>
                <img src="<?php echo esc_url( $mobile_menu_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="<?php echo esc_attr( (int) $mobile_logo_dims['width'] ); ?>" height="<?php echo esc_attr( (int) $mobile_logo_dims['height'] ); ?>" loading="lazy" decoding="async" />
            <?php elseif ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <span class="site-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
            <?php endif; ?>
        </div>
        <button type="button" class="mobile-menu-close" id="mobile-menu-close" aria-label="<?php esc_attr_e( '关闭菜单', 'developer-starter' ); ?>" aria-controls="mobile-menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <nav class="mobile-menu-nav" aria-label="<?php esc_attr_e( '移动端导航', 'developer-starter' ); ?>">
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
            $fallback_items = '<li class="menu-item menu-item-home"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( '首页', 'developer-starter' ) . '</a></li>';
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
    <div class="mobile-menu-footer">
        <?php if ( $phone && $phone_tel ) : ?>
            <a href="tel:<?php echo esc_attr( $phone_tel ); ?>" class="mobile-phone-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                <?php echo esc_html( $phone ); ?>
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-phone-btn mobile-home-btn">
                <?php esc_html_e( '返回首页', 'developer-starter' ); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
<div class="mobile-menu-overlay" id="mobile-menu-overlay" aria-hidden="true"></div>
