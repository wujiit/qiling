<?php
/**
 * Search and search-rate-limit helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function developer_starter_get_search_mode_manager() {
    return \Developer_Starter\Core\Search_Mode_Manager::get_instance();
}

function developer_starter_get_current_search_mode() {
    return developer_starter_get_search_mode_manager()->get_current_mode();
}

function developer_starter_get_search_mode_choices( $frontend_only = false ) {
    $manager = developer_starter_get_search_mode_manager();
    $modes   = $frontend_only ? $manager->get_frontend_modes() : $manager->get_modes( true );
    $choices = array();
    foreach ( $modes as $mode_key => $mode ) {
        $choices[ $mode_key ] = $mode['label'];
    }

    return $choices;
}

function developer_starter_get_search_mode_form_value() {
    return developer_starter_get_current_search_mode();
}

function developer_starter_resolve_search_mode( $configured_mode = 'inherit' ) {
    $configured_mode = sanitize_key( (string) $configured_mode );
    if ( '' === $configured_mode || 'inherit' === $configured_mode ) {
        return developer_starter_get_search_mode_form_value();
    }

    return developer_starter_get_search_mode_manager()->normalize_mode( $configured_mode );
}

function developer_starter_get_search_hot_keywords() {
    $raw = (string) developer_starter_get_option( 'search_hot_keywords', '' );
    if ( '' === trim( $raw ) ) {
        return array();
    }

    $keywords = preg_split( '/[,，\r\n]+/u', $raw );
    $keywords = array_map(
        static function ( $keyword ) {
            return sanitize_text_field( trim( (string) $keyword ) );
        },
        (array) $keywords
    );

    return array_values( array_unique( array_filter( $keywords ) ) );
}

function developer_starter_get_search_result_template( $template, $mode = '' ) {
    return developer_starter_get_search_mode_manager()->get_result_template( $template, $mode );
}

function developer_starter_get_search_result_card_data( $data, $post_id, $mode = '' ) {
    return developer_starter_get_search_mode_manager()->get_result_card_data( $data, $post_id, $mode );
}

/**
 * 获取搜索表单 action 地址
 *
 * @return string
 */
function developer_starter_get_search_form_action_url() {
    $url = developer_starter_get_option( 'search_rewrite', '' ) ? home_url( '/search/' ) : home_url( '/' );
    if ( function_exists( 'developer_starter_translate_internal_url_for_frontend_lang' ) ) {
        $url = developer_starter_translate_internal_url_for_frontend_lang( $url );
    }
    return (string) apply_filters( 'developer_starter_search_form_action_url', $url );
}

/**
 * 获取搜索关键词的前台访问地址
 *
 * @param string $search 关键词
 * @return string
 */
function developer_starter_get_search_pretty_url( $search, $args = array() ) {
    $search = trim( wp_strip_all_tags( (string) $search ) );
    if ( $search === '' ) {
        return developer_starter_get_search_form_action_url();
    }

    if ( developer_starter_get_option( 'search_rewrite', '' ) ) {
        $url = trailingslashit( developer_starter_get_search_form_action_url() ) . rawurlencode( $search ) . '/';
        return empty( $args ) ? $url : add_query_arg( $args, $url );
    }

    return add_query_arg( array_merge( array( 's' => $search ), (array) $args ), developer_starter_get_search_form_action_url() );
}

/**
 * Normalize the front-end search scope.
 *
 * @param string $scope Raw scope.
 * @return string
 */
function developer_starter_normalize_search_scope( $scope ) {
    $scope = sanitize_key( (string) $scope );

    return in_array( $scope, array( 'all', 'title', 'content', 'tag' ), true ) ? $scope : 'all';
}

/**
 * Search scope labels used by the native search template.
 *
 * @return array<string,string>
 */
function developer_starter_get_search_scope_choices() {
    return array(
        'all'     => __( '全部', 'developer-starter' ),
        'title'   => __( '标题', 'developer-starter' ),
        'content' => __( '正文', 'developer-starter' ),
        'tag'     => __( '标签', 'developer-starter' ),
    );
}

/**
 * Resolve the current search scope from query vars or GET params.
 *
 * @return string
 */
