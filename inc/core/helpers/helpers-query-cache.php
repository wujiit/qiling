<?php
/**
 * Query and fragment cache helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 递归排序数组，用于生成稳定缓存键
 *
 * @param mixed $value 输入值
 * @return mixed
 */
function developer_starter_sort_recursive( $value ) {
    if ( ! is_array( $value ) ) {
        return $value;
    }
    foreach ( $value as $k => $v ) {
        $value[ $k ] = developer_starter_sort_recursive( $v );
    }
    ksort( $value );
    return $value;
}

/**
 * 获取当前请求的缓存上下文。
 *
 * 用于统一区分前后台、登录态、Ajax/REST 等缓存安全边界。
 *
 * @return array<string,mixed>
 */
function developer_starter_get_cache_request_context() {
    static $context = null;

    if ( null !== $context ) {
        return $context;
    }

    $cookie_hint = function_exists( 'developer_starter_has_logged_in_cookie_hint' )
        ? developer_starter_has_logged_in_cookie_hint()
        : false;
    $logged_in = function_exists( 'is_user_logged_in' ) ? is_user_logged_in() : false;
    $user_id = ( $logged_in && function_exists( 'get_current_user_id' ) ) ? (int) get_current_user_id() : 0;
    $surface = 'frontend';

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        $surface = 'cli';
    } elseif ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
        $surface = 'cron';
    } elseif ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
        $surface = 'ajax';
    } elseif ( ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        $surface = 'rest';
    } elseif ( is_admin() ) {
        $surface = 'admin';
    }

    $context = array(
        'surface'           => $surface,
        'cookie_hint'       => (bool) $cookie_hint,
        'is_user_logged_in' => (bool) $logged_in,
        'has_login_hint'    => (bool) ( $logged_in || $cookie_hint ),
        'user_id'           => $user_id,
        'blog_id'           => function_exists( 'get_current_blog_id' ) ? (int) get_current_blog_id() : 1,
    );

    return $context;
}

/**
 * 标准化缓存策略参数。
 *
 * audience:
 * - public: 仅游客可用，严禁复用于登录请求
 * - user:   按用户隔离，仅在已登录且 user_id 可确定时使用
 * - system: 与登录态无关，适用于锁、限流、外部数据等基础设施缓存
 *
 * @param array<string,mixed> $args 原始策略参数。
 * @return array<string,mixed>
 */
function developer_starter_normalize_cache_policy_args( $args = array() ) {
    $args = wp_parse_args(
        is_array( $args ) ? $args : array(),
        array(
            'audience'               => 'public',
            'surface'                => 'any',
            'group'                  => 'default',
            'scope'                  => '',
            'user_id'                => 0,
            'segment'                => '',
            'version_groups'         => array(),
            'blog_scoped'            => false,
            'respect_content_bypass' => false,
        )
    );

    $args['audience'] = sanitize_key( (string) $args['audience'] );
    if ( ! in_array( $args['audience'], array( 'public', 'user', 'system' ), true ) ) {
        $args['audience'] = 'public';
    }

    if ( is_array( $args['surface'] ) ) {
        $args['surface'] = array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_key', $args['surface'] )
                )
            )
        );
    } else {
        $args['surface'] = sanitize_key( (string) $args['surface'] );
        if ( '' === $args['surface'] ) {
            $args['surface'] = 'any';
        }
    }

    $args['group'] = sanitize_key( (string) $args['group'] );
    if ( '' === $args['group'] ) {
        $args['group'] = 'default';
    }

    $args['scope'] = (string) $args['scope'];
    $args['user_id'] = absint( $args['user_id'] );
    $args['segment'] = trim( (string) $args['segment'] );
    $args['version_groups'] = is_array( $args['version_groups'] ) ? $args['version_groups'] : array();
    $args['blog_scoped'] = (bool) $args['blog_scoped'];
    $args['respect_content_bypass'] = (bool) $args['respect_content_bypass'];

    return $args;
}

/**
 * 判断当前请求是否允许使用指定缓存策略。
 *
 * @param array<string,mixed> $args 缓存策略。
 * @return bool
 */
