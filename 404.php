<?php
/**
 * 404 页面模板
 *
 * @package Developer_Starter
 */

get_header();

$get_error_404_option = function( $key, $default = '' ) {
    return developer_starter_get_option( $key, $default );
};

$get_error_404_text = function( $key, $default = '' ) use ( $get_error_404_option ) {
    $value = trim( (string) $get_error_404_option( $key, '' ) );
    return '' !== $value ? $value : $default;
};

$sanitize_error_404_color = function( $value, $fallback ) {
    $value = trim( (string) $value );
    if ( function_exists( 'sanitize_hex_color' ) ) {
        $color = sanitize_hex_color( $value );
        if ( is_string( $color ) && '' !== $color ) {
            return $color;
        }
    } elseif ( preg_match( '/^#(?:[0-9A-Fa-f]{3}){1,2}$/', $value ) ) {
        return $value;
    }

    return $fallback;
};

$custom_404_rendered = false;
if ( $get_error_404_option( 'error_404_builder_enable', '' ) && function_exists( 'developer_starter_render_builder_template_page' ) ) {
    $custom_404_rendered = developer_starter_render_builder_template_page(
        absint( $get_error_404_option( 'error_404_builder_page_id', '' ) ),
        array(
            'context'       => '404',
            'wrapper_class' => 'error-404-builder-template',
        )
    );
}

$error_404_preset = sanitize_key( (string) $get_error_404_option( 'error_404_preset', 'guide' ) );
if ( ! in_array( $error_404_preset, array( 'guide', 'clean', 'bold', 'image' ), true ) ) {
    $error_404_preset = 'guide';
}

$error_404_code            = $get_error_404_text( 'error_404_code', '404' );
$error_404_title           = $get_error_404_text( 'error_404_title', __( '页面未找到', 'developer-starter' ) );
$error_404_description     = $get_error_404_text( 'error_404_description', __( '抱歉，您访问的页面不存在或已被移除。请检查网址是否正确，或返回首页继续浏览。', 'developer-starter' ) );
$error_404_primary_label   = $get_error_404_text( 'error_404_primary_label', __( '返回首页', 'developer-starter' ) );
$error_404_secondary_label = $get_error_404_text( 'error_404_secondary_label', __( '返回上页', 'developer-starter' ) );
$error_404_search_hint     = $get_error_404_text( 'error_404_search_hint', __( '也许您可以试试搜索：', 'developer-starter' ) );
$error_404_show_back       = '1' === (string) $get_error_404_option( 'error_404_back_enable', '1' );
$error_404_show_search     = '1' === (string) $get_error_404_option( 'error_404_search_enable', '1' );
$error_404_background      = $sanitize_error_404_color( $get_error_404_option( 'error_404_background_color', '#f8fafc' ), '#f8fafc' );
$error_404_accent          = $sanitize_error_404_color( $get_error_404_option( 'error_404_accent_color', '#2563eb' ), '#2563eb' );
$error_404_background_img  = trim( (string) $get_error_404_option( 'error_404_background_image', '' ) );
$error_404_home_url        = home_url( '/' );
$error_404_search_action   = function_exists( 'developer_starter_get_search_form_action_url' ) ? developer_starter_get_search_form_action_url() : home_url( '/' );

if ( '' !== $error_404_background_img ) {
    $error_404_background_img = function_exists( 'developer_starter_get_media_url' )
        ? developer_starter_get_media_url( $error_404_background_img )
        : ( function_exists( 'developer_starter_normalize_asset_url' ) ? developer_starter_normalize_asset_url( $error_404_background_img ) : $error_404_background_img );
}

$error_404_classes = array(
    'error-404-page',
    'error-404-page--' . sanitize_html_class( $error_404_preset ),
);

$error_404_styles = array(
    '--error-404-bg: ' . $error_404_background,
    '--error-404-accent: ' . $error_404_accent,
);

