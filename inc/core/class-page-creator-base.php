<?php
/**
 * 通用页面创建器基类。
 *
 * 单模板页面创建器只需要声明模板、AJAX action、填充标记，并返回默认模块数组。
 *
 * @package Developer_Starter
 * @since 2.5.7
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 通用页面创建器基类。
 */
abstract class Page_Creator_Base {

    /**
     * 页面模板路径。
     *
     * @var string
     */
    protected const TEMPLATE = '';

    /**
     * 后台手动填充模块的 AJAX action。
     *
     * @var string
     */
    protected const AJAX_ACTION = '';

    /**
     * AJAX nonce action。默认复用 AJAX action。
     *
     * @var string
     */
    protected const NONCE_ACTION = '';

    /**
     * 模块已填充标记 meta key。
     *
     * @var string
     */
    protected const FILLED_META_KEY = '';

    /**
     * 构造函数。
     */
    public function __construct() {
        add_action( 'save_post', array( $this, 'on_page_save' ), 99, 2 );

        $ajax_action = $this->get_ajax_action();
        if ( '' !== $ajax_action ) {
            add_action( 'wp_ajax_' . $ajax_action, array( $this, 'ajax_fill_modules' ) );
        }
    }

    /**
     * 页面保存时自动填充默认模块。
     *
     * @param int     $post_id 页面 ID。
     * @param WP_Post $post    页面对象。
     * @return void
     */
    public function on_page_save( $post_id, $post ) {
        if ( ! is_object( $post ) || ! isset( $post->post_type ) || 'page' !== $post->post_type ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $post_id = absint( $post_id );
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $template = get_post_meta( $post_id, '_wp_page_template', true );
        if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
            $template = developer_starter_normalize_page_template_slug( $template );
        }

        if ( $template !== $this->get_template_slug() ) {
            return;
        }

        if ( ! $this->page_has_modules( $post_id ) ) {
            $this->fill_page_modules( $post_id );
        }
    }

    /**
     * AJAX 手动填充模块。
     *
     * @return void
     */
    public function ajax_fill_modules() {
        check_ajax_referer( $this->get_nonce_action(), 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'developer-starter' ) ) );
        }

        $this->fill_page_modules( $post_id );

        wp_send_json_success( array( 'message' => $this->get_ajax_success_message() ) );
    }

    /**
     * 设置页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return void
     */
    public function set_default_modules( $page_id ) {
        $this->persist_default_modules( $page_id, $this->get_default_modules( $page_id ) );
    }

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        return array();
    }

    /**
     * 持久化页面默认模块。
     *
     * @param int   $page_id 页面 ID。
     * @param array $modules 模块配置。
     * @return void
     */
    protected function persist_default_modules( $page_id, $modules ) {
        self::persist_default_modules_for_creator(
            $page_id,
            $modules,
            get_class( $this ),
            $this->get_template_slug()
        );
    }

    /**
     * 为页面创建器持久化默认模块。
     *
     * @param int    $page_id       页面 ID。
     * @param array  $modules       模块配置。
     * @param string $creator_class 页面创建器类名。
     * @param string $template_slug 页面模板路径。
     * @return void
     */
    public static function persist_default_modules_for_creator( $page_id, $modules, $creator_class = '', $template_slug = '' ) {
        $page_id = absint( $page_id );
        if ( ! $page_id || ! is_array( $modules ) ) {
            return;
        }

        $creator_class = is_string( $creator_class ) ? $creator_class : '';
        $template_slug = is_string( $template_slug ) ? $template_slug : '';

        $modules = apply_filters(
            'developer_starter_page_creator_default_modules',
            array_values( $modules ),
            $page_id,
            $creator_class,
            $template_slug
        );

        if ( ! is_array( $modules ) ) {
            return;
        }

        update_post_meta( $page_id, '_developer_starter_modules', array_values( $modules ) );
    }

    /**
     * 获取页面模板路径。
     *
     * @return string
     */
    protected function get_template_slug() {
        return (string) static::TEMPLATE;
    }

    /**
     * 获取 AJAX action。
     *
     * @return string
     */
    protected function get_ajax_action() {
        return (string) static::AJAX_ACTION;
    }

    /**
     * 获取 nonce action。
     *
     * @return string
     */
    protected function get_nonce_action() {
        $nonce_action = (string) static::NONCE_ACTION;
        return '' !== $nonce_action ? $nonce_action : $this->get_ajax_action();
    }

    /**
     * 获取填充标记 meta key。
     *
     * @return string
     */
    protected function get_filled_meta_key() {
        return (string) static::FILLED_META_KEY;
    }

    /**
     * 获取 AJAX 成功提示。
     *
     * @return string
     */
    protected function get_ajax_success_message() {
        return __( '模块已填充，请刷新页面', 'developer-starter' );
    }

    /**
     * 判断页面是否已有模块配置。
     *
     * @param int $post_id 页面 ID。
     * @return bool
     */
    protected function page_has_modules( $post_id ) {
        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );

        return ! empty( $modules ) && is_array( $modules ) && count( $modules ) > 0;
    }

    /**
     * 填充默认模块并写入标记。
     *
     * @param int $post_id 页面 ID。
     * @return void
     */
    protected function fill_page_modules( $post_id ) {
        $this->set_default_modules( $post_id );

        $filled_meta_key = $this->get_filled_meta_key();
        if ( '' !== $filled_meta_key ) {
            update_post_meta( $post_id, $filled_meta_key, '1' );
        }
    }
}
