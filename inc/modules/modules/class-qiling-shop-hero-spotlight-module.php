<?php
/**
 * 启灵商城旗舰焦点展台模块
 *
 * 媲美 Apple / 大疆官网首页的单品巨幕展台，用于打爆核心战略单品。
 * 沉浸式产品渲染图、黑科技参数芯片、核心卖点标语与双决策按钮。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Qiling_Shop_Hero_Spotlight_Module extends Module_Base {

    private const MODULE_CACHE_GROUP    = 'developer_starter_module';
    private const PRODUCTS_CACHE_TTL    = 10 * MINUTE_IN_SECONDS;
    private const MODULE_ID_PLACEHOLDER = '__DS_QSH_MODULE_ID__';

    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-star-filled';
        $this->description = __( '大品牌机皇主打款单品聚焦展台，巨幅渲染图、黑科技参数芯片与双购买行动号召', 'qiling' );
    }

    public function get_id() {
        return 'qiling_shop_hero_spotlight';
    }

    public function get_name() {
        return __( '启灵旗舰产品展台', 'qiling' );
    }

    public function get_fields() {
        return array(
            // === 视觉风格与布局 ===
            array(
                'id'      => 'qsh_bg_theme',
                'type'    => 'select',
                'label'   => __( '展台视觉主题', 'qiling' ),
                'options' => array(
                    'dark'     => __( '暗黑科技巨幕 (Dark Pro · 推荐)', 'qiling' ),
                    'light'    => __( '极简典雅纯白 (Clean Light)', 'qiling' ),
                    'gradient' => __( '流光渐变深邃 (Ambient Glow)', 'qiling' ),
                ),
                'default' => 'dark',
            ),
            array(
                'id'      => 'qsh_layout',
                'type'    => 'select',
                'label'   => __( '展台排版布局', 'qiling' ),
                'options' => array(
                    'split'   => __( '大牌经典：左图右文', 'qiling' ),
                    'reverse' => __( '反向布局：左文右图', 'qiling' ),
                    'center'  => __( '居中聚焦：巨幕沉浸式', 'qiling' ),
                ),
                'default' => 'split',
            ),
            array(
                'id'      => 'qsh_padding',
                'type'    => 'select',
                'label'   => __( '上下留白尺寸', 'qiling' ),
                'options' => array(
                    '80'  => '80px',
                    '100' => '100px',
                    '120' => __( '120px (大牌气场推荐)', 'qiling' ),
                    '140' => '140px',
                ),
                'default' => '120',
            ),

            // === 关联商品与基础内容 ===
            array(
                'id'      => 'qsh_product_id',
                'type'    => 'text',
                'label'   => __( '关联商城商品 ID 或 Slug', 'qiling' ),
                'default' => '',
                'desc'    => __( '输入商城商品 ID（例如 1）或别名，将自动同步读取真实售价、主图与购买链接', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_badge_text',
                'type'    => 'text',
                'label'   => __( '顶部亮点角标', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'FLAGSHIP · 全新旗舰', 'NEW FLAGSHIP' ) : __( 'FLAGSHIP · 全新旗舰', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_title',
                'type'    => 'text',
                'label'   => __( '旗舰产品主标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'TITANIUM X PRO · 旗舰定义者', 'TITANIUM X PRO · PURE INNOVATION' ) : __( 'TITANIUM X PRO · 旗舰定义者', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_subtitle',
                'type'    => 'textarea',
                'label'   => __( '产品核心设计标语/副标题', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '极致工艺，为专业而生。全自研低功耗处理核心与航天级精密机身，重新突破便携性能想象边界。', 'Engineered for perfection. Aerospace-grade enclosure paired with custom low-power silicon, redefining portable excellence.' ) : __( '极致工艺，为专业而生。全自研低功耗处理核心与航天级精密机身，重新突破便携性能想象边界。', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_custom_image',
                'type'    => 'image',
                'label'   => __( '自定义产品高清渲染图', 'qiling' ),
                'desc'    => __( '建议上传透明底 PNG 渲染大图，留空则自动读取关联商品主图', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_price_override',
                'type'    => 'text',
                'label'   => __( '自定义价格文本', 'qiling' ),
                'default' => '',
                'desc'    => __( '留空自动显示关联商品真实售价，如填 “起售价 $999”', 'qiling' ),
            ),

            // === 核心黑科技参数芯片 ===
            array(
                'id'      => 'qsh_spec_1_val',
                'type'    => 'text',
                'label'   => __( '参数 1 数值/亮点', 'qiling' ),
                'default' => '4nm',
            ),
            array(
                'id'      => 'qsh_spec_1_lbl',
                'type'    => 'text',
                'label'   => __( '参数 1 说明', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '先进制程自研核心', 'Advanced Custom Silicon' ) : __( '先进制程自研核心', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_spec_2_val',
                'type'    => 'text',
                'label'   => __( '参数 2 数值/亮点', 'qiling' ),
                'default' => 'IP68',
            ),
            array(
                'id'      => 'qsh_spec_2_lbl',
                'type'    => 'text',
                'label'   => __( '参数 2 说明', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '深度抗水防尘', 'Water & Dust Resistance' ) : __( '深度抗水防尘', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_spec_3_val',
                'type'    => 'text',
                'label'   => __( '参数 3 数值/亮点', 'qiling' ),
                'default' => '48h+',
            ),
            array(
                'id'      => 'qsh_spec_3_lbl',
                'type'    => 'text',
                'label'   => __( '参数 3 说明', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '长效全天候续航', 'All-Day Battery Life' ) : __( '长效全天候续航', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_spec_4_val',
                'type'    => 'text',
                'label'   => __( '参数 4 数值/亮点', 'qiling' ),
                'default' => '5000 nits',
            ),
            array(
                'id'      => 'qsh_spec_4_lbl',
                'type'    => 'text',
                'label'   => __( '参数 4 说明', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '户外超感峰值视界', 'Peak Outdoor Display' ) : __( '户外超感峰值视界', 'qiling' ),
            ),

            // === 行动号召双按钮 ===
            array(
                'id'      => 'qsh_btn_primary_text',
                'type'    => 'text',
                'label'   => __( '主按钮文字', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即选购', 'Buy Now' ) : __( '立即选购', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_btn_primary_url',
                'type'    => 'text',
                'label'   => __( '主按钮跳转链接', 'qiling' ),
                'default' => '',
                'desc'    => __( '留空自动跳转关联商品详情页', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_btn_secondary_text',
                'type'    => 'text',
                'label'   => __( '次按钮文字', 'qiling' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '了解设计亮点 →', 'Explore Innovations →' ) : __( '了解设计亮点 →', 'qiling' ),
            ),
            array(
                'id'      => 'qsh_btn_secondary_url',
                'type'    => 'text',
                'label'   => __( '次按钮跳转链接', 'qiling' ),
                'default' => '#features',
            ),
        );
    }

    public function render( $data = array() ) {
        global $wpdb;

        $settings     = $this->normalize_settings( $data );
        $module_id    = 'qsh-' . uniqid();
        $fragment_key = $this->build_fragment_cache_key( $settings );

        if ( function_exists( 'developer_starter_get_fragment_cache' ) ) {
            $cached_html = \developer_starter_get_fragment_cache( $fragment_key );
            if ( is_string( $cached_html ) && '' !== $cached_html ) {
                echo str_replace( self::MODULE_ID_PLACEHOLDER, $module_id, $cached_html );
                return;
            }
        }

        $linked_product = null;
        if ( ! empty( $settings['product_id'] ) ) {
            $linked_product = $this->get_linked_product( $wpdb, $settings['product_id'] );
        }

        $html = $this->build_module_markup( $settings, $linked_product, self::MODULE_ID_PLACEHOLDER );

        if ( function_exists( 'developer_starter_set_fragment_cache' ) && '' !== $html ) {
            \developer_starter_set_fragment_cache( $fragment_key, $html );
        }

        echo str_replace( self::MODULE_ID_PLACEHOLDER, $module_id, $html );
    }

    private function normalize_settings( $data ) {
        $bg_theme = isset( $data['qsh_bg_theme'] ) ? sanitize_key( (string) $data['qsh_bg_theme'] ) : 'dark';
        if ( ! in_array( $bg_theme, array( 'dark', 'light', 'gradient' ), true ) ) {
            $bg_theme = 'dark';
        }

        $layout = isset( $data['qsh_layout'] ) ? sanitize_key( (string) $data['qsh_layout'] ) : 'split';
        if ( ! in_array( $layout, array( 'split', 'reverse', 'center' ), true ) ) {
            $layout = 'split';
        }

        return array(
            'bg_theme'           => $bg_theme,
            'layout'             => $layout,
            'padding'            => isset( $data['qsh_padding'] ) ? max( 40, intval( $data['qsh_padding'] ) ) : 120,
            'product_id'         => isset( $data['qsh_product_id'] ) ? sanitize_text_field( (string) $data['qsh_product_id'] ) : '',
            'badge_text'         => isset( $data['qsh_badge_text'] ) ? (string) $data['qsh_badge_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'FLAGSHIP · 全新旗舰', 'NEW FLAGSHIP' ) : __( 'FLAGSHIP · 全新旗舰', 'qiling' ) ),
            'title'              => isset( $data['qsh_title'] ) ? (string) $data['qsh_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'TITANIUM X PRO · 旗舰定义者', 'TITANIUM X PRO · PURE INNOVATION' ) : __( 'TITANIUM X PRO · 旗舰定义者', 'qiling' ) ),
            'subtitle'           => isset( $data['qsh_subtitle'] ) ? (string) $data['qsh_subtitle'] : '',
            'custom_image'       => isset( $data['qsh_custom_image'] ) ? (string) $data['qsh_custom_image'] : '',
            'price_override'     => isset( $data['qsh_price_override'] ) ? (string) $data['qsh_price_override'] : '',
            'spec_1_val'         => isset( $data['qsh_spec_1_val'] ) ? (string) $data['qsh_spec_1_val'] : '4nm',
            'spec_1_lbl'         => isset( $data['qsh_spec_1_lbl'] ) ? (string) $data['qsh_spec_1_lbl'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '先进制程自研核心', 'Advanced Custom Silicon' ) : __( '先进制程自研核心', 'qiling' ) ),
            'spec_2_val'         => isset( $data['qsh_spec_2_val'] ) ? (string) $data['qsh_spec_2_val'] : 'IP68',
            'spec_2_lbl'         => isset( $data['qsh_spec_2_lbl'] ) ? (string) $data['qsh_spec_2_lbl'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '深度抗水防尘', 'Water & Dust Resistance' ) : __( '深度抗水防尘', 'qiling' ) ),
            'spec_3_val'         => isset( $data['qsh_spec_3_val'] ) ? (string) $data['qsh_spec_3_val'] : '48h+',
            'spec_3_lbl'         => isset( $data['qsh_spec_3_lbl'] ) ? (string) $data['qsh_spec_3_lbl'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '长效全天候续航', 'All-Day Battery Life' ) : __( '长效全天候续航', 'qiling' ) ),
            'spec_4_val'         => isset( $data['qsh_spec_4_val'] ) ? (string) $data['qsh_spec_4_val'] : '5000 nits',
            'spec_4_lbl'         => isset( $data['qsh_spec_4_lbl'] ) ? (string) $data['qsh_spec_4_lbl'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '户外超感峰值视界', 'Peak Outdoor Display' ) : __( '户外超感峰值视界', 'qiling' ) ),
            'btn_primary_text'   => isset( $data['qsh_btn_primary_text'] ) ? (string) $data['qsh_btn_primary_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即选购', 'Buy Now' ) : __( '立即选购', 'qiling' ) ),
            'btn_primary_url'    => isset( $data['qsh_btn_primary_url'] ) ? (string) $data['qsh_btn_primary_url'] : '',
            'btn_secondary_text' => isset( $data['qsh_btn_secondary_text'] ) ? (string) $data['qsh_btn_secondary_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '了解设计亮点 →', 'Explore Innovations →' ) : __( '了解设计亮点 →', 'qiling' ) ),
            'btn_secondary_url'  => isset( $data['qsh_btn_secondary_url'] ) ? (string) $data['qsh_btn_secondary_url'] : '',
        );
    }

    private function build_fragment_cache_key( $settings ) {
        $lang = function_exists( 'developer_starter_get_current_frontend_lang' )
            ? (string) developer_starter_get_current_frontend_lang()
            : (string) get_locale();

        $seed = array(
            'module'   => $this->get_id(),
            'lang'     => $lang,
            'settings' => $settings,
        );

        return 'qsh_frag_' . md5( wp_json_encode( $seed ) );
    }

    private function get_linked_product( $wpdb, $identifier ) {
        $shop_prefix = defined( 'QLS_SHOP_TABLE_PREFIX' ) ? QLS_SHOP_TABLE_PREFIX : 'qls_shop_';
        $table       = $wpdb->prefix . $shop_prefix . 'products';

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            return null;
        }

        if ( is_numeric( $identifier ) ) {
            $sql = $wpdb->prepare( "SELECT id, title, subtitle, slug, main_image, min_price FROM {$table} WHERE id = %d AND status = 1 LIMIT 1", absint( $identifier ) );
        } else {
            $sql = $wpdb->prepare( "SELECT id, title, subtitle, slug, main_image, min_price FROM {$table} WHERE slug = %s AND status = 1 LIMIT 1", sanitize_title( $identifier ) );
        }

        return $wpdb->get_row( $sql );
    }

    private function build_module_markup( $settings, $linked_product, $module_id ) {
        $bg_theme = (string) $settings['bg_theme'];
        $layout   = (string) $settings['layout'];
        $padding  = (int) $settings['padding'];

        // 智能填充商品信息
        $product_base = (string) get_option( 'qls_shop_product_base', 'shop/product' );
        $title        = ! empty( $settings['title'] ) ? $settings['title'] : ( $linked_product ? $linked_product->title : '' );
        $subtitle     = ! empty( $settings['subtitle'] ) ? $settings['subtitle'] : ( $linked_product && ! empty( $linked_product->subtitle ) ? $linked_product->subtitle : '' );
        
        $currency_code = function_exists( 'developer_starter_get_demo_currency_code' ) ? developer_starter_get_demo_currency_code() : 'CNY';
        $price_html    = '';
        if ( ! empty( $settings['price_override'] ) ) {
            $price_html = esc_html( $settings['price_override'] );
        } elseif ( $linked_product ) {
            $price_html = function_exists( 'developer_starter_format_currency_amount' )
                ? developer_starter_format_currency_amount( $linked_product->min_price, $currency_code, 2 )
                : 'CNY ' . number_format_i18n( $linked_product->min_price, 2 );
        }

        $image_url = ! empty( $settings['custom_image'] ) ? $settings['custom_image'] : '';
        if ( empty( $image_url ) && $linked_product && ! empty( $linked_product->main_image ) ) {
            $img_data = json_decode( $linked_product->main_image, true );
            if ( is_array( $img_data ) && isset( $img_data[0] ) ) {
                $image_url = is_string( $img_data[0] ) ? $img_data[0] : ( isset( $img_data[0]['url'] ) ? $img_data[0]['url'] : '' );
            } elseif ( is_string( $linked_product->main_image ) ) {
                $image_url = $linked_product->main_image;
            }
        }

        $primary_url = ! empty( $settings['btn_primary_url'] ) ? $settings['btn_primary_url'] : ( $linked_product ? home_url( '/' . trim( $product_base, '/' ) . '/' . $linked_product->slug ) : '#' );
        $secondary_url = ! empty( $settings['btn_secondary_url'] ) ? $settings['btn_secondary_url'] : '#';

        ob_start();
        ?>
        <section class="module module-qiling-shop-hero-spotlight theme-<?php echo esc_attr( $bg_theme ); ?> layout-<?php echo esc_attr( $layout ); ?>" id="<?php echo esc_attr( $module_id ); ?>" style="padding: <?php echo esc_attr( $padding ); ?>px 0;">
            <style>
                #<?php echo esc_attr( $module_id ); ?> { position: relative; overflow: hidden; }
                
                /* 主题风格配色 */
                #<?php echo esc_attr( $module_id ); ?>.theme-dark { background: #0a0a0c; color: #ffffff; }
                #<?php echo esc_attr( $module_id ); ?>.theme-dark .qsh-title { color: #ffffff; }
                #<?php echo esc_attr( $module_id ); ?>.theme-dark .qsh-subtitle { color: #9ca3af; }
                #<?php echo esc_attr( $module_id ); ?>.theme-dark .qsh-spec-val { color: #ffffff; }
                #<?php echo esc_attr( $module_id ); ?>.theme-dark .qsh-spec-lbl { color: #6b7280; }
                #<?php echo esc_attr( $module_id ); ?>.theme-dark .qsh-glow { background: radial-gradient(circle, rgba(14, 165, 233, 0.22) 0%, rgba(10, 10, 12, 0) 70%); }
                #<?php echo esc_attr( $module_id ); ?>.theme-dark .qsh-spec-card { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); }
                #<?php echo esc_attr( $module_id ); ?>.theme-dark .qsh-badge { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }

                #<?php echo esc_attr( $module_id ); ?>.theme-light { background: #ffffff; color: #111827; }
                #<?php echo esc_attr( $module_id ); ?>.theme-light .qsh-title { color: #111827; }
                #<?php echo esc_attr( $module_id ); ?>.theme-light .qsh-subtitle { color: #4b5563; }
                #<?php echo esc_attr( $module_id ); ?>.theme-light .qsh-spec-val { color: #111827; }
                #<?php echo esc_attr( $module_id ); ?>.theme-light .qsh-spec-lbl { color: #6b7280; }
                #<?php echo esc_attr( $module_id ); ?>.theme-light .qsh-glow { background: radial-gradient(circle, rgba(243, 244, 246, 0.8) 0%, rgba(255, 255, 255, 0) 70%); }
                #<?php echo esc_attr( $module_id ); ?>.theme-light .qsh-spec-card { background: #f9fafb; border: 1px solid #e5e7eb; }
                #<?php echo esc_attr( $module_id ); ?>.theme-light .qsh-badge { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }

                #<?php echo esc_attr( $module_id ); ?>.theme-gradient { background: linear-gradient(135deg, #090d16 0%, #111827 50%, #030712 100%); color: #ffffff; }
                #<?php echo esc_attr( $module_id ); ?>.theme-gradient .qsh-title { color: #ffffff; }
                #<?php echo esc_attr( $module_id ); ?>.theme-gradient .qsh-subtitle { color: #94a3b8; }
                #<?php echo esc_attr( $module_id ); ?>.theme-gradient .qsh-spec-val { color: #ffffff; }
                #<?php echo esc_attr( $module_id ); ?>.theme-gradient .qsh-spec-lbl { color: #64748b; }
                #<?php echo esc_attr( $module_id ); ?>.theme-gradient .qsh-glow { background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(3, 7, 18, 0) 70%); }
                #<?php echo esc_attr( $module_id ); ?>.theme-gradient .qsh-spec-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); }
                #<?php echo esc_attr( $module_id ); ?>.theme-gradient .qsh-badge { background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(129, 140, 248, 0.3); }

                /* 布局 */
                #<?php echo esc_attr( $module_id ); ?> .qsh-container { max-width: 1320px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 2; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-row { display: grid; grid-template-columns: 1.15fr 1fr; align-items: center; gap: 64px; }
                #<?php echo esc_attr( $module_id ); ?>.layout-reverse .qsh-row { grid-template-columns: 1fr 1.15fr; direction: rtl; }
                #<?php echo esc_attr( $module_id ); ?>.layout-reverse .qsh-col-content,
                #<?php echo esc_attr( $module_id ); ?>.layout-reverse .qsh-col-media { direction: ltr; }

                /* 居中大沉浸布局 */
                #<?php echo esc_attr( $module_id ); ?>.layout-center .qsh-row { grid-template-columns: 1fr; text-align: center; gap: 40px; }
                #<?php echo esc_attr( $module_id ); ?>.layout-center .qsh-col-content { max-width: 860px; margin: 0 auto; }
                #<?php echo esc_attr( $module_id ); ?>.layout-center .qsh-specs-grid { max-width: 760px; margin: 32px auto 0; }
                #<?php echo esc_attr( $module_id ); ?>.layout-center .qsh-cta-row { justify-content: center; }

                /* 图片舞台 */
                #<?php echo esc_attr( $module_id ); ?> .qsh-col-media { position: relative; display: flex; align-items: center; justify-content: center; min-height: 420px; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-glow { position: absolute; inset: -15%; z-index: 1; pointer-events: none; border-radius: 50%; filter: blur(50px); }
                #<?php echo esc_attr( $module_id ); ?> .qsh-product-img { position: relative; z-index: 2; max-width: 100%; height: auto; object-fit: contain; filter: drop-shadow(0 25px 35px rgba(0,0,0,0.35)); transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); animation: qshFloat 6s ease-in-out infinite; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-col-media:hover .qsh-product-img { transform: scale(1.04) rotate(-1deg); }
                @keyframes qshFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }

                /* 文案内容区 */
                #<?php echo esc_attr( $module_id ); ?> .qsh-badge { display: inline-flex; align-items: center; height: 26px; padding: 0 12px; border-radius: 999px; font-size: 12px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 18px; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-title { font-size: clamp(30px, 4vw, 52px); font-weight: 800; line-height: 1.15; letter-spacing: -0.03em; margin: 0 0 16px; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-subtitle { font-size: 16px; line-height: 1.7; margin: 0 0 24px; max-width: 580px; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-price-tag { font-size: 28px; font-weight: 800; color: #38bdf8; margin-bottom: 28px; letter-spacing: -0.02em; font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }

                /* 4 维核心参数芯片 */
                #<?php echo esc_attr( $module_id ); ?> .qsh-specs-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 36px; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-spec-card { padding: 14px 18px; border-radius: 12px; backdrop-filter: blur(8px); }
                #<?php echo esc_attr( $module_id ); ?> .qsh-spec-val { display: block; font-size: 22px; font-weight: 800; line-height: 1.2; letter-spacing: -0.02em; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-spec-lbl { display: block; font-size: 12px; margin-top: 4px; line-height: 1.4; }

                /* 双按钮操作区 */
                #<?php echo esc_attr( $module_id ); ?> .qsh-cta-row { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-btn-primary { display: inline-flex; align-items: center; justify-content: center; height: 50px; padding: 0 32px; border-radius: 999px; background: var(--qsh-btn-primary-bg, var(--qiling-component-button-bg, var(--color-primary, #0ea5e9))); color: var(--qsh-btn-primary-text, var(--qiling-component-button-text, #ffffff)); font-size: 15px; font-weight: 700; text-decoration: none; transition: all 0.25s ease; box-shadow: 0 4px 18px rgba(var(--color-primary-rgb, 14, 165, 233), 0.45); }
                #<?php echo esc_attr( $module_id ); ?> .qsh-btn-primary:hover { background: var(--qsh-btn-primary-hover-bg, var(--qiling-component-button-hover-bg, var(--color-primary-hover, #0284c7))); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(var(--color-primary-rgb, 14, 165, 233), 0.6); color: var(--qsh-btn-primary-text, var(--qiling-component-button-text, #ffffff)); }
                #<?php echo esc_attr( $module_id ); ?> .qsh-btn-secondary { display: inline-flex; align-items: center; justify-content: center; height: 50px; padding: 0 24px; border-radius: 999px; background: transparent; color: inherit; font-size: 15px; font-weight: 600; text-decoration: none; border: 1px solid currentColor; opacity: 0.85; transition: all 0.25s ease; }
                #<?php echo esc_attr( $module_id ); ?> .qsh-btn-secondary:hover { opacity: 1; transform: translateY(-2px); }

                @media (max-width: 1024px) {
                    #<?php echo esc_attr( $module_id ); ?> .qsh-row { gap: 36px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsh-col-media { min-height: 320px; }
                }
                @media (max-width: 768px) {
                    #<?php echo esc_attr( $module_id ); ?> .qsh-row { grid-template-columns: 1fr; gap: 32px; }
                    #<?php echo esc_attr( $module_id ); ?>.layout-reverse .qsh-row { direction: ltr; }
                    #<?php echo esc_attr( $module_id ); ?> .qsh-specs-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsh-spec-card { padding: 10px 12px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsh-spec-val { font-size: 18px; }
                    #<?php echo esc_attr( $module_id ); ?> .qsh-cta-row { flex-direction: column; align-items: stretch; }
                    #<?php echo esc_attr( $module_id ); ?> .qsh-btn-primary, #<?php echo esc_attr( $module_id ); ?> .qsh-btn-secondary { width: 100%; }
                }
            </style>

            <div class="qsh-container">
                <div class="qsh-row">
                    <!-- 媒体渲染舞台 -->
                    <div class="qsh-col-media">
                        <div class="qsh-glow" aria-hidden="true"></div>
                        <?php if ( ! empty( $image_url ) ) : ?>
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="qsh-product-img" loading="lazy" decoding="async">
                        <?php else : ?>
                            <div class="qsh-product-img" style="font-size: 80px; color: rgba(255,255,255,0.2);">
                                📦
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 品牌文案与规格芯片 -->
                    <div class="qsh-col-content">
                        <?php if ( ! empty( $settings['badge_text'] ) ) : ?>
                            <span class="qsh-badge"><?php echo esc_html( $settings['badge_text'] ); ?></span>
                        <?php endif; ?>

                        <?php if ( ! empty( $title ) ) : ?>
                            <h2 class="qsh-title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>

                        <?php if ( ! empty( $subtitle ) ) : ?>
                            <p class="qsh-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! empty( $price_html ) ) : ?>
                            <div class="qsh-price-tag"><?php echo esc_html( $price_html ); ?></div>
                        <?php endif; ?>

                        <!-- 4 维核心参数指标 -->
                        <div class="qsh-specs-grid">
                            <div class="qsh-spec-card">
                                <span class="qsh-spec-val"><?php echo esc_html( $settings['spec_1_val'] ); ?></span>
                                <span class="qsh-spec-lbl"><?php echo esc_html( $settings['spec_1_lbl'] ); ?></span>
                            </div>
                            <div class="qsh-spec-card">
                                <span class="qsh-spec-val"><?php echo esc_html( $settings['spec_2_val'] ); ?></span>
                                <span class="qsh-spec-lbl"><?php echo esc_html( $settings['spec_2_lbl'] ); ?></span>
                            </div>
                            <div class="qsh-spec-card">
                                <span class="qsh-spec-val"><?php echo esc_html( $settings['spec_3_val'] ); ?></span>
                                <span class="qsh-spec-lbl"><?php echo esc_html( $settings['spec_3_lbl'] ); ?></span>
                            </div>
                            <div class="qsh-spec-card">
                                <span class="qsh-spec-val"><?php echo esc_html( $settings['spec_4_val'] ); ?></span>
                                <span class="qsh-spec-lbl"><?php echo esc_html( $settings['spec_4_lbl'] ); ?></span>
                            </div>
                        </div>

                        <!-- 行动号召按钮 -->
                        <div class="qsh-cta-row">
                            <?php if ( ! empty( $settings['btn_primary_text'] ) ) : ?>
                                <a href="<?php echo esc_url( $primary_url ); ?>" class="qsh-btn-primary" target="_blank">
                                    <?php echo esc_html( $settings['btn_primary_text'] ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( ! empty( $settings['btn_secondary_text'] ) ) : ?>
                                <a href="<?php echo esc_url( $secondary_url ); ?>" class="qsh-btn-secondary">
                                    <?php echo esc_html( $settings['btn_secondary_text'] ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
