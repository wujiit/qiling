<?php
/**
 * Front-end page resource manifest and quality auditor.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generates a current-page resource manifest and lightweight performance/a11y audit.
 *
 * The auditor is intentionally admin-only by default so public visitors do not pay
 * for HTML inspection. It complements the existing dual resource loading modes
 * without changing their behavior.
 */
class Page_Performance_A11y_Auditor {

    const OPTION_ENABLE     = 'page_quality_audit_enable';
    const OPTION_EMBED_JSON = 'page_quality_audit_embed_json';
    const QUERY_VAR         = 'qiling_page_audit';
    const REPORT_VERSION    = '1.0.0';

    /**
     * @var Page_Performance_A11y_Auditor|null
     */
    private static $instance = null;

    /**
     * @var array<string,mixed>
     */
    private $last_report = array();

    /**
     * Get singleton instance.
     *
     * @return Page_Performance_A11y_Auditor
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Sanitize admin options owned by this auditor.
     *
     * @param array<string,mixed> $options Submitted options.
     * @param array<string,mixed> $existing_options Existing options.
     * @return array<string,mixed>
     */
    public static function sanitize_options( $options, $existing_options = array() ) {
        foreach ( array( self::OPTION_ENABLE, self::OPTION_EMBED_JSON ) as $field ) {
            if ( isset( $options[ $field ] ) ) {
                $options[ $field ] = ( '1' === (string) $options[ $field ] ) ? '1' : '';
            }
        }

        return $options;
    }

