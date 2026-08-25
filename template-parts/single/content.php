<?php
/**
 * Single post body, gallery mode, tags, navigation, comments and sidebar.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'post_id'                 => get_the_ID(),
        'has_sidebar'             => false,
        'full_width_mode'         => false,
        'toc_enable'              => false,
        'toc_position'            => 'sidebar',
        'toc_data'                => array( 'toc' => '', 'content' => '' ),
        'post_poster_enable'      => false,
        'post_speech_enable'      => false,
        'post_poster_cover'       => '',
        'post_poster_excerpt'     => '',
        'post_poster_cache_key'   => '',
        'post_poster_nonce'       => '',
        'post_poster_button_label'=> __( '生成海报', 'developer-starter' ),
        'hide_post_tags'          => false,
        'hide_post_navigation'    => false,
        'hide_post_comments'      => false,
        'resource_detail_contexts' => array(),
        'is_resource_detail_skin'  => false,
    )
);

$toc_data = is_array( $args['toc_data'] ) ? $args['toc_data'] : array( 'toc' => '', 'content' => '' );
$toc_html = isset( $toc_data['toc'] ) ? (string) $toc_data['toc'] : '';
$toc_content = isset( $toc_data['content'] ) ? (string) $toc_data['content'] : '';
$post_poster_button_label = trim( wp_strip_all_tags( (string) $args['post_poster_button_label'] ) );
if ( '' === $post_poster_button_label ) {
    $post_poster_button_label = __( '生成海报', 'developer-starter' );
}
$resource_detail_contexts = is_array( $args['resource_detail_contexts'] ) ? $args['resource_detail_contexts'] : array();
$article_classes = array( 'single-post', 'section-padding' );
$layout_classes = array(
    'post-layout',
    $args['has_sidebar'] ? 'has-sidebar' : 'no-sidebar',
);
if ( ! empty( $args['is_resource_detail_skin'] ) ) {
    $article_classes[] = 'qiling-resource-detail';
    $layout_classes[] = 'qiling-resource-detail-layout';
    foreach ( $resource_detail_contexts as $resource_detail_context ) {
        $resource_detail_context = sanitize_html_class( (string) $resource_detail_context );
        if ( '' !== $resource_detail_context ) {
            $article_classes[] = 'qiling-resource-detail--' . $resource_detail_context;
            $layout_classes[] = 'qiling-resource-detail-layout--' . $resource_detail_context;
        }
    }
}
if ( $args['full_width_mode'] ) {
    $layout_classes[] = 'is-full-width-mode';
}
if ( ! $args['full_width_mode'] && $args['toc_enable'] && 'sidebar' === $args['toc_position'] && $toc_html ) {
    $layout_classes[] = 'has-toc-sidebar';
}
?>
<article class="<?php echo esc_attr( implode( ' ', $article_classes ) ); ?>">
    <div class="container">
        <div class="<?php echo esc_attr( implode( ' ', $layout_classes ) ); ?>">
            <?php if ( $args['toc_enable'] && 'before_content' === $args['toc_position'] && $toc_html ) : ?>
                <div class="toc-before-content">
                    <?php echo wp_kses_post( $toc_html ); ?>
                </div>
            <?php endif; ?>

            <div class="post-main-content">
                <?php if ( ! empty( $args['post_speech_enable'] ) ) : ?>
                    <div class="qiling-post-speech" data-speech-scope="article" aria-label="<?php esc_attr_e( '文章语音朗读', 'developer-starter' ); ?>">
                        <div class="qiling-post-speech__actions">
                            <button type="button" class="qiling-post-speech__button" data-speech-action="play">
                                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><polygon points="6 4 20 12 6 20 6 4" fill="currentColor"></polygon></svg>
                                <span><?php esc_html_e( '朗读文章', 'developer-starter' ); ?></span>
                            </button>
                            <button type="button" class="qiling-post-speech__button" data-speech-action="pause">
                                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><rect x="6" y="4" width="4" height="16" fill="currentColor"></rect><rect x="14" y="4" width="4" height="16" fill="currentColor"></rect></svg>
                                <span><?php esc_html_e( '暂停', 'developer-starter' ); ?></span>
                            </button>
                            <button type="button" class="qiling-post-speech__button" data-speech-action="resume">
                                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M8 5v14l11-7z" fill="currentColor"></path><rect x="4" y="5" width="2" height="14" fill="currentColor"></rect></svg>
                                <span><?php esc_html_e( '继续', 'developer-starter' ); ?></span>
                            </button>
                            <button type="button" class="qiling-post-speech__button" data-speech-action="stop">
                                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><rect x="6" y="6" width="12" height="12" rx="1" fill="currentColor"></rect></svg>
                                <span><?php esc_html_e( '停止', 'developer-starter' ); ?></span>
                            </button>
                        </div>
                        <div class="qiling-post-speech__meta">
                            <span class="qiling-post-speech__status" aria-live="polite"><?php esc_html_e( '准备朗读', 'developer-starter' ); ?></span>
                            <span class="qiling-post-speech__progress" data-speech-progress hidden>0/0</span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php
                    while ( have_posts() ) :
                        the_post();

                        $gallery_mode = get_post_meta( get_the_ID(), '_qiling_gallery_mode', true );
                        if ( '1' === $gallery_mode ) {
                            $content = get_the_content();

                            if ( preg_match_all( '/<figure\b[^>]*>.*?<img[^>]+>.*?<\/figure>|<img[^>]+>/is', $content, $matches ) ) {
                                $images = $matches[0];
                                $total_pages = count( $images );
                                $gallery_page = get_query_var( 'gallery_page' );
                                $paged = $gallery_page ? intval( $gallery_page ) : 1;
                                $paged = max( 1, min( $paged, $total_pages ) );
                                $current_img = $images[ $paged - 1 ];
                                $base_url = get_permalink();
                                $is_html = substr( $base_url, -5 ) === '.html';
                                $url_prefix = $is_html ? substr( $base_url, 0, -5 ) : $base_url;
                                $get_page_link = function( $num ) use ( $is_html, $url_prefix, $base_url ) {
                                    if ( 1 === (int) $num ) {
                                        return $base_url;
                                    }

                                    return $is_html ? $url_prefix . '_' . (int) $num . '.html' : add_query_arg( 'gallery_page', (int) $num, $base_url );
                                };
                                $prev_link = $paged > 1 ? $get_page_link( $paged - 1 ) : '#';
                                $next_link = $paged < $total_pages ? $get_page_link( $paged + 1 ) : '#';
                                ?>
                                <div class="gallery-mode-viewer">
                                    <?php if ( $paged > 1 ) : ?>
                                        <a href="<?php echo esc_url( $prev_link ); ?>" class="gallery-nav-btn gallery-nav-prev" rel="prev" aria-label="<?php esc_attr_e( '上一张', 'developer-starter' ); ?>"><span aria-hidden="true">❮</span></a>
                                    <?php endif; ?>

                                    <?php echo wp_kses_post( $current_img ); ?>

                                    <?php if ( $paged < $total_pages ) : ?>
                                        <a href="<?php echo esc_url( $next_link ); ?>" class="gallery-nav-btn gallery-nav-next" rel="next" aria-label="<?php esc_attr_e( '下一张', 'developer-starter' ); ?>"><span aria-hidden="true">❯</span></a>
                                    <?php endif; ?>
                                </div>

                                <nav class="gallery-pagination" aria-label="<?php esc_attr_e( '相册分页', 'developer-starter' ); ?>">
                                    <?php
                                    $range = 4;
                                    if ( $paged > 1 ) {
                                        echo '<a href="' . esc_url( $prev_link ) . '" class="gallery-page-item" rel="prev" aria-label="' . esc_attr__( '上一张', 'developer-starter' ) . '">&lt;</a>';
                                    }

                                    for ( $i = 1; $i <= $total_pages; $i++ ) {
                                        if ( 1 === $i || $i === $total_pages || ( $i >= $paged - $range && $i <= $paged + $range ) ) {
                                            $class = ( $i === $paged ) ? ' current' : '';
                                            $aria_current = ( $i === $paged ) ? ' aria-current="page"' : '';
                                            echo '<a href="' . esc_url( $get_page_link( $i ) ) . '" class="gallery-page-item' . esc_attr( $class ) . '"' . $aria_current . '>' . esc_html( (string) $i ) . '</a>';
                                        } elseif ( $i === $paged - $range - 1 || $i === $paged + $range + 1 ) {
                                            echo '<span class="gallery-page-item">...</span>';
                                        }
                                    }

                                    if ( $paged < $total_pages ) {
                                        echo '<a href="' . esc_url( $next_link ) . '" class="gallery-page-item" rel="next" aria-label="' . esc_attr__( '下一张', 'developer-starter' ) . '">&gt;</a>';
                                    }
                                    ?>
                                </nav>

                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var img = document.querySelector('.gallery-mode-viewer img');
                                    if (!img) {
                                        return;
                                    }
                                    var wrapper = img.closest('.gallery-mode-viewer');
                                    if (!wrapper) {
                                        return;
                                    }
                                    var linkPrev = '<?php echo esc_js( $paged > 1 ? $prev_link : '' ); ?>';
                                    var linkNext = '<?php echo esc_js( $paged < $total_pages ? $next_link : '' ); ?>';
                                    if (linkPrev) {
                                        var prevArea = document.createElement('a');
                                        prevArea.href = linkPrev;
                                        prevArea.className = 'gallery-click-area gallery-click-prev';
                                        prevArea.title = '<?php echo esc_js( __( '上一张', 'developer-starter' ) ); ?>';
                                        wrapper.appendChild(prevArea);
                                    }
                                    if (linkNext) {
                                        var nextArea = document.createElement('a');
                                        nextArea.href = linkNext;
                                        nextArea.className = 'gallery-click-area gallery-click-next';
                                        nextArea.title = '<?php echo esc_js( __( '下一张', 'developer-starter' ) ); ?>';
                                        wrapper.appendChild(nextArea);
                                    }
                                });
                                </script>
                                <?php
                                $content_text = preg_replace( '/<figure\b[^>]*>.*?<img[^>]+>.*?<\/figure>|<img[^>]+>/is', '', $content );
                                $content_text = trim( strip_tags( $content_text, '<p><br><div><span><strong><em><a><ul><li><ol><h1><h2><h3><h4><h5><h6>' ) );
                                if ( ! empty( $content_text ) ) {
                                    echo '<div class="gallery-text-content gallery-text-content--after-images">';
                                    echo apply_filters( 'the_content', $content_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    echo '</div>';
                                }
                            } elseif ( $args['toc_enable'] && $toc_content ) {
                                echo apply_filters( 'the_content', $toc_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            } else {
                                the_content();
                            }
                        } elseif ( $args['toc_enable'] && $toc_content ) {
                            echo apply_filters( 'the_content', $toc_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } else {
                            the_content();
                        }

                        $page_links = wp_link_pages(
                            array(
                                'before'      => '<nav class="single-post-page-links" aria-label="' . esc_attr__( '文章分页', 'developer-starter' ) . '"><span class="single-post-page-links__label">' . esc_html__( '分页', 'developer-starter' ) . '</span>',
                                'after'       => '</nav>',
                                'link_before' => '<span class="single-post-page-links__item">',
                                'link_after'  => '</span>',
                                'pagelink'    => '%',
                                'echo'        => 0,
                            )
                        );
                        if ( $page_links ) {
                            echo wp_kses_post( $page_links );
                        }
                    endwhile;
                    ?>
                </div>

                <?php if ( $args['post_poster_enable'] ) : ?>
                    <div class="ds-post-poster-wrap">
                        <button type="button" class="ds-post-poster-trigger" data-poster-title="<?php echo esc_attr( wp_strip_all_tags( get_the_title( $args['post_id'] ) ) ); ?>" data-poster-url="<?php echo esc_url( get_permalink( $args['post_id'] ) ); ?>" data-poster-cover="<?php echo esc_url( $args['post_poster_cover'] ); ?>" data-poster-excerpt="<?php echo esc_attr( $args['post_poster_excerpt'] ); ?>" data-post-id="<?php echo esc_attr( (int) $args['post_id'] ); ?>" data-cache-key="<?php echo esc_attr( $args['post_poster_cache_key'] ); ?>" data-cache-nonce="<?php echo esc_attr( $args['post_poster_nonce'] ); ?>">
                            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2"></rect><line x1="3" y1="14" x2="21" y2="14" stroke="currentColor" stroke-width="2"></line><circle cx="8" cy="9" r="1.6" fill="currentColor"></circle><path d="M11 11l2.2 2.6L16 10.6 20 14" fill="none" stroke="currentColor" stroke-width="2"></path></svg>
                            <span><?php echo esc_html( $post_poster_button_label ); ?></span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php
                $tags = get_the_tags();
                if ( empty( $args['hide_post_tags'] ) && $tags ) :
                    ?>
                    <div class="post-tags">
                        <strong><?php esc_html_e( '标签：', 'developer-starter' ); ?></strong>
                        <?php foreach ( $tags as $tag ) : ?>
                            <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" rel="tag">
                                <?php echo esc_html( $tag->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php echo wp_kses_post( Developer_Starter\Core\Post_Enhancer::render_copyright() ); ?>
                <?php echo wp_kses_post( Developer_Starter\Core\Post_Enhancer::render_author_box() ); ?>

                <?php if ( empty( $args['hide_post_navigation'] ) ) : ?>
                    <nav class="post-navigation" aria-label="<?php esc_attr_e( '文章上一篇下一篇', 'developer-starter' ); ?>">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        ?>
                        <div class="post-navigation__item post-navigation__item--prev">
                            <?php if ( $prev_post ) : ?>
                                <span class="post-navigation__label">← <?php esc_html_e( '上一篇', 'developer-starter' ); ?></span>
                                <a class="post-navigation__link" href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev"><?php echo esc_html( $prev_post->post_title ); ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="post-navigation__item post-navigation__item--next">
                            <?php if ( $next_post ) : ?>
                                <span class="post-navigation__label"><?php esc_html_e( '下一篇', 'developer-starter' ); ?> →</span>
                                <a class="post-navigation__link" href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="next"><?php echo esc_html( $next_post->post_title ); ?></a>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php endif; ?>

                <?php if ( empty( $args['hide_post_comments'] ) && ( function_exists( 'developer_starter_comments_feature_enabled' ) ? developer_starter_comments_feature_enabled() : true ) && comments_open() ) : ?>
                    <div class="single-post-comments">
                        <?php comments_template(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! $args['full_width_mode'] && ( $args['has_sidebar'] || ( $args['toc_enable'] && 'sidebar' === $args['toc_position'] && $toc_html ) ) ) : ?>
                <div class="post-sidebar toc-sidebar">
                    <?php
                    if ( $args['toc_enable'] && 'sidebar' === $args['toc_position'] && $toc_html ) {
                        echo wp_kses_post( $toc_html );
                    }
                    if ( $args['has_sidebar'] ) {
                        get_sidebar();
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</article>
