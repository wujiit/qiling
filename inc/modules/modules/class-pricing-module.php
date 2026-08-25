<?php
/**
 * Pricing Module - 价格方案
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pricing_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-money-alt';
        $this->description = __( '展示价格方案套餐', 'developer-starter' );
    }

    public function get_id() {
        return 'pricing';
    }

    public function get_name() {
        return __( '价格方案', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'pricing_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '价格方案', 'Pricing Plans' ) : __( '价格方案', 'developer-starter' ) ),
            array(
                'id'      => 'pricing_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
                'desc'    => __( '如 2rem 或 32px', 'developer-starter' ),
            ),
            array( 'id' => 'pricing_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'pricing_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array(
                'id'      => 'pricing_subtitle_size',
                'type'    => 'text',
                'label'   => __( '副标题字体大小', 'developer-starter' ),
                'default' => '1rem',
                'desc'    => __( '如 1rem 或 16px', 'developer-starter' ),
            ),
            array( 'id' => 'pricing_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ) ),
            
            array(
                'id' => 'module_bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
                'dependency' => array( 'module_bg_type', '==', 'color' ),
            ),
            array(
                'id' => 'module_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
            array(
                'id' => 'module_bg_overlay',
                'label' => __( '背景遮罩浓度', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '0' => __( '无遮罩', 'developer-starter' ),
                    '0.1' => '10%',
                    '0.2' => '20%',
                    '0.3' => '30%',
                    '0.4' => '40%',
                    '0.5' => '50%',
                    '0.6' => '60%',
                    '0.7' => '70%',
                    '0.8' => '80%',
                    '0.9' => '90%',
                ),
                'default' => '0',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
             array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            
            array( 'id' => 'pricing_columns', 'type' => 'select', 'label' => __( '列数', 'developer-starter' ), 'options' => array( '3' => __( '3列', 'developer-starter' ), '4' => __( '4列', 'developer-starter' ) ), 'default' => '3' ),
            array(
                'id'      => 'pricing_cards_mode',
                'type'    => 'select',
                'label'   => __( '方案布局模式', 'developer-starter' ),
                'options' => array(
                    'native'      => __( '跟随原有样式', 'developer-starter' ),
                    'connected'   => __( '连体方案', 'developer-starter' ),
                    'independent' => __( '独立卡片', 'developer-starter' ),
                ),
                'default' => 'native',
                'desc'    => __( '连体方案使用一个共同外框，方案之间没有空白；独立卡片让每个方案单独成卡并保留间距。', 'developer-starter' ),
            ),
            array( 'id' => 'pricing_items', 'type' => 'repeater', 'label' => __( '方案列表', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'name', 'type' => 'text', 'label' => __( '方案名称', 'developer-starter' ) ),
                array( 'id' => 'name_color', 'type' => 'color', 'label' => __( '名称颜色', 'developer-starter' ) ),
                array( 'id' => 'price', 'type' => 'text', 'label' => __( '价格', 'developer-starter' ) ),
                array( 'id' => 'price_color', 'type' => 'color', 'label' => __( '价格颜色', 'developer-starter' ) ),
                array( 'id' => 'period', 'type' => 'text', 'label' => __( '周期 (如 /月)', 'developer-starter' ) ),
                array( 'id' => 'desc', 'type' => 'text', 'label' => __( '描述', 'developer-starter' ) ),
                array( 'id' => 'desc_color', 'type' => 'color', 'label' => __( '描述颜色', 'developer-starter' ) ),
                
                array( 'id' => 'features', 'type' => 'textarea', 'label' => __( '特性列表 (每行一个，支持 ✓✗ 前缀)', 'developer-starter' ), 'rows' => 10 ),
                array( 'id' => 'features_color', 'type' => 'color', 'label' => __( '特性列表文字颜色', 'developer-starter' ) ),
                
                array( 'id' => 'btn_text', 'type' => 'text', 'label' => __( '按钮文字', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即购买', 'Buy Now' ) : __( '立即购买', 'developer-starter' ) ),
                array( 'id' => 'btn_link', 'type' => 'text', 'label' => __( '按钮链接', 'developer-starter' ), 'default' => '#' ),
                array( 'id' => 'btn_bg', 'type' => 'color', 'label' => __( '按钮背景色', 'developer-starter' ) ),
                array( 'id' => 'btn_text_color', 'type' => 'color', 'label' => __( '按钮文字颜色', 'developer-starter' ) ),
                $this->get_button_border_color_field( 'btn_border_color' ),
                array( 'id' => 'card_bg', 'type' => 'color', 'label' => __( '卡片背景色', 'developer-starter' ), 'default' => 'var(--color-neutral-0)' ),
                array( 'id' => 'featured', 'type' => 'select', 'label' => __( '推荐高亮', 'developer-starter' ), 'options' => array( '' => __( '否', 'developer-starter' ), '1' => __( '是', 'developer-starter' ) ) ),
                array( 'id' => 'featured_text', 'type' => 'text', 'label' => __( '推荐标签文字', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '推荐', 'Popular' ) : __( '推荐', 'developer-starter' ), 'dependency' => array( 'featured', '==', '1' ) ),

                array( 'id' => 'featured_bg', 'type' => 'color', 'label' => __( '推荐标签背景', 'developer-starter' ), 'desc' => __( '留空时跟随页面预设/全局徽章颜色。', 'developer-starter' ), 'dependency' => array( 'featured', '==', '1' ) ),
            ) ),
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，价格方案将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['pricing_title'] ) && $data['pricing_title'] !== ''
            ? $data['pricing_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '价格方案', 'Pricing Plans' ) : __( '价格方案', 'developer-starter' ) );
        $title_size = isset( $data['pricing_title_size'] ) && $data['pricing_title_size'] !== '' ? $data['pricing_title_size'] : '2rem';
        
        $subtitle = isset( $data['pricing_subtitle'] )
            ? $data['pricing_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '选择适合您的方案，开启高效之旅', 'Choose the plan that fits your next stage of growth.' ) : __( '选择适合您的方案，开启高效之旅', 'developer-starter' ) );
        $subtitle_size = isset( $data['pricing_subtitle_size'] ) && $data['pricing_subtitle_size'] !== '' ? $data['pricing_subtitle_size'] : '1rem';
        
        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        // Fallback for old field
        if ( empty( $bg_color ) && isset( $data['pricing_bg_color'] ) ) {
            $bg_color = $data['pricing_bg_color'];
        }
        
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        
        $title_color = isset( $data['pricing_title_color'] ) && ! empty( $data['pricing_title_color'] ) ? $data['pricing_title_color'] : '';
        $subtitle_color = isset( $data['pricing_subtitle_color'] ) && ! empty( $data['pricing_subtitle_color'] ) ? $data['pricing_subtitle_color'] : '';
        
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '80px';
        
        $columns = isset( $data['pricing_columns'] ) && ! empty( $data['pricing_columns'] ) ? intval( $data['pricing_columns'] ) : 3;
        $items = isset( $data['pricing_items'] ) ? $data['pricing_items'] : array();
        $cards_mode = isset( $data['pricing_cards_mode'] )
            ? sanitize_key( (string) $data['pricing_cards_mode'] )
            : '';
        if ( '' === $cards_mode && isset( $data['_ds_visual']['cards']['mode'] ) ) {
            $cards_mode = sanitize_key( (string) $data['_ds_visual']['cards']['mode'] );
        }
        if ( ! in_array( $cards_mode, array( 'native', 'connected', 'independent' ), true ) ) {
            $cards_mode = 'native';
        }
        
        // 默认示例数据
        if ( empty( $items ) ) {
            $items = array(
                array( 
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础版', 'Starter' ) : __( '基础版', 'developer-starter' ),
                    'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 99 ) : '¥99',
                    'period' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/月', '/month' ) : __( '/月', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合个人用户和小型项目', 'Best for individuals and small projects.' ) : __( '适合个人用户和小型项目', 'developer-starter' ),
                    'features' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓ 基础功能支持\n✓ 5GB 存储空间\n✓ 邮件支持\n✗ 高级分析\n✗ API 接口", "✓ Core feature access\n✓ 5GB storage\n✓ Email support\n✗ Advanced analytics\n✗ API access" ) : __( "✓ 基础功能支持\n✓ 5GB 存储空间\n✓ 邮件支持\n✗ 高级分析\n✗ API 接口", 'developer-starter' ),
                    'btn_text' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即购买', 'Buy Now' ) : __( '立即购买', 'developer-starter' ),
                    'btn_link' => '#',
                    'card_bg' => 'var(--color-neutral-0)',
                    'featured' => '',
                    'featured_text' => '',
                    'featured_bg' => ''
                ),
                array( 
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业版', 'Professional' ) : __( '专业版', 'developer-starter' ),
                    'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 299 ) : '¥299',
                    'period' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/月', '/month' ) : __( '/月', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合成长型企业', 'Built for growing teams and active businesses.' ) : __( '适合成长型企业', 'developer-starter' ),
                    'features' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓ 全部基础功能\n✓ 50GB 存储空间\n✓ 优先技术支持\n✓ 高级数据分析\n✓ API 接口", "✓ Everything in Starter\n✓ 50GB storage\n✓ Priority support\n✓ Advanced analytics\n✓ API access" ) : __( "✓ 全部基础功能\n✓ 50GB 存储空间\n✓ 优先技术支持\n✓ 高级数据分析\n✓ API 接口", 'developer-starter' ),
                    'btn_text' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即购买', 'Buy Now' ) : __( '立即购买', 'developer-starter' ),
                    'btn_link' => '#',
                    'card_bg' => 'var(--color-neutral-0)',
                    'featured' => '1',
                    'featured_text' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '推荐', 'Popular' ) : __( '推荐', 'developer-starter' ),
                    'featured_bg' => ''
                ),
                array( 
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '企业版', 'Enterprise' ) : __( '企业版', 'developer-starter' ),
                    'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 999 ) : '¥999',
                    'period' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/月', '/month' ) : __( '/月', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合大型企业定制需求', 'For custom enterprise requirements and larger delivery scope.' ) : __( '适合大型企业定制需求', 'developer-starter' ),
                    'features' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "✓ 全部专业功能\n✓ 无限存储空间\n✓ 7×24专属客服\n✓ 定制化开发\n✓ 专属客户经理", "✓ Everything in Professional\n✓ Unlimited storage\n✓ Dedicated support\n✓ Custom development\n✓ Account manager" ) : __( "✓ 全部专业功能\n✓ 无限存储空间\n✓ 7×24专属客服\n✓ 定制化开发\n✓ 专属客户经理", 'developer-starter' ),
                    'btn_text' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '联系我们', 'Contact Sales' ) : __( '联系我们', 'developer-starter' ),
                    'btn_link' => '#',
                    'card_bg' => 'var(--color-neutral-0)',
                    'featured' => '',
                    'featured_text' => '',
                    'featured_bg' => ''
                ),
            );
        }
        
        // Section Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_type === 'color' && ! empty( $bg_color ) ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        } elseif ( $bg_type === 'image' && ! empty( $bg_image ) ) {
            $section_style .= "position: relative; background-image: url('{$bg_image}'); background-size: cover; background-position: center;";
        }
        
        // Typography Styles
        $title_style = "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        // 网格类
        $grid_class = 'pricing-grid grid-cols-' . $columns;
        if ( 'connected' === $cards_mode ) {
            $grid_class .= ' pricing-cards-connected';
        }
        $grid_layout_style = '';
        if ( 'connected' === $cards_mode ) {
            $grid_layout_style = 'gap:0;';
        } elseif ( 'independent' === $cards_mode ) {
            $grid_layout_style = 'gap:var(--qiling-module-component-gap,var(--qiling-space-32));';
        }
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-pricing bg-type-<?php echo esc_attr( $bg_type ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="background: rgba(var(--qiling-rgb-0-0-0), <?php echo esc_attr( $bg_overlay ); ?>);"></div>
            <?php endif; ?>
            
            <div class="container" style="position: relative; z-index: 2;">
                <div class="section-header text-center">
                    <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>"><?php echo wp_kses_post( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle" style="<?php echo esc_attr( $subtitle_style ); ?>"><?php echo wp_kses_post( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <?php if ( 'connected' === $cards_mode ) : ?>
                        <div class="pricing-connected-shell" data-pricing-connected-shell="1">
                    <?php endif; ?>
                    <div class="<?php echo esc_attr( $grid_class ); ?>" data-pricing-cards-mode="<?php echo esc_attr( $cards_mode ); ?>" style="<?php echo esc_attr( $grid_layout_style ); ?>">
                        <?php foreach ( $items as $index => $item ) : 
                            $name = isset( $item['name'] ) ? $item['name'] : '';
                            $name_color = isset( $item['name_color'] ) && ! empty( $item['name_color'] ) ? $item['name_color'] : '';
                            $price = isset( $item['price'] ) ? $item['price'] : '';
                            $price_color = isset( $item['price_color'] ) && ! empty( $item['price_color'] ) ? $item['price_color'] : '';
                            $period = isset( $item['period'] ) ? $item['period'] : '';
                            $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                            $desc_color = isset( $item['desc_color'] ) && ! empty( $item['desc_color'] ) ? $item['desc_color'] : '';
                            
                            $features = isset( $item['features'] ) ? $item['features'] : '';
                            $features_color = isset( $item['features_color'] ) && ! empty( $item['features_color'] ) ? $item['features_color'] : '';
                            
                            $btn_text = isset( $item['btn_text'] ) ? $item['btn_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即购买', 'Buy Now' ) : __( '立即购买', 'developer-starter' ) );
                                    $btn_link = isset( $item['btn_link'] ) ? $item['btn_link'] : '#';
                                    $btn_bg = isset( $item['btn_bg'] ) && ! empty( $item['btn_bg'] ) ? $item['btn_bg'] : '';
                                    $btn_text_color = isset( $item['btn_text_color'] ) && ! empty( $item['btn_text_color'] ) ? $item['btn_text_color'] : '';
                                     $btn_border_color = isset( $item['btn_border_color'] ) && ! empty( $item['btn_border_color'] ) ? $item['btn_border_color'] : '';
                                     $card_bg = isset( $item['card_bg'] ) && ! empty( $item['card_bg'] ) ? $item['card_bg'] : 'var(--color-neutral-0)';
                            $normalized_card_bg = strtolower( preg_replace( '/\s+/', '', (string) $card_bg ) );
                            $normalized_features_color = strtolower( preg_replace( '/\s+/', '', (string) $features_color ) );
                            $light_card_backgrounds = array( '#fff', '#ffffff', 'white', 'rgb(255,255,255)', 'rgba(255,255,255,1)', 'var(--color-neutral-0)' );
                            $white_text_colors = array( '#fff', '#ffffff', 'white', 'rgb(255,255,255)', 'rgba(255,255,255,1)' );
                            if ( in_array( $normalized_card_bg, $light_card_backgrounds, true ) && in_array( $normalized_features_color, $white_text_colors, true ) ) {
                                $features_color = 'var(--color-text, #334155)';
                            }
                             $is_featured = isset( $item['featured'] ) && $item['featured'];
                            $featured_text = isset( $item['featured_text'] ) && ! empty( $item['featured_text'] ) ? $item['featured_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '推荐', 'Popular' ) : __( '推荐', 'developer-starter' ) );
                            $featured_bg = isset( $item['featured_bg'] ) && ! empty( $item['featured_bg'] ) ? $item['featured_bg'] : 'var(--qiling-component-badge-bg)';
                            
                            // 卡片背景样式
                            $card_bg_style = '';
                            if ( 'connected' !== $cards_mode ) {
                                $card_bg_style = strpos( $card_bg, 'gradient' ) !== false ? "background: {$card_bg};" : "background-color: {$card_bg};";
                            } else {
                                $card_bg_style = 'background:transparent;border-radius:0;box-shadow:none;transform:none;';
                            }
                            
                            // 特性列表转数组
                            $features = str_replace( array( "\r\n", "\r" ), "\n", $features );
                            $feature_list = array_filter( array_map( 'trim', explode( "\n", $features ) ) );
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="pricing-card <?php echo $is_featured ? 'pricing-featured' : ''; ?>" style="<?php echo esc_attr( $card_bg_style ); ?>" <?php echo $anim_attr; ?>>
                                <!-- 推荐标注 -->
                                <?php if ( $is_featured ) : ?>
                                    <div class="pricing-badge" style="background: <?php echo esc_attr( $featured_bg ); ?>;">
                                        <?php echo esc_html( $featured_text ); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- 方案名称 -->
                                <?php $name_style = ! empty( $name_color ) ? "color: {$name_color};" : ''; ?>
                                <h3 class="pricing-name" style="<?php echo esc_attr( $name_style ); ?>">
                                    <?php echo wp_kses_post( $name ); ?>
                                </h3>
                                
                                <!-- 方案描述 -->
                                <?php if ( $desc ) : 
                                    $desc_style = ! empty( $desc_color ) ? "color: {$desc_color};" : '';
                                ?>
                                    <p class="pricing-desc" style="<?php echo esc_attr( $desc_style ); ?>">
                                        <?php echo wp_kses_post( $desc ); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- 价格 -->
                                <?php 
                                // 价格颜色：支持渐变色或纯色
                                if ( ! empty( $price_color ) ) {
                                    if ( strpos( $price_color, 'gradient' ) !== false ) {
                                        $price_style = "background: {$price_color}; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;";
                                    } else {
                                        $price_style = "color: {$price_color};";
                                    }
                                } else {
                                    $price_style = "background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;";
                                }
                                ?>
                                <div class="pricing-price-box">
                                    <span class="pricing-price" style="<?php echo esc_attr( $price_style ); ?>">
                                        <?php echo wp_kses_post( $price ); ?>
                                    </span>
                                    <?php if ( $period ) : ?>
                                        <span class="pricing-period"><?php echo wp_kses_post( $period ); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- 特性列表 -->
                                <?php if ( ! empty( $feature_list ) ) : ?>
                                    <ul class="pricing-features">
                                        <?php foreach ( $feature_list as $feature ) : 
                                            $is_included = strpos( $feature, '✓' ) === 0 || strpos( $feature, '√' ) === 0;
                                            $is_excluded = strpos( $feature, '✗' ) === 0 || strpos( $feature, '×' ) === 0;
                                            $feature_text = ltrim( $feature, '✓✗√×  ' );
                                            
                                            // 特性文字颜色：如果有设置则优先生效，否则excluded用灰色
                                            if ( ! empty( $features_color ) ) {
                                                $text_style = "color: {$features_color};";
                                            } else {
                                                $text_style = $is_excluded
                                                    ? 'color: var(--color-gray-400, var(--color-neutral-400));'
                                                    : 'color: var(--qiling-module-card-text, var(--color-text, #334155));';
                                            }
                                            
                                            $icon_color = $is_included ? 'var(--color-success)' : ( $is_excluded ? 'var(--color-error)' : 'var(--color-primary)' );
                                        ?>
                                            <li style="<?php echo esc_attr( $text_style ); ?>">
                                                <span class="pricing-icon">
                                                    <?php if ( $is_included ) : ?>
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $icon_color ); ?>" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    <?php elseif ( $is_excluded ) : ?>
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $icon_color ); ?>" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                    <?php else : ?>
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $icon_color ); ?>" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    <?php endif; ?>
                                                </span>
                                                <span><?php echo wp_kses_post( $feature_text ); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <!-- 按钮 -->
                                <?php if ( $btn_text && $btn_link ) : 
                                    // 按钮样式
                                    $btn_style = '';
                                    if ( ! empty( $btn_bg ) ) {
                                        $btn_style .= strpos( $btn_bg, 'gradient' ) !== false ? "background: {$btn_bg};" : "background-color: {$btn_bg};";
                                    } elseif ( $is_featured ) {
                                        $btn_style .= "background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);";
                                    }
                                    
                                    if ( ! empty( $btn_text_color ) ) {
                                        $btn_style .= "color: {$btn_text_color};";
                                    } elseif ( $is_featured ) {
                                        $btn_style .= "color: var(--color-neutral-0);";
                                    }

                                    if ( ! empty( $btn_border_color ) ) {
                                        $btn_style .= "border-color: {$btn_border_color};";
                                    } elseif ( ! empty( $btn_bg ) ) {
                                        $btn_style .= "border-color: {$btn_bg};";
                                    } elseif ( $is_featured ) {
                                        $btn_style .= "border-color: var(--color-primary);";
                                    }
                                ?>
                                    <a href="<?php echo esc_url( $btn_link ); ?>" class="pricing-btn <?php echo $is_featured ? 'btn-featured' : ''; ?>" style="<?php echo esc_attr( $btn_style ); ?>">
                                        <?php echo wp_kses_post( $btn_text ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ( 'connected' === $cards_mode ) : ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
