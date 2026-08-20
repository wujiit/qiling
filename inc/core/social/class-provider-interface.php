<?php
/**
 * Social login provider contract.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\Social;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Provider_Interface {

    /**
     * Provider key, for example qq/github/google.
     *
     * @return string
     */
    public function get_key();

    /**
     * Provider display label.
     *
     * @return string
     */
    public function get_label();

    /**
     * Whether this provider is configured and enabled.
     *
     * @return bool
     */
    public function is_available();

    /**
     * OAuth callback URL registered for this provider.
     *
     * @return string
     */
    public function get_callback_url();

    /**
     * Build authorization URL for a newly created state token.
     *
     * @param string $state OAuth state token.
     * @return string|\WP_Error
     */
    public function get_authorization_url( $state );

    /**
     * Resolve callback request into a normalized social profile.
     *
     * @param array<string,mixed> $request Callback request.
     * @return array<string,mixed>|\WP_Error
     */
    public function get_profile_from_callback( $request );
}
