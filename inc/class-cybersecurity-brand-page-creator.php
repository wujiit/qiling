<?php
/**
 * 网络安全品牌官网创建器类
 *
 * 当用户选择"网络安全品牌官网"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.7
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cybersecurity_Brand_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-cybersecurity-brand.php';
    protected const AJAX_ACTION = 'fill_cybersecurity_brand_modules';
    protected const FILLED_META_KEY = '_cybersecurity_brand_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '网络安全品牌官网', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'interact_hero',
                'data' => array(
                    'badge_text'            => 'CYBER SECURITY',
                    'hero_title_content'    => $page_title . '<br><span style="color:#38bdf8;">主动防护 · 智能响应</span>',
                    'hero_subtitle_content' => __( '覆盖威胁检测、漏洞治理、资产监控与应急响应，为企业构建持续演进的安全防线。', 'developer-starter' ),
                    'btn_primary_content'   => __( '预约安全评估', 'developer-starter' ),
                    'btn_primary_link'      => '#contact-section',
                    'btn_secondary_content' => __( '查看防护能力', 'developer-starter' ),
                    'btn_secondary_link'    => '#security-features',
                    'media_type'            => 'image',
                    'hero_image'            => '',
                    'enable_float_anim'     => 'yes',
                    'feature_items'         => array(
                        array(
                            'f_icon_type'     => 'class',
                            'f_icon_class'    => '🛡️',
                            'f_title_content' => __( '7x24 威胁监测', 'developer-starter' ),
                            'f_desc'          => __( '持续监控异常行为并实时告警。', 'developer-starter' ),
                            'f_link'          => '#',
                        ),
                        array(
                            'f_icon_type'     => 'class',
                            'f_icon_class'    => '🔐',
                            'f_title_content' => __( '零信任访问', 'developer-starter' ),
                            'f_desc'          => __( '按身份与上下文动态控制访问权限。', 'developer-starter' ),
                            'f_link'          => '#',
                        ),
                        array(
                            'f_icon_type'     => 'class',
                            'f_icon_class'    => '🚨',
                            'f_title_content' => __( '秒级处置联动', 'developer-starter' ),
                            'f_desc'          => __( '自动化剧本触发，快速封禁和隔离风险。', 'developer-starter' ),
                            'f_link'          => '#',
                        ),
                        array(
                            'f_icon_type'     => 'class',
                            'f_icon_class'    => '📊',
                            'f_title_content' => __( '可观测安全看板', 'developer-starter' ),
                            'f_desc'          => __( '统一展示攻击态势与处置结果。', 'developer-starter' ),
                            'f_link'          => '#',
                        ),
                    ),
                    'bg_color'              => '#0b1220',
                    'text_color'            => '#e2e8f0',
                    'highlight_color'       => '#38bdf8',
                    'padding_top'           => '98px',
                    'padding_bottom'        => '88px',
                ),
            ),
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'                 => __( '安全能力体系', 'developer-starter' ),
                    'subtitle'              => __( '从预防、检测到响应，形成完整闭环', 'developer-starter' ),
                    'columns'               => '3',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs'                  => array(
                        array(
                            'tab_id'    => 'prevention',
                            'tab_title' => __( '预防', 'developer-starter' ),
                            'tab_icon'  => '🧱',
                            'features'  => __( "🕵️|资产识别|自动发现公网暴露面和关键资产\n🔎|漏洞治理|持续扫描、优先级排序与修复闭环\n📐|基线加固|按行业规范执行安全配置检查", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'detection',
                            'tab_title' => __( '检测', 'developer-starter' ),
                            'tab_icon'  => '👁️',
                            'features'  => __( "📡|日志分析|集中采集终端、网络与应用日志\n🤖|异常识别|行为建模发现异常操作与潜在攻击\n🔔|实时告警|多通道通知，保障关键事件可达", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'response',
                            'tab_title' => __( '响应', 'developer-starter' ),
                            'tab_icon'  => '⚔️',
                            'features'  => __( "🧩|自动化剧本|一键联动封禁、隔离与溯源动作\n🛠️|应急支持|专家团队介入，缩短处置时间\n📘|复盘改进|攻击路径复盘，持续优化防护策略", 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'         => __( '安全服务流程', 'developer-starter' ),
                    'process_subtitle'      => __( '标准化流程保障每一步可执行、可追踪', 'developer-starter' ),
                    'process_mode'          => 'standard',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'process_items'         => array(
                        array(
                            'icon'      => '01',
                            'title'     => __( '风险评估', 'developer-starter' ),
                            'stage_tag' => __( '诊断', 'developer-starter' ),
                            'desc'      => __( '梳理资产与威胁面，识别关键风险点。', 'developer-starter' ),
                        ),
                        array(
                            'icon'      => '02',
                            'title'     => __( '策略部署', 'developer-starter' ),
                            'stage_tag' => __( '防护', 'developer-starter' ),
                            'desc'      => __( '按业务优先级落地防护策略与监测规则。', 'developer-starter' ),
                        ),
                        array(
                            'icon'      => '03',
                            'title'     => __( '持续监测', 'developer-starter' ),
                            'stage_tag' => __( '监控', 'developer-starter' ),
                            'desc'      => __( '7x24监控告警与安全事件跟踪处置。', 'developer-starter' ),
                        ),
                        array(
                            'icon'      => '04',
                            'title'     => __( '应急与复盘', 'developer-starter' ),
                            'stage_tag' => __( '优化', 'developer-starter' ),
                            'desc'      => __( '事件复盘与策略优化，建立长期防御能力。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'comparison',
                'data' => array(
                    'comparison_title'      => __( '方案级别对比', 'developer-starter' ),
                    'comparison_subtitle'   => __( '按团队规模和合规需求选择防护方案', 'developer-starter' ),
                    'comparison_features'   => __( "资产监控\n漏洞扫描\n态势感知\n自动化响应\n专属顾问\nSLA 保障", 'developer-starter' ),
                    'comparison_products'   => array(
                        array(
                            'name'   => __( '标准版', 'developer-starter' ),
                            'values' => __( "基础\n基础\n✗\n✗\n✗\n✗", 'developer-starter' ),
                        ),
                        array(
                            'name'   => __( '专业版', 'developer-starter' ),
                            'values' => __( "增强\n增强\n✓\n基础\n在线支持\n✓", 'developer-starter' ),
                        ),
                        array(
                            'name'   => __( '企业版', 'developer-starter' ),
                            'values' => __( "高级\n高级\n✓\n高级\n专属团队\n✓", 'developer-starter' ),
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
                'type' => 'compliance_trust',
                'data' => array(
                    'ct_title'                => __( '安全合规背书', 'developer-starter' ),
                    'ct_subtitle'             => __( '采用行业标准与持续审计，满足企业级安全治理要求。', 'developer-starter' ),
                    'ct_layout'               => 'grid',
                    'ct_columns'              => '3',
                    'ct_enable_filter'        => 'yes',
                    'module_bg_type'          => 'color',
                    'module_bg_color'         => '#f8fafc',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'ct_items'                => array(
                        array(
                            'icon'        => '🛡️',
                            'title'       => 'SOC 2 Type II',
                            'short_name'  => 'SOC2',
                            'category'    => __( '安全控制', 'developer-starter' ),
                            'status'      => 'active',
                            'issuer'      => __( '第三方审计机构', 'developer-starter' ),
                            'scope'       => __( '安全监测与运维流程', 'developer-starter' ),
                            'valid_until' => __( '年度审计', 'developer-starter' ),
                            'report_url'  => '#',
                            'report_text' => __( '查看审计说明', 'developer-starter' ),
                        ),
                        array(
                            'icon'        => '🔐',
                            'title'       => 'ISO/IEC 27001',
                            'short_name'  => 'ISO27001',
                            'category'    => __( '信息安全', 'developer-starter' ),
                            'status'      => 'active',
                            'issuer'      => __( '国际认证机构', 'developer-starter' ),
                            'scope'       => __( '信息安全管理体系', 'developer-starter' ),
                            'valid_until' => '2028-12-31',
                            'report_url'  => '#',
                            'report_text' => __( '查看认证范围', 'developer-starter' ),
                        ),
                        array(
                            'icon'        => '📜',
                            'title'       => __( '等保三级', 'developer-starter' ),
                            'short_name'  => __( '等保3级', 'developer-starter' ),
                            'category'    => __( '本地监管', 'developer-starter' ),
                            'status'      => 'progress',
                            'issuer'      => __( '测评机构', 'developer-starter' ),
                            'scope'       => __( '核心业务系统安全防护', 'developer-starter' ),
                            'valid_until' => __( '推进中', 'developer-starter' ),
                            'report_url'  => '#',
                            'report_text' => __( '查看进展', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'          => __( '行业防护案例', 'developer-starter' ),
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
                    'testimonials_subtitle'    => __( '来自安全团队和IT管理者的真实反馈', 'developer-starter' ),
                    'testimonials_layout'      => 'grid',
                    'testimonials_columns'     => '3',
                    'show_rating_summary'      => 'yes',
                    'total_reviews'            => '98+',
                    'average_rating'           => '4.9',
                    'testimonials_bg_color'    => '#ffffff',
                    'module_padding_top'       => '80px',
                    'module_padding_bottom'    => '80px',
                    'testimonials_items'       => array(
                        array(
                            'avatar'   => '',
                            'name'     => __( '孙总监', 'developer-starter' ),
                            'position' => __( '制造企业 · 信息安全负责人', 'developer-starter' ),
                            'content'  => __( '威胁响应速度提升明显，季度漏洞闭环率显著改善。', 'developer-starter' ),
                            'rating'   => '5',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '杨经理', 'developer-starter' ),
                            'position' => __( '互联网公司 · IT 运维经理', 'developer-starter' ),
                            'content'  => __( '策略可视化和告警联动做得很好，运维压力下降很多。', 'developer-starter' ),
                            'rating'   => '5',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '郑工程师', 'developer-starter' ),
                            'position' => __( '金融机构 · 安全工程师', 'developer-starter' ),
                            'content'  => __( '兼顾合规要求和业务可用性，落地过程比较顺畅。', 'developer-starter' ),
                            'rating'   => '5',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '申请安全评估', 'developer-starter' ),
                    'contact_subtitle'       => __( '提交业务场景与现状，我们将给出分阶段安全建设建议。', 'developer-starter' ),
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
                    'cta_title'             => __( '立即构建你的下一代安全防线', 'developer-starter' ),
                    'cta_subtitle'          => __( '从评估到上线，提供可执行的安全落地方案。', 'developer-starter' ),
                    'cta_button_text'       => __( '预约专家沟通', 'developer-starter' ),
                    'cta_button_url'        => '#contact-section',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0b1220 0%, #1e3a8a 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