function developer_starter_get_current_search_scope() {
    $scope = get_query_var( 'search_scope' );

    if ( '' === (string) $scope && isset( $_GET['search_scope'] ) ) {
        $scope = sanitize_key( wp_unslash( (string) $_GET['search_scope'] ) );
    }

    return developer_starter_normalize_search_scope( (string) $scope );
}

/**
 * Register the search_scope query var for pagination and pretty-search URLs.
 *
 * @param string[] $public_query_vars Public query vars.
 * @return string[]
 */
function developer_starter_search_query_vars( $public_query_vars ) {
    $public_query_vars[] = 'search_scope';

    return array_values( array_unique( $public_query_vars ) );
}
add_filter( 'query_vars', 'developer_starter_search_query_vars' );

/**
 * 搜索伪静态功能
 * 将搜索链接格式从 /?s=关键词 改为 /search/关键词/
 */
function developer_starter_search_rewrite_init() {
    if ( ! developer_starter_get_option( 'search_rewrite', '' ) ) {
        return;
    }

    add_rewrite_rule(
        '^search/(.+)/?$',
        'index.php?s=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^search/(.+)/page/([0-9]+)/?$',
        'index.php?s=$matches[1]&paged=$matches[2]',
        'top'
    );
}
add_action( 'init', 'developer_starter_search_rewrite_init', 10 );

/**
 * 搜索重定向兼容函数（保留旧钩子，不再执行 301 额外跳转）
 */
function developer_starter_search_redirect() {
    return;
}
add_action( 'template_redirect', 'developer_starter_search_redirect', 1 );

/**
 * 修改搜索表单的 action 和链接格式
 */
function developer_starter_search_form_filter( $form ) {
    if ( false === strpos( $form, 'name="qiling_search_mode"' ) && false === strpos( $form, "name='qiling_search_mode'" ) ) {
        $hidden = '<input type="hidden" name="qiling_search_mode" value="' . esc_attr( developer_starter_get_search_mode_form_value() ) . '" />';
        $form   = preg_replace( '/<\/form>/i', $hidden . '</form>', $form, 1 );
    }

    if ( developer_starter_get_option( 'search_rewrite', '' ) ) {
        $action = esc_url( developer_starter_get_search_form_action_url() );
        $replacement = 'action="' . $action . '" onsubmit="return dsSearchRedirect(this);"';
        $form = preg_replace( '/action=["\'][^"\']*["\']/i', $replacement, $form );

        if ( strpos( $form, 'onsubmit=' ) === false ) {
            $form = preg_replace( '/<form\b/i', '<form onsubmit="return dsSearchRedirect(this);"', $form, 1 );
        }
    }

    return $form;
}
add_filter( 'get_search_form', 'developer_starter_search_form_filter', 20 );

/**
 * 输出搜索表单重定向 JavaScript
 */
function developer_starter_search_js() {
    if ( ! developer_starter_get_option( 'search_rewrite', '' ) ) {
        return;
    }
    $search_base_url = trailingslashit( developer_starter_get_search_form_action_url() );
    ?>
    <script>
    function dsSearchRedirect(form) {
        var input = form.querySelector('input[name="s"]');
        if (input && input.value.trim() !== '') {
            var query = '';
            if (window.FormData && window.URLSearchParams) {
                var params = new URLSearchParams(new FormData(form));
                params.delete('s');
                query = params.toString();
            }
            window.location.href = '<?php echo esc_url( $search_base_url ); ?>' + encodeURIComponent(input.value.trim()) + '/' + (query ? '?' + query : '');
            return false;
        }
        return false;
    }
    </script>
    <?php
}
add_action( 'wp_footer', 'developer_starter_search_js', 1 );

/**
 * 修改搜索链接生成
 */
function developer_starter_search_link( $link, $search ) {
    if ( developer_starter_get_option( 'search_rewrite', '' ) ) {
        return developer_starter_get_search_pretty_url( $search );
    }
    return $link;
}
add_filter( 'search_link', 'developer_starter_search_link', 10, 2 );

/**
 * 当设置更改时刷新固定链接规则
 */
