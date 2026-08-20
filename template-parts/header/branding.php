<?php
/**
 * Header branding.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'site_branding_classes' => array( 'site-branding' ),
        'frontend_home_url'     => home_url( '/' ),
        'pc_logo_slogan_text'   => '',
        'show_pc_logo_divider'  => false,
    )
);

$site_branding_classes = array_filter( array_map( 'sanitize_html_class', (array) $args['site_branding_classes'] ) );
$frontend_home_url     = (string) $args['frontend_home_url'];
$pc_logo_slogan_text   = (string) $args['pc_logo_slogan_text'];
$show_pc_logo_divider  = ! empty( $args['show_pc_logo_divider'] );

$site_logo       = developer_starter_get_option( 'site_logo', '' );
$mobile_logo     = developer_starter_get_option( 'mobile_logo', '' );
$site_logo_svg   = trim( (string) developer_starter_get_option( 'site_logo_svg', '' ) );
$mobile_logo_svg = trim( (string) developer_starter_get_option( 'mobile_logo_svg', '' ) );

$normalize_media_url = static function ( $value ) {
    if ( empty( $value ) ) {
        return '';
    }
    if ( is_array( $value ) ) {
        return ! empty( $value['url'] ) ? $value['url'] : '';
    }
    if ( is_numeric( $value ) ) {
        $url = wp_get_attachment_url( (int) $value );
        if ( $url ) {
            return $url;
        }
    }
    return $value;
};

if ( function_exists( 'developer_starter_get_media_url' ) ) {
    $site_logo   = developer_starter_get_media_url( $site_logo );
    $mobile_logo = developer_starter_get_media_url( $mobile_logo );
} else {
    $site_logo   = $normalize_media_url( $site_logo );
    $mobile_logo = $normalize_media_url( $mobile_logo );
}

$get_logo_dimensions = static function ( $logo_url ) {
    $logo_dims = array( 'width' => 220, 'height' => 60 );
    if ( function_exists( 'developer_starter_get_image_dimensions_by_url' ) ) {
        $logo_dims = developer_starter_get_image_dimensions_by_url(
            $logo_url,
            $logo_dims
        );
    }

    return array(
        'width'  => ! empty( $logo_dims['width'] ) ? (int) $logo_dims['width'] : 220,
        'height' => ! empty( $logo_dims['height'] ) ? (int) $logo_dims['height'] : 60,
    );
};

$render_logo_markup = static function ( $logo_url, $logo_svg_markup, $extra_class = '', $image_loading = 'lazy', $image_fetchpriority = 'low' ) use ( $frontend_home_url, $get_logo_dimensions ) {
    $logo_svg_markup = trim( (string) $logo_svg_markup );
    $logo_url        = trim( (string) $logo_url );
    $classes         = trim( 'custom-logo-link ' . $extra_class );

    if ( '' !== $logo_svg_markup ) {
        $svg_logo = function_exists( 'developer_starter_sanitize_svg' )
            ? developer_starter_sanitize_svg( $logo_svg_markup )
            : wp_kses_post( $logo_svg_markup );
        if ( $svg_logo ) {
            if ( preg_match( '/<svg[^>]*class=/i', $svg_logo ) ) {
                $svg_logo = preg_replace( '/<svg([^>]*?)class=[\"\']([^\"\']*)[\"\']([^>]*)>/i', '<svg$1class="$2 custom-logo"$3>', $svg_logo, 1 );
            } else {
                $svg_logo = preg_replace( '/<svg\b/i', '<svg class="custom-logo"', $svg_logo, 1 );
            }
        }

        if ( $svg_logo ) {
            return '<a href="' . esc_url( $frontend_home_url ) . '" class="' . esc_attr( trim( $classes . ' custom-logo-link-svg' ) ) . '">' . $svg_logo . '</a>';
        }
    }

    if ( '' !== $logo_url ) {
        $logo_dims           = $get_logo_dimensions( $logo_url );
        $allowed_loading     = array( 'eager', 'lazy' );
        $allowed_priority    = array( 'high', 'low', 'auto' );
        $image_loading       = in_array( $image_loading, $allowed_loading, true ) ? $image_loading : 'lazy';
        $image_fetchpriority = in_array( $image_fetchpriority, $allowed_priority, true ) ? $image_fetchpriority : 'auto';
        $priority_attr       = 'auto' !== $image_fetchpriority ? ' fetchpriority="' . esc_attr( $image_fetchpriority ) . '"' : '';

        return '<a href="' . esc_url( $frontend_home_url ) . '" class="' . esc_attr( $classes ) . '"><img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="custom-logo" width="' . esc_attr( $logo_dims['width'] ) . '" height="' . esc_attr( $logo_dims['height'] ) . '" loading="' . esc_attr( $image_loading ) . '" decoding="async"' . $priority_attr . ' /></a>';
    }

    if ( has_custom_logo() ) {
        ob_start();
        the_custom_logo();
        $logo_html = trim( (string) ob_get_clean() );
        if ( '' !== $logo_html ) {
            return $logo_html;
        }
    }

    return '<a href="' . esc_url( $frontend_home_url ) . '" class="site-title-link ' . esc_attr( trim( $extra_class ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
};

$render_picture_logo_markup = static function ( $desktop_logo_url, $mobile_logo_url, $extra_class = '' ) use ( $frontend_home_url, $get_logo_dimensions ) {
    $desktop_logo_url = trim( (string) $desktop_logo_url );
    $mobile_logo_url  = trim( (string) $mobile_logo_url );

    if ( '' === $desktop_logo_url && '' === $mobile_logo_url ) {
        return '';
    }

    if ( '' === $desktop_logo_url ) {
        $desktop_logo_url = $mobile_logo_url;
    }

    if ( '' === $mobile_logo_url ) {
        $mobile_logo_url = $desktop_logo_url;
    }

    $classes   = trim( 'custom-logo-link ' . $extra_class );
    $logo_dims = $get_logo_dimensions( $desktop_logo_url );
    $source    = '';

    if ( $mobile_logo_url !== $desktop_logo_url ) {
        $source = '<source media="(max-width: 992px)" srcset="' . esc_url( $mobile_logo_url ) . '" />';
    }

    return '<a href="' . esc_url( $frontend_home_url ) . '" class="' . esc_attr( $classes ) . '"><picture>' . $source . '<img src="' . esc_url( $desktop_logo_url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="custom-logo" width="' . esc_attr( $logo_dims['width'] ) . '" height="' . esc_attr( $logo_dims['height'] ) . '" loading="eager" decoding="async" fetchpriority="high" /></picture></a>';
};

$mobile_header_logo     = $mobile_logo ? $mobile_logo : $site_logo;
$mobile_header_logo_svg = $mobile_logo_svg ? $mobile_logo_svg : $site_logo_svg;
$use_picture_logo       = '' !== $site_logo && '' === $site_logo_svg && '' === $mobile_header_logo_svg;
$logos_are_same         = $site_logo === $mobile_header_logo && $site_logo_svg === $mobile_header_logo_svg;
?>
<div class="<?php echo esc_attr( implode( ' ', $site_branding_classes ) ); ?>">
    <?php
    if ( $use_picture_logo ) {
        echo $render_picture_logo_markup( $site_logo, $mobile_header_logo, 'site-branding-logo site-branding-logo-picture' );
    } elseif ( $logos_are_same ) {
        echo $render_logo_markup( $site_logo, $site_logo_svg, 'site-branding-logo', 'eager', 'high' );
    } else {
        echo $render_logo_markup( $site_logo, $site_logo_svg, 'site-branding-logo site-branding-logo-desktop', 'lazy', 'low' );
        echo $render_logo_markup( $mobile_header_logo, $mobile_header_logo_svg, 'site-branding-logo site-branding-logo-mobile', 'lazy', 'low' );
    }
    ?>
    <?php if ( '' !== $pc_logo_slogan_text ) : ?>
        <?php if ( $show_pc_logo_divider ) : ?>
            <span class="site-branding-divider" aria-hidden="true"></span>
        <?php endif; ?>
        <span class="site-branding-slogan" title="<?php echo esc_attr( $pc_logo_slogan_text ); ?>">
            <?php echo esc_html( $pc_logo_slogan_text ); ?>
        </span>
    <?php endif; ?>
</div>
