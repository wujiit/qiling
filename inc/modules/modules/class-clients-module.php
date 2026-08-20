<?php
/**
 * Clients Module - 合作客户（增强版）
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Clients_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-groups';
        $this->description = __( '合作客户Logo展示', 'developer-starter' );
    }

    public function get_id() {
        return 'clients';
    }

    public function get_name() {
        return __( '合作客户', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'clients_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '合作客户', 'Trusted By' ) : __( '合作客户', 'developer-starter' ) ),
            array(
                'id' => 'clients_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array( 'id' => 'clients_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'clients_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array(
                'id' => 'clients_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            
            array( 'id' => 'clients_bg_color', 'type' => 'color', 'label' => __( '背景颜色', 'developer-starter' ), 'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ) ),
            
            array( 'id' => 'clients_columns', 'type' => 'select', 'label' => __( '列数', 'developer-starter' ), 'options' => array( '4' => __( '4列', 'developer-starter' ), '5' => __( '5列', 'developer-starter' ), '6' => __( '6列', 'developer-starter' ) ), 'default' => '6' ),
            array( 'id' => 'clients_logo_style', 'type' => 'select', 'label' => __( 'Logo样式', 'developer-starter' ), 'options' => array( 'normal' => __( '原色', 'developer-starter' ), 'grayscale' => __( '黑白', 'developer-starter' ) ), 'default' => 'normal' ),
            array( 'id' => 'clients_auto_scroll', 'type' => 'select', 'label' => __( '自动滚动', 'developer-starter' ), 'options' => array( '' => __( '否', 'developer-starter' ), '1' => __( '是', 'developer-starter' ) ) ),
            array( 'id' => 'clients_scroll_speed', 'type' => 'number', 'label' => __( '滚动速度 (秒)', 'developer-starter' ), 'default' => '30', 'dependency' => array( 'clients_auto_scroll', '==', '1' ) ),
            array( 'id' => 'clients_card_bg', 'type' => 'color', 'label' => __( '卡片背景色', 'developer-starter' ), 'default' => 'var(--color-neutral-0)' ),
            array( 'id' => 'clients_logo_height', 'type' => 'text', 'label' => __( 'Logo高度', 'developer-starter' ), 'default' => '50px' ),
            array( 'id' => 'clients_show_name', 'type' => 'select', 'label' => __( '显示名称', 'developer-starter' ), 'options' => array( '' => __( '否', 'developer-starter' ), '1' => __( '是', 'developer-starter' ) ) ),
            
            // Spacing
            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 80px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 80px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            
            array( 'id' => 'clients_items', 'type' => 'repeater', 'label' => __( '客户列表', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'name', 'type' => 'text', 'label' => __( '客户名称', 'developer-starter' ) ),
                array( 'id' => 'logo', 'type' => 'image', 'label' => __( 'Logo图片', 'developer-starter' ) ),
                array( 'id' => 'link', 'type' => 'text', 'label' => __( '链接 (可选)', 'developer-starter' ) ),
            ) ),
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，客户Logo将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['clients_title'] ) ? $data['clients_title'] : '';
        $subtitle = isset( $data['clients_subtitle'] ) ? $data['clients_subtitle'] : '';
        $bg_color = isset( $data['clients_bg_color'] ) && '' !== trim( (string) $data['clients_bg_color'] )
            ? $data['clients_bg_color']
            : ( isset( $data['module_bg_color'] ) ? trim( (string) $data['module_bg_color'] ) : '' );
        $title_color = isset( $data['clients_title_color'] ) && ! empty( $data['clients_title_color'] ) ? $data['clients_title_color'] : '';
        $columns = isset( $data['clients_columns'] ) && ! empty( $data['clients_columns'] ) ? intval( $data['clients_columns'] ) : 6;
        $logo_style = isset( $data['clients_logo_style'] ) ? $data['clients_logo_style'] : 'normal';
        $auto_scroll = isset( $data['clients_auto_scroll'] ) ? $data['clients_auto_scroll'] : '';
        $scroll_speed = isset( $data['clients_scroll_speed'] ) && ! empty( $data['clients_scroll_speed'] ) ? intval( $data['clients_scroll_speed'] ) : 30;
        $card_bg = isset( $data['clients_card_bg'] ) && ! empty( $data['clients_card_bg'] ) ? $data['clients_card_bg'] : 'var(--color-neutral-0)';
        $logo_height = isset( $data['clients_logo_height'] ) && ! empty( $data['clients_logo_height'] ) ? $data['clients_logo_height'] : '50px';
        $show_name = isset( $data['clients_show_name'] ) ? $data['clients_show_name'] : '';
        $items = isset( $data['clients_items'] ) ? $data['clients_items'] : array();
        
        // Typography & Spacing
        $title_size = isset( $data['clients_title_size'] ) ? $data['clients_title_size'] : '';
        $subtitle_size = isset( $data['clients_subtitle_size'] ) ? $data['clients_subtitle_size'] : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '80px';
        
        if ( empty( $items ) ) {
            $items = array(
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '华为', 'Acme Corp' ) : __( '华为', 'developer-starter' ), 'logo' => '' ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '阿里巴巴', 'Northwind' ) : __( '阿里巴巴', 'developer-starter' ), 'logo' => '' ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '腾讯', 'BrightLabs' ) : __( '腾讯', 'developer-starter' ), 'logo' => '' ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '百度', 'Vertex Studio' ) : __( '百度', 'developer-starter' ), 'logo' => '' ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '京东', 'BluePeak' ) : __( '京东', 'developer-starter' ), 'logo' => '' ),
                array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '字节跳动', 'NovaWorks' ) : __( '字节跳动', 'developer-starter' ), 'logo' => '' ),
            );
        }
        
        // Dynamic Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( ! empty( $bg_color ) ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        
        // Client Item Styles (Card BG)
        $item_style = "background: {$card_bg};";
        
        // Logo Style Class
        $logo_class = "client-logo";
        if ( $logo_style === 'grayscale' ) {
            $logo_class .= " is-grayscale";
        }
        
        // Scroll Animation Duration
        $scroll_style = $auto_scroll === '1' ? "animation-duration: {$scroll_speed}s;" : "";
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-clients" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $items ) ) : ?>
                    <?php if ( $auto_scroll === '1' ) : ?>
                        <!-- Auto Scroll Mode -->
                        <div class="clients-scroll-wrapper">
                            <div class="clients-scroll-track" style="<?php echo esc_attr( $scroll_style ); ?>">
                                <?php 
                                // Duplicate for infinite scroll
                                for ( $loop = 0; $loop < 2; $loop++ ) :
                                    foreach ( $items as $item ) : 
                                        $logo = isset( $item['logo'] ) ? $item['logo'] : '';
                                        $name = isset( $item['name'] ) ? $item['name'] : '';
                                ?>
                                    <div class="client-item" style="<?php echo esc_attr( $item_style ); ?>">
                                        <?php if ( $logo ) : ?>
                                            <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $name ); ?>" style="height: <?php echo esc_attr( $logo_height ); ?>;" class="<?php echo esc_attr( $logo_class ); ?>" />
                                        <?php else : ?>
                                            <span class="client-name-placeholder"><?php echo esc_html( $name ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $show_name === '1' && $logo && $name ) : ?>
                                            <span class="client-name"><?php echo esc_html( $name ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php 
                                    endforeach;
                                endfor;
                                ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- Grid Mode -->
                        <div class="clients-grid grid-cols-<?php echo esc_attr( $columns ); ?>">
                            <?php foreach ( $items as $index => $item ) : 
                                $logo = isset( $item['logo'] ) ? $item['logo'] : '';
                                $name = isset( $item['name'] ) ? $item['name'] : '';
                                $link = isset( $item['link'] ) ? $item['link'] : '';
                                
                                $tag = $link ? 'a' : 'div';
                                $href = $link ? ' href="' . esc_url( $link ) . '" target="_blank"' : '';
                                
                                // Calculate Staggered Animation
                                $anim_attr = '';
                                if ( $enable_anim === 'yes' ) {
                                    $anim_attr = $this->get_staggered_animation_attr( $index );
                                }
                            ?>
                                <<?php echo $tag . $href; ?> class="client-item" style="<?php echo esc_attr( $item_style ); ?>" <?php echo $anim_attr; ?>>
                                    <?php if ( $logo ) : ?>
                                        <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $name ); ?>" style="height: <?php echo esc_attr( $logo_height ); ?>;" class="<?php echo esc_attr( $logo_class ); ?>" />
                                    <?php else : ?>
                                        <span class="client-name-placeholder"><?php echo esc_html( $name ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $show_name === '1' && $logo && $name ) : ?>
                                        <span class="client-name"><?php echo esc_html( $name ); ?></span>
                                    <?php endif; ?>
                                </<?php echo $tag; ?>>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
