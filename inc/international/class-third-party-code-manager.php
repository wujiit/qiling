<?php
/**
 * 国际化基础第三方代码管理器。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\International;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 输出在国际化设置面板中配置的轻量级第三方代码片段。
 *
 * 此类仅读取 international_* 相关的新增配置，
 * 不替代百度统计、自定义 JS、验证码、SEO 或备案号输出等功能。
 */
class Third_Party_Code_Manager {

    /**
     * 全局总开关配置项。
     */
    const OPTION_ENABLE = 'international_third_party_code_enable';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_head', array( $this, 'render_head_codes' ), 998 );
        add_action( 'wp_footer', array( $this, 'render_footer_codes' ), 998 );
    }

    /**
     * 获取第三方代码分组配置。
     *
     * @return array<string,array<string,string>>
     */
    public static function get_code_groups() {
        return array(
            'head'      => array(
                'enable'           => 'international_code_head_enable',
                'content'          => 'international_code_head_content',
                'position'         => 'international_code_head_position',
                'consent'          => 'international_code_head_require_consent',
                'category'         => 'international_code_head_category',
                'default'          => 'head',
                'default_category' => 'necessary',
            ),
            'footer'    => array(
                'enable'           => 'international_code_footer_enable',
                'content'          => 'international_code_footer_content',
                'position'         => 'international_code_footer_position',
                'consent'          => 'international_code_footer_require_consent',
                'category'         => 'international_code_footer_category',
                'default'          => 'footer',
                'default_category' => 'custom',
            ),
            'analytics' => array(
                'enable'           => 'international_code_analytics_enable',
                'content'          => 'international_code_analytics_content',
                'position'         => 'international_code_analytics_position',
                'consent'          => 'international_code_analytics_require_consent',
                'category'         => 'international_code_analytics_category',
                'default'          => 'head',
                'default_category' => 'statistics',
            ),
            'ads'       => array(
                'enable'           => 'international_code_ads_enable',
                'content'          => 'international_code_ads_content',
                'position'         => 'international_code_ads_position',
                'consent'          => 'international_code_ads_require_consent',
                'category'         => 'international_code_ads_category',
                'default'          => 'footer',
                'default_category' => 'advertising',
            ),
            'custom'    => array(
                'enable'           => 'international_code_custom_enable',
                'content'          => 'international_code_custom_content',
                'position'         => 'international_code_custom_position',
                'consent'          => 'international_code_custom_require_consent',
                'category'         => 'international_code_custom_category',
                'default'          => 'footer',
                'default_category' => 'custom',
            ),
        );
    }

    /**
     * 渲染挂载至 wp_head 的代码组。
     *
     * @return void
     */
    public function render_head_codes() {
        $this->render_codes_for_position( 'head' );
    }

    /**
     * 渲染挂载至 wp_footer 的代码组。
     *
     * @return void
     */
    public function render_footer_codes() {
        $this->render_codes_for_position( 'footer' );
    }

    /**
     * 在指定钩子位置渲染配置的代码组。
     *
     * @param string $position Hook position: head or footer.
     * @return void
     */
    private function render_codes_for_position( $position ) {
        $position = 'head' === $position ? 'head' : 'footer';

        if ( '1' !== $this->get_option( self::OPTION_ENABLE, '' ) ) {
            return;
        }

        foreach ( self::get_code_groups() as $group_id => $group ) {
            if ( empty( $group['enable'] ) || '1' !== $this->get_option( $group['enable'], '' ) ) {
                continue;
            }

            $target_position = isset( $group['position'], $group['default'] )
                ? $this->normalize_position( $this->get_option( $group['position'], $group['default'] ) )
                : 'footer';
            if ( $target_position !== $position ) {
                continue;
            }

            $code = isset( $group['content'] ) ? trim( (string) $this->get_option( $group['content'], '' ) ) : '';
            if ( '' === $code ) {
                continue;
            }

            $category = $this->resolve_group_category( $group_id, $group );
            $requires_consent = $this->category_requires_consent( $category );
            $allowed = (bool) apply_filters(
                'developer_starter_international_third_party_code_allowed',
                true,
                $group_id,
                $position,
                $requires_consent,
                $category
            );
            if ( ! $allowed ) {
                $should_defer = (bool) apply_filters(
                    'developer_starter_international_third_party_code_should_defer',
                    false,
                    $group_id,
                    $position,
                    $requires_consent,
                    $code,
                    $category
                );
                if ( $should_defer ) {
                    $this->render_deferred_code( $group_id, $position, $code, $category );
                }
                continue;
            }

            $code = (string) apply_filters(
                'developer_starter_international_third_party_code_html',
                $code,
                $group_id,
                $position,
                $requires_consent,
                $category
            );
            if ( '' === trim( $code ) ) {
                continue;
            }

            echo "\n" . '<!-- Qiling international third-party code: ' . esc_html( $group_id ) . ' -->' . "\n";
            echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-provided snippet, sanitized on save.
            echo '<!-- /Qiling international third-party code: ' . esc_html( $group_id ) . ' -->' . "\n";
        }
    }

    /**
     * 为等待 Cookie 授权的代码片段渲染非直接执行的 template 标签。
     *
     * @param string $group_id Code group id.
     * @param string $position Target output position.
     * @param string $code Snippet HTML.
     * @return void
     */
    private function render_deferred_code( $group_id, $position, $code, $category ) {
        $encoded = base64_encode( (string) $code );
        if ( '' === $encoded ) {
            return;
        }

        echo "\n" . '<script type="application/json" data-qiling-international-code="1" data-group="' . esc_attr( $group_id ) . '" data-position="' . esc_attr( $position ) . '" data-category="' . esc_attr( $category ) . '">' . esc_html( $encoded ) . '</script>' . "\n";
    }

    /**
     * 解析代码组对应的授权分类。
     *
     * @param string              $group_id Code group id.
     * @param array<string,mixed> $group Group config.
     * @return string
     */
    private function resolve_group_category( $group_id, $group ) {
        $default = isset( $group['default_category'] ) ? (string) $group['default_category'] : 'custom';
        $category_key = isset( $group['category'] ) ? (string) $group['category'] : '';
        $saved = '' !== $category_key ? (string) $this->get_option( $category_key, '' ) : '';

        if ( '' === $saved && ! empty( $group['consent'] ) && '1' === $this->get_option( $group['consent'], '' ) ) {
            $saved = $this->get_legacy_consent_category( $group_id, $default );
        }

        return $this->normalize_category( '' !== $saved ? $saved : $default );
    }

    /**
     * 将旧版仅需同意的代码组映射到非必要分类中。
     *
     * @param string $group_id Code group id.
     * @param string $default Default category.
     * @return string
     */
    private function get_legacy_consent_category( $group_id, $default ) {
        $legacy_map = array(
            'analytics' => 'statistics',
            'ads'       => 'advertising',
            'custom'    => 'custom',
            'footer'    => 'custom',
            'head'      => 'custom',
        );

        return isset( $legacy_map[ $group_id ] ) ? $legacy_map[ $group_id ] : $default;
    }

    /**
     * 检查分类是否需要在执行前获得访问者授权。
     *
     * @param string $category Category key.
     * @return bool
     */
    private function category_requires_consent( $category ) {
        return 'necessary' !== $this->normalize_category( $category );
    }

    /**
     * 规范化授权分类键名。
     *
     * @param string $category Raw category.
     * @return string
     */
    private function normalize_category( $category ) {
        if ( class_exists( '\Developer_Starter\International\Cookie_Consent_Manager' ) && method_exists( '\Developer_Starter\International\Cookie_Consent_Manager', 'normalize_category' ) ) {
            return \Developer_Starter\International\Cookie_Consent_Manager::normalize_category( $category, 'custom' );
        }

        $category = sanitize_key( (string) $category );
        $allowed = array( 'necessary', 'statistics', 'marketing', 'advertising', 'custom' );

        return in_array( $category, $allowed, true ) ? $category : 'custom';
    }

    /**
     * 规范化输出位置选项。
     *
     * @param mixed $position Raw position.
     * @return string
     */
    private function normalize_position( $position ) {
        return 'head' === (string) $position ? 'head' : 'footer';
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
