<?php
/**
 * Runtime optimization helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_dangerous_runtime_optimization_keys' ) ) {
    /**
     * 获取可能影响插件、编辑器、REST 客户端或自动更新策略的运行优化开关。
     *
     * @return array<int,string>
     */
    function developer_starter_get_dangerous_runtime_optimization_keys() {
        return array(
            'disable_embeds',
            'disable_xmlrpc',
            'disable_rest_api',
            'restrict_rest_api_important',
            'restrict_rest_users',
            'disable_application_passwords',
            'disable_core_auto_update',
            'disable_plugin_auto_update',
            'disable_theme_auto_update',
            'disable_translation_auto_update',
            'disable_update_emails',
            'disable_gutenberg',
            'disable_block_widgets',
            'remove_json_api_link',
            'remove_gutenberg_css',
            'remove_global_styles',
            'disable_jquery_migrate',
            'disable_external_google_fonts',
            'disable_wp_core_ai',
        );
    }
}

if ( ! function_exists( 'developer_starter_runtime_optimization_enabled' ) ) {
    /**
     * 判断运行优化是否启用。危险优化会被兼容回滚模式统一短路。
     *
     * @param string $key 选项键名。
     * @return bool
     */
    function developer_starter_runtime_optimization_enabled( $key ) {
        if ( ! function_exists( 'developer_starter_get_option' ) ) {
            return false;
        }

        $key = sanitize_key( (string) $key );
        if ( '' === $key || ! developer_starter_get_option( $key, '' ) ) {
            return false;
        }

        $dangerous = in_array( $key, developer_starter_get_dangerous_runtime_optimization_keys(), true );
        if ( $dangerous && developer_starter_get_option( 'runtime_compat_safe_mode', '' ) ) {
            return false;
        }

        /**
         * 允许站点或插件按需接管某个运行优化开关的最终启用状态。
         *
         * @param bool   $enabled   当前是否启用。
         * @param string $key       选项键名。
         * @param bool   $dangerous 是否为高兼容风险开关。
         */
        return (bool) apply_filters( 'developer_starter_runtime_optimization_enabled', true, $key, $dangerous );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_runtime_whitelist_field' ) ) {
    /**
     * 清洗运行优化相关白名单字段。
     *
     * @param string $option_key 字段键名。
     * @param mixed  $value      原始值。
     * @return string
     */
    function developer_starter_sanitize_runtime_whitelist_field( $option_key, $value ) {
        $option_key = sanitize_key( (string) $option_key );
        $value      = wp_strip_all_tags( (string) $value );
        $value      = preg_replace( "/\r\n|\r/u", "\n", $value );
        $lines      = preg_split( '/[\n,]+/', (string) $value );
        $clean      = array();

        if ( ! is_array( $lines ) ) {
            return '';
        }

        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( '' === $line || 0 === strpos( $line, '#' ) ) {
                continue;
            }
            $line = preg_replace( '/\s+/', '', $line );
            if ( '' === $line ) {
                continue;
            }

            switch ( $option_key ) {
                case 'runtime_rest_whitelist_prefixes':
                    $line = strtok( $line, '?' );
                    $line = '/' . ltrim( (string) $line, '/' );
                    if ( 0 !== strpos( $line, '/wp-json/' ) || ! preg_match( '#\A/wp-json/[A-Za-z0-9._~/%-]+\z#', $line ) ) {
                        continue 2;
                    }
                    $line = trailingslashit( untrailingslashit( $line ) );
                    break;

                case 'runtime_application_passwords_allowlist':
                    $line = strtolower( $line );
                    if ( preg_match( '/\Auser:([1-9][0-9]*)\z/', $line, $matches ) ) {
                        $line = 'user:' . $matches[1];
                    } elseif ( preg_match( '/\Arole:([a-z0-9_-]+)\z/', $line, $matches ) ) {
                        $line = 'role:' . sanitize_key( $matches[1] );
                    } else {
                        continue 2;
                    }
                    break;

                case 'runtime_auto_update_allowlist':
                    $line = strtolower( $line );
                    if ( ! preg_match( '#\A(?:core:(?:major|minor|dev|\*)|plugin:[a-z0-9._/-]+|theme:[a-z0-9._-]+|translation:[a-z0-9_.*-]+)\z#', $line ) ) {
                        continue 2;
                    }
                    break;

                case 'runtime_block_editor_allowlist':
                    $line = strtolower( $line );
                    if ( 'widgets' === $line ) {
                        $line = 'screen:widgets';
                    } elseif ( preg_match( '/\Apost_type:([a-z0-9_-]+|\*)\z/', $line, $matches ) ) {
                        $line = ( '*' === $matches[1] ) ? 'post_type:*' : 'post_type:' . sanitize_key( $matches[1] );
                    } elseif ( 'screen:widgets' !== $line ) {
                        continue 2;
                    }
                    break;

                case 'runtime_style_output_allowlist':
                    if ( 0 === strpos( $line, '/' ) ) {
                        $line = 'path:' . $line;
                    }
                    if ( preg_match( '#\Apath:/[A-Za-z0-9._~/%-]*\z#', $line ) ) {
                        $path = substr( $line, 5 );
                        $line = 'path:' . trailingslashit( untrailingslashit( $path ) );
                    } elseif ( preg_match( '/\Astyle:([A-Za-z0-9_.-]+|\*)\z/', $line, $matches ) ) {
                        $line = 'style:' . strtolower( $matches[1] );
                    } else {
                        continue 2;
                    }
                    break;

                default:
                    $line = sanitize_text_field( $line );
                    if ( '' === $line ) {
                        continue 2;
                    }
                    break;
            }

            $clean[] = $line;
        }

        return implode( "\n", array_values( array_unique( $clean ) ) );
    }
}

