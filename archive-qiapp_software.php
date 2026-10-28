<?php
/**
 * 启灵软件库归档模板
 *
 * @package Developer_Starter
 */

get_header();
global $wp_query;

$software_post_type = developer_starter_qiapp_get_post_type();
$category_taxonomy = developer_starter_qiapp_get_category_taxonomy();
$tag_taxonomy = developer_starter_qiapp_get_tag_taxonomy();
$archive_link = developer_starter_qiapp_get_archive_link();
$filter_base_link = $archive_link;
$archive_title = post_type_archive_title( '', false );
$archive_description = function_exists( 'get_the_archive_description' ) ? get_the_archive_description() : '';
if ( ! $archive_description ) {
    $post_type_object = get_post_type_object( $software_post_type );
    if ( $post_type_object && ! empty( $post_type_object->description ) ) {
        $archive_description = $post_type_object->description;
    }
}
if ( is_tax( array( $category_taxonomy, $tag_taxonomy ) ) ) {
    $term = get_queried_object();
    if ( $term instanceof WP_Term ) {
        $archive_title = $term->name;
        $archive_description = $term->description;

        if ( $term->taxonomy === $tag_taxonomy ) {
            $term_link = get_term_link( $term );
            if ( ! is_wp_error( $term_link ) && $term_link ) {
                $filter_base_link = $term_link;
            }
        }
    }
}
if ( ! $archive_description ) {
    $archive_description = __( '集中展示所有软件产品、版本、下载入口与相关内容。', 'qiling' );
}

$software_categories = get_terms(
    array(
        'taxonomy'   => $category_taxonomy,
        'hide_empty' => true,
        'number'     => 12,
    )
);
if ( ! is_array( $software_categories ) ) {
    $software_categories = array();
}

$license_options = array(
    'free'         => __( '免费', 'qiling' ),
    'opensource'   => __( '开源', 'qiling' ),
    'paid'         => __( '收费', 'qiling' ),
    'trial'        => __( '试用', 'qiling' ),
    'subscription' => __( '订阅', 'qiling' ),
    'ad'           => __( '广告支持', 'qiling' ),
    'freemium'     => __( '免费增值', 'qiling' ),
);
$sort_options = array(
    'latest'    => __( '最新发布', 'qiling' ),
    'updated'   => __( '最近更新', 'qiling' ),
    'downloads' => __( '下载量优先', 'qiling' ),
    'views'     => __( '浏览量优先', 'qiling' ),
    'name'      => __( '名称排序', 'qiling' ),
    'oldest'    => __( '最早发布', 'qiling' ),
);
$selected_category = isset( $_GET['qiapp_category'] ) ? sanitize_title( wp_unslash( $_GET['qiapp_category'] ) ) : '';
$selected_license = isset( $_GET['qiapp_license'] ) ? sanitize_key( wp_unslash( $_GET['qiapp_license'] ) ) : '';
$selected_sort = isset( $_GET['qiapp_sort'] ) ? sanitize_key( wp_unslash( $_GET['qiapp_sort'] ) ) : 'latest';
if ( ! $selected_category && is_tax( $category_taxonomy ) ) {
    $current_term = get_queried_object();
    if ( $current_term instanceof WP_Term ) {
        $selected_category = $current_term->slug;
    }
}
$chip_query_args = array_filter(
    array(
        's'             => get_search_query(),
        'qiapp_license' => $selected_license,
        'qiapp_sort'    => 'latest' !== $selected_sort ? $selected_sort : '',
    ),
    static function ( $value ) {
        return '' !== $value && null !== $value;
    }
);
$total_software = function_exists( 'developer_starter_qiapp_get_total_software_count' )
    ? developer_starter_qiapp_get_total_software_count()
    : 0;
$total_downloads = function_exists( 'developer_starter_qiapp_get_total_downloads_count' )
    ? developer_starter_qiapp_get_total_downloads_count()
    : 0;
$results_count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
$loop_post_ids = ! empty( $wp_query->posts ) ? wp_list_pluck( $wp_query->posts, 'ID' ) : array();
$preloaded_entries = developer_starter_qiapp_preload_entries( $loop_post_ids );
?>

