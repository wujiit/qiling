<?php
/**
 * Hotel Amenities Module - 酒店设施模块
 * 
 * 展示酒店配套设施和服务
 * 支持阿里巴巴 iconfont Symbol/JS 图标方式
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
 * 酒店设施模块类
 * 
 * CSS前缀: ql-hotel-amenity-
 * 避免与其他模块样式冲突
 */
class Hotel_Amenities_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-building';
        $this->description = __( '展示酒店配套设施', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'hotel-amenities';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '酒店设施', 'developer-starter' );
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
                'id'      => 'amenity_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '酒店设施', 'Amenities' ) : __( '酒店设施', 'developer-starter' ),
            ),
            array(
                'id'      => 'amenity_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
            ),
            array(
                'id'    => 'amenity_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'amenity_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '完善的配套服务，只为您的舒适体验', 'Thoughtful facilities designed for a comfortable stay.' ) : __( '完善的配套服务，只为您的舒适体验', 'developer-starter' ),
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
            array(
                'id'      => 'icon_size',
                'type'    => 'text',
                'label'   => __( '图标大小', 'developer-starter' ),
                'default' => '48px',
            ),
            array(
                'id'    => 'icon_color',
                'type'  => 'color',
                'label' => __( '图标颜色', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'amenity_columns',
                'type'    => 'select',
                'label'   => __( '列数', 'developer-starter' ),
                'options' => array(
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                    '5' => __( '5列', 'developer-starter' ),
                    '6' => __( '6列', 'developer-starter' ),
                ),
                'default' => '4',
            ),
            array(
                'id'      => 'amenity_layout',
                'type'    => 'select',
                'label'   => __( '布局样式', 'developer-starter' ),
                'options' => array(
                    'grid'     => __( '网格布局', 'developer-starter' ),
                    'card'     => __( '卡片布局', 'developer-starter' ),
                    'minimal'  => __( '简约布局', 'developer-starter' ),
                ),
                'default' => 'grid',
            ),

            // ========================================
            // 设施列表 (Repeater)
            // ========================================
            array(
                'id'     => 'amenity_items',
                'type'   => 'repeater',
                'label'  => __( '设施列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'icon',
                        'type'  => 'text',
                        'label' => __( '图标名称', 'developer-starter' ),
                        'desc'  => __( 'iconfont图标名，如：icon-wifi', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'fallback_image',
                        'type'  => 'image',
                        'label' => __( '备用图片', 'developer-starter' ),
                        'desc'  => __( '无iconfont时显示此图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '设施名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'textarea',
                        'label' => __( '设施描述', 'developer-starter' ),
                        'rows'  => 2,
                    ),
                    array(
                        'id'    => 'time',
                        'type'  => 'text',
                        'label' => __( '开放时间', 'developer-starter' ),
                        'desc'  => __( '如：06:00-22:00', 'developer-starter' ),
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
                'id'    => 'module_bg_image',
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
        // ========================================
        // 获取配置数据
        // ========================================
        $title       = isset( $data['amenity_title'] ) && $data['amenity_title'] !== '' 
                       ? $data['amenity_title'] 
                       : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '酒店设施', 'Amenities' ) : __( '酒店设施', 'developer-starter' ) );
        $title_size  = isset( $data['amenity_title_size'] ) && $data['amenity_title_size'] !== '' 
                       ? $data['amenity_title_size'] 
                       : '2rem';
        $title_color = isset( $data['amenity_title_color'] ) ? $data['amenity_title_color'] : '';
        $subtitle    = isset( $data['amenity_subtitle'] ) ? $data['amenity_subtitle'] : '';
        
        // iconfont
        $iconfont_url = isset( $data['iconfont_url'] ) ? $data['iconfont_url'] : '';
        $icon_size    = isset( $data['icon_size'] ) && $data['icon_size'] !== '' 
                        ? $data['icon_size'] 
                        : '48px';
        $icon_color   = isset( $data['icon_color'] ) ? $data['icon_color'] : '';
        
        $columns  = isset( $data['amenity_columns'] ) ? intval( $data['amenity_columns'] ) : 4;
        $layout   = isset( $data['amenity_layout'] ) ? $data['amenity_layout'] : 'grid';
        $items    = isset( $data['amenity_items'] ) ? $data['amenity_items'] : array();
        
        // 背景设置
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
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
                    'icon' => 'icon-swimming',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '室内恒温泳池', 'Indoor Heated Pool' ) : __( '室内恒温泳池', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '25米标准泳道，全年恒温', '25-meter lap pool with year-round temperature control.' ) : __( '25米标准泳道，全年恒温', 'developer-starter' ),
                    'time' => '06:00-22:00',
                ),
                array(
                    'icon' => 'icon-gym',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '健身中心', 'Fitness Center' ) : __( '健身中心', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业器械，24小时开放', 'Professional equipment with around-the-clock access.' ) : __( '专业器械，24小时开放', 'developer-starter' ),
                    'time' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '24小时', '24 hours' ) : __( '24小时', 'developer-starter' ),
                ),
                array(
                    'icon' => 'icon-spa',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'SPA水疗', 'Spa & Wellness' ) : __( 'SPA水疗', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业理疗师，放松身心', 'Relax with treatments from professional therapists.' ) : __( '专业理疗师，放松身心', 'developer-starter' ),
                    'time' => '10:00-22:00',
                ),
                array(
                    'icon' => 'icon-restaurant',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '中西餐厅', 'Dining Room' ) : __( '中西餐厅', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精选美食，环球风味', 'A curated menu with regional and international flavors.' ) : __( '精选美食，环球风味', 'developer-starter' ),
                    'time' => '06:30-22:00',
                ),
                array(
                    'icon' => 'icon-bar',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '大堂酒吧', 'Lobby Bar' ) : __( '大堂酒吧', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精选酒水，惬意时光', 'Signature drinks and a relaxed lounge atmosphere.' ) : __( '精选酒水，惬意时光', 'developer-starter' ),
                    'time' => '14:00-02:00',
                ),
                array(
                    'icon' => 'icon-meeting',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '会议中心', 'Meeting Center' ) : __( '会议中心', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '多功能会议室，专业设备', 'Flexible meeting rooms with business-ready equipment.' ) : __( '多功能会议室，专业设备', 'developer-starter' ),
                    'time' => '08:00-20:00',
                ),
                array(
                    'icon' => 'icon-parking',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '停车场', 'Parking' ) : __( '停车场', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '地下车位，24小时安保', 'Underground parking with 24-hour security.' ) : __( '地下车位，24小时安保', 'developer-starter' ),
                    'time' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '24小时', '24 hours' ) : __( '24小时', 'developer-starter' ),
                ),
                array(
                    'icon' => 'icon-wifi',
                    'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费WiFi', 'Free Wi-Fi' ) : __( '免费WiFi', 'developer-starter' ),
                    'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全覆盖高速无线网络', 'High-speed wireless coverage throughout the property.' ) : __( '全覆盖高速无线网络', 'developer-starter' ),
                    'time' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '24小时', '24 hours' ) : __( '24小时', 'developer-starter' ),
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
        if ( $bg_image ) {
            $section_style .= "background-image: url('{$bg_image}'); background-size: cover; background-position: center;";
        }

        $title_style = "font-size: {$title_size};";
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        // 图标样式
        $icon_style = "width: {$icon_size}; height: {$icon_size};";
        if ( $icon_color ) {
            $icon_style .= "fill: {$icon_color}; color: {$icon_color};";
        }
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

        <section class="module module-hotel-amenities" style="<?php echo esc_attr( $section_style ); ?>">
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

                <!-- 设施列表 -->
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="ql-hotel-amenity-grid ql-hotel-cols-<?php echo esc_attr( $columns ); ?> ql-hotel-layout-<?php echo esc_attr( $layout ); ?>">
                        <?php foreach ( $items as $index => $item ) :
                            // 获取项目数据
                            $item_icon    = isset( $item['icon'] ) ? $item['icon'] : '';
                            $item_image   = isset( $item['fallback_image'] ) ? $item['fallback_image'] : '';
                            $item_name    = isset( $item['name'] ) ? $item['name'] : '';
                            $item_desc    = isset( $item['desc'] ) ? $item['desc'] : '';
                            $item_time    = isset( $item['time'] ) ? $item['time'] : '';

                            // 动画属性
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="ql-hotel-amenity-item" <?php echo $anim_attr; ?>>
                                <!-- 图标区域 -->
                                <div class="ql-hotel-amenity-icon" style="<?php echo esc_attr( $icon_style ); ?>">
                                    <?php if ( $iconfont_url && $item_icon ) : ?>
                                        <svg class="ql-hotel-icon" aria-hidden="true" style="<?php echo esc_attr( $icon_style ); ?>">
                                            <use xlink:href="#<?php echo esc_attr( $item_icon ); ?>"></use>
                                        </svg>
                                    <?php elseif ( $item_image ) : ?>
                                        <img src="<?php echo esc_url( $item_image ); ?>" alt="<?php echo esc_attr( $item_name ); ?>" />
                                    <?php else : ?>
                                        <svg class="ql-hotel-icon-default" style="<?php echo esc_attr( $icon_style ); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    <?php endif; ?>
                                </div>

                                <!-- 内容区域 -->
                                <div class="ql-hotel-amenity-content">
                                    <h4 class="ql-hotel-amenity-name"><?php echo esc_html( $item_name ); ?></h4>
                                    
                                    <?php if ( $item_desc ) : ?>
                                        <p class="ql-hotel-amenity-desc"><?php echo esc_html( $item_desc ); ?></p>
                                    <?php endif; ?>

                                    <?php if ( $item_time ) : ?>
                                        <span class="ql-hotel-amenity-time">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                            <?php echo esc_html( $item_time ); ?>
                                        </span>
                                    <?php endif; ?>
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