function developer_starter_should_use_cache_policy( $args = array() ) {
    $args = developer_starter_normalize_cache_policy_args( $args );
    $context = developer_starter_get_cache_request_context();
    $surface = $args['surface'];

    if ( is_array( $surface ) ) {
        if ( ! empty( $surface ) && ! in_array( 'any', $surface, true ) && ! in_array( $context['surface'], $surface, true ) ) {
            return false;
        }
    } elseif ( 'any' !== $surface && $surface !== $context['surface'] ) {
        return false;
    }

    if ( $args['respect_content_bypass'] && function_exists( 'developer_starter_should_bypass_content_cache' ) ) {
        if ( developer_starter_should_bypass_content_cache( $args['scope'] ) ) {
            return false;
        }
    }

    switch ( $args['audience'] ) {
        case 'system':
            return true;

        case 'user':
            $target_user_id = $args['user_id'] > 0 ? $args['user_id'] : (int) $context['user_id'];
            return $target_user_id > 0;

        case 'public':
        default:
            return ! $context['has_login_hint'];
    }
}

/**
 * 生成统一缓存版本签名。
 *
 * @param array<int|string,mixed> $groups 版本分组列表。
 * @return string
 */
function developer_starter_get_cache_version_stamp( $groups = array() ) {
    $groups = is_array( $groups ) ? $groups : array( $groups );
    $normalized_groups = array_values(
        array_unique(
            array_filter(
                array_map( 'sanitize_key', $groups )
            )
        )
    );

    if ( empty( $normalized_groups ) ) {
        return '';
    }

    $versions = array();
    foreach ( $normalized_groups as $group ) {
        $versions[ $group ] = developer_starter_get_cache_version( $group );
    }

    return md5( wp_json_encode( $versions ) );
}

/**
 * 根据缓存策略生成最终缓存键。
 *
 * @param string              $key  原始缓存键。
 * @param array<string,mixed> $args 缓存策略。
 * @return string
 */
function developer_starter_resolve_cache_policy_key( $key, $args = array() ) {
    $key = (string) $key;
    if ( '' === $key ) {
        return '';
    }

    $args = developer_starter_normalize_cache_policy_args( $args );
    $context = developer_starter_get_cache_request_context();
    $segments = array( $key );

    if ( $args['blog_scoped'] ) {
        $segments[] = 'blog:' . (int) $context['blog_id'];
    }

    if ( 'user' === $args['audience'] ) {
        $target_user_id = $args['user_id'] > 0 ? $args['user_id'] : (int) $context['user_id'];
        $segments[] = 'uid:' . $target_user_id;
    }

    if ( '' !== $args['segment'] ) {
        $segments[] = 'seg:' . sanitize_key( $args['segment'] );
    }

    if ( ! empty( $args['version_groups'] ) ) {
        $version_stamp = developer_starter_get_cache_version_stamp( $args['version_groups'] );
        if ( '' !== $version_stamp ) {
            $segments[] = 'ver:' . $version_stamp;
        }
    }

    $resolved = implode( '|', $segments );

    if ( strlen( $resolved ) > 172 ) {
        return 'ds_ctx_' . md5( $resolved );
    }

    return $resolved;
}

/**
 * 通用缓存读取入口。
 *
 * @param string              $key  原始缓存键。
 * @param array<string,mixed> $args 缓存策略。
 * @return mixed
 */
function developer_starter_cache_read( $key, $args = array() ) {
    $key = developer_starter_resolve_cache_policy_key( $key, $args );
    if ( '' === $key || ! developer_starter_should_use_cache_policy( $args ) ) {
        return false;
    }

    $args = developer_starter_normalize_cache_policy_args( $args );
    if ( wp_using_ext_object_cache() ) {
        return wp_cache_get( $key, $args['group'] );
    }

    return get_transient( $key );
}

/**
 * 通用缓存写入入口。
 *
 * @param string              $key   原始缓存键。
 * @param mixed               $value 缓存值。
 * @param int                 $ttl   过期秒数。
 * @param array<string,mixed> $args  缓存策略。
 * @return bool
 */
