<?php
/**
 * 产品品牌官网创建器类
 *
 * 当用户选择对应品牌官网模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.7
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AI_Product_Brand_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-ai-product-brand.php';
    protected const AJAX_ACTION = 'fill_ai_product_brand_modules';
    protected const FILLED_META_KEY = '_ai_product_brand_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( 'AI产品品牌官网', 'qiling' );
        }

        $default_modules = array(
            array(
                'type' => 'dynamic_banner',
                'data' => array(
                    'db_height'          => '82vh',
                    'db_bg_type'         => 'gradient',
                    'db_bg_gradient'     => 'linear-gradient(135deg, #0b1020 0%, #1d4ed8 45%, #0ea5e9 100%)',
                    'db_title_prefix'    => $page_title,
                    'db_typing_mode'     => 'loop',
                    'db_typing_text'     => __( "多场景 AI Agent 工作流\n企业级知识问答与自动化协作\n从 PoC 到生产快速落地", 'qiling' ),
                    'db_highlight_color' => '#67e8f9',
                    'db_title_color'     => '#e2e8f0',
                    'db_subtitle'        => __( '围绕增长、效率与稳定性，构建可持续迭代的 AI 产品体系。', 'qiling' ),
                    'db_desc'            => __( '该模板已内置品牌官网常用区块：能力介绍、实施流程、案例、客户评价、合规信任与转化入口。', 'qiling' ),
                    'db_text_color'      => 'rgba(226,232,240,0.88)',
                    'db_buttons'         => array(
                        array(
                            'text'  => __( '申请产品演示', 'qiling' ),
                            'link'  => '#contact-section',
                            'style' => 'primary',
                            'icon'  => 'icon-rocket',
                        ),
                        array(
                            'text'  => __( '查看能力清单', 'qiling' ),
                            'link'  => '#features-section',
                            'style' => 'outline',
                            'icon'  => '🧠',
                        ),
                    ),
                    'db_media_type'      => 'image',
                    'db_main_image'      => '',
                    'db_image_shadow'    => 'soft',
                    'db_floating_cards'  => array(
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '支持私有化部署', 'qiling' ),
                            'pos_top'      => '8%',
                            'pos_right'    => '-2%',
                            'animation'    => 'float',
                            'delay'        => '0s',
                        ),
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '分钟级迭代发布', 'qiling' ),
                            'pos_bottom'   => '10%',
                            'pos_left'     => '-4%',
                            'animation'    => 'pulse',
                            'delay'        => '0.4s',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'                 => __( 'AI 核心能力矩阵', 'qiling' ),
                    'subtitle'              => __( '覆盖模型接入、数据治理、应用编排与运维保障', 'qiling' ),
                    'columns'               => '3',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs'                  => array(
                        array(
                            'tab_id'    => 'ai_engine',
                            'tab_title' => __( 'AI 引擎', 'qiling' ),
                            'tab_icon'  => '🧠',
                            'features'  => __( "icon-android|多模型接入|统一接入主流大模型，支持策略路由与回退\n🧩|Prompt工程|提示词版本化管理，支持灰度与A/B测试\nicon-settings|工具调用|函数调用与外部系统连接，打通业务流程", 'qiling' ),
                        ),
                        array(
                            'tab_id'    => 'data_layer',
                            'tab_title' => __( '数据层', 'qiling' ),
                            'tab_icon'  => '📚',
                            'features'  => __( "icon-book|知识库检索|文档切片、向量检索与语义召回一体化\nicon-lock|权限隔离|按组织、部门、角色进行数据访问控制\n🧹|数据治理|内容清洗、标注与更新策略可配置", 'qiling' ),
                        ),
                        array(
                            'tab_id'    => 'ops',
                            'tab_title' => __( '可观测运维', 'qiling' ),
                            'tab_icon'  => '📈',
                            'features'  => __( "icon-chart|调用监控|时延、成本、成功率实时可视化\nicon-shield|安全防护|敏感词过滤、越权检测与审计追踪\nicon-rocket|弹性伸缩|高峰流量自动扩容，保障稳定交付", 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'service_cards',
                'data' => array(
                    'sc_bg_color' => '#f8fafc',
                    'sc_padding'  => '72',
                    'sc_columns'  => '4',
                    'sc_gap'      => '20px',
                    'sc_card_bg'  => '#ffffff',
                    'sc_icon_bg'  => 'linear-gradient(135deg, #2563eb 0%, #06b6d4 100%)',
                    'sc_cards'    => array(
                        array(
                            'icon'        => '🧪',
                            'title'       => __( 'PoC快速验证', 'qiling' ),
                            'badge'       => __( '2-4周', 'qiling' ),
                            'description' => __( '聚焦单场景验证价值，快速建立可量化成果。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '🏗️',
                            'title'       => __( '平台化建设', 'qiling' ),
                            'badge'       => __( '标准化', 'qiling' ),
                            'description' => __( '搭建统一AI中台，支持多业务线复用。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '🔄',
                            'title'       => __( '业务流程自动化', 'qiling' ),
                            'badge'       => __( '高效率', 'qiling' ),
                            'description' => __( '将高频任务转为自动执行，提升团队产能。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => 'icon-target',
                            'title'       => __( '持续优化运营', 'qiling' ),
                            'badge'       => __( '闭环', 'qiling' ),
                            'description' => __( '基于数据反馈持续优化模型效果与交互体验。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'         => __( '项目落地流程', 'qiling' ),
                    'process_subtitle'      => __( '从业务目标到线上运营的标准化实施路径', 'qiling' ),
                    'process_mode'          => 'standard',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'process_items'         => array(
                        array(
                            'icon'      => '01',
                            'title'     => __( '业务诊断', 'qiling' ),
                            'stage_tag' => __( '梳理', 'qiling' ),
                            'desc'      => __( '识别高价值场景、核心指标与现有流程瓶颈。', 'qiling' ),
                        ),
                        array(
                            'icon'      => '02',
                            'title'     => __( '方案设计', 'qiling' ),
                            'stage_tag' => __( '设计', 'qiling' ),
                            'desc'      => __( '完成模型策略、数据方案与交互流程设计。', 'qiling' ),
                        ),
                        array(
                            'icon'      => '03',
                            'title'     => __( '开发集成', 'qiling' ),
                            'stage_tag' => __( '实施', 'qiling' ),
                            'desc'      => __( '打通业务系统与AI能力，进行联调与压测。', 'qiling' ),
                        ),
                        array(
                            'icon'      => '04',
                            'title'     => __( '上线运营', 'qiling' ),
                            'stage_tag' => __( '优化', 'qiling' ),
                            'desc'      => __( '按业务反馈持续优化效果、成本与稳定性。', 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'          => __( 'AI 实战案例', 'qiling' ),
                    'cases_count'          => '6',
                    'cases_columns'        => '3',
                    'cases_show_image'     => '1',
                    'cases_image_height'   => '220px',
                    'cases_padding_top'    => '80px',
                    'cases_padding_bottom' => '80px',
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'       => __( '客户反馈', 'qiling' ),
                    'testimonials_subtitle'    => __( '来自产品、运营与技术团队的一线评价', 'qiling' ),
                    'testimonials_layout'      => 'grid',
                    'testimonials_columns'     => '3',
                    'show_rating_summary'      => 'yes',
                    'total_reviews'            => '120+',
                    'average_rating'           => '4.9',
                    'testimonials_bg_color'    => '#f8fafc',
                    'module_padding_top'       => '80px',
                    'module_padding_bottom'    => '80px',
                    'testimonials_items'       => array(
                        array(
                            'avatar'   => '',
                            'name'     => __( '陈总监', 'qiling' ),
                            'position' => __( '某零售品牌 · 数字化负责人', 'qiling' ),
                            'content'  => __( '上线 2 个月后，客服工单分流率和首响效率显著提升。', 'qiling' ),
                            'rating'   => '5',
                            'source'   => '企业客户',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '吴经理', 'qiling' ),
                            'position' => __( '某SaaS公司 · 产品经理', 'qiling' ),
                            'content'  => __( '模板化交付能力很强，内部多团队可以快速复用。', 'qiling' ),
                            'rating'   => '5',
                            'source'   => 'SaaS 团队',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '赵架构师', 'qiling' ),
                            'position' => __( '某金融科技公司 · 技术负责人', 'qiling' ),
                            'content'  => __( '在稳定性和权限隔离方面达到了企业级要求。', 'qiling' ),
                            'rating'   => '5',
                            'source'   => '技术团队',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'compliance_trust',
                'data' => array(
                    'ct_title'                => __( '安全与合规能力', 'qiling' ),
                    'ct_subtitle'             => __( '通过标准化控制体系与持续审计，确保企业数据安全与服务可靠。', 'qiling' ),
                    'ct_layout'               => 'grid',
                    'ct_columns'              => '3',
                    'ct_enable_filter'        => 'yes',
                    'ct_card_style'           => 'solid',
                    'ct_accent_color'         => '#2563eb',
                    'module_bg_type'          => 'color',
                    'module_bg_color'         => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'ct_items'                => array(
                        array(
                            'icon'        => 'icon-shield',
                            'title'       => 'SOC 2 Type II',
                            'short_name'  => 'SOC2',
                            'category'    => __( '安全控制', 'qiling' ),
                            'status'      => 'active',
                            'issuer'      => __( '第三方审计机构', 'qiling' ),
                            'scope'       => __( '平台应用与运维流程', 'qiling' ),
                            'valid_until' => __( '年度滚动审计', 'qiling' ),
                            'description' => __( '覆盖访问控制、变更管理和日志审计等关键控制项。', 'qiling' ),
                            'report_url'  => '#',
                            'report_text' => __( '查看审计说明', 'qiling' ),
                        ),
                        array(
                            'icon'        => '🔐',
                            'title'       => 'ISO/IEC 27001',
                            'short_name'  => 'ISO27001',
                            'category'    => __( '信息安全', 'qiling' ),
                            'status'      => 'active',
                            'issuer'      => __( '国际认证机构', 'qiling' ),
                            'scope'       => __( '信息安全管理体系', 'qiling' ),
                            'valid_until' => '2028-12-31',
                            'description' => __( '基于风险评估构建安全管理体系并持续改进。', 'qiling' ),
                            'report_url'  => '#',
                            'report_text' => __( '查看认证范围', 'qiling' ),
                        ),
                        array(
                            'icon'        => 'icon-globe',
                            'title'       => 'GDPR',
                            'short_name'  => 'GDPR',
                            'category'    => __( '隐私合规', 'qiling' ),
                            'status'      => 'active',
                            'issuer'      => __( '法务与合规团队', 'qiling' ),
                            'scope'       => __( '欧盟用户数据处理流程', 'qiling' ),
                            'valid_until' => __( '持续监测', 'qiling' ),
                            'description' => __( '支持数据主体权利响应与跨境处理合规流程。', 'qiling' ),
                            'report_url'  => '#',
                            'report_text' => __( '查看隐私承诺', 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '常见问题', 'qiling' ),
                    'faq_subtitle'          => __( '关于部署、数据和计费的高频问题说明', 'qiling' ),
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array(
                            'question' => __( '是否支持私有化部署？', 'qiling' ),
                            'answer'   => __( '支持私有化和专有云部署，可根据企业安全策略定制方案。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '能否对接现有业务系统？', 'qiling' ),
                            'answer'   => __( '支持通过 API/Webhook 与 CRM、工单、OA 等系统集成。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '如何保障模型输出稳定性？', 'qiling' ),
                            'answer'   => __( '提供多模型路由、提示词版本管理、监控告警和回退机制。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '是否提供实施与培训服务？', 'qiling' ),
                            'answer'   => __( '提供从方案设计到上线运营的实施陪跑与团队培训。', 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '预约 AI 方案咨询', 'qiling' ),
                    'contact_subtitle'       => __( '提交需求后，我们会在 1 个工作日内联系你。', 'qiling' ),
                    'contact_show_form'      => '1',
                    'contact_form_id'        => '',
                    'module_bg_type'         => 'color',
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '准备把 AI 能力真正跑进业务？', 'qiling' ),
                    'cta_subtitle'          => __( '从场景诊断到上线运营，我们帮助你更快获得可量化增长。', 'qiling' ),
                    'cta_button_text'       => __( '立即预约演示', 'qiling' ),
                    'cta_button_url'        => '#contact-section',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0b1020 0%, #1e3a8a 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
