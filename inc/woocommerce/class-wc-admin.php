<?php
/**
 * WooCommerce Admin Settings Class
 *
 * 在主题设置中添加 WooCommerce 专用设置页面
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
 * WooCommerce 后台设置类
 */
class WC_Admin {

    /**
     * 设置页面 slug
     */
    const PAGE_SLUG = 'developer-starter-woocommerce';

    /**
     * 选项名称
     */
    const OPTION_NAME = 'developer_starter_wc_options';

    /**
     * 构造函数
     */
    public function __construct() {
        if ( function_exists( 'developer_starter_is_woocommerce_admin_menu_enabled' ) && ! developer_starter_is_woocommerce_admin_menu_enabled() ) {
            return;
        }

        // 注册设置页面（始终显示，即使 WooCommerce 未安装）
        add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // 管理页面样式
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
    }

    /**
     * 添加子菜单页面
     */
    public function add_submenu() {
        add_submenu_page(
            'developer-starter-settings',           // 父菜单 slug（主题设置）
            __( 'WooCommerce 设置', 'developer-starter' ),  // 页面标题
            __( 'WooCommerce', 'developer-starter' ),       // 菜单标题
            'manage_options',                       // 权限
            self::PAGE_SLUG,                        // slug
            array( $this, 'render_page' )           // 回调
        );
    }

    /**
     * 注册设置
     */
    public function register_settings() {
        register_setting(
            self::OPTION_NAME,
            self::OPTION_NAME,
            array( $this, 'sanitize_options' )
        );
    }

    /**
     * 清理选项
     *
     * @param array $input 输入数据
     * @return array 清理后的数据
     */
    public function sanitize_options( $input ) {
        $sanitized = array();

        // 布局设置
        $sanitized['wc_shop_layout'] = isset( $input['wc_shop_layout'] ) 
            ? sanitize_text_field( $input['wc_shop_layout'] ) 
            : 'full-width';

        $sanitized['wc_product_layout'] = isset( $input['wc_product_layout'] ) 
            ? sanitize_text_field( $input['wc_product_layout'] ) 
            : 'full-width';

        // 数字设置
        $sanitized['wc_shop_columns'] = isset( $input['wc_shop_columns'] ) 
            ? absint( $input['wc_shop_columns'] ) 
            : 4;

        $sanitized['wc_products_per_page'] = isset( $input['wc_products_per_page'] ) 
            ? absint( $input['wc_products_per_page'] ) 
            : 12;

        // 开关设置
        $checkboxes = array(
            'wc_ajax_cart',
            'wc_remove_breadcrumb',
            'wc_disable_default_styles',
            // 中国本土化设置
            'wc_hide_country',      // 隐藏国家
            'wc_hide_postcode',
            'wc_hide_city',
            'wc_hide_state',
            'wc_hide_last_name',
            'wc_hide_email',
            'wc_hide_company',
            'wc_simplified_checkout',
            'wc_disable_reviews',
            // 悬浮购物车
            'wc_floating_cart_enable',
        );

        foreach ( $checkboxes as $key ) {
            $sanitized[ $key ] = isset( $input[ $key ] ) && $input[ $key ] === '1' ? '1' : '';
        }

        // Shop Banner (3 Slots)
        for ( $i = 1; $i <= 3; $i++ ) {
            $sanitized["wc_shop_banner_type_$i"] = isset( $input["wc_shop_banner_type_$i"] ) ? sanitize_text_field( $input["wc_shop_banner_type_$i"] ) : 'image';
            $sanitized["wc_shop_banner_url_$i"] = isset( $input["wc_shop_banner_url_$i"] ) ? sanitize_text_field( $input["wc_shop_banner_url_$i"] ) : '';
            $sanitized["wc_shop_banner_link_$i"] = isset( $input["wc_shop_banner_link_$i"] ) ? sanitize_text_field( $input["wc_shop_banner_link_$i"] ) : '';
        }

        return $sanitized;
    }

