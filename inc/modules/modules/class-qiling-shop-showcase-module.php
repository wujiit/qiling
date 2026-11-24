<?php
/**
 * 商城精选商品展示模块（品牌大牌体验增强版）
 *
 * 展示启灵实物商城商品，支持 Swiper 横向轮播、副图悬浮平滑变图、HOT/NEW/SALE 状态角标与多币种价格格式化
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Qiling_Shop_Showcase_Module extends Module_Base {

    private const MODULE_CACHE_GROUP = 'developer_starter_module';
    private const PRODUCTS_CACHE_TTL = 10 * MINUTE_IN_SECONDS;
    private const TABLE_CACHE_TTL = HOUR_IN_SECONDS;
    private const MODULE_ID_PLACEHOLDER = '__DS_QSS_MODULE_ID__';

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-store';
        $this->description = __( '展示启灵实物商城的商品，支持横向轮播、副图切换与状态角标', 'qiling' );
    }

    public function get_id() {
        return 'qiling_shop_showcase';
    }

    public function get_name() {
        return __( '启灵商品展示 (轮播版)', 'qiling' );
    }

    public function get_fields() {
        return array(
            // === 模块整体设置 ===
            array(
                'id'      => 'qss_bg_color',
                'type'    => 'text',
                'label'   => __( '背景颜色(支持渐变)', 'qiling' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'qss_content_width',
                'type'    => 'select',
                'label'   => __( '内容宽度', 'qiling' ),
                'options' => array(
                    'container' => __( '默认容器宽度', 'qiling' ),
                    'full'      => __( '全宽', 'qiling' ),
                ),
                'default' => 'container',
            ),
            array(
                'id'      => 'qss_padding',
                'type'    => 'select',
                'label'   => __( '上下内边距', 'qiling' ),
                'options' => array(
                    '40'  => '40px',
                    '60'  => '60px',
                    '80'  => '80px',
                    '100' => '100px',
                ),
                'default' => '60',
            ),

            // === 标题设置 ===
            array(
                'id'      => 'qss_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门商品', 'Featured Products' ) : __( '热门商品', 'qiling' ),
            ),
            array(
                'id'      => 'qss_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精选实物好货，品质保证', 'Curated physical products with reliable quality.' ) : __( '精选实物好货，品质保证', 'qiling' ),
            ),

            // === 查询设置 ===
            array(
                'id'      => 'qss_limit',
                'type'    => 'text',
                'label'   => __( '显示数量', 'qiling' ),
                'default' => '8',
                'desc'    => __( '输入数字，例如 8', 'qiling' ),
            ),
            array(
                'id'      => 'qss_orderby',
                'type'    => 'select',
                'label'   => __( '排序方式', 'qiling' ),
                'options' => array(
                    'newest'     => __( '最新发布', 'qiling' ),
                    'sales'      => __( '销量最高', 'qiling' ),
                    'price_asc'  => __( '价格从低到高', 'qiling' ),
                    'price_desc' => __( '价格从高到低', 'qiling' ),
                ),
                'default' => 'newest',
            ),

            // === 样式与交互设置 ===
            array(
                'id'      => 'qss_show_cart',
                'type'    => 'select',
                'label'   => __( '显示购物车图标', 'qiling' ),
                'options' => array(
                    'yes' => __( '是', 'qiling' ),
                    'no'  => __( '否', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qss_show_badge',
                'type'    => 'select',
                'label'   => __( '显示商品状态角标', 'qiling' ),
                'options' => array(
                    'yes' => __( '是 (自动识别 HOT / NEW / SALE)', 'qiling' ),
                    'no'  => __( '否', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qss_hover_image',
                'type'    => 'select',
                'label'   => __( '悬浮切换第二张细节图', 'qiling' ),
                'options' => array(
                    'yes' => __( '开启 (有副图时平滑渐变)', 'qiling' ),
                    'no'  => __( '关闭', 'qiling' ),
                ),
                'default' => 'yes',
            ),
        );
    }

    public function render( $data = array() ) {
        global $wpdb;

        $settings = $this->normalize_settings( $data );
        $module_id = 'qss-' . uniqid();
        $fragment_key = $this->build_fragment_cache_key( $settings );

        if ( function_exists( 'developer_starter_get_fragment_cache' ) ) {
            $cached_html = \developer_starter_get_fragment_cache( $fragment_key );
            if ( is_string( $cached_html ) && '' !== $cached_html ) {
                echo str_replace( self::MODULE_ID_PLACEHOLDER, $module_id, $cached_html );
                return;
            }
        }

        $table_products = $this->get_products_table_name( $wpdb );
        if ( ! $this->has_products_table( $wpdb, $table_products ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="alert alert-warning">' . sprintf( __( '提示：未找到启灵积分商城数据表 (%s)，请确保插件已安装并激活。', 'qiling' ), esc_html( $table_products ) ) . '</div>';
            }
            return;
        }

        $products = $this->get_products( $wpdb, $table_products, $settings['limit'], $settings['orderby'] );
        if ( empty( $products ) ) {
            return;
        }

        $product_base = (string) get_option( 'qls_shop_product_base', 'shop/product' );
        $html = $this->build_module_markup( $settings, $products, $product_base, self::MODULE_ID_PLACEHOLDER );

        if ( function_exists( 'developer_starter_set_fragment_cache' ) && '' !== $html ) {
            \developer_starter_set_fragment_cache( $fragment_key, $html );
        }

        echo str_replace( self::MODULE_ID_PLACEHOLDER, $module_id, $html );
    }

    /**
     * 规范化模块设置，便于复用和生成稳定缓存键。
     *
     * @param array $data 原始模块数据
     * @return array<string,mixed>
     */
    private function normalize_settings( $data ) {
        $limit = isset( $data['qss_limit'] ) ? intval( $data['qss_limit'] ) : 8;
        if ( $limit <= 0 ) {
            $limit = 8;
        }

        $orderby = isset( $data['qss_orderby'] ) ? sanitize_key( (string) $data['qss_orderby'] ) : 'newest';
        if ( ! in_array( $orderby, array( 'newest', 'sales', 'price_asc', 'price_desc' ), true ) ) {
            $orderby = 'newest';
        }

        $content_width = isset( $data['qss_content_width'] ) ? sanitize_key( (string) $data['qss_content_width'] ) : 'container';
        if ( ! in_array( $content_width, array( 'container', 'full' ), true ) ) {
            $content_width = 'container';
        }

        $show_cart = isset( $data['qss_show_cart'] ) ? sanitize_key( (string) $data['qss_show_cart'] ) : 'yes';
        if ( ! in_array( $show_cart, array( 'yes', 'no' ), true ) ) {
            $show_cart = 'yes';
        }

        $show_badge = isset( $data['qss_show_badge'] ) ? sanitize_key( (string) $data['qss_show_badge'] ) : 'yes';
        if ( ! in_array( $show_badge, array( 'yes', 'no' ), true ) ) {
            $show_badge = 'yes';
        }

        $hover_image = isset( $data['qss_hover_image'] ) ? sanitize_key( (string) $data['qss_hover_image'] ) : 'yes';
        if ( ! in_array( $hover_image, array( 'yes', 'no' ), true ) ) {
            $hover_image = 'yes';
        }

        return array(
            'bg_color'      => isset( $data['qss_bg_color'] ) ? (string) $data['qss_bg_color'] : 'var(--color-neutral-50)',
            'content_width' => $content_width,
            'padding'       => isset( $data['qss_padding'] ) ? max( 0, intval( $data['qss_padding'] ) ) : 60,
            'title'         => isset( $data['qss_title'] ) ? (string) $data['qss_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门商品', 'Featured Products' ) : __( '热门商品', 'qiling' ) ),
            'subtitle'      => isset( $data['qss_subtitle'] ) ? (string) $data['qss_subtitle'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精选实物好货，品质保证', 'Carefully selected products with dependable quality.' ) : __( '精选实物好货，品质保证', 'qiling' ) ),
            'limit'         => $limit,
            'orderby'       => $orderby,
            'show_cart'     => $show_cart,
            'show_badge'    => $show_badge,
            'hover_image'   => $hover_image,
        );
    }

    /**
     * 构建模块片段缓存键。
     *
     * @param array<string,mixed> $settings 模块设置
     * @return string
     */
    private function build_fragment_cache_key( $settings ) {
        $lang = function_exists( 'developer_starter_get_current_frontend_lang' )
            ? (string) developer_starter_get_current_frontend_lang()
            : (string) get_locale();

        $seed = array(
            'module'       => $this->get_id(),
            'lang'         => $lang,
            'settings'     => $settings,
            'product_base' => (string) get_option( 'qls_shop_product_base', 'shop/product' ),
        );

        if ( function_exists( 'developer_starter_sort_recursive' ) ) {
            $seed = \developer_starter_sort_recursive( $seed );
        }

        return 'qss_frag_' . md5( wp_json_encode( $seed ) );
    }

    /**
     * 获取商品数据表名称。
     *
     * @param \wpdb $wpdb 数据库对象
     * @return string
     */
    private function get_products_table_name( $wpdb ) {
        $shop_prefix = defined( 'QLS_SHOP_TABLE_PREFIX' ) ? QLS_SHOP_TABLE_PREFIX : 'qls_shop_';
        return $wpdb->prefix . $shop_prefix . 'products';
    }

    /**
     * 检查商品表是否存在，并缓存结果。
     *
     * @param \wpdb  $wpdb 数据库对象
     * @param string $table_name 表名
     * @return bool
     */
    private function has_products_table( $wpdb, $table_name ) {
        $cache_key = 'qss_table_' . md5( (string) $table_name );
        $cached = $this->cache_fetch( $cache_key );
        if ( is_array( $cached ) && array_key_exists( 'exists', $cached ) ) {
            return (bool) $cached['exists'];
        }

        $exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name );
        $this->cache_store( $cache_key, array( 'exists' => $exists ), self::TABLE_CACHE_TTL );

        return $exists;
    }

    /**
     * 获取商品查询结果，并接入模块数据缓存。
     *
     * @param \wpdb  $wpdb 数据库对象
     * @param string $table_products 商品表名
     * @param int    $limit 数量
     * @param string $orderby 排序方式
     * @return array<int,object>
     */
    private function get_products( $wpdb, $table_products, $limit, $orderby ) {
        $cache_key = 'qss_products_' . md5(
            wp_json_encode(
                array(
                    'table'   => (string) $table_products,
                    'limit'   => (int) $limit,
                    'orderby' => (string) $orderby,
                )
            )
        );

        $cached = $this->cache_fetch( $cache_key );
        if ( is_array( $cached ) ) {
            return $cached;
        }

        $sql = "SELECT id, title, subtitle, slug, main_image, gallery, min_price, max_price, sales_count, is_hot, created_at 
                FROM {$table_products} 
                WHERE status = 1 ";

        switch ( $orderby ) {
            case 'sales':
                $sql .= 'ORDER BY sales_count DESC ';
                break;
            case 'price_asc':
                $sql .= 'ORDER BY min_price ASC ';
                break;
            case 'price_desc':
                $sql .= 'ORDER BY min_price DESC ';
                break;
            case 'newest':
            default:
                $sql .= 'ORDER BY created_at DESC ';
                break;
        }

        $sql .= $wpdb->prepare( 'LIMIT %d', $limit );

        $products = $wpdb->get_results( $sql );
        $products = is_array( $products ) ? $products : array();

        $this->cache_store( $cache_key, $products, self::PRODUCTS_CACHE_TTL );

        return $products;
    }

    /**
     * 读取模块公共缓存。
     *
     * @param string $cache_key 缓存键
     * @return mixed
     */
    private function cache_fetch( $cache_key ) {
        if ( function_exists( 'developer_starter_cache_fetch' ) ) {
            return \developer_starter_cache_fetch( $cache_key, self::MODULE_CACHE_GROUP );
        }

        return get_transient( $cache_key );
    }

    /**
     * 写入模块公共缓存。
     *
     * @param string $cache_key 缓存键
     * @param mixed  $value 缓存值
     * @param int    $ttl 生存时间
     * @return void
     */
    private function cache_store( $cache_key, $value, $ttl ) {
        if ( function_exists( 'developer_starter_cache_store' ) ) {
            \developer_starter_cache_store( $cache_key, $value, $ttl, self::MODULE_CACHE_GROUP );
            return;
        }

        set_transient( $cache_key, $value, $ttl );
    }

    /**
     * 解析商品主图及副图 URL（用于悬浮平滑变图）。
     *
     * @param string $main_image 主图字段
     * @param string $gallery    画册图字段
     * @return array{primary:string,secondary:string}
     */
    private function resolve_product_images( $main_image, $gallery = '' ) {
        $urls = array();

        if ( '' !== (string) $main_image ) {
            $img_data = json_decode( $main_image, true );
            if ( is_array( $img_data ) ) {
                foreach ( $img_data as $item ) {
                    if ( is_string( $item ) && '' !== $item ) {
                        $urls[] = $item;
                    } elseif ( is_array( $item ) && isset( $item['url'] ) && is_string( $item['url'] ) ) {
                        $urls[] = $item['url'];
                    }
                }
                if ( empty( $urls ) && isset( $img_data['url'] ) && is_string( $img_data['url'] ) ) {
                    $urls[] = $img_data['url'];
                }
            } elseif ( filter_var( $main_image, FILTER_VALIDATE_URL ) ) {
                $urls[] = (string) $main_image;
            }
        }

        if ( count( $urls ) < 2 && '' !== (string) $gallery ) {
            $gal_data = json_decode( $gallery, true );
            if ( is_array( $gal_data ) ) {
                foreach ( $gal_data as $item ) {
                    if ( is_string( $item ) && '' !== $item ) {
                        $urls[] = $item;
                    } elseif ( is_array( $item ) && isset( $item['url'] ) && is_string( $item['url'] ) ) {
                        $urls[] = $item['url'];
                    }
                    if ( count( $urls ) >= 2 ) {
                        break;
                    }
                }
            } elseif ( filter_var( $gallery, FILTER_VALIDATE_URL ) ) {
                $urls[] = (string) $gallery;
            }
        }

        $primary   = isset( $urls[0] ) ? $urls[0] : '';
        $secondary = ( isset( $urls[1] ) && $urls[1] !== $primary ) ? $urls[1] : '';

        return array(
            'primary'   => $primary,
            'secondary' => $secondary,
        );
    }

    /**
     * 计算商品状态角标。
     *
     * @param object $product 商品数据对象
     * @return array{type:string,text:string}|null
     */
    private function get_product_badge( $product ) {
        if ( ! empty( $product->is_hot ) ) {
            return array(
                'type' => 'hot',
                'text' => __( 'HOT', 'qiling' ),
            );
        }

        $sales = isset( $product->sales_count ) ? (int) $product->sales_count : 0;
        if ( $sales >= 30 ) {
            return array(
                'type' => 'hot',
                'text' => __( 'HOT', 'qiling' ),
            );
        }

        $created = isset( $product->created_at ) ? strtotime( (string) $product->created_at ) : 0;
        if ( $created > 0 && ( time() - $created ) < 30 * DAY_IN_SECONDS ) {
            return array(
                'type' => 'new',
                'text' => __( 'NEW', 'qiling' ),
            );
        }

        $min_p = isset( $product->min_price ) ? (float) $product->min_price : 0;
        $max_p = isset( $product->max_price ) ? (float) $product->max_price : 0;
        if ( $max_p > $min_p && $min_p > 0 ) {
            return array(
                'type' => 'sale',
                'text' => __( 'SALE', 'qiling' ),
            );
        }

        return null;
    }

    /**
     * 构建模块 HTML。
     *
     * @param array<string,mixed> $settings 模块设置
     * @param array<int,object>   $products 商品数据
     * @param string              $product_base 商品链接基础路径
     * @param string              $module_id 模块 DOM ID
     * @return string
     */
    private function build_module_markup( $settings, $products, $product_base, $module_id ) {
        $bg_color      = (string) $settings['bg_color'];
        $content_width = (string) $settings['content_width'];
        $padding       = (int) $settings['padding'];
        $title         = (string) $settings['title'];
        $subtitle      = (string) $settings['subtitle'];
        $show_cart     = (string) $settings['show_cart'];
        $show_badge    = (string) $settings['show_badge'];
        $hover_image   = (string) $settings['hover_image'];

        ob_start();
        ?>
        <section class="module module-qiling-shop-showcase" id="<?php echo esc_attr( $module_id ); ?>" style="padding: <?php echo esc_attr( $padding ); ?>px 0; background: <?php echo esc_attr( $bg_color ); ?>;">
            <style>
                #<?php echo esc_attr( $module_id ); ?> .qss-img-wrap { position: relative; overflow: hidden; }
                #<?php echo esc_attr( $module_id ); ?> .qss-img-primary { transition: opacity 0.4s ease, transform 0.5s ease; }
                #<?php echo esc_attr( $module_id ); ?> .qss-img-secondary { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.4s ease, transform 0.5s ease; }
                #<?php echo esc_attr( $module_id ); ?> .qss-product-card:hover .qss-img-primary.has-secondary { opacity: 0; }
                #<?php echo esc_attr( $module_id ); ?> .qss-product-card:hover .qss-img-secondary { opacity: 1; transform: scale(1.05); }
                #<?php echo esc_attr( $module_id ); ?> .qss-badge { position: absolute; top: 12px; left: 12px; z-index: 3; display: inline-flex; align-items: center; justify-content: center; min-width: 42px; height: 22px; padding: 0 8px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1; pointer-events: none; }
                #<?php echo esc_attr( $module_id ); ?> .qss-badge-hot { background: var(--color-error, #ef4444); color: var(--color-text-inverse, #ffffff); box-shadow: 0 4px 10px rgba(var(--color-error-rgb, 220, 38, 38), 0.35); }
                #<?php echo esc_attr( $module_id ); ?> .qss-badge-new { background: var(--color-info, #0284c7); color: var(--color-text-inverse, #ffffff); box-shadow: 0 4px 10px rgba(var(--color-info-rgb, 14, 165, 233), 0.35); }
                #<?php echo esc_attr( $module_id ); ?> .qss-badge-sale { background: var(--color-warning, #d97706); color: var(--color-text-inverse, #ffffff); box-shadow: 0 4px 10px rgba(var(--color-warning-rgb, 245, 158, 11), 0.35); }
                #<?php echo esc_attr( $module_id ); ?> .qss-cart-btn { cursor: pointer; transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.2s, color 0.2s; }
                #<?php echo esc_attr( $module_id ); ?> .qss-cart-btn:hover { transform: scale(1.15); }
                #<?php echo esc_attr( $module_id ); ?> .qss-price { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; letter-spacing: -0.02em; }
            </style>

            <div class="<?php echo $content_width === 'full' ? 'container-fluid' : 'container'; ?>">

                <?php if ( $title || $subtitle ) : ?>
                    <div class="qss-header">
                        <?php if ( $title ) : ?>
                            <h2 class="qss-title"><?php echo wp_kses_post( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <div class="qss-subtitle"><?php echo wp_kses_post( $subtitle ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="qss-carousel-container">
                    <div class="swiper qss-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ( $products as $product ) : ?>
                                <?php
                                $product_url = home_url( '/' . trim( $product_base, '/' ) . '/' . $product->slug );
                                $currency_code = function_exists( 'developer_starter_get_demo_currency_code' ) ? developer_starter_get_demo_currency_code() : 'CNY';
                                $price_html = function_exists( 'developer_starter_format_currency_amount' )
                                    ? developer_starter_format_currency_amount( $product->min_price, $currency_code, 2 )
                                    : 'CNY ' . number_format_i18n( $product->min_price, 2 );
                                $images = $this->resolve_product_images(
                                    isset( $product->main_image ) ? (string) $product->main_image : '',
                                    isset( $product->gallery ) ? (string) $product->gallery : ''
                                );
                                $badge = ( 'yes' === $show_badge ) ? $this->get_product_badge( $product ) : null;
                                $has_secondary = ( 'yes' === $hover_image && ! empty( $images['secondary'] ) );
                                ?>
                                <div class="swiper-slide">
                                    <a href="<?php echo esc_url( $product_url ); ?>" class="qss-product-card" target="_blank">
                                        <div class="qss-img-wrap">
                                            <?php if ( ! empty( $badge ) ) : ?>
                                                <span class="qss-badge qss-badge-<?php echo esc_attr( $badge['type'] ); ?>"><?php echo esc_html( $badge['text'] ); ?></span>
                                            <?php endif; ?>

                                            <?php if ( '' !== $images['primary'] ) : ?>
                                                <img src="<?php echo esc_url( $images['primary'] ); ?>" alt="<?php echo esc_attr( $product->title ); ?>" class="qss-img qss-img-primary<?php echo $has_secondary ? ' has-secondary' : ''; ?>" loading="lazy" decoding="async">
                                                <?php if ( $has_secondary ) : ?>
                                                    <img src="<?php echo esc_url( $images['secondary'] ); ?>" alt="<?php echo esc_attr( $product->title ); ?>" class="qss-img qss-img-secondary" loading="lazy" decoding="async">
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <div class="qss-img-placeholder">
                                                    <span class="dashicons dashicons-format-image"></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="qss-overlay"></div>
                                        </div>

                                        <div class="qss-info">
                                            <h3 class="qss-name" title="<?php echo esc_attr( $product->title ); ?>">
                                                <?php echo esc_html( $product->title ); ?>
                                            </h3>

                                            <div class="qss-meta">
                                                <span class="qss-price"><?php echo esc_html( $price_html ); ?></span>

                                                <?php if ( 'yes' === $show_cart ) : ?>
                                                    <span class="qss-cart-btn" aria-label="<?php esc_attr_e( '查看商品', 'qiling' ); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="9" cy="21" r="1"></circle>
                                                            <circle cx="20" cy="21" r="1"></circle>
                                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                                        </svg>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="swiper-button-prev qss-prev"></div>
                        <div class="swiper-button-next qss-next"></div>
                        <div class="swiper-pagination qss-pagination"></div>
                    </div>
                </div>

            </div>
        </section>
        <script>
            (function() {
            function boot() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root || root.dataset.qilingShopShowcaseInitialized) return;
                root.dataset.qilingShopShowcaseInitialized = 'true';
                var container = root.querySelector('.qss-swiper');

                function initSwiper() {
                    if (!container) {
                        return true;
                    }
                    if (container.classList.contains('swiper-initialized')) {
                        return true;
                    }
                    if (typeof Swiper === 'undefined') {
                        return false;
                    }

                    new Swiper(container, {
                        slidesPerView: 1.2,
                        spaceBetween: 16,
                        loop: false,
                        autoplay: {
                            delay: 5000,
                            disableOnInteraction: false,
                        },
                        pagination: {
                            el: root.querySelector('.qss-pagination'),
                            clickable: true,
                        },
                        navigation: {
                            nextEl: root.querySelector('.qss-next'),
                            prevEl: root.querySelector('.qss-prev'),
                        },
                        breakpoints: {
                            576: {
                                slidesPerView: 2,
                                spaceBetween: 20
                            },
                            768: {
                                slidesPerView: 3,
                                spaceBetween: 24
                            },
                            1024: {
                                slidesPerView: 4,
                                spaceBetween: 24
                            }
                        }
                    });
                    return true;
                }

                if (initSwiper()) {
                    return;
                }

                var retryCount = 0;
                var retryTimer = setInterval(function() {
                    if (!root.isConnected) {
                        clearInterval(retryTimer);
                        return;
                    }
                    retryCount++;
                    if (initSwiper() || retryCount >= 100) {
                        clearInterval(retryTimer);
                    }
                }, 100);
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot, { once: true });
            } else {
                boot();
            }
            })();
        </script>
        <?php

        return (string) ob_get_clean();
    }
}
