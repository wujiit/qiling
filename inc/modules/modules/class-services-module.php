<?php
/**
 * Services Module - 服务展示
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Services_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-grid-view';
        $this->description = __( '服务项目展示', 'developer-starter' );
    }

    public function get_id() {
        return 'services';
    }

    public function get_name() {
        return __( '服务展示', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id' => 'services_title',
                'label' => __( '模块标题', 'developer-starter' ),
                'type' => 'text',
                'default' => __( '我们的服务', 'developer-starter' ),
            ),
            array(
                'id' => 'services_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px', 'developer-starter' ),
            ),
            array(
                'id' => 'services_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
            ),
            array(
                'id' => 'services_subtitle',
                'label' => __( '模块副标题', 'developer-starter' ),
                'type' => 'text',
            ),
            array(
                'id' => 'services_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px', 'developer-starter' ),
            ),
            array(
                'id' => 'services_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
            ),
            array(
                'id' => 'services_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id' => 'services_padding',
                'label' => __( '上下边距', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'compact' => __( '紧凑 (40px)', 'developer-starter' ),
                    'normal' => __( '标准 (80px)', 'developer-starter' ),
                    'loose' => __( '宽松 (120px)', 'developer-starter' ),
                ),
                'default' => 'normal',
            ),
            array(
                'id' => 'services_columns',
                'label' => __( '每行显示列数', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '4',
            ),
            array( 'id' => 'services_card_bg', 'label' => __( '卡片背景颜色', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'services_card_border', 'label' => __( '卡片边框颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'services_card_hover_border', 'label' => __( '卡片悬停边框颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'services_item_title_color', 'label' => __( '项目标题颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'services_item_desc_color', 'label' => __( '项目描述颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'services_icon_color', 'label' => __( '图标文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'services_icon_bg', 'label' => __( '图标背景颜色', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'services_accent_color', 'label' => __( '顶部强调色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array(
                'id' => 'services_items',
                'label' => __( '服务项目', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'icon', 'label' => __( '图标 (icon-xxx 或 文字)', 'developer-starter' ), 'type' => 'text', 'default' => '01' ),
                    array( 'id' => 'title', 'label' => __( '服务名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'desc', 'label' => __( '服务描述', 'developer-starter' ), 'type' => 'textarea' ),
                    array( 'id' => 'link', 'label' => __( '详情链接', 'developer-starter' ), 'type' => 'text' ),
                ),
            ),
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，服务项目将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['services_title'] ) && $data['services_title'] !== ''
            ? $data['services_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们的服务', 'Our Services' ) : __( '我们的服务', 'developer-starter' ) );
        $subtitle = isset( $data['services_subtitle'] )
            ? $data['services_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '为企业提供全方位的专业服务', 'Professional services built around modern business needs.' ) : __( '为企业提供全方位的专业服务', 'developer-starter' ) );
        $items = isset( $data['services_items'] ) ? $data['services_items'] : array();
        
        // Items Default
        if ( empty( $items ) ) {
            $items = array(
                array( 'icon' => '01', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品研发', 'Product Development' ) : __( '产品研发', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '提供专业的产品研发服务，从需求分析到产品上线全流程支持。', 'Support the full path from discovery and planning to launch.' ) : __( '提供专业的产品研发服务，从需求分析到产品上线全流程支持。', 'developer-starter' ), 'link' => '#' ),
                array( 'icon' => '02', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '解决方案', 'Custom Solutions' ) : __( '解决方案', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '针对不同行业提供定制化解决方案，满足企业个性化需求。', 'Tailored delivery for different industries, goals, and workflows.' ) : __( '针对不同行业提供定制化解决方案，满足企业个性化需求。', 'developer-starter' ), 'link' => '#' ),
                array( 'icon' => '03', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '技术支持', 'Technical Support' ) : __( '技术支持', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '7x24小时技术支持服务，快速响应解决技术问题。', 'Responsive support to resolve issues and keep projects running smoothly.' ) : __( '7x24小时技术支持服务，快速响应解决技术问题。', 'developer-starter' ), 'link' => '#' ),
                array( 'icon' => '04', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '数据分析', 'Data Insights' ) : __( '数据分析', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业数据分析团队，助力企业数据驱动决策。', 'Turn data into practical reporting and business decisions.' ) : __( '专业数据分析团队，助力企业数据驱动决策。', 'developer-starter' ), 'link' => '#' ),
            );
        }

        // Styles
        $bg_color = isset( $data['services_bg_color'] ) ? $data['services_bg_color'] : 'var(--color-neutral-0)';
        $columns = isset( $data['services_columns'] ) ? intval( $data['services_columns'] ) : 4;
        
        $padding_map = array( 'compact' => '40px 0', 'normal' => '80px 0', 'loose' => '120px 0' );
        $padding = isset( $data['services_padding'] ) ? $padding_map[ $data['services_padding'] ] : '80px 0';
        
        $title_size = isset( $data['services_title_size'] ) ? $data['services_title_size'] : '';
        $title_color = isset( $data['services_title_color'] ) ? $data['services_title_color'] : '';
        $subtitle_size = isset( $data['services_subtitle_size'] ) ? $data['services_subtitle_size'] : '';
        $subtitle_color = isset( $data['services_subtitle_color'] ) ? $data['services_subtitle_color'] : '';

        // CSS Variables
        $css_vars = array();
        if ( $bg_color ) $css_vars[] = "--s-bg-color: {$bg_color}";
        $css_vars[] = "--s-padding: {$padding}";
        $css_vars[] = "--s-cols: {$columns}";
        if ( $title_size ) $css_vars[] = "--s-heading-size: {$title_size}";
        if ( $title_color ) $css_vars[] = "--s-heading-color: {$title_color}";
        if ( $subtitle_size ) $css_vars[] = "--s-heading-sub-size: {$subtitle_size}";
        if ( $subtitle_color ) $css_vars[] = "--s-heading-sub-color: {$subtitle_color}";
        $service_color_fields = array(
            'services_card_bg' => '--s-card-bg', 'services_card_border' => '--s-card-border',
            'services_card_hover_border' => '--s-card-hover-border', 'services_item_title_color' => '--s-item-title',
            'services_item_desc_color' => '--s-item-desc', 'services_icon_color' => '--s-icon-color',
            'services_icon_bg' => '--s-icon-bg', 'services_accent_color' => '--s-accent',
        );
        foreach ( $service_color_fields as $field => $variable ) {
            if ( ! empty( $data[ $field ] ) ) $css_vars[] = $variable . ': ' . $data[ $field ];
        }



        $style_attr = ! empty( $css_vars ) ? 'style="' . esc_attr( implode( '; ', $css_vars ) ) . '"' : '';

        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-services" <?php echo $style_attr; ?>>
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="services-grid">
                        <?php foreach ( $items as $index => $item ) : 
                            $icon_raw = isset( $item['icon'] ) ? trim( $item['icon'] ) : '01';
                            $item_title = isset( $item['title'] ) ? $item['title'] : '';
                            $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                            $link = isset( $item['link'] ) ? $item['link'] : '';
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                            ?>
                            <div class="service-item" <?php echo $anim_attr; ?>>
                                <div class="service-icon">
                                    <?php echo developer_starter_get_icon_html( trim( $icon_raw ) ); ?>
                                </div>
                                <h3 class="service-title">
                                    <?php if ( $link ) : ?>
                                        <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $item_title ); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html( $item_title ); ?>
                                    <?php endif; ?>
                                </h3>
                                <p class="service-desc"><?php echo esc_html( $desc ); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
