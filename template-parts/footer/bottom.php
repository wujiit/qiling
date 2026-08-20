<?php
/**
 * Footer bottom bar.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="footer-bottom footer-section footer-section--bottom">
    <div class="container">
        <div class="footer-bottom-content">
            <div class="footer-copyright">
                <?php
                $copyright = developer_starter_get_option( 'footer_copyright', '' );
                if ( $copyright ) {
                    $copyright_output = (string) $copyright;
                    if ( function_exists( 'developer_starter_translate_theme_option_text' ) ) {
                        $copyright_output = developer_starter_translate_theme_option_text( $copyright_output );
                    }
                    echo wp_kses_post( $copyright_output );
                } else {
                    echo '&copy; ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '. ' . esc_html__( '版权所有', 'developer-starter' ) . '.';
                }
                ?>
            </div>

            <div class="footer-filing">
                <?php \Developer_Starter\China\China_Features::render_footer_filings(); ?>
            </div>
        </div>
    </div>
</div>
