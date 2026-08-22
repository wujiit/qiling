<?php
namespace Developer_Starter\Core {

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 用户管理类
 * 处理用户头像、投稿、作者相关功能
 */
class User_Manager {
    const PUBLIC_PROFILE_ID_META_KEY = 'ds_public_profile_id';
    const PUBLIC_PROFILE_ID_MIN      = 10000000;
    const PUBLIC_PROFILE_ID_MAX      = 99999999;
    
    /**
     * 初始化钩子
     */
    public static function init() {
        // 自定义头像
        add_filter( 'get_avatar_url', [ __CLASS__, 'custom_avatar_url' ], 10, 3 );

        // 公开作者ID与作者链接
        add_action( 'user_register', [ __CLASS__, 'ensure_public_profile_id_for_user' ], 20, 1 );
        add_action( 'init', [ __CLASS__, 'ensure_current_user_public_profile_id' ], 1 );
        add_action( 'wp_login', [ __CLASS__, 'ensure_public_profile_id_on_login' ], 20, 2 );
        add_action( 'admin_init', [ __CLASS__, 'maybe_seed_public_profile_ids' ], 20 );
        add_filter( 'author_link', [ __CLASS__, 'filter_author_link_to_public_id' ], 10, 3 );
        add_filter( 'request', [ __CLASS__, 'map_public_id_author_request' ], 10, 1 );
        
        // 用户投稿 AJAX
        add_action( 'wp_ajax_developer_starter_submit_post', [ __CLASS__, 'handle_submit_post' ] );
        add_action( 'wp_ajax_developer_starter_delete_post', [ __CLASS__, 'handle_delete_post' ] );
        
        // 分类页每页文章数
        add_action( 'pre_get_posts', [ __CLASS__, 'category_posts_per_page' ] );
        
        // 作者页样式
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_author_page_styles' ] );
    }

    /**
     * 获取用户公开ID（8位随机数字，不含前导0）。
     */
    public static function get_user_public_profile_id( $user_id, $create_if_missing = true ) {
        $user_id = absint( $user_id );

        if ( ! $user_id ) {
            return '';
        }

        $public_id = self::get_stored_public_profile_id( $user_id );
        if ( '' !== $public_id ) {
            return $public_id;
        }

        if ( ! $create_if_missing ) {
            return '';
        }

        return self::ensure_public_profile_id_for_user( $user_id );
    }

    /**
     * 给指定用户确保生成并持久化公开ID，同时同步为 author slug。
     */
    public static function ensure_public_profile_id_for_user( $user_id ) {
        $user_id = absint( $user_id );

        if ( ! $user_id ) {
            return '';
        }

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return '';
        }

        $public_id = self::get_stored_public_profile_id( $user_id );

        if ( '' === $public_id ) {
            $public_id = self::generate_unique_public_profile_id( $user_id );

            if ( '' === $public_id ) {
                return '';
            }

            update_user_meta( $user_id, self::PUBLIC_PROFILE_ID_META_KEY, $public_id );
        }

        if ( (string) $user->user_nicename !== $public_id ) {
            wp_update_user(
                array(
                    'ID'            => $user_id,
                    'user_nicename' => $public_id,
                )
            );
            clean_user_cache( $user_id );
        }

        return $public_id;
    }

