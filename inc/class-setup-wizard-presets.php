<?php
/**
 * Setup wizard recommendation presets.
 *
 * Phase 2 only resolves lightweight recommendations for templates, pages,
 * content models and optional plugin hints. It does not create content,
 * install plugins, activate plugins or write third-party configuration.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_Presets {

    const DEFAULT_SITE_TYPE = 'corporate';
    const DEFAULT_INDUSTRY  = 'general';

    const MAX_ITEMS = 40;

    /**
     * @var Setup_Wizard_Presets|null
     */
    private static $instance = null;

    /**
     * @return Setup_Wizard_Presets
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return array<string,string>
     */
    public function get_site_type_choices() {
        return $this->get_choice_labels( $this->get_site_types() );
    }

    /**
     * @return array<string,string>
     */
    public function get_industry_choices() {
        return $this->get_choice_labels( $this->get_industries() );
    }

    /**
     * Get the sanitized template catalog for setup/import services.
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_template_catalog_items() {
        return $this->get_template_catalog();
    }

    /**
     * Get the sanitized page catalog for setup/import services.
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_page_catalog_items() {
        return $this->get_page_catalog();
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function get_site_types() {
        $defaults = array(
            'corporate'  => array(
                'label'            => __( '企业官网', 'developer-starter' ),
                'description'      => __( '适合品牌展示、服务介绍、案例背书和线索收集。', 'developer-starter' ),
                'default_industry' => 'general',
                'templates'        => array( 'home', 'solutions', 'cases', 'news' ),
                'pages'            => array( 'home', 'about', 'services', 'products', 'cases', 'news', 'contact' ),
                'content_models'   => array( 'service', 'product', 'case', 'testimonial', 'team', 'faq' ),
                'features'         => array( 'template_center', 'content_model_center', 'seo_basic', 'contact_info', 'footer_builder' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'product'    => array(
                'label'            => __( '产品/服务官网', 'developer-starter' ),
                'description'      => __( '适合一个核心产品、服务套餐或转化型落地页。', 'developer-starter' ),
                'default_industry' => 'general',
                'templates'        => array( 'software_home', 'software_intro', 'products', 'features_showcase', 'cases' ),
                'pages'            => array( 'home', 'products', 'features', 'solutions', 'cases', 'faq', 'contact' ),
                'content_models'   => array( 'product', 'service', 'case', 'testimonial', 'faq' ),
                'features'         => array( 'template_center', 'content_model_center', 'seo_basic', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'content'    => array(
                'label'            => __( '内容博客/资讯站', 'developer-starter' ),
                'description'      => __( '适合博客、知识库、资讯聚合和内容营销。', 'developer-starter' ),
                'default_industry' => 'general',
                'templates'        => array( 'blog', 'news', 'topic', 'latest_posts' ),
                'pages'            => array( 'home', 'blog', 'topics', 'about', 'contact' ),
                'content_models'   => array( 'post', 'author', 'resource', 'faq' ),
                'features'         => array( 'seo_basic', 'search_autocomplete', 'template_center' ),
                'optional_plugins' => array(),
            ),
            'resource'   => array(
                'label'            => __( '资源下载站', 'developer-starter' ),
                'description'      => __( '适合资料库、下载中心、工具集合和筛选搜索。', 'developer-starter' ),
                'default_industry' => 'general',
                'templates'        => array( 'resources', 'resource_search', 'data_showcase', 'blog' ),
                'pages'            => array( 'home', 'resources', 'resource_search', 'downloads', 'blog', 'contact' ),
                'content_models'   => array( 'resource', 'download', 'post', 'faq' ),
                'features'         => array( 'content_model_center', 'search_autocomplete', 'query_cache', 'template_center' ),
                'optional_plugins' => array( 'qilingapp' ),
            ),
            'saas'       => array(
                'label'            => __( 'SaaS / 软件产品', 'developer-starter' ),
                'description'      => __( '适合 SaaS、软件工具、在线平台和产品增长页。', 'developer-starter' ),
                'default_industry' => 'general',
                'templates'        => array( 'saas_home', 'saas_pricing', 'software_intro', 'features_showcase', 'interactive_product_launch' ),
                'pages'            => array( 'home', 'features', 'pricing', 'cases', 'faq', 'blog', 'contact' ),
                'content_models'   => array( 'software', 'product', 'case', 'testimonial', 'faq' ),
                'features'         => array( 'template_center', 'content_model_center', 'seo_basic', 'search_autocomplete' ),
                'optional_plugins' => array( 'qilingapp', 'qiling_forms' ),
            ),
            'global'     => array(
                'label'            => __( '外贸/出海站', 'developer-starter' ),
                'description'      => __( '适合 B2B 外贸、跨境服务和国际化品牌展示。', 'developer-starter' ),
                'default_industry' => 'global',
                'templates'        => array( 'foreign_trade_b2b', 'cross_border_ecommerce_service', 'overseas_warehouse_supply_chain', 'products' ),
                'pages'            => array( 'home', 'products', 'solutions', 'cases', 'about', 'contact' ),
                'content_models'   => array( 'product', 'service', 'case', 'partner', 'faq' ),
                'features'         => array( 'international', 'cookie_consent', 'seo_basic', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'local'      => array(
                'label'            => __( '本地生活服务', 'developer-starter' ),
                'description'      => __( '适合门店、预约服务、同城业务和多网点展示。', 'developer-starter' ),
                'default_industry' => 'store',
                'templates'        => array( 'local_service_official', 'chain_store_official', 'restaurant', 'contact' ),
                'pages'            => array( 'home', 'services', 'branches', 'cases', 'faq', 'contact' ),
                'content_models'   => array( 'service', 'branch', 'case', 'testimonial', 'faq' ),
                'features'         => array( 'local_business', 'content_model_center', 'contact_info', 'template_center' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'personal'   => array(
                'label'            => __( '个人品牌/IP', 'developer-starter' ),
                'description'      => __( '适合个人主页、作品集、简历和长期内容输出。', 'developer-starter' ),
                'default_industry' => 'general',
                'templates'        => array( 'personal_ip_home', 'resume', 'blog', 'contact' ),
                'pages'            => array( 'home', 'about', 'portfolio', 'blog', 'contact' ),
                'content_models'   => array( 'post', 'case', 'media_item', 'author', 'faq' ),
                'features'         => array( 'seo_basic', 'template_center', 'search_autocomplete' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'opensource' => array(
                'label'            => __( '开源/开发者工具', 'developer-starter' ),
                'description'      => __( '适合开源项目、开发者工具、文档入口和版本动态。', 'developer-starter' ),
                'default_industry' => 'opensource',
                'templates'        => array( 'open_source_devtools', 'developer_platform', 'software_home', 'resources' ),
                'pages'            => array( 'home', 'downloads', 'docs', 'resources', 'blog', 'contact' ),
                'content_models'   => array( 'software', 'download', 'resource', 'post', 'faq' ),
                'features'         => array( 'github_activity', 'search_autocomplete', 'content_model_center', 'template_center' ),
                'optional_plugins' => array( 'qilingapp' ),
            ),
            'custom'     => array(
                'label'            => __( '自定义', 'developer-starter' ),
                'description'      => __( '只保留最小推荐，后续手动选择模板和页面。', 'developer-starter' ),
                'default_industry' => 'other',
                'templates'        => array( 'landing', 'home' ),
                'pages'            => array( 'home', 'contact' ),
                'content_models'   => array( 'page', 'post' ),
                'features'         => array( 'template_center' ),
                'optional_plugins' => array(),
            ),
        );

        $filtered = apply_filters( 'developer_starter_setup_wizard_site_type_presets', $defaults, $this );
        $presets  = $this->sanitize_preset_collection( $filtered );

        return ! empty( $presets ) ? $presets : $this->sanitize_preset_collection( $defaults );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function get_industries() {
        $defaults = array(
            'general'    => array(
                'label'            => __( '通用企业', 'developer-starter' ),
                'description'      => __( '适合大多数企业官网和服务型站点。', 'developer-starter' ),
                'templates'        => array( 'home', 'solutions', 'cases' ),
                'pages'            => array( 'about', 'services', 'cases', 'contact' ),
                'content_models'   => array( 'service', 'case', 'faq' ),
                'features'         => array( 'seo_basic', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'b2b'        => array(
                'label'            => __( 'B2B 制造', 'developer-starter' ),
                'description'      => __( '突出工厂实力、产品目录、解决方案和询盘转化。', 'developer-starter' ),
                'templates'        => array( 'manufacturing_factory', 'products', 'solutions', 'foreign_trade_b2b' ),
                'pages'            => array( 'factory', 'products', 'solutions', 'cases', 'contact' ),
                'content_models'   => array( 'product', 'service', 'case', 'partner', 'faq' ),
                'features'         => array( 'content_model_center', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'education'  => array(
                'label'            => __( '教育培训', 'developer-starter' ),
                'description'      => __( '适合课程招生、校区展示、师资团队和报名咨询。', 'developer-starter' ),
                'templates'        => array( 'course_enrollment', 'early_childhood_education', 'vocational_training_school' ),
                'pages'            => array( 'courses', 'teachers', 'branches', 'faq', 'contact' ),
                'content_models'   => array( 'course', 'team', 'branch', 'testimonial', 'faq' ),
                'features'         => array( 'local_business', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'health'     => array(
                'label'            => __( '医疗健康', 'developer-starter' ),
                'description'      => __( '适合诊所、医疗服务、专家团队和预约咨询。', 'developer-starter' ),
                'templates'        => array( 'healthcare_clinic', 'dental_clinic', 'medical_beauty' ),
                'pages'            => array( 'services', 'team', 'branches', 'faq', 'contact' ),
                'content_models'   => array( 'service', 'team', 'branch', 'case', 'faq' ),
                'features'         => array( 'local_business', 'contact_info', 'seo_basic' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'consulting' => array(
                'label'            => __( '法律/咨询', 'developer-starter' ),
                'description'      => __( '适合律师、财税、知识产权和专业咨询机构。', 'developer-starter' ),
                'templates'        => array( 'law_firm', 'finance_consulting', 'accounting_tax_service', 'intellectual_property_service' ),
                'pages'            => array( 'services', 'team', 'cases', 'faq', 'contact' ),
                'content_models'   => array( 'service', 'team', 'case', 'testimonial', 'faq' ),
                'features'         => array( 'seo_basic', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'store'      => array(
                'label'            => __( '餐饮/门店', 'developer-starter' ),
                'description'      => __( '适合餐饮、连锁门店、美业、健身和预约服务。', 'developer-starter' ),
                'templates'        => array( 'restaurant', 'chain_store_official', 'beauty_salon', 'gym_fitness' ),
                'pages'            => array( 'services', 'branches', 'menu', 'booking', 'contact' ),
                'content_models'   => array( 'service', 'branch', 'menu_item', 'event', 'testimonial', 'faq' ),
                'features'         => array( 'local_business', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'renovation' => array(
                'label'            => __( '房产/家装', 'developer-starter' ),
                'description'      => __( '适合装修、设计、地产、园林和项目案例展示。', 'developer-starter' ),
                'templates'        => array( 'renovation_construction', 'real_estate_service', 'architecture_design_studio', 'interior_soft_decoration' ),
                'pages'            => array( 'services', 'cases', 'team', 'faq', 'contact' ),
                'content_models'   => array( 'service', 'case', 'team', 'testimonial', 'faq' ),
                'features'         => array( 'content_model_center', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'opensource' => array(
                'label'            => __( '开源/开发者工具', 'developer-starter' ),
                'description'      => __( '突出仓库动态、版本发布、文档入口和开发者资源。', 'developer-starter' ),
                'templates'        => array( 'open_source_devtools', 'developer_platform', 'resources', 'changelog' ),
                'pages'            => array( 'docs', 'downloads', 'resources', 'blog', 'contact' ),
                'content_models'   => array( 'software', 'download', 'resource', 'post', 'faq' ),
                'features'         => array( 'github_activity', 'search_autocomplete' ),
                'optional_plugins' => array( 'qilingapp' ),
            ),
            'ai'         => array(
                'label'            => __( 'AI 产品', 'developer-starter' ),
                'description'      => __( '适合 AI 工具、智能体、AIGC 服务和技术品牌。', 'developer-starter' ),
                'templates'        => array( 'ai_product_brand', 'ai_agent_enterprise', 'qiling_ai_writing_studio', 'data_intelligence_bi' ),
                'pages'            => array( 'features', 'pricing', 'cases', 'faq', 'contact' ),
                'content_models'   => array( 'software', 'product', 'case', 'testimonial', 'faq' ),
                'features'         => array( 'seo_basic', 'search_autocomplete' ),
                'optional_plugins' => array( 'qilingapp', 'qiling_forms' ),
            ),
            'global'     => array(
                'label'            => __( '外贸服务', 'developer-starter' ),
                'description'      => __( '适合外贸获客、跨境电商服务、海外仓和供应链。', 'developer-starter' ),
                'templates'        => array( 'foreign_trade_b2b', 'cross_border_ecommerce_service', 'overseas_warehouse_supply_chain' ),
                'pages'            => array( 'products', 'solutions', 'cases', 'contact' ),
                'content_models'   => array( 'product', 'service', 'case', 'partner', 'faq' ),
                'features'         => array( 'international', 'cookie_consent', 'contact_info' ),
                'optional_plugins' => array( 'qiling_forms' ),
            ),
            'other'      => array(
                'label'            => __( '其他', 'developer-starter' ),
                'description'      => __( '保留通用页面和模板建议，后续手动细化。', 'developer-starter' ),
                'templates'        => array( 'home', 'landing' ),
                'pages'            => array( 'home', 'about', 'contact' ),
                'content_models'   => array( 'page', 'post', 'faq' ),
                'features'         => array( 'template_center' ),
                'optional_plugins' => array(),
            ),
        );

        $filtered = apply_filters( 'developer_starter_setup_wizard_industry_presets', $defaults, $this );
        $presets  = $this->sanitize_preset_collection( $filtered );

        return ! empty( $presets ) ? $presets : $this->sanitize_preset_collection( $defaults );
    }

    /**
     * Resolve a site type and industry into one recommendation payload.
     *
     * @param string $site_type Site type key.
     * @param string $industry Industry key.
     * @return array<string,mixed>
     */
    public function resolve( $site_type = '', $industry = '' ) {
        $site_types = $this->get_site_types();
        $industries = $this->get_industries();

        $site_type = sanitize_key( (string) $site_type );
        if ( '' === $site_type || ! isset( $site_types[ $site_type ] ) ) {
            $site_type = isset( $site_types[ self::DEFAULT_SITE_TYPE ] ) ? self::DEFAULT_SITE_TYPE : '';
            if ( '' === $site_type ) {
                reset( $site_types );
                $site_type = (string) key( $site_types );
            }
        }

        $industry = sanitize_key( (string) $industry );
        if ( '' === $industry || ! isset( $industries[ $industry ] ) ) {
            $default_industry = isset( $site_types[ $site_type ]['default_industry'] ) ? $site_types[ $site_type ]['default_industry'] : self::DEFAULT_INDUSTRY;
            $industry = isset( $industries[ $default_industry ] ) ? $default_industry : '';
            if ( '' === $industry ) {
                $industry = isset( $industries[ self::DEFAULT_INDUSTRY ] ) ? self::DEFAULT_INDUSTRY : '';
            }
            if ( '' === $industry ) {
                reset( $industries );
                $industry = (string) key( $industries );
            }
        }

        $raw = array(
            'templates'        => array(),
            'pages'            => array(),
            'content_models'   => array(),
            'features'         => array(),
            'optional_plugins' => array(),
        );
        $raw = $this->merge_preset_config( $raw, $site_types[ $site_type ] );
        $raw = $this->merge_preset_config( $raw, $industries[ $industry ] );

        $resolved = array(
            'site_type'             => $site_type,
            'site_type_label'       => isset( $site_types[ $site_type ]['label'] ) ? $site_types[ $site_type ]['label'] : $site_type,
            'site_type_description' => isset( $site_types[ $site_type ]['description'] ) ? $site_types[ $site_type ]['description'] : '',
            'industry'              => $industry,
            'industry_label'        => isset( $industries[ $industry ]['label'] ) ? $industries[ $industry ]['label'] : $industry,
            'industry_description'  => isset( $industries[ $industry ]['description'] ) ? $industries[ $industry ]['description'] : '',
            'recommended_templates' => $this->expand_catalog_items( $raw['templates'], $this->get_template_catalog() ),
            'recommended_pages'     => $this->expand_catalog_items( $raw['pages'], $this->get_page_catalog() ),
            'content_models'        => $this->expand_catalog_items( $raw['content_models'], $this->get_content_model_catalog() ),
            'features'              => $this->expand_catalog_items( $raw['features'], $this->get_feature_catalog() ),
            'optional_plugins'      => $this->expand_catalog_items( $raw['optional_plugins'], $this->get_optional_plugin_catalog() ),
        );

        $resolved['content_model_keys'] = $this->items_to_keys( $resolved['content_models'] );
        $resolved['feature_keys']       = $this->items_to_keys( $resolved['features'] );

        $filtered = apply_filters( 'developer_starter_setup_wizard_resolved_preset', $resolved, $site_type, $industry, $this );

        return $this->sanitize_resolved_preset( $filtered );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_template_catalog() {
        $catalog = array(
            'home'                            => $this->template( __( '企业官网首页', 'developer-starter' ), 'templates/template-home.php', 'home.json' ),
            'landing'                         => $this->template( __( '通用落地页', 'developer-starter' ), 'templates/template-landing.php', 'landing.json' ),
            'about'                           => $this->template( __( '关于我们', 'developer-starter' ), 'templates/template-about.php', '' ),
            'contact'                         => $this->template( __( '联系页面', 'developer-starter' ), 'templates/template-contact.php', '' ),
            'solutions'                       => $this->template( __( '解决方案', 'developer-starter' ), 'templates/template-solutions.php', 'solutions.json' ),
            'products'                        => $this->template( __( '产品展示', 'developer-starter' ), 'templates/template-products.php', 'products.json' ),
            'cases'                           => $this->template( __( '案例展示', 'developer-starter' ), 'templates/template-cases.php', 'cases.json' ),
            'news'                            => $this->template( __( '新闻资讯', 'developer-starter' ), 'templates/template-news.php', 'news.json' ),
            'blog'                            => $this->template( __( '博客首页', 'developer-starter' ), 'templates/template-blog.php', 'blog.json' ),
            'topic'                           => $this->template( __( '专题聚合', 'developer-starter' ), 'templates/template-topic.php', 'topic.json' ),
            'latest_posts'                    => $this->template( __( '最新文章', 'developer-starter' ), 'templates/template-latest-posts.php', '' ),
            'resources'                       => $this->template( __( '资源中心', 'developer-starter' ), 'templates/template-resources.php', 'resources.json' ),
            'resource_search'                 => $this->template( __( '资源搜索', 'developer-starter' ), 'templates/template-resource-search.php', 'resource-search.json' ),
            'data_showcase'                   => $this->template( __( '数据展示', 'developer-starter' ), 'templates/template-data-showcase.php', 'data-showcase.json' ),
            'software_home'                   => $this->template( __( '软件产品首页', 'developer-starter' ), 'templates/template-software-home.php', 'software-home.json' ),
            'software_intro'                  => $this->template( __( '软件介绍页', 'developer-starter' ), 'templates/template-software-intro.php', 'software-intro.json' ),
            'saas_home'                       => $this->template( __( 'SaaS 首页', 'developer-starter' ), 'templates/template-saas-home.php', 'saas-home.json' ),
            'hosting_saas_home'               => $this->template( __( '云主机SaaS官网（一体式）', 'developer-starter' ), 'templates/template-hosting-saas-home.php', 'hosting-saas-home.json' ),
            'saas_pricing'                    => $this->template( __( '价格方案', 'developer-starter' ), 'templates/template-saas-pricing.php', 'saas-pricing.json' ),
            'features_showcase'               => $this->template( __( '功能展示', 'developer-starter' ), 'templates/template-features-showcase.php', 'features-showcase.json' ),
            'interactive_product_launch'      => $this->template( __( '互动产品发布页', 'developer-starter' ), 'templates/template-interactive-product-launch.php', 'interactive-product-launch.json' ),
            'foreign_trade_b2b'               => $this->template( __( '外贸 B2B 官网', 'developer-starter' ), 'templates/template-foreign-trade-b2b.php', 'foreign-trade-b2b.json' ),
            'cross_border_ecommerce_service'  => $this->template( __( '跨境电商服务', 'developer-starter' ), 'templates/template-cross-border-ecommerce-service.php', 'cross-border-ecommerce-service.json' ),
            'overseas_warehouse_supply_chain' => $this->template( __( '海外仓供应链', 'developer-starter' ), 'templates/template-overseas-warehouse-supply-chain.php', 'overseas-warehouse-supply-chain.json' ),
            'local_service_official'          => $this->template( __( '本地服务官网', 'developer-starter' ), 'templates/template-local-service-official.php', 'local-service-official.json' ),
            'chain_store_official'            => $this->template( __( '连锁门店官网', 'developer-starter' ), 'templates/template-chain-store-official.php', 'chain-store-official.json' ),
            'restaurant'                      => $this->template( __( '餐饮门店', 'developer-starter' ), 'templates/template-restaurant.php', 'restaurant.json' ),
            'beauty_salon'                    => $this->template( __( '美业门店', 'developer-starter' ), 'templates/template-beauty-salon.php', 'beauty-salon.json' ),
            'gym_fitness'                     => $this->template( __( '健身工作室', 'developer-starter' ), 'templates/template-gym-fitness.php', 'gym-fitness.json' ),
            'personal_ip_home'                => $this->template( __( '个人品牌首页', 'developer-starter' ), 'templates/template-personal-ip-home.php', 'personal-ip-home.json' ),
            'resume'                          => $this->template( __( '个人简历', 'developer-starter' ), 'templates/template-resume.php', 'resume.json' ),
            'open_source_devtools'            => $this->template( __( '开源项目动态', 'developer-starter' ), 'templates/template-open-source-devtools.php', 'open-source-devtools.json' ),
            'developer_platform'              => $this->template( __( '开发者平台', 'developer-starter' ), 'templates/template-developer-platform.php', 'developer-platform.json' ),
            'changelog'                       => $this->template( __( '更新日志', 'developer-starter' ), 'templates/template-changelog.php', '' ),
            'manufacturing_factory'           => $this->template( __( '制造工厂', 'developer-starter' ), 'templates/template-manufacturing-factory.php', 'manufacturing-factory.json' ),
            'course_enrollment'               => $this->template( __( '课程招生', 'developer-starter' ), 'templates/template-course-enrollment.php', 'course-enrollment.json' ),
            'early_childhood_education'       => $this->template( __( '早教机构', 'developer-starter' ), 'templates/template-early-childhood-education.php', 'early-childhood-education.json' ),
            'vocational_training_school'      => $this->template( __( '职业培训', 'developer-starter' ), 'templates/template-vocational-training-school.php', 'vocational-training-school.json' ),
            'healthcare_clinic'               => $this->template( __( '医疗诊所', 'developer-starter' ), 'templates/template-healthcare-clinic.php', 'healthcare-clinic.json' ),
            'dental_clinic'                   => $this->template( __( '牙科诊所', 'developer-starter' ), 'templates/template-dental-clinic.php', 'dental-clinic.json' ),
            'medical_beauty'                  => $this->template( __( '医疗美容', 'developer-starter' ), 'templates/template-medical-beauty.php', 'medical-beauty.json' ),
            'law_firm'                        => $this->template( __( '律师事务所', 'developer-starter' ), 'templates/template-law-firm.php', 'law-firm.json' ),
            'finance_consulting'              => $this->template( __( '金融咨询', 'developer-starter' ), 'templates/template-finance-consulting.php', 'finance-consulting.json' ),
            'accounting_tax_service'          => $this->template( __( '财税服务', 'developer-starter' ), 'templates/template-accounting-tax-service.php', 'accounting-tax-service.json' ),
            'intellectual_property_service'   => $this->template( __( '知识产权服务', 'developer-starter' ), 'templates/template-intellectual-property-service.php', 'intellectual-property-service.json' ),
            'renovation_construction'         => $this->template( __( '装修施工', 'developer-starter' ), 'templates/template-renovation-construction.php', 'renovation-construction.json' ),
            'real_estate_service'             => $this->template( __( '房产服务', 'developer-starter' ), 'templates/template-real-estate-service.php', 'real-estate-service.json' ),
            'architecture_design_studio'      => $this->template( __( '建筑设计', 'developer-starter' ), 'templates/template-architecture-design-studio.php', 'architecture-design-studio.json' ),
            'interior_soft_decoration'        => $this->template( __( '软装设计', 'developer-starter' ), 'templates/template-interior-soft-decoration.php', 'interior-soft-decoration.json' ),
            'ai_product_brand'                => $this->template( __( 'AI 产品品牌', 'developer-starter' ), 'templates/template-ai-product-brand.php', 'ai-product-brand.json' ),
            'ai_agent_enterprise'             => $this->template( __( 'AI 智能体企业', 'developer-starter' ), 'templates/template-ai-agent-enterprise.php', 'ai-agent-enterprise.json' ),
            'qiling_ai_writing_studio'        => $this->template( __( 'AI 写作工作台', 'developer-starter' ), 'templates/template-qiling-ai-writing-studio.php', 'qiling-ai-writing-studio.json' ),
            'data_intelligence_bi'            => $this->template( __( '数据智能 BI', 'developer-starter' ), 'templates/template-data-intelligence-bi.php', 'data-intelligence-bi.json' ),
        );

        $filtered = apply_filters( 'developer_starter_setup_wizard_template_catalog', $catalog, $this );
        return $this->sanitize_catalog( $filtered );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_page_catalog() {
        $catalog = array(
            'home'            => $this->page( __( '首页', 'developer-starter' ), 'home', 'home' ),
            'about'           => $this->page( __( '关于我们', 'developer-starter' ), 'about', 'about' ),
            'services'        => $this->page( __( '服务', 'developer-starter' ), 'services', 'solutions' ),
            'products'        => $this->page( __( '产品', 'developer-starter' ), 'products', 'products' ),
            'solutions'       => $this->page( __( '解决方案', 'developer-starter' ), 'solutions', 'solutions' ),
            'features'        => $this->page( __( '功能介绍', 'developer-starter' ), 'features', 'features_showcase' ),
            'pricing'         => $this->page( __( '价格方案', 'developer-starter' ), 'pricing', 'saas_pricing' ),
            'cases'           => $this->page( __( '案例', 'developer-starter' ), 'cases', 'cases' ),
            'news'            => $this->page( __( '新闻', 'developer-starter' ), 'news', 'news' ),
            'blog'            => $this->page( __( '博客', 'developer-starter' ), 'blog', 'blog' ),
            'topics'          => $this->page( __( '专题', 'developer-starter' ), 'topics', 'topic' ),
            'resources'       => $this->page( __( '资源中心', 'developer-starter' ), 'resources', 'resources' ),
            'resource_search' => $this->page( __( '资源搜索', 'developer-starter' ), 'resource-search', 'resource_search' ),
            'downloads'       => $this->page( __( '下载中心', 'developer-starter' ), 'downloads', 'resources' ),
            'docs'            => $this->page( __( '文档', 'developer-starter' ), 'docs', 'developer_platform' ),
            'faq'             => $this->page( __( '常见问题', 'developer-starter' ), 'faq', 'faq' ),
            'contact'         => $this->page( __( '联系我们', 'developer-starter' ), 'contact', 'contact' ),
            'branches'        => $this->page( __( '门店/网点', 'developer-starter' ), 'branches', 'local_service_official' ),
            'menu'            => $this->page( __( '菜单/价目表', 'developer-starter' ), 'menu', 'restaurant' ),
            'booking'         => $this->page( __( '预约咨询', 'developer-starter' ), 'booking', 'contact' ),
            'courses'         => $this->page( __( '课程', 'developer-starter' ), 'courses', 'course_enrollment' ),
            'teachers'        => $this->page( __( '师资团队', 'developer-starter' ), 'teachers', 'about' ),
            'team'            => $this->page( __( '团队', 'developer-starter' ), 'team', 'about' ),
            'factory'         => $this->page( __( '工厂实力', 'developer-starter' ), 'factory', 'manufacturing_factory' ),
            'portfolio'       => $this->page( __( '作品集', 'developer-starter' ), 'portfolio', 'cases' ),
        );

        $filtered = apply_filters( 'developer_starter_setup_wizard_page_catalog', $catalog, $this );
        return $this->sanitize_catalog( $filtered );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_content_model_catalog() {
        $catalog = array(
            'page'        => $this->item( __( '页面', 'developer-starter' ), __( '承载官网页面和专题页。', 'developer-starter' ) ),
            'post'        => $this->item( __( '文章', 'developer-starter' ), __( '承载博客、资讯和知识内容。', 'developer-starter' ) ),
            'service'     => $this->item( __( '服务', 'developer-starter' ), __( '用于服务项目、咨询方案和本地业务。', 'developer-starter' ) ),
            'product'     => $this->item( __( '产品', 'developer-starter' ), __( '用于产品目录、设备和方案包。', 'developer-starter' ) ),
            'case'        => $this->item( __( '案例', 'developer-starter' ), __( '用于客户案例、项目作品和成果故事。', 'developer-starter' ) ),
            'testimonial' => $this->item( __( '评价', 'developer-starter' ), __( '用于客户评价和用户证言。', 'developer-starter' ) ),
            'team'        => $this->item( __( '团队', 'developer-starter' ), __( '用于成员、讲师、医生和顾问。', 'developer-starter' ) ),
            'branch'      => $this->item( __( '门店/分支', 'developer-starter' ), __( '用于门店、校区和服务网点。', 'developer-starter' ) ),
            'faq'         => $this->item( __( 'FAQ', 'developer-starter' ), __( '用于常见问题和帮助条目。', 'developer-starter' ) ),
            'download'    => $this->item( __( '下载资料', 'developer-starter' ), __( '用于白皮书、资料包和附件。', 'developer-starter' ) ),
            'course'      => $this->item( __( '课程', 'developer-starter' ), __( '用于课程、训练营和培训项目。', 'developer-starter' ) ),
            'event'       => $this->item( __( '活动', 'developer-starter' ), __( '用于活动、发布会和预约项目。', 'developer-starter' ) ),
            'software'    => $this->item( __( '软件应用', 'developer-starter' ), __( '用于软件目录和工具库；启灵应用插件可增强。', 'developer-starter' ) ),
            'resource'    => $this->item( __( '资源', 'developer-starter' ), __( '用于资料、知识卡片和内容库。', 'developer-starter' ) ),
            'media_item'  => $this->item( __( '媒体条目', 'developer-starter' ), __( '用于视频、播客、图集和素材。', 'developer-starter' ) ),
            'author'      => $this->item( __( '作者', 'developer-starter' ), __( '用于博客作者和个人主页。', 'developer-starter' ) ),
            'partner'     => $this->item( __( '合作伙伴', 'developer-starter' ), __( '用于客户、渠道商和合作伙伴。', 'developer-starter' ) ),
            'menu_item'   => $this->item( __( '菜单菜品', 'developer-starter' ), __( '用于餐饮菜单、套餐和项目价目表。', 'developer-starter' ) ),
        );

        $filtered = apply_filters( 'developer_starter_setup_wizard_content_model_catalog', $catalog, $this );
        return $this->sanitize_catalog( $filtered );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_feature_catalog() {
        $catalog = array(
            'template_center'      => $this->item( __( '模板中心', 'developer-starter' ), __( '后续用于导入官方模板包。', 'developer-starter' ) ),
            'content_model_center' => $this->item( __( '内容模型中心', 'developer-starter' ), __( '用于服务、产品、案例等主题自带内容模型。', 'developer-starter' ) ),
            'seo_basic'            => $this->item( __( '基础 SEO', 'developer-starter' ), __( '主题自带标题、结构化和搜索优化能力。', 'developer-starter' ) ),
            'contact_info'         => $this->item( __( '联系信息', 'developer-starter' ), __( '用于电话、邮箱、地址和咨询入口。', 'developer-starter' ) ),
            'footer_builder'       => $this->item( __( '页脚配置', 'developer-starter' ), __( '用于站点页脚、备案和链接分组。', 'developer-starter' ) ),
            'search_autocomplete'  => $this->item( __( '智能搜索增强', 'developer-starter' ), __( 'Ajax 实时搜索和关键词高亮。', 'developer-starter' ) ),
            'query_cache'          => $this->item( __( '查询缓存', 'developer-starter' ), __( '主题查询缓存能力，减少重复查询。', 'developer-starter' ) ),
            'local_business'       => $this->item( __( '本地商家能力', 'developer-starter' ), __( '门店、网点、地址和本地业务信息。', 'developer-starter' ) ),
            'international'        => $this->item( __( '国际化展示', 'developer-starter' ), __( '出海站常用的字体、代码和区域配置。', 'developer-starter' ) ),
            'cookie_consent'       => $this->item( __( 'Cookie 提示', 'developer-starter' ), __( '出海站常用的 Cookie 提示组件。', 'developer-starter' ) ),
            'github_activity'      => $this->item( __( 'GitHub 项目动态', 'developer-starter' ), __( '公开仓库数据看板，启用后才访问 GitHub。', 'developer-starter' ) ),
        );

        $filtered = apply_filters( 'developer_starter_setup_wizard_feature_catalog', $catalog, $this );
        return $this->sanitize_catalog( $filtered );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_optional_plugin_catalog() {
        $catalog = array(
            'qiling_forms'  => $this->item( __( '启灵表单', 'developer-starter' ), __( '可增强咨询、报名、预约和线索收集。只提示，不安装不配置。', 'developer-starter' ) ),
            'woocommerce'   => $this->item( __( 'WooCommerce', 'developer-starter' ), __( '可增强电商和商品售卖。只提示，不安装不配置。', 'developer-starter' ) ),
            'qilingshop'    => $this->item( __( '积分商城', 'developer-starter' ), __( '可增强积分兑换和会员权益。只提示，不安装不配置。', 'developer-starter' ) ),
            'qiling_weixin' => $this->item( __( '启灵微信登录', 'developer-starter' ), __( '可增强微信登录能力。只提示，不安装不配置。', 'developer-starter' ) ),
            'qilingapp'     => $this->item( __( '启灵应用', 'developer-starter' ), __( '可增强软件/资源应用目录。只提示，不安装不配置。', 'developer-starter' ) ),
        );

        $filtered = apply_filters( 'developer_starter_setup_wizard_optional_plugin_catalog', $catalog, $this );
        return $this->sanitize_catalog( $filtered );
    }

    /**
     * @param string $label Label.
     * @param string $template Template path.
     * @param string $package Official package filename.
     * @return array<string,mixed>
     */
    private function template( $label, $template, $package = '' ) {
        return array(
            'label'    => $label,
            'template' => $template,
            'package'  => $package,
        );
    }

    /**
     * @param string $label Page label.
     * @param string $slug Suggested slug.
     * @param string $template_id Recommended template id.
     * @return array<string,mixed>
     */
    private function page( $label, $slug, $template_id = '' ) {
        return array(
            'label'       => $label,
            'slug'        => $slug,
            'template_id' => $template_id,
        );
    }

    /**
     * @param string $label Label.
     * @param string $description Description.
     * @return array<string,mixed>
     */
    private function item( $label, $description = '' ) {
        return array(
            'label'       => $label,
            'description' => $description,
        );
    }

    /**
     * @param array<string,array<string,mixed>> $presets Presets.
     * @return array<string,string>
     */
    private function get_choice_labels( $presets ) {
        $choices = array();
        foreach ( $presets as $key => $preset ) {
            $choices[ $key ] = isset( $preset['label'] ) ? (string) $preset['label'] : $this->humanize_key( $key );
        }

        return $choices;
    }

    /**
     * @param mixed $presets Raw presets.
     * @return array<string,array<string,mixed>>
     */
    private function sanitize_preset_collection( $presets ) {
        $clean = array();
        if ( ! is_array( $presets ) ) {
            return $clean;
        }

        foreach ( $presets as $key => $preset ) {
            $key = sanitize_key( (string) $key );
            if ( '' === $key || ! is_array( $preset ) ) {
                continue;
            }

            $clean[ $key ] = array(
                'label'            => isset( $preset['label'] ) ? $this->sanitize_short_text( $preset['label'], 80 ) : $this->humanize_key( $key ),
                'description'      => isset( $preset['description'] ) ? $this->sanitize_short_text( $preset['description'], 180 ) : '',
                'default_industry' => isset( $preset['default_industry'] ) ? sanitize_key( (string) $preset['default_industry'] ) : '',
                'templates'        => $this->normalize_key_list( isset( $preset['templates'] ) ? $preset['templates'] : array() ),
                'pages'            => $this->normalize_key_list( isset( $preset['pages'] ) ? $preset['pages'] : array() ),
                'content_models'   => $this->normalize_key_list( isset( $preset['content_models'] ) ? $preset['content_models'] : array() ),
                'features'         => $this->normalize_key_list( isset( $preset['features'] ) ? $preset['features'] : array() ),
                'optional_plugins' => $this->normalize_key_list( isset( $preset['optional_plugins'] ) ? $preset['optional_plugins'] : array() ),
            );

            if ( count( $clean ) >= self::MAX_ITEMS ) {
                break;
            }
        }

        return $clean;
    }

    /**
     * @param array<string,array<string,mixed>> $raw Raw catalog.
     * @return array<string,array<string,mixed>>
     */
    private function sanitize_catalog( $raw ) {
        $clean = array();
        if ( ! is_array( $raw ) ) {
            return $clean;
        }

        foreach ( $raw as $key => $item ) {
            $key = sanitize_key( (string) $key );
            if ( '' === $key || ! is_array( $item ) ) {
                continue;
            }

            $clean[ $key ] = $this->sanitize_item( array_merge( array( 'id' => $key ), $item ) );
            if ( count( $clean ) >= self::MAX_ITEMS * 4 ) {
                break;
            }
        }

        return $clean;
    }

    /**
     * @param array<string,mixed> $raw Raw merged config.
     * @param array<string,mixed> $preset Preset config.
     * @return array<string,mixed>
     */
    private function merge_preset_config( $raw, $preset ) {
        foreach ( array( 'templates', 'pages', 'content_models', 'features', 'optional_plugins' ) as $key ) {
            $existing = isset( $raw[ $key ] ) ? $raw[ $key ] : array();
            $incoming = isset( $preset[ $key ] ) ? $preset[ $key ] : array();
            $raw[ $key ] = $this->merge_key_lists( $existing, $incoming );
        }

        return $raw;
    }

    /**
     * @param mixed                                 $keys Keys.
     * @param array<string,array<string,mixed>>     $catalog Catalog.
     * @return array<int,array<string,mixed>>
     */
    private function expand_catalog_items( $keys, $catalog ) {
        $items = array();
        foreach ( $this->normalize_key_list( $keys ) as $key ) {
            $items[] = isset( $catalog[ $key ] )
                ? $this->sanitize_item( $catalog[ $key ] )
                : $this->sanitize_item( array( 'id' => $key, 'label' => $this->humanize_key( $key ) ) );
        }

        return $items;
    }

    /**
     * @param mixed $preset Preset.
     * @return array<string,mixed>
     */
    private function sanitize_resolved_preset( $preset ) {
        $preset = is_array( $preset ) ? $preset : array();

        $normalized = array(
            'site_type'             => isset( $preset['site_type'] ) ? sanitize_key( (string) $preset['site_type'] ) : self::DEFAULT_SITE_TYPE,
            'site_type_label'       => isset( $preset['site_type_label'] ) ? $this->sanitize_short_text( $preset['site_type_label'], 80 ) : '',
            'site_type_description' => isset( $preset['site_type_description'] ) ? $this->sanitize_short_text( $preset['site_type_description'], 180 ) : '',
            'industry'              => isset( $preset['industry'] ) ? sanitize_key( (string) $preset['industry'] ) : self::DEFAULT_INDUSTRY,
            'industry_label'        => isset( $preset['industry_label'] ) ? $this->sanitize_short_text( $preset['industry_label'], 80 ) : '',
            'industry_description'  => isset( $preset['industry_description'] ) ? $this->sanitize_short_text( $preset['industry_description'], 180 ) : '',
            'recommended_templates' => $this->sanitize_item_list( isset( $preset['recommended_templates'] ) ? $preset['recommended_templates'] : array() ),
            'recommended_pages'     => $this->sanitize_item_list( isset( $preset['recommended_pages'] ) ? $preset['recommended_pages'] : array() ),
            'content_models'        => $this->sanitize_item_list( isset( $preset['content_models'] ) ? $preset['content_models'] : array() ),
            'features'              => $this->sanitize_item_list( isset( $preset['features'] ) ? $preset['features'] : array() ),
            'optional_plugins'      => $this->sanitize_item_list( isset( $preset['optional_plugins'] ) ? $preset['optional_plugins'] : array() ),
        );

        $normalized['content_model_keys'] = $this->items_to_keys( $normalized['content_models'] );
        $normalized['feature_keys']       = $this->items_to_keys( $normalized['features'] );

        return $normalized;
    }

    /**
     * @param mixed $items Raw item list.
     * @return array<int,array<string,mixed>>
     */
    private function sanitize_item_list( $items ) {
        $clean = array();
        if ( ! is_array( $items ) ) {
            return $clean;
        }

        foreach ( $items as $key => $item ) {
            if ( is_string( $item ) || is_numeric( $item ) ) {
                $item = array(
                    'id'    => sanitize_key( (string) $item ),
                    'label' => $this->humanize_key( (string) $item ),
                );
            } elseif ( is_array( $item ) && empty( $item['id'] ) && ! is_int( $key ) ) {
                $item['id'] = sanitize_key( (string) $key );
            }

            if ( ! is_array( $item ) ) {
                continue;
            }

            $item = $this->sanitize_item( $item );
            if ( ! empty( $item['id'] ) ) {
                $clean[] = $item;
            }

            if ( count( $clean ) >= self::MAX_ITEMS ) {
                break;
            }
        }

        return $clean;
    }

    /**
     * @param array<string,mixed> $item Raw item.
     * @return array<string,mixed>
     */
    private function sanitize_item( $item ) {
        $id = isset( $item['id'] ) ? sanitize_key( (string) $item['id'] ) : '';
        if ( '' === $id ) {
            $id = isset( $item['key'] ) ? sanitize_key( (string) $item['key'] ) : '';
        }

        $clean = array(
            'id'          => $id,
            'label'       => isset( $item['label'] ) ? $this->sanitize_short_text( $item['label'], 90 ) : $this->humanize_key( $id ),
            'description' => isset( $item['description'] ) ? $this->sanitize_short_text( $item['description'], 180 ) : '',
        );

        foreach ( array( 'template', 'package', 'slug', 'template_id' ) as $field ) {
            if ( isset( $item[ $field ] ) && '' !== (string) $item[ $field ] ) {
                $clean[ $field ] = $this->sanitize_identifier_path( $item[ $field ] );
            }
        }

        return array_filter(
            $clean,
            function ( $value ) {
                return '' !== $value && array() !== $value;
            }
        );
    }

    /**
     * @param array<int,array<string,mixed>> $items Items.
     * @return array<int,string>
     */
    private function items_to_keys( $items ) {
        $keys = array();
        foreach ( (array) $items as $item ) {
            if ( is_array( $item ) && ! empty( $item['id'] ) ) {
                $keys[] = sanitize_key( (string) $item['id'] );
            }
        }

        return array_values( array_unique( array_filter( $keys ) ) );
    }

    /**
     * @param mixed $a First list.
     * @param mixed $b Second list.
     * @return array<int,string>
     */
    private function merge_key_lists( $a, $b ) {
        return array_values( array_unique( array_merge( $this->normalize_key_list( $a ), $this->normalize_key_list( $b ) ) ) );
    }

    /**
     * @param mixed $list Raw list.
     * @return array<int,string>
     */
    private function normalize_key_list( $list ) {
        $clean = array();
        foreach ( (array) $list as $value ) {
            $value = sanitize_key( (string) $value );
            if ( '' !== $value ) {
                $clean[] = $value;
            }

            if ( count( $clean ) >= self::MAX_ITEMS ) {
                break;
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * @param mixed $value Raw text.
     * @param int   $max_length Max bytes.
     * @return string
     */
    private function sanitize_short_text( $value, $max_length = 120 ) {
        $value = sanitize_text_field( (string) $value );
        if ( strlen( $value ) > $max_length ) {
            $value = substr( $value, 0, $max_length );
        }

        return $value;
    }

    /**
     * @param mixed $value Raw identifier path.
     * @return string
     */
    private function sanitize_identifier_path( $value ) {
        $value = sanitize_text_field( (string) $value );
        $value = preg_replace( '/[^A-Za-z0-9_\-\.\/]/', '', $value );

        return substr( (string) $value, 0, 140 );
    }

    /**
     * @param string $key Key.
     * @return string
     */
    private function humanize_key( $key ) {
        $key = str_replace( array( '_', '-' ), ' ', (string) $key );
        $key = trim( $key );

        return '' !== $key ? ucwords( $key ) : __( '未命名', 'developer-starter' );
    }
}
