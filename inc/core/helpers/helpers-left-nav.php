<?php
/**
 * Left navigation helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_left_nav_menu_id' ) ) {
    /**
     * 解析左侧导航实际使用的菜单 ID。
     * 优先取当前主题绑定；若当前为子主题且未绑定，则回退父主题绑定。
     *
     * @return int
     */
    function developer_starter_get_left_nav_menu_id() {
        static $resolved_menu_id = null;
        if ( null !== $resolved_menu_id ) {
            return (int) $resolved_menu_id;
        }

        $resolved_menu_id = 0;
        $locations        = function_exists( 'get_nav_menu_locations' ) ? (array) get_nav_menu_locations() : array();

        if ( ! empty( $locations['left_sidebar'] ) ) {
            $resolved_menu_id = absint( $locations['left_sidebar'] );
        }

        if ( ! $resolved_menu_id && is_child_theme() ) {
            $parent_stylesheet = (string) get_template();
            if ( '' !== $parent_stylesheet ) {
                $parent_mods = get_option( 'theme_mods_' . $parent_stylesheet, array() );
                if ( is_array( $parent_mods )
                    && ! empty( $parent_mods['nav_menu_locations'] )
                    && is_array( $parent_mods['nav_menu_locations'] )
                    && ! empty( $parent_mods['nav_menu_locations']['left_sidebar'] )
                ) {
                    $resolved_menu_id = absint( $parent_mods['nav_menu_locations']['left_sidebar'] );
                }
            }
        }

        if ( $resolved_menu_id && ! wp_get_nav_menu_object( $resolved_menu_id ) ) {
            $resolved_menu_id = 0;
        }

        $resolved_menu_id = (int) apply_filters( 'developer_starter_left_nav_menu_id', $resolved_menu_id );

        return (int) $resolved_menu_id;
    }
}

if ( ! function_exists( 'developer_starter_get_left_nav_menu_args' ) ) {
    /**
     * 获取左侧导航菜单参数。
     *
     * @return array
     */
    function developer_starter_get_left_nav_menu_args() {
        $menu_id = function_exists( 'developer_starter_get_left_nav_menu_id' ) ? developer_starter_get_left_nav_menu_id() : 0;
        $args    = array(
            'menu_id'     => 'qiling-left-nav-menu',
            'menu_class'  => 'qiling-left-nav-menu',
            'container'   => false,
            'depth'       => 2,
            'fallback_cb' => false,
            'link_before' => '<span class="qiling-left-nav-label">',
            'link_after'  => '</span>',
        );

        if ( $menu_id > 0 ) {
            $args['menu'] = $menu_id;
        } else {
            $args['theme_location'] = 'left_sidebar';
        }

        return (array) apply_filters( 'developer_starter_left_nav_menu_args', $args, $menu_id );
    }
}

