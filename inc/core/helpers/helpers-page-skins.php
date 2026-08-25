<?php
/**
 * Page-level visual skin helpers.
 *
 * @package Developer_Starter
 * @since 2.5.17
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_page_visual_skins' ) ) {
    /**
     * Get registered page-level visual skins.
     *
     * @return array<string,array<string,mixed>>
     */
    function developer_starter_get_page_visual_skins() {
        $skins = array(
            'cloud_canvas' => array(
                'label'           => __( '一体式官网', 'developer-starter' ),
                'templates'       => array(
                    'templates/template-qiling-cloud-canvas.php',
                ),
                'style_handle'    => 'developer-starter-cloud-canvas-skin',
                'style_path'      => 'assets/css/cloud-canvas-skin.css',
                'style_deps'      => array( 'developer-starter-main' ),
                'header'          => array(
                    'vars' => array(
                        '--qiling-header-bg'                         => 'rgba(255, 255, 255, 0.88)',
                        '--qiling-header-text'                       => 'var(--qcc-ink, #3f3268)',
                        '--qiling-header-nav-link'                   => 'var(--qcc-ink, #3f3268)',
                        '--qiling-header-nav-hover-bg'               => 'linear-gradient(135deg, var(--qcc-coral, #ff836f) 0%, var(--qcc-blue, #4f7dff) 100%)',
                        '--qiling-header-nav-hover-text'             => '#ffffff',
                        '--qiling-header-scrolled-text'              => 'var(--qcc-ink, #3f3268)',
                        '--qiling-header-scrolled-nav-link'          => 'var(--qcc-ink, #3f3268)',
                        '--qiling-header-scrolled-nav-hover-text'    => '#ffffff',
                        '--qiling-header-logo-transparent-fill'      => 'linear-gradient(90deg, var(--qcc-coral, #ff836f) 0%, var(--qcc-blue, #4f7dff) 100%)',
                        '--qiling-header-logo-scrolled-fill'         => 'linear-gradient(90deg, var(--qcc-coral, #ff836f) 0%, var(--qcc-blue, #4f7dff) 100%)',
                        '--qiling-header-phone-transparent-bg'       => 'rgba(255, 255, 255, 0.16)',
                        '--qiling-header-phone-transparent-text'     => '#ffffff',
                        '--qiling-header-phone-normal-bg'            => 'linear-gradient(135deg, var(--qcc-coral, #ff836f) 0%, var(--qcc-blue, #4f7dff) 100%)',
                        '--qiling-header-phone-normal-text'          => '#ffffff',
                        '--qiling-header-search-bg'                  => 'rgba(255, 255, 255, 0.92)',
                        '--qiling-header-search-border'              => 'rgba(79, 125, 255, 0.18)',
                        '--qiling-header-search-text'                => 'var(--qcc-ink, #3f3268)',
                        '--qiling-header-search-placeholder'         => 'rgba(63, 50, 104, 0.54)',
                        '--qiling-header-search-icon'                => 'var(--qcc-blue, #4f7dff)',
                        '--qiling-header-search-shadow'              => '0 14px 30px rgba(63, 50, 104, 0.08)',
                        '--qiling-header-search-transparent-bg'      => 'rgba(255, 255, 255, 0.14)',
                        '--qiling-header-search-transparent-border'  => 'rgba(255, 255, 255, 0.28)',
                        '--qiling-header-search-transparent-text'    => '#ffffff',
                        '--qiling-header-search-transparent-placeholder' => 'rgba(255, 255, 255, 0.72)',
                        '--qiling-header-search-transparent-icon'    => '#ffffff',
                        '--qiling-header-search-transparent-shadow'  => '0 16px 36px rgba(15, 23, 42, 0.12)',
                        '--qiling-header-search-transparent-submit-border' => 'rgba(255, 255, 255, 0.18)',
                        '--qiling-announcement-marketing-bg'         => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
                        '--qiling-announcement-marketing-button-text' => '#1d4ed8',
                    ),
                ),
                'body_classes'    => array(
                    'qiling-page-skin',
                    'qiling-page-skin-cloud-canvas',
                    'qiling-page-skin-integrated',
                ),
                'wrapper_classes' => array(
                    'qiling-page-skin',
                    'qiling-page-skin--cloud-canvas',
                    'qiling-page-skin--integrated',
                ),
                'footer'          => array(
                    'vars'         => array(
                        '--qiling-footer-main-bg'            => 'linear-gradient(180deg, color-mix(in srgb, var(--qcc-coral, #2563eb) 72%, var(--qcc-ink, #172554) 28%) 0%, color-mix(in srgb, var(--qcc-coral, #2563eb) 38%, var(--qcc-ink, #172554) 62%) 52%, var(--qcc-ink, #172554) 100%)',
                        '--qiling-footer-main-text'          => 'var(--qiling-color-rgba-255-255-255-088)',
                        '--qiling-footer-main-heading'       => 'var(--qiling-color-rgba-255-255-255-098)',
                        '--qiling-footer-main-link'          => 'var(--qiling-color-rgba-255-255-255-084)',
                        '--qiling-footer-main-link-hover'    => 'var(--qiling-color-rgba-255-255-255-098)',
                        '--qiling-footer-friend-bg'          => 'color-mix(in srgb, var(--qcc-coral, #2563eb) 30%, var(--qcc-ink, #172554) 70%)',
                        '--qiling-footer-friend-text'        => 'var(--qiling-color-rgba-255-255-255-08)',
                        '--qiling-footer-friend-link'        => 'var(--qiling-color-rgba-255-255-255-084)',
                        '--qiling-footer-friend-link-hover'  => 'var(--qiling-color-rgba-255-255-255-098)',
                        '--qiling-footer-bottom-bg'          => 'var(--qcc-ink, #172554)',
                        '--qiling-footer-bottom-text'        => 'var(--qiling-color-rgba-255-255-255-078)',
                        '--qiling-footer-bottom-link'        => 'var(--qiling-color-rgba-255-255-255-088)',
                        '--qiling-footer-bottom-border'      => 'rgba(255, 255, 255, 0.12)',
                        '--qiling-footer-wave-height'        => '76px',
                        '--qiling-footer-wave-backdrop'      => 'var(--qiling-page-bg, #ffffff)',
                        '--qiling-footer-wave-transition-from' => 'var(--qiling-page-bg, #ffffff)',
                        '--qiling-footer-wave-transition-height' => '24px',
                        '--qiling-footer-wave-color'         => 'color-mix(in srgb, var(--qcc-coral, #2563eb) 72%, var(--qcc-ink, #172554) 28%)',
                        '--qiling-footer-wave-layer-color'   => 'color-mix(in srgb, var(--qcc-coral, #2563eb) 54%, var(--qiling-page-bg, #ffffff) 46%)',
                        '--qiling-footer-wave-layer-opacity' => '0.28',
                    ),
                    'wave_enabled' => true,
                    'wave_style'   => 'soft',
                    'effect_scope' => 'decorative',
                    'classes'      => array( 'site-footer--integrated-canvas' ),
                ),
            ),
            'tech_canvas' => array(
                'label'           => __( '科技公司官网（一体式）', 'developer-starter' ),
                'templates'       => array(
                    'templates/template-tech-company-integrated.php',
                    'templates/template-home.php',
                ),
                'style_handle'    => 'developer-starter-cloud-canvas-skin',
                'style_path'      => 'assets/css/cloud-canvas-skin.css',
                'style_deps'      => array( 'developer-starter-main' ),
                'header'          => array(
                    'vars' => array(
                        '--qiling-header-bg'                         => 'rgba(247, 251, 255, 0.9)',
                        '--qiling-header-text'                       => '#25365f',
                        '--qiling-header-nav-link'                   => '#25365f',
                        '--qiling-header-nav-hover-bg'               => 'linear-gradient(135deg, #4f7dff 0%, #38c9a6 100%)',
                        '--qiling-header-nav-hover-text'             => '#ffffff',
                        '--qiling-header-scrolled-text'              => '#25365f',
                        '--qiling-header-scrolled-nav-link'          => '#25365f',
                        '--qiling-header-scrolled-nav-hover-text'    => '#ffffff',
                        '--qiling-header-logo-transparent-fill'      => 'linear-gradient(90deg, #4f7dff 0%, #38c9a6 100%)',
                        '--qiling-header-logo-scrolled-fill'         => 'linear-gradient(90deg, #4f7dff 0%, #38c9a6 100%)',
                        '--qiling-header-phone-transparent-bg'       => 'rgba(255, 255, 255, 0.16)',
                        '--qiling-header-phone-transparent-text'     => '#ffffff',
                        '--qiling-header-phone-normal-bg'            => 'linear-gradient(135deg, #4f7dff 0%, #38c9a6 100%)',
                        '--qiling-header-phone-normal-text'          => '#ffffff',
                        '--qiling-header-search-bg'                  => 'rgba(255, 255, 255, 0.94)',
                        '--qiling-header-search-border'              => 'rgba(79, 125, 255, 0.18)',
                        '--qiling-header-search-text'                => '#25365f',
                        '--qiling-header-search-placeholder'         => 'rgba(37, 54, 95, 0.52)',
                        '--qiling-header-search-icon'                => '#4f7dff',
                        '--qiling-header-search-shadow'              => '0 14px 30px rgba(37, 54, 95, 0.1)',
                        '--qiling-header-search-transparent-bg'      => 'rgba(255, 255, 255, 0.14)',
                        '--qiling-header-search-transparent-border'  => 'rgba(255, 255, 255, 0.28)',
                        '--qiling-header-search-transparent-text'    => '#ffffff',
                        '--qiling-header-search-transparent-placeholder' => 'rgba(255, 255, 255, 0.72)',
                        '--qiling-header-search-transparent-icon'    => '#ffffff',
                        '--qiling-header-search-transparent-shadow'  => '0 16px 36px rgba(15, 23, 42, 0.12)',
                        '--qiling-header-search-transparent-submit-border' => 'rgba(255, 255, 255, 0.18)',
                        '--qiling-announcement-marketing-bg'         => 'linear-gradient(135deg, #2563eb 0%, #06b6d4 100%)',
                        '--qiling-announcement-marketing-button-text' => '#2563eb',
                    ),
                ),
                'body_classes'    => array(
                    'qiling-page-skin',
                    'qiling-page-skin-cloud-canvas',
                    'qiling-page-skin-tech-canvas',
                    'qiling-page-skin-integrated',
                ),
                'wrapper_classes' => array(
                    'qiling-page-skin',
                    'qiling-page-skin--cloud-canvas',
                    'qiling-page-skin--tech-canvas',
                    'qiling-page-skin--integrated',
                ),
                'footer'          => array(
                    'vars'         => array(
                        '--qiling-footer-main-bg'            => 'linear-gradient(180deg, #f7fbff 0%, #edf8ff 52%, #e3f7f3 100%)',
                        '--qiling-footer-main-text'          => '#52617e',
                        '--qiling-footer-main-heading'       => '#25365f',
                        '--qiling-footer-main-link'          => '#52617e',
                        '--qiling-footer-main-link-hover'    => '#315fdc',
                        '--qiling-footer-friend-bg'          => '#e3f7f3',
                        '--qiling-footer-friend-text'        => '#5c6b84',
                        '--qiling-footer-friend-link'        => '#52617e',
                        '--qiling-footer-friend-link-hover'  => '#315fdc',
                        '--qiling-footer-bottom-bg'          => '#e8efff',
                        '--qiling-footer-bottom-text'        => '#667085',
                        '--qiling-footer-bottom-link'        => '#3d5b9d',
                        '--qiling-footer-bottom-link-hover'  => '#315fdc',
                        '--qiling-footer-bottom-border'      => 'rgba(79, 125, 255, 0.14)',
                        '--qiling-footer-wave-height'        => '76px',
                        '--qiling-footer-wave-backdrop'      => '#f7fbff',
                        '--qiling-footer-wave-transition-from' => '#f7fbff',
                        '--qiling-footer-wave-transition-height' => '24px',
                        '--qiling-footer-wave-color'         => '#f7fbff',
                        '--qiling-footer-wave-layer-color'   => '#dff7ff',
                        '--qiling-footer-wave-layer-opacity' => '0.72',
                    ),
                    'wave_enabled' => true,
                    'wave_style'   => 'soft',
                    'effect_scope' => 'decorative',
                    'classes'      => array( 'site-footer--integrated-canvas', 'site-footer--tech-canvas' ),
                ),
            ),
            'hosting_canvas' => array(
                'label'           => __( '云主机SaaS官网（一体式）', 'developer-starter' ),
                'templates'       => array( 'templates/template-hosting-saas-home.php' ),
                'style_handle'    => 'developer-starter-cloud-canvas-skin',
                'style_path'      => 'assets/css/cloud-canvas-skin.css',
                'style_deps'      => array( 'developer-starter-main' ),
                'header'          => array(
                    'vars' => array(
                        '--qiling-header-bg'                         => 'rgba(255, 255, 255, 0.94)',
                        '--qiling-header-text'                       => '#134e4a',
                        '--qiling-header-nav-link'                   => '#134e4a',
                        '--qiling-header-nav-hover-bg'               => 'linear-gradient(135deg, #059669 0%, #2563eb 100%)',
                        '--qiling-header-nav-hover-text'             => '#ffffff',
                        '--qiling-header-scrolled-text'              => '#134e4a',
                        '--qiling-header-scrolled-nav-link'          => '#134e4a',
                        '--qiling-header-scrolled-nav-hover-text'    => '#ffffff',
                        '--qiling-header-logo-transparent-fill'      => 'linear-gradient(90deg, #059669 0%, #2563eb 100%)',
                        '--qiling-header-logo-scrolled-fill'         => 'linear-gradient(90deg, #059669 0%, #2563eb 100%)',
                        '--qiling-header-phone-transparent-bg'       => 'rgba(255, 255, 255, 0.16)',
                        '--qiling-header-phone-transparent-text'     => '#ffffff',
                        '--qiling-header-phone-normal-bg'            => 'linear-gradient(135deg, #059669 0%, #2563eb 100%)',
                        '--qiling-header-phone-normal-text'          => '#ffffff',
                        '--qiling-header-search-bg'                  => 'rgba(255, 255, 255, 0.94)',
                        '--qiling-header-search-border'              => 'rgba(5, 150, 105, 0.22)',
                        '--qiling-header-search-text'                => '#134e4a',
                        '--qiling-header-search-placeholder'         => 'rgba(19, 78, 74, 0.56)',
                        '--qiling-header-search-icon'                => '#059669',
                        '--qiling-header-search-shadow'              => '0 14px 30px rgba(19, 78, 74, 0.1)',
                        '--qiling-header-search-transparent-bg'      => 'rgba(255, 255, 255, 0.14)',
                        '--qiling-header-search-transparent-border'  => 'rgba(255, 255, 255, 0.28)',
                        '--qiling-header-search-transparent-text'    => '#ffffff',
                        '--qiling-header-search-transparent-placeholder' => 'rgba(255, 255, 255, 0.72)',
                        '--qiling-header-search-transparent-icon'    => '#ffffff',
                        '--qiling-header-search-transparent-shadow'  => '0 16px 36px rgba(15, 23, 42, 0.12)',
                        '--qiling-header-search-transparent-submit-border' => 'rgba(255, 255, 255, 0.18)',
                        '--qiling-announcement-marketing-bg'         => 'linear-gradient(135deg, #059669 0%, #2563eb 100%)',
                        '--qiling-announcement-marketing-button-text' => '#047857',
                    ),
                ),
                'body_classes'    => array(
                    'qiling-page-skin',
                    'qiling-page-skin-cloud-canvas',
                    'qiling-page-skin-hosting-canvas',
                    'qiling-page-skin-integrated',
                ),
                'wrapper_classes' => array(
                    'qiling-page-skin',
                    'qiling-page-skin--cloud-canvas',
                    'qiling-page-skin--hosting-canvas',
                    'qiling-page-skin--integrated',
                ),
                'footer'          => array(
                    'vars'         => array(
                        '--qiling-footer-main-bg'            => 'linear-gradient(180deg, #087f69 0%, #07645e 52%, #064e3b 100%)',
                        '--qiling-footer-main-text'          => 'rgba(255, 255, 255, 0.94)',
                        '--qiling-footer-main-heading'       => 'rgba(255, 255, 255, 0.98)',
                        '--qiling-footer-main-link'          => 'rgba(255, 255, 255, 0.88)',
                        '--qiling-footer-main-link-hover'    => 'rgba(255, 255, 255, 0.98)',
                        '--qiling-footer-friend-bg'          => '#07594f',
                        '--qiling-footer-friend-text'        => 'rgba(255, 255, 255, 0.82)',
                        '--qiling-footer-friend-link'        => 'rgba(255, 255, 255, 0.88)',
                        '--qiling-footer-friend-link-hover'  => 'rgba(255, 255, 255, 0.98)',
                        '--qiling-footer-bottom-bg'          => '#064e3b',
                        '--qiling-footer-bottom-text'        => 'rgba(255, 255, 255, 0.78)',
                        '--qiling-footer-bottom-link'        => 'rgba(255, 255, 255, 0.9)',
                        '--qiling-footer-bottom-border'      => 'rgba(255, 255, 255, 0.18)',
                        '--qiling-footer-wave-height'        => '64px',
                        '--qiling-footer-wave-backdrop'      => 'var(--qiling-page-bg, #f0fdf9)',
                        '--qiling-footer-wave-transition-from' => 'var(--qiling-page-bg, #f0fdf9)',
                        '--qiling-footer-wave-transition-height' => '28px',
                        '--qiling-footer-wave-color'         => '#087f69',
                        '--qiling-footer-wave-layer-color'   => '#55a49a',
                        '--qiling-footer-wave-layer-opacity' => '0.26',
                    ),
                    'wave_enabled' => true,
                    'wave_style'   => 'soft',
                    'effect_scope' => 'decorative',
                    'classes'      => array( 'site-footer--integrated-canvas', 'site-footer--hosting-canvas' ),
                ),
            ),
        );

        if ( function_exists( 'developer_starter_get_industry_page_visual_skin_presets' ) ) {
            $skins = array_merge( $skins, developer_starter_get_industry_page_visual_skin_presets() );
        }

        return apply_filters( 'developer_starter_page_visual_skins', $skins );
    }
}

