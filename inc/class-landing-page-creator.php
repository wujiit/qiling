<?php
/**
 * 落地页创建器类
 *
 * 当用户选择"Landing Page"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.2
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 落地页创建器类
 */
class Landing_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-landing.php';
    protected const AJAX_ACTION = 'fill_landing_modules';
    protected const FILLED_META_KEY = '_landing_modules_filled';

    /**
     * 获取落地页的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        // 获取页面标题用于动态内容
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '专业解决方案', 'developer-starter' );
        }
        
        $default_modules = array(
            // 模块1：Banner - 引人注目的首屏
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_title'    => $page_title,
                    'banner_subtitle' => __( '专业团队 · 定制方案 · 快速交付 · 全程服务', 'developer-starter' ),
                    'banner_btn_text' => __( '立即咨询', 'developer-starter' ),
                    'banner_btn_url'  => '#contact-form',
                    'banner_btn2_text' => __( '了解更多', 'developer-starter' ),
                    'banner_btn2_url'  => '#features',
                    'banner_bg_image' => '',
                    'banner_bg_color' => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 52%, #059669 100%)',
                    'banner_height'   => '600',
                ),
            ),

            // 模块2：统计数据 - 建立信任
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_title'    => '',
                    'stats_subtitle' => '',
                    'stats_bg_color' => '#ffffff',
                    'stats_items'    => array(
                        array(
                            'number' => '10+',
                            'label'  => __( '年行业经验', 'developer-starter' ),
                            'icon'   => '🏆',
                        ),
                        array(
                            'number' => '500+',
                            'label'  => __( '成功案例', 'developer-starter' ),
                            'icon'   => '📈',
                        ),
                        array(
                            'number' => '98%',
                            'label'  => __( '客户满意度', 'developer-starter' ),
                            'icon'   => '⭐',
                        ),
                        array(
                            'number' => '24h',
                            'label'  => __( '响应时间', 'developer-starter' ),
                            'icon'   => '⚡',
                        ),
                    ),
                ),
            ),

            // 模块3：服务展示 - 核心优势
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'    => __( '我们的核心优势', 'developer-starter' ),
                    'services_subtitle' => __( '选择我们，就是选择专业与可靠', 'developer-starter' ),
                    'services_bg_color' => '#f8fafc',
                    'services_items'    => array(
                        array(
                            'icon'  => '🎯',
                            'title' => __( '精准定位', 'developer-starter' ),
                            'desc'  => __( '深入了解您的需求，提供量身定制的解决方案，确保每一分投入都能产生最大价值', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '⚡',
                            'title' => __( '高效执行', 'developer-starter' ),
                            'desc'  => __( '专业团队协同作业，标准化流程管理，确保项目按时高质量交付', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '🛡️',
                            'title' => __( '安全可靠', 'developer-starter' ),
                            'desc'  => __( '采用业界领先的安全技术和标准，全方位保障您的数据和业务安全', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '🤝',
                            'title' => __( '全程陪伴', 'developer-starter' ),
                            'desc'  => __( '从咨询到售后，专属客户经理一对一服务，让您全程无忧', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块4：图文模块 - 产品/方案介绍
            array(
                'type' => 'image_text',
                'data' => array(
                    'image_text_layout'  => 'left',
                    'image_text_title'   => __( '为什么选择我们？', 'developer-starter' ),
                    'image_text_content' => __( '<p>我们不只是服务提供商，更是您的长期战略合作伙伴。凭借多年的行业积累和持续创新，我们已帮助数百家企业实现业务突破。</p><ul><li>✓ <strong>专业团队</strong> - 100+ 资深专家，平均从业经验 8 年以上</li><li>✓ <strong>技术领先</strong> - 持续研发投入，保持技术和方案的先进性</li><li>✓ <strong>服务保障</strong> - 7×24 小时技术支持，快速响应需求</li><li>✓ <strong>效果导向</strong> - 以客户成功为目标，用数据说话</li></ul>', 'developer-starter' ),
                    'image_text_button'  => __( '查看案例', 'developer-starter' ),
                    'image_text_url'     => '#cases',
                    'image_text_image'   => '',
                ),
            ),

            // 模块5：流程展示 - 合作步骤
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'    => __( '合作流程', 'developer-starter' ),
                    'process_subtitle' => __( '简单四步，开启您的成功之旅', 'developer-starter' ),
                    'process_bg_color' => '#ffffff',
                    'process_items'    => array(
                        array(
                            'icon'    => '📞',
                            'title'   => __( '免费咨询', 'developer-starter' ),
                            'desc'    => __( '联系我们，详细沟通您的需求 and 目标', 'developer-starter' ),
                            'icon_bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        ),
                        array(
                            'icon'    => '📋',
                            'title'   => __( '方案制定', 'developer-starter' ),
                            'desc'    => __( '根据需求分析，制定专属解决方案', 'developer-starter' ),
                            'icon_bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        ),
                        array(
                            'icon'    => '🚀',
                            'title'   => __( '快速落地', 'developer-starter' ),
                            'desc'    => __( '专业团队高效执行，按时高质交付', 'developer-starter' ),
                            'icon_bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                        ),
                        array(
                            'icon'    => '📈',
                            'title'   => __( '持续优化', 'developer-starter' ),
                            'desc'    => __( '定期复盘，持续优化，确保长期成功', 'developer-starter' ),
                            'icon_bg' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                        ),
                    ),
                ),
            ),

            // 模块6：客户评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'    => __( '客户好评', 'developer-starter' ),
                    'testimonials_subtitle' => __( '听听他们怎么说', 'developer-starter' ),
                    'testimonials_bg_color' => '#f8fafc',
                    'testimonials_items'    => array(
                        array(
                            'content' => __( '与他们合作是我们做过最正确的决定之一。专业的团队、高效的执行、贴心的服务，让我们的项目顺利上线并取得了超预期的效果。', 'developer-starter' ),
                            'author'  => __( '张总', 'developer-starter' ),
                            'company' => __( '某科技公司 CEO', 'developer-starter' ),
                            'avatar'  => '',
                        ),
                        array(
                            'content' => __( '他们不仅提供了优质的产品，更重要的是真正理解我们的业务需求。在整个合作过程中，沟通顺畅，响应及时，非常专业。', 'developer-starter' ),
                            'author'  => __( '李经理', 'developer-starter' ),
                            'company' => __( '某制造企业 IT总监', 'developer-starter' ),
                            'avatar'  => '',
                        ),
                        array(
                            'content' => __( '从前期咨询到后期维护，每一个环节都让人放心。特别是售后服务，有问题随时响应，真正做到了客户至上。', 'developer-starter' ),
                            'author'  => __( '王女士', 'developer-starter' ),
                            'company' => __( '某电商平台 运营总监', 'developer-starter' ),
                            'avatar'  => '',
                        ),
                    ),
                ),
            ),

            // 模块7：企业优势/特点
            array(
                'type' => 'features',
                'data' => array(
                    'features_title'    => __( '我们的承诺', 'developer-starter' ),
                    'features_subtitle' => __( '用专业 and 诚信赢得您的信任', 'developer-starter' ),
                    'features_bg_color' => '#ffffff',
                    'features_items'    => array(
                        array(
                            'icon'  => '💯',
                            'title' => __( '品质保证', 'developer-starter' ),
                            'desc'  => __( '严格的质量管控体系，确保交付物达到最高标准', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '💰',
                            'title' => __( '透明报价', 'developer-starter' ),
                            'desc'  => __( '明确的价格体系，无隐藏费用，物超所值', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '🔒',
                            'title' => __( '保密协议', 'developer-starter' ),
                            'desc'  => __( '严格的保密措施，全方位保护您的商业机密', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '🎁',
                            'title' => __( '增值服务', 'developer-starter' ),
                            'desc'  => __( '免费提供培训、文档等增值服务，助您快速上手', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块8：联系表单 CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'    => __( '立即行动，抢占先机', 'developer-starter' ),
                    'cta_subtitle' => __( '填写表单，我们的专家顾问将在 24 小时内与您联系，为您提供免费咨询服务', 'developer-starter' ),
                    'cta_btn_text' => __( '立即咨询', 'developer-starter' ),
                    'cta_btn_url'  => '/contact/',
                    'cta_bg_color' => 'linear-gradient(135deg, #1e3a8a 0%, #059669 100%)',
                ),
            ),
        );

        return $default_modules;
    }
}
