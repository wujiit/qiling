<?php
/**
 * Template Name: 更新日志
 * Template Post Type: page
 * 
 * 软件/产品更新日志页面模板
 * 自动识别页面内容中的标题、列表、下载链接，以时间线形式展示
 * 
 * @package Developer_Starter
 */

get_header();

// 页面头部设置
$page_header = new Developer_Starter\Core\Page_Header();
$page_header->render();
?>

<div class="changelog-page">
    <div class="container">
        <div class="changelog-wrapper">
            <?php
            while ( have_posts() ) :
                the_post();
                
                // 使用原始 post_content 解析，避免经典编辑器可视化残留在整体过滤阶段被重新拼回条目
                $content = (string) get_post_field( 'post_content', get_the_ID(), 'raw' );
                
                // 解析内容为更新日志条目
                $entries = developer_starter_parse_changelog( $content );
                
                if ( ! empty( $entries ) ) :
            ?>
                <div class="changelog-timeline">
                    <?php foreach ( $entries as $index => $entry ) : ?>
                        <div class="changelog-entry <?php echo $index === 0 ? 'is-latest' : ''; ?>" data-index="<?php echo $index; ?>">
                            <div class="changelog-marker">
                                <span class="marker-dot"></span>
                                <span class="marker-line"></span>
                            </div>
                            <div class="changelog-card">
                                <button type="button" class="changelog-header" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                    <div class="changelog-title-group">
                                        <?php if ( $entry['is_latest'] || $index === 0 ) : ?>
                                            <span class="changelog-badge"><?php esc_html_e( '最新', 'developer-starter' ); ?></span>
                                        <?php endif; ?>
                                        <h3 class="changelog-title"><?php echo esc_html( $entry['title'] ); ?></h3>
                                    </div>
                                    <div class="changelog-meta">
                                        <?php if ( ! empty( $entry['date'] ) ) : ?>
                                            <span class="changelog-date">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                <?php echo esc_html( $entry['date'] ); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="changelog-toggle">
                                            <svg class="icon-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                        </span>
                                    </div>
                                </button>
                                <div class="changelog-content <?php echo $index === 0 ? 'is-open' : ''; ?>">
                                    <div class="changelog-body">
                                        <?php echo $entry['content']; ?>
                                    </div>
                                    <?php if ( ! empty( $entry['downloads'] ) ) : ?>
                                        <div class="changelog-downloads">
                                            <?php foreach ( $entry['downloads'] as $download ) : ?>
                                                <a href="<?php echo esc_url( $download['url'] ); ?>" class="changelog-download-btn" target="_blank" rel="noopener">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    <?php echo esc_html( $download['text'] ); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="changelog-empty">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <p><?php esc_html_e( '暂无更新日志', 'developer-starter' ); ?></p>
                </div>
            <?php
                endif;
            endwhile;
            ?>
        </div>
    </div>
</div>

<style>
/* ===== 更新日志页面样式 ===== */
.changelog-page {
    padding: var(--qiling-space-60) 0;
    background: linear-gradient(180deg, var(--color-neutral-50) 0%, var(--color-neutral-100) 100%);
    min-height: 60vh;
}

.changelog-wrapper {
    max-width: var(--qiling-measure-800);
    margin: 0 auto;
}

/* 时间线 */
.changelog-timeline {
    position: relative;
}

/* 条目 */
.changelog-entry {
    display: flex;
    gap: var(--qiling-space-24);
    margin-bottom: var(--qiling-space-24);
    position: relative;
}

.changelog-entry:last-child {
    margin-bottom: 0;
}

.changelog-entry:last-child .marker-line {
    display: none;
}

/* 时间线标记 */
.changelog-marker {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: 24px;
}

.marker-dot {
    width: 16px;
    height: 16px;
    background: linear-gradient(135deg, var(--color-info), var(--color-success));
    border-radius: 50%;
    box-shadow: 0 0 0 4px rgba(var(--color-info-rgb), 0.15);
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}

