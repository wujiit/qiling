<?php
/**
 * Menu Icon Helpers
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_normalize_menu_icon_token' ) ) {
    /**
     * Normalize menu icon token spacing.
     *
     * @param string $icon Raw icon value.
     * @return string
     */
    function developer_starter_normalize_menu_icon_token( $icon ) {
        $icon = trim( preg_replace( '/\s+/', ' ', (string) $icon ) );
        if ( '' === $icon ) {
            return '';
        }

        if ( preg_match( '/^(icon-[a-zA-Z0-9_-]+)$/', $icon, $matches ) ) {
            return $matches[1];
        }

        return $icon;
    }
}

if ( ! function_exists( 'developer_starter_is_supported_menu_icon_token' ) ) {
    /**
     * Check whether a token can be treated as a menu icon.
     *
     * Supports:
     * - icon-xxx
     * - inline HTML icon snippet (emoji/span/i/svg)
     * - emoji text / html-entity emoji
     *
     * @param string $icon Raw icon value.
     * @return bool
     */
    function developer_starter_is_supported_menu_icon_token( $icon ) {
        $icon = developer_starter_normalize_menu_icon_token( $icon );
        if ( '' === $icon ) {
            return false;
        }

        if ( preg_match( '/^icon-[a-zA-Z0-9_-]+$/', $icon ) ) {
            return true;
        }

        if ( false !== strpos( $icon, '<' ) && false !== strpos( $icon, '>' ) ) {
            return true;
        }

        $decoded = html_entity_decode( $icon, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        if ( '' !== trim( $decoded ) && $decoded !== $icon ) {
            return true;
        }

        $is_emoji = preg_match( '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $decoded );

        return (bool) $is_emoji;
    }
}

if ( ! function_exists( 'developer_starter_extract_menu_icon_from_classes' ) ) {
    /**
     * Extract icon token from nav menu CSS class list.
     *
     * @param array $classes Menu CSS classes.
     * @return string
     */
    function developer_starter_extract_menu_icon_from_classes( $classes ) {
        if ( empty( $classes ) || ! is_array( $classes ) ) {
            return '';
        }

        $icon_class = '';

        foreach ( $classes as $class ) {
            $class = trim( (string) $class );
            if ( '' === $class ) {
                continue;
            }

            if ( preg_match( '/^icon-[a-zA-Z0-9_-]+$/', $class ) ) {
                $icon_class = $class;
            }
        }

        if ( '' === $icon_class ) {
            return '';
        }

        return $icon_class;
    }
}

if ( ! function_exists( 'developer_starter_extract_menu_icon_from_title' ) ) {
    /**
     * Parse icon token from title prefix.
     *
     * Supported forms:
     * - [icon-home]首页
     * - [<span>🔥</span>]首页
     * - icon-home|首页
     *
     * @param string $title Menu title.
     * @return array{icon:string,title:string}
     */
    function developer_starter_extract_menu_icon_from_title( $title ) {
        $raw_title = (string) $title;
        $plain_title = trim( wp_strip_all_tags( $raw_title ) );

        if ( '' === $plain_title ) {
            return array(
                'icon'  => '',
                'title' => $raw_title,
            );
        }

        if ( preg_match( '/^\s*\[(.+?)\]\s*(.+)$/u', $plain_title, $matches ) ) {
            $icon_token = developer_starter_normalize_menu_icon_token( $matches[1] );
            if ( developer_starter_is_supported_menu_icon_token( $icon_token ) ) {
                return array(
                    'icon'  => $icon_token,
                    'title' => trim( (string) $matches[2] ),
                );
            }
        }

        if ( preg_match( '/^\s*(icon-[a-zA-Z0-9_-]+)\s*[|｜]\s*(.+)$/u', $plain_title, $matches ) ) {
            return array(
                'icon'  => developer_starter_normalize_menu_icon_token( $matches[1] ),
                'title' => trim( (string) $matches[2] ),
            );
        }

        return array(
            'icon'  => '',
            'title' => $raw_title,
        );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_menu_icon_html' ) ) {
    /**
     * Sanitize inline HTML icon snippet.
     *
     * @param string $html Raw html.
     * @return string
     */
    function developer_starter_sanitize_menu_icon_html( $html ) {
        $html = trim( (string) $html );
        if ( '' === $html ) {
            return '';
        }

        if ( false !== stripos( $html, '<svg' ) && function_exists( 'developer_starter_sanitize_svg' ) ) {
            return developer_starter_sanitize_svg( $html );
        }

        $allowed_tags = array(
            'span'   => array(
                'class'       => true,
                'style'       => true,
                'aria-hidden' => true,
            ),
            'i'      => array(
                'class'       => true,
                'style'       => true,
                'aria-hidden' => true,
            ),
            'em'     => array(
                'class'       => true,
                'style'       => true,
                'aria-hidden' => true,
            ),
            'strong' => array(
                'class'       => true,
                'style'       => true,
                'aria-hidden' => true,
            ),
            'b'      => array(
                'class'       => true,
                'style'       => true,
                'aria-hidden' => true,
            ),
            'small'  => array(
                'class'       => true,
                'style'       => true,
                'aria-hidden' => true,
            ),
            'img'    => array(
                'class'       => true,
                'style'       => true,
                'src'         => true,
                'alt'         => true,
                'width'       => true,
                'height'      => true,
                'aria-hidden' => true,
            ),
        );

        return wp_kses( $html, $allowed_tags );
    }
}

if ( ! function_exists( 'developer_starter_render_menu_icon_html' ) ) {
    /**
     * Render menu icon html from icon token.
     *
     * @param string $icon Icon token.
     * @return string
     */
    function developer_starter_render_menu_icon_html( $icon ) {
        $icon = developer_starter_normalize_menu_icon_token( $icon );
        if ( '' === $icon ) {
            return '';
        }

        // icon-xxx -> symbol/js mode.
        if ( preg_match( '/^icon-[a-zA-Z0-9_-]+$/', $icon ) ) {
            if ( function_exists( 'developer_starter_get_icon_html' ) ) {
                return developer_starter_get_icon_html( $icon, 'qiling-menu-icon qiling-menu-icon--symbol' );
            }
            return '';
        }

        // Inline HTML icon markup.
        if ( false !== strpos( $icon, '<' ) && false !== strpos( $icon, '>' ) ) {
            $safe_html = developer_starter_sanitize_menu_icon_html( $icon );
            if ( '' === trim( $safe_html ) ) {
                return '';
            }

            return '<span class="qiling-menu-icon qiling-menu-icon--html" aria-hidden="true">' . $safe_html . '</span>';
        }

        // Emoji text / html entity emoji.
        $decoded = html_entity_decode( $icon, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        if ( '' !== trim( $decoded ) && function_exists( 'developer_starter_get_icon_html' ) ) {
            return developer_starter_get_icon_html( $decoded, 'qiling-menu-icon qiling-menu-icon--emoji' );
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_filter_nav_menu_item_title_with_icon' ) ) {
    /**
     * Inject icon markup before nav menu item title on frontend.
     *
     * Priority:
     * 1) custom field _menu_item_icon
     * 2) menu CSS classes
     * 3) title prefix syntax
     *
     * @param string   $title     Menu item title.
     * @param WP_Post  $menu_item Menu item object.
     * @param stdClass $args      Menu args.
     * @param int      $depth     Item depth.
     * @return string
     */
    function developer_starter_filter_nav_menu_item_title_with_icon( $title, $menu_item, $args, $depth ) {
        unset( $args, $depth );

        if ( is_admin() && ! wp_doing_ajax() ) {
            return $title;
        }

        if ( ! is_object( $menu_item ) || ! isset( $menu_item->ID ) ) {
            return $title;
        }

        $title_str = (string) $title;
        if ( '' === trim( wp_strip_all_tags( $title_str ) ) ) {
            return $title;
        }

        // Avoid double wrapping.
        if ( strpos( $title_str, 'qiling-menu-title-with-icon' ) !== false || strpos( $title_str, 'qiling-menu-icon' ) !== false ) {
            return $title;
        }

        $icon_token = '';

        // 1) Custom menu field.
        $meta_icon = get_post_meta( (int) $menu_item->ID, '_menu_item_icon', true );
        if ( is_string( $meta_icon ) && '' !== trim( $meta_icon ) ) {
            $candidate = developer_starter_normalize_menu_icon_token( $meta_icon );
            if ( developer_starter_is_supported_menu_icon_token( $candidate ) ) {
                $icon_token = $candidate;
            }
        }

        // 2) Menu CSS classes.
        if ( '' === $icon_token && isset( $menu_item->classes ) ) {
            $icon_token = developer_starter_extract_menu_icon_from_classes( (array) $menu_item->classes );
        }

        // 3) Prefix in title.
        $parsed_title_data = developer_starter_extract_menu_icon_from_title( $title_str );
        if ( '' === $icon_token && ! empty( $parsed_title_data['icon'] ) ) {
            $icon_token = $parsed_title_data['icon'];
        }

        if ( '' === $icon_token ) {
            return $title;
        }

        $icon_html = developer_starter_render_menu_icon_html( $icon_token );
        if ( '' === $icon_html ) {
            return $title;
        }

        $text_title = isset( $parsed_title_data['title'] ) ? (string) $parsed_title_data['title'] : $title_str;
        $text_title = trim( wp_strip_all_tags( $text_title ) );
        if ( '' === $text_title ) {
            $text_title = trim( wp_strip_all_tags( $title_str ) );
        }

        return '<span class="qiling-menu-title-with-icon">' . $icon_html . '<span class="qiling-menu-title-text">' . esc_html( $text_title ) . '</span></span>';
    }
}
add_filter( 'nav_menu_item_title', 'developer_starter_filter_nav_menu_item_title_with_icon', 20, 4 );
