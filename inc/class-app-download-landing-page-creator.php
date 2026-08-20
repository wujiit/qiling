<?php
/**
 * App下载落地页创建器类
 *
 * 当用户选择"App下载落地页"模板创建页面时，自动填充预设模块内容
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
 * App下载落地页创建器类
 */
class App_Download_Landing_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-app-download-landing.php';
    protected const AJAX_ACTION = 'fill_app_download_landing_modules';
    protected const FILLED_META_KEY = '_app_download_landing_modules_filled';

    /**
     * 获取 App 下载落地页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( 'App下载落地页', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：App 推广首屏
            array(
                'type' => 'app_hero',
                'data' => array(
                    'hero_title'        => $page_title,
                    'hero_desc'         => __( '适用于应用下载推广场景，支持 iOS / Android 双端引导、二维码直达和多卖点展示。', 'developer-starter' ),
                    'hero_buttons'      => array(
                        array(
                            'btn_type'    => 'apple',
                            'btn_text'    => 'App Store',
                            'btn_subtext' => 'Download on the',
                            'btn_link'    => '#',
                            'btn_style'   => 'light',
                        ),
                        array(
                            'btn_type'    => 'android',
                            'btn_text'    => 'Google Play',
                            'btn_subtext' => 'GET IT ON',
                            'btn_link'    => '#',
                            'btn_style'   => 'dark',
                        ),
                    ),
                    'show_qr_card'      => '1',
                    'qr_icon'           => '📱',
                    'qr_sub'            => __( '扫码下载', 'developer-starter' ),
                    'qr_title'          => __( '手机快速安装', 'developer-starter' ),
                    'qr_desc'           => __( '免手动搜索，扫码即可直达下载页', 'developer-starter' ),
                    'qr_image'          => '',
                    'media_type'        => 'image',
                    'hero_image'        => '',
                    'floating_elements' => array(
                        array(
                            'float_icon'  => '⚡',
                            'float_title' => __( '秒开体验', 'developer-starter' ),
                            'float_desc'  => __( '冷启动优化', 'developer-starter' ),
                            'float_pos'   => 'top-left',
                        ),
                        array(
                            'float_icon'  => '🔒',
                            'float_title' => __( '隐私安全', 'developer-starter' ),
                            'float_desc'  => __( '数据加密传输', 'developer-starter' ),
                            'float_pos'   => 'top-right',
                        ),
                        array(
                            'float_icon'  => '☁️',
                            'float_title' => __( '多端同步', 'developer-starter' ),
                            'float_desc'  => __( '账号云端托管', 'developer-starter' ),
                            'float_pos'   => 'bottom-left',
                        ),
                    ),
                    'bg_type'           => 'color',
                    'bg_color'          => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #38bdf8 100%)',
                    'padding_top'       => '100px',
                    'padding_bottom'    => '100px',
                ),
            ),

            // 模块2：小图引导模块
            array(
                'type' => 'qiling_image_guide',
                'data' => array(
                    'guide_title'         => __( '下载前先看核心功能', 'developer-starter' ),
                    'guide_subtitle'      => __( '通过小图引导快速了解产品价值，提升下载转化率', 'developer-starter' ),
                    'guide_bg_color'      => '#ffffff',
                    'guide_columns'       => '4',
                    'guide_item_height'   => '180px',
                    'guide_item_radius'   => '12px',
                    'guide_gap'           => '20px',
                    'module_padding_top'  => '80px',
                    'module_padding_bottom' => '80px',
                    'guide_items'         => array(
                        array(
                            'image'    => '',
                            'title'    => __( '智能首页', 'developer-starter' ),
                            'subtitle' => __( '关键数据与任务一屏掌握', 'developer-starter' ),
                            'link'     => '#',
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '离线可用', 'developer-starter' ),
                            'subtitle' => __( '弱网环境也能继续操作', 'developer-starter' ),
                            'link'     => '#',
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '消息中心', 'developer-starter' ),
                            'subtitle' => __( '重要提醒不错过', 'developer-starter' ),
                            'link'     => '#',
                        ),
                        array(
                            'image'    => '',
                            'title'    => __( '多平台同步', 'developer-starter' ),
                            'subtitle' => __( '手机、平板、桌面数据一致', 'developer-starter' ),
                            'link'     => '#',
                        ),
                    ),
                ),
            ),

            // 模块3：功能清单
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'                => __( '为什么值得下载', 'developer-starter' ),
                    'subtitle'             => __( '覆盖个人效率与团队协作的核心场景，下载即用', 'developer-starter' ),
                    'columns'              => '3',
                    'module_bg_type'       => 'color',
                    'module_bg_color'      => '#f8fafc',
                    'module_padding_top'   => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs'                 => array(
                        array(
                            'tab_id'    => 'efficiency',
                            'tab_title' => __( '效率提升', 'developer-starter' ),
                            'tab_icon'  => '⚡',
                            'features'  => __( "🧭|清晰信息结构|关键功能入口明确，上手成本更低\n⏱️|快捷操作链路|高频任务步骤更少，执行更快\n🧠|智能推荐|根据行为推荐最常用操作", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'collab',
                            'tab_title' => __( '协作能力', 'developer-starter' ),
                            'tab_icon'  => '🤝',
                            'features'  => __( "👥|团队共享空间|统一任务视图，协作更顺畅\n🔔|实时通知|重要节点及时提醒，减少延误\n📎|多格式附件|文档、图片、音视频统一管理", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'security',
                            'tab_title' => __( '稳定安全', 'developer-starter' ),
                            'tab_icon'  => '🛡️',
                            'features'  => __( "🔐|账号安全策略|支持多层校验和风险防护\n☁️|云端备份|历史数据可追溯，误删可恢复\n📊|稳定运行|高并发场景下保持流畅使用", 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块4：客户评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '用户口碑反馈', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实用户评价，帮助新用户更快建立信任', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '3,200+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'      => '',
                            'name'        => __( '林晨', 'developer-starter' ),
                            'position'    => __( '产品运营', 'developer-starter' ),
                            'content'     => __( '下载后两天就全员切换使用，任务同步和消息提醒非常稳定。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'google',
                            'date'        => '2026-01-12',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '周奕', 'developer-starter' ),
                            'position'    => __( '独立开发者', 'developer-starter' ),
                            'content'     => __( 'UI 清爽、上手快，移动端和桌面端的同步体验超出预期。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'xiaohongshu',
                            'date'        => '2026-02-03',
                            'verified'    => 'vip',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '何敏', 'developer-starter' ),
                            'position'    => __( '增长负责人', 'developer-starter' ),
                            'content'     => __( '活动落地页配合下载链路非常顺，转化率比上一版提升明显。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'weibo',
                            'date'        => '2026-02-26',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块5：FAQ
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '下载相关常见问题', 'developer-starter' ),
                    'faq_subtitle'          => __( '覆盖安装、兼容性、数据同步与账号安全问题', 'developer-starter' ),
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array(
                            'question' => __( '支持哪些系统版本？', 'developer-starter' ),
                            'answer'   => __( '支持主流 iOS / Android 版本，并持续兼容近三年的系统更新。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '需要注册才能使用吗？', 'developer-starter' ),
                            'answer'   => __( '支持手机号或邮箱快速注册，也支持第三方账号登录。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '数据会和网页版同步吗？', 'developer-starter' ),
                            'answer'   => __( '会。登录同一账号后可实时同步任务、消息与个人配置。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '下载后是否可以免费试用？', 'developer-starter' ),
                            'answer'   => __( '可以。默认提供免费试用期，试用结束后可按需升级套餐。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块6：CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'            => __( '立即下载，开启高效协作', 'developer-starter' ),
                    'cta_subtitle'         => __( '扫码直达下载页，或联系团队获取企业部署方案。', 'developer-starter' ),
                    'cta_button_text'      => __( '马上下载 App', 'developer-starter' ),
                    'cta_button_url'       => '#',
                    'cta_bg_type'          => 'color',
                    'cta_bg_color'         => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)',
                    'module_padding_top'   => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
