<?php
/**
 * Room Showcase Module - 房型展示模块
 * 
 * 展示酒店客房类型、面积、床型、价格和设施
 * 适用于酒店官网和民宿预订网站
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
 * 房型展示模块类
 * 
 * CSS前缀: ql-hotel-room-
 * 避免与其他模块样式冲突
 */
class Room_Showcase_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-admin-home';
        $this->description = __( '展示酒店房型和价格', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'room-showcase';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '房型展示', 'developer-starter' );
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
                'id'      => 'room_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => __( '精选客房', 'developer-starter' ),
            ),
            array(
                'id'      => 'room_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
            ),
            array(
                'id'    => 'room_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'room_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => __( '为您精心准备的舒适空间', 'developer-starter' ),
            ),

            // ========================================
            // iconfont 设置
            // ========================================
            array(
                'id'    => 'iconfont_url',
                'type'  => 'text',
                'label' => __( 'iconfont JS地址', 'developer-starter' ),
                'desc'  => __( '阿里巴巴 iconfont Symbol 方式的 JS 链接', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'room_columns',
                'type'    => 'select',
                'label'   => __( '列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'room_card_style',
                'type'    => 'select',
                'label'   => __( '卡片样式', 'developer-starter' ),
                'options' => array(
                    'standard'   => __( '标准卡片', 'developer-starter' ),
                    'horizontal' => __( '横向布局', 'developer-starter' ),
                ),
                'default' => 'standard',
            ),

            // ========================================
            // 房型列表 (Repeater)
            // ========================================
            array(
                'id'     => 'room_items',
                'type'   => 'repeater',
                'label'  => __( '房型列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '房型图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '房型名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'area',
                        'type'  => 'text',
                        'label' => __( '房间面积', 'developer-starter' ),
                        'desc'  => __( '如：45㎡', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'bed_type',
                        'type'    => 'select',
                        'label'   => __( '床型', 'developer-starter' ),
                        'options' => array(
                            'king'      => __( '大床', 'developer-starter' ),
                            'twin'      => __( '双床', 'developer-starter' ),
                            'queen'     => __( '标准床', 'developer-starter' ),
                            'family'    => __( '家庭多床', 'developer-starter' ),
                            'tatami'    => __( '榻榻米', 'developer-starter' ),
                        ),
                        'default' => 'king',
                    ),
                    array(
                        'id'    => 'capacity',
                        'type'  => 'text',
                        'label' => __( '可住人数', 'developer-starter' ),
                        'desc'  => __( '如：2人', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'price',
                        'type'  => 'text',
                        'label' => __( '价格', 'developer-starter' ),
                        'desc'  => function_exists( 'developer_starter_get_demo_price_hint' ) ? developer_starter_get_demo_price_hint( 688 ) : __( '如：¥688', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'original_price',
                        'type'  => 'text',
                        'label' => __( '原价（划线价）', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'badge',
                        'type'    => 'select',
                        'label'   => __( '房型标签', 'developer-starter' ),
                        'options' => array(
                            ''          => __( '无', 'developer-starter' ),
                            'luxury'    => __( '豪华', 'developer-starter' ),
                            'seaview'   => __( '海景', 'developer-starter' ),
                            'cityview'  => __( '城景', 'developer-starter' ),
                            'garden'    => __( '园景', 'developer-starter' ),
                            'discount'  => __( '特惠', 'developer-starter' ),
                            'recommend' => __( '推荐', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'    => 'amenities',
                        'type'  => 'textarea',
                        'label' => __( '房间设施', 'developer-starter' ),
                        'desc'  => __( '每行格式：图标名|设施名称，如：icon-wifi|免费WiFi', 'developer-starter' ),
                        'rows'  => 5,
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'textarea',
                        'label' => __( '房型描述', 'developer-starter' ),
                        'rows'  => 2,
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
                        'label'   => __( '预订链接', 'developer-starter' ),
                        'default' => '#',
                    ),
                ),
            ),
            array(
                'id'      => 'room_btn_bg_color',
                'type'    => 'color',
                'label'   => __( '预订按钮背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'room_btn_text_color',
                'type'    => 'color',
                'label'   => __( '预订按钮文字颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'room_btn_border_color', __( '预订按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'      => 'room_btn_hover_bg_color',
                'type'    => 'color',
                'label'   => __( '预订按钮悬停背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'room_btn_hover_text_color',
                'type'    => 'color',
                'label'   => __( '预订按钮悬停文字颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'room_btn_hover_border_color', __( '预订按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随预订按钮悬停背景颜色。', 'developer-starter' ) ),

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
        $title       = isset( $data['room_title'] ) && $data['room_title'] !== '' 
                       ? $data['room_title'] 
                       : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精选客房', 'Featured Rooms' ) : __( '精选客房', 'developer-starter' ) );
        $title_size  = isset( $data['room_title_size'] ) && $data['room_title_size'] !== '' 
                       ? $data['room_title_size'] 
                       : '2rem';
        $title_color = isset( $data['room_title_color'] ) ? $data['room_title_color'] : '';
        $subtitle    = isset( $data['room_subtitle'] ) ? $data['room_subtitle'] : '';
        
        // iconfont
        $iconfont_url = isset( $data['iconfont_url'] ) ? $data['iconfont_url'] : '';
        
        $columns     = isset( $data['room_columns'] ) ? intval( $data['room_columns'] ) : 3;
        $card_style  = isset( $data['room_card_style'] ) ? $data['room_card_style'] : 'standard';
        $items       = isset( $data['room_items'] ) ? $data['room_items'] : array();
        $button_bg_color = isset( $data['room_btn_bg_color'] ) ? $clean_css_value( $data['room_btn_bg_color'] ) : '';
        $button_text_color = isset( $data['room_btn_text_color'] ) ? $clean_css_value( $data['room_btn_text_color'] ) : '';
        $button_border_color = isset( $data['room_btn_border_color'] ) ? $clean_css_value( $data['room_btn_border_color'] ) : '';
        $button_hover_bg_color = isset( $data['room_btn_hover_bg_color'] ) ? $clean_css_value( $data['room_btn_hover_bg_color'] ) : '';
        $button_hover_text_color = isset( $data['room_btn_hover_text_color'] ) ? $clean_css_value( $data['room_btn_hover_text_color'] ) : '';
        $button_hover_border_color = isset( $data['room_btn_hover_border_color'] ) ? $clean_css_value( $data['room_btn_hover_border_color'] ) : '';
        
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
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '豪华大床房', 'Deluxe King Room' ) : __( '豪华大床房', 'developer-starter' ),
                    'area'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '45㎡', '45 sqm' ) : '45㎡',
                    'bed_type'       => 'king',
                    'capacity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '2人', '2 guests' ) : __( '2人', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 688 ) : '¥688',
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 888 ) : '¥888',
                    'badge'          => 'luxury',
                    'amenities'      => "icon-wifi|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费WiFi', 'Free Wi-Fi' ) : __( '免费WiFi', 'developer-starter' ) ) . "\nicon-tv|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '智能电视', 'Smart TV' ) : __( '智能电视', 'developer-starter' ) ) . "\nicon-bath|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '独立浴室', 'Private bathroom' ) : __( '独立浴室', 'developer-starter' ) ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '宽敞明亮的豪华大床房，配备高品质床品', 'A spacious king room with premium bedding and refined finishes.' ) : __( '宽敞明亮的豪华大床房，配备高品质床品', 'developer-starter' ),
                    'btn_text'       => __( '立即预订', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '海景双床房', 'Ocean Twin Room' ) : __( '海景双床房', 'developer-starter' ),
                    'area'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '52㎡', '52 sqm' ) : '52㎡',
                    'bed_type'       => 'twin',
                    'capacity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '2-3人', '2-3 guests' ) : __( '2-3人', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 888 ) : '¥888',
                    'original_price' => '',
                    'badge'          => 'seaview',
                    'amenities'      => "icon-wifi|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费WiFi', 'Free Wi-Fi' ) : __( '免费WiFi', 'developer-starter' ) ) . "\nicon-balcony|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '观景阳台', 'Private balcony' ) : __( '观景阳台', 'developer-starter' ) ) . "\nicon-minibar|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '迷你吧', 'Minibar' ) : __( '迷你吧', 'developer-starter' ) ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '无敌海景视野，让您尽享海滨度假时光', 'Open sea views designed for a relaxed coastal stay.' ) : __( '无敌海景视野，让您尽享海滨度假时光', 'developer-starter' ),
                    'btn_text'       => __( '立即预订', 'developer-starter' ),
                    'btn_link'       => '#',
                ),
                array(
                    'image'          => '',
                    'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '行政套房', 'Executive Suite' ) : __( '行政套房', 'developer-starter' ),
                    'area'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '78㎡', '78 sqm' ) : '78㎡',
                    'bed_type'       => 'king',
                    'capacity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '2人', '2 guests' ) : __( '2人', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 1288 ) : '¥1288',
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 1588 ) : '¥1588',
                    'badge'          => 'recommend',
                    'amenities'      => "icon-wifi|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费WiFi', 'Free Wi-Fi' ) : __( '免费WiFi', 'developer-starter' ) ) . "\nicon-lounge|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '行政酒廊', 'Executive lounge' ) : __( '行政酒廊', 'developer-starter' ) ) . "\nicon-breakfast|" . ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '含早餐', 'Breakfast included' ) : __( '含早餐', 'developer-starter' ) ),
                    'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '尊享行政礼遇，配备独立客厅和办公区', 'Premium suite with a lounge area and private workspace.' ) : __( '尊享行政礼遇，配备独立客厅和办公区', 'developer-starter' ),
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
            $section_style .= "--ql-room-btn-bg: {$button_bg_color};--ql-room-btn-border: {$button_bg_color};";
        }
        if ( $button_text_color ) {
            $section_style .= "--ql-room-btn-text: {$button_text_color};";
        }
        if ( $button_border_color ) {
            $section_style .= "--ql-room-btn-border: {$button_border_color};";
        }
        if ( $button_hover_bg_color ) {
            $section_style .= "--ql-room-btn-hover-bg: {$button_hover_bg_color};--ql-room-btn-hover-border: {$button_hover_bg_color};";
        }
        if ( $button_hover_text_color ) {
            $section_style .= "--ql-room-btn-hover-text: {$button_hover_text_color};";
        }
        if ( $button_hover_border_color ) {
            $section_style .= "--ql-room-btn-hover-border: {$button_hover_border_color};";
        }

        $title_style = "font-size: {$title_size};";
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        // 床型文字映射
        $bed_labels = array(
            'king'   => __( '大床', 'developer-starter' ),
            'twin'   => __( '双床', 'developer-starter' ),
            'queen'  => __( '标准床', 'developer-starter' ),
            'family' => __( '家庭多床', 'developer-starter' ),
            'tatami' => __( '榻榻米', 'developer-starter' ),
        );

        // 标签文字映射
        $badge_labels = array(
            'luxury'    => __( '豪华', 'developer-starter' ),
            'seaview'   => __( '海景', 'developer-starter' ),
            'cityview'  => __( '城景', 'developer-starter' ),
            'garden'    => __( '园景', 'developer-starter' ),
            'discount'  => __( '特惠', 'developer-starter' ),
            'recommend' => __( '推荐', 'developer-starter' ),
        );
        ?>
        
        <?php
        // 使用 WP 脚本句柄加载，避免多个模块重复输出相同 <script src>。
        $iconfont_url_safe = esc_url_raw( (string) $iconfont_url );
        if ( '' !== $iconfont_url_safe ) {
            $iconfont_handle = 'developer-starter-module-iconfont-' . substr( md5( $iconfont_url_safe ), 0, 12 );
            if ( ! wp_script_is( $iconfont_handle, 'registered' ) ) {
                wp_register_script( $iconfont_handle, $iconfont_url_safe, array(), null, true );
            }
            wp_enqueue_script( $iconfont_handle );
        }
        ?>

        <section class="module module-room-showcase" style="<?php echo esc_attr( $section_style ); ?>">
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

                <!-- 房型列表 -->
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="ql-hotel-room-grid ql-hotel-cols-<?php echo esc_attr( $columns ); ?> ql-hotel-style-<?php echo esc_attr( $card_style ); ?>">
                        <?php foreach ( $items as $index => $item ) :
                            // 获取项目数据
                            $item_image    = isset( $item['image'] ) ? $item['image'] : '';
                            $item_name     = isset( $item['name'] ) ? $item['name'] : '';
                            $item_area     = isset( $item['area'] ) ? $item['area'] : '';
                            $item_bed      = isset( $item['bed_type'] ) ? $item['bed_type'] : 'king';
                            $item_capacity = isset( $item['capacity'] ) ? $item['capacity'] : '';
                            $item_price    = isset( $item['price'] ) ? $item['price'] : '';
                            $item_orig     = isset( $item['original_price'] ) ? $item['original_price'] : '';
                            $item_badge    = isset( $item['badge'] ) ? $item['badge'] : '';
                            $item_amenities= isset( $item['amenities'] ) ? $item['amenities'] : '';
                            $item_desc     = isset( $item['desc'] ) ? $item['desc'] : '';
                            $item_btn_text = isset( $item['btn_text'] ) ? $item['btn_text'] : __( '立即预订', 'developer-starter' );
                            $item_btn_link = isset( $item['btn_link'] ) ? $item['btn_link'] : '#';

                            // 解析设施列表
                            $amenities_array = array();
                            if ( $item_amenities ) {
                                $item_amenities = str_replace( array( "\r\n", "\r" ), "\n", $item_amenities );
                                $lines = array_filter( array_map( 'trim', explode( "\n", $item_amenities ) ) );
                                foreach ( $lines as $line ) {
                                    $parts = explode( '|', $line, 2 );
                                    if ( count( $parts ) === 2 ) {
                                        $amenities_array[] = array(
                                            'icon' => trim( $parts[0] ),
                                            'name' => trim( $parts[1] ),
                                        );
                                    }
                                }
                            }

                            // 动画属性
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }

                            // 默认占位图
                            $placeholder_img = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 260"%3E%3Crect fill="%23e2e8f0" width="400" height="260"/%3E%3Ctext x="50%25" y="50%25" fill="%2394a3b8" text-anchor="middle" dy=".3em" font-size="18"%3E' . urlencode( __( '房型图片', 'developer-starter' ) ) . '%3C/text%3E%3C/svg%3E';
                            $display_image = $item_image ? $item_image : $placeholder_img;
                        ?>
                            <div class="ql-hotel-room-card" <?php echo $anim_attr; ?>>
                                <!-- 图片区域 -->
                                <div class="ql-hotel-room-image">
                                    <img src="<?php echo esc_url( $display_image ); ?>" alt="<?php echo esc_attr( $item_name ); ?>" loading="lazy" />
                                    
                                    <!-- 标签 -->
                                    <?php if ( $item_badge && isset( $badge_labels[ $item_badge ] ) ) : ?>
                                        <span class="ql-hotel-room-badge ql-hotel-badge-<?php echo esc_attr( $item_badge ); ?>">
                                            <?php echo esc_html( $badge_labels[ $item_badge ] ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- 内容区域 -->
                                <div class="ql-hotel-room-content">
                                    <!-- 房型名称 -->
                                    <h3 class="ql-hotel-room-name"><?php echo esc_html( $item_name ); ?></h3>

                                    <!-- 房型信息 -->
                                    <div class="ql-hotel-room-meta">
                                        <?php if ( $item_area ) : ?>
                                            <span class="ql-hotel-room-area">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                                </svg>
                                                <?php echo esc_html( $item_area ); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ( $item_bed && isset( $bed_labels[ $item_bed ] ) ) : ?>
                                            <span class="ql-hotel-room-bed">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M2 9V20h20V9"></path>
                                                    <path d="M2 14h20"></path>
                                                    <path d="M4 9V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"></path>
                                                </svg>
                                                <?php echo esc_html( $bed_labels[ $item_bed ] ); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ( $item_capacity ) : ?>
                                            <span class="ql-hotel-room-capacity">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="9" cy="7" r="4"></circle>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                </svg>
                                                <?php echo esc_html( $item_capacity ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 描述 -->
                                    <?php if ( $item_desc ) : ?>
                                        <p class="ql-hotel-room-desc"><?php echo esc_html( $item_desc ); ?></p>
                                    <?php endif; ?>

                                    <!-- 设施列表 -->
                                    <?php if ( ! empty( $amenities_array ) ) : ?>
                                        <div class="ql-hotel-room-amenities">
                                            <?php foreach ( array_slice( $amenities_array, 0, 4 ) as $amenity ) : ?>
                                                <span class="ql-hotel-amenity-item">
                                                    <?php if ( $iconfont_url && $amenity['icon'] ) : ?>
                                                        <svg class="ql-hotel-icon" aria-hidden="true">
                                                            <use xlink:href="#<?php echo esc_attr( $amenity['icon'] ); ?>"></use>
                                                        </svg>
                                                    <?php else : ?>
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    <?php endif; ?>
                                                    <?php echo esc_html( $amenity['name'] ); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- 价格和预订 -->
                                    <div class="ql-hotel-room-footer">
                                        <div class="ql-hotel-room-price">
                                            <span class="ql-hotel-price-current"><?php echo esc_html( $item_price ); ?></span>
                                            <span class="ql-hotel-price-unit"><?php echo esc_html( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/晚', '/night' ) : __( '/晚', 'developer-starter' ) ); ?></span>
                                            <?php if ( $item_orig ) : ?>
                                                <span class="ql-hotel-price-original"><?php echo esc_html( $item_orig ); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="<?php echo esc_url( $item_btn_link ); ?>" class="ql-hotel-room-btn">
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
