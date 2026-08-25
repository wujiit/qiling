<?php
/**
 * Category Tabs Module - 分类标签切换模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Category_Tabs_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-category'; // 使用分类图标
        $this->description = __( '支持分类/标签切换展示文章列表，无需跳转', 'developer-starter' );
    }

    public function get_id() {
        return 'category_tabs';
    }

    public function get_name() {
        return __( '分类标签切换', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // 标题设置
            array( 'id' => 'tabs_title', 'label' => __( '标题', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'tabs_subtitle', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array(
                'id' => 'tabs_source_mode',
                'label' => __( '标签来源模式', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'manual' => __( '手动配置', 'developer-starter' ),
                    'auto_hot_tags' => __( '自动聚合热门标签', 'developer-starter' ),
                ),
                'default' => 'manual',
            ),
            array(
                'id' => 'tabs_auto_tag_count',
                'label' => __( '自动标签数量', 'developer-starter' ),
                'type' => 'number',
                'default' => '8',
                'dependency' => array( 'id' => 'tabs_source_mode', 'value' => 'auto_hot_tags' ),
            ),
            array(
                'id' => 'tabs_auto_min_count',
                'label' => __( '最小文章数（过滤冷标签）', 'developer-starter' ),
                'type' => 'number',
                'default' => '1',
                'dependency' => array( 'id' => 'tabs_source_mode', 'value' => 'auto_hot_tags' ),
            ),
            array(
                'id' => 'tabs_auto_exclude_tag_ids',
                'label' => __( '排除标签ID (逗号分隔)', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'dependency' => array( 'id' => 'tabs_source_mode', 'value' => 'auto_hot_tags' ),
            ),
            
            // 样式设置
            array(
                'id' => 'tabs_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'tabs_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            
            // 布局设置
            array( 'id' => 'columns', 'label' => __( '显示列数', 'developer-starter' ), 'type' => 'select', 'options' => array( 
                '2' => __( '2列', 'developer-starter' ), 
                '3' => __( '3列', 'developer-starter' ), 
                '4' => __( '4列', 'developer-starter' ),
                '5' => __( '5列', 'developer-starter' )
            ), 'default' => '4' ),
            
            array( 'id' => 'image_aspect_ratio', 'label' => __( '封面比例', 'developer-starter' ), 'type' => 'select', 'options' => array( 
                '16:9' => '16:9 (宽屏)', 
                '4:3' => '4:3 (标准)', 
                '1:1' => '1:1 (正方形)', 
                '3:4' => '3:4 (竖屏)', 
                'custom' => __( '自定义', 'developer-starter' )
            ), 'default' => '16:9' ),
            
            array( 
                'id' => 'image_height', 
                'label' => __( '自定义封面高度', 'developer-starter' ), 
                'type' => 'text', 
                'default' => '200px', 
                'dependency' => array( 'image_aspect_ratio', '==', 'custom' ),
                'description' => __( '例如: 200px 或 15rem', 'developer-starter' )
            ),

            // 查询设置
            array( 'id' => 'post_count', 'label' => __( '显示数量', 'developer-starter' ), 'type' => 'number', 'default' => '8' ),
            
            // 标签项设置
            array(
                'id' => 'tabs_list',
                'label' => __( '分类/标签页', 'developer-starter' ),
                'type' => 'repeater',
                'description' => __( '添加要展示的分类或标签', 'developer-starter' ),
                'dependency' => array( 'id' => 'tabs_source_mode', 'value' => 'manual' ),
                'fields' => array(
                    array( 'id' => 'tab_name', 'label' => __( '显示名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'source_type', 'label' => __( '数据来源', 'developer-starter' ), 'type' => 'select', 'options' => array(
                        'category' => __( '文章分类', 'developer-starter' ),
                        'tag' => __( '文章标签', 'developer-starter' )
                    ) ),
                    array( 'id' => 'source_id', 'label' => __( 'ID (多个用逗号分隔)', 'developer-starter' ), 'type' => 'text', 'description' => __( '输入分类或标签的ID', 'developer-starter' ) ),
                ),
            ),
            
            // 显示选项
            array( 'id' => 'show_date', 'label' => __( '显示日期', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'show_author', 'label' => __( '显示作者', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'show_views', 'label' => __( '显示浏览量', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'show_category_badge', 'label' => __( '显示分类标识', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'no' ),
            array(
                'id' => 'card_style',
                'label' => __( '卡片风格', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'default' => __( '默认文章卡片', 'developer-starter' ),
                    'wallpaper' => __( '图片素材卡片', 'developer-starter' ),
                ),
                'default' => 'default',
            ),
            array( 'id' => 'show_title', 'label' => __( '显示标题', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'show_image_dimensions', 'label' => __( '显示图片尺寸', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'show_image_format', 'label' => __( '显示图片格式', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'no' ),
            array(
                'id' => 'show_resource_badges',
                'label' => __( '显示商城资源角标', 'developer-starter' ),
                'type' => 'select',
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'no',
                'description' => __( '仅在文章配置了启灵商城资源且全局角标已启用时显示；普通图片文章不受影响。', 'developer-starter' ),
            ),
            array(
                'id' => 'show_resource_price',
                'label' => __( '显示商城价格', 'developer-starter' ),
                'type' => 'select',
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'no',
                'description' => __( '价格只读取启灵商城资源状态，不需要商城的普通文章会自动忽略。', 'developer-starter' ),
            ),
            array(
                'id' => 'show_download_button',
                'label' => __( '显示获取图片按钮', 'developer-starter' ),
                'type' => 'select',
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'no',
                'description' => __( '按钮进入文章详情；如文章使用启灵商城，购买和下载权限仍由商城在详情页处理。', 'developer-starter' ),
            ),
            array( 'id' => 'download_button_text', 'label' => __( '获取图片按钮文案', 'developer-starter' ), 'type' => 'text', 'default' => __( '获取图片', 'developer-starter' ), 'dependency' => array( 'id' => 'show_download_button', 'value' => 'yes' ) ),
            array( 'id' => 'download_button_bg_color', 'label' => __( '获取图片按钮背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'dependency' => array( 'id' => 'show_download_button', 'value' => 'yes' ) ),
            array( 'id' => 'download_button_text_color', 'label' => __( '获取图片按钮文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'dependency' => array( 'id' => 'show_download_button', 'value' => 'yes' ) ),
            $this->get_button_border_color_field( 'download_button_border_color', __( '获取图片按钮边框颜色', 'developer-starter' ), '', array( 'dependency' => array( 'id' => 'show_download_button', 'value' => 'yes' ) ) ),
            array( 'id' => 'download_button_hover_bg_color', 'label' => __( '获取图片按钮悬停背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'dependency' => array( 'id' => 'show_download_button', 'value' => 'yes' ) ),
            array( 'id' => 'download_button_hover_text_color', 'label' => __( '获取图片按钮悬停文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'dependency' => array( 'id' => 'show_download_button', 'value' => 'yes' ) ),
            $this->get_button_border_color_field( 'download_button_hover_border_color', __( '获取图片按钮悬停边框颜色', 'developer-starter' ), '', array( 'dependency' => array( 'id' => 'show_download_button', 'value' => 'yes' ) ) ),
            array( 'id' => 'tabs_badge_bg', 'label' => __( '分类标识背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'desc' => __( '控制封面分类标识，留空时跟随页面预设/全局徽章颜色。', 'developer-starter' ) ),

            // 标准间距和背景
            array(
                'id' => 'padding_top',
                'label' => __( '上边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
            array(
                'id' => 'padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
            
            // 底部按钮设置
            array(
                'id' => 'more_btn_type',
                'label' => __( '底部按钮功能', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'ajax' => __( '加载更多 (当前页加载)', 'developer-starter' ),
                    'link' => __( '查看更多 (跳转到分类/标签页)', 'developer-starter' )
                ),
                'default' => 'ajax',
                'description' => __( '选择点击底部按钮时的行为', 'developer-starter' )
            ),
            array( 'id' => 'more_link_text', 'label' => __( '查看更多按钮文案', 'developer-starter' ), 'type' => 'text', 'default' => __( '查看更多', 'developer-starter' ) ),
            array( 'id' => 'more_ajax_text', 'label' => __( '加载更多按钮文案', 'developer-starter' ), 'type' => 'text', 'default' => __( '加载更多', 'developer-starter' ) ),
            array( 'id' => 'more_button_bg_color', 'label' => __( '底部按钮背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'more_button_text_color', 'label' => __( '底部按钮文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'more_button_hover_bg_color', 'label' => __( '底部按钮悬停背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'more_button_hover_text_color', 'label' => __( '底部按钮悬停文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            $this->get_button_border_color_field( 'more_button_border_color', __( '底部按钮边框颜色', 'developer-starter' ) ),
            $this->get_button_border_color_field( 'more_button_hover_border_color', __( '底部按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随悬停背景颜色。', 'developer-starter' ) ),
            array(
                'id' => 'bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        // 基础数据
        $title = isset( $data['tabs_title'] ) ? $data['tabs_title'] : '';
        $subtitle = isset( $data['tabs_subtitle'] ) ? $data['tabs_subtitle'] : '';
        $source_mode = isset( $data['tabs_source_mode'] ) ? $data['tabs_source_mode'] : 'manual';
        $tabs = isset( $data['tabs_list'] ) ? $data['tabs_list'] : array();
        if ( $source_mode === 'auto_hot_tags' ) {
            $tabs = $this->build_auto_tag_tabs( $data );
        }
        
        if ( empty( $tabs ) ) {
            return;
        }

        // 样式数据
        $title_color = isset( $data['tabs_title_color'] ) ? $data['tabs_title_color'] : '';
        $subtitle_color = isset( $data['tabs_subtitle_color'] ) ? $data['tabs_subtitle_color'] : '';
        $bg_color = isset( $data['bg_color'] ) ? $data['bg_color'] : '';
        $badge_bg = isset( $data['tabs_badge_bg'] ) ? $clean_css_value( $data['tabs_badge_bg'] ) : '';
        $more_link_text = isset( $data['more_link_text'] ) && '' !== trim( (string) $data['more_link_text'] ) ? (string) $data['more_link_text'] : __( '查看更多', 'developer-starter' );
        $more_ajax_text = isset( $data['more_ajax_text'] ) && '' !== trim( (string) $data['more_ajax_text'] ) ? (string) $data['more_ajax_text'] : __( '加载更多', 'developer-starter' );
        $pt = isset( $data['padding_top'] ) ? $data['padding_top'] : '60px';
        $pb = isset( $data['padding_bottom'] ) ? $data['padding_bottom'] : '60px';

        // 布局参数
        $columns = isset( $data['columns'] ) ? $data['columns'] : '4';
        
        // 构建 Section 样式
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        if ( $badge_bg ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};--qiling-cover-badge-category-bg: {$badge_bg};";
        }
        $button_style_map = array(
            'more_button_bg_color'           => '--category-tabs-more-bg',
            'more_button_text_color'         => '--category-tabs-more-text',
            'more_button_border_color'       => '--category-tabs-more-border',
            'more_button_hover_bg_color'     => '--category-tabs-more-hover-bg',
            'more_button_hover_text_color'   => '--category-tabs-more-hover-text',
            'more_button_hover_border_color' => '--category-tabs-more-hover-border',
            'download_button_bg_color'           => '--category-tabs-action-bg',
            'download_button_text_color'         => '--category-tabs-action-text',
            'download_button_border_color'       => '--category-tabs-action-border',
            'download_button_hover_bg_color'     => '--category-tabs-action-hover-bg',
            'download_button_hover_text_color'   => '--category-tabs-action-hover-text',
            'download_button_hover_border_color' => '--category-tabs-action-hover-border',
        );
        foreach ( $button_style_map as $field_id => $css_var ) {
            $value = isset( $data[ $field_id ] ) ? $clean_css_value( $data[ $field_id ] ) : '';
            if ( '' !== $value ) {
                $section_style .= $css_var . ':' . $value . ';';
            }
        }

        // 唯一ID
        $module_id = 'cat-tabs-' . uniqid();
        
        // 准备传递给JS的配置参数
        $js_config = array(
            'post_count' => isset( $data['post_count'] ) ? intval( $data['post_count'] ) : 8,
            'image_aspect_ratio' => isset( $data['image_aspect_ratio'] ) ? $data['image_aspect_ratio'] : '16:9',
            'image_height' => isset( $data['image_height'] ) ? $data['image_height'] : '200px',
            'show_date' => isset( $data['show_date'] ) ? $data['show_date'] : 'yes',
            'show_author' => isset( $data['show_author'] ) ? $data['show_author'] : 'no',
            'show_views' => isset( $data['show_views'] ) ? $data['show_views'] : 'yes',
            'show_category_badge' => isset( $data['show_category_badge'] ) ? $data['show_category_badge'] : 'no',
            'card_style' => isset( $data['card_style'] ) ? $data['card_style'] : 'default',
            'show_title' => isset( $data['show_title'] ) ? $data['show_title'] : 'yes',
            'show_image_dimensions' => isset( $data['show_image_dimensions'] ) ? $data['show_image_dimensions'] : 'no',
            'show_image_format' => isset( $data['show_image_format'] ) ? $data['show_image_format'] : 'no',
            'show_resource_badges' => isset( $data['show_resource_badges'] ) ? $data['show_resource_badges'] : 'no',
            'show_resource_price' => isset( $data['show_resource_price'] ) ? $data['show_resource_price'] : 'no',
            'show_download_button' => isset( $data['show_download_button'] ) ? $data['show_download_button'] : 'no',
            'download_button_text' => isset( $data['download_button_text'] ) ? $data['download_button_text'] : __( '获取图片', 'developer-starter' ),
            'columns' => $columns,
            'more_btn_type' => isset( $data['more_btn_type'] ) ? $data['more_btn_type'] : 'ajax',
        );

        ?>
        <section class="module module-category-tabs" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title" style="<?php echo $title_color ? 'color:' . esc_attr($title_color) : ''; ?>"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle" style="<?php echo $subtitle_color ? 'color:' . esc_attr($subtitle_color) : ''; ?>"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="category-tabs-wrapper">
                    <!-- 标签导航 -->
                    <div class="cat-tabs-nav text-center">
                        <?php foreach ( $tabs as $index => $tab ) : 
                            $active_class = $index === 0 ? 'active' : '';
                            
                            // 获取链接
                            $link = '#';
                            $source_raw = isset( $tab['source_id'] ) ? (string) $tab['source_id'] : '';
                            $source_ids = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $source_raw ) ) ) );
                            $source_id = ! empty( $source_ids ) ? (int) $source_ids[0] : 0;
                            if ( $source_id > 0 && isset( $tab['source_type'] ) && $tab['source_type'] === 'category' ) {
                                $link = get_category_link( $source_id );
                            } elseif ( $source_id > 0 && isset( $tab['source_type'] ) && $tab['source_type'] === 'tag' ) {
                                $link = get_tag_link( $source_id );
                            }
                        ?>
                            <button type="button" 
                                class="cat-tab-btn <?php echo $active_class; ?>" 
                                data-index="<?php echo $index; ?>"
                                data-type="<?php echo esc_attr( $tab['source_type'] ); ?>"
                                data-id="<?php echo esc_attr( $tab['source_id'] ); ?>"
                                data-link="<?php echo esc_url( $link ); ?>">
                                <?php echo esc_html( $tab['tab_name'] ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- 内容区域 -->
                    <div class="cat-tabs-content-container">
                        <div class="cat-tabs-grid grid-cols-<?php echo esc_attr( $columns ); ?>">
                            <!-- 内容将通过 AJAX 加载 -->
                            <div class="cat-tabs-loading-placeholder">
                                <div class="loading-spinner"></div>
                            </div>
                        </div>
                        
                        <!-- 加载/查看更多按钮 -->
                        <div class="cat-tabs-load-more" style="display: none;"> <!-- Initial display none, JS controls it -->
                            <?php if ( isset( $data['more_btn_type'] ) && $data['more_btn_type'] === 'link' ) : ?>
                                <a href="#" class="btn-load-more btn-view-more" target="_blank">
                                    <span><?php echo esc_html( $more_link_text ); ?></span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            <?php else : ?>
                                <button type="button" class="btn-load-more btn-ajax-more">
                                    <span><?php echo esc_html( $more_ajax_text ); ?></span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 13l5 5 5-5M7 6l5 5 5-5"/></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 初始化脚本 -->
            <script>
            (function () {
            function boot() {
                var module = document.getElementById(<?php echo wp_json_encode( $module_id ); ?>);
                if (!module || module.dataset.categoryTabsInitialized) {
                    return;
                }
                module.dataset.categoryTabsInitialized = 'true';

                var config = <?php echo wp_json_encode( $js_config ); ?>;
                var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
                var grid = module.querySelector('.cat-tabs-grid');
                var loadMore = module.querySelector('.cat-tabs-load-more');
                var ajaxBtn = module.querySelector('.btn-ajax-more');
                var linkBtn = module.querySelector('.btn-view-more');
                var tabButtons = Array.prototype.slice.call(module.querySelectorAll('.cat-tab-btn'));
                var ajaxBtnDefaultHtml = ajaxBtn ? ajaxBtn.innerHTML : '';
                var loadingMarkup = '<div class="loading-spinner-wrapper"><div class="loading-spinner"></div></div>';
                var noPostsMarkup = '<div class="no-posts"><?php echo esc_js( __( '暂无内容', 'developer-starter' ) ); ?></div>';
                var loadFailedMessage = <?php echo wp_json_encode( __( '加载失败，请稍后重试', 'developer-starter' ) ); ?>;
                var networkErrorMessage = <?php echo wp_json_encode( __( '网络错误，请稍后重试', 'developer-starter' ) ); ?>;
                var loadingButtonText = <?php echo wp_json_encode( __( '加载中...', 'developer-starter' ) ); ?>;

                var currentState = {
                    type: '',
                    id: '',
                    page: 1,
                    isLoading: false,
                    hasMore: true
                };

                function setLoadMoreVisible(visible) {
                    if (!loadMore) {
                        return;
                    }
                    loadMore.style.display = visible ? '' : 'none';
                }

                function setGridMessage(className, message) {
                    if (!grid) {
                        return;
                    }
                    grid.innerHTML = '<div class="' + className + '">' + message + '</div>';
                }

                function parseJsonSafely(text) {
                    if (!text) {
                        return null;
                    }

                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        return null;
                    }
                }

                function appendFormData(formData, key, value) {
                    if (value === null || typeof value === 'undefined') {
                        return;
                    }

                    if (Object.prototype.toString.call(value) === '[object Object]') {
                        Object.keys(value).forEach(function (childKey) {
                            appendFormData(formData, key + '[' + childKey + ']', value[childKey]);
                        });
                        return;
                    }

                    if (Array.isArray(value)) {
                        value.forEach(function (childValue, index) {
                            appendFormData(formData, key + '[' + index + ']', childValue);
                        });
                        return;
                    }

                    formData.append(key, String(value));
                }

                function requestJson(payload, timeout) {
                    var formData = new FormData();
                    Object.keys(payload).forEach(function (key) {
                        appendFormData(formData, key, payload[key]);
                    });

                    var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
                    var timer = null;
                    var options = {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    };

                    if (controller) {
                        options.signal = controller.signal;
                        timer = window.setTimeout(function () {
                            if (!module.isConnected) {
                                controller.abort();
                                return;
                            }
                            controller.abort();
                        }, timeout);
                    }

                    return fetch(ajaxUrl, options)
                        .then(function (response) {
                            if (!module.isConnected) {
                                return null;
                            }
                            return response.text();
                        })
                        .then(function (text) {
                            if (timer) {
                                clearTimeout(timer);
                            }
                            return module.isConnected ? parseJsonSafely(text) : null;
                        })
                        .catch(function (error) {
                            if (timer) {
                                clearTimeout(timer);
                            }
                            throw error;
                        });
                }

                function appendHtmlWithFade(html) {
                    if (!grid || !html) {
                        return;
                    }

                    var template = document.createElement('template');
                    template.innerHTML = html.trim();
                    var elements = Array.prototype.slice.call(template.content.children);

                    elements.forEach(function (element) {
                        element.style.opacity = '0';
                        element.style.transition = 'opacity 0.4s ease';
                    });

                    grid.appendChild(template.content);

                    window.requestAnimationFrame(function () {
                        if (!module.isConnected) return;
                        elements.forEach(function (element) {
                            element.style.opacity = '1';
                        });
                    });
                }

                function restoreAjaxButton() {
                    if (!ajaxBtn) {
                        return;
                    }
                    ajaxBtn.classList.remove('loading');
                    ajaxBtn.innerHTML = ajaxBtnDefaultHtml;
                }

                function loadPosts(reset) {
                    if (typeof reset === 'undefined') {
                        reset = false;
                    }

                    if (currentState.isLoading && !reset) {
                        return;
                    }

                    currentState.requestId = (currentState.requestId || 0) + 1;
                    var requestId = currentState.requestId;
                    var requestType = currentState.type;
                    var requestTermId = currentState.id;
                    var requestPage = reset ? 1 : currentState.page;

                    currentState.isLoading = true;

                    if (reset) {
                        if (grid) {
                            grid.innerHTML = loadingMarkup;
                        }
                        setLoadMoreVisible(false);
                        currentState.page = 1;
                        currentState.hasMore = true;
                    } else if (config.more_btn_type === 'ajax' && ajaxBtn) {
                        ajaxBtn.classList.add('loading');
                        ajaxBtn.textContent = loadingButtonText;
                    }

                    requestJson({
                        action: 'ds_load_category_tabs_posts',
                        type: requestType,
                        id: requestTermId,
                        page: requestPage,
                        count: config.post_count,
                        config: config
                    }, 15000).then(function (response) {
                        if (!module.isConnected || requestId !== currentState.requestId) return;
                        if (response && response.success && response.data) {
                            if (reset && grid) {
                                grid.innerHTML = '';
                            }

                            if (response.data.html) {
                                appendHtmlWithFade(response.data.html);
                            } else if (reset) {
                                if (grid) {
                                    grid.innerHTML = noPostsMarkup;
                                }
                            }

                            currentState.hasMore = !!response.data.has_next;

                            if (config.more_btn_type === 'link') {
                                setLoadMoreVisible(true);
                            } else if (currentState.hasMore) {
                                setLoadMoreVisible(true);
                                currentState.page = response.data.next_page ? parseInt(response.data.next_page, 10) || (currentState.page + 1) : (currentState.page + 1);
                            } else {
                                setLoadMoreVisible(false);
                            }

                            return;
                        }

                        var message = loadFailedMessage;
                        if (response && response.data && response.data.message) {
                            message = response.data.message;
                        }
                        if (reset) {
                            setGridMessage('error-msg', message);
                        }
                    }).catch(function (error) {
                        if (!module.isConnected || requestId !== currentState.requestId) return;
                        if (error && error.name === 'AbortError') {
                            if (reset) {
                                setGridMessage('error-msg', networkErrorMessage);
                            }
                            return;
                        }

                        if (reset) {
                            setGridMessage('error-msg', networkErrorMessage);
                        }
                    }).then(function () {
                        if (!module.isConnected || requestId !== currentState.requestId) return;
                        currentState.isLoading = false;
                        if (config.more_btn_type === 'ajax') {
                            restoreAjaxButton();
                        }
                    });
                }

                tabButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        if (button.classList.contains('active')) {
                            return;
                        }

                        tabButtons.forEach(function (item) {
                            item.classList.remove('active');
                        });
                        button.classList.add('active');

                        currentState.type = button.getAttribute('data-type') || '';
                        currentState.id = button.getAttribute('data-id') || '';

                        if (config.more_btn_type === 'link' && linkBtn) {
                            linkBtn.setAttribute('href', button.getAttribute('data-link') || '#');
                        }

                        loadPosts(true);
                    });
                });

                if (config.more_btn_type === 'ajax' && ajaxBtn) {
                    ajaxBtn.addEventListener('click', function () {
                        loadPosts(false);
                    });
                }

                var firstTab = tabButtons.length ? tabButtons[0] : null;
                if (firstTab) {
                    currentState.type = firstTab.getAttribute('data-type') || '';
                    currentState.id = firstTab.getAttribute('data-id') || '';

                    if (config.more_btn_type === 'link' && linkBtn) {
                        linkBtn.setAttribute('href', firstTab.getAttribute('data-link') || '#');
                    }

                    loadPosts(true);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot, { once: true });
            } else {
                boot();
            }
            })();
            </script>
        </section>
        <?php
    }

    /**
     * 自动聚合热门标签
     */
    private function build_auto_tag_tabs( $data ) {
        $tag_count = isset( $data['tabs_auto_tag_count'] ) ? max( 1, intval( $data['tabs_auto_tag_count'] ) ) : 8;
        $min_count = isset( $data['tabs_auto_min_count'] ) ? max( 1, intval( $data['tabs_auto_min_count'] ) ) : 1;
        $exclude_ids_raw = isset( $data['tabs_auto_exclude_tag_ids'] ) ? (string) $data['tabs_auto_exclude_tag_ids'] : '';
        $exclude_ids = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $exclude_ids_raw ) ) ) );

        $terms = get_terms( array(
            'taxonomy'   => 'post_tag',
            'hide_empty' => true,
            'number'     => $tag_count,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'exclude'    => $exclude_ids,
        ) );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            $terms = get_terms( array(
                'taxonomy'   => 'category',
                'hide_empty' => true,
                'number'     => $tag_count,
                'orderby'    => 'count',
                'order'      => 'DESC',
            ) );
            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                return array();
            }

            $tabs = array();
            foreach ( $terms as $term ) {
                if ( ! isset( $term->count ) || (int) $term->count < $min_count ) {
                    continue;
                }
                $tabs[] = array(
                    'tab_name' => $term->name,
                    'source_type' => 'category',
                    'source_id' => (string) $term->term_id,
                );
            }
            return $tabs;
        }

        $tabs = array();
        foreach ( $terms as $term ) {
            if ( ! isset( $term->count ) || (int) $term->count < $min_count ) {
                continue;
            }
            $tabs[] = array(
                'tab_name' => $term->name,
                'source_type' => 'tag',
                'source_id' => (string) $term->term_id,
            );
        }

        return $tabs;
    }
}
