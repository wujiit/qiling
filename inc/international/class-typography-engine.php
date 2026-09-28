<?php
/**
 * 国际化多语言排版字体引擎。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\International;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 显式启用时为多语言环境提供轻量级字型排版支持。
 */
class Typography_Engine {

    /**
     * 开关配置项。
     */
    const OPTION_ENABLE = 'international_typography_enable';

    /**
     * 排版模式配置项。
     */
    const OPTION_MODE = 'international_typography_mode';

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'body_class', array( $this, 'filter_body_class' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 30 );
    }

    /**
     * 排版引擎是否启用。
     *
     * @return bool
     */
    public function is_enabled() {
        return '1' === (string) $this->get_option( self::OPTION_ENABLE, '' );
    }

    /**
     * 向 body 添加排版辅助类名。
     *
     * @param array<int,string> $classes Body classes.
     * @return array<int,string>
     */
    public function filter_body_class( $classes ) {
        if ( ! $this->is_enabled() ) {
            return $classes;
        }

        $mode      = $this->get_effective_mode();
        $direction = $this->get_direction_for_mode( $mode );
        $locale    = $this->get_current_locale();

        $classes[] = 'qiling-i18n-typography';
        $classes[] = 'qiling-typography-mode-' . sanitize_html_class( $this->get_configured_mode() );
        $classes[] = 'qiling-lang-' . sanitize_html_class( $mode );
        $classes[] = 'qiling-dir-' . sanitize_html_class( $direction );

        if ( '' !== $locale ) {
            $classes[] = 'qiling-locale-' . sanitize_html_class( str_replace( '_', '-', strtolower( $locale ) ) );
        }

        return array_values( array_unique( array_filter( $classes ) ) );
    }

    /**
     * 加载前台字体排版样式表。
     *
     * @return void
     */
    public function enqueue_styles() {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $file = DEVELOPER_STARTER_DIR . '/assets/css/international-typography.css';
        $ver  = file_exists( $file ) ? (string) filemtime( $file ) : DEVELOPER_STARTER_VERSION;

        wp_enqueue_style(
            'developer-starter-international-typography',
            DEVELOPER_STARTER_ASSETS . '/css/international-typography.css',
            array( 'developer-starter-main' ),
            $ver
        );
    }

    /**
     * 获取配置的排版模式。
     *
     * @return string
     */
    private function get_configured_mode() {
        $mode = (string) $this->get_option( self::OPTION_MODE, 'auto' );
        return in_array( $mode, array( 'auto', 'zh', 'en', 'ja', 'ko', 'rtl' ), true ) ? $mode : 'auto';
    }

    /**
     * 自动配置模式下解析当前生效的排版模式。
     *
     * @return string zh, en, ja, ko, or rtl.
     */
    private function get_effective_mode() {
        $mode = $this->get_configured_mode();
        if ( 'auto' !== $mode ) {
            return $mode;
        }

        if ( function_exists( 'is_rtl' ) && is_rtl() ) {
            return 'rtl';
        }

        $frontend_lang = function_exists( 'developer_starter_get_current_frontend_lang' )
            ? sanitize_key( (string) developer_starter_get_current_frontend_lang() )
            : '';
        if ( '' !== $frontend_lang ) {
            $mode_from_lang = $this->map_language_to_mode( $frontend_lang );
            if ( '' !== $mode_from_lang ) {
                return $mode_from_lang;
            }
        }

        $locale = strtolower( str_replace( '_', '-', $this->get_current_locale() ) );
        if ( '' !== $locale ) {
            $mode_from_locale = $this->map_language_to_mode( $locale );
            if ( '' !== $mode_from_locale ) {
                return $mode_from_locale;
            }
        }

        return 'zh';
    }

    /**
     * 将语言或地区编码映射为对应的排版模式。
     *
     * @param string $language Language code or locale.
     * @return string
     */
    private function map_language_to_mode( $language ) {
        $language = strtolower( str_replace( '_', '-', trim( (string) $language ) ) );
        if ( '' === $language ) {
            return '';
        }

        $primary = strtok( $language, '-' );
        if ( false === $primary ) {
            $primary = $language;
        }

        if ( in_array( $primary, array( 'ar', 'fa', 'he', 'ur' ), true ) ) {
            return 'rtl';
        }
        if ( in_array( $primary, array( 'zh', 'cn', 'chinese' ), true ) ) {
            return 'zh';
        }
        if ( in_array( $primary, array( 'ja', 'jp', 'japanese' ), true ) ) {
            return 'ja';
        }
        if ( in_array( $primary, array( 'ko', 'kr', 'korean' ), true ) ) {
            return 'ko';
        }
        if ( in_array( $primary, array( 'en', 'fr', 'de', 'es', 'it', 'pt', 'nl', 'pl', 'ru', 'tr', 'vi', 'id', 'ms' ), true ) ) {
            return 'en';
        }

        return '';
    }

    /**
     * 获取排版模式的文字书写方向（LTR/RTL）。
     *
     * @param string $mode Typography mode.
     * @return string
     */
    private function get_direction_for_mode( $mode ) {
        return 'rtl' === $mode ? 'rtl' : 'ltr';
    }

    /**
     * 获取当前语言地区编码。
     *
     * @return string
     */
    private function get_current_locale() {
        if ( function_exists( 'determine_locale' ) ) {
            return (string) determine_locale();
        }

        return function_exists( 'get_locale' ) ? (string) get_locale() : '';
    }

    /**
     * 安全读取主题配置选项。
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
