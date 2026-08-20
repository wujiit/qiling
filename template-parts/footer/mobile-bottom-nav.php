<?php
/**
 * Mobile bottom navigation.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! has_nav_menu( 'mobile_bottom' ) ) {
    return;
}

$label_mode = developer_starter_get_option( 'mobile_bottom_label_mode', 'icon_text' );
$label_mode = in_array( $label_mode, array( 'icon_text', 'icon_only', 'text_only' ), true ) ? $label_mode : 'icon_text';

$recommended_items = (string) developer_starter_get_option( 'mobile_bottom_recommended_items', '5' );
$recommended_items = in_array( $recommended_items, array( '3', '4', '5' ), true ) ? $recommended_items : '5';

$locations  = get_nav_menu_locations();
$menu_id    = isset( $locations['mobile_bottom'] ) ? (int) $locations['mobile_bottom'] : 0;
$item_count = 0;

if ( $menu_id > 0 ) {
    $menu_items = wp_get_nav_menu_items(
        $menu_id,
        array(
            'update_post_term_cache' => false,
        )
    );

    if ( is_array( $menu_items ) ) {
        foreach ( $menu_items as $menu_item ) {
            if ( isset( $menu_item->menu_item_parent ) && '0' === (string) $menu_item->menu_item_parent ) {
                $item_count++;
            }
        }
    }
}

$nav_classes = array(
    'mobile-bottom-nav',
    'mobile-bottom-nav--' . sanitize_html_class( $label_mode ),
    'mobile-bottom-nav-count-' . min( max( $item_count, 0 ), 9 ),
);

if ( $item_count > (int) $recommended_items ) {
    $nav_classes[] = 'is-over-recommended-items';
}

$menu_classes = array(
    'mobile-bottom-menu',
    'mobile-bottom-menu--' . sanitize_html_class( $label_mode ),
);
?>
<div class="<?php echo esc_attr( implode( ' ', $nav_classes ) ); ?>" data-item-count="<?php echo esc_attr( (string) $item_count ); ?>" data-recommended-items="<?php echo esc_attr( $recommended_items ); ?>">
    <?php
    wp_nav_menu(
        array(
            'theme_location' => 'mobile_bottom',
            'menu_class'     => implode( ' ', $menu_classes ),
            'container'      => false,
            'depth'          => 1,
            'fallback_cb'    => false,
        )
    );
    ?>
</div>
