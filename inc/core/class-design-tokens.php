<?php
/**
 * Global style service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Centralizes site-wide colors, typography, spacing, radius and shadows.
 */
class Design_Tokens {

    const TOKEN_SCHEMA_VERSION = '5.0.0';
    const STORAGE_OPTION_KEY = 'design_system_v2';
    const LEGACY_CUSTOMIZER_PRIMARY_COLOR = 'developer_starter_primary_color';
    const PAGE_DESIGN_META_KEY = '_qiling_page_design_overrides';
    const PAGE_DESIGN_PRESET_META_KEY = '_qiling_page_design_preset';
    const CATEGORY_DESIGN_PRESET_META_KEY = 'ds_category_design_preset';

    /**
     * Loads extracted design-token data files once per request.
     *
     * @param string $key Data group key.
     * @return array<mixed>
     */
    private static function get_design_token_data( $key ) {
        static $cache = array();
        static $files = null;

        if ( null === $files ) {
            $files = array(
                'component_group_labels' => __DIR__ . '/design-tokens/component-group-labels.php',
                'token_definitions' => __DIR__ . '/design-tokens/token-definitions.php',
                'responsive_device_definitions' => __DIR__ . '/design-tokens/responsive-device-definitions.php',
                'typography_style_definitions' => __DIR__ . '/design-tokens/typography-style-definitions.php',
                'typography_property_definitions' => __DIR__ . '/design-tokens/typography-property-definitions.php',
                'layout_field_definitions' => __DIR__ . '/design-tokens/layout-field-definitions.php',
                'page_structure_field_definitions' => __DIR__ . '/design-tokens/page-structure-field-definitions.php',
                'page_structure_field_aliases' => __DIR__ . '/design-tokens/page-structure-field-aliases.php',
                'component_style_definitions' => __DIR__ . '/design-tokens/component-style-definitions.php',
                'default_palette_tokens' => __DIR__ . '/design-tokens/default-palette-tokens.php',
                'palette_preset_token_keys' => __DIR__ . '/design-tokens/palette-preset-token-keys.php',
                'system_style_presets' => __DIR__ . '/design-tokens/system-style-presets.php',
                'design_token_option_map' => __DIR__ . '/design-tokens/design-token-option-map.php',
                'design_component_option_map' => __DIR__ . '/design-tokens/design-component-option-map.php',
                'design_token_schema' => __DIR__ . '/design-tokens/design-token-schema.php',
                'design_component_schema' => __DIR__ . '/design-tokens/design-component-schema.php',
                'default_typography_system' => __DIR__ . '/design-tokens/default-typography-system.php',
                'default_layout_system' => __DIR__ . '/design-tokens/default-layout-system.php',
                'foundation_style_aliases' => __DIR__ . '/design-tokens/foundation-style-aliases.php',
            );
        }

        if ( ! is_string( $key ) || ! isset( $files[ $key ] ) ) {
            return array();
        }

        if ( array_key_exists( $key, $cache ) ) {
            return $cache[ $key ];
        }

        if ( ! is_readable( $files[ $key ] ) ) {
            $cache[ $key ] = array();
            return $cache[ $key ];
        }

        $data = require $files[ $key ];
        $cache[ $key ] = is_array( $data ) ? $data : array();

        return $cache[ $key ];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function get_style_presets( $options = null ) {
        $presets = array_merge(
            self::get_system_style_presets(),
            self::get_user_style_presets( $options )
        );

        if ( function_exists( 'apply_filters' ) ) {
            $presets = apply_filters( 'developer_starter_design_token_presets', $presets, $options );
        }

        return is_array( $presets ) ? $presets : array();
    }

    /**
     * @return array<string,string>
     */
    public static function get_preset_choices( $options = null ) {
        $choices = array();
        foreach ( self::get_style_presets( $options ) as $key => $preset ) {
            $choices[ $key ] = isset( $preset['label'] ) ? (string) $preset['label'] : $key;
        }

        return $choices;
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,string>
     */
    public static function get_context_preset_choices( $options = null ) {
        return array_merge(
            array( 'inherit' => __( '继承全局配色', 'developer-starter' ) ),
            self::get_preset_choices( $options )
        );
    }

    /**
     * @param mixed                    $preset_key Raw preset key.
     * @param array<string,mixed>|null $options    Optional option source.
     * @return string
     */
    public static function sanitize_context_preset_key( $preset_key, $options = null ) {
        $preset_key = is_scalar( $preset_key ) ? sanitize_key( (string) $preset_key ) : '';
        if ( '' === $preset_key || 'inherit' === $preset_key ) {
            return '';
        }

        $presets = self::get_style_presets( $options );
        return isset( $presets[ $preset_key ] ) ? $preset_key : '';
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,array<int,string>>
     */
    public static function get_context_preset_rules( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        $rules   = isset( $options['design_preset_context_rules'] ) ? $options['design_preset_context_rules'] : array();

        return self::sanitize_context_preset_rules( $rules, $options );
    }

    /**
     * @param mixed                    $rules   Raw context rules.
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,array<int,string>>
     */
    public static function sanitize_context_preset_rules( $rules, $options = null ) {
        $normalized = array(
            'pages'      => array(),
            'categories' => array(),
        );

        if ( ! is_array( $rules ) ) {
            return $normalized;
        }

        $sources = array(
            'pages'      => isset( $rules['pages'] ) && is_array( $rules['pages'] ) ? $rules['pages'] : array(),
            'categories' => isset( $rules['categories'] ) && is_array( $rules['categories'] ) ? $rules['categories'] : array(),
        );
        if ( empty( $sources['categories'] ) && isset( $rules['terms'] ) && is_array( $rules['terms'] ) ) {
            $sources['categories'] = $rules['terms'];
        }

        foreach ( $sources as $group_key => $items ) {
            foreach ( $items as $item_key => $item ) {
                $object_id = 0;
                $preset    = '';

                if ( is_array( $item ) ) {
                    if ( isset( $item['id'] ) ) {
                        $object_id = absint( $item['id'] );
                    } elseif ( isset( $item['page_id'] ) ) {
                        $object_id = absint( $item['page_id'] );
                    } elseif ( isset( $item['term_id'] ) ) {
                        $object_id = absint( $item['term_id'] );
                    } elseif ( isset( $item['category_id'] ) ) {
                        $object_id = absint( $item['category_id'] );
                    } elseif ( is_numeric( $item_key ) ) {
                        $object_id = absint( $item_key );
                    }

                    $preset = isset( $item['preset'] ) ? $item['preset'] : '';
                } else {
                    $object_id = is_numeric( $item_key ) ? absint( $item_key ) : 0;
                    $preset    = $item;
                }

                $preset = self::sanitize_context_preset_key( $preset, $options );
                if ( $object_id <= 0 || '' === $preset ) {
                    continue;
                }

                $normalized[ $group_key ][ $object_id ] = $preset;
            }
        }

        ksort( $normalized['pages'] );
        ksort( $normalized['categories'] );

        return $normalized;
    }

    /**
     * @return array<string,string>
     */
    private static function get_component_group_labels() {
        return self::get_design_token_data( 'component_group_labels' );
    }

    /**
     * @return array<int,array<string,string>>
     */
    public static function get_token_definitions() {
        $definitions = self::get_design_token_data( 'token_definitions' );

        if ( function_exists( 'apply_filters' ) ) {
            $definitions = apply_filters( 'developer_starter_design_token_definitions', $definitions );
        }

        return is_array( $definitions ) ? $definitions : array();
    }

    /**
     * @return array<string,array<string,string>>
     */
    public static function get_responsive_device_definitions() {
        return self::get_design_token_data( 'responsive_device_definitions' );
    }

    /**
     * @return array<string,array<string,string>>
     */
    public static function get_typography_style_definitions() {
        return self::get_design_token_data( 'typography_style_definitions' );
    }

    /**
     * @return array<string,array<string,string>>
     */
    public static function get_typography_property_definitions() {
        return self::get_design_token_data( 'typography_property_definitions' );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function get_layout_field_definitions() {
        return self::get_design_token_data( 'layout_field_definitions' );
    }

    /**
     * @return array<string,mixed>
     */
    public static function get_page_design_field_definitions() {
        return array(
            'palette' => array(
                'label'  => __( '页面配色覆盖', 'developer-starter' ),
                'fields' => array(
                    'primary'    => array( 'label' => __( '页面主色', 'developer-starter' ), 'type' => 'color' ),
                    'secondary'  => array( 'label' => __( '页面辅助色', 'developer-starter' ), 'type' => 'color' ),
                    'accent'     => array( 'label' => __( '页面点缀色', 'developer-starter' ), 'type' => 'color' ),
                    'success'    => array( 'label' => __( '页面成功色', 'developer-starter' ), 'type' => 'color' ),
                    'info'       => array( 'label' => __( '页面信息色', 'developer-starter' ), 'type' => 'color' ),
                    'warning'    => array( 'label' => __( '页面警示色', 'developer-starter' ), 'type' => 'color' ),
                    'error'      => array( 'label' => __( '页面错误色', 'developer-starter' ), 'type' => 'color' ),
                    'overlay'    => array( 'label' => __( '页面遮罩色', 'developer-starter' ), 'type' => 'color' ),
                    'background' => array( 'label' => __( '页面背景', 'developer-starter' ), 'type' => 'color' ),
                    'surface'    => array( 'label' => __( '页面卡片背景', 'developer-starter' ), 'type' => 'color' ),
                    'surface_alt' => array( 'label' => __( '页面浅色区块', 'developer-starter' ), 'type' => 'color' ),
                    'text'       => array( 'label' => __( '页面正文颜色', 'developer-starter' ), 'type' => 'color' ),
                    'text_muted' => array( 'label' => __( '页面弱化文字', 'developer-starter' ), 'type' => 'color' ),
                    'heading'    => array( 'label' => __( '页面标题颜色', 'developer-starter' ), 'type' => 'color' ),
                    'border'     => array( 'label' => __( '页面边框颜色', 'developer-starter' ), 'type' => 'color' ),
                    'dark_bg'    => array( 'label' => __( '页面暗色背景', 'developer-starter' ), 'type' => 'color' ),
                    'dark_surface' => array( 'label' => __( '页面暗色卡片背景', 'developer-starter' ), 'type' => 'color' ),
                    'dark_text'  => array( 'label' => __( '页面暗色正文', 'developer-starter' ), 'type' => 'color' ),
                    'dark_text_muted' => array( 'label' => __( '页面暗色弱化文字', 'developer-starter' ), 'type' => 'color' ),
                    'dark_border' => array( 'label' => __( '页面暗色边框', 'developer-starter' ), 'type' => 'color' ),
                ),
            ),
            'layout'  => array(
                'label'  => __( '页面布局覆盖', 'developer-starter' ),
                'fields' => array(
                    'container_width' => self::get_layout_field_definitions()['container_width'],
                    'section_spacing' => self::get_layout_field_definitions()['section_spacing'],
                    'grid_gap'        => self::get_layout_field_definitions()['grid_gap'],
                    'layout_mode'     => self::get_layout_field_definitions()['layout_mode'],
                ),
            ),
            'structure' => array(
                'label'  => __( '页面圆角与动效', 'developer-starter' ),
                'fields' => self::get_page_structure_field_definitions(),
            ),
            'typography' => array(
                'label'      => __( '页面排版覆盖', 'developer-starter' ),
                'styles'     => self::get_typography_style_definitions(),
                'properties' => self::get_typography_property_definitions(),
            ),
            'component_styles' => array(
                'label'  => __( '页面组件覆盖', 'developer-starter' ),
                'groups' => self::get_page_component_style_groups(),
            ),
        );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private static function get_page_component_style_groups() {
        $group_labels = self::get_component_group_labels();
        $schema = self::get_design_component_schema();
        $groups = array();

        foreach ( $group_labels as $group_key => $group_label ) {
            $groups[ $group_key ] = array(
                'label'  => $group_label,
                'fields' => array(),
            );
        }

        foreach ( self::get_component_style_definitions() as $definition ) {
            $group_key = isset( $definition['group'] ) ? sanitize_key( (string) $definition['group'] ) : '';
            $style_key = isset( $definition['key'] ) ? sanitize_key( (string) $definition['key'] ) : '';
            if ( '' === $group_key || '' === $style_key || ! isset( $groups[ $group_key ] ) ) {
                continue;
            }

            $groups[ $group_key ]['fields'][ $style_key ] = array(
                'label'  => isset( $definition['label'] ) ? (string) $definition['label'] : $style_key,
                'type'   => isset( $schema[ $style_key ] ) ? (string) $schema[ $style_key ] : 'text',
                'cssVar' => isset( $definition['cssVar'] ) ? (string) $definition['cssVar'] : '',
            );
        }

        return $groups;
    }

    /**
     * @return array<string,array<string,string>>
     */
    private static function get_page_structure_field_definitions() {
        return self::get_design_token_data( 'page_structure_field_definitions' );
    }

    /**
     * @return array<string,string>
     */
    private static function get_page_structure_field_aliases() {
        return self::get_design_token_data( 'page_structure_field_aliases' );
    }

    /**
     * @return array<string,string>
     */
    private static function get_page_typography_property_aliases() {
        return array(
            'font_size'      => 'fontSize',
            'line_height'    => 'lineHeight',
            'font_weight'    => 'fontWeight',
            'letter_spacing' => 'letterSpacing',
        );
    }

    /**
     * @return array<string,array<string,array<string,string>>>
     */
    private static function get_empty_page_typography_overrides() {
        $overrides = array();

        foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
            $overrides[ $style_key ] = array();
            foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                $overrides[ $style_key ][ $device_key ] = array();
                foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                    $overrides[ $style_key ][ $device_key ][ $property_key ] = '';
                }
            }
        }

        return $overrides;
    }

    /**
     * @return string
     */
    public static function get_page_design_meta_key() {
        return self::PAGE_DESIGN_META_KEY;
    }

    /**
     * @return string
     */
    public static function get_page_design_preset_meta_key() {
        return self::PAGE_DESIGN_PRESET_META_KEY;
    }

    /**
     * @return string
     */
    public static function get_category_design_preset_meta_key() {
        return self::CATEGORY_DESIGN_PRESET_META_KEY;
    }

    /**
     * @return array<int,array<string,string>>
     */
    public static function get_component_style_definitions() {
        $definitions = self::get_design_token_data( 'component_style_definitions' );
        $style_types = self::get_component_style_type_map();

        foreach ( $definitions as $index => $definition ) {
            $style_key = isset( $definition['key'] ) ? (string) $definition['key'] : '';
            $definitions[ $index ]['type'] = isset( $style_types[ $style_key ] ) ? $style_types[ $style_key ] : 'text';
        }

        if ( function_exists( 'apply_filters' ) ) {
            $definitions = apply_filters( 'developer_starter_component_style_definitions', $definitions );
        }

        return is_array( $definitions ) ? $definitions : array();
    }

    /**
     * @param string                  $preset_key Preset key.
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,string>
     */
    public static function get_preset_token_values( $preset_key = 'default', $options = null ) {
        $preset_key = is_scalar( $preset_key ) ? sanitize_key( (string) $preset_key ) : 'default';
        $presets    = self::get_style_presets( $options );

        if ( ! isset( $presets[ $preset_key ] ) ) {
            $preset_key = 'default';
        }

        $tokens        = self::get_default_palette_tokens();
        $preset_tokens = isset( $presets[ $preset_key ]['tokens'] ) && is_array( $presets[ $preset_key ]['tokens'] )
            ? $presets[ $preset_key ]['tokens']
            : array();

        foreach ( self::get_palette_preset_token_keys() as $token_key ) {
            if ( ! array_key_exists( $token_key, $preset_tokens ) ) {
                continue;
            }

            $value = self::sanitize_token_value_by_key( $token_key, $preset_tokens[ $token_key ] );
            if ( '' !== $value ) {
                $tokens[ $token_key ] = $value;
            }
        }

        return $tokens;
    }

    /**
     * @param string                  $preset_key Preset key.
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,mixed>
     */
    private static function get_preset_payload( $preset_key = 'default', $options = null ) {
        $preset_key = is_scalar( $preset_key ) ? sanitize_key( (string) $preset_key ) : 'default';
        $presets    = self::get_style_presets( $options );

        if ( ! isset( $presets[ $preset_key ] ) ) {
            $preset_key = 'default';
        }

        $preset = isset( $presets[ $preset_key ] ) && is_array( $presets[ $preset_key ] )
            ? $presets[ $preset_key ]
            : array();

        return array(
            'id'               => $preset_key,
            'label'            => isset( $preset['label'] ) ? (string) $preset['label'] : $preset_key,
            'source'           => isset( $preset['source'] ) ? (string) $preset['source'] : 'system',
            'tokens'           => isset( $preset['tokens'] ) && is_array( $preset['tokens'] )
                ? self::normalize_palette_preset_tokens( $preset['tokens'] )
                : self::get_default_palette_tokens(),
            'typography_system' => isset( $preset['typography_system'] ) && is_array( $preset['typography_system'] )
                ? self::normalize_typography_system( $preset['typography_system'] )
                : array(),
            'layout_system'    => isset( $preset['layout_system'] ) && is_array( $preset['layout_system'] )
                ? self::normalize_layout_system( $preset['layout_system'] )
                : array(),
            'component_styles' => isset( $preset['component_styles'] ) && is_array( $preset['component_styles'] )
                ? self::sanitize_component_style_map( $preset['component_styles'] )
                : array(),
        );
    }

    /**
     * @return array<string,array<string,array<string,string>>>
     */
    public static function get_default_typography_system_values() {
        return self::get_default_typography_system();
    }

    /**
     * @return array<string,mixed>
     */
    public static function get_default_layout_system_values() {
        return self::get_default_layout_system();
    }

    /**
     * @param array<string,string>|null $tokens Current tokens.
     * @return array<string,string>
     */
    public static function get_default_component_styles( $tokens = null ) {
        $tokens = is_array( $tokens ) ? $tokens : self::get_current_tokens();
        return self::get_base_component_styles( $tokens );
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,mixed>
     */
    public static function get_client_payload( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        $design_system = self::get_normalized_design_system_v2( $options );
        $tokens = self::get_current_tokens( $options );
        $variables = self::get_css_variables( $tokens );
        $typography_system = self::get_current_typography_system( $options );
        $layout_system = self::get_current_layout_system( $options );
        $component_styles = self::get_current_component_styles( $options, $tokens );
        $component_variables = self::get_component_css_variables( $component_styles );
        $presets = self::get_style_presets( $options );
        $system_presets = self::get_system_style_presets();
        $custom_presets = self::get_user_style_presets( $options );
        $preset_key = isset( $design_system['preset'] ) ? sanitize_key( (string) $design_system['preset'] ) : self::get_current_preset_key( $options );
        $preset_label = isset( $presets[ $preset_key ]['label'] ) ? (string) $presets[ $preset_key ]['label'] : $preset_key;
        $preset_source = isset( $presets[ $preset_key ]['source'] ) ? (string) $presets[ $preset_key ]['source'] : 'system';

        return array(
            'schemaVersion'    => self::TOKEN_SCHEMA_VERSION,
            'enabled'          => ! empty( $design_system['enabled'] ),
            'preset'           => $preset_key,
            'presetLabel'      => $preset_label,
            'presetSource'     => $preset_source,
            'presets'          => self::get_preset_choices( $options ),
            'systemPresets'    => $system_presets,
            'customPresets'    => $custom_presets,
            'tokens'           => $tokens,
            'cssVariables'     => $variables,
            'typographySystem' => $typography_system,
            'layoutSystem'     => $layout_system,
            'responsiveDevices' => self::get_responsive_device_definitions(),
            'typographyDefinitions' => self::get_typography_style_definitions(),
            'typographyPropertyDefinitions' => self::get_typography_property_definitions(),
            'layoutDefinitions' => self::get_layout_field_definitions(),
            'pageDesignDefinitions' => self::get_page_design_field_definitions(),
            'pageDesignDefaults' => self::format_page_design_overrides_for_builder( self::get_default_page_design_overrides() ),
            'pageDesignMetaKey' => self::PAGE_DESIGN_META_KEY,
            'tokenDefinitions' => self::get_token_definitions(),
            'tokenOptionMap'   => self::get_design_token_option_map(),
            'tokenSchema'      => self::get_design_token_schema(),
            'componentStyles' => $component_styles,
            'componentCssVariables' => $component_variables,
            'componentStyleDefinitions' => self::get_component_style_definitions(),
            'componentOptionMap' => self::get_design_component_option_map(),
            'componentSchema'  => self::get_design_component_schema(),
            'storageKey'       => self::STORAGE_OPTION_KEY,
        );
    }

    /**
     * 获取用于导出/传输的完整 design_system_v2 存储结构。
     *
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,mixed>
     */
    public static function get_storage_payload( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();

        return self::get_normalized_design_system_v2( $options );
    }

    /**
     * 基于完整 design_system_v2 构建兼容 option 片段。
     *
     * @param array<string,mixed>|null $design_system Optional design system payload.
     * @param array<string,mixed>|null $options       Optional option source.
     * @return array<string,mixed>
     */
    public static function get_compatibility_option_payload( $design_system = null, $options = null ) {
        if ( ! is_array( $design_system ) || empty( $design_system ) ) {
            $design_system = self::get_storage_payload( $options );
        }

        return self::build_compatibility_option_sync_from_design_system( $design_system );
    }

    /**
     * 在不覆盖 design_system_v2 已有字段的前提下，使用兼容 options 补齐缺失值。
     *
     * @param array<string,mixed> $storage_payload  design_system_v2 存储结构。
     * @param array<string,mixed> $option_overrides 兼容 option 片段。
     * @param array<string,mixed> $existing_options 当前站点选项。
     * @return array<string,mixed>
     */
    public static function merge_storage_payload_with_options( $storage_payload, $option_overrides = array(), $existing_options = array() ) {
        if ( ! is_array( $storage_payload ) || empty( $storage_payload ) ) {
            return array();
        }

        $option_overrides = is_array( $option_overrides ) ? $option_overrides : array();
        $base_design_system = self::get_stored_design_system_v2(
            array(
                self::STORAGE_OPTION_KEY => $storage_payload,
            )
        );
        $merged_design_system = empty( $option_overrides )
            ? array()
            : self::get_normalized_design_system_v2( $option_overrides );

        if ( empty( $base_design_system ) ) {
            return $merged_design_system;
        }

        return self::merge_design_system_payload_preserving_base( $base_design_system, $merged_design_system );
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,mixed>
     */
    public static function get_prompt_context( $options = null ) {
        $payload = self::get_client_payload( $options );
        $tokens = isset( $payload['tokens'] ) && is_array( $payload['tokens'] ) ? $payload['tokens'] : array();
        $typography_system = isset( $payload['typographySystem'] ) && is_array( $payload['typographySystem'] ) ? $payload['typographySystem'] : array();
        $layout_system = isset( $payload['layoutSystem'] ) && is_array( $payload['layoutSystem'] ) ? $payload['layoutSystem'] : array();

        return array(
            'schema_version' => isset( $payload['schemaVersion'] ) ? (string) $payload['schemaVersion'] : self::TOKEN_SCHEMA_VERSION,
            'enabled'        => ! empty( $payload['enabled'] ),
            'preset'         => isset( $payload['preset'] ) ? (string) $payload['preset'] : 'default',
            'preset_label'   => isset( $payload['presetLabel'] ) ? (string) $payload['presetLabel'] : '',
            'tokens'         => array(
                'primary'         => isset( $tokens['primary'] ) ? (string) $tokens['primary'] : '',
                'primary_hover'   => isset( $tokens['primary_hover'] ) ? (string) $tokens['primary_hover'] : '',
                'secondary'       => isset( $tokens['secondary'] ) ? (string) $tokens['secondary'] : '',
                'accent'          => isset( $tokens['accent'] ) ? (string) $tokens['accent'] : '',
                'success'         => isset( $tokens['success'] ) ? (string) $tokens['success'] : '',
                'info'            => isset( $tokens['info'] ) ? (string) $tokens['info'] : '',
                'warning'         => isset( $tokens['warning'] ) ? (string) $tokens['warning'] : '',
                'error'           => isset( $tokens['error'] ) ? (string) $tokens['error'] : '',
                'overlay'         => isset( $tokens['overlay'] ) ? (string) $tokens['overlay'] : '',
                'text'            => isset( $tokens['text'] ) ? (string) $tokens['text'] : '',
                'text_muted'      => isset( $tokens['text_muted'] ) ? (string) $tokens['text_muted'] : '',
                'heading'         => isset( $tokens['heading'] ) ? (string) $tokens['heading'] : '',
                'background'      => isset( $tokens['background'] ) ? (string) $tokens['background'] : '',
                'surface'         => isset( $tokens['surface'] ) ? (string) $tokens['surface'] : '',
                'surface_alt'     => isset( $tokens['surface_alt'] ) ? (string) $tokens['surface_alt'] : '',
                'border'          => isset( $tokens['border'] ) ? (string) $tokens['border'] : '',
                'dark_bg'         => isset( $tokens['dark_bg'] ) ? (string) $tokens['dark_bg'] : '',
                'dark_surface'    => isset( $tokens['dark_surface'] ) ? (string) $tokens['dark_surface'] : '',
                'dark_text'       => isset( $tokens['dark_text'] ) ? (string) $tokens['dark_text'] : '',
                'dark_text_muted' => isset( $tokens['dark_text_muted'] ) ? (string) $tokens['dark_text_muted'] : '',
                'dark_border'     => isset( $tokens['dark_border'] ) ? (string) $tokens['dark_border'] : '',
                'card_radius'     => isset( $tokens['card_radius'] ) ? (string) $tokens['card_radius'] : '',
                'button_radius'   => isset( $tokens['button_radius'] ) ? (string) $tokens['button_radius'] : '',
                'section_padding' => isset( $tokens['section_padding'] ) ? (string) $tokens['section_padding'] : '',
                'grid_gap'        => isset( $tokens['grid_gap'] ) ? (string) $tokens['grid_gap'] : '',
                'container_width' => isset( $tokens['container_width'] ) ? (string) $tokens['container_width'] : '',
            ),
            'typography_system' => $typography_system,
            'layout_system'     => $layout_system,
            'recommended_css_variables' => array(
                'primary'       => 'var(--color-primary)',
                'primary_hover' => 'var(--color-primary-hover)',
                'secondary'     => 'var(--color-secondary)',
                'accent'        => 'var(--color-accent)',
                'brand_gradient' => 'var(--qiling-gradient-brand)',
                'brand_gradient_soft' => 'var(--qiling-gradient-brand-soft)',
                'accent_gradient' => 'var(--qiling-gradient-accent)',
                'success'       => 'var(--color-success)',
                'success_gradient' => 'var(--qiling-gradient-success)',
                'info'          => 'var(--color-info)',
                'info_gradient' => 'var(--qiling-gradient-info)',
                'warning'       => 'var(--color-warning)',
                'warning_gradient' => 'var(--qiling-gradient-warning)',
                'error'         => 'var(--color-error)',
                'error_gradient' => 'var(--qiling-gradient-error)',
                'overlay'       => 'var(--color-overlay)',
                'text'          => 'var(--color-text)',
                'muted_text'    => 'var(--color-text-muted)',
                'heading'       => 'var(--color-heading)',
                'surface'       => 'var(--color-surface)',
                'surface_alt'   => 'var(--color-surface-alt)',
                'border'        => 'var(--color-border)',
                'dark_bg'       => 'var(--qiling-dark-bg)',
                'dark_surface'  => 'var(--qiling-dark-surface)',
                'dark_text'     => 'var(--qiling-dark-text)',
                'dark_text_muted' => 'var(--qiling-dark-text-muted)',
                'dark_border'   => 'var(--qiling-dark-border)',
                'card_radius'   => 'var(--qiling-card-radius)',
                'button_radius' => 'var(--qiling-button-radius)',
                'shadow'        => 'var(--shadow-md)',
            ),
            'component_styles' => array(
                'button_bg' => 'var(--qiling-component-button-bg)',
                'button_text' => 'var(--qiling-component-button-text)',
                'card_bg' => 'var(--qiling-component-card-bg)',
                'form_input_bg' => 'var(--qiling-component-form-input-bg)',
                'auth_action_bg' => 'var(--qiling-component-auth-action-bg)',
                'auth_code_bg' => 'var(--qiling-component-auth-code-bg)',
                'auth_slider_handle_bg' => 'var(--qiling-component-auth-slider-handle-bg)',
                'module_title_color' => 'var(--qiling-component-module-title-color)',
                'post_card_bg' => 'var(--qiling-component-post-card-bg)',
                'post_card_title_color' => 'var(--qiling-component-post-card-title-color)',
                'header_bg' => 'var(--qiling-component-header-bg)',
                'nav_link' => 'var(--qiling-component-nav-link)',
                'dropdown_bg' => 'var(--qiling-component-dropdown-bg)',
                'mobile_nav_bg' => 'var(--qiling-component-mobile-nav-bg)',
                'footer_bg' => 'var(--qiling-component-footer-bg)',
                'sidebar_bg' => 'var(--qiling-component-sidebar-bg)',
                'woo_card_bg' => 'var(--qiling-component-woo-card-bg)',
            ),
            'rules' => array(
                '模块颜色字段优先使用 recommended_css_variables 中的 var() 值，除非用户明确要求固定颜色',
                '品牌主色用于主要按钮、链接和强调元素；点缀色用于价格、徽标、提示和重点数据',
                '页面背景、卡片背景、边框、文字颜色应保持来自同一预设，避免每个模块各自使用无关配色',
                '圆角、阴影、区块间距优先沿用全局样式，保持整页一致',
                '按钮、卡片、表单、页头导航、下拉层、分页、侧栏、页脚、Woo 卡片优先使用全局组件样式变量，模块单独设置仅用于局部覆盖',
            ),
        );
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,array<string,array<string,string>>>
     */
    public static function get_current_typography_system( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        if ( array_key_exists( 'design_typography_system', $options ) && is_array( $options['design_typography_system'] ) ) {
            return self::normalize_typography_system( $options['design_typography_system'] );
        }

        $stored = self::get_stored_design_system_v2( $options );
        if ( isset( $stored['typography_system'] ) && is_array( $stored['typography_system'] ) ) {
            return self::normalize_typography_system( $stored['typography_system'] );
        }

        $preset = self::get_preset_payload( self::get_current_preset_key( $options ), $options );

        if ( ! empty( $preset['typography_system'] ) && is_array( $preset['typography_system'] ) ) {
            return self::normalize_typography_system( $preset['typography_system'] );
        }

        return self::get_default_typography_system();
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,mixed>
     */
    public static function get_current_layout_system( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        if ( array_key_exists( 'design_layout_system', $options ) && is_array( $options['design_layout_system'] ) ) {
            return self::normalize_layout_system( $options['design_layout_system'] );
        }

        $stored = self::get_stored_design_system_v2( $options );
        if ( isset( $stored['layout_system'] ) && is_array( $stored['layout_system'] ) ) {
            return self::normalize_layout_system( $stored['layout_system'] );
        }

        $preset = self::get_preset_payload( self::get_current_preset_key( $options ), $options );

        if ( ! empty( $preset['layout_system'] ) && is_array( $preset['layout_system'] ) ) {
            return self::normalize_layout_system( $preset['layout_system'] );
        }

        return self::get_default_layout_system();
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,string>
     */
    public static function get_current_tokens( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        $design_system = self::get_normalized_design_system_v2( $options );
        $preset_payload = self::get_preset_payload(
            isset( $design_system['preset'] ) ? (string) $design_system['preset'] : self::get_current_preset_key( $options ),
            $options
        );
        $preset_key = isset( $preset_payload['id'] ) ? (string) $preset_payload['id'] : 'default';
        $preset = isset( $preset_payload['tokens'] ) && is_array( $preset_payload['tokens'] )
            ? $preset_payload['tokens']
            : array();

        $tokens = array_merge(
            self::get_structural_defaults(),
            $preset
        );

        if ( ! empty( $design_system['tokens'] ) && is_array( $design_system['tokens'] ) ) {
            $tokens = array_merge( $tokens, $design_system['tokens'] );
        }

        $typography_system = self::get_current_typography_system( $options );
        $layout_system = self::get_current_layout_system( $options );

        $tokens = array_merge(
            $tokens,
            self::flatten_typography_system_tokens( $typography_system ),
            self::flatten_layout_system_tokens( $layout_system )
        );
        $tokens['font_size_base'] = $typography_system['body']['desktop']['font_size'];
        $tokens['line_height_base'] = $typography_system['body']['desktop']['line_height'];
        $tokens['container_width'] = $layout_system['container_width']['desktop'];
        $tokens['section_padding'] = $layout_system['section_spacing']['desktop'];
        $tokens['grid_gap'] = $layout_system['grid_gap']['desktop'];
        $tokens['breakpoint_tablet'] = $layout_system['breakpoints']['tablet'];
        $tokens['breakpoint_mobile'] = $layout_system['breakpoints']['mobile'];
        $tokens['layout_mode'] = $layout_system['layout_mode'];

        $primary = isset( $tokens['primary'] ) ? $tokens['primary'] : '#2563eb';
        if ( self::is_hex_color( $primary ) ) {
            $primary_hover = isset( $tokens['primary_hover'] ) && self::is_hex_color( $tokens['primary_hover'] )
                ? $tokens['primary_hover']
                : self::shift_hex_color( $primary, -16 );
            $tokens['primary_hover'] = $primary_hover;
            $tokens['primary_dark'] = $primary_hover;
            $tokens['primary_light'] = self::shift_hex_color( $primary, 12 );
            $tokens['primary_rgb'] = self::hex_to_rgb_string( $primary );
        } else {
            $tokens['primary_hover'] = isset( $tokens['primary_hover'] ) ? $tokens['primary_hover'] : $primary;
            $tokens['primary_dark'] = isset( $tokens['primary_dark'] ) ? $tokens['primary_dark'] : $primary;
            $tokens['primary_light'] = isset( $tokens['primary_light'] ) ? $tokens['primary_light'] : $primary;
            $tokens['primary_rgb'] = '37, 99, 235';
        }

        $derived_color_map = array(
            'accent'  => -12,
            'success' => -14,
            'info'    => -14,
            'warning' => -14,
            'error'   => -14,
        );
        foreach ( $derived_color_map as $token_key => $shift ) {
            $token_value = isset( $tokens[ $token_key ] ) ? $tokens[ $token_key ] : '';
            $derived_key = $token_key . '_dark';
            if ( self::is_hex_color( $token_value ) ) {
                $tokens[ $derived_key ] = self::shift_hex_color( $token_value, $shift );
            } else {
                $tokens[ $derived_key ] = isset( $tokens[ $derived_key ] ) ? $tokens[ $derived_key ] : $token_value;
            }
        }

        $rgb_fallbacks = array(
            'accent'      => '5, 150, 105',
            'success'     => '22, 163, 74',
            'info'        => '14, 165, 233',
            'warning'     => '245, 158, 11',
            'error'       => '220, 38, 38',
            'neutral_400' => '148, 163, 184',
        );
        foreach ( $rgb_fallbacks as $token_key => $fallback_rgb ) {
            $token_value = isset( $tokens[ $token_key ] ) ? $tokens[ $token_key ] : '';
            $rgb_key = $token_key . '_rgb';
            if ( self::is_hex_color( $token_value ) ) {
                $tokens[ $rgb_key ] = self::hex_to_rgb_string( $token_value );
            } else {
                $tokens[ $rgb_key ] = isset( $tokens[ $rgb_key ] ) ? $tokens[ $rgb_key ] : $fallback_rgb;
            }
        }

        if ( function_exists( 'apply_filters' ) ) {
            $tokens = apply_filters( 'developer_starter_design_tokens', $tokens, $options, $preset_key );
        }

        return is_array( $tokens ) ? array_map( 'strval', $tokens ) : array();
    }

    /**
     * @param int                      $post_id 页面 ID。
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,string>
     */
    public static function get_current_tokens_for_page( $post_id, $options = null ) {
        $post_id = absint( $post_id );
        $options = is_array( $options ) ? $options : self::get_theme_options();
        if ( $post_id <= 0 ) {
            return self::get_current_tokens( $options );
        }

        $resolved_options = self::build_options_with_page_design_overrides( $options, $post_id );
        return self::get_current_tokens( $resolved_options );
    }

    /**
     * @param int                      $post_id 页面 ID。
     * @param array<string,mixed>|null $options Optional option source.
     * @return array<string,string>
     */
    public static function get_current_component_styles_for_page( $post_id, $options = null ) {
        $post_id = absint( $post_id );
        $options = is_array( $options ) ? $options : self::get_theme_options();
        if ( $post_id <= 0 ) {
            return self::get_current_component_styles( $options );
        }

        $resolved_options = self::build_options_with_page_design_overrides( $options, $post_id );
        $tokens = self::get_current_tokens( $resolved_options );

        return self::get_current_component_styles( $resolved_options, $tokens );
    }

    /**
     * @param array<string,string>|null $tokens Optional token set.
     * @return array<string,string>
     */
    public static function get_css_variables( $tokens = null ) {
        $tokens = is_array( $tokens ) ? $tokens : self::get_current_tokens();
        $variables = array(
            '--qiling-design-token-version' => '"' . self::TOKEN_SCHEMA_VERSION . '"',
            '--color-primary'               => $tokens['primary'],
            '--color-primary-hover'         => $tokens['primary_hover'],
            '--color-primary-dark'          => $tokens['primary_dark'],
            '--color-primary-light'         => $tokens['primary_light'],
            '--color-primary-rgb'           => $tokens['primary_rgb'],
            '--color-secondary'             => $tokens['secondary'],
            '--color-accent'                => $tokens['accent'],
            '--color-accent-dark'           => $tokens['accent_dark'],
            '--color-accent-rgb'            => $tokens['accent_rgb'],
            '--color-success'               => $tokens['success'],
            '--color-success-dark'          => $tokens['success_dark'],
            '--color-success-rgb'           => $tokens['success_rgb'],
            '--color-info'                  => $tokens['info'],
            '--color-info-dark'             => $tokens['info_dark'],
            '--color-info-rgb'              => $tokens['info_rgb'],
            '--color-warning'               => $tokens['warning'],
            '--color-warning-dark'          => $tokens['warning_dark'],
            '--color-warning-rgb'           => $tokens['warning_rgb'],
            '--color-error'                 => $tokens['error'],
            '--color-error-dark'            => $tokens['error_dark'],
            '--color-error-rgb'             => $tokens['error_rgb'],
            '--color-overlay'               => $tokens['overlay'],
            '--color-dark'                  => $tokens['text'],
            '--color-text'                  => $tokens['text'],
            '--color-text-muted'            => $tokens['text_muted'],
            '--color-heading'               => $tokens['heading'],
            '--color-background'            => $tokens['background'],
            '--color-surface'               => $tokens['surface'],
            '--color-surface-alt'           => $tokens['surface_alt'],
            '--color-border'                => $tokens['border'],
            '--color-neutral-0'             => $tokens['neutral_0'],
            '--color-neutral-50'            => $tokens['neutral_50'],
            '--color-neutral-100'           => $tokens['neutral_100'],
            '--color-neutral-200'           => $tokens['neutral_200'],
            '--color-neutral-300'           => $tokens['neutral_300'],
            '--color-neutral-400'           => $tokens['neutral_400'],
            '--color-neutral-400-rgb'       => $tokens['neutral_400_rgb'],
            '--color-neutral-500'           => $tokens['neutral_500'],
            '--color-neutral-600'           => $tokens['neutral_600'],
            '--color-neutral-700'           => $tokens['neutral_700'],
            '--color-neutral-800'           => $tokens['neutral_800'],
            '--color-neutral-900'           => $tokens['neutral_900'],
            '--color-gray-100'              => $tokens['neutral_100'],
            '--color-gray-200'              => $tokens['neutral_200'],
            '--color-gray-300'              => $tokens['neutral_300'],
            '--color-gray-500'              => $tokens['neutral_500'],
            '--color-gray-600'              => $tokens['neutral_600'],
            '--color-gray-900'              => $tokens['neutral_900'],
            '--color-bg-dark'               => $tokens['dark_bg'],
            '--color-card-dark'             => $tokens['dark_surface'],
            '--color-text-light'            => $tokens['dark_text'],
            '--qiling-dark-bg'              => $tokens['dark_bg'],
            '--qiling-dark-surface'         => $tokens['dark_surface'],
            '--qiling-dark-text'            => $tokens['dark_text'],
            '--qiling-dark-text-muted'      => $tokens['dark_text_muted'],
            '--qiling-dark-border'          => $tokens['dark_border'],
            '--dm-bg'                       => $tokens['dark_bg'],
            '--dm-bg-secondary'             => $tokens['dark_surface'],
            '--dm-bg-card'                  => $tokens['dark_surface'],
            '--dm-text'                     => $tokens['dark_text'],
            '--dm-text-muted'               => $tokens['dark_text_muted'],
            '--dm-border'                   => $tokens['dark_border'],
            '--qiling-gradient-brand'       => 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%)',
            '--qiling-gradient-brand-soft'  => 'linear-gradient(135deg, rgba(var(--color-primary-rgb), 0.12) 0%, rgba(var(--color-primary-rgb), 0.04) 100%)',
            '--qiling-gradient-accent'      => 'linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-dark) 100%)',
            '--qiling-gradient-success'     => 'linear-gradient(135deg, var(--color-success) 0%, var(--color-success-dark) 100%)',
            '--qiling-gradient-info'        => 'linear-gradient(135deg, var(--color-info) 0%, var(--color-info-dark) 100%)',
            '--qiling-gradient-warning'     => 'linear-gradient(135deg, var(--color-warning) 0%, var(--color-warning-dark) 100%)',
            '--qiling-gradient-error'       => 'linear-gradient(135deg, var(--color-error) 0%, var(--color-error-dark) 100%)',
            '--qiling-gradient-cool'        => 'linear-gradient(135deg, var(--color-info) 0%, var(--color-success) 100%)',
            '--font-sans'                   => $tokens['font_family'],
            '--qiling-font-size-base'       => 'var(--qiling-body-font-size)',
            '--qiling-line-height-base'     => 'var(--qiling-body-line-height)',
            '--qiling-container-width'      => 'var(--qiling-container-width-desktop)',
            '--qiling-section-padding'      => 'var(--qiling-section-spacing-desktop)',
            '--section-padding'             => 'var(--qiling-section-padding)',
            '--qiling-grid-gap'             => 'var(--qiling-grid-gap-desktop)',
            '--qiling-breakpoint-tablet'    => $tokens['breakpoint_tablet'],
            '--qiling-breakpoint-mobile'    => $tokens['breakpoint_mobile'],
            '--qiling-layout-mode'          => $tokens['layout_mode'],
            '--qiling-card-radius'          => $tokens['card_radius'],
            '--qiling-button-radius'        => $tokens['button_radius'],
            '--qiling-input-radius'         => $tokens['input_radius'],
            '--qiling-animation-speed'      => $tokens['animation_speed'],
            '--shadow-sm'                   => $tokens['shadow_sm'],
            '--shadow-md'                   => $tokens['shadow_md'],
            '--shadow-lg'                   => $tokens['shadow_lg'],
        );

        $foundation_aliases = self::get_design_token_data( 'foundation_style_aliases' );
        if ( ! empty( $foundation_aliases ) ) {
            $variables = array_merge( $variables, $foundation_aliases );
        }

        foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
            foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                $desktop_var = self::get_typography_css_var_name( $style_key, $property_key, 'desktop' );
                $current_var = self::get_typography_css_var_name( $style_key, $property_key );
                foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                    $token_key = self::get_typography_system_token_key( $style_key, $property_key, $device_key );
                    if ( isset( $tokens[ $token_key ] ) ) {
                        $variables[ self::get_typography_css_var_name( $style_key, $property_key, $device_key ) ] = $tokens[ $token_key ];
                    }
                }
                $variables[ $current_var ] = 'var(' . $desktop_var . ')';
            }
        }

        foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
            $container_key = 'container_width_' . $device_key;
            $section_key = 'section_spacing_' . $device_key;
            $grid_gap_key = 'grid_gap_' . $device_key;

            if ( isset( $tokens[ $container_key ] ) ) {
                $variables[ '--qiling-container-width-' . $device_key ] = $tokens[ $container_key ];
            }
            if ( isset( $tokens[ $section_key ] ) ) {
                $variables[ '--qiling-section-spacing-' . $device_key ] = $tokens[ $section_key ];
            }
            if ( isset( $tokens[ $grid_gap_key ] ) ) {
                $variables[ '--qiling-grid-gap-' . $device_key ] = $tokens[ $grid_gap_key ];
            }
        }

        if ( function_exists( 'apply_filters' ) ) {
            $variables = apply_filters( 'developer_starter_design_token_css_variables', $variables, $tokens );
        }

        return is_array( $variables ) ? array_map( 'strval', $variables ) : array();
    }

    /**
     * @param array<string,mixed>|null  $options Optional option source.
     * @param array<string,string>|null $tokens  Optional token set.
     * @return array<string,string>
     */
    public static function get_current_component_styles( $options = null, $tokens = null ) {
        $options = is_array( $options ) ? self::normalize_runtime_theme_options( $options ) : self::get_theme_options();
        $tokens  = is_array( $tokens ) ? $tokens : self::get_current_tokens( $options );
        $design_system = self::get_normalized_design_system_v2( $options );
        $preset = self::get_preset_payload(
            isset( $design_system['preset'] ) ? (string) $design_system['preset'] : self::get_current_preset_key( $options ),
            $options
        );

        $styles = self::get_base_component_styles( $tokens );

        if ( ! empty( $preset['component_styles'] ) && is_array( $preset['component_styles'] ) ) {
            $styles = array_merge( $styles, $preset['component_styles'] );
        }

        if ( ! empty( $design_system['component_styles'] ) && is_array( $design_system['component_styles'] ) ) {
            $styles = array_merge( $styles, $design_system['component_styles'] );
        }

        if ( function_exists( 'apply_filters' ) ) {
            $styles = apply_filters( 'developer_starter_component_styles', $styles, $options, $tokens );
        }

        return is_array( $styles ) ? array_map( 'strval', $styles ) : array();
    }

    /**
     * @param array<string,mixed>|null  $options Optional option source.
     * @param array<string,string>|null $tokens  Optional token set.
     * @return string
     */
    public static function get_header_runtime_style_vars( $options = null, $tokens = null ) {
        $options = is_array( $options ) ? self::normalize_runtime_theme_options( $options ) : self::get_theme_options();
        $tokens  = is_array( $tokens ) ? $tokens : self::get_current_tokens( $options );

        $variables = array(
            '--qiling-header-bg'                     => 'var(--qiling-component-header-bg)',
            '--qiling-header-text'                   => 'var(--qiling-component-header-text)',
            '--qiling-header-nav-link'               => 'var(--qiling-component-nav-link)',
            '--qiling-header-scrolled-text'          => 'var(--qiling-component-header-scrolled-text, var(--qiling-header-text))',
            '--qiling-header-scrolled-nav-link'      => 'var(--qiling-component-nav-scrolled-link, var(--qiling-component-header-scrolled-text, var(--qiling-header-nav-link)))',
            '--qiling-header-scrolled-nav-hover-text' => 'var(--qiling-component-nav-scrolled-hover-text, var(--qiling-component-nav-hover-text))',
            '--qiling-header-logo-transparent-fill'  => 'var(--qiling-component-header-logo-transparent-fill)',
            '--qiling-header-logo-scrolled-fill'     => 'var(--qiling-component-header-logo-scrolled-fill)',
            '--qiling-header-phone-normal-bg'        => 'var(--qiling-component-header-phone-bg)',
            '--qiling-header-phone-normal-text'      => 'var(--qiling-component-header-phone-text)',
            '--qiling-header-phone-transparent-bg'   => 'var(--qiling-component-header-phone-transparent-bg)',
            '--qiling-header-phone-transparent-text' => 'var(--qiling-component-header-phone-transparent-text)',
        );

        if ( function_exists( 'apply_filters' ) ) {
            $variables = apply_filters( 'developer_starter_header_runtime_style_variables', $variables, $options, $tokens );
        }

        return self::build_inline_css_variable_string( $variables );
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return string
     */
    public static function get_footer_runtime_style_vars( $options = null ) {
        $options = is_array( $options ) ? self::normalize_runtime_theme_options( $options ) : self::get_theme_options();

        $variables = array(
            '--qiling-footer-widgets-bg'  => 'var(--qiling-component-footer-bg)',
            '--qiling-footer-text-color'  => 'var(--qiling-component-footer-text)',
            '--qiling-footer-bottom-bg'   => 'var(--qiling-component-footer-bottom-bg)',
            '--qiling-footer-heading-color' => 'var(--qiling-component-footer-heading, var(--qiling-component-footer-text))',
            '--qiling-footer-heading-size'  => 'var(--qiling-component-footer-heading-size)',
        );

        if ( function_exists( 'apply_filters' ) ) {
            $variables = apply_filters( 'developer_starter_footer_runtime_style_variables', $variables, $options );
        }

        return self::build_inline_css_variable_string( $variables );
    }

    /**
     * @param array<string,string> $variables
     * @return string
     */
    private static function build_inline_css_variable_string( $variables ) {
        $declarations = array();
        foreach ( $variables as $name => $value ) {
            $name = trim( (string) $name );
            $value = trim( (string) $value );
            if ( '' === $name || '' === $value ) {
                continue;
            }

            $declarations[] = $name . ': ' . $value;
        }

        return empty( $declarations ) ? '' : implode( '; ', $declarations ) . ';';
    }

    /**
     * @param array<string,string> $tokens Current tokens.
     * @return array<string,string>
     */
    private static function get_base_component_styles( $tokens ) {
        return array(
            'button_bg'                => 'var(--color-primary)',
            'button_text'              => '#ffffff',
            'button_border'            => 'var(--color-primary)',
            'button_hover_bg'          => 'var(--color-primary-hover)',
            'button_hover_text'        => '#ffffff',
            'button_shadow'            => isset( $tokens['shadow_sm'] ) ? (string) $tokens['shadow_sm'] : 'var(--shadow-sm)',
            'button_padding'           => '12px 24px',
            'button_secondary_bg'      => 'var(--color-surface-alt)',
            'button_secondary_text'    => 'var(--color-neutral-800)',
            'button_secondary_border'  => 'var(--color-border)',
            'button_secondary_hover_bg'=> 'var(--color-surface)',
            'border_accent'            => 'var(--color-primary)',
            'heading_weight'           => '700',
            'heading_letter_spacing'   => '0em',
            'card_bg'                  => 'var(--color-surface)',
            'card_border'              => 'var(--color-border)',
            'card_shadow'              => isset( $tokens['shadow_md'] ) ? (string) $tokens['shadow_md'] : 'var(--shadow-md)',
            'title_bar_bg'             => 'var(--qiling-gradient-brand-soft)',
            'title_bar_text'           => 'var(--color-heading)',
            'title_bar_border'         => 'rgba(var(--color-primary-rgb), 0.18)',
            'list_header_bg'           => 'var(--color-surface-alt)',
            'list_header_text'         => 'var(--color-heading)',
            'list_header_border'       => 'var(--color-border)',
            'highlight_bg'             => 'var(--qiling-gradient-brand)',
            'highlight_text'           => 'var(--color-text-inverse)',
            'highlight_border'         => 'var(--color-primary)',
            'highlight_soft_bg'        => 'rgba(var(--color-primary-rgb), 0.08)',
            'form_input_bg'            => 'var(--color-surface)',
            'form_input_text'          => 'var(--color-text)',
            'form_input_border'        => 'var(--color-border)',
            'form_focus_border'        => 'var(--color-primary)',
            'auth_action_bg'           => 'var(--qiling-gradient-brand)',
            'auth_action_text'         => 'var(--color-text-inverse)',
            'auth_code_bg'             => 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%)',
            'auth_code_text'           => 'var(--color-text-inverse)',
            'auth_slider_track_bg'     => 'linear-gradient(to right, var(--color-neutral-200), var(--color-neutral-100))',
            'auth_slider_handle_bg'    => 'var(--qiling-gradient-brand)',
            'auth_slider_progress_bg'  => 'linear-gradient(to right, rgba(var(--color-primary-rgb), 0.26), rgba(var(--color-primary-rgb), 0.1))',
            'auth_verified_color'      => 'var(--color-success)',
            'module_title_color'       => 'var(--color-heading)',
            'module_title_size'        => '2rem',
            'module_title_align'       => 'center',
            'post_card_bg'             => 'var(--color-surface)',
            'post_card_border'         => 'var(--color-border)',
            'post_card_shadow'         => isset( $tokens['shadow_sm'] ) ? (string) $tokens['shadow_sm'] : 'var(--shadow-sm)',
            'post_card_title_color'    => 'var(--color-heading)',
            'post_card_meta_color'     => 'var(--color-text-muted)',
            'header_bg'                => 'var(--color-surface)',
            'header_border'            => 'var(--color-border)',
            'header_shadow'            => isset( $tokens['shadow_sm'] ) ? (string) $tokens['shadow_sm'] : 'var(--shadow-sm)',
            'header_text'              => 'var(--color-heading)',
            'header_scrolled_text'     => 'var(--qiling-component-header-text)',
            'header_logo_transparent_fill' => 'var(--qiling-gradient-brand)',
            'header_logo_scrolled_fill' => 'var(--qiling-gradient-brand)',
            'header_phone_bg'          => 'var(--qiling-gradient-brand)',
            'header_phone_text'        => '#ffffff',
            'header_phone_transparent_bg' => 'rgba(255, 255, 255, 0.16)',
            'header_phone_transparent_text' => '#ffffff',
            'nav_link'                 => 'var(--color-text)',
            'nav_scrolled_link'        => 'var(--qiling-component-nav-link)',
            'nav_hover_bg'             => 'var(--qiling-gradient-brand)',
            'nav_hover_text'           => '#ffffff',
            'nav_scrolled_hover_text'  => 'var(--qiling-component-nav-hover-text)',
            'mobile_nav_bg'            => 'var(--color-surface)',
            'mobile_nav_border'        => 'var(--color-border)',
            'mobile_nav_link'          => 'var(--color-heading)',
            'mobile_nav_hover_bg'      => 'var(--color-surface-alt)',
            'mobile_nav_hover_text'    => 'var(--color-primary)',
            'dropdown_bg'              => 'var(--color-surface)',
            'dropdown_border'          => 'var(--color-border)',
            'dropdown_shadow'          => isset( $tokens['shadow_md'] ) ? (string) $tokens['shadow_md'] : 'var(--shadow-md)',
            'dropdown_link'            => 'var(--color-text)',
            'dropdown_hover_bg'        => 'var(--color-surface-alt)',
            'dropdown_hover_text'      => 'var(--color-primary)',
            'badge_bg'                 => 'var(--qiling-gradient-accent)',
            'badge_text'               => '#ffffff',
            'badge_border'             => 'var(--qiling-component-badge-bg)',
            'tabs_border'              => 'var(--color-border)',
            'tabs_text'                => 'var(--color-text-muted)',
            'tabs_active_bg'           => 'var(--qiling-gradient-brand-soft)',
            'tabs_active_text'         => 'var(--color-primary)',
            'tabs_active_border'       => 'var(--color-primary)',
            'accordion_bg'             => 'var(--color-surface)',
            'accordion_border'         => 'var(--color-border)',
            'accordion_title'          => 'var(--color-heading)',
            'pagination_bg'            => 'var(--color-surface)',
            'pagination_border'        => 'var(--color-border)',
            'pagination_text'          => 'var(--color-text)',
            'pagination_active_bg'     => 'var(--qiling-gradient-brand)',
            'pagination_active_text'   => '#ffffff',
            'breadcrumb_bg'            => 'var(--color-surface)',
            'breadcrumb_text'          => 'var(--color-text-muted)',
            'breadcrumb_link'          => 'var(--color-heading)',
            'alert_bg'                 => 'var(--color-surface-alt)',
            'alert_border'             => 'var(--color-border)',
            'alert_text'               => 'var(--color-text)',
            'modal_bg'                 => 'var(--color-surface)',
            'modal_border'             => 'var(--color-border)',
            'modal_shadow'             => isset( $tokens['shadow_lg'] ) ? (string) $tokens['shadow_lg'] : 'var(--shadow-lg)',
            'modal_title'              => 'var(--color-heading)',
            'announcement_marketing_bg' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%)',
            'announcement_marketing_button_text' => 'var(--color-primary-hover)',
            'sidebar_bg'               => 'var(--color-surface)',
            'sidebar_border'           => 'var(--color-border)',
            'sidebar_shadow'           => isset( $tokens['shadow_md'] ) ? (string) $tokens['shadow_md'] : 'var(--shadow-md)',
            'sidebar_title'            => 'var(--color-heading)',
            'footer_bg'                => 'var(--color-neutral-900)',
            'footer_text'              => 'rgba(255, 255, 255, 0.78)',
            'footer_heading'           => 'var(--qiling-component-footer-text)',
            'footer_heading_size'      => '18px',
            'footer_link'              => 'rgba(255, 255, 255, 0.72)',
            'footer_link_hover'        => '#ffffff',
            'footer_bottom_bg'         => 'rgba(2, 6, 23, 0.82)',
            'woo_card_bg'              => 'var(--color-surface)',
            'woo_card_border'          => 'var(--color-border)',
            'woo_card_shadow'          => isset( $tokens['shadow_md'] ) ? (string) $tokens['shadow_md'] : 'var(--shadow-md)',
            'woo_card_title'           => 'var(--color-heading)',
            'woo_card_price'           => 'var(--color-primary)',
            'dark_card_bg'             => 'var(--qiling-dark-surface)',
            'dark_card_border'         => 'var(--qiling-dark-border)',
            'dark_form_input_bg'       => 'var(--qiling-dark-surface)',
            'dark_form_input_text'     => 'var(--qiling-dark-text)',
            'dark_form_input_border'   => 'var(--qiling-dark-border)',
            'dark_module_title_color'  => 'var(--qiling-dark-text)',
            'dark_post_card_bg'        => 'var(--qiling-dark-surface)',
            'dark_post_card_border'    => 'var(--qiling-dark-border)',
            'dark_post_card_title_color' => 'var(--qiling-dark-text)',
            'dark_post_card_meta_color'  => 'var(--qiling-dark-text-muted)',
        );
    }

    /**
     * @param array<string,string>|null $styles Optional component styles.
     * @return array<string,string>
     */
    public static function get_component_css_variables( $styles = null ) {
        $styles = is_array( $styles ) ? $styles : self::get_current_component_styles();
        $variables = array(
            '--qiling-component-button-bg'             => $styles['button_bg'],
            '--qiling-component-button-text'           => $styles['button_text'],
            '--qiling-component-button-border'         => $styles['button_border'],
            '--qiling-component-button-hover-bg'       => $styles['button_hover_bg'],
            '--qiling-component-button-hover-text'     => $styles['button_hover_text'],
            '--qiling-component-button-shadow'         => $styles['button_shadow'],
            '--qiling-component-button-padding'        => $styles['button_padding'],
            '--qiling-component-button-secondary-bg'   => $styles['button_secondary_bg'],
            '--qiling-component-button-secondary-text'  => $styles['button_secondary_text'],
            '--qiling-component-button-secondary-border'=> $styles['button_secondary_border'],
            '--qiling-component-button-secondary-hover-bg' => $styles['button_secondary_hover_bg'],
            '--qiling-component-border-accent'         => $styles['border_accent'],
            '--qiling-component-heading-weight'        => $styles['heading_weight'],
            '--qiling-component-heading-letter-spacing'=> $styles['heading_letter_spacing'],
            '--qiling-component-card-bg'               => $styles['card_bg'],
            '--qiling-component-card-border'           => $styles['card_border'],
            '--qiling-component-card-shadow'           => $styles['card_shadow'],
            '--qiling-component-title-bar-bg'          => $styles['title_bar_bg'],
            '--qiling-component-title-bar-text'        => $styles['title_bar_text'],
            '--qiling-component-title-bar-border'      => $styles['title_bar_border'],
            '--qiling-component-list-header-bg'        => $styles['list_header_bg'],
            '--qiling-component-list-header-text'      => $styles['list_header_text'],
            '--qiling-component-list-header-border'    => $styles['list_header_border'],
            '--qiling-component-highlight-bg'          => $styles['highlight_bg'],
            '--qiling-component-highlight-text'        => $styles['highlight_text'],
            '--qiling-component-highlight-border'      => $styles['highlight_border'],
            '--qiling-component-highlight-soft-bg'     => $styles['highlight_soft_bg'],
            '--qiling-component-form-input-bg'         => $styles['form_input_bg'],
            '--qiling-component-form-input-text'       => $styles['form_input_text'],
            '--qiling-component-form-input-border'     => $styles['form_input_border'],
            '--qiling-component-form-focus-border'     => $styles['form_focus_border'],
            '--qiling-component-auth-action-bg'        => $styles['auth_action_bg'],
            '--qiling-component-auth-action-text'      => $styles['auth_action_text'],
            '--qiling-component-auth-code-bg'          => $styles['auth_code_bg'],
            '--qiling-component-auth-code-text'        => $styles['auth_code_text'],
            '--qiling-component-auth-slider-track-bg'  => $styles['auth_slider_track_bg'],
            '--qiling-component-auth-slider-handle-bg' => $styles['auth_slider_handle_bg'],
            '--qiling-component-auth-slider-progress-bg' => $styles['auth_slider_progress_bg'],
            '--qiling-component-auth-verified-color'   => $styles['auth_verified_color'],
            '--qiling-component-module-title-color'    => $styles['module_title_color'],
            '--qiling-component-module-title-size'     => $styles['module_title_size'],
            '--qiling-component-module-title-align'    => $styles['module_title_align'],
            '--qiling-component-post-card-bg'          => $styles['post_card_bg'],
            '--qiling-component-post-card-border'      => $styles['post_card_border'],
            '--qiling-component-post-card-shadow'      => $styles['post_card_shadow'],
            '--qiling-component-post-card-title-color' => $styles['post_card_title_color'],
            '--qiling-component-post-card-meta-color'  => $styles['post_card_meta_color'],
            '--qiling-component-header-bg'             => $styles['header_bg'],
            '--qiling-component-header-border'         => $styles['header_border'],
            '--qiling-component-header-shadow'         => $styles['header_shadow'],
            '--qiling-component-header-text'           => $styles['header_text'],
            '--qiling-component-header-scrolled-text'  => $styles['header_scrolled_text'],
            '--qiling-component-header-logo-transparent-fill' => $styles['header_logo_transparent_fill'],
            '--qiling-component-header-logo-scrolled-fill' => $styles['header_logo_scrolled_fill'],
            '--qiling-component-header-phone-bg'       => $styles['header_phone_bg'],
            '--qiling-component-header-phone-text'     => $styles['header_phone_text'],
            '--qiling-component-header-phone-transparent-bg' => $styles['header_phone_transparent_bg'],
            '--qiling-component-header-phone-transparent-text' => $styles['header_phone_transparent_text'],
            '--qiling-component-nav-link'              => $styles['nav_link'],
            '--qiling-component-nav-scrolled-link'     => $styles['nav_scrolled_link'],
            '--qiling-component-nav-hover-bg'          => $styles['nav_hover_bg'],
            '--qiling-component-nav-hover-text'        => $styles['nav_hover_text'],
            '--qiling-component-nav-scrolled-hover-text' => $styles['nav_scrolled_hover_text'],
            '--qiling-component-mobile-nav-bg'         => $styles['mobile_nav_bg'],
            '--qiling-component-mobile-nav-border'     => $styles['mobile_nav_border'],
            '--qiling-component-mobile-nav-link'       => $styles['mobile_nav_link'],
            '--qiling-component-mobile-nav-hover-bg'   => $styles['mobile_nav_hover_bg'],
            '--qiling-component-mobile-nav-hover-text' => $styles['mobile_nav_hover_text'],
            '--qiling-component-dropdown-bg'           => $styles['dropdown_bg'],
            '--qiling-component-dropdown-border'       => $styles['dropdown_border'],
            '--qiling-component-dropdown-shadow'       => $styles['dropdown_shadow'],
            '--qiling-component-dropdown-link'         => $styles['dropdown_link'],
            '--qiling-component-dropdown-hover-bg'     => $styles['dropdown_hover_bg'],
            '--qiling-component-dropdown-hover-text'   => $styles['dropdown_hover_text'],
            '--qiling-component-badge-bg'              => $styles['badge_bg'],
            '--qiling-component-badge-text'            => $styles['badge_text'],
            '--qiling-component-badge-border'          => $styles['badge_border'],
            '--qiling-component-tabs-border'           => $styles['tabs_border'],
            '--qiling-component-tabs-text'             => $styles['tabs_text'],
            '--qiling-component-tabs-active-bg'        => $styles['tabs_active_bg'],
            '--qiling-component-tabs-active-text'      => $styles['tabs_active_text'],
            '--qiling-component-tabs-active-border'    => $styles['tabs_active_border'],
            '--qiling-component-accordion-bg'          => $styles['accordion_bg'],
            '--qiling-component-accordion-border'      => $styles['accordion_border'],
            '--qiling-component-accordion-title'       => $styles['accordion_title'],
            '--qiling-component-pagination-bg'         => $styles['pagination_bg'],
            '--qiling-component-pagination-border'     => $styles['pagination_border'],
            '--qiling-component-pagination-text'       => $styles['pagination_text'],
            '--qiling-component-pagination-active-bg'  => $styles['pagination_active_bg'],
            '--qiling-component-pagination-active-text'=> $styles['pagination_active_text'],
            '--qiling-component-breadcrumb-bg'         => $styles['breadcrumb_bg'],
            '--qiling-component-breadcrumb-text'       => $styles['breadcrumb_text'],
            '--qiling-component-breadcrumb-link'       => $styles['breadcrumb_link'],
            '--qiling-component-alert-bg'              => $styles['alert_bg'],
            '--qiling-component-alert-border'          => $styles['alert_border'],
            '--qiling-component-alert-text'            => $styles['alert_text'],
            '--qiling-component-modal-bg'              => $styles['modal_bg'],
            '--qiling-component-modal-border'          => $styles['modal_border'],
            '--qiling-component-modal-shadow'          => $styles['modal_shadow'],
            '--qiling-component-modal-title'           => $styles['modal_title'],
            '--qiling-announcement-marketing-bg'       => $styles['announcement_marketing_bg'],
            '--qiling-announcement-marketing-button-text' => $styles['announcement_marketing_button_text'],
            '--qiling-component-sidebar-bg'            => $styles['sidebar_bg'],
            '--qiling-component-sidebar-border'        => $styles['sidebar_border'],
            '--qiling-component-sidebar-shadow'        => $styles['sidebar_shadow'],
            '--qiling-component-sidebar-title'         => $styles['sidebar_title'],
            '--qiling-component-footer-bg'             => $styles['footer_bg'],
            '--qiling-component-footer-text'           => $styles['footer_text'],
            '--qiling-component-footer-heading'        => $styles['footer_heading'],
            '--qiling-component-footer-heading-size'   => $styles['footer_heading_size'],
            '--qiling-component-footer-link'           => $styles['footer_link'],
            '--qiling-component-footer-link-hover'     => $styles['footer_link_hover'],
            '--qiling-component-footer-bottom-bg'      => $styles['footer_bottom_bg'],
            '--qiling-component-woo-card-bg'           => $styles['woo_card_bg'],
            '--qiling-component-woo-card-border'       => $styles['woo_card_border'],
            '--qiling-component-woo-card-shadow'       => $styles['woo_card_shadow'],
            '--qiling-component-woo-card-title'        => $styles['woo_card_title'],
            '--qiling-component-woo-card-price'        => $styles['woo_card_price'],
            '--qiling-component-card-bg-dark'          => $styles['dark_card_bg'],
            '--qiling-component-card-border-dark'      => $styles['dark_card_border'],
            '--qiling-component-form-input-bg-dark'    => $styles['dark_form_input_bg'],
            '--qiling-component-form-input-text-dark'  => $styles['dark_form_input_text'],
            '--qiling-component-form-input-border-dark'=> $styles['dark_form_input_border'],
            '--qiling-component-module-title-color-dark'=> $styles['dark_module_title_color'],
            '--qiling-component-post-card-bg-dark'     => $styles['dark_post_card_bg'],
            '--qiling-component-post-card-border-dark' => $styles['dark_post_card_border'],
            '--qiling-component-post-card-title-color-dark' => $styles['dark_post_card_title_color'],
            '--qiling-component-post-card-meta-color-dark'  => $styles['dark_post_card_meta_color'],
        );

        if ( function_exists( 'apply_filters' ) ) {
            $variables = apply_filters( 'developer_starter_component_style_css_variables', $variables, $styles );
        }

        return is_array( $variables ) ? array_map( 'strval', $variables ) : array();
    }

    /**
     * @return string
     */
    public static function build_root_css() {
        $options = self::get_theme_options();
        $tokens = self::get_current_tokens( $options );
        $custom_font = self::get_custom_font_runtime_config( $options, isset( $tokens['font_family'] ) ? (string) $tokens['font_family'] : '' );
        if ( ! empty( $custom_font['font_stack'] ) ) {
            $tokens['font_family'] = (string) $custom_font['font_stack'];
        }
        $layout_mode = isset( $tokens['layout_mode'] ) ? (string) $tokens['layout_mode'] : 'wide';
        $component_styles = self::get_current_component_styles( $options, $tokens );
        $variables = array_merge(
            self::get_css_variables( $tokens ),
            self::get_component_css_variables( $component_styles )
        );
        $css = isset( $custom_font['font_face_css'] ) ? (string) $custom_font['font_face_css'] : '';
        $css .= ":root{\n";
        foreach ( $variables as $name => $value ) {
            if ( '' === trim( (string) $name ) || '' === trim( (string) $value ) ) {
                continue;
            }
            $css .= '    ' . $name . ': ' . $value . ";\n";
        }
        $css .= "}\n";
        $css .= "html.dark-mode,[data-theme='dark']{\n";
        $css .= "    color-scheme: dark;\n";
        $css .= "    --color-dark: var(--qiling-dark-text);\n";
        $css .= "    --color-text: var(--qiling-dark-text);\n";
        $css .= "    --color-text-muted: var(--qiling-dark-text-muted);\n";
        $css .= "    --color-heading: var(--qiling-dark-text);\n";
        $css .= "    --color-background: var(--qiling-dark-bg);\n";
        $css .= "    --color-surface: var(--qiling-dark-surface);\n";
        $css .= "    --color-surface-alt: var(--qiling-dark-surface);\n";
        $css .= "    --color-border: var(--qiling-dark-border);\n";
        $css .= "    --dm-bg: var(--qiling-dark-bg);\n";
        $css .= "    --dm-bg-secondary: var(--qiling-dark-surface);\n";
        $css .= "    --dm-bg-card: var(--qiling-dark-surface);\n";
        $css .= "    --dm-text: var(--qiling-dark-text);\n";
        $css .= "    --dm-text-muted: var(--qiling-dark-text-muted);\n";
        $css .= "    --dm-border: var(--qiling-dark-border);\n";
        $css .= "}\n";
        $css .= self::build_responsive_runtime_css( $tokens );

        if ( self::is_global_tokens_enabled( $options ) ) {
            $css .= "body{font-family:var(--font-sans);font-size:var(--qiling-body-font-size);line-height:var(--qiling-body-line-height);font-weight:var(--qiling-body-font-weight);letter-spacing:var(--qiling-body-letter-spacing);color:var(--color-text);background:var(--color-background);}\n";
            $css .= "small,.text-small,.footer-copyright,.footer-filing,.footer-menu a{font-size:var(--qiling-small-font-size);line-height:var(--qiling-small-line-height);font-weight:var(--qiling-small-font-weight);letter-spacing:var(--qiling-small-letter-spacing);}\n";
            $css .= ".lead,.page-lead,.hero-lead,.banner-subtitle,.section-subtitle{font-size:var(--qiling-lead-font-size);line-height:var(--qiling-lead-line-height);font-weight:var(--qiling-lead-font-weight);letter-spacing:var(--qiling-lead-letter-spacing);}\n";
            $css .= "h1{font-size:var(--qiling-h1-font-size);line-height:var(--qiling-h1-line-height);font-weight:var(--qiling-h1-font-weight);letter-spacing:var(--qiling-h1-letter-spacing);color:var(--color-heading);}\n";
            $css .= "h2,.section-title{font-size:var(--qiling-h2-font-size);line-height:var(--qiling-h2-line-height);font-weight:var(--qiling-h2-font-weight);letter-spacing:var(--qiling-h2-letter-spacing);color:var(--color-heading);}\n";
            $css .= "h3{font-size:var(--qiling-h3-font-size);line-height:var(--qiling-h3-line-height);font-weight:var(--qiling-h3-font-weight);letter-spacing:var(--qiling-h3-letter-spacing);color:var(--color-heading);}\n";
            $css .= "h4{font-size:var(--qiling-h4-font-size);line-height:var(--qiling-h4-line-height);font-weight:var(--qiling-h4-font-weight);letter-spacing:var(--qiling-h4-letter-spacing);color:var(--color-heading);}\n";
            $css .= "h5{font-size:var(--qiling-h5-font-size);line-height:var(--qiling-h5-line-height);font-weight:var(--qiling-h5-font-weight);letter-spacing:var(--qiling-h5-letter-spacing);color:var(--color-heading);}\n";
            $css .= "h6{font-size:var(--qiling-h6-font-size);line-height:var(--qiling-h6-line-height);font-weight:var(--qiling-h6-font-weight);letter-spacing:var(--qiling-h6-letter-spacing);color:var(--color-heading);}\n";
            $css .= ".bg-light,.section-light{background:var(--color-surface-alt);}\n";
            $css .= ".container{max-width:var(--qiling-container-width);}\n";
            $css .= ".section-padding{padding:var(--qiling-section-padding) 0;}\n";
            $css .= ".grid-cols-2,.grid-cols-3,.grid-cols-4,.grid-cols-6,.footer-widgets-grid{gap:var(--qiling-grid-gap);}\n";
            $css .= ".site-header .primary-navigation>ul>li>a,.site-header .primary-navigation li ul li a,.mobile-menu a,.header-phone,.site-branding-slogan{font-size:var(--qiling-menu-font-size);line-height:var(--qiling-menu-line-height);font-weight:var(--qiling-menu-font-weight);letter-spacing:var(--qiling-menu-letter-spacing);}\n";
            $css .= ".btn,.button,button,input[type='submit'],input[type='button'],input[type='reset'],.post-read-more{font-size:var(--qiling-button-font-size);line-height:var(--qiling-button-line-height);font-weight:var(--qiling-button-font-weight);letter-spacing:var(--qiling-button-letter-spacing);border-radius:var(--qiling-button-radius);transition-duration:var(--qiling-animation-speed);}\n";
            $css .= "input:not([type='checkbox']):not([type='radio']):not([type='range']),select,textarea{font-size:var(--qiling-input-font-size);line-height:var(--qiling-input-line-height);font-weight:var(--qiling-input-font-weight);letter-spacing:var(--qiling-input-letter-spacing);border-radius:var(--qiling-input-radius);}\n";
            $css .= ".card,.news-card,.product-card,.case-card,.pricing-card,.faq-item,.team-member,.about-culture-card,.about-gallery-item,.timeline-content,.account-section,.module-services .service-item,.module-features .feature-item{border-radius:var(--qiling-card-radius);}\n";
            $css .= ".card,.news-card,.product-card,.case-card,.pricing-card,.team-member,.about-culture-card,.account-section{box-shadow:var(--shadow-md);}\n";
            if ( 'boxed' === $layout_mode ) {
                $css .= "body{background:var(--color-surface-alt);}\n";
                $css .= "#page.site{max-width:calc(var(--qiling-container-width) + 48px);margin:0 auto;background:var(--color-background);box-shadow:var(--shadow-lg);}\n";
                $css .= "@media (max-width:" . $tokens['breakpoint_tablet'] . "){#page.site{max-width:none;box-shadow:none;}}\n";
            }
            $css .= self::build_component_css();
        }

        $css .= self::build_context_design_preset_css( $options );

        if ( function_exists( 'is_singular' ) && is_singular( 'page' ) && function_exists( 'get_queried_object_id' ) ) {
            $css .= self::build_page_override_css( get_queried_object_id(), $options );
        }

        if ( function_exists( 'apply_filters' ) ) {
            $css = apply_filters( 'developer_starter_design_tokens_css', $css, $tokens, $variables );
        }

        return is_string( $css ) ? $css : '';
    }

    /**
     * @param array<string,mixed> $options
     * @param string              $fallback_stack Existing global font stack.
     * @return array{font_face_css:string,font_stack:string}
     */
    private static function get_custom_font_runtime_config( $options, $fallback_stack ) {
        if ( ! is_array( $options ) || '1' !== (string) ( isset( $options['custom_font_enable'] ) ? $options['custom_font_enable'] : '' ) ) {
            return array(
                'font_face_css' => '',
                'font_stack'    => '',
            );
        }

        $family = self::sanitize_custom_font_family_name( isset( $options['custom_font_family'] ) ? $options['custom_font_family'] : '' );
        if ( '' === $family ) {
            $family = 'Qiling Custom Font';
        }

        $sources = self::get_custom_font_sources( $options );
        if ( empty( $sources ) ) {
            return array(
                'font_face_css' => '',
                'font_stack'    => '',
            );
        }

        $weight = isset( $options['custom_font_weight'] ) ? trim( (string) $options['custom_font_weight'] ) : '400';
        if ( ! preg_match( '/^(?:[1-9]00|[1-9]00\s+[1-9]00)$/', $weight ) ) {
            $weight = '400';
        }

        $style = isset( $options['custom_font_style'] ) ? (string) $options['custom_font_style'] : 'normal';
        if ( ! in_array( $style, array( 'normal', 'italic' ), true ) ) {
            $style = 'normal';
        }

        $display = isset( $options['custom_font_display'] ) ? (string) $options['custom_font_display'] : 'swap';
        if ( ! in_array( $display, array( 'auto', 'block', 'swap', 'fallback', 'optional' ), true ) ) {
            $display = 'swap';
        }

        $source_css = array();
        foreach ( $sources as $source ) {
            $source_css[] = 'url("' . self::escape_css_string( $source['url'] ) . '") format("' . self::escape_css_string( $source['format'] ) . '")';
        }

        $font_face_css  = "@font-face{\n";
        $font_face_css .= '    font-family: "' . self::escape_css_string( $family ) . "\";\n";
        $font_face_css .= '    src: ' . implode( ",\n        ", $source_css ) . ";\n";
        $font_face_css .= '    font-weight: ' . $weight . ";\n";
        $font_face_css .= '    font-style: ' . $style . ";\n";
        $font_face_css .= '    font-display: ' . $display . ";\n";
        $font_face_css .= "}\n";

        $fallback_stack = self::sanitize_font_stack( $fallback_stack );
        if ( '' === $fallback_stack ) {
            $fallback_stack = 'sans-serif';
        }

        return array(
            'font_face_css' => $font_face_css,
            'font_stack'    => '"' . self::escape_css_string( $family ) . '", ' . $fallback_stack,
        );
    }

    /**
     * @param array<string,mixed> $options
     * @return array<int,array{url:string,format:string}>
     */
    private static function get_custom_font_sources( $options ) {
        $fields = array(
            'custom_font_woff2_url' => array( 'extension' => 'woff2', 'format' => 'woff2' ),
            'custom_font_woff_url'  => array( 'extension' => 'woff', 'format' => 'woff' ),
            'custom_font_ttf_url'   => array( 'extension' => 'ttf', 'format' => 'truetype' ),
            'custom_font_otf_url'   => array( 'extension' => 'otf', 'format' => 'opentype' ),
        );

        $sources = array();
        foreach ( $fields as $field => $meta ) {
            $raw_url = isset( $options[ $field ] ) ? trim( (string) $options[ $field ] ) : '';
            if ( '' === $raw_url ) {
                continue;
            }

            $url = function_exists( 'developer_starter_normalize_asset_url' )
                ? developer_starter_normalize_asset_url( $raw_url )
                : esc_url_raw( $raw_url );
            $url = is_string( $url ) ? trim( $url ) : '';
            if ( '' === $url || ! self::is_custom_font_url_allowed( $url, $meta['extension'] ) ) {
                continue;
            }

            $sources[] = array(
                'url'    => $url,
                'format' => $meta['format'],
            );
        }

        return $sources;
    }

    private static function is_custom_font_url_allowed( $url, $expected_extension ) {
        $path = (string) wp_parse_url( $url, PHP_URL_PATH );
        $extension = strtolower( (string) pathinfo( $path, PATHINFO_EXTENSION ) );
        if ( strtolower( (string) $expected_extension ) !== $extension ) {
            return false;
        }

        if ( function_exists( 'developer_starter_is_external_asset_url_allowed' ) ) {
            return (bool) developer_starter_is_external_asset_url_allowed( $url );
        }

        return '' !== esc_url_raw( $url, array( 'http', 'https' ) );
    }

    private static function sanitize_custom_font_family_name( $value ) {
        $value = function_exists( 'sanitize_text_field' )
            ? sanitize_text_field( (string) $value )
            : trim( strip_tags( (string) $value ) );
        $value = trim( $value, " \t\n\r\0\x0B\"'" );

        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        return $value;
    }

    private static function escape_css_string( $value ) {
        $value = str_replace( array( "\\", '"', "\n", "\r", "\f" ), array( "\\\\", '\"', '', '', '' ), (string) $value );
        return $value;
    }

    /**
     * @param int                      $post_id 页面 ID。
     * @param array<string,mixed>|null $options Optional option source.
     * @return string
     */
    public static function build_page_override_css( $post_id, $options = null ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return '';
        }

        $options = is_array( $options ) ? $options : self::get_theme_options();
        $overrides = self::get_page_design_overrides( $post_id, 'storage' );
        $compact_overrides = self::compact_page_design_overrides( $overrides );
        if ( empty( $compact_overrides ) ) {
            return '';
        }

        $base_tokens = self::get_current_tokens( $options );
        $page_tokens = self::get_current_tokens_for_page( $post_id, $options );
        $base_variables = self::get_css_variables( $base_tokens );
        $page_variables = self::get_css_variables( $page_tokens );
        $base_component_styles = self::get_current_component_styles( $options, $base_tokens );
        $page_component_styles = self::get_current_component_styles_for_page( $post_id, $options );
        $base_component_variables = self::get_component_css_variables( $base_component_styles );
        $page_component_variables = self::get_component_css_variables( $page_component_styles );
        $selector = 'body.page-id-' . $post_id;
        $css = '';
        $has_variable_override = false;

        foreach ( $page_variables as $name => $value ) {
            $base_value = isset( $base_variables[ $name ] ) ? (string) $base_variables[ $name ] : '';
            if ( (string) $value === $base_value ) {
                continue;
            }
            if ( ! $has_variable_override ) {
                $css .= $selector . "{\n";
                $has_variable_override = true;
            }
            $css .= '    ' . $name . ': ' . $value . ";\n";
        }

        foreach ( $page_component_variables as $name => $value ) {
            $base_value = isset( $base_component_variables[ $name ] ) ? (string) $base_component_variables[ $name ] : '';
            if ( (string) $value === $base_value ) {
                continue;
            }
            if ( ! $has_variable_override ) {
                $css .= $selector . "{\n";
                $has_variable_override = true;
            }
            $css .= '    ' . $name . ': ' . $value . ";\n";
        }

        if ( $has_variable_override ) {
            $css .= "}\n";
        }

        $layout_mode_override = isset( $compact_overrides['layout']['layout_mode'] ) ? sanitize_key( (string) $compact_overrides['layout']['layout_mode'] ) : '';
        if ( 'boxed' === $layout_mode_override ) {
            $css .= $selector . "{background:var(--color-surface-alt);}\n";
            $css .= $selector . " #page.site{max-width:calc(var(--qiling-container-width) + 48px);margin:0 auto;background:var(--color-background);box-shadow:var(--shadow-lg);}\n";
            $css .= "@media (max-width:" . $page_tokens['breakpoint_tablet'] . '){' . $selector . " #page.site{max-width:none;box-shadow:none;}}\n";
        } elseif ( 'wide' === $layout_mode_override ) {
            $css .= $selector . "{background:var(--color-background);}\n";
            $css .= $selector . " #page.site{max-width:none;margin:0;background:transparent;box-shadow:none;}\n";
        }

        return $css;
    }

