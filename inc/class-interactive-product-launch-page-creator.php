<?php
/**
 * 交互产品发布页创建器类
 *
 * 当用户选择"交互产品发布页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.5
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 交互产品发布页创建器类
 */
class Interactive_Product_Launch_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-interactive-product-launch.php';
    protected const AJAX_ACTION = 'fill_interactive_product_launch_modules';
    protected const FILLED_META_KEY = '_interactive_product_launch_modules_filled';

    /**
     * 获取交互产品发布页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '交互产品发布页', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：交互首屏 Banner
            array(
                'type' => 'interact_hero',
                'data' => array(
                    'badge_text'            => 'NEW RELEASE',
                    'hero_title_content'    => $page_title . '<br><span style="color:#2563eb;">全新交互体验</span>',
                    'hero_subtitle_content' => __( '用于新品发布、版本发布和活动宣发，内置交互首屏结构，发布信息更清晰，转化路径更直接。', 'developer-starter' ),
                    'btn_primary_content'   => __( '立即预约演示', 'developer-starter' ),
                    'btn_primary_link'      => '#release-features',
                    'btn_secondary_content' => __( '查看核心亮点', 'developer-starter' ),
                    'btn_secondary_link'    => '#wheel-features',
                    'media_type'            => 'image',
                    'hero_image'            => '',
                    'enable_float_anim'     => 'yes',
                    'feature_items'         => array(
                        array(
                            'f_icon_type'      => 'class',
                            'f_icon_class'     => '⚡',
                            'f_title_content'  => __( '快速发布', 'developer-starter' ),
                            'f_desc'           => __( '活动页与产品页一键组合', 'developer-starter' ),
                            'f_link'           => '#',
                        ),
                        array(
                            'f_icon_type'      => 'class',
                            'f_icon_class'     => '🎨',
                            'f_title_content'  => __( '视觉统一', 'developer-starter' ),
                            'f_desc'           => __( '预设发布风格，保持品牌一致性', 'developer-starter' ),
                            'f_link'           => '#',
                        ),
                        array(
                            'f_icon_type'      => 'class',
                            'f_icon_class'     => '📈',
                            'f_title_content'  => __( '转化导向', 'developer-starter' ),
                            'f_desc'           => __( '强调 CTA 与功能亮点展示', 'developer-starter' ),
                            'f_link'           => '#',
                        ),
                        array(
                            'f_icon_type'      => 'class',
                            'f_icon_class'     => '🧩',
                            'f_title_content'  => __( '模块可编辑', 'developer-starter' ),
                            'f_desc'           => __( '全部文案、按钮与布局均可修改', 'developer-starter' ),
                            'f_link'           => '#',
                        ),
                    ),
                    'bg_color'              => '#eef7ff',
                    'text_color'            => '#1e293b',
                    'highlight_color'       => '#2563eb',
                    'padding_top'           => '96px',
                    'padding_bottom'        => '86px',
                ),
            ),

            // 模块2：圆形交互轮盘
            array(
                'type' => 'circle_wheel',
                'data' => array(
                    'wheel_title'          => __( '发布版本核心能力', 'developer-starter' ),
                    'wheel_subtitle'       => __( '通过 <span style="color:#4ffbdf;">交互轮盘</span> 快速浏览每个核心亮点', 'developer-starter' ),
                    'wheel_bg_type'        => 'color',
                    'wheel_bg_color'       => '#0f172a',
                    'wheel_bg_overlay'     => '0.75',
                    'highlight_color'      => '#4ffbdf',
                    'wheel_padding_top'    => '96px',
                    'wheel_padding_bottom' => '96px',
                    'wheel_items'          => array(
                        array(
                            'ring_title_desc'  => __( '实时协作', 'developer-starter' ),
                            'hover_title_desc' => __( '多人实时协作', 'developer-starter' ),
                            'hover_desc'       => __( '支持跨团队在线协同编辑，变更实时同步，减少沟通成本。', 'developer-starter' ),
                            'highlight'        => 'yes',
                        ),
                        array(
                            'ring_title_desc'  => __( '自动化流程', 'developer-starter' ),
                            'hover_title_desc' => __( '自动化流程编排', 'developer-starter' ),
                            'hover_desc'       => __( '将高频重复操作配置为自动流程，显著提升执行效率。', 'developer-starter' ),
                            'highlight'        => 'no',
                        ),
                        array(
                            'ring_title_desc'  => __( '洞察看板', 'developer-starter' ),
                            'hover_title_desc' => __( '业务洞察看板', 'developer-starter' ),
                            'hover_desc'       => __( '关键指标一屏呈现，支持按团队、渠道、阶段进行多维分析。', 'developer-starter' ),
                            'highlight'        => 'no',
                        ),
                        array(
                            'ring_title_desc'  => __( '开放接口', 'developer-starter' ),
                            'hover_title_desc' => __( '开放 API 能力', 'developer-starter' ),
                            'hover_desc'       => __( '可与现有 CRM、工单系统和 BI 平台快速打通。', 'developer-starter' ),
                            'highlight'        => 'no',
                        ),
                        array(
                            'ring_title_desc'  => __( '权限模型', 'developer-starter' ),
                            'hover_title_desc' => __( '细粒度权限管理', 'developer-starter' ),
                            'hover_desc'       => __( '内置角色权限与操作审计机制，满足企业合规要求。', 'developer-starter' ),
                            'highlight'        => 'no',
                        ),
                        array(
                            'ring_title_desc'  => __( '弹性扩容', 'developer-starter' ),
                            'hover_title_desc' => __( '高并发弹性架构', 'developer-starter' ),
                            'hover_desc'       => __( '面对大流量活动发布场景，仍可保持稳定访问体验。', 'developer-starter' ),
                            'highlight'        => 'no',
                        ),
                        array(
                            'ring_title_desc'  => __( '多端适配', 'developer-starter' ),
                            'hover_title_desc' => __( '全端一致体验', 'developer-starter' ),
                            'hover_desc'       => __( '支持 PC、平板和移动端，确保核心信息在不同设备一致展示。', 'developer-starter' ),
                            'highlight'        => 'no',
                        ),
                        array(
                            'ring_title_desc'  => __( '持续迭代', 'developer-starter' ),
                            'hover_title_desc' => __( '持续迭代升级', 'developer-starter' ),
                            'hover_desc'       => __( '版本更新可平滑发布，支持灰度上线与快速回滚策略。', 'developer-starter' ),
                            'highlight'        => 'no',
                        ),
                    ),
                ),
            ),

            // 模块3：双栏轮播
            array(
                'type' => 'double_column_carousel',
                'data' => array(
                    'dcc_layout'  => '3',
                    'dcc_height'  => '500',
                    'dcc_gap'     => '16',
                    'dcc_slides'  => array(
                        array(
                            'image' => '',
                            'url'   => '#',
                            'title' => __( '发布会主视觉', 'developer-starter' ),
                        ),
                        array(
                            'image' => '',
                            'url'   => '#',
                            'title' => __( '产品能力展示', 'developer-starter' ),
                        ),
                        array(
                            'image' => '',
                            'url'   => '#',
                            'title' => __( '客户案例与数据', 'developer-starter' ),
                        ),
                    ),
                    'dcc_right_1_image' => '',
                    'dcc_right_1_url'   => '#',
                    'dcc_right_2_image' => '',
                    'dcc_right_2_url'   => '#',
                    'dcc_right_3_image' => '',
                    'dcc_right_3_url'   => '#',
                ),
            ),

            // 模块4：功能清单
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'                => __( '发布版本能力地图', 'developer-starter' ),
                    'subtitle'             => __( '从用户体验、团队协作到运维安全，一页看全新版能力升级', 'developer-starter' ),
                    'columns'              => '3',
                    'module_bg_type'       => 'color',
                    'module_bg_color'      => '#ffffff',
                    'module_padding_top'   => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs'                 => array(
                        array(
                            'tab_id'    => 'experience',
                            'tab_title' => __( '体验升级', 'developer-starter' ),
                            'tab_icon'  => '✨',
                            'features'  => __( "🎛️|全新交互界面|页面信息分层更清晰，关键操作路径更短\n⚡|加载体验优化|关键首屏资源按需加载，打开更流畅\n📱|移动端细节优化|触控反馈和布局适配更符合移动习惯", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'business',
                            'tab_title' => __( '业务能力', 'developer-starter' ),
                            'tab_icon'  => '📊',
                            'features'  => __( "📈|数据看板升级|新增趋势分析和目标达成跟踪\n🧭|流程引导增强|支持关键任务分步引导，降低学习成本\n🔌|第三方集成|可对接常见企业工具和内部系统", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'reliability',
                            'tab_title' => __( '稳定与安全', 'developer-starter' ),
                            'tab_icon'  => '🛡️',
                            'features'  => __( "🔐|权限体系强化|角色边界更清晰，支持审计追踪\n🧱|弹性架构支持|应对活动高峰，稳定承载大流量\n🚨|预警机制升级|异常行为和性能波动可提前感知", 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块5：CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'           => __( '准备好发布你的下一代产品了吗？', 'developer-starter' ),
                    'cta_subtitle'        => __( '使用该模板快速搭建交互式产品发布页，聚焦亮点传达与转化。', 'developer-starter' ),
                    'cta_button_text'     => __( '预约演示', 'developer-starter' ),
                    'cta_button_url'      => '/contact/',
                    'cta_bg_type'         => 'color',
                    'cta_bg_color'        => 'linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%)',
                    'module_padding_top'  => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
