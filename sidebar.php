<?php
/**
 * 侧边栏模板
 *
 * @package Developer_Starter
 */

// 确定使用哪个侧边栏。商店上下文优先，避免 product 单页被 is_single() 误判到文章侧边栏。
$sidebar_id      = 'sidebar-main';
$sidebar_context = 'main';
$is_shop_context = function_exists( 'is_shop' )
    && (
        is_shop()
        || ( function_exists( 'is_product_category' ) && is_product_category() )
        || ( function_exists( 'is_product_tag' ) && is_product_tag() )
        || ( function_exists( 'is_product' ) && is_product() )
    );

if ( $is_shop_context ) {
    $sidebar_id      = 'sidebar-shop';
    $sidebar_context = 'shop';
} elseif ( is_singular( 'post' ) ) {
    $sidebar_id      = 'sidebar-post';
    $sidebar_context = 'post';
} elseif ( is_page() ) {
    $sidebar_id      = 'sidebar-page';
    $sidebar_context = 'page';
}

$sidebar_id = (string) apply_filters( 'developer_starter_sidebar_id', $sidebar_id, $sidebar_context );

if ( ! is_active_sidebar( $sidebar_id ) ) {
    return;
}

$sidebar_labels = array(
    'sidebar-main' => __( '主侧边栏', 'developer-starter' ),
    'sidebar-post' => __( '文章侧边栏', 'developer-starter' ),
    'sidebar-page' => __( '页面侧边栏', 'developer-starter' ),
    'sidebar-shop' => __( '商店侧边栏', 'developer-starter' ),
);
$sidebar_label = isset( $sidebar_labels[ $sidebar_id ] ) ? $sidebar_labels[ $sidebar_id ] : __( '侧边栏', 'developer-starter' );
$sidebar_classes = array(
    'widget-area',
    'sidebar',
    'sidebar--' . sanitize_html_class( $sidebar_context ),
);
?>

<aside id="secondary" class="<?php echo esc_attr( implode( ' ', $sidebar_classes ) ); ?>" role="complementary" aria-label="<?php echo esc_attr( $sidebar_label ); ?>" data-sidebar-id="<?php echo esc_attr( $sidebar_id ); ?>">
    <?php dynamic_sidebar( $sidebar_id ); ?>
</aside>
