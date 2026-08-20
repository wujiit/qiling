<?php
/**
 * Ajax search autocomplete service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Search_Autocomplete {

    const AJAX_ACTION  = 'developer_starter_search_autocomplete';
    const NONCE_ACTION = 'developer_starter_search_autocomplete';
    const CACHE_GROUP  = 'developer_starter_search_autocomplete';

    /**
     * Singleton instance.
     *
     * @var Search_Autocomplete|null
     */
    private static $instance = null;

    /**
     * @return Search_Autocomplete
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register hooks.
     *
     * @return void
     */
    public function init() {
        add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'handle_ajax' ) );
        add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( $this, 'handle_ajax' ) );
        add_filter( 'posts_search', array( $this, 'filter_posts_search' ), 20, 2 );
        add_filter( 'posts_clauses', array( $this, 'filter_posts_clauses' ), 20, 2 );
    }

    /**
     * Client config exposed to frontend scripts.
     *
     * @return array<string,mixed>
     */
    public static function get_client_config() {
        return array(
            'autocompleteEnabled' => self::is_enabled(),
            'autocompleteAction'  => self::AJAX_ACTION,
            'autocompleteNonce'   => wp_create_nonce( self::NONCE_ACTION ),
            'minChars'            => self::get_min_chars(),
            'maxResults'          => self::get_max_results(),
            'debounce'            => 250,
            'showThumbnail'       => self::show_thumbnail(),
            'showExcerpt'         => self::show_excerpt(),
            'showPrice'           => self::show_price(),
        );
    }

    /**
     * Whether autocomplete is enabled.
     *
     * @return bool
     */
    public static function is_enabled() {
        return (bool) apply_filters( 'developer_starter_search_autocomplete_enabled', self::get_bool_option( 'search_autocomplete_enable', '1' ) );
    }

    /**
     * @return int
     */
    public static function get_min_chars() {
        $min = function_exists( 'developer_starter_get_option' )
            ? absint( developer_starter_get_option( 'search_autocomplete_min_chars', 2 ) )
            : 2;

        return max( 1, min( 10, (int) apply_filters( 'developer_starter_search_autocomplete_min_chars', $min ) ) );
    }

    /**
     * @return int
     */
    public static function get_max_results() {
        $max = function_exists( 'developer_starter_get_option' )
            ? absint( developer_starter_get_option( 'search_autocomplete_max_results', 6 ) )
            : 6;

        return max( 1, min( 12, (int) apply_filters( 'developer_starter_search_autocomplete_max_results', $max ) ) );
    }

    /**
     * @return bool
     */
    public static function include_pages() {
        return (bool) apply_filters( 'developer_starter_search_autocomplete_include_pages', self::get_bool_option( 'search_autocomplete_include_pages', '1' ) );
    }

    /**
     * @return bool
     */
    public static function include_products() {
        return (bool) apply_filters( 'developer_starter_search_autocomplete_include_products', self::get_bool_option( 'search_autocomplete_include_products', '1' ) );
    }

    /**
     * @return bool
     */
    public static function show_thumbnail() {
        return (bool) apply_filters( 'developer_starter_search_autocomplete_show_thumbnail', self::get_bool_option( 'search_autocomplete_show_thumbnail', '1' ) );
    }

    /**
     * @return bool
     */
    public static function show_excerpt() {
        return (bool) apply_filters( 'developer_starter_search_autocomplete_show_excerpt', self::get_bool_option( 'search_autocomplete_show_excerpt', '1' ) );
    }

    /**
     * @return bool
     */
    public static function show_price() {
        return (bool) apply_filters( 'developer_starter_search_autocomplete_show_price', self::get_bool_option( 'search_autocomplete_show_price', '' ) );
    }

    /**
     * Handle the public Ajax request.
     *
     * @return void
     */
    public function handle_ajax() {
        nocache_headers();

        if ( ! self::is_enabled() ) {
            wp_send_json_error(
                array(
                    'message' => __( '实时搜索未启用。', 'developer-starter' ),
                    'code'    => 'disabled',
                ),
                403
            );
        }

        $nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['nonce'] ) ) : '';
        if ( '' === $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
            wp_send_json_error(
                array(
                    'message' => __( '安全验证失败，请刷新页面重试。', 'developer-starter' ),
                    'code'    => 'bad_nonce',
                ),
                403
            );
        }

        if (
            function_exists( 'developer_starter_is_public_ajax_rate_limited' )
            && developer_starter_is_public_ajax_rate_limited( 'search_autocomplete', 80, MINUTE_IN_SECONDS )
        ) {
            developer_starter_send_public_ajax_rate_limited();
        }

        $term = isset( $_REQUEST['term'] ) ? $this->normalize_term( wp_unslash( (string) $_REQUEST['term'] ) ) : '';
        $scope = isset( $_REQUEST['scope'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['scope'] ) ) : 'all';
        $scope = function_exists( 'developer_starter_normalize_search_scope' )
            ? developer_starter_normalize_search_scope( $scope )
            : $this->normalize_scope( $scope );

        if ( $this->get_term_length( $term ) < self::get_min_chars() ) {
            wp_send_json_success(
                array(
                    'term'       => $term,
                    'scope'      => $scope,
                    'items'      => array(),
                    'search_url' => $this->get_search_url( $term, $scope ),
                )
            );
        }

        $payload = $this->get_cached_payload( $term, $scope );
        if ( ! is_array( $payload ) ) {
            $payload = $this->build_payload( $term, $scope );
            $this->set_cached_payload( $term, $scope, $payload );
        }

        wp_send_json_success( $payload );
    }

    /**
     * Search WHERE for autocomplete queries.
     *
     * @param string    $search SQL fragment.
     * @param \WP_Query $query Query.
     * @return string
     */
    public function filter_posts_search( $search, $query ) {
        if ( ! ( $query instanceof \WP_Query ) || ! $query->get( 'developer_starter_autocomplete_search' ) ) {
            return $search;
        }

        $term = $this->normalize_term( (string) $query->get( 's' ) );
        if ( '' === $term ) {
            return $search;
        }

        $terms = function_exists( 'developer_starter_get_search_query_terms' )
            ? developer_starter_get_search_query_terms( $term )
            : array( $term );
        $fields = $this->get_match_fields( $query );
        if ( empty( $terms ) || empty( $fields ) ) {
            return $search;
        }

        global $wpdb;

        $term_groups = array();
        foreach ( $terms as $single_term ) {
            $single_term = $this->normalize_term( $single_term );
            if ( '' === $single_term ) {
                continue;
            }

            $like = '%' . $wpdb->esc_like( $single_term ) . '%';
            $group = array();

            if ( in_array( 'title', $fields, true ) ) {
                $group[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
            }
            if ( in_array( 'excerpt', $fields, true ) ) {
                $group[] = $wpdb->prepare( "{$wpdb->posts}.post_excerpt LIKE %s", $like );
            }
            if ( in_array( 'content', $fields, true ) ) {
                $group[] = $wpdb->prepare( "{$wpdb->posts}.post_content LIKE %s", $like );
            }
            if ( in_array( 'tag', $fields, true ) ) {
                $group[] = $this->get_taxonomy_match_sql( $single_term );
            }

            if ( ! empty( $group ) ) {
                $term_groups[] = '(' . implode( ' OR ', $group ) . ')';
            }
        }

        if ( empty( $term_groups ) ) {
            return $search;
        }

        $password_sql = is_user_logged_in() ? '' : " AND ({$wpdb->posts}.post_password = '')";

        return ' AND ' . implode( ' AND ', $term_groups ) . $password_sql;
    }

    /**
     * Weighted ordering for autocomplete queries.
     *
     * @param array<string,string> $clauses SQL clauses.
     * @param \WP_Query            $query Query.
     * @return array<string,string>
     */
    public function filter_posts_clauses( $clauses, $query ) {
        if ( ! ( $query instanceof \WP_Query ) || ! $query->get( 'developer_starter_autocomplete_search' ) ) {
            return $clauses;
        }

        $term = $this->normalize_term( (string) $query->get( 's' ) );
        if ( '' === $term ) {
            return $clauses;
        }

        global $wpdb;

        $fields = $this->get_match_fields( $query );
        $score_parts = array();
        $exact_title = $wpdb->prepare( "{$wpdb->posts}.post_title = %s", $term );
        $prefix_title = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $wpdb->esc_like( $term ) . '%' );
        $contains_title = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like( $term ) . '%' );

        if ( in_array( 'title', $fields, true ) ) {
            $score_parts[] = "WHEN {$exact_title} THEN 220";
            $score_parts[] = "WHEN {$prefix_title} THEN 160";
            $score_parts[] = "WHEN {$contains_title} THEN 120";
        }
        if ( in_array( 'excerpt', $fields, true ) ) {
            $contains_excerpt = $wpdb->prepare( "{$wpdb->posts}.post_excerpt LIKE %s", '%' . $wpdb->esc_like( $term ) . '%' );
            $score_parts[] = "WHEN {$contains_excerpt} THEN 70";
        }
        if ( in_array( 'content', $fields, true ) ) {
            $contains_content = $wpdb->prepare( "{$wpdb->posts}.post_content LIKE %s", '%' . $wpdb->esc_like( $term ) . '%' );
            $score_parts[] = "WHEN {$contains_content} THEN 40";
        }
        if ( in_array( 'tag', $fields, true ) ) {
            $score_parts[] = 'WHEN ' . $this->get_taxonomy_match_sql( $term ) . ' THEN 90';
        }

        if ( empty( $score_parts ) ) {
            return $clauses;
        }

        $score_sql = "(CASE\n" . implode( "\n", $score_parts ) . "\nELSE 0 END) + (CASE
            WHEN {$wpdb->posts}.post_type = 'product' THEN 18
            WHEN {$wpdb->posts}.post_type = 'post' THEN 12
            WHEN {$wpdb->posts}.post_type = 'page' THEN 6
            ELSE 0
        END)";

        if ( isset( $clauses['fields'] ) && false === strpos( $clauses['fields'], 'ds_autocomplete_score' ) ) {
            $clauses['fields'] .= ", {$score_sql} AS ds_autocomplete_score";
        }
        $clauses['orderby'] = "ds_autocomplete_score DESC, {$wpdb->posts}.post_date DESC";

        return $clauses;
    }

    /**
     * Build response payload.
     *
     * @param string $term Search term.
     * @param string $scope Search scope.
     * @return array<string,mixed>
     */
    private function build_payload( $term, $scope ) {
        $query = new \WP_Query(
            apply_filters(
                'developer_starter_search_autocomplete_query_args',
                array(
                    's'                                      => $term,
                    'post_type'                              => $this->get_post_types(),
                    'post_status'                            => 'publish',
                    'posts_per_page'                         => self::get_max_results(),
                    'no_found_rows'                          => true,
                    'ignore_sticky_posts'                    => true,
                    'suppress_filters'                       => false,
                    'developer_starter_autocomplete_search'  => 1,
                    'developer_starter_search_scope'         => $scope,
                ),
                $term,
                $scope
            )
        );

        $items = array();
        foreach ( $query->posts as $post ) {
            if ( ! ( $post instanceof \WP_Post ) ) {
                continue;
            }

            $items[] = $this->format_item( $post, $term );
        }

        wp_reset_postdata();

        $payload = array(
            'term'       => $term,
            'scope'      => $scope,
            'items'      => array_values( array_filter( $items ) ),
            'search_url' => $this->get_search_url( $term, $scope ),
        );

        return (array) apply_filters( 'developer_starter_search_autocomplete_payload', $payload, $term, $scope );
    }

    /**
     * Format a result item.
     *
     * @param \WP_Post $post Post.
     * @param string   $term Search term.
     * @return array<string,mixed>
     */
    private function format_item( $post, $term ) {
        $post_type = get_post_type( $post );
        $post_type_object = get_post_type_object( $post_type );
        $type_label = $post_type_object && ! empty( $post_type_object->labels->singular_name )
            ? $post_type_object->labels->singular_name
            : $post_type;

        if ( 'post' === $post_type ) {
            $type_label = __( '文章', 'developer-starter' );
        } elseif ( 'page' === $post_type ) {
            $type_label = __( '页面', 'developer-starter' );
        } elseif ( 'product' === $post_type ) {
            $type_label = __( '产品', 'developer-starter' );
        }

        $excerpt = '';
        if ( self::show_excerpt() ) {
            $excerpt = has_excerpt( $post )
                ? $post->post_excerpt
                : $post->post_content;
            $excerpt = wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) $excerpt ) ), 22 );
        }

        $item = array(
            'id'         => (int) $post->ID,
            'type'       => sanitize_key( $post_type ),
            'type_label' => sanitize_text_field( $type_label ),
            'title'      => wp_strip_all_tags( get_the_title( $post ) ),
            'excerpt'    => $excerpt,
            'url'        => esc_url_raw( get_permalink( $post ) ),
            'thumbnail'  => self::show_thumbnail() ? get_the_post_thumbnail_url( $post, 'thumbnail' ) : '',
            'price'      => '',
        );

        if ( self::show_price() && 'product' === $post_type && function_exists( 'wc_get_product' ) ) {
            $product = wc_get_product( $post->ID );
            if ( $product ) {
                $item['price'] = wp_strip_all_tags( (string) $product->get_price_html() );
            }
        }

        if ( ! is_string( $item['thumbnail'] ) ) {
            $item['thumbnail'] = '';
        }

        return (array) apply_filters( 'developer_starter_search_autocomplete_item', $item, $post, $term );
    }

    /**
     * @return string[]
     */
    private function get_post_types() {
        $public_searchable = get_post_types(
            array(
                'public'              => true,
                'exclude_from_search' => false,
            ),
            'names'
        );

        $preferred = array( 'post' );
        if ( self::include_pages() ) {
            $preferred[] = 'page';
        }
        if ( self::include_products() ) {
            $preferred[] = 'product';
        }

        $post_types = array_values( array_intersect( $preferred, (array) $public_searchable ) );
        if ( empty( $post_types ) ) {
            $post_types = array_values( (array) $public_searchable );
        }

        $post_types = (array) apply_filters( 'developer_starter_search_autocomplete_post_types', $post_types, $public_searchable );
        $post_types = array_values( array_unique( array_filter( array_map( 'sanitize_key', $post_types ) ) ) );

        return empty( $post_types ) ? array( 'post' ) : $post_types;
    }

    /**
     * @param \WP_Query $query Query.
     * @return string[]
     */
    private function get_match_fields( $query ) {
        if ( function_exists( 'developer_starter_get_search_match_fields' ) ) {
            return developer_starter_get_search_match_fields( $query );
        }

        $scope = $this->normalize_scope( (string) $query->get( 'developer_starter_search_scope' ) );
        if ( 'title' === $scope ) {
            return array( 'title' );
        }
        if ( 'content' === $scope ) {
            return array( 'content' );
        }
        if ( 'tag' === $scope ) {
            return array( 'tag' );
        }

        return array( 'title', 'excerpt', 'content', 'tag' );
    }

    /**
     * @param string $term Term.
     * @return string
     */
    private function get_taxonomy_match_sql( $term ) {
        global $wpdb;

        $taxonomies = array( 'post_tag', 'product_tag', 'product_cat' );
        $taxonomies = array_values( array_filter( array_map( 'sanitize_key', (array) apply_filters( 'developer_starter_search_autocomplete_taxonomies', $taxonomies, $term ) ) ) );
        if ( empty( $taxonomies ) ) {
            return '1=0';
        }

        $placeholders = implode( ',', array_fill( 0, count( $taxonomies ), '%s' ) );
        $like = '%' . $wpdb->esc_like( $term ) . '%';
        $args = array_merge( $taxonomies, array( $like, $like ) );

        return $wpdb->prepare(
            "{$wpdb->posts}.ID IN (
                SELECT tr.object_id
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
                WHERE tt.taxonomy IN ({$placeholders})
                AND (t.name LIKE %s OR t.slug LIKE %s)
            )",
            $args
        );
    }

    /**
     * @param string $term Term.
     * @param string $scope Scope.
     * @return array<string,mixed>|false
     */
    private function get_cached_payload( $term, $scope ) {
        $key = $this->get_cache_key( $term, $scope );
        if ( function_exists( 'developer_starter_cache_fetch' ) ) {
            $payload = developer_starter_cache_fetch( $key, self::CACHE_GROUP );
        } else {
            $payload = get_transient( $key );
        }

        return is_array( $payload ) ? $payload : false;
    }

    /**
     * @param string              $term Term.
     * @param string              $scope Scope.
     * @param array<string,mixed> $payload Payload.
     * @return void
     */
    private function set_cached_payload( $term, $scope, $payload ) {
        $key = $this->get_cache_key( $term, $scope );
        $ttl = (int) apply_filters( 'developer_starter_search_autocomplete_cache_ttl', 3 * MINUTE_IN_SECONDS, $term, $scope, $payload );
        $ttl = max( 30, min( HOUR_IN_SECONDS, $ttl ) );

        if ( function_exists( 'developer_starter_cache_store' ) ) {
            developer_starter_cache_store( $key, $payload, $ttl, self::CACHE_GROUP );
            return;
        }

        set_transient( $key, $payload, $ttl );
    }

    /**
     * @param string $term Term.
     * @param string $scope Scope.
     * @return string
     */
    private function get_cache_key( $term, $scope ) {
        $seed = array(
            'blog_id'    => get_current_blog_id(),
            'term'       => $term,
            'scope'      => $scope,
            'post_types' => $this->get_post_types(),
            'display'    => array(
                'thumbnail' => self::show_thumbnail(),
                'excerpt'   => self::show_excerpt(),
                'price'     => self::show_price(),
            ),
            'max'        => self::get_max_results(),
            'version'    => function_exists( 'developer_starter_get_cache_version_stamp' ) ? developer_starter_get_cache_version_stamp( array( 'content', 'settings' ) ) : '',
        );

        return 'ds_sac_' . md5( wp_json_encode( $seed ) );
    }

    /**
     * @param string $term Term.
     * @param string $scope Scope.
     * @return string
     */
    private function get_search_url( $term, $scope ) {
        $url = function_exists( 'developer_starter_get_search_pretty_url' )
            ? developer_starter_get_search_pretty_url( $term, array( 'qiling_search_mode' => 'all' ) )
            : add_query_arg( array( 's' => rawurlencode( $term ), 'qiling_search_mode' => 'all' ), home_url( '/' ) );

        if ( 'all' !== $scope ) {
            $url = add_query_arg( 'search_scope', $scope, $url );
        }

        return esc_url_raw( $url );
    }

    /**
     * @param string $term Term.
     * @return string
     */
    private function normalize_term( $term ) {
        $term = trim( wp_strip_all_tags( (string) $term ) );
        $term = preg_replace( '/\s+/u', ' ', $term );
        if ( ! is_string( $term ) ) {
            return '';
        }

        if ( $this->get_term_length( $term ) > 80 ) {
            $term = function_exists( 'mb_substr' ) ? mb_substr( $term, 0, 80 ) : substr( $term, 0, 80 );
        }

        return trim( $term );
    }

    /**
     * @param string $term Term.
     * @return int
     */
    private function get_term_length( $term ) {
        return function_exists( 'mb_strlen' ) ? (int) mb_strlen( (string) $term ) : strlen( (string) $term );
    }

    /**
     * @param string $key Option key.
     * @param string $default Default value.
     * @return bool
     */
    private static function get_bool_option( $key, $default = '1' ) {
        $value = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( $key, $default )
            : $default;

        return ! in_array( strtolower( (string) $value ), array( '', '0', 'false', 'no' ), true );
    }

    /**
     * @param string $scope Scope.
     * @return string
     */
    private function normalize_scope( $scope ) {
        $scope = sanitize_key( (string) $scope );
        return in_array( $scope, array( 'all', 'title', 'content', 'tag' ), true ) ? $scope : 'all';
    }
}