<div class="qiapp-archive-template">
    <section class="qiapp-archive-hero">
        <div class="container">
            <span class="qiapp-archive-eyebrow"><?php esc_html_e( '软件库', 'qiling' ); ?></span>
            <h1 class="qiapp-archive-title"><?php echo esc_html( $archive_title ? $archive_title : __( '软件库', 'qiling' ) ); ?></h1>
            <p class="qiapp-archive-subtitle"><?php echo esc_html( wp_strip_all_tags( $archive_description ) ); ?></p>
            <div class="qiapp-archive-stats">
                <div class="qiapp-archive-stat">
                    <strong><?php echo esc_html( number_format_i18n( $total_software ) ); ?></strong>
                    <span><?php esc_html_e( '软件数量', 'qiling' ); ?></span>
                </div>
                <div class="qiapp-archive-stat">
                    <strong><?php echo esc_html( number_format_i18n( $total_downloads ) ); ?></strong>
                    <span><?php esc_html_e( '累计下载', 'qiling' ); ?></span>
                </div>
                <div class="qiapp-archive-stat">
                    <strong><?php echo esc_html( number_format_i18n( count( $software_categories ) ) ); ?></strong>
                    <span><?php esc_html_e( '活跃分类', 'qiling' ); ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="qiapp-archive-list-section">
        <div class="container">
            <form class="qiapp-archive-filters" method="get" action="<?php echo esc_url( $filter_base_link ); ?>">
                <div class="qiapp-archive-filter-field qiapp-archive-filter-search">
                    <label for="qiapp-archive-search"><?php esc_html_e( '搜索软件', 'qiling' ); ?></label>
                    <input id="qiapp-archive-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( '输入软件名、功能或关键词', 'qiling' ); ?>" />
                </div>

                <div class="qiapp-archive-filter-field">
                    <label for="qiapp-archive-category"><?php esc_html_e( '软件分类', 'qiling' ); ?></label>
                    <select id="qiapp-archive-category" name="qiapp_category">
                        <option value=""><?php esc_html_e( '全部分类', 'qiling' ); ?></option>
                        <?php foreach ( $software_categories as $software_category ) : ?>
                            <option value="<?php echo esc_attr( $software_category->slug ); ?>" <?php selected( $selected_category, $software_category->slug ); ?>>
                                <?php echo esc_html( $software_category->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="qiapp-archive-filter-field">
                    <label for="qiapp-archive-license"><?php esc_html_e( '授权方式', 'qiling' ); ?></label>
                    <select id="qiapp-archive-license" name="qiapp_license">
                        <option value=""><?php esc_html_e( '全部授权', 'qiling' ); ?></option>
                        <?php foreach ( $license_options as $license_key => $license_label ) : ?>
                            <option value="<?php echo esc_attr( $license_key ); ?>" <?php selected( $selected_license, $license_key ); ?>>
                                <?php echo esc_html( $license_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="qiapp-archive-filter-field">
                    <label for="qiapp-archive-sort"><?php esc_html_e( '排序方式', 'qiling' ); ?></label>
                    <select id="qiapp-archive-sort" name="qiapp_sort">
                        <?php foreach ( $sort_options as $sort_key => $sort_label ) : ?>
                            <option value="<?php echo esc_attr( $sort_key ); ?>" <?php selected( $selected_sort, $sort_key ); ?>>
                                <?php echo esc_html( $sort_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="qiapp-archive-filter-actions">
                    <button type="submit"><?php esc_html_e( '筛选结果', 'qiling' ); ?></button>
                    <a href="<?php echo esc_url( $filter_base_link ); ?>"><?php esc_html_e( '重置', 'qiling' ); ?></a>
                </div>
            </form>

            <?php if ( ! empty( $software_categories ) ) : ?>
                <div class="qiapp-archive-chips">
                    <a class="qiapp-archive-chip <?php echo $selected_category ? '' : 'is-active'; ?>" href="<?php echo esc_url( add_query_arg( $chip_query_args, $filter_base_link ) ); ?>">
                        <?php esc_html_e( '全部', 'qiling' ); ?>
                    </a>
                    <?php foreach ( array_slice( $software_categories, 0, 8 ) as $software_category ) : ?>
                        <a class="qiapp-archive-chip <?php echo $selected_category === $software_category->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_merge( $chip_query_args, array( 'qiapp_category' => $software_category->slug ) ), $filter_base_link ) ); ?>">
                            <?php echo esc_html( $software_category->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="qiapp-archive-results-bar">
                <span><?php echo esc_html( sprintf( __( '共找到 %d 个软件结果', 'qiling' ), $results_count ) ); ?></span>
                <?php if ( $selected_category || $selected_license || get_search_query() ) : ?>
                    <span><?php esc_html_e( '当前结果已应用筛选条件', 'qiling' ); ?></span>
                <?php endif; ?>
            </div>

            <?php if ( have_posts() ) : ?>
                <div class="qiapp-archive-grid">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php
                        $entry = developer_starter_qiapp_get_entry_data( get_the_ID(), $preloaded_entries );
                        if ( ! $entry ) {
                            continue;
                        }

                        $initial = function_exists( 'mb_substr' )
                            ? mb_substr( $entry['title'], 0, 1 )
                            : substr( $entry['title'], 0, 1 );
                        $detail_link = ! empty( $entry['permalink'] ) ? $entry['permalink'] : get_permalink();
                        $review_link = '';
                        if ( ! empty( $entry['primary_article_id'] ) ) {
                            $review_permalink = get_permalink( (int) $entry['primary_article_id'] );
                            if ( $review_permalink ) {
                                $review_link = $review_permalink;
                            }
                        }
                        ?>
                        <article class="qiapp-archive-card">
                            <div class="qiapp-archive-card-link">
                                <div class="qiapp-archive-card-main">
                                    <div class="qiapp-archive-card-top">
                                        <a class="qiapp-archive-card-icon-link" href="<?php echo esc_url( $detail_link ); ?>">
                                            <?php if ( $entry['icon'] ) : ?>
                                                <img class="qiapp-archive-card-icon" src="<?php echo esc_url( $entry['icon'] ); ?>" alt="<?php echo esc_attr( $entry['title'] ); ?>" loading="lazy" decoding="async" />
                                            <?php else : ?>
                                                <div class="qiapp-archive-card-icon qiapp-archive-card-icon-placeholder">
                                                    <span><?php echo esc_html( $initial ); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </a>

                                        <div class="qiapp-archive-card-headings">
                                            <h2 class="qiapp-archive-card-title">
                                                <a class="qiapp-archive-card-title-link" href="<?php echo esc_url( $detail_link ); ?>"><?php echo esc_html( $entry['title'] ); ?></a>
                                            </h2>
                                            <?php if ( $entry['version_label'] ) : ?>
                                                <span class="qiapp-archive-card-version"><?php echo esc_html( $entry['version_label'] ); ?></span>
                                            <?php endif; ?>
                                            <?php if ( $entry['primary_category'] ) : ?>
                                                <span class="qiapp-archive-card-category"><?php echo esc_html( $entry['primary_category'] ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ( $entry['summary'] ) : ?>
                                        <p class="qiapp-archive-card-summary"><?php echo esc_html( wp_trim_words( $entry['summary'], 26, '...' ) ); ?></p>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $entry['license_items'] ) || $entry['platform_text'] ) : ?>
                                        <div class="qiapp-software-badges">
                                            <?php foreach ( $entry['license_items'] as $license_item ) : ?>
                                                <span class="qiapp-badge <?php echo esc_attr( $license_item['class'] ); ?>">
                                                    <?php echo esc_html( $license_item['text'] ); ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if ( $entry['platform_text'] ) : ?>
                                                <span class="qiapp-taxonomy-chip"><?php echo esc_html( $entry['platform_text'] ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="qiapp-archive-card-meta">
                                        <?php if ( $entry['developer'] ) : ?>
                                            <span><?php echo esc_html( $entry['developer'] ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $entry['file_size'] ) : ?>
                                            <span><?php echo esc_html( $entry['file_size'] ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $entry['official_website'] ) : ?>
                                            <span><?php esc_html_e( '官网', 'qiling' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $entry['docs_url'] ) : ?>
                                            <span><?php esc_html_e( '文档', 'qiling' ); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="qiapp-archive-card-meta">
                                        <span><?php echo esc_html( $entry['download_text'] ); ?> <?php esc_html_e( '下载', 'qiling' ); ?></span>
                                        <?php if ( $entry['view_count'] > 0 ) : ?>
                                            <span><?php echo esc_html( $entry['view_text'] ); ?> <?php esc_html_e( '浏览', 'qiling' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $entry['update_date'] ) : ?>
                                            <span><?php echo esc_html( $entry['update_date'] ); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ( $entry['system_requirements'] || $entry['installation_guide'] ) : ?>
                                        <div class="qiapp-archive-card-meta">
                                            <?php if ( $entry['system_requirements'] ) : ?>
                                                <span><?php esc_html_e( '系统要求', 'qiling' ); ?></span>
                                            <?php endif; ?>
                                            <?php if ( $entry['installation_guide'] ) : ?>
                                                <span><?php esc_html_e( '安装说明', 'qiling' ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="qiapp-archive-card-actions">
                                    <a class="qiapp-archive-card-action qiapp-archive-card-action-download" href="<?php echo esc_url( $detail_link ); ?>">
                                        <?php esc_html_e( '下载', 'qiling' ); ?>
                                    </a>
                                    <?php if ( $review_link ) : ?>
                                        <a class="qiapp-archive-card-action qiapp-archive-card-action-review" href="<?php echo esc_url( $review_link ); ?>">
                                            <?php esc_html_e( '评测', 'qiling' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <div class="qiapp-archive-pagination">
                    <?php
                    the_posts_pagination(
                        array(
                            'prev_text' => __( '上一页', 'qiling' ),
                            'next_text' => __( '下一页', 'qiling' ),
                        )
                    );
                    ?>
                </div>
            <?php else : ?>
                <div class="qiapp-archive-empty">
                    <h2><?php esc_html_e( '还没有软件内容', 'qiling' ); ?></h2>
                    <p><?php esc_html_e( '先在后台新增软件，软件库就会自动在这里展示。', 'qiling' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
get_footer();
