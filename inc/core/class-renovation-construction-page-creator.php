<?php
/**
 * 装修/工装官网页创建器类
 *
 * 当用户选择"装修/工装官网页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.6
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 装修/工装官网页创建器类
 */
class Renovation_Construction_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-renovation-construction.php';
    protected const AJAX_ACTION = 'fill_renovation_construction_modules';
    protected const FILLED_META_KEY = '_renovation_construction_modules_filled';

    /**
     * 获取装修/工装官网页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '装修/工装官网页', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：首屏
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'       => 'slider',
                    'banner_height'       => 'large',
                    'banner_bg_color'     => 'linear-gradient(135deg, #111827 0%, #1f2937 100%)',
                    'banner_slides'       => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '办公室、商业空间与连锁门店全案设计施工，一体化交付。', 'developer-starter' ),
                            'btn_text'       => __( '获取方案报价', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#111827',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '设计 + 施工 + 软装落地', 'developer-starter' ),
                            'subtitle'       => __( '从量房到竣工验收，提供标准化节点与可视化进度。', 'developer-starter' ),
                            'btn_text'       => __( '查看服务流程', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#111827',
                        ),
                    ),
                    'show_stats_bar'      => '1',
                    'stats_data'          => array(
                        array(
                            'icon'   => '🏢',
                            'number' => '300+',
                            'label'  => __( '商业项目经验', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '📐',
                            'number' => '120万㎡',
                            'label'  => __( '累计施工面积', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '🛠️',
                            'number' => '30天',
                            'label'  => __( '标准交付周期', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '✅',
                            'number' => '96%',
                            'label'  => __( '按期交付率', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                    ),
                    'banner_wave_enable'  => '0',
                ),
            ),

            // 模块2：服务内容
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'            => __( '装修/工装服务', 'developer-starter' ),
                    'services_subtitle'         => __( '围绕商业空间全生命周期，提供可落地的设计与施工能力', 'developer-starter' ),
                    'services_bg_color'         => '#ffffff',
                    'services_padding'          => 'normal',
                    'services_columns'          => '4',
                    'services_items'            => array(
                        array(
                            'icon'  => '🧭',
                            'title' => __( '空间规划设计', 'developer-starter' ),
                            'desc'  => __( '动线、分区与品牌风格统一设计，兼顾功能与体验。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🏗️',
                            'title' => __( '工装施工落地', 'developer-starter' ),
                            'desc'  => __( '标准化施工管理，过程可追踪，节点可验收。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🪑',
                            'title' => __( '软装与设备配套', 'developer-starter' ),
                            'desc'  => __( '家具、灯光、导视系统一体化搭配交付。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🔧',
                            'title' => __( '维保与改造升级', 'developer-starter' ),
                            'desc'  => __( '运营阶段维保响应，支持局部更新和形象升级。', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块3：流程模块
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'             => __( '项目实施流程', 'developer-starter' ),
                    'process_subtitle'          => __( '清晰里程碑管理，保证工期、质量与预算可控', 'developer-starter' ),
                    'process_mode'              => 'industrial',
                    'process_items'             => array(
                        array(
                            'icon'               => '01',
                            'title'              => __( '需求沟通与现场勘测', 'developer-starter' ),
                            'stage_tag'          => __( '前期', 'developer-starter' ),
                            'desc'               => __( '明确经营目标、预算区间与空间功能需求。', 'developer-starter' ),
                            'duration'           => __( '3-5天', 'developer-starter' ),
                            'quality_checkpoint' => __( '需求确认单', 'developer-starter' ),
                            'deliverable'        => __( '勘测报告', 'developer-starter' ),
                            'icon_bg'            => '#1f2937',
                        ),
                        array(
                            'icon'               => '02',
                            'title'              => __( '方案设计与预算报价', 'developer-starter' ),
                            'stage_tag'          => __( '设计', 'developer-starter' ),
                            'desc'               => __( '输出平面、效果与材质建议，形成报价清单。', 'developer-starter' ),
                            'duration'           => __( '7-10天', 'developer-starter' ),
                            'quality_checkpoint' => __( '方案评审', 'developer-starter' ),
                            'deliverable'        => __( '设计方案包', 'developer-starter' ),
                            'icon_bg'            => '#374151',
                        ),
                        array(
                            'icon'               => '03',
                            'title'              => __( '施工进场与节点验收', 'developer-starter' ),
                            'stage_tag'          => __( '施工', 'developer-starter' ),
                            'desc'               => __( '按节点推进水电、泥木、油漆与安装工程。', 'developer-starter' ),
                            'duration'           => __( '20-35天', 'developer-starter' ),
                            'quality_checkpoint' => __( '隐蔽工程验收', 'developer-starter' ),
                            'deliverable'        => __( '阶段验收记录', 'developer-starter' ),
                            'icon_bg'            => '#4b5563',
                        ),
                        array(
                            'icon'               => '04',
                            'title'              => __( '软装布置与开业交付', 'developer-starter' ),
                            'stage_tag'          => __( '交付', 'developer-starter' ),
                            'desc'               => __( '完成软装入场、清洁收尾与运营前检查。', 'developer-starter' ),
                            'duration'           => __( '3-5天', 'developer-starter' ),
                            'quality_checkpoint' => __( '竣工验收', 'developer-starter' ),
                            'deliverable'        => __( '交付清单', 'developer-starter' ),
                            'icon_bg'            => '#6b7280',
                        ),
                    ),
                    'module_bg_type'            => 'color',
                    'module_bg_color'           => '#f9fafb',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块4：案例展示
            array(
                'type' => 'cases',
                'data' => array(
                    'cases_title'             => __( '工装案例', 'developer-starter' ),
                    'cases_count'             => '6',
                    'cases_columns'           => '3',
                    'cases_show_image'        => '1',
                    'cases_image_height'      => '220px',
                    'cases_padding_top'       => '80px',
                    'cases_padding_bottom'    => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块5：客户评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '客户评价', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '来自商业业主与品牌方的真实反馈', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '1,200+',
                    'average_rating'          => '4.8',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'      => '',
                            'name'        => __( '陈总', 'developer-starter' ),
                            'position'    => __( '连锁餐饮品牌负责人', 'developer-starter' ),
                            'content'     => __( '方案落地性强，工期控制稳定，门店如期开业。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'dianping',
                            'date'        => '2026-01-18',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '张经理', 'developer-starter' ),
                            'position'    => __( '办公空间项目负责人', 'developer-starter' ),
                            'content'     => __( '施工过程沟通及时，节点验收资料齐全，协作顺畅。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'google',
                            'date'        => '2026-02-06',
                            'verified'    => 'vip',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '刘女士', 'developer-starter' ),
                            'position'    => __( '零售门店运营总监', 'developer-starter' ),
                            'content'     => __( '导视与陈列细节做得很好，开业后客流反馈明显提升。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'meituan',
                            'date'        => '2026-03-11',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块6：FAQ
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'              => __( '装修工装常见问题', 'developer-starter' ),
                    'faq_subtitle'           => __( '关于预算、工期、材料与售后的高频问题', 'developer-starter' ),
                    'module_bg_color'        => '#f9fafb',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                    'faq_items'              => array(
                        array(
                            'question' => __( '工装项目通常多久能交付？', 'developer-starter' ),
                            'answer'   => __( '根据面积和施工难度不同，常见交付周期为 30-60 天。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '报价是否包含主材和软装？', 'developer-starter' ),
                            'answer'   => __( '可按需求拆分为基础施工、主材与软装三部分明细报价。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '施工期间如何同步进度？', 'developer-starter' ),
                            'answer'   => __( '我们按周输出施工进度与节点照片，并安排关键节点验收。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '竣工后是否提供维保？', 'developer-starter' ),
                            'answer'   => __( '支持维保与改造升级服务，具体按合同约定执行。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块7：联系我们
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '获取专属方案与报价', 'developer-starter' ),
                    'contact_subtitle'       => __( '提交项目信息后，顾问会尽快联系并安排现场沟通。', 'developer-starter' ),
                    'contact_show_form'      => '1',
                    'contact_form_id'        => '',
                    'contact_image'          => '',
                    'module_bg_type'         => 'color',
                    'module_bg_color'        => '#ffffff',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),

            // 模块8：行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '准备启动你的空间项目？', 'developer-starter' ),
                    'cta_subtitle'          => __( '从设计到交付，一次对接，快速推进。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即咨询', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #111827 0%, #374151 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
