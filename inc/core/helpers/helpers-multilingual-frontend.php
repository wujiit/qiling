<?php
/**
 * Frontend multilingual helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_multilingual_frontend_locale' ) ) {
    /**
     * 获取多语言内容模式下当前前台请求应使用的 locale。
     *
     * @param string $fallback_locale 兜底 locale。
     * @return string
     */
    function developer_starter_get_multilingual_frontend_locale( $fallback_locale = '' ) {
        $fallback_locale = (string) $fallback_locale;

        if ( is_admin() || wp_doing_ajax() ) {
            return $fallback_locale;
        }

        if ( ! function_exists( 'developer_starter_is_multilingual_content_mode' ) || ! developer_starter_is_multilingual_content_mode() ) {
            return $fallback_locale;
        }

        $lang = function_exists( 'developer_starter_get_current_frontend_lang' )
            ? developer_starter_get_current_frontend_lang()
            : '';

        if ( '' === $lang ) {
            return $fallback_locale;
        }

        $target_locale = function_exists( 'developer_starter_get_frontend_locale_by_lang' )
            ? developer_starter_get_frontend_locale_by_lang( $lang )
            : '';

        return '' !== $target_locale ? $target_locale : $fallback_locale;
    }
}

if ( ! function_exists( 'developer_starter_filter_multilingual_frontend_locale' ) ) {
    /**
     * 多语言内容模式下，根据当前前台语言切换主题 locale。
     *
     * @param string $locale 当前 locale。
     * @return string
     */
    function developer_starter_filter_multilingual_frontend_locale( $locale ) {
        return developer_starter_get_multilingual_frontend_locale( $locale );
    }
}
add_filter( 'locale', 'developer_starter_filter_multilingual_frontend_locale', 20 );
add_filter( 'determine_locale', 'developer_starter_filter_multilingual_frontend_locale', 20 );

if ( ! function_exists( 'developer_starter_filter_multilingual_theme_locale' ) ) {
    /**
     * 仅针对主题 textdomain 强制使用当前前台 locale。
     *
     * @param string $locale 当前 theme locale。
     * @param string $domain textdomain。
     * @return string
     */
    function developer_starter_filter_multilingual_theme_locale( $locale, $domain ) {
        if ( 'developer-starter' !== (string) $domain ) {
            return $locale;
        }

        return developer_starter_get_multilingual_frontend_locale( $locale );
    }
}
add_filter( 'theme_locale', 'developer_starter_filter_multilingual_theme_locale', 20, 2 );

if ( ! function_exists( 'developer_starter_switch_multilingual_frontend_locale' ) ) {
    /**
     * 前台多语言内容模式下，显式切换当前 WordPress locale。
     *
     * @return void
     */
    function developer_starter_switch_multilingual_frontend_locale() {
        static $switched = false;

        if ( $switched || is_admin() || wp_doing_ajax() ) {
            return;
        }

        if ( ! function_exists( 'switch_to_locale' ) ) {
            return;
        }

        $locale = developer_starter_get_multilingual_frontend_locale( '' );
        if ( '' === $locale ) {
            return;
        }

        $switched = switch_to_locale( $locale ) || $switched;
    }
}
add_action( 'after_setup_theme', 'developer_starter_switch_multilingual_frontend_locale', 0 );

if ( ! function_exists( 'developer_starter_load_multilingual_theme_textdomain' ) ) {
    /**
     * 前台多语言内容模式下，在基础 textdomain 注册后显式加载当前语言对应的主题 .mo 文件。
     *
     * @return void
     */
    function developer_starter_load_multilingual_theme_textdomain() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        if ( ! function_exists( 'developer_starter_is_multilingual_content_mode' ) || ! developer_starter_is_multilingual_content_mode() ) {
            return;
        }

        $locale = developer_starter_get_multilingual_frontend_locale( '' );
        if ( '' === $locale ) {
            return;
        }

        $languages_dir = trailingslashit( DEVELOPER_STARTER_DIR ) . 'languages';
        $mofile        = trailingslashit( $languages_dir ) . 'developer-starter-' . $locale . '.mo';

        load_theme_textdomain( 'developer-starter', $languages_dir );

        if ( ! is_readable( $mofile ) ) {
            return;
        }

        unload_textdomain( 'developer-starter' );
        load_textdomain( 'developer-starter', $mofile );
    }
}
add_action( 'after_setup_theme', 'developer_starter_load_multilingual_theme_textdomain', 11 );

