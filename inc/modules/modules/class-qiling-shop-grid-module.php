<?php
/**
 * 启灵商城多分类商品矩阵模块
 *
 * 外贸独立站与大品牌商城的核心商品货架模块。
 * 支持商城分类 Tab 实时无刷新切换、3列/4列网格排版、大牌悬浮副图、HOT/NEW/SALE 状态角标与快速加购。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Qiling_Shop_Grid_Module extends Module_Base {

    private const MODULE_CACHE_GROUP    = 'developer_starter_module';
    private const PRODUCTS_CACHE_TTL    = 10 * MINUTE_IN_SECONDS;
    private const TABLE_CACHE_TTL       = HOUR_IN_SECONDS;
    private const MODULE_ID_PLACEHOLDER = '__DS_QSG_MODULE_ID__';

    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-grid-view';
        $this->description = __( '多分类 Tab 实时无刷新切换、3/4列网格排版、大牌悬浮副图与快速加购', 'qiling' );
    }

    public function get_id() {
        return 'qiling_shop_grid';
    }

    public function get_name() {
        return __( '启灵商城商品矩阵', 'qiling' );
    }

    public function get_fields() {
        return array(
            // === 整体基础设置 ===
            array(
                'id'      => 'qsg_bg_color',
                'type'    => 'text',
                'label'   => __( '背景颜色(支持渐变)', 'qiling' ),
                'default' => 'var(--color-surface)',
            ),
            array(
                'id'      => 'qsg_content_width',
                'type'    => 'select',
                'label'   => __( '内容宽度', 'qiling' ),
                'options' => array(
                    'container' => __( '默认容器宽度', 'qiling' ),
                    'full'      => __( '全宽容器', 'qiling' ),
                ),
                'default' => 'container',
            ),
            array(
                'id'      => 'qsg_padding',
                'type'    => 'select',
                'label'   => __( '上下内边距', 'qiling' ),
                'options' => array(
                    '40'  => '40px',
                    '60'  => '60px',
                    '80'  => '80px',
                    '100' => '100px',
                    '120' => '120px',
                ),
                'default' => '80',
            ),

            // === 标题与导语 ===
            array(
                'id'      => 'qsg_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全系产品矩阵', 'Featured Collections' ) : __( '全系产品矩阵', 'qiling' ),
            ),
            array(
                'id'      => 'qsg_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '探索官方精选全系列产品，享全球无忧质保与极速配送', 'Explore our curated lineup with global warranty & fast shipping.' ) : __( '探索官方精选全系列产品，享全球无忧质保与极速配送', 'qiling' ),
            ),

            // === 排版与展示设置 ===
            array(
                'id'      => 'qsg_columns',
                'type'    => 'select',
                'label'   => __( '网格列数', 'qiling' ),
                'options' => array(
                    '3' => __( '3 列 (大画幅/质感更强)', 'qiling' ),
                    '4' => __( '4 列 (经典独立站电商/密度适中)', 'qiling' ),
                ),
                'default' => '4',
            ),
            array(
                'id'      => 'qsg_show_tabs',
                'type'    => 'select',
                'label'   => __( '启用分类 Tab 切换', 'qiling' ),
                'options' => array(
                    'yes' => __( '开启 (自动读取商城前 6 个热门分类)', 'qiling' ),
                    'no'  => __( '关闭 (仅平铺展示全部商品)', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qsg_limit',
                'type'    => 'text',
                'label'   => __( '每分类展示商品数量', 'qiling' ),
                'default' => '8',
                'desc'    => __( '建议填 6 或 8', 'qiling' ),
            ),
            array(
                'id'      => 'qsg_orderby',
                'type'    => 'select',
                'label'   => __( '商品排序方式', 'qiling' ),
                'options' => array(
                    'sales'      => __( '销量优先 (畅销爆款)', 'qiling' ),
                    'newest'     => __( '最新发布 (新品推荐)', 'qiling' ),
                    'price_asc'  => __( '价格从低到高', 'qiling' ),
                    'price_desc' => __( '价格从高到低', 'qiling' ),
                ),
                'default' => 'sales',
            ),

            // === 品牌与交互设置 ===
            array(
                'id'      => 'qsg_show_badge',
                'type'    => 'select',
                'label'   => __( '显示状态角标', 'qiling' ),
                'options' => array(
                    'yes' => __( '开启 (自动识别 HOT / NEW / SALE)', 'qiling' ),
                    'no'  => __( '关闭', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qsg_hover_image',
                'type'    => 'select',
                'label'   => __( '悬浮平滑切换副图', 'qiling' ),
                'options' => array(
                    'yes' => __( '开启 (有相册副图时平滑展示)', 'qiling' ),
                    'no'  => __( '关闭', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qsg_show_cart',
                'type'    => 'select',
                'label'   => __( '显示加购胶囊按钮', 'qiling' ),
                'options' => array(
                    'yes' => __( '是', 'qiling' ),
                    'no'  => __( '否', 'qiling' ),
                ),
                'default' => 'yes',
            ),

            // === 底部按钮 ===
            array(
                'id'      => 'qsg_show_more_btn',
                'type'    => 'select',
                'label'   => __( '底部显示“查看全部”按钮', 'qiling' ),
                'options' => array(
                    'yes' => __( '是', 'qiling' ),
                    'no'  => __( '否', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qsg_more_btn_text',
                'type'    => 'text',
                'label'   => __( '查看全部按钮文字', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '探索全部商品 →', 'Explore All Products →' ) : __( '探索全部商品 →', 'qiling' ),
            ),
            array(
                'id'      => 'qsg_more_btn_url',
                'type'    => 'text',
                'label'   => __( '查看全部跳转链接', 'qiling' ),
                'default' => '',
                'desc'    => __( '留空则自动跳转启灵商城商品主目录', 'qiling' ),
            ),
        );
    }

    public function render( $data = array() ) {
        global $wpdb;

        $settings     = $this->normalize_settings( $data );
        $module_id    = 'qsg-' . uniqid();
        $fragment_key = $this->build_fragment_cache_key( $settings );

        if ( function_exists( 'developer_starter_get_fragment_cache' ) ) {
            $cached_html = \developer_starter_get_fragment_cache( $fragment_key );
            if ( is_string( $cached_html ) && '' !== $cached_html ) {
                echo str_replace( self::MODULE_ID_PLACEHOLDER, $module_id, $cached_html );
                return;
            }
        }

        $table_products   = $this->get_table_name( $wpdb, 'products' );
        $table_categories = $this->get_table_name( $wpdb, 'categories' );
        $categories       = array();
        $product_data     = array();

        if ( $this->has_table( $wpdb, $table_products ) ) {
            if ( 'yes' === $settings['show_tabs'] && $this->has_table( $wpdb, $table_categories ) ) {
                $categories = $this->get_active_categories( $wpdb, $table_categories );
            }
            $product_data = $this->get_grouped_products( $wpdb, $table_products, $categories, $settings['limit'], $settings['orderby'] );
        }

        // 智能兜底：未安装商城表或商城暂无商品时（如模板中心实时预览、新站部署），自动启用高质量演示数据
        if ( empty( $product_data['all'] ) ) {
            $demo_pack    = $this->get_demo_data( $settings['limit'] );
            $categories   = $demo_pack['categories'];
            $product_data = $demo_pack['product_data'];
        }

        $product_base = (string) get_option( 'qls_shop_product_base', 'shop/product' );
        $html         = $this->build_module_markup( $settings, $categories, $product_data, $product_base, self::MODULE_ID_PLACEHOLDER );

        if ( function_exists( 'developer_starter_set_fragment_cache' ) && '' !== $html ) {
            \developer_starter_set_fragment_cache( $fragment_key, $html );
        }

        echo str_replace( self::MODULE_ID_PLACEHOLDER, $module_id, $html );
    }

    private function normalize_settings( $data ) {
        $limit = isset( $data['qsg_limit'] ) ? intval( $data['qsg_limit'] ) : 8;
        if ( $limit <= 0 ) {
            $limit = 8;
        }

        $columns = isset( $data['qsg_columns'] ) ? sanitize_key( (string) $data['qsg_columns'] ) : '4';
        if ( ! in_array( $columns, array( '3', '4' ), true ) ) {
            $columns = '4';
        }

        $orderby = isset( $data['qsg_orderby'] ) ? sanitize_key( (string) $data['qsg_orderby'] ) : 'sales';
        if ( ! in_array( $orderby, array( 'sales', 'newest', 'price_asc', 'price_desc' ), true ) ) {
            $orderby = 'sales';
        }

        $content_width = isset( $data['qsg_content_width'] ) ? sanitize_key( (string) $data['qsg_content_width'] ) : 'container';
        if ( ! in_array( $content_width, array( 'container', 'full' ), true ) ) {
            $content_width = 'container';
        }

        $show_tabs = isset( $data['qsg_show_tabs'] ) ? sanitize_key( (string) $data['qsg_show_tabs'] ) : 'yes';
        if ( ! in_array( $show_tabs, array( 'yes', 'no' ), true ) ) {
            $show_tabs = 'yes';
        }

        $show_cart = isset( $data['qsg_show_cart'] ) ? sanitize_key( (string) $data['qsg_show_cart'] ) : 'yes';
        if ( ! in_array( $show_cart, array( 'yes', 'no' ), true ) ) {
            $show_cart = 'yes';
        }

        $show_badge = isset( $data['qsg_show_badge'] ) ? sanitize_key( (string) $data['qsg_show_badge'] ) : 'yes';
        if ( ! in_array( $show_badge, array( 'yes', 'no' ), true ) ) {
            $show_badge = 'yes';
        }

        $hover_image = isset( $data['qsg_hover_image'] ) ? sanitize_key( (string) $data['qsg_hover_image'] ) : 'yes';
        if ( ! in_array( $hover_image, array( 'yes', 'no' ), true ) ) {
            $hover_image = 'yes';
        }

        $show_more_btn = isset( $data['qsg_show_more_btn'] ) ? sanitize_key( (string) $data['qsg_show_more_btn'] ) : 'yes';
        if ( ! in_array( $show_more_btn, array( 'yes', 'no' ), true ) ) {
            $show_more_btn = 'yes';
        }

        return array(
            'bg_color'      => isset( $data['qsg_bg_color'] ) ? (string) $data['qsg_bg_color'] : 'var(--color-surface)',
            'content_width' => $content_width,
            'padding'       => isset( $data['qsg_padding'] ) ? max( 0, intval( $data['qsg_padding'] ) ) : 80,
            'title'         => isset( $data['qsg_title'] ) ? (string) $data['qsg_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全系产品矩阵', 'Featured Collections' ) : __( '全系产品矩阵', 'qiling' ) ),
            'subtitle'      => isset( $data['qsg_subtitle'] ) ? (string) $data['qsg_subtitle'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '探索官方精选全系列产品，享全球无忧质保与极速配送', 'Explore our curated lineup with global warranty & fast shipping.' ) : __( '探索官方精选全系列产品，享全球无忧质保与极速配送', 'qiling' ) ),
            'columns'       => $columns,
            'show_tabs'     => $show_tabs,
            'limit'         => $limit,
            'orderby'       => $orderby,
            'show_cart'     => $show_cart,
            'show_badge'    => $show_badge,
            'hover_image'   => $hover_image,
            'show_more_btn' => $show_more_btn,
            'more_btn_text' => isset( $data['qsg_more_btn_text'] ) ? (string) $data['qsg_more_btn_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '探索全部商品 →', 'Explore All Products →' ) : __( '探索全部商品 →', 'qiling' ) ),
            'more_btn_url'  => isset( $data['qsg_more_btn_url'] ) ? (string) $data['qsg_more_btn_url'] : '',
        );
    }

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

        return 'qsg_frag_' . md5( wp_json_encode( $seed ) );
    }

    private function get_table_name( $wpdb, $table_suffix ) {
        $shop_prefix = defined( 'QLS_SHOP_TABLE_PREFIX' ) ? QLS_SHOP_TABLE_PREFIX : 'qls_shop_';
        return $wpdb->prefix . $shop_prefix . $table_suffix;
    }

    private function has_table( $wpdb, $table_name ) {
        $cache_key = 'qsg_tbl_' . md5( (string) $table_name );
        $cached    = $this->cache_fetch( $cache_key );
        if ( is_array( $cached ) && array_key_exists( 'exists', $cached ) ) {
            return (bool) $cached['exists'];
        }

        $exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name );
        $this->cache_store( $cache_key, array( 'exists' => $exists ), self::TABLE_CACHE_TTL );

        return $exists;
    }

    private function get_active_categories( $wpdb, $table_categories ) {
        $cache_key = 'qsg_active_cats';
        $cached    = $this->cache_fetch( $cache_key );
        if ( is_array( $cached ) ) {
            return $cached;
        }

        $sql  = "SELECT id, name, slug FROM {$table_categories} WHERE status = 1 ORDER BY sort_order ASC, id ASC LIMIT 6";
        $rows = $wpdb->get_results( $sql );
        $rows = is_array( $rows ) ? $rows : array();

        $this->cache_store( $cache_key, $rows, self::PRODUCTS_CACHE_TTL );
        return $rows;
    }

    private function get_grouped_products( $wpdb, $table_products, $categories, $limit, $orderby ) {
        $cache_key = 'qsg_grp_prods_' . md5( wp_json_encode( array( 'limit' => (int) $limit, 'orderby' => (string) $orderby ) ) );
        $cached    = $this->cache_fetch( $cache_key );
        if ( is_array( $cached ) ) {
            return $cached;
        }

        $order_clause = 'ORDER BY sales_count DESC ';
        if ( 'newest' === $orderby ) {
            $order_clause = 'ORDER BY created_at DESC ';
        } elseif ( 'price_asc' === $orderby ) {
            $order_clause = 'ORDER BY min_price ASC ';
        } elseif ( 'price_desc' === $orderby ) {
            $order_clause = 'ORDER BY min_price DESC ';
        }

        // 1. 获取全量前 N 个
        $sql_all = "SELECT id, title, subtitle, slug, main_image, gallery, min_price, max_price, sales_count, is_hot, category_id, created_at 
                    FROM {$table_products} 
                    WHERE status = 1 {$order_clause} " . $wpdb->prepare( 'LIMIT %d', $limit );
        $all_prods = $wpdb->get_results( $sql_all );
        $all_prods = is_array( $all_prods ) ? $all_prods : array();

        $grouped = array( 'all' => $all_prods );

        // 2. 如果存在分类，按各分类拉取
        if ( ! empty( $categories ) ) {
            foreach ( $categories as $cat ) {
                $cat_id = absint( $cat->id );
                $sql_cat = "SELECT id, title, subtitle, slug, main_image, gallery, min_price, max_price, sales_count, is_hot, category_id, created_at 
                            FROM {$table_products} 
                            WHERE status = 1 AND category_id = %d {$order_clause} " . $wpdb->prepare( 'LIMIT %d', $limit );
                $cat_prods = $wpdb->get_results( $wpdb->prepare( $sql_cat, $cat_id ) );
                $grouped[ 'cat_' . $cat_id ] = is_array( $cat_prods ) ? $cat_prods : array();
            }
        }

        $this->cache_store( $cache_key, $grouped, self::PRODUCTS_CACHE_TTL );
        return $grouped;
    }

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

    private function get_product_badge( $product ) {
        if ( ! empty( $product->is_hot ) ) {
            return array( 'type' => 'hot', 'text' => __( 'HOT', 'qiling' ) );
        }

        $sales = isset( $product->sales_count ) ? (int) $product->sales_count : 0;
        if ( $sales >= 30 ) {
            return array( 'type' => 'hot', 'text' => __( 'HOT', 'qiling' ) );
        }

        $created = isset( $product->created_at ) ? strtotime( (string) $product->created_at ) : 0;
        if ( $created > 0 && ( time() - $created ) < 30 * DAY_IN_SECONDS ) {
            return array( 'type' => 'new', 'text' => __( 'NEW', 'qiling' ) );
        }

        $min_p = isset( $product->min_price ) ? (float) $product->min_price : 0;
        $max_p = isset( $product->max_price ) ? (float) $product->max_price : 0;
        if ( $max_p > $min_p && $min_p > 0 ) {
            return array( 'type' => 'sale', 'text' => __( 'SALE', 'qiling' ) );
        }

        return null;
    }

    private function cache_fetch( $cache_key ) {
        if ( function_exists( 'developer_starter_cache_fetch' ) ) {
            return \developer_starter_cache_fetch( $cache_key, self::MODULE_CACHE_GROUP );
        }
        return get_transient( $cache_key );
    }

    private function cache_store( $cache_key, $value, $ttl ) {
        if ( function_exists( 'developer_starter_cache_store' ) ) {
            \developer_starter_cache_store( $cache_key, $value, $ttl, self::MODULE_CACHE_GROUP );
            return;
        }
        set_transient( $cache_key, $value, $ttl );
    }

    private function build_module_markup( $settings, $categories, $product_data, $product_base, $module_id ) {
        $bg_color      = (string) $settings['bg_color'];
        $content_width = (string) $settings['content_width'];
        $padding       = (int) $settings['padding'];
        $title         = (string) $settings['title'];
        $subtitle      = (string) $settings['subtitle'];
        $columns       = (string) $settings['columns'];
        $show_tabs     = (string) $settings['show_tabs'];
        $show_cart     = (string) $settings['show_cart'];
        $show_badge    = (string) $settings['show_badge'];
        $hover_image   = (string) $settings['hover_image'];
        $show_more_btn = (string) $settings['show_more_btn'];
        $more_btn_text = (string) $settings['more_btn_text'];
        $more_btn_url  = ! empty( $settings['more_btn_url'] ) ? (string) $settings['more_btn_url'] : home_url( '/' . trim( $product_base, '/' ) );

        $currency_code = function_exists( 'developer_starter_get_demo_currency_code' ) ? developer_starter_get_demo_currency_code() : 'CNY';
        $has_valid_tabs = ( 'yes' === $show_tabs && ! empty( $categories ) );

        ob_start();
        ?>
        <section class="module module-qiling-shop-grid" id="<?php echo esc_attr( $module_id ); ?>" style="padding: <?php echo esc_attr( $padding ); ?>px 0; background: <?php echo esc_attr( $bg_color ); ?>;">
            <style>
                #<?php echo esc_attr( $module_id ); ?> { position: relative; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-header { text-align: center; max-width: 820px; margin: 0 auto 36px; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-title { margin: 0 0 10px; font-size: clamp(24px, 3.5vw, 36px); font-weight: 800; letter-spacing: -0.02em; color: var(--color-text-main, #111827); line-height: 1.25; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-subtitle { font-size: 15px; color: var(--color-text-muted, #6b7280); line-height: 1.6; }
                
                /* 分类 Tabs 药丸设计 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-tabs-wrapper { display: flex; justify-content: center; margin-bottom: 32px; overflow-x: auto; padding: 4px; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-tabs-nav { display: inline-flex; align-items: center; gap: 8px; padding: 6px; background: var(--color-neutral-100, #f3f4f6); border-radius: 999px; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-tab-btn { border: 0; background: transparent; padding: 8px 18px; border-radius: 999px; font-size: 14px; font-weight: 600; color: var(--color-text-muted, #4b5563); cursor: pointer; transition: all 0.25s ease; white-space: nowrap; line-height: 1.4; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-tab-btn:hover { color: var(--color-text-main, #111827); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-tab-btn.is-active { background: var(--color-surface, #ffffff); color: var(--color-primary, #0ea5e9); box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
                
                /* 网格布局 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-grid { display: grid; gap: 24px; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-grid-col-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-grid-col-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
                
                /* 单个商品卡片 - 大牌极简质感 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-card { display: flex; flex-direction: column; background: var(--color-surface, #ffffff); border-radius: 16px; border: 1px solid var(--color-border, #e5e7eb); overflow: hidden; text-decoration: none; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s; position: relative; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.04); border-color: var(--color-border-hover, #d1d5db); }
                
                /* 图片与悬浮副图 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-img-box { position: relative; width: 100%; padding-bottom: 100%; background: var(--color-neutral-50, #f9fafb); overflow: hidden; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-img-primary { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: opacity 0.4s ease, transform 0.5s ease; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-img-secondary { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.4s ease, transform 0.5s ease; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-card:hover .qsg-img-primary.has-secondary { opacity: 0; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-card:hover .qsg-img-secondary { opacity: 1; transform: scale(1.05); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-card:hover .qsg-img-primary:not(.has-secondary) { transform: scale(1.05); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-img-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 40px; }
                
                /* 角标 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-badge { position: absolute; top: 12px; left: 12px; z-index: 3; display: inline-flex; align-items: center; justify-content: center; height: 22px; padding: 0 9px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1; pointer-events: none; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-badge-hot { background: var(--color-error, #ef4444); color: var(--color-text-inverse, #ffffff); box-shadow: 0 4px 10px rgba(var(--color-error-rgb, 220, 38, 38), 0.35); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-badge-new { background: var(--color-info, #0284c7); color: var(--color-text-inverse, #ffffff); box-shadow: 0 4px 10px rgba(var(--color-info-rgb, 14, 165, 233), 0.35); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-badge-sale { background: var(--color-warning, #d97706); color: var(--color-text-inverse, #ffffff); box-shadow: 0 4px 10px rgba(var(--color-warning-rgb, 245, 158, 11), 0.35); }
                
                /* 信息区 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-info { padding: 18px; display: flex; flex-direction: column; flex-grow: 1; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-name { margin: 0 0 6px; font-size: 16px; font-weight: 700; color: var(--color-text-main, #111827); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 44px; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-desc { font-size: 13px; color: var(--color-text-muted, #6b7280); line-height: 1.5; margin: 0 0 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-bottom { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px solid var(--color-border-subtle, #f3f4f6); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-price { font-size: 18px; font-weight: 800; color: var(--color-primary, #0ea5e9); letter-spacing: -0.02em; font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-cart-pill { display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; border-radius: 999px; background: var(--color-neutral-100, #f3f4f6); color: var(--color-text-main, #374151); font-size: 12px; font-weight: 600; transition: all 0.2s; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-card:hover .qsg-cart-pill { background: var(--color-primary, #0ea5e9); color: var(--color-text-inverse, #ffffff); transform: scale(1.04); }
                
                /* 分类面板切换容器 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-pane { display: none; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-pane.is-active { display: block; animation: qsgFadeIn 0.35s ease-out; }
                @keyframes qsgFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

                /* 底部探索全部按钮 */
                #<?php echo esc_attr( $module_id ); ?> .qsg-footer { text-align: center; margin-top: 44px; }
                #<?php echo esc_attr( $module_id ); ?> .qsg-more-btn { display: inline-flex; align-items: center; justify-content: center; height: 48px; padding: 0 32px; border-radius: 999px; background: var(--color-text-main, #111827); color: #ffffff; font-size: 15px; font-weight: 700; text-decoration: none; transition: all 0.25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
                #<?php echo esc_attr( $module_id ); ?> .qsg-more-btn:hover { background: var(--color-primary, #0ea5e9); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3); color: #ffffff; }

                /* 响应式断点 */
                @media (max-width: 1024px) {
                    #<?php echo esc_attr( $module_id ); ?> .qsg-grid-col-4 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                }
                @media (max-width: 768px) {
                    #<?php echo esc_attr( $module_id ); ?> .qsg-grid-col-3,
                    #<?php echo esc_attr( $module_id ); ?> .qsg-grid-col-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsg-info { padding: 12px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsg-name { font-size: 14px; min-height: 38px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsg-price { font-size: 16px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsg-cart-pill span { display: none; }
                    #<?php echo esc_attr( $module_id ); ?> .qsg-cart-pill { width: 30px; height: 30px; padding: 0; justify-content: center; }
                }
                @media (max-width: 480px) {
                    #<?php echo esc_attr( $module_id ); ?> .qsg-grid-col-3,
                    #<?php echo esc_attr( $module_id ); ?> .qsg-grid-col-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
                }
            </style>

            <div class="<?php echo 'full' === $content_width ? 'container-fluid' : 'container'; ?>">

                <?php if ( $title || $subtitle ) : ?>
                    <div class="qsg-header">
                        <?php if ( $title ) : ?>
                            <h2 class="qsg-title"><?php echo wp_kses_post( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <div class="qsg-subtitle"><?php echo wp_kses_post( $subtitle ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ( $has_valid_tabs ) : ?>
                    <div class="qsg-tabs-wrapper">
                        <nav class="qsg-tabs-nav" role="tablist">
                            <button type="button" class="qsg-tab-btn is-active" data-target="all" role="tab">
                                <?php esc_html_e( '全部精选', 'qiling' ); ?>
                            </button>
                            <?php foreach ( $categories as $cat ) : ?>
                                <?php if ( ! empty( $product_data[ 'cat_' . $cat->id ] ) ) : ?>
                                    <button type="button" class="qsg-tab-btn" data-target="<?php echo esc_attr( 'cat_' . $cat->id ); ?>" role="tab">
                                        <?php echo esc_html( $cat->name ); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <div class="qsg-panes-wrapper">
                    <!-- 全部门类面板 -->
                    <div class="qsg-pane is-active" data-pane="all">
                        <div class="qsg-grid qsg-grid-col-<?php echo esc_attr( $columns ); ?>">
                            <?php foreach ( $product_data['all'] as $product ) : ?>
                                <?php $this->render_single_product_card( $product, $product_base, $currency_code, $show_badge, $hover_image, $show_cart ); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 各子分类面板 -->
                    <?php if ( $has_valid_tabs ) : ?>
                        <?php foreach ( $categories as $cat ) : ?>
                            <?php $pane_key = 'cat_' . $cat->id; ?>
                            <?php if ( ! empty( $product_data[ $pane_key ] ) ) : ?>
                                <div class="qsg-pane" data-pane="<?php echo esc_attr( $pane_key ); ?>">
                                    <div class="qsg-grid qsg-grid-col-<?php echo esc_attr( $columns ); ?>">
                                        <?php foreach ( $product_data[ $pane_key ] as $product ) : ?>
                                            <?php $this->render_single_product_card( $product, $product_base, $currency_code, $show_badge, $hover_image, $show_cart ); ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ( 'yes' === $show_more_btn && ! empty( $more_btn_text ) ) : ?>
                    <div class="qsg-footer">
                        <a href="<?php echo esc_url( $more_btn_url ); ?>" class="qsg-more-btn" target="_blank">
                            <?php echo esc_html( $more_btn_text ); ?>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </section>

        <?php if ( $has_valid_tabs ) : ?>
            <script>
                (function() {
                    var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                    if (!root) return;
                    var tabs = root.querySelectorAll('.qsg-tab-btn');
                    var panes = root.querySelectorAll('.qsg-pane');

                    tabs.forEach(function(tab) {
                        tab.addEventListener('click', function() {
                            var target = this.dataset.target;
                            tabs.forEach(function(t) { t.classList.remove('is-active'); });
                            this.classList.add('is-active');

                            panes.forEach(function(pane) {
                                if (pane.dataset.pane === target) {
                                    pane.classList.add('is-active');
                                } else {
                                    pane.classList.remove('is-active');
                                }
                            });
                        });
                    });
                })();
            </script>
        <?php endif; ?>
        <?php

        return (string) ob_get_clean();
    }

    private function render_single_product_card( $product, $product_base, $currency_code, $show_badge, $hover_image, $show_cart ) {
        $product_url = home_url( '/' . trim( $product_base, '/' ) . '/' . $product->slug );
        $price_html  = function_exists( 'developer_starter_format_currency_amount' )
            ? developer_starter_format_currency_amount( $product->min_price, $currency_code, 2 )
            : 'CNY ' . number_format_i18n( $product->min_price, 2 );

        $images = $this->resolve_product_images(
            isset( $product->main_image ) ? (string) $product->main_image : '',
            isset( $product->gallery ) ? (string) $product->gallery : ''
        );
        $badge = ( 'yes' === $show_badge ) ? $this->get_product_badge( $product ) : null;
        $has_secondary = ( 'yes' === $hover_image && ! empty( $images['secondary'] ) );
        ?>
        <a href="<?php echo esc_url( $product_url ); ?>" class="qsg-card" target="_blank">
            <div class="qsg-img-box">
                <?php if ( ! empty( $badge ) ) : ?>
                    <span class="qsg-badge qsg-badge-<?php echo esc_attr( $badge['type'] ); ?>"><?php echo esc_html( $badge['text'] ); ?></span>
                <?php endif; ?>

                <?php if ( '' !== $images['primary'] ) : ?>
                    <img src="<?php echo esc_url( $images['primary'] ); ?>" alt="<?php echo esc_attr( $product->title ); ?>" class="qsg-img-primary<?php echo $has_secondary ? ' has-secondary' : ''; ?>" loading="lazy" decoding="async">
                    <?php if ( $has_secondary ) : ?>
                        <img src="<?php echo esc_url( $images['secondary'] ); ?>" alt="<?php echo esc_attr( $product->title ); ?>" class="qsg-img-secondary" loading="lazy" decoding="async">
                    <?php endif; ?>
                <?php else : ?>
                    <div class="qsg-img-placeholder">
                        <span class="dashicons dashicons-format-image"></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="qsg-info">
                <h3 class="qsg-name" title="<?php echo esc_attr( $product->title ); ?>">
                    <?php echo esc_html( $product->title ); ?>
                </h3>

                <?php if ( ! empty( $product->subtitle ) ) : ?>
                    <div class="qsg-desc"><?php echo esc_html( $product->subtitle ); ?></div>
                <?php endif; ?>

                <div class="qsg-bottom">
                    <span class="qsg-price"><?php echo esc_html( $price_html ); ?></span>
                    <?php if ( 'yes' === $show_cart ) : ?>
                        <span class="qsg-cart-pill" aria-label="<?php esc_attr_e( '立即加购', 'qiling' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <span><?php esc_html_e( '选购', 'qiling' ); ?></span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php
    }

    /**
     * 智能兜底：当启灵商城数据表尚未建立或商品为空时，提供大品牌/工贸出海级的高质感演示数据
     *
     * @param int $limit 数量
     * @return array{categories: array<int, object>, product_data: array<string, array<int, object>>}
     */
    private function get_demo_data( $limit = 8 ) {
        $is_en = function_exists( 'developer_starter_get_current_frontend_lang' ) && 'en_US' === developer_starter_get_current_frontend_lang();

        $cat1_name = $is_en ? 'Smart Wearables' : __( '智能穿戴', 'qiling' );
        $cat2_name = $is_en ? 'Acoustic Audio' : __( '声学视听', 'qiling' );
        $cat3_name = $is_en ? 'Precision Hardware' : __( '工贸精工', 'qiling' );

        $categories = array(
            (object) array( 'id' => 1, 'name' => $cat1_name, 'slug' => 'wearables' ),
            (object) array( 'id' => 2, 'name' => $cat2_name, 'slug' => 'audio' ),
            (object) array( 'id' => 3, 'name' => $cat3_name, 'slug' => 'hardware' ),
        );

        $demo_all = array(
            (object) array(
                'id'          => 101,
                'title'       => $is_en ? 'Titanium Watch Ultra 2' : 'TITANIUM Watch Ultra 2 · 钛金旗舰款',
                'subtitle'    => $is_en ? 'Aerospace titanium casing · 4nm chipset · 5000 nits' : '航空级钛金属锻造 · 4nm 自研芯片 · 5000尼特超感屏',
                'slug'        => 'titanium-watch-ultra-2',
                'main_image'  => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 429.00 : 2999.00,
                'max_price'   => $is_en ? 499.00 : 3499.00,
                'sales_count' => 1280,
                'is_hot'      => 1,
                'category_id' => 1,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 3 * DAY_IN_SECONDS ),
            ),
            (object) array(
                'id'          => 102,
                'title'       => $is_en ? 'AERO Master Pro Wireless Headphones' : 'AERO Master Pro · 头戴式空间音频耳机',
                'subtitle'    => $is_en ? 'Hi-Res certified · -48dB active noise cancellation' : 'Hi-Res 臻彩原声 · -48dB 动态智能深海降噪',
                'slug'        => 'aero-master-pro-headphones',
                'main_image'  => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1484704849700-f032a568e944?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 289.00 : 1899.00,
                'max_price'   => $is_en ? 329.00 : 2199.00,
                'sales_count' => 960,
                'is_hot'      => 1,
                'category_id' => 2,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 10 * DAY_IN_SECONDS ),
            ),
            (object) array(
                'id'          => 103,
                'title'       => $is_en ? 'Industrial Smart IoT Gateway Pro' : '工业级边缘计算智能网关 Pro',
                'subtitle'    => $is_en ? 'Dual Gigabit LAN · 5G cellular · -40°C to +85°C' : '双千兆网口 · 5G全网通冗余 · -40℃~85℃宽温运行',
                'slug'        => 'industrial-smart-iot-gateway',
                'main_image'  => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 189.00 : 1280.00,
                'max_price'   => $is_en ? 249.00 : 1680.00,
                'sales_count' => 450,
                'is_hot'      => 0,
                'category_id' => 3,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 5 * DAY_IN_SECONDS ),
            ),
            (object) array(
                'id'          => 104,
                'title'       => $is_en ? 'Studio Sound Earbuds ANC Gen3' : '真无线降噪耳机 ANC 第三代',
                'subtitle'    => $is_en ? 'Custom acoustic chamber · 36h battery · IP54' : '超轻半入耳设计 · 36小时超长复合续航 · 蓝牙5.4',
                'slug'        => 'studio-sound-earbuds-gen3',
                'main_image'  => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 119.00 : 699.00,
                'max_price'   => $is_en ? 149.00 : 899.00,
                'sales_count' => 2100,
                'is_hot'      => 1,
                'category_id' => 2,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 2 * DAY_IN_SECONDS ),
            ),
            (object) array(
                'id'          => 105,
                'title'       => $is_en ? 'Precision Laser Distance Sensor' : '高精度激光测距传感器模组',
                'subtitle'    => $is_en ? '0.1mm resolution · RS485 / Modbus protocol' : '±1.0mm 工业级测距精度 · RS485/Modbus 通信协议',
                'slug'        => 'precision-laser-distance-sensor',
                'main_image'  => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 89.00 : 580.00,
                'max_price'   => $is_en ? 119.00 : 780.00,
                'sales_count' => 380,
                'is_hot'      => 0,
                'category_id' => 3,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 15 * DAY_IN_SECONDS ),
            ),
            (object) array(
                'id'          => 106,
                'title'       => $is_en ? 'Titanium Sport Band Pro' : '钛金快拆编织高弹运动表带',
                'subtitle'    => $is_en ? 'Ultra-lightweight · Sweatproof · Quick release' : '航空钛金双扣联动 · 纳米级防汗涂层 · 3秒秒级快拆',
                'slug'        => 'titanium-sport-band-pro',
                'main_image'  => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 49.00 : 299.00,
                'max_price'   => $is_en ? 69.00 : 399.00,
                'sales_count' => 740,
                'is_hot'      => 0,
                'category_id' => 1,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 20 * DAY_IN_SECONDS ),
            ),
            (object) array(
                'id'          => 107,
                'title'       => $is_en ? 'Commercial Energy Storage BMS Controller' : '工商业高压储能 BMS 主控单元',
                'subtitle'    => $is_en ? '1500V high voltage · Active balancing · UL certified' : '1500V 高压架构 · 主动毫秒级均衡 · UL/IEC 双重安全认证',
                'slug'        => 'energy-storage-bms-controller',
                'main_image'  => 'https://images.unsplash.com/photo-1508873696983-2df5293cb325?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 890.00 : 5800.00,
                'max_price'   => $is_en ? 1150.00 : 7500.00,
                'sales_count' => 120,
                'is_hot'      => 0,
                'category_id' => 3,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 25 * DAY_IN_SECONDS ),
            ),
            (object) array(
                'id'          => 108,
                'title'       => $is_en ? 'Magnetic Wireless Rapid Charger 100W' : '桌面级超导磁吸三合一快充坞 100W',
                'subtitle'    => $is_en ? 'GaN 5 technology · Triple simultaneous charging' : '第五代氮化镓核心 · 手表/耳机/手机三合一极速闪充',
                'slug'        => 'magnetic-wireless-rapid-charger',
                'main_image'  => 'https://images.unsplash.com/photo-1586816879360-004f5b0c51e5?auto=format&fit=crop&w=800&q=80',
                'gallery'     => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                'min_price'   => $is_en ? 79.00 : 499.00,
                'max_price'   => $is_en ? 99.00 : 599.00,
                'sales_count' => 1580,
                'is_hot'      => 1,
                'category_id' => 1,
                'created_at'  => gmdate( 'Y-m-d H:i:s', time() - 4 * DAY_IN_SECONDS ),
            ),
        );

        $demo_all = array_slice( $demo_all, 0, $limit );

        $grouped = array( 'all' => $demo_all );
        foreach ( $categories as $cat ) {
            $cat_id = $cat->id;
            $cat_items = array_filter( $demo_all, function( $p ) use ( $cat_id ) {
                return (int) $p->category_id === (int) $cat_id;
            } );
            $grouped[ 'cat_' . $cat_id ] = array_values( $cat_items );
        }

        return array(
            'categories'   => $categories,
            'product_data' => $grouped,
        );
    }
}
