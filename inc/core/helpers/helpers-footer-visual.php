<?php
/**
 * Footer visual decoration helpers.
 *
 * @package Developer_Starter
 * @since 2.5.17
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_sanitize_footer_visual_css_value' ) ) {
    /**
     * Sanitize a CSS value used in footer visual custom properties.
     *
     * @param mixed  $value    Raw CSS value.
     * @param string $fallback Fallback value.
     * @return string
     */
    function developer_starter_sanitize_footer_visual_css_value( $value, $fallback = '' ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value ) {
            return (string) $fallback;
        }

        if ( preg_match( '/[;{}<>]/', $value ) || preg_match( '/(?:expression|javascript\s*:|url\s*\()/i', $value ) ) {
            return (string) $fallback;
        }

        return $value;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_footer_visual_spacing_value' ) ) {
    /**
     * Sanitize a footer spacing value.
     *
     * @param mixed  $value    Raw spacing value.
     * @param string $fallback Fallback value.
     * @return string
     */
    function developer_starter_sanitize_footer_visual_spacing_value( $value, $fallback = '0px' ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value ) {
            return (string) $fallback;
        }

        if ( preg_match( '/[;{}<>]/', $value ) || preg_match( '/(?:expression|javascript\s*:|url\s*\()/i', $value ) ) {
            return (string) $fallback;
        }

        if ( preg_match( '/^(?:\d+(?:\.\d+)?(?:px|rem|em|vh|vw|%)|var\(--[a-z0-9_-]+\)|clamp\([^)]+\))$/i', $value ) ) {
            return $value;
        }

        return (string) $fallback;
    }
}

if ( ! function_exists( 'developer_starter_get_footer_wave_palette_presets' ) ) {
    /**
     * Get footer wave palette presets.
     *
     * @return array<string,array<string,string>>
     */
    function developer_starter_get_footer_wave_palette_presets() {
        return array(
            'auto'        => array(
                'label'             => __( '自动跟随当前页面', 'developer-starter' ),
                'backdrop'          => 'var(--qiling-page-bg, var(--color-background, #ffffff))',
                'transition_from'   => 'var(--qiling-page-bg, var(--color-background, #ffffff))',
                'transition_height' => '32px',
                'height'            => '120px',
            ),
            'soft_blue'   => array(
                'label'             => __( '清亮蓝色', 'developer-starter' ),
                'backdrop'          => '#eff6ff',
                'transition_from'   => '#dbeafe',
                'transition_height' => '36px',
                'height'            => '120px',
                'color'             => '#bfdbfe',
                'layer_color'       => '#dbeafe',
                'layer_opacity'     => '0.56',
            ),
            'warm_orange' => array(
                'label'             => __( '暖橙营销', 'developer-starter' ),
                'backdrop'          => '#fff7ed',
                'transition_from'   => '#ffedd5',
                'transition_height' => '36px',
                'height'            => '120px',
                'color'             => '#fed7aa',
                'layer_color'       => '#ffedd5',
                'layer_opacity'     => '0.5',
            ),
            'fresh_green' => array(
                'label'             => __( '清新绿色', 'developer-starter' ),
                'backdrop'          => '#ecfdf5',
                'transition_from'   => '#d1fae5',
                'transition_height' => '36px',
                'height'            => '120px',
                'color'             => '#bbf7d0',
                'layer_color'       => '#d1fae5',
                'layer_opacity'     => '0.52',
            ),
            'rose'        => array(
                'label'             => __( '红粉活动', 'developer-starter' ),
                'backdrop'          => '#fff1f2',
                'transition_from'   => '#ffe4e6',
                'transition_height' => '36px',
                'height'            => '120px',
                'color'             => '#fecdd3',
                'layer_color'       => '#ffe4e6',
                'layer_opacity'     => '0.5',
            ),
            'violet'      => array(
                'label'             => __( '柔紫科技', 'developer-starter' ),
                'backdrop'          => '#f5f3ff',
                'transition_from'   => '#ede9fe',
                'transition_height' => '36px',
                'height'            => '120px',
                'color'             => '#ddd6fe',
                'layer_color'       => '#ede9fe',
                'layer_opacity'     => '0.52',
            ),
        );
    }
}

