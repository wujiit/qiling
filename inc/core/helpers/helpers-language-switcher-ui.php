<?php
/**
 * Language switcher UI helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function developer_starter_output_translate_modal() {
    if ( ! function_exists( 'developer_starter_get_frontend_language_switcher_enabled' ) || ! developer_starter_get_frontend_language_switcher_enabled() ) {
        return;
    }

    $mode = function_exists( 'developer_starter_get_frontend_language_switch_mode' )
        ? developer_starter_get_frontend_language_switch_mode()
        : '';
    if ( '' === $mode ) {
        return;
    }
    $items = array();

    if ( function_exists( 'developer_starter_get_multilingual_switcher_items' ) ) {
        $multilingual_items = developer_starter_get_multilingual_switcher_items();
        if ( ! empty( $multilingual_items ) && 'multilingual_content' === $mode ) {
            $mode  = 'multilingual_content';
            $items = $multilingual_items;
        }
    }

    if ( empty( $items ) ) {
        $translate_languages = developer_starter_get_option( 'translate_languages', array() );

        foreach ( (array) $translate_languages as $lang ) {
            if ( ! is_array( $lang ) || empty( $lang['name'] ) || empty( $lang['code'] ) ) {
                continue;
            }

            $items[] = array(
                'name'   => (string) $lang['name'],
                'code'   => (string) $lang['code'],
                'icon'   => isset( $lang['icon'] ) ? (string) $lang['icon'] : '',
                'url'    => '#',
                'active' => false,
            );
        }
    }

    if ( empty( $items ) ) {
        return;
    }
    ?>
    <!-- 语言切换弹窗 - Apple风格 -->
    <div class="translate-modal-overlay" id="translate-modal-overlay"></div>
    <div class="translate-modal" id="translate-modal">
        <div class="translate-modal-header">
            <h3><?php esc_html_e( '选择语言', 'developer-starter' ); ?></h3>
            <button type="button" class="translate-modal-close" id="translate-modal-close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="translate-modal-body">
            <div class="translate-lang-grid">
                <?php foreach ( $items as $lang ) : ?>
                    <?php
                    $item_classes = 'translate-lang-item';
                    if ( ! empty( $lang['active'] ) ) {
                        $item_classes .= ' active';
                    }
                    $item_url      = isset( $lang['url'] ) ? (string) $lang['url'] : '#';
                    $item_url_attr = esc_url( $item_url );
                    $switch_attr   = '';
                    if ( 'multilingual_content' === $mode && '' !== $item_url && '#' !== $item_url ) {
                        $switch_attr = ' data-switch-url="' . esc_attr( $item_url ) . '" onclick="window.location.href=this.getAttribute(\'data-switch-url\');return false;"';
                    }
                    ?>
                    <a class="<?php echo esc_attr( $item_classes ); ?>" data-lang="<?php echo esc_attr( $lang['code'] ); ?>" data-mode="<?php echo esc_attr( $mode ); ?>" data-no-lang-rewrite="1" href="<?php echo $item_url_attr; ?>"<?php echo $switch_attr; ?>>
                        <?php if ( ! empty( $lang['icon'] ) ) : ?>
                            <?php if ( strpos( $lang['icon'], 'http' ) === 0 ) : ?>
                                <img src="<?php echo esc_url( $lang['icon'] ); ?>" alt="" class="lang-icon" loading="lazy" decoding="async" />
                            <?php else : ?>
                                <span class="lang-icon-emoji"><?php echo esc_html( developer_starter_country_to_flag( $lang['icon'] ) ); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <span class="lang-name"><?php echo esc_html( $lang['name'] ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'developer_starter_output_translate_modal' );
