<?php
/**
 * Module standards and catalog metadata helpers.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Central vocabulary for module catalog, builder selection and future site packages.
 */
class Module_Standards {

    const CATALOG_SCHEMA_VERSION = '2.6.0';
    const MODULE_DATA_SCHEMA_VERSION = '2.0.0';

    /**
     * Visual controls supported by each module's actual render tree.
     * Functional controls such as tabs, carousel arrows and media controls are not buttons here.
     *
     * @param string $module_id Module id.
     * @return array<string,bool>
     */
    public static function get_design_capabilities( $module_id ) {
        $rows = array(
            'banner' => '11010', 'services' => '11001', 'features' => '11001', 'clients' => '11001',
            'stats' => '11001', 'cta' => '11010', 'image_text' => '11110', 'columns' => '11001',
            'timeline' => '10001', 'faq' => '11001', 'contact' => '11011', 'news' => '10011',
            'products' => '11011', 'work_library' => '11001', 'work_detail' => '11111', 'cases' => '10001',
            'downloads' => '11011', 'process' => '11001', 'pricing' => '11011', 'video' => '11000',
            'testimonials' => '11001', 'countdown' => '11110', 'multi_image_text' => '11001', 'features_list' => '11001',
            'team' => '11001', 'gallery' => '11001', 'certificate_honors' => '11011', 'compliance_trust' => '11011',
            'branches' => '11011', 'tabs' => '11001', 'visual_tabs' => '11011', 'accordion' => '11001',
            'comparison' => '11000', 'image_comparison' => '11000', 'chart' => '00000', 'blog' => '11011',
            'query_loop' => '11011', 'featured_posts' => '10001', 'breaking_news_ticker' => '00000', 'author_matrix' => '11011',
            'about_me_card' => '11000', 'friendly_links' => '11001', 'media_list' => '11011', 'micro_journal_stream' => '11011',
            'reader_wall' => '11011', 'knowledge_cards' => '11011', 'double_column_carousel' => '00001', 'product_showcase' => '11110',
            'hero_search' => '11010', 'service_cards' => '00001', 'magic_layout' => '10110', 'footer_suite' => '00101',
            'software_carousel' => '11011', 'software_category' => '11011', 'software_ranking' => '11011', 'github_activity' => '11011',
            'resume_hero' => '11110', 'skills' => '11001', 'experience_timeline' => '11001', 'qiling_image_guide' => '11001',
            'category_tabs' => '11011', 'resource_stats' => '11001', 'qiling_universal_recommend' => '11011', 'dynamic_banner' => '11111',
            'brand_banner_pro' => '11110', 'resource_hero_pro' => '11111', 'app_hero' => '10111', 'tabbed_carousel' => '11001',
            'fullscreen_video' => '11001', 'circle_wheel' => '11001', 'interact_hero' => '11011', 'qiling_main_category_content' => '10011',
            'qiling_video_portal_hero' => '11111', 'qiling_video_ranking' => '11011', 'qiling_shop_showcase' => '11001', 'menu' => '11001',
            'curriculum' => '11001', 'pet_profile' => '11001', 'lookbook' => '10001', 'promotion' => '11011',
            'tour-package' => '11001', 'itinerary' => '11001', 'ticket-showcase' => '11011', 'room-showcase' => '11011',
            'hotel-amenities' => '11001', 'booking-entry' => '11010',
        );
        $keys = array( 'title', 'subtitle', 'text', 'buttons', 'cards' );
        $row  = isset( $rows[ $module_id ] ) ? $rows[ $module_id ] : '00000';

        return array_combine( $keys, array_map( static function ( $value ) { return '1' === $value; }, str_split( $row ) ) );
    }

    /**
     * Get catalog schema version.
     *
     * @return string
     */
    public static function get_catalog_schema_version() {
        return self::CATALOG_SCHEMA_VERSION;
    }

    /**
     * Get module data schema version.
     *
     * @return string
     */
    public static function get_module_data_schema_version() {
        return self::MODULE_DATA_SCHEMA_VERSION;
    }

    /**
     * Metadata taxonomy exposed to builders and package tools.
     *
     * @return array<string,array<string,string>>
     */
    public static function get_metadata_taxonomy() {
        return array(
            'industryTags' => self::get_industry_labels(),
            'pageTags' => array(
                'section'  => __( '通用区块', 'developer-starter' ),
                'home'     => __( '首页', 'developer-starter' ),
                'about'    => __( '关于', 'developer-starter' ),
                'services' => __( '服务', 'developer-starter' ),
                'products' => __( '产品', 'developer-starter' ),
                'cases'    => __( '案例', 'developer-starter' ),
                'blog'     => __( '博客', 'developer-starter' ),
                'news'     => __( '新闻资讯', 'developer-starter' ),
                'resource' => __( '资源', 'developer-starter' ),
                'contact'  => __( '联系', 'developer-starter' ),
                'landing'  => __( '落地页', 'developer-starter' ),
                'pricing'  => __( '价格', 'developer-starter' ),
                'faq'      => __( 'FAQ', 'developer-starter' ),
                'careers'  => __( '招聘', 'developer-starter' ),
                'account'  => __( '账户', 'developer-starter' ),
                'search'   => __( '搜索', 'developer-starter' ),
                'detail'   => __( '详情页', 'developer-starter' ),
                'profile'  => __( '个人资料', 'developer-starter' ),
                'course'   => __( '课程', 'developer-starter' ),
                'event'    => __( '活动', 'developer-starter' ),
                'booking'  => __( '预约', 'developer-starter' ),
                'shop'     => __( '商城', 'developer-starter' ),
            ),
            'intentTags' => array(
                'hero'          => __( '首屏吸引', 'developer-starter' ),
                'navigation'    => __( '导航分流', 'developer-starter' ),
                'conversion'    => __( '转化引导', 'developer-starter' ),
                'trust'         => __( '建立信任', 'developer-starter' ),
                'proof'         => __( '实力证明', 'developer-starter' ),
                'content'       => __( '内容展示', 'developer-starter' ),
                'listing'       => __( '列表聚合', 'developer-starter' ),
                'detail'        => __( '详情承载', 'developer-starter' ),
                'search'        => __( '搜索筛选', 'developer-starter' ),
                'education'     => __( '教育说明', 'developer-starter' ),
                'lead_capture'  => __( '线索收集', 'developer-starter' ),
                'commerce'      => __( '商品销售', 'developer-starter' ),
                'media'         => __( '媒体呈现', 'developer-starter' ),
                'storytelling'  => __( '品牌叙事', 'developer-starter' ),
                'comparison'    => __( '对比决策', 'developer-starter' ),
                'pricing'       => __( '价格决策', 'developer-starter' ),
                'social'        => __( '社交背书', 'developer-starter' ),
                'support'       => __( '支持答疑', 'developer-starter' ),
            ),
            'contentModels' => array(
                'page'        => __( '页面', 'developer-starter' ),
                'post'        => __( '文章', 'developer-starter' ),
                'service'     => __( '服务', 'developer-starter' ),
                'product'     => __( '产品', 'developer-starter' ),
                'case'        => __( '案例', 'developer-starter' ),
                'testimonial' => __( '评价', 'developer-starter' ),
                'team'        => __( '团队', 'developer-starter' ),
                'branch'      => __( '门店/分支', 'developer-starter' ),
                'faq'         => __( 'FAQ', 'developer-starter' ),
                'download'    => __( '下载资料', 'developer-starter' ),
                'course'      => __( '课程', 'developer-starter' ),
                'event'       => __( '活动', 'developer-starter' ),
                'job'         => __( '职位', 'developer-starter' ),
                'room'        => __( '房型', 'developer-starter' ),
                'menu_item'   => __( '菜单菜品', 'developer-starter' ),
                'software'    => __( '软件应用', 'developer-starter' ),
                'resource'    => __( '资源', 'developer-starter' ),
                'media_item'  => __( '媒体条目', 'developer-starter' ),
                'author'      => __( '作者', 'developer-starter' ),
                'partner'     => __( '合作伙伴', 'developer-starter' ),
            ),
            'schemaTypes' => array(
                'Organization'        => 'Organization',
                'LocalBusiness'       => 'LocalBusiness',
                'WebSite'             => 'WebSite',
                'BreadcrumbList'      => 'BreadcrumbList',
                'FAQPage'             => 'FAQPage',
                'Product'             => 'Product',
                'Service'             => 'Service',
                'Article'             => 'Article',
                'BlogPosting'         => 'BlogPosting',
                'CollectionPage'      => 'CollectionPage',
                'ItemList'            => 'ItemList',
                'Review'              => 'Review',
                'AggregateRating'     => 'AggregateRating',
                'Course'              => 'Course',
                'Event'               => 'Event',
                'JobPosting'          => 'JobPosting',
                'Restaurant'          => 'Restaurant',
                'Hotel'               => 'Hotel',
                'MedicalClinic'       => 'MedicalClinic',
                'Dentist'             => 'Dentist',
                'LegalService'        => 'LegalService',
                'AutoRepair'          => 'AutoRepair',
                'SoftwareApplication' => 'SoftwareApplication',
                'Person'              => 'Person',
            ),
        );
    }

