<?php
/**
 * 开发者平台/API官网创建器类
 *
 * 当用户选择"开发者平台/API官网"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.7
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Developer_Platform_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-developer-platform.php';
    protected const AJAX_ACTION = 'fill_developer_platform_modules';
    protected const FILLED_META_KEY = '_developer_platform_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '开发者平台/API官网', 'qiling' );
        }

        $default_modules = array(
            array(
                'type' => 'hero_search',
                'data' => array(
                    'hs_height'             => 'medium',
                    'hs_overlay_color'      => 'rgba(15, 23, 42, 0.6)',
                    'hs_title'              => $page_title,
                    'hs_subtitle'           => __( 'API 文档、SDK 下载、接入指南与常见问题一站式聚合。', 'qiling' ),
                    'hs_bg_items'           => array(
                        array(
                            'type'      => 'image',
                            'image'     => '',
                            'video_url' => '',
                        ),
                    ),
                    'hs_show_search'        => '1',
                    'hs_search_placeholder' => __( '搜索 API、SDK、错误码、接入教程...', 'qiling' ),
                    'hs_search_btn_text'    => __( '搜索文档', 'qiling' ),
                    'hs_search_btn_bg'      => 'linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%)',
                    'hs_tags_label'         => __( '热门：', 'qiling' ),
                    'hs_tags'               => array(
                        array( 'text' => 'Authentication' ),
                        array( 'text' => 'Webhooks' ),
                        array( 'text' => 'Rate Limit' ),
                        array( 'text' => 'Error Codes' ),
                        array( 'text' => 'SDK' ),
                    ),
                    'hs_title_color'        => '#ffffff',
                    'hs_subtitle_color'     => 'rgba(255,255,255,0.85)',
                    'hs_tags_bg'            => 'rgba(255,255,255,0.14)',
                    'hs_tags_color'         => '#ffffff',
                ),
            ),
            array(
                'type' => 'tabs',
                'data' => array(
                    'tabs_title'            => __( '快速上手指引', 'qiling' ),
                    'tabs_subtitle'         => __( '按开发流程组织内容，降低集成门槛', 'qiling' ),
                    'tabs_style'            => 'pills',
                    'tabs_align'            => 'center',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs_items'            => array(
                        array(
                            'title'   => __( '认证与鉴权', 'qiling' ),
                            'icon'    => '🔐',
                            'content' => '<p>' . esc_html__( '获取 API Key，完成签名校验与权限范围配置。', 'qiling' ) . '</p><ul><li>API Key 创建与轮换</li><li>签名算法说明</li><li>权限Scope管理</li></ul>',
                        ),
                        array(
                            'title'   => __( '接口调用', 'qiling' ),
                            'icon'    => 'icon-flash',
                            'content' => '<p>' . esc_html__( '通过 REST API 发起请求，统一处理分页、幂等和重试策略。', 'qiling' ) . '</p><ul><li>请求参数规范</li><li>分页与筛选</li><li>幂等与重试机制</li></ul>',
                        ),
                        array(
                            'title'   => __( 'Webhook 事件', 'qiling' ),
                            'icon'    => 'icon-bell',
                            'content' => '<p>' . esc_html__( '订阅业务事件回调，实时同步订单、状态和消息变更。', 'qiling' ) . '</p><ul><li>事件订阅配置</li><li>回调签名校验</li><li>失败重投规则</li></ul>',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'accordion',
                'data' => array(
                    'accordion_title'       => __( '接入注意事项', 'qiling' ),
                    'accordion_subtitle'    => __( '上线前请确认以下关键配置', 'qiling' ),
                    'accordion_style'       => 'bordered',
                    'accordion_first_open'  => '1',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '70px',
                    'module_padding_bottom' => '70px',
                    'accordion_items'       => array(
                        array(
                            'title'   => __( '沙箱与生产环境如何切换？', 'qiling' ),
                            'content' => __( '建议在沙箱验证通过后，再切换到生产 Key，并同步更新回调地址。', 'qiling' ),
                            'icon'    => '🧪',
                        ),
                        array(
                            'title'   => __( '如何处理接口限流？', 'qiling' ),
                            'content' => __( '请读取响应头中的限流信息，采用指数退避与队列缓冲策略。', 'qiling' ),
                            'icon'    => '⏱️',
                        ),
                        array(
                            'title'   => __( '遇到错误码排查顺序？', 'qiling' ),
                            'content' => __( '优先检查鉴权、参数格式与时间戳，再核对业务状态与权限范围。', 'qiling' ),
                            'icon'    => 'icon-tool',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'service_cards',
                'data' => array(
                    'sc_bg_color' => '#ffffff',
                    'sc_padding'  => '72',
                    'sc_columns'  => '4',
                    'sc_gap'      => '20px',
                    'sc_card_bg'  => '#ffffff',
                    'sc_icon_bg'  => 'linear-gradient(135deg, #1d4ed8 0%, #06b6d4 100%)',
                    'sc_cards'    => array(
                        array(
                            'icon'        => 'icon-box',
                            'title'       => __( 'SDK 套件', 'qiling' ),
                            'badge'       => 'PHP / JS / Python',
                            'description' => __( '提供主流语言 SDK，减少重复开发。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '🧭',
                            'title'       => __( '场景化示例', 'qiling' ),
                            'badge'       => __( 'Best Practice', 'qiling' ),
                            'description' => __( '覆盖支付、消息、数据同步等核心集成场景。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '🧱',
                            'title'       => __( '模块化 API', 'qiling' ),
                            'badge'       => __( '可扩展', 'qiling' ),
                            'description' => __( '按能力域拆分接口，支持按需开通和迭代。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '💬',
                            'title'       => __( '开发者支持', 'qiling' ),
                            'badge'       => 'SLA',
                            'description' => __( '提供工单、群组与专属技术支持通道。', 'qiling' ),
                            'url'         => '#',
                            'target'      => '_self',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'resource_stats',
                'data' => array(
                    'rs_title'          => __( '平台运行数据', 'qiling' ),
                    'rs_subtitle'       => __( '面向开发者生态持续增长', 'qiling' ),
                    'rs_bg_color'       => '#f8fafc',
                    'rs_padding_top'    => '80px',
                    'rs_padding_bottom' => '80px',
                    'rs_stats_list'     => array(
                        array(
                            'stat_label'  => __( 'API 请求/日', 'qiling' ),
                            'stat_icon'   => 'icon-flash',
                            'data_source' => 'custom',
                            'source_id'   => '2800000',
                            'virtual_num' => '0',
                            'show_plus'   => 'yes',
                        ),
                        array(
                            'stat_label'  => __( '活跃开发者', 'qiling' ),
                            'stat_icon'   => '👨‍icon-laptop',
                            'data_source' => 'custom',
                            'source_id'   => '3500',
                            'virtual_num' => '0',
                            'show_plus'   => 'yes',
                        ),
                        array(
                            'stat_label'  => __( '上线应用', 'qiling' ),
                            'stat_icon'   => 'icon-rocket',
                            'data_source' => 'custom',
                            'source_id'   => '1200',
                            'virtual_num' => '0',
                            'show_plus'   => 'yes',
                        ),
                        array(
                            'stat_label'  => __( '可用性 SLA', 'qiling' ),
                            'stat_icon'   => 'icon-shield',
                            'data_source' => 'custom',
                            'source_id'   => '99.95',
                            'virtual_num' => '0',
                            'show_plus'   => 'no',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'downloads',
                'data' => array(
                    'downloads_title'       => __( 'SDK 与开发资源下载', 'qiling' ),
                    'downloads_subtitle'    => __( '选择对应语言快速接入，附带示例与更新日志', 'qiling' ),
                    'downloads_bg_color'    => '#ffffff',
                    'downloads_columns'     => '3',
                    'downloads_items'       => array(
                        array(
                            'title'       => 'PHP SDK',
                            'size'        => '3.6 MB',
                            'file'        => '#',
                            'icon'        => '🐘',
                            'format'      => 'ZIP',
                            'date'        => '2026-03-20',
                            'btn_text'    => __( '下载', 'qiling' ),
                            'description' => __( '包含 Composer 安装与示例代码。', 'qiling' ),
                        ),
                        array(
                            'title'       => 'JavaScript SDK',
                            'size'        => '2.1 MB',
                            'file'        => '#',
                            'icon'        => '🟨',
                            'format'      => 'ZIP',
                            'date'        => '2026-03-20',
                            'btn_text'    => __( '下载', 'qiling' ),
                            'description' => __( '支持浏览器与 Node.js 环境。', 'qiling' ),
                        ),
                        array(
                            'title'       => 'Python SDK',
                            'size'        => '2.8 MB',
                            'file'        => '#',
                            'icon'        => '🐍',
                            'format'      => 'ZIP',
                            'date'        => '2026-03-20',
                            'btn_text'    => __( '下载', 'qiling' ),
                            'description' => __( '适用于脚本自动化与服务端集成。', 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'compliance_trust',
                'data' => array(
                    'ct_title'                => __( '平台安全与合规', 'qiling' ),
                    'ct_subtitle'             => __( '从鉴权、审计到隐私保护，构建企业级开放平台信任基础。', 'qiling' ),
                    'ct_layout'               => 'grid',
                    'ct_columns'              => '3',
                    'ct_enable_filter'        => 'yes',
                    'ct_card_style'           => 'solid',
                    'module_bg_type'          => 'color',
                    'module_bg_color'         => '#f8fafc',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'ct_items'                => array(
                        array(
                            'icon'        => '🔐',
                            'title'       => 'SOC 2 Type II',
                            'short_name'  => 'SOC2',
                            'category'    => __( '平台安全', 'qiling' ),
                            'status'      => 'active',
                            'issuer'      => __( '第三方审计机构', 'qiling' ),
                            'scope'       => __( 'API 平台与运维流程', 'qiling' ),
                            'valid_until' => __( '年度审计', 'qiling' ),
                            'report_url'  => '#',
                            'report_text' => __( '查看审计说明', 'qiling' ),
                        ),
                        array(
                            'icon'        => '📜',
                            'title'       => 'ISO/IEC 27001',
                            'short_name'  => 'ISO27001',
                            'category'    => __( '信息安全', 'qiling' ),
                            'status'      => 'active',
                            'issuer'      => __( '国际认证机构', 'qiling' ),
                            'scope'       => __( '平台与数据管理体系', 'qiling' ),
                            'valid_until' => '2028-11-30',
                            'report_url'  => '#',
                            'report_text' => __( '查看认证范围', 'qiling' ),
                        ),
                        array(
                            'icon'        => 'icon-globe',
                            'title'       => 'GDPR',
                            'short_name'  => 'GDPR',
                            'category'    => __( '隐私合规', 'qiling' ),
                            'status'      => 'active',
                            'issuer'      => __( '法务合规团队', 'qiling' ),
                            'scope'       => __( '用户数据处理与跨境规则', 'qiling' ),
                            'valid_until' => __( '持续合规监测', 'qiling' ),
                            'report_url'  => '#',
                            'report_text' => __( '查看隐私说明', 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '开发者常见问题', 'qiling' ),
                    'faq_subtitle'          => __( '关于限流、版本升级和错误排查的重点说明', 'qiling' ),
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array(
                            'question' => __( 'API 版本升级会影响现有接口吗？', 'qiling' ),
                            'answer'   => __( '采用版本并行策略，旧版接口在公告周期内继续可用。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '如何提高请求配额？', 'qiling' ),
                            'answer'   => __( '可在控制台提交申请，审核后会按业务规模调整配额。', 'qiling' ),
                        ),
                        array(
                            'question' => __( 'Webhook 回调失败怎么处理？', 'qiling' ),
                            'answer'   => __( '平台会按重试策略投递，并提供失败事件查询与重放。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '是否提供企业私有网关？', 'qiling' ),
                            'answer'   => __( '支持企业专属网关与专线接入方案，可联系技术团队评估。', 'qiling' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '联系开发者支持团队', 'qiling' ),
                    'contact_subtitle'       => __( '提交接入需求，我们会提供专属对接建议与排期。', 'qiling' ),
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
                    'cta_title'             => __( '准备开始 API 集成？', 'qiling' ),
                    'cta_subtitle'          => __( '从测试环境到生产上线，获得完整接入支持。', 'qiling' ),
                    'cta_button_text'       => __( '申请接入', 'qiling' ),
                    'cta_button_url'        => '#',
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
