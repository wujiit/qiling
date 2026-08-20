<?php
/**
 * Header primary navigation.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args            = is_array( $args ) ? $args : array();
$primary_menu_id = isset( $args['primary_menu_id'] ) ? (int) $args['primary_menu_id'] : 0;
?>
<nav id="site-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( '主导航', 'developer-starter' ); ?>">
    <?php
    if ( $primary_menu_id ) {
        $menu_args = array(
            'theme_location' => 'primary',
            'menu'           => $primary_menu_id,
            'menu_id'        => 'primary-menu',
            'menu_class'     => 'primary-menu menu',
            'container'      => false,
            'fallback_cb'    => false,
        );

        $mega_manager_class      = 'Developer_Starter\\Core\\Mega_Menu_Manager';
        $should_use_mega_walker = false;
        if ( class_exists( $mega_manager_class ) && method_exists( $mega_manager_class, 'has_active_mega_menu_for_primary' ) ) {
            $should_use_mega_walker = (bool) call_user_func( array( $mega_manager_class, 'has_active_mega_menu_for_primary' ) );
        }

        if ( $should_use_mega_walker && class_exists( 'Developer_Starter\\Core\\Walker_Nav_Menu_Mega' ) ) {
            $menu_args['walker'] = new Developer_Starter\Core\Walker_Nav_Menu_Mega();
        }

        wp_nav_menu( $menu_args );
    } else {
        $fallback_items = '<li class="menu-item menu-item-home"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( '首页', 'developer-starter' ) . '</a></li>';
        $fallback_pages = wp_list_pages(
            array(
                'depth'    => 1,
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

        echo '<ul id="primary-menu" class="primary-menu menu primary-menu--fallback">' . wp_kses_post( $fallback_items ) . '</ul>';
    }
    ?>
</nav>