    /**
     * Canonical industry standards shared by modules, templates and packages.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function get_industry_standards() {
        return array(
            'general'        => array( 'label' => __( '通用', 'developer-starter' ), 'group' => 'general', 'schemaTypes' => array( 'Organization', 'WebSite' ) ),
            'enterprise'     => array( 'label' => __( '企业官网', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'WebSite' ) ),
            'manufacturing'  => array( 'label' => __( '制造业', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Product' ) ),
            'b2b'            => array( 'label' => __( 'B2B', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Product' ) ),
            'logistics'      => array( 'label' => __( '物流供应链', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'overseas_warehouse_supply_chain' => array( 'label' => __( '海外仓/跨境供应链', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'agriculture_food' => array( 'label' => __( '农业食品', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Product' ) ),
            'energy_environment' => array( 'label' => __( '能源环保', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'solar_storage_equipment' => array( 'label' => __( '光伏储能设备', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Product', 'Service' ) ),
            'water_treatment_environmental' => array( 'label' => __( '水处理/环保工程', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'industrial_park' => array( 'label' => __( '产业园区', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Place' ) ),
            'semiconductor_electronics' => array( 'label' => __( '半导体/电子元器件', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Product' ) ),
            'industrial_automation_robotics' => array( 'label' => __( '工业机器人/自动化', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Product', 'Service' ) ),
            'technology'     => array( 'label' => __( '科技', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'Organization', 'SoftwareApplication' ) ),
            'software'       => array( 'label' => __( '软件', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Product' ) ),
            'enterprise_software_integrator' => array( 'label' => __( '企业软件/SI 集成商', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'Organization', 'SoftwareApplication', 'Service' ) ),
            'saas'           => array( 'label' => __( 'SaaS', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Product' ) ),
            'app'            => array( 'label' => __( 'APP', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'MobileApplication' ) ),
            'ai_agent_enterprise' => array( 'label' => __( '企业 AI Agent', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'Organization', 'SoftwareApplication', 'Service' ) ),
            'ai_writing'     => array( 'label' => __( 'AI 写作', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'multilingual_seo' => array( 'label' => __( '多语言 SEO', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'document_ocr'   => array( 'label' => __( '文档 OCR', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'ai_image'       => array( 'label' => __( 'AI 图像', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'cloud_storage'  => array( 'label' => __( '云存储图床', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'security_ops'   => array( 'label' => __( '安全运维', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'escrow_trading' => array( 'label' => __( '担保交易', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'freelance_task' => array( 'label' => __( '悬赏众包', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'matchmaking'    => array( 'label' => __( '相亲婚恋', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'community_support' => array( 'label' => __( '社区工单', 'developer-starter' ), 'group' => 'technology', 'schemaTypes' => array( 'SoftwareApplication', 'Service' ) ),
            'education'      => array( 'label' => __( '教育培训', 'developer-starter' ), 'group' => 'service', 'schemaTypes' => array( 'EducationalOrganization', 'Course' ) ),
            'study_abroad'   => array( 'label' => __( '留学移民', 'developer-starter' ), 'group' => 'education', 'schemaTypes' => array( 'EducationalOrganization', 'Service' ) ),
            'early_childhood' => array( 'label' => __( '幼儿早教', 'developer-starter' ), 'group' => 'education', 'schemaTypes' => array( 'EducationalOrganization', 'ChildCare' ) ),
            'vocational_training' => array( 'label' => __( '职业培训', 'developer-starter' ), 'group' => 'education', 'schemaTypes' => array( 'EducationalOrganization', 'Course' ) ),
            'psychological_counseling' => array( 'label' => __( '心理咨询', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'MedicalBusiness', 'ProfessionalService' ) ),
            'senior_care'    => array( 'label' => __( '养老康养', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'MedicalBusiness', 'LocalBusiness' ) ),
            'postpartum_care' => array( 'label' => __( '月子产康', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'MedicalBusiness', 'HealthAndBeautyBusiness' ) ),
            'human_resources' => array( 'label' => __( '人力资源', 'developer-starter' ), 'group' => 'service', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'healthcare'     => array( 'label' => __( '医疗健康', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'MedicalOrganization', 'MedicalBusiness' ) ),
            'medical_device' => array( 'label' => __( '医疗器械', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'Organization', 'Product', 'Service' ) ),
            'lab_instrument' => array( 'label' => __( '实验室设备/科研仪器', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Product', 'Service' ) ),
            'dental'         => array( 'label' => __( '牙科', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'Dentist', 'MedicalClinic' ) ),
            'medical_beauty' => array( 'label' => __( '医美', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'MedicalBusiness', 'HealthAndBeautyBusiness' ) ),
            'law'            => array( 'label' => __( '律师法务', 'developer-starter' ), 'group' => 'professional', 'schemaTypes' => array( 'LegalService', 'ProfessionalService' ) ),
            'restaurant'     => array( 'label' => __( '餐饮', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'Restaurant', 'Menu' ) ),
            'hospitality'    => array( 'label' => __( '酒店民宿', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'LodgingBusiness', 'Hotel' ) ),
            'travel'         => array( 'label' => __( '旅游', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'TouristTrip', 'TravelAgency' ) ),
            'pet'            => array( 'label' => __( '宠物', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'LocalBusiness', 'Service' ) ),
            'automotive'     => array( 'label' => __( '汽车服务', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'AutomotiveBusiness', 'AutoRepair' ) ),
            'real_estate'    => array( 'label' => __( '房地产', 'developer-starter' ), 'group' => 'property', 'schemaTypes' => array( 'RealEstateAgent', 'Residence' ) ),
            'property_management' => array( 'label' => __( '物业社区', 'developer-starter' ), 'group' => 'property', 'schemaTypes' => array( 'LocalBusiness', 'Service' ) ),
            'architecture_design' => array( 'label' => __( '建筑设计', 'developer-starter' ), 'group' => 'property', 'schemaTypes' => array( 'ProfessionalService', 'HomeAndConstructionBusiness' ) ),
            'interior_design' => array( 'label' => __( '室内软装', 'developer-starter' ), 'group' => 'property', 'schemaTypes' => array( 'HomeAndConstructionBusiness', 'ProfessionalService' ) ),
            'landscape_garden' => array( 'label' => __( '园林景观', 'developer-starter' ), 'group' => 'property', 'schemaTypes' => array( 'HomeAndConstructionBusiness', 'ProfessionalService' ) ),
            'appliance_repair' => array( 'label' => __( '维修安装', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'HomeAndConstructionBusiness', 'LocalBusiness' ) ),
            'renovation'     => array( 'label' => __( '装修建筑', 'developer-starter' ), 'group' => 'property', 'schemaTypes' => array( 'HomeAndConstructionBusiness', 'ProfessionalService' ) ),
            'ecommerce'      => array( 'label' => __( '电商零售', 'developer-starter' ), 'group' => 'commerce', 'schemaTypes' => array( 'OnlineStore', 'Product' ) ),
            'cross_border_ecommerce_service' => array( 'label' => __( '跨境电商服务', 'developer-starter' ), 'group' => 'commerce', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'mcn_live_commerce' => array( 'label' => __( 'MCN/直播电商', 'developer-starter' ), 'group' => 'commerce', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'media'          => array( 'label' => __( '媒体资讯', 'developer-starter' ), 'group' => 'content', 'schemaTypes' => array( 'NewsMediaOrganization', 'Article' ) ),
            'conference_event_service' => array( 'label' => __( '会议会展/活动承办', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Event', 'Organization', 'Service' ) ),
            'magazine'       => array( 'label' => __( '杂志', 'developer-starter' ), 'group' => 'content', 'schemaTypes' => array( 'Periodical', 'Article' ) ),
            'blog'           => array( 'label' => __( '博客', 'developer-starter' ), 'group' => 'content', 'schemaTypes' => array( 'Blog', 'BlogPosting' ) ),
            'personal'       => array( 'label' => __( '个人IP', 'developer-starter' ), 'group' => 'personal', 'schemaTypes' => array( 'Person', 'ProfilePage' ) ),
            'nonprofit'      => array( 'label' => __( '公益组织', 'developer-starter' ), 'group' => 'public', 'schemaTypes' => array( 'NGO', 'Organization' ) ),
            'government'     => array( 'label' => __( '政务机构', 'developer-starter' ), 'group' => 'public', 'schemaTypes' => array( 'GovernmentOrganization', 'WebSite' ) ),
            'finance'        => array( 'label' => __( '金融保险', 'developer-starter' ), 'group' => 'professional', 'schemaTypes' => array( 'FinancialService', 'InsuranceAgency' ) ),
            'accounting_tax' => array( 'label' => __( '代理记账/会计税务', 'developer-starter' ), 'group' => 'professional', 'schemaTypes' => array( 'AccountingService', 'ProfessionalService' ) ),
            'intellectual_property' => array( 'label' => __( '知识产权', 'developer-starter' ), 'group' => 'professional', 'schemaTypes' => array( 'LegalService', 'ProfessionalService' ) ),
            'wellness'       => array( 'label' => __( '康养', 'developer-starter' ), 'group' => 'health', 'schemaTypes' => array( 'HealthAndBeautyBusiness', 'LocalBusiness' ) ),
            'fitness'        => array( 'label' => __( '健身', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'ExerciseGym', 'SportsActivityLocation' ) ),
            'beauty'         => array( 'label' => __( '美业', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'HealthAndBeautyBusiness', 'LocalBusiness' ) ),
            'wedding'        => array( 'label' => __( '婚庆摄影', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'LocalBusiness', 'Service' ) ),
            'franchise'      => array( 'label' => __( '招商加盟', 'developer-starter' ), 'group' => 'business', 'schemaTypes' => array( 'Organization', 'Service' ) ),
            'recycling'      => array( 'label' => __( '回收服务', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'LocalBusiness', 'Service' ) ),
            'housekeeping'   => array( 'label' => __( '家政服务', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'LocalBusiness', 'Service' ) ),
            'local_service'  => array( 'label' => __( '本地服务', 'developer-starter' ), 'group' => 'local', 'schemaTypes' => array( 'LocalBusiness', 'Service' ) ),
        );
    }

    /**
     * Get canonical industry labels.
     *
     * @return array<string,string>
     */
    public static function get_industry_labels() {
        $labels = array();
        foreach ( self::get_industry_standards() as $key => $config ) {
            $labels[ $key ] = isset( $config['label'] ) && is_scalar( $config['label'] ) ? (string) $config['label'] : self::humanize_key( $key );
        }

        return $labels;
    }

