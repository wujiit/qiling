<?php
/**
 * Read-only optional plugin detector for the setup wizard.
 *
 * This class never installs, activates, deactivates, or configures plugins.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_Plugin_Detector {

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MISSING  = 'missing';
    const STATUS_UNKNOWN  = 'unknown';

    /**
     * Detect all known optional integrations.
     *
     * @return array<string,array<string,string>>
     */
    public function detect_all() {
        $plugins = $this->get_known_plugins();
        $result  = array();

        foreach ( $plugins as $key => $plugin ) {
            $result[ $key ] = $this->detect_plugin( $key, $plugin );
        }

        return $result;
    }

    /**
     * Build a compact status snapshot suitable for wizard state.
     *
     * @return array<string,string>
     */
    public function get_status_snapshot() {
        $snapshot = array();
        foreach ( $this->detect_all() as $key => $plugin ) {
            $snapshot[ $key ] = isset( $plugin['status'] ) ? $plugin['status'] : self::STATUS_UNKNOWN;
        }

        return $snapshot;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function get_known_plugins() {
        return array(
            'woocommerce'   => array(
                'label'      => 'WooCommerce',
                'basenames'  => array( 'woocommerce/woocommerce.php' ),
                'classes'    => array( 'WooCommerce' ),
                'functions'  => array(),
                'hint'       => '可增强电商、商品和订单能力。向导只检测，不安装不配置。',
            ),
            'qilingshop'    => array(
                'label'      => '启灵积分商城',
                'basenames'  => array( 'qilingshop/qilingshop.php', 'qiling-shop/qiling-shop.php' ),
                'classes'    => array(),
                'functions'  => array( 'qilingshop_points_resource_enabled', 'qilingshop_registration_code_is_enabled' ),
                'hint'       => '可增强积分兑换、付费资源和会员权益。向导只检测，不安装不配置。',
            ),
            'qiling_weixin' => array(
                'label'      => '启灵微信登录',
                'basenames'  => array( 'qiling-weixin/qiling-weixin.php', 'qiling_weixin/qiling_weixin.php' ),
                'classes'    => array( 'qiling_weixin_login' ),
                'functions'  => array(),
                'hint'       => '可增强微信公众号/微信扫码登录能力。向导只检测，不安装不配置。',
            ),
            'qiling_forms'  => array(
                'label'      => '启灵表单',
                'basenames'  => array( 'qiling-forms/qiling-forms.php' ),
                'classes'    => array(),
                'functions'  => array( 'qiling_forms' ),
                'hint'       => '可增强咨询、报名、预约和线索收集。向导只检测，不安装不配置。',
            ),
            'qilingapp'     => array(
                'label'      => '启灵应用',
                'basenames'  => array( 'qilingapp/qilingapp.php' ),
                'classes'    => array(),
                'functions'  => array( 'qilingapp', 'qilingapp_get_post_type', 'qiapp_get_post_type', 'qlapp_get_post_type' ),
                'hint'       => '可增强软件、资源和应用目录。向导只检测，不安装不配置。',
            ),
        );
    }

    /**
     * Detect one optional plugin without modifying it.
     *
     * @param string              $key Plugin key.
     * @param array<string,mixed> $plugin Plugin definition.
     * @return array<string,string>
     */
    private function detect_plugin( $key, $plugin ) {
        $status   = self::STATUS_MISSING;
        $basename = $this->resolve_installed_basename( isset( $plugin['basenames'] ) ? $plugin['basenames'] : array() );

        if ( $this->has_runtime_indicator( $plugin ) ) {
            $status = self::STATUS_ACTIVE;
        } elseif ( '' !== $basename ) {
            $status = $this->is_plugin_active_readonly( $basename ) ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
        }

        return array(
            'key'      => sanitize_key( (string) $key ),
            'label'    => isset( $plugin['label'] ) ? sanitize_text_field( (string) $plugin['label'] ) : sanitize_key( (string) $key ),
            'status'   => $status,
            'basename' => $basename,
            'hint'     => isset( $plugin['hint'] ) ? sanitize_text_field( (string) $plugin['hint'] ) : '',
        );
    }

    /**
     * @param array<string,mixed> $plugin Plugin definition.
     * @return bool
     */
    private function has_runtime_indicator( $plugin ) {
        foreach ( (array) ( isset( $plugin['classes'] ) ? $plugin['classes'] : array() ) as $class_name ) {
            if ( class_exists( (string) $class_name, false ) ) {
                return true;
            }
        }

        foreach ( (array) ( isset( $plugin['functions'] ) ? $plugin['functions'] : array() ) as $function_name ) {
            if ( function_exists( (string) $function_name ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,string> $basenames Candidate plugin basenames.
     * @return string
     */
    private function resolve_installed_basename( $basenames ) {
        $installed = $this->get_installed_plugin_basenames();
        foreach ( (array) $basenames as $basename ) {
            $basename = plugin_basename( (string) $basename );
            if ( isset( $installed[ $basename ] ) ) {
                return $basename;
            }
        }

        return '';
    }

    /**
     * @return array<string,bool>
     */
    private function get_installed_plugin_basenames() {
        if ( ! function_exists( 'get_plugins' ) && defined( 'ABSPATH' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( ! function_exists( 'get_plugins' ) ) {
            return array();
        }

        $plugins = get_plugins();
        $map     = array();
        foreach ( array_keys( (array) $plugins ) as $basename ) {
            $map[ plugin_basename( (string) $basename ) ] = true;
        }

        return $map;
    }

    /**
     * @param string $basename Plugin basename.
     * @return bool
     */
    private function is_plugin_active_readonly( $basename ) {
        if ( ! function_exists( 'is_plugin_active' ) && defined( 'ABSPATH' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( function_exists( 'is_plugin_active' ) && is_plugin_active( $basename ) ) {
            return true;
        }

        if ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( $basename ) ) {
            return true;
        }

        return false;
    }
}
