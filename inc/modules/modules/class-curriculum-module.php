<?php
/**
 * Curriculum Module - 课程/日程表
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Curriculum_Module extends Module_Base {

    public function __construct() {
        $this->category = 'education'; // 归类为教育
        $this->icon = 'dashicons-welcome-learn-more';
        $this->description = __( '展示课程大纲、活动日程或时间安排表', 'developer-starter' );
    }

    public function get_id() {
        return 'curriculum';
    }

    public function get_name() {
        return __( '课程/日程表', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id' => 'curriculum_title',
                'label' => __( '模块标题', 'developer-starter' ),
                'type' => 'text',
                'default' => __( '课程大纲', 'developer-starter' ),
            ),
            array(
                'id' => 'curriculum_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'text',
                'default' => __( '由浅入深，系统掌握核心知识', 'developer-starter' ),
            ),
            array(
                'id' => 'curriculum_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id' => 'curriculum_primary_color',
                'label' => __( '主色调 (图标/高亮)', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-primary)',
            ),
            array(
                'id'          => 'curriculum_badge_bg',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'type'        => 'color',
                'default'     => '',
                'description' => __( '控制章节元信息标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            
            // Repeater
            array(
                'id' => 'curriculum_items',
                'label' => __( '章节/日程列表', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'title', 'label' => __( '章节标题', 'developer-starter' ), 'type' => 'text', 'placeholder' => __( '第一章：基础入门', 'developer-starter' ) ),
                    array( 'id' => 'meta', 'label' => __( '元信息 (如时长/讲师)', 'developer-starter' ), 'type' => 'text', 'placeholder' => __( '45分钟 | 张老师', 'developer-starter' ) ),
                    array( 'id' => 'content', 'label' => __( '详细内容 (支持HTML)', 'developer-starter' ), 'type' => 'textarea', 'rows' => 3, 'placeholder' => __( '本章将介绍...', 'developer-starter' ) ),
                    array( 'id' => 'open', 'label' => __( '默认展开', 'developer-starter' ), 'type' => 'select', 'options' => array( 'no' => __( '否', 'developer-starter' ), 'yes' => __( '是', 'developer-starter' ) ) )
                ),
                'default_items' => array(
                    array( 'title' => __( 'Day 1: 签到与开幕式', 'developer-starter' ), 'meta' => '09:00 - 12:00', 'content' => __( '<p>领取会议资料，参观展区，聆听开幕主题演讲。</p>', 'developer-starter' ), 'open' => 'yes' ),
                    array( 'title' => __( 'Day 1: 行业高峰论坛', 'developer-starter' ), 'meta' => '14:00 - 17:00', 'content' => __( '<p>邀请行业领袖进行圆桌讨论，探讨未来发展趋势。</p>', 'developer-starter' ), 'open' => 'no' ),
                    array( 'title' => __( 'Day 2: 专题研讨会', 'developer-starter' ), 'meta' => '09:00 - 11:30', 'content' => __( '<p>分组进行技术、市场、运营三个方向的深度研讨。</p>', 'developer-starter' ), 'open' => 'no' ),
                ),
            ),
            
             // Spacing
            array(
                'id' => 'module_margin_top',
                'label' => __( '上间距', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'module_margin_bottom',
                'label' => __( '下间距', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
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
                'description' => __( '开启后，课程章节将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['curriculum_title'] ) ? $data['curriculum_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '课程大纲', 'Course Outline' ) : __( '课程大纲', 'developer-starter' ) );
        $subtitle = isset( $data['curriculum_subtitle'] ) ? $data['curriculum_subtitle'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '由浅入深，系统掌握核心知识', 'A structured path to build skills step by step.' ) : __( '由浅入深，系统掌握核心知识', 'developer-starter' ) );
        $bg_color = isset( $data['curriculum_bg_color'] ) ? $data['curriculum_bg_color'] : 'var(--color-neutral-50)';
        $primary_color = isset( $data['curriculum_primary_color'] ) ? $data['curriculum_primary_color'] : 'var(--color-primary)';
        $badge_bg = isset( $data['curriculum_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['curriculum_badge_bg'] ) ) : '';
        $badge_bg = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $items = isset( $data['curriculum_items'] ) ? $data['curriculum_items'] : array();
        
        $style_vars = "background-color: {$bg_color};";

        $style_vars .= "--curr-primary: {$primary_color};";
        if ( '' !== $badge_bg ) {
            $style_vars .= "--qiling-component-badge-bg: {$badge_bg};";
        }
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-curriculum" style="<?php echo esc_attr( $style_vars ); ?>">
            <div class="container" style="max-width: var(--qiling-measure-800);"> <!-- Limit width for better readability -->
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"><?php echo wp_kses_post( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="qiling-accordion">
                        <?php foreach ( $items as $index => $item ) : 
                            $c_title = isset( $item['title'] ) ? $item['title'] : '';
                            $c_meta = isset( $item['meta'] ) ? $item['meta'] : '';
                            $c_content = isset( $item['content'] ) ? $item['content'] : '';
                            $is_open = isset( $item['open'] ) && $item['open'] === 'yes';
                            
                            $item_class = 'qiling-accordion-item';
                            if ( $is_open ) $item_class .= ' is-open';

                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="<?php echo esc_attr( $item_class ); ?>" <?php echo $anim_attr; ?>>
                                <div class="qiling-accordion-header" onclick="this.parentNode.classList.toggle('is-open')">
                                    <div class="qiling-accordion-info">
                                        <h3 class="qiling-accordion-title"><?php echo esc_html( $c_title ); ?></h3>
                                        <?php if ( $c_meta ) : ?>
                                            <span class="qiling-accordion-meta"><?php echo esc_html( $c_meta ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="qiling-accordion-icon"></span>
                                </div>
                                <div class="qiling-accordion-content">
                                    <div class="qiling-accordion-body">
                                        <?php echo wp_kses_post( $c_content ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