    /**
     * Legacy industry key aliases.
     *
     * @return array<string,string>
     */
    public static function get_industry_aliases() {
        return array(
            'ai'                   => 'technology',
            'writing_ai'           => 'ai_writing',
            'ai_content'           => 'ai_writing',
            'content_ai'           => 'ai_writing',
            'ai_multilingual'      => 'multilingual_seo',
            'multilingual_ai'      => 'multilingual_seo',
            'cross_border_seo'     => 'multilingual_seo',
            'aifanyi'              => 'multilingual_seo',
            'enterprise_software'  => 'enterprise_software_integrator',
            'system_integrator'    => 'enterprise_software_integrator',
            'si_integrator'        => 'enterprise_software_integrator',
            'software_integrator'  => 'enterprise_software_integrator',
            'enterprise_it'        => 'enterprise_software_integrator',
            'erp_integrator'       => 'enterprise_software_integrator',
            'crm_integrator'       => 'enterprise_software_integrator',
            'oa_system'            => 'enterprise_software_integrator',
            'system_integration'   => 'enterprise_software_integrator',
            'ai_agent'             => 'ai_agent_enterprise',
            'enterprise_ai_agent'  => 'ai_agent_enterprise',
            'agentic_ai'           => 'ai_agent_enterprise',
            'ai_copilot'           => 'ai_agent_enterprise',
            'enterprise_copilot'   => 'ai_agent_enterprise',
            'ai_workflow'          => 'ai_agent_enterprise',
            'ai_employee'          => 'ai_agent_enterprise',
            'knowledge_agent'      => 'ai_agent_enterprise',
            'doc_ocr'              => 'document_ocr',
            'document_converter'   => 'document_ocr',
            'pdf_converter'        => 'document_ocr',
            'ocr_converter'        => 'document_ocr',
            'image_ai'             => 'ai_image',
            'ai_image_studio'      => 'ai_image',
            'image_studio'         => 'ai_image',
            'aigc_image'           => 'ai_image',
            'storage'              => 'cloud_storage',
            'cloud'                => 'cloud_storage',
            'cloud_hosting'        => 'cloud_storage',
            'image_hosting'        => 'cloud_storage',
            'oss_cos'              => 'cloud_storage',
            'security'             => 'security_ops',
            'cybersecurity'        => 'security_ops',
            'security_operations'  => 'security_ops',
            'security_ops_service' => 'security_ops',
            'waf_security'         => 'security_ops',
            'escrow'               => 'escrow_trading',
            'escrow_platform'      => 'escrow_trading',
            'guarantee_trade'      => 'escrow_trading',
            'trustee_payment'      => 'escrow_trading',
            'freetask'             => 'freelance_task',
            'free_task'            => 'freelance_task',
            'task_marketplace'     => 'freelance_task',
            'crowdsourcing'        => 'freelance_task',
            'friends'              => 'matchmaking',
            'dating'               => 'matchmaking',
            'matchmaking_service'  => 'matchmaking',
            'marriage_service'     => 'matchmaking',
            'bbs'                  => 'community_support',
            'community'            => 'community_support',
            'support_community'    => 'community_support',
            'ticket_support'       => 'community_support',
            'auto'                 => 'automotive',
            'auto_service'         => 'automotive',
            'car'                  => 'automotive',
            'cars'                 => 'automotive',
            'construction'         => 'renovation',
            'dental_clinic'        => 'dental',
            'education_training'   => 'education',
            'early_childhood_education' => 'early_childhood',
            'early_education'      => 'early_childhood',
            'kindergarten'         => 'early_childhood',
            'preschool'            => 'early_childhood',
            'nursery'              => 'early_childhood',
            'daycare'              => 'early_childhood',
            'childcare'            => 'early_childhood',
            'vocational_training_school' => 'vocational_training',
            'career_training'      => 'vocational_training',
            'job_training'         => 'vocational_training',
            'skills_training'      => 'vocational_training',
            'certificate_training' => 'vocational_training',
            'trade_school'         => 'vocational_training',
            'psychological_counseling_center' => 'psychological_counseling',
            'psychology'           => 'psychological_counseling',
            'psychological'        => 'psychological_counseling',
            'counseling'           => 'psychological_counseling',
            'counselling'          => 'psychological_counseling',
            'mental_health'        => 'psychological_counseling',
            'therapy'              => 'psychological_counseling',
            'senior_care_center'   => 'senior_care',
            'elder_care'           => 'senior_care',
            'elderly_care'         => 'senior_care',
            'nursing_home'         => 'senior_care',
            'retirement_home'      => 'senior_care',
            'care_home'            => 'senior_care',
            'senior_living'        => 'senior_care',
            'aged_care'            => 'senior_care',
            'postpartum_care_center' => 'postpartum_care',
            'postpartum_center'    => 'postpartum_care',
            'postnatal_care'       => 'postpartum_care',
            'maternity_care'       => 'postpartum_care',
            'confinement_center'   => 'postpartum_care',
            'mother_baby_care'     => 'postpartum_care',
            'maternal_care'        => 'postpartum_care',
            'architecture_design_studio' => 'architecture_design',
            'architectural_design' => 'architecture_design',
            'architecture'         => 'architecture_design',
            'architect'            => 'architecture_design',
            'architects'           => 'architecture_design',
            'building_design'      => 'architecture_design',
            'design_studio'        => 'architecture_design',
            'interior_soft_decoration' => 'interior_design',
            'interior_design_studio' => 'interior_design',
            'interior'             => 'interior_design',
            'soft_decoration'      => 'interior_design',
            'soft_furnishing'      => 'interior_design',
            'home_styling'         => 'interior_design',
            'full_case_design'     => 'interior_design',
            'landscape_garden_design' => 'landscape_garden',
            'landscape_design'     => 'landscape_garden',
            'garden_design'        => 'landscape_garden',
            'garden_landscape'     => 'landscape_garden',
            'courtyard_design'     => 'landscape_garden',
            'garden_studio'        => 'landscape_garden',
            'outdoor_landscape'    => 'landscape_garden',
            'appliance_repair_service' => 'appliance_repair',
            'repair_service'       => 'appliance_repair',
            'home_repair'          => 'appliance_repair',
            'electrical_repair'    => 'appliance_repair',
            'plumbing_repair'      => 'appliance_repair',
            'handyman'             => 'appliance_repair',
            'maintenance_service'  => 'appliance_repair',
            'mcn'                  => 'mcn_live_commerce',
            'mcn_agency'           => 'mcn_live_commerce',
            'live_commerce'        => 'mcn_live_commerce',
            'live_streaming'       => 'mcn_live_commerce',
            'live_ecommerce'       => 'mcn_live_commerce',
            'influencer_marketing' => 'mcn_live_commerce',
            'short_video_agency'   => 'mcn_live_commerce',
            'cross_border'         => 'cross_border_ecommerce_service',
            'cross_border_ecommerce' => 'cross_border_ecommerce_service',
            'cross_border_service' => 'cross_border_ecommerce_service',
            'amazon_operation'     => 'cross_border_ecommerce_service',
            'amazon_service'       => 'cross_border_ecommerce_service',
            'tiktok_shop_service'  => 'cross_border_ecommerce_service',
            'shopify_service'      => 'cross_border_ecommerce_service',
            'overseas_marketing'   => 'cross_border_ecommerce_service',
            'conference_event'     => 'conference_event_service',
            'event_service'        => 'conference_event_service',
            'event_planning'       => 'conference_event_service',
            'event_execution'      => 'conference_event_service',
            'conference_service'   => 'conference_event_service',
            'exhibition_service'   => 'conference_event_service',
            'event_agency'         => 'conference_event_service',
            'semiconductor'        => 'semiconductor_electronics',
            'semiconductors'       => 'semiconductor_electronics',
            'electronic_components' => 'semiconductor_electronics',
            'electronics_components' => 'semiconductor_electronics',
            'chip_components'      => 'semiconductor_electronics',
            'ic_components'        => 'semiconductor_electronics',
            'industrial_automation' => 'industrial_automation_robotics',
            'automation_robotics'  => 'industrial_automation_robotics',
            'industrial_robotics'  => 'industrial_automation_robotics',
            'industrial_robot'     => 'industrial_automation_robotics',
            'robotics'             => 'industrial_automation_robotics',
            'robotic_automation'   => 'industrial_automation_robotics',
            'smart_factory'        => 'industrial_automation_robotics',
            'factory_automation'   => 'industrial_automation_robotics',
            'medical_devices'      => 'medical_device',
            'medical_equipment'    => 'medical_device',
            'healthcare_equipment' => 'medical_device',
            'medical_instrument'   => 'medical_device',
            'medical_instruments'  => 'medical_device',
            'medical_apparatus'    => 'medical_device',
            'lab_instruments'      => 'lab_instrument',
            'laboratory_equipment' => 'lab_instrument',
            'scientific_instrument' => 'lab_instrument',
            'scientific_instruments' => 'lab_instrument',
            'research_instrument'  => 'lab_instrument',
            'research_instruments' => 'lab_instrument',
            'lab_equipment'        => 'lab_instrument',
            'laboratory_instrument' => 'lab_instrument',
            'health'               => 'healthcare',
            'hotel'                => 'hospitality',
            'homestay'             => 'hospitality',
            'hr'                   => 'human_resources',
            'human_resource'       => 'human_resources',
            'recruitment'          => 'human_resources',
            'recruiting'           => 'human_resources',
            'talent'               => 'human_resources',
            'accounting'           => 'accounting_tax',
            'tax'                  => 'accounting_tax',
            'bookkeeping'          => 'accounting_tax',
            'tax_service'          => 'accounting_tax',
            'accounting_service'   => 'accounting_tax',
            'intellectual_property_service' => 'intellectual_property',
            'ipr'                  => 'intellectual_property',
            'ip_service'           => 'intellectual_property',
            'trademark'            => 'intellectual_property',
            'trademark_service'    => 'intellectual_property',
            'patent'               => 'intellectual_property',
            'patent_service'       => 'intellectual_property',
            'copyright_service'    => 'intellectual_property',
            'study_abroad_service' => 'study_abroad',
            'immigration'          => 'study_abroad',
            'immigration_service'  => 'study_abroad',
            'overseas_study'       => 'study_abroad',
            'visa_service'         => 'study_abroad',
            'legal'                => 'law',
            'logistic'             => 'logistics',
            'supply_chain'         => 'logistics',
            'warehouse'            => 'logistics',
            'overseas_warehouse'   => 'overseas_warehouse_supply_chain',
            'cross_border_supply_chain' => 'overseas_warehouse_supply_chain',
            'cross_border_logistics' => 'overseas_warehouse_supply_chain',
            'overseas_fulfillment' => 'overseas_warehouse_supply_chain',
            'fba_warehouse'        => 'overseas_warehouse_supply_chain',
            'fba_prep'             => 'overseas_warehouse_supply_chain',
            'third_party_logistics' => 'overseas_warehouse_supply_chain',
            'cross_border_3pl'     => 'overseas_warehouse_supply_chain',
            'local'                => 'local_service',
            'lodging'              => 'hospitality',
            'medical'              => 'healthcare',
            'medical_beauty_clinic' => 'medical_beauty',
            'agriculture'          => 'agriculture_food',
            'food'                 => 'agriculture_food',
            'food_supply'          => 'agriculture_food',
            'energy'               => 'energy_environment',
            'environment'          => 'energy_environment',
            'environmental'        => 'energy_environment',
            'solar'                => 'solar_storage_equipment',
            'photovoltaic'         => 'solar_storage_equipment',
            'solar_storage'        => 'solar_storage_equipment',
            'energy_storage'       => 'solar_storage_equipment',
            'solar_equipment'      => 'solar_storage_equipment',
            'photovoltaic_equipment' => 'solar_storage_equipment',
            'battery_storage'      => 'solar_storage_equipment',
            'bess'                 => 'solar_storage_equipment',
            'water_treatment'      => 'water_treatment_environmental',
            'wastewater_treatment' => 'water_treatment_environmental',
            'sewage_treatment'     => 'water_treatment_environmental',
            'environmental_engineering' => 'water_treatment_environmental',
            'environmental_project' => 'water_treatment_environmental',
            'water_recycling'      => 'water_treatment_environmental',
            'industrial_wastewater' => 'water_treatment_environmental',
            'zero_liquid_discharge' => 'water_treatment_environmental',
            'park'                 => 'industrial_park',
            'business_park'        => 'industrial_park',
            'property_management_service' => 'property_management',
            'community_service'    => 'property_management',
            'property'             => 'real_estate',
            'realestate'           => 'real_estate',
            'recycle'              => 'recycling',
            'secondhand'           => 'recycling',
            'cleaning'             => 'housekeeping',
            'home_service'         => 'housekeeping',
            'housekeeping_service' => 'housekeeping',
            'renovation_construction' => 'renovation',
            'salon'                => 'beauty',
            'beauty_salon'         => 'beauty',
            'wedding_photography'  => 'wedding',
            'yoga'                 => 'wellness',
            'yoga_studio'          => 'wellness',
        );
    }

