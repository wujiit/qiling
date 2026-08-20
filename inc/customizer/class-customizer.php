<?php
/**
 * Customizer Class
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Customizer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Customizer {

    public function __construct() {
        add_action( 'customize_register', array( $this, 'register' ) );
        add_action( 'customize_save_after', array( $this, 'sync_legacy_primary_color_to_theme_options' ) );
    }

    public function register( $wp_customize ) {
        // Header Section
        $wp_customize->add_section( 'developer_starter_header', array(
            'title'    => __( '头部设置', 'developer-starter' ),
            'priority' => 40,
        ) );

        $wp_customize->add_setting( 'developer_starter_header_sticky', array(
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
        ) );
        $wp_customize->add_control( 'developer_starter_header_sticky', array(
            'type'    => 'checkbox',
            'label'   => __( '启用固定头部', 'developer-starter' ),
            'section' => 'developer_starter_header',
        ) );

        // Footer Section
        $wp_customize->add_section( 'developer_starter_footer', array(
            'title'    => __( '页脚设置', 'developer-starter' ),
            'priority' => 50,
        ) );

        $wp_customize->add_setting( 'developer_starter_copyright', array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
        ) );
        $wp_customize->add_control( 'developer_starter_copyright', array(
            'type'    => 'textarea',
            'label'   => __( '版权信息', 'developer-starter' ),
            'section' => 'developer_starter_footer',
        ) );
    }

    /**
     * Keep the legacy Customizer primary color in sync with the theme option store.
     *
     * @return void
     */
    public function sync_legacy_primary_color_to_theme_options() {
        if ( ! isset( $_POST['customized'] ) ) {
            return;
        }

        $customized = json_decode( wp_unslash( (string) $_POST['customized'] ), true );
        if ( ! is_array( $customized ) || ! array_key_exists( 'developer_starter_primary_color', $customized ) ) {
            return;
        }

        $primary = sanitize_hex_color( (string) $customized['developer_starter_primary_color'] );
        if ( ! $primary ) {
            return;
        }

        $existing_options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $existing_options ) ) {
            $existing_options = array();
        }

        $sync_input = array(
            'primary_color'        => $primary,
            'design_primary_color' => $primary,
        );

        if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            $sync_input = \Developer_Starter\Core\Design_Tokens::sanitize_options( $sync_input, $existing_options );
        }

        if ( ! is_array( $sync_input ) ) {
            return;
        }

        update_option( 'developer_starter_options', array_merge( $existing_options, $sync_input ) );
    }
}

new Customizer();
