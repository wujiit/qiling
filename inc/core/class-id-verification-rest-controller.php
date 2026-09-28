<?php
/**
 * 实名认证 REST 控制器。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 实名认证 REST API 接口层。
 */
class ID_Verification_REST_Controller extends \WP_REST_Controller {

    /**
     * 实名认证业务管理器。
     *
     * @var ID_Verification_Manager
     */
    private $manager;

    /**
     * 构造函数.
     *
     * @param ID_Verification_Manager $manager Business manager.
     */
    public function __construct( ID_Verification_Manager $manager ) {
        $this->namespace = 'qiling/v1';
        $this->rest_base = 'id-verification';
        $this->manager   = $manager;
    }

    /**
     * 注册 REST API 路由。
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/verify',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'verify_item' ),
                    'permission_callback' => array( $this, 'verify_permissions_check' ),
                    'args'                => $this->get_verify_args(),
                ),
                'schema' => array( $this, 'get_verify_response_schema' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/status',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_status' ),
                    'permission_callback' => array( $this, 'status_permissions_check' ),
                ),
                'schema' => array( $this, 'get_status_response_schema' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/delete/(?P<id>\d+)',
            array(
                array(
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_item' ),
                    'permission_callback' => array( $this, 'delete_permissions_check' ),
                    'args'                => $this->get_delete_args(),
                ),
                'schema' => array( $this, 'get_delete_response_schema' ),
            )
        );
    }

    /**
     * 校验当前用户是否有权提交实名认证申请。
     *
     * @param \WP_REST_Request $request Request object.
     * @return bool
     */
    public function verify_permissions_check( $request ) {
        unset( $request );
        return is_user_logged_in();
    }

    /**
     * 校验当前用户是否有权读取实名认证状态。
     *
     * @param \WP_REST_Request $request Request object.
     * @return bool
     */
    public function status_permissions_check( $request ) {
        unset( $request );
        return is_user_logged_in();
    }

    /**
     * 校验当前用户是否有权删除实名认证记录。
     *
     * @param \WP_REST_Request $request Request object.
     * @return bool
     */
    public function delete_permissions_check( $request ) {
        unset( $request );
        return current_user_can( 'manage_options' );
    }

    /**
     * 提交实名认证申请请求。
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function verify_item( $request ) {
        return rest_ensure_response( $this->manager->handle_verification( $request ) );
    }

    /**
     * 获取当前登录用户的实名认证状态。
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_status( $request ) {
        return rest_ensure_response( $this->manager->get_user_status( $request ) );
    }

    /**
     * 删除实名认证记录。
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_item( $request ) {
        return rest_ensure_response( $this->manager->delete_record( $request ) );
    }

    /**
     * 获取提交认证接口的参数规则。
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_verify_args() {
        return array(
            'name'   => array(
                'description'       => __( '实名认证姓名', 'developer-starter' ),
                'type'              => 'string',
                'required'          => true,
                'sanitize_callback' => array( $this->manager, 'sanitize_verification_name_arg' ),
                'validate_callback' => array( $this->manager, 'validate_verification_name_arg' ),
            ),
            'mobile' => array(
                'description'       => __( '中国大陆手机号', 'developer-starter' ),
                'type'              => 'string',
                'required'          => true,
                'sanitize_callback' => array( $this->manager, 'sanitize_verification_mobile_arg' ),
                'validate_callback' => array( $this->manager, 'validate_verification_mobile_arg' ),
            ),
            'idcard' => array(
                'description'       => __( '18 位居民身份证号', 'developer-starter' ),
                'type'              => 'string',
                'required'          => true,
                'sanitize_callback' => array( $this->manager, 'sanitize_verification_idcard_arg' ),
                'validate_callback' => array( $this->manager, 'validate_verification_idcard_arg' ),
            ),
        );
    }

    /**
     * 获取删除接口的请求参数规则。
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_delete_args() {
        return array(
            'id' => array(
                'description'       => __( '实名认证记录 ID', 'developer-starter' ),
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
                'validate_callback' => array( $this->manager, 'validate_positive_integer_arg' ),
            ),
        );
    }

    /**
     * 获取控制器接口探测所需的默认数据模式。
     *
     * @return array<string,mixed>
     */
    public function get_item_schema() {
        return $this->get_status_response_schema();
    }

    /**
     * 获取提交认证接口的响应模式定义。
     *
     * @return array<string,mixed>
     */
    public function get_verify_response_schema() {
        return array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'qiling-id-verification-verify-response',
            'type'       => 'object',
            'properties' => array(
                'success' => array(
                    'description' => __( '实名认证是否成功。', 'developer-starter' ),
                    'type'        => 'boolean',
                    'readonly'    => true,
                ),
                'message' => array(
                    'description' => __( '面向用户的验证结果消息。', 'developer-starter' ),
                    'type'        => 'string',
                    'readonly'    => true,
                ),
                'data'    => array(
                    'description' => __( '验证结果摘要。', 'developer-starter' ),
                    'type'        => 'object',
                    'readonly'    => true,
                    'properties'  => array(
                        'verified' => array(
                            'type'     => 'boolean',
                            'readonly' => true,
                        ),
                        'status'   => array(
                            'type'     => 'string',
                            'enum'     => array( 'success', 'failed' ),
                            'readonly' => true,
                        ),
                    ),
                ),
                'time'    => array(
                    'description' => __( '服务器记录时间。', 'developer-starter' ),
                    'type'        => 'string',
                    'readonly'    => true,
                ),
            ),
        );
    }

    /**
     * 获取认证状态接口的响应结构模式。
     *
     * @return array<string,mixed>
     */
    public function get_status_response_schema() {
        return array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'qiling-id-verification-status-response',
            'type'       => 'object',
            'properties' => array(
                'verified' => array(
                    'description' => __( '当前用户是否已实名认证。', 'developer-starter' ),
                    'type'        => 'boolean',
                    'readonly'    => true,
                ),
                'name'     => array(
                    'description' => __( '脱敏后的实名姓名。', 'developer-starter' ),
                    'type'        => 'string',
                    'readonly'    => true,
                ),
                'mobile'   => array(
                    'description' => __( '脱敏后的手机号。', 'developer-starter' ),
                    'type'        => 'string',
                    'readonly'    => true,
                ),
                'idcard'   => array(
                    'description' => __( '脱敏后的身份证号。', 'developer-starter' ),
                    'type'        => 'string',
                    'readonly'    => true,
                ),
            ),
        );
    }

    /**
     * 获取删除接口的响应结构模式。
     *
     * @return array<string,mixed>
     */
    public function get_delete_response_schema() {
        return array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'qiling-id-verification-delete-response',
            'type'       => 'object',
            'properties' => array(
                'success' => array(
                    'description' => __( '记录是否删除成功。', 'developer-starter' ),
                    'type'        => 'boolean',
                    'readonly'    => true,
                ),
            ),
        );
    }
}