    /**
     * Normalize an industry key to the canonical vocabulary.
     *
     * @param mixed $industry Industry key.
     * @return string
     */
    public static function normalize_industry_key( $industry ) {
        $key = is_scalar( $industry ) ? sanitize_key( str_replace( '-', '_', (string) $industry ) ) : '';
        if ( '' === $key ) {
            return 'general';
        }

        $aliases = self::get_industry_aliases();
        if ( isset( $aliases[ $key ] ) ) {
            return $aliases[ $key ];
        }

        return $key;
    }

    /**
     * Normalize a list of industry keys.
     *
     * @param mixed $value Raw value.
     * @return array<int,string>
     */
    public static function normalize_industry_key_list( $value ) {
        $items = is_array( $value ) ? $value : ( is_scalar( $value ) && '' !== (string) $value ? explode( ',', (string) $value ) : array() );
        $out = array();

        foreach ( $items as $item ) {
            $key = self::normalize_industry_key( $item );
            if ( '' !== $key ) {
                $out[] = $key;
            }
        }

        return array_values( array_unique( $out ) );
    }

    /**
     * Get a canonical industry label.
     *
     * @param mixed $industry Industry key.
     * @return string
     */
    public static function get_industry_label( $industry ) {
        $key = self::normalize_industry_key( $industry );
        $labels = self::get_industry_labels();

        return isset( $labels[ $key ] ) ? $labels[ $key ] : self::humanize_key( $key );
    }

