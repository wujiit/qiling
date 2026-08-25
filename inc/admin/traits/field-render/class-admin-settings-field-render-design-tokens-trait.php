<?php
/**
 * Admin settings design token preview field render trait.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_Design_Tokens_Trait {

    private function render_design_tokens_preview_field( $options ) {
        echo '<tr id="setting-row-design_tokens_preview" data-setting-id="design_tokens_preview"><th scope="row">' . esc_html__( '当前样式预览', 'developer-starter' ) . '</th><td>';

        if ( ! class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            echo '<p class="description">' . esc_html__( '全局样式服务未加载。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $options               = is_array( $options ) ? $options : array();
        $payload               = \Developer_Starter\Core\Design_Tokens::get_client_payload( $options );
        $tokens                = isset( $payload['tokens'] ) && is_array( $payload['tokens'] ) ? $payload['tokens'] : array();
        $typography_system     = isset( $payload['typographySystem'] ) && is_array( $payload['typographySystem'] ) ? $payload['typographySystem'] : array();
        $layout_system         = isset( $payload['layoutSystem'] ) && is_array( $payload['layoutSystem'] ) ? $payload['layoutSystem'] : array();
        $component_styles      = isset( $payload['componentStyles'] ) && is_array( $payload['componentStyles'] ) ? $payload['componentStyles'] : array();
        $page_design_defs      = isset( $payload['pageDesignDefinitions'] ) && is_array( $payload['pageDesignDefinitions'] ) ? $payload['pageDesignDefinitions'] : array();
        $preset_key            = isset( $payload['preset'] ) ? (string) $payload['preset'] : 'default';
        $preset_label          = isset( $payload['presetLabel'] ) ? (string) $payload['presetLabel'] : $preset_key;
        $preset_source         = isset( $payload['presetSource'] ) ? (string) $payload['presetSource'] : 'system';
        $preset_tokens         = \Developer_Starter\Core\Design_Tokens::get_preset_token_values( $preset_key, $options );
        $storage_payload       = \Developer_Starter\Core\Design_Tokens::get_storage_payload( $options );
        $preset_custom_presets = isset( $storage_payload['custom_presets'] ) && is_array( $storage_payload['custom_presets'] )
            ? $storage_payload['custom_presets']
            : array();
        $preset_baseline_options = array(
            'design_preset'         => $preset_key,
            'design_custom_presets' => $preset_custom_presets,
            \Developer_Starter\Core\Design_Tokens::STORAGE_OPTION_KEY => array(
                'preset'         => $preset_key,
                'custom_presets' => $preset_custom_presets,
            ),
        );
        $default_typography    = \Developer_Starter\Core\Design_Tokens::get_default_typography_system_values();
        $default_layout        = \Developer_Starter\Core\Design_Tokens::get_default_layout_system_values();
        $preset_typography     = \Developer_Starter\Core\Design_Tokens::get_current_typography_system( $preset_baseline_options );
        $preset_layout         = \Developer_Starter\Core\Design_Tokens::get_current_layout_system( $preset_baseline_options );
        $preset_components     = \Developer_Starter\Core\Design_Tokens::get_current_component_styles( $preset_baseline_options, $tokens );
        $diagnostics           = class_exists( '\Developer_Starter\Core\Page_Performance_A11y_Auditor' )
            ? \Developer_Starter\Core\Page_Performance_A11y_Auditor::build_design_system_diagnostics( $payload )
            : array(
                'summary'             => array( 'contrastWarnings' => 0, 'hardcodedCount' => 0, 'darkModeWarnings' => 0 ),
                'contrast'            => array(),
                'hardcodedComponents' => array(),
                'darkMode'            => array(),
            );
        $css_variables         = array_merge(
            isset( $payload['cssVariables'] ) && is_array( $payload['cssVariables'] ) ? $payload['cssVariables'] : array(),
            isset( $payload['componentCssVariables'] ) && is_array( $payload['componentCssVariables'] ) ? $payload['componentCssVariables'] : array()
        );

        $normalize_value = static function ( $value ) {
            return trim( (string) $value );
        };
        $count_nested_difference = static function ( $current, $base ) use ( &$count_nested_difference, $normalize_value ) {
            if ( is_array( $current ) || is_array( $base ) ) {
                $current = is_array( $current ) ? $current : array();
                $base    = is_array( $base ) ? $base : array();
                $count   = 0;
                foreach ( array_unique( array_merge( array_keys( $current ), array_keys( $base ) ) ) as $key ) {
                    $count += $count_nested_difference( $current[ $key ] ?? '', $base[ $key ] ?? '' );
                }
                return $count;
            }

            return $normalize_value( $current ) === $normalize_value( $base ) ? 0 : 1;
        };
        $build_source_badge = static function ( $current, $base, $inherit_label, $override_label ) use ( $normalize_value ) {
            $is_override = $normalize_value( $current ) !== $normalize_value( $base );
            return array(
                'label' => $is_override ? $override_label : $inherit_label,
                'tone'  => $is_override ? 'override' : 'inherit',
            );
        };
        $build_count_badge = static function ( $count, $inherit_label, $override_label ) {
            $count = absint( $count );
            return array(
                'label' => $count > 0 ? $override_label : $inherit_label,
                'tone'  => $count > 0 ? 'override' : 'inherit',
            );
        };
        $resolve_style_value = static function ( $value, $depth = 0 ) use ( &$resolve_style_value, $css_variables ) {
            $value = trim( (string) $value );
            if ( '' === $value || $depth > 5 ) {
                return '';
            }

            if ( false === strpos( $value, 'var(' ) ) {
                return $value;
            }

            $resolved = preg_replace_callback(
                '/var\((--[a-zA-Z0-9_-]+)\)/',
                static function ( $matches ) use ( &$resolve_style_value, $css_variables, $depth ) {
                    $variable_key = isset( $matches[1] ) ? (string) $matches[1] : '';
                    if ( '' === $variable_key || ! isset( $css_variables[ $variable_key ] ) ) {
                        return $matches[0];
                    }

                    return $resolve_style_value( $css_variables[ $variable_key ], $depth + 1 );
                },
                $value
            );

            return is_string( $resolved ) ? trim( $resolved ) : '';
        };
        $build_style_attr = static function ( $styles ) {
            $parts = array();
            foreach ( $styles as $property => $value ) {
                $value = trim( (string) $value );
                if ( '' === $value ) {
                    continue;
                }
                $parts[] = $property . ':' . $value;
            }
            return implode( ';', $parts );
        };
        $format_typography_meta = static function ( $properties ) {
            return implode( ' / ', array_filter( array(
                isset( $properties['font_size'] ) ? (string) $properties['font_size'] : '',
                isset( $properties['line_height'] ) && '' !== (string) $properties['line_height'] ? 'LH ' . (string) $properties['line_height'] : '',
                isset( $properties['font_weight'] ) && '' !== (string) $properties['font_weight'] ? 'W ' . (string) $properties['font_weight'] : '',
                isset( $properties['letter_spacing'] ) && '' !== (string) $properties['letter_spacing'] ? 'LS ' . (string) $properties['letter_spacing'] : '',
            ) ) );
        };
        $format_source_tone = static function ( $tone ) {
            return 'override' === $tone ? 'is-override' : 'is-inherit';
        };

        $palette_diff_count = 0;
        foreach ( $preset_tokens as $token_key => $base_value ) {
            $current_value = isset( $tokens[ $token_key ] ) ? (string) $tokens[ $token_key ] : '';
            if ( $normalize_value( $current_value ) !== $normalize_value( $base_value ) ) {
                $palette_diff_count++;
            }
        }

        $typography_diff_count = $count_nested_difference( $typography_system, $preset_typography );
        $layout_diff_count     = $count_nested_difference( $layout_system, $preset_layout );
        $component_diff_count  = $count_nested_difference( $component_styles, $preset_components );

        $swatches = array(
            'primary'       => __( '品牌主色', 'developer-starter' ),
            'primary_hover' => __( '主色悬停', 'developer-starter' ),
            'secondary'     => __( '辅助色', 'developer-starter' ),
            'accent'        => __( '点缀色', 'developer-starter' ),
            'success'       => __( '成功色', 'developer-starter' ),
            'info'          => __( '信息色', 'developer-starter' ),
            'warning'       => __( '警告色', 'developer-starter' ),
            'error'         => __( '错误色', 'developer-starter' ),
            'text'          => __( '正文', 'developer-starter' ),
            'heading'       => __( '标题', 'developer-starter' ),
            'background'    => __( '页面背景', 'developer-starter' ),
            'surface'       => __( '卡片背景', 'developer-starter' ),
            'surface_alt'   => __( '浅色区块', 'developer-starter' ),
            'border'        => __( '边框', 'developer-starter' ),
            'dark_bg'       => __( '暗色背景', 'developer-starter' ),
            'dark_surface'  => __( '暗色表面', 'developer-starter' ),
            'dark_text'     => __( '暗色正文', 'developer-starter' ),
            'dark_border'   => __( '暗色边框', 'developer-starter' ),
        );
        $swatch_items = array();
        foreach ( $swatches as $key => $label ) {
            $current_value = isset( $tokens[ $key ] ) ? (string) $tokens[ $key ] : '';
            $base_value    = isset( $preset_tokens[ $key ] ) ? (string) $preset_tokens[ $key ] : '';
            $swatch_items[] = array(
                'label'   => $label,
                'value'   => $current_value,
                'preview' => $resolve_style_value( $current_value ),
                'source'  => $build_source_badge( $current_value, $base_value, __( '跟随预设', 'developer-starter' ), __( '本站单独设置', 'developer-starter' ) ),
            );
        }

        $typography_items = array();
        foreach ( array(
            'body'   => __( 'Body 正文', 'developer-starter' ),
            'lead'   => __( 'Lead 导语', 'developer-starter' ),
            'menu'   => __( 'Menu 导航', 'developer-starter' ),
            'button' => __( 'Button 按钮', 'developer-starter' ),
            'h1'     => 'H1',
            'h2'     => 'H2',
        ) as $style_key => $label ) {
            $current_style = isset( $typography_system[ $style_key ] ) && is_array( $typography_system[ $style_key ] ) ? $typography_system[ $style_key ] : array();
            $base_style    = isset( $preset_typography[ $style_key ] ) && is_array( $preset_typography[ $style_key ] ) ? $preset_typography[ $style_key ] : array();
            $typography_items[] = array(
                'label'   => $label,
                'desktop' => isset( $current_style['desktop'] ) && is_array( $current_style['desktop'] ) ? $current_style['desktop'] : array(),
                'mobile'  => isset( $current_style['mobile'] ) && is_array( $current_style['mobile'] ) ? $current_style['mobile'] : array(),
                'source'  => $build_count_badge( $count_nested_difference( $current_style, $base_style ), __( '主题默认', 'developer-starter' ), __( '本站单独设置', 'developer-starter' ) ),
            );
        }

        $layout_items = array(
            array(
                'label'  => __( '容器宽度', 'developer-starter' ),
                'value'  => implode( ' / ', array_filter( array(
                    isset( $layout_system['container_width']['desktop'] ) ? (string) $layout_system['container_width']['desktop'] : '',
                    isset( $layout_system['container_width']['tablet'] ) ? (string) $layout_system['container_width']['tablet'] : '',
                    isset( $layout_system['container_width']['mobile'] ) ? (string) $layout_system['container_width']['mobile'] : '',
                ) ) ),
                'source' => $build_count_badge(
                    $count_nested_difference( $layout_system['container_width'] ?? array(), $preset_layout['container_width'] ?? array() ),
                    __( '主题默认', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'  => __( '区块间距', 'developer-starter' ),
                'value'  => implode( ' / ', array_filter( array(
                    isset( $layout_system['section_spacing']['desktop'] ) ? (string) $layout_system['section_spacing']['desktop'] : '',
                    isset( $layout_system['section_spacing']['tablet'] ) ? (string) $layout_system['section_spacing']['tablet'] : '',
                    isset( $layout_system['section_spacing']['mobile'] ) ? (string) $layout_system['section_spacing']['mobile'] : '',
                ) ) ),
                'source' => $build_count_badge(
                    $count_nested_difference( $layout_system['section_spacing'] ?? array(), $preset_layout['section_spacing'] ?? array() ),
                    __( '主题默认', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'  => __( '栅格间距', 'developer-starter' ),
                'value'  => implode( ' / ', array_filter( array(
                    isset( $layout_system['grid_gap']['desktop'] ) ? (string) $layout_system['grid_gap']['desktop'] : '',
                    isset( $layout_system['grid_gap']['tablet'] ) ? (string) $layout_system['grid_gap']['tablet'] : '',
                    isset( $layout_system['grid_gap']['mobile'] ) ? (string) $layout_system['grid_gap']['mobile'] : '',
                ) ) ),
                'source' => $build_count_badge(
                    $count_nested_difference( $layout_system['grid_gap'] ?? array(), $preset_layout['grid_gap'] ?? array() ),
                    __( '主题默认', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'  => __( '断点', 'developer-starter' ),
                'value'  => implode( ' / ', array_filter( array(
                    isset( $layout_system['breakpoints']['tablet'] ) ? 'Tablet ' . (string) $layout_system['breakpoints']['tablet'] : '',
                    isset( $layout_system['breakpoints']['mobile'] ) ? 'Mobile ' . (string) $layout_system['breakpoints']['mobile'] : '',
                ) ) ),
                'source' => $build_count_badge(
                    $count_nested_difference( $layout_system['breakpoints'] ?? array(), $preset_layout['breakpoints'] ?? array() ),
                    __( '主题默认', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'  => __( '布局模式', 'developer-starter' ),
                'value'  => isset( $layout_system['layout_mode'] ) ? (string) $layout_system['layout_mode'] : '',
                'source' => $build_source_badge(
                    isset( $layout_system['layout_mode'] ) ? (string) $layout_system['layout_mode'] : '',
                    isset( $preset_layout['layout_mode'] ) ? (string) $preset_layout['layout_mode'] : '',
                    __( '主题默认', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
        );

        $component_cards = array(
            array(
                'label'   => __( '页头 / 文字 Logo / 桌面导航 / 电话按钮', 'developer-starter' ),
                'bg'      => $resolve_style_value( $component_styles['header_bg'] ?? '' ),
                'text'    => $resolve_style_value( $component_styles['nav_link'] ?? '' ),
                'meta'    => isset( $component_styles['header_logo_scrolled_fill'] ) ? (string) $component_styles['header_logo_scrolled_fill'] : '',
                'source'  => $build_count_badge(
                    $count_nested_difference(
                        array(
                            'header_bg'                    => $component_styles['header_bg'] ?? '',
                            'nav_link'                     => $component_styles['nav_link'] ?? '',
                            'nav_hover_bg'                 => $component_styles['nav_hover_bg'] ?? '',
                            'header_logo_transparent_fill' => $component_styles['header_logo_transparent_fill'] ?? '',
                            'header_logo_scrolled_fill'    => $component_styles['header_logo_scrolled_fill'] ?? '',
                            'header_phone_bg'              => $component_styles['header_phone_bg'] ?? '',
                            'header_phone_text'            => $component_styles['header_phone_text'] ?? '',
                        ),
                        array(
                            'header_bg'                    => $preset_components['header_bg'] ?? '',
                            'nav_link'                     => $preset_components['nav_link'] ?? '',
                            'nav_hover_bg'                 => $preset_components['nav_hover_bg'] ?? '',
                            'header_logo_transparent_fill' => $preset_components['header_logo_transparent_fill'] ?? '',
                            'header_logo_scrolled_fill'    => $preset_components['header_logo_scrolled_fill'] ?? '',
                            'header_phone_bg'              => $preset_components['header_phone_bg'] ?? '',
                            'header_phone_text'            => $preset_components['header_phone_text'] ?? '',
                        )
                    ),
                    __( '跟随全局', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'   => __( '按钮', 'developer-starter' ),
                'bg'      => $resolve_style_value( $component_styles['button_bg'] ?? '' ),
                'text'    => $resolve_style_value( $component_styles['button_text'] ?? '' ),
                'meta'    => isset( $component_styles['button_padding'] ) ? (string) $component_styles['button_padding'] : '',
                'source'  => $build_count_badge(
                    $count_nested_difference(
                        array(
                            'button_bg'      => $component_styles['button_bg'] ?? '',
                            'button_text'    => $component_styles['button_text'] ?? '',
                            'button_padding' => $component_styles['button_padding'] ?? '',
                        ),
                        array(
                            'button_bg'      => $preset_components['button_bg'] ?? '',
                            'button_text'    => $preset_components['button_text'] ?? '',
                            'button_padding' => $preset_components['button_padding'] ?? '',
                        )
                    ),
                    __( '跟随全局', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'   => __( '卡片 / 文章卡片', 'developer-starter' ),
                'bg'      => $resolve_style_value( $component_styles['post_card_bg'] ?? '' ),
                'text'    => $resolve_style_value( $component_styles['post_card_title_color'] ?? '' ),
                'meta'    => isset( $component_styles['post_card_meta_color'] ) ? (string) $component_styles['post_card_meta_color'] : '',
                'source'  => $build_count_badge(
                    $count_nested_difference(
                        array(
                            'post_card_bg'          => $component_styles['post_card_bg'] ?? '',
                            'post_card_title_color' => $component_styles['post_card_title_color'] ?? '',
                            'post_card_meta_color'  => $component_styles['post_card_meta_color'] ?? '',
                        ),
                        array(
                            'post_card_bg'          => $preset_components['post_card_bg'] ?? '',
                            'post_card_title_color' => $preset_components['post_card_title_color'] ?? '',
                            'post_card_meta_color'  => $preset_components['post_card_meta_color'] ?? '',
                        )
                    ),
                    __( '跟随全局', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'   => __( '表单', 'developer-starter' ),
                'bg'      => $resolve_style_value( $component_styles['form_input_bg'] ?? '' ),
                'text'    => $resolve_style_value( $component_styles['form_input_text'] ?? '' ),
                'meta'    => isset( $component_styles['form_input_border'] ) ? (string) $component_styles['form_input_border'] : '',
                'source'  => $build_count_badge(
                    $count_nested_difference(
                        array(
                            'form_input_bg'     => $component_styles['form_input_bg'] ?? '',
                            'form_input_text'   => $component_styles['form_input_text'] ?? '',
                            'form_input_border' => $component_styles['form_input_border'] ?? '',
                        ),
                        array(
                            'form_input_bg'     => $preset_components['form_input_bg'] ?? '',
                            'form_input_text'   => $preset_components['form_input_text'] ?? '',
                            'form_input_border' => $preset_components['form_input_border'] ?? '',
                        )
                    ),
                    __( '跟随全局', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'   => __( '移动导航', 'developer-starter' ),
                'bg'      => $resolve_style_value( $component_styles['mobile_nav_bg'] ?? '' ),
                'text'    => $resolve_style_value( $component_styles['mobile_nav_link'] ?? '' ),
                'meta'    => isset( $component_styles['mobile_nav_hover_bg'] ) ? (string) $component_styles['mobile_nav_hover_bg'] : '',
                'source'  => $build_count_badge(
                    $count_nested_difference(
                        array(
                            'mobile_nav_bg'       => $component_styles['mobile_nav_bg'] ?? '',
                            'mobile_nav_link'     => $component_styles['mobile_nav_link'] ?? '',
                            'mobile_nav_hover_bg' => $component_styles['mobile_nav_hover_bg'] ?? '',
                        ),
                        array(
                            'mobile_nav_bg'       => $preset_components['mobile_nav_bg'] ?? '',
                            'mobile_nav_link'     => $preset_components['mobile_nav_link'] ?? '',
                            'mobile_nav_hover_bg' => $preset_components['mobile_nav_hover_bg'] ?? '',
                        )
                    ),
                    __( '跟随全局', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
            array(
                'label'   => __( '页脚 / Woo 卡片', 'developer-starter' ),
                'bg'      => $resolve_style_value( $component_styles['footer_bg'] ?? '' ),
                'text'    => $resolve_style_value( $component_styles['footer_text'] ?? '' ),
                'meta'    => isset( $component_styles['woo_card_price'] ) ? (string) $component_styles['woo_card_price'] : '',
                'source'  => $build_count_badge(
                    $count_nested_difference(
                        array(
                            'footer_bg'       => $component_styles['footer_bg'] ?? '',
                            'footer_text'     => $component_styles['footer_text'] ?? '',
                            'woo_card_price'  => $component_styles['woo_card_price'] ?? '',
                        ),
                        array(
                            'footer_bg'       => $preset_components['footer_bg'] ?? '',
                            'footer_text'     => $preset_components['footer_text'] ?? '',
                            'woo_card_price'  => $preset_components['woo_card_price'] ?? '',
                        )
                    ),
                    __( '跟随全局', 'developer-starter' ),
                    __( '本站单独设置', 'developer-starter' )
                ),
            ),
        );

        $desktop_h1     = isset( $typography_system['h1']['desktop'] ) && is_array( $typography_system['h1']['desktop'] ) ? $typography_system['h1']['desktop'] : array();
        $desktop_body   = isset( $typography_system['body']['desktop'] ) && is_array( $typography_system['body']['desktop'] ) ? $typography_system['body']['desktop'] : array();
        $desktop_lead   = isset( $typography_system['lead']['desktop'] ) && is_array( $typography_system['lead']['desktop'] ) ? $typography_system['lead']['desktop'] : array();
        $desktop_menu   = isset( $typography_system['menu']['desktop'] ) && is_array( $typography_system['menu']['desktop'] ) ? $typography_system['menu']['desktop'] : array();
        $desktop_button = isset( $typography_system['button']['desktop'] ) && is_array( $typography_system['button']['desktop'] ) ? $typography_system['button']['desktop'] : array();
        $desktop_input  = isset( $typography_system['input']['desktop'] ) && is_array( $typography_system['input']['desktop'] ) ? $typography_system['input']['desktop'] : array();
        $mobile_h2      = isset( $typography_system['h2']['mobile'] ) && is_array( $typography_system['h2']['mobile'] ) ? $typography_system['h2']['mobile'] : array();
        $mobile_body    = isset( $typography_system['body']['mobile'] ) && is_array( $typography_system['body']['mobile'] ) ? $typography_system['body']['mobile'] : array();
        $mobile_menu    = isset( $typography_system['menu']['mobile'] ) && is_array( $typography_system['menu']['mobile'] ) ? $typography_system['menu']['mobile'] : array();
        $dark_body      = isset( $typography_system['body']['desktop'] ) && is_array( $typography_system['body']['desktop'] ) ? $typography_system['body']['desktop'] : array();

        $page_palette_field_count = isset( $page_design_defs['palette']['fields'] ) && is_array( $page_design_defs['palette']['fields'] ) ? count( $page_design_defs['palette']['fields'] ) : 0;
        $page_layout_field_count  = isset( $page_design_defs['layout']['fields'] ) && is_array( $page_design_defs['layout']['fields'] ) ? count( $page_design_defs['layout']['fields'] ) : 0;
        $page_typography_style_count = isset( $page_design_defs['typography']['styles'] ) && is_array( $page_design_defs['typography']['styles'] ) ? count( $page_design_defs['typography']['styles'] ) : 0;
        $diagnostic_summary       = isset( $diagnostics['summary'] ) && is_array( $diagnostics['summary'] ) ? $diagnostics['summary'] : array();
        $preset_source_label      = 'custom' === $preset_source ? __( '自定义预设', 'developer-starter' ) : __( '系统预设', 'developer-starter' );
        $typography_summary_config = array(
            'body'   => __( 'Body 正文', 'developer-starter' ),
            'lead'   => __( 'Lead 导语', 'developer-starter' ),
            'menu'   => __( 'Menu 导航', 'developer-starter' ),
            'button' => __( 'Button 按钮', 'developer-starter' ),
            'h1'     => 'H1',
            'h2'     => 'H2',
        );
        $component_summary_config = array(
            array(
                'label'       => __( '页头 / 文字 Logo / 桌面导航 / 电话按钮', 'developer-starter' ),
                'bgKey'       => 'header_bg',
                'textKey'     => 'nav_link',
                'metaKey'     => 'header_logo_scrolled_fill',
                'sourceKeys'  => array( 'header_bg', 'nav_link', 'nav_hover_bg', 'header_logo_transparent_fill', 'header_logo_scrolled_fill', 'header_phone_bg', 'header_phone_text', 'header_phone_transparent_bg', 'header_phone_transparent_text' ),
                'inheritLabel'=> __( '跟随全局', 'developer-starter' ),
                'overrideLabel' => __( '本站单独设置', 'developer-starter' ),
            ),
            array(
                'label'       => __( '按钮', 'developer-starter' ),
                'bgKey'       => 'button_bg',
                'textKey'     => 'button_text',
                'metaKey'     => 'button_padding',
                'sourceKeys'  => array( 'button_bg', 'button_text', 'button_padding' ),
                'inheritLabel'=> __( '跟随全局', 'developer-starter' ),
                'overrideLabel' => __( '本站单独设置', 'developer-starter' ),
            ),
            array(
                'label'       => __( '卡片 / 文章卡片', 'developer-starter' ),
                'bgKey'       => 'post_card_bg',
                'textKey'     => 'post_card_title_color',
                'metaKey'     => 'post_card_meta_color',
                'sourceKeys'  => array( 'post_card_bg', 'post_card_title_color', 'post_card_meta_color' ),
                'inheritLabel'=> __( '跟随全局', 'developer-starter' ),
                'overrideLabel' => __( '本站单独设置', 'developer-starter' ),
            ),
            array(
                'label'       => __( '表单', 'developer-starter' ),
                'bgKey'       => 'form_input_bg',
                'textKey'     => 'form_input_text',
                'metaKey'     => 'form_input_border',
                'sourceKeys'  => array( 'form_input_bg', 'form_input_text', 'form_input_border' ),
                'inheritLabel'=> __( '跟随全局', 'developer-starter' ),
                'overrideLabel' => __( '本站单独设置', 'developer-starter' ),
            ),
            array(
                'label'       => __( '移动导航', 'developer-starter' ),
                'bgKey'       => 'mobile_nav_bg',
                'textKey'     => 'mobile_nav_link',
                'metaKey'     => 'mobile_nav_hover_bg',
                'sourceKeys'  => array( 'mobile_nav_bg', 'mobile_nav_link', 'mobile_nav_hover_bg' ),
                'inheritLabel'=> __( '跟随全局', 'developer-starter' ),
                'overrideLabel' => __( '本站单独设置', 'developer-starter' ),
            ),
            array(
                'label'       => __( '页脚 / Woo 卡片', 'developer-starter' ),
                'bgKey'       => 'footer_bg',
                'textKey'     => 'footer_text',
                'metaKey'     => 'woo_card_price',
                'sourceKeys'  => array( 'footer_bg', 'footer_text', 'footer_heading', 'footer_heading_size', 'woo_card_price' ),
                'inheritLabel'=> __( '跟随全局', 'developer-starter' ),
                'overrideLabel' => __( '本站单独设置', 'developer-starter' ),
            ),
        );
        $contrast_diagnostic_config = array(
            array(
                'key'   => 'text_background',
                'label' => __( '正文 / 页面背景', 'developer-starter' ),
                'fg'    => array( 'type' => 'token', 'key' => 'text' ),
                'bg'    => array( 'type' => 'token', 'key' => 'background' ),
            ),
            array(
                'key'   => 'heading_background',
                'label' => __( '标题 / 页面背景', 'developer-starter' ),
                'fg'    => array( 'type' => 'token', 'key' => 'heading' ),
                'bg'    => array( 'type' => 'token', 'key' => 'background' ),
            ),
            array(
                'key'   => 'button_primary',
                'label' => __( '主按钮文字 / 背景', 'developer-starter' ),
                'fg'    => array( 'type' => 'component', 'key' => 'button_text' ),
                'bg'    => array( 'type' => 'component', 'key' => 'button_bg' ),
            ),
            array(
                'key'   => 'form_input',
                'label' => __( '输入框文字 / 背景', 'developer-starter' ),
                'fg'    => array( 'type' => 'component', 'key' => 'form_input_text' ),
                'bg'    => array( 'type' => 'component', 'key' => 'form_input_bg' ),
            ),
            array(
                'key'   => 'dropdown_layer',
                'label' => __( '下拉层文字 / 背景', 'developer-starter' ),
                'fg'    => array( 'type' => 'component', 'key' => 'dropdown_link' ),
                'bg'    => array( 'type' => 'component', 'key' => 'dropdown_bg' ),
            ),
            array(
                'key'   => 'footer_main',
                'label' => __( '页脚文字 / 页脚背景', 'developer-starter' ),
                'fg'    => array( 'type' => 'component', 'key' => 'footer_text' ),
                'bg'    => array( 'type' => 'component', 'key' => 'footer_bg' ),
            ),
            array(
                'key'   => 'dark_text_background',
                'label' => __( '暗色正文 / 暗色背景', 'developer-starter' ),
                'fg'    => array( 'type' => 'token', 'key' => 'dark_text' ),
                'bg'    => array( 'type' => 'token', 'key' => 'dark_bg' ),
            ),
        );
        $dark_mode_diagnostic_config = array(
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
        $focus_base_url = admin_url( 'admin.php?page=developer-starter-settings&tab=design' );
        $workbench_seed = array(
            'payload' => $payload,
            'defaults' => array(
                'palette'    => $preset_tokens,
                'typography' => $preset_typography,
                'layout'     => $preset_layout,
                'components' => $preset_components,
            ),
            'baseDefaults' => array(
                'typography' => $default_typography,
                'layout'     => $default_layout,
            ),
            'swatches'               => $swatches,
            'typographySummary'      => $typography_summary_config,
            'componentSummary'       => $component_summary_config,
            'contrastDiagnostics'    => $contrast_diagnostic_config,
            'darkModeDiagnostics'    => $dark_mode_diagnostic_config,
            'messages'               => array(
                'enabled'            => __( '全局设计已启用', 'developer-starter' ),
                'disabled'           => __( '全局设计未启用', 'developer-starter' ),
                'systemPreset'       => __( '系统预设', 'developer-starter' ),
                'customPreset'       => __( '自定义预设', 'developer-starter' ),
                'contrastNormal'     => __( '对比度基线正常', 'developer-starter' ),
                'contrastReview'     => __( '存在对比度待复核', 'developer-starter' ),
                'presetInherit'      => __( '跟随预设', 'developer-starter' ),
                'siteOverride'       => __( '本站单独设置', 'developer-starter' ),
                'baseDefault'        => __( '主题默认', 'developer-starter' ),
                'varInherit'         => __( '跟随全局', 'developer-starter' ),
                'defaultInherit'     => __( '默认跟随', 'developer-starter' ),
                'diffCount'          => __( '%d 处调整', 'developer-starter' ),
                'overviewBreakdown'  => __( '色板 %1$d / 排版 %2$d / 布局 %3$d / 组件 %4$d', 'developer-starter' ),
                'contrastCount'      => __( '%d 项', 'developer-starter' ),
                'unknownValue'       => __( '-', 'developer-starter' ),
                'desktopPrefix'      => __( 'Desktop: ', 'developer-starter' ),
                'mobilePrefix'       => __( 'Mobile: ', 'developer-starter' ),
                'containerPrefix'    => __( '容器 ', 'developer-starter' ),
                'spacingPrefix'      => __( '区块 ', 'developer-starter' ),
                'gapPrefix'          => __( '栅格 ', 'developer-starter' ),
                'tabletPrefix'       => __( 'Tablet ', 'developer-starter' ),
                'mobileLabel'        => __( 'Mobile ', 'developer-starter' ),
                'manualReview'       => __( '待人工复核', 'developer-starter' ),
                'contrastPass'       => __( '对比度通过 WCAG AA 正文建议值。', 'developer-starter' ),
                'contrastWarning'    => __( '对比度低于 4.5，建议提升文字与背景反差。', 'developer-starter' ),
                'contrastUnknown'    => __( '包含渐变或复杂变量，当前工作台只对纯色对比度做快速检测。', 'developer-starter' ),
                'hardcodedClean'     => __( '可忽略', 'developer-starter' ),
                'hardcodedWarn'      => __( '可忽略', 'developer-starter' ),
                'darkPass'           => __( '可忽略', 'developer-starter' ),
                'darkWarning'        => __( '可忽略', 'developer-starter' ),
                'darkPassMessage'    => __( '可忽略', 'developer-starter' ),
                'darkWarningMessage' => __( '可忽略', 'developer-starter' ),
                'darkLiteralWarning' => __( '可忽略', 'developer-starter' ),
                'overrideCount'      => __( '%d 处覆盖', 'developer-starter' ),
                'overridePalette'    => __( '色板单独设置', 'developer-starter' ),
                'overrideTypography' => __( '排版单独设置', 'developer-starter' ),
                'overrideLayout'     => __( '布局单独设置', 'developer-starter' ),
                'overrideComponent'  => __( '组件单独设置', 'developer-starter' ),
                'overrideCurrent'    => __( '当前：', 'developer-starter' ),
                'overrideBase'       => __( '基准：', 'developer-starter' ),
                'overrideGroupClean' => __( '当前分组还在跟随全局。', 'developer-starter' ),
                'overrideMore'       => __( '其余 %d 项可在对应设置区继续查看。', 'developer-starter' ),
                'missingClean'       => __( '当前关键字段没有发现明显缺项。', 'developer-starter' ),
                'missingWarn'        => __( '建议完善', 'developer-starter' ),
                'missingPaletteScope' => __( '全局色板', 'developer-starter' ),
                'missingTypographyScope' => __( '响应式排版', 'developer-starter' ),
                'missingLayoutScope' => __( '响应式布局', 'developer-starter' ),
                'missingDetail'      => __( '当前为空，建议完善后再观察联动效果。', 'developer-starter' ),
                'focusField'         => __( '定位设置', 'developer-starter' ),
                'focusBaseUrl'       => $focus_base_url,
                'impactHigh'         => __( '高影响', 'developer-starter' ),
                'impactMedium'       => __( '中影响', 'developer-starter' ),
                'impactLow'          => __( '低影响', 'developer-starter' ),
                'riskAllLabel'       => __( '查看全部', 'developer-starter' ),
                'riskAllHint'        => __( '当前正在查看全部入口，下面会一起显示所有调整和提醒。', 'developer-starter' ),
                'riskFilterHint'     => __( '当前聚焦：%s，下面列表已按该主流程分区筛选。', 'developer-starter' ),
                'riskHeaderNav'      => __( '页头 / 导航关注点', 'developer-starter' ),
                'riskHeaderNavDescription' => __( '重点检查页头、桌面导航和下拉主入口。', 'developer-starter' ),
                'riskMobile'         => __( '移动端关注点', 'developer-starter' ),
                'riskMobileDescription' => __( '重点检查手机排版、移动导航和断点联动。', 'developer-starter' ),
                'riskDark'           => __( '暗色提醒', 'developer-starter' ),
                'riskDarkDescription' => __( '用于检查暗色风格的显示效果，普通使用可以先忽略。', 'developer-starter' ),
                'riskBreakdown'      => __( '覆盖 %1$d / 缺项 %2$d / 诊断 %3$d', 'developer-starter' ),
                'riskEmpty'          => __( '当前分区没有对应项目：%s。', 'developer-starter' ),
                'ratioPrefix'        => __( 'Ratio ', 'developer-starter' ),
                'coverageCommand'    => __( '可在下方查看详细检查结果。', 'developer-starter' ),
            ),
        );

        ob_start();
        ?>
        <div class="ds-design-workbench" data-ds-design-workbench="1">
            <script type="application/json" data-ds-design-workbench-seed="1"><?php echo wp_json_encode( $workbench_seed ); ?></script>
            <div class="ds-design-workbench__head">
                <div class="ds-design-workbench__head-copy">
                    <span class="ds-design-workbench__eyebrow"><?php echo esc_html__( '全局设计工作台', 'developer-starter' ); ?></span>
                    <h3 data-ds-workbench-title="1"><?php echo esc_html( sprintf( __( '当前预设：%s', 'developer-starter' ), $preset_label ) ); ?></h3>
                    <p><?php echo esc_html__( '集中展示全站预设、本站单独设置、页面单独设置和模块单独设置，用于判断当前生效来源。', 'developer-starter' ); ?></p>
                </div>
                <div class="ds-design-workbench__head-badges">
                    <span class="ds-design-workbench__badge <?php echo esc_attr( ! empty( $payload['enabled'] ) ? 'is-info' : 'is-warning' ); ?>" data-ds-workbench-enabled-badge="1"><?php echo esc_html( ! empty( $payload['enabled'] ) ? __( '全局设计已启用', 'developer-starter' ) : __( '全局设计未启用', 'developer-starter' ) ); ?></span>
                    <span class="ds-design-workbench__badge is-info" data-ds-workbench-preset-source-badge="1"><?php echo esc_html( $preset_source_label ); ?></span>
                    <span class="ds-design-workbench__badge <?php echo esc_attr( ! empty( $diagnostic_summary['contrastWarnings'] ) ? 'is-warning' : 'is-success' ); ?>" data-ds-workbench-contrast-badge="1"><?php echo esc_html( ! empty( $diagnostic_summary['contrastWarnings'] ) ? __( '存在对比度待复核', 'developer-starter' ) : __( '对比度基线正常', 'developer-starter' ) ); ?></span>
                </div>
            </div>

            <div class="ds-design-workbench__overview">
                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '设置层级', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '全局预设 → 本站单独设置 → 页面单独设置 → 模块单独设置', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__inheritance">
                        <article class="ds-design-workbench__inherit-step">
                            <span class="ds-design-workbench__badge is-info" data-ds-workbench-preset-source-badge="1"><?php echo esc_html( $preset_source_label ); ?></span>
                            <strong><?php echo esc_html__( '全局预设', 'developer-starter' ); ?></strong>
                            <p data-ds-workbench-preset-label="1"><?php echo esc_html( $preset_label ); ?></p>
                            <code><?php echo esc_html__( '作为整站色板和基础氛围的起点。', 'developer-starter' ); ?></code>
                        </article>
                        <article class="ds-design-workbench__inherit-step">
                            <span class="ds-design-workbench__badge <?php echo esc_attr( $palette_diff_count + $typography_diff_count + $layout_diff_count + $component_diff_count > 0 ? 'is-warning' : 'is-success' ); ?>" data-ds-workbench-site-diff-badge="1"><?php echo esc_html( sprintf( __( '%d 处调整', 'developer-starter' ), $palette_diff_count + $typography_diff_count + $layout_diff_count + $component_diff_count ) ); ?></span>
                            <strong><?php echo esc_html__( '本站单独设置', 'developer-starter' ); ?></strong>
                            <p data-ds-workbench-site-diff-copy="1"><?php echo esc_html( sprintf( __( '色板 %1$d / 排版 %2$d / 布局 %3$d / 组件 %4$d', 'developer-starter' ), $palette_diff_count, $typography_diff_count, $layout_diff_count, $component_diff_count ) ); ?></p>
                            <code><?php echo esc_html__( '只有和当前预设不一样的值，才会记在这里。', 'developer-starter' ); ?></code>
                        </article>
                        <article class="ds-design-workbench__inherit-step">
                            <span class="ds-design-workbench__badge is-info"><?php echo esc_html( sprintf( __( '色板 %1$d / 排版 %2$d / 布局 %3$d', 'developer-starter' ), $page_palette_field_count, $page_typography_style_count, $page_layout_field_count ) ); ?></span>
                            <strong><?php echo esc_html__( '页面单独设置', 'developer-starter' ); ?></strong>
                            <p><?php echo esc_html__( '装修器中的“页面设计覆盖”只作用于当前页面。', 'developer-starter' ); ?></p>
                            <code><?php echo esc_html__( '在“页面装修 -> 页面设置”里单独维护。', 'developer-starter' ); ?></code>
                        </article>
                        <article class="ds-design-workbench__inherit-step">
                            <span class="ds-design-workbench__badge is-info"><?php echo esc_html__( '默认跟随', 'developer-starter' ); ?></span>
                            <strong><?php echo esc_html__( '模块单独设置', 'developer-starter' ); ?></strong>
                            <p><?php echo esc_html__( '模块默认跟随页面和全局样式；这里的调整只影响当前模块。', 'developer-starter' ); ?></p>
                            <code><?php echo esc_html__( '适合局部高亮、区块差异化和少量局部样式调整。', 'developer-starter' ); ?></code>
                        </article>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '样式提示', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '快速检测当前样式是否存在明显风险。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__summary-grid">
                        <article class="ds-design-workbench__summary-card">
                            <strong><?php echo esc_html__( '对比度复核', 'developer-starter' ); ?></strong>
                            <span class="<?php echo esc_attr( ! empty( $diagnostic_summary['contrastWarnings'] ) ? 'is-warning' : 'is-success' ); ?>" data-ds-workbench-summary-card="contrastWarnings"><?php echo esc_html( sprintf( __( '%d 项', 'developer-starter' ), isset( $diagnostic_summary['contrastWarnings'] ) ? absint( $diagnostic_summary['contrastWarnings'] ) : 0 ) ); ?></span>
                        </article>
                        <article class="ds-design-workbench__summary-card">
                            <strong><?php echo esc_html__( '颜色写法', 'developer-starter' ); ?></strong>
                            <span class="is-info" data-ds-workbench-summary-card="hardcodedCount"><?php echo esc_html( sprintf( __( '%d 项', 'developer-starter' ), isset( $diagnostic_summary['hardcodedCount'] ) ? absint( $diagnostic_summary['hardcodedCount'] ) : 0 ) ); ?></span>
                        </article>
                        <article class="ds-design-workbench__summary-card">
                            <strong><?php echo esc_html__( '暗色提醒', 'developer-starter' ); ?></strong>
                            <span class="is-info" data-ds-workbench-summary-card="darkModeWarnings"><?php echo esc_html( sprintf( __( '%d 项', 'developer-starter' ), isset( $diagnostic_summary['darkModeWarnings'] ) ? absint( $diagnostic_summary['darkModeWarnings'] ) : 0 ) ); ?></span>
                        </article>
                        <article class="ds-design-workbench__summary-card">
                            <strong><?php echo esc_html__( '深入检查', 'developer-starter' ); ?></strong>
                            <span><?php echo esc_html__( '查看下方详细检查结果', 'developer-starter' ); ?></span>
                        </article>
                    </div>
                </section>
            </div>

            <div class="ds-design-workbench__studio">
                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '桌面工作台快照', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '同时看页头、Hero、卡片、表单、标签页和页脚。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__canvas <?php echo esc_attr( isset( $layout_system['layout_mode'] ) && 'boxed' === (string) $layout_system['layout_mode'] ? 'is-boxed' : '' ); ?>" data-ds-preview-node="desktopCanvas" style="<?php echo esc_attr( $build_style_attr( array(
                        'background' => $resolve_style_value( $tokens['background'] ?? '#ffffff' ),
                        'color'      => $resolve_style_value( $tokens['text'] ?? '#1f2937' ),
                    ) ) ); ?>">
                        <div class="ds-design-workbench__canvas-header" data-ds-preview-node="desktopHeader" style="<?php echo esc_attr( $build_style_attr( array(
                            'background'   => $resolve_style_value( $component_styles['header_bg'] ?? '' ),
                            'border-color' => $resolve_style_value( $component_styles['header_border'] ?? '' ),
                            'box-shadow'   => $resolve_style_value( $component_styles['header_shadow'] ?? '' ),
                        ) ) ); ?>">
                            <strong data-ds-preview-node="desktopBrand" style="<?php echo esc_attr( $build_style_attr( array(
                                'color'         => $resolve_style_value( $tokens['heading'] ?? '' ),
                                'font-size'     => isset( $desktop_menu['font_size'] ) ? (string) $desktop_menu['font_size'] : '',
                                'line-height'   => isset( $desktop_menu['line_height'] ) ? (string) $desktop_menu['line_height'] : '',
                                'font-weight'   => isset( $desktop_menu['font_weight'] ) ? (string) $desktop_menu['font_weight'] : '',
                                'letter-spacing'=> isset( $desktop_menu['letter_spacing'] ) ? (string) $desktop_menu['letter_spacing'] : '',
                            ) ) ); ?>">Qiling</strong>
                            <nav>
                                <span data-ds-preview-node="desktopNavHome" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['nav_link'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '首页', 'developer-starter' ); ?></span>
                                <span data-ds-preview-node="desktopNavActive" style="<?php echo esc_attr( $build_style_attr( array(
                                    'background' => $resolve_style_value( $component_styles['nav_hover_bg'] ?? '' ),
                                    'color'      => $resolve_style_value( $component_styles['nav_hover_text'] ?? '' ),
                                ) ) ); ?>"><?php echo esc_html__( '服务', 'developer-starter' ); ?></span>
                                <span data-ds-preview-node="desktopNavCase" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['nav_link'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '案例', 'developer-starter' ); ?></span>
                            </nav>
                            <span class="ds-design-workbench__mini-badge" data-ds-preview-node="desktopPhone" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'      => $resolve_style_value( $component_styles['header_phone_bg'] ?? '' ),
                                'color'           => $resolve_style_value( $component_styles['header_phone_text'] ?? '' ),
                                'font-size'       => isset( $desktop_menu['font_size'] ) ? (string) $desktop_menu['font_size'] : '',
                                'line-height'     => isset( $desktop_menu['line_height'] ) ? (string) $desktop_menu['line_height'] : '',
                                'font-weight'     => isset( $desktop_menu['font_weight'] ) ? (string) $desktop_menu['font_weight'] : '',
                                'letter-spacing'  => isset( $desktop_menu['letter_spacing'] ) ? (string) $desktop_menu['letter_spacing'] : '',
                            ) ) ); ?>"><?php echo esc_html__( '400-123-4567', 'developer-starter' ); ?></span>
                            <span class="ds-design-workbench__mini-badge" data-ds-preview-node="desktopMiniBadge" style="<?php echo esc_attr( $build_style_attr( array(
                                'background' => $resolve_style_value( $component_styles['badge_bg'] ?? '' ),
                                'color'      => $resolve_style_value( $component_styles['badge_text'] ?? '' ),
                            ) ) ); ?>"><?php echo esc_html__( 'Global', 'developer-starter' ); ?></span>
                        </div>
                        <div class="ds-design-workbench__canvas-hero" data-ds-preview-node="desktopHero" style="<?php echo esc_attr( $build_style_attr( array(
                            'padding-top'    => isset( $layout_system['section_spacing']['desktop'] ) ? (string) $layout_system['section_spacing']['desktop'] : '',
                            'padding-bottom' => isset( $layout_system['section_spacing']['desktop'] ) ? (string) $layout_system['section_spacing']['desktop'] : '',
                        ) ) ); ?>">
                            <h4 data-ds-preview-node="desktopHeroTitle" style="<?php echo esc_attr( $build_style_attr( array(
                                'color'          => $resolve_style_value( $tokens['heading'] ?? '' ),
                                'font-size'      => isset( $desktop_h1['font_size'] ) ? (string) $desktop_h1['font_size'] : '',
                                'line-height'    => isset( $desktop_h1['line_height'] ) ? (string) $desktop_h1['line_height'] : '',
                                'font-weight'    => isset( $desktop_h1['font_weight'] ) ? (string) $desktop_h1['font_weight'] : '',
                                'letter-spacing' => isset( $desktop_h1['letter_spacing'] ) ? (string) $desktop_h1['letter_spacing'] : '',
                            ) ) ); ?>"><?php echo esc_html__( '工作台预览会同时反映标题、正文、布局和组件样式。', 'developer-starter' ); ?></h4>
                            <p data-ds-preview-node="desktopHeroLead" style="<?php echo esc_attr( $build_style_attr( array(
                                'color'          => $resolve_style_value( $tokens['text_muted'] ?? '' ),
                                'font-size'      => isset( $desktop_lead['font_size'] ) ? (string) $desktop_lead['font_size'] : '',
                                'line-height'    => isset( $desktop_lead['line_height'] ) ? (string) $desktop_lead['line_height'] : '',
                                'font-weight'    => isset( $desktop_lead['font_weight'] ) ? (string) $desktop_lead['font_weight'] : '',
                                'letter-spacing' => isset( $desktop_lead['letter_spacing'] ) ? (string) $desktop_lead['letter_spacing'] : '',
                            ) ) ); ?>"><?php echo esc_html__( '调整全局样式后，可以看到主流程界面的整体变化。', 'developer-starter' ); ?></p>
                            <button type="button" data-ds-preview-node="desktopButton" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'      => $resolve_style_value( $component_styles['button_bg'] ?? '' ),
                                'color'           => $resolve_style_value( $component_styles['button_text'] ?? '' ),
                                'border-color'    => $resolve_style_value( $component_styles['button_border'] ?? '' ),
                                'box-shadow'      => $resolve_style_value( $component_styles['button_shadow'] ?? '' ),
                                'padding'         => $resolve_style_value( $component_styles['button_padding'] ?? '' ),
                                'font-size'       => isset( $desktop_button['font_size'] ) ? (string) $desktop_button['font_size'] : '',
                                'line-height'     => isset( $desktop_button['line_height'] ) ? (string) $desktop_button['line_height'] : '',
                                'font-weight'     => isset( $desktop_button['font_weight'] ) ? (string) $desktop_button['font_weight'] : '',
                                'letter-spacing'  => isset( $desktop_button['letter_spacing'] ) ? (string) $desktop_button['letter_spacing'] : '',
                            ) ) ); ?>"><?php echo esc_html__( '立即预览', 'developer-starter' ); ?></button>
                        </div>
                        <div class="ds-design-workbench__canvas-grid" data-ds-preview-node="desktopGrid" style="<?php echo esc_attr( $build_style_attr( array(
                            'gap' => isset( $layout_system['grid_gap']['desktop'] ) ? (string) $layout_system['grid_gap']['desktop'] : '',
                        ) ) ); ?>">
                            <article class="ds-design-workbench__canvas-card" data-ds-preview-node="desktopCardPrimary" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'   => $resolve_style_value( $component_styles['card_bg'] ?? '' ),
                                'border-color' => $resolve_style_value( $component_styles['card_border'] ?? '' ),
                                'box-shadow'   => $resolve_style_value( $component_styles['card_shadow'] ?? '' ),
                            ) ) ); ?>">
                                <strong data-ds-preview-node="desktopCardPrimaryTitle" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['post_card_title_color'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '文章卡片', 'developer-starter' ); ?></strong>
                                <p data-ds-preview-node="desktopCardPrimaryBody" style="<?php echo esc_attr( $build_style_attr( array(
                                    'color'          => $resolve_style_value( $tokens['text'] ?? '' ),
                                    'font-size'      => isset( $desktop_body['font_size'] ) ? (string) $desktop_body['font_size'] : '',
                                    'line-height'    => isset( $desktop_body['line_height'] ) ? (string) $desktop_body['line_height'] : '',
                                    'font-weight'    => isset( $desktop_body['font_weight'] ) ? (string) $desktop_body['font_weight'] : '',
                                    'letter-spacing' => isset( $desktop_body['letter_spacing'] ) ? (string) $desktop_body['letter_spacing'] : '',
                                ) ) ); ?>"><?php echo esc_html__( '预览卡片背景、标题层级和正文节奏。', 'developer-starter' ); ?></p>
                                <span data-ds-preview-node="desktopCardPrimaryMeta" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['post_card_meta_color'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '2026-04-20 · 全局设计', 'developer-starter' ); ?></span>
                            </article>
                            <article class="ds-design-workbench__canvas-card" data-ds-preview-node="desktopCardSecondary" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'   => $resolve_style_value( $tokens['surface_alt'] ?? '' ),
                                'border-color' => $resolve_style_value( $component_styles['tabs_border'] ?? '' ),
                            ) ) ); ?>">
                                <div class="ds-design-workbench__canvas-tabs">
                                    <span><?php echo esc_html__( '概览', 'developer-starter' ); ?></span>
                                    <span data-ds-preview-node="desktopTabActive" style="<?php echo esc_attr( $build_style_attr( array(
                                        'background'   => $resolve_style_value( $component_styles['tabs_active_bg'] ?? '' ),
                                        'color'        => $resolve_style_value( $component_styles['tabs_active_text'] ?? '' ),
                                        'border-color' => $resolve_style_value( $component_styles['tabs_active_border'] ?? '' ),
                                    ) ) ); ?>"><?php echo esc_html__( '跟随', 'developer-starter' ); ?></span>
                                    <span><?php echo esc_html__( '诊断', 'developer-starter' ); ?></span>
                                </div>
                                <div class="ds-design-workbench__canvas-alert" data-ds-preview-node="desktopAlert" style="<?php echo esc_attr( $build_style_attr( array(
                                    'background'   => $resolve_style_value( $component_styles['alert_bg'] ?? '' ),
                                    'border-color' => $resolve_style_value( $component_styles['alert_border'] ?? '' ),
                                    'color'        => $resolve_style_value( $component_styles['alert_text'] ?? '' ),
                                ) ) ); ?>">
                                    <?php echo esc_html__( '若预览不协调，可优先检查全局样式。', 'developer-starter' ); ?>
                                </div>
                                <input type="text" value="<?php echo esc_attr__( '输入关键词，观察表单状态', 'developer-starter' ); ?>" readonly data-ds-preview-node="desktopInput" style="<?php echo esc_attr( $build_style_attr( array(
                                    'background'      => $resolve_style_value( $component_styles['form_input_bg'] ?? '' ),
                                    'color'           => $resolve_style_value( $component_styles['form_input_text'] ?? '' ),
                                    'border-color'    => $resolve_style_value( $component_styles['form_input_border'] ?? '' ),
                                    'font-size'       => isset( $desktop_input['font_size'] ) ? (string) $desktop_input['font_size'] : '',
                                    'line-height'     => isset( $desktop_input['line_height'] ) ? (string) $desktop_input['line_height'] : '',
                                    'font-weight'     => isset( $desktop_input['font_weight'] ) ? (string) $desktop_input['font_weight'] : '',
                                    'letter-spacing'  => isset( $desktop_input['letter_spacing'] ) ? (string) $desktop_input['letter_spacing'] : '',
                                ) ) ); ?>" />
                            </article>
                        </div>
                        <div class="ds-design-workbench__canvas-footer" data-ds-preview-node="desktopFooter" style="<?php echo esc_attr( $build_style_attr( array(
                            'background' => $resolve_style_value( $component_styles['footer_bg'] ?? '' ),
                            'color'      => $resolve_style_value( $component_styles['footer_text'] ?? '' ),
                        ) ) ); ?>">
                            <span data-ds-preview-node="desktopFooterHeading" style="<?php echo esc_attr( $build_style_attr( array(
                                'color'     => $resolve_style_value( $component_styles['footer_heading'] ?? '' ),
                                'font-size' => $resolve_style_value( $component_styles['footer_heading_size'] ?? '' ),
                            ) ) ); ?>"><?php echo esc_html__( '页脚标题', 'developer-starter' ); ?></span>
                            <span><?php echo esc_html__( '页脚区域', 'developer-starter' ); ?></span>
                            <span data-ds-preview-node="desktopFooterLink" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['footer_link'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '政策条款', 'developer-starter' ); ?></span>
                            <span data-ds-preview-node="desktopFooterPrice" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['woo_card_price'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '￥299', 'developer-starter' ); ?></span>
                        </div>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '手机与暗色快照', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '重点看移动导航、底部导航和暗色可读性。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__side-stack">
                        <div class="ds-design-workbench__phone" data-ds-preview-node="mobilePhone" style="<?php echo esc_attr( $build_style_attr( array(
                            'background' => $resolve_style_value( $tokens['background'] ?? '' ),
                            'color'      => $resolve_style_value( $tokens['text'] ?? '' ),
                        ) ) ); ?>">
                            <div class="ds-design-workbench__phone-head" data-ds-preview-node="mobilePhoneHead" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'   => $resolve_style_value( $component_styles['mobile_nav_bg'] ?? '' ),
                                'border-color' => $resolve_style_value( $component_styles['mobile_nav_border'] ?? '' ),
                            ) ) ); ?>">
                                <span><?php echo esc_html__( '启灵', 'developer-starter' ); ?></span>
                                <strong data-ds-preview-node="mobilePhoneMenu" style="<?php echo esc_attr( $build_style_attr( array(
                                    'color'          => $resolve_style_value( $component_styles['mobile_nav_link'] ?? '' ),
                                    'font-size'      => isset( $mobile_menu['font_size'] ) ? (string) $mobile_menu['font_size'] : '',
                                    'line-height'    => isset( $mobile_menu['line_height'] ) ? (string) $mobile_menu['line_height'] : '',
                                    'font-weight'    => isset( $mobile_menu['font_weight'] ) ? (string) $mobile_menu['font_weight'] : '',
                                    'letter-spacing' => isset( $mobile_menu['letter_spacing'] ) ? (string) $mobile_menu['letter_spacing'] : '',
                                ) ) ); ?>"><?php echo esc_html__( '移动导航', 'developer-starter' ); ?></strong>
                            </div>
                            <div class="ds-design-workbench__phone-body">
                                <h5 data-ds-preview-node="mobilePhoneTitle" style="<?php echo esc_attr( $build_style_attr( array(
                                    'color'          => $resolve_style_value( $tokens['heading'] ?? '' ),
                                    'font-size'      => isset( $mobile_h2['font_size'] ) ? (string) $mobile_h2['font_size'] : '',
                                    'line-height'    => isset( $mobile_h2['line_height'] ) ? (string) $mobile_h2['line_height'] : '',
                                    'font-weight'    => isset( $mobile_h2['font_weight'] ) ? (string) $mobile_h2['font_weight'] : '',
                                    'letter-spacing' => isset( $mobile_h2['letter_spacing'] ) ? (string) $mobile_h2['letter_spacing'] : '',
                                ) ) ); ?>"><?php echo esc_html__( '移动端会先暴露布局和字号问题。', 'developer-starter' ); ?></h5>
                                <p data-ds-preview-node="mobilePhoneBody" style="<?php echo esc_attr( $build_style_attr( array(
                                    'color'          => $resolve_style_value( $tokens['text_muted'] ?? '' ),
                                    'font-size'      => isset( $mobile_body['font_size'] ) ? (string) $mobile_body['font_size'] : '',
                                    'line-height'    => isset( $mobile_body['line_height'] ) ? (string) $mobile_body['line_height'] : '',
                                    'font-weight'    => isset( $mobile_body['font_weight'] ) ? (string) $mobile_body['font_weight'] : '',
                                    'letter-spacing' => isset( $mobile_body['letter_spacing'] ) ? (string) $mobile_body['letter_spacing'] : '',
                                ) ) ); ?>"><?php echo esc_html__( '手机字号、间距和底部导航状态会单独展示。', 'developer-starter' ); ?></p>
                            </div>
                            <div class="ds-design-workbench__phone-bottom" data-ds-preview-node="mobilePhoneBottom" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'   => $resolve_style_value( $component_styles['mobile_nav_bg'] ?? '' ),
                                'border-color' => $resolve_style_value( $component_styles['mobile_nav_border'] ?? '' ),
                            ) ) ); ?>">
                                <span data-ds-preview-node="mobilePhoneBottomHome" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['mobile_nav_link'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '首页', 'developer-starter' ); ?></span>
                                <span data-ds-preview-node="mobilePhoneBottomActive" style="<?php echo esc_attr( $build_style_attr( array(
                                    'background' => $resolve_style_value( $component_styles['mobile_nav_hover_bg'] ?? '' ),
                                    'color'      => $resolve_style_value( $component_styles['mobile_nav_hover_text'] ?? '' ),
                                ) ) ); ?>"><?php echo esc_html__( '导航', 'developer-starter' ); ?></span>
                                <span data-ds-preview-node="mobilePhoneBottomMine" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['mobile_nav_link'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '我的', 'developer-starter' ); ?></span>
                            </div>
                        </div>

                        <div class="ds-design-workbench__dark-preview" data-ds-preview-node="darkPreview" style="<?php echo esc_attr( $build_style_attr( array(
                            'background' => $resolve_style_value( $tokens['dark_bg'] ?? '' ),
                            'color'      => $resolve_style_value( $tokens['dark_text'] ?? '' ),
                            'border-color' => $resolve_style_value( $tokens['dark_border'] ?? '' ),
                        ) ) ); ?>">
                            <strong><?php echo esc_html__( '暗色模式快照', 'developer-starter' ); ?></strong>
                            <p data-ds-preview-node="darkPreviewBody" style="<?php echo esc_attr( $build_style_attr( array(
                                'color'          => $resolve_style_value( $tokens['dark_text_muted'] ?? '' ),
                                'font-size'      => isset( $dark_body['font_size'] ) ? (string) $dark_body['font_size'] : '',
                                'line-height'    => isset( $dark_body['line_height'] ) ? (string) $dark_body['line_height'] : '',
                                'font-weight'    => isset( $dark_body['font_weight'] ) ? (string) $dark_body['font_weight'] : '',
                                'letter-spacing' => isset( $dark_body['letter_spacing'] ) ? (string) $dark_body['letter_spacing'] : '',
                            ) ) ); ?>"><?php echo esc_html__( '这里优先观察暗色正文、暗色卡片、暗色表单是否仍然清晰。', 'developer-starter' ); ?></p>
                            <div class="ds-design-workbench__dark-card" data-ds-preview-node="darkCard" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'   => $resolve_style_value( $component_styles['dark_post_card_bg'] ?? '' ),
                                'border-color' => $resolve_style_value( $component_styles['dark_post_card_border'] ?? '' ),
                            ) ) ); ?>">
                                <strong data-ds-preview-node="darkCardTitle" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['dark_post_card_title_color'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( '暗色文章卡片', 'developer-starter' ); ?></strong>
                                <span data-ds-preview-node="darkCardMeta" style="<?php echo esc_attr( $build_style_attr( array( 'color' => $resolve_style_value( $component_styles['dark_post_card_meta_color'] ?? '' ) ) ) ); ?>"><?php echo esc_html__( 'Meta / 时间 / 标签', 'developer-starter' ); ?></span>
                            </div>
                            <input type="text" value="<?php echo esc_attr__( '暗色表单输入', 'developer-starter' ); ?>" readonly data-ds-preview-node="darkInput" style="<?php echo esc_attr( $build_style_attr( array(
                                'background'   => $resolve_style_value( $component_styles['dark_form_input_bg'] ?? '' ),
                                'color'        => $resolve_style_value( $component_styles['dark_form_input_text'] ?? '' ),
                                'border-color' => $resolve_style_value( $component_styles['dark_form_input_border'] ?? '' ),
                            ) ) ); ?>" />
                        </div>
                    </div>
                </section>
            </div>

            <div class="ds-design-workbench__meta">
                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '色板与来源', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '色板默认跟随预设，只有不一样的值才算本站单独设置。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__swatches" data-ds-workbench-swatches="1">
                        <?php foreach ( $swatch_items as $item ) : ?>
                            <article class="ds-design-workbench__swatch">
                                <span class="ds-design-workbench__swatch-chip" style="<?php echo esc_attr( $build_style_attr( array( 'background' => $item['preview'] ) ) ); ?>"></span>
                                <div>
                                    <strong><?php echo esc_html( $item['label'] ); ?></strong>
                                    <code><?php echo esc_html( $item['value'] ); ?></code>
                                    <span class="ds-design-workbench__badge <?php echo esc_attr( $format_source_tone( $item['source']['tone'] ) ); ?>"><?php echo esc_html( $item['source']['label'] ); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '排版体系摘要', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '看桌面 / 手机是否都在同一个层级体系里。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__metric-list" data-ds-workbench-typography-summary="1">
                        <?php foreach ( $typography_items as $item ) : ?>
                            <article class="ds-design-workbench__metric-card">
                                <div class="ds-design-workbench__metric-head">
                                    <strong><?php echo esc_html( $item['label'] ); ?></strong>
                                    <span class="ds-design-workbench__badge <?php echo esc_attr( $format_source_tone( $item['source']['tone'] ) ); ?>"><?php echo esc_html( $item['source']['label'] ); ?></span>
                                </div>
                                <code><?php echo esc_html( 'Desktop: ' . $format_typography_meta( $item['desktop'] ) ); ?></code>
                                <code><?php echo esc_html( 'Mobile: ' . $format_typography_meta( $item['mobile'] ) ); ?></code>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '布局尺度摘要', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '容器、间距、断点和 boxed / wide 的联动状态。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__metric-list" data-ds-workbench-layout-summary="1">
                        <?php foreach ( $layout_items as $item ) : ?>
                            <article class="ds-design-workbench__metric-card">
                                <div class="ds-design-workbench__metric-head">
                                    <strong><?php echo esc_html( $item['label'] ); ?></strong>
                                    <span class="ds-design-workbench__badge <?php echo esc_attr( $format_source_tone( $item['source']['tone'] ) ); ?>"><?php echo esc_html( $item['source']['label'] ); ?></span>
                                </div>
                                <code><?php echo esc_html( $item['value'] ); ?></code>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '公共组件摘要', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '检查组件是否仍然走 token，而不是局部写死。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__components" data-ds-workbench-component-summary="1">
                        <?php foreach ( $component_cards as $component ) : ?>
                            <article class="ds-design-workbench__component-card">
                                <div class="ds-design-workbench__component-head">
                                    <strong><?php echo esc_html( $component['label'] ); ?></strong>
                                    <span class="ds-design-workbench__badge <?php echo esc_attr( $format_source_tone( $component['source']['tone'] ) ); ?>"><?php echo esc_html( $component['source']['label'] ); ?></span>
                                </div>
                                <div class="ds-design-workbench__component-demo" style="<?php echo esc_attr( $build_style_attr( array(
                                    'background' => $component['bg'],
                                    'color'      => $component['text'],
                                ) ) ); ?>"><?php echo esc_html( $component['label'] ); ?></div>
                                <code><?php echo esc_html( (string) $component['meta'] ); ?></code>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <div class="ds-design-workbench__diagnostics">
                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '重点检查区域', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '按站点主流程分区展示关键检查项。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__risk-toolbar">
                        <button type="button" class="ds-design-workbench__risk-pill is-active" data-ds-workbench-risk-zone="all"><?php echo esc_html__( '查看全部', 'developer-starter' ); ?></button>
                        <p class="ds-design-workbench__risk-status" data-ds-workbench-risk-status="1"><?php echo esc_html__( '当前查看全部入口，下面的覆盖、缺项和诊断列表会一起联动。', 'developer-starter' ); ?></p>
                    </div>
                    <div class="ds-design-workbench__risk-zones" data-ds-workbench-risk-zones="1"></div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '对比度诊断', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '优先检查文字 / 背景这类最影响体验的组合。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__diagnostic-list" data-ds-workbench-contrast-list="1">
                        <?php foreach ( array_slice( isset( $diagnostics['contrast'] ) && is_array( $diagnostics['contrast'] ) ? $diagnostics['contrast'] : array(), 0, 8 ) as $item ) : ?>
                            <article class="ds-design-workbench__diagnostic-card">
                                <div class="ds-design-workbench__metric-head">
                                    <strong><?php echo esc_html( isset( $item['label'] ) ? (string) $item['label'] : '' ); ?></strong>
                                    <span class="ds-design-workbench__badge <?php echo esc_attr( 'warning' === ( $item['status'] ?? '' ) ? 'is-warning' : ( 'pass' === ( $item['status'] ?? '' ) ? 'is-success' : 'is-info' ) ); ?>">
                                        <?php
                                        if ( isset( $item['ratio'] ) && is_numeric( $item['ratio'] ) ) {
                                            echo esc_html( 'Ratio ' . number_format_i18n( (float) $item['ratio'], 2 ) );
                                        } else {
                                            echo esc_html__( '待人工复核', 'developer-starter' );
                                        }
                                        ?>
                                    </span>
                                </div>
                                <p><?php echo esc_html( isset( $item['message'] ) ? (string) $item['message'] : '' ); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '颜色写法', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '可忽略', 'developer-starter' ); ?></span>
                    </div>
                    <?php $hardcoded_components = isset( $diagnostics['hardcodedComponents'] ) && is_array( $diagnostics['hardcodedComponents'] ) ? $diagnostics['hardcodedComponents'] : array(); ?>
                    <div data-ds-workbench-hardcoded-body="1">
                    <?php if ( empty( $hardcoded_components ) ) : ?>
                        <p class="ds-design-workbench__empty"><?php echo esc_html__( '可忽略', 'developer-starter' ); ?></p>
                    <?php else : ?>
                        <div class="ds-design-workbench__diagnostic-list">
                            <?php foreach ( array_slice( $hardcoded_components, 0, 8 ) as $item ) : ?>
                                <article class="ds-design-workbench__diagnostic-card">
                                    <div class="ds-design-workbench__metric-head">
                                        <strong><?php echo esc_html( isset( $item['label'] ) ? (string) $item['label'] : '' ); ?></strong>
                                        <span class="ds-design-workbench__badge is-info"><?php echo esc_html__( '可忽略', 'developer-starter' ); ?></span>
                                    </div>
                                    <code><?php echo esc_html( isset( $item['value'] ) ? (string) $item['value'] : '' ); ?></code>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '暗色提醒', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '可忽略', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__diagnostic-list" data-ds-workbench-dark-list="1">
                        <?php foreach ( array_slice( isset( $diagnostics['darkMode'] ) && is_array( $diagnostics['darkMode'] ) ? $diagnostics['darkMode'] : array(), 0, 8 ) as $item ) : ?>
                            <article class="ds-design-workbench__diagnostic-card">
                                <div class="ds-design-workbench__metric-head">
                                    <strong><?php echo esc_html( isset( $item['label'] ) ? (string) $item['label'] : '' ); ?></strong>
                                    <span class="ds-design-workbench__badge is-info"><?php echo esc_html__( '可忽略', 'developer-starter' ); ?></span>
                                </div>
                                <p><?php echo esc_html( isset( $item['message'] ) ? (string) $item['message'] : '' ); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '覆盖明细', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '直接看哪些字段正在改写预设或主题默认，避免只看总数。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__audit-groups" data-ds-workbench-override-groups="1"></div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '缺项与空值提醒', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '关键颜色、排版和布局为空时会在这里提示。', 'developer-starter' ); ?></span>
                    </div>
                    <div data-ds-workbench-missing-body="1"></div>
                </section>

                <section class="ds-design-workbench__panel">
                    <div class="ds-design-workbench__panel-head">
                        <strong><?php echo esc_html__( '样式详细检查', 'developer-starter' ); ?></strong>
                        <span><?php echo esc_html__( '用于进一步检查样式一致性和潜在显示问题。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="ds-design-workbench__script-box">
                        <p><?php echo esc_html__( '请结合上方对比度、覆盖明细和空值提醒逐项检查。', 'developer-starter' ); ?></p>
                    </div>
                </section>
            </div>
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function(){
            var root = document.querySelector("[data-ds-design-workbench='1']");
            if (!root) {
                return;
            }
            var seedNode = root.querySelector("[data-ds-design-workbench-seed='1']");
            if (!seedNode) {
                return;
            }
            function parseJsonNode(node, fallback) {
                if (!node) {
                    return fallback;
                }
                try {
                    return JSON.parse(node.textContent || "");
                } catch (error) {
                    return fallback;
                }
            }
            function cloneData(value) {
                try {
                    return JSON.parse(JSON.stringify(value || {}));
                } catch (error) {
                    return {};
                }
            }
            function normalizeValue(value) {
                if (value === null || typeof value === "undefined") {
                    return "";
                }
                return String(value).trim();
            }
            function hasValue(value) {
                return normalizeValue(value) !== "";
            }
            function escapeHtml(value) {
                return String(value == null ? "" : value)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
            function formatTemplate(template, values) {
                var index = 0;
                return String(template || "").replace(/%(\d+\$)?[ds]/g, function(match, positional){
                    if (positional) {
                        var position = parseInt(String(positional).replace("$", ""), 10) - 1;
                        return typeof values[position] !== "undefined" ? values[position] : "";
                    }
                    var value = typeof values[index] !== "undefined" ? values[index] : "";
                    index += 1;
                    return value;
                });
            }
            function formatCount(value) {
                var count = Math.max(0, parseInt(value, 10) || 0);
                return formatTemplate(messages.contrastCount || "%d 项", [count]);
            }
            function formatDiffCount(value) {
                var count = Math.max(0, parseInt(value, 10) || 0);
                return formatTemplate(messages.diffCount || "%d 处调整", [count]);
            }
            function isHexColor(value) {
                return /^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i.test(normalizeValue(value));
            }
            function expandHexColor(value) {
                var hex = normalizeValue(value).replace(/^#/, "");
                if (hex.length === 3) {
                    return "#" + hex.split("").map(function(channel){
                        return channel + channel;
                    }).join("");
                }
                return "#" + hex;
            }
            function shiftHexColor(value, percent) {
                var hex = expandHexColor(value).replace(/^#/, "");
                var amount = Math.max(-100, Math.min(100, parseInt(percent, 10) || 0));
                var result = "#";
                hex.match(/.{2}/g).forEach(function(channel){
                    var channelValue = parseInt(channel, 16);
                    if (amount >= 0) {
                        channelValue = Math.round(channelValue + (255 - channelValue) * (amount / 100));
                    } else {
                        channelValue = Math.round(channelValue * (1 + amount / 100));
                    }
                    channelValue = Math.max(0, Math.min(255, channelValue));
                    result += channelValue.toString(16).padStart(2, "0");
                });
                return result;
            }
            function hexToRgbString(value) {
                var hex = expandHexColor(value).replace(/^#/, "");
                return [
                    parseInt(hex.slice(0, 2), 16),
                    parseInt(hex.slice(2, 4), 16),
                    parseInt(hex.slice(4, 6), 16)
                ].join(", ");
            }
            function getElementValue(element) {
                if (!element) {
                    return "";
                }
                if (element.type === "checkbox") {
                    return element.checked ? normalizeValue(element.value || "1") : "";
                }
                return normalizeValue(element.value);
            }
            function getPreviewNode(name) {
                return root.querySelector("[data-ds-preview-node='" + name + "']");
            }
            function setNodeStyles(node, styles) {
                if (!node) {
                    return;
                }
                Object.keys(styles || {}).forEach(function(property){
                    node.style[property] = styles[property] || "";
                });
            }
            function applyTypographyStyles(node, properties) {
                setNodeStyles(node, {
                    fontSize: normalizeValue(properties && properties.font_size),
                    lineHeight: normalizeValue(properties && properties.line_height),
                    fontWeight: normalizeValue(properties && properties.font_weight),
                    letterSpacing: normalizeValue(properties && properties.letter_spacing)
                });
            }
            function parseCssColor(value) {
                var normalized = normalizeValue(value);
                if (!normalized) {
                    return null;
                }
                if (isHexColor(normalized)) {
                    var hex = expandHexColor(normalized).replace(/^#/, "");
                    return {
                        r: parseInt(hex.slice(0, 2), 16),
                        g: parseInt(hex.slice(2, 4), 16),
                        b: parseInt(hex.slice(4, 6), 16),
                        a: 1
                    };
                }
                var match = normalized.match(/^rgba?\(([^)]+)\)$/i);
                if (!match) {
                    return null;
                }
                var channels = match[1].split(",").map(function(channel){
                    return normalizeValue(channel);
                });
                if (channels.length < 3) {
                    return null;
                }
                return {
                    r: Math.max(0, Math.min(255, parseFloat(channels[0]) || 0)),
                    g: Math.max(0, Math.min(255, parseFloat(channels[1]) || 0)),
                    b: Math.max(0, Math.min(255, parseFloat(channels[2]) || 0)),
                    a: channels.length > 3 ? Math.max(0, Math.min(1, parseFloat(channels[3]) || 0)) : 1
                };
            }
            function blendColor(color, background) {
                var alpha = typeof color.a === "number" ? color.a : 1;
                return {
                    r: Math.round(color.r * alpha + background.r * (1 - alpha)),
                    g: Math.round(color.g * alpha + background.g * (1 - alpha)),
                    b: Math.round(color.b * alpha + background.b * (1 - alpha))
                };
            }
            function relativeLuminance(color) {
                var channels = [color.r, color.g, color.b].map(function(channel){
                    var value = channel / 255;
                    return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
                });
                return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
            }
            function calculateContrastRatio(foreground, background) {
                var foregroundColor = parseCssColor(foreground);
                var backgroundColor = parseCssColor(background);
                if (!foregroundColor || !backgroundColor) {
                    return null;
                }
                var white = { r: 255, g: 255, b: 255 };
                var fg = foregroundColor.a < 1 ? blendColor(foregroundColor, white) : foregroundColor;
                var bg = backgroundColor.a < 1 ? blendColor(backgroundColor, white) : backgroundColor;
                var fgLuminance = relativeLuminance(fg);
                var bgLuminance = relativeLuminance(bg);
                var lighter = Math.max(fgLuminance, bgLuminance);
                var darker = Math.min(fgLuminance, bgLuminance);
                return (lighter + 0.05) / (darker + 0.05);
            }
            function resolveStyleValue(value, variables, depth) {
                var normalized = normalizeValue(value);
                if (!normalized || depth > 6) {
                    return "";
                }
                if (normalized.indexOf("var(") === -1) {
                    return normalized;
                }
                return normalized.replace(/var\((--[a-zA-Z0-9_-]+)(?:\s*,\s*([^)]+))?\)/g, function(match, variableKey, fallback){
                    if (variables[variableKey]) {
                        return resolveStyleValue(variables[variableKey], variables, depth + 1);
                    }
                    return fallback ? resolveStyleValue(fallback, variables, depth + 1) : match;
                }).trim();
            }
            function resolveColorValue(value, variables) {
                var resolved = resolveStyleValue(value, variables, 0);
                if (!resolved || /(linear-gradient|radial-gradient|conic-gradient)/i.test(resolved)) {
                    return "";
                }
                return resolved;
            }
            function containsLiteralDesignColor(value) {
                return /#(?:[0-9a-f]{3}|[0-9a-f]{6})\b|rgba?\(/i.test(normalizeValue(value));
            }
            function isVariableDrivenDesignValue(value) {
                return /var\(--/i.test(normalizeValue(value));
            }
            function countNestedDifference(current, base) {
                if (Array.isArray(current) || Array.isArray(base)) {
                    current = Array.isArray(current) ? current : [];
                    base = Array.isArray(base) ? base : [];
                }
                if ((current && typeof current === "object") || (base && typeof base === "object")) {
                    var currentObject = current && typeof current === "object" ? current : {};
                    var baseObject = base && typeof base === "object" ? base : {};
                    var keys = Object.keys(currentObject).concat(Object.keys(baseObject)).filter(function(key, index, list){
                        return list.indexOf(key) === index;
                    });
                    return keys.reduce(function(total, key){
                        return total + countNestedDifference(currentObject[key], baseObject[key]);
                    }, 0);
                }
                return normalizeValue(current) === normalizeValue(base) ? 0 : 1;
            }
            function formatTypographyMeta(properties) {
                return [
                    normalizeValue(properties && properties.font_size),
                    hasValue(properties && properties.line_height) ? "LH " + normalizeValue(properties.line_height) : "",
                    hasValue(properties && properties.font_weight) ? "W " + normalizeValue(properties.font_weight) : "",
                    hasValue(properties && properties.letter_spacing) ? "LS " + normalizeValue(properties.letter_spacing) : ""
                ].filter(Boolean).join(" / ");
            }
            var seed = parseJsonNode(seedNode, {});
            var payload = seed.payload && typeof seed.payload === "object" ? seed.payload : {};
            var messages = seed.messages && typeof seed.messages === "object" ? seed.messages : {};
            var tokenOptionMap = payload.tokenOptionMap && typeof payload.tokenOptionMap === "object" ? payload.tokenOptionMap : {};
            var componentOptionMap = payload.componentOptionMap && typeof payload.componentOptionMap === "object" ? payload.componentOptionMap : {};
            var tokenDefinitions = Array.isArray(payload.tokenDefinitions) ? payload.tokenDefinitions : [];
            var componentDefinitions = Array.isArray(payload.componentStyleDefinitions) ? payload.componentStyleDefinitions : [];
            var componentSchema = payload.componentSchema && typeof payload.componentSchema === "object" ? payload.componentSchema : {};
            var responsiveDevices = payload.responsiveDevices && typeof payload.responsiveDevices === "object" ? payload.responsiveDevices : {};
            var typographyDefinitions = payload.typographyDefinitions && typeof payload.typographyDefinitions === "object" ? payload.typographyDefinitions : {};
            var typographyPropertyDefinitions = payload.typographyPropertyDefinitions && typeof payload.typographyPropertyDefinitions === "object" ? payload.typographyPropertyDefinitions : {};
            var layoutDefinitions = payload.layoutDefinitions && typeof payload.layoutDefinitions === "object" ? payload.layoutDefinitions : {};
            var tokenDefinitionMap = {};
            var componentDefinitionMap = {};
            tokenDefinitions.forEach(function(definition){
                if (!definition || !definition.key) {
                    return;
                }
                tokenDefinitionMap[definition.key] = definition;
            });
            componentDefinitions.forEach(function(definition){
                if (!definition || !definition.key) {
                    return;
                }
                componentDefinitionMap[definition.key] = definition;
            });
            var riskZoneDefinitions = [
                {
                    key: "header_nav",
                    label: messages.riskHeaderNav || "页头 / 导航风险",
                    description: messages.riskHeaderNavDescription || "优先检查页头、桌面导航和下拉主入口。",
                    scopeKeywords: ["页头", "导航", "桌面导航", "导航下拉"],
                    textKeywords: ["header", "nav", "dropdown", "menu", "breadcrumb"]
                },
                {
                    key: "mobile",
                    label: messages.riskMobile || "移动端风险",
                    description: messages.riskMobileDescription || "优先检查手机排版、移动导航和断点联动。",
                    scopeKeywords: ["移动端", "底部导航", "平板端"],
                    textKeywords: ["mobile", "tablet", "phone"]
                },
                {
                    key: "dark",
                    label: messages.riskDark || "暗色风险",
                            description: messages.riskDarkDescription || "优先检查暗色全局样式、暗色组件与颜色映射。",
                    scopeKeywords: ["暗色"],
                    textKeywords: ["dark"]
                }
            ];
            var activeRiskZone = "all";
            function formatOverrideCount(value) {
                var count = Math.max(0, parseInt(value, 10) || 0);
                return formatTemplate(messages.overrideCount || "%d 处覆盖", [count]);
            }
            function getRiskZoneDefinition(zoneKey) {
                var match = null;
                riskZoneDefinitions.some(function(definition){
                    if (definition && definition.key === zoneKey) {
                        match = definition;
                        return true;
                    }
                    return false;
                });
                return match;
            }
            function getRiskZoneKeysForItem(item) {
                var scopedText = [];
                if (item && typeof item.scope === "string" && item.scope) {
                    scopedText.push(item.scope);
                }
                if (Array.isArray(item && item.scopes)) {
                    scopedText = scopedText.concat(item.scopes);
                }
                var scopeHaystack = scopedText.join(" ").toLowerCase();
                var textHaystack = [
                    item && item.key,
                    item && item.label,
                    item && item.message
                ].filter(Boolean).join(" ").toLowerCase();
                return riskZoneDefinitions.reduce(function(zoneKeys, definition){
                    var scopeMatch = (definition.scopeKeywords || []).some(function(keyword){
                        return scopeHaystack.indexOf(String(keyword).toLowerCase()) !== -1;
                    });
                    var textMatch = (definition.textKeywords || []).some(function(keyword){
                        return textHaystack.indexOf(String(keyword).toLowerCase()) !== -1;
                    });
                    if (scopeMatch || textMatch) {
                        zoneKeys.push(definition.key);
                    }
                    return zoneKeys;
                }, []);
            }
            function attachRiskZones(item) {
                var nextItem = Object.assign({}, item || {});
                nextItem.zoneKeys = Array.isArray(item && item.zoneKeys) && item.zoneKeys.length
                    ? item.zoneKeys.slice()
                    : getRiskZoneKeysForItem(item);
                return nextItem;
            }
            function itemMatchesRiskZone(item, zoneKey) {
                if (!zoneKey || zoneKey === "all") {
                    return true;
                }
                return attachRiskZones(item).zoneKeys.indexOf(zoneKey) !== -1;
            }
            function filterItemsByRiskZone(items, zoneKey) {
                var targetZone = zoneKey || activeRiskZone;
                return (Array.isArray(items) ? items : []).map(function(item){
                    return attachRiskZones(item);
                }).filter(function(item){
                    return itemMatchesRiskZone(item, targetZone);
                });
            }
            function getActiveRiskZoneLabel() {
                var definition = getRiskZoneDefinition(activeRiskZone);
                return definition && definition.label ? definition.label : (messages.riskAllLabel || "查看全部");
            }
            function getRiskFilterStatusText() {
                if (!activeRiskZone || activeRiskZone === "all") {
                    return messages.riskAllHint || "当前查看全部入口，下面的覆盖、缺项和诊断列表会一起联动。";
                }
                return formatTemplate(messages.riskFilterHint || "当前聚焦：%s，下面列表已按该主流程分区筛选。", [getActiveRiskZoneLabel()]);
            }
            function isDerivedLegacyTokenKey(tokenKey) {
                return [
                    "font_size_base",
                    "line_height_base",
                    "container_width",
                    "section_padding",
                    "grid_gap",
                    "breakpoint_tablet",
                    "breakpoint_mobile",
                    "layout_mode"
                ].indexOf(tokenKey) !== -1;
            }
            function getLegacyLocatorByOptionId(optionId) {
                var optionMap = {
                    design_font_size_base: {
                        type: "typography",
                        styleKey: "body",
                        deviceKey: "desktop",
                        propertyKey: "font_size"
                    },
                    design_line_height_base: {
                        type: "typography",
                        styleKey: "body",
                        deviceKey: "desktop",
                        propertyKey: "line_height"
                    },
                    design_container_width: {
                        type: "layout",
                        layoutKey: "container_width",
                        deviceKey: "desktop"
                    },
                    design_section_padding: {
                        type: "layout",
                        layoutKey: "section_spacing",
                        deviceKey: "desktop"
                    },
                    design_grid_gap: {
                        type: "layout",
                        layoutKey: "grid_gap",
                        deviceKey: "desktop"
                    },
                    design_breakpoint_tablet: {
                        type: "layout",
                        layoutKey: "breakpoints",
                        deviceKey: "tablet"
                    },
                    design_breakpoint_mobile: {
                        type: "layout",
                        layoutKey: "breakpoints",
                        deviceKey: "mobile"
                    },
                    design_layout_mode: {
                        type: "layout",
                        layoutKey: "layout_mode"
                    }
                };
                return optionMap[optionId] || null;
            }
            function buildLocatorSelector(locator) {
                if (!locator || typeof locator !== "object") {
                    return "";
                }
                if (locator.type === "id" && locator.value) {
                    var legacyLocator = getLegacyLocatorByOptionId(locator.value);
                    if (legacyLocator) {
                        return buildLocatorSelector(legacyLocator);
                    }
                    return "#setting-row-" + locator.value + ",[data-setting-id='" + locator.value + "'],#" + locator.value;
                }
                if (locator.type === "typography") {
                    if (locator.styleKey && locator.deviceKey && locator.propertyKey) {
                        return "#setting-row-design_typography_system [data-ds-typography-input='1'][data-style-key='" + locator.styleKey + "'][data-device-key='" + locator.deviceKey + "'][data-property-key='" + locator.propertyKey + "'],[data-setting-id='design_typography_system'] [data-ds-typography-input='1'][data-style-key='" + locator.styleKey + "'][data-device-key='" + locator.deviceKey + "'][data-property-key='" + locator.propertyKey + "'],#setting-row-design_typography_system,[data-setting-id='design_typography_system']";
                    }
                    return "#setting-row-design_typography_system,[data-setting-id='design_typography_system']";
                }
                if (locator.type === "layout") {
                    if (locator.layoutKey === "layout_mode") {
                        return "#setting-row-design_layout_system [data-ds-layout-mode='1'],[data-setting-id='design_layout_system'] [data-ds-layout-mode='1'],#setting-row-design_layout_system,[data-setting-id='design_layout_system']";
                    }
                    if (locator.layoutKey && locator.deviceKey) {
                        return "#setting-row-design_layout_system [data-ds-layout-input='1'][data-layout-group='" + locator.layoutKey + "'][data-layout-device='" + locator.deviceKey + "'],[data-setting-id='design_layout_system'] [data-ds-layout-input='1'][data-layout-group='" + locator.layoutKey + "'][data-layout-device='" + locator.deviceKey + "'],#setting-row-design_layout_system,[data-setting-id='design_layout_system']";
                    }
                    return "#setting-row-design_layout_system,[data-setting-id='design_layout_system']";
                }
                return "";
            }
            function extractFocusHref(selector) {
                var match = String(selector || "").match(/#([A-Za-z0-9_-]+)/);
                var hash = match ? ("#" + match[1]) : "#";
                var baseUrl = String(messages.focusBaseUrl || window.location.href || "").split("#")[0];
                return baseUrl ? (baseUrl + hash) : hash;
            }
            function navigateToFocusTarget(focusButton) {
                if (!focusButton) {
                    return;
                }
                var selector = focusButton.getAttribute("data-ds-workbench-focus-selector") || "";
                var href = focusButton.getAttribute("href") || extractFocusHref(selector);
                var currentBase = String(window.location.href || "").split("#")[0];
                var targetBase = String(href || "").split("#")[0];
                if (href && href !== "#" && targetBase && targetBase !== currentBase) {
                    window.location.assign(href);
                    return;
                }
                var hashIndex = String(href || "").indexOf("#");
                if (hashIndex !== -1) {
                    window.location.hash = String(href).slice(hashIndex);
                }
                window.setTimeout(function(){
                    focusFieldBySelector(selector);
                }, 0);
            }
            function buildImpactLevel(score) {
                var numericScore = Math.max(0, parseInt(score, 10) || 0);
                if (numericScore >= 90) {
                    return {
                        label: messages.impactHigh || "高影响",
                        tone: "is-warning"
                    };
                }
                if (numericScore >= 75) {
                    return {
                        label: messages.impactMedium || "中影响",
                        tone: "is-info"
                    };
                }
                return {
                    label: messages.impactLow || "低影响",
                    tone: "is-inherit"
                };
            }
            function sortItemsByImpact(items) {
                return (Array.isArray(items) ? items.slice() : []).sort(function(left, right){
                    var scoreDiff = (parseInt(right && right.score, 10) || 0) - (parseInt(left && left.score, 10) || 0);
                    if (scoreDiff !== 0) {
                        return scoreDiff;
                    }
                    return String((left && left.label) || "").localeCompare(String((right && right.label) || ""), "zh-Hans-CN");
                });
            }
            function getTokenImpactMeta(tokenKey) {
                var impactMap = {
                    primary: { score: 100, scopes: ["品牌", "按钮", "导航"] },
                    primary_hover: { score: 90, scopes: ["按钮", "交互"] },
                    secondary: { score: 82, scopes: ["品牌辅助", "区块"] },
                    accent: { score: 86, scopes: ["高亮", "入口"] },
                    text: { score: 100, scopes: ["正文", "整站可读性"] },
                    text_muted: { score: 78, scopes: ["辅助信息", "说明文字"] },
                    heading: { score: 96, scopes: ["标题", "导航"] },
                    background: { score: 92, scopes: ["整站背景", "首屏"] },
                    surface: { score: 90, scopes: ["卡片", "表单"] },
                    surface_alt: { score: 80, scopes: ["浅色区块", "标签页"] },
                    border: { score: 78, scopes: ["边框", "分隔"] },
                    dark_bg: { score: 92, scopes: ["暗色", "整站背景"] },
                    dark_text: { score: 96, scopes: ["暗色", "正文"] },
                    dark_border: { score: 78, scopes: ["暗色", "边框"] }
                };
                return impactMap[tokenKey] || { score: 68, scopes: ["全局设计"] };
            }
            function getTypographyImpactMeta(styleKey, deviceKey, propertyKey) {
                var styleImpactMap = {
                    body: { score: 96, scopes: ["正文"] },
                    lead: { score: 82, scopes: ["导语", "首屏"] },
                    menu: { score: 92, scopes: ["导航"] },
                    button: { score: 92, scopes: ["按钮", "转化"] },
                    input: { score: 88, scopes: ["表单"] },
                    h1: { score: 94, scopes: ["主标题", "首屏"] },
                    h2: { score: 88, scopes: ["区块标题"] },
                    h3: { score: 76, scopes: ["卡片标题"] },
                    h4: { score: 70, scopes: ["次级标题"] },
                    h5: { score: 64, scopes: ["微标题"] },
                    h6: { score: 60, scopes: ["微标题"] },
                    small: { score: 58, scopes: ["辅助信息"] }
                };
                var propertyPenaltyMap = {
                    font_size: 0,
                    line_height: 4,
                    font_weight: 10,
                    letter_spacing: 14
                };
                var deviceBonusMap = {
                    desktop: 0,
                    tablet: 3,
                    mobile: 6
                };
                var baseMeta = styleImpactMap[styleKey] || { score: 66, scopes: ["排版"] };
                var score = baseMeta.score - (propertyPenaltyMap[propertyKey] || 0) + (deviceBonusMap[deviceKey] || 0);
                var scopes = baseMeta.scopes.slice();
                if (deviceKey === "mobile" && scopes.indexOf("移动端") === -1) {
                    scopes.push("移动端");
                }
                if (deviceKey === "desktop" && styleKey === "menu" && scopes.indexOf("桌面导航") === -1) {
                    scopes.push("桌面导航");
                }
                return {
                    score: Math.max(48, score),
                    scopes: scopes
                };
            }
            function getLayoutImpactMeta(layoutKey, deviceKey) {
                var layoutImpactMap = {
                    container_width: { score: 94, scopes: ["布局", "容器"] },
                    section_spacing: { score: 88, scopes: ["节奏", "区块"] },
                    grid_gap: { score: 84, scopes: ["栅格", "卡片"] },
                    breakpoints: { score: 98, scopes: ["响应式", "整站"] },
                    layout_mode: { score: 92, scopes: ["布局模式", "整站"] }
                };
                var baseMeta = layoutImpactMap[layoutKey] || { score: 72, scopes: ["布局"] };
                var score = baseMeta.score + (deviceKey === "mobile" ? 6 : deviceKey === "tablet" ? 3 : 0);
                var scopes = baseMeta.scopes.slice();
                if (deviceKey && deviceKey !== "desktop" && scopes.indexOf("移动端") === -1) {
                    scopes.push(deviceKey === "mobile" ? "移动端" : "平板端");
                }
                return {
                    score: Math.max(48, score),
                    scopes: scopes
                };
            }
            function getComponentImpactMeta(componentKey) {
                var definition = componentDefinitionMap[componentKey] || {};
                var group = definition.group || "";
                var groupImpactMap = {
                    header: { score: 96, scopes: ["页头", "桌面端"] },
                    desktop_nav: { score: 98, scopes: ["导航", "桌面端"] },
                    mobile_nav: { score: 100, scopes: ["移动端", "底部导航"] },
                    button: { score: 96, scopes: ["按钮", "转化"] },
                    form: { score: 90, scopes: ["表单", "搜索"] },
                    post_card: { score: 84, scopes: ["内容卡片"] },
                    card: { score: 82, scopes: ["卡片"] },
                    footer: { score: 80, scopes: ["页脚"] },
                    dropdown: { score: 84, scopes: ["导航下拉"] },
                    tabs: { score: 76, scopes: ["交互组件"] },
                    modal: { score: 72, scopes: ["弹层"] },
                    sidebar: { score: 70, scopes: ["侧栏"] },
                    pagination: { score: 70, scopes: ["分页"] },
                    breadcrumb: { score: 66, scopes: ["面包屑"] },
                    accordion: { score: 66, scopes: ["折叠面板"] },
                    badge: { score: 68, scopes: ["徽标", "高亮"] },
                    woo_card: { score: 76, scopes: ["商城", "商品卡片"] },
                    module_title: { score: 74, scopes: ["模块标题"] }
                };
                var keyOverrides = {
                    footer_bg: { score: 88, scopes: ["页脚", "整站底部"] },
                    footer_text: { score: 84, scopes: ["页脚", "可读性"] },
                    woo_card_price: { score: 78, scopes: ["商城", "价格"] },
                    dark_post_card_bg: { score: 82, scopes: ["暗色", "内容卡片"] },
                    dark_form_input_bg: { score: 84, scopes: ["暗色", "表单"] }
                };
                return keyOverrides[componentKey] || groupImpactMap[group] || { score: 64, scopes: ["组件"] };
            }
            function getStandaloneTokenImpactMeta(tokenKey, groupKey) {
                var overrides = {
                    font_family: { score: 88, scopes: ["排版", "字体族"] },
                    card_radius: { score: 68, scopes: ["卡片", "圆角"] },
                    button_radius: { score: 76, scopes: ["按钮", "圆角"] },
                    input_radius: { score: 74, scopes: ["表单", "圆角"] },
                    shadow_sm: { score: 64, scopes: ["阴影", "轻层级"] },
                    shadow_md: { score: 68, scopes: ["阴影", "卡片"] },
                    shadow_lg: { score: 72, scopes: ["阴影", "弹层"] }
                };
                if (overrides[tokenKey]) {
                    return overrides[tokenKey];
                }
                if (groupKey === "typography") {
                    return { score: 80, scopes: ["排版"] };
                }
                if (groupKey === "layout") {
                    return { score: 84, scopes: ["布局"] };
                }
                if (groupKey === "component") {
                    return { score: 70, scopes: ["组件"] };
                }
                return getTokenImpactMeta(tokenKey);
            }
            function getDerivedTokenConfig(tokenKey) {
                switch (tokenKey) {
                    case "font_size_base":
                        return {
                            groupKey: "typography",
                            label: [
                                typographyDefinitions.body && typographyDefinitions.body.label ? typographyDefinitions.body.label : "Body 正文",
                                responsiveDevices.desktop && responsiveDevices.desktop.label ? responsiveDevices.desktop.label : "桌面端",
                                typographyPropertyDefinitions.font_size && typographyPropertyDefinitions.font_size.label ? typographyPropertyDefinitions.font_size.label : "字号"
                            ].join(" · "),
                            selector: buildLocatorSelector({
                                type: "typography",
                                styleKey: "body",
                                deviceKey: "desktop",
                                propertyKey: "font_size"
                            }),
                            impactMeta: getTypographyImpactMeta("body", "desktop", "font_size")
                        };
                    case "line_height_base":
                        return {
                            groupKey: "typography",
                            label: [
                                typographyDefinitions.body && typographyDefinitions.body.label ? typographyDefinitions.body.label : "Body 正文",
                                responsiveDevices.desktop && responsiveDevices.desktop.label ? responsiveDevices.desktop.label : "桌面端",
                                typographyPropertyDefinitions.line_height && typographyPropertyDefinitions.line_height.label ? typographyPropertyDefinitions.line_height.label : "行高"
                            ].join(" · "),
                            selector: buildLocatorSelector({
                                type: "typography",
                                styleKey: "body",
                                deviceKey: "desktop",
                                propertyKey: "line_height"
                            }),
                            impactMeta: getTypographyImpactMeta("body", "desktop", "line_height")
                        };
                    case "container_width":
                        return {
                            groupKey: "layout",
                            label: [
                                layoutDefinitions.container_width && layoutDefinitions.container_width.label ? layoutDefinitions.container_width.label : "容器宽度",
                                responsiveDevices.desktop && responsiveDevices.desktop.label ? responsiveDevices.desktop.label : "桌面端"
                            ].join(" · "),
                            selector: buildLocatorSelector({
                                type: "layout",
                                layoutKey: "container_width",
                                deviceKey: "desktop"
                            }),
                            impactMeta: getLayoutImpactMeta("container_width", "desktop")
                        };
                    case "section_padding":
                        return {
                            groupKey: "layout",
                            label: [
                                layoutDefinitions.section_spacing && layoutDefinitions.section_spacing.label ? layoutDefinitions.section_spacing.label : "区块上下间距",
                                responsiveDevices.desktop && responsiveDevices.desktop.label ? responsiveDevices.desktop.label : "桌面端"
                            ].join(" · "),
                            selector: buildLocatorSelector({
                                type: "layout",
                                layoutKey: "section_spacing",
                                deviceKey: "desktop"
                            }),
                            impactMeta: getLayoutImpactMeta("section_spacing", "desktop")
                        };
                    case "grid_gap":
                        return {
                            groupKey: "layout",
                            label: [
                                layoutDefinitions.grid_gap && layoutDefinitions.grid_gap.label ? layoutDefinitions.grid_gap.label : "栅格间距",
                                responsiveDevices.desktop && responsiveDevices.desktop.label ? responsiveDevices.desktop.label : "桌面端"
                            ].join(" · "),
                            selector: buildLocatorSelector({
                                type: "layout",
                                layoutKey: "grid_gap",
                                deviceKey: "desktop"
                            }),
                            impactMeta: getLayoutImpactMeta("grid_gap", "desktop")
                        };
                    case "breakpoint_tablet":
                        return {
                            groupKey: "layout",
                            label: [
                                layoutDefinitions.breakpoints && layoutDefinitions.breakpoints.label ? layoutDefinitions.breakpoints.label : "响应断点",
                                responsiveDevices.tablet && responsiveDevices.tablet.label ? responsiveDevices.tablet.label : "平板端"
                            ].join(" · "),
                            selector: buildLocatorSelector({
                                type: "layout",
                                layoutKey: "breakpoints",
                                deviceKey: "tablet"
                            }),
                            impactMeta: getLayoutImpactMeta("breakpoints", "tablet")
                        };
                    case "breakpoint_mobile":
                        return {
                            groupKey: "layout",
                            label: [
                                layoutDefinitions.breakpoints && layoutDefinitions.breakpoints.label ? layoutDefinitions.breakpoints.label : "响应断点",
                                responsiveDevices.mobile && responsiveDevices.mobile.label ? responsiveDevices.mobile.label : "手机端"
                            ].join(" · "),
                            selector: buildLocatorSelector({
                                type: "layout",
                                layoutKey: "breakpoints",
                                deviceKey: "mobile"
                            }),
                            impactMeta: getLayoutImpactMeta("breakpoints", "mobile")
                        };
                    case "layout_mode":
                        return {
                            groupKey: "layout",
                            label: layoutDefinitions.layout_mode && layoutDefinitions.layout_mode.label ? layoutDefinitions.layout_mode.label : "布局模式",
                            selector: buildLocatorSelector({
                                type: "layout",
                                layoutKey: "layout_mode"
                            }),
                            impactMeta: getLayoutImpactMeta("layout_mode", "desktop")
                        };
                    default:
                        return null;
                }
            }
            function getDerivedTypographyTokenKey(styleKey, deviceKey, propertyKey) {
                if (styleKey === "body" && deviceKey === "desktop" && propertyKey === "font_size") {
                    return "font_size_base";
                }
                if (styleKey === "body" && deviceKey === "desktop" && propertyKey === "line_height") {
                    return "line_height_base";
                }
                return "";
            }
            function getDerivedLayoutTokenKey(layoutKey, deviceKey) {
                if (layoutKey === "container_width" && deviceKey === "desktop") {
                    return "container_width";
                }
                if (layoutKey === "section_spacing" && deviceKey === "desktop") {
                    return "section_padding";
                }
                if (layoutKey === "grid_gap" && deviceKey === "desktop") {
                    return "grid_gap";
                }
                if (layoutKey === "breakpoints" && deviceKey === "tablet") {
                    return "breakpoint_tablet";
                }
                if (layoutKey === "breakpoints" && deviceKey === "mobile") {
                    return "breakpoint_mobile";
                }
                return "";
            }
            function hasRepresentedDerivedTokenDiff(state, presetTokens, tokenKey) {
                if (!tokenKey) {
                    return false;
                }
                return normalizeValue(state.tokens[tokenKey]) !== normalizeValue(presetTokens[tokenKey]);
            }
            function getTokenOverrideGroupKey(tokenKey) {
                if (isDerivedLegacyTokenKey(tokenKey)) {
                    return "";
                }
                var definition = tokenDefinitionMap[tokenKey] || {};
                var definitionGroup = definition.group || "";
                if (definitionGroup === "typography") {
                    return "typography";
                }
                if (definitionGroup === "layout") {
                    return "layout";
                }
                if (definitionGroup === "component") {
                    return "component";
                }
                return "palette";
            }
            function buildOverrideGroups(state) {
                var presetTokens = state.preset.data && state.preset.data.tokens ? state.preset.data.tokens : {};
                var groups = {
                    palette: {
                        key: "palette",
                        label: messages.overridePalette || "色板覆盖",
                        items: []
                    },
                    typography: {
                        key: "typography",
                        label: messages.overrideTypography || "排版覆盖",
                        items: []
                    },
                    layout: {
                        key: "layout",
                        label: messages.overrideLayout || "布局覆盖",
                        items: []
                    },
                    component: {
                        key: "component",
                        label: messages.overrideComponent || "组件覆盖",
                        items: []
                    }
                };

                Object.keys(tokenOptionMap).forEach(function(optionId){
                    var tokenKey = tokenOptionMap[optionId];
                    var currentValue = normalizeValue(state.tokens[tokenKey]);
                    var baseValue = normalizeValue(presetTokens[tokenKey]);
                    if (currentValue === baseValue) {
                        return;
                    }
                    var derivedConfig = getDerivedTokenConfig(tokenKey);
                    if (derivedConfig) {
                        groups[derivedConfig.groupKey].items.push({
                            label: derivedConfig.label,
                            current: currentValue,
                            base: baseValue,
                            score: derivedConfig.impactMeta.score,
                            scopes: derivedConfig.impactMeta.scopes,
                            selector: derivedConfig.selector
                        });
                        return;
                    }
                    var targetGroupKey = getTokenOverrideGroupKey(tokenKey);
                    if (!targetGroupKey) {
                        return;
                    }
                    var impactMeta = getStandaloneTokenImpactMeta(tokenKey, targetGroupKey);
                    groups[targetGroupKey].items.push({
                        label: tokenDefinitionMap[tokenKey] && tokenDefinitionMap[tokenKey].label ? tokenDefinitionMap[tokenKey].label : tokenKey,
                        current: currentValue,
                        base: baseValue,
                        score: impactMeta.score,
                        scopes: impactMeta.scopes,
                        selector: buildLocatorSelector({ type: "id", value: optionId })
                    });
                });

                Object.keys(typographyDefinitions).forEach(function(styleKey){
                    Object.keys(responsiveDevices).forEach(function(deviceKey){
                        Object.keys(typographyPropertyDefinitions).forEach(function(propertyKey){
                            if (hasRepresentedDerivedTokenDiff(state, presetTokens, getDerivedTypographyTokenKey(styleKey, deviceKey, propertyKey))) {
                                return;
                            }
                            var currentValue = normalizeValue((((state.typographySystem[styleKey] || {})[deviceKey] || {})[propertyKey]));
                            var baseValue = normalizeValue((((state.defaults.typography[styleKey] || {})[deviceKey] || {})[propertyKey]));
                            if (currentValue === baseValue) {
                                return;
                            }
                            var impactMeta = getTypographyImpactMeta(styleKey, deviceKey, propertyKey);
                            groups.typography.items.push({
                                label: [
                                    typographyDefinitions[styleKey] && typographyDefinitions[styleKey].label ? typographyDefinitions[styleKey].label : styleKey,
                                    responsiveDevices[deviceKey] && responsiveDevices[deviceKey].label ? responsiveDevices[deviceKey].label : deviceKey,
                                    typographyPropertyDefinitions[propertyKey] && typographyPropertyDefinitions[propertyKey].label ? typographyPropertyDefinitions[propertyKey].label : propertyKey
                                ].join(" · "),
                                current: currentValue,
                                base: baseValue,
                                score: impactMeta.score,
                                scopes: impactMeta.scopes,
                                selector: buildLocatorSelector({
                                    type: "typography",
                                    styleKey: styleKey,
                                    deviceKey: deviceKey,
                                    propertyKey: propertyKey
                                })
                            });
                        });
                    });
                });

                Object.keys(layoutDefinitions).forEach(function(layoutKey){
                    if (layoutKey === "layout_mode") {
                        if (hasRepresentedDerivedTokenDiff(state, presetTokens, "layout_mode")) {
                            return;
                        }
                        var currentMode = normalizeValue(state.layoutSystem.layout_mode);
                        var baseMode = normalizeValue(state.defaults.layout.layout_mode);
                        if (currentMode !== baseMode) {
                            var layoutModeImpactMeta = getLayoutImpactMeta(layoutKey, "desktop");
                            groups.layout.items.push({
                                label: layoutDefinitions[layoutKey] && layoutDefinitions[layoutKey].label ? layoutDefinitions[layoutKey].label : layoutKey,
                                current: currentMode,
                                base: baseMode,
                                score: layoutModeImpactMeta.score,
                                scopes: layoutModeImpactMeta.scopes,
                                selector: buildLocatorSelector({
                                    type: "layout",
                                    layoutKey: layoutKey
                                })
                            });
                        }
                        return;
                    }
                    Object.keys((state.layoutSystem[layoutKey] || {})).forEach(function(deviceKey){
                        if (hasRepresentedDerivedTokenDiff(state, presetTokens, getDerivedLayoutTokenKey(layoutKey, deviceKey))) {
                            return;
                        }
                        var currentValue = normalizeValue((((state.layoutSystem[layoutKey] || {})[deviceKey])));
                        var baseValue = normalizeValue((((state.defaults.layout[layoutKey] || {})[deviceKey])));
                        if (currentValue === baseValue) {
                            return;
                        }
                        var impactMeta = getLayoutImpactMeta(layoutKey, deviceKey);
                        groups.layout.items.push({
                            label: [
                                layoutDefinitions[layoutKey] && layoutDefinitions[layoutKey].label ? layoutDefinitions[layoutKey].label : layoutKey,
                                responsiveDevices[deviceKey] && responsiveDevices[deviceKey].label ? responsiveDevices[deviceKey].label : deviceKey
                            ].join(" · "),
                            current: currentValue,
                            base: baseValue,
                            score: impactMeta.score,
                            scopes: impactMeta.scopes,
                            selector: buildLocatorSelector({
                                type: "layout",
                                layoutKey: layoutKey,
                                deviceKey: deviceKey
                            })
                        });
                    });
                });

                Object.keys(componentOptionMap).forEach(function(optionId){
                    var componentKey = componentOptionMap[optionId];
                    var currentValue = normalizeValue(state.componentStyles[componentKey]);
                    var baseValue = normalizeValue(state.defaults.components[componentKey]);
                    if (currentValue === baseValue) {
                        return;
                    }
                    var impactMeta = getComponentImpactMeta(componentKey);
                    groups.component.items.push({
                        label: componentDefinitionMap[componentKey] && componentDefinitionMap[componentKey].label ? componentDefinitionMap[componentKey].label : componentKey,
                        current: currentValue,
                        base: baseValue,
                        score: impactMeta.score,
                        scopes: impactMeta.scopes,
                        selector: buildLocatorSelector({ type: "id", value: optionId })
                    });
                });

                Object.keys(groups).forEach(function(groupKey){
                    groups[groupKey].items = sortItemsByImpact(groups[groupKey].items);
                });

                return [
                    groups.palette,
                    groups.typography,
                    groups.layout,
                    groups.component
                ];
            }
            function buildMissingDiagnostics(state) {
                var items = [];
                function pushItem(label, scope, score, scopes, selector) {
                    items.push({
                        label: label,
                        scope: scope,
                        message: messages.missingDetail || "",
                        score: score,
                        scopes: Array.isArray(scopes) ? scopes : [],
                        selector: selector || ""
                    });
                }

                [
                    "primary",
                    "text",
                    "heading",
                    "background",
                    "surface",
                    "border",
                    "dark_bg",
                    "dark_text",
                    "dark_border"
                ].forEach(function(tokenKey){
                    if (hasValue(state.tokens[tokenKey])) {
                        return;
                    }
                    var tokenImpactMeta = getTokenImpactMeta(tokenKey);
                    pushItem(
                        tokenDefinitionMap[tokenKey] && tokenDefinitionMap[tokenKey].label ? tokenDefinitionMap[tokenKey].label : tokenKey,
                        messages.missingPaletteScope || "全局色板",
                        tokenImpactMeta.score,
                        tokenImpactMeta.scopes,
                        buildLocatorSelector({ type: "id", value: Object.keys(tokenOptionMap).find(function(optionId){
                            return tokenOptionMap[optionId] === tokenKey;
                        }) })
                    );
                });

                [
                    ["body", "desktop", "font_size"],
                    ["body", "desktop", "line_height"],
                    ["body", "mobile", "font_size"],
                    ["body", "mobile", "line_height"],
                    ["menu", "desktop", "font_size"],
                    ["button", "desktop", "font_size"],
                    ["input", "desktop", "font_size"],
                    ["h1", "desktop", "font_size"],
                    ["h2", "mobile", "font_size"]
                ].forEach(function(path){
                    var styleKey = path[0];
                    var deviceKey = path[1];
                    var propertyKey = path[2];
                    var value = normalizeValue((((state.typographySystem[styleKey] || {})[deviceKey] || {})[propertyKey]));
                    if (hasValue(value)) {
                        return;
                    }
                    var typographyImpactMeta = getTypographyImpactMeta(styleKey, deviceKey, propertyKey);
                    pushItem(
                        [
                            typographyDefinitions[styleKey] && typographyDefinitions[styleKey].label ? typographyDefinitions[styleKey].label : styleKey,
                            responsiveDevices[deviceKey] && responsiveDevices[deviceKey].label ? responsiveDevices[deviceKey].label : deviceKey,
                            typographyPropertyDefinitions[propertyKey] && typographyPropertyDefinitions[propertyKey].label ? typographyPropertyDefinitions[propertyKey].label : propertyKey
                        ].join(" · "),
                        messages.missingTypographyScope || "响应式排版",
                        typographyImpactMeta.score,
                        typographyImpactMeta.scopes,
                        buildLocatorSelector({
                            type: "typography",
                            styleKey: styleKey,
                            deviceKey: deviceKey,
                            propertyKey: propertyKey
                        })
                    );
                });

                [
                    ["container_width", "desktop"],
                    ["container_width", "mobile"],
                    ["section_spacing", "desktop"],
                    ["section_spacing", "mobile"],
                    ["grid_gap", "desktop"],
                    ["grid_gap", "mobile"],
                    ["breakpoints", "tablet"],
                    ["breakpoints", "mobile"]
                ].forEach(function(path){
                    var layoutKey = path[0];
                    var deviceKey = path[1];
                    var value = normalizeValue((((state.layoutSystem[layoutKey] || {})[deviceKey])));
                    if (hasValue(value)) {
                        return;
                    }
                    var layoutImpactMeta = getLayoutImpactMeta(layoutKey, deviceKey);
                    pushItem(
                        [
                            layoutDefinitions[layoutKey] && layoutDefinitions[layoutKey].label ? layoutDefinitions[layoutKey].label : layoutKey,
                            responsiveDevices[deviceKey] && responsiveDevices[deviceKey].label ? responsiveDevices[deviceKey].label : deviceKey
                        ].join(" · "),
                        messages.missingLayoutScope || "响应式布局",
                        layoutImpactMeta.score,
                        layoutImpactMeta.scopes,
                        buildLocatorSelector({
                            type: "layout",
                            layoutKey: layoutKey,
                            deviceKey: deviceKey
                        })
                    );
                });
                if (!hasValue(state.layoutSystem.layout_mode)) {
                    var layoutModeImpactMeta = getLayoutImpactMeta("layout_mode", "desktop");
                    pushItem(
                        layoutDefinitions.layout_mode && layoutDefinitions.layout_mode.label ? layoutDefinitions.layout_mode.label : "layout_mode",
                        messages.missingLayoutScope || "响应式布局",
                        layoutModeImpactMeta.score,
                        layoutModeImpactMeta.scopes,
                        buildLocatorSelector({
                            type: "layout",
                            layoutKey: "layout_mode"
                        })
                    );
                }

                return sortItemsByImpact(items);
            }
            function buildDefaultPaletteTokens() {
                var fallback = payload.systemPresets && payload.systemPresets.default && payload.systemPresets.default.tokens
                    ? payload.systemPresets.default.tokens
                    : payload.tokens || {};
                return cloneData(seed.defaults && seed.defaults.palette ? seed.defaults.palette : fallback);
            }
            var presetSnapshotHelper = window.DSDesignPresetSnapshot || {};
            function readPresetSnapshot(card) {
                if (typeof presetSnapshotHelper.readSnapshot === "function") {
                    return presetSnapshotHelper.readSnapshot(card);
                }
                return {
                    typographySystem: {},
                    layoutSystem: {},
                    componentStyles: {}
                };
            }
            function applySnapshotToCard(card, snapshot) {
                if (typeof presetSnapshotHelper.applySnapshotToCard === "function") {
                    presetSnapshotHelper.applySnapshotToCard(card, snapshot, messages);
                }
            }
            function applyTokensToCard(card, tokens) {
                if (!card || !tokens || typeof tokens !== "object") {
                    return;
                }
                Object.keys(tokens).forEach(function(key){
                    var input = card.querySelector("[data-token-key='" + key + "']");
                    if (input) {
                        input.value = normalizeValue(tokens[key]);
                    }
                });
            }
            function collectCustomPresets() {
                var presets = {};
                collectPresetCards().forEach(function(preset){
                    if (!preset || !preset.id) {
                        return;
                    }
                    presets[preset.id] = Object.assign({ source: "custom" }, preset);
                });
                return presets;
            }
            function getPresetMap() {
                return Object.assign({}, payload.systemPresets || {}, collectCustomPresets());
            }
            function getCurrentPreset() {
                var presetField = document.getElementById("design_preset");
                var presetMap = getPresetMap();
                var presetKey = normalizeValue(presetField && presetField.value) || normalizeValue(payload.preset) || "default";
                if (presetMap[presetKey]) {
                    return {
                        key: presetKey,
                        data: presetMap[presetKey]
                    };
                }
                if (payload.preset && presetMap[payload.preset]) {
                    return {
                        key: payload.preset,
                        data: presetMap[payload.preset]
                    };
                }
                return {
                    key: presetKey,
                    data: {
                        label: payload.presetLabel || presetKey,
                        source: payload.presetSource || "system",
                        tokens: cloneData(payload.tokens || {}),
                        typographySystem: cloneData(payload.typographySystem || {}),
                        layoutSystem: cloneData(payload.layoutSystem || {}),
                        componentStyles: cloneData(payload.componentStyles || {})
                    }
                };
            }
            function collectTypographySystem() {
                var currentPreset = getCurrentPreset();
                var system = cloneData(
                    currentPreset.data && currentPreset.data.typographySystem && Object.keys(currentPreset.data.typographySystem).length
                        ? currentPreset.data.typographySystem
                        : (seed.baseDefaults && seed.baseDefaults.typography ? seed.baseDefaults.typography : {})
                );
                document.querySelectorAll("[data-ds-typography-input='1']").forEach(function(input){
                    var styleKey = input.getAttribute("data-style-key");
                    var deviceKey = input.getAttribute("data-device-key");
                    var propertyKey = input.getAttribute("data-property-key");
                    if (!styleKey || !deviceKey || !propertyKey) {
                        return;
                    }
                    system[styleKey] = system[styleKey] && typeof system[styleKey] === "object" ? system[styleKey] : {};
                    system[styleKey][deviceKey] = system[styleKey][deviceKey] && typeof system[styleKey][deviceKey] === "object" ? system[styleKey][deviceKey] : {};
                    system[styleKey][deviceKey][propertyKey] = normalizeValue(input.value);
                });
                return system;
            }
            function collectLayoutSystem() {
                var currentPreset = getCurrentPreset();
                var system = cloneData(
                    currentPreset.data && currentPreset.data.layoutSystem && Object.keys(currentPreset.data.layoutSystem).length
                        ? currentPreset.data.layoutSystem
                        : (seed.baseDefaults && seed.baseDefaults.layout ? seed.baseDefaults.layout : {})
                );
                document.querySelectorAll("[data-ds-layout-input='1']").forEach(function(input){
                    var layoutGroup = input.getAttribute("data-layout-group");
                    var layoutDevice = input.getAttribute("data-layout-device");
                    if (!layoutGroup || !layoutDevice) {
                        return;
                    }
                    system[layoutGroup] = system[layoutGroup] && typeof system[layoutGroup] === "object" ? system[layoutGroup] : {};
                    system[layoutGroup][layoutDevice] = normalizeValue(input.value);
                });
                var layoutModeField = document.querySelector("[data-ds-layout-mode='1']");
                if (layoutModeField) {
                    system.layout_mode = getElementValue(layoutModeField) || system.layout_mode || "wide";
                }
                return system;
            }
            function buildTokenState() {
                var preset = getCurrentPreset();
                var tokens = cloneData(preset.data && preset.data.tokens ? preset.data.tokens : payload.tokens || {});
                Object.keys(tokenOptionMap).forEach(function(optionId){
                    var input = document.getElementById(optionId);
                    if (!input) {
                        return;
                    }
                    var tokenValue = getElementValue(input);
                    if (!hasValue(tokenValue)) {
                        return;
                    }
                    tokens[tokenOptionMap[optionId]] = tokenValue;
                });

                var typographySystem = collectTypographySystem();
                var layoutSystem = collectLayoutSystem();
                tokens.font_size_base = normalizeValue((((typographySystem.body || {}).desktop || {}).font_size));
                tokens.line_height_base = normalizeValue((((typographySystem.body || {}).desktop || {}).line_height));
                tokens.container_width = normalizeValue((((layoutSystem.container_width || {}).desktop)));
                tokens.section_padding = normalizeValue((((layoutSystem.section_spacing || {}).desktop)));
                tokens.grid_gap = normalizeValue((((layoutSystem.grid_gap || {}).desktop)));
                tokens.breakpoint_tablet = normalizeValue((((layoutSystem.breakpoints || {}).tablet)));
                tokens.breakpoint_mobile = normalizeValue((((layoutSystem.breakpoints || {}).mobile)));
                tokens.layout_mode = normalizeValue(layoutSystem.layout_mode) || "wide";

                Object.keys(typographySystem || {}).forEach(function(styleKey){
                    Object.keys(typographySystem[styleKey] || {}).forEach(function(deviceKey){
                        Object.keys(typographySystem[styleKey][deviceKey] || {}).forEach(function(propertyKey){
                            tokens["typography_" + styleKey + "_" + propertyKey + "_" + deviceKey] = normalizeValue(typographySystem[styleKey][deviceKey][propertyKey]);
                        });
                    });
                });
                ["desktop", "tablet", "mobile"].forEach(function(deviceKey){
                    if (layoutSystem.container_width && typeof layoutSystem.container_width === "object") {
                        tokens["container_width_" + deviceKey] = normalizeValue(layoutSystem.container_width[deviceKey]);
                    }
                    if (layoutSystem.section_spacing && typeof layoutSystem.section_spacing === "object") {
                        tokens["section_spacing_" + deviceKey] = normalizeValue(layoutSystem.section_spacing[deviceKey]);
                    }
                    if (layoutSystem.grid_gap && typeof layoutSystem.grid_gap === "object") {
                        tokens["grid_gap_" + deviceKey] = normalizeValue(layoutSystem.grid_gap[deviceKey]);
                    }
                });

                var primary = normalizeValue(tokens.primary || "#2563eb");
                if (isHexColor(primary)) {
                    tokens.primary_hover = isHexColor(tokens.primary_hover) ? tokens.primary_hover : shiftHexColor(primary, -16);
                    tokens.primary_dark = tokens.primary_hover;
                    tokens.primary_light = shiftHexColor(primary, 12);
                    tokens.primary_rgb = hexToRgbString(primary);
                } else {
                    tokens.primary_hover = normalizeValue(tokens.primary_hover) || primary;
                    tokens.primary_dark = normalizeValue(tokens.primary_dark) || primary;
                    tokens.primary_light = normalizeValue(tokens.primary_light) || primary;
                    tokens.primary_rgb = normalizeValue(tokens.primary_rgb) || "37, 99, 235";
                }

                [
                    ["accent", -12, "249, 115, 22"],
                    ["success", -14, "22, 163, 74"],
                    ["info", -14, "14, 165, 233"],
                    ["warning", -14, "245, 158, 11"],
                    ["error", -14, "220, 38, 38"]
                ].forEach(function(entry){
                    var tokenKey = entry[0];
                    var shift = entry[1];
                    var fallback = entry[2];
                    var value = normalizeValue(tokens[tokenKey]);
                    if (isHexColor(value)) {
                        tokens[tokenKey + "_dark"] = shiftHexColor(value, shift);
                        tokens[tokenKey + "_rgb"] = hexToRgbString(value);
                    } else {
                        tokens[tokenKey + "_dark"] = normalizeValue(tokens[tokenKey + "_dark"]) || value;
                        tokens[tokenKey + "_rgb"] = normalizeValue(tokens[tokenKey + "_rgb"]) || fallback;
                    }
                });
                if (isHexColor(tokens.neutral_400)) {
                    tokens.neutral_400_rgb = hexToRgbString(tokens.neutral_400);
                } else {
                    tokens.neutral_400_rgb = normalizeValue(tokens.neutral_400_rgb) || "148, 163, 184";
                }

                return {
                    preset: preset,
                    tokens: tokens,
                    typographySystem: typographySystem,
                    layoutSystem: layoutSystem
                };
            }
            function buildDefaultComponentStyles(tokens) {
                return {
                    button_bg: "var(--color-primary)",
                    button_text: "#ffffff",
                    button_border: "var(--color-primary)",
                    button_hover_bg: "var(--color-primary-hover)",
                    button_hover_text: "#ffffff",
                    button_shadow: normalizeValue(tokens.shadow_sm) || "var(--shadow-sm)",
                    button_padding: "12px 24px",
                    border_accent: "var(--color-primary)",
                    heading_weight: "700",
                    heading_letter_spacing: "0em",
                    card_bg: "var(--color-surface)",
                    card_border: "var(--color-border)",
                    card_shadow: normalizeValue(tokens.shadow_md) || "var(--shadow-md)",
                    title_bar_bg: "var(--qiling-gradient-brand-soft)",
                    title_bar_text: "var(--color-heading)",
                    title_bar_border: "rgba(var(--color-primary-rgb), 0.18)",
                    list_header_bg: "var(--color-surface-alt)",
                    list_header_text: "var(--color-heading)",
                    list_header_border: "var(--color-border)",
                    highlight_bg: "var(--qiling-gradient-brand)",
                    highlight_text: "var(--color-text-inverse)",
                    highlight_border: "var(--color-primary)",
                    highlight_soft_bg: "rgba(var(--color-primary-rgb), 0.08)",
                    form_input_bg: "var(--color-surface)",
                    form_input_text: "var(--color-text)",
                    form_input_border: "var(--color-border)",
                    form_focus_border: "var(--color-primary)",
                    module_title_color: "var(--color-heading)",
                    module_title_size: "2rem",
                    module_title_align: "center",
                    post_card_bg: "var(--color-surface)",
                    post_card_border: "var(--color-border)",
                    post_card_shadow: normalizeValue(tokens.shadow_sm) || "var(--shadow-sm)",
                    post_card_title_color: "var(--color-heading)",
                    post_card_meta_color: "var(--color-text-muted)",
                    header_bg: "var(--color-surface)",
                    header_border: "var(--color-border)",
                    header_shadow: normalizeValue(tokens.shadow_sm) || "var(--shadow-sm)",
                    header_text: "var(--color-heading)",
                    nav_link: "var(--color-text)",
                    nav_hover_bg: "var(--qiling-gradient-brand)",
                    nav_hover_text: "#ffffff",
                    mobile_nav_bg: "var(--color-surface)",
                    mobile_nav_border: "var(--color-border)",
                    mobile_nav_link: "var(--color-heading)",
                    mobile_nav_hover_bg: "var(--color-surface-alt)",
                    mobile_nav_hover_text: "var(--color-primary)",
                    dropdown_bg: "var(--color-surface)",
                    dropdown_border: "var(--color-border)",
                    dropdown_shadow: normalizeValue(tokens.shadow_md) || "var(--shadow-md)",
                    dropdown_link: "var(--color-text)",
                    dropdown_hover_bg: "var(--color-surface-alt)",
                    dropdown_hover_text: "var(--color-primary)",
                    badge_bg: "var(--qiling-gradient-accent)",
                    badge_text: "#ffffff",
                    badge_border: "var(--qiling-component-badge-bg)",
                    tabs_border: "var(--color-border)",
                    tabs_text: "var(--color-text-muted)",
                    tabs_active_bg: "var(--qiling-gradient-brand-soft)",
                    tabs_active_text: "var(--color-primary)",
                    tabs_active_border: "var(--color-primary)",
                    accordion_bg: "var(--color-surface)",
                    accordion_border: "var(--color-border)",
                    accordion_title: "var(--color-heading)",
                    pagination_bg: "var(--color-surface)",
                    pagination_border: "var(--color-border)",
                    pagination_text: "var(--color-text)",
                    pagination_active_bg: "var(--qiling-gradient-brand)",
                    pagination_active_text: "#ffffff",
                    breadcrumb_bg: "var(--color-surface)",
                    breadcrumb_text: "var(--color-text-muted)",
                    breadcrumb_link: "var(--color-heading)",
                    alert_bg: "var(--color-surface-alt)",
                    alert_border: "var(--color-border)",
                    alert_text: "var(--color-text)",
                    modal_bg: "var(--color-surface)",
                    modal_border: "var(--color-border)",
                    modal_shadow: normalizeValue(tokens.shadow_lg) || "var(--shadow-lg)",
                    modal_title: "var(--color-heading)",
                    sidebar_bg: "var(--color-surface)",
                    sidebar_border: "var(--color-border)",
                    sidebar_shadow: normalizeValue(tokens.shadow_md) || "var(--shadow-md)",
                    sidebar_title: "var(--color-heading)",
                    footer_bg: "var(--color-neutral-900)",
                    footer_text: "rgba(255, 255, 255, 0.78)",
                    footer_link: "rgba(255, 255, 255, 0.72)",
                    footer_link_hover: "#ffffff",
                    footer_bottom_bg: "rgba(2, 6, 23, 0.82)",
                    woo_card_bg: "var(--color-surface)",
                    woo_card_border: "var(--color-border)",
                    woo_card_shadow: normalizeValue(tokens.shadow_md) || "var(--shadow-md)",
                    woo_card_title: "var(--color-heading)",
                    woo_card_price: "var(--color-primary)",
                    dark_card_bg: "var(--qiling-dark-surface)",
                    dark_card_border: "var(--qiling-dark-border)",
                    dark_form_input_bg: "var(--qiling-dark-surface)",
                    dark_form_input_text: "var(--qiling-dark-text)",
                    dark_form_input_border: "var(--qiling-dark-border)",
                    dark_module_title_color: "var(--qiling-dark-text)",
                    dark_post_card_bg: "var(--qiling-dark-surface)",
                    dark_post_card_border: "var(--qiling-dark-border)",
                    dark_post_card_title_color: "var(--qiling-dark-text)",
                    dark_post_card_meta_color: "var(--qiling-dark-text-muted)"
                };
            }
            function collectComponentStyles(tokens) {
                var styles = buildDefaultComponentStyles(tokens);
                var currentPreset = getCurrentPreset();
                if (currentPreset.data && currentPreset.data.componentStyles && typeof currentPreset.data.componentStyles === "object") {
                    styles = Object.assign(styles, currentPreset.data.componentStyles);
                }
                Object.keys(componentOptionMap).forEach(function(optionId){
                    var input = document.getElementById(optionId);
                    if (!input) {
                        return;
                    }
                    var value = getElementValue(input);
                    if (!hasValue(value)) {
                        return;
                    }
                    styles[componentOptionMap[optionId]] = value;
                });
                return styles;
            }
            function buildCssVariables(tokens, componentStyles) {
                var variables = Object.assign({}, payload.cssVariables || {}, payload.componentCssVariables || {});
                tokenDefinitions.forEach(function(definition){
                    if (!definition || !definition.cssVar || !definition.key) {
                        return;
                    }
                    if (!hasValue(tokens[definition.key])) {
                        return;
                    }
                    variables[definition.cssVar] = String(tokens[definition.key]);
                });
                variables["--color-primary-dark"] = normalizeValue(tokens.primary_dark);
                variables["--color-primary-light"] = normalizeValue(tokens.primary_light);
                variables["--color-primary-rgb"] = normalizeValue(tokens.primary_rgb);
                variables["--color-accent-dark"] = normalizeValue(tokens.accent_dark);
                variables["--color-accent-rgb"] = normalizeValue(tokens.accent_rgb);
                variables["--color-success-dark"] = normalizeValue(tokens.success_dark);
                variables["--color-success-rgb"] = normalizeValue(tokens.success_rgb);
                variables["--color-info-dark"] = normalizeValue(tokens.info_dark);
                variables["--color-info-rgb"] = normalizeValue(tokens.info_rgb);
                variables["--color-warning-dark"] = normalizeValue(tokens.warning_dark);
                variables["--color-warning-rgb"] = normalizeValue(tokens.warning_rgb);
                variables["--color-error-dark"] = normalizeValue(tokens.error_dark);
                variables["--color-error-rgb"] = normalizeValue(tokens.error_rgb);
                variables["--color-neutral-400-rgb"] = normalizeValue(tokens.neutral_400_rgb);
                variables["--qiling-dark-bg"] = normalizeValue(tokens.dark_bg);
                variables["--qiling-dark-surface"] = normalizeValue(tokens.dark_surface);
                variables["--qiling-dark-text"] = normalizeValue(tokens.dark_text);
                variables["--qiling-dark-text-muted"] = normalizeValue(tokens.dark_text_muted);
                variables["--qiling-dark-border"] = normalizeValue(tokens.dark_border);
                variables["--shadow-sm"] = normalizeValue(tokens.shadow_sm);
                variables["--shadow-md"] = normalizeValue(tokens.shadow_md);
                variables["--shadow-lg"] = normalizeValue(tokens.shadow_lg);
                variables["--qiling-gradient-brand"] = "linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%)";
                variables["--qiling-gradient-brand-soft"] = "linear-gradient(135deg, rgba(var(--color-primary-rgb), 0.12) 0%, rgba(var(--color-primary-rgb), 0.04) 100%)";
                variables["--qiling-gradient-accent"] = "linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-dark) 100%)";
                variables["--qiling-gradient-success"] = "linear-gradient(135deg, var(--color-success) 0%, var(--color-success-dark) 100%)";
                variables["--qiling-gradient-info"] = "linear-gradient(135deg, var(--color-info) 0%, var(--color-info-dark) 100%)";
                variables["--qiling-gradient-warning"] = "linear-gradient(135deg, var(--color-warning) 0%, var(--color-warning-dark) 100%)";
                variables["--qiling-gradient-error"] = "linear-gradient(135deg, var(--color-error) 0%, var(--color-error-dark) 100%)";
                componentDefinitions.forEach(function(definition){
                    if (!definition || !definition.cssVar || !definition.key) {
                        return;
                    }
                    if (!hasValue(componentStyles[definition.key])) {
                        return;
                    }
                    variables[definition.cssVar] = String(componentStyles[definition.key]);
                });
                return variables;
            }
            function buildDiagnostics(state) {
                var variables = state.variables;
                var contrastItems = [];
                var contrastWarnings = 0;
                var contrastConfig = Array.isArray(seed.contrastDiagnostics) ? seed.contrastDiagnostics : [];
                contrastConfig.forEach(function(item){
                    var foregroundValue = item && item.fg && item.fg.type === "component"
                        ? state.componentStyles[item.fg.key]
                        : state.tokens[item && item.fg ? item.fg.key : ""];
                    var backgroundValue = item && item.bg && item.bg.type === "component"
                        ? state.componentStyles[item.bg.key]
                        : state.tokens[item && item.bg ? item.bg.key : ""];
                    var foreground = resolveColorValue(foregroundValue, variables);
                    var background = resolveColorValue(backgroundValue, variables);
                    var ratio = foreground && background ? calculateContrastRatio(foreground, background) : null;
                    var status = "unknown";
                    var message = messages.contrastUnknown || "";
                    if (ratio !== null) {
                        status = ratio >= 4.5 ? "pass" : "warning";
                        message = ratio >= 4.5 ? (messages.contrastPass || "") : (messages.contrastWarning || "");
                        if (status === "warning") {
                            contrastWarnings += 1;
                        }
                    }
                    contrastItems.push({
                        key: item.key,
                        label: item.label,
                        status: status,
                        ratio: ratio !== null ? Math.round(ratio * 100) / 100 : null,
                        message: message
                    });
                });

                var hardcodedComponents = [];
                Object.keys(state.componentStyles).forEach(function(styleKey){
                    var definitionType = componentSchema[styleKey];
                    if (["color", "paint"].indexOf(definitionType) === -1) {
                        return;
                    }
                    var styleValue = normalizeValue(state.componentStyles[styleKey]);
                    if (!styleValue || isVariableDrivenDesignValue(styleValue) || !containsLiteralDesignColor(styleValue)) {
                        return;
                    }
                    hardcodedComponents.push({
                        key: styleKey,
                        label: componentDefinitionMap[styleKey] && componentDefinitionMap[styleKey].label ? componentDefinitionMap[styleKey].label : styleKey,
                        value: styleValue
                    });
                });

                var darkItems = [];
                var darkWarnings = 0;
                var mappedDarkKeys = [];
                (Array.isArray(seed.darkModeDiagnostics) ? seed.darkModeDiagnostics : []).forEach(function(pair){
                    var lightKey = pair.light;
                    var darkKey = pair.dark;
                    var lightValue = normalizeValue(state.componentStyles[lightKey]);
                    var darkValue = normalizeValue(state.componentStyles[darkKey]);
                    mappedDarkKeys.push(lightKey, darkKey);
                    var isVariableDriven = isVariableDrivenDesignValue(lightValue) && isVariableDrivenDesignValue(darkValue);
                    var hasExplicitDark = hasValue(darkValue) && darkValue !== lightValue;
                    var status = isVariableDriven || hasExplicitDark ? "pass" : "warning";
                    if (status === "warning") {
                        darkWarnings += 1;
                    }
                    darkItems.push({
                        key: lightKey,
                        label: componentDefinitionMap[lightKey] && componentDefinitionMap[lightKey].label ? componentDefinitionMap[lightKey].label : lightKey,
                        status: status,
                        message: status === "pass" ? (messages.darkPassMessage || "") : (messages.darkWarningMessage || "")
                    });
                });
                Object.keys(state.componentStyles).forEach(function(styleKey){
                    var styleValue = normalizeValue(state.componentStyles[styleKey]);
                    if (!styleValue || styleKey.indexOf("dark_") === 0 || mappedDarkKeys.indexOf(styleKey) !== -1) {
                        return;
                    }
                    var definitionType = componentSchema[styleKey];
                    if (["color", "paint"].indexOf(definitionType) === -1 || isVariableDrivenDesignValue(styleValue) || !containsLiteralDesignColor(styleValue)) {
                        return;
                    }
                    darkWarnings += 1;
                    darkItems.push({
                        key: styleKey,
                        label: componentDefinitionMap[styleKey] && componentDefinitionMap[styleKey].label ? componentDefinitionMap[styleKey].label : styleKey,
                        status: "warning",
                        message: messages.darkLiteralWarning || ""
                    });
                });

                return {
                    summary: {
                        contrastWarnings: contrastWarnings,
                        hardcodedCount: hardcodedComponents.length,
                        darkModeWarnings: darkWarnings
                    },
                    contrast: contrastItems,
                    hardcodedComponents: hardcodedComponents,
                    darkMode: darkItems
                };
            }
            function renderSwatches(state) {
                var container = root.querySelector("[data-ds-workbench-swatches='1']");
                if (!container) {
                    return;
                }
                var presetTokens = state.preset.data && state.preset.data.tokens ? state.preset.data.tokens : {};
                var swatches = seed.swatches && typeof seed.swatches === "object" ? seed.swatches : {};
                container.innerHTML = Object.keys(swatches).map(function(key){
                    var value = normalizeValue(state.tokens[key]);
                    var resolved = resolveStyleValue(value, state.variables, 0);
                    var isOverride = normalizeValue(value) !== normalizeValue(presetTokens[key]);
                    return '<article class="ds-design-workbench__swatch">'
                        + '<span class="ds-design-workbench__swatch-chip" style="background:' + escapeHtml(resolved || "transparent") + '"></span>'
                        + '<div>'
                        + '<strong>' + escapeHtml(swatches[key]) + '</strong>'
                        + '<code>' + escapeHtml(value || (messages.unknownValue || "-")) + '</code>'
                        + '<span class="ds-design-workbench__badge ' + (isOverride ? 'is-override' : 'is-inherit') + '">' + escapeHtml(isOverride ? (messages.siteOverride || "") : (messages.presetInherit || "")) + '</span>'
                        + '</div>'
                        + '</article>';
                }).join("");
            }
            function renderTypographySummary(state) {
                var container = root.querySelector("[data-ds-workbench-typography-summary='1']");
                if (!container) {
                    return;
                }
                var summary = seed.typographySummary && typeof seed.typographySummary === "object" ? seed.typographySummary : {};
                var defaults = state.defaults && state.defaults.typography ? state.defaults.typography : {};
                container.innerHTML = Object.keys(summary).map(function(styleKey){
                    var currentStyle = state.typographySystem[styleKey] && typeof state.typographySystem[styleKey] === "object" ? state.typographySystem[styleKey] : {};
                    var baseStyle = defaults[styleKey] && typeof defaults[styleKey] === "object" ? defaults[styleKey] : {};
                    var diffCount = countNestedDifference(currentStyle, baseStyle);
                    return '<article class="ds-design-workbench__metric-card">'
                        + '<div class="ds-design-workbench__metric-head">'
                        + '<strong>' + escapeHtml(summary[styleKey]) + '</strong>'
                        + '<span class="ds-design-workbench__badge ' + (diffCount > 0 ? 'is-override' : 'is-inherit') + '">' + escapeHtml(diffCount > 0 ? (messages.siteOverride || "") : (messages.baseDefault || "")) + '</span>'
                        + '</div>'
                        + '<code>' + escapeHtml((messages.desktopPrefix || "Desktop: ") + formatTypographyMeta(currentStyle.desktop || {})) + '</code>'
                        + '<code>' + escapeHtml((messages.mobilePrefix || "Mobile: ") + formatTypographyMeta(currentStyle.mobile || {})) + '</code>'
                        + '</article>';
                }).join("");
            }
            function renderLayoutSummary(state) {
                var container = root.querySelector("[data-ds-workbench-layout-summary='1']");
                if (!container) {
                    return;
                }
                var defaults = state.defaults && state.defaults.layout ? state.defaults.layout : {};
                var items = [
                    {
                        label: payload.layoutDefinitions && payload.layoutDefinitions.container_width ? payload.layoutDefinitions.container_width.label : "容器宽度",
                        value: [state.layoutSystem.container_width && state.layoutSystem.container_width.desktop, state.layoutSystem.container_width && state.layoutSystem.container_width.tablet, state.layoutSystem.container_width && state.layoutSystem.container_width.mobile].filter(Boolean).join(" / "),
                        diff: countNestedDifference(state.layoutSystem.container_width || {}, defaults.container_width || {})
                    },
                    {
                        label: payload.layoutDefinitions && payload.layoutDefinitions.section_spacing ? payload.layoutDefinitions.section_spacing.label : "区块上下间距",
                        value: [state.layoutSystem.section_spacing && state.layoutSystem.section_spacing.desktop, state.layoutSystem.section_spacing && state.layoutSystem.section_spacing.tablet, state.layoutSystem.section_spacing && state.layoutSystem.section_spacing.mobile].filter(Boolean).join(" / "),
                        diff: countNestedDifference(state.layoutSystem.section_spacing || {}, defaults.section_spacing || {})
                    },
                    {
                        label: payload.layoutDefinitions && payload.layoutDefinitions.grid_gap ? payload.layoutDefinitions.grid_gap.label : "栅格间距",
                        value: [state.layoutSystem.grid_gap && state.layoutSystem.grid_gap.desktop, state.layoutSystem.grid_gap && state.layoutSystem.grid_gap.tablet, state.layoutSystem.grid_gap && state.layoutSystem.grid_gap.mobile].filter(Boolean).join(" / "),
                        diff: countNestedDifference(state.layoutSystem.grid_gap || {}, defaults.grid_gap || {})
                    },
                    {
                        label: payload.layoutDefinitions && payload.layoutDefinitions.breakpoints ? payload.layoutDefinitions.breakpoints.label : "响应断点",
                        value: [
                            state.layoutSystem.breakpoints && state.layoutSystem.breakpoints.tablet ? (messages.tabletPrefix || "Tablet ") + state.layoutSystem.breakpoints.tablet : "",
                            state.layoutSystem.breakpoints && state.layoutSystem.breakpoints.mobile ? (messages.mobileLabel || "Mobile ") + state.layoutSystem.breakpoints.mobile : ""
                        ].filter(Boolean).join(" / "),
                        diff: countNestedDifference(state.layoutSystem.breakpoints || {}, defaults.breakpoints || {})
                    },
                    {
                        label: payload.layoutDefinitions && payload.layoutDefinitions.layout_mode ? payload.layoutDefinitions.layout_mode.label : "布局模式",
                        value: normalizeValue(state.layoutSystem.layout_mode),
                        diff: normalizeValue(state.layoutSystem.layout_mode) === normalizeValue(defaults.layout_mode) ? 0 : 1
                    }
                ];
                container.innerHTML = items.map(function(item){
                    return '<article class="ds-design-workbench__metric-card">'
                        + '<div class="ds-design-workbench__metric-head">'
                        + '<strong>' + escapeHtml(item.label) + '</strong>'
                        + '<span class="ds-design-workbench__badge ' + (item.diff > 0 ? 'is-override' : 'is-inherit') + '">' + escapeHtml(item.diff > 0 ? (messages.siteOverride || "") : (messages.baseDefault || "")) + '</span>'
                        + '</div>'
                        + '<code>' + escapeHtml(item.value || (messages.unknownValue || "-")) + '</code>'
                        + '</article>';
                }).join("");
            }
            function renderComponentSummary(state) {
                var container = root.querySelector("[data-ds-workbench-component-summary='1']");
                if (!container) {
                    return;
                }
                var defaults = state.defaults && state.defaults.components ? state.defaults.components : buildDefaultComponentStyles(state.tokens);
                var summary = Array.isArray(seed.componentSummary) ? seed.componentSummary : [];
                container.innerHTML = summary.map(function(item){
                    var currentGroup = {};
                    var defaultGroup = {};
                    (item.sourceKeys || []).forEach(function(key){
                        currentGroup[key] = state.componentStyles[key] || "";
                        defaultGroup[key] = defaults[key] || "";
                    });
                    var diffCount = countNestedDifference(currentGroup, defaultGroup);
                    var background = resolveStyleValue(state.componentStyles[item.bgKey] || "", state.variables, 0);
                    var textColor = resolveStyleValue(state.componentStyles[item.textKey] || "", state.variables, 0);
                    return '<article class="ds-design-workbench__component-card">'
                        + '<div class="ds-design-workbench__component-head">'
                        + '<strong>' + escapeHtml(item.label) + '</strong>'
                        + '<span class="ds-design-workbench__badge ' + (diffCount > 0 ? 'is-override' : 'is-inherit') + '">' + escapeHtml(diffCount > 0 ? (item.overrideLabel || messages.siteOverride || "") : (item.inheritLabel || messages.varInherit || "")) + '</span>'
                        + '</div>'
                        + '<div class="ds-design-workbench__component-demo" style="background:' + escapeHtml(background || "transparent") + ';color:' + escapeHtml(textColor || "inherit") + ';">' + escapeHtml(item.label) + '</div>'
                        + '<code>' + escapeHtml(normalizeValue(state.componentStyles[item.metaKey]) || (messages.unknownValue || "-")) + '</code>'
                        + '</article>';
                }).join("");
            }
            function buildImpactTagsHtml(item) {
                var impactLevel = buildImpactLevel(item && item.score);
                var scopes = Array.isArray(item && item.scopes) ? item.scopes.slice(0, 4) : [];
                var fragments = [
                    '<span class="ds-design-workbench__badge ' + escapeHtml(impactLevel.tone) + '">' + escapeHtml(impactLevel.label) + '</span>'
                ];
                scopes.forEach(function(scope){
                    fragments.push('<span class="ds-design-workbench__badge is-info">' + escapeHtml(scope) + '</span>');
                });
                return '<div class="ds-design-workbench__impact-tags">' + fragments.join("") + '</div>';
            }
            function buildFocusButtonHtml(item) {
                if (!item || !item.selector) {
                    return "";
                }
                return '<a class="ds-design-workbench__focus-btn" href="' + escapeHtml(extractFocusHref(item.selector)) + '" data-ds-workbench-focus-selector="' + escapeHtml(item.selector) + '">' + escapeHtml(messages.focusField || "定位设置") + '</a>';
            }
            function buildRiskZoneSummary(overrideGroups, missingItems, diagnostics) {
                var overrideList = [];
                (Array.isArray(overrideGroups) ? overrideGroups : []).forEach(function(group){
                    (group && Array.isArray(group.items) ? group.items : []).forEach(function(item){
                        overrideList.push(attachRiskZones(Object.assign({ kind: "override" }, item)));
                    });
                });
                var missingList = (Array.isArray(missingItems) ? missingItems : []).map(function(item){
                    return attachRiskZones(Object.assign({ kind: "missing" }, item));
                });
                var diagnosticList = []
                    .concat((Array.isArray(diagnostics && diagnostics.contrast) ? diagnostics.contrast : []).map(function(item){
                        return attachRiskZones(Object.assign({ kind: "contrast" }, item));
                    }))
                    .concat((Array.isArray(diagnostics && diagnostics.hardcodedComponents) ? diagnostics.hardcodedComponents : []).map(function(item){
                        return attachRiskZones(Object.assign({ kind: "hardcoded" }, item));
                    }))
                    .concat((Array.isArray(diagnostics && diagnostics.darkMode) ? diagnostics.darkMode : []).map(function(item){
                        return attachRiskZones(Object.assign({ kind: "dark" }, item));
                    }));

                return riskZoneDefinitions.map(function(definition){
                    var overrideCount = overrideList.filter(function(item){
                        return item.zoneKeys.indexOf(definition.key) !== -1;
                    }).length;
                    var missingCount = missingList.filter(function(item){
                        return item.zoneKeys.indexOf(definition.key) !== -1;
                    }).length;
                    var diagnosticCount = diagnosticList.filter(function(item){
                        return item.zoneKeys.indexOf(definition.key) !== -1;
                    }).length;

                    return {
                        key: definition.key,
                        label: definition.label,
                        description: definition.description,
                        overrideCount: overrideCount,
                        missingCount: missingCount,
                        diagnosticCount: diagnosticCount,
                        total: overrideCount + missingCount + diagnosticCount
                    };
                });
            }
            function renderRiskZones(state, diagnostics) {
                var container = root.querySelector("[data-ds-workbench-risk-zones='1']");
                if (!container) {
                    return;
                }
                var statusNode = root.querySelector("[data-ds-workbench-risk-status='1']");
                var zones = Array.isArray(state.riskZones)
                    ? state.riskZones
                    : buildRiskZoneSummary(state.overrideGroups || [], state.missingDiagnostics || [], diagnostics);

                if (statusNode) {
                    statusNode.textContent = getRiskFilterStatusText();
                }
                container.innerHTML = zones.map(function(zone){
                    var totalBadgeClass = zone.total > 0 ? "is-warning" : "is-success";
                    var activeClass = activeRiskZone === zone.key ? " is-active" : "";
                    return '<button type="button" class="ds-design-workbench__risk-card' + activeClass + '" data-ds-workbench-risk-zone="' + escapeHtml(zone.key) + '">'
                        + '<div class="ds-design-workbench__risk-card-head">'
                        + '<strong>' + escapeHtml(zone.label) + '</strong>'
                        + '<span class="ds-design-workbench__badge ' + totalBadgeClass + '">' + escapeHtml(formatCount(zone.total)) + '</span>'
                        + '</div>'
                        + '<p>' + escapeHtml(zone.description) + '</p>'
                        + '<code>' + escapeHtml(formatTemplate(messages.riskBreakdown || "覆盖 %1$d / 缺项 %2$d / 诊断 %3$d", [
                            zone.overrideCount,
                            zone.missingCount,
                            zone.diagnosticCount
                        ])) + '</code>'
                        + '</button>';
                }).join("");

                root.querySelectorAll("[data-ds-workbench-risk-zone]").forEach(function(node){
                    var zoneKey = normalizeValue(node.getAttribute("data-ds-workbench-risk-zone")) || "all";
                    node.classList.toggle("is-active", zoneKey === activeRiskZone);
                });
            }
            function renderOverrideGroups(state) {
                var container = root.querySelector("[data-ds-workbench-override-groups='1']");
                if (!container) {
                    return;
                }
                var groups = Array.isArray(state.overrideGroups) ? state.overrideGroups : buildOverrideGroups(state);
                if (activeRiskZone && activeRiskZone !== "all") {
                    groups = groups.map(function(group){
                        return Object.assign({}, group, {
                            items: filterItemsByRiskZone(group.items, activeRiskZone)
                        });
                    }).filter(function(group){
                        return group.items.length > 0;
                    });
                    if (!groups.length) {
                        container.innerHTML = '<p class="ds-design-workbench__empty">' + escapeHtml(formatTemplate(messages.riskEmpty || "当前分区没有对应项目：%s。", [getActiveRiskZoneLabel()])) + '</p>';
                        return;
                    }
                }
                container.innerHTML = groups.map(function(group){
                    var visibleItems = group.items.slice(0, 6);
                    var moreCount = Math.max(0, group.items.length - visibleItems.length);
                    var body = visibleItems.length
                        ? '<div class="ds-design-workbench__audit-items">' + visibleItems.map(function(item){
                            return '<article class="ds-design-workbench__audit-item">'
                                + '<div class="ds-design-workbench__audit-item-head">'
                                + '<strong>' + escapeHtml(item.label) + '</strong>'
                                + '<div class="ds-design-workbench__audit-item-actions">'
                                + '<span class="ds-design-workbench__badge is-override">' + escapeHtml(messages.siteOverride || "") + '</span>'
                                + buildFocusButtonHtml(item)
                                + '</div>'
                                + '</div>'
                                + buildImpactTagsHtml(item)
                                + '<code>' + escapeHtml((messages.overrideCurrent || "当前：") + (item.current || (messages.unknownValue || "-"))) + '</code>'
                                + '<code>' + escapeHtml((messages.overrideBase || "基准：") + (item.base || (messages.unknownValue || "-"))) + '</code>'
                                + '</article>';
                        }).join("") + '</div>'
                        : '<p class="ds-design-workbench__empty">' + escapeHtml(messages.overrideGroupClean || "") + '</p>';
                    if (moreCount > 0) {
                        body += '<p class="ds-design-workbench__empty">' + escapeHtml(formatTemplate(messages.overrideMore || "其余 %d 项可在对应设置区继续查看。", [moreCount])) + '</p>';
                    }
                    return '<section class="ds-design-workbench__audit-group">'
                        + '<div class="ds-design-workbench__audit-group-head">'
                        + '<strong>' + escapeHtml(group.label) + '</strong>'
                        + '<span class="ds-design-workbench__badge ' + (group.items.length > 0 ? 'is-override' : 'is-inherit') + '">' + escapeHtml(formatOverrideCount(group.items.length)) + '</span>'
                        + '</div>'
                        + body
                        + '</section>';
                }).join("");
            }
            function renderMissingDiagnostics(state) {
                var container = root.querySelector("[data-ds-workbench-missing-body='1']");
                if (!container) {
                    return;
                }
                var items = Array.isArray(state.missingDiagnostics) ? state.missingDiagnostics : buildMissingDiagnostics(state);
                if (activeRiskZone && activeRiskZone !== "all") {
                    items = filterItemsByRiskZone(items, activeRiskZone);
                }
                if (!items.length) {
                    container.innerHTML = '<p class="ds-design-workbench__empty">' + escapeHtml(activeRiskZone && activeRiskZone !== "all" ? formatTemplate(messages.riskEmpty || "当前分区没有对应项目：%s。", [getActiveRiskZoneLabel()]) : (messages.missingClean || "")) + '</p>';
                    return;
                }
                container.innerHTML = '<div class="ds-design-workbench__diagnostic-list">'
                    + items.slice(0, 10).map(function(item){
                        return '<article class="ds-design-workbench__diagnostic-card">'
                            + '<div class="ds-design-workbench__metric-head">'
                            + '<strong>' + escapeHtml(item.label) + '</strong>'
                            + '<div class="ds-design-workbench__audit-item-actions">'
                            + '<span class="ds-design-workbench__badge is-warning">' + escapeHtml(messages.missingWarn || "") + '</span>'
                            + buildFocusButtonHtml(item)
                            + '</div>'
                            + '</div>'
                            + buildImpactTagsHtml(item)
                            + '<code>' + escapeHtml(item.scope) + '</code>'
                            + '<p>' + escapeHtml(item.message) + '</p>'
                            + '</article>';
                    }).join("")
                    + '</div>';
            }
            function renderContrastList(diagnostics) {
                var container = root.querySelector("[data-ds-workbench-contrast-list='1']");
                if (!container) {
                    return;
                }
                var items = filterItemsByRiskZone(diagnostics.contrast || [], activeRiskZone);
                if (!items.length) {
                    container.innerHTML = '<p class="ds-design-workbench__empty">' + escapeHtml(activeRiskZone && activeRiskZone !== "all" ? formatTemplate(messages.riskEmpty || "当前分区没有对应项目：%s。", [getActiveRiskZoneLabel()]) : (messages.manualReview || "")) + '</p>';
                    return;
                }
                container.innerHTML = items.slice(0, 8).map(function(item){
                    var badgeClass = item.status === "warning" ? "is-warning" : (item.status === "pass" ? "is-success" : "is-info");
                    var badgeLabel = item.ratio !== null ? (messages.ratioPrefix || "Ratio ") + Number(item.ratio).toFixed(2) : (messages.manualReview || "");
                    return '<article class="ds-design-workbench__diagnostic-card">'
                        + '<div class="ds-design-workbench__metric-head">'
                        + '<strong>' + escapeHtml(item.label) + '</strong>'
                        + '<span class="ds-design-workbench__badge ' + badgeClass + '">' + escapeHtml(badgeLabel) + '</span>'
                        + '</div>'
                        + '<p>' + escapeHtml(item.message) + '</p>'
                        + '</article>';
                }).join("");
            }
            function renderHardcodedList(diagnostics) {
                var container = root.querySelector("[data-ds-workbench-hardcoded-body='1']");
                if (!container) {
                    return;
                }
                var items = filterItemsByRiskZone(diagnostics.hardcodedComponents || [], activeRiskZone);
                if (!items.length) {
                    container.innerHTML = '<p class="ds-design-workbench__empty">' + escapeHtml(activeRiskZone && activeRiskZone !== "all" ? formatTemplate(messages.riskEmpty || "当前分区没有对应项目：%s。", [getActiveRiskZoneLabel()]) : (messages.hardcodedClean || "")) + '</p>';
                    return;
                }
                container.innerHTML = '<div class="ds-design-workbench__diagnostic-list">'
                    + items.slice(0, 8).map(function(item){
                        return '<article class="ds-design-workbench__diagnostic-card">'
                            + '<div class="ds-design-workbench__metric-head">'
                            + '<strong>' + escapeHtml(item.label) + '</strong>'
                            + '<span class="ds-design-workbench__badge is-info">' + escapeHtml(messages.hardcodedWarn || "") + '</span>'
                            + '</div>'
                            + '<code>' + escapeHtml(item.value) + '</code>'
                            + '</article>';
                    }).join("")
                    + '</div>';
            }
            function renderDarkList(diagnostics) {
                var container = root.querySelector("[data-ds-workbench-dark-list='1']");
                if (!container) {
                    return;
                }
                var items = filterItemsByRiskZone(diagnostics.darkMode || [], activeRiskZone);
                if (!items.length) {
                    container.innerHTML = '<p class="ds-design-workbench__empty">' + escapeHtml(activeRiskZone && activeRiskZone !== "all" ? formatTemplate(messages.riskEmpty || "当前分区没有对应项目：%s。", [getActiveRiskZoneLabel()]) : (messages.darkPassMessage || "")) + '</p>';
                    return;
                }
                container.innerHTML = items.slice(0, 8).map(function(item){
                    return '<article class="ds-design-workbench__diagnostic-card">'
                        + '<div class="ds-design-workbench__metric-head">'
                        + '<strong>' + escapeHtml(item.label) + '</strong>'
                        + '<span class="ds-design-workbench__badge is-info">' + escapeHtml(item.status === "warning" ? (messages.darkWarning || "") : (messages.darkPass || "")) + '</span>'
                        + '</div>'
                        + '<p>' + escapeHtml(item.message) + '</p>'
                        + '</article>';
                }).join("");
            }
            var highlightedFieldRow = null;
            function clearHighlightedFieldRow() {
                if (!highlightedFieldRow) {
                    return;
                }
                highlightedFieldRow.classList.remove("ds-workbench-field-highlight");
                highlightedFieldRow = null;
            }
            function resolveSelectorTarget(selector) {
                var candidates = [];
                try {
                    candidates = selector ? Array.prototype.slice.call(document.querySelectorAll(selector)) : [];
                } catch (error) {
                    candidates = [];
                }
                if (!candidates.length) {
                    return null;
                }
                var visibleTarget = candidates.find(function(node){
                    return !!(node && (node.offsetWidth || node.offsetHeight || node.getClientRects().length));
                });
                return visibleTarget || candidates[0];
            }
            function findScrollableAncestor(node) {
                var current = node && node.parentElement ? node.parentElement : null;
                while (current && current !== document.body) {
                    var style = window.getComputedStyle ? window.getComputedStyle(current) : null;
                    var overflowY = style ? String(style.overflowY || "") : "";
                    var overflow = style ? String(style.overflow || "") : "";
                    var isScrollable = /(auto|scroll|overlay)/.test(overflowY) || /(auto|scroll|overlay)/.test(overflow);
                    if (isScrollable && current.scrollHeight > current.clientHeight + 4) {
                        return current;
                    }
                    current = current.parentElement;
                }
                return null;
            }
            function focusFieldBySelector(selector) {
                var target = resolveSelectorTarget(selector);
                if (!target) {
                    return;
                }
                var fieldRow = target.matches && target.matches("tr") ? target : target.closest("tr");
                var focusTarget = target;
                if (target.matches && !target.matches("input, select, textarea, button, [tabindex]")) {
                    var nestedTarget = target.querySelector("input, select, textarea, button, [tabindex]");
                    if (nestedTarget) {
                        focusTarget = nestedTarget;
                    }
                }
                if (fieldRow) {
                    clearHighlightedFieldRow();
                    highlightedFieldRow = fieldRow;
                    fieldRow.classList.add("ds-workbench-field-highlight");
                    window.setTimeout(function(){
                        if (highlightedFieldRow === fieldRow) {
                            clearHighlightedFieldRow();
                        }
                    }, 2200);
                }
                var scrollTarget = fieldRow || focusTarget;
                var scrollContainer = findScrollableAncestor(scrollTarget);
                if (scrollContainer && scrollTarget && scrollTarget.getBoundingClientRect && scrollContainer.getBoundingClientRect) {
                    var containerRect = scrollContainer.getBoundingClientRect();
                    var targetRect = scrollTarget.getBoundingClientRect();
                    var nextScrollTop = scrollContainer.scrollTop + (targetRect.top - containerRect.top) - 24;
                    if (typeof scrollContainer.scrollTo === "function") {
                        scrollContainer.scrollTo({
                            top: Math.max(0, nextScrollTop),
                            behavior: "smooth"
                        });
                    } else {
                        scrollContainer.scrollTop = Math.max(0, nextScrollTop);
                    }
                }
                if (scrollTarget && scrollTarget.getBoundingClientRect) {
                    var targetTop = window.pageYOffset + scrollTarget.getBoundingClientRect().top - 120;
                    if (typeof window.scrollTo === "function") {
                        window.scrollTo({
                            top: Math.max(0, targetTop),
                            behavior: "smooth"
                        });
                    } else if (typeof scrollTarget.scrollIntoView === "function") {
                        scrollTarget.scrollIntoView({
                            behavior: "smooth",
                            block: "center",
                            inline: "nearest"
                        });
                    }
                }
                if (focusTarget && typeof focusTarget.focus === "function") {
                    try {
                        focusTarget.focus({ preventScroll: true });
                    } catch (error) {
                        focusTarget.focus();
                    }
                }
            }
            function updateHeadState(state, diagnostics) {
                var enabledField = document.getElementById("design_enable_global_tokens");
                var enabled = !!(enabledField && enabledField.checked);
                var presetLabel = state.preset.data && state.preset.data.label ? state.preset.data.label : state.preset.key;
                var presetSourceLabel = state.preset.data && state.preset.data.source === "custom" ? (messages.customPreset || "") : (messages.systemPreset || "");
                var totalDiff = state.paletteDiffCount + state.typographyDiffCount + state.layoutDiffCount + state.componentDiffCount;
                var titleNode = root.querySelector("[data-ds-workbench-title='1']");
                if (titleNode) {
                    titleNode.textContent = "当前预设：" + presetLabel;
                }
                root.querySelectorAll("[data-ds-workbench-enabled-badge='1']").forEach(function(node){
                    node.textContent = enabled ? (messages.enabled || "") : (messages.disabled || "");
                    node.classList.remove("is-info", "is-warning", "is-success");
                    node.classList.add(enabled ? "is-info" : "is-warning");
                });
                root.querySelectorAll("[data-ds-workbench-preset-source-badge='1']").forEach(function(node){
                    node.textContent = presetSourceLabel;
                    node.classList.remove("is-warning", "is-success", "is-override", "is-inherit");
                    node.classList.add("is-info");
                });
                root.querySelectorAll("[data-ds-workbench-preset-label='1']").forEach(function(node){
                    node.textContent = presetLabel;
                });
                root.querySelectorAll("[data-ds-workbench-contrast-badge='1']").forEach(function(node){
                    node.textContent = diagnostics.summary.contrastWarnings > 0 ? (messages.contrastReview || "") : (messages.contrastNormal || "");
                    node.classList.remove("is-warning", "is-success", "is-info");
                    node.classList.add(diagnostics.summary.contrastWarnings > 0 ? "is-warning" : "is-success");
                });
                root.querySelectorAll("[data-ds-workbench-site-diff-badge='1']").forEach(function(node){
                    node.textContent = formatDiffCount(totalDiff);
                    node.classList.remove("is-warning", "is-success", "is-info");
                    node.classList.add(totalDiff > 0 ? "is-warning" : "is-success");
                });
                root.querySelectorAll("[data-ds-workbench-site-diff-copy='1']").forEach(function(node){
                    node.textContent = formatTemplate(messages.overviewBreakdown || "色板 %1$d / 排版 %2$d / 布局 %3$d / 组件 %4$d", [
                        state.paletteDiffCount,
                        state.typographyDiffCount,
                        state.layoutDiffCount,
                        state.componentDiffCount
                    ]);
                });
                root.querySelectorAll("[data-ds-workbench-summary-card]").forEach(function(node){
                    var key = node.getAttribute("data-ds-workbench-summary-card");
                    var count = diagnostics.summary[key] || 0;
                    node.textContent = formatCount(count);
                    node.classList.remove("is-warning", "is-success", "is-info");
                    node.classList.add(key === "hardcodedCount" || key === "darkModeWarnings" ? "is-info" : (count > 0 ? "is-warning" : "is-success"));
                });
            }
            function applyPreviewState(state) {
                var variables = state.variables;
                var typography = state.typographySystem;
                var layout = state.layoutSystem;
                var components = state.componentStyles;
                var tokens = state.tokens;
                setNodeStyles(getPreviewNode("desktopCanvas"), {
                    background: resolveStyleValue(tokens.background, variables, 0),
                    color: resolveStyleValue(tokens.text, variables, 0)
                });
                var desktopCanvas = getPreviewNode("desktopCanvas");
                if (desktopCanvas) {
                    desktopCanvas.classList.toggle("is-boxed", normalizeValue(layout.layout_mode) === "boxed");
                }
                setNodeStyles(getPreviewNode("desktopHeader"), {
                    background: resolveStyleValue(components.header_bg, variables, 0),
                    borderColor: resolveStyleValue(components.header_border, variables, 0),
                    boxShadow: resolveStyleValue(components.header_shadow, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopBrand"), {
                    color: resolveStyleValue(tokens.heading, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("desktopBrand"), ((typography.menu || {}).desktop || {}));
                setNodeStyles(getPreviewNode("desktopNavHome"), {
                    color: resolveStyleValue(components.nav_link, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopNavActive"), {
                    background: resolveStyleValue(components.nav_hover_bg, variables, 0),
                    color: resolveStyleValue(components.nav_hover_text, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopNavCase"), {
                    color: resolveStyleValue(components.nav_link, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopPhone"), {
                    background: resolveStyleValue(components.header_phone_bg, variables, 0),
                    color: resolveStyleValue(components.header_phone_text, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("desktopPhone"), ((typography.menu || {}).desktop || {}));
                setNodeStyles(getPreviewNode("desktopMiniBadge"), {
                    background: resolveStyleValue(components.badge_bg, variables, 0),
                    color: resolveStyleValue(components.badge_text, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopHero"), {
                    paddingTop: normalizeValue((layout.section_spacing || {}).desktop),
                    paddingBottom: normalizeValue((layout.section_spacing || {}).desktop)
                });
                setNodeStyles(getPreviewNode("desktopHeroTitle"), {
                    color: resolveStyleValue(tokens.heading, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("desktopHeroTitle"), ((typography.h1 || {}).desktop || {}));
                setNodeStyles(getPreviewNode("desktopHeroLead"), {
                    color: resolveStyleValue(tokens.text_muted, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("desktopHeroLead"), ((typography.lead || {}).desktop || {}));
                setNodeStyles(getPreviewNode("desktopButton"), {
                    background: resolveStyleValue(components.button_bg, variables, 0),
                    color: resolveStyleValue(components.button_text, variables, 0),
                    borderColor: resolveStyleValue(components.button_border, variables, 0),
                    boxShadow: resolveStyleValue(components.button_shadow, variables, 0),
                    padding: resolveStyleValue(components.button_padding, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("desktopButton"), ((typography.button || {}).desktop || {}));
                setNodeStyles(getPreviewNode("desktopGrid"), {
                    gap: normalizeValue((layout.grid_gap || {}).desktop)
                });
                setNodeStyles(getPreviewNode("desktopCardPrimary"), {
                    background: resolveStyleValue(components.card_bg, variables, 0),
                    borderColor: resolveStyleValue(components.card_border, variables, 0),
                    boxShadow: resolveStyleValue(components.card_shadow, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopCardPrimaryTitle"), {
                    color: resolveStyleValue(components.post_card_title_color, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopCardPrimaryBody"), {
                    color: resolveStyleValue(tokens.text, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("desktopCardPrimaryBody"), ((typography.body || {}).desktop || {}));
                setNodeStyles(getPreviewNode("desktopCardPrimaryMeta"), {
                    color: resolveStyleValue(components.post_card_meta_color, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopCardSecondary"), {
                    background: resolveStyleValue(tokens.surface_alt, variables, 0),
                    borderColor: resolveStyleValue(components.tabs_border, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopTabActive"), {
                    background: resolveStyleValue(components.tabs_active_bg, variables, 0),
                    color: resolveStyleValue(components.tabs_active_text, variables, 0),
                    borderColor: resolveStyleValue(components.tabs_active_border, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopAlert"), {
                    background: resolveStyleValue(components.alert_bg, variables, 0),
                    borderColor: resolveStyleValue(components.alert_border, variables, 0),
                    color: resolveStyleValue(components.alert_text, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopInput"), {
                    background: resolveStyleValue(components.form_input_bg, variables, 0),
                    color: resolveStyleValue(components.form_input_text, variables, 0),
                    borderColor: resolveStyleValue(components.form_input_border, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("desktopInput"), ((typography.input || {}).desktop || {}));
                setNodeStyles(getPreviewNode("desktopFooter"), {
                    background: resolveStyleValue(components.footer_bg, variables, 0),
                    color: resolveStyleValue(components.footer_text, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopFooterLink"), {
                    color: resolveStyleValue(components.footer_link, variables, 0)
                });
                setNodeStyles(getPreviewNode("desktopFooterPrice"), {
                    color: resolveStyleValue(components.woo_card_price, variables, 0)
                });

                setNodeStyles(getPreviewNode("mobilePhone"), {
                    background: resolveStyleValue(tokens.background, variables, 0),
                    color: resolveStyleValue(tokens.text, variables, 0)
                });
                setNodeStyles(getPreviewNode("mobilePhoneHead"), {
                    background: resolveStyleValue(components.mobile_nav_bg, variables, 0),
                    borderColor: resolveStyleValue(components.mobile_nav_border, variables, 0)
                });
                setNodeStyles(getPreviewNode("mobilePhoneMenu"), {
                    color: resolveStyleValue(components.mobile_nav_link, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("mobilePhoneMenu"), ((typography.menu || {}).mobile || {}));
                setNodeStyles(getPreviewNode("mobilePhoneTitle"), {
                    color: resolveStyleValue(tokens.heading, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("mobilePhoneTitle"), ((typography.h2 || {}).mobile || {}));
                setNodeStyles(getPreviewNode("mobilePhoneBody"), {
                    color: resolveStyleValue(tokens.text_muted, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("mobilePhoneBody"), ((typography.body || {}).mobile || {}));
                setNodeStyles(getPreviewNode("mobilePhoneBottom"), {
                    background: resolveStyleValue(components.mobile_nav_bg, variables, 0),
                    borderColor: resolveStyleValue(components.mobile_nav_border, variables, 0)
                });
                setNodeStyles(getPreviewNode("mobilePhoneBottomHome"), {
                    color: resolveStyleValue(components.mobile_nav_link, variables, 0)
                });
                setNodeStyles(getPreviewNode("mobilePhoneBottomActive"), {
                    background: resolveStyleValue(components.mobile_nav_hover_bg, variables, 0),
                    color: resolveStyleValue(components.mobile_nav_hover_text, variables, 0)
                });
                setNodeStyles(getPreviewNode("mobilePhoneBottomMine"), {
                    color: resolveStyleValue(components.mobile_nav_link, variables, 0)
                });

                setNodeStyles(getPreviewNode("darkPreview"), {
                    background: resolveStyleValue(tokens.dark_bg, variables, 0),
                    color: resolveStyleValue(tokens.dark_text, variables, 0),
                    borderColor: resolveStyleValue(tokens.dark_border, variables, 0)
                });
                setNodeStyles(getPreviewNode("darkPreviewBody"), {
                    color: resolveStyleValue(tokens.dark_text_muted, variables, 0)
                });
                applyTypographyStyles(getPreviewNode("darkPreviewBody"), ((typography.body || {}).desktop || {}));
                setNodeStyles(getPreviewNode("darkCard"), {
                    background: resolveStyleValue(components.dark_post_card_bg, variables, 0),
                    borderColor: resolveStyleValue(components.dark_post_card_border, variables, 0)
                });
                setNodeStyles(getPreviewNode("darkCardTitle"), {
                    color: resolveStyleValue(components.dark_post_card_title_color, variables, 0)
                });
                setNodeStyles(getPreviewNode("darkCardMeta"), {
                    color: resolveStyleValue(components.dark_post_card_meta_color, variables, 0)
                });
                setNodeStyles(getPreviewNode("darkInput"), {
                    background: resolveStyleValue(components.dark_form_input_bg, variables, 0),
                    color: resolveStyleValue(components.dark_form_input_text, variables, 0),
                    borderColor: resolveStyleValue(components.dark_form_input_border, variables, 0)
                });
            }
            function renderWorkbench() {
                var tokenState = buildTokenState();
                var componentStyles = collectComponentStyles(tokenState.tokens);
                var variables = buildCssVariables(tokenState.tokens, componentStyles);
                var diagnostics = buildDiagnostics({
                    tokens: tokenState.tokens,
                    componentStyles: componentStyles,
                    variables: variables
                });
                var presetTokens = tokenState.preset.data && tokenState.preset.data.tokens ? tokenState.preset.data.tokens : {};
                var presetTypography = tokenState.preset.data && tokenState.preset.data.typographySystem && Object.keys(tokenState.preset.data.typographySystem).length
                    ? tokenState.preset.data.typographySystem
                    : (seed.baseDefaults && seed.baseDefaults.typography ? seed.baseDefaults.typography : {});
                var presetLayout = tokenState.preset.data && tokenState.preset.data.layoutSystem && Object.keys(tokenState.preset.data.layoutSystem).length
                    ? tokenState.preset.data.layoutSystem
                    : (seed.baseDefaults && seed.baseDefaults.layout ? seed.baseDefaults.layout : {});
                var presetComponents = Object.assign(
                    buildDefaultComponentStyles(tokenState.tokens),
                    tokenState.preset.data && tokenState.preset.data.componentStyles && typeof tokenState.preset.data.componentStyles === "object"
                        ? tokenState.preset.data.componentStyles
                        : {}
                );
                var defaults = {
                    typography: cloneData(presetTypography),
                    layout: cloneData(presetLayout),
                    components: cloneData(presetComponents)
                };
                var state = {
                    preset: tokenState.preset,
                    tokens: tokenState.tokens,
                    typographySystem: tokenState.typographySystem,
                    layoutSystem: tokenState.layoutSystem,
                    componentStyles: componentStyles,
                    variables: variables,
                    defaults: defaults,
                    paletteDiffCount: 0,
                    typographyDiffCount: 0,
                    layoutDiffCount: 0,
                    componentDiffCount: 0
                };
                state.overrideGroups = buildOverrideGroups(state);
                state.overrideGroups.forEach(function(group){
                    if (!group || !group.key || !Array.isArray(group.items)) {
                        return;
                    }
                    if (group.key === "palette") {
                        state.paletteDiffCount = group.items.length;
                    } else if (group.key === "typography") {
                        state.typographyDiffCount = group.items.length;
                    } else if (group.key === "layout") {
                        state.layoutDiffCount = group.items.length;
                    } else if (group.key === "component") {
                        state.componentDiffCount = group.items.length;
                    }
                });
                state.missingDiagnostics = buildMissingDiagnostics(state);
                state.riskZones = buildRiskZoneSummary(state.overrideGroups, state.missingDiagnostics, diagnostics);
                updateHeadState(state, diagnostics);
                applyPreviewState(state);
                renderSwatches(state);
                renderTypographySummary(state);
                renderLayoutSummary(state);
                renderComponentSummary(state);
                renderRiskZones(state, diagnostics);
                renderOverrideGroups(state);
                renderMissingDiagnostics(state);
                renderContrastList(diagnostics);
                renderHardcodedList(diagnostics);
                renderDarkList(diagnostics);
            }
            var scheduled = false;
            function scheduleWorkbenchRender() {
                if (scheduled) {
                    return;
                }
                scheduled = true;
                var schedule = window.requestAnimationFrame || function(callback){
                    return window.setTimeout(callback, 16);
                };
                schedule(function(){
                    scheduled = false;
                    renderWorkbench();
                });
            }
            document.addEventListener("input", function(event){
                if (!event.target) {
                    return;
                }
                if (
                    event.target.matches(".ds-color-picker, .regular-text, [data-ds-typography-input='1'], [data-ds-layout-input='1'], [data-token-key], [data-design-preset-field]")
                    || event.target.id === "design_preset"
                ) {
                    scheduleWorkbenchRender();
                }
            });
            document.addEventListener("change", function(event){
                if (!event.target) {
                    return;
                }
                if (
                    event.target.matches("select, input[type='checkbox'], [data-ds-layout-mode='1']")
                    || event.target.id === "design_preset"
                ) {
                    scheduleWorkbenchRender();
                }
            });
            document.addEventListener("click", function(event){
                if (!event.target || !event.target.closest) {
                    return;
                }
                var focusButton = event.target.closest("[data-ds-workbench-focus-selector]");
                if (focusButton) {
                    event.preventDefault();
                    navigateToFocusTarget(focusButton);
                    return;
                }
                var riskButton = event.target.closest("[data-ds-workbench-risk-zone]");
                if (riskButton) {
                    event.preventDefault();
                    activeRiskZone = normalizeValue(riskButton.getAttribute("data-ds-workbench-risk-zone")) || "all";
                    renderWorkbench();
                    return;
                }
                if (event.target.closest(".ds-add-design-preset, .ds-clone-design-preset, .ds-import-design-presets, .ds-replace-design-presets, .ds-remove-design-preset")) {
                    window.setTimeout(scheduleWorkbenchRender, 0);
                }
            }, true);
            renderWorkbench();
        });
        </script>
        <?php
        echo (string) ob_get_clean();

        echo '</td></tr>';
    }
}
