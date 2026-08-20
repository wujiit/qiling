<?php
/**
 * Micro Journal Stream Module - 时间流/日记流
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Micro_Journal_Stream_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-clock';
        $this->description = __( '碎片化时间流更新，不是长文文章', 'developer-starter' );
    }

    public function get_id() {
        return 'micro_journal_stream';
    }

    public function get_name() {
        return __( '时间流/日记流', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'mjs_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( '时间流 / 日记流', 'developer-starter' ),
            ),
            array(
                'id'    => 'mjs_subtitle',
                'type'  => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
            ),
            array(
                'id'      => 'mjs_limit',
                'type'    => 'number',
                'label'   => __( '最多显示条数', 'developer-starter' ),
                'default' => '20',
            ),
            array(
                'id'      => 'mjs_show_image',
                'type'    => 'select',
                'label'   => __( '显示配图', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array( 'id' => 'mjs_link_text', 'type' => 'text', 'label' => __( '外部链接文案', 'developer-starter' ), 'default' => __( '查看相关链接', 'developer-starter' ) ),
            array(
                'id'     => 'mjs_items',
                'type'   => 'repeater',
                'label'  => __( '时间流条目', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'date',
                        'type'  => 'text',
                        'label' => __( '日期 (如 2026-02-12)', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'time',
                        'type'  => 'text',
                        'label' => __( '时间 (如 14:30)', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'content',
                        'type'  => 'textarea',
                        'label' => __( '内容', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'mood',
                        'type'    => 'text',
                        'label'   => __( '心情', 'developer-starter' ),
                        'default' => '🙂',
                    ),
                    array(
                        'id'    => 'location',
                        'type'  => 'text',
                        'label' => __( '位置', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '配图', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'link',
                        'type'  => 'text',
                        'label' => __( '外部链接', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'mjs_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'mjs_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'mjs_line_color',
                'type'    => 'color',
                'label'   => __( '时间线颜色', 'developer-starter' ),
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
        $title = isset( $data['mjs_title'] ) ? $data['mjs_title'] : '';
        $subtitle = isset( $data['mjs_subtitle'] ) ? $data['mjs_subtitle'] : '';
        $limit = isset( $data['mjs_limit'] ) ? max( 1, min( 100, intval( $data['mjs_limit'] ) ) ) : 20;
        $show_image = ! isset( $data['mjs_show_image'] ) || $data['mjs_show_image'] === 'yes';
        $link_text = isset( $data['mjs_link_text'] ) && '' !== trim( (string) $data['mjs_link_text'] ) ? (string) $data['mjs_link_text'] : __( '查看相关链接', 'developer-starter' );

        $items = isset( $data['mjs_items'] ) && is_array( $data['mjs_items'] ) ? $data['mjs_items'] : array();
        if ( empty( $items ) ) {
            $date_format = function_exists( 'developer_starter_get_date_time_format' ) ? developer_starter_get_date_time_format( false ) : get_option( 'date_format' );
            $items = array(
                array(
                    'date'     => date_i18n( $date_format ),
                    'time'     => date_i18n( 'H:i' ),
                    'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '今天把首页模块又简化了一轮，加载速度更稳。', 'Simplified the homepage modules again today and improved load consistency.' ) : __( '今天把首页模块又简化了一轮，加载速度更稳。', 'developer-starter' ),
                    'mood'     => '⚡',
                    'location' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '工作室', 'Studio' ) : __( '工作室', 'developer-starter' ),
                    'image'    => '',
                    'link'     => '',
                ),
                array(
                    'date'     => date_i18n( $date_format, strtotime( '-1 day' ) ),
                    'time'     => '22:10',
                    'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '读完一本关于写作的书，准备把灵感整理成新文章。', 'Finished a book on writing and started shaping the ideas into a new article.' ) : __( '读完一本关于写作的书，准备把灵感整理成新文章。', 'developer-starter' ),
                    'mood'     => '📚',
                    'location' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '书房', 'Study' ) : __( '书房', 'developer-starter' ),
                    'image'    => '',
                    'link'     => '',
                ),
                array(
                    'date'     => date_i18n( $date_format, strtotime( '-2 day' ) ),
                    'time'     => '09:40',
                    'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '测试了新的评论交互，准备继续优化读者体验。', 'Tested a new comment interaction flow and will keep refining the reader experience.' ) : __( '测试了新的评论交互，准备继续优化读者体验。', 'developer-starter' ),
                    'mood'     => '🛠️',
                    'location' => '',
                    'image'    => '',
                    'link'     => '',
                ),
            );
        }

        $items = array_slice( $items, 0, $limit );
        if ( empty( $items ) ) {
            return;
        }

        $card_bg = isset( $data['mjs_card_bg'] ) && $data['mjs_card_bg'] !== '' ? $data['mjs_card_bg'] : 'var(--color-neutral-0)';
        $bg_color = isset( $data['mjs_bg_color'] ) && $data['mjs_bg_color'] !== '' ? $data['mjs_bg_color'] : 'var(--color-neutral-50)';
        $line_color = isset( $data['mjs_line_color'] ) && $data['mjs_line_color'] !== '' ? $data['mjs_line_color'] : 'var(--color-primary)';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '56px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '56px';

        $module_id = 'micro-journal-' . uniqid();
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-micro-journal-stream" style="padding-top: <?php echo esc_attr( $pt ); ?>; padding-bottom: <?php echo esc_attr( $pb ); ?>; background: <?php echo esc_attr( $bg_color ); ?>;">
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

                <div class="qil-mjs-list" style="--qil-mjs-card-bg: <?php echo esc_attr( $card_bg ); ?>; --qil-mjs-line: <?php echo esc_attr( $line_color ); ?>;">
                    <?php foreach ( $items as $index => $item ) : ?>
                        <?php
                        if ( ! is_array( $item ) ) {
                            continue;
                        }
                        $date = isset( $item['date'] ) ? trim( (string) $item['date'] ) : '';
                        $time = isset( $item['time'] ) ? trim( (string) $item['time'] ) : '';
                        $content = isset( $item['content'] ) ? trim( (string) $item['content'] ) : '';
                        $mood = isset( $item['mood'] ) && $item['mood'] !== '' ? (string) $item['mood'] : '📝';
                        $location = isset( $item['location'] ) ? trim( (string) $item['location'] ) : '';
                        $image = isset( $item['image'] ) ? trim( (string) $item['image'] ) : '';
                        $link = isset( $item['link'] ) ? trim( (string) $item['link'] ) : '';
                        if ( $content === '' ) {
                            continue;
                        }
                        ?>
                        <article class="qil-mjs-item">
                            <div class="qil-mjs-point"></div>
                            <div class="qil-mjs-card">
                                <div class="qil-mjs-head">
                                    <span class="qil-mjs-mood"><?php echo esc_html( $mood ); ?></span>
                                    <span class="qil-mjs-datetime"><?php echo esc_html( trim( $date . ' ' . $time ) ); ?></span>
                                </div>
                                <div class="qil-mjs-content"><?php echo nl2br( esc_html( $content ) ); ?></div>
                                <?php if ( $location !== '' ) : ?>
                                    <div class="qil-mjs-location"><?php echo esc_html__( '位置：', 'developer-starter' ) . esc_html( $location ); ?></div>
                                <?php endif; ?>
                                <?php if ( $show_image && $image !== '' ) : ?>
                                    <img class="qil-mjs-image" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $date ); ?>" />
                                <?php endif; ?>
                                <?php if ( $link !== '' ) : ?>
                                    <a class="qil-mjs-link" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $link_text ); ?></a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
