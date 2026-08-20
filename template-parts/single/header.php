<?php
/**
 * Single post header.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'options'                    => array(),
        'post_id'                    => get_the_ID(),
        'qls_use_title_layout'       => false,
        'qls_title_box'              => '',
        'qls_cover_url'              => '',
        'qls_cover_dims'             => array( 'width' => 640, 'height' => 640 ),
        'resource_detail_contexts'   => array(),
        'is_resource_detail_skin'    => false,
        'hide_post_breadcrumb'       => false,
    )
);

$options = is_array( $args['options'] ) ? $args['options'] : array();
$hide_category = ! empty( $options['hide_post_category'] ) && '1' === $options['hide_post_category'];
$show_breadcrumb = empty( $args['hide_post_breadcrumb'] );
$categories = get_the_category();
$primary_category = ! empty( $categories ) ? $categories[0] : null;
if ( ! empty( $categories ) ) {
    usort(
        $categories,
        static function ( $left, $right ) {
            $left_depth = count( get_ancestors( (int) $left->term_id, 'category' ) );
            $right_depth = count( get_ancestors( (int) $right->term_id, 'category' ) );

            return $right_depth <=> $left_depth;
        }
    );
    $primary_category = $categories[0];
}
$breadcrumb_terms = array();
if ( $primary_category ) {
    $ancestor_ids = array_reverse( get_ancestors( (int) $primary_category->term_id, 'category' ) );
    foreach ( $ancestor_ids as $ancestor_id ) {
        $ancestor_term = get_category( $ancestor_id );
        if ( $ancestor_term && ! is_wp_error( $ancestor_term ) ) {
            $breadcrumb_terms[] = $ancestor_term;
        }
    }
    $breadcrumb_terms[] = $primary_category;
}
$header_common_args = $args;
$resource_detail_contexts = is_array( $args['resource_detail_contexts'] ) ? $args['resource_detail_contexts'] : array();
$resource_header_classes = array( 'page-header', 'single-post-header' );
if ( ! empty( $args['is_resource_detail_skin'] ) ) {
    $resource_header_classes[] = 'qiling-resource-detail-header';
    foreach ( $resource_detail_contexts as $resource_detail_context ) {
        $resource_detail_context = sanitize_html_class( (string) $resource_detail_context );
        if ( '' !== $resource_detail_context ) {
            $resource_header_classes[] = 'qiling-resource-detail-header--' . $resource_detail_context;
        }
    }
}
?>
<?php if ( $args['qls_use_title_layout'] ) : ?>
    <div class="<?php echo esc_attr( implode( ' ', array_merge( $resource_header_classes, array( 'single-post-header--resource' ) ) ) ); ?>">
        <div class="container single-post-header__container">
            <div class="single-post-header__resource-layout">
                <?php if ( $args['qls_cover_url'] ) : ?>
                    <div class="single-post-header__cover">
                        <img src="<?php echo esc_url( $args['qls_cover_url'] ); ?>" alt="<?php the_title_attribute(); ?>" width="<?php echo esc_attr( (int) $args['qls_cover_dims']['width'] ); ?>" height="<?php echo esc_attr( (int) $args['qls_cover_dims']['height'] ); ?>" loading="eager" decoding="async" fetchpriority="high" />
                    </div>
                <?php endif; ?>

                <div class="single-post-header__content">
                    <div class="single-post-header__title-block">
                        <?php if ( $show_breadcrumb && ! empty( $breadcrumb_terms ) ) : ?>
                            <nav class="single-post-breadcrumb" aria-label="<?php esc_attr_e( '文章面包屑', 'developer-starter' ); ?>">
                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '首页', 'developer-starter' ); ?></a>
                                <?php foreach ( $breadcrumb_terms as $breadcrumb_term ) : ?>
                                    <span aria-hidden="true">/</span>
                                    <a href="<?php echo esc_url( get_category_link( $breadcrumb_term->term_id ) ); ?>"><?php echo esc_html( $breadcrumb_term->name ); ?></a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>

                        <?php if ( ! $hide_category && $primary_category ) : ?>
                            <div class="post-header-category">
                                <a class="post-header-category-link" href="<?php echo esc_url( get_category_link( $primary_category->term_id ) ); ?>">
                                    <?php echo esc_html( $primary_category->name ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <h1 class="page-title single-post-title">
                            <?php echo esc_html( get_the_title() ); ?>
                        </h1>

                        <?php
                        $header_common_args['class'] = 'single-post-meta-stats--resource';
                        get_template_part( 'template-parts/single/meta-stats', null, $header_common_args );
                        ?>
                    </div>

                    <div class="single-post-header__resource-box">
                        <?php
                        echo function_exists( 'developer_starter_kses_qilingshop_box' )
                            ? developer_starter_kses_qilingshop_box( $args['qls_title_box'] )
                            : wp_kses_post( $args['qls_title_box'] );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="<?php echo esc_attr( implode( ' ', array_merge( $resource_header_classes, array( 'single-post-header--centered' ) ) ) ); ?>">
        <div class="container single-post-header__container">
            <div class="single-post-header__intro">
                <?php if ( $show_breadcrumb && ! empty( $breadcrumb_terms ) ) : ?>
                    <nav class="single-post-breadcrumb" aria-label="<?php esc_attr_e( '文章面包屑', 'developer-starter' ); ?>">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '首页', 'developer-starter' ); ?></a>
                        <?php foreach ( $breadcrumb_terms as $breadcrumb_term ) : ?>
                            <span aria-hidden="true">/</span>
                            <a href="<?php echo esc_url( get_category_link( $breadcrumb_term->term_id ) ); ?>"><?php echo esc_html( $breadcrumb_term->name ); ?></a>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <?php if ( ! $hide_category && $primary_category ) : ?>
                    <div class="post-header-category">
                        <a href="<?php echo esc_url( get_category_link( $primary_category->term_id ) ); ?>" class="post-header-category-link">
                            <?php echo esc_html( $primary_category->name ); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <h1 class="page-title single-post-title">
                    <?php echo esc_html( get_the_title() ); ?>
                </h1>

                <?php
                $header_common_args['class'] = 'single-post-meta-stats--centered';
                get_template_part( 'template-parts/single/meta-stats', null, $header_common_args );
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>
