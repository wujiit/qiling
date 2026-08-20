<?php
/**
 * 功能清单展示页面创建器类
 *
 * 当用户选择"功能清单展示"模板创建页面时，自动填充预设模块内容
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
 * 功能清单展示页面创建器类
 */
class Features_Showcase_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-features-showcase.php';
    protected const AJAX_ACTION = 'fill_features_showcase_modules';
    protected const FILLED_META_KEY = '_features_showcase_modules_filled';

    /**
     * 获取功能清单展示页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 模块1：功能清单列表 - 核心模块
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'    => __( '强大的功能特性', 'developer-starter' ),
                    'subtitle' => __( '22+ 内置模块，覆盖企业网站建设的各个方面', 'developer-starter' ),
                    'bg_color' => '',
                    'columns'  => '3',
                    'tabs'     => array(
                        // Tab 1：页面模块系统
                        array(
                            'tab_id'    => 'modules',
                            'tab_title' => __( '页面模块', 'developer-starter' ),
                            'tab_icon'  => '📦',
                            'features'  => __( "🎯|横幅模块|支持轮播、视频背景的Banner展示，多种动画效果可选\n📦|产品中心|分类展示产品，自动获取封面图，多种布局风格\n💼|成功案例|客户案例展示，支持多种卡片风格切换\n📰|新闻动态|自动获取文章列表，多布局展示新闻资讯\n💰|价格套餐|可定制的定价表模块，支持渐变色和特性列表\n📋|功能清单|Tab切换的功能卡片展示，现代化设计\n🔄|流程展示|时间线/步骤式流程展示，多种样式可选\n🎥|视频模块|支持本地视频 and B站视频嵌入播放\n💬|客户评价|多种样式的客户见证模块，增强可信度", 'developer-starter' ),
                        ),
                        // Tab 2：用户系统
                        array(
                            'tab_id'    => 'user',
                            'tab_title' => __( '用户系统', 'developer-starter' ),
                            'tab_icon'  => '👤',
                            'features'  => __( "🔐|登录注册|完整的用户认证系统，支持邮箱验证\n👤|会员中心|个人资料管理，头像上传，密码修改\n🔑|密码找回|邮件验证重置密码，安全可靠\n📧|邮件系统|SMTP邮件发送支持，加密存储凭证\n🛡️|表单验证|前后端双重验证，防止恶意提交\n⏱️|频率限制|防止暴力破解，保护用户账户安全", 'developer-starter' ),
                        ),
                        // Tab 3：个性化定制
                        array(
                            'tab_id'    => 'customize',
                            'tab_title' => __( '个性化定制', 'developer-starter' ),
                            'tab_icon'  => '🎨',
                            'features'  => __( "🎨|主题色定制|一键切换主题色，支持渐变色配置\n🌙|暗黑模式|完整的深色主题支持，自动适配所有模块\n📱|悬浮工具栏|可定制的悬浮按钮，联系方式快捷入口\n📢|公告系统|多类型公告展示，文字/图文/弹窗模式\n🖼️|Logo定制|支持普通/暗色模式双Logo配置\n📐|页脚配置|多栏页脚布局，社交媒体图标，动画效果", 'developer-starter' ),
                        ),
                        // Tab 4：性能与安全
                        array(
                            'tab_id'    => 'performance',
                            'tab_title' => __( '性能与安全', 'developer-starter' ),
                            'tab_icon'  => '⚡',
                            'features'  => __( "⚡|性能优化|图片懒加载，资源按需加载，极速体验\n🛡️|安全防护|XSS防护，数据转义，安全输出\n📊|后台管理|简洁直观的设置面板，分组配置项\n🔧|模块管理|拖拽可视化配置，实时预览效果\n📱|响应式设计|完美适配各种屏幕尺寸，移动端优化\n🔍|SEO优化|语义化标签，结构化数据，搜索引擎友好", 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块2：数据统计
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_bg_image'   => '',
                    'stats_text_align' => 'center',
                    'stats_items'      => array(
                        array( 'number' => '22', 'label' => __( '内置模块', 'developer-starter' ) ),
                        array( 'number' => '50', 'label' => __( '功能特性', 'developer-starter' ) ),
                        array( 'number' => '100', 'label' => __( '响应式', 'developer-starter' ) ),
                        array( 'number' => '0', 'label' => __( '代码要求', 'developer-starter' ) ),
                    ),
                ),
            ),

            // 模块3：多图文模块 - 更多亮点
            array(
                'type' => 'multi_image_text',
                'data' => array(
                    'multi_image_text_title'    => __( '更多亮点', 'developer-starter' ),
                    'multi_image_text_subtitle' => __( '精心设计的细节，让您的网站与众不同', 'developer-starter' ),
                    'multi_image_text_layout'   => 'left',
                    'multi_image_text_items'    => array(
                        array(
                            'icon'  => '📦',
                            'title' => __( '模块化架构', 'developer-starter' ),
                            'desc'  => __( '采用面向对象的模块化设计，代码结构清晰，易于扩展和维护。每个模块独立运作，互不干扰。', 'developer-starter' ),
                            'image' => '',
                            'link'  => '',
                        ),
                        array(
                            'icon'  => '🎨',
                            'title' => __( '可视化配置', 'developer-starter' ),
                            'desc'  => __( '后台拖拽式模块配置，所见即所得。无需编写代码，轻松构建专业页面。', 'developer-starter' ),
                            'image' => '',
                            'link'  => '',
                        ),
                        array(
                            'icon'  => '🛡️',
                            'title' => __( '安全可靠', 'developer-starter' ),
                            'desc'  => __( '遵循WordPress安全最佳实践，数据转义、权限验证、CSRF保护，让您高枕无忧。', 'developer-starter' ),
                            'image' => '',
                            'link'  => '',
                        ),
                    ),
                ),
            ),

            // 模块4：常见问题
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title' => __( '常见问题', 'developer-starter' ),
                    'faq_items' => array(
                        array(
                            'question' => __( '如何添加自定义模块到页面？', 'developer-starter' ),
                            'answer'   => __( '在后台编辑页面时，找到「页面模块配置」区域，点击「添加模块」按钮选择需要的模块类型，然后填写模块内容即可。支持拖拽排序和实时预览。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '主题支持哪些页面模板？', 'developer-starter' ),
                            'answer'   => __( '主题内置多种页面模板：模块化首页、关于我们、产品中心、成功案例、新闻动态、解决方案、落地页、功能清单展示、常见问题、联系我们等，满足企业网站的各种需求。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '如何自定义主题色？', 'developer-starter' ),
                            'answer'   => __( '进入「外观」→「主题设置」，在「基础设置」选项卡中可以配置主题主色调、辅助色等，支持颜色选择器 and 渐变色配置，修改后全站自动应用。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '主题对SEO优化有帮助吗？', 'developer-starter' ),
                            'answer'   => __( '是的，主题采用语义化HTML5标签、合理的标题层级结构、图片懒加载优化、快速加载性能等，都有助于搜索引擎优化。建议配合专业SEO插件使用效果更佳。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块5：CTA行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'       => __( '准备好开始了吗？', 'developer-starter' ),
                    'cta_subtitle'    => __( '立即体验功能强大的企业级WordPress主题', 'developer-starter' ),
                    'cta_button_text' => __( '联系我们', 'developer-starter' ),
                    'cta_button_url'  => '/contact/',
                ),
            ),
        );

        return $default_modules;
    }
}
