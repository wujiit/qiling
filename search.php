<?php
/**
 * The template for displaying search results
 *
 * @package Developer_Starter
 */

global $wp_query;

$search_theme_options = function_exists( 'developer_starter_get_options_cache' ) ? developer_starter_get_options_cache() : array();
$search_option_enabled = static function ( $key, $default = true ) use ( $search_theme_options ) {
    if ( is_array( $search_theme_options ) && array_key_exists( $key, $search_theme_options ) ) {
        return '1' === (string) $search_theme_options[ $key ];
    }

    return (bool) $default;
};

$search_builder_enabled  = developer_starter_get_option( 'search_builder_enable', '' );
$search_builder_page_id  = absint( developer_starter_get_option( 'search_builder_page_id', '' ) );
$search_builder_position = sanitize_key( (string) developer_starter_get_option( 'search_builder_position', 'prepend_results' ) );
if ( ! in_array( $search_builder_position, array( 'prepend_results', 'replace_header' ), true ) ) {
    $search_builder_position = 'prepend_results';
}
$search_query_display = rawurldecode( (string) get_search_query( false ) );
$search_has_query     = '' !== trim( $search_query_display );
$search_results_count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
$search_form_enabled = $search_option_enabled( 'search_form_enable', true );
$search_scope_enabled = $search_option_enabled( 'search_scope_enable', true );
$search_result_show_thumb = $search_option_enabled( 'search_result_show_thumb', true );
$search_result_show_type = $search_option_enabled( 'search_result_show_type', true );
$search_result_show_date = $search_option_enabled( 'search_result_show_date', true );
$search_result_show_excerpt = $search_option_enabled( 'search_result_show_excerpt', true );
$search_result_excerpt_length = isset( $search_theme_options['search_result_excerpt_length'] ) ? absint( $search_theme_options['search_result_excerpt_length'] ) : 40;
if ( $search_result_excerpt_length <= 0 ) {
    $search_result_excerpt_length = 40;
}
$search_result_excerpt_length = min( 120, max( 10, $search_result_excerpt_length ) );
$search_empty_title_template = isset( $search_theme_options['search_empty_title'] ) ? trim( wp_strip_all_tags( (string) $search_theme_options['search_empty_title'] ) ) : '';
if ( '' === $search_empty_title_template ) {
    $search_empty_title = $search_has_query
        ? sprintf(
            /* translators: %s: search query */
            __( '未找到与 "%s" 相关的内容', 'developer-starter' ),
            $search_query_display
        )
        : __( '请输入关键词开始搜索', 'developer-starter' );
} elseif ( $search_has_query && false !== strpos( $search_empty_title_template, '%s' ) ) {
    $search_empty_title = str_replace( '%s', $search_query_display, $search_empty_title_template );
} else {
    $search_empty_title = $search_empty_title_template;
}
$search_empty_text = isset( $search_theme_options['search_empty_text'] ) ? trim( wp_strip_all_tags( (string) $search_theme_options['search_empty_text'] ) ) : '';
if ( '' === $search_empty_text ) {
    $search_empty_text = __( '可以换一个更短的关键词，或从最新内容继续浏览。', 'developer-starter' );
}
$search_scope = function_exists( 'developer_starter_get_current_search_scope' ) ? developer_starter_get_current_search_scope() : 'all';
$search_scope_choices = function_exists( 'developer_starter_get_search_scope_choices' ) ? developer_starter_get_search_scope_choices() : array(
    'all'     => __( '全部', 'developer-starter' ),
    'title'   => __( '标题', 'developer-starter' ),
    'content' => __( '正文', 'developer-starter' ),
    'tag'     => __( '标签', 'developer-starter' ),
);
$search_terms = function_exists( 'developer_starter_get_search_query_terms' ) ? developer_starter_get_search_query_terms( $search_query_display ) : array( $search_query_display );
$search_highlight_allowed = array(
    // developer_starter_highlight_search_terms() emits search-highlight marks.
    'mark' => array(
        'class' => true,
    ),
);
$search_form_action = function_exists( 'developer_starter_get_search_form_action_url' ) ? developer_starter_get_search_form_action_url() : home_url( '/' );
$search_mode_manager = function_exists( 'developer_starter_get_search_mode_manager' ) ? developer_starter_get_search_mode_manager() : null;
$search_mode = $search_mode_manager ? $search_mode_manager->get_current_mode() : 'all';
$search_mode_choices = $search_mode_manager ? $search_mode_manager->get_frontend_modes() : array();
$search_mode_switch_enabled = $search_mode_manager && $search_mode_manager->visitor_switching_enabled() && count( $search_mode_choices ) > 1;
$search_mode_data = $search_mode_manager ? $search_mode_manager->get_modes( true ) : array();
$search_mode_label = isset( $search_mode_data[ $search_mode ]['label'] ) ? $search_mode_data[ $search_mode ]['label'] : __( '综合搜索', 'developer-starter' );
$search_hot_keywords = function_exists( 'developer_starter_get_search_hot_keywords' ) ? developer_starter_get_search_hot_keywords() : array();

