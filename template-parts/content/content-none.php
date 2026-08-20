<?php
/**
 * Template part for no content found
 *
 * @package Developer_Starter
 * @since 1.0.0
 */
?>

<section class="no-results not-found">
    <div class="no-results-content text-center">
        <h2 class="no-results-title"><?php esc_html_e( '暂无内容', 'developer-starter' ); ?></h2>
        
        <?php if ( is_search() ) : ?>
            <p><?php esc_html_e( '抱歉，没有找到相关内容。请尝试其他关键词。', 'developer-starter' ); ?></p>
            <?php get_search_form(); ?>
        <?php else : ?>
            <p><?php esc_html_e( '此处暂无内容，请稍后再来查看。', 'developer-starter' ); ?></p>
        <?php endif; ?>
    </div>
</section>
