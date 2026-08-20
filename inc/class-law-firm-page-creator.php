<?php
/**
 * 律所官网页创建器类
 *
 * 当用户选择"律所官网页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Law_Firm_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-law-firm.php';
    protected const AJAX_ACTION = 'fill_law_firm_modules';
    protected const FILLED_META_KEY = '_law_firm_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '律所官网页', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'      => 'slider',
                    'banner_height'      => 'large',
                    'banner_bg_color'    => 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
                    'banner_slides'      => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '民商事争议、企业合规与法律顾问服务，提供可执行的法律方案。', 'developer-starter' ),
                            'btn_text'       => __( '咨询律师', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#0f172a',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '专业分工 + 团队协作', 'developer-starter' ),
                            'subtitle'       => __( '针对不同案件类型，提供分层次、可落地的法律支持。', 'developer-starter' ),
                            'btn_text'       => __( '查看服务领域', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#0f172a',
                        ),
                    ),
                    'show_stats_bar'     => '1',
                    'stats_data'         => array(
                        array( 'icon' => '⚖️', 'number' => '12年+', 'label' => __( '执业经验', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '👨‍⚖️', 'number' => '30+', 'label' => __( '律师团队', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '✅', 'number' => '3000+', 'label' => __( '服务案例', 'developer-starter' ), 'color' => '#ffffff' ),
                        array( 'icon' => '🏢', 'number' => '200+', 'label' => __( '企业客户', 'developer-starter' ), 'color' => '#ffffff' ),
                    ),
                    'banner_wave_enable' => '0',
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '法律服务领域', 'developer-starter' ),
                    'services_subtitle'         => __( '覆盖企业经营全周期与个人常见法律需求', 'developer-starter' ),
                    'services_bg_color'         => '#ffffff',
                    'services_padding'          => 'normal',
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array( 'icon' => '🏛️', 'title' => __( '民商事诉讼', 'developer-starter' ), 'desc' => __( '合同纠纷、股权争议、债权追偿等案件代理。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '📑', 'title' => __( '企业合规', 'developer-starter' ), 'desc' => __( '劳动用工、数据合规、经营风险评估与整改。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🤝', 'title' => __( '常年法律顾问', 'developer-starter' ), 'desc' => __( '合同审查、制度建设与专项法律培训支持。', 'developer-starter' ), 'link' => '#' ),
                        array( 'icon' => '🛡️', 'title' => __( '争议预防', 'developer-starter' ), 'desc' => __( '通过流程优化和条款设计降低潜在法律风险。', 'developer-starter' ), 'link' => '#' ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'team',
                'data' => array(
                    'team_title'                => __( '律师团队', 'developer-starter' ),
                    'team_subtitle'             => __( '按专业方向组建服务小组，提升办案效率与质量', 'developer-starter' ),
                    'team_columns'              => '3',
                    'team_members'              => array(
                        array( 'avatar' => '', 'name' => __( '李律师', 'developer-starter' ), 'position' => __( '高级合伙人', 'developer-starter' ), 'desc' => __( '专注民商事争议解决与企业治理。', 'developer-starter' ), 'wechat' => '', 'email' => 'lawyer1@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '周律师', 'developer-starter' ), 'position' => __( '合规负责人', 'developer-starter' ), 'desc' => __( '长期为科技与零售企业提供合规体系建设。', 'developer-starter' ), 'wechat' => '', 'email' => 'lawyer2@example.com', 'phone' => '' ),
                        array( 'avatar' => '', 'name' => __( '王律师', 'developer-starter' ), 'position' => __( '诉讼律师', 'developer-starter' ), 'desc' => __( '擅长合同纠纷和劳动争议案件代理。', 'developer-starter' ), 'wechat' => '', 'email' => 'lawyer3@example.com', 'phone' => '' ),
                    ),
                    'module_bg_type'            => 'color',
                    'module_bg_color'           => '#f8fafc',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'              => __( '典型案例', 'developer-starter' ),
                    'cases_count'              => '6',
                    'cases_columns'            => '3',
                    'cases_show_image'         => '1',
                    'cases_image_height'       => '220px',
                    'cases_padding_top'        => '80px',
                    'cases_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'              => __( '服务流程', 'developer-starter' ),
                    'process_subtitle'           => __( '标准化办案流程，关键节点可跟踪', 'developer-starter' ),
                    'process_mode'               => 'standard',
                    'process_items'              => array(
                        array( 'icon' => '01', 'title' => __( '案件评估', 'developer-starter' ), 'desc' => __( '初步了解案情，明确诉求和可行路径。', 'developer-starter' ), 'stage_tag' => __( '受理', 'developer-starter' ) ),
                        array( 'icon' => '02', 'title' => __( '策略制定', 'developer-starter' ), 'desc' => __( '形成证据方案、程序方案和风险提示。', 'developer-starter' ), 'stage_tag' => __( '方案', 'developer-starter' ) ),
                        array( 'icon' => '03', 'title' => __( '执行推进', 'developer-starter' ), 'desc' => __( '按节点推进诉讼或非诉工作，并同步进度。', 'developer-starter' ), 'stage_tag' => __( '办理', 'developer-starter' ) ),
                        array( 'icon' => '04', 'title' => __( '结案复盘', 'developer-starter' ), 'desc' => __( '交付结案材料并给出后续风险防控建议。', 'developer-starter' ), 'stage_tag' => __( '交付', 'developer-starter' ) ),
                    ),
                    'module_bg_type'             => 'color',
                    'module_bg_color'            => '#ffffff',
                    'module_padding_top'         => '80px',
                    'module_padding_bottom'      => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '法律咨询常见问题', 'developer-starter' ),
                    'faq_subtitle'          => __( '先把关键问题讲清楚，再进入正式委托', 'developer-starter' ),
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array( 'question' => __( '初次咨询需要准备哪些资料？', 'developer-starter' ), 'answer' => __( '建议准备合同、往来记录、证据材料及诉求说明，便于快速评估。', 'developer-starter' ) ),
                        array( 'question' => __( '律师费如何计算？', 'developer-starter' ), 'answer' => __( '可按案件类型采用固定收费、阶段收费或风险代理模式。', 'developer-starter' ) ),
                        array( 'question' => __( '企业顾问服务包含哪些内容？', 'developer-starter' ), 'answer' => __( '通常包含合同审查、法律咨询、制度合规与专项培训等。', 'developer-starter' ) ),
                        array( 'question' => __( '异地案件是否可以代理？', 'developer-starter' ), 'answer' => __( '可提供异地案件协作与出庭安排，具体以案件情况评估。', 'developer-starter' ) ),
                    ),
                ),
            ),
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '提交案件咨询', 'developer-starter' ),
                    'contact_subtitle'       => __( '留下关键信息后，律师助理会尽快联系并安排沟通。', 'developer-starter' ),
                    'contact_show_form'      => '1',
                    'contact_form_id'        => '',
                    'contact_image'          => '',
                    'module_bg_type'         => 'color',
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '需要律师快速评估你的问题？', 'developer-starter' ),
                    'cta_subtitle'          => __( '一对一沟通案情，明确处理路径与时间预期。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即咨询律师', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