if ( ! function_exists( 'developer_starter_get_runtime_whitelist_entries' ) ) {
    /**
     * 读取并规范化运行优化白名单。
     *
     * @param string            $option_key      字段键名。
     * @param array<int,string> $default_entries 默认白名单。
     * @return array<int,string>
     */
    function developer_starter_get_runtime_whitelist_entries( $option_key, $default_entries = array() ) {
        $option_key = sanitize_key( (string) $option_key );
        $entries    = is_array( $default_entries ) ? $default_entries : array();
        $raw        = function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( $option_key, '' ) : '';

        if ( is_string( $raw ) && trim( $raw ) !== '' ) {
            $stored = developer_starter_sanitize_runtime_whitelist_field( $option_key, $raw );
            if ( '' !== $stored ) {
                $entries = array_merge( $entries, explode( "\n", $stored ) );
            }
        } elseif ( is_array( $raw ) ) {
            $stored = developer_starter_sanitize_runtime_whitelist_field( $option_key, implode( "\n", $raw ) );
            if ( '' !== $stored ) {
                $entries = array_merge( $entries, explode( "\n", $stored ) );
            }
        }

        $entries = array_values( array_unique( array_filter( array_map( 'trim', $entries ) ) ) );

        /**
         * 允许插件扩展运行优化白名单。
         *
         * @param array<int,string> $entries    白名单条目。
         * @param string            $option_key 字段键名。
         */
        $entries = apply_filters( 'developer_starter_runtime_whitelist_entries', $entries, $option_key );
        if ( ! is_array( $entries ) ) {
            return array();
        }

        return array_values( array_unique( array_filter( array_map( 'trim', $entries ) ) ) );
    }
}

if ( ! function_exists( 'developer_starter_runtime_request_path' ) ) {
    /**
     * 获取当前请求路径，剔除查询字符串和控制字符。
     *
     * @return string
     */
    function developer_starter_runtime_request_path() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $request_uri = (string) preg_replace( '/[\r\n\x00-\x1F\x7F]+/', '', $request_uri );
        $path        = wp_parse_url( $request_uri, PHP_URL_PATH );
        $path        = is_string( $path ) ? $path : '';

        return '/' . ltrim( $path, '/' );
    }
}

if ( ! function_exists( 'developer_starter_runtime_normalize_path' ) ) {
    /**
     * 规范化运行时路径，剔除查询字符串并保持前导斜杠。
     *
     * @param string $path 路径或 URL。
     * @return string
     */
    function developer_starter_runtime_normalize_path( $path ) {
        $path = is_scalar( $path ) ? (string) $path : '';
        $path = (string) preg_replace( '/[\r\n\x00-\x1F\x7F]+/', '', $path );

        $parsed_path = wp_parse_url( $path, PHP_URL_PATH );
        if ( is_string( $parsed_path ) && '' !== $parsed_path ) {
            $path = $parsed_path;
        } else {
            $path = strtok( $path, '?' );
            $path = is_string( $path ) ? $path : '';
        }

        $path = '/' . ltrim( $path, '/' );
        $path = (string) preg_replace( '#/+#', '/', $path );

        return $path;
    }
}

if ( ! function_exists( 'developer_starter_runtime_request_path_candidates' ) ) {
    /**
     * 获取用于白名单匹配的请求路径候选值，兼容 WordPress 子目录安装。
     *
     * @param string $request_path 请求路径。
     * @return array<int,string>
     */
    function developer_starter_runtime_request_path_candidates( $request_path ) {
        $request_path = developer_starter_runtime_normalize_path( $request_path );
        $candidates   = array( $request_path );
        $base_urls    = array();

        if ( function_exists( 'home_url' ) ) {
            $base_urls[] = home_url( '/' );
        }
        if ( function_exists( 'site_url' ) ) {
            $base_urls[] = site_url( '/' );
        }

        foreach ( array_unique( array_filter( $base_urls ) ) as $base_url ) {
            $base_path = developer_starter_runtime_normalize_path( $base_url );
            $base_path = untrailingslashit( $base_path );

            if ( '' === $base_path || '/' === $base_path ) {
                continue;
            }

            if ( $request_path === $base_path ) {
                $candidates[] = '/';
                continue;
            }

            $base_prefix = trailingslashit( $base_path );
            if ( 0 === strpos( $request_path, $base_prefix ) ) {
                $candidates[] = developer_starter_runtime_normalize_path( substr( $request_path, strlen( $base_path ) ) );
            }
        }

        return array_values( array_unique( array_filter( $candidates ) ) );
    }
}