function developer_starter_search_rewrite_flush( $old_value, $new_value ) {
    $old_search = isset( $old_value['search_rewrite'] ) ? $old_value['search_rewrite'] : '';
    $new_search = isset( $new_value['search_rewrite'] ) ? $new_value['search_rewrite'] : '';

    if ( $old_search !== $new_search ) {
        update_option( 'developer_starter_flush_rewrite', '1' );
    }
}
add_action( 'update_option_developer_starter_options', 'developer_starter_search_rewrite_flush', 10, 2 );

/**
 * 是否启用游客请求限流
 *
 * @return bool
 */
function developer_starter_is_public_rate_limit_enabled() {
    return (bool) developer_starter_get_option( 'request_rate_limit_enable', '' );
}

/**
 * 获取限流窗口秒数
 *
 * @return int
 */
function developer_starter_get_rate_limit_window() {
    $window = intval( developer_starter_get_option( 'request_rate_limit_window', 60 ) );
    return max( 10, min( 3600, $window ) );
}

/**
 * 判定是否可信爬虫
 *
 * @return bool
 */
function developer_starter_is_trusted_bot_request() {
    $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
    if ( $ua === '' ) {
        return false;
    }
    $bots = array(
        'googlebot',
        'bingbot',
        'baiduspider',
        'bytespider',
        'yandexbot',
        'duckduckbot',
    );
    foreach ( $bots as $bot ) {
        if ( strpos( $ua, $bot ) !== false ) {
            return true;
        }
    }
    return false;
}

/**
 * 统一限流命中判断（同 IP + UA）
 *
 * @param string $scope 作用域
 * @param int    $max_requests 最大请求次数
 * @param int    $window_seconds 时间窗口（秒）
 * @return bool
 */
function developer_starter_is_rate_limited( $scope, $max_requests, $window_seconds ) {
    $scope = sanitize_key( (string) $scope );
    $max_requests = max( 1, intval( $max_requests ) );
    $window_seconds = max( 10, intval( $window_seconds ) );
    $ip = developer_starter_get_client_ip();
    $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

    $key_seed = $scope . '|' . $ip . '|' . substr( $ua, 0, 80 );
    $key = 'ds_rl_' . md5( $key_seed );
    $payload = get_transient( $key );
    if ( ! is_array( $payload ) ) {
        $payload = array(
            'count' => 0,
        );
    }

    $count = isset( $payload['count'] ) ? intval( $payload['count'] ) : 0;
    if ( $count >= $max_requests ) {
        return true;
    }

    $payload['count'] = $count + 1;
    set_transient( $key, $payload, $window_seconds );

    return false;
}

/**
 * 获取公共 AJAX 限流配置。
 *
 * @param string   $scope 作用域。
 * @param int      $default_max_requests 默认窗口请求数。
 * @param int|null $default_window_seconds 默认窗口秒数。
 * @return array<string,mixed>
 */
function developer_starter_get_public_ajax_rate_limit_config( $scope, $default_max_requests = 60, $default_window_seconds = null ) {
    $scope = sanitize_key( (string) $scope );
    $window = null === $default_window_seconds ? developer_starter_get_rate_limit_window() : intval( $default_window_seconds );

    $config = array(
        'enabled'               => true,
        'max_requests'          => $default_max_requests,
        'window_seconds'        => $window,
        'include_authenticated' => false,
    );

    /**
     * 过滤公共 AJAX 限流配置。
     *
     * @param array<string,mixed> $config 限流配置。
     * @param string              $scope  作用域。
     */
    $config = apply_filters( 'developer_starter_public_ajax_rate_limit_config', $config, $scope );
    if ( ! is_array( $config ) ) {
        $config = array();
    }

    $config['enabled'] = array_key_exists( 'enabled', $config ) ? (bool) $config['enabled'] : true;
    $config['max_requests'] = max( 1, min( 1000, intval( isset( $config['max_requests'] ) ? $config['max_requests'] : $default_max_requests ) ) );
    $config['window_seconds'] = max( 10, min( 3600, intval( isset( $config['window_seconds'] ) ? $config['window_seconds'] : $window ) ) );
    $config['include_authenticated'] = ! empty( $config['include_authenticated'] );

    return $config;
}

