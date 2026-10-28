<?php
/**
 * 社交登录提供者接口契约。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\Social;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Provider_Interface {

    /**
     * 提供者标识符，例如 qq、github、google。
     *
     * @return string
     */
    public function get_key();

    /**
     * 提供者前台展示名称。
     *
     * @return string
     */
    public function get_label();

    /**
     * 检测当前提供者是否已配置并启用。
     *
     * @return bool
     */
    public function is_available();

    /**
     * 当前提供者注册的 OAuth 回调 URL。
     *
     * @return string
     */
    public function get_callback_url();

    /**
     * 为新生成的 state 安全令牌构建授权跳转 URL。
     *
     * @param string $state OAuth state token.
     * @return string|\WP_Error
     */
    public function get_authorization_url( $state );

    /**
     * 解析回调请求并转换为规范化的社交资料数组。
     *
     * @param array<string,mixed> $request Callback request.
     * @return array<string,mixed>|\WP_Error
     */
    public function get_profile_from_callback( $request );
}
