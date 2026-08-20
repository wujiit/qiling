<?php
/**
 * Tabs Module - 标签页切换
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tabs_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-index-card';
        $this->description = __( '多标签页内容切换', 'developer-starter' );
    }

    public function get_id() {
        return 'tabs';
    }

    public function get_name() {
        return __( '标签切换', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'tabs_title', 'label' => __( '标题', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'tabs_subtitle', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            
            // Typography Settings
            array(
                'id' => 'tabs_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'tabs_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'tabs_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'tabs_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            
            array( 'id' => 'tabs_style', 'label' => __( '标签样式', 'developer-starter' ), 'type' => 'select', 'options' => array( 
                'default' => __( '默认样式', 'developer-starter' ), 
                'pills' => __( '胶囊样式', 'developer-starter' ), 
                'underline' => __( '下划线样式', 'developer-starter' ),
                'boxed' => __( '卡片样式', 'developer-starter' ),
            ), 'default' => 'default' ),
            array( 'id' => 'tabs_align', 'label' => __( '标签对齐', 'developer-starter' ), 'type' => 'select', 'options' => array( 
                'left' => __( '左对齐', 'developer-starter' ), 
                'center' => __( '居中', 'developer-starter' ), 
                'right' => __( '右对齐', 'developer-starter' ),
            ), 'default' => 'center' ),
            array(
                'id' => 'tabs_items',
                'label' => __( '标签页', 'developer-starter' ),
                'type' => 'repeater',
                'description' => __( '添加标签页，内容支持HTML', 'developer-starter' ),
                'fields' => array(
                    array( 'id' => 'title', 'label' => __( '标签标题', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'icon', 'label' => __( '图标(emoji或留空)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'content', 'label' => __( '标签内容(支持HTML)', 'developer-starter' ), 'type' => 'textarea' ),
                ),
            ),
            
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
        $title = isset( $data['tabs_title'] ) ? $data['tabs_title'] : '';
        $subtitle = isset( $data['tabs_subtitle'] ) ? $data['tabs_subtitle'] : '';
        $style = isset( $data['tabs_style'] ) ? $data['tabs_style'] : 'default';
        $align = isset( $data['tabs_align'] ) ? $data['tabs_align'] : 'center';
        $items = isset( $data['tabs_items'] ) ? $data['tabs_items'] : array();
        
        // 默认示例数据
        if ( empty( $items ) ) {
            $items = array(
                array( 
                    'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品介绍', 'Overview' ) : __( '产品介绍', 'developer-starter' ),
                    'icon' => '📦',
                    'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '<p>这里是产品介绍的详细内容。您可以在这里添加产品的特点、优势、使用方法等信息。</p><ul><li>特点一：高效稳定</li><li>特点二：易于使用</li><li>特点三：安全可靠</li></ul>', '<p>Use this area to introduce the product, highlight strengths, and explain the core value.</p><ul><li>Benefit one: reliable performance</li><li>Benefit two: easy to use</li><li>Benefit three: safe and dependable</li></ul>' ) : __( '<p>这里是产品介绍的详细内容。您可以在这里添加产品的特点、优势、使用方法等信息。</p><ul><li>特点一：高效稳定</li><li>特点二：易于使用</li><li>特点三：安全可靠</li></ul>', 'developer-starter' ),
                ),
                array( 
                    'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '技术规格', 'Specifications' ) : __( '技术规格', 'developer-starter' ),
                    'icon' => '⚙️',
                    'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '<p>产品的技术参数和规格说明。</p><table style="width:100%;border-collapse:collapse;"><tr><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">尺寸</td><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">100 x 50 x 30 mm</td></tr><tr><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">重量</td><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">500g</td></tr></table>', '<p>Technical specifications and key product details.</p><table style="width:100%;border-collapse:collapse;"><tr><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">Size</td><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">100 x 50 x 30 mm</td></tr><tr><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">Weight</td><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">500g</td></tr></table>' ) : __( '<p>产品的技术参数和规格说明。</p><table style="width:100%;border-collapse:collapse;"><tr><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">尺寸</td><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">100 x 50 x 30 mm</td></tr><tr><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">重量</td><td style="padding:var(--qiling-space-10);border:1px solid var(--color-neutral-200);">500g</td></tr></table>', 'developer-starter' ),
                ),
                array( 
                    'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '使用说明', 'How to Use' ) : __( '使用说明', 'developer-starter' ),
                    'icon' => '📖',
                    'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '<p>产品的使用步骤和注意事项。</p><ol><li>第一步：打开包装</li><li>第二步：阅读说明书</li><li>第三步：按照指引操作</li></ol>', '<p>Setup steps and usage guidance.</p><ol><li>Step 1: unpack the product</li><li>Step 2: review the instructions</li><li>Step 3: follow the setup steps</li></ol>' ) : __( '<p>产品的使用步骤和注意事项。</p><ol><li>第一步：打开包装</li><li>第二步：阅读说明书</li><li>第三步：按照指引操作</li></ol>', 'developer-starter' ),
                ),
            );
        }
        
        // Typography Logic
        $title_size = isset( $data['tabs_title_size'] ) ? $data['tabs_title_size'] : '';
        $title_color = isset( $data['tabs_title_color'] ) ? $data['tabs_title_color'] : '';
        $subtitle_size = isset( $data['tabs_subtitle_size'] ) ? $data['tabs_subtitle_size'] : '';
        $subtitle_color = isset( $data['tabs_subtitle_color'] ) ? $data['tabs_subtitle_color'] : '';

        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        
        // 兼容旧版标题颜色
        if ( empty( $title_color ) && isset( $data['tabs_title_color_old'] ) ) {
             $title_color = $data['tabs_title_color_old'];
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
        if ( empty( $bg_color ) && isset( $data['tabs_bg_color'] ) ) {
            $bg_color = $data['tabs_bg_color'];
        }

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        $tabs_id = 'tabs-' . uniqid();
        
        // 对齐样式
        $align_class = 'align-center';
        if ( $align === 'left' ) $align_class = 'align-left';
        if ( $align === 'right' ) $align_class = 'align-right';
        ?>
        <section class="module module-tabs" style="<?php echo esc_attr( $section_style ); ?>">
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
                    <div id="<?php echo esc_attr( $tabs_id ); ?>" class="tabs-wrapper tabs-style-<?php echo esc_attr( $style ); ?>">
                        <!-- 标签导航 -->
                        <div class="tabs-nav <?php echo esc_attr( $align_class ); ?>">
                            <?php foreach ( $items as $index => $item ) : 
                                $tab_title = isset( $item['title'] ) ? $item['title'] : __( '标签', 'developer-starter' );
                                $icon = isset( $item['icon'] ) ? $item['icon'] : '';
                            ?>
                                <button type="button" class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo esc_attr( $index ); ?>">
                                    <?php if ( $icon ) : ?>
                                        <span><?php echo developer_starter_get_icon_html( $icon ); ?></span>
                                    <?php endif; ?>
                                    <?php echo wp_kses_post( $tab_title ); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- 标签内容 -->
                        <div class="tabs-content">
                            <?php foreach ( $items as $index => $item ) : 
                                $content = isset( $item['content'] ) ? $item['content'] : '';
                            ?>
                                <div class="tab-pane <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo esc_attr( $index ); ?>">
                                    <div class="tab-content-inner">
                                        <?php echo wp_kses_post( $content ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <script>
        (function() {
            var tabsId = '<?php echo esc_js( $tabs_id ); ?>';
            var wrapper = document.getElementById(tabsId);
            if (!wrapper) return;
            
            var btns = wrapper.querySelectorAll('.tab-btn');
            var panes = wrapper.querySelectorAll('.tab-pane');
            
            btns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var tabIndex = this.getAttribute('data-tab');
                    
                    // 更新按钮状态
                    btns.forEach(function(b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // 更新内容显示
                    panes.forEach(function(pane) {
                        if (pane.getAttribute('data-tab') === tabIndex) {
                            pane.classList.add('active');
                        } else {
                            pane.classList.remove('active');
                        }
                    });
                });
            });
        })();
        </script>
        <?php
    }
}
