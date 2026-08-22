<?php
/**
 * Extensible front-end search mode registry and query integration.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Search_Mode_Manager {
    const QUERY_VAR = 'qiling_search_mode';
    const FALLBACK_MODE = 'all';

    private static $instance = null;
    private $video_table_available = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        add_filter( 'query_vars', array( $this, 'register_query_var' ) );
        add_action( 'pre_get_posts', array( $this, 'apply_to_main_query' ), 15 );
        add_filter( 'posts_where', array( $this, 'exclude_video_posts_from_post_mode' ), 20, 2 );
        add_filter( 'developer_starter_search_cache_seed', array( $this, 'add_mode_to_cache_seed' ), 10, 2 );
    }

    public function register_query_var( $query_vars ) {
        $query_vars[] = self::QUERY_VAR;

        return array_values( array_unique( $query_vars ) );
    }

    public function get_modes( $available_only = false ) {
        $modes = array(
            'all'   => array(
                'label'        => __( '综合搜索', 'developer-starter' ),
                'description'  => __( '搜索文章和页面等现有内容', 'developer-starter' ),
                'available'    => true,
                'result_style' => 'default',
            ),
            'post'  => array(
                'label'        => __( '文章搜索', 'developer-starter' ),
                'description'  => __( '只搜索普通文章', 'developer-starter' ),
                'available'    => true,
                'result_style' => 'default',
            ),
            'video' => array(
                'label'        => __( '影视搜索', 'developer-starter' ),
                'description'  => __( '只搜索启用了视频模式的影视文章', 'developer-starter' ),
                'available'    => $this->is_video_mode_available(),
                'result_style' => 'video',
            ),
        );

        $modes = (array) apply_filters( 'qiling_search_modes', $modes, $this );
        $normalized = array();
        foreach ( $modes as $mode_key => $mode ) {
            $mode_key = sanitize_key( (string) $mode_key );
            if ( '' === $mode_key || ! is_array( $mode ) || empty( $mode['label'] ) ) {
                continue;
            }

            $mode['label']        = (string) $mode['label'];
            $mode['description']  = isset( $mode['description'] ) ? (string) $mode['description'] : '';
            $mode['available']    = ! isset( $mode['available'] ) || (bool) $mode['available'];
            $mode['result_style'] = isset( $mode['result_style'] ) ? sanitize_key( (string) $mode['result_style'] ) : 'default';

            if ( ! $available_only || $mode['available'] ) {
                $normalized[ $mode_key ] = $mode;
            }
        }

        return $normalized;
    }

    public function is_mode_available( $mode ) {
        $mode  = sanitize_key( (string) $mode );
        $modes = $this->get_modes();

        return isset( $modes[ $mode ] ) && ! empty( $modes[ $mode ]['available'] );
    }

    public function normalize_mode( $mode ) {
        $mode = sanitize_key( (string) $mode );

        return $this->is_mode_available( $mode ) ? $mode : self::FALLBACK_MODE;
    }

    public function get_default_mode() {
        return $this->normalize_mode( developer_starter_get_option( 'search_default_mode', self::FALLBACK_MODE ) );
    }

    public function get_current_mode() {
        $mode = get_query_var( self::QUERY_VAR );
        $explicit = '' !== (string) $mode;
        if ( '' === (string) $mode && isset( $_GET[ self::QUERY_VAR ] ) ) {
            $mode = sanitize_key( wp_unslash( (string) $_GET[ self::QUERY_VAR ] ) );
            $explicit = true;
        }

        if ( ! $explicit ) {
            $context_post_id = is_singular() ? get_queried_object_id() : 0;
            $context_mode    = $context_post_id > 0 ? sanitize_key( (string) get_post_meta( $context_post_id, '_qiling_search_mode', true ) ) : '';
            return '' !== $context_mode ? $this->normalize_mode( $context_mode ) : $this->get_default_mode();
        }

        $requested_mode = sanitize_key( (string) $mode );
        if ( ! $this->is_mode_available( $requested_mode ) ) {
            return self::FALLBACK_MODE;
        }

        return $requested_mode;
    }

    public function get_frontend_modes() {
        $available = $this->get_modes( true );
        $enabled   = developer_starter_get_option( 'search_frontend_modes', array( 'all', 'post', 'video' ) );
        $enabled   = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $enabled ) ) ) );
        $modes     = array_intersect_key( $available, array_flip( $enabled ) );

        if ( empty( $modes ) ) {
            $default = $this->get_default_mode();
            $modes   = isset( $available[ $default ] ) ? array( $default => $available[ $default ] ) : array( 'all' => $available['all'] );
        }

        return $modes;
    }

    public function visitor_switching_enabled() {
        return '1' === (string) developer_starter_get_option( 'search_mode_switch_enable', '1' );
    }

    public function get_result_template( $template, $mode = '' ) {
        $mode = $this->normalize_mode( '' === (string) $mode ? $this->get_current_mode() : $mode );

        return (string) apply_filters( 'qiling_search_result_template', $template, $mode, $this );
    }

    public function get_result_card_data( $data, $post_id, $mode = '' ) {
        $mode = $this->normalize_mode( '' === (string) $mode ? $this->get_current_mode() : $mode );

        return (array) apply_filters( 'qiling_search_result_card_data', (array) $data, absint( $post_id ), $mode, $this );
    }

    public function is_video_mode_available() {
        if ( ! defined( 'WP_ARTDPLAYER_VERSION' ) || ! class_exists( 'ArtPlayer_Video_Frontend' ) || ! method_exists( 'ArtPlayer_Video_Frontend', 'get_video_meta_public' ) ) {
            return false;
        }
        if ( null !== $this->video_table_available ) {
            return $this->video_table_available;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'artplayer_video_meta';
        $found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
        $this->video_table_available = ( $table === $found );

        return $this->video_table_available;
    }

    public function apply_to_main_query( $query ) {
        if ( ! ( $query instanceof \WP_Query ) || is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
            return;
        }

        $mode = $this->get_current_mode();
        $query->set( self::QUERY_VAR, $mode );
        $query->set( 'developer_starter_search_mode', $mode );

        if ( 'post' === $mode ) {
            $query->set( 'post_type', 'post' );
            if ( $this->is_video_mode_available() ) {
                $query->set( 'developer_starter_exclude_video_posts', 1 );
            }
        } elseif ( 'video' === $mode ) {
            $query->set( 'post_type', 'post' );
            $query->set( 'artplayer_video_mode', '1' );
        }

        $per_page = absint( developer_starter_get_option( 'search_results_per_page', 18 ) );
        $query->set( 'posts_per_page', in_array( $per_page, array( 12, 18, 24, 30 ), true ) ? $per_page : 18 );

        do_action( 'qiling_apply_search_mode_query', $query, $mode, $this );
        $query_args = (array) apply_filters( 'qiling_search_mode_query_args', array(), $mode, $query, $this );
        foreach ( $query_args as $key => $value ) {
            $key = sanitize_key( (string) $key );
            if ( '' !== $key ) {
                $query->set( $key, $value );
            }
        }
    }

    public function exclude_video_posts_from_post_mode( $where, $query ) {
        if ( ! ( $query instanceof \WP_Query ) || ! $query->get( 'developer_starter_exclude_video_posts' ) ) {
            return $where;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'artplayer_video_meta';

        return $where . " AND NOT EXISTS (SELECT 1 FROM {$table} qiling_video_meta WHERE qiling_video_meta.post_id = {$wpdb->posts}.ID AND qiling_video_meta.is_video_mode = 1)";
    }

    public function add_mode_to_cache_seed( $seed, $query ) {
        $seed['search_mode'] = $query instanceof \WP_Query ? $this->normalize_mode( $query->get( 'developer_starter_search_mode' ) ) : self::FALLBACK_MODE;

        return $seed;
    }
}
