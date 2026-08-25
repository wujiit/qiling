<?php
/**
 * 启灵软件库独立软件详情模板
 *
 * @package Developer_Starter
 */

add_action(
    'wp_enqueue_scripts',
    static function() {
        wp_enqueue_style(
            'developer-starter-resource-detail-skins',
            DEVELOPER_STARTER_ASSETS . '/css/resource-detail-skins.css',
            array( 'developer-starter-main' ),
            developer_starter_get_assets_version()
        );
    },
    30
);

get_header();
?>

<div class="qiapp-single-template qiling-resource-detail-template qiling-resource-detail-template--software">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <section class="content-section qiapp-software-content-section qiling-resource-detail-section qiling-resource-detail-section--software">
                <div class="container">
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'qiapp-software-article qiling-resource-detail qiling-resource-detail--software' ); ?>>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>

                    <?php if ( comments_open() ) : ?>
                        <div class="qiapp-software-comments">
                            <?php comments_template(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php
get_footer();
