<?php
/**
 * The footer for the Qi Ling theme.
 *
 * Footer markup is composed from child-theme-overridable template parts.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

    </main><!-- #primary -->

    <?php
    $effect_enabled                  = developer_starter_get_option( 'footer_effect_enable', '' );
    $effect_type                     = developer_starter_get_option( 'footer_effect_type', 'particles' );
    $footer_visual_config            = function_exists( 'developer_starter_get_footer_visual_config' )
        ? developer_starter_get_footer_visual_config()
        : array();
    $footer_effect_scope             = isset( $footer_visual_config['effect_scope'] ) ? sanitize_key( (string) $footer_visual_config['effect_scope'] ) : 'main';
    $footer_builder_enabled          = developer_starter_get_option( 'footer_builder_enable', '' );
    $footer_builder_page_id          = absint( developer_starter_get_option( 'footer_builder_page_id', '' ) );
    $footer_builder_region_page_ids  = array(
        'replace_widgets'      => absint( developer_starter_get_option( 'footer_builder_main_page_id', '' ) ),
        'replace_friend_links' => absint( developer_starter_get_option( 'footer_builder_friend_page_id', '' ) ),
        'replace_bottom'       => absint( developer_starter_get_option( 'footer_builder_bottom_page_id', '' ) ),
    );
    $footer_builder_position         = sanitize_key( (string) developer_starter_get_option( 'footer_builder_position', 'replace_widgets' ) );
    if ( ! in_array( $footer_builder_position, array( 'replace_widgets', 'replace_friend_links', 'replace_bottom', 'replace_all' ), true ) ) {
        $footer_builder_position = 'replace_widgets';
    }
    $page_footer_region_decoration = array();
    if ( function_exists( 'developer_starter_resolve_current_page_region_decoration' ) ) {
        foreach ( array( 'footer_main', 'footer_friend', 'footer_bottom' ) as $footer_region ) {
            $page_footer_region_decoration[ $footer_region ] = developer_starter_resolve_current_page_region_decoration( $footer_region );
        }
    }

    $footer_css_vars = class_exists( '\Developer_Starter\Core\Design_Tokens' )
        ? \Developer_Starter\Core\Design_Tokens::get_footer_runtime_style_vars()
        : '';

    $footer_about_title   = trim( developer_starter_get_option( 'footer_about_title', '' ) );
    $footer_links_title   = trim( developer_starter_get_option( 'footer_links_title', '' ) );
    $footer_contact_title = trim( developer_starter_get_option( 'footer_contact_title', '' ) );
    $footer_follow_title  = trim( developer_starter_get_option( 'footer_follow_title', '' ) );
    $company_brief        = (string) developer_starter_get_option( 'company_brief', __( '专业的企业服务提供商，致力于为客户提供优质的产品与服务。', 'developer-starter' ) );
    $footer_section_visibility = array(
        'about'   => '1' === (string) developer_starter_get_option( 'footer_about_enable', '1' ),
        'links'   => '1' === (string) developer_starter_get_option( 'footer_links_enable', '1' ),
        'contact' => '1' === (string) developer_starter_get_option( 'footer_contact_enable', '1' ),
        'follow'  => '1' === (string) developer_starter_get_option( 'footer_follow_enable', '1' ),
    );

    $footer_about_title   = '' !== $footer_about_title ? $footer_about_title : __( '关于我们', 'developer-starter' );
    $footer_links_title   = '' !== $footer_links_title ? $footer_links_title : __( '快速链接', 'developer-starter' );
    $footer_contact_title = '' !== $footer_contact_title ? $footer_contact_title : __( '联系方式', 'developer-starter' );
    $footer_follow_title  = '' !== $footer_follow_title ? $footer_follow_title : __( '关注我们', 'developer-starter' );

    if ( function_exists( 'developer_starter_translate_theme_option_text' ) ) {
        $footer_about_title   = developer_starter_translate_theme_option_text( $footer_about_title );
        $footer_links_title   = developer_starter_translate_theme_option_text( $footer_links_title );
        $footer_contact_title = developer_starter_translate_theme_option_text( $footer_contact_title );
        $footer_follow_title  = developer_starter_translate_theme_option_text( $footer_follow_title );
        $company_brief        = developer_starter_translate_theme_option_text( $company_brief );
    }

    $footer_template_args = array(
        'footer_css_vars'         => $footer_css_vars,
        'effect_enabled'          => (bool) $effect_enabled,
        'effect_type'             => $effect_type,
        'effect_scope'            => $footer_effect_scope,
        'footer_visual_config'    => $footer_visual_config,
        'footer_about_title'      => $footer_about_title,
        'footer_links_title'      => $footer_links_title,
        'footer_contact_title'    => $footer_contact_title,
        'footer_follow_title'     => $footer_follow_title,
        'company_brief'           => nl2br( wp_kses_post( $company_brief ) ),
        'footer_section_visibility' => $footer_section_visibility,
        'footer_builder_enabled'  => (bool) $footer_builder_enabled,
        'footer_builder_page_id'  => $footer_builder_page_id,
        'footer_builder_region_page_ids' => $footer_builder_region_page_ids,
        'footer_builder_position' => $footer_builder_position,
        'page_footer_region_decoration' => $page_footer_region_decoration,
    );
    $footer_template_args = (array) apply_filters( 'developer_starter_footer_template_args', $footer_template_args );

    get_template_part( 'template-parts/footer/site-footer', null, $footer_template_args );
    ?>

</div><!-- #page -->

<?php get_template_part( 'template-parts/footer/footer-effect-data', null, $footer_template_args ); ?>
<?php get_template_part( 'template-parts/footer/privacy-banner', null, $footer_template_args ); ?>
<?php get_template_part( 'template-parts/footer/mobile-bottom-nav', null, $footer_template_args ); ?>

<?php wp_footer(); ?>

</body>
</html>