if ( ! function_exists( 'developer_starter_get_footer_wave_palette_vars' ) ) {
    /**
     * Resolve footer wave palette CSS values.
     *
     * @param string $palette_key Palette key.
     * @param string $main_bg     Footer main background fallback.
     * @param string $friend_bg   Footer friend background fallback.
     * @return array<string,string>
     */
    function developer_starter_get_footer_wave_palette_vars( $palette_key, $main_bg = '', $friend_bg = '' ) {
        $presets     = developer_starter_get_footer_wave_palette_presets();
        $palette_key = sanitize_key( (string) $palette_key );
        if ( '' === $palette_key || empty( $presets[ $palette_key ] ) ) {
            $palette_key = 'auto';
        }

        $palette = $presets[ $palette_key ];
        return array(
            'backdrop'          => isset( $palette['backdrop'] ) ? (string) $palette['backdrop'] : 'var(--qiling-page-bg, var(--color-background, #ffffff))',
            'transition_from'   => isset( $palette['transition_from'] ) ? (string) $palette['transition_from'] : 'var(--qiling-page-bg, var(--color-background, #ffffff))',
            'transition_height' => isset( $palette['transition_height'] ) ? (string) $palette['transition_height'] : '32px',
            'height'            => isset( $palette['height'] ) ? (string) $palette['height'] : '120px',
            'color'             => isset( $palette['color'] ) ? (string) $palette['color'] : ( '' !== $main_bg ? $main_bg : 'var(--qiling-component-footer-bg)' ),
            'layer_color'       => isset( $palette['layer_color'] ) ? (string) $palette['layer_color'] : ( '' !== $friend_bg ? $friend_bg : 'var(--qiling-component-footer-bottom-bg, var(--qiling-footer-main-bg))' ),
            'layer_opacity'     => isset( $palette['layer_opacity'] ) ? (string) $palette['layer_opacity'] : '0.38',
        );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_footer_visual_options' ) ) {
    /**
     * Sanitize footer visual settings.
     *
     * @param array<string,mixed> $options Sanitized options.
     * @return array<string,mixed>
     */
    function developer_starter_sanitize_footer_visual_options( $options ) {
        if ( ! is_array( $options ) ) {
            return array();
        }

        foreach ( array( 'footer_visual_wave_enable', 'footer_visual_friend_merge_bg' ) as $toggle ) {
            if ( isset( $options[ $toggle ] ) ) {
                $options[ $toggle ] = '1' === (string) $options[ $toggle ] ? '1' : '';
            }
        }

        if ( isset( $options['footer_visual_wave_style'] ) && ! in_array( (string) $options['footer_visual_wave_style'], array( 'single', 'double', 'soft', 'slope' ), true ) ) {
            $options['footer_visual_wave_style'] = 'double';
        }

        if ( isset( $options['footer_visual_wave_palette'] ) ) {
            $wave_palette_presets = developer_starter_get_footer_wave_palette_presets();
            $wave_palette         = sanitize_key( (string) $options['footer_visual_wave_palette'] );
            $options['footer_visual_wave_palette'] = isset( $wave_palette_presets[ $wave_palette ] ) ? $wave_palette : 'auto';
        }

        if ( isset( $options['footer_effect_scope'] ) && ! in_array( (string) $options['footer_effect_scope'], array( 'main', 'all', 'decorative' ), true ) ) {
            $options['footer_effect_scope'] = 'main';
        }

        foreach ( array(
            'footer_visual_main_bg',
            'footer_visual_main_text',
            'footer_visual_main_heading',
            'footer_visual_main_link',
            'footer_visual_main_link_hover',
            'footer_visual_friend_bg',
            'footer_visual_friend_text',
            'footer_visual_friend_link',
            'footer_visual_friend_link_hover',
            'footer_visual_bottom_bg',
            'footer_visual_bottom_text',
            'footer_visual_bottom_link',
            'footer_visual_bottom_link_hover',
            'footer_visual_bottom_border',
            'footer_visual_wave_backdrop',
            'footer_visual_wave_transition_from',
            'footer_visual_wave_color',
            'footer_visual_wave_layer_color',
        ) as $css_field ) {
            if ( isset( $options[ $css_field ] ) ) {
                $options[ $css_field ] = developer_starter_sanitize_footer_visual_css_value( $options[ $css_field ] );
            }
        }

        foreach ( array(
            'footer_visual_main_padding_top'    => '60px',
            'footer_visual_main_padding_bottom' => '60px',
            'footer_visual_friend_padding_y'    => '20px',
            'footer_visual_bottom_padding_y'    => '20px',
            'footer_visual_wave_height'         => '120px',
            'footer_visual_wave_transition_height' => '32px',
        ) as $spacing_field => $fallback ) {
            if ( isset( $options[ $spacing_field ] ) ) {
                $options[ $spacing_field ] = developer_starter_sanitize_footer_visual_spacing_value( $options[ $spacing_field ], $fallback );
            }
        }

        if ( isset( $options['footer_visual_wave_layer_opacity'] ) ) {
            $opacity = is_numeric( $options['footer_visual_wave_layer_opacity'] ) ? (float) $options['footer_visual_wave_layer_opacity'] : 0.38;
            $opacity = max( 0, min( 1, $opacity ) );
            $options['footer_visual_wave_layer_opacity'] = rtrim( rtrim( number_format( $opacity, 2, '.', '' ), '0' ), '.' );
        }

        return $options;
    }
}

if ( ! function_exists( 'developer_starter_get_footer_visual_page_meta_keys' ) ) {
    /**
     * Get footer visual page-level meta keys.
     *
     * @return array<string,string>
     */
    function developer_starter_get_footer_visual_page_meta_keys() {
        return array(
            'mode'                => '_qiling_footer_visual_mode',
            'wave'                => '_qiling_footer_wave_mode',
            'preset'              => '_qiling_footer_visual_preset',
            'inherit_skin_colors' => '_qiling_footer_inherit_skin_colors',
        );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_footer_visual_page_bool' ) ) {
    /**
     * Normalize a page footer visual boolean value.
     *
     * @param mixed $value Raw value.
     * @return bool
     */
    function developer_starter_sanitize_footer_visual_page_bool( $value ) {
        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_numeric( $value ) ) {
            return (float) $value > 0;
        }

        return in_array( strtolower( trim( (string) $value ) ), array( '1', 'true', 'yes', 'on' ), true );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_footer_visual_page_settings' ) ) {
    /**
     * Sanitize page-level footer visual settings.
     *
     * @param mixed $settings Raw settings.
     * @return array<string,mixed>
     */
    function developer_starter_sanitize_footer_visual_page_settings( $settings ) {
        $settings = is_array( $settings ) ? $settings : array();

        $mode = '';
        foreach ( array( 'mode', 'footer_mode', 'visual_mode' ) as $key ) {
            if ( isset( $settings[ $key ] ) && is_scalar( $settings[ $key ] ) ) {
                $mode = sanitize_key( (string) $settings[ $key ] );
                break;
            }
        }
        if ( 'hide' === $mode ) {
            $mode = 'hidden';
        } elseif ( 'skin' === $mode ) {
            $mode = 'page_skin';
        }
        if ( ! in_array( $mode, array( 'inherit', 'page_skin', 'preset', 'hidden' ), true ) ) {
            $mode = 'inherit';
        }

        $wave = '';
        foreach ( array( 'wave', 'wave_mode', 'footer_wave' ) as $key ) {
            if ( isset( $settings[ $key ] ) && is_scalar( $settings[ $key ] ) ) {
                $wave = sanitize_key( (string) $settings[ $key ] );
                break;
            }
        }
        if ( 'enabled' === $wave || 'enable' === $wave || 'true' === $wave ) {
            $wave = 'on';
        } elseif ( 'disabled' === $wave || 'disable' === $wave || 'false' === $wave ) {
            $wave = 'off';
        }
        if ( ! in_array( $wave, array( 'inherit', 'on', 'off' ), true ) ) {
            $wave = 'inherit';
        }

        $preset = '';
        foreach ( array( 'preset', 'footer_preset', 'footerPreset', 'visual_skin', 'visualSkin' ) as $key ) {
            if ( isset( $settings[ $key ] ) && is_scalar( $settings[ $key ] ) ) {
                $preset = sanitize_key( (string) $settings[ $key ] );
                break;
            }
        }

        $inherit_skin_colors = false;
        foreach ( array( 'inheritSkinColors', 'inherit_skin_colors', 'inherit_skin', 'skin_colors' ) as $key ) {
            if ( array_key_exists( $key, $settings ) ) {
                $inherit_skin_colors = developer_starter_sanitize_footer_visual_page_bool( $settings[ $key ] );
                break;
            }
        }

        return array(
            'mode'                => $mode,
            'wave'                => $wave,
            'preset'              => $preset,
            'inheritSkinColors'   => $inherit_skin_colors,
            'inherit_skin_colors' => $inherit_skin_colors,
        );
    }
}

if ( ! function_exists( 'developer_starter_get_post_footer_visual_settings' ) ) {
    /**
     * Read page-level footer visual settings from post meta.
     *
     * @param int $post_id Post ID.
     * @return array<string,mixed>
     */
    function developer_starter_get_post_footer_visual_settings( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return developer_starter_sanitize_footer_visual_page_settings( array() );
        }

        $meta_keys = developer_starter_get_footer_visual_page_meta_keys();

        return developer_starter_sanitize_footer_visual_page_settings(
            array(
                'mode'                => get_post_meta( $post_id, $meta_keys['mode'], true ),
                'wave'                => get_post_meta( $post_id, $meta_keys['wave'], true ),
                'preset'              => get_post_meta( $post_id, $meta_keys['preset'], true ),
                'inherit_skin_colors' => get_post_meta( $post_id, $meta_keys['inherit_skin_colors'], true ),
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_persist_post_footer_visual_settings' ) ) {
    /**
     * Persist page-level footer visual settings.
     *
     * @param int   $post_id  Post ID.
     * @param mixed $settings Raw settings.
     * @return void
     */
    function developer_starter_persist_post_footer_visual_settings( $post_id, $settings ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return;
        }

        $settings  = developer_starter_sanitize_footer_visual_page_settings( $settings );
        $meta_keys = developer_starter_get_footer_visual_page_meta_keys();

        if ( 'inherit' === $settings['mode'] ) {
            delete_post_meta( $post_id, $meta_keys['mode'] );
        } else {
            update_post_meta( $post_id, $meta_keys['mode'], $settings['mode'] );
        }

        if ( 'inherit' === $settings['wave'] ) {
            delete_post_meta( $post_id, $meta_keys['wave'] );
        } else {
            update_post_meta( $post_id, $meta_keys['wave'], $settings['wave'] );
        }

        if ( '' === $settings['preset'] ) {
            delete_post_meta( $post_id, $meta_keys['preset'] );
        } else {
            update_post_meta( $post_id, $meta_keys['preset'], $settings['preset'] );
        }

        if ( ! empty( $settings['inherit_skin_colors'] ) ) {
            update_post_meta( $post_id, $meta_keys['inherit_skin_colors'], '1' );
        } else {
            delete_post_meta( $post_id, $meta_keys['inherit_skin_colors'] );
        }
    }
}

if ( ! function_exists( 'developer_starter_get_current_footer_visual_page_settings' ) ) {
    /**
     * Resolve page-level footer visual settings for the current queried page.
     *
     * @return array<string,mixed>
     */
    function developer_starter_get_current_footer_visual_page_settings() {
        if ( ! function_exists( 'is_singular' ) || ! is_singular( 'page' ) ) {
            return developer_starter_sanitize_footer_visual_page_settings( array() );
        }

        $post_id = function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
        return developer_starter_get_post_footer_visual_settings( $post_id );
    }
}

if ( ! function_exists( 'developer_starter_get_footer_visual_config' ) ) {
    /**
     * Build footer visual runtime configuration.
     *
     * @return array<string,mixed>
     */
    function developer_starter_get_footer_visual_config() {
        $main_bg            = developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_main_bg', '' ), 'var(--qiling-component-footer-bg)' );
        $friend_merge_bg    = '1' === (string) developer_starter_get_option( 'footer_visual_friend_merge_bg', '' );
        $friend_bg_fallback = $friend_merge_bg ? $main_bg : 'var(--qiling-component-footer-bottom-bg, var(--qiling-footer-main-bg))';
        $friend_bg          = developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_friend_bg', '' ), $friend_bg_fallback );
        $bottom_bg          = developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_bottom_bg', '' ), 'var(--qiling-component-footer-bottom-bg, var(--qiling-footer-friend-bg))' );

        $wave_style = sanitize_key( (string) developer_starter_get_option( 'footer_visual_wave_style', 'double' ) );
        if ( ! in_array( $wave_style, array( 'single', 'double', 'soft', 'slope' ), true ) ) {
            $wave_style = 'double';
        }

        $effect_scope = sanitize_key( (string) developer_starter_get_option( 'footer_effect_scope', 'main' ) );
        if ( ! in_array( $effect_scope, array( 'main', 'all', 'decorative' ), true ) ) {
            $effect_scope = 'main';
        }

        $wave_enabled = '1' === (string) developer_starter_get_option( 'footer_visual_wave_enable', '' );
        $wave_palette = sanitize_key( (string) developer_starter_get_option( 'footer_visual_wave_palette', 'auto' ) );
        $wave_palette_vars = developer_starter_get_footer_wave_palette_vars( $wave_palette, $main_bg, $friend_bg );
        $vars = array(
            '--qiling-footer-main-bg'             => $main_bg,
            '--qiling-footer-main-text'           => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_main_text', '' ), 'var(--qiling-component-footer-text)' ),
            '--qiling-footer-main-heading'        => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_main_heading', '' ), 'var(--qiling-component-footer-heading, var(--qiling-footer-main-text))' ),
            '--qiling-footer-main-link'           => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_main_link', '' ), 'var(--qiling-component-footer-link)' ),
            '--qiling-footer-main-link-hover'     => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_main_link_hover', '' ), 'var(--qiling-component-footer-link-hover)' ),
            '--qiling-footer-friend-bg'           => $friend_bg,
            '--qiling-footer-friend-text'         => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_friend_text', '' ), 'var(--qiling-footer-main-text)' ),
            '--qiling-footer-friend-link'         => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_friend_link', '' ), 'var(--qiling-footer-main-link)' ),
            '--qiling-footer-friend-link-hover'   => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_friend_link_hover', '' ), 'var(--qiling-footer-main-link-hover)' ),
            '--qiling-footer-bottom-bg'           => $bottom_bg,
            '--qiling-footer-bottom-text'         => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_bottom_text', '' ), 'var(--qiling-footer-main-text)' ),
            '--qiling-footer-bottom-link'         => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_bottom_link', '' ), 'var(--qiling-footer-main-link)' ),
            '--qiling-footer-bottom-link-hover'   => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_bottom_link_hover', '' ), 'var(--qiling-footer-main-link-hover)' ),
            '--qiling-footer-bottom-border'       => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_bottom_border', '' ), 'var(--qiling-color-rgba-255-255-255-01)' ),
            '--qiling-footer-main-pt'             => developer_starter_sanitize_footer_visual_spacing_value( developer_starter_get_option( 'footer_visual_main_padding_top', '' ), '60px' ),
            '--qiling-footer-main-pb'             => developer_starter_sanitize_footer_visual_spacing_value( developer_starter_get_option( 'footer_visual_main_padding_bottom', '' ), '60px' ),
            '--qiling-footer-friend-py'           => developer_starter_sanitize_footer_visual_spacing_value( developer_starter_get_option( 'footer_visual_friend_padding_y', '' ), '20px' ),
            '--qiling-footer-bottom-py'           => developer_starter_sanitize_footer_visual_spacing_value( developer_starter_get_option( 'footer_visual_bottom_padding_y', '' ), '20px' ),
            '--qiling-footer-wave-backdrop'       => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_wave_backdrop', '' ), $wave_palette_vars['backdrop'] ),
            '--qiling-footer-wave-transition-from' => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_wave_transition_from', '' ), $wave_palette_vars['transition_from'] ),
            '--qiling-footer-wave-transition-height' => developer_starter_sanitize_footer_visual_spacing_value( developer_starter_get_option( 'footer_visual_wave_transition_height', '' ), $wave_palette_vars['transition_height'] ),
            '--qiling-footer-wave-height'         => developer_starter_sanitize_footer_visual_spacing_value( developer_starter_get_option( 'footer_visual_wave_height', '' ), $wave_palette_vars['height'] ),
            '--qiling-footer-wave-color'          => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_wave_color', '' ), $wave_palette_vars['color'] ),
            '--qiling-footer-wave-layer-color'    => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_wave_layer_color', '' ), $wave_palette_vars['layer_color'] ),
            '--qiling-footer-wave-layer-opacity'  => developer_starter_sanitize_footer_visual_css_value( developer_starter_get_option( 'footer_visual_wave_layer_opacity', '' ), $wave_palette_vars['layer_opacity'] ),
        );

        $page_footer_settings = developer_starter_get_current_footer_visual_page_settings();
        $page_footer_mode     = isset( $page_footer_settings['mode'] ) ? (string) $page_footer_settings['mode'] : 'inherit';
        $requested_preset_key = isset( $page_footer_settings['preset'] ) ? sanitize_key( (string) $page_footer_settings['preset'] ) : '';
        $active_skin          = function_exists( 'developer_starter_get_current_page_visual_skin' )
            ? developer_starter_get_current_page_visual_skin()
            : null;
        $preset_skin          = 'preset' === $page_footer_mode && '' !== $requested_preset_key && function_exists( 'developer_starter_get_page_visual_skin' )
            ? developer_starter_get_page_visual_skin( $requested_preset_key )
            : null;
        if ( 'preset' === $page_footer_mode && ! is_array( $preset_skin ) && '' !== $requested_preset_key && function_exists( 'developer_starter_get_page_visual_custom_preset_skin' ) ) {
            $preset_skin = developer_starter_get_page_visual_custom_preset_skin( $requested_preset_key );
        }
        $footer_skin          = 'preset' === $page_footer_mode ? $preset_skin : $active_skin;
        $footer_skin_key      = is_array( $footer_skin ) && ! empty( $footer_skin['key'] ) ? sanitize_key( (string) $footer_skin['key'] ) : '';
        $skin_footer          = is_array( $footer_skin ) && ! empty( $footer_skin['footer'] ) && is_array( $footer_skin['footer'] )
            ? $footer_skin['footer']
            : array();
        $uses_skin_footer     = ! empty( $skin_footer )
            && (
                'page_skin' === $page_footer_mode
                || 'preset' === $page_footer_mode
                || ! empty( $page_footer_settings['inherit_skin_colors'] )
            );

        if ( 'hidden' === $page_footer_mode ) {
            return apply_filters(
                'developer_starter_footer_visual_config',
                array(
                    'classes'           => array( 'site-footer--hidden-by-page' ),
                    'style'             => '',
                    'wave_enabled'      => false,
                    'wave_style'        => $wave_style,
                    'effect_scope'      => $effect_scope,
                    'hidden'            => true,
                    'page_footer_mode'  => $page_footer_mode,
                    'page_footer_skin'  => $footer_skin_key,
                )
            );
        }

        $skin_classes = array();
        if ( $uses_skin_footer ) {
            if ( ! empty( $skin_footer['vars'] ) && is_array( $skin_footer['vars'] ) ) {
                foreach ( $skin_footer['vars'] as $name => $value ) {
                    $name = is_string( $name ) ? trim( $name ) : '';
                    if ( ! preg_match( '/^--[a-z0-9_-]+$/i', $name ) ) {
                        continue;
                    }
                    $vars[ $name ] = developer_starter_sanitize_footer_visual_css_value( $value, isset( $vars[ $name ] ) ? $vars[ $name ] : '' );
                }
            }

            if ( array_key_exists( 'wave_enabled', $skin_footer ) ) {
                $wave_enabled = developer_starter_sanitize_footer_visual_page_bool( $skin_footer['wave_enabled'] );
            }

            if ( ! empty( $skin_footer['wave_style'] ) ) {
                $skin_wave_style = sanitize_key( (string) $skin_footer['wave_style'] );
                if ( in_array( $skin_wave_style, array( 'single', 'double', 'soft', 'slope' ), true ) ) {
                    $wave_style = $skin_wave_style;
                }
            }

            if ( ! empty( $skin_footer['effect_scope'] ) ) {
                $skin_effect_scope = sanitize_key( (string) $skin_footer['effect_scope'] );
                if ( in_array( $skin_effect_scope, array( 'main', 'all', 'decorative' ), true ) ) {
                    $effect_scope = $skin_effect_scope;
                }
            }

            if ( ! empty( $skin_footer['classes'] ) ) {
                $skin_classes = is_array( $skin_footer['classes'] ) ? $skin_footer['classes'] : preg_split( '/\s+/', (string) $skin_footer['classes'] );
            }
        }

        $page_visual_footer_classes = array();
        if ( function_exists( 'developer_starter_get_current_page_visual_style_post_id' ) && function_exists( 'developer_starter_get_post_page_visual_style' ) && function_exists( 'developer_starter_get_page_visual_style_custom_vars_array' ) ) {
            $page_visual_post_id = developer_starter_get_current_page_visual_style_post_id();
            if ( $page_visual_post_id > 0 ) {
                $page_visual_settings = developer_starter_get_post_page_visual_style( $page_visual_post_id );
                $page_visual_vars = developer_starter_get_page_visual_style_custom_vars_array( $page_visual_settings, 'footer' );
                foreach ( $page_visual_vars as $name => $value ) {
                    $name = is_string( $name ) ? trim( $name ) : '';
                    if ( 0 !== strpos( $name, '--qiling-footer-' ) ) {
                        continue;
                    }
                    $vars[ $name ] = developer_starter_sanitize_footer_visual_css_value( $value, isset( $vars[ $name ] ) ? $vars[ $name ] : '' );
                }
                if ( ! empty( $page_visual_vars ) ) {
                    $page_visual_footer_classes[] = 'site-footer--page-visual-custom';
                }
            }
        }

        if ( 'on' === $page_footer_settings['wave'] ) {
            $wave_enabled = true;
        } elseif ( 'off' === $page_footer_settings['wave'] ) {
            $wave_enabled = false;
        }

        $style = '';
        foreach ( $vars as $name => $value ) {
            if ( '' !== (string) $value ) {
                $style .= $name . ':' . $value . ';';
            }
        }

        $classes = array(
            'site-footer--visual',
            'site-footer--wave-' . $wave_style,
            'site-footer--effect-scope-' . $effect_scope,
        );

        if ( 'inherit' !== $page_footer_mode ) {
            $classes[] = 'site-footer--page-footer-' . str_replace( '_', '-', $page_footer_mode );
        }
        if ( $uses_skin_footer ) {
            $classes[] = 'site-footer--page-skin';
            if ( '' !== $footer_skin_key ) {
                $classes[] = 'site-footer--page-skin-' . str_replace( '_', '-', $footer_skin_key );
            }
            foreach ( $skin_classes as $skin_class ) {
                $classes[] = (string) $skin_class;
            }
        }
        foreach ( $page_visual_footer_classes as $page_visual_footer_class ) {
            $classes[] = (string) $page_visual_footer_class;
        }
        if ( $wave_enabled ) {
            $classes[] = 'site-footer--wave-enabled';
        }
        if ( $friend_merge_bg ) {
            $classes[] = 'site-footer--friend-merged';
        }

        return apply_filters(
            'developer_starter_footer_visual_config',
            array(
                'classes'      => array_values( array_unique( array_map( 'sanitize_html_class', $classes ) ) ),
                'style'        => $style,
                'wave_enabled' => $wave_enabled,
                'wave_style'   => $wave_style,
                'effect_scope' => $effect_scope,
                'hidden'       => false,
                'page_footer_mode' => $page_footer_mode,
                'page_footer_skin' => $footer_skin_key,
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_get_footer_wave_paths' ) ) {
    /**
     * Get SVG paths for footer wave styles.
     *
     * @param string $style Wave style.
     * @return array<string,string>
     */
    function developer_starter_get_footer_wave_paths( $style ) {
        $style = sanitize_key( (string) $style );

        $paths = array(
            'primary' => 'M0,78 C180,28 332,124 534,78 C742,30 882,18 1084,66 C1248,104 1322,58 1440,40 L1440,160 L0,160 Z',
            'soft'    => 'M0,92 C250,130 430,20 704,62 C936,98 1112,130 1440,72 L1440,160 L0,160 Z',
        );

        if ( 'slope' === $style ) {
            $paths['primary'] = 'M0,58 L1440,0 L1440,160 L0,160 Z';
            $paths['soft']    = 'M0,92 L1440,38 L1440,160 L0,160 Z';
        } elseif ( 'soft' === $style ) {
            $paths['primary'] = 'M0,96 C260,46 400,126 664,84 C918,44 1086,28 1440,82 L1440,160 L0,160 Z';
            $paths['soft']    = 'M0,74 C320,118 512,44 754,76 C1028,112 1196,34 1440,70 L1440,160 L0,160 Z';
        }

        return $paths;
    }
}
