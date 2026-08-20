<?php
/**
 * Universal content model center.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers and describes reusable content models for industry sites.
 */
class Content_Model_Center {

    const SCHEMA_VERSION          = '1.0.0';
    const OPTION_ENABLE          = 'content_model_center_enable';
    const OPTION_MODELS          = 'content_model_enabled_models';
    const OPTION_ARCHIVE_BASE    = 'content_model_archive_base';
    const OPTION_REST_ENABLE     = 'content_model_rest_enable';
    const OPTION_ARCHIVE_ENABLE  = 'content_model_archive_enable';
    const OPTION_META_BOX_ENABLE       = 'content_model_meta_box_enable';
    const OPTION_LOCAL_BUSINESS_ENABLE = 'local_business_features_enable';

    const DEFAULT_ARCHIVE_BASE = 'content';

    const DEFAULT_ENABLED_MODELS = array(
        'service',
        'product',
        'case',
        'testimonial',
        'team',
        'resource',
        'faq',
    );

    const LOCAL_BUSINESS_MODEL_IDS = array(
        'branch',
    );

    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @var array<string,array<string,mixed>>|null
     */
    private $definitions = null;

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

    private function __construct() {
        add_action( 'init', array( $this, 'register_content_types' ), 8 );
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ), 20 );
        add_action( 'save_post', array( $this, 'save_model_meta' ), 10, 2 );
        add_action( 'admin_init', array( $this, 'maybe_flush_rewrite_rules' ), 20 );
    }

    /**
     * Check whether the content model center is enabled.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return bool
     */
    public static function is_enabled( $options = null ) {
        $options = self::resolve_options( $options );
        $enabled = isset( $options[ self::OPTION_ENABLE ] ) ? (string) $options[ self::OPTION_ENABLE ] : '1';

        return '1' === $enabled;
    }

    /**
     * Default enabled models.
     *
     * @return array<int,string>
     */
    public static function get_default_enabled_model_ids( $options = null ) {
        $models = self::DEFAULT_ENABLED_MODELS;
        if ( self::is_local_business_enabled( $options ) ) {
            $models = array_merge( $models, self::LOCAL_BUSINESS_MODEL_IDS );
        }

        return array_values( array_unique( $models ) );
    }

    /**
     * Check whether local store/branch features are enabled.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return bool
     */
    public static function is_local_business_enabled( $options = null ) {
        $options = self::resolve_options( $options );
        $enabled = isset( $options[ self::OPTION_LOCAL_BUSINESS_ENABLE ] ) ? (string) $options[ self::OPTION_LOCAL_BUSINESS_ENABLE ] : '';

        return '1' === $enabled;
    }

    /**
     * Get choices for settings UI.
     *
     * @return array<string,string>
     */
    public static function get_model_choices() {
        $choices = array();
        foreach ( self::get_static_model_definitions() as $model_id => $definition ) {
            $choices[ $model_id ] = isset( $definition['label'] ) ? (string) $definition['label'] : $model_id;
        }

        return $choices;
    }

    /**
     * Sanitize theme option values owned by this service.
     *
     * @param array<string,mixed> $sanitized Sanitized option draft.
     * @param array<string,mixed> $existing_options Existing options.
     * @return array<string,mixed>
     */
    public static function sanitize_options( $sanitized, $existing_options = array() ) {
        $sanitized = is_array( $sanitized ) ? $sanitized : array();
        $existing_options = is_array( $existing_options ) ? $existing_options : array();

        foreach ( array( self::OPTION_ENABLE, self::OPTION_LOCAL_BUSINESS_ENABLE, self::OPTION_REST_ENABLE, self::OPTION_ARCHIVE_ENABLE, self::OPTION_META_BOX_ENABLE ) as $flag ) {
            if ( array_key_exists( $flag, $sanitized ) ) {
                $sanitized[ $flag ] = ( '1' === (string) $sanitized[ $flag ] ) ? '1' : '';
            }
        }

        $local_business_enabled = array_key_exists( self::OPTION_LOCAL_BUSINESS_ENABLE, $sanitized )
            ? '1' === (string) $sanitized[ self::OPTION_LOCAL_BUSINESS_ENABLE ]
            : self::is_local_business_enabled( $existing_options );

        if ( array_key_exists( self::OPTION_MODELS, $sanitized ) ) {
            $raw_models = $sanitized[ self::OPTION_MODELS ];
            if ( ! is_array( $raw_models ) ) {
                $raw_models = '' === (string) $raw_models ? array() : preg_split( '/[\s,]+/', (string) $raw_models );
            }

            $allowed = array_keys( self::get_model_choices() );
            $models = array();
            foreach ( $raw_models as $model_id ) {
                $model_id = sanitize_key( (string) $model_id );
                if ( '' !== $model_id && in_array( $model_id, $allowed, true ) ) {
                    $models[] = $model_id;
                }
            }
            if ( ! $local_business_enabled ) {
                $models = array_diff( $models, self::LOCAL_BUSINESS_MODEL_IDS );
            }
            $sanitized[ self::OPTION_MODELS ] = array_values( array_unique( $models ) );
        }

        if ( array_key_exists( self::OPTION_ARCHIVE_BASE, $sanitized ) ) {
            $archive_base = sanitize_title( (string) $sanitized[ self::OPTION_ARCHIVE_BASE ] );
            $sanitized[ self::OPTION_ARCHIVE_BASE ] = '' !== $archive_base ? $archive_base : self::DEFAULT_ARCHIVE_BASE;
        }

        return $sanitized;
    }

    /**
     * Register active custom post types, taxonomies and model meta fields.
     *
     * @return void
     */
    public function register_content_types() {
        if ( ! self::is_enabled() ) {
            return;
        }

        foreach ( $this->get_active_models() as $model ) {
            if ( empty( $model['postType'] ) ) {
                continue;
            }

            if ( ! empty( $model['register'] ) ) {
                $this->register_model_post_type( $model );
                $this->register_model_taxonomies( $model );
            }

            $this->register_model_meta_fields( $model );
        }
    }

    /**
     * Get active model definitions for runtime use.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return array<string,array<string,mixed>>
     */
    public function get_active_models( $options = null ) {
        $models = array();
        if ( ! self::is_enabled( $options ) ) {
            return $models;
        }

        $enabled_ids = self::get_enabled_model_ids( $options );
        foreach ( $enabled_ids as $model_id ) {
            $definition = $this->get_model_definition( $model_id );
            if ( ! empty( $definition ) ) {
                $models[ $model_id ] = $this->normalize_runtime_definition( $definition );
            }
        }

        return $models;
    }

    /**
     * Get all model definitions.
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_model_definitions() {
        if ( null !== $this->definitions ) {
            return $this->definitions;
        }

        $this->definitions = apply_filters(
            'developer_starter_content_model_definitions',
            self::get_static_model_definitions()
        );

        if ( ! is_array( $this->definitions ) ) {
            $this->definitions = array();
        }

        return $this->definitions;
    }

    /**
     * Get one model definition.
     *
     * @param string $model_id Model id.
     * @return array<string,mixed>
     */
    public function get_model_definition( $model_id ) {
        $model_id = sanitize_key( (string) $model_id );
        $definitions = $this->get_model_definitions();

        return isset( $definitions[ $model_id ] ) && is_array( $definitions[ $model_id ] )
            ? $definitions[ $model_id ]
            : array();
    }

    /**
     * Get enabled model ids from options.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return array<int,string>
     */
    public static function get_enabled_model_ids( $options = null ) {
        $options = self::resolve_options( $options );
        if ( array_key_exists( self::OPTION_MODELS, $options ) ) {
            $models = $options[ self::OPTION_MODELS ];
            if ( ! is_array( $models ) ) {
                $models = '' === (string) $models ? array() : preg_split( '/[\s,]+/', (string) $models );
            }
        } else {
            $models = self::get_default_enabled_model_ids( $options );
        }

        $allowed = array_keys( self::get_model_choices() );
        $out = array();
        foreach ( $models as $model_id ) {
            $model_id = sanitize_key( (string) $model_id );
            if ( '' !== $model_id && in_array( $model_id, $allowed, true ) ) {
                $out[] = $model_id;
            }
        }

        if ( ! self::is_local_business_enabled( $options ) ) {
            $out = array_diff( $out, self::LOCAL_BUSINESS_MODEL_IDS );
        } else {
            $out = array_merge( $out, self::LOCAL_BUSINESS_MODEL_IDS );
        }

        return array_values( array_unique( $out ) );
    }

    /**
     * Check whether a model belongs to local store/branch features.
     *
     * @param string $model_id Model id.
     * @return bool
     */
    private static function is_local_business_model_id( $model_id ) {
        return in_array( sanitize_key( (string) $model_id ), self::LOCAL_BUSINESS_MODEL_IDS, true );
    }

    /**
     * Payload for builders and admin previews.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return array<string,mixed>
     */
    public static function get_client_payload( $options = null ) {
        $instance = self::get_instance();
        $enabled = self::is_enabled( $options );
        $active_models = $instance->get_active_models( $options );
        $all_models = array();

        foreach ( $instance->get_model_definitions() as $model_id => $definition ) {
            if ( ! is_array( $definition ) ) {
                continue;
            }
            if ( ! self::is_local_business_enabled( $options ) && self::is_local_business_model_id( $model_id ) ) {
                continue;
            }
            $all_models[ $model_id ] = $instance->format_model_for_client( $instance->normalize_runtime_definition( $definition ), true );
        }

        $active = array();
        foreach ( $active_models as $model_id => $definition ) {
            $active[ $model_id ] = $instance->format_model_for_client( $definition, true );
        }

        return array(
            'schemaVersion'   => self::SCHEMA_VERSION,
            'enabled'         => $enabled,
            'enabledModelIds' => array_keys( $active ),
            'models'          => $active,
            'allModels'       => $all_models,
            'archiveBase'     => self::get_archive_base( $options ),
            'restEnabled'     => self::is_rest_enabled( $options ),
            'archiveEnabled'  => self::is_archive_enabled( $options ),
        );
    }

    /**
     * Compact generation context.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return array<string,mixed>
     */
    public static function get_prompt_context( $options = null ) {
        $payload = self::get_client_payload( $options );
        $models = array();

        foreach ( $payload['models'] as $model ) {
            if ( ! is_array( $model ) ) {
                continue;
            }

            $fields = array();
            if ( ! empty( $model['fields'] ) && is_array( $model['fields'] ) ) {
                foreach ( $model['fields'] as $field ) {
                    if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                        continue;
                    }
                    $fields[] = array(
                        'id'    => (string) $field['id'],
                        'label' => isset( $field['label'] ) ? (string) $field['label'] : (string) $field['id'],
                        'type'  => isset( $field['type'] ) ? (string) $field['type'] : 'text',
                    );
                    if ( count( $fields ) >= 10 ) {
                        break;
                    }
                }
            }

            $models[] = array(
                'id'           => isset( $model['id'] ) ? (string) $model['id'] : '',
                'label'        => isset( $model['label'] ) ? (string) $model['label'] : '',
                'description'  => isset( $model['description'] ) ? (string) $model['description'] : '',
                'post_type'    => isset( $model['postType'] ) ? (string) $model['postType'] : '',
                'schema_types' => isset( $model['schemaTypes'] ) && is_array( $model['schemaTypes'] ) ? $model['schemaTypes'] : array(),
                'module_hints' => isset( $model['moduleHints'] ) && is_array( $model['moduleHints'] ) ? $model['moduleHints'] : array(),
                'fields'       => $fields,
            );
        }

        return array(
            'schema_version' => self::SCHEMA_VERSION,
            'enabled'        => ! empty( $payload['enabled'] ),
            'archive_base'   => isset( $payload['archiveBase'] ) ? (string) $payload['archiveBase'] : self::DEFAULT_ARCHIVE_BASE,
            'models'         => $models,
        );
    }

    /**
     * Query model posts for future modules and package tools.
     *
     * @param string              $model_id Model id.
     * @param array<string,mixed> $args Query args.
     * @return array<int,\WP_Post>
     */
    public static function query_model_items( $model_id, $args = array() ) {
        $instance = self::get_instance();
        $definition = $instance->normalize_runtime_definition( $instance->get_model_definition( $model_id ) );
        if ( empty( $definition['postType'] ) ) {
            return array();
        }

        $query_args = wp_parse_args(
            is_array( $args ) ? $args : array(),
            array(
                'post_type'              => sanitize_key( (string) $definition['postType'] ),
                'post_status'            => 'publish',
                'posts_per_page'         => 12,
                'orderby'                => 'menu_order date',
                'order'                  => 'DESC',
                'no_found_rows'          => true,
                'update_post_meta_cache' => true,
                'update_post_term_cache' => true,
            )
        );

        return get_posts( $query_args );
    }

    /**
     * Register admin meta boxes for enabled model post types.
     *
     * @return void
     */
    public function register_meta_boxes() {
        if ( ! self::is_enabled() || ! self::is_meta_box_enabled() ) {
            return;
        }

        foreach ( $this->get_active_models() as $model ) {
            if ( empty( $model['postType'] ) || empty( $model['fields'] ) || empty( $model['register'] ) ) {
                continue;
            }

            add_meta_box(
                'qiling_content_model_fields',
                __( '内容模型字段', 'developer-starter' ),
                array( $this, 'render_meta_box' ),
                (string) $model['postType'],
                'normal',
                'default'
            );
        }
    }

    /**
     * Render model field meta box.
     *
     * @param \WP_Post $post Post object.
     * @return void
     */
    public function render_meta_box( $post ) {
        $model = $this->find_model_by_post_type( $post->post_type );
        if ( empty( $model ) || empty( $model['fields'] ) ) {
            echo '<p>' . esc_html__( '当前内容类型未配置模型字段。', 'developer-starter' ) . '</p>';
            return;
        }

        wp_nonce_field( 'qiling_content_model_meta_' . $post->ID, 'qiling_content_model_meta_nonce' );

        echo '<div class="qiling-content-model-meta">';
        echo '<p class="description">' . esc_html__( '这些字段由通用内容模型中心提供，后续可被模块、AI 装修和站点包复用。', 'developer-starter' ) . '</p>';
        echo '<table class="form-table" role="presentation"><tbody>';
        foreach ( $model['fields'] as $field ) {
            $this->render_model_meta_field( $post->ID, $field );
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    /**
     * Save model meta fields.
     *
     * @param int      $post_id Post id.
     * @param \WP_Post $post Post object.
     * @return void
     */
    public function save_model_meta( $post_id, $post ) {
        if ( ! $post instanceof \WP_Post || ! self::is_enabled() || ! self::is_meta_box_enabled() ) {
            return;
        }

        $model = $this->find_model_by_post_type( $post->post_type );
        if ( empty( $model ) || empty( $model['fields'] ) || empty( $model['register'] ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['qiling_content_model_meta_nonce'] ) ) {
            return;
        }

        $nonce = sanitize_text_field( wp_unslash( (string) $_POST['qiling_content_model_meta_nonce'] ) );
        if ( ! wp_verify_nonce( $nonce, 'qiling_content_model_meta_' . $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $raw_fields = isset( $_POST['qiling_content_model_meta'] ) && is_array( $_POST['qiling_content_model_meta'] )
            ? wp_unslash( $_POST['qiling_content_model_meta'] )
            : array();

        foreach ( $model['fields'] as $field ) {
            if ( empty( $field['id'] ) ) {
                continue;
            }

            $field_id = sanitize_key( (string) $field['id'] );
            $meta_key = self::get_model_meta_key( $field_id );
            $raw_value = isset( $raw_fields[ $field_id ] ) ? $raw_fields[ $field_id ] : '';
            $clean = $this->sanitize_model_field_value( $raw_value, $field );

            if ( '' === $clean ) {
                delete_post_meta( $post_id, $meta_key );
            } else {
                update_post_meta( $post_id, $meta_key, $clean );
            }
        }
    }

    /**
     * Flush rewrite rules when model registration options change.
     *
     * @return void
     */
    public function maybe_flush_rewrite_rules() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $signature = md5(
            wp_json_encode(
                array(
                    'enabled'  => self::is_enabled(),
                    'models'   => self::get_enabled_model_ids(),
                    'base'     => self::get_archive_base(),
                    'archives' => self::is_archive_enabled(),
                )
            )
        );

        $stored_signature = (string) get_option( 'developer_starter_content_model_rewrite_signature', '' );
        if ( $signature === $stored_signature ) {
            return;
        }

        flush_rewrite_rules( false );
        update_option( 'developer_starter_content_model_rewrite_signature', $signature, false );
    }

    /**
     * Get model meta key.
     *
     * @param string $field_id Field id.
     * @return string
     */
    public static function get_model_meta_key( $field_id ) {
        return '_qiling_model_' . sanitize_key( (string) $field_id );
    }

    /**
     * Get static model definitions.
     *
     * @return array<string,array<string,mixed>>
     */
    private static function get_static_model_definitions() {
        return array(
            'page'        => self::model( 'page', __( '页面', 'developer-starter' ), __( '承载官网、专题页、落地页和内容聚合页。', 'developer-starter' ), 'page', false, 'dashicons-admin-page', array(), array( 'WebPage' ), array( 'banner', 'image_text', 'cta' ) ),
            'post'        => self::model( 'post', __( '文章', 'developer-starter' ), __( '适合博客、资讯、杂志、知识库和内容营销。', 'developer-starter' ), 'post', false, 'dashicons-admin-post', array(), array( 'Article', 'BlogPosting' ), array( 'blog', 'featured_posts', 'news' ) ),
            'service'     => self::model( 'service', __( '服务', 'developer-starter' ), __( '适合企业服务、门店项目、咨询方案和本地业务。', 'developer-starter' ), 'ql_service', true, 'dashicons-hammer', array( 'subtitle', 'summary', 'icon', 'price', 'external_url', 'featured', 'sort_order' ), array( 'Service' ), array( 'services', 'service_cards', 'pricing' ), 'services' ),
            'product'     => self::model( 'product', __( '产品', 'developer-starter' ), __( '适合产品目录、设备、课程商品、数字产品和方案包。', 'developer-starter' ), 'ql_product', true, 'dashicons-products', array( 'subtitle', 'summary', 'price', 'external_url', 'gallery', 'featured', 'sort_order' ), array( 'Product', 'ItemList' ), array( 'products', 'product_showcase', 'pricing' ), 'products' ),
            'case'        => self::model( 'case', __( '案例', 'developer-starter' ), __( '适合客户案例、项目作品、实施成果和成功故事。', 'developer-starter' ), 'ql_case', true, 'dashicons-portfolio', array( 'subtitle', 'client', 'industry', 'result', 'external_url', 'gallery', 'featured', 'sort_order' ), array( 'Article', 'CreativeWork' ), array( 'cases', 'work_library', 'testimonials' ), 'cases' ),
            'testimonial' => self::model( 'testimonial', __( '评价', 'developer-starter' ), __( '适合客户评价、学员反馈、用户证言和口碑背书。', 'developer-starter' ), 'ql_testimonial', true, 'dashicons-format-quote', array( 'author_name', 'author_title', 'company', 'rating', 'featured', 'sort_order' ), array( 'Review' ), array( 'testimonials', 'reader_wall' ), 'testimonials' ),
            'team'        => self::model( 'team', __( '团队', 'developer-starter' ), __( '适合团队成员、专家医生、讲师、顾问和主创人员。', 'developer-starter' ), 'ql_team_member', true, 'dashicons-groups', array( 'position', 'department', 'phone', 'email', 'external_url', 'sort_order' ), array( 'Person' ), array( 'team', 'author_matrix' ), 'team' ),
            'branch'      => self::model( 'branch', __( '门店/分支', 'developer-starter' ), __( '适合连锁门店、校区、服务网点、办公地点和经销商。', 'developer-starter' ), 'ql_branch', true, 'dashicons-location-alt', array( 'address', 'phone', 'email', 'business_hours', 'latitude', 'longitude', 'sort_order' ), array( 'LocalBusiness', 'Organization' ), array( 'branches', 'contact', 'booking_entry' ), 'branches' ),
            'faq'         => self::model( 'faq', __( 'FAQ', 'developer-starter' ), __( '适合常见问题、帮助中心、售前答疑和文档条目。', 'developer-starter' ), 'ds_faq', false, 'dashicons-editor-help', array( 'sort_order', 'file_url', 'file_format', 'file_size' ), array( 'FAQPage' ), array( 'faq', 'accordion' ), 'faq' ),
            'download'    => self::model( 'download', __( '下载资料', 'developer-starter' ), __( '适合白皮书、资料包、软件附件、报价单和说明书。', 'developer-starter' ), 'ql_download', true, 'dashicons-download', array( 'summary', 'file_url', 'file_format', 'file_size', 'featured', 'sort_order' ), array( 'DigitalDocument' ), array( 'downloads', 'resource_stats' ), 'downloads' ),
            'course'      => self::model( 'course', __( '课程', 'developer-starter' ), __( '适合课程、训练营、培训项目和知识付费。', 'developer-starter' ), 'ql_course', true, 'dashicons-welcome-learn-more', array( 'subtitle', 'price', 'duration', 'level', 'external_url', 'featured', 'sort_order' ), array( 'Course' ), array( 'curriculum', 'course_enrollment' ), 'courses' ),
            'event'       => self::model( 'event', __( '活动', 'developer-starter' ), __( '适合展会、沙龙、旅行线路、发布会和预约活动。', 'developer-starter' ), 'ql_event', true, 'dashicons-calendar-alt', array( 'summary', 'start_date', 'end_date', 'location', 'external_url', 'featured', 'sort_order' ), array( 'Event' ), array( 'timeline', 'itinerary', 'ticket_showcase' ), 'events' ),
            'job'         => self::model( 'job', __( '职位', 'developer-starter' ), __( '适合招聘职位和人才招募；可和主题招聘系统衔接。', 'developer-starter' ), '', false, 'dashicons-id', array( 'department', 'location', 'salary', 'external_url' ), array( 'JobPosting' ), array( 'cta', 'contact' ), 'jobs' ),
            'room'        => self::model( 'room', __( '房型', 'developer-starter' ), __( '适合酒店民宿房型、公寓、场地和空间产品。', 'developer-starter' ), 'ql_room', true, 'dashicons-building', array( 'subtitle', 'price', 'capacity', 'gallery', 'featured', 'sort_order' ), array( 'HotelRoom', 'Product' ), array( 'room_showcase', 'hotel_amenities' ), 'rooms' ),
            'menu_item'   => self::model( 'menu_item', __( '菜单菜品', 'developer-starter' ), __( '适合餐饮菜单、套餐、项目价目表和门店商品。', 'developer-starter' ), 'ql_menu_item', true, 'dashicons-food', array( 'subtitle', 'price', 'badge', 'gallery', 'featured', 'sort_order' ), array( 'MenuItem', 'Product' ), array( 'menu', 'pricing' ), 'menu' ),
            'software'    => self::model( 'software', __( '软件应用', 'developer-starter' ), __( '适合软件目录、应用市场和工具库；优先兼容启灵应用插件。', 'developer-starter' ), 'qiapp_software', false, 'dashicons-cloud', array( 'subtitle', 'external_url', 'rating', 'featured' ), array( 'SoftwareApplication' ), array( 'software_carousel', 'software_ranking' ), 'software' ),
            'resource'    => self::model( 'resource', __( '资源', 'developer-starter' ), __( '适合资料、知识卡片、媒体资源和可筛选内容库。', 'developer-starter' ), 'ql_resource', true, 'dashicons-media-document', array( 'summary', 'resource_type', 'file_url', 'external_url', 'featured', 'sort_order' ), array( 'CreativeWork', 'Article' ), array( 'media_list', 'knowledge_cards', 'resource_hero_pro' ), 'resources' ),
            'media_item'  => self::model( 'media_item', __( '媒体条目', 'developer-starter' ), __( '适合视频、播客、图集、媒体报道和素材条目。', 'developer-starter' ), 'ql_media_item', true, 'dashicons-format-video', array( 'summary', 'media_type', 'media_url', 'external_url', 'featured', 'sort_order' ), array( 'MediaObject' ), array( 'video', 'gallery', 'media_list' ), 'media' ),
            'author'      => self::model( 'author', __( '作者', 'developer-starter' ), __( '适合博客作者、杂志专栏、个人主页和投稿人。', 'developer-starter' ), '', false, 'dashicons-admin-users', array( 'bio', 'avatar', 'external_url' ), array( 'Person' ), array( 'author_matrix', 'about_me_card' ), 'authors' ),
            'partner'     => self::model( 'partner', __( '合作伙伴', 'developer-starter' ), __( '适合客户、品牌伙伴、渠道商、供应商和友情链接。', 'developer-starter' ), 'ql_partner', true, 'dashicons-businessperson', array( 'logo', 'summary', 'external_url', 'featured', 'sort_order' ), array( 'Organization' ), array( 'clients', 'friendly_links' ), 'partners' ),
        );
    }

    /**
     * Build one static model definition.
     *
     * @param string            $id Model id.
     * @param string            $label Label.
     * @param string            $description Description.
     * @param string            $post_type Post type.
     * @param bool              $register Register post type.
     * @param string            $menu_icon Dashicon.
     * @param array<int,string> $field_ids Field ids.
     * @param array<int,string> $schema_types Schema types.
     * @param array<int,string> $module_hints Module hints.
     * @param string            $archive_slug Archive slug.
     * @return array<string,mixed>
     */
    private static function model( $id, $label, $description, $post_type, $register, $menu_icon, $field_ids, $schema_types, $module_hints, $archive_slug = '' ) {
        $fields = array();
        foreach ( $field_ids as $field_id ) {
            $field = self::get_field_definition( $field_id );
            if ( ! empty( $field ) ) {
                $fields[] = $field;
            }
        }

        $plural = sprintf(
            /* translators: %s: content model label */
            __( '%s内容', 'developer-starter' ),
            $label
        );
        $taxonomies = array();
        if ( $register && '' !== $post_type ) {
            $taxonomies = array(
                array(
                    'taxonomy'     => sanitize_key( $post_type . '_category' ),
                    'label'        => sprintf( __( '%s分类', 'developer-starter' ), $label ),
                    'hierarchical' => true,
                    'slug'         => ( '' !== $archive_slug ? $archive_slug : $id ) . '-category',
                ),
                array(
                    'taxonomy'     => sanitize_key( $post_type . '_tag' ),
                    'label'        => sprintf( __( '%s标签', 'developer-starter' ), $label ),
                    'hierarchical' => false,
                    'slug'         => ( '' !== $archive_slug ? $archive_slug : $id ) . '-tag',
                ),
            );
        }

        return array(
            'id'           => sanitize_key( $id ),
            'label'        => $label,
            'plural'       => $plural,
            'description'  => $description,
            'post_type'    => sanitize_key( $post_type ),
            'register'     => (bool) $register,
            'public'       => true,
            'archive_slug' => '' !== $archive_slug ? sanitize_title( $archive_slug ) : sanitize_title( $id ),
            'menu_icon'    => $menu_icon,
            'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
            'taxonomies'   => $taxonomies,
            'fields'       => $fields,
            'schema_types' => array_values( $schema_types ),
            'module_hints' => array_values( $module_hints ),
        );
    }

    /**
     * Get reusable field definition.
     *
     * @param string $field_id Field id.
     * @return array<string,mixed>
     */
    private static function get_field_definition( $field_id ) {
        $fields = array(
            'subtitle'       => array( 'id' => 'subtitle', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'text' ),
            'summary'        => array( 'id' => 'summary', 'label' => __( '摘要', 'developer-starter' ), 'type' => 'textarea' ),
            'icon'           => array( 'id' => 'icon', 'label' => __( '图标', 'developer-starter' ), 'type' => 'text' ),
            'price'          => array( 'id' => 'price', 'label' => __( '价格/报价', 'developer-starter' ), 'type' => 'text' ),
            'external_url'   => array( 'id' => 'external_url', 'label' => __( '外部链接', 'developer-starter' ), 'type' => 'url' ),
            'featured'       => array( 'id' => 'featured', 'label' => __( '推荐展示', 'developer-starter' ), 'type' => 'checkbox' ),
            'sort_order'     => array( 'id' => 'sort_order', 'label' => __( '排序权重', 'developer-starter' ), 'type' => 'number' ),
            'gallery'        => array( 'id' => 'gallery', 'label' => __( '图库 URL', 'developer-starter' ), 'type' => 'textarea' ),
            'client'         => array( 'id' => 'client', 'label' => __( '客户名称', 'developer-starter' ), 'type' => 'text' ),
            'industry'       => array( 'id' => 'industry', 'label' => __( '所属行业', 'developer-starter' ), 'type' => 'text' ),
            'result'         => array( 'id' => 'result', 'label' => __( '成果亮点', 'developer-starter' ), 'type' => 'textarea' ),
            'author_name'    => array( 'id' => 'author_name', 'label' => __( '评价人', 'developer-starter' ), 'type' => 'text' ),
            'author_title'   => array( 'id' => 'author_title', 'label' => __( '评价人身份', 'developer-starter' ), 'type' => 'text' ),
            'company'        => array( 'id' => 'company', 'label' => __( '公司/组织', 'developer-starter' ), 'type' => 'text' ),
            'rating'         => array( 'id' => 'rating', 'label' => __( '评分', 'developer-starter' ), 'type' => 'number' ),
            'position'       => array( 'id' => 'position', 'label' => __( '职位', 'developer-starter' ), 'type' => 'text' ),
            'department'     => array( 'id' => 'department', 'label' => __( '部门', 'developer-starter' ), 'type' => 'text' ),
            'phone'          => array( 'id' => 'phone', 'label' => __( '电话', 'developer-starter' ), 'type' => 'text' ),
            'email'          => array( 'id' => 'email', 'label' => __( '邮箱', 'developer-starter' ), 'type' => 'email' ),
            'address'        => array( 'id' => 'address', 'label' => __( '地址', 'developer-starter' ), 'type' => 'textarea' ),
            'business_hours' => array( 'id' => 'business_hours', 'label' => __( '营业时间', 'developer-starter' ), 'type' => 'textarea' ),
            'latitude'       => array( 'id' => 'latitude', 'label' => __( '纬度', 'developer-starter' ), 'type' => 'text' ),
            'longitude'      => array( 'id' => 'longitude', 'label' => __( '经度', 'developer-starter' ), 'type' => 'text' ),
            'file_url'       => array( 'id' => 'file_url', 'label' => __( '文件链接', 'developer-starter' ), 'type' => 'url' ),
            'file_format'    => array( 'id' => 'file_format', 'label' => __( '文件格式', 'developer-starter' ), 'type' => 'text' ),
            'file_size'      => array( 'id' => 'file_size', 'label' => __( '文件大小', 'developer-starter' ), 'type' => 'text' ),
            'duration'       => array( 'id' => 'duration', 'label' => __( '周期/时长', 'developer-starter' ), 'type' => 'text' ),
            'level'          => array( 'id' => 'level', 'label' => __( '难度/等级', 'developer-starter' ), 'type' => 'text' ),
            'start_date'     => array( 'id' => 'start_date', 'label' => __( '开始日期', 'developer-starter' ), 'type' => 'date' ),
            'end_date'       => array( 'id' => 'end_date', 'label' => __( '结束日期', 'developer-starter' ), 'type' => 'date' ),
            'location'       => array( 'id' => 'location', 'label' => __( '地点', 'developer-starter' ), 'type' => 'text' ),
            'salary'         => array( 'id' => 'salary', 'label' => __( '薪资', 'developer-starter' ), 'type' => 'text' ),
            'capacity'       => array( 'id' => 'capacity', 'label' => __( '容量/人数', 'developer-starter' ), 'type' => 'text' ),
            'badge'          => array( 'id' => 'badge', 'label' => __( '角标', 'developer-starter' ), 'type' => 'text' ),
            'resource_type'  => array( 'id' => 'resource_type', 'label' => __( '资源类型', 'developer-starter' ), 'type' => 'text' ),
            'media_type'     => array( 'id' => 'media_type', 'label' => __( '媒体类型', 'developer-starter' ), 'type' => 'text' ),
            'media_url'      => array( 'id' => 'media_url', 'label' => __( '媒体链接', 'developer-starter' ), 'type' => 'url' ),
            'bio'            => array( 'id' => 'bio', 'label' => __( '简介', 'developer-starter' ), 'type' => 'textarea' ),
            'avatar'         => array( 'id' => 'avatar', 'label' => __( '头像 URL', 'developer-starter' ), 'type' => 'url' ),
            'logo'           => array( 'id' => 'logo', 'label' => __( 'Logo URL', 'developer-starter' ), 'type' => 'url' ),
        );

        return isset( $fields[ $field_id ] ) ? $fields[ $field_id ] : array();
    }

    /**
     * Register one model post type.
     *
     * @param array<string,mixed> $model Model definition.
     * @return void
     */
    private function register_model_post_type( $model ) {
        $post_type = isset( $model['postType'] ) ? sanitize_key( (string) $model['postType'] ) : '';
        if ( '' === $post_type || post_type_exists( $post_type ) ) {
            return;
        }

        $label = isset( $model['label'] ) ? (string) $model['label'] : $post_type;
        $plural = isset( $model['plural'] ) ? (string) $model['plural'] : $label;
        $archive_base = self::get_archive_base();
        $archive_slug = isset( $model['archiveSlug'] ) ? sanitize_title( (string) $model['archiveSlug'] ) : $post_type;
        $has_archive = self::is_archive_enabled() ? $archive_base . '/' . $archive_slug : false;

        $args = array(
            'labels'       => array(
                'name'               => $plural,
                'singular_name'      => $label,
                'menu_name'          => $label,
                'add_new'            => __( '新增', 'developer-starter' ),
                'add_new_item'       => sprintf( __( '新增%s', 'developer-starter' ), $label ),
                'edit_item'          => sprintf( __( '编辑%s', 'developer-starter' ), $label ),
                'new_item'           => sprintf( __( '新%s', 'developer-starter' ), $label ),
                'view_item'          => sprintf( __( '查看%s', 'developer-starter' ), $label ),
                'search_items'       => sprintf( __( '搜索%s', 'developer-starter' ), $label ),
                'not_found'          => sprintf( __( '暂无%s', 'developer-starter' ), $label ),
                'not_found_in_trash' => sprintf( __( '回收站中暂无%s', 'developer-starter' ), $label ),
            ),
            'public'       => ! empty( $model['public'] ),
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_icon'    => isset( $model['menuIcon'] ) ? (string) $model['menuIcon'] : 'dashicons-screenoptions',
            'supports'     => isset( $model['supports'] ) && is_array( $model['supports'] ) ? $model['supports'] : array( 'title', 'editor', 'thumbnail' ),
            'has_archive'  => $has_archive,
            'rewrite'      => self::is_archive_enabled()
                ? array(
                    'slug'       => $archive_base . '/' . $archive_slug,
                    'with_front' => false,
                )
                : false,
            'show_in_rest' => self::is_rest_enabled(),
            'query_var'    => true,
            'capability_type' => 'post',
        );

        register_post_type(
            $post_type,
            apply_filters( 'developer_starter_content_model_post_type_args', $args, $model )
        );
    }

    /**
     * Register taxonomies for one model.
     *
     * @param array<string,mixed> $model Model definition.
     * @return void
     */
    private function register_model_taxonomies( $model ) {
        $post_type = isset( $model['postType'] ) ? sanitize_key( (string) $model['postType'] ) : '';
        if ( '' === $post_type || empty( $model['taxonomies'] ) || ! is_array( $model['taxonomies'] ) ) {
            return;
        }

        $archive_base = self::get_archive_base();
        foreach ( $model['taxonomies'] as $taxonomy ) {
            if ( ! is_array( $taxonomy ) || empty( $taxonomy['taxonomy'] ) ) {
                continue;
            }

            $taxonomy_name = sanitize_key( (string) $taxonomy['taxonomy'] );
            if ( '' === $taxonomy_name || taxonomy_exists( $taxonomy_name ) ) {
                continue;
            }

            $label = isset( $taxonomy['label'] ) ? (string) $taxonomy['label'] : $taxonomy_name;
            $slug = isset( $taxonomy['slug'] ) ? sanitize_title( (string) $taxonomy['slug'] ) : $taxonomy_name;
            $args = array(
                'labels'            => array(
                    'name'          => $label,
                    'singular_name' => $label,
                    'menu_name'     => $label,
                ),
                'hierarchical'      => ! empty( $taxonomy['hierarchical'] ),
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => self::is_rest_enabled(),
                'rewrite'           => self::is_archive_enabled()
                    ? array(
                        'slug'       => $archive_base . '/' . $slug,
                        'with_front' => false,
                    )
                    : false,
            );

            register_taxonomy(
                $taxonomy_name,
                $post_type,
                apply_filters( 'developer_starter_content_model_taxonomy_args', $args, $taxonomy, $model )
            );
        }
    }

    /**
     * Register meta fields for one model.
     *
     * @param array<string,mixed> $model Model definition.
     * @return void
     */
    private function register_model_meta_fields( $model ) {
        if ( ! function_exists( 'register_post_meta' ) || empty( $model['postType'] ) || empty( $model['fields'] ) || ! is_array( $model['fields'] ) ) {
            return;
        }

        $post_type = sanitize_key( (string) $model['postType'] );
        foreach ( $model['fields'] as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            register_post_meta(
                $post_type,
                self::get_model_meta_key( (string) $field['id'] ),
                array(
                    'type'              => $this->get_rest_meta_type( $field ),
                    'single'            => true,
                    'show_in_rest'      => self::is_rest_enabled(),
                    'sanitize_callback' => function( $value, $meta_key = '', $object_type = '', $meta_subtype = '' ) use ( $field ) {
                        unset( $meta_key, $object_type, $meta_subtype );
                        return $this->sanitize_model_field_value( $value, $field );
                    },
                    'auth_callback'     => function() {
                        return current_user_can( 'edit_posts' );
                    },
                )
            );
        }
    }

    /**
     * Render one model meta field.
     *
     * @param int                 $post_id Post id.
     * @param array<string,mixed> $field Field definition.
     * @return void
     */
    private function render_model_meta_field( $post_id, $field ) {
        $field_id = isset( $field['id'] ) ? sanitize_key( (string) $field['id'] ) : '';
        if ( '' === $field_id ) {
            return;
        }

        $label = isset( $field['label'] ) ? (string) $field['label'] : $field_id;
        $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
        $value = get_post_meta( $post_id, self::get_model_meta_key( $field_id ), true );
        $name = 'qiling_content_model_meta[' . $field_id . ']';
        $input_id = 'qiling-model-' . $field_id;

        echo '<tr><th scope="row"><label for="' . esc_attr( $input_id ) . '">' . esc_html( $label ) . '</label></th><td>';
        if ( 'textarea' === $type ) {
            echo '<textarea id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $name ) . '" rows="3" class="large-text">' . esc_textarea( $value ) . '</textarea>';
        } elseif ( 'checkbox' === $type ) {
            echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="" />';
            echo '<label><input id="' . esc_attr( $input_id ) . '" type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( (string) $value, '1', false ) . ' /> ' . esc_html__( '启用', 'developer-starter' ) . '</label>';
        } else {
            $input_type = in_array( $type, array( 'url', 'number', 'date', 'email' ), true ) ? $type : 'text';
            echo '<input id="' . esc_attr( $input_id ) . '" type="' . esc_attr( $input_type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
        }
        echo '</td></tr>';
    }

    /**
     * Find active model by post type.
     *
     * @param string $post_type Post type.
     * @return array<string,mixed>
     */
    private function find_model_by_post_type( $post_type ) {
        $post_type = sanitize_key( (string) $post_type );
        foreach ( $this->get_active_models() as $model ) {
            if ( isset( $model['postType'] ) && $post_type === (string) $model['postType'] ) {
                return $model;
            }
        }

        return array();
    }

    /**
     * Normalize definition for runtime payloads.
     *
     * @param array<string,mixed> $definition Definition.
     * @return array<string,mixed>
     */
    private function normalize_runtime_definition( $definition ) {
        $definition = is_array( $definition ) ? $definition : array();
        $post_type = isset( $definition['post_type'] ) ? sanitize_key( (string) $definition['post_type'] ) : '';
        if ( 'software' === ( $definition['id'] ?? '' ) && function_exists( 'developer_starter_qiapp_get_post_type' ) ) {
            $post_type = sanitize_key( (string) developer_starter_qiapp_get_post_type() );
        }

        return array(
            'id'           => isset( $definition['id'] ) ? sanitize_key( (string) $definition['id'] ) : '',
            'label'        => isset( $definition['label'] ) ? (string) $definition['label'] : '',
            'plural'       => isset( $definition['plural'] ) ? (string) $definition['plural'] : '',
            'description'  => isset( $definition['description'] ) ? (string) $definition['description'] : '',
            'postType'     => $post_type,
            'register'     => ! empty( $definition['register'] ),
            'public'       => ! empty( $definition['public'] ),
            'archiveSlug'  => isset( $definition['archive_slug'] ) ? sanitize_title( (string) $definition['archive_slug'] ) : '',
            'menuIcon'     => isset( $definition['menu_icon'] ) ? (string) $definition['menu_icon'] : 'dashicons-screenoptions',
            'supports'     => isset( $definition['supports'] ) && is_array( $definition['supports'] ) ? array_values( $definition['supports'] ) : array(),
            'taxonomies'   => isset( $definition['taxonomies'] ) && is_array( $definition['taxonomies'] ) ? array_values( $definition['taxonomies'] ) : array(),
            'fields'       => isset( $definition['fields'] ) && is_array( $definition['fields'] ) ? array_values( $definition['fields'] ) : array(),
            'schemaTypes'  => isset( $definition['schema_types'] ) && is_array( $definition['schema_types'] ) ? array_values( $definition['schema_types'] ) : array(),
            'moduleHints'  => isset( $definition['module_hints'] ) && is_array( $definition['module_hints'] ) ? array_values( $definition['module_hints'] ) : array(),
        );
    }

    /**
     * Format model for client payload.
     *
     * @param array<string,mixed> $model Runtime model.
     * @param bool                $include_fields Include fields.
     * @return array<string,mixed>
     */
    private function format_model_for_client( $model, $include_fields = true ) {
        $post_type = isset( $model['postType'] ) ? sanitize_key( (string) $model['postType'] ) : '';
        $admin_url = '';
        if ( '' !== $post_type && ( ! isset( $model['register'] ) || ! empty( $model['register'] ) || post_type_exists( $post_type ) ) ) {
            $admin_url = admin_url( 'edit.php?post_type=' . rawurlencode( $post_type ) );
        }

        $item = array(
            'id'          => isset( $model['id'] ) ? (string) $model['id'] : '',
            'label'       => isset( $model['label'] ) ? (string) $model['label'] : '',
            'description' => isset( $model['description'] ) ? (string) $model['description'] : '',
            'postType'    => $post_type,
            'register'    => ! empty( $model['register'] ),
            'archiveSlug' => isset( $model['archiveSlug'] ) ? (string) $model['archiveSlug'] : '',
            'adminUrl'    => $admin_url,
            'taxonomies'  => isset( $model['taxonomies'] ) && is_array( $model['taxonomies'] ) ? $model['taxonomies'] : array(),
            'schemaTypes' => isset( $model['schemaTypes'] ) && is_array( $model['schemaTypes'] ) ? $model['schemaTypes'] : array(),
            'moduleHints' => isset( $model['moduleHints'] ) && is_array( $model['moduleHints'] ) ? $model['moduleHints'] : array(),
        );

        if ( $include_fields ) {
            $item['fields'] = isset( $model['fields'] ) && is_array( $model['fields'] ) ? $model['fields'] : array();
        }

        return $item;
    }

    /**
     * Sanitize one model field.
     *
     * @param mixed               $value Raw value.
     * @param array<string,mixed> $field Field definition.
     * @return string
     */
    private function sanitize_model_field_value( $value, $field ) {
        $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
        if ( is_array( $value ) || is_object( $value ) ) {
            $value = '';
        }

        $value = trim( (string) $value );
        if ( 'checkbox' === $type ) {
            return '1' === $value ? '1' : '';
        }
        if ( 'textarea' === $type ) {
            return sanitize_textarea_field( $value );
        }
        if ( 'url' === $type ) {
            return esc_url_raw( $value, array( 'http', 'https', 'mailto', 'tel' ) );
        }
        if ( 'email' === $type ) {
            return sanitize_email( $value );
        }
        if ( 'number' === $type ) {
            return is_numeric( $value ) ? (string) $value : '';
        }
        if ( 'date' === $type ) {
            return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
        }

        return sanitize_text_field( $value );
    }

    /**
     * Get REST meta type.
     *
     * @param array<string,mixed> $field Field definition.
     * @return string
     */
    private function get_rest_meta_type( $field ) {
        $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'string';
        if ( 'checkbox' === $type ) {
            return 'boolean';
        }
        if ( 'number' === $type ) {
            return 'number';
        }

        return 'string';
    }

    /**
     * Get archive base.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return string
     */
    private static function get_archive_base( $options = null ) {
        $options = self::resolve_options( $options );
        $base = isset( $options[ self::OPTION_ARCHIVE_BASE ] ) ? sanitize_title( (string) $options[ self::OPTION_ARCHIVE_BASE ] ) : self::DEFAULT_ARCHIVE_BASE;

        return '' !== $base ? $base : self::DEFAULT_ARCHIVE_BASE;
    }

    /**
     * REST enabled flag.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return bool
     */
    private static function is_rest_enabled( $options = null ) {
        $options = self::resolve_options( $options );
        return ! isset( $options[ self::OPTION_REST_ENABLE ] ) || '1' === (string) $options[ self::OPTION_REST_ENABLE ];
    }

    /**
     * Archive enabled flag.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return bool
     */
    private static function is_archive_enabled( $options = null ) {
        $options = self::resolve_options( $options );
        return ! isset( $options[ self::OPTION_ARCHIVE_ENABLE ] ) || '1' === (string) $options[ self::OPTION_ARCHIVE_ENABLE ];
    }

    /**
     * Meta box enabled flag.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return bool
     */
    private static function is_meta_box_enabled( $options = null ) {
        $options = self::resolve_options( $options );
        return ! isset( $options[ self::OPTION_META_BOX_ENABLE ] ) || '1' === (string) $options[ self::OPTION_META_BOX_ENABLE ];
    }

    /**
     * Resolve theme options.
     *
     * @param array<string,mixed>|null $options Theme options.
     * @return array<string,mixed>
     */
    private static function resolve_options( $options = null ) {
        if ( is_array( $options ) ) {
            return $options;
        }

        $stored = get_option( 'developer_starter_options', array() );
        return is_array( $stored ) ? $stored : array();
    }
}