$assets_version = ! empty( $search_theme_options['assets_version'] ) ? $search_theme_options['assets_version'] : DEVELOPER_STARTER_VERSION;
$archive_loading_mode = isset( $search_theme_options['archive_loading_mode'] ) ? sanitize_key( (string) $search_theme_options['archive_loading_mode'] ) : 'regular';
if ( ! in_array( $archive_loading_mode, array( 'regular', 'infinite' ), true ) ) {
    $archive_loading_mode = 'regular';
}
$archive_infinite_enabled = 'infinite' === $archive_loading_mode;
if ( $archive_infinite_enabled ) {
    wp_enqueue_style( 'developer-starter-infinite-scroll', get_template_directory_uri() . '/assets/css/infinite-scroll.css', array(), $assets_version );
    wp_enqueue_script( 'developer-starter-infinite-scroll', get_template_directory_uri() . '/assets/js/infinite-scroll.js', array(), $assets_version, true );
    wp_localize_script(
        'developer-starter-infinite-scroll',
        'qilingInfiniteScrollI18n',
        array(
            'loadMore' => __( '加载更多', 'developer-starter' ),
            'loading'  => __( '正在加载...', 'developer-starter' ),
            'done'     => __( '已经到底了', 'developer-starter' ),
            'error'    => __( '加载失败，请稍后再试', 'developer-starter' ),
            'retry'    => __( '重试加载', 'developer-starter' ),
        )
    );
}

