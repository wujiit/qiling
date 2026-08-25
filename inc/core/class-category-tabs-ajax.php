<?php
/**
 * Category Tabs Module AJAX Handler
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Category_Tabs_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_ds_load_category_tabs_posts', array( $this, 'load_posts' ) );
        add_action( 'wp_ajax_nopriv_ds_load_category_tabs_posts', array( $this, 'load_posts' ) );
    }

    /**
     * 公开读取分类 Tabs 文章列表。
     *
     * 该接口只返回公开文章，不把 nonce 当成权限边界；滥用控制由轻量限流、参数收敛和短缓存负责。
     */
    public function load_posts() {
        if ( $this->is_rate_limited() ) {
            wp_send_json_error( array( 'message' => __( '请求过于频繁，请稍后重试', 'developer-starter' ) ), 429 );
        }

        $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
        $id_str = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $page = isset( $_POST['page'] ) ? max( 1, absint( wp_unslash( $_POST['page'] ) ) ) : 1;
        $count = isset( $_POST['count'] ) ? absint( wp_unslash( $_POST['count'] ) ) : 8;
        $count = max( 1, min( $count, 24 ) );
        $config = $this->sanitize_config(
            isset( $_POST['config'] ) && is_array( $_POST['config'] )
                ? wp_unslash( $_POST['config'] )
                : array()
        );

        if ( empty( $type ) || empty( $id_str ) ) {
            wp_send_json_error( array( 'message' => __( '参数无效', 'developer-starter' ) ) );
        }

        if ( ! in_array( $type, array( 'category', 'tag' ), true ) ) {
            wp_send_json_error( array( 'message' => __( '分类类型无效', 'developer-starter' ) ) );
        }

        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $count,
            'paged'               => $page,
            'ignore_sticky_posts' => true,
        );

        // Parse IDs (comma separated)
        $ids = array_slice( array_filter( array_map( 'intval', explode( ',', $id_str ) ) ), 0, 20 );

        if ( empty( $ids ) ) {
            wp_send_json_error( array( 'message' => __( '无效的 ID', 'developer-starter' ) ) );
        }

        if ( $type === 'category' ) {
            $args['category__in'] = $ids;
        } elseif ( $type === 'tag' ) {
            $args['tag__in'] = $ids;
        }
        $args = apply_filters( 'developer_starter_category_tabs_query_args', $args, $type, $ids, $page, $count, $config );

        $cache_key = $this->build_response_cache_key( $args, $type, $ids, $page, $count, $config );
        $cached_response = $this->get_cached_response( $cache_key );
        if ( is_array( $cached_response ) ) {
            wp_send_json_success( $cached_response );
        }

        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query(
                $args,
                'module_category_tabs_' . sanitize_key( $type ),
                array(
                    'needs_pagination' => true,
                )
            );
        } else {
            $query = new \WP_Query( $args );
        }

        if ( $query->have_posts() ) {
            if ( function_exists( 'update_post_thumbnail_cache' ) ) {
                update_post_thumbnail_cache( $query );
            }
            ob_start();
            
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->render_post_item( get_the_ID(), $config );
            }
            
            $html = ob_get_clean();
            wp_reset_postdata();
            
            $response = array(
                'html'     => $html,
                'has_next' => $page < $query->max_num_pages,
                'next_page' => $page + 1,
            );
            $this->set_cached_response( $cache_key, $response );
            wp_send_json_success( $response );
        } else {
            $response = array(
                'html'     => '',
                'has_next' => false,
            );
            $this->set_cached_response( $cache_key, $response );
            wp_send_json_success( $response );
        }
    }

    /**
     * 分类 Tabs 是公开读接口，做轻量 IP 频率限制。
     *
     * @return bool
     */
    private function is_rate_limited() {
        $client_ip = developer_starter_get_client_ip();
        $rate_key = 'ds_cat_tabs_rate_' . md5( (string) $client_ip );
        $request_count = (int) get_transient( $rate_key );
        $limit = (int) apply_filters( 'developer_starter_category_tabs_rate_limit_max', 60, $client_ip );
        $limit = max( 10, min( 300, $limit ) );
        if ( $request_count >= $limit ) {
            return true;
        }
        $window = (int) apply_filters( 'developer_starter_category_tabs_rate_limit_window', MINUTE_IN_SECONDS, $client_ip );
        $window = max( 30, min( 5 * MINUTE_IN_SECONDS, $window ) );
        set_transient( $rate_key, $request_count + 1, $window );

        return false;
    }

    /**
     * 收敛前台传入的展示配置，避免公开接口接收任意大数组。
     *
     * @param array<string,mixed> $config 原始配置。
     * @return array<string,string>
     */
    private function sanitize_config( $config ) {
        if ( ! is_array( $config ) ) {
            return array();
        }

        $yes_no = array( 'yes', 'no' );
        $aspect_ratios = array( '16:9', '4:3', '1:1', '3:4', 'custom' );
        $show_date = $this->get_config_string( $config, 'show_date', 'yes' );
        $show_author = $this->get_config_string( $config, 'show_author', 'no' );
        $show_views = $this->get_config_string( $config, 'show_views', 'yes' );
        $show_category_badge = $this->get_config_string( $config, 'show_category_badge', 'no' );
        $show_title = $this->get_config_string( $config, 'show_title', 'yes' );
        $show_image_dimensions = $this->get_config_string( $config, 'show_image_dimensions', 'no' );
        $show_image_format = $this->get_config_string( $config, 'show_image_format', 'no' );
        $show_resource_badges = $this->get_config_string( $config, 'show_resource_badges', 'no' );
        $show_resource_price = $this->get_config_string( $config, 'show_resource_price', 'no' );
        $show_download_button = $this->get_config_string( $config, 'show_download_button', 'no' );
        $image_aspect_ratio = $this->get_config_string( $config, 'image_aspect_ratio', '16:9' );
        $card_style = $this->get_config_string( $config, 'card_style', 'default' );

        return array(
            'show_date' => in_array( $show_date, $yes_no, true )
                ? $show_date
                : 'yes',
            'show_author' => in_array( $show_author, $yes_no, true )
                ? $show_author
                : 'no',
            'show_views' => in_array( $show_views, $yes_no, true )
                ? $show_views
                : 'yes',
            'show_category_badge' => in_array( $show_category_badge, $yes_no, true )
                ? $show_category_badge
                : 'no',
            'card_style' => in_array( $card_style, array( 'default', 'wallpaper' ), true ) ? $card_style : 'default',
            'show_title' => in_array( $show_title, $yes_no, true ) ? $show_title : 'yes',
            'show_image_dimensions' => in_array( $show_image_dimensions, $yes_no, true ) ? $show_image_dimensions : 'no',
            'show_image_format' => in_array( $show_image_format, $yes_no, true ) ? $show_image_format : 'no',
            'show_resource_badges' => in_array( $show_resource_badges, $yes_no, true ) ? $show_resource_badges : 'no',
            'show_resource_price' => in_array( $show_resource_price, $yes_no, true ) ? $show_resource_price : 'no',
            'show_download_button' => in_array( $show_download_button, $yes_no, true ) ? $show_download_button : 'no',
            'download_button_text' => $this->truncate_config_text(
                $this->get_config_string( $config, 'download_button_text', __( '获取图片', 'developer-starter' ) ),
                40
            ),
            'image_aspect_ratio' => in_array( $image_aspect_ratio, $aspect_ratios, true )
                ? $image_aspect_ratio
                : '16:9',
            'image_height' => substr(
                $this->get_config_string( $config, 'image_height', '200px' ),
                0,
                32
            ),
            'columns' => (string) max(
                1,
                min( 5, absint( $this->get_config_string( $config, 'columns', '4' ) ) )
            ),
            'more_btn_type' => 'link' === $this->get_config_string( $config, 'more_btn_type', 'ajax' )
                ? 'link'
                : 'ajax',
        );
    }

    /**
     * 从配置数组中获取标量字符串。
     *
     * @param array<string,mixed> $config  配置数组。
     * @param string              $key     键名。
     * @param string              $default 默认值。
     * @return string
     */
    private function get_config_string( $config, $key, $default ) {
        if ( ! isset( $config[ $key ] ) || is_array( $config[ $key ] ) || is_object( $config[ $key ] ) ) {
            return $default;
        }

        return sanitize_text_field( (string) $config[ $key ] );
    }

    /**
     * 限制公开接口文案长度，同时避免截断多字节字符。
     */
    private function truncate_config_text( $value, $length ) {
        $value = (string) $value;
        $length = max( 1, absint( $length ) );
        return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $length, 'UTF-8' ) : substr( $value, 0, $length );
    }

    /**
     * 构建公开响应缓存键。
     *
     * @param array<string,mixed> $args   查询参数。
     * @param string              $type   分类源类型。
     * @param array<int,int>      $ids    分类/标签 ID。
     * @param int                 $page   页码。
     * @param int                 $count  每页数量。
     * @param array<string,mixed> $config 展示配置。
     * @return string
     */
    private function build_response_cache_key( $args, $type, $ids, $page, $count, $config ) {
        $last_changed = function_exists( 'wp_cache_get_last_changed' )
            ? wp_cache_get_last_changed( 'posts' )
            : '';
        $payload = array(
            'args'         => $args,
            'type'         => $type,
            'ids'          => $ids,
            'page'         => $page,
            'count'        => $count,
            'config'       => $config,
            'last_changed' => $last_changed,
        );

        return 'ds_cat_tabs_resp_' . md5( (string) wp_json_encode( $payload ) );
    }

    /**
     * 获取公开响应短缓存。
     *
     * @param string $cache_key 缓存键。
     * @return array<string,mixed>|false
     */
    private function get_cached_response( $cache_key ) {
        $cached = get_transient( $cache_key );
        return is_array( $cached ) ? $cached : false;
    }

    /**
     * 写入公开响应短缓存。
     *
     * @param string              $cache_key 缓存键。
     * @param array<string,mixed> $response  响应数据。
     * @return void
     */
    private function set_cached_response( $cache_key, $response ) {
        $ttl = (int) apply_filters( 'developer_starter_category_tabs_response_cache_ttl', 5 * MINUTE_IN_SECONDS, $response );
        $ttl = max( 0, min( HOUR_IN_SECONDS, $ttl ) );
        if ( $ttl <= 0 ) {
            return;
        }

        set_transient( $cache_key, $response, $ttl );
    }

    private function render_post_item( $post_id, $config ) {
        // Extract config
        $show_date = isset($config['show_date']) && $config['show_date'] === 'yes';
        $show_author = isset($config['show_author']) && $config['show_author'] === 'yes';
        $show_views = isset($config['show_views']) && $config['show_views'] === 'yes';
        $show_category_badge = isset($config['show_category_badge']) && $config['show_category_badge'] === 'yes';
        $show_title = ! isset( $config['show_title'] ) || $config['show_title'] === 'yes';
        $show_image_dimensions = isset( $config['show_image_dimensions'] ) && $config['show_image_dimensions'] === 'yes';
        $show_image_format = isset( $config['show_image_format'] ) && $config['show_image_format'] === 'yes';
        $show_resource_badges = isset( $config['show_resource_badges'] ) && $config['show_resource_badges'] === 'yes';
        $show_resource_price = isset( $config['show_resource_price'] ) && $config['show_resource_price'] === 'yes';
        $show_download_button = isset( $config['show_download_button'] ) && $config['show_download_button'] === 'yes';
        $download_button_text = isset( $config['download_button_text'] ) && '' !== trim( (string) $config['download_button_text'] )
            ? (string) $config['download_button_text']
            : __( '获取图片', 'developer-starter' );
        $card_style = isset( $config['card_style'] ) && 'wallpaper' === $config['card_style'] ? 'wallpaper' : 'default';
        $aspect_ratio = isset($config['image_aspect_ratio']) ? $config['image_aspect_ratio'] : '16:9';
        $custom_height = isset($config['image_height']) ? $config['image_height'] : '200px';

        // Calculate height style based on aspect ratio
        $wrapper_style = '';
        $img_style = '';
        
        // This class setup relies on CSS for aspect ratio usually, but inline style works for custom
        if ( $aspect_ratio === 'custom' ) {
            $img_style = 'height: ' . esc_attr( $custom_height ) . '; object-fit: cover;';
        } else {
            // Apply a class or style for aspect ratio. 
            // For simplicity in this AJAX handler, we'll try to use a padding-hack wrapper or simple object-fit
            // Assuming the CSS will handle .ratio-16-9 etc.
        }

        // Check for video
        $video_data = false;
        $has_video_cover = false;
        if ( function_exists( 'developer_starter_get_first_video' ) ) {
             $video_data = developer_starter_get_first_video( $post_id );
             if ( $video_data && $video_data['type'] === 'video' ) {
                 $has_video_cover = true;
             }
        }

        // Get Thumbnail
        $thumbnail_url = '';
        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
            $thumbnail_url = developer_starter_get_thumbnail_url( $post_id, 'medium' );
        } elseif ( has_post_thumbnail( $post_id ) ) {
            $thumbnail_url = get_the_post_thumbnail_url( $post_id, 'medium' );
        }
        
        // If has video poster, use it
        if ( $has_video_cover && ! empty( $video_data['poster'] ) ) {
            $thumbnail_url = $video_data['poster'];
        }
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        $image_meta = $thumbnail_id ? wp_get_attachment_metadata( $thumbnail_id ) : array();
        $image_width = is_array( $image_meta ) && ! empty( $image_meta['width'] ) ? absint( $image_meta['width'] ) : 0;
        $image_height = is_array( $image_meta ) && ! empty( $image_meta['height'] ) ? absint( $image_meta['height'] ) : 0;
        $image_format = '';
        if ( $thumbnail_id ) {
            $mime_type = (string) get_post_mime_type( $thumbnail_id );
            if ( 0 === strpos( $mime_type, 'image/' ) ) {
                $image_format = strtoupper( substr( $mime_type, 6 ) );
                if ( 'JPEG' === $image_format ) {
                    $image_format = 'JPG';
                }
            }
        }

        $resource = array();
        if ( ( $show_resource_badges || $show_resource_price ) && function_exists( 'developer_starter_get_qilingshop_resource_snapshot' ) ) {
            $resource = developer_starter_get_qilingshop_resource_snapshot( $post_id );
        }
        $has_resource = ! empty( $resource['has_resource'] );
        $resource_price_text = '';
        if ( $show_resource_price && $has_resource ) {
            if ( ! empty( $resource['is_free'] ) ) {
                $resource_price_text = __( '免费', 'developer-starter' );
            } elseif ( ! empty( $resource['has_price'] ) && isset( $resource['points_price'] ) ) {
                $resource_price_text = sprintf( __( '%s 积分', 'developer-starter' ), number_format_i18n( (float) $resource['points_price'], 0 ) );
            } elseif ( ! empty( $resource['is_vip'] ) ) {
                $resource_price_text = __( 'VIP权益', 'developer-starter' );
            }
        }
        $video_preview_src = ( $has_video_cover && ! empty( $video_data['preview_src'] ) ) ? $video_data['preview_src'] : ( $video_data['url'] ?? '' );
        $video_badges = function_exists( 'developer_starter_get_post_cover_badges' )
            ? developer_starter_get_post_cover_badges(
                $post_id,
                array(
                    'context'                              => 'category_tabs',
                    'has_video'                            => (bool) $video_data,
                    'has_video_cover'                      => $has_video_cover,
                    'ignore_video_badge_setting'           => true,
                    'ignore_max_count'                     => true,
                    'include_types'                        => array( 'video' ),
                    'include_app_badge'                     => false,
                    'include_album_badge'                   => false,
                    'include_resource_badges'               => false,
                    'suppress_video_badge_when_video_cover' => true,
                    'video_icon_only'                      => true,
                    'video_badge_class'                    => 'video-badge',
                )
            )
            : array();
        $category_badges = ( $show_category_badge || $show_resource_badges ) && function_exists( 'developer_starter_get_post_cover_badges' )
            ? developer_starter_get_post_cover_badges(
                $post_id,
                array(
                    'context'                => 'category_tabs',
                    'ignore_max_count'       => true,
                    'include_types'          => array_merge(
                        $show_category_badge ? array( 'category' ) : array(),
                        $show_resource_badges ? array( 'free', 'vip' ) : array()
                    ),
                    'include_app_badge'      => false,
                    'include_album_badge'    => false,
                    'include_resource_badges' => $show_resource_badges,
                    'include_category_badge' => $show_category_badge,
                    'category_badge_class'   => 'post-cat-badge',
                )
            )
            : array();

        // Fallback image - REMOVED as per user request to not show broken icon
        // if ( empty( $thumbnail_url ) ) {
        //    $thumbnail_url = DEVELOPER_STARTER_URI . '/assets/images/no-image.png'; 
        // }

        ?>
        <article class="cat-tab-post-item card-style-<?php echo esc_attr( $card_style ); ?> <?php echo 'ratio-' . esc_attr( str_replace( ':', '-', $aspect_ratio ) ); ?>">
            <div class="post-media<?php echo $has_video_cover ? ' has-video-cover' : ''; ?>">
                <?php if ( $has_video_cover ) : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-thumbnail-link video-cover-link" <?php echo $aspect_ratio === 'custom' ? 'style="height:' . esc_attr($custom_height) . '"' : ''; ?>>
                        <?php if ( $thumbnail_url ) : ?>
                            <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php the_title_attribute(); ?>" class="video-poster" loading="lazy">
                        <?php endif; ?>
                        
                        <video class="video-cover-player" src="<?php echo esc_url( $video_preview_src ); ?>" muted loop playsinline preload="<?php echo $thumbnail_url ? 'metadata' : 'auto'; ?>" <?php if ( $thumbnail_url ) : ?>poster="<?php echo esc_url( $thumbnail_url ); ?>"<?php endif; ?>></video>
                        
                        <div class="video-play-overlay">
                            <svg class="play-icon" width="48" height="48" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-thumbnail-link" <?php echo $aspect_ratio === 'custom' ? 'style="height:' . esc_attr($custom_height) . '"' : ''; ?>>
                        <?php if ( $thumbnail_url ) : ?>
                            <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                
                <?php if ( ! empty( $video_badges ) && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                    <?php echo developer_starter_get_post_cover_badges_html( $video_badges, array( 'context' => 'category_tabs', 'wrapper' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endif; ?>
                
                <?php if ( ! empty( $category_badges ) && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                    <?php echo developer_starter_get_post_cover_badges_html( $category_badges, array( 'context' => 'category_tabs', 'wrapper_class' => 'post-cat-badges', 'use_position_class' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endif; ?>
            </div>

            <div class="post-body">
                <?php if ( $show_title ) : ?>
                    <h3 class="post-title">
                        <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
                    </h3>
                <?php endif; ?>

                <?php if ( ( $show_image_dimensions && $image_width && $image_height ) || ( $show_image_format && $image_format ) || '' !== $resource_price_text ) : ?>
                    <div class="post-image-facts">
                        <?php if ( $show_image_dimensions && $image_width && $image_height ) : ?>
                            <span><?php echo esc_html( $image_width . ' × ' . $image_height ); ?></span>
                        <?php endif; ?>
                        <?php if ( $show_image_format && $image_format ) : ?>
                            <span><?php echo esc_html( $image_format ); ?></span>
                        <?php endif; ?>
                        <?php if ( '' !== $resource_price_text ) : ?>
                            <span class="resource-price"><?php echo esc_html( $resource_price_text ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="post-meta">
                    <?php if ( $show_date ) : ?>
                        <span class="meta-item meta-date">
                           <?php echo get_the_date(); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ( $show_author ) : ?>
                        <span class="meta-item meta-author"><?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?></span>
                    <?php endif; ?>
                    
                    <?php if ( $show_views && function_exists('developer_starter_get_post_views') ) : ?>
                        <span class="meta-item meta-views">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <?php echo developer_starter_get_post_views( $post_id ); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ( $show_download_button ) : ?>
                    <a class="cat-tab-resource-action" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                        <span><?php echo esc_html( $download_button_text ); ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}

new Category_Tabs_Ajax();
