<?php
/**
 * Software Ranking Module - 软件排行榜模块
 *
 * 展示软件下载/好评/热门排行榜，支持多种排行类型和布局样式
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Software_Ranking_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-chart-bar';
        $this->description = __( '展示软件下载/好评/热门排行榜', 'developer-starter' );
    }

    public function get_id() {
        return 'software_ranking';
    }

    public function get_name() {
        return __( '软件排行榜', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // 标题设置
            array( 'id' => 'sr_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
            array( 'id' => 'sr_title_size', 'type' => 'text', 'label' => __( '标题字体大小', 'developer-starter' ), 'desc' => __( '如 2rem 或 36px，留空默认', 'developer-starter' ) ),
            array( 'id' => 'sr_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            
            // 副标题设置
            array( 'id' => 'sr_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array( 'id' => 'sr_subtitle_size', 'type' => 'text', 'label' => __( '副标题字体大小', 'developer-starter' ), 'desc' => __( '如 1.1rem 或 18px，留空默认', 'developer-starter' ) ),
            array( 'id' => 'sr_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ) ),

            // 背景设置
            array(
                'id' => 'sr_bg_type',
                'type' => 'select',
                'label' => __( '背景类型', 'developer-starter' ),
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array( 
                'id' => 'sr_bg_color', 
                'type' => 'color', 
                'label' => __( '背景颜色', 'developer-starter' ), 
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'dependency' => array( 'sr_bg_type', '==', 'color' )
            ),
            array(
                'id' => 'sr_bg_image',
                'type' => 'image',
                'label' => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'sr_bg_type', '==', 'image' ),
            ),
            array(
                'id' => 'sr_bg_overlay',
                'type' => 'select',
                'label' => __( '背景遮罩浓度', 'developer-starter' ),
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
                'dependency' => array( 'sr_bg_type', '==', 'image' ),
            ),

            // 间距设置
            array( 'id' => 'sr_padding_top', 'type' => 'text', 'label' => __( '上边距', 'developer-starter' ), 'default' => '60px' ),
            array( 'id' => 'sr_padding_bottom', 'type' => 'text', 'label' => __( '下边距', 'developer-starter' ), 'default' => '60px' ),
            
            // 布局设置
            array( 'id' => 'sr_layout', 'type' => 'select', 'label' => __( '布局方式', 'developer-starter' ), 'options' => array( 
                'list' => __( '列表榜单', 'developer-starter' ), 
                'cards' => __( '卡片榜单(Top3突出)', 'developer-starter' ), 
                'numbered' => __( '数字榜单', 'developer-starter' ), 
                'multi_column' => __( '多列榜单', 'developer-starter' ) 
            ), 'default' => 'list' ),
            array( 'id' => 'sr_columns', 'type' => 'number', 'label' => __( '列数 (多列模式)', 'developer-starter' ), 'default' => '2' ),
            
            // 数据设置
            array( 'id' => 'sr_ranking_type', 'type' => 'select', 'label' => __( '排行依据', 'developer-starter' ), 'options' => array( 
                'downloads' => __( '下载量', 'developer-starter' ), 
                'views' => __( '浏览量', 'developer-starter' ), 
                'latest' => __( '最近更新', 'developer-starter' ), 
                'newest' => __( '最新发布', 'developer-starter' ) 
            ), 'default' => 'downloads' ),
            array( 'id' => 'sr_categories', 'type' => 'text', 'label' => __( '软件分类ID (逗号分隔)', 'developer-starter' ) ),
            array( 'id' => 'sr_count', 'type' => 'number', 'label' => __( '显示数量', 'developer-starter' ), 'default' => '10' ),
            
            // 样式设置
            array( 'id' => 'sr_card_bg', 'type' => 'color', 'label' => __( '卡片背景色', 'developer-starter' ), 'default' => 'var(--color-neutral-0)' ),
            array(
                'id'          => 'sr_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'description' => __( '控制排行类型、版本与排名徽章背景；留空时保留原有排行语义色并跟随全局标签风格。', 'developer-starter' ),
            ),
            array( 'id' => 'sr_icon_size', 'type' => 'text', 'label' => __( '图标大小', 'developer-starter' ), 'default' => '48px' ),
            
            // 显示设置
            array( 'id' => 'sr_show_rank_badge', 'type' => 'select', 'label' => __( '显示排名徽章', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            array( 'id' => 'sr_show_stats', 'type' => 'select', 'label' => __( '显示统计数值', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            array( 'id' => 'sr_show_version', 'type' => 'select', 'label' => __( '显示版本', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            
            array( 'id' => 'sr_show_btn', 'type' => 'select', 'label' => __( '显示按钮', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '0' ),
            array( 'id' => 'sr_btn_text', 'type' => 'text', 'label' => __( '按钮文字', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看完整榜单', 'View Full Ranking' ) : __( '查看完整榜单', 'developer-starter' ) ),
            array( 'id' => 'sr_btn_link', 'type' => 'text', 'label' => __( '按钮链接', 'developer-starter' ) ),
            array(
                'id'          => 'sr_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'sr_btn_text_color',
                'type'        => 'color',
                'label'       => __( '按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'sr_btn_border_color' ),
            array(
                'id'          => 'sr_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'sr_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'sr_btn_hover_border_color', __( '按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随按钮悬停背景颜色。', 'developer-starter' ) ),
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

        // 检查启灵软件库插件是否激活
        if ( ! function_exists( 'developer_starter_qiapp_is_available' ) || ! developer_starter_qiapp_is_available() ) {
            $this->render_plugin_notice();
            return;
        }

        // 基础配置
        $title = isset( $data['sr_title'] ) ? sanitize_text_field( $data['sr_title'] ) : '';
        $subtitle = isset( $data['sr_subtitle'] ) ? sanitize_text_field( $data['sr_subtitle'] ) : '';

        // 样式配置
        $bg_type = $this->sanitize_select_value(
            isset( $data['sr_bg_type'] ) ? $data['sr_bg_type'] : '',
            array( 'color', 'image' ),
            'color'
        );
        $bg_color = $this->sanitize_css_background_value(
            isset( $data['sr_bg_color'] ) ? $data['sr_bg_color'] : '',
            ''
        );
        $bg_image = isset( $data['sr_bg_image'] ) ? esc_url_raw( $data['sr_bg_image'] ) : '';
        $bg_overlay = $this->sanitize_overlay_value( isset( $data['sr_bg_overlay'] ) ? $data['sr_bg_overlay'] : '0' );
        $pt = $this->sanitize_css_size_value(
            isset( $data['sr_padding_top'] ) ? $data['sr_padding_top'] : '',
            '60px'
        );
        $pb = $this->sanitize_css_size_value(
            isset( $data['sr_padding_bottom'] ) ? $data['sr_padding_bottom'] : '',
            '60px'
        );

        // 字体样式
        $title_size = $this->sanitize_css_size_value(
            isset( $data['sr_title_size'] ) ? $data['sr_title_size'] : '',
            ''
        );
        $title_color = $this->sanitize_css_color_value(
            isset( $data['sr_title_color'] ) ? $data['sr_title_color'] : '',
            ''
        );
        $subtitle_size = $this->sanitize_css_size_value(
            isset( $data['sr_subtitle_size'] ) ? $data['sr_subtitle_size'] : '',
            ''
        );
        $subtitle_color = $this->sanitize_css_color_value(
            isset( $data['sr_subtitle_color'] ) ? $data['sr_subtitle_color'] : '',
            ''
        );

        // 布局配置
        $layout = $this->sanitize_select_value(
            isset( $data['sr_layout'] ) ? $data['sr_layout'] : '',
            array( 'list', 'cards', 'numbered', 'multi_column' ),
            'list'
        );
        $columns = $this->sanitize_integer_range(
            isset( $data['sr_columns'] ) ? $data['sr_columns'] : '',
            2,
            1,
            4
        );

        // 数据配置
        $ranking_type = $this->sanitize_select_value(
            isset( $data['sr_ranking_type'] ) ? $data['sr_ranking_type'] : '',
            array( 'downloads', 'views', 'latest', 'newest' ),
            'downloads'
        );
        $categories = isset( $data['sr_categories'] ) ? sanitize_text_field( $data['sr_categories'] ) : '';
        $count = $this->sanitize_integer_range(
            isset( $data['sr_count'] ) ? $data['sr_count'] : '',
            10,
            1,
            100
        );

        // 卡片样式
        $card_bg = $this->sanitize_css_color_value(
            isset( $data['sr_card_bg'] ) ? $data['sr_card_bg'] : '',
            'var(--color-neutral-0)'
        );
        $badge_bg = $this->sanitize_css_color_value(
            isset( $data['sr_badge_bg'] ) ? $data['sr_badge_bg'] : '',
            ''
        );
        $icon_size = $this->sanitize_css_size_value(
            isset( $data['sr_icon_size'] ) ? $data['sr_icon_size'] : '',
            '48px'
        );
        $show_rank_badge = isset( $data['sr_show_rank_badge'] ) ? $data['sr_show_rank_badge'] === '1' : true;
        $show_stats = isset( $data['sr_show_stats'] ) ? $data['sr_show_stats'] === '1' : true;
        $show_version = isset( $data['sr_show_version'] ) ? $data['sr_show_version'] === '1' : true;

        // 按钮配置
        $show_btn = isset( $data['sr_show_btn'] ) && $data['sr_show_btn'] === '1';
        $btn_text = isset( $data['sr_btn_text'] ) && ! empty( $data['sr_btn_text'] )
            ? sanitize_text_field( $data['sr_btn_text'] )
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看完整榜单', 'View Full Ranking' ) : __( '查看完整榜单', 'developer-starter' ) );
        $btn_link = ! empty( $data['sr_btn_link'] ) ? esc_url_raw( $data['sr_btn_link'] ) : developer_starter_qiapp_get_archive_link();
        $btn_bg_color = $this->sanitize_css_color_value(
            isset( $data['sr_btn_bg_color'] ) ? $data['sr_btn_bg_color'] : '',
            ''
        );
        $btn_text_color = $this->sanitize_css_color_value(
            isset( $data['sr_btn_text_color'] ) ? $data['sr_btn_text_color'] : '',
            ''
        );
        $btn_border_color = $this->sanitize_css_color_value(
            isset( $data['sr_btn_border_color'] ) ? $data['sr_btn_border_color'] : '',
            ''
        );
        $btn_hover_bg_color = $this->sanitize_css_color_value(
            isset( $data['sr_btn_hover_bg_color'] ) ? $data['sr_btn_hover_bg_color'] : '',
            ''
        );
        $btn_hover_text_color = $this->sanitize_css_color_value(
            isset( $data['sr_btn_hover_text_color'] ) ? $data['sr_btn_hover_text_color'] : '',
            ''
        );
        $btn_hover_border_color = $this->sanitize_css_color_value(
            isset( $data['sr_btn_hover_border_color'] ) ? $data['sr_btn_hover_border_color'] : '',
            ''
        );

        // 将清洗后的关键值回填，保障后续各布局输出统一安全。
        $data['sr_title'] = $title;
        $data['sr_subtitle'] = $subtitle;
        $data['sr_card_bg'] = $card_bg;
        $data['sr_badge_bg'] = $badge_bg;
        $data['sr_icon_size'] = $icon_size;
        $data['sr_show_rank_badge'] = $show_rank_badge ? '1' : '0';
        $data['sr_show_stats'] = $show_stats ? '1' : '0';
        $data['sr_show_version'] = $show_version ? '1' : '0';
        $data['sr_show_btn'] = $show_btn ? '1' : '0';
        $data['sr_btn_text'] = $btn_text;
        $data['sr_btn_link'] = $btn_link;
        $data['sr_columns'] = $columns;

        // 获取排行榜数据
        $ranking_items = $this->get_ranking_items( $ranking_type, $categories, $count );
        
        if ( empty( $ranking_items ) ) {
            $this->render_empty_notice();
            return;
        }

        // 构建Section样式
        $section_styles = array(
            "padding-top: {$pt}",
            "padding-bottom: {$pb}",
        );
        if ( $bg_type === 'image' && ! empty( $bg_image ) ) {
            $section_styles[] = 'background-image: url("' . $bg_image . '")';
            $section_styles[] = 'background-size: cover';
            $section_styles[] = 'background-position: center';
        } elseif ( ! empty( $bg_color ) ) {
            if ( 0 === stripos( $bg_color, 'linear-gradient(' ) || 0 === stripos( $bg_color, 'radial-gradient(' ) ) {
                $section_styles[] = "background: {$bg_color}";
            } else {
                $section_styles[] = "background-color: {$bg_color}";
            }
        }
        if ( '' !== $btn_bg_color ) {
            $section_styles[] = "--sr-btn-bg: {$btn_bg_color}";
            $section_styles[] = "--sr-btn-border: {$btn_bg_color}";
        }
        if ( '' !== $btn_text_color ) {
            $section_styles[] = "--sr-btn-text: {$btn_text_color}";
        }
        if ( '' !== $btn_border_color ) {
            $section_styles[] = "--sr-btn-border: {$btn_border_color}";
        }
        if ( '' !== $btn_hover_bg_color ) {
            $section_styles[] = "--sr-btn-hover-bg: {$btn_hover_bg_color}";
            $section_styles[] = "--sr-btn-hover-border: {$btn_hover_bg_color}";
        }
        if ( '' !== $btn_hover_text_color ) {
            $section_styles[] = "--sr-btn-hover-text: {$btn_hover_text_color}";
        }
        if ( '' !== $btn_hover_border_color ) {
            $section_styles[] = "--sr-btn-hover-border: {$btn_hover_border_color}";
        }
        if ( '' !== $badge_bg ) {
            $section_styles[] = "--qiling-component-badge-bg: {$badge_bg}";
            $section_styles[] = "--sr-rank-badge-bg: {$badge_bg}";
        }
        $section_style = implode( '; ', $section_styles ) . ';';
        
        // 构建标题/副标题样式
        $title_style_parts = array();
        if ( $title_size ) {
            $title_style_parts[] = "font-size: {$title_size}";
        }
        if ( $title_color ) {
            $title_style_parts[] = "color: {$title_color}";
        }
        $title_style_str = ! empty( $title_style_parts ) ? implode( '; ', $title_style_parts ) . ';' : '';

        $subtitle_style_parts = array();
        if ( $subtitle_size ) {
            $subtitle_style_parts[] = "font-size: {$subtitle_size}";
        }
        if ( $subtitle_color ) {
            $subtitle_style_parts[] = "color: {$subtitle_color}";
        }
        $subtitle_style_str = ! empty( $subtitle_style_parts ) ? implode( '; ', $subtitle_style_parts ) . ';' : '';

        $unique_id = 'software-ranking-' . uniqid();
        
        // 获取排行类型标签
        $ranking_labels = array(
            'downloads' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '下载量', 'Downloads' ) : __( '下载量', 'developer-starter' ),
            'views' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '浏览量', 'Views' ) : __( '浏览量', 'developer-starter' ),
            'latest' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '最新更新', 'Recently Updated' ) : __( '最新更新', 'developer-starter' ),
            'newest' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '最新发布', 'Newest Releases' ) : __( '最新发布', 'developer-starter' ),
        );
        $ranking_label = isset( $ranking_labels[ $ranking_type ] ) ? $ranking_labels[ $ranking_type ] : '';
        
        // 传递给布局渲染方法的参数
        $layout_args = array(
            'unique_id' => $unique_id,
            'items' => $ranking_items,
            'data' => $data,
            'section_style' => $section_style,
            'title_style' => $title_style_str,
            'subtitle_style' => $subtitle_style_str,
            'ranking_label' => $ranking_label,
            'bg_type' => $bg_type,
            'bg_image' => $bg_image,
            'bg_overlay' => $bg_overlay
        );

        // 根据布局类型选择渲染方法
        switch ( $layout ) {
            case 'cards':
                $this->render_cards_layout( $layout_args );
                break;
            case 'numbered':
                $this->render_numbered_layout( $layout_args );
                break;
            case 'multi_column':
                $this->render_multi_column_layout( $layout_args );
                break;
            case 'list':
            default:
                $this->render_list_layout( $layout_args );
                break;
        }
    }

    /**
     * 规范化布局渲染参数，避免动态变量展开覆盖局部变量。
     *
     * @param mixed $args 原始布局参数。
     * @return array<string,mixed>
     */
    private function normalize_layout_args( $args ) {
        $args = is_array( $args ) ? $args : array();

        return array(
            'unique_id'      => isset( $args['unique_id'] ) ? (string) $args['unique_id'] : '',
            'items'          => isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array(),
            'data'           => isset( $args['data'] ) && is_array( $args['data'] ) ? $args['data'] : array(),
            'section_style'  => isset( $args['section_style'] ) ? (string) $args['section_style'] : '',
            'title_style'    => isset( $args['title_style'] ) ? (string) $args['title_style'] : '',
            'subtitle_style' => isset( $args['subtitle_style'] ) ? (string) $args['subtitle_style'] : '',
            'ranking_label'  => isset( $args['ranking_label'] ) ? (string) $args['ranking_label'] : '',
            'bg_type'        => isset( $args['bg_type'] ) ? (string) $args['bg_type'] : 'color',
            'bg_image'       => isset( $args['bg_image'] ) ? (string) $args['bg_image'] : '',
            'bg_overlay'     => isset( $args['bg_overlay'] ) ? (float) $args['bg_overlay'] : 0,
        );
    }

    /**
     * 渲染列表布局（默认）
     * 带排名徽章的垂直列表
     */
    private function render_list_layout( $args ) {
        $layout_args    = $this->normalize_layout_args( $args );
        $unique_id      = $layout_args['unique_id'];
        $items          = $layout_args['items'];
        $data           = $layout_args['data'];
        $section_style  = $layout_args['section_style'];
        $title_style    = $layout_args['title_style'];
        $subtitle_style = $layout_args['subtitle_style'];
        $ranking_label  = $layout_args['ranking_label'];
        $bg_type        = $layout_args['bg_type'];
        $bg_image       = $layout_args['bg_image'];
        $bg_overlay     = $layout_args['bg_overlay'];
        
        $title = isset( $data['sr_title'] ) ? $data['sr_title'] : '';
        $subtitle = isset( $data['sr_subtitle'] ) ? $data['sr_subtitle'] : '';
        $card_bg = isset( $data['sr_card_bg'] ) && ! empty( $data['sr_card_bg'] ) ? $data['sr_card_bg'] : 'var(--color-neutral-0)';
        $icon_size = isset( $data['sr_icon_size'] ) && ! empty( $data['sr_icon_size'] ) ? $data['sr_icon_size'] : '48px';
        $show_rank_badge = isset( $data['sr_show_rank_badge'] ) ? $data['sr_show_rank_badge'] === '1' : true;
        $show_stats = isset( $data['sr_show_stats'] ) ? $data['sr_show_stats'] === '1' : true;
        $show_version = isset( $data['sr_show_version'] ) ? $data['sr_show_version'] === '1' : true;
        $show_btn = isset( $data['sr_show_btn'] ) && $data['sr_show_btn'] === '1';
        $btn_text = isset( $data['sr_btn_text'] ) && ! empty( $data['sr_btn_text'] )
            ? $data['sr_btn_text']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看完整榜单', 'View Full Ranking' ) : __( '查看完整榜单', 'developer-starter' ) );
        $btn_link = ! empty( $data['sr_btn_link'] ) ? $data['sr_btn_link'] : developer_starter_qiapp_get_archive_link();
        ?>
        <section class="module module-software-ranking layout-list" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            <div class="container" style="position: relative; z-index: 2;">
                <?php $this->render_section_header( $title, $subtitle, $title_style, $subtitle_style, $show_btn, $btn_text, $btn_link, $ranking_label ); ?>
                
                <div class="sr-list">
                    <?php foreach ( $items as $index => $item ) : 
                        $rank = $index + 1;
                    ?>
                        <a href="<?php echo esc_url( $item['link'] ); ?>" class="sr-item sr-item-list" style="background: <?php echo esc_attr( $card_bg ); ?>;">
                            <?php if ( $show_rank_badge ) : ?>
                                <?php $this->render_rank_badge( $rank ); ?>
                            <?php endif; ?>
                            <?php $this->render_software_icon( $item, $icon_size, '12px' ); ?>
                            <div class="sr-info">
                                <h4 class="sr-name"><?php echo esc_html( $item['name'] ); ?></h4>
                                <div class="sr-meta">
                                    <?php if ( $show_version && ! empty( $item['version'] ) ) : ?>
                                        <span class="sr-version"><?php echo esc_html( $item['version'] ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $item['category'] ) ) : ?>
                                        <span class="sr-category"><?php echo esc_html( $item['category'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ( $show_stats && ! empty( $item['stat_value'] ) ) : ?>
                                <div class="sr-stat">
                                    <span class="sr-stat-value"><?php echo esc_html( $item['stat_value'] ); ?></span>
                                    <span class="sr-stat-label"><?php echo esc_html( $item['stat_label'] ); ?></span>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php

    }

    /**
     * 渲染卡片布局
     * 突出TOP3的卡片样式
     */
    private function render_cards_layout( $args ) {
        $layout_args    = $this->normalize_layout_args( $args );
        $unique_id      = $layout_args['unique_id'];
        $items          = $layout_args['items'];
        $data           = $layout_args['data'];
        $section_style  = $layout_args['section_style'];
        $title_style    = $layout_args['title_style'];
        $subtitle_style = $layout_args['subtitle_style'];
        $ranking_label  = $layout_args['ranking_label'];
        $bg_type        = $layout_args['bg_type'];
        $bg_image       = $layout_args['bg_image'];
        $bg_overlay     = $layout_args['bg_overlay'];
        
        $title = isset( $data['sr_title'] ) ? $data['sr_title'] : '';
        $subtitle = isset( $data['sr_subtitle'] ) ? $data['sr_subtitle'] : '';
        $card_bg = isset( $data['sr_card_bg'] ) && ! empty( $data['sr_card_bg'] ) ? $data['sr_card_bg'] : 'var(--color-neutral-0)';
        $show_stats = isset( $data['sr_show_stats'] ) ? $data['sr_show_stats'] === '1' : true;
        $show_version = isset( $data['sr_show_version'] ) ? $data['sr_show_version'] === '1' : true;
        $show_btn = isset( $data['sr_show_btn'] ) && $data['sr_show_btn'] === '1';
        $btn_text = isset( $data['sr_btn_text'] ) && ! empty( $data['sr_btn_text'] )
            ? $data['sr_btn_text']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看完整榜单', 'View Full Ranking' ) : __( '查看完整榜单', 'developer-starter' ) );
        $btn_link = ! empty( $data['sr_btn_link'] ) ? $data['sr_btn_link'] : developer_starter_qiapp_get_archive_link();
        
        // 分离TOP3和其他
        $top3 = array_slice( $items, 0, 3 );
        $others = array_slice( $items, 3 );
        ?>
        <section class="module module-software-ranking layout-cards" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            <div class="container" style="position: relative; z-index: 2;">
                <?php $this->render_section_header( $title, $subtitle, $title_style, $subtitle_style, $show_btn, $btn_text, $btn_link, $ranking_label ); ?>
                
                <!-- TOP3 展示 -->
                <div class="sr-top3">
                    <?php foreach ( $top3 as $index => $item ) : 
                        $rank = $index + 1;
                        $is_first = $rank === 1;
                        $medal_colors = array( 1 => 'var(--qiling-color-fbbf24)', 2 => 'var(--color-neutral-400)', 3 => 'var(--color-accent)' );
                        $medal_color = isset( $medal_colors[ $rank ] ) ? $medal_colors[ $rank ] : 'var(--color-text-muted)';
                        $medal_badge_bg = "var(--sr-rank-badge-bg, {$medal_color})";
                    ?>
                        <div class="sr-card-wrapper">
                            <!-- 排名勋章 -->
                            <div class="sr-medal" style="
                                background: <?php echo esc_attr( $medal_badge_bg ); ?>;
                            "><?php echo $rank; ?></div>
                            <a href="<?php echo esc_url( $item['link'] ); ?>" class="sr-card sr-card-top<?php echo $rank; ?>" style="
                                background: <?php echo esc_attr( $card_bg ); ?>;
                                box-shadow: <?php echo $is_first ? '0 8px 32px var(--qiling-color-rgba-0-0-0-01)' : '0 4px 16px var(--qiling-color-rgba-0-0-0-006)'; ?>;
                                border: 2px solid <?php echo $is_first ? $medal_color : 'var(--qiling-color-rgba-0-0-0-004)'; ?>;
                            ">
                                <?php $this->render_software_icon( $item, '64px' ); ?>
                                <h4 class="sr-name" style="text-align: center;"><?php echo esc_html( $item['name'] ); ?></h4>
                                <?php if ( $show_version && ! empty( $item['version'] ) ) : ?>
                                    <span class="sr-version-badge"><?php echo esc_html( $item['version'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $show_stats && ! empty( $item['stat_value'] ) ) : ?>
                                    <div class="sr-stat" style="margin-top: auto; text-align: center;">
                                        <span class="sr-stat-value" style="font-size: var(--qiling-text-rem-1p15); color: <?php echo esc_attr( $medal_color ); ?>;"><?php echo esc_html( $item['stat_value'] ); ?></span>
                                        <span class="sr-stat-label"><?php echo esc_html( $item['stat_label'] ); ?></span>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 其他排名 -->
                <?php if ( ! empty( $others ) ) : ?>
                    <div class="sr-others">
                        <?php foreach ( $others as $index => $item ) : 
                            $rank = $index + 4;
                        ?>
                            <a href="<?php echo esc_url( $item['link'] ); ?>" class="sr-item-other" style="background: <?php echo esc_attr( $card_bg ); ?>;">
                                <span class="sr-rank-num"><?php echo $rank; ?></span>
                                <?php $this->render_software_icon( $item, '36px', '8px' ); ?>
                                <span class="sr-name"><?php echo esc_html( $item['name'] ); ?></span>
                                <?php if ( $show_stats && ! empty( $item['stat_value'] ) ) : ?>
                                    <span class="sr-stat-mini"><?php echo esc_html( $item['stat_value'] ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    /**
     * 渲染编号布局
     * 大编号+信息的紧凑列表
     */
    private function render_numbered_layout( $args ) {
        $layout_args    = $this->normalize_layout_args( $args );
        $unique_id      = $layout_args['unique_id'];
        $items          = $layout_args['items'];
        $data           = $layout_args['data'];
        $section_style  = $layout_args['section_style'];
        $title_style    = $layout_args['title_style'];
        $subtitle_style = $layout_args['subtitle_style'];
        $ranking_label  = $layout_args['ranking_label'];
        $bg_type        = $layout_args['bg_type'];
        $bg_image       = $layout_args['bg_image'];
        $bg_overlay     = $layout_args['bg_overlay'];
        
        $title = isset( $data['sr_title'] ) ? $data['sr_title'] : '';
        $subtitle = isset( $data['sr_subtitle'] ) ? $data['sr_subtitle'] : '';
        $card_bg = isset( $data['sr_card_bg'] ) && ! empty( $data['sr_card_bg'] ) ? $data['sr_card_bg'] : 'var(--color-neutral-0)';
        $show_stats = isset( $data['sr_show_stats'] ) ? $data['sr_show_stats'] === '1' : true;
        $show_btn = isset( $data['sr_show_btn'] ) && $data['sr_show_btn'] === '1';
        $btn_text = isset( $data['sr_btn_text'] ) && ! empty( $data['sr_btn_text'] )
            ? $data['sr_btn_text']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看完整榜单', 'View Full Ranking' ) : __( '查看完整榜单', 'developer-starter' ) );
        $btn_link = ! empty( $data['sr_btn_link'] ) ? $data['sr_btn_link'] : developer_starter_qiapp_get_archive_link();
        ?>
        <section class="module module-software-ranking layout-numbered" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            <div class="container" style="position: relative; z-index: 2;">
                <?php $this->render_section_header( $title, $subtitle, $title_style, $subtitle_style, $show_btn, $btn_text, $btn_link, $ranking_label ); ?>
                
                <div class="sr-numbered-list" style="background: <?php echo esc_attr( $card_bg ); ?>;">
                    <?php foreach ( $items as $index => $item ) : 
                        $rank = $index + 1;
                        $is_top3 = $rank <= 3;
                        $rank_colors = array( 1 => 'var(--qiling-color-ef4444)', 2 => 'var(--color-accent)', 3 => 'var(--qiling-color-eab308)' );
                        $rank_color = isset( $rank_colors[ $rank ] ) ? $rank_colors[ $rank ] : 'var(--color-neutral-400)';
                    ?>
                        <a href="<?php echo esc_url( $item['link'] ); ?>" class="sr-numbered-item" style="<?php echo esc_attr( $index > 0 ? 'border-top: 1px solid var(--qiling-color-rgba-0-0-0-004);' : '' ); ?>">
                            <span class="sr-big-rank" style="
                                font-size: <?php echo esc_attr( $is_top3 ? 'var(--qiling-text-rem-1p5)' : 'var(--qiling-text-rem-1p1)' ); ?>;
                                color: <?php echo esc_attr( $rank_color ); ?>;
                            "><?php echo esc_html( (string) $rank ); ?></span>
                            <?php $this->render_software_icon( $item, '42px', '10px' ); ?>
                            <div class="sr-info">
                                <h4 class="sr-name"><?php echo esc_html( $item['name'] ); ?></h4>
                            </div>
                            <?php if ( $show_stats && ! empty( $item['stat_value'] ) ) : ?>
                                <div class="sr-stat">
                                    <span class="sr-stat-value" style="font-size: var(--qiling-text-rem-0p95);"><?php echo esc_html( $item['stat_value'] ); ?></span>
                                </div>
                            <?php endif; ?>
                            <span class="sr-arrow">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 渲染多列布局
     * 适合在侧边栏或多栏展示
     */
    private function render_multi_column_layout( $args ) {
        $layout_args    = $this->normalize_layout_args( $args );
        $unique_id      = $layout_args['unique_id'];
        $items          = $layout_args['items'];
        $data           = $layout_args['data'];
        $section_style  = $layout_args['section_style'];
        $title_style    = $layout_args['title_style'];
        $subtitle_style = $layout_args['subtitle_style'];
        $ranking_label  = $layout_args['ranking_label'];
        $bg_type        = $layout_args['bg_type'];
        $bg_image       = $layout_args['bg_image'];
        $bg_overlay     = $layout_args['bg_overlay'];
        
        $title = isset( $data['sr_title'] ) ? $data['sr_title'] : '';
        $subtitle = isset( $data['sr_subtitle'] ) ? $data['sr_subtitle'] : '';
        $columns = isset( $data['sr_columns'] ) && ! empty( $data['sr_columns'] ) ? intval( $data['sr_columns'] ) : 2;
        $card_bg = isset( $data['sr_card_bg'] ) && ! empty( $data['sr_card_bg'] ) ? $data['sr_card_bg'] : 'var(--color-neutral-0)';
        $show_stats = isset( $data['sr_show_stats'] ) ? $data['sr_show_stats'] === '1' : true;
        $show_btn = isset( $data['sr_show_btn'] ) && $data['sr_show_btn'] === '1';
        $btn_text = isset( $data['sr_btn_text'] ) && ! empty( $data['sr_btn_text'] )
            ? $data['sr_btn_text']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看完整榜单', 'View Full Ranking' ) : __( '查看完整榜单', 'developer-starter' ) );
        $btn_link = ! empty( $data['sr_btn_link'] ) ? $data['sr_btn_link'] : developer_starter_qiapp_get_archive_link();
        
        // 将items分成多列
        $items_per_column = ceil( count( $items ) / $columns );
        $item_chunks = array_chunk( $items, $items_per_column );
        ?>
        <section class="module module-software-ranking layout-multi-column" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' && $bg_image && $bg_overlay > 0 ) : ?>
                <div class="module-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>
            <div class="container" style="position: relative; z-index: 2;">
                <?php $this->render_section_header( $title, $subtitle, $title_style, $subtitle_style, $show_btn, $btn_text, $btn_link, $ranking_label ); ?>
                
                <div class="sr-multi-column" style="grid-template-columns: repeat(<?php echo esc_attr( $columns ); ?>, 1fr);">
                    <?php 
                    $global_index = 0;
                    foreach ( $item_chunks as $chunk ) : 
                    ?>
                        <div class="sr-column" style="background: <?php echo esc_attr( $card_bg ); ?>;">
                            <?php foreach ( $chunk as $item ) : 
                                $rank = $global_index + 1;
                                $global_index++;
                                $is_top3 = $rank <= 3;
                            ?>
                                <a href="<?php echo esc_url( $item['link'] ); ?>" class="sr-col-item">
                                    <?php $this->render_rank_badge( $rank, true ); ?>
                                    <?php $this->render_software_icon( $item, '32px', '8px' ); ?>
                                    <span class="sr-name"><?php echo esc_html( $item['name'] ); ?></span>
                                    <?php if ( $show_stats && ! empty( $item['stat_value'] ) ) : ?>
                                        <span class="sr-stat-mini"><?php echo esc_html( $item['stat_value'] ); ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
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
    private function render_section_header( $title, $subtitle, $title_style, $subtitle_style, $show_btn, $btn_text, $btn_link, $ranking_label = '' ) {
        if ( ! $title && ! $show_btn ) return;
        ?>
        <div class="sr-header">
            <div class="sr-header-left">
                <div style="display: flex; align-items: center; gap: var(--qiling-space-12);">
                    <?php if ( $title ) : ?>
                        <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>"><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $ranking_label ) : ?>
                        <span class="sr-type-badge"><?php echo esc_html( $ranking_label ); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ( $subtitle ) : ?>
                    <p class="section-subtitle" style="<?php echo esc_attr( $subtitle_style ); ?>"><?php echo esc_html( $subtitle ); ?></p>
                <?php endif; ?>
            </div>
            <?php if ( $show_btn && $btn_link ) : ?>
                <a href="<?php echo esc_url( $btn_link ); ?>" class="sr-more-btn">
                    <?php echo esc_html( $btn_text ); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * 渲染排名徽章
     */
    private function render_rank_badge( $rank, $small = false ) {
        $is_top3 = $rank <= 3;
        $badge_colors = array(
            1 => array( 'bg' => 'linear-gradient(135deg, var(--qiling-color-fbbf24), var(--color-warning))', 'text' => 'var(--color-neutral-0)' ),
            2 => array( 'bg' => 'linear-gradient(135deg, var(--color-neutral-300), var(--color-neutral-400))', 'text' => 'var(--color-neutral-0)' ),
            3 => array( 'bg' => 'linear-gradient(135deg, var(--color-accent), var(--color-accent-dark))', 'text' => 'var(--color-neutral-0)' ),
        );
        
        if ( $is_top3 ) {
            $colors = $badge_colors[ $rank ];
            $size = $small ? '22px' : '28px';
            $font_size = $small ? '0.7rem' : '0.8rem';
        } else {
            $size = $small ? '20px' : '24px';
            $font_size = $small ? '0.65rem' : '0.75rem';
        }
        ?>
        <span class="sr-rank-badge" style="
            width: <?php echo esc_attr( $size ); ?>;
            height: <?php echo esc_attr( $size ); ?>;
            background: <?php echo esc_attr( 'var(--sr-rank-badge-bg, ' . ( $is_top3 ? $colors['bg'] : 'var(--color-neutral-100)' ) . ')' ); ?>;
            color: <?php echo esc_attr( $is_top3 ? $colors['text'] : 'var(--color-text-muted)' ); ?>;
            border-radius: <?php echo esc_attr( $is_top3 ? '50%' : '6px' ); ?>;
            font-size: <?php echo esc_attr( $font_size ); ?>;
            <?php echo esc_attr( $is_top3 ? 'box-shadow: 0 2px 6px var(--qiling-color-rgba-0-0-0-015);' : '' ); ?>
        "><?php echo esc_html( (string) $rank ); ?></span>
        <?php
    }

    /**
     * 渲染软件图标
     */
    private function render_software_icon( $item, $size = '48px', $radius = '12px' ) {
        if ( ! empty( $item['icon'] ) ) : ?>
            <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" class="sr-icon" style="
                width: <?php echo esc_attr( $size ); ?>;
                height: <?php echo esc_attr( $size ); ?>;
                border-radius: <?php echo esc_attr( $radius ); ?>;
            " />
        <?php else : ?>
            <div class="sr-icon-placeholder" style="
                width: <?php echo esc_attr( $size ); ?>;
                height: <?php echo esc_attr( $size ); ?>;
                border-radius: <?php echo esc_attr( $radius ); ?>;
            ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--color-neutral-400)"><path d="M4 4h16v16H4V4zm2 2v12h12V6H6z"/></svg>
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

        if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    /**
     * 清洗 CSS 背景值（支持颜色和渐变）
     */
    private function sanitize_css_background_value( $value, $default ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return $default;
        }

        $sanitized_color = $this->sanitize_css_color_value( $value, '' );
        if ( '' !== $sanitized_color ) {
            return $sanitized_color;
        }

        if ( preg_match( '/^(linear-gradient|radial-gradient)\([^;{}]+\)$/i', $value ) ) {
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
     * 限制整数字段范围，避免异常输入影响渲染
     */
    private function sanitize_integer_range( $value, $default, $min, $max ) {
        $number = is_numeric( $value ) ? (int) $value : (int) $default;
        return max( $min, min( $max, $number ) );
    }

    /**
     * 枚举值白名单过滤
     */
    private function sanitize_select_value( $value, $allowed, $default ) {
        return in_array( $value, $allowed, true ) ? $value : $default;
    }

    /**
     * 清洗背景遮罩浓度，仅允许已配置值
     */
    private function sanitize_overlay_value( $value ) {
        $value = is_scalar( $value ) ? (string) $value : '0';
        $allowed = array( '0', '0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9' );
        return in_array( $value, $allowed, true ) ? $value : '0';
    }

    /**
     * 列表布局样式
     */


    /**
     * 多列布局样式
     */


    /**
     * 获取排行榜数据
     */
    /**
     * 获取排行榜数据
     * (已优化: 增加缓存机制)
     */
    private function get_ranked_post_ids( $ranking_type, $categories, $count ) {
        $orderby = 'newest';

        switch ( $ranking_type ) {
            case 'downloads':
                $orderby = 'downloads';
                break;
            case 'views':
                $orderby = 'views';
                break;
            case 'latest':
                $orderby = 'latest';
                break;
            case 'newest':
            default:
                $orderby = 'newest';
                break;
        }

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

    private function get_ranking_items( $ranking_type, $categories, $count ) {
        $software_post_type = function_exists( 'developer_starter_qiapp_get_post_type' ) ? developer_starter_qiapp_get_post_type() : 'qiapp_software';
        if ( ! post_type_exists( $software_post_type ) ) {
            return array();
        }
        $cache_for_request = ! is_user_logged_in();

        // 1. 构建缓存 Key
        // key 包含所有查询条件，确保不同配置的模块使用不同缓存
        $cache_key = 'sr_v2_' . md5( $ranking_type . '_' . $categories . '_' . $count );
        
        // 2. 尝试获取缓存 (缓存时间设置为 1小时 = 3600秒)
        if ( $cache_for_request ) {
            if ( function_exists( 'developer_starter_cache_fetch' ) ) {
                $cached_items = \developer_starter_cache_fetch( $cache_key, 'developer_starter_module' );
            } else {
                $cached_items = get_transient( $cache_key );
            }
            if ( false !== $cached_items ) {
                return $cached_items;
            }
        }

        $items = array();

        // 直接拿当前排行真正需要的文章 ID，避免把整张 software 表的 post_id 拉到 PHP 再拼超长 post__in。
        $post_ids = $this->get_ranked_post_ids( $ranking_type, $categories, $count );
        if ( empty( $post_ids ) ) {
            if ( $cache_for_request ) {
                if ( function_exists( 'developer_starter_cache_store' ) ) {
                    \developer_starter_cache_store( $cache_key, $items, HOUR_IN_SECONDS, 'developer_starter_module' );
                } else {
                    set_transient( $cache_key, $items, HOUR_IN_SECONDS );
                }
            }
            return $items;
        }
        
        $args = array(
            'post_type'      => $software_post_type,
            'posts_per_page' => count( $post_ids ),
            'post_status'    => 'publish',
            'post__in'       => $post_ids,
            'orderby'        => 'post__in',
            'no_found_rows'  => true,            // 性能优化: 不需要计算总行数
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => true,
        );

        // 排行类型配置
        $stat_labels = array(
            'downloads' => __( '下载', 'developer-starter' ),
            'views'     => __( '浏览', 'developer-starter' ),
            'latest'    => __( '更新', 'developer-starter' ),
            'newest'    => __( '发布', 'developer-starter' ),
        );
        $stat_label = isset( $stat_labels[ $ranking_type ] ) ? $stat_labels[ $ranking_type ] : '';
        
        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query(
                $args,
                'module_software_ranking_' . sanitize_key( $ranking_type ),
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

                $stat_value = '';
                switch ( $ranking_type ) {
                    case 'downloads':
                        $stat_value = $entry['download_text'];
                        break;
                    case 'views':
                        $stat_value = $entry['view_text'];
                        break;
                    case 'latest':
                        $stat_value = ! empty( $entry['update_timestamp'] ) ? wp_date( 'm-d', $entry['update_timestamp'] ) : '';
                        break;
                    case 'newest':
                    default:
                        $stat_value = get_the_date( 'm-d' );
                        break;
                }

                $items[] = array(
                    'icon'        => $entry['icon'],
                    'name'        => $entry['title'],
                    'version'     => $entry['version_label'],
                    'category'    => $entry['primary_category'],
                    'stat_value'  => $stat_value,
                    'stat_label'  => $stat_label,
                    'link'        => $entry['permalink'],
                );
            }
            wp_reset_postdata();
        }
        
        // 3. 设置缓存 (1小时)
        if ( $cache_for_request ) {
            if ( function_exists( 'developer_starter_cache_store' ) ) {
                \developer_starter_cache_store( $cache_key, $items, HOUR_IN_SECONDS, 'developer_starter_module' );
            } else {
                set_transient( $cache_key, $items, HOUR_IN_SECONDS );
            }
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
        <div class="software-ranking-notice" style="
            background: linear-gradient(135deg, var(--qiling-color-fef3c7), var(--qiling-color-fde68a));
            border: 1px solid var(--color-warning);
            border-radius: 12px;
            padding: var(--qiling-space-30);
            text-align: center;
            margin: var(--qiling-space-20) 0;
        ">
            <span style="font-size: var(--qiling-text-rem-2p5); display: block; margin-bottom: var(--qiling-space-12);">⚠️</span>
            <h3 style="margin: 0 0 var(--qiling-space-8); color: var(--qiling-color-92400e);"><?php esc_html_e( '请先安装启灵软件库插件', 'developer-starter' ); ?></h3>
            <p style="margin: 0; color: var(--qiling-color-a16207);"><?php esc_html_e( '软件排行榜模块需要启灵软件库插件（qilingapp）的支持，请先安装并激活该插件。', 'developer-starter' ); ?></p>
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
        <div class="software-ranking-notice" style="
            background: var(--color-neutral-50);
            border: 1px dashed var(--color-neutral-300);
            border-radius: 12px;
            padding: var(--qiling-space-30);
            text-align: center;
            margin: var(--qiling-space-20) 0;
        ">
            <span style="font-size: var(--qiling-text-rem-2p5); display: block; margin-bottom: var(--qiling-space-12);">📊</span>
            <h3 style="margin: 0 0 var(--qiling-space-8); color: var(--color-neutral-600);"><?php esc_html_e( '暂无排行数据', 'developer-starter' ); ?></h3>
            <p style="margin: 0; color: var(--color-text-muted);"><?php esc_html_e( '请先在启灵软件库插件中添加软件数据，或选择包含软件数据的分类。', 'developer-starter' ); ?></p>
        </div>
        <?php
    }
}
