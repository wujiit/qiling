<?php
/**
 * Ticket Showcase Module - 景点门票模块
 * 
 * 展示景点门票类型、价格、有效期等信息
 * 适用于旅行社和景区官网
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
 * 景点门票模块类
 * 
 * CSS前缀: ql-travel-ticket-
 * 避免与其他模块样式冲突
 */
class Ticket_Showcase_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-tickets-alt';
        $this->description = __( '展示景点门票和价格', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'ticket-showcase';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '景点门票', 'developer-starter' );
    }

    /**
     * 获取模块配置字段
     *
     * @return array 字段配置数组
     */
    public function get_fields() {
        return array(
            // ========================================
            // 标题设置
            // ========================================
            array(
                'id'      => 'ticket_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门景点门票', 'Popular Attraction Tickets' ) : __( '热门景点门票', 'developer-starter' ),
            ),
            array(
                'id'      => 'ticket_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
            ),
            array(
                'id'    => 'ticket_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'    => 'ticket_subtitle',
                'type'  => 'text',
                'label' => __( '副标题', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'ticket_columns',
                'type'    => 'select',
                'label'   => __( '列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'ticket_card_style',
                'type'    => 'select',
                'label'   => __( '卡片样式', 'developer-starter' ),
                'options' => array(
                    'standard' => __( '标准卡片', 'developer-starter' ),
                    'minimal'  => __( '简约风格', 'developer-starter' ),
                    'featured' => __( '突出价格', 'developer-starter' ),
                ),
                'default' => 'standard',
            ),

            // ========================================
            // 门票列表 (Repeater)
            // ========================================
            array(
                'id'     => 'ticket_items',
                'type'   => 'repeater',
                'label'  => __( '门票列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '景点图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '门票名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'textarea',
                        'label' => __( '门票描述', 'developer-starter' ),
                        'rows'  => 2,
                    ),
                    array(
                        'id'    => 'price',
                        'type'  => 'text',
                        'label' => __( '现价', 'developer-starter' ),
                        'desc'  => function_exists( 'developer_starter_get_demo_price_hint' ) ? developer_starter_get_demo_price_hint( 128 ) : __( '如：¥128', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'original_price',
                        'type'  => 'text',
                        'label' => __( '原价', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'validity',
                        'type'  => 'text',
                        'label' => __( '有效期', 'developer-starter' ),
                        'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '如：当日有效、30天内有效', 'Examples: Valid today, Valid for 30 days' ) : __( '如：当日有效、30天内有效', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'ticket_type',
                        'type'    => 'select',
                        'label'   => __( '票务类型', 'developer-starter' ),
                        'options' => array(
                            'e-ticket'  => __( '电子票', 'developer-starter' ),
                            'physical'  => __( '实体票', 'developer-starter' ),
                            'both'      => __( '电子/实体', 'developer-starter' ),
                        ),
                        'default' => 'e-ticket',
                    ),
                    array(
                        'id'      => 'audience',
                        'type'    => 'select',
                        'label'   => __( '适用人群', 'developer-starter' ),
                        'options' => array(
                            'adult'   => __( '成人票', 'developer-starter' ),
                            'child'   => __( '儿童票', 'developer-starter' ),
                            'student' => __( '学生票', 'developer-starter' ),
                            'senior'  => __( '老人票', 'developer-starter' ),
                            'family'  => __( '家庭套票', 'developer-starter' ),
                            'all'     => __( '通用票', 'developer-starter' ),
                        ),
                        'default' => 'adult',
                    ),
                    array(
                        'id'    => 'features',
                        'type'  => 'textarea',
                        'label' => __( '包含项目', 'developer-starter' ),
                        'desc'  => __( '每行一个项目', 'developer-starter' ),
                        'rows'  => 4,
                    ),
                    array(
                        'id'      => 'badge',
                        'type'    => 'select',
                        'label'   => __( '促销标签', 'developer-starter' ),
                        'options' => array(
                            ''          => __( '无', 'developer-starter' ),
                            'hot'       => __( '热卖', 'developer-starter' ),
                            'recommend' => __( '推荐', 'developer-starter' ),
                            'limited'   => __( '限时', 'developer-starter' ),
                            'vip'       => __( 'VIP', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'btn_text',
                        'type'    => 'text',
                        'label'   => __( '按钮文字', 'developer-starter' ),
                        'default' => __( '立即预订', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'btn_link',
                        'type'    => 'text',
                        'label'   => __( '购票链接', 'developer-starter' ),
                        'default' => '#',
                    ),
                ),
            ),
            array(
                'id'      => 'ticket_btn_bg_color',
                'type'    => 'color',
                'label'   => __( '购票按钮背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'ticket_btn_text_color',
                'type'    => 'color',
                'label'   => __( '购票按钮文字颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'ticket_btn_border_color', __( '购票按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'      => 'ticket_btn_hover_bg_color',
                'type'    => 'color',
                'label'   => __( '购票按钮悬停背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'ticket_btn_hover_text_color',
                'type'    => 'color',
                'label'   => __( '购票按钮悬停文字颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'ticket_btn_hover_border_color', __( '购票按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随购票按钮悬停背景颜色。', 'developer-starter' ) ),

            // ========================================
            // 背景设置
            // ========================================
            array(
                'id'      => 'module_bg_color',
                'type'    => 'color',
                'label'   => __( '背景颜色', 'developer-starter' ),
                'default' => '',
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
        $title       = isset( $data['ticket_title'] ) && $data['ticket_title'] !== '' 
                       ? $data['ticket_title'] 
                       : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门景点门票', 'Popular Attraction Tickets' ) : __( '热门景点门票', 'developer-starter' ) );
        $title_size  = isset( $data['ticket_title_size'] ) && $data['ticket_title_size'] !== '' 
                       ? $data['ticket_title_size'] 
                       : '2rem';
        $title_color = isset( $data['ticket_title_color'] ) ? $data['ticket_title_color'] : '';
        $subtitle    = isset( $data['ticket_subtitle'] ) ? $data['ticket_subtitle'] : '';
        
        $columns     = isset( $data['ticket_columns'] ) ? intval( $data['ticket_columns'] ) : 3;
        $card_style  = isset( $data['ticket_card_style'] ) ? $data['ticket_card_style'] : 'standard';
        $items       = isset( $data['ticket_items'] ) ? $data['ticket_items'] : array();
        $button_bg_color = isset( $data['ticket_btn_bg_color'] ) ? $clean_css_value( $data['ticket_btn_bg_color'] ) : '';
        $button_text_color = isset( $data['ticket_btn_text_color'] ) ? $clean_css_value( $data['ticket_btn_text_color'] ) : '';
        $button_border_color = isset( $data['ticket_btn_border_color'] ) ? $clean_css_value( $data['ticket_btn_border_color'] ) : '';
        $button_hover_bg_color = isset( $data['ticket_btn_hover_bg_color'] ) ? $clean_css_value( $data['ticket_btn_hover_bg_color'] ) : '';
        $button_hover_text_color = isset( $data['ticket_btn_hover_text_color'] ) ? $clean_css_value( $data['ticket_btn_hover_text_color'] ) : '';
        $button_hover_border_color = isset( $data['ticket_btn_hover_border_color'] ) ? $clean_css_value( $data['ticket_btn_hover_border_color'] ) : '';
        
        // 背景设置
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $pt       = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' 
                    ? $data['module_padding_top'] 
                    : '80px';
        $pb       = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' 
                    ? $data['module_padding_bottom'] 
                    : '80px';
        
        // 动画
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        // ========================================
        // 默认示例数据
        // ========================================
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '亚龙湾热带天堂森林公园', 'Rainforest Park Pass' ) : __( '亚龙湾热带天堂森林公园', 'developer-starter' ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热带雨林探秘，俯瞰亚龙湾全景', 'Explore the rainforest with panoramic bay views.' ) : __( '热带雨林探秘，俯瞰亚龙湾全景', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 158 ) : '¥158',
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 198 ) : '¥198',
                    'validity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当日有效', 'Valid today' ) : __( '当日有效', 'developer-starter' ),
                    'ticket_type'    => 'e-ticket',
                    'audience'       => 'adult',
                    'features'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "景区大门票\n玻璃栈道\n滑索体验", "Park admission\nGlass skywalk\nZipline experience" ) : __( "景区大门票\n玻璃栈道\n滑索体验", 'developer-starter' ),
                    'badge'          => 'hot',
                    'btn_text'       => __( '立即预订', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '南山文化旅游区', 'Cultural Heritage Park' ) : __( '南山文化旅游区', 'developer-starter' ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '南海观音圣像，祈福圣地', 'An iconic cultural destination with coastal landmarks.' ) : __( '南海观音圣像，祈福圣地', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 129 ) : '¥129',
                    'original_price' => '',
                    'validity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当日有效', 'Valid today' ) : __( '当日有效', 'developer-starter' ),
                    'ticket_type'    => 'e-ticket',
                    'audience'       => 'adult',
                    'features'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "景区大门票\n南海观音\n长寿谷", "Park admission\nCultural monuments\nScenic valley route" ) : __( "景区大门票\n南海观音\n长寿谷", 'developer-starter' ),
                    'badge'          => 'recommend',
                    'btn_text'       => __( '立即预订', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '天涯海角风景区', 'Coastal Landmark Ticket' ) : __( '天涯海角风景区', 'developer-starter' ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '经典地标，浪漫海滨', 'A classic seaside stop for scenic coastal photos.' ) : __( '经典地标，浪漫海滨', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 68 ) : '¥68',
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 81 ) : '¥81',
                    'validity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当日有效', 'Valid today' ) : __( '当日有效', 'developer-starter' ),
                    'ticket_type'    => 'e-ticket',
                    'audience'       => 'adult',
                    'features'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "景区大门票\n天涯石\n海角石", "Park admission\nSignature rock landmark\nSeaside viewpoint" ) : __( "景区大门票\n天涯石\n海角石", 'developer-starter' ),
                    'badge'          => 'limited',
                    'btn_text'       => __( '立即预订', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
            );
        }

        // ========================================
        // 构建样式
        // ========================================
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false 
                              ? "background: {$bg_color};" 
                              : "background-color: {$bg_color};";
        }
        if ( $button_bg_color ) {
            $section_style .= "--ql-ticket-btn-bg: {$button_bg_color};--ql-ticket-btn-border: {$button_bg_color};";
        }
        if ( $button_text_color ) {
            $section_style .= "--ql-ticket-btn-text: {$button_text_color};";
        }
        if ( $button_border_color ) {
            $section_style .= "--ql-ticket-btn-border: {$button_border_color};";
        }
        if ( $button_hover_bg_color ) {
            $section_style .= "--ql-ticket-btn-hover-bg: {$button_hover_bg_color};--ql-ticket-btn-hover-border: {$button_hover_bg_color};";
        }
        if ( $button_hover_text_color ) {
            $section_style .= "--ql-ticket-btn-hover-text: {$button_hover_text_color};";
        }
        if ( $button_hover_border_color ) {
            $section_style .= "--ql-ticket-btn-hover-border: {$button_hover_border_color};";
        }

        $title_style = "font-size: {$title_size};";
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        // 标签和人群文字映射
        $badge_labels = array(
            'hot'       => __( '热卖', 'developer-starter' ),
            'recommend' => __( '推荐', 'developer-starter' ),
            'limited'   => __( '限时', 'developer-starter' ),
            'vip'       => __( 'VIP', 'developer-starter' ),
        );

        $audience_labels = array(
            'adult'   => __( '成人票', 'developer-starter' ),
            'child'   => __( '儿童票', 'developer-starter' ),
            'student' => __( '学生票', 'developer-starter' ),
            'senior'  => __( '老人票', 'developer-starter' ),
            'family'  => __( '家庭套票', 'developer-starter' ),
            'all'     => __( '通用票', 'developer-starter' ),
        );

        $type_labels = array(
            'e-ticket' => __( '电子票', 'developer-starter' ),
            'physical' => __( '实体票', 'developer-starter' ),
            'both'     => __( '电子/实体', 'developer-starter' ),
        );
        ?>
        <section class="module module-ticket-showcase" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <!-- 标题区域 -->
                <div class="section-header text-center">
                    <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>">
                        <?php echo wp_kses_post( $title ); ?>
                    </h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- 门票列表 -->
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="ql-travel-ticket-grid ql-travel-cols-<?php echo esc_attr( $columns ); ?>">
                        <?php foreach ( $items as $index => $item ) :
                            // 获取项目数据
                            $item_image    = isset( $item['image'] ) ? $item['image'] : '';
                            $item_name     = isset( $item['name'] ) ? $item['name'] : '';
                            $item_desc     = isset( $item['desc'] ) ? $item['desc'] : '';
                            $item_price    = isset( $item['price'] ) ? $item['price'] : '';
                            $item_orig     = isset( $item['original_price'] ) ? $item['original_price'] : '';
                            $item_validity = isset( $item['validity'] ) ? $item['validity'] : '';
                            $item_type     = isset( $item['ticket_type'] ) ? $item['ticket_type'] : 'e-ticket';
                            $item_audience = isset( $item['audience'] ) ? $item['audience'] : 'adult';
                            $item_features = isset( $item['features'] ) ? $item['features'] : '';
                            $item_badge    = isset( $item['badge'] ) ? $item['badge'] : '';
                            $item_btn_text = isset( $item['btn_text'] ) ? $item['btn_text'] : __( '立即预订', 'developer-starter' );
                            $item_btn_link = isset( $item['btn_link'] ) ? $item['btn_link'] : '#';

                            // 解析包含项目
                            $features_array = array();
                            if ( $item_features ) {
                                $item_features = str_replace( array( "\r\n", "\r" ), "\n", $item_features );
                                $features_array = array_filter( array_map( 'trim', explode( "\n", $item_features ) ) );
                            }

                            // 动画属性
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }

                            // 默认占位图
                            $placeholder_img = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200"%3E%3Crect fill="%23e2e8f0" width="400" height="200"/%3E%3Ctext x="50%25" y="50%25" fill="%2394a3b8" text-anchor="middle" dy=".3em" font-size="18"%3E' . urlencode( __( '景点图片', 'developer-starter' ) ) . '%3C/text%3E%3C/svg%3E';
                            $display_image = $item_image ? $item_image : $placeholder_img;
                        ?>
                            <div class="ql-travel-ticket-card ql-travel-style-<?php echo esc_attr( $card_style ); ?>" <?php echo $anim_attr; ?>>
                                <!-- 图片区域 -->
                                <?php if ( $card_style === 'standard' ) : ?>
                                    <div class="ql-travel-ticket-image">
                                        <img src="<?php echo esc_url( $display_image ); ?>" alt="<?php echo esc_attr( $item_name ); ?>" loading="lazy" />
                                        
                                        <!-- 标签 -->
                                        <?php if ( $item_badge && isset( $badge_labels[ $item_badge ] ) ) : ?>
                                            <span class="ql-travel-ticket-badge ql-travel-badge-<?php echo esc_attr( $item_badge ); ?>">
                                                <?php echo esc_html( $badge_labels[ $item_badge ] ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- 内容区域 -->
                                <div class="ql-travel-ticket-content">
                                    <!-- 头部信息 -->
                                    <div class="ql-travel-ticket-header">
                                        <h3 class="ql-travel-ticket-name"><?php echo esc_html( $item_name ); ?></h3>
                                        
                                        <!-- 标签（简约风格） -->
                                        <?php if ( $card_style !== 'standard' && $item_badge && isset( $badge_labels[ $item_badge ] ) ) : ?>
                                            <span class="ql-travel-ticket-badge-inline ql-travel-badge-<?php echo esc_attr( $item_badge ); ?>">
                                                <?php echo esc_html( $badge_labels[ $item_badge ] ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 描述 -->
                                    <?php if ( $item_desc ) : ?>
                                        <p class="ql-travel-ticket-desc"><?php echo esc_html( $item_desc ); ?></p>
                                    <?php endif; ?>

                                    <!-- 票务信息 -->
                                    <div class="ql-travel-ticket-meta">
                                        <?php if ( $item_audience && isset( $audience_labels[ $item_audience ] ) ) : ?>
                                            <span class="ql-travel-ticket-audience">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                                <?php echo esc_html( $audience_labels[ $item_audience ] ); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ( $item_type && isset( $type_labels[ $item_type ] ) ) : ?>
                                            <span class="ql-travel-ticket-type">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                                </svg>
                                                <?php echo esc_html( $type_labels[ $item_type ] ); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ( $item_validity ) : ?>
                                            <span class="ql-travel-ticket-validity">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                <?php echo esc_html( $item_validity ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 包含项目 -->
                                    <?php if ( ! empty( $features_array ) ) : ?>
                                        <ul class="ql-travel-ticket-features">
                                            <?php foreach ( array_slice( $features_array, 0, 4 ) as $feature ) : ?>
                                                <li>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                    <?php echo esc_html( $feature ); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <!-- 价格和购买 -->
                                    <div class="ql-travel-ticket-footer">
                                        <div class="ql-travel-ticket-price">
                                            <span class="ql-travel-ticket-price-current">
                                                <?php echo esc_html( $item_price ); ?>
                                            </span>
                                            <?php if ( $item_orig ) : ?>
                                                <span class="ql-travel-ticket-price-original">
                                                    <?php echo esc_html( $item_orig ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="<?php echo esc_url( $item_btn_link ); ?>" class="ql-travel-ticket-btn">
                                            <?php echo esc_html( $item_btn_text ); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
