<?php
/**
 * Lightweight SEO health checks.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\SEO;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Provides temporary, low-cost SEO diagnostics.
 */
class SEO_Health_Check {
    const SNAPSHOT_TRANSIENT = 'developer_starter_seo_health_snapshot';
    const SNAPSHOT_TTL       = 21600; // 6 hours.
    const DEFAULT_LIMIT      = 200;
    const MAX_LIMIT          = 300;
    const MAX_TERM_LIMIT     = 120;
    const MAX_ISSUES_BUCKET  = 100;

    /**
     * Get the latest cached scan snapshot.
     *
     * @return array<string,mixed>
     */
    public static function get_snapshot() {
        $snapshot = get_transient( self::SNAPSHOT_TRANSIENT );
        return is_array( $snapshot ) ? $snapshot : array();
    }

    /**
     * Clear cached SEO health data.
     *
     * @return void
     */
    public static function clear_snapshot() {
        delete_transient( self::SNAPSHOT_TRANSIENT );
    }

    /**
     * Run a bounded manual scan and cache only the latest compact snapshot.
     *
     * @param int $limit Max posts/pages to scan in this run.
     * @return array<string,mixed>
     */
    public static function run_scan( $limit = self::DEFAULT_LIMIT ) {
        $limit = max( 20, min( self::MAX_LIMIT, absint( $limit ) ) );

        $records = array();
        $summary = array(
            'posts'      => 0,
            'pages'      => 0,
            'categories' => 0,
            'limit'      => $limit,
        );

        $post_type_limits = self::split_post_type_limits( $limit );
        foreach ( array( 'post', 'page' ) as $post_type ) {
            $ids = self::query_public_post_ids( $post_type, $post_type_limits[ $post_type ] );
            $summary[ 'post' === $post_type ? 'posts' : 'pages' ] = count( $ids );

            foreach ( $ids as $post_id ) {
                $records[] = self::build_post_record( $post_id, $post_type );
            }
        }

        $terms = self::query_category_terms( min( self::MAX_TERM_LIMIT, max( 40, (int) floor( $limit / 2 ) ) ) );
        $summary['categories'] = count( $terms );
        foreach ( $terms as $term ) {
            $records[] = self::build_term_record( $term );
        }

        $buckets = array(
            'critical' => array(),
            'warning'  => array(),
            'info'     => array(),
        );
        $counts = array(
            'critical' => 0,
            'warning'  => 0,
            'info'     => 0,
        );
        $seen_titles       = array();
        $seen_descriptions = array();

        foreach ( $records as $record ) {
            self::collect_duplicate_key( $seen_titles, $record, 'seo_title' );
            self::collect_duplicate_key( $seen_descriptions, $record, 'seo_description' );
            self::add_record_rule_issues( $record, $buckets, $counts );
        }

        self::add_duplicate_issues( $seen_titles, 'duplicate_title', __( 'SEO 标题重复', 'developer-starter' ), $buckets, $counts );
        self::add_duplicate_issues( $seen_descriptions, 'duplicate_description', __( 'SEO 描述重复', 'developer-starter' ), $buckets, $counts );

        $snapshot = array(
            'version'      => 1,
            'generated_at' => current_time( 'mysql' ),
            'expires_at'   => gmdate( 'Y-m-d H:i:s', time() + self::SNAPSHOT_TTL ),
            'summary'      => array(
                'scanned'          => $summary,
                'issue_counts'     => $counts,
                'total_issues'     => array_sum( $counts ),
                'stored_issues'    => count( $buckets['critical'] ) + count( $buckets['warning'] ) + count( $buckets['info'] ),
                'storage'          => 'transient',
                'retention'        => __( '仅保存最近一次轻量快照，可自动过期或手动清除。', 'developer-starter' ),
            ),
            'issues'       => $buckets,
        );

        set_transient( self::SNAPSHOT_TRANSIENT, $snapshot, self::SNAPSHOT_TTL );

        return $snapshot;
    }