/**
 * 统一判断公共 AJAX 请求是否命中限流。
 *
 * 默认只限制未登录请求，避免影响已登录用户的正常后台/前台操作。
 *
 * @param string   $scope 作用域。
 * @param int      $default_max_requests 默认窗口请求数。
 * @param int|null $default_window_seconds 默认窗口秒数。
 * @return bool
 */
function developer_starter_is_public_ajax_rate_limited( $scope, $default_max_requests = 60, $default_window_seconds = null ) {
    $scope = sanitize_key( (string) $scope );
    if ( '' === $scope ) {
        $scope = 'default';
    }

    $config = developer_starter_get_public_ajax_rate_limit_config( $scope, $default_max_requests, $default_window_seconds );
    $enabled = (bool) apply_filters( 'developer_starter_public_ajax_rate_limit_enabled', $config['enabled'], $scope, $config );
    if ( ! $enabled ) {
        return false;
    }

    if ( is_user_logged_in() && empty( $config['include_authenticated'] ) ) {
        return false;
    }

    return developer_starter_is_rate_limited(
        'public_ajax_' . $scope,
        $config['max_requests'],
        $config['window_seconds']
    );
}

/**
 * 输出统一公共 AJAX 限流 JSON 响应。
 *
 * @param string $message 提示文案。
 * @return void
 */
function developer_starter_send_public_ajax_rate_limited( $message = '' ) {
    if ( '' === $message ) {
        $message = __( '请求过于频繁，请稍后再试', 'developer-starter' );
    }

    nocache_headers();
    wp_send_json_error(
        array(
            'message' => $message,
            'code'    => 'rate_limited',
        ),
        429
    );
}

/**
 * 输出统一公共 AJAX 限流非 JSON 响应。
 *
 * @param string $message 提示文案。
 * @return void
 */
function developer_starter_die_public_ajax_rate_limited( $message = '' ) {
    if ( '' === $message ) {
        $message = __( '请求过于频繁，请稍后再试', 'developer-starter' );
    }

    status_header( 429 );
    nocache_headers();
    wp_die( esc_html( $message ), esc_html__( 'Too Many Requests', 'developer-starter' ), array( 'response' => 429 ) );
}

/**
 * 前台搜索限流（仅游客）
 */
function developer_starter_guard_public_search_rate_limit() {
    if ( is_admin() || ! is_search() || is_user_logged_in() || ! developer_starter_is_public_rate_limit_enabled() ) {
        return;
    }
    if ( developer_starter_is_trusted_bot_request() ) {
        return;
    }

    $window = developer_starter_get_rate_limit_window();
    $search_max = intval( developer_starter_get_option( 'request_rate_limit_search_max', 30 ) );
    $search_max = max( 1, min( 500, $search_max ) );

    if ( developer_starter_is_rate_limited( 'public_search', $search_max, $window ) ) {
        status_header( 429 );
        nocache_headers();
        wp_die( esc_html__( '请求过于频繁，请稍后再试。', 'developer-starter' ), 'Too Many Requests', array( 'response' => 429 ) );
    }
}
add_action( 'template_redirect', 'developer_starter_guard_public_search_rate_limit', 0 );

/**
 * 在 init 时刷新固定链接规则（如果需要）
 */
function developer_starter_maybe_flush_rewrite() {
    if ( get_option( 'developer_starter_flush_rewrite', '' ) === '1' ) {
        flush_rewrite_rules();
        delete_option( 'developer_starter_flush_rewrite' );
    }
}
add_action( 'init', 'developer_starter_maybe_flush_rewrite', 999 );

/**
 * 搜索范围收敛：默认仅检索高价值内容类型
 *
 * @param WP_Query $query 查询对象
 * @return string[]
 */
function developer_starter_get_scoped_search_post_types( $query ) {
    $public_searchable = get_post_types(
        array(
            'public'              => true,
            'exclude_from_search' => false,
        ),
        'names'
    );

    $preferred = array( 'post', 'page' );
    $scoped = array_values( array_intersect( $preferred, $public_searchable ) );

    if ( empty( $scoped ) ) {
        $scoped = array_values( (array) $public_searchable );
    }

    $scoped = (array) apply_filters( 'developer_starter_search_post_types', $scoped, $query, $public_searchable );
    $scoped = array_values( array_unique( array_filter( array_map( 'sanitize_key', $scoped ) ) ) );

    return $scoped;
}

