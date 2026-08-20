<?php
/**
 * 功能清单列表模块
 *
 * Tab标签切换的功能清单展示模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Features_List_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-list-view';
        $this->description = __( 'Tab标签切换的功能清单，展示产品功能特性', 'developer-starter' );
    }

    public function get_id() {
        return 'features_list';
    }

    public function get_name() {
        return __( '功能清单列表', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => __( '产品功能', 'developer-starter' ) ),
            array(
                'id' => 'title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array(
                'id' => 'subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'columns', 'type' => 'select', 'label' => __( '列数', 'developer-starter' ), 'options' => array( '2' => __( '2列', 'developer-starter' ), '3' => __( '3列', 'developer-starter' ), '4' => __( '4列', 'developer-starter' ) ), 'default' => '3' ),
            array( 'id' => 'tabs', 'type' => 'repeater', 'label' => __( 'Tab标签页', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'tab_id', 'type' => 'text', 'label' => __( 'Tab ID (唯一标识)', 'developer-starter' ), 'default' => 'tab1' ),
                array( 'id' => 'tab_title', 'type' => 'text', 'label' => __( 'Tab标题', 'developer-starter' ) ),
                array( 'id' => 'tab_icon', 'type' => 'text', 'label' => __( 'Tab图标 (可选)', 'developer-starter' ) ),
                array( 'id' => 'features', 'type' => 'textarea', 'label' => __( '功能列表 (每行格式: 图标|标题|描述)', 'developer-starter' ), 'rows' => 6 ),
            ) ),
            
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
            array( 'id' => 'fl_tabs_bg', 'label' => __( '标签栏背景', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'fl_tab_text', 'label' => __( '标签文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'fl_tab_active_bg', 'label' => __( '激活标签背景', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'fl_tab_active_text', 'label' => __( '激活标签文字', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'fl_card_bg', 'label' => __( '功能卡片背景', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'fl_card_border', 'label' => __( '功能卡片边框', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'fl_item_title', 'label' => __( '功能标题颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'fl_item_desc', 'label' => __( '功能描述颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'fl_icon_bg', 'label' => __( '功能图标背景', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'fl_icon_color', 'label' => __( '功能图标颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            
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
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['title'] ) ? $data['title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品功能', 'Product Features' ) : __( '产品功能', 'developer-starter' ) );
        $subtitle = isset( $data['subtitle'] ) ? $data['subtitle'] : '';
        $columns = isset( $data['columns'] ) ? $data['columns'] : '3';
        $tabs = isset( $data['tabs'] ) ? $data['tabs'] : array();
        
        // Typography
        $title_color = isset( $data['title_color'] ) ? $data['title_color'] : '';
        $title_size = isset( $data['title_size'] ) ? $data['title_size'] : '';
        $subtitle_color = isset( $data['subtitle_color'] ) ? $data['subtitle_color'] : '';
        $subtitle_size = isset( $data['subtitle_size'] ) ? $data['subtitle_size'] : '';
        
        // Background
        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        
        // Legacy support for simple bg_color if new field is empty but old one exists (optional, but requested "complete custom settings" implies new fields take precedence)
        // If module_bg_color is empty but 'bg_color' (old field) is set, use it.
        if ( empty( $bg_color ) && isset( $data['bg_color'] ) && ! empty( $data['bg_color'] ) ) {
            $bg_color = $data['bg_color'];
        }

        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        
        // Padding
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '80px';
        
        if ( empty( $tabs ) ) {
            return;
        }
        
        // 解析功能清单数据
        $parsed_tabs = array();
        foreach ( $tabs as $tab ) {
            $parsed_tab = $tab;
            if ( isset( $tab['features'] ) && is_string( $tab['features'] ) ) {
                $features_text = $tab['features'];
                $lines = explode( "\n", $features_text );
                $parsed_tab['features'] = array();
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( empty( $line ) ) continue;
                    $parts = explode( '|', $line );
                    if ( count( $parts ) >= 3 ) {
                        $parsed_tab['features'][] = array(
                            'icon' => trim( $parts[0] ),
                            'title' => trim( $parts[1] ),
                            'desc' => trim( $parts[2] ),
                        );
                    }
                }
            }
            $parsed_tabs[] = $parsed_tab;
        }
        $tabs = $parsed_tabs;
        
        $unique_id = 'features-list-' . uniqid();
        
        // Dynamic Style Construction
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        foreach ( array( 'fl_tabs_bg' => '--fl-tabs-bg', 'fl_tab_text' => '--fl-tab-text', 'fl_tab_active_bg' => '--fl-tab-active-bg', 'fl_tab_active_text' => '--fl-tab-active-text', 'fl_card_bg' => '--fl-card-bg', 'fl_card_border' => '--fl-card-border', 'fl_item_title' => '--fl-item-title', 'fl_item_desc' => '--fl-item-desc', 'fl_icon_bg' => '--fl-icon-bg', 'fl_icon_color' => '--fl-icon-color' ) as $field => $variable ) {
            if ( ! empty( $data[ $field ] ) ) $section_style .= $variable . ':' . $data[ $field ] . ';';
        }
        
        // Title Style
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        // Subtitle Style
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        ?>
        
        <section class="module module-features-list" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            
            <div class="container module-features-list-container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center" data-aos="fade-up">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="features-list-wrapper" data-aos="fade-up" data-aos-delay="100">
                    <div class="features-tabs">
                        <?php foreach ( $tabs as $index => $tab ) : ?>
                            <button 
                                class="features-tab-btn<?php echo $index === 0 ? ' active' : ''; ?>" 
                                data-tab="<?php echo esc_attr( $tab['tab_id'] ); ?>"
                                data-target="<?php echo esc_attr( $unique_id ); ?>">
                                <?php if ( ! empty( $tab['tab_icon'] ) ) : ?>
                                    <span class="tab-icon"><?php echo developer_starter_get_icon_html( $tab['tab_icon'] ); ?></span>
                                <?php endif; ?>
                                <span class="tab-text"><?php echo esc_html( $tab['tab_title'] ); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="features-tabs-content">
                        <?php foreach ( $tabs as $index => $tab ) : ?>
                            <div 
                                class="features-tab-pane<?php echo $index === 0 ? ' active' : ''; ?>" 
                                data-tab-content="<?php echo esc_attr( $tab['tab_id'] ); ?>">
                                
                                <?php if ( ! empty( $tab['features'] ) ) : ?>
                                    <div class="features-grid features-grid-<?php echo esc_attr( $columns ); ?>">
                                        <?php foreach ( $tab['features'] as $feature ) : ?>
                                            <div class="feature-card">
                                                <?php if ( ! empty( $feature['icon'] ) ) : ?>
                                                    <div class="feature-icon">
                                                        <?php echo developer_starter_get_icon_html( $feature['icon'] ); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ( ! empty( $feature['title'] ) ) : ?>
                                                    <h3 class="feature-title"><?php echo esc_html( $feature['title'] ); ?></h3>
                                                <?php endif; ?>
                                                
                                                <?php if ( ! empty( $feature['desc'] ) ) : ?>
                                                    <p class="feature-desc"><?php echo esc_html( $feature['desc'] ); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        
        <?php
        $this->enqueue_tab_script();
    }
    
    private function enqueue_tab_script() {
        static $script_added = false;
        if ( $script_added ) return;
        $script_added = true;
        ?>
        <script>
        (function() {
            if (window.qilingFeaturesListDelegated) return;
            window.qilingFeaturesListDelegated = true;

            document.addEventListener('click', function(event) {
                const button = event.target.closest('.module-features-list .features-tab-btn');
                if (!button) return;

                const container = button.closest('.module-features-list');
                if (!container) return;

                container.querySelectorAll('.features-tab-btn').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                container.querySelectorAll('.features-tab-pane').forEach(function(pane) {
                    pane.classList.remove('active');
                });

                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                const targetPane = Array.prototype.find.call(
                    container.querySelectorAll('[data-tab-content]'),
                    function(pane) { return pane.getAttribute('data-tab-content') === tabId; }
                );
                if (targetPane) targetPane.classList.add('active');
            });
        })();
        </script>
        <?php
    }
}