    /**
     * @param array<string,mixed>|null $options Optional option source.
     * @return string
     */
    private static function build_context_design_preset_css( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        $target  = self::get_context_design_preset_target( $options );

        if ( empty( $target['preset'] ) || empty( $target['selector'] ) ) {
            return '';
        }

        $preset_options = self::build_options_with_context_preset( $options, $target['preset'] );
        $selector       = (string) $target['selector'];
        $css            = self::build_scoped_context_variable_css( $selector, $options, $preset_options );

        if ( '' === trim( $css ) ) {
            return '';
        }

        $base_tokens   = self::get_current_tokens( $options );
        $preset_tokens = self::get_current_tokens( $preset_options );
        $base_mode     = isset( $base_tokens['layout_mode'] ) ? sanitize_key( (string) $base_tokens['layout_mode'] ) : 'wide';
        $preset_mode   = isset( $preset_tokens['layout_mode'] ) ? sanitize_key( (string) $preset_tokens['layout_mode'] ) : $base_mode;

        if ( 'boxed' === $preset_mode && $preset_mode !== $base_mode ) {
            $css .= $selector . "{background:var(--color-surface-alt);}\n";
            $css .= $selector . " #page.site{max-width:calc(var(--qiling-container-width) + 48px);margin:0 auto;background:var(--color-background);box-shadow:var(--shadow-lg);}\n";
            $css .= "@media (max-width:" . $preset_tokens['breakpoint_tablet'] . '){' . $selector . " #page.site{max-width:none;box-shadow:none;}}\n";
        } elseif ( 'wide' === $preset_mode && $preset_mode !== $base_mode ) {
            $css .= $selector . "{background:var(--color-background);}\n";
            $css .= $selector . " #page.site{max-width:none;margin:0;background:transparent;box-shadow:none;}\n";
        }

        if ( function_exists( 'apply_filters' ) ) {
            $css = apply_filters( 'developer_starter_context_design_preset_css', $css, $target, $preset_options, $options );
        }

        return is_string( $css ) ? $css : '';
    }

