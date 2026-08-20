<?php
/**
 * Branches Module - 门店/分支机构
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Branches_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-location';
        $this->description = __( '展示门店/分支机构信息', 'developer-starter' );
    }

    public function get_id() {
        return 'branches';
    }

    public function get_name() {
        return __( '门店机构', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'branches_title', 'label' => __( '标题', 'developer-starter' ), 'type' => 'text', 'default' => __( '全国分支机构', 'developer-starter' ) ),
            array(
                'id' => 'branches_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'branches_title_color', 'label' => __( '标题颜色', 'developer-starter' ), 'type' => 'color' ),

            array( 'id' => 'branches_subtitle', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'text', 'default' => __( '覆盖全国主要城市，为您提供本地化服务', 'developer-starter' ) ),
            array(
                'id' => 'branches_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'branches_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),

            array(
                'id' => 'branches_columns',
                'label' => __( '每行列数', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id' => 'enable_city_filter',
                'label' => __( '启用城市筛选', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '启用', 'developer-starter' ),
                    'no'  => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id' => 'map_provider',
                'label' => __( '默认地图服务', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'auto'    => __( '自动（高德）', 'developer-starter' ),
                    'gaode'   => __( '高德地图', 'developer-starter' ),
                    'baidu'   => __( '百度地图', 'developer-starter' ),
                    'tencent' => __( '腾讯地图', 'developer-starter' ),
                    'google'  => __( 'Google Maps', 'developer-starter' ),
                ),
                'default' => 'auto',
            ),
            array(
                'id'      => 'branches_badge_bg',
                'label'   => __( '标签/徽章背景颜色', 'developer-starter' ),
                'type'    => 'color',
                'default' => '',
                'description' => __( '控制服务标签和营业状态徽章，留空时保留状态默认色并跟随全局徽章颜色', 'developer-starter' ),
            ),
            array(
                'id' => 'show_booking_button',
                'label' => __( '显示预约按钮', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id' => 'navigation_button_text',
                'label' => __( '导航按钮文案', 'developer-starter' ),
                'type' => 'text',
                'default' => __( '导航到店', 'developer-starter' ),
            ),
            array(
                'id' => 'navigation_button_bg_color',
                'label' => __( '导航按钮背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响“导航到店”按钮，留空时跟随全局设计', 'developer-starter' ),
            ),
            array(
                'id' => 'navigation_button_text_color',
                'label' => __( '导航按钮文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响“导航到店”按钮，留空时跟随全局设计', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'navigation_button_border_color', __( '导航按钮边框颜色', 'developer-starter' ) ),
            array(
                'id' => 'navigation_button_hover_bg_color',
                'label' => __( '导航按钮悬停背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响“导航到店”按钮悬停状态，留空时跟随全局设计', 'developer-starter' ),
            ),
            array(
                'id' => 'navigation_button_hover_text_color',
                'label' => __( '导航按钮悬停文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '只影响“导航到店”按钮悬停状态，留空时跟随全局设计', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'navigation_button_hover_border_color', __( '导航按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随导航按钮悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id' => 'booking_button_text',
                'label' => __( '预约按钮文案', 'developer-starter' ),
                'type' => 'text',
                'default' => __( '在线预约', 'developer-starter' ),
                'dependency' => array( 'show_booking_button', '==', 'yes' ),
            ),
            array(
                'id' => 'booking_button_bg_color',
                'label' => __( '预约按钮背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'dependency' => array( 'show_booking_button', '==', 'yes' ),
                'description' => __( '只影响“在线预约”按钮，留空时跟随全局设计', 'developer-starter' ),
            ),
            array(
                'id' => 'booking_button_text_color',
                'label' => __( '预约按钮文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'dependency' => array( 'show_booking_button', '==', 'yes' ),
                'description' => __( '只影响“在线预约”按钮，留空时跟随全局设计', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'booking_button_border_color', __( '预约按钮边框颜色', 'developer-starter' ), '', array( 'dependency' => array( 'show_booking_button', '==', 'yes' ) ) ),
            array(
                'id' => 'booking_button_hover_bg_color',
                'label' => __( '预约按钮悬停背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'dependency' => array( 'show_booking_button', '==', 'yes' ),
                'description' => __( '只影响“在线预约”按钮悬停状态，留空时跟随全局设计', 'developer-starter' ),
            ),
            array(
                'id' => 'booking_button_hover_text_color',
                'label' => __( '预约按钮悬停文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'dependency' => array( 'show_booking_button', '==', 'yes' ),
                'description' => __( '只影响“在线预约”按钮悬停状态，留空时跟随全局设计', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'booking_button_hover_border_color', __( '预约按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随预约按钮悬停背景颜色。', 'developer-starter' ), array( 'dependency' => array( 'show_booking_button', '==', 'yes' ) ) ),

            array(
                'id' => 'branches_list',
                'label' => __( '门店列表', 'developer-starter' ),
                'type' => 'repeater',
                'description' => __( '新增门店时可填写坐标，系统会自动生成地图导航链接', 'developer-starter' ),
                'fields' => array(
                    array( 'id' => 'name', 'label' => __( '门店名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'city', 'label' => __( '所在城市', 'developer-starter' ), 'type' => 'text', 'description' => __( '用于前台筛选，如：北京', 'developer-starter' ) ),
                    array( 'id' => 'status', 'label' => __( '营业状态', 'developer-starter' ), 'type' => 'select', 'options' => array(
                        'open'   => __( '营业中', 'developer-starter' ),
                        'busy'   => __( '客流较高', 'developer-starter' ),
                        'closed' => __( '暂停营业', 'developer-starter' ),
                        'coming' => __( '即将开业', 'developer-starter' ),
                    ) ),
                    array( 'id' => 'address', 'label' => __( '地址', 'developer-starter' ), 'type' => 'textarea' ),
                    array( 'id' => 'phone', 'label' => __( '电话', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'email', 'label' => __( '邮箱', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'hours', 'label' => __( '营业时间', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'services', 'label' => __( '服务标签', 'developer-starter' ), 'type' => 'text', 'description' => __( '多个标签用逗号分隔，如：器械区, 私教, 康复', 'developer-starter' ) ),
                    array( 'id' => 'transport', 'label' => __( '交通提示', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'lat', 'label' => __( '纬度(可选)', 'developer-starter' ), 'type' => 'text', 'description' => __( '如：39.9042', 'developer-starter' ) ),
                    array( 'id' => 'lng', 'label' => __( '经度(可选)', 'developer-starter' ), 'type' => 'text', 'description' => __( '如：116.4074', 'developer-starter' ) ),
                    array( 'id' => 'map_url', 'label' => __( '地图链接(可选)', 'developer-starter' ), 'type' => 'text', 'description' => __( '优先使用手动填写链接', 'developer-starter' ) ),
                    array( 'id' => 'booking_url', 'label' => __( '预约链接(可选)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'image', 'label' => __( '图片(可选)', 'developer-starter' ), 'type' => 'text' ),
                ),
            ),

            array(
                'id' => 'module_bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),

            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
                'dependency' => array( 'module_bg_type', '==', 'color' ),
            ),

            array(
                'id' => 'module_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),

            array(
                'id' => 'module_bg_overlay',
                'label' => __( '背景遮罩浓度', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '0' => __( '无遮罩', 'developer-starter' ),
                    '0.1' => '10%',
                    '0.2' => '20%',
                    '0.3' => '30%',
                    '0.4' => '40%',
                    '0.5' => '50%',
                    '0.6' => '60%',
                    '0.7' => '70%',
                    '0.8' => '80%',
                    '0.9' => '90%',
                ),
                'default' => '0',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),

            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),

            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
        );
    }

    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        $title = isset( $data['branches_title'] ) ? $data['branches_title'] : __( '全国分支机构', 'developer-starter' );
        $subtitle = isset( $data['branches_subtitle'] ) ? $data['branches_subtitle'] : __( '覆盖全国主要城市，为您提供本地化服务', 'developer-starter' );

        $title_color = isset( $data['branches_title_color'] ) ? $data['branches_title_color'] : '';
        $title_size = isset( $data['branches_title_size'] ) ? $data['branches_title_size'] : '';
        $subtitle_color = isset( $data['branches_subtitle_color'] ) ? $data['branches_subtitle_color'] : '';
        $subtitle_size = isset( $data['branches_subtitle_size'] ) ? $data['branches_subtitle_size'] : '';

        $columns = isset( $data['branches_columns'] ) ? intval( $data['branches_columns'] ) : 3;
        if ( ! in_array( $columns, array( 2, 3, 4 ), true ) ) {
            $columns = 3;
        }

        $enable_city_filter = ( isset( $data['enable_city_filter'] ) ? $data['enable_city_filter'] : 'yes' ) === 'yes';
        $show_booking_button = ( isset( $data['show_booking_button'] ) ? $data['show_booking_button'] : 'yes' ) === 'yes';
        $badge_bg = isset( $data['branches_badge_bg'] ) ? $clean_css_value( $data['branches_badge_bg'] ) : '';
        $navigation_button_text = ! empty( $data['navigation_button_text'] ) ? $data['navigation_button_text'] : __( '导航到店', 'developer-starter' );
        $booking_button_text = ! empty( $data['booking_button_text'] ) ? $data['booking_button_text'] : __( '在线预约', 'developer-starter' );
        $navigation_button_bg_color = isset( $data['navigation_button_bg_color'] ) ? $clean_css_value( $data['navigation_button_bg_color'] ) : '';
        $navigation_button_text_color = isset( $data['navigation_button_text_color'] ) ? $clean_css_value( $data['navigation_button_text_color'] ) : '';
        $navigation_button_border_color = isset( $data['navigation_button_border_color'] ) ? $clean_css_value( $data['navigation_button_border_color'] ) : '';
        $navigation_button_hover_bg_color = isset( $data['navigation_button_hover_bg_color'] ) ? $clean_css_value( $data['navigation_button_hover_bg_color'] ) : '';
        $navigation_button_hover_text_color = isset( $data['navigation_button_hover_text_color'] ) ? $clean_css_value( $data['navigation_button_hover_text_color'] ) : '';
        $navigation_button_hover_border_color = isset( $data['navigation_button_hover_border_color'] ) ? $clean_css_value( $data['navigation_button_hover_border_color'] ) : '';
        $booking_button_bg_color = isset( $data['booking_button_bg_color'] ) ? $clean_css_value( $data['booking_button_bg_color'] ) : '';
        $booking_button_text_color = isset( $data['booking_button_text_color'] ) ? $clean_css_value( $data['booking_button_text_color'] ) : '';
        $booking_button_border_color = isset( $data['booking_button_border_color'] ) ? $clean_css_value( $data['booking_button_border_color'] ) : '';
        $booking_button_hover_bg_color = isset( $data['booking_button_hover_bg_color'] ) ? $clean_css_value( $data['booking_button_hover_bg_color'] ) : '';
        $booking_button_hover_text_color = isset( $data['booking_button_hover_text_color'] ) ? $clean_css_value( $data['booking_button_hover_text_color'] ) : '';
        $booking_button_hover_border_color = isset( $data['booking_button_hover_border_color'] ) ? $clean_css_value( $data['booking_button_hover_border_color'] ) : '';
        $map_provider = isset( $data['map_provider'] ) ? sanitize_key( $data['map_provider'] ) : 'auto';
        if ( ! in_array( $map_provider, array( 'auto', 'gaode', 'baidu', 'tencent', 'google' ), true ) ) {
            $map_provider = 'auto';
        }

        $branches = isset( $data['branches_list'] ) && is_array( $data['branches_list'] ) ? $data['branches_list'] : array();
        if ( empty( $branches ) ) {
            $branches = array(
                array(
                    'name' => __( '北京总部店', 'developer-starter' ),
                    'city' => __( '北京', 'developer-starter' ),
                    'status' => 'open',
                    'address' => __( '北京市朝阳区建国路88号SOHO现代城A座', 'developer-starter' ),
                    'phone' => '010-88888888',
                    'email' => 'beijing@example.com',
                    'hours' => __( '周一至周日 09:00-20:00', 'developer-starter' ),
                    'services' => __( '门店零售, 私人顾问, 上门服务', 'developer-starter' ),
                ),
                array(
                    'name' => __( '上海旗舰店', 'developer-starter' ),
                    'city' => __( '上海', 'developer-starter' ),
                    'status' => 'busy',
                    'address' => __( '上海市浦东新区陆家嘴环路1000号恒生银行大厦', 'developer-starter' ),
                    'phone' => '021-88888888',
                    'email' => 'shanghai@example.com',
                    'hours' => __( '周一至周日 10:00-21:00', 'developer-starter' ),
                    'services' => __( '体验中心, 快速安装, 企业团购', 'developer-starter' ),
                ),
                array(
                    'name' => __( '深圳南山店', 'developer-starter' ),
                    'city' => __( '深圳', 'developer-starter' ),
                    'status' => 'coming',
                    'address' => __( '深圳市南山区科技园南区高新南七道', 'developer-starter' ),
                    'phone' => '0755-88888888',
                    'email' => 'shenzhen@example.com',
                    'hours' => __( '预计下月开业', 'developer-starter' ),
                    'services' => __( '展示体验, 售后服务', 'developer-starter' ),
                ),
            );
        }

        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $bg_overlay = isset( $data['module_bg_overlay'] ) ? $data['module_bg_overlay'] : '0';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        if ( $badge_bg ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};";
            $section_style .= "--branches-status-open-bg: {$badge_bg};--branches-status-busy-bg: {$badge_bg};--branches-status-closed-bg: {$badge_bg};--branches-status-coming-bg: {$badge_bg};";
            $section_style .= "--branches-status-open-text: var(--qiling-component-badge-text);--branches-status-busy-text: var(--qiling-component-badge-text);--branches-status-closed-text: var(--qiling-component-badge-text);--branches-status-coming-text: var(--qiling-component-badge-text);";
        }
        if ( $navigation_button_bg_color ) {
            $section_style .= "--branches-map-btn-bg: {$navigation_button_bg_color};--branches-map-btn-border: {$navigation_button_bg_color};";
        }
        if ( $navigation_button_text_color ) {
            $section_style .= "--branches-map-btn-text: {$navigation_button_text_color};";
        }
        if ( $navigation_button_border_color ) {
            $section_style .= "--branches-map-btn-border: {$navigation_button_border_color};";
        }
        if ( $navigation_button_hover_bg_color ) {
            $section_style .= "--branches-map-btn-hover-bg: {$navigation_button_hover_bg_color};--branches-map-btn-hover-border: {$navigation_button_hover_bg_color};";
        }
        if ( $navigation_button_hover_text_color ) {
            $section_style .= "--branches-map-btn-hover-text: {$navigation_button_hover_text_color};";
        }
        if ( $navigation_button_hover_border_color ) {
            $section_style .= "--branches-map-btn-hover-border: {$navigation_button_hover_border_color};";
        }
        if ( $booking_button_bg_color ) {
            $section_style .= "--branches-book-btn-bg: {$booking_button_bg_color};--branches-book-btn-border: {$booking_button_bg_color};";
        }
        if ( $booking_button_text_color ) {
            $section_style .= "--branches-book-btn-text: {$booking_button_text_color};";
        }
        if ( $booking_button_border_color ) {
            $section_style .= "--branches-book-btn-border: {$booking_button_border_color};";
        }
        if ( $booking_button_hover_bg_color ) {
            $section_style .= "--branches-book-btn-hover-bg: {$booking_button_hover_bg_color};--branches-book-btn-hover-border: {$booking_button_hover_bg_color};";
        }
        if ( $booking_button_hover_text_color ) {
            $section_style .= "--branches-book-btn-hover-text: {$booking_button_hover_text_color};";
        }
        if ( $booking_button_hover_border_color ) {
            $section_style .= "--branches-book-btn-hover-border: {$booking_button_hover_border_color};";
        }

        $title_style = '';
        if ( $title_size ) {
            $title_style .= "font-size: {$title_size};";
        }
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        $subtitle_style = '';
        if ( $subtitle_size ) {
            $subtitle_style .= "font-size: {$subtitle_size};";
        }
        if ( $subtitle_color ) {
            $subtitle_style .= "color: {$subtitle_color};";
        }

        $grid_class = 'grid-cols-' . $columns;
        $filter_id = 'branch-filter-' . wp_rand( 1000, 9999 );
        $cities = array();
        ?>
        <section id="<?php echo esc_attr( $filter_id ); ?>" class="module module-branches" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>

            <div class="container module-branches-container">
                <div class="section-header text-center">
                    <?php if ( $title ) : ?>
                        <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $branches ) ) : ?>
                    <?php foreach ( $branches as $branch ) : ?>
                        <?php
                        $city_name = sanitize_text_field( $branch['city'] ?? '' );
                        if ( $city_name !== '' ) {
                            $cities[] = $city_name;
                        }
                        ?>
                    <?php endforeach; ?>

                    <?php $cities = array_values( array_unique( $cities ) ); ?>
                    <?php if ( $enable_city_filter && count( $cities ) > 1 ) : ?>
                        <div class="branches-city-filter">
                            <button type="button" class="branches-city-btn is-active" data-city-filter="all"><?php esc_html_e( '全部城市', 'developer-starter' ); ?></button>
                            <?php foreach ( $cities as $city_item ) : ?>
                                <button type="button" class="branches-city-btn" data-city-filter="<?php echo esc_attr( $city_item ); ?>"><?php echo esc_html( $city_item ); ?></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="branches-grid <?php echo esc_attr( $grid_class ); ?>">
                        <?php foreach ( $branches as $branch ) : ?>
                            <?php
                            $name = sanitize_text_field( $branch['name'] ?? '' );
                            $address = sanitize_textarea_field( $branch['address'] ?? '' );
                            $city = sanitize_text_field( $branch['city'] ?? '' );
                            $phone = sanitize_text_field( $branch['phone'] ?? '' );
                            $email = sanitize_email( $branch['email'] ?? '' );
                            $hours = sanitize_text_field( $branch['hours'] ?? '' );
                            $services = $this->parse_branch_tags( $branch['services'] ?? '' );
                            $transport = sanitize_text_field( $branch['transport'] ?? '' );
                            $status = sanitize_key( $branch['status'] ?? 'open' );
                            $status_label = $this->get_branch_status_label( $status );

                            $image = '';
                            if ( function_exists( 'developer_starter_get_media_url' ) ) {
                                $image = developer_starter_get_media_url( $branch['image'] ?? '' );
                            }
                            if ( ! $image ) {
                                $image = $branch['image'] ?? '';
                            }

                            $lat = sanitize_text_field( $branch['lat'] ?? '' );
                            $lng = sanitize_text_field( $branch['lng'] ?? '' );
                            $manual_map = $branch['map_url'] ?? '';
                            $map_link = $manual_map ? esc_url_raw( $manual_map ) : $this->build_map_url( $map_provider, $name, $address, $lat, $lng );

                            $booking_url = esc_url_raw( $branch['booking_url'] ?? '' );
                            $city_attr = $city !== '' ? $city : 'other';
                            $phone_href = preg_replace( '/[^0-9\+\-]/', '', $phone );
                            ?>
                            <article class="branch-card" data-city="<?php echo esc_attr( $city_attr ); ?>">
                                <?php if ( $image ) : ?>
                                    <div class="branch-image-wrapper">
                                        <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="branch-image" loading="lazy" />
                                    </div>
                                <?php else : ?>
                                    <div class="branch-bar"></div>
                                <?php endif; ?>

                                <div class="branch-content">
                                    <div class="branch-head">
                                        <h3 class="branch-name">
                                            <span class="branch-icon-pin">📍</span>
                                            <?php echo esc_html( $name ); ?>
                                        </h3>
                                        <?php if ( $status_label ) : ?>
                                            <span class="branch-status status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ( $city ) : ?>
                                        <p class="branch-city"><?php echo esc_html( $city ); ?></p>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $services ) ) : ?>
                                        <div class="branch-tags">
                                            <?php foreach ( $services as $service_tag ) : ?>
                                                <span class="branch-tag"><?php echo esc_html( $service_tag ); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="branch-info-list">
                                        <?php if ( $address ) : ?>
                                            <div class="branch-info-item">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="branch-icon"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                <span><?php echo esc_html( $address ); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $phone ) : ?>
                                            <div class="branch-info-item">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="branch-icon"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                                                <a href="tel:<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone ); ?></a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $email ) : ?>
                                            <div class="branch-info-item">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="branch-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $hours ) : ?>
                                            <div class="branch-info-item">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="branch-icon"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                <span><?php echo esc_html( $hours ); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $transport ) : ?>
                                            <div class="branch-info-item">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="branch-icon"><rect x="4" y="3" width="16" height="16" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
                                                <span><?php echo esc_html( $transport ); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="branch-actions">
                                        <?php if ( $map_link ) : ?>
                                            <a href="<?php echo esc_url( $map_link ); ?>" target="_blank" rel="noopener noreferrer" class="branch-map-btn">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                                                <?php echo esc_html( $navigation_button_text ); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ( $show_booking_button && $booking_url ) : ?>
                                            <a href="<?php echo esc_url( $booking_url ); ?>" class="branch-book-btn">
                                                <?php echo esc_html( $booking_button_text ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ( $enable_city_filter && count( $cities ) > 1 ) : ?>
            <script>
                (function () {
                    var wrap = document.getElementById('<?php echo esc_js( $filter_id ); ?>');
                    if (!wrap) {
                        return;
                    }

                    var buttons = wrap.querySelectorAll('.branches-city-btn');
                    var cards = wrap.querySelectorAll('.branch-card');
                    if (!buttons.length || !cards.length) {
                        return;
                    }

                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            var city = button.getAttribute('data-city-filter') || 'all';
                            buttons.forEach(function (btn) { btn.classList.remove('is-active'); });
                            button.classList.add('is-active');

                            cards.forEach(function (card) {
                                var cardCity = card.getAttribute('data-city') || '';
                                card.style.display = (city === 'all' || city === cardCity) ? '' : 'none';
                            });
                        });
                    });
                })();
            </script>
        <?php endif; ?>
        <?php
    }

    /**
     * 服务标签解析
     */
    private function parse_branch_tags( $raw ) {
        if ( is_array( $raw ) ) {
            $tags = $raw;
        } else {
            $tags = preg_split( '/[,，|\/]+/', (string) $raw );
        }

        $clean = array();
        foreach ( $tags as $tag ) {
            $tag = sanitize_text_field( trim( (string) $tag ) );
            if ( $tag !== '' ) {
                $clean[] = $tag;
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * 营业状态文案
     */
    private function get_branch_status_label( $status ) {
        $map = array(
            'open'   => __( '营业中', 'developer-starter' ),
            'busy'   => __( '客流较高', 'developer-starter' ),
            'closed' => __( '暂停营业', 'developer-starter' ),
            'coming' => __( '即将开业', 'developer-starter' ),
        );

        return isset( $map[ $status ] ) ? $map[ $status ] : '';
    }

    /**
     * 自动构建地图链接
     */
    private function build_map_url( $provider, $name, $address, $lat, $lng ) {
        $provider = $provider === 'auto' ? 'gaode' : $provider;
        $name = sanitize_text_field( $name );
        $address = sanitize_text_field( $address );
        $lat = trim( (string) $lat );
        $lng = trim( (string) $lng );

        $has_coords = is_numeric( $lat ) && is_numeric( $lng );
        $query = rawurlencode( trim( $name . ' ' . $address ) );

        if ( $provider === 'baidu' ) {
            if ( $has_coords ) {
                return 'https://api.map.baidu.com/marker?location=' . rawurlencode( $lat . ',' . $lng )
                    . '&title=' . rawurlencode( $name )
                    . '&content=' . rawurlencode( $address )
                    . '&output=html';
            }
            return 'https://map.baidu.com/search/' . $query;
        }

        if ( $provider === 'tencent' ) {
            if ( $has_coords ) {
                return 'https://apis.map.qq.com/uri/v1/marker?marker=coord:'
                    . rawurlencode( $lat . ',' . $lng )
                    . ';title:' . rawurlencode( $name )
                    . ';addr:' . rawurlencode( $address );
            }
            return 'https://apis.map.qq.com/uri/v1/search?keyword=' . $query;
        }

        if ( $provider === 'google' ) {
            if ( $has_coords ) {
                return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $lat . ',' . $lng );
            }
            return 'https://www.google.com/maps/search/?api=1&query=' . $query;
        }

        // 默认高德
        if ( $has_coords ) {
            return 'https://uri.amap.com/marker?position=' . rawurlencode( $lng . ',' . $lat )
                . '&name=' . rawurlencode( $name )
                . '&src=wordpress.qiling';
        }

        return 'https://uri.amap.com/search?keyword=' . $query;
    }
}
