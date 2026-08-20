<?php
/**
 * Skills Module - 技能进度条模块
 *
 * 用于展示专业技能，支持进度条样式和分组显示
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Skills_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-chart-bar';
        $this->description = __( '技能进度条展示，适合简历和作品集网站', 'developer-starter' );
    }

    public function get_id() {
        return 'skills';
    }

    public function get_name() {
        return __( '技能进度条', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // === 基础设置 ===
            array( 'id' => 'skills_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => __( '专业技能', 'developer-starter' ) ),
            array( 'id' => 'skills_title_size', 'type' => 'text', 'label' => __( '标题字体大小', 'developer-starter' ), 'default' => '', 'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ) ),
            array( 'id' => 'skills_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'skills_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array( 'id' => 'skills_subtitle_size', 'type' => 'text', 'label' => __( '副标题字体大小', 'developer-starter' ), 'default' => '', 'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ) ),
            array( 'id' => 'skills_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ), 'default' => '' ),
            
            // === 背景设置 ===
            array( 
                'id' => 'skills_bg_type', 
                'type' => 'select', 
                'label' => __( '背景类型', 'developer-starter' ), 
                'options' => array( 'color' => __( '纯色/渐变背景', 'developer-starter' ), 'image' => __( '图片背景', 'developer-starter' ) ), 
                'default' => 'color' 
            ),
            array( 
                'id' => 'skills_bg_color', 
                'type' => 'text', 
                'label' => __( '背景颜色(支持渐变)', 'developer-starter' ), 
                'default' => '',
                'description' => __( '如 var(--color-neutral-50) 或 linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)', 'developer-starter' ),
                'dependency' => array( 'skills_bg_type', '==', 'color' )
            ),
            array( 
                'id' => 'skills_bg_image', 
                'type' => 'image', 
                'label' => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'skills_bg_type', '==', 'image' )
            ),
            array( 
                'id' => 'skills_bg_overlay', 
                'type' => 'select', 
                'label' => __( '背景遮罩浓度', 'developer-starter' ), 
                'options' => array(
                    '0' => __( '无遮罩', 'developer-starter' ), '0.1' => '10%', '0.2' => '20%', '0.3' => '30%', 
                    '0.4' => '40%', '0.5' => '50%', '0.6' => '60%', '0.7' => '70%', 
                    '0.8' => '80%', '0.9' => '90%'
                ), 
                'default' => '0',
                'dependency' => array( 'skills_bg_type', '==', 'image' )
            ),
            
            // === 间距设置 ===
            array( 'id' => 'skills_padding_top', 'type' => 'text', 'label' => __( '上边距 (如 60px)', 'developer-starter' ), 'default' => '60px' ),
            array( 'id' => 'skills_padding_bottom', 'type' => 'text', 'label' => __( '下边距 (如 60px)', 'developer-starter' ), 'default' => '60px' ),
            
            // === 布局设置 ===
            array( 'id' => 'skills_layout', 'type' => 'select', 'label' => __( '布局方式', 'developer-starter' ), 'options' => array( 'single' => __( '单列', 'developer-starter' ), 'double' => __( '双列', 'developer-starter' ) ), 'default' => 'single' ),
            array( 'id' => 'skills_style', 'type' => 'select', 'label' => __( '展示样式', 'developer-starter' ), 'options' => array( 'bar' => __( '进度条', 'developer-starter' ), 'circle' => __( '圆环', 'developer-starter' ) ), 'default' => 'bar' ),
            array( 'id' => 'skills_bar_height', 'type' => 'text', 'label' => __( '进度条高度', 'developer-starter' ), 'default' => '10px' ),
            array( 'id' => 'skills_bar_color', 'type' => 'text', 'label' => __( '进度条颜色/主色', 'developer-starter' ), 'default' => '', 'description' => __( '支持颜色值或渐变，留空时跟随全局主色与强调色', 'developer-starter' ) ),
            array( 'id' => 'skills_bar_bg', 'type' => 'color', 'label' => __( '进度条背景/底色', 'developer-starter' ), 'default' => 'var(--color-neutral-200)' ),
            array( 'id' => 'skills_show_percent', 'type' => 'select', 'label' => __( '显示百分比', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            array( 'id' => 'skills_animate', 'type' => 'select', 'label' => __( '启用动画', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            
            // === 技能分组 ===
            array( 'id' => 'skills_group1_title', 'type' => 'text', 'label' => __( '分组1标题', 'developer-starter' ) ),
            array( 'id' => 'skills_group1', 'type' => 'repeater', 'label' => __( '分组1技能列表', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'name', 'type' => 'text', 'label' => __( '技能名称', 'developer-starter' ) ),
                array( 'id' => 'percent', 'type' => 'number', 'label' => __( '百分比 (0-100)', 'developer-starter' ) ),
            ) ),
            
            array( 'id' => 'skills_group2_title', 'type' => 'text', 'label' => __( '分组2标题 (双列布局有效)', 'developer-starter' ) ),
            array( 'id' => 'skills_group2', 'type' => 'repeater', 'label' => __( '分组2技能列表', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'name', 'type' => 'text', 'label' => __( '技能名称', 'developer-starter' ) ),
                array( 'id' => 'percent', 'type' => 'number', 'label' => __( '百分比 (0-100)', 'developer-starter' ) ),
            ) ),
        );
    }

    public function render( $data = array() ) {
        // 基础配置
        $title = isset( $data['skills_title'] ) ? $data['skills_title'] : '';
        $subtitle = isset( $data['skills_subtitle'] ) ? $data['skills_subtitle'] : '';
        
        // 标题/副标题样式
        $title_size = isset( $data['skills_title_size'] ) && $data['skills_title_size'] ? $data['skills_title_size'] : '';
        $title_color = isset( $data['skills_title_color'] ) && $data['skills_title_color'] ? $data['skills_title_color'] : '';
        $subtitle_size = isset( $data['skills_subtitle_size'] ) && $data['skills_subtitle_size'] ? $data['skills_subtitle_size'] : '';
        $subtitle_color = isset( $data['skills_subtitle_color'] ) && $data['skills_subtitle_color'] ? $data['skills_subtitle_color'] : '';
        
        // 背景配置
        $bg_type = isset( $data['skills_bg_type'] ) ? $data['skills_bg_type'] : 'color';
        $bg_color = isset( $data['skills_bg_color'] ) && $data['skills_bg_color'] ? $data['skills_bg_color'] : '';
        $bg_image = isset( $data['skills_bg_image'] ) && $data['skills_bg_image'] ? $data['skills_bg_image'] : '';
        $bg_overlay = isset( $data['skills_bg_overlay'] ) ? $data['skills_bg_overlay'] : '0';
        
        // 间距配置
        $pt = isset( $data['skills_padding_top'] ) && $data['skills_padding_top'] !== '' ? $data['skills_padding_top'] : '60px';
        $pb = isset( $data['skills_padding_bottom'] ) && $data['skills_padding_bottom'] !== '' ? $data['skills_padding_bottom'] : '60px';
        
        // 布局配置
        $layout = isset( $data['skills_layout'] ) ? $data['skills_layout'] : 'single';
        $style = isset( $data['skills_style'] ) ? $data['skills_style'] : 'bar';
        $bar_height = isset( $data['skills_bar_height'] ) ? $data['skills_bar_height'] : '10px';
        $bar_color = ! empty( $data['skills_bar_color'] ) ? $data['skills_bar_color'] : 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%)';
        $bar_bg = isset( $data['skills_bar_bg'] ) ? $data['skills_bar_bg'] : 'var(--color-neutral-200)';
        $show_percent = isset( $data['skills_show_percent'] ) && $data['skills_show_percent'] === '1';
        $animate = isset( $data['skills_animate'] ) && $data['skills_animate'] === '1';
        
        // 技能分组1
        $group1_title = isset( $data['skills_group1_title'] ) ? $data['skills_group1_title'] : '';
        $skills1 = isset( $data['skills_group1'] ) && is_array( $data['skills_group1'] ) ? $data['skills_group1'] : array();
        
        // 技能分组2
        $group2_title = isset( $data['skills_group2_title'] ) ? $data['skills_group2_title'] : '';
        $skills2 = isset( $data['skills_group2'] ) && is_array( $data['skills_group2'] ) ? $data['skills_group2'] : array();
        
        $unique_id = 'skills-' . uniqid();
        
        // 动态样式（仅包含用户自定义的部分）
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        $section_style .= "--skills-bar-color: {$bar_color}; --skills-bar-bg: {$bar_bg}; --skills-bar-height: {$bar_height};";
        
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        // 标题动态样式
        $title_style = '';
        if ( $title_size ) {
            $title_style .= "font-size: {$title_size};";
        }
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }
        
        // 副标题动态样式
        $subtitle_style = '';
        if ( $subtitle_size ) {
            $subtitle_style .= "font-size: {$subtitle_size};";
        }
        if ( $subtitle_color ) {
            $subtitle_style .= "color: {$subtitle_color};";
        }
        ?>
        <section class="module module-skills" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>" <?php echo $animate ? 'data-animate="1"' : ''; ?>>
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            <div class="container skills-container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="skills-wrapper <?php echo esc_attr( 'layout-' . $layout ); ?>">
                    <?php if ( ! empty( $skills1 ) || $group1_title ) : ?>
                        <div class="skills-group">
                            <?php if ( $group1_title ) : ?>
                                <h3 class="skills-group-title"><?php echo esc_html( $group1_title ); ?></h3>
                            <?php endif; ?>
                            <?php $this->render_skills( $skills1, $style, $bar_height, $bar_color, $bar_bg, $show_percent, $unique_id . '-1' ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $layout === 'double' && ( ! empty( $skills2 ) || $group2_title ) ) : ?>
                        <div class="skills-group">
                            <?php if ( $group2_title ) : ?>
                                <h3 class="skills-group-title"><?php echo esc_html( $group2_title ); ?></h3>
                            <?php endif; ?>
                            <?php $this->render_skills( $skills2, $style, $bar_height, $bar_color, $bar_bg, $show_percent, $unique_id . '-2' ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <?php if ( $animate ) : ?>
        <script>
        (function(){
            function initSkillsAnimation() {
                var section = document.getElementById('<?php echo esc_js( $unique_id ); ?>');
                if(!section) return;
                
                var wrapper = section.querySelector('.skills-wrapper');
                if(!wrapper) return;
                
                // 检查IntersectionObserver是否支持
                if('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if(entry.isIntersecting) {
                                section.classList.add('animated');
                                wrapper.classList.add('animated');
                                observer.unobserve(section);
                            }
                        });
                    }, { threshold: 0.1 });
                    
                    observer.observe(section);
                } else {
                    // 不支持IntersectionObserver时直接添加动画类
                    section.classList.add('animated');
                    wrapper.classList.add('animated');
                }
            }
            
            // 兼容DOMContentLoaded已触发的情况
            if(document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSkillsAnimation);
            } else {
                initSkillsAnimation();
            }
        })();
        </script>
        <?php endif; ?>
        <?php
    }
    
    /**
     * 渲染技能列表
     */
    private function render_skills( $skills, $style, $bar_height, $bar_color, $bar_bg, $show_percent, $gradient_id ) {
        if ( empty( $skills ) ) {
            // 默认技能数据
            $skills = array(
                array( 'name' => 'HTML/CSS', 'percent' => '95' ),
                array( 'name' => 'JavaScript', 'percent' => '85' ),
                array( 'name' => 'React/Vue', 'percent' => '80' ),
                array( 'name' => 'PHP/WordPress', 'percent' => '75' ),
            );
        }
        
        if ( $style === 'circle' ) {
            $this->render_circle_skills( $skills, $show_percent, $gradient_id );
        } else {
            $this->render_bar_skills( $skills, $show_percent );
        }
    }
    
    /**
     * 渲染进度条样式技能
     */
    private function render_bar_skills( $skills, $show_percent ) {
        ?>
        <div class="skills-bars">
            <?php foreach ( $skills as $skill ) : 
                $name = isset( $skill['name'] ) ? $skill['name'] : '';
                $percent = isset( $skill['percent'] ) ? intval( $skill['percent'] ) : 0;
                if ( empty( $name ) ) continue;
            ?>
                <div class="skill-item">
                    <div class="skill-header">
                        <span class="skill-name"><?php echo esc_html( $name ); ?></span>
                        <?php if ( $show_percent ) : ?>
                            <span class="skill-percent"><?php echo esc_html( $percent ); ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="skill-bar-wrap">
                        <div class="skill-bar" style="width: <?php echo esc_attr( $percent ); ?>%; --skill-percent: <?php echo esc_attr( $percent ); ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * 渲染圆环样式技能
     */
    private function render_circle_skills( $skills, $show_percent, $gradient_id ) {
        $circumference = 2 * 3.14159 * 42; // r=42
        ?>
        <svg width="0" height="0" style="position: absolute;">
            <defs>
                <linearGradient id="<?php echo esc_attr( $gradient_id ); ?>" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:var(--color-primary)"/>
                    <stop offset="100%" style="stop-color:var(--color-accent)"/>
                </linearGradient>
            </defs>
        </svg>
        <div class="skills-circles">
            <?php foreach ( $skills as $skill ) : 
                $name = isset( $skill['name'] ) ? $skill['name'] : '';
                $percent = isset( $skill['percent'] ) ? intval( $skill['percent'] ) : 0;
                if ( empty( $name ) ) continue;
                $dashoffset = $circumference - ( $percent / 100 ) * $circumference;
            ?>
                <div class="skill-circle-item">
                    <div class="skill-circle">
                        <svg width="100" height="100" viewBox="0 0 100 100">
                            <circle class="skill-circle-bg" cx="50" cy="50" r="42"/>
                            <circle class="skill-circle-progress" cx="50" cy="50" r="42" style="stroke:url(#<?php echo esc_attr( $gradient_id ); ?>)"
                                    stroke-dasharray="<?php echo esc_attr( $circumference ); ?>" 
                                    stroke-dashoffset="<?php echo esc_attr( $dashoffset ); ?>"/>
                        </svg>
                        <?php if ( $show_percent ) : ?>
                            <span class="skill-circle-text"><?php echo esc_html( $percent ); ?>%</span>
                        <?php endif; ?>
                    </div>
                    <span class="skill-circle-name"><?php echo esc_html( $name ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