    /**
     * @param array<string,mixed> $options Theme options.
     * @return array<string,mixed>
     */
    private static function get_context_design_preset_target( $options ) {
        $target = array();
        $rules  = self::get_context_preset_rules( $options );

        if ( function_exists( 'is_singular' ) && is_singular( 'page' ) && function_exists( 'get_queried_object_id' ) ) {
            $post_id = absint( get_queried_object_id() );
            $preset  = isset( $rules['pages'][ $post_id ] )
                ? self::sanitize_context_preset_key( $rules['pages'][ $post_id ], $options )
                : self::get_page_design_preset( $post_id, $options );
            if ( '' !== $preset ) {
                $target = array(
                    'source'   => 'page',
                    'objectId' => $post_id,
                    'preset'   => $preset,
                    'selector' => 'body.page-id-' . $post_id,
                );
            }
        }

        if ( empty( $target ) && function_exists( 'is_category' ) && is_category() && function_exists( 'get_queried_object_id' ) ) {
            $term_id = absint( get_queried_object_id() );
            $preset  = isset( $rules['categories'][ $term_id ] )
                ? self::sanitize_context_preset_key( $rules['categories'][ $term_id ], $options )
                : self::get_category_design_preset( $term_id, $options );
            if ( '' !== $preset ) {
                $target = array(
                    'source'   => 'category',
                    'objectId' => $term_id,
                    'preset'   => $preset,
                    'selector' => 'body.category',
                );
            }
        }

        if ( empty( $target ) && function_exists( 'is_singular' ) && is_singular( 'post' ) && function_exists( 'get_queried_object_id' ) && function_exists( 'get_the_category' ) ) {
            $post_id    = absint( get_queried_object_id() );
            $categories = get_the_category( $post_id );
            if ( is_array( $categories ) ) {
                foreach ( $categories as $category ) {
                    if ( ! $category instanceof \WP_Term ) {
                        continue;
                    }

                    $preset = isset( $rules['categories'][ $category->term_id ] )
                        ? self::sanitize_context_preset_key( $rules['categories'][ $category->term_id ], $options )
                        : self::get_category_design_preset( $category->term_id, $options );
                    if ( '' === $preset ) {
                        continue;
                    }

                    $target = array(
                        'source'   => 'post_category',
                        'objectId' => (int) $category->term_id,
                        'postId'   => $post_id,
                        'preset'   => $preset,
                        'selector' => 'body.single-post',
                    );
                    break;
                }
            }
        }

        if ( function_exists( 'apply_filters' ) ) {
            $target = apply_filters( 'developer_starter_context_design_preset_target', $target, $options );
        }

        return is_array( $target ) ? $target : array();
    }

