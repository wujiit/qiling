<?php
/**
 * 菜单保护器类
 *
 * 当删除页面/文章/分类时，保持菜单结构不变
 * 将菜单项转换为自定义链接而不是删除
 *
 * @package Developer_Starter
 * @since 1.0.2
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 菜单保护器类
 */
class Menu_Protector {

    /**
     * 构造函数
     */
    public function __construct() {
        // 删除页面/文章前的钩子
        add_action( 'before_delete_post', array( $this, 'protect_menu_on_post_delete' ), 10, 1 );
        // 页面/文章移入回收站时也要保护（用户常见操作）
        add_action( 'wp_trash_post', array( $this, 'protect_menu_on_post_delete' ), 10, 1 );
        
        // 删除分类/标签前的钩子
        add_action( 'pre_delete_term', array( $this, 'protect_menu_on_term_delete' ), 10, 2 );

        if ( (bool) apply_filters( 'developer_starter_enable_menu_history_repair', false ) ) {
            add_action( 'admin_init', array( $this, 'maybe_repair_invalid_menu_items' ), 20 );
        }
        
        // 过滤菜单项，添加标记类
        add_filter( 'nav_menu_css_class', array( $this, 'add_menu_item_classes' ), 10, 4 );
        
        // 过滤损坏的菜单项链接
        add_filter( 'nav_menu_link_attributes', array( $this, 'filter_menu_link' ), 10, 4 );
    }

    /**
     * 删除页面/文章时保护菜单
     *
     * @param int $post_id 文章ID
     */
    public function protect_menu_on_post_delete( $post_id ) {
        // 获取文章信息
        $post = get_post( $post_id );
        if ( ! $post ) {
            return;
        }
        
        // 获取所有使用此文章的菜单项
        $menu_items = $this->get_menu_items_by_object( $post_id, $post->post_type );
        
        if ( empty( $menu_items ) ) {
            return;
        }
        
        // 将每个菜单项转换为自定义链接
        foreach ( $menu_items as $menu_item ) {
            $this->convert_to_custom_link( $menu_item, $post->post_title, '#' );
        }
    }

    /**
     * 删除分类/标签时保护菜单
     *
     * @param int    $term_id  分类ID
     * @param string $taxonomy 分类法
     */
    public function protect_menu_on_term_delete( $term_id, $taxonomy ) {
        // 获取分类信息
        $term = get_term( $term_id, $taxonomy );
        if ( ! $term || is_wp_error( $term ) ) {
            return;
        }
        
        // 获取所有使用此分类的菜单项
        $menu_items = $this->get_menu_items_by_term( $term_id, $taxonomy );
        
        if ( empty( $menu_items ) ) {
            return;
        }
        
        // 将每个菜单项转换为自定义链接
        foreach ( $menu_items as $menu_item ) {
            $this->convert_to_custom_link( $menu_item, $term->name, '#' );
        }
    }

    /**
     * 修复历史遗留的无效菜单项
     *
     * 场景：
     * - 菜单引用的页面已被删除/回收站，导致父级项被前台过滤掉，子级错位。
     * - 将这类菜单项转为自定义链接 #，保留层级结构与显示标题。
     *
     * @return void
     */
    public function maybe_repair_invalid_menu_items() {
        $cache_key = 'ds_menu_protector_repair_done';
        if ( get_transient( $cache_key ) ) {
            return;
        }

        $menu_items = get_posts(
            array(
                'post_type'      => 'nav_menu_item',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'all',
                'no_found_rows'  => true,
            )
        );

        if ( empty( $menu_items ) ) {
            set_transient( $cache_key, '1', 10 * MINUTE_IN_SECONDS );
            return;
        }

        foreach ( $menu_items as $menu_item ) {
            if ( ! $menu_item instanceof \WP_Post ) {
                continue;
            }

            $item_type = (string) get_post_meta( $menu_item->ID, '_menu_item_type', true );
            if ( 'post_type' !== $item_type && 'taxonomy' !== $item_type ) {
                continue;
            }

            $object_id = (int) get_post_meta( $menu_item->ID, '_menu_item_object_id', true );
            if ( $object_id <= 0 ) {
                continue;
            }

            $title = (string) $menu_item->post_title;
            if ( '' === $title ) {
                $title = __( '未命名菜单', 'developer-starter' );
            }

            if ( 'post_type' === $item_type ) {
                $target_post = get_post( $object_id );
                if ( ! $target_post instanceof \WP_Post || 'publish' !== $target_post->post_status ) {
                    $this->convert_to_custom_link( $menu_item, $title, '#' );
                }
                continue;
            }

            $taxonomy = (string) get_post_meta( $menu_item->ID, '_menu_item_object', true );
            if ( '' === $taxonomy ) {
                continue;
            }

            $target_term = get_term( $object_id, $taxonomy );
            if ( ! $target_term || is_wp_error( $target_term ) ) {
                $this->convert_to_custom_link( $menu_item, $title, '#' );
            }
        }

        set_transient( $cache_key, '1', 10 * MINUTE_IN_SECONDS );
    }

