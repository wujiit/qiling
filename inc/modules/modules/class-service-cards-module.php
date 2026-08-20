<?php
/**
 * Service Cards Module - 服务标签卡片
 *
 * 左右布局的服务卡片：左侧图标，右侧标题+标签+描述
 * 支持自定义数量、链接、背景透明度等
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Service_Cards_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-grid-view';
        $this->description = __( '图标卡片，左侧图标右侧标题描述', 'developer-starter' );
    }

    public function get_id() {
        return 'service_cards';
    }

    public function get_name() {
        return __( '服务标签卡片', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // === 模块整体设置 ===
            array(
                'id'      => 'sc_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'sc_subtitle',
                'type'    => 'text',
                'label'   => __( '模块副标题', 'developer-starter' ),
                'default' => '',
            ),
            array( 'id' => 'sc_heading_color', 'type' => 'color', 'label' => __( '模块标题颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'sc_subtitle_color', 'type' => 'color', 'label' => __( '模块副标题颜色', 'developer-starter' ), 'default' => '' ),
            array(
                'id'      => 'sc_bg_color',
                'type'    => 'text',
                'label'   => __( '模块背景颜色(支持渐变)', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'sc_padding',
                'type'    => 'select',
                'label'   => __( '上下内边距', 'developer-starter' ),
                'options' => array(
                    '20'  => '20px',
                    '40'  => '40px',
                    '60'  => __( '60px（推荐）', 'developer-starter' ),
                    '80'  => '80px',
                ),
                'default' => '60',
            ),
            array(
                'id'      => 'sc_columns',
                'type'    => 'select',
                'label'   => __( '每行显示数量', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2个', 'developer-starter' ),
                    '3' => __( '3个', 'developer-starter' ),
                    '4' => __( '4个（推荐）', 'developer-starter' ),
                ),
                'default' => '4',
            ),
            array(
                'id'      => 'sc_gap',
                'type'    => 'text',
                'label'   => __( '卡片间距', 'developer-starter' ),
                'default' => '20px',
            ),
            
            // === 卡片样式设置 ===
            array(
                'id'      => 'sc_card_bg',
                'type'    => 'text',
                'label'   => __( '卡片背景颜色(支持rgba透明度)', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-255-255-255-09)',
            ),
            array(
                'id'      => 'sc_card_radius',
                'type'    => 'text',
                'label'   => __( '卡片圆角', 'developer-starter' ),
                'default' => '12px',
            ),
            array(
                'id'      => 'sc_card_shadow',
                'type'    => 'select',
                'label'   => __( '卡片阴影', 'developer-starter' ),
                'options' => array(
                    'none'   => __( '无阴影', 'developer-starter' ),
                    'light'  => __( '轻微阴影', 'developer-starter' ),
                    'medium' => __( '中等阴影（推荐）', 'developer-starter' ),
                    'strong' => __( '明显阴影', 'developer-starter' ),
                ),
                'default' => 'medium',
            ),
            array(
                'id'      => 'sc_card_border',
                'type'    => 'text',
                'label'   => __( '卡片边框(如: 1px solid var(--color-neutral-200))', 'developer-starter' ),
                'default' => '',
            ),
            
            // === 图标设置 ===
            array(
                'id'      => 'sc_icon_size',
                'type'    => 'text',
                'label'   => __( '图标大小', 'developer-starter' ),
                'default' => '48px',
            ),
            array(
                'id'      => 'sc_icon_bg',
                'type'    => 'text',
                'label'   => __( '图标背景颜色(支持渐变)', 'developer-starter' ),
                'default' => 'linear-gradient(135deg, var(--color-primary-light) 0%, var(--qiling-color-10b981) 100%)',
            ),
            array(
                'id'      => 'sc_icon_radius',
                'type'    => 'text',
                'label'   => __( '图标圆角', 'developer-starter' ),
                'default' => '12px',
            ),
            
            // === 标题标签设置 ===
            array(
                'id'      => 'sc_title_color',
                'type'    => 'text',
                'label'   => __( '标题颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-800)',
            ),
            array(
                'id'      => 'sc_badge_bg',
                'type'    => 'text',
                'label'   => __( '标签按钮渐变背景', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随页面预设/全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'sc_badge_text',
                'type'    => 'color',
                'label'   => __( '标签文字颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'sc_desc_color',
                'type'    => 'text',
                'label'   => __( '描述文字颜色', 'developer-starter' ),
                'default' => 'var(--color-text-muted)',
            ),
            
            // === 卡片内容 ===
            array(
                'id'         => 'sc_cards',
                'type'       => 'repeater',
                'label'      => __( '服务卡片', 'developer-starter' ),
                'add_button' => __( '添加卡片', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'icon',
                        'type'  => 'text',
                        'label' => __( '图标(emoji或Symbol类名)', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'icon_image',
                        'type'  => 'image',
                        'label' => __( '图标图片(优先使用，留空则用上方图标)', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'badge',
                        'type'  => 'text',
                        'label' => __( '标签文字(如: 优惠、热门、留空不显示)', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'description',
                        'type'  => 'text',
                        'label' => __( '描述', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'label' => __( '链接地址', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'target',
                        'type'    => 'select',
                        'label'   => __( '打开方式', 'developer-starter' ),
                        'options' => array(
                            '_self'  => __( '当前窗口', 'developer-starter' ),
                            '_blank' => __( '新窗口', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
        );
    }

    public function render( $data = array() ) {
        // 获取配置
        $section_title = isset( $data['sc_title'] ) ? (string) $data['sc_title'] : '';
        $section_subtitle = isset( $data['sc_subtitle'] ) ? (string) $data['sc_subtitle'] : '';
        $heading_color = isset( $data['sc_heading_color'] ) ? (string) $data['sc_heading_color'] : '';
        $subtitle_color = isset( $data['sc_subtitle_color'] ) ? (string) $data['sc_subtitle_color'] : '';
        $bg_color = isset( $data['sc_bg_color'] ) ? $data['sc_bg_color'] : '';
        $padding = isset( $data['sc_padding'] ) ? intval( $data['sc_padding'] ) : 60;
        $columns = isset( $data['sc_columns'] ) ? intval( $data['sc_columns'] ) : 4;
        $gap = isset( $data['sc_gap'] ) ? $data['sc_gap'] : '20px';
        
        $card_bg = isset( $data['sc_card_bg'] ) ? $data['sc_card_bg'] : 'var(--qiling-color-rgba-255-255-255-09)';
        $card_radius = isset( $data['sc_card_radius'] ) ? $data['sc_card_radius'] : '12px';
        $card_shadow = isset( $data['sc_card_shadow'] ) ? $data['sc_card_shadow'] : 'medium';
        $card_border = isset( $data['sc_card_border'] ) ? $data['sc_card_border'] : '';
        
        $icon_size = isset( $data['sc_icon_size'] ) ? $data['sc_icon_size'] : '48px';
        $icon_bg = isset( $data['sc_icon_bg'] ) ? $data['sc_icon_bg'] : '';
        $icon_radius = isset( $data['sc_icon_radius'] ) ? $data['sc_icon_radius'] : '12px';
        
        $title_color = isset( $data['sc_title_color'] ) ? $data['sc_title_color'] : 'var(--color-neutral-800)';
        $badge_bg = isset( $data['sc_badge_bg'] ) && $data['sc_badge_bg'] !== '' ? $data['sc_badge_bg'] : 'var(--qiling-component-badge-bg)';
        $badge_text = isset( $data['sc_badge_text'] ) ? $data['sc_badge_text'] : '';
        $desc_color = isset( $data['sc_desc_color'] ) ? $data['sc_desc_color'] : 'var(--color-text-muted)';
        
        $cards = isset( $data['sc_cards'] ) ? $data['sc_cards'] : array();
        
        $module_id = 'sc-' . uniqid();
        
        // 背景样式
        $section_style = "padding: {$padding}px 0;";
        if ( $bg_color ) {
            $bg_value = strpos( $bg_color, 'gradient' ) !== false ? $bg_color : $bg_color;
            $section_style .= "--sc-bg: {$bg_value};";
        }
        
        // 阴影样式映射
        $shadow_map = array(
            'none'   => 'none',
            'light'  => '0 2px 8px var(--qiling-color-rgba-0-0-0-006)',
            'medium' => '0 4px 16px var(--qiling-color-rgba-0-0-0-01)',
            'strong' => '0 8px 30px var(--qiling-color-rgba-0-0-0-015)',
        );
        $box_shadow = isset( $shadow_map[ $card_shadow ] ) ? $shadow_map[ $card_shadow ] : $shadow_map['medium'];
        ?>
        <section class="module module-service-cards" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( '' !== $section_title || '' !== $section_subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( '' !== $section_title ) : ?>
                            <h2 class="section-title"<?php echo '' !== $heading_color ? ' style="color:' . esc_attr( $heading_color ) . ';"' : ''; ?>><?php echo esc_html( $section_title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( '' !== $section_subtitle ) : ?>
                            <p class="section-subtitle"<?php echo '' !== $subtitle_color ? ' style="color:' . esc_attr( $subtitle_color ) . ';"' : ''; ?>><?php echo esc_html( $section_subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ( ! empty( $cards ) ) : ?>
                    <div class="sc-grid" style="display: grid; grid-template-columns: repeat(<?php echo $columns; ?>, minmax(0, 1fr)); gap: <?php echo esc_attr( $gap ); ?>;">
                        <?php foreach ( $cards as $card ) : ?>
                            <?php if ( ! empty( $card['title'] ) ) : ?>
                                <a href="<?php echo esc_url( $card['url'] ?: '#' ); ?>" 
                                   target="<?php echo esc_attr( $card['target'] ?: '_self' ); ?>"
                                   class="sc-card"
                                   style="background: <?php echo esc_attr( $card_bg ); ?>; border-radius: <?php echo esc_attr( $card_radius ); ?>; box-shadow: <?php echo $box_shadow; ?>; <?php echo $card_border ? 'border: ' . esc_attr( $card_border ) . ';' : ''; ?>">
                                    <div class="sc-icon" style="width: <?php echo esc_attr( $icon_size ); ?>; height: <?php echo esc_attr( $icon_size ); ?>; border-radius: <?php echo esc_attr( $icon_radius ); ?>; background: <?php echo esc_attr( $icon_bg ); ?>;">
                                        <?php if ( ! empty( $card['icon_image'] ) ) : ?>
                                            <img src="<?php echo esc_url( $card['icon_image'] ); ?>" alt="">
                                        <?php elseif ( ! empty( $card['icon'] ) ) : ?>
                                            <?php echo developer_starter_get_icon_html( trim( $card['icon'] ) ); ?>
                                        <?php else : ?>
                                            <span class="sc-icon-emoji">📦</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="sc-content">
                                        <div class="sc-title-row">
                                            <span class="sc-title" style="color: <?php echo esc_attr( $title_color ); ?>;">
                                                <?php echo esc_html( $card['title'] ); ?>
                                            </span>
                                            <?php if ( ! empty( $card['badge'] ) ) : ?>
                                                <span class="sc-badge" style="background: <?php echo esc_attr( $badge_bg ); ?>;<?php echo '' !== $badge_text ? 'color:' . esc_attr( $badge_text ) . ';' : ''; ?>">
                                                    <?php echo esc_html( $card['badge'] ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ( ! empty( $card['description'] ) ) : ?>
                                            <p class="sc-desc" style="color: <?php echo esc_attr( $desc_color ); ?>;">
                                                <?php echo esc_html( $card['description'] ); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="sc-empty" style="text-align: center; padding: var(--qiling-space-60) var(--qiling-space-20); background: var(--color-neutral-50); border-radius: 12px;">
                        <span style="font-size: var(--qiling-text-rem-3); display: block; margin-bottom: var(--qiling-space-16);">📦</span>
                        <p style="color: var(--color-text-muted);"><?php esc_html_e( '请添加服务卡片', 'developer-starter' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
<?php
    }
}