    /**
     * Normalize explicit manifest metadata.
     *
     * @param array<string,mixed> $entry Manifest entry.
     * @return array<string,mixed>
     */
    public static function normalize_manifest_metadata( $entry ) {
        $entry = is_array( $entry ) ? $entry : array();

        return array(
            'catalogSchemaVersion' => self::CATALOG_SCHEMA_VERSION,
            'version'              => self::normalize_version( self::read_entry_value( $entry, array( 'version', 'module_version' ) ), '1.0.0' ),
            'status'               => self::normalize_status( self::read_entry_value( $entry, array( 'status' ) ) ),
            'catalogRole'          => self::normalize_catalog_role( self::read_entry_value( $entry, array( 'catalog_role', 'catalogRole' ) ), '' ),
            'metadataSource'       => self::has_explicit_catalog_metadata( $entry ) ? 'explicit' : 'inferred',
            'industryTags'         => self::normalize_industry_key_list( self::read_entry_value( $entry, array( 'industry_tags', 'industryTags', 'industries' ) ) ),
            'pageTags'             => self::normalize_key_list( self::read_entry_value( $entry, array( 'page_tags', 'pageTags', 'pages' ) ) ),
            'intentTags'           => self::normalize_key_list( self::read_entry_value( $entry, array( 'intent_tags', 'intentTags', 'intents' ) ) ),
            'contentModels'        => self::normalize_key_list( self::read_entry_value( $entry, array( 'content_models', 'contentModels' ) ) ),
            'schemaTypes'          => self::normalize_schema_type_list( self::read_entry_value( $entry, array( 'schema_types', 'schemaTypes' ) ) ),
            'assetHints'           => self::normalize_key_list( self::read_entry_value( $entry, array( 'asset_hints', 'assetHints' ) ) ),
            'aiHints'              => self::normalize_ai_hints( self::read_entry_value( $entry, array( 'ai_hints', 'aiHints' ) ) ),
        );
    }

