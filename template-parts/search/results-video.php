<?php
/**
 * Poster grid for video-mode search results.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args( is_array( $args ) ? $args : array(), array( 'wp_query' => null ) );
$wp_query = $args['wp_query'];
$video_frontend = class_exists( 'ArtPlayer_Video_Frontend' ) ? ArtPlayer_Video_Frontend::get_instance() : null;
if ( function_exists( 'update_post_thumbnail_cache' ) && $wp_query instanceof WP_Query ) {
    update_post_thumbnail_cache( $wp_query );
}
?>
<div class="search-results-list search-video-grid">
    <?php while ( have_posts() ) : the_post(); ?>
        <?php
        $post_id    = get_the_ID();
        $video_meta = $video_frontend ? $video_frontend->get_video_meta_public( $post_id ) : null;
        $title      = trim( get_the_title() );
        $title      = '' !== $title ? $title : __( '（无标题）', 'developer-starter' );
        $poster     = $video_meta && ! empty( $video_meta->cover_image ) ? (string) $video_meta->cover_image : '';
        if ( '' === $poster && has_post_thumbnail( $post_id ) ) {
            $poster = (string) get_the_post_thumbnail_url( $post_id, 'medium_large' );
        }
        if ( '' === $poster && function_exists( 'developer_starter_get_first_image' ) ) {
            $poster = (string) developer_starter_get_first_image( $post_id );
        }
        $poster_is_default = '' === $poster;
        if ( $poster_is_default ) {
            $poster = get_template_directory_uri() . '/assets/images/default-thumbnail.svg';
        }

        $rating = $video_meta && isset( $video_meta->rating ) && is_numeric( $video_meta->rating ) ? (float) $video_meta->rating : 0;
        $quality = $video_meta && ! empty( $video_meta->video_quality ) ? sanitize_text_field( (string) $video_meta->video_quality ) : '';
        $episode_count = function_exists( 'artplayer_get_post_video_urls' ) ? count( (array) artplayer_get_post_video_urls( $post_id ) ) : 0;
        $vip_enabled = '1' === (string) get_post_meta( $post_id, '_artplayer_vip_gate_enabled', true );
        $categories = get_the_category( $post_id );
        $category_names = array();
        foreach ( array_slice( (array) $categories, 0, 2 ) as $category ) {
            if ( $category instanceof WP_Term ) {
                $category_names[] = $category->name;
            }
        }
        $card_data = array(
            'title'          => $title,
            'poster'         => $poster,
            'rating'         => $rating,
            'quality'        => $quality,
            'episode_count'  => $episode_count,
            'vip_enabled'    => $vip_enabled,
            'categories'     => $category_names,
            'year'           => get_the_date( 'Y', $post_id ),
            'permalink'      => get_permalink( $post_id ),
        );
        if ( function_exists( 'developer_starter_get_search_result_card_data' ) ) {
            $card_data = wp_parse_args( developer_starter_get_search_result_card_data( $card_data, $post_id, 'video' ), $card_data );
        }
        $card_data['title']         = sanitize_text_field( (string) $card_data['title'] );
        $card_data['poster']        = esc_url_raw( (string) $card_data['poster'] );
        $card_data['permalink']     = esc_url_raw( (string) $card_data['permalink'] );
        $card_data['rating']        = is_numeric( $card_data['rating'] ) ? max( 0, (float) $card_data['rating'] ) : 0;
        $card_data['quality']       = sanitize_text_field( (string) $card_data['quality'] );
        $card_data['episode_count'] = absint( $card_data['episode_count'] );
        $card_data['vip_enabled']   = ! empty( $card_data['vip_enabled'] );
        $card_data['categories']    = array_values( array_filter( array_map( 'sanitize_text_field', (array) $card_data['categories'] ) ) );
        $card_data['year']          = sanitize_text_field( (string) $card_data['year'] );
        ?>
        <article id="post-<?php echo esc_attr( $post_id ); ?>" <?php post_class( 'search-video-card' ); ?>>
            <a class="search-video-card__poster<?php echo $poster_is_default ? ' is-placeholder' : ''; ?>" href="<?php echo esc_url( $card_data['permalink'] ); ?>" aria-label="<?php echo esc_attr( $card_data['title'] ); ?>">
                <img src="<?php echo esc_url( $card_data['poster'] ); ?>" alt="<?php echo esc_attr( $card_data['title'] ); ?>" width="400" height="600" loading="lazy" decoding="async" />
                <span class="search-video-card__badges search-video-card__badges--left">
                    <span class="search-video-badge search-video-badge--mode"><?php esc_html_e( '影视', 'developer-starter' ); ?></span>
                    <?php if ( ! empty( $card_data['vip_enabled'] ) ) : ?><span class="search-video-badge search-video-badge--vip">VIP</span><?php endif; ?>
                </span>
                <?php if ( ! empty( $card_data['quality'] ) || ! empty( $card_data['rating'] ) ) : ?>
                    <span class="search-video-card__badges search-video-card__badges--right">
                        <?php if ( ! empty( $card_data['quality'] ) ) : ?><span class="search-video-badge search-video-badge--quality"><?php echo esc_html( $card_data['quality'] ); ?></span><?php endif; ?>
                        <?php if ( ! empty( $card_data['rating'] ) ) : ?><span class="search-video-badge search-video-badge--rating"><?php echo esc_html( number_format_i18n( (float) $card_data['rating'], 1 ) ); ?></span><?php endif; ?>
                    </span>
                <?php endif; ?>
                <span class="search-video-card__play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
                <?php if ( ! empty( $card_data['episode_count'] ) ) : ?>
                    <span class="search-video-card__episode"><?php echo esc_html( sprintf( _n( '全 %d 集', '全 %d 集', (int) $card_data['episode_count'], 'developer-starter' ), (int) $card_data['episode_count'] ) ); ?></span>
                <?php endif; ?>
            </a>
            <div class="search-video-card__body">
                <h2 class="search-video-card__title"><a href="<?php echo esc_url( $card_data['permalink'] ); ?>"><?php echo esc_html( $card_data['title'] ); ?></a></h2>
                <div class="search-video-card__meta">
                    <?php if ( ! empty( $card_data['categories'] ) ) : ?><span><?php echo esc_html( implode( ' / ', (array) $card_data['categories'] ) ); ?></span><?php endif; ?>
                    <?php if ( ! empty( $card_data['year'] ) ) : ?><time datetime="<?php echo esc_attr( $card_data['year'] ); ?>"><?php echo esc_html( $card_data['year'] ); ?></time><?php endif; ?>
                </div>
            </div>
        </article>
    <?php endwhile; ?>
</div>
