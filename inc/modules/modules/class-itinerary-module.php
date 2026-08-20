<?php
/**
 * Itinerary Module - 行程规划模块
 * 
 * 按天展示旅游行程时间线，包含景点、餐饮、住宿安排
 * 适用于旅行社官网详细展示行程规划
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
 * 行程规划模块类
 * 
 * CSS前缀: ql-travel-itinerary-
 * 避免与其他模块样式冲突
 */
class Itinerary_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-calendar-alt';
        $this->description = __( '按天展示旅游行程规划', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'itinerary';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '行程规划', 'developer-starter' );
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
                'id'      => 'itinerary_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => __( '行程安排', 'developer-starter' ),
            ),
            array(
                'id'      => 'itinerary_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
            ),
            array(
                'id'    => 'itinerary_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'    => 'itinerary_subtitle',
                'type'  => 'text',
                'label' => __( '副标题', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'itinerary_layout',
                'type'    => 'select',
                'label'   => __( '布局样式', 'developer-starter' ),
                'options' => array(
                    'left'      => __( '左侧时间线', 'developer-starter' ),
                    'alternate' => __( '左右交替', 'developer-starter' ),
                    'cards'     => __( '卡片列表', 'developer-starter' ),
                ),
                'default' => 'left',
            ),
            array(
                'id'    => 'itinerary_line_color',
                'type'  => 'color',
                'label' => __( '时间线颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'          => 'itinerary_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '控制 Day 徽章与景点标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),

            // ========================================
            // 行程天数 (Repeater)
            // ========================================
            array(
                'id'     => 'itinerary_days',
                'type'   => 'repeater',
                'label'  => __( '行程列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'      => 'day_number',
                        'type'    => 'text',
                        'label'   => __( '第几天', 'developer-starter' ),
                        'default' => 'Day 1',
                        'desc'    => __( '如：Day 1 或 第一天', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'day_title',
                        'type'  => 'text',
                        'label' => __( '当日主题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'day_image',
                        'type'  => 'image',
                        'label' => __( '当日配图', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'morning',
                        'type'  => 'textarea',
                        'label' => __( '上午安排', 'developer-starter' ),
                        'rows'  => 3,
                    ),
                    array(
                        'id'    => 'afternoon',
                        'type'  => 'textarea',
                        'label' => __( '下午安排', 'developer-starter' ),
                        'rows'  => 3,
                    ),
                    array(
                        'id'    => 'evening',
                        'type'  => 'textarea',
                        'label' => __( '晚间安排', 'developer-starter' ),
                        'rows'  => 2,
                    ),
                    array(
                        'id'    => 'meals',
                        'type'  => 'text',
                        'label' => __( '餐饮安排', 'developer-starter' ),
                        'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '如：早餐/午餐/晚餐', 'Example: Breakfast/Lunch/Dinner' ) : __( '如：早餐/午餐/晚餐', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'accommodation',
                        'type'  => 'text',
                        'label' => __( '住宿安排', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'attractions',
                        'type'  => 'textarea',
                        'label' => __( '途经景点', 'developer-starter' ),
                        'desc'  => __( '每行一个景点', 'developer-starter' ),
                        'rows'  => 3,
                    ),
                ),
            ),

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
        // ========================================
        // 获取配置数据
        // ========================================
        $title        = isset( $data['itinerary_title'] ) && $data['itinerary_title'] !== '' 
                        ? $data['itinerary_title'] 
                        : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '行程安排', 'Travel Itinerary' ) : __( '行程安排', 'developer-starter' ) );
        $title_size   = isset( $data['itinerary_title_size'] ) && $data['itinerary_title_size'] !== '' 
                        ? $data['itinerary_title_size'] 
                        : '2rem';
        $title_color  = isset( $data['itinerary_title_color'] ) ? $data['itinerary_title_color'] : '';
        $subtitle     = isset( $data['itinerary_subtitle'] ) ? $data['itinerary_subtitle'] : '';
        
        $layout       = isset( $data['itinerary_layout'] ) ? $data['itinerary_layout'] : 'left';
        $line_color   = isset( $data['itinerary_line_color'] ) ? $data['itinerary_line_color'] : '';
        $badge_bg     = isset( $data['itinerary_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['itinerary_badge_bg'] ) ) : '';
        $badge_bg     = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $days         = isset( $data['itinerary_days'] ) ? $data['itinerary_days'] : array();
        
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
        if ( empty( $days ) ) {
            $days = array(
                array(
                    'day_number'    => 'Day 1',
                    'day_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '抵达三亚，入住酒店', 'Arrival and Hotel Check-in' ) : __( '抵达三亚，入住酒店', 'developer-starter' ),
                    'day_image'     => '',
                    'morning'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '乘坐航班抵达三亚凤凰国际机场，接机后前往酒店', 'Arrive at the airport and transfer to the hotel.' ) : __( '乘坐航班抵达三亚凤凰国际机场，接机后前往酒店', 'developer-starter' ),
                    'afternoon'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '入住五星级海景酒店，自由活动', 'Check in to the resort and enjoy free time.' ) : __( '入住五星级海景酒店，自由活动', 'developer-starter' ),
                    'evening'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '海边漫步，享用海鲜晚餐', 'Take a seaside walk and enjoy a signature seafood dinner.' ) : __( '海边漫步，享用海鲜晚餐', 'developer-starter' ),
                    'meals'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '晚餐', 'Dinner' ) : __( '晚餐', 'developer-starter' ),
                    'accommodation' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '三亚湾希尔顿酒店', 'Bayfront Resort Hotel' ) : __( '三亚湾希尔顿酒店', 'developer-starter' ),
                    'attractions'   => '',
                ),
                array(
                    'day_number'    => 'Day 2',
                    'day_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '亚龙湾一日游', 'Bay Discovery Day' ) : __( '亚龙湾一日游', 'developer-starter' ),
                    'day_image'     => '',
                    'morning'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '前往亚龙湾热带天堂森林公园，欣赏热带雨林风光', 'Visit the rainforest park for scenic trails and viewpoints.' ) : __( '前往亚龙湾热带天堂森林公园，欣赏热带雨林风光', 'developer-starter' ),
                    'afternoon'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '亚龙湾沙滩自由活动，畅享阳光沙滩', 'Spend the afternoon on the beach with free leisure time.' ) : __( '亚龙湾沙滩自由活动，畅享阳光沙滩', 'developer-starter' ),
                    'evening'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '返回酒店休息', 'Return to the hotel for the evening.' ) : __( '返回酒店休息', 'developer-starter' ),
                    'meals'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '早餐/午餐/晚餐', 'Breakfast/Lunch/Dinner' ) : __( '早餐/午餐/晚餐', 'developer-starter' ),
                    'accommodation' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '三亚湾希尔顿酒店', 'Bayfront Resort Hotel' ) : __( '三亚湾希尔顿酒店', 'developer-starter' ),
                    'attractions'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "亚龙湾热带天堂森林公园\n亚龙湾沙滩", "Rainforest Park\nBay Beach" ) : __( "亚龙湾热带天堂森林公园\n亚龙湾沙滩", 'developer-starter' ),
                ),
                array(
                    'day_number'    => 'Day 3',
                    'day_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '天涯海角/南山文化', 'Coastal Landmarks and Culture' ) : __( '天涯海角/南山文化', 'developer-starter' ),
                    'day_image'     => '',
                    'morning'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '游览天涯海角风景区，打卡经典地标', 'Visit iconic coastal viewpoints and signature landmarks.' ) : __( '游览天涯海角风景区，打卡经典地标', 'developer-starter' ),
                    'afternoon'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '前往南山文化旅游区，参观南海观音', 'Continue to the cultural park for an afternoon tour.' ) : __( '前往南山文化旅游区，参观南海观音', 'developer-starter' ),
                    'evening'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '品尝当地特色美食', 'Enjoy local specialties for dinner.' ) : __( '品尝当地特色美食', 'developer-starter' ),
                    'meals'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '早餐/午餐/晚餐', 'Breakfast/Lunch/Dinner' ) : __( '早餐/午餐/晚餐', 'developer-starter' ),
                    'accommodation' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '三亚湾希尔顿酒店', 'Bayfront Resort Hotel' ) : __( '三亚湾希尔顿酒店', 'developer-starter' ),
                    'attractions'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "天涯海角\n南山文化旅游区\n南海观音", "Coastal Landmark Park\nCultural Heritage Park\nOceanfront monument" ) : __( "天涯海角\n南山文化旅游区\n南海观音", 'developer-starter' ),
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
        if ( $badge_bg ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};";
        }

        $title_style = "font-size: {$title_size};";
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        $line_style = $line_color ? "background-color: {$line_color};" : '';
        ?>
        <section class="module module-itinerary" style="<?php echo esc_attr( $section_style ); ?>">
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

                <!-- 行程时间线 -->
                <?php if ( ! empty( $days ) ) : ?>
                    <div class="ql-travel-itinerary ql-travel-layout-<?php echo esc_attr( $layout ); ?>">
                        <!-- 时间线 -->
                        <?php if ( $layout !== 'cards' ) : ?>
                            <div class="ql-travel-itinerary-line" <?php echo $line_style ? 'style="' . esc_attr( $line_style ) . '"' : ''; ?>></div>
                        <?php endif; ?>

                        <?php foreach ( $days as $index => $day ) :
                            // 获取当天数据
                            $day_num     = isset( $day['day_number'] ) ? $day['day_number'] : '';
                            $day_title   = isset( $day['day_title'] ) ? $day['day_title'] : '';
                            $day_image   = isset( $day['day_image'] ) ? $day['day_image'] : '';
                            $morning     = isset( $day['morning'] ) ? $day['morning'] : '';
                            $afternoon   = isset( $day['afternoon'] ) ? $day['afternoon'] : '';
                            $evening     = isset( $day['evening'] ) ? $day['evening'] : '';
                            $meals       = isset( $day['meals'] ) ? $day['meals'] : '';
                            $accommodation = isset( $day['accommodation'] ) ? $day['accommodation'] : '';
                            $attractions = isset( $day['attractions'] ) ? $day['attractions'] : '';

                            // 解析景点列表
                            $attractions_array = array();
                            if ( $attractions ) {
                                $attractions = str_replace( array( "\r\n", "\r" ), "\n", $attractions );
                                $attractions_array = array_filter( array_map( 'trim', explode( "\n", $attractions ) ) );
                            }

                            // 动画属性
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }

                            // 交替布局的位置
                            $position_class = '';
                            if ( $layout === 'alternate' ) {
                                $position_class = $index % 2 === 0 ? 'ql-travel-left' : 'ql-travel-right';
                            }
                        ?>
                            <div class="ql-travel-itinerary-day <?php echo esc_attr( $position_class ); ?>" <?php echo $anim_attr; ?>>
                                <!-- 时间点标记 -->
                                <?php if ( $layout !== 'cards' ) : ?>
                                    <div class="ql-travel-itinerary-marker">
                                        <span class="ql-travel-itinerary-day-num"><?php echo esc_html( $day_num ); ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- 内容卡片 -->
                                <div class="ql-travel-itinerary-content">
                                    <!-- 卡片头部 -->
                                    <div class="ql-travel-itinerary-header">
                                        <?php if ( $layout === 'cards' ) : ?>
                                            <span class="ql-travel-itinerary-day-badge"><?php echo esc_html( $day_num ); ?></span>
                                        <?php endif; ?>
                                        <h3 class="ql-travel-itinerary-title"><?php echo esc_html( $day_title ); ?></h3>
                                    </div>

                                    <!-- 当日配图 -->
                                    <?php if ( $day_image ) : ?>
                                        <div class="ql-travel-itinerary-image">
                                            <img src="<?php echo esc_url( $day_image ); ?>" alt="<?php echo esc_attr( $day_title ); ?>" loading="lazy" />
                                        </div>
                                    <?php endif; ?>

                                    <!-- 日程安排 -->
                                    <div class="ql-travel-itinerary-schedule">
                                        <?php if ( $morning ) : ?>
                                            <div class="ql-travel-schedule-item">
                                                <span class="ql-travel-schedule-time">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="5"></circle>
                                                        <line x1="12" y1="1" x2="12" y2="3"></line>
                                                        <line x1="12" y1="21" x2="12" y2="23"></line>
                                                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                                        <line x1="1" y1="12" x2="3" y2="12"></line>
                                                        <line x1="21" y1="12" x2="23" y2="12"></line>
                                                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                                                    </svg>
                                                    <?php esc_html_e( '上午', 'developer-starter' ); ?>
                                                </span>
                                                <p class="ql-travel-schedule-desc"><?php echo wp_kses_post( nl2br( esc_html( $morning ) ) ); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $afternoon ) : ?>
                                            <div class="ql-travel-schedule-item">
                                                <span class="ql-travel-schedule-time">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    <?php esc_html_e( '下午', 'developer-starter' ); ?>
                                                </span>
                                                <p class="ql-travel-schedule-desc"><?php echo wp_kses_post( nl2br( esc_html( $afternoon ) ) ); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $evening ) : ?>
                                            <div class="ql-travel-schedule-item">
                                                <span class="ql-travel-schedule-time">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                                                    </svg>
                                                    <?php esc_html_e( '晚间', 'developer-starter' ); ?>
                                                </span>
                                                <p class="ql-travel-schedule-desc"><?php echo wp_kses_post( nl2br( esc_html( $evening ) ) ); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 途经景点 -->
                                    <?php if ( ! empty( $attractions_array ) ) : ?>
                                        <div class="ql-travel-itinerary-attractions">
                                            <span class="ql-travel-attractions-label">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                    <circle cx="12" cy="10" r="3"></circle>
                                                </svg>
                                                <?php esc_html_e( '途经景点', 'developer-starter' ); ?>
                                            </span>
                                            <div class="ql-travel-attractions-tags">
                                                <?php foreach ( $attractions_array as $attraction ) : ?>
                                                    <span class="ql-travel-attraction-tag"><?php echo esc_html( $attraction ); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- 餐饮住宿 -->
                                    <div class="ql-travel-itinerary-footer">
                                        <?php if ( $meals ) : ?>
                                            <div class="ql-travel-footer-item">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                                    <line x1="6" y1="1" x2="6" y2="4"></line>
                                                    <line x1="10" y1="1" x2="10" y2="4"></line>
                                                    <line x1="14" y1="1" x2="14" y2="4"></line>
                                                </svg>
                                                <span><?php echo esc_html( $meals ); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $accommodation ) : ?>
                                            <div class="ql-travel-footer-item">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                                </svg>
                                                <span><?php echo esc_html( $accommodation ); ?></span>
                                            </div>
                                        <?php endif; ?>
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