/**
 * 获取主搜索的匹配字段范围。
 *
 * 默认匹配标题、摘要、正文和标签；前台可通过 search_scope 收敛到标题、正文或标签。
 *
 * @param WP_Query|null $query 查询对象
 * @return string[]
 */
function developer_starter_get_search_match_fields( $query = null ) {
    $scope = 'all';
    if ( $query instanceof WP_Query ) {
        $scope = developer_starter_normalize_search_scope( (string) $query->get( 'developer_starter_search_scope' ) );
    } elseif ( is_search() ) {
        $scope = developer_starter_get_current_search_scope();
    }

    switch ( $scope ) {
        case 'title':
            $fields = array( 'title' );
            break;
        case 'content':
            $fields = array( 'content' );
            break;
        case 'tag':
            $fields = array( 'tag' );
            break;
        case 'all':
        default:
            $fields = array( 'title', 'excerpt', 'content', 'tag' );
            break;
    }

    $fields = (array) apply_filters( 'developer_starter_search_match_fields', $fields, $query, $scope );
    $fields = array_values( array_unique( array_filter( array_map( 'sanitize_key', $fields ) ) ) );

    return $fields;
}

/**
 * Build a SQL condition for post tag matches.
 *
 * @param string $term Search term.
 * @return string
 */
function developer_starter_get_search_tag_match_sql( $term ) {
    global $wpdb;

    $like = '%' . $wpdb->esc_like( $term ) . '%';

    return $wpdb->prepare(
        "{$wpdb->posts}.ID IN (
            SELECT tr.object_id
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
            WHERE tt.taxonomy = 'post_tag'
            AND (t.name LIKE %s OR t.slug LIKE %s)
        )",
        $like,
        $like
    );
}

/**
 * 解析搜索关键词，尽量保留短语搜索语义。
 *
 * @param string $search_term 原始关键词
 * @return string[]
 */
function developer_starter_get_search_query_terms( $search_term ) {
    $search_term = trim( wp_strip_all_tags( (string) $search_term ) );
    if ( '' === $search_term ) {
        return array();
    }

    $terms = array();
    if ( preg_match_all( '/"([^"]+)"|\'([^\']+)\'|(\\S+)/u', $search_term, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $term = '';
            if ( ! empty( $match[1] ) ) {
                $term = $match[1];
            } elseif ( ! empty( $match[2] ) ) {
                $term = $match[2];
            } elseif ( ! empty( $match[3] ) ) {
                $term = $match[3];
            }

            $term = trim( wp_strip_all_tags( (string) $term ) );
            if ( '' !== $term ) {
                $terms[] = $term;
            }
        }
    }

    if ( empty( $terms ) ) {
        $terms[] = $search_term;
    }

    $terms = array_values( array_unique( $terms ) );

    return (array) apply_filters( 'developer_starter_search_terms', $terms, $search_term );
}

/**
 * Highlight search terms in escaped display text.
 *
 * @param string        $text Text to render.
 * @param string[]|null $terms Search terms.
 * @return string
 */
function developer_starter_highlight_search_terms( $text, $terms = null ) {
    $text = wp_strip_all_tags( (string) $text );
    if ( '' === $text ) {
        return '';
    }

    $terms = is_array( $terms ) ? $terms : developer_starter_get_search_query_terms( get_search_query( false ) );
    $terms = array_values( array_filter( array_unique( array_map( 'wp_strip_all_tags', $terms ) ) ) );
    usort(
        $terms,
        function( $a, $b ) {
            return strlen( (string) $b ) - strlen( (string) $a );
        }
    );

    $escaped = esc_html( $text );
    $patterns = array();
    foreach ( $terms as $term ) {
        $term = trim( (string) $term );
        if ( '' === $term ) {
            continue;
        }

        $patterns[] = preg_quote( esc_html( $term ), '/' );
    }

    if ( empty( $patterns ) ) {
        return $escaped;
    }

    $highlighted = preg_replace( '/(' . implode( '|', $patterns ) . ')/iu', '<mark class="search-highlight">$1</mark>', $escaped );

    return is_string( $highlighted ) ? $highlighted : $escaped;
}

