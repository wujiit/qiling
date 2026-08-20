<?php
/**
 * Single post template.
 *
 * @package Developer_Starter
 */
$post_id = get_queried_object_id();
$resource_detail_contexts = array();
if ( function_exists( 'developer_starter_get_qilingshop_resource_snapshot' ) ) {
    $qls_resource_snapshot = developer_starter_get_qilingshop_resource_snapshot( $post_id );
    if ( ! empty( $qls_resource_snapshot['has_resource'] ) ) {
        $resource_detail_contexts[] = 'shop';
    }
} elseif ( class_exists( 'QilingShop_Resource' ) ) {
    $qls_resource_enabled = function_exists( 'qilingshop_points_resource_enabled' )
        ? qilingshop_points_resource_enabled( $post_id )
        : true;
    if ( $qls_resource_enabled ) {
        $qls_resource_detector = QilingShop_Resource::instance();
        $is_qls_resource_detail = false;
        if ( method_exists( $qls_resource_detector, 'is_paid_resource' ) && $qls_resource_detector->is_paid_resource( $post_id ) ) {
            $is_qls_resource_detail = true;
        }
        if ( ! $is_qls_resource_detail && method_exists( $qls_resource_detector, 'has_download_urls' ) && $qls_resource_detector->has_download_urls( $post_id ) ) {
            $is_qls_resource_detail = true;
        }
        if ( ! $is_qls_resource_detail && method_exists( $qls_resource_detector, 'has_resource_features' ) && $qls_resource_detector->has_resource_features( $post_id ) ) {
            $is_qls_resource_detail = true;
        }
        if ( $is_qls_resource_detail ) {
            $resource_detail_contexts[] = 'shop';
        }
    }
}
if ( '1' === get_post_meta( $post_id, '_qiling_gallery_mode', true ) ) {
    $resource_detail_contexts[] = 'gallery';
}
$resource_detail_contexts = array_values( array_unique( $resource_detail_contexts ) );
$is_resource_detail_skin = ! empty( $resource_detail_contexts );
if ( $is_resource_detail_skin ) {
    add_action(
        'wp_enqueue_scripts',
        static function() {
            wp_enqueue_style(
                'developer-starter-resource-detail-skins',
                DEVELOPER_STARTER_ASSETS . '/css/resource-detail-skins.css',
                array( 'developer-starter-main' ),
                developer_starter_get_assets_version()
            );
        },
        30
    );
}
get_header();
$options = developer_starter_get_options_cache();
$full_width_mode = get_post_meta( $post_id, '_qiling_full_width_mode', true ) === '1';
$hide_sidebar = ( ! empty( $options['hide_post_sidebar'] ) && $options['hide_post_sidebar'] === '1' ) || $full_width_mode;
$has_sidebar = ! $hide_sidebar && is_active_sidebar( 'sidebar-post' );
$has_sidebar = apply_filters( 'qiling_show_sidebar', $has_sidebar, 'single' );
$toc_enable = ! empty( $options['toc_enable'] );
$toc_position = isset( $options['toc_position'] ) ? $options['toc_position'] : 'sidebar';
$image_zoom_enable = ! empty( $options['post_image_zoom_enable'] );
$post_views_enable = ! empty( $options['post_views_enable'] );
$reading_time_enable = ! empty( $options['reading_time_enable'] );
$post_modified_date_enable = ! empty( $options['post_modified_date_enable'] );
$post_like_enable = ! empty( $options['post_like_enable'] );
$post_favorite_enable = ! empty( $options['post_favorite_enable'] );
$post_poster_enable = ! empty( $options['post_poster_enable'] );
$post_speech_enable = ! empty( $options['post_speech_enable'] );
$hide_post_breadcrumb = ! empty( $options['hide_post_breadcrumb'] ) && '1' === (string) $options['hide_post_breadcrumb'];
$hide_post_publish_date = ! empty( $options['hide_post_publish_date'] ) && '1' === (string) $options['hide_post_publish_date'];
$hide_post_author = ! empty( $options['hide_post_author'] ) && '1' === (string) $options['hide_post_author'];
$hide_post_comment_count = ! empty( $options['hide_post_comment_count'] ) && '1' === (string) $options['hide_post_comment_count'];
$hide_post_tags = ! empty( $options['hide_post_tags'] ) && '1' === (string) $options['hide_post_tags'];
$hide_post_navigation = ! empty( $options['hide_post_navigation'] ) && '1' === (string) $options['hide_post_navigation'];
$hide_post_comments = ! empty( $options['hide_post_comments'] ) && '1' === (string) $options['hide_post_comments'];
$post_poster_button_label = isset( $options['post_poster_button_label'] ) ? trim( wp_strip_all_tags( (string) $options['post_poster_button_label'] ) ) : '';
if ( '' === $post_poster_button_label ) {
    $post_poster_button_label = __( '生成海报', 'developer-starter' );
}
$post_published_timestamp = (int) get_post_time( 'U', true, $post_id );
$post_modified_timestamp = (int) get_post_modified_time( 'U', true, $post_id );
$post_modified_date = '';
if ( $post_modified_date_enable && $post_modified_timestamp > ( $post_published_timestamp + MINUTE_IN_SECONDS ) ) {
    $post_modified_date = get_the_modified_date( '', $post_id );
} else {
    $post_modified_date_enable = false;
}
$interaction_manager_available = class_exists( 'Developer_Starter\\Core\\Post_Interaction_Manager' );
$like_count = $interaction_manager_available ? Developer_Starter\Core\Post_Interaction_Manager::get_count( $post_id, 'like' ) : 0;
$favorite_count = $interaction_manager_available ? Developer_Starter\Core\Post_Interaction_Manager::get_count( $post_id, 'favorite' ) : 0;
$like_active = $post_like_enable && $interaction_manager_available && is_user_logged_in()
    ? Developer_Starter\Core\Post_Interaction_Manager::has_interaction( $post_id, get_current_user_id(), 'like' )
    : false;