    /**
     * 管理页面样式
     *
     * @param string $hook 当前页面 hook
     */
    public function admin_styles( $hook ) {
        if ( strpos( $hook, self::PAGE_SLUG ) === false ) {
            return;
        }

        wp_add_inline_style( 'developer-starter-admin', '
            .qiling-wc-settings { max-width: 800px; }
            .qiling-wc-settings .form-table th { width: 200px; }
            .qiling-wc-settings h2 { 
                margin: 30px 0 15px; 
                padding-bottom: 10px; 
                border-bottom: 1px solid #e2e8f0; 
                font-size: 1.2rem;
            }
            .qiling-wc-settings h2:first-of-type { margin-top: 0; }
            .qiling-wc-settings .description { 
                color: #6b7280; 
                font-style: normal;
                margin-top: 4px;
            }
            .qiling-wc-settings select,
            .qiling-wc-settings input[type="number"] {
                min-width: 200px;
            }
            .qiling-wc-notice {
                background: #f0f9ff;
                border-left: 4px solid #3b82f6;
                padding: 12px 16px;
                margin-bottom: 20px;
                border-radius: 0 4px 4px 0;
            }
        ' );
    }

    /**
     * 渲染设置页面
     */
    public function render_page() {
        // 检查权限
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $wc_active = class_exists( 'WooCommerce' );
        $options = get_option( self::OPTION_NAME, array() );

        // 默认值
        $defaults = array(
            'wc_shop_layout' => 'full-width',
            'wc_product_layout' => 'full-width',
            'wc_shop_columns' => 4,
            'wc_products_per_page' => 12,
            'wc_ajax_cart' => '',
            'wc_remove_breadcrumb' => '',
            'wc_disable_default_styles' => '',
            // 中国本土化设置
            'wc_hide_country' => '1',       // 默认隐藏国家
            'wc_hide_postcode' => '1',      // 默认隐藏邮编
            'wc_hide_city' => '',
            'wc_hide_state' => '',
            'wc_hide_last_name' => '1',     // 默认隐藏姓氏
            'wc_hide_email' => '',
            'wc_hide_company' => '1',       // 默认隐藏公司
            'wc_simplified_checkout' => '',
            'wc_disable_reviews' => '',
            // 悬浮购物车
            'wc_floating_cart_enable' => '1', // 默认启用
        );

        $options = wp_parse_args( $options, $defaults );
        ?>
        <div class="wrap qiling-wc-settings">
            <h1><?php esc_html_e( 'WooCommerce 主题设置', 'developer-starter' ); ?></h1>

            <?php if ( ! $wc_active ) : ?>
            <div class="notice notice-warning" style="padding: 12px 16px; margin: 20px 0;">
                <p>
                    <strong><?php esc_html_e( '⚠️ WooCommerce 未安装或未激活', 'developer-starter' ); ?></strong><br>
                    <?php esc_html_e( '您可以预先配置这些设置，安装并激活 WooCommerce 插件后，这些设置将自动生效。', 'developer-starter' ); ?>
                </p>
                <p>
                    <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>" class="button button-primary">
                        <?php esc_html_e( '安装 WooCommerce', 'developer-starter' ); ?>
                    </a>
                </p>
            </div>
            <?php else : ?>
            <div class="qiling-wc-notice">
                <strong><?php esc_html_e( '提示', 'developer-starter' ); ?>：</strong>
                <?php esc_html_e( '这些设置仅影响主题对 WooCommerce 的样式和布局处理，不会修改 WooCommerce 核心功能。', 'developer-starter' ); ?>
            </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields( self::OPTION_NAME ); ?>

                <h2><?php esc_html_e( '布局设置', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wc_shop_layout"><?php esc_html_e( '商店页面布局', 'developer-starter' ); ?></label>
                        </th>
                        <td>
                            <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_shop_layout]" id="wc_shop_layout">
                                <option value="full-width" <?php selected( $options['wc_shop_layout'], 'full-width' ); ?>>
                                    <?php esc_html_e( '全宽布局', 'developer-starter' ); ?>
                                </option>
                                <option value="with-sidebar" <?php selected( $options['wc_shop_layout'], 'with-sidebar' ); ?>>
                                    <?php esc_html_e( '带侧边栏', 'developer-starter' ); ?>
                                </option>
                            </select>
                            <p class="description"><?php esc_html_e( '应用于商店页面和商品分类页面', 'developer-starter' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_product_layout"><?php esc_html_e( '单品页面布局', 'developer-starter' ); ?></label>
                        </th>
                        <td>
                            <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_product_layout]" id="wc_product_layout">
                                <option value="full-width" <?php selected( $options['wc_product_layout'], 'full-width' ); ?>>
                                    <?php esc_html_e( '全宽布局', 'developer-starter' ); ?>
                                </option>
                                <option value="with-sidebar" <?php selected( $options['wc_product_layout'], 'with-sidebar' ); ?>>
                                    <?php esc_html_e( '带侧边栏', 'developer-starter' ); ?>
                                </option>
                            </select>
                            <p class="description"><?php esc_html_e( '应用于单个商品详情页面', 'developer-starter' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_shop_columns"><?php esc_html_e( '每行商品数', 'developer-starter' ); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_shop_columns]" 
                                   id="wc_shop_columns"
                                   value="<?php echo esc_attr( $options['wc_shop_columns'] ); ?>" 
                                   min="2" 
                                   max="6"
                                   step="1">
                            <p class="description"><?php esc_html_e( '商店页面每行显示的商品数量（2-6）', 'developer-starter' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_products_per_page"><?php esc_html_e( '每页商品数', 'developer-starter' ); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_products_per_page]" 
                                   id="wc_products_per_page"
                                   value="<?php echo esc_attr( $options['wc_products_per_page'] ); ?>" 
                                   min="4" 
                                   max="48"
                                   step="4">
                            <p class="description"><?php esc_html_e( '商店页面每页显示的商品数量', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Shop Banner 设置 (轮播)', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <?php for ( $i = 1; $i <= 3; $i++ ) : 
                        $type_key = "wc_shop_banner_type_$i";
                        $url_key = "wc_shop_banner_url_$i";
                        $link_key = "wc_shop_banner_link_$i";
                    ?>
                    <tr>
                        <th scope="row">
                            <h3><?php echo sprintf( esc_html__( 'Banner #%d', 'developer-starter' ), $i ); ?></h3>
                        </th>
                        <td>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; max-width: 600px;">
                                <p>
                                    <label style="display:inline-block; width: 100px;"><strong><?php esc_html_e( '类型:', 'developer-starter' ); ?></strong></label>
                                    <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo $type_key; ?>]">
                                        <option value="image" <?php selected( isset($options[$type_key]) ? $options[$type_key] : '', 'image' ); ?>><?php esc_html_e( '图片 (Image)', 'developer-starter' ); ?></option>
                                        <option value="video" <?php selected( isset($options[$type_key]) ? $options[$type_key] : '', 'video' ); ?>><?php esc_html_e( '视频 (Video MP4)', 'developer-starter' ); ?></option>
                                    </select>
                                </p>
                                <p>
                                    <label style="display:inline-block; width: 100px;"><strong><?php esc_html_e( '资源 URL:', 'developer-starter' ); ?></strong></label>
                                    <input type="text" class="regular-text" style="width: 100%; max-width: 400px;" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo $url_key; ?>]" value="<?php echo esc_attr( isset($options[$url_key]) ? $options[$url_key] : '' ); ?>" placeholder="<?php echo esc_attr__( 'https://... (图片或MP4链接)', 'developer-starter' ); ?>">
                                </p>
                                <p>
                                    <label style="display:inline-block; width: 100px;"><strong><?php esc_html_e( '点击跳转:', 'developer-starter' ); ?></strong></label>
                                    <input type="text" class="regular-text" style="width: 100%; max-width: 400px;" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo $link_key; ?>]" value="<?php echo esc_attr( isset($options[$link_key]) ? $options[$link_key] : '' ); ?>">
                                </p>
                            </div>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </table>

                <h2><?php esc_html_e( '功能开关', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Ajax 添加到购物车', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_ajax_cart]" 
                                       value="1" 
                                       <?php checked( $options['wc_ajax_cart'], '1' ); ?>>
                                <?php esc_html_e( '启用 Ajax 添加到购物车增强', 'developer-starter' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( '在商品列表页启用平滑的 Ajax 添加到购物车体验', 'developer-starter' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '移除 WC 面包屑', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_remove_breadcrumb]" 
                                       value="1" 
                                       <?php checked( $options['wc_remove_breadcrumb'], '1' ); ?>>
                                <?php esc_html_e( '移除 WooCommerce 默认面包屑', 'developer-starter' ); ?>
                            </label>

                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( '高级设置', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( '禁用 WC 默认样式', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_disable_default_styles]" 
                                       value="1" 
                                       <?php checked( $options['wc_disable_default_styles'], '1' ); ?>>
                                <?php esc_html_e( '禁用 WooCommerce 默认样式表', 'developer-starter' ); ?>
                            </label>

                        </td>
                    </tr>
                </table>

                <h2>🇨🇳 <?php esc_html_e( '中国本土化', 'developer-starter' ); ?></h2>
                <p class="description" style="margin-top: -10px; margin-bottom: 15px;">
                    <?php esc_html_e( '简化结账流程，适应中国用户习惯', 'developer-starter' ); ?>
                </p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( '隐藏国家/地区', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_hide_country]" 
                                       value="1" 
                                       <?php checked( $options['wc_hide_country'], '1' ); ?>>
                                <?php esc_html_e( '隐藏国家/地区选择字段', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '隐藏邮政编码', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_hide_postcode]" 
                                       value="1" 
                                       <?php checked( $options['wc_hide_postcode'], '1' ); ?>>
                                <?php esc_html_e( '隐藏邮政编码字段（中国购物不常用）', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '隐藏姓氏字段', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_hide_last_name]" 
                                       value="1" 
                                       <?php checked( $options['wc_hide_last_name'], '1' ); ?>>
                                <?php esc_html_e( '隐藏姓氏字段，仅保留姓名（中国用户习惯填全名）', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '隐藏公司字段', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_hide_company]" 
                                       value="1" 
                                       <?php checked( $options['wc_hide_company'], '1' ); ?>>
                                <?php esc_html_e( '隐藏公司名称字段', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '隐藏城市字段', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_hide_city]" 
                                       value="1" 
                                       <?php checked( $options['wc_hide_city'], '1' ); ?>>
                                <?php esc_html_e( '隐藏城市字段', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '隐藏省/州字段', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_hide_state]" 
                                       value="1" 
                                       <?php checked( $options['wc_hide_state'], '1' ); ?>>
                                <?php esc_html_e( '隐藏省/州选择字段', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '隐藏邮箱字段', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_hide_email]" 
                                       value="1" 
                                       <?php checked( $options['wc_hide_email'], '1' ); ?>>
                                <?php esc_html_e( '隐藏邮箱字段（需确保有其他联系方式）', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '极简结账模式', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_simplified_checkout]" 
                                       value="1" 
                                       <?php checked( $options['wc_simplified_checkout'], '1' ); ?>>
                                <?php esc_html_e( '仅保留：姓名、收货地址、电话', 'developer-starter' ); ?>
                            </label>
                            <p class="description" style="color: #f59e0b;">
                                <?php esc_html_e( '⚠️ 启用后将覆盖上方所有字段设置', 'developer-starter' ); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( '关闭商品评论', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_disable_reviews]" 
                                       value="1" 
                                       <?php checked( $options['wc_disable_reviews'], '1' ); ?>>
                                <?php esc_html_e( '禁用商品详情页的评论功能', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h2>🛒 <?php esc_html_e( '悬浮购物车', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( '启用悬浮购物车', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr( self::OPTION_NAME ); ?>[wc_floating_cart_enable]" 
                                       value="1" 
                                       <?php checked( $options['wc_floating_cart_enable'], '1' ); ?>>
                                <?php esc_html_e( '在页面右侧显示悬浮购物车图标', 'developer-starter' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( '显示购物车商品数量，点击进入购物车页面', 'developer-starter' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( '保存设置', 'developer-starter' ) ); ?>
            </form>
        </div>
        <?php
    }
}
