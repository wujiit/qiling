<?php
/**
 * 媒体与书影音清单模块
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
        $this->description = __( '展示读书、观影、听歌清单与进度', 'qiling' );
    }

    public function get_id() {
        return 'media_list';
    }

    public function get_name() {
        return __( '读书/观影/听歌清单', 'qiling' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'ml_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'qiling' ),
                'default' => __( '读书 / 观影 / 听歌清单', 'qiling' ),
            ),
            array(
                'id'    => 'ml_subtitle',
                'type'  => 'text',
                'label' => __( '模块副标题', 'qiling' ),
            ),
            array(
                'id'      => 'ml_filter',
                'type'    => 'select',
                'label'   => __( '显示类型', 'qiling' ),
                'options' => array(
                    'all'   => __( '全部', 'qiling' ),
                    'book'  => __( '仅图书', 'qiling' ),
                    'movie' => __( '仅影视', 'qiling' ),
                    'music' => __( '仅音乐', 'qiling' ),
                ),
                'default' => 'all',
            ),
            array(
                'id'      => 'ml_columns',
                'type'    => 'select',
                'label'   => __( '每行列数', 'qiling' ),
                'options' => array(
                    '2' => __( '2列', 'qiling' ),
                    '3' => __( '3列', 'qiling' ),
                    '4' => __( '4列', 'qiling' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'ml_show_note',
                'type'    => 'select',
                'label'   => __( '显示短评', 'qiling' ),
                'options' => array(
                    'yes' => __( '显示', 'qiling' ),
                    'no'  => __( '隐藏', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'ml_show_link',
                'type'    => 'select',
                'label'   => __( '显示详情链接', 'qiling' ),
                'options' => array(
                    'yes' => __( '显示', 'qiling' ),
                    'no'  => __( '隐藏', 'qiling' ),
                ),
                'default' => 'yes',
            ),
            array( 'id' => 'ml_link_text', 'type' => 'text', 'label' => __( '详情链接文案', 'qiling' ), 'default' => __( '查看详情', 'qiling' ) ),
            array(
                'id'     => 'ml_items',
                'type'   => 'repeater',
                'label'  => __( '清单条目', 'qiling' ),
                'fields' => array(
                    array(
                        'id'      => 'type',
                        'type'    => 'select',
                        'label'   => __( '类型', 'qiling' ),
                        'options' => array(
                            'book'  => __( '图书', 'qiling' ),
                            'movie' => __( '影视', 'qiling' ),
                            'music' => __( '音乐', 'qiling' ),
                        ),
                        'default' => 'book',
                    ),
                    array(
                        'id'    => 'cover',
                        'type'  => 'image',
                        'label' => __( '封面', 'qiling' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题', 'qiling' ),
                    ),
                    array(
                        'id'    => 'creator',
                        'type'  => 'text',
                        'label' => __( '作者/导演/歌手', 'qiling' ),
                    ),
                    array(
                        'id'      => 'status',
                        'type'    => 'select',
                        'label'   => __( '状态', 'qiling' ),
                        'options' => array(
                            'done'  => __( '已完成', 'qiling' ),
                            'doing' => __( '进行中', 'qiling' ),
                            'wish'  => __( '想看/想读/想听', 'qiling' ),
                        ),
                        'default' => 'done',
                    ),
                    array(
                        'id'      => 'score',
                        'type'    => 'number',
                        'label'   => __( '评分 (1-10)', 'qiling' ),
                        'default' => '8',
                    ),
                    array(
                        'id'    => 'progress',
                        'type'  => 'text',
                        'label' => __( '进度描述', 'qiling' ),
                    ),
                    array(
                        'id'    => 'link',
                        'type'  => 'text',
                        'label' => __( '详情链接', 'qiling' ),
                    ),
                    array(
                        'id'    => 'note',
                        'type'  => 'textarea',
                        'label' => __( '短评', 'qiling' ),
                    ),
                ),
                'default_items' => array(
                    array(
                        'type'     => 'book',
                        'cover'    => '',
                        'title'    => __( '纳瓦尔宝典', 'qiling' ),
                        'creator'  => __( 'Eric Jorgenson', 'qiling' ),
                        'status'   => 'done',
                        'score'    => '9',
                        'progress' => __( '已读', 'qiling' ),
                        'note'     => __( '对杠杆与长期主义的阐述很实用。', 'qiling' ),
                        'link'     => '',
                    ),
                    array(
                        'type'     => 'movie',
                        'cover'    => '',
                        'title'    => __( '沙丘', 'qiling' ),
                        'creator'  => __( 'Denis Villeneuve', 'qiling' ),
                        'status'   => 'doing',
                        'score'    => '8',
                        'progress' => __( '看到第二部', 'qiling' ),
                        'note'     => __( '世界观宏大，视听质感优秀。', 'qiling' ),
                        'link'     => '',
                    ),
                    array(
                        'type'     => 'music',
                        'cover'    => '',
                        'title'    => __( 'Random Access Memories', 'qiling' ),
                        'creator'  => 'Daft Punk',
                        'status'   => 'wish',
                        'score'    => '9',
                        'progress' => __( '准备重听', 'qiling' ),
                        'note'     => __( '复古与未来感融合得很自然。', 'qiling' ),
                        'link'     => '',
                    ),
                ),
            ),
            array(
                'id'      => 'ml_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'qiling' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'ml_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'qiling' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'ml_accent_color',
                'type'    => 'color',
                'label'   => __( '强调色', 'qiling' ),
                'default' => 'var(--color-primary)',
            ),
            array(
                'id'      => 'ml_badge_bg',
                'type'    => 'color',
                'label'   => __( '标签/徽章背景颜色', 'qiling' ),
                'default' => '',
                'desc'    => __( '控制类型标签和状态徽章，留空时保留状态默认色并跟随全局徽章颜色。', 'qiling' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'qiling' ),
                'default' => '56px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'qiling' ),
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
        $link_text = isset( $data['ml_link_text'] ) && '' !== trim( (string) $data['ml_link_text'] ) ? (string) $data['ml_link_text'] : __( '查看详情', 'qiling' );

        $items = isset( $data['ml_items'] ) && is_array( $data['ml_items'] ) ? $data['ml_items'] : array();
        if ( empty( $items ) && ! isset( $data['ml_items'] ) ) {
            $items = array(
                array(
                    'type'     => 'book',
                    'cover'    => '',
                    'title'    => __( '纳瓦尔宝典', 'qiling' ),
                    'creator'  => __( 'Eric Jorgenson', 'qiling' ),
                    'status'   => 'done',
                    'score'    => '9',
                    'progress' => __( '已读', 'qiling' ),
                    'note'     => __( '对杠杆与长期主义的阐述很实用。', 'qiling' ),
                    'link'     => '',
                ),
                array(
                    'type'     => 'movie',
                    'cover'    => '',
                    'title'    => __( '沙丘', 'qiling' ),
                    'creator'  => __( 'Denis Villeneuve', 'qiling' ),
                    'status'   => 'doing',
                    'score'    => '8',
                    'progress' => __( '看到第二部', 'qiling' ),
                    'note'     => __( '世界观宏大，视听质感优秀。', 'qiling' ),
                    'link'     => '',
                ),
                array(
                    'type'     => 'music',
                    'cover'    => '',
                    'title'    => __( 'Random Access Memories', 'qiling' ),
                    'creator'  => 'Daft Punk',
                    'status'   => 'wish',
                    'score'    => '9',
                    'progress' => __( '准备重听', 'qiling' ),
                    'note'     => __( '复古与未来感融合得很自然。', 'qiling' ),
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
                                        <span><?php printf( esc_html__( '评分 %d/10', 'qiling' ), $score ); ?></span>
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
            return __( '影视', 'qiling' );
        }
        if ( $type === 'music' ) {
            return __( '音乐', 'qiling' );
        }
        return __( '图书', 'qiling' );
    }

    private function get_type_icon( $type ) {
        if ( $type === 'movie' ) {
            return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>';
        }
        if ( $type === 'music' ) {
            return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>';
        }
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
    }

    private function get_status_label( $status ) {
        if ( $status === 'doing' ) {
            return __( '进行中', 'qiling' );
        }
        if ( $status === 'wish' ) {
            return __( '想看/想读/想听', 'qiling' );
        }
        return __( '已完成', 'qiling' );
    }
}
