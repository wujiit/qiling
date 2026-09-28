<?php
/**
 * 产品内容 AJAX 加载器
 *
 * 处理产品展示弹窗中文章详情正文的动态获取。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AJAX_Product_Loader {

    /**
     * 构造函数
     */
    public function __construct() {
        add_action( 'wp_ajax_ds_fetch_product_content', array( $this, 'fetch_content' ) );
        add_action( 'wp_ajax_nopriv_ds_fetch_product_content', array( $this, 'fetch_content' ) );
    }

    /**
     * 获取文章正文内容
     */
    public function fetch_content() {
        // 公共接口：强制校验 Nonce 以抵御恶意流量。
        if ( ! check_ajax_referer( 'ds_product_nonce', 'nonce', false ) ) {
            wp_send_json_error(
                array( 'message' => __( '安全验证失败，请刷新后重试', 'developer-starter' ) ),
                403
            );
        }

        // 针对未登录访客流量的可选全局访问频次限制。
        if ( ! is_user_logged_in() && function_exists( 'developer_starter_is_public_rate_limit_enabled' ) && developer_starter_is_public_rate_limit_enabled() ) {
            $window = function_exists( 'developer_starter_get_rate_limit_window' ) ? developer_starter_get_rate_limit_window() : 60;
            $max = function_exists( 'developer_starter_get_option' ) ? intval( developer_starter_get_option( 'request_rate_limit_product_max', 60 ) ) : 60;
            $max = max( 5, min( 500, $max ) );

            if ( function_exists( 'developer_starter_is_rate_limited' ) && developer_starter_is_rate_limited( 'public_product_modal', $max, $window ) ) {
                wp_send_json_error(
                    array( 'message' => __( '请求过于频繁，请稍后再试', 'developer-starter' ) ),
                    429
                );
            }
        }

        $post_id = $this->get_posted_absint( 'post_id' );
        $source_id = $this->get_posted_absint( 'source_id' );
        $module_key = $this->get_posted_text( 'module_key' );

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( '无效的产品 ID', 'developer-starter' ) ) );
        }

        $post = get_post( $post_id );

        if ( ! $this->is_allowed_product_post( $post ) ) {
            wp_send_json_error( array( 'message' => __( '未找到该产品', 'developer-starter' ) ) );
        }

        if ( ! $this->is_allowed_product_for_module_source( $post_id, $source_id, $module_key ) ) {
            wp_send_json_error( array( 'message' => __( '该产品未在当前模块中配置', 'developer-starter' ) ), 403 );
        }

        // 执行标准短代码与媒体嵌入解析以处理正文内容。
        $previous_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
        $GLOBALS['post'] = $post;
        setup_postdata( $post );
        $content = apply_filters( 'the_content', $post->post_content );
        wp_reset_postdata();
        if ( $previous_post ) {
            $GLOBALS['post'] = $previous_post;
        }

        // 包裹在干净的容器标签内。
        $html = '<div class="ds-product-modal-content entry-content">';
        $html .= $content;
        $html .= '</div>';

        wp_send_json_success( array(
            'html' => $html,
            'title' => get_the_title( $post_id ),
            'link' => get_permalink( $post_id )
        ) );
    }

    /**
     * 为产品模块 AJAX 请求边界构建稳定的公共模块标识。
     *
     * @param int        $source_id Source page ID.
     * @param array<int> $post_ids  Product detail post IDs configured in one module.
     * @return string
     */
    public static function build_module_key( $source_id, $post_ids ) {
        $source_id = absint( $source_id );
        $post_ids = self::normalize_allowed_post_ids( $post_ids );

        if ( $source_id <= 0 || empty( $post_ids ) ) {
            return '';
        }

        return wp_hash( 'products|' . $source_id . '|' . implode( ',', $post_ids ), 'nonce' );
    }

    /**
     * 从产品展示模块数据中提取已配置的详情文章 ID。
     *
     * @param array<string,mixed> $data Module data.
     * @return array<int>
     */
    public static function extract_allowed_post_ids_from_products_data( $data ) {
        $data = is_array( $data ) ? $data : array();
        $items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
        $post_ids = array();

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) || empty( $item['post_id'] ) ) {
                continue;
            }

            $post_ids[] = absint( $item['post_id'] );
        }

        return self::normalize_allowed_post_ids( $post_ids );
    }

    /**
     * 规范化配置的产品详情文章 ID 列表。
     *
     * @param array<int|string> $post_ids Post IDs.
     * @return array<int>
     */
    public static function normalize_allowed_post_ids( $post_ids ) {
        if ( ! is_array( $post_ids ) ) {
            return array();
        }

        $post_ids = array_values( array_filter( array_map( 'absint', $post_ids ) ) );
        $post_ids = array_values( array_unique( $post_ids ) );
        sort( $post_ids, SORT_NUMERIC );

        return $post_ids;
    }

    /**
     * 从 AJAX POST 载荷中读取整数值。
     *
     * @param string $key Request key.
     * @return int
     */
    private function get_posted_absint( $key ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return 0;
        }

        $value = wp_unslash( $_POST[ $key ] );
        if ( ! is_scalar( $value ) ) {
            return 0;
        }

        return absint( $value );
    }

    /**
     * 从 AJAX POST 载荷中安全读取文本值。
     *
     * @param string $key Request key.
     * @return string
     */
    private function get_posted_text( $key ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return '';
        }

        $value = wp_unslash( $_POST[ $key ] );
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        return sanitize_text_field( (string) $value );
    }

    /**
     * 校验目标文章是否为公开的产品详情来源。
     *
     * @param mixed $post Post object candidate.
     * @return bool
     */
    private function is_allowed_product_post( $post ) {
        if ( ! $post instanceof \WP_Post ) {
            return false;
        }

        if ( 'publish' !== $post->post_status ) {
            return false;
        }

        if ( ! in_array( sanitize_key( (string) $post->post_type ), $this->get_allowed_product_post_types(), true ) ) {
            return false;
        }

        if ( function_exists( 'is_post_publicly_viewable' ) && ! is_post_publicly_viewable( $post ) ) {
            return false;
        }

        if ( post_password_required( $post ) ) {
            return false;
        }

        return true;
    }

    /**
     * 严格限制目标内容必须为来源页面产品模块中显式配置的文章 ID。
     *
     * @param int    $post_id    Requested product detail post ID.
     * @param int    $source_id  Source page ID.
     * @param string $module_key Products module key from the rendered page.
     * @return bool
     */
    private function is_allowed_product_for_module_source( $post_id, $source_id, $module_key ) {
        $post_id = absint( $post_id );
        $source_id = absint( $source_id );
        $module_key = sanitize_text_field( (string) $module_key );

        if ( $post_id <= 0 || $source_id <= 0 || '' === $module_key ) {
            return false;
        }

        if ( ! $this->is_allowed_source_post( $source_id ) ) {
            return false;
        }

        $allowed_ids = $this->get_allowed_product_ids_for_request( $source_id, $module_key );
        return in_array( $post_id, $allowed_ids, true );
    }

    /**
     * 获取产品弹窗支持的公开目标文章类型。
     *
     * @return array<int,string>
     */
    private function get_allowed_product_post_types() {
        $post_types = apply_filters(
            'developer_starter_ajax_product_content_allowed_post_types',
            array( 'post', 'ql_product' )
        );

        if ( ! is_array( $post_types ) ) {
            $post_types = array( 'post' );
        }

        $post_types = array_values( array_filter( array_unique( array_map( 'sanitize_key', $post_types ) ) ) );
        return array_values(
            array_filter(
                $post_types,
                static function( $post_type ) {
                    $object = get_post_type_object( $post_type );
                    return $object && ! empty( $object->public );
                }
            )
        );
    }

    /**
     * 获取可挂载产品模块配置的来源文章类型。
     *
     * @return array<int,string>
     */
    private function get_allowed_source_post_types() {
        $post_types = array( 'page' );

        $post_types = apply_filters( 'developer_starter_ajax_product_content_source_post_types', $post_types );
        if ( ! is_array( $post_types ) ) {
            $post_types = array( 'page' );
        }

        return array_values( array_filter( array_unique( array_map( 'sanitize_key', $post_types ) ) ) );
    }

    /**
     * 校验来源页面是否具备开放模块产品数据的权限。
     *
     * @param int $source_id Source page ID.
     * @return bool
     */
    private function is_allowed_source_post( $source_id ) {
        $source = get_post( absint( $source_id ) );
        if ( ! $source instanceof \WP_Post ) {
            return false;
        }

        if ( ! in_array( sanitize_key( (string) $source->post_type ), $this->get_allowed_source_post_types(), true ) ) {
            return false;
        }

        if ( 'publish' !== $source->post_status ) {
            return false;
        }

        if ( function_exists( 'is_post_publicly_viewable' ) && ! is_post_publicly_viewable( $source ) ) {
            return false;
        }

        if ( post_password_required( $source ) ) {
            return false;
        }

        return true;
    }

    /**
     * 从来源页面的模块实例中解析允许访问的产品 ID。
     *
     * @param int    $source_id  Source page ID.
     * @param string $module_key Module key.
     * @return array<int>
     */
    private function get_allowed_product_ids_for_request( $source_id, $module_key ) {
        $modules = $this->get_source_modules( $source_id );
        if ( empty( $modules ) ) {
            return array();
        }

        foreach ( $modules as $module_data ) {
            if ( ! is_array( $module_data ) ) {
                continue;
            }

            $module_type = isset( $module_data['type'] ) ? sanitize_key( (string) $module_data['type'] ) : '';
            if ( 'products' !== $module_type ) {
                continue;
            }

            $data = isset( $module_data['data'] ) && is_array( $module_data['data'] ) ? $module_data['data'] : array();
            $allowed_ids = self::extract_allowed_post_ids_from_products_data( $data );
            if ( empty( $allowed_ids ) ) {
                continue;
            }

            $expected_key = self::build_module_key( $source_id, $allowed_ids );
            if ( '' !== $expected_key && hash_equals( $expected_key, $module_key ) ) {
                return $allowed_ids;
            }
        }

        return array();
    }

    /**
     * 加载来源页面的原始模块配置数据。
     *
     * @param int $source_id Source page ID.
     * @return array<int,array<string,mixed>>
     */
    private function get_source_modules( $source_id ) {
        $source_id = absint( $source_id );
        if ( $source_id <= 0 ) {
            return array();
        }

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $source_id )
            : get_post_meta( $source_id, '_developer_starter_modules', true );

        $modules = apply_filters( 'developer_starter_ajax_product_content_source_modules', $modules, $source_id );
        return is_array( $modules ) ? $modules : array();
    }
}
