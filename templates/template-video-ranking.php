<?php
/**
 * Template Name: 影视排行榜
 * Template Post Type: page
 *
 * Dedicated full-width shell for video ranking pages.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'QILING_VIDEO_PORTAL_PAGE' ) ) {
    define( 'QILING_VIDEO_PORTAL_PAGE', true );
}

add_action(
    'wp_enqueue_scripts',
    static function () {
        $css_file = DEVELOPER_STARTER_DIR . '/assets/css/video-ranking-page.css';
        wp_enqueue_style(
            'developer-starter-video-ranking-page',
            DEVELOPER_STARTER_ASSETS . '/css/video-ranking-page.css',
            array( 'developer-starter-main' ),
            file_exists( $css_file ) ? (string) filemtime( $css_file ) : DEVELOPER_STARTER_VERSION
        );

        $footer_css_file = DEVELOPER_STARTER_DIR . '/assets/css/modules-split/footer_suite.css';
        wp_enqueue_style(
            'developer-starter-footer-suite-module',
            DEVELOPER_STARTER_ASSETS . '/css/modules-split/footer_suite.css',
            array( 'developer-starter-video-ranking-page' ),
            file_exists( $footer_css_file ) ? (string) filemtime( $footer_css_file ) : DEVELOPER_STARTER_VERSION
        );
    },
    20
);

add_filter(
    'body_class',
    static function ( $classes ) {
        $classes[] = 'qiling-video-ranking-page';
        $classes[] = 'qiling-video-portal-page';
        return array_values( array_unique( $classes ) );
    }
);

$page_id = get_queried_object_id();
$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( $page_id )
    : get_post_meta( $page_id, '_developer_starter_modules', true );
$has_modules = is_array( $modules ) && ! empty( $modules );
$portal_pages = get_posts(
    array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'templates/template-video-portal.php',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    )
);
$portal_page_id = ! empty( $portal_pages ) ? (int) $portal_pages[0] : 0;
$portal_modules = $portal_page_id > 0 && function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( $portal_page_id )
    : array();
$footer_suite_data = array();
foreach ( is_array( $portal_modules ) ? $portal_modules : array() as $portal_module ) {
    if ( is_array( $portal_module ) && isset( $portal_module['type'] ) && 'footer_suite' === sanitize_key( (string) $portal_module['type'] ) ) {
        $footer_suite_data = isset( $portal_module['data'] ) && is_array( $portal_module['data'] ) ? $portal_module['data'] : array();
        break;
    }
}

if ( empty( $footer_suite_data ) ) {
    $package_file = DEVELOPER_STARTER_DIR . '/inc/template-center/official/video-portal.json';
    $package_data = is_readable( $package_file ) ? json_decode( (string) file_get_contents( $package_file ), true ) : array();
    foreach ( isset( $package_data['modules'] ) && is_array( $package_data['modules'] ) ? $package_data['modules'] : array() as $package_module ) {
        if ( is_array( $package_module ) && isset( $package_module['type'] ) && 'footer_suite' === sanitize_key( (string) $package_module['type'] ) ) {
            $footer_suite_data = isset( $package_module['data'] ) && is_array( $package_module['data'] ) ? $package_module['data'] : array();
            break;
        }
    }
}

if ( isset( $footer_suite_data['qfs_link_groups'] ) && is_array( $footer_suite_data['qfs_link_groups'] ) ) {
    $footer_suite_data['qfs_link_groups'] = array_values(
        array_filter(
            $footer_suite_data['qfs_link_groups'],
            static function ( $group ) {
                $title = is_array( $group ) && isset( $group['title'] ) ? trim( (string) $group['title'] ) : '';
                return ! in_array( $title, array( '内容频道', 'Content Channels' ), true );
            }
        )
    );
    $footer_suite_data['qfs_link_columns'] = '1';
}

add_filter(
    'get_post_metadata',
    static function ( $value, $object_id, $meta_key ) use ( $page_id, $portal_page_id ) {
        if ( (int) $object_id !== (int) $page_id ) {
            return $value;
        }

        $shared_meta_keys = array(
            '_qiling_transparent_header',
            '_qiling_page_visual_style',
            '_qiling_page_design_overrides',
            '_qiling_page_design_preset',
        );
        if ( ! in_array( (string) $meta_key, $shared_meta_keys, true ) ) {
            return $value;
        }

        if ( $portal_page_id > 0 ) {
            $source_value = get_post_meta( $portal_page_id, $meta_key, true );
            if ( '' !== $source_value && null !== $source_value ) {
                return $source_value;
            }
        }

        return '_qiling_transparent_header' === $meta_key ? '1' : $value;
    },
    10,
    3
);

add_filter(
    'developer_starter_footer_visual_config',
    static function ( $config ) {
        $config = is_array( $config ) ? $config : array();
        $config['hidden'] = true;
        return $config;
    }
);

get_header();
?>
<div class="video-ranking-page">
    <header class="video-ranking-page__hero">
        <div class="video-ranking-page__glow video-ranking-page__glow--red" aria-hidden="true"></div>
        <div class="video-ranking-page__glow video-ranking-page__glow--amber" aria-hidden="true"></div>
        <div class="container video-ranking-page__hero-inner">
            <div class="video-ranking-page__eyebrow">
                <span aria-hidden="true"></span>
                <?php esc_html_e( 'QILING CHARTS', 'developer-starter' ); ?>
            </div>
            <h1><?php echo esc_html( get_the_title( $page_id ) ?: __( '影视排行榜', 'developer-starter' ) ); ?></h1>
            <p><?php esc_html_e( '聚合最新上线、热度趋势与真实互动数据，快速发现当下值得观看的影视内容。', 'developer-starter' ); ?></p>
            <div class="video-ranking-page__meta" aria-label="<?php esc_attr_e( '榜单说明', 'developer-starter' ); ?>">
                <span><?php esc_html_e( '实时更新', 'developer-starter' ); ?></span>
                <span><?php esc_html_e( '多维排序', 'developer-starter' ); ?></span>
                <span><?php esc_html_e( '电影 · 剧集 · 动漫 · 综艺', 'developer-starter' ); ?></span>
            </div>
        </div>
    </header>

    <section class="video-ranking-page__content">
        <?php if ( $has_modules ) : ?>
            <?php developer_starter_render_page_modules(); ?>
        <?php elseif ( current_user_can( 'edit_pages' ) ) : ?>
            <div class="container">
                <div class="video-ranking-page__empty">
                    <strong><?php esc_html_e( '暂无可展示内容。', 'developer-starter' ); ?></strong>
                    <p><?php esc_html_e( '暂无内容。', 'developer-starter' ); ?></p>
                    <a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>"><?php esc_html_e( '编辑页面', 'developer-starter' ); ?></a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php if ( '' !== trim( get_the_content() ) ) : ?>
                <div class="container video-ranking-page__entry">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; endif; ?>
    </section>

    <?php if ( ! empty( $footer_suite_data ) ) : ?>
        <div class="video-ranking-page__shared-footer">
            <?php developer_starter_render_module( 'footer_suite', $footer_suite_data ); ?>
        </div>
    <?php endif; ?>
</div>
<?php
get_footer();