if ( ! function_exists( 'developer_starter_persist_current_frontend_lang' ) ) {
    /**
     * 记录当前前台语言到 Cookie，保证后续前台导航持续维持同一语言。
     *
     * @return void
     */
    function developer_starter_persist_current_frontend_lang() {
        if (
            headers_sent()
            || is_admin()
            || wp_doing_ajax()
            || ( function_exists( 'developer_starter_is_sitemap_request' ) && developer_starter_is_sitemap_request() )
            || ! function_exists( 'developer_starter_is_multilingual_content_mode' )
            || ! developer_starter_is_multilingual_content_mode()
        ) {
            return;
        }

        $lang = function_exists( 'developer_starter_get_current_frontend_lang' )
            ? developer_starter_get_current_frontend_lang()
            : '';

        if ( '' === $lang ) {
            return;
        }

        $explicit_lang = function_exists( 'developer_starter_get_explicit_frontend_lang_from_request' )
            ? developer_starter_get_explicit_frontend_lang_from_request()
            : '';
        $persisted_lang = function_exists( 'developer_starter_get_persisted_frontend_lang' )
            ? developer_starter_get_persisted_frontend_lang()
            : '';

        // 用户已经在其他标签页显式切过语言时，不要因为当前访问了 /en/... 再把全局偏好写回去。
        if ( '' !== $persisted_lang && '' !== $explicit_lang && $persisted_lang !== $explicit_lang ) {
            return;
        }

        $cookie_name = function_exists( 'developer_starter_get_multilingual_lang_cookie_name' )
            ? developer_starter_get_multilingual_lang_cookie_name()
            : 'developer_starter_front_lang';
        $cookie_version_name = function_exists( 'developer_starter_get_multilingual_lang_cookie_version_name' )
            ? developer_starter_get_multilingual_lang_cookie_version_name()
            : 'developer_starter_front_lang_ver';
        $cookie_version = function_exists( 'developer_starter_get_multilingual_lang_cookie_version' )
            ? developer_starter_get_multilingual_lang_cookie_version()
            : 'v1';
        $cookie_path = function_exists( 'developer_starter_get_raw_home_path' )
            ? '/' . ltrim( developer_starter_get_raw_home_path(), '/' )
            : '/';

        if ( '/' !== $cookie_path ) {
            $cookie_path = untrailingslashit( $cookie_path );
        }

        $secure = is_ssl();

        setcookie( $cookie_name, $lang, time() + YEAR_IN_SECONDS, $cookie_path, COOKIE_DOMAIN, $secure, false );
        $_COOKIE[ $cookie_name ] = $lang;
        setcookie( $cookie_version_name, $cookie_version, time() + YEAR_IN_SECONDS, $cookie_path, COOKIE_DOMAIN, $secure, false );
        $_COOKIE[ $cookie_version_name ] = $cookie_version;
    }
}
add_action( 'send_headers', 'developer_starter_persist_current_frontend_lang', 20 );

if ( ! function_exists( 'developer_starter_redirect_explicit_frontend_lang_to_preference' ) ) {
    /**
     * 当请求 URL 的语言前缀与当前已选择的全局语言偏好不一致时，跳到偏好语言的对应链接。
     *
     * @return void
     */
    function developer_starter_redirect_explicit_frontend_lang_to_preference() {
        $is_builder_request = isset( $_GET['qiling_builder'] ) && sanitize_text_field( wp_unslash( (string) $_GET['qiling_builder'] ) ) === '1';
        if (
            is_admin()
            || wp_doing_ajax()
            || is_feed()
            || is_robots()
            || is_trackback()
            || is_preview()
            || $is_builder_request
            || ( function_exists( 'developer_starter_is_sitemap_request' ) && developer_starter_is_sitemap_request() )
            || ! function_exists( 'developer_starter_is_multilingual_content_mode' )
            || ! developer_starter_is_multilingual_content_mode()
        ) {
            return;
        }

        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
        if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
            return;
        }

        $explicit_lang = function_exists( 'developer_starter_get_explicit_frontend_lang_from_request' )
            ? developer_starter_get_explicit_frontend_lang_from_request()
            : '';
        $preferred_lang = function_exists( 'developer_starter_get_persisted_frontend_lang' )
            ? developer_starter_get_persisted_frontend_lang()
            : '';

        if ( '' === $explicit_lang || '' === $preferred_lang || $explicit_lang === $preferred_lang ) {
            return;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '';
        $current_url = function_exists( 'developer_starter_build_raw_home_url' )
            ? developer_starter_build_raw_home_url( '' !== $request_uri ? $request_uri : '/' )
            : home_url( '' !== $request_uri ? $request_uri : '/' );

        $target_url = '';

        if ( is_singular() && function_exists( 'developer_starter_get_post_url_for_frontend_lang' ) ) {
            $post_id = get_queried_object_id();
            if ( $post_id > 0 ) {
                $target_url = developer_starter_get_post_url_for_frontend_lang( $post_id, $preferred_lang );
                if ( '' !== $target_url && function_exists( 'developer_starter_append_query_string_to_url' ) ) {
                    $target_url = developer_starter_append_query_string_to_url(
                        $target_url,
                        (string) wp_parse_url( $current_url, PHP_URL_QUERY )
                    );
                }
            }
        }

        if ( '' === $target_url && function_exists( 'developer_starter_get_multilingual_url' ) ) {
            $target_url = developer_starter_get_multilingual_url( $preferred_lang, $current_url );
        }

        if ( '' === $target_url ) {
            return;
        }

        if ( untrailingslashit( $current_url ) === untrailingslashit( $target_url ) ) {
            return;
        }

        wp_safe_redirect( $target_url, 302 );
        exit;
    }
}
add_action( 'template_redirect', 'developer_starter_redirect_explicit_frontend_lang_to_preference', 1 );

