<?php
/**
 * Columns Module - 多列布局
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Columns_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-columns';
        $this->description = __( '多列布局模块', 'developer-starter' );
    }

    public function get_id() {
        return 'columns';
    }

    public function get_name() {
        return __( '多列布局', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'columns_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => '' ),
            array(
                'id' => 'columns_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'columns_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            array(
                'id' => 'columns_subtitle',
                'label' => __( '模块副标题', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
            ),
            array(
                'id' => 'columns_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'columns_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            
            array( 'id' => 'columns_count', 'type' => 'select', 'label' => __( '列数', 'developer-starter' ), 'options' => array(
                '2' => __( '2列', 'developer-starter' ),
                '3' => __( '3列', 'developer-starter' ),
                '4' => __( '4列', 'developer-starter' ),
            ), 'default' => '3' ),
            
            array( 'id' => 'columns_items', 'type' => 'repeater', 'label' => __( '列内容', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
                array( 'id' => 'content', 'type' => 'textarea', 'label' => __( '内容', 'developer-starter' ) ),
                array( 'id' => 'image', 'type' => 'image', 'label' => __( '图片', 'developer-starter' ) ),
                array( 'id' => 'link', 'type' => 'text', 'label' => __( '链接(可选)', 'developer-starter' ) ),
            ) ),
            
            // Style Settings
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
            ),
            array( 'id' => 'columns_card_bg', 'label' => __( '卡片背景颜色', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'columns_card_border', 'label' => __( '卡片边框颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'columns_card_hover_border', 'label' => __( '卡片悬停边框颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'columns_item_title_color', 'label' => __( '卡片标题颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'columns_item_text_color', 'label' => __( '卡片正文颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
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
        $title = isset( $data['columns_title'] ) ? $data['columns_title'] : '';
        $subtitle = isset( $data['columns_subtitle'] ) ? $data['columns_subtitle'] : '';
        $columns = isset( $data['columns_count'] ) ? $data['columns_count'] : '3';
        $items = isset( $data['columns_items'] ) ? $data['columns_items'] : array();
        
        // Typography
        $title_size = isset( $data['columns_title_size'] ) ? $data['columns_title_size'] : '';
        $title_color = isset( $data['columns_title_color'] ) ? $data['columns_title_color'] : '';
        $subtitle_size = isset( $data['columns_subtitle_size'] ) ? $data['columns_subtitle_size'] : '';
        $subtitle_color = isset( $data['columns_subtitle_color'] ) ? $data['columns_subtitle_color'] : '';
        
        // Background & Spacing
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        if ( empty( $items ) ) {
            $items = array(
                array( 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '第一列', 'Column One' ) : __( '第一列', 'developer-starter' ), 'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '内容描述', 'Content description' ) : __( '内容描述', 'developer-starter' ), 'image' => '' ),
                array( 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '第二列', 'Column Two' ) : __( '第二列', 'developer-starter' ), 'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '内容描述', 'Content description' ) : __( '内容描述', 'developer-starter' ), 'image' => '' ),
                array( 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '第三列', 'Column Three' ) : __( '第三列', 'developer-starter' ), 'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '内容描述', 'Content description' ) : __( '内容描述', 'developer-starter' ), 'image' => '' ),
            );
        }
        
        // Dynamic Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        foreach ( array( 'columns_card_bg' => '--columns-card-bg', 'columns_card_border' => '--columns-card-border', 'columns_card_hover_border' => '--columns-card-hover-border', 'columns_item_title_color' => '--columns-item-title', 'columns_item_text_color' => '--columns-item-text' ) as $field => $variable ) {
            if ( ! empty( $data[ $field ] ) ) $section_style .= $variable . ':' . $data[ $field ] . ';';
        }
        
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        ?>
        <section class="module module-columns" style="<?php echo esc_attr( $section_style ); ?>">
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
                
                <div class="columns-grid grid-cols-<?php echo esc_attr( $columns ); ?>">
                    <?php foreach ( $items as $item ) : 
                        $item_title = isset( $item['title'] ) ? $item['title'] : '';
                        $item_content = isset( $item['content'] ) ? $item['content'] : '';
                        $item_image = isset( $item['image'] ) ? $item['image'] : '';
                        $item_link = isset( $item['link'] ) ? $item['link'] : '';
                        
                        $tag = $item_link ? 'a' : 'div';
                        $attrs = $item_link ? ' href="' . esc_url( $item_link ) . '"' : '';
                    ?>
                        <<?php echo $tag . $attrs; ?> class="column-item">
                            <?php if ( $item_image ) : ?>
                                <div class="column-image">
                                    <img src="<?php echo esc_url( $item_image ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" />
                                </div>
                            <?php endif; ?>
                            <?php if ( $item_title ) : ?>
                                <h3 class="column-title"><?php echo esc_html( $item_title ); ?></h3>
                            <?php endif; ?>
                            <?php if ( $item_content ) : ?>
                                <div class="column-content"><?php echo wp_kses_post( $item_content ); ?></div>
                            <?php endif; ?>
                        </<?php echo $tag; ?>>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
