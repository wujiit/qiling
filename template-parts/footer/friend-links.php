<?php
/**
 * Footer friend links.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$friend_links_enable = developer_starter_get_option( 'friend_links_enable', '' );
$friend_links        = developer_starter_get_option( 'friend_links', array() );

if ( ! $friend_links_enable || ! is_front_page() || empty( $friend_links ) || ! is_array( $friend_links ) ) {
    return;
}
?>
<div class="footer-friend-links footer-section footer-section--friend-links">
    <div class="container">
        <div class="footer-friend-links-row">
            <span class="footer-friend-links-label"><?php esc_html_e( '友情链接：', 'developer-starter' ); ?></span>
            <?php
            foreach ( $friend_links as $link ) :
                $text = isset( $link['text'] ) ? $link['text'] : '';
                $url  = isset( $link['url'] ) ? $link['url'] : '#';
                if ( $text && function_exists( 'developer_starter_translate_theme_option_text' ) ) {
                    $text = developer_starter_translate_theme_option_text( $text );
                }
                if ( $text ) :
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="external nofollow noopener noreferrer" class="footer-friend-link"><?php echo esc_html( $text ); ?></a>
                    <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>
</div>