    /**
     * 确保当前管理员有公开ID，避免主题启用后站长作者链接暴露真实用户名。
     */
    public static function ensure_current_user_public_profile_id() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        self::ensure_public_profile_id_if_missing( get_current_user_id() );
    }

    /**
     * 用户登录时只补齐该用户自己的公开ID。
     *
     * @param string  $user_login 用户登录名。
     * @param WP_User $user       用户对象。
     * @return void
     */
    public static function ensure_public_profile_id_on_login( $user_login, $user ) {
        unset( $user_login );

        if ( ! ( $user instanceof \WP_User ) || empty( $user->ID ) ) {
            return;
        }

        self::ensure_public_profile_id_if_missing( $user->ID );
    }

    /**
     * 后台轻量分批补齐历史用户公开ID，避免老站用户量大时拖慢普通请求。
     */
    public static function maybe_seed_public_profile_ids() {
        if ( ! (bool) apply_filters( 'developer_starter_enable_public_profile_bulk_seed', false ) ) {
            return;
        }

        if ( '1' === (string) get_option( 'ds_public_profile_ids_seeded', '0' ) ) {
            return;
        }

        if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $lock_key = 'ds_public_profile_ids_seed_lock';
        if ( get_transient( $lock_key ) ) {
            return;
        }
        set_transient( $lock_key, '1', MINUTE_IN_SECONDS );

        $batch = (int) apply_filters( 'developer_starter_public_profile_seed_batch_size', 20 );
        if ( $batch < 1 ) {
            $batch = 1;
        } elseif ( $batch > 50 ) {
            $batch = 50;
        }

        $started_at  = microtime( true );
        $time_budget = (float) apply_filters( 'developer_starter_public_profile_seed_time_budget', 1.5 );
        if ( $time_budget < 0.2 ) {
            $time_budget = 0.2;
        }

        $user_ids = get_users(
            array(
                'fields'      => 'ids',
                'number'      => $batch,
                'orderby'     => 'ID',
                'order'       => 'ASC',
                'count_total' => false,
                'meta_query'  => array(
                    array(
                        'key'     => self::PUBLIC_PROFILE_ID_META_KEY,
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            )
        );

        if ( empty( $user_ids ) ) {
            update_option( 'ds_public_profile_ids_seeded', '1', false );
            delete_option( 'ds_public_profile_ids_seed_offset' );
            delete_transient( $lock_key );
            return;
        }

        foreach ( $user_ids as $uid ) {
            self::ensure_public_profile_id_for_user( $uid );
            if ( microtime( true ) - $started_at >= $time_budget ) {
                break;
            }
        }

        delete_option( 'ds_public_profile_ids_seed_offset' );
    }

    /**
     * 作者链接统一改为 /author/{公开ID}。
     */
    public static function filter_author_link_to_public_id( $link, $author_id, $author_nicename ) {
        $author_id = absint( $author_id );

        if ( ! $author_id ) {
            return $link;
        }

        $public_id = self::get_stored_public_profile_id( $author_id );

        if ( '' === $public_id ) {
            return $link;
        }

        $author_base = get_option( 'author_base' );
        $author_base = is_string( $author_base ) && '' !== trim( $author_base ) ? trim( $author_base ) : 'author';

        return home_url( user_trailingslashit( $author_base . '/' . $public_id, 'author' ) );
    }

    /**
     * 将 /author/{公开ID} 映射到对应用户；阻止 /author/{用户名} 直接访问。
     */
    public static function map_public_id_author_request( $query_vars ) {
        if ( is_admin() || ! is_array( $query_vars ) || empty( $query_vars['author_name'] ) ) {
            return $query_vars;
        }

        $author_name = sanitize_title_for_query( (string) $query_vars['author_name'] );

        if ( '' === $author_name ) {
            return $query_vars;
        }

        if ( ! self::is_valid_public_profile_id( $author_name ) ) {
            $query_vars['error'] = '404';
            unset( $query_vars['author_name'] );
            return $query_vars;
        }

        $user_ids = get_users(
            array(
                'meta_key'    => self::PUBLIC_PROFILE_ID_META_KEY,
                'meta_value'  => $author_name,
                'fields'      => 'ids',
                'number'      => 1,
                'count_total' => false,
            )
        );

        if ( empty( $user_ids ) ) {
            $query_vars['error'] = '404';
            unset( $query_vars['author_name'] );
            return $query_vars;
        }

        $query_vars['author'] = absint( $user_ids[0] );
        unset( $query_vars['author_name'] );

        return $query_vars;
    }

    /**
     * 读取已存储的公开ID；不做唯一性查询，避免每次请求扫描用户表。
     */
    private static function get_stored_public_profile_id( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return '';
        }

        $public_id = (string) get_user_meta( $user_id, self::PUBLIC_PROFILE_ID_META_KEY, true );
        $public_id = preg_replace( '/\D+/', '', $public_id );

        return self::is_valid_public_profile_id( $public_id ) ? $public_id : '';
    }

    /**
     * 仅在用户缺少公开ID时生成，避免重复查询和写入。
     */
    private static function ensure_public_profile_id_if_missing( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id || '' !== self::get_stored_public_profile_id( $user_id ) ) {
            return '';
        }

        return self::ensure_public_profile_id_for_user( $user_id );
    }

    private static function generate_unique_public_profile_id( $user_id = 0 ) {
        $user_id  = absint( $user_id );
        $attempts = 0;

        while ( $attempts < 80 ) {
            $candidate = (string) wp_rand( self::PUBLIC_PROFILE_ID_MIN, self::PUBLIC_PROFILE_ID_MAX );

            if ( self::public_profile_id_is_unique( $candidate, $user_id ) ) {
                return $candidate;
            }

            $attempts++;
        }

        return '';
    }

    private static function is_valid_public_profile_id( $public_id ) {
        return 1 === preg_match( '/^[1-9][0-9]{7}$/', (string) $public_id );
    }

    private static function public_profile_id_is_unique( $public_id, $user_id = 0 ) {
        if ( ! self::is_valid_public_profile_id( $public_id ) ) {
            return false;
        }

        $user_id = absint( $user_id );
        $matches = get_users(
            array(
                'meta_key'    => self::PUBLIC_PROFILE_ID_META_KEY,
                'meta_value'  => (string) $public_id,
                'fields'      => 'ids',
                'number'      => 2,
                'count_total' => false,
            )
        );

        if ( empty( $matches ) ) {
            return true;
        }

        if ( 1 === count( $matches ) && absint( $matches[0] ) === $user_id ) {
            return true;
        }

        return false;
    }
    
    /**
     * 自定义用户头像系统
     * 替代WordPress默认的Gravatar头像服务
     */
    public static function custom_avatar_url( $url, $id_or_email, $args ) {
        // 获取用户ID
        $user_id = 0;
        if ( is_numeric( $id_or_email ) ) {
            $user_id = (int) $id_or_email;
        } elseif ( is_object( $id_or_email ) ) {
            if ( ! empty( $id_or_email->user_id ) ) {
                $user_id = (int) $id_or_email->user_id;
            }
        } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
            $user = get_user_by( 'email', $id_or_email );
            if ( $user ) {
                $user_id = $user->ID;
            }
        }
        
        // 1. 检查用户是否有自定义头像
        if ( $user_id > 0 ) {
            $custom_avatar = get_user_meta( $user_id, 'ds_custom_avatar', true );
            if ( ! empty( $custom_avatar ) ) {
                return $custom_avatar;
            }

            $social_avatar = self::get_social_avatar_url( $user_id );
            if ( '' !== $social_avatar ) {
                return $social_avatar;
            }
        }
        
        // 2. 检查后台是否设置了默认头像
        $default_avatar = developer_starter_get_option( 'default_avatar', '' );
        if ( ! empty( $default_avatar ) ) {
            return $default_avatar;
        }
        
        // 3. 回退到默认
        return $url;
    }
    
    /**
     * 获取用户自定义头像URL
     */
    public static function get_user_avatar_url( $user_id, $size = 96 ) {
        // 检查用户自定义头像
        $custom_avatar = get_user_meta( $user_id, 'ds_custom_avatar', true );
        if ( ! empty( $custom_avatar ) ) {
            return $custom_avatar;
        }

        $social_avatar = self::get_social_avatar_url( $user_id );
        if ( '' !== $social_avatar ) {
            return $social_avatar;
        }
        
        // 检查默认头像设置
        $default_avatar = developer_starter_get_option( 'default_avatar', '' );
        if ( ! empty( $default_avatar ) ) {
            return $default_avatar;
        }
        
        // 回退到WordPress默认
        return get_avatar_url( $user_id, array( 'size' => $size ) );
    }

    /**
     * 获取第三方社交登录头像 URL。
     *
     * @param int $user_id 用户 ID。
     * @return string
     */
    private static function get_social_avatar_url( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return '';
        }

        $provider = sanitize_key( (string) get_user_meta( $user_id, 'qiling_social_login_provider', true ) );
        $meta_keys = array();
        if ( '' !== $provider ) {
            $meta_keys[] = 'qiling_social_' . $provider . '_avatar';
        }

        $meta_keys = array_merge(
            $meta_keys,
            array(
                'qiling_weixin_avatar',
                'qiling_social_qq_avatar',
                'qiling_social_github_avatar',
                'qiling_social_google_avatar',
            )
        );

        foreach ( array_unique( $meta_keys ) as $meta_key ) {
            $avatar = get_user_meta( $user_id, $meta_key, true );
            if ( ! empty( $avatar ) ) {
                $avatar = esc_url_raw( (string) $avatar );
                if ( '' !== $avatar ) {
                    return $avatar;
                }
            }
        }

        return '';
    }
    
    /**
     * 用户投稿 AJAX 处理
     */
    public static function handle_submit_post() {
        // 1. 验证 nonce
        if ( ! check_ajax_referer( 'developer_starter_submit_post', 'submit_post_nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( '安全验证失败，请刷新页面后重试。', 'developer-starter' ) ) );
        }
        
        // 2. 检查用户登录状态
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( '请先登录后再投稿。', 'developer-starter' ) ) );
        }
        
        // 3. 检查功能是否启用
        if ( ! developer_starter_get_option( 'submit_post_enable', '' ) ) {
            wp_send_json_error( array( 'message' => __( '投稿功能暂未开放。', 'developer-starter' ) ) );
        }
        
        // 4. 获取并验证表单数据
        $title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
        $content = isset( $_POST['post_content'] ) ? wp_kses_post( wp_unslash( $_POST['post_content'] ) ) : '';
        $category = isset( $_POST['post_category'] ) ? absint( wp_unslash( $_POST['post_category'] ) ) : 0;
        $tags = isset( $_POST['post_tags'] ) ? sanitize_text_field( wp_unslash( $_POST['post_tags'] ) ) : '';
        
        // 验证标题
        if ( empty( $title ) ) {
            wp_send_json_error( array( 'message' => __( '请输入文章标题。', 'developer-starter' ) ) );
        }
        
        if ( mb_strlen( $title ) > 100 ) {
            wp_send_json_error( array( 'message' => __( '文章标题不能超过100个字符。', 'developer-starter' ) ) );
        }
        
        // 验证内容
        if ( empty( $content ) || trim( strip_tags( $content ) ) === '' ) {
            wp_send_json_error( array( 'message' => __( '请输入文章内容。', 'developer-starter' ) ) );
        }
        
        // 验证分类
        $allowed_categories = developer_starter_get_option( 'submit_post_categories', array() );
        if ( ! empty( $allowed_categories ) && is_array( $allowed_categories ) ) {
            // 如果设置了允许的分类，检查是否在范围内
            if ( ! in_array( $category, array_map( 'intval', $allowed_categories ) ) ) {
                wp_send_json_error( array( 'message' => __( '请选择有效的文章分类。', 'developer-starter' ) ) );
            }
        } else {
            // 没有限制分类时，仅检查分类是否存在
            if ( $category && ! term_exists( $category, 'category' ) ) {
                wp_send_json_error( array( 'message' => __( '所选分类不存在。', 'developer-starter' ) ) );
            }
        }
        
        // 处理标签
        $tag_array = array();
        if ( ! empty( $tags ) && developer_starter_get_option( 'submit_post_allow_tags', '1' ) ) {
            $max_tags = developer_starter_get_option( 'submit_post_max_tags', '5' );
            $max_tags = $max_tags ? intval( $max_tags ) : 5;
            
            $tag_list = array_map( 'trim', explode( ',', $tags ) );
            $tag_list = array_filter( $tag_list );
            $tag_array = array_slice( $tag_list, 0, $max_tags );
        }
        
        // 5. 创建或更新文章
        $current_user = wp_get_current_user();
        
        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'pending', // 无论是新建还是更新，都需要重新审核
            'post_author'  => $current_user->ID,
            'post_type'    => 'post',
        );
        
        // 设置分类
        if ( $category ) {
            $post_data['post_category'] = array( $category );
        }

        $post_id = 0;
        $is_update = false;

        // 检查是否是更新文章
        if ( isset( $_POST['post_id'] ) && '' !== (string) wp_unslash( $_POST['post_id'] ) ) {
            $edit_post_id = absint( wp_unslash( $_POST['post_id'] ) );
            
            // 验证文章存在且属于当前用户
            $edit_post = get_post( $edit_post_id );
            if ( $edit_post && (int) $edit_post->post_author === (int) $current_user->ID ) {
                $post_data['ID'] = $edit_post_id;
                $post_id = wp_update_post( $post_data, true );
                $is_update = true;
            } else {
                wp_send_json_error( array( 'message' => __( '无法编辑该文章：权限不足或文章不存在。', 'developer-starter' ) ) );
            }
        } else {
            // 插入新文章
            $post_id = wp_insert_post( $post_data, true );
        }
        
        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '投稿失败：', 'developer-starter' ) . $post_id->get_error_message() ) );
        }
        
        // 6. 设置标签
        if ( ! empty( $tag_array ) ) {
            wp_set_post_tags( $post_id, $tag_array );
        }
        
        /**
         * 投稿保存成功后钩子
         */
        do_action( 'qiling_submit_post_saved', $post_id, $_POST, $is_update );
        
        // 7. 添加/更新投稿来源标记
        if ( ! $is_update ) {
            update_post_meta( $post_id, '_ds_submitted_from_frontend', '1' );
        }
        // 添加提交时间记录
        update_post_meta( $post_id, '_ds_submitted_at', current_time( 'mysql' ) );
        
        // 8. 返回成功消息
        $success_message = developer_starter_get_option( 'submit_post_success_message', '' );
        if ( empty( $success_message ) ) {
            $success_message = $is_update ? __( '更新成功！请等待管理员审核。', 'developer-starter' ) : __( '投稿成功！请等待管理员审核。', 'developer-starter' );
        }
        
        // 获取个人中心页面URL
        $redirect_url = '';
        
        // 1. 首先检查后台是否设置了跳转页面
        $redirect_page_id = developer_starter_get_option( 'submit_post_redirect_page', '' );
        $redirect_tab = developer_starter_get_option( 'submit_post_redirect_tab', 'posts' );
        
        if ( ! empty( $redirect_page_id ) ) {
            // 使用后台设置的页面
            $redirect_url = get_permalink( $redirect_page_id );
            if ( $redirect_url && ! empty( $redirect_tab ) ) {
                $redirect_url = add_query_arg( 'tab', $redirect_tab, $redirect_url );
            }
        } else {
            // 2. 自动查找使用 template-account.php 模板的页面
            $account_pages = get_posts( array(
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'meta_key'       => '_wp_page_template',
                'meta_value'     => 'templates/template-account.php',
                'fields'         => 'ids',
            ) );
            if ( ! empty( $account_pages ) ) {
                $account_page_id = $account_pages[0];
                $tab = ! empty( $redirect_tab ) ? $redirect_tab : 'posts';
                $redirect_url = add_query_arg( 'tab', $tab, get_permalink( $account_page_id ) );
            }
        }
        
        wp_send_json_success( array(
            'message'      => $success_message,
            'post_id'      => $post_id,
            'redirect_url' => $redirect_url,
        ) );
    }
    
    /**
     * 用户删除投稿 AJAX 处理
     */
    public static function handle_delete_post() {
        // 1. 验证 nonce
        if ( ! check_ajax_referer( 'developer_starter_delete_post', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( '安全验证失败，请刷新页面后重试。', 'developer-starter' ) ) );
        }
        
        // 2. 检查用户登录状态
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( '请先登录。', 'developer-starter' ) ) );
        }
        
        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( '参数错误。', 'developer-starter' ) ) );
        }
        
        $current_user = wp_get_current_user();
        $post = get_post( $post_id );
        
        // 3. 验证权限：只能删除自己的文章
        if ( ! $post || (int) $post->post_author !== (int) $current_user->ID ) {
            wp_send_json_error( array( 'message' => __( '权限不足，无法删除此文章。', 'developer-starter' ) ) );
        }
        
        // 4. 执行删除（移至回收站）
        $result = wp_trash_post( $post_id );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( '文章已删除至回收站。', 'developer-starter' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( '删除失败，请重试。', 'developer-starter' ) ) );
        }
    }
    
    /**
     * 分类页面每页文章数量设置
     */
    public static function category_posts_per_page( $query ) {
        // 仅在前端分类页面的主查询中生效
        if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() ) {
            return;
        }
        
        // 获取当前分类ID
        $cat_id = absint( $query->get( 'cat' ) );
        if ( $cat_id <= 0 ) {
            $category_path = (string) $query->get( 'category_name' );
            if ( '' !== $category_path && function_exists( 'get_category_by_path' ) ) {
                $term = get_category_by_path( $category_path, false );
                if ( $term instanceof \WP_Term ) {
                    $cat_id = absint( $term->term_id );
                }
            }
        }
        if ( $cat_id <= 0 ) {
            $cat_id = absint( get_queried_object_id() );
        }
        if ( $cat_id <= 0 ) {
            return;
        }
        
        // 获取分类自定义设置
        $settings = array();
        if ( class_exists( 'Developer_Starter\\Core\\Category_Manager' ) ) {
            $settings = \Developer_Starter\Core\Category_Manager::get_category_settings( $cat_id );
        }

        $resolved_archive_settings = class_exists( 'Developer_Starter\\Core\\Blog_Visual_Manager' )
            ? \Developer_Starter\Core\Blog_Visual_Manager::resolve_category_archive_settings( $cat_id, $settings )
            : array();

        $resolved_per_page = ! empty( $resolved_archive_settings['posts_per_page'] ) ? intval( $resolved_archive_settings['posts_per_page'] ) : 0;
        if ( $resolved_per_page > 0 ) {
            $query->set( 'posts_per_page', $resolved_per_page );
        }

        if ( empty( $settings['adv_filter_enabled'] ) ) {
            return;
        }

        $theme_options = function_exists( 'developer_starter_get_options_cache' )
            ? developer_starter_get_options_cache()
            : get_option( 'developer_starter_options', array() );
        $current_sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( (string) $_GET['sort'] ) ) : '';
        if ( function_exists( 'developer_starter_normalize_category_sort' ) ) {
            $current_sort = developer_starter_normalize_category_sort( $current_sort, $theme_options );
        } elseif ( ! in_array( $current_sort, array( 'latest', 'random', 'hot', 'like', 'favorite' ), true ) ) {
            $current_sort = 'latest';
        }

        if ( function_exists( 'developer_starter_apply_category_sort_query_args' ) ) {
            $query_args = developer_starter_apply_category_sort_query_args( array(), $current_sort, $cat_id, array() );
            foreach ( $query_args as $query_key => $query_value ) {
                $query->set( $query_key, $query_value );
            }

            return;
        }

        if ( 'random' === $current_sort ) {
            $query->set( 'orderby', 'rand' );
        } elseif ( 'hot' === $current_sort ) {
            $query->set( 'meta_key', 'ds_post_views_count' );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'DESC' );
        } elseif ( 'like' === $current_sort ) {
            $query->set( 'meta_key', 'post_like_count' );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'DESC' );
        } elseif ( 'favorite' === $current_sort ) {
            $query->set( 'meta_key', 'post_favorite_count' );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'DESC' );
        } else {
            $query->set( 'orderby', 'date' );
            $query->set( 'order', 'DESC' );
        }
    }
    
    /**
     * 获取作者所有文章的总浏览量
     */
    public static function get_author_total_views( $author_id ) {
        $cache_key = 'ds_author_total_views_v2_' . $author_id;
        $cache_enabled = true;
        if ( $cache_enabled ) {
            if ( function_exists( 'developer_starter_cache_fetch' ) ) {
                $total = \developer_starter_cache_fetch( $cache_key, 'developer_starter_user' );
            } else {
                $total = get_transient( $cache_key );
            }
        } else {
            $total = false;
        }
        
        if ( false === $total ) {
            global $wpdb;

            $total = $wpdb->get_var( $wpdb->prepare(
                "SELECT SUM(COALESCE(v.ds_views, v.legacy_views, 0))
                 FROM {$wpdb->posts} p
                 LEFT JOIN (
                     SELECT pm.post_id,
                            MAX(CASE WHEN pm.meta_key = 'ds_post_views_count' THEN CAST(pm.meta_value AS UNSIGNED) END) AS ds_views,
                            MAX(CASE WHEN pm.meta_key = 'post_views_count' THEN CAST(pm.meta_value AS UNSIGNED) END) AS legacy_views
                     FROM {$wpdb->postmeta} pm
                     WHERE pm.meta_key IN ('ds_post_views_count', 'post_views_count')
                     GROUP BY pm.post_id
                 ) v ON v.post_id = p.ID
                 WHERE p.post_author = %d
                 AND p.post_status = 'publish'
                 AND p.post_type = 'post'",
                $author_id
            ) );

            $total = $total ? (int) $total : 0;
            if ( $cache_enabled ) {
                if ( function_exists( 'developer_starter_cache_store' ) ) {
                    \developer_starter_cache_store( $cache_key, $total, HOUR_IN_SECONDS, 'developer_starter_user' );
                } else {
                    set_transient( $cache_key, $total, HOUR_IN_SECONDS );
                }
            }
        }
        
        return (int) $total;
    }
    
    /**
     * 获取作者收到的评论总数
     */
    public static function get_author_comment_count( $author_id ) {
        if ( function_exists( '\developer_starter_comments_feature_enabled' ) && ! \developer_starter_comments_feature_enabled() ) {
            return 0;
        }

        $cache_key = 'ds_author_comment_count_' . $author_id;
        $cache_enabled = true;
        if ( $cache_enabled ) {
            if ( function_exists( 'developer_starter_cache_fetch' ) ) {
                $count = \developer_starter_cache_fetch( $cache_key, 'developer_starter_user' );
            } else {
                $count = get_transient( $cache_key );
            }
        } else {
            $count = false;
        }
        
        if ( false === $count ) {
            global $wpdb;
            
            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(c.comment_ID)
                 FROM {$wpdb->comments} c
                 INNER JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID
                 WHERE p.post_author = %d
                 AND p.post_status = 'publish'
                 AND c.comment_approved = '1'",
                $author_id
            ) );
            
            $count = $count ? (int) $count : 0;
            if ( $cache_enabled ) {
                if ( function_exists( 'developer_starter_cache_store' ) ) {
                    \developer_starter_cache_store( $cache_key, $count, HOUR_IN_SECONDS, 'developer_starter_user' );
                } else {
                    set_transient( $cache_key, $count, HOUR_IN_SECONDS );
                }
            }
        }
        
        return (int) $count;
    }
    
    /**
     * 格式化数字为友好显示格式
     */
    public static function format_number( $number ) {
        $number = (int) $number;
        
        if ( $number >= 100000000 ) {
            return round( $number / 100000000, 1 ) . __( '亿', 'developer-starter' );
        } elseif ( $number >= 10000 ) {
            return round( $number / 10000, 1 ) . __( '万', 'developer-starter' );
        } elseif ( $number >= 1000 ) {
            return number_format( $number );
        }
        
        return (string) $number;
    }
    
    /**
     * 在作者页面加载专用样式
     */
    public static function enqueue_author_page_styles() {
        if ( ! is_author() ) {
            return;
        }
        
        wp_enqueue_style(
            'developer-starter-author-profile',
            DEVELOPER_STARTER_ASSETS . '/css/author-profile.css',
            array( 'developer-starter-main' ),
            DEVELOPER_STARTER_VERSION
        );
    }
    
    /**
     * 用户名脱敏
     */
    public static function mask_username( $username ) {
        $len = mb_strlen( $username, 'UTF-8' );
        if ( $len <= 2 ) {
            return $username . '***';
        } elseif ( $len <= 5 ) {
            return mb_substr( $username, 0, 1, 'UTF-8' ) . '***' . mb_substr( $username, -1, 1, 'UTF-8' );
        } else {
            return mb_substr( $username, 0, 2, 'UTF-8' ) . '***' . mb_substr( $username, -2, 2, 'UTF-8' );
        }
    }
}

}

