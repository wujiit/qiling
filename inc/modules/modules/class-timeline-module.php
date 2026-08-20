<?php
/**
 * Timeline Module - 时间轴
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Timeline_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-backup';
        $this->description = __( '发展历程展示', 'developer-starter' );
    }

    public function get_id() {
        return 'timeline';
    }

    public function get_name() {
        return __( '时间轴', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'timeline_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '发展历程', 'Company Timeline' ) : __( '发展历程', 'developer-starter' ) ),
            array(
                'id' => 'timeline_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'timeline_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            
            array( 'id' => 'timeline_items', 'type' => 'repeater', 'label' => __( '时间节点', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'year', 'type' => 'text', 'label' => __( '年份/时间点', 'developer-starter' ) ),
                array( 'id' => 'title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
                array( 'id' => 'desc', 'type' => 'textarea', 'label' => __( '描述', 'developer-starter' ) ),
            ) ),
            
            // Style Settings
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
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
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，时间节点将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['timeline_title'] ) && $data['timeline_title'] !== ''
            ? $data['timeline_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '发展历程', 'Company Timeline' ) : __( '发展历程', 'developer-starter' ) );
        $items = isset( $data['timeline_items'] ) ? $data['timeline_items'] : array();
        
        // Typography
        $title_size = isset( $data['timeline_title_size'] ) ? $data['timeline_title_size'] : '';
        $title_color = isset( $data['timeline_title_color'] ) ? $data['timeline_title_color'] : '';
        
        // Background & Spacing
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        if ( empty( $items ) ) {
            $items = array(
                array( 'year' => '2020', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '公司成立', 'Company Founded' ) : __( '公司成立', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '正式成立，开始创业之旅', 'The team launched with a clear product vision and first clients.' ) : __( '正式成立，开始创业之旅', 'developer-starter' ) ),
                array( 'year' => '2021', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '业务扩展', 'Team Expansion' ) : __( '业务扩展', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '团队规模扩大至50人', 'Operations expanded and the team grew to support more projects.' ) : __( '团队规模扩大至50人', 'developer-starter' ) ),
                array( 'year' => '2022', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品升级', 'Product Upgrade' ) : __( '产品升级', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '发布2.0版本产品', 'A major release introduced a more polished and flexible platform.' ) : __( '发布2.0版本产品', 'developer-starter' ) ),
                array( 'year' => '2023', 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全国布局', 'Wider Reach' ) : __( '全国布局', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '业务覆盖全国20个省市', 'The business reached a broader market footprint and more industries.' ) : __( '业务覆盖全国20个省市', 'developer-starter' ) ),
            );
        }
        
        // Dynamic Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-timeline" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title ) : ?>
                    <div class="section-header text-center">
                        <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    </div>
                <?php endif; ?>
                
                <div class="timeline-container">
                    <div class="timeline-line"></div>
                    <?php foreach ( $items as $i => $item ) : 
                        $year = isset( $item['year'] ) ? $item['year'] : '';
                        $item_title = isset( $item['title'] ) ? $item['title'] : '';
                        $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                        $is_left = $i % 2 === 0;
                        $item_class = $is_left ? 'timeline-left' : 'timeline-right';
                        
                        // Calculate Staggered Animation
                        $anim_attr = '';
                        if ( $enable_anim === 'yes' ) {
                            $anim_attr = $this->get_staggered_animation_attr( $i );
                        }
                    ?>
                        <div class="timeline-item <?php echo esc_attr( $item_class ); ?>" <?php echo $anim_attr; ?>>
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-year"><?php echo esc_html( $year ); ?></div>
                                <h3 class="timeline-title"><?php echo esc_html( $item_title ); ?></h3>
                                <p class="timeline-desc"><?php echo esc_html( $desc ); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
