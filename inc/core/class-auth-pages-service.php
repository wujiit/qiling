<?php
/**
 * Auth pages lifecycle service.
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
     * 自动创建认证页面。
     *
     * @return void
     */
    public function create_auth_pages() {
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
            $existing_id = $this->find_page_by_template( $page['template'] );
            if ( ! $existing_id ) {
                $existing = get_page_by_path( $slug );
                if ( $existing instanceof \WP_Post ) {
                    $existing_template = get_post_meta( $existing->ID, '_wp_page_template', true );
                    if ( $existing_template === $page['template'] ) {
                        $existing_id = $existing->ID;
                    }
                }
            }

            if ( $existing_id ) {
                if ( ! empty( $page['option_key'] ) ) {
                    $options[ $page['option_key'] ] = $existing_id;
                }
                if ( ! empty( $page['global_option_key'] ) ) {
                    update_option( $page['global_option_key'], $existing_id );
                }
                continue;
            }

            $page_id = wp_insert_post(
                array(
                    'post_title'   => $page['title'],
                    'post_name'    => $slug,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_content' => '',
                )
            );

            if ( $page_id && ! is_wp_error( $page_id ) ) {
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
    }

    /**
     * 兜底补建个人中心页。
     *
     * @return void
     */
    public function maybe_backfill_account_page() {
        if ( wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $account_page_id = (int) get_option( 'developer_starter_account_page_id', 0 );
        if ( $account_page_id > 0 && get_post_status( $account_page_id ) ) {
            return;
        }

        $existing_id = $this->find_page_by_template( 'templates/template-account.php' );
        if ( $existing_id > 0 ) {
            update_option( 'developer_starter_account_page_id', $existing_id );
            return;
        }

        $this->create_auth_pages();
    }

    /**
     * 根据页面模板查找页面。
     *
     * @param string $template 模板文件。
     * @return int
     */
    private function find_page_by_template( $template ) {
        $pages = get_posts(
            array(
                'post_type'   => 'page',
                'post_status' => 'any',
                'meta_key'    => '_wp_page_template',
                'meta_value'  => $template,
                'numberposts' => 1,
                'fields'      => 'ids',
            )
        );

        if ( ! empty( $pages ) ) {
            return (int) $pages[0];
        }

        return 0;
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
