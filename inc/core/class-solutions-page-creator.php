<?php
/**
 * 解决方案页面创建器类
 *
 * 当用户选择"解决方案"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 解决方案页面创建器类
 */
class Solutions_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-solutions.php';
    protected const AJAX_ACTION = 'fill_solutions_modules';
    protected const FILLED_META_KEY = '_solutions_modules_filled';

    /**
     * 获取解决方案页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 模块1：图文模块 - 解决方案概述（左图右文）
            array(
                'type' => 'image_text',
                'data' => array(
                    'image_text_layout'  => 'left',
                    'image_text_title'   => __( '全方位解决方案', 'qiling' ),
                    'image_text_content' => '<p>' . __( '我们提供专业、高效、定制化的解决方案，帮助企业应对数字化转型过程中的各种挑战。', 'qiling' ) . '</p><p>' . __( '基于多年行业经验和技术积累，我们的解决方案已成功服务于数百家企业，涵盖制造、金融、医疗、教育等多个领域。', 'qiling' ) . '</p><ul><li>✓ ' . __( '深度定制，贴合业务需求', 'qiling' ) . '</li><li>✓ ' . __( '快速部署，降低实施风险', 'qiling' ) . '</li><li>✓ ' . __( '持续迭代，保持技术领先', 'qiling' ) . '</li></ul>',
                    'image_text_button'  => __( '了解详情', 'qiling' ),
                    'image_text_url'     => '#features',
                    'image_text_image'   => '',
                ),
            ),

            // 模块2：多列布局 - 核心能力（3列）
            array(
                'type' => 'columns',
                'data' => array(
                    'columns_count' => '3',
                    'columns_items' => array(
                        array(
                            'title'   => __( '智能分析', 'qiling' ),
                            'content' => '<p>' . __( '利用AI和大数据技术，对业务数据进行深度分析，挖掘潜在价值，为决策提供数据支撑。', 'qiling' ) . '</p><ul><li>' . __( '实时数据监控', 'qiling' ) . '</li><li>' . __( '智能预警系统', 'qiling' ) . '</li><li>' . __( '可视化报表', 'qiling' ) . '</li></ul>',
                            'image'   => '',
                        ),
                        array(
                            'title'   => __( 'icon-flash 高效协同', 'qiling' ),
                            'content' => '<p>' . __( '打通部门壁垒，实现信息共享和流程协同，大幅提升团队协作效率和项目交付速度。', 'qiling' ) . '</p><ul><li>' . __( '跨部门协作', 'qiling' ) . '</li><li>' . __( '流程自动化', 'qiling' ) . '</li><li>' . __( '移动办公支持', 'qiling' ) . '</li></ul>',
                            'image'   => '',
                        ),
                        array(
                            'title'   => __( 'icon-shield 安全可靠', 'qiling' ),
                            'content' => '<p>' . __( '采用企业级安全架构，多层防护机制确保数据安全，让您的业务运行无后忧之忧。', 'qiling' ) . '</p><ul><li>' . __( '数据加密存储', 'qiling' ) . '</li><li>' . __( '权限精细管理', 'qiling' ) . '</li><li>' . __( '灾备恢复机制', 'qiling' ) . '</li></ul>',
                            'image'   => '',
                        ),
                    ),
                ),
            ),

            // 模块3：合作流程 - 实施步骤
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'    => __( '解决方案实施流程', 'qiling' ),
                    'process_subtitle' => __( '科学规范的实施流程，确保项目顺利落地', 'qiling' ),
                    'process_bg_color' => '#f8fafc',
                    'process_items'    => array(
                        array(
                            'icon'    => 'icon-clipboard',
                            'title'   => __( '需求调研', 'qiling' ),
                            'desc'    => __( '深入了解企业现状和痛点，明确项目目标和范围', 'qiling' ),
                            'icon_bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        ),
                        array(
                            'icon'    => '📐',
                            'title'   => __( '方案设计', 'qiling' ),
                            'desc'    => __( '根据调研结果，定制专属解决方案和实施计划', 'qiling' ),
                            'icon_bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        ),
                        array(
                            'icon'    => '🔧',
                            'title'   => __( '开发部署', 'qiling' ),
                            'desc'    => __( '敏捷开发模式，快速迭代，分阶段交付成果', 'qiling' ),
                            'icon_bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                        ),
                        array(
                            'icon'    => 'icon-rocket',
                            'title'   => __( '培训上线', 'qiling' ),
                            'desc'    => __( '完善的培训体系和上线支持，确保平稳过渡', 'qiling' ),
                            'icon_bg' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                        ),
                    ),
                ),
            ),

            // 模块4：图文模块 - 技术优势（右图左文）
            array(
                'type' => 'image_text',
                'data' => array(
                    'image_text_layout'  => 'right',
                    'image_text_title'   => __( '领先的技术架构', 'qiling' ),
                    'image_text_content' => '<p>' . __( '采用微服务架构设计，具备高可用、高并发、易扩展的特性，支持私有化部署和混合云方案。', 'qiling' ) . '</p><p><strong>' . __( '技术亮点：', 'qiling' ) . '</strong></p><ul><li>🔹 ' . __( '云原生架构，弹性伸缩', 'qiling' ) . '</li><li>🔹 ' . __( '微服务设计，模块解耦', 'qiling' ) . '</li><li>🔹 ' . __( '容器化部署，运维简便', 'qiling' ) . '</li><li>🔹 ' . __( '多租户支持，资源隔离', 'qiling' ) . '</li><li>🔹 ' . __( '开放API接口，无缝集成', 'qiling' ) . '</li></ul>',
                    'image_text_button'  => __( '技术白皮书', 'qiling' ),
                    'image_text_url'     => '#',
                    'image_text_image'   => '',
                ),
            ),

            // 模块5：企业优势 - 为什么选择我们
            array(
                'type' => 'features',
                'data' => array(
                    'features_title'    => __( '为什么选择我们的解决方案', 'qiling' ),
                    'features_subtitle' => __( '专业团队 + 成熟产品 + 优质服务 = 成功保障', 'qiling' ),
                    'features_items'    => array(
                        array(
                            'icon'  => 'icon-trophy',
                            'title' => __( '行业领先', 'qiling' ),
                            'desc'  => __( '10+年行业深耕，服务500+企业客户', 'qiling' ),
                        ),
                        array(
                            'icon'  => '👥',
                            'title' => __( '专业团队', 'qiling' ),
                            'desc'  => __( '200+技术专家，提供全程专业支持', 'qiling' ),
                        ),
                        array(
                            'icon'  => '📈',
                            'title' => __( '效果显著', 'qiling' ),
                            'desc'  => __( '平均提升30%运营效率，降低20%成本', 'qiling' ),
                        ),
                        array(
                            'icon'  => '🤝',
                            'title' => __( '贴心服务', 'qiling' ),
                            'desc'  => __( '7×24小时响应，专属客户成功经理', 'qiling' ),
                        ),
                    ),
                ),
            ),

            // 模块6：多列布局 - 成功案例预览（带图片）
            array(
                'type' => 'columns',
                'data' => array(
                    'columns_count' => '3',
                    'columns_items'      => array(
                        array(
                            'title'   => __( '制造业数字化转型', 'qiling' ),
                            'content' => '<p style="color: #64748b; font-size: 0.9rem;">' . __( '帮助某大型制造企业实现生产全流程数字化管理，生产效率提升35%，库存周转率提高28%。', 'qiling' ) . '</p><a href="#" style="color: var(--color-primary); font-weight: 500;">' . __( '查看详情 →', 'qiling' ) . '</a>',
                            'image'   => '',
                        ),
                        array(
                            'title'   => __( '金融风控系统升级', 'qiling' ),
                            'content' => '<p style="color: #64748b; font-size: 0.9rem;">' . __( '为某银行打造智能风控平台，实现风险识别准确率98%，审批效率提升50%。', 'qiling' ) . '</p><a href="#" style="color: var(--color-primary); font-weight: 500;">' . __( '查看详情 →', 'qiling' ) . '</a>',
                            'image'   => '',
                        ),
                        array(
                            'title'   => __( '医疗信息化建设', 'qiling' ),
                            'content' => '<p style="color: #64748b; font-size: 0.9rem;">' . __( '助力某三甲医院建设智慧医疗系统，患者等待时间减少60%，医疗差错率降低75%。', 'qiling' ) . '</p><a href="#" style="color: var(--color-primary); font-weight: 500;">' . __( '查看详情 →', 'qiling' ) . '</a>',
                            'image'   => '',
                        ),
                    ),
                ),
            ),

            // 模块7：CTA行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'    => __( '开启您的数字化转型之旅', 'qiling' ),
                    'cta_subtitle' => __( '立即联系我们，获取免费需求评估和专属解决方案', 'qiling' ),
                    'cta_btn_text' => __( '预约咨询', 'qiling' ),
                    'cta_btn_url'  => '/contact/',
                    'cta_bg_color' => 'linear-gradient(135deg, #2563eb 0%, #059669 100%)',
                ),
            ),
        );

        return $default_modules;
    }
}
