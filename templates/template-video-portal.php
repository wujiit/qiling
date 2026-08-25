<?php
/**
 * Template Name: 影视门户首页
 * Template Post Type: page
 *
 * Full-width shell for the official video portal page package.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'QILING_VIDEO_PORTAL_PAGE' ) ) {
    define( 'QILING_VIDEO_PORTAL_PAGE', true );
}

add_filter(
    'body_class',
    static function ( $classes ) {
        $classes[] = 'qiling-video-portal-page';
        return array_values( array_unique( $classes ) );
    }
);

$page_id = get_queried_object_id();
if ( class_exists( '\Developer_Starter\Core\Official_Template_Package_Service' ) ) {
    $package_service = new \Developer_Starter\Core\Official_Template_Package_Service();
    $package_service->maybe_upgrade_video_portal_page( $page_id );
}
get_header();

$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( $page_id )
    : get_post_meta( $page_id, '_developer_starter_modules', true );
$has_modules = is_array( $modules ) && ! empty( $modules );
?>
<div class="page-template template-video-portal">
    <?php if ( $has_modules ) : ?>
        <?php developer_starter_render_page_modules(); ?>
    <?php elseif ( current_user_can( 'edit_pages' ) ) : ?>
        <section class="section-padding qiling-video-portal-empty">
            <div class="container text-center">
                <h1><?php esc_html_e( '影视门户首页', 'developer-starter' ); ?></h1>
                <p><?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?></p>
                <a class="btn btn-primary" href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>"><?php esc_html_e( '编辑页面', 'developer-starter' ); ?></a>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <?php if ( '' !== trim( get_the_content() ) ) : ?>
            <section class="content-section section-padding">
                <div class="container"><div class="entry-content"><?php the_content(); ?></div></div>
            </section>
        <?php endif; ?>
    <?php endwhile; endif; ?>
</div>
<?php
get_footer();
