<?php
/**
 * Qiling Image Guide Module - 小图引导模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Qiling_Image_Guide_Module extends Module_Base {

    public function __construct() {
        $this->category = 'component'; // 组件类
        $this->icon = 'dashicons-images-alt2';
        $this->description = __( '小图引导模块，支持自定义图片、标题、副标题及链接跳转', 'developer-starter' );
    }

    public function get_id() {
        return 'qiling_image_guide';
    }

    public function get_name() {
        return __( '小图引导模块', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'guide_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'guide_subtitle', 'type' => 'text', 'label' => __( '模块副标题', 'developer-starter' ), 'default' => '' ),
            
            array( 'id' => 'guide_bg_color', 'type' => 'color', 'label' => __( '背景颜色', 'developer-starter' ), 'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ) ),
            
            array( 'id' => 'guide_columns', 'type' => 'select', 'label' => __( '列数 (PC端)', 'developer-starter' ), 'options' => array( '2' => __( '2列', 'developer-starter' ), '3' => __( '3列', 'developer-starter' ), '4' => __( '4列', 'developer-starter' ), '5' => __( '5列', 'developer-starter' ), '6' => __( '6列', 'developer-starter' ) ), 'default' => '4' ),
            array( 'id' => 'guide_item_height', 'type' => 'text', 'label' => __( '图片高度', 'developer-starter' ), 'default' => '160px', 'desc' => __( '支持 px, rem, vw 等单位', 'developer-starter' ) ),
            array( 'id' => 'guide_item_radius', 'type' => 'text', 'label' => __( '圆角大小', 'developer-starter' ), 'default' => '8px' ),
            array( 'id' => 'guide_gap', 'type' => 'text', 'label' => __( '图片间距', 'developer-starter' ), 'default' => '20px' ),
            
            // Spacing
            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '40px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '40px',
            ),
            
            array( 'id' => 'guide_items', 'type' => 'repeater', 'label' => __( '图片列表', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'image', 'type' => 'image', 'label' => __( '背景图片', 'developer-starter' ) ),
                array( 'id' => 'title', 'type' => 'text', 'label' => __( '主标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '标题文本', 'Demo Title' ) : __( '标题文本', 'developer-starter' ) ),
                array( 'id' => 'subtitle', 'type' => 'text', 'label' => __( '副标题 (可选)', 'developer-starter' ), 'default' => '' ),
                array( 'id' => 'link', 'type' => 'text', 'label' => __( '跳转链接', 'developer-starter' ) ),
            ) ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['guide_title'] ) ? $data['guide_title'] : '';
        $subtitle = isset( $data['guide_subtitle'] ) ? $data['guide_subtitle'] : '';
        $bg_color = isset( $data['guide_bg_color'] ) ? $data['guide_bg_color'] : '';
        $columns = isset( $data['guide_columns'] ) ? intval( $data['guide_columns'] ) : 4;
        $item_height = isset( $data['guide_item_height'] ) && $data['guide_item_height'] !== '' ? $data['guide_item_height'] : '160px';
        $item_radius = isset( $data['guide_item_radius'] ) && $data['guide_item_radius'] !== '' ? $data['guide_item_radius'] : '8px';
        $gap = isset( $data['guide_gap'] ) && $data['guide_gap'] !== '' ? $data['guide_gap'] : '20px';
        
        $items = isset( $data['guide_items'] ) ? $data['guide_items'] : array();
        
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '40px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '40px';
        
        if ( empty( $items ) ) {
            // 默认演示数据
            $items = array_fill( 0, 4, array( 
                'image' => '', 
                'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '演示标题', 'Demo Title' ) : __( '演示标题', 'developer-starter' ),
                'subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '演示副标题', 'Demo Subtitle' ) : __( '演示副标题', 'developer-starter' ),
                'link' => '#' 
            ) );
        }
        
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( ! empty( $bg_color ) ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        ?>
        <section class="module module-qiling-image-guide" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="qiling-guide-grid guide-cols-<?php echo esc_attr( $columns ); ?>" style="--guide-gap: <?php echo esc_attr( $gap ); ?>; --guide-height: <?php echo esc_attr( $item_height ); ?>; --guide-radius: <?php echo esc_attr( $item_radius ); ?>;">
                    <?php foreach ( $items as $item ) : 
                        $img = isset( $item['image'] ) ? $item['image'] : '';
                        $item_title = isset( $item['title'] ) ? $item['title'] : '';
                        $item_subtitle = isset( $item['subtitle'] ) ? $item['subtitle'] : '';
                        $link = isset( $item['link'] ) ? $item['link'] : '';
                        
                        $tag = $link ? 'a' : 'div';
                        $href = $link ? ' href="' . esc_url( $link ) . '" target="_blank"' : '';
                    ?>
                        <<?php echo $tag . $href; ?> class="qiling-guide-item">
                            <div class="qiling-guide-bg">
                                <?php if ( $img ) : ?>
                                    <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" />
                                <?php else : ?>
                                    <div class="qiling-guide-placeholder"></div>
                                <?php endif; ?>
                                <div class="qiling-guide-overlay"></div>
                            </div>
                            
                            <div class="qiling-guide-content">
                                <?php if ( $item_title ) : ?>
                                    <h4 class="qiling-guide-title"><?php echo esc_html( $item_title ); ?></h4>
                                <?php endif; ?>
                                
                                <div class="qiling-guide-line"></div>
                                
                                <?php if ( $item_subtitle ) : ?>
                                    <p class="qiling-guide-sub"><?php echo esc_html( $item_subtitle ); ?></p>
                                <?php endif; ?>
                            </div>
                        </<?php echo $tag; ?>>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
