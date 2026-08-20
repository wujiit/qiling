<?php
/**
 * Industry-aware Schema.org JSON-LD engine.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\SEO;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Builds one connected @graph from site settings, page modules and content models.
 */
class Industry_Schema_Engine {

    const VERSION                 = '1.0.0';
    const OPTION_ENABLE           = 'schema_engine_enable';
    const OPTION_INDUSTRY_TYPE    = 'schema_industry_type';
    const OPTION_DEFAULT_CURRENCY = 'schema_default_currency';
    const META_SCHEMA_OVERRIDE_ENABLE = '_qiling_schema_override_enable';
    const META_SCHEMA_OVERRIDE_TYPE   = '_qiling_schema_override_type';
    const META_SCHEMA_OVERRIDE_DATA   = '_qiling_schema_override_data';

    /**
     * @var self|null
     */
    private static $instance = null;

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
     * Settings choices for the industry selector.
     *
     * @return array<string,string>
     */
    public static function get_industry_choices() {
        return array(
            'auto'          => __( '自动识别', 'developer-starter' ),
            'corporate'     => __( '企业官网 / 通用品牌', 'developer-starter' ),
            'local_service' => __( '本地服务 / 门店', 'developer-starter' ),
            'ecommerce'     => __( '电商零售 / 产品目录', 'developer-starter' ),
            'publisher'     => __( '博客 / 杂志 / 媒体', 'developer-starter' ),
            'education'     => __( '教育培训 / 课程', 'developer-starter' ),
            'medical'       => __( '医疗健康 / 诊所', 'developer-starter' ),
            'real_estate'   => __( '房产 / 空间 / 场地', 'developer-starter' ),
            'restaurant'    => __( '餐饮 / 菜单', 'developer-starter' ),
            'software'      => __( '软件 / SaaS / 应用', 'developer-starter' ),
            'manufacturing' => __( '制造业 / B2B', 'developer-starter' ),
            'logistics'     => __( '物流 / 供应链', 'developer-starter' ),
            'agriculture_food' => __( '农业 / 食品', 'developer-starter' ),
            'energy_environment' => __( '能源 / 环保', 'developer-starter' ),
            'industrial_park' => __( '产业园区 / 招商', 'developer-starter' ),
            'human_resources' => __( '人力资源 / 招聘', 'developer-starter' ),
            'hospitality'   => __( '酒店民宿 / 旅游', 'developer-starter' ),
            'property_management' => __( '物业 / 社区服务', 'developer-starter' ),
            'nonprofit'     => __( '公益组织 / 社会服务', 'developer-starter' ),
            'government'    => __( '政务机构 / 公共服务', 'developer-starter' ),
            'event'         => __( '活动 / 展会 / 票务', 'developer-starter' ),
        );
    }

    /**
     * Page-level Schema override type choices.
     *
     * @return array<string,string>
     */
    public static function get_page_schema_type_choices() {
        return array(
            ''           => __( '不覆盖，使用自动识别', 'developer-starter' ),
            'FAQPage'    => 'FAQ',
            'Product'    => 'Product',
            'Article'    => 'Article',
            'Course'     => 'Course',
            'Event'      => 'Event',
            'Service'    => 'Service',
            'Review'     => 'Review',
            'HowTo'      => 'HowTo',
            'JobPosting' => 'JobPosting',
        );
    }

