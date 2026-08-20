<?php
/**
 * Downloads Module - 下载中心
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Downloads_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-download';
        $this->description = __( '资料下载中心', 'developer-starter' );
    }

    public function get_id() {
        return 'downloads';
    }

    public function get_name() {
        return __( '下载中心', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id' => 'downloads_title',
                'label' => __( '标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '资料下载', 'Downloads' ) : __( '资料下载', 'developer-starter' ),
            ),
            array(
                'id' => 'downloads_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '下载我们的产品资料和技术文档', 'Download our product materials and technical documents.' ) : __( '下载我们的产品资料和技术文档', 'developer-starter' ),
            ),
            // Background Settings
            array(
                'id' => 'downloads_bg_color',
                'label' => __( '背景颜色 (支持渐变)', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
                'description' => __( '例如: var(--color-neutral-0) 或 linear-gradient(to right, var(--color-error), var(--color-primary))', 'developer-starter' ),
            ),
            // Typography Settings
            array(
                'id' => 'downloads_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'downloads_title_size',
                'label' => __( '标题大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '2rem',
                'description' => __( '例如: 2rem, 32px', 'developer-starter' ),
            ),
            array(
                'id' => 'downloads_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'downloads_subtitle_size',
                'label' => __( '副标题大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '1.125rem',
                'description' => __( '例如: 1.125rem, 18px', 'developer-starter' ),
            ),
            array(
                'id' => 'downloads_btn_bg_color',
                'label' => __( '下载按钮背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id' => 'downloads_btn_text_color',
                'label' => __( '下载按钮文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'downloads_btn_border_color', __( '下载按钮边框颜色', 'developer-starter' ) ),
            array(
                'id' => 'downloads_btn_hover_bg_color',
                'label' => __( '下载按钮悬停背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id' => 'downloads_btn_hover_text_color',
                'label' => __( '下载按钮悬停文字颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'downloads_btn_hover_border_color', __( '下载按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随下载按钮悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id'          => 'downloads_badge_bg',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'type'        => 'color',
                'default'     => '',
                'description' => __( '控制文件格式等标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id' => 'downloads_columns',
                'label' => __( '每行列数', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '1' => __( '1列', 'developer-starter' ),
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id' => 'downloads_items',
                'label' => __( '下载项目', 'developer-starter' ),
                'type' => 'repeater',
                'description' => __( '添加下载文件，链接可填写外部URL。文件格式、日期、说明为可选项，填写后会显示', 'developer-starter' ),
                'fields' => array(
                    array( 'id' => 'title', 'label' => __( '文件名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'size', 'label' => __( '文件大小', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'file', 'label' => __( '文件链接(可填外部URL)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'icon', 'label' => __( '图标(emoji或留空)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'format', 'label' => __( '文件格式(可选，如PDF、DOC等)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'date', 'label' => __( '文件日期(可选，如2024-01-01)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'btn_text', 'label' => __( '按钮文案(默认: 下载)', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '下载', 'Download' ) : __( '下载', 'developer-starter' ) ),
                    array( 'id' => 'btn_icon', 'label' => __( '按钮图标(默认: 下载图标)', 'developer-starter' ), 'type' => 'text', 'description' => __( '输入图标类名，如 icon-download', 'developer-starter' ) ),
                    array( 'id' => 'description', 'label' => __( '文件说明(可选)', 'developer-starter' ), 'type' => 'textarea' ),
                ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['downloads_title'] ) ? $data['downloads_title'] : '';
        $subtitle = isset( $data['downloads_subtitle'] ) ? $data['downloads_subtitle'] : '';
        $columns = isset( $data['downloads_columns'] ) ? intval( $data['downloads_columns'] ) : 1;
        $items = isset( $data['downloads_items'] ) ? $data['downloads_items'] : array();
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };
        
        // Style Settings
        $bg_color = isset( $data['downloads_bg_color'] ) && '' !== trim( (string) $data['downloads_bg_color'] )
            ? $data['downloads_bg_color']
            : ( isset( $data['module_bg_color'] ) && '' !== trim( (string) $data['module_bg_color'] ) ? $data['module_bg_color'] : 'var(--color-neutral-0)' );
        $title_color = isset( $data['downloads_title_color'] ) ? $data['downloads_title_color'] : '';
        $title_size = isset( $data['downloads_title_size'] ) ? $data['downloads_title_size'] : '2rem';
        $subtitle_color = isset( $data['downloads_subtitle_color'] ) ? $data['downloads_subtitle_color'] : '';
        $subtitle_size = isset( $data['downloads_subtitle_size'] ) ? $data['downloads_subtitle_size'] : '1.125rem';
        $btn_bg_color = isset( $data['downloads_btn_bg_color'] ) ? $clean_css_value( $data['downloads_btn_bg_color'] ) : '';
        $btn_text_color = isset( $data['downloads_btn_text_color'] ) ? $clean_css_value( $data['downloads_btn_text_color'] ) : '';
        $btn_border_color = isset( $data['downloads_btn_border_color'] ) ? $clean_css_value( $data['downloads_btn_border_color'] ) : '';
        $btn_hover_bg_color = isset( $data['downloads_btn_hover_bg_color'] ) ? $clean_css_value( $data['downloads_btn_hover_bg_color'] ) : '';
        $btn_hover_text_color = isset( $data['downloads_btn_hover_text_color'] ) ? $clean_css_value( $data['downloads_btn_hover_text_color'] ) : '';
        $btn_hover_border_color = isset( $data['downloads_btn_hover_border_color'] ) ? $clean_css_value( $data['downloads_btn_hover_border_color'] ) : '';
        $badge_bg = isset( $data['downloads_badge_bg'] ) ? $clean_css_value( $data['downloads_badge_bg'] ) : '';
        
        if ( empty( $items ) ) {
            $items = array(
                array( 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品手册', 'Product Brochure' ) : __( '产品手册', 'developer-starter' ), 'file' => '', 'size' => '2.5MB', 'icon' => '📄' ),
                array( 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '技术白皮书', 'Technical Whitepaper' ) : __( '技术白皮书', 'developer-starter' ), 'file' => '', 'size' => '1.2MB', 'icon' => '📋' ),
                array( 'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '用户指南', 'User Guide' ) : __( '用户指南', 'developer-starter' ), 'file' => '', 'size' => '3.8MB', 'icon' => '📘' ),
            );
        }
        
        $grid_style = $columns > 1 ? "grid-template-columns: repeat({$columns}, 1fr);" : "";
        
        // Dynamic Title Style
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color && ! in_array( $title_color, array( 'var(--color-neutral-800)', '#' . '1e293b' ), true ) ) $title_style .= "color: {$title_color};";
        
        // Dynamic Subtitle Style
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color && ! in_array( $subtitle_color, array( 'var(--color-text-muted)', '#' . '64748b' ), true ) ) $subtitle_style .= "color: {$subtitle_color};";

        // Dynamic Background Style
        $bg_style = '';
        if ( $bg_color && ! in_array( $bg_color, array( 'var(--color-neutral-0)', '#' . 'ffffff' ), true ) ) {
            $bg_style = strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }

        if ( '' !== $btn_bg_color ) {
            $bg_style .= '--downloads-btn-bg:' . $btn_bg_color . ';';
            $bg_style .= '--downloads-btn-border:' . $btn_bg_color . ';';
        }

        if ( '' !== $btn_text_color ) {
            $bg_style .= '--downloads-btn-text:' . $btn_text_color . ';';
        }

        if ( '' !== $btn_border_color ) {
            $bg_style .= '--downloads-btn-border:' . $btn_border_color . ';';
        }

        if ( '' !== $btn_hover_bg_color ) {
            $bg_style .= '--downloads-btn-hover-bg:' . $btn_hover_bg_color . ';';
            $bg_style .= '--downloads-btn-hover-border:' . $btn_hover_bg_color . ';';
        }

        if ( '' !== $btn_hover_text_color ) {
            $bg_style .= '--downloads-btn-hover-text:' . $btn_hover_text_color . ';';
        }

        if ( '' !== $btn_hover_border_color ) {
            $bg_style .= '--downloads-btn-hover-border:' . $btn_hover_border_color . ';';
        }

        if ( '' !== $badge_bg ) {
            $bg_style .= '--qiling-component-badge-bg:' . $badge_bg . ';';
        }
        ?>
        <section class="module module-downloads section-padding"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?>>
            <div class="container">
                <div class="section-header text-center">
                    <?php if ( $title ) : ?>
                        <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="downloads-list <?php echo $columns > 1 ? 'downloads-grid' : ''; ?>" style="<?php echo $grid_style; ?>">
                    <?php foreach ( $items as $item ) : 
                        $item_title = isset( $item['title'] ) ? $item['title'] : '';
                        $file = isset( $item['file'] ) ? trim( $item['file'] ) : '';
                        $size = isset( $item['size'] ) ? $item['size'] : '';
                        $icon = isset( $item['icon'] ) && $item['icon'] ? $item['icon'] : '📄';
                        $format = isset( $item['format'] ) ? trim( $item['format'] ) : '';
                        $date = isset( $item['date'] ) ? trim( $item['date'] ) : '';
                        $description = isset( $item['description'] ) ? trim( $item['description'] ) : '';
                    ?>
                        <div class="download-item">
                            <div class="download-content">
                                <span class="download-icon">
                                    <?php echo developer_starter_get_icon_html( $icon ); ?>
                                </span>
                                <div class="download-info">
                                    <strong class="download-title"><?php echo esc_html( $item_title ); ?></strong>
                                    <div class="download-meta">
                                        <?php if ( $format ) : ?>
                                            <span class="meta-tag"><?php echo esc_html( $format ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $size ) : ?>
                                            <span class="meta-text"><?php echo esc_html( $size ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $date ) : ?>
                                            <span class="meta-text">📅 <?php echo esc_html( $date ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ( $description ) : ?>
                                        <p class="download-desc"><?php echo esc_html( $description ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="download-action">
                                <?php if ( $file ) : 
                                    $btn_text = isset( $item['btn_text'] ) && $item['btn_text'] ? $item['btn_text'] : __( '下载', 'developer-starter' );
                                    $btn_icon_class = isset( $item['btn_icon'] ) ? trim( $item['btn_icon'] ) : '';
                                ?>
                                    <a href="<?php echo esc_url( $file ); ?>" class="btn-download" target="_blank" download>
                                        <?php 
                                        if ( $btn_icon_class ) {
                                            echo developer_starter_get_icon_html( $btn_icon_class );
                                        } else {
                                            echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
                                        }
                                        ?>
                                        <?php echo esc_html( $btn_text ); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="no-file"><?php esc_html_e( '暂无文件', 'developer-starter' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
