<?php
/**
 * Resume Hero Module - 个人简历首屏模块
 *
 * 专为个人简历/作品集网站设计的首屏展示模块
 * 包含头像、姓名、职位、个人简介、联系方式、社交媒体、CTA按钮等
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Resume_Hero_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-id-alt';
        $this->description = __( '个人简历首屏，适合展示个人资料、联系方式、社交媒体等', 'developer-starter' );
    }

    public function get_id() {
        return 'resume_hero';
    }

    public function get_name() {
        return __( '个人简历首屏', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'rh_style_variant',
                'type'    => 'select',
                'label'   => __( '首屏风格', 'developer-starter' ),
                'options' => array(
                    'classic' => __( '经典介绍', 'developer-starter' ),
                    'card'    => __( '资料卡片', 'developer-starter' ),
                ),
                'default' => 'classic',
            ),
            array( 'id' => 'rh_layout', 'type' => 'select', 'label' => __( '布局方式', 'developer-starter' ), 'options' => array( 'center' => __( '居中', 'developer-starter' ), 'left' => __( '左对齐', 'developer-starter' ) ), 'default' => 'center', 'dependency' => array( 'rh_style_variant', '==', 'classic' ) ),
            array( 'id' => 'rh_height', 'type' => 'text', 'label' => __( '区域高度', 'developer-starter' ), 'default' => '100vh' ),
            array( 'id' => 'rh_bg_image', 'type' => 'image', 'label' => __( '背景图片', 'developer-starter' ) ),
            array( 'id' => 'rh_bg_color', 'type' => 'color', 'label' => __( '背景颜色', 'developer-starter' ), 'default' => 'var(--color-neutral-900)' ),
            array( 'id' => 'rh_overlay_color', 'type' => 'color', 'label' => __( '遮罩颜色', 'developer-starter' ), 'default' => 'rgba(var(--qiling-rgb-15-23-42), 0.85)' ),
            array(
                'id'         => 'rh_intro_badge',
                'type'       => 'text',
                'label'      => __( '卡片标签', 'developer-starter' ),
                'default'    => 'HELLO',
                'dependency' => array( 'rh_style_variant', '==', 'card' ),
            ),
            array(
                'id'         => 'rh_accent_color',
                'type'       => 'color',
                'label'      => __( '卡片强调色', 'developer-starter' ),
                'default'    => 'var(--color-success)',
                'dependency' => array( 'rh_style_variant', '==', 'card' ),
            ),
            
            array( 'id' => 'rh_avatar', 'type' => 'image', 'label' => __( '头像', 'developer-starter' ) ),
            array( 'id' => 'rh_avatar_style', 'type' => 'select', 'label' => __( '头像样式', 'developer-starter' ), 'options' => array( 'circle' => __( '圆形', 'developer-starter' ), 'rounded' => __( '圆角', 'developer-starter' ), 'square' => __( '方形', 'developer-starter' ) ), 'default' => 'circle' ),
            array( 'id' => 'rh_avatar_size', 'type' => 'text', 'label' => __( '头像尺寸', 'developer-starter' ), 'default' => '150px' ),
            array( 'id' => 'rh_name', 'type' => 'text', 'label' => __( '姓名', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '张三', 'Alex Chen' ) : __( '张三', 'developer-starter' ) ),
            array( 'id' => 'rh_name_color', 'type' => 'color', 'label' => __( '姓名颜色', 'developer-starter' ), 'default' => 'var(--color-neutral-0)' ),
            array( 'id' => 'rh_titles', 'type' => 'textarea', 'label' => __( '职位/头衔 (每行一个)', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "前端工程师\n全栈开发者", "Frontend Engineer\nFull-Stack Developer" ) : __( "前端工程师\n全栈开发者", 'developer-starter' ) ),
            array( 'id' => 'rh_title_color', 'type' => 'color', 'label' => __( '职位颜色', 'developer-starter' ) ),
            array( 'id' => 'rh_typewriter', 'type' => 'checkbox', 'label' => __( '启用打字机效果', 'developer-starter' ), 'default' => '1' ),
            
            array( 'id' => 'rh_bio', 'type' => 'textarea', 'label' => __( '个人简介', 'developer-starter' ) ),
            array( 'id' => 'rh_bio_color', 'type' => 'color', 'label' => __( '简介颜色', 'developer-starter' ), 'default' => 'var(--qiling-color-rgba-255-255-255-08)' ),
            array( 'id' => 'rh_location', 'type' => 'text', 'label' => __( '所在地', 'developer-starter' ) ),
            array( 'id' => 'rh_email', 'type' => 'text', 'label' => __( '邮箱', 'developer-starter' ) ),
            array( 'id' => 'rh_phone', 'type' => 'text', 'label' => __( '电话', 'developer-starter' ) ),
            array( 'id' => 'rh_website', 'type' => 'text', 'label' => __( '网站', 'developer-starter' ) ),
            array(
                'id'         => 'rh_profile_facts',
                'type'       => 'repeater',
                'label'      => __( '卡片资料条目', 'developer-starter' ),
                'dependency' => array( 'rh_style_variant', '==', 'card' ),
                'fields'     => array(
                    array( 'id' => 'label', 'type' => 'text', 'label' => __( '标签', 'developer-starter' ) ),
                    array( 'id' => 'value', 'type' => 'text', 'label' => __( '内容', 'developer-starter' ) ),
                ),
            ),
            
            array( 'id' => 'rh_socials', 'type' => 'repeater', 'label' => __( '社交媒体', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'icon', 'type' => 'text', 'label' => __( '图标 (支持SVG/Predefined)', 'developer-starter' ) ),
                array( 'id' => 'name', 'type' => 'text', 'label' => __( '名称', 'developer-starter' ) ),
                array( 'id' => 'link', 'type' => 'text', 'label' => __( '链接', 'developer-starter' ) ),
            ) ),
            
            array( 'id' => 'rh_btn1_text', 'type' => 'text', 'label' => __( '按钮1文字', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '下载简历', 'Download Resume' ) : __( '下载简历', 'developer-starter' ) ),
            array( 'id' => 'rh_btn1_link', 'type' => 'text', 'label' => __( '按钮1链接', 'developer-starter' ), 'default' => '#' ),
            array( 'id' => 'rh_btn1_style', 'type' => 'select', 'label' => __( '按钮1样式', 'developer-starter' ), 'options' => array( 'solid' => __( '实心', 'developer-starter' ), 'outline' => __( '描边', 'developer-starter' ) ), 'default' => 'solid' ),
            
            array( 'id' => 'rh_btn2_text', 'type' => 'text', 'label' => __( '按钮2文字', 'developer-starter' ) ),
            array( 'id' => 'rh_btn2_link', 'type' => 'text', 'label' => __( '按钮2链接', 'developer-starter' ) ),
            array( 'id' => 'rh_btn2_style', 'type' => 'select', 'label' => __( '按钮2样式', 'developer-starter' ), 'options' => array( 'solid' => __( '实心', 'developer-starter' ), 'outline' => __( '描边', 'developer-starter' ) ), 'default' => 'outline' ),
        );
    }

    public function render( $data = array() ) {
        // 基础配置
        $style_variant = isset( $data['rh_style_variant'] ) ? $data['rh_style_variant'] : 'classic';
        if ( ! in_array( $style_variant, array( 'classic', 'card' ), true ) ) {
            $style_variant = 'classic';
        }
        $layout = isset( $data['rh_layout'] ) ? $data['rh_layout'] : 'center';
        $height = isset( $data['rh_height'] ) ? $data['rh_height'] : '100vh';
        $bg_image = isset( $data['rh_bg_image'] ) ? $data['rh_bg_image'] : '';
        $bg_color = isset( $data['rh_bg_color'] ) ? $data['rh_bg_color'] : 'var(--color-neutral-900)';
        $overlay_color = isset( $data['rh_overlay_color'] ) ? $data['rh_overlay_color'] : 'rgba(var(--qiling-rgb-15-23-42), 0.85)';
        $intro_badge = isset( $data['rh_intro_badge'] ) ? $data['rh_intro_badge'] : 'HELLO';
        $accent_color = isset( $data['rh_accent_color'] ) ? $data['rh_accent_color'] : 'var(--color-success)';
        $is_card_variant = 'card' === $style_variant;
        
        // 个人信息
        $avatar = isset( $data['rh_avatar'] ) ? $data['rh_avatar'] : '';
        $avatar_style = isset( $data['rh_avatar_style'] ) ? $data['rh_avatar_style'] : 'circle';
        $avatar_size = isset( $data['rh_avatar_size'] ) ? $data['rh_avatar_size'] : '150px';
        $name = isset( $data['rh_name'] ) ? $data['rh_name'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '张三', 'Alex Chen' ) : __( '张三', 'developer-starter' ) );
        $name_color = isset( $data['rh_name_color'] ) ? $data['rh_name_color'] : 'var(--color-neutral-0)';
        $titles = isset( $data['rh_titles'] ) ? $data['rh_titles'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "前端工程师\n全栈开发者", "Frontend Engineer\nFull-Stack Developer" ) : __( "前端工程师\n全栈开发者", 'developer-starter' ) );
        $title_color = isset( $data['rh_title_color'] ) ? $data['rh_title_color'] : '';
        $bio = isset( $data['rh_bio'] ) ? $data['rh_bio'] : '';
        $bio_color = isset( $data['rh_bio_color'] ) ? $data['rh_bio_color'] : 'var(--qiling-color-rgba-255-255-255-08)';
        $location = isset( $data['rh_location'] ) ? $data['rh_location'] : '';
        
        // 联系方式
        $email = isset( $data['rh_email'] ) ? $data['rh_email'] : '';
        $phone = isset( $data['rh_phone'] ) ? $data['rh_phone'] : '';
        $website = isset( $data['rh_website'] ) ? $data['rh_website'] : '';
        
        // 社交媒体
        $socials = isset( $data['rh_socials'] ) && is_array( $data['rh_socials'] ) ? $data['rh_socials'] : array();
        $profile_facts = $this->get_profile_facts( $data, $location, $email, $phone, $website );
        
        // CTA按钮
        $btn1_text = isset( $data['rh_btn1_text'] ) ? $data['rh_btn1_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '下载简历', 'Download Resume' ) : __( '下载简历', 'developer-starter' ) );
        $btn1_link = isset( $data['rh_btn1_link'] ) ? $data['rh_btn1_link'] : '#';
        $btn1_style = isset( $data['rh_btn1_style'] ) ? $data['rh_btn1_style'] : 'solid';
        $btn2_text = isset( $data['rh_btn2_text'] ) ? $data['rh_btn2_text'] : '';
        $btn2_link = isset( $data['rh_btn2_link'] ) ? $data['rh_btn2_link'] : '#';
        $btn2_style = isset( $data['rh_btn2_style'] ) ? $data['rh_btn2_style'] : 'outline';
        
        // 打字机效果 - 支持多种truthy值，当有多个职位时默认启用
        $typewriter_raw = isset( $data['rh_typewriter'] ) ? $data['rh_typewriter'] : '1';
        $typewriter = ( $typewriter_raw === '1' || $typewriter_raw === 1 || $typewriter_raw === true || $typewriter_raw === 'yes' || $typewriter_raw === 'on' || $typewriter_raw === '' );
        
        $unique_id = 'resume-hero-' . uniqid();
        
        // 处理职位列表 - 支持 \n 和 PHP_EOL
        $titles_clean = str_replace( array( "\r\n", "\r" ), "\n", $titles );
        $title_list = array_values( array_filter( array_map( 'trim', explode( "\n", $titles_clean ) ) ) );

        if ( $is_card_variant && 'circle' === $avatar_style ) {
            $avatar_style = 'rounded';
        }

        if ( $is_card_variant && ( '' === trim( (string) $avatar_size ) || '150px' === trim( (string) $avatar_size ) ) ) {
            $avatar_size = '420px';
        }

        $name_color = $this->resolve_variant_color( $name_color, 'var(--color-neutral-0)', 'var(--color-text)', $is_card_variant );
        $title_color = $this->resolve_variant_color( $title_color, '', 'var(--qiling-color-4b5563)', $is_card_variant );
        $bio_color = $this->resolve_variant_color( $bio_color, 'var(--qiling-color-rgba-255-255-255-08)', 'var(--qiling-color-6b7280)', $is_card_variant );
        
        // 背景样式
        $section_style = "min-height: {$height}; --rh-accent-color: {$accent_color};";
        if ( $bg_image ) {
            $section_style .= "background-image: url('{$bg_image}'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }

        ob_start();
        if ( $btn1_text || $btn2_text ) :
            ?>
            <div class="rh-buttons<?php echo $is_card_variant ? ' rh-buttons-card' : ''; ?>">
                <?php if ( $btn1_text ) : ?>
                    <a href="<?php echo esc_url( $btn1_link ); ?>" class="rh-btn <?php echo esc_attr( 'btn-' . $btn1_style ); ?>">
                        <?php if ( $btn1_style === 'solid' ) : ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <?php endif; ?>
                        <?php echo esc_html( $btn1_text ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( $btn2_text ) : ?>
                    <a href="<?php echo esc_url( $btn2_link ); ?>" class="rh-btn <?php echo esc_attr( 'btn-' . $btn2_style ); ?>">
                        <?php echo esc_html( $btn2_text ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php
        endif;
        $buttons_html = ob_get_clean();
        ?>
        <section class="module module-resume-hero <?php echo esc_attr( 'style-' . $style_variant ); ?>" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_image && $overlay_color ) : ?>
                <div class="rh-overlay" style="position: absolute; inset: 0; background: <?php echo esc_attr( $overlay_color ); ?>; pointer-events: none;"></div>
            <?php endif; ?>
            <?php if ( $is_card_variant ) : ?>
                <div class="rh-card-shell">
                    <div class="rh-card">
                        <div class="rh-card-main<?php echo $avatar ? '' : ' is-no-avatar'; ?>" style="--rh-card-photo-width: <?php echo esc_attr( $avatar_size ); ?>;">
                            <?php if ( $avatar ) : ?>
                                <div class="rh-card-photo <?php echo esc_attr( 'style-' . $avatar_style ); ?>">
                                    <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="rh-avatar <?php echo esc_attr( 'style-' . $avatar_style ); ?>" />
                                </div>
                            <?php endif; ?>

                            <div class="rh-card-content">
                                <?php if ( '' !== trim( $intro_badge ) ) : ?>
                                    <div class="rh-card-badge"><?php echo esc_html( $intro_badge ); ?></div>
                                <?php endif; ?>

                                <h1 class="rh-name" style="<?php echo $name_color ? 'color:' . esc_attr( $name_color ) . ';' : ''; ?>"><?php echo esc_html( $name ); ?></h1>

                                <?php if ( ! empty( $title_list ) ) : ?>
                                    <div class="rh-titles" style="<?php echo $title_color ? 'color:' . esc_attr( $title_color ) . ';' : ''; ?>">
                                        <?php if ( $typewriter && count( $title_list ) > 1 ) : ?>
                                            <span class="rh-title-typewriter" data-titles='<?php echo esc_attr( wp_json_encode( $title_list ) ); ?>'><?php echo esc_html( $title_list[0] ); ?></span>
                                            <span class="rh-cursor">|</span>
                                        <?php else : ?>
                                            <?php foreach ( $title_list as $t ) : ?>
                                                <span class="rh-title-item"><?php echo esc_html( $t ); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ( $bio ) : ?>
                                    <p class="rh-bio" style="<?php echo $bio_color ? 'color:' . esc_attr( $bio_color ) . ';' : ''; ?>"><?php echo esc_html( $bio ); ?></p>
                                <?php endif; ?>

                                <?php if ( ! empty( $profile_facts ) ) : ?>
                                    <div class="rh-card-divider"></div>
                                    <div class="rh-card-facts">
                                        <?php foreach ( $profile_facts as $fact ) : ?>
                                            <div class="rh-card-fact">
                                                <div class="rh-card-fact-label"><?php echo esc_html( $fact['label'] ); ?></div>
                                                <div class="rh-card-fact-value"><?php echo $this->render_profile_fact_value( $fact['label'], $fact['value'] ); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php echo $buttons_html; ?>
                            </div>
                        </div>

                        <?php if ( ! empty( $socials ) ) : ?>
                            <div class="rh-card-footer">
                                <div class="rh-socials">
                                    <?php foreach ( $socials as $social ) :
                                        $s_icon = isset( $social['icon'] ) ? $social['icon'] : '';
                                        $s_link = isset( $social['link'] ) ? $social['link'] : '#';
                                        $s_name = isset( $social['name'] ) ? $social['name'] : '';
                                        if ( empty( $s_icon ) && empty( $s_name ) ) {
                                            continue;
                                        }
                                        ?>
                                        <a href="<?php echo esc_url( $s_link ); ?>" class="rh-social-item" title="<?php echo esc_attr( $s_name ); ?>" target="_blank" rel="noopener">
                                            <?php echo $this->render_social_icon( $s_icon, $s_name ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="rh-container <?php echo esc_attr( 'layout-' . $layout ); ?>">
                    <?php if ( $avatar ) : ?>
                        <div class="rh-avatar-wrap">
                            <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="rh-avatar <?php echo esc_attr( 'style-' . $avatar_style ); ?>" style="width: <?php echo esc_attr( $avatar_size ); ?>; height: <?php echo esc_attr( $avatar_size ); ?>;" />
                        </div>
                    <?php endif; ?>

                    <h1 class="rh-name" style="<?php echo $name_color ? 'color:' . esc_attr( $name_color ) . ';' : ''; ?>"><?php echo esc_html( $name ); ?></h1>

                    <?php if ( ! empty( $title_list ) ) : ?>
                        <div class="rh-titles" style="<?php echo $title_color ? 'color:' . esc_attr( $title_color ) . ';' : ''; ?>">
                            <?php if ( $typewriter && count( $title_list ) > 1 ) : ?>
                                <span class="rh-title-typewriter" data-titles='<?php echo esc_attr( wp_json_encode( $title_list ) ); ?>'><?php echo esc_html( $title_list[0] ); ?></span>
                                <span class="rh-cursor">|</span>
                            <?php else : ?>
                                <?php foreach ( $title_list as $t ) : ?>
                                    <span class="rh-title-item"><?php echo esc_html( $t ); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $bio ) : ?>
                        <p class="rh-bio" style="<?php echo $bio_color ? 'color:' . esc_attr( $bio_color ) . ';' : ''; ?>"><?php echo esc_html( $bio ); ?></p>
                    <?php endif; ?>

                    <?php if ( $email || $phone || $location ) : ?>
                        <div class="rh-contact">
                            <?php if ( $email ) : ?>
                                <a href="mailto:<?php echo esc_attr( $email ); ?>" class="rh-contact-item">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <span><?php echo esc_html( $email ); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ( $phone ) : ?>
                                <a href="tel:<?php echo esc_attr( $phone ); ?>" class="rh-contact-item">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span><?php echo esc_html( $phone ); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ( $location ) : ?>
                                <span class="rh-contact-item">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span><?php echo esc_html( $location ); ?></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $socials ) ) : ?>
                        <div class="rh-socials">
                            <?php foreach ( $socials as $social ) :
                                $s_icon = isset( $social['icon'] ) ? $social['icon'] : '';
                                $s_link = isset( $social['link'] ) ? $social['link'] : '#';
                                $s_name = isset( $social['name'] ) ? $social['name'] : '';
                                if ( empty( $s_icon ) && empty( $s_name ) ) {
                                    continue;
                                }
                                ?>
                                <a href="<?php echo esc_url( $s_link ); ?>" class="rh-social-item" title="<?php echo esc_attr( $s_name ); ?>" target="_blank" rel="noopener">
                                    <?php echo $this->render_social_icon( $s_icon, $s_name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php echo $buttons_html; ?>
                </div>
            <?php endif; ?>
        </section>
<?php if ( $typewriter && count( $title_list ) > 1 ) : ?>
        <script>
        (function(){
            function initTypewriter() {
                var el = document.querySelector('#<?php echo esc_js( $unique_id ); ?> .rh-title-typewriter');
                if(!el) return;
                var titles = JSON.parse(el.getAttribute('data-titles'));
                if(!titles || titles.length < 2) return;
                var idx = 0, charIdx = titles[0].length, isDeleting = true, pauseTime = 2000;
                
                function type() {
                    if (!el.isConnected) return;
                    var current = titles[idx];
                    if(isDeleting) {
                        el.textContent = current.substring(0, charIdx--);
                        if(charIdx < 0) {
                            isDeleting = false;
                            idx = (idx + 1) % titles.length;
                            setTimeout(type, 500);
                            return;
                        }
                    } else {
                        el.textContent = current.substring(0, charIdx++);
                        if(charIdx > current.length) {
                            isDeleting = true;
                            setTimeout(type, pauseTime);
                            return;
                        }
                    }
                    setTimeout(type, isDeleting ? 50 : 100);
                }
                setTimeout(type, pauseTime);
            }
            
            // 兼容DOMContentLoaded已触发的情况
            if(document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTypewriter);
            } else {
                initTypewriter();
            }
        })();
        </script>
        <?php endif; ?>
        <?php
    }

    private function resolve_variant_color( $value, $classic_default, $card_default, $is_card_variant ) {
        $value = is_string( $value ) ? trim( $value ) : '';

        if ( ! $is_card_variant ) {
            return $value;
        }

        if ( '' === $value ) {
            return $card_default;
        }

        if ( '' !== $classic_default && strtolower( $value ) === strtolower( $classic_default ) ) {
            return $card_default;
        }

        return $value;
    }

    private function get_profile_facts( $data, $location, $email, $phone, $website ) {
        $facts = array();

        if ( isset( $data['rh_profile_facts'] ) && is_array( $data['rh_profile_facts'] ) ) {
            foreach ( $data['rh_profile_facts'] as $fact ) {
                $label = isset( $fact['label'] ) ? trim( (string) $fact['label'] ) : '';
                $value = isset( $fact['value'] ) ? trim( (string) $fact['value'] ) : '';

                if ( '' === $label && '' === $value ) {
                    continue;
                }

                $facts[] = array(
                    'label' => '' !== $label ? $label : __( '资料', 'developer-starter' ),
                    'value' => $value,
                );
            }
        }

        if ( ! empty( $facts ) ) {
            return $facts;
        }

        $defaults = array(
            array(
                'label' => __( '所在地', 'developer-starter' ),
                'value' => $location,
            ),
            array(
                'label' => __( '邮箱', 'developer-starter' ),
                'value' => $email,
            ),
            array(
                'label' => __( '电话', 'developer-starter' ),
                'value' => $phone,
            ),
            array(
                'label' => __( '网站', 'developer-starter' ),
                'value' => $website,
            ),
        );

        return array_values(
            array_filter(
                $defaults,
                static function( $fact ) {
                    return ! empty( $fact['value'] );
                }
            )
        );
    }

    private function render_profile_fact_value( $label, $value ) {
        $label = trim( (string) $label );
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        $label_lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $label, 'UTF-8' ) : strtolower( $label );

        if ( is_email( $value ) || false !== strpos( $label_lower, 'mail' ) || false !== strpos( $label_lower, '邮箱' ) ) {
            return '<a class="rh-card-fact-link" href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
        }

        if ( false !== strpos( $label_lower, 'phone' ) || false !== strpos( $label_lower, '电话' ) || false !== strpos( $label_lower, 'mobile' ) ) {
            return '<a class="rh-card-fact-link" href="tel:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
        }

        if ( preg_match( '#^https?://#i', $value ) ) {
            return '<a class="rh-card-fact-link" href="' . esc_url( $value ) . '" target="_blank" rel="noopener">' . esc_html( $value ) . '</a>';
        }

        return '<span>' . esc_html( $value ) . '</span>';
    }
    
    /**
     * 渲染社交媒体图标
     */
    private function render_social_icon( $icon, $name ) {
        // 常见社交平台图标
        $icons = array(
            'github' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>',
            'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>',
            'wechat' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-6.656-6.088V8.89l-.002-.032z"/></svg>',
        );
        
        $icon_lower = strtolower( trim( $icon ) );
        
        // 检查是否是预设图标
        if ( isset( $icons[ $icon_lower ] ) ) {
            return $icons[ $icon_lower ];
        }
        
        // 检查是否是完整 SVG 代码
        if ( strpos( $icon, '<svg' ) !== false ) {
            if ( function_exists( 'developer_starter_sanitize_svg' ) ) {
                return developer_starter_sanitize_svg( $icon );
            }
            return $icon;
        }
        
        // 检查是否是emoji
        if ( mb_strlen( $icon ) <= 2 && ! preg_match( '/[a-zA-Z]/', $icon ) ) {
            return '<span style="font-size: var(--qiling-text-rem-1p2);">' . esc_html( $icon ) . '</span>';
        }
        
        // 检查是否是 icon-xxx 格式
        if ( strpos( $icon_lower, 'icon-' ) !== false ) {
            return '<svg class="icon" aria-hidden="true"><use xlink:href="#' . esc_attr( trim( $icon ) ) . '"></use></svg>';
        }
        
        // 默认使用名称首字母
        return '<span>' . esc_html( mb_substr( $name ?: $icon, 0, 1 ) ) . '</span>';
    }
}
