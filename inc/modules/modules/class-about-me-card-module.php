<?php
/**
 * About Me Card Module - About 我是谁
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class About_Me_Card_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-id';
        $this->description = __( '展示作者头像、简介、社媒与 Now 状态', 'developer-starter' );
    }

    public function get_id() {
        return 'about_me_card';
    }

    public function get_name() {
        return __( 'About 我是谁', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'about_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( 'About 我是谁', 'developer-starter' ),
            ),
            array(
                'id'    => 'about_subtitle',
                'type'  => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
            ),
            array(
                'id'    => 'about_avatar',
                'type'  => 'image',
                'label' => __( '头像', 'developer-starter' ),
            ),
            array(
                'id'      => 'about_name',
                'type'    => 'text',
                'label'   => __( '名称', 'developer-starter' ),
                'default' => __( '启灵站长', 'developer-starter' ),
            ),
            array(
                'id'      => 'about_role',
                'type'    => 'text',
                'label'   => __( '身份/标签', 'developer-starter' ),
                'default' => __( '创作者 / 开发者', 'developer-starter' ),
            ),
            array(
                'id'      => 'about_intro',
                'type'    => 'textarea',
                'label'   => __( '简介', 'developer-starter' ),
                'default' => __( '在这里分享产品、技术和内容创作思考。', 'developer-starter' ),
            ),
            array(
                'id'      => 'about_now',
                'type'    => 'text',
                'label'   => __( 'Now 状态', 'developer-starter' ),
                'default' => __( '最近在优化主题模块和内容体验。', 'developer-starter' ),
            ),
            array(
                'id'      => 'about_show_now',
                'type'    => 'select',
                'label'   => __( '显示 Now 状态', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'    => 'about_location',
                'type'  => 'text',
                'label' => __( '所在地', 'developer-starter' ),
            ),
            array(
                'id'    => 'about_website',
                'type'  => 'text',
                'label' => __( '个人网站链接', 'developer-starter' ),
            ),
            array( 'id' => 'about_website_text', 'type' => 'text', 'label' => __( '个人网站文案', 'developer-starter' ), 'default' => __( '个人网站', 'developer-starter' ) ),
            array(
                'id'    => 'about_email',
                'type'  => 'text',
                'label' => __( '邮箱', 'developer-starter' ),
            ),
            array(
                'id'     => 'about_socials',
                'type'   => 'repeater',
                'label'  => __( '社媒列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'label',
                        'type'  => 'text',
                        'label' => __( '名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'label' => __( '链接', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'icon',
                        'type'    => 'text',
                        'label'   => __( '图标文字/Emoji', 'developer-starter' ),
                        'default' => '🔗',
                    ),
                ),
            ),
            array(
                'id'      => 'about_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'about_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'about_text_color',
                'type'    => 'color',
                'label'   => __( '文字颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-900)',
            ),
            array(
                'id'      => 'about_accent_color',
                'type'    => 'color',
                'label'   => __( '强调色', 'developer-starter' ),
                'default' => 'var(--color-primary)',
            ),
            array(
                'id'          => 'about_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '控制 Now 标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'developer-starter' ),
                'default' => '56px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'developer-starter' ),
                'default' => '56px',
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['about_title'] ) ? $data['about_title'] : '';
        $subtitle = isset( $data['about_subtitle'] ) ? $data['about_subtitle'] : '';
        $avatar = isset( $data['about_avatar'] ) ? $data['about_avatar'] : '';
        $name = isset( $data['about_name'] ) && $data['about_name'] !== '' ? $data['about_name'] : __( '启灵站长', 'developer-starter' );
        $role = isset( $data['about_role'] ) ? $data['about_role'] : '';
        $intro = isset( $data['about_intro'] ) ? $data['about_intro'] : '';
        $now = isset( $data['about_now'] ) ? $data['about_now'] : '';
        $show_now = ! isset( $data['about_show_now'] ) || $data['about_show_now'] === 'yes';
        $location = isset( $data['about_location'] ) ? $data['about_location'] : '';
        $website = isset( $data['about_website'] ) ? $data['about_website'] : '';
        $website_text = isset( $data['about_website_text'] ) && '' !== trim( (string) $data['about_website_text'] ) ? (string) $data['about_website_text'] : __( '个人网站', 'developer-starter' );
        $email = isset( $data['about_email'] ) ? $data['about_email'] : '';
        $safe_email = sanitize_email( $email );

        $socials = isset( $data['about_socials'] ) && is_array( $data['about_socials'] ) ? $data['about_socials'] : array();
        if ( empty( $socials ) ) {
            $socials = array(
                array(
                    'label' => 'GitHub',
                    'url'   => 'https://github.com/',
                    'icon'  => '🐙',
                ),
                array(
                    'label' => 'X',
                    'url'   => 'https://x.com/',
                    'icon'  => '𝕏',
                ),
                array(
                    'label' => 'RSS',
                    'url'   => home_url( '/feed/' ),
                    'icon'  => '📡',
                ),
            );
        }

        $card_bg = isset( $data['about_card_bg'] ) && $data['about_card_bg'] !== '' ? $data['about_card_bg'] : 'var(--color-neutral-0)';
        $bg_color = isset( $data['about_bg_color'] ) && $data['about_bg_color'] !== '' ? $data['about_bg_color'] : 'var(--color-neutral-50)';
        $text_color = isset( $data['about_text_color'] ) && $data['about_text_color'] !== '' ? $data['about_text_color'] : 'var(--color-neutral-900)';
        $accent_color = isset( $data['about_accent_color'] ) && $data['about_accent_color'] !== '' ? $data['about_accent_color'] : 'var(--color-primary)';
        $badge_bg = isset( $data['about_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['about_badge_bg'] ) ) : '';
        $badge_bg = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '56px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '56px';

        $module_id = 'about-me-card-' . uniqid();
        $name_initial = $this->extract_initial( $name );
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-about-me-card" style="padding-top: <?php echo esc_attr( $pt ); ?>; padding-bottom: <?php echo esc_attr( $pb ); ?>; background: <?php echo esc_attr( $bg_color ); ?>;<?php echo '' !== $badge_bg ? esc_attr( '--qiling-component-badge-bg: ' . $badge_bg . ';' ) : ''; ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <article class="qil-about-card" style="--qil-about-card-bg: <?php echo esc_attr( $card_bg ); ?>; --qil-about-text: <?php echo esc_attr( $text_color ); ?>; --qil-about-accent: <?php echo esc_attr( $accent_color ); ?>;">
                    <div class="qil-about-head">
                        <div class="qil-about-avatar-wrap">
                            <?php if ( $avatar ) : ?>
                                <img class="qil-about-avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                            <?php else : ?>
                                <div class="qil-about-avatar is-fallback"><?php echo esc_html( $name_initial ); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="qil-about-profile">
                            <h3 class="qil-about-name"><?php echo esc_html( $name ); ?></h3>
                            <?php if ( $role ) : ?>
                                <p class="qil-about-role"><?php echo esc_html( $role ); ?></p>
                            <?php endif; ?>
                            <?php if ( $intro ) : ?>
                                <p class="qil-about-intro"><?php echo esc_html( $intro ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ( $show_now && $now !== '' ) : ?>
                        <div class="qil-about-now">
                            <span class="qil-about-now-label"><?php echo esc_html__( 'Now', 'developer-starter' ); ?></span>
                            <span class="qil-about-now-text"><?php echo esc_html( $now ); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $location || $website || $email ) : ?>
                        <div class="qil-about-meta">
                            <?php if ( $location ) : ?>
                                <span class="qil-about-meta-item"><?php echo esc_html__( '位置：', 'developer-starter' ) . esc_html( $location ); ?></span>
                            <?php endif; ?>
                            <?php if ( $website ) : ?>
                                <a class="qil-about-meta-item" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $website_text ); ?></a>
                            <?php endif; ?>
                            <?php if ( $safe_email !== '' ) : ?>
                                <a class="qil-about-meta-item" href="mailto:<?php echo esc_attr( $safe_email ); ?>"><?php echo esc_html( $safe_email ); ?></a>
                            <?php elseif ( $email ) : ?>
                                <span class="qil-about-meta-item"><?php echo esc_html( $email ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $socials ) ) : ?>
                        <div class="qil-about-socials">
                            <?php foreach ( $socials as $social ) : ?>
                                <?php
                                if ( ! is_array( $social ) ) {
                                    continue;
                                }
                                $social_label = isset( $social['label'] ) ? trim( (string) $social['label'] ) : '';
                                $social_url = isset( $social['url'] ) ? trim( (string) $social['url'] ) : '';
                                $social_icon = isset( $social['icon'] ) && $social['icon'] !== '' ? (string) $social['icon'] : '🔗';
                                if ( $social_label === '' || $social_url === '' ) {
                                    continue;
                                }
                                ?>
                                <a class="qil-about-social" href="<?php echo esc_url( $social_url ); ?>" target="_blank" rel="noopener nofollow">
                                    <span class="qil-about-social-icon"><?php echo esc_html( $social_icon ); ?></span>
                                    <span class="qil-about-social-label"><?php echo esc_html( $social_label ); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </div>
        </section>
        <?php
    }

    private function extract_initial( $name ) {
        $name = trim( (string) $name );
        if ( $name === '' ) {
            return 'ME';
        }

        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $name, 0, 1 );
        }

        return strtoupper( substr( $name, 0, 1 ) );
    }
}
