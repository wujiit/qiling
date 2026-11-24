<?php
/**
 * 搭配画册与场景购模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Lookbook_Module extends Module_Base {

    public function __construct() {
        $this->category = 'business';
        $this->icon = 'dashicons-groups'; // 使用群组图标代表搭配
        $this->description = __( '展示模特搭配或场景组合，点击弹出详情购买', 'qiling' );
    }

    public function get_id() {
        return 'lookbook';
    }

    public function get_name() {
        return __( '搭配画册', 'qiling' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'lookbook_title', 'type' => 'text', 'label' => __( '模块标题', 'qiling' ), 'default' => __( '当季精选搭配', 'qiling' ) ),
            
            // 搭配画册中继器
            array(
                'id' => 'lookbook_items',
                'label' => __( '搭配列表', 'qiling' ),
                'type' => 'repeater',
                'fields' => array(
                    // Cover
                    array( 'id' => 'cover_image', 'label' => __( '封面/模特图 (大图)', 'qiling' ), 'type' => 'image', 'description' => __( '如果未上传视频，则显示此图。', 'qiling' ) ),
                    array( 'id' => 'video_360', 'label' => __( '360°展示视频 (MP4链接)', 'qiling' ), 'type' => 'text', 'description' => __( '直接输入视频链接(MP4格式)。前台将自动循环播放。', 'qiling' ) ),
                    array( 'id' => 'name', 'label' => __( '搭配名称/编号', 'qiling' ), 'type' => 'text', 'default' => 'LOOK 01' ),
                    array( 'id' => 'desc', 'label' => __( '搭配描述', 'qiling' ), 'type' => 'textarea', 'rows' => 2 ),
                    array( 'id' => 'btn_text', 'label' => __( '按钮文案', 'qiling' ), 'type' => 'text', 'default' => __( '查看详情', 'qiling' ), 'description' => __( '默认为“查看详情”', 'qiling' ) ),
                    array(
                        'id'      => 'enable_shop_look',
                        'label'   => __( '启用模特图同款打点标记 (Shop The Look)', 'qiling' ),
                        'type'    => 'select',
                        'options' => array(
                            'no'  => __( '关闭', 'qiling' ),
                            'yes' => __( '开启', 'qiling' ),
                        ),
                        'default' => 'no',
                    ),
                    
                    // 关联单品配置（最多支持 5 组单品信息）
                    
                    array( 'type' => 'header', 'label' => __( '单品 1', 'qiling' ) ),
                    array( 'id' => 'item_1_img', 'label' => __( '单品1 图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_1_title', 'label' => __( '单品1 名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_1_price', 'label' => __( '单品1 价格', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_1_link', 'label' => __( '单品1 链接', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_1_pin_x', 'label' => __( '单品1 模特图标记X百分比 (0-100，如 65)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_1_pin_y', 'label' => __( '单品1 模特图标记Y百分比 (0-100，如 42)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_1_spec_name', 'label' => __( '单品1 主规格名', 'qiling' ), 'type' => 'text', 'description' => __( '例如：默认同款 / 黑色', 'qiling' ) ),
                    array( 'id' => 'item_1_spec_2_name', 'label' => __( '单品1 规格2名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_1_spec_2_img', 'label' => __( '单品1 规格2图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_1_spec_3_name', 'label' => __( '单品1 规格3名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_1_spec_3_img', 'label' => __( '单品1 规格3图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_1_btn_bg', 'label' => __( '单品1 按钮背景色', 'qiling' ), 'type' => 'text', 'default' => 'var(--qiling-color-000000)', 'description' => __( '支持颜色代码或渐变色(如 linear-gradient(...))', 'qiling' ) ),
                    $this->get_button_border_color_field( 'item_1_btn_border', __( '单品1 按钮边框颜色', 'qiling' ) ),
                    
                    array( 'type' => 'header', 'label' => __( '单品 2', 'qiling' ) ),
                    array( 'id' => 'item_2_img', 'label' => __( '单品2 图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_2_title', 'label' => __( '单品2 名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_price', 'label' => __( '单品2 价格', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_link', 'label' => __( '单品2 链接', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_pin_x', 'label' => __( '单品2 模特图标记X百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_pin_y', 'label' => __( '单品2 模特图标记Y百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_spec_name', 'label' => __( '单品2 主规格名', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_spec_2_name', 'label' => __( '单品2 规格2名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_spec_2_img', 'label' => __( '单品2 规格2图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_2_spec_3_name', 'label' => __( '单品2 规格3名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_2_spec_3_img', 'label' => __( '单品2 规格3图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_2_btn_bg', 'label' => __( '单品2 按钮背景色', 'qiling' ), 'type' => 'text', 'default' => 'var(--qiling-color-000000)' ),
                    $this->get_button_border_color_field( 'item_2_btn_border', __( '单品2 按钮边框颜色', 'qiling' ) ),
                    
                    array( 'type' => 'header', 'label' => __( '单品 3', 'qiling' ) ),
                    array( 'id' => 'item_3_img', 'label' => __( '单品3 图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_3_title', 'label' => __( '单品3 名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_price', 'label' => __( '单品3 价格', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_link', 'label' => __( '单品3 链接', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_pin_x', 'label' => __( '单品3 模特图标记X百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_pin_y', 'label' => __( '单品3 模特图标记Y百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_spec_name', 'label' => __( '单品3 主规格名', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_spec_2_name', 'label' => __( '单品3 规格2名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_spec_2_img', 'label' => __( '单品3 规格2图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_3_spec_3_name', 'label' => __( '单品3 规格3名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_3_spec_3_img', 'label' => __( '单品3 规格3图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_3_btn_bg', 'label' => __( '单品3 按钮背景色', 'qiling' ), 'type' => 'text', 'default' => 'var(--qiling-color-000000)' ),
                    $this->get_button_border_color_field( 'item_3_btn_border', __( '单品3 按钮边框颜色', 'qiling' ) ),
                    
                    array( 'type' => 'header', 'label' => __( '单品 4', 'qiling' ) ),
                    array( 'id' => 'item_4_img', 'label' => __( '单品4 图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_4_title', 'label' => __( '单品4 名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_price', 'label' => __( '单品4 价格', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_link', 'label' => __( '单品4 链接', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_pin_x', 'label' => __( '单品4 模特图标记X百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_pin_y', 'label' => __( '单品4 模特图标记Y百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_spec_name', 'label' => __( '单品4 主规格名', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_spec_2_name', 'label' => __( '单品4 规格2名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_spec_2_img', 'label' => __( '单品4 规格2图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_4_spec_3_name', 'label' => __( '单品4 规格3名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_4_spec_3_img', 'label' => __( '单品4 规格3图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_4_btn_bg', 'label' => __( '单品4 按钮背景色', 'qiling' ), 'type' => 'text', 'default' => 'var(--qiling-color-000000)' ),
                    $this->get_button_border_color_field( 'item_4_btn_border', __( '单品4 按钮边框颜色', 'qiling' ) ),
                    
                    array( 'type' => 'header', 'label' => __( '单品 5', 'qiling' ) ),
                    array( 'id' => 'item_5_img', 'label' => __( '单品5 图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_5_title', 'label' => __( '单品5 名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_price', 'label' => __( '单品5 价格', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_link', 'label' => __( '单品5 链接', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_pin_x', 'label' => __( '单品5 模特图标记X百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_pin_y', 'label' => __( '单品5 模特图标记Y百分比 (0-100)', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_spec_name', 'label' => __( '单品5 主规格名', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_spec_2_name', 'label' => __( '单品5 规格2名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_spec_2_img', 'label' => __( '单品5 规格2图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_5_spec_3_name', 'label' => __( '单品5 规格3名称', 'qiling' ), 'type' => 'text' ),
                    array( 'id' => 'item_5_spec_3_img', 'label' => __( '单品5 规格3图片', 'qiling' ), 'type' => 'image' ),
                    array( 'id' => 'item_5_btn_bg', 'label' => __( '单品5 按钮背景色', 'qiling' ), 'type' => 'text', 'default' => 'var(--qiling-color-000000)' ),
                    $this->get_button_border_color_field( 'item_5_btn_border', __( '单品5 按钮边框颜色', 'qiling' ) ),
                ),
                'default_items' => array(
                     array( 
                         'name' => __( '都市行者', 'qiling' ), 
                         'desc' => __( '适合日常通勤的舒适穿搭', 'qiling' ),
                         'item_1_title' => __( '纯棉T恤', 'qiling' ), 'item_1_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 99 ) : '¥99',
                         'item_2_title' => __( '休闲长裤', 'qiling' ), 'item_2_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 199 ) : '¥199',
                     ),
                ),
            ),
            
            array( 'id' => 'lookbook_columns', 'label' => __( '每行显示', 'qiling' ), 'type' => 'select', 'options' => array( '2' => __( '2列', 'qiling' ), '3' => __( '3列', 'qiling' ) ), 'default' => '3' ),
            
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'qiling' ),
                'type' => 'select',
                'options' => array( 'yes' => __( '开启', 'qiling' ), 'no' => __( '关闭', 'qiling' ) ),
                'default' => 'yes',
                'description' => __( '开启后，搭配卡片将依次延迟显示', 'qiling' ),
            ),
            
            array( 'id' => 'module_bg_color', 'label' => __( '背景颜色', 'qiling' ), 'type' => 'color', 'default' => 'var(--color-neutral-50)' ),
            array( 'id' => 'module_padding_top', 'label' => __( '上边距', 'qiling' ), 'type' => 'text', 'default' => '80px' ),
            array( 'id' => 'module_padding_bottom', 'label' => __( '下边距', 'qiling' ), 'type' => 'text', 'default' => '80px' ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['lookbook_title'] ) ? $data['lookbook_title'] : '';
        $items = isset( $data['lookbook_items'] ) ? $data['lookbook_items'] : array();
        $columns = isset( $data['lookbook_columns'] ) ? intval( $data['lookbook_columns'] ) : 3;
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        $module_uid = 'lookbook-' . uniqid();
        
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : 'var(--color-neutral-50)';
        $pt = isset( $data['module_padding_top'] ) ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) ? $data['module_padding_bottom'] : '80px';
        
        $bg_prop = strpos( $bg_color, 'gradient' ) !== false ? 'background' : 'background-color';
        $style_vars = "{$bg_prop}: {$bg_color}; padding-top: {$pt}; padding-bottom: {$pb};";
        
        // 弹窗与网格样式
        ?>
        <style>
            #<?php echo esc_attr( $module_uid ); ?> .lookbook-cover-wrap {
                position: relative;
                width: 100%;
                aspect-ratio: 3/4;
                overflow: hidden;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lookbook-cover-wrap .lookbook-cover,
            #<?php echo esc_attr( $module_uid ); ?> .lookbook-cover-wrap .lookbook-video {
                width: 100%;
                height: 100%;
                aspect-ratio: 3/4;
                object-fit: cover;
                display: block;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pins-overlay {
                position: absolute;
                inset: 0;
                pointer-events: none;
                z-index: 5;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-shop-pin {
                position: absolute;
                transform: translate(-50%, -50%);
                width: 26px;
                height: 26px;
                pointer-events: auto;
                cursor: pointer;
                z-index: 8;
                outline: none;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-pulse {
                position: absolute;
                inset: -4px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.65);
                animation: lbPinPulse 2s cubic-bezier(0.16, 1, 0.3, 1) infinite;
            }
            @keyframes lbPinPulse {
                0% { transform: scale(0.8); opacity: 0.9; }
                70% { transform: scale(1.6); opacity: 0; }
                100% { transform: scale(1.6); opacity: 0; }
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-dot {
                position: absolute;
                inset: 3px;
                border-radius: 50%;
                background: #ffffff;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
                transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background 0.2s ease;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-shop-pin:hover .lb-pin-dot,
            #<?php echo esc_attr( $module_uid ); ?> .lb-shop-pin:focus .lb-pin-dot,
            #<?php echo esc_attr( $module_uid ); ?> .lb-shop-pin.is-active .lb-pin-dot {
                transform: scale(1.25);
                background: var(--color-primary, #2563eb);
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-popover {
                position: absolute;
                bottom: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%) translateY(4px);
                background: rgba(15, 23, 42, 0.92);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 8px;
                padding: 8px 12px;
                color: #ffffff;
                font-size: 12px;
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
                z-index: 20;
                display: flex;
                flex-direction: column;
                gap: 3px;
                align-items: center;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-popover::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 5px solid transparent;
                border-top-color: rgba(15, 23, 42, 0.92);
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-shop-pin:hover .lb-pin-popover,
            #<?php echo esc_attr( $module_uid ); ?> .lb-shop-pin:focus .lb-pin-popover,
            #<?php echo esc_attr( $module_uid ); ?> .lb-shop-pin.is-active .lb-pin-popover {
                opacity: 1;
                visibility: visible;
                transform: translateX(-50%) translateY(0);
                pointer-events: auto;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-name {
                font-weight: 600;
                color: #ffffff;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-price {
                font-weight: 700;
                color: #38bdf8;
                font-size: 13px;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-btn {
                display: inline-block;
                padding: 3px 8px;
                margin-top: 4px;
                border-radius: 4px;
                background: #2563eb;
                color: #ffffff;
                font-size: 11px;
                text-decoration: none;
                font-weight: 600;
            }
            #<?php echo esc_attr( $module_uid ); ?> .lb-pin-btn:hover {
                background: #1d4ed8;
                color: #ffffff;
            }
        </style>
        <section id="<?php echo esc_attr( $module_uid ); ?>" class="module module-lookbook" style="<?php echo esc_attr( $style_vars ); ?>">
            <div class="container">
                <?php if ( $title ) : ?>
                    <div class="section-header text-center" style="margin-bottom: var(--qiling-space-40);">
                        <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <div class="lookbook-grid lookbook-grid-<?php echo esc_attr( $columns ); ?>">
                        <?php foreach ( $items as $index => $item ) :
                            $cover = isset( $item['cover_image'] ) ? esc_url_raw( (string) $item['cover_image'] ) : '';
                            $name = isset( $item['name'] ) ? sanitize_text_field( (string) $item['name'] ) : '';
                            $desc = isset( $item['desc'] ) ? sanitize_textarea_field( (string) $item['desc'] ) : '';
                            $btn_text_raw = isset( $item['btn_text'] ) ? sanitize_text_field( (string) $item['btn_text'] ) : '';
                            $btn_text = $btn_text_raw !== '' ? $btn_text_raw : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看详情', 'View Details' ) : __( '查看详情', 'qiling' ) );
                            $video = isset( $item['video_360'] ) ? esc_url_raw( (string) $item['video_360'] ) : '';
                            
                            // 收集条目数据用于 JSON 输出
                            $look_items = array();
                            for ( $i = 1; $i <= 5; $i++ ) {
                                $item_title = isset( $item["item_{$i}_title"] ) ? sanitize_text_field( (string) $item["item_{$i}_title"] ) : '';
                                if ( $item_title !== '' ) {
                                    $item_image = isset( $item["item_{$i}_img"] ) ? esc_url_raw( (string) $item["item_{$i}_img"] ) : '';
                                    // 收集规格数据
                                    $specs = array();
                                    
                                    // 默认主规格
                                    $main_spec_name = isset( $item["item_{$i}_spec_name"] ) && ! empty( $item["item_{$i}_spec_name"] ) ? sanitize_text_field( (string) $item["item_{$i}_spec_name"] ) : '';
                                    if ( ! empty( $item_image ) ) {
                                         $specs[] = array(
                                             'name' => $main_spec_name ? $main_spec_name : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '默认', 'Default' ) : __( '默认', 'qiling' ) ),
                                             'img'  => $item_image,
                                             'is_main' => true
                                         );
                                    }
                                    
                                    // 附加规格
                                    for ( $s = 2; $s <= 3; $s++ ) {
                                        if ( ! empty( $item["item_{$i}_spec_{$s}_name"] ) && ! empty( $item["item_{$i}_spec_{$s}_img"] ) ) {
                                            $specs[] = array(
                                                'name' => sanitize_text_field( (string) $item["item_{$i}_spec_{$s}_name"] ),
                                                'img'  => esc_url_raw( (string) $item["item_{$i}_spec_{$s}_img"] )
                                            );
                                        }
                                    }

                                    $item_link = isset( $item["item_{$i}_link"] ) ? esc_url_raw( (string) $item["item_{$i}_link"], array( 'http', 'https', 'mailto', 'tel' ) ) : '';
                                    if ( $item_link === '' ) {
                                        $item_link = '#';
                                    }

                                    $item_btn_bg = isset( $item["item_{$i}_btn_bg"] ) ? sanitize_text_field( (string) $item["item_{$i}_btn_bg"] ) : 'var(--qiling-color-000000)';
                                    if ( $item_btn_bg === '' ) {
                                        $item_btn_bg = 'var(--qiling-color-000000)';
                                    }
                                    $item_btn_border = isset( $item["item_{$i}_btn_border"] ) ? sanitize_text_field( (string) $item["item_{$i}_btn_border"] ) : '';
                                    if ( $item_btn_border === '' ) {
                                        $item_btn_border = $item_btn_bg;
                                    }
                                    
                                    $look_items[] = array(
                                        'img' => $item_image,
                                        'title' => $item_title,
                                        'price' => isset( $item["item_{$i}_price"] ) ? sanitize_text_field( (string) $item["item_{$i}_price"] ) : '',
                                        'link' => $item_link,
                                        'btn_bg' => $item_btn_bg,
                                        'btn_border' => $item_btn_border,
                                        'specs' => $specs,
                                    );
                                }
                            }

                            // 收集同款打点标注 (Shop The Look Pins)
                            $enable_shop_look = isset( $item['enable_shop_look'] ) ? (string) $item['enable_shop_look'] : 'no';
                            $shop_pins = array();
                            if ( 'yes' === $enable_shop_look ) {
                                for ( $p = 1; $p <= 5; $p++ ) {
                                    $pin_x = isset( $item["item_{$p}_pin_x"] ) && '' !== trim( (string) $item["item_{$p}_pin_x"] ) ? floatval( $item["item_{$p}_pin_x"] ) : null;
                                    $pin_y = isset( $item["item_{$p}_pin_y"] ) && '' !== trim( (string) $item["item_{$p}_pin_y"] ) ? floatval( $item["item_{$p}_pin_y"] ) : null;
                                    $pin_title = isset( $item["item_{$p}_title"] ) ? sanitize_text_field( (string) $item["item_{$p}_title"] ) : '';
                                    if ( null !== $pin_x && null !== $pin_y && '' !== $pin_title ) {
                                        $shop_pins[] = array(
                                            'x'     => $pin_x,
                                            'y'     => $pin_y,
                                            'title' => $pin_title,
                                            'price' => isset( $item["item_{$p}_price"] ) ? sanitize_text_field( (string) $item["item_{$p}_price"] ) : '',
                                            'link'  => isset( $item["item_{$p}_link"] ) ? esc_url_raw( (string) $item["item_{$p}_link"] ) : '',
                                        );
                                    }
                                }
                            }
                            
                            $json_data = htmlspecialchars( json_encode( array(
                                'name' => $name,
                                'desc' => $desc,
                                'btn_text' => $btn_text,
                                'img' => $cover,
                                'video' => $video,
                                'items' => $look_items
                            ) ), ENT_QUOTES, 'UTF-8' );
                            
                            // Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="lookbook-card" onclick="openLookbookModal(this, '<?php echo esc_js( $module_uid ); ?>')" data-look="<?php echo $json_data; ?>" <?php echo $anim_attr; ?>>
                                <div class="lookbook-cover-wrap">
                                    <?php if ( $video ) : ?>
                                        <video src="<?php echo esc_url( $video ); ?>" class="lookbook-video" autoplay muted loop playsinline></video>
                                    <?php elseif ( $cover ) : ?>
                                        <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="lookbook-cover">
                                    <?php else : ?>
                                        <div class="lookbook-cover lookbook-cover-placeholder">
                                            <div class="lookbook-placeholder-inner">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lookbook-placeholder-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                                <span class="lookbook-placeholder-label"><?php echo esc_html( $name ? $name : __( '当季风尚画册', 'qiling' ) ); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ( 'yes' === $enable_shop_look && ! empty( $shop_pins ) ) : ?>
                                        <div class="lb-pins-overlay">
                                            <?php foreach ( $shop_pins as $spin ) : ?>
                                                <div class="lb-shop-pin" style="left: <?php echo esc_attr( $spin['x'] ); ?>%; top: <?php echo esc_attr( $spin['y'] ); ?>%;" onclick="event.stopPropagation();" tabindex="0">
                                                    <span class="lb-pin-pulse"></span>
                                                    <span class="lb-pin-dot"></span>
                                                    <div class="lb-pin-popover">
                                                        <span class="lb-pin-title"><?php echo esc_html( $spin['title'] ); ?></span>
                                                        <?php if ( ! empty( $spin['price'] ) ) : ?>
                                                            <span class="lb-pin-price"><?php echo esc_html( $spin['price'] ); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ( ! empty( $spin['link'] ) && '#' !== $spin['link'] ) : ?>
                                                            <a href="<?php echo esc_url( $spin['link'] ); ?>" class="lb-pin-btn" target="_blank"><?php esc_html_e( '购买同款', 'qiling' ); ?></a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="lookbook-meta">
                                    <h3 class="lookbook-name"><?php echo esc_html( $name ); ?></h3>
                                    <?php if ( $desc ) : ?>
                                        <p class="lookbook-desc"><?php echo esc_html( $desc ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Shared Modal -->
        <div class="lb-modal" data-lb-modal data-lb-owner="<?php echo esc_attr( $module_uid ); ?>">
            <div class="lb-modal-content">
                <span class="lb-close" onclick="closeLookbookModal('<?php echo esc_js( $module_uid ); ?>')">&times;</span>
                
                <div class="lb-left">
                    <!-- Dynamic Content -->
                    <h2 class="lb-look-name" data-lb-title></h2>
                    <p class="lb-look-desc" data-lb-desc></p>
                    
                    <div class="lb-items-list" data-lb-items>
                        <!-- Items injected here -->
                    </div>
                </div>
                
                <div class="lb-right" data-lb-main-wrap>
                    <img src="" alt="" class="lb-model-img" data-lb-main-img>
                    <video class="lb-model-video" data-lb-main-video muted playsinline loop autoplay></video>
                </div>
            </div>
        </div>
        
        <script>
        function lbSafeHref(url) {
            var value = typeof url === 'string' ? url.trim() : '';
            if (!value) {
                return '#';
            }

            try {
                var parsed = new URL(value, window.location.origin);
                var protocol = parsed.protocol.toLowerCase();
                if (protocol === 'http:' || protocol === 'https:' || protocol === 'mailto:' || protocol === 'tel:') {
                    return parsed.href;
                }
            } catch (e) {
                return '#';
            }
            return '#';
        }

        function lbSafeMediaUrl(url) {
            var value = typeof url === 'string' ? url.trim() : '';
            if (!value) {
                return '';
            }

            try {
                var parsed = new URL(value, window.location.origin);
                var protocol = parsed.protocol.toLowerCase();
                if (protocol === 'http:' || protocol === 'https:') {
                    return parsed.href;
                }
            } catch (e) {
                return '';
            }

            return '';
        }

        function lbSafeColor(value) {
            var color = typeof value === 'string' ? value.trim() : '';
            if (!color || color.length > 120) {
                return 'var(--qiling-color-000000)';
            }

            var colorPattern = /^(#[0-9a-fA-F]{3,8}|r(?:gb|gba)\([^<>{}]{1,80}\)|h(?:sl|sla)\([^<>{}]{1,80}\)|var\(--[a-zA-Z0-9_-]+\)|linear-gradient\([^<>{}]{1,110}\))$/;
            return colorPattern.test(color) ? color : 'var(--qiling-color-000000)';
        }

        function lbParseLookData(raw) {
            if (typeof raw !== 'string' || raw.trim() === '') {
                return null;
            }

            try {
                var parsed = JSON.parse(raw);
                return parsed && typeof parsed === 'object' ? parsed : null;
            } catch (e) {
                return null;
            }
        }

        function lbGetModal(rootId) {
            var root = document.getElementById(rootId);
            if (!root) {
                return null;
            }

            var scope = root.closest('[data-qiling-module-scope]') || root.parentElement;
            return scope ? scope.querySelector('[data-lb-modal][data-lb-owner="' + rootId + '"]') : null;
        }

        function lbBuildLookItem(modal, item, btnText) {
            var itemDiv = document.createElement('div');
            itemDiv.className = 'lb-item';

            var thumb = document.createElement('img');
            thumb.className = 'lb-item-thumb';
            thumb.alt = item && item.title ? String(item.title) : '';
            thumb.loading = 'lazy';
            thumb.decoding = 'async';
            var thumbSrc = lbSafeMediaUrl(item && item.img ? item.img : '');
            if (thumbSrc) {
                thumb.src = thumbSrc;
            }
            itemDiv.appendChild(thumb);

            var info = document.createElement('div');
            info.className = 'lb-item-info';

            var title = document.createElement('span');
            title.className = 'lb-item-title';
            title.textContent = item && item.title ? String(item.title) : '';
            info.appendChild(title);

            var price = document.createElement('span');
            price.className = 'lb-item-price';
            price.textContent = item && item.price ? String(item.price) : '';
            info.appendChild(price);

            if (item && item.specs && item.specs.length > 1) {
                var specsWrap = document.createElement('div');
                specsWrap.className = 'lb-specs';

                item.specs.forEach(function(spec, index) {
                    var specBtn = document.createElement('span');
                    specBtn.className = 'lb-spec-item' + (index === 0 ? ' active' : '');
                    specBtn.textContent = spec && spec.name ? String(spec.name) : ('<?php echo esc_js( __( '规格', 'qiling' ) ); ?>' + (index + 1));
                    specBtn.setAttribute('data-img', lbSafeMediaUrl(spec && spec.img ? spec.img : ''));
                    specBtn.addEventListener('click', function(event) {
                        lbSwitchSpec(specBtn, event);
                    });
                    specsWrap.appendChild(specBtn);
                });

                info.appendChild(specsWrap);
            }

            var link = document.createElement('a');
            link.className = 'lb-item-btn';
            link.href = lbSafeHref(item && item.link ? item.link : '#');
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = btnText;
            var safeBtnColor = lbSafeColor(item && item.btn_bg ? item.btn_bg : 'var(--qiling-color-000000)');
            var safeBtnBorder = lbSafeColor(item && item.btn_border ? item.btn_border : safeBtnColor);
            link.style.background = safeBtnColor;
            link.style.borderColor = safeBtnBorder;
            link.addEventListener('click', function(event) {
                event.stopPropagation();
            });
            info.appendChild(link);

            itemDiv.appendChild(info);
            itemDiv.addEventListener('click', function() {
                var showImg = thumbSrc;
                var activeSpec = itemDiv.querySelector('.lb-spec-item.active');
                if (activeSpec) {
                    showImg = activeSpec.getAttribute('data-img') || '';
                }
                lbSwapImage(modal, itemDiv, showImg);
            });

            return itemDiv;
        }

        function openLookbookModal(element, rootId) {
            var root = document.getElementById(rootId);
            if (!root) {
                return;
            }

            var data = lbParseLookData(element.getAttribute('data-look'));
            if (!data) {
                return;
            }

            var modal = lbGetModal(rootId);
            if (!modal) {
                return;
            }

            var titleEl = modal.querySelector('[data-lb-title]');
            var descEl = modal.querySelector('[data-lb-desc]');
            if (titleEl) {
                titleEl.textContent = data.name ? String(data.name) : '';
            }
            if (descEl) {
                descEl.textContent = data.desc ? String(data.desc) : '';
            }

            var mainImg = modal.querySelector('[data-lb-main-img]');
            var mainVideo = modal.querySelector('[data-lb-main-video]');
            if (!mainImg || !mainVideo) {
                return;
            }

            var safeVideo = lbSafeMediaUrl(data.video || '');
            var safeCover = lbSafeMediaUrl(data.img || '');

            if (safeVideo) {
                mainImg.style.display = 'none';
                mainVideo.style.display = 'block';
                mainVideo.src = safeVideo;
                mainVideo.play();
                modal.dataset.origType = 'video';
                modal.dataset.origSrc = safeVideo;
            } else {
                mainVideo.pause();
                mainVideo.style.display = 'none';
                if (safeCover) {
                    mainImg.src = safeCover;
                } else {
                    mainImg.removeAttribute('src');
                }
                mainImg.style.display = safeCover ? 'block' : 'none';
                modal.dataset.origType = 'image';
                modal.dataset.origSrc = safeCover;
            }

            var itemsContainer = modal.querySelector('[data-lb-items]');
            if (!itemsContainer) {
                return;
            }
            itemsContainer.innerHTML = '';

            var btnText = (data.btn_text && String(data.btn_text).trim() !== '')
                ? String(data.btn_text)
                : '<?php echo esc_js( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看详情', 'View Details' ) : __( '查看详情', 'qiling' ) ); ?>';

            if (data.items && data.items.length > 0) {
                data.items.forEach(function(item) {
                    itemsContainer.appendChild(lbBuildLookItem(modal, item, btnText));
                });
            } else {
                var empty = document.createElement('p');
                empty.className = 'text-gray-500';
                empty.textContent = '<?php echo esc_js( __( '暂无关联单品', 'qiling' ) ); ?>';
                itemsContainer.appendChild(empty);
            }

            modal.classList.add('is-active');
            if (!modal._lbBodyScrollLocked) {
                modal._lbPreviousBodyOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                modal._lbBodyScrollLocked = true;
            }
        }

        function lbSwitchSpec(el, e) {
            if (e) {
                e.stopPropagation();
            }

            if (!el || !el.parentNode) {
                return;
            }

            var siblings = el.parentNode.children;
            for (var i = 0; i < siblings.length; i++) {
                siblings[i].classList.remove('active');
            }
            el.classList.add('active');

            var imgSrc = el.getAttribute('data-img') || '';
            var parentItem = el.closest('.lb-item');
            var modal = el.closest('[data-lb-modal]');
            lbSwapImage(modal, parentItem, imgSrc);
        }

        function lbSwapImage(modal, el, imgSrc) {
            var safeImg = lbSafeMediaUrl(imgSrc);
            if (!modal || !safeImg) {
                return;
            }

            var items = modal.querySelectorAll('.lb-item');
            items.forEach(function(item) {
                item.classList.remove('active');
            });
            if (el) {
                el.classList.add('active');
            }

            var mainImg = modal.querySelector('[data-lb-main-img]');
            var mainVideo = modal.querySelector('[data-lb-main-video]');
            if (!mainImg || !mainVideo) {
                return;
            }

            mainVideo.pause();
            mainVideo.style.display = 'none';
            mainImg.src = safeImg;
            mainImg.style.display = 'block';
        }

        function closeLookbookModal(rootId) {
            var modal = lbGetModal(rootId);
            if (!modal) {
                return;
            }

            var mainVideo = modal.querySelector('[data-lb-main-video]');
            if (mainVideo) {
                mainVideo.pause();
            }

            modal.classList.remove('is-active');
            if (modal._lbBodyScrollLocked) {
                document.body.style.overflow = modal._lbPreviousBodyOverflow || '';
                modal._lbBodyScrollLocked = false;
            }
        }

        // 点击外部遮罩关闭弹窗
        (function() {
            var root = document.getElementById('<?php echo esc_js( $module_uid ); ?>');
            if (!root) {
                return;
            }

            var modal = lbGetModal('<?php echo esc_js( $module_uid ); ?>');
            if (!modal) {
                return;
            }

            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeLookbookModal('<?php echo esc_js( $module_uid ); ?>');
                }
            });
        })();
        </script>
        <?php
    }
}
