<?php
/**
 * Module Advanced Style Service
 *
 * 统一处理模块高级样式协议在后台、前台渲染与前台 Builder 的接入。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Advanced_Style_Service {

    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    private function __construct() {
        add_filter( 'developer_starter_module_wrapper_class', array( $this, 'filter_wrapper_class' ), 10, 4 );
        add_filter( 'developer_starter_module_wrapper_style', array( $this, 'filter_wrapper_style' ), 10, 4 );
        add_filter( 'developer_starter_module_wrapper_attr', array( $this, 'filter_wrapper_attr' ), 10, 4 );
    }

    /**
     * 前台 Builder 注入统一高级控件。
     *
     * @return array<int,array<string,mixed>>
     */
    public function get_builder_fields( $capabilities = array() ) {
        return array(
            array(
                'id'          => '_ds_style',
                'type'        => 'advanced_style',
                'label'       => __( '统一高级样式', 'developer-starter' ),
                'description' => __( '统一设置模块的响应式间距、排版、背景、边框、阴影、圆角与悬停状态。', 'developer-starter' ),
                'capabilities' => is_array( $capabilities ) ? $capabilities : array(),
            ),
            array(
                'id'          => '_ds_visibility',
                'type'        => 'advanced_visibility',
                'label'       => __( '模块显隐', 'developer-starter' ),
                'description' => __( '可以暂时隐藏整个模块，也可以按桌面、平板、手机分别控制显示。', 'developer-starter' ),
            ),
        );
    }

    /**
     * 渲染后台经典模块编辑器里的统一高级控件。
     *
     * @param int                 $idx  模块索引。
     * @param array<string,mixed> $data 模块数据。
     * @return void
     */
    public function render_admin_controls( $idx, $data, $capabilities = array() ) {
        $style_payload      = $this->get_style_payload( $data );
        $visibility_payload = $this->get_visibility_payload( $data );
        $legacy_payload     = isset( $style_payload['legacy'] ) && is_array( $style_payload['legacy'] ) ? $style_payload['legacy'] : array();
        ?>
        <details class="dsm-advanced-style-panel" style="margin-top:var(--qiling-space-20); border-top:1px dashed var(--color-border); padding-top:var(--qiling-space-15);">
            <summary style="cursor:pointer; font-weight:600; color:var(--color-neutral-900); outline:none;"><?php esc_html_e( '高级样式协议（响应式 / 排版 / 背景 / 状态）', 'developer-starter' ); ?></summary>
            <p style="margin:var(--qiling-space-10) 0 0; color:var(--color-neutral-500); font-size:var(--qiling-text-rem-0p75);"><?php esc_html_e( '这是模块样式的统一编辑入口。旧版公共样式字段只作为兼容镜像保留，当前优先在这里维护。', 'developer-starter' ); ?></p>

            <div style="margin-top:var(--qiling-space-16);">
                <p style="margin:0 0 var(--qiling-space-8); font-weight:600;"><?php esc_html_e( '响应式间距', 'developer-starter' ); ?></p>
                <?php
                $this->render_responsive_table(
                    $idx,
                    '_ds_style',
                    'spacing.margin',
                    __( '外边距', 'developer-starter' ),
                    array(
                        'top'    => __( '上', 'developer-starter' ),
                        'right'  => __( '右', 'developer-starter' ),
                        'bottom' => __( '下', 'developer-starter' ),
                        'left'   => __( '左', 'developer-starter' ),
                    ),
                    $style_payload,
                    __( '如 32px / 2rem / clamp(...)', 'developer-starter' )
                );
                $this->render_responsive_table(
                    $idx,
                    '_ds_style',
                    'spacing.padding',
                    __( '内边距', 'developer-starter' ),
                    array(
                        'top'    => __( '上', 'developer-starter' ),
                        'right'  => __( '右', 'developer-starter' ),
                        'bottom' => __( '下', 'developer-starter' ),
                        'left'   => __( '左', 'developer-starter' ),
                    ),
                    $style_payload,
                    __( '如 24px / 4vw / clamp(...)', 'developer-starter' )
                );
                ?>
            </div>

            <div style="margin-top:var(--qiling-space-18);">
                <p style="margin:0 0 var(--qiling-space-8); font-weight:600;"><?php esc_html_e( '排版', 'developer-starter' ); ?></p>
                <?php
                if ( ! empty( $capabilities['title'] ) ) $this->render_typography_group( $idx, '_ds_style', 'typography.title', __( '模块主标题', 'developer-starter' ), $style_payload );
                if ( ! empty( $capabilities['subtitle'] ) ) $this->render_typography_group( $idx, '_ds_style', 'typography.subtitle', __( '模块副标题', 'developer-starter' ), $style_payload );
                if ( ! empty( $capabilities['text'] ) ) $this->render_typography_group( $idx, '_ds_style', 'typography.text', __( '模块正文', 'developer-starter' ), $style_payload );
                if ( ! empty( $capabilities['buttons'] ) ) $this->render_typography_group( $idx, '_ds_style', 'typography.button', __( '模块行动按钮文字', 'developer-starter' ), $style_payload );
                ?>
            </div>

            <div style="margin-top:var(--qiling-space-18);">
                <p style="margin:0 0 var(--qiling-space-8); font-weight:600;"><?php esc_html_e( '背景与边框', 'developer-starter' ); ?></p>
                <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:var(--qiling-space-12);">
                    <?php
                    $this->render_text_input( $idx, '_ds_style', 'background.color', __( '背景颜色 / 渐变', 'developer-starter' ), $this->get_nested_string( $style_payload, 'background.color' ), __( 'var(--color-neutral-0) 或 linear-gradient(...)', 'developer-starter' ) );
                    $this->render_text_input( $idx, '_ds_style', 'background.image', __( '背景图片 URL', 'developer-starter' ), $this->get_nested_string( $style_payload, 'background.image' ), __( 'https://', 'developer-starter' ) );
                    $this->render_select_input(
                        $idx,
                        '_ds_style',
                        'background.size',
                        __( '背景尺寸', 'developer-starter' ),
                        $this->get_nested_string( $style_payload, 'background.size' ),
                        array(
                            ''          => __( '默认', 'developer-starter' ),
                            'cover'     => 'cover',
                            'contain'   => 'contain',
                            'auto'      => 'auto',
                            '100% 100%' => '100% 100%',
                        )
                    );
                    $this->render_select_input(
                        $idx,
                        '_ds_style',
                        'background.position',
                        __( '背景位置', 'developer-starter' ),
                        $this->get_nested_string( $style_payload, 'background.position' ),
                        array(
                            ''              => __( '默认', 'developer-starter' ),
                            'center center' => __( '居中', 'developer-starter' ),
                            'top center'    => __( '顶部居中', 'developer-starter' ),
                            'bottom center' => __( '底部居中', 'developer-starter' ),
                            'center left'   => __( '左侧居中', 'developer-starter' ),
                            'center right'  => __( '右侧居中', 'developer-starter' ),
                        )
                    );
                    $this->render_select_input(
                        $idx,
                        '_ds_style',
                        'background.repeat',
                        __( '背景重复', 'developer-starter' ),
                        $this->get_nested_string( $style_payload, 'background.repeat' ),
                        array(
                            ''          => __( '默认', 'developer-starter' ),
                            'no-repeat' => 'no-repeat',
                            'repeat'    => 'repeat',
                            'repeat-x'  => 'repeat-x',
                            'repeat-y'  => 'repeat-y',
                        )
                    );
                    $this->render_text_input( $idx, '_ds_style', 'border.width', __( '边框宽度', 'developer-starter' ), $this->get_nested_string( $style_payload, 'border.width' ), __( '如 1px', 'developer-starter' ) );
                    $this->render_select_input(
                        $idx,
                        '_ds_style',
                        'border.style',
                        __( '边框样式', 'developer-starter' ),
                        $this->get_nested_string( $style_payload, 'border.style' ),
                        array(
                            ''       => __( '默认', 'developer-starter' ),
                            'solid'  => 'solid',
                            'dashed' => 'dashed',
                            'dotted' => 'dotted',
                            'double' => 'double',
                        )
                    );
                    $this->render_text_input( $idx, '_ds_style', 'border.color', __( '边框颜色', 'developer-starter' ), $this->get_nested_string( $style_payload, 'border.color' ), __( 'var(--color-border)', 'developer-starter' ) );
                    ?>
                </div>
                <?php
                $this->render_responsive_table(
                    $idx,
                    '_ds_style',
                    'radius',
                    __( '圆角', 'developer-starter' ),
                    array(
                        'value' => __( '半径', 'developer-starter' ),
                    ),
                    $style_payload,
                    __( '如 24px / 1.5rem', 'developer-starter' )
                );
                ?>
            </div>

            <div style="margin-top:var(--qiling-space-18);">
                <p style="margin:0 0 var(--qiling-space-8); font-weight:600;"><?php esc_html_e( '阴影与状态', 'developer-starter' ); ?></p>
                <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:var(--qiling-space-12);">
                    <?php
                    $this->render_text_input( $idx, '_ds_style', 'shadow.default', __( '默认阴影', 'developer-starter' ), $this->get_nested_string( $style_payload, 'shadow.default' ), __( '如 0 18px 48px rgba(var(--qiling-rgb-15-23-42), .12)', 'developer-starter' ) );
                    $this->render_text_input( $idx, '_ds_style', 'shadow.hover', __( '悬停阴影', 'developer-starter' ), $this->get_nested_string( $style_payload, 'shadow.hover' ), __( '如 0 24px 60px rgba(var(--qiling-rgb-15-23-42), .18)', 'developer-starter' ) );
                    $this->render_text_input( $idx, '_ds_style', 'state.hover.background_color', __( '悬停背景色', 'developer-starter' ), $this->get_nested_string( $style_payload, 'state.hover.background_color' ), __( 'var(--color-neutral-900)', 'developer-starter' ) );
                    $this->render_text_input( $idx, '_ds_style', 'state.hover.border_color', __( '悬停边框色', 'developer-starter' ), $this->get_nested_string( $style_payload, 'state.hover.border_color' ), __( 'var(--color-neutral-900)', 'developer-starter' ) );
                    $this->render_text_input( $idx, '_ds_style', 'state.hover.title_color', __( '悬停标题色', 'developer-starter' ), $this->get_nested_string( $style_payload, 'state.hover.title_color' ), __( 'var(--color-neutral-0)', 'developer-starter' ) );
                    ?>
                </div>
            </div>

            <div style="margin-top:var(--qiling-space-18);">
                <p style="margin:0 0 var(--qiling-space-8); font-weight:600;"><?php esc_html_e( '模块显隐', 'developer-starter' ); ?></p>
                <div style="margin-bottom:var(--qiling-space-12);">
                    <?php
                    $this->render_select_input(
                        $idx,
                        '_ds_visibility',
                        'status',
                        __( '模块状态', 'developer-starter' ),
                        $this->get_nested_string( $visibility_payload, 'status', '' ),
                        array(
                            ''       => __( '默认显示', 'developer-starter' ),
                            'show'   => __( '显示', 'developer-starter' ),
                            'hidden' => __( '暂时隐藏（保留全部设置）', 'developer-starter' ),
                        )
                    );
                    ?>
                </div>
                <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:var(--qiling-space-12);">
                    <?php
                    $this->render_select_input(
                        $idx,
                        '_ds_visibility',
                        'desktop',
                        __( '桌面端', 'developer-starter' ),
                        $this->get_nested_string( $visibility_payload, 'desktop', '' ),
                        array(
                            ''  => __( '默认显示', 'developer-starter' ),
                            '1' => __( '显示', 'developer-starter' ),
                            '0' => __( '隐藏', 'developer-starter' ),
                        )
                    );
                    $this->render_select_input(
                        $idx,
                        '_ds_visibility',
                        'tablet',
                        __( '平板端', 'developer-starter' ),
                        $this->get_nested_string( $visibility_payload, 'tablet', '' ),
                        array(
                            ''  => __( '默认显示', 'developer-starter' ),
                            '1' => __( '显示', 'developer-starter' ),
                            '0' => __( '隐藏', 'developer-starter' ),
                        )
                    );
                    $this->render_select_input(
                        $idx,
                        '_ds_visibility',
                        'mobile',
                        __( '手机端', 'developer-starter' ),
                        $this->get_nested_string( $visibility_payload, 'mobile', '' ),
                        array(
                            ''  => __( '默认显示', 'developer-starter' ),
                            '1' => __( '显示', 'developer-starter' ),
                            '0' => __( '隐藏', 'developer-starter' ),
                        )
                    );
                    ?>
                </div>
            </div>

            <?php if ( ! empty( $legacy_payload ) ) : ?>
                <p style="margin:var(--qiling-space-16) 0 0; font-size:var(--qiling-text-rem-0p75); color:var(--color-neutral-500);"><?php esc_html_e( '旧版公共样式字段仍保留兼容，并会同步映射到这套协议里。', 'developer-starter' ); ?></p>
            <?php endif; ?>
        </details>
        <?php
    }

    /**
     * @param string              $classes     原 class。
     * @param string              $module_id   模块 ID。
     * @param array<string,mixed> $module_data 模块数据。
     * @param int|string          $post_id     页面 ID。
     * @return string
     */
    public function filter_wrapper_class( $classes, $module_id, $module_data, $post_id ) {
        unset( $module_id, $post_id );

        if ( ! $this->has_advanced_configuration( $module_data ) ) {
            return (string) $classes;
        }

        $class_list   = preg_split( '/\s+/', trim( (string) $classes ) );
        $class_list   = is_array( $class_list ) ? array_filter( $class_list ) : array();
        $class_list[] = 'qds-advanced-wrapper';

        return implode( ' ', array_unique( $class_list ) );
    }

    /**
     * @param string              $style       原 style。
     * @param string              $module_id   模块 ID。
     * @param array<string,mixed> $module_data 模块数据。
     * @param int|string          $post_id     页面 ID。
     * @return string
     */
    public function filter_wrapper_style( $style, $module_id, $module_data, $post_id ) {
        unset( $module_id, $post_id );

        $vars = $this->compile_wrapper_css_variables( $module_data );
        if ( empty( $vars ) ) {
            return (string) $style;
        }

        $compiled = trim( (string) $style );
        if ( '' !== $compiled && substr( $compiled, -1 ) !== ';' ) {
            $compiled .= ';';
        }

        foreach ( $vars as $property => $value ) {
            if ( '' === $property || '' === $value ) {
                continue;
            }
            $compiled .= $property . ':' . $value . ';';
        }

        return $compiled;
    }

    /**
     * @param string              $attr        原属性。
     * @param string              $module_id   模块 ID。
     * @param array<string,mixed> $module_data 模块数据。
     * @param int|string          $post_id     页面 ID。
     * @return string
     */
    public function filter_wrapper_attr( $attr, $module_id, $module_data, $post_id ) {
        unset( $module_id, $post_id );

        if ( ! $this->has_advanced_configuration( $module_data ) ) {
            return (string) $attr;
        }

        $attr = (string) $attr . ' data-qds-advanced="1"';
        $vars = $this->compile_wrapper_css_variables( $module_data );
        $button_states = array(
            'color'       => array( '--qds-button-color' ),
            'hover-color' => array( '--qds-button-hover-color' ),
            'size'        => array( '--qds-button-size-desktop', '--qds-button-size-tablet', '--qds-button-size-mobile' ),
            'weight'      => array( '--qds-button-weight' ),
            'line-height' => array( '--qds-button-line-height' ),
        );
        foreach ( $button_states as $state => $properties ) {
            foreach ( $properties as $property ) {
                if ( isset( $vars[ $property ] ) && '' !== $vars[ $property ] ) {
                    $attr .= ' data-qds-button-' . $state . '="1"';
                    break;
                }
            }
        }

        return $attr;
    }

    /**
     * 对外暴露模块是否需要高级样式 wrapper，供真实前台渲染链路判定。
     *
     * @param array<string,mixed> $module_data 模块数据。
     * @return bool
     */
    public function module_requires_wrapper( $module_data ) {
        return $this->has_advanced_configuration( $module_data );
    }

    public function module_is_hidden( $module_data ) {
        $visibility = $this->get_visibility_payload( $module_data );
        return 'hidden' === $this->get_nested_string( $visibility, 'status', '' );
    }

    /**
     * @param array<string,mixed> $module_data 模块数据。
     * @return bool
     */
    private function has_advanced_configuration( $module_data ) {
        if ( ! is_array( $module_data ) ) {
            return false;
        }

        return $this->payload_has_non_empty_value( $this->get_runtime_style_payload( $module_data ) )
            || $this->payload_has_non_empty_value( $this->get_visibility_payload( $module_data ) );
    }

    /**
     * @param array<string,mixed> $module_data 模块数据。
     * @return array<string,string>
     */
    private function compile_wrapper_css_variables( $module_data ) {
        if ( ! is_array( $module_data ) ) {
            return array();
        }

        $style_payload      = $this->get_runtime_style_payload( $module_data );
        $visibility_payload = $this->get_visibility_payload( $module_data );

        $vars = array();

        $spacing_map = array(
            'spacing.margin.top.desktop'    => '--qds-margin-top-desktop',
            'spacing.margin.top.tablet'     => '--qds-margin-top-tablet',
            'spacing.margin.top.mobile'     => '--qds-margin-top-mobile',
            'spacing.margin.right.desktop'  => '--qds-margin-right-desktop',
            'spacing.margin.right.tablet'   => '--qds-margin-right-tablet',
            'spacing.margin.right.mobile'   => '--qds-margin-right-mobile',
            'spacing.margin.bottom.desktop' => '--qds-margin-bottom-desktop',
            'spacing.margin.bottom.tablet'  => '--qds-margin-bottom-tablet',
            'spacing.margin.bottom.mobile'  => '--qds-margin-bottom-mobile',
            'spacing.margin.left.desktop'   => '--qds-margin-left-desktop',
            'spacing.margin.left.tablet'    => '--qds-margin-left-tablet',
            'spacing.margin.left.mobile'    => '--qds-margin-left-mobile',
            'spacing.padding.top.desktop'   => '--qds-padding-top-desktop',
            'spacing.padding.top.tablet'    => '--qds-padding-top-tablet',
            'spacing.padding.top.mobile'    => '--qds-padding-top-mobile',
            'spacing.padding.right.desktop' => '--qds-padding-right-desktop',
            'spacing.padding.right.tablet'  => '--qds-padding-right-tablet',
            'spacing.padding.right.mobile'  => '--qds-padding-right-mobile',
            'spacing.padding.bottom.desktop' => '--qds-padding-bottom-desktop',
            'spacing.padding.bottom.tablet' => '--qds-padding-bottom-tablet',
            'spacing.padding.bottom.mobile' => '--qds-padding-bottom-mobile',
            'spacing.padding.left.desktop'  => '--qds-padding-left-desktop',
            'spacing.padding.left.tablet'   => '--qds-padding-left-tablet',
            'spacing.padding.left.mobile'   => '--qds-padding-left-mobile',
            'radius.desktop'                => '--qds-radius-desktop',
            'radius.tablet'                 => '--qds-radius-tablet',
            'radius.mobile'                 => '--qds-radius-mobile',
            'typography.title.size.desktop' => '--qds-title-size-desktop',
            'typography.title.size.tablet'  => '--qds-title-size-tablet',
            'typography.title.size.mobile'  => '--qds-title-size-mobile',
            'typography.subtitle.size.desktop' => '--qds-subtitle-size-desktop',
            'typography.subtitle.size.tablet'  => '--qds-subtitle-size-tablet',
            'typography.subtitle.size.mobile'  => '--qds-subtitle-size-mobile',
            'typography.text.size.desktop'  => '--qds-text-size-desktop',
            'typography.text.size.tablet'   => '--qds-text-size-tablet',
            'typography.text.size.mobile'   => '--qds-text-size-mobile',
            'typography.button.size.desktop' => '--qds-button-size-desktop',
            'typography.button.size.tablet'  => '--qds-button-size-tablet',
            'typography.button.size.mobile'  => '--qds-button-size-mobile',
        );

        foreach ( $spacing_map as $path => $css_var ) {
            $value = $this->sanitize_spacing_value( $this->get_nested_string( $style_payload, $path ) );
            if ( '' !== $value ) {
                $vars[ $css_var ] = $value;
            }
        }

        $title_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'typography.title.color' ) );
        if ( '' !== $title_color ) {
            $vars['--qds-title-color'] = $title_color;
        }

        $title_weight = $this->sanitize_font_weight( $this->get_nested_string( $style_payload, 'typography.title.weight' ) );
        if ( '' !== $title_weight ) {
            $vars['--qds-title-weight'] = $title_weight;
        }

        $title_line_height = $this->sanitize_line_height( $this->get_nested_string( $style_payload, 'typography.title.line_height' ) );
        if ( '' !== $title_line_height ) {
            $vars['--qds-title-line-height'] = $title_line_height;
        }

        foreach ( array( 'subtitle', 'text', 'button' ) as $typography_role ) {
            $role_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'typography.' . $typography_role . '.color' ) );
            if ( '' !== $role_color ) {
                $vars[ '--qds-' . $typography_role . '-color' ] = $role_color;
                if ( 'button' === $typography_role ) {
                    $vars['--qiling-module-button-text'] = $role_color;
                    $vars['--qiling-component-button-text'] = $role_color;
                }
            }

            $role_weight = $this->sanitize_font_weight( $this->get_nested_string( $style_payload, 'typography.' . $typography_role . '.weight' ) );
            if ( '' !== $role_weight ) {
                $vars[ '--qds-' . $typography_role . '-weight' ] = $role_weight;
            }

            $role_line_height = $this->sanitize_line_height( $this->get_nested_string( $style_payload, 'typography.' . $typography_role . '.line_height' ) );
            if ( '' !== $role_line_height ) {
                $vars[ '--qds-' . $typography_role . '-line-height' ] = $role_line_height;
            }
        }

        $button_hover_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'typography.button.hover_color' ) );
        if ( '' !== $button_hover_color ) {
            $vars['--qds-button-hover-color'] = $button_hover_color;
            $vars['--qiling-module-button-hover-text'] = $button_hover_color;
            $vars['--qiling-component-button-hover-text'] = $button_hover_color;
        }

        $background_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'background.color' ) );
        if ( '' !== $background_color ) {
            $vars['--qds-background-color'] = $background_color;
        }

        $background_image = $this->sanitize_background_image( $this->get_nested_string( $style_payload, 'background.image' ) );
        if ( '' !== $background_image ) {
            $vars['--qds-background-image'] = $background_image;
        }

        $background_size = $this->sanitize_background_size( $this->get_nested_string( $style_payload, 'background.size' ) );
        if ( '' !== $background_size ) {
            $vars['--qds-background-size'] = $background_size;
        }

        $background_position = $this->sanitize_background_position( $this->get_nested_string( $style_payload, 'background.position' ) );
        if ( '' !== $background_position ) {
            $vars['--qds-background-position'] = $background_position;
        }

        $background_repeat = $this->sanitize_background_repeat( $this->get_nested_string( $style_payload, 'background.repeat' ) );
        if ( '' !== $background_repeat ) {
            $vars['--qds-background-repeat'] = $background_repeat;
        }

        $border_width = $this->sanitize_spacing_value( $this->get_nested_string( $style_payload, 'border.width' ) );
        if ( '' !== $border_width ) {
            $vars['--qds-border-width'] = $border_width;
        }

        $border_style = $this->sanitize_border_style( $this->get_nested_string( $style_payload, 'border.style' ) );
        if ( '' !== $border_style ) {
            $vars['--qds-border-style'] = $border_style;
        } elseif ( '' !== $border_width ) {
            $vars['--qds-border-style'] = 'solid';
        }

        $border_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'border.color' ) );
        if ( '' !== $border_color ) {
            $vars['--qds-border-color'] = $border_color;
        }

        $shadow_default = $this->sanitize_shadow_value( $this->get_nested_string( $style_payload, 'shadow.default' ) );
        if ( '' !== $shadow_default ) {
            $vars['--qds-shadow-default'] = $shadow_default;
        }

        $shadow_hover = $this->sanitize_shadow_value( $this->get_nested_string( $style_payload, 'shadow.hover' ) );
        if ( '' !== $shadow_hover ) {
            $vars['--qds-shadow-hover'] = $shadow_hover;
        }

        $hover_background_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'state.hover.background_color' ) );
        if ( '' !== $hover_background_color ) {
            $vars['--qds-hover-background-color'] = $hover_background_color;
        }

        $hover_border_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'state.hover.border_color' ) );
        if ( '' !== $hover_border_color ) {
            $vars['--qds-hover-border-color'] = $hover_border_color;
        }

        $hover_title_color = $this->sanitize_css_color_value( $this->get_nested_string( $style_payload, 'state.hover.title_color' ) );
        if ( '' !== $hover_title_color ) {
            $vars['--qds-hover-title-color'] = $hover_title_color;
        }

        foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
            $visible = $this->normalize_visibility_value( $this->get_nested_string( $visibility_payload, $device, '1' ) );
            $vars[ '--qds-display-' . $device ] = ( '0' === $visible ) ? 'none' : 'block';
        }

        return $vars;
    }

    /**
     * @param array<string,mixed> $module_data 模块数据。
     * @return array<string,mixed>
     */
    private function get_style_payload( $module_data ) {
        return ( isset( $module_data['_ds_style'] ) && is_array( $module_data['_ds_style'] ) ) ? $module_data['_ds_style'] : array();
    }

    /**
     * 运行时仅消费真正需要 wrapper 的高级样式，
     * legacy 兼容镜像不参与前台样式计算，避免与旧模块渲染链路双写叠加。
     *
     * @param array<string,mixed> $module_data 模块数据。
     * @return array<string,mixed>
     */
    private function get_runtime_style_payload( $module_data ) {
        $style_payload = $this->get_style_payload( $module_data );
        if ( isset( $style_payload['legacy'] ) ) {
            unset( $style_payload['legacy'] );
        }

        return $style_payload;
    }

    /**
     * @param array<string,mixed> $module_data 模块数据。
     * @return array<string,mixed>
     */
    private function get_visibility_payload( $module_data ) {
        return ( isset( $module_data['_ds_visibility'] ) && is_array( $module_data['_ds_visibility'] ) ) ? $module_data['_ds_visibility'] : array();
    }

    /**
     * @param mixed $value 任意值。
     * @return bool
     */
    private function payload_has_non_empty_value( $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $item ) {
                if ( $this->payload_has_non_empty_value( $item ) ) {
                    return true;
                }
            }
            return false;
        }

        return is_scalar( $value ) && trim( (string) $value ) !== '';
    }

    /**
     * @param array<string,mixed> $payload  数据。
     * @param string              $path     点路径。
     * @param string              $default  默认值。
     * @return string
     */
    private function get_nested_string( $payload, $path, $default = '' ) {
        if ( ! is_array( $payload ) || '' === $path ) {
            return (string) $default;
        }

        $current = $payload;
        foreach ( explode( '.', $path ) as $segment ) {
            if ( ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
                return (string) $default;
            }
            $current = $current[ $segment ];
        }

        return is_scalar( $current ) ? trim( (string) $current ) : (string) $default;
    }

    /**
     * @param int                 $idx         模块索引。
     * @param string              $root_field  根字段。
     * @param string              $base_path   基础路径。
     * @param string              $title       标题。
     * @param array<string,mixed> $rows        行定义。
     * @param array<string,mixed> $payload     当前值。
     * @param string              $placeholder 占位符。
     * @return void
     */
    private function render_responsive_table( $idx, $root_field, $base_path, $title, $rows, $payload, $placeholder ) {
        $devices = array(
            'desktop' => __( '桌面', 'developer-starter' ),
            'tablet'  => __( '平板', 'developer-starter' ),
            'mobile'  => __( '手机', 'developer-starter' ),
        );
        ?>
        <div style="margin-bottom:var(--qiling-space-14);">
            <div style="margin:0 0 var(--qiling-space-6); color:var(--color-neutral-600); font-size:var(--qiling-text-rem-0p75);"><?php echo esc_html( $title ); ?></div>
            <table class="widefat striped" style="margin:0; font-size:var(--qiling-text-rem-0p75);">
                <thead>
                    <tr>
                        <th style="width:90px;"><?php esc_html_e( '方向', 'developer-starter' ); ?></th>
                        <?php foreach ( $devices as $label ) : ?>
                            <th><?php echo esc_html( $label ); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $rows as $row_key => $row_label ) : ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html( $row_label ); ?></td>
                            <?php foreach ( array_keys( $devices ) as $device ) : ?>
                                <td>
                                    <?php
                                    $path  = ( 'value' === $row_key ) ? $base_path . '.' . $device : $base_path . '.' . $row_key . '.' . $device;
                                    $value = $this->get_nested_string( $payload, $path );
                                    ?>
                                    <input type="text" name="<?php echo esc_attr( $this->build_nested_field_name( $idx, $root_field, $path ) ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" style="width:100%;"/>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * @param int                 $idx        模块索引。
     * @param string              $root_field 根字段。
     * @param string              $base_path  路径。
     * @param string              $title      标题。
     * @param array<string,mixed> $payload    当前值。
     * @return void
     */
    private function render_typography_group( $idx, $root_field, $base_path, $title, $payload ) {
        ?>
        <div style="border:1px solid var(--color-neutral-200); border-radius:var(--qiling-space-8); padding:var(--qiling-space-12); margin-bottom:var(--qiling-space-12);">
            <div style="margin:0 0 var(--qiling-space-10); font-weight:600; color:var(--color-neutral-900);"><?php echo esc_html( $title ); ?></div>
            <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:var(--qiling-space-12);">
                <?php
                $this->render_text_input( $idx, $root_field, $base_path . '.color', __( '颜色', 'developer-starter' ), $this->get_nested_string( $payload, $base_path . '.color' ), __( 'var(--color-neutral-900)', 'developer-starter' ) );
                $this->render_select_input(
                    $idx,
                    $root_field,
                    $base_path . '.weight',
                    __( '字重', 'developer-starter' ),
                    $this->get_nested_string( $payload, $base_path . '.weight' ),
                    array(
                        ''       => __( '默认', 'developer-starter' ),
                        '300'    => '300',
                        '400'    => '400',
                        '500'    => '500',
                        '600'    => '600',
                        '700'    => '700',
                        '800'    => '800',
                        '900'    => '900',
                        'normal' => 'normal',
                        'bold'   => 'bold',
                    )
                );
                $this->render_text_input( $idx, $root_field, $base_path . '.line_height', __( '行高', 'developer-starter' ), $this->get_nested_string( $payload, $base_path . '.line_height' ), __( '如 1.6 / 28px', 'developer-starter' ) );
                if ( 'typography.button' === $base_path ) {
                    $this->render_text_input( $idx, $root_field, $base_path . '.hover_color', __( '悬停文字颜色', 'developer-starter' ), $this->get_nested_string( $payload, $base_path . '.hover_color' ), __( '#ffffff', 'developer-starter' ) );
                }
                ?>
            </div>
            <?php
            $this->render_responsive_table(
                $idx,
                $root_field,
                $base_path . '.size',
                __( '字号', 'developer-starter' ),
                array(
                    'value' => __( '字号', 'developer-starter' ),
                ),
                $payload,
                __( '如 18px / clamp(...)', 'developer-starter' )
            );
            ?>
        </div>
        <?php
    }

    /**
     * @param int    $idx        模块索引。
     * @param string $root_field 根字段。
     * @param string $path       点路径。
     * @param string $label      标签。
     * @param string $value      当前值。
     * @param string $placeholder 占位符。
     * @return void
     */
    private function render_text_input( $idx, $root_field, $path, $label, $value, $placeholder = '' ) {
        ?>
        <div>
            <label style="display:block; margin-bottom:var(--qiling-space-6); font-size:var(--qiling-text-rem-0p75); color:var(--color-neutral-700);"><?php echo esc_html( $label ); ?></label>
            <input type="text" name="<?php echo esc_attr( $this->build_nested_field_name( $idx, $root_field, $path ) ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" style="width:100%;"/>
        </div>
        <?php
    }

    /**
     * @param int                 $idx        模块索引。
     * @param string              $root_field 根字段。
     * @param string              $path       点路径。
     * @param string              $label      标签。
     * @param string              $value      当前值。
     * @param array<string,string> $options   选项。
     * @return void
     */
    private function render_select_input( $idx, $root_field, $path, $label, $value, $options ) {
        ?>
        <div>
            <label style="display:block; margin-bottom:var(--qiling-space-6); font-size:var(--qiling-text-rem-0p75); color:var(--color-neutral-700);"><?php echo esc_html( $label ); ?></label>
            <select name="<?php echo esc_attr( $this->build_nested_field_name( $idx, $root_field, $path ) ); ?>" style="width:100%;">
                <?php foreach ( $options as $option_value => $option_label ) : ?>
                    <option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * @param int    $idx        模块索引。
     * @param string $root_field 根字段。
     * @param string $path       点路径。
     * @return string
     */
    private function build_nested_field_name( $idx, $root_field, $path ) {
        $name = 'modules[' . absint( $idx ) . '][data][' . $root_field . ']';
        foreach ( explode( '.', $path ) as $segment ) {
            $name .= '[' . $segment . ']';
        }

        return $name;
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_spacing_value( $value ) {
        if ( class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            return Module_Manager::sanitize_spacing_value( $value );
        }

        return '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_css_color_value( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        $lower = strtolower( $value );
        if ( in_array( $lower, array( 'transparent', 'currentcolor', 'inherit', 'initial' ), true ) ) {
            return $lower;
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

        if ( preg_match( '/^(?:linear-gradient|radial-gradient|conic-gradient)\([a-z0-9#\-\+\*\/%\.,\s\(\)]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9\-_]+\)$/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_background_image( $value ) {
        $value = esc_url_raw( trim( (string) $value ) );
        if ( '' === $value ) {
            return '';
        }

        return 'url(' . $value . ')';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_background_size( $value ) {
        $value = trim( (string) $value );
        return in_array( $value, array( 'cover', 'contain', 'auto', '100% 100%' ), true ) ? $value : '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_background_position( $value ) {
        $value = trim( (string) $value );
        return in_array( $value, array( 'center center', 'top center', 'bottom center', 'center left', 'center right' ), true ) ? $value : '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_background_repeat( $value ) {
        $value = trim( (string) $value );
        return in_array( $value, array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' ), true ) ? $value : '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_border_style( $value ) {
        $value = trim( (string) $value );
        return in_array( $value, array( 'solid', 'dashed', 'dotted', 'double' ), true ) ? $value : '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_shadow_value( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>\"\']/', $value ) ) {
            return '';
        }

        if ( preg_match( '/^[a-z0-9#\-\+\*\/%\.,\s\(\)]+$/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_line_height( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        if ( preg_match( '/^(?:\d+|\d*\.\d+)$/', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^(?:\d+|\d*\.\d+)(?:px|rem|em|%)$/', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_font_weight( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        return in_array( $value, array( '300', '400', '500', '600', '700', '800', '900', 'normal', 'bold' ), true ) ? $value : '';
    }

    /**
     * @param mixed $value 原始值。
     * @return string
     */
    private function normalize_visibility_value( $value ) {
        $value = trim( (string) $value );
        return in_array( $value, array( '0', '1' ), true ) ? $value : '1';
    }
}
