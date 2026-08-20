<?php
/**
 * Features Module - 企业优势
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Features_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-awards';
        $this->description = __( '展示企业核心优势', 'developer-starter' );
        
        // 定义字段
        $this->fields = array(
            // 内容设置
            array(
                'id' => 'features_title',
                'label' => __( '模块标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '为什么选择我们', 'Why Choose Us' ) : __( '为什么选择我们', 'developer-starter' ),
            ),
            array(
                'id' => 'features_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px', 'developer-starter' ),
            ),
            array(
                'id' => 'features_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
            ),
            
            array(
                'id' => 'features_subtitle',
                'label' => __( '模块副标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们的核心竞争优势', 'The strengths that set our team apart.' ) : __( '我们的核心竞争优势', 'developer-starter' ),
            ),
            array(
                'id' => 'features_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px', 'developer-starter' ),
            ),
            array(
                'id' => 'features_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
            ),

            array(
                'id' => 'features_items',
                'label' => __( '优势列表', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'icon', 'label' => __( '图标 (Emoji 或 Symbol类名)', 'developer-starter' ), 'type' => 'text', 'default' => '+' ),
                    array( 'id' => 'title', 'label' => __( '优势标题', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'desc', 'label' => __( '优势描述', 'developer-starter' ), 'type' => 'textarea' ),
                    array( 'id' => 'link', 'label' => __( '链接 (可选)', 'developer-starter' ), 'type' => 'text' ),
                ),
            ),
            
            // 布局设置
            array(
                'id' => 'features_columns',
                'label' => __( '每行显示列数', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '1' => __( '1列', 'developer-starter' ),
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '4',
            ),
            array(
                'id' => 'features_icon_position',
                'label' => __( '图标位置', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'left' => __( '左侧 (水平排列)', 'developer-starter' ),
                    'top' => __( '顶部 (垂直排列)', 'developer-starter' ),
                ),
                'default' => 'left',
            ),
            array(
                'id' => 'features_text_align',
                'label' => __( '文本对齐方式', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'left' => __( '左对齐', 'developer-starter' ),
                    'center' => __( '居中对齐', 'developer-starter' ),
                    'right' => __( '右对齐', 'developer-starter' ),
                ),
                'default' => 'left',
            ),

            // 样式设置
            array(
                'id' => 'features_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color', 
                'default' => 'var(--color-neutral-50)',
            ),
             array(
                'id' => 'features_padding',
                'label' => __( '模块内边距', 'developer-starter' ),
                'type' => 'select', 
                'options' => array(
                    'compact' => __( '紧凑 (40px)', 'developer-starter' ),
                    'normal' => __( '标准 (80px)', 'developer-starter' ),
                    'loose' => __( '宽敞 (120px)', 'developer-starter' ),
                ),
                'default' => 'normal',
            ),
            
            // 卡片样式
            array(
                'id' => 'features_card_style',
                'label' => __( '卡片风格', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'none' => __( '无样式 (透明)', 'developer-starter' ),
                    'border' => __( '仅边框', 'developer-starter' ),
                    'shadow' => __( '仅阴影', 'developer-starter' ),
                    'card' => __( '完整卡片 (背景+阴影)', 'developer-starter' ),
                ),
                'default' => 'none',
            ),
            array(
                'id' => 'features_card_bg',
                'label' => __( '卡片背景色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
                'dependency' => array( 'features_card_style', '!=', 'none' ),
            ),
            array( 'id' => 'features_card_border', 'label' => __( '卡片边框颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'features_card_hover_border', 'label' => __( '卡片悬停边框颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'features_item_title_color', 'label' => __( '卡片标题颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'features_item_desc_color', 'label' => __( '卡片描述颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array(
                'id' => 'features_card_radius',
                'label' => __( '卡片圆角', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '0' => __( '直角 (0px)', 'developer-starter' ),
                    '8px' => __( '小圆角 (8px)', 'developer-starter' ),
                    '16px' => __( '中圆角 (16px)', 'developer-starter' ),
                    '24px' => __( '大圆角 (24px)', 'developer-starter' ),
                ),
                'default' => '16px',
                 'dependency' => array( 'features_card_style', '!=', 'none' ),
            ),
            array(
                'id' => 'features_hover_effect',
                'label' => __( '悬停效果', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'none' => __( '无效果', 'developer-starter' ),
                    'lift' => __( '上浮', 'developer-starter' ),
                    'scale' => __( '微放大', 'developer-starter' ),
                ),
                'default' => 'none',
            ),

            // 图标样式
            array(
                'id' => 'features_icon_style',
                'label' => __( '图标风格', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'simple' => __( '仅图标', 'developer-starter' ),
                    'circle' => __( '圆形背景', 'developer-starter' ),
                    'square' => __( '方形背景 (带圆角)', 'developer-starter' ),
                ),
                'default' => 'square',
            ),
            array(
                'id' => 'features_icon_size',
                'label' => __( '图标尺寸', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '32px' => __( '小 (32px)', 'developer-starter' ),
                    '48px' => __( '中 (48px)', 'developer-starter' ),
                    '64px' => __( '大 (64px)', 'developer-starter' ),
                    '80px' => __( '超大 (80px)', 'developer-starter' ),
                ),
                'default' => '48px',
            ),
            array(
                'id' => 'features_icon_color',
                'label' => __( '图标颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'features_icon_bg',
                'label' => __( '图标背景色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--qiling-color-e0e7ff)',
                'dependency' => array( 'features_icon_style', '!=', 'simple' ),
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
                'description' => __( '开启后，优势卡片将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function get_id() {
        return 'features';
    }

    public function get_name() {
        return __( '企业优势', 'developer-starter' );
    }
    
    // 移除自定义 get_fields，使用 Module_Base 的默认方法返回 $this->fields

    public function render( $data = array() ) {
        // 内容
        $title = isset( $data['features_title'] )
            ? $data['features_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '为什么选择我们', 'Why Choose Us' ) : __( '为什么选择我们', 'developer-starter' ) );
        $subtitle = isset( $data['features_subtitle'] )
            ? $data['features_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们的核心竞争优势', 'The strengths that set us apart.' ) : __( '我们的核心竞争优势', 'developer-starter' ) );
        $items = isset( $data['features_items'] ) ? $data['features_items'] : array();
        
        // 布局
        $columns = isset( $data['features_columns'] ) ? intval( $data['features_columns'] ) : 4;
        $icon_pos = isset( $data['features_icon_position'] ) ? $data['features_icon_position'] : 'left';
        $text_align = isset( $data['features_text_align'] ) ? $data['features_text_align'] : 'left';
        
        // 样式
        $bg_color = isset( $data['features_bg_color'] ) && '' !== trim( (string) $data['features_bg_color'] )
            ? $data['features_bg_color']
            : ( isset( $data['module_bg_color'] ) && '' !== trim( (string) $data['module_bg_color'] ) ? $data['module_bg_color'] : 'var(--color-neutral-50)' );
        $padding_map = array( 'compact' => '40px 0', 'normal' => '80px 0', 'loose' => '120px 0' );
        $padding = isset( $data['features_padding'] ) ? $padding_map[ $data['features_padding'] ] : '80px 0';
        
        // Typography
        $title_size = isset( $data['features_title_size'] ) ? $data['features_title_size'] : '';
        $title_color = isset( $data['features_title_color'] ) ? $data['features_title_color'] : '';
        $subtitle_size = isset( $data['features_subtitle_size'] ) ? $data['features_subtitle_size'] : '';
        $subtitle_color = isset( $data['features_subtitle_color'] ) ? $data['features_subtitle_color'] : '';
        
        // 卡片
        $card_style = isset( $data['features_card_style'] ) ? $data['features_card_style'] : 'none';
        $card_bg = isset( $data['features_card_bg'] ) ? $data['features_card_bg'] : 'var(--color-neutral-0)';
        $card_radius = isset( $data['features_card_radius'] ) ? $data['features_card_radius'] : '16px';
        $hover_effect = isset( $data['features_hover_effect'] ) ? $data['features_hover_effect'] : 'none';
        
        // 图标
        $icon_style = isset( $data['features_icon_style'] ) ? $data['features_icon_style'] : 'square';
        $icon_size = isset( $data['features_icon_size'] ) ? $data['features_icon_size'] : '48px';
        $icon_color = isset( $data['features_icon_color'] ) && !empty($data['features_icon_color']) ? $data['features_icon_color'] : 'var(--color-primary)';
        $icon_bg = isset( $data['features_icon_bg'] ) && !empty($data['features_icon_bg']) ? $data['features_icon_bg'] : 'var(--qiling-color-e0e7ff)';
        
        // 默认数据
        if ( empty( $items ) ) {
            $items = array(
                array( 'icon' => '+', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业团队', 'Experienced Team' ) : __( '专业团队', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '拥有10年行业经验的专业团队。', 'A skilled team with years of hands-on delivery experience.' ) : __( '拥有10年行业经验的专业团队。', 'developer-starter' ) ),
                array( 'icon' => '+', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '优质服务', 'Reliable Service' ) : __( '优质服务', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '7x24小时全天候服务支持。', 'Responsive communication and dependable follow-through.' ) : __( '7x24小时全天候服务支持。', 'developer-starter' ) ),
                array( 'icon' => '+', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '价格透明', 'Transparent Pricing' ) : __( '价格透明', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '无隐形消费，明码标价。', 'Clear pricing with no hidden fees or surprise add-ons.' ) : __( '无隐形消费，明码标价。', 'developer-starter' ) ),
                array( 'icon' => '+', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '品质保障', 'Quality Assurance' ) : __( '品质保障', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'ISO9001质量管理体系认证。', 'A process-focused approach to quality and consistency.' ) : __( 'ISO9001质量管理体系认证。', 'developer-starter' ) ),
            );
        }

        // 计算唯一ID用于样式隔离
        $unique_id = 'features-' . uniqid();
        
        // 构建动态 CSS
        $css_vars = array();
        $css_vars[] = "--f-cols: {$columns}";
        $css_vars[] = "--f-icon-size: {$icon_size}";
        $css_vars[] = "--f-icon-color: {$icon_color}";
        $css_vars[] = "--f-icon-bg: {$icon_bg}";
        $css_vars[] = "--f-card-bg: {$card_bg}";
        $css_vars[] = "--f-card-radius: {$card_radius}";
        $css_vars[] = "--f-icon-font-size: calc({$icon_size} * 0.5)"; 
        foreach ( array( 'features_card_border' => '--f-card-border', 'features_card_hover_border' => '--f-card-hover-border', 'features_item_title_color' => '--f-item-title', 'features_item_desc_color' => '--f-item-desc' ) as $field => $variable ) {
            if ( ! empty( $data[ $field ] ) ) $css_vars[] = $variable . ': ' . $data[ $field ];
        }
        
        // Title/Subtitle Styles
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        // 图标容器圆角
        if ( $icon_style === 'circle' ) {
            $css_vars[] = "--f-icon-radius: 50%";
        } elseif ( $icon_style === 'square' ) {
            $css_vars[] = "--f-icon-radius: 12px"; // 方形稍微带点圆角好看
        } else {
             $css_vars[] = "--f-icon-radius: 0";
        }

        $style_attr = 'style="' . implode( '; ', $css_vars ) . '"';
        
        // 容器类名
        $container_classes = array( 'features-grid' );
        if ( $icon_pos === 'top' ) $container_classes[] = 'icon-pos-top';
        if ( $icon_pos === 'left' ) $container_classes[] = 'icon-pos-left';
        $container_classes[] = 'text-' . $text_align;
        
        // 卡片类名
        $card_classes = array( 'feature-item' );
        $card_classes[] = 'style-' . $card_style;
        $card_classes[] = 'hover-' . $hover_effect;

        $card_classes[] = 'hover-' . $hover_effect;

        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-features" id="<?php echo esc_attr( $unique_id ); ?>" style="background: <?php echo esc_attr( $bg_color ); ?>; padding: <?php echo esc_attr( $padding ); ?>;">
            <div class="container" <?php echo $style_attr; ?>>
                <div class="section-header text-center" style="margin-bottom: var(--qiling-space-50);">
                    <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo wp_kses_post( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo wp_kses_post( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>">
                        <?php foreach ( $items as $index => $item ) : 
                            $icon_raw = isset( $item['icon'] ) ? trim( $item['icon'] ) : '+';
                            $item_title = isset( $item['title'] ) ? $item['title'] : '';
                            $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                            $link = isset( $item['link'] ) ? $item['link'] : '';
                            
                            // 图标仅走当前主题统一图标 helper
                            $icon = trim( $icon_raw );
                            
                            $tag = $link ? 'a' : 'div';
                            $href = $link ? ' href="' . esc_url( $link ) . '"' : '';
                            $link_class = $link ? ' has-link' : '';
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                            ?>
                            <<?php echo $tag . $href; ?> class="<?php echo esc_attr( implode( ' ', $card_classes ) . $link_class ); ?>" <?php echo $anim_attr; ?>>
                                
                                <div class="feature-icon-wrapper style-<?php echo esc_attr( $icon_style ); ?>">
                                    <?php echo developer_starter_get_icon_html( $icon ); ?>
                                </div>
                                
                                <div class="feature-content">
                                    <h3 class="feature-title"><?php echo wp_kses_post( $item_title ); ?></h3>
                                    <p class="feature-desc"><?php echo wp_kses_post( $desc ); ?></p>
                                </div>
                            </<?php echo $tag; ?>>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            

        </section>
        <?php
    }
}
