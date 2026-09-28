<?php
/**
 * 用户认证页面生命周期服务。
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auth_Pages_Service {

    /**
     * @var callable|null
     */
    private $option_callback;

    /**
     * @var string
     */
    private $option_name = 'developer_starter_options';

    /**
     * @param array<string,mixed> $args 配置项。
     */
    public function __construct( $args = array() ) {
        $this->option_callback = isset( $args['option_callback'] ) && is_callable( $args['option_callback'] )
            ? $args['option_callback']
            : null;
        $this->option_name = isset( $args['option_name'] ) && is_string( $args['option_name'] ) && $args['option_name'] !== ''
            ? $args['option_name']
            : $this->option_name;
    }

    /**
     * 更新个人中心页面 Option。
     *
     * @param int           $post_id Post ID。
     * @param \WP_Post|null $post Post 对象。
     * @return void
     */
    public function update_account_page_option( $post_id, $post ) {
        if ( ! ( $post instanceof \WP_Post ) || 'page' !== $post->post_type ) {
            return;
        }

        $template = get_post_meta( $post_id, '_wp_page_template', true );
        if ( 'templates/template-account.php' === $template ) {
            update_option( 'developer_starter_account_page_id', $post_id );
        }
    }

    /**
     * 重定向默认登录/注册入口到主题页面。
     *
     * @return void
     */
    public function redirect_default_auth_pages() {
        global $pagenow;

        if ( 'wp-login.php' !== $pagenow || is_user_logged_in() ) {
            return;
        }

        $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['action'] ) ) : 'login';

        if ( 'register' === $action ) {
            $register_page_id = (int) $this->get_option( 'register_page_id', '' );
            if ( $register_page_id > 0 ) {
                wp_safe_redirect( get_permalink( $register_page_id ) );
                exit;
            }

            status_header( 403 );
            wp_die(
                esc_html__( '本站已关闭默认注册入口，请使用前台注册页面。', 'developer-starter' ),
                esc_html__( '注册已受限', 'developer-starter' ),
                array( 'response' => 403 )
            );
        }

        if ( ! $this->get_option( 'custom_auth_enable', '' ) ) {
            return;
        }

        switch ( $action ) {
            case 'lostpassword':
                $page_id = absint( $this->get_option( 'forgot_password_page_id', '' ) );
                if ( $page_id > 0 ) {
                    $page_url = get_permalink( $page_id );
                    if ( is_string( $page_url ) && '' !== $page_url ) {
                        wp_safe_redirect( $page_url );
                        exit;
                    }
                }
                break;
            case 'rp':
            case 'resetpass':
                $page_id = absint( $this->get_option( 'forgot_password_page_id', '' ) );
                if ( $page_id > 0 ) {
                    $page_url = get_permalink( $page_id );
                    if ( ! is_string( $page_url ) || '' === $page_url ) {
                        break;
                    }
                    $redirect_url = add_query_arg(
                        array(
                            'action' => 'reset',
                            'key'    => isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['key'] ) ) : '',
                            'login'  => isset( $_GET['login'] ) ? sanitize_user( wp_unslash( (string) $_GET['login'] ) ) : '',
                        ),
                        $page_url
                    );
                    wp_safe_redirect( $redirect_url );
                    exit;
                }
                break;
            default:
                $page_id = $this->get_option( 'login_page_id', '' );
                if ( $page_id ) {
                    wp_safe_redirect( get_permalink( $page_id ) );
                    exit;
                }
                break;
        }
    }

    /**
     * 将系统注册 URL 替换为主题注册页。
     *
     * @param string $register_url WordPress 默认注册 URL。
     * @return string
     */
    public function filter_register_url( $register_url ) {
        $page_id = (int) $this->get_option( 'register_page_id', '' );
        if ( $page_id > 0 ) {
            $permalink = get_permalink( $page_id );
            if ( $permalink ) {
                return $permalink;
            }
        }

        return $register_url;
    }

    /**
     * 并发防重入互斥锁 Key 与过期时间（秒）。
     */
    const AUTH_PAGES_MUTEX_KEY = 'developer_starter_auth_pages_lock';
    const AUTH_PAGES_MUTEX_TTL = 30;

    /**
     * 自动创建认证页面（具备防并发重入互斥锁与多维度查重机制）。
     *
     * @return void
     */
    public function create_auth_pages() {
        // 1. 并发互斥锁保护，防止多进程同时进入导致重复创建
        if ( get_transient( self::AUTH_PAGES_MUTEX_KEY ) ) {
            return;
        }
        set_transient( self::AUTH_PAGES_MUTEX_KEY, 1, self::AUTH_PAGES_MUTEX_TTL );

        try {
            $pages = array(
                'login'           => array(
                    'title'      => __( '用户登录', 'developer-starter' ),
                    'template'   => 'templates/template-login.php',
                    'option_key' => 'login_page_id',
                ),
                'register'        => array(
                    'title'      => __( '用户注册', 'developer-starter' ),
                    'template'   => 'templates/template-register.php',
                    'option_key' => 'register_page_id',
                ),
                'forgot-password' => array(
                    'title'      => __( '找回密码', 'developer-starter' ),
                    'template'   => 'templates/template-forgot-password.php',
                    'option_key' => 'forgot_password_page_id',
                ),
                'account-center'  => array(
                    'title'             => __( '个人中心', 'developer-starter' ),
                    'template'          => 'templates/template-account.php',
                    'global_option_key' => 'developer_starter_account_page_id',
                ),
            );

            $options = get_option( $this->option_name, array() );
            if ( ! is_array( $options ) ) {
                $options = array();
            }

            foreach ( $pages as $slug => $page ) {
                $existing_id = $this->find_existing_auth_page( $slug, $page, $options );

                if ( $existing_id > 0 ) {
                    if ( ! empty( $page['option_key'] ) ) {
                        $options[ $page['option_key'] ] = $existing_id;
                    }
                    if ( ! empty( $page['global_option_key'] ) ) {
                        update_option( $page['global_option_key'], $existing_id );
                    }
                    continue;
                }

                // 原子性创建页面：直接在 post 属性中携带 page_template，避免中断导致无模板孤儿页面
                $page_id = wp_insert_post(
                    array(
                        'post_title'    => $page['title'],
                        'post_name'     => $slug,
                        'post_status'   => 'publish',
                        'post_type'     => 'page',
                        'post_content'  => '',
                        'page_template' => $page['template'],
                    )
                );

                if ( $page_id && ! is_wp_error( $page_id ) ) {
                    $page_id = (int) $page_id;
                    update_post_meta( $page_id, '_wp_page_template', $page['template'] );
                    if ( ! empty( $page['option_key'] ) ) {
                        $options[ $page['option_key'] ] = $page_id;
                    }
                    if ( ! empty( $page['global_option_key'] ) ) {
                        update_option( $page['global_option_key'], $page_id );
                    }
                }
            }

            update_option( $this->option_name, $options );
            update_option( 'developer_starter_auth_pages_created', 1, false );
        } finally {
            delete_transient( self::AUTH_PAGES_MUTEX_KEY );
        }
    }

    /**
     * 兜底补建个人中心页（增加节流控制与防刷保护）。
     *
     * @return void
     */
    public function maybe_backfill_account_page() {
        if ( wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // 节流控制：避免每次 admin_init 都频繁全量查询数据库
        $throttle_key = 'developer_starter_auth_backfill_throttle';
        if ( get_transient( $throttle_key ) ) {
            return;
        }

        $account_page_id = (int) get_option( 'developer_starter_account_page_id', 0 );
        if ( $account_page_id > 0 && get_post_status( $account_page_id ) && 'trash' !== get_post_status( $account_page_id ) ) {
            set_transient( $throttle_key, 1, 12 * HOUR_IN_SECONDS );
            return;
        }

        $existing_id = $this->find_page_by_template( 'templates/template-account.php' );
        if ( $existing_id > 0 ) {
            update_option( 'developer_starter_account_page_id', $existing_id );
            set_transient( $throttle_key, 1, 12 * HOUR_IN_SECONDS );
            return;
        }

        // 检查是否已有名为“个人中心”的既有页面
        $existing_by_title = get_posts(
            array(
                'post_type'      => 'page',
                'title'          => __( '个人中心', 'developer-starter' ),
                'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
            )
        );
        if ( ! empty( $existing_by_title ) && absint( $existing_by_title[0] ) > 0 ) {
            $found_id = absint( $existing_by_title[0] );
            $this->ensure_page_template( $found_id, 'templates/template-account.php' );
            update_option( 'developer_starter_account_page_id', $found_id );
            set_transient( $throttle_key, 1, 12 * HOUR_IN_SECONDS );
            return;
        }

        // 确实缺少时才调用创建，并设置 5 分钟重试节流，防止卡顿时雪崩重试
        set_transient( $throttle_key, 1, 300 );
        $this->create_auth_pages();
    }

    /**
     * 多维度查找已存在的认证页面，杜绝重复创建。
     *
     * 优先级：
     * 1. 检查 Option 中已绑定的有效 Page ID
     * 2. 检查页面模板匹配（含完整路径与文件名）
     * 3. 检查页面标题匹配（如“用户登录”）
     * 4. 检查页面别名匹配（如“login”）
     *
     * @param string              $slug 页面别名。
     * @param array<string,mixed> $page 页面定义。
     * @param array<string,mixed> $options 当前主题设置。
     * @return int 找到的有效页面 ID，未找到返回 0。
     */
    private function find_existing_auth_page( $slug, $page, $options ) {
        // 1. 优先检查配置中已记录的有效 Post ID
        $configured_id = 0;
        if ( ! empty( $page['option_key'] ) && isset( $options[ $page['option_key'] ] ) ) {
            $configured_id = absint( $options[ $page['option_key'] ] );
        } elseif ( ! empty( $page['global_option_key'] ) ) {
            $configured_id = absint( get_option( $page['global_option_key'], 0 ) );
        }

        if ( $configured_id > 0 ) {
            $status = get_post_status( $configured_id );
            if ( $status && 'trash' !== $status ) {
                $this->ensure_page_template( $configured_id, $page['template'] );
                return $configured_id;
            }
        }

        // 2. 根据页面模板查找（包括完整相对路径与文件名兼容）
        $existing_id = $this->find_page_by_template( $page['template'] );
        if ( $existing_id > 0 ) {
            return $existing_id;
        }

        // 3. 根据页面标题查重（如“用户登录”/“个人中心”）—— 防止同名页面重复创建
        $pages_by_title = get_posts(
            array(
                'post_type'      => 'page',
                'title'          => $page['title'],
                'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'orderby'        => 'ID',
                'order'          => 'ASC',
            )
        );
        if ( ! empty( $pages_by_title ) && absint( $pages_by_title[0] ) > 0 ) {
            $found_id = absint( $pages_by_title[0] );
            $this->ensure_page_template( $found_id, $page['template'] );
            return $found_id;
        }

        // 4. 根据页面别名 Slug 查找
        $existing = get_page_by_path( $slug, OBJECT, 'page' );
        if ( $existing instanceof \WP_Post && 'trash' !== $existing->post_status ) {
            $this->ensure_page_template( $existing->ID, $page['template'] );
            return (int) $existing->ID;
        }

        return 0;
    }

    /**
     * 确保页面已绑定正确的模板。
     *
     * @param int    $post_id 页面 ID。
     * @param string $template 模板相对路径。
     * @return void
     */
    private function ensure_page_template( $post_id, $template ) {
        if ( $post_id <= 0 || '' === $template ) {
            return;
        }

        $current = get_post_meta( $post_id, '_wp_page_template', true );
        if ( $current !== $template ) {
            update_post_meta( $post_id, '_wp_page_template', $template );
        }
    }

    /**
     * 根据页面模板查找页面（支持完整相对路径与仅文件名双重兼容）。
     *
     * @param string $template 模板文件路径。
     * @return int
     */
    private function find_page_by_template( $template ) {
        $templates = array( $template );
        $basename  = basename( $template );
        if ( $basename !== $template ) {
            $templates[] = $basename;
        }

        $pages = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                'meta_query'     => array(
                    array(
                        'key'     => '_wp_page_template',
                        'value'   => $templates,
                        'compare' => 'IN',
                    ),
                ),
                'numberposts'    => 1,
                'fields'         => 'ids',
                'orderby'        => 'ID',
                'order'          => 'ASC',
            )
        );

        if ( ! empty( $pages ) ) {
            return (int) $pages[0];
        }

        return 0;
    }

    /**
     * 统计当前冗余的重复系统认证页面总数。
     *
     * @return int
     */
    public function count_duplicate_auth_pages() {
        $definitions = array(
            'login'           => array(
                'title'    => __( '用户登录', 'developer-starter' ),
                'template' => 'templates/template-login.php',
            ),
            'register'        => array(
                'title'    => __( '用户注册', 'developer-starter' ),
                'template' => 'templates/template-register.php',
            ),
            'forgot-password' => array(
                'title'    => __( '找回密码', 'developer-starter' ),
                'template' => 'templates/template-forgot-password.php',
            ),
            'account-center'  => array(
                'title'    => __( '个人中心', 'developer-starter' ),
                'template' => 'templates/template-account.php',
            ),
        );

        $total_duplicates = 0;
        foreach ( $definitions as $def ) {
            $ids = array();
            $by_template = get_posts(
                array(
                    'post_type'      => 'page',
                    'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                    'meta_query'     => array(
                        array(
                            'key'     => '_wp_page_template',
                            'value'   => array( $def['template'], basename( $def['template'] ) ),
                            'compare' => 'IN',
                        ),
                    ),
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                )
            );
            if ( ! empty( $by_template ) ) {
                $ids = array_merge( $ids, array_map( 'absint', $by_template ) );
            }

            $by_title = get_posts(
                array(
                    'post_type'      => 'page',
                    'title'          => $def['title'],
                    'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                )
            );
            if ( ! empty( $by_title ) ) {
                $ids = array_merge( $ids, array_map( 'absint', $by_title ) );
            }

            $unique_ids = array_unique( array_filter( $ids ) );
            if ( count( $unique_ids ) > 1 ) {
                $total_duplicates += ( count( $unique_ids ) - 1 );
            }
        }

        return $total_duplicates;
    }

    /**
     * 清理重复的系统认证页面（保留最早创建的一套有效页面，将其余重复页面移至回收站）。
     *
     * @return array{success: bool, trashed_count: int, kept_pages: array<string,int>, message: string}
     */
    public function cleanup_duplicate_auth_pages() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array(
                'success'       => false,
                'trashed_count' => 0,
                'kept_pages'    => array(),
                'message'       => __( '权限不足', 'developer-starter' ),
            );
        }

        $definitions = array(
            'login'           => array(
                'title'             => __( '用户登录', 'developer-starter' ),
                'template'          => 'templates/template-login.php',
                'option_key'        => 'login_page_id',
            ),
            'register'        => array(
                'title'             => __( '用户注册', 'developer-starter' ),
                'template'          => 'templates/template-register.php',
                'option_key'        => 'register_page_id',
            ),
            'forgot-password' => array(
                'title'             => __( '找回密码', 'developer-starter' ),
                'template'          => 'templates/template-forgot-password.php',
                'option_key'        => 'forgot_password_page_id',
            ),
            'account-center'  => array(
                'title'             => __( '个人中心', 'developer-starter' ),
                'template'          => 'templates/template-account.php',
                'global_option_key' => 'developer_starter_account_page_id',
            ),
        );

        $options = get_option( $this->option_name, array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        $trashed_count = 0;
        $kept_pages    = array();

        foreach ( $definitions as $key => $def ) {
            $configured_id = 0;
            if ( ! empty( $def['option_key'] ) && isset( $options[ $def['option_key'] ] ) ) {
                $configured_id = absint( $options[ $def['option_key'] ] );
            } elseif ( ! empty( $def['global_option_key'] ) ) {
                $configured_id = absint( get_option( $def['global_option_key'], 0 ) );
            }

            $candidate_ids = array();

            // 按模板查找
            $by_template = get_posts(
                array(
                    'post_type'      => 'page',
                    'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                    'meta_query'     => array(
                        array(
                            'key'     => '_wp_page_template',
                            'value'   => array( $def['template'], basename( $def['template'] ) ),
                            'compare' => 'IN',
                        ),
                    ),
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                )
            );
            if ( ! empty( $by_template ) ) {
                $candidate_ids = array_merge( $candidate_ids, array_map( 'absint', $by_template ) );
            }

            // 按标题查找
            $by_title = get_posts(
                array(
                    'post_type'      => 'page',
                    'title'          => $def['title'],
                    'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                )
            );
            if ( ! empty( $by_title ) ) {
                $candidate_ids = array_merge( $candidate_ids, array_map( 'absint', $by_title ) );
            }

            $candidate_ids = array_values( array_unique( array_filter( $candidate_ids ) ) );
            if ( empty( $candidate_ids ) ) {
                continue;
            }

            // 排序：优先保留当前配置的有效 ID，否则保留最早创建的最小 ID
            sort( $candidate_ids, SORT_NUMERIC );
            $canonical_id = 0;
            if ( $configured_id > 0 && in_array( $configured_id, $candidate_ids, true ) && 'trash' !== get_post_status( $configured_id ) ) {
                $canonical_id = $configured_id;
            } else {
                $canonical_id = $candidate_ids[0];
            }

            // 确保保留的页面模板绑定正确
            $this->ensure_page_template( $canonical_id, $def['template'] );
            $kept_pages[ $key ] = $canonical_id;

            // 同步选项
            if ( ! empty( $def['option_key'] ) ) {
                $options[ $def['option_key'] ] = $canonical_id;
            }
            if ( ! empty( $def['global_option_key'] ) ) {
                update_option( $def['global_option_key'], $canonical_id );
            }

            // 将多余的重复候选页面移至回收站
            foreach ( $candidate_ids as $cid ) {
                if ( $cid === $canonical_id ) {
                    continue;
                }
                wp_trash_post( $cid );
                $trashed_count++;
            }
        }

        update_option( $this->option_name, $options );
        delete_transient( 'developer_starter_auth_backfill_throttle' );

        return array(
            'success'       => true,
            'trashed_count' => $trashed_count,
            'kept_pages'    => $kept_pages,
            'message'       => sprintf( __( '清理完成！共将 %d 个多余的重复页面移入回收站，系统已自动保留主页面并绑定好对应设置。', 'developer-starter' ), $trashed_count ),
        );
    }

    /**
     * 读取主题设置。
     *
     * @param string $key 选项键名。
     * @param mixed  $default 默认值。
     * @return mixed
     */
    private function get_option( $key, $default = '' ) {
        if ( is_callable( $this->option_callback ) ) {
            return call_user_func( $this->option_callback, $key, $default );
        }

        return function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( $key, $default )
            : $default;
    }
}