if ( ! function_exists( 'developer_starter_redirect_to_persisted_frontend_lang' ) ) {
    /**
     * 若前台当前没有语言前缀，则根据已切换的语言偏好自动跳回对应语言 URL。
     *
     * @return void
     */
    function developer_starter_redirect_to_persisted_frontend_lang() {
        $is_builder_request = isset( $_GET['qiling_builder'] ) && sanitize_text_field( wp_unslash( (string) $_GET['qiling_builder'] ) ) === '1';
        if (
            is_admin()
            || wp_doing_ajax()
            || is_feed()
            || is_robots()
            || is_trackback()
            || is_preview()
            || $is_builder_request
            || ( function_exists( 'developer_starter_is_sitemap_request' ) && developer_starter_is_sitemap_request() )
            || ! function_exists( 'developer_starter_is_multilingual_content_mode' )
            || ! developer_starter_is_multilingual_content_mode()
        ) {
            return;
        }

        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
        if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
            return;
        }

        $explicit_lang = function_exists( 'developer_starter_get_explicit_frontend_lang_from_request' )
            ? developer_starter_get_explicit_frontend_lang_from_request()
            : '';
        if ( '' !== $explicit_lang ) {
            return;
        }

        $preferred_lang = function_exists( 'developer_starter_get_persisted_frontend_lang' )
            ? developer_starter_get_persisted_frontend_lang()
            : '';
        if ( '' === $preferred_lang ) {
            return;
        }

        $default_lang = function_exists( 'developer_starter_get_multilingual_default_lang' )
            ? developer_starter_get_multilingual_default_lang()
            : 'zh';
        if ( $preferred_lang === $default_lang ) {
            return;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '';
        $current_url = function_exists( 'developer_starter_build_raw_home_url' )
            ? developer_starter_build_raw_home_url( '' !== $request_uri ? $request_uri : '/' )
            : home_url( '' !== $request_uri ? $request_uri : '/' );

        $target_url = function_exists( 'developer_starter_translate_internal_url_for_frontend_lang' )
            ? developer_starter_translate_internal_url_for_frontend_lang( $current_url, $preferred_lang )
            : '';
        if ( '' === $target_url && function_exists( 'developer_starter_get_multilingual_url' ) ) {
            $target_url = developer_starter_get_multilingual_url( $preferred_lang );
        }
        if ( '' === $target_url ) {
            return;
        }

        if ( untrailingslashit( $current_url ) === untrailingslashit( $target_url ) ) {
            return;
        }

        wp_safe_redirect( $target_url, 302 );
        exit;
    }
}
add_action( 'template_redirect', 'developer_starter_redirect_to_persisted_frontend_lang', 0 );

if ( ! function_exists( 'developer_starter_filter_custom_logo_for_frontend_lang' ) ) {
    /**
     * 多语言内容模式下，让自定义 Logo 链接指向当前语言首页。
     *
     * @param string $html Logo HTML。
     * @return string
     */
    function developer_starter_filter_custom_logo_for_frontend_lang( $html ) {
        if ( is_admin() || ! function_exists( 'developer_starter_is_multilingual_content_mode' ) || ! developer_starter_is_multilingual_content_mode() ) {
            return $html;
        }

        $home_url = function_exists( 'developer_starter_get_frontend_home_url' )
            ? developer_starter_get_frontend_home_url()
            : home_url( '/' );

        return (string) preg_replace(
            '/href=["\'][^"\']*["\']/i',
            'href="' . esc_url( $home_url ) . '"',
            (string) $html,
            1
        );
    }
}
add_filter( 'get_custom_logo', 'developer_starter_filter_custom_logo_for_frontend_lang', 20 );

