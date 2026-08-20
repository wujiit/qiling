<?php
/**
 * Template Name: 常见问题
 * Template Post Type: page
 *
 * FAQ 文档中心页面模板
 *
 * @package Developer_Starter
 */

get_header();

use Developer_Starter\Core\FAQ_Manager;

$categories = FAQ_Manager::get_categories();
$all_faqs = FAQ_Manager::get_faqs();
?>

<main class="faq-page">
    <?php \Developer_Starter\Core\Page_Header::render( 'default' ); ?>

    <!-- FAQ 内容区 -->
    <section class="faq-content">
        <div class="container">
            <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
            <!-- 分类筛选 -->
            <div class="faq-categories">
                <button type="button" class="faq-cat-btn active" data-category="all">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <?php esc_html_e( '全部', 'developer-starter' ); ?>
                </button>
                <?php foreach ( $categories as $cat ) : ?>
                    <button type="button" class="faq-cat-btn" data-category="<?php echo esc_attr( $cat->term_id ); ?>">
                        <?php echo esc_html( $cat->name ); ?>
                        <span class="cat-count"><?php echo esc_html( $cat->count ); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- FAQ 列表 -->
            <div class="faq-list">
                <?php if ( ! empty( $all_faqs ) ) : ?>
                    <?php foreach ( $all_faqs as $faq ) : 
                        $doc_name   = get_post_meta( $faq->ID, '_faq_doc_name', true );
                        $doc_format = get_post_meta( $faq->ID, '_faq_doc_format', true );
                        $doc_size   = get_post_meta( $faq->ID, '_faq_doc_size', true );
                        $doc_url    = get_post_meta( $faq->ID, '_faq_doc_url', true );
                        
                        // 获取分类ID
                        $faq_cats = wp_get_post_terms( $faq->ID, 'faq_category', array( 'fields' => 'ids' ) );
                        $cat_ids = ! empty( $faq_cats ) ? implode( ',', $faq_cats ) : '';
                    ?>
                        <div class="faq-item" data-categories="<?php echo esc_attr( $cat_ids ); ?>">
                            <div class="faq-question">
                                <div class="faq-question-content">
                                    <span class="faq-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"/>
                                        </svg>
                                    </span>
                                    <h3><?php echo esc_html( $faq->post_title ); ?></h3>
                                </div>
                                <?php if ( $doc_format ) : ?>
                                    <span class="faq-format" style="background: <?php echo esc_attr( FAQ_Manager::get_format_color( $doc_format ) ); ?>">
                                        <?php echo esc_html( strtoupper( $doc_format ) ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    <?php echo wp_kses_post( apply_filters( 'the_content', $faq->post_content ) ); ?>
                                </div>
                                
                                <?php if ( ! empty( $doc_url ) && ! empty( $doc_name ) ) : ?>
                                    <div class="faq-document">
                                        <div class="doc-info">
                                            <span class="doc-icon" style="color: <?php echo esc_attr( FAQ_Manager::get_format_color( $doc_format ) ); ?>">
                                                <?php echo FAQ_Manager::get_format_icon( $doc_format ); ?>
                                            </span>
                                            <div class="doc-details">
                                                <span class="doc-name"><?php echo esc_html( $doc_name ); ?></span>
                                                <?php if ( $doc_size ) : ?>
                                                    <span class="doc-size"><?php echo esc_html( $doc_size ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <a href="<?php echo esc_url( $doc_url ); ?>" class="doc-download" target="_blank" download>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                                <polyline points="7 10 12 15 17 10"/>
                                                <line x1="12" y1="15" x2="12" y2="3"/>
                                            </svg>
                                            <?php esc_html_e( '下载', 'developer-starter' ); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="faq-empty">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <p><?php esc_html_e( '暂无常见问题', 'developer-starter' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
