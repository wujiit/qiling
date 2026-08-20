<?php
/**
 * Software Category Module - 软件分类展示模块
 *
 * 按分类展示软件列表，支持多种布局样式，避免审美疲劳
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Software_Category_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-grid-view';
        $this->description = __( '按分类展示软件列表，支持多种布局样式', 'developer-starter' );
    }

    public function get_id() {
        return 'software_category';
    }

    public function get_name() {
        return __( '软件分类展示', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // 这里的字段定义更丰富一些
            array( 'id' => 'content_settings_sep', 'type' => 'separator', 'label' => __( '内容设置', 'developer-starter' ) ),
            array( 'id' => 'sc_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ) ),
            array( 'id' => 'sc_subtitle', 'type' => 'text', 'label' => __( '模块副标题', 'developer-starter' ) ),
            
            // 样式设置
            array( 'id' => 'style_settings_sep', 'type' => 'separator', 'label' => __( '背景与样式', 'developer-starter' ) ),
            array( 
                'id' => 'sc_bg_type', 
                'type' => 'select', 
                'label' => __( '背景类型', 'developer-starter' ), 
                'options' => array(
                    'color' => __( '纯色背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array( 
                'id' => 'sc_bg_color', 
                'type' => 'color', 
                'label' => __( '背景颜色', 'developer-starter' ), 
                'default' => 'var(--color-surface-alt)',
                'dependency' => array( 'sc_bg_type', '==', 'color' )
            ),
            array( 
                'id' => 'sc_bg_image', 
                'type' => 'image', 
                'label' => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'sc_bg_type', '==', 'image' )
            ),
            array( 
                'id' => 'sc_bg_overlay', 
                'type' => 'color', 
                'label' => __( '图片遮罩颜色', 'developer-starter' ),
                'desc' => __( '设置带透明度的颜色以增加文字可读性', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-0-0-0-05)',
                'dependency' => array( 'sc_bg_type', '==', 'image' )
            ),
            
            // 排版设置
            array( 'id' => 'typography_settings_sep', 'type' => 'separator', 'label' => __( '排版设置', 'developer-starter' ) ),
            array( 'id' => 'sc_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ), 'default' => 'var(--color-heading)' ),
            array( 'id' => 'sc_title_size', 'type' => 'text', 'label' => __( '标题大小', 'developer-starter' ), 'default' => '1.5rem', 'desc' => __( '例如: 24px 或 1.5rem', 'developer-starter' ) ),
            array( 'id' => 'sc_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ), 'default' => 'var(--color-text-muted)' ),
            array( 'id' => 'sc_subtitle_size', 'type' => 'text', 'label' => __( '副标题大小', 'developer-starter' ), 'default' => '0.95rem' ),

            array( 'id' => 'layout_settings_sep', 'type' => 'separator', 'label' => __( '布局与显示', 'developer-starter' ) ),
            
            array( 'id' => 'sc_layout', 'type' => 'select', 'label' => __( '布局方式', 'developer-starter' ), 'options' => array( 
                'grid' => __( '网格布局', 'developer-starter' ), 
                'list' => __( '列表布局', 'developer-starter' ), 
                'compact' => __( '紧凑布局', 'developer-starter' ), 
                'horizontal' => __( '水平滚动', 'developer-starter' ) 
            ), 'default' => 'grid' ),
            array( 'id' => 'sc_columns', 'type' => 'number', 'label' => __( '列数 (网格/紧凑)', 'developer-starter' ), 'default' => '4' ),
            
            array( 'id' => 'sc_categories', 'type' => 'text', 'label' => __( '软件分类ID (逗号分隔)', 'developer-starter' ) ),
            array( 'id' => 'sc_count', 'type' => 'number', 'label' => __( '显示数量', 'developer-starter' ), 'default' => '8' ),
            array( 'id' => 'sc_orderby', 'type' => 'select', 'label' => __( '排序方式', 'developer-starter' ), 'options' => array( 
                'date' => __( '发布时间', 'developer-starter' ), 
                'downloads' => __( '下载量', 'developer-starter' ), 
                'name' => __( '名称', 'developer-starter' ), 
                'modified' => __( '更新时间', 'developer-starter' ), 
                'random' => __( '随机', 'developer-starter' ) 
            ), 'default' => 'date' ),
            
            array( 'id' => 'sc_card_bg', 'type' => 'color', 'label' => __( '卡片背景色', 'developer-starter' ), 'default' => 'var(--qiling-component-card-bg)' ),
            array(
                'id'          => 'sc_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'description' => __( '控制版本等标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
                'default'     => '',
            ),
            array( 'id' => 'sc_icon_size', 'type' => 'text', 'label' => __( '图标大小', 'developer-starter' ), 'default' => '56px' ),
            
            array( 'id' => 'sc_show_version', 'type' => 'select', 'label' => __( '显示版本', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            array( 'id' => 'sc_show_date', 'type' => 'select', 'label' => __( '显示日期', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            array( 'id' => 'sc_show_downloads', 'type' => 'select', 'label' => __( '显示下载量', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '0' ),
            array( 'id' => 'sc_show_desc', 'type' => 'select', 'label' => __( '显示描述', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '0' ),
            
            array( 'id' => 'sc_show_btn', 'type' => 'select', 'label' => __( '显示按钮', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '0' ),
            array( 'id' => 'sc_btn_text', 'type' => 'text', 'label' => __( '按钮文字', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看更多', 'View More' ) : __( '查看更多', 'developer-starter' ) ),
            array( 'id' => 'sc_btn_link', 'type' => 'text', 'label' => __( '按钮链接', 'developer-starter' ) ),
            $this->get_button_border_color_field( 'sc_btn_border_color' ),
            $this->get_button_border_color_field( 'sc_btn_hover_border_color', __( '按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随按钮悬停背景颜色。', 'developer-starter' ) ),
        );
    }

    public function render( $data = array() ) {
        if ( ! function_exists( 'developer_starter_qiapp_is_available' ) ) {
            $helper_file = defined( 'DEVELOPER_STARTER_INC' )
                ? DEVELOPER_STARTER_INC . '/core/helpers/helpers-qiapp-theme.php'
                : get_template_directory() . '/inc/core/helpers/helpers-qiapp-theme.php';
            if ( is_string( $helper_file ) && file_exists( $helper_file ) ) {
                require_once $helper_file;
            }
        }

        // 检查启灵App插件是否激活
        if ( ! function_exists( 'developer_starter_qiapp_is_available' ) || ! developer_starter_qiapp_is_available() ) {
            $this->render_plugin_notice();
            return;
        }

        // 基础配置
        $title = isset( $data['sc_title'] ) ? $data['sc_title'] : '';
        $subtitle = isset( $data['sc_subtitle'] ) ? $data['sc_subtitle'] : '';
        
        // 背景配置
        $bg_type = isset( $data['sc_bg_type'] ) && in_array( $data['sc_bg_type'], array( 'color', 'image' ), true )
            ? $data['sc_bg_type']
            : 'color';
        $bg_color = $this->sanitize_css_color_value(
            isset( $data['sc_bg_color'] ) ? $data['sc_bg_color'] : '',
            'var(--color-surface-alt)'
        );
        $bg_image = isset( $data['sc_bg_image'] )
            ? $this->sanitize_css_url_value( $data['sc_bg_image'] )
            : '';
        $bg_overlay = $this->sanitize_css_color_value(
            isset( $data['sc_bg_overlay'] ) ? $data['sc_bg_overlay'] : '',
            'var(--qiling-color-rgba-0-0-0-05)'
        );
        
        // 排版配置
        $title_color = $this->sanitize_css_color_value(
            isset( $data['sc_title_color'] ) ? $data['sc_title_color'] : '',
            'var(--color-heading)'
        );
        $title_size = $this->sanitize_css_size_value(
            isset( $data['sc_title_size'] ) ? $data['sc_title_size'] : '',
            '1.5rem'
        );
        $subtitle_color = $this->sanitize_css_color_value(
            isset( $data['sc_subtitle_color'] ) ? $data['sc_subtitle_color'] : '',
            'var(--color-text-muted)'
        );
        $subtitle_size = $this->sanitize_css_size_value(
            isset( $data['sc_subtitle_size'] ) ? $data['sc_subtitle_size'] : '',
            '0.95rem'
        );
        
        // 布局配置
        $layout = isset( $data['sc_layout'] ) && ! empty( $data['sc_layout'] ) ? $data['sc_layout'] : 'grid';
        $columns = $this->sanitize_integer_range(
            isset( $data['sc_columns'] ) ? $data['sc_columns'] : '',
            4,
            1,
            6
        );
        
        // 数据配置
        $categories = isset( $data['sc_categories'] ) ? $data['sc_categories'] : '';
        $count = isset( $data['sc_count'] ) && $data['sc_count'] !== '' ? intval( $data['sc_count'] ) : 8;
        $orderby = isset( $data['sc_orderby'] ) && ! empty( $data['sc_orderby'] ) ? $data['sc_orderby'] : 'date';
        
        // 样式配置 (卡片/图标)
        $card_bg = $this->sanitize_css_color_value(
            isset( $data['sc_card_bg'] ) ? $data['sc_card_bg'] : '',
            'var(--qiling-component-card-bg)'
        );
        $badge_bg = $this->sanitize_css_color_value(
            isset( $data['sc_badge_bg'] ) ? $data['sc_badge_bg'] : '',
            ''
        );
        $icon_size = $this->sanitize_css_size_value(
            isset( $data['sc_icon_size'] ) ? $data['sc_icon_size'] : '',
            '56px'
        );
        
        // 开关配置
        $show_version = isset( $data['sc_show_version'] ) ? $data['sc_show_version'] === '1' : true;
        $show_date = isset( $data['sc_show_date'] ) ? $data['sc_show_date'] === '1' : true;
        $show_downloads = isset( $data['sc_show_downloads'] ) ? $data['sc_show_downloads'] === '1' : false;
        $show_desc = isset( $data['sc_show_desc'] ) ? $data['sc_show_desc'] === '1' : false;
        
        // 按钮配置
        $show_btn = isset( $data['sc_show_btn'] ) && $data['sc_show_btn'] === '1';
        $btn_text = isset( $data['sc_btn_text'] ) && ! empty( $data['sc_btn_text'] )
            ? $data['sc_btn_text']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看更多', 'View More' ) : __( '查看更多', 'developer-starter' ) );
        $btn_link = ! empty( $data['sc_btn_link'] ) ? $data['sc_btn_link'] : developer_starter_qiapp_get_archive_link();
        $btn_border_color = $this->sanitize_css_color_value(
            isset( $data['sc_btn_border_color'] ) ? $data['sc_btn_border_color'] : '',
            ''
        );
        $btn_hover_border_color = $this->sanitize_css_color_value(
            isset( $data['sc_btn_hover_border_color'] ) ? $data['sc_btn_hover_border_color'] : '',
            ''
        );

        // 获取软件数据
        $software_items = $this->get_software_items( $categories, $count, $orderby );
        
        if ( empty( $software_items ) ) {
            $this->render_empty_notice();
            return;
        }

        // 构建 CSS 变量
        $css_vars = array();
        
        // 背景处理
        if ( $bg_type === 'image' && ! empty( $bg_image ) ) {
            $css_vars['--sc-bg-image'] = 'url("' . $bg_image . '")';
            $css_vars['--sc-bg-overlay'] = $bg_overlay;
            // 图片背景通常需要白色文字
            if ( 'var(--color-heading)' === $title_color || '#' . '1e293b' === $title_color ) {
                $title_color = 'var(--color-neutral-0)';
            }
            if ( 'var(--color-text-muted)' === $subtitle_color || '#' . '64748b' === $subtitle_color ) {
                $subtitle_color = 'var(--qiling-color-rgba-255-255-255-08)';
            }
        } else {
            $css_vars['--sc-bg-color'] = $bg_color;
        }
        
        $css_vars['--sc-title-color'] = $title_color;
        $css_vars['--sc-title-size'] = $title_size;
        $css_vars['--sc-subtitle-color'] = $subtitle_color;
        $css_vars['--sc-subtitle-size'] = $subtitle_size;
        $css_vars['--sc-card-bg'] = $card_bg;
        $css_vars['--sc-icon-size'] = $icon_size;
        $css_vars['--sc-columns'] = (string) $columns;
        if ( '' !== $badge_bg ) {
            $css_vars['--qiling-component-badge-bg'] = $badge_bg;
        }
        if ( '' !== $btn_border_color ) {
            $css_vars['--sc-btn-border'] = $btn_border_color;
        }
        if ( '' !== $btn_hover_border_color ) {
            $css_vars['--sc-btn-hover-border'] = $btn_hover_border_color;
        }

        $style_attr = $this->build_safe_css_var_style( $css_vars );
        
        // 容器类名
        $container_classes = array( 'module', 'module-software-category', 'section-padding' );
        $container_classes[] = 'layout-' . $layout;
        if ( $bg_type === 'image' && ! empty( $bg_image ) ) $container_classes[] = 'has-bg-image';
        
        $unique_id = 'software-category-' . uniqid();
        
        // 重新打包参数以传递给子渲染方法
        $render_args = array(
            'unique_id' => $unique_id,
            'items' => $software_items,
            'title' => $title,
            'subtitle' => $subtitle,
            'show_btn' => $show_btn,
            'btn_text' => $btn_text,
            'btn_link' => $btn_link,
            'show_version' => $show_version,
            'show_date' => $show_date,
            'show_downloads' => $show_downloads,
            'show_desc' => $show_desc,
            'style_attr' => $style_attr,
            'container_classes' => implode( ' ', $container_classes ),
            'columns' => $columns,
            'icon_size' => $icon_size
        );
        
        // 根据布局类型选择渲染方法
        switch ( $layout ) {
            case 'list':
                $this->render_list_layout( $render_args );
                break;
            case 'compact':
                $this->render_compact_layout( $render_args );
                break;
            case 'horizontal':
                $this->render_horizontal_layout( $render_args );
                break;
            case 'grid':
            default:
                $this->render_grid_layout( $render_args );
                break;
        }
    }

    /**
     * 渲染网格布局（默认）
     * 大图标卡片网格，适合展示推荐软件
     */
    /**
     * 渲染网格布局（默认）
     * 大图标卡片网格，适合展示推荐软件
     */
    private function render_grid_layout( $args ) {
        extract( $args );
        ?>
        <section class="<?php echo esc_attr( $container_classes ); ?>" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
            <div class="module-bg-overlay"></div>
            <div class="container relative z-10">
                <?php $this->render_section_header( $title, $subtitle, $show_btn, $btn_text, $btn_link ); ?>
                
                <div class="sc-grid">
                    <?php foreach ( $items as $item ) : ?>
                        <a href="<?php echo esc_url( $item['link'] ); ?>" class="sc-card sc-card-grid">
                            <?php $this->render_software_icon( $item, $icon_size ); ?>
                            <h4 class="sc-name"><?php echo esc_html( $item['name'] ); ?></h4>
                            <?php if ( $show_desc && ! empty( $item['desc'] ) ) : ?>
                                <p class="sc-desc"><?php echo esc_html( $item['desc'] ); ?></p>
                            <?php endif; ?>
                            <div class="sc-meta">
                                <?php if ( $show_version && ! empty( $item['version'] ) ) : ?>
                                    <span class="sc-version"><?php echo esc_html( $item['version'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $show_downloads && ! empty( $item['downloads'] ) ) : ?>
                                    <span class="sc-downloads">
                                        <svg class="sc-download-icon sc-download-icon--grid" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <?php echo esc_html( $item['downloads'] ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ( $show_date && ! empty( $item['update_date'] ) ) : ?>
                                <span class="sc-date"><?php echo esc_html( $item['update_date'] ); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 渲染列表布局
     * 左图标右信息的水平列表，适合展示详细信息
     */
    /**
     * 渲染列表布局
     * 左图标右信息的水平列表，适合展示详细信息
     */
    private function render_list_layout( $args ) {
        extract( $args );
        ?>
        <section class="<?php echo esc_attr( $container_classes ); ?>" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
            <div class="module-bg-overlay"></div>
            <div class="container relative z-10">
                <?php $this->render_section_header( $title, $subtitle, $show_btn, $btn_text, $btn_link ); ?>
                
                <div class="sc-list">
                    <?php foreach ( $items as $item ) : ?>
                        <a href="<?php echo esc_url( $item['link'] ); ?>" class="sc-card sc-card-list">
                            <?php $this->render_software_icon( $item, $icon_size, '12px' ); ?>
                            <div class="sc-info">
                                <h4 class="sc-name"><?php echo esc_html( $item['name'] ); ?></h4>
                                <?php if ( $show_desc && ! empty( $item['desc'] ) ) : ?>
                                    <p class="sc-desc"><?php echo esc_html( $item['desc'] ); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="sc-meta-right">
                                <?php if ( $show_version && ! empty( $item['version'] ) ) : ?>
                                    <span class="sc-version"><?php echo esc_html( $item['version'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $show_downloads && ! empty( $item['downloads'] ) ) : ?>
                                    <span class="sc-downloads">
                                        <svg class="sc-download-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <?php echo esc_html( $item['downloads'] ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( $show_date && ! empty( $item['update_date'] ) ) : ?>
                                    <span class="sc-date"><?php echo esc_html( $item['update_date'] ); ?></span>
                                <?php endif; ?>
                                <span class="sc-arrow">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 渲染紧凑布局
     * 小图标+名称的紧凑排列，适合展示更多软件
     */
    /**
     * 渲染紧凑布局
     * 小图标+名称的紧凑排列，适合展示更多软件
     */
    private function render_compact_layout( $args ) {
        extract( $args );
        ?>
        <section class="<?php echo esc_attr( $container_classes ); ?>" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
            <div class="module-bg-overlay"></div>
            <div class="container relative z-10">
                <?php $this->render_section_header( $title, $subtitle, $show_btn, $btn_text, $btn_link ); ?>
                
                <div class="sc-compact-grid">
                    <?php foreach ( $items as $item ) : ?>
                        <a href="<?php echo esc_url( $item['link'] ); ?>" class="sc-card sc-card-compact">
                            <?php $this->render_software_icon( $item, '36px', '8px' ); ?>
                            <span class="sc-name"><?php echo esc_html( $item['name'] ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 渲染水平滚动布局
     * 左右可滚动的卡片展示，适合横向展示
     */
    /**
     * 渲染水平滚动布局
     * 左右可滚动的卡片展示，适合横向展示
     */
    private function render_horizontal_layout( $args ) {
        extract( $args );
        ?>
        <section class="<?php echo esc_attr( $container_classes ); ?>" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
            <div class="module-bg-overlay"></div>
            <div class="container relative z-10">
                <?php $this->render_section_header( $title, $subtitle, $show_btn, $btn_text, $btn_link ); ?>
                
                <div class="sc-horizontal-wrapper">
                    <div class="sc-horizontal-scroll">
                        <?php foreach ( $items as $item ) : ?>
                            <a href="<?php echo esc_url( $item['link'] ); ?>" class="sc-card sc-card-horizontal">
                                <?php $this->render_software_icon( $item, $icon_size ); ?>
                                <h4 class="sc-name"><?php echo esc_html( $item['name'] ); ?></h4>
                                <?php if ( $show_version && ! empty( $item['version'] ) ) : ?>
                                    <span class="sc-version"><?php echo esc_html( $item['version'] ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <!-- 左右滚动指示 -->
                    <div class="sc-scroll-fade-left"></div>
                    <div class="sc-scroll-fade-right"></div>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 渲染板块头部
     */
    /**
     * 渲染板块头部
     */
    private function render_section_header( $title, $subtitle, $show_btn, $btn_text, $btn_link ) {
        if ( ! $title && ! $show_btn ) return;
        ?>
        <div class="sc-header">
            <div class="sc-header-left">
                <?php if ( $title ) : ?>
                    <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                <?php endif; ?>
                <?php if ( $subtitle ) : ?>
                    <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                <?php endif; ?>
            </div>
            <?php if ( $show_btn && $btn_link ) : ?>
                <a href="<?php echo esc_url( $btn_link ); ?>" class="sc-more-btn">
                    <?php echo esc_html( $btn_text ); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * 渲染软件图标
     */
    private function render_software_icon( $item, $size = '56px', $radius = '14px' ) {
        if ( ! empty( $item['icon'] ) ) : ?>
            <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" class="sc-icon" />
        <?php else : ?>
            <div class="sc-icon-placeholder">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16v16H4V4zm2 2v12h12V6H6z"/></svg>
            </div>
        <?php endif;
    }

    /**
     * 清洗 CSS 颜色值（仅允许常见安全格式）
     */
    private function sanitize_css_color_value( $value, $default ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return $default;
        }

        $hex_color = sanitize_hex_color( $value );
        if ( $hex_color ) {
            return $hex_color;
        }

        if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9\.\s,%]+\s*\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(?:rgba?|hsla?)\(\s*var\(--[a-z0-9_-]+\)(?:\s*,\s*[0-9\.\s%]+)*\s*\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    /**
     * 清洗 CSS 尺寸值（如 16px / 1.2rem / 80%）
     */
    private function sanitize_css_size_value( $value, $default ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return $default;
        }

        if ( preg_match( '/^\d+(?:\.\d+)?(?:px|rem|em|vh|vw|%)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    /**
     * 清洗 CSS URL 值（仅允许 http/https，禁止分号等可断句字符）。
     */
    private function sanitize_css_url_value( $value ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return '';
        }

        $value = esc_url_raw( $value, array( 'http', 'https' ) );
        if ( '' === $value ) {
            return '';
        }

        if ( ! preg_match( '#^https?://[^\\s"\'<>;]+$#i', $value ) ) {
            return '';
        }

        return $value;
    }

    /**
     * 构建安全的 CSS 自定义变量 style 字符串。
     */
    private function build_safe_css_var_style( $variables ) {
        $declarations = array();

        foreach ( (array) $variables as $property => $raw_value ) {
            $property = trim( (string) $property );
            if ( '' === $property || ! preg_match( '/^(--sc-[a-z0-9-]+|--qiling-component-badge-bg)$/', $property ) ) {
                continue;
            }

            if ( ! is_scalar( $raw_value ) ) {
                continue;
            }

            $value = trim( (string) $raw_value );
            if ( '' === $value || preg_match( '/[;<>{}]/', $value ) ) {
                continue;
            }

            $declarations[] = "{$property}: {$value}";
        }

        return implode( '; ', $declarations );
    }

    /**
     * 限制整数字段范围，避免异常输入影响渲染
     */
    private function sanitize_integer_range( $value, $default, $min, $max ) {
        $number = is_numeric( $value ) ? (int) $value : (int) $default;
        return max( $min, min( $max, $number ) );
    }
    
    // 移除已废弃的样式渲染方法 (render_grid_styles, render_list_styles, etc.)

    /**
     * 获取软件数据
     */
    private function get_software_post_ids( $categories, $count, $orderby ) {
        return function_exists( 'developer_starter_qiapp_get_post_ids' )
            ? developer_starter_qiapp_get_post_ids(
                array(
                    'term_ids' => $categories,
                    'limit'    => $count,
                    'orderby'  => $orderby,
                )
            )
            : array();
    }

    private function get_software_items( $categories, $count, $orderby = 'date' ) {
        $items = array();
        $software_post_type = function_exists( 'developer_starter_qiapp_get_post_type' ) ? developer_starter_qiapp_get_post_type() : 'qiapp_software';
        if ( ! post_type_exists( $software_post_type ) ) {
            return $items;
        }

        // 先直接拿到当前模块真正需要的文章 ID，避免把整张 software 表的 post_id 全拉进 PHP。
        $post_ids = $this->get_software_post_ids( $categories, $count, $orderby );
        if ( empty( $post_ids ) ) {
            return $items;
        }

        $args = array(
            'post_type'      => $software_post_type,
            'posts_per_page' => count( $post_ids ),
            'post_status'    => 'publish',
            'post__in'       => $post_ids,
            'orderby'        => 'post__in',
            'ignore_sticky_posts' => true,
            'no_found_rows'  => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );

        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query(
                $args,
                'module_software_category',
                array(
                    'needs_pagination' => false,
                )
            );
        } else {
            $query = new \WP_Query( $args );
        }

        $preloaded_entries = function_exists( 'developer_starter_qiapp_preload_entries' )
            ? developer_starter_qiapp_preload_entries( $post_ids )
            : array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                $entry = function_exists( 'developer_starter_qiapp_get_entry_data' )
                    ? developer_starter_qiapp_get_entry_data( $post_id, $preloaded_entries )
                    : null;

                if ( ! $entry ) {
                    continue;
                }

                $items[] = array(
                    'icon'        => $entry['icon'],
                    'name'        => $entry['title'],
                    'desc'        => $entry['summary'],
                    'version'     => $entry['version_label'],
                    'update_date' => $entry['update_date'],
                    'downloads'   => $entry['download_count'] > 0 ? $entry['download_text'] : '',
                    'link'        => $entry['permalink'],
                );
            }
            wp_reset_postdata();
        }
        
        return $items;
    }

    /**
     * 渲染插件未安装提示
     */
    private function render_plugin_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="software-category-notice software-category-notice--plugin">
            <span class="software-category-notice__icon" aria-hidden="true">⚠️</span>
            <h3 class="software-category-notice__title"><?php esc_html_e( '请先安装启灵App插件', 'developer-starter' ); ?></h3>
            <p class="software-category-notice__text"><?php esc_html_e( '软件分类展示模块需要启灵App插件（qilingapp）的支持，请先安装并激活该插件。', 'developer-starter' ); ?></p>
        </div>
        <?php
    }

    /**
     * 渲染无数据提示
     */
    private function render_empty_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="software-category-notice software-category-notice--empty">
            <span class="software-category-notice__icon" aria-hidden="true">📦</span>
            <h3 class="software-category-notice__title"><?php esc_html_e( '暂无软件数据', 'developer-starter' ); ?></h3>
            <p class="software-category-notice__text"><?php esc_html_e( '请先在启灵App插件中添加软件数据，或选择包含软件数据的分类。', 'developer-starter' ); ?></p>
        </div>
        <?php
    }
}
