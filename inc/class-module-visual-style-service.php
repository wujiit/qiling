<?php
/**
 * Module Visual Style Service
 *
 * Provides the module-level visual override protocol.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Standards;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Visual_Style_Service {

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
        add_filter( 'developer_starter_module_wrapper_class', array( $this, 'filter_wrapper_class' ), 11, 4 );
        add_filter( 'developer_starter_module_wrapper_style', array( $this, 'filter_wrapper_style' ), 11, 4 );
        add_filter( 'developer_starter_module_wrapper_attr', array( $this, 'filter_wrapper_attr' ), 11, 4 );
    }

    /**
     * Frontend Builder field definition.
     *
     * @return array<int,array<string,mixed>>
     */
    public function get_builder_fields( $module_id = '' ) {
        return array(
            array(
                'id'          => '_ds_visual',
                'type'        => 'module_visual_style',
                'label'       => __( '模块视觉风格', 'developer-starter' ),
                'description' => __( '只影响当前模块，优先级高于当前页面视觉风格。', 'developer-starter' ),
                'groups'      => $this->get_field_groups( $module_id ),
            ),
        );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function get_field_groups( $module_id = '' ) {
        $groups = array(
            'base'    => array(
                'label'       => __( '基础', 'developer-starter' ),
                'description' => __( '控制当前模块主色、辅助色和背景。', 'developer-starter' ),
                'fields'      => array(
                    'mode'         => array(
                        'label'   => __( '简单模式', 'developer-starter' ),
                        'type'    => 'select',
                        'options' => array(
                            'follow' => __( '跟随页面风格', 'developer-starter' ),
                            'light'  => __( '使用浅色模块', 'developer-starter' ),
                            'dark'   => __( '使用深色模块', 'developer-starter' ),
                            'accent' => __( '使用强调色模块', 'developer-starter' ),
                            'custom' => __( '自定义', 'developer-starter' ),
                        ),
                    ),
                    'inherit_page' => array(
                        'label'   => __( '继承页面主色', 'developer-starter' ),
                        'type'    => 'select',
                        'options' => array(
                            '1' => __( '继承', 'developer-starter' ),
                            '0' => __( '不继承', 'developer-starter' ),
                        ),
                    ),
                    'primary'      => array(
                        'label'       => __( '模块主色', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'var(--qiling-page-primary)',
                        'description' => __( '用于当前模块的强调元素，例如图标、标签、链接和局部高亮；不是标题文字颜色。', 'developer-starter' ),
                    ),
                    'accent'       => array(
                        'label'       => __( '模块辅助色', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'var(--qiling-page-accent-2)',
                        'description' => __( '用于当前模块的第二强调色和悬停状态，与模块主色搭配使用。', 'developer-starter' ),
                    ),
                    'background'   => array(
                        'label'       => __( '模块背景', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => '#ffffff',
                        'description' => __( '控制整个当前模块区块的背景颜色或渐变。', 'developer-starter' ),
                    ),
                ),
            ),
            'content' => array(
                'label'       => __( '文字', 'developer-starter' ),
                'description' => __( '控制当前模块中的标题、副标题和正文文字。', 'developer-starter' ),
                'fields'      => array(
                    'title' => array(
                        'label'       => __( '标题颜色', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'var(--qiling-page-text)',
                        'description' => __( '控制模块主标题；兼容模块内使用标准标题类的标题链接。', 'developer-starter' ),
                    ),
                    'subtitle' => array(
                        'label'       => __( '副标题颜色', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'var(--color-text-muted)',
                        'description' => __( '控制模块主标题下方的说明、副标题和描述文字。', 'developer-starter' ),
                    ),
                    'text' => array(
                        'label'       => __( '正文颜色', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'var(--color-text)',
                        'description' => __( '控制当前模块中的普通段落、列表和摘要文字。', 'developer-starter' ),
                    ),
                ),
            ),
            'buttons' => array(
                'label'       => __( '按钮', 'developer-starter' ),
                'description' => __( '覆盖当前模块里的按钮和链接按钮。', 'developer-starter' ),
                'fields'      => array(
                    'background'       => array(
                        'label'       => __( '按钮背景', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'var(--qiling-button-bg)',
                        'description' => __( '控制当前模块实心主按钮的正常背景颜色。', 'developer-starter' ),
                    ),
                    'text'             => array(
                        'label'       => __( '按钮文字', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => '#ffffff',
                        'description' => __( '控制当前模块主按钮正常状态的文字颜色。', 'developer-starter' ),
                    ),
                    'hover_background' => array(
                        'label'       => __( '按钮悬停背景', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'var(--qiling-button-hover-bg)',
                        'description' => __( '控制鼠标移到当前模块主按钮上时的背景颜色。', 'developer-starter' ),
                    ),
                    'hover_text'       => array(
                        'label'       => __( '按钮悬停文字', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => '#ffffff',
                        'description' => __( '控制鼠标移到当前模块主按钮上时的文字颜色。', 'developer-starter' ),
                    ),
                ),
            ),
            'cards'   => array(
                'label'       => __( '卡片', 'developer-starter' ),
                'description' => __( '覆盖当前模块里的卡片背景、边框和阴影。', 'developer-starter' ),
                'fields'      => array(
                    'background' => array(
                        'label'       => __( '卡片背景', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => '#ffffff',
                        'description' => __( '控制当前模块内卡片的背景颜色。', 'developer-starter' ),
                    ),
                    'border'     => array(
                        'label'       => __( '卡片边框', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => 'rgba(15,23,42,.1)',
                        'description' => __( '控制当前模块内卡片的边框颜色。', 'developer-starter' ),
                    ),
                    'shadow'     => array(
                        'label'       => __( '卡片阴影', 'developer-starter' ),
                        'type'        => 'css',
                        'placeholder' => '0 18px 48px rgba(15,23,42,.12)',
                        'description' => __( '控制当前模块内卡片的阴影；填写 none 可关闭阴影。', 'developer-starter' ),
                    ),
                ),
            ),
        );
        if ( '' === $module_id ) {
            return $groups;
        }

        $capabilities = Module_Standards::get_design_capabilities( $module_id );
        if ( empty( $capabilities['title'] ) ) {
            unset( $groups['content']['fields']['title'] );
        }
        if ( empty( $capabilities['subtitle'] ) ) {
            unset( $groups['content']['fields']['subtitle'] );
        }
        if ( empty( $capabilities['text'] ) ) {
            unset( $groups['content']['fields']['text'] );
        }
        if ( empty( $capabilities['buttons'] ) ) {
            unset( $groups['buttons'] );
        }
        if ( empty( $capabilities['cards'] ) ) {
            unset( $groups['cards'] );
        }
        if ( empty( $groups['content']['fields'] ) ) {
            unset( $groups['content'] );
        }

        return $groups;
    }

    /**
     * Render classic admin controls.
     *
     * @param int                 $idx  Module index.
     * @param array<string,mixed> $data Module data.
     * @return void
     */
    public function render_admin_controls( $idx, $data, $module_id = '' ) {
        $payload             = $this->get_visual_payload( $data );
        $has_advanced_values = $this->payload_has_advanced_value( $payload );
        $mode                = $this->get_nested_string( $payload, 'base.mode', '' );
        if ( ! in_array( $mode, array( 'follow', 'light', 'dark', 'accent', 'custom' ), true ) ) {
            $mode = $has_advanced_values ? 'custom' : 'follow';
        } elseif ( 'follow' === $mode && $has_advanced_values ) {
            $mode = 'custom';
        }
        $status_message  = __( '当前模块跟随页面风格。', 'developer-starter' );
        if ( 'custom' === $mode && ! $has_advanced_values ) {
            $status_message = __( '已选择自定义，请在高级设置里填写字段后生效。', 'developer-starter' );
        } elseif ( 'follow' !== $mode ) {
            $status_message = __( '当前模块正在覆盖页面风格。', 'developer-starter' );
        }
        $field_groups    = $this->get_field_groups( $module_id );
        $mode_options    = isset( $field_groups['base']['fields']['mode']['options'] ) ? $field_groups['base']['fields']['mode']['options'] : array();
        $advanced_fields = array(
            array(
                'path'        => 'base.background',
                'label'       => __( '背景色', 'developer-starter' ),
                'placeholder' => '#ffffff / linear-gradient(...)',
                'description' => __( '控制整个当前模块区块的背景。', 'developer-starter' ),
            ),
            array(
                'path'        => 'content.title',
                'label'       => __( '标题颜色', 'developer-starter' ),
                'placeholder' => 'var(--qiling-page-text)',
                'description' => __( '控制当前模块主标题和兼容的标题链接。', 'developer-starter' ),
            ),
            array(
                'path'        => 'content.subtitle',
                'label'       => __( '副标题颜色', 'developer-starter' ),
                'placeholder' => 'var(--color-text-muted)',
                'description' => __( '控制主标题下方的副标题和说明。', 'developer-starter' ),
            ),
            array(
                'path'        => 'content.text',
                'label'       => __( '正文颜色', 'developer-starter' ),
                'placeholder' => 'var(--color-text)',
                'description' => __( '控制普通段落、列表和摘要文字。', 'developer-starter' ),
            ),
            array(
                'path'        => 'buttons.background',
                'label'       => __( '按钮色', 'developer-starter' ),
                'placeholder' => 'var(--qiling-button-bg)',
                'description' => __( '控制当前模块实心主按钮的背景。', 'developer-starter' ),
            ),
            array(
                'path'        => 'buttons.text',
                'label'       => __( '按钮文字', 'developer-starter' ),
                'placeholder' => '#ffffff',
                'description' => __( '控制主按钮正常状态的文字颜色。', 'developer-starter' ),
            ),
            array(
                'path'        => 'buttons.hover_background',
                'label'       => __( '按钮悬停背景', 'developer-starter' ),
                'placeholder' => 'var(--qiling-button-hover-bg)',
                'description' => __( '控制鼠标移到主按钮上时的背景。', 'developer-starter' ),
            ),
            array(
                'path'        => 'buttons.hover_text',
                'label'       => __( '按钮悬停文字', 'developer-starter' ),
                'placeholder' => '#ffffff',
                'description' => __( '控制鼠标移到主按钮上时的文字颜色。', 'developer-starter' ),
            ),
            array(
                'path'        => 'cards.background',
                'label'       => __( '卡片背景', 'developer-starter' ),
                'placeholder' => '#ffffff',
            ),
            array(
                'path'        => 'cards.border',
                'label'       => __( '卡片边框', 'developer-starter' ),
                'placeholder' => 'rgba(15,23,42,.1)',
            ),
        );
        $allowed_paths = array();
        foreach ( $field_groups as $group_key => $group ) {
            foreach ( isset( $group['fields'] ) && is_array( $group['fields'] ) ? array_keys( $group['fields'] ) : array() as $field_key ) {
                $allowed_paths[] = $group_key . '.' . $field_key;
            }
        }
        $advanced_fields = array_values( array_filter( $advanced_fields, static function ( $field ) use ( $allowed_paths ) {
            return ! empty( $field['path'] ) && in_array( $field['path'], $allowed_paths, true );
        } ) );
        ?>
        <details class="dsm-module-visual-style-panel" style="margin-top:var(--qiling-space-20); border-top:1px dashed var(--color-border); padding-top:var(--qiling-space-15);">
            <summary style="cursor:pointer; font-weight:600; color:var(--color-neutral-900); outline:none;"><?php esc_html_e( '模块视觉风格（页面级覆盖）', 'developer-starter' ); ?></summary>
            <p style="margin:var(--qiling-space-10) 0 0; color:var(--color-neutral-500); font-size:var(--qiling-text-rem-0p75);"><?php esc_html_e( '这里的设置只影响当前模块，并优先于当前页面风格。留空字段会继续继承页面或全站。', 'developer-starter' ); ?></p>

            <div data-dsm-module-visual-classic-panel style="margin-top:var(--qiling-space-14); padding:var(--qiling-space-12); border:1px solid var(--color-border); border-radius:8px; background:var(--color-neutral-0);">
                <label style="display:block; margin-bottom:var(--qiling-space-6); font-size:var(--qiling-text-rem-0p75); color:var(--color-neutral-700);"><?php esc_html_e( '简单模式', 'developer-starter' ); ?></label>
                <select name="<?php echo esc_attr( $this->build_nested_field_name( $idx, '_ds_visual', 'base.mode' ) ); ?>" data-dsm-module-visual-input="base.mode" style="width:100%;">
                    <?php foreach ( $mode_options as $option_value => $option_label ) : ?>
                        <option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( $mode, (string) $option_value ); ?>><?php echo esc_html( (string) $option_label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p style="margin:var(--qiling-space-8) 0 0; color:var(--color-neutral-500); font-size:var(--qiling-text-rem-0p75);">
                    <?php echo esc_html( $status_message ); ?>
                </p>
                <p style="display:flex; flex-wrap:wrap; gap:var(--qiling-space-8); margin:var(--qiling-space-10) 0 0;">
                    <button type="button" class="button button-secondary" data-dsm-module-visual-action="follow"><?php esc_html_e( '恢复跟随页面', 'developer-starter' ); ?></button>
                    <button type="button" class="button button-secondary" data-dsm-module-visual-action="sync-primary"><?php esc_html_e( '同步页面主色', 'developer-starter' ); ?></button>
                </p>
            </div>

            <details style="margin-top:var(--qiling-space-14);" <?php echo ( 'custom' === $mode || $has_advanced_values ) ? 'open' : ''; ?>>
                <summary style="cursor:pointer; font-weight:600;"><?php esc_html_e( '高级设置', 'developer-starter' ); ?></summary>
                <div style="display:grid; grid-template-columns:minmax(0, 1fr); gap:var(--qiling-space-12); margin-top:var(--qiling-space-12);">
                    <?php foreach ( $advanced_fields as $field ) : ?>
                        <?php
                        $path  = isset( $field['path'] ) ? (string) $field['path'] : '';
                        $value = $this->get_nested_string( $payload, $path, '' );
                        ?>
                        <div>
                            <label style="display:block; margin-bottom:var(--qiling-space-6); font-size:var(--qiling-text-rem-0p75); color:var(--color-neutral-700);"><?php echo esc_html( isset( $field['label'] ) ? (string) $field['label'] : $path ); ?></label>
                            <input type="text" name="<?php echo esc_attr( $this->build_nested_field_name( $idx, '_ds_visual', $path ) ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( isset( $field['placeholder'] ) ? (string) $field['placeholder'] : '' ); ?>" data-dsm-module-visual-input="<?php echo esc_attr( $path ); ?>" style="width:100%;"/>
                            <?php if ( ! empty( $field['description'] ) ) : ?>
                                <p style="margin:var(--qiling-space-5) 0 0; color:var(--color-neutral-500); font-size:var(--qiling-text-rem-0p75);"><?php echo esc_html( (string) $field['description'] ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        </details>
        <script>
        (function() {
            if (window.qilingModuleVisualClassicBound) {
                return;
            }
            window.qilingModuleVisualClassicBound = true;
            document.addEventListener('click', function(event) {
                var button = event.target.closest('[data-dsm-module-visual-action]');
                if (!button) {
                    return;
                }
                var panel = button.closest('[data-dsm-module-visual-classic-panel]');
                var wrapper = button.closest('.dsm-module-visual-style-panel');
                var root = wrapper || panel;
                var action = button.getAttribute('data-dsm-module-visual-action') || '';
                var setValue = function(path, value) {
                    var input = root ? root.querySelector('[data-dsm-module-visual-input="' + path + '"]') : null;
                    if (input) {
                        input.value = value;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                };
                if ('follow' === action) {
                    Array.prototype.forEach.call(root.querySelectorAll('[data-dsm-module-visual-input]'), function(input) {
                        var path = input.getAttribute('data-dsm-module-visual-input') || '';
                        input.value = 'base.inherit_page' === path ? '1' : ('base.mode' === path ? 'follow' : '');
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                } else if ('sync-primary' === action) {
                    setValue('base.mode', 'custom');
                    setValue('base.background', 'var(--qiling-page-bg,#ffffff)');
                    setValue('buttons.background', 'var(--qiling-button-bg,var(--qiling-page-primary,var(--color-primary)))');
                    setValue('cards.background', 'var(--qiling-component-card-bg,#ffffff)');
                    setValue('cards.border', 'var(--qiling-component-card-border,rgba(15,23,42,.1))');
                }
            });
            document.addEventListener('change', function(event) {
                var input = event.target.closest('[data-dsm-module-visual-input]');
                if (!input) {
                    return;
                }
                var path = input.getAttribute('data-dsm-module-visual-input') || '';
                if (!path || 'base.mode' === path) {
                    return;
                }
                var root = input.closest('.dsm-module-visual-style-panel');
                var modeInput = root ? root.querySelector('[data-dsm-module-visual-input="base.mode"]') : null;
                if (modeInput && 'follow' === modeInput.value) {
                    modeInput.value = 'custom';
                    modeInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }());
        </script>
        <?php
    }

    /**
     * @param string              $classes     Original classes.
     * @param string              $module_id   Module id.
     * @param array<string,mixed> $module_data Module data.
     * @param int|string          $post_id     Post id.
     * @return string
     */
    public function filter_wrapper_class( $classes, $module_id, $module_data, $post_id ) {
        unset( $module_id, $post_id );

        if ( ! $this->has_visual_configuration( $module_data ) ) {
            return (string) $classes;
        }

        $class_list   = preg_split( '/\s+/', trim( (string) $classes ) );
        $class_list   = is_array( $class_list ) ? array_filter( $class_list ) : array();
        $class_list[] = 'qds-visual-wrapper';

        return implode( ' ', array_unique( $class_list ) );
    }

    /**
     * @param string              $style       Original style.
     * @param string              $module_id   Module id.
     * @param array<string,mixed> $module_data Module data.
     * @param int|string          $post_id     Post id.
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
     * @param string              $attr        Original attr.
     * @param string              $module_id   Module id.
     * @param array<string,mixed> $module_data Module data.
     * @param int|string          $post_id     Post id.
     * @return string
     */
    public function filter_wrapper_attr( $attr, $module_id, $module_data, $post_id ) {
        unset( $module_id, $post_id );

        if ( ! $this->has_visual_configuration( $module_data ) ) {
            return (string) $attr;
        }

        return (string) $attr . ' data-qds-visual="1"';
    }

    /**
     * @param array<string,mixed> $module_data Module data.
     * @return bool
     */
    public function module_requires_wrapper( $module_data ) {
        return $this->has_visual_configuration( $module_data );
    }

    /**
     * @param array<string,mixed> $module_data Module data.
     * @return bool
     */
    private function has_visual_configuration( $module_data ) {
        if ( ! is_array( $module_data ) ) {
            return false;
        }

        return $this->payload_has_non_empty_value( $this->get_visual_payload( $module_data ), true );
    }

    /**
     * @param array<string,mixed> $module_data Module data.
     * @return array<string,string>
     */
    private function compile_wrapper_css_variables( $module_data ) {
        $payload = $this->get_visual_payload( $module_data );
        if ( empty( $payload ) ) {
            return array();
        }

        $vars = array();
        $mode = $this->get_nested_string( $payload, 'base.mode', '' );
        if ( in_array( $mode, array( 'light', 'dark', 'accent' ), true ) ) {
            $vars = $this->get_simple_mode_css_variables( $mode );
        }

        if ( '1' === $this->get_nested_string( $payload, 'base.inherit_page', '1' ) ) {
            $vars['--qiling-module-primary']     = 'var(--qiling-page-primary, var(--color-primary))';
            $vars['--qiling-module-accent']      = 'var(--qiling-page-accent-2, var(--qiling-page-accent, var(--color-secondary)))';
        }

        $map = array(
            'base.primary'              => array( '--qiling-module-primary', '--qiling-page-primary', '--qiling-page-accent', '--color-primary' ),
            'base.accent'               => array( '--qiling-module-accent', '--qiling-page-accent-2', '--color-secondary', '--color-accent' ),
            'base.background'           => array( '--qiling-module-bg', '--qds-background-color', '--qiling-page-bg', '--color-background' ),
            'buttons.background'        => array( '--qiling-module-button-bg', '--qiling-button-bg', '--qiling-component-button-bg' ),
            'buttons.text'              => array( '--qiling-module-button-text', '--qiling-button-text', '--qiling-component-button-text' ),
            'buttons.hover_background'  => array( '--qiling-module-button-hover-bg', '--qiling-button-hover-bg', '--qiling-component-button-hover-bg' ),
            'buttons.hover_text'        => array( '--qiling-module-button-hover-text', '--qiling-button-hover-text', '--qiling-component-button-hover-text' ),
            'content.title'             => array( '--qiling-module-title', '--qds-title-color', '--qiling-component-module-title-color' ),
            'content.subtitle'          => array( '--qiling-module-subtitle', '--qds-subtitle-color' ),
            'content.text'              => array( '--qiling-module-text', '--qds-text-color' ),
            'cards.background'          => array( '--qiling-module-card-bg', '--qiling-card-bg', '--qiling-component-card-bg' ),
            'cards.border'              => array( '--qiling-module-card-border', '--qiling-card-border', '--qiling-component-card-border' ),
            'cards.shadow'              => array( '--qiling-module-card-shadow', '--qiling-component-card-shadow' ),
        );

        foreach ( $map as $path => $css_vars ) {
            $value = $this->sanitize_visual_value( $this->get_nested_string( $payload, $path ) );
            if ( '' === $value ) {
                continue;
            }

            foreach ( $css_vars as $css_var ) {
                $vars[ $css_var ] = $value;
            }
        }

        $mirrors = array(
            '--qiling-module-button-bg'         => '--qiling-component-button-bg',
            '--qiling-module-button-text'       => '--qiling-component-button-text',
            '--qiling-module-button-hover-bg'   => '--qiling-component-button-hover-bg',
            '--qiling-module-button-hover-text' => '--qiling-component-button-hover-text',
            '--qiling-module-card-bg'           => '--qiling-component-card-bg',
            '--qiling-module-card-border'       => '--qiling-component-card-border',
            '--qiling-module-card-shadow'       => '--qiling-component-card-shadow',
            '--qiling-module-title'             => '--qiling-component-module-title-color',
        );
        foreach ( $mirrors as $source => $target ) {
            if ( isset( $vars[ $source ] ) && ! isset( $vars[ $target ] ) ) {
                $vars[ $target ] = $vars[ $source ];
            }
        }

        return $vars;
    }

    /**
     * @param string $mode Simple visual mode.
     * @return array<string,string>
     */
    private function get_simple_mode_css_variables( $mode ) {
        if ( 'dark' === $mode ) {
            return array(
                '--qiling-module-bg'                => '#111827',
                '--qds-background-color'            => '#111827',
                '--qiling-module-title'             => '#ffffff',
                '--qiling-module-subtitle'          => 'rgba(255,255,255,0.78)',
                '--qds-title-color'                 => '#ffffff',
                '--qiling-module-text'              => 'rgba(255,255,255,0.78)',
                '--qds-text-color'                  => 'rgba(255,255,255,0.78)',
                '--qiling-module-muted'             => 'rgba(255,255,255,0.56)',
                '--qiling-module-card-bg'           => 'rgba(255,255,255,0.06)',
                '--qiling-module-card-border'       => 'rgba(255,255,255,0.14)',
                '--qiling-module-card-shadow'       => 'none',
                '--qiling-module-button-bg'         => 'var(--qiling-page-primary, var(--color-primary))',
                '--qiling-module-button-text'       => '#ffffff',
                '--qiling-module-button-hover-bg'   => 'var(--qiling-page-accent-2, var(--qiling-page-accent, var(--color-primary-dark)))',
                '--qiling-module-button-hover-text' => '#ffffff',
            );
        }

        if ( 'accent' === $mode ) {
            return array(
                '--qiling-module-bg'                => 'linear-gradient(135deg,var(--qiling-page-primary,var(--color-primary)) 0%,var(--qiling-page-accent-2,var(--qiling-page-accent,var(--color-secondary))) 100%)',
                '--qds-background-color'            => 'var(--qiling-page-primary,var(--color-primary))',
                '--qiling-module-title'             => '#ffffff',
                '--qiling-module-subtitle'          => 'rgba(255,255,255,0.84)',
                '--qds-title-color'                 => '#ffffff',
                '--qiling-module-text'              => 'rgba(255,255,255,0.84)',
                '--qds-text-color'                  => 'rgba(255,255,255,0.84)',
                '--qiling-module-muted'             => 'rgba(255,255,255,0.66)',
                '--qiling-module-card-bg'           => 'rgba(255,255,255,0.14)',
                '--qiling-module-card-border'       => 'rgba(255,255,255,0.24)',
                '--qiling-module-card-shadow'       => '0 18px 42px rgba(15,23,42,0.18)',
                '--qiling-module-button-bg'         => 'rgba(15,23,42,0.9)',
                '--qiling-module-button-text'       => '#ffffff',
                '--qiling-module-button-hover-bg'   => '#0f172a',
                '--qiling-module-button-hover-text' => '#ffffff',
            );
        }

        return array(
            '--qiling-module-bg'                => 'color-mix(in srgb,var(--qiling-page-bg,#f7fbff) 36%,#ffffff 64%)',
            '--qds-background-color'            => 'color-mix(in srgb,var(--qiling-page-bg,#f7fbff) 36%,#ffffff 64%)',
            '--qiling-module-title'             => 'var(--qiling-page-text,var(--color-heading))',
            '--qiling-module-subtitle'          => 'var(--color-text-muted)',
            '--qds-title-color'                 => 'var(--qiling-page-text,var(--color-heading))',
            '--qiling-module-text'              => 'var(--qiling-page-text,var(--color-text))',
            '--qds-text-color'                  => 'var(--qiling-page-text,var(--color-text))',
            '--qiling-module-muted'             => 'var(--color-text-muted)',
            '--qiling-module-card-bg'           => '#ffffff',
            '--qiling-module-card-border'       => 'color-mix(in srgb,var(--qiling-page-primary,#4f7dff) 18%,transparent)',
            '--qiling-module-card-shadow'       => '0 12px 34px rgba(15,23,42,0.08)',
            '--qiling-module-button-bg'         => 'var(--qiling-button-bg,var(--qiling-page-primary,var(--color-primary)))',
            '--qiling-module-button-text'       => 'var(--qiling-button-text,#ffffff)',
            '--qiling-module-button-hover-bg'   => 'var(--qiling-button-hover-bg,var(--qiling-page-accent-2,var(--color-primary-dark)))',
            '--qiling-module-button-hover-text' => 'var(--qiling-button-hover-text,#ffffff)',
        );
    }

    /**
     * @param array<string,mixed> $module_data Module data.
     * @return array<string,mixed>
     */
    private function get_visual_payload( $module_data ) {
        return ( isset( $module_data['_ds_visual'] ) && is_array( $module_data['_ds_visual'] ) ) ? $module_data['_ds_visual'] : array();
    }

    /**
     * @param mixed $value Raw value.
     * @return bool
     */
    private function payload_has_non_empty_value( $value, $ignore_inherit_flag = false ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                if ( $ignore_inherit_flag && 'inherit_page' === (string) $key ) {
                    continue;
                }
                if ( $ignore_inherit_flag && 'mode' === (string) $key && in_array( trim( (string) $item ), array( '', 'follow', 'custom' ), true ) ) {
                    continue;
                }
                if ( $this->payload_has_non_empty_value( $item, $ignore_inherit_flag ) ) {
                    return true;
                }
            }
            return false;
        }

        return is_scalar( $value ) && trim( (string) $value ) !== '';
    }

    /**
     * @param mixed $value Raw value.
     * @return bool
     */
    private function payload_has_advanced_value( $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                if ( in_array( (string) $key, array( 'mode', 'inherit_page' ), true ) ) {
                    continue;
                }
                if ( $this->payload_has_advanced_value( $item ) ) {
                    return true;
                }
            }
            return false;
        }

        return is_scalar( $value ) && trim( (string) $value ) !== '';
    }

    /**
     * @param array<string,mixed> $payload Data.
     * @param string              $path    Dot path.
     * @param string              $default Default value.
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
     * @param int    $idx        Module index.
     * @param string $root_field Root field.
     * @param string $path       Dot path.
     * @return string
     */
    private function build_nested_field_name( $idx, $root_field, $path ) {
        $name = 'modules[' . absint( $idx ) . '][data][' . $root_field . ']';
        foreach ( explode( '.', $path ) as $segment ) {
            $name .= '[' . sanitize_key( (string) $segment ) . ']';
        }

        return $name;
    }

    /**
     * @param mixed $value Raw CSS value.
     * @return string
     */
    private function sanitize_visual_value( $value ) {
        if ( function_exists( 'developer_starter_sanitize_page_visual_style_css_value' ) ) {
            return developer_starter_sanitize_page_visual_style_css_value( $value );
        }

        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value ) {
            return '';
        }
        if ( preg_match( '/[;{}<>]/', $value ) || preg_match( '/(?:expression|javascript\s*:|url\s*\()/i', $value ) ) {
            return '';
        }

        return $value;
    }
}