    /**
     * Infer metadata from module id, group and existing labels.
     *
     * @param string              $module_id Module id.
     * @param object              $module Module instance.
     * @param array<string,mixed> $manifest Manifest data.
     * @return array<string,mixed>
     */
    public static function infer_module_metadata( $module_id, $module, $manifest = array() ) {
        $module_id = sanitize_key( (string) $module_id );
        $manifest  = is_array( $manifest ) ? $manifest : array();
        $group     = isset( $manifest['group'] ) ? sanitize_key( (string) $manifest['group'] ) : 'general';
        $category  = method_exists( $module, 'get_category' ) ? sanitize_key( (string) $module->get_category() ) : 'general';
        $name      = method_exists( $module, 'get_name' ) ? wp_strip_all_tags( (string) $module->get_name() ) : $module_id;
        $desc      = method_exists( $module, 'get_description' ) ? wp_strip_all_tags( (string) $module->get_description() ) : '';
        $keywords  = isset( $manifest['keywords'] ) && is_array( $manifest['keywords'] ) ? implode( ' ', array_map( 'strval', $manifest['keywords'] ) ) : '';
        $haystack  = strtolower( $module_id . ' ' . $group . ' ' . $category . ' ' . $name . ' ' . $desc . ' ' . $keywords );

        $industry_tags  = array( 'general' );
        $page_tags      = array( 'section' );
        $intent_tags    = array( 'content' );
        $content_models = array( 'page' );
        $schema_types   = array();
        $asset_hints    = array();
        $catalog_role   = self::infer_catalog_role( $group );

        if ( 'core' === $group || false !== strpos( $category, 'homepage' ) ) {
            $industry_tags[] = 'enterprise';
        }
        if ( 'software' === $group || self::contains_any( $haystack, array( 'software', 'saas', 'app', 'download' ) ) ) {
            $industry_tags  = array_merge( $industry_tags, array( 'technology', 'software', 'saas', 'app' ) );
            $content_models = array_merge( $content_models, array( 'software', 'resource' ) );
            $schema_types[] = 'SoftwareApplication';
        }
        if ( 'resume' === $group || self::contains_any( $haystack, array( 'resume', 'author', 'personal', 'profile' ) ) ) {
            $industry_tags[] = 'personal';
            $page_tags[]     = 'profile';
            $schema_types[]  = 'Person';
        }
        if ( 'industry' === $group ) {
            $industry_tags[] = 'local_service';
        }

        $rules = array(
            array( array( 'banner', 'hero' ), array( 'home', 'landing' ), array( 'hero', 'conversion', 'storytelling' ), array(), array(), array( 'image', 'video' ) ),
            array( array( 'service' ), array( 'services', 'home' ), array( 'listing', 'conversion' ), array( 'service' ), array( 'Service' ), array() ),
            array( array( 'feature' ), array( 'home', 'services' ), array( 'content', 'conversion' ), array( 'service' ), array( 'Service' ), array() ),
            array( array( 'client', 'testimonial', 'compliance', 'certificate', 'stats', 'team', 'branch' ), array( 'home', 'about' ), array( 'trust', 'proof', 'social' ), array( 'testimonial', 'team', 'branch', 'partner' ), array( 'Organization', 'Review', 'AggregateRating', 'LocalBusiness' ), array() ),
            array( array( 'faq', 'accordion' ), array( 'faq', 'services' ), array( 'support', 'trust' ), array( 'faq' ), array( 'FAQPage' ), array() ),
            array( array( 'contact', 'booking' ), array( 'contact', 'booking' ), array( 'lead_capture', 'conversion' ), array( 'branch' ), array( 'LocalBusiness', 'Organization' ), array() ),
            array( array( 'blog', 'news', 'post', 'ticker', 'journal', 'knowledge', 'media', 'resource' ), array( 'blog', 'news', 'resource' ), array( 'content', 'listing', 'media' ), array( 'post', 'resource', 'media_item', 'author' ), array( 'Article', 'BlogPosting', 'CollectionPage', 'ItemList' ), array() ),
            array( array( 'product', 'pricing', 'shop', 'ecommerce' ), array( 'products', 'pricing', 'shop', 'landing' ), array( 'commerce', 'pricing', 'conversion' ), array( 'product' ), array( 'Product', 'ItemList' ), array() ),
            array( array( 'case', 'work' ), array( 'cases', 'detail' ), array( 'proof', 'storytelling' ), array( 'case' ), array( 'CreativeWork', 'Article' ), array() ),
            array( array( 'menu' ), array( 'products', 'shop' ), array( 'commerce', 'listing' ), array( 'menu_item' ), array( 'Restaurant', 'ItemList' ), array() ),
            array( array( 'course', 'curriculum' ), array( 'course', 'landing' ), array( 'education', 'conversion' ), array( 'course' ), array( 'Course' ), array() ),
            array( array( 'room', 'hotel', 'amenities' ), array( 'booking', 'products' ), array( 'commerce', 'lead_capture' ), array( 'room' ), array( 'Hotel' ), array() ),
            array( array( 'tour', 'itinerary', 'ticket' ), array( 'event', 'products' ), array( 'commerce', 'storytelling' ), array( 'event' ), array( 'Event' ), array() ),
            array( array( 'search', 'tabs', 'category' ), array( 'search', 'resource' ), array( 'search', 'navigation', 'listing' ), array( 'resource' ), array( 'CollectionPage', 'ItemList' ), array() ),
            array( array( 'video' ), array( 'landing', 'resource' ), array( 'media', 'storytelling' ), array( 'media_item' ), array( 'VideoObject' ), array( 'video' ) ),
            array( array( 'chart' ), array( 'home', 'about' ), array( 'proof', 'content' ), array( 'page' ), array(), array( 'chart' ) ),
        );

        foreach ( $rules as $rule ) {
            if ( self::contains_any( $haystack, $rule[0] ) ) {
                $page_tags      = array_merge( $page_tags, $rule[1] );
                $intent_tags    = array_merge( $intent_tags, $rule[2] );
                $content_models = array_merge( $content_models, $rule[3] );
                $schema_types   = array_merge( $schema_types, $rule[4] );
                $asset_hints    = array_merge( $asset_hints, $rule[5] );
            }
        }

        if ( self::contains_any( $haystack, array( 'restaurant', 'menu' ) ) ) {
            $industry_tags[] = 'restaurant';
        }
        if ( self::contains_any( $haystack, array( 'hotel', 'room', 'homestay' ) ) ) {
            $industry_tags[] = 'hospitality';
        }
        if ( self::contains_any( $haystack, array( 'travel', 'tour', 'itinerary', 'ticket' ) ) ) {
            $industry_tags[] = 'travel';
        }
        if ( self::contains_any( $haystack, array( 'pet' ) ) ) {
            $industry_tags[] = 'pet';
        }
        if ( self::contains_any( $haystack, array( 'fitness', 'gym', 'yoga', 'wellness' ) ) ) {
            $industry_tags = array_merge( $industry_tags, array( 'fitness', 'wellness' ) );
        }
        if ( self::contains_any( $haystack, array( 'beauty', 'salon' ) ) ) {
            $industry_tags[] = 'beauty';
        }

        return array(
            'catalogSchemaVersion' => self::CATALOG_SCHEMA_VERSION,
            'version'              => '1.0.0',
            'status'               => 'stable',
            'catalogRole'          => $catalog_role,
            'metadataSource'       => 'inferred',
            'industryTags'         => self::normalize_industry_key_list( $industry_tags ),
            'pageTags'             => self::normalize_key_list( $page_tags ),
            'intentTags'           => self::normalize_key_list( $intent_tags ),
            'contentModels'        => self::normalize_key_list( $content_models ),
            'schemaTypes'          => self::normalize_schema_type_list( $schema_types ),
            'assetHints'           => self::normalize_key_list( $asset_hints ),
            'aiHints'              => array(
                'summary'            => $desc !== '' ? $desc : $name,
                'bestFor'            => self::normalize_text_list( array( $name ) ),
                'avoidFor'           => array(),
                'contentGuidance'    => '',
                'styleGuidance'      => '',
                'conversionGuidance' => '',
            ),
        );
    }

    /**
     * Merge inferred metadata with explicit manifest metadata.
     *
     * @param array<string,mixed> $inferred Inferred metadata.
     * @param array<string,mixed> $explicit Explicit metadata.
     * @return array<string,mixed>
     */
    public static function merge_module_metadata( $inferred, $explicit ) {
        $inferred = is_array( $inferred ) ? $inferred : array();
        $explicit = is_array( $explicit ) ? $explicit : array();
        $merged   = $inferred;

        foreach ( array( 'industryTags', 'pageTags', 'intentTags', 'contentModels', 'schemaTypes', 'assetHints' ) as $key ) {
            if ( 'industryTags' === $key ) {
                $merged[ $key ] = self::normalize_industry_key_list(
                    array_merge(
                        isset( $inferred[ $key ] ) && is_array( $inferred[ $key ] ) ? $inferred[ $key ] : array(),
                        isset( $explicit[ $key ] ) && is_array( $explicit[ $key ] ) ? $explicit[ $key ] : array()
                    )
                );
                continue;
            }

            $merged[ $key ] = array_values(
                array_unique(
                    array_merge(
                        isset( $inferred[ $key ] ) && is_array( $inferred[ $key ] ) ? $inferred[ $key ] : array(),
                        isset( $explicit[ $key ] ) && is_array( $explicit[ $key ] ) ? $explicit[ $key ] : array()
                    )
                )
            );
        }

        foreach ( array( 'catalogSchemaVersion', 'version', 'status', 'metadataSource' ) as $key ) {
            if ( isset( $explicit[ $key ] ) && is_scalar( $explicit[ $key ] ) && '' !== (string) $explicit[ $key ] ) {
                $merged[ $key ] = (string) $explicit[ $key ];
            }
        }

        if ( isset( $explicit['catalogRole'] ) && is_scalar( $explicit['catalogRole'] ) && '' !== (string) $explicit['catalogRole'] ) {
            $merged['catalogRole'] = self::normalize_catalog_role( $explicit['catalogRole'], isset( $inferred['catalogRole'] ) ? (string) $inferred['catalogRole'] : 'extension' );
        }

        $merged['aiHints'] = self::merge_ai_hints(
            isset( $inferred['aiHints'] ) && is_array( $inferred['aiHints'] ) ? $inferred['aiHints'] : array(),
            isset( $explicit['aiHints'] ) && is_array( $explicit['aiHints'] ) ? $explicit['aiHints'] : array()
        );

        $merged['metadataCompleteness'] = self::calculate_metadata_completeness( $merged );

        return $merged;
    }

