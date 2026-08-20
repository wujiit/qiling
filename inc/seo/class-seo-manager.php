<?php
/**
 * SEO Manager Class
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\SEO;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SEO_Manager {

    public function __construct() {
        add_action( 'wp_head', array( $this, 'output_meta_tags' ), 1 );
        add_action( 'wp_head', array( $this, 'output_schema' ), 5 );
        add_action( 'wp_head', array( $this, 'output_hreflang' ), 10 );

        if ( ! $this->has_seo_plugin() ) {
            add_filter( 'document_title_parts', array( $this, 'filter_title' ) );
            add_filter( 'pre_get_document_title', array( $this, 'custom_document_title' ), 10 );
        }
    }

    /**
     * 获取首页或文章列表页对应的页面 ID。
     *
     * @return int
     */
    private function get_home_context_post_id() {
        if ( is_front_page() ) {
            $front_page_id = (int) get_option( 'page_on_front' );
            if ( $front_page_id > 0 ) {
                return $front_page_id;
            }
        }

        if ( is_home() ) {
            $posts_page_id = (int) get_option( 'page_for_posts' );
            if ( $posts_page_id > 0 ) {
                return $posts_page_id;
            }
        }

        return 0;
    }

    /**
     * 获取首页优先使用的自定义标题。
     *
     * 优先级：
     * 1. 首页/文章列表页对应页面的 SEO 标题
     * 2. 主题 SEO 设置里的“默认标题”
     *
     * @return string
     */
    private function get_home_preferred_title() {
        $home_post_id = $this->get_home_context_post_id();

        if ( $home_post_id > 0 ) {
            $seo_title = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( $home_post_id, '_developer_starter_seo_title', '' )
                : get_post_meta( $home_post_id, '_developer_starter_seo_title', true );

            if ( ! empty( $seo_title ) ) {
                return (string) $seo_title;
            }
        }

        $custom_title = developer_starter_get_option( 'default_title', '' );
        if ( ! empty( $custom_title ) ) {
            return (string) $custom_title;
        }

        return '';
    }

    /**
     * 获取首页默认标题 parts。
     *
     * 当主题未设置首页默认标题时，回退到 WordPress 常规里的站点标题和副标题。
     *
     * @return array{title: string, tagline: string}
     */
    private function get_home_default_title_parts() {
        return array(
            'title'   => trim( (string) get_bloginfo( 'name' ) ),
            'tagline' => trim( (string) get_bloginfo( 'description' ) ),
        );
    }

    /**
     * 获取首页默认标题字符串。
     *
     * @return string
     */
    private function get_home_default_title_string() {
        $parts = $this->get_home_default_title_parts();

        if ( '' !== $parts['title'] && '' !== $parts['tagline'] ) {
            return $parts['title'] . ' - ' . $parts['tagline'];
        }

        return '' !== $parts['title'] ? $parts['title'] : $parts['tagline'];
    }

    /**
     * 完全自定义首页标题
     */
    public function custom_document_title( $title ) {
        if ( $this->has_seo_plugin() ) {
            return $title;
        }

        // 首页使用自定义标题
        if ( is_front_page() || is_home() ) {
            $preferred_title = $this->get_home_preferred_title();
            if ( '' !== $preferred_title ) {
                return $preferred_title;
            }
        }
        return $title;
    }

    public function output_meta_tags() {
        // Skip if other SEO plugin is active
        if ( $this->has_seo_plugin() ) {
            return;
        }

        $description            = $this->get_description();
        $keywords               = $this->get_keywords();
        $canonical              = $this->get_canonical_url();
        $should_output_canonical = $this->should_output_canonical_tag();
        $robots                 = $this->get_robots_directives();
        $og_title               = $this->get_og_title();
        $og_desc                = $this->get_og_description();
        $og_image               = $this->get_og_image();
        ?>
        <?php if ( ! empty( $description ) ) : ?>
            <meta name="description" content="<?php echo esc_attr( $description ); ?>" />
        <?php endif; ?>
        <?php if ( ! empty( $keywords ) ) : ?>
            <meta name="keywords" content="<?php echo esc_attr( $keywords ); ?>" />
        <?php endif; ?>
        <?php if ( ! empty( $robots ) ) : ?>
            <meta name="robots" content="<?php echo esc_attr( $robots ); ?>" />
        <?php endif; ?>
        <?php if ( $should_output_canonical && ! empty( $canonical ) ) : ?>
            <link rel="canonical" href="<?php echo esc_url( $canonical ); ?>" />
        <?php endif; ?>
        
        <!-- Open Graph -->
        <meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>" />
        <meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>" />
        <meta property="og:type" content="<?php echo is_singular() ? 'article' : 'website'; ?>" />
        <meta property="og:url" content="<?php echo esc_url( $should_output_canonical && $canonical ? $canonical : $this->get_current_url() ); ?>" />
        <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
        <?php
        $og_locale_data = function_exists( 'xb_aifanyi_get_frontend_og_locale_data' )
            ? xb_aifanyi_get_frontend_og_locale_data()
            : array(
                'current'    => '',
                'alternates' => array(),
            );
        $og_current_locale = ( is_array( $og_locale_data ) && ! empty( $og_locale_data['current'] ) ) ? (string) $og_locale_data['current'] : '';
        $og_alternates     = ( is_array( $og_locale_data ) && ! empty( $og_locale_data['alternates'] ) && is_array( $og_locale_data['alternates'] ) )
            ? $og_locale_data['alternates']
            : array();
        ?>
        <?php if ( ! empty( $og_current_locale ) ) : ?>
            <meta property="og:locale" content="<?php echo esc_attr( $og_current_locale ); ?>" />
        <?php endif; ?>
        <?php foreach ( $og_alternates as $og_alternate_locale ) : ?>
            <meta property="og:locale:alternate" content="<?php echo esc_attr( $og_alternate_locale ); ?>" />
        <?php endforeach; ?>
        <?php if ( ! empty( $og_image ) ) : ?>
            <meta property="og:image" content="<?php echo esc_url( $og_image ); ?>" />
        <?php endif; ?>
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>" />
        <meta name="twitter:description" content="<?php echo esc_attr( $og_desc ); ?>" />
        <?php if ( ! empty( $og_image ) ) : ?>
            <meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>" />
        <?php endif; ?>
        <?php
    }

    public function output_schema() {
        if ( $this->has_seo_plugin() ) {
            return;
        }

        if ( ! class_exists( '\Developer_Starter\SEO\Industry_Schema_Engine' ) ) {
            return;
        }

        $json_ld = Industry_Schema_Engine::get_instance()->get_json_ld();
        if ( '' === $json_ld ) {
            return;
        }

        echo '<script type="application/ld+json">' . $json_ld . '</script>' . "\n";
    }

    public function output_hreflang() {
        if ( function_exists( 'xb_aifanyi_get_frontend_hreflang_map' ) ) {
            $hreflang_map = xb_aifanyi_get_frontend_hreflang_map();
            if ( ! empty( $hreflang_map ) && is_array( $hreflang_map ) ) {
                foreach ( $hreflang_map as $lang_data ) {
                    if ( ! is_array( $lang_data ) || empty( $lang_data['hreflang'] ) || empty( $lang_data['url'] ) ) {
                        continue;
                    }

                    echo '<link rel="alternate" hreflang="' . esc_attr( $lang_data['hreflang'] ) . '" href="' . esc_url( $lang_data['url'] ) . '" />' . "\n";
                }

                if ( function_exists( 'developer_starter_get_multilingual_default_lang' ) ) {
                    $default_lang = developer_starter_get_multilingual_default_lang();
                    if ( isset( $hreflang_map[ $default_lang ]['url'] ) && ! empty( $hreflang_map[ $default_lang ]['url'] ) ) {
                        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $hreflang_map[ $default_lang ]['url'] ) . '" />' . "\n";
                    }
                }

                return;
            }
        }

        // Multi-language hreflang support
        if ( function_exists( 'pll_the_languages' ) ) {
            $languages = pll_the_languages( array( 'raw' => 1 ) );
            foreach ( $languages as $lang ) {
                echo '<link rel="alternate" hreflang="' . esc_attr( $lang['slug'] ) . '" href="' . esc_url( $lang['url'] ) . '" />' . "\n";
            }
        } elseif ( function_exists( 'icl_get_languages' ) ) {
            $languages = icl_get_languages( 'skip_missing=0' );
            foreach ( $languages as $lang ) {
                echo '<link rel="alternate" hreflang="' . esc_attr( $lang['language_code'] ) . '" href="' . esc_url( $lang['url'] ) . '" />' . "\n";
            }
        }
    }

    public function filter_title( $title ) {
        if ( $this->has_seo_plugin() ) {
            return $title;
        }

        $use_tagline_on_non_home = developer_starter_get_option( 'non_home_title_use_tagline', '' ) === '1';

        // 首页标题
        if ( is_front_page() || is_home() ) {
            $resolved_title = $this->get_home_preferred_title();

            if ( '' !== $resolved_title ) {
                $title['title'] = $resolved_title;
                // 移除 tagline 避免重复
                unset( $title['tagline'] );
            } else {
                $default_parts = $this->get_home_default_title_parts();

                if ( '' !== $default_parts['title'] ) {
                    $title['title'] = $default_parts['title'];
                }

                if ( '' !== $default_parts['tagline'] ) {
                    $title['tagline'] = $default_parts['tagline'];
                }
            }
        }
        // 单页/文章标题
        elseif ( is_singular() ) {
            $seo_title = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( get_the_ID(), '_developer_starter_seo_title', '' )
                : get_post_meta( get_the_ID(), '_developer_starter_seo_title', true );
            if ( ! empty( $seo_title ) ) {
                return array( 'title' => $seo_title );
            }
        }
        elseif ( is_category() ) {
            $cat = get_queried_object();
            if ( $cat && isset( $cat->term_id ) ) {
                $seo_title = get_term_meta( $cat->term_id, 'ds_category_seo_title', true );
                if ( ! empty( $seo_title ) ) {
                    return array( 'title' => $seo_title );
                }
                if ( empty( $seo_title ) ) {
                    $title['title'] = $cat->name;
                }
            }
        }
        elseif ( is_tag() ) {
            $tag = get_queried_object();
            if ( $tag && isset( $tag->term_id ) ) {
                $seo_title = get_term_meta( $tag->term_id, 'ds_category_seo_title', true );
                if ( ! empty( $seo_title ) ) {
                    return array( 'title' => $seo_title );
                }
                if ( empty( $seo_title ) ) {
                    $title['title'] = $tag->name;
                }
            }
        }

        // 非首页标题的副标题使用站点副标题（Tagline）
        if ( ! ( is_front_page() || is_home() ) && $use_tagline_on_non_home ) {
            $site_tagline = trim( (string) get_bloginfo( 'description' ) );
            if ( $site_tagline !== '' ) {
                $title['site'] = $site_tagline;
            }
        }

        return $title;
    }

    private function get_title() {
        // 首页
        if ( is_front_page() || is_home() ) {
            $preferred_title = $this->get_home_preferred_title();

            return '' !== $preferred_title ? $preferred_title : $this->get_home_default_title_string();
        }
        // 单页/文章
        if ( is_singular() ) {
            $post_id   = get_the_ID();
            $seo_title = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( $post_id, '_developer_starter_seo_title', '' )
                : get_post_meta( $post_id, '_developer_starter_seo_title', true );
            $title     = function_exists( 'developer_starter_get_translated_post_title' )
                ? developer_starter_get_translated_post_title( $post_id )
                : get_the_title( $post_id );
            return ! empty( $seo_title ) ? $seo_title : $title;
        }
        if ( is_category() ) {
            $cat = get_queried_object();
            if ( $cat && isset( $cat->term_id ) ) {
                $seo_title = get_term_meta( $cat->term_id, 'ds_category_seo_title', true );
                if ( ! empty( $seo_title ) ) {
                    return $seo_title;
                }
                $site_name = get_bloginfo( 'name' );
                return $cat->name . ' - ' . $site_name;
            }
        }
        if ( is_tag() ) {
            $tag = get_queried_object();
            if ( $tag && isset( $tag->term_id ) ) {
                $seo_title = get_term_meta( $tag->term_id, 'ds_category_seo_title', true );
                if ( ! empty( $seo_title ) ) {
                    return $seo_title;
                }
                return $tag->name . ' - ' . get_bloginfo( 'name' );
            }
        }
        return get_bloginfo( 'name' );
    }

    private function get_description() {
        // 首页
        if ( is_front_page() || is_home() ) {
            $home_post_id = $this->get_home_context_post_id();
            if ( $home_post_id > 0 ) {
                $seo_desc = function_exists( 'developer_starter_get_translated_post_meta_value' )
                    ? developer_starter_get_translated_post_meta_value( $home_post_id, '_developer_starter_seo_description', '' )
                    : get_post_meta( $home_post_id, '_developer_starter_seo_description', true );
                if ( ! empty( $seo_desc ) ) {
                    return $seo_desc;
                }

                $page_excerpt = function_exists( 'developer_starter_get_translated_post_excerpt' )
                    ? developer_starter_get_translated_post_excerpt( $home_post_id )
                    : get_the_excerpt( $home_post_id );
                if ( ! empty( $page_excerpt ) ) {
                    return wp_trim_words( $page_excerpt, 30 );
                }
            }

            $custom_desc = developer_starter_get_option( 'default_description', '' );
            return ! empty( $custom_desc ) ? $custom_desc : get_bloginfo( 'description' );
        }
        // 单页/文章
        if ( is_singular() ) {
            $post_id  = get_the_ID();
            $seo_desc = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( $post_id, '_developer_starter_seo_description', '' )
                : get_post_meta( $post_id, '_developer_starter_seo_description', true );
            if ( ! empty( $seo_desc ) ) return $seo_desc;
            $excerpt = function_exists( 'developer_starter_get_translated_post_excerpt' )
                ? developer_starter_get_translated_post_excerpt( $post_id )
                : get_the_excerpt( $post_id );
            return wp_trim_words( $excerpt, 30 );
        }
        if ( is_category() ) {
            $cat = get_queried_object();
            if ( $cat && isset( $cat->term_id ) ) {
                $seo_desc = get_term_meta( $cat->term_id, 'ds_category_seo_description', true );
                if ( ! empty( $seo_desc ) ) {
                    return $seo_desc;
                }
                $term_desc = term_description( $cat->term_id );
                return trim( wp_strip_all_tags( $term_desc ) );
            }
        }
        if ( is_tag() ) {
            $tag = get_queried_object();
            if ( $tag && isset( $tag->term_id ) ) {
                $seo_desc = get_term_meta( $tag->term_id, 'ds_category_seo_description', true );
                if ( ! empty( $seo_desc ) ) {
                    return $seo_desc;
                }
                $term_desc = term_description( $tag->term_id, 'post_tag' );
                if ( ! empty( $term_desc ) ) {
                    return trim( wp_strip_all_tags( $term_desc ) );
                }
                return sprintf( __( '标签「%s」相关文章聚合页。', 'developer-starter' ), $tag->name );
            }
        }
        // 其他页面（分类、标签、归档等）
        return developer_starter_get_option( 'default_description', get_bloginfo( 'description' ) );
    }

    private function get_keywords() {
        // 首页
        if ( is_front_page() || is_home() ) {
            $home_post_id = $this->get_home_context_post_id();
            if ( $home_post_id > 0 ) {
                $seo_keywords = function_exists( 'developer_starter_get_translated_post_meta_value' )
                    ? developer_starter_get_translated_post_meta_value( $home_post_id, '_developer_starter_seo_keywords', '' )
                    : get_post_meta( $home_post_id, '_developer_starter_seo_keywords', true );
                if ( ! empty( $seo_keywords ) ) {
                    return $seo_keywords;
                }
            }

            return developer_starter_get_option( 'default_keywords', '' );
        }
        // 单页/文章
        if ( is_singular() ) {
            return function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( get_the_ID(), '_developer_starter_seo_keywords', '' )
                : get_post_meta( get_the_ID(), '_developer_starter_seo_keywords', true );
        }
        if ( is_category() ) {
            $cat = get_queried_object();
            if ( $cat && isset( $cat->term_id ) ) {
                $seo_keywords = get_term_meta( $cat->term_id, 'ds_category_seo_keywords', true );
                if ( ! empty( $seo_keywords ) ) {
                    return $seo_keywords;
                }
                return $cat->name;
            }
        }
        if ( is_tag() ) {
            $tag = get_queried_object();
            if ( $tag && isset( $tag->term_id ) ) {
                $seo_keywords = get_term_meta( $tag->term_id, 'ds_category_seo_keywords', true );
                if ( ! empty( $seo_keywords ) ) {
                    return $seo_keywords;
                }
                return $tag->name;
            }
        }
        // 其他页面
        return developer_starter_get_option( 'default_keywords', '' );
    }

    /**
     * 获取 canonical URL
     */
    private function get_canonical_url() {
        if ( function_exists( 'xb_aifanyi_get_frontend_canonical_url' ) ) {
            $plugin_canonical = xb_aifanyi_get_frontend_canonical_url();
            if ( ! empty( $plugin_canonical ) ) {
                return esc_url_raw( $plugin_canonical );
            }
        }

        if ( is_singular() ) {
            $canonical = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( get_the_ID(), '_developer_starter_seo_canonical', '' )
                : get_post_meta( get_the_ID(), '_developer_starter_seo_canonical', true );
            if ( ! empty( $canonical ) ) {
                return esc_url_raw( $canonical );
            }
        }

        return $this->get_current_url();
    }

    /**
     * 获取 robots 指令
     */
    private function get_robots_directives() {
        $directives = array( 'index', 'follow' );
        if ( is_singular() ) {
            $noindex = get_post_meta( get_the_ID(), '_developer_starter_seo_noindex', true );
            $nofollow = get_post_meta( get_the_ID(), '_developer_starter_seo_nofollow', true );
            if ( $noindex === '1' ) {
                $directives[0] = 'noindex';
            }
            if ( $nofollow === '1' ) {
                $directives[1] = 'nofollow';
            }
        }
        return implode( ',', $directives );
    }

    /**
     * 获取 OG 标题
     */
    private function get_og_title() {
        if ( is_singular() ) {
            $og_title = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( get_the_ID(), '_developer_starter_og_title', '' )
                : get_post_meta( get_the_ID(), '_developer_starter_og_title', true );
            if ( ! empty( $og_title ) ) {
                return $og_title;
            }
        }
        return $this->get_title();
    }

    /**
     * 获取 OG 描述
     */
    private function get_og_description() {
        if ( is_singular() ) {
            $og_desc = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( get_the_ID(), '_developer_starter_og_description', '' )
                : get_post_meta( get_the_ID(), '_developer_starter_og_description', true );
            if ( ! empty( $og_desc ) ) {
                return $og_desc;
            }
        }
        return $this->get_description();
    }

    /**
     * 获取 OG 图片
     */
    private function get_og_image() {
        if ( is_singular() ) {
            $og_image = get_post_meta( get_the_ID(), '_developer_starter_og_image', true );
            if ( ! empty( $og_image ) ) {
                return esc_url_raw( $og_image );
            }
        }
        if ( is_singular() ) {
            if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                $featured = developer_starter_get_featured_image_url( get_the_ID(), 'large' );
                if ( ! empty( $featured ) ) {
                    return $featured;
                }
            }
            if ( has_post_thumbnail() ) {
                return get_the_post_thumbnail_url( null, 'large' );
            }
        }
        return '';
    }

    private function get_current_url() {
        if ( function_exists( 'developer_starter_get_request_path_parts' ) ) {
            $request_parts = developer_starter_get_request_path_parts();
            $path          = isset( $request_parts['path'] ) ? (string) $request_parts['path'] : '';
            $query         = isset( $request_parts['query'] ) ? (string) $request_parts['query'] : '';
            $url           = function_exists( 'developer_starter_build_raw_home_url' )
                ? developer_starter_build_raw_home_url( '' === $path ? '/' : '/' . ltrim( $path, '/' ) )
                : home_url( '' === $path ? '/' : '/' . ltrim( $path, '/' ) );

            if ( '' !== $query ) {
                $url .= '?' . $query;
            }

            return $url;
        }

        global $wp;
        $request = isset( $wp->request ) ? (string) $wp->request : '';

        return home_url( '' === $request ? '/' : '/' . ltrim( $request, '/' ) );
    }

    /**
     * 获取结构化数据使用的站点 Logo URL。
     *
     * @return string
     */
    private function get_schema_logo_url() {
        if ( ! has_custom_logo() ) {
            return '';
        }

        $logo_url = wp_get_attachment_url( get_theme_mod( 'custom_logo' ) );

        return is_string( $logo_url ) && '' !== $logo_url ? esc_url_raw( $logo_url ) : '';
    }

    private function has_seo_plugin() {
        return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );
    }

    private function should_output_canonical_tag() {
        return true;
    }
}
