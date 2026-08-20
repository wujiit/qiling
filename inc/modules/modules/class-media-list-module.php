<?php
/**
 * Media List Module - 读书/观影/听歌清单
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Media_List_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-format-audio';
        $this->description = __( '展示读书、观影、听歌清单与进度', 'developer-starter' );
    }

    public function get_id() {
        return 'media_list';
    }

    public function get_name() {
        return __( '读书/观影/听歌清单', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'ml_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( '读书 / 观影 / 听歌清单', 'developer-starter' ),
            ),
            array(
                'id'    => 'ml_subtitle',
                'type'  => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
            ),
            array(
                'id'      => 'ml_filter',
                'type'    => 'select',
                'label'   => __( '显示类型', 'developer-starter' ),
                'options' => array(
                    'all'   => __( '全部', 'developer-starter' ),
                    'book'  => __( '仅图书', 'developer-starter' ),
                    'movie' => __( '仅影视', 'developer-starter' ),
                    'music' => __( '仅音乐', 'developer-starter' ),
                ),
                'default' => 'all',
            ),
            array(
                'id'      => 'ml_columns',
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
                'id'      => 'ml_show_note',
                'type'    => 'select',
                'label'   => __( '显示短评', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'ml_show_link',
                'type'    => 'select',
                'label'   => __( '显示详情链接', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array( 'id' => 'ml_link_text', 'type' => 'text', 'label' => __( '详情链接文案', 'developer-starter' ), 'default' => __( '查看详情', 'developer-starter' ) ),
            array(
                'id'     => 'ml_items',
                'type'   => 'repeater',
                'label'  => __( '清单条目', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'      => 'type',
                        'type'    => 'select',
                        'label'   => __( '类型', 'developer-starter' ),
                        'options' => array(
                            'book'  => __( '图书', 'developer-starter' ),
                            'movie' => __( '影视', 'developer-starter' ),
                            'music' => __( '音乐', 'developer-starter' ),
                        ),
                        'default' => 'book',
                    ),
                    array(
                        'id'    => 'cover',
                        'type'  => 'image',
                        'label' => __( '封面', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'creator',
                        'type'  => 'text',
                        'label' => __( '作者/导演/歌手', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'status',
                        'type'    => 'select',
                        'label'   => __( '状态', 'developer-starter' ),
                        'options' => array(
                            'done'  => __( '已完成', 'developer-starter' ),
                            'doing' => __( '进行中', 'developer-starter' ),
                            'wish'  => __( '想看/想读/想听', 'developer-starter' ),
                        ),
                        'default' => 'done',
                    ),
                    array(
                        'id'      => 'score',
                        'type'    => 'number',
                        'label'   => __( '评分 (1-10)', 'developer-starter' ),
                        'default' => '8',
                    ),
                    array(
                        'id'    => 'progress',
                        'type'  => 'text',
                        'label' => __( '进度描述', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'link',
                        'type'  => 'text',
                        'label' => __( '详情链接', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'note',
                        'type'  => 'textarea',
                        'label' => __( '短评', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'ml_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'ml_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'ml_accent_color',
                'type'    => 'color',
                'label'   => __( '强调色', 'developer-starter' ),
                'default' => 'var(--color-primary)',
            ),
            array(
                'id'      => 'ml_badge_bg',
                'type'    => 'color',
                'label'   => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '控制类型标签和状态徽章，留空时保留状态默认色并跟随全局徽章颜色。', 'developer-starter' ),
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
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        $title = isset( $data['ml_title'] ) ? $data['ml_title'] : '';
        $subtitle = isset( $data['ml_subtitle'] ) ? $data['ml_subtitle'] : '';
        $filter = isset( $data['ml_filter'] ) ? $data['ml_filter'] : 'all';
        $columns = isset( $data['ml_columns'] ) ? max( 2, min( 4, intval( $data['ml_columns'] ) ) ) : 3;
        $show_note = ! isset( $data['ml_show_note'] ) || $data['ml_show_note'] === 'yes';
        $show_link = ! isset( $data['ml_show_link'] ) || $data['ml_show_link'] === 'yes';
        $link_text = isset( $data['ml_link_text'] ) && '' !== trim( (string) $data['ml_link_text'] ) ? (string) $data['ml_link_text'] : __( '查看详情', 'developer-starter' );

        $items = isset( $data['ml_items'] ) && is_array( $data['ml_items'] ) ? $data['ml_items'] : array();
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'type'     => 'book',
                    'cover'    => '',
                    'title'    => __( '纳瓦尔宝典', 'developer-starter' ),
                    'creator'  => __( 'Eric Jorgenson', 'developer-starter' ),
                    'status'   => 'done',
                    'score'    => '9',
                    'progress' => __( '已读', 'developer-starter' ),
                    'note'     => __( '对杠杆与长期主义的阐述很实用。', 'developer-starter' ),
                    'link'     => '',
                ),
                array(
                    'type'     => 'movie',
                    'cover'    => '',
                    'title'    => __( '沙丘', 'developer-starter' ),
                    'creator'  => __( 'Denis Villeneuve', 'developer-starter' ),
                    'status'   => 'doing',
                    'score'    => '8',
                    'progress' => __( '看到第二部', 'developer-starter' ),
                    'note'     => __( '世界观宏大，视听质感优秀。', 'developer-starter' ),
                    'link'     => '',
                ),
                array(
                    'type'     => 'music',
                    'cover'    => '',
                    'title'    => __( 'Random Access Memories', 'developer-starter' ),
                    'creator'  => 'Daft Punk',
                    'status'   => 'wish',
                    'score'    => '9',
                    'progress' => __( '准备重听', 'developer-starter' ),
                    'note'     => __( '复古与未来感融合得很自然。', 'developer-starter' ),
                    'link'     => '',
                ),
            );
        }

        $display_items = array();
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $type = isset( $item['type'] ) ? (string) $item['type'] : 'book';
            if ( $filter !== 'all' && $type !== $filter ) {
                continue;
            }
            $display_items[] = $item;
        }

        if ( empty( $display_items ) ) {
            return;
        }

        $card_bg = isset( $data['ml_card_bg'] ) && $data['ml_card_bg'] !== '' ? $data['ml_card_bg'] : 'var(--color-neutral-0)';
        $bg_color = isset( $data['ml_bg_color'] ) && $data['ml_bg_color'] !== '' ? $data['ml_bg_color'] : 'var(--color-neutral-50)';
        $accent_color = isset( $data['ml_accent_color'] ) && $data['ml_accent_color'] !== '' ? $data['ml_accent_color'] : 'var(--color-primary)';
        $badge_bg = isset( $data['ml_badge_bg'] ) ? $clean_css_value( $data['ml_badge_bg'] ) : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '56px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '56px';

        $module_id = 'media-list-' . uniqid();
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb}; background: {$bg_color};";
        if ( $badge_bg ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};";
            $section_style .= "--qil-ml-status-done-bg: {$badge_bg};--qil-ml-status-doing-bg: {$badge_bg};--qil-ml-status-wish-bg: {$badge_bg};";
            $section_style .= "--qil-ml-status-done-text: var(--qiling-component-badge-text);--qil-ml-status-doing-text: var(--qiling-component-badge-text);--qil-ml-status-wish-text: var(--qiling-component-badge-text);";
        }
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-media-list" style="<?php echo esc_attr( $section_style ); ?>">
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

                <div class="qil-ml-grid qil-ml-cols-<?php echo esc_attr( (string) $columns ); ?>" style="--qil-ml-card-bg: <?php echo esc_attr( $card_bg ); ?>; --qil-ml-accent: <?php echo esc_attr( $accent_color ); ?>;">
                    <?php foreach ( $display_items as $item ) : ?>
                        <?php
                        $type = isset( $item['type'] ) ? (string) $item['type'] : 'book';
                        $cover = isset( $item['cover'] ) ? trim( (string) $item['cover'] ) : '';
                        $item_title = isset( $item['title'] ) ? trim( (string) $item['title'] ) : '';
                        $creator = isset( $item['creator'] ) ? trim( (string) $item['creator'] ) : '';
                        $status = isset( $item['status'] ) ? (string) $item['status'] : 'done';
                        $score = isset( $item['score'] ) ? intval( $item['score'] ) : 0;
                        $progress = isset( $item['progress'] ) ? trim( (string) $item['progress'] ) : '';
                        $link = isset( $item['link'] ) ? trim( (string) $item['link'] ) : '';
                        $note = isset( $item['note'] ) ? trim( (string) $item['note'] ) : '';
                        if ( $item_title === '' ) {
                            continue;
                        }
                        ?>
                        <article class="qil-ml-card">
                            <div class="qil-ml-cover-wrap">
                                <?php if ( $cover ) : ?>
                                    <img class="qil-ml-cover" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" />
                                <?php else : ?>
                                    <div class="qil-ml-cover is-fallback"><?php echo esc_html( $this->get_type_icon( $type ) ); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="qil-ml-body">
                                <div class="qil-ml-top">
                                    <span class="qil-ml-type"><?php echo esc_html( $this->get_type_label( $type ) ); ?></span>
                                    <span class="qil-ml-status is-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $this->get_status_label( $status ) ); ?></span>
                                </div>
                                <h3 class="qil-ml-title"><?php echo esc_html( $item_title ); ?></h3>
                                <?php if ( $creator !== '' ) : ?>
                                    <p class="qil-ml-creator"><?php echo esc_html( $creator ); ?></p>
                                <?php endif; ?>
                                <div class="qil-ml-meta">
                                    <?php if ( $progress !== '' ) : ?>
                                        <span><?php echo esc_html( $progress ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $score > 0 ) : ?>
                                        <span><?php printf( esc_html__( '评分 %d/10', 'developer-starter' ), $score ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ( $show_note && $note !== '' ) : ?>
                                    <p class="qil-ml-note"><?php echo esc_html( wp_trim_words( $note, 24, '...' ) ); ?></p>
                                <?php endif; ?>
                                <?php if ( $show_link && $link !== '' ) : ?>
                                    <a class="qil-ml-link" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $link_text ); ?></a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function get_type_label( $type ) {
        if ( $type === 'movie' ) {
            return __( '影视', 'developer-starter' );
        }
        if ( $type === 'music' ) {
            return __( '音乐', 'developer-starter' );
        }
        return __( '图书', 'developer-starter' );
    }

    private function get_type_icon( $type ) {
        if ( $type === 'movie' ) {
            return '🎬';
        }
        if ( $type === 'music' ) {
            return '🎧';
        }
        return '📚';
    }

    private function get_status_label( $status ) {
        if ( $status === 'doing' ) {
            return __( '进行中', 'developer-starter' );
        }
        if ( $status === 'wish' ) {
            return __( '想看/想读/想听', 'developer-starter' );
        }
        return __( '已完成', 'developer-starter' );
    }
}