function developer_starter_cache_write( $key, $value, $ttl, $args = array() ) {
    $ttl = max( 1, (int) $ttl );
    $key = developer_starter_resolve_cache_policy_key( $key, $args );
    if ( '' === $key || ! developer_starter_should_use_cache_policy( $args ) ) {
        return false;
    }

    $args = developer_starter_normalize_cache_policy_args( $args );
    if ( wp_using_ext_object_cache() ) {
        return (bool) wp_cache_set( $key, $value, $args['group'], $ttl );
    }

    return (bool) set_transient( $key, $value, $ttl );
}

/**
 * 通用缓存删除入口。
 *
 * @param string              $key  原始缓存键。
 * @param array<string,mixed> $args 缓存策略。
 * @return bool
 */
function developer_starter_cache_delete( $key, $args = array() ) {
    $key = developer_starter_resolve_cache_policy_key( $key, $args );
    if ( '' === $key ) {
        return false;
    }

    $args = developer_starter_normalize_cache_policy_args( $args );
    if ( wp_using_ext_object_cache() ) {
        return (bool) wp_cache_delete( $key, $args['group'] );
    }

    return (bool) delete_transient( $key );
}

/**
 * 获取缓存版本号（用于精准失效）
 *
 * @param string $group 缓存分组
 * @return string
 */
function developer_starter_get_cache_version( $group = 'content' ) {
    $key = 'developer_starter_cache_ver_' . sanitize_key( $group );
    $ver = get_option( $key, '' );
    if ( ! is_string( $ver ) || $ver === '' ) {
        $ver = (string) microtime( true );
        update_option( $key, $ver, false );
    }
    return $ver;
}

/**
 * 更新缓存版本号
 *
 * @param string $group 缓存分组
 * @return void
 */
function developer_starter_bump_cache_version( $group = 'content' ) {
    $key = 'developer_starter_cache_ver_' . sanitize_key( $group );
    update_option( $key, (string) microtime( true ), false );
}

/**
 * 内容变化时刷新缓存版本
 *
 * @param int $post_id 文章ID
 * @return void
 */
function developer_starter_bump_content_cache_version( $post_id = 0 ) {
    if ( $post_id && ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) ) {
        return;
    }
    developer_starter_bump_cache_version( 'content' );
}

add_action( 'save_post', 'developer_starter_bump_content_cache_version', 20 );
add_action( 'deleted_post', 'developer_starter_bump_content_cache_version', 20 );
add_action( 'created_term', 'developer_starter_bump_content_cache_version', 20 );
add_action( 'edited_term', 'developer_starter_bump_content_cache_version', 20 );
add_action( 'delete_term', 'developer_starter_bump_content_cache_version', 20 );
add_action( 'comment_post', 'developer_starter_bump_content_cache_version', 20 );
add_action( 'edit_comment', 'developer_starter_bump_content_cache_version', 20 );
add_action( 'delete_comment', 'developer_starter_bump_content_cache_version', 20 );

/**
 * 主题设置变化时刷新缓存版本
 *
 * @return void
 */
function developer_starter_bump_settings_cache_version() {
    developer_starter_bump_cache_version( 'settings' );
}

add_action( 'update_option_developer_starter_options', 'developer_starter_bump_settings_cache_version', 20 );

/**
 * 获取查询缓存 TTL
 *
 * @return int
 */
function developer_starter_get_query_cache_ttl() {
    $ttl = (int) developer_starter_get_option( 'query_cache_ttl', 300 );
    if ( $ttl < 30 ) {
        $ttl = 30;
    }
    if ( $ttl > DAY_IN_SECONDS ) {
        $ttl = DAY_IN_SECONDS;
    }
    return (int) apply_filters( 'developer_starter_query_cache_ttl', $ttl );
}

/**
 * 获取片段缓存 TTL
 *
 * @return int
 */
function developer_starter_get_fragment_cache_ttl() {
    $ttl = (int) developer_starter_get_option( 'fragment_cache_ttl', 180 );
    if ( $ttl < 30 ) {
        $ttl = 30;
    }
    if ( $ttl > DAY_IN_SECONDS ) {
        $ttl = DAY_IN_SECONDS;
    }
    return (int) apply_filters( 'developer_starter_fragment_cache_ttl', $ttl );
}