    /**
     * Build a catalog audit report for module governance.
     *
     * @param array<int,array<string,mixed>> $catalog Module catalog.
     * @return array<string,mixed>
     */
    public static function build_catalog_audit( $catalog ) {
        $catalog = is_array( $catalog ) ? $catalog : array();
        $audit = array(
            'catalogSchemaVersion' => self::CATALOG_SCHEMA_VERSION,
            'total'                => 0,
            'groups'               => array(),
            'roles'                => array(),
            'statuses'             => array(),
            'coverage'             => array(
                'explicitMetadata' => 0,
                'schemaTypes'      => 0,
                'aiGuidance'       => 0,
                'completeMetadata' => 0,
            ),
            'needsReview'          => array(),
            'crowdedSignatures'    => array(),
        );
        $signature_map = array();

        foreach ( $catalog as $item ) {
            if ( ! is_array( $item ) || empty( $item['id'] ) ) {
                continue;
            }

            $audit['total']++;
            $module_id = sanitize_key( (string) $item['id'] );
            $group     = isset( $item['group'] ) ? sanitize_key( (string) $item['group'] ) : 'general';
            $role      = isset( $item['catalogRole'] ) ? sanitize_key( (string) $item['catalogRole'] ) : self::infer_catalog_role( $group );
            $status    = isset( $item['status'] ) ? sanitize_key( (string) $item['status'] ) : 'stable';

            self::increment_audit_count( $audit['groups'], $group );
            self::increment_audit_count( $audit['roles'], $role );
            self::increment_audit_count( $audit['statuses'], $status );

            $issues = array();
            if ( ! isset( $item['metadataSource'] ) || 'explicit' !== (string) $item['metadataSource'] ) {
                $issues[] = 'metadata_inferred';
            } else {
                $audit['coverage']['explicitMetadata']++;
            }

            if ( ! isset( $item['schemaTypes'] ) || ! is_array( $item['schemaTypes'] ) || empty( $item['schemaTypes'] ) ) {
                $issues[] = 'schema_types_missing';
            } else {
                $audit['coverage']['schemaTypes']++;
            }

            if ( self::has_actionable_ai_guidance( isset( $item['aiHints'] ) ? $item['aiHints'] : array() ) ) {
                $audit['coverage']['aiGuidance']++;
            } else {
                $issues[] = 'ai_guidance_light';
            }

            $completeness = isset( $item['metadataCompleteness'] ) ? absint( $item['metadataCompleteness'] ) : self::calculate_metadata_completeness( $item );
            if ( $completeness >= 85 ) {
                $audit['coverage']['completeMetadata']++;
            } else {
                $issues[] = 'metadata_completeness_below_85';
            }

            if ( in_array( $status, array( 'beta', 'experimental', 'deprecated' ), true ) ) {
                $issues[] = 'non_stable_status';
            }

            if ( ! empty( $issues ) ) {
                $audit['needsReview'][] = array(
                    'id'           => $module_id,
                    'name'         => isset( $item['name'] ) ? (string) $item['name'] : $module_id,
                    'group'        => $group,
                    'catalogRole'  => $role,
                    'status'       => $status,
                    'completeness' => $completeness,
                    'issues'       => array_values( array_unique( $issues ) ),
                );
            }

            $signature = self::build_catalog_signature( $item );
            if ( '' !== $signature ) {
                if ( ! isset( $signature_map[ $signature ] ) ) {
                    $signature_map[ $signature ] = array();
                }
                $signature_map[ $signature ][] = $module_id;
            }
        }

        foreach ( $signature_map as $signature => $module_ids ) {
            if ( count( $module_ids ) < 4 ) {
                continue;
            }

            $audit['crowdedSignatures'][] = array(
                'signature' => $signature,
                'count'     => count( $module_ids ),
                'modules'   => array_slice( array_values( array_unique( $module_ids ) ), 0, 12 ),
            );
        }

        $audit['needsReview'] = array_slice( $audit['needsReview'], 0, 50 );

        return $audit;
    }

    /**
     * Normalize a list of key-like tags.
     *
     * @param mixed $value Raw value.
     * @return array<int,string>
     */
    public static function normalize_key_list( $value ) {
        $items = is_array( $value ) ? $value : ( is_scalar( $value ) && '' !== (string) $value ? explode( ',', (string) $value ) : array() );
        $out   = array();

        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }

