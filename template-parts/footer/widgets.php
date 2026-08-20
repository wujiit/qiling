<?php
/**
 * Footer widget columns.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'effect_enabled'       => false,
        'footer_about_title'   => __( '关于我们', 'developer-starter' ),
        'footer_links_title'   => __( '快速链接', 'developer-starter' ),
        'footer_contact_title' => __( '联系方式', 'developer-starter' ),
        'footer_follow_title'  => __( '关注我们', 'developer-starter' ),
        'company_brief'        => '',
        'footer_section_visibility' => array(),
        'effect_scope'         => 'main',
    )
);

$footer_section_visibility = is_array( $args['footer_section_visibility'] ) ? $args['footer_section_visibility'] : array();
$show_about                = array_key_exists( 'about', $footer_section_visibility ) ? ! empty( $footer_section_visibility['about'] ) : true;
$show_links                = array_key_exists( 'links', $footer_section_visibility ) ? ! empty( $footer_section_visibility['links'] ) : true;
$show_contact              = array_key_exists( 'contact', $footer_section_visibility ) ? ! empty( $footer_section_visibility['contact'] ) : true;
$show_follow               = array_key_exists( 'follow', $footer_section_visibility ) ? ! empty( $footer_section_visibility['follow'] ) : true;
$visible_footer_columns    = count( array_filter( array( $show_about, $show_links, $show_contact, $show_follow ) ) );

if ( $visible_footer_columns <= 0 ) {
    return;
}

$footer_grid_classes = array(
    'footer-widgets-grid',
    'footer-widgets-grid--columns-' . $visible_footer_columns,
);
?>
<div class="footer-widgets footer-section footer-section--main">
    <?php if ( ! empty( $args['effect_enabled'] ) && 'main' === sanitize_key( (string) $args['effect_scope'] ) ) : ?>
        <canvas id="footer-effect-canvas" class="footer-effect-canvas footer-effect-canvas--main"></canvas>
    <?php endif; ?>
    <div class="container footer-widgets-inner">
        <div class="<?php echo esc_attr( implode( ' ', $footer_grid_classes ) ); ?>" data-footer-columns="<?php echo esc_attr( (string) $visible_footer_columns ); ?>">
            <?php if ( $show_about ) : ?>
            <div class="footer-widget-area">
                <h3><?php echo esc_html( (string) $args['footer_about_title'] ); ?></h3>
                <div class="footer-widget-text">
                    <?php echo wp_kses_post( (string) $args['company_brief'] ); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( $show_links ) : ?>
            <div class="footer-widget-area">
                <h3><?php echo esc_html( (string) $args['footer_links_title'] ); ?></h3>
                <?php
                if ( is_active_sidebar( 'footer-quick-links' ) ) {
                    echo '<div class="footer-quick-links-widgets">';
                    dynamic_sidebar( 'footer-quick-links' );
                    echo '</div>';
                } elseif ( has_nav_menu( 'footer' ) ) {
                    wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false ) );
                } else {
                    $quick_links = developer_starter_get_option( 'footer_quick_links', array() );
                    if ( ! empty( $quick_links ) && is_array( $quick_links ) ) {
                        echo '<ul class="footer-links">';
                        foreach ( $quick_links as $link ) {
                            $text = isset( $link['text'] ) ? $link['text'] : '';
                            $url  = isset( $link['url'] ) ? $link['url'] : '#';
                            if ( $text && function_exists( 'developer_starter_translate_theme_option_text' ) ) {
                                $text = developer_starter_translate_theme_option_text( $text );
                            }
                            if ( $text ) {
                                echo '<li class="footer-link-item"><a href="' . esc_url( $url ) . '" class="footer-contact-link" target="_blank">' . esc_html( $text ) . '</a></li>';
                            }
                        }
                        echo '</ul>';
                    }
                }
                ?>
            </div>
            <?php endif; ?>

            <?php if ( $show_contact ) : ?>
            <div class="footer-widget-area">
                <h3><?php echo esc_html( (string) $args['footer_contact_title'] ); ?></h3>
                <?php
                $phone         = developer_starter_get_option( 'company_phone', '' );
                $qq            = developer_starter_get_option( 'company_qq', '' );
                $qq_link       = function_exists( 'developer_starter_get_qq_contact_link' ) ? developer_starter_get_qq_contact_link( $qq ) : '';
                $wechat_qrcode = developer_starter_get_option( 'company_wechat_qrcode', '' );
                $email         = developer_starter_get_option( 'company_email', '' );
                $address       = developer_starter_get_option( 'company_address', '' );
                $working_hours = developer_starter_get_option( 'company_working_hours', '' );
                ?>
                <div class="footer-contact-info">
                    <?php if ( $phone ) : ?>
                        <p class="footer-contact-item">
                            <span class="footer-contact-icon">📞</span><?php echo esc_html( $phone ); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $qq ) : ?>
                        <p class="footer-contact-item">
                            <span class="footer-contact-icon">🗨️</span>
                            <?php if ( $qq_link ) : ?>
                                <a href="<?php echo esc_url( $qq_link ); ?>" target="_blank" rel="noopener noreferrer" class="footer-contact-link"><?php echo esc_html( $qq ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $qq ); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $email ) : ?>
                        <p class="footer-contact-item">
                            <span class="footer-contact-icon">📧</span><?php echo esc_html( $email ); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $address ) : ?>
                        <p class="footer-contact-item footer-contact-item-address">
                            <span class="footer-contact-icon">📍</span><?php echo esc_html( $address ); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $working_hours ) : ?>
                        <p class="footer-contact-item">
                            <span class="footer-contact-icon">🕐</span><?php echo esc_html( $working_hours ); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $wechat_qrcode ) : ?>
                        <div class="footer-contact-qr-card">
                            <img src="<?php echo esc_url( $wechat_qrcode ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" loading="lazy" decoding="async" />
                            <span><?php esc_html_e( '扫码添加微信', 'developer-starter' ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ( is_active_sidebar( 'footer-contact' ) ) : ?>
                    <div class="footer-contact-widgets">
                        <?php dynamic_sidebar( 'footer-contact' ); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( $show_follow ) : ?>
            <div class="footer-widget-area">
                <h3><?php echo esc_html( (string) $args['footer_follow_title'] ); ?></h3>
                <?php
                $wechat_qr   = developer_starter_get_option( 'wechat_qrcode', '' );
                $wechat_text = developer_starter_get_option( 'wechat_qr_text', __( '扫码关注公众号', 'developer-starter' ) );
                $douyin_qr   = developer_starter_get_option( 'douyin_qrcode', '' );
                $douyin_text = developer_starter_get_option( 'douyin_qr_text', __( '扫码关注抖音', 'developer-starter' ) );
                if ( function_exists( 'developer_starter_translate_theme_option_text' ) ) {
                    $wechat_text = developer_starter_translate_theme_option_text( (string) $wechat_text );
                    $douyin_text = developer_starter_translate_theme_option_text( (string) $douyin_text );
                }
                ?>
                <div class="qrcode-grid">
                    <?php if ( $wechat_qr ) : ?>
                        <div class="qrcode-item">
                            <img src="<?php echo esc_url( $wechat_qr ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" loading="lazy" decoding="async" class="qrcode-image" />
                            <?php if ( $wechat_text ) : ?>
                                <p class="qrcode-caption"><?php echo esc_html( $wechat_text ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $douyin_qr ) : ?>
                        <div class="qrcode-item">
                            <img src="<?php echo esc_url( $douyin_qr ); ?>" alt="<?php esc_attr_e( '抖音二维码', 'developer-starter' ); ?>" loading="lazy" decoding="async" class="qrcode-image" />
                            <?php if ( $douyin_text ) : ?>
                                <p class="qrcode-caption"><?php echo esc_html( $douyin_text ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