    /**
     * @param array<string,mixed> $options    Theme options.
     * @param string              $preset_key Preset key.
     * @return array<string,mixed>
     */
    private static function build_options_with_context_preset( $options, $preset_key ) {
        $options    = is_array( $options ) ? $options : array();
        $preset_key = self::sanitize_context_preset_key( $preset_key, $options );
        if ( '' === $preset_key ) {
            return $options;
        }

        $design_system = self::get_normalized_design_system_v2( $options );
        $preset        = self::get_preset_payload( $preset_key, $options );

        $design_system['preset'] = $preset_key;
        $design_system['tokens'] = array_merge(
            isset( $design_system['tokens'] ) && is_array( $design_system['tokens'] ) ? $design_system['tokens'] : array(),
            isset( $preset['tokens'] ) && is_array( $preset['tokens'] ) ? $preset['tokens'] : array()
        );

        if ( ! empty( $preset['typography_system'] ) && is_array( $preset['typography_system'] ) ) {
            $design_system['typography_system'] = self::normalize_typography_system( $preset['typography_system'] );
        }

        if ( ! empty( $preset['layout_system'] ) && is_array( $preset['layout_system'] ) ) {
            $design_system['layout_system'] = self::normalize_layout_system( $preset['layout_system'] );
        }

        if ( ! empty( $preset['component_styles'] ) && is_array( $preset['component_styles'] ) ) {
            $design_system['component_styles'] = array_merge(
                isset( $design_system['component_styles'] ) && is_array( $design_system['component_styles'] ) ? $design_system['component_styles'] : array(),
                self::sanitize_component_style_map( $preset['component_styles'] )
            );
        }

        $options[ self::STORAGE_OPTION_KEY ] = $design_system;

        return array_merge(
            $options,
            self::build_compatibility_option_sync_from_design_system( $design_system )
        );
    }

    /**
     * @param string              $selector       CSS selector.
     * @param array<string,mixed> $base_options   Base theme options.
     * @param array<string,mixed> $scoped_options Context preset options.
     * @return string
     */
    private static function build_scoped_context_variable_css( $selector, $base_options, $scoped_options ) {
        $selector = trim( (string) $selector );
        if ( '' === $selector ) {
            return '';
        }

        $base_tokens    = self::get_current_tokens( $base_options );
        $scoped_tokens  = self::get_current_tokens( $scoped_options );
        $base_variables = array_merge(
            self::get_css_variables( $base_tokens ),
            self::get_component_css_variables( self::get_current_component_styles( $base_options, $base_tokens ) )
        );
        $scoped_variables = array_merge(
            self::get_css_variables( $scoped_tokens ),
            self::get_component_css_variables( self::get_current_component_styles( $scoped_options, $scoped_tokens ) )
        );
        $overrides = array();

        foreach ( $scoped_variables as $name => $value ) {
            $name  = trim( (string) $name );
            $value = trim( (string) $value );
            if ( '' === $name || '' === $value ) {
                continue;
            }

            $base_value = isset( $base_variables[ $name ] ) ? trim( (string) $base_variables[ $name ] ) : '';
            if ( $value === $base_value ) {
                continue;
            }

            $overrides[ $name ] = $value;
        }

        if ( empty( $overrides ) ) {
            return '';
        }

        $css = $selector . "{\n";
        foreach ( $overrides as $name => $value ) {
            $css .= '    ' . $name . ': ' . $value . ";\n";
        }
        $css .= "}\n";
        $css .= self::build_scoped_dark_runtime_css( $selector );
        $css .= self::build_scoped_responsive_runtime_css( $selector, $scoped_tokens );

        return $css;
    }

    /**
     * @param string $selector CSS selector.
     * @return string
     */
    private static function build_scoped_dark_runtime_css( $selector ) {
        $selector = trim( (string) $selector );
        if ( '' === $selector ) {
            return '';
        }

        $css  = 'html.dark-mode ' . $selector . ",[data-theme='dark'] " . $selector . "{\n";
        $css .= "    color-scheme: dark;\n";
        $css .= "    --color-dark: var(--qiling-dark-text);\n";
        $css .= "    --color-text: var(--qiling-dark-text);\n";
        $css .= "    --color-text-muted: var(--qiling-dark-text-muted);\n";
        $css .= "    --color-heading: var(--qiling-dark-text);\n";
        $css .= "    --color-background: var(--qiling-dark-bg);\n";
        $css .= "    --color-surface: var(--qiling-dark-surface);\n";
        $css .= "    --color-surface-alt: var(--qiling-dark-surface);\n";
        $css .= "    --color-border: var(--qiling-dark-border);\n";
        $css .= "    --dm-bg: var(--qiling-dark-bg);\n";
        $css .= "    --dm-bg-secondary: var(--qiling-dark-surface);\n";
        $css .= "    --dm-bg-card: var(--qiling-dark-surface);\n";
        $css .= "    --dm-text: var(--qiling-dark-text);\n";
        $css .= "    --dm-text-muted: var(--qiling-dark-text-muted);\n";
        $css .= "    --dm-border: var(--qiling-dark-border);\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * @param string               $selector CSS selector.
     * @param array<string,string> $tokens   Scoped tokens.
     * @return string
     */
    private static function build_scoped_responsive_runtime_css( $selector, $tokens ) {
        $selector = trim( (string) $selector );
        if ( '' === $selector ) {
            return '';
        }

        $css = $selector . "{\n";
        foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
            foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                $css .= '    ' . self::get_typography_css_var_name( $style_key, $property_key ) . ': var(' . self::get_typography_css_var_name( $style_key, $property_key, 'desktop' ) . ");\n";
            }
        }
        $css .= "    --qiling-container-width: var(--qiling-container-width-desktop);\n";
        $css .= "    --qiling-section-padding: var(--qiling-section-spacing-desktop);\n";
        $css .= "    --section-padding: var(--qiling-section-padding);\n";
        $css .= "    --qiling-grid-gap: var(--qiling-grid-gap-desktop);\n";
        $css .= "    --qiling-font-size-base: var(--qiling-body-font-size);\n";
        $css .= "    --qiling-line-height-base: var(--qiling-body-line-height);\n";
        $css .= "}\n";