            $key = sanitize_key( (string) $item );
            if ( '' !== $key ) {
                $out[] = $key;
            }
        }

        return array_values( array_unique( $out ) );
    }

    /**
     * Normalize schema.org type names.
     *
     * @param mixed $value Raw value.
     * @return array<int,string>
     */
    public static function normalize_schema_type_list( $value ) {
        $items = is_array( $value ) ? $value : ( is_scalar( $value ) && '' !== (string) $value ? explode( ',', (string) $value ) : array() );
        $known = self::get_metadata_taxonomy();
        $known = isset( $known['schemaTypes'] ) && is_array( $known['schemaTypes'] ) ? $known['schemaTypes'] : array();
        $map   = array();
        $out   = array();

        foreach ( array_keys( $known ) as $schema_type ) {
            $map[ strtolower( (string) $schema_type ) ] = (string) $schema_type;
        }

        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }

            $raw = trim( wp_strip_all_tags( (string) $item ) );
            if ( '' === $raw ) {
                continue;
            }

            $lookup = strtolower( $raw );
            if ( isset( $map[ $lookup ] ) ) {
                $out[] = $map[ $lookup ];
                continue;
            }

            $custom = preg_replace( '/[^A-Za-z0-9_]/', '', $raw );
            if ( is_string( $custom ) && '' !== $custom ) {
                $out[] = $custom;
            }
        }

        return array_values( array_unique( $out ) );
    }

    /**
     * Normalize a text list for generation hints.
     *
     * @param mixed $value Raw value.
     * @return array<int,string>
     */
    public static function normalize_text_list( $value ) {
        $items = is_array( $value ) ? $value : ( is_scalar( $value ) && '' !== (string) $value ? array( $value ) : array() );
        $out   = array();

        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }

            $text = trim( wp_strip_all_tags( (string) $item ) );
            if ( '' !== $text ) {
                $out[] = $text;
            }
        }

        return array_values( array_unique( $out ) );
    }

    /**
     * Normalize module catalog role.
     *
     * @param mixed  $value Raw value.
     * @param string $fallback Fallback role.
     * @return string
     */
    private static function normalize_catalog_role( $value, $fallback = 'extension' ) {
        $role = is_scalar( $value ) ? sanitize_key( (string) $value ) : '';
        $allowed = array( 'core', 'featured', 'vertical', 'industry', 'component', 'extension', 'legacy' );
        if ( in_array( $role, $allowed, true ) ) {
            return $role;
        }

        if ( '' === $fallback ) {
            return '';
        }

        return in_array( $fallback, $allowed, true ) ? $fallback : 'extension';
    }

    /**
     * Infer catalog role from group.
     *
     * @param string $group Module group.
     * @return string
     */
    private static function infer_catalog_role( $group ) {
        $group = sanitize_key( (string) $group );
        if ( 'core' === $group ) {
            return 'core';
        }
        if ( 'qiling' === $group ) {
            return 'featured';
        }
        if ( in_array( $group, array( 'software', 'resume' ), true ) ) {
            return 'vertical';
        }
        if ( 'industry' === $group ) {
            return 'industry';
        }
        if ( in_array( $group, array( 'component', 'media', 'header' ), true ) ) {
            return 'component';
        }

        return 'extension';
    }

    /**
     * Whether a manifest entry declares catalog metadata explicitly.
     *
     * @param array<string,mixed> $entry Entry.
     * @return bool
     */
    private static function has_explicit_catalog_metadata( $entry ) {
        if ( ! is_array( $entry ) ) {
            return false;
        }

        foreach ( array( 'industry_tags', 'industryTags', 'page_tags', 'pageTags', 'intent_tags', 'intentTags', 'content_models', 'contentModels', 'schema_types', 'schemaTypes', 'ai_hints', 'aiHints', 'catalog_role', 'catalogRole', 'status' ) as $key ) {
            if ( array_key_exists( $key, $entry ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate metadata completeness from required catalog fields.
     *
     * @param array<string,mixed> $metadata Module metadata.
     * @return int
     */
    private static function calculate_metadata_completeness( $metadata ) {
        $score = 0;
        $weights = array(
            'industryTags'   => 15,
            'pageTags'       => 15,
            'intentTags'     => 20,
            'contentModels'  => 15,
            'schemaTypes'    => 15,
            'aiGuidance'     => 15,
            'catalogRole'    => 5,
        );

        foreach ( array( 'industryTags', 'pageTags', 'intentTags', 'contentModels', 'schemaTypes' ) as $key ) {
            if ( isset( $metadata[ $key ] ) && is_array( $metadata[ $key ] ) && ! empty( $metadata[ $key ] ) ) {
                $score += $weights[ $key ];
            }
        }

        if ( self::has_actionable_ai_guidance( isset( $metadata['aiHints'] ) ? $metadata['aiHints'] : array() ) ) {
            $score += $weights['aiGuidance'];
        }

        if ( isset( $metadata['catalogRole'] ) && is_scalar( $metadata['catalogRole'] ) && '' !== (string) $metadata['catalogRole'] ) {
            $score += $weights['catalogRole'];
        }

        return min( 100, max( 0, (int) $score ) );
    }

    /**
     * Check whether generation hints include usable author guidance.
     *
     * @param mixed $ai_hints Generation hints.
     * @return bool
     */
    private static function has_actionable_ai_guidance( $ai_hints ) {
        if ( ! is_array( $ai_hints ) ) {
            return false;
        }

        foreach ( array( 'contentGuidance', 'styleGuidance', 'conversionGuidance' ) as $key ) {
            if ( isset( $ai_hints[ $key ] ) && is_scalar( $ai_hints[ $key ] ) && '' !== trim( (string) $ai_hints[ $key ] ) ) {
                return true;
            }
        }

        return isset( $ai_hints['bestFor'] ) && is_array( $ai_hints['bestFor'] ) && count( $ai_hints['bestFor'] ) > 1;
    }

    /**
     * Increment an audit counter.
     *
     * @param array<string,int> $bucket Counter bucket.
     * @param string            $key Counter key.
     * @return void
     */
    private static function increment_audit_count( &$bucket, $key ) {
        $key = sanitize_key( (string) $key );
        if ( '' === $key ) {
            $key = 'unknown';
        }

        if ( ! isset( $bucket[ $key ] ) ) {
            $bucket[ $key ] = 0;
        }

        $bucket[ $key ]++;
    }

    /**
     * Build a coarse module signature for crowded capability detection.
     *
     * @param array<string,mixed> $item Catalog item.
     * @return string
     */
    private static function build_catalog_signature( $item ) {
        $page_tags = isset( $item['pageTags'] ) && is_array( $item['pageTags'] ) ? array_values( $item['pageTags'] ) : array();
        $intent_tags = isset( $item['intentTags'] ) && is_array( $item['intentTags'] ) ? array_values( $item['intentTags'] ) : array();
        $content_models = isset( $item['contentModels'] ) && is_array( $item['contentModels'] ) ? array_values( $item['contentModels'] ) : array();

        $page = isset( $page_tags[0] ) ? sanitize_key( (string) $page_tags[0] ) : '';
        $intent = isset( $intent_tags[0] ) ? sanitize_key( (string) $intent_tags[0] ) : '';
        $model = isset( $content_models[0] ) ? sanitize_key( (string) $content_models[0] ) : '';

        if ( '' === $page || '' === $intent || '' === $model ) {
            return '';
        }

        return $page . '|' . $intent . '|' . $model;
    }

    /**
     * Read the first available entry value.
     *
     * @param array<string,mixed> $entry Entry.
     * @param array<int,string>   $keys  Candidate keys.
     * @return mixed|null
     */
    private static function read_entry_value( $entry, $keys ) {
        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $entry ) ) {
                return $entry[ $key ];
            }
        }

        return null;
    }

    /**
     * Normalize module status.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    private static function normalize_status( $value ) {
        $status = is_scalar( $value ) ? sanitize_key( (string) $value ) : '';
        return in_array( $status, array( 'stable', 'beta', 'experimental', 'deprecated' ), true ) ? $status : 'stable';
    }

    /**
     * Normalize version string.
     *
     * @param mixed  $value Raw value.
     * @param string $fallback Fallback version.
     * @return string
     */
    private static function normalize_version( $value, $fallback ) {
        $version = is_scalar( $value ) ? trim( wp_strip_all_tags( (string) $value ) ) : '';
        if ( '' === $version || ! preg_match( '/^[0-9A-Za-z_.\-]+$/', $version ) ) {
            return $fallback;
        }

        return $version;
    }

    /**
     * Normalize generation hints.
     *
     * @param mixed $value Raw value.
     * @return array<string,mixed>
     */
    private static function normalize_ai_hints( $value ) {
        $hints = array(
            'summary'            => '',
            'bestFor'            => array(),
            'avoidFor'           => array(),
            'contentGuidance'    => '',
            'styleGuidance'      => '',
            'conversionGuidance' => '',
        );

        if ( is_scalar( $value ) ) {
            $hints['summary'] = trim( wp_strip_all_tags( (string) $value ) );
            return $hints;
        }

        if ( ! is_array( $value ) ) {
            return $hints;
        }

        $scalar_keys = array(
            'summary'             => 'summary',
            'content_guidance'    => 'contentGuidance',
            'contentGuidance'     => 'contentGuidance',
            'style_guidance'      => 'styleGuidance',
            'styleGuidance'       => 'styleGuidance',
            'conversion_guidance' => 'conversionGuidance',
            'conversionGuidance'  => 'conversionGuidance',
        );

        foreach ( $scalar_keys as $source_key => $target_key ) {
            if ( isset( $value[ $source_key ] ) && is_scalar( $value[ $source_key ] ) ) {
                $hints[ $target_key ] = trim( wp_strip_all_tags( (string) $value[ $source_key ] ) );
            }
        }

        $best_for = self::read_entry_value( $value, array( 'best_for', 'bestFor' ) );
        $avoid_for = self::read_entry_value( $value, array( 'avoid_for', 'avoidFor' ) );
        $hints['bestFor']  = self::normalize_text_list( $best_for );
        $hints['avoidFor'] = self::normalize_text_list( $avoid_for );

        return $hints;
    }

    /**
     * Merge generation hints.
     *
     * @param array<string,mixed> $base Base hints.
     * @param array<string,mixed> $override Override hints.
     * @return array<string,mixed>
     */
    private static function merge_ai_hints( $base, $override ) {
        $merged = self::normalize_ai_hints( $base );
        $override = self::normalize_ai_hints( $override );

        foreach ( array( 'summary', 'contentGuidance', 'styleGuidance', 'conversionGuidance' ) as $key ) {
            if ( isset( $override[ $key ] ) && is_scalar( $override[ $key ] ) && '' !== (string) $override[ $key ] ) {
                $merged[ $key ] = (string) $override[ $key ];
            }
        }

        foreach ( array( 'bestFor', 'avoidFor' ) as $key ) {
            $merged[ $key ] = array_values(
                array_unique(
                    array_merge(
                        isset( $base[ $key ] ) && is_array( $base[ $key ] ) ? self::normalize_text_list( $base[ $key ] ) : array(),
                        isset( $override[ $key ] ) && is_array( $override[ $key ] ) ? self::normalize_text_list( $override[ $key ] ) : array()
                    )
                )
            );
        }

        return $merged;
    }

    /**
     * Check if text contains any needle.
     *
     * @param string           $haystack Text.
     * @param array<int,mixed> $needles Needles.
     * @return bool
     */
    private static function contains_any( $haystack, $needles ) {
        foreach ( $needles as $needle ) {
            if ( is_scalar( $needle ) && '' !== (string) $needle && false !== strpos( $haystack, strtolower( (string) $needle ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Humanize a key for fallback labels.
     *
     * @param mixed $key Raw key.
     * @return string
     */
    private static function humanize_key( $key ) {
        return ucwords( str_replace( array( '-', '_' ), ' ', (string) $key ) );
    }
}
