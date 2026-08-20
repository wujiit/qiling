<?php
/**
 * Footer Suite Module.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Footer_Suite_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'footer';
        $this->icon        = 'dashicons-layout';
        $this->description = __( '整合品牌、链接、联系、二维码、友情链接和版权备案的页脚模块', 'developer-starter' );
    }

    public function get_id() {
        return 'footer_suite';
    }

    public function get_name() {
        return __( '页脚风格', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'qfs_layout',
                'type'    => 'select',
                'label'   => __( '布局', 'developer-starter' ),
                'options' => array(
                    'balanced' => __( '品牌 + 链接 + 联系', 'developer-starter' ),
                    'columns'  => __( '多列', 'developer-starter' ),
                    'compact'  => __( '紧凑', 'developer-starter' ),
                ),
                'default' => 'balanced',
            ),
            array(
                'id'      => 'qfs_width',
                'type'    => 'select',
                'label'   => __( '宽度', 'developer-starter' ),
                'options' => array(
                    'contained' => __( '常规', 'developer-starter' ),
                    'wide'      => __( '宽屏', 'developer-starter' ),
                    'full'      => __( '通栏', 'developer-starter' ),
                ),
                'default' => 'contained',
            ),
            array(
                'id'      => 'qfs_variant',
                'type'    => 'select',
                'label'   => __( '视觉风格', 'developer-starter' ),
                'options' => array(
                    'classic' => __( '经典页脚', 'developer-starter' ),
                    'cinema'  => __( '深色影院', 'developer-starter' ),
                ),
                'default' => 'classic',
            ),
            array(
                'id'    => 'qfs_logo',
                'type'  => 'image',
                'label' => __( 'Logo', 'developer-starter' ),
            ),
            array(
                'id'    => 'qfs_brand_title',
                'type'  => 'text',
                'label' => __( '品牌名称', 'developer-starter' ),
            ),
            array(
                'id'    => 'qfs_brand_desc',
                'type'  => 'textarea',
                'label' => __( '品牌简介', 'developer-starter' ),
            ),
            array(
                'id'            => 'qfs_link_groups',
                'type'          => 'repeater',
                'label'         => __( '链接分组', 'developer-starter' ),
                'default_items' => array(
                    array(
                        'title' => __( '快速链接', 'developer-starter' ),
                        'links' => __( "首页|/\n关于我们|#\n联系我们|#", 'developer-starter' ),
                    ),
                ),
                'fields'        => array(
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '分组标题', 'developer-starter' ),
                    ),
                    array(
                        'id'          => 'links',
                        'type'        => 'textarea',
                        'label'       => __( '链接', 'developer-starter' ),
                        'description' => __( '每行：文字|链接', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'qfs_link_columns',
                'type'    => 'select',
                'label'   => __( '链接列数', 'developer-starter' ),
                'options' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ),
                'default' => '2',
            ),
            array(
                'id'      => 'qfs_contact_title',
                'type'    => 'text',
                'label'   => __( '联系标题', 'developer-starter' ),
                'default' => __( '联系方式', 'developer-starter' ),
            ),
            array(
                'id'          => 'qfs_contact_items',
                'type'        => 'textarea',
                'label'       => __( '联系内容', 'developer-starter' ),
                'description' => __( '每行：名称|内容|链接', 'developer-starter' ),
            ),
            array(
                'id'      => 'qfs_social_title',
                'type'    => 'text',
                'label'   => __( '社交标题', 'developer-starter' ),
                'default' => __( '关注我们', 'developer-starter' ),
            ),
            array(
                'id'          => 'qfs_social_links',
                'type'        => 'textarea',
                'label'       => __( '社交链接', 'developer-starter' ),
                'description' => __( '每行：文字|链接', 'developer-starter' ),
            ),
            array(
                'id'            => 'qfs_qr_items',
                'type'          => 'repeater',
                'label'         => __( '二维码', 'developer-starter' ),
                'default_items' => array(),
                'fields'        => array(
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'text',
                        'type'  => 'text',
                        'label' => __( '说明', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'qfs_show_qr',
                'type'    => 'select',
                'label'   => __( '显示二维码', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qfs_friend_mode',
                'type'    => 'select',
                'label'   => __( '友情链接', 'developer-starter' ),
                'options' => array(
                    'theme_home'  => __( '使用主题友链，仅首页', 'developer-starter' ),
                    'custom_home' => __( '自定义，仅首页', 'developer-starter' ),
                    'custom_all'  => __( '自定义，全站', 'developer-starter' ),
                    'none'        => __( '不显示', 'developer-starter' ),
                ),
                'default' => 'theme_home',
            ),
            array(
                'id'      => 'qfs_friend_title',
                'type'    => 'text',
                'label'   => __( '友链标题', 'developer-starter' ),
                'default' => __( '友情链接：', 'developer-starter' ),
            ),
            array(
                'id'          => 'qfs_friend_links',
                'type'        => 'textarea',
                'label'       => __( '自定义友链', 'developer-starter' ),
                'description' => __( '每行：文字|链接', 'developer-starter' ),
            ),
            array(
                'id'    => 'qfs_bottom_text',
                'type'  => 'editor',
                'label' => __( '版权内容', 'developer-starter' ),
            ),
            array(
                'id'          => 'qfs_bottom_links',
                'type'        => 'textarea',
                'label'       => __( '底部链接', 'developer-starter' ),
                'description' => __( '每行：文字|链接', 'developer-starter' ),
            ),
            array(
                'id'      => 'qfs_show_filings',
                'type'    => 'select',
                'label'   => __( '显示备案', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'qfs_bg',
                'type'    => 'color',
                'label'   => __( '背景', 'developer-starter' ),
                'default' => 'var(--qiling-color-111111)',
            ),
            array(
                'id'      => 'qfs_text_color',
                'type'    => 'color',
                'label'   => __( '文字颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'qfs_muted_color',
                'type'    => 'color',
                'label'   => __( '辅助文字颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'qfs_heading_color',
                'type'    => 'color',
                'label'   => __( '标题颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'qfs_accent_color',
                'type'    => 'color',
                'label'   => __( '强调色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'qfs_badge_bg',
                'type'    => 'color',
                'label'   => __( '联系标签背景颜色', 'developer-starter' ),
                'default' => '',
                'description' => __( '控制联系内容前的小标签，留空时跟随页面预设/全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'qfs_border_color',
                'type'    => 'color',
                'label'   => __( '边线颜色', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-255-255-255-014)',
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'developer-starter' ),
                'default' => '64px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'developer-starter' ),
                'default' => '28px',
            ),
            array(
                'id'          => 'qfs_custom_css',
                'type'        => 'textarea',
                'label'       => __( '自定义 CSS', 'developer-starter' ),
                'description' => __( '可用 {{WRAPPER}} 表示当前模块', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $data = is_array( $data ) ? $data : array();

        $layout = $this->sanitize_choice( $this->string_value( $data, 'qfs_layout', 'balanced' ), array( 'balanced', 'columns', 'compact' ), 'balanced' );
        $width  = $this->sanitize_choice( $this->string_value( $data, 'qfs_width', 'contained' ), array( 'contained', 'wide', 'full' ), 'contained' );
        $variant = $this->sanitize_choice( $this->string_value( $data, 'qfs_variant', 'classic' ), array( 'classic', 'cinema' ), 'classic' );
        $is_video_portal = defined( 'QILING_VIDEO_PORTAL_PAGE' ) && QILING_VIDEO_PORTAL_PAGE;
        if ( $is_video_portal ) {
            $layout  = 'balanced';
            $width   = 'wide';
            $variant = 'cinema';
        }

        $brand_title = $this->string_value( $data, 'qfs_brand_title', '' );
        if ( '' === $brand_title ) {
            $brand_title = $this->option_text( 'company_name', get_bloginfo( 'name' ) );
        }

        $brand_desc = $this->string_value( $data, 'qfs_brand_desc', '' );
        if ( '' === $brand_desc ) {
            $brand_desc = $this->option_text( 'company_brief', '' );
        }

        $logo = $this->resolve_media_url( $this->string_value( $data, 'qfs_logo', '' ) );
        if ( '' === $logo ) {
            $logo = $this->resolve_media_url( $this->option( 'site_logo', '' ) );
        }

        $link_columns  = max( 1, min( 4, absint( $this->string_value( $data, 'qfs_link_columns', '2' ) ) ) );
        $link_groups   = $this->get_link_groups( $data );
        $link_groups = array_values(
            array_filter(
                $link_groups,
                static function ( $group ) {
                    $title = isset( $group['title'] ) ? trim( (string) $group['title'] ) : '';
                    return ! in_array( $title, array( '内容频道', 'Content Channels' ), true );
                }
            )
        );
        if ( 'cinema' === $variant ) {
            $link_columns = 1;
        }
        $contact_items = $this->get_contact_items( $data );
        $social_links  = $this->parse_link_lines( $this->string_value( $data, 'qfs_social_links', '' ) );
        $qr_items      = 'no' === $this->string_value( $data, 'qfs_show_qr', 'yes' ) ? array() : $this->get_qr_items( $data );
        if ( $is_video_portal ) {
            $qr_items = array();
        }
        $friend_links  = $this->get_friend_links( $data );
        $bottom_links  = $this->parse_link_lines( $this->string_value( $data, 'qfs_bottom_links', '' ) );

        $contact_title = $this->string_value( $data, 'qfs_contact_title', __( '联系方式', 'developer-starter' ) );
        $social_title  = $this->string_value( $data, 'qfs_social_title', __( '关注我们', 'developer-starter' ) );
        $friend_title  = $this->string_value( $data, 'qfs_friend_title', __( '友情链接：', 'developer-starter' ) );
        $bottom_text   = $this->get_bottom_text( $data );
        $show_filings  = 'no' !== $this->string_value( $data, 'qfs_show_filings', 'yes' );

        $module_id = 'footer-suite-' . uniqid();
        $classes   = array(
            'module',
            'module-footer-suite',
            'qfs-layout-' . $layout,
            'qfs-width-' . $width,
            'qfs-variant-' . $variant,
        );

        $accent_color = $this->string_value( $data, 'qfs_accent_color', '' );
        if ( '' === trim( $accent_color ) ) {
            $accent_color = 'var(--color-accent)';
        }

        $style_attr = $this->build_css_var_style(
            array(
                '--qfs-bg'      => $this->sanitize_css_color_value( $this->string_value( $data, 'qfs_bg', 'var(--qiling-color-111111)' ), 'var(--qiling-color-111111)' ),
                '--qfs-text'    => $this->sanitize_css_color_value( $this->inherit_footer_color_value( $data, 'qfs_text_color', 'var(--qiling-component-footer-text)', array( '#' . 'f7f7f7' ) ), 'var(--qiling-component-footer-text)' ),
                '--qfs-muted'   => $this->sanitize_css_color_value( $this->inherit_footer_color_value( $data, 'qfs_muted_color', 'var(--qiling-component-footer-link)', array( 'rgba' . '(255,255,255,0.68)' ) ), 'var(--qiling-component-footer-link)' ),
                '--qfs-heading' => $this->sanitize_css_color_value( $this->inherit_footer_color_value( $data, 'qfs_heading_color', 'var(--qiling-component-footer-heading)', array( '#' . 'ffffff' ) ), 'var(--qiling-component-footer-heading)' ),
                '--qfs-accent'  => $this->sanitize_css_color_value( $accent_color, 'var(--color-accent)' ),
                '--qiling-component-badge-bg' => $this->sanitize_css_color_value( $this->string_value( $data, 'qfs_badge_bg', '' ), '' ),
                '--qfs-border'  => $this->sanitize_css_color_value( $this->string_value( $data, 'qfs_border_color', 'var(--qiling-color-rgba-255-255-255-014)' ), 'var(--qiling-color-rgba-255-255-255-014)' ),
                '--qfs-pt'      => $this->sanitize_spacing_value( $this->string_value( $data, 'module_padding_top', '64px' ), '64px' ),
                '--qfs-pb'      => $this->sanitize_spacing_value( $this->string_value( $data, 'module_padding_bottom', '28px' ), '28px' ),
                '--qfs-cols'    => (string) $link_columns,
            )
        );

        $custom_css = $this->prepare_custom_css( $this->string_value( $data, 'qfs_custom_css', '' ), $module_id );
        if ( '' !== $custom_css ) {
            echo '<style id="' . esc_attr( $module_id . '-css' ) . '">' . "\n" . $custom_css . "\n" . '</style>';
        }
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
            <div class="qfs-inner">
                <div class="qfs-main">
                    <?php if ( $logo || $brand_title || $brand_desc ) : ?>
                        <div class="qfs-brand">
                            <?php if ( $logo ) : ?>
                                <a class="qfs-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                                    <img class="qfs-logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
                                </a>
                            <?php endif; ?>
                            <?php if ( $brand_title ) : ?>
                                <div class="qfs-brand-title"><?php echo esc_html( $brand_title ); ?></div>
                            <?php endif; ?>
                            <?php if ( $brand_desc ) : ?>
                                <div class="qfs-brand-desc"><?php echo wp_kses_post( wpautop( $brand_desc ) ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $link_groups ) ) : ?>
                        <div class="qfs-links-wrap">
                            <?php foreach ( $link_groups as $group ) : ?>
                                <?php
                                $group_title = isset( $group['title'] ) ? (string) $group['title'] : '';
                                $links       = isset( $group['links'] ) && is_array( $group['links'] ) ? $group['links'] : array();
                                if ( '' === $group_title && empty( $links ) ) {
                                    continue;
                                }
                                ?>
                                <nav class="qfs-link-group"<?php echo $group_title ? ' aria-label="' . esc_attr( $group_title ) . '"' : ''; ?>>
                                    <?php if ( $group_title ) : ?>
                                        <h3 class="qfs-section-title"><?php echo esc_html( $group_title ); ?></h3>
                                    <?php endif; ?>
                                    <?php $this->render_link_list( $links, 'qfs-list qfs-link-list', '_self' ); ?>
                                </nav>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $contact_items ) || ! empty( $social_links ) || ! empty( $qr_items ) ) : ?>
                        <div class="qfs-connect">
                            <?php if ( ! empty( $contact_items ) ) : ?>
                                <div class="qfs-connect-section">
                                    <?php if ( $contact_title ) : ?>
                                        <h3 class="qfs-section-title"><?php echo esc_html( $contact_title ); ?></h3>
                                    <?php endif; ?>
                                    <ul class="qfs-list qfs-contact-list">
                                        <?php foreach ( $contact_items as $item ) : ?>
                                            <li>
                                                <?php if ( ! empty( $item['label'] ) ) : ?>
                                                    <span class="qfs-contact-label"><?php echo esc_html( $item['label'] ); ?></span>
                                                <?php endif; ?>
                                                <?php if ( ! empty( $item['url'] ) ) : ?>
                                                    <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
                                                <?php else : ?>
                                                    <span><?php echo esc_html( $item['text'] ); ?></span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $social_links ) ) : ?>
                                <div class="qfs-connect-section">
                                    <?php if ( $social_title ) : ?>
                                        <h3 class="qfs-section-title"><?php echo esc_html( $social_title ); ?></h3>
                                    <?php endif; ?>
                                    <?php $this->render_link_list( $social_links, 'qfs-list qfs-social-list', '_blank' ); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $qr_items ) ) : ?>
                                <div class="qfs-qr-grid">
                                    <?php foreach ( $qr_items as $item ) : ?>
                                        <div class="qfs-qr-card">
                                            <?php if ( ! empty( $item['image'] ) ) : ?>
                                                <img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( ! empty( $item['title'] ) ? $item['title'] : get_bloginfo( 'name' ) ); ?>" />
                                            <?php endif; ?>
                                            <?php if ( ! empty( $item['title'] ) ) : ?>
                                                <strong><?php echo esc_html( $item['title'] ); ?></strong>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $item['text'] ) ) : ?>
                                                <span><?php echo esc_html( $item['text'] ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $friend_links ) ) : ?>
                    <div class="qfs-friend-links">
                        <?php if ( $friend_title ) : ?>
                            <span class="qfs-friend-title"><?php echo esc_html( $friend_title ); ?></span>
                        <?php endif; ?>
                        <div class="qfs-friend-list">
                            <?php foreach ( $friend_links as $link ) : ?>
                                <?php $this->render_anchor( $link, '_blank', true ); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="qfs-bottom">
                    <div class="qfs-copyright"><?php echo wp_kses_post( $bottom_text ); ?></div>
                    <?php if ( ! empty( $bottom_links ) || $show_filings ) : ?>
                        <div class="qfs-bottom-extra">
                            <?php if ( ! empty( $bottom_links ) ) : ?>
                                <div class="qfs-bottom-links">
                                    <?php foreach ( $bottom_links as $link ) : ?>
                                        <?php $this->render_anchor( $link, '_self', false ); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $show_filings && class_exists( '\Developer_Starter\China\China_Features' ) ) : ?>
                                <div class="qfs-filings">
                                    <?php \Developer_Starter\China\China_Features::render_footer_filings(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function get_link_groups( $data ) {
        $groups = isset( $data['qfs_link_groups'] ) && is_array( $data['qfs_link_groups'] ) ? $data['qfs_link_groups'] : array();
        $normalized = array();

        foreach ( $groups as $group ) {
            if ( ! is_array( $group ) ) {
                continue;
            }

            $title = isset( $group['title'] ) ? trim( (string) $group['title'] ) : '';
            $links = $this->parse_link_lines( isset( $group['links'] ) ? (string) $group['links'] : '' );
            if ( '' === $title && empty( $links ) ) {
                continue;
            }

            $normalized[] = array(
                'title' => $title,
                'links' => $links,
            );
        }

        if ( ! empty( $normalized ) ) {
            return $normalized;
        }

        $quick_links = $this->option( 'footer_quick_links', array() );
        if ( is_array( $quick_links ) && ! empty( $quick_links ) ) {
            $links = array();
            foreach ( $quick_links as $link ) {
                if ( ! is_array( $link ) ) {
                    continue;
                }
                $label = isset( $link['text'] ) ? $this->translate_text( (string) $link['text'] ) : '';
                $url   = isset( $link['url'] ) ? (string) $link['url'] : '#';
                if ( '' !== trim( $label ) ) {
                    $links[] = $this->normalize_link( $label, $url );
                }
            }

            if ( ! empty( $links ) ) {
                return array(
                    array(
                        'title' => $this->option_text( 'footer_links_title', __( '快速链接', 'developer-starter' ) ),
                        'links' => $links,
                    ),
                );
            }
        }

        return array(
            array(
                'title' => __( '快速链接', 'developer-starter' ),
                'links' => array(
                    $this->normalize_link( __( '首页', 'developer-starter' ), home_url( '/' ) ),
                ),
            ),
        );
    }

    private function get_contact_items( $data ) {
        $raw = $this->string_value( $data, 'qfs_contact_items', '' );
        if ( '' !== trim( $raw ) ) {
            return $this->parse_contact_lines( $raw );
        }

        $items = array();
        $phone = $this->option_text( 'company_phone', '' );
        if ( '' !== $phone ) {
            $items[] = array(
                'label' => __( '电话', 'developer-starter' ),
                'text'  => $phone,
                'url'   => 'tel:' . preg_replace( '/[^0-9\+]/', '', $phone ),
            );
        }

        $qq = $this->option_text( 'company_qq', '' );
        if ( '' !== $qq ) {
            $items[] = array(
                'label' => 'QQ',
                'text'  => $qq,
                'url'   => function_exists( 'developer_starter_get_qq_contact_link' ) ? developer_starter_get_qq_contact_link( $qq ) : '',
            );
        }

        $email = $this->option_text( 'company_email', '' );
        if ( '' !== $email ) {
            $items[] = array(
                'label' => __( '邮箱', 'developer-starter' ),
                'text'  => $email,
                'url'   => 'mailto:' . $email,
            );
        }

        $address = $this->option_text( 'company_address', '' );
        if ( '' !== $address ) {
            $items[] = array(
                'label' => __( '地址', 'developer-starter' ),
                'text'  => $address,
                'url'   => '',
            );
        }

        $hours = $this->option_text( 'company_working_hours', '' );
        if ( '' !== $hours ) {
            $items[] = array(
                'label' => __( '时间', 'developer-starter' ),
                'text'  => $hours,
                'url'   => '',
            );
        }

        return $items;
    }

    private function get_qr_items( $data ) {
        $items = isset( $data['qfs_qr_items'] ) && is_array( $data['qfs_qr_items'] ) ? $data['qfs_qr_items'] : array();
        $normalized = array();

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $image = $this->resolve_media_url( isset( $item['image'] ) ? (string) $item['image'] : '' );
            if ( '' === $image ) {
                continue;
            }

            $normalized[] = array(
                'title' => isset( $item['title'] ) ? sanitize_text_field( (string) $item['title'] ) : '',
                'image' => $image,
                'text'  => isset( $item['text'] ) ? sanitize_text_field( (string) $item['text'] ) : '',
            );
        }

        if ( ! empty( $normalized ) ) {
            return $normalized;
        }

        $contact_qr = $this->resolve_media_url( $this->option( 'company_wechat_qrcode', '' ) );
        if ( '' !== $contact_qr ) {
            $normalized[] = array(
                'title' => __( '联系微信', 'developer-starter' ),
                'image' => $contact_qr,
                'text'  => __( '扫码添加微信', 'developer-starter' ),
            );
        }

        $wechat_qr = $this->resolve_media_url( $this->option( 'wechat_qrcode', '' ) );
        if ( $wechat_qr === $contact_qr ) {
            $wechat_qr = '';
        }
        if ( '' !== $wechat_qr ) {
            $normalized[] = array(
                'title' => __( '微信公众号', 'developer-starter' ),
                'image' => $wechat_qr,
                'text'  => $this->option_text( 'wechat_qr_text', __( '扫码关注公众号', 'developer-starter' ) ),
            );
        }

        $douyin_qr = $this->resolve_media_url( $this->option( 'douyin_qrcode', '' ) );
        if ( '' !== $douyin_qr ) {
            $normalized[] = array(
                'title' => __( '抖音', 'developer-starter' ),
                'image' => $douyin_qr,
                'text'  => $this->option_text( 'douyin_qr_text', __( '扫码关注抖音', 'developer-starter' ) ),
            );
        }

        return $normalized;
    }

    private function get_friend_links( $data ) {
        $mode = $this->sanitize_choice( $this->string_value( $data, 'qfs_friend_mode', 'theme_home' ), array( 'theme_home', 'custom_home', 'custom_all', 'none' ), 'theme_home' );
        if ( 'none' === $mode ) {
            return array();
        }

        if ( in_array( $mode, array( 'theme_home', 'custom_home' ), true ) && function_exists( 'is_front_page' ) && ! is_front_page() ) {
            return array();
        }

        if ( 'theme_home' === $mode ) {
            if ( ! $this->option( 'friend_links_enable', '' ) ) {
                return array();
            }

            $theme_links = $this->option( 'friend_links', array() );
            if ( ! is_array( $theme_links ) ) {
                return array();
            }

            $links = array();
            foreach ( $theme_links as $link ) {
                if ( ! is_array( $link ) ) {
                    continue;
                }
                $label = isset( $link['text'] ) ? $this->translate_text( (string) $link['text'] ) : '';
                $url   = isset( $link['url'] ) ? (string) $link['url'] : '#';
                if ( '' !== trim( $label ) ) {
                    $links[] = $this->normalize_link( $label, $url, '_blank' );
                }
            }
            return $links;
        }

        return $this->parse_link_lines( $this->string_value( $data, 'qfs_friend_links', '' ), '_blank' );
    }

    private function get_bottom_text( $data ) {
        $bottom_text = $this->string_value( $data, 'qfs_bottom_text', '' );
        if ( '' !== trim( $bottom_text ) ) {
            return $bottom_text;
        }

        $copyright = $this->option_text( 'footer_copyright', '' );
        if ( '' !== $copyright ) {
            return $copyright;
        }

        return '&copy; ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '. ' . esc_html__( '版权所有', 'developer-starter' ) . '.';
    }

    private function parse_link_lines( $value, $target = '_self' ) {
        $links = array();
        $lines = preg_split( '/\r\n|\r|\n|\|\|/', (string) $value );
        if ( ! is_array( $lines ) ) {
            return $links;
        }

        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( '' === $line ) {
                continue;
            }

            $parts = array_map( 'trim', explode( '|', $line ) );
            $label = isset( $parts[0] ) ? $parts[0] : '';
            $url   = isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] : '#';
            $item_target = isset( $parts[2] ) && '_blank' === $parts[2] ? '_blank' : $target;
            if ( '' !== $label ) {
                $links[] = $this->normalize_link( $label, $url, $item_target );
            }
        }

        return $links;
    }

    private function parse_contact_lines( $value ) {
        $items = array();
        $lines = preg_split( '/\r\n|\r|\n|\|\|/', (string) $value );
        if ( ! is_array( $lines ) ) {
            return $items;
        }

        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( '' === $line ) {
                continue;
            }

            $parts = array_map( 'trim', explode( '|', $line ) );
            $label = isset( $parts[0] ) ? $parts[0] : '';
            $text  = isset( $parts[1] ) ? $parts[1] : '';
            $url   = isset( $parts[2] ) ? $parts[2] : '';
            if ( '' === $text && '' !== $label ) {
                $text  = $label;
                $label = '';
            }
            if ( '' !== $text ) {
                $items[] = array(
                    'label' => sanitize_text_field( $label ),
                    'text'  => sanitize_text_field( $text ),
                    'url'   => esc_url_raw( $url, array( 'http', 'https', 'mailto', 'tel' ) ),
                );
            }
        }

        return $items;
    }

    private function render_link_list( $links, $class, $default_target ) {
        if ( empty( $links ) || ! is_array( $links ) ) {
            return;
        }

        echo '<ul class="' . esc_attr( $class ) . '">';
        foreach ( $links as $link ) {
            echo '<li>';
            $this->render_anchor( $link, $default_target, false );
            echo '</li>';
        }
        echo '</ul>';
    }

    private function render_anchor( $link, $default_target = '_self', $friend = false ) {
        if ( ! is_array( $link ) || empty( $link['label'] ) ) {
            return;
        }

        $url = isset( $link['url'] ) && '' !== (string) $link['url'] ? (string) $link['url'] : '#';
        $target = isset( $link['target'] ) && '_blank' === $link['target'] ? '_blank' : $default_target;
        $rel = '_blank' === $target ? ( $friend ? 'external nofollow noopener noreferrer' : 'noopener noreferrer' ) : '';

        echo '<a href="' . esc_url( $url ) . '"';
        if ( '_blank' === $target ) {
            echo ' target="_blank"';
        }
        if ( '' !== $rel ) {
            echo ' rel="' . esc_attr( $rel ) . '"';
        }
        echo '>' . esc_html( (string) $link['label'] ) . '</a>';
    }

    private function normalize_link( $label, $url, $target = '_self' ) {
        $target = '_blank' === $target ? '_blank' : '_self';

        return array(
            'label'  => sanitize_text_field( (string) $label ),
            'url'    => '' !== trim( (string) $url ) ? trim( (string) $url ) : '#',
            'target' => $target,
        );
    }

    private function string_value( $data, $key, $default = '' ) {
        if ( ! is_array( $data ) || ! isset( $data[ $key ] ) || ! is_scalar( $data[ $key ] ) ) {
            return (string) $default;
        }

        return (string) $data[ $key ];
    }

    private function option( $key, $default = '' ) {
        return function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( $key, $default ) : $default;
    }

    private function option_text( $key, $default = '' ) {
        $value = $this->option( $key, $default );
        if ( ! is_scalar( $value ) ) {
            return (string) $default;
        }

        return $this->translate_text( (string) $value );
    }

    private function translate_text( $text ) {
        return function_exists( 'developer_starter_translate_theme_option_text' )
            ? developer_starter_translate_theme_option_text( (string) $text )
            : (string) $text;
    }

    private function resolve_media_url( $value ) {
        if ( function_exists( 'developer_starter_get_media_url' ) ) {
            return (string) developer_starter_get_media_url( $value );
        }

        return is_scalar( $value ) ? (string) $value : '';
    }

    private function sanitize_choice( $value, $allowed, $default ) {
        $value = sanitize_key( (string) $value );
        return in_array( $value, $allowed, true ) ? $value : $default;
    }

    private function sanitize_spacing_value( $value, $default ) {
        if ( class_exists( Module_Manager::class ) ) {
            $value = Module_Manager::sanitize_spacing_value( $value );
            return '' !== $value ? $value : $default;
        }

        $value = trim( wp_strip_all_tags( (string) $value ) );
        return preg_match( '/[;{}<>]/', $value ) ? $default : $value;
    }

    private function sanitize_css_color_value( $value, $default ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return $default;
        }

        $lower = strtolower( $value );
        if ( in_array( $lower, array( 'transparent', 'currentcolor', 'inherit', 'initial' ), true ) ) {
            return $lower;
        }

        if ( function_exists( 'sanitize_hex_color' ) ) {
            $hex = sanitize_hex_color( $value );
            if ( '' !== $hex && null !== $hex ) {
                return $hex;
            }
        } elseif ( preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\([0-9\.\,\s%]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(?:linear-gradient|radial-gradient|conic-gradient)\([a-z0-9#\-\+\*\/%\.,\s\(\)]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9\-_]+\)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    private function inherit_footer_color_value( $data, $key, $inherit_value, array $legacy_defaults ) {
        $value = trim( $this->string_value( $data, $key, '' ) );
        if ( '' === $value ) {
            return $inherit_value;
        }

        $normalized_value    = preg_replace( '/\s+/', '', strtolower( $value ) );
        $normalized_defaults = array_map(
            static function ( $item ) {
                return preg_replace( '/\s+/', '', strtolower( (string) $item ) );
            },
            $legacy_defaults
        );

        return in_array( $normalized_value, $normalized_defaults, true ) ? $inherit_value : $value;
    }

    private function build_css_var_style( $variables ) {
        $declarations = array();
        foreach ( (array) $variables as $property => $value ) {
            $property = trim( (string) $property );
            $value    = trim( (string) $value );
            if ( '' === $property || '' === $value || ! preg_match( '/^--(?:qfs|qiling-component)-[a-z0-9-]+$/', $property ) || preg_match( '/[;{}<>]/', $value ) ) {
                continue;
            }
            $declarations[] = $property . ': ' . $value;
        }

        return implode( '; ', $declarations );
    }

    private function prepare_custom_css( $css, $module_id ) {
        $css = trim( wp_strip_all_tags( (string) $css ) );
        if ( '' === $css ) {
            return '';
        }

        $css = str_ireplace( array( '</style', '<style', '</script', '<script' ), '', $css );
        $clean_css = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $css );
        if ( is_string( $clean_css ) ) {
            $css = $clean_css;
        }
        $css = str_replace( '{{WRAPPER}}', '#' . $module_id, (string) $css );

        return trim( $css );
    }
}
