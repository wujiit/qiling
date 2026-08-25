<?php
/**
 * Magic Layout Module - 魔方布局
 *
 * 阶段2增强版：新增容器层 + 分栏层 + 元素层，
 * 让一个模块内支持多段自由布局，同时保留旧单区块数据兼容。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Magic_Layout_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'general';
        $this->icon        = 'dashicons-screenoptions';
        $this->description = __( '自由容器 + 分栏 + 元素组合模块，可在一个模块里组合多段魔方式布局。', 'developer-starter' );
    }

    public function get_id() {
        return 'magic_layout';
    }

    public function get_name() {
        return __( '魔方布局', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'          => 'magic_layout_notice',
                'type'        => 'info',
                'label'       => __( '使用说明', 'developer-starter' ),
                'description' => __( '一个模块可添加多个容器段，并分别设置列数、列宽、面板风格和元素归属。', 'developer-starter' ),
            ),
            array(
                'id'            => 'magic_layout_sections',
                'type'          => 'repeater',
                'label'         => __( '容器层', 'developer-starter' ),
                'description'   => __( '每一项就是一个独立的自由区块。元素可以归属到不同容器，再归属到容器内的列。', 'developer-starter' ),
                'default_items' => $this->get_default_sections(),
                'max_items'     => 8,
                'fields'        => array(
                    array(
                        'id'      => 'section_slot',
                        'type'    => 'select',
                        'label'   => __( '容器编号', 'developer-starter' ),
                        'default' => '1',
                        'options' => $this->get_section_slot_options(),
                    ),
                    array(
                        'id'          => 'section_label',
                        'type'        => 'text',
                        'label'       => __( '容器名称', 'developer-starter' ),
                        'default'     => '',
                        'description' => __( '仅用于后台识别，例如“首屏说明区”、“下方双栏区”。', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'section_columns',
                        'type'    => 'select',
                        'label'   => __( '列数', 'developer-starter' ),
                        'default' => '2',
                        'options' => array(
                            '1' => __( '1列', 'developer-starter' ),
                            '2' => __( '2列', 'developer-starter' ),
                            '3' => __( '3列', 'developer-starter' ),
                            '4' => __( '4列', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'          => 'section_preset',
                        'type'        => 'select',
                        'label'       => __( '列宽预设', 'developer-starter' ),
                        'default'     => 'equal',
                        'description' => __( '主要在 2 列或 3 列时明显生效。', 'developer-starter' ),
                        'options'     => array(
                            'equal'         => __( '均分', 'developer-starter' ),
                            'wide_left'     => __( '左宽右窄', 'developer-starter' ),
                            'wide_right'    => __( '左窄右宽', 'developer-starter' ),
                            'sidebar_left'  => __( '左侧边栏', 'developer-starter' ),
                            'sidebar_right' => __( '右侧边栏', 'developer-starter' ),
                            'center_focus'  => __( '中间强调', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'          => 'section_gap',
                        'type'        => 'text',
                        'label'       => __( '列间距', 'developer-starter' ),
                        'default'     => '28px',
                        'description' => __( '如 24px、2rem、clamp(...)', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'section_vertical_align',
                        'type'    => 'select',
                        'label'   => __( '列内纵向对齐', 'developer-starter' ),
                        'default' => 'start',
                        'options' => array(
                            'start'  => __( '顶部对齐', 'developer-starter' ),
                            'center' => __( '居中', 'developer-starter' ),
                            'end'    => __( '底部对齐', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'section_container_width',
                        'type'    => 'select',
                        'label'   => __( '容器宽度', 'developer-starter' ),
                        'default' => 'default',
                        'options' => array(
                            'narrow'  => __( '窄容器', 'developer-starter' ),
                            'default' => __( '默认容器', 'developer-starter' ),
                            'wide'    => __( '宽容器', 'developer-starter' ),
                            'full'    => __( '全宽', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'section_surface',
                        'type'    => 'select',
                        'label'   => __( '列面板风格', 'developer-starter' ),
                        'default' => 'none',
                        'options' => array(
                            'none'    => __( '无', 'developer-starter' ),
                            'card'    => __( '卡片', 'developer-starter' ),
                            'soft'    => __( '柔和底色', 'developer-starter' ),
                            'outline' => __( '描边', 'developer-starter' ),
                            'glass'   => __( '玻璃感', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'          => 'section_column_padding',
                        'type'        => 'text',
                        'label'       => __( '列内边距', 'developer-starter' ),
                        'default'     => '24px',
                        'description' => __( '当列面板风格不是“无”时最明显。', 'developer-starter' ),
                    ),
                    array(
                        'id'          => 'section_background',
                        'type'        => 'text',
                        'label'       => __( '容器背景', 'developer-starter' ),
                        'default'     => '',
                        'description' => __( '支持安全的颜色值、var(...)、linear-gradient(...)。', 'developer-starter' ),
                    ),
                    array(
                        'id'          => 'section_shell_padding',
                        'type'        => 'text',
                        'label'       => __( '容器包裹内边距', 'developer-starter' ),
                        'default'     => '',
                        'description' => __( '统一作用在整个容器段外层，例如 28px。', 'developer-starter' ),
                    ),
                    array(
                        'id'          => 'section_radius',
                        'type'        => 'text',
                        'label'       => __( '容器圆角', 'developer-starter' ),
                        'default'     => '',
                        'description' => __( '例如 28px。配合容器背景更明显。', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'          => 'magic_layout_section_gap',
                'type'        => 'text',
                'label'       => __( '容器之间间距', 'developer-starter' ),
                'default'     => '48px',
                'description' => __( '控制同一个魔方布局模块里多个容器段之间的距离。', 'developer-starter' ),
            ),
            array(
                'id'          => 'magic_layout_legacy_notice',
                'type'        => 'info',
                'label'       => __( '其他布局设置', 'developer-starter' ),
                'description' => __( '设置模块的列数、列宽、间距、对齐方式和容器样式。', 'developer-starter' ),
            ),
            array(
                'id'      => 'magic_layout_columns',
                'type'    => 'select',
                'label'   => __( '列数', 'developer-starter' ),
                'default' => '2',
                'options' => array(
                    '1' => __( '1列', 'developer-starter' ),
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
            ),
            array(
                'id'          => 'magic_layout_preset',
                'type'        => 'select',
                'label'       => __( '列宽预设', 'developer-starter' ),
                'default'     => 'equal',
                'description' => __( '主要在 2 列或 3 列时明显生效。', 'developer-starter' ),
                'options'     => array(
                    'equal'         => __( '均分', 'developer-starter' ),
                    'wide_left'     => __( '左宽右窄', 'developer-starter' ),
                    'wide_right'    => __( '左窄右宽', 'developer-starter' ),
                    'sidebar_left'  => __( '左侧边栏', 'developer-starter' ),
                    'sidebar_right' => __( '右侧边栏', 'developer-starter' ),
                    'center_focus'  => __( '中间强调', 'developer-starter' ),
                ),
            ),
            array(
                'id'          => 'magic_layout_gap',
                'type'        => 'text',
                'label'       => __( '列间距', 'developer-starter' ),
                'default'     => '28px',
                'description' => __( '如 24px、2rem、clamp(...)', 'developer-starter' ),
            ),
            array(
                'id'      => 'magic_layout_vertical_align',
                'type'    => 'select',
                'label'   => __( '列内纵向对齐', 'developer-starter' ),
                'default' => 'start',
                'options' => array(
                    'start'  => __( '顶部对齐', 'developer-starter' ),
                    'center' => __( '居中', 'developer-starter' ),
                    'end'    => __( '底部对齐', 'developer-starter' ),
                ),
            ),
            array(
                'id'      => 'magic_layout_container_width',
                'type'    => 'select',
                'label'   => __( '容器宽度', 'developer-starter' ),
                'default' => 'default',
                'options' => array(
                    'narrow'  => __( '窄容器', 'developer-starter' ),
                    'default' => __( '默认容器', 'developer-starter' ),
                    'wide'    => __( '宽容器', 'developer-starter' ),
                    'full'    => __( '全宽', 'developer-starter' ),
                ),
            ),
            array(
                'id'      => 'magic_layout_surface',
                'type'    => 'select',
                'label'   => __( '列面板风格', 'developer-starter' ),
                'default' => 'none',
                'options' => array(
                    'none'    => __( '无', 'developer-starter' ),
                    'card'    => __( '卡片', 'developer-starter' ),
                    'soft'    => __( '柔和底色', 'developer-starter' ),
                    'outline' => __( '描边', 'developer-starter' ),
                    'glass'   => __( '玻璃感', 'developer-starter' ),
                ),
            ),
            array(
                'id'          => 'magic_layout_column_padding',
                'type'        => 'text',
                'label'       => __( '列内边距', 'developer-starter' ),
                'default'     => '24px',
                'description' => __( '当列面板风格不是“无”时最明显。', 'developer-starter' ),
            ),
            array(
                'id'            => 'magic_layout_elements',
                'type'          => 'repeater',
                'label'         => __( '布局元素', 'developer-starter' ),
                'description'   => __( '每个元素都可以指定归属到哪个容器、哪个列。标题、文本、按钮、图片、间距、分割线都在这里配置。', 'developer-starter' ),
                'default_items' => $this->get_default_elements(),
                'fields'        => array(
                    array(
                        'id'      => 'type',
                        'type'    => 'select',
                        'label'   => __( '元素类型', 'developer-starter' ),
                        'default' => 'heading',
                        'options' => array(
                            'heading' => __( '标题', 'developer-starter' ),
                            'text'    => __( '文本', 'developer-starter' ),
                            'button'  => __( '按钮', 'developer-starter' ),
                            'image'   => __( '图片', 'developer-starter' ),
                            'spacer'  => __( '间距', 'developer-starter' ),
                            'divider' => __( '分割线', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'section_slot',
                        'type'    => 'select',
                        'label'   => __( '放入第几个容器', 'developer-starter' ),
                        'default' => '1',
                        'options' => $this->get_section_slot_options(),
                    ),
                    array(
                        'id'      => 'column',
                        'type'    => 'select',
                        'label'   => __( '放入第几列', 'developer-starter' ),
                        'default' => '1',
                        'options' => array(
                            '1' => __( '第1列', 'developer-starter' ),
                            '2' => __( '第2列', 'developer-starter' ),
                            '3' => __( '第3列', 'developer-starter' ),
                            '4' => __( '第4列', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'align',
                        'type'    => 'select',
                        'label'   => __( '元素对齐', 'developer-starter' ),
                        'default' => '',
                        'options' => array(
                            ''       => __( '跟随列', 'developer-starter' ),
                            'left'   => __( '左对齐', 'developer-starter' ),
                            'center' => __( '居中', 'developer-starter' ),
                            'right'  => __( '右对齐', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'          => 'visibility_desktop',
                        'type'        => 'select',
                        'label'       => __( '桌面端显示', 'developer-starter' ),
                        'default'     => '',
                        'description' => __( '留空表示默认显示。', 'developer-starter' ),
                        'options'     => array(
                            ''  => __( '默认显示', 'developer-starter' ),
                            '1' => __( '显示', 'developer-starter' ),
                            '0' => __( '隐藏', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'          => 'visibility_tablet',
                        'type'        => 'select',
                        'label'       => __( '平板端显示', 'developer-starter' ),
                        'default'     => '',
                        'description' => __( '留空表示默认显示。', 'developer-starter' ),
                        'options'     => array(
                            ''  => __( '默认显示', 'developer-starter' ),
                            '1' => __( '显示', 'developer-starter' ),
                            '0' => __( '隐藏', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'          => 'visibility_mobile',
                        'type'        => 'select',
                        'label'       => __( '手机端显示', 'developer-starter' ),
                        'default'     => '',
                        'description' => __( '留空表示默认显示。', 'developer-starter' ),
                        'options'     => array(
                            ''  => __( '默认显示', 'developer-starter' ),
                            '1' => __( '显示', 'developer-starter' ),
                            '0' => __( '隐藏', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'heading_tag',
                        'type'    => 'select',
                        'label'   => __( '标题标签', 'developer-starter' ),
                        'default' => 'h2',
                        'options' => array(
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'p'  => __( '普通段落', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'title',
                        'type'    => 'text',
                        'label'   => __( '标题 / 按钮文字', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'content',
                        'type'    => 'textarea',
                        'label'   => __( '文本内容', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'button_url',
                        'type'    => 'text',
                        'label'   => __( '按钮 / 图片链接', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'button_style',
                        'type'    => 'select',
                        'label'   => __( '按钮风格', 'developer-starter' ),
                        'default' => 'primary',
                        'options' => array(
                            'primary'   => __( '主按钮', 'developer-starter' ),
                            'secondary' => __( '次按钮', 'developer-starter' ),
                            'ghost'     => __( '描边按钮', 'developer-starter' ),
                            'text'      => __( '文字按钮', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'open_new',
                        'type'    => 'checkbox',
                        'label'   => __( '新窗口打开链接', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'image',
                        'type'    => 'image',
                        'label'   => __( '图片', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'image_alt',
                        'type'    => 'text',
                        'label'   => __( '图片 ALT', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'image_width',
                        'type'    => 'text',
                        'label'   => __( '图片宽度', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'image_height',
                        'type'    => 'text',
                        'label'   => __( '图片高度', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'spacer_height',
                        'type'    => 'text',
                        'label'   => __( '间距高度', 'developer-starter' ),
                        'default' => '24px',
                    ),
                    array(
                        'id'      => 'divider_style',
                        'type'    => 'select',
                        'label'   => __( '分割线样式', 'developer-starter' ),
                        'default' => 'solid',
                        'options' => array(
                            'solid'    => __( '实线', 'developer-starter' ),
                            'dashed'   => __( '虚线', 'developer-starter' ),
                            'gradient' => __( '渐变', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'divider_color',
                        'type'    => 'text',
                        'label'   => __( '分割线颜色', 'developer-starter' ),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'divider_width',
                        'type'    => 'text',
                        'label'   => __( '分割线宽度', 'developer-starter' ),
                        'default' => '100%',
                    ),
                    array(
                        'id'      => 'divider_thickness',
                        'type'    => 'text',
                        'label'   => __( '分割线粗细', 'developer-starter' ),
                        'default' => '1px',
                    ),
                ),
            ),
        );
    }

    public function render( $data = array() ) {
        $sections     = $this->resolve_sections( $data );
        $section_gap  = $this->sanitize_spacing( isset( $data['magic_layout_section_gap'] ) ? $data['magic_layout_section_gap'] : '48px', '48px' );
        $elements     = isset( $data['magic_layout_elements'] ) && is_array( $data['magic_layout_elements'] ) ? $data['magic_layout_elements'] : $this->get_default_elements();
        $section_keys = array_keys( $sections );
        $default_slot = ! empty( $section_keys ) ? (string) reset( $section_keys ) : '1';

        $grouped_elements = array();
        foreach ( $sections as $slot => $section ) {
            $grouped_elements[ $slot ] = array();
            for ( $column_index = 1; $column_index <= $section['columns']; $column_index++ ) {
                $grouped_elements[ $slot ][ $column_index ] = array();
            }
        }

        foreach ( $elements as $element ) {
            if ( ! is_array( $element ) ) {
                continue;
            }

            $section_slot = $this->sanitize_section_slot( isset( $element['section_slot'] ) ? $element['section_slot'] : $default_slot );
            if ( ! isset( $sections[ $section_slot ] ) ) {
                $section_slot = $default_slot;
            }

            $column = isset( $element['column'] ) ? absint( $element['column'] ) : 1;
            if ( $column < 1 ) {
                $column = 1;
            } elseif ( $column > $sections[ $section_slot ]['columns'] ) {
                $column = $sections[ $section_slot ]['columns'];
            }

            $grouped_elements[ $section_slot ][ $column ][] = $element;
        }

        ?>
        <section class="module module-magic-layout" style="<?php echo esc_attr( '--qml-section-gap:' . $section_gap . ';' ); ?>">
            <?php foreach ( $sections as $slot => $section ) : ?>
                <?php
                $template        = $this->build_grid_template( $section['columns'], $section['preset'] );
                $tablet_template = ( 1 === $section['columns'] ) ? 'minmax(0, 1fr)' : 'repeat(2, minmax(0, 1fr))';
                $container_style = '--qml-gap:' . esc_attr( $section['gap'] ) . ';--qml-template:' . esc_attr( $template ) . ';--qml-template-tablet:' . esc_attr( $tablet_template ) . ';--qml-column-padding:' . esc_attr( $section['column_padding'] ) . ';';
                $column_class    = 'qml-columns qml-surface-' . $section['surface'] . ' qml-align-' . $section['vertical_align'];
                $shell_style     = $this->build_section_shell_style( $section );
                ?>
                <div class="qml-section qml-section-slot-<?php echo esc_attr( $slot ); ?>">
                    <div class="qml-container qml-width-<?php echo esc_attr( $section['container_width'] ); ?>">
                        <div class="qml-section-shell<?php echo '' !== $shell_style ? ' qml-section-shell-custom' : ''; ?>"<?php echo '' !== $shell_style ? ' style="' . esc_attr( $shell_style ) . '"' : ''; ?>>
                            <div class="<?php echo esc_attr( $column_class ); ?>" style="<?php echo esc_attr( $container_style ); ?>">
                                <?php for ( $column_index = 1; $column_index <= $section['columns']; $column_index++ ) : ?>
                                    <div class="qml-column qml-column-<?php echo esc_attr( (string) $column_index ); ?>">
                                        <?php
                                        if ( empty( $grouped_elements[ $slot ][ $column_index ] ) ) {
                                            echo '<div class="qml-empty-column">' . esc_html__( '这个列暂时还没有元素', 'developer-starter' ) . '</div>';
                                        } else {
                                            foreach ( $grouped_elements[ $slot ][ $column_index ] as $element ) {
                                                $this->render_element( $element );
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
        <?php
    }

    /**
     * @return array<int,array<string,string>>
     */
    private function get_default_sections() {
        return array(
            array(
                'section_slot'           => '1',
                'section_label'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '主容器', 'Primary Section' ) : __( '主容器', 'developer-starter' ),
                'section_columns'        => '2',
                'section_preset'         => 'equal',
                'section_gap'            => '28px',
                'section_vertical_align' => 'start',
                'section_container_width'=> 'default',
                'section_surface'        => 'none',
                'section_column_padding' => '24px',
                'section_background'     => '',
                'section_shell_padding'  => '',
                'section_radius'         => '',
            ),
        );
    }

    /**
     * @return array<int,array<string,string>>
     */
    private function get_default_elements() {
        return array(
            array(
                'type'         => 'heading',
                'section_slot' => '1',
                'column'       => '1',
                'heading_tag'  => 'h2',
                'title'        => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '用魔方布局做更自由的页面区块', 'Build freer sections with Magic Layout' ) : __( '用魔方布局做更自由的页面区块', 'developer-starter' ),
                'content'      => '',
                'align'        => '',
                'visibility_desktop' => '',
                'visibility_tablet'  => '',
                'visibility_mobile'  => '',
            ),
            array(
                'type'         => 'text',
                'section_slot' => '1',
                'column'       => '1',
                'title'        => '',
                'content'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '你可以把多个容器段、标题、段落、按钮、图片、分割线和间距自由放进不同列里，用它来做特殊版式，而不是继续新增专用模块。', 'Place multiple sections, headings, text, buttons, images, dividers and spacers into different columns to build special layouts without creating another dedicated module.' ) : __( '你可以把多个容器段、标题、段落、按钮、图片、分割线和间距自由放进不同列里，用它来做特殊版式，而不是继续新增专用模块。', 'developer-starter' ),
                'visibility_desktop' => '',
                'visibility_tablet'  => '',
                'visibility_mobile'  => '',
            ),
            array(
                'type'         => 'button',
                'section_slot' => '1',
                'column'       => '1',
                'title'        => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '开始自由组合', 'Start Building' ) : __( '开始自由组合', 'developer-starter' ),
                'button_url'   => '#',
                'button_style' => 'primary',
                'open_new'     => '',
                'visibility_desktop' => '',
                'visibility_tablet'  => '',
                'visibility_mobile'  => '',
            ),
            array(
                'type'         => 'image',
                'section_slot' => '1',
                'column'       => '2',
                'image'        => '',
                'image_alt'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '示意图', 'Preview' ) : __( '示意图', 'developer-starter' ),
                'image_width'  => '',
                'image_height' => '320px',
                'visibility_desktop' => '',
                'visibility_tablet'  => '',
                'visibility_mobile'  => '',
            ),
        );
    }

    /**
     * @param array<string,mixed> $data 模块数据。
     * @return array<string,array<string,mixed>>
     */
    private function resolve_sections( $data ) {
        $raw_sections = isset( $data['magic_layout_sections'] ) && is_array( $data['magic_layout_sections'] ) ? $data['magic_layout_sections'] : array();
        $sections     = array();

        foreach ( $raw_sections as $index => $raw_section ) {
            if ( ! is_array( $raw_section ) ) {
                continue;
            }

            $fallback_slot = (string) min( 8, max( 1, absint( $index ) + 1 ) );
            $slot          = $this->sanitize_section_slot( isset( $raw_section['section_slot'] ) ? $raw_section['section_slot'] : $fallback_slot );

            $sections[ $slot ] = array(
                'slot'            => $slot,
                'label'           => isset( $raw_section['section_label'] ) ? sanitize_text_field( (string) $raw_section['section_label'] ) : '',
                'columns'         => $this->normalize_columns( isset( $raw_section['section_columns'] ) ? $raw_section['section_columns'] : '2' ),
                'preset'          => $this->sanitize_preset( isset( $raw_section['section_preset'] ) ? $raw_section['section_preset'] : 'equal' ),
                'gap'             => $this->sanitize_spacing( isset( $raw_section['section_gap'] ) ? $raw_section['section_gap'] : '28px', '28px' ),
                'vertical_align'  => $this->sanitize_vertical_align( isset( $raw_section['section_vertical_align'] ) ? $raw_section['section_vertical_align'] : 'start' ),
                'container_width' => $this->sanitize_container_width( isset( $raw_section['section_container_width'] ) ? $raw_section['section_container_width'] : 'default' ),
                'surface'         => $this->sanitize_surface( isset( $raw_section['section_surface'] ) ? $raw_section['section_surface'] : 'none' ),
                'column_padding'  => $this->sanitize_spacing( isset( $raw_section['section_column_padding'] ) ? $raw_section['section_column_padding'] : '24px', '24px' ),
                'background'      => $this->sanitize_background( isset( $raw_section['section_background'] ) ? $raw_section['section_background'] : '' ),
                'shell_padding'   => $this->sanitize_spacing( isset( $raw_section['section_shell_padding'] ) ? $raw_section['section_shell_padding'] : '', '' ),
                'radius'          => $this->sanitize_spacing( isset( $raw_section['section_radius'] ) ? $raw_section['section_radius'] : '', '' ),
            );
        }

        if ( empty( $sections ) ) {
            $legacy_section            = $this->build_legacy_section( $data );
            $sections['1'] = $legacy_section;
        }

        ksort( $sections, SORT_NATURAL );

        return $sections;
    }

    /**
     * @param array<string,mixed> $data 模块数据。
     * @return array<string,mixed>
     */
    private function build_legacy_section( $data ) {
        return array(
            'slot'            => '1',
            'label'           => '',
            'columns'         => $this->normalize_columns( isset( $data['magic_layout_columns'] ) ? $data['magic_layout_columns'] : '2' ),
            'preset'          => $this->sanitize_preset( isset( $data['magic_layout_preset'] ) ? $data['magic_layout_preset'] : 'equal' ),
            'gap'             => $this->sanitize_spacing( isset( $data['magic_layout_gap'] ) ? $data['magic_layout_gap'] : '28px', '28px' ),
            'vertical_align'  => $this->sanitize_vertical_align( isset( $data['magic_layout_vertical_align'] ) ? $data['magic_layout_vertical_align'] : 'start' ),
            'container_width' => $this->sanitize_container_width( isset( $data['magic_layout_container_width'] ) ? $data['magic_layout_container_width'] : 'default' ),
            'surface'         => $this->sanitize_surface( isset( $data['magic_layout_surface'] ) ? $data['magic_layout_surface'] : 'none' ),
            'column_padding'  => $this->sanitize_spacing( isset( $data['magic_layout_column_padding'] ) ? $data['magic_layout_column_padding'] : '24px', '24px' ),
            'background'      => '',
            'shell_padding'   => '',
            'radius'          => '',
        );
    }

    /**
     * @param array<string,mixed> $section 容器配置。
     * @return string
     */
    private function build_section_shell_style( $section ) {
        $styles = array();

        if ( ! empty( $section['background'] ) ) {
            $styles[] = '--qml-shell-background:' . $section['background'];
        }

        if ( ! empty( $section['shell_padding'] ) ) {
            $styles[] = '--qml-shell-padding:' . $section['shell_padding'];
        }

        if ( ! empty( $section['radius'] ) ) {
            $styles[] = '--qml-shell-radius:' . $section['radius'];
            $styles[] = '--qml-shell-overflow:hidden';
        } elseif ( ! empty( $section['background'] ) ) {
            $styles[] = '--qml-shell-radius:28px';
            $styles[] = '--qml-shell-overflow:hidden';
        }

        return implode( ';', $styles );
    }

    /**
     * @param array<string,mixed> $element 元素数据。
     * @return void
     */
    private function render_element( $element ) {
        $type            = isset( $element['type'] ) ? sanitize_key( (string) $element['type'] ) : 'text';
        $align           = $this->sanitize_text_align( isset( $element['align'] ) ? $element['align'] : '' );
        $alignment_class = '' !== $align ? ' qml-text-' . $align : '';
        $visibility_attr = $this->build_element_visibility_attr( $element );

        switch ( $type ) {
            case 'heading':
                $tag   = $this->sanitize_heading_tag( isset( $element['heading_tag'] ) ? $element['heading_tag'] : 'h2' );
                $title = isset( $element['title'] ) ? trim( (string) $element['title'] ) : '';
                if ( '' === $title ) {
                    return;
                }
                printf(
                    '<%1$s class="qml-element qml-heading%3$s"%4$s>%2$s</%1$s>',
                    esc_html( $tag ),
                    esc_html( $title ),
                    esc_attr( $alignment_class ),
                    $visibility_attr
                );
                return;

            case 'text':
                $content = isset( $element['content'] ) ? trim( (string) $element['content'] ) : '';
                if ( '' === $content ) {
                    return;
                }
                echo '<div class="qml-element qml-text' . esc_attr( $alignment_class ) . '"' . $visibility_attr . '>' . wpautop( wp_kses_post( $content ) ) . '</div>';
                return;

            case 'button':
                $title      = isset( $element['title'] ) ? trim( (string) $element['title'] ) : '';
                $button_url = isset( $element['button_url'] ) ? esc_url( (string) $element['button_url'] ) : '';
                if ( '' === $title ) {
                    return;
                }

                $style       = $this->sanitize_button_style( isset( $element['button_style'] ) ? $element['button_style'] : 'primary' );
                $target_attr = ! empty( $element['open_new'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
                echo '<div class="qml-element qml-button-wrap' . esc_attr( $alignment_class ) . '"' . $visibility_attr . '>';
                echo '<a class="qml-button qml-button-' . esc_attr( $style ) . '" href="' . ( '' !== $button_url ? $button_url : '#' ) . '"' . $target_attr . '>' . esc_html( $title ) . '</a>';
                echo '</div>';
                return;

            case 'image':
                $image = isset( $element['image'] ) ? esc_url( (string) $element['image'] ) : '';
                if ( '' === $image ) {
                    echo '<div class="qml-element qml-image qml-image-empty' . esc_attr( $alignment_class ) . '"' . $visibility_attr . '>' . esc_html__( '请为这个图片元素上传图片', 'developer-starter' ) . '</div>';
                    return;
                }

                $alt          = isset( $element['image_alt'] ) ? (string) $element['image_alt'] : '';
                $image_link   = isset( $element['button_url'] ) ? esc_url( (string) $element['button_url'] ) : '';
                $image_width  = $this->sanitize_spacing( isset( $element['image_width'] ) ? $element['image_width'] : '', '' );
                $image_height = $this->sanitize_spacing( isset( $element['image_height'] ) ? $element['image_height'] : '', '' );
                $style_parts  = array();
                if ( '' !== $image_width ) {
                    $style_parts[] = '--qml-image-width:' . $image_width;
                }
                if ( '' !== $image_height ) {
                    $style_parts[] = '--qml-image-height:' . $image_height;
                }
                $image_attr  = $this->build_element_visibility_attr( $element, implode( ';', $style_parts ) );
                $image_class = 'qml-element qml-image' . $alignment_class . ( '' !== $image_height ? ' qml-image-has-height' : '' );

                echo '<div class="' . esc_attr( $image_class ) . '"' . $image_attr . '>';
                if ( '' !== $image_link ) {
                    echo '<a href="' . $image_link . '"' . ( ! empty( $element['open_new'] ) ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>';
                }
                echo '<img src="' . $image . '" alt="' . esc_attr( $alt ) . '" />';
                if ( '' !== $image_link ) {
                    echo '</a>';
                }
                echo '</div>';
                return;

            case 'spacer':
                $spacer_height = $this->sanitize_spacing( isset( $element['spacer_height'] ) ? $element['spacer_height'] : '24px', '24px' );
                echo '<div class="qml-element qml-spacer"' . $this->build_element_visibility_attr( $element, '--qml-spacer-height:' . $spacer_height ) . '></div>';
                return;

            case 'divider':
                $divider_style     = $this->sanitize_divider_style( isset( $element['divider_style'] ) ? $element['divider_style'] : 'solid' );
                $divider_color     = $this->sanitize_color( isset( $element['divider_color'] ) ? $element['divider_color'] : '' );
                $divider_width     = $this->sanitize_spacing( isset( $element['divider_width'] ) ? $element['divider_width'] : '100%', '100%' );
                $divider_thickness = $this->sanitize_spacing( isset( $element['divider_thickness'] ) ? $element['divider_thickness'] : '1px', '1px' );
                $style             = '--qml-divider-width:' . esc_attr( $divider_width ) . ';--qml-divider-thickness:' . esc_attr( $divider_thickness ) . ';';
                if ( '' !== $divider_color ) {
                    $style .= '--qml-divider-color:' . esc_attr( $divider_color ) . ';';
                }
                echo '<div class="qml-element qml-divider-wrap' . esc_attr( $alignment_class ) . '"' . $visibility_attr . '><span class="qml-divider qml-divider-' . esc_attr( $divider_style ) . '" style="' . esc_attr( $style ) . '"></span></div>';
                return;
        }
    }

    /**
     * @param array<string,mixed> $element 元素数据。
     * @return string
     */
    private function build_element_visibility_attr( $element, $extra_style = '' ) {
        if ( ! is_array( $element ) ) {
            return '' !== $extra_style ? ' style="' . esc_attr( $extra_style ) . '"' : '';
        }

        $desktop = $this->normalize_visibility_value( isset( $element['visibility_desktop'] ) ? $element['visibility_desktop'] : '' );
        $tablet  = $this->normalize_visibility_value( isset( $element['visibility_tablet'] ) ? $element['visibility_tablet'] : '' );
        $mobile  = $this->normalize_visibility_value( isset( $element['visibility_mobile'] ) ? $element['visibility_mobile'] : '' );

        $styles = array();
        $extra_style = is_scalar( $extra_style ) ? trim( (string) $extra_style ) : '';
        if ( '' !== $extra_style ) {
            $styles[] = rtrim( $extra_style, ';' );
        }
        if ( '' !== $desktop ) {
            $styles[] = '--qml-display-desktop:' . ( '0' === $desktop ? 'none' : 'block' );
        }
        if ( '' !== $tablet ) {
            $styles[] = '--qml-display-tablet:' . ( '0' === $tablet ? 'none' : 'block' );
        }
        if ( '' !== $mobile ) {
            $styles[] = '--qml-display-mobile:' . ( '0' === $mobile ? 'none' : 'block' );
        }

        if ( '' === $desktop && '' === $tablet && '' === $mobile ) {
            return ! empty( $styles ) ? ' style="' . esc_attr( implode( ';', $styles ) . ';' ) . '"' : '';
        }

        return ' data-qml-visibility="1" style="' . esc_attr( implode( ';', $styles ) . ';' ) . '"';
    }

    /**
     * @return array<string,string>
     */
    private function get_section_slot_options() {
        return array(
            '1' => __( '容器1', 'developer-starter' ),
            '2' => __( '容器2', 'developer-starter' ),
            '3' => __( '容器3', 'developer-starter' ),
            '4' => __( '容器4', 'developer-starter' ),
            '5' => __( '容器5', 'developer-starter' ),
            '6' => __( '容器6', 'developer-starter' ),
            '7' => __( '容器7', 'developer-starter' ),
            '8' => __( '容器8', 'developer-starter' ),
        );
    }

    /**
     * @param string $value 原始容器编号。
     * @return string
     */
    private function sanitize_section_slot( $value ) {
        $slot = absint( $value );
        if ( $slot < 1 ) {
            $slot = 1;
        }
        if ( $slot > 8 ) {
            $slot = 8;
        }

        return (string) $slot;
    }

    /**
     * @param string $value 原始列数。
     * @return int
     */
    private function normalize_columns( $value ) {
        $columns = absint( $value );
        if ( $columns < 1 ) {
            $columns = 1;
        }
        if ( $columns > 4 ) {
            $columns = 4;
        }

        return $columns;
    }

    /**
     * @param string $preset 原始预设。
     * @return string
     */
    private function sanitize_preset( $preset ) {
        $preset  = sanitize_key( (string) $preset );
        $allowed = array( 'equal', 'wide_left', 'wide_right', 'sidebar_left', 'sidebar_right', 'center_focus' );

        return in_array( $preset, $allowed, true ) ? $preset : 'equal';
    }

    /**
     * @param string $value 原始值。
     * @param string $fallback 兜底值。
     * @return string
     */
    private function sanitize_spacing( $value, $fallback ) {
        $sanitized = Module_Manager::sanitize_spacing_value( $value );
        return '' !== $sanitized ? $sanitized : $fallback;
    }

    /**
     * @param string $value 原始值。
     * @return string
     */
    private function sanitize_vertical_align( $value ) {
        $value   = sanitize_key( (string) $value );
        $allowed = array( 'start', 'center', 'end' );

        return in_array( $value, $allowed, true ) ? $value : 'start';
    }

    /**
     * @param string $value 原始值。
     * @return string
     */
    private function sanitize_container_width( $value ) {
        $value   = sanitize_key( (string) $value );
        $allowed = array( 'narrow', 'default', 'wide', 'full' );

        return in_array( $value, $allowed, true ) ? $value : 'default';
    }

    /**
     * @param string $value 原始值。
     * @return string
     */
    private function sanitize_surface( $value ) {
        $value   = sanitize_key( (string) $value );
        $allowed = array( 'none', 'card', 'soft', 'outline', 'glass' );

        return in_array( $value, $allowed, true ) ? $value : 'none';
    }

    /**
     * @param int    $columns 列数。
     * @param string $preset  预设。
     * @return string
     */
    private function build_grid_template( $columns, $preset ) {
        if ( 1 === $columns ) {
            return 'minmax(0, 1fr)';
        }

        if ( 2 === $columns ) {
            switch ( $preset ) {
                case 'wide_left':
                    return 'minmax(0, 1.35fr) minmax(0, 0.65fr)';
                case 'wide_right':
                    return 'minmax(0, 0.65fr) minmax(0, 1.35fr)';
                case 'sidebar_left':
                    return 'minmax(220px, 0.42fr) minmax(0, 1.58fr)';
                case 'sidebar_right':
                    return 'minmax(0, 1.58fr) minmax(220px, 0.42fr)';
                default:
                    return 'repeat(2, minmax(0, 1fr))';
            }
        }

        if ( 3 === $columns && 'center_focus' === $preset ) {
            return 'minmax(0, 0.9fr) minmax(0, 1.2fr) minmax(0, 0.9fr)';
        }

        return 'repeat(' . absint( $columns ) . ', minmax(0, 1fr))';
    }

    /**
     * @param string $value 原始对齐。
     * @return string
     */
    private function sanitize_text_align( $value ) {
        $value   = sanitize_key( (string) $value );
        $allowed = array( 'left', 'center', 'right' );

        return in_array( $value, $allowed, true ) ? $value : '';
    }

    /**
     * @param string $value 原始标签。
     * @return string
     */
    private function sanitize_heading_tag( $value ) {
        $value   = strtolower( sanitize_key( (string) $value ) );
        $allowed = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'p' );

        return in_array( $value, $allowed, true ) ? $value : 'h2';
    }

    /**
     * @param string $value 原始按钮样式。
     * @return string
     */
    private function sanitize_button_style( $value ) {
        $value   = sanitize_key( (string) $value );
        $allowed = array( 'primary', 'secondary', 'ghost', 'text' );

        return in_array( $value, $allowed, true ) ? $value : 'primary';
    }

    /**
     * @param string $value 原始样式。
     * @return string
     */
    private function sanitize_divider_style( $value ) {
        $value   = sanitize_key( (string) $value );
        $allowed = array( 'solid', 'dashed', 'gradient' );

        return in_array( $value, $allowed, true ) ? $value : 'solid';
    }

    /**
     * @param string $value 原始显隐值。
     * @return string
     */
    private function normalize_visibility_value( $value ) {
        $value = trim( (string) $value );
        if ( '0' === $value || '1' === $value ) {
            return $value;
        }

        return '';
    }

    /**
     * @param string $value 原始颜色。
     * @return string
     */
    private function sanitize_color( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        if ( function_exists( 'sanitize_hex_color' ) ) {
            $hex = sanitize_hex_color( $value );
            if ( '' !== $hex && null !== $hex ) {
                return $hex;
            }
        }

        if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\([0-9\.\,\s%]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9\-_]+\)$/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param string $value 原始背景值。
     * @return string
     */
    private function sanitize_background( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        $color = $this->sanitize_color( $value );
        if ( '' !== $color ) {
            return $color;
        }

        if ( preg_match( '/^(?:linear-gradient|radial-gradient)\([a-z0-9\-\+\*\/%\.,\s#\(\)]+\)$/i', $value ) ) {
            return $value;
        }

        return '';
    }

}
