<?php
/**
 * 子主题兼容桥。
 *
 * 父主题核心能力尽量通过钩子和资源队列兜底，避免子主题覆盖 header/footer
 * 后丢失登录态同步、核心脚本和模块模板覆盖能力。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_is_child_theme_active' ) ) {
    /**
     * 判断当前是否启用了子主题。
     *
     * @return bool
     */
    function developer_starter_is_child_theme_active() {
        return get_stylesheet() !== get_template();
    }
}

if ( ! function_exists( 'developer_starter_normalize_theme_relative_path' ) ) {
    /**
     * 规范化主题内相对路径，避免跨目录读取。
     *
     * @param string $relative_path 主题内相对路径。
     * @return string
     */
    function developer_starter_normalize_theme_relative_path( $relative_path ) {
        if ( ! is_string( $relative_path ) ) {
            return '';
        }

        $relative_path = str_replace( '\\', '/', $relative_path );
        if ( function_exists( 'wp_normalize_path' ) ) {
            $relative_path = wp_normalize_path( $relative_path );
        }

        $relative_path = ltrim( $relative_path, '/' );
        $relative_path = (string) preg_replace( '#/+#', '/', $relative_path );

        if ( '' === $relative_path || false !== strpos( $relative_path, "\0" ) ) {
            return '';
        }

        if ( preg_match( '#(^|/)\.\.(/|$)#', $relative_path ) ) {
            return '';
        }

        if ( function_exists( 'validate_file' ) && 0 !== validate_file( $relative_path ) ) {
            return '';
        }

        return $relative_path;
    }
}

if ( ! function_exists( 'developer_starter_locate_child_aware_template' ) ) {
    /**
     * 查找支持子主题覆盖的模板文件。
     *
     * @param string $relative_path          首选模板相对路径。
     * @param string $fallback_relative_path 备用模板相对路径。
     * @return string
     */
    function developer_starter_locate_child_aware_template( $relative_path, $fallback_relative_path = '' ) {
        $relative_path          = developer_starter_normalize_theme_relative_path( $relative_path );
        $fallback_relative_path = developer_starter_normalize_theme_relative_path( $fallback_relative_path );
        $paths                  = array();

        if ( '' !== $relative_path ) {
            $paths[] = $relative_path;
        }

        if ( '' !== $fallback_relative_path && $fallback_relative_path !== $relative_path ) {
            $paths[] = $fallback_relative_path;
        }

        if ( empty( $paths ) ) {
            return (string) apply_filters(
                'developer_starter_child_aware_template_path',
                '',
                $relative_path,
                $fallback_relative_path,
                array()
            );
        }

        $candidates = array();
        foreach ( $paths as $path ) {
            if ( developer_starter_is_child_theme_active() ) {
                $candidates[] = trailingslashit( get_stylesheet_directory() ) . $path;
            }

            $candidates[] = trailingslashit( get_template_directory() ) . $path;
        }

        $candidates = array_values( array_unique( $candidates ) );
        $candidates = (array) apply_filters(
            'developer_starter_child_aware_template_candidates',
            $candidates,
            $relative_path,
            $fallback_relative_path
        );

        foreach ( $candidates as $candidate ) {
            if ( ! is_string( $candidate ) || '' === $candidate ) {
                continue;
            }

            if ( file_exists( $candidate ) && is_file( $candidate ) ) {
                return (string) apply_filters(
                    'developer_starter_child_aware_template_path',
                    $candidate,
                    $relative_path,
                    $fallback_relative_path,
                    $candidates
                );
            }
        }

        return (string) apply_filters(
            'developer_starter_child_aware_template_path',
            '',
            $relative_path,
            $fallback_relative_path,
            $candidates
        );
    }
}

if ( ! function_exists( 'developer_starter_get_child_theme_required_hooks' ) ) {
    /**
     * 子主题覆盖关键模板时必须保留的 WordPress 钩子。
     *
     * @return array<string,array<int,string>>
     */
    function developer_starter_get_child_theme_required_hooks() {
        $required_hooks = array(
            'header.php' => array( 'wp_head', 'wp_body_open' ),
            'footer.php' => array( 'wp_footer' ),
        );

        return (array) apply_filters( 'developer_starter_child_theme_required_hooks', $required_hooks );
    }
}

if ( ! function_exists( 'developer_starter_child_theme_template_delegates_to_parent' ) ) {
    /**
     * 判断子主题模板是否明确委托给父主题同名模板。
     *
     * @param string $contents 模板内容。
     * @param string $template 模板相对路径。
     * @return bool
     */
    function developer_starter_child_theme_template_delegates_to_parent( $contents, $template ) {
        $contents = (string) $contents;
        $template = developer_starter_normalize_theme_relative_path( (string) $template );

        if ( '' === $contents || '' === $template ) {
            return false;
        }

        if ( preg_match( '/@qiling-delegates-parent-template\s+' . preg_quote( $template, '/' ) . '\b/', $contents ) ) {
            return true;
        }

        return false !== strpos( $contents, 'get_template_directory()' )
            && false !== strpos( $contents, $template );
    }
}

