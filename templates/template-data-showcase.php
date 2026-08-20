<?php
/**
 * Template Name: 数据展示
 * Template Post Type: page
 *
 * @package Developer_Starter
 */

get_header();

?>

<div class="content-area">
    <main id="main" class="site-main">
        <?php
        while ( have_posts() ) :
            the_post();
            $modules = function_exists( 'developer_starter_get_page_modules_data' )
                ? developer_starter_get_page_modules_data( get_the_ID() )
                : get_post_meta( get_the_ID(), '_developer_starter_modules', true );

            if ( ! empty( $modules ) && is_array( $modules ) ) {
                \Developer_Starter\Modules\Module_Manager::get_instance()->render_page_modules();
            } else {
                ?>
                <article class="page-content section-padding">
                    <div class="container">
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </article>
                <?php
            }

        endwhile;
        ?>
    </main>
</div>

<?php
get_footer();
