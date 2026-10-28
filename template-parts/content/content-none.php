<?php
/**
 * 无内容状态提示模板。
 *
 * @package Developer_Starter
 * @since 1.0.0
 */
?>

<section class="no-results not-found qiling-empty-state">
    <div class="qiling-empty-state__visual" aria-hidden="true">
        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="9" y1="15" x2="15" y2="15"></line>
        </svg>
    </div>
    
    <div class="no-results-content text-center">
        <?php if ( is_search() ) : ?>
            <h2 class="qiling-empty-state__title"><?php esc_html_e( '未找到相关结果', 'qiling' ); ?></h2>
            <p class="qiling-empty-state__desc"><?php esc_html_e( '抱歉，没有找到匹配的内容。请尝试更换关键词或返回首页。', 'qiling' ); ?></p>
            <div class="qiling-empty-state__actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( '返回首页', 'qiling' ); ?></a>
            </div>
        <?php else : ?>
            <h2 class="qiling-empty-state__title"><?php esc_html_e( '暂无内容', 'qiling' ); ?></h2>
            <p class="qiling-empty-state__desc"><?php esc_html_e( '此处暂无发布内容，请稍后再来查看。', 'qiling' ); ?></p>
            <div class="qiling-empty-state__actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-outline"><?php esc_html_e( '返回首页', 'qiling' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
