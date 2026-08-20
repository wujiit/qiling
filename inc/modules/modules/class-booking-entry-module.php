<?php
/**
 * Booking Entry Module - 预订入口模块
 * 
 * 展示酒店预订入口，对接启灵表单插件（qiling-forms）
 * 支持选择已创建的表单进行渲染
 *
 * @package Developer_Starter
 * @since 1.0.3
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
// 仅对接独立表单插件 Qiling Forms（qiling-forms）。

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 预订入口模块类
 * 
 * CSS前缀: ql-hotel-booking-
 * 避免与其他模块样式冲突
 */
class Booking_Entry_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-calendar-alt';
        $this->description = __( '酒店预订入口表单', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'booking-entry';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '预订入口', 'developer-starter' );
    }

    /**
     * 获取默认预约表单 ID
     *
     * @return int
     */
    private function ql_hotel_get_default_form_id() {
        $manager = $this->ql_hotel_get_form_manager();
        if ( ! $manager ) {
            return 0;
        }

        $slug = defined( 'Qiling_Forms\Qiling_Form_Manager::DEFAULT_BOOKING_FORM_SLUG' )
            ? \Qiling_Forms\Qiling_Form_Manager::DEFAULT_BOOKING_FORM_SLUG
            : 'appointment-booking';

        $form = $manager->get_form_by_slug( $slug );

        return $form && ! empty( $form->id ) ? absint( $form->id ) : 0;
    }

    /**
     * 获取表单管理器实例（仅插件）
     *
     * @return object|null
     */
    private function ql_hotel_get_form_manager() {
        if ( class_exists( 'Qiling_Forms\\Qiling_Form_Manager' ) ) {
            return \Qiling_Forms\Qiling_Form_Manager::get_instance();
        }
        return null;
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
                'id'      => 'booking_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '在线预订', 'Online Booking' ) : __( '在线预订', 'developer-starter' ),
            ),
            array(
                'id'      => 'booking_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
            ),
            array(
                'id'    => 'booking_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'booking_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预订您的理想房间，开启美好旅程', 'Reserve your ideal stay and start a great trip.' ) : __( '预订您的理想房间，开启美好旅程', 'developer-starter' ),
            ),

            // ========================================
            // 表单设置
            // ========================================
            array(
                'id'      => 'form_id',
                'type'    => 'text',
                'label'   => __( '表单ID', 'developer-starter' ),
                'desc'    => __( '输入 qiling-forms 插件中的表单ID（可在启灵表单后台查看）', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'booking_layout',
                'type'    => 'select',
                'label'   => __( '布局样式', 'developer-starter' ),
                'options' => array(
                    'card'       => __( '卡片样式', 'developer-starter' ),
                    'fullwidth'  => __( '全宽样式', 'developer-starter' ),
                    'sidebar'    => __( '侧边信息', 'developer-starter' ),
                ),
                'default' => 'card',
            ),
            array(
                'id'      => 'card_max_width',
                'type'    => 'text',
                'label'   => __( '卡片最大宽度', 'developer-starter' ),
                'default' => '600px',
                'desc'    => __( '仅卡片样式生效', 'developer-starter' ),
            ),

            // ========================================
            // 侧边信息（侧边布局时显示）
            // ========================================
            array(
                'id'      => 'sidebar_title',
                'type'    => 'text',
                'label'   => __( '侧边标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预订须知', 'Booking Notes' ) : __( '预订须知', 'developer-starter' ),
            ),
            array(
                'id'      => 'sidebar_content',
                'type'    => 'textarea',
                'label'   => __( '侧边内容', 'developer-starter' ),
                'desc'    => __( '每行一条信息', 'developer-starter' ),
                'rows'    => 6,
            ),
            array(
                'id'      => 'contact_phone',
                'type'    => 'text',
                'label'   => __( '预订电话', 'developer-starter' ),
            ),
            array(
                'id'      => 'contact_hours',
                'type'    => 'text',
                'label'   => __( '服务时间', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '24小时服务', '24/7 service' ) : __( '24小时服务', 'developer-starter' ),
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
                'id'      => 'bg_overlay',
                'type'    => 'text',
                'label'   => __( '背景遮罩透明度', 'developer-starter' ),
                'default' => '0.5',
                'desc'    => __( '0-1之间，有背景图时生效', 'developer-starter' ),
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
        $title       = isset( $data['booking_title'] ) && $data['booking_title'] !== '' 
                       ? $data['booking_title'] 
                       : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '在线预订', 'Online Booking' ) : __( '在线预订', 'developer-starter' ) );
        $title_size  = isset( $data['booking_title_size'] ) && $data['booking_title_size'] !== '' 
                       ? $data['booking_title_size'] 
                       : '2rem';
        $title_color = isset( $data['booking_title_color'] ) ? $data['booking_title_color'] : '';
        $subtitle    = isset( $data['booking_subtitle'] ) ? $data['booking_subtitle'] : '';
        
        // 表单
        $form_id = isset( $data['form_id'] ) ? absint( $data['form_id'] ) : 0;
        if ( ! $form_id ) {
            $form_id = $this->ql_hotel_get_default_form_id();
        }
        
        // 布局
        $layout     = isset( $data['booking_layout'] ) ? $data['booking_layout'] : 'card';
        $max_width  = isset( $data['card_max_width'] ) && $data['card_max_width'] !== '' 
                      ? $data['card_max_width'] 
                      : '600px';
        
        // 侧边信息
        $sidebar_title   = isset( $data['sidebar_title'] ) ? $data['sidebar_title'] : '';
        $sidebar_content = isset( $data['sidebar_content'] ) ? $data['sidebar_content'] : '';
        $contact_phone   = isset( $data['contact_phone'] ) ? $data['contact_phone'] : '';
        $contact_hours   = isset( $data['contact_hours'] ) ? $data['contact_hours'] : '';
        
        // 背景设置
        $bg_color  = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image  = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay= isset( $data['bg_overlay'] ) ? floatval( $data['bg_overlay'] ) : 0.5;
        $pt        = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' 
                     ? $data['module_padding_top'] 
                     : '80px';
        $pb        = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' 
                     ? $data['module_padding_bottom'] 
                     : '80px';

        // 防止历史脏值污染内联样式（如 transform/zoom 注入）
        $title_size = $this->ql_hotel_sanitize_length_value( $title_size, '2rem' );
        $max_width  = $this->ql_hotel_sanitize_length_value( $max_width, '600px' );
        $pt         = $this->ql_hotel_sanitize_length_value( $pt, '80px' );
        $pb         = $this->ql_hotel_sanitize_length_value( $pb, '80px' );

        // ========================================
        // 构建样式
        // ========================================
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb}; position: relative;";
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

        // 解析侧边内容
        $sidebar_items = array();
        if ( $sidebar_content ) {
            $sidebar_content = str_replace( array( "\r\n", "\r" ), "\n", $sidebar_content );
            $sidebar_items   = array_filter( array_map( 'trim', explode( "\n", $sidebar_content ) ) );
        }
        ?>

        <section class="module module-booking-entry ql-hotel-booking-layout-<?php echo esc_attr( $layout ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            
            <?php // 背景遮罩 ?>
            <?php if ( $bg_image ) : ?>
                <div class="ql-hotel-booking-overlay" style="background: rgba(var(--qiling-rgb-0-0-0), <?php echo esc_attr( $bg_overlay ); ?>);"></div>
            <?php endif; ?>

            <div class="container" style="position: relative; z-index: 2;">
                <!-- 标题区域 -->
                <div class="section-header text-center">
                    <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>">
                        <?php echo wp_kses_post( $title ); ?>
                    </h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- 表单区域 -->
                <div class="ql-hotel-booking-wrapper ql-hotel-layout-<?php echo esc_attr( $layout ); ?>">
                    
                    <?php // 侧边布局时显示侧边栏 ?>
                    <?php if ( $layout === 'sidebar' ) : ?>
                        <div class="ql-hotel-booking-sidebar">
                            <?php if ( $sidebar_title ) : ?>
                                <h3 class="ql-hotel-sidebar-title"><?php echo esc_html( $sidebar_title ); ?></h3>
                            <?php endif; ?>

                            <?php if ( ! empty( $sidebar_items ) ) : ?>
                                <ul class="ql-hotel-sidebar-list">
                                    <?php foreach ( $sidebar_items as $item ) : ?>
                                        <li>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <?php echo esc_html( $item ); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if ( $contact_phone ) : ?>
                                <div class="ql-hotel-sidebar-contact">
                                    <div class="ql-hotel-contact-label"><?php esc_html_e( '预订热线', 'developer-starter' ); ?></div>
                                    <a href="tel:<?php echo esc_attr( $contact_phone ); ?>" class="ql-hotel-contact-phone">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                        <?php echo esc_html( $contact_phone ); ?>
                                    </a>
                                    <?php if ( $contact_hours ) : ?>
                                        <div class="ql-hotel-contact-hours"><?php echo esc_html( $contact_hours ); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- 表单主体 -->
                    <div class="ql-hotel-booking-form" <?php echo $layout === 'card' ? 'style="max-width: ' . esc_attr( $max_width ) . '; margin: 0 auto;"' : ''; ?>>
                        <?php if ( $form_id && ( shortcode_exists( 'qiling_form' ) || shortcode_exists( 'developer_form' ) ) ) : ?>
                            <?php echo do_shortcode( '[qiling_form id="' . $form_id . '"]' ); ?>
                        <?php elseif ( ! class_exists( 'Qiling_Forms\\Qiling_Form_Manager' ) ) : ?>
                            <div class="ql-hotel-booking-placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                                    <path d="M12 8v4"></path>
                                    <path d="M12 16h.01"></path>
                                </svg>
                                <p><?php esc_html_e( '请先安装并启用「启灵表单（qiling-forms）」插件', 'developer-starter' ); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="ql-hotel-booking-placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <p><?php esc_html_e( '请在模块设置中选择一个预订表单', 'developer-starter' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 清理长度类样式值（调用模块管理器统一规则）
     *
     * @param mixed  $value 原始值
     * @param string $fallback 回退值
     * @return string
     */
    private function ql_hotel_sanitize_length_value( $value, $fallback ) {
        if ( class_exists( 'Developer_Starter\\Modules\\Module_Manager' ) ) {
            $clean = \Developer_Starter\Modules\Module_Manager::sanitize_spacing_value( $value );
            if ( $clean !== '' ) {
                return $clean;
            }
        }

        return $fallback;
    }
}