/**
 * 生成主搜索查询缓存键
 *
 * @param WP_Query $query 查询对象
 * @return string
 */
function developer_starter_build_search_cache_key( $query ) {
    if ( ! ( $query instanceof WP_Query ) ) {
        return '';
    }

    $seed = array(
        'ctx'            => 'main_search',
        'blog_id'        => get_current_blog_id(),
        's'              => trim( wp_strip_all_tags( (string) $query->get( 's' ) ) ),
        'engine'         => 'scoped_search_v3',
        'scope'          => developer_starter_normalize_search_scope( (string) $query->get( 'developer_starter_search_scope' ) ),
        'search_mode'    => sanitize_key( (string) $query->get( 'developer_starter_search_mode' ) ),
        'fields'         => developer_starter_get_search_match_fields( $query ),
        'paged'          => max( 1, (int) $query->get( 'paged' ) ),
        'posts_per_page' => (int) $query->get( 'posts_per_page' ),
        'post_type'      => $query->get( 'post_type' ),
        'post_status'    => $query->get( 'post_status' ),
        'orderby'        => (string) $query->get( 'orderby' ),
        'order'          => (string) $query->get( 'order' ),
        'version'        => developer_starter_get_cache_version_stamp( array( 'content', 'settings' ) ),
    );

    $seed = developer_starter_sort_recursive( $seed );
    $seed = apply_filters( 'developer_starter_search_cache_seed', $seed, $query );

    return 'ds_sch_' . md5( wp_json_encode( $seed ) );
}

/**
 * 读取搜索缓存
 *
 * @param string $cache_key 缓存键
 * @return array|false
 */
function developer_starter_get_search_cache_payload( $cache_key ) {
    if ( $cache_key === '' ) {
        return false;
    }
    if ( developer_starter_should_bypass_content_cache( 'search:' . (string) $cache_key ) ) {
        return false;
    }

    $group = 'developer_starter_search';
    $payload = developer_starter_cache_fetch( $cache_key, $group );

    return is_array( $payload ) ? $payload : false;
}

/**
 * 写入搜索缓存
 *
 * @param string $cache_key 缓存键
 * @param array  $payload 缓存内容
 * @param int    $ttl 过期时间
 * @return void
 */
function developer_starter_set_search_cache_payload( $cache_key, $payload, $ttl ) {
    if ( $cache_key === '' || ! is_array( $payload ) ) {
        return;
    }
    if ( developer_starter_should_bypass_content_cache( 'search:' . (string) $cache_key ) ) {
        return;
    }

    $group = 'developer_starter_search';
    developer_starter_cache_store( $cache_key, $payload, $ttl, $group );
}

/**
 * 前台主搜索查询优化：范围收敛 + 缓存装载 + 启用权重排序
 *
 * @param WP_Query $query 查询对象
 * @return void
 */
function developer_starter_optimize_main_search_query( $query ) {
    if ( ! ( $query instanceof WP_Query ) || is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
        return;
    }

    $scope = developer_starter_get_current_search_scope();
    $query->set( 'search_scope', $scope );
    $query->set( 'developer_starter_search_scope', $scope );

    if ( ! $query->get( 'post_type' ) ) {
        $scoped_types = developer_starter_get_scoped_search_post_types( $query );
        if ( ! empty( $scoped_types ) ) {
            $query->set( 'post_type', $scoped_types );
        }
    }

    if ( ! $query->get( 'post_status' ) ) {
        $query->set( 'post_status', 'publish' );
    }

    $query->set( 'ignore_sticky_posts', true );
    $query->set( 'developer_starter_search_weighted', 1 );

    $cache_enabled = (bool) developer_starter_get_option( 'query_cache_enable', '1' );
    $cache_enabled = (bool) apply_filters( 'developer_starter_search_cache_enable', $cache_enabled, $query );
    if ( ! $cache_enabled || is_user_logged_in() || developer_starter_has_logged_in_cookie_hint() ) {
        return;
    }

    $cache_key = developer_starter_build_search_cache_key( $query );
    $payload = developer_starter_get_search_cache_payload( $cache_key );
    if ( ! is_array( $payload ) || ! array_key_exists( 'ids', $payload ) ) {
        $query->set( 'developer_starter_search_cache_key', $cache_key );
        return;
    }

    $ids = array_values( array_filter( array_map( 'intval', (array) $payload['ids'] ) ) );
    if ( empty( $ids ) ) {
        $ids = array( 0 );
    }

    $query->set( 'post__in', $ids );
    $query->set( 'orderby', 'post__in' );
    $query->set( 'developer_starter_search_weighted', 0 );
    $query->set( 'developer_starter_search_cache_hit', 1 );
    $query->set( 'developer_starter_search_cache_found_posts', isset( $payload['found_posts'] ) ? (int) $payload['found_posts'] : count( $ids ) );
    $query->set( 'developer_starter_search_cache_max_num_pages', isset( $payload['max_num_pages'] ) ? (int) $payload['max_num_pages'] : 1 );
}
add_action( 'pre_get_posts', 'developer_starter_optimize_main_search_query', 20 );

