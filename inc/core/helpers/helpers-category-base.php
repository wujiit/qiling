<?php
/**
 * Category base rewrite helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 分类链接去除 category 前缀。
 *
 * 基于 No Category Base (WPML) 插件实现。
 *
 * @return void
 */
function developer_starter_remove_category_base_init() {
    if ( ! developer_starter_get_option( 'remove_category_base', '' ) ) {
        return;
    }

    global $wp_rewrite;

    $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
}
add_action( 'init', 'developer_starter_remove_category_base_init', 1 );

/**
 * 分类重写规则 - 参考 No Category Base 插件。
 *
 * @param array<string,string> $category_rewrite 分类重写规则。
 * @return array<string,string>
 */
function developer_starter_category_rewrite_rules( $category_rewrite ) {
    if ( ! developer_starter_get_option( 'remove_category_base', '' ) ) {
        return $category_rewrite;
    }

    global $wp_rewrite;
    $category_rewrite = array();

    $categories = get_categories( array( 'hide_empty' => false ) );

    foreach ( $categories as $category ) {
        $category_nicename = $category->slug;

        if ( $category->parent == $category->cat_ID ) {
            $category->parent = 0;
        } elseif ( $category->parent != 0 ) {
            $category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;
        }

        $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$']                         = 'index.php?category_name=$matches[1]&feed=$matches[2]';
        $category_rewrite['(' . $category_nicename . ')/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
        $category_rewrite['(' . $category_nicename . ')/?$']                                                            = 'index.php?category_name=$matches[1]';
    }

    $old_category_base = get_option( 'category_base' ) ? get_option( 'category_base' ) : 'category';
    $old_category_base = trim( $old_category_base, '/' );
    $category_rewrite[ $old_category_base . '/(.*)$' ] = 'index.php?category_redirect=$matches[1]';

    return $category_rewrite;
}
add_filter( 'category_rewrite_rules', 'developer_starter_category_rewrite_rules' );

/**
 * 添加 category_redirect 查询变量。
 *
 * @param string[] $public_query_vars 公开查询变量。
 * @return string[]
 */
function developer_starter_category_query_vars( $public_query_vars ) {
    if ( developer_starter_get_option( 'remove_category_base', '' ) ) {
        $public_query_vars[] = 'category_redirect';
    }
    return $public_query_vars;
}
add_filter( 'query_vars', 'developer_starter_category_query_vars' );

/**
 * 处理旧 category 链接的 301 重定向。
 *
 * @param array<string,mixed> $query_vars 查询变量。
 * @return array<string,mixed>
 */
function developer_starter_category_redirect( $query_vars ) {
    if ( isset( $query_vars['category_redirect'] ) ) {
        $catlink = trailingslashit( get_option( 'home' ) ) . user_trailingslashit( $query_vars['category_redirect'], 'category' );
        wp_safe_redirect( $catlink, 301 );
        exit;
    }
    return $query_vars;
}
add_filter( 'request', 'developer_starter_category_redirect' );

/**
 * 分类创建/编辑/删除时标记延迟刷新规则。
 *
 * @return void
 */
function developer_starter_refresh_category_rules() {
    if ( developer_starter_get_option( 'remove_category_base', '' ) ) {
        developer_starter_queue_rewrite_rules_flush();
    }
}
add_action( 'created_category', 'developer_starter_refresh_category_rules' );
add_action( 'delete_category', 'developer_starter_refresh_category_rules' );
add_action( 'edited_category', 'developer_starter_refresh_category_rules' );

/**
 * 保存选项时标记延迟刷新固定链接规则。
 *
 * @param array<string,mixed> $old_value 旧设置。
 * @param array<string,mixed> $new_value 新设置。
 * @return void
 */
function developer_starter_flush_rewrite_on_save( $old_value, $new_value ) {
    $old_cat = isset( $old_value['remove_category_base'] ) ? $old_value['remove_category_base'] : '';
    $new_cat = isset( $new_value['remove_category_base'] ) ? $new_value['remove_category_base'] : '';

    if ( $old_cat !== $new_cat ) {
        developer_starter_queue_rewrite_rules_flush();
    }
}
add_action( 'update_option_developer_starter_options', 'developer_starter_flush_rewrite_on_save', 10, 2 );
