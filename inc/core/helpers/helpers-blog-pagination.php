<?php
/**
 * Blog-like static page pagination rewrite helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取需要支持分页规则的静态页面 slug。
 *
 * @param bool $force_refresh 是否强制重建缓存。
 * @return array<int,string>
 */
function developer_starter_get_blog_page_pagination_slugs( $force_refresh = false ) {
    $option_key = 'developer_starter_blog_page_pagination_slugs';

    if ( ! $force_refresh ) {
        $cached = get_option( $option_key, null );
        if ( is_array( $cached ) ) {
            return $cached;
        }
    }

    $slugs_map   = array();
    $paged_pages = get_posts(
        array(
            'post_type'      => 'page',
            'posts_per_page' => -1,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => array( 'templates/template-blog.php', 'templates/template-topic.php', 'templates/template-latest-posts.php' ),
            'meta_compare'   => 'IN',
            'fields'         => 'ids',
        )
    );

    foreach ( $paged_pages as $page_id ) {
        $page = get_post( $page_id );
        if ( $page && ! empty( $page->post_name ) ) {
            $slugs_map[ (int) $page_id ] = (string) $page->post_name;
        }
    }

    update_option( $option_key, $slugs_map, false );

    return $slugs_map;
}

/**
 * 注册博客/专题/最新文章页分页重写规则。
 *
 * @return void
 */
function developer_starter_blog_page_pagination_support() {
    $slugs_map = developer_starter_get_blog_page_pagination_slugs();
    if ( empty( $slugs_map ) ) {
        return;
    }

    foreach ( $slugs_map as $slug ) {
        add_rewrite_rule(
            $slug . '/page/?([0-9]{1,})/?$',
            'index.php?pagename=' . $slug . '&paged=$matches[1]',
            'top'
        );
    }
}
add_action( 'init', 'developer_starter_blog_page_pagination_support', 1 );

/**
 * 标记需要延迟刷新重写规则。
 *
 * @return void
 */
function developer_starter_queue_rewrite_rules_flush() {
    update_option( 'developer_starter_flush_rules', '1', false );
}

/**
 * 当博客模板/专题模板/最新文章模板页面保存时标记延迟刷新重写规则。
 *
 * @param int $post_id 文章 ID。
 * @return void
 */
function developer_starter_flush_blog_page_rules( $post_id ) {
    if ( get_post_type( $post_id ) !== 'page' ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    $template = get_post_meta( $post_id, '_wp_page_template', true );
    $tracked  = get_option( 'developer_starter_blog_page_pagination_slugs', array() );

    $is_current_target = in_array( $template, array( 'templates/template-blog.php', 'templates/template-topic.php', 'templates/template-latest-posts.php' ), true );
    $was_tracked       = is_array( $tracked ) && isset( $tracked[ (int) $post_id ] );

    if ( $is_current_target || $was_tracked ) {
        delete_option( 'developer_starter_blog_page_pagination_slugs' );
        developer_starter_queue_rewrite_rules_flush();
    }
}
add_action( 'save_post', 'developer_starter_flush_blog_page_rules' );

/**
 * 当已跟踪页面被删除时标记延迟刷新规则和缓存。
 *
 * @param int $post_id 文章 ID。
 * @return void
 */
function developer_starter_flush_blog_page_rules_on_delete( $post_id ) {
    if ( get_post_type( $post_id ) !== 'page' ) {
        return;
    }

    $tracked = get_option( 'developer_starter_blog_page_pagination_slugs', array() );
    if ( is_array( $tracked ) && isset( $tracked[ (int) $post_id ] ) ) {
        delete_option( 'developer_starter_blog_page_pagination_slugs' );
        developer_starter_queue_rewrite_rules_flush();
    }
}
add_action( 'before_delete_post', 'developer_starter_flush_blog_page_rules_on_delete' );

/**
 * 延迟刷新重写规则。
 *
 * @return void
 */
function developer_starter_delayed_flush_rules() {
    if ( get_option( 'developer_starter_flush_rules' ) === '1' ) {
        // 先重建分页页面缓存，再刷新规则，避免下一次请求继续实时扫描。
        developer_starter_get_blog_page_pagination_slugs( true );
        flush_rewrite_rules();
        delete_option( 'developer_starter_flush_rules' );
    }
}
add_action( 'init', 'developer_starter_delayed_flush_rules', 999 );
