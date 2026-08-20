<?php
/**
 * Sidebar Post List Widget
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Widget_Post_List extends \WP_Widget {

    const CACHE_TTL = 300;
    const RANDOM_CACHE_TTL = 600;

    public function __construct() {
        parent::__construct(
            'developer_starter_post_list',
            __( '启灵文章展示', 'developer-starter' ),
            array(
                'description' => __( '显示最新、热门或随机文章，可选缩略图、日期、排行序号并支持排除分类。', 'developer-starter' ),
            )
        );
    }

    public function widget( $args, $instance ) {
        $instance = $this->normalize_instance( $instance );
        $post_ids = $this->get_post_ids( $instance );

        if ( ! empty( $post_ids ) ) {
            $this->prime_post_caches( $post_ids, ! empty( $instance['show_thumbnail'] ) );
        }

        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
        }

        $widget_classes = array( 'ds-post-list-widget' );
        $widget_classes[] = ! empty( $instance['show_thumbnail'] ) ? 'has-thumbnail' : 'no-thumbnail';
        if ( ! empty( $instance['show_date'] ) ) {
            $widget_classes[] = 'has-date';
        }
        if ( ! empty( $instance['show_rank'] ) ) {
            $widget_classes[] = 'has-rank';
        }
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $widget_classes ) ); ?>">
            <?php if ( empty( $post_ids ) ) : ?>
                <p class="ds-post-list-widget__empty"><?php esc_html_e( '暂无可显示的文章。', 'developer-starter' ); ?></p>
            <?php else : ?>
                <ul class="ds-post-list-widget__list">
                    <?php foreach ( $post_ids as $index => $post_id ) : ?>
                        <?php
                        $title = get_the_title( $post_id );
                        if ( '' === $title ) {
                            continue;
                        }

                        $permalink = get_permalink( $post_id );
                        if ( ! $permalink ) {
                            continue;
                        }

                        $thumbnail_markup = ! empty( $instance['show_thumbnail'] ) ? $this->get_theme_thumbnail_markup( $post_id, $title ) : '';
                        $has_thumbnail = '' !== $thumbnail_markup;
                        $display_title = $this->trim_title_for_display( $title, $has_thumbnail );
                        $link_classes = array( 'ds-post-list-widget__link' );
                        if ( $has_thumbnail ) {
                            $link_classes[] = 'has-thumb';
                        }
                        if ( ! empty( $instance['show_rank'] ) ) {
                            $link_classes[] = 'has-rank';
                        }
                        ?>
                        <li class="ds-post-list-widget__item">
                            <a class="<?php echo esc_attr( implode( ' ', $link_classes ) ); ?>" href="<?php echo esc_url( $permalink ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>">
                                <?php if ( ! empty( $instance['show_rank'] ) ) : ?>
                                    <span class="ds-post-list-widget__rank <?php echo esc_attr( $this->get_rank_class( $index ) ); ?>"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
                                <?php endif; ?>
                                <?php if ( $has_thumbnail ) : ?>
                                    <span class="ds-post-list-widget__thumb">
                                        <?php echo $thumbnail_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </span>
                                <?php endif; ?>
                                <span class="ds-post-list-widget__content">
                                    <span class="ds-post-list-widget__title"><?php echo esc_html( $display_title ); ?></span>
                                    <?php if ( ! empty( $instance['show_date'] ) ) : ?>
                                        <span class="ds-post-list-widget__meta">
                                            <time datetime="<?php echo esc_attr( get_post_time( 'c', false, $post_id ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d', $post_id ) ); ?></time>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $instance = $this->normalize_instance( $instance );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( '标题:', 'developer-starter' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>"><?php esc_html_e( '文章来源:', 'developer-starter' ); ?></label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'source' ) ); ?>">
                <option value="latest" <?php selected( $instance['source'], 'latest' ); ?>><?php esc_html_e( '最新文章', 'developer-starter' ); ?></option>
                <option value="popular" <?php selected( $instance['source'], 'popular' ); ?>><?php esc_html_e( '热门文章', 'developer-starter' ); ?></option>
                <option value="random" <?php selected( $instance['source'], 'random' ); ?>><?php esc_html_e( '随机文章', 'developer-starter' ); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'posts_per_page' ) ); ?>"><?php esc_html_e( '显示数量:', 'developer-starter' ); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'posts_per_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts_per_page' ) ); ?>" type="number" min="1" max="12" step="1" value="<?php echo esc_attr( $instance['posts_per_page'] ); ?>">
        </p>

        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_thumbnail' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_thumbnail' ) ); ?>" value="1" <?php checked( $instance['show_thumbnail'], '1' ); ?>>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_thumbnail' ) ); ?>"><?php esc_html_e( '显示缩略图', 'developer-starter' ); ?></label>
        </p>

        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" value="1" <?php checked( $instance['show_date'], '1' ); ?>>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( '显示日期', 'developer-starter' ); ?></label>
        </p>

        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_rank' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_rank' ) ); ?>" value="1" <?php checked( $instance['show_rank'], '1' ); ?>>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_rank' ) ); ?>"><?php esc_html_e( '显示排行序号', 'developer-starter' ); ?></label>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'exclude_categories' ) ); ?>"><?php esc_html_e( '排除分类 ID:', 'developer-starter' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'exclude_categories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'exclude_categories' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['exclude_categories'] ); ?>">
            <small><?php esc_html_e( '多个分类 ID 用逗号分隔，例如：3,8,15', 'developer-starter' ); ?></small>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';

        $source = isset( $new_instance['source'] ) ? sanitize_key( $new_instance['source'] ) : 'latest';
        if ( ! in_array( $source, array( 'latest', 'popular', 'random' ), true ) ) {
            $source = 'latest';
        }
        $instance['source'] = $source;

        $posts_per_page = isset( $new_instance['posts_per_page'] ) ? absint( $new_instance['posts_per_page'] ) : 5;
        $instance['posts_per_page'] = max( 1, min( 12, $posts_per_page ) );
        $instance['show_thumbnail'] = ! empty( $new_instance['show_thumbnail'] ) ? '1' : '';
        $instance['show_date'] = ! empty( $new_instance['show_date'] ) ? '1' : '';
        $instance['show_rank'] = ! empty( $new_instance['show_rank'] ) ? '1' : '';

        $exclude_categories = isset( $new_instance['exclude_categories'] ) ? (string) $new_instance['exclude_categories'] : '';
        $exclude_categories = preg_replace( '/[^0-9,\s]+/', '', $exclude_categories );
        $instance['exclude_categories'] = trim( (string) $exclude_categories );

        return $instance;
    }

    protected function normalize_instance( $instance ) {
        $defaults = array(
            'title'              => __( '文章展示', 'developer-starter' ),
            'source'             => 'latest',
            'posts_per_page'     => 5,
            'show_thumbnail'     => '1',
            'show_date'          => '',
            'show_rank'          => '',
            'exclude_categories' => '',
        );

        $instance = wp_parse_args( (array) $instance, $defaults );
        $instance['title'] = sanitize_text_field( $instance['title'] );
        $instance['source'] = sanitize_key( $instance['source'] );
        if ( ! in_array( $instance['source'], array( 'latest', 'popular', 'random' ), true ) ) {
            $instance['source'] = 'latest';
        }
        $instance['posts_per_page'] = max( 1, min( 12, absint( $instance['posts_per_page'] ) ) );
        $instance['show_thumbnail'] = ! empty( $instance['show_thumbnail'] ) ? '1' : '';
        $instance['show_date'] = ! empty( $instance['show_date'] ) ? '1' : '';
        $instance['show_rank'] = ! empty( $instance['show_rank'] ) ? '1' : '';
        $instance['exclude_categories'] = trim( preg_replace( '/[^0-9,\s]+/', '', (string) $instance['exclude_categories'] ) );

        return $instance;
    }

    protected function prime_post_caches( $post_ids, $prime_meta_cache = false ) {
        if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
            return;
        }

        $post_ids = array_values( array_filter( array_map( 'absint', $post_ids ) ) );
        if ( empty( $post_ids ) ) {
            return;
        }

        if ( function_exists( '_prime_post_caches' ) ) {
            _prime_post_caches( $post_ids, (bool) $prime_meta_cache, false );
            return;
        }

        foreach ( $post_ids as $post_id ) {
            get_post( $post_id );

            if ( $prime_meta_cache ) {
                get_post_meta( $post_id );
            }
        }
    }

    protected function trim_title_for_display( $title, $has_thumbnail = false ) {
        $title = trim( wp_strip_all_tags( (string) $title ) );
        if ( '' === $title ) {
            return '';
        }

        $max_width = $has_thumbnail ? 28 : 44;

        if ( function_exists( 'mb_strimwidth' ) ) {
            return mb_strimwidth( $title, 0, $max_width, '...', 'UTF-8' );
        }

        if ( function_exists( 'mb_substr' ) && function_exists( 'mb_strlen' ) ) {
            $max_length = $has_thumbnail ? 15 : 24;

            if ( mb_strlen( $title, 'UTF-8' ) > $max_length ) {
                return mb_substr( $title, 0, $max_length - 3, 'UTF-8' ) . '...';
            }

            return $title;
        }

        return wp_html_excerpt( $title, $max_width, '...' );
    }

    protected function get_theme_thumbnail_markup( $post_id, $title ) {
        if ( ! function_exists( 'developer_starter_the_thumbnail' ) ) {
            return '';
        }

        $attr = array(
            'class'    => 'ds-post-list-widget__thumb-image',
            'loading'  => 'lazy',
            'decoding' => 'async',
            'alt'      => trim( wp_strip_all_tags( (string) $title ) ),
            'style'    => 'object-fit:' . $this->get_thumbnail_object_fit() . ';',
        );

        ob_start();
        developer_starter_the_thumbnail( $post_id, 'thumbnail', $attr );
        $thumbnail_markup = trim( (string) ob_get_clean() );

        if ( '' === $thumbnail_markup || false === strpos( $thumbnail_markup, '<img' ) ) {
            return '';
        }

        return $thumbnail_markup;
    }

    protected function get_thumbnail_object_fit() {
        $fit = function_exists( 'developer_starter_get_thumbnail_display_mode' )
            ? (string) developer_starter_get_thumbnail_display_mode()
            : 'cover';

        if ( ! in_array( $fit, array( 'cover', 'contain', 'fill', 'none' ), true ) ) {
            $fit = 'cover';
        }

        return $fit;
    }

    protected function get_rank_class( $index ) {
        switch ( (int) $index ) {
            case 0:
                return 'is-top-1';
            case 1:
                return 'is-top-2';
            case 2:
                return 'is-top-3';
            default:
                return 'is-default';
        }
    }

    protected function get_post_ids( $instance ) {
        $count = $instance['posts_per_page'];
        $exclude_categories = $this->parse_category_ids( $instance['exclude_categories'] );

        $cache_key = 'ds_wpl_' . md5(
            wp_json_encode(
                array(
                    'source'  => $instance['source'],
                    'count'   => $count,
                    'exclude' => $exclude_categories,
                    'site'    => get_current_blog_id(),
                    'locale'  => function_exists( 'determine_locale' ) ? determine_locale() : get_locale(),
                )
            )
        );

        if ( function_exists( 'developer_starter_cache_fetch' ) ) {
            $cached = developer_starter_cache_fetch( $cache_key, 'developer_starter_widget' );
        } else {
            $cached = get_transient( $cache_key );
        }
        if ( is_array( $cached ) ) {
            return array_values( array_filter( array_map( 'absint', $cached ) ) );
        }

        $post_ids = array();
        switch ( $instance['source'] ) {
            case 'popular':
                $post_ids = $this->get_popular_post_ids( $count, $exclude_categories, ! empty( $instance['show_thumbnail'] ) );
                break;
            case 'random':
                $post_ids = $this->get_random_post_ids( $count, $exclude_categories, ! empty( $instance['show_thumbnail'] ) );
                break;
            case 'latest':
            default:
                $args = $this->get_base_query_args( $count, $exclude_categories, ! empty( $instance['show_thumbnail'] ) );
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                $post_ids = $this->query_post_ids( $args );
                break;
        }

        $ttl = $instance['source'] === 'random' ? self::RANDOM_CACHE_TTL : self::CACHE_TTL;
        if ( function_exists( 'developer_starter_cache_store' ) ) {
            developer_starter_cache_store( $cache_key, $post_ids, $ttl, 'developer_starter_widget' );
        } else {
            set_transient( $cache_key, $post_ids, $ttl );
        }

        return $post_ids;
    }

    protected function get_popular_post_ids( $count, $exclude_categories, $prime_meta_cache = false ) {
        $args = $this->get_base_query_args( $count, $exclude_categories, $prime_meta_cache );
        $args['meta_key'] = 'ds_post_views_count';
        $args['orderby'] = array(
            'meta_value_num' => 'DESC',
            'date'           => 'DESC',
        );

        $post_ids = $this->query_post_ids( $args );

        if ( count( $post_ids ) >= $count ) {
            return array_slice( $post_ids, 0, $count );
        }

        $fallback_args = $this->get_base_query_args( $count - count( $post_ids ), $exclude_categories, $prime_meta_cache );
        $fallback_args['orderby'] = 'date';
        $fallback_args['order'] = 'DESC';
        if ( ! empty( $post_ids ) ) {
            $fallback_args['post__not_in'] = $post_ids;
        }

        $fallback_ids = $this->query_post_ids( $fallback_args );

        return array_slice( array_values( array_unique( array_merge( $post_ids, $fallback_ids ) ) ), 0, $count );
    }

    protected function get_random_post_ids( $count, $exclude_categories, $prime_meta_cache = false ) {
        $args = $this->get_base_query_args( $count, $exclude_categories, $prime_meta_cache );
        $args['orderby'] = 'rand';

        return $this->query_post_ids( $args );
    }

    protected function get_base_query_args( $count, $exclude_categories, $prime_meta_cache = false ) {
        $args = array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $count,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'fields'                 => 'ids',
            'cache_results'          => true,
            'update_post_meta_cache' => (bool) $prime_meta_cache,
            'update_post_term_cache' => false,
        );

        if ( ! empty( $exclude_categories ) ) {
            $args['category__not_in'] = $exclude_categories;
        }

        return $args;
    }

    protected function query_post_ids( $args ) {
        $query = new \WP_Query( $args );
        if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
            return array();
        }

        return array_values( array_filter( array_map( 'absint', $query->posts ) ) );
    }

    protected function parse_category_ids( $raw ) {
        if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
            return array();
        }

        $parts = preg_split( '/[\s,]+/', $raw );
        if ( ! is_array( $parts ) ) {
            return array();
        }

        $ids = array_map( 'absint', $parts );

        return array_values( array_unique( array_filter( $ids ) ) );
    }
}

add_action(
    'widgets_init',
    function() {
        register_widget( '\Developer_Starter\Widgets\Widget_Post_List' );
    }
);