    /**
     * Build design-token diagnostics for the admin workbench.
     *
     * @param array<string,mixed> $payload Design token payload.
     * @return array<string,mixed>
     */
    public static function build_design_system_diagnostics( $payload = array() ) {
        $payload = is_array( $payload ) ? $payload : array();

        $tokens            = isset( $payload['tokens'] ) && is_array( $payload['tokens'] ) ? $payload['tokens'] : array();
        $component_styles  = isset( $payload['componentStyles'] ) && is_array( $payload['componentStyles'] ) ? $payload['componentStyles'] : array();
        $css_variables     = isset( $payload['cssVariables'] ) && is_array( $payload['cssVariables'] ) ? $payload['cssVariables'] : array();
        $component_vars    = isset( $payload['componentCssVariables'] ) && is_array( $payload['componentCssVariables'] ) ? $payload['componentCssVariables'] : array();
        $component_defs    = isset( $payload['componentStyleDefinitions'] ) && is_array( $payload['componentStyleDefinitions'] ) ? $payload['componentStyleDefinitions'] : array();
        $variables         = array_merge( $css_variables, $component_vars );
        $definition_map    = array();

        foreach ( $component_defs as $definition ) {
            if ( ! is_array( $definition ) || empty( $definition['key'] ) ) {
                continue;
            }
            $definition_map[ (string) $definition['key'] ] = $definition;
        }

        $contrast_pairs = array(
            array(
                'key'   => 'text_background',
                'label' => __( '正文 / 页面背景', 'developer-starter' ),
                'fg'    => isset( $tokens['text'] ) ? (string) $tokens['text'] : '',
                'bg'    => isset( $tokens['background'] ) ? (string) $tokens['background'] : '',
            ),
            array(
                'key'   => 'heading_background',
                'label' => __( '标题 / 页面背景', 'developer-starter' ),
                'fg'    => isset( $tokens['heading'] ) ? (string) $tokens['heading'] : '',
                'bg'    => isset( $tokens['background'] ) ? (string) $tokens['background'] : '',
            ),
            array(
                'key'   => 'button_primary',
                'label' => __( '主按钮文字 / 背景', 'developer-starter' ),
                'fg'    => isset( $component_styles['button_text'] ) ? (string) $component_styles['button_text'] : '',
                'bg'    => isset( $component_styles['button_bg'] ) ? (string) $component_styles['button_bg'] : '',
            ),
            array(
                'key'   => 'form_input',
                'label' => __( '输入框文字 / 背景', 'developer-starter' ),
                'fg'    => isset( $component_styles['form_input_text'] ) ? (string) $component_styles['form_input_text'] : '',
                'bg'    => isset( $component_styles['form_input_bg'] ) ? (string) $component_styles['form_input_bg'] : '',
            ),
            array(
                'key'   => 'dropdown_layer',
                'label' => __( '下拉层文字 / 背景', 'developer-starter' ),
                'fg'    => isset( $component_styles['dropdown_link'] ) ? (string) $component_styles['dropdown_link'] : '',
                'bg'    => isset( $component_styles['dropdown_bg'] ) ? (string) $component_styles['dropdown_bg'] : '',
            ),
            array(
                'key'   => 'footer_main',
                'label' => __( '页脚文字 / 页脚背景', 'developer-starter' ),
                'fg'    => isset( $component_styles['footer_text'] ) ? (string) $component_styles['footer_text'] : '',
                'bg'    => isset( $component_styles['footer_bg'] ) ? (string) $component_styles['footer_bg'] : '',
            ),
            array(
                'key'   => 'dark_text_background',
                'label' => __( '暗色正文 / 暗色背景', 'developer-starter' ),
                'fg'    => isset( $tokens['dark_text'] ) ? (string) $tokens['dark_text'] : '',
                'bg'    => isset( $tokens['dark_bg'] ) ? (string) $tokens['dark_bg'] : '',
            ),
        );

        $contrast_items    = array();
        $contrast_warnings = 0;

        foreach ( $contrast_pairs as $pair ) {
            $resolved_bg = self::resolve_diagnostic_color_value( $pair['bg'], $variables );
            $resolved_fg = self::resolve_diagnostic_color_value( $pair['fg'], $variables );
            $ratio       = ( '' !== $resolved_fg && '' !== $resolved_bg ) ? self::contrast_ratio( $resolved_fg, $resolved_bg ) : null;
            $status      = 'unknown';
            $message     = __( '包含渐变或复杂变量，当前工作台只对纯色对比度做快速检测。', 'developer-starter' );

            if ( null !== $ratio ) {
                $status  = $ratio >= 4.5 ? 'pass' : 'warning';
                $message = $ratio >= 4.5
                    ? __( '对比度通过 WCAG AA 正文建议值。', 'developer-starter' )
                    : __( '对比度低于 4.5，建议提升文字与背景反差。', 'developer-starter' );
                if ( 'warning' === $status ) {
                    $contrast_warnings++;
                }
            }

            $contrast_items[] = array(
                'key'      => $pair['key'],
                'label'    => $pair['label'],
                'status'   => $status,
                'ratio'    => null !== $ratio ? round( $ratio, 2 ) : null,
                'message'  => $message,
                'resolved' => array(
                    'foreground' => $resolved_fg,
                    'background' => $resolved_bg,
                ),
            );
        }

        $hardcoded_components = array();

        foreach ( $component_styles as $style_key => $style_value ) {
            $definition = isset( $definition_map[ $style_key ] ) ? $definition_map[ $style_key ] : array();
            if ( ! self::is_component_color_like_definition( $definition ) ) {
                continue;
            }

            $style_value = trim( (string) $style_value );
            if ( '' === $style_value || self::is_variable_driven_design_value( $style_value ) || ! self::contains_literal_design_color( $style_value ) ) {
                continue;
            }

            $hardcoded_components[] = array(
                'key'   => (string) $style_key,
                'label' => isset( $definition['label'] ) ? (string) $definition['label'] : (string) $style_key,
                'value' => $style_value,
                'group' => isset( $definition['group'] ) ? (string) $definition['group'] : '',
            );
        }

        $dark_pairs = array(
            array( 'light' => 'card_bg', 'dark' => 'dark_card_bg' ),
            array( 'light' => 'card_border', 'dark' => 'dark_card_border' ),
            array( 'light' => 'form_input_bg', 'dark' => 'dark_form_input_bg' ),
            array( 'light' => 'form_input_text', 'dark' => 'dark_form_input_text' ),
            array( 'light' => 'form_input_border', 'dark' => 'dark_form_input_border' ),
            array( 'light' => 'module_title_color', 'dark' => 'dark_module_title_color' ),
            array( 'light' => 'post_card_bg', 'dark' => 'dark_post_card_bg' ),
            array( 'light' => 'post_card_border', 'dark' => 'dark_post_card_border' ),
            array( 'light' => 'post_card_title_color', 'dark' => 'dark_post_card_title_color' ),
            array( 'light' => 'post_card_meta_color', 'dark' => 'dark_post_card_meta_color' ),
        );

        $dark_items      = array();
        $dark_warnings   = 0;
        $mapped_dark_keys = array();

        foreach ( $dark_pairs as $pair ) {
            $light_key   = (string) $pair['light'];
            $dark_key    = (string) $pair['dark'];
            $light_value = isset( $component_styles[ $light_key ] ) ? trim( (string) $component_styles[ $light_key ] ) : '';
            $dark_value  = isset( $component_styles[ $dark_key ] ) ? trim( (string) $component_styles[ $dark_key ] ) : '';
            $light_def   = isset( $definition_map[ $light_key ] ) ? $definition_map[ $light_key ] : array();
            $dark_def    = isset( $definition_map[ $dark_key ] ) ? $definition_map[ $dark_key ] : array();
            $mapped_dark_keys[] = $light_key;
            $mapped_dark_keys[] = $dark_key;

            $is_variable_driven = self::is_variable_driven_design_value( $light_value ) && self::is_variable_driven_design_value( $dark_value );
            $has_explicit_dark  = '' !== $dark_value && $dark_value !== $light_value;
            $status             = ( $is_variable_driven || $has_explicit_dark ) ? 'pass' : 'warning';
            $message            = __( '先不用管', 'developer-starter' );

            if ( 'warning' === $status ) {
                $dark_warnings++;
            }

            $dark_items[] = array(
                'key'      => $light_key,
                'label'    => isset( $light_def['label'] ) ? (string) $light_def['label'] : $light_key,
                'darkLabel'=> isset( $dark_def['label'] ) ? (string) $dark_def['label'] : $dark_key,
                'status'   => $status,
                'message'  => $message,
            );
        }

        foreach ( $component_styles as $style_key => $style_value ) {
            $style_key  = (string) $style_key;
            $style_value = trim( (string) $style_value );
            if ( '' === $style_value || 0 === strpos( $style_key, 'dark_' ) || in_array( $style_key, $mapped_dark_keys, true ) ) {
                continue;
            }

            $definition = isset( $definition_map[ $style_key ] ) ? $definition_map[ $style_key ] : array();
            if ( ! self::is_component_color_like_definition( $definition ) || self::is_variable_driven_design_value( $style_value ) || ! self::contains_literal_design_color( $style_value ) ) {
                continue;
            }

            $dark_warnings++;
            $dark_items[] = array(
                'key'      => $style_key,
                'label'    => isset( $definition['label'] ) ? (string) $definition['label'] : $style_key,
                'darkLabel'=> '',
                'status'   => 'warning',
                'message'  => __( '先不用管', 'developer-starter' ),
            );
        }

        return array(
            'summary' => array(
                'contrastWarnings' => $contrast_warnings,
                'hardcodedCount'   => count( $hardcoded_components ),
                'darkModeWarnings' => $dark_warnings,
            ),
            'contrast'            => $contrast_items,
            'hardcodedComponents' => $hardcoded_components,
            'darkMode'            => $dark_items,
        );
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'template_redirect', array( $this, 'maybe_start_buffer' ), 99 );
    }

    /**
     * Start output inspection buffer when safe and useful.
     *
     * @return void
     */
    public function maybe_start_buffer() {
        if ( ! $this->should_collect() ) {
            return;
        }

        ob_start( array( $this, 'finalize_html' ) );
    }

    /**
     * Return the last generated report for integrations/tests.
     *
     * @return array<string,mixed>
     */
    public function get_last_report() {
        return $this->last_report;
    }

    /**
     * Analyze completed HTML and optionally append JSON payloads for admins.
     *
     * @param string $html Full page HTML.
     * @return string
     */
    public function finalize_html( $html ) {
        if ( ! is_string( $html ) || '' === $html || false === stripos( $html, '<html' ) ) {
            return $html;
        }

        $report = $this->analyze_html( $html );
        $this->last_report = $report;
        $GLOBALS['developer_starter_last_page_quality_report'] = $report;

        /**
         * Fires after the front-end quality report has been generated.
         *
         * @param array<string,mixed> $report Current page report.
         * @param string              $html   Raw HTML inspected by the auditor.
         */
        do_action( 'developer_starter_page_quality_report', $report, $html );

        if ( ! $this->should_embed_report() ) {
            return $html;
        }

        return $this->inject_report_payload( $html, $report );
    }

    /**
     * Build a full report from HTML.
     *
     * @param string $html Full page HTML.
     * @return array<string,mixed>
     */
    public function analyze_html( $html ) {
        $manifest = $this->build_resource_manifest( $html );
        $audits   = array(
            'lcp'             => $this->audit_lcp( $manifest ),
            'images'          => $this->audit_images( $manifest ),
            'headings'        => $this->audit_headings( $html ),
            'forms'           => $this->audit_forms( $html ),
            'color_contrast'  => $this->audit_color_contrast( $html ),
            'mobile_overflow' => $this->audit_mobile_overflow( $html ),
            'cls'             => $this->audit_cls( $manifest ),
        );

        $summary = $this->summarize_report( $manifest, $audits );

        return array(
            'version'      => self::REPORT_VERSION,
            'generated_at' => function_exists( 'current_time' ) ? current_time( 'mysql' ) : gmdate( 'Y-m-d H:i:s' ),
            'url'          => $this->get_current_url(),
            'post_id'      => function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0,
            'summary'      => $summary,
            'manifest'     => $manifest,
            'audits'       => $audits,
        );
    }

    /**
     * Build a resource manifest from final HTML plus page module metadata.
     *
     * @param string $html Full page HTML.
     * @return array<string,mixed>
     */
    private function build_resource_manifest( $html ) {
        $links   = $this->extract_tags( $html, 'link' );
        $scripts = $this->extract_tags( $html, 'script' );
        $images  = $this->extract_tags( $html, 'img' );
        $sources = $this->extract_tags( $html, 'source' );
        $iframes = $this->extract_tags( $html, 'iframe' );
        $videos  = $this->extract_tags( $html, 'video' );

        $stylesheets = array();
        $hints       = array();

        foreach ( $links as $link ) {
            $attrs = isset( $link['attrs'] ) && is_array( $link['attrs'] ) ? $link['attrs'] : array();
            $rel   = strtolower( isset( $attrs['rel'] ) ? (string) $attrs['rel'] : '' );
            $href  = $this->normalize_url_value( isset( $attrs['href'] ) ? (string) $attrs['href'] : '' );

            if ( '' === $href ) {
                continue;
            }

            if ( false !== strpos( $rel, 'stylesheet' ) ) {
                $stylesheets[] = array(
                    'href'  => $href,
                    'media' => isset( $attrs['media'] ) ? sanitize_text_field( (string) $attrs['media'] ) : 'all',
                );
                continue;
            }

            if ( preg_match( '/\b(preload|prefetch|preconnect|dns-prefetch|modulepreload)\b/i', $rel, $matches ) ) {
                $hints[] = array(
                    'rel'  => strtolower( $matches[1] ),
                    'as'   => isset( $attrs['as'] ) ? sanitize_key( (string) $attrs['as'] ) : '',
                    'href' => $href,
                    'type' => isset( $attrs['type'] ) ? sanitize_text_field( (string) $attrs['type'] ) : '',
                );
            }
        }

        $script_assets = array();
        foreach ( $scripts as $script ) {
            $attrs = isset( $script['attrs'] ) && is_array( $script['attrs'] ) ? $script['attrs'] : array();
            $src   = $this->normalize_url_value( isset( $attrs['src'] ) ? (string) $attrs['src'] : '' );
            if ( '' === $src ) {
                continue;
            }

            $script_assets[] = array(
                'src'   => $src,
                'type'  => isset( $attrs['type'] ) ? sanitize_text_field( (string) $attrs['type'] ) : '',
                'async' => isset( $attrs['async'] ),
                'defer' => isset( $attrs['defer'] ),
            );
        }

        $image_assets = array();
        foreach ( $images as $image ) {
            $attrs = isset( $image['attrs'] ) && is_array( $image['attrs'] ) ? $image['attrs'] : array();
            $src   = $this->normalize_url_value( isset( $attrs['src'] ) ? (string) $attrs['src'] : '' );
            if ( '' === $src ) {
                continue;
            }

            $image_assets[] = array(
                'src'           => $src,
                'srcset'        => isset( $attrs['srcset'] ) ? sanitize_text_field( (string) $attrs['srcset'] ) : '',
                'sizes'         => isset( $attrs['sizes'] ) ? sanitize_text_field( (string) $attrs['sizes'] ) : '',
                'alt'           => isset( $attrs['alt'] ) ? sanitize_text_field( (string) $attrs['alt'] ) : null,
                'width'         => isset( $attrs['width'] ) ? sanitize_text_field( (string) $attrs['width'] ) : '',
                'height'        => isset( $attrs['height'] ) ? sanitize_text_field( (string) $attrs['height'] ) : '',
                'loading'       => isset( $attrs['loading'] ) ? sanitize_key( (string) $attrs['loading'] ) : '',
                'decoding'      => isset( $attrs['decoding'] ) ? sanitize_key( (string) $attrs['decoding'] ) : '',
                'fetchpriority' => isset( $attrs['fetchpriority'] ) ? sanitize_key( (string) $attrs['fetchpriority'] ) : '',
                'style'         => isset( $attrs['style'] ) ? sanitize_text_field( (string) $attrs['style'] ) : '',
            );
        }

        $source_assets = array();
        foreach ( $sources as $source ) {
            $attrs = isset( $source['attrs'] ) && is_array( $source['attrs'] ) ? $source['attrs'] : array();
            $source_assets[] = array(
                'srcset' => isset( $attrs['srcset'] ) ? sanitize_text_field( (string) $attrs['srcset'] ) : '',
                'type'   => isset( $attrs['type'] ) ? sanitize_text_field( (string) $attrs['type'] ) : '',
                'media'  => isset( $attrs['media'] ) ? sanitize_text_field( (string) $attrs['media'] ) : '',
            );
        }

        $embed_assets = array();
        foreach ( array_merge( $iframes, $videos ) as $embed ) {
            $attrs = isset( $embed['attrs'] ) && is_array( $embed['attrs'] ) ? $embed['attrs'] : array();
            $embed_assets[] = array(
                'tag'     => isset( $embed['tag'] ) ? sanitize_key( (string) $embed['tag'] ) : '',
                'src'     => $this->normalize_url_value( isset( $attrs['src'] ) ? (string) $attrs['src'] : '' ),
                'width'   => isset( $attrs['width'] ) ? sanitize_text_field( (string) $attrs['width'] ) : '',
                'height'  => isset( $attrs['height'] ) ? sanitize_text_field( (string) $attrs['height'] ) : '',
                'loading' => isset( $attrs['loading'] ) ? sanitize_key( (string) $attrs['loading'] ) : '',
                'style'   => isset( $attrs['style'] ) ? sanitize_text_field( (string) $attrs['style'] ) : '',
            );
        }

        $modules = $this->get_current_page_modules_manifest();

        $manifest = array(
            'page'      => array(
                'url'     => $this->get_current_url(),
                'post_id' => function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0,
            ),
            'assets'    => array(
                'stylesheets' => $this->dedupe_assets( $stylesheets, 'href' ),
                'scripts'     => $this->dedupe_assets( $script_assets, 'src' ),
                'images'      => $this->dedupe_assets( $image_assets, 'src' ),
                'sources'     => $source_assets,
                'embeds'      => $embed_assets,
                'hints'       => $this->dedupe_assets( $hints, 'href' ),
            ),
            'modules'   => $modules,
            'vendors'   => $this->detect_vendor_assets( $html, $script_assets, $stylesheets ),
            'formats'   => $this->summarize_image_formats( $image_assets, $source_assets ),
            'resources' => array(),
        );

        $manifest['resources'] = array(
            'stylesheets' => count( $manifest['assets']['stylesheets'] ),
            'scripts'     => count( $manifest['assets']['scripts'] ),
            'images'      => count( $manifest['assets']['images'] ),
            'embeds'      => count( $manifest['assets']['embeds'] ),
            'hints'       => count( $manifest['assets']['hints'] ),
        );

        return apply_filters( 'developer_starter_page_resource_manifest', $manifest, $html );
    }

    /**
     * Audit LCP-related image hints.
     *
     * @param array<string,mixed> $manifest Resource manifest.
     * @return array<string,mixed>
     */
    private function audit_lcp( $manifest ) {
        $images = isset( $manifest['assets']['images'] ) && is_array( $manifest['assets']['images'] ) ? $manifest['assets']['images'] : array();
        $hints  = isset( $manifest['assets']['hints'] ) && is_array( $manifest['assets']['hints'] ) ? $manifest['assets']['hints'] : array();
        $issues = array();

        $high_priority_images = array_values(
            array_filter(
                $images,
                function( $image ) {
                    return is_array( $image ) && isset( $image['fetchpriority'] ) && 'high' === strtolower( (string) $image['fetchpriority'] );
                }
            )
        );
        $image_preloads = array_values(
            array_filter(
                $hints,
                function( $hint ) {
                    return is_array( $hint )
                        && isset( $hint['rel'], $hint['as'] )
                        && 'preload' === strtolower( (string) $hint['rel'] )
                        && 'image' === strtolower( (string) $hint['as'] );
                }
            )
        );

        if ( ! empty( $images ) && empty( $high_priority_images ) && empty( $image_preloads ) ) {
            $issues[] = $this->make_issue(
                'lcp_image_priority_missing',
                __( '页面有图片，但没有发现 fetchpriority="high" 或图片 preload；首屏大图建议指定 LCP 优先级。', 'developer-starter' ),
                'warning'
            );
        }

        if ( count( $high_priority_images ) > 1 ) {
            $issues[] = $this->make_issue(
                'lcp_multiple_high_priority_images',
                __( '页面存在多个 fetchpriority="high" 图片，建议只保留最可能成为 LCP 的首屏图片。', 'developer-starter' ),
                'notice',
                array( 'count' => count( $high_priority_images ) )
            );
        }

        return $this->make_audit_result( $issues, array(
            'high_priority_images' => count( $high_priority_images ),
            'image_preloads'       => count( $image_preloads ),
        ) );
    }

    /**
     * Audit image attributes and modern format coverage.
     *
     * @param array<string,mixed> $manifest Resource manifest.
     * @return array<string,mixed>
     */
    private function audit_images( $manifest ) {
        $images  = isset( $manifest['assets']['images'] ) && is_array( $manifest['assets']['images'] ) ? $manifest['assets']['images'] : array();
        $formats = isset( $manifest['formats'] ) && is_array( $manifest['formats'] ) ? $manifest['formats'] : array();
        $issues  = array();

        foreach ( $images as $index => $image ) {
            if ( ! is_array( $image ) ) {
                continue;
            }

            $src = isset( $image['src'] ) ? (string) $image['src'] : '';
            if ( '' === $src || $this->is_ignored_image_src( $src ) ) {
                continue;
            }

            $context = array(
                'index' => $index,
                'src'   => $this->shorten_url_for_report( $src ),
            );

            if ( ! array_key_exists( 'alt', $image ) || null === $image['alt'] ) {
                $issues[] = $this->make_issue(
                    'image_alt_missing',
                    __( '图片缺少 alt 属性；内容图需要描述文字，装饰图也应显式 alt=""。', 'developer-starter' ),
                    'warning',
                    $context
                );
            }

            $has_width  = isset( $image['width'] ) && '' !== trim( (string) $image['width'] );
            $has_height = isset( $image['height'] ) && '' !== trim( (string) $image['height'] );
            $has_aspect = isset( $image['style'] ) && false !== stripos( (string) $image['style'], 'aspect-ratio' );
            if ( ( ! $has_width || ! $has_height ) && ! $has_aspect && ! $this->is_vector_image( $src ) ) {
                $issues[] = $this->make_issue(
                    'image_dimensions_missing',
                    __( '图片缺少 width/height 或 aspect-ratio，可能造成布局位移。', 'developer-starter' ),
                    'warning',
                    $context
                );
            }

            if ( empty( $image['loading'] ) ) {
                $issues[] = $this->make_issue(
                    'image_loading_missing',
                    __( '图片缺少 loading 属性；非首屏图片建议 lazy，首屏关键图建议 eager。', 'developer-starter' ),
                    'notice',
                    $context
                );
            }

            if ( empty( $image['decoding'] ) ) {
                $issues[] = $this->make_issue(
                    'image_decoding_missing',
                    __( '图片缺少 decoding="async"，可能阻塞渲染。', 'developer-starter' ),
                    'notice',
                    $context
                );
            }
        }

        $raster_count = isset( $formats['raster'] ) ? absint( $formats['raster'] ) : 0;
        $modern_count = ( isset( $formats['webp'] ) ? absint( $formats['webp'] ) : 0 ) + ( isset( $formats['avif'] ) ? absint( $formats['avif'] ) : 0 );
        if ( $raster_count > 0 && 0 === $modern_count ) {
            $issues[] = $this->make_issue(
                'modern_image_format_missing',
                __( '页面包含 JPG/PNG 图片，但没有发现 WebP/AVIF 资源或 source，建议为大图提供现代格式。', 'developer-starter' ),
                'notice',
                array( 'raster_images' => $raster_count )
            );
        }

        return $this->make_audit_result( $issues, array(
            'images'         => count( $images ),
            'modern_formats' => $modern_count,
        ) );
    }

    /**
     * Audit heading hierarchy.
     *
     * @param string $html Full page HTML.
     * @return array<string,mixed>
     */
    private function audit_headings( $html ) {
        $issues   = array();
        $headings = array();

        if ( preg_match_all( '/<h([1-6])\b[^>]*>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $level = absint( $match[1] );
                $text  = $this->clean_text_sample( $match[2], 80 );
                $headings[] = array(
                    'level' => $level,
                    'text'  => $text,
                );

                if ( '' === $text ) {
                    $issues[] = $this->make_issue(
                        'heading_empty',
                        __( '发现空标题标签，可能影响屏幕阅读器和 SEO 结构。', 'developer-starter' ),
                        'warning',
                        array( 'level' => $level )
                    );
                }
            }
        }

        $h1_count = 0;
        foreach ( $headings as $heading ) {
            if ( 1 === (int) $heading['level'] ) {
                $h1_count++;
            }
        }

        if ( empty( $headings ) ) {
            $issues[] = $this->make_issue(
                'heading_none',
                __( '页面没有标题标签，建议至少有一个清晰的 H1。', 'developer-starter' ),
                'warning'
            );
        } elseif ( 0 === $h1_count ) {
            $issues[] = $this->make_issue(
                'h1_missing',
                __( '页面缺少 H1，建议每个主要页面保留一个主标题。', 'developer-starter' ),
                'warning'
            );
        } elseif ( $h1_count > 1 ) {
            $issues[] = $this->make_issue(
                'h1_multiple',
                __( '页面存在多个 H1，建议保留一个主 H1，其余改为 H2/H3。', 'developer-starter' ),
                'notice',
                array( 'count' => $h1_count )
            );
        }

        $previous_level = 0;
        foreach ( $headings as $heading ) {
            $level = (int) $heading['level'];
            if ( $previous_level > 0 && $level > $previous_level + 1 ) {
                $issues[] = $this->make_issue(
                    'heading_level_skipped',
                    __( '标题层级出现跳级，建议按 H1 > H2 > H3 顺序组织内容。', 'developer-starter' ),
                    'notice',
                    array(
                        'from' => $previous_level,
                        'to'   => $level,
                        'text' => $heading['text'],
                    )
                );
            }
            $previous_level = $level;
        }

        return $this->make_audit_result( $issues, array(
            'headings' => $headings,
            'h1_count' => $h1_count,
        ) );
    }

    /**
     * Audit forms and interactive controls.
     *
     * @param string $html Full page HTML.
     * @return array<string,mixed>
     */
    private function audit_forms( $html ) {
        $issues    = array();
        $label_ids = array();

        if ( preg_match_all( '/<label\b[^>]*\bfor\s*=\s*(["\'])(.*?)\1[^>]*>/is', $html, $labels, PREG_SET_ORDER ) ) {
            foreach ( $labels as $label ) {
                $label_id = sanitize_html_class( html_entity_decode( (string) $label[2], ENT_QUOTES, 'UTF-8' ) );
                if ( '' !== $label_id ) {
                    $label_ids[ $label_id ] = true;
                }
            }
        }

        $controls = array();
        if ( preg_match_all( '/<(input|select|textarea)\b[^>]*>/is', $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
            foreach ( $matches as $match ) {
                $tag_name = strtolower( (string) $match[1][0] );
                $tag      = (string) $match[0][0];
                $offset   = (int) $match[0][1];
                $attrs    = $this->parse_attrs( $tag );
                $type     = isset( $attrs['type'] ) ? strtolower( (string) $attrs['type'] ) : '';

                if ( 'input' === $tag_name && in_array( $type, array( 'hidden', 'submit', 'reset', 'button', 'image' ), true ) ) {
                    continue;
                }

                $id = isset( $attrs['id'] ) ? sanitize_html_class( (string) $attrs['id'] ) : '';
                $controls[] = array(
                    'tag'  => $tag_name,
                    'type' => $type,
                    'id'   => $id,
                );

                $has_name = isset( $attrs['aria-label'] ) && '' !== trim( (string) $attrs['aria-label'] );
                $has_name = $has_name || ( isset( $attrs['aria-labelledby'] ) && '' !== trim( (string) $attrs['aria-labelledby'] ) );
                $has_name = $has_name || ( '' !== $id && isset( $label_ids[ $id ] ) );
                $has_name = $has_name || $this->is_control_wrapped_by_label( $html, $offset );

                if ( ! $has_name ) {
                    $issues[] = $this->make_issue(
                        'form_control_name_missing',
                        __( '表单控件缺少可访问名称，请添加 label、aria-label 或 aria-labelledby。', 'developer-starter' ),
                        'warning',
                        array(
                            'tag'  => $tag_name,
                            'type' => $type,
                            'id'   => $id,
                        )
                    );
                }
            }
        }

        if ( preg_match_all( '/<button\b([^>]*)>(.*?)<\/button>/is', $html, $buttons, PREG_SET_ORDER ) ) {
            foreach ( $buttons as $button ) {
                $attrs = $this->parse_attrs( '<button ' . $button[1] . '>' );
                $text  = $this->clean_text_sample( $button[2], 80 );
                $has_name = '' !== $text
                    || ( isset( $attrs['aria-label'] ) && '' !== trim( (string) $attrs['aria-label'] ) )
                    || ( isset( $attrs['aria-labelledby'] ) && '' !== trim( (string) $attrs['aria-labelledby'] ) );

                if ( ! $has_name ) {
                    $issues[] = $this->make_issue(
                        'button_name_missing',
                        __( '按钮缺少可访问名称，请补充按钮文字或 aria-label。', 'developer-starter' ),
                        'warning'
                    );
                }
            }
        }

        return $this->make_audit_result( $issues, array(
            'controls' => count( $controls ),
        ) );
    }

    /**
     * Audit design-token and inline color contrast.
     *
     * @param string $html Full page HTML.
     * @return array<string,mixed>
     */
    private function audit_color_contrast( $html ) {
        $issues = array();

        if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            $tokens = Design_Tokens::get_current_tokens();
            $pairs  = array(
                array( 'text', 'background', __( '正文 / 页面背景', 'developer-starter' ) ),
                array( 'heading', 'background', __( '标题 / 页面背景', 'developer-starter' ) ),
                array( 'text_muted', 'background', __( '弱化文字 / 页面背景', 'developer-starter' ) ),
                array( 'primary', 'background', __( '品牌主色 / 页面背景', 'developer-starter' ) ),
                array( 'dark_text', 'dark_bg', __( '暗色正文 / 暗色背景', 'developer-starter' ) ),
            );

            foreach ( $pairs as $pair ) {
                $foreground = isset( $tokens[ $pair[0] ] ) ? (string) $tokens[ $pair[0] ] : '';
                $background = isset( $tokens[ $pair[1] ] ) ? (string) $tokens[ $pair[1] ] : '';
                $ratio      = self::contrast_ratio( $foreground, $background );

                if ( null !== $ratio && $ratio < 4.5 ) {
                    $issues[] = $this->make_issue(
                        'design_token_contrast_low',
                        sprintf(
                            /* translators: 1: token pair label, 2: contrast ratio. */
                            __( '%1$s 对比度为 %2$s，低于 WCAG AA 正文建议值 4.5。', 'developer-starter' ),
                            $pair[2],
                            number_format_i18n( $ratio, 2 )
                        ),
                        'warning',
                        array(
                            'pair'  => $pair[0] . '/' . $pair[1],
                            'ratio' => round( $ratio, 2 ),
                        )
                    );
                }
            }
        }

        if ( preg_match_all( '/<([a-z][a-z0-9]*)\b[^>]*\bstyle\s*=\s*(["\'])(.*?)\2[^>]*>/is', $html, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $tag_name = strtolower( (string) $match[1] );
                $style    = (string) $match[3];
                $colors   = $this->extract_inline_style_colors( $style );

                if ( empty( $colors['color'] ) || empty( $colors['background'] ) ) {
                    continue;
                }

                $ratio = self::contrast_ratio( $colors['color'], $colors['background'] );
                if ( null !== $ratio && $ratio < 4.5 ) {
                    $issues[] = $this->make_issue(
                        'inline_contrast_low',
                        __( '内联样式文字与背景色对比度偏低，移动端和弱视用户可能难以阅读。', 'developer-starter' ),
                        'warning',
                        array(
                            'tag'   => $tag_name,
                            'ratio' => round( $ratio, 2 ),
                        )
                    );
                }
            }
        }

        return $this->make_audit_result( $issues );
    }

    /**
     * Audit common mobile overflow patterns.
     *
     * @param string $html Full page HTML.
     * @return array<string,mixed>
     */
    private function audit_mobile_overflow( $html ) {
        $issues = array();

        if ( preg_match_all( '/<(h[1-6]|a|button|span|p|li)\b[^>]*>(.*?)<\/\1>/is', $html, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $tag  = strtolower( (string) $match[1] );
                $text = $this->clean_text_sample( $match[2], 180 );
                if ( '' === $text ) {
                    continue;
                }

                if ( preg_match( '/[A-Za-z0-9][A-Za-z0-9_\/\.\-:@%?&=#]{31,}/', $text, $long_match ) ) {
                    $issues[] = $this->make_issue(
                        'mobile_long_unbroken_text',
                        __( '发现较长的连续字符，移动端可能横向溢出；建议允许断行或改短展示文案。', 'developer-starter' ),
                        'notice',
                        array(
                            'tag'  => $tag,
                            'text' => $this->clean_text_sample( $long_match[0], 60 ),
                        )
                    );
                }
            }
        }

        if ( preg_match_all( '/<([a-z][a-z0-9]*)\b[^>]*\bstyle\s*=\s*(["\'])(.*?)\2[^>]*>/is', $html, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $tag_name = strtolower( (string) $match[1] );
                $style    = (string) $match[3];

                if ( false !== stripos( $style, 'white-space' ) && preg_match( '/white-space\s*:\s*nowrap/i', $style ) ) {
                    $issues[] = $this->make_issue(
                        'mobile_nowrap_text',
                        __( '发现 white-space: nowrap，窄屏下可能造成文字溢出。', 'developer-starter' ),
                        'notice',
                        array( 'tag' => $tag_name )
                    );
                }

                if ( preg_match( '/(?:^|;)\s*(?:min-)?width\s*:\s*(\d{3,})px/i', $style, $width_match ) && absint( $width_match[1] ) > 390 ) {
                    $issues[] = $this->make_issue(
                        'mobile_fixed_width_large',
                        __( '发现较大的固定像素宽度，移动端可能出现横向滚动。', 'developer-starter' ),
                        'notice',
                        array(
                            'tag'   => $tag_name,
                            'width' => absint( $width_match[1] ),
                        )
                    );
                }
            }
        }

        return $this->make_audit_result( $issues );
    }

    /**
     * Audit likely CLS contributors.
     *
     * @param array<string,mixed> $manifest Resource manifest.
     * @return array<string,mixed>
     */
    private function audit_cls( $manifest ) {
        $issues = array();
        $images = isset( $manifest['assets']['images'] ) && is_array( $manifest['assets']['images'] ) ? $manifest['assets']['images'] : array();
        $embeds = isset( $manifest['assets']['embeds'] ) && is_array( $manifest['assets']['embeds'] ) ? $manifest['assets']['embeds'] : array();

        $image_missing_dimensions = 0;
        foreach ( $images as $image ) {
            if ( ! is_array( $image ) ) {
                continue;
            }

            $src = isset( $image['src'] ) ? (string) $image['src'] : '';
            if ( '' === $src || $this->is_vector_image( $src ) || $this->is_ignored_image_src( $src ) ) {
                continue;
            }

            $has_width  = isset( $image['width'] ) && '' !== trim( (string) $image['width'] );
            $has_height = isset( $image['height'] ) && '' !== trim( (string) $image['height'] );
            $has_aspect = isset( $image['style'] ) && false !== stripos( (string) $image['style'], 'aspect-ratio' );
            if ( ( ! $has_width || ! $has_height ) && ! $has_aspect ) {
                $image_missing_dimensions++;
            }
        }

        if ( $image_missing_dimensions > 0 ) {
            $issues[] = $this->make_issue(
                'cls_image_dimensions_missing',
                __( '存在未声明尺寸的图片，浏览器无法提前预留空间，可能造成 CLS。', 'developer-starter' ),
                'warning',
                array( 'count' => $image_missing_dimensions )
            );
        }

        $embed_missing_dimensions = 0;
        foreach ( $embeds as $embed ) {
            if ( ! is_array( $embed ) ) {
                continue;
            }

            $has_width  = isset( $embed['width'] ) && '' !== trim( (string) $embed['width'] );
            $has_height = isset( $embed['height'] ) && '' !== trim( (string) $embed['height'] );
            $has_aspect = isset( $embed['style'] ) && false !== stripos( (string) $embed['style'], 'aspect-ratio' );
            if ( ( ! $has_width || ! $has_height ) && ! $has_aspect ) {
                $embed_missing_dimensions++;
            }
        }

        if ( $embed_missing_dimensions > 0 ) {
            $issues[] = $this->make_issue(
                'cls_embed_dimensions_missing',
                __( '存在未声明尺寸的 iframe/video，建议设置 width/height 或 aspect-ratio。', 'developer-starter' ),
                'warning',
                array( 'count' => $embed_missing_dimensions )
            );
        }

        return $this->make_audit_result( $issues, array(
            'images_missing_dimensions' => $image_missing_dimensions,
            'embeds_missing_dimensions' => $embed_missing_dimensions,
        ) );
    }

    /**
     * Extract HTML tags and attributes.
     *
     * @param string $html Full page HTML.
     * @param string $tag_name Tag name.
     * @return array<int,array<string,mixed>>
     */
    private function extract_tags( $html, $tag_name ) {
        $items = array();
        if ( ! preg_match_all( '/<' . preg_quote( $tag_name, '/' ) . '\b[^>]*>/i', $html, $matches, PREG_SET_ORDER ) ) {
            return $items;
        }

        foreach ( $matches as $match ) {
            $tag = $match[0];
            $items[] = array(
                'tag'   => strtolower( $tag_name ),
                'html'  => $tag,
                'attrs' => $this->parse_attrs( $tag ),
            );
        }

        return $items;
    }

    /**
     * Parse HTML attributes from one tag string.
     *
     * @param string $tag HTML tag.
     * @return array<string,string|bool>
     */
    private function parse_attrs( $tag ) {
        $attrs = array();
        if ( ! preg_match_all( '/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*(?:=\s*("([^"]*)"|\'([^\']*)\'|([^\s"\'>`=]+)))?/', $tag, $matches, PREG_SET_ORDER ) ) {
            return $attrs;
        }

        $tag_names = array( 'a', 'button', 'iframe', 'img', 'input', 'label', 'link', 'script', 'select', 'source', 'textarea', 'video' );

        foreach ( $matches as $index => $match ) {
            $name = strtolower( (string) $match[1] );
            if ( 0 === $index && in_array( $name, $tag_names, true ) ) {
                continue;
            }

            $value = true;
            if ( isset( $match[3] ) && '' !== $match[3] ) {
                $value = $match[3];
            } elseif ( isset( $match[4] ) && '' !== $match[4] ) {
                $value = $match[4];
            } elseif ( isset( $match[5] ) && '' !== $match[5] ) {
                $value = $match[5];
            }

            if ( is_string( $value ) ) {
                $value = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
            }

            $attrs[ $name ] = $value;
        }

        return $attrs;
    }

    /**
     * Get module summary for the current queried page.
     *
     * @return array<string,mixed>
     */
    private function get_current_page_modules_manifest() {
        $post_id = function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
        if ( $post_id <= 0 || ! function_exists( 'developer_starter_get_raw_page_modules_meta' ) ) {
            return array(
                'count'     => 0,
                'types'     => array(),
                'required'  => array( 'swiper' => false, 'chart' => false ),
                'type_hits' => array(),
            );
        }

        $modules = developer_starter_get_raw_page_modules_meta( $post_id );
        if ( ! is_array( $modules ) ) {
            $modules = array();
        }

        $types = array();
        foreach ( $modules as $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                continue;
            }
            $types[] = sanitize_key( (string) $module['type'] );
        }

        $required = array( 'swiper' => false, 'chart' => false );
        if ( class_exists( '\Developer_Starter\Core\Frontend_Builder_Assets_Service' ) ) {
            $assets_service = new Frontend_Builder_Assets_Service();
            $required = $assets_service->get_required_external_assets_for_modules(
                $modules,
                $assets_service->get_module_dependencies()
            );
        }

        return array(
            'count'     => count( $types ),
            'types'     => array_values( $types ),
            'type_hits' => array_count_values( $types ),
            'required'  => $required,
        );
    }

    /**
     * Detect known optional vendors from the final page.
     *
     * @param string                  $html Full page HTML.
     * @param array<int,array>        $scripts Script assets.
     * @param array<int,array>        $styles Stylesheet assets.
     * @return array<string,bool>
     */
    private function detect_vendor_assets( $html, $scripts, $styles ) {
        $haystack = strtolower( $html );
        foreach ( array_merge( $scripts, $styles ) as $asset ) {
            if ( is_array( $asset ) ) {
                $haystack .= ' ' . strtolower( implode( ' ', array_map( 'strval', $asset ) ) );
            }
        }

        return array(
            'swiper' => false !== strpos( $haystack, 'swiper' ),
            'chart'  => false !== strpos( $haystack, 'chart.js' ) || false !== strpos( $haystack, 'chart-js' ),
        );
    }

    /**
     * Summarize image format usage.
     *
     * @param array<int,array> $images Image assets.
     * @param array<int,array> $sources Source assets.
     * @return array<string,int>
     */
    private function summarize_image_formats( $images, $sources ) {
        $formats = array(
            'jpg'    => 0,
            'png'    => 0,
            'gif'    => 0,
            'svg'    => 0,
            'webp'   => 0,
            'avif'   => 0,
            'raster' => 0,
        );

        foreach ( $images as $image ) {
            if ( ! is_array( $image ) || empty( $image['src'] ) ) {
                continue;
            }
            $extension = $this->get_url_extension( (string) $image['src'] );
            if ( isset( $formats[ $extension ] ) ) {
                $formats[ $extension ]++;
            }
            if ( in_array( $extension, array( 'jpg', 'jpeg', 'png' ), true ) ) {
                $formats['raster']++;
            }
            if ( 'jpeg' === $extension ) {
                $formats['jpg']++;
            }
        }

        foreach ( $sources as $source ) {
            if ( ! is_array( $source ) ) {
                continue;
            }
            $type = isset( $source['type'] ) ? strtolower( (string) $source['type'] ) : '';
            if ( false !== strpos( $type, 'image/webp' ) ) {
                $formats['webp']++;
            }
            if ( false !== strpos( $type, 'image/avif' ) ) {
                $formats['avif']++;
            }

            $srcset = isset( $source['srcset'] ) ? (string) $source['srcset'] : '';
            if ( preg_match_all( '/\.(webp|avif)(?:[\s,?]|$)/i', $srcset, $matches ) ) {
                foreach ( $matches[1] as $extension ) {
                    $extension = strtolower( (string) $extension );
                    if ( isset( $formats[ $extension ] ) ) {
                        $formats[ $extension ]++;
                    }
                }
            }
        }

        return $formats;
    }

    /**
     * Deduplicate assets by URL key.
     *
     * @param array<int,array<string,mixed>> $assets Assets.
     * @param string                         $key URL key.
     * @return array<int,array<string,mixed>>
     */
    private function dedupe_assets( $assets, $key ) {
        $seen = array();
        $out  = array();

        foreach ( $assets as $asset ) {
            if ( ! is_array( $asset ) ) {
                continue;
            }

            $value = isset( $asset[ $key ] ) ? (string) $asset[ $key ] : '';
            if ( '' === $value ) {
                $out[] = $asset;
                continue;
            }

            if ( isset( $seen[ $value ] ) ) {
                continue;
            }

            $seen[ $value ] = true;
            $out[] = $asset;
        }

        return $out;
    }

    /**
     * Make a normalized audit result.
     *
     * @param array<int,array<string,mixed>> $issues Issues.
     * @param array<string,mixed>            $extra Extra payload.
     * @return array<string,mixed>
     */
    private function make_audit_result( $issues, $extra = array() ) {
        $warnings = 0;
        $notices  = 0;
        foreach ( $issues as $issue ) {
            if ( isset( $issue['severity'] ) && 'warning' === $issue['severity'] ) {
                $warnings++;
            } else {
                $notices++;
            }
        }

        return array_merge(
            array(
                'status'         => empty( $issues ) ? 'pass' : 'review',
                'issue_count'    => count( $issues ),
                'warning_count'  => $warnings,
                'notice_count'   => $notices,
                'issues'         => $issues,
            ),
            $extra
        );
    }

    /**
     * Make a normalized issue payload.
     *
     * @param string              $code Issue code.
     * @param string              $message Issue message.
     * @param string              $severity warning|notice.
     * @param array<string,mixed> $context Context.
     * @return array<string,mixed>
     */
    private function make_issue( $code, $message, $severity = 'warning', $context = array() ) {
        return array(
            'code'     => sanitize_key( $code ),
            'severity' => 'warning' === $severity ? 'warning' : 'notice',
            'message'  => (string) $message,
            'context'  => is_array( $context ) ? $context : array(),
        );
    }

    /**
     * Summarize report counts.
     *
     * @param array<string,mixed> $manifest Resource manifest.
     * @param array<string,mixed> $audits Audit results.
     * @return array<string,mixed>
     */
    private function summarize_report( $manifest, $audits ) {
        $issue_count   = 0;
        $warning_count = 0;
        $notice_count  = 0;

        foreach ( $audits as $audit ) {
            if ( ! is_array( $audit ) ) {
                continue;
            }
            $issue_count += isset( $audit['issue_count'] ) ? absint( $audit['issue_count'] ) : 0;
            $warning_count += isset( $audit['warning_count'] ) ? absint( $audit['warning_count'] ) : 0;
            $notice_count += isset( $audit['notice_count'] ) ? absint( $audit['notice_count'] ) : 0;
        }

        $resources = isset( $manifest['resources'] ) && is_array( $manifest['resources'] ) ? $manifest['resources'] : array();

        return array(
            'status'        => $warning_count > 0 ? 'review' : 'pass',
            'issue_count'   => $issue_count,
            'warning_count' => $warning_count,
            'notice_count'  => $notice_count,
            'resources'     => $resources,
        );
    }

    /**
     * Inject JSON report payload before </body>.
     *
     * @param string              $html Full page HTML.
     * @param array<string,mixed> $report Report.
     * @return string
     */
    private function inject_report_payload( $html, $report ) {
        $json_flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $manifest_json = wp_json_encode( isset( $report['manifest'] ) ? $report['manifest'] : array(), $json_flags );
        $audit_json    = wp_json_encode( $report, $json_flags );
        if ( ! is_string( $manifest_json ) || ! is_string( $audit_json ) ) {
            return $html;
        }

        $summary = isset( $report['summary'] ) && is_array( $report['summary'] ) ? $report['summary'] : array();
        $comment = sprintf(
            "\n<!-- Qiling page quality audit: %d issues, %d warnings. -->\n",
            isset( $summary['issue_count'] ) ? absint( $summary['issue_count'] ) : 0,
            isset( $summary['warning_count'] ) ? absint( $summary['warning_count'] ) : 0
        );

        $payload  = $comment;
        $payload .= '<script type="application/json" id="qiling-page-resource-manifest">' . $manifest_json . "</script>\n";
        $payload .= '<script type="application/json" id="qiling-page-quality-audit">' . $audit_json . "</script>\n";

        if ( false !== stripos( $html, '</body>' ) ) {
            $updated = preg_replace( '/<\/body>/i', $payload . '</body>', $html, 1 );
            return is_string( $updated ) ? $updated : $html;
        }

        return $html . $payload;
    }

    /**
     * Whether the auditor should collect this request.
     *
     * @return bool
     */
    private function should_collect() {
        if ( is_admin() || is_feed() ) {
            return false;
        }
        if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
            return false;
        }
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return false;
        }
        if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        $forced = $this->is_query_enabled();
        $enabled = '1' === (string) developer_starter_get_option( self::OPTION_ENABLE, '1' );

        return $forced || $enabled;
    }

    /**
     * Whether to embed JSON report in the final HTML.
     *
     * @return bool
     */
    private function should_embed_report() {
        if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        if ( $this->is_query_enabled() ) {
            return true;
        }

        return '1' === (string) developer_starter_get_option( self::OPTION_EMBED_JSON, '' );
    }

    /**
     * Whether the debug query variable is active.
     *
     * @return bool
     */
    private function is_query_enabled() {
        if ( ! isset( $_GET[ self::QUERY_VAR ] ) ) {
            return false;
        }

        if ( is_array( $_GET[ self::QUERY_VAR ] ) ) {
            return false;
        }

        $value = sanitize_text_field( wp_unslash( $_GET[ self::QUERY_VAR ] ) );
        return '' === $value || '0' !== $value;
    }

    /**
     * Normalize a URL-ish HTML attribute value.
     *
     * @param string $value Raw value.
     * @return string
     */
    private function normalize_url_value( $value ) {
        $value = trim( html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' ) );
        if ( '' === $value ) {
            return '';
        }

        return esc_url_raw( $value );
    }

    /**
     * Current request URL.
     *
     * @return string
     */
    private function get_current_url() {
        $scheme = function_exists( 'is_ssl' ) && is_ssl() ? 'https' : 'http';
        $host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
        $uri    = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        if ( '' === $host ) {
            return function_exists( 'home_url' ) ? home_url( '/' ) : '';
        }

        return esc_url_raw( $scheme . '://' . $host . $uri );
    }

    /**
     * Strip tags and produce a short readable sample.
     *
     * @param string $value Raw HTML/text.
     * @param int    $length Max length.
     * @return string
     */
    private function clean_text_sample( $value, $length = 120 ) {
        $text = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', (string) $value );
        $text = preg_replace( '/<style\b[^>]*>.*?<\/style>/is', '', (string) $text );
        $text = wp_strip_all_tags( (string) $text );
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        $text = preg_replace( '/\s+/u', ' ', $text );
        $text = trim( (string) $text );

        if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) && mb_strlen( $text ) > $length ) {
            return mb_substr( $text, 0, $length ) . '...';
        }

        if ( strlen( $text ) > $length ) {
            return substr( $text, 0, $length ) . '...';
        }

        return $text;
    }

    /**
     * Whether an input/select/textarea is wrapped by a label.
     *
     * @param string $html Full page HTML.
     * @param int    $offset Control offset.
     * @return bool
     */
    private function is_control_wrapped_by_label( $html, $offset ) {
        $before = substr( $html, 0, max( 0, $offset ) );
        $last_open  = strripos( $before, '<label' );
        $last_close = strripos( $before, '</label>' );

        return false !== $last_open && ( false === $last_close || $last_open > $last_close );
    }

    /**
     * Extract color/background hex values from inline style.
     *
     * @param string $style Inline style.
     * @return array<string,string>
     */
    private function extract_inline_style_colors( $style ) {
        $out = array(
            'color'      => '',
            'background' => '',
        );

        $declarations = explode( ';', (string) $style );
        foreach ( $declarations as $declaration ) {
            $parts = explode( ':', $declaration, 2 );
            if ( 2 !== count( $parts ) ) {
                continue;
            }

            $property = strtolower( trim( $parts[0] ) );
            $value    = trim( $parts[1] );

            if ( 'color' === $property && preg_match( '/#[0-9a-f]{3}(?:[0-9a-f]{3})?\b/i', $value, $match ) ) {
                $out['color'] = $match[0];
            }

            if ( in_array( $property, array( 'background', 'background-color' ), true ) && preg_match( '/#[0-9a-f]{3}(?:[0-9a-f]{3})?\b/i', $value, $match ) ) {
                $out['background'] = $match[0];
            }
        }

        return $out;
    }

    /**
     * Whether the component definition is color-like.
     *
     * @param array<string,mixed> $definition Component definition.
     * @return bool
     */
    private static function is_component_color_like_definition( $definition ) {
        $type = isset( $definition['type'] ) ? (string) $definition['type'] : '';
        return in_array( $type, array( 'color', 'paint' ), true );
    }

    /**
     * Whether a style value is driven by CSS variables.
     *
     * @param string $value Style value.
     * @return bool
     */
    private static function is_variable_driven_design_value( $value ) {
        return false !== strpos( (string) $value, 'var(--' );
    }

    /**
     * Whether a style value contains a literal color token.
     *
     * @param string $value Style value.
     * @return bool
     */
    private static function contains_literal_design_color( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return false;
        }

        return (bool) preg_match( '/#(?:[0-9a-f]{3}|[0-9a-f]{6})\b|(?:rgba?|hsla?)\(/i', $value );
    }

    /**
     * Resolve simple CSS variable references for diagnostics.
     *
     * @param string              $value Raw value.
     * @param array<string,mixed> $variables Variable map.
     * @param int                 $depth Recursion depth.
     * @return string
     */
    private static function resolve_diagnostic_color_value( $value, $variables, $depth = 0 ) {
        $value = trim( (string) $value );
        if ( '' === $value || $depth > 5 ) {
            return '';
        }

        if ( false === strpos( $value, 'var(' ) ) {
            return $value;
        }

        $resolved = preg_replace_callback(
            '/var\((--[a-zA-Z0-9_-]+)\)/',
            static function ( $matches ) use ( $variables, $depth ) {
                $variable_key = isset( $matches[1] ) ? (string) $matches[1] : '';
                if ( '' === $variable_key || ! isset( $variables[ $variable_key ] ) ) {
                    return $matches[0];
                }

                return self::resolve_diagnostic_color_value( (string) $variables[ $variable_key ], $variables, $depth + 1 );
            },
            $value
        );

        return is_string( $resolved ) ? trim( $resolved ) : '';
    }

    /**
     * Calculate WCAG contrast ratio for supported CSS colors.
     *
     * @param string $foreground Foreground color.
     * @param string $background Background color.
     * @return float|null
     */
    private static function contrast_ratio( $foreground, $background ) {
        $bg = self::color_to_rgb( $background );
        $fg = self::color_to_rgb( $foreground, $bg );
        if ( null === $fg || null === $bg ) {
            return null;
        }

        $l1 = self::relative_luminance( $fg );
        $l2 = self::relative_luminance( $bg );
        $lighter = max( $l1, $l2 );
        $darker  = min( $l1, $l2 );

        return ( $lighter + 0.05 ) / ( $darker + 0.05 );
    }

    /**
     * Convert a supported CSS color to RGB.
     *
     * @param string              $color CSS color.
     * @param array<int,int>|null $background_rgb Optional background for alpha blending.
     * @return array<int,int>|null
     */
    private static function color_to_rgb( $color, $background_rgb = null ) {
        $color = trim( (string) $color );
        if ( '' === $color ) {
            return null;
        }

        $hex = self::hex_to_rgb( $color );
        if ( null !== $hex ) {
            return $hex;
        }

        if ( preg_match( '/^rgba?\(\s*([0-9.]+%?)\s*,\s*([0-9.]+%?)\s*,\s*([0-9.]+%?)(?:\s*,\s*([0-9.]+))?\s*\)$/i', $color, $matches ) ) {
            $rgb = array(
                self::normalize_rgb_channel( $matches[1] ),
                self::normalize_rgb_channel( $matches[2] ),
                self::normalize_rgb_channel( $matches[3] ),
            );
            $alpha = isset( $matches[4] ) ? max( 0.0, min( 1.0, (float) $matches[4] ) ) : 1.0;

            if ( $alpha < 1 && is_array( $background_rgb ) && count( $background_rgb ) === 3 ) {
                return self::blend_rgb_over_background( $rgb, $background_rgb, $alpha );
            }

            return $rgb;
        }

        return null;
    }

    /**
     * Convert hex color to RGB.
     *
     * @param string $color Color.
     * @return array<int,int>|null
     */
    private static function hex_to_rgb( $color ) {
        $color = trim( (string) $color );
        if ( ! preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color, $match ) ) {
            return null;
        }

        $hex = strtolower( $match[1] );
        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return array(
            hexdec( substr( $hex, 0, 2 ) ),
            hexdec( substr( $hex, 2, 2 ) ),
            hexdec( substr( $hex, 4, 2 ) ),
        );
    }

    /**
     * Calculate relative luminance.
     *
     * @param array<int,int> $rgb RGB channels.
     * @return float
     */
    private static function relative_luminance( $rgb ) {
        $channels = array();
        foreach ( $rgb as $channel ) {
            $value = max( 0, min( 255, (int) $channel ) ) / 255;
            $channels[] = $value <= 0.03928
                ? $value / 12.92
                : pow( ( $value + 0.055 ) / 1.055, 2.4 );
        }

        return ( 0.2126 * $channels[0] ) + ( 0.7152 * $channels[1] ) + ( 0.0722 * $channels[2] );
    }

    /**
     * Normalize a single RGB channel.
     *
     * @param string $channel Channel value.
     * @return int
     */
    private static function normalize_rgb_channel( $channel ) {
        $channel = trim( (string) $channel );

        if ( false !== strpos( $channel, '%' ) ) {
            $percentage = (float) str_replace( '%', '', $channel );
            return (int) round( max( 0, min( 100, $percentage ) ) * 2.55 );
        }

        return (int) round( max( 0, min( 255, (float) $channel ) ) );
    }

    /**
     * Alpha-blend an RGB foreground over an RGB background.
     *
     * @param array<int,int> $foreground_rgb Foreground RGB.
     * @param array<int,int> $background_rgb Background RGB.
     * @param float          $alpha Foreground alpha.
     * @return array<int,int>
     */
    private static function blend_rgb_over_background( $foreground_rgb, $background_rgb, $alpha ) {
        $alpha = max( 0.0, min( 1.0, (float) $alpha ) );

        return array(
            (int) round( ( $foreground_rgb[0] * $alpha ) + ( $background_rgb[0] * ( 1 - $alpha ) ) ),
            (int) round( ( $foreground_rgb[1] * $alpha ) + ( $background_rgb[1] * ( 1 - $alpha ) ) ),
            (int) round( ( $foreground_rgb[2] * $alpha ) + ( $background_rgb[2] * ( 1 - $alpha ) ) ),
        );
    }

    /**
     * Get extension from URL.
     *
     * @param string $url URL.
     * @return string
     */
    private function get_url_extension( $url ) {
        $path = function_exists( 'wp_parse_url' ) ? wp_parse_url( $url, PHP_URL_PATH ) : parse_url( $url, PHP_URL_PATH );
        if ( ! is_string( $path ) || '' === $path ) {
            return '';
        }

        $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
        return 'jpeg' === $extension ? 'jpg' : $extension;
    }

    /**
     * Whether the image source should be ignored by image audits.
     *
     * @param string $src Image URL.
     * @return bool
     */
    private function is_ignored_image_src( $src ) {
        $src = trim( (string) $src );
        return '' === $src || 0 === strpos( $src, 'data:' ) || false !== strpos( $src, '${' );
    }

    /**
     * Whether image is vector-like.
     *
     * @param string $src Image URL.
     * @return bool
     */
    private function is_vector_image( $src ) {
        return 'svg' === $this->get_url_extension( $src ) || false !== stripos( $src, 'data:image/svg' );
    }

    /**
     * Shorten URL for report context.
     *
     * @param string $url URL.
     * @return string
     */
    private function shorten_url_for_report( $url ) {
        $url = (string) $url;
        if ( function_exists( 'home_url' ) ) {
            $home = untrailingslashit( home_url() );
            if ( 0 === strpos( $url, $home ) ) {
                return substr( $url, strlen( $home ) );
            }
        }

        return $this->clean_text_sample( $url, 120 );
    }
}