/**
 * 主搜索查询 WHERE 优化。
 *
 * 命中缓存时直接移除 SQL 搜索条件；
 * 冷搜索时按 search_scope 生成字段条件，支持标题、正文和标签范围筛选。
 *
 * @param string   $search 搜索 SQL 片段
 * @param WP_Query $query 查询对象
 * @return string
 */
function developer_starter_skip_search_where_on_cache_hit( $search, $query ) {
    if ( ! ( $query instanceof WP_Query ) || is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
        return $search;
    }
    if ( $query->get( 'developer_starter_search_cache_hit' ) ) {
        return '';
    }

    $terms = developer_starter_get_search_query_terms( $query->get( 's' ) );
    if ( empty( $terms ) ) {
        return $search;
    }

    $match_fields = developer_starter_get_search_match_fields( $query );
    if ( empty( $match_fields ) ) {
        return $search;
    }

    global $wpdb;
    $term_groups = array();

    foreach ( $terms as $term ) {
        $like = '%' . $wpdb->esc_like( $term ) . '%';
        $group = array();

        if ( in_array( 'title', $match_fields, true ) ) {
            $group[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
        }

        if ( in_array( 'excerpt', $match_fields, true ) ) {
            $group[] = $wpdb->prepare( "{$wpdb->posts}.post_excerpt LIKE %s", $like );
        }

        if ( in_array( 'content', $match_fields, true ) ) {
            $group[] = $wpdb->prepare( "{$wpdb->posts}.post_content LIKE %s", $like );
        }

        if ( in_array( 'tag', $match_fields, true ) ) {
            $group[] = developer_starter_get_search_tag_match_sql( $term );
        }

        if ( ! empty( $group ) ) {
            $term_groups[] = '(' . implode( ' OR ', $group ) . ')';
        }
    }

    if ( empty( $term_groups ) ) {
        return $search;
    }

    $password_sql = is_user_logged_in() ? '' : " AND ({$wpdb->posts}.post_password = '')";

    return ' AND ' . implode( ' AND ', $term_groups ) . $password_sql;
}
add_filter( 'posts_search', 'developer_starter_skip_search_where_on_cache_hit', 20, 2 );

/**
 * 主搜索查询权重排序
 *
 * @param array    $clauses SQL 子句
 * @param WP_Query $query 查询对象
 * @return array
 */
function developer_starter_search_weighted_posts_clauses( $clauses, $query ) {
    if ( ! ( $query instanceof WP_Query ) || is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
        return $clauses;
    }
    if ( ! $query->get( 'developer_starter_search_weighted' ) || $query->get( 'developer_starter_search_cache_hit' ) ) {
        return $clauses;
    }

    $search_term = trim( wp_strip_all_tags( (string) $query->get( 's' ) ) );
    if ( $search_term === '' ) {
        return $clauses;
    }

    global $wpdb;
    $match_fields = developer_starter_get_search_match_fields( $query );
    $exact_title = $wpdb->prepare( "{$wpdb->posts}.post_title = %s", $search_term );
    $prefix_title = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $wpdb->esc_like( $search_term ) . '%' );
    $contains_title = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like( $search_term ) . '%' );
    $contains_excerpt = $wpdb->prepare( "{$wpdb->posts}.post_excerpt LIKE %s", '%' . $wpdb->esc_like( $search_term ) . '%' );

    $score_parts = array();

    if ( in_array( 'title', $match_fields, true ) ) {
        $score_parts[] = "WHEN {$exact_title} THEN 220";
        $score_parts[] = "WHEN {$prefix_title} THEN 160";
        $score_parts[] = "WHEN {$contains_title} THEN 110";
    }

    if ( in_array( 'excerpt', $match_fields, true ) ) {
        $score_parts[] = "WHEN {$contains_excerpt} THEN 60";
    }

    if ( in_array( 'content', $match_fields, true ) ) {
        $contains_content = $wpdb->prepare( "{$wpdb->posts}.post_content LIKE %s", '%' . $wpdb->esc_like( $search_term ) . '%' );
        $score_parts[] = "WHEN {$contains_content} THEN 35";
    }

    if ( in_array( 'tag', $match_fields, true ) ) {
        $tag_match_sql = developer_starter_get_search_tag_match_sql( $search_term );
        $score_parts[] = "WHEN {$tag_match_sql} THEN 80";
    }

    if ( empty( $score_parts ) ) {
        return $clauses;
    }

    $score_sql = "(CASE
        " . implode( "\n        ", $score_parts ) . "
        ELSE 0
    END) + (CASE
        WHEN {$wpdb->posts}.post_type = 'post' THEN 12
        WHEN {$wpdb->posts}.post_type = 'page' THEN 6
        ELSE 0
    END)";

    if ( strpos( $clauses['fields'], 'ds_search_score' ) === false ) {
        $clauses['fields'] .= ", {$score_sql} AS ds_search_score";
    }
    $clauses['orderby'] = "ds_search_score DESC, {$wpdb->posts}.post_date DESC";

    return $clauses;
}
add_filter( 'posts_clauses', 'developer_starter_search_weighted_posts_clauses', 20, 2 );

