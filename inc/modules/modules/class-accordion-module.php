<?php
/**
 * Accordion Module - 手风琴折叠
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Accordion_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-list-view';
        $this->description = __( '可折叠的手风琴内容', 'developer-starter' );
    }

    public function get_id() {
        return 'accordion';
    }

    public function get_name() {
        return __( '手风琴', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'accordion_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
            array( 'id' => 'accordion_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            
            // Typography Settings
            array(
                'id' => 'accordion_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'accordion_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'accordion_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'accordion_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            
            array( 'id' => 'accordion_style', 'type' => 'select', 'label' => __( '样式', 'developer-starter' ), 'options' => array(
                'default' => __( '默认', 'developer-starter' ),
                'bordered' => __( '带边框', 'developer-starter' ),
                'minimal' => __( '极简', 'developer-starter' ),
            ), 'default' => 'default' ),
            array( 'id' => 'accordion_multiple', 'type' => 'select', 'label' => __( '允许多开', 'developer-starter' ), 'options' => array( '' => __( '否', 'developer-starter' ), '1' => __( '是', 'developer-starter' ) ) ),
            array( 'id' => 'accordion_first_open', 'type' => 'select', 'label' => __( '默认展开第一项', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            array( 'id' => 'accordion_items', 'type' => 'repeater', 'label' => __( '折叠项', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
                array( 'id' => 'content', 'type' => 'textarea', 'label' => __( '内容', 'developer-starter' ) ),
                array( 'id' => 'icon', 'type' => 'text', 'label' => __( '图标 (Emoji 或 Symbol类名)', 'developer-starter' ) ),
            ) ),
            
            // Background Settings
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
                'default' => '60px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['accordion_title'] ) ? $data['accordion_title'] : '';
        $subtitle = isset( $data['accordion_subtitle'] ) ? $data['accordion_subtitle'] : '';
        $style = isset( $data['accordion_style'] ) ? $data['accordion_style'] : 'default';
        $allow_multiple = isset( $data['accordion_multiple'] ) ? $data['accordion_multiple'] : '';
        $first_open = isset( $data['accordion_first_open'] ) ? $data['accordion_first_open'] : '1';
        $items = isset( $data['accordion_items'] ) ? $data['accordion_items'] : array();
        
        // 默认示例数据
        if ( empty( $items ) ) {
            $items = array(
                array( 'title' => __( '产品质量如何保证？', 'developer-starter' ), 'content' => __( '我们拥有完善的质量管理体系，通过ISO9001认证。每件产品都经过严格的质检流程，确保出厂产品100%合格。如有任何质量问题，我们提供无条件退换货服务。', 'developer-starter' ), 'icon' => '🛡️' ),
                array( 'title' => __( '配送范围和时效？', 'developer-starter' ), 'content' => __( '我们支持全国配送，一二线城市1-3天送达，其他地区3-7天送达。部分地区支持当日达服务，下单时可查看具体配送时效。', 'developer-starter' ), 'icon' => '🚚' ),
                array( 'title' => __( '售后服务政策？', 'developer-starter' ), 'content' => __( '我们提供7x24小时在线客服支持，产品享有1 year 质保期。质保期内非人为损坏可免费维修或更换。质保期外提供有偿维修服务。', 'developer-starter' ), 'icon' => '💬' ),
            );
        }
        
        // Typography Logic
        $title_size = isset( $data['accordion_title_size'] ) ? $data['accordion_title_size'] : '';
        $title_color = isset( $data['accordion_title_color'] ) ? $data['accordion_title_color'] : '';
        $subtitle_size = isset( $data['accordion_subtitle_size'] ) ? $data['accordion_subtitle_size'] : '';
        $subtitle_color = isset( $data['accordion_subtitle_color'] ) ? $data['accordion_subtitle_color'] : '';

        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        
        // 兼容旧版标题颜色
        if ( empty( $title_color ) && isset( $data['accordion_title_color_old'] ) ) {
             $title_color = $data['accordion_title_color_old'];
        }
        if ( $title_color ) $title_style .= "color: {$title_color};";

        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        // Background Logic
        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        // 兼容旧版背景
        if ( empty( $bg_color ) && isset( $data['accordion_bg_color'] ) ) {
            $bg_color = $data['accordion_bg_color'];
        }

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        $accordion_id = 'accordion-' . uniqid();
        ?>
        <section class="module module-accordion" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            
            <div class="container">
                <?php if ( $title ) : ?>
                    <div class="section-header text-center">
                        <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo wp_kses_post( $title ); ?></h2>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo wp_kses_post( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <div id="<?php echo esc_attr( $accordion_id ); ?>" class="accordion-wrapper accordion-style-<?php echo esc_attr( $style ); ?>" data-multiple="<?php echo esc_attr( $allow_multiple ); ?>">
                        <?php foreach ( $items as $index => $item ) : 
                            $item_title = isset( $item['title'] ) ? $item['title'] : '';
                            $content = isset( $item['content'] ) ? $item['content'] : '';
                            $icon = isset( $item['icon'] ) ? $item['icon'] : '';
                            $is_open = ( $first_open === '1' && $index === 0 );
                        ?>
                            <div class="accordion-item <?php echo $is_open ? 'active' : ''; ?>">
                                <div class="accordion-header">
                                    <?php if ( $icon ) : ?>
                                        <span class="accordion-header-icon">
                                            <?php echo developer_starter_get_icon_html( $icon ); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="accordion-title"><?php echo wp_kses_post( $item_title ); ?></span>
                                    <span class="accordion-toggle-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="accordion-content" style="<?php echo $is_open ? 'max-height: 500px; padding-bottom: var(--qiling-space-25);' : ''; ?>">
                                    <div class="accordion-inner">
                                        <?php echo wp_kses_post( $content ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <script>
        (function() {
            var id = '<?php echo esc_js( $accordion_id ); ?>';
            var wrapper = document.getElementById(id);
            if (!wrapper) return;
            
            var allowMultiple = wrapper.getAttribute('data-multiple') === '1';
            var headers = wrapper.querySelectorAll('.accordion-header');
            
            headers.forEach(function(header) {
                header.addEventListener('click', function() {
                    var item = this.closest('.accordion-item');
                    var isActive = item.classList.contains('active');
                    // var icon = this.querySelector('.accordion-toggle-icon'); // SVG handled by CSS now
                    var content = item.querySelector('.accordion-content');
                    
                    if (!allowMultiple) {
                        // 关闭其他
                        wrapper.querySelectorAll('.accordion-item.active').forEach(function(activeItem) {
                            if (activeItem !== item) {
                                activeItem.classList.remove('active');
                                activeItem.querySelector('.accordion-content').style.maxHeight = '0';
                                activeItem.querySelector('.accordion-content').style.paddingBottom = '0';
                            }
                        });
                    }
                    
                    if (isActive) {
                        item.classList.remove('active');
                        content.style.maxHeight = '0';
                        content.style.paddingBottom = '0';
                    } else {
                        item.classList.add('active');
                        content.style.maxHeight = content.scrollHeight + 'px';
                        content.style.paddingBottom = '25px';
                    }
                });
            });
        })();
        </script>
        <?php
    }
}
