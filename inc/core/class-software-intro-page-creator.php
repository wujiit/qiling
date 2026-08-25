<?php
/**
 * 软件介绍页面创建器类
 *
 * 当用户选择"软件介绍"模板创建页面时，自动填充预设模块内容
 * 使用图文视频轮播、服务标签卡片、功能清单列表等模块组合
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
 * 软件介绍页面创建器类
 */
class Software_Intro_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-software-intro.php';
    protected const AJAX_ACTION = 'fill_software_intro_modules';
    protected const FILLED_META_KEY = '_software_intro_modules_filled';

    /**
     * 获取软件介绍页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 模块1：图文视频轮播 - 首屏展示
            array(
                'type' => 'product_showcase',
                'data' => array(
                    'ps_bg_color'  => 'linear-gradient(135deg, #14b8ff 0%, #2563eb 100%)',
                    'ps_padding'   => '80',
                    'ps_layout'    => 'left',
                    'ps_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题', 'Qiling Theme' ) : __( '启灵主题', 'developer-starter' ),
                    'ps_subtitle'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业级WordPress企业主题', 'A professional WordPress theme for modern business sites.' ) : __( '专业级WordPress企业主题', 'developer-starter' ),
                    'ps_media_items' => array(
                        array(
                            'type'      => 'image',
                            'image'     => '',
                            'video_url' => '',
                            'video_poster' => '',
                        ),
                    ),
                    'ps_media_height' => '450px',
                    'ps_media_radius' => '16px',
                    'ps_show_price' => 'yes',
                    'ps_price'     => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 399 ) : __( '¥399', 'developer-starter' ),
                    'ps_original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 299 ) : __( '¥299', 'developer-starter' ),
                    'ps_cta_text'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '前往购买', 'Buy Now' ) : __( '前往购买', 'developer-starter' ),
                    'ps_cta_url'   => '#',
                    'ps_cta_bg'    => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'ps_cta_color' => '#ffffff',
                    'ps_cta_target' => '_self',
                    'ps_description' => function_exists( 'developer_starter_get_locale_text' )
                        ? developer_starter_get_locale_text(
                            "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼用PHP：7.4及以上，推荐8.0版本\n授权时间：永久授权使用，免费更新",
                            "Latest version: V2.5\nUpdated: 2026-01-01\nWP support: 6.0-6.9, latest recommended\nPHP support: 7.4+, 8.0 recommended\nLicense: lifetime access with free updates"
                        )
                        : __( "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼用PHP：7.4及以上，推荐8.0版本\n授权时间：永久授权使用，免费更新", 'developer-starter' ),
                ),
            ),

            // 模块2：服务标签卡片 - 快速入口
            array(
                'type' => 'service_cards',
                'data' => array(
                    'sc_bg_color'    => '#f8fafc',
                    'sc_padding'     => '60',
                    'sc_columns'     => '4',
                    'sc_gap'         => '20px',
                    'sc_card_bg'     => 'rgba(255, 255, 255, 1)',
                    'sc_card_radius' => '16px',
                    'sc_card_shadow' => 'medium',
                    'sc_icon_size'   => '48px',
                    'sc_icon_bg'     => 'linear-gradient(135deg, #3b82f6 0%, #10b981 100%)',
                    'sc_icon_radius' => '12px',
                    'sc_badge_bg'    => 'linear-gradient(135deg, #3b82f6 0%, #10b981 100%)',
                    'sc_cards'       => array(
                        array(
                            'icon'        => '📦',
                            'icon_image'  => '',
                            'title'       => __( '50+ 功能模块', 'developer-starter' ),
                            'badge'       => '',
                            'description' => __( '开箱即用的页面构建模块', 'developer-starter' ),
                            'url'         => '#features',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '🎨',
                            'icon_image'  => '',
                            'title'       => __( '高度自定义', 'developer-starter' ),
                            'badge'       => __( 'Hot', 'developer-starter' ),
                            'description' => __( '每个模块都支持深度定制', 'developer-starter' ),
                            'url'         => '#customize',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '⚡',
                            'icon_image'  => '',
                            'title'       => __( '极致性能', 'developer-starter' ),
                            'badge'       => '',
                            'description' => __( '优化加载，秒开体验', 'developer-starter' ),
                            'url'         => '#performance',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => '🔒',
                            'icon_image'  => '',
                            'title'       => __( '安全可靠', 'developer-starter' ),
                            'badge'       => '',
                            'description' => __( '代码规范，安全防护', 'developer-starter' ),
                            'url'         => '#security',
                            'target'      => '_self',
                        ),
                    ),
                    'module_margin_top'    => '-60px',
                    'module_margin_bottom' => '',
                ),
            ),

            // 模块3：功能清单列表 - 核心功能展示
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'    => __( '强大的功能特性', 'developer-starter' ),
                    'subtitle' => __( '50+ 内置模块，覆盖企业网站建设的各个方面', 'developer-starter' ),
                    'bg_color' => '',
                    'columns'  => '3',
                    'tabs'     => array(
                        // Tab 1：页面模块系统
                        array(
                            'tab_id'    => 'modules',
                            'tab_title' => __( '页面模块', 'developer-starter' ),
                            'tab_icon'  => '📦',
                            'features'  => "🎯|" . __( '横幅模块', 'developer-starter' ) . "|" . __( '支持轮播、视频背景的Banner展示', 'developer-starter' ) . "\n📦|" . __( '图文视频轮播', 'developer-starter' ) . "|" . __( '左右布局展示产品，支持视频媒体', 'developer-starter' ) . "\n💼|" . __( '服务标签卡片', 'developer-starter' ) . "|" . __( '快速导航入口，图标+标题+描述', 'developer-starter' ) . "\n📰|" . __( '博客布局', 'developer-starter' ) . "|" . __( '自动获取文章，多种布局风格', 'developer-starter' ) . "\n💰|" . __( '价格套餐', 'developer-starter' ) . "|" . __( '可定制的定价表，支持渐变色', 'developer-starter' ) . "\n📋|" . __( '功能清单', 'developer-starter' ) . "|" . __( 'Tab切换的功能卡片展示', 'developer-starter' ),
                        ),
                        // Tab 2：用户系统
                        array(
                            'tab_id'    => 'user',
                            'tab_title' => __( '用户系统', 'developer-starter' ),
                            'tab_icon'  => '👤',
                            'features'  => "🔐|" . __( '登录注册', 'developer-starter' ) . "|" . __( '完整的用户认证系统，支持弹窗登录', 'developer-starter' ) . "\n👤|" . __( '会员中心', 'developer-starter' ) . "|" . __( '个人资料管理，头像上传', 'developer-starter' ) . "\n🔑|" . __( '密码找回', 'developer-starter' ) . "|" . __( '邮件验证重置密码', 'developer-starter' ) . "\n📧|" . __( '邮件系统', 'developer-starter' ) . "|" . __( 'SMTP邮件发送支持', 'developer-starter' ) . "\n🛡️|" . __( '表单验证', 'developer-starter' ) . "|" . __( '前后端双重验证', 'developer-starter' ) . "\n⏱️|" . __( '滑块验证', 'developer-starter' ) . "|" . __( '防止恶意提交，保护安全', 'developer-starter' ),
                        ),
                        // Tab 3：个性化定制
                        array(
                            'tab_id'    => 'customize',
                            'tab_title' => __( '个性化定制', 'developer-starter' ),
                            'tab_icon'  => '🎨',
                            'features'  => "🎨|" . __( '主题色定制', 'developer-starter' ) . "|" . __( '一建立切换主题色，支持渐变色', 'developer-starter' ) . "\n🌙|" . __( '暗黑模式', 'developer-starter' ) . "|" . __( '完整的深色主题支持', 'developer-starter' ) . "\n📱|" . __( '悬浮工具栏', 'developer-starter' ) . "|" . __( '可定制的悬浮按钮', 'developer-starter' ) . "\n📢|" . __( '公告系统', 'developer-starter' ) . "|" . __( '多类型公告展示', 'developer-starter' ) . "\n🖼️|" . __( 'Logo定制', 'developer-starter' ) . "|" . __( '支持双Logo配置', 'developer-starter' ) . "\n📐|" . __( '模块间距', 'developer-starter' ) . "|" . __( '每个模块可自定义间距', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块4：数据统计
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_bg_image'   => '',
                    'stats_text_align' => 'center',
                    'stats_items'      => array(
                        array( 'number' => '50+', 'label' => __( '功能模块', 'developer-starter' ) ),
                        array( 'number' => '30+', 'label' => __( '页面模板', 'developer-starter' ) ),
                        array( 'number' => '100%', 'label' => __( '响应式', 'developer-starter' ) ),
                        array( 'number' => '24/7', 'label' => __( '技术支持', 'developer-starter' ) ),
                    ),
                ),
            ),

            // 模块5：多图文模块 - 产品亮点
            array(
                'type' => 'multi_image_text',
                'data' => array(
                    'multi_image_text_title'    => __( '为什么选择我们？', 'developer-starter' ),
                    'multi_image_text_subtitle' => __( '精心设计的每一个细节，只为给您最好的体验', 'developer-starter' ),
                    'multi_image_text_layout'   => 'left',
                    'multi_image_text_items'    => array(
                        array(
                            'icon'  => '🧩',
                            'title' => __( '模块化设计', 'developer-starter' ),
                            'desc'  => __( '50+ 可复用模块，拖拽式页面构建，无需编码即可创建专业网站', 'developer-starter' ),
                            'image' => '',
                            'link'  => '',
                        ),
                        array(
                            'icon'  => '🎯',
                            'title' => __( '响应式布局', 'developer-starter' ),
                            'desc'  => __( '完美适配桌面、平板、手机等各种设备，确保最佳浏览体验', 'developer-starter' ),
                            'image' => '',
                            'link'  => '',
                        ),
                        array(
                            'icon'  => '🌙',
                            'title' => __( '暗黑模式', 'developer-starter' ),
                            'desc'  => __( '内置智能暗黑模式切换，保护用户眼睛，提升夜间浏览舒适度', 'developer-starter' ),
                            'image' => '',
                            'link'  => '',
                        ),
                    ),
                ),
            ),

            // 模块6：常见问题
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title' => __( '常见问题', 'developer-starter' ),
                    'faq_items' => array(
                        array(
                            'question' => __( '主题购买后包含哪些服务？', 'developer-starter' ),
                            'answer'   => __( '购买后您将获得：完整源代码、详细使用文档、终身免费更新、以及专业的技术支持服务。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '如何自定义页面模块？', 'developer-starter' ),
                            'answer'   => __( '选择需要展示的内容模块并填写内容即可，也可以调整模块顺序。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '主题支持多少个功能模块？', 'developer-starter' ),
                            'answer'   => __( '主题内置50+功能模块，包括：横幅、服务、产品、案例、新闻、价格表、视频、客户评价等，覆盖企业网站的各个需求。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '是否支持暗黑模式？', 'developer-starter' ),
                            'answer'   => __( '是的，主题内置完整的暗黑模式支持，用户可以手动切换或跟随系统设置自动切换。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块7：CTA行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'       => __( '准备好开始了吗？', 'developer-starter' ),
                    'cta_subtitle'    => __( '立即体验专业级WordPress企业主题', 'developer-starter' ),
                    'cta_button_text' => __( '立即购买', 'developer-starter' ),
                    'cta_button_url'  => '#',
                    'cta_bg_type'     => 'gradient',
                ),
            ),
        );

        return $default_modules;
    }
}