.changelog-entry.is-latest .marker-dot {
    width: 20px;
    height: 20px;
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 4px rgba(var(--color-info-rgb), 0.15); }
    50% { box-shadow: 0 0 0 8px rgba(var(--color-info-rgb), 0.1); }
}

.marker-line {
    width: 2px;
    flex: 1;
    background: linear-gradient(180deg, var(--color-info) 0%, var(--color-neutral-200) 100%);
    margin-top: var(--qiling-space-8);
    border-radius: 1px;
}

/* 卡片 */
.changelog-card {
    flex: 1;
    background: var(--color-neutral-0);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(var(--qiling-rgb-0-0-0), 0.06);
    border: 1px solid rgba(var(--color-info-rgb), 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.changelog-card:hover {
    box-shadow: 0 8px 30px rgba(var(--color-info-rgb), 0.12);
    border-color: rgba(var(--color-info-rgb), 0.2);
}

.changelog-entry.is-latest .changelog-card {
    border-color: rgba(var(--color-info-rgb), 0.3);
    box-shadow: 0 8px 30px rgba(var(--color-info-rgb), 0.15);
}

/* 头部 */
.changelog-header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--qiling-space-20) var(--qiling-space-24);
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background 0.2s;
}

.changelog-header:hover {
    background: rgba(var(--color-info-rgb), 0.03);
}

.changelog-title-group {
    display: flex;
    align-items: center;
    gap: var(--qiling-space-12);
    flex-wrap: wrap;
}

.changelog-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--qiling-space-4) var(--qiling-space-10);
    background: linear-gradient(135deg, var(--color-info) 0%, var(--color-success) 100%);
    color: var(--color-neutral-0);
    font-size: calc(var(--qiling-font-size-base) * 0.6875);
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: calc(1em * 0.03125);
}

.changelog-title {
    margin: 0;
    font-size: calc(var(--qiling-font-size-base) * 1.1);
    font-weight: 600;
    color: var(--color-neutral-800);
    line-height: 1.4;
}

.changelog-meta {
    display: flex;
    align-items: center;
    gap: var(--qiling-space-16);
    flex-shrink: 0;
}

.changelog-date {
    display: flex;
    align-items: center;
    gap: var(--qiling-space-6);
    color: var(--color-neutral-500);
    font-size: calc(var(--qiling-font-size-base) * 0.8125);
}

.changelog-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--color-neutral-100);
    transition: all 0.3s;
}

.changelog-header:hover .changelog-toggle {
    background: var(--color-neutral-200);
}

.icon-chevron {
    transition: transform 0.3s ease;
}

.changelog-header[aria-expanded="true"] .icon-chevron {
    transform: rotate(180deg);
}

/* 内容区 */
.changelog-content {
    display: none;
    border-top: 1px solid var(--color-neutral-100);
}

.changelog-content.is-open {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.changelog-body {
    padding: var(--qiling-space-24);
    color: var(--color-neutral-600);
    font-size: calc(var(--qiling-font-size-base) * 0.9375);
    line-height: 1.7;
}

.changelog-body ul,
.changelog-body ol {
    margin: 0;
    padding-left: var(--qiling-space-24);
}

.changelog-body li {
    margin-bottom: var(--qiling-space-10);
    position: relative;
}

.changelog-body li:last-child {
    margin-bottom: 0;
}

.changelog-body li::marker {
    color: var(--color-info);
}

.changelog-body p {
    margin: 0 0 var(--qiling-space-16);
}

.changelog-body p:last-child {
    margin-bottom: 0;
}

.changelog-body a {
    color: var(--color-info);
    text-decoration: none;
    font-weight: 500;
}

.changelog-body a:hover {
    text-decoration: underline;
}

.changelog-body strong {
    color: var(--color-neutral-800);
    font-weight: 600;
}

.changelog-body code {
    background: var(--color-neutral-100);
    padding: var(--qiling-space-2) var(--qiling-space-6);
    border-radius: 4px;
    font-size: calc(var(--qiling-font-size-base) * 0.8125);
    color: var(--color-error);
}

/* 下载按钮区 */
.changelog-downloads {
    padding: var(--qiling-space-16) var(--qiling-space-24) var(--qiling-space-24);
    display: flex;
    flex-wrap: wrap;
    gap: var(--qiling-space-12);
}

.changelog-download-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--qiling-space-8);
    padding: var(--qiling-space-10) var(--qiling-space-20);
    background: linear-gradient(135deg, var(--color-info) 0%, var(--color-success) 100%);
    color: var(--color-neutral-0);
    font-size: calc(var(--qiling-font-size-base) * 0.875);
    font-weight: 500;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(var(--color-info-rgb), 0.3);
}

