<?php
/**
 * Experience Timeline Module - 经历时间线模块
 *
 * 专为个人简历设计的教育/工作经历展示，垂直单列布局
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Experience_Timeline_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-welcome-learn-more';
        $this->description = __( '教育/工作经历展示，适合个人简历', 'developer-starter' );
    }

    public function get_id() {
        return 'experience_timeline';
    }

    public function get_name() {
        return __( '经历时间线', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // === 基础设置 ===
            array(
                'id'      => 'exp_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
            ),
            array(
                'id'      => 'exp_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id'      => 'exp_title_color',
                'type'    => 'color',
                'label'   => __( '标题颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'exp_subtitle',
                'type'    => 'text',
                'label'   => __( '模块副标题', 'developer-starter' ),
            ),
            array(
                'id'      => 'exp_subtitle_size',
                'type'    => 'text',
                'label'   => __( '副标题字体大小', 'developer-starter' ),
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id'      => 'exp_subtitle_color',
                'type'    => 'color',
                'label'   => __( '副标题颜色', 'developer-starter' ),
                'default' => '',
            ),
            
            // === 背景设置 ===
            array(
                'id'      => 'exp_bg_type',
                'type'    => 'select',
                'label'   => __( '背景类型', 'developer-starter' ),
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id'      => 'exp_bg_color',
                'type'    => 'text',
                'label'   => __( '背景颜色(支持渐变)', 'developer-starter' ),
                'default' => '',
                'description' => __( '如 var(--color-neutral-50) 或 linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)', 'developer-starter' ),
                'dependency' => array( 'exp_bg_type', '==', 'color' ),
            ),
            array(
                'id'      => 'exp_bg_image',
                'type'    => 'image',
                'label'   => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'exp_bg_type', '==', 'image' ),
            ),
            array(
                'id'      => 'exp_bg_overlay',
                'type'    => 'select',
                'label'   => __( '背景遮罩浓度', 'developer-starter' ),
                'options' => array(
                    '0'   => __( '无遮罩', 'developer-starter' ),
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
                'dependency' => array( 'exp_bg_type', '==', 'image' ),
            ),
            
            // === 间距设置 ===
            array(
                'id'      => 'exp_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距 (如 60px)', 'developer-starter' ),
                'default' => '60px',
            ),
            array(
                'id'      => 'exp_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距 (如 60px)', 'developer-starter' ),
                'default' => '60px',
            ),
            
            // === 布局设置 ===
            array(
                'id'      => 'exp_layout',
                'type'    => 'select',
                'label'   => __( '布局模式', 'developer-starter' ),
                'options' => array(
                    'single' => __( '单列 (仅显示第1组)', 'developer-starter' ),
                    'double' => __( '双列 (显示2组)', 'developer-starter' ),
                ),
                'default' => 'single',
            ),
            array(
                'id'      => 'exp_line_color',
                'type'    => 'color',
                'label'   => __( '时间线/高亮颜色', 'developer-starter' ),
                'default' => 'var(--color-primary-light)',
            ),
            array(
                'id'          => 'exp_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '控制时间段徽章与经历标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'exp_dot_style',
                'type'    => 'select',
                'label'   => __( '节点样式', 'developer-starter' ),
                'options' => array(
                    'dot'   => __( '实心圆点', 'developer-starter' ),
                    'ring'  => __( '空心圆环', 'developer-starter' ),
                    'icon'  => __( '图标节点', 'developer-starter' ),
                ),
                'default' => 'dot',
            ),
            
            // === 经历分组 1 ===
            array(
                'id'      => 'exp_group1_title',
                'type'    => 'text',
                'label'   => __( '分组1 标题 (如: 教育经历)', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '教育经历', 'Education' ) : __( '教育经历', 'developer-starter' ),
            ),
            array(
                'id'      => 'exp_group1_icon',
                'type'    => 'text',
                'label'   => __( '分组1 图标 (Emoji 或 icon-xxx)', 'developer-starter' ),
                'default' => '🎓',
            ),
            array(
                'id'         => 'exp_group1',
                'type'       => 'repeater',
                'label'      => __( '分组1 内容列表', 'developer-starter' ),
                'add_button' => __( '添加经历', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'period',
                        'type'  => 'text',
                        'label' => __( '时间段 (如: 2016 - 2020)', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '主标题/学位/职位', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'company',
                        'type'  => 'text',
                        'label' => __( '副标题/学校/公司', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'company_link',
                        'type'  => 'text',
                        'label' => __( '链接URL', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'textarea',
                        'label' => __( '详细描述', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'tags',
                        'type'  => 'text',
                        'label' => __( '标签 (逗号分隔)', 'developer-starter' ),
                    ),
                ),
            ),
            
            // === 经历分组 2 ===
            array(
                'id'      => 'exp_group2_title',
                'type'    => 'text',
                'label'   => __( '分组2 标题 (如: 工作经验)', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '工作经验', 'Experience' ) : __( '工作经验', 'developer-starter' ),
            ),
            array(
                'id'      => 'exp_group2_icon',
                'type'    => 'text',
                'label'   => __( '分组2 图标 (Emoji 或 icon-xxx)', 'developer-starter' ),
                'default' => '💼',
            ),
            array(
                'id'         => 'exp_group2',
                'type'       => 'repeater',
                'label'      => __( '分组2 内容列表', 'developer-starter' ),
                'add_button' => __( '添加经历', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'period',
                        'type'  => 'text',
                        'label' => __( '时间段', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '职位/标题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'company',
                        'type'  => 'text',
                        'label' => __( '公司/副标题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'company_link',
                        'type'  => 'text',
                        'label' => __( '链接URL', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'textarea',
                        'label' => __( '详细描述', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'tags',
                        'type'  => 'text',
                        'label' => __( '标签 (逗号分隔)', 'developer-starter' ),
                    ),
                ),
            ),
        );
    }

    public function render( $data = array() ) {
        // 基础配置
        $title = isset( $data['exp_title'] ) ? $data['exp_title'] : '';
        $subtitle = isset( $data['exp_subtitle'] ) ? $data['exp_subtitle'] : '';
        
        // 标题/副标题样式
        $title_size = isset( $data['exp_title_size'] ) && $data['exp_title_size'] ? $data['exp_title_size'] : '';
        $title_color = isset( $data['exp_title_color'] ) && $data['exp_title_color'] ? $data['exp_title_color'] : '';
        $subtitle_size = isset( $data['exp_subtitle_size'] ) && $data['exp_subtitle_size'] ? $data['exp_subtitle_size'] : '';
        $subtitle_color = isset( $data['exp_subtitle_color'] ) && $data['exp_subtitle_color'] ? $data['exp_subtitle_color'] : '';
        
        // 背景配置
        $bg_type = isset( $data['exp_bg_type'] ) ? $data['exp_bg_type'] : 'color';
        $bg_color = isset( $data['exp_bg_color'] ) && $data['exp_bg_color'] ? $data['exp_bg_color'] : '';
        $bg_image = isset( $data['exp_bg_image'] ) && $data['exp_bg_image'] ? $data['exp_bg_image'] : '';
        $bg_overlay = isset( $data['exp_bg_overlay'] ) ? $data['exp_bg_overlay'] : '0';
        
        // 间距配置
        $pt = isset( $data['exp_padding_top'] ) && $data['exp_padding_top'] !== '' ? $data['exp_padding_top'] : '60px';
        $pb = isset( $data['exp_padding_bottom'] ) && $data['exp_padding_bottom'] !== '' ? $data['exp_padding_bottom'] : '60px';
        
        // 布局配置
        $layout = isset( $data['exp_layout'] ) ? $data['exp_layout'] : 'single';
        $line_color = isset( $data['exp_line_color'] ) && $data['exp_line_color'] ? $data['exp_line_color'] : 'var(--color-primary, var(--color-primary-light))';
        $badge_bg = isset( $data['exp_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['exp_badge_bg'] ) ) : '';
        $badge_bg = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $dot_style = isset( $data['exp_dot_style'] ) ? $data['exp_dot_style'] : 'dot';
        
        // 经历分组1（教育经历）
        $group1_title = isset( $data['exp_group1_title'] ) ? $data['exp_group1_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '教育经历', 'Education' ) : __( '教育经历', 'developer-starter' ) );
        $group1_icon = isset( $data['exp_group1_icon'] ) ? $data['exp_group1_icon'] : '🎓';
        $experiences1 = isset( $data['exp_group1'] ) && is_array( $data['exp_group1'] ) ? $data['exp_group1'] : array();
        
        // 经历分组2（工作经验）
        $group2_title = isset( $data['exp_group2_title'] ) ? $data['exp_group2_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '工作经验', 'Experience' ) : __( '工作经验', 'developer-starter' ) );
        $group2_icon = isset( $data['exp_group2_icon'] ) ? $data['exp_group2_icon'] : '💼';
        $experiences2 = isset( $data['exp_group2'] ) && is_array( $data['exp_group2'] ) ? $data['exp_group2'] : array();
        
        $unique_id = 'exp-timeline-' . uniqid();
        
        // 动态样式（仅包含用户自定义的部分）
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        if ( '' !== $badge_bg ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};";
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
        <section class="module module-experience-timeline" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            <div class="container exp-timeline-container">
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
                
                <div class="exp-wrapper <?php echo esc_attr( 'layout-' . $layout ); ?>">
                    <?php if ( ! empty( $experiences1 ) || $group1_title ) : ?>
                        <div class="exp-group">
                            <?php if ( $group1_title ) : ?>
                                <h3 class="exp-group-title">
                                    <span class="exp-group-icon">
                                        <?php echo developer_starter_get_icon_html( $group1_icon ); ?>
                                    </span>
                                    <?php echo esc_html( $group1_title ); ?>
                                </h3>
                            <?php endif; ?>
                            <div class="exp-list" style="--exp-line-color: <?php echo esc_attr( $line_color ); ?>;">
                                <?php $this->render_experiences( $experiences1, $dot_style, $line_color, 'education' ); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $layout === 'double' && ( ! empty( $experiences2 ) || $group2_title ) ) : ?>
                        <div class="exp-group">
                            <?php if ( $group2_title ) : ?>
                                <h3 class="exp-group-title">
                                    <span class="exp-group-icon">
                                        <?php echo developer_starter_get_icon_html( $group2_icon ); ?>
                                    </span>
                                    <?php echo esc_html( $group2_title ); ?>
                                </h3>
                            <?php endif; ?>
                            <div class="exp-list" style="--exp-line-color: <?php echo esc_attr( $line_color ); ?>;">
                                <?php $this->render_experiences( $experiences2, $dot_style, $line_color, 'work' ); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }
    
    /**
     * 渲染经历列表
     */
    private function render_experiences( $experiences, $dot_style, $line_color, $type = 'work' ) {
        if ( empty( $experiences ) ) {
            // 默认数据
            if ( $type === 'education' ) {
                $experiences = array(
                    array( 
                        'period' => '2016 - 2020', 
                        'title' => __( '计算机科学与技术 学士', 'developer-starter' ), 
                        'company' => __( '清华大学', 'developer-starter' ),
                        'company_link' => '',
                        'desc' => __( '主修软件工程 direction，GPA 3.8/4.0，获得优秀毕业生称号', 'developer-starter' ),
                        'tags' => __( '数据结构,算法,软件工程', 'developer-starter' )
                    ),
                    array( 
                        'period' => '2020 - 2022', 
                        'title' => __( '软件工程 硕士', 'developer-starter' ), 
                        'company' => __( '北京大学', 'developer-starter' ),
                        'company_link' => '',
                        'desc' => __( '专注于Web前端技术研究，发表SCI论文2篇', 'developer-starter' ),
                        'tags' => __( '前端架构,性能优化', 'developer-starter' )
                    ),
                );
            } else {
                $experiences = array(
                    array( 
                        'period' => '2022 - ' . __( '至今', 'developer-starter' ), 
                        'title' => __( '高级前端工程师', 'developer-starter' ), 
                        'company' => __( '字节跳动', 'developer-starter' ),
                        'company_link' => 'https://bytedance.com',
                        'desc' => __( '负责抖音Web端核心业务开发，带领5人团队完成多个重点项目', 'developer-starter' ),
                        'tags' => 'React,TypeScript,Node.js'
                    ),
                    array( 
                        'period' => '2020 - 2022', 
                        'title' => __( '前端开发工程师', 'developer-starter' ), 
                        'company' => __( '阿里巴巴', 'developer-starter' ),
                        'company_link' => 'https://alibaba.com',
                        'desc' => __( '参与淘宝商家后台开发，优化页面性能，加载速度提升40%', 'developer-starter' ),
                        'tags' => __( 'Vue,Webpack,小程序', 'developer-starter' )
                    ),
                );
            }
        }
        
        foreach ( $experiences as $exp ) :
            $period = isset( $exp['period'] ) ? $exp['period'] : '';
            $exp_title = isset( $exp['title'] ) ? $exp['title'] : '';
            $company = isset( $exp['company'] ) ? $exp['company'] : '';
            $company_link = isset( $exp['company_link'] ) ? $exp['company_link'] : '';
            $desc = isset( $exp['desc'] ) ? $exp['desc'] : '';
            $tags = isset( $exp['tags'] ) ? $exp['tags'] : '';
            
            if ( empty( $exp_title ) && empty( $company ) ) continue;
            
            $tag_list = array_filter( array_map( 'trim', explode( ',', $tags ) ) );
        ?>
            <div class="exp-item">
                <div class="exp-dot <?php echo esc_attr( 'style-' . $dot_style ); ?>">
                    <?php if ( $dot_style === 'icon' ) : ?>
                        <?php echo $type === 'education' ? '🎓' : '💼'; ?>
                    <?php endif; ?>
                </div>
                <div class="exp-content">
                    <div class="exp-header">
                        <?php if ( $period ) : ?>
                            <span class="exp-period">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <?php echo esc_html( $period ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $exp_title ) : ?>
                        <h4 class="exp-title"><?php echo esc_html( $exp_title ); ?></h4>
                    <?php endif; ?>
                    <?php if ( $company ) : ?>
                        <p class="exp-company">
                            <?php if ( $company_link ) : ?>
                                <a href="<?php echo esc_url( $company_link ); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html( $company ); ?> →
                                </a>
                            <?php else : ?>
                                <?php echo esc_html( $company ); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $desc ) : ?>
                        <p class="exp-desc"><?php echo esc_html( $desc ); ?></p>
                    <?php endif; ?>
                    <?php if ( ! empty( $tag_list ) ) : ?>
                        <div class="exp-tags">
                            <?php foreach ( $tag_list as $tag ) : ?>
                                <span class="exp-tag"><?php echo esc_html( $tag ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php
        endforeach;
    }
}
