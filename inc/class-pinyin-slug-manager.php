<?php
/**
 * Convert Chinese post and term slugs to pinyin.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Overtrue\Pinyin\Pinyin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pinyin_Slug_Manager {

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Pinyin converter instance.
     *
     * @var Pinyin|null
     */
    private $pinyin = null;

    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function init() {
        if ( ! self::is_enabled() ) {
            return;
        }

        add_filter( 'wp_unique_post_slug', array( $this, 'filter_post_slug' ), 10, 6 );
        add_filter( 'wp_insert_term_data', array( $this, 'filter_insert_term_data' ), 10, 3 );
        add_filter( 'wp_update_term_data', array( $this, 'filter_update_term_data' ), 10, 4 );
    }

    /**
     * Whether the feature is enabled.
     *
     * @return bool
     */
    public static function is_enabled() {
        return '1' === (string) developer_starter_get_option( 'pinyin_slug_enable', '' );
    }

    /**
     * Convert post/page slugs during first publish.
     *
     * @param string $slug          Generated slug.
     * @param int    $post_id       Post ID.
     * @param string $post_status   Post status.
     * @param string $post_type     Post type.
     * @param int    $post_parent   Parent post ID.
     * @param string $original_slug Original slug.
     * @return string
     */
    public function filter_post_slug( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug ) {
        unset( $post_parent );

        if ( ! $this->should_convert_post_slug( $post_id, $post_status, $post_type, $slug, $original_slug ) ) {
            return $slug;
        }

        $source    = '' !== (string) $original_slug ? (string) $original_slug : (string) $slug;
        $converted = $this->convert_to_slug( $source, 'post' );

        return '' !== $converted ? $converted : $slug;
    }

    /**
     * Convert category/tag slugs when no manual slug is submitted.
     *
     * @param array<string,mixed> $data     Term data.
     * @param string              $taxonomy Taxonomy.
     * @param array<string,mixed> $args     Term args.
     * @return array<string,mixed>
     */
    public function filter_insert_term_data( $data, $taxonomy, $args ) {
        if ( ! $this->should_convert_term_slug( $taxonomy, $args ) || empty( $data['name'] ) ) {
            return $data;
        }

        $converted = $this->convert_to_slug( (string) $data['name'], 'term' );

        if ( '' !== $converted ) {
            $data['slug'] = $this->get_unique_term_slug( $converted, $taxonomy, 0, $args );
        }

        return $data;
    }

    /**
     * Convert category/tag slugs on update when the submitted slug is empty.
     *
     * @param array<string,mixed> $data     Term data.
     * @param int                 $term_id  Term ID.
     * @param string              $taxonomy Taxonomy.
     * @param array<string,mixed> $args     Term args.
     * @return array<string,mixed>
     */
    public function filter_update_term_data( $data, $term_id, $taxonomy, $args ) {
        if ( ! $this->should_convert_term_slug( $taxonomy, $args ) || empty( $data['name'] ) ) {
            return $data;
        }

        $converted = $this->convert_to_slug( (string) $data['name'], 'term' );

        if ( '' !== $converted ) {
            $data['slug'] = $this->get_unique_term_slug( $converted, $taxonomy, absint( $term_id ), $args );
        }

        return $data;
    }

    /**
     * Determine whether a post slug should be converted.
     *
     * @param int    $post_id       Post ID.
     * @param string $post_status   Post status.
     * @param string $post_type     Post type.
     * @param string $slug          Generated slug.
     * @param string $original_slug Original slug.
     * @return bool
     */
    private function should_convert_post_slug( $post_id, $post_status, $post_type, $slug, $original_slug ) {
        $allowed_post_types = apply_filters( 'developer_starter_pinyin_slug_post_types', array( 'post', 'page' ) );
        $allowed_post_types = is_array( $allowed_post_types ) ? array_map( 'sanitize_key', $allowed_post_types ) : array( 'post', 'page' );

        if ( ! in_array( $post_type, $allowed_post_types, true ) || 'attachment' === $post_type ) {
            return false;
        }

        if ( in_array( $post_status, array( 'auto-draft', 'inherit', 'trash' ), true ) ) {
            return false;
        }

        $old_status = $post_id ? get_post_field( 'post_status', $post_id, 'edit' ) : '';
        if ( 'publish' === $old_status ) {
            return false;
        }

        $source = '' !== (string) $original_slug ? (string) $original_slug : (string) $slug;

        return (bool) apply_filters(
            'developer_starter_pinyin_slug_should_convert_post',
            $this->contains_chinese( $source ),
            $post_id,
            $post_status,
            $post_type,
            $slug,
            $original_slug
        );
    }

    /**
     * Determine whether a term slug should be converted.
     *
     * @param string              $taxonomy Taxonomy.
     * @param array<string,mixed> $args     Term args.
     * @return bool
     */
    private function should_convert_term_slug( $taxonomy, $args ) {
        $allowed_taxonomies = apply_filters( 'developer_starter_pinyin_slug_taxonomies', array( 'category', 'post_tag' ) );
        $allowed_taxonomies = is_array( $allowed_taxonomies ) ? array_map( 'sanitize_key', $allowed_taxonomies ) : array( 'category', 'post_tag' );

        if ( ! in_array( $taxonomy, $allowed_taxonomies, true ) ) {
            return false;
        }

        $manual_slug = isset( $args['slug'] ) ? trim( (string) $args['slug'] ) : '';
        if ( '' !== $manual_slug ) {
            return false;
        }

        return (bool) apply_filters( 'developer_starter_pinyin_slug_should_convert_term', true, $taxonomy, $args );
    }

    /**
     * Convert a string to a sanitized pinyin slug.
     *
     * @param string $source  Source text.
     * @param string $context Conversion context.
     * @return string
     */
    private function convert_to_slug( $source, $context = 'post' ) {
        $source = urldecode( wp_strip_all_tags( (string) $source ) );
        $source = html_entity_decode( $source, ENT_QUOTES, get_bloginfo( 'charset' ) );
        $source = trim( $source );

        if ( '' === $source || '自动草稿' === $source || ! $this->contains_chinese( $source ) ) {
            return '';
        }

        if ( ! $this->load_pinyin() ) {
            return '';
        }

        $divider = $this->get_divider();
        $mode    = $this->get_mode();
        $option  = defined( 'PINYIN_KEEP_ENGLISH' ) ? constant( 'PINYIN_KEEP_ENGLISH' ) : 64;

        if ( 'abbr' === $mode ) {
            $slug = $this->pinyin->abbr( $source, $divider, $option );
        } else {
            $slug = $this->pinyin->permalink( $source, $divider, $option );
        }

        $slug = strtolower( $this->trim_slug( (string) $slug, $this->get_max_length(), $divider ) );
        $slug = sanitize_title( $slug );

        return (string) apply_filters( 'developer_starter_pinyin_slug_converted', $slug, $source, $context );
    }

    /**
     * Load the bundled pinyin library.
     *
     * @return bool
     */
    private function load_pinyin() {
        if ( $this->pinyin instanceof Pinyin ) {
            return true;
        }

        if ( ! function_exists( 'mb_substr' ) ) {
            return false;
        }

        if ( ! class_exists( '\Overtrue\Pinyin\Pinyin' ) ) {
            $autoload = DEVELOPER_STARTER_INC . '/vendor/pinyin-autoload.php';

            if ( file_exists( $autoload ) ) {
                require_once $autoload;
            }
        }

        if ( ! class_exists( '\Overtrue\Pinyin\Pinyin' ) ) {
            return false;
        }

        $this->pinyin = new Pinyin();

        return true;
    }

    /**
     * Check whether a string contains Chinese characters.
     *
     * @param string $value Value.
     * @return bool
     */
    private function contains_chinese( $value ) {
        return 1 === preg_match( '/[\x{3400}-\x{9fff}\x{f900}-\x{faff}]/u', urldecode( (string) $value ) );
    }

    /**
     * Get conversion mode.
     *
     * @return string
     */
    private function get_mode() {
        $mode = sanitize_key( (string) developer_starter_get_option( 'pinyin_slug_mode', 'full' ) );

        return in_array( $mode, array( 'full', 'abbr' ), true ) ? $mode : 'full';
    }

    /**
     * Get pinyin divider.
     *
     * @return string
     */
    private function get_divider() {
        $divider = (string) developer_starter_get_option( 'pinyin_slug_divider', '-' );

        return in_array( $divider, array( '-', '_', '.', '' ), true ) ? $divider : '-';
    }

    /**
     * Get maximum slug length.
     *
     * @return int
     */
    private function get_max_length() {
        $length = absint( developer_starter_get_option( 'pinyin_slug_max_length', 60 ) );

        return min( 200, $length );
    }

    /**
     * Trim slug without cutting a pinyin segment when possible.
     *
     * @param string $input   Slug.
     * @param int    $length  Max length.
     * @param string $divider Divider.
     * @return string
     */
    private function trim_slug( $input, $length, $divider = '-' ) {
        $input = strip_tags( $input );

        if ( ! $length || strlen( $input ) <= $length ) {
            return $input;
        }

        $trimmed = substr( $input, 0, $length );

        if ( '' !== $divider ) {
            $last_divider = strrpos( $trimmed, $divider );

            if ( false !== $last_divider && $last_divider > 0 ) {
                $trimmed = substr( $trimmed, 0, $last_divider );
            }
        }

        return $trimmed;
    }

    /**
     * Build a unique term slug.
     *
     * @param string              $slug     Slug.
     * @param string              $taxonomy Taxonomy.
     * @param int                 $term_id  Term ID.
     * @param array<string,mixed> $args     Term args.
     * @return string
     */
    private function get_unique_term_slug( $slug, $taxonomy, $term_id, $args ) {
        $term = (object) array(
            'term_id'  => $term_id,
            'taxonomy' => $taxonomy,
            'parent'   => isset( $args['parent'] ) ? absint( $args['parent'] ) : 0,
        );

        return wp_unique_term_slug( $slug, $term );
    }
}
