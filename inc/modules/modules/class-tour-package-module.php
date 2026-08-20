<?php
/**
 * Tour Package Module - 旅游线路模块
 * 
 * 展示旅游线路/套餐卡片，适用于旅行社官网
 * 支持行程天数、价格、目的地、出发城市等信息展示
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
 * 旅游线路模块类
 * 
 * CSS前缀: ql-travel-tour-
 * 避免与其他模块样式冲突
 */
class Tour_Package_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-location-alt';
        $this->description = __( '展示旅游线路和套餐', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'tour-package';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '旅游线路', 'developer-starter' );
    }

    /**
     * 获取模块配置字段
     * 
     * 所有字段使用动态变量，无硬编码
     *
     * @return array 字段配置数组
     */
    public function get_fields() {
        return array(
            // ========================================
            // 标题设置
            // ========================================
            array(
                'id'      => 'tour_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门旅游线路', 'Featured Tours' ) : __( '热门旅游线路', 'developer-starter' ),
            ),
            array(
                'id'      => 'tour_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
                'desc'    => __( '如 2rem 或 32px', 'developer-starter' ),
            ),
            array(
                'id'    => 'tour_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'tour_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精选优质旅游线路，开启美好旅程', 'Curated trips for memorable journeys.' ) : __( '精选优质旅游线路，开启美好旅程', 'developer-starter' ),
            ),
            array(
                'id'    => 'tour_subtitle_color',
                'type'  => 'color',
                'label' => __( '副标题颜色', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'tour_columns',
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
                'id'      => 'tour_card_style',
                'type'    => 'select',
                'label'   => __( '卡片样式', 'developer-starter' ),
                'options' => array(
                    'standard' => __( '标准卡片', 'developer-starter' ),
                    'overlay'  => __( '图片叠加', 'developer-starter' ),
                ),
                'default' => 'standard',
            ),

            // ========================================
            // 线路列表 (Repeater)
            // ========================================
            array(
                'id'     => 'tour_items',
                'type'   => 'repeater',
                'label'  => __( '线路列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '封面图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '线路名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'destination',
                        'type'  => 'text',
                        'label' => __( '目的地', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'departure',
                        'type'  => 'text',
                        'label' => __( '出发城市', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'days',
                        'type'  => 'text',
                        'label' => __( '行程天数', 'developer-starter' ),
                        'desc'  => __( '如：5天4晚', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'price',
                        'type'  => 'text',
                        'label' => __( '价格', 'developer-starter' ),
                        'desc'  => function_exists( 'developer_starter_get_demo_price_hint' ) ? developer_starter_get_demo_price_hint( 2999 ) : __( '如：¥2999', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'original_price',
                        'type'  => 'text',
                        'label' => __( '原价（划线价）', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'badge',
                        'type'    => 'select',
                        'label'   => __( '标签', 'developer-starter' ),
                        'options' => array(
                            ''         => __( '无', 'developer-starter' ),
                            'hot'      => __( '热门', 'developer-starter' ),
                            'recommend'=> __( '推荐', 'developer-starter' ),
                            'discount' => __( '特价', 'developer-starter' ),
                            'new'      => __( '新品', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'    => 'highlights',
                        'type'  => 'textarea',
                        'label' => __( '行程亮点', 'developer-starter' ),
                        'desc'  => __( '每行一个亮点', 'developer-starter' ),
                        'rows'  => 4,
                    ),
                    array(
                        'id'      => 'link',
                        'type'    => 'text',
                        'label'   => __( '详情链接', 'developer-starter' ),
                        'default' => '#',
                    ),
                ),
            ),

            // ========================================
            // 背景设置
            // ========================================
            array(
                'id'      => 'module_bg_type',
                'type'    => 'select',
                'label'   => __( '背景类型', 'developer-starter' ),
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id'         => 'module_bg_color',
                'type'       => 'color',
                'label'      => __( '背景颜色', 'developer-starter' ),
                'desc'       => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default'    => '',
                'dependency' => array( 'module_bg_type', '==', 'color' ),
            ),
            array(
                'id'         => 'module_bg_image',
                'type'       => 'image',
                'label'      => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'module_bg_type', '==', 'image' ),
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
                'id'          => 'enable_staggered_animation',
                'type'        => 'select',
                'label'       => __( '开启列表逐个显示动画', 'developer-starter' ),
                'options'     => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no'  => __( '关闭', 'developer-starter' ),
                ),
                'default'     => 'yes',
                'description' => __( '开启后，线路卡片将依次延迟显示', 'developer-starter' ),
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
        $title          = isset( $data['tour_title'] ) && $data['tour_title'] !== '' 
                          ? $data['tour_title'] 
                          : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门旅游线路', 'Featured Tours' ) : __( '热门旅游线路', 'developer-starter' ) );
        $title_size     = isset( $data['tour_title_size'] ) && $data['tour_title_size'] !== '' 
                          ? $data['tour_title_size'] 
                          : '2rem';
        $title_color    = isset( $data['tour_title_color'] ) ? $data['tour_title_color'] : '';
        
        $subtitle       = isset( $data['tour_subtitle'] ) ? $data['tour_subtitle'] : '';
        $subtitle_color = isset( $data['tour_subtitle_color'] ) ? $data['tour_subtitle_color'] : '';
        
        $columns        = isset( $data['tour_columns'] ) ? intval( $data['tour_columns'] ) : 3;
        $card_style     = isset( $data['tour_card_style'] ) ? $data['tour_card_style'] : 'standard';
        $items          = isset( $data['tour_items'] ) ? $data['tour_items'] : array();
        
        // 背景设置
        $bg_type  = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $pt       = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' 
                    ? $data['module_padding_top'] 
                    : '80px';
        $pb       = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' 
                    ? $data['module_padding_bottom'] 
                    : '80px';
        
        // 动画设置
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        // ========================================
        // 默认示例数据（方便预览）
        // ========================================
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'image'          => '',
                    'title'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '三亚阳光海岸5日游', 'Sunny Coast Escape · 5 Days' ) : __( '三亚阳光海岸5日游', 'developer-starter' ),
                    'destination'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '三亚', 'Coastal Resort' ) : __( '三亚', 'developer-starter' ),
                    'departure'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '北京', 'Beijing' ) : __( '北京', 'developer-starter' ),
                    'days'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '5天4晚', '5 days / 4 nights' ) : __( '5天4晚', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 2999 ) : '¥2999',
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 3999 ) : '¥3999',
                    'badge'          => 'hot',
                    'highlights'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "五星海景酒店\n亚龙湾/天涯海角\n含三餐", "Oceanfront resort\nSignature coastal landmarks\nMeals included" ) : __( "五星海景酒店\n亚龙湾/天涯海角\n含三餐", 'developer-starter' ),
                    'link'           => '#',
                ),
                array(
                    'image'          => '',
                    'title'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '云南丽江大理双飞6日', 'Lijiang & Dali Discovery · 6 Days' ) : __( '云南丽江大理双飞6日', 'developer-starter' ),
                    'destination'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '云南', 'Yunnan' ) : __( '云南', 'developer-starter' ),
                    'departure'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '上海', 'Shanghai' ) : __( '上海', 'developer-starter' ),
                    'days'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '6天5晚', '6 days / 5 nights' ) : __( '6天5晚', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 3599 ) : '¥3599',
                    'original_price' => '',
                    'badge'          => 'recommend',
                    'highlights'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "丽江古城\n玉龙雪山\n洱海游船", "Old town walks\nSnow mountain views\nLake cruise" ) : __( "丽江古城\n玉龙雪山\n洱海游船", 'developer-starter' ),
                    'link'           => '#',
                ),
                array(
                    'image'          => '',
                    'title'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '桂林山水甲天下4日', 'Guilin Landscape Highlights · 4 Days' ) : __( '桂林山水甲天下4日', 'developer-starter' ),
                    'destination'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '桂林', 'Guilin' ) : __( '桂林', 'developer-starter' ),
                    'departure'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '广州', 'Guangzhou' ) : __( '广州', 'developer-starter' ),
                    'days'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '4天3晚', '4 days / 3 nights' ) : __( '4天3晚', 'developer-starter' ),
                    'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 1999 ) : '¥1999',
                    'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 2599 ) : '¥2599',
                    'badge'          => 'discount',
                    'highlights'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "漓江竹筏\n阳朔西街\n象鼻山", "Li River raft ride\nYangshuo strolls\nLandmark viewpoints" ) : __( "漓江竹筏\n阳朔西街\n象鼻山", 'developer-starter' ),
                    'link'           => '#',
                ),
            );
        }

        // ========================================
        // 构建样式
        // ========================================
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_type === 'color' && ! empty( $bg_color ) ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false 
                              ? "background: {$bg_color};" 
                              : "background-color: {$bg_color};";
        } elseif ( $bg_type === 'image' && ! empty( $bg_image ) ) {
            $section_style .= "background-image: url('{$bg_image}'); background-size: cover; background-position: center;";
        }

        $title_style = "font-size: {$title_size};";
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        $subtitle_style = '';
        if ( $subtitle_color ) {
            $subtitle_style = "color: {$subtitle_color};";
        }

        // 标签文字映射
        $badge_labels = array(
            'hot'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门', 'Popular' ) : __( '热门', 'developer-starter' ),
            'recommend' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '推荐', 'Recommended' ) : __( '推荐', 'developer-starter' ),
            'discount'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '特价', 'Special' ) : __( '特价', 'developer-starter' ),
            'new'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '新品', 'New' ) : __( '新品', 'developer-starter' ),
        );
        ?>
        <section class="module module-tour-package" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <!-- 标题区域 -->
                <div class="section-header text-center">
                    <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>">
                        <?php echo wp_kses_post( $title ); ?>
                    </h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle" style="<?php echo esc_attr( $subtitle_style ); ?>">
                            <?php echo wp_kses_post( $subtitle ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- 线路列表 -->
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="ql-travel-tour-grid ql-travel-cols-<?php echo esc_attr( $columns ); ?>">
                        <?php foreach ( $items as $index => $item ) :
                            // 获取项目数据
                            $item_image    = isset( $item['image'] ) ? $item['image'] : '';
                            $item_title    = isset( $item['title'] ) ? $item['title'] : '';
                            $item_dest     = isset( $item['destination'] ) ? $item['destination'] : '';
                            $item_departure= isset( $item['departure'] ) ? $item['departure'] : '';
                            $item_days     = isset( $item['days'] ) ? $item['days'] : '';
                            $item_price    = isset( $item['price'] ) ? $item['price'] : '';
                            $item_orig     = isset( $item['original_price'] ) ? $item['original_price'] : '';
                            $item_badge    = isset( $item['badge'] ) ? $item['badge'] : '';
                            $item_highlights = isset( $item['highlights'] ) ? $item['highlights'] : '';
                            $item_link     = isset( $item['link'] ) ? $item['link'] : '#';

                            // 解析亮点列表
                            $highlights_array = array();
                            if ( $item_highlights ) {
                                $item_highlights = str_replace( array( "\r\n", "\r" ), "\n", $item_highlights );
                                $highlights_array = array_filter( array_map( 'trim', explode( "\n", $item_highlights ) ) );
                            }

                            // 动画属性
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }

                            $price_prefix = '';
                            $price_value  = trim( (string) $item_price );
                            $price_suffix = function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '起', 'from' ) : __( '起', 'developer-starter' );
                            if ( preg_match( '/^([^\d]*?)\s*([\d.,]+)\s*(.*)$/u', trim( (string) $item_price ), $price_matches ) ) {
                                $price_prefix = trim( $price_matches[1] );
                                $price_value  = trim( $price_matches[2] );
                                if ( '' !== trim( $price_matches[3] ) ) {
                                    $price_suffix = trim( $price_matches[3] );
                                }
                            }

                            // 默认占位图
                            $placeholder_img = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23e2e8f0" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" fill="%2394a3b8" text-anchor="middle" dy=".3em" font-size="20"%3E' . urlencode( __( '旅游线路', 'developer-starter' ) ) . '%3C/text%3E%3C/svg%3E';
                            $display_image = $item_image ? $item_image : $placeholder_img;
                        ?>
                            <div class="ql-travel-tour-card ql-travel-style-<?php echo esc_attr( $card_style ); ?>" <?php echo $anim_attr; ?>>
                                <!-- 图片区域 -->
                                <div class="ql-travel-tour-image">
                                    <a href="<?php echo esc_url( $item_link ); ?>">
                                        <img src="<?php echo esc_url( $display_image ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" loading="lazy" />
                                    </a>
                                    
                                    <!-- 标签 -->
                                    <?php if ( $item_badge && isset( $badge_labels[ $item_badge ] ) ) : ?>
                                        <span class="ql-travel-tour-badge ql-travel-badge-<?php echo esc_attr( $item_badge ); ?>">
                                            <?php echo esc_html( $badge_labels[ $item_badge ] ); ?>
                                        </span>
                                    <?php endif; ?>

                                    <!-- 行程天数 -->
                                    <?php if ( $item_days ) : ?>
                                        <span class="ql-travel-tour-days">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                            <?php echo esc_html( $item_days ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- 信息区域 -->
                                <div class="ql-travel-tour-info">
                                    <!-- 线路名称 -->
                                    <h3 class="ql-travel-tour-title">
                                        <a href="<?php echo esc_url( $item_link ); ?>">
                                            <?php echo esc_html( $item_title ); ?>
                                        </a>
                                    </h3>

                                    <!-- 出发/目的地 -->
                                    <?php if ( $item_departure || $item_dest ) : ?>
                                        <div class="ql-travel-tour-route">
                                            <?php if ( $item_departure ) : ?>
                                                <span class="ql-travel-tour-departure">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="10" r="3"></circle>
                                                        <path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"></path>
                                                    </svg>
                                                    <?php echo esc_html( $item_departure ); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ( $item_departure && $item_dest ) : ?>
                                                <span class="ql-travel-tour-arrow">→</span>
                                            <?php endif; ?>
                                            <?php if ( $item_dest ) : ?>
                                                <span class="ql-travel-tour-destination">
                                                    <?php echo esc_html( $item_dest ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- 行程亮点 -->
                                    <?php if ( ! empty( $highlights_array ) ) : ?>
                                        <ul class="ql-travel-tour-highlights">
                                            <?php foreach ( array_slice( $highlights_array, 0, 3 ) as $highlight ) : ?>
                                                <li>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                    <?php echo esc_html( $highlight ); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <!-- 价格区域 -->
                                    <div class="ql-travel-tour-price">
                                        <?php if ( $price_prefix ) : ?>
                                            <span class="ql-travel-tour-price-label"><?php echo esc_html( $price_prefix ); ?></span>
                                        <?php endif; ?>
                                        <span class="ql-travel-tour-price-value">
                                            <?php echo esc_html( $price_value ); ?>
                                        </span>
                                        <?php if ( $price_suffix ) : ?>
                                            <span class="ql-travel-tour-price-unit"><?php echo esc_html( $price_suffix ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $item_orig ) : ?>
                                            <span class="ql-travel-tour-price-original"><?php echo esc_html( $item_orig ); ?></span>
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
