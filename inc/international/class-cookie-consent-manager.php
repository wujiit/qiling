<?php
/**
 * Cookie consent manager for international basics.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\International;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Controls the lightweight international Cookie notice and gated snippets.
 */
class Cookie_Consent_Manager {

    /**
     * Cookie notice enable option.
     */
    const OPTION_ENABLE = 'international_cookie_notice_enable';

    /**
     * Browser cookie name.
     */
    const COOKIE_NAME = 'qiling_international_cookie_consent';

    /**
     * Consent payload version.
     */
    const CONSENT_VERSION = '2.0';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_notice' ), 19 );
        add_action( 'developer_starter_after_site_footer', array( $this, 'render_footer_settings_button' ), 25 );
        add_shortcode( 'qiling_cookie_settings', array( $this, 'render_settings_button_shortcode' ) );
        add_shortcode( 'qiling_cookie_consent_settings', array( $this, 'render_settings_button_shortcode' ) );
        add_filter( 'developer_starter_international_third_party_code_allowed', array( $this, 'filter_third_party_code_allowed' ), 10, 5 );
        add_filter( 'developer_starter_international_third_party_code_should_defer', array( $this, 'filter_third_party_code_should_defer' ), 10, 6 );
    }

    /**
     * Get supported consent categories.
     *
     * @return array<string,array{label:string,description:string,required:bool}>
     */
    public static function get_categories() {
        return array(
            'necessary'   => array(
                'label'       => __( '必要', 'developer-starter' ),
                'description' => __( '用于网站安全、基础功能和偏好保存，始终启用。', 'developer-starter' ),
                'required'    => true,
            ),
            'statistics'  => array(
                'label'       => __( '统计', 'developer-starter' ),
                'description' => __( '帮助了解访问表现，例如 GA4、Clarity 或其他统计代码。', 'developer-starter' ),
                'required'    => false,
            ),
            'marketing'   => array(
                'label'       => __( '营销', 'developer-starter' ),
                'description' => __( '用于营销自动化、转化分析和客户互动工具。', 'developer-starter' ),
                'required'    => false,
            ),
            'advertising' => array(
                'label'       => __( '广告', 'developer-starter' ),
                'description' => __( '用于广告追踪、再营销和投放转化，例如 Meta Pixel。', 'developer-starter' ),
                'required'    => false,
            ),
            'custom'      => array(
                'label'       => __( '自定义', 'developer-starter' ),
                'description' => __( '用于其他第三方代码，请按实际用途谨慎开启。', 'developer-starter' ),
                'required'    => false,
            ),
        );
    }

    /**
     * Normalize a category key.
     *
     * @param string $category Raw category.
     * @param string $default Default category.
     * @return string
     */
    public static function normalize_category( $category, $default = 'custom' ) {
        $category = sanitize_key( (string) $category );
        $default  = sanitize_key( (string) $default );
        $allowed  = array_keys( self::get_categories() );

        if ( in_array( $category, $allowed, true ) ) {
            return $category;
        }

        return in_array( $default, $allowed, true ) ? $default : 'custom';
    }

    /**
     * Whether the international Cookie notice is enabled.
     *
     * @return bool
     */
    public function is_enabled() {
        return '1' === (string) $this->get_option( self::OPTION_ENABLE, '' );
    }

    /**
     * Enqueue frontend assets.
     *
     * @return void
     */
    public function enqueue_assets() {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $css_file = DEVELOPER_STARTER_DIR . '/assets/css/international-cookie-consent.css';
        $js_file  = DEVELOPER_STARTER_DIR . '/assets/js/international-cookie-consent.js';
        $css_ver  = file_exists( $css_file ) ? (string) filemtime( $css_file ) : DEVELOPER_STARTER_VERSION;
        $js_ver   = file_exists( $js_file ) ? (string) filemtime( $js_file ) : DEVELOPER_STARTER_VERSION;

        wp_enqueue_style(
            'developer-starter-international-cookie-consent',
            DEVELOPER_STARTER_ASSETS . '/css/international-cookie-consent.css',
            array(),
            $css_ver
        );
        wp_enqueue_script(
            'developer-starter-international-cookie-consent',
            DEVELOPER_STARTER_ASSETS . '/js/international-cookie-consent.js',
            array(),
            $js_ver,
            true
        );

        wp_localize_script(
            'developer-starter-international-cookie-consent',
            'qilingInternationalConsent',
            array(
                'cookieName' => self::COOKIE_NAME,
                'version'    => $this->get_consent_version(),
                'regionPreset' => $this->get_region_preset(),
                'defaultOptionalConsent' => $this->should_default_optional_consent(),
                'maxAgeDays' => 180,
                'accepted'   => 'accepted',
                'rejected'   => 'rejected',
                'categories' => $this->get_frontend_categories(),
            )
        );
    }

    /**
     * Render the Cookie notice.
     *
     * @return void
     */
    public function render_notice() {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $message = trim( (string) $this->get_option( 'international_cookie_notice_text', __( '本网站使用 Cookie 和类似技术来提升访问体验。', 'developer-starter' ) ) );
        if ( '' === $message ) {
            $message = __( '本网站使用 Cookie 和类似技术来提升访问体验。', 'developer-starter' );
        }

        $accept_text = trim( (string) $this->get_option( 'international_cookie_accept_text', __( '接受全部', 'developer-starter' ) ) );
        if ( '' === $accept_text ) {
            $accept_text = __( '接受全部', 'developer-starter' );
        }

        $reject_text = trim( (string) $this->get_option( 'international_cookie_reject_text', __( '拒绝非必要', 'developer-starter' ) ) );
        if ( '' === $reject_text ) {
            $reject_text = __( '拒绝非必要', 'developer-starter' );
        }

        $customize_text = trim( (string) $this->get_option( 'international_cookie_customize_text', __( '自定义设置', 'developer-starter' ) ) );
        if ( '' === $customize_text ) {
            $customize_text = __( '自定义设置', 'developer-starter' );
        }

        $save_text = trim( (string) $this->get_option( 'international_cookie_save_text', __( '保存设置', 'developer-starter' ) ) );
        if ( '' === $save_text ) {
            $save_text = __( '保存设置', 'developer-starter' );
        }

        $link_text = trim( (string) $this->get_option( 'international_cookie_policy_link_text', __( '了解更多', 'developer-starter' ) ) );
        $link_url  = trim( (string) $this->get_option( 'international_cookie_policy_url', '' ) );
        $position  = $this->normalize_notice_position( (string) $this->get_option( 'international_cookie_notice_position', 'bottom_center' ) );
        $categories = self::get_categories();
        $has_choice = $this->has_consent_choice();
        ?>
        <div class="qiling-cookie-consent" data-qiling-cookie-consent data-position="<?php echo esc_attr( $position ); ?>" data-consent-version="<?php echo esc_attr( $this->get_consent_version() ); ?>"<?php echo $has_choice ? ' data-hidden="1"' : ''; ?> role="region" aria-label="<?php esc_attr_e( 'Cookie 提示', 'developer-starter' ); ?>">
            <div class="qiling-cookie-consent__main">
                <div class="qiling-cookie-consent__content">
                    <p class="qiling-cookie-consent__text"><?php echo esc_html( $message ); ?></p>
                    <?php if ( '' !== $link_text && '' !== $link_url ) : ?>
                        <a class="qiling-cookie-consent__link" href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link_text ); ?></a>
                    <?php endif; ?>
                </div>
                <div class="qiling-cookie-consent__actions">
                    <button type="button" class="qiling-cookie-consent__button qiling-cookie-consent__button--ghost" data-qiling-cookie-reject><?php echo esc_html( $reject_text ); ?></button>
                    <button type="button" class="qiling-cookie-consent__button qiling-cookie-consent__button--ghost" data-qiling-cookie-customize><?php echo esc_html( $customize_text ); ?></button>
                    <button type="button" class="qiling-cookie-consent__button qiling-cookie-consent__button--primary" data-qiling-cookie-accept><?php echo esc_html( $accept_text ); ?></button>
                </div>
            </div>
            <div class="qiling-cookie-consent__settings" data-qiling-cookie-settings hidden>
                <?php foreach ( $categories as $category_key => $category ) : ?>
                    <?php
                    $is_required = ! empty( $category['required'] );
                    $input_id = 'qiling-cookie-category-' . sanitize_html_class( $category_key );
                    ?>
                    <label class="qiling-cookie-consent__category" for="<?php echo esc_attr( $input_id ); ?>">
                        <span>
                            <strong><?php echo esc_html( (string) $category['label'] ); ?></strong>
                            <em><?php echo esc_html( (string) $category['description'] ); ?></em>
                        </span>
                        <input
                            id="<?php echo esc_attr( $input_id ); ?>"
                            type="checkbox"
                            data-qiling-cookie-category="<?php echo esc_attr( $category_key ); ?>"
                            <?php checked( $is_required ); ?>
                            <?php disabled( $is_required ); ?>
                        />
                    </label>
                <?php endforeach; ?>
                <div class="qiling-cookie-consent__settings-actions">
                    <button type="button" class="qiling-cookie-consent__button qiling-cookie-consent__button--primary" data-qiling-cookie-save><?php echo esc_html( $save_text ); ?></button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the footer Cookie settings button.
     *
     * @return void
     */
    public function render_footer_settings_button() {
        if ( ! $this->is_enabled() || '1' !== (string) $this->get_option( 'international_cookie_footer_button_enable', '' ) ) {
            return;
        }

        $text = trim( (string) $this->get_option( 'international_cookie_footer_button_text', __( 'Cookie 设置', 'developer-starter' ) ) );
        if ( '' === $text ) {
            $text = __( 'Cookie 设置', 'developer-starter' );
        }

        echo '<div class="qiling-cookie-settings-footer" data-qiling-cookie-settings-footer>';
        echo $this->render_settings_button_markup( $text, 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped by renderer.
        echo '</div>';
    }

    /**
     * Shortcode for reopening Cookie settings.
     *
     * @param array<string,mixed>|string $atts Shortcode attributes.
     * @return string
     */
    public function render_settings_button_shortcode( $atts ) {
        if ( ! $this->is_enabled() ) {
            return '';
        }

        $atts = shortcode_atts(
            array(
                'text'  => __( 'Cookie 设置', 'developer-starter' ),
                'class' => '',
            ),
            is_array( $atts ) ? $atts : array(),
            'qiling_cookie_settings'
        );

        $text = trim( (string) $atts['text'] );
        if ( '' === $text ) {
            $text = __( 'Cookie 设置', 'developer-starter' );
        }

        $extra_class = trim( sanitize_html_class( (string) $atts['class'] ) );

        return $this->render_settings_button_markup( $text, 'shortcode', $extra_class );
    }

    /**
     * Build a settings button markup.
     *
     * @param string $text Button text.
     * @param string $context Render context.
     * @param string $extra_class Extra CSS class.
     * @return string
     */
    private function render_settings_button_markup( $text, $context = 'shortcode', $extra_class = '' ) {
        $classes = array(
            'qiling-cookie-settings-button',
            'qiling-cookie-settings-button--' . sanitize_html_class( $context ),
        );
        if ( '' !== $extra_class ) {
            $classes[] = $extra_class;
        }

        return sprintf(
            '<button type="button" class="%1$s" data-qiling-open-cookie-settings aria-haspopup="dialog">%2$s</button>',
            esc_attr( implode( ' ', array_filter( $classes ) ) ),
            esc_html( $text )
        );
    }

    /**
     * Prevent consent-gated snippets from direct output before acceptance.
     *
     * @param bool   $allowed          Current allowed flag.
     * @param string $group_id         Code group id.
     * @param string $position         Output position.
     * @param bool   $requires_consent Whether the snippet requires consent.
     * @param string $category         Consent category.
     * @return bool
     */
    public function filter_third_party_code_allowed( $allowed, $group_id, $position, $requires_consent, $category = 'custom' ) {
        unset( $group_id, $position );

        $category = self::normalize_category( $category, 'custom' );
        if ( ! $allowed || 'necessary' === $category || ! $requires_consent || ! $this->is_enabled() ) {
            return $allowed;
        }

        $consent = $this->get_consent_payload();

        return $this->is_category_allowed( $category, $consent );
    }

    /**
     * Defer consent-gated snippets while the visitor has not chosen yet.
     *
     * @param bool   $defer            Current defer flag.
     * @param string $group_id         Code group id.
     * @param string $position         Output position.
     * @param bool   $requires_consent Whether the snippet requires consent.
     * @param string $code             Snippet HTML.
     * @param string $category         Consent category.
     * @return bool
     */
    public function filter_third_party_code_should_defer( $defer, $group_id, $position, $requires_consent, $code, $category = 'custom' ) {
        unset( $defer, $group_id, $position, $code );

        $category = self::normalize_category( $category, 'custom' );
        if ( 'necessary' === $category || ! $requires_consent || ! $this->is_enabled() ) {
            return false;
        }

        return ! $this->is_category_allowed( $category, $this->get_consent_payload() );
    }

    /**
     * Whether the visitor has already made a consent choice.
     *
     * @return bool
     */
    private function has_consent_choice() {
        return null !== $this->get_consent_payload();
    }

    /**
     * Get current consent payload from browser cookie.
     *
     * @return array<string,mixed>|null
     */
    private function get_consent_payload() {
        $raw = isset( $_COOKIE[ self::COOKIE_NAME ] )
            ? trim( wp_unslash( (string) $_COOKIE[ self::COOKIE_NAME ] ) )
            : '';
        if ( '' === $raw ) {
            return null;
        }

        $legacy_state = sanitize_key( $raw );
        if ( in_array( $legacy_state, array( 'accepted', 'rejected' ), true ) ) {
            return $this->build_legacy_payload( $legacy_state );
        }

        $decoded = json_decode( $raw, true );
        if ( ! is_array( $decoded ) ) {
            return null;
        }

        $categories = isset( $decoded['categories'] ) && is_array( $decoded['categories'] ) ? $decoded['categories'] : array();
        $normalized = array();
        foreach ( array_keys( self::get_categories() ) as $category ) {
            $normalized[ $category ] = 'necessary' === $category ? true : ! empty( $categories[ $category ] );
        }

        $version = isset( $decoded['version'] ) ? sanitize_text_field( (string) $decoded['version'] ) : '';
        if ( $version !== $this->get_consent_version() ) {
            return null;
        }

        return array(
            'version'    => $version,
            'timestamp'  => isset( $decoded['timestamp'] ) ? sanitize_text_field( (string) $decoded['timestamp'] ) : '',
            'categories' => $normalized,
        );
    }

    /**
     * Build a 2.0-shaped payload from old accepted/rejected cookies.
     *
     * @param string $state Legacy accepted/rejected state.
     * @return array<string,mixed>
     */
    private function build_legacy_payload( $state ) {
        $allow_optional = 'accepted' === $state;
        $categories = array();
        foreach ( array_keys( self::get_categories() ) as $category ) {
            $categories[ $category ] = 'necessary' === $category ? true : $allow_optional;
        }

        return array(
            'version'    => $this->get_consent_version(),
            'timestamp'  => '',
            'categories' => $categories,
        );
    }

    /**
     * Whether a category is allowed by a consent payload.
     *
     * @param string                   $category Category key.
     * @param array<string,mixed>|null $consent Consent payload.
     * @return bool
     */
    private function is_category_allowed( $category, $consent ) {
        $category = self::normalize_category( $category, 'custom' );
        if ( 'necessary' === $category ) {
            return true;
        }

        if ( ! is_array( $consent ) || empty( $consent['categories'] ) || ! is_array( $consent['categories'] ) ) {
            return false;
        }

        return ! empty( $consent['categories'][ $category ] );
    }

    /**
     * Build frontend category config.
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_frontend_categories() {
        $categories = array();
        foreach ( self::get_categories() as $key => $category ) {
            $categories[ $key ] = array(
                'label'       => (string) $category['label'],
                'description' => (string) $category['description'],
                'required'    => ! empty( $category['required'] ),
            );
        }

        return $categories;
    }

    /**
     * Get current consent version from theme options.
     *
     * @return string
     */
    private function get_consent_version() {
        $version = preg_replace( '/[^A-Za-z0-9._-]/', '', (string) $this->get_option( 'international_cookie_consent_version', self::CONSENT_VERSION ) );
        $version = is_string( $version ) ? trim( $version ) : '';

        return '' !== $version ? substr( $version, 0, 32 ) : self::CONSENT_VERSION;
    }

    /**
     * Get the configured regional preset.
     *
     * @return string
     */
    private function get_region_preset() {
        $preset = sanitize_key( (string) $this->get_option( 'international_cookie_region_preset', 'cross_border' ) );
        $allowed = array( 'cn', 'eu', 'uk', 'us', 'cross_border' );

        return in_array( $preset, $allowed, true ) ? $preset : 'cross_border';
    }

    /**
     * Whether optional categories should be preselected in the custom panel.
     *
     * @return bool
     */
    private function should_default_optional_consent() {
        return 'us' === $this->get_region_preset();
    }

    /**
     * Normalize notice position.
     *
     * @param string $position Raw position.
     * @return string
     */
    private function normalize_notice_position( $position ) {
        $position = sanitize_key( (string) $position );
        $allowed = array( 'bottom_center', 'bottom_left', 'bottom_right' );

        return in_array( $position, $allowed, true ) ? $position : 'bottom_center';
    }

    /**
     * Read a theme option safely.
     *
     * @param string $key Option key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    private function get_option( $key, $default = '' ) {
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return developer_starter_get_option( $key, $default );
        }

        $options = get_option( 'developer_starter_options', array() );
        if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
            return $options[ $key ];
        }

        return $default;
    }
}
