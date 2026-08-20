<?php
/**
 * Team Module - 团队成员
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Team_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-groups';
        $this->description = __( '展示团队成员/核心人员', 'developer-starter' );
    }

    public function get_id() {
        return 'team';
    }

    public function get_name() {
        return __( '团队成员', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'team_title', 'label' => __( '标题', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '核心团队', 'Our Team' ) : __( '核心团队', 'developer-starter' ) ),
            array(
                'id' => 'team_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'team_title_color', 'label' => __( '标题颜色', 'developer-starter' ), 'type' => 'color' ),
            
            array( 'id' => 'team_subtitle', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业团队，值得信赖', 'Experienced people you can rely on.' ) : __( '专业团队，值得信赖', 'developer-starter' ) ),
            array(
                'id' => 'team_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'team_subtitle_color', 'label' => __( '副标题颜色', 'developer-starter' ), 'type' => 'color' ),
            
            array( 'id' => 'team_columns', 'label' => __( '每行列数', 'developer-starter' ), 'type' => 'select', 'options' => array( '2' => __( '2列', 'developer-starter' ), '3' => __( '3列', 'developer-starter' ), '4' => __( '4列', 'developer-starter' ) ), 'default' => '4' ),
            array(
                'id' => 'team_members',
                'label' => __( '团队成员', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'avatar', 'label' => __( '头像', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'name', 'label' => __( '姓名', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'position', 'label' => __( '职位', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'desc', 'label' => __( '简介', 'developer-starter' ), 'type' => 'textarea' ),
                    array( 'id' => 'wechat', 'label' => __( '微信二维码', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'email', 'label' => __( '邮箱', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'phone', 'label' => __( '电话', 'developer-starter' ), 'type' => 'text' ),
                ),
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
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，团队成员将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['team_title'] ) && $data['team_title'] !== ''
            ? $data['team_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '核心团队', 'Our Team' ) : __( '核心团队', 'developer-starter' ) );
        $subtitle = isset( $data['team_subtitle'] )
            ? $data['team_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业团队，值得信赖', 'Experienced people you can rely on.' ) : __( '专业团队，值得信赖', 'developer-starter' ) );
        
        // Typography
        $title_color = isset( $data['team_title_color'] ) ? $data['team_title_color'] : '';
        $title_size = isset( $data['team_title_size'] ) ? $data['team_title_size'] : '';
        $subtitle_color = isset( $data['team_subtitle_color'] ) ? $data['team_subtitle_color'] : '';
        $subtitle_size = isset( $data['team_subtitle_size'] ) ? $data['team_subtitle_size'] : '';
        
        $columns = isset( $data['team_columns'] ) && ! empty( $data['team_columns'] ) ? intval( $data['team_columns'] ) : 4;
        $members = isset( $data['team_members'] ) ? $data['team_members'] : array();
        
        // Background
        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        
        // Padding
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        // Default Data
        if ( empty( $members ) ) {
            $members = array(
                array( 'avatar' => '', 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '张明', 'Michael Zhang' ) : __( '张明', 'developer-starter' ), 'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '首席执行官', 'Chief Executive Officer' ) : __( '首席执行官', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '20年行业经验，曾任多家知名企业高管。', 'Leads strategy and long-term growth with extensive industry experience.' ) : __( '20年行业经验，曾任多家知名企业高管。', 'developer-starter' ) ),
                array( 'avatar' => '', 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '李华', 'Sophia Lee' ) : __( '李华', 'developer-starter' ), 'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '技术总监', 'Technical Director' ) : __( '技术总监', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '资深技术专家，主导多个大型项目研发。', 'Oversees delivery quality, architecture, and technical execution.' ) : __( '资深技术专家，主导多个大型项目研发。', 'developer-starter' ) ),
                array( 'avatar' => '', 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '王芳', 'Emma Wang' ) : __( '王芳', 'developer-starter' ), 'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '市场总监', 'Marketing Director' ) : __( '市场总监', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '深耕市场营销领域15年，擅长品牌策略。', 'Shapes brand positioning, messaging, and campaign strategy.' ) : __( '深耕市场营销领域15年，擅长品牌策略。', 'developer-starter' ) ),
                array( 'avatar' => '', 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '刘强', 'David Liu' ) : __( '刘强', 'developer-starter' ), 'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '运营总监', 'Operations Director' ) : __( '运营总监', 'developer-starter' ), 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精细化运营专家，打造高效团队管理体系。', 'Keeps workflows efficient and operations aligned across teams.' ) : __( '精细化运营专家，打造高效团队管理体系。', 'developer-starter' ) ),
            );
        }
        
        // Dynamic Style Construction
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        // Title Style
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        // Subtitle Style
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        $grid_class = 'grid-cols-' . $columns;
        
        $avatar_colors = array(
            'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)',
            'linear-gradient(135deg, var(--color-accent) 0%, var(--color-error) 100%)',
            'linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-info) 100%)',
            'linear-gradient(135deg, var(--color-success) 0%, var(--color-info) 100%)',
            'linear-gradient(135deg, var(--color-error) 0%, var(--color-warning) 100%)',
            'linear-gradient(135deg, var(--color-info) 0%, var(--qiling-color-error-alpha-01) 100%)',
        );
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-team" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            
            <div class="container module-team-container">
                <div class="section-header text-center">
                    <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ( ! empty( $members ) ) : ?>
                    <div class="team-grid <?php echo esc_attr( $grid_class ); ?>">
                        <?php foreach ( $members as $index => $member ) : 
                            $avatar = isset( $member['avatar'] ) ? $member['avatar'] : '';
                            $name = isset( $member['name'] ) ? $member['name'] : '';
                            $position = isset( $member['position'] ) ? $member['position'] : '';
                            $desc = isset( $member['desc'] ) ? $member['desc'] : '';
                            $wechat = isset( $member['wechat'] ) ? $member['wechat'] : '';
                            $email = isset( $member['email'] ) ? $member['email'] : '';
                            $phone = isset( $member['phone'] ) ? $member['phone'] : '';
                            $default_avatar_bg = $avatar_colors[ $index % count( $avatar_colors ) ];
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="team-card" <?php echo $anim_attr; ?>>
                                <!-- Avatar -->
                                <div class="team-avatar" style="<?php echo empty( $avatar ) ? "background: {$default_avatar_bg};" : ''; ?>">
                                    <?php if ( $avatar ) : ?>
                                        <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="team-avatar-img" />
                                    <?php else : ?>
                                        <span class="team-avatar-text"><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Name -->
                                <h3 class="team-name"><?php echo esc_html( $name ); ?></h3>
                                
                                <!-- Position -->
                                <?php if ( $position ) : ?>
                                    <p class="team-position"><?php echo esc_html( $position ); ?></p>
                                <?php endif; ?>
                                
                                <!-- Description -->
                                <?php if ( $desc ) : ?>
                                    <p class="team-desc"><?php echo esc_html( $desc ); ?></p>
                                <?php endif; ?>
                                
                                <!-- Contact Info -->
                                <?php if ( $wechat || $email || $phone ) : ?>
                                    <div class="team-social">
                                        <?php if ( $phone ) : ?>
                                            <a href="tel:<?php echo esc_attr( $phone ); ?>" class="team-social-link" title="<?php echo esc_attr( $phone ); ?>">
                                                <svg class="team-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ( $email ) : ?>
                                            <a href="mailto:<?php echo esc_attr( $email ); ?>" class="team-social-link" title="<?php echo esc_attr( $email ); ?>">
                                                <svg class="team-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ( $wechat ) : ?>
                                            <div class="team-wechat-wrap">
                                                <span class="team-social-link">
                                                    <svg class="team-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 01.213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 00.167-.054l1.903-1.114a.864.864 0 01.717-.098 10.16 10.16 0 002.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348z"/></svg>
                                                </span>
                                                <div class="team-wechat-qr">
                                                    <img src="<?php echo esc_url( $wechat ); ?>" alt="<?php esc_attr_e( '微信', 'developer-starter' ); ?>" class="team-qr-img" />
                                                </div>
                                            </div>
                                        <?php endif; ?>
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
