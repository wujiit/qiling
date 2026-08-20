<?php
/**
 * Reader Wall Module - 评论精选/读者墙
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Reader_Wall_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-admin-comments';
        $this->description = __( '展示评论精选和读者墙', 'developer-starter' );
    }

    public function get_id() {
        return 'reader_wall';
    }

    public function get_name() {
        return __( '评论精选/读者墙', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'rw_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( '评论精选 / 读者墙', 'developer-starter' ),
            ),
            array(
                'id'    => 'rw_subtitle',
                'type'  => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
            ),
            array(
                'id'      => 'rw_source',
                'type'    => 'select',
                'label'   => __( '数据来源', 'developer-starter' ),
                'options' => array(
                    'latest_comments' => __( '最新评论', 'developer-starter' ),
                    'manual'          => __( '手动输入', 'developer-starter' ),
                ),
                'default' => 'latest_comments',
            ),
            array(
                'id'      => 'rw_count',
                'type'    => 'number',
                'label'   => __( '显示数量', 'developer-starter' ),
                'default' => '8',
            ),
            array(
                'id'      => 'rw_columns',
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
                'id'      => 'rw_show_avatar',
                'type'    => 'select',
                'label'   => __( '显示头像', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'rw_show_post',
                'type'    => 'select',
                'label'   => __( '显示来源文章', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'     => 'rw_manual_items',
                'type'   => 'repeater',
                'label'  => __( '手动评论列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '读者昵称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'avatar',
                        'type'  => 'image',
                        'label' => __( '头像', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'content',
                        'type'  => 'textarea',
                        'label' => __( '评论内容', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'date',
                        'type'  => 'text',
                        'label' => __( '日期', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'post_title',
                        'type'  => 'text',
                        'label' => __( '来源文章标题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'label' => __( '链接（可选）', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'rw_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'rw_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'rw_accent_color',
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
        if ( function_exists( '\developer_starter_comments_feature_enabled' ) && ! \developer_starter_comments_feature_enabled() ) {
            return;
        }

        $title = isset( $data['rw_title'] ) ? $data['rw_title'] : '';
        $subtitle = isset( $data['rw_subtitle'] ) ? $data['rw_subtitle'] : '';
        $source = isset( $data['rw_source'] ) ? $data['rw_source'] : 'latest_comments';
        $count = isset( $data['rw_count'] ) ? max( 1, min( 50, intval( $data['rw_count'] ) ) ) : 8;
        $columns = isset( $data['rw_columns'] ) ? max( 2, min( 4, intval( $data['rw_columns'] ) ) ) : 3;
        $show_avatar = ! isset( $data['rw_show_avatar'] ) || $data['rw_show_avatar'] === 'yes';
        $show_post = ! isset( $data['rw_show_post'] ) || $data['rw_show_post'] === 'yes';

        $items = array();
        if ( $source === 'manual' ) {
            $items = $this->collect_manual_items( $data, $count );
        } else {
            $items = $this->collect_comment_items( $count );
        }

        if ( empty( $items ) ) {
            return;
        }

        $card_bg = isset( $data['rw_card_bg'] ) && $data['rw_card_bg'] !== '' ? $data['rw_card_bg'] : 'var(--color-neutral-0)';
        $bg_color = isset( $data['rw_bg_color'] ) && $data['rw_bg_color'] !== '' ? $data['rw_bg_color'] : 'var(--color-neutral-50)';
        $accent_color = isset( $data['rw_accent_color'] ) && $data['rw_accent_color'] !== '' ? $data['rw_accent_color'] : 'var(--color-primary)';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '56px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '56px';

        $module_id = 'reader-wall-' . uniqid();
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-reader-wall" style="padding-top: <?php echo esc_attr( $pt ); ?>; padding-bottom: <?php echo esc_attr( $pb ); ?>; background: <?php echo esc_attr( $bg_color ); ?>;">
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

                <div class="qil-rw-grid qil-rw-cols-<?php echo esc_attr( (string) $columns ); ?>" style="--qil-rw-card-bg: <?php echo esc_attr( $card_bg ); ?>; --qil-rw-accent: <?php echo esc_attr( $accent_color ); ?>;">
                    <?php foreach ( $items as $item ) : ?>
                        <?php
                        $name = isset( $item['name'] ) ? (string) $item['name'] : '';
                        $avatar = isset( $item['avatar'] ) ? (string) $item['avatar'] : '';
                        $content = isset( $item['content'] ) ? (string) $item['content'] : '';
                        $date = isset( $item['date'] ) ? (string) $item['date'] : '';
                        $post_title = isset( $item['post_title'] ) ? (string) $item['post_title'] : '';
                        $url = isset( $item['url'] ) ? (string) $item['url'] : '';
                        if ( trim( $content ) === '' ) {
                            continue;
                        }
                        ?>
                        <article class="qil-rw-card">
                            <div class="qil-rw-top">
                                <?php if ( $show_avatar ) : ?>
                                    <?php if ( $avatar ) : ?>
                                        <img class="qil-rw-avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                                    <?php else : ?>
                                        <span class="qil-rw-avatar is-fallback"><?php echo esc_html( $this->extract_initial( $name ) ); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="qil-rw-head-meta">
                                    <strong class="qil-rw-name"><?php echo esc_html( $name ); ?></strong>
                                    <?php if ( $date ) : ?>
                                        <span class="qil-rw-date"><?php echo esc_html( $date ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <blockquote class="qil-rw-content">“<?php echo esc_html( $content ); ?>”</blockquote>

                            <?php if ( $show_post && $post_title !== '' ) : ?>
                                <?php if ( $url !== '' ) : ?>
                                    <a class="qil-rw-post" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $post_title ); ?></a>
                                <?php else : ?>
                                    <span class="qil-rw-post"><?php echo esc_html( $post_title ); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function collect_comment_items( $count ) {
        $comments = get_comments(
            array(
                'status' => 'approve',
                'number' => max( 1, $count ),
                'type'   => 'comment',
                'orderby' => 'comment_date_gmt',
                'order'   => 'DESC',
            )
        );

        if ( empty( $comments ) || ! is_array( $comments ) ) {
            return array();
        }

        $items = array();
        foreach ( $comments as $comment ) {
            if ( ! $comment instanceof \WP_Comment ) {
                continue;
            }

            $post_id = intval( $comment->comment_post_ID );
            $post_title = get_the_title( $post_id );
            $comment_url = get_comment_link( $comment );
            if ( is_wp_error( $comment_url ) ) {
                $comment_url = '';
            }
            $content = trim( wp_strip_all_tags( (string) $comment->comment_content ) );
            if ( $content === '' ) {
                continue;
            }

            $items[] = array(
                'name'      => $comment->comment_author ? $comment->comment_author : __( '匿名读者', 'developer-starter' ),
                'avatar'    => get_avatar_url( $comment, array( 'size' => 80 ) ),
                'content'   => wp_trim_words( $content, 30, '...' ),
                'date'      => get_comment_date( get_option( 'date_format' ), $comment ),
                'post_title'=> $post_title ? $post_title : '',
                'url'       => $comment_url ? $comment_url : '',
            );
        }

        return $items;
    }

    private function collect_manual_items( $data, $count ) {
        $rows = isset( $data['rw_manual_items'] ) && is_array( $data['rw_manual_items'] ) ? $data['rw_manual_items'] : array();
        if ( empty( $rows ) ) {
            $date_format = function_exists( 'developer_starter_get_date_time_format' ) ? developer_starter_get_date_time_format( false ) : get_option( 'date_format' );
            $rows = array(
                array(
                    'name'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '读者A', 'Reader A' ) : __( '读者A', 'developer-starter' ),
                    'avatar'     => '',
                    'content'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '文章非常实用，解决了我很多实际问题。', 'Very practical article. It helped solve several real project issues for me.' ) : __( '文章非常实用，解决了我很多实际问题。', 'developer-starter' ),
                    'date'       => date_i18n( $date_format ),
                    'post_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '主题模块实战经验', 'Practical Theme Module Notes' ) : __( '主题模块实战经验', 'developer-starter' ),
                    'url'        => '',
                ),
                array(
                    'name'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '读者B', 'Reader B' ) : __( '读者B', 'developer-starter' ),
                    'avatar'     => '',
                    'content'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '希望后续能有更多案例拆解，学习成本更低。', 'Would love to see more case studies like this. They make the learning curve much easier.' ) : __( '希望后续能有更多案例拆解，学习成本更低。', 'developer-starter' ),
                    'date'       => date_i18n( $date_format, strtotime( '-1 day' ) ),
                    'post_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '性能优化指南', 'Performance Optimization Guide' ) : __( '性能优化指南', 'developer-starter' ),
                    'url'        => '',
                ),
            );
        }

        $items = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
            $content = isset( $row['content'] ) ? trim( (string) $row['content'] ) : '';
            if ( $content === '' ) {
                continue;
            }
            $items[] = array(
                'name'       => $name !== '' ? $name : __( '匿名读者', 'developer-starter' ),
                'avatar'     => isset( $row['avatar'] ) ? trim( (string) $row['avatar'] ) : '',
                'content'    => wp_trim_words( $content, 30, '...' ),
                'date'       => isset( $row['date'] ) ? trim( (string) $row['date'] ) : '',
                'post_title' => isset( $row['post_title'] ) ? trim( (string) $row['post_title'] ) : '',
                'url'        => isset( $row['url'] ) ? trim( (string) $row['url'] ) : '',
            );
            if ( count( $items ) >= $count ) {
                break;
            }
        }

        return $items;
    }

    private function extract_initial( $name ) {
        $name = trim( (string) $name );
        if ( $name === '' ) {
            return 'R';
        }

        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $name, 0, 1 );
        }

        return strtoupper( substr( $name, 0, 1 ) );
    }
}
