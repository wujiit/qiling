<?php
/**
 * Resource Hero Pro Module - 虚拟资源专用首屏
 *
 * 用于数字产品/虚拟资源网站的营销首屏：
 * - 左侧品牌与权益说明
 * - 右侧资源文章自动滚动展示（纵向/横向）
 * - 底部资源横向流（可选）
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Resource_Hero_Pro_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-download';
        $this->description = __( '虚拟资源站专用首屏，支持资源文章滚动展示与转化引导。', 'developer-starter' );
    }

    public function get_id() {
        return 'resource_hero_pro';
    }

    public function get_name() {
        return __( '资源首屏Pro', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'rh_layout',
                'type'    => 'select',
                'label'   => __( '布局模式', 'developer-starter' ),
                'options' => array(
                    'left_right' => __( '左文右资源（推荐）', 'developer-starter' ),
                    'top_bottom' => __( '上文下资源', 'developer-starter' ),
                    'text_only'  => __( '仅文案区', 'developer-starter' ),
                ),
                'default' => 'left_right',
            ),
            array(
                'id'      => 'rh_height',
                'type'    => 'select',
                'label'   => __( '模块高度', 'developer-starter' ),
                'options' => array(
                    'auto'   => __( '自适应内容', 'developer-starter' ),
                    '58vh'   => __( '58vh', 'developer-starter' ),
                    '78vh'   => __( '78vh', 'developer-starter' ),
                    '88vh'   => __( '88vh', 'developer-starter' ),
                    '100vh'  => __( '100vh', 'developer-starter' ),
                    'custom' => __( '自定义', 'developer-starter' ),
                ),
                'default' => '58vh',
            ),
            array(
                'id'         => 'rh_height_custom',
                'type'       => 'text',
                'label'      => __( '自定义高度', 'developer-starter' ),
                'default'    => '760px',
                'dependency' => array( 'rh_height', '==', 'custom' ),
            ),
            array(
                'id'      => 'rh_bg_type',
                'type'    => 'select',
                'label'   => __( '背景类型', 'developer-starter' ),
                'options' => array(
                    'gradient' => __( '渐变背景', 'developer-starter' ),
                    'color'    => __( '纯色背景', 'developer-starter' ),
                    'image'    => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'gradient',
            ),
            array(
                'id'         => 'rh_bg_gradient',
                'type'       => 'text',
                'label'      => __( '渐变 CSS', 'developer-starter' ),
                'default'    => 'linear-gradient(135deg, var(--color-neutral-900) 0%, var(--color-primary-dark) 45%, var(--qiling-color-60a5fa) 100%)',
                'dependency' => array( 'rh_bg_type', '==', 'gradient' ),
            ),
            array(
                'id'         => 'rh_bg_color',
                'type'       => 'color',
                'label'      => __( '背景颜色', 'developer-starter' ),
                'default'    => 'var(--color-neutral-900)',
                'dependency' => array( 'rh_bg_type', '==', 'color' ),
            ),
            array(
                'id'         => 'rh_bg_image',
                'type'       => 'image',
                'label'      => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'rh_bg_type', '==', 'image' ),
            ),
            array(
                'id'         => 'rh_bg_overlay',
                'type'       => 'text',
                'label'      => __( '图片遮罩透明度（0-0.9）', 'developer-starter' ),
                'default'    => '0.42',
                'dependency' => array( 'rh_bg_type', '==', 'image' ),
            ),
            array(
                'id'      => 'rh_style_preset',
                'type'    => 'select',
                'label'   => __( '视觉风格', 'developer-starter' ),
                'options' => array(
                    'premium' => __( '高级光感（推荐）', 'developer-starter' ),
                    'clean'   => __( '简洁商务', 'developer-starter' ),
                    'dark'    => __( '深色质感', 'developer-starter' ),
                ),
                'default' => 'premium',
            ),
            array(
                'id'      => 'rh_enable_motion',
                'type'    => 'switcher',
                'label'   => __( '开启轻量动效', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'         => 'rh_motion_level',
                'type'       => 'select',
                'label'      => __( '动效强度', 'developer-starter' ),
                'options'    => array(
                    'soft' => __( '柔和', 'developer-starter' ),
                    'rich' => __( '增强', 'developer-starter' ),
                ),
                'default'    => 'soft',
                'dependency' => array( 'rh_enable_motion', '==', '1' ),
            ),

            array(
                'id'      => 'rh_badge',
                'type'    => 'text',
                'label'   => __( '顶部标签', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( 'VIP资源站解决方案', 'VIP Resource Site Kit' )
                    : __( 'VIP资源站解决方案', 'developer-starter' ),
            ),
            array(
                'id'      => 'rh_title',
                'type'    => 'textarea',
                'label'   => __( '主标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '打造更有转化力的<br><strong>虚拟资源首页</strong>', 'Build a conversion-focused hero for your digital resource business.' )
                    : __( '打造更有转化力的<br><strong>虚拟资源首页</strong>', 'developer-starter' ),
            ),
            array(
                'id'      => 'rh_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '资源展示、VIP权益、付费引导一次到位', 'Resource showcase, member value and purchase CTAs in one unified hero.' )
                    : __( '资源展示、VIP权益、付费引导一次到位', 'developer-starter' ),
            ),
            array(
                'id'      => 'rh_desc',
                'type'    => 'textarea',
                'label'   => __( '描述文案', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '启灵积分商城负责付费与权限逻辑，这个模块专注资源展示与首页转化氛围。', 'Qiling Shop handles payment and access rules, while this hero focuses on visual storytelling and conversion.' )
                    : __( '启灵积分商城负责付费与权限逻辑，这个模块专注资源展示与首页转化氛围。', 'developer-starter' ),
            ),
            array(
                'id'         => 'rh_visual_slides',
                'type'       => 'repeater',
                'label'      => __( '主视觉图片列表', 'developer-starter' ),
                'add_button' => __( '添加图片', 'developer-starter' ),
                'description'=> __( '每项支持：上传图片 + 可选链接。添加1张显示单图，添加多张自动轮播。', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'      => 'image',
                        'type'    => 'image',
                        'label'   => __( '图片', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'link',
                        'type'    => 'text',
                        'label'   => __( '链接（可选）', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'alt',
                        'type'    => 'text',
                        'label'   => __( '图片说明（可选）', 'developer-starter' ),
                        'default' => '',
                    ),
                ),
            ),
            array(
                'id'         => 'rh_visual_position',
                'type'       => 'select',
                'label'      => __( '主视觉位置', 'developer-starter' ),
                'options'    => array(
                    'right' => __( '右侧', 'developer-starter' ),
                    'left'  => __( '左侧', 'developer-starter' ),
                ),
                'default'    => 'right',
            ),
            array(
                'id'         => 'rh_visual_height',
                'type'       => 'text',
                'label'      => __( '主视觉高度', 'developer-starter' ),
                'default'    => '320px',
            ),
            array(
                'id'         => 'rh_visual_radius',
                'type'       => 'text',
                'label'      => __( '主视觉圆角', 'developer-starter' ),
                'default'    => '18px',
            ),
            array(
                'id'         => 'rh_visual_fit',
                'type'       => 'select',
                'label'      => __( '图片填充方式', 'developer-starter' ),
                'options'    => array(
                    'cover'   => __( '填充裁切（cover）', 'developer-starter' ),
                    'contain' => __( '完整显示（contain）', 'developer-starter' ),
                ),
                'default'    => 'cover',
            ),
            array(
                'id'         => 'rh_visual_shadow',
                'type'       => 'select',
                'label'      => __( '主视觉阴影', 'developer-starter' ),
                'options'    => array(
                    'none' => __( '无', 'developer-starter' ),
                    'soft' => __( '柔和', 'developer-starter' ),
                    'glow' => __( '光晕', 'developer-starter' ),
                ),
                'default'    => 'soft',
            ),
            array(
                'id'         => 'rh_visual_shine',
                'type'       => 'switcher',
                'label'      => __( '主视觉扫光动效', 'developer-starter' ),
                'default'    => '1',
            ),
            array(
                'id'         => 'rh_visual_autoplay',
                'type'       => 'switcher',
                'label'      => __( '主视觉自动轮播', 'developer-starter' ),
                'default'    => '1',
            ),
            array(
                'id'         => 'rh_visual_interval',
                'type'       => 'number',
                'label'      => __( '轮播间隔（秒）', 'developer-starter' ),
                'default'    => '4',
            ),
            array(
                'id'         => 'rh_visual_show_dots',
                'type'       => 'switcher',
                'label'      => __( '显示轮播圆点', 'developer-starter' ),
                'default'    => '1',
            ),
            array(
                'id'         => 'rh_visual_caption',
                'type'       => 'text',
                'label'      => __( '主视觉说明（可选）', 'developer-starter' ),
                'default'    => '',
            ),

            array(
                'id'         => 'rh_benefits',
                'type'       => 'repeater',
                'label'      => __( '权益卖点', 'developer-starter' ),
                'add_button' => __( '添加卖点', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'      => 'text',
                        'type'    => 'text',
                        'label'   => __( '文案', 'developer-starter' ),
                        'default' => __( 'VIP专属资源持续更新', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'         => 'rh_stats',
                'type'       => 'repeater',
                'label'      => __( '数据指标', 'developer-starter' ),
                'add_button' => __( '添加指标', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'      => 'value',
                        'type'    => 'text',
                        'label'   => __( '数值', 'developer-starter' ),
                        'default' => '12,000+',
                    ),
                    array(
                        'id'      => 'label',
                        'type'    => 'text',
                        'label'   => __( '说明', 'developer-starter' ),
                        'default' => __( '资源总量', 'developer-starter' ),
                    ),
                ),
            ),

            array(
                'id'      => 'rh_primary_text',
                'type'    => 'text',
                'label'   => __( '主按钮文字', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '开通VIP', 'Upgrade VIP' )
                    : __( '开通VIP', 'developer-starter' ),
            ),
            array(
                'id'      => 'rh_primary_link',
                'type'    => 'text',
                'label'   => __( '主按钮链接', 'developer-starter' ),
                'default' => '#',
            ),
            array(
                'id'          => 'rh_primary_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '主按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认主按钮样式', 'developer-starter' ),
                'default'     => '',
            ),
            array(
                'id'          => 'rh_primary_btn_text_color',
                'type'        => 'color',
                'label'       => __( '主按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认主按钮样式', 'developer-starter' ),
                'default'     => '',
            ),
            $this->get_button_border_color_field( 'rh_primary_btn_border_color', __( '主按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'          => 'rh_primary_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '主按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认主按钮悬停样式', 'developer-starter' ),
                'default'     => '',
            ),
            array(
                'id'          => 'rh_primary_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '主按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认主按钮悬停样式', 'developer-starter' ),
                'default'     => '',
            ),
            $this->get_button_border_color_field( 'rh_primary_btn_hover_border_color', __( '主按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随主按钮悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id'      => 'rh_secondary_text',
                'type'    => 'text',
                'label'   => __( '次按钮文字', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' )
                    ? developer_starter_get_locale_text( '立即下载资源', 'Browse Resources' )
                    : __( '立即下载资源', 'developer-starter' ),
            ),
            array(
                'id'      => 'rh_secondary_link',
                'type'    => 'text',
                'label'   => __( '次按钮链接', 'developer-starter' ),
                'default' => '#',
            ),
            array(
                'id'          => 'rh_secondary_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '次按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认次按钮样式', 'developer-starter' ),
                'default'     => '',
            ),
            array(
                'id'          => 'rh_secondary_btn_text_color',
                'type'        => 'color',
                'label'       => __( '次按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认次按钮样式', 'developer-starter' ),
                'default'     => '',
            ),
            $this->get_button_border_color_field( 'rh_secondary_btn_border_color', __( '次按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'          => 'rh_secondary_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '次按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认次按钮悬停样式', 'developer-starter' ),
                'default'     => '',
            ),
            array(
                'id'          => 'rh_secondary_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '次按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时使用资源首屏Pro默认次按钮悬停样式', 'developer-starter' ),
                'default'     => '',
            ),
            $this->get_button_border_color_field( 'rh_secondary_btn_hover_border_color', __( '次按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随次按钮悬停背景颜色。', 'developer-starter' ) ),

            array(
                'id'      => 'rh_enable_feed',
                'type'    => 'switcher',
                'label'   => __( '开启右侧资源流', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'rh_feed_direction',
                'type'    => 'select',
                'label'   => __( '右侧资源流方向', 'developer-starter' ),
                'options' => array(
                    'vertical'   => __( '纵向滚动', 'developer-starter' ),
                    'horizontal' => __( '横向滚动', 'developer-starter' ),
                ),
                'default' => 'vertical',
            ),
            array(
                'id'      => 'rh_feed_source',
                'type'    => 'select',
                'label'   => __( '资源来源', 'developer-starter' ),
                'options' => array(
                    'latest'   => __( '最新文章', 'developer-starter' ),
                    'category' => __( '指定分类', 'developer-starter' ),
                    'tag'      => __( '指定标签', 'developer-starter' ),
                ),
                'default' => 'latest',
            ),
            array(
                'id'      => 'rh_feed_categories',
                'type'    => 'text',
                'label'   => __( '分类ID (逗号分隔)', 'developer-starter' ),
                'dependency' => array( 'rh_feed_source', '==', 'category' ),
            ),
            array(
                'id'      => 'rh_feed_tags',
                'type'    => 'text',
                'label'   => __( '标签 (逗号分隔)', 'developer-starter' ),
                'dependency' => array( 'rh_feed_source', '==', 'tag' ),
            ),
            array(
                'id'      => 'rh_feed_filter',
                'type'    => 'select',
                'label'   => __( '内容过滤', 'developer-starter' ),
                'options' => array(
                    'resource_only' => __( '仅资源文章（推荐）', 'developer-starter' ),
                    'all_posts'     => __( '全部文章', 'developer-starter' ),
                ),
                'default' => 'resource_only',
            ),
            array(
                'id'      => 'rh_feed_orderby',
                'type'    => 'select',
                'label'   => __( '排序方式', 'developer-starter' ),
                'options' => array(
                    'date'          => __( '发布日期', 'developer-starter' ),
                    'modified'      => __( '更新时间', 'developer-starter' ),
                    'comment_count' => __( '评论数', 'developer-starter' ),
                    'views'         => __( '浏览量', 'developer-starter' ),
                    'random'        => __( '随机', 'developer-starter' ),
                ),
                'default' => 'date',
            ),
            array(
                'id'      => 'rh_feed_count',
                'type'    => 'number',
                'label'   => __( '资源数量', 'developer-starter' ),
                'default' => '8',
            ),
            array(
                'id'      => 'rh_feed_duration',
                'type'    => 'number',
                'label'   => __( '右侧资源流动画时长（秒）', 'developer-starter' ),
                'default' => '26',
            ),
            array(
                'id'      => 'rh_feed_pause_hover',
                'type'    => 'switcher',
                'label'   => __( '悬停暂停滚动', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'rh_feed_show_excerpt',
                'type'    => 'switcher',
                'label'   => __( '显示摘要', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'rh_feed_excerpt_length',
                'type'    => 'number',
                'label'   => __( '摘要长度', 'developer-starter' ),
                'default' => '36',
            ),
            array(
                'id'      => 'rh_feed_show_price',
                'type'    => 'switcher',
                'label'   => __( '显示价格信息', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'rh_feed_show_download_count',
                'type'    => 'switcher',
                'label'   => __( '显示下载项数量', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'rh_feed_show_date',
                'type'    => 'switcher',
                'label'   => __( '显示日期', 'developer-starter' ),
                'default' => '0',
            ),
            array(
                'id'      => 'rh_enable_bottom_strip',
                'type'    => 'switcher',
                'label'   => __( '开启底部横向资源流', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'rh_strip_duration',
                'type'    => 'number',
                'label'   => __( '底部横向资源流动画时长（秒）', 'developer-starter' ),
                'default' => '34',
            ),
        );
    }

    public function get_demo_data() {
        return array(
            'rh_layout' => 'left_right',
            'rh_height' => '58vh',
            'rh_bg_type' => 'gradient',
            'rh_bg_gradient' => 'linear-gradient(140deg, var(--color-neutral-900) 0%, var(--qiling-color-1e3a8a) 38%, var(--color-primary) 68%, var(--qiling-color-60a5fa) 100%)',
            'rh_bg_overlay' => '0.4',
            'rh_badge' => function_exists( 'developer_starter_get_locale_text' )
                ? developer_starter_get_locale_text( '数字资源变现系统', 'Digital Resource Monetization' )
                : __( '数字资源变现系统', 'developer-starter' ),
            'rh_title' => function_exists( 'developer_starter_get_locale_text' )
                ? developer_starter_get_locale_text( '让你的资源首页，<br><strong>更像专业付费平台</strong>', 'Make your hero look like a premium resource platform.' )
                : __( '让你的资源首页，<br><strong>更像专业付费平台</strong>', 'developer-starter' ),
            'rh_subtitle' => function_exists( 'developer_starter_get_locale_text' )
                ? developer_starter_get_locale_text( '虚拟资源展示 + VIP权益 + 强转化 CTA', 'Resource cards, member value and conversion CTAs in one place.' )
                : __( '虚拟资源展示 + VIP权益 + 强转化 CTA', 'developer-starter' ),
            'rh_desc' => function_exists( 'developer_starter_get_locale_text' )
                ? developer_starter_get_locale_text( '你专注内容更新，首页交给资源首屏Pro。适合软件、模板、素材、课程、文档等虚拟资源站。', 'You focus on content operations while Resource Hero Pro handles your first-screen conversion experience.' )
                : __( '你专注内容更新，首页交给资源首屏Pro。适合软件、模板、素材、课程、文档等虚拟资源站。', 'developer-starter' ),
            'rh_style_preset' => 'premium',
            'rh_enable_motion' => '1',
            'rh_motion_level' => 'soft',
            'rh_visual_slides' => array(
                array(
                    'image' => 'https://picsum.photos/seed/qiling-resource-hero-1/1200/900',
                    'link'  => '#',
                    'alt'   => __( '资源首屏轮播图 1', 'developer-starter' ),
                ),
                array(
                    'image' => 'https://picsum.photos/seed/qiling-resource-hero-2/1200/900',
                    'link'  => '#',
                    'alt'   => __( '资源首屏轮播图 2', 'developer-starter' ),
                ),
                array(
                    'image' => 'https://picsum.photos/seed/qiling-resource-hero-3/1200/900',
                    'link'  => '#',
                    'alt'   => __( '资源首屏轮播图 3', 'developer-starter' ),
                ),
            ),
            'rh_visual_position' => 'right',
            'rh_visual_height' => '320px',
            'rh_visual_radius' => '18px',
            'rh_visual_fit' => 'cover',
            'rh_visual_shadow' => 'soft',
            'rh_visual_shine' => '1',
            'rh_visual_autoplay' => '1',
            'rh_visual_interval' => '4',
            'rh_visual_show_dots' => '1',
            'rh_visual_caption' => '',
            'rh_benefits' => array(
                array( 'text' => __( 'VIP专享资源可视化展示', 'developer-starter' ) ),
                array( 'text' => __( '免费/付费状态一眼区分', 'developer-starter' ) ),
                array( 'text' => __( '与启灵积分商城逻辑无缝配合', 'developer-starter' ) ),
            ),
            'rh_stats' => array(
                array( 'value' => '12,000+', 'label' => __( '资源总量', 'developer-starter' ) ),
                array( 'value' => '4,200+', 'label' => __( '付费会员', 'developer-starter' ) ),
                array( 'value' => '99.9%', 'label' => __( '下载可用率', 'developer-starter' ) ),
            ),
            'rh_primary_text' => function_exists( 'developer_starter_get_locale_text' )
                ? developer_starter_get_locale_text( '开通VIP', 'Upgrade VIP' )
                : __( '开通VIP', 'developer-starter' ),
            'rh_primary_link' => '#',
            'rh_secondary_text' => function_exists( 'developer_starter_get_locale_text' )
                ? developer_starter_get_locale_text( '浏览资源', 'Browse Resources' )
                : __( '浏览资源', 'developer-starter' ),
            'rh_secondary_link' => '#',
            'rh_enable_feed' => '1',
            'rh_feed_direction' => 'vertical',
            'rh_feed_source' => 'latest',
            'rh_feed_filter' => 'resource_only',
            'rh_feed_count' => 8,
            'rh_feed_duration' => 26,
            'rh_feed_pause_hover' => '1',
            'rh_feed_show_excerpt' => '1',
            'rh_feed_excerpt_length' => 36,
            'rh_feed_show_price' => '1',
            'rh_feed_show_download_count' => '1',
            'rh_feed_show_date' => '0',
            'rh_enable_bottom_strip' => '1',
            'rh_strip_duration' => 34,
        );
    }

    public function render( $data = array() ) {
        $layout = isset( $data['rh_layout'] ) ? sanitize_key( $data['rh_layout'] ) : 'left_right';
        if ( ! in_array( $layout, array( 'left_right', 'top_bottom', 'text_only' ), true ) ) {
            $layout = 'left_right';
        }

        $height_mode = isset( $data['rh_height'] ) ? sanitize_key( $data['rh_height'] ) : '58vh';
        $height_value = in_array( $height_mode, array( 'auto', '58vh', '78vh', '88vh', '100vh' ), true )
            ? $height_mode
            : $this->sanitize_length( isset( $data['rh_height_custom'] ) ? $data['rh_height_custom'] : '', '760px' );

        $bg_type = isset( $data['rh_bg_type'] ) ? sanitize_key( $data['rh_bg_type'] ) : 'gradient';
        if ( ! in_array( $bg_type, array( 'gradient', 'color', 'image' ), true ) ) {
            $bg_type = 'gradient';
        }

        $bg_css = '';
        $bg_image = '';
        $bg_overlay = '0';

        if ( $bg_type === 'color' ) {
            $bg_css = $this->sanitize_hex( isset( $data['rh_bg_color'] ) ? $data['rh_bg_color'] : '', 'var(--color-neutral-900)' );
        } elseif ( $bg_type === 'image' ) {
            $bg_image = isset( $data['rh_bg_image'] ) ? esc_url_raw( $data['rh_bg_image'] ) : '';
            $bg_overlay = $this->sanitize_opacity( isset( $data['rh_bg_overlay'] ) ? $data['rh_bg_overlay'] : '0.42', '0.42' );
            $bg_css = 'var(--color-neutral-900)';
        } else {
            $bg_css = $this->sanitize_gradient(
                isset( $data['rh_bg_gradient'] ) ? $data['rh_bg_gradient'] : '',
                'linear-gradient(135deg, var(--color-neutral-900) 0%, var(--color-primary-dark) 45%, var(--qiling-color-60a5fa) 100%)'
            );
        }

        $badge = isset( $data['rh_badge'] ) ? (string) $data['rh_badge'] : '';
        $title = isset( $data['rh_title'] ) ? (string) $data['rh_title'] : '';
        $subtitle = isset( $data['rh_subtitle'] ) ? (string) $data['rh_subtitle'] : '';
        $desc = isset( $data['rh_desc'] ) ? (string) $data['rh_desc'] : '';
        $style_preset = isset( $data['rh_style_preset'] ) ? sanitize_key( (string) $data['rh_style_preset'] ) : 'premium';
        if ( ! in_array( $style_preset, array( 'premium', 'clean', 'dark' ), true ) ) {
            $style_preset = 'premium';
        }
        $enable_motion = isset( $data['rh_enable_motion'] ) ? (string) $data['rh_enable_motion'] : '1';
        $motion_level = isset( $data['rh_motion_level'] ) ? sanitize_key( (string) $data['rh_motion_level'] ) : 'soft';
        if ( ! in_array( $motion_level, array( 'soft', 'rich' ), true ) ) {
            $motion_level = 'soft';
        }
        $visual_image = isset( $data['rh_visual_image'] ) ? esc_url_raw( (string) $data['rh_visual_image'] ) : '';
        $visual_slides_raw = ( isset( $data['rh_visual_slides'] ) && is_array( $data['rh_visual_slides'] ) ) ? $data['rh_visual_slides'] : array();
        $visual_gallery = isset( $data['rh_visual_gallery'] ) ? (string) $data['rh_visual_gallery'] : '';
        $visual_images_raw = ( isset( $data['rh_visual_images'] ) && is_array( $data['rh_visual_images'] ) ) ? $data['rh_visual_images'] : array();
        // 兼容旧数据：曾存在全局主视觉链接字段，现已从配置面板移除。
        $visual_link = isset( $data['rh_visual_link'] ) ? esc_url( (string) $data['rh_visual_link'] ) : '';
        $visual_position = isset( $data['rh_visual_position'] ) ? sanitize_key( (string) $data['rh_visual_position'] ) : 'right';
        if ( ! in_array( $visual_position, array( 'left', 'right' ), true ) ) {
            $visual_position = 'right';
        }
        $visual_height = $this->sanitize_length( isset( $data['rh_visual_height'] ) ? $data['rh_visual_height'] : '', '320px' );
        $visual_radius = $this->sanitize_length( isset( $data['rh_visual_radius'] ) ? $data['rh_visual_radius'] : '', '18px' );
        $visual_fit = isset( $data['rh_visual_fit'] ) ? sanitize_key( (string) $data['rh_visual_fit'] ) : 'cover';
        if ( ! in_array( $visual_fit, array( 'cover', 'contain' ), true ) ) {
            $visual_fit = 'cover';
        }
        $visual_shadow = isset( $data['rh_visual_shadow'] ) ? sanitize_key( (string) $data['rh_visual_shadow'] ) : 'soft';
        if ( ! in_array( $visual_shadow, array( 'none', 'soft', 'glow' ), true ) ) {
            $visual_shadow = 'soft';
        }
        $visual_shine = isset( $data['rh_visual_shine'] ) ? (string) $data['rh_visual_shine'] : '1';
        $visual_autoplay = isset( $data['rh_visual_autoplay'] ) ? (string) $data['rh_visual_autoplay'] : '1';
        $visual_interval = isset( $data['rh_visual_interval'] ) ? max( 2, min( 20, absint( $data['rh_visual_interval'] ) ) ) : 4;
        $visual_show_dots = isset( $data['rh_visual_show_dots'] ) ? (string) $data['rh_visual_show_dots'] : '1';
        $visual_caption = isset( $data['rh_visual_caption'] ) ? trim( (string) $data['rh_visual_caption'] ) : '';

        $visual_images = array();

        // 1) 新版多图列表：每项图片 + 可选链接。
        foreach ( $visual_slides_raw as $slide ) {
            if ( ! is_array( $slide ) ) {
                continue;
            }
            $slide_url = isset( $slide['image'] ) ? esc_url_raw( trim( (string) $slide['image'] ) ) : '';
            if ( $slide_url === '' ) {
                continue;
            }
            $slide_alt = isset( $slide['alt'] ) ? trim( wp_strip_all_tags( (string) $slide['alt'] ) ) : '';
            $slide_link = isset( $slide['link'] ) ? esc_url( (string) $slide['link'] ) : '';
            $visual_images[] = array(
                'url'  => $slide_url,
                'alt'  => $slide_alt,
                'link' => $slide_link,
            );
        }

        // 2) 兼容 gallery（逗号 URL）旧结构。
        if ( empty( $visual_images ) && $visual_gallery !== '' ) {
            $gallery_urls = array_map( 'trim', explode( ',', $visual_gallery ) );
            foreach ( $gallery_urls as $gallery_url ) {
                $gallery_url = esc_url_raw( $gallery_url );
                if ( $gallery_url === '' ) {
                    continue;
                }
                $visual_images[] = array(
                    'url'  => $gallery_url,
                    'alt'  => '',
                    'link' => '',
                );
            }
        }

        // 3) 兼容此前 repeater 结构。
        if ( empty( $visual_images ) ) {
            foreach ( $visual_images_raw as $slide ) {
                if ( ! is_array( $slide ) ) {
                    continue;
                }
                $slide_url = isset( $slide['image'] ) ? esc_url_raw( trim( (string) $slide['image'] ) ) : '';
                if ( $slide_url === '' ) {
                    continue;
                }
                $slide_alt = isset( $slide['alt'] ) ? trim( wp_strip_all_tags( (string) $slide['alt'] ) ) : '';
                $visual_images[] = array(
                    'url'  => $slide_url,
                    'alt'  => $slide_alt,
                    'link' => '',
                );
            }
        }

        // 向后兼容：如果未配置轮播图，继续使用旧的单图字段。
        if ( empty( $visual_images ) && $visual_image !== '' ) {
            $visual_images[] = array(
                'url'  => $visual_image,
                'alt'  => trim( wp_strip_all_tags( $title ) ),
                'link' => '',
            );
        }

        $visual_slider_enabled = count( $visual_images ) > 1;
        $visual_slider_autoplay = ( $visual_autoplay === '1' && $visual_slider_enabled ) ? '1' : '0';
        $visual_interval_ms = $visual_interval * 1000;
        $has_visual = ( ! empty( $visual_images ) && $layout !== 'text_only' );

        $benefits = isset( $data['rh_benefits'] ) && is_array( $data['rh_benefits'] ) ? $data['rh_benefits'] : array();
        $stats = isset( $data['rh_stats'] ) && is_array( $data['rh_stats'] ) ? $data['rh_stats'] : array();

        $primary_text = isset( $data['rh_primary_text'] ) ? trim( (string) $data['rh_primary_text'] ) : '';
        $primary_link = isset( $data['rh_primary_link'] ) ? esc_url( (string) $data['rh_primary_link'] ) : '#';
        $primary_btn_bg_color = $this->sanitize_color_value( isset( $data['rh_primary_btn_bg_color'] ) ? $data['rh_primary_btn_bg_color'] : '', '' );
        $primary_btn_text_color = $this->sanitize_color_value( isset( $data['rh_primary_btn_text_color'] ) ? $data['rh_primary_btn_text_color'] : '', '' );
        $primary_btn_border_color = $this->sanitize_color_value( isset( $data['rh_primary_btn_border_color'] ) ? $data['rh_primary_btn_border_color'] : '', '' );
        $primary_btn_hover_bg_color = $this->sanitize_color_value( isset( $data['rh_primary_btn_hover_bg_color'] ) ? $data['rh_primary_btn_hover_bg_color'] : '', '' );
        $primary_btn_hover_text_color = $this->sanitize_color_value( isset( $data['rh_primary_btn_hover_text_color'] ) ? $data['rh_primary_btn_hover_text_color'] : '', '' );
        $primary_btn_hover_border_color = $this->sanitize_color_value( isset( $data['rh_primary_btn_hover_border_color'] ) ? $data['rh_primary_btn_hover_border_color'] : '', '' );
        $secondary_text = isset( $data['rh_secondary_text'] ) ? trim( (string) $data['rh_secondary_text'] ) : '';
        $secondary_link = isset( $data['rh_secondary_link'] ) ? esc_url( (string) $data['rh_secondary_link'] ) : '#';
        $secondary_btn_bg_color = $this->sanitize_color_value( isset( $data['rh_secondary_btn_bg_color'] ) ? $data['rh_secondary_btn_bg_color'] : '', '' );
        $secondary_btn_text_color = $this->sanitize_color_value( isset( $data['rh_secondary_btn_text_color'] ) ? $data['rh_secondary_btn_text_color'] : '', '' );
        $secondary_btn_border_color = $this->sanitize_color_value( isset( $data['rh_secondary_btn_border_color'] ) ? $data['rh_secondary_btn_border_color'] : '', '' );
        $secondary_btn_hover_bg_color = $this->sanitize_color_value( isset( $data['rh_secondary_btn_hover_bg_color'] ) ? $data['rh_secondary_btn_hover_bg_color'] : '', '' );
        $secondary_btn_hover_text_color = $this->sanitize_color_value( isset( $data['rh_secondary_btn_hover_text_color'] ) ? $data['rh_secondary_btn_hover_text_color'] : '', '' );
        $secondary_btn_hover_border_color = $this->sanitize_color_value( isset( $data['rh_secondary_btn_hover_border_color'] ) ? $data['rh_secondary_btn_hover_border_color'] : '', '' );

        $enable_feed = isset( $data['rh_enable_feed'] ) ? (string) $data['rh_enable_feed'] : '1';
        $feed_direction = isset( $data['rh_feed_direction'] ) ? sanitize_key( $data['rh_feed_direction'] ) : 'vertical';
        if ( ! in_array( $feed_direction, array( 'vertical', 'horizontal' ), true ) ) {
            $feed_direction = 'vertical';
        }

        $feed_source = isset( $data['rh_feed_source'] ) ? sanitize_key( $data['rh_feed_source'] ) : 'latest';
        $feed_filter = isset( $data['rh_feed_filter'] ) ? sanitize_key( $data['rh_feed_filter'] ) : 'resource_only';
        $feed_orderby = isset( $data['rh_feed_orderby'] ) ? sanitize_key( $data['rh_feed_orderby'] ) : 'date';
        $feed_count = isset( $data['rh_feed_count'] ) ? max( 3, min( 18, absint( $data['rh_feed_count'] ) ) ) : 8;
        $feed_duration = isset( $data['rh_feed_duration'] ) ? max( 8, min( 120, absint( $data['rh_feed_duration'] ) ) ) : 26;
        $feed_pause_hover = isset( $data['rh_feed_pause_hover'] ) ? (string) $data['rh_feed_pause_hover'] : '1';
        $feed_show_excerpt = isset( $data['rh_feed_show_excerpt'] ) ? (string) $data['rh_feed_show_excerpt'] : '1';
        $feed_excerpt_length = isset( $data['rh_feed_excerpt_length'] ) ? max( 8, min( 120, absint( $data['rh_feed_excerpt_length'] ) ) ) : 36;
        $feed_show_price = isset( $data['rh_feed_show_price'] ) ? (string) $data['rh_feed_show_price'] : '1';
        $feed_show_download_count = isset( $data['rh_feed_show_download_count'] ) ? (string) $data['rh_feed_show_download_count'] : '1';
        $feed_show_date = isset( $data['rh_feed_show_date'] ) ? (string) $data['rh_feed_show_date'] : '0';

        $enable_bottom_strip = isset( $data['rh_enable_bottom_strip'] ) ? (string) $data['rh_enable_bottom_strip'] : '1';
        $strip_duration = isset( $data['rh_strip_duration'] ) ? max( 10, min( 140, absint( $data['rh_strip_duration'] ) ) ) : 34;

        $items = $this->query_resource_posts( array(
            'source'      => $feed_source,
            'categories'  => isset( $data['rh_feed_categories'] ) ? (string) $data['rh_feed_categories'] : '',
            'tags'        => isset( $data['rh_feed_tags'] ) ? (string) $data['rh_feed_tags'] : '',
            'filter'      => $feed_filter,
            'orderby'     => $feed_orderby,
            'count'       => $feed_count,
        ) );

        $module_id = 'resource-hero-pro-' . uniqid();
        $section_classes = array( 'module', 'module-resource-hero-pro', 'layout-' . $layout );
        $section_classes[] = 'rhp-skin-' . $style_preset;
        if ( $enable_motion === '1' ) {
            $section_classes[] = 'rhp-motion-on';
            $section_classes[] = 'rhp-motion-' . $motion_level;
        } else {
            $section_classes[] = 'rhp-motion-off';
        }
        if ( $enable_feed !== '1' || $layout === 'text_only' ) {
            $section_classes[] = 'feed-disabled';
        }
        if ( $has_visual ) {
            $section_classes[] = 'has-visual';
            $section_classes[] = 'visual-' . $visual_position;
            $section_classes[] = 'visual-shadow-' . $visual_shadow;
            if ( $visual_shine === '1' ) {
                $section_classes[] = 'rhp-visual-shine';
            }
        }

        $section_style = '--rhp-height:' . esc_attr( $height_value ) . ';';
        $section_style .= '--rhp-feed-duration:' . esc_attr( $feed_duration ) . 's;';
        $section_style .= '--rhp-strip-duration:' . esc_attr( $strip_duration ) . 's;';
        $section_style .= '--rhp-bg:' . esc_attr( $bg_css ) . ';';

        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= '--rhp-bg-image:url(' . esc_url( $bg_image ) . ');';
            $section_style .= '--rhp-bg-overlay:' . esc_attr( $bg_overlay ) . ';';
        }
        if ( $primary_btn_bg_color !== '' ) {
            $section_style .= '--rhp-primary-btn-bg:' . esc_attr( $primary_btn_bg_color ) . ';';
            $section_style .= '--rhp-primary-btn-border:' . esc_attr( $primary_btn_bg_color ) . ';';
        }
        if ( $primary_btn_text_color !== '' ) {
            $section_style .= '--rhp-primary-btn-text:' . esc_attr( $primary_btn_text_color ) . ';';
        }
        if ( $primary_btn_border_color !== '' ) {
            $section_style .= '--rhp-primary-btn-border:' . esc_attr( $primary_btn_border_color ) . ';';
        }
        if ( $primary_btn_hover_bg_color !== '' ) {
            $section_style .= '--rhp-primary-btn-hover-bg:' . esc_attr( $primary_btn_hover_bg_color ) . ';';
            $section_style .= '--rhp-primary-btn-hover-border:' . esc_attr( $primary_btn_hover_bg_color ) . ';';
        }
        if ( $primary_btn_hover_text_color !== '' ) {
            $section_style .= '--rhp-primary-btn-hover-text:' . esc_attr( $primary_btn_hover_text_color ) . ';';
        }
        if ( $primary_btn_hover_border_color !== '' ) {
            $section_style .= '--rhp-primary-btn-hover-border:' . esc_attr( $primary_btn_hover_border_color ) . ';';
        }
        if ( $secondary_btn_bg_color !== '' ) {
            $section_style .= '--rhp-secondary-btn-bg:' . esc_attr( $secondary_btn_bg_color ) . ';';
            $section_style .= '--rhp-secondary-btn-border:' . esc_attr( $secondary_btn_bg_color ) . ';';
        }
        if ( $secondary_btn_text_color !== '' ) {
            $section_style .= '--rhp-secondary-btn-text:' . esc_attr( $secondary_btn_text_color ) . ';';
        }
        if ( $secondary_btn_border_color !== '' ) {
            $section_style .= '--rhp-secondary-btn-border:' . esc_attr( $secondary_btn_border_color ) . ';';
        }
        if ( $secondary_btn_hover_bg_color !== '' ) {
            $section_style .= '--rhp-secondary-btn-hover-bg:' . esc_attr( $secondary_btn_hover_bg_color ) . ';';
            $section_style .= '--rhp-secondary-btn-hover-border:' . esc_attr( $secondary_btn_hover_bg_color ) . ';';
        }
        if ( $secondary_btn_hover_text_color !== '' ) {
            $section_style .= '--rhp-secondary-btn-hover-text:' . esc_attr( $secondary_btn_hover_text_color ) . ';';
        }
        if ( $secondary_btn_hover_border_color !== '' ) {
            $section_style .= '--rhp-secondary-btn-hover-border:' . esc_attr( $secondary_btn_hover_border_color ) . ';';
        }

        $safe_title = wp_kses( $title, array( 'br' => array(), 'strong' => array(), 'span' => array( 'style' => true ), 'em' => array() ) );
        $safe_subtitle = wp_kses_post( $subtitle );
        $safe_desc = wp_kses_post( $desc );
        $safe_visual_caption = wp_kses_post( $visual_caption );

        $duplicated_items = ! empty( $items ) ? array_merge( $items, $items ) : array();
        $feed_box_style = ( $feed_direction === 'horizontal' )
            ? 'min-height:0;max-height:none;height:auto;'
            : 'min-height:320px;max-height:560px;';
        $feed_track_style = ( $feed_direction === 'horizontal' ) ? 'align-items:flex-start;' : '';
        $visual_box_style = '--rhp-visual-height:' . esc_attr( $visual_height ) . ';--rhp-visual-radius:' . esc_attr( $visual_radius ) . ';';
        $content_wrap_classes = array( 'rhp-content-wrap' );
        if ( $has_visual ) {
            $content_wrap_classes[] = 'has-visual';
            $content_wrap_classes[] = 'visual-' . $visual_position;
        }
        $show_intro_text = ( ! $has_visual || $layout === 'top_bottom' );
        $visual_before_content = ( $has_visual && $layout !== 'top_bottom' );
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="rhp-bg-layer"></div>
            <div class="container">
                <div class="rhp-main rhp-feed-<?php echo esc_attr( $feed_direction ); ?>">
                    <div class="<?php echo esc_attr( implode( ' ', $content_wrap_classes ) ); ?>">
                    <?php if ( $visual_before_content ) : ?>
                        <?php
                        $this->render_visual_block( array(
                            'visual_shadow'      => $visual_shadow,
                            'visual_link'        => $visual_link,
                            'visual_box_style'   => $visual_box_style,
                            'visual_images'      => $visual_images,
                            'visual_fit'         => $visual_fit,
                            'visual_caption'     => $safe_visual_caption,
                            'slider_enabled'     => $visual_slider_enabled,
                            'slider_autoplay'    => $visual_slider_autoplay,
                            'slider_interval_ms' => $visual_interval_ms,
                            'show_dots'          => $visual_show_dots,
                        ) );
                        ?>
                    <?php endif; ?>

                    <div class="rhp-content">
                        <?php if ( $show_intro_text ) : ?>
                            <?php if ( $badge !== '' ) : ?>
                                <div class="rhp-badge"><?php echo esc_html( $badge ); ?></div>
                            <?php endif; ?>

                            <?php if ( $safe_title !== '' ) : ?>
                                <h2 class="rhp-title"><?php echo $safe_title; ?></h2>
                            <?php endif; ?>

                            <?php if ( $safe_subtitle !== '' ) : ?>
                                <p class="rhp-subtitle"><?php echo $safe_subtitle; ?></p>
                            <?php endif; ?>

                            <?php if ( $safe_desc !== '' ) : ?>
                                <div class="rhp-desc"><?php echo $safe_desc; ?></div>
                            <?php endif; ?>

                            <?php if ( ! empty( $benefits ) ) : ?>
                                <ul class="rhp-benefits" role="list">
                                    <?php foreach ( $benefits as $item ) :
                                        $text = isset( $item['text'] ) ? trim( (string) $item['text'] ) : '';
                                        if ( $text === '' ) {
                                            continue;
                                        }
                                        ?>
                                        <li><?php echo esc_html( $text ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ( ! empty( $stats ) ) : ?>
                            <div class="rhp-stats">
                                <?php foreach ( $stats as $item ) :
                                    $value = isset( $item['value'] ) ? trim( (string) $item['value'] ) : '';
                                    $label = isset( $item['label'] ) ? trim( (string) $item['label'] ) : '';
                                    if ( $value === '' && $label === '' ) {
                                        continue;
                                    }
                                    ?>
                                    <div class="rhp-stat">
                                        <?php if ( $value !== '' ) : ?><strong><?php echo esc_html( $value ); ?></strong><?php endif; ?>
                                        <?php if ( $label !== '' ) : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="rhp-actions">
                            <?php if ( $primary_text !== '' ) : ?>
                                <a href="<?php echo esc_url( $primary_link ); ?>" class="rhp-btn rhp-btn-primary"><?php echo esc_html( $primary_text ); ?></a>
                            <?php endif; ?>
                            <?php if ( $secondary_text !== '' ) : ?>
                                <a href="<?php echo esc_url( $secondary_link ); ?>" class="rhp-btn rhp-btn-secondary"><?php echo esc_html( $secondary_text ); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ( $has_visual && ! $visual_before_content ) : ?>
                        <?php
                        $this->render_visual_block( array(
                            'visual_shadow'      => $visual_shadow,
                            'visual_link'        => $visual_link,
                            'visual_box_style'   => $visual_box_style,
                            'visual_images'      => $visual_images,
                            'visual_fit'         => $visual_fit,
                            'visual_caption'     => $safe_visual_caption,
                            'slider_enabled'     => $visual_slider_enabled,
                            'slider_autoplay'    => $visual_slider_autoplay,
                            'slider_interval_ms' => $visual_interval_ms,
                            'show_dots'          => $visual_show_dots,
                        ) );
                        ?>
                    <?php endif; ?>
                    </div>

                    <?php if ( $enable_feed === '1' && $layout !== 'text_only' ) : ?>
                        <div class="rhp-feed<?php echo $feed_pause_hover === '1' ? ' can-pause' : ''; ?>" style="<?php echo esc_attr( $feed_box_style ); ?>">
                            <?php if ( ! empty( $duplicated_items ) ) : ?>
                                <div class="rhp-feed-track" style="<?php echo esc_attr( $feed_track_style ); ?>">
                                    <?php foreach ( $duplicated_items as $item ) : ?>
                                        <?php $this->render_feed_card( $item, $feed_show_excerpt === '1', $feed_excerpt_length, $feed_show_price === '1', $feed_show_download_count === '1', $feed_show_date === '1' ); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="rhp-feed-empty"><?php esc_html_e( '暂无可展示的资源文章', 'developer-starter' ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ( $enable_bottom_strip === '1' && ! empty( $duplicated_items ) ) : ?>
                    <div class="rhp-strip<?php echo $feed_pause_hover === '1' ? ' can-pause' : ''; ?>">
                        <div class="rhp-strip-track">
                            <?php foreach ( $duplicated_items as $item ) :
                                $resource = isset( $item['resource'] ) && is_array( $item['resource'] ) ? $item['resource'] : array();
                                $price_text = isset( $resource['price_text'] ) ? (string) $resource['price_text'] : '';
                                ?>
                                <a class="rhp-strip-item" href="<?php echo esc_url( $item['url'] ); ?>">
                                    <span class="rhp-strip-title"><?php echo esc_html( $item['title'] ); ?></span>
                                    <?php if ( $price_text !== '' ) : ?>
                                        <span class="rhp-strip-price"><?php echo esc_html( $price_text ); ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php if ( $has_visual && $visual_slider_enabled ) : ?>
            <script>
            (function() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root) { return; }
                var sliders = root.querySelectorAll('.rhp-visual-slider');
                if (!sliders.length) { return; }

                sliders.forEach(function(slider) {
                    var slides = slider.querySelectorAll('.rhp-visual-slide');
                    if (!slides.length || slides.length < 2) { return; }

                    var dots = slider.querySelectorAll('.rhp-visual-dot');
                    var current = 0;
                    var autoplay = slider.getAttribute('data-autoplay') === '1';
                    var interval = parseInt(slider.getAttribute('data-interval') || '4000', 10);
                    if (isNaN(interval)) { interval = 4000; }
                    interval = Math.max(2000, Math.min(20000, interval));
                    var timer = null;

                    var setActive = function(nextIndex) {
                        current = (nextIndex + slides.length) % slides.length;
                        slides.forEach(function(slide, index) {
                            slide.classList.toggle('is-active', index === current);
                        });
                        dots.forEach(function(dot, index) {
                            dot.classList.toggle('is-active', index === current);
                            dot.setAttribute('aria-current', index === current ? 'true' : 'false');
                        });
                    };

                    var stop = function() {
                        if (timer) {
                            clearInterval(timer);
                            timer = null;
                        }
                    };

                    var start = function() {
                        if (!autoplay || !root.isConnected) { stop(); return; }
                        stop();
                        timer = setInterval(function() {
                            if (!root.isConnected) { stop(); return; }
                            setActive(current + 1);
                        }, interval);
                    };

                    dots.forEach(function(dot) {
                        dot.addEventListener('click', function() {
                            var target = parseInt(dot.getAttribute('data-index') || '0', 10);
                            if (isNaN(target)) { target = 0; }
                            setActive(target);
                            start();
                        });
                    });

                    slider.addEventListener('mouseenter', stop);
                    slider.addEventListener('mouseleave', start);
                    slider.addEventListener('touchstart', stop, { passive: true });
                    slider.addEventListener('touchend', start, { passive: true });

                    var handleVisibilityChange = function() {
                        if (!root.isConnected) {
                            stop();
                            document.removeEventListener('visibilitychange', handleVisibilityChange);
                            return;
                        }
                        if (document.hidden) {
                            stop();
                        } else {
                            start();
                        }
                    };
                    document.addEventListener('visibilitychange', handleVisibilityChange);

                    setActive(0);
                    start();
                });
            })();
            </script>
        <?php endif; ?>
        <?php
    }

    private function render_visual_block( $args ) {
        $visual_shadow = isset( $args['visual_shadow'] ) ? (string) $args['visual_shadow'] : 'soft';
        $visual_link = isset( $args['visual_link'] ) ? (string) $args['visual_link'] : '';
        $visual_box_style = isset( $args['visual_box_style'] ) ? (string) $args['visual_box_style'] : '';
        $visual_images = isset( $args['visual_images'] ) && is_array( $args['visual_images'] ) ? $args['visual_images'] : array();
        $visual_fit = isset( $args['visual_fit'] ) ? (string) $args['visual_fit'] : 'cover';
        $visual_caption = isset( $args['visual_caption'] ) ? (string) $args['visual_caption'] : '';
        $slider_enabled = ! empty( $args['slider_enabled'] );
        $slider_autoplay = isset( $args['slider_autoplay'] ) ? (string) $args['slider_autoplay'] : '0';
        $slider_interval_ms = isset( $args['slider_interval_ms'] ) ? absint( $args['slider_interval_ms'] ) : 4000;
        $show_dots = isset( $args['show_dots'] ) ? (string) $args['show_dots'] : '1';

        if ( empty( $visual_images ) ) {
            return;
        }
        ?>
        <div class="rhp-visual-col rhp-visual-shadow-<?php echo esc_attr( $visual_shadow ); ?>">
            <div class="rhp-visual-wrap<?php echo $slider_enabled ? ' has-slider' : ''; ?>" style="<?php echo esc_attr( $visual_box_style ); ?>">
                <?php if ( $slider_enabled ) : ?>
                    <div class="rhp-visual-slider" data-autoplay="<?php echo esc_attr( $slider_autoplay ); ?>" data-interval="<?php echo esc_attr( $slider_interval_ms ); ?>">
                        <div class="rhp-visual-slides">
                            <?php foreach ( $visual_images as $index => $slide ) :
                                $slide_url = isset( $slide['url'] ) ? (string) $slide['url'] : '';
                                if ( $slide_url === '' ) {
                                    continue;
                                }
                                $slide_alt = isset( $slide['alt'] ) ? trim( (string) $slide['alt'] ) : '';
                                $slide_link = isset( $slide['link'] ) ? esc_url( (string) $slide['link'] ) : '';
                                if ( $slide_link === '' ) {
                                    $slide_link = $visual_link;
                                }
                                if ( $slide_alt === '' ) {
                                    $slide_alt = __( '主视觉轮播图', 'developer-starter' );
                                }
                                ?>
                                <div class="rhp-visual-slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( (string) $index ); ?>">
                                    <?php if ( $slide_link !== '' ) : ?>
                                        <a class="rhp-visual-slide-inner" href="<?php echo esc_url( $slide_link ); ?>">
                                            <img src="<?php echo esc_url( $slide_url ); ?>" alt="<?php echo esc_attr( $slide_alt ); ?>" loading="lazy" decoding="async" style="object-fit:<?php echo esc_attr( $visual_fit ); ?>;" />
                                        </a>
                                    <?php else : ?>
                                        <span class="rhp-visual-slide-inner">
                                            <img src="<?php echo esc_url( $slide_url ); ?>" alt="<?php echo esc_attr( $slide_alt ); ?>" loading="lazy" decoding="async" style="object-fit:<?php echo esc_attr( $visual_fit ); ?>;" />
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ( $show_dots === '1' ) : ?>
                            <div class="rhp-visual-dots">
                                <?php foreach ( $visual_images as $index => $slide ) : ?>
                                    <button type="button" class="rhp-visual-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '切换到第 %d 张图片', 'developer-starter' ), $index + 1 ) ); ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else :
                    $slide = $visual_images[0];
                    $slide_url = isset( $slide['url'] ) ? (string) $slide['url'] : '';
                    $slide_alt = isset( $slide['alt'] ) ? trim( (string) $slide['alt'] ) : '';
                    $slide_link = isset( $slide['link'] ) ? esc_url( (string) $slide['link'] ) : '';
                    if ( $slide_link === '' ) {
                        $slide_link = $visual_link;
                    }
                    if ( $slide_alt === '' ) {
                        $slide_alt = __( '主视觉图片', 'developer-starter' );
                    }
                    ?>
                    <?php if ( $slide_link !== '' ) : ?>
                        <a class="rhp-visual-slide-inner" href="<?php echo esc_url( $slide_link ); ?>">
                            <img src="<?php echo esc_url( $slide_url ); ?>" alt="<?php echo esc_attr( $slide_alt ); ?>" loading="lazy" decoding="async" style="object-fit:<?php echo esc_attr( $visual_fit ); ?>;" />
                        </a>
                    <?php else : ?>
                        <span class="rhp-visual-slide-inner">
                            <img src="<?php echo esc_url( $slide_url ); ?>" alt="<?php echo esc_attr( $slide_alt ); ?>" loading="lazy" decoding="async" style="object-fit:<?php echo esc_attr( $visual_fit ); ?>;" />
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ( $visual_caption !== '' ) : ?>
                <div class="rhp-visual-caption"><?php echo $visual_caption; ?></div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_feed_card( $item, $show_excerpt, $excerpt_length, $show_price, $show_download_count, $show_date ) {
        $resource = isset( $item['resource'] ) && is_array( $item['resource'] ) ? $item['resource'] : array();
        $badge_text = isset( $resource['badge_text'] ) ? (string) $resource['badge_text'] : '';
        $badge_class = isset( $resource['badge_class'] ) ? (string) $resource['badge_class'] : '';
        $price_text = isset( $resource['price_text'] ) ? (string) $resource['price_text'] : '';
        $download_count = isset( $resource['download_count'] ) ? (int) $resource['download_count'] : 0;
        $date = isset( $item['date'] ) ? (string) $item['date'] : '';
        $has_thumb = ! empty( $item['thumb'] );
        ?>
        <article class="rhp-card<?php echo $has_thumb ? '' : ' rhp-card-no-thumb'; ?>" style="min-height:0;height:auto;">
            <?php if ( $has_thumb ) : ?>
                <a class="rhp-card-thumb" href="<?php echo esc_url( $item['url'] ); ?>" aria-label="<?php echo esc_attr( $item['title'] ); ?>">
                    <img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" decoding="async" />
                </a>
            <?php else : ?>
                <span class="rhp-card-thumb rhp-card-thumb-placeholder" aria-hidden="true"></span>
            <?php endif; ?>

            <div class="rhp-card-content">
                <div class="rhp-card-top">
                    <?php if ( $badge_text !== '' ) : ?>
                        <span class="rhp-pill <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
                    <?php endif; ?>
                    <?php if ( $show_date && $date !== '' ) : ?>
                        <span class="rhp-date"><?php echo esc_html( $date ); ?></span>
                    <?php endif; ?>
                </div>

                <h3 class="rhp-card-title">
                    <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                </h3>

                <?php if ( $show_excerpt && ! empty( $item['excerpt'] ) ) : ?>
                    <p class="rhp-card-excerpt"><?php echo esc_html( wp_trim_words( $item['excerpt'], $excerpt_length ) ); ?></p>
                <?php endif; ?>

                <?php if ( $show_price || $show_download_count ) : ?>
                    <div class="rhp-card-bottom">
                        <?php if ( $show_price && $price_text !== '' ) : ?>
                            <span class="rhp-price"><?php echo esc_html( $price_text ); ?></span>
                        <?php endif; ?>
                        <?php if ( $show_download_count && $download_count > 0 ) : ?>
                            <span class="rhp-download-count"><?php echo esc_html( sprintf( _n( '%d 项资源', '%d 项资源', $download_count, 'developer-starter' ), $download_count ) ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }

    /**
     * 查询并组装资源文章数据。
     *
     * @param array $options 查询参数。
     * @return array<int, array<string, mixed>>
     */
    private function query_resource_posts( $options ) {
        $count = isset( $options['count'] ) ? max( 3, min( 18, absint( $options['count'] ) ) ) : 8;
        $source = isset( $options['source'] ) ? sanitize_key( $options['source'] ) : 'latest';
        $filter = isset( $options['filter'] ) ? sanitize_key( $options['filter'] ) : 'resource_only';
        $orderby = isset( $options['orderby'] ) ? sanitize_key( $options['orderby'] ) : 'date';

        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $count,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        );

        if ( $source === 'category' && ! empty( $options['categories'] ) ) {
            $cat_ids = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', (string) $options['categories'] ) ) ) );
            if ( ! empty( $cat_ids ) ) {
                $args['category__in'] = $cat_ids;
            }
        } elseif ( $source === 'tag' && ! empty( $options['tags'] ) ) {
            $tag_list = array_map( 'trim', explode( ',', (string) $options['tags'] ) );
            $tag_list = array_filter( $tag_list );
            if ( ! empty( $tag_list ) ) {
                if ( is_numeric( reset( $tag_list ) ) ) {
                    $args['tag__in'] = array_map( 'intval', $tag_list );
                } else {
                    $args['tag_slug__in'] = $tag_list;
                }
            }
        }

        switch ( $orderby ) {
            case 'modified':
                $args['orderby'] = 'modified';
                $args['order'] = 'DESC';
                break;
            case 'comment_count':
                $args['orderby'] = 'comment_count';
                break;
            case 'views':
                $args['meta_key'] = 'ds_post_views_count';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'random':
                $args['orderby'] = 'rand';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        // 避免资源筛选使用重 OR meta_query 造成大站点慢查询，改为单次宽查询后在 PHP 侧轻量过滤。
        if ( $filter === 'resource_only' ) {
            $items = array();
            $seen = array();
            $batch_size = max( 24, min( 120, $count * 4 ) );
            $batch_args = $args;
            $batch_args['posts_per_page'] = $batch_size;

            if ( function_exists( '\\developer_starter_run_cached_query' ) ) {
                $query = \developer_starter_run_cached_query( $batch_args, 'module_resource_hero_pro' );
            } else {
                $query = new \WP_Query( $batch_args );
            }

            if ( $query instanceof \WP_Query && $query->have_posts() ) {
                foreach ( (array) $query->posts as $post_obj ) {
                    $post_id = ( $post_obj instanceof \WP_Post ) ? (int) $post_obj->ID : (int) $post_obj;
                    if ( $post_id <= 0 || isset( $seen[ $post_id ] ) ) {
                        continue;
                    }

                    $seen[ $post_id ] = true;
                    $item = $this->build_post_item( $post_id );
                    if ( empty( $item['resource']['has_resource'] ) ) {
                        continue;
                    }

                    $items[] = $item;
                    if ( count( $items ) >= $count ) {
                        break;
                    }
                }
            }

            return $items;
        }

        if ( function_exists( '\\developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query( $args, 'module_resource_hero_pro' );
        } else {
            $query = new \WP_Query( $args );
        }

        $items = array();

        if ( $query instanceof \WP_Query && $query->have_posts() ) {
            foreach ( (array) $query->posts as $post_obj ) {
                $post_id = ( $post_obj instanceof \WP_Post ) ? (int) $post_obj->ID : (int) $post_obj;
                if ( $post_id <= 0 ) {
                    continue;
                }

                $items[] = $this->build_post_item( $post_id );
            }
        }

        return $items;
    }

    /**
     * 组装文章卡片数据，供资源流与底部条复用。
     *
     * @param int $post_id 文章ID。
     * @return array<string, mixed>
     */
    private function build_post_item( $post_id ) {
        $thumb = '';

        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
            $thumb = developer_starter_get_thumbnail_url( $post_id, 'medium' );
        } elseif ( has_post_thumbnail( $post_id ) ) {
            $thumb = get_the_post_thumbnail_url( $post_id, 'medium' );
        }

        $excerpt = get_the_excerpt( $post_id );
        if ( ! is_string( $excerpt ) ) {
            $excerpt = '';
        }

        return array(
            'id'       => (int) $post_id,
            'title'    => get_the_title( $post_id ),
            'url'      => get_permalink( $post_id ),
            'thumb'    => $thumb,
            'excerpt'  => wp_strip_all_tags( $excerpt ),
            'date'     => get_the_date( 'Y-m-d', $post_id ),
            'resource' => $this->get_resource_snapshot( $post_id ),
        );
    }

    /**
     * 获取资源状态快照（仅展示层使用，不涉及支付流程）。
     *
     * @param int $post_id 文章ID。
     * @return array<string, mixed>
     */
    private function get_resource_snapshot( $post_id ) {
        $resource = function_exists( 'developer_starter_get_qilingshop_resource_snapshot' )
            ? developer_starter_get_qilingshop_resource_snapshot( $post_id )
            : array();
        $has_resource = ! empty( $resource['has_resource'] );
        $download_count = isset( $resource['download_count'] ) ? (int) $resource['download_count'] : 0;
        $points_price = isset( $resource['points_price'] ) ? (float) $resource['points_price'] : 0.0;
        $badge_text = '';
        $badge_class = '';
        $price_text = '';

        if ( $has_resource ) {
            if ( ! empty( $resource['is_free'] ) ) {
                $badge_text = __( '免费', 'developer-starter' );
                $badge_class = 'is-free';
                $price_text = __( '免费获取', 'developer-starter' );
            } elseif ( ! empty( $resource['is_paid'] ) && $points_price > 0 ) {
                $badge_text = __( '付费', 'developer-starter' );
                $badge_class = 'is-paid';
                $price_text = sprintf( __( '%s 积分', 'developer-starter' ), number_format_i18n( $points_price, 0 ) );
            } elseif ( ! empty( $resource['is_vip'] ) ) {
                $badge_text = __( 'VIP', 'developer-starter' );
                $badge_class = 'is-vip';
                $price_text = __( 'VIP权益', 'developer-starter' );
            } elseif ( $download_count > 0 ) {
                $badge_text = __( '资源', 'developer-starter' );
                $badge_class = 'is-resource';
                $price_text = __( '可下载', 'developer-starter' );
            } else {
                $badge_text = __( '资源', 'developer-starter' );
                $badge_class = 'is-resource';
                $price_text = __( '资源内容', 'developer-starter' );
            }
        }

        return array(
            'has_resource'   => $has_resource,
            'badge_text'     => $badge_text,
            'badge_class'    => $badge_class,
            'price_text'     => $price_text,
            'download_count' => $download_count,
        );
    }

    private function sanitize_length( $value, $default ) {
        $value = trim( (string) $value );
        if ( $value === '' ) {
            return $default;
        }

        if ( preg_match( '/^-?(?:\d+|\d*\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    private function sanitize_hex( $value, $default ) {
        $hex = sanitize_hex_color( (string) $value );
        return $hex ? $hex : $default;
    }

    private function sanitize_color_value( $value, $default ) {
        $value = trim( (string) $value );
        if ( $value === '' ) {
            return $default;
        }

        $hex = sanitize_hex_color( $value );
        if ( $hex ) {
            return $hex;
        }

        if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9\.\s,%]+\s*\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    private function sanitize_opacity( $value, $default ) {
        $num = (float) $value;
        if ( $num < 0 || $num > 0.9 ) {
            return $default;
        }
        return (string) $num;
    }

    private function sanitize_gradient( $value, $default ) {
        $value = trim( (string) $value );
        if ( $value === '' ) {
            return $default;
        }

        if ( ! preg_match( '/^(linear-gradient|radial-gradient|conic-gradient)\(.+\)$/i', $value ) ) {
            return $default;
        }

        if ( preg_match( '/[;{}<>]/', $value ) ) {
            return $default;
        }

        return $value;
    }
}