    /**
     * Sanitize a page-level Schema override payload.
     *
     * @param mixed $value Raw override payload.
     * @return array<string,mixed>
     */
    public static function sanitize_page_schema_override( $value ) {
        $value = is_array( $value ) ? wp_unslash( $value ) : array();
        $choices = self::get_page_schema_type_choices();
        $type = isset( $value['type'] ) ? sanitize_text_field( (string) $value['type'] ) : '';
        $type = array_key_exists( $type, $choices ) ? $type : '';
        $enabled = ! empty( $value['enabled'] ) || ! empty( $value['enable'] );

        $data = isset( $value['data'] ) && is_array( $value['data'] ) ? $value['data'] : $value;
        unset( $data['enabled'], $data['enable'], $data['type'], $data['data'] );

        $sanitized = array();
        $textarea_fields = array(
            'description',
            'review_body',
            'faq_items_text',
            'howto_steps_text',
            'location_address',
            'job_location',
        );
        $url_fields = array( 'url', 'image' );
        $date_fields = array( 'date_published', 'date_modified', 'start_date', 'end_date', 'date_posted', 'valid_through' );
        $text_fields = array(
            'name',
            'headline',
            'title',
            'author_name',
            'brand',
            'sku',
            'price',
            'currency',
            'rating_value',
            'rating_count',
            'item_name',
            'provider_name',
            'course_provider',
            'service_area',
            'location_name',
            'event_status',
            'event_attendance_mode',
            'employment_type',
            'hiring_organization',
        );

        foreach ( $text_fields as $field ) {
            if ( isset( $data[ $field ] ) && ! is_array( $data[ $field ] ) && ! is_object( $data[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( (string) $data[ $field ] );
            }
        }
        foreach ( $textarea_fields as $field ) {
            if ( isset( $data[ $field ] ) && ! is_array( $data[ $field ] ) && ! is_object( $data[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_textarea_field( (string) $data[ $field ] );
            }
        }
        foreach ( $url_fields as $field ) {
            if ( isset( $data[ $field ] ) && ! is_array( $data[ $field ] ) && ! is_object( $data[ $field ] ) ) {
                $sanitized[ $field ] = esc_url_raw( trim( (string) $data[ $field ] ) );
            }
        }
        foreach ( $date_fields as $field ) {
            if ( isset( $data[ $field ] ) && ! is_array( $data[ $field ] ) && ! is_object( $data[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( (string) $data[ $field ] );
            }
        }

        if ( ! empty( $sanitized['currency'] ) ) {
            $currency = (string) preg_replace( '/[^A-Z]/', '', strtoupper( (string) $sanitized['currency'] ) );
            $sanitized['currency'] = 3 === strlen( $currency ) ? $currency : '';
        }
        if ( isset( $sanitized['rating_value'] ) && '' !== $sanitized['rating_value'] && is_numeric( $sanitized['rating_value'] ) ) {
            $sanitized['rating_value'] = (string) max( 1, min( 5, (float) $sanitized['rating_value'] ) );
        }
        if ( isset( $sanitized['rating_count'] ) && '' !== $sanitized['rating_count'] && is_numeric( $sanitized['rating_count'] ) ) {
            $sanitized['rating_count'] = (string) max( 1, absint( $sanitized['rating_count'] ) );
        }

        $sanitized['faq_items'] = self::sanitize_override_faq_items(
            isset( $data['faq_items'] ) ? $data['faq_items'] : array(),
            isset( $sanitized['faq_items_text'] ) ? $sanitized['faq_items_text'] : ''
        );
        $sanitized['howto_steps'] = self::sanitize_override_howto_steps(
            isset( $data['howto_steps'] ) ? $data['howto_steps'] : array(),
            isset( $sanitized['howto_steps_text'] ) ? $sanitized['howto_steps_text'] : ''
        );

        return array(
            'enabled' => $enabled && '' !== $type,
            'type'    => $type,
            'data'    => $sanitized,
        );
    }

    /**
     * Read a page-level Schema override from post meta.
     *
     * @param int $post_id Post id.
     * @return array<string,mixed>
     */
    public static function get_page_schema_override( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array(
                'enabled' => false,
                'type'    => '',
                'data'    => array(),
            );
        }

        $data = get_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_DATA, true );

        return self::sanitize_page_schema_override(
            array(
                'enabled' => get_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_ENABLE, true ),
                'type'    => get_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_TYPE, true ),
                'data'    => is_array( $data ) ? $data : array(),
            )
        );
    }

    /**
     * Persist a page-level Schema override to post meta.
     *
     * @param int   $post_id Post id.
     * @param mixed $raw Raw override payload.
     * @return array<string,mixed>
     */
    public static function persist_page_schema_override( $post_id, $raw ) {
        $post_id = absint( $post_id );
        $override = self::sanitize_page_schema_override( $raw );
        if ( $post_id <= 0 ) {
            return $override;
        }

        if ( ! empty( $override['enabled'] ) ) {
            update_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_ENABLE, '1' );
        } else {
            delete_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_ENABLE );
        }

        if ( ! empty( $override['type'] ) ) {
            update_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_TYPE, (string) $override['type'] );
        } else {
            delete_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_TYPE );
        }

        if ( ! empty( $override['data'] ) && is_array( $override['data'] ) ) {
            update_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_DATA, $override['data'] );
        } else {
            delete_post_meta( $post_id, self::META_SCHEMA_OVERRIDE_DATA );
        }

        return $override;
    }

    /**
     * Normalize FAQ rows from structured rows or textarea lines.
     *
     * @param mixed  $rows Raw rows.
     * @param string $text Textarea fallback.
     * @return array<int,array<string,string>>
     */
    private static function sanitize_override_faq_items( $rows, $text = '' ) {
        $items = array();
        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }
                $question = isset( $row['question'] ) ? sanitize_text_field( (string) $row['question'] ) : '';
                $answer = isset( $row['answer'] ) ? sanitize_textarea_field( (string) $row['answer'] ) : '';
                if ( '' !== $question && '' !== $answer ) {
                    $items[] = array(
                        'question' => $question,
                        'answer'   => $answer,
                    );
                }
            }
        }

        if ( empty( $items ) && '' !== trim( (string) $text ) ) {
            $lines = preg_split( '/\r\n|\r|\n/', (string) $text );
            foreach ( is_array( $lines ) ? $lines : array() as $line ) {
                $line = trim( (string) $line );
                if ( '' === $line ) {
                    continue;
                }
                $parts = preg_split( '/\s*[|｜]\s*/u', $line, 2 );
                if ( ! is_array( $parts ) || count( $parts ) < 2 ) {
                    continue;
                }
                $question = sanitize_text_field( (string) $parts[0] );
                $answer = sanitize_textarea_field( (string) $parts[1] );
                if ( '' !== $question && '' !== $answer ) {
                    $items[] = array(
                        'question' => $question,
                        'answer'   => $answer,
                    );
                }
            }
        }

        return array_slice( $items, 0, 24 );
    }

    /**
     * Normalize HowTo steps from structured rows or textarea lines.
     *
     * @param mixed  $rows Raw rows.
     * @param string $text Textarea fallback.
     * @return array<int,array<string,string>>
     */
    private static function sanitize_override_howto_steps( $rows, $text = '' ) {
        $items = array();
        if ( is_array( $rows ) ) {
            foreach ( $rows as $index => $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }
                $name = isset( $row['name'] ) ? sanitize_text_field( (string) $row['name'] ) : sprintf( __( '步骤 %d', 'developer-starter' ), absint( $index ) + 1 );
                $step_text = isset( $row['text'] ) ? sanitize_textarea_field( (string) $row['text'] ) : '';
                if ( '' !== $step_text ) {
                    $items[] = array(
                        'name' => '' !== $name ? $name : sprintf( __( '步骤 %d', 'developer-starter' ), count( $items ) + 1 ),
                        'text' => $step_text,
                    );
                }
            }
        }

        if ( empty( $items ) && '' !== trim( (string) $text ) ) {
            $lines = preg_split( '/\r\n|\r|\n/', (string) $text );
            foreach ( is_array( $lines ) ? $lines : array() as $line ) {
                $line = trim( (string) $line );
                if ( '' === $line ) {
                    continue;
                }
                $items[] = array(
                    'name' => sprintf( __( '步骤 %d', 'developer-starter' ), count( $items ) + 1 ),
                    'text' => sanitize_textarea_field( $line ),
                );
            }
        }

        return array_slice( $items, 0, 24 );
    }

    /**
     * Sanitize theme options owned by this service.
     *
     * @param array<string,mixed> $sanitized Sanitized option draft.
     * @param array<string,mixed> $existing_options Existing options.
     * @return array<string,mixed>
     */
    public static function sanitize_options( $sanitized, $existing_options = array() ) {
        $sanitized = is_array( $sanitized ) ? $sanitized : array();

        if ( array_key_exists( self::OPTION_ENABLE, $sanitized ) ) {
            $sanitized[ self::OPTION_ENABLE ] = ( '1' === (string) $sanitized[ self::OPTION_ENABLE ] ) ? '1' : '';
        }

        if ( array_key_exists( self::OPTION_INDUSTRY_TYPE, $sanitized ) ) {
            $industry = sanitize_key( (string) $sanitized[ self::OPTION_INDUSTRY_TYPE ] );
            if ( ! array_key_exists( $industry, self::get_industry_choices() ) ) {
                $industry = 'auto';
            }
            $sanitized[ self::OPTION_INDUSTRY_TYPE ] = $industry;
        }

        if ( array_key_exists( self::OPTION_DEFAULT_CURRENCY, $sanitized ) ) {
            $currency = (string) preg_replace( '/[^A-Z]/', '', strtoupper( (string) $sanitized[ self::OPTION_DEFAULT_CURRENCY ] ) );
            $sanitized[ self::OPTION_DEFAULT_CURRENCY ] = 3 === strlen( $currency ) ? $currency : 'CNY';
        }

        unset( $existing_options );

        return $sanitized;
    }

    /**
     * Whether front-end schema output is enabled.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return bool
     */
    public function is_enabled( $options = null ) {
        $options = $this->get_options( $options );
        $enabled = isset( $options[ self::OPTION_ENABLE ] ) ? (string) $options[ self::OPTION_ENABLE ] : '1';

        return '1' === $enabled;
    }

    /**
     * JSON-LD payload for the current request.
     *
     * @param int $post_id Optional post id override.
     * @return string
     */
    public function get_json_ld( $post_id = 0 ) {
        $context = $this->build_context( $post_id );
        if ( ! $this->is_enabled( $context['options'] ) || ! empty( $context['is_404'] ) ) {
            return '';
        }

        $graph = $this->build_graph_from_context( $context );
        if ( empty( $graph ) ) {
            return '';
        }

        $payload = $this->build_schema_payload( $graph, $context );

        return $this->encode_schema_payload( $payload );
    }

    /**
     * Build admin preview data from the same graph generator used by front-end output.
     *
     * @param int $post_id Optional post id override.
     * @return array<string,mixed>
     */
    public function get_preview_data( $post_id = 0 ) {
        $context = $this->build_context( $post_id );
        $graph = $this->build_graph_from_context( $context );
        $payload = $this->build_schema_payload( $graph, $context );
        $required_warnings = $this->get_schema_preview_required_warnings( $context, $graph );
        $diagnostics = $this->build_schema_diagnostics( $context, $graph, $required_warnings );

        return array(
            'enabled'          => $this->is_enabled( $context['options'] ),
            'post_id'          => isset( $context['post_id'] ) ? (int) $context['post_id'] : 0,
            'industry'         => isset( $context['industry'] ) ? (string) $context['industry'] : '',
            'default_currency' => $this->get_default_currency(),
            'primary_type'     => $this->get_preview_primary_type( $graph ),
            'node_status'      => $this->get_preview_node_status( $graph ),
            'missing_required' => isset( $diagnostics['missing_required'] ) ? $diagnostics['missing_required'] : $required_warnings,
            'diagnostics'      => $diagnostics,
            'visual_issues'    => isset( $diagnostics['issues'] ) ? $diagnostics['issues'] : array(),
            'json_ld'          => $this->encode_schema_payload( $payload ),
            'graph'            => array_values( $graph ),
        );
    }

    /**
     * Build Schema diagnostics for preview and page editors.
     *
     * @param int $post_id Optional post id override.
     * @return array<string,mixed>
     */
    public function get_schema_diagnostics( $post_id = 0 ) {
        $context = $this->build_context( $post_id );
        $graph = $this->build_graph_from_context( $context );

        return $this->build_schema_diagnostics( $context, $graph, $this->get_schema_preview_required_warnings( $context, $graph ) );
    }

    /**
     * Build graph nodes for the current request.
     *
     * @param int $post_id Optional post id override.
     * @return array<int,array<string,mixed>>
     */
    public function build_graph( $post_id = 0 ) {
        return $this->build_graph_from_context( $this->build_context( $post_id ) );
    }

    /**
     * Build filterable JSON-LD payload from graph nodes.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param array<string,mixed>            $context Resolved page context.
     * @return array<string,mixed>
     */
    private function build_schema_payload( $graph, $context ) {
        if ( empty( $graph ) ) {
            return array();
        }

        $payload = array(
            '@context' => 'https://schema.org',
            '@graph'   => array_values( $graph ),
        );

        /**
         * Filter the full Schema.org payload before JSON encoding.
         *
         * @param array<string,mixed> $payload JSON-LD payload.
         * @param array<string,mixed> $context Resolved page context.
         */
        $payload = apply_filters( 'developer_starter_schema_payload', $payload, $context );

        return is_array( $payload ) ? $payload : array();
    }

    /**
     * Encode a JSON-LD payload for output or preview.
     *
     * @param array<string,mixed> $payload JSON-LD payload.
     * @return string
     */
    private function encode_schema_payload( $payload ) {
        if ( ! is_array( $payload ) || empty( $payload['@graph'] ) || ! is_array( $payload['@graph'] ) ) {
            return '';
        }

        $json = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

        return is_string( $json ) ? $json : '';
    }

    /**
     * Build reusable context for graph generation.
     *
     * @param int $post_id Optional post id override.
     * @return array<string,mixed>
     */
    public function build_context( $post_id = 0 ) {
        $options = $this->get_options();
        $post_id = $this->resolve_context_post_id( $post_id );
        $post    = $post_id > 0 ? get_post( $post_id ) : null;
        $modules = $this->get_page_modules( $post_id );
        $module_schema = $this->extract_module_schema_entities( $modules );
        $content_model = $post instanceof \WP_Post ? $this->resolve_content_model_for_post_type( $post->post_type ) : array();
        $industry = $this->resolve_industry_type( $options, $post_id, $modules, $content_model );

        $context = array(
            'version'       => self::VERSION,
            'options'       => $options,
            'post_id'       => $post_id,
            'post'          => $post,
            'post_type'     => $post instanceof \WP_Post ? (string) $post->post_type : '',
            'content_model' => $content_model,
            'industry'      => $industry,
            'modules'       => $modules,
            'module_schema' => $module_schema,
            'page_schema_override' => self::get_page_schema_override( $post_id ),
            'home_url'      => $this->get_home_url(),
            'current_url'   => $this->get_current_url( $post_id ),
            'canonical_url' => $this->get_canonical_url( $post_id ),
            'title'         => $this->get_context_title( $post_id ),
            'description'   => $this->get_context_description( $post_id ),
            'image'         => $this->get_context_image( $post_id ),
            'is_singular'   => is_singular() || $post instanceof \WP_Post,
            'is_front_page' => is_front_page(),
            'is_home'       => is_home(),
            'is_archive'    => is_archive(),
            'is_search'     => is_search(),
            'is_404'        => is_404(),
        );

        /**
         * Filter resolved context before graph nodes are built.
         *
         * @param array<string,mixed> $context Resolved page context.
         */
        return apply_filters( 'developer_starter_schema_engine_context', $context );
    }

    /**
     * Build graph nodes from context.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<int,array<string,mixed>>
     */
    private function build_graph_from_context( $context ) {
        if ( ! is_array( $context ) || empty( $context['home_url'] ) ) {
            return array();
        }

        $graph = array(
            $this->build_organization_node( $context ),
            $this->build_website_node( $context ),
        );

        $breadcrumb = $this->build_breadcrumb_node( $context );
        if ( ! empty( $breadcrumb ) ) {
            $graph[] = $breadcrumb;
        }

        $webpage = $this->build_webpage_node( $context );
        if ( ! empty( $webpage ) ) {
            $graph[] = $webpage;
        }

        $primary = $this->build_primary_content_node( $context );
        if ( ! empty( $primary ) ) {
            $graph[] = $primary;
        }

        foreach ( $this->build_module_item_list_nodes( $context ) as $node ) {
            $graph[] = $node;
        }

        $graph = $this->apply_page_schema_override_to_graph( $graph, $context );
        $graph = $this->clean_graph( $graph );

        /**
         * Filter Schema.org @graph nodes.
         *
         * @param array<int,array<string,mixed>> $graph Graph nodes.
         * @param array<string,mixed>            $context Resolved page context.
         */
        $graph = apply_filters( 'developer_starter_schema_graph', $graph, $context );

        return is_array( $graph ) ? array_values( array_filter( $graph, 'is_array' ) ) : array();
    }

    /**
     * Build Organization/LocalBusiness level node.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<string,mixed>
     */
    private function build_organization_node( $context ) {
        $options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $home_url = isset( $context['home_url'] ) ? (string) $context['home_url'] : home_url( '/' );
        $type = $this->get_organization_schema_type( isset( $context['industry'] ) ? (string) $context['industry'] : 'corporate' );
        $name = isset( $options['company_name'] ) && '' !== trim( (string) $options['company_name'] )
            ? (string) $options['company_name']
            : get_bloginfo( 'name' );
        $description = isset( $options['company_brief'] ) && '' !== trim( (string) $options['company_brief'] )
            ? (string) $options['company_brief']
            : get_bloginfo( 'description' );

        $node = array(
            '@type'       => $type,
            '@id'         => trailingslashit( $home_url ) . '#organization',
            'name'        => $name,
            'url'         => $home_url,
            'description' => $description,
        );

        $logo = $this->get_schema_logo_url( $options );
        if ( '' !== $logo ) {
            $node['logo'] = array(
                '@type' => 'ImageObject',
                'url'   => $logo,
            );
            $node['image'] = $logo;
        }

        if ( ! empty( $options['company_phone'] ) ) {
            $node['telephone'] = (string) $options['company_phone'];
        }
        if ( ! empty( $options['company_email'] ) ) {
            $node['email'] = (string) $options['company_email'];
        }
        if ( ! empty( $options['company_address'] ) ) {
            $node['address'] = array(
                '@type'         => 'PostalAddress',
                'streetAddress' => (string) $options['company_address'],
            );
        }
        if ( ! empty( $options['company_working_hours'] ) && in_array( $type, array( 'LocalBusiness', 'Store', 'Restaurant', 'MedicalOrganization', 'RealEstateAgent', 'LodgingBusiness' ), true ) ) {
            $node['openingHours'] = (string) $options['company_working_hours'];
        }

        return $this->filter_node( 'organization', $node, $context );
    }

    /**
     * Build WebSite node.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<string,mixed>
     */
    private function build_website_node( $context ) {
        $home_url = isset( $context['home_url'] ) ? (string) $context['home_url'] : home_url( '/' );

        $node = array(
            '@type'           => 'WebSite',
            '@id'             => trailingslashit( $home_url ) . '#website',
            'url'             => $home_url,
            'name'            => get_bloginfo( 'name' ),
            'description'     => get_bloginfo( 'description' ),
            'publisher'       => array(
                '@id' => trailingslashit( $home_url ) . '#organization',
            ),
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => add_query_arg( 's', '{search_term_string}', $home_url ),
                'query-input' => 'required name=search_term_string',
            ),
        );

        return $this->filter_node( 'website', $node, $context );
    }

    /**
     * Build current WebPage/CollectionPage/FAQPage node.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<string,mixed>
     */
    private function build_webpage_node( $context ) {
        $home_url = isset( $context['home_url'] ) ? (string) $context['home_url'] : home_url( '/' );
        $url = ! empty( $context['canonical_url'] ) ? (string) $context['canonical_url'] : (string) $context['current_url'];
        $module_schema = isset( $context['module_schema'] ) && is_array( $context['module_schema'] ) ? $context['module_schema'] : array();
        $faq_items = isset( $module_schema['faq'] ) && is_array( $module_schema['faq'] ) ? $module_schema['faq'] : array();
        $types = $this->get_webpage_types( $context, $faq_items );

        $node = array(
            '@type'       => count( $types ) > 1 ? $types : $types[0],
            '@id'         => untrailingslashit( $url ) . '#webpage',
            'url'         => $url,
            'name'        => isset( $context['title'] ) ? (string) $context['title'] : '',
            'description' => isset( $context['description'] ) ? (string) $context['description'] : '',
            'isPartOf'    => array(
                '@id' => trailingslashit( $home_url ) . '#website',
            ),
            'about'       => array(
                '@id' => trailingslashit( $home_url ) . '#organization',
            ),
        );

        if ( ! empty( $context['image'] ) ) {
            $node['primaryImageOfPage'] = array(
                '@type' => 'ImageObject',
                'url'   => (string) $context['image'],
            );
        }

        $breadcrumb = $this->build_breadcrumb_items( $context );
        if ( ! empty( $breadcrumb ) ) {
            $node['breadcrumb'] = array(
                '@id' => untrailingslashit( $url ) . '#breadcrumb',
            );
        }

        if ( ! empty( $faq_items ) ) {
            $node['mainEntity'] = $faq_items;
        }

        return $this->filter_node( 'webpage', $node, $context );
    }

    /**
     * Build BreadcrumbList node.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<string,mixed>
     */
    private function build_breadcrumb_node( $context ) {
        $items = $this->build_breadcrumb_items( $context );
        if ( empty( $items ) || count( $items ) < 2 ) {
            return array();
        }

        $url = ! empty( $context['canonical_url'] ) ? (string) $context['canonical_url'] : (string) $context['current_url'];
        $node = array(
            '@type'           => 'BreadcrumbList',
            '@id'             => untrailingslashit( $url ) . '#breadcrumb',
            'itemListElement' => $items,
        );

        return $this->filter_node( 'breadcrumb', $node, $context );
    }

    /**
     * Build Article/Product/Service/etc node for singular content.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<string,mixed>
     */
    private function build_primary_content_node( $context ) {
        $post = isset( $context['post'] ) ? $context['post'] : null;
        if ( ! $post instanceof \WP_Post || empty( $context['is_singular'] ) ) {
            return array();
        }

        $post_id = (int) $post->ID;
        $url = ! empty( $context['canonical_url'] ) ? (string) $context['canonical_url'] : get_permalink( $post_id );
        $content_model = isset( $context['content_model'] ) && is_array( $context['content_model'] ) ? $context['content_model'] : array();
        $schema_types = isset( $content_model['schemaTypes'] ) && is_array( $content_model['schemaTypes'] ) ? $content_model['schemaTypes'] : array();
        $primary_type = $this->resolve_primary_schema_type_for_post( $post, $schema_types );
        if ( 'WebPage' === $primary_type ) {
            return array();
        }

        $description = isset( $context['description'] ) ? (string) $context['description'] : '';
        $home_url = isset( $context['home_url'] ) ? (string) $context['home_url'] : home_url( '/' );

        $node = array(
            '@type'       => $primary_type,
            '@id'         => untrailingslashit( $url ) . '#primary',
            'url'         => $url,
            'name'        => isset( $context['title'] ) ? (string) $context['title'] : get_the_title( $post_id ),
            'description' => $description,
            'isPartOf'    => array(
                '@id' => untrailingslashit( $url ) . '#webpage',
            ),
        );

        if ( ! empty( $context['image'] ) ) {
            $node['image'] = (string) $context['image'];
        }

        if ( in_array( $primary_type, array( 'Article', 'BlogPosting', 'NewsArticle' ), true ) ) {
            $node['headline']      = $node['name'];
            $node['datePublished'] = get_post_time( 'c', true, $post_id );
            $node['dateModified']  = get_post_modified_time( 'c', true, $post_id );
            $node['author']        = array(
                '@type' => 'Person',
                'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
                'url'   => get_author_posts_url( (int) $post->post_author ),
            );
            $node['publisher']     = array(
                '@id' => trailingslashit( $home_url ) . '#organization',
            );
            $node['mainEntityOfPage'] = array(
                '@id' => untrailingslashit( $url ) . '#webpage',
            );
        } else {
            $node = $this->enrich_model_node( $node, $post_id, $primary_type, $context );
        }

        return $this->filter_node( 'primary', $node, $context );
    }

    /**
     * Build ItemList nodes from page modules.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<int,array<string,mixed>>
     */
    private function build_module_item_list_nodes( $context ) {
        $module_schema = isset( $context['module_schema'] ) && is_array( $context['module_schema'] ) ? $context['module_schema'] : array();
        $url = ! empty( $context['canonical_url'] ) ? (string) $context['canonical_url'] : (string) $context['current_url'];
        $lists = array(
            'services' => array( 'name' => __( '服务项目', 'developer-starter' ), 'type' => 'Service' ),
            'products' => array( 'name' => __( '产品项目', 'developer-starter' ), 'type' => 'Product' ),
            'courses'  => array( 'name' => __( '课程项目', 'developer-starter' ), 'type' => 'Course' ),
            'events'   => array( 'name' => __( '活动项目', 'developer-starter' ), 'type' => 'Event' ),
            'branches' => array( 'name' => __( '门店与服务网点', 'developer-starter' ), 'type' => 'LocalBusiness' ),
            'people'   => array( 'name' => __( '团队成员', 'developer-starter' ), 'type' => 'Person' ),
            'reviews'  => array( 'name' => __( '客户评价', 'developer-starter' ), 'type' => 'Review' ),
            'works'    => array( 'name' => __( '案例与资源', 'developer-starter' ), 'type' => 'CreativeWork' ),
        );
        $nodes = array();

        foreach ( $lists as $key => $definition ) {
            if ( empty( $module_schema[ $key ] ) || ! is_array( $module_schema[ $key ] ) ) {
                continue;
            }

            $items = array_slice( array_values( $module_schema[ $key ] ), 0, 24 );
            $list_items = array();
            foreach ( $items as $index => $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }
                $list_items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $index + 1,
                    'item'     => $item,
                );
            }

            if ( empty( $list_items ) ) {
                continue;
            }

            $nodes[] = $this->filter_node(
                'module_item_list_' . $key,
                array(
                    '@type'           => 'ItemList',
                    '@id'             => untrailingslashit( $url ) . '#schema-' . $key,
                    'name'            => $definition['name'],
                    'itemListElement' => $list_items,
                ),
                $context
            );
        }

        return $nodes;
    }

    /**
     * Merge a page-level Schema override into the same graph.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param array<string,mixed>            $context Resolved page context.
     * @return array<int,array<string,mixed>>
     */
    private function apply_page_schema_override_to_graph( $graph, $context ) {
        $override = isset( $context['page_schema_override'] ) && is_array( $context['page_schema_override'] ) ? $context['page_schema_override'] : array();
        if ( empty( $override['enabled'] ) || empty( $override['type'] ) ) {
            return $graph;
        }

        $type = (string) $override['type'];
        $data = isset( $override['data'] ) && is_array( $override['data'] ) ? $override['data'] : array();

        if ( 'FAQPage' === $type ) {
            return $this->apply_page_faq_override_to_graph( $graph, $context, $data );
        }

        $node = $this->build_page_schema_override_node( $context, $type, $data );
        if ( empty( $node ) ) {
            return $graph;
        }

        return $this->replace_graph_node_by_id_suffix( $graph, '#primary', $node );
    }

    /**
     * Apply FAQ override to the existing WebPage node.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param array<string,mixed>            $context Resolved page context.
     * @param array<string,mixed>            $data Override data.
     * @return array<int,array<string,mixed>>
     */
    private function apply_page_faq_override_to_graph( $graph, $context, $data ) {
        $faq_items = $this->build_override_faq_questions( $data );
        if ( empty( $faq_items ) ) {
            return $graph;
        }

        $found = false;
        foreach ( $graph as $index => $node ) {
            if ( ! is_array( $node ) || empty( $node['@id'] ) || '#webpage' !== substr( (string) $node['@id'], -8 ) ) {
                continue;
            }
            $types = isset( $node['@type'] ) && is_array( $node['@type'] ) ? $node['@type'] : array( isset( $node['@type'] ) ? $node['@type'] : 'WebPage' );
            $types[] = 'FAQPage';
            $types = array_values( array_unique( array_filter( array_map( 'strval', $types ) ) ) );
            $node['@type'] = count( $types ) > 1 ? $types : $types[0];
            $node['mainEntity'] = $faq_items;
            $graph[ $index ] = $this->filter_node( 'page_schema_override_faq', $node, $context );
            $found = true;
            break;
        }

        if ( $found ) {
            return $graph;
        }

        $webpage = $this->build_webpage_node( $context );
        $webpage['@type'] = array( 'WebPage', 'FAQPage' );
        $webpage['mainEntity'] = $faq_items;
        $graph[] = $this->filter_node( 'page_schema_override_faq', $webpage, $context );

        return $graph;
    }

    /**
     * Build a primary node from a page-level override.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @param string              $type Schema.org type.
     * @param array<string,mixed> $data Override data.
     * @return array<string,mixed>
     */
    private function build_page_schema_override_node( $context, $type, $data ) {
        $allowed = array_keys( self::get_page_schema_type_choices() );
        if ( ! in_array( $type, $allowed, true ) || 'FAQPage' === $type || '' === $type ) {
            return array();
        }

        $page_url = ! empty( $context['canonical_url'] ) ? (string) $context['canonical_url'] : (string) $context['current_url'];
        $url = ! empty( $data['url'] ) ? (string) $data['url'] : $page_url;
        $home_url = isset( $context['home_url'] ) ? (string) $context['home_url'] : home_url( '/' );
        $name = $this->first_override_value( $data, array( 'name', 'headline', 'title' ), isset( $context['title'] ) ? (string) $context['title'] : '' );
        $description = $this->first_override_value( $data, array( 'description', 'review_body' ), isset( $context['description'] ) ? (string) $context['description'] : '' );
        $image = ! empty( $data['image'] ) ? (string) $data['image'] : ( ! empty( $context['image'] ) ? (string) $context['image'] : '' );

        $node = array(
            '@type'       => $type,
            '@id'         => untrailingslashit( $url ) . '#primary',
            'url'         => $url,
            'name'        => $name,
            'description' => $description,
            'isPartOf'    => array(
                '@id' => untrailingslashit( $page_url ) . '#webpage',
            ),
        );

        if ( '' !== $image ) {
            $node['image'] = $image;
        }

        if ( 'Article' === $type ) {
            $node['headline'] = $this->first_override_value( $data, array( 'headline', 'name', 'title' ), $name );
            $node['datePublished'] = $this->first_override_value( $data, array( 'date_published' ), $this->get_context_post_time( $context, 'published' ) );
            $node['dateModified'] = $this->first_override_value( $data, array( 'date_modified' ), $this->get_context_post_time( $context, 'modified' ) );
            $node['author'] = array(
                '@type' => 'Person',
                'name'  => $this->first_override_value( $data, array( 'author_name' ), $this->get_context_author_name( $context ) ),
            );
            $node['publisher'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
            $node['mainEntityOfPage'] = array( '@id' => untrailingslashit( $page_url ) . '#webpage' );
        } elseif ( 'Product' === $type ) {
            $node['brand'] = ! empty( $data['brand'] )
                ? array( '@type' => 'Brand', 'name' => (string) $data['brand'] )
                : array( '@id' => trailingslashit( $home_url ) . '#organization' );
            if ( ! empty( $data['sku'] ) ) {
                $node['sku'] = (string) $data['sku'];
            }
            if ( ! empty( $data['price'] ) ) {
                $node = $this->add_price_to_node( $node, (string) $data['price'] );
                if ( ! empty( $data['currency'] ) && isset( $node['offers'] ) && is_array( $node['offers'] ) ) {
                    $node['offers']['priceCurrency'] = (string) $data['currency'];
                }
            }
            if ( ! empty( $data['rating_value'] ) ) {
                $node['aggregateRating'] = array(
                    '@type'       => 'AggregateRating',
                    'ratingValue' => (float) $data['rating_value'],
                    'reviewCount' => ! empty( $data['rating_count'] ) ? absint( $data['rating_count'] ) : 1,
                );
            }
        } elseif ( 'Course' === $type ) {
            $node['provider'] = ! empty( $data['course_provider'] )
                ? array( '@type' => 'Organization', 'name' => (string) $data['course_provider'] )
                : array( '@id' => trailingslashit( $home_url ) . '#organization' );
        } elseif ( 'Event' === $type ) {
            $node['startDate'] = isset( $data['start_date'] ) ? (string) $data['start_date'] : '';
            $node['endDate'] = isset( $data['end_date'] ) ? (string) $data['end_date'] : '';
            $node['location'] = $this->build_override_place_node( $data );
            $node['organizer'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
            if ( ! empty( $data['event_status'] ) ) {
                $node['eventStatus'] = (string) $data['event_status'];
            }
            if ( ! empty( $data['event_attendance_mode'] ) ) {
                $node['eventAttendanceMode'] = (string) $data['event_attendance_mode'];
            }
        } elseif ( 'Service' === $type ) {
            $node['provider'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
            if ( ! empty( $data['service_area'] ) ) {
                $node['areaServed'] = (string) $data['service_area'];
            }
            if ( ! empty( $data['price'] ) ) {
                $node = $this->add_price_to_node( $node, (string) $data['price'] );
                if ( ! empty( $data['currency'] ) && isset( $node['offers'] ) && is_array( $node['offers'] ) ) {
                    $node['offers']['priceCurrency'] = (string) $data['currency'];
                }
            }
        } elseif ( 'Review' === $type ) {
            $node['reviewBody'] = $description;
            $node['author'] = array(
                '@type' => 'Person',
                'name'  => $this->first_override_value( $data, array( 'author_name' ), __( '客户', 'developer-starter' ) ),
            );
            $node['itemReviewed'] = ! empty( $data['item_name'] )
                ? array( '@type' => 'Thing', 'name' => (string) $data['item_name'] )
                : array( '@id' => trailingslashit( $home_url ) . '#organization' );
            if ( ! empty( $data['rating_value'] ) ) {
                $node['reviewRating'] = array(
                    '@type'       => 'Rating',
                    'ratingValue' => (float) $data['rating_value'],
                    'bestRating'  => 5,
                    'worstRating' => 1,
                );
            }
        } elseif ( 'HowTo' === $type ) {
            $node['step'] = $this->build_override_howto_step_nodes( $data );
        } elseif ( 'JobPosting' === $type ) {
            $node['title'] = $this->first_override_value( $data, array( 'title', 'name', 'headline' ), $name );
            $node['datePosted'] = isset( $data['date_posted'] ) ? (string) $data['date_posted'] : '';
            $node['validThrough'] = isset( $data['valid_through'] ) ? (string) $data['valid_through'] : '';
            $node['employmentType'] = isset( $data['employment_type'] ) ? (string) $data['employment_type'] : '';
            $node['hiringOrganization'] = array(
                '@type' => 'Organization',
                'name'  => $this->first_override_value( $data, array( 'hiring_organization' ), get_bloginfo( 'name' ) ),
            );
            if ( ! empty( $data['job_location'] ) ) {
                $node['jobLocation'] = array(
                    '@type'   => 'Place',
                    'address' => array(
                        '@type'         => 'PostalAddress',
                        'streetAddress' => (string) $data['job_location'],
                    ),
                );
            }
        }

        return $this->filter_node( 'page_schema_override_' . strtolower( $type ), $node, $context );
    }

    /**
     * Replace a graph node by @id suffix or append when not found.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param string                         $suffix ID suffix.
     * @param array<string,mixed>            $replacement Replacement node.
     * @return array<int,array<string,mixed>>
     */
    private function replace_graph_node_by_id_suffix( $graph, $suffix, $replacement ) {
        foreach ( $graph as $index => $node ) {
            if ( ! is_array( $node ) || empty( $node['@id'] ) ) {
                continue;
            }
            $id = (string) $node['@id'];
            if ( '' !== $suffix && substr( $id, -strlen( $suffix ) ) === $suffix ) {
                $graph[ $index ] = $replacement;
                return $graph;
            }
        }

        $graph[] = $replacement;

        return $graph;
    }

    /**
     * Pick the first non-empty override value.
     *
     * @param array<string,mixed> $data Override data.
     * @param array<int,string>   $keys Candidate keys.
     * @param string              $fallback Fallback value.
     * @return string
     */
    private function first_override_value( $data, $keys, $fallback = '' ) {
        foreach ( $keys as $key ) {
            if ( isset( $data[ $key ] ) && ! is_array( $data[ $key ] ) && ! is_object( $data[ $key ] ) && '' !== trim( (string) $data[ $key ] ) ) {
                return trim( wp_strip_all_tags( (string) $data[ $key ] ) );
            }
        }

        return trim( wp_strip_all_tags( (string) $fallback ) );
    }

    /**
     * Resolve post time for an override preview.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @param string              $kind published|modified.
     * @return string
     */
    private function get_context_post_time( $context, $kind ) {
        $post = isset( $context['post'] ) ? $context['post'] : null;
        if ( $post instanceof \WP_Post ) {
            return 'modified' === $kind ? get_post_modified_time( 'c', true, (int) $post->ID ) : get_post_time( 'c', true, (int) $post->ID );
        }

        return gmdate( 'c' );
    }

    /**
     * Resolve author name for an override preview.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return string
     */
    private function get_context_author_name( $context ) {
        $post = isset( $context['post'] ) ? $context['post'] : null;
        if ( $post instanceof \WP_Post ) {
            $name = get_the_author_meta( 'display_name', (int) $post->post_author );
            if ( '' !== trim( (string) $name ) ) {
                return (string) $name;
            }
        }

        return get_bloginfo( 'name' );
    }

    /**
     * Build FAQ Question nodes from override data.
     *
     * @param array<string,mixed> $data Override data.
     * @return array<int,array<string,mixed>>
     */
    private function build_override_faq_questions( $data ) {
        $items = isset( $data['faq_items'] ) && is_array( $data['faq_items'] ) ? $data['faq_items'] : array();
        $out = array();
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $question = isset( $item['question'] ) ? trim( wp_strip_all_tags( (string) $item['question'] ) ) : '';
            $answer = isset( $item['answer'] ) ? trim( wp_strip_all_tags( (string) $item['answer'] ) ) : '';
            if ( '' === $question || '' === $answer ) {
                continue;
            }
            $out[] = array(
                '@type'          => 'Question',
                'name'           => $question,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $answer,
                ),
            );
        }

        return $out;
    }

    /**
     * Build HowToStep nodes from override data.
     *
     * @param array<string,mixed> $data Override data.
     * @return array<int,array<string,mixed>>
     */
    private function build_override_howto_step_nodes( $data ) {
        $items = isset( $data['howto_steps'] ) && is_array( $data['howto_steps'] ) ? $data['howto_steps'] : array();
        $out = array();
        foreach ( $items as $index => $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $text = isset( $item['text'] ) ? trim( wp_strip_all_tags( (string) $item['text'] ) ) : '';
            if ( '' === $text ) {
                continue;
            }
            $out[] = array(
                '@type'    => 'HowToStep',
                'position' => $index + 1,
                'name'     => isset( $item['name'] ) && '' !== trim( (string) $item['name'] ) ? trim( wp_strip_all_tags( (string) $item['name'] ) ) : sprintf( __( '步骤 %d', 'developer-starter' ), $index + 1 ),
                'text'     => $text,
            );
        }

        return $out;
    }

    /**
     * Build a Place node for Event overrides.
     *
     * @param array<string,mixed> $data Override data.
     * @return array<string,mixed>|string
     */
    private function build_override_place_node( $data ) {
        $name = isset( $data['location_name'] ) ? trim( wp_strip_all_tags( (string) $data['location_name'] ) ) : '';
        $address = isset( $data['location_address'] ) ? trim( wp_strip_all_tags( (string) $data['location_address'] ) ) : '';
        if ( '' === $name && '' === $address ) {
            return '';
        }

        $node = array(
            '@type' => 'Place',
            'name'  => '' !== $name ? $name : $address,
        );
        if ( '' !== $address ) {
            $node['address'] = array(
                '@type'         => 'PostalAddress',
                'streetAddress' => $address,
            );
        }

        return $node;
    }

    /**
     * Extract FAQ, Product, Service and other item entities from page modules.
     *
     * @param array<int,array<string,mixed>> $modules Page modules.
     * @return array<string,array<int,array<string,mixed>>>
     */
    private function extract_module_schema_entities( $modules ) {
        $entities = array(
            'faq'      => array(),
            'services' => array(),
            'products' => array(),
            'courses'  => array(),
            'events'   => array(),
            'branches' => array(),
            'people'   => array(),
            'reviews'  => array(),
            'works'    => array(),
        );

        if ( empty( $modules ) || ! is_array( $modules ) ) {
            return $entities;
        }

        foreach ( $modules as $module ) {
            if ( ! is_array( $module ) ) {
                continue;
            }

            $type = isset( $module['type'] ) ? sanitize_key( (string) $module['type'] ) : '';
            $data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array();
            $metadata = $this->get_module_metadata( $type );
            $schema_types = isset( $metadata['schemaTypes'] ) && is_array( $metadata['schemaTypes'] ) ? $metadata['schemaTypes'] : array();
            $content_models = isset( $metadata['contentModels'] ) && is_array( $metadata['contentModels'] ) ? $metadata['contentModels'] : array();

            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'FAQPage', array( 'faq', 'accordion' ) ) ) {
                $entities['faq'] = array_merge( $entities['faq'], $this->extract_faq_entities( $data ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'Service', array( 'service', 'services' ) ) ) {
                $entities['services'] = array_merge( $entities['services'], $this->extract_generic_entities( $data, 'Service', array( 'services_items', 'service_items', 'items', 'cards', 'features', 'plans', 'pricing_items' ) ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'Product', array( 'product', 'products', 'pricing', 'menu', 'room', 'software', 'app' ) ) ) {
                $entities['products'] = array_merge( $entities['products'], $this->extract_generic_entities( $data, 'Product', array( 'items', 'products', 'product_items', 'plans', 'pricing_items', 'rooms', 'menu_items', 'apps', 'software_items' ) ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'Course', array( 'course', 'curriculum' ) ) ) {
                $entities['courses'] = array_merge( $entities['courses'], $this->extract_generic_entities( $data, 'Course', array( 'items', 'courses', 'course_items', 'curriculum_items', 'plans' ) ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'Event', array( 'event', 'activity', 'campaign', 'timeline', 'itinerary', 'ticket' ) ) ) {
                $entities['events'] = array_merge( $entities['events'], $this->extract_generic_entities( $data, 'Event', array( 'items', 'events', 'event_items', 'activity_items', 'campaign_items', 'timeline_items', 'itinerary_items' ) ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'LocalBusiness', array( 'branch', 'branches', 'contact', 'location', 'map' ) ) ) {
                $entities['branches'] = array_merge( $entities['branches'], $this->extract_local_business_entities( $data ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'Person', array( 'team', 'author', 'expert', 'coach', 'doctor' ) ) ) {
                $entities['people'] = array_merge( $entities['people'], $this->extract_generic_entities( $data, 'Person', array( 'items', 'members', 'team_items', 'authors', 'people', 'experts' ) ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'Review', array( 'testimonial', 'review', 'reader_wall' ) ) ) {
                $entities['reviews'] = array_merge( $entities['reviews'], $this->extract_review_entities( $data ) );
            }
            if ( $this->module_supports_schema( $type, $schema_types, $content_models, 'CreativeWork', array( 'case', 'cases', 'work', 'resource', 'download', 'media' ) ) ) {
                $entities['works'] = array_merge( $entities['works'], $this->extract_generic_entities( $data, 'CreativeWork', array( 'items', 'cases', 'case_items', 'works', 'resources', 'downloads', 'media_items', 'posts' ) ) );
            }
        }

        foreach ( $entities as $key => $items ) {
            $entities[ $key ] = array_slice( $this->dedupe_entities( $items ), 0, 24 );
        }

        return $entities;
    }

    /**
     * Extract FAQ question nodes.
     *
     * @param array<string,mixed> $data Module data.
     * @return array<int,array<string,mixed>>
     */
    private function extract_faq_entities( $data ) {
        $items = $this->collect_repeater_items( $data, array( 'faq_items', 'items', 'questions', 'qa_items', 'accordion_items' ) );
        $out = array();

        foreach ( $items as $item ) {
            $question = $this->first_text( $item, array( 'question', 'title', 'q', 'name' ) );
            $answer = $this->first_text( $item, array( 'answer', 'content', 'desc', 'description', 'a', 'text' ) );
            if ( '' === $question || '' === $answer ) {
                continue;
            }

            $out[] = array(
                '@type'          => 'Question',
                'name'           => $question,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $answer,
                ),
            );
        }

        return $out;
    }

    /**
     * Extract generic item nodes.
     *
     * @param array<string,mixed> $data Module data.
     * @param string              $type Schema.org type.
     * @param array<int,string>   $candidate_keys Candidate repeater keys.
     * @return array<int,array<string,mixed>>
     */
    private function extract_generic_entities( $data, $type, $candidate_keys ) {
        $items = $this->collect_repeater_items( $data, $candidate_keys );
        $out = array();

        foreach ( $items as $item ) {
            $name = $this->first_text( $item, array( 'name', 'title', 'label', 'service_title', 'product_title', 'course_title', 'event_title', 'plan_name' ) );
            $description = $this->first_text( $item, array( 'description', 'desc', 'summary', 'content', 'text', 'intro', 'quote', 'subtitle' ) );
            if ( '' === $name && '' === $description ) {
                continue;
            }

            $node = array(
                '@type'       => $type,
                'name'        => '' !== $name ? $name : wp_trim_words( $description, 10, '' ),
                'description' => $description,
            );

            $url = $this->first_url( $item, array( 'url', 'link', 'href', 'external_url', 'btn_url', 'button_url' ) );
            if ( '' !== $url ) {
                $node['url'] = $url;
            }

            $image = $this->first_image_url( $item, array( 'image', 'thumb', 'thumbnail', 'cover', 'logo', 'avatar', 'icon' ) );
            if ( '' !== $image ) {
                $node['image'] = $image;
            }

            $price = $this->first_text( $item, array( 'price', 'amount', 'fee', 'plan_price' ) );
            if ( '' !== $price ) {
                $node = $this->add_price_to_node( $node, $price );
            }

            if ( 'Course' === $type ) {
                $node['provider'] = array( '@id' => trailingslashit( $this->get_home_url() ) . '#organization' );
                $node['timeRequired'] = $this->first_text( $item, array( 'duration', 'time', 'period' ) );
                $node['educationalLevel'] = $this->first_text( $item, array( 'level', 'difficulty' ) );
            } elseif ( 'Event' === $type ) {
                $node['startDate'] = $this->first_text( $item, array( 'start_date', 'date', 'startDate' ) );
                $node['endDate'] = $this->first_text( $item, array( 'end_date', 'endDate' ) );
                $node['location'] = $this->first_text( $item, array( 'location', 'address', 'venue' ) );
                $node['organizer'] = array( '@id' => trailingslashit( $this->get_home_url() ) . '#organization' );
            } elseif ( 'Service' === $type ) {
                $node['provider'] = array( '@id' => trailingslashit( $this->get_home_url() ) . '#organization' );
            } elseif ( 'Person' === $type ) {
                $node['jobTitle'] = $this->first_text( $item, array( 'position', 'title_text', 'job_title', 'role' ) );
                $node['email'] = $this->first_text( $item, array( 'email' ) );
                $node['telephone'] = $this->first_text( $item, array( 'phone', 'telephone' ) );
            }

            $out[] = $node;
        }

        return $out;
    }

    /**
     * Extract LocalBusiness nodes.
     *
     * @param array<string,mixed> $data Module data.
     * @return array<int,array<string,mixed>>
     */
    private function extract_local_business_entities( $data ) {
        $items = $this->collect_repeater_items( $data, array( 'branches', 'branch_items', 'locations', 'items', 'stores' ) );
        if ( empty( $items ) ) {
            $items = array( $data );
        }

        $out = array();
        foreach ( $items as $item ) {
            $name = $this->first_text( $item, array( 'name', 'title', 'branch_name', 'store_name' ) );
            $address = $this->first_text( $item, array( 'address', 'location', 'streetAddress' ) );
            $phone = $this->first_text( $item, array( 'phone', 'telephone', 'tel' ) );
            $email = $this->first_text( $item, array( 'email' ) );

            if ( '' === $name && '' === $address && '' === $phone && '' === $email ) {
                continue;
            }

            $node = array(
                '@type'       => 'LocalBusiness',
                'name'        => '' !== $name ? $name : get_bloginfo( 'name' ),
                'description' => $this->first_text( $item, array( 'desc', 'description', 'summary' ) ),
                'telephone'   => $phone,
                'email'       => $email,
            );

            if ( '' !== $address ) {
                $node['address'] = array(
                    '@type'         => 'PostalAddress',
                    'streetAddress' => $address,
                );
            }

            $url = $this->first_url( $item, array( 'url', 'link', 'external_url' ) );
            if ( '' !== $url ) {
                $node['url'] = $url;
            }

            $out[] = $node;
        }

        return $out;
    }

    /**
     * Extract Review nodes.
     *
     * @param array<string,mixed> $data Module data.
     * @return array<int,array<string,mixed>>
     */
    private function extract_review_entities( $data ) {
        $items = $this->collect_repeater_items( $data, array( 'items', 'testimonials', 'reviews', 'reader_items' ) );
        $out = array();

        foreach ( $items as $item ) {
            $body = $this->first_text( $item, array( 'content', 'quote', 'desc', 'description', 'text' ) );
            $author = $this->first_text( $item, array( 'author', 'author_name', 'name', 'customer', 'user_name' ) );
            if ( '' === $body && '' === $author ) {
                continue;
            }

            $node = array(
                '@type'       => 'Review',
                'reviewBody'  => $body,
                'author'      => array(
                    '@type' => 'Person',
                    'name'  => '' !== $author ? $author : __( '客户', 'developer-starter' ),
                ),
                'itemReviewed' => array(
                    '@id' => trailingslashit( $this->get_home_url() ) . '#organization',
                ),
            );

            $rating = $this->first_text( $item, array( 'rating', 'score', 'stars' ) );
            if ( '' !== $rating && is_numeric( $rating ) ) {
                $node['reviewRating'] = array(
                    '@type'       => 'Rating',
                    'ratingValue' => (float) $rating,
                    'bestRating'  => 5,
                    'worstRating' => 1,
                );
            }

            $out[] = $node;
        }

        return $out;
    }

    /**
     * Enrich custom model nodes with model meta.
     *
     * @param array<string,mixed> $node Base node.
     * @param int                 $post_id Post id.
     * @param string              $type Schema.org type.
     * @param array<string,mixed> $context Resolved page context.
     * @return array<string,mixed>
     */
    private function enrich_model_node( $node, $post_id, $type, $context ) {
        $home_url = isset( $context['home_url'] ) ? (string) $context['home_url'] : home_url( '/' );
        $summary = $this->get_model_meta( $post_id, 'summary' );
        if ( '' !== $summary && empty( $node['description'] ) ) {
            $node['description'] = $summary;
        }

        if ( in_array( $type, array( 'Product', 'Service', 'Course', 'HotelRoom', 'MenuItem', 'SoftwareApplication' ), true ) ) {
            $price = $this->get_model_meta( $post_id, 'price' );
            if ( '' !== $price ) {
                $node = $this->add_price_to_node( $node, $price );
            }
        }

        if ( 'Service' === $type ) {
            $node['provider'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
        } elseif ( 'Product' === $type || 'MenuItem' === $type || 'HotelRoom' === $type ) {
            $node['brand'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
        } elseif ( 'Course' === $type ) {
            $node['provider'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
            $node['timeRequired'] = $this->get_model_meta( $post_id, 'duration' );
            $node['educationalLevel'] = $this->get_model_meta( $post_id, 'level' );
        } elseif ( 'Event' === $type ) {
            $node['startDate'] = $this->get_model_meta( $post_id, 'start_date' );
            $node['endDate'] = $this->get_model_meta( $post_id, 'end_date' );
            $node['location'] = $this->get_model_meta( $post_id, 'location' );
            $node['organizer'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
        } elseif ( 'LocalBusiness' === $type ) {
            $address = $this->get_model_meta( $post_id, 'address' );
            if ( '' !== $address ) {
                $node['address'] = array(
                    '@type'         => 'PostalAddress',
                    'streetAddress' => $address,
                );
            }
            $node['telephone'] = $this->get_model_meta( $post_id, 'phone' );
            $node['email'] = $this->get_model_meta( $post_id, 'email' );
        } elseif ( 'Person' === $type ) {
            $node['jobTitle'] = $this->get_model_meta( $post_id, 'position' );
            $node['worksFor'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
            $node['email'] = $this->get_model_meta( $post_id, 'email' );
            $node['telephone'] = $this->get_model_meta( $post_id, 'phone' );
        } elseif ( 'Review' === $type ) {
            $rating = $this->get_model_meta( $post_id, 'rating' );
            $author = $this->get_model_meta( $post_id, 'author_name' );
            if ( '' !== $author ) {
                $node['author'] = array(
                    '@type' => 'Person',
                    'name'  => $author,
                );
            }
            if ( '' !== $rating && is_numeric( $rating ) ) {
                $node['reviewRating'] = array(
                    '@type'       => 'Rating',
                    'ratingValue' => (float) $rating,
                    'bestRating'  => 5,
                    'worstRating' => 1,
                );
            }
            $node['itemReviewed'] = array( '@id' => trailingslashit( $home_url ) . '#organization' );
        }

        $external_url = $this->get_model_meta( $post_id, 'external_url' );
        if ( '' !== $external_url ) {
            $node['sameAs'] = $external_url;
        }

        return $node;
    }

    /**
     * Add price data without inventing unavailable offers.
     *
     * @param array<string,mixed> $node Schema node.
     * @param string              $price Price string.
     * @return array<string,mixed>
     */
    private function add_price_to_node( $node, $price ) {
        $price = trim( wp_strip_all_tags( (string) $price ) );
        if ( '' === $price ) {
            return $node;
        }

        if ( preg_match( '/\d+(?:\.\d+)?/', $price, $matches ) ) {
            $node['offers'] = array(
                '@type'         => 'Offer',
                'price'         => $matches[0],
                'priceCurrency' => $this->get_default_currency(),
                'availability'  => 'https://schema.org/InStock',
            );
        } else {
            $node['additionalProperty'][] = array(
                '@type' => 'PropertyValue',
                'name'  => __( '价格/报价', 'developer-starter' ),
                'value' => $price,
            );
        }

        return $node;
    }

    /**
     * Resolve context post id.
     *
     * @param int $post_id Optional post id.
     * @return int
     */
    private function resolve_context_post_id( $post_id = 0 ) {
        $post_id = absint( $post_id );
        if ( $post_id > 0 ) {
            return $post_id;
        }

        if ( is_singular() ) {
            return absint( get_the_ID() );
        }

        if ( is_front_page() ) {
            return absint( get_option( 'page_on_front' ) );
        }

        if ( is_home() ) {
            return absint( get_option( 'page_for_posts' ) );
        }

        return 0;
    }

    /**
     * Resolve industry type from options and context.
     *
     * @param array<string,mixed> $options Theme options.
     * @param int                 $post_id Post id.
     * @param array<int,array<string,mixed>> $modules Page modules.
     * @param array<string,mixed> $content_model Content model.
     * @return string
     */
    private function resolve_industry_type( $options, $post_id, $modules, $content_model ) {
        $configured = isset( $options[ self::OPTION_INDUSTRY_TYPE ] ) ? sanitize_key( (string) $options[ self::OPTION_INDUSTRY_TYPE ] ) : 'auto';
        if ( 'auto' !== $configured && array_key_exists( $configured, self::get_industry_choices() ) ) {
            return $configured;
        }

        $model_id = isset( $content_model['id'] ) ? sanitize_key( (string) $content_model['id'] ) : '';
        $model_map = array(
            'product'   => 'ecommerce',
            'service'   => 'local_service',
            'branch'    => 'local_service',
            'course'    => 'education',
            'event'     => 'event',
            'room'      => 'hospitality',
            'menu_item' => 'restaurant',
            'software'  => 'software',
            'post'      => 'publisher',
            'resource'  => 'publisher',
            'media_item' => 'publisher',
        );
        if ( isset( $model_map[ $model_id ] ) ) {
            return $model_map[ $model_id ];
        }

        $haystack = '';
        if ( $post_id > 0 ) {
            $template = get_page_template_slug( $post_id );
            $haystack .= ' ' . $template;
        }
        foreach ( $modules as $module ) {
            if ( is_array( $module ) && ! empty( $module['type'] ) ) {
                $haystack .= ' ' . sanitize_key( (string) $module['type'] );
                $metadata = $this->get_module_metadata( (string) $module['type'] );
                if ( ! empty( $metadata['industryTags'] ) && is_array( $metadata['industryTags'] ) ) {
                    $haystack .= ' ' . implode( ' ', array_map( 'sanitize_key', $metadata['industryTags'] ) );
                }
            }
        }
        $haystack = strtolower( $haystack );

        $keyword_map = array(
            'restaurant'    => array( 'restaurant', 'menu', 'dining', 'food' ),
            'medical'       => array( 'medical', 'clinic', 'dental', 'dentist', 'healthcare', 'wellness', 'beauty', 'gym', 'fitness', 'yoga' ),
            'education'     => array( 'education', 'course', 'curriculum', 'school', 'training' ),
            'hospitality'   => array( 'hotel', 'homestay', 'hospitality', 'travel', 'room' ),
            'real_estate'   => array( 'real_estate', 'real-estate', 'property', 'renovation', 'construction' ),
            'software'      => array( 'software', 'saas', 'app', 'developer', 'platform', 'cybersecurity' ),
            'ecommerce'     => array( 'ecommerce', 'shop', 'product', 'pricing', 'commerce' ),
            'publisher'     => array( 'blog', 'magazine', 'media', 'news', 'resource' ),
            'event'         => array( 'event', 'ticket', 'itinerary' ),
            'manufacturing' => array( 'manufacturing', 'factory', 'b2b' ),
            'logistics'     => array( 'logistics', 'supply_chain', 'supply-chain', 'warehouse', 'shipping', 'delivery' ),
            'agriculture_food' => array( 'agriculture_food', 'agriculture', 'farm', 'food', 'traceability' ),
            'energy_environment' => array( 'energy_environment', 'energy', 'environment', 'environmental', 'carbon', 'green' ),
            'industrial_park' => array( 'industrial_park', 'industrial-park', 'business_park', 'park', 'incubator' ),
            'human_resources' => array( 'human_resources', 'human-resources', 'recruitment', 'recruiting', 'talent' ),
            'property_management' => array( 'property_management', 'property-management', 'community_service', 'property-service' ),
            'nonprofit'     => array( 'nonprofit', 'ngo', 'foundation', 'charity', 'volunteer' ),
            'government'    => array( 'government', 'public_service', 'public-service' ),
            'local_service' => array( 'local_service', 'service', 'branch', 'contact', 'booking', 'automotive', 'pet', 'law' ),
        );

        foreach ( $keyword_map as $industry => $needles ) {
            foreach ( $needles as $needle ) {
                if ( false !== strpos( $haystack, $needle ) ) {
                    return $industry;
                }
            }
        }

        return ( is_home() || is_archive() || is_singular( 'post' ) ) ? 'publisher' : 'corporate';
    }

    /**
     * Resolve content model from post type.
     *
     * @param string $post_type Post type.
     * @return array<string,mixed>
     */
    private function resolve_content_model_for_post_type( $post_type ) {
        $post_type = sanitize_key( (string) $post_type );
        if ( '' === $post_type ) {
            return array();
        }

        if ( class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            $center = \Developer_Starter\Core\Content_Model_Center::get_instance();
            foreach ( $center->get_model_definitions() as $definition ) {
                if ( ! is_array( $definition ) ) {
                    continue;
                }
                $normalized = $this->normalize_content_model_definition( $definition );
                if ( isset( $normalized['postType'] ) && $post_type === $normalized['postType'] ) {
                    return $normalized;
                }
            }
        }

        if ( 'post' === $post_type ) {
            return array(
                'id'          => 'post',
                'postType'    => 'post',
                'schemaTypes' => array( 'Article', 'BlogPosting' ),
            );
        }
        if ( 'page' === $post_type ) {
            return array(
                'id'          => 'page',
                'postType'    => 'page',
                'schemaTypes' => array( 'WebPage' ),
            );
        }

        return array();
    }

    /**
     * Normalize content model definition keys.
     *
     * @param array<string,mixed> $definition Raw definition.
     * @return array<string,mixed>
     */
    private function normalize_content_model_definition( $definition ) {
        return array(
            'id'          => isset( $definition['id'] ) ? sanitize_key( (string) $definition['id'] ) : '',
            'postType'    => isset( $definition['postType'] ) ? sanitize_key( (string) $definition['postType'] ) : ( isset( $definition['post_type'] ) ? sanitize_key( (string) $definition['post_type'] ) : '' ),
            'schemaTypes' => isset( $definition['schemaTypes'] ) && is_array( $definition['schemaTypes'] )
                ? array_values( array_map( 'strval', $definition['schemaTypes'] ) )
                : ( isset( $definition['schema_types'] ) && is_array( $definition['schema_types'] ) ? array_values( array_map( 'strval', $definition['schema_types'] ) ) : array() ),
        );
    }

    /**
     * Resolve primary Schema.org type for post.
     *
     * @param \WP_Post          $post Post object.
     * @param array<int,string> $schema_types Schema type hints.
     * @return string
     */
    private function resolve_primary_schema_type_for_post( $post, $schema_types ) {
        if ( 'post' === $post->post_type ) {
            return $this->post_looks_like_news( (int) $post->ID ) ? 'NewsArticle' : 'BlogPosting';
        }

        $allowed = array(
            'Article',
            'BlogPosting',
            'NewsArticle',
            'Service',
            'Product',
            'CreativeWork',
            'Review',
            'Person',
            'LocalBusiness',
            'Organization',
            'DigitalDocument',
            'Course',
            'Event',
            'HotelRoom',
            'MenuItem',
            'SoftwareApplication',
            'MediaObject',
            'JobPosting',
        );

        foreach ( $schema_types as $type ) {
            $type = (string) $type;
            if ( in_array( $type, $allowed, true ) ) {
                return $type;
            }
        }

        return 'page' === $post->post_type ? 'WebPage' : 'CreativeWork';
    }

    /**
     * Get webpage schema types for current request.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @param array<int,array<string,mixed>> $faq_items FAQ entities.
     * @return array<int,string>
     */
    private function get_webpage_types( $context, $faq_items ) {
        $types = array();
        if ( ! empty( $context['is_search'] ) ) {
            $types[] = 'SearchResultsPage';
        } elseif ( ! empty( $context['is_archive'] ) || ! empty( $context['is_home'] ) ) {
            $types[] = 'CollectionPage';
        } else {
            $types[] = 'WebPage';
        }

        if ( ! empty( $faq_items ) ) {
            $types[] = 'FAQPage';
        }

        $title = isset( $context['title'] ) ? strtolower( (string) $context['title'] ) : '';
        $url = isset( $context['current_url'] ) ? strtolower( (string) $context['current_url'] ) : '';
        if ( false !== strpos( $title . ' ' . $url, 'contact' ) || false !== strpos( $title, '联系' ) ) {
            $types[] = 'ContactPage';
        }

        return array_values( array_unique( $types ) );
    }

    /**
     * Build breadcrumb items.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<int,array<string,mixed>>
     */
    private function build_breadcrumb_items( $context ) {
        if ( ! empty( $context['is_front_page'] ) ) {
            return array();
        }

        $home_url = isset( $context['home_url'] ) ? (string) $context['home_url'] : home_url( '/' );
        $items = array(
            array(
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => __( '首页', 'developer-starter' ),
                'item'     => $home_url,
            ),
        );

        $position = 2;
        if ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term instanceof \WP_Term ) {
                $term_link = get_term_link( $term );
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $term->name,
                    'item'     => is_string( $term_link ) && ! is_wp_error( $term_link ) ? $term_link : '',
                );
            }
        } elseif ( is_singular( 'post' ) ) {
            $categories = get_the_category();
            if ( ! empty( $categories ) ) {
                $category = $categories[0];
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $category->name,
                    'item'     => get_category_link( $category->term_id ),
                );
            }
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => isset( $context['title'] ) ? (string) $context['title'] : get_the_title(),
                'item'     => isset( $context['canonical_url'] ) ? (string) $context['canonical_url'] : get_permalink(),
            );
        } elseif ( ! empty( $context['post_id'] ) && is_page( (int) $context['post_id'] ) ) {
            $ancestors = array_reverse( get_post_ancestors( (int) $context['post_id'] ) );
            foreach ( $ancestors as $ancestor_id ) {
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => get_the_title( $ancestor_id ),
                    'item'     => get_permalink( $ancestor_id ),
                );
            }
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => isset( $context['title'] ) ? (string) $context['title'] : get_the_title( (int) $context['post_id'] ),
                'item'     => isset( $context['canonical_url'] ) ? (string) $context['canonical_url'] : get_permalink( (int) $context['post_id'] ),
            );
        } elseif ( ! empty( $context['title'] ) ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => (string) $context['title'],
                'item'     => isset( $context['canonical_url'] ) ? (string) $context['canonical_url'] : (string) $context['current_url'],
            );
        }

        return $items;
    }

    /**
     * Whether a module supports a schema type or keyword.
     *
     * @param string            $type Module id.
     * @param array<int,string> $schema_types Manifest schema types.
     * @param array<int,string> $content_models Manifest content model ids.
     * @param string            $schema_type Target schema type.
     * @param array<int,string> $needles Type keywords.
     * @return bool
     */
    private function module_supports_schema( $type, $schema_types, $content_models, $schema_type, $needles ) {
        if ( in_array( $schema_type, $schema_types, true ) ) {
            return true;
        }

        $model_map = array(
            'FAQPage'       => 'faq',
            'Service'       => 'service',
            'Product'       => 'product',
            'Course'        => 'course',
            'Event'         => 'event',
            'LocalBusiness' => 'branch',
            'Person'        => 'team',
            'Review'        => 'testimonial',
            'CreativeWork'  => 'case',
        );
        if ( isset( $model_map[ $schema_type ] ) && in_array( $model_map[ $schema_type ], $content_models, true ) ) {
            return true;
        }

        $haystack = strtolower( (string) $type );
        foreach ( $needles as $needle ) {
            if ( false !== strpos( $haystack, strtolower( (string) $needle ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get module metadata.
     *
     * @param string $module_id Module id.
     * @return array<string,mixed>
     */
    private function get_module_metadata( $module_id ) {
        $module_id = sanitize_key( (string) $module_id );
        if ( '' === $module_id || ! class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            return array();
        }

        $manifest = \Developer_Starter\Modules\Module_Manager::get_instance()->get_module_manifest_item( $module_id );

        return is_array( $manifest ) ? $manifest : array();
    }

    /**
     * Collect repeater-like lists from module data.
     *
     * @param array<string,mixed> $data Module data.
     * @param array<int,string>   $candidate_keys Candidate keys.
     * @return array<int,array<string,mixed>>
     */
    private function collect_repeater_items( $data, $candidate_keys ) {
        if ( ! is_array( $data ) ) {
            return array();
        }

        foreach ( $candidate_keys as $key ) {
            if ( isset( $data[ $key ] ) && is_array( $data[ $key ] ) ) {
                $items = $this->normalize_repeater_list( $data[ $key ] );
                if ( ! empty( $items ) ) {
                    return $items;
                }
            }
        }

        $fallback = array();
        foreach ( $data as $value ) {
            if ( is_array( $value ) ) {
                $items = $this->normalize_repeater_list( $value );
                if ( count( $items ) >= 2 ) {
                    $fallback = $items;
                    break;
                }
            }
        }

        return $fallback;
    }

    /**
     * Normalize list arrays.
     *
     * @param array<mixed> $list Raw list.
     * @return array<int,array<string,mixed>>
     */
    private function normalize_repeater_list( $list ) {
        $items = array();
        foreach ( $list as $item ) {
            if ( is_array( $item ) && ! empty( $item ) ) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * First non-empty text value by keys.
     *
     * @param array<string,mixed> $data Source data.
     * @param array<int,string>   $keys Candidate keys.
     * @return string
     */
    private function first_text( $data, $keys ) {
        if ( ! is_array( $data ) ) {
            return '';
        }

        foreach ( $keys as $key ) {
            if ( ! isset( $data[ $key ] ) || is_array( $data[ $key ] ) || is_object( $data[ $key ] ) ) {
                continue;
            }

            $value = trim( wp_strip_all_tags( (string) $data[ $key ] ) );
            if ( '' !== $value && '#' !== $value ) {
                return $value;
            }
        }

        return '';
    }

    /**
     * First usable URL by keys.
     *
     * @param array<string,mixed> $data Source data.
     * @param array<int,string>   $keys Candidate keys.
     * @return string
     */
    private function first_url( $data, $keys ) {
        foreach ( $keys as $key ) {
            if ( empty( $data[ $key ] ) || is_array( $data[ $key ] ) || is_object( $data[ $key ] ) ) {
                continue;
            }
            $url = $this->normalize_url( (string) $data[ $key ] );
            if ( '' !== $url ) {
                return $url;
            }
        }

        return '';
    }

    /**
     * First usable image URL by keys.
     *
     * @param array<string,mixed> $data Source data.
     * @param array<int,string>   $keys Candidate keys.
     * @return string
     */
    private function first_image_url( $data, $keys ) {
        foreach ( $keys as $key ) {
            if ( empty( $data[ $key ] ) || is_array( $data[ $key ] ) || is_object( $data[ $key ] ) ) {
                continue;
            }
            $url = $this->normalize_image_url( $data[ $key ] );
            if ( '' !== $url ) {
                return $url;
            }
        }

        return '';
    }

    /**
     * Normalize URL.
     *
     * @param string $url Raw URL.
     * @return string
     */
    private function normalize_url( $url ) {
        $url = trim( (string) $url );
        if ( '' === $url || '#' === $url || 0 === strpos( $url, 'javascript:' ) ) {
            return '';
        }

        if ( 0 === strpos( $url, '/' ) ) {
            return esc_url_raw( home_url( $url ) );
        }

        return esc_url_raw( $url );
    }

    /**
     * Normalize image URL.
     *
     * @param mixed $value Raw image value.
     * @return string
     */
    private function normalize_image_url( $value ) {
        if ( is_numeric( $value ) ) {
            $image = wp_get_attachment_image_url( absint( $value ), 'full' );
            return is_string( $image ) ? esc_url_raw( $image ) : '';
        }

        $value = trim( (string) $value );
        if ( '' === $value || '#' === $value ) {
            return '';
        }

        if ( function_exists( 'developer_starter_normalize_asset_url' ) ) {
            return esc_url_raw( developer_starter_normalize_asset_url( $value ) );
        }

        return $this->normalize_url( $value );
    }

    /**
     * Deduplicate entities by type/name/url.
     *
     * @param array<int,array<string,mixed>> $items Entities.
     * @return array<int,array<string,mixed>>
     */
    private function dedupe_entities( $items ) {
        $out = array();
        $seen = array();

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $name = isset( $item['name'] ) ? (string) $item['name'] : wp_json_encode( $item );
            $type = isset( $item['@type'] ) ? ( is_array( $item['@type'] ) ? implode( ',', $item['@type'] ) : (string) $item['@type'] ) : '';
            $url = isset( $item['url'] ) ? (string) $item['url'] : '';
            $key = md5( $type . '|' . $name . '|' . $url );
            if ( isset( $seen[ $key ] ) ) {
                continue;
            }
            $seen[ $key ] = true;
            $out[] = $item;
        }

        return $out;
    }

    /**
     * Get page modules.
     *
     * @param int $post_id Post id.
     * @return array<int,array<string,mixed>>
     */
    private function get_page_modules( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
            return array();
        }

        $modules = function_exists( 'developer_starter_get_page_modules_data' )
            ? developer_starter_get_page_modules_data( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );

        return is_array( $modules ) ? $modules : array();
    }

    /**
     * Get current request title.
     *
     * @param int $post_id Post id.
     * @return string
     */
    private function get_context_title( $post_id ) {
        if ( $post_id > 0 ) {
            $seo_title = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( $post_id, '_developer_starter_seo_title', '' )
                : get_post_meta( $post_id, '_developer_starter_seo_title', true );
            if ( ! empty( $seo_title ) ) {
                return (string) $seo_title;
            }

            return function_exists( 'developer_starter_get_translated_post_title' )
                ? (string) developer_starter_get_translated_post_title( $post_id )
                : (string) get_the_title( $post_id );
        }

        if ( is_category() || is_tag() || is_tax() ) {
            return single_term_title( '', false );
        }

        if ( is_search() ) {
            return sprintf( __( '搜索：%s', 'developer-starter' ), get_search_query() );
        }

        if ( is_post_type_archive() ) {
            return post_type_archive_title( '', false );
        }

        return get_bloginfo( 'name' );
    }

    /**
     * Get current request description.
     *
     * @param int $post_id Post id.
     * @return string
     */
    private function get_context_description( $post_id ) {
        if ( $post_id > 0 ) {
            $seo_desc = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( $post_id, '_developer_starter_seo_description', '' )
                : get_post_meta( $post_id, '_developer_starter_seo_description', true );
            if ( ! empty( $seo_desc ) ) {
                return (string) $seo_desc;
            }

            $excerpt = function_exists( 'developer_starter_get_translated_post_excerpt' )
                ? developer_starter_get_translated_post_excerpt( $post_id )
                : get_the_excerpt( $post_id );
            if ( ! empty( $excerpt ) ) {
                return wp_trim_words( wp_strip_all_tags( $excerpt ), 32 );
            }

            $post = get_post( $post_id );
            if ( $post instanceof \WP_Post && '' !== trim( $post->post_content ) ) {
                return wp_trim_words( wp_strip_all_tags( $post->post_content ), 32 );
            }
        }

        if ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term instanceof \WP_Term ) {
                $desc = term_description( $term->term_id, $term->taxonomy );
                if ( '' !== trim( wp_strip_all_tags( $desc ) ) ) {
                    return trim( wp_strip_all_tags( $desc ) );
                }
            }
        }

        $default_desc = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( 'default_description', '' )
            : '';

        return '' !== trim( (string) $default_desc ) ? (string) $default_desc : get_bloginfo( 'description' );
    }

    /**
     * Get primary image for current request.
     *
     * @param int $post_id Post id.
     * @return string
     */
    private function get_context_image( $post_id ) {
        if ( $post_id > 0 ) {
            $og_image = get_post_meta( $post_id, '_developer_starter_og_image', true );
            if ( ! empty( $og_image ) ) {
                return $this->normalize_image_url( $og_image );
            }

            if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                $featured = developer_starter_get_featured_image_url( $post_id, 'full' );
                if ( ! empty( $featured ) ) {
                    return esc_url_raw( $featured );
                }
            }

            if ( has_post_thumbnail( $post_id ) ) {
                $thumbnail = get_the_post_thumbnail_url( $post_id, 'full' );
                if ( $thumbnail ) {
                    return esc_url_raw( $thumbnail );
                }
            }
        }

        return $this->get_schema_logo_url( $this->get_options() );
    }

    /**
     * Get canonical URL for current request.
     *
     * @param int $post_id Post id.
     * @return string
     */
    private function get_canonical_url( $post_id ) {
        if ( function_exists( 'xb_aifanyi_get_frontend_canonical_url' ) ) {
            $plugin_canonical = xb_aifanyi_get_frontend_canonical_url();
            if ( ! empty( $plugin_canonical ) ) {
                return esc_url_raw( $plugin_canonical );
            }
        }

        if ( $post_id > 0 ) {
            $canonical = function_exists( 'developer_starter_get_translated_post_meta_value' )
                ? developer_starter_get_translated_post_meta_value( $post_id, '_developer_starter_seo_canonical', '' )
                : get_post_meta( $post_id, '_developer_starter_seo_canonical', true );
            if ( ! empty( $canonical ) ) {
                return esc_url_raw( $canonical );
            }
        }

        return $this->get_current_url( $post_id );
    }

    /**
     * Get current URL.
     *
     * @param int $post_id Post id.
     * @return string
     */
    private function get_current_url( $post_id ) {
        if ( is_front_page() ) {
            return $this->get_home_url();
        }

        if ( $post_id > 0 ) {
            $permalink = get_permalink( $post_id );
            if ( is_string( $permalink ) && '' !== $permalink ) {
                return esc_url_raw( $permalink );
            }
        }

        if ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term instanceof \WP_Term ) {
                $link = get_term_link( $term );
                if ( is_string( $link ) && ! is_wp_error( $link ) ) {
                    return esc_url_raw( $link );
                }
            }
        }

        if ( is_search() ) {
            return esc_url_raw( get_search_link() );
        }

        if ( is_post_type_archive() ) {
            $post_type = get_query_var( 'post_type' );
            $post_type = is_array( $post_type ) ? reset( $post_type ) : $post_type;
            $link = get_post_type_archive_link( (string) $post_type );
            if ( $link ) {
                return esc_url_raw( $link );
            }
        }

        if ( function_exists( 'developer_starter_get_request_path_parts' ) ) {
            $request_parts = developer_starter_get_request_path_parts();
            $path = isset( $request_parts['path'] ) ? (string) $request_parts['path'] : '';
            $query = isset( $request_parts['query'] ) ? (string) $request_parts['query'] : '';
            $url = function_exists( 'developer_starter_build_raw_home_url' )
                ? developer_starter_build_raw_home_url( '' === $path ? '/' : '/' . ltrim( $path, '/' ) )
                : home_url( '' === $path ? '/' : '/' . ltrim( $path, '/' ) );

            if ( '' !== $query ) {
                $url .= '?' . $query;
            }

            return esc_url_raw( $url );
        }

        global $wp;
        $request = isset( $wp->request ) ? (string) $wp->request : '';

        return esc_url_raw( home_url( '' === $request ? '/' : '/' . ltrim( $request, '/' ) ) );
    }

    /**
     * Get front-end home URL.
     *
     * @return string
     */
    private function get_home_url() {
        $home_url = function_exists( 'developer_starter_get_frontend_home_url' )
            ? developer_starter_get_frontend_home_url()
            : home_url( '/' );

        return esc_url_raw( trailingslashit( $home_url ) );
    }

    /**
     * Get logo URL used by schema.
     *
     * @param array<string,mixed> $options Theme options.
     * @return string
     */
    private function get_schema_logo_url( $options ) {
        if ( ! empty( $options['site_logo'] ) ) {
            $logo = $this->normalize_image_url( $options['site_logo'] );
            if ( '' !== $logo ) {
                return $logo;
            }
        }

        if ( has_custom_logo() ) {
            $logo_url = wp_get_attachment_url( get_theme_mod( 'custom_logo' ) );
            if ( is_string( $logo_url ) && '' !== $logo_url ) {
                return esc_url_raw( $logo_url );
            }
        }

        return '';
    }

    /**
     * Build compact node status data for the admin JSON-LD preview.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @return array<string,array<string,mixed>>
     */
    private function get_preview_node_status( $graph ) {
        $organization = $this->find_graph_node_by_id_suffix( $graph, '#organization' );
        $website = $this->find_graph_node_by_id_suffix( $graph, '#website' );
        $breadcrumb_present = $this->graph_has_node_type( $graph, array( 'BreadcrumbList' ) );
        $primary_type = $this->get_preview_primary_type( $graph );

        return array(
            'organization' => array(
                'label'   => 'Organization',
                'present' => ! empty( $organization ),
                'type'    => ! empty( $organization ) ? $this->get_graph_node_type_label( $organization ) : '',
            ),
            'website'      => array(
                'label'   => 'WebSite',
                'present' => ! empty( $website ),
                'type'    => ! empty( $website ) ? $this->get_graph_node_type_label( $website ) : '',
            ),
            'breadcrumb'   => array(
                'label'   => 'Breadcrumb',
                'present' => $breadcrumb_present,
                'type'    => $breadcrumb_present ? 'BreadcrumbList' : '',
            ),
            'primary'      => array(
                'label'   => __( '当前页面主类型', 'developer-starter' ),
                'present' => '' !== $primary_type,
                'type'    => $primary_type,
            ),
        );
    }

    /**
     * Resolve the primary type shown in the preview panel.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @return string
     */
    private function get_preview_primary_type( $graph ) {
        $primary = $this->find_graph_node_by_id_suffix( $graph, '#primary' );
        if ( ! empty( $primary ) ) {
            return $this->get_graph_node_type_label( $primary );
        }

        $webpage = $this->find_graph_node_by_id_suffix( $graph, '#webpage' );
        if ( ! empty( $webpage ) ) {
            return $this->get_graph_node_type_label( $webpage );
        }

        return '';
    }

    /**
     * Get site-level field warnings for Schema preview.
     *
     * @param array<string,mixed>            $context Resolved page context.
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @return array<int,array<string,string>>
     */
    private function get_schema_preview_required_warnings( $context, $graph ) {
        $options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $warnings = array();

        if ( empty( $options['company_name'] ) || '' === trim( (string) $options['company_name'] ) ) {
            $warnings[] = array(
                'field'   => 'company_name',
                'label'   => __( '组织名称', 'developer-starter' ),
                'message' => __( '未填写企业名称，Schema 会回退站点名称；正式上线前建议补齐。', 'developer-starter' ),
            );
        }

        if ( '' === $this->get_schema_logo_url( $options ) ) {
            $warnings[] = array(
                'field'   => 'site_logo',
                'label'   => 'Logo',
                'message' => __( '未检测到网站 Logo 或 WordPress 自定义 Logo，Organization 缺少 logo/image。', 'developer-starter' ),
            );
        }

        $currency = isset( $options[ self::OPTION_DEFAULT_CURRENCY ] ) ? strtoupper( (string) $options[ self::OPTION_DEFAULT_CURRENCY ] ) : 'CNY';
        $currency = (string) preg_replace( '/[^A-Z]/', '', $currency );
        if ( 3 !== strlen( $currency ) ) {
            $warnings[] = array(
                'field'   => self::OPTION_DEFAULT_CURRENCY,
                'label'   => __( '默认币种', 'developer-starter' ),
                'message' => __( '默认币种需要填写三位 ISO 货币代码，例如 CNY、USD、EUR。', 'developer-starter' ),
            );
        }

        $organization = $this->find_graph_node_by_id_suffix( $graph, '#organization' );
        $organization_type = ! empty( $organization ) ? $this->get_graph_node_type_label( $organization ) : '';
        $local_business_types = array( 'LocalBusiness', 'Store', 'Restaurant', 'MedicalOrganization', 'RealEstateAgent', 'LodgingBusiness' );
        if ( in_array( $organization_type, $local_business_types, true ) ) {
            $local_required_fields = array(
                'company_phone'         => __( '电话', 'developer-starter' ),
                'company_email'         => __( '邮箱', 'developer-starter' ),
                'company_address'       => __( '地址', 'developer-starter' ),
                'company_working_hours' => __( '营业时间', 'developer-starter' ),
            );
            foreach ( $local_required_fields as $field => $label ) {
                if ( empty( $options[ $field ] ) || '' === trim( (string) $options[ $field ] ) ) {
                    $warnings[] = array(
                        'field'   => $field,
                        'label'   => $label,
                        'message' => sprintf( __( '%s 类型建议补齐 %s，避免 LocalBusiness 信息不完整。', 'developer-starter' ), $organization_type, $label ),
                    );
                }
            }
        }

        return $warnings;
    }

    /**
     * Build visual diagnostics for the generated graph.
     *
     * @param array<string,mixed>            $context Resolved page context.
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param array<int,array<string,string>> $site_warnings Site-level required warnings.
     * @return array<string,mixed>
     */
    private function build_schema_diagnostics( $context, $graph, $site_warnings = array() ) {
        $missing_required = is_array( $site_warnings ) ? array_values( $site_warnings ) : array();
        $override_warnings = $this->get_page_schema_override_required_warnings( $context );
        $missing_required = array_merge( $missing_required, $override_warnings );
        $conflicts = $this->get_schema_conflict_warnings( $context, $graph );
        $issues = array();

        foreach ( $missing_required as $warning ) {
            if ( ! is_array( $warning ) ) {
                continue;
            }
            $issues[] = array(
                'severity' => isset( $warning['severity'] ) ? (string) $warning['severity'] : 'warning',
                'type'     => 'missing_required',
                'label'    => isset( $warning['label'] ) ? (string) $warning['label'] : __( '必填字段', 'developer-starter' ),
                'message'  => isset( $warning['message'] ) ? (string) $warning['message'] : '',
            );
        }

        foreach ( $conflicts as $conflict ) {
            if ( ! is_array( $conflict ) ) {
                continue;
            }
            $issues[] = array(
                'severity' => 'error',
                'type'     => 'conflict',
                'label'    => isset( $conflict['label'] ) ? (string) $conflict['label'] : __( 'Schema 冲突', 'developer-starter' ),
                'message'  => isset( $conflict['message'] ) ? (string) $conflict['message'] : '',
            );
        }

        return array(
            'status'           => empty( $issues ) ? 'pass' : ( empty( $conflicts ) ? 'warning' : 'error' ),
            'issues'           => $issues,
            'conflicts'        => $conflicts,
            'missing_required' => $missing_required,
            'counts'           => array(
                'Product'      => $this->count_schema_type_in_value( $graph, 'Product' ),
                'Organization' => $this->count_top_level_schema_types( $graph, array( 'Organization', 'LocalBusiness', 'Store', 'Restaurant', 'MedicalOrganization', 'RealEstateAgent', 'LodgingBusiness', 'NewsMediaOrganization', 'EducationalOrganization', 'EmploymentAgency', 'NGO', 'GovernmentOrganization' ) ),
            ),
        );
    }

    /**
     * Get required field warnings for page-level overrides.
     *
     * @param array<string,mixed> $context Resolved page context.
     * @return array<int,array<string,string>>
     */
    private function get_page_schema_override_required_warnings( $context ) {
        $override = isset( $context['page_schema_override'] ) && is_array( $context['page_schema_override'] ) ? $context['page_schema_override'] : array();
        if ( empty( $override['enabled'] ) || empty( $override['type'] ) ) {
            return array();
        }

        $type = (string) $override['type'];
        $data = isset( $override['data'] ) && is_array( $override['data'] ) ? $override['data'] : array();
        $warnings = array();
        $name = $this->first_override_value( $data, array( 'name', 'headline', 'title' ), isset( $context['title'] ) ? (string) $context['title'] : '' );

        if ( '' === $name && 'FAQPage' !== $type ) {
            $warnings[] = $this->build_schema_required_warning( 'schema_override_name', __( '页面主名称', 'developer-starter' ), sprintf( __( '%s 覆盖需要可读的 name/headline/title。', 'developer-starter' ), $type ) );
        }

        if ( 'FAQPage' === $type && empty( $data['faq_items'] ) ) {
            $warnings[] = $this->build_schema_required_warning( 'schema_override_faq_items', 'FAQ', __( 'FAQPage 至少需要一组“问题 | 答案”。', 'developer-starter' ) );
        } elseif ( 'Product' === $type && empty( $data['price'] ) ) {
            $warnings[] = $this->build_schema_required_warning( 'schema_override_price', __( '产品价格', 'developer-starter' ), __( 'Product 建议填写价格，才能生成 Offer.price / priceCurrency。', 'developer-starter' ) );
        } elseif ( 'Article' === $type && '' === $this->first_override_value( $data, array( 'headline', 'name', 'title' ), '' ) ) {
            $warnings[] = $this->build_schema_required_warning( 'schema_override_headline', __( '文章标题', 'developer-starter' ), __( 'Article 至少需要 headline。', 'developer-starter' ) );
        } elseif ( 'Course' === $type && empty( $data['course_provider'] ) ) {
            $warnings[] = $this->build_schema_required_warning( 'schema_override_course_provider', __( '课程提供方', 'developer-starter' ), __( 'Course 建议填写课程提供方；留空时会回退站点组织。', 'developer-starter' ) );
        } elseif ( 'Event' === $type ) {
            if ( empty( $data['start_date'] ) ) {
                $warnings[] = $this->build_schema_required_warning( 'schema_override_start_date', __( '活动开始时间', 'developer-starter' ), __( 'Event 需要 startDate，便于搜索引擎识别活动时间。', 'developer-starter' ) );
            }
            if ( empty( $data['location_name'] ) && empty( $data['location_address'] ) ) {
                $warnings[] = $this->build_schema_required_warning( 'schema_override_location', __( '活动地点', 'developer-starter' ), __( 'Event 需要地点名称或地址。', 'developer-starter' ) );
            }
        } elseif ( 'Review' === $type ) {
            if ( empty( $data['item_name'] ) ) {
                $warnings[] = $this->build_schema_required_warning( 'schema_override_item_reviewed', __( '评价对象', 'developer-starter' ), __( 'Review 需要明确 itemReviewed，避免评价对象不清晰。', 'developer-starter' ) );
            }
            if ( empty( $data['rating_value'] ) ) {
                $warnings[] = $this->build_schema_required_warning( 'schema_override_rating', __( '评分', 'developer-starter' ), __( 'Review 建议填写 1-5 分评分。', 'developer-starter' ) );
            }
        } elseif ( 'HowTo' === $type && empty( $data['howto_steps'] ) ) {
            $warnings[] = $this->build_schema_required_warning( 'schema_override_howto_steps', 'HowTo', __( 'HowTo 至少需要一个步骤。', 'developer-starter' ) );
        } elseif ( 'JobPosting' === $type ) {
            if ( empty( $data['date_posted'] ) ) {
                $warnings[] = $this->build_schema_required_warning( 'schema_override_date_posted', __( '发布日期', 'developer-starter' ), __( 'JobPosting 需要 datePosted。', 'developer-starter' ) );
            }
            if ( empty( $data['hiring_organization'] ) ) {
                $warnings[] = $this->build_schema_required_warning( 'schema_override_hiring_organization', __( '招聘组织', 'developer-starter' ), __( 'JobPosting 需要 hiringOrganization；留空时会回退站点名称。', 'developer-starter' ) );
            }
            if ( empty( $data['job_location'] ) ) {
                $warnings[] = $this->build_schema_required_warning( 'schema_override_job_location', __( '工作地点', 'developer-starter' ), __( 'JobPosting 建议填写 jobLocation。', 'developer-starter' ) );
            }
        }

        return $warnings;
    }

    /**
     * Build one required field warning.
     *
     * @param string $field Field id.
     * @param string $label Field label.
     * @param string $message Message.
     * @return array<string,string>
     */
    private function build_schema_required_warning( $field, $label, $message ) {
        return array(
            'field'    => $field,
            'label'    => $label,
            'message'  => $message,
            'severity' => 'warning',
        );
    }

    /**
     * Detect graph conflicts that should be visible in admin.
     *
     * @param array<string,mixed>            $context Resolved page context.
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @return array<int,array<string,string>>
     */
    private function get_schema_conflict_warnings( $context, $graph ) {
        unset( $context );
        $conflicts = array();
        $product_count = $this->count_top_level_schema_types( $graph, array( 'Product' ) );
        if ( $product_count > 1 ) {
            $conflicts[] = array(
                'code'    => 'multiple_product',
                'label'   => __( '多个顶层 Product', 'developer-starter' ),
                'message' => sprintf( __( '当前 @graph 顶层检测到 %d 个 Product。请只保留一个页面主 Product，产品列表应放入 ItemList 或模块列表。', 'developer-starter' ), $product_count ),
            );
        }

        $organization_count = $this->count_top_level_schema_types( $graph, array( 'Organization', 'LocalBusiness', 'Store', 'Restaurant', 'MedicalOrganization', 'RealEstateAgent', 'LodgingBusiness', 'NewsMediaOrganization', 'EducationalOrganization', 'EmploymentAgency', 'NGO', 'GovernmentOrganization' ) );
        if ( $organization_count > 1 ) {
            $conflicts[] = array(
                'code'    => 'multiple_organization',
                'label'   => __( '多个 Organization', 'developer-starter' ),
                'message' => sprintf( __( '当前 @graph 顶层检测到 %d 个组织实体。建议站点级只保留一个 Organization/LocalBusiness 主体。', 'developer-starter' ), $organization_count ),
            );
        }

        return $conflicts;
    }

    /**
     * Count Schema.org type occurrences recursively.
     *
     * @param mixed  $value Value to inspect.
     * @param string $type Target type.
     * @return int
     */
    private function count_schema_type_in_value( $value, $type ) {
        if ( ! is_array( $value ) ) {
            return 0;
        }

        $count = 0;
        if ( isset( $value['@type'] ) ) {
            $types = is_array( $value['@type'] ) ? $value['@type'] : array( $value['@type'] );
            foreach ( $types as $node_type ) {
                if ( (string) $node_type === $type ) {
                    $count++;
                    break;
                }
            }
        }

        foreach ( $value as $item ) {
            if ( is_array( $item ) ) {
                $count += $this->count_schema_type_in_value( $item, $type );
            }
        }

        return $count;
    }

    /**
     * Count top-level graph nodes matching any target type.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param array<int,string>              $types Target types.
     * @return int
     */
    private function count_top_level_schema_types( $graph, $types ) {
        $count = 0;
        foreach ( $graph as $node ) {
            if ( ! is_array( $node ) || ! isset( $node['@type'] ) ) {
                continue;
            }
            $node_types = is_array( $node['@type'] ) ? $node['@type'] : array( $node['@type'] );
            foreach ( $node_types as $node_type ) {
                if ( in_array( (string) $node_type, $types, true ) ) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * Find a graph node whose @id ends with a known fragment.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param string                         $suffix ID suffix.
     * @return array<string,mixed>
     */
    private function find_graph_node_by_id_suffix( $graph, $suffix ) {
        foreach ( $graph as $node ) {
            if ( ! is_array( $node ) || empty( $node['@id'] ) ) {
                continue;
            }

            $id = (string) $node['@id'];
            if ( '' !== $suffix && substr( $id, -strlen( $suffix ) ) === $suffix ) {
                return $node;
            }
        }

        return array();
    }

    /**
     * Whether the graph contains any of the given Schema.org types.
     *
     * @param array<int,array<string,mixed>> $graph Graph nodes.
     * @param array<int,string>              $types Target types.
     * @return bool
     */
    private function graph_has_node_type( $graph, $types ) {
        foreach ( $graph as $node ) {
            if ( ! is_array( $node ) || ! isset( $node['@type'] ) ) {
                continue;
            }

            $node_types = is_array( $node['@type'] ) ? $node['@type'] : array( $node['@type'] );
            foreach ( $node_types as $type ) {
                if ( in_array( (string) $type, $types, true ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get a readable @type value from a graph node.
     *
     * @param array<string,mixed> $node Graph node.
     * @return string
     */
    private function get_graph_node_type_label( $node ) {
        if ( ! isset( $node['@type'] ) ) {
            return '';
        }

        $types = is_array( $node['@type'] ) ? $node['@type'] : array( $node['@type'] );
        $types = array_values( array_filter( array_map( 'strval', $types ) ) );

        return implode( ', ', $types );
    }

    /**
     * Get default currency for numeric offers.
     *
     * @return string
     */
    private function get_default_currency() {
        $options = $this->get_options();
        $currency = isset( $options[ self::OPTION_DEFAULT_CURRENCY ] ) ? strtoupper( (string) $options[ self::OPTION_DEFAULT_CURRENCY ] ) : 'CNY';
        $currency = (string) preg_replace( '/[^A-Z]/', '', $currency );

        return 3 === strlen( $currency ) ? $currency : 'CNY';
    }

    /**
     * Get all theme options.
     *
     * @param array<string,mixed>|null $options Optional options.
     * @return array<string,mixed>
     */
    private function get_options( $options = null ) {
        if ( is_array( $options ) ) {
            return $options;
        }

        $options = get_option( 'developer_starter_options', array() );

        return is_array( $options ) ? $options : array();
    }

    /**
     * Get organization schema type by industry.
     *
     * @param string $industry Industry type.
     * @return string
     */
    private function get_organization_schema_type( $industry ) {
        $map = array(
            'local_service' => 'LocalBusiness',
            'ecommerce'     => 'Store',
            'publisher'     => 'NewsMediaOrganization',
            'education'     => 'EducationalOrganization',
            'medical'       => 'MedicalOrganization',
            'real_estate'   => 'RealEstateAgent',
            'restaurant'    => 'Restaurant',
            'hospitality'   => 'LodgingBusiness',
            'property_management' => 'LocalBusiness',
            'human_resources' => 'EmploymentAgency',
            'nonprofit'     => 'NGO',
            'government'    => 'GovernmentOrganization',
        );

        return isset( $map[ $industry ] ) ? $map[ $industry ] : 'Organization';
    }

    /**
     * Whether a post looks like news content.
     *
     * @param int $post_id Post id.
     * @return bool
     */
    private function post_looks_like_news( $post_id ) {
        $terms = get_the_terms( $post_id, 'category' );
        if ( ! is_array( $terms ) ) {
            return false;
        }

        foreach ( $terms as $term ) {
            $needle = strtolower( $term->slug . ' ' . $term->name );
            if ( false !== strpos( $needle, 'news' ) || false !== strpos( $needle, '资讯' ) || false !== strpos( $needle, '新闻' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get model meta.
     *
     * @param int    $post_id Post id.
     * @param string $field_id Field id.
     * @return string
     */
    private function get_model_meta( $post_id, $field_id ) {
        $field_id = sanitize_key( (string) $field_id );
        $meta_key = class_exists( '\Developer_Starter\Core\Content_Model_Center' )
            ? \Developer_Starter\Core\Content_Model_Center::get_model_meta_key( $field_id )
            : '_qiling_model_' . $field_id;

        $value = get_post_meta( $post_id, $meta_key, true );
        if ( is_array( $value ) || is_object( $value ) ) {
            return '';
        }

        return trim( wp_strip_all_tags( (string) $value ) );
    }

    /**
     * Remove empty values from graph.
     *
     * @param array<int,array<string,mixed>> $graph Raw graph.
     * @return array<int,array<string,mixed>>
     */
    private function clean_graph( $graph ) {
        $clean = array();
        foreach ( $graph as $node ) {
            if ( ! is_array( $node ) ) {
                continue;
            }
            $node = $this->clean_value( $node );
            if ( ! empty( $node ) ) {
                $clean[] = $node;
            }
        }

        return $clean;
    }

    /**
     * Recursively remove empty values and strip unsafe markup.
     *
     * @param mixed $value Raw value.
     * @return mixed
     */
    private function clean_value( $value ) {
        if ( is_array( $value ) ) {
            $out = array();
            $is_list = $this->is_list_array( $value );
            foreach ( $value as $key => $item ) {
                $item = $this->clean_value( $item );
                if ( '' === $item || null === $item || array() === $item ) {
                    continue;
                }
                $out[ $key ] = $item;
            }

            return $is_list ? array_values( $out ) : $out;
        }

        if ( is_string( $value ) ) {
            return trim( wp_strip_all_tags( $value ) );
        }

        return $value;
    }

    /**
     * Determine whether an array should remain a JSON list.
     *
     * @param array<mixed> $value Array value.
     * @return bool
     */
    private function is_list_array( $value ) {
        if ( array() === $value ) {
            return true;
        }

        return array_keys( $value ) === range( 0, count( $value ) - 1 );
    }

    /**
     * Filter one graph node.
     *
     * @param string              $name Node name.
     * @param array<string,mixed> $node Schema node.
     * @param array<string,mixed> $context Resolved page context.
     * @return array<string,mixed>
     */
    private function filter_node( $name, $node, $context ) {
        /**
         * Filter an individual Schema.org node.
         *
         * @param array<string,mixed> $node Schema node.
         * @param string              $name Node name.
         * @param array<string,mixed> $context Resolved page context.
         */
        $node = apply_filters( 'developer_starter_schema_node', $node, $name, $context );

        return is_array( $node ) ? $node : array();
    }
}
