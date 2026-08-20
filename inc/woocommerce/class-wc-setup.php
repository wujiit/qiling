<?php
/**
 * WooCommerce Setup Class
 *
 * 负责 WooCommerce 兼容性配置和 Hook 注册
 * 不复制模板文件，全部使用 Hook/Filter 实现
 *
 * @package Developer_Starter
 * @since 2.1.0
 */

namespace Developer_Starter\WooCommerce;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WooCommerce 设置类
 */
class WC_Setup {

    /**
     * 构造函数
     */
    public function __construct() {
        // 延迟到 init 时初始化，确保 WooCommerce 已加载
        add_action( 'init', array( $this, 'maybe_init' ), 1 );
    }

    /**
     * 条件初始化
     */
    public function maybe_init() {
        // 仅在 WooCommerce 激活时初始化
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        // 主题支持增强
        add_action( 'after_setup_theme', array( $this, 'enhanced_wc_support' ), 20 );

        // 布局调整 Hooks
        add_action( 'woocommerce_before_main_content', array( $this, 'wrapper_start' ), 10 );
        add_action( 'woocommerce_after_main_content', array( $this, 'wrapper_end' ), 10 );

        // 自定义侧边栏
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
        add_action( 'woocommerce_sidebar', array( $this, 'custom_sidebar' ), 10 );
        
        // 商店 Banner 与 导航
        add_action( 'woocommerce_before_main_content', array( $this, 'shop_banner_output' ), 12 );
        add_action( 'woocommerce_before_main_content', array( $this, 'shop_category_nav' ), 15 );

        // 样式加载
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_wc_styles' ), 20 );

        // 商品网格配置
        add_filter( 'loop_shop_columns', array( $this, 'shop_columns' ) );
        add_filter( 'loop_shop_per_page', array( $this, 'products_per_page' ) );

        // 保护结账页
        add_action( 'woocommerce_before_checkout_form', array( $this, 'checkout_protection' ), 5 );
        // 强制使用 Shortcode 结账 (简体中文优化必需)
        add_filter( 'the_content', array( $this, 'force_shortcode_checkout' ), 5 );

        // body 类
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );

        // 面包屑移除（使用主题自带的）
        $remove_breadcrumb = $this->get_option( 'wc_remove_breadcrumb', '' );
        if ( $remove_breadcrumb ) {
            remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
            // 添加自定义极简面包屑
            add_action( 'woocommerce_before_main_content', array( $this, 'custom_breadcrumb' ), 20 );
        }

        // 中国本土化：直接注册结账字段过滤器
        add_filter( 'woocommerce_checkout_fields', array( $this, 'china_checkout_fields' ), 999 );
        add_filter( 'woocommerce_billing_fields', array( $this, 'china_billing_fields' ), 999 );
        
        // 默认地址字段过滤器（影响账户页面地址编辑）
        add_filter( 'woocommerce_default_address_fields', array( $this, 'china_default_address_fields' ), 999 );
        
        // 账户详情页（修改密码/资料）
        // add_filter( 'woocommerce_edit_account_form_fields', array( $this, 'china_account_details_fields' ), 99 ); // 错误：这是action hook，不能用filter
        add_filter( 'woocommerce_save_account_details_required_fields', array( $this, 'china_save_account_details_required_fields' ), 99 );
        