if ( ! function_exists( 'developer_starter_build_industry_page_visual_skin' ) ) {
    /**
     * Build a reusable industry visual preset from a compact palette spec.
     *
     * @param string              $key  Preset key.
     * @param array<string,mixed> $spec Preset spec.
     * @return array<string,mixed>
     */
    function developer_starter_build_industry_page_visual_skin( $key, $spec ) {
        $key         = sanitize_key( (string) $key );
        $label       = ! empty( $spec['label'] ) && is_scalar( $spec['label'] ) ? (string) $spec['label'] : $key;
        $primary     = ! empty( $spec['primary'] ) ? (string) $spec['primary'] : '#4f7dff';
        $accent      = ! empty( $spec['accent'] ) ? (string) $spec['accent'] : '#38c9a6';
        $ink         = ! empty( $spec['ink'] ) ? (string) $spec['ink'] : '#25365f';
        $muted       = ! empty( $spec['muted'] ) ? (string) $spec['muted'] : 'rgba(37,54,95,0.58)';
        $background  = ! empty( $spec['background'] ) ? (string) $spec['background'] : '#f7fbff';
        $header_bg   = ! empty( $spec['header_bg'] ) ? (string) $spec['header_bg'] : 'rgba(255, 255, 255, 0.9)';
        $search_bg   = ! empty( $spec['search_bg'] ) ? (string) $spec['search_bg'] : 'rgba(255, 255, 255, 0.78)';
        $footer_main = ! empty( $spec['footer_main'] ) ? (string) $spec['footer_main'] : $primary;
        $footer_deep = ! empty( $spec['footer_deep'] ) ? (string) $spec['footer_deep'] : $ink;
        $wave        = ! empty( $spec['wave'] ) ? (string) $spec['wave'] : $background;
        $wave_layer  = ! empty( $spec['wave_layer'] ) ? (string) $spec['wave_layer'] : '#ffffff';
        $templates   = ! empty( $spec['templates'] ) && is_array( $spec['templates'] ) ? $spec['templates'] : array();
        $announcement_bg          = ! empty( $spec['announcement_bg'] ) ? (string) $spec['announcement_bg'] : 'linear-gradient(135deg, ' . $primary . ' 0%, ' . $accent . ' 100%)';
        $announcement_button_text = ! empty( $spec['announcement_button_text'] ) ? (string) $spec['announcement_button_text'] : $primary;
        $primary_rgb = '';
        $accent_rgb  = '';
        foreach ( array( 'primary' => $primary, 'accent' => $accent ) as $rgb_key => $rgb_color ) {
            if ( preg_match( '/^#([a-f0-9]{6})$/i', trim( (string) $rgb_color ), $matches ) ) {
                $hex = $matches[1];
                $rgb = hexdec( substr( $hex, 0, 2 ) ) . ', ' . hexdec( substr( $hex, 2, 2 ) ) . ', ' . hexdec( substr( $hex, 4, 2 ) );
                if ( 'primary' === $rgb_key ) {
                    $primary_rgb = $rgb;
                } else {
                    $accent_rgb = $rgb;
                }
            }
        }

        return array(
            'label'           => $label,
            'templates'       => array_values( array_filter( array_map( 'strval', $templates ) ) ),
            'header'          => array(
                'vars' => array(
                    '--qiling-page-primary'                         => $primary,
                    '--qiling-page-accent'                          => $primary,
                    '--qiling-page-accent-2'                        => $accent,
                    '--qiling-page-accent-contrast'                 => '#ffffff',
                    '--color-primary'                               => $primary,
                    '--color-primary-rgb'                           => '' !== $primary_rgb ? $primary_rgb : '37, 99, 235',
                    '--color-secondary'                             => $accent,
                    '--color-accent'                                => $accent,
                    '--color-accent-rgb'                            => '' !== $accent_rgb ? $accent_rgb : '5, 150, 105',
                    '--qiling-gradient-brand'                       => 'linear-gradient(135deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-gradient-brand-soft'                  => 'linear-gradient(135deg, color-mix(in srgb, ' . $primary . ' 14%, transparent) 0%, color-mix(in srgb, ' . $accent . ' 8%, transparent) 100%)',
                    '--qiling-page-bg'                              => $background,
                    '--qiling-page-text'                            => $ink,
                    '--qiling-component-button-bg'                  => $primary,
                    '--qiling-component-button-text'                => '#ffffff',
                    '--qiling-component-button-border'              => $primary,
                    '--qiling-component-button-hover-bg'            => 'color-mix(in srgb, ' . $primary . ' 84%, #000000 16%)',
                    '--qiling-component-button-hover-text'          => '#ffffff',
                    '--qiling-component-border-accent'              => $primary,
                    '--qiling-component-badge-bg'                   => 'linear-gradient(135deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-component-badge-text'                 => '#ffffff',
                    '--qiling-component-badge-border'               => $primary,
                    '--qiling-component-title-bar-bg'               => 'linear-gradient(135deg, color-mix(in srgb, ' . $primary . ' 14%, transparent) 0%, color-mix(in srgb, ' . $accent . ' 8%, transparent) 100%)',
                    '--qiling-component-title-bar-text'             => $ink,
                    '--qiling-component-title-bar-border'           => 'color-mix(in srgb, ' . $primary . ' 18%, transparent)',
                    '--qiling-component-list-header-bg'             => 'color-mix(in srgb, ' . $primary . ' 9%, #ffffff 91%)',
                    '--qiling-component-list-header-text'           => $ink,
                    '--qiling-component-list-header-border'         => 'color-mix(in srgb, ' . $primary . ' 18%, transparent)',
                    '--qiling-component-highlight-bg'               => 'linear-gradient(135deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-component-highlight-text'             => '#ffffff',
                    '--qiling-component-highlight-border'           => $primary,
                    '--qiling-component-highlight-soft-bg'          => 'color-mix(in srgb, ' . $primary . ' 8%, transparent)',
                    '--qiling-announcement-marketing-bg'            => $announcement_bg,
                    '--qiling-announcement-marketing-button-text'    => $announcement_button_text,
                    '--qiling-header-bg'                            => $header_bg,
                    '--qiling-header-transparent-bg'                => $header_bg,
                    '--qiling-header-text'                          => $ink,
                    '--qiling-header-nav-link'                      => $ink,
                    '--qiling-header-transparent-text'              => '#ffffff',
                    '--qiling-header-transparent-nav-link'          => '#ffffff',
                    '--qiling-header-nav-hover-bg'                  => 'linear-gradient(135deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-header-nav-hover-text'                => '#ffffff',
                    '--qiling-header-scrolled-text'                 => $ink,
                    '--qiling-header-scrolled-nav-link'             => $ink,
                    '--qiling-header-scrolled-nav-hover-bg'         => 'linear-gradient(135deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-header-scrolled-nav-hover-text'       => '#ffffff',
                    '--qiling-header-logo-transparent-fill'         => 'linear-gradient(90deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-header-logo-scrolled-fill'            => 'linear-gradient(90deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-header-phone-transparent-bg'          => 'rgba(255, 255, 255, 0.16)',
                    '--qiling-header-phone-transparent-text'        => '#ffffff',
                    '--qiling-header-phone-normal-bg'               => 'linear-gradient(135deg, ' . $primary . ' 0%, ' . $accent . ' 100%)',
                    '--qiling-header-phone-normal-text'             => '#ffffff',
                    '--qiling-header-search-bg'                     => $search_bg,
                    '--qiling-header-search-border'                 => 'color-mix(in srgb, ' . $primary . ' 24%, transparent)',
                    '--qiling-header-search-text'                   => $ink,
                    '--qiling-header-search-placeholder'            => $muted,
                    '--qiling-header-search-icon'                   => $primary,
                    '--qiling-header-search-transparent-bg'         => 'rgba(255, 255, 255, 0.14)',
                    '--qiling-header-search-transparent-border'     => 'rgba(255, 255, 255, 0.28)',
                    '--qiling-header-search-transparent-text'       => '#ffffff',
                    '--qiling-header-search-transparent-placeholder' => 'rgba(255, 255, 255, 0.72)',
                    '--qiling-header-search-transparent-icon'       => '#ffffff',
                    '--qiling-header-search-transparent-submit-border' => 'rgba(255, 255, 255, 0.18)',
                    '--qiling-button-bg'                            => $primary,
                    '--qiling-button-text'                          => '#ffffff',
                    '--qiling-button-hover-bg'                      => 'color-mix(in srgb, ' . $primary . ' 84%, #000000 16%)',
                    '--qiling-button-hover-text'                    => '#ffffff',
                ),
            ),
            'body_classes'    => array(
                'qiling-page-visual-preset',
                'qiling-page-visual-preset-' . str_replace( '_', '-', $key ),
            ),
            'wrapper_classes' => array(
                'qiling-page-visual-preset',
                'qiling-page-visual-preset--' . str_replace( '_', '-', $key ),
            ),
            'footer'          => array(
                'vars'         => array(
                    '--qiling-footer-main-bg'            => 'linear-gradient(180deg, color-mix(in srgb, ' . $footer_main . ' 76%, ' . $footer_deep . ' 24%) 0%, color-mix(in srgb, ' . $footer_main . ' 44%, ' . $footer_deep . ' 56%) 52%, ' . $footer_deep . ' 100%)',
                    '--qiling-footer-main-text'          => 'rgba(255, 255, 255, 0.88)',
                    '--qiling-footer-main-heading'       => 'rgba(255, 255, 255, 0.98)',
                    '--qiling-footer-main-link'          => 'rgba(255, 255, 255, 0.86)',
                    '--qiling-footer-main-link-hover'    => 'rgba(255, 255, 255, 0.98)',
                    '--qiling-footer-friend-bg'          => 'color-mix(in srgb, ' . $footer_main . ' 36%, ' . $footer_deep . ' 64%)',
                    '--qiling-footer-friend-text'        => 'rgba(255, 255, 255, 0.82)',
                    '--qiling-footer-friend-link'        => 'rgba(255, 255, 255, 0.88)',
                    '--qiling-footer-friend-link-hover'  => 'rgba(255, 255, 255, 0.98)',
                    '--qiling-footer-bottom-bg'          => $footer_deep,
                    '--qiling-footer-bottom-text'        => 'rgba(255, 255, 255, 0.78)',
                    '--qiling-footer-bottom-link'        => 'rgba(255, 255, 255, 0.9)',
                    '--qiling-footer-bottom-border'      => 'rgba(255, 255, 255, 0.12)',
                    '--qiling-footer-wave-height'        => '76px',
                    '--qiling-footer-wave-backdrop'      => $background,
                    '--qiling-footer-wave-transition-from' => $background,
                    '--qiling-footer-wave-transition-height' => '24px',
                    '--qiling-footer-wave-color'         => 'color-mix(in srgb, ' . $footer_main . ' 76%, ' . $footer_deep . ' 24%)',
                    '--qiling-footer-wave-layer-color'   => 'color-mix(in srgb, ' . $footer_main . ' 56%, ' . $background . ' 44%)',
                    '--qiling-footer-wave-layer-opacity' => '0.28',
                ),
                'wave_enabled' => true,
                'wave_style'   => 'soft',
                'effect_scope' => 'decorative',
                'classes'      => array( 'site-footer--industry-preset', 'site-footer--industry-' . str_replace( '_', '-', $key ) ),
            ),
        );
    }
}

