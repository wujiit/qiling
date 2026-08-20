<?php
/**
 * Process Module - 合作流程
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Process_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-randomize';
        $this->description = __( '展示合作流程步骤', 'developer-starter' );
    }

    public function get_id() {
        return 'process';
    }

    public function get_name() {
        return __( '合作流程', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'process_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '合作流程', 'Our Process' ) : __( '合作流程', 'developer-starter' ) ),
            array( 'id' => 'process_title_size', 'type' => 'text', 'label' => __( '标题字体大小', 'developer-starter' ), 'desc' => __( '如 2rem 或 32px', 'developer-starter' ) ),
            array( 'id' => 'process_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'process_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array( 'id' => 'process_subtitle_size', 'type' => 'text', 'label' => __( '副标题字体大小', 'developer-starter' ), 'desc' => __( '如 1rem 或 16px', 'developer-starter' ) ),
            array( 'id' => 'process_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ) ),
            array(
                'id'      => 'process_mode',
                'type'    => 'select',
                'label'   => __( '流程样式', 'developer-starter' ),
                'options' => array(
                    'standard'   => __( '标准流程', 'developer-starter' ),
                    'industrial' => __( '工业流程', 'developer-starter' ),
                ),
                'default' => 'standard',
            ),
            
            array( 'id' => 'process_desc_color', 'type' => 'color', 'label' => __( '步骤描述文字颜色', 'developer-starter' ) ),
            array( 'id' => 'process_card_bg', 'type' => 'text', 'label' => __( '步骤卡片背景', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'process_card_border', 'type' => 'color', 'label' => __( '步骤卡片边框', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'process_card_hover_border', 'type' => 'color', 'label' => __( '卡片悬停边框', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'process_arrow_color', 'type' => 'color', 'label' => __( '步骤连接线颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'process_icon_color', 'type' => 'color', 'label' => __( '步骤图标文字颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'process_badge_text', 'type' => 'color', 'label' => __( '阶段标签文字颜色', 'developer-starter' ), 'default' => '' ),
            array(
                'id'      => 'process_badge_bg',
                'type'    => 'color',
                'label'   => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '控制阶段标签和工业流程信息徽章，留空时跟随页面预设/全局徽章颜色。', 'developer-starter' ),
            ),
            
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
                'default' => '60px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),

            array( 'id' => 'process_items', 'type' => 'repeater', 'label' => __( '流程步骤', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'icon', 'type' => 'text', 'label' => __( '图标(emoji或Symbol类名)', 'developer-starter' ) ),
                array( 'id' => 'title', 'type' => 'text', 'label' => __( '步骤标题', 'developer-starter' ) ),
                array( 'id' => 'stage_tag', 'type' => 'text', 'label' => __( '阶段标签(可选)', 'developer-starter' ) ),
                array( 'id' => 'title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
                array( 'id' => 'desc', 'type' => 'textarea', 'label' => __( '描述', 'developer-starter' ) ),
                array( 'id' => 'duration', 'type' => 'text', 'label' => __( '预计周期(可选)', 'developer-starter' ) ),
                array( 'id' => 'metric', 'type' => 'text', 'label' => __( '产能/指标(可选)', 'developer-starter' ) ),
                array( 'id' => 'quality_checkpoint', 'type' => 'text', 'label' => __( '质检点(可选)', 'developer-starter' ) ),
                array( 'id' => 'deliverable', 'type' => 'text', 'label' => __( '交付物(可选)', 'developer-starter' ) ),
                array( 'id' => 'image', 'type' => 'image', 'label' => __( '工序图片(工业流程推荐)', 'developer-starter' ) ),
                array( 'id' => 'icon_bg', 'type' => 'color', 'label' => __( '图标背景色', 'developer-starter' ) ),
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
                'description' => __( '开启后，流程步骤将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        $title = isset( $data['process_title'] ) && $data['process_title'] !== ''
            ? $data['process_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '合作流程', 'Our Process' ) : __( '合作流程', 'developer-starter' ) );
        $title_size = isset( $data['process_title_size'] ) && $data['process_title_size'] !== '' ? $data['process_title_size'] : '';
        $title_color = isset( $data['process_title_color'] ) && ! empty( $data['process_title_color'] ) ? $data['process_title_color'] : '';
        
        $subtitle = isset( $data['process_subtitle'] )
            ? $data['process_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '简单四步，开启合作之旅', 'Four clear steps to launch the collaboration.' ) : __( '简单四步，开启合作之旅', 'developer-starter' ) );
        $subtitle_size = isset( $data['process_subtitle_size'] ) && $data['process_subtitle_size'] !== '' ? $data['process_subtitle_size'] : '';
        $subtitle_color = isset( $data['process_subtitle_color'] ) && ! empty( $data['process_subtitle_color'] ) ? $data['process_subtitle_color'] : '';
        
        $desc_color = isset( $data['process_desc_color'] ) && ! empty( $data['process_desc_color'] ) ? $data['process_desc_color'] : '';
        $badge_bg = isset( $data['process_badge_bg'] ) ? $clean_css_value( $data['process_badge_bg'] ) : '';
        $process_mode = isset( $data['process_mode'] ) && in_array( $data['process_mode'], array( 'standard', 'industrial' ), true ) ? $data['process_mode'] : 'standard';
        
        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        // Fallback for old field if exists (though strictly following new structure here)
        if ( empty( $bg_color ) && isset( $data['process_bg_color'] ) ) {
             $bg_color = $data['process_bg_color'];
        }
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        $items = isset( $data['process_items'] ) ? $data['process_items'] : array();
        
        // 默认示例数据
        if ( empty( $items ) ) {
            $items = array(
                array( 
                    'icon' => '01', 
                    'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '需求沟通', 'Discovery Call' ) : __( '需求沟通', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '深入了解您的业务需求和目标。', 'Understand your goals, audience, and project requirements.' ) : __( '深入了解您的业务需求和目标。', 'developer-starter' ),
                    'icon_bg' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)'
                ),
                array( 
                    'icon' => '02', 
                    'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '方案设计', 'Solution Planning' ) : __( '方案设计', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '根据需求制定专属解决方案。', 'Shape a tailored plan with structure, milestones, and deliverables.' ) : __( '根据需求制定专属解决方案。', 'developer-starter' ),
                    'icon_bg' => 'linear-gradient(135deg, var(--color-accent) 0%, var(--color-error) 100%)'
                ),
                array( 
                    'icon' => '03', 
                    'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '开发实施', 'Production' ) : __( '开发实施', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业团队高效执行项目开发。', 'Build and refine the project with efficient execution and reviews.' ) : __( '专业团队高效执行项目开发。', 'developer-starter' ),
                    'icon_bg' => 'linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-info) 100%)'
                ),
                array( 
                    'icon' => '04', 
                    'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '交付上线', 'Launch & Support' ) : __( '交付上线', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '严格测试后交付，并提供持续支持。', 'Launch after testing and keep the experience supported over time.' ) : __( '严格测试后交付，并提供持续支持。', 'developer-starter' ),
                    'icon_bg' => 'linear-gradient(135deg, var(--color-success) 0%, var(--color-info) 100%)'
                ),
            );
        }
        
        // Section Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_type === 'image' && ! empty( $bg_image ) ) {
            $section_style .= "position: relative; background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        if ( $badge_bg ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};";
        }
        foreach ( array( 'process_card_bg' => '--process-card-bg', 'process_card_border' => '--process-card-border', 'process_card_hover_border' => '--process-card-hover-border', 'process_arrow_color' => '--process-arrow', 'process_icon_color' => '--process-icon-color', 'process_badge_text' => '--process-badge-text' ) as $field => $variable ) {
            if ( ! empty( $data[ $field ] ) ) $section_style .= $variable . ':' . $clean_css_value( $data[ $field ] ) . ';';
        }
        
        // Typography Styles
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        $desc_style_global = '';
        if ( $desc_color ) $desc_style_global = "color: {$desc_color};";
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        $get_media_url = function( $value ) {
            if ( empty( $value ) ) {
                return '';
            }
            if ( function_exists( 'developer_starter_get_media_url' ) ) {
                $resolved = developer_starter_get_media_url( $value );
                if ( ! empty( $resolved ) ) {
                    return $resolved;
                }
            }
            if ( is_numeric( $value ) ) {
                $url = wp_get_attachment_url( (int) $value );
                if ( $url ) {
                    return $url;
                }
            }
            if ( is_array( $value ) && ! empty( $value['url'] ) ) {
                return (string) $value['url'];
            }
            return (string) $value;
        };
        ?>
        <section class="module module-process process-mode-<?php echo esc_attr( $process_mode ); ?> bg-type-<?php echo esc_attr( $bg_type ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            
            <div class="container" style="position: relative; z-index: 2;">
                <div class="section-header text-center">
                    <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>"><?php echo esc_html( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle" style="<?php echo esc_attr( $subtitle_style ); ?>"><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="process-grid">
                        <?php 
                        $total = count( $items );
                        foreach ( $items as $index => $item ) : 
                            $icon_raw = isset( $item['icon'] ) ? trim( $item['icon'] ) : sprintf( '%02d', $index + 1 );
                            $item_title = isset( $item['title'] ) ? $item['title'] : '';
                            $stage_tag = isset( $item['stage_tag'] ) ? $item['stage_tag'] : '';
                            $item_title_color = isset( $item['title_color'] ) && ! empty( $item['title_color'] ) ? $item['title_color'] : '';
                            $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                            $duration = isset( $item['duration'] ) ? $item['duration'] : '';
                            $metric = isset( $item['metric'] ) ? $item['metric'] : '';
                            $quality_checkpoint = isset( $item['quality_checkpoint'] ) ? $item['quality_checkpoint'] : '';
                            $deliverable = isset( $item['deliverable'] ) ? $item['deliverable'] : '';
                            $step_image = $get_media_url( isset( $item['image'] ) ? $item['image'] : '' );
                            $icon_bg = isset( $item['icon_bg'] ) && ! empty( $item['icon_bg'] ) ? $item['icon_bg'] : 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%)';
                            $is_last = ( $index === $total - 1 );
                            
                            
                            $icon = trim( $icon_raw );
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                            ?>
                            <div class="process-item" <?php echo $anim_attr; ?>>
                                <?php if ( $process_mode === 'industrial' && $step_image ) : ?>
                                    <div class="process-image-wrap">
                                        <img src="<?php echo esc_url( $step_image ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" class="process-step-image" loading="lazy" />
                                    </div>
                                <?php endif; ?>

                                <div class="process-icon" style="background: <?php echo esc_attr( $icon_bg ); ?>;">
                                    <?php echo developer_starter_get_icon_html( $icon ); ?>
                                </div>
                                
                                <!-- 箭头连接线（非最后一个） -->
                                <?php if ( ! $is_last ) : ?>
                                    <div class="process-arrow">
                                        <div></div>
                                    </div>
                                <?php endif; ?>

                                <?php if ( $stage_tag ) : ?>
                                    <div class="process-stage-tag"><?php echo esc_html( $stage_tag ); ?></div>
                                <?php endif; ?>
                                
                                <!-- 标题 -->
                                <?php 
                                $item_title_style = ! empty( $item_title_color ) ? "color: {$item_title_color};" : '';
                                ?>
                                <h3 class="process-title" style="<?php echo esc_attr( $item_title_style ); ?>">
                                    <?php echo esc_html( $item_title ); ?>
                                </h3>
                                
                                <!-- 描述 -->
                                <p class="process-desc" style="<?php echo esc_attr( $desc_style_global ); ?>">
                                    <?php echo esc_html( $desc ); ?>
                                </p>

                                <?php if ( $process_mode === 'industrial' && ( $duration || $metric || $quality_checkpoint || $deliverable ) ) : ?>
                                    <div class="process-meta">
                                        <?php if ( $duration ) : ?><span class="process-chip"><?php esc_html_e( '周期', 'developer-starter' ); ?>: <?php echo esc_html( $duration ); ?></span><?php endif; ?>
                                        <?php if ( $metric ) : ?><span class="process-chip"><?php esc_html_e( '指标', 'developer-starter' ); ?>: <?php echo esc_html( $metric ); ?></span><?php endif; ?>
                                        <?php if ( $quality_checkpoint ) : ?><span class="process-chip"><?php esc_html_e( '质检', 'developer-starter' ); ?>: <?php echo esc_html( $quality_checkpoint ); ?></span><?php endif; ?>
                                        <?php if ( $deliverable ) : ?><span class="process-chip"><?php esc_html_e( '交付', 'developer-starter' ); ?>: <?php echo esc_html( $deliverable ); ?></span><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
