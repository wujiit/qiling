<?php
/**
 * Template Manager - Handle saving and loading module templates
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Template_Manager {

    const POST_TYPE = 'ql_module_template';

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'wp_ajax_qiling_save_template', array( $this, 'ajax_save_template' ) );
        add_action( 'wp_ajax_qiling_get_templates', array( $this, 'ajax_get_templates' ) );
        add_action( 'wp_ajax_delete_template', array( $this, 'ajax_delete_template' ) );
    }

    public function register_post_type() {
        register_post_type( self::POST_TYPE, array(
            'labels' => array(
                'name' => __( '从模块库', 'developer-starter' ),
                'singular_name' => __( '模块模版', 'developer-starter' ),
            ),
            'public' => false,
            'show_ui' => false, // Hidden from admin menu
            'supports' => array( 'title', 'editor', 'custom-fields' ),
            'capability_type' => 'post',
        ));
    }

    /**
     * 构建当前用户可见的模板查询参数。
     *
     * @param array<string,mixed> $args 查询参数。
     * @return array<string,mixed>
     */
    public static function build_visible_template_query_args( $args = array() ) {
        $args = is_array( $args ) ? $args : array();
        $args = array_merge(
            array(
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            ),
            $args
        );

        if ( ! current_user_can( 'manage_options' ) ) {
            $args['author'] = get_current_user_id();
        }

        return $args;
    }

    /**
     * 获取模板文章对象。
     *
     * @param int $template_id 模板文章 ID。
     * @return \WP_Post|null
     */
    public static function get_template_post( $template_id ) {
        $template_id = absint( $template_id );
        if ( $template_id <= 0 ) {
            return null;
        }

        $template = get_post( $template_id );
        if ( ! ( $template instanceof \WP_Post ) || self::POST_TYPE !== $template->post_type ) {
            return null;
        }

        return $template;
    }

    /**
     * 当前用户是否可访问指定模板。
     *
     * 普通用户仅可访问自己创建的模板；管理员可访问全部模板。
     *
     * @param int|\WP_Post $template            模板对象或模板 ID。
     * @param string       $required_capability 需要的对象能力。
     * @return bool
     */
    public static function current_user_can_access_template_post( $template, $required_capability = 'read_post' ) {
        if ( is_numeric( $template ) ) {
            $template = self::get_template_post( $template );
        }

        if ( ! ( $template instanceof \WP_Post ) || self::POST_TYPE !== $template->post_type ) {
            return false;
        }

        $required_capability = in_array( $required_capability, array( 'read_post', 'edit_post', 'delete_post' ), true )
            ? $required_capability
            : 'read_post';

        if ( current_user_can( 'manage_options' ) ) {
            return current_user_can( $required_capability, $template->ID );
        }

        $current_user_id = get_current_user_id();
        if ( $current_user_id <= 0 || (int) $template->post_author !== $current_user_id ) {
            return false;
        }

        return current_user_can( $required_capability, $template->ID );
    }

    public function ajax_save_template() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
        $data_json = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
        $raw_data = isset( $_POST['raw_data'] ) ? wp_unslash( $_POST['raw_data'] ) : '';

        if ( empty( $title ) || empty( $type ) ) {
            wp_send_json_error( __( '信息不完整', 'developer-starter' ) );
        }

        // Handle raw serialized form data
        if ( empty( $data_json ) && ! empty( $raw_data ) ) {
            $parsed = array();
            parse_str( $raw_data, $parsed );
            
            // Expected structure: parsed['modules'][INDEX]['data']...
            if ( isset( $parsed['modules'] ) && is_array( $parsed['modules'] ) ) {
                $first_module = reset( $parsed['modules'] ); // Get first item (we only serialized one module)
                if ( isset( $first_module['data'] ) ) {
                    $data_array = $first_module['data'];
                    
                    // No other sanitation or modification. Raw data as is.
                    // $data_array = $this->simple_unslash_recursive($data_array);

                    $data_json = json_encode( $data_array, JSON_UNESCAPED_UNICODE );
                }
            }
        }

        if ( empty( $data_json ) ) {
            wp_send_json_error( __( '数据为空', 'developer-starter' ) );
        }

        // 验证 JSON
        $data = json_decode( $data_json, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( __( '数据格式错误', 'developer-starter' ) );
        }

        $post_data = array(
            'post_title'   => $title,
            'post_content' => wp_slash( $data_json ), // Save JSON in content (slashed to prevent stripping)
            'post_status'  => 'publish',
            'post_type'    => self::POST_TYPE,
            'post_author'  => get_current_user_id(),
            'meta_input'   => array(
                '_ql_template_type' => $type,
            )
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( $post_id->get_error_message() );
        }

        wp_send_json_success( array( 'id' => $post_id, 'message' => __( '模版保存成功', 'developer-starter' ) ) );
    }

    /**
     * Simple recursive unslash to handle PHP magic quotes.
     * Absolutely NO other modifications.
     */
    /*
    private function simple_unslash_recursive($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->simple_unslash_recursive($value);
            }
            return $data;
        } else {
            return wp_unslash($data);
        }
    }
    */

    public function ajax_get_templates() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $args = self::build_visible_template_query_args();

        $query = new \WP_Query( $args );
        $templates = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $template_id = get_the_ID();
                if ( ! self::current_user_can_access_template_post( $template_id, 'read_post' ) ) {
                    continue;
                }
                $type = get_post_meta( get_the_ID(), '_ql_template_type', true );
                // Validate if module type still exists
                $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
                $module_obj = $module_manager->get_module( $type );
                $type_name = $module_obj ? $module_obj->get_name() : $type;

                $templates[] = array(
                    'id' => $template_id,
                    'title' => get_the_title(),
                    'type' => $type,
                    'type_name' => $type_name,
                    'date' => get_the_date( get_option( 'date_format' ) ),
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success( $templates );
    }

    public function ajax_delete_template() {
        check_ajax_referer( 'developer_starter_modules_nonce', 'nonce' );

        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
        if ( ! $id ) {
            wp_send_json_error( __( 'ID无效', 'developer-starter' ) );
        }

        $template = self::get_template_post( $id );
        if ( ! $template ) {
            wp_send_json_error( __( '无此模版', 'developer-starter' ) );
        }

        if ( ! self::current_user_can_access_template_post( $template, 'delete_post' ) ) {
            wp_send_json_error( __( '权限不足', 'developer-starter' ) );
        }

        $result = wp_delete_post( $template->ID, true );

        if ( $result ) {
            wp_send_json_success( __( '删除成功', 'developer-starter' ) );
        } else {
            wp_send_json_error( __( '删除失败', 'developer-starter' ) );
        }
    }
}