if ( ! function_exists( 'developer_starter_get_industry_page_visual_skin_presets' ) ) {
    /**
     * Get reusable industry visual presets for page-level one-click styling.
     *
     * @return array<string,array<string,mixed>>
     */
	function developer_starter_get_industry_page_visual_skin_presets() {
		$specs = array(
			'video_entertainment' => array(
				'label'       => __( '影视娱乐', 'developer-starter' ),
				'primary'     => '#2563eb',
				'accent'      => '#10b981',
				'ink'         => '#f8fafc',
				'muted'       => 'rgba(226, 232, 240, 0.62)',
				'background'  => '#090b12',
				'header_bg'   => 'rgba(7, 9, 16, 0.90)',
				'search_bg'   => 'rgba(17, 21, 34, 0.88)',
				'footer_main' => '#0b0e16',
				'footer_deep' => '#05070c',
				'wave'        => '#111522',
				'wave_layer'  => '#0d1019',
				'announcement_bg'          => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
				'announcement_button_text' => '#2563eb',
				'templates'   => array(
					'templates/template-video-portal.php',
					'templates/template-video-ranking.php',
				),
			),
			'technology_company' => array(
                'label'       => __( '科技公司', 'developer-starter' ),
                'primary'     => '#4f7dff',
                'accent'      => '#38c9a6',
                'ink'         => '#25365f',
                'muted'       => 'rgba(37, 54, 95, 0.54)',
                'background'  => '#f7fbff',
                'header_bg'   => 'rgba(247, 251, 255, 0.88)',
                'search_bg'   => 'rgba(255, 255, 255, 0.76)',
                'footer_main' => '#4f7dff',
                'footer_deep' => '#25365f',
                'wave'        => '#dff7ff',
                'wave_layer'  => '#e8efff',
                'announcement_bg'          => 'linear-gradient(135deg, #2563eb 0%, #06b6d4 100%)',
                'announcement_button_text' => '#2563eb',
                'templates'   => array(
                    'templates/template-software-home.php',
                    'templates/template-software-intro.php',
                    'templates/template-saas-home.php',
                    'templates/template-hosting-saas-home.php',
                    'templates/template-saas-pricing.php',
                    'templates/template-developer-platform.php',
                    'templates/template-data-intelligence-bi.php',
                    'templates/template-data-showcase.php',
                    'templates/template-ai-agent-enterprise.php',
                    'templates/template-ai-product-brand.php',
                    'templates/template-enterprise-software-integrator.php',
                    'templates/template-app-download-landing.php',
                    'templates/template-cases.php',
                    'templates/template-cross-border-ecommerce-service.php',
                    'templates/template-cybersecurity-brand.php',
                    'templates/template-ecommerce-promo.php',
                    'templates/template-features-showcase.php',
                    'templates/template-interactive-product-launch.php',
                    'templates/template-landing.php',
                    'templates/template-open-source-devtools.php',
                    'templates/template-qiling-ai-multilingual-seo.php',
                    'templates/template-qiling-ai-writing-studio.php',
                    'templates/template-qiling-bbs-support-community.php',
                    'templates/template-qiling-cloud-storage-hosting.php',
                    'templates/template-qiling-doc-ocr-converter.php',
                    'templates/template-qiling-freetask-platform.php',
                    'templates/template-qiling-security-ops.php',
                    'templates/template-resource-search.php',
                    'templates/template-resources.php',
                    'templates/template-solutions.php',
                    'templates/template-video-hero.php',
				),
            ),
            'food_company' => array(
                'label'       => __( '食品餐饮', 'developer-starter' ),
                'primary'     => '#059669',
                'accent'      => '#2563eb',
                'ink'         => '#123c36',
                'muted'       => 'rgba(18, 60, 54, 0.58)',
                'background'  => '#f0fdf9',
                'header_bg'   => 'rgba(240, 253, 249, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.76)',
                'footer_main' => '#065f46',
                'footer_deep' => '#022c22',
                'wave'        => '#d1fae5',
                'wave_layer'  => '#dbeafe',
                'announcement_bg'          => 'linear-gradient(135deg, #059669 0%, #2563eb 100%)',
                'announcement_button_text' => '#047857',
                'templates'   => array(
                    'templates/template-agriculture-food.php',
                    'templates/template-restaurant.php',
                    'templates/template-chain-store-official.php',
                    'templates/template-franchise-investment.php',
                ),
            ),
            'renovation_company' => array(
                'label'       => __( '装修设计', 'developer-starter' ),
                'primary'     => '#1d4ed8',
                'accent'      => '#0f766e',
                'ink'         => '#172554',
                'muted'       => 'rgba(23, 37, 84, 0.58)',
                'background'  => '#f7fbff',
                'header_bg'   => 'rgba(247, 251, 255, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.72)',
                'footer_main' => '#1e3a8a',
                'footer_deep' => '#0f172a',
                'wave'        => '#dbeafe',
                'wave_layer'  => '#ccfbf1',
                'announcement_bg'          => 'linear-gradient(135deg, #1d4ed8 0%, #0f766e 100%)',
                'announcement_button_text' => '#1d4ed8',
                'templates'   => array(
                    'templates/template-renovation-construction.php',
                    'templates/template-architecture-design-studio.php',
                    'templates/template-interior-soft-decoration.php',
                    'templates/template-landscape-garden-design.php',
                    'templates/template-appliance-repair-service.php',
                    'templates/template-local-service-official.php',
                    'templates/template-property-management.php',
                    'templates/template-qiling-housekeeping-official.php',
                    'templates/template-real-estate-service.php',
                ),
            ),
            'education_company' => array(
                'label'       => __( '教育培训', 'developer-starter' ),
                'primary'     => '#2563eb',
                'accent'      => '#10b981',
                'ink'         => '#172554',
                'muted'       => 'rgba(23, 37, 84, 0.56)',
                'background'  => '#f5f9ff',
                'header_bg'   => 'rgba(245, 249, 255, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.76)',
                'footer_main' => '#1e40af',
                'footer_deep' => '#172554',
                'wave'        => '#dbeafe',
                'wave_layer'  => '#ccfbf1',
                'announcement_bg'          => 'linear-gradient(135deg, #2563eb 0%, #10b981 100%)',
                'announcement_button_text' => '#1d4ed8',
                'templates'   => array(
                    'templates/template-course-enrollment.php',
                    'templates/template-early-childhood-education.php',
                    'templates/template-vocational-training-school.php',
                    'templates/template-study-abroad-immigration.php',
                    'templates/template-nonprofit-organization.php',
                ),
            ),
            'healthcare_company' => array(
                'label'       => __( '医疗健康', 'developer-starter' ),
                'primary'     => '#10b981',
                'accent'      => '#0ea5e9',
                'ink'         => '#164e63',
                'muted'       => 'rgba(22, 78, 99, 0.56)',
                'background'  => '#f0fdfa',
                'header_bg'   => 'rgba(240, 253, 250, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.78)',
                'footer_main' => '#10b981',
                'footer_deep' => '#164e63',
                'wave'        => '#ccfbf1',
                'wave_layer'  => '#dbeafe',
                'announcement_bg'          => 'linear-gradient(135deg, #10b981 0%, #0ea5e9 100%)',
                'announcement_button_text' => '#059669',
                'templates'   => array(
                    'templates/template-dental-clinic.php',
                    'templates/template-healthcare-clinic.php',
                    'templates/template-medical-device.php',
                    'templates/template-wellness-center.php',
                    'templates/template-health-supplements.php',
                    'templates/template-intimate-wellness.php',
                    'templates/template-senior-care-center.php',
                    'templates/template-postpartum-care-center.php',
                    'templates/template-pet.php',
                    'templates/template-psychological-counseling.php',
                ),
            ),
            'law_company' => array(
                'label'       => __( '法律咨询', 'developer-starter' ),
                'primary'     => '#1d4ed8',
                'accent'      => '#059669',
                'ink'         => '#172554',
                'muted'       => 'rgba(23, 37, 84, 0.58)',
                'background'  => '#f8fafc',
                'header_bg'   => 'rgba(248, 250, 252, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.78)',
                'footer_main' => '#1d4ed8',
                'footer_deep' => '#172554',
                'wave'        => '#dbeafe',
                'wave_layer'  => '#d1fae5',
                'announcement_bg'          => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
                'announcement_button_text' => '#1d4ed8',
                'templates'   => array(
                    'templates/template-law-firm.php',
                    'templates/template-intellectual-property-service.php',
                    'templates/template-government-public-service.php',
                ),
            ),
            'beauty_company' => array(
                'label'       => __( '美业护肤', 'developer-starter' ),
                'primary'     => '#0f766e',
                'accent'      => '#2563eb',
                'ink'         => '#134e4a',
                'muted'       => 'rgba(19, 78, 74, 0.56)',
                'background'  => '#f0fdfa',
                'header_bg'   => 'rgba(240, 253, 250, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.76)',
                'footer_main' => '#115e59',
                'footer_deep' => '#042f2e',
                'wave'        => '#ccfbf1',
                'wave_layer'  => '#dbeafe',
                'announcement_bg'          => 'linear-gradient(135deg, #0f766e 0%, #2563eb 100%)',
                'announcement_button_text' => '#0f766e',
                'templates'   => array(
                    'templates/template-beauty-salon.php',
                    'templates/template-medical-beauty.php',
                    'templates/template-fashion-brand.php',
                    'templates/template-yoga-studio.php',
                    'templates/template-gym-fitness.php',
                    'templates/template-qiling-friends-matchmaking.php',
                ),
            ),
            'manufacturing_company' => array(
                'label'       => __( '工业制造', 'developer-starter' ),
                'primary'     => '#2563eb',
                'accent'      => '#059669',
                'ink'         => '#1e293b',
                'muted'       => 'rgba(30, 41, 59, 0.56)',
                'background'  => '#f1f5f9',
                'header_bg'   => 'rgba(241, 245, 249, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.78)',
                'footer_main' => '#2563eb',
                'footer_deep' => '#1e293b',
                'wave'        => '#dbeafe',
                'wave_layer'  => '#e2e8f0',
                'announcement_bg'          => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
                'announcement_button_text' => '#2563eb',
                'templates'   => array(
                    'templates/template-manufacturing-factory.php',
                    'templates/template-industrial-automation-robotics.php',
                    'templates/template-semiconductor-electronics.php',
                    'templates/template-industrial-park.php',
                    'templates/template-lab-instrument.php',
                    'templates/template-auto-service.php',
                    'templates/template-energy-environment.php',
                    'templates/template-ev-charging-station.php',
                    'templates/template-foreign-trade-b2b.php',
                    'templates/template-logistics-supply-chain.php',
                    'templates/template-overseas-warehouse-supply-chain.php',
                    'templates/template-products.php',
                    'templates/template-qiling-recycling-official.php',
                    'templates/template-solar-storage-equipment.php',
                    'templates/template-water-treatment-environmental.php',
                ),
            ),
            'finance_company' => array(
                'label'       => __( '财税金融', 'developer-starter' ),
                'primary'     => '#0f766e',
                'accent'      => '#2563eb',
                'ink'         => '#134e4a',
                'muted'       => 'rgba(19, 78, 74, 0.56)',
                'background'  => '#f5fbf8',
                'header_bg'   => 'rgba(245, 251, 248, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.78)',
                'footer_main' => '#0f766e',
                'footer_deep' => '#134e4a',
                'wave'        => '#ccfbf1',
                'wave_layer'  => '#dbeafe',
                'announcement_bg'          => 'linear-gradient(135deg, #0f766e 0%, #2563eb 100%)',
                'announcement_button_text' => '#0f766e',
                'templates'   => array(
                    'templates/template-finance-consulting.php',
                    'templates/template-accounting-tax-service.php',
                    'templates/template-qiling-escrow-platform.php',
                    'templates/template-recruitment-hr-service.php',
                ),
            ),
            'photography_company' => array(
                'label'       => __( '摄影设计', 'developer-starter' ),
                'primary'     => '#2563eb',
                'accent'      => '#059669',
                'ink'         => '#172554',
                'muted'       => 'rgba(23, 37, 84, 0.58)',
                'background'  => '#f7fafc',
                'header_bg'   => 'rgba(247, 250, 252, 0.9)',
                'search_bg'   => 'rgba(255, 255, 255, 0.76)',
                'footer_main' => '#1e3a8a',
                'footer_deep' => '#0f172a',
                'wave'        => '#dbeafe',
                'wave_layer'  => '#d1fae5',
                'announcement_bg'          => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
                'announcement_button_text' => '#1d4ed8',
                'templates'   => array(
                    'templates/template-wedding-photography.php',
                    'templates/template-personal-ip-home.php',
                    'templates/template-marketing-pr-agency.php',
                    'templates/template-blog.php',
                    'templates/template-conference-event-service.php',
                    'templates/template-event-exhibition.php',
                    'templates/template-homestay.php',
                    'templates/template-mcn-live-commerce.php',
                    'templates/template-news.php',
                    'templates/template-qiling-image-studio.php',
                    'templates/template-qiling-wallpaper-gallery.php',
                    'templates/template-resume.php',
                    'templates/template-topic.php',
                    'templates/template-travel.php',
                ),
            ),
        );

        $presets = array();
        foreach ( $specs as $key => $spec ) {
            $presets[ $key ] = developer_starter_build_industry_page_visual_skin( $key, $spec );
        }

        return $presets;
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_skin' ) ) {
    /**
     * Resolve a visual skin config by key.
     *
     * @param string $skin_key Skin key.
     * @return array<string,mixed>|null
     */
    function developer_starter_get_page_visual_skin( $skin_key ) {
        $skin_key = sanitize_key( (string) $skin_key );
        if ( '' === $skin_key ) {
            return null;
        }

        $skins = developer_starter_get_page_visual_skins();
        if ( empty( $skins[ $skin_key ] ) || ! is_array( $skins[ $skin_key ] ) ) {
            return null;
        }

        $skin        = $skins[ $skin_key ];
        $skin['key'] = $skin_key;

        return $skin;
    }
}

if ( ! function_exists( 'developer_starter_normalize_page_skin_class_list' ) ) {
    /**
     * Sanitize a CSS class list.
     *
     * @param array<int,string>|string $classes Class list.
     * @return array<int,string>
     */
    function developer_starter_normalize_page_skin_class_list( $classes ) {
        if ( is_string( $classes ) ) {
            $classes = preg_split( '/\s+/', trim( $classes ) );
        }

        if ( ! is_array( $classes ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $classes as $class_name ) {
            $class_name = sanitize_html_class( (string) $class_name );
            if ( '' !== $class_name ) {
                $normalized[] = $class_name;
            }
        }

        return array_values( array_unique( $normalized ) );
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_skin_for_template' ) ) {
    /**
     * Resolve the visual skin config for a page template.
     *
     * @param string $template Page template path.
     * @return array<string,mixed>|null
     */
    function developer_starter_get_page_visual_skin_for_template( $template ) {
        $template = ltrim( (string) $template, '/' );
        if ( '' === $template ) {
            return null;
        }

        foreach ( developer_starter_get_page_visual_skins() as $skin_key => $skin ) {
            if ( ! is_array( $skin ) ) {
                continue;
            }

            $templates = isset( $skin['templates'] ) && is_array( $skin['templates'] ) ? $skin['templates'] : array();
            $templates = array_map(
                static function ( $template_path ) {
                    return ltrim( (string) $template_path, '/' );
                },
                $templates
            );

            if ( in_array( $template, $templates, true ) ) {
                $skin['key'] = sanitize_key( (string) $skin_key );
                return $skin;
            }
        }

        return null;
    }
}

if ( ! function_exists( 'developer_starter_get_current_page_visual_skin' ) ) {
    /**
     * Resolve the visual skin config for the current queried page.
     *
     * @return array<string,mixed>|null
     */
    function developer_starter_get_current_page_visual_skin() {
        if ( ! function_exists( 'is_singular' ) || ! is_singular( 'page' ) ) {
            return null;
        }

        $post_id = function_exists( 'get_queried_object_id' ) ? (int) get_queried_object_id() : 0;
        if ( $post_id <= 0 || ! function_exists( 'get_page_template_slug' ) ) {
            return null;
        }

        if ( function_exists( 'developer_starter_get_post_page_visual_style' ) ) {
            $page_visual_style = developer_starter_get_post_page_visual_style( $post_id );
            $page_visual_mode  = is_array( $page_visual_style ) && isset( $page_visual_style['mode'] ) ? (string) $page_visual_style['mode'] : '';
            if ( 'global' === $page_visual_mode ) {
                return null;
            }
            if ( is_array( $page_visual_style ) && 'custom' === $page_visual_mode && ! empty( $page_visual_style['preset'] ) ) {
                $preset_skin = developer_starter_get_page_visual_skin( $page_visual_style['preset'] );
                if ( is_array( $preset_skin ) ) {
                    return $preset_skin;
                }
                if ( function_exists( 'developer_starter_get_page_visual_custom_preset_skin' ) ) {
                    $custom_preset_skin = developer_starter_get_page_visual_custom_preset_skin( $page_visual_style['preset'] );
                    if ( is_array( $custom_preset_skin ) ) {
                        return $custom_preset_skin;
                    }
                }
            }
        }

        return developer_starter_get_page_visual_skin_for_template( get_page_template_slug( $post_id ) );
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_style_meta_key' ) ) {
    /**
     * Get the single post meta key used for page-level visual overrides.
     *
     * @return string
     */
    function developer_starter_get_page_visual_style_meta_key() {
        return '_qiling_page_visual_style';
    }
}

if ( ! function_exists( 'developer_starter_sanitize_page_visual_rest_flag' ) ) {
    /**
     * Sanitize simple page visual toggle meta values used by the editor REST API.
     *
     * @param mixed $value Raw meta value.
     * @return string
     */
    function developer_starter_sanitize_page_visual_rest_flag( $value ) {
        if ( is_bool( $value ) ) {
            return $value ? '1' : '';
        }

        return '1' === (string) $value ? '1' : '';
    }
}

if ( ! function_exists( 'developer_starter_register_page_visual_rest_meta' ) ) {
    /**
     * Register page-level visual settings so the block editor can persist them.
     *
     * Classic meta boxes still save through save_post; this REST registration keeps
     * the same controls reliable in the block editor's autosave/update flow.
     *
     * @return void
     */
    function developer_starter_register_page_visual_rest_meta() {
        if ( ! function_exists( 'register_post_meta' ) ) {
            return;
        }

        $auth_callback = static function ( $allowed, $meta_key, $post_id ) {
            unset( $allowed, $meta_key );
            return current_user_can( 'edit_post', absint( $post_id ) );
        };

        register_post_meta(
            'page',
            '_qiling_hide_page_header',
            array(
                'single'            => true,
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'developer_starter_sanitize_page_visual_rest_flag',
                'auth_callback'     => $auth_callback,
                'show_in_rest'      => true,
            )
        );

        register_post_meta(
            'page',
            '_qiling_transparent_header',
            array(
                'single'            => true,
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'developer_starter_sanitize_page_visual_rest_flag',
                'auth_callback'     => $auth_callback,
                'show_in_rest'      => true,
            )
        );

        register_post_meta(
            'page',
            developer_starter_get_page_visual_style_meta_key(),
            array(
                'single'            => true,
                'type'              => 'object',
                'default'           => array(),
                'sanitize_callback' => 'developer_starter_sanitize_page_visual_style_settings',
                'auth_callback'     => $auth_callback,
                'show_in_rest'      => array(
                    'schema' => array(
                        'type'                 => 'object',
                        'additionalProperties' => true,
                    ),
                ),
            )
        );
    }
}
add_action( 'init', 'developer_starter_register_page_visual_rest_meta', 20 );

if ( ! function_exists( 'developer_starter_get_page_visual_custom_presets_option_key' ) ) {
    /**
     * Get the option key used to store user-created page visual presets.
     *
     * @return string
     */
    function developer_starter_get_page_visual_custom_presets_option_key() {
        return 'qiling_page_visual_custom_presets';
    }
}

if ( ! function_exists( 'developer_starter_filter_page_visual_vars_by_scope' ) ) {
    /**
     * Filter page visual CSS variables for a preview/runtime scope.
     *
     * @param array<string,string> $vars  CSS variables.
     * @param string               $scope Scope key.
     * @return array<string,string>
     */
    function developer_starter_filter_page_visual_vars_by_scope( $vars, $scope = 'all' ) {
        if ( ! is_array( $vars ) ) {
            return array();
        }

        $scope = sanitize_key( (string) $scope );
        if ( '' === $scope || 'all' === $scope ) {
            return $vars;
        }

        $prefixes = array();
        if ( 'header' === $scope ) {
            $prefixes = array( '--qiling-header-', '--qiling-page-', '--qiling-button-', '--color-primary', '--color-primary-dark' );
        } elseif ( 'footer' === $scope ) {
            $prefixes = array( '--qiling-footer-', '--qiling-page-', '--qiling-button-', '--color-primary', '--color-primary-dark' );
        }

        if ( empty( $prefixes ) ) {
            return $vars;
        }

        $filtered = array();
        foreach ( $vars as $name => $value ) {
            $name = is_string( $name ) ? trim( $name ) : '';
            if ( '' === $name ) {
                continue;
            }

            foreach ( $prefixes as $prefix ) {
                if ( 0 === strpos( $name, $prefix ) ) {
                    $filtered[ $name ] = $value;
                    break;
                }
            }
        }

        return $filtered;
    }
}

if ( ! function_exists( 'developer_starter_normalize_page_visual_custom_preset_skin' ) ) {
    /**
     * Sanitize a reusable visual skin snapshot stored with user presets.
     *
     * @param mixed  $skin       Raw skin data.
     * @param string $preset_key Custom preset key.
     * @param string $label      Custom preset label.
     * @param array  $vars       Compiled preset CSS variables.
     * @return array<string,mixed>
     */
    function developer_starter_normalize_page_visual_custom_preset_skin( $skin, $preset_key = '', $label = '', $vars = array() ) {
        $skin       = is_array( $skin ) ? $skin : array();
        $preset_key = sanitize_key( (string) $preset_key );
        $label      = sanitize_text_field( wp_strip_all_tags( (string) $label ) );
        $vars       = is_array( $vars ) ? $vars : array();

        $normalized = array(
            'key'             => $preset_key,
            'label'           => '' !== $label ? $label : $preset_key,
            'body_classes'    => array( 'qiling-page-visual-custom-preset' ),
            'wrapper_classes' => array( 'qiling-page-visual-custom-preset' ),
        );

        if ( '' !== $preset_key ) {
            $normalized['body_classes'][]    = 'qiling-page-visual-custom-preset-' . str_replace( '_', '-', $preset_key );
            $normalized['wrapper_classes'][] = 'qiling-page-visual-custom-preset--' . str_replace( '_', '-', $preset_key );
        }

        if ( ! empty( $skin['style_handle'] ) && is_scalar( $skin['style_handle'] ) ) {
            $style_handle = sanitize_key( (string) $skin['style_handle'] );
            if ( '' !== $style_handle ) {
                $normalized['style_handle'] = $style_handle;
            }
        }

        if ( ! empty( $skin['style_path'] ) && is_scalar( $skin['style_path'] ) ) {
            $style_path = ltrim( (string) $skin['style_path'], '/' );
            if ( '' !== $style_path && false === strpos( $style_path, '..' ) ) {
                $normalized['style_path'] = $style_path;
            }
        }

        foreach ( array( 'body_classes', 'wrapper_classes' ) as $class_group ) {
            if ( ! empty( $skin[ $class_group ] ) ) {
                $normalized[ $class_group ] = array_merge(
                    $normalized[ $class_group ],
                    developer_starter_normalize_page_skin_class_list( $skin[ $class_group ] )
                );
            }
            $normalized[ $class_group ] = array_values( array_unique( $normalized[ $class_group ] ) );
        }

        $header_vars = developer_starter_filter_page_visual_vars_by_scope( $vars, 'header' );
        if ( ! empty( $header_vars ) ) {
            $normalized['header'] = array( 'vars' => $header_vars );
        }

        $footer = array();
        if ( ! empty( $skin['footer'] ) && is_array( $skin['footer'] ) ) {
            $source_footer = $skin['footer'];
            if ( array_key_exists( 'wave_enabled', $source_footer ) ) {
                $footer['wave_enabled'] = in_array( strtolower( (string) $source_footer['wave_enabled'] ), array( '1', 'true', 'yes', 'on' ), true ) || true === $source_footer['wave_enabled'];
            }
            if ( ! empty( $source_footer['wave_style'] ) ) {
                $wave_style = sanitize_key( (string) $source_footer['wave_style'] );
                if ( in_array( $wave_style, array( 'single', 'double', 'soft', 'slope' ), true ) ) {
                    $footer['wave_style'] = $wave_style;
                }
            }
            if ( ! empty( $source_footer['effect_scope'] ) ) {
                $effect_scope = sanitize_key( (string) $source_footer['effect_scope'] );
                if ( in_array( $effect_scope, array( 'main', 'all', 'decorative' ), true ) ) {
                    $footer['effect_scope'] = $effect_scope;
                }
            }
            if ( ! empty( $source_footer['classes'] ) ) {
                $footer['classes'] = developer_starter_normalize_page_skin_class_list( $source_footer['classes'] );
            }
        }

        $footer_vars = developer_starter_filter_page_visual_vars_by_scope( $vars, 'footer' );
        if ( ! empty( $footer_vars ) ) {
            $footer['vars'] = $footer_vars;
        }
        if ( ! empty( $footer ) ) {
            $normalized['footer'] = $footer;
        }

        return $normalized;
    }
}

if ( ! function_exists( 'developer_starter_normalize_page_visual_custom_presets' ) ) {
    /**
     * Normalize user-created page visual presets from the options table.
     *
     * @param mixed $raw Raw option value.
     * @return array<string,array<string,mixed>>
     */
    function developer_starter_normalize_page_visual_custom_presets( $raw ) {
        if ( ! is_array( $raw ) ) {
            return array();
        }

        $presets = array();
        foreach ( $raw as $preset_key => $preset ) {
            $preset_key = sanitize_key( (string) $preset_key );
            if ( '' === $preset_key || ! is_array( $preset ) ) {
                continue;
            }

            $label = isset( $preset['label'] ) && is_scalar( $preset['label'] )
                ? sanitize_text_field( wp_strip_all_tags( (string) $preset['label'] ) )
                : '';
            if ( '' === $label ) {
                $label = $preset_key;
            }

            $vars = array();
            if ( ! empty( $preset['vars'] ) && is_array( $preset['vars'] ) ) {
                foreach ( $preset['vars'] as $name => $value ) {
                    $name = is_string( $name ) ? trim( $name ) : '';
                    if ( ! preg_match( '/^--[a-z0-9_-]+$/i', $name ) || ! is_scalar( $value ) ) {
                        continue;
                    }

                    $value = developer_starter_sanitize_page_visual_style_css_value( $value );
                    if ( '' !== $value ) {
                        $vars[ $name ] = $value;
                    }
                }
            }

            $presets[ $preset_key ] = array(
                'label'      => $label,
                'settings'   => isset( $preset['settings'] ) && is_array( $preset['settings'] ) ? $preset['settings'] : array(),
                'vars'       => $vars,
                'skin'       => developer_starter_normalize_page_visual_custom_preset_skin(
                    isset( $preset['skin'] ) && is_array( $preset['skin'] ) ? $preset['skin'] : array(),
                    $preset_key,
                    $label,
                    $vars
                ),
                'created_at' => isset( $preset['created_at'] ) && is_scalar( $preset['created_at'] ) ? absint( $preset['created_at'] ) : 0,
                'updated_at' => isset( $preset['updated_at'] ) && is_scalar( $preset['updated_at'] ) ? absint( $preset['updated_at'] ) : 0,
            );
        }

        return $presets;
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_custom_presets' ) ) {
    /**
     * Get user-created page visual presets.
     *
     * @return array<string,array<string,mixed>>
     */
    function developer_starter_get_page_visual_custom_presets() {
        $presets = get_option( developer_starter_get_page_visual_custom_presets_option_key(), array() );
        return developer_starter_normalize_page_visual_custom_presets( $presets );
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_custom_preset' ) ) {
    /**
     * Get one user-created page visual preset.
     *
     * @param string $preset_key Preset key.
     * @return array<string,mixed>|null
     */
    function developer_starter_get_page_visual_custom_preset( $preset_key ) {
        $preset_key = sanitize_key( (string) $preset_key );
        if ( '' === $preset_key ) {
            return null;
        }

        $presets = developer_starter_get_page_visual_custom_presets();
        if ( empty( $presets[ $preset_key ] ) || ! is_array( $presets[ $preset_key ] ) ) {
            return null;
        }

        return $presets[ $preset_key ];
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_custom_preset_skin' ) ) {
    /**
     * Get the reusable skin snapshot for a user-created page visual preset.
     *
     * @param string $preset_key Preset key.
     * @return array<string,mixed>|null
     */
    function developer_starter_get_page_visual_custom_preset_skin( $preset_key ) {
        $preset = developer_starter_get_page_visual_custom_preset( $preset_key );
        if ( ! is_array( $preset ) || empty( $preset['skin'] ) || ! is_array( $preset['skin'] ) ) {
            return null;
        }

        $skin        = $preset['skin'];
        $skin['key'] = sanitize_key( (string) $preset_key );

        return $skin;
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_custom_preset_key_from_label' ) ) {
    /**
     * Build a stable preset key from a user-facing label.
     *
     * @param string $label Preset label.
     * @return string
     */
    function developer_starter_get_page_visual_custom_preset_key_from_label( $label ) {
        $label = sanitize_text_field( wp_strip_all_tags( (string) $label ) );
        if ( '' === $label ) {
            return '';
        }

        $slug = sanitize_key( sanitize_title( $label ) );
        if ( '' === $slug ) {
            $slug = substr( md5( $label ), 0, 10 );
        }

        return 'custom_' . $slug;
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_custom_preset_vars_array' ) ) {
    /**
     * Get CSS variables stored for a user-created visual preset.
     *
     * @param string $preset_key Preset key.
     * @param string $scope      Optional scope key.
     * @return array<string,string>
     */
    function developer_starter_get_page_visual_custom_preset_vars_array( $preset_key, $scope = 'all' ) {
        $preset = developer_starter_get_page_visual_custom_preset( $preset_key );
        if ( ! is_array( $preset ) || empty( $preset['vars'] ) || ! is_array( $preset['vars'] ) ) {
            return array();
        }

        $vars = array();
        foreach ( $preset['vars'] as $name => $value ) {
            $name = is_string( $name ) ? trim( $name ) : '';
            if ( ! preg_match( '/^--[a-z0-9_-]+$/i', $name ) || ! is_scalar( $value ) ) {
                continue;
            }

            $value = developer_starter_sanitize_page_visual_style_css_value( $value );
            if ( '' !== $value ) {
                $vars[ $name ] = $value;
            }
        }

        return developer_starter_filter_page_visual_vars_by_scope( $vars, $scope );
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_preset_vars_array' ) ) {
    /**
     * Get CSS variables for a built-in or user-created visual preset.
     *
     * @param string $preset_key Preset key.
     * @param string $scope      Optional scope key.
     * @return array<string,string>
     */
    function developer_starter_get_page_visual_preset_vars_array( $preset_key, $scope = 'all' ) {
        $preset_key = sanitize_key( (string) $preset_key );
        if ( '' === $preset_key ) {
            return array();
        }

        $skin = developer_starter_get_page_visual_skin( $preset_key );
        if ( is_array( $skin ) ) {
            return developer_starter_get_page_visual_skin_vars_array( $skin, $scope );
        }

        return developer_starter_get_page_visual_custom_preset_vars_array( $preset_key, $scope );
    }
}

if ( ! function_exists( 'developer_starter_page_visual_preset_exists' ) ) {
    /**
     * Check whether a built-in or user-created visual preset exists.
     *
     * @param string $preset_key Preset key.
     * @return bool
     */
    function developer_starter_page_visual_preset_exists( $preset_key ) {
        $preset_key = sanitize_key( (string) $preset_key );
        if ( '' === $preset_key ) {
            return false;
        }

        return (bool) developer_starter_get_page_visual_skin( $preset_key ) || (bool) developer_starter_get_page_visual_custom_preset( $preset_key );
    }
}

if ( ! function_exists( 'developer_starter_save_page_visual_custom_preset' ) ) {
    /**
     * Save current page visual settings as a reusable preset.
     *
     * @param string $label    Preset label.
     * @param mixed  $settings Raw page visual settings.
     * @return string Saved preset key, or empty string on failure.
     */
    function developer_starter_save_page_visual_custom_preset( $label, $settings ) {
        $label = sanitize_text_field( wp_strip_all_tags( (string) $label ) );
        if ( '' === $label ) {
            return '';
        }

        $preset_key = developer_starter_get_page_visual_custom_preset_key_from_label( $label );
        if ( '' === $preset_key ) {
            return '';
        }

        $settings         = is_array( $settings ) ? $settings : array();
        $settings['mode'] = 'custom';
        $settings         = developer_starter_sanitize_page_visual_style_settings( $settings );

        $vars        = array();
        $source_skin = array();
        if ( ! empty( $settings['preset'] ) ) {
            $vars = developer_starter_get_page_visual_preset_vars_array( $settings['preset'], 'all' );
            $source_skin = developer_starter_get_page_visual_skin( $settings['preset'] );
            if ( ! is_array( $source_skin ) && function_exists( 'developer_starter_get_page_visual_custom_preset_skin' ) ) {
                $source_skin = developer_starter_get_page_visual_custom_preset_skin( $settings['preset'] );
            }
        }
        $vars = array_merge( $vars, developer_starter_get_page_visual_style_custom_vars_array( $settings, 'all' ) );
        if ( empty( $vars ) ) {
            return '';
        }

        $skin = developer_starter_normalize_page_visual_custom_preset_skin(
            is_array( $source_skin ) ? $source_skin : array(),
            $preset_key,
            $label,
            $vars
        );

        $presets   = developer_starter_get_page_visual_custom_presets();
        $timestamp = time();

        $presets[ $preset_key ] = array(
            'label'      => $label,
            'settings'   => $settings,
            'vars'       => $vars,
            'skin'       => $skin,
            'created_at' => isset( $presets[ $preset_key ]['created_at'] ) ? absint( $presets[ $preset_key ]['created_at'] ) : $timestamp,
            'updated_at' => $timestamp,
        );

        update_option( developer_starter_get_page_visual_custom_presets_option_key(), $presets, false );

        return $preset_key;
    }
}

if ( ! function_exists( 'developer_starter_update_page_visual_custom_preset_label' ) ) {
    /**
     * Update the label for a user-created page visual preset.
     *
     * @param string $preset_key Preset key.
     * @param string $label      New label.
     * @return bool
     */
    function developer_starter_update_page_visual_custom_preset_label( $preset_key, $label ) {
        $preset_key = sanitize_key( (string) $preset_key );
        $label      = sanitize_text_field( wp_strip_all_tags( (string) $label ) );
        if ( '' === $preset_key || '' === $label ) {
            return false;
        }

        $presets = developer_starter_get_page_visual_custom_presets();
        if ( empty( $presets[ $preset_key ] ) || ! is_array( $presets[ $preset_key ] ) ) {
            return false;
        }

        $presets[ $preset_key ]['label']      = $label;
        $presets[ $preset_key ]['updated_at'] = time();

        update_option( developer_starter_get_page_visual_custom_presets_option_key(), $presets, false );

        return true;
    }
}

if ( ! function_exists( 'developer_starter_delete_page_visual_custom_preset' ) ) {
    /**
     * Delete a user-created page visual preset.
     *
     * @param string $preset_key Preset key.
     * @return bool
     */
    function developer_starter_delete_page_visual_custom_preset( $preset_key ) {
        $preset_key = sanitize_key( (string) $preset_key );
        if ( '' === $preset_key ) {
            return false;
        }

        $presets = developer_starter_get_page_visual_custom_presets();
        if ( empty( $presets[ $preset_key ] ) ) {
            return false;
        }

        unset( $presets[ $preset_key ] );
        update_option( developer_starter_get_page_visual_custom_presets_option_key(), $presets, false );

        return true;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_page_visual_style_css_value' ) ) {
    /**
     * Sanitize a page visual CSS value.
     *
     * @param mixed  $value    Raw value.
     * @param string $fallback Fallback.
     * @return string
     */
    function developer_starter_sanitize_page_visual_style_css_value( $value, $fallback = '' ) {
        if ( function_exists( 'developer_starter_sanitize_footer_visual_css_value' ) ) {
            return developer_starter_sanitize_footer_visual_css_value( $value, $fallback );
        }

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

if ( ! function_exists( 'developer_starter_get_page_visual_style_fields' ) ) {
    /**
     * Get page-level visual fields and CSS variable mappings.
     *
     * @return array<string,array<string,mixed>>
     */
    function developer_starter_get_page_visual_style_fields() {
        return apply_filters(
            'developer_starter_page_visual_style_fields',
            array(
                'colors'  => array(
                    'label'       => __( '页面基础色', 'developer-starter' ),
                    'description' => __( '控制当前页面主色、辅助色和基础背景。', 'developer-starter' ),
                    'fields'      => array(
                        'primary'    => array(
                            'label'       => __( '页面主色', 'developer-starter' ),
                            'placeholder' => '#4f7dff',
                            'vars'        => array( '--qiling-page-primary', '--qiling-page-accent', '--color-primary' ),
                        ),
                        'accent'     => array(
                            'label'       => __( '页面辅助色', 'developer-starter' ),
                            'placeholder' => '#38c9a6',
                            'vars'        => array( '--qiling-page-accent-2' ),
                        ),
                        'background' => array(
                            'label'       => __( '页面背景色', 'developer-starter' ),
                            'placeholder' => '#f7fbff',
                            'vars'        => array( '--qiling-page-bg', '--qiling-page-background' ),
                        ),
                        'text'       => array(
                            'label'       => __( '页面文字色', 'developer-starter' ),
                            'placeholder' => '#25365f',
                            'vars'        => array( '--qiling-page-text' ),
                        ),
                    ),
                ),
                'canvas' => array(
                    'label'       => __( '页面连续画布', 'developer-starter' ),
                    'description' => __( '控制正文模块之间以及正文与页脚之间的整体衔接。标准分区保持各模块原有布局；连续画布适合一体化页面。', 'developer-starter' ),
                    'fields'      => array(
                        'mode' => array(
                            'label'       => __( '画布模式', 'developer-starter' ),
                            'type'        => 'select',
                            'options'     => array(
                                'standard'   => __( '标准分区', 'developer-starter' ),
                                'continuous' => __( '一体化连续画布', 'developer-starter' ),
                            ),
                        ),
                        'background' => array(
                            'label'       => __( '画布背景', 'developer-starter' ),
                            'placeholder' => 'var(--qiling-page-bg)',
                            'vars'        => array( '--qiling-canvas-bg' ),
                        ),
                        'footer_transition' => array(
                            'label'       => __( '正文到页脚衔接色', 'developer-starter' ),
                            'placeholder' => 'var(--qiling-canvas-bg)',
                            'vars'        => array( '--qiling-canvas-footer-transition', '--qiling-footer-wave-backdrop', '--qiling-footer-wave-transition-from' ),
                        ),
                    ),
                ),
                'header'  => array(
                    'label'       => __( '顶部菜单栏', 'developer-starter' ),
                    'description' => __( '分别控制常规/滚动状态和首屏透明状态。透明状态文字留空时，会根据首屏标题明暗自动选择深色或白色。', 'developer-starter' ),
                    'fields'      => array(
                        'background'         => array(
                            'label'       => __( '常规/滚动顶部背景', 'developer-starter' ),
                            'placeholder' => 'rgba(255,255,255,0.9)',
                            'vars'        => array( '--qiling-header-bg' ),
                        ),
                        'text'               => array(
                            'label'       => __( '菜单文字色', 'developer-starter' ),
                            'placeholder' => '#25365f',
                            'vars'        => array( '--qiling-header-text', '--qiling-header-nav-link', '--qiling-header-scrolled-text', '--qiling-header-scrolled-nav-link' ),
                        ),
                        'transparent_text'   => array(
                            'label'       => __( '首屏透明状态文字色', 'developer-starter' ),
                            'placeholder' => '#25365f',
                            'vars'        => array( '--qiling-header-transparent-text', '--qiling-header-transparent-nav-link' ),
                        ),
                        'nav_hover_bg'       => array(
                            'label'       => __( '菜单悬停背景', 'developer-starter' ),
                            'placeholder' => 'linear-gradient(135deg,#4f7dff,#38c9a6)',
                            'vars'        => array( '--qiling-header-nav-hover-bg', '--qiling-header-scrolled-nav-hover-bg' ),
                        ),
                        'nav_hover_text'     => array(
                            'label'       => __( '菜单悬停文字', 'developer-starter' ),
                            'placeholder' => '#ffffff',
                            'vars'        => array( '--qiling-header-nav-hover-text', '--qiling-header-scrolled-nav-hover-text' ),
                        ),
                        'search_bg'          => array(
                            'label'       => __( '搜索框背景', 'developer-starter' ),
                            'placeholder' => 'rgba(255,255,255,0.78)',
                            'vars'        => array( '--qiling-header-search-bg', '--qiling-header-search-transparent-bg' ),
                        ),
                        'search_text'        => array(
                            'label'       => __( '搜索框文字', 'developer-starter' ),
                            'placeholder' => '#25365f',
                            'vars'        => array( '--qiling-header-search-text', '--qiling-header-search-transparent-text' ),
                        ),
                        'search_placeholder' => array(
                            'label'       => __( '搜索占位文案', 'developer-starter' ),
                            'placeholder' => 'rgba(37,54,95,0.52)',
                            'vars'        => array( '--qiling-header-search-placeholder', '--qiling-header-search-transparent-placeholder' ),
                        ),
                        'search_icon'        => array(
                            'label'       => __( '搜索图标颜色', 'developer-starter' ),
                            'placeholder' => '#4f7dff',
                            'vars'        => array( '--qiling-header-search-icon', '--qiling-header-search-transparent-icon' ),
                        ),
                        'phone_bg'           => array(
                            'label'       => __( '联系电话背景', 'developer-starter' ),
                            'placeholder' => 'rgba(255,255,255,0.78)',
                            'vars'        => array( '--qiling-header-phone-normal-bg', '--qiling-header-phone-transparent-bg' ),
                        ),
                        'phone_text'         => array(
                            'label'       => __( '联系电话文字', 'developer-starter' ),
                            'placeholder' => '#25365f',
                            'vars'        => array( '--qiling-header-phone-normal-text', '--qiling-header-phone-transparent-text' ),
                        ),
                    ),
                ),
                'footer'  => array(
                    'label'       => __( '底部和波浪', 'developer-starter' ),
                    'description' => __( '这里是当前页显式覆盖，优先于全局页脚和所选页面皮肤；留空则按“页脚策略”继承。', 'developer-starter' ),
                    'fields'      => array(
                        'background'         => array(
                            'label'       => __( '关于我们/联系区域背景', 'developer-starter' ),
                            'placeholder' => '#4f7dff',
                            'vars'        => array( '--qiling-footer-main-bg' ),
                        ),
                        'text'               => array(
                            'label'       => __( '关于我们/联系区域文字', 'developer-starter' ),
                            'placeholder' => 'rgba(255,255,255,0.9)',
                            'vars'        => array(
                                '--qiling-footer-main-text',
                                '--qiling-footer-main-heading',
                                '--qiling-footer-main-link',
                                '--qiling-footer-main-link-hover',
                            ),
                        ),
                        'friend_background'  => array(
                            'label'       => __( '友情链接区域背景', 'developer-starter' ),
                            'placeholder' => '#e3f7f3',
                            'vars'        => array( '--qiling-footer-friend-bg' ),
                        ),
                        'friend_text'        => array(
                            'label'       => __( '友情链接区域文字', 'developer-starter' ),
                            'placeholder' => '#52617e',
                            'vars'        => array( '--qiling-footer-friend-text', '--qiling-footer-friend-link', '--qiling-footer-friend-link-hover' ),
                        ),
                        'bottom_background'  => array(
                            'label'       => __( '版权/ICP 备案区域背景', 'developer-starter' ),
                            'placeholder' => '#25365f',
                            'vars'        => array( '--qiling-footer-bottom-bg' ),
                        ),
                        'bottom_text'        => array(
                            'label'       => __( '版权/ICP 备案文案颜色', 'developer-starter' ),
                            'placeholder' => 'rgba(255,255,255,0.82)',
                            'vars'        => array( '--qiling-footer-bottom-text' ),
                        ),
                        'bottom_link'        => array(
                            'label'       => __( 'ICP/公安备案链接颜色', 'developer-starter' ),
                            'placeholder' => '#3d5b9d',
                            'vars'        => array( '--qiling-footer-bottom-link' ),
                        ),
                        'bottom_link_hover'  => array(
                            'label'       => __( '备案链接悬停颜色', 'developer-starter' ),
                            'placeholder' => '#315fdc',
                            'vars'        => array( '--qiling-footer-bottom-link-hover' ),
                        ),
                        'bottom_border'      => array(
                            'label'       => __( '版权/备案区域分隔线', 'developer-starter' ),
                            'placeholder' => 'rgba(79,125,255,0.14)',
                            'vars'        => array( '--qiling-footer-bottom-border' ),
                        ),
                        'wave_backdrop'      => array(
                            'label'       => __( '波浪上方背景', 'developer-starter' ),
                            'placeholder' => '#ffffff',
                            'vars'        => array( '--qiling-footer-wave-backdrop' ),
                        ),
                        'wave_transition_from' => array(
                            'label'       => __( '波浪柔化起始色', 'developer-starter' ),
                            'placeholder' => '#f7fbff',
                            'vars'        => array( '--qiling-footer-wave-transition-from' ),
                        ),
                        'wave_transition_height' => array(
                            'label'       => __( '波浪柔化高度', 'developer-starter' ),
                            'placeholder' => '32px',
                            'vars'        => array( '--qiling-footer-wave-transition-height' ),
                        ),
                        'wave_height'        => array(
                            'label'       => __( '波浪高度', 'developer-starter' ),
                            'placeholder' => '64px',
                            'vars'        => array( '--qiling-footer-wave-height' ),
                        ),
                        'wave_color'         => array(
                            'label'       => __( '主波浪颜色', 'developer-starter' ),
                            'placeholder' => '#dff7ff',
                            'vars'        => array( '--qiling-footer-wave-color' ),
                        ),
                        'wave_layer_color'   => array(
                            'label'       => __( '副波浪颜色', 'developer-starter' ),
                            'placeholder' => '#e8efff',
                            'vars'        => array( '--qiling-footer-wave-layer-color' ),
                        ),
                        'wave_layer_opacity' => array(
                            'label'       => __( '副波浪透明度', 'developer-starter' ),
                            'placeholder' => '0.5',
                            'type'        => 'opacity',
                            'vars'        => array( '--qiling-footer-wave-layer-opacity' ),
                        ),
                    ),
                ),
                'buttons' => array(
                    'label'       => __( '按钮和筛选项', 'developer-starter' ),
                    'description' => __( '控制当前页面通用按钮、tab/cat/filter 按钮。', 'developer-starter' ),
                    'fields'      => array(
                        'background'       => array(
                            'label'       => __( '按钮背景', 'developer-starter' ),
                            'placeholder' => '#4f7dff',
                            'vars'        => array( '--qiling-button-bg', '--qiling-component-button-bg', '--qiling-page-accent' ),
                        ),
                        'text'             => array(
                            'label'       => __( '按钮文字', 'developer-starter' ),
                            'placeholder' => '#ffffff',
                            'vars'        => array( '--qiling-button-text', '--qiling-component-button-text', '--qiling-page-accent-contrast' ),
                        ),
                        'hover_background' => array(
                            'label'       => __( '按钮悬停背景', 'developer-starter' ),
                            'placeholder' => '#38c9a6',
                            'vars'        => array( '--qiling-button-hover-bg', '--qiling-component-button-hover-bg', '--color-primary-dark' ),
                        ),
                        'hover_text'       => array(
                            'label'       => __( '按钮悬停文字', 'developer-starter' ),
                            'placeholder' => '#ffffff',
                            'vars'        => array( '--qiling-button-hover-text', '--qiling-component-button-hover-text' ),
                        ),
                    ),
                ),
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_page_visual_style_settings' ) ) {
    /**
     * Sanitize page-level visual settings.
     *
     * @param mixed $settings Raw settings.
     * @return array<string,mixed>
     */
    function developer_starter_sanitize_page_visual_style_settings( $settings ) {
        $settings = is_array( $settings ) ? $settings : array();

        $mode = isset( $settings['mode'] ) && is_scalar( $settings['mode'] ) ? sanitize_key( (string) $settings['mode'] ) : 'inherit';
        if ( ! in_array( $mode, array( 'inherit', 'global', 'custom' ), true ) ) {
            $mode = 'inherit';
        }

        $preset = isset( $settings['preset'] ) && is_scalar( $settings['preset'] ) ? sanitize_key( (string) $settings['preset'] ) : '';
        if ( '' !== $preset && ( ! function_exists( 'developer_starter_page_visual_preset_exists' ) || ! developer_starter_page_visual_preset_exists( $preset ) ) ) {
            $preset = '';
        }

        $sanitized = array(
            'mode'    => $mode,
            'preset'  => 'custom' === $mode ? $preset : '',
            'colors'  => array(),
            'canvas'  => array(),
            'header'  => array(),
            'footer'  => array(),
            'buttons' => array(),
        );

        if ( 'custom' !== $mode ) {
            return $sanitized;
        }

        $groups = developer_starter_get_page_visual_style_fields();
        foreach ( $groups as $group_key => $group ) {
            $group_key = sanitize_key( (string) $group_key );
            if ( '' === $group_key || empty( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
                continue;
            }

            $raw_group = isset( $settings[ $group_key ] ) && is_array( $settings[ $group_key ] ) ? $settings[ $group_key ] : array();
            foreach ( $group['fields'] as $field_key => $field ) {
                $field_key = sanitize_key( (string) $field_key );
                if ( '' === $field_key || ! array_key_exists( $field_key, $raw_group ) || ! is_scalar( $raw_group[ $field_key ] ) ) {
                    continue;
                }

                $value = trim( (string) $raw_group[ $field_key ] );
                if ( '' === $value ) {
                    continue;
                }

                $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'css';
                if ( 'opacity' === $type ) {
                    $opacity = is_numeric( $value ) ? (float) $value : 0;
                    $opacity = max( 0, min( 1, $opacity ) );
                    $value   = rtrim( rtrim( number_format( $opacity, 2, '.', '' ), '0' ), '.' );
                } elseif ( 'select' === $type ) {
                    $options = isset( $field['options'] ) && is_array( $field['options'] ) ? array_map( 'strval', array_keys( $field['options'] ) ) : array();
                    $value = in_array( $value, $options, true ) ? $value : '';
                } else {
                    $value = developer_starter_sanitize_page_visual_style_css_value( $value );
                }

                if ( '' !== $value ) {
                    $sanitized[ $group_key ][ $field_key ] = $value;
                }
            }
        }

        return $sanitized;
    }
}

if ( ! function_exists( 'developer_starter_get_post_page_visual_style' ) ) {
    /**
     * Read page-level visual settings from post meta.
     *
     * @param int $post_id Post ID.
     * @return array<string,mixed>
     */
    function developer_starter_get_post_page_visual_style( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return developer_starter_sanitize_page_visual_style_settings( array() );
        }

        $raw = get_post_meta( $post_id, developer_starter_get_page_visual_style_meta_key(), true );
        if ( is_string( $raw ) && '' !== trim( $raw ) && '{' === substr( ltrim( $raw ), 0, 1 ) ) {
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $raw = $decoded;
            }
        }

        return developer_starter_sanitize_page_visual_style_settings( is_array( $raw ) ? $raw : array() );
    }
}

if ( ! function_exists( 'developer_starter_page_visual_style_has_custom_values' ) ) {
    /**
     * Check whether a sanitized page visual settings array has custom values.
     *
     * @param array<string,mixed> $settings Settings.
     * @return bool
     */
    function developer_starter_page_visual_style_has_custom_values( $settings ) {
        if ( empty( $settings ) || ! is_array( $settings ) ) {
            return false;
        }

        $mode = isset( $settings['mode'] ) ? (string) $settings['mode'] : '';
        if ( 'global' === $mode ) {
            return true;
        }
        if ( 'custom' !== $mode ) {
            return false;
        }

        if ( ! empty( $settings['preset'] ) ) {
            return true;
        }

        foreach ( array( 'colors', 'canvas', 'header', 'footer', 'buttons' ) as $group_key ) {
            if ( ! empty( $settings[ $group_key ] ) && is_array( $settings[ $group_key ] ) ) {
                return true;
            }
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_persist_post_page_visual_style' ) ) {
    /**
     * Persist page-level visual settings.
     *
     * @param int   $post_id  Post ID.
     * @param mixed $settings Raw settings.
     * @return void
     */
    function developer_starter_persist_post_page_visual_style( $post_id, $settings ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return;
        }

        $settings = developer_starter_sanitize_page_visual_style_settings( $settings );
        if ( 'custom' !== $settings['mode'] ) {
            if ( 'global' === $settings['mode'] ) {
                update_post_meta( $post_id, developer_starter_get_page_visual_style_meta_key(), $settings );
            } else {
                delete_post_meta( $post_id, developer_starter_get_page_visual_style_meta_key() );
            }
            return;
        }

        update_post_meta( $post_id, developer_starter_get_page_visual_style_meta_key(), $settings );
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_style_presets' ) ) {
    /**
     * Get preset choices for page-level visual settings.
     *
     * @return array<string,string>
     */
    function developer_starter_get_page_visual_style_presets() {
        $choices = array();
        foreach ( developer_starter_get_page_visual_skins() as $skin_key => $skin ) {
            if ( ! is_array( $skin ) ) {
                continue;
            }

            $label = ! empty( $skin['label'] ) && is_scalar( $skin['label'] )
                ? (string) $skin['label']
                : (string) $skin_key;
            $choices[ sanitize_key( (string) $skin_key ) ] = $label;
        }

        if ( function_exists( 'developer_starter_get_page_visual_custom_presets' ) ) {
            foreach ( developer_starter_get_page_visual_custom_presets() as $preset_key => $preset ) {
                $preset_key = sanitize_key( (string) $preset_key );
                if ( '' === $preset_key || empty( $preset['label'] ) ) {
                    continue;
                }

                $choices[ $preset_key ] = sprintf(
                    /* translators: %s: custom preset label */
                    __( '我的：%s', 'developer-starter' ),
                    (string) $preset['label']
                );
            }
        }

        return $choices;
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_skin_vars_array' ) ) {
    /**
     * Get sanitized CSS variables from a skin.
     *
     * @param array<string,mixed>|null $skin  Skin config.
     * @param string                   $scope Optional scope.
     * @return array<string,string>
     */
    function developer_starter_get_page_visual_skin_vars_array( $skin, $scope = '' ) {
        if ( ! is_array( $skin ) ) {
            return array();
        }

        $scope  = sanitize_key( (string) $scope );
        $scopes = '' !== $scope && 'all' !== $scope ? array( $scope ) : array( 'header', 'footer' );
        $vars   = array();

        foreach ( $scopes as $scope_key ) {
            if ( empty( $skin[ $scope_key ] ) || ! is_array( $skin[ $scope_key ] ) ) {
                continue;
            }

            $scope_vars = isset( $skin[ $scope_key ]['vars'] ) && is_array( $skin[ $scope_key ]['vars'] )
                ? $skin[ $scope_key ]['vars']
                : array();
            foreach ( $scope_vars as $name => $value ) {
                $name = is_string( $name ) ? trim( $name ) : '';
                if ( ! preg_match( '/^--[a-z0-9_-]+$/i', $name ) || ! is_scalar( $value ) ) {
                    continue;
                }

                $value = developer_starter_sanitize_page_visual_style_css_value( $value );
                if ( '' !== $value ) {
                    $vars[ $name ] = $value;
                }
            }
        }

        return $vars;
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_style_custom_vars_array' ) ) {
    /**
     * Build CSS variables from custom page visual settings.
     *
     * @param array<string,mixed> $settings Sanitized settings.
     * @param string              $scope    Optional scope key.
     * @return array<string,string>
     */
    function developer_starter_get_page_visual_style_custom_vars_array( $settings, $scope = 'all' ) {
        $settings = developer_starter_sanitize_page_visual_style_settings( $settings );
        if ( 'custom' !== $settings['mode'] ) {
            return array();
        }

        $scope = sanitize_key( (string) $scope );
        $vars  = array();
        $groups = developer_starter_get_page_visual_style_fields();
        $allowed_groups = array_keys( $groups );
        if ( 'header' === $scope ) {
            $allowed_groups = array( 'colors', 'header', 'buttons' );
        } elseif ( 'footer' === $scope ) {
            $allowed_groups = array( 'colors', 'footer', 'buttons' );
        }

        foreach ( $groups as $group_key => $group ) {
            $group_key = sanitize_key( (string) $group_key );
            if ( ! in_array( $group_key, $allowed_groups, true ) ) {
                continue;
            }
            if ( '' === $group_key || empty( $group['fields'] ) || ! is_array( $group['fields'] ) || empty( $settings[ $group_key ] ) || ! is_array( $settings[ $group_key ] ) ) {
                continue;
            }

            foreach ( $group['fields'] as $field_key => $field ) {
                $field_key = sanitize_key( (string) $field_key );
                if ( '' === $field_key || ! array_key_exists( $field_key, $settings[ $group_key ] ) || '' === (string) $settings[ $group_key ][ $field_key ] || empty( $field['vars'] ) || ! is_array( $field['vars'] ) ) {
                    continue;
                }

                $value = (string) $settings[ $group_key ][ $field_key ];
                foreach ( $field['vars'] as $var_name ) {
                    $var_name = is_string( $var_name ) ? trim( $var_name ) : '';
                    if ( preg_match( '/^--[a-z0-9_-]+$/i', $var_name ) ) {
                        $vars[ $var_name ] = $value;
                    }
                }
            }
        }

        // Older page styles used one footer background/text pair for both the main and friend-link regions.
        if ( isset( $settings['footer']['background'] ) && empty( $settings['footer']['friend_background'] ) ) {
            $vars['--qiling-footer-friend-bg'] = (string) $settings['footer']['background'];
        }
        if ( isset( $settings['footer']['text'] ) && empty( $settings['footer']['friend_text'] ) ) {
            foreach ( array( '--qiling-footer-friend-text', '--qiling-footer-friend-link', '--qiling-footer-friend-link-hover' ) as $var_name ) {
                $vars[ $var_name ] = (string) $settings['footer']['text'];
            }
        }
        if ( isset( $settings['footer']['bottom_text'] ) && empty( $settings['footer']['bottom_link'] ) ) {
            $vars['--qiling-footer-bottom-link'] = (string) $settings['footer']['bottom_text'];
        }

        return $vars;
    }
}

if ( ! function_exists( 'developer_starter_resolve_page_visual_style' ) ) {
    /**
     * Resolve page visual vars in priority order: template preset, selected preset, custom values.
     *
     * @param int $post_id Post ID.
     * @return array<string,mixed>
     */
    function developer_starter_resolve_page_visual_style( $post_id ) {
        $post_id  = absint( $post_id );
        $settings = developer_starter_get_post_page_visual_style( $post_id );
        $template = '';
        if ( $post_id > 0 && function_exists( 'get_page_template_slug' ) ) {
            $template = (string) get_page_template_slug( $post_id );
        }
        if ( '' === trim( $template ) && $post_id > 0 ) {
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        $skin = null;
        $vars = array();
        if ( 'custom' === $settings['mode'] && ! empty( $settings['preset'] ) ) {
            $skin = developer_starter_get_page_visual_skin( $settings['preset'] );
            if ( is_array( $skin ) ) {
                $vars = developer_starter_get_page_visual_skin_vars_array( $skin, 'all' );
            } else {
                if ( function_exists( 'developer_starter_get_page_visual_custom_preset_skin' ) ) {
                    $skin = developer_starter_get_page_visual_custom_preset_skin( $settings['preset'] );
                }
                $vars = developer_starter_get_page_visual_preset_vars_array( $settings['preset'], 'all' );
            }
        }
        if ( ! is_array( $skin ) && empty( $vars ) && 'global' !== $settings['mode'] ) {
            $skin = developer_starter_get_page_visual_skin_for_template( $template );
            $vars = developer_starter_get_page_visual_skin_vars_array( $skin, 'all' );
        }

        if ( 'custom' === $settings['mode'] ) {
            $vars = array_merge( $vars, developer_starter_get_page_visual_style_custom_vars_array( $settings ) );
        }

        return array(
            'post_id'     => $post_id,
            'settings'    => $settings,
            'skin'        => $skin,
            'skin_key'    => is_array( $skin ) && ! empty( $skin['key'] ) ? sanitize_key( (string) $skin['key'] ) : '',
            'preset_key'  => ! empty( $settings['preset'] ) ? sanitize_key( (string) $settings['preset'] ) : '',
            'skin_footer' => is_array( $skin ) && ! empty( $skin['footer'] ) && is_array( $skin['footer'] ) ? $skin['footer'] : array(),
            'vars'        => $vars,
            'is_custom'   => 'custom' === $settings['mode'],
            'is_global'   => 'global' === $settings['mode'],
        );
    }
}

if ( ! function_exists( 'developer_starter_build_page_visual_style_vars' ) ) {
    /**
     * Build a style attribute value from CSS variables.
     *
     * @param array<string,string> $vars CSS vars.
     * @return string
     */
    function developer_starter_build_page_visual_style_vars( $vars ) {
        $style = '';
        if ( ! is_array( $vars ) ) {
            return $style;
        }

        foreach ( $vars as $name => $value ) {
            $name = is_string( $name ) ? trim( $name ) : '';
            if ( ! preg_match( '/^--[a-z0-9_-]+$/i', $name ) || ! is_scalar( $value ) ) {
                continue;
            }

            $value = developer_starter_sanitize_page_visual_style_css_value( $value );
            if ( '' !== $value ) {
                $style .= $name . ':' . $value . ';';
            }
        }

        return $style;
    }
}

if ( ! function_exists( 'developer_starter_get_current_page_visual_style_post_id' ) ) {
    /**
     * Get current singular page ID for visual style resolution.
     *
     * @return int
     */
    function developer_starter_get_current_page_visual_style_post_id() {
        if ( ! function_exists( 'is_singular' ) || ! is_singular( 'page' ) ) {
            return 0;
        }

        return function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
    }
}

if ( ! function_exists( 'developer_starter_get_current_page_visual_style_vars_array' ) ) {
    /**
     * Get current page visual style vars.
     *
     * @param string $scope Reserved scope key.
     * @return array<string,string>
     */
    function developer_starter_get_current_page_visual_style_vars_array( $scope = 'all' ) {
        $post_id = developer_starter_get_current_page_visual_style_post_id();
        if ( $post_id <= 0 ) {
            return array();
        }

        $resolved = developer_starter_resolve_page_visual_style( $post_id );
        $scope    = sanitize_key( (string) $scope );
        if ( '' === $scope || 'all' === $scope ) {
            return isset( $resolved['vars'] ) && is_array( $resolved['vars'] ) ? $resolved['vars'] : array();
        }

        $settings = isset( $resolved['settings'] ) && is_array( $resolved['settings'] ) ? $resolved['settings'] : array();
        $skin     = isset( $resolved['skin'] ) && is_array( $resolved['skin'] ) ? $resolved['skin'] : null;
        $mode     = isset( $settings['mode'] ) ? (string) $settings['mode'] : 'inherit';
        $vars     = array();
        if ( 'custom' === $mode && ! empty( $settings['preset'] ) ) {
            $built_in_skin = developer_starter_get_page_visual_skin( $settings['preset'] );
            $vars          = is_array( $built_in_skin )
                ? developer_starter_get_page_visual_skin_vars_array( $built_in_skin, $scope )
                : developer_starter_get_page_visual_preset_vars_array( $settings['preset'], $scope );
        } elseif ( 'global' !== $mode ) {
            $vars = developer_starter_get_page_visual_skin_vars_array( $skin, $scope );
        }

        if ( 'custom' === $mode ) {
            $vars = array_merge( $vars, developer_starter_get_page_visual_style_custom_vars_array( $settings, $scope ) );
        }

        return $vars;
    }
}

if ( ! function_exists( 'developer_starter_get_current_page_visual_style_vars' ) ) {
    /**
     * Get current page visual style vars as a style attribute fragment.
     *
     * @param string $scope Reserved scope key.
     * @return string
     */
    function developer_starter_get_current_page_visual_style_vars( $scope = 'all' ) {
        return developer_starter_build_page_visual_style_vars( developer_starter_get_current_page_visual_style_vars_array( $scope ) );
    }
}

if ( ! function_exists( 'developer_starter_output_current_page_visual_style_inline_css' ) ) {
    /**
     * Output page-level visual variables after enqueued styles.
     *
     * @return void
     */
    function developer_starter_output_current_page_visual_style_inline_css() {
        $post_id = developer_starter_get_current_page_visual_style_post_id();
        if ( $post_id <= 0 ) {
            return;
        }

        $style = developer_starter_get_current_page_visual_style_vars( 'all' );
        if ( '' === $style ) {
            return;
        }

        $selector = 'body.page-id-' . absint( $post_id );
        $css      = $selector . '{' . $style . '}';
        $css     .= $selector . '.qiling-page-visual-custom{background:var(--qiling-page-bg, inherit);color:var(--qiling-page-text, inherit);}';
        $css     .= $selector . '.qiling-page-visual-custom .site-main{background:var(--qiling-page-bg, inherit);color:var(--qiling-page-text, inherit);}';

        echo "\n<style id=\"qiling-page-visual-style-" . esc_attr( (string) $post_id ) . "\">" . $css . "</style>\n";
    }
}
add_action( 'wp_head', 'developer_starter_output_current_page_visual_style_inline_css', 99 );

if ( ! function_exists( 'developer_starter_filter_page_visual_style_body_classes' ) ) {
    /**
     * Add page visual style classes to body_class().
     *
     * @param array<int,string> $classes Body classes.
     * @return array<int,string>
     */
    function developer_starter_filter_page_visual_style_body_classes( $classes ) {
        $post_id = developer_starter_get_current_page_visual_style_post_id();
        if ( $post_id <= 0 ) {
            return $classes;
        }

        $resolved = developer_starter_resolve_page_visual_style( $post_id );
        $vars     = isset( $resolved['vars'] ) && is_array( $resolved['vars'] ) ? $resolved['vars'] : array();
        $settings = isset( $resolved['settings'] ) && is_array( $resolved['settings'] ) ? $resolved['settings'] : array();
        $canvas_mode = isset( $settings['canvas']['mode'] ) ? sanitize_key( (string) $settings['canvas']['mode'] ) : '';
        if ( empty( $vars ) && 'continuous' !== $canvas_mode ) {
            return $classes;
        }

        $classes   = is_array( $classes ) ? $classes : array();
        $classes[] = 'qiling-page-visual-enabled';

        if ( ! empty( $resolved['is_custom'] ) ) {
            $classes[] = 'qiling-page-visual-custom';
        }
        if ( 'continuous' === $canvas_mode ) {
            $classes[] = 'qiling-continuous-canvas';
        }

        if ( ! empty( $resolved['skin_key'] ) ) {
            $classes[] = 'qiling-page-visual-preset-' . str_replace( '_', '-', sanitize_html_class( (string) $resolved['skin_key'] ) );
        } elseif ( ! empty( $resolved['preset_key'] ) ) {
            $classes[] = 'qiling-page-visual-preset-' . str_replace( '_', '-', sanitize_html_class( (string) $resolved['preset_key'] ) );
        }

        return array_values( array_unique( $classes ) );
    }
}
add_filter( 'body_class', 'developer_starter_filter_page_visual_style_body_classes', 22 );

if ( ! function_exists( 'developer_starter_get_page_visual_skin_wrapper_classes' ) ) {
    /**
     * Build wrapper classes for a page-level visual skin.
     *
     * @param array<int,string>|string $base_classes Base wrapper classes.
     * @param string                   $template     Optional page template path.
     * @return string
     */
    function developer_starter_get_page_visual_skin_wrapper_classes( $base_classes = array(), $template = '' ) {
        $classes = developer_starter_normalize_page_skin_class_list( $base_classes );
        $skin    = '' !== (string) $template
            ? developer_starter_get_page_visual_skin_for_template( $template )
            : developer_starter_get_current_page_visual_skin();

        if ( is_array( $skin ) ) {
            $classes = array_merge(
                $classes,
                developer_starter_normalize_page_skin_class_list( isset( $skin['wrapper_classes'] ) ? $skin['wrapper_classes'] : array() )
            );
        }

        return implode( ' ', array_values( array_unique( $classes ) ) );
    }
}

if ( ! function_exists( 'developer_starter_get_page_visual_skin_style_vars' ) ) {
    /**
     * Build sanitized CSS custom properties for one page skin scope.
     *
     * @param array<string,mixed>|null $skin  Page skin config.
     * @param string                   $scope Scope key such as header/footer.
     * @return string
     */
    function developer_starter_get_page_visual_skin_style_vars( $skin, $scope ) {
        if ( ! is_array( $skin ) ) {
            return '';
        }

        $scope = sanitize_key( (string) $scope );
        if ( '' === $scope || empty( $skin[ $scope ] ) || ! is_array( $skin[ $scope ] ) ) {
            return '';
        }

        $vars = isset( $skin[ $scope ]['vars'] ) && is_array( $skin[ $scope ]['vars'] )
            ? $skin[ $scope ]['vars']
            : array();
        if ( empty( $vars ) ) {
            return '';
        }

        $style = '';
        foreach ( $vars as $name => $value ) {
            $name = is_string( $name ) ? trim( $name ) : '';
            if ( ! preg_match( '/^--[a-z0-9_-]+$/i', $name ) || ! is_scalar( $value ) ) {
                continue;
            }

            $value = trim( (string) $value );
            if ( '' === $value ) {
                continue;
            }

            $value = function_exists( 'developer_starter_sanitize_footer_visual_css_value' )
                ? developer_starter_sanitize_footer_visual_css_value( $value, '' )
                : sanitize_text_field( $value );
            if ( '' !== $value ) {
                $style .= $name . ':' . $value . ';';
            }
        }

        return $style;
    }
}

if ( ! function_exists( 'developer_starter_filter_page_visual_skin_body_classes' ) ) {
    /**
     * Add active page skin classes to body_class().
     *
     * @param array<int,string> $classes Body classes.
     * @return array<int,string>
     */
    function developer_starter_filter_page_visual_skin_body_classes( $classes ) {
        $skin = developer_starter_get_current_page_visual_skin();
        if ( ! is_array( $skin ) ) {
            return $classes;
        }

        $classes = array_merge(
            is_array( $classes ) ? $classes : array(),
            developer_starter_normalize_page_skin_class_list( isset( $skin['body_classes'] ) ? $skin['body_classes'] : array() )
        );

        return array_values( array_unique( $classes ) );
    }
}
add_filter( 'body_class', 'developer_starter_filter_page_visual_skin_body_classes', 20 );

if ( ! function_exists( 'developer_starter_enqueue_current_page_visual_skin_styles' ) ) {
    /**
     * Enqueue styles for the active page-level visual skin.
     *
     * @param string $fallback_version Fallback asset version.
     * @return void
     */
    function developer_starter_enqueue_current_page_visual_skin_styles( $fallback_version = '' ) {
        $skin = developer_starter_get_current_page_visual_skin();
        if ( ! is_array( $skin ) ) {
            return;
        }

        $handle = isset( $skin['style_handle'] ) ? sanitize_key( (string) $skin['style_handle'] ) : '';
        $path   = isset( $skin['style_path'] ) ? ltrim( (string) $skin['style_path'], '/' ) : '';
        if ( '' === $handle || '' === $path || false !== strpos( $path, '..' ) ) {
            return;
        }

        $file_path = trailingslashit( DEVELOPER_STARTER_DIR ) . $path;
        if ( ! file_exists( $file_path ) ) {
            return;
        }

        $deps         = isset( $skin['style_deps'] ) && is_array( $skin['style_deps'] ) ? $skin['style_deps'] : array( 'developer-starter-main' );
        $file_version = filemtime( $file_path );
        $version      = false !== $file_version ? (string) $file_version : (string) $fallback_version;
        $style_url    = 0 === strpos( $path, 'assets/' )
            ? trailingslashit( DEVELOPER_STARTER_ASSETS ) . substr( $path, strlen( 'assets/' ) )
            : trailingslashit( DEVELOPER_STARTER_URI ) . $path;

        wp_enqueue_style(
            $handle,
            $style_url,
            $deps,
            $version
        );
    }
}
