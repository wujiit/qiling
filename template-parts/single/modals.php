<?php
/**
 * Single post modal markup.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'image_zoom_enable' => false,
        'post_poster_enable'=> false,
    )
);
?>
<?php if ( $args['image_zoom_enable'] ) : ?>
    <dialog id="ds-img-viewer" class="ds-img-viewer">
        <button type="button" id="ds-img-close" class="ds-img-close" aria-label="<?php esc_attr_e( '关闭', 'developer-starter' ); ?>">×</button>
        <button type="button" id="ds-img-prev" class="ds-img-prev" aria-label="<?php esc_attr_e( '上一张', 'developer-starter' ); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <img id="ds-viewer-img" class="ds-img-viewer__img" alt="" />
        <button type="button" id="ds-img-next" class="ds-img-next" aria-label="<?php esc_attr_e( '下一张', 'developer-starter' ); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
        </button>
    </dialog>
<?php endif; ?>

<?php if ( $args['post_poster_enable'] ) : ?>
    <div id="ds-post-poster-modal" class="ds-post-poster-modal" hidden>
        <div class="ds-post-poster-dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( '文章海报', 'developer-starter' ); ?>">
            <button type="button" class="ds-post-poster-close" aria-label="<?php esc_attr_e( '关闭', 'developer-starter' ); ?>">×</button>
            <div class="ds-post-poster-canvas-wrap">
                <canvas id="ds-post-poster-canvas" width="560" height="900"></canvas>
            </div>
            <div class="ds-post-poster-actions">
                <a id="ds-post-poster-download" class="ds-post-poster-download" href="#" download><?php esc_html_e( '下载海报', 'developer-starter' ); ?></a>
                <span id="ds-post-poster-tip" class="ds-post-poster-tip"></span>
            </div>
        </div>
    </div>
<?php endif; ?>
