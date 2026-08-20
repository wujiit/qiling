<?php
/**
 * Advanced category filter AJAX helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_category_sort_choices' ) ) {
    /**
     * 获取分类页高级筛选可用排序项。
     *
     * @return array<string,string>
     */
    function developer_starter_get_category_sort_choices() {
        $choices = array(
            'latest'   => __( '最新', 'developer-starter' ),
            'random'   => __( '随机', 'developer-starter' ),
            'hot'      => __( '热门', 'developer-starter' ),
            'like'     => __( '点赞', 'developer-starter' ),
            'favorite' => __( '收藏', 'developer-starter' ),
        );

        return (array) apply_filters( 'developer_starter_category_sort_choices', $choices );
    }
}

if ( ! function_exists( 'developer_starter_get_category_sort_options' ) ) {
    /**
     * 获取后台启用的分类页排序项。
     *
     * @param array|null $theme_options 主题设置。
     * @return array<string,string>
     */
    function developer_starter_get_category_sort_options( $theme_options = null ) {
        $choices = developer_starter_get_category_sort_choices();

        if ( null === $theme_options ) {
            $theme_options = function_exists( 'developer_starter_get_options_cache' )
                ? developer_starter_get_options_cache()
                : get_option( 'developer_starter_options', array() );
        }
        $theme_options = is_array( $theme_options ) ? $theme_options : array();

        if ( array_key_exists( 'category_sort_options', $theme_options ) ) {
            $raw_options = $theme_options['category_sort_options'];
            if ( is_array( $raw_options ) ) {
                $enabled_keys = $raw_options;
            } else {
                $raw_options = trim( (string) $raw_options );
                $enabled_keys = '' === $raw_options ? array() : preg_split( '/[\s,]+/', $raw_options );
            }
        } else {
            $enabled_keys = array_keys( $choices );
        }

        $enabled_keys = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $enabled_keys ) ) ) );
        $enabled = array();
        foreach ( $enabled_keys as $enabled_key ) {
            if ( isset( $choices[ $enabled_key ] ) ) {
                $enabled[ $enabled_key ] = $choices[ $enabled_key ];
            }
        }

        if ( empty( $enabled ) && isset( $choices['latest'] ) ) {
            $enabled['latest'] = $choices['latest'];
        }

        return $enabled;
    }
}

if ( ! function_exists( 'developer_starter_get_category_default_sort' ) ) {
    /**
     * 获取分类页默认排序，并保证它存在于启用的排序项中。
     *
     * @param array|null $theme_options 主题设置。
     * @param array|null $enabled_sorts 已启用排序项。
     * @return string
     */
    function developer_starter_get_category_default_sort( $theme_options = null, $enabled_sorts = null ) {
        if ( null === $theme_options ) {
            $theme_options = function_exists( 'developer_starter_get_options_cache' )
                ? developer_starter_get_options_cache()
                : get_option( 'developer_starter_options', array() );
        }
        $theme_options = is_array( $theme_options ) ? $theme_options : array();
        $enabled_sorts = is_array( $enabled_sorts ) ? $enabled_sorts : developer_starter_get_category_sort_options( $theme_options );

        $default_sort = isset( $theme_options['category_default_sort'] ) ? sanitize_key( (string) $theme_options['category_default_sort'] ) : 'latest';
        if ( ! isset( $enabled_sorts[ $default_sort ] ) ) {
            $enabled_keys = array_keys( $enabled_sorts );
            $default_sort = ! empty( $enabled_keys ) ? (string) reset( $enabled_keys ) : 'latest';
        }

        return $default_sort;
    }
}

if ( ! function_exists( 'developer_starter_normalize_category_sort' ) ) {
    /**
     * 标准化分类排序值。
     *
     * @param string     $sort 排序值。
     * @param array|null $theme_options 主题设置。
     * @return string
     */
    function developer_starter_normalize_category_sort( $sort, $theme_options = null ) {
        $enabled_sorts = developer_starter_get_category_sort_options( $theme_options );
        $sort = sanitize_key( (string) $sort );

        if ( isset( $enabled_sorts[ $sort ] ) ) {
            return $sort;
        }

        return developer_starter_get_category_default_sort( $theme_options, $enabled_sorts );
    }
}

