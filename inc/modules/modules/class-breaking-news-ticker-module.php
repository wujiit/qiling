<?php
/**
 * Breaking News Ticker Module - 热点快讯滚动条
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Breaking_News_Ticker_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-megaphone';
        $this->description = __( 'Breaking News / 热点快讯滚动条', 'developer-starter' );
    }

    public function get_id() {
        return 'breaking_news_ticker';
    }

    public function get_name() {
        return __( '热点快讯滚动条', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'bn_title',
                'type'    => 'text',
                'label'   => __( '标签标题', 'developer-starter' ),
                'default' => __( '热点快讯', 'developer-starter' ),
            ),
            array(
                'id'      => 'bn_source',
                'type'    => 'select',
                'label'   => __( '数据来源', 'developer-starter' ),
                'options' => array(
                    'latest' => __( '最新文章', 'developer-starter' ),
                    'category' => __( '指定分类', 'developer-starter' ),
                    'tag' => __( '指定标签', 'developer-starter' ),
                    'manual' => __( '手动输入', 'developer-starter' ),
                ),
                'default' => 'latest',
            ),
            array(
                'id'      => 'bn_count',
                'type'    => 'number',
                'label'   => __( '自动获取数量', 'developer-starter' ),
                'default' => '8',
            ),
            array(
                'id'    => 'bn_categories',
                'type'  => 'text',
                'label' => __( '分类ID (逗号分隔)', 'developer-starter' ),
            ),
            array(
                'id'    => 'bn_tags',
                'type'  => 'text',
                'label' => __( '标签ID/Slug (逗号分隔)', 'developer-starter' ),
            ),
            array(
                'id'    => 'bn_post_ids',
                'type'  => 'text',
                'label' => __( '手动文章ID (逗号分隔，仅手动模式)', 'developer-starter' ),
            ),
            array(
                'id'    => 'bn_manual_items',
                'type'  => 'repeater',
                'label' => __( '手动快讯条目', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id' => 'text',
                        'type' => 'text',
                        'label' => __( '文案', 'developer-starter' ),
                    ),
                    array(
                        'id' => 'url',
                        'type' => 'text',
                        'label' => __( '链接', 'developer-starter' ),
                    ),
                    array(
                        'id' => 'target',
                        'type' => 'select',
                        'label' => __( '打开方式', 'developer-starter' ),
                        'options' => array(
                            '_self' => __( '当前窗口', 'developer-starter' ),
                            '_blank' => __( '新窗口', 'developer-starter' ),
                        ),
                        'default' => '_self',
                    ),
                ),
            ),
            array(
                'id'      => 'bn_show_time',
                'type'    => 'select',
                'label'   => __( '显示时间', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'no',
            ),
            array(
                'id'      => 'bn_speed',
                'type'    => 'number',
                'label'   => __( '滚动一轮时长 (秒)', 'developer-starter' ),
                'default' => '45',
            ),
            array(
                'id'      => 'bn_pause_on_hover',
                'type'    => 'select',
                'label'   => __( '鼠标悬停暂停', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'bn_bg_color',
                'type'    => 'color',
                'label'   => __( '背景颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'bn_label_bg_color',
                'type'    => 'color',
                'label'   => __( '标签背景色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'bn_text_color',
                'type'    => 'color',
                'label'   => __( '文字颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-900)',
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'developer-starter' ),
                'default' => '16px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'developer-starter' ),
                'default' => '16px',
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['bn_title'] ) && $data['bn_title'] !== '' ? $data['bn_title'] : __( '热点快讯', 'developer-starter' );
        $source = isset( $data['bn_source'] ) && $data['bn_source'] !== '' ? $data['bn_source'] : 'latest';
        $count = isset( $data['bn_count'] ) ? max( 1, intval( $data['bn_count'] ) ) : 8;
        $show_time = isset( $data['bn_show_time'] ) && $data['bn_show_time'] === 'yes';
        $pause_on_hover = ! isset( $data['bn_pause_on_hover'] ) || $data['bn_pause_on_hover'] === 'yes';
        $speed = isset( $data['bn_speed'] ) ? intval( $data['bn_speed'] ) : 45;
        $speed = max( 10, min( 180, $speed ) );

        $bg_color = isset( $data['bn_bg_color'] ) ? $data['bn_bg_color'] : 'var(--color-neutral-0)';
        $label_bg = ! empty( $data['bn_label_bg_color'] ) ? $data['bn_label_bg_color'] : 'var(--color-error)';
        $text_color = isset( $data['bn_text_color'] ) ? $data['bn_text_color'] : 'var(--color-neutral-900)';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '16px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '16px';

        $items = $this->collect_items( $data, $source, $count );
        if ( empty( $items ) ) {
            return;
        }

        $module_id = 'bn-ticker-' . uniqid();
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-breaking-news-ticker" style="padding-top: <?php echo esc_attr( $pt ); ?>; padding-bottom: <?php echo esc_attr( $pb ); ?>;">
            <div class="container">
                <div class="ql-bn-wrap <?php echo $pause_on_hover ? 'is-pause-hover' : ''; ?>" style="--ql-bn-bg: <?php echo esc_attr( $bg_color ); ?>; --ql-bn-label-bg: <?php echo esc_attr( $label_bg ); ?>; --ql-bn-text: <?php echo esc_attr( $text_color ); ?>; --ql-bn-speed: <?php echo esc_attr( $speed ); ?>s;">
                    <div class="ql-bn-label"><?php echo esc_html( $title ); ?></div>
                    <div class="ql-bn-viewport" aria-label="<?php esc_attr_e( '热点快讯滚动内容', 'developer-starter' ); ?>">
                        <div class="ql-bn-track">
                            <?php $this->render_track_row( $items, $show_time ); ?>
                            <?php $this->render_track_row( $items, $show_time, true ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    private function render_track_row( $items, $show_time = false, $is_cloned = false ) {
        ?>
        <div class="ql-bn-row" <?php echo $is_cloned ? 'aria-hidden="true"' : ''; ?>>
            <?php foreach ( $items as $index => $item ) : ?>
                <?php if ( $index > 0 ) : ?>
                    <span class="ql-bn-dot"></span>
                <?php endif; ?>
                <a class="ql-bn-item" href="<?php echo esc_url( $item['url'] ); ?>" target="<?php echo esc_attr( $item['target'] ); ?>"<?php echo $item['target'] === '_blank' ? ' rel="nofollow noopener"' : ''; ?>>
                    <span class="ql-bn-text"><?php echo esc_html( $item['text'] ); ?></span>
                    <?php if ( $show_time && ! empty( $item['time'] ) ) : ?>
                        <span class="ql-bn-time"><?php echo esc_html( $item['time'] ); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function collect_items( $data, $source, $count ) {
        if ( $source === 'manual' ) {
            $manual = $this->collect_manual_items( $data );
            if ( ! empty( $manual ) ) {
                return $manual;
            }
            $manual_by_post_ids = $this->collect_manual_posts_by_ids( $data, $count );
            if ( ! empty( $manual_by_post_ids ) ) {
                return $manual_by_post_ids;
            }
            return array();
        }
        return $this->collect_post_items( $data, $source, $count );
    }

    private function collect_manual_items( $data ) {
        $rows = isset( $data['bn_manual_items'] ) && is_array( $data['bn_manual_items'] ) ? $data['bn_manual_items'] : array();
        $items = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $text = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
            if ( $text === '' ) {
                continue;
            }
            $url = isset( $row['url'] ) && trim( (string) $row['url'] ) !== '' ? trim( (string) $row['url'] ) : '#';
            $target = isset( $row['target'] ) && $row['target'] === '_blank' ? '_blank' : '_self';
            $items[] = array(
                'text' => $text,
                'url' => $url,
                'target' => $target,
                'time' => '',
            );
        }
        return $items;
    }

    private function collect_manual_posts_by_ids( $data, $count ) {
        $ids_raw = isset( $data['bn_post_ids'] ) ? (string) $data['bn_post_ids'] : '';
        $ids = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', $ids_raw ) ) ) );
        if ( empty( $ids ) ) {
            return array();
        }

        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'post__in'            => $ids,
            'orderby'             => 'post__in',
            'posts_per_page'      => max( 1, $count ),
            'ignore_sticky_posts' => true,
        );
        return $this->query_posts_to_items( $args );
    }

    private function collect_post_items( $data, $source, $count ) {
        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'DESC',
            'posts_per_page'      => max( 1, $count ),
            'ignore_sticky_posts' => true,
        );

        if ( $source === 'category' ) {
            $cat_raw = isset( $data['bn_categories'] ) ? (string) $data['bn_categories'] : '';
            $cat_ids = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', $cat_raw ) ) ) );
            if ( ! empty( $cat_ids ) ) {
                $args['category__in'] = $cat_ids;
            }
        } elseif ( $source === 'tag' ) {
            $tags_raw = isset( $data['bn_tags'] ) ? (string) $data['bn_tags'] : '';
            $tags = array_filter( array_map( 'trim', explode( ',', $tags_raw ) ) );
            if ( ! empty( $tags ) ) {
                if ( is_numeric( $tags[0] ) ) {
                    $args['tag__in'] = array_map( 'intval', $tags );
                } else {
                    $args['tag_slug__in'] = $tags;
                }
            }
        }

        return $this->query_posts_to_items( $args );
    }

    private function query_posts_to_items( $args ) {
        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query(
                $args,
                'module_breaking_news_ticker',
                array( 'needs_pagination' => false )
            );
        } else {
            $query = new \WP_Query( $args );
        }

        $items = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $items[] = array(
                    'text'   => get_the_title(),
                    'url'    => get_permalink(),
                    'target' => '_self',
                    'time'   => get_the_date( 'm-d' ),
                );
            }
        }
        wp_reset_postdata();

        return $items;
    }
}
