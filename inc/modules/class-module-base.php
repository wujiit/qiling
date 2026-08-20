<?php
/**
 * Module Base Class
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Module_Base {

    protected $id;
    protected $name;
    protected $description = '';
    protected $icon = 'dashicons-layout';
    protected $category = 'general';
    protected $fields = array();

    abstract public function get_id();
    abstract public function get_name();
    abstract public function render( $data = array() );

    public function get_description() {
        return $this->description;
    }

    public function get_icon() {
        return $this->icon;
    }

    public function get_category() {
        return $this->category;
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_default_data() {
        $defaults = array();
        foreach ( $this->get_fields() as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }
            if ( isset( $field['type'] ) && 'repeater' === $field['type'] ) {
                $defaults[ $field['id'] ] = isset( $field['default_items'] ) && is_array( $field['default_items'] ) ? $field['default_items'] : array();
                continue;
            }
            $defaults[ $field['id'] ] = isset( $field['default'] ) ? $field['default'] : '';
        }
        return $defaults;
    }

    /**
     * 加载模块模板片段。
     *
     * @param string              $template 模块模板名，相对 template-parts/modules/ 且不含 .php。
     * @param array<string,mixed> $data     传给模板的参数，模板中通过 $args 读取。
     * @return void
     */
    protected function get_template_part( $template, $data = array() ) {
        $template = str_replace( '\\', '/', (string) $template );
        $template = ltrim( $template, '/' );
        $template = (string) preg_replace( '#/+#', '/', $template );

        if (
            '' === $template ||
            false !== strpos( $template, "\0" ) ||
            1 === preg_match( '#(^|/)\.\.(/|$)#', $template ) ||
            1 !== preg_match( '#^[A-Za-z0-9/_-]+$#', $template ) ||
            ( function_exists( 'validate_file' ) && 0 !== validate_file( $template ) )
        ) {
            return;
        }

        $relative_template = 'template-parts/modules/' . $template . '.php';

        if ( function_exists( 'developer_starter_locate_child_aware_template' ) ) {
            $template_path = developer_starter_locate_child_aware_template( $relative_template );
        } else {
            $template_path = DEVELOPER_STARTER_DIR . '/' . $relative_template;
        }

        $template_path = apply_filters(
            'developer_starter_module_template_path',
            $template_path,
            $template,
            $data,
            $this
        );
        if (
            ! is_string( $template_path ) ||
            '' === $template_path ||
            false !== strpos( $template_path, "\0" )
        ) {
            return;
        }

        $real_template_path = realpath( $template_path );
        if (
            ! is_string( $real_template_path ) ||
            ! is_file( $real_template_path ) ||
            ! is_readable( $real_template_path )
        ) {
            return;
        }

        $real_template_path = wp_normalize_path( $real_template_path );
        if ( '.php' !== substr( $real_template_path, -4 ) ) {
            return;
        }

        $allowed_roots = array(
            trailingslashit( get_template_directory() ) . 'template-parts/modules',
        );

        if ( get_stylesheet_directory() !== get_template_directory() ) {
            array_unshift( $allowed_roots, trailingslashit( get_stylesheet_directory() ) . 'template-parts/modules' );
        }

        $allowed_roots = (array) apply_filters(
            'developer_starter_module_template_roots',
            $allowed_roots,
            $relative_template,
            $template,
            $data,
            $this
        );

        $is_allowed_template = false;
        foreach ( $allowed_roots as $allowed_root ) {
            if (
                ! is_string( $allowed_root ) ||
                '' === $allowed_root ||
                false !== strpos( $allowed_root, "\0" )
            ) {
                continue;
            }

            $real_allowed_root = realpath( $allowed_root );
            if ( ! is_string( $real_allowed_root ) || ! is_dir( $real_allowed_root ) ) {
                continue;
            }

            $real_allowed_root = trailingslashit( wp_normalize_path( $real_allowed_root ) );
            if ( 0 === strpos( $real_template_path, $real_allowed_root ) ) {
                $is_allowed_template = true;
                break;
            }
        }

        if ( ! $is_allowed_template ) {
            return;
        }

        load_template( $real_template_path, false, is_array( $data ) ? $data : array() );
    }

    protected function add_field( $id, $label, $type = 'text', $default = '', $options = array() ) {
        $this->fields[] = array(
            'id'      => $id,
            'label'   => $label,
            'type'    => $type,
            'default' => $default,
            'options' => $options,
        );
    }

    /**
     * 生成统一的按钮描边颜色字段。
     *
     * @param string $id 字段 ID。
     * @param string $label 字段标签。
     * @param string $description 字段说明。
     * @param array<string,mixed> $extra 额外字段配置。
     * @return array<string,mixed>
     */
    protected function get_button_border_color_field( $id, $label = '', $description = '', $extra = array() ) {
        return array_merge( array(
            'id'          => $id,
            'type'        => 'color',
            'label'       => $label ? $label : __( '按钮边框颜色', 'developer-starter' ),
            'default'     => '',
            'description' => $description ? $description : __( '留空时跟随按钮背景色；没有单独背景色时跟随全局按钮边框。', 'developer-starter' ),
        ), is_array( $extra ) ? $extra : array() );
    }

    /**
     * 获取交错动画属性
     *
     * @param int $index 当前项目的索引
     * @param int $base_delay 基础延迟 (ms)
     * @param int $step 递增延迟 (ms)
     * @return string
     */
    protected function get_staggered_animation_attr( $index, $base_delay = 100, $step = 100 ) {
        $delay = $base_delay + ( $index * $step );
        // 设置最大延迟限制，避免列表过长时等待太久
        $delay = min( $delay, 1500 );
        return 'data-aos="fade-up" data-aos-delay="' . esc_attr( $delay ) . '"';
    }
}