.changelog-download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(var(--color-info-rgb), 0.4);
    color: var(--color-neutral-0);
}

/* 空状态 */
.changelog-empty {
    text-align: center;
    padding: var(--qiling-space-80) var(--qiling-space-20);
    color: var(--color-neutral-400);
}

.changelog-empty svg {
    margin-bottom: var(--qiling-space-16);
    opacity: 0.5;
}

.changelog-empty p {
    margin: 0;
    font-size: var(--qiling-font-size-base);
}

/* ===== 深色模式 ===== */
html.dark-mode .changelog-page,
body.dark-mode .changelog-page {
    background: linear-gradient(180deg, var(--color-neutral-900) 0%, var(--color-neutral-800) 100%);
}

html.dark-mode .changelog-card,
body.dark-mode .changelog-card {
    background: var(--color-neutral-800);
    border-color: rgba(var(--color-info-rgb), 0.2);
}

html.dark-mode .changelog-title,
body.dark-mode .changelog-title {
    color: var(--color-neutral-100);
}

html.dark-mode .changelog-date,
body.dark-mode .changelog-date {
    color: var(--color-neutral-400);
}

html.dark-mode .changelog-toggle,
body.dark-mode .changelog-toggle {
    background: var(--color-neutral-700);
}

html.dark-mode .changelog-header:hover .changelog-toggle,
body.dark-mode .changelog-header:hover .changelog-toggle {
    background: var(--color-neutral-600);
}

html.dark-mode .changelog-content,
body.dark-mode .changelog-content {
    border-color: var(--color-neutral-700);
}

html.dark-mode .changelog-body,
body.dark-mode .changelog-body {
    color: var(--color-neutral-300);
}

html.dark-mode .changelog-body strong,
body.dark-mode .changelog-body strong {
    color: var(--color-neutral-100);
}

html.dark-mode .changelog-body code,
body.dark-mode .changelog-body code {
    background: var(--color-neutral-700);
}

html.dark-mode .marker-line,
body.dark-mode .marker-line {
    background: linear-gradient(180deg, var(--color-info) 0%, var(--color-neutral-700) 100%);
}

/* ===== 响应式 ===== */
@media (max-width: 768px) {
    .changelog-page {
        padding: var(--qiling-measure-40) 0;
    }

    .changelog-entry {
        gap: var(--qiling-space-16);
    }

    .changelog-marker {
        width: 16px;
    }

    .marker-dot {
        width: 12px;
        height: 12px;
    }

    .changelog-entry.is-latest .marker-dot {
        width: 14px;
        height: 14px;
    }

    .changelog-header {
        padding: var(--qiling-space-16) var(--qiling-space-20);
        flex-direction: column;
        align-items: flex-start;
        gap: var(--qiling-space-12);
    }

    .changelog-meta {
        width: 100%;
        justify-content: space-between;
    }

    .changelog-title {
        font-size: var(--qiling-font-size-base);
    }

    .changelog-body {
        padding: var(--qiling-space-20);
    }

    .changelog-downloads {
        padding: var(--qiling-space-12) var(--qiling-space-20) var(--qiling-space-20);
    }

    .changelog-download-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var headers = document.querySelectorAll('.changelog-header');
    
    headers.forEach(function(header) {
        header.addEventListener('click', function() {
            var content = this.parentElement.querySelector('.changelog-content');
            var isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // 切换状态
            this.setAttribute('aria-expanded', !isExpanded);
            content.classList.toggle('is-open');
        });
    });
});
</script>

<?php
get_footer();