/**
 * 写入主搜索查询缓存
 *
 * @param WP_Post[] $posts 查询结果
 * @param WP_Query  $query 查询对象
 * @return WP_Post[]
 */
function developer_starter_store_main_search_cache( $posts, $query ) {
    if ( ! ( $query instanceof WP_Query ) || is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
        return $posts;
    }
    if ( $query->get( 'developer_starter_search_cache_hit' ) ) {
        return $posts;
    }

    $cache_key = (string) $query->get( 'developer_starter_search_cache_key' );
    if ( $cache_key === '' ) {
        return $posts;
    }

    $ids = array();
    foreach ( (array) $posts as $post_obj ) {
        if ( is_object( $post_obj ) && isset( $post_obj->ID ) ) {
            $ids[] = (int) $post_obj->ID;
        }
    }

    $payload = array(
        'ids'           => $ids,
        'found_posts'   => (int) $query->found_posts,
        'max_num_pages' => (int) $query->max_num_pages,
        'cached_at'     => time(),
    );
    $payload = (array) apply_filters( 'developer_starter_search_cache_store_payload', $payload, $query, $posts );

    $ttl = developer_starter_get_query_cache_ttl();
    $ttl = (int) apply_filters( 'developer_starter_search_cache_ttl', $ttl, $query, $payload );
    developer_starter_set_search_cache_payload( $cache_key, $payload, $ttl );

    return $posts;
}
add_filter( 'the_posts', 'developer_starter_store_main_search_cache', 20, 2 );

/**
 * 命中缓存时恢复分页总数
 *
 * @param int      $found_posts 查询总数
 * @param WP_Query $query 查询对象
 * @return int
 */
function developer_starter_restore_search_found_posts_from_cache( $found_posts, $query ) {
    if ( ! ( $query instanceof WP_Query ) || ! $query->get( 'developer_starter_search_cache_hit' ) ) {
        return $found_posts;
    }
    return max( 0, (int) $query->get( 'developer_starter_search_cache_found_posts' ) );
}
add_filter( 'found_posts', 'developer_starter_restore_search_found_posts_from_cache', 20, 2 );
