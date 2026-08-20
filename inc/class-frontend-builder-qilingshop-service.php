<?php
/**
 * Frontend Builder qilingshop adapter service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Frontend_Builder_QilingShop_Service {

    /**
     * 是否属于商城模块类型。
     *
     * @param string $module_type 模块类型。
     * @return bool
     */
    public function is_shop_module_type( $module_type ) {
        return strpos( sanitize_key( (string) $module_type ), 'qls_fb_' ) === 0;
    }

    /**
     * 当前环境是否具备启灵积分商城前台装修能力。
     *
     * @return bool
     */
    public function is_builder_available() {
        if ( class_exists( '\QLS_FrontendBuilder_Registrar' ) ) {
            return true;
        }

        if ( function_exists( 'qls_shop_public' ) || class_exists( '\QLS_Shop_Public' ) ) {
            return true;
        }

        if ( ! defined( 'QILINGSHOP_PATH' ) ) {
            return false;
        }

        $base_dir = trailingslashit( QILINGSHOP_PATH ) . 'includes/shop/frontend-builder/';
        return is_dir( $base_dir );
    }

    /**
     * 当前页面是否属于启灵积分商城前台装修页面。
     *
     * @param int $post_id 页面 ID。
     * @return bool
     */
    public function is_builder_page( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return false;
        }

        if ( ! $this->is_builder_available() ) {
            return false;
        }

        $post = get_post( $post_id );
        if ( ! ( $post instanceof \WP_Post ) ) {
            return false;
        }

        $content = (string) $post->post_content;
        if ( '' === $content ) {
            return false;
        }

        return $this->content_has_qilingshop_shortcodes( $content );
    }

    /**
     * 在积分商城装修上下文中触发商城前台装修模块注册。
     *
     * @param int $post_id 页面 ID。
     * @return void
     */
    public function bootstrap_modules( $post_id = 0 ) {
        if ( ! $this->is_builder_available() ) {
            return;
        }

        $post_id = absint( $post_id );
        if ( $post_id <= 0 && isset( $_REQUEST['post_id'] ) ) {
            $post_id = absint( wp_unslash( (string) $_REQUEST['post_id'] ) );
        }
        if ( $post_id > 0 && ! $this->is_builder_page( $post_id ) ) {
            return;
        }

        if ( ! class_exists( '\QLS_FrontendBuilder_Registrar' ) && defined( 'QILINGSHOP_PATH' ) ) {
            $registrar_file = trailingslashit( QILINGSHOP_PATH ) . 'includes/shop/frontend-builder/class-qls-fb-registrar.php';
            if ( file_exists( $registrar_file ) ) {
                require_once $registrar_file;
            }
        }

        if ( class_exists( '\QLS_FrontendBuilder_Registrar' ) ) {
            if ( method_exists( '\QLS_FrontendBuilder_Registrar', 'init' ) ) {
                \QLS_FrontendBuilder_Registrar::init();
            }

            if ( method_exists( '\QLS_FrontendBuilder_Registrar', 'register_modules' ) ) {
                \QLS_FrontendBuilder_Registrar::register_modules();
            }
        }

        $manager = Module_Manager::get_instance();
        if ( $manager->get_module( 'qls_fb_product_list' ) ) {
            return;
        }

        if ( ! defined( 'QILINGSHOP_PATH' ) ) {
            return;
        }

        $base_dir = trailingslashit( QILINGSHOP_PATH ) . 'includes/shop/frontend-builder/';
        $base_file = $base_dir . 'class-qls-fb-adapter-base.php';
        if ( file_exists( $base_file ) ) {
            require_once $base_file;
        }

        $adapters = array(
            'class-qls-fb-product-list.php'  => array( 'class' => 'QLS_FB_Product_List', 'id' => 'qls_fb_product_list' ),
            'class-qls-fb-hero-carousel.php' => array( 'class' => 'QLS_FB_Hero_Carousel', 'id' => 'qls_fb_hero_carousel' ),
            'class-qls-fb-category-nav.php'  => array( 'class' => 'QLS_FB_Category_Nav', 'id' => 'qls_fb_category_nav' ),
            'class-qls-fb-coupon.php'        => array( 'class' => 'QLS_FB_Coupon', 'id' => 'qls_fb_coupon' ),
            'class-qls-fb-group.php'         => array( 'class' => 'QLS_FB_Group', 'id' => 'qls_fb_group' ),
            'class-qls-fb-assist.php'        => array( 'class' => 'QLS_FB_Assist', 'id' => 'qls_fb_assist' ),
            'class-qls-fb-new-user-zone.php' => array( 'class' => 'QLS_FB_New_User_Zone', 'id' => 'qls_fb_new_user_zone' ),
        );

        foreach ( $adapters as $file => $adapter ) {
            $class_name = isset( $adapter['class'] ) ? (string) $adapter['class'] : '';
            $module_id = isset( $adapter['id'] ) ? sanitize_key( (string) $adapter['id'] ) : '';
            if ( '' === $class_name || '' === $module_id ) {
                continue;
            }
            $file_path = $base_dir . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
            if ( class_exists( $class_name ) && ! $manager->get_module( $module_id ) ) {
                $manager->register_module( new $class_name() );
            }
        }
    }

    /**
     * 读取商城布局并转换为前台装修结构。
     *
     * @param int $post_id 页面 ID。
     * @return array<int,array<string,mixed>>
     */
    public function get_layout_modules( $post_id ) {
        $layout = $this->normalize_layout_value( get_post_meta( $post_id, '_qls_shop_layout', true ) );
        if ( ! is_array( $layout ) || empty( $layout ) ) {
            $shop_home_page_id = (int) get_option( 'qls_shop_page_shop', 0 );
            $is_shop_home_context = ( $shop_home_page_id === (int) $post_id );

            if ( ! $is_shop_home_context ) {
                $post = get_post( $post_id );
                if ( $post instanceof \WP_Post ) {
                    $content = (string) $post->post_content;
                    if ( '' !== $content && strpos( $content, '[qls_shop' ) !== false ) {
                        $is_shop_home_context = true;
                    }
                }
            }

            if ( ! $is_shop_home_context ) {
                return array();
            }
            $legacy_layout = $this->normalize_layout_value( get_option( 'qls_shop_home_layout', array() ) );
            $layout = is_array( $legacy_layout ) ? $legacy_layout : array();
        }

        return $this->map_layout_to_builder_modules( $layout );
    }

    /**
     * 保存模块到对应数据源。
     *
     * @param int                            $post_id 页面 ID。
     * @param array<int,array<string,mixed>> $clean_modules 清洗后的模块列表。
     * @param string                         $source 数据源。
     * @return void
     */
    public function persist_modules_for_source( $post_id, $clean_modules, $source = 'theme' ) {
        if ( 'qilingshop' === $source ) {
            $layout = $this->map_builder_modules_to_layout( $clean_modules );
            update_post_meta( $post_id, '_qls_shop_layout', $layout );
            clean_post_cache( $post_id );
            do_action( 'qilingshop_shop_layout_saved', $post_id, $layout, $clean_modules );
            do_action( 'developer_starter_modules_saved', $post_id, $clean_modules );
            return;
        }

        update_post_meta( $post_id, '_developer_starter_modules', $clean_modules );
        clean_post_cache( $post_id );
        do_action( 'developer_starter_modules_saved', $post_id, $clean_modules );
    }

    /**
     * 商城布局结构 => 前台装修结构。
     *
     * @param mixed $layout 商城布局。
     * @return array<int,array<string,mixed>>
     */
    private function map_layout_to_builder_modules( $layout ) {
        if ( ! is_array( $layout ) ) {
            return array();
        }

        $type_map = $this->get_module_type_map();
        $modules = array();
        foreach ( $layout as $row ) {
            if ( ! is_array( $row ) || empty( $row['type'] ) ) {
                continue;
            }

            $shop_type = sanitize_key( (string) $row['type'] );
            if ( ! isset( $type_map[ $shop_type ] ) ) {
                continue;
            }

            $modules[] = array(
                'type' => $type_map[ $shop_type ],
                'data' => isset( $row['settings'] ) && is_array( $row['settings'] ) ? $row['settings'] : array(),
            );
        }

        return $modules;
    }

    /**
     * 前台装修结构 => 商城布局结构。
     *
     * @param array<int,array<string,mixed>> $modules 前台装修模块。
     * @return array<int,array<string,mixed>>
     */
    private function map_builder_modules_to_layout( $modules ) {
        if ( ! is_array( $modules ) ) {
            return array();
        }

        $reverse_map = array_flip( $this->get_module_type_map() );
        $layout = array();

        foreach ( $modules as $row ) {
            if ( ! is_array( $row ) || empty( $row['type'] ) ) {
                continue;
            }

            $builder_type = sanitize_key( (string) $row['type'] );
            if ( ! isset( $reverse_map[ $builder_type ] ) ) {
                continue;
            }

            $layout[] = array(
                'id'       => 'mod_' . uniqid(),
                'type'     => $reverse_map[ $builder_type ],
                'settings' => isset( $row['data'] ) && is_array( $row['data'] ) ? $row['data'] : array(),
            );
        }

        return $layout;
    }

    /**
     * 商城模块类型映射（商城 type => 前台装修模块 type）。
     *
     * @return array<string,string>
     */
    private function get_module_type_map() {
        return array(
            'product_list'  => 'qls_fb_product_list',
            'hero_carousel' => 'qls_fb_hero_carousel',
            'category_nav'  => 'qls_fb_category_nav',
            'coupon'        => 'qls_fb_coupon',
            'group'         => 'qls_fb_group',
            'assist'        => 'qls_fb_assist',
            'new_user_zone' => 'qls_fb_new_user_zone',
        );
    }

    /**
     * 页面内容是否包含积分商城首页短代码 [qls_shop]。
     *
     * @param string $content 页面内容。
     * @return bool
     */
    private function content_has_qilingshop_shortcodes( $content ) {
        return strpos( (string) $content, '[qls_shop' ) !== false;
    }

    /**
     * 规范化商城布局值（支持数组 / 序列化字符串 / JSON 字符串）。
     *
     * @param mixed $layout 布局原始值。
     * @return array<int,mixed>|mixed
     */
    private function normalize_layout_value( $layout ) {
        if ( is_array( $layout ) ) {
            return $layout;
        }

        if ( is_string( $layout ) ) {
            $raw = trim( $layout );
            if ( '' === $raw ) {
                return array();
            }

            if ( function_exists( 'is_serialized' ) && is_serialized( $raw ) ) {
                $unserialized = maybe_unserialize( $raw );
                if ( is_array( $unserialized ) ) {
                    return $unserialized;
                }
            }

            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                return $decoded;
            }
        }

        return $layout;
    }
}