    /**
     * Build content-level score for a post/page edit screen.
     *
     * @param int $post_id Post ID.
     * @return array<string,mixed>
     */
    public static function score_post( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof \WP_Post ) {
            return array();
        }

        $seo_title       = trim( (string) get_post_meta( $post_id, '_developer_starter_seo_title', true ) );
        $seo_description = trim( (string) get_post_meta( $post_id, '_developer_starter_seo_description', true ) );
        $seo_keywords    = self::split_keywords( (string) get_post_meta( $post_id, '_developer_starter_seo_keywords', true ) );
        $canonical       = trim( (string) get_post_meta( $post_id, '_developer_starter_seo_canonical', true ) );
        $og_image        = trim( (string) get_post_meta( $post_id, '_developer_starter_og_image', true ) );

        $effective_title = '' !== $seo_title ? $seo_title : (string) get_the_title( $post_id );
        $content_text    = self::get_post_plain_text( $post_id );
        $content_length  = self::text_length( $content_text );
        $title_length    = self::text_length( $effective_title );
        $desc_length     = self::text_length( $seo_description );
        $has_image       = '' !== $og_image || has_post_thumbnail( $post_id ) || '' !== (string) get_post_meta( $post_id, '_developer_starter_featured_image_url', true );

        $html          = (string) get_post_field( 'post_content', $post_id );
        $image_stats   = self::count_html_images_without_alt( $html );
        $internal_urls = self::count_internal_links( $html );
        $score         = 100;
        $checks        = array();

        self::push_score_check( $checks, $score, '' !== $seo_title, __( 'SEO 标题', 'developer-starter' ), __( '已填写 SEO 标题。', 'developer-starter' ), __( '建议填写独立 SEO 标题。', 'developer-starter' ), 12 );
        self::push_score_check( $checks, $score, $title_length >= 10 && $title_length <= 70, __( '标题长度', 'developer-starter' ), __( '标题长度处于常用范围。', 'developer-starter' ), __( '标题建议控制在 10-70 个字符。', 'developer-starter' ), 10 );
        self::push_score_check( $checks, $score, '' !== $seo_description, __( 'SEO 描述', 'developer-starter' ), __( '已填写 SEO 描述。', 'developer-starter' ), __( '建议填写页面 SEO 描述。', 'developer-starter' ), 16 );
        self::push_score_check( $checks, $score, $desc_length >= 50 && $desc_length <= 160, __( '描述长度', 'developer-starter' ), __( '描述长度处于常用范围。', 'developer-starter' ), __( '描述建议控制在 50-160 个字符。', 'developer-starter' ), 10 );
        self::push_score_check( $checks, $score, $content_length >= ( 'post' === $post->post_type ? 300 : 120 ), __( '内容长度', 'developer-starter' ), __( '正文/模块文本量基本够用。', 'developer-starter' ), __( '当前内容偏短，建议补充更完整的信息。', 'developer-starter' ), 10 );

        if ( ! empty( $seo_keywords ) ) {
            $primary_keyword = $seo_keywords[0];
            $lower_title     = self::normalize_text( $effective_title );
            $lower_content   = self::normalize_text( $content_text );
            $lower_keyword   = self::normalize_text( $primary_keyword );
            self::push_score_check( $checks, $score, false !== strpos( $lower_title, $lower_keyword ), __( '关键词标题覆盖', 'developer-starter' ), __( '主关键词已出现在标题中。', 'developer-starter' ), __( '建议让主关键词自然出现在标题中。', 'developer-starter' ), 8 );
            self::push_score_check( $checks, $score, false !== strpos( $lower_content, $lower_keyword ), __( '关键词内容覆盖', 'developer-starter' ), __( '主关键词已出现在正文或模块文本中。', 'developer-starter' ), __( '建议在正文或模块文本中自然覆盖主关键词。', 'developer-starter' ), 8 );
        } else {
            self::push_score_check( $checks, $score, false, __( 'SEO 关键词', 'developer-starter' ), '', __( '建议填写 1-3 个核心关键词，便于基础检查。', 'developer-starter' ), 8 );
        }