if ( ! function_exists( 'developer_starter_filter_nav_menu_link_attributes_for_frontend_lang' ) ) {
    /**
     * 多语言内容模式下，前台菜单链接自动切到当前语言 URL。
     *
     * @param array    $atts      菜单链接属性。
     * @param WP_Post  $menu_item 菜单项。
     * @param stdClass $args      菜单参数。
     * @param int      $depth     深度。
     * @return array
     */
    function developer_starter_filter_nav_menu_link_attributes_for_frontend_lang( $atts, $menu_item, $args, $depth ) {
        unset( $args, $depth );

        if (
            is_admin()
            || ! function_exists( 'developer_starter_is_multilingual_content_mode' )
            || ! developer_starter_is_multilingual_content_mode()
            || empty( $atts['href'] )
        ) {
            return $atts;
        }

        $current_lang = function_exists( 'developer_starter_get_current_frontend_lang' )
            ? developer_starter_get_current_frontend_lang()
            : '';

        if ( '' === $current_lang ) {
            return $atts;
        }

        $href = (string) $atts['href'];

        if ( isset( $menu_item->type, $menu_item->object_id ) && 'post_type' === $menu_item->type && (int) $menu_item->object_id > 0 ) {
            $translated_url = function_exists( 'developer_starter_get_post_url_for_frontend_lang' )
                ? developer_starter_get_post_url_for_frontend_lang( (int) $menu_item->object_id, $current_lang )
                : '';
            if ( '' !== $translated_url ) {
                $atts['href'] = $translated_url;
            }

            return $atts;
        }

        if ( isset( $menu_item->type, $menu_item->object_id, $menu_item->object ) && 'taxonomy' === $menu_item->type && (int) $menu_item->object_id > 0 ) {
            $term_link = get_term_link( (int) $menu_item->object_id, (string) $menu_item->object );
            if ( ! is_wp_error( $term_link ) && is_string( $term_link ) && '' !== $term_link ) {
                $atts['href'] = $term_link;
            }

            return $atts;
        }

        if ( isset( $menu_item->type, $menu_item->object ) && 'post_type_archive' === $menu_item->type ) {
            $archive_link = get_post_type_archive_link( (string) $menu_item->object );
            if ( is_string( $archive_link ) && '' !== $archive_link ) {
                $atts['href'] = $archive_link;
            }

            return $atts;
        }

        if ( function_exists( 'developer_starter_translate_internal_url_for_frontend_lang' ) ) {
            $atts['href'] = developer_starter_translate_internal_url_for_frontend_lang( $href, $current_lang );
        }

        return $atts;
    }
}
add_filter( 'nav_menu_link_attributes', 'developer_starter_filter_nav_menu_link_attributes_for_frontend_lang', 30, 4 );

