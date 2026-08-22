<?php
/**
 * 视频首屏页面创建器类
 *
 * 当用户选择"视频首屏页面"模板创建页面时，自动填充预设模块内容
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
 * 视频首屏页面创建器类
 */
class Video_Hero_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-video-hero.php';
    protected const AJAX_ACTION = 'fill_video_hero_modules';
    protected const FILLED_META_KEY = '_video_hero_modules_filled';

    /**
     * 获取视频首屏页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 模块1：全屏视频首屏
            array(
                'type' => 'fullscreen_video',
                'data' => array(
                    'fsv_title'           => __( '启灵主题 · <strong>超乎想象</strong>', 'developer-starter' ),
                    'fsv_subtitle'        => __( '极速开发 · 优雅设计 · 极致体验', 'developer-starter' ),
                    'fsv_glow_intensity'  => 'medium',
                    'fsv_overlay_opacity' => '0.3',
                    'fsv_nav_items'       => array(
                        array( 'item_label' => __( '快速入门', 'developer-starter' ), 'item_icon' => '🚀', 'item_type' => 'link', 'item_link' => '#' ),
                        array( 'item_label' => __( '功能特性', 'developer-starter' ), 'item_icon' => '⚡', 'item_type' => 'link', 'item_link' => '#' ),
                        array( 'item_label' => __( '扫码体验', 'developer-starter' ), 'item_icon' => '📱', 'item_type' => 'qr', 'item_qr_desc' => __( '扫码查看移动端', 'developer-starter' ), 'item_qr' => '' ),
                    ),
                ),
            ),

            // 模块2：功能特性 (核心优势) - 使用 Emoji
            array(
                'type' => 'features',
                'data' => array(
                    'features_title'    => __( '核心优势', 'developer-starter' ),
                    'features_subtitle' => __( '为什么选择启灵主题？', 'developer-starter' ),
                    'features_columns'  => '4',
                    'features_items'    => array(
                        array( 'icon' => '⚡', 'title' => __( '极致性能', 'developer-starter' ), 'desc' => __( '底层代码优化，秒开体验', 'developer-starter' ) ),
                        array( 'icon' => '📱', 'title' => __( '全端适配', 'developer-starter' ), 'desc' => __( '完美兼容手机、平板和PC', 'developer-starter' ) ),
                        array( 'icon' => '🛡️', 'title' => __( '安全稳定', 'developer-starter' ), 'desc' => __( '企业级安全防护标准', 'developer-starter' ) ),
                        array( 'icon' => '🔄', 'title' => __( '持续更新', 'developer-starter' ), 'desc' => __( '源源不断的新功能支持', 'developer-starter' ) ),
                    ),
                ),
            ),

            // 模块3：CTA 行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'       => __( '准备好开始了吗？', 'developer-starter' ),
                    'cta_subtitle'    => __( '立即体验功能强大的企业级 WordPress 主题', 'developer-starter' ),
                    'cta_button_text' => __( '🔥 立即购买', 'developer-starter' ),
                    'cta_button_url'  => '#',
                ),
            ),
        );

        return $default_modules;
    }
}