if ( ! function_exists( 'developer_starter_get_child_theme_hook_report' ) ) {
    /**
     * 检查子主题覆盖的关键模板是否缺少必要钩子。
     *
     * @return array<int,array{template:string,path:string,missing_hooks:array<int,string>}>
     */
    function developer_starter_get_child_theme_hook_report() {
        if ( ! developer_starter_is_child_theme_active() ) {
            return array();
        }

        $child_dir = trailingslashit( get_stylesheet_directory() );
        $report    = array();

        foreach ( developer_starter_get_child_theme_required_hooks() as $template => $hooks ) {
            $template = developer_starter_normalize_theme_relative_path( (string) $template );
            if ( '' === $template || empty( $hooks ) || ! is_array( $hooks ) ) {
                continue;
            }

            $path = $child_dir . $template;
            if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
                continue;
            }

            $contents = file_get_contents( $path );
            if ( ! is_string( $contents ) ) {
                continue;
            }

            if ( developer_starter_child_theme_template_delegates_to_parent( $contents, $template ) ) {
                continue;
            }

            $missing_hooks = array();
            foreach ( $hooks as $hook ) {
                $hook = trim( (string) $hook );
                if ( '' === $hook ) {
                    continue;
                }

                if ( ! preg_match( '/\b' . preg_quote( $hook, '/' ) . '\s*\(/', $contents ) ) {
                    $missing_hooks[] = $hook . '()';
                }
            }

            if ( ! empty( $missing_hooks ) ) {
                $report[] = array(
                    'template'      => $template,
                    'path'          => $path,
                    'missing_hooks' => $missing_hooks,
                );
            }
        }

        return $report;
    }
}

if ( ! function_exists( 'developer_starter_admin_child_theme_compat_notice' ) ) {
    /**
     * 后台提示子主题关键模板缺失核心钩子。
     *
     * @return void
     */
    function developer_starter_admin_child_theme_compat_notice() {
        if ( ! developer_starter_is_child_theme_active() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $notice_enabled = (bool) apply_filters( 'developer_starter_child_theme_compat_notice_enabled', true );
        if ( ! $notice_enabled ) {
            return;
        }

        $report = developer_starter_get_child_theme_hook_report();
        if ( empty( $report ) ) {
            return;
        }

        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e( '启灵子主题兼容提醒：', 'developer-starter' ); ?></strong>
                <?php esc_html_e( '子主题覆盖的关键模板缺少必要 WordPress 钩子，可能导致父主题核心脚本、登录态同步、SEO 或插件资源无法输出。', 'developer-starter' ); ?>
            </p>
            <ul>
                <?php foreach ( $report as $item ) : ?>
                    <li>
                        <code><?php echo esc_html( $item['template'] ); ?></code>
                        <?php echo esc_html( sprintf( __( '缺少：%s', 'developer-starter' ), implode( ', ', $item['missing_hooks'] ) ) ); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><?php esc_html_e( '请把这些钩子补回子主题模板。启灵父主题会继续通过兼容桥兜底核心能力，但这些 WordPress 标准钩子仍然必须保留。', 'developer-starter' ); ?></p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'developer_starter_admin_child_theme_compat_notice' );

if ( ! function_exists( 'developer_starter_enqueue_child_theme_core_bridge' ) ) {
    /**
     * 子主题启用时补齐父主题核心前台脚本。
     *
     * 旧子主题可能复制了旧版 header.php，没有父主题新版的登录态同步脚本加载逻辑。
     * 这里通过父主题资源队列补上，保证只要保留 wp_head/wp_footer 就不会丢核心登录态修正。
     *
     * @param string $version 资源版本。
     * @return void
     */
    function developer_starter_enqueue_child_theme_core_bridge( $version = '' ) {
        if ( ! developer_starter_is_child_theme_active() ) {
            return;
        }

        $header_login_enabled = function_exists( 'developer_starter_get_option' )
            ? (bool) developer_starter_get_option( 'header_login_enable', '' )
            : true;
        $is_auth_template_page = function_exists( 'developer_starter_is_auth_template_page' )
            && developer_starter_is_auth_template_page();

        $should_enqueue = $header_login_enabled || $is_auth_template_page;
        $should_enqueue = (bool) apply_filters(
            'developer_starter_child_theme_enqueue_header_auth_bridge',
            $should_enqueue,
            $header_login_enabled,
            $is_auth_template_page
        );

        if ( ! $should_enqueue ) {
            return;
        }

        if (
            wp_script_is( 'developer-starter-header-auth', 'enqueued' )
            || wp_script_is( 'developer-starter-header-auth', 'to_do' )
            || wp_script_is( 'developer-starter-header-auth', 'done' )
        ) {
            return;
        }

        if ( '' === (string) $version && defined( 'DEVELOPER_STARTER_VERSION' ) ) {
            $version = DEVELOPER_STARTER_VERSION;
        }

        $deps = wp_script_is( 'developer-starter-main', 'registered' )
            ? array( 'developer-starter-main' )
            : array();

        wp_enqueue_script(
            'developer-starter-header-auth',
            DEVELOPER_STARTER_ASSETS . '/js/header-auth.js',
            $deps,
            (string) $version,
            true
        );

        $frontend_home_url = function_exists( 'developer_starter_get_frontend_home_url' )
            ? developer_starter_get_frontend_home_url()
            : home_url( '/' );
        $fallback_data     = array(
            'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
            'userStatusNonce' => wp_create_nonce( 'developer_starter_user_status' ),
            'homeUrl'         => $frontend_home_url,
        );
        $fallback_json     = wp_json_encode( $fallback_data );

        if ( is_string( $fallback_json ) ) {
            wp_add_inline_script(
                'developer-starter-header-auth',
                'window.developerStarterData=window.developerStarterData||{};(function(data){for(var key in data){if(Object.prototype.hasOwnProperty.call(data,key)&&!window.developerStarterData[key]){window.developerStarterData[key]=data[key];}}})(' . $fallback_json . ');',
                'before'
            );
        }
    }
}
add_action( 'developer_starter_after_enqueue_scripts', 'developer_starter_enqueue_child_theme_core_bridge', 20 );