namespace {
// 兼容全局函数定义

if ( ! function_exists( 'developer_starter_custom_avatar_url' ) ) {
    function developer_starter_custom_avatar_url( $url, $id_or_email, $args ) {
        return Developer_Starter\Core\User_Manager::custom_avatar_url( $url, $id_or_email, $args );
    }
}

if ( ! function_exists( 'developer_starter_get_user_avatar_url' ) ) {
    function developer_starter_get_user_avatar_url( $user_id, $size = 96 ) {
        return Developer_Starter\Core\User_Manager::get_user_avatar_url( $user_id, $size );
    }
}

if ( ! function_exists( 'developer_starter_get_user_public_profile_id' ) ) {
    function developer_starter_get_user_public_profile_id( $user_id, $create_if_missing = true ) {
        return Developer_Starter\Core\User_Manager::get_user_public_profile_id( $user_id, $create_if_missing );
    }
}

if ( ! function_exists( 'developer_starter_handle_submit_post' ) ) {
    function developer_starter_handle_submit_post() {
        Developer_Starter\Core\User_Manager::handle_submit_post();
    }
}

if ( ! function_exists( 'developer_starter_handle_delete_post' ) ) {
    function developer_starter_handle_delete_post() {
        Developer_Starter\Core\User_Manager::handle_delete_post();
    }
}

if ( ! function_exists( 'developer_starter_category_posts_per_page' ) ) {
    function developer_starter_category_posts_per_page( $query ) {
        Developer_Starter\Core\User_Manager::category_posts_per_page( $query );
    }
}

if ( ! function_exists( 'developer_starter_get_author_total_views' ) ) {
    function developer_starter_get_author_total_views( $author_id ) {
        return Developer_Starter\Core\User_Manager::get_author_total_views( $author_id );
    }
}

if ( ! function_exists( 'developer_starter_get_author_comment_count' ) ) {
    function developer_starter_get_author_comment_count( $author_id ) {
        return Developer_Starter\Core\User_Manager::get_author_comment_count( $author_id );
    }
}

if ( ! function_exists( 'developer_starter_format_number' ) ) {
    function developer_starter_format_number( $number ) {
        return Developer_Starter\Core\User_Manager::format_number( $number );
    }
}

if ( ! function_exists( 'developer_starter_enqueue_author_page_styles' ) ) {
    function developer_starter_enqueue_author_page_styles() {
        Developer_Starter\Core\User_Manager::enqueue_author_page_styles();
    }
}

if ( ! function_exists( 'developer_starter_mask_username' ) ) {
    function developer_starter_mask_username( $username ) {
        return Developer_Starter\Core\User_Manager::mask_username( $username );
    }
}
}