/**
 * 判断当前缓存作用域是否属于游客公共相关文章。
 *
 * 单篇页正文可能包含登录态/付费态片段，因此默认不参与内容缓存；相关文章只输出公开文章卡片，
 * 可以在游客前台请求中单独放行，降低重复查询与片段渲染成本。
 *
 * @param string $scope_lower 小写后的缓存作用域。
 * @return bool
 */
function developer_starter_is_public_single_related_cache_scope( $scope_lower ) {
    $safe_prefixes = array(
        'fragment:single_related_',
        'query:single_related_',
        'related:single_related_',
    );
    $safe_prefixes = (array) apply_filters( 'developer_starter_public_single_related_cache_scope_prefixes', $safe_prefixes );

    foreach ( $safe_prefixes as $prefix ) {
        $prefix = strtolower( (string) $prefix );
        if ( '' !== $prefix && 0 === strpos( $scope_lower, $prefix ) ) {
            return true;
        }
    }

    return false;
}

/**
 * 当前请求是否应绕过主题内容缓存
 *
 * 当前策略：
 * - 已登录用户（含登录 Cookie 线索）全站绕过主题内容缓存；
 * - 游客在安全敏感作用域下绕过缓存。
 *
 * @param string $scope 缓存作用域标识
 * @return bool
 */
function developer_starter_should_bypass_content_cache( $scope = 'general' ) {
    $scope_raw = (string) $scope;
    $scope = strtolower( $scope_raw );
    $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
    $bypass = ! in_array( $method, array( 'GET', 'HEAD' ), true );
    $is_public_single_related_scope = developer_starter_is_public_single_related_cache_scope( $scope );

    // 文章详情页默认不参与主题内容缓存，避免登录态/付费态片段被游客缓存复用。
    if ( ! $bypass && is_singular( 'post' ) && ! $is_public_single_related_scope ) {
        $bypass = true;
    }

    // 已登录用户（含登录 Cookie 线索）全站禁止内容缓存，不区分作用域。
    if ( ! $bypass && ( is_user_logged_in() || developer_starter_has_logged_in_cookie_hint() ) ) {
        $bypass = true;
    }

    // 安全敏感作用域：所有用户都不走缓存。
    $always_bypass_tokens = array(
        'nonce',
        'token',
        'captcha',
        'verify',
        'otp',
        'sms',
        'oauth',
        'session',
    );

    if ( ! $bypass && $scope !== '' ) {
        foreach ( $always_bypass_tokens as $token ) {
            if ( strpos( $scope, $token ) !== false ) {
                $bypass = true;
                break;
            }
        }
    }

    return (bool) apply_filters( 'developer_starter_should_bypass_content_cache', $bypass, $scope_raw );
}

/**
 * 统一读取缓存（对象缓存优先；否则回退 transient）
 *
 * @param string $key   缓存键
 * @param string $group 缓存组
 * @return mixed
 */
function developer_starter_cache_fetch( $key, $group = 'default' ) {
    return developer_starter_cache_read(
        $key,
        array(
            'group'    => $group,
            'audience' => 'public',
            'surface'  => 'any',
        )
    );
}

/**
 * 统一写入缓存（对象缓存优先；否则回退 transient）
 *
 * @param string $key   缓存键
 * @param mixed  $value 缓存值
 * @param int    $ttl   过期秒数
 * @param string $group 缓存组
 * @return bool
 */
function developer_starter_cache_store( $key, $value, $ttl, $group = 'default' ) {
    return developer_starter_cache_write(
        $key,
        $value,
        $ttl,
        array(
            'group'    => $group,
            'audience' => 'public',
            'surface'  => 'any',
        )
    );
}

/**
 * 统一优化查询参数（低风险）
 *
 * @param array $args 查询参数
 * @param array $options 扩展选项
 * @return array
 */
function developer_starter_optimize_query_args( $args, $options = array() ) {
    $needs_pagination = ! empty( $options['needs_pagination'] );
    if ( ! developer_starter_get_option( 'query_optimize_enable', '' ) ) {
        return apply_filters( 'developer_starter_optimized_query_args', $args, $options );
    }

    if ( ! isset( $args['ignore_sticky_posts'] ) ) {
        $args['ignore_sticky_posts'] = true;
    }

    if ( ! $needs_pagination && ! isset( $args['no_found_rows'] ) ) {
        $args['no_found_rows'] = true;
    }

    return apply_filters( 'developer_starter_optimized_query_args', $args, $options );
}