        self::push_score_check( $checks, $score, $has_image, __( '分享/特色图', 'developer-starter' ), __( '已检测到特色图或 OG 图片。', 'developer-starter' ), __( '建议设置特色图或 OG 图片。', 'developer-starter' ), 8 );
        if ( $image_stats['total'] > 0 ) {
            self::push_score_check(
                $checks,
                $score,
                0 === $image_stats['missing_alt'],
                __( '图片 Alt', 'developer-starter' ),
                __( '正文图片均带有 alt 文本。', 'developer-starter' ),
                sprintf( __( '检测到 %d 张正文图片缺少 alt 文本。', 'developer-starter' ), (int) $image_stats['missing_alt'] ),
                6
            );
        }
        self::push_score_check( $checks, $score, $internal_urls > 0, __( '内部链接', 'developer-starter' ), __( '正文中包含内部链接。', 'developer-starter' ), __( '可以加入相关页面的内部链接。', 'developer-starter' ), 5 );
        if ( '' !== $canonical ) {
            self::push_score_check( $checks, $score, self::is_valid_url( $canonical ), __( 'Canonical', 'developer-starter' ), __( 'Canonical URL 格式有效。', 'developer-starter' ), __( 'Canonical URL 格式异常，请检查。', 'developer-starter' ), 10 );
        }

        $score = max( 0, min( 100, $score ) );

