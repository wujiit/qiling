<?php
/**
 * Multi Image Text Module - 多图文模块
 *
 * 鼠标悬停切换图片的交互式图文展示模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Multi_Image_Text_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-images-alt2';
        $this->description = __( '多图文悬停切换模块', 'developer-starter' );
    }

    public function get_id() {
        return 'multi_image_text';
    }

    public function get_name() {
        return __( '多图文模块', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'multi_image_text_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
            array(
                'id' => 'multi_image_text_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'multi_image_text_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'multi_image_text_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array(
                'id' => 'multi_image_text_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'multi_image_text_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'multi_image_text_layout', 'type' => 'select', 'label' => __( '布局方向', 'developer-starter' ), 'options' => array( 'left' => __( '左图右文', 'developer-starter' ), 'right' => __( '右图左文', 'developer-starter' ) ), 'default' => 'left' ),
            
            array( 'id' => 'module_bg_color', 'type' => 'color', 'label' => __( '背景颜色', 'developer-starter' ), 'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ) ),
            
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
            
            array( 'id' => 'multi_image_text_item_title_size', 'type' => 'text', 'label' => __( '子项标题大小', 'developer-starter' ), 'default' => '1.25rem' ),
            array( 'id' => 'multi_image_text_items', 'type' => 'repeater', 'label' => __( '图文列表', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'icon', 'type' => 'text', 'label' => __( '图标 (Emoji 或 Symbol类名)', 'developer-starter' ) ),
                array( 'id' => 'title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
                array( 'id' => 'title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
                array( 'id' => 'desc', 'type' => 'textarea', 'label' => __( '描述', 'developer-starter' ) ),
                array( 'id' => 'desc_color', 'type' => 'color', 'label' => __( '描述颜色', 'developer-starter' ) ),
                array( 'id' => 'image', 'type' => 'image', 'label' => __( '对应图片', 'developer-starter' ) ),
                array( 'id' => 'link', 'type' => 'text', 'label' => __( '链接', 'developer-starter' ) ),
            ) ),
        );
    }

    public function render( $data = array() ) {
        // 获取模块配置
        $title = isset( $data['multi_image_text_title'] ) && $data['multi_image_text_title'] !== '' 
            ? $data['multi_image_text_title'] : '';
        $subtitle = isset( $data['multi_image_text_subtitle'] ) ? $data['multi_image_text_subtitle'] : '';
        $layout = isset( $data['multi_image_text_layout'] ) ? $data['multi_image_text_layout'] : 'left';
        
        // Typography
        $title_color = isset( $data['multi_image_text_title_color'] ) ? $data['multi_image_text_title_color'] : '';
        $title_size = isset( $data['multi_image_text_title_size'] ) ? $data['multi_image_text_title_size'] : '';
        $subtitle_color = isset( $data['multi_image_text_subtitle_color'] ) ? $data['multi_image_text_subtitle_color'] : '';
        $subtitle_size = isset( $data['multi_image_text_subtitle_size'] ) ? $data['multi_image_text_subtitle_size'] : '';
        
        // Background
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        // Legacy support
        if ( empty( $bg_color ) && isset( $data['multi_image_text_bg_color'] ) ) {
            $bg_color = $data['multi_image_text_bg_color'];
        }
        
        // Padding
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '80px';
        
        $item_title_size = isset( $data['multi_image_text_item_title_size'] ) && ! empty( $data['multi_image_text_item_title_size'] ) 
            ? $data['multi_image_text_item_title_size'] : '1.25rem';
        $items = isset( $data['multi_image_text_items'] ) ? $data['multi_image_text_items'] : array();
        
        // 默认数据
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'icon'  => '🚀',
                    'title' => __( '快速部署', 'developer-starter' ),
                    'desc'  => __( '采用自动化部署流程，5分钟即可完成系统上线，大幅降低运维成本和时间投入。', 'developer-starter' ),
                    'image' => '',
                    'link'  => '',
                ),
                array(
                    'icon'  => '🛡️',
                    'title' => __( '安全可靠', 'developer-starter' ),
                    'desc'  => __( '企业级安全架构，多层防护机制，数据加密存储，确保您的业务数据安全无虞。', 'developer-starter' ),
                    'image' => '',
                    'link'  => '',
                ),
                array(
                    'icon'  => '📊',
                    'title' => __( '数据分析', 'developer-starter' ),
                    'desc'  => __( '强大的数据分析引擎，实时监控业务指标，智能报表助力精准决策。', 'developer-starter' ),
                    'image' => '',
                    'link'  => '',
                ),
            );
        }
        
        // 生成唯一ID
        $module_id = 'mit-' . uniqid();
        
        // 背景样式
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( ! empty( $bg_color ) ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false 
                ? "background: {$bg_color};" 
                : "background-color: {$bg_color};";
        }
        
        // 标题样式
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        // 副标题样式
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        $container_class = 'mit-container';
        if ( $layout === 'right' ) {
            $container_class .= ' layout-right';
        }
        ?>
        <section class="module module-multi-image-text" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center" style="margin-bottom: var(--qiling-space-50);">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="<?php echo esc_attr( $container_class ); ?>">
                    <!-- 图片区域 -->
                    <div class="mit-image-area">
                        <div class="mit-image-wrapper">
                            <?php foreach ( $items as $index => $item ) : 
                                $item_image = isset( $item['image'] ) && ! empty( $item['image'] ) ? $item['image'] : '';
                            ?>
                                <div class="mit-image <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                    <?php if ( $item_image ) : ?>
                                        <img src="<?php echo esc_url( $item_image ); ?>" alt="" class="mit-img" />
                                    <?php else : ?>
                                        <div class="mit-placeholder" style="background: linear-gradient(135deg, <?php echo $this->get_gradient_color( $index ); ?>);">
                                            <span class="mit-placeholder-icon">
                                                <?php 
                                                $icon_ph = isset( $item['icon'] ) ? $item['icon'] : '📷';
                                                echo developer_starter_get_icon_html( $icon_ph ); 
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 文字列表区域 -->
                    <div class="mit-text-area">
                        <?php foreach ( $items as $index => $item ) : 
                            $icon_raw = isset( $item['icon'] ) ? trim( $item['icon'] ) : '';
                            $item_title = isset( $item['title'] ) ? $item['title'] : '';
                            $item_desc = isset( $item['desc'] ) ? $item['desc'] : '';
                            $item_link = isset( $item['link'] ) ? $item['link'] : '';
                            $item_title_color = isset( $item['title_color'] ) && ! empty( $item['title_color'] ) ? $item['title_color'] : '';
                            $item_desc_color = isset( $item['desc_color'] ) && ! empty( $item['desc_color'] ) ? $item['desc_color'] : '';
                            
                            $icon = trim( $icon_raw );
                            
                            // Item Styles
                            $item_title_style = "font-size: {$item_title_size};";
                            if ( $item_title_color ) $item_title_style .= "color: {$item_title_color};";
                            
                            $item_desc_style = '';
                            if ( $item_desc_color ) $item_desc_style .= "color: {$item_desc_color};";
                        ?>
                            <div class="mit-item <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                <div class="mit-item-inner">
                                    <?php if ( $icon ) : ?>
                                        <div class="mit-icon">
                                            <?php echo developer_starter_get_icon_html( $icon ); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mit-content">
                                        <?php if ( $item_title ) : ?>
                                            <h3 class="mit-title"<?php echo $item_title_style ? ' style="' . esc_attr( $item_title_style ) . '"' : ''; ?>>
                                                <?php if ( $item_link && $item_link !== '#' ) : ?>
                                                    <a href="<?php echo esc_url( $item_link ); ?>"><?php echo esc_html( $item_title ); ?></a>
                                                <?php else : ?>
                                                    <?php echo esc_html( $item_title ); ?>
                                                <?php endif; ?>
                                            </h3>
                                        <?php endif; ?>
                                        
                                        <?php if ( $item_desc ) : ?>
                                            <p class="mit-desc"<?php echo $item_desc_style ? ' style="' . esc_attr( $item_desc_style ) . '"' : ''; ?>><?php echo esc_html( $item_desc ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        
        <script>
        (function() {
            var container = document.getElementById('<?php echo esc_js( $module_id ); ?>');
            if (!container) return;
            
            var items = container.querySelectorAll('.mit-item');
            var images = container.querySelectorAll('.mit-image');
            
            items.forEach(function(item) {
                item.addEventListener('mouseenter', function() {
                    var index = this.getAttribute('data-index');
                    
                    // Update text items
                    items.forEach(function(i) {
                        i.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Update images
                    images.forEach(function(img) {
                        if (img.getAttribute('data-index') === index) {
                            img.classList.add('active');
                        } else {
                            img.classList.remove('active');
                        }
                    });
                });
            });
        })();
        </script>
        <?php
    }
    
    /**
     * 获取渐变色
     */
    private function get_gradient_color( $index ) {
        $gradients = array(
            'var(--color-primary) 0%, var(--qiling-color-764ba2) 100%',
            'var(--color-accent) 0%, var(--color-error) 100%',
            'var(--color-primary-light) 0%, var(--color-info) 100%',
            'var(--color-success) 0%, var(--color-info) 100%',
            'var(--color-error) 0%, var(--color-warning) 100%',
            'var(--color-info) 0%, var(--qiling-color-error-alpha-01) 100%',
        );
        return $gradients[ $index % count( $gradients ) ];
    }
}