$search_current_page = max( 1, (int) get_query_var( 'paged' ) );
$search_max_pages    = ( isset( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query'] instanceof WP_Query ) ? max( 1, (int) $GLOBALS['wp_query']->max_num_pages ) : 1;
$search_next_url     = '';
if ( $archive_infinite_enabled && $search_current_page < $search_max_pages ) {
    $search_next_url = get_pagenum_link( $search_current_page + 1 );
    if ( 'all' !== $search_scope ) {
        $search_next_url = add_query_arg( 'search_scope', $search_scope, $search_next_url );
    }
    $search_next_url = add_query_arg( 'qiling_search_mode', $search_mode, $search_next_url );
}
$search_infinite_attrs = '';
if ( $archive_infinite_enabled ) {
    $search_infinite_attrs = sprintf(
        ' data-qiling-infinite-scroll="1" data-context="search" data-item-container=".search-results-list" data-pagination=".search-results-pagination" data-current-page="%1$d" data-max-pages="%2$d" data-next-url="%3$s"',
        (int) $search_current_page,
        (int) $search_max_pages,
        esc_url( $search_next_url )
    );
}

get_header();

$search_builder_header_rendered = false;
if ( $search_builder_enabled && 'replace_header' === $search_builder_position && function_exists( 'developer_starter_render_builder_template_page' ) ) {
    $search_builder_header_rendered = developer_starter_render_builder_template_page(
        $search_builder_page_id,
        array(
            'context'       => 'search',
            'wrapper_class' => 'search-builder-template search-builder-template--header',
        )
    );
}
?>

<?php if ( ! $search_builder_header_rendered ) : ?>
    <div class="page-header search-page-header">
        <div class="container">
            <h1 class="page-title">
                <?php
                if ( $search_has_query ) {
                    printf( esc_html__( '搜索结果：%s', 'developer-starter' ), esc_html( $search_query_display ) );
                } else {
                    esc_html_e( '搜索结果', 'developer-starter' );
                }
                ?>
            </h1>
            <p class="search-page-description">
                <?php printf( esc_html( _n( '共找到 %s 条结果', '共找到 %s 条结果', $search_results_count, 'developer-starter' ) ), esc_html( number_format_i18n( $search_results_count ) ) ); ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<section class="search-results section-padding<?php echo $archive_infinite_enabled ? ' qiling-infinite-root' : ''; ?>"<?php echo $search_infinite_attrs; ?>>
    <div class="container">
        <?php
        // 搜索页装修只插入自定义模块，原生搜索结果链路继续保留。
        if ( $search_builder_enabled && 'prepend_results' === $search_builder_position && function_exists( 'developer_starter_render_builder_template_page' ) ) {
            developer_starter_render_builder_template_page(
                $search_builder_page_id,
                array(
                    'context'       => 'search',
                    'wrapper_class' => 'search-builder-template search-builder-template--prepend',
                )
            );
        }
        ?>
        <?php if ( 'video' === $search_mode ) : ?>
        <div class="search-mode-summary">
            <div class="search-mode-summary__meta">
                <span class="search-mode-summary__label"><?php echo esc_html( $search_mode_label ); ?></span>
                <span><?php printf( esc_html( _n( '%s 条结果', '%s 条结果', $search_results_count, 'developer-starter' ) ), esc_html( number_format_i18n( $search_results_count ) ) ); ?></span>
            </div>
            <?php if ( $search_mode_switch_enabled ) : ?>
                <nav class="search-mode-tabs" aria-label="<?php esc_attr_e( '切换搜索模式', 'developer-starter' ); ?>">
                    <?php foreach ( $search_mode_choices as $mode_key => $mode_data ) : ?>
                        <?php
                        $mode_url_args = array( 'qiling_search_mode' => $mode_key );
                        if ( 'all' !== $search_scope ) {
                            $mode_url_args['search_scope'] = $search_scope;
                        }
                        $mode_url = function_exists( 'developer_starter_get_search_pretty_url' )
                            ? developer_starter_get_search_pretty_url( $search_query_display, $mode_url_args )
                            : add_query_arg( array_merge( array( 's' => $search_query_display ), $mode_url_args ), home_url( '/' ) );
                        ?>
                        <a class="search-mode-tab<?php echo $mode_key === $search_mode ? ' is-active' : ''; ?>" href="<?php echo esc_url( $mode_url ); ?>"<?php echo $mode_key === $search_mode ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $mode_data['label'] ); ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $search_hot_keywords ) ) : ?>
            <div class="search-hot-keywords">
                <span class="search-hot-keywords__label"><?php esc_html_e( '热门搜索', 'developer-starter' ); ?></span>
                <?php foreach ( $search_hot_keywords as $hot_keyword ) : ?>
                    <?php
                    $hot_url = function_exists( 'developer_starter_get_search_pretty_url' )
                        ? developer_starter_get_search_pretty_url( $hot_keyword, array( 'qiling_search_mode' => $search_mode ) )
                        : add_query_arg( array( 's' => $hot_keyword, 'qiling_search_mode' => $search_mode ), home_url( '/' ) );
                    ?>
                    <a href="<?php echo esc_url( $hot_url ); ?>"><?php echo esc_html( $hot_keyword ); ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Search Form -->
        <?php if ( $search_form_enabled ) : ?>
        <div class="search-form-wrap">
            <form role="search" method="get" class="qiling-search-enhanced" data-qiling-search-form="1" action="<?php echo esc_url( $search_form_action ); ?>"<?php if ( developer_starter_get_option( 'search_rewrite', '' ) ) : ?> onsubmit="return dsSearchRedirect(this);"<?php endif; ?>>
                <div class="search-form-fields">
                    <div class="search-form-input-group">
                        <label class="screen-reader-text" for="search-page-input"><?php esc_html_e( '搜索关键词', 'developer-starter' ); ?></label>
                        <input id="search-page-input" type="search" name="s" value="<?php echo esc_attr( $search_query_display ); ?>"
                               placeholder="<?php esc_attr_e( '继续搜索...', 'developer-starter' ); ?>"
                               class="search-form-input" autocomplete="off" data-qiling-search-input="1" />
                    </div>
                    <?php if ( $search_scope_enabled ) : ?>
                    <label class="screen-reader-text" for="search-scope-select"><?php esc_html_e( '搜索范围', 'developer-starter' ); ?></label>
                    <select id="search-scope-select" name="search_scope" class="search-scope-select" data-qiling-search-scope="1">
                        <?php foreach ( $search_scope_choices as $scope_key => $scope_label ) : ?>
                            <option value="<?php echo esc_attr( $scope_key ); ?>"<?php selected( $search_scope, $scope_key ); ?>>
                                <?php echo esc_html( $scope_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <?php if ( $search_mode_switch_enabled ) : ?>
                    <label class="screen-reader-text" for="search-mode-select"><?php esc_html_e( '搜索模式', 'developer-starter' ); ?></label>
                    <select id="search-mode-select" name="qiling_search_mode" class="search-mode-select">
                        <?php foreach ( $search_mode_choices as $mode_key => $mode_data ) : ?>
                            <option value="<?php echo esc_attr( $mode_key ); ?>"<?php selected( $search_mode, $mode_key ); ?>><?php echo esc_html( $mode_data['label'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php else : ?>
                    <input type="hidden" name="qiling_search_mode" value="<?php echo esc_attr( $search_mode ); ?>" />
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary search-form-submit"><?php esc_html_e( '搜索', 'developer-starter' ); ?></button>
                </div>
                <div class="search-history" data-qiling-search-history></div>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if ( have_posts() ) : ?>
            <?php
            $search_results_template = 'template-parts/search/results-' . ( 'video' === $search_mode ? 'video' : 'default' );
            if ( function_exists( 'developer_starter_get_search_result_template' ) ) {
                $search_results_template = developer_starter_get_search_result_template( $search_results_template, $search_mode );
            }
            if ( '' === $search_results_template || false !== strpos( $search_results_template, '..' ) || ! preg_match( '#^[a-zA-Z0-9_/-]+$#', $search_results_template ) ) {
                $search_results_template = 'template-parts/search/results-default';
            }
            if ( ! locate_template( $search_results_template . '.php', false, false ) ) {
                $search_results_template = 'template-parts/search/results-default';
            }
            get_template_part(
                $search_results_template,
                null,
                array(
                    'wp_query'                     => $wp_query,
                    'search_result_show_thumb'     => $search_result_show_thumb,
                    'search_result_show_type'      => $search_result_show_type,
                    'search_result_show_date'      => $search_result_show_date,
                    'search_result_show_excerpt'   => $search_result_show_excerpt,
                    'search_result_excerpt_length' => $search_result_excerpt_length,
                    'search_terms'                 => $search_terms,
                    'search_highlight_allowed'     => $search_highlight_allowed,
                )
            );
            ?>
            
            <!-- Pagination -->
            <nav class="search-results-pagination<?php echo $archive_infinite_enabled ? ' qiling-infinite-pagination-fallback' : ''; ?>" aria-label="<?php esc_attr_e( '搜索结果分页', 'developer-starter' ); ?>">
                <?php
                $search_pagination_args = array(
                    'mid_size'  => 2,
                    'prev_text' => sprintf( '&laquo; %s', __( '上一页', 'developer-starter' ) ),
                    'next_text' => sprintf( '%s &raquo;', __( '下一页', 'developer-starter' ) ),
                );

                $search_pagination_args['add_args'] = array(
                    'qiling_search_mode' => $search_mode,
                );
                if ( 'all' !== $search_scope ) {
                    $search_pagination_args['add_args']['search_scope'] = $search_scope;
                }

                the_posts_pagination( $search_pagination_args );
                ?>
            </nav>

            <?php if ( $archive_infinite_enabled ) : ?>
                <div class="qiling-infinite-control" data-qiling-infinite-control>
                    <button type="button" class="qiling-infinite-load-more" data-qiling-infinite-load-more><?php esc_html_e( '加载更多', 'developer-starter' ); ?></button>
                    <span class="qiling-infinite-status qiling-infinite-status--loading"><span class="qiling-infinite-spinner" aria-hidden="true"></span><?php esc_html_e( '正在加载...', 'developer-starter' ); ?></span>
                    <span class="qiling-infinite-status qiling-infinite-status--done"><?php esc_html_e( '已经到底了', 'developer-starter' ); ?></span>
                    <span class="qiling-infinite-status qiling-infinite-status--error"><?php esc_html_e( '加载失败，请稍后再试', 'developer-starter' ); ?></span>
                </div>
            <?php endif; ?>
            
        <?php else : ?>
            <?php
            $recent_search_posts = array();
            if ( 'video' !== $search_mode ) {
                $recent_search_posts = get_posts(
                    array(
                        'ignore_sticky_posts' => true,
                        'no_found_rows'       => true,
                        'post_status'         => 'publish',
                        'post_type'           => 'post',
                        'posts_per_page'      => 4,
                        'suppress_filters'    => false,
                    )
                );
            }
            $posts_page_id  = (int) get_option( 'page_for_posts' );
            $posts_page_url = $posts_page_id > 0 ? get_permalink( $posts_page_id ) : get_post_type_archive_link( 'post' );
            if ( ! $posts_page_url ) {
                $posts_page_url = home_url( '/' );
            }
            $search_empty_secondary_url = $posts_page_url;
            $search_empty_secondary_label = __( '查看最新内容', 'developer-starter' );
            if ( 'video' === $search_mode ) {
                $search_empty_secondary_url = function_exists( 'developer_starter_get_search_pretty_url' )
                    ? developer_starter_get_search_pretty_url( $search_query_display, array( 'qiling_search_mode' => 'all' ) )
                    : add_query_arg( array( 's' => $search_query_display, 'qiling_search_mode' => 'all' ), home_url( '/' ) );
                $search_empty_secondary_label = __( '切换到综合搜索', 'developer-starter' );
            }
            ?>
            <div class="search-empty-state">
                <h2 class="search-empty-state__title">
                    <?php echo esc_html( 'video' === $search_mode ? __( '没有找到相关影视内容', 'developer-starter' ) : $search_empty_title ); ?>
                </h2>
                <p class="search-empty-state__hint">
                    <?php echo esc_html( 'video' === $search_mode ? __( '请检查关键词是否正确，或尝试搜索演员、导演或影片名称。', 'developer-starter' ) : $search_empty_text ); ?>
                </p>
                <div class="search-empty-actions">
                    <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '返回首页', 'developer-starter' ); ?></a>
                    <a class="btn btn-outline" href="<?php echo esc_url( $search_empty_secondary_url ); ?>"><?php echo esc_html( $search_empty_secondary_label ); ?></a>
                </div>

                <?php if ( $recent_search_posts ) : ?>
                    <div class="search-empty-suggestions">
                        <h3 class="search-empty-suggestions__title"><?php esc_html_e( '最新发布', 'developer-starter' ); ?></h3>
                        <ul class="search-empty-suggestions__list">
                            <?php foreach ( $recent_search_posts as $recent_post ) : ?>
                                <li>
                                    <a href="<?php echo esc_url( get_permalink( $recent_post ) ); ?>">
                                        <?php echo esc_html( get_the_title( $recent_post ) ? get_the_title( $recent_post ) : __( '（无标题）', 'developer-starter' ) ); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
