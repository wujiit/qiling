<?php
/**
 * Post cover badge helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_post_cover_badge_options' ) ) {
    /**
     * Get theme options used by cover badges.
     *
     * @return array
     */
    function developer_starter_get_post_cover_badge_options() {
        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        return apply_filters( 'developer_starter_post_cover_badge_options', $options );
    }
}

if ( ! function_exists( 'developer_starter_get_post_cover_badge_default_priorities' ) ) {
    /**
     * Badge display priorities. Lower numbers render first.
     *
     * @return array
     */
    function developer_starter_get_post_cover_badge_default_priorities() {
        $priorities = array(
            'hd'       => 10,
            'rating'   => 15,
            'video'    => 20,
            'app'      => 30,
            'album'    => 40,
            'free'     => 50,
            'vip'      => 60,
            'sticky'   => 70,
            'category' => 80,
        );

        return apply_filters( 'developer_starter_post_cover_badge_default_priorities', $priorities );
    }
}

if ( ! function_exists( 'developer_starter_get_post_cover_badge_label' ) ) {
    /**
     * Get a configurable badge label.
     *
     * @param string $type Badge type.
     * @param string $default Default label.
     * @param array  $options Theme options.
     * @return string
     */
    function developer_starter_get_post_cover_badge_label( $type, $default, $options = array() ) {
        $type = sanitize_key( $type );
        $key  = 'cover_badge_' . $type . '_label';

        $label = isset( $options[ $key ] ) && trim( (string) $options[ $key ] ) !== ''
            ? trim( (string) $options[ $key ] )
            : (string) $default;

        return (string) apply_filters( 'developer_starter_post_cover_badge_label', $label, $type, $default, $options );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_post_cover_badge_class_list' ) ) {
    /**
     * Sanitize a space-separated CSS class list.
     *
     * @param string $class_list Class list.
     * @return string
     */
    function developer_starter_sanitize_post_cover_badge_class_list( $class_list ) {
        $classes = preg_split( '/\s+/', trim( (string) $class_list ) );
        $classes = array_filter(
            array_map(
                static function ( $class_name ) {
                    return sanitize_html_class( $class_name );
                },
                is_array( $classes ) ? $classes : array()
            )
        );

        return implode( ' ', array_unique( $classes ) );
    }
}

if ( ! function_exists( 'developer_starter_normalize_post_cover_badge' ) ) {
    /**
     * Normalize a badge item before sorting/rendering.
     *
     * @param array $badge Raw badge item.
     * @param array $priorities Priority map.
     * @return array|null
     */
    function developer_starter_normalize_post_cover_badge( $badge, $priorities = array() ) {
        if ( ! is_array( $badge ) || empty( $badge['type'] ) ) {
            return null;
        }

        $type = sanitize_key( (string) $badge['type'] );
        if ( '' === $type ) {
            return null;
        }

        $badge['type']     = $type;
        $badge['label']    = isset( $badge['label'] ) ? (string) $badge['label'] : '';
        $badge['class']    = isset( $badge['class'] ) ? developer_starter_sanitize_post_cover_badge_class_list( (string) $badge['class'] ) : 'post-badge-' . $type;
        $badge['tag']      = isset( $badge['tag'] ) && in_array( $badge['tag'], array( 'span', 'a' ), true ) ? $badge['tag'] : 'span';
        $badge['url']      = isset( $badge['url'] ) ? (string) $badge['url'] : '';
        $badge['icon']     = ! empty( $badge['icon'] );
        $badge['priority'] = isset( $badge['priority'] ) ? (int) $badge['priority'] : ( isset( $priorities[ $type ] ) ? (int) $priorities[ $type ] : 100 );
        $badge['attrs']    = isset( $badge['attrs'] ) && is_array( $badge['attrs'] ) ? $badge['attrs'] : array();

        return $badge;
    }
}

if ( ! function_exists( 'developer_starter_apply_post_cover_badge_order' ) ) {
    /**
     * Apply custom order text such as "video,app,album,free,vip".
     *
     * @param array $badges Badge list.
     * @param array $options Theme options.
     * @return array
     */
    function developer_starter_apply_post_cover_badge_order( $badges, $options ) {
        $order_text = isset( $options['cover_badge_order'] ) ? trim( (string) $options['cover_badge_order'] ) : '';
        if ( '' === $order_text ) {
            return $badges;
        }

        $order = array();
        foreach ( preg_split( '/[\s,，|]+/', $order_text ) as $index => $type ) {
            $type = sanitize_key( $type );
            if ( '' !== $type && ! isset( $order[ $type ] ) ) {
                $order[ $type ] = ( $index + 1 ) * 10;
            }
        }

        if ( empty( $order ) ) {
            return $badges;
        }

        foreach ( $badges as $badge_index => $badge ) {
            if ( isset( $badge['type'], $order[ $badge['type'] ] ) ) {
                $badges[ $badge_index ]['priority'] = $order[ $badge['type'] ];
            } else {
                $badges[ $badge_index ]['priority'] = 1000 + ( isset( $badge['priority'] ) ? (int) $badge['priority'] : 100 );
            }
        }

        return $badges;
    }
}

if ( ! function_exists( 'developer_starter_sort_post_cover_badges' ) ) {
    /**
     * Sort badges by priority and original position.
     *
     * @param array $badges Badge list.
     * @return array
     */
    function developer_starter_sort_post_cover_badges( $badges ) {
        foreach ( $badges as $index => $badge ) {
            $badges[ $index ]['_index'] = $index;
        }

        usort(
            $badges,
            static function ( $a, $b ) {
                $priority_a = isset( $a['priority'] ) ? (int) $a['priority'] : 100;
                $priority_b = isset( $b['priority'] ) ? (int) $b['priority'] : 100;

                if ( $priority_a === $priority_b ) {
                    $index_a = isset( $a['_index'] ) ? (int) $a['_index'] : 0;
                    $index_b = isset( $b['_index'] ) ? (int) $b['_index'] : 0;
                    return $index_a <=> $index_b;
                }

                return $priority_a <=> $priority_b;
            }
        );

        foreach ( $badges as $index => $badge ) {
            unset( $badges[ $index ]['_index'] );
        }

        return $badges;
    }
}

if ( ! function_exists( 'developer_starter_limit_post_cover_badges' ) ) {
    /**
     * Limit badges by context or global option.
     *
     * @param array $badges Badge list.
     * @param array $context Render context.
     * @param array $options Theme options.
     * @return array
     */
    function developer_starter_limit_post_cover_badges( $badges, $context, $options ) {
        if ( ! empty( $context['ignore_max_count'] ) ) {
            return $badges;
        }

        $max_count = array_key_exists( 'max_count', $context )
            ? absint( $context['max_count'] )
            : ( isset( $options['cover_badge_max_count'] ) ? absint( $options['cover_badge_max_count'] ) : 0 );

        if ( $max_count > 0 ) {
            $badges = array_slice( $badges, 0, $max_count );
        }

        return $badges;
    }
}

if ( ! function_exists( 'developer_starter_filter_post_cover_badges_by_type' ) ) {
    /**
     * Filter badges by include/exclude types from context.
     *
     * @param array $badges Badge list.
     * @param array $context Render context.
     * @return array
     */
    function developer_starter_filter_post_cover_badges_by_type( $badges, $context ) {
        $include_types = isset( $context['include_types'] ) && is_array( $context['include_types'] )
            ? array_filter( array_map( 'sanitize_key', $context['include_types'] ) )
            : array();
        $exclude_types = isset( $context['exclude_types'] ) && is_array( $context['exclude_types'] )
            ? array_filter( array_map( 'sanitize_key', $context['exclude_types'] ) )
            : array();

        if ( empty( $include_types ) && empty( $exclude_types ) ) {
            return $badges;
        }

        return array_values(
            array_filter(
                $badges,
                static function ( $badge ) use ( $include_types, $exclude_types ) {
                    $type = isset( $badge['type'] ) ? sanitize_key( (string) $badge['type'] ) : '';
                    if ( '' === $type ) {
                        return false;
                    }
                    if ( ! empty( $include_types ) && ! in_array( $type, $include_types, true ) ) {
                        return false;
                    }
                    if ( ! empty( $exclude_types ) && in_array( $type, $exclude_types, true ) ) {
                        return false;
                    }
                    return true;
                }
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_get_post_cover_badges' ) ) {
    /**
     * Build cover badges for a post.
     *
     * @param int   $post_id Post ID.
     * @param array $context Context flags and precomputed data.
     * @return array
     */
    function developer_starter_get_post_cover_badges( $post_id, $context = array() ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array();
        }

        $context    = is_array( $context ) ? $context : array();
        $options    = isset( $context['theme_options'] ) && is_array( $context['theme_options'] )
            ? $context['theme_options']
            : developer_starter_get_post_cover_badge_options();
        $priorities = developer_starter_get_post_cover_badge_default_priorities();
        $badges     = array();

        $has_video = ! empty( $context['has_video'] );
        $video_data = isset( $context['video_data'] ) ? $context['video_data'] : null;
        if ( ! $has_video && $video_data ) {
            $has_video = true;
        }

        $video_meta = isset( $context['video_meta'] ) ? $context['video_meta'] : null;
        $video_category_enabled = ! empty( $context['video_category_enabled'] );
        $include_video_meta_badges = ! empty( $context['include_video_meta_badges'] ) || $video_category_enabled;

        if ( $include_video_meta_badges && $video_meta ) {
            $video_quality = isset( $context['video_quality'] ) ? (string) $context['video_quality'] : '';
            if ( '' === $video_quality && isset( $video_meta->video_quality ) ) {
                $video_quality = (string) $video_meta->video_quality;
            }
            if ( '' !== trim( $video_quality ) ) {
                $badges[] = array(
                    'type'     => 'hd',
                    'label'    => $video_quality,
                    'class'    => 'post-video-hd-badge',
                    'priority' => isset( $priorities['hd'] ) ? $priorities['hd'] : 10,
                );
            }

            $video_rating = isset( $context['video_rating'] ) ? (float) $context['video_rating'] : 0.0;
            if ( $video_rating <= 0 && isset( $video_meta->rating ) ) {
                $video_rating = (float) $video_meta->rating;
            }
            if ( $video_rating > 0 ) {
                $badges[] = array(
                    'type'     => 'rating',
                    'label'    => number_format( $video_rating, 1 ),
                    'class'    => 'post-video-rating',
                    'priority' => isset( $priorities['rating'] ) ? $priorities['rating'] : 15,
                );
            }
        }

        $ignore_video_setting = ! empty( $context['ignore_video_badge_setting'] );
        $video_badge_enabled  = $ignore_video_setting || ! empty( $options['video_badge_enable'] );
        $suppress_video_badge = ( ! empty( $context['suppress_video_badge_when_category_mode'] ) && $video_category_enabled )
            || ( ! empty( $context['suppress_video_badge_when_video_cover'] ) && ! empty( $context['has_video_cover'] ) );
        if ( $has_video && $video_badge_enabled && ! $suppress_video_badge ) {
            $video_icon_only = ! empty( $context['video_icon_only'] );
            $badges[] = array(
                'type'     => 'video',
                'label'    => $video_icon_only ? '' : developer_starter_get_post_cover_badge_label( 'video', __( '视频', 'developer-starter' ), $options ),
                'class'    => isset( $context['video_badge_class'] ) ? (string) $context['video_badge_class'] : 'post-badge post-badge-video',
                'icon'     => true,
                'priority' => isset( $priorities['video'] ) ? $priorities['video'] : 20,
            );
        }

        if ( ! array_key_exists( 'include_app_badge', $context ) || ! empty( $context['include_app_badge'] ) ) {
            $app_badge_enabled = function_exists( 'developer_starter_qiapp_is_available' )
                && developer_starter_qiapp_is_available()
                && ! empty( $options['app_badge_enable'] );
            if ( $app_badge_enabled && function_exists( 'developer_starter_qiapp_has_software_data' ) && developer_starter_qiapp_has_software_data( $post_id ) ) {
                $badges[] = array(
                    'type'     => 'app',
                    'label'    => developer_starter_get_post_cover_badge_label( 'app', __( 'APP', 'developer-starter' ), $options ),
                    'class'    => 'post-badge post-badge-app',
                    'priority' => isset( $priorities['app'] ) ? $priorities['app'] : 30,
                );
            }
        }

        if ( ! array_key_exists( 'include_album_badge', $context ) || ! empty( $context['include_album_badge'] ) ) {
            if ( ! empty( $options['album_badge_enable'] ) && get_post_meta( $post_id, '_qiling_gallery_mode', true ) === '1' ) {
                $badges[] = array(
                    'type'     => 'album',
                    'label'    => developer_starter_get_post_cover_badge_label( 'album', __( '相册', 'developer-starter' ), $options ),
                    'class'    => 'post-badge post-badge-album',
                    'priority' => isset( $priorities['album'] ) ? $priorities['album'] : 40,
                );
            }
        }

        if ( ! array_key_exists( 'include_resource_badges', $context ) || ! empty( $context['include_resource_badges'] ) ) {
            $qilingshop_resource = isset( $context['qilingshop_resource'] ) && is_array( $context['qilingshop_resource'] )
                ? $context['qilingshop_resource']
                : array();
            if ( empty( $qilingshop_resource ) && function_exists( 'developer_starter_get_qilingshop_resource_snapshot' ) ) {
                $qilingshop_resource = developer_starter_get_qilingshop_resource_snapshot( $post_id );
            }

            if ( ! empty( $qilingshop_resource['has_resource'] ) ) {
                $resource_is_free = ! empty( $qilingshop_resource['is_free'] );
                if ( $resource_is_free && ! empty( $options['qilingshop_free_badge_enable'] ) ) {
                    $badges[] = array(
                        'type'     => 'free',
                        'label'    => developer_starter_get_post_cover_badge_label( 'free', __( '免费', 'developer-starter' ), $options ),
                        'class'    => 'post-badge post-badge-free',
                        'priority' => isset( $priorities['free'] ) ? $priorities['free'] : 50,
                    );
                }
                if ( ! $resource_is_free && ! empty( $options['qilingshop_vip_badge_enable'] ) && ( ! empty( $qilingshop_resource['is_paid'] ) || ! empty( $qilingshop_resource['is_vip'] ) ) ) {
                    $badges[] = array(
                        'type'     => 'vip',
                        'label'    => developer_starter_get_post_cover_badge_label( 'vip', __( 'VIP', 'developer-starter' ), $options ),
                        'class'    => 'post-badge post-badge-vip',
                        'priority' => isset( $priorities['vip'] ) ? $priorities['vip'] : 60,
                    );
                }
            }
        }

        if ( ! empty( $context['include_sticky_badge'] ) && is_sticky( $post_id ) ) {
            $badges[] = array(
                'type'     => 'sticky',
                'label'    => developer_starter_get_post_cover_badge_label( 'sticky', __( '置顶', 'developer-starter' ), $options ),
                'class'    => isset( $context['sticky_badge_class'] ) ? (string) $context['sticky_badge_class'] : 'qiling-post-card-badge qiling-post-card-badge-sticky',
                'priority' => isset( $priorities['sticky'] ) ? $priorities['sticky'] : 70,
            );
        }

        if ( ! empty( $context['include_category_badge'] ) ) {
            $categories = get_the_category( $post_id );
            if ( ! empty( $categories ) ) {
                $category = $categories[0];
                $category_url = get_category_link( $category->term_id );
                if ( is_wp_error( $category_url ) ) {
                    $category_url = '';
                }
                $badges[] = array(
                    'type'     => 'category',
                    'label'    => $category->name,
                    'class'    => isset( $context['category_badge_class'] ) ? (string) $context['category_badge_class'] : 'post-cat-badge',
                    'tag'      => 'a',
                    'url'      => $category_url,
                    'priority' => isset( $priorities['category'] ) ? $priorities['category'] : 80,
                );
            }
        }

        $normalized = array();
        foreach ( $badges as $badge ) {
            $badge = developer_starter_normalize_post_cover_badge( $badge, $priorities );
            if ( $badge ) {
                $normalized[] = $badge;
            }
        }

        $normalized = developer_starter_filter_post_cover_badges_by_type( $normalized, $context );
        $normalized = apply_filters( 'developer_starter_post_cover_badges', $normalized, $post_id, $context, $options );
        $normalized = developer_starter_apply_post_cover_badge_order( $normalized, $options );
        $normalized = developer_starter_sort_post_cover_badges( $normalized );
        $normalized = developer_starter_limit_post_cover_badges( $normalized, $context, $options );

        return array_values( $normalized );
    }
}

if ( ! function_exists( 'developer_starter_get_post_cover_badge_icon_html' ) ) {
    /**
     * Get built-in badge icon HTML.
     *
     * @param string $type Badge type.
     * @return string
     */
    function developer_starter_get_post_cover_badge_icon_html( $type ) {
        $type = sanitize_key( $type );
        $icon = '';

        if ( 'video' === $type ) {
            $icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>';
        }

        return (string) apply_filters( 'developer_starter_post_cover_badge_icon_html', $icon, $type );
    }
}

if ( ! function_exists( 'developer_starter_get_post_cover_badge_position_class' ) ) {
    /**
     * Get wrapper position class from context or option.
     *
     * @param array $context Render context.
     * @return string
     */
    function developer_starter_get_post_cover_badge_position_class( $context = array() ) {
        $position = isset( $context['position'] ) ? sanitize_key( (string) $context['position'] ) : '';
        if ( '' === $position ) {
            $options  = developer_starter_get_post_cover_badge_options();
            $position = isset( $options['cover_badge_position'] ) ? sanitize_key( (string) $options['cover_badge_position'] ) : '';
        }

        if ( ! in_array( $position, array( 'top-left', 'top-right', 'bottom-left', 'bottom-right' ), true ) ) {
            $position = 'top-right';
        }

        return 'is-position-' . $position;
    }
}

if ( ! function_exists( 'developer_starter_get_post_cover_badges_html' ) ) {
    /**
     * Render badge list HTML.
     *
     * @param array $badges Badge list.
     * @param array $context Render context.
     * @return string
     */
    function developer_starter_get_post_cover_badges_html( $badges, $context = array() ) {
        if ( empty( $badges ) || ! is_array( $badges ) ) {
            return '';
        }

        $context = is_array( $context ) ? $context : array();
        $items   = array();

        foreach ( $badges as $badge ) {
            $badge = developer_starter_normalize_post_cover_badge( $badge, developer_starter_get_post_cover_badge_default_priorities() );
            if ( ! $badge ) {
                continue;
            }

            $class = trim( (string) $badge['class'] );
            if ( '' === $class ) {
                continue;
            }

            $label = (string) $badge['label'];
            $icon  = ! empty( $badge['icon'] ) ? developer_starter_get_post_cover_badge_icon_html( $badge['type'] ) : '';
            $body  = $icon . ( '' !== $label ? esc_html( $label ) : '' );
            if ( '' === $body ) {
                continue;
            }

            $attrs = array(
                'class' => $class,
            );
            if ( ! empty( $badge['attrs'] ) && is_array( $badge['attrs'] ) ) {
                foreach ( $badge['attrs'] as $attr_key => $attr_value ) {
                    $attr_key = sanitize_key( (string) $attr_key );
                    if ( '' !== $attr_key && ! in_array( $attr_key, array( 'href', 'class' ), true ) ) {
                        $attrs[ $attr_key ] = (string) $attr_value;
                    }
                }
            }

            $attr_html = '';
            foreach ( $attrs as $attr_key => $attr_value ) {
                $attr_html .= ' ' . $attr_key . '="' . esc_attr( $attr_value ) . '"';
            }

            if ( 'a' === $badge['tag'] && '' !== $badge['url'] ) {
                $item = '<a href="' . esc_url( $badge['url'] ) . '"' . $attr_html . '>' . $body . '</a>';
            } else {
                $item = '<span' . $attr_html . '>' . $body . '</span>';
            }

            $items[] = (string) apply_filters( 'developer_starter_post_cover_badge_html', $item, $badge, $context );
        }

        if ( empty( $items ) ) {
            return '';
        }

        if ( array_key_exists( 'wrapper', $context ) && ! $context['wrapper'] ) {
            return implode( '', $items );
        }

        $wrapper_class = isset( $context['wrapper_class'] ) ? (string) $context['wrapper_class'] : 'post-video-corner-badges';
        $position_class = ( ! array_key_exists( 'use_position_class', $context ) || ! empty( $context['use_position_class'] ) )
            ? developer_starter_get_post_cover_badge_position_class( $context )
            : '';
        $wrapper_class = developer_starter_sanitize_post_cover_badge_class_list( trim( $wrapper_class . ' ' . $position_class ) );

        $html = '<div class="' . esc_attr( $wrapper_class ) . '">' . implode( '', $items ) . '</div>';

        return (string) apply_filters( 'developer_starter_post_cover_badges_html', $html, $badges, $context );
    }
}

if ( ! function_exists( 'developer_starter_render_post_cover_badges' ) ) {
    /**
     * Build and echo cover badge HTML for a post.
     *
     * @param int   $post_id Post ID.
     * @param array $context Context flags and render options.
     * @return void
     */
    function developer_starter_render_post_cover_badges( $post_id, $context = array() ) {
        $badges = developer_starter_get_post_cover_badges( $post_id, $context );
        echo developer_starter_get_post_cover_badges_html( $badges, $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
