<?php
/**
 * Promotion Module - 特价促销模块
 * 
 * 展示限时优惠、特价活动、促销套餐等营销内容
 * 倒计时基于设置的截止时间，非刷新重置
 *
 * @package Developer_Starter
 * @since 1.0.3
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 特价促销模块类
 * 
 * CSS前缀: ql-promo-
 * 函数前缀: ql_promo_
 */
class Promotion_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'marketing';
        $this->icon        = 'dashicons-tag';
        $this->description = __( '展示限时优惠和特价促销活动', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'promotion';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '特价促销', 'developer-starter' );
    }

    /**
     * 获取模块配置字段
     *
     * @return array 字段配置数组
     */
    public function get_fields() {
        return array(
            // ========================================
            // 基础设置
            // ========================================
            array(
                'id'      => 'ql_promo_title',
                'type'    => 'text',
                'label'   => __( '活动标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '限时特惠', 'Limited-Time Deals' ) : __( '限时特惠', 'developer-starter' ),
            ),
            array(
                'id'      => 'ql_promo_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2.5rem',
            ),
            array(
                'id'    => 'ql_promo_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'ql_promo_subtitle',
                'type'    => 'text',
                'label'   => __( '活动副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '错过再等一年，限时抢购中', 'Seasonal offers are live now with limited availability.' ) : __( '错过再等一年，限时抢购中', 'developer-starter' ),
            ),
            array(
                'id'    => 'ql_promo_subtitle_color',
                'type'  => 'color',
                'label' => __( '副标题颜色', 'developer-starter' ),
            ),

            // ========================================
            // 倒计时设置（关键功能）
            // ========================================
            array(
                'id'      => 'ql_promo_show_countdown',
                'type'    => 'select',
                'label'   => __( '显示倒计时', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'    => 'ql_promo_end_date',
                'type'  => 'date',
                'label' => __( '活动截止日期', 'developer-starter' ),
                'desc'  => __( '格式: YYYY-MM-DD，如 2024-12-31', 'developer-starter' ),
            ),
            array(
                'id'    => 'ql_promo_end_time',
                'type'  => 'text',
                'label' => __( '活动截止时间', 'developer-starter' ),
                'desc'  => __( '格式: HH:MM，如 23:59（24小时制）', 'developer-starter' ),
            ),
            array(
                'id'      => 'ql_promo_expired_text',
                'type'    => 'text',
                'label'   => __( '活动结束提示', 'developer-starter' ),
                'default' => __( '活动已结束', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'ql_promo_layout',
                'type'    => 'select',
                'label'   => __( '布局样式', 'developer-starter' ),
                'options' => array(
                    'banner'   => __( '横幅式', 'developer-starter' ),
                    'cards'    => __( '卡片式', 'developer-starter' ),
                    'fullscreen' => __( '全屏式', 'developer-starter' ),
                ),
                'default' => 'banner',
            ),
            array(
                'id'      => 'ql_promo_columns',
                'type'    => 'select',
                'label'   => __( '卡片列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
                'desc'    => __( '仅卡片式布局有效', 'developer-starter' ),
            ),

            // ========================================
            // 促销产品列表 (Repeater)
            // ========================================
            array(
                'id'     => 'ql_promo_items',
                'type'   => 'repeater',
                'label'  => __( '促销产品列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '产品图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '产品名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'textarea',
                        'label' => __( '简短描述', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'original_price',
                        'type'  => 'text',
                        'label' => __( '原价', 'developer-starter' ),
                        'desc'  => function_exists( 'developer_starter_get_demo_price_hint' ) ? developer_starter_get_demo_price_hint( 999 ) : __( '如: ¥999', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'sale_price',
                        'type'  => 'text',
                        'label' => __( '促销价', 'developer-starter' ),
                        'desc'  => function_exists( 'developer_starter_get_demo_price_hint' ) ? developer_starter_get_demo_price_hint( 499 ) : __( '如: ¥499', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'badge',
                        'type'    => 'text',
                        'label'   => __( '折扣标签', 'developer-starter' ),
                        'desc'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '如: 限时5折、立减200、买一送一', 'Examples: 50% Off, Save $200, Buy 1 Get 1' ) : __( '如: 限时5折、立减200、买一送一', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'badge_color',
                        'type'    => 'color',
                        'label'   => __( '标签颜色', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'btn_text',
                        'type'    => 'text',
                        'label'   => __( '按钮文字', 'developer-starter' ),
                        'default' => __( '立即抢购', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'btn_link',
                        'type'  => 'text',
                        'label' => __( '按钮链接', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'      => 'ql_promo_btn_bg_color',
                'type'    => 'color',
                'label'   => __( '活动按钮背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'ql_promo_btn_text_color',
                'type'    => 'color',
                'label'   => __( '活动按钮文字颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'ql_promo_btn_border_color', __( '活动按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'      => 'ql_promo_btn_hover_bg_color',
                'type'    => 'color',
                'label'   => __( '活动按钮悬停背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'ql_promo_btn_hover_text_color',
                'type'    => 'color',
                'label'   => __( '活动按钮悬停文字颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'ql_promo_btn_hover_border_color', __( '活动按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随活动按钮悬停背景颜色。', 'developer-starter' ) ),

            // ========================================
            // 背景设置
            // ========================================
            array(
                'id'    => 'ql_promo_bg_color',
                'type'  => 'color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'desc'  => __( '支持渐变代码', 'developer-starter' ),
            ),
            array(
                'id'    => 'ql_promo_bg_image',
                'type'  => 'image',
                'label' => __( '背景图片', 'developer-starter' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'developer-starter' ),
                'default' => '80px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'developer-starter' ),
                'default' => '80px',
            ),

            // ========================================
            // 动画设置
            // ========================================
            array(
                'id'      => 'enable_staggered_animation',
                'type'    => 'select',
                'label'   => __( '开启逐个显示动画', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no'  => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
        );
    }

    /**
     * 渲染模块前端HTML
     *
     * @param array $data 模块配置数据
     */
    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        // ========================================
        // 获取配置数据
        // ========================================
        $title       = isset( $data['ql_promo_title'] ) && $data['ql_promo_title'] !== '' 
                       ? $data['ql_promo_title'] 
                       : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '限时特惠', 'Limited-Time Deals' ) : __( '限时特惠', 'developer-starter' ) );
        $title_size  = isset( $data['ql_promo_title_size'] ) && $data['ql_promo_title_size'] !== '' 
                       ? $data['ql_promo_title_size'] 
                       : '2.5rem';
        $title_color = isset( $data['ql_promo_title_color'] ) ? $data['ql_promo_title_color'] : '';
        
        $subtitle       = isset( $data['ql_promo_subtitle'] ) ? $data['ql_promo_subtitle'] : '';
        $subtitle_color = isset( $data['ql_promo_subtitle_color'] ) ? $data['ql_promo_subtitle_color'] : '';
        
        // 倒计时配置
        $show_countdown = isset( $data['ql_promo_show_countdown'] ) ? $data['ql_promo_show_countdown'] : 'yes';
        $end_date       = isset( $data['ql_promo_end_date'] ) ? trim( $data['ql_promo_end_date'] ) : '';
        $end_time       = isset( $data['ql_promo_end_time'] ) ? trim( $data['ql_promo_end_time'] ) : '23:59';
        $expired_text   = isset( $data['ql_promo_expired_text'] ) ? $data['ql_promo_expired_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '活动已结束', 'This promotion has ended.' ) : __( '活动已结束', 'developer-starter' ) );
        
        // 布局配置
        $layout  = isset( $data['ql_promo_layout'] ) ? $data['ql_promo_layout'] : 'banner';
        $columns = isset( $data['ql_promo_columns'] ) ? intval( $data['ql_promo_columns'] ) : 3;
        
        // 促销产品列表
        $items = isset( $data['ql_promo_items'] ) ? $data['ql_promo_items'] : array();
        $button_bg_color = isset( $data['ql_promo_btn_bg_color'] ) ? $clean_css_value( $data['ql_promo_btn_bg_color'] ) : '';
        $button_text_color = isset( $data['ql_promo_btn_text_color'] ) ? $clean_css_value( $data['ql_promo_btn_text_color'] ) : '';
        $button_border_color = isset( $data['ql_promo_btn_border_color'] ) ? $clean_css_value( $data['ql_promo_btn_border_color'] ) : '';
        $button_hover_bg_color = isset( $data['ql_promo_btn_hover_bg_color'] ) ? $clean_css_value( $data['ql_promo_btn_hover_bg_color'] ) : '';
        $button_hover_text_color = isset( $data['ql_promo_btn_hover_text_color'] ) ? $clean_css_value( $data['ql_promo_btn_hover_text_color'] ) : '';
        $button_hover_border_color = isset( $data['ql_promo_btn_hover_border_color'] ) ? $clean_css_value( $data['ql_promo_btn_hover_border_color'] ) : '';
        
        // 背景设置
        $bg_color = isset( $data['ql_promo_bg_color'] ) ? $data['ql_promo_bg_color'] : '';
        $bg_image = isset( $data['ql_promo_bg_image'] ) ? $data['ql_promo_bg_image'] : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' 
              ? $data['module_padding_top'] 
              : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' 
              ? $data['module_padding_bottom'] 
              : '80px';
        
        // 动画设置
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        // ========================================
        // 默认示例数据
        // ========================================
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '豪华海景套房', 'Ocean View Suite' ) : __( '豪华海景套房', 'developer-starter' ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '270°无敌海景，私人阳台', '270-degree ocean views with a private terrace.' ) : __( '270°无敌海景，私人阳台', 'developer-starter' ),
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 2999 ) : '¥2999',
                    'sale_price'     => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 1499 ) : '¥1499',
                    'badge'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '限时5折', '50% Off' ) : __( '限时5折', 'developer-starter' ),
                    'badge_color'    => 'var(--color-error)',
                    'btn_text'       => __( '立即抢购', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '双人SPA套餐', 'Couples Spa Package' ) : __( '双人SPA套餐', 'developer-starter' ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '90分钟精油按摩 + 茶点', '90-minute aromatherapy session with refreshments.' ) : __( '90分钟精油按摩 + 茶点', 'developer-starter' ),
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 1280 ) : '¥1280',
                    'sale_price'     => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 699 ) : '¥699',
                    'badge'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '买一送一', 'Buy 1 Get 1' ) : __( '买一送一', 'developer-starter' ),
                    'badge_color'    => 'var(--color-success)',
                    'btn_text'       => __( '立即抢购', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '自助海鲜晚餐', 'Seafood Dinner Buffet' ) : __( '自助海鲜晚餐', 'developer-starter' ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '无限畅吃龙虾、帝王蟹', 'Unlimited lobster and king crab specialties.' ) : __( '无限畅吃龙虾、帝王蟹', 'developer-starter' ),
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 598 ) : '¥598',
                    'sale_price'     => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 398 ) : '¥398',
                    'badge'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立减200', 'Save $200' ) : __( '立减200', 'developer-starter' ),
                    'badge_color'    => 'var(--color-warning)',
                    'btn_text'       => __( '立即抢购', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
            );
        }

        // ========================================
        // 计算倒计时（基于服务器时间和设置的截止时间）
        // ========================================
        $countdown_data   = array();
        $is_expired       = false;
        $end_timestamp    = 0;
        $server_timestamp = current_time( 'timestamp' ); // WordPress服务器当前时间戳
        
        if ( $show_countdown === 'yes' && $end_date ) {
            // 将设置的日期时间转换为时间戳
            $datetime_string = $end_date . ' ' . ( $end_time ? $end_time : '23:59' ) . ':59';
            $end_timestamp   = strtotime( $datetime_string );
            
            if ( $end_timestamp && $end_timestamp > $server_timestamp ) {
                // 活动进行中 - 计算剩余时间
                $remaining = $end_timestamp - $server_timestamp;
                
                $countdown_data['days']    = floor( $remaining / 86400 );
                $countdown_data['hours']   = floor( ( $remaining % 86400 ) / 3600 );
                $countdown_data['minutes'] = floor( ( $remaining % 3600 ) / 60 );
                $countdown_data['seconds'] = $remaining % 60;
            } else {
                // 活动已结束
                $is_expired = true;
            }
        }

        // ========================================
        // 构建样式
        // ========================================
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_image ) {
            $section_style .= "background-image: url('{$bg_image}'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false 
                              ? "background: {$bg_color};" 
                              : "background-color: {$bg_color};";
        }

        if ( $button_bg_color ) {
            $section_style .= "--ql-promo-btn-bg: {$button_bg_color};--ql-promo-btn-border: {$button_bg_color};";
        }
        if ( $button_text_color ) {
            $section_style .= "--ql-promo-btn-text: {$button_text_color};";
        }
        if ( $button_border_color ) {
            $section_style .= "--ql-promo-btn-border: {$button_border_color};";
        }
        if ( $button_hover_bg_color ) {
            $section_style .= "--ql-promo-btn-hover-bg: {$button_hover_bg_color};--ql-promo-btn-hover-border: {$button_hover_bg_color};";
        }
        if ( $button_hover_text_color ) {
            $section_style .= "--ql-promo-btn-hover-text: {$button_hover_text_color};";
        }
        if ( $button_hover_border_color ) {
            $section_style .= "--ql-promo-btn-hover-border: {$button_hover_border_color};";
        }

        $title_style = "font-size: {$title_size};";
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        $subtitle_style = '';
        if ( $subtitle_color ) {
            $subtitle_style = "color: {$subtitle_color};";
        }

        // 唯一ID用于倒计时
        $countdown_id = 'ql-promo-countdown-' . uniqid();

        // 占位图颜色
        $placeholder_colors = array(
            'linear-gradient(135deg, var(--color-error) 0%, var(--color-error-dark) 100%)',
            'linear-gradient(135deg, var(--color-info) 0%, var(--color-success) 100%)',
            'linear-gradient(135deg, var(--color-accent) 0%, var(--color-error) 100%)',
            'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)',
        );
        ?>
        
        <section class="module module-ql-promotion ql-promo-layout-<?php echo esc_attr( $layout ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php // 背景图片时添加遮罩层 ?>
            <?php if ( $bg_image ) : ?>
                <div class="ql-promo-overlay"></div>
            <?php endif; ?>
            
            <div class="container ql-promo-container">
                <!-- 头部区域：标题 + 倒计时 -->
                <div class="ql-promo-header">
                    <?php if ( $title ) : ?>
                        <h2 class="ql-promo-title" style="<?php echo esc_attr( $title_style ); ?>">
                            <?php echo esc_html( $title ); ?>
                        </h2>
                    <?php endif; ?>
                    
                    <?php if ( $subtitle ) : ?>
                        <p class="ql-promo-subtitle" style="<?php echo esc_attr( $subtitle_style ); ?>">
                            <?php echo esc_html( $subtitle ); ?>
                        </p>
                    <?php endif; ?>
                    
                    <!-- 倒计时区域 -->
                    <?php if ( $show_countdown === 'yes' && $end_date ) : ?>
                        <div class="ql-promo-countdown-wrap" 
                             id="<?php echo esc_attr( $countdown_id ); ?>"
                             data-end-timestamp="<?php echo esc_attr( $end_timestamp ); ?>"
                             data-expired-text="<?php echo esc_attr( $expired_text ); ?>">
                            
                            <?php if ( $is_expired ) : ?>
                                <div class="ql-promo-expired">
                                    <?php echo esc_html( $expired_text ); ?>
                                </div>
                            <?php else : ?>
                                <div class="ql-promo-countdown">
                                    <span class="ql-promo-countdown-label"><?php esc_html_e( '距离结束', 'developer-starter' ); ?></span>
                                    <div class="ql-promo-countdown-boxes">
                                        <div class="ql-promo-countdown-box">
                                            <span class="ql-promo-countdown-num" data-unit="days"><?php echo esc_html( $countdown_data['days'] ?? 0 ); ?></span>
                                            <span class="ql-promo-countdown-unit"><?php esc_html_e( '天', 'developer-starter' ); ?></span>
                                        </div>
                                        <span class="ql-promo-countdown-sep">:</span>
                                        <div class="ql-promo-countdown-box">
                                            <span class="ql-promo-countdown-num" data-unit="hours"><?php echo esc_html( str_pad( $countdown_data['hours'] ?? 0, 2, '0', STR_PAD_LEFT ) ); ?></span>
                                            <span class="ql-promo-countdown-unit"><?php esc_html_e( '时', 'developer-starter' ); ?></span>
                                        </div>
                                        <span class="ql-promo-countdown-sep">:</span>
                                        <div class="ql-promo-countdown-box">
                                            <span class="ql-promo-countdown-num" data-unit="minutes"><?php echo esc_html( str_pad( $countdown_data['minutes'] ?? 0, 2, '0', STR_PAD_LEFT ) ); ?></span>
                                            <span class="ql-promo-countdown-unit"><?php esc_html_e( '分', 'developer-starter' ); ?></span>
                                        </div>
                                        <span class="ql-promo-countdown-sep">:</span>
                                        <div class="ql-promo-countdown-box">
                                            <span class="ql-promo-countdown-num" data-unit="seconds"><?php echo esc_html( str_pad( $countdown_data['seconds'] ?? 0, 2, '0', STR_PAD_LEFT ) ); ?></span>
                                            <span class="ql-promo-countdown-unit"><?php esc_html_e( '秒', 'developer-starter' ); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 促销产品列表 -->
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="ql-promo-items ql-promo-cols-<?php echo esc_attr( $columns ); ?>">
                        <?php foreach ( $items as $index => $item ) : 
                            $this->ql_promo_render_item( $item, $index, $enable_anim, $placeholder_colors );
                        endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- 倒计时JS（基于服务器设置的截止时间） -->
        <?php if ( $show_countdown === 'yes' && $end_date && ! $is_expired ) : ?>
        <script>
        (function() {
            var container = document.getElementById('<?php echo esc_js( $countdown_id ); ?>');
            if (!container) return;
            
            // 从data属性获取截止时间戳（服务器设置的时间）
            var endTimestamp = parseInt(container.getAttribute('data-end-timestamp')) * 1000;
            var expiredText = container.getAttribute('data-expired-text');
            
            // 计算服务器时间与客户端时间的偏差
            // 服务器当前时间戳
            var serverNow = <?php echo esc_js( $server_timestamp ); ?> * 1000;
            // 客户端当前时间戳
            var clientNow = Date.now();
            // 时间偏差（服务器时间 - 客户端时间）
            var timeOffset = serverNow - clientNow;
            
            /**
             * 更新倒计时显示
             * 使用服务器时间偏差校正，确保与服务器时间同步
             */
            function qlPromoUpdateCountdown() {
                if (!container.isConnected) return;
                // 使用校正后的当前时间
                var now = Date.now() + timeOffset;
                var remaining = endTimestamp - now;
                
                if (remaining <= 0) {
                    // 活动已结束
                    container.innerHTML = '<div class="ql-promo-expired">' + expiredText + '</div>';
                    return;
                }
                
                // 计算剩余时间
                var days = Math.floor(remaining / 86400000);
                var hours = Math.floor((remaining % 86400000) / 3600000);
                var minutes = Math.floor((remaining % 3600000) / 60000);
                var seconds = Math.floor((remaining % 60000) / 1000);
                
                // 更新显示
                var daysEl = container.querySelector('[data-unit="days"]');
                var hoursEl = container.querySelector('[data-unit="hours"]');
                var minutesEl = container.querySelector('[data-unit="minutes"]');
                var secondsEl = container.querySelector('[data-unit="seconds"]');
                
                if (daysEl) daysEl.textContent = days;
                if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
                
                // 每秒更新
                setTimeout(qlPromoUpdateCountdown, 1000);
            }
            
            // 启动倒计时
            qlPromoUpdateCountdown();
        })();
        </script>
        <?php endif; ?>
        <?php
    }

    /**
     * 渲染单个促销产品项
     *
     * @param array  $item             产品数据
     * @param int    $index            索引
     * @param string $enable_anim      是否启用动画
     * @param array  $placeholder_colors 占位图颜色
     */
    private function ql_promo_render_item( $item, $index, $enable_anim, $placeholder_colors ) {
        $image          = isset( $item['image'] ) ? $item['image'] : '';
        $name           = isset( $item['name'] ) ? $item['name'] : '';
        $desc           = isset( $item['desc'] ) ? $item['desc'] : '';
        $original_price = isset( $item['original_price'] ) ? $item['original_price'] : '';
        $sale_price     = isset( $item['sale_price'] ) ? $item['sale_price'] : '';
        $badge          = isset( $item['badge'] ) ? $item['badge'] : '';
        $badge_color    = isset( $item['badge_color'] ) && ! empty( $item['badge_color'] ) 
                          ? $item['badge_color'] 
                          : 'var(--color-error)';
        $btn_text       = isset( $item['btn_text'] ) && $item['btn_text'] !== '' 
                          ? $item['btn_text'] 
                          : __( '立即抢购', 'developer-starter' );
        $btn_link       = isset( $item['btn_link'] ) ? $item['btn_link'] : '#';

        $placeholder_bg = $placeholder_colors[ $index % count( $placeholder_colors ) ];

        // 动画属性
        $anim_attr = '';
        if ( $enable_anim === 'yes' ) {
            $anim_attr = $this->get_staggered_animation_attr( $index );
        }
        ?>
        <div class="ql-promo-item" <?php echo $anim_attr; ?>>
            <!-- 折扣标签 -->
            <?php if ( $badge ) : ?>
                <div class="ql-promo-badge" style="background-color: <?php echo esc_attr( $badge_color ); ?>;">
                    <?php echo esc_html( $badge ); ?>
                </div>
            <?php endif; ?>
            
            <!-- 产品图片 -->
            <div class="ql-promo-image">
                <?php if ( $image ) : ?>
                    <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
                <?php else : ?>
                    <div class="ql-promo-placeholder" style="background: <?php echo esc_attr( $placeholder_bg ); ?>;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--qiling-color-rgba-255-255-255-06)" stroke-width="1.5">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- 产品信息 -->
            <div class="ql-promo-info">
                <?php if ( $name ) : ?>
                    <h3 class="ql-promo-name"><?php echo esc_html( $name ); ?></h3>
                <?php endif; ?>
                
                <?php if ( $desc ) : ?>
                    <p class="ql-promo-desc"><?php echo esc_html( $desc ); ?></p>
                <?php endif; ?>
                
                <!-- 价格区域 -->
                <div class="ql-promo-price">
                    <?php if ( $sale_price ) : ?>
                        <span class="ql-promo-sale-price"><?php echo esc_html( $sale_price ); ?></span>
                    <?php endif; ?>
                    <?php if ( $original_price ) : ?>
                        <span class="ql-promo-original-price"><?php echo esc_html( $original_price ); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- 行动按钮 -->
                <?php if ( $btn_link ) : ?>
                    <a href="<?php echo esc_url( $btn_link ); ?>" class="ql-promo-btn">
                        <?php echo esc_html( $btn_text ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