if ( '' !== $error_404_background_img ) {
    $error_404_classes[] = 'has-background-image';
    $error_404_bg_css    = str_replace( array( '"', "'", '(', ')', '\\', ';', '{', '}' ), '', esc_url_raw( $error_404_background_img ) );
    if ( '' !== $error_404_bg_css ) {
        $error_404_styles[] = '--error-404-bg-image: url("' . $error_404_bg_css . '")';
    }
}
?>

<?php if ( ! $custom_404_rendered ) : ?>
    <section class="<?php echo esc_attr( implode( ' ', $error_404_classes ) ); ?>" style="<?php echo esc_attr( implode( '; ', $error_404_styles ) ); ?>" aria-labelledby="error-404-title">
        <div class="container">
            <div class="error-404-layout">
                <div class="error-404-copy">
                    <span class="error-404-kicker"><?php esc_html_e( '访问中断', 'developer-starter' ); ?></span>

                    <div class="error-code" aria-hidden="true">
                        <?php echo esc_html( $error_404_code ); ?>
                    </div>

                    <h1 class="error-404-title" id="error-404-title"><?php echo esc_html( $error_404_title ); ?></h1>

                    <p class="error-404-description">
                        <?php echo esc_html( $error_404_description ); ?>
                    </p>

                    <div class="error-404-actions">
                        <a href="<?php echo esc_url( $error_404_home_url ); ?>" class="btn btn-primary"><?php echo esc_html( $error_404_primary_label ); ?></a>
                        <?php if ( $error_404_show_back ) : ?>
                            <button type="button" class="btn btn-outline" data-qiling-404-back="1" data-fallback-url="<?php echo esc_url( $error_404_home_url ); ?>"><?php echo esc_html( $error_404_secondary_label ); ?></button>
                        <?php endif; ?>
                    </div>

                    <?php if ( $error_404_show_search ) : ?>
                        <div class="error-404-search">
                            <p class="error-404-hint"><?php echo esc_html( $error_404_search_hint ); ?></p>
                            <form role="search" method="get" action="<?php echo esc_url( $error_404_search_action ); ?>"<?php if ( developer_starter_get_option( 'search_rewrite', '' ) ) : ?> onsubmit="return dsSearchRedirect(this);"<?php endif; ?> class="error-404-search-form qiling-search-enhanced" data-qiling-search-form="1">
                                <div class="error-404-search-fields">
                                    <label class="screen-reader-text" for="error-404-search-input"><?php esc_html_e( '搜索关键词', 'developer-starter' ); ?></label>
                                    <input id="error-404-search-input" type="search" name="s" placeholder="<?php esc_attr_e( '输入关键词搜索...', 'developer-starter' ); ?>" class="error-404-search-input" autocomplete="off" data-qiling-search-input="1" />
                                    <input type="hidden" name="qiling_search_mode" value="<?php echo esc_attr( function_exists( 'developer_starter_get_search_mode_form_value' ) ? developer_starter_get_search_mode_form_value() : 'all' ); ?>" />
                                    <button type="submit" class="btn btn-primary"><?php esc_html_e( '搜索', 'developer-starter' ); ?></button>
                                </div>
                                <div class="search-history" data-qiling-search-history></div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="error-404-panel" aria-hidden="true">
                    <div class="error-404-panel__bar"><span></span></div>
                    <div class="error-404-map">
                        <div class="error-404-map__row">
                            <span class="error-404-map__mark"></span>
                            <span class="error-404-map__line"></span>
                        </div>
                        <div class="error-404-map__row">
                            <span class="error-404-map__mark"></span>
                            <span class="error-404-map__line error-404-map__line--accent"></span>
                        </div>
                        <div class="error-404-map__row">
                            <span class="error-404-map__mark"></span>
                            <span class="error-404-map__line error-404-map__line--short"></span>
                        </div>
                    </div>
                    <div class="error-404-panel__notice"><?php esc_html_e( '这条路径暂时不可达', 'developer-starter' ); ?></div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>
