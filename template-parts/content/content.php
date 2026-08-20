<?php
/**
 * Template part for displaying posts
 *
 * @package Developer_Starter
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
    <?php
    $article_thumb_height = absint( developer_starter_get_option( 'article_thumb_height', 0 ) );
    $hide_article_thumb = developer_starter_get_option( 'hide_article_thumb', '' );
    $hide_article_excerpt = developer_starter_get_option( 'hide_article_excerpt', '' );
    $hide_article_date = developer_starter_get_option( 'hide_article_date', '' );
    $hide_article_category = developer_starter_get_option( 'hide_article_category', '' );
    $hide_article_author = developer_starter_get_option( 'hide_article_author', '' );
    $excerpt_length = absint( developer_starter_get_option( 'article_excerpt_length', 0 ) );
    if ( $excerpt_length <= 0 ) {
        $excerpt_length = 20;
    }

    $thumb_url = '';
    if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
        $thumb_url = developer_starter_get_featured_image_url( get_the_ID(), 'developer-starter-card' );
    } elseif ( has_post_thumbnail() ) {
        $thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'developer-starter-card' );
    }
    $thumb_dims = array( 'width' => 600, 'height' => 400 );
    if ( function_exists( 'developer_starter_get_post_image_dimensions' ) ) {
        $thumb_dims = developer_starter_get_post_image_dimensions(
            get_the_ID(),
            'developer-starter-card',
            array( 'width' => 600, 'height' => 400 )
        );
    }

    $thumb_height_attr = ( $article_thumb_height > 0 ) ? $article_thumb_height : (int) $thumb_dims['height'];
    $thumb_style = ( $article_thumb_height > 0 ) ? ' style="height: ' . esc_attr( $article_thumb_height ) . 'px; overflow: hidden;"' : '';
    $thumb_img_style = ( $article_thumb_height > 0 ) ? ' style="height: 100%; width: 100%; object-fit: cover;"' : '';
    ?>
    <?php if ( ! $hide_article_thumb && $thumb_url ) : ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-thumb"<?php echo $thumb_style; ?>>
            <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" width="<?php echo esc_attr( (int) $thumb_dims['width'] ); ?>" height="<?php echo esc_attr( (int) $thumb_height_attr ); ?>" loading="lazy" decoding="async"<?php echo $thumb_img_style; ?> />
        </a>
    <?php endif; ?>
    
    <div class="post-content">
        <div class="post-meta">
            <?php if ( ! $hide_article_date ) : ?>
                <span class="post-date"><?php echo esc_html( get_the_date() ); ?></span>
            <?php endif; ?>
            <?php if ( ! $hide_article_author ) : ?>
                <span class="post-author"><?php echo esc_html( get_the_author() ); ?></span>
            <?php endif; ?>
            <?php if ( ! $hide_article_category ) : ?>
                <?php developer_starter_entry_categories(); ?>
            <?php endif; ?>
        </div>
        
        <h2 class="post-title">
            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
        </h2>
        
        <?php if ( ! $hide_article_excerpt ) : ?>
            <p class="post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), $excerpt_length ); ?></p>
        <?php endif; ?>
        
        <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-read-more">
            <?php esc_html_e( '阅读更多 →', 'developer-starter' ); ?>
        </a>
    </div>
</article>