if ( ! function_exists( 'developer_starter_render_left_nav_toggle_icon' ) ) {
    /**
     * 渲染左侧导航展开/收起按钮图标。
     *
     * @param string $raw_icon 用户配置的图标。
     * @param string $fallback 兜底图标。
     * @return string
     */
    function developer_starter_render_left_nav_toggle_icon( $raw_icon, $fallback = '☰' ) {
        $icon = trim( (string) $raw_icon );
        if ( '' === $icon ) {
            $icon = (string) $fallback;
        }

        if ( function_exists( 'developer_starter_render_menu_icon_html' ) ) {
            $icon_html = (string) developer_starter_render_menu_icon_html( $icon );
            if ( '' !== trim( $icon_html ) ) {
                return '<span class="qiling-left-nav-toggle-icon qiling-left-nav-toggle-icon--rendered" aria-hidden="true">' . $icon_html . '</span>';
            }
        }

        $plain_icon = html_entity_decode( wp_strip_all_tags( $icon ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        if ( '' === trim( $plain_icon ) ) {
            $plain_icon = (string) $fallback;
        }

        return '<span class="qiling-left-nav-toggle-icon qiling-left-nav-toggle-icon--text" aria-hidden="true">' . esc_html( $plain_icon ) . '</span>';
    }
}

if ( ! function_exists( 'developer_starter_render_left_nav' ) ) {
    /**
     * 输出左侧导航（挂在 wp_body_open），避免子主题覆盖 header.php 导致丢失。
     *
     * @return void
     */
    function developer_starter_render_left_nav() {
        if ( ! function_exists( 'developer_starter_should_render_left_nav' ) || ! developer_starter_should_render_left_nav() ) {
            return;
        }

        $left_nav_args = function_exists( 'developer_starter_get_left_nav_menu_args' )
            ? developer_starter_get_left_nav_menu_args()
            : array(
                'theme_location' => 'left_sidebar',
                'menu_id'        => 'qiling-left-nav-menu',
                'menu_class'     => 'qiling-left-nav-menu',
                'container'      => false,
                'depth'          => 2,
                'fallback_cb'    => false,
                'link_before'    => '<span class="qiling-left-nav-label">',
                'link_after'     => '</span>',
            );

        $default_open           = ( '1' === (string) developer_starter_get_option( 'left_nav_toggle_default_open', '' ) );
        $auto_open_on_large     = (bool) apply_filters( 'developer_starter_left_nav_auto_open_on_large', true );
        $large_screen_min_width = (int) apply_filters( 'developer_starter_left_nav_auto_open_min_width', 1440 );
        if ( $large_screen_min_width < 1025 ) {
            $large_screen_min_width = 1025;
        }

        $shell_classes   = 'qiling-left-nav-shell' . ( $default_open ? ' is-expanded' : '' );
        $aside_classes   = 'qiling-left-nav' . ( $default_open ? ' is-expanded' : '' );
        $open_icon_html  = developer_starter_render_left_nav_toggle_icon(
            developer_starter_get_option( 'left_nav_toggle_open_icon', '☰' ),
            '☰'
        );
        $close_icon_html = developer_starter_render_left_nav_toggle_icon(
            developer_starter_get_option( 'left_nav_toggle_close_icon', '✕' ),
            '✕'
        );
        ?>
        <div
            id="qiling-left-nav-shell"
            class="<?php echo esc_attr( $shell_classes ); ?>"
            data-default-open="<?php echo $default_open ? '1' : '0'; ?>"
            data-auto-open-large="<?php echo $auto_open_on_large ? '1' : '0'; ?>"
            data-auto-open-large-min-width="<?php echo esc_attr( (string) $large_screen_min_width ); ?>"
        >
            <button type="button" id="qiling-left-nav-toggle" class="qiling-left-nav-toggle" aria-controls="qiling-left-nav" aria-expanded="<?php echo $default_open ? 'true' : 'false'; ?>">
                <span class="qiling-left-nav-toggle-state qiling-left-nav-toggle-state-open" aria-hidden="<?php echo $default_open ? 'true' : 'false'; ?>">
                    <?php echo wp_kses_post( $open_icon_html ); ?>
                </span>
                <span class="qiling-left-nav-toggle-state qiling-left-nav-toggle-state-close" aria-hidden="<?php echo $default_open ? 'false' : 'true'; ?>">
                    <?php echo wp_kses_post( $close_icon_html ); ?>
                </span>
                <span class="screen-reader-text"><?php esc_html_e( '切换左侧导航', 'developer-starter' ); ?></span>
            </button>

            <aside id="qiling-left-nav" class="<?php echo esc_attr( $aside_classes ); ?>" aria-label="<?php esc_attr_e( '左侧导航菜单', 'developer-starter' ); ?>">
                <div class="qiling-left-nav-scroll" tabindex="0" aria-label="<?php esc_attr_e( '左侧导航内容', 'developer-starter' ); ?>">
                    <?php wp_nav_menu( $left_nav_args ); ?>
                </div>
            </aside>
        </div>
        <?php
    }
}
add_action( 'wp_body_open', 'developer_starter_render_left_nav', 30 );

if ( ! function_exists( 'developer_starter_get_left_nav_excluded_page_ids' ) ) {
    /**
     * 获取不加载左侧导航的页面 ID 列表。
     *
     * @return int[]
     */
    function developer_starter_get_left_nav_excluded_page_ids() {
        $raw_ids = (string) developer_starter_get_option( 'left_nav_excluded_page_ids', '' );
        $ids     = array();

        if ( '' !== trim( $raw_ids ) ) {
            preg_match_all( '/\d+/', $raw_ids, $matches );

            if ( ! empty( $matches[0] ) ) {
                foreach ( $matches[0] as $raw_id ) {
                    $page_id = absint( $raw_id );

                    if ( $page_id > 0 ) {
                        $ids[ $page_id ] = $page_id;
                    }
                }
            }
        }

        return (array) apply_filters( 'developer_starter_left_nav_excluded_page_ids', array_values( $ids ), $raw_ids );
    }
}

if ( ! function_exists( 'developer_starter_should_render_left_nav' ) ) {
    /**
     * 判断当前请求是否需要渲染左侧导航菜单。
     *
     * @return bool
     */
    function developer_starter_should_render_left_nav() {
        if ( is_admin() ) {
            return false;
        }

        if ( ! function_exists( 'developer_starter_get_left_nav_menu_id' ) || developer_starter_get_left_nav_menu_id() <= 0 ) {
            return false;
        }

        $excluded_page_ids = array();
        if ( function_exists( 'developer_starter_get_left_nav_excluded_page_ids' ) ) {
            $excluded_page_ids = array_map( 'absint', developer_starter_get_left_nav_excluded_page_ids() );
        }

        if ( is_page() && ! empty( $excluded_page_ids ) ) {
            $current_page_id = absint( get_queried_object_id() );

            if ( $current_page_id > 0 && in_array( $current_page_id, $excluded_page_ids, true ) ) {
                return false;
            }
        }

        $display_mode = (string) developer_starter_get_option( 'left_nav_display_mode', 'all' );
        if ( ! in_array( $display_mode, array( 'all', 'except_home' ), true ) ) {
            $display_mode = 'all';
        }

        if ( 'except_home' === $display_mode && ( is_front_page() || is_home() ) ) {
            return false;
        }

        return true;
    }
}