/**
 * 统一补齐 WP_Query 在 the_post() 依赖的 query_vars 默认键
 *
 * @param WP_Query $query 查询对象
 * @param array    $args  原始查询参数
 * @return WP_Query
 */
function developer_starter_normalize_query_for_loop( $query, $args = array() ) {
    if ( ! ( $query instanceof WP_Query ) ) {
        return $query;
    }

    $query_vars = is_array( $query->query_vars ) ? $query->query_vars : array();
    if ( ! empty( $args ) && is_array( $args ) ) {
        $query_vars = wp_parse_args( $query_vars, $args );
    }

    if ( method_exists( $query, 'fill_query_vars' ) ) {
        $filled_vars = $query->fill_query_vars( $query_vars );
        if ( is_array( $filled_vars ) ) {
            $query_vars = $filled_vars;
        }
    }

    // 兜底关键键，兼容不同版本 WP 在严格错误级别下的数组键读取。
    $query_vars = wp_parse_args(
        $query_vars,
        array(
            'fields'                 => '',
            'update_post_term_cache' => true,
            'update_post_meta_cache' => true,
            'cache_results'          => true,
            'lazy_load_term_meta'    => true,
            'suppress_filters'       => false,
        )
    );

    $query->query_vars = $query_vars;
    if ( ! is_array( $query->query ) || empty( $query->query ) ) {
        $query->query = $query_vars;
    }

    return $query;
}

/**
 * 从查询结果中提取文章 ID 列表。
 *
 * @param mixed $posts 查询结果 posts 字段。
 * @return int[]
 */
function developer_starter_extract_query_post_ids( $posts ) {
    if ( ! is_array( $posts ) ) {
        return array();
    }

    $post_ids = array();
    foreach ( $posts as $post_item ) {
        if ( is_object( $post_item ) && isset( $post_item->ID ) ) {
            $post_ids[] = (int) $post_item->ID;
        } elseif ( is_numeric( $post_item ) ) {
            $post_ids[] = (int) $post_item;
        }
    }

    return array_values( array_filter( array_unique( array_map( 'intval', $post_ids ) ) ) );
}

/**
 * 预热查询结果依赖的文章/Meta/Term 缓存。
 *
 * @param int[] $post_ids 文章 ID 列表。
 * @param array $query_vars 查询参数。
 * @return void
 */
function developer_starter_prime_query_post_caches( $post_ids, $query_vars = array() ) {
    if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
        return;
    }

    $post_ids = array_values( array_filter( array_map( 'intval', $post_ids ) ) );
    if ( empty( $post_ids ) ) {
        return;
    }

    $query_vars = is_array( $query_vars ) ? $query_vars : array();
    $prime_meta_cache = ! isset( $query_vars['update_post_meta_cache'] ) || (bool) $query_vars['update_post_meta_cache'];
    $prime_term_cache = ! isset( $query_vars['update_post_term_cache'] ) || (bool) $query_vars['update_post_term_cache'];
    $post_type = isset( $query_vars['post_type'] ) ? $query_vars['post_type'] : 'post';
    if ( empty( $post_type ) ) {
        $post_type = 'post';
    }

    if ( function_exists( '_prime_post_caches' ) ) {
        _prime_post_caches( $post_ids, $prime_term_cache, $prime_meta_cache );
        return;
    }

    foreach ( $post_ids as $post_id ) {
        get_post( $post_id );
    }

    if ( $prime_meta_cache ) {
        update_postmeta_cache( $post_ids );
    }

    if ( $prime_term_cache ) {
        update_object_term_cache( $post_ids, $post_type );
    }
}

/**
 * 根据缓存载荷重建查询结果。
 *
 * @param array $payload 缓存载荷。
 * @param array $query_vars 查询参数。
 * @return array
 */
