<?php
/**
 * Knowledge Cards Module - 知识点卡
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Knowledge_Cards_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-welcome-learn-more';
        $this->description = __( '用于展示术语解释、知识点、误区和实践建议', 'developer-starter' );
    }

    public function get_id() {
        return 'knowledge_cards';
    }

    public function get_name() {
        return __( '知识点卡', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'kc_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( '知识点卡', 'developer-starter' ),
            ),
            array(
                'id'    => 'kc_subtitle',
                'type'  => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
            ),
            array(
                'id'      => 'kc_columns',
                'type'    => 'select',
                'label'   => __( '每行列数', 'developer-starter' ),
                'options' => array(
                    '1' => __( '1列', 'developer-starter' ),
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'kc_show_number',
                'type'    => 'select',
                'label'   => __( '显示序号徽标', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'kc_show_example',
                'type'    => 'select',
                'label'   => __( '显示实践示例', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'kc_show_mistake',
                'type'    => 'select',
                'label'   => __( '显示常见误区', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'kc_show_link',
                'type'    => 'select',
                'label'   => __( '显示扩展链接', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'     => 'kc_items',
                'type'   => 'repeater',
                'label'  => __( '知识点列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'      => 'term',
                        'type'    => 'text',
                        'label'   => __( '术语/知识点名称', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'definition',
                        'type'    => 'textarea',
                        'label'   => __( '一句话定义', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'importance',
                        'type'    => 'select',
                        'label'   => __( '重要级别', 'developer-starter' ),
                        'options' => array(
                            'high'   => __( '高', 'developer-starter' ),
                            'medium' => __( '中', 'developer-starter' ),
                            'low'    => __( '低', 'developer-starter' ),
                        ),
                        'default' => 'medium',
                    ),
                    array(
                        'id'    => 'example',
                        'type'  => 'textarea',
                        'label' => __( '实践示例', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'mistake',
                        'type'  => 'text',
                        'label' => __( '常见误区', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'link_text',
                        'type'  => 'text',
                        'label' => __( '链接文案', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'link_url',
                        'type'  => 'text',
                        'label' => __( '链接地址', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'kc_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'kc_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'kc_accent_color',
                'type'    => 'color',
                'label'   => __( '强调色', 'developer-starter' ),
                'default' => 'var(--color-primary)',
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
        $title = isset( $data['kc_title'] ) ? $data['kc_title'] : '';
        $subtitle = isset( $data['kc_subtitle'] ) ? $data['kc_subtitle'] : '';
        $columns = isset( $data['kc_columns'] ) ? max( 1, min( 3, intval( $data['kc_columns'] ) ) ) : 3;
        $show_number = ! isset( $data['kc_show_number'] ) || $data['kc_show_number'] === 'yes';
        $show_example = ! isset( $data['kc_show_example'] ) || $data['kc_show_example'] === 'yes';
        $show_mistake = ! isset( $data['kc_show_mistake'] ) || $data['kc_show_mistake'] === 'yes';
        $show_link = ! isset( $data['kc_show_link'] ) || $data['kc_show_link'] === 'yes';

        $items = isset( $data['kc_items'] ) && is_array( $data['kc_items'] ) ? $data['kc_items'] : array();
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'term'       => 'LCP',
                    'definition' => __( '首屏最大可见内容绘制时间，越短通常代表首屏体验越好。', 'developer-starter' ),
                    'importance' => 'high',
                    'example'    => __( '压缩首图、预加载首屏关键资源可改善 LCP。', 'developer-starter' ),
                    'mistake'    => __( '只优化跑分，不关注真实用户网络环境。', 'developer-starter' ),
                    'link_text'  => __( '查看性能优化建议', 'developer-starter' ),
                    'link_url'   => '',
                ),
                array(
                    'term'       => 'FAQ Schema',
                    'definition' => __( '用结构化数据标注问答内容，帮助搜索引擎更好理解页面。', 'developer-starter' ),
                    'importance' => 'medium',
                    'example'    => __( '把高频问题整理成标准问答并保持与正文一致。', 'developer-starter' ),
                    'mistake'    => __( '堆砌无关问题或与页面主体内容不一致。', 'developer-starter' ),
                    'link_text'  => __( '查看 FAQ 实践', 'developer-starter' ),
                    'link_url'   => '',
                ),
                array(
                    'term'       => 'GEO',
                    'definition' => __( '通过结构化和高质量内容，提升页面被 AI 系统理解与引用的概率。', 'developer-starter' ),
                    'importance' => 'high',
                    'example'    => __( '清晰标题结构、摘要、关键结论有助于 AI 抽取。', 'developer-starter' ),
                    'mistake'    => __( '关键词堆砌和低质量改写会降低可用性。', 'developer-starter' ),
                    'link_text'  => __( '查看 GEO 指南', 'developer-starter' ),
                    'link_url'   => '',
                ),
            );
        }

        $display_items = array();
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $term = isset( $item['term'] ) ? trim( (string) $item['term'] ) : '';
            $definition = isset( $item['definition'] ) ? trim( (string) $item['definition'] ) : '';
            if ( $term === '' && $definition === '' ) {
                continue;
            }
            $display_items[] = $item;
        }

        if ( empty( $display_items ) ) {
            return;
        }

        $card_bg = isset( $data['kc_card_bg'] ) && $data['kc_card_bg'] !== '' ? $data['kc_card_bg'] : 'var(--color-neutral-0)';
        $bg_color = isset( $data['kc_bg_color'] ) && $data['kc_bg_color'] !== '' ? $data['kc_bg_color'] : 'var(--color-neutral-50)';
        $accent_color = isset( $data['kc_accent_color'] ) && $data['kc_accent_color'] !== '' ? $data['kc_accent_color'] : 'var(--color-primary)';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '56px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '56px';

        $module_id = 'knowledge-cards-' . uniqid();
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-knowledge-cards" style="padding-top: <?php echo esc_attr( $pt ); ?>; padding-bottom: <?php echo esc_attr( $pb ); ?>; background: <?php echo esc_attr( $bg_color ); ?>;">
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

                <div class="qil-kc-grid qil-kc-cols-<?php echo esc_attr( (string) $columns ); ?>" style="--qil-kc-card-bg: <?php echo esc_attr( $card_bg ); ?>; --qil-kc-accent: <?php echo esc_attr( $accent_color ); ?>;">
                    <?php foreach ( $display_items as $index => $item ) : ?>
                        <?php
                        $term = isset( $item['term'] ) ? trim( (string) $item['term'] ) : '';
                        $definition = isset( $item['definition'] ) ? trim( (string) $item['definition'] ) : '';
                        $importance = isset( $item['importance'] ) ? (string) $item['importance'] : 'medium';
                        $example = isset( $item['example'] ) ? trim( (string) $item['example'] ) : '';
                        $mistake = isset( $item['mistake'] ) ? trim( (string) $item['mistake'] ) : '';
                        $link_text = isset( $item['link_text'] ) ? trim( (string) $item['link_text'] ) : '';
                        $link_url = isset( $item['link_url'] ) ? trim( (string) $item['link_url'] ) : '';
                        if ( $term === '' && $definition === '' ) {
                            continue;
                        }
                        ?>
                        <article class="qil-kc-card">
                            <div class="qil-kc-head">
                                <?php if ( $show_number ) : ?>
                                    <span class="qil-kc-no"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
                                <?php endif; ?>
                                <?php if ( $term !== '' ) : ?>
                                    <h3 class="qil-kc-term"><?php echo esc_html( $term ); ?></h3>
                                <?php endif; ?>
                                <span class="qil-kc-level is-<?php echo esc_attr( $importance ); ?>"><?php echo esc_html( $this->get_level_label( $importance ) ); ?></span>
                            </div>

                            <?php if ( $definition !== '' ) : ?>
                                <p class="qil-kc-definition"><?php echo esc_html( $definition ); ?></p>
                            <?php endif; ?>

                            <?php if ( $show_example && $example !== '' ) : ?>
                                <div class="qil-kc-row">
                                    <strong><?php echo esc_html__( '示例：', 'developer-starter' ); ?></strong>
                                    <span><?php echo esc_html( $example ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $show_mistake && $mistake !== '' ) : ?>
                                <div class="qil-kc-row is-mistake">
                                    <strong><?php echo esc_html__( '误区：', 'developer-starter' ); ?></strong>
                                    <span><?php echo esc_html( $mistake ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $show_link && $link_text !== '' && $link_url !== '' ) : ?>
                                <a class="qil-kc-link" href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $link_text ); ?></a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function get_level_label( $importance ) {
        if ( $importance === 'high' ) {
            return __( '高优先', 'developer-starter' );
        }
        if ( $importance === 'low' ) {
            return __( '基础', 'developer-starter' );
        }
        return __( '重点', 'developer-starter' );
    }
}