if ( ! function_exists( 'developer_starter_should_skip_multilingual_html_link' ) ) {
    /**
     * 判断前台 HTML 中的链接是否应跳过多语言改写。
     *
     * @param string $url 原始链接。
     * @return bool
     */
    function developer_starter_should_skip_multilingual_html_link( $url ) {
        $url = trim( (string) $url );

        if (
            '' === $url
            || ! function_exists( 'developer_starter_is_site_internal_url' )
            || ! developer_starter_is_site_internal_url( $url )
        ) {
            return true;
        }

        if (
            function_exists( 'developer_starter_is_document_relative_url' )
            && developer_starter_is_document_relative_url( $url )
        ) {
            return true;
        }

        $path  = (string) wp_parse_url( wp_make_link_relative( $url ), PHP_URL_PATH );
        $query = (string) wp_parse_url( $url, PHP_URL_QUERY );
        $path  = ltrim( $path, '/' );

        if ( '' !== $query && false !== strpos( $query, 'rest_route=' ) ) {
            return true;
        }

        foreach ( array( 'wp-admin/', 'wp-login.php', 'wp-json', 'xmlrpc.php', 'wp-content/', 'wp-includes/' ) as $prefix ) {
            if ( 0 === strpos( $path, $prefix ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_should_skip_multilingual_html_tag' ) ) {
    /**
     * 判断前台 HTML 标签是否应跳过多语言链接改写。
     *
     * @param string $tag_html 标签 HTML。
     * @return bool
     */
    function developer_starter_should_skip_multilingual_html_tag( $tag_html ) {
        $tag_html = (string) $tag_html;

        if ( '' === $tag_html ) {
            return false;
        }

        $patterns = array(
            '/\bdata-no-lang-rewrite\s*=\s*(["\'])?1\1?/i',
            '/\bdata-switch-url\s*=/i',
            '/\bdata-lang\s*=/i',
            '/\bhreflang\s*=/i',
            '/\bclass\s*=\s*(["\'])[^"\']*\btranslate-lang-item\b[^"\']*\1/i',
            '/\bclass\s*=\s*(["\'])[^"\']*\bxb-aifanyi-language-switcher\b[^"\']*\1/i',
            '/\bclass\s*=\s*(["\'])[^"\']*\bxb-aifanyi-language-switcher__link\b[^"\']*\1/i',
        );

        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $tag_html ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_rewrite_multilingual_html_link_callback' ) ) {
    /**
     * 改写 HTML 中的站内链接属性。
     *
     * @param array<int, string> $matches 正则匹配结果。
     * @return string
     */
    function developer_starter_rewrite_multilingual_html_link_callback( $matches ) {
        $tag_prefix = isset( $matches[1] ) ? (string) $matches[1] : '';
        $attr_name  = isset( $matches[2] ) ? (string) $matches[2] : '';
        $quote      = isset( $matches[4] ) ? (string) $matches[4] : '"';
        $raw_url    = isset( $matches[5] ) ? html_entity_decode( (string) $matches[5], ENT_QUOTES, get_bloginfo( 'charset' ) ) : '';
        $tag_suffix = isset( $matches[6] ) ? (string) $matches[6] : '>';
        $full_tag   = $tag_prefix . ' ' . $attr_name . '=' . $quote . $raw_url . $quote . $tag_suffix;
        $translated = $raw_url;

        if (
            ! developer_starter_should_skip_multilingual_html_tag( $full_tag )
            && ! developer_starter_should_skip_multilingual_html_link( $raw_url )
            && function_exists( 'developer_starter_translate_internal_url_for_frontend_lang' )
        ) {
            $translated = developer_starter_translate_internal_url_for_frontend_lang( $raw_url );
        }

        return $tag_prefix . ' ' . $attr_name . '=' . $quote . esc_url( $translated ) . $quote . $tag_suffix;
    }
}

if ( ! function_exists( 'developer_starter_multilingual_output_buffer_callback' ) ) {
    /**
     * 多语言内容模式下，统一改写页面中的站内跳转链接。
     *
     * @param string $html 页面 HTML。
     * @return string
     */
    function developer_starter_multilingual_output_buffer_callback( $html ) {
        if (
            empty( $html )
            || ! function_exists( 'developer_starter_is_multilingual_content_mode' )
            || ! developer_starter_is_multilingual_content_mode()
            || false === stripos( $html, '<html' )
        ) {
            return $html;
        }

        if ( false === stripos( $html, '<a' ) && false === stripos( $html, '<form' ) ) {
            return $html;
        }

        $pattern = '/(<(?:a|form)\b[^>]*?)\s(href|action)(=)(["\'])(.*?)\4([^>]*>)/isu';
        $html    = (string) preg_replace_callback( $pattern, 'developer_starter_rewrite_multilingual_html_link_callback', $html );

        return $html;
    }
}

if ( ! function_exists( 'developer_starter_multilingual_output_buffer_start' ) ) {
    /**
     * 启动前台多语言链接改写输出缓冲。
     *
     * @return void
     */
    function developer_starter_multilingual_output_buffer_start() {
        if (
            is_admin()
            || defined( 'DOING_AJAX' )
            || defined( 'XMLRPC_REQUEST' )
            || defined( 'REST_REQUEST' )
            || is_feed()
            || ( function_exists( 'developer_starter_is_sitemap_request' ) && developer_starter_is_sitemap_request() )
            || ! function_exists( 'developer_starter_is_multilingual_content_mode' )
            || ! developer_starter_is_multilingual_content_mode()
        ) {
            return;
        }

        ob_start( 'developer_starter_multilingual_output_buffer_callback' );
    }
}
add_action( 'template_redirect', 'developer_starter_multilingual_output_buffer_start', 2 );