        foreach ( array( 'tablet', 'mobile' ) as $device_key ) {
            $breakpoint_key = 'breakpoint_' . $device_key;
            if ( empty( $tokens[ $breakpoint_key ] ) ) {
                continue;
            }
            $css .= '@media (max-width:' . $tokens[ $breakpoint_key ] . "){\n";
            $css .= $selector . "{\n";
            foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
                foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                    $css .= '    ' . self::get_typography_css_var_name( $style_key, $property_key ) . ': var(' . self::get_typography_css_var_name( $style_key, $property_key, $device_key ) . ");\n";
                }
            }
            $css .= '    --qiling-container-width: var(--qiling-container-width-' . $device_key . ");\n";
            $css .= '    --qiling-section-padding: var(--qiling-section-spacing-' . $device_key . ");\n";
            $css .= '    --section-padding: var(--qiling-section-padding);' . "\n";
            $css .= '    --qiling-grid-gap: var(--qiling-grid-gap-' . $device_key . ");\n";
            $css .= '    --qiling-font-size-base: var(--qiling-body-font-size);' . "\n";
            $css .= '    --qiling-line-height-base: var(--qiling-body-line-height);' . "\n";
            $css .= "}\n";
            $css .= "}\n";
        }

        return $css;
    }

    /**
     * @param array<string,mixed> $options
     * @param array<string,mixed> $existing_options
     * @return array<string,mixed>
     */
    public static function sanitize_options( $options, $existing_options = array() ) {
        if ( ! is_array( $options ) ) {
            return array();
        }

        if ( isset( $options['primary_color'] ) ) {
            $options['primary_color'] = self::sanitize_css_color_value( $options['primary_color'] );
        }
        if ( isset( $options['primary_color'] ) && '' !== $options['primary_color'] && ! array_key_exists( 'design_primary_color', $options ) ) {
            // Legacy admin saves should continue to update the canonical design token state.
            $options['design_primary_color'] = $options['primary_color'];
        }
        if ( isset( $options['design_custom_presets_present'] ) ) {
            $options['design_custom_presets'] = isset( $options['design_custom_presets'] )
                ? self::sanitize_custom_presets( $options['design_custom_presets'] )
                : array();
        } elseif ( isset( $options['design_custom_presets'] ) ) {
            $options['design_custom_presets'] = self::sanitize_custom_presets( $options['design_custom_presets'] );
        }

        if ( isset( $options['design_enable_global_tokens'] ) ) {
            $options['design_enable_global_tokens'] = ( '1' === (string) $options['design_enable_global_tokens'] ) ? '1' : '';
        }

        if ( isset( $options['design_preset'] ) ) {
            $presets = self::get_style_presets(
                array_merge(
                    is_array( $existing_options ) ? $existing_options : array(),
                    $options
                )
            );
            $preset = sanitize_key( (string) $options['design_preset'] );
            $options['design_preset'] = isset( $presets[ $preset ] ) ? $preset : 'default';
        }

        $color_fields = array(
            'design_primary_color',
            'design_primary_hover_color',
            'design_secondary_color',
            'design_accent_color',
            'design_success_color',
            'design_info_color',
            'design_warning_color',
            'design_error_color',
            'design_overlay_color',
            'design_text_color',
            'design_text_muted_color',
            'design_heading_color',
            'design_background_color',
            'design_surface_color',
            'design_surface_alt_color',
            'design_border_color',
            'design_neutral_0',
            'design_neutral_50',
            'design_neutral_100',
            'design_neutral_200',
            'design_neutral_300',
            'design_neutral_400',
            'design_neutral_500',
            'design_neutral_600',
            'design_neutral_700',
            'design_neutral_800',
            'design_neutral_900',
            'design_dark_bg',
            'design_dark_surface',
            'design_dark_text',
            'design_dark_text_muted',
            'design_dark_border',
        );
        foreach ( $color_fields as $field ) {
            if ( isset( $options[ $field ] ) ) {
                $options[ $field ] = self::sanitize_css_color_value( $options[ $field ] );
            }
        }

        if ( isset( $options['design_font_family'] ) ) {
            $options['design_font_family'] = self::sanitize_font_stack( $options['design_font_family'] );
        }

        if ( isset( $options['design_typography_system'] ) ) {
            $options['design_typography_system'] = self::normalize_typography_system( $options['design_typography_system'] );
        }

        if ( isset( $options['design_layout_system'] ) ) {
            $options['design_layout_system'] = self::normalize_layout_system( $options['design_layout_system'] );
        }

        $length_fields = array(
            'design_font_size_base',
            'design_container_width',
            'design_section_padding',
            'design_grid_gap',
            'design_card_radius',
            'design_button_radius',
            'design_input_radius',
        );
        foreach ( $length_fields as $field ) {
            if ( isset( $options[ $field ] ) ) {
                $options[ $field ] = self::sanitize_length_value( $options[ $field ] );
            }
        }

        if ( isset( $options['design_line_height_base'] ) ) {
            $options['design_line_height_base'] = self::sanitize_line_height_value( $options['design_line_height_base'] );
        }
        if ( isset( $options['design_breakpoint_tablet'] ) ) {
            $options['design_breakpoint_tablet'] = self::sanitize_breakpoint_value( $options['design_breakpoint_tablet'] );
        }
        if ( isset( $options['design_breakpoint_mobile'] ) ) {
            $options['design_breakpoint_mobile'] = self::sanitize_breakpoint_value( $options['design_breakpoint_mobile'] );
        }
        if ( isset( $options['design_layout_mode'] ) && ! in_array( (string) $options['design_layout_mode'], array( 'wide', 'boxed' ), true ) ) {
            $options['design_layout_mode'] = 'wide';
        }
        if ( isset( $options['design_animation_speed'] ) ) {
            $options['design_animation_speed'] = self::sanitize_duration_value( $options['design_animation_speed'] );
        }

        $shadow_fields = array( 'design_shadow_sm', 'design_shadow_md', 'design_shadow_lg' );
        foreach ( $shadow_fields as $field ) {
            if ( isset( $options[ $field ] ) ) {
                $options[ $field ] = self::sanitize_shadow_value( $options[ $field ] );
            }
        }

        $component_paint_fields = array(
            'design_component_button_bg',
            'design_component_button_hover_bg',
            'design_component_button_secondary_bg',
            'design_component_button_secondary_hover_bg',
            'design_component_card_bg',
            'design_component_title_bar_bg',
            'design_component_list_header_bg',
            'design_component_highlight_bg',
            'design_component_highlight_soft_bg',
            'design_component_form_input_bg',
            'design_component_post_card_bg',
            'design_component_header_bg',
            'design_component_header_logo_transparent_fill',
            'design_component_header_logo_scrolled_fill',
            'design_component_header_phone_bg',
            'design_component_header_phone_transparent_bg',
            'design_component_nav_hover_bg',
            'design_component_mobile_nav_bg',
            'design_component_mobile_nav_hover_bg',
            'design_component_dropdown_bg',
            'design_component_dropdown_hover_bg',
            'design_component_badge_bg',
            'design_component_tabs_active_bg',
            'design_component_accordion_bg',
            'design_component_pagination_bg',
            'design_component_pagination_active_bg',
            'design_component_breadcrumb_bg',
            'design_component_alert_bg',
            'design_component_modal_bg',
            'design_component_sidebar_bg',
            'design_component_footer_bg',
            'design_component_footer_bottom_bg',
            'design_component_woo_card_bg',
        );
        foreach ( $component_paint_fields as $field ) {
            if ( isset( $options[ $field ] ) ) {
                $options[ $field ] = self::sanitize_paint_value( $options[ $field ] );
            }
        }

        $component_color_fields = array(
            'design_component_button_text',
            'design_component_button_border',
            'design_component_button_hover_text',
            'design_component_button_secondary_text',
            'design_component_button_secondary_border',
            'design_component_border_accent',
            'design_component_card_border',
            'design_component_title_bar_text',
            'design_component_title_bar_border',
            'design_component_list_header_text',
            'design_component_list_header_border',
            'design_component_highlight_text',
            'design_component_highlight_border',
            'design_component_form_input_text',
            'design_component_form_input_border',
            'design_component_form_focus_border',
            'design_component_module_title_color',
            'design_component_post_card_border',
            'design_component_post_card_title_color',
            'design_component_post_card_meta_color',
            'design_component_header_border',
            'design_component_header_text',
            'design_component_header_scrolled_text',
            'design_component_header_phone_text',
            'design_component_header_phone_transparent_text',
            'design_component_nav_link',
            'design_component_nav_scrolled_link',
            'design_component_nav_hover_text',
            'design_component_nav_scrolled_hover_text',
            'design_component_mobile_nav_border',
            'design_component_mobile_nav_link',
            'design_component_mobile_nav_hover_text',
            'design_component_dropdown_border',
            'design_component_dropdown_link',
            'design_component_dropdown_hover_text',
            'design_component_badge_text',
            'design_component_badge_border',
            'design_component_tabs_border',
            'design_component_tabs_text',
            'design_component_tabs_active_text',
            'design_component_tabs_active_border',
            'design_component_accordion_border',
            'design_component_accordion_title',
            'design_component_pagination_border',
            'design_component_pagination_text',
            'design_component_pagination_active_text',
            'design_component_breadcrumb_text',
            'design_component_breadcrumb_link',
            'design_component_alert_border',
            'design_component_alert_text',
            'design_component_modal_border',
            'design_component_modal_title',
            'design_component_sidebar_border',
            'design_component_sidebar_title',
            'design_component_footer_text',
            'design_component_footer_heading',
            'design_component_footer_link',
            'design_component_footer_link_hover',
            'design_component_woo_card_border',
            'design_component_woo_card_title',
            'design_component_woo_card_price',
        );
        foreach ( $component_color_fields as $field ) {
            if ( isset( $options[ $field ] ) ) {
                $options[ $field ] = self::sanitize_css_color_value( $options[ $field ] );
            }
        }

        $component_shadow_fields = array(
            'design_component_button_shadow',
            'design_component_card_shadow',
            'design_component_post_card_shadow',
            'design_component_header_shadow',
            'design_component_dropdown_shadow',
            'design_component_modal_shadow',
            'design_component_sidebar_shadow',
            'design_component_woo_card_shadow',
        );
        foreach ( $component_shadow_fields as $field ) {
            if ( isset( $options[ $field ] ) ) {
                $options[ $field ] = self::sanitize_shadow_value( $options[ $field ] );
            }
        }

        if ( isset( $options['design_component_button_padding'] ) ) {
            $options['design_component_button_padding'] = self::sanitize_box_spacing_value( $options['design_component_button_padding'] );
        }
        if ( isset( $options['design_component_heading_letter_spacing'] ) ) {
            $options['design_component_heading_letter_spacing'] = self::sanitize_box_spacing_value( $options['design_component_heading_letter_spacing'], 1 );
        }
        if ( isset( $options['design_component_module_title_size'] ) ) {
            $options['design_component_module_title_size'] = self::sanitize_length_value( $options['design_component_module_title_size'] );
        }
        if ( isset( $options['design_component_footer_heading_size'] ) ) {
            $options['design_component_footer_heading_size'] = self::sanitize_length_value( $options['design_component_footer_heading_size'] );
        }
        if ( isset( $options['design_component_heading_weight'] ) && ! in_array( (string) $options['design_component_heading_weight'], array( '500', '600', '700', '800', '900' ), true ) ) {
            $options['design_component_heading_weight'] = '700';
        }
        if ( isset( $options['design_component_module_title_align'] ) && ! in_array( (string) $options['design_component_module_title_align'], array( 'left', 'center', 'right' ), true ) ) {
            $options['design_component_module_title_align'] = 'center';
        }

        if ( isset( $options['design_preset_context_rules_present'] ) || isset( $options['design_preset_context_rules'] ) ) {
            $options['design_preset_context_rules'] = self::sanitize_context_preset_rules(
                isset( $options['design_preset_context_rules'] ) ? $options['design_preset_context_rules'] : array(),
                array_merge( is_array( $existing_options ) ? $existing_options : array(), $options )
            );
        }

        $effective_options = array_merge( is_array( $existing_options ) ? $existing_options : array(), $options );
        $design_system_v2 = self::get_normalized_design_system_v2( $effective_options );
        $options = array_merge( self::build_compatibility_option_sync_from_design_system( $design_system_v2 ), $options );
        $options[ self::STORAGE_OPTION_KEY ] = $design_system_v2;
        unset( $options['design_custom_presets_present'] );
        unset( $options['design_typography_system_present'] );
        unset( $options['design_layout_system_present'] );
        unset( $options['design_preset_context_rules_present'] );

        $resolved_options = array_merge(
            is_array( $existing_options ) ? $existing_options : array(),
            $options,
            array(
                self::STORAGE_OPTION_KEY => $design_system_v2,
            )
        );
        $tokens = self::get_current_tokens( $resolved_options );
        if ( ! empty( $tokens['primary'] ) && self::is_hex_color( $tokens['primary'] ) ) {
            $options['primary_color'] = $tokens['primary'];
            if ( function_exists( 'set_theme_mod' ) ) {
                set_theme_mod( self::LEGACY_CUSTOMIZER_PRIMARY_COLOR, $tokens['primary'] );
            }
        }

        return $options;
    }

    /**
     * @return array<string,string>
     */
    private static function get_structural_defaults() {
        return array(
            'font_family'      => '-apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "Helvetica Neue", Helvetica, Arial, sans-serif',
            'font_size_base'   => '16px',
            'line_height_base' => '1.6',
            'container_width'  => '1200px',
            'section_padding'  => '50px',
            'grid_gap'         => '30px',
            'breakpoint_tablet' => '992px',
            'breakpoint_mobile' => '768px',
            'layout_mode'      => 'wide',
            'card_radius'      => '8px',
            'button_radius'    => '8px',
            'input_radius'     => '8px',
            'animation_speed'  => '0.25s',
            'shadow_sm'        => '0 1px 2px rgba(0, 0, 0, 0.05)',
            'shadow_md'        => '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
            'shadow_lg'        => '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        );
    }

    /**
     * @return array<string,string>
     */
    private static function get_default_palette_tokens() {
        return self::get_design_token_data( 'default_palette_tokens' );
    }

    /**
     * @return array<int,string>
     */
    private static function get_palette_preset_token_keys() {
        return self::get_design_token_data( 'palette_preset_token_keys' );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private static function get_system_style_presets() {
        $presets = self::get_design_token_data( 'system_style_presets' );

        foreach ( $presets as $preset_key => $preset ) {
            $label = isset( $preset['label'] ) ? (string) $preset['label'] : $preset_key;
            $tokens = isset( $preset['tokens'] ) && is_array( $preset['tokens'] ) ? $preset['tokens'] : array();
            $component_styles = isset( $preset['component_styles'] ) && is_array( $preset['component_styles'] )
                ? $preset['component_styles']
                : array();
            $presets[ $preset_key ] = array(
                'label'            => $label,
                'tokens'           => self::normalize_palette_preset_tokens( $tokens ),
                'component_styles' => self::sanitize_component_style_map( $component_styles ),
                'source'           => 'system',
            );
        }

        return $presets;
    }

    /**
     * @param array<string,mixed>|null $options
     * @return array<string,array<string,mixed>>
     */
    private static function get_user_style_presets( $options = null ) {
        $design_system = self::get_normalized_design_system_v2( $options );
        $custom_presets = isset( $design_system['custom_presets'] ) && is_array( $design_system['custom_presets'] )
            ? $design_system['custom_presets']
            : array();

        $presets = array();
        foreach ( $custom_presets as $preset ) {
            if ( ! is_array( $preset ) || empty( $preset['id'] ) ) {
                continue;
            }

            $preset_id = sanitize_key( (string) $preset['id'] );
            if ( '' === $preset_id ) {
                continue;
            }

            $label = isset( $preset['label'] ) ? sanitize_text_field( (string) $preset['label'] ) : $preset_id;
            $tokens = isset( $preset['tokens'] ) && is_array( $preset['tokens'] ) ? $preset['tokens'] : array();
            $presets[ $preset_id ] = array(
                'label'            => '' !== $label ? $label : $preset_id,
                'tokens'           => self::normalize_palette_preset_tokens( $tokens ),
                'typography_system' => isset( $preset['typography_system'] ) && is_array( $preset['typography_system'] )
                    ? self::normalize_typography_system( $preset['typography_system'] )
                    : array(),
                'layout_system'    => isset( $preset['layout_system'] ) && is_array( $preset['layout_system'] )
                    ? self::normalize_layout_system( $preset['layout_system'] )
                    : array(),
                'component_styles' => isset( $preset['component_styles'] ) && is_array( $preset['component_styles'] )
                    ? self::sanitize_component_style_map( $preset['component_styles'] )
                    : array(),
                'source'           => 'custom',
            );
        }

        return $presets;
    }

    /**
     * @param array<string,mixed> $tokens
     * @return array<string,string>
     */
    private static function normalize_palette_preset_tokens( $tokens ) {
        $defaults = self::get_default_palette_tokens();
        $normalized = $defaults;

        foreach ( self::get_palette_preset_token_keys() as $token_key ) {
            if ( ! array_key_exists( $token_key, $tokens ) ) {
                continue;
            }

            $value = self::sanitize_token_value_by_key( $token_key, $tokens[ $token_key ] );
            if ( '' !== $value ) {
                $normalized[ $token_key ] = $value;
            }
        }

        $primary = isset( $normalized['primary'] ) ? $normalized['primary'] : $defaults['primary'];
        if ( self::is_hex_color( $primary ) ) {
            $normalized['primary_hover'] = isset( $normalized['primary_hover'] ) && '' !== $normalized['primary_hover']
                ? $normalized['primary_hover']
                : self::shift_hex_color( $primary, -16 );
        }

        return array_map( 'strval', $normalized );
    }

    /**
     * @param mixed $presets
     * @return array<int,array<string,mixed>>
     */
    private static function sanitize_custom_presets( $presets ) {
        if ( ! is_array( $presets ) ) {
            return array();
        }

        $system_keys = array_keys( self::get_system_style_presets() );
        $used_ids = array();
        $sanitized = array();

        foreach ( $presets as $preset ) {
            if ( ! is_array( $preset ) ) {
                continue;
            }

            $raw_label = isset( $preset['label'] ) ? sanitize_text_field( (string) $preset['label'] ) : '';
            $raw_id = '';
            if ( isset( $preset['id'] ) ) {
                $raw_id = sanitize_key( (string) $preset['id'] );
            } elseif ( isset( $preset['key'] ) ) {
                $raw_id = sanitize_key( (string) $preset['key'] );
            }
            if ( '' === $raw_id && '' !== $raw_label ) {
                $raw_id = sanitize_key( $raw_label );
            }
            if ( '' === $raw_id ) {
                $raw_id = 'custom-preset';
            }

            $preset_id = $raw_id;
            $suffix = 2;
            while ( in_array( $preset_id, $system_keys, true ) || in_array( $preset_id, $used_ids, true ) ) {
                $preset_id = $raw_id . '-' . $suffix;
                $suffix++;
            }

            $tokens = array();
            $raw_tokens = isset( $preset['tokens'] ) && is_array( $preset['tokens'] ) ? $preset['tokens'] : array();
            foreach ( self::get_palette_preset_token_keys() as $token_key ) {
                if ( ! array_key_exists( $token_key, $raw_tokens ) ) {
                    continue;
                }

                $value = self::sanitize_token_value_by_key( $token_key, $raw_tokens[ $token_key ] );
                if ( '' !== $value ) {
                    $tokens[ $token_key ] = $value;
                }
            }

            if ( empty( $tokens ) ) {
                continue;
            }

            $typography_system = array();
            if ( isset( $preset['typography_system'] ) && is_array( $preset['typography_system'] ) ) {
                $typography_system = self::normalize_typography_system( $preset['typography_system'] );
            } elseif ( isset( $preset['typographySystem'] ) && is_array( $preset['typographySystem'] ) ) {
                $typography_system = self::normalize_typography_system( $preset['typographySystem'] );
            } elseif ( isset( $preset['typography_json'] ) && is_scalar( $preset['typography_json'] ) ) {
                $decoded_typography = json_decode( (string) $preset['typography_json'], true );
                if ( is_array( $decoded_typography ) ) {
                    $typography_system = self::normalize_typography_system( $decoded_typography );
                }
            }

            $layout_system = array();
            if ( isset( $preset['layout_system'] ) && is_array( $preset['layout_system'] ) ) {
                $layout_system = self::normalize_layout_system( $preset['layout_system'] );
            } elseif ( isset( $preset['layoutSystem'] ) && is_array( $preset['layoutSystem'] ) ) {
                $layout_system = self::normalize_layout_system( $preset['layoutSystem'] );
            } elseif ( isset( $preset['layout_json'] ) && is_scalar( $preset['layout_json'] ) ) {
                $decoded_layout = json_decode( (string) $preset['layout_json'], true );
                if ( is_array( $decoded_layout ) ) {
                    $layout_system = self::normalize_layout_system( $decoded_layout );
                }
            }

            $component_styles = array();
            if ( isset( $preset['component_styles'] ) && is_array( $preset['component_styles'] ) ) {
                $component_styles = self::sanitize_component_style_map( $preset['component_styles'] );
            } elseif ( isset( $preset['componentStyles'] ) && is_array( $preset['componentStyles'] ) ) {
                $component_styles = self::sanitize_component_style_map( $preset['componentStyles'] );
            } elseif ( isset( $preset['components_json'] ) && is_scalar( $preset['components_json'] ) ) {
                $decoded_components = json_decode( (string) $preset['components_json'], true );
                if ( is_array( $decoded_components ) ) {
                    $component_styles = self::sanitize_component_style_map( $decoded_components );
                }
            }

            $used_ids[] = $preset_id;
            $sanitized_preset = array(
                'id'     => $preset_id,
                'label'  => '' !== $raw_label ? $raw_label : ucfirst( str_replace( '-', ' ', $preset_id ) ),
                'tokens' => self::normalize_palette_preset_tokens( $tokens ),
            );
            if ( ! empty( $typography_system ) ) {
                $sanitized_preset['typography_system'] = $typography_system;
            }
            if ( ! empty( $layout_system ) ) {
                $sanitized_preset['layout_system'] = $layout_system;
            }
            if ( ! empty( $component_styles ) ) {
                $sanitized_preset['component_styles'] = $component_styles;
            }
            $sanitized[] = $sanitized_preset;
        }

        return $sanitized;
    }

    /**
     * @param mixed $styles
     * @return array<string,string>
     */
    private static function sanitize_component_style_map( $styles ) {
        if ( ! is_array( $styles ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( self::get_design_component_schema() as $style_key => $type ) {
            if ( ! array_key_exists( $style_key, $styles ) ) {
                continue;
            }

            $value = self::sanitize_design_value_by_type( $type, $styles[ $style_key ] );
            if ( '' !== $value ) {
                $sanitized[ $style_key ] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * @return array<string,string>
     */
    private static function get_design_token_option_map() {
        return self::get_design_token_data( 'design_token_option_map' );
    }

    /**
     * @return array<string,string>
     */
    private static function get_design_component_option_map() {
        return self::get_design_token_data( 'design_component_option_map' );
    }

    /**
     * @return array<string,string>
     */
    private static function get_design_token_schema() {
        return self::get_design_token_data( 'design_token_schema' );
    }

    /**
     * @return array<string,string>
     */
    private static function get_design_component_schema() {
        return self::get_design_token_data( 'design_component_schema' );
    }

    /**
     * 兼容旧方法名。
     *
     * `get_component_style_definitions()` 早期调用过这个名字，
     * 现在统一回到 design component schema，避免线上旧链路直接 fatal。
     *
     * @return array<string,string>
     */
    private static function get_component_style_type_map() {
        return self::get_design_component_schema();
    }

    /**
     * @return array<string,array<string,array<string,string>>>
     */
    private static function get_default_typography_system() {
        return self::get_design_token_data( 'default_typography_system' );
    }

    /**
     * @return array<string,mixed>
     */
    private static function get_default_layout_system() {
        return self::get_design_token_data( 'default_layout_system' );
    }

    /**
     * @return array<string,mixed>
     */
    private static function get_default_page_design_overrides() {
        $component_styles = array();
        foreach ( self::get_page_component_style_groups() as $group ) {
            $fields = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : array();
            foreach ( array_keys( $fields ) as $style_key ) {
                $component_styles[ $style_key ] = '';
            }
        }

        return array(
            'palette' => array(
                'primary'     => '',
                'secondary'   => '',
                'accent'      => '',
                'success'     => '',
                'info'        => '',
                'warning'     => '',
                'error'       => '',
                'overlay'     => '',
                'background'  => '',
                'surface'     => '',
                'surface_alt' => '',
                'text'        => '',
                'text_muted'  => '',
                'heading'     => '',
                'border'      => '',
                'dark_bg'     => '',
                'dark_surface' => '',
                'dark_text'   => '',
                'dark_text_muted' => '',
                'dark_border' => '',
            ),
            'layout'  => array(
                'container_width' => array(
                    'desktop' => '',
                    'tablet'  => '',
                    'mobile'  => '',
                ),
                'section_spacing' => array(
                    'desktop' => '',
                    'tablet'  => '',
                    'mobile'  => '',
                ),
                'grid_gap'        => array(
                    'desktop' => '',
                    'tablet'  => '',
                    'mobile'  => '',
                ),
                'layout_mode'     => '',
            ),
            'structure' => array(
                'card_radius' => '',
                'button_radius' => '',
                'input_radius' => '',
                'animation_speed' => '',
            ),
            'typography' => self::get_empty_page_typography_overrides(),
            'component_styles' => $component_styles,
        );
    }

    /**
     * @param mixed       $overrides 原始页面覆盖。
     * @param string|null $format 输出格式。
     * @return array<string,mixed>
     */
    public static function sanitize_page_design_overrides( $overrides, $format = 'storage' ) {
        $format = is_scalar( $format ) ? sanitize_key( (string) $format ) : 'storage';
        $defaults = self::get_default_page_design_overrides();
        $normalized = $defaults;
        $overrides = is_array( $overrides ) ? $overrides : array();

        $palette = isset( $overrides['palette'] ) && is_array( $overrides['palette'] ) ? $overrides['palette'] : array();
        $layout = isset( $overrides['layout'] ) && is_array( $overrides['layout'] ) ? $overrides['layout'] : array();
        $structure = isset( $overrides['structure'] ) && is_array( $overrides['structure'] ) ? $overrides['structure'] : array();
        $typography = array();
        if ( isset( $overrides['typography'] ) && is_array( $overrides['typography'] ) ) {
            $typography = $overrides['typography'];
        } elseif ( isset( $overrides['typography_system'] ) && is_array( $overrides['typography_system'] ) ) {
            $typography = $overrides['typography_system'];
        } elseif ( isset( $overrides['typographySystem'] ) && is_array( $overrides['typographySystem'] ) ) {
            $typography = $overrides['typographySystem'];
        }
        $component_styles = isset( $overrides['component_styles'] ) && is_array( $overrides['component_styles'] ) ? $overrides['component_styles'] : array();
        if ( isset( $overrides['componentStyles'] ) && is_array( $overrides['componentStyles'] ) ) {
            $component_styles = $overrides['componentStyles'];
        }

        foreach ( $defaults['palette'] as $field_key => $default_value ) {
            $raw_value = '';
            if ( array_key_exists( $field_key, $palette ) ) {
                $raw_value = $palette[ $field_key ];
            } elseif ( 'surface_alt' === $field_key && array_key_exists( 'surfaceAlt', $palette ) ) {
                $raw_value = $palette['surfaceAlt'];
            } elseif ( 'text_muted' === $field_key && array_key_exists( 'textMuted', $palette ) ) {
                $raw_value = $palette['textMuted'];
            } elseif ( 'dark_bg' === $field_key && array_key_exists( 'darkBg', $palette ) ) {
                $raw_value = $palette['darkBg'];
            } elseif ( 'dark_surface' === $field_key && array_key_exists( 'darkSurface', $palette ) ) {
                $raw_value = $palette['darkSurface'];
            } elseif ( 'dark_text' === $field_key && array_key_exists( 'darkText', $palette ) ) {
                $raw_value = $palette['darkText'];
            } elseif ( 'dark_text_muted' === $field_key && array_key_exists( 'darkTextMuted', $palette ) ) {
                $raw_value = $palette['darkTextMuted'];
            } elseif ( 'dark_border' === $field_key && array_key_exists( 'darkBorder', $palette ) ) {
                $raw_value = $palette['darkBorder'];
            }

            $normalized['palette'][ $field_key ] = ( is_scalar( $raw_value ) && '' !== trim( (string) $raw_value ) )
                ? self::sanitize_css_color_value( $raw_value )
                : $default_value;
        }

        foreach ( array( 'container_width', 'section_spacing', 'grid_gap' ) as $field_key ) {
            $raw_values = isset( $layout[ $field_key ] ) && is_array( $layout[ $field_key ] )
                ? $layout[ $field_key ]
                : array();
            if ( 'container_width' === $field_key && isset( $layout['containerWidth'] ) && is_array( $layout['containerWidth'] ) ) {
                $raw_values = $layout['containerWidth'];
            } elseif ( 'section_spacing' === $field_key && isset( $layout['sectionSpacing'] ) && is_array( $layout['sectionSpacing'] ) ) {
                $raw_values = $layout['sectionSpacing'];
            } elseif ( 'grid_gap' === $field_key && isset( $layout['gridGap'] ) && is_array( $layout['gridGap'] ) ) {
                $raw_values = $layout['gridGap'];
            }

            foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                $value = self::sanitize_length_value( $raw_values[ $device_key ] ?? '' );
                $normalized['layout'][ $field_key ][ $device_key ] = '' !== $value ? $value : '';
            }
        }

        $layout_mode = '';
        if ( isset( $layout['layout_mode'] ) ) {
            $layout_mode = sanitize_key( (string) $layout['layout_mode'] );
        } elseif ( isset( $layout['layoutMode'] ) ) {
            $layout_mode = sanitize_key( (string) $layout['layoutMode'] );
        }
        $normalized['layout']['layout_mode'] = in_array( $layout_mode, array( 'wide', 'boxed' ), true ) ? $layout_mode : '';

        $structure_aliases = self::get_page_structure_field_aliases();
        foreach ( array_keys( $defaults['structure'] ) as $field_key ) {
            $raw_value = '';
            if ( array_key_exists( $field_key, $structure ) ) {
                $raw_value = $structure[ $field_key ];
            } elseif ( isset( $structure_aliases[ $field_key ] ) && array_key_exists( $structure_aliases[ $field_key ], $structure ) ) {
                $raw_value = $structure[ $structure_aliases[ $field_key ] ];
            }

            $value = self::sanitize_token_value_by_key( $field_key, $raw_value );
            $normalized['structure'][ $field_key ] = '' !== $value ? $value : '';
        }

        $property_aliases = self::get_page_typography_property_aliases();
        foreach ( array_keys( $defaults['typography'] ) as $style_key ) {
            $style_values = isset( $typography[ $style_key ] ) && is_array( $typography[ $style_key ] )
                ? $typography[ $style_key ]
                : array();

            foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                $device_values = isset( $style_values[ $device_key ] ) && is_array( $style_values[ $device_key ] )
                    ? $style_values[ $device_key ]
                    : array();

                foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                    $raw_value = '';
                    if ( array_key_exists( $property_key, $device_values ) ) {
                        $raw_value = $device_values[ $property_key ];
                    } elseif ( isset( $property_aliases[ $property_key ] ) && array_key_exists( $property_aliases[ $property_key ], $device_values ) ) {
                        $raw_value = $device_values[ $property_aliases[ $property_key ] ];
                    }

                    $value = self::sanitize_typography_value_by_property( $property_key, $raw_value );
                    $normalized['typography'][ $style_key ][ $device_key ][ $property_key ] = '' !== $value ? $value : '';
                }
            }
        }

        foreach ( array_keys( $defaults['component_styles'] ) as $style_key ) {
            $value = self::sanitize_component_style_value_by_key( $style_key, $component_styles[ $style_key ] ?? '' );
            $normalized['component_styles'][ $style_key ] = '' !== $value ? $value : '';
        }

        if ( 'builder' === $format ) {
            return self::format_page_design_overrides_for_builder( $normalized );
        }

        if ( 'package' === $format ) {
            return self::compact_page_design_overrides( $normalized );
        }

        return $normalized;
    }

    /**
     * @param int         $post_id 页面 ID。
     * @param string|null $format 输出格式。
     * @return array<string,mixed>
     */
    public static function get_page_design_overrides( $post_id, $format = 'storage' ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return self::sanitize_page_design_overrides( array(), $format );
        }

        $stored = get_post_meta( $post_id, self::PAGE_DESIGN_META_KEY, true );
        return self::sanitize_page_design_overrides( $stored, $format );
    }

    /**
     * @param int   $post_id 页面 ID。
     * @param mixed $overrides 覆盖值。
     * @return void
     */
    public static function persist_page_design_overrides( $post_id, $overrides ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return;
        }

        $normalized = self::sanitize_page_design_overrides( $overrides, 'storage' );
        $compact = self::compact_page_design_overrides( $normalized );
        if ( empty( $compact ) ) {
            delete_post_meta( $post_id, self::PAGE_DESIGN_META_KEY );
            return;
        }

        update_post_meta( $post_id, self::PAGE_DESIGN_META_KEY, $compact );
    }

    /**
     * @param int                      $post_id 页面 ID。
     * @param array<string,mixed>|null $options Optional option source.
     * @return string
     */
    public static function get_page_design_preset( $post_id, $options = null ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return '';
        }

        return self::sanitize_context_preset_key( get_post_meta( $post_id, self::PAGE_DESIGN_PRESET_META_KEY, true ), $options );
    }

    /**
     * @param int                      $post_id 页面 ID。
     * @param mixed                    $preset_key Preset key or inherit.
     * @param array<string,mixed>|null $options Optional option source.
     * @return void
     */
    public static function persist_page_design_preset( $post_id, $preset_key, $options = null ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return;
        }

        $preset_key = self::sanitize_context_preset_key( $preset_key, $options );
        if ( '' === $preset_key ) {
            delete_post_meta( $post_id, self::PAGE_DESIGN_PRESET_META_KEY );
            return;
        }

        update_post_meta( $post_id, self::PAGE_DESIGN_PRESET_META_KEY, $preset_key );
    }

    /**
     * @param int                      $term_id 分类 ID。
     * @param array<string,mixed>|null $options Optional option source.
     * @return string
     */
    public static function get_category_design_preset( $term_id, $options = null ) {
        $term_id = absint( $term_id );
        if ( $term_id <= 0 ) {
            return '';
        }

        return self::sanitize_context_preset_key( get_term_meta( $term_id, self::CATEGORY_DESIGN_PRESET_META_KEY, true ), $options );
    }

    /**
     * @param int                      $term_id 分类 ID。
     * @param mixed                    $preset_key Preset key or inherit.
     * @param array<string,mixed>|null $options Optional option source.
     * @return void
     */
    public static function persist_category_design_preset( $term_id, $preset_key, $options = null ) {
        $term_id = absint( $term_id );
        if ( $term_id <= 0 ) {
            return;
        }

        $preset_key = self::sanitize_context_preset_key( $preset_key, $options );
        if ( '' === $preset_key ) {
            delete_term_meta( $term_id, self::CATEGORY_DESIGN_PRESET_META_KEY );
            return;
        }

        update_term_meta( $term_id, self::CATEGORY_DESIGN_PRESET_META_KEY, $preset_key );
    }

    /**
     * @param array<string,mixed>|null $options 主题设置。
     * @param int                      $post_id 页面 ID。
     * @return array<string,mixed>
     */
    private static function build_options_with_page_design_overrides( $options, $post_id ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        $overrides = self::get_page_design_overrides( $post_id, 'storage' );
        $compact_overrides = self::compact_page_design_overrides( $overrides );
        if ( empty( $compact_overrides ) ) {
            return $options;
        }

        $design_system = self::get_normalized_design_system_v2( $options );
        $typography_system = isset( $design_system['typography_system'] ) && is_array( $design_system['typography_system'] )
            ? self::normalize_typography_system( $design_system['typography_system'] )
            : self::get_default_typography_system();
        $layout_system = isset( $design_system['layout_system'] ) && is_array( $design_system['layout_system'] )
            ? self::normalize_layout_system( $design_system['layout_system'] )
            : self::get_default_layout_system();
        $tokens = isset( $design_system['tokens'] ) && is_array( $design_system['tokens'] ) ? $design_system['tokens'] : array();
        $component_styles = isset( $design_system['component_styles'] ) && is_array( $design_system['component_styles'] )
            ? $design_system['component_styles']
            : array();

        if ( ! empty( $compact_overrides['palette'] ) && is_array( $compact_overrides['palette'] ) ) {
            foreach ( $compact_overrides['palette'] as $token_key => $value ) {
                $tokens[ $token_key ] = $value;
            }
        }

        if ( ! empty( $compact_overrides['layout'] ) && is_array( $compact_overrides['layout'] ) ) {
            foreach ( array( 'container_width', 'section_spacing', 'grid_gap' ) as $field_key ) {
                if ( empty( $compact_overrides['layout'][ $field_key ] ) || ! is_array( $compact_overrides['layout'][ $field_key ] ) ) {
                    continue;
                }
                foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                    if ( ! empty( $compact_overrides['layout'][ $field_key ][ $device_key ] ) ) {
                        $layout_system[ $field_key ][ $device_key ] = $compact_overrides['layout'][ $field_key ][ $device_key ];
                    }
                }
            }

            if ( ! empty( $compact_overrides['layout']['layout_mode'] ) ) {
                $layout_system['layout_mode'] = sanitize_key( (string) $compact_overrides['layout']['layout_mode'] );
            }
        }

        if ( ! empty( $compact_overrides['structure'] ) && is_array( $compact_overrides['structure'] ) ) {
            foreach ( $compact_overrides['structure'] as $field_key => $value ) {
                $tokens[ $field_key ] = (string) $value;
            }
        }

        if ( ! empty( $compact_overrides['typography'] ) && is_array( $compact_overrides['typography'] ) ) {
            foreach ( $compact_overrides['typography'] as $style_key => $style_values ) {
                if ( ! isset( $typography_system[ $style_key ] ) || ! is_array( $style_values ) ) {
                    continue;
                }

                foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                    if ( empty( $style_values[ $device_key ] ) || ! is_array( $style_values[ $device_key ] ) ) {
                        continue;
                    }

                    foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                        if ( ! empty( $style_values[ $device_key ][ $property_key ] ) ) {
                            $typography_system[ $style_key ][ $device_key ][ $property_key ] = $style_values[ $device_key ][ $property_key ];
                        }
                    }
                }
            }
        }

        if ( ! empty( $compact_overrides['component_styles'] ) && is_array( $compact_overrides['component_styles'] ) ) {
            foreach ( $compact_overrides['component_styles'] as $style_key => $value ) {
                $component_styles[ $style_key ] = (string) $value;
            }
        }

        $design_system['tokens'] = $tokens;
        $design_system['typography_system'] = $typography_system;
        $design_system['layout_system'] = $layout_system;
        $design_system['component_styles'] = $component_styles;
        $options[ self::STORAGE_OPTION_KEY ] = $design_system;

        return $options;
    }

    /**
     * @param array<string,mixed> $overrides 标准化覆盖。
     * @return array<string,mixed>
     */
    private static function format_page_design_overrides_for_builder( $overrides ) {
        $overrides = self::sanitize_page_design_overrides( $overrides, 'storage' );

        return array(
            'palette' => array(
                'primary'    => $overrides['palette']['primary'],
                'secondary'  => $overrides['palette']['secondary'],
                'accent'     => $overrides['palette']['accent'],
                'success'    => $overrides['palette']['success'],
                'info'       => $overrides['palette']['info'],
                'warning'    => $overrides['palette']['warning'],
                'error'      => $overrides['palette']['error'],
                'overlay'    => $overrides['palette']['overlay'],
                'background' => $overrides['palette']['background'],
                'surface'    => $overrides['palette']['surface'],
                'surfaceAlt' => $overrides['palette']['surface_alt'],
                'text'       => $overrides['palette']['text'],
                'textMuted'  => $overrides['palette']['text_muted'],
                'heading'    => $overrides['palette']['heading'],
                'border'     => $overrides['palette']['border'],
                'darkBg'     => $overrides['palette']['dark_bg'],
                'darkSurface' => $overrides['palette']['dark_surface'],
                'darkText'   => $overrides['palette']['dark_text'],
                'darkTextMuted' => $overrides['palette']['dark_text_muted'],
                'darkBorder' => $overrides['palette']['dark_border'],
            ),
            'layout'  => array(
                'containerWidth' => $overrides['layout']['container_width'],
                'sectionSpacing' => $overrides['layout']['section_spacing'],
                'gridGap'        => $overrides['layout']['grid_gap'],
                'layoutMode'     => $overrides['layout']['layout_mode'],
            ),
            'structure' => array(
                'cardRadius' => $overrides['structure']['card_radius'],
                'buttonRadius' => $overrides['structure']['button_radius'],
                'inputRadius' => $overrides['structure']['input_radius'],
                'animationSpeed' => $overrides['structure']['animation_speed'],
            ),
            'typography' => self::format_page_typography_overrides_for_builder( $overrides['typography'] ),
            'componentStyles' => $overrides['component_styles'],
        );
    }

    /**
     * @param array<string,array<string,array<string,string>>> $typography_overrides 页面级排版覆盖。
     * @return array<string,array<string,array<string,string>>>
     */
    private static function format_page_typography_overrides_for_builder( $typography_overrides ) {
        $formatted = array();
        $aliases = self::get_page_typography_property_aliases();

        foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
            $formatted[ $style_key ] = array();
            foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                $formatted[ $style_key ][ $device_key ] = array();
                foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                    $builder_key = isset( $aliases[ $property_key ] ) ? $aliases[ $property_key ] : $property_key;
                    $formatted[ $style_key ][ $device_key ][ $builder_key ] = isset( $typography_overrides[ $style_key ][ $device_key ][ $property_key ] )
                        ? (string) $typography_overrides[ $style_key ][ $device_key ][ $property_key ]
                        : '';
                }
            }
        }

        return $formatted;
    }

    /**
     * @param array<string,mixed> $overrides 标准化覆盖。
     * @return array<string,mixed>
     */
    private static function compact_page_design_overrides( $overrides ) {
        $overrides = self::sanitize_page_design_overrides( $overrides, 'storage' );
        $compact = array(
            'palette' => array(),
            'layout'  => array(),
            'structure' => array(),
            'typography' => array(),
            'component_styles' => array(),
        );

        foreach ( $overrides['palette'] as $field_key => $value ) {
            if ( '' !== (string) $value ) {
                $compact['palette'][ $field_key ] = (string) $value;
            }
        }

        foreach ( array( 'container_width', 'section_spacing', 'grid_gap' ) as $field_key ) {
            $field_values = array();
            foreach ( $overrides['layout'][ $field_key ] as $device_key => $value ) {
                if ( '' !== (string) $value ) {
                    $field_values[ $device_key ] = (string) $value;
                }
            }
            if ( ! empty( $field_values ) ) {
                $compact['layout'][ $field_key ] = $field_values;
            }
        }

        if ( '' !== (string) $overrides['layout']['layout_mode'] ) {
            $compact['layout']['layout_mode'] = (string) $overrides['layout']['layout_mode'];
        }

        foreach ( $overrides['structure'] as $field_key => $value ) {
            if ( '' !== (string) $value ) {
                $compact['structure'][ $field_key ] = (string) $value;
            }
        }

        $compact['typography'] = self::compact_page_typography_overrides( $overrides['typography'] );

        foreach ( $overrides['component_styles'] as $style_key => $value ) {
            if ( '' !== (string) $value ) {
                $compact['component_styles'][ $style_key ] = (string) $value;
            }
        }

        if ( empty( $compact['palette'] ) ) {
            unset( $compact['palette'] );
        }
        if ( empty( $compact['layout'] ) ) {
            unset( $compact['layout'] );
        }
        if ( empty( $compact['structure'] ) ) {
            unset( $compact['structure'] );
        }
        if ( empty( $compact['typography'] ) ) {
            unset( $compact['typography'] );
        }
        if ( empty( $compact['component_styles'] ) ) {
            unset( $compact['component_styles'] );
        }

        return $compact;
    }

    /**
     * @param array<string,array<string,array<string,string>>> $typography_overrides 页面级排版覆盖。
     * @return array<string,array<string,array<string,string>>>
     */
    private static function compact_page_typography_overrides( $typography_overrides ) {
        $compact = array();

        foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
            foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                    $value = isset( $typography_overrides[ $style_key ][ $device_key ][ $property_key ] )
                        ? (string) $typography_overrides[ $style_key ][ $device_key ][ $property_key ]
                        : '';
                    if ( '' === $value ) {
                        continue;
                    }

                    if ( ! isset( $compact[ $style_key ] ) ) {
                        $compact[ $style_key ] = array();
                    }
                    if ( ! isset( $compact[ $style_key ][ $device_key ] ) ) {
                        $compact[ $style_key ][ $device_key ] = array();
                    }

                    $compact[ $style_key ][ $device_key ][ $property_key ] = $value;
                }
            }
        }

        return $compact;
    }

    /**
     * @param mixed $system
     * @return array<string,array<string,array<string,string>>>
     */
    private static function normalize_typography_system( $system ) {
        $defaults = self::get_default_typography_system();
        $normalized = $defaults;
        if ( ! is_array( $system ) ) {
            return $normalized;
        }

        foreach ( $defaults as $style_key => $device_values ) {
            if ( ! isset( $system[ $style_key ] ) || ! is_array( $system[ $style_key ] ) ) {
                continue;
            }

            foreach ( $device_values as $device_key => $properties ) {
                if ( ! isset( $system[ $style_key ][ $device_key ] ) || ! is_array( $system[ $style_key ][ $device_key ] ) ) {
                    continue;
                }

                foreach ( $properties as $property_key => $default_value ) {
                    $value = self::sanitize_typography_value_by_property( $property_key, $system[ $style_key ][ $device_key ][ $property_key ] ?? '' );
                    if ( '' !== $value ) {
                        $normalized[ $style_key ][ $device_key ][ $property_key ] = $value;
                    } else {
                        $normalized[ $style_key ][ $device_key ][ $property_key ] = $default_value;
                    }
                }
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $system
     * @return array<string,mixed>
     */
    private static function normalize_layout_system( $system ) {
        $defaults = self::get_default_layout_system();
        $normalized = $defaults;
        if ( ! is_array( $system ) ) {
            return $normalized;
        }

        foreach ( array( 'container_width', 'section_spacing', 'grid_gap' ) as $responsive_key ) {
            if ( ! isset( $system[ $responsive_key ] ) || ! is_array( $system[ $responsive_key ] ) ) {
                continue;
            }
            foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
                $value = self::sanitize_length_value( $system[ $responsive_key ][ $device_key ] ?? '' );
                if ( '' !== $value ) {
                    $normalized[ $responsive_key ][ $device_key ] = $value;
                }
            }
        }

        if ( isset( $system['breakpoints'] ) && is_array( $system['breakpoints'] ) ) {
            foreach ( array( 'tablet', 'mobile' ) as $device_key ) {
                $value = self::sanitize_breakpoint_value( $system['breakpoints'][ $device_key ] ?? '' );
                if ( '' !== $value ) {
                    $normalized['breakpoints'][ $device_key ] = $value;
                }
            }
        }

        if ( isset( $system['layout_mode'] ) ) {
            $layout_mode = is_scalar( $system['layout_mode'] ) ? sanitize_key( (string) $system['layout_mode'] ) : '';
            if ( in_array( $layout_mode, array( 'wide', 'boxed' ), true ) ) {
                $normalized['layout_mode'] = $layout_mode;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string,array<string,array<string,string>>> $system
     * @return array<string,string>
     */
    private static function flatten_typography_system_tokens( $system ) {
        $tokens = array();
        foreach ( self::get_typography_style_definitions() as $style_key => $style_definition ) {
            unset( $style_definition );
            foreach ( self::get_responsive_device_definitions() as $device_key => $device_definition ) {
                unset( $device_definition );
                foreach ( self::get_typography_property_definitions() as $property_key => $property_definition ) {
                    unset( $property_definition );
                    $token_key = self::get_typography_system_token_key( $style_key, $property_key, $device_key );
                    $tokens[ $token_key ] = isset( $system[ $style_key ][ $device_key ][ $property_key ] )
                        ? (string) $system[ $style_key ][ $device_key ][ $property_key ]
                        : '';
                }
            }
        }

        return $tokens;
    }

    /**
     * @param array<string,mixed> $system
     * @return array<string,string>
     */
    private static function flatten_layout_system_tokens( $system ) {
        $tokens = array();
        foreach ( array_keys( self::get_responsive_device_definitions() ) as $device_key ) {
            $tokens[ 'container_width_' . $device_key ] = isset( $system['container_width'][ $device_key ] ) ? (string) $system['container_width'][ $device_key ] : '';
            $tokens[ 'section_spacing_' . $device_key ] = isset( $system['section_spacing'][ $device_key ] ) ? (string) $system['section_spacing'][ $device_key ] : '';
            $tokens[ 'grid_gap_' . $device_key ] = isset( $system['grid_gap'][ $device_key ] ) ? (string) $system['grid_gap'][ $device_key ] : '';
        }
        $tokens['breakpoint_tablet'] = isset( $system['breakpoints']['tablet'] ) ? (string) $system['breakpoints']['tablet'] : '992px';
        $tokens['breakpoint_mobile'] = isset( $system['breakpoints']['mobile'] ) ? (string) $system['breakpoints']['mobile'] : '768px';
        $tokens['layout_mode'] = isset( $system['layout_mode'] ) ? (string) $system['layout_mode'] : 'wide';

        return $tokens;
    }

    /**
     * @param array<string,mixed> $design_system
     * @return array<string,mixed>
     */
    private static function inject_responsive_system_compat_tokens( $design_system ) {
        $design_system['tokens'] = isset( $design_system['tokens'] ) && is_array( $design_system['tokens'] ) ? $design_system['tokens'] : array();
        $design_system['typography_system'] = isset( $design_system['typography_system'] ) && is_array( $design_system['typography_system'] )
            ? self::normalize_typography_system( $design_system['typography_system'] )
            : self::get_default_typography_system();
        $design_system['layout_system'] = isset( $design_system['layout_system'] ) && is_array( $design_system['layout_system'] )
            ? self::normalize_layout_system( $design_system['layout_system'] )
            : self::get_default_layout_system();

        if ( ! empty( $design_system['tokens']['font_size_base'] ) ) {
            $body_font_size = self::sanitize_length_value( $design_system['tokens']['font_size_base'] );
            if ( '' !== $body_font_size ) {
                $design_system['typography_system']['body']['desktop']['font_size'] = $body_font_size;
            }
        }
        if ( ! empty( $design_system['tokens']['line_height_base'] ) ) {
            $body_line_height = self::sanitize_line_height_value( $design_system['tokens']['line_height_base'] );
            if ( '' !== $body_line_height ) {
                $design_system['typography_system']['body']['desktop']['line_height'] = $body_line_height;
            }
        }
        if ( ! empty( $design_system['tokens']['container_width'] ) ) {
            $container_width = self::sanitize_length_value( $design_system['tokens']['container_width'] );
            if ( '' !== $container_width ) {
                $design_system['layout_system']['container_width']['desktop'] = $container_width;
            }
        }
        if ( ! empty( $design_system['tokens']['section_padding'] ) ) {
            $section_spacing = self::sanitize_length_value( $design_system['tokens']['section_padding'] );
            if ( '' !== $section_spacing ) {
                $design_system['layout_system']['section_spacing']['desktop'] = $section_spacing;
            }
        }
        if ( ! empty( $design_system['tokens']['grid_gap'] ) ) {
            $grid_gap = self::sanitize_length_value( $design_system['tokens']['grid_gap'] );
            if ( '' !== $grid_gap ) {
                $design_system['layout_system']['grid_gap']['desktop'] = $grid_gap;
            }
        }
        if ( ! empty( $design_system['tokens']['breakpoint_tablet'] ) ) {
            $tablet_breakpoint = self::sanitize_breakpoint_value( $design_system['tokens']['breakpoint_tablet'] );
            if ( '' !== $tablet_breakpoint ) {
                $design_system['layout_system']['breakpoints']['tablet'] = $tablet_breakpoint;
            }
        }
        if ( ! empty( $design_system['tokens']['breakpoint_mobile'] ) ) {
            $mobile_breakpoint = self::sanitize_breakpoint_value( $design_system['tokens']['breakpoint_mobile'] );
            if ( '' !== $mobile_breakpoint ) {
                $design_system['layout_system']['breakpoints']['mobile'] = $mobile_breakpoint;
            }
        }
        if ( ! empty( $design_system['tokens']['layout_mode'] ) ) {
            $layout_mode = sanitize_key( (string) $design_system['tokens']['layout_mode'] );
            if ( in_array( $layout_mode, array( 'wide', 'boxed' ), true ) ) {
                $design_system['layout_system']['layout_mode'] = $layout_mode;
            }
        }

        $design_system['tokens']['font_size_base'] = $design_system['typography_system']['body']['desktop']['font_size'];
        $design_system['tokens']['line_height_base'] = $design_system['typography_system']['body']['desktop']['line_height'];
        $design_system['tokens']['container_width'] = $design_system['layout_system']['container_width']['desktop'];
        $design_system['tokens']['section_padding'] = $design_system['layout_system']['section_spacing']['desktop'];
        $design_system['tokens']['grid_gap'] = $design_system['layout_system']['grid_gap']['desktop'];
        $design_system['tokens']['breakpoint_tablet'] = $design_system['layout_system']['breakpoints']['tablet'];
        $design_system['tokens']['breakpoint_mobile'] = $design_system['layout_system']['breakpoints']['mobile'];
        $design_system['tokens']['layout_mode'] = $design_system['layout_system']['layout_mode'];

        return $design_system;
    }

    /**
     * @param string $style_key
     * @param string $property_key
     * @param string $device_key
     * @return string
     */
    private static function get_typography_system_token_key( $style_key, $property_key, $device_key ) {
        return sanitize_key( $style_key . '_' . $property_key . '_' . $device_key );
    }

    /**
     * @param string      $style_key
     * @param string      $property_key
     * @param string|null $device_key
     * @return string
     */
    private static function get_typography_css_var_name( $style_key, $property_key, $device_key = null ) {
        $suffix = null !== $device_key ? '-' . sanitize_key( (string) $device_key ) : '';
        return '--qiling-' . sanitize_key( (string) $style_key ) . '-' . str_replace( '_', '-', sanitize_key( (string) $property_key ) ) . $suffix;
    }

    /**
     * @param array<string,string> $tokens
     * @return string
     */
    private static function build_responsive_runtime_css( $tokens ) {
        $css = ":root{\n";
        foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
            foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                $css .= '    ' . self::get_typography_css_var_name( $style_key, $property_key ) . ': var(' . self::get_typography_css_var_name( $style_key, $property_key, 'desktop' ) . ");\n";
            }
        }
        $css .= "    --qiling-container-width: var(--qiling-container-width-desktop);\n";
        $css .= "    --qiling-section-padding: var(--qiling-section-spacing-desktop);\n";
        $css .= "    --section-padding: var(--qiling-section-padding);\n";
        $css .= "    --qiling-grid-gap: var(--qiling-grid-gap-desktop);\n";
        $css .= "    --qiling-font-size-base: var(--qiling-body-font-size);\n";
        $css .= "    --qiling-line-height-base: var(--qiling-body-line-height);\n";
        $css .= "}\n";

        foreach ( array( 'tablet', 'mobile' ) as $device_key ) {
            $breakpoint_key = 'breakpoint_' . $device_key;
            if ( empty( $tokens[ $breakpoint_key ] ) ) {
                continue;
            }
            $css .= '@media (max-width:' . $tokens[ $breakpoint_key ] . "){\n";
            $css .= ":root{\n";
            foreach ( array_keys( self::get_typography_style_definitions() ) as $style_key ) {
                foreach ( array_keys( self::get_typography_property_definitions() ) as $property_key ) {
                    $css .= '    ' . self::get_typography_css_var_name( $style_key, $property_key ) . ': var(' . self::get_typography_css_var_name( $style_key, $property_key, $device_key ) . ");\n";
                }
            }
            $css .= '    --qiling-container-width: var(--qiling-container-width-' . $device_key . ");\n";
            $css .= '    --qiling-section-padding: var(--qiling-section-spacing-' . $device_key . ");\n";
            $css .= '    --section-padding: var(--qiling-section-padding);' . "\n";
            $css .= '    --qiling-grid-gap: var(--qiling-grid-gap-' . $device_key . ");\n";
            $css .= '    --qiling-font-size-base: var(--qiling-body-font-size);' . "\n";
            $css .= '    --qiling-line-height-base: var(--qiling-body-line-height);' . "\n";
            $css .= "}\n";
            $css .= "}\n";
        }

        return $css;
    }

    /**
     * @param string $property_key
     * @param mixed  $value
     * @return string
     */
    private static function sanitize_typography_value_by_property( $property_key, $value ) {
        switch ( $property_key ) {
            case 'font_size':
                return self::sanitize_length_value( $value );
            case 'line_height':
                return self::sanitize_line_height_value( $value );
            case 'font_weight':
                return self::sanitize_font_weight_value( $value );
            case 'letter_spacing':
                return self::sanitize_letter_spacing_value( $value );
            default:
                return '';
        }
    }

    /**
     * @param array<string,mixed>|null $options
     * @return array<string,mixed>
     */
    private static function get_stored_design_system_v2( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        if ( ! isset( $options[ self::STORAGE_OPTION_KEY ] ) || ! is_array( $options[ self::STORAGE_OPTION_KEY ] ) ) {
            return array();
        }

        $raw = $options[ self::STORAGE_OPTION_KEY ];
        $stored = array(
            'schema_version' => self::TOKEN_SCHEMA_VERSION,
        );

        $raw_custom_presets = array();
        if ( isset( $raw['custom_presets'] ) && is_array( $raw['custom_presets'] ) ) {
            $raw_custom_presets = $raw['custom_presets'];
        } elseif ( isset( $raw['customPresets'] ) && is_array( $raw['customPresets'] ) ) {
            $raw_custom_presets = $raw['customPresets'];
        }
        $stored_custom_presets = ! empty( $raw_custom_presets )
            ? self::sanitize_custom_presets( $raw_custom_presets )
            : array();
        $presets = self::get_system_style_presets();
        foreach ( $stored_custom_presets as $preset ) {
            if ( ! is_array( $preset ) || empty( $preset['id'] ) ) {
                continue;
            }
            $presets[ sanitize_key( (string) $preset['id'] ) ] = $preset;
        }

        $schema_version = '';
        if ( isset( $raw['schema_version'] ) && is_scalar( $raw['schema_version'] ) ) {
            $schema_version = sanitize_text_field( (string) $raw['schema_version'] );
        } elseif ( isset( $raw['schemaVersion'] ) && is_scalar( $raw['schemaVersion'] ) ) {
            $schema_version = sanitize_text_field( (string) $raw['schemaVersion'] );
        }
        if ( '' !== $schema_version ) {
            $stored['schema_version'] = $schema_version;
        }

        if ( array_key_exists( 'enabled', $raw ) ) {
            $stored['enabled'] = self::normalize_bool_flag( $raw['enabled'], true );
        }

        if ( isset( $raw['preset'] ) && is_scalar( $raw['preset'] ) ) {
            $preset = sanitize_key( (string) $raw['preset'] );
            if ( isset( $presets[ $preset ] ) ) {
                $stored['preset'] = $preset;
            }
        }

        $raw_tokens = isset( $raw['tokens'] ) && is_array( $raw['tokens'] ) ? $raw['tokens'] : array();
        if ( ! empty( $raw_tokens ) ) {
            $stored['tokens'] = array();
            foreach ( self::get_design_token_schema() as $token_key => $type ) {
                if ( ! array_key_exists( $token_key, $raw_tokens ) ) {
                    continue;
                }
                $value = self::sanitize_design_value_by_type( $type, $raw_tokens[ $token_key ] );
                if ( '' !== $value ) {
                    $stored['tokens'][ $token_key ] = $value;
                }
            }
        }

        $raw_typography_system = array();
        if ( isset( $raw['typography_system'] ) && is_array( $raw['typography_system'] ) ) {
            $raw_typography_system = $raw['typography_system'];
        } elseif ( isset( $raw['typographySystem'] ) && is_array( $raw['typographySystem'] ) ) {
            $raw_typography_system = $raw['typographySystem'];
        }
        if ( ! empty( $raw_typography_system ) ) {
            $stored['typography_system'] = self::normalize_typography_system( $raw_typography_system );
        }

        $raw_layout_system = array();
        if ( isset( $raw['layout_system'] ) && is_array( $raw['layout_system'] ) ) {
            $raw_layout_system = $raw['layout_system'];
        } elseif ( isset( $raw['layoutSystem'] ) && is_array( $raw['layoutSystem'] ) ) {
            $raw_layout_system = $raw['layoutSystem'];
        }
        if ( ! empty( $raw_layout_system ) ) {
            $stored['layout_system'] = self::normalize_layout_system( $raw_layout_system );
        }

        $raw_component_styles = array();
        if ( isset( $raw['component_styles'] ) && is_array( $raw['component_styles'] ) ) {
            $raw_component_styles = $raw['component_styles'];
        } elseif ( isset( $raw['componentStyles'] ) && is_array( $raw['componentStyles'] ) ) {
            $raw_component_styles = $raw['componentStyles'];
        }
        if ( ! empty( $raw_component_styles ) ) {
            $stored['component_styles'] = array();
            foreach ( self::get_design_component_schema() as $style_key => $type ) {
                if ( ! array_key_exists( $style_key, $raw_component_styles ) ) {
                    continue;
                }
                $value = self::sanitize_design_value_by_type( $type, $raw_component_styles[ $style_key ] );
                if ( '' !== $value ) {
                    $stored['component_styles'][ $style_key ] = $value;
                }
            }
        }

        if ( ! empty( $stored_custom_presets ) ) {
            $stored['custom_presets'] = $stored_custom_presets;
        }

        if ( isset( $raw['compat'] ) && is_array( $raw['compat'] ) ) {
            $compat = array();
            foreach ( $raw['compat'] as $compat_key => $compat_value ) {
                if ( ! is_scalar( $compat_value ) ) {
                    continue;
                }
                $compat_value = sanitize_text_field( (string) $compat_value );
                if ( '' !== $compat_value ) {
                    $compat[ sanitize_key( (string) $compat_key ) ] = $compat_value;
                }
            }
            if ( ! empty( $compat ) ) {
                $stored['compat'] = $compat;
            }
        }

        return $stored;
    }

    /**
     * @param array<string,mixed>|null $options
     * @return array<string,mixed>
     */
    private static function get_normalized_design_system_v2( $options = null ) {
        $options = is_array( $options ) ? $options : self::get_theme_options();
        $presets = self::get_system_style_presets();
        $stored  = self::get_stored_design_system_v2( $options );
        $has_stored_design_system = isset( $options[ self::STORAGE_OPTION_KEY ] ) && is_array( $options[ self::STORAGE_OPTION_KEY ] );

        $design_system = array(
            'schema_version'   => self::TOKEN_SCHEMA_VERSION,
            'enabled'          => isset( $stored['enabled'] ) ? (bool) $stored['enabled'] : true,
            'preset'           => isset( $stored['preset'] ) ? (string) $stored['preset'] : 'default',
            'tokens'           => isset( $stored['tokens'] ) && is_array( $stored['tokens'] ) ? $stored['tokens'] : array(),
            'typography_system' => isset( $stored['typography_system'] ) && is_array( $stored['typography_system'] ) ? self::normalize_typography_system( $stored['typography_system'] ) : self::get_default_typography_system(),
            'layout_system'    => isset( $stored['layout_system'] ) && is_array( $stored['layout_system'] ) ? self::normalize_layout_system( $stored['layout_system'] ) : self::get_default_layout_system(),
            'component_styles' => isset( $stored['component_styles'] ) && is_array( $stored['component_styles'] ) ? $stored['component_styles'] : array(),
            'custom_presets'   => isset( $stored['custom_presets'] ) && is_array( $stored['custom_presets'] ) ? $stored['custom_presets'] : array(),
            'compat'           => array(),
        );

        if ( array_key_exists( 'design_enable_global_tokens', $options ) ) {
            $design_system['enabled'] = '1' === (string) $options['design_enable_global_tokens'];
        }

        foreach ( self::get_design_token_option_map() as $option_key => $token_key ) {
            if ( ! array_key_exists( $option_key, $options ) ) {
                continue;
            }
            $value = self::sanitize_token_value_by_key( $token_key, $options[ $option_key ] );
            if ( '' !== $value ) {
                $design_system['tokens'][ $token_key ] = $value;
            } else {
                unset( $design_system['tokens'][ $token_key ] );
            }
        }

        foreach ( self::get_design_component_option_map() as $option_key => $style_key ) {
            if ( ! array_key_exists( $option_key, $options ) ) {
                continue;
            }
            $value = self::sanitize_component_style_value_by_key( $style_key, $options[ $option_key ] );
            if ( '' !== $value ) {
                $design_system['component_styles'][ $style_key ] = $value;
            } else {
                unset( $design_system['component_styles'][ $style_key ] );
            }
        }

        if ( array_key_exists( 'design_typography_system', $options ) ) {
            $design_system['typography_system'] = self::normalize_typography_system( $options['design_typography_system'] );
        }

        if ( array_key_exists( 'design_layout_system', $options ) ) {
            $design_system['layout_system'] = self::normalize_layout_system( $options['design_layout_system'] );
        }

        if ( array_key_exists( 'design_custom_presets', $options ) ) {
            $design_system['custom_presets'] = self::sanitize_custom_presets( $options['design_custom_presets'] );
        }
        foreach ( $design_system['custom_presets'] as $custom_preset ) {
            if ( ! is_array( $custom_preset ) || empty( $custom_preset['id'] ) ) {
                continue;
            }
            $presets[ sanitize_key( (string) $custom_preset['id'] ) ] = $custom_preset;
        }

        if ( array_key_exists( 'design_preset', $options ) ) {
            $preset = sanitize_key( (string) $options['design_preset'] );
            $design_system['preset'] = isset( $presets[ $preset ] ) ? $preset : 'default';
        }

        if ( ! $has_stored_design_system && ! isset( $design_system['tokens']['primary'] ) ) {
            $legacy_primary = self::get_legacy_primary_color_value( $options, '' );
            if ( '' !== $legacy_primary ) {
                $design_system['tokens']['primary'] = $legacy_primary;
            }
        }

        if ( ! isset( $presets[ $design_system['preset'] ] ) ) {
            $design_system['preset'] = 'default';
        }

        $legacy_primary = self::get_legacy_primary_color_value( $options, '' );
        if ( '' !== $legacy_primary ) {
            $design_system['compat']['legacy_primary_color'] = $legacy_primary;
        }
        if ( function_exists( 'get_theme_mod' ) ) {
            $customizer_primary = self::sanitize_css_color_value( get_theme_mod( self::LEGACY_CUSTOMIZER_PRIMARY_COLOR, '' ) );
            if ( '' !== $customizer_primary ) {
                $design_system['compat']['legacy_customizer_primary_color'] = $customizer_primary;
            }
        }

        $design_system = self::inject_responsive_system_compat_tokens( $design_system );

        return $design_system;
    }

    /**
     * @param array<string,mixed> $design_system
     * @return array<string,mixed>
     */
    private static function build_compatibility_option_sync_from_design_system( $design_system ) {
        $options = array(
            'design_enable_global_tokens' => ! empty( $design_system['enabled'] ) ? '1' : '',
            'design_preset'               => isset( $design_system['preset'] ) ? sanitize_key( (string) $design_system['preset'] ) : 'default',
        );

        $token_values = isset( $design_system['tokens'] ) && is_array( $design_system['tokens'] ) ? $design_system['tokens'] : array();
        foreach ( self::get_design_token_option_map() as $option_key => $token_key ) {
            if ( array_key_exists( $token_key, $token_values ) ) {
                $options[ $option_key ] = (string) $token_values[ $token_key ];
            }
        }

        if ( isset( $design_system['typography_system'] ) && is_array( $design_system['typography_system'] ) ) {
            $options['design_typography_system'] = $design_system['typography_system'];
        }

        if ( isset( $design_system['layout_system'] ) && is_array( $design_system['layout_system'] ) ) {
            $options['design_layout_system'] = $design_system['layout_system'];
        }

        $component_styles = isset( $design_system['component_styles'] ) && is_array( $design_system['component_styles'] ) ? $design_system['component_styles'] : array();
        foreach ( self::get_design_component_option_map() as $option_key => $style_key ) {
            if ( array_key_exists( $style_key, $component_styles ) ) {
                $options[ $option_key ] = (string) $component_styles[ $style_key ];
            }
        }

        $options['design_custom_presets'] = isset( $design_system['custom_presets'] ) && is_array( $design_system['custom_presets'] )
            ? $design_system['custom_presets']
            : array();

        return $options;
    }

    /**
     * 递归合并 design_system_v2，已存在的 base 值优先，fallback 仅补齐缺项。
     *
     * @param mixed $base     基线 payload。
     * @param mixed $fallback 备用 payload。
     * @return mixed
     */
    private static function merge_design_system_payload_preserving_base( $base, $fallback ) {
        if ( ! is_array( $base ) ) {
            return $base;
        }

        if ( ! is_array( $fallback ) ) {
            return $base;
        }

        if ( empty( $base ) ) {
            return $fallback;
        }

        if ( self::is_list_array( $base ) || self::is_list_array( $fallback ) ) {
            return $base;
        }

        $merged = $fallback;
        foreach ( $base as $key => $value ) {
            if ( array_key_exists( $key, $fallback ) ) {
                $merged[ $key ] = self::merge_design_system_payload_preserving_base( $value, $fallback[ $key ] );
            } else {
                $merged[ $key ] = $value;
            }
        }

        return $merged;
    }

    /**
     * 判断数组是否为 list 结构。
     *
     * @param mixed $value 待判断值。
     * @return bool
     */
    private static function is_list_array( $value ) {
        if ( ! is_array( $value ) ) {
            return false;
        }

        return array_keys( $value ) === range( 0, count( $value ) - 1 );
    }

    /**
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    private static function normalize_runtime_theme_options( $options ) {
        $options = is_array( $options ) ? $options : array();

        $initial_design_system = self::get_normalized_design_system_v2( $options );
        $normalized = array_merge(
            $options,
            self::build_compatibility_option_sync_from_design_system( $initial_design_system ),
            array(
                self::STORAGE_OPTION_KEY => $initial_design_system,
            )
        );

        $normalized = self::migrate_legacy_component_styles_to_single_source( $normalized );

        $final_design_system = self::get_normalized_design_system_v2( $normalized );
        $normalized = array_merge(
            $normalized,
            self::build_compatibility_option_sync_from_design_system( $final_design_system ),
            array(
                self::STORAGE_OPTION_KEY => $final_design_system,
            )
        );

        $tokens = self::get_current_tokens( $normalized );
        if ( ! empty( $tokens['primary'] ) && self::is_hex_color( $tokens['primary'] ) ) {
            $normalized['primary_color'] = $tokens['primary'];
        }

        return $normalized;
    }

    /**
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    private static function migrate_legacy_component_styles_to_single_source( $options ) {
        $options = is_array( $options ) ? $options : array();

        $header_bg = self::get_runtime_option_string_value( $options, 'header_bg_color' );
        if ( '' !== $header_bg ) {
            self::maybe_copy_runtime_option( $options, 'design_component_header_bg', $header_bg );
            unset( $options['header_bg_color'] );
        }

        $header_text = self::get_runtime_option_string_value( $options, 'header_text_color' );
        if ( '' !== $header_text ) {
            $has_header_text = self::runtime_option_has_custom_value( $options, 'design_component_header_text' );
            $has_nav_link    = self::runtime_option_has_custom_value( $options, 'design_component_nav_link' );
            if ( ! $has_header_text ) {
                self::maybe_copy_runtime_option( $options, 'design_component_header_text', $header_text );
            }
            if ( ! $has_nav_link && ! $has_header_text ) {
                self::maybe_copy_runtime_option( $options, 'design_component_nav_link', $header_text );
            }
            unset( $options['header_text_color'] );
        }

        $nav_hover_bg = self::get_runtime_option_string_value( $options, 'nav_hover_bg' );
        if ( '' !== $nav_hover_bg ) {
            self::maybe_copy_runtime_option( $options, 'design_component_nav_hover_bg', $nav_hover_bg );
            unset( $options['nav_hover_bg'] );
        }

        $nav_hover_text = self::get_runtime_option_string_value( $options, 'nav_hover_text' );
        if ( '' !== $nav_hover_text ) {
            self::maybe_copy_runtime_option( $options, 'design_component_nav_hover_text', $nav_hover_text );
            unset( $options['nav_hover_text'] );
        }

        $scrolled_menu_text = self::get_runtime_option_string_value( $options, 'scrolled_menu_text_color' );
        if ( '' !== $scrolled_menu_text ) {
            if ( ! self::runtime_option_has_custom_value( $options, 'design_component_header_scrolled_text' ) ) {
                self::maybe_copy_runtime_option( $options, 'design_component_header_scrolled_text', $scrolled_menu_text );
            }
            if ( ! self::runtime_option_has_custom_value( $options, 'design_component_nav_scrolled_link' ) ) {
                self::maybe_copy_runtime_option( $options, 'design_component_nav_scrolled_link', $scrolled_menu_text );
            }
            unset( $options['scrolled_menu_text_color'] );
        }

        $scrolled_menu_hover = self::get_runtime_option_string_value( $options, 'scrolled_menu_hover_color' );
        if ( '' !== $scrolled_menu_hover ) {
            if ( ! self::runtime_option_has_custom_value( $options, 'design_component_nav_scrolled_hover_text' ) ) {
                self::maybe_copy_runtime_option( $options, 'design_component_nav_scrolled_hover_text', $scrolled_menu_hover );
            }
            unset( $options['scrolled_menu_hover_color'] );
        }

        $logo_transparent_fill = self::get_runtime_option_string_value( $options, 'logo_bg_color' );
        if ( '' !== $logo_transparent_fill ) {
            self::maybe_copy_runtime_option( $options, 'design_component_header_logo_transparent_fill', $logo_transparent_fill );
            unset( $options['logo_bg_color'] );
        }

        $logo_scrolled_fill = self::get_runtime_option_string_value( $options, 'logo_scrolled_bg_color' );
        if ( '' !== $logo_scrolled_fill ) {
            self::maybe_copy_runtime_option( $options, 'design_component_header_logo_scrolled_fill', $logo_scrolled_fill );
            unset( $options['logo_scrolled_bg_color'] );
        }

        $phone_bg_normal = self::get_runtime_option_string_value( $options, 'phone_bg_normal' );
        if ( '' !== $phone_bg_normal ) {
            self::maybe_copy_runtime_option( $options, 'design_component_header_phone_bg', $phone_bg_normal );
            unset( $options['phone_bg_normal'] );
        }

        $phone_text_normal = self::get_runtime_option_string_value( $options, 'phone_text_normal' );
        if ( '' !== $phone_text_normal ) {
            self::maybe_copy_runtime_option( $options, 'design_component_header_phone_text', $phone_text_normal );
            unset( $options['phone_text_normal'] );
        }

        $phone_bg_transparent = self::get_runtime_option_string_value( $options, 'phone_bg_transparent' );
        if ( '' !== $phone_bg_transparent ) {
            self::maybe_copy_runtime_option( $options, 'design_component_header_phone_transparent_bg', $phone_bg_transparent );
            unset( $options['phone_bg_transparent'] );
        }

        $phone_text_transparent = self::get_runtime_option_string_value( $options, 'phone_text_transparent' );
        if ( '' !== $phone_text_transparent ) {
            self::maybe_copy_runtime_option( $options, 'design_component_header_phone_transparent_text', $phone_text_transparent );
            unset( $options['phone_text_transparent'] );
        }

        $footer_bg = self::get_runtime_option_string_value( $options, 'footer_widgets_bg' );
        if ( '' !== $footer_bg ) {
            self::maybe_copy_runtime_option( $options, 'design_component_footer_bg', $footer_bg );
            unset( $options['footer_widgets_bg'] );
        }

        $footer_bottom = self::get_runtime_option_string_value( $options, 'footer_bottom_bg' );
        if ( '' !== $footer_bottom ) {
            self::maybe_copy_runtime_option( $options, 'design_component_footer_bottom_bg', $footer_bottom );
            unset( $options['footer_bottom_bg'] );
        }

        $footer_text = self::get_runtime_option_string_value( $options, 'footer_text_color' );
        if ( '' !== $footer_text ) {
            $has_footer_text = self::runtime_option_has_custom_value( $options, 'design_component_footer_text' );
            $has_footer_link = self::runtime_option_has_custom_value( $options, 'design_component_footer_link' );
            if ( ! $has_footer_text ) {
                self::maybe_copy_runtime_option( $options, 'design_component_footer_text', $footer_text );
            }
            if ( ! $has_footer_link && ! $has_footer_text ) {
                self::maybe_copy_runtime_option( $options, 'design_component_footer_link', $footer_text );
            }
            unset( $options['footer_text_color'] );
        }

        $footer_heading = self::get_runtime_option_string_value( $options, 'footer_heading_color' );
        if ( '' !== $footer_heading ) {
            self::maybe_copy_runtime_option( $options, 'design_component_footer_heading', $footer_heading );
            unset( $options['footer_heading_color'] );
        }

        $footer_heading_size = absint( self::get_runtime_option_string_value( $options, 'footer_heading_font_size' ) );
        if ( $footer_heading_size >= 12 && $footer_heading_size <= 48 ) {
            self::maybe_copy_runtime_option( $options, 'design_component_footer_heading_size', $footer_heading_size . 'px' );
            unset( $options['footer_heading_font_size'] );
        }

        return $options;
    }

    /**
     * @param array<string,mixed> $options
     * @param string              $key
     * @return string
     */
    private static function get_runtime_option_string_value( $options, $key ) {
        if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) || is_array( $options[ $key ] ) ) {
            return '';
        }

        return trim( (string) $options[ $key ] );
    }

    /**
     * @param array<string,mixed> $options
     * @param string              $key
     * @param mixed               $default
     * @return bool
     */
    private static function runtime_option_has_custom_value( $options, $key, $default = null ) {
        if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) ) {
            return false;
        }

        $value = $options[ $key ];
        if ( is_array( $value ) ) {
            $filtered = array_values(
                array_filter(
                    $value,
                    static function ( $item ) {
                        return '' !== trim( (string) $item );
                    }
                )
            );

            if ( null === $default ) {
                return ! empty( $filtered );
            }

            return $filtered !== (array) $default;
        }

        $value = trim( (string) $value );
        if ( null === $default ) {
            return '' !== $value;
        }

        return $value !== trim( (string) $default );
    }

    /**
     * @param array<string,mixed> $options
     * @param string              $target_key
     * @param string              $value
     * @return void
     */
    private static function maybe_copy_runtime_option( &$options, $target_key, $value ) {
        if ( self::runtime_option_has_custom_value( $options, $target_key ) ) {
            return;
        }

        $style_key = isset( self::get_design_component_option_map()[ $target_key ] )
            ? self::get_design_component_option_map()[ $target_key ]
            : '';
        if ( '' === $style_key ) {
            return;
        }

        $sanitized = self::sanitize_component_style_value_by_key( $style_key, $value );
        if ( '' === $sanitized ) {
            return;
        }

        $options[ $target_key ] = $sanitized;
    }

    /**
     * @return array<string,mixed>
     */
    private static function get_theme_options() {
        if ( function_exists( 'get_option' ) ) {
            $options = get_option( 'developer_starter_options', array() );
            $options = is_array( $options ) ? $options : array();
            $normalized = self::normalize_runtime_theme_options( $options );

            if ( function_exists( 'update_option' ) && serialize( $normalized ) !== serialize( $options ) ) {
                update_option( 'developer_starter_options', $normalized );
            }

            return $normalized;
        }

        return array();
    }

    /**
     * @param array<string,mixed> $options
     * @return bool
     */
    private static function is_global_tokens_enabled( $options ) {
        if ( array_key_exists( 'design_enable_global_tokens', $options ) ) {
            return '1' === (string) $options['design_enable_global_tokens'];
        }

        $stored = self::get_stored_design_system_v2( $options );
        if ( array_key_exists( 'enabled', $stored ) ) {
            return ! empty( $stored['enabled'] );
        }

        return true;
    }

    /**
     * @param array<string,mixed> $options
     * @return bool
     */
    private static function has_design_options( $options ) {
        foreach ( $options as $key => $value ) {
            if ( self::STORAGE_OPTION_KEY === (string) $key ) {
                continue;
            }
            if ( 0 === strpos( (string) $key, 'design_' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,mixed> $options
     * @return string
     */
    private static function get_current_preset_key( $options ) {
        $presets = self::get_style_presets( $options );
        if ( array_key_exists( 'design_preset', $options ) ) {
            $preset_key = self::get_option_value( $options, 'design_preset', 'default' );
            return isset( $presets[ $preset_key ] ) ? $preset_key : 'default';
        }

        $stored = self::get_stored_design_system_v2( $options );
        $preset_key = isset( $stored['preset'] ) ? sanitize_key( (string) $stored['preset'] ) : 'default';

        return isset( $presets[ $preset_key ] ) ? $preset_key : 'default';
    }

    /**
     * @param string $token_key
     * @param mixed  $value
     * @return string
     */
    private static function sanitize_token_value_by_key( $token_key, $value ) {
        $schema = self::get_design_token_schema();
        $type   = isset( $schema[ $token_key ] ) ? $schema[ $token_key ] : '';

        return self::sanitize_design_value_by_type( $type, $value );
    }

    /**
     * @param string $style_key
     * @param mixed  $value
     * @return string
     */
    private static function sanitize_component_style_value_by_key( $style_key, $value ) {
        $schema = self::get_design_component_schema();
        $type   = isset( $schema[ $style_key ] ) ? $schema[ $style_key ] : '';

        return self::sanitize_design_value_by_type( $type, $value );
    }

    /**
     * @param string $type
     * @param mixed  $value
     * @return string
     */
    private static function sanitize_design_value_by_type( $type, $value ) {
        switch ( (string) $type ) {
            case 'color':
                return self::sanitize_css_color_value( $value );
            case 'paint':
                return self::sanitize_paint_value( $value );
            case 'font_stack':
                return self::sanitize_font_stack( $value );
            case 'length':
                return self::sanitize_length_value( $value );
            case 'line_height':
                return self::sanitize_line_height_value( $value );
            case 'breakpoint':
                return self::sanitize_breakpoint_value( $value );
            case 'duration':
                return self::sanitize_duration_value( $value );
            case 'shadow':
                return self::sanitize_shadow_value( $value );
            case 'box_spacing':
                return self::sanitize_box_spacing_value( $value );
            case 'box_spacing_single':
                return self::sanitize_box_spacing_value( $value, 1 );
            case 'heading_weight':
                $value = is_scalar( $value ) ? (string) $value : '';
                return in_array( $value, array( '500', '600', '700', '800', '900' ), true ) ? $value : '';
            case 'font_weight':
                return self::sanitize_font_weight_value( $value );
            case 'letter_spacing':
                return self::sanitize_letter_spacing_value( $value );
            case 'align':
                $value = is_scalar( $value ) ? (string) $value : '';
                return in_array( $value, array( 'left', 'center', 'right' ), true ) ? $value : '';
            case 'layout_mode':
                $value = is_scalar( $value ) ? sanitize_key( (string) $value ) : '';
                return in_array( $value, array( 'wide', 'boxed' ), true ) ? $value : '';
            default:
                return '';
        }
    }

    /**
     * @param array<string,mixed> $options
     * @param string              $default
     * @return string
     */
    private static function get_legacy_primary_color_value( $options, $default = '' ) {
        $legacy_primary = self::sanitize_css_color_value( self::get_option_value( $options, 'primary_color', '' ) );
        if ( '' !== $legacy_primary ) {
            return $legacy_primary;
        }

        if ( function_exists( 'get_theme_mod' ) ) {
            $customizer_primary = self::sanitize_css_color_value( get_theme_mod( self::LEGACY_CUSTOMIZER_PRIMARY_COLOR, '' ) );
            if ( '' !== $customizer_primary ) {
                return $customizer_primary;
            }
        }

        return (string) $default;
    }

    /**
     * @param mixed $value
     * @param bool  $default
     * @return bool
     */
    private static function normalize_bool_flag( $value, $default = false ) {
        if ( is_bool( $value ) ) {
            return $value;
        }
        if ( is_numeric( $value ) ) {
            return (bool) intval( $value );
        }
        if ( is_string( $value ) ) {
            $value = strtolower( trim( $value ) );
            if ( in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ) {
                return true;
            }
            if ( in_array( $value, array( '0', 'false', 'no', 'off', '' ), true ) ) {
                return false;
            }
        }

        return (bool) $default;
    }

    /**
     * @param array<string,mixed> $options
     * @param string              $key
     * @param string              $default
     * @return string
     */
    private static function get_option_value( $options, $key, $default = '' ) {
        if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
            return is_scalar( $options[ $key ] ) ? trim( (string) $options[ $key ] ) : $default;
        }

        return $default;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_css_color_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( self::is_safe_css_value( $value, 260 ) && preg_match( '/^(?:var\(|color-mix\(|rgba?\(|hsla?\(|#[0-9a-f]{3,8}\b|[a-z]+$)/i', $value ) ) {
            return $value;
        }

        if ( in_array( strtolower( $value ), array( 'transparent', 'currentcolor' ), true ) ) {
            return strtolower( $value );
        }

        if ( function_exists( 'sanitize_hex_color' ) ) {
            $hex = sanitize_hex_color( $value );
            if ( $hex ) {
                return $hex;
            }
        } elseif ( self::is_hex_color( $value ) ) {
            return strtolower( $value );
        }

        if ( preg_match( '/^var\(--[a-zA-Z0-9_-]+\)$/', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(rgba?|hsla?)\([0-9.,%\/\s-]+\)$/i', $value ) && false === strpos( $value, ';' ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_paint_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        $color = self::sanitize_css_color_value( $value );
        if ( '' !== $color ) {
            return $color;
        }

        if ( self::is_safe_css_value( $value, 420 ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_font_stack( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_length_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( self::is_safe_css_value( $value, 160 ) && preg_match( '/^(?:0|[0-9]+(?:\.[0-9]+)?(?:px|rem|em|%|vh|vw|svh|lvh|dvh|ch|ex)|var\(|calc\(|clamp\(|min\(|max\()/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_breakpoint_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( self::is_safe_css_value( $value, 160 ) && preg_match( '/^(?:0|[0-9]+(?:\.[0-9]+)?(?:px|rem|em)|var\(|calc\(|clamp\()/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @param int   $max_parts
     * @return string
     */
    private static function sanitize_box_spacing_value( $value, $max_parts = 4 ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( self::is_safe_css_value( $value, 220 ) && preg_match( '/^(?:var\(|calc\(|clamp\(|min\(|max\()/i', $value ) ) {
            return $value;
        }

        $parts = preg_split( '/\s+/', $value );
        $parts = is_array( $parts ) ? array_values( array_filter( $parts, 'strlen' ) ) : array();
        $max_parts = max( 1, min( 4, (int) $max_parts ) );

        if ( empty( $parts ) || count( $parts ) > $max_parts ) {
            return '';
        }

        $sanitized_parts = array();
        foreach ( $parts as $part ) {
            $part = trim( (string) $part );
            if ( '0' === $part ) {
                $sanitized_parts[] = '0';
                continue;
            }

            $length = self::sanitize_length_value( $part );
            if ( '' === $length ) {
                return '';
            }
            $sanitized_parts[] = $length;
        }

        return implode( ' ', $sanitized_parts );
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_line_height_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( self::is_safe_css_value( $value, 120 ) && ( preg_match( '/^([1-2](\.[0-9]{1,2})?|3)$/', $value ) || preg_match( '/^[0-9]+(\.[0-9]+)?(px|rem|em|%)$/', $value ) || preg_match( '/^(?:var\(|calc\(|clamp\()/i', $value ) ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_font_weight_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '/^[1-9]00$/', $value ) ) {
            return $value;
        }

        if ( in_array( strtolower( $value ), array( 'normal', 'bold' ), true ) ) {
            return strtolower( $value );
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_letter_spacing_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( '0' === $value ) {
            return '0';
        }

        if ( self::is_safe_css_value( $value, 120 ) && preg_match( '/^(?:-?(?:0|[0-9]+(?:\.[0-9]+)?)(?:px|rem|em)|var\(|calc\(|clamp\()/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_duration_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( self::is_safe_css_value( $value, 120 ) && preg_match( '/^(?:0|[0-9]+(?:\.[0-9]+)?(?:ms|s)|var\(|calc\()/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function sanitize_shadow_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }
        if ( 'none' === strtolower( $value ) ) {
            return 'none';
        }
        if ( self::is_safe_css_value( $value, 420 ) ) {
            return $value;
        }

        return '';
    }

    private static function is_safe_css_value( $value, $max_length = 320 ) {
        $value = trim( (string) $value );
        if ( '' === $value || strlen( $value ) > $max_length ) {
            return false;
        }

        return ! (bool) preg_match( '/[;{}<>]/', $value );
    }

    /**
     * @return string
     */
    private static function build_component_css() {
        $button_base_selector = ".btn,.button,a.button,input[type='submit'],input[type='button'],input[type='reset'],.post-read-more";
        $button_primary_selector = ".btn-primary,.btn,.button,a.button,input[type='submit'],input[type='button'],input[type='reset'],.post-read-more";
        $button_outline_selector = ".btn-outline,.btn-outline-light";
        $card_selector = ".card,.news-card,.product-card,.case-card,.pricing-card,.faq-item,.team-member,.about-culture-card,.about-gallery-item,.timeline-content,.account-section,.search-result-item,.author-post-card";
        $post_card_selector = ".post-card,.module-blog .blog-post-item,.news-card,.author-post-card,.search-result-item";
        $module_title_selector = ".section-title,.module-title,.block-title,.qfs-section-title";
        $module_header_selector = ".module .section-header,.module .module-header,.module .block-header";
        $heading_selector = "h1,h2,h3,h4,h5,h6,.page-title,.entry-title,.post-title,.news-title,.search-result-title,.error-code," . $module_title_selector;
        $form_selector = "input:not(.header-search-input):not([data-qiling-search-input]):not([type='checkbox']):not([type='radio']):not([type='submit']):not([type='button']):not([type='reset']):not([type='range']):not([type='file']),select,textarea,.contact-form input,.contact-form textarea,.contact-form select,.comment-form input[type='text'],.comment-form input[type='email'],.comment-form textarea";
        $header_surface_selector = ".site-header:not(.header-transparent),.site-header.header-transparent.header-scrolled";
        $header_text_selector = ".site-header:not(.header-transparent) .search-toggle,.site-header.header-transparent.header-scrolled .search-toggle";
        $header_toggle_selector = ".site-header:not(.header-transparent) .mobile-menu-toggle span,.site-header.header-transparent.header-scrolled .mobile-menu-toggle span";
        $desktop_nav_link_selector = ".site-header:not(.header-transparent) .primary-navigation>ul>li>a,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li>a";
        $desktop_nav_active_selector = ".site-header:not(.header-transparent) .primary-navigation>ul>li>a:hover,.site-header:not(.header-transparent) .primary-navigation>ul>li.current-menu-item>a,.site-header:not(.header-transparent) .primary-navigation>ul>li.current-menu-ancestor>a,.site-header:not(.header-transparent) .primary-navigation>ul>li.current-menu-parent>a,.site-header:not(.header-transparent) .primary-navigation>ul>li.current_page_item>a,.site-header:not(.header-transparent) .primary-navigation>ul>li.current_page_parent>a,.site-header:not(.header-transparent) .primary-navigation>ul>li.current_page_ancestor>a,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li>a:hover,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li.current-menu-item>a,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li.current-menu-ancestor>a,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li.current-menu-parent>a,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li.current_page_item>a,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li.current_page_parent>a,.site-header.header-transparent.header-scrolled .primary-navigation>ul>li.current_page_ancestor>a";
        $mobile_nav_shell_selector = ".mobile-menu,.mobile-bottom-nav";
        $mobile_nav_link_selector = ".mobile-menu-nav>ul>li>a,.mobile-bottom-menu a";
        $mobile_nav_active_selector = ".mobile-menu-nav>ul>li>a:hover,.mobile-menu-nav>ul>li.current-menu-item>a,.mobile-menu-nav>ul>li.current-menu-ancestor>a,.mobile-menu-nav>ul>li.current-menu-parent>a,.mobile-menu-nav>ul>li.current_page_item>a,.mobile-menu-nav>ul>li.current_page_parent>a,.mobile-menu-nav>ul>li.current_page_ancestor>a,.mobile-bottom-menu .current-menu-item a,.mobile-bottom-menu a:hover";
        $mobile_nav_panel_selector = ".mobile-menu-nav .sub-menu,.mobile-menu-nav li.qiling-menu-item-display-divider > a";
        $dropdown_selector = ".site-header .primary-navigation .sub-menu,.user-dropdown";
        $dropdown_link_selector = ".site-header .primary-navigation .sub-menu li a,.user-dropdown a";
        $dropdown_active_selector = ".site-header .primary-navigation .sub-menu li a:hover,.site-header .primary-navigation .sub-menu li.current-menu-item>a,.site-header .primary-navigation .sub-menu li.current-menu-ancestor>a,.site-header .primary-navigation .sub-menu li.current-menu-parent>a,.site-header .primary-navigation .sub-menu li.current_page_item>a,.site-header .primary-navigation .sub-menu li.current_page_parent>a,.site-header .primary-navigation .sub-menu li.current_page_ancestor>a,.user-dropdown a:hover";
        $badge_selector = ".header-user-badge,.woocommerce ul.products li.product .onsale,.woocommerce span.onsale,.pricing-badge,.module :is(.badge,.label,.pill,.tag,.status-badge,.category-badge,.post-category,.highlight-label)";
        $title_bar_selector = ".module :is(.section-kicker,.section-eyebrow,.module-kicker,.module-eyebrow,.block-title-bar,.section-title-bar,.qfs-section-title)";
        $list_header_selector = ".module :is(.list-header,.table-header,.ranking-header,.software-ranking-header,.ql-list-header,.ql-table-header),.comparison-table th";
        $border_accent_selector = ".pricing-card.pricing-featured,.module :is(.is-featured,.featured-card,.highlight-card)";
        $highlight_selector = ".comparison-table .th-product.is-highlight,.module :is(.highlight-card,.highlight-block,.highlight-banner),.module :is(.ct-card,.case-card,.service-item).is-highlight";
        $highlight_soft_selector = ".comparison-table .td-value.is-highlight,.module :is(.highlight-soft,.highlight-panel,.active-card)";
        $tabs_list_selector = ".woocommerce div.product .woocommerce-tabs ul.tabs";
        $tabs_link_selector = ".woocommerce div.product .woocommerce-tabs ul.tabs li a,.tab-btn,.about-tab-btn";
        $tabs_active_selector = ".woocommerce div.product .woocommerce-tabs ul.tabs li.active a,.woocommerce div.product .woocommerce-tabs ul.tabs li a:hover,.tab-btn:hover,.tab-btn.active,.about-tab-btn:hover,.about-tab-btn.active";
        $accordion_selector = ".faq-item,.accordion-item";
        $accordion_title_selector = ".faq-item h3,.faq-question,.accordion-header,.accordion-title";
        $pagination_selector = ".pagination-nav .page-numbers,.woocommerce nav.woocommerce-pagination ul li a,.woocommerce nav.woocommerce-pagination ul li span";
        $pagination_active_selector = ".pagination-nav .page-numbers.current,.woocommerce nav.woocommerce-pagination ul li a:hover,.woocommerce nav.woocommerce-pagination ul li span.current";
        $breadcrumb_selector = ".qiling-wc-breadcrumb,.breadcrumb,.breadcrumbs,.woocommerce-breadcrumb";
        $breadcrumb_link_selector = ".qiling-wc-breadcrumb a,.breadcrumb a,.breadcrumbs a,.woocommerce-breadcrumb a";
        $alert_selector = ".account-message,.woocommerce-error,.woocommerce-message,.woocommerce-info,.qiling-alert";
        $modal_selector = ".login-modal,.translate-modal";
        $modal_title_selector = ".login-modal-header h3,.translate-modal-header h3";
        $sidebar_selector = ".wc-sidebar,.account-nav,.sidebar-widget,.sidebar-card";
        $sidebar_title_selector = ".wc-sidebar .widget-title,.account-nav-title,.sidebar-widget .widget-title,.sidebar-card-title";
        $footer_selector = ".site-footer:not(.site-footer--builder-replace-all),.footer-widgets";
        $footer_link_selector = ".footer-widget-area a,.footer-friend-link,.footer-copyright a,.footer-filing a,.footer-navigation a,.social-navigation a,.footer-contact-link,.footer-widget-text a";
        $footer_bottom_selector = ".footer-bottom,.footer-friend-links";
        $woo_card_selector = ".woocommerce ul.products li.product,.woocommerce div.product,.woocommerce-cart .woocommerce,.woocommerce-checkout .woocommerce,.woocommerce-account .woocommerce";
        $woo_card_title_selector = ".woocommerce ul.products li.product .woocommerce-loop-product__title,.woocommerce div.product .product_title,.woocommerce div.product .woocommerce-tabs .panel h2";
        $woo_card_price_selector = ".woocommerce ul.products li.product .price,.woocommerce ul.products li.product .price ins,.woocommerce div.product p.price,.woocommerce div.product span.price";
        $card_match = ":is(" . $card_selector . ")";
        $post_card_match = ":is(" . $post_card_selector . ")";
        $module_title_match = ":is(" . $module_title_selector . ")";
        $form_match = ":is(" . $form_selector . ")";
        $button_primary_match = ":is(" . $button_primary_selector . ")";
        $button_outline_match = ":is(" . $button_outline_selector . ")";
        $dark_card_selector = "html.dark-mode {$card_match},[data-theme='dark'] {$card_match}";
        $dark_post_card_selector = "html.dark-mode {$post_card_match},[data-theme='dark'] {$post_card_match}";
        $dark_module_title_selector = "html.dark-mode {$module_title_match},[data-theme='dark'] {$module_title_match}";
        $dark_form_selector = "html.dark-mode {$form_match},[data-theme='dark'] {$form_match}";

        $css  = $button_base_selector . "{padding:var(--qiling-component-button-padding);border-radius:var(--qiling-button-radius);box-shadow:var(--qiling-component-button-shadow);transition:background-color var(--qiling-animation-speed),color var(--qiling-animation-speed),border-color var(--qiling-animation-speed),box-shadow var(--qiling-animation-speed),transform var(--qiling-animation-speed);}\n";
        $css .= $button_primary_selector . "{background:var(--qiling-component-button-bg);color:var(--qiling-component-button-text);border:1px solid var(--qiling-component-button-border);}\n";
        $css .= $button_primary_match . ":hover{background:var(--qiling-component-button-hover-bg);color:var(--qiling-component-button-hover-text);border-color:var(--qiling-component-button-hover-bg);}\n";
        $css .= $button_outline_selector . "{background:transparent;color:var(--qiling-component-button-border);border:1px solid var(--qiling-component-button-border);box-shadow:none;}\n";
        $css .= $button_outline_match . ":hover{background:var(--qiling-component-button-bg);color:var(--qiling-component-button-text);border-color:var(--qiling-component-button-border);}\n";

        $css .= $heading_selector . "{font-weight:var(--qiling-component-heading-weight);letter-spacing:var(--qiling-component-heading-letter-spacing);}\n";
        $css .= $module_header_selector . "{text-align:var(--qiling-component-module-title-align);}\n";
        $css .= $module_title_selector . "{color:var(--qiling-component-module-title-color);font-size:var(--qiling-component-module-title-size);text-align:var(--qiling-component-module-title-align);}\n";

        $css .= $card_selector . "{background:var(--qiling-component-card-bg);border:1px solid var(--qiling-component-card-border);border-radius:var(--qiling-card-radius);box-shadow:var(--qiling-component-card-shadow);}\n";
        $css .= $post_card_selector . "{background:var(--qiling-component-post-card-bg);border:1px solid var(--qiling-component-post-card-border);border-radius:var(--qiling-card-radius);box-shadow:var(--qiling-component-post-card-shadow);}\n";
        $css .= ".post-card .post-title a,.news-title a,.search-result-title a,.author-post-card .post-title a,.module-blog .blog-post-item .post-title a,.module-blog .blog-post-item .post-title,.news-card .news-title a{color:var(--qiling-component-post-card-title-color);}\n";
        $css .= ".post-card .post-meta,.post-card .post-meta a,.search-result-meta,.search-result-meta a,.qiling-blog-card-meta,.qiling-blog-card-meta a,.author-post-card .post-meta,.author-post-card .post-meta a,.news-date,.news-author{color:var(--qiling-component-post-card-meta-color);}\n";

        $css .= $form_selector . "{background:var(--qiling-component-form-input-bg);color:var(--qiling-component-form-input-text);border:1px solid var(--qiling-component-form-input-border);border-radius:var(--qiling-input-radius);}\n";
        $css .= $form_match . ":focus{border-color:var(--qiling-component-form-focus-border);box-shadow:0 0 0 3px rgba(var(--color-primary-rgb),0.12);outline:none;}\n";
        $css .= $form_match . "::placeholder{color:var(--color-text-muted);}\n";
        $css .= $dark_card_selector . "{background:var(--qiling-component-card-bg-dark);border-color:var(--qiling-component-card-border-dark);}\n";
        $css .= $dark_post_card_selector . "{background:var(--qiling-component-post-card-bg-dark);border-color:var(--qiling-component-post-card-border-dark);}\n";
        $css .= "html.dark-mode :is(.post-card .post-title a,.news-title a,.search-result-title a,.author-post-card .post-title a,.module-blog .blog-post-item .post-title a),[data-theme='dark'] :is(.post-card .post-title a,.news-title a,.search-result-title a,.author-post-card .post-title a,.module-blog .blog-post-item .post-title a){color:var(--qiling-component-post-card-title-color-dark);}\n";
        $css .= "html.dark-mode :is(.post-card .post-meta,.post-card .post-meta a,.search-result-meta,.search-result-meta a,.qiling-blog-card-meta,.qiling-blog-card-meta a),[data-theme='dark'] :is(.post-card .post-meta,.post-card .post-meta a,.search-result-meta,.search-result-meta a,.qiling-blog-card-meta,.qiling-blog-card-meta a){color:var(--qiling-component-post-card-meta-color-dark);}\n";
        $css .= $dark_module_title_selector . "{color:var(--qiling-component-module-title-color-dark);}\n";
        $css .= $dark_form_selector . "{background:var(--qiling-component-form-input-bg-dark);color:var(--qiling-component-form-input-text-dark);border-color:var(--qiling-component-form-input-border-dark);}\n";
        $css .= "html.dark-mode {$form_match}::placeholder,[data-theme='dark'] {$form_match}::placeholder{color:var(--qiling-dark-text-muted);}\n";
        $css .= $header_surface_selector . "{background:var(--qiling-component-header-bg);border-bottom:1px solid var(--qiling-component-header-border);box-shadow:var(--qiling-component-header-shadow);}\n";
        $css .= $header_text_selector . "{color:var(--qiling-component-header-text);}\n";
        $css .= $header_toggle_selector . "{background:var(--qiling-component-header-text);}\n";
        $css .= $desktop_nav_link_selector . "{color:var(--qiling-component-nav-link);}\n";
        $css .= $desktop_nav_active_selector . "{background:var(--qiling-component-nav-hover-bg);color:var(--qiling-component-nav-hover-text);}\n";
        $css .= "html.dark-mode .site-header:not(.header-transparent),html.dark-mode .site-header.header-transparent.header-scrolled,[data-theme='dark'] .site-header:not(.header-transparent),[data-theme='dark'] .site-header.header-transparent.header-scrolled{background:var(--qiling-dark-surface);border-bottom-color:var(--qiling-dark-border);}\n";
        $css .= "html.dark-mode .site-header .primary-navigation>ul>li>a,[data-theme='dark'] .site-header .primary-navigation>ul>li>a{color:var(--qiling-dark-text) !important;}\n";
        $css .= "html.dark-mode .site-header .header-actions,html.dark-mode .site-header .search-toggle,html.dark-mode .site-header .translate-toggle,html.dark-mode .site-header .darkmode-toggle,[data-theme='dark'] .site-header .header-actions,[data-theme='dark'] .site-header .search-toggle,[data-theme='dark'] .site-header .translate-toggle,[data-theme='dark'] .site-header .darkmode-toggle{color:var(--qiling-dark-text) !important;}\n";
        $css .= $mobile_nav_shell_selector . "{background:var(--qiling-component-mobile-nav-bg);border-color:var(--qiling-component-mobile-nav-border);}\n";
        $css .= $mobile_nav_link_selector . "{color:var(--qiling-component-mobile-nav-link);}\n";
        $css .= $mobile_nav_active_selector . "{background:var(--qiling-component-mobile-nav-hover-bg);color:var(--qiling-component-mobile-nav-hover-text);}\n";
        $css .= $mobile_nav_panel_selector . "{background:var(--qiling-component-mobile-nav-hover-bg);border-color:var(--qiling-component-mobile-nav-border);}\n";
        $css .= $dropdown_selector . "{background:var(--qiling-component-dropdown-bg);border:1px solid var(--qiling-component-dropdown-border);box-shadow:var(--qiling-component-dropdown-shadow);}\n";
        $css .= $dropdown_link_selector . "{color:var(--qiling-component-dropdown-link);}\n";
        $css .= $dropdown_active_selector . "{background:var(--qiling-component-dropdown-hover-bg);color:var(--qiling-component-dropdown-hover-text);}\n";
        $css .= $badge_selector . "{background:var(--qiling-component-badge-bg);color:var(--qiling-component-badge-text);border-color:var(--qiling-component-badge-border);}\n";
        $css .= $title_bar_selector . "{background:var(--qiling-component-title-bar-bg);color:var(--qiling-component-title-bar-text);border-color:var(--qiling-component-title-bar-border);}\n";
        $css .= $list_header_selector . "{background:var(--qiling-component-list-header-bg);color:var(--qiling-component-list-header-text);border-color:var(--qiling-component-list-header-border);}\n";
        $css .= $border_accent_selector . "{border-color:var(--qiling-component-border-accent);}\n";
        $css .= $highlight_selector . "{background:var(--qiling-component-highlight-bg);color:var(--qiling-component-highlight-text);border-color:var(--qiling-component-highlight-border);}\n";
        $css .= $highlight_soft_selector . "{background:var(--qiling-component-highlight-soft-bg);border-color:var(--qiling-component-highlight-border);}\n";
        $css .= $tabs_list_selector . "{border-bottom-color:var(--qiling-component-tabs-border);}\n";
        $css .= $tabs_link_selector . "{color:var(--qiling-component-tabs-text);}\n";
        $css .= $tabs_active_selector . "{background:var(--qiling-component-tabs-active-bg);color:var(--qiling-component-tabs-active-text);border-color:var(--qiling-component-tabs-active-border);}\n";
        $css .= $accordion_selector . "{background:var(--qiling-component-accordion-bg);border:1px solid var(--qiling-component-accordion-border);}\n";
        $css .= $accordion_title_selector . "{color:var(--qiling-component-accordion-title);}\n";
        $css .= $pagination_selector . "{background:var(--qiling-component-pagination-bg);border:1px solid var(--qiling-component-pagination-border);color:var(--qiling-component-pagination-text);}\n";
        $css .= $pagination_active_selector . "{background:var(--qiling-component-pagination-active-bg);border-color:var(--qiling-component-pagination-border);color:var(--qiling-component-pagination-active-text);}\n";
        $css .= $breadcrumb_selector . "{background:var(--qiling-component-breadcrumb-bg);color:var(--qiling-component-breadcrumb-text);}\n";
        $css .= $breadcrumb_link_selector . "{color:var(--qiling-component-breadcrumb-link);}\n";
        $css .= $alert_selector . "{background:var(--qiling-component-alert-bg);color:var(--qiling-component-alert-text);border-color:var(--qiling-component-alert-border);}\n";
        $css .= $modal_selector . "{background:var(--qiling-component-modal-bg);border:1px solid var(--qiling-component-modal-border);box-shadow:var(--qiling-component-modal-shadow);}\n";
        $css .= $modal_title_selector . "{color:var(--qiling-component-modal-title);}\n";
        $css .= $sidebar_selector . "{background:var(--qiling-component-sidebar-bg);border:1px solid var(--qiling-component-sidebar-border);box-shadow:var(--qiling-component-sidebar-shadow);}\n";
        $css .= $sidebar_title_selector . "{color:var(--qiling-component-sidebar-title);}\n";
        $css .= $footer_selector . "{background:var(--qiling-component-footer-bg);color:var(--qiling-component-footer-text);--qiling-footer-widgets-bg:var(--qiling-component-footer-bg);--qiling-footer-text-color:var(--qiling-component-footer-text);--qiling-footer-heading-color:var(--qiling-component-footer-heading, var(--qiling-component-footer-text));--qiling-footer-heading-size:var(--qiling-component-footer-heading-size, 18px);}\n";
        $css .= $footer_bottom_selector . "{background:var(--qiling-component-footer-bottom-bg);}\n";
        $css .= $footer_link_selector . "{color:var(--qiling-component-footer-link);}\n";
        $css .= ".footer-widget-area a:hover,.footer-friend-link:hover,.footer-copyright a:hover,.footer-filing a:hover,.footer-navigation a:hover,.social-navigation a:hover,.footer-contact-link:hover,.footer-widget-text a:hover{color:var(--qiling-component-footer-link-hover);}\n";
        $css .= $woo_card_selector . "{background:var(--qiling-component-woo-card-bg);border-color:var(--qiling-component-woo-card-border);box-shadow:var(--qiling-component-woo-card-shadow);}\n";
        $css .= $woo_card_title_selector . "{color:var(--qiling-component-woo-card-title);}\n";
        $css .= $woo_card_price_selector . "{color:var(--qiling-component-woo-card-price);}\n";

        return $css;
    }

    /**
     * @param string $color
     * @return bool
     */
    private static function is_hex_color( $color ) {
        return (bool) preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', (string) $color );
    }

    /**
     * @param string $hex
     * @param int    $percent
     * @return string
     */
    private static function shift_hex_color( $hex, $percent ) {
        $hex = ltrim( (string) $hex, '#' );
        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $amount = max( -100, min( 100, (int) $percent ) );
        $channels = str_split( $hex, 2 );
        $result = '#';
        foreach ( $channels as $channel ) {
            $value = hexdec( $channel );
            if ( $amount >= 0 ) {
                $value = (int) round( $value + ( 255 - $value ) * ( $amount / 100 ) );
            } else {
                $value = (int) round( $value * ( 1 + $amount / 100 ) );
            }
            $result .= str_pad( dechex( max( 0, min( 255, $value ) ) ), 2, '0', STR_PAD_LEFT );
        }

        return $result;
    }

    /**
     * @param string $hex
     * @return string
     */
    private static function hex_to_rgb_string( $hex ) {
        $hex = ltrim( (string) $hex, '#' );
        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return implode( ', ', array(
            (string) hexdec( substr( $hex, 0, 2 ) ),
            (string) hexdec( substr( $hex, 2, 2 ) ),
            (string) hexdec( substr( $hex, 4, 2 ) ),
        ) );
    }
}