function developer_starter_restore_cached_query_posts( $payload, $query_vars = array() ) {
    if ( ! is_array( $payload ) ) {
        return array();
    }

    $fields = isset( $payload['fields'] )
        ? (string) $payload['fields']
        : ( isset( $query_vars['fields'] ) ? (string) $query_vars['fields'] : '' );

    if ( isset( $payload['post_ids'] ) && is_array( $payload['post_ids'] ) ) {
        $post_ids = array_values( array_filter( array_map( 'intval', $payload['post_ids'] ) ) );
        if ( 'ids' === $fields ) {
            return $post_ids;
        }

        developer_starter_prime_query_post_caches( $post_ids, $query_vars );

        $posts = array();
        foreach ( $post_ids as $post_id ) {
            $post_obj = get_post( $post_id );
            if ( $post_obj instanceof WP_Post ) {
                $posts[] = $post_obj;
            }
        }

        return $posts;
    }

    $posts = isset( $payload['posts'] ) && is_array( $payload['posts'] ) ? $payload['posts'] : array();
    if ( empty( $posts ) ) {
        return array();
    }

    if ( '' === $fields || 'all' === $fields ) {
        developer_starter_prime_query_post_caches(
            developer_starter_extract_query_post_ids( $posts ),
            $query_vars
        );
    }

    return $posts;
}

/**
 * 运行带缓存的 WP_Query
 *
 * @param array  $args 查询参数
 * @param string $context 缓存上下文
 * @param array  $options 扩展选项
 * @return WP_Query
 */
function developer_starter_run_cached_query( $args, $context = 'default', $options = array() ) {
    $context = sanitize_key( (string) $context );
    $args = apply_filters( 'developer_starter_cached_query_args', $args, $context, $options );
    $args = developer_starter_optimize_query_args( $args, $options );

    $cache_enabled = (bool) developer_starter_get_option( 'query_cache_enable', '1' );
    $cache_enabled = (bool) apply_filters( 'developer_starter_cached_query_enable', $cache_enabled, $context, $args, $options );
    if ( $cache_enabled && developer_starter_should_bypass_content_cache( 'query:' . $context ) ) {
        $cache_enabled = false;
    }
    if ( ! $cache_enabled ) {
        $query = new WP_Query( $args );
        $query = developer_starter_normalize_query_for_loop( $query, $args );
        $query = apply_filters( 'developer_starter_cached_query_result', $query, $context, $args, $options, false );
        return developer_starter_normalize_query_for_loop( $query, $args );
    }

    $stable_args = developer_starter_sort_recursive( $args );
    $cache_seed = array(
        'ctx'      => (string) $context,
        'args'     => $stable_args,
        'blog_id'  => get_current_blog_id(),
        'version'  => developer_starter_get_cache_version_stamp( array( 'content', 'settings' ) ),
    );
    $cache_seed = apply_filters( 'developer_starter_cached_query_seed', $cache_seed, $context, $args, $options );
    $cache_key = 'ds_qry_' . md5( wp_json_encode( $cache_seed ) );
    $cache_key = (string) apply_filters( 'developer_starter_cached_query_key', $cache_key, $cache_seed, $context, $args, $options );
    $cache_ttl = developer_starter_get_query_cache_ttl();
    $cache_ttl = (int) apply_filters( 'developer_starter_cached_query_ttl', $cache_ttl, $context, $args, $options );
    $cache_group = 'developer_starter_query';
    $cache_group = (string) apply_filters( 'developer_starter_cached_query_group', $cache_group, $context, $args, $options );

    $payload = developer_starter_cache_fetch( $cache_key, $cache_group );
    $payload = apply_filters( 'developer_starter_cached_query_payload', $payload, $cache_key, $cache_group, $context, $args, $options );

    if ( is_array( $payload ) && ( isset( $payload['post_ids'] ) || isset( $payload['posts'] ) ) ) {
        $cached_query = new WP_Query();
        $cached_query = developer_starter_normalize_query_for_loop( $cached_query, $args );
        $cached_query->posts = developer_starter_restore_cached_query_posts( $payload, $cached_query->query_vars );
        $cached_query->post_count = count( $cached_query->posts );
        $cached_query->found_posts = isset( $payload['found_posts'] ) ? (int) $payload['found_posts'] : $cached_query->post_count;
        $cached_query->max_num_pages = isset( $payload['max_num_pages'] ) ? (int) $payload['max_num_pages'] : 1;
        $cached_query->current_post = -1;
        $cached_query->in_the_loop = false;
        do_action( 'developer_starter_cached_query_hit', $cache_key, $context, $args, $options, $cached_query );
        $cached_query = apply_filters( 'developer_starter_cached_query_result', $cached_query, $context, $args, $options, true );
        return developer_starter_normalize_query_for_loop( $cached_query, $args );
    }

    do_action( 'developer_starter_cached_query_miss', $cache_key, $context, $args, $options );
    $query = new WP_Query( $args );
    $query_fields = isset( $query->query_vars['fields'] ) ? (string) $query->query_vars['fields'] : '';
    $to_cache = array(
        'fields'        => $query_fields,
        'found_posts'   => (int) $query->found_posts,
        'max_num_pages' => (int) $query->max_num_pages,
    );

    if ( 'ids' === $query_fields ) {
        $to_cache['post_ids'] = developer_starter_extract_query_post_ids( $query->posts );
    } elseif ( '' === $query_fields || 'all' === $query_fields ) {
        // 默认只缓存 ID，避免把完整 WP_Post（含正文）重复序列化进 transient / 对象缓存。
        $to_cache['post_ids'] = developer_starter_extract_query_post_ids( $query->posts );
    } else {
        $to_cache['posts'] = $query->posts;
    }

    $to_cache = apply_filters( 'developer_starter_cached_query_store_payload', $to_cache, $query, $cache_key, $context, $args, $options );

    developer_starter_cache_store( $cache_key, $to_cache, $cache_ttl, $cache_group );
    do_action( 'developer_starter_cached_query_stored', $cache_key, $context, $args, $options, $query, $to_cache );

    $query = developer_starter_normalize_query_for_loop( $query, $args );
    $query = apply_filters( 'developer_starter_cached_query_result', $query, $context, $args, $options, false );
    return developer_starter_normalize_query_for_loop( $query, $args );
}