if ( ! function_exists( 'developer_starter_runtime_request_matches_prefix' ) ) {
    /**
     * 判断当前请求路径是否匹配允许前缀，兼容子目录安装。
     *
     * @param string $request_path 请求路径。
     * @param string $prefix       允许前缀。
     * @return bool
     */
    function developer_starter_runtime_request_matches_prefix( $request_path, $prefix ) {
        $prefix = developer_starter_runtime_normalize_path( $prefix );

        $prefix_exact = untrailingslashit( $prefix );
        $match_prefix = trailingslashit( $prefix_exact );

        foreach ( developer_starter_runtime_request_path_candidates( $request_path ) as $candidate ) {
            $candidate = developer_starter_runtime_normalize_path( $candidate );

            if ( untrailingslashit( $candidate ) === $prefix_exact ) {
                return true;
            }

            if ( '/' === $match_prefix || 0 === strpos( $candidate, $match_prefix ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_get_runtime_rest_whitelist_prefixes' ) ) {
    /**
     * 获取 REST API 放行路径前缀。
     *
     * @return array<int,string>
     */
    function developer_starter_get_runtime_rest_whitelist_prefixes() {
        $defaults = array(
            '/wp-json/qivoting/',
            '/wp-json/qibbs/',
        );

        return developer_starter_get_runtime_whitelist_entries( 'runtime_rest_whitelist_prefixes', $defaults );
    }
}

if ( ! function_exists( 'developer_starter_runtime_application_passwords_user_allowed' ) ) {
    /**
     * 判断用户是否在 Application Passwords 放行名单中。
     *
     * @param mixed $user 用户对象。
     * @return bool
     */
    function developer_starter_runtime_application_passwords_user_allowed( $user ) {
        if ( ! $user instanceof WP_User ) {
            $user = get_userdata( absint( $user ) );
        }

        if ( ! $user instanceof WP_User ) {
            return false;
        }

        $entries = developer_starter_get_runtime_whitelist_entries( 'runtime_application_passwords_allowlist' );
        $allowed = false;

        foreach ( $entries as $entry ) {
            if ( 'user:' . (int) $user->ID === $entry ) {
                $allowed = true;
                break;
            }
            if ( 0 === strpos( $entry, 'role:' ) ) {
                $role = substr( $entry, 5 );
                if ( in_array( $role, (array) $user->roles, true ) ) {
                    $allowed = true;
                    break;
                }
            }
        }

        return (bool) apply_filters( 'developer_starter_runtime_application_passwords_user_allowed', $allowed, $user, $entries );
    }
}

if ( ! function_exists( 'developer_starter_runtime_auto_update_item_keys' ) ) {
    /**
     * 提取自动更新对象可用于白名单匹配的键。
     *
     * @param mixed $item 更新对象。
     * @return array<int,string>
     */
    function developer_starter_runtime_auto_update_item_keys( $item ) {
        $keys = array();

        if ( is_object( $item ) ) {
            foreach ( array( 'plugin', 'slug', 'theme', 'stylesheet', 'template', 'language', 'locale' ) as $property ) {
                if ( isset( $item->{$property} ) && is_scalar( $item->{$property} ) ) {
                    $value = strtolower( trim( (string) $item->{$property} ) );
                    if ( '' !== $value ) {
                        $keys[] = $value;
                        if ( false !== strpos( $value, '/' ) ) {
                            $keys[] = strtok( $value, '/' );
                        }
                    }
                }
            }
        } elseif ( is_string( $item ) ) {
            $keys[] = strtolower( trim( $item ) );
        }

        return array_values( array_unique( array_filter( $keys ) ) );
    }
}

if ( ! function_exists( 'developer_starter_runtime_core_update_matches_allowlist' ) ) {
    /**
     * 判断核心更新是否匹配白名单。
     *
     * @param mixed             $item    核心更新对象。
     * @param array<int,string> $entries 白名单条目。
     * @return bool
     */
    function developer_starter_runtime_core_update_matches_allowlist( $item, $entries ) {
        if ( in_array( 'core:*', $entries, true ) ) {
            return true;
        }

        $new_version = is_object( $item ) && isset( $item->version ) ? (string) $item->version : '';
        $current     = get_bloginfo( 'version' );
        $is_minor    = false;
        $is_dev      = false;

        if ( '' !== $new_version && '' !== $current ) {
            $current_parts = explode( '.', preg_replace( '/[^0-9.].*$/', '', $current ) );
            $new_parts     = explode( '.', preg_replace( '/[^0-9.].*$/', '', $new_version ) );
            $is_minor      = isset( $current_parts[0], $current_parts[1], $new_parts[0], $new_parts[1] )
                && $current_parts[0] === $new_parts[0]
                && $current_parts[1] === $new_parts[1];
            $is_dev        = ( false !== strpos( $new_version, '-' ) );
        }

        if ( '' === $new_version ) {
            return false;
        }

        if ( $is_minor && in_array( 'core:minor', $entries, true ) ) {
            return true;
        }
        if ( ! $is_minor && in_array( 'core:major', $entries, true ) ) {
            return true;
        }
        if ( $is_dev && in_array( 'core:dev', $entries, true ) ) {
            return true;
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_runtime_auto_update_allowed' ) ) {
    /**
     * 判断某类自动更新是否在放行名单内。
     *
     * @param string $type 更新类型：core/plugin/theme/translation。
     * @param mixed  $item 更新对象。
     * @return bool
     */
    function developer_starter_runtime_auto_update_allowed( $type, $item = null ) {
        $type    = sanitize_key( (string) $type );
        $entries = developer_starter_get_runtime_whitelist_entries( 'runtime_auto_update_allowlist' );
        $allowed = false;

        if ( 'core' === $type ) {
            $allowed = developer_starter_runtime_core_update_matches_allowlist( $item, $entries );
        } elseif ( in_array( $type . ':*', $entries, true ) ) {
            $allowed = true;
        } else {
            $keys = developer_starter_runtime_auto_update_item_keys( $item );
            foreach ( $keys as $key ) {
                if ( in_array( $type . ':' . $key, $entries, true ) ) {
                    $allowed = true;
                    break;
                }
            }
        }

        return (bool) apply_filters( 'developer_starter_runtime_auto_update_allowed', $allowed, $type, $item, $entries );
    }
}

if ( ! function_exists( 'developer_starter_runtime_block_editor_allowed' ) ) {
    /**
     * 判断指定上下文是否允许继续使用区块编辑器。
     *
     * @param string $post_type 文章类型或 screen 标识。
     * @return bool
     */
    function developer_starter_runtime_block_editor_allowed( $post_type = '' ) {
        $context = sanitize_key( (string) $post_type );
        $entries = developer_starter_get_runtime_whitelist_entries(
            'runtime_block_editor_allowlist',
            developer_starter_get_default_runtime_block_editor_allowlist()
        );

        if ( 'widgets' === $context ) {
            return in_array( 'screen:widgets', $entries, true );
        }

        if ( '' === $context ) {
            return false;
        }

        return in_array( 'post_type:*', $entries, true ) || in_array( 'post_type:' . $context, $entries, true );
    }
}

if ( ! function_exists( 'developer_starter_get_default_runtime_block_editor_allowlist' ) ) {
    /**
     * 获取运行优化默认保留的区块编辑器上下文。
     *
     * @return array<int,string>
     */
    function developer_starter_get_default_runtime_block_editor_allowlist() {
        $entries = array();

        $entries = apply_filters( 'developer_starter_default_runtime_block_editor_allowlist', $entries );
        if ( ! is_array( $entries ) ) {
            return array();
        }

        return array_values( array_unique( array_filter( array_map( 'trim', $entries ) ) ) );
    }
}

if ( ! function_exists( 'developer_starter_runtime_style_output_allowed' ) ) {
    /**
     * 判断指定前端样式是否应被保留。
     *
     * @param string $handle 样式 handle。
     * @return bool
     */
    function developer_starter_runtime_style_output_allowed( $handle = '' ) {
        $handle       = strtolower( trim( (string) $handle ) );
        $request_path = developer_starter_runtime_request_path();
        $entries      = developer_starter_get_runtime_whitelist_entries( 'runtime_style_output_allowlist' );
        $allowed      = false;

        foreach ( $entries as $entry ) {
            if ( 'style:*' === $entry || ( '' !== $handle && 'style:' . $handle === $entry ) ) {
                $allowed = true;
                break;
            }
            if ( 0 === strpos( $entry, 'path:' ) && developer_starter_runtime_request_matches_prefix( $request_path, substr( $entry, 5 ) ) ) {
                $allowed = true;
                break;
            }
        }

        return (bool) apply_filters( 'developer_starter_runtime_style_output_allowed', $allowed, $handle, $request_path, $entries );
    }
}

if ( ! function_exists( 'developer_starter_optimizations' ) ) {
    /**
     * 运行时优化总开关。
     *
     * @return void
     */
    function developer_starter_optimizations() {
        // 禁用 Emoji 脚本
        if ( developer_starter_get_option( 'disable_emoji', '' ) ) {
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_action( 'admin_print_styles', 'print_emoji_styles' );
            remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
            remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
            remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
            add_filter( 'tiny_mce_plugins', function( $plugins ) {
                return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
            } );
            add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
                if ( 'dns-prefetch' === $relation_type ) {
                    $urls = array_filter( $urls, function( $url ) {
                        return strpos( $url, 'https://s.w.org/images/core/emoji/' ) === false;
                    } );
                }
                return $urls;
            }, 10, 2 );
        }

        // 禁用 oEmbed
        if ( developer_starter_runtime_optimization_enabled( 'disable_embeds' ) ) {
            remove_action( 'rest_api_init', 'wp_oembed_register_route' );
            remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
            remove_action( 'wp_head', 'wp_oembed_add_host_js' );
            add_filter( 'embed_oembed_discover', '__return_false' );
            add_filter( 'rewrite_rules_array', function( $rules ) {
                foreach ( $rules as $rule => $rewrite ) {
                    if ( strpos( $rewrite, 'embed=true' ) !== false ) {
                        unset( $rules[ $rule ] );
                    }
                }
                return $rules;
            } );
        }

        // 禁用 XML-RPC
        if ( developer_starter_runtime_optimization_enabled( 'disable_xmlrpc' ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            add_filter( 'wp_headers', function( $headers ) {
                unset( $headers['X-Pingback'] );
                return $headers;
            } );
            remove_action( 'wp_head', 'rsd_link' );
        }

        // 隐藏 WordPress 版本号（仅移除HTML中的generator标签，不影响资源文件版本号）
        if ( developer_starter_get_option( 'remove_wp_version', '' ) ) {
            remove_action( 'wp_head', 'wp_generator' );
            add_filter( 'the_generator', '__return_empty_string' );
            // 注意：资源文件版本号的移除由独立选项 remove_assets_version 控制
        }

        // 限制 REST API 访问
        if ( developer_starter_runtime_optimization_enabled( 'disable_rest_api' ) ) {
            add_filter( 'rest_authentication_errors', function( $result ) {
                if ( ! empty( $result ) ) {
                    return $result;
                }

                // 白名单：默认允许启灵系列插件的 API，请在后台按需追加第三方 REST 路径。
                $request_path         = developer_starter_runtime_request_path();
                $allowed_api_prefixes = developer_starter_get_runtime_rest_whitelist_prefixes();

                /**
                 * 过滤 REST API 白名单前缀
                 *
                 * @param array $allowed_api_prefixes 允许通过的 API 路径前缀
                 */
                $allowed_api_prefixes = apply_filters( 'developer_starter_rest_api_whitelist', $allowed_api_prefixes );

                foreach ( $allowed_api_prefixes as $prefix ) {
                    if ( developer_starter_runtime_request_matches_prefix( $request_path, $prefix ) ) {
                        return $result;
                    }
                }

                if ( ! is_user_logged_in() ) {
                    return new WP_Error( 'rest_not_logged_in', '仅允许登录用户访问 REST API', array( 'status' => 401 ) );
                }
                return $result;
            } );
        }

        if ( developer_starter_runtime_optimization_enabled( 'restrict_rest_api_important' ) && ! developer_starter_runtime_optimization_enabled( 'disable_rest_api' ) ) {
            add_filter( 'rest_authentication_errors', function( $result ) {
                if ( ! empty( $result ) ) {
                    return $result;
                }

                if ( is_user_logged_in() ) {
                    return $result;
                }

                $request_uri = developer_starter_runtime_request_path();
                $current_route = '';
                if ( $request_uri !== '' ) {
                    $parts = explode( '?', $request_uri, 2 );
                    $path = $parts[0];
                    $prefix = rest_get_url_prefix();
                    $needle = '/' . $prefix . '/';
                    $pos = strpos( $path, $needle );
                    if ( $pos !== false ) {
                        $current_route = trim( substr( $path, $pos + strlen( $needle ) ), '/' );
                    }
                }

                if ( $current_route === '' || preg_match( '#^(wp/v2|oembed/1.0)(/.*)?$#', $current_route ) ) {
                    return new WP_Error( 'rest_forbidden', 'This feature is not available.', array( 'status' => 403 ) );
                }

                return $result;
            }, 9 );
        }

        // 精细化拦截 REST 用户端点（仅游客）
        if ( developer_starter_runtime_optimization_enabled( 'restrict_rest_users' ) && ! developer_starter_runtime_optimization_enabled( 'disable_rest_api' ) ) {
            add_filter( 'rest_authentication_errors', function( $result ) {
                if ( ! empty( $result ) || is_user_logged_in() ) {
                    return $result;
                }

                $request_uri = developer_starter_runtime_request_path();
                if ( $request_uri === '' ) {
                    return $result;
                }

                $parts = explode( '?', $request_uri, 2 );
                $path = isset( $parts[0] ) ? (string) $parts[0] : '';
                $prefix = rest_get_url_prefix();
                $needle = '/' . $prefix . '/';
                $pos = strpos( $path, $needle );
                if ( $pos === false ) {
                    return $result;
                }

                $current_route = trim( substr( $path, $pos + strlen( $needle ) ), '/' );
                if ( preg_match( '#^wp/v2/users(?:/.*)?$#', $current_route ) ) {
                    return new WP_Error( 'rest_users_forbidden', 'This feature is not available.', array( 'status' => 403 ) );
                }

                return $result;
            }, 8 );
        }

        // 禁用 Application Passwords
        if ( developer_starter_runtime_optimization_enabled( 'disable_application_passwords' ) ) {
            add_filter( 'wp_is_application_passwords_available_for_user', function( $available, $user ) {
                if ( developer_starter_runtime_application_passwords_user_allowed( $user ) ) {
                    return $available;
                }

                return false;
            }, 10, 2 );
        }

        // 移除短链接
        if ( developer_starter_get_option( 'remove_shortlink', '' ) ) {
            remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
            remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
        }

        // 移除 RSD/WLW 链接
        if ( developer_starter_get_option( 'remove_rsd_wlw', '' ) ) {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
        }

        // 禁用 Pingback/Trackback
        if ( developer_starter_get_option( 'disable_pingback', '' ) ) {
            // 禁用 pingback
            add_filter( 'xmlrpc_methods', function( $methods ) {
                unset( $methods['pingback.ping'] );
                unset( $methods['pingback.extensions.getPingbacks'] );
                return $methods;
            } );
            // 移除 X-Pingback header
            add_filter( 'wp_headers', function( $headers ) {
                unset( $headers['X-Pingback'] );
                return $headers;
            } );
            // 禁用 trackback
            add_filter( 'pings_open', '__return_false', 9999 );
            // 关闭文章的 ping 状态
            add_action( 'pre_ping', function( &$links ) {
                $links = array();
            } );
        }

        // 限制修订版本
        if ( developer_starter_get_option( 'disable_revisions', '' ) ) {
            if ( ! defined( 'WP_POST_REVISIONS' ) ) {
                define( 'WP_POST_REVISIONS', 3 );
            }
        }

        // 自动更新控制（默认不干预 WordPress 官方更新策略）
        if ( developer_starter_runtime_optimization_enabled( 'disable_core_auto_update' ) ) {
            add_filter( 'auto_update_core', function( $update, $item = null ) {
                return developer_starter_runtime_auto_update_allowed( 'core', $item ) ? $update : false;
            }, 10, 2 );
        }

        if ( developer_starter_runtime_optimization_enabled( 'disable_plugin_auto_update' ) ) {
            add_filter( 'auto_update_plugin', function( $update, $item = null ) {
                return developer_starter_runtime_auto_update_allowed( 'plugin', $item ) ? $update : false;
            }, 10, 2 );
        }

        if ( developer_starter_runtime_optimization_enabled( 'disable_theme_auto_update' ) ) {
            add_filter( 'auto_update_theme', function( $update, $item = null ) {
                return developer_starter_runtime_auto_update_allowed( 'theme', $item ) ? $update : false;
            }, 10, 2 );
        }

        if ( developer_starter_runtime_optimization_enabled( 'disable_translation_auto_update' ) ) {
            add_filter( 'auto_update_translation', function( $update, $item = null ) {
                return developer_starter_runtime_auto_update_allowed( 'translation', $item ) ? $update : false;
            }, 10, 2 );
        }

        if ( developer_starter_runtime_optimization_enabled( 'disable_update_emails' ) ) {
            add_filter( 'auto_core_update_send_email', '__return_false', 10, 4 );
            add_filter( 'send_core_update_notification_email', '__return_false', 10, 2 );
            add_filter( 'auto_plugin_update_send_email', '__return_false', 10, 2 );
            add_filter( 'auto_theme_update_send_email', '__return_false', 10, 2 );
        }

        // 禁用 Gutenberg
        if ( developer_starter_runtime_optimization_enabled( 'disable_gutenberg' ) ) {
            add_filter( 'use_block_editor_for_post', function( $use_block_editor, $post ) {
                $post_type = ( $post instanceof WP_Post ) ? $post->post_type : '';
                return developer_starter_runtime_block_editor_allowed( $post_type ) ? $use_block_editor : false;
            }, 999, 2 );
            add_filter( 'use_block_editor_for_post_type', function( $use_block_editor, $post_type ) {
                return developer_starter_runtime_block_editor_allowed( $post_type ) ? $use_block_editor : false;
            }, 999, 2 );
            add_action( 'wp_enqueue_scripts', function() {
                foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'wc-block-style', 'global-styles' ) as $handle ) {
                    if ( ! developer_starter_runtime_style_output_allowed( $handle ) ) {
                        wp_dequeue_style( $handle );
                    }
                }
            }, 100 );
        }

        // 禁用 WordPress 7.0 Core AI
        if ( developer_starter_runtime_optimization_enabled( 'disable_wp_core_ai' ) ) {
            add_filter( 'wp_ai_support', '__return_false', 999 );
            add_filter( 'wp_core_ai_enabled', '__return_false', 999 );
            
            add_action( 'admin_menu', function() {
                remove_submenu_page( 'options-general.php', 'options-connectors.php' );
                remove_menu_page( 'options-connectors.php' );
            }, 999 );
            
            add_action( 'admin_init', function() {
                if ( isset( $GLOBALS['pagenow'] ) && 'options-connectors.php' === $GLOBALS['pagenow'] ) {
                    wp_die( esc_html__( '该功能已被系统优化模块禁用，以保障访问速度与合规安全。', 'developer-starter' ) );
                }
            }, 0 );
        }

        // 后台编辑器优化（Gutenberg 轻量化）
        if ( developer_starter_get_option( 'admin_disable_remote_block_patterns', '' ) ) {
            add_filter( 'should_load_remote_block_patterns', '__return_false' );
        }

        if ( developer_starter_get_option( 'admin_disable_block_directory', '' ) ) {
            add_filter( 'block_directory_enabled', '__return_false' );
            add_filter( 'block_editor_settings_all', function( $settings ) {
                if ( is_array( $settings ) ) {
                    $settings['enableBlockDirectory'] = false;
                }
                return $settings;
            }, 20 );
        }

        if ( developer_starter_get_option( 'admin_disable_openverse', '' ) ) {
            add_filter( 'block_editor_settings_all', function( $settings ) {
                if ( is_array( $settings ) ) {
                    $settings['enableOpenverseMediaCategory'] = false;
                }
                return $settings;
            }, 20 );
        }

        if ( developer_starter_get_option( 'admin_reduce_editor_preload', '' ) ) {
            add_filter( 'block_editor_rest_api_preload_paths', function( $preload_paths ) {
                if ( ! is_array( $preload_paths ) ) {
                    return $preload_paths;
                }

                $skip_needles = array(
                    '/wp/v2/block-directory/search',
                    '/wp/v2/pattern-directory/patterns',
                );

                $filtered = array();
                $seen = array();

                foreach ( $preload_paths as $entry ) {
                    $path = '';
                    $method = 'GET';

                    if ( is_array( $entry ) ) {
                        $path = isset( $entry[0] ) ? (string) $entry[0] : '';
                        $method = isset( $entry[1] ) ? (string) $entry[1] : 'GET';
                    } elseif ( is_string( $entry ) ) {
                        $path = $entry;
                    }

                    $path = trim( $path );
                    if ( $path === '' ) {
                        continue;
                    }

                    $skip = false;
                    foreach ( $skip_needles as $needle ) {
                        if ( strpos( $path, $needle ) !== false ) {
                            $skip = true;
                            break;
                        }
                    }
                    if ( $skip ) {
                        continue;
                    }

                    $dedupe_key = md5( $method . '|' . $path );
                    if ( isset( $seen[ $dedupe_key ] ) ) {
                        continue;
                    }
                    $seen[ $dedupe_key ] = true;

                    $filtered[] = $entry;
                }

                // 仅在明确设置上限时才截断，默认不截断，避免第三方端点被误裁剪导致编辑器空白。
                $preload_limit = (int) apply_filters( 'developer_starter_editor_preload_limit', 0 );
                if ( $preload_limit > 0 && count( $filtered ) > $preload_limit ) {
                    $filtered = array_slice( $filtered, 0, $preload_limit );
                }

                return $filtered;
            }, 10 );
        }

        // 禁用区块小工具（恢复经典小工具界面）
        if ( developer_starter_runtime_optimization_enabled( 'disable_block_widgets' ) && ! developer_starter_runtime_block_editor_allowed( 'widgets' ) ) {
            add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
            add_filter( 'use_widgets_block_editor', '__return_false' );
        }

        // 仪表盘减负
        if ( developer_starter_get_option( 'admin_disable_welcome_panel', '' ) ) {
            add_action( 'admin_init', function() {
                remove_action( 'welcome_panel', 'wp_welcome_panel' );
            }, 20 );
        }

        if ( developer_starter_get_option( 'admin_disable_default_dashboard_widgets', '' ) ) {
            add_action( 'wp_dashboard_setup', function() {
                remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
                remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
                remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
                remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
                remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
                remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );

                remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
                remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
                remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
            }, 999 );
        }

        // 隐藏普通用户前台顶部 Admin Bar（管理员除外）
        if ( developer_starter_get_option( 'hide_admin_bar_for_users', '' ) ) {
            add_filter( 'show_admin_bar', function( $show ) {
                // 管理员始终显示
                if ( current_user_can( 'manage_options' ) ) {
                    return $show;
                }
                // 非管理员不显示
                return false;
            } );
        }

        // ===== 输出优化（Head 清理）=====

        // 移除相邻文章链接
        if ( developer_starter_get_option( 'remove_adjacent_posts', '' ) ) {
            remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
        }

        // 移除 Feed 链接
        if ( developer_starter_get_option( 'remove_feed_links', '' ) ) {
            remove_action( 'wp_head', 'feed_links_extra', 3 );
            remove_action( 'wp_head', 'feed_links', 2 );
        }

        // 移除 JSON API 链接
        if ( developer_starter_runtime_optimization_enabled( 'remove_json_api_link' ) ) {
            remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
            remove_action( 'template_redirect', 'rest_output_link_header', 11 );
        }

        // 移除 DNS 预取提示
        if ( developer_starter_get_option( 'remove_dns_prefetch_hints', '' ) ) {
            add_filter( 'wp_resource_hints', function( $hints, $relation_type ) {
                if ( 'dns-prefetch' === $relation_type ) {
                    return array();
                }
                return $hints;
            }, 10, 2 );
        }

        // 移除 Gutenberg 样式
        if ( developer_starter_runtime_optimization_enabled( 'remove_gutenberg_css' ) ) {
            add_action( 'wp_enqueue_scripts', function() {
                foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles' ) as $handle ) {
                    if ( ! developer_starter_runtime_style_output_allowed( $handle ) ) {
                        wp_dequeue_style( $handle );
                    }
                }
            }, 999 );
        }

        // 移除全局样式
        if ( developer_starter_runtime_optimization_enabled( 'remove_global_styles' ) && ! developer_starter_runtime_style_output_allowed( 'global-styles' ) ) {
            remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
            remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
        }

        // 禁用 jQuery Migrate
        if ( developer_starter_runtime_optimization_enabled( 'disable_jquery_migrate' ) ) {
            $remove_jquery_migrate = static function( $scripts ) {
                if ( ! $scripts || ! isset( $scripts->registered ) || ! is_array( $scripts->registered ) ) {
                    return;
                }

                foreach ( array( 'jquery', 'jquery-core' ) as $handle ) {
                    if ( isset( $scripts->registered[ $handle ] ) && ! empty( $scripts->registered[ $handle ]->deps ) ) {
                        $scripts->registered[ $handle ]->deps = array_values(
                            array_diff( $scripts->registered[ $handle ]->deps, array( 'jquery-migrate' ) )
                        );
                    }
                }

                if ( isset( $scripts->registered['jquery-migrate'] ) ) {
                    unset( $scripts->registered['jquery-migrate'] );
                }

                foreach ( array( 'queue', 'to_do', 'done' ) as $queue_prop ) {
                    if ( isset( $scripts->{$queue_prop} ) && is_array( $scripts->{$queue_prop} ) ) {
                        $scripts->{$queue_prop} = array_values(
                            array_filter(
                                $scripts->{$queue_prop},
                                static function( $item ) {
                                    return $item !== 'jquery-migrate';
                                }
                            )
                        );
                    }
                }
            };

            add_action( 'wp_default_scripts', function( $scripts ) use ( $remove_jquery_migrate ) {
                if ( is_admin() ) {
                    return;
                }

                $remove_jquery_migrate( $scripts );
            }, 1000 );

            $force_remove_jquery_migrate = static function() use ( $remove_jquery_migrate ) {
                if ( is_admin() ) {
                    return;
                }

                if ( function_exists( 'wp_scripts' ) ) {
                    $scripts = wp_scripts();
                    $remove_jquery_migrate( $scripts );
                }

                if ( wp_script_is( 'jquery-migrate', 'enqueued' ) ) {
                    wp_dequeue_script( 'jquery-migrate' );
                }

                if ( wp_script_is( 'jquery-migrate', 'registered' ) ) {
                    wp_deregister_script( 'jquery-migrate' );
                }
            };

            add_action( 'wp_enqueue_scripts', $force_remove_jquery_migrate, 9999 );
            add_action( 'wp_print_scripts', $force_remove_jquery_migrate, 1 );
            add_action( 'wp_print_footer_scripts', $force_remove_jquery_migrate, 1 );

            add_filter( 'print_scripts_array', function( $to_do ) {
                if ( is_admin() || ! is_array( $to_do ) ) {
                    return $to_do;
                }

                return array_values(
                    array_filter(
                        $to_do,
                        static function( $handle ) {
                            return $handle !== 'jquery-migrate';
                        }
                    )
                );
            }, 9999 );
        }

        // 禁用 Gravatar
        if ( developer_starter_get_option( 'disable_gravatar', '' ) ) {
            // 强制使用默认头像，防止 Gravatar 请求
            add_filter( 'get_avatar', function( $avatar, $id_or_email, $size, $default, $alt ) {
                // 如果已经有本地头像（例如插件生成的），直接返回
                if ( strpos( $avatar, 'gravatar.com' ) === false ) {
                    return $avatar;
                }
                // 否则返回空字符串 或 强制使用默认头像 URL
                // 这里返回一个本地默认头像的 img 标签
                $default_avatar = get_stylesheet_directory_uri() . '/assets/images/default-avatar.png';
                $avatar = "<img alt='{$alt}' src='{$default_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
                return $avatar;
            }, 1, 5 );

            // 阻止 Gravatar DNS 预解析
            add_filter( 'wp_resource_hints', function( $hints, $relation_type ) {
                if ( 'dns-prefetch' === $relation_type ) {
                    foreach ( $hints as $key => $url ) {
                        if ( strpos( $url, 'gravatar.com' ) !== false ) {
                            unset( $hints[$key] );
                        }
                    }
                }
                return $hints;
            }, 10, 2 );
        }

        // 上传文件自动重命名
        if ( developer_starter_get_option( 'upload_file_rename', '' ) ) {
            add_filter( 'sanitize_file_name', function( $filename ) {
                $info = pathinfo( $filename );
                $ext = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
                // 生成随机文件名：日期+随机串
                $name = date( 'YmdHis' ) . mt_rand( 1000, 9999 );
                return $name . $ext;
            }, 10 );
        }
    }
}
add_action( 'init', 'developer_starter_optimizations', 1 );