        // 动态加载资源
        add_action( 'wp_enqueue_scripts', array( $this, 'conditional_wc_assets' ), 1000 );
    }

    /**
     * 条件加载 WooCommerce 资源
     * 在非 WooCommerce 页面移除相关脚本和样式
     */
    public function conditional_wc_assets() {
        if ( ! $this->is_wc_page() ) {
            // 移除核心样式
            wp_dequeue_style( 'woocommerce-general' );
            wp_dequeue_style( 'woocommerce-layout' );
            wp_dequeue_style( 'woocommerce-smallscreen' );
            wp_dequeue_style( 'woocommerce_frontend_styles' );
            wp_dequeue_style( 'woocommerce-inline' );

            // 移除 Block 样式
            wp_dequeue_style( 'wc-blocks-style' );
            wp_dequeue_style( 'wc-blocks-vendors-style' );
            wp_dequeue_style( 'wc-block-style' );
            wp_dequeue_style( 'wp-block-library' );       // 可选：如果全站不使用 Gutenberg，可以移除
            wp_dequeue_style( 'wp-block-library-theme' ); // 可选
            
            // 强力注销样式避免重新加载
            wp_deregister_style( 'wc-blocks-style' );
            wp_deregister_style( 'wc-block-style' );
            wp_deregister_style( 'woocommerce-general' );
            wp_deregister_style( 'woocommerce-layout' );
            wp_deregister_style( 'woocommerce-smallscreen' );

            // 移除所有 WooCommerce 相关脚本
            wp_dequeue_script( 'woocommerce' );
            wp_dequeue_script( 'wc-cart-fragments' );
            wp_dequeue_script( 'wc-add-to-cart' );
            wp_dequeue_script( 'wc-checkout' );
            wp_dequeue_script( 'wc-add-to-cart-variation' );
            wp_dequeue_script( 'wc-single-product' );
            wp_dequeue_script( 'wc-cart' );
            wp_dequeue_script( 'wc-chosen' );
            wp_dequeue_script( 'prettyPhoto' );
            wp_dequeue_script( 'prettyPhoto-init' );
            wp_dequeue_script( 'jquery-blockui' );
            wp_dequeue_script( 'jquery-placeholder' );
            wp_dequeue_script( 'jquery-payment' );
            wp_dequeue_script( 'zoom' );
            wp_dequeue_script( 'flexslider' );
            wp_dequeue_script( 'photoswipe' );
            wp_dequeue_script( 'photoswipe-ui-default' );
        }
    }

    /**
     * 强制将 WooCommerce Blocks 替换为 Shortcode
     * 解决 Blocks 无法被过滤器简化字段的问题
     */
    public function force_shortcode_checkout( $content ) {
        if ( is_admin() || wp_is_json_request() ) {
            return $content;
        }

        // 处理结账页面
        if ( is_checkout() && ! is_order_received_page() ) {
            // 如果内容包含 Checkout Block 或为空
            if ( has_block( 'woocommerce/checkout' ) || empty( $content ) || strpos( $content, 'wp:woocommerce/checkout' ) !== false ) {
                return '[woocommerce_checkout]';
            }
        }

        // 处理购物车页面
        if ( is_cart() ) {
            // 如果内容包含 Cart Block
            if ( has_block( 'woocommerce/cart' ) || empty( $content ) || strpos( $content, 'wp:woocommerce/cart' ) !== false ) {
                return '[woocommerce_cart]';
            }
        }

        return $content;
    }

    /**
     * 中国本土化：修改默认地址字段
     */
    public function china_default_address_fields( $fields ) {
        $options = $this->get_wc_options();
        
        // 极简模式：隐藏不需要的字段 (改为 hidden 类型而不是 unset，防止保存验证失败)
        if ( ! empty( $options['wc_simplified_checkout'] ) ) {
            $hidden_fields = array( 'last_name', 'company', 'country', 'state', 'city', 'postcode', 'address_2' );
            
            foreach ( $hidden_fields as $field_key ) {
                if ( isset( $fields[ $field_key ] ) ) {
                    $fields[ $field_key ]['required'] = false;
                    $fields[ $field_key ]['type'] = 'hidden';
                    $fields[ $field_key ]['class'][] = 'hidden';
                    $fields[ $field_key ]['label_class'][] = 'hidden';
                    // 给国家设置默认值 CN
                    if ( $field_key === 'country' ) {
                        $fields[ $field_key ]['default'] = 'CN';
                    }
                }
            }
            
            // 修改保留字段标签
            if ( isset( $fields['first_name'] ) ) {
                $fields['first_name']['label'] = __( '收件人', 'developer-starter' );
                $fields['first_name']['class'] = array( 'form-row-wide' );
                $fields['first_name']['priority'] = 10;
            }
            if ( isset( $fields['address_1'] ) ) {
                $fields['address_1']['label'] = __( '收货地址', 'developer-starter' );
                $fields['address_1']['placeholder'] = __( '请填写详细收件地址', 'developer-starter' );
                $fields['address_1']['class'] = array( 'form-row-wide' );
                $fields['address_1']['priority'] = 20;
            }

        } else {
            // 非极简模式下的单独隐藏（兼容旧逻辑，但也建议用 hidden）
            // 为保险起见，这里也改为 hidden，避免保存问题
            $individual_hides = array(
                'wc_hide_postcode' => 'postcode',
                'wc_hide_city' => 'city',
                'wc_hide_state' => 'state',
                'wc_hide_last_name' => 'last_name',
                'wc_hide_company' => 'company',
                'wc_hide_country' => 'country'
            );
            
            foreach ( $individual_hides as $opt => $field ) {
                if ( ! empty( $options[ $opt ] ) && isset( $fields[ $field ] ) ) {
                    $fields[ $field ]['required'] = false;
                    $fields[ $field ]['type'] = 'hidden';
                    $fields[ $field ]['class'][] = 'hidden';
                }
            }
            
            // 姓名特殊处理
            if ( ! empty( $options['wc_hide_last_name'] ) && isset( $fields['first_name'] ) ) {
                 $fields['first_name']['label'] = __( '姓名', 'developer-starter' );
                 $fields['first_name']['class'] = array( 'form-row-wide' );
            }
        }
        
        return $fields;
    }

    /**
     * 中国本土化：结账页面字段（修改为 hidden）
     */
    public function china_checkout_fields( $fields ) {
        $options = $this->get_wc_options();
        
        // 极简模式：仅保留姓名、地址、电话
        if ( ! empty( $options['wc_simplified_checkout'] ) ) {
            
            // ===== 隐藏账单字段 (type=hidden) =====
            $billing_hidden = array( 'billing_company', 'billing_country', 'billing_postcode', 'billing_state', 'billing_city', 'billing_address_2', 'billing_email', 'billing_last_name' );
            
            foreach ( $billing_hidden as $field_key ) {
                if ( isset( $fields['billing'][ $field_key ] ) ) {
                    $fields['billing'][ $field_key ]['required'] = false; // 必须设为 false，否则隐藏了必填项会导致无法提交
                    $fields['billing'][ $field_key ]['type'] = 'hidden';
                    $fields['billing'][ $field_key ]['class'][] = 'hidden';
                    if ( $field_key === 'billing_country' ) {
                        $fields['billing'][ $field_key ]['default'] = 'CN';
                    }
                }
            }

            // ===== 只保留必要字段 =====
            // 姓名
            if ( isset( $fields['billing']['billing_first_name'] ) ) {
                $fields['billing']['billing_first_name']['label'] = __( '姓名', 'developer-starter' );
                $fields['billing']['billing_first_name']['required'] = true;
                $fields['billing']['billing_first_name']['class'] = array( 'form-row-wide' );
                $fields['billing']['billing_first_name']['priority'] = 10;
            }

            // 收货地址
            if ( isset( $fields['billing']['billing_address_1'] ) ) {
                $fields['billing']['billing_address_1']['label'] = __( '收货地址', 'developer-starter' );
                $fields['billing']['billing_address_1']['placeholder'] = __( '请填写详细收件地址', 'developer-starter' );
                $fields['billing']['billing_address_1']['required'] = true;
                $fields['billing']['billing_address_1']['class'] = array( 'form-row-wide' );
                $fields['billing']['billing_address_1']['priority'] = 20;
            }

            // 电话
            if ( isset( $fields['billing']['billing_phone'] ) ) {
                $fields['billing']['billing_phone']['label'] = __( '联系电话', 'developer-starter' );
                $fields['billing']['billing_phone']['required'] = true;
                $fields['billing']['billing_phone']['class'] = array( 'form-row-wide' );
                $fields['billing']['billing_phone']['priority'] = 30;
            }
            
            // 禁用配送地址 (直接 unset 依然是最好的，因为它是整个 section)
            unset( $fields['shipping'] );
            
            return $fields;
        }
        
        // 非极简模式：按单独设置删除
        if ( ! empty( $options['wc_hide_postcode'] ) ) {
            unset( $fields['billing']['billing_postcode'] );
            unset( $fields['shipping']['shipping_postcode'] );
        }
        if ( ! empty( $options['wc_hide_city'] ) ) {
            unset( $fields['billing']['billing_city'] );
            unset( $fields['shipping']['shipping_city'] );
        }
        if ( ! empty( $options['wc_hide_state'] ) ) {
            unset( $fields['billing']['billing_state'] );
            unset( $fields['shipping']['shipping_state'] );
        }
        if ( ! empty( $options['wc_hide_last_name'] ) ) {
            unset( $fields['billing']['billing_last_name'] );
            unset( $fields['shipping']['shipping_last_name'] );
            if ( isset( $fields['billing']['billing_first_name'] ) ) {
                $fields['billing']['billing_first_name']['label'] = __( '姓名', 'developer-starter' );
                $fields['billing']['billing_first_name']['class'] = array( 'form-row-wide' );
            }
            if ( isset( $fields['shipping']['shipping_first_name'] ) ) {
                $fields['shipping']['shipping_first_name']['label'] = __( '姓名', 'developer-starter' );
                $fields['shipping']['shipping_first_name']['class'] = array( 'form-row-wide' );
            }
        }
        if ( ! empty( $options['wc_hide_company'] ) ) {
            unset( $fields['billing']['billing_company'] );
            unset( $fields['shipping']['shipping_company'] );
        }
        if ( ! empty( $options['wc_hide_email'] ) ) {
            unset( $fields['billing']['billing_email'] );
        }
        
        // 始终删除
        unset( $fields['billing']['billing_address_2'] );
        unset( $fields['billing']['billing_country'] );
        unset( $fields['shipping']['shipping_address_2'] );
        unset( $fields['shipping']['shipping_country'] );
        
        return $fields;
    }

    /**
     * 中国本土化：账单地址字段
     */
    public function china_billing_fields( $fields ) {
        $options = $this->get_wc_options();
        
        if ( ! empty( $options['wc_simplified_checkout'] ) || ! empty( $options['wc_hide_email'] ) ) {
            unset( $fields['billing_email'] );
        }
        
        if ( ! empty( $options['wc_simplified_checkout'] ) ) {
            if ( isset( $fields['billing_phone'] ) ) {
                $fields['billing_phone']['label'] = __( '联系电话', 'developer-starter' );
                $fields['billing_phone']['class'] = array( 'form-row-wide' );
                $fields['billing_phone']['required'] = true;
            }
        }
        
        return $fields;
    }
    
    /**
     * 中国本土化：账户详情页字段（修改密码/资料页）
     */
    public function china_account_details_fields( $fields ) {
        $options = $this->get_wc_options();
        
        if ( ! empty( $options['wc_hide_last_name'] ) || ! empty( $options['wc_simplified_checkout'] ) ) {
            unset( $fields['account_last_name'] );
            $fields['account_first_name']['label'] = __( '姓名', 'developer-starter' );
            $fields['account_first_name']['class'] = array( 'form-row-wide' );
        }
        
        return $fields;
    }
    
    /**
     * 中国本土化：账户详情页保存验证
     */
    public function china_save_account_details_required_fields( $required_fields ) {
        $options = $this->get_wc_options();
        
        if ( ! empty( $options['wc_hide_last_name'] ) || ! empty( $options['wc_simplified_checkout'] ) ) {
            unset( $required_fields['account_last_name'] );
        }
        
        return $required_fields;
    }

    /**
     * 增强 WooCommerce 支持配置
     */
    public function enhanced_wc_support() {
        // 完整的 WooCommerce 主题支持
        add_theme_support( 'woocommerce', array(
            'thumbnail_image_width' => 300,
            'gallery_thumbnail_image_width' => 100,
            'single_image_width' => 600,
            'product_grid' => array(
                'default_rows'    => 4,
                'min_rows'        => 1,
                'default_columns' => 4,
                'min_columns'     => 2,
                'max_columns'     => 6,
            ),
        ) );

        // 产品图库支持（已在 class-theme-setup.php 中添加，这里确保存在）
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
    }

    /**
     * 页面包装器开始
     */
    public function wrapper_start() {
        $layout = $this->get_layout();
        $class = 'wc-content-wrapper';
        if ( $layout === 'with-sidebar' ) {
            $class .= ' wc-with-sidebar';
        }
        echo '<div class="container"><div class="' . esc_attr( $class ) . '"><main class="wc-main-content">';
    }

    /**
     * 页面包装器结束
     */
    public function wrapper_end() {
        echo '</main>';
    }

    /**
     * 自定义侧边栏（条件显示）
     */
    public function custom_sidebar() {
        $layout = $this->get_layout();
        if ( $layout === 'with-sidebar' && is_active_sidebar( 'sidebar-shop' ) ) {
            echo '<aside class="wc-sidebar">';
            dynamic_sidebar( 'sidebar-shop' );
            echo '</aside>';
        }
        // 关闭包装器
        echo '</div></div>';
    }

    /**
     * 获取当前页面布局
     *
     * @return string 'full-width' 或 'with-sidebar'
     */
    private function get_layout() {
        // 结账页和购物车页始终全宽
        if ( is_checkout() || is_cart() || is_account_page() ) {
            return 'full-width';
        }

        // 单品页布局
        if ( is_product() ) {
            $product_layout = $this->get_option( 'wc_product_layout', 'full-width' );
            return $product_layout;
        }

        // 商店页/分类页布局
        $shop_layout = $this->get_option( 'wc_shop_layout', 'full-width' );
        return $shop_layout;
    }

    /**
     * 商品列数
     *
     * @return int
     */
    public function shop_columns() {
        return (int) $this->get_option( 'wc_shop_columns', 4 );
    }

    /**
     * 每页商品数
     *
     * @return int
     */
    public function products_per_page() {
        return (int) $this->get_option( 'wc_products_per_page', 12 );
    }

    /**
     * 加载 WooCommerce 专用样式
     */
    public function enqueue_wc_styles() {
        if ( $this->is_wc_page() ) {
            $version = function_exists( 'developer_starter_get_assets_version' ) 
                ? developer_starter_get_assets_version() 
                : DEVELOPER_STARTER_VERSION;

            wp_enqueue_style(
                'developer-starter-woocommerce',
                DEVELOPER_STARTER_ASSETS . '/css/woocommerce.css',
                array( 'developer-starter-main' ),
                $version
            );
            
            $options = $this->get_wc_options();
            
            // 动态 CSS 生成：严格遵循后台设置
            $css = '';
            
            // 极简模式：隐藏所有非必要字段
            if ( ! empty( $options['wc_simplified_checkout'] ) ) {
                $hide_selectors = array(
                    '#billing_company_field', '#shipping_company_field',
                    '#billing_country_field', '#shipping_country_field',
                    '#billing_state_field', '#shipping_state_field',
                    '#billing_city_field', '#shipping_city_field',
                    '#billing_postcode_field', '#shipping_postcode_field',
                    '#billing_address_2_field', '#shipping_address_2_field',
                    '#billing_last_name_field', '#shipping_last_name_field',
                    '#billing_email_field'
                );
                $css .= implode( ', ', $hide_selectors ) . ' { display: none !important; }';
            } else {
                // 非极简模式：根据单独开关隐藏
                $individual_map = array(
                    'wc_hide_company'   => array( '#billing_company_field', '#shipping_company_field' ),
                    'wc_hide_state'     => array( '#billing_state_field', '#shipping_state_field' ),
                    'wc_hide_city'      => array( '#billing_city_field', '#shipping_city_field' ),
                    'wc_hide_postcode'  => array( '#billing_postcode_field', '#shipping_postcode_field' ),
                    'wc_hide_last_name' => array( '#billing_last_name_field', '#shipping_last_name_field' ),
                    'wc_hide_email'     => array( '#billing_email_field' ),
                    'wc_hide_country'   => array( '#billing_country_field', '#shipping_country_field' ),
                );
                
                foreach ( $individual_map as $opt_key => $selectors ) {
                    if ( ! empty( $options[ $opt_key ] ) ) {
                        $css .= implode( ', ', $selectors ) . ' { display: none !important; }';
                    }
                }
            }
            
            if ( $css ) {
                wp_add_inline_style( 'developer-starter-woocommerce', $css );
            }
            
            // 账户详情页姓氏移除脚本 (仅在需要时加载)
            if ( ! empty( $options['wc_simplified_checkout'] ) || ! empty( $options['wc_hide_last_name'] ) ) {
                wp_add_inline_script( 'developer-starter-woocommerce', '
                    document.addEventListener("DOMContentLoaded", function() {
                        // 移除账户详情页的姓氏字段行
                        var selectors = [
                            ".woocommerce-account input[name=\'account_last_name\']",
                            ".woocommerce-account label[for=\'account_last_name\']"
                        ];

                        selectors.forEach(function(selector) {
                            var nodes = document.querySelectorAll(selector);
                            nodes.forEach(function(node) {
                                var row = node.closest(".woocommerce-form-row");
                                if (row) {
                                    row.remove();
                                }
                            });
                        });
                    });
                ' );
            }

            // 禁用 WooCommerce 默认样式（可选）
            $disable_wc_styles = isset( $options['wc_disable_default_styles'] ) ? $options['wc_disable_default_styles'] : '';
            if ( $disable_wc_styles ) {
                wp_dequeue_style( 'woocommerce-general' );
                wp_dequeue_style( 'woocommerce-layout' );
                wp_dequeue_style( 'woocommerce-smallscreen' );
            }
        }
    }


    /**
     * 检测是否为 WooCommerce 页面
     *
     * @return bool
     */
    private function is_wc_page() {
        return function_exists( 'is_woocommerce' ) && ( 
            is_woocommerce() || 
            is_cart() || 
            is_checkout() || 
            is_account_page() 
        );
    }

    /**
     * 结账页保护（防止主题 JS/CSS 破坏）
     */
    public function checkout_protection() {
        // 确保 WC checkout 脚本正常加载
        if ( is_checkout() && ! is_wc_endpoint_url() ) {
            wp_enqueue_script( 'wc-checkout' );
            wp_enqueue_script( 'wc-country-select' );
            wp_enqueue_script( 'wc-address-i18n' );
        }
    }

    /**
     * 添加 body 类
     *
     * @param array $classes 现有 body 类
     * @return array
     */
    public function add_body_classes( $classes ) {
        if ( $this->is_wc_page() ) {
            $classes[] = 'qiling-woocommerce';
            $classes[] = sanitize_html_class( 'wc-layout-' . $this->get_layout(), 'wc-layout-full-width' );
        }
        return $classes;
    }

    /**
     * 获取 WooCommerce 选项数组（兼容序列化损坏场景）。
     *
     * @return array
     */
    private function get_wc_options() {
        if ( function_exists( 'developer_starter_get_wc_options' ) ) {
            $options = developer_starter_get_wc_options();
        } else {
            $options = array();
        }

        return is_array( $options ) ? $options : array();
    }

    /**
     * 获取 WooCommerce 设置选项
     *
     * @param string $key 选项键
     * @param mixed $default 默认值
     * @return mixed
     */
    private function get_option( $key, $default = '' ) {
        $options = $this->get_wc_options();
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }
    /**
     * 自定义面包屑 (极简风格)
     * 逻辑：商店首页 -> 分类 -> 产品
     */
    public function custom_breadcrumb() {
        $shop_page_id = wc_get_page_id( 'shop' );
        $shop_url = $shop_page_id ? get_permalink( $shop_page_id ) : home_url( '/' );
        
        if ( is_product() ) {
            $cat_html = '';
            $terms = get_the_terms( get_the_ID(), 'product_cat' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                // 获取层级最深的分类或第一个
                $cat_obj = array_shift( $terms );
                $cat_link = get_term_link( $cat_obj );
                $cat_html = '<span class="separator">/</span> <a href="' . esc_url( $cat_link ) . '">' . esc_html( $cat_obj->name ) . '</a>';
            }
            
            echo '<div class="qiling-wc-breadcrumb">';
            echo '<a href="' . esc_url( $shop_url ) . '">&laquo; ' . esc_html__( '返回商店首页', 'developer-starter' ) . '</a>';
            echo $cat_html;
            echo '</div>';
        } 
        elseif ( is_product_category() ) {
             echo '<div class="qiling-wc-breadcrumb">';
             echo '<a href="' . esc_url( $shop_url ) . '">&laquo; ' . esc_html__( '返回商店首页', 'developer-starter' ) . '</a>';
             echo '</div>';
        }
    }

    /**
     * 商店首页 Banner 输出 (支持轮播)
     */
    public function shop_banner_output() {
        if ( ! is_shop() ) {
            return;
        }

        $options = $this->get_wc_options();
        $slides = array();

        for ( $i = 1; $i <= 3; $i++ ) {
            $type = isset( $options["wc_shop_banner_type_$i"] ) ? $options["wc_shop_banner_type_$i"] : 'image';
            $url = isset( $options["wc_shop_banner_url_$i"] ) ? trim( $options["wc_shop_banner_url_$i"] ) : '';
            $link = isset( $options["wc_shop_banner_link_$i"] ) ? trim( $options["wc_shop_banner_link_$i"] ) : '';

            if ( ! empty( $url ) ) {
                $slides[] = array(
                    'type' => $type,
                    'url'  => $url,
                    'link' => $link,
                );
            }
        }

        if ( empty( $slides ) ) {
            return;
        }

        // 单张展示
        if ( count( $slides ) === 1 ) {
            $slide = $slides[0];
            echo '<div class="qiling-shop-banner is-single">';
            if ( $slide['link'] ) echo '<a href="' . esc_url( $slide['link'] ) . '">';
            
            if ( $slide['type'] === 'video' ) {
                echo '<video src="' . esc_url( $slide['url'] ) . '" autoplay loop muted playsinline></video>';
            } else {
                echo '<img src="' . esc_url( $slide['url'] ) . '" alt="' . esc_attr__( 'Shop Banner', 'developer-starter' ) . '">';
            }
            
            if ( $slide['link'] ) echo '</a>';
            echo '</div>';
            return;
        }

        // 多张轮播 (Swiper)
        wp_enqueue_script( 'swiper' );
        wp_enqueue_style( 'swiper' );

        echo '<div class="qiling-shop-banner swiper swiper-container qiling-banner-swiper">';
        echo '<div class="swiper-wrapper">';
        
        foreach ( $slides as $slide ) {
            echo '<div class="swiper-slide">';
            if ( $slide['link'] ) echo '<a href="' . esc_url( $slide['link'] ) . '" class="banner-link">';
            
            if ( $slide['type'] === 'video' ) {
                echo '<video src="' . esc_url( $slide['url'] ) . '" autoplay loop muted playsinline></video>';
            } else {
                echo '<img src="' . esc_url( $slide['url'] ) . '" alt="' . esc_attr__( 'Shop Banner', 'developer-starter' ) . '">';
            }
            
            if ( $slide['link'] ) echo '</a>';
            echo '</div>';
        }
        
        echo '</div>'; // .swiper-wrapper
        echo '<div class="swiper-pagination"></div>';
        echo '</div>'; // .swiper-container

        // 初始化脚本 (Swiper 8+)
        wp_add_inline_script( 'swiper', "
            document.addEventListener('DOMContentLoaded', function() {
                var qilingBannerSwiper = new Swiper('.qiling-banner-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                });
            });
        " );
    }

    /**
     * 商店首页分类导航栏 (Category Ribbon)
     */
    public function shop_category_nav() {
        if ( ! is_shop() ) {
            return;
        }

        $terms = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0, // 只显示顶层分类
        ) );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return;
        }

        echo '<div class="qiling-category-ribbon">';
        echo '<ul>';
        
        // 全部商品/首页链接
        $shop_link = get_permalink( wc_get_page_id( 'shop' ) );
        echo '<li class="active"><a href="' . esc_url( $shop_link ) . '">' . esc_html__( '全部', 'developer-starter' ) . '</a></li>';

        foreach ( $terms as $term ) {
            echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
        }
        
        echo '</ul>';
        echo '</div>';
    }
}

