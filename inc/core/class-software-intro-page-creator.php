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
                    'ps_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题', 'Qiling Theme' ) : __( '启灵主题', 'qiling' ),
                    'ps_subtitle'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业级WordPress企业主题', 'A professional WordPress theme for modern business sites.' ) : __( '专业级WordPress企业主题', 'qiling' ),
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
                    'ps_price'     => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 399 ) : __( '¥399', 'qiling' ),
                    'ps_original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 299 ) : __( '¥299', 'qiling' ),
                    'ps_cta_text'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '前往购买', 'Buy Now' ) : __( '前往购买', 'qiling' ),
                    'ps_cta_url'   => '#',
                    'ps_cta_bg'    => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'ps_cta_color' => '#ffffff',
                    'ps_cta_target' => '_self',
                    'ps_description' => function_exists( 'developer_starter_get_locale_text' )
                        ? developer_starter_get_locale_text(
                            "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼容PHP：8.1及以上，推荐8.1/8.2版本\n授权时间：永久授权使用，免费更新",
                            "Latest version: V2.5\nUpdated: 2026-01-01\nWP support: 6.0-6.9, latest recommended\nPHP support: 8.1+, 8.2 recommended\nLicense: lifetime access with free updates"
                        )
                        : __( "最新版本：V2.5\n更新时间：2026-01-01\n兼容 WP：6.0-6.9，推荐最新版本\n兼容PHP：8.1及以上，推荐8.1/8.2版本\n授权时间：永久授权使用，免费更新", 'qiling' ),
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
                            'icon'        => 'icon-box',
                            'icon_image'  => '',
                            'title'       => __( '50+ 功能模块', 'qiling' ),
                            'badge'       => '',
                            'description' => __( '开箱即用的页面构建模块', 'qiling' ),
                            'url'         => '#features',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => 'icon-palette',
                            'icon_image'  => '',
                            'title'       => __( '高度自定义', 'qiling' ),
                            'badge'       => __( 'Hot', 'qiling' ),
                            'description' => __( '每个模块都支持深度定制', 'qiling' ),
                            'url'         => '#customize',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => 'icon-flash',
                            'icon_image'  => '',
                            'title'       => __( '极致性能', 'qiling' ),
                            'badge'       => '',
                            'description' => __( '优化加载，秒开体验', 'qiling' ),
                            'url'         => '#performance',
                            'target'      => '_self',
                        ),
                        array(
                            'icon'        => 'icon-lock',
                            'icon_image'  => '',
                            'title'       => __( '安全可靠', 'qiling' ),
                            'badge'       => '',
                            'description' => __( '代码规范，安全防护', 'qiling' ),
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
                    'title'    => __( '强大的功能特性', 'qiling' ),
                    'subtitle' => __( '50+ 内置模块，覆盖企业网站建设的各个方面', 'qiling' ),
                    'bg_color' => '',
                    'columns'  => '3',
                    'tabs'     => array(
                        // Tab 1：页面模块系统
                        array(
                            'tab_id'    => 'modules',
                            'tab_title' => __( '页面模块', 'qiling' ),
                            'tab_icon'  => 'icon-box',
                            'features'  => "icon-target|" . __( '横幅模块', 'qiling' ) . "|" . __( '支持轮播、视频背景的Banner展示', 'qiling' ) . "\nicon-box|" . __( '图文视频轮播', 'qiling' ) . "|" . __( '左右布局展示产品，支持视频媒体', 'qiling' ) . "\nicon-briefcase|" . __( '服务标签卡片', 'qiling' ) . "|" . __( '快速导航入口，图标+标题+描述', 'qiling' ) . "\n📰|" . __( '博客布局', 'qiling' ) . "|" . __( '自动获取文章，多种布局风格', 'qiling' ) . "\n💰|" . __( '价格套餐', 'qiling' ) . "|" . __( '可定制的定价表，支持渐变色', 'qiling' ) . "\nicon-clipboard|" . __( '功能清单', 'qiling' ) . "|" . __( 'Tab切换的功能卡片展示', 'qiling' ),
                        ),
                        // Tab 2：用户系统
                        array(
                            'tab_id'    => 'user',
                            'tab_title' => __( '用户系统', 'qiling' ),
                            'tab_icon'  => '👤',
                            'features'  => "🔐|" . __( '登录注册', 'qiling' ) . "|" . __( '完整的用户认证系统，支持弹窗登录', 'qiling' ) . "\n👤|" . __( '会员中心', 'qiling' ) . "|" . __( '个人资料管理，头像上传', 'qiling' ) . "\n🔑|" . __( '密码找回', 'qiling' ) . "|" . __( '邮件验证重置密码', 'qiling' ) . "\n📧|" . __( '邮件系统', 'qiling' ) . "|" . __( 'SMTP邮件发送支持', 'qiling' ) . "\nicon-shield|" . __( '表单验证', 'qiling' ) . "|" . __( '前后端双重验证', 'qiling' ) . "\n⏱️|" . __( '滑块验证', 'qiling' ) . "|" . __( '防止恶意提交，保护安全', 'qiling' ),
                        ),
                        // Tab 3：个性化定制
                        array(
                            'tab_id'    => 'customize',
                            'tab_title' => __( '个性化定制', 'qiling' ),
                            'tab_icon'  => 'icon-palette',
                            'features'  => "icon-palette|" . __( '主题色定制', 'qiling' ) . "|" . __( '一建立切换主题色，支持渐变色', 'qiling' ) . "\n🌙|" . __( '暗黑模式', 'qiling' ) . "|" . __( '完整的深色主题支持', 'qiling' ) . "\n📱|" . __( '悬浮工具栏', 'qiling' ) . "|" . __( '可定制的悬浮按钮', 'qiling' ) . "\n📢|" . __( '公告系统', 'qiling' ) . "|" . __( '多类型公告展示', 'qiling' ) . "\n🖼️|" . __( 'Logo定制', 'qiling' ) . "|" . __( '支持双Logo配置', 'qiling' ) . "\n📐|" . __( '模块间距', 'qiling' ) . "|" . __( '每个模块可自定义间距', 'qiling' ),
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
                        array( 'number' => '50+', 'label' => __( '功能模块', 'qiling' ) ),
                        array( 'number' => '30+', 'label' => __( '页面模板', 'qiling' ) ),
                        array( 'number' => '100%', 'label' => __( '响应式', 'qiling' ) ),
                        array( 'number' => '24/7', 'label' => __( '技术支持', 'qiling' ) ),
                    ),
                ),
            ),

            // 模块5：多图文模块 - 产品亮点
            array(
                'type' => 'multi_image_text',
                'data' => array(
                    'multi_image_text_title'    => __( '为什么选择我们？', 'qiling' ),
                    'multi_image_text_subtitle' => __( '精心设计的每一个细节，只为给您最好的体验', 'qiling' ),
                    'multi_image_text_layout'   => 'left',
                    'multi_image_text_items'    => array(
                        array(
                            'icon'  => '🧩',
                            'title' => __( '模块化设计', 'qiling' ),
                            'desc'  => __( '50+ 可复用模块，拖拽式页面构建，无需编码即可创建专业网站', 'qiling' ),
                            'image' => '',
                            'link'  => '',
                        ),
                        array(
                            'icon'  => 'icon-target',
                            'title' => __( '响应式布局', 'qiling' ),
                            'desc'  => __( '完美适配桌面、平板、手机等各种设备，确保最佳浏览体验', 'qiling' ),
                            'image' => '',
                            'link'  => '',
                        ),
                        array(
                            'icon'  => '🌙',
                            'title' => __( '暗黑模式', 'qiling' ),
                            'desc'  => __( '内置智能暗黑模式切换，保护用户眼睛，提升夜间浏览舒适度', 'qiling' ),
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
                    'faq_title' => __( '常见问题', 'qiling' ),
                    'faq_items' => array(
                        array(
                            'question' => __( '主题购买后包含哪些服务？', 'qiling' ),
                            'answer'   => __( '购买后您将获得：完整源代码、详细使用文档、终身免费更新、以及专业的技术支持服务。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '如何自定义页面模块？', 'qiling' ),
                            'answer'   => __( '选择需要展示的内容模块并填写内容即可，也可以调整模块顺序。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '主题支持多少个功能模块？', 'qiling' ),
                            'answer'   => __( '主题内置50+功能模块，包括：横幅、服务、产品、案例、新闻、价格表、视频、客户评价等，覆盖企业网站的各个需求。', 'qiling' ),
                        ),
                        array(
                            'question' => __( '是否支持暗黑模式？', 'qiling' ),
                            'answer'   => __( '是的，主题内置完整的暗黑模式支持，用户可以手动切换或跟随系统设置自动切换。', 'qiling' ),
                        ),
                    ),
                ),
            ),

            // 模块7：CTA行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'       => __( '准备好开始了吗？', 'qiling' ),
                    'cta_subtitle'    => __( '立即体验专业级WordPress企业主题', 'qiling' ),
                    'cta_button_text' => __( '立即购买', 'qiling' ),
                    'cta_button_url'  => '#',
                    'cta_bg_type'     => 'gradient',
                ),
            ),
        );

        return $default_modules;
    }
}
