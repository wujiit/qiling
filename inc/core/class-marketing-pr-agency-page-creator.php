<?php
/**
 * 营销/公关服务官网页创建器类
 *
 * 当用户选择"营销/公关服务官网页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.7
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Marketing_PR_Agency_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-marketing-pr-agency.php';
    protected const AJAX_ACTION = 'fill_marketing_pr_agency_modules';
    protected const FILLED_META_KEY = '_marketing_pr_agency_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '营销/公关服务官网页', 'qiling' );
        }

        $default_modules = array(
            array(
                'type' => 'dynamic_banner',
                'data' => array(
                    'db_height'          => '82vh',
                    'db_bg_type'         => 'gradient',
                    'db_bg_gradient'     => 'linear-gradient(135deg, #111827 0%, #1d4ed8 45%, #06b6d4 100%)',
                    'db_title_prefix'    => $page_title,
                    'db_typing_mode'     => 'loop',
                    'db_typing_text'     => __( "品牌战略咨询与定位升级\n整合营销传播与内容增长\n公关声量管理与口碑运营", 'qiling' ),
                    'db_highlight_color' => '#67e8f9',
                    'db_title_color'     => '#e2e8f0',
                    'db_subtitle'        => __( '帮助品牌在竞争市场中获得持续关注、有效转化和长期资产沉淀。', 'qiling' ),
                    'db_desc'            => __( '预设已包含服务介绍、项目流程、案例、客户评价、方案报价与咨询入口，适合营销/公关公司官网快速上线。', 'qiling' ),
                    'db_text_color'      => 'rgba(226,232,240,0.88)',
                    'db_buttons'         => array(
                        array(
                            'text'  => __( '预约策略沟通', 'qiling' ),
                            'link'  => '#contact-section',
                            'style' => 'primary',
                            'icon'  => '',
                        ),
                        array(
                            'text'  => __( '查看项目案例', 'qiling' ),
                            'link'  => '#cases-section',
                            'style' => 'outline',
                            'icon'  => '',
                        ),
                    ),
                    'db_media_type'      => 'image',
                    'db_main_image'      => '',
                    'db_image_shadow'    => 'soft',
                    'db_floating_cards'  => array(
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '品牌增长导向', 'qiling' ),
                            'pos_top'      => '8%',
                            'pos_right'    => '-2%',
                            'animation'    => 'float',
                            'delay'        => '0s',
                        ),
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '传播与转化协同', 'qiling' ),
                            'pos_bottom'   => '10%',
                            'pos_left'     => '-4%',
                            'animation'    => 'pulse',
                            'delay'        => '0.4s',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '核心服务模块', 'qiling' ),
                    'services_subtitle'         => __( '覆盖战略、传播、执行到复盘的全链路营销服务', 'qiling' ),
                    'services_bg_color'         => '#ffffff',
                    'services_padding'          => 'normal',
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array(
                            'icon'  => '01',
                            'title' => __( '品牌战略咨询', 'qiling' ),
                            'desc'  => __( '定位诊断、竞品分析与年度增长路线规划。', 'qiling' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '02',
                            'title' => __( '整合营销传播', 'qiling' ),
                            'desc'  => __( '多平台协同传播，提升曝光、互动与线索转化。', 'qiling' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '03',
                            'title' => __( '内容创意与制作', 'qiling' ),
                            'desc'  => __( '品牌内容策划、素材制作与传播节奏设计。', 'qiling' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '04',
                            'title' => __( '公关传播管理', 'qiling' ),
                            'desc'  => __( '媒体关系维护、议题策划与舆情响应机制建设。', 'qiling' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '05',
                            'title' => __( '活动策划执行', 'qiling' ),
                            'desc'  => __( '发布会、快闪活动、城市路演全流程管理。', 'qiling' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '06',
                            'title' => __( '渠道投放优化', 'qiling' ),
                            'desc'  => __( '人群策略、预算分配与投放效果迭代优化。', 'qiling' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '07',
                            'title' => __( '私域增长运营', 'qiling' ),
                            'desc'  => __( '线索培育、社群运营和复购增长机制搭建。', 'qiling' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '08',
                            'title' => __( '数据复盘与顾问', 'qiling' ),
                            'desc'  => __( '阶段性复盘报告与持续增长顾问支持。', 'qiling' ),
                            'link'  => '#',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'         => __( '项目执行流程', 'qiling' ),
                    'process_subtitle'      => __( '标准化流程保障策略可落地、执行可追踪、结果可复盘', 'qiling' ),
                    'process_mode'          => 'standard',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'process_items'         => array(
                        array(
                            'icon'      => '01',
                            'title'     => __( '品牌诊断', 'qiling' ),
                            'stage_tag' => __( '分析', 'qiling' ),
                            'desc'      => __( '梳理现状、目标与问题，明确核心增长方向。', 'qiling' ),
                        ),
                        array(
                            'icon'      => '02',
                            'title'     => __( '策略制定', 'qiling' ),
                            'stage_tag' => __( '规划', 'qiling' ),
                            'desc'      => __( '输出品牌策略、传播节奏、资源计划与 KPI。', 'qiling' ),
                        ),
                        array(
                            'icon'      => '03',
                            'title'     => __( '整合执行', 'qiling' ),
                            'stage_tag' => __( '落地', 'qiling' ),
                            'desc'      => __( '多渠道协同执行，确保传播和转化同步推进。', 'qiling' ),
                        ),
                        array(
                            'icon'      => '04',
                            'title'     => __( '数据复盘', 'qiling' ),
                            'stage_tag' => __( '优化', 'qiling' ),
                            'desc'      => __( '基于数据复盘结果持续优化下一阶段方案。', 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'          => __( '品牌与公关项目案例', 'qiling' ),
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
                    'testimonials_title'       => __( '客户评价', 'qiling' ),
                    'testimonials_subtitle'    => __( '来自品牌方市场、公关与增长团队的反馈', 'qiling' ),
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
                            'name'     => __( '林总监', 'qiling' ),
                            'position' => __( '消费品牌 · 市场负责人', 'qiling' ),
                            'content'  => __( '策略和执行都很扎实，品牌声量和线索量同步提升。', 'qiling' ),
                            'rating'   => '5',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '王经理', 'qiling' ),
                            'position' => __( '科技公司 · 公关经理', 'qiling' ),
                            'content'  => __( '传播节奏把控专业，关键节点曝光效果超预期。', 'qiling' ),
                            'rating'   => '5',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '陈负责人', 'qiling' ),
                            'position' => __( '连锁品牌 · 增长负责人', 'qiling' ),
                            'content'  => __( '数据复盘机制非常清晰，后续优化方向明确。', 'qiling' ),
                            'rating'   => '5',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'pricing',
                'data' => array(
                    'pricing_title'         => __( '服务合作方案', 'qiling' ),
                    'pricing_subtitle'      => __( '按项目阶段和目标规模灵活选择合作方式', 'qiling' ),
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'pricing_columns'       => '3',
                    'pricing_items'         => array(
                        array(
                            'name'          => __( '策略咨询包', 'qiling' ),
                            'price'         => '¥29,800',
                            'period'        => __( '/项目', 'qiling' ),
                            'desc'          => __( '适合阶段性策略升级', 'qiling' ),
                            'features'      => __( "品牌诊断\n竞品分析\n传播策略建议\n季度复盘报告", 'qiling' ),
                            'btn_text'      => __( '咨询详情', 'qiling' ),
                            'btn_link'      => '#contact-section',
                            'card_bg'       => '#ffffff',
                            'featured'      => '',
                            'featured_text' => '',
                            'featured_bg'   => '',
                        ),
                        array(
                            'name'          => __( '整合营销包', 'qiling' ),
                            'price'         => '¥69,800',
                            'period'        => __( '/项目', 'qiling' ),
                            'desc'          => __( '适合新品发布与增长冲刺', 'qiling' ),
                            'features'      => __( "品牌策略与传播规划\n内容创意与制作\n多渠道投放执行\n月度数据复盘", 'qiling' ),
                            'btn_text'      => __( '预约方案沟通', 'qiling' ),
                            'btn_link'      => '#contact-section',
                            'card_bg'       => '#ffffff',
                            'featured'      => '1',
                            'featured_text' => __( '推荐', 'qiling' ),
                            'featured_bg'   => 'linear-gradient(135deg, #2563eb 0%, #06b6d4 100%)',
                        ),
                        array(
                            'name'          => __( '年度增长包', 'qiling' ),
                            'price'         => __( '定制报价', 'qiling' ),
                            'period'        => '',
                            'desc'          => __( '适合长期品牌与公关增长', 'qiling' ),
                            'features'      => __( "年度营销规划\n公关传播与舆情管理\n活动策划与执行\n专属顾问团队", 'qiling' ),
                            'btn_text'      => __( '联系我们', 'qiling' ),
                            'btn_link'      => '#contact-section',
                            'card_bg'       => '#ffffff',
                            'featured'      => '',
                            'featured_text' => '',
                            'featured_bg'   => '',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '获取品牌增长方案', 'qiling' ),
                    'contact_subtitle'       => __( '提交你的品牌目标、预算区间和项目周期，我们会给出阶段性执行建议。', 'qiling' ),
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
                    'cta_title'             => __( '让品牌增长更可持续', 'qiling' ),
                    'cta_subtitle'          => __( '从品牌策略到传播执行，一站式协同落地。', 'qiling' ),
                    'cta_button_text'       => __( '立即预约沟通', 'qiling' ),
                    'cta_button_url'        => '#contact-section',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #111827 0%, #1d4ed8 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