/**
 * 中国本土化：关闭商品评论
 */
add_filter( 'woocommerce_product_tabs', function( $tabs ) {
    $options = function_exists( 'developer_starter_get_wc_options' )
        ? developer_starter_get_wc_options()
        : array();
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    
    if ( ! empty( $options['wc_disable_reviews'] ) ) {
        unset( $tabs['reviews'] );
    }
    
    return $tabs;
}, 98 );

/**
 * 悬浮购物车
 */
add_action( 'wp_footer', function() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    $options = function_exists( 'developer_starter_get_wc_options' )
        ? developer_starter_get_wc_options()
        : array();
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    
    if ( empty( $options['wc_floating_cart_enable'] ) ) {
        return;
    }
    
    // 仅在 WooCommerce 页面显示
    if ( ! function_exists( 'is_woocommerce' ) || ! ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
        return;
    }

    // 结账页和购物车页不显示
    if ( is_checkout() || is_cart() ) {
        return;
    }
    
    $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $cart_url = wc_get_cart_url();
    ?>
    <div class="qiling-floating-cart" id="qiling-floating-cart">
        <a href="<?php echo esc_url( $cart_url ); ?>" class="floating-cart-btn" title="<?php esc_attr_e( '购物车', 'developer-starter' ); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
            </svg>
            <span class="cart-count<?php echo $cart_count === 0 ? ' hidden' : ''; ?>" data-count="<?php echo esc_attr( $cart_count ); ?>">
                <?php echo esc_html( $cart_count ); ?>
            </span>
        </a>
    </div>
    <?php
}, 50 );

/**
 * AJAX 更新购物车数量
 */
add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
    $cart_count = WC()->cart->get_cart_contents_count();
    
    $fragments['.qiling-floating-cart .cart-count'] = sprintf(
        '<span class="cart-count%s" data-count="%d">%d</span>',
        $cart_count === 0 ? ' hidden' : '',
        $cart_count,
        $cart_count
    );
    
    return $fragments;
});