if ( ! function_exists( 'developer_starter_apply_category_sort_query_args' ) ) {
    /**
     * 将分类筛选排序规则应用到 WP_Query 参数。
     *
     * @param array  $query_args 查询参数。
     * @param string $sort 排序值。
     * @param int    $category_id 分类 ID。
     * @param array  $filters 当前筛选项。
     * @return array
     */
    function developer_starter_apply_category_sort_query_args( $query_args, $sort, $category_id = 0, $filters = array() ) {
        $sort = sanitize_key( (string) $sort );

        if ( 'random' === $sort ) {
            $random_seed = absint( sprintf( '%u', crc32( absint( $category_id ) . '|' . wp_json_encode( $filters ) ) ) );
            $query_args['orderby'] = 'RAND(' . max( 1, $random_seed ) . ')';
        } elseif ( 'hot' === $sort ) {
            $query_args['meta_key'] = 'ds_post_views_count';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';
        } elseif ( 'like' === $sort ) {
            $query_args['meta_key'] = 'post_like_count';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';
        } elseif ( 'favorite' === $sort ) {
            $query_args['meta_key'] = 'post_favorite_count';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';
        } else {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'DESC';
        }

        return $query_args;
    }
}

if ( ! function_exists( 'developer_starter_adv_category_filter' ) ) {
    /**
     * 高级分类筛选 AJAX 处理。
     *
     * @return void
     */
    function developer_starter_adv_category_filter() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'ds_adv_filter_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
        }

        if ( ! is_user_logged_in() ) {
            $window       = function_exists( 'developer_starter_get_rate_limit_window' ) ? developer_starter_get_rate_limit_window() : 60;
            $fallback_max = function_exists( 'developer_starter_get_option' ) ? intval( developer_starter_get_option( 'request_rate_limit_search_max', 30 ) ) : 30;
            $max          = function_exists( 'developer_starter_get_option' ) ? intval( developer_starter_get_option( 'request_rate_limit_adv_filter_max', $fallback_max ) ) : $fallback_max;
            $max          = max( 1, min( 300, $max ) );

            $is_limited = false;
            if (
                function_exists( 'developer_starter_is_public_rate_limit_enabled' )
                && function_exists( 'developer_starter_is_rate_limited' )
                && developer_starter_is_public_rate_limit_enabled()
            ) {
                $is_limited = developer_starter_is_rate_limited( 'public_adv_category_filter', $max, $window );
            } else {
                $ip               = developer_starter_get_client_ip();
                $hard_limit_key   = 'ds_adv_filter_rl_' . md5( (string) $ip );
                $hard_limit_count = (int) get_transient( $hard_limit_key );
                if ( $hard_limit_count >= $max ) {
                    $is_limited = true;
                } else {
                    set_transient( $hard_limit_key, $hard_limit_count + 1, $window );
                }
            }

            if ( $is_limited ) {
                wp_send_json_error(
                    array(
                        'message' => __( '请求过于频繁，请稍后再试', 'developer-starter' ),
                        'code'    => 'rate_limited',
                    ),
                    429
                );
            }
        }

        $category_id = isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0;
        $filters     = isset( $_POST['filters'] ) && is_array( $_POST['filters'] ) ? wp_unslash( $_POST['filters'] ) : array();
        $sort        = isset( $_POST['sort'] ) ? sanitize_text_field( wp_unslash( $_POST['sort'] ) ) : '';
        $paged       = isset( $_POST['paged'] ) ? absint( wp_unslash( $_POST['paged'] ) ) : 1;
        $paged       = max( 1, $paged );

        if ( ! $category_id ) {
            wp_send_json_error( array( 'message' => 'Invalid category' ) );
        }

        $settings               = Developer_Starter\Core\Category_Manager::get_category_settings( $category_id );
        $theme_options          = get_option( 'developer_starter_options', array() );
        $sort                   = developer_starter_normalize_category_sort( $sort, $theme_options );
        $video_category_enabled = ! empty( $settings['video_category_enabled'] );
        $video_plugin_active    = class_exists( 'ArtPlayer_Video_Frontend' );
        $video_list_enabled     = $video_category_enabled && $video_plugin_active;
        $video_frontend         = $video_list_enabled ? ArtPlayer_Video_Frontend::get_instance() : null;
        $resolved_archive_settings = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
            ? \Developer_Starter\Core\Blog_Visual_Manager::resolve_category_archive_settings( $category_id, $settings )
            : array(
                'layout'         => 'card',
                'excerpt_length' => 40,
            );

        $layout = ! empty( $resolved_archive_settings['layout'] ) ? (string) $resolved_archive_settings['layout'] : 'card';
        $layout_class_map = array(
            'card'     => array( 'container' => 'posts-card', 'item' => 'post-item-card' ),
            'list'     => array( 'container' => 'posts-list', 'item' => 'post-item-list' ),
            'grid'     => array( 'container' => 'posts-grid', 'item' => 'post-item-grid' ),
            'magazine' => array( 'container' => 'posts-magazine', 'item' => 'post-item-card' ),
            'video'    => array( 'container' => 'posts-video', 'item' => 'post-item-video' ),
        );
        $active_layout  = $layout;
        $layout_classes = isset( $layout_class_map[ $active_layout ] ) ? $layout_class_map[ $active_layout ] : $layout_class_map['card'];

        $hide_thumb        = ! empty( $resolved_archive_settings['hide_thumb'] );
        $hide_excerpt      = ! empty( $resolved_archive_settings['hide_excerpt'] );
        $hide_date         = ! empty( $resolved_archive_settings['hide_date'] );
        $hide_category_tag = ! empty( $resolved_archive_settings['hide_category'] );
        $hide_author       = ! empty( $resolved_archive_settings['hide_author'] );
        $excerpt_length    = isset( $resolved_archive_settings['excerpt_length'] ) ? intval( $resolved_archive_settings['excerpt_length'] ) : 40;
        $per_page          = ! empty( $resolved_archive_settings['posts_per_page'] ) ? intval( $resolved_archive_settings['posts_per_page'] ) : get_option( 'posts_per_page' );
        $per_page          = min( $per_page, 20 );

        $video_cover_enable           = ! empty( $theme_options['video_cover_enable'] );
        $video_badge_enable           = ! empty( $theme_options['video_badge_enable'] );

        $query_args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'cat'            => $category_id,
            'posts_per_page' => $per_page,
            'paged'          => $paged,
        );

        $meta_query = array();
        if ( ! empty( $filters ) ) {
            foreach ( $filters as $key => $value ) {
                $clean_key   = sanitize_key( $key );
                $clean_value = sanitize_text_field( (string) $value );
                if ( $clean_value === '' ) {
                    continue;
                }
                $meta_query[] = array(
                    'key'     => '_ds_adv_' . $clean_key,
                    'value'   => $clean_value,
                    'compare' => '=',
                );
            }
        }

        if ( ! empty( $meta_query ) ) {
            $meta_query['relation'] = 'AND';
            $query_args['meta_query'] = $meta_query;
        }

        $query_args = developer_starter_apply_category_sort_query_args( $query_args, $sort, $category_id, $filters );

        $query = developer_starter_run_cached_query(
            $query_args,
            'adv_category_filter',
            array(
                'needs_pagination' => true,
            )
        );

        ob_start();

        if ( $query->have_posts() ) {
            $qiling_category_loop_index    = 0;
            $qiling_category_loop_settings = array(
                'context'     => 'advanced_category_filter',
                'category_id' => $category_id,
            );
            $qiling_category_loop_post_ids = array();
            if ( ! empty( $query->posts ) ) {
                foreach ( $query->posts as $qiling_category_loop_post ) {
                    if ( $qiling_category_loop_post instanceof WP_Post ) {
                        $qiling_category_loop_post_ids[] = (int) $qiling_category_loop_post->ID;
                    } elseif ( is_numeric( $qiling_category_loop_post ) ) {
                        $qiling_category_loop_post_ids[] = (int) $qiling_category_loop_post;
                    }
                }
                $qiling_category_loop_post_ids = array_values( array_unique( array_filter( $qiling_category_loop_post_ids ) ) );
                if ( ! empty( $qiling_category_loop_post_ids ) ) {
                    update_meta_cache( 'post', $qiling_category_loop_post_ids );
                    if ( function_exists( 'developer_starter_prime_first_video_cache' ) ) {
                        developer_starter_prime_first_video_cache( $qiling_category_loop_post_ids );
                    }
                }
            }

            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                $video_data      = false;
                $has_video_cover = false;
                $has_video       = false;
                $video_meta      = null;
                $is_video_mode   = false;
                $video_rating    = 0;

                if ( $video_list_enabled && $video_frontend ) {
                    $video_meta    = $video_frontend->get_video_meta_public( $post_id );
                    $is_video_mode = $video_meta && ! empty( $video_meta->is_video_mode );
                    $video_rating  = $is_video_mode ? floatval( $video_meta->rating ) : 0;
                }

                $needs_first_video = ( $video_cover_enable || $video_badge_enable ) && function_exists( 'developer_starter_get_first_video' );
                if ( $needs_first_video ) {
                    $video_data = developer_starter_get_first_video( $post_id );
                    if ( $video_data ) {
                        $has_video = true;
                    }
                    if ( $video_cover_enable && $video_data && isset( $video_data['type'] ) && $video_data['type'] === 'video' ) {
                        $has_video_cover = true;
                    }
                }

                $cover_badges = function_exists( 'developer_starter_get_post_cover_badges' )
                    ? developer_starter_get_post_cover_badges(
                        $post_id,
                        array(
                            'context'                   => 'advanced_category_filter',
                            'theme_options'             => $theme_options,
                            'has_video'                 => $has_video,
                            'video_data'                => $video_data,
                            'video_meta'                => $video_meta,
                            'video_rating'              => $video_rating,
                            'video_category_enabled'    => $video_list_enabled,
                            'include_video_meta_badges' => $video_list_enabled,
                        )
                    )
                    : array();
                $has_cover_badges = ! empty( $cover_badges );

                $cover_image = '';
                if ( ! $hide_thumb ) {
                    if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
                        $cover_image = developer_starter_get_thumbnail_url( $post_id, 'medium' );
                    } elseif ( has_post_thumbnail() ) {
                        $cover_image = get_the_post_thumbnail_url( $post_id, 'medium_large' );
                    } elseif ( function_exists( 'developer_starter_get_first_image' ) ) {
                        $cover_image = developer_starter_get_first_image( $post_id );
                    }
                    if ( $has_video_cover && ! empty( $video_data['poster'] ) ) {
                        $cover_image = $video_data['poster'];
                    }
                }
                if ( $video_list_enabled && $is_video_mode && ! empty( $video_meta->cover_image ) ) {
                    $cover_image = $video_meta->cover_image;
                }
                ?>
                <article class="<?php echo esc_attr( $layout_classes['item'] ); ?><?php echo $has_video_cover ? ' has-video-cover' : ''; ?><?php echo $is_video_mode ? ' is-video-mode' : ''; ?>">
                    <?php if ( ! $hide_thumb && $cover_image ) : ?>
                        <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-thumb<?php echo $is_video_mode ? ' is-video-cover' : ''; ?>">
                            <img src="<?php echo esc_url( $cover_image ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async" />
                            <?php if ( $video_list_enabled ) : ?>
                                <span class="post-video-overlay"></span>
                                <span class="post-video-play">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                                <?php
                                $overlay_tags = array();
                                $adv_levels   = get_post_meta( $post_id, '_ds_adv_levels', true );
                                if ( is_array( $adv_levels ) ) {
                                    foreach ( $adv_levels as $adv_value ) {
                                        if ( $adv_value !== '' ) {
                                            $overlay_tags[] = $adv_value;
                                        }
                                    }
                                } else {
                                    $legacy_major = get_post_meta( $post_id, '_ds_adv_major_cat', true );
                                    $legacy_minor = get_post_meta( $post_id, '_ds_adv_minor_cat', true );
                                    if ( $legacy_major ) {
                                        $overlay_tags[] = $legacy_major;
                                    }
                                    if ( $legacy_minor ) {
                                        $overlay_tags[] = $legacy_minor;
                                    }
                                }
                                if ( empty( $overlay_tags ) ) {
                                    $overlay_categories = get_the_category();
                                    if ( ! empty( $overlay_categories ) ) {
                                        foreach ( $overlay_categories as $overlay_category ) {
                                            $overlay_tags[] = $overlay_category->name;
                                        }
                                    }
                                    $overlay_post_tags = get_the_tags();
                                    if ( ! empty( $overlay_post_tags ) ) {
                                        foreach ( $overlay_post_tags as $overlay_post_tag ) {
                                            $overlay_tags[] = $overlay_post_tag->name;
                                        }
                                    }
                                }
                                $overlay_tags = array_values( array_unique( array_filter( $overlay_tags ) ) );
                                $overlay_tags = array_slice( $overlay_tags, 0, 4 );
                                ?>
                                <?php if ( ! empty( $overlay_tags ) ) : ?>
                                    <span class="post-video-tags">
                                        <?php foreach ( $overlay_tags as $overlay_tag ) : ?>
                                            <span class="post-video-tag"><?php echo esc_html( $overlay_tag ); ?></span>
                                        <?php endforeach; ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ( $has_cover_badges && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                                <?php echo developer_starter_get_post_cover_badges_html( $cover_badges, array( 'context' => 'advanced_category_filter' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <div class="post-content">
                        <div class="post-meta-badges">
                            <?php if ( ! $hide_category_tag ) : ?>
                                <?php $cats = get_the_category(); ?>
                                <?php if ( ! empty( $cats ) ) : ?>
                                    <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="post-category-tag"><?php echo esc_html( $cats[0]->name ); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ( ! $hide_date || ! $hide_author ) : ?>
                            <div class="post-meta-info">
                                <?php if ( ! $hide_date ) : ?>
                                    <span class="post-date"><?php echo get_the_date(); ?></span>
                                <?php endif; ?>
                                <?php if ( ! $hide_author ) : ?>
                                    <span class="post-author"><?php the_author(); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <h2 class="post-title">
                            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
                        </h2>

                        <?php if ( ! $hide_excerpt ) : ?>
                            <p class="post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), $excerpt_length ); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php
                $qiling_category_loop_index++;
                do_action( 'qiling_blog_loop_after_item', $qiling_category_loop_index, $query, $qiling_category_loop_settings );
            }
        } else {
            ?>
            <div class="adv-filter-no-results" style="grid-column: 1 / -1; text-align: center; padding: var(--qiling-space-60) var(--qiling-space-20);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.5; margin-bottom: var(--qiling-space-16);">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <p style="margin: 0; color: var(--color-text-muted, var(--qiling-color-6b7280));"><?php esc_html_e( '没有找到符合条件的文章', 'developer-starter' ); ?></p>
            </div>
            <?php
        }

        $html = ob_get_clean();
        wp_reset_postdata();

        wp_send_json_success(
            array(
                'html'          => $html,
                'found'         => $query->found_posts,
                'current_page'  => $paged,
                'max_num_pages' => (int) $query->max_num_pages,
                'has_more'      => $paged < (int) $query->max_num_pages,
                'pagination'    => '',
            )
        );
    }
}
add_action( 'wp_ajax_ds_adv_category_filter', 'developer_starter_adv_category_filter' );
add_action( 'wp_ajax_nopriv_ds_adv_category_filter', 'developer_starter_adv_category_filter' );
