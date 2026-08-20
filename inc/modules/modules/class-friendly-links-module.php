<?php
/**
 * Friendly Links Module - 友链/推荐博客
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Friendly_Links_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-admin-links';
        $this->description = __( '展示友链与推荐博客列表', 'developer-starter' );
    }

    public function get_id() {
        return 'friendly_links';
    }

    public function get_name() {
        return __( '友链/推荐博客', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'fl_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( '友链 / 推荐博客', 'developer-starter' ),
            ),
            array(
                'id'    => 'fl_subtitle',
                'type'  => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
            ),
            array(
                'id'      => 'fl_columns',
                'type'    => 'select',
                'label'   => __( '每行列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'fl_show_desc',
                'type'    => 'select',
                'label'   => __( '显示简介', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'fl_show_domain',
                'type'    => 'select',
                'label'   => __( '显示域名', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'     => 'fl_items',
                'type'   => 'repeater',
                'label'  => __( '友链列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'label' => __( '链接', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'logo',
                        'type'  => 'image',
                        'label' => __( 'Logo', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'text',
                        'label' => __( '一句话介绍', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'tag',
                        'type'  => 'text',
                        'label' => __( '标签', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'target',
                        'type'    => 'select',
                        'label'   => __( '打开方式', 'developer-starter' ),
                        'options' => array(
                            '_blank' => __( '新窗口', 'developer-starter' ),
                            '_self'  => __( '当前窗口', 'developer-starter' ),
                        ),
                        'default' => '_blank',
                    ),
                ),
            ),
            array(
                'id'      => 'fl_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'fl_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'fl_accent_color',
                'type'    => 'color',
                'label'   => __( '强调色', 'developer-starter' ),
                'default' => 'var(--color-primary)',
            ),
            array(
                'id'          => 'fl_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '控制友链标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
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
        $title = isset( $data['fl_title'] ) ? $data['fl_title'] : '';
        $subtitle = isset( $data['fl_subtitle'] ) ? $data['fl_subtitle'] : '';
        $columns = isset( $data['fl_columns'] ) ? max( 2, min( 4, intval( $data['fl_columns'] ) ) ) : 3;
        $show_desc = ! isset( $data['fl_show_desc'] ) || $data['fl_show_desc'] === 'yes';
        $show_domain = ! isset( $data['fl_show_domain'] ) || $data['fl_show_domain'] === 'yes';

        $items = isset( $data['fl_items'] ) && is_array( $data['fl_items'] ) ? $data['fl_items'] : array();
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'name'   => __( 'WordPress', 'developer-starter' ),
                    'url'    => 'https://wordpress.org/',
                    'logo'   => '',
                    'desc'   => __( '开源内容管理系统社区', 'developer-starter' ),
                    'tag'    => 'CMS',
                    'target' => '_blank',
                ),
                array(
                    'name'   => __( 'OpenAI', 'developer-starter' ),
                    'url'    => 'https://openai.com/',
                    'logo'   => '',
                    'desc'   => __( 'AI 技术与产品实践', 'developer-starter' ),
                    'tag'    => 'AI',
                    'target' => '_blank',
                ),
                array(
                    'name'   => __( 'MDN Web Docs', 'developer-starter' ),
                    'url'    => 'https://developer.mozilla.org/',
                    'logo'   => '',
                    'desc'   => __( '前端开发参考文档', 'developer-starter' ),
                    'tag'    => 'Docs',
                    'target' => '_blank',
                ),
            );
        }

        $card_bg = isset( $data['fl_card_bg'] ) && $data['fl_card_bg'] !== '' ? $data['fl_card_bg'] : 'var(--color-neutral-0)';
        $bg_color = isset( $data['fl_bg_color'] ) && $data['fl_bg_color'] !== '' ? $data['fl_bg_color'] : 'var(--color-neutral-50)';
        $accent_color = isset( $data['fl_accent_color'] ) && $data['fl_accent_color'] !== '' ? $data['fl_accent_color'] : 'var(--color-primary)';
        $badge_bg = isset( $data['fl_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['fl_badge_bg'] ) ) : '';
        $badge_bg = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '56px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '56px';

        $module_id = 'friendly-links-' . uniqid();
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-friendly-links" style="padding-top: <?php echo esc_attr( $pt ); ?>; padding-bottom: <?php echo esc_attr( $pb ); ?>; background: <?php echo esc_attr( $bg_color ); ?>;<?php echo '' !== $badge_bg ? esc_attr( '--qiling-component-badge-bg: ' . $badge_bg . ';' ) : ''; ?>">
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

                <div class="qil-fl-grid qil-fl-cols-<?php echo esc_attr( (string) $columns ); ?>" style="--qil-fl-card-bg: <?php echo esc_attr( $card_bg ); ?>; --qil-fl-accent: <?php echo esc_attr( $accent_color ); ?>;">
                    <?php foreach ( $items as $item ) : ?>
                        <?php
                        if ( ! is_array( $item ) ) {
                            continue;
                        }
                        $name = isset( $item['name'] ) ? trim( (string) $item['name'] ) : '';
                        $url = isset( $item['url'] ) ? trim( (string) $item['url'] ) : '';
                        $logo = isset( $item['logo'] ) ? trim( (string) $item['logo'] ) : '';
                        $desc = isset( $item['desc'] ) ? trim( (string) $item['desc'] ) : '';
                        $tag = isset( $item['tag'] ) ? trim( (string) $item['tag'] ) : '';
                        $target = isset( $item['target'] ) && $item['target'] === '_self' ? '_self' : '_blank';
                        if ( $name === '' || $url === '' ) {
                            continue;
                        }
                        $domain = wp_parse_url( $url, PHP_URL_HOST );
                        ?>
                        <a class="qil-fl-card" href="<?php echo esc_url( $url ); ?>" target="<?php echo esc_attr( $target ); ?>" rel="<?php echo esc_attr( $target === '_blank' ? 'noopener nofollow' : 'noopener' ); ?>">
                            <div class="qil-fl-head">
                                <div class="qil-fl-logo-wrap">
                                    <?php if ( $logo ) : ?>
                                        <img class="qil-fl-logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                                    <?php else : ?>
                                        <span class="qil-fl-logo-fallback"><?php echo esc_html( $this->extract_initial( $name ) ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="qil-fl-meta">
                                    <h3 class="qil-fl-name"><?php echo esc_html( $name ); ?></h3>
                                    <?php if ( $show_domain && $domain ) : ?>
                                        <div class="qil-fl-domain"><?php echo esc_html( $domain ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ( $show_desc && $desc !== '' ) : ?>
                                <p class="qil-fl-desc"><?php echo esc_html( $desc ); ?></p>
                            <?php endif; ?>

                            <?php if ( $tag !== '' ) : ?>
                                <span class="qil-fl-tag"><?php echo esc_html( $tag ); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function extract_initial( $name ) {
        $name = trim( (string) $name );
        if ( $name === '' ) {
            return 'L';
        }

        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $name, 0, 1 );
        }

        return strtoupper( substr( $name, 0, 1 ) );
    }
}
