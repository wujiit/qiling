<?php
/**
 * SaaS价格对比页创建器类
 *
 * 当用户选择"SaaS价格对比页"模板创建页面时，自动填充预设模块内容
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
 * SaaS价格对比页创建器类
 */
class Saas_Pricing_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-saas-pricing.php';
    protected const AJAX_ACTION = 'fill_saas_pricing_modules';
    protected const FILLED_META_KEY = '_saas_pricing_modules_filled';

    /**
     * 获取 SaaS 价格对比页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( 'SaaS 价格对比方案', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：动态首屏
            array(
                'type' => 'dynamic_banner',
                'data' => array(
                    'db_height'          => '80vh',
                    'db_bg_type'         => 'gradient',
                    'db_bg_gradient'     => 'linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #0ea5e9 100%)',
                    'db_title_prefix'    => $page_title,
                    'db_typing_mode'     => 'loop',
                    'db_typing_text'     => __( "灵活计费，按需升级\n从个人团队到企业级部署\n7 天免费试用，随时取消", 'developer-starter' ),
                    'db_highlight_color' => '#38bdf8',
                    'db_title_color'     => '#e2e8f0',
                    'db_subtitle'        => __( '<strong>清晰价格</strong> + <strong style="color:#60a5fa;">功能对比</strong>，帮助用户快速决策', 'developer-starter' ),
                    'db_desc'            => __( '预设包含套餐表、功能对比、常见问题与行动召唤模块。用户可直接替换文案和价格，快速搭建专业 SaaS 定价页面。', 'developer-starter' ),
                    'db_text_color'      => 'rgba(226,232,240,0.88)',
                    'db_buttons'         => array(
                        array(
                            'text'  => __( '开始免费试用', 'developer-starter' ),
                            'link'  => '#pricing-section',
                            'style' => 'primary',
                            'icon'  => '🚀',
                        ),
                        array(
                            'text'  => __( '查看功能对比', 'developer-starter' ),
                            'link'  => '#comparison-section',
                            'style' => 'outline',
                            'icon'  => '📊',
                        ),
                    ),
                    'db_media_type'      => 'image',
                    'db_main_image'      => '',
                    'db_image_shadow'    => 'soft',
                    'db_floating_cards'  => array(
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '14天试用', 'developer-starter' ),
                            'pos_top'      => '8%',
                            'pos_right'    => '-2%',
                            'animation'    => 'float',
                            'delay'        => '0s',
                        ),
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '支持月付/年付', 'developer-starter' ),
                            'pos_bottom'   => '10%',
                            'pos_left'     => '-4%',
                            'animation'    => 'pulse',
                            'delay'        => '0.4s',
                        ),
                    ),
                ),
            ),

            // 模块2：功能清单
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'             => __( '核心能力一目了然', 'developer-starter' ),
                    'subtitle'          => __( '围绕增长型 SaaS 场景设计，开箱即可用于商业落地', 'developer-starter' ),
                    'columns'           => '3',
                    'module_bg_type'    => 'color',
                    'module_bg_color'   => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs'              => array(
                        array(
                            'tab_id'    => 'growth',
                            'tab_title' => __( '增长能力', 'developer-starter' ),
                            'tab_icon'  => '📈',
                            'features'  => __( "🚀|快速上线|内置模块化页面结构，按需组合即可发布\n🎯|转化追踪|支持关键行为埋点与渠道成效分析\n💳|灵活付费|支持月付/年付与优惠活动配置", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'team',
                            'tab_title' => __( '团队协作', 'developer-starter' ),
                            'tab_icon'  => '🤝',
                            'features'  => __( "👥|多角色权限|支持管理员、运营、客服等多角色协作\n📂|工作流管理|从线索到成交的流程可视化追踪\n🔔|通知机制|关键操作实时提醒，避免信息遗漏", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'security',
                            'tab_title' => __( '稳定与安全', 'developer-starter' ),
                            'tab_icon'  => '🛡️',
                            'features'  => __( "🔐|数据隔离|租户级数据隔离，确保业务安全\n⚡|高可用架构|弹性扩展，应对业务高峰流量\n🧰|运维支持|监控告警与故障处理机制完善", 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块3：比较表格
            array(
                'type' => 'comparison',
                'data' => array(
                    'comparison_title'    => __( '版本功能对比', 'developer-starter' ),
                    'comparison_subtitle' => __( '按团队规模与业务阶段选择最合适的版本', 'developer-starter' ),
                    'comparison_features' => __( "成员席位\n自动化流程\n数据看板\n开放 API\n专属客服\nSLA 服务保障", 'developer-starter' ),
                    'comparison_products' => array(
                        array(
                            'name'   => __( '基础版', 'developer-starter' ),
                            'values' => __( "5 人\n基础流程\n基础看板\n✗\n邮件支持\n✗", 'developer-starter' ),
                        ),
                        array(
                            'name'   => __( '专业版', 'developer-starter' ),
                            'values' => __( "30 人\n高级流程\n高级看板\n✓\n在线支持\n✓", 'developer-starter' ),
                        ),
                        array(
                            'name'   => __( '企业版', 'developer-starter' ),
                            'values' => __( "无限制\n高级流程+定制\n企业级看板\n✓\n专属客户成功经理\n✓", 'developer-starter' ),
                        ),
                    ),
                    'comparison_highlight' => '2',
                    'module_bg_type'       => 'color',
                    'module_bg_color'      => '#f8fafc',
                    'module_padding_top'   => '80px',
                    'module_padding_bottom' => '80px',
                ),
            ),

            // 模块4：价格方案
            array(
                'type' => 'pricing',
                'data' => array(
                    'pricing_title'      => __( '透明套餐价格', 'developer-starter' ),
                    'pricing_subtitle'   => __( '支持月付与年付，随时升级，不影响历史数据', 'developer-starter' ),
                    'pricing_columns'    => '3',
                    'module_bg_type'     => 'color',
                    'module_bg_color'    => '#ffffff',
                    'module_padding_top' => '80px',
                    'module_padding_bottom' => '80px',
                    'pricing_items'      => array(
                        array(
                            'name'     => __( '基础版', 'developer-starter' ),
                            'price'    => '¥199',
                            'period'   => __( '/月', 'developer-starter' ),
                            'desc'     => __( '适合个人开发者与小型团队', 'developer-starter' ),
                            'features' => __( "✓ 5 个成员席位\n✓ 基础自动化流程\n✓ 基础数据看板\n✗ 开放 API\n✗ 专属客户成功", 'developer-starter' ),
                            'btn_text' => __( '立即试用', 'developer-starter' ),
                            'btn_link' => '#',
                            'card_bg'  => '#ffffff',
                            'featured' => '',
                        ),
                        array(
                            'name'         => __( '专业版', 'developer-starter' ),
                            'price'        => '¥699',
                            'period'       => __( '/月', 'developer-starter' ),
                            'desc'         => __( '适合成长型团队与业务部门', 'developer-starter' ),
                            'features'     => __( "✓ 30 个成员席位\n✓ 高级自动化流程\n✓ 高级数据看板\n✓ 开放 API\n✓ 在线优先支持", 'developer-starter' ),
                            'btn_text'     => __( '开始使用', 'developer-starter' ),
                            'btn_link'     => '#',
                            'card_bg'      => '#ffffff',
                            'featured'     => '1',
                            'featured_text' => __( '推荐', 'developer-starter' ),
                            'featured_bg'  => 'linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%)',
                        ),
                        array(
                            'name'     => __( '企业版', 'developer-starter' ),
                            'price'    => '¥2999',
                            'period'   => __( '/月', 'developer-starter' ),
                            'desc'     => __( '适合中大型企业与集团化部署', 'developer-starter' ),
                            'features' => __( "✓ 无限成员席位\n✓ 定制流程与报表\n✓ 专属部署方案\n✓ 企业级 API 与 SSO\n✓ 客户成功经理", 'developer-starter' ),
                            'btn_text' => __( '联系销售', 'developer-starter' ),
                            'btn_link' => '/contact/',
                            'card_bg'  => '#ffffff',
                            'featured' => '',
                        ),
                    ),
                ),
            ),

            // 模块5：常见问题
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'            => __( '购买前常见问题', 'developer-starter' ),
                    'faq_subtitle'         => __( '关于计费、升级、数据迁移与售后支持', 'developer-starter' ),
                    'module_bg_color'      => '#f8fafc',
                    'module_padding_top'   => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'            => array(
                        array(
                            'question' => __( '支持免费试用吗？', 'developer-starter' ),
                            'answer'   => __( '支持。默认提供 14 天免费试用，试用期可体验主要功能，确认后再升级付费版本。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '套餐升级会影响现有数据吗？', 'developer-starter' ),
                            'answer'   => __( '不会。升级仅调整可用功能与额度，原有业务数据和配置会完整保留。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '是否支持开票和对公付款？', 'developer-starter' ),
                            'answer'   => __( '支持。可根据企业采购流程提供对公合同、发票与付款信息。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '企业版可以私有化部署吗？', 'developer-starter' ),
                            'answer'   => __( '可以。企业版支持私有化部署与定制化安全策略，请联系销售评估实施方案。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块6：CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'         => __( '准备好提升你的业务效率了吗？', 'developer-starter' ),
                    'cta_subtitle'      => __( '立即开启免费试用，或联系销售获取企业级解决方案与专属报价。', 'developer-starter' ),
                    'cta_button_text'   => __( '免费试用 14 天', 'developer-starter' ),
                    'cta_button_url'    => '#',
                    'cta_bg_type'       => 'color',
                    'cta_bg_color'      => 'linear-gradient(135deg, #1d4ed8 0%, #0284c7 100%)',
                    'module_padding_top' => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