        return array(
            'score'          => $score,
            'grade'          => self::score_grade( $score ),
            'checks'         => $checks,
            'content_length' => $content_length,
            'title_length'   => $title_length,
            'desc_length'    => $desc_length,
        );
    }

    /**
     * Get a small sitemap diagnostics snapshot.
     *
     * @return array<string,mixed>
     */
    public static function get_sitemap_diagnostics() {
        $enabled = function_exists( 'wp_sitemaps_get_server' ) ? (bool) apply_filters( 'wp_sitemaps_enabled', true ) : false;
        $url     = home_url( '/wp-sitemap.xml' );
        $providers = array();

        if ( $enabled && function_exists( 'wp_sitemaps_get_server' ) ) {
            $server = wp_sitemaps_get_server();
            if ( is_object( $server ) && isset( $server->registry ) && is_object( $server->registry ) && method_exists( $server->registry, 'get_providers' ) ) {
                $raw_providers = $server->registry->get_providers();
                if ( is_array( $raw_providers ) ) {
                    $providers = array_keys( $raw_providers );
                }
            }
        }

        $noindex_count = 0;
        foreach ( array( 'post', 'page' ) as $post_type ) {
            $ids = get_posts(
                array(
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => 50,
                    'fields'         => 'ids',
                    'no_found_rows'  => true,
                    'meta_key'       => '_developer_starter_seo_noindex',
                    'meta_value'     => '1',
                )
            );
            $noindex_count += is_array( $ids ) ? count( $ids ) : 0;
        }

        return array(
            'enabled'                    => $enabled,
            'url'                        => $url,
            'providers'                  => $providers,
            'provider_count'             => count( $providers ),
            'noindex_sample_count'       => $noindex_count,
            'multilingual_provider'      => function_exists( 'xb_aifanyi_get_theme_provider_capabilities' ),
            'multilingual_sitemap_state' => self::get_multilingual_sitemap_state(),
        );
    }

    /**
     * Build a robots.txt preview without writing a file.
     *
     * @return array<string,mixed>
     */
    public static function get_robots_preview() {
        $physical_path = defined( 'ABSPATH' ) ? trailingslashit( ABSPATH ) . 'robots.txt' : '';
        $source        = 'virtual';
        $content       = '';

        if ( '' !== $physical_path && file_exists( $physical_path ) && is_readable( $physical_path ) ) {
            $source  = 'file';
            $content = (string) file_get_contents( $physical_path, false, null, 0, 8192 );
        } else {
            $public = (string) get_option( 'blog_public' );
            if ( '0' === $public ) {
                $content = "User-agent: *\nDisallow: /\n";
            } else {
                $content = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n";
            }
            $content = (string) apply_filters( 'robots_txt', $content, $public );
        }

        $sitemap_url     = home_url( '/wp-sitemap.xml' );
        $has_sitemap     = false !== stripos( $content, 'Sitemap:' );
        $blocks_all_site = (bool) preg_match( '/Disallow:\s*\/\s*(?:\r?\n|$)/i', $content );

        return array(
            'source'          => $source,
            'content'         => $content,
            'sitemap_url'     => $sitemap_url,
            'has_sitemap'     => $has_sitemap,
            'blocks_all_site' => $blocks_all_site,
        );
    }

    /**
     * Split scan budget between posts and pages.
     *
     * @param int $limit Total limit.
     * @return array<string,int>
     */
    private static function split_post_type_limits( $limit ) {
        $half = (int) floor( $limit / 2 );
        return array(
            'post' => max( 20, $half ),
            'page' => max( 20, $limit - $half ),
        );
    }

    /**
     * Query public post IDs with minimal data loading.
     *
     * @param string $post_type Post type.
     * @param int    $limit Limit.
     * @return int[]
     */
    private static function query_public_post_ids( $post_type, $limit ) {
        $ids = get_posts(
            array(
                'post_type'              => sanitize_key( $post_type ),
                'post_status'            => 'publish',
                'posts_per_page'         => max( 1, absint( $limit ) ),
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'fields'                 => 'ids',
                'no_found_rows'          => true,
                'update_post_meta_cache' => true,
                'update_post_term_cache' => false,
            )
        );

        return is_array( $ids ) ? array_map( 'absint', $ids ) : array();
    }

    /**
     * Query category terms for diagnostics.
     *
     * @param int $limit Limit.
     * @return \WP_Term[]
     */
    private static function query_category_terms( $limit ) {
        $terms = get_terms(
            array(
                'taxonomy'   => 'category',
                'hide_empty' => false,
                'number'     => max( 1, absint( $limit ) ),
                'orderby'    => 'count',
                'order'      => 'DESC',
            )
        );

        return is_array( $terms ) ? $terms : array();
    }

    /**
     * Build a compact post record.
     *
     * @param int    $post_id Post ID.
     * @param string $post_type Post type.
     * @return array<string,mixed>
     */
    private static function build_post_record( $post_id, $post_type ) {
        $title = get_the_title( $post_id );
        return array(
            'object_type'     => $post_type,
            'object_label'    => 'page' === $post_type ? __( '页面', 'developer-starter' ) : __( '文章', 'developer-starter' ),
            'object_id'       => absint( $post_id ),
            'title'           => '' !== (string) $title ? (string) $title : sprintf( __( '未命名 #%d', 'developer-starter' ), absint( $post_id ) ),
            'edit_url'        => get_edit_post_link( $post_id, '' ),
            'seo_title'       => trim( (string) get_post_meta( $post_id, '_developer_starter_seo_title', true ) ),
            'seo_description' => trim( (string) get_post_meta( $post_id, '_developer_starter_seo_description', true ) ),
            'seo_keywords'    => trim( (string) get_post_meta( $post_id, '_developer_starter_seo_keywords', true ) ),
            'canonical'       => trim( (string) get_post_meta( $post_id, '_developer_starter_seo_canonical', true ) ),
            'og_image'        => trim( (string) get_post_meta( $post_id, '_developer_starter_og_image', true ) ),
            'noindex'         => '1' === (string) get_post_meta( $post_id, '_developer_starter_seo_noindex', true ),
        );
    }

    /**
     * Build a compact term record.
     *
     * @param \WP_Term $term Term object.
     * @return array<string,mixed>
     */
    private static function build_term_record( $term ) {
        return array(
            'object_type'     => 'category',
            'object_label'    => __( '分类', 'developer-starter' ),
            'object_id'       => isset( $term->term_id ) ? absint( $term->term_id ) : 0,
            'title'           => isset( $term->name ) ? (string) $term->name : '',
            'edit_url'        => isset( $term->term_id ) ? get_edit_term_link( $term->term_id, 'category' ) : '',
            'seo_title'       => isset( $term->term_id ) ? trim( (string) get_term_meta( $term->term_id, 'ds_category_seo_title', true ) ) : '',
            'seo_description' => isset( $term->term_id ) ? trim( (string) get_term_meta( $term->term_id, 'ds_category_seo_description', true ) ) : '',
            'seo_keywords'    => isset( $term->term_id ) ? trim( (string) get_term_meta( $term->term_id, 'ds_category_seo_keywords', true ) ) : '',
            'canonical'       => '',
            'og_image'        => '',
            'noindex'         => false,
        );
    }

    /**
     * Add simple rule-based issues for one record.
     *
     * @param array<string,mixed> $record Record.
     * @param array<string,array> $buckets Issue buckets.
     * @param array<string,int>   $counts Issue counts.
     * @return void
     */
    private static function add_record_rule_issues( $record, &$buckets, &$counts ) {
        $seo_title       = isset( $record['seo_title'] ) ? (string) $record['seo_title'] : '';
        $seo_description = isset( $record['seo_description'] ) ? (string) $record['seo_description'] : '';
        $canonical       = isset( $record['canonical'] ) ? (string) $record['canonical'] : '';
        $og_image        = isset( $record['og_image'] ) ? (string) $record['og_image'] : '';
        $is_category     = isset( $record['object_type'] ) && 'category' === $record['object_type'];

        if ( '' === $seo_title ) {
            self::add_issue( $buckets, $counts, $record, 'warning', 'missing_title', __( '缺少 SEO 标题', 'developer-starter' ) );
        } elseif ( self::text_length( $seo_title ) < 10 || self::text_length( $seo_title ) > 70 ) {
            self::add_issue( $buckets, $counts, $record, 'warning', 'title_length', __( 'SEO 标题长度建议控制在 10-70 个字符。', 'developer-starter' ) );
        }

        if ( '' === $seo_description ) {
            self::add_issue( $buckets, $counts, $record, 'critical', 'missing_description', __( '缺少 SEO 描述', 'developer-starter' ) );
        } elseif ( self::text_length( $seo_description ) < 50 || self::text_length( $seo_description ) > 160 ) {
            self::add_issue( $buckets, $counts, $record, 'warning', 'description_length', __( 'SEO 描述建议控制在 50-160 个字符。', 'developer-starter' ) );
        }

        if ( '' !== $canonical && ! self::is_valid_url( $canonical ) ) {
            self::add_issue( $buckets, $counts, $record, 'critical', 'canonical_invalid', __( '自定义 canonical URL 格式异常。', 'developer-starter' ) );
        }

        if ( ! $is_category && '' === $og_image ) {
            self::add_issue( $buckets, $counts, $record, 'info', 'missing_og_image', __( '未设置 OG 图片；可回退特色图，但建议关键页面单独设置。', 'developer-starter' ) );
        }

        if ( ! empty( $record['noindex'] ) ) {
            self::add_issue( $buckets, $counts, $record, 'info', 'noindex', __( '该内容设置了 noindex，请确认这是有意设置。', 'developer-starter' ) );
        }
    }

    /**
     * Add one capped issue.
     *
     * @param array<string,array> $buckets Issue buckets.
     * @param array<string,int>   $counts Issue counts.
     * @param array<string,mixed> $record Record.
     * @param string              $severity Severity.
     * @param string              $code Code.
     * @param string              $message Message.
     * @return void
     */
    private static function add_issue( &$buckets, &$counts, $record, $severity, $code, $message ) {
        if ( ! isset( $counts[ $severity ] ) ) {
            $severity = 'info';
        }

        $counts[ $severity ]++;
        if ( count( $buckets[ $severity ] ) >= self::MAX_ISSUES_BUCKET ) {
            return;
        }

        $buckets[ $severity ][] = array(
            'severity'     => $severity,
            'code'         => sanitize_key( $code ),
            'object_type'  => isset( $record['object_type'] ) ? (string) $record['object_type'] : '',
            'object_label' => isset( $record['object_label'] ) ? (string) $record['object_label'] : '',
            'object_id'    => isset( $record['object_id'] ) ? absint( $record['object_id'] ) : 0,
            'title'        => isset( $record['title'] ) ? (string) $record['title'] : '',
            'message'      => $message,
            'edit_url'     => isset( $record['edit_url'] ) ? (string) $record['edit_url'] : '',
        );
    }

    /**
     * Collect duplicate title/description keys.
     *
     * @param array<string,array> $seen Seen map.
     * @param array<string,mixed> $record Record.
     * @param string              $field Field name.
     * @return void
     */
    private static function collect_duplicate_key( &$seen, $record, $field ) {
        $value = isset( $record[ $field ] ) ? trim( (string) $record[ $field ] ) : '';
        if ( '' === $value ) {
            return;
        }
        $key = md5( self::normalize_text( $value ) );
        if ( ! isset( $seen[ $key ] ) ) {
            $seen[ $key ] = array();
        }
        $seen[ $key ][] = $record;
    }

    /**
     * Add duplicate issues for all duplicate groups.
     *
     * @param array<string,array> $seen Seen map.
     * @param string              $code Issue code.
     * @param string              $message Message.
     * @param array<string,array> $buckets Issue buckets.
     * @param array<string,int>   $counts Issue counts.
     * @return void
     */
    private static function add_duplicate_issues( $seen, $code, $message, &$buckets, &$counts ) {
        foreach ( $seen as $records ) {
            if ( ! is_array( $records ) || count( $records ) < 2 ) {
                continue;
            }
            foreach ( $records as $record ) {
                self::add_issue( $buckets, $counts, $record, 'critical', $code, $message );
            }
        }
    }

    /**
     * Extract plain text from post content and module data.
     *
     * @param int $post_id Post ID.
     * @return string
     */
    private static function get_post_plain_text( $post_id ) {
        $texts   = array();
        $content = (string) get_post_field( 'post_content', $post_id );
        if ( function_exists( 'strip_shortcodes' ) ) {
            $content = strip_shortcodes( $content );
        }
        $texts[] = wp_strip_all_tags( $content );

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );
        self::extract_text_from_value( $modules, $texts );

        return trim( preg_replace( '/\s+/u', ' ', implode( ' ', array_filter( array_map( 'trim', $texts ) ) ) ) );
    }

    /**
     * Recursively collect human text from module arrays.
     *
     * @param mixed  $value Value.
     * @param array  $texts Text collection.
     * @param int    $depth Current depth.
     * @param string $key Current key.
     * @return void
     */
    private static function extract_text_from_value( $value, &$texts, $depth = 0, $key = '' ) {
        if ( $depth > 8 ) {
            return;
        }

        if ( is_array( $value ) ) {
            foreach ( $value as $child_key => $child_value ) {
                self::extract_text_from_value( $child_value, $texts, $depth + 1, is_string( $child_key ) ? $child_key : '' );
            }
            return;
        }

        if ( ! is_scalar( $value ) ) {
            return;
        }

        $key = strtolower( (string) $key );
        if ( preg_match( '/(^id$|_id$|url|href|link|image|img|icon|logo|avatar|video|file|color|class|style|layout|type|count|order|sort|enable|show|visible)/', $key ) ) {
            return;
        }

        $text = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $text || self::is_valid_url( $text ) || self::text_length( $text ) < 2 ) {
            return;
        }

        $texts[] = $text;
    }

    /**
     * Push one score check.
     *
     * @param array<string,mixed> $checks Check collection.
     * @param int                 $score Score.
     * @param bool                $passed Passed.
     * @param string              $label Label.
     * @param string              $pass_message Pass message.
     * @param string              $fail_message Fail message.
     * @param int                 $deduct Deduction.
     * @return void
     */
    private static function push_score_check( &$checks, &$score, $passed, $label, $pass_message, $fail_message, $deduct ) {
        $checks[] = array(
            'label'   => $label,
            'status'  => $passed ? 'pass' : 'warning',
            'message' => $passed ? $pass_message : $fail_message,
        );
        if ( ! $passed ) {
            $score -= absint( $deduct );
        }
    }

    /**
     * Split SEO keyword string.
     *
     * @param string $keywords Raw keywords.
     * @return string[]
     */
    private static function split_keywords( $keywords ) {
        $keywords = str_replace( '，', ',', (string) $keywords );
        $parts    = array_map( 'trim', explode( ',', $keywords ) );
        return array_values( array_filter( $parts, 'strlen' ) );
    }

    /**
     * Count HTML images with missing alt attributes.
     *
     * @param string $html HTML.
     * @return array<string,int>
     */
    private static function count_html_images_without_alt( $html ) {
        $matches = array();
        preg_match_all( '/<img\b[^>]*>/i', (string) $html, $matches );
        $images = isset( $matches[0] ) && is_array( $matches[0] ) ? $matches[0] : array();
        $missing = 0;

        foreach ( $images as $image ) {
            if ( ! preg_match( '/\salt\s*=\s*([\"\'])(.*?)\1/i', $image, $alt_match ) || '' === trim( wp_strip_all_tags( $alt_match[2] ) ) ) {
                $missing++;
            }
        }

        return array(
            'total'       => count( $images ),
            'missing_alt' => $missing,
        );
    }

    /**
     * Count internal links in raw HTML.
     *
     * @param string $html HTML.
     * @return int
     */
    private static function count_internal_links( $html ) {
        $matches = array();
        preg_match_all( '/<a\b[^>]+href\s*=\s*([\"\'])(.*?)\1/i', (string) $html, $matches );
        $hrefs = isset( $matches[2] ) && is_array( $matches[2] ) ? $matches[2] : array();
        $home_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        $count = 0;

        foreach ( $hrefs as $href ) {
            $href = trim( (string) $href );
            if ( '' === $href || 0 === strpos( $href, '#' ) || 0 === strpos( $href, 'mailto:' ) || 0 === strpos( $href, 'tel:' ) ) {
                continue;
            }
            $host = wp_parse_url( $href, PHP_URL_HOST );
            if ( empty( $host ) || $host === $home_host ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Basic URL validation.
     *
     * @param string $url URL.
     * @return bool
     */
    private static function is_valid_url( $url ) {
        $url = trim( (string) $url );
        if ( '' === $url ) {
            return false;
        }
        $scheme = wp_parse_url( $url, PHP_URL_SCHEME );
        $host   = wp_parse_url( $url, PHP_URL_HOST );
        return in_array( $scheme, array( 'http', 'https' ), true ) && ! empty( $host );
    }

    /**
     * Normalize text for comparison.
     *
     * @param string $text Text.
     * @return string
     */
    private static function normalize_text( $text ) {
        $text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) ) );
        return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
    }

    /**
     * Get text length.
     *
     * @param string $text Text.
     * @return int
     */
    private static function text_length( $text ) {
        $text = trim( wp_strip_all_tags( (string) $text ) );
        return function_exists( 'mb_strlen' ) ? mb_strlen( $text, 'UTF-8' ) : strlen( $text );
    }

    /**
     * Score label.
     *
     * @param int $score Score.
     * @return string
     */
    private static function score_grade( $score ) {
        if ( $score >= 85 ) {
            return __( '良好', 'developer-starter' );
        }
        if ( $score >= 65 ) {
            return __( '可优化', 'developer-starter' );
        }
        return __( '需要处理', 'developer-starter' );
    }

    /**
     * Get multilingual sitemap provider state if available.
     *
     * @return string
     */
    private static function get_multilingual_sitemap_state() {
        if ( ! function_exists( 'xb_aifanyi_get_theme_provider_capabilities' ) ) {
            return 'not_detected';
        }

        $capabilities = xb_aifanyi_get_theme_provider_capabilities();
        if ( ! is_array( $capabilities ) ) {
            return 'unknown';
        }

        if ( ! empty( $capabilities['sitemap_provider_name'] ) || ! empty( $capabilities['multilingual_sitemap'] ) ) {
            return 'detected';
        }

        return 'detected';
    }
}