/**
 * 获取片段缓存
 *
 * @param string $key 缓存键
 * @return string|false
 */
function developer_starter_get_fragment_cache( $key ) {
    $enabled = (bool) developer_starter_get_option( 'fragment_cache_enable', '' );
    $enabled = (bool) apply_filters( 'developer_starter_fragment_cache_enable', $enabled, $key );
    if ( ! $enabled ) {
        return false;
    }
    if ( developer_starter_should_bypass_content_cache( 'fragment:' . (string) $key ) ) {
        return false;
    }
    $safe_key = 'ds_frag_' . md5( $key . '|' . developer_starter_get_cache_version_stamp( array( 'content', 'settings' ) ) );
    $safe_key = (string) apply_filters( 'developer_starter_fragment_cache_key', $safe_key, $key );
    $cached = developer_starter_cache_fetch( $safe_key, 'developer_starter_fragment' );
    return apply_filters( 'developer_starter_fragment_cache_get', $cached, $safe_key, $key );
}

/**
 * 写入片段缓存
 *
 * @param string $key 缓存键
 * @param string $html 缓存内容
 * @return void
 */
function developer_starter_set_fragment_cache( $key, $html ) {
    $enabled = (bool) developer_starter_get_option( 'fragment_cache_enable', '' );
    $enabled = (bool) apply_filters( 'developer_starter_fragment_cache_enable', $enabled, $key );
    if ( ! $enabled ) {
        return;
    }
    if ( developer_starter_should_bypass_content_cache( 'fragment:' . (string) $key ) ) {
        return;
    }
    $safe_key = 'ds_frag_' . md5( $key . '|' . developer_starter_get_cache_version_stamp( array( 'content', 'settings' ) ) );
    $safe_key = (string) apply_filters( 'developer_starter_fragment_cache_key', $safe_key, $key );
    $html = (string) apply_filters( 'developer_starter_fragment_cache_set_html', (string) $html, $safe_key, $key );
    $ttl = developer_starter_get_fragment_cache_ttl();
    $ttl = (int) apply_filters( 'developer_starter_fragment_cache_set_ttl', $ttl, $safe_key, $key, $html );
    developer_starter_cache_store( $safe_key, $html, $ttl, 'developer_starter_fragment' );
    do_action( 'developer_starter_fragment_cache_saved', $safe_key, $key, $ttl );
}
