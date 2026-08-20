<?php
/**
 * Decoration generation service facade.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Core\AI\Connection_Manager;
use Developer_Starter\Core\AI\Generation_Orchestrator;
use Developer_Starter\Core\AI\Prompt_Builder;
use Developer_Starter\Core\AI\Response_Parser;
use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AI_Decorator {

    const NONCE_ACTION = 'qiling_ai_decorator_nonce';
    const AJAX_ACTION_GENERATE = 'qiling_ai_generate_page_package';
    const AJAX_ACTION_PLAN = 'qiling_ai_plan_page_package';
    const AJAX_ACTION_GENERATE_MODULE = 'qiling_ai_generate_page_module';
    const AJAX_ACTION_LOCALIZE_PAGE = 'qiling_ai_localize_page_package';
    const AJAX_ACTION_BATCH_LOCALIZE = 'qiling_ai_batch_localize_content';
    const MODULE_SCHEMA_CACHE_GROUP = 'developer_starter_ai';
    const AI_SCOPE_MODULE = 'module';
    const AI_SCOPE_PAGE = 'page';
    const AI_SCOPE_SITE = 'site';
    const AI_MODE_LOCALIZATION = 'localization';
    const MAX_PAGES_PER_AI_REQUEST = 1;
    const MAX_MODULES_PER_REQUEST = 10;
    const MAX_REPEATER_ITEMS = 30;

    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @var array<string,array<string,mixed>>|null
     */
    private $module_schema_cache = null;

    /**
     * @var Connection_Manager|null
     */
    private $connection_manager = null;

    /**
     * @var object|null
     */
    private $prompt_builder = null;

    /**
     * @var Response_Parser|null
     */
    private $response_parser = null;

    /**
     * @var Builder_Data_Service|null
     */
    private $builder_data_service = null;

    /**
     * @var Generation_Orchestrator|null
     */
    private $generation_orchestrator = null;

    /**
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_ajax_' . self::AJAX_ACTION_GENERATE, array( $this, 'ajax_generate_page_package' ) );
        add_action( 'wp_ajax_' . self::AJAX_ACTION_PLAN, array( $this, 'ajax_plan_page_package' ) );
        add_action( 'wp_ajax_' . self::AJAX_ACTION_GENERATE_MODULE, array( $this, 'ajax_generate_page_module' ) );
        add_action( 'wp_ajax_' . self::AJAX_ACTION_LOCALIZE_PAGE, array( $this, 'ajax_localize_page_package' ) );
        add_action( 'wp_ajax_' . self::AJAX_ACTION_BATCH_LOCALIZE, array( $this, 'ajax_batch_localize_content' ) );
    }

    /**
     * 装修生成功能是否启用。
     *
     * @return bool
     */
    public function is_enabled() {
        return $this->get_option_value( 'ai_builder_enable', '' ) === '1';
    }

    /**
     * 获取前端 / 后台可用的客户端配置（不含敏感信息）。
     *
     * @param int $post_id 页面 ID。
     * @return array<string,mixed>
     */
    public function get_client_config( $post_id = 0 ) {
        $connections = array();
        foreach ( $this->get_connections() as $connection ) {
            if ( isset( $connection['api_key'] ) ) {
                unset( $connection['api_key'] );
            }
            $connections[] = $connection;
        }

        $default_connection_id = $this->get_connection_manager()->get_default_connection_id();
        $default_connection = $this->get_connection( $default_connection_id );

        return array(
            'enabled'                => $this->is_enabled() && ! empty( $connections ),
            'ajaxAction'             => self::AJAX_ACTION_GENERATE,
            'planAction'             => self::AJAX_ACTION_PLAN,
            'moduleAction'           => self::AJAX_ACTION_GENERATE_MODULE,
            'localizePageAction'     => self::AJAX_ACTION_LOCALIZE_PAGE,
            'batchLocalizeAction'    => self::AJAX_ACTION_BATCH_LOCALIZE,
            'nonce'                  => wp_create_nonce( self::NONCE_ACTION ),
            'connections'            => $connections,
            'defaultConnectionId'    => $default_connection_id,
            'defaultModel'           => $default_connection ? (string) $default_connection['default_model'] : '',
            'defaultMaxModules'      => $this->get_default_max_modules(),
            'defaultSystemPrompt'    => $this->get_system_prompt(),
            'defaultTemperature'     => $this->get_default_temperature(),
            'defaultMaxOutputTokens' => $this->get_default_max_output_tokens(),
            'pageSettings'           => $this->get_post_page_settings( $post_id ),
            'designSystem'           => $this->get_design_system_context( 'client' ),
            'contentModels'          => $this->get_content_model_context( 'client' ),
            'builderProtocol'        => $this->get_builder_protocol_context( 'client' ),
            'scopePolicy'            => $this->get_ai_scope_policy( 'client' ),
            'capabilities'           => $this->get_ai_capabilities( 'client' ),
            'localization'           => $this->get_ai_localization_client_config(),
        );
    }

    /**
     * 获取 Builder 协议上下文。
     *
     * @param string $mode 输出模式。
     * @return array<string,mixed>
     */
    public function get_builder_protocol_context( $mode = 'prompt' ) {
        $context = array(
            'module_schema_version' => $this->get_builder_data_service()->get_module_data_schema_version(),
            'builder_protocol_version' => $this->get_builder_data_service()->get_builder_protocol_version(),
            'reserved_field_ids'    => $this->get_builder_data_service()->get_reserved_protocol_field_ids(),
        );

        if ( 'client' === $mode ) {
            $context['notice'] = __( '高级协议保留字段当前只做底座兼容，未配置时可省略。', 'developer-starter' );
        } else {
            $context['rules'] = array(
                '如需输出高级协议字段，只能使用 reserved_field_ids 里给出的根字段',
                '没有明确高级装修需求时，可以省略所有 _ds_* 字段',
                '旧公共样式字段仍然有效，不要强行移除 module_margin_*、module_padding_*、module_bg_*',
            );
        }

        return $context;
    }

    /**
     * 获取全局样式上下文，供前端装修器与生成请求使用。
     *
     * @param string $mode 输出模式。
     * @return array<string,mixed>
     */
    public function get_design_system_context( $mode = 'prompt' ) {
        if ( ! class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            return array();
        }

        if ( 'client' === $mode ) {
            return Design_Tokens::get_client_payload();
        }

        return Design_Tokens::get_prompt_context();
    }

    /**
     * 获取通用内容模型上下文，供前端装修器与生成请求使用。
     *
     * @param string $mode 输出模式。
     * @return array<string,mixed>
     */
    public function get_content_model_context( $mode = 'prompt' ) {
        if ( ! class_exists( '\Developer_Starter\Core\Content_Model_Center' ) ) {
            return array();
        }

        if ( 'client' === $mode ) {
            return Content_Model_Center::get_client_payload();
        }

        return Content_Model_Center::get_prompt_context();
    }

    /**
     * 获取装修生成范围策略。
     *
     * 范围约束：在线生成只处理单模块、当前单页与 SEO，不做整站/多页面站点在线生成。
     *
     * @param string $mode 输出模式。
     * @return array<string,mixed>
     */
    public function get_ai_scope_policy( $mode = 'prompt' ) {
        $policy = array(
            'site_generation_allowed' => false,
            'max_pages_per_request'   => self::MAX_PAGES_PER_AI_REQUEST,
            'allowed_scopes'          => array( self::AI_SCOPE_MODULE, self::AI_SCOPE_PAGE ),
            'disallowed_scopes'       => array(
                self::AI_SCOPE_SITE,
                'whole_site',
                'multi_page_site',
                'site_package',
                'market_package',
            ),
            'allowed_tasks'           => array(
                'generate_single_module',
                'optimize_existing_module',
                'localize_single_module',
                'localize_current_page',
                'create_language_page',
                'batch_localize_existing_content',
                'localize_template_package',
                'generate_single_page',
                'optimize_existing_page',
                'seo_assist',
            ),
            'blocked_task'            => 'online_whole_site_generation',
            'reason'                  => __( '在线 AI 生成整站耗时长且体验不可控，启灵主题仅支持当前单页和单模块 AI 装修。', 'developer-starter' ),
        );

        if ( 'client' === $mode ) {
            $policy['notice'] = __( '当前工具用于当前单页或当前模块，可辅助生成 SEO 内容。', 'developer-starter' );
        } else {
            $policy['prompt_rules'] = array(
                '本次请求只能生成或优化当前单页，max_pages_per_request 固定为 1',
                '允许生成或优化单个模块，但不能生成整站、站点包、多页面站点或市场包',
                '允许对已有内容进行批量本地化，但每次请求仍只处理一个既有页面包或一个模块，不生成新的站点结构',
                '如果用户提出整站、多页面或站点包需求，只将其理解为当前页面的行业风格与内容方向，不要输出多页面结构',
                '可以输出 seo 对象，辅助当前页面的标题、描述、关键词和 Open Graph 文案',
            );
        }

        return $policy;
    }

    /**
     * 获取生成能力描述。
     *
     * @param string $mode 输出模式。
     * @return array<string,mixed>
     */
    public function get_ai_capabilities( $mode = 'prompt' ) {
        $capabilities = array(
            'generate_single_module'    => true,
            'optimize_existing_module'  => true,
            'localize_single_module'    => true,
            'localize_page'             => true,
            'create_language_page'      => true,
            'batch_localization'        => true,
            'template_package_localization' => true,
            'generate_single_page'      => true,
            'optimize_existing_page'    => true,
            'seo_assist'                => true,
            'online_whole_site_builder' => false,
        );

        if ( 'prompt' === $mode ) {
            $capabilities['notes'] = array(
                '生成单页时应参考 existing_modules，必要时优化而不是无差别重写',
                '生成单模块时应参考 current_module.existing_data，保留可复用信息并强化结构、文案、CTA 与 SEO 一致性',
                'SEO 输出只服务当前页面，不要给出全站 SEO 批处理计划',
            );
        }

        return $capabilities;
    }

    /**
     * Get client-safe localization controls.
     *
     * @return array<string,mixed>
     */
    public function get_ai_localization_client_config() {
        return array(
            'enabled'             => true,
            'mode'                => self::AI_MODE_LOCALIZATION,
            'preserveLayout'      => true,
            'supportsPage'        => true,
            'supportsBatch'       => true,
            'supportsLanguagePage'=> true,
            'providerAvailable'   => function_exists( 'xb_aifanyi_upsert_theme_localization' ),
            'providerLabel'       => __( '启灵AI多语言', 'developer-starter' ),
            'languages'           => $this->get_ai_localization_language_choices(),
            'tones'               => $this->get_ai_localization_tone_choices(),
            'industryTonePacks'   => $this->get_ai_localization_industry_tone_packs(),
            'batchContentTypes'   => $this->get_ai_localization_batch_content_types(),
            'defaultCurrency'     => 'USD',
            'rules'               => array(
                __( '支持单模块和整页本地化；整页模式仍只改已有页面包中的文案字段。', 'developer-starter' ),
                __( '只允许改文案字段，布局、样式、图片、图标、链接和数据源字段会被服务端拦截。', 'developer-starter' ),
                __( '可选择同步到启灵AI多语言译文记录，并生成对应语言页面。', 'developer-starter' ),
                __( '生成结果会先进入 diff 待应用状态，确认后才会修改当前页面。', 'developer-starter' ),
            ),
        );
    }

    /**
     * Supported AI localization target languages.
     *
     * @return array<string,string>
     */
    public function get_ai_localization_language_choices() {
        return array(
            'en' => __( '英文', 'developer-starter' ),
            'ja' => __( '日文', 'developer-starter' ),
            'ko' => __( '韩文', 'developer-starter' ),
            'fr' => __( '法文', 'developer-starter' ),
            'de' => __( '德文', 'developer-starter' ),
            'es' => __( '西班牙文', 'developer-starter' ),
        );
    }

    /**
     * Supported AI localization tone presets.
     *
     * @return array<string,string>
     */
    public function get_ai_localization_tone_choices() {
        return array(
            'professional' => __( '专业可信', 'developer-starter' ),
            'friendly'     => __( '自然友好', 'developer-starter' ),
            'concise'      => __( '简洁直接', 'developer-starter' ),
            'premium'      => __( '高端克制', 'developer-starter' ),
            'technical'    => __( '技术清晰', 'developer-starter' ),
        );
    }

    /**
     * Industry-specific tone packs for localization.
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_ai_localization_industry_tone_packs() {
        return array(
            'saas' => array(
                'label' => __( 'SaaS', 'developer-starter' ),
                'guidance' => __( '突出效率、集成、可扩展、安全合规和清晰 CTA，避免过度营销腔。', 'developer-starter' ),
            ),
            'export_trade' => array(
                'label' => __( '外贸', 'developer-starter' ),
                'guidance' => __( '强调交付能力、认证、MOQ、售后、询盘转化和目标市场贸易习惯。', 'developer-starter' ),
            ),
            'lawyer' => array(
                'label' => __( '律师', 'developer-starter' ),
                'guidance' => __( '语气稳重克制，避免保证结果，突出资质、流程、保密和咨询入口。', 'developer-starter' ),
            ),
            'medical' => array(
                'label' => __( '医疗', 'developer-starter' ),
                'guidance' => __( '避免诊断承诺和夸大疗效，突出专业资质、预约、风险提示和合规表达。', 'developer-starter' ),
            ),
            'education' => array(
                'label' => __( '教育', 'developer-starter' ),
                'guidance' => __( '突出课程成果、学习路径、师资、适合人群和报名转化，语言清楚亲和。', 'developer-starter' ),
            ),
            'local_service' => array(
                'label' => __( '本地服务', 'developer-starter' ),
                'guidance' => __( '突出服务范围、响应速度、评价信任、本地电话和预约入口。', 'developer-starter' ),
            ),
        );
    }

    /**
     * Batch localization content types.
     *
     * @return array<string,string>
     */
    public function get_ai_localization_batch_content_types() {
        return array(
            'page'             => __( '页面', 'developer-starter' ),
            'post'             => __( '文章', 'developer-starter' ),
            'faq'              => __( 'FAQ', 'developer-starter' ),
        );
    }

    /**
     * Normalize localization request params.
     *
     * @param mixed $value Raw request value.
     * @return array<string,mixed>
     */
    public function normalize_ai_localization_request( $value ) {
        $value = $this->normalize_json_array_input( $value );

        $scope = isset( $value['scope'] ) ? sanitize_key( (string) $value['scope'] ) : self::AI_SCOPE_MODULE;
        if ( ! in_array( $scope, array( self::AI_SCOPE_MODULE, self::AI_SCOPE_PAGE, 'batch', 'template_package' ), true ) ) {
            $scope = self::AI_SCOPE_MODULE;
        }

        $languages = $this->get_ai_localization_language_choices();
        $language = isset( $value['target_language'] ) ? sanitize_key( (string) $value['target_language'] ) : 'en';
        $language_aliases = array(
            'english' => 'en',
            'en_us'   => 'en',
            'en_gb'   => 'en',
            'jp'      => 'ja',
            'japanese'=> 'ja',
            'ja_jp'   => 'ja',
            'kr'      => 'ko',
            'korean'  => 'ko',
            'ko_kr'   => 'ko',
        );
        if ( isset( $language_aliases[ $language ] ) ) {
            $language = $language_aliases[ $language ];
        }
        if ( ! isset( $languages[ $language ] ) ) {
            $language = 'en';
        }

        $tones = $this->get_ai_localization_tone_choices();
        $tone = isset( $value['tone'] ) ? sanitize_key( (string) $value['tone'] ) : 'professional';
        if ( ! isset( $tones[ $tone ] ) ) {
            $tone = 'professional';
        }

        $tone_packs = $this->get_ai_localization_industry_tone_packs();
        $industry_tone_pack = isset( $value['industry_tone_pack'] ) ? sanitize_key( (string) $value['industry_tone_pack'] ) : '';
        if ( ! isset( $tone_packs[ $industry_tone_pack ] ) ) {
            $industry_tone_pack = '';
        }

        $default_currency = 'en' === $language ? 'USD' : ( 'ja' === $language ? 'JPY' : 'KRW' );
        $currency = isset( $value['currency'] ) ? strtoupper( (string) $value['currency'] ) : $default_currency;
        $currency = (string) preg_replace( '/[^A-Z]/', '', $currency );
        if ( 3 !== strlen( $currency ) ) {
            $currency = $default_currency;
        }

        $batch_types = $this->get_ai_localization_batch_content_types();
        $batch_content_types = array();
        $raw_batch_content_types = isset( $value['batch_content_types'] ) ? $value['batch_content_types'] : array();
        if ( is_string( $raw_batch_content_types ) ) {
            $raw_batch_content_types = preg_split( '/[\s,]+/', $raw_batch_content_types );
        }
        if ( is_array( $raw_batch_content_types ) ) {
            foreach ( $raw_batch_content_types as $raw_type ) {
                $type = sanitize_key( (string) $raw_type );
                if ( isset( $batch_types[ $type ] ) ) {
                    $batch_content_types[] = $type;
                }
            }
        }
        $batch_content_types = array_values( array_unique( $batch_content_types ) );
        if ( empty( $batch_content_types ) ) {
            $batch_content_types = array( 'page' );
        }

        $create_language_page = $this->normalize_bool( isset( $value['create_language_page'] ) ? $value['create_language_page'] : false, false );
        $sync_provider = $this->normalize_bool( isset( $value['sync_provider'] ) ? $value['sync_provider'] : $create_language_page, $create_language_page );

        return array(
            'scope'                 => $scope,
            'target_language'       => $language,
            'target_language_label' => $languages[ $language ],
            'target_market'         => isset( $value['target_market'] ) ? $this->truncate_text_for_log( sanitize_text_field( (string) $value['target_market'] ), 80 ) : '',
            'tone'                  => $tone,
            'tone_label'            => $tones[ $tone ],
            'currency'              => $currency,
            'industry'              => isset( $value['industry'] ) ? $this->truncate_text_for_log( sanitize_text_field( (string) $value['industry'] ), 80 ) : '',
            'industry_tone_pack'    => $industry_tone_pack,
            'industry_tone_pack_label' => '' !== $industry_tone_pack ? (string) $tone_packs[ $industry_tone_pack ]['label'] : '',
            'industry_tone_pack_guidance' => '' !== $industry_tone_pack ? (string) $tone_packs[ $industry_tone_pack ]['guidance'] : '',
            'fixed_translations'    => $this->normalize_ai_localization_translation_map( isset( $value['fixed_translations'] ) ? $value['fixed_translations'] : array() ),
            'forbidden_words'       => $this->normalize_ai_localization_term_list( isset( $value['forbidden_words'] ) ? $value['forbidden_words'] : array() ),
            'product_terms'         => $this->normalize_ai_localization_term_list( isset( $value['product_terms'] ) ? $value['product_terms'] : array() ),
            'create_language_page'  => $create_language_page,
            'sync_provider'         => $sync_provider,
            'batch_content_types'   => $batch_content_types,
            'batch_limit'           => min( 20, max( 1, absint( isset( $value['batch_limit'] ) ? $value['batch_limit'] : 5 ) ) ),
            'preserve_layout'       => true,
        );
    }

    /**
     * Normalize a term list from array, JSON, comma separated or line separated text.
     *
     * @param mixed $value Raw value.
     * @return array<int,string>
     */
    public function normalize_ai_localization_term_list( $value ) {
        $items = array();
        if ( is_string( $value ) ) {
            $decoded = json_decode( trim( $value ), true );
            if ( is_array( $decoded ) ) {
                $value = $decoded;
            } else {
                $value = preg_split( '/[\r\n,]+/', $value );
            }
        }

        if ( is_array( $value ) ) {
            foreach ( $value as $item ) {
                if ( is_array( $item ) ) {
                    $item = isset( $item['term'] ) ? $item['term'] : ( isset( $item['value'] ) ? $item['value'] : '' );
                }
                if ( ! is_scalar( $item ) ) {
                    continue;
                }

                $term = $this->truncate_text_for_log( sanitize_text_field( (string) $item ), 80 );
                if ( '' !== $term ) {
                    $items[] = $term;
                }
            }
        }

        return array_values( array_unique( $items ) );
    }

    /**
     * Normalize fixed translations from map, JSON or line-based "source=target" text.
     *
     * @param mixed $value Raw value.
     * @return array<int,array{source:string,target:string}>
     */
    public function normalize_ai_localization_translation_map( $value ) {
        if ( is_string( $value ) ) {
            $decoded = json_decode( trim( $value ), true );
            if ( is_array( $decoded ) ) {
                $value = $decoded;
            } else {
                $lines = preg_split( '/[\r\n]+/', $value );
                $pairs = array();
                foreach ( is_array( $lines ) ? $lines : array() as $line ) {
                    $line = trim( (string) $line );
                    if ( '' === $line ) {
                        continue;
                    }

                    $separator = false !== strpos( $line, '=' ) ? '=' : ( false !== strpos( $line, ':' ) ? ':' : '' );
                    if ( '' === $separator ) {
                        continue;
                    }

                    $parts = explode( $separator, $line, 2 );
                    $pairs[] = array(
                        'source' => isset( $parts[0] ) ? $parts[0] : '',
                        'target' => isset( $parts[1] ) ? $parts[1] : '',
                    );
                }
                $value = $pairs;
            }
        }

        $items = array();
        if ( is_array( $value ) ) {
            foreach ( $value as $source => $target ) {
                if ( is_array( $target ) ) {
                    $source = isset( $target['source'] ) ? $target['source'] : $source;
                    $target = isset( $target['target'] ) ? $target['target'] : '';
                }
                if ( ! is_scalar( $source ) || ! is_scalar( $target ) ) {
                    continue;
                }

                $source = $this->truncate_text_for_log( sanitize_text_field( (string) $source ), 80 );
                $target = $this->truncate_text_for_log( sanitize_text_field( (string) $target ), 120 );
                if ( '' !== $source && '' !== $target ) {
                    $items[] = array(
                        'source' => $source,
                        'target' => $target,
                    );
                }
            }
        }

        return array_values( $items );
    }

    /**
     * Build a text-only schema subset for AI localization.
     *
     * @param string $module_type Module type.
     * @return array<string,array<string,mixed>>
     */
    public function get_module_text_only_field_schema_map( $module_type ) {
        $module_type = sanitize_key( (string) $module_type );
        if ( '' === $module_type ) {
            return array();
        }

        $schemas = $this->get_module_schema_map( array( $module_type ) );
        if ( empty( $schemas[ $module_type ]['field_map'] ) || ! is_array( $schemas[ $module_type ]['field_map'] ) ) {
            return array();
        }

        return $this->filter_ai_localization_text_field_schema_map( $schemas[ $module_type ]['field_map'] );
    }

    /**
     * Filter a field schema down to localizable text fields.
     *
     * @param array<string,array<string,mixed>> $schema Field schema map.
     * @return array<string,array<string,mixed>>
     */
    public function filter_ai_localization_text_field_schema_map( $schema ) {
        $text_schema = array();
        if ( ! is_array( $schema ) ) {
            return $text_schema;
        }

        foreach ( $schema as $key => $field_schema ) {
            $key = is_string( $key ) ? $key : (string) $key;
            if ( '' === $key || ! is_array( $field_schema ) || $this->is_ai_localization_blocked_field( $key ) ) {
                continue;
            }

            $field_type = isset( $field_schema['type'] ) ? sanitize_key( (string) $field_schema['type'] ) : 'text';
            $child_schema = isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] )
                ? $this->filter_ai_localization_text_field_schema_map( $field_schema['fields'] )
                : array();

            if ( 'repeater' === $field_type ) {
                if ( ! empty( $child_schema ) ) {
                    $text_schema[ $key ] = array(
                        'id'     => $key,
                        'type'   => 'repeater',
                        'label'  => isset( $field_schema['label'] ) ? (string) $field_schema['label'] : $key,
                        'fields' => $child_schema,
                    );
                }
                continue;
            }

            if ( ! empty( $child_schema ) ) {
                $text_schema[ $key ] = array(
                    'id'     => $key,
                    'type'   => $field_type,
                    'label'  => isset( $field_schema['label'] ) ? (string) $field_schema['label'] : $key,
                    'fields' => $child_schema,
                );
                continue;
            }

            if ( $this->is_ai_localization_text_field( $key, $field_schema ) ) {
                $text_schema[ $key ] = array(
                    'id'    => $key,
                    'type'  => $field_type,
                    'label' => isset( $field_schema['label'] ) ? (string) $field_schema['label'] : $key,
                );
            }
        }

        return $text_schema;
    }

    /**
     * Whether a schema field may be localized as text.
     *
     * @param string              $key Field key.
     * @param array<string,mixed> $field_schema Field schema.
     * @return bool
     */
    public function is_ai_localization_text_field( $key, $field_schema ) {
        if ( $this->is_ai_localization_blocked_field( $key ) ) {
            return false;
        }

        $field_type = isset( $field_schema['type'] ) ? sanitize_key( (string) $field_schema['type'] ) : 'text';

        return in_array( $field_type, array( 'text', 'textarea', 'editor' ), true );
    }

    /**
     * Whether a field is blocked from AI localization.
     *
     * @param string $key Field key.
     * @return bool
     */
    public function is_ai_localization_blocked_field( $key ) {
        $key = strtolower( sanitize_key( (string) $key ) );
        if ( '' === $key || 0 === strpos( $key, '_ds_' ) ) {
            return true;
        }

        return (bool) preg_match(
            '/(^id$|_id$|ids$|slug|type$|layout|style|class|image|img|logo|avatar|icon|file|media|qrcode|url$|_url$|link$|_link$|href|source|data_source|query|taxonomy|term|category|post_|_post|count|number|columns?|order|sort|filter|ratio|aspect|width|height|margin|padding|spacing|radius|shadow|background|bg_|_bg|color|gradient|animation|effect|show_|_show|enable_|_enable|toggle|switch|visible|visibility)/',
            $key
        );
    }

    /**
     * 获取页面级设置。
     *
     * @param int $post_id 页面 ID。
     * @return array<string,mixed>
     */
    public function get_post_page_settings( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array(
                'title'              => '',
                'pageTemplate'       => $this->get_default_page_template(),
                'hidePageHeader'     => false,
                'transparentHeader'  => false,
                'enableScrollReveal' => false,
                'design'             => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? Design_Tokens::get_page_design_overrides( 0, 'builder' )
                    : array(),
                'designPreset'       => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? Design_Tokens::get_page_design_preset( 0 )
                    : '',
                'design_preset'      => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? Design_Tokens::get_page_design_preset( 0 )
                    : '',
                'footer'             => function_exists( 'developer_starter_sanitize_footer_visual_page_settings' )
                    ? developer_starter_sanitize_footer_visual_page_settings( array() )
                    : array(),
                'regionDecoration'   => function_exists( 'developer_starter_get_post_page_region_decoration' )
                    ? developer_starter_get_post_page_region_decoration( 0 )
                    : array(),
                'visualStyle'        => function_exists( 'developer_starter_sanitize_page_visual_style_settings' )
                    ? developer_starter_sanitize_page_visual_style_settings( array() )
                    : array(),
                'visual_style'       => function_exists( 'developer_starter_sanitize_page_visual_style_settings' )
                    ? developer_starter_sanitize_page_visual_style_settings( array() )
                    : array(),
                'seo'                => $this->get_post_seo_context( 0 ),
            );
        }

        $template = function_exists( 'get_page_template_slug' ) ? get_page_template_slug( $post_id ) : '';
        if ( ! is_string( $template ) || '' === trim( $template ) ) {
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        return array(
            'title'              => get_the_title( $post_id ),
            'pageTemplate'       => $this->normalize_page_template( $template ),
            'hidePageHeader'     => $this->normalize_bool( get_post_meta( $post_id, '_qiling_hide_page_header', true ), false ),
            'transparentHeader'  => $this->normalize_bool( get_post_meta( $post_id, '_qiling_transparent_header', true ), false ),
            'enableScrollReveal' => $this->normalize_bool( get_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', true ), false ),
            'design'             => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                ? Design_Tokens::get_page_design_overrides( $post_id, 'builder' )
                : array(),
            'designPreset'       => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                ? Design_Tokens::get_page_design_preset( $post_id )
                : '',
            'design_preset'      => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                ? Design_Tokens::get_page_design_preset( $post_id )
                : '',
            'footer'             => function_exists( 'developer_starter_get_post_footer_visual_settings' )
                ? developer_starter_get_post_footer_visual_settings( $post_id )
                : array(),
            'regionDecoration'   => function_exists( 'developer_starter_get_post_page_region_decoration' )
                ? developer_starter_get_post_page_region_decoration( $post_id )
                : array(),
            'visualStyle'        => function_exists( 'developer_starter_get_post_page_visual_style' )
                ? developer_starter_get_post_page_visual_style( $post_id )
                : array(),
            'visual_style'       => function_exists( 'developer_starter_get_post_page_visual_style' )
                ? developer_starter_get_post_page_visual_style( $post_id )
                : array(),
            'seo'                => $this->get_post_seo_context( $post_id ),
        );
    }

    /**
     * 获取当前文章/页面 SEO 上下文。
     *
     * @param int $post_id 文章/页面 ID。
     * @return array<string,mixed>
     */
    public function get_post_seo_context( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array(
                'title'          => '',
                'description'    => '',
                'keywords'       => '',
                'canonical'      => '',
                'noindex'        => false,
                'nofollow'       => false,
                'og_title'       => '',
                'og_description' => '',
                'og_image'       => '',
            );
        }

        return array(
            'title'          => sanitize_text_field( (string) get_post_meta( $post_id, '_developer_starter_seo_title', true ) ),
            'description'    => sanitize_textarea_field( (string) get_post_meta( $post_id, '_developer_starter_seo_description', true ) ),
            'keywords'       => sanitize_text_field( (string) get_post_meta( $post_id, '_developer_starter_seo_keywords', true ) ),
            'canonical'      => esc_url_raw( (string) get_post_meta( $post_id, '_developer_starter_seo_canonical', true ) ),
            'noindex'        => $this->normalize_bool( get_post_meta( $post_id, '_developer_starter_seo_noindex', true ), false ),
            'nofollow'       => $this->normalize_bool( get_post_meta( $post_id, '_developer_starter_seo_nofollow', true ), false ),
            'og_title'       => sanitize_text_field( (string) get_post_meta( $post_id, '_developer_starter_og_title', true ) ),
            'og_description' => sanitize_textarea_field( (string) get_post_meta( $post_id, '_developer_starter_og_description', true ) ),
            'og_image'       => esc_url_raw( (string) get_post_meta( $post_id, '_developer_starter_og_image', true ) ),
        );
    }

    /**
     * 获取当前页面已有模块摘要，供优化已有装修时参考。
     *
     * @param int $post_id 页面 ID。
     * @param int $limit   模块数量上限。
     * @return array<int,array<string,mixed>>
     */
    public function get_existing_modules_context( $post_id, $limit = 12 ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array();
        }

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );

        if ( empty( $modules ) && function_exists( 'developer_starter_get_page_modules_data' ) ) {
            $modules = developer_starter_get_page_modules_data( $post_id );
        }

        if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
            $modules = developer_starter_normalize_modules_meta_types( $modules );
        }

        if ( ! is_array( $modules ) || empty( $modules ) ) {
            return array();
        }

        $limit = max( 1, min( 30, absint( $limit ) ) );
        $module_ids = array();
        foreach ( $modules as $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                continue;
            }
            $module_ids[] = sanitize_key( (string) $module['type'] );
        }
        $schemas = $this->get_module_schema_map( $module_ids );

        $items = array();
        foreach ( array_slice( array_values( $modules ), 0, $limit ) as $index => $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                continue;
            }

            $type = sanitize_key( (string) $module['type'] );
            if ( '' === $type ) {
                continue;
            }

            $data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array();
            $items[] = array(
                'index'   => $index,
                'type'    => $type,
                'name'    => isset( $schemas[ $type ]['name'] ) ? (string) $schemas[ $type ]['name'] : $type,
                'summary' => $this->collect_module_context_values( $data ),
            );
        }

        return $items;
    }

    /**
     * 保存页面级设置。
     *
     * @param int                 $post_id 页面 ID。
     * @param array<string,mixed> $settings 页面设置。
     * @return void
     */
    public function persist_post_page_settings( $post_id, $settings ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 || ! is_array( $settings ) ) {
            return;
        }

        if ( isset( $settings['title'] ) && is_scalar( $settings['title'] ) ) {
            $title = sanitize_text_field( (string) $settings['title'] );
            if ( '' !== $title ) {
                $current_post = get_post( $post_id );
                if ( $current_post instanceof \WP_Post && $current_post->post_title !== $title ) {
                    wp_update_post(
                        array(
                            'ID'         => $post_id,
                            'post_title' => $title,
                        )
                    );
                }
            }
        }

        $page_template = isset( $settings['pageTemplate'] ) ? $this->normalize_page_template( $settings['pageTemplate'] ) : '';
        if ( '' !== $page_template ) {
            update_post_meta( $post_id, '_wp_page_template', $page_template );
        }

        if ( isset( $settings['hidePageHeader'] ) ) {
            if ( $this->normalize_bool( $settings['hidePageHeader'], false ) ) {
                update_post_meta( $post_id, '_qiling_hide_page_header', '1' );
            } else {
                delete_post_meta( $post_id, '_qiling_hide_page_header' );
            }
        }

        if ( isset( $settings['transparentHeader'] ) ) {
            if ( $this->normalize_bool( $settings['transparentHeader'], false ) ) {
                update_post_meta( $post_id, '_qiling_transparent_header', '1' );
            } else {
                delete_post_meta( $post_id, '_qiling_transparent_header' );
            }
        }

        if ( isset( $settings['enableScrollReveal'] ) ) {
            update_post_meta(
                $post_id,
                '_developer_starter_enable_scroll_reveal',
                $this->normalize_bool( $settings['enableScrollReveal'], false ) ? '1' : '0'
            );
        }

        if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            $page_design = array();
            if ( isset( $settings['design'] ) && is_array( $settings['design'] ) ) {
                $page_design = $settings['design'];
            } elseif ( isset( $settings['pageDesign'] ) && is_array( $settings['pageDesign'] ) ) {
                $page_design = $settings['pageDesign'];
            } elseif ( isset( $settings['page_design'] ) && is_array( $settings['page_design'] ) ) {
                $page_design = $settings['page_design'];
            }

            Design_Tokens::persist_page_design_overrides( $post_id, $page_design );

            if ( isset( $settings['designPreset'] ) || isset( $settings['design_preset'] ) || isset( $settings['page_design_preset'] ) ) {
                $design_preset = '';
                if ( isset( $settings['designPreset'] ) ) {
                    $design_preset = $settings['designPreset'];
                } elseif ( isset( $settings['design_preset'] ) ) {
                    $design_preset = $settings['design_preset'];
                } elseif ( isset( $settings['page_design_preset'] ) ) {
                    $design_preset = $settings['page_design_preset'];
                }

                Design_Tokens::persist_page_design_preset( $post_id, $design_preset );
            }
        }

        if ( isset( $settings['footer'] ) && function_exists( 'developer_starter_persist_post_footer_visual_settings' ) ) {
            developer_starter_persist_post_footer_visual_settings( $post_id, $settings['footer'] );
        } elseif ( isset( $settings['footer_settings'] ) && function_exists( 'developer_starter_persist_post_footer_visual_settings' ) ) {
            developer_starter_persist_post_footer_visual_settings( $post_id, $settings['footer_settings'] );
        }

        if ( isset( $settings['regionDecoration'] ) && function_exists( 'developer_starter_persist_post_page_region_decoration' ) ) {
            developer_starter_persist_post_page_region_decoration( $post_id, $settings['regionDecoration'] );
        } elseif ( isset( $settings['region_decoration'] ) && function_exists( 'developer_starter_persist_post_page_region_decoration' ) ) {
            developer_starter_persist_post_page_region_decoration( $post_id, $settings['region_decoration'] );
        }

        if ( isset( $settings['visualStyle'] ) && function_exists( 'developer_starter_persist_post_page_visual_style' ) ) {
            developer_starter_persist_post_page_visual_style( $post_id, $settings['visualStyle'] );
        } elseif ( isset( $settings['visual_style'] ) && function_exists( 'developer_starter_persist_post_page_visual_style' ) ) {
            developer_starter_persist_post_page_visual_style( $post_id, $settings['visual_style'] );
        }

        if ( isset( $settings['seo'] ) && is_array( $settings['seo'] ) ) {
            $seo = $this->sanitize_ai_seo_payload( $settings['seo'] );
            update_post_meta( $post_id, '_developer_starter_seo_title', $seo['title'] );
            update_post_meta( $post_id, '_developer_starter_seo_description', $seo['description'] );
            update_post_meta( $post_id, '_developer_starter_seo_keywords', $seo['keywords'] );
            update_post_meta( $post_id, '_developer_starter_og_title', $seo['og_title'] );
            update_post_meta( $post_id, '_developer_starter_og_description', $seo['og_description'] );
        }
    }

    /**
     * Ajax: 生成页面 JSON 草稿。
     *
     * @return void
     */
    public function ajax_generate_page_package() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足，仅管理员可使用 AI 装修。', 'developer-starter' ) ), 403 );
        }

        if ( ! $this->is_enabled() ) {
            wp_send_json_error( array( 'message' => __( 'AI 装修尚未在主题设置中启用。', 'developer-starter' ) ), 400 );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '当前页面无编辑权限。', 'developer-starter' ) ), 403 );
        }

        $prompt = isset( $_POST['prompt'] ) ? trim( wp_unslash( (string) $_POST['prompt'] ) ) : '';
        $scope = isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( (string) $_POST['scope'] ) ) : self::AI_SCOPE_PAGE;
        if ( $this->is_disallowed_ai_scope( $scope ) ) {
            $error = $this->get_disallowed_site_generation_error();
            wp_send_json_error( array( 'message' => $error->get_error_message() ), 400 );
        }
        $connection_id = isset( $_POST['connection_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['connection_id'] ) ) : '';
        $model = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model'] ) ) : '';
        $module_ids = $this->normalize_module_ids_input( isset( $_POST['module_ids'] ) ? wp_unslash( $_POST['module_ids'] ) : array() );

        $result = $this->generate_page_package(
            array(
                'post_id'       => $post_id,
                'prompt'        => $prompt,
                'connection_id' => $connection_id,
                'model'         => $model,
                'module_ids'    => $module_ids,
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
        }

        wp_send_json_success( $result );
    }

    /**
     * Ajax: 规划页面结构。
     *
     * @return void
     */
    public function ajax_plan_page_package() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足，仅管理员可使用 AI 装修。', 'developer-starter' ) ), 403 );
        }

        if ( ! $this->is_enabled() ) {
            wp_send_json_error( array( 'message' => __( 'AI 装修尚未在主题设置中启用。', 'developer-starter' ) ), 400 );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '当前页面无编辑权限。', 'developer-starter' ) ), 403 );
        }

        $scope = isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( (string) $_POST['scope'] ) ) : self::AI_SCOPE_PAGE;
        if ( $this->is_disallowed_ai_scope( $scope ) ) {
            $error = $this->get_disallowed_site_generation_error();
            wp_send_json_error( array( 'message' => $error->get_error_message() ), 400 );
        }

        $result = $this->plan_page_package(
            array(
                'post_id'       => $post_id,
                'prompt'        => isset( $_POST['prompt'] ) ? trim( wp_unslash( (string) $_POST['prompt'] ) ) : '',
                'connection_id' => isset( $_POST['connection_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['connection_id'] ) ) : '',
                'model'         => isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model'] ) ) : '',
                'module_ids'    => $this->normalize_module_ids_input( isset( $_POST['module_ids'] ) ? wp_unslash( $_POST['module_ids'] ) : array() ),
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
        }

        wp_send_json_success( $result );
    }

    /**
     * Ajax: 生成单个模块 JSON。
     *
     * @return void
     */
    public function ajax_generate_page_module() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足，仅管理员可使用 AI 装修。', 'developer-starter' ) ), 403 );
        }

        if ( ! $this->is_enabled() ) {
            wp_send_json_error( array( 'message' => __( 'AI 装修尚未在主题设置中启用。', 'developer-starter' ) ), 400 );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '当前页面无编辑权限。', 'developer-starter' ) ), 403 );
        }

        $scope = isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( (string) $_POST['scope'] ) ) : self::AI_SCOPE_MODULE;
        if ( $this->is_disallowed_ai_scope( $scope ) ) {
            $error = $this->get_disallowed_site_generation_error();
            wp_send_json_error( array( 'message' => $error->get_error_message() ), 400 );
        }

        $result = $this->generate_page_module(
            array(
                'post_id'             => $post_id,
                'mode'                => isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( (string) $_POST['mode'] ) ) : '',
                'prompt'              => isset( $_POST['prompt'] ) ? trim( wp_unslash( (string) $_POST['prompt'] ) ) : '',
                'connection_id'       => isset( $_POST['connection_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['connection_id'] ) ) : '',
                'model'               => isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model'] ) ) : '',
                'module_ids'          => $this->normalize_module_ids_input( isset( $_POST['module_ids'] ) ? wp_unslash( $_POST['module_ids'] ) : array() ),
                'plan'                => isset( $_POST['plan'] ) ? wp_unslash( $_POST['plan'] ) : array(),
                'current_module_type' => isset( $_POST['current_module_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['current_module_type'] ) ) : '',
                'current_module_data' => isset( $_POST['current_module_data'] ) ? wp_unslash( $_POST['current_module_data'] ) : array(),
                'completed_modules'   => isset( $_POST['completed_modules'] ) ? wp_unslash( $_POST['completed_modules'] ) : array(),
                'localization'        => isset( $_POST['localization'] ) ? wp_unslash( $_POST['localization'] ) : array(),
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
        }

        wp_send_json_success( $result );
    }

    /**
     * Ajax: localize the current page package.
     *
     * @return void
     */
    public function ajax_localize_page_package() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足，仅管理员可使用 AI 装修。', 'developer-starter' ) ), 403 );
        }

        if ( ! $this->is_enabled() ) {
            wp_send_json_error( array( 'message' => __( 'AI 装修尚未在主题设置中启用。', 'developer-starter' ) ), 400 );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( (string) $_POST['post_id'] ) ) : 0;
        if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( '当前页面无编辑权限。', 'developer-starter' ) ), 403 );
        }

        $result = $this->localize_page_package(
            array(
                'post_id'         => $post_id,
                'prompt'          => isset( $_POST['prompt'] ) ? trim( wp_unslash( (string) $_POST['prompt'] ) ) : '',
                'connection_id'   => isset( $_POST['connection_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['connection_id'] ) ) : '',
                'model'           => isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model'] ) ) : '',
                'current_package' => isset( $_POST['current_package'] ) ? wp_unslash( $_POST['current_package'] ) : array(),
                'localization'    => isset( $_POST['localization'] ) ? wp_unslash( $_POST['localization'] ) : array(),
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
        }

        wp_send_json_success( $result );
    }

    /**
     * Ajax: batch localize existing content.
     *
     * @return void
     */
    public function ajax_batch_localize_content() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足，仅管理员可使用 AI 装修。', 'developer-starter' ) ), 403 );
        }

        if ( ! $this->is_enabled() ) {
            wp_send_json_error( array( 'message' => __( 'AI 装修尚未在主题设置中启用。', 'developer-starter' ) ), 400 );
        }

        $result = $this->batch_localize_content(
            array(
                'prompt'        => isset( $_POST['prompt'] ) ? trim( wp_unslash( (string) $_POST['prompt'] ) ) : '',
                'connection_id' => isset( $_POST['connection_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['connection_id'] ) ) : '',
                'model'         => isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model'] ) ) : '',
                'post_ids'      => isset( $_POST['post_ids'] ) ? wp_unslash( $_POST['post_ids'] ) : array(),
                'localization'  => isset( $_POST['localization'] ) ? wp_unslash( $_POST['localization'] ) : array(),
            )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
        }

        wp_send_json_success( $result );
    }

    /**
     * 规划页面结构。
     *
     * @param array<string,mixed> $args 请求参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function plan_page_package( $args ) {
        return $this->get_generation_orchestrator()->plan_page_package( $args );
    }

    /**
     * 生成单个页面模块数据。
     *
     * @param array<string,mixed> $args 请求参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function generate_page_module( $args ) {
        return $this->get_generation_orchestrator()->generate_page_module( $args );
    }

    /**
     * 生成页面装修草稿。
     *
     * @param array<string,mixed> $args 请求参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function generate_page_package( $args ) {
        return $this->get_generation_orchestrator()->generate_page_package( $args );
    }

    /**
     * Localize a full page package.
     *
     * @param array<string,mixed> $args Request args.
     * @return array<string,mixed>|\WP_Error
     */
    public function localize_page_package( $args ) {
        return $this->get_generation_orchestrator()->localize_page_package( $args );
    }

    /**
     * Batch localize existing content.
     *
     * @param array<string,mixed> $args Request args.
     * @return array<string,mixed>|\WP_Error
     */
    public function batch_localize_content( $args ) {
        return $this->get_generation_orchestrator()->batch_localize_content( $args );
    }

    /**
     * Get default connection/model values for server-side integrations.
     *
     * @return array{connection_id:string,model:string}
     */
    public function get_default_ai_connection_request_args() {
        $connection_id = $this->get_connection_manager()->get_default_connection_id();
        $connection = $this->get_connection( $connection_id );

        return array(
            'connection_id' => (string) $connection_id,
            'model'         => $connection && ! empty( $connection['default_model'] ) ? (string) $connection['default_model'] : '',
        );
    }

    /**
     * 测试生成连接是否可用。
     *
     * @param array<string,mixed> $args 测试参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function test_connection( $args ) {
        return $this->get_connection_manager()->test_connection( $args );
    }

    /**
     * 获取连接配置。
     *
     * @param string $connection_id 连接 ID。
     * @return array<string,mixed>|null
     */
    public function get_connection( $connection_id ) {
        return $this->get_connection_manager()->get_connection( $connection_id );
    }

    /**
     * 获取启用的连接列表。
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_connections() {
        return $this->get_connection_manager()->get_connections();
    }

    /**
     * 规范化模块 ID 输入。
     *
     * @param mixed $module_ids 原始输入。
     * @return array<int,string>
     */
    public function normalize_module_ids_input( $module_ids ) {
        if ( is_string( $module_ids ) ) {
            $trimmed = trim( $module_ids );
            if ( '' === $trimmed ) {
                $module_ids = array();
            } else {
                $decoded = json_decode( $trimmed, true );
                if ( is_array( $decoded ) ) {
                    $module_ids = $decoded;
                } else {
                    $module_ids = preg_split( '/[\s,]+/', $trimmed );
                }
            }
        }

        if ( ! is_array( $module_ids ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $module_ids as $module_id ) {
            $module_id = sanitize_key( (string) $module_id );
            if ( '' !== $module_id ) {
                $normalized[] = $module_id;
            }
        }

        return array_values( array_unique( $normalized ) );
    }

    /**
     * 规范化任意 JSON / 数组输入。
     *
     * @param mixed $value 输入值。
     * @return array<int|string,mixed>
     */
    public function normalize_json_array_input( $value ) {
        if ( is_string( $value ) ) {
            $trimmed = trim( $value );
            if ( '' === $trimmed ) {
                return array();
            }

            $decoded = json_decode( $trimmed, true );
            if ( is_array( $decoded ) ) {
                return $decoded;
            }

            return array();
        }

        return is_array( $value ) ? $value : array();
    }

    /**
     * Build a normalized page package from request input or the saved post state.
     *
     * @param int   $post_id Current post ID.
     * @param mixed $value Request page package.
     * @return array<string,mixed>
     */
    public function normalize_ai_page_package_input( $post_id, $value = array() ) {
        $post_id = absint( $post_id );
        $input = $this->normalize_json_array_input( $value );
        $settings = $this->get_post_page_settings( $post_id );

        $saved_modules = array();
        if ( $post_id > 0 ) {
            $saved_modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
                ? developer_starter_get_raw_page_modules_meta( $post_id )
                : get_post_meta( $post_id, '_developer_starter_modules', true );
            if ( empty( $saved_modules ) && function_exists( 'developer_starter_get_page_modules_data' ) ) {
                $saved_modules = developer_starter_get_page_modules_data( $post_id );
            }
            if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
                $saved_modules = developer_starter_normalize_modules_meta_types( $saved_modules );
            }
        }

        $raw_modules = isset( $input['modules'] ) && is_array( $input['modules'] )
            ? $input['modules']
            : ( is_array( $saved_modules ) ? $saved_modules : array() );

        $modules = array();
        foreach ( array_values( $raw_modules ) as $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                continue;
            }

            $type = sanitize_key( (string) $module['type'] );
            if ( '' === $type ) {
                continue;
            }

            $modules[] = array(
                'type'                   => $type,
                'data'                   => isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array(),
                'schemaVersion'          => isset( $module['schemaVersion'] ) && is_scalar( $module['schemaVersion'] ) ? sanitize_text_field( (string) $module['schemaVersion'] ) : $this->get_builder_data_service()->get_module_data_schema_version(),
                'builderProtocolVersion' => isset( $module['builderProtocolVersion'] ) && is_scalar( $module['builderProtocolVersion'] ) ? sanitize_text_field( (string) $module['builderProtocolVersion'] ) : $this->get_builder_data_service()->get_builder_protocol_version(),
            );
        }

        $seo = array();
        if ( isset( $input['seo'] ) && is_array( $input['seo'] ) ) {
            $seo = $this->sanitize_ai_seo_payload( $input['seo'] );
        } elseif ( isset( $settings['seo'] ) && is_array( $settings['seo'] ) ) {
            $seo = $this->sanitize_ai_seo_payload( $settings['seo'] );
        }

        return array(
            'title'                    => isset( $input['title'] ) && is_scalar( $input['title'] )
                ? sanitize_text_field( (string) $input['title'] )
                : ( isset( $settings['title'] ) ? sanitize_text_field( (string) $settings['title'] ) : '' ),
            'page_template'            => isset( $input['page_template'] )
                ? $this->normalize_page_template( $input['page_template'] )
                : ( isset( $input['pageTemplate'] ) ? $this->normalize_page_template( $input['pageTemplate'] ) : ( isset( $settings['pageTemplate'] ) ? $this->normalize_page_template( $settings['pageTemplate'] ) : $this->get_default_page_template() ) ),
            'hide_page_header'         => isset( $input['hide_page_header'] )
                ? $this->normalize_bool( $input['hide_page_header'], false )
                : ( isset( $input['hidePageHeader'] ) ? $this->normalize_bool( $input['hidePageHeader'], false ) : ( ! empty( $settings['hidePageHeader'] ) ) ),
            'transparent_header'       => isset( $input['transparent_header'] )
                ? $this->normalize_bool( $input['transparent_header'], false )
                : ( isset( $input['transparentHeader'] ) ? $this->normalize_bool( $input['transparentHeader'], false ) : ( ! empty( $settings['transparentHeader'] ) ) ),
            'enable_scroll_reveal'     => isset( $input['enable_scroll_reveal'] )
                ? $this->normalize_bool( $input['enable_scroll_reveal'], false )
                : ( isset( $input['enableScrollReveal'] ) ? $this->normalize_bool( $input['enableScrollReveal'], false ) : ( ! empty( $settings['enableScrollReveal'] ) ) ),
            'seo'                      => $seo,
            'module_schema_version'    => $this->get_builder_data_service()->get_module_data_schema_version(),
            'builder_protocol_version' => $this->get_builder_data_service()->get_builder_protocol_version(),
            'modules'                  => $modules,
        );
    }

    /**
     * Build text-only schema maps for every module in a page package.
     *
     * @param array<int,array<string,mixed>> $modules Modules.
     * @return array<int,array<string,mixed>>
     */
    public function get_page_package_text_only_schema_map( $modules ) {
        $items = array();
        foreach ( is_array( $modules ) ? array_values( $modules ) : array() as $index => $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                continue;
            }

            $type = sanitize_key( (string) $module['type'] );
            if ( '' === $type ) {
                continue;
            }

            $items[ $index ] = array(
                'type'   => $type,
                'fields' => $this->get_module_text_only_field_schema_map( $type ),
            );
        }

        return $items;
    }

    /**
     * Get unique module IDs from a page package.
     *
     * @param array<string,mixed> $package Page package.
     * @return array<int,string>
     */
    public function get_module_ids_from_page_package( $package ) {
        $module_ids = array();
        $modules = isset( $package['modules'] ) && is_array( $package['modules'] ) ? $package['modules'] : array();
        foreach ( $modules as $module ) {
            if ( is_array( $module ) && ! empty( $module['type'] ) ) {
                $module_ids[] = sanitize_key( (string) $module['type'] );
            }
        }

        return array_values( array_unique( array_filter( $module_ids ) ) );
    }

    /**
     * Sync a localized page package to the xb-aifanyi provider when available.
     *
     * @param int                 $post_id Current post ID.
     * @param array<string,mixed> $package Localized package.
     * @param array<string,mixed> $localization Localization args.
     * @return array<string,mixed>
     */
    public function sync_localized_package_to_aifanyi( $post_id, $package, $localization ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array(
                'success' => false,
                'provider' => 'xb-aifanyi-translator',
                'message' => __( '缺少内容 ID，无法同步到启灵AI多语言。', 'developer-starter' ),
            );
        }

        if ( ! function_exists( 'xb_aifanyi_upsert_theme_localization' ) ) {
            return array(
                'success' => false,
                'provider' => 'xb-aifanyi-translator',
                'message' => __( '启灵AI多语言插件未启用，已仅生成本地化页面包。', 'developer-starter' ),
            );
        }

        $post = get_post( $post_id );
        $payload = array(
            'title'                => isset( $package['title'] ) ? (string) $package['title'] : '',
            'content'              => $post instanceof \WP_Post ? (string) $post->post_content : '',
            'excerpt'              => $post instanceof \WP_Post ? (string) $post->post_excerpt : '',
            'seo'                  => isset( $package['seo'] ) && is_array( $package['seo'] ) ? $package['seo'] : array(),
            'modules'              => isset( $package['modules'] ) && is_array( $package['modules'] ) ? $package['modules'] : array(),
            'create_language_page' => ! empty( $localization['create_language_page'] ),
        );

        $result = xb_aifanyi_upsert_theme_localization(
            $post_id,
            isset( $localization['target_language'] ) ? (string) $localization['target_language'] : 'en',
            $payload
        );

        return is_array( $result ) ? $result : array(
            'success' => false,
            'provider' => 'xb-aifanyi-translator',
            'message' => __( '启灵AI多语言 provider 返回异常。', 'developer-starter' ),
        );
    }

    /**
     * Sync localized plain post/article content to xb-aifanyi.
     *
     * @param int                 $post_id Current post ID.
     * @param array<string,mixed> $content Localized content payload.
     * @param array<string,mixed> $localization Localization args.
     * @return array<string,mixed>
     */
    public function sync_localized_content_to_aifanyi( $post_id, $content, $localization ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array(
                'success' => false,
                'provider' => 'xb-aifanyi-translator',
                'message' => __( '缺少内容 ID，无法同步到启灵AI多语言。', 'developer-starter' ),
            );
        }

        if ( ! function_exists( 'xb_aifanyi_upsert_theme_localization' ) ) {
            return array(
                'success' => false,
                'provider' => 'xb-aifanyi-translator',
                'message' => __( '启灵AI多语言插件未启用，已仅生成本地化内容。', 'developer-starter' ),
            );
        }

        $payload = array(
            'title'                => isset( $content['title'] ) ? (string) $content['title'] : '',
            'content'              => isset( $content['content'] ) ? (string) $content['content'] : '',
            'excerpt'              => isset( $content['excerpt'] ) ? (string) $content['excerpt'] : '',
            'seo'                  => isset( $content['seo'] ) && is_array( $content['seo'] ) ? $content['seo'] : array(),
            'create_language_page' => ! empty( $localization['create_language_page'] ),
        );

        $result = xb_aifanyi_upsert_theme_localization(
            $post_id,
            isset( $localization['target_language'] ) ? (string) $localization['target_language'] : 'en',
            $payload
        );

        return is_array( $result ) ? $result : array(
            'success' => false,
            'provider' => 'xb-aifanyi-translator',
            'message' => __( '启灵AI多语言 provider 返回异常。', 'developer-starter' ),
        );
    }

    /**
     * 清洗生成输出的当前页面 SEO 建议。
     *
     * @param mixed $seo SEO 输入。
     * @return array<string,string>
     */
    public function sanitize_ai_seo_payload( $seo ) {
        $seo = is_array( $seo ) ? $seo : array();

        $title = '';
        foreach ( array( 'title', 'seo_title', 'meta_title' ) as $key ) {
            if ( isset( $seo[ $key ] ) && is_scalar( $seo[ $key ] ) ) {
                $title = sanitize_text_field( (string) $seo[ $key ] );
                break;
            }
        }

        $description = '';
        foreach ( array( 'description', 'seo_description', 'meta_description', 'desc' ) as $key ) {
            if ( isset( $seo[ $key ] ) && is_scalar( $seo[ $key ] ) ) {
                $description = sanitize_textarea_field( (string) $seo[ $key ] );
                break;
            }
        }

        $keywords = '';
        foreach ( array( 'keywords', 'seo_keywords', 'focus_keywords', 'keyword' ) as $key ) {
            if ( ! isset( $seo[ $key ] ) ) {
                continue;
            }

            if ( is_array( $seo[ $key ] ) ) {
                $keywords = implode( ',', array_filter( array_map( 'sanitize_text_field', array_map( 'strval', $seo[ $key ] ) ) ) );
            } elseif ( is_scalar( $seo[ $key ] ) ) {
                $keywords = sanitize_text_field( (string) $seo[ $key ] );
            }
            break;
        }
        $keywords = str_replace( '，', ',', $keywords );
        $keywords = preg_replace( '/\s*,\s*/', ',', (string) $keywords );
        $keywords = preg_replace( '/,+/', ',', (string) $keywords );
        $keywords = trim( (string) $keywords, " \t\n\r\0\x0B," );

        $og_title = '';
        foreach ( array( 'og_title', 'open_graph_title' ) as $key ) {
            if ( isset( $seo[ $key ] ) && is_scalar( $seo[ $key ] ) ) {
                $og_title = sanitize_text_field( (string) $seo[ $key ] );
                break;
            }
        }

        $og_description = '';
        foreach ( array( 'og_description', 'open_graph_description' ) as $key ) {
            if ( isset( $seo[ $key ] ) && is_scalar( $seo[ $key ] ) ) {
                $og_description = sanitize_textarea_field( (string) $seo[ $key ] );
                break;
            }
        }

        return array(
            'title'          => $this->truncate_text_for_log( $title, 80 ),
            'description'    => $this->truncate_text_for_log( $description, 180 ),
            'keywords'       => $this->truncate_text_for_log( $keywords, 160 ),
            'og_title'       => $this->truncate_text_for_log( $og_title, 80 ),
            'og_description' => $this->truncate_text_for_log( $og_description, 180 ),
        );
    }

    /**
     * 判断用户需求是否明确要求在线整站/多页面生成。
     *
     * @param string $prompt 用户需求。
     * @return bool
     */
    public function is_disallowed_site_generation_prompt( $prompt ) {
        $prompt = trim( (string) $prompt );
        if ( '' === $prompt ) {
            return false;
        }

        $needles = array(
            '整站',
            '全站',
            '站点包',
            '网站包',
            '整套网站',
            '完整网站',
            '全套页面',
            '多个页面',
            '多页面',
            '所有页面',
            '批量页面',
            '一键建站',
            'whole site',
            'full website',
            'entire site',
            'site package',
            'multi-page',
            'multiple pages',
            'all pages',
        );

        $lower_prompt = strtolower( $prompt );
        foreach ( $needles as $needle ) {
            if ( false !== strpos( $lower_prompt, strtolower( $needle ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断请求 scope 是否为已禁用的在线整站范围。
     *
     * @param string $scope 请求范围。
     * @return bool
     */
    public function is_disallowed_ai_scope( $scope ) {
        $scope = sanitize_key( (string) $scope );
        if ( '' === $scope ) {
            return false;
        }

        return in_array(
            $scope,
            array(
                self::AI_SCOPE_SITE,
                'whole_site',
                'multi_page_site',
                'site_package',
                'market_package',
            ),
            true
        );
    }

    /**
     * 明确整站生成被禁用时的错误对象。
     *
     * @return \WP_Error
     */
    public function get_disallowed_site_generation_error() {
        return new \WP_Error(
            'online_whole_site_generation_disabled',
            __( 'AI 在线整站生成已关闭。请改为生成当前单页、优化当前单页，或选中单个模块后做模块优化。', 'developer-starter' )
        );
    }

    /**
     * 获取可用于生成请求的模块 schema。
     *
     * @param array<int,string> $module_ids 模块 ID。
     * @return array<string,array<string,mixed>>
     */
    public function get_selected_module_prompt_schemas( $module_ids ) {
        $schemas = $this->get_module_schema_map( $module_ids );
        $selected = array();

        foreach ( $module_ids as $module_id ) {
            if ( isset( $schemas[ $module_id ] ) ) {
                $selected[ $module_id ] = array(
                    'id'          => $module_id,
                    'name'        => $schemas[ $module_id ]['name'],
                    'fields'      => $schemas[ $module_id ]['prompt_fields'],
                    'defaultData' => $schemas[ $module_id ]['default_data'],
                    'metadata'    => isset( $schemas[ $module_id ]['metadata'] ) && is_array( $schemas[ $module_id ]['metadata'] ) ? $schemas[ $module_id ]['metadata'] : array(),
                );
            }
        }

        return $selected;
    }

    /**
     * 获取模块 schema 缓存。
     *
     * @param array<int,string> $allowed_module_ids 允许模块。
     * @return array<string,array<string,mixed>>
     */
    public function get_module_schema_map( $allowed_module_ids = array() ) {
        if ( null === $this->module_schema_cache ) {
            $this->module_schema_cache = array();
        }

        $allowed_module_ids = array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_key', is_array( $allowed_module_ids ) ? $allowed_module_ids : array() )
                )
            )
        );

        if ( empty( $allowed_module_ids ) ) {
            $fresh_schemas = $this->build_module_schema_entries();
            foreach ( $fresh_schemas as $module_id => $schema ) {
                $this->module_schema_cache[ $module_id ] = $schema;
                $this->store_persistent_module_schema_entry( $module_id, $schema );
            }

            return $this->module_schema_cache;
        }

        $filtered = array();
        $missing_module_ids = array();

        foreach ( $allowed_module_ids as $module_id ) {
            if ( isset( $this->module_schema_cache[ $module_id ] ) ) {
                $filtered[ $module_id ] = $this->module_schema_cache[ $module_id ];
                continue;
            }

            $cached_schema = $this->get_persistent_module_schema_entry( $module_id );
            if ( null !== $cached_schema ) {
                $this->module_schema_cache[ $module_id ] = $cached_schema;
                $filtered[ $module_id ] = $cached_schema;
                continue;
            }

            $missing_module_ids[] = $module_id;
        }

        if ( ! empty( $missing_module_ids ) ) {
            $fresh_schemas = $this->build_module_schema_entries( $missing_module_ids );
            foreach ( $fresh_schemas as $module_id => $schema ) {
                $this->module_schema_cache[ $module_id ] = $schema;
                $filtered[ $module_id ] = $schema;
                $this->store_persistent_module_schema_entry( $module_id, $schema );
            }
        }

        $ordered = array();
        foreach ( $allowed_module_ids as $module_id ) {
            if ( isset( $filtered[ $module_id ] ) ) {
                $ordered[ $module_id ] = $filtered[ $module_id ];
            }
        }

        return $ordered;
    }

    /**
     * 转换字段白名单。
     *
     * @param array<int,array<string,mixed>> $fields 字段定义。
     * @return array<string,array<string,mixed>>
     */
    public function build_field_schema_map( $fields ) {
        return $this->get_builder_data_service()->build_module_data_schema_map( $fields );
    }

    /**
     * 转换字段用于生成请求。
     *
     * @param array<int,array<string,mixed>> $fields 字段定义。
     * @return array<int,array<string,mixed>>
     */
    public function sanitize_field_schema_for_prompt( $fields ) {
        $schema = array();
        if ( ! is_array( $fields ) ) {
            return $schema;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) ) {
                continue;
            }

            $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
            $id = isset( $field['id'] ) ? sanitize_key( (string) $field['id'] ) : '';
            $label = isset( $field['label'] ) ? wp_strip_all_tags( (string) $field['label'] ) : '';

            if ( '' === $id && ! in_array( $type, array( 'header', 'info' ), true ) ) {
                continue;
            }

            $item = array(
                'type'  => $type,
                'id'    => $id,
                'label' => $label,
            );

            if ( isset( $field['default'] ) && is_scalar( $field['default'] ) ) {
                $item['default'] = (string) $field['default'];
            }

            if ( isset( $field['description'] ) ) {
                $item['description'] = wp_strip_all_tags( (string) $field['description'] );
            } elseif ( isset( $field['desc'] ) ) {
                $item['description'] = wp_strip_all_tags( (string) $field['desc'] );
            }

            if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                $options = array();
                foreach ( $field['options'] as $opt_key => $opt_label ) {
                    $options[ (string) $opt_key ] = (string) $opt_label;
                }
                $item['options'] = $options;
            }

            if ( in_array( $type, array( 'range', 'number' ), true ) ) {
                if ( isset( $field['min'] ) && is_scalar( $field['min'] ) ) {
                    $item['min'] = (string) $field['min'];
                }
                if ( isset( $field['max'] ) && is_scalar( $field['max'] ) ) {
                    $item['max'] = (string) $field['max'];
                }
                if ( isset( $field['step'] ) && is_scalar( $field['step'] ) ) {
                    $item['step'] = (string) $field['step'];
                }
            }

            if ( 'repeater' === $type ) {
                $item['fields'] = isset( $field['fields'] ) && is_array( $field['fields'] )
                    ? $this->sanitize_field_schema_for_prompt( $field['fields'] )
                    : array();
                if ( isset( $field['default_items'] ) && is_array( $field['default_items'] ) ) {
                    $item['default_items'] = $field['default_items'];
                }
            }

            $schema[] = $item;
        }

        return $schema;
    }

    /**
     * 构建默认数据。
     *
     * @param array<int,array<string,mixed>> $fields 字段定义。
     * @return array<string,mixed>
     */
    public function build_default_data_from_fields( $fields ) {
        $defaults = array();
        if ( ! is_array( $fields ) ) {
            return $defaults;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            $field_id = sanitize_key( (string) $field['id'] );
            $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';

            if ( '' === $field_id ) {
                continue;
            }

            if ( 'repeater' === $type ) {
                $defaults[ $field_id ] = isset( $field['default_items'] ) && is_array( $field['default_items'] )
                    ? $field['default_items']
                    : array();
                continue;
            }

            $defaults[ $field_id ] = isset( $field['default'] ) ? $field['default'] : '';
        }

        return $defaults;
    }

    /**
     * 默认系统指令。
     *
     * @return string
     */
    public function get_system_prompt() {
        $custom_prompt = trim( (string) $this->get_option_value( 'ai_default_system_prompt', '' ) );
        if ( '' !== $custom_prompt ) {
            return $custom_prompt;
        }

        return implode(
            "\n",
            array(
                '你是启灵主题（WordPress 主题）的 AI 装修助手。',
                '你的目标是根据用户需求，为当前单页或当前单个模块生成严格可解析的 json 装修配置。',
                '你可以优化已有模块、已有页面结构和当前页面 SEO，但不能在线生成整站、多页面站点、站点包或市场包。',
                '你必须只使用输入中给出的候选模块与字段 schema，不能发明模块、字段或额外格式。',
                '你返回的内容必须是单个 json 对象，不要包含解释、Markdown、代码块或注释。',
                '如果某个字段无法确定，请尽量留空或沿用 defaultData，不要猜测不存在的字段。',
                '页面文案默认使用中文，强调清晰、可信、简洁，并优先适合企业官网/产品介绍页场景。',
            )
        );
    }

    /**
     * 默认温度值。
     *
     * @return float
     */
    public function get_default_temperature() {
        $temperature = $this->get_option_value( 'ai_default_temperature', '0.4' );
        $temperature = is_numeric( $temperature ) ? (float) $temperature : 0.4;
        if ( $temperature < 0 ) {
            $temperature = 0.0;
        } elseif ( $temperature > 2 ) {
            $temperature = 2.0;
        }

        return $temperature;
    }

    /**
     * 默认最大输出 tokens。
     *
     * @return int
     */
    public function get_default_max_output_tokens() {
        $tokens = absint( $this->get_option_value( 'ai_default_max_output_tokens', 4000 ) );
        if ( $tokens < 256 ) {
            $tokens = 4000;
        }
        if ( $tokens > 16000 ) {
            $tokens = 16000;
        }

        return $tokens;
    }

    /**
     * 按请求类型获取最大输出 tokens 预算。
     *
     * @param string $request_type 请求类型。
     * @param int    $module_count 模块数量。
     * @return int
     */
    public function get_request_max_output_tokens( $request_type, $module_count = 0 ) {
        $global_limit = $this->get_default_max_output_tokens();
        $module_count = absint( $module_count );
        $budget = $global_limit;

        switch ( $request_type ) {
            case 'connection_test':
                $budget = 256;
                break;

            case 'page_plan':
                $budget = max( 1024, 640 + ( $module_count * 180 ) );
                if ( $budget > 2400 ) {
                    $budget = 2400;
                }
                break;

            case 'module_generate':
                $budget = 1800;
                break;

            case 'module_localization':
                $budget = 1600;
                break;

            case 'page_localization':
                $budget = max( 2600, 1600 + ( $module_count * 260 ) );
                break;

            case 'batch_localization':
                $budget = 1800;
                break;

            case 'content_localization':
                $budget = 2200;
                break;

            case 'page_generate':
                $budget = max( 2400, 1800 + ( $module_count * 350 ) );
                break;
        }

        if ( $budget < 256 ) {
            $budget = 256;
        }

        if ( $budget > $global_limit ) {
            $budget = $global_limit;
        }

        return $budget;
    }

    /**
     * 默认候选模块上限。
     *
     * @return int
     */
    public function get_default_max_modules() {
        $max_modules = absint( $this->get_option_value( 'ai_default_max_modules', 8 ) );
        if ( $max_modules < 1 ) {
            $max_modules = 8;
        }
        if ( $max_modules > self::MAX_MODULES_PER_REQUEST ) {
            $max_modules = self::MAX_MODULES_PER_REQUEST;
        }

        return $max_modules;
    }

    /**
     * 默认请求超时。
     *
     * @return int
     */
    public function get_default_request_timeout() {
        $timeout = absint( $this->get_option_value( 'ai_default_request_timeout', 120 ) );
        if ( $timeout < 10 ) {
            $timeout = 120;
        }
        if ( $timeout > 300 ) {
            $timeout = 300;
        }

        return $timeout;
    }

    /**
     * 单模块生成时的超时。
     *
     * @param int $step_index 当前是第几个模块。
     * @return int
     */
    public function get_single_module_request_timeout( $step_index = 1 ) {
        $timeout = max( $this->get_default_request_timeout(), 120 );
        if ( absint( $step_index ) >= 5 ) {
            $timeout = max( $timeout, 150 );
        }
        if ( $timeout > 300 ) {
            $timeout = 300;
        }

        return $timeout;
    }

    /**
     * 正式生成页面时使用的超时。
     *
     * @param int $module_count 候选模块数量。
     * @return int
     */
    public function get_generation_request_timeout( $module_count = 0 ) {
        $timeout = $this->get_default_request_timeout();
        $module_count = absint( $module_count );

        if ( $module_count >= 8 ) {
            $timeout = max( $timeout, 240 );
        } else {
            $timeout = max( $timeout, 180 );
        }

        if ( $timeout > 300 ) {
            $timeout = 300;
        }

        return $timeout;
    }

    /**
     * 判断是否为超时错误。
     *
     * @param \WP_Error $error 错误对象。
     * @return bool
     */
    public function is_timeout_error( $error ) {
        if ( ! is_wp_error( $error ) ) {
            return false;
        }

        $message = $error->get_error_message();
        if ( ! is_string( $message ) || '' === $message ) {
            return false;
        }

        $message = strtolower( $message );
        return false !== strpos( $message, 'curl error 28' ) || false !== strpos( $message, 'timed out' );
    }

    /**
     * 获取默认页面模板。
     *
     * @return string
     */
    public function get_default_page_template() {
        return 'templates/template-fullscreen.php';
    }

    /**
     * 规范化页面模板。
     *
     * @param string $template 模板。
     * @return string
     */
    public function normalize_page_template( $template ) {
        $template = is_scalar( $template ) ? sanitize_text_field( (string) $template ) : '';
        if ( '' === $template || 'default' === $template ) {
            return $this->get_default_page_template();
        }

        return $template;
    }

    /**
     * 规范化布尔值。
     *
     * @param mixed $value 值。
     * @param bool  $default 默认值。
     * @return bool
     */
    public function normalize_bool( $value, $default = false ) {
        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_numeric( $value ) ) {
            return ( (int) $value ) === 1;
        }

        if ( is_string( $value ) ) {
            $value = strtolower( trim( $value ) );
            if ( in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ) {
                return true;
            }
            if ( in_array( $value, array( '0', 'false', 'no', 'off', '' ), true ) ) {
                return false;
            }
        }

        return (bool) $default;
    }

    /**
     * 间距值清洗。
     *
     * @param mixed $value 原值。
     * @return string
     */
    public function sanitize_spacing_value( $value ) {
        if ( class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            return Module_Manager::sanitize_spacing_value( $value );
        }

        return sanitize_text_field( (string) $value );
    }

    /**
     * 支持 qiling:// 占位链接。
     *
     * @param string $value 原值。
     * @return string
     */
    public function sanitize_supported_placeholder_url( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value || stripos( $value, 'qiling://' ) !== 0 ) {
            return '';
        }

        if ( preg_match( '/^qiling:\/\/page\/([a-z0-9_\-]+)$/i', $value, $matches ) ) {
            $page_key = sanitize_key( (string) $matches[1] );
            if ( '' !== $page_key ) {
                return 'qiling://page/' . $page_key;
            }
        }

        if ( preg_match( '/^qiling:\/\/system\/([a-z0-9_\-]+)$/i', $value, $matches ) ) {
            $target = sanitize_key( (string) $matches[1] );
            if ( '' !== $target ) {
                return 'qiling://system/' . $target;
            }
        }

        return '';
    }

    /**
     * 短文本长度预警。
     *
     * @param string            $key 字段名。
     * @param string            $value 值。
     * @param array<int,string> $style_warnings 预警。
     * @param string            $field_path 路径。
     * @return void
     */
    public function maybe_collect_text_length_style_warning( $key, $value, &$style_warnings, $field_path ) {
        $key = sanitize_key( (string) $key );
        if ( '' === $key || '' === trim( $value ) ) {
            return;
        }

        $is_short_text_field = (bool) preg_match(
            '/(^title$|_title$|title_|^subtitle$|_subtitle$|subtitle_|^label$|_label$|label_|^badge$|_badge$|badge_|btn_text|button_text|tab_title|card_title|item_title|^name$|_name$|name_)/',
            $key
        );
        if ( ! $is_short_text_field ) {
            return;
        }

        $text_length = function_exists( 'mb_strlen' )
            ? mb_strlen( wp_strip_all_tags( $value ), 'UTF-8' )
            : strlen( wp_strip_all_tags( $value ) );
        $threshold = ( false !== strpos( $key, 'btn' ) || false !== strpos( $key, 'button' ) || false !== strpos( $key, 'badge' ) || false !== strpos( $key, 'label' ) )
            ? 18
            : 36;

        if ( $text_length > $threshold ) {
            $style_warnings[] = sprintf(
                /* translators: %s: field path */
                __( '字段 %s 的文案较长，应用后可能出现换行或布局挤压。', 'developer-starter' ),
                $field_path
            );
        }
    }

    /**
     * 安全预警。
     *
     * @param string            $key 字段名。
     * @param string            $value 值。
     * @param array<int,string> $security_warnings 预警。
     * @param string            $field_path 路径。
     * @return void
     */
    public function maybe_collect_scalar_security_warning( $key, $value, &$security_warnings, $field_path ) {
        $key = sanitize_key( (string) $key );
        $value = (string) $value;
        if ( '' === trim( $value ) ) {
            return;
        }

        if ( preg_match( '/<\s*(script|iframe|object|embed|form|style|link)\b/i', $value ) ) {
            $security_warnings[] = sprintf(
                /* translators: %s: field path */
                __( '字段 %s 包含高风险 HTML，保存时会按主题安全规则过滤。', 'developer-starter' ),
                $field_path
            );
        }

        if ( preg_match( '/\son[a-z]+\s*=/i', $value ) ) {
            $security_warnings[] = sprintf(
                /* translators: %s: field path */
                __( '字段 %s 包含事件属性写法，保存时会按主题安全规则过滤。', 'developer-starter' ),
                $field_path
            );
        }
    }

    /**
     * 摘要模块数据中的少量可读文案，避免把整页内容塞进请求上下文。
     *
     * @param array<string,mixed> $data 模块数据。
     * @return array<int,string>
     */
    private function collect_module_context_values( $data ) {
        $values = array();
        if ( ! is_array( $data ) ) {
            return $values;
        }

        foreach ( $data as $key => $value ) {
            if ( count( $values ) >= 4 ) {
                break;
            }

            $key = sanitize_key( (string) $key );
            if ( '' === $key || preg_match( '/(url|link|image|logo|file|icon|color|shadow|radius|margin|padding|style|class)/', $key ) ) {
                continue;
            }

            if ( is_array( $value ) ) {
                if ( $this->is_list_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) ) {
                    foreach ( $this->collect_module_context_values( $value[0] ) as $nested_value ) {
                        if ( count( $values ) >= 4 ) {
                            break;
                        }
                        $values[] = $nested_value;
                    }
                }
                continue;
            }

            if ( ! is_scalar( $value ) ) {
                continue;
            }

            $text = trim( wp_strip_all_tags( (string) $value ) );
            if ( '' === $text ) {
                continue;
            }

            $values[] = $key . ':' . $this->truncate_text_for_log( $text, 56 );
        }

        return $values;
    }

    /**
     * 是否是列表数组。
     *
     * @param mixed $value 值。
     * @return bool
     */
    public function is_list_array( $value ) {
        if ( ! is_array( $value ) ) {
            return false;
        }

        return array_keys( $value ) === range( 0, count( $value ) - 1 );
    }

    /**
     * 读取主题设置。
     *
     * @return array<string,mixed>
     */
    public function get_theme_options() {
        $options = get_option( 'developer_starter_options', array() );
        return is_array( $options ) ? $options : array();
    }

    /**
     * 读取单项设置。
     *
     * @param string $key 键名。
     * @param mixed  $default 默认值。
     * @return mixed
     */
    public function get_option_value( $key, $default = '' ) {
        $options = $this->get_theme_options();
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    /**
     * 是否写入生成调试日志。
     *
     * @return bool
     */
    public function should_log_debug_messages() {
        $ai_debug_log_enabled = $this->get_option_value( 'ai_debug_log_enable', '' ) === '1';
        if ( ! $ai_debug_log_enabled ) {
            return false;
        }

        return defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
    }

    /**
     * 记录生成调试日志（不包含敏感信息）。
     *
     * @param string              $message 日志消息。
     * @param array<string,mixed> $context 上下文。
     * @return void
     */
    public function log_debug_message( $message, $context = array() ) {
        if ( ! $this->should_log_debug_messages() ) {
            return;
        }

        $suffix = '';
        if ( ! empty( $context ) ) {
            $encoded = wp_json_encode( $context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            if ( is_string( $encoded ) && '' !== $encoded ) {
                $suffix = ' ' . $encoded;
            }
        }

        error_log( '[AI Decorator] ' . sanitize_text_field( (string) $message ) . $suffix );
    }

    /**
     * 截断文本用于日志和测试回显。
     *
     * @param string $text 文本。
     * @param int    $limit 最大长度。
     * @return string
     */
    public function truncate_text_for_log( $text, $limit = 240 ) {
        $text = trim( wp_strip_all_tags( (string) $text ) );
        $limit = absint( $limit );
        if ( $limit < 1 || '' === $text ) {
            return $text;
        }

        if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
            if ( mb_strlen( $text, 'UTF-8' ) > $limit ) {
                return mb_substr( $text, 0, $limit, 'UTF-8' ) . '...';
            }
            return $text;
        }

        if ( strlen( $text ) > $limit ) {
            return substr( $text, 0, $limit ) . '...';
        }

        return $text;
    }

    /**
     * 获取连接管理器。
     *
     * @return Connection_Manager
     */
    private function get_connection_manager() {
        if ( null === $this->connection_manager ) {
            $this->connection_manager = new Connection_Manager( $this, $this->get_response_parser() );
        }

        return $this->connection_manager;
    }

    /**
     * 获取消息构建器。
     *
     * @return object
     */
    private function get_prompt_builder() {
        if ( null === $this->prompt_builder ) {
            $this->prompt_builder = new Prompt_Builder( $this );
        }

        return $this->prompt_builder;
    }

    /**
     * 获取响应解析器。
     *
     * @return Response_Parser
     */
    private function get_response_parser() {
        if ( null === $this->response_parser ) {
            $this->response_parser = new Response_Parser( $this );
        }

        return $this->response_parser;
    }

    /**
     * 获取生成编排器。
     *
     * @return Generation_Orchestrator
     */
    private function get_generation_orchestrator() {
        if ( null === $this->generation_orchestrator ) {
            $this->generation_orchestrator = new Generation_Orchestrator(
                $this,
                $this->get_connection_manager(),
                $this->get_prompt_builder(),
                $this->get_response_parser()
            );
        }

        return $this->generation_orchestrator;
    }

    /**
     * 获取 Builder 数据服务。
     *
     * @return Builder_Data_Service
     */
    private function get_builder_data_service() {
        if ( null === $this->builder_data_service ) {
            $this->builder_data_service = new Builder_Data_Service();
        }

        return $this->builder_data_service;
    }

    /**
     * 构建模块 schema 条目。
     *
     * @param object $module 模块实例。
     * @return array<string,mixed>|null
     */
    private function build_module_schema_entry( $module ) {
        if ( ! is_object( $module ) || ! method_exists( $module, 'get_fields' ) || ! method_exists( $module, 'get_name' ) ) {
            return null;
        }

        $fields = $module->get_fields();
        $module_id = method_exists( $module, 'get_id' ) ? sanitize_key( (string) $module->get_id() ) : '';
        $metadata = array();
        if ( '' !== $module_id ) {
            $manager = Module_Manager::get_instance();
            $manifest_item = method_exists( $manager, 'get_module_manifest_item' )
                ? $manager->get_module_manifest_item( $module_id )
                : null;
            if ( is_array( $manifest_item ) ) {
                $metadata = $this->build_module_prompt_metadata( $manifest_item );
            }
        }

        return array(
            'name'         => (string) $module->get_name(),
            'field_map'    => $this->build_field_schema_map( $fields ),
            'prompt_fields'=> $this->sanitize_field_schema_for_prompt( $fields ),
            'default_data' => $this->build_default_data_from_fields( $fields ),
            'metadata'     => $metadata,
        );
    }

    /**
     * 构建可进入生成请求的模块元数据。
     *
     * @param array<string,mixed> $manifest_item 模块目录条目。
     * @return array<string,mixed>
     */
    private function build_module_prompt_metadata( $manifest_item ) {
        $metadata = array();
        foreach ( array( 'industryTags', 'pageTags', 'intentTags', 'contentModels', 'schemaTypes' ) as $key ) {
            if ( isset( $manifest_item[ $key ] ) && is_array( $manifest_item[ $key ] ) ) {
                $metadata[ $key ] = array_slice( array_values( array_map( 'strval', $manifest_item[ $key ] ) ), 0, 12 );
            }
        }

        foreach ( array( 'catalogRole', 'metadataSource', 'metadataCompleteness' ) as $key ) {
            if ( isset( $manifest_item[ $key ] ) && is_scalar( $manifest_item[ $key ] ) ) {
                $metadata[ $key ] = (string) $manifest_item[ $key ];
            }
        }

        if ( isset( $manifest_item['aiHints'] ) && is_array( $manifest_item['aiHints'] ) ) {
            $metadata['aiHints'] = $manifest_item['aiHints'];
        }

        return $metadata;
    }

    /**
     * 按需构建指定模块的 schema 条目。
     *
     * @param array<int,string> $module_ids 模块 ID。
     * @return array<string,array<string,mixed>>
     */
    private function build_module_schema_entries( $module_ids = array() ) {
        $manager = Module_Manager::get_instance();
        $schemas = array();
        $module_ids = array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_key', is_array( $module_ids ) ? $module_ids : array() )
                )
            )
        );

        if ( empty( $module_ids ) ) {
            $modules = $manager->get_all_modules();
            if ( ! is_array( $modules ) ) {
                return $schemas;
            }

            foreach ( $modules as $module_id => $module ) {
                $schema = $this->build_module_schema_entry( $module );
                if ( null !== $schema ) {
                    $schemas[ sanitize_key( (string) $module_id ) ] = $schema;
                }
            }

            return $schemas;
        }

        foreach ( $module_ids as $module_id ) {
            $module = $manager->get_module( $module_id );
            $schema = $this->build_module_schema_entry( $module );
            if ( null !== $schema ) {
                $schemas[ $module_id ] = $schema;
            }
        }

        return $schemas;
    }

    /**
     * 模块 schema 缓存版本。
     *
     * @return string
     */
    private function get_module_schema_cache_version() {
        static $version = null;

        if ( null !== $version ) {
            return $version;
        }

        $parts = array(
            (string) filemtime( __FILE__ ),
        );

        $module_manager_file = dirname( __DIR__ ) . '/modules/class-module-manager.php';
        if ( file_exists( $module_manager_file ) ) {
            $parts[] = (string) filemtime( $module_manager_file );
        }

        $module_standards_file = dirname( __DIR__ ) . '/modules/class-module-standards.php';
        if ( file_exists( $module_standards_file ) ) {
            $parts[] = (string) filemtime( $module_standards_file );
        }

        $module_registry_file = dirname( __DIR__ ) . '/modules/module-registry.php';
        if ( file_exists( $module_registry_file ) ) {
            $parts[] = (string) filemtime( $module_registry_file );
        }

        $module_files = glob( dirname( __DIR__ ) . '/modules/modules/*.php' );
        $module_count = 0;
        $latest_module_mtime = 0;

        if ( is_array( $module_files ) ) {
            $module_count = count( $module_files );
            foreach ( $module_files as $module_file ) {
                $mtime = file_exists( $module_file ) ? (int) filemtime( $module_file ) : 0;
                if ( $mtime > $latest_module_mtime ) {
                    $latest_module_mtime = $mtime;
                }
            }
        }

        $parts[] = (string) $module_count;
        $parts[] = (string) $latest_module_mtime;
        $version = md5( implode( '|', $parts ) );

        return $version;
    }

    /**
     * 模块 schema 缓存语言。
     *
     * @return string
     */
    private function get_module_schema_cache_locale() {
        if ( function_exists( 'determine_locale' ) ) {
            return (string) determine_locale();
        }

        return (string) get_locale();
    }

    /**
     * 模块 schema 缓存 key。
     *
     * @param string $module_id 模块 ID。
     * @return string
     */
    private function get_module_schema_cache_key( $module_id ) {
        $module_id = sanitize_key( (string) $module_id );
        if ( $module_id === '' ) {
            return '';
        }

        return 'qiling_ai_schema_' . md5(
            $module_id . '|' . $this->get_module_schema_cache_locale() . '|' . $this->get_module_schema_cache_version()
        );
    }

    /**
     * 模块 schema 缓存 TTL。
     *
     * @return int
     */
    private function get_module_schema_cache_ttl() {
        return defined( 'DAY_IN_SECONDS' ) ? DAY_IN_SECONDS : 86400;
    }

    /**
     * 校验模块 schema 缓存结构。
     *
     * @param mixed $schema schema
     * @return bool
     */
    private function is_valid_module_schema_entry( $schema ) {
        return is_array( $schema )
            && isset( $schema['name'], $schema['field_map'], $schema['prompt_fields'], $schema['default_data'] )
            && is_string( $schema['name'] )
            && is_array( $schema['field_map'] )
            && is_array( $schema['prompt_fields'] )
            && is_array( $schema['default_data'] );
    }

    /**
     * 读取持久化模块 schema 缓存。
     *
     * @param string $module_id 模块 ID。
     * @return array<string,mixed>|null
     */
    private function get_persistent_module_schema_entry( $module_id ) {
        $cache_key = $this->get_module_schema_cache_key( $module_id );
        if ( $cache_key === '' ) {
            return null;
        }

        $cached_schema = wp_cache_get( $cache_key, self::MODULE_SCHEMA_CACHE_GROUP );
        if ( $this->is_valid_module_schema_entry( $cached_schema ) ) {
            return $cached_schema;
        } elseif ( false !== $cached_schema ) {
            wp_cache_delete( $cache_key, self::MODULE_SCHEMA_CACHE_GROUP );
            delete_transient( $cache_key );
        }

        $cached_schema = get_transient( $cache_key );
        if ( $this->is_valid_module_schema_entry( $cached_schema ) ) {
            wp_cache_set( $cache_key, $cached_schema, self::MODULE_SCHEMA_CACHE_GROUP, $this->get_module_schema_cache_ttl() );
            return $cached_schema;
        } elseif ( false !== $cached_schema ) {
            wp_cache_delete( $cache_key, self::MODULE_SCHEMA_CACHE_GROUP );
            delete_transient( $cache_key );
        }

        return null;
    }

    /**
     * 写入持久化模块 schema 缓存。
     *
     * @param string              $module_id 模块 ID。
     * @param array<string,mixed> $schema schema
     * @return void
     */
    private function store_persistent_module_schema_entry( $module_id, $schema ) {
        if ( ! $this->is_valid_module_schema_entry( $schema ) ) {
            return;
        }

        $cache_key = $this->get_module_schema_cache_key( $module_id );
        if ( $cache_key === '' ) {
            return;
        }

        $ttl = $this->get_module_schema_cache_ttl();
        wp_cache_set( $cache_key, $schema, self::MODULE_SCHEMA_CACHE_GROUP, $ttl );
        set_transient( $cache_key, $schema, $ttl );
    }
}
