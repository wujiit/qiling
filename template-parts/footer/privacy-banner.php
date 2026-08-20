<?php
/**
 * Privacy/Cookie banner.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$privacy_banner_enable = developer_starter_get_option( 'privacy_banner_enable', '' );
if ( ! $privacy_banner_enable ) {
    return;
}

$privacy_text         = developer_starter_get_option( 'privacy_banner_text', __( '本网站使用Cookie和类似技术来提升您的体验。继续使用本网站即表示您同意我们的隐私政策。', 'developer-starter' ) );
$privacy_link_text    = developer_starter_get_option( 'privacy_banner_link_text', __( '了解更多', 'developer-starter' ) );
$privacy_link_url     = developer_starter_get_option( 'privacy_banner_link_url', '' );
$privacy_btn_text     = developer_starter_get_option( 'privacy_banner_btn_text', __( '全部接受', 'developer-starter' ) );
$privacy_decline_text = developer_starter_get_option( 'privacy_banner_decline_text', '' );
$privacy_bg           = developer_starter_get_option( 'privacy_banner_bg', 'var(--color-neutral-800)' );
$privacy_text_color   = developer_starter_get_option( 'privacy_banner_text_color', 'var(--color-neutral-0)' );
?>
<div id="privacy-banner" class="privacy-banner" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999; background: <?php echo esc_attr( $privacy_bg ); ?>; color: <?php echo esc_attr( $privacy_text_color ); ?>; padding: var(--qiling-space-15) var(--qiling-space-20); box-shadow: 0 -4px 20px rgba(var(--qiling-rgb-0-0-0), 0.15);">
    <div class="container" style="max-width: var(--qiling-measure-1200); margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: var(--qiling-space-20); flex-wrap: wrap;">
        <div class="privacy-banner-content" style="flex: 1; min-width: var(--qiling-measure-300);">
            <p style="margin: 0; font-size: var(--qiling-text-rem-0p95); line-height: 1.6;">
                🍪 <?php echo esc_html( $privacy_text ); ?>
                <?php if ( $privacy_link_url ) : ?>
                    <a href="<?php echo esc_url( $privacy_link_url ); ?>" style="color: <?php echo esc_attr( $privacy_text_color ); ?>; text-decoration: underline; margin-left: var(--qiling-space-5);" target="_blank"><?php echo esc_html( $privacy_link_text ); ?></a>
                <?php endif; ?>
            </p>
        </div>
        <div class="privacy-banner-actions" style="display: flex; gap: var(--qiling-space-10); flex-shrink: 0;">
            <?php if ( $privacy_decline_text ) : ?>
                <button type="button" id="privacy-decline" style="padding: var(--qiling-space-10) var(--qiling-space-24); background: transparent; color: <?php echo esc_attr( $privacy_text_color ); ?>; border: 2px solid rgba(var(--qiling-rgb-255-255-255), 0.3); border-radius: 8px; font-size: var(--qiling-text-rem-0p9); font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    <?php echo esc_html( $privacy_decline_text ); ?>
                </button>
            <?php endif; ?>
            <button type="button" id="privacy-accept" style="padding: var(--qiling-space-10) var(--qiling-space-24); background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-violet-600) 100%); color: var(--color-neutral-0); border: none; border-radius: 8px; font-size: var(--qiling-text-rem-0p9); font-weight: 600; cursor: pointer; transition: all 0.3s;">
                <?php echo esc_html( $privacy_btn_text ); ?>
            </button>
        </div>
    </div>
</div>
<style>
#privacy-banner button:hover {
    transform: translateY(-2px);
}
#privacy-banner #privacy-accept:hover {
    box-shadow: 0 5px 15px rgba(var(--color-primary-rgb), 0.4);
}
#privacy-banner #privacy-decline:hover {
    background: rgba(var(--qiling-rgb-255-255-255), 0.1);
    border-color: rgba(var(--qiling-rgb-255-255-255), 0.5);
}
@media (max-width: 768px) {
    #privacy-banner .container {
        flex-direction: column;
        text-align: center;
    }
    #privacy-banner .privacy-banner-content {
        min-width: auto;
    }
    #privacy-banner .privacy-banner-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>
<script>
(function() {
    var banner = document.getElementById('privacy-banner');
    var acceptBtn = document.getElementById('privacy-accept');
    var declineBtn = document.getElementById('privacy-decline');
    var storageKey = 'ds_privacy_consent';

    if (!banner) return;

    var consent = localStorage.getItem(storageKey);
    if (!consent) {
        banner.style.display = 'block';
    }

    function hideBanner() {
        banner.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
        banner.style.transform = 'translateY(100%)';
        banner.style.opacity = '0';
        setTimeout(function() {
            banner.style.display = 'none';
        }, 300);
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            localStorage.setItem(storageKey, 'all');
            hideBanner();
        });
    }

    if (declineBtn) {
        declineBtn.addEventListener('click', function() {
            localStorage.setItem(storageKey, 'essential');
            hideBanner();
        });
    }
})();
</script>