    /**
     * 获取引用指定文章的所有菜单项
     *
     * @param int    $object_id   对象ID
     * @param string $object_type 对象类型 (post, page, etc.)
     * @return array 菜单项数组
     */
    private function get_menu_items_by_object( $object_id, $object_type ) {
        $args = array(
            'post_type'      => 'nav_menu_item',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_menu_item_object_id',
                    'value' => $object_id,
                ),
                array(
                    'key'   => '_menu_item_type',
                    'value' => 'post_type',
                ),
            ),
        );
        
        return get_posts( $args );
    }

    /**
     * 获取引用指定分类的所有菜单项
     *
     * @param int    $term_id  分类ID
     * @param string $taxonomy 分类法
     * @return array 菜单项数组
     */
    private function get_menu_items_by_term( $term_id, $taxonomy ) {
        $args = array(
            'post_type'      => 'nav_menu_item',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_menu_item_object_id',
                    'value' => $term_id,
                ),
                array(
                    'key'   => '_menu_item_type',
                    'value' => 'taxonomy',
                ),
                array(
                    'key'   => '_menu_item_object',
                    'value' => $taxonomy,
                ),
            ),
        );
        
        return get_posts( $args );
    }

    /**
     * 将菜单项转换为自定义链接
     *
     * @param WP_Post $menu_item 菜单项
     * @param string  $title     标题
     * @param string  $url       URL
     */
    private function convert_to_custom_link( $menu_item, $title, $url ) {
        $url = trim( (string) $url );
        if ( '' === $url ) {
            $url = '#';
        }

        // 更新菜单项类型为自定义链接
        update_post_meta( $menu_item->ID, '_menu_item_type', 'custom' );
        update_post_meta( $menu_item->ID, '_menu_item_object', 'custom' );
        update_post_meta( $menu_item->ID, '_menu_item_object_id', '0' );
        update_post_meta( $menu_item->ID, '_menu_item_url', $url );
        
        // 标记为已删除内容的链接
        update_post_meta( $menu_item->ID, '_menu_item_deleted_content', '1' );
        
        // 如果菜单项没有自定义标题，使用原标题
        if ( empty( $menu_item->post_title ) ) {
            wp_update_post( array(
                'ID'         => $menu_item->ID,
                'post_title' => $title,
            ) );
        }
    }

    /**
     * 为删除内容的菜单项添加CSS类
     *
     * @param array    $classes   CSS类数组
     * @param WP_Post  $item      菜单项
     * @param stdClass $args      菜单参数
     * @param int      $depth     深度
     * @return array
     */
    public function add_menu_item_classes( $classes, $item, $args, $depth ) {
        $is_deleted = get_post_meta( $item->ID, '_menu_item_deleted_content', true );
        
        if ( $is_deleted ) {
            $classes[] = 'menu-item-deleted-content';
        }
        
        return $classes;
    }

    /**
     * 过滤已删除内容的菜单链接
     *
     * @param array    $atts  链接属性
     * @param WP_Post  $item  菜单项
     * @param stdClass $args  菜单参数
     * @param int      $depth 深度
     * @return array
     */
    public function filter_menu_link( $atts, $item, $args, $depth ) {
        $is_deleted = get_post_meta( $item->ID, '_menu_item_deleted_content', true );
        
        if ( $is_deleted ) {
            // 删除对象保留占位，避免点击到无效链接。
            $atts['href'] = '#';
            
            // 添加提示属性
            $atts['title'] = isset( $atts['title'] ) ? $atts['title'] : '';
            if ( current_user_can( 'edit_theme_options' ) ) {
                $atts['title'] .= ' ' . __( '(原内容已删除)', 'developer-starter' );
            }
        }
        
        return $atts;
    }
}
