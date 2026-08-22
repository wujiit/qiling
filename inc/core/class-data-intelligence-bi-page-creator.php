<?php
/**
 * 数据智能/BI平台官网创建器类
 *
 * 当用户选择"数据智能/BI平台官网"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.7
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Data_Intelligence_BI_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-data-intelligence-bi.php';
    protected const AJAX_ACTION = 'fill_data_intelligence_bi_modules';
    protected const FILLED_META_KEY = '_data_intelligence_bi_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '数据智能/BI平台官网', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'dynamic_banner',
                'data' => array(
                    'db_height'          => '82vh',
                    'db_bg_type'         => 'gradient',
                    'db_bg_gradient'     => 'linear-gradient(135deg, #0f172a 0%, #1e40af 50%, #0891b2 100%)',
                    'db_title_prefix'    => $page_title,
                    'db_typing_mode'     => 'loop',
                    'db_typing_text'     => __( "全链路数据接入与治理\n多维分析与可视化看板\n业务指标实时驱动决策", 'developer-starter' ),
                    'db_highlight_color' => '#67e8f9',
                    'db_title_color'     => '#e2e8f0',
                    'db_subtitle'        => __( '让数据从“被记录”变为“被使用”，支撑业务增长与组织协同。', 'developer-starter' ),
                    'db_desc'            => __( '预设包含统计总览、图表分析、能力说明、方案对比、案例与咨询入口，适合 BI 品牌官网快速上线。', 'developer-starter' ),
                    'db_text_color'      => 'rgba(226,232,240,0.88)',
                    'db_buttons'         => array(
                        array(
                            'text'  => __( '申请产品演示', 'developer-starter' ),
                            'link'  => '#contact-section',
                            'style' => 'primary',
                            'icon'  => '📊',
                        ),
                        array(
                            'text'  => __( '查看能力矩阵', 'developer-starter' ),
                            'link'  => '#bi-features',
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
                            'title'        => __( '10+ 数据源直连', 'developer-starter' ),
                            'pos_top'      => '8%',
                            'pos_right'    => '-2%',
                            'animation'    => 'float',
                            'delay'        => '0s',
                        ),
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '分钟级刷新', 'developer-starter' ),
                            'pos_bottom'   => '10%',
                            'pos_left'     => '-4%',
                            'animation'    => 'pulse',
                            'delay'        => '0.4s',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_title'           => __( '关键业务指标总览', 'developer-starter' ),
                    'stats_subtitle'        => __( '面向管理层与业务团队的一体化指标看板', 'developer-starter' ),
                    'stats_bg_color'        => '#ffffff',
                    'stats_text_align'      => 'center',
                    'stats_items'           => array(
                        array( 'number' => '38%', 'label' => __( '分析效率提升', 'developer-starter' ) ),
                        array( 'number' => '12+', 'label' => __( '已接入业务系统', 'developer-starter' ) ),
                        array( 'number' => '99.9%', 'label' => __( '数据可用性', 'developer-starter' ) ),
                        array( 'number' => '5分钟', 'label' => __( '指标刷新周期', 'developer-starter' ) ),
                    ),
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                ),
            ),
            array(
                'type' => 'chart',
                'data' => array(
                    'chart_type'   => 'line',
                    'chart_title'  => __( '近6个月核心指标趋势', 'developer-starter' ),
                    'chart_height' => '420',
                    'chart_data'   => array(
                        array( 'label' => '10月', 'value' => '120', 'color' => '#2563eb' ),
                        array( 'label' => '11月', 'value' => '168', 'color' => '#2563eb' ),
                        array( 'label' => '12月', 'value' => '220', 'color' => '#2563eb' ),
                        array( 'label' => '1月', 'value' => '260', 'color' => '#2563eb' ),
                        array( 'label' => '2月', 'value' => '315', 'color' => '#2563eb' ),
                        array( 'label' => '3月', 'value' => '380', 'color' => '#2563eb' ),
                    ),
                ),
            ),
            array(
                'type' => 'chart',
                'data' => array(
                    'chart_type'   => 'bar',
                    'chart_title'  => __( '部门数据使用覆盖率', 'developer-starter' ),
                    'chart_height' => '420',
                    'chart_data'   => array(
                        array( 'label' => __( '销售', 'developer-starter' ), 'value' => '88', 'color' => '#0ea5e9' ),
                        array( 'label' => __( '市场', 'developer-starter' ), 'value' => '81', 'color' => '#22c55e' ),
                        array( 'label' => __( '运营', 'developer-starter' ), 'value' => '92', 'color' => '#6366f1' ),
                        array( 'label' => __( '客服', 'developer-starter' ), 'value' => '74', 'color' => '#f59e0b' ),
                        array( 'label' => __( '财务', 'developer-starter' ), 'value' => '69', 'color' => '#ef4444' ),
                    ),
                ),
            ),
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'                 => __( '数据平台核心能力', 'developer-starter' ),
                    'subtitle'              => __( '覆盖数据采集、治理、建模、分析与共享全流程', 'developer-starter' ),
                    'columns'               => '3',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs'                  => array(
                        array(
                            'tab_id'    => 'ingestion',
                            'tab_title' => __( '采集集成', 'developer-starter' ),
                            'tab_icon'  => '🔌',
                            'features'  => __( "🔗|多源接入|数据库、SaaS、文件与API统一接入\n🧭|任务编排|可视化调度任务，支持依赖关系管理\n⏱️|实时同步|增量同步与实时流处理并行支持", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'governance',
                            'tab_title' => __( '治理建模', 'developer-starter' ),
                            'tab_icon'  => '🧱',
                            'features'  => __( "🧹|数据清洗|异常值处理、规则校验与质量监控\n📚|指标体系|统一口径管理，避免跨团队认知偏差\n🛡️|权限控制|按组织角色精细化授权与审计追踪", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'analysis',
                            'tab_title' => __( '分析应用', 'developer-starter' ),
                            'tab_icon'  => '📈',
                            'features'  => __( "📊|自助看板|拖拽式搭建分析报表，快速交付业务洞察\n🤖|智能预警|阈值与异常模式触发自动告警\n🤝|协同决策|跨团队共享指标与洞察结论", 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'comparison',
                'data' => array(
                    'comparison_title'      => __( '版本能力对比', 'developer-starter' ),
                    'comparison_subtitle'   => __( '按团队规模和分析深度选择版本', 'developer-starter' ),
                    'comparison_features'   => __( "数据源接入数\n自定义看板\n高级建模\n权限审计\nAPI开放\n专属成功经理", 'developer-starter' ),
                    'comparison_products'   => array(
                        array(
                            'name'   => __( '标准版', 'developer-starter' ),
                            'values' => __( "5\n基础\n✗\n基础\n✗\n✗", 'developer-starter' ),
                        ),
                        array(
                            'name'   => __( '专业版', 'developer-starter' ),
                            'values' => __( "20\n高级\n✓\n✓\n✓\n在线支持", 'developer-starter' ),
                        ),
                        array(
                            'name'   => __( '企业版', 'developer-starter' ),
                            'values' => __( "不限\n高级\n✓\n✓\n✓\n✓", 'developer-starter' ),
                        ),
                    ),
                    'comparison_highlight'  => '2',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                ),
            ),
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'          => __( '行业落地案例', 'developer-starter' ),
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
                    'testimonials_title'       => __( '客户评价', 'developer-starter' ),
                    'testimonials_subtitle'    => __( '来自管理层和数据团队的反馈', 'developer-starter' ),
                    'testimonials_layout'      => 'grid',
                    'testimonials_columns'     => '3',
                    'show_rating_summary'      => 'yes',
                    'total_reviews'            => '86+',
                    'average_rating'           => '4.9',
                    'testimonials_bg_color'    => '#f8fafc',
                    'module_padding_top'       => '80px',
                    'module_padding_bottom'    => '80px',
                    'testimonials_items'       => array(
                        array(
                            'avatar'   => '',
                            'name'     => __( '何总监', 'developer-starter' ),
                            'position' => __( '消费品牌 · 运营负责人', 'developer-starter' ),
                            'content'  => __( '跨部门指标终于统一了，周会决策效率提升很明显。', 'developer-starter' ),
                            'rating'   => '5',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '刘经理', 'developer-starter' ),
                            'position' => __( 'SaaS企业 · 数据产品经理', 'developer-starter' ),
                            'content'  => __( '看板配置和权限管理都很成熟，能快速复制到新团队。', 'developer-starter' ),
                            'rating'   => '5',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '周负责人', 'developer-starter' ),
                            'position' => __( '零售集团 · BI负责人', 'developer-starter' ),
                            'content'  => __( '从数据接入到建模分析形成了稳定流程，维护成本更低。', 'developer-starter' ),
                            'rating'   => '5',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '常见问题', 'developer-starter' ),
                    'faq_subtitle'          => __( '关于接入、治理与上线周期的高频问题', 'developer-starter' ),
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array(
                            'question' => __( '可以接入哪些数据源？', 'developer-starter' ),
                            'answer'   => __( '支持主流数据库、SaaS 系统、文件上传和 API 接入。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '数据口径如何统一？', 'developer-starter' ),
                            'answer'   => __( '通过指标体系管理和权限策略，保证跨团队口径一致。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '上线周期大概多久？', 'developer-starter' ),
                            'answer'   => __( '常见场景 2-4 周可完成首版上线，复杂场景按需求评估。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '是否支持私有化部署？', 'developer-starter' ),
                            'answer'   => __( '支持私有化和专有云部署，可按合规要求定制。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '预约 BI 方案咨询', 'developer-starter' ),
                    'contact_subtitle'       => __( '提交业务目标与现状，我们会给出分阶段落地建议。', 'developer-starter' ),
                    'contact_show_form'      => '1',
                    'contact_form_id'        => '',
                    'module_bg_type'         => 'color',
                    'module_bg_color'        => '#f8fafc',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '让每一次决策都有数据支撑', 'developer-starter' ),
                    'cta_subtitle'          => __( '从数据治理到分析应用，构建可持续增长的数据能力。', 'developer-starter' ),
                    'cta_button_text'       => __( '申请产品演示', 'developer-starter' ),
                    'cta_button_url'        => '#contact-section',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0f172a 0%, #1e40af 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