$favorite_active = $post_favorite_enable && $interaction_manager_available && is_user_logged_in()
    ? Developer_Starter\Core\Post_Interaction_Manager::has_interaction( $post_id, get_current_user_id(), 'favorite' )
    : false;
$post_poster_cover = '';
$post_poster_excerpt = '';
$post_poster_cache_key = '';
$post_poster_nonce = '';
if ( $post_poster_enable ) {
    $post_content_raw = (string) get_post_field( 'post_content', $post_id );
    if ( has_post_thumbnail( $post_id ) ) {
        $post_poster_cover = (string) get_the_post_thumbnail_url( $post_id, 'large' );
    }
    if ( '' === $post_poster_cover && preg_match( '/<img[^>]+(?:src|data-src|data-original)\s*=\s*["\']([^"\']+)["\']/i', $post_content_raw, $matches ) ) {
        $post_poster_cover = (string) $matches[1];
    }
    $content_plain = (string) wp_strip_all_tags( strip_shortcodes( $post_content_raw ) );
    $content_plain_no_ws = preg_replace( '/\s+/u', '', $content_plain );
    $content_plain = trim( is_string( $content_plain_no_ws ) ? $content_plain_no_ws : $content_plain );
    $excerpt_limit = 56;
    if ( function_exists( 'mb_substr' ) ) {
        $post_poster_excerpt = (string) mb_substr( $content_plain, 0, $excerpt_limit, 'UTF-8' );
        if ( function_exists( 'mb_strlen' ) && mb_strlen( $content_plain, 'UTF-8' ) > $excerpt_limit ) {
            $post_poster_excerpt .= '...';
        }
    } else {
        $post_poster_excerpt = (string) substr( $content_plain, 0, $excerpt_limit );
        if ( strlen( $content_plain ) > $excerpt_limit ) {
            $post_poster_excerpt .= '...';
        }
    }
    $cache_signature = implode(
        '|',
        array(
            'v6',
            (string) get_post_modified_time( 'U', true, $post_id ),
            (string) get_the_title( $post_id ),
            (string) get_permalink( $post_id ),
            (string) $post_poster_cover,
            (string) $post_poster_excerpt,
        )
    );
    $post_poster_cache_key = substr( md5( $cache_signature ), 0, 16 );
    $post_poster_nonce = wp_create_nonce( 'ds_post_poster_' . $post_id );
}
$post_views = Developer_Starter\Core\Post_Enhancer::get_post_views();
$reading_time = Developer_Starter\Core\Post_Enhancer::get_reading_time();
$toc_data = array( 'toc' => '', 'content' => '' );
if ( $toc_enable && have_posts() ) {
    global $post;
    $toc_data = Developer_Starter\Core\Post_Enhancer::generate_toc( $post->post_content );
}
$qls_use_title_layout = false;
$qls_title_box = '';
$qls_cover_url = '';
if ( class_exists( 'QilingShop_Resource' ) && class_exists( 'QilingShop_Public' ) ) {
    $qls_position = get_option( 'qilingshop_download_box_position', 'bottom' );
    if ( 'title' === $qls_position ) {
        $qls_resource = QilingShop_Resource::instance();
        if ( $qls_resource->is_paid_resource( $post_id ) || $qls_resource->has_download_urls( $post_id ) ) {
            $qls_user_id = get_current_user_id();
            $qls_sale_mode = $qls_resource->get_sale_mode( $post_id );
            $qls_price = $qls_resource->get_points_price( $post_id );
            $qls_is_login_free = ( 'free' !== $qls_sale_mode && $qls_price <= 0 && $qls_user_id );
            $qls_has_purchased = class_exists( 'QilingShop_Order' )
                ? QilingShop_Order::instance()->user_has_purchased( $post_id, $qls_user_id )
                : false;
            if ( $qls_user_id && $qls_resource->is_vip_free( $post_id, $qls_user_id ) ) {
                $qls_has_purchased = true;
            }
            if ( $qls_is_login_free ) {
                $qls_has_purchased = true;
            }
            if ( $qls_has_purchased ) {
                if ( $qls_resource->has_download_urls( $post_id ) ) {
                    $qls_title_box = QilingShop_Public::instance()->render_download_box( $post_id, true, 'title' );
                }
            } else {
                $qls_title_box = QilingShop_Public::instance()->render_buy_box( $post_id, 'title' );
            }
            if ( $qls_title_box ) {
                $qls_use_title_layout = true;
                if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
                    $qls_cover_url = developer_starter_get_thumbnail_url( $post_id, 'large' );
                } elseif ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                    $qls_cover_url = developer_starter_get_featured_image_url( $post_id, 'large' );
                } elseif ( has_post_thumbnail( $post_id ) ) {
                    $qls_cover_url = get_the_post_thumbnail_url( $post_id, 'large' );
                }
            }
        }
    }
}
$qls_cover_dims = array( 'width' => 640, 'height' => 640 );
if ( $qls_cover_url && function_exists( 'developer_starter_get_post_image_dimensions' ) ) {
    $qls_cover_dims = developer_starter_get_post_image_dimensions( $post_id, 'large', $qls_cover_dims );
}
$single_context = compact(
    'options',
    'post_id',
    'full_width_mode',
    'has_sidebar',
    'toc_enable',
    'toc_position',
    'toc_data',
    'image_zoom_enable',
    'post_views_enable',
    'reading_time_enable',
    'post_modified_date_enable',
    'post_modified_date',
    'post_modified_timestamp',
    'post_like_enable',
    'post_favorite_enable',
    'post_poster_enable',
    'post_speech_enable',
    'hide_post_breadcrumb',
    'hide_post_publish_date',
    'hide_post_author',
    'hide_post_comment_count',
    'hide_post_tags',
    'hide_post_navigation',
    'hide_post_comments',
    'post_poster_button_label',
    'post_poster_cover',
    'post_poster_excerpt',
    'post_poster_cache_key',
    'post_poster_nonce',
    'post_views',
    'reading_time',
    'like_count',
    'favorite_count',
    'like_active',
    'favorite_active',
    'qls_use_title_layout',
    'qls_title_box',
    'qls_cover_url',
    'qls_cover_dims',
    'resource_detail_contexts',
    'is_resource_detail_skin'
);
get_template_part( 'template-parts/single/header', null, $single_context );
get_template_part( 'template-parts/single/content', null, $single_context );
get_template_part( 'template-parts/single/modals', null, $single_context );
get_template_part( 'template-parts/single/related', null, $single_context );
get_template_part( 'template-parts/single/interaction-script', null, $single_context );
get_footer();
