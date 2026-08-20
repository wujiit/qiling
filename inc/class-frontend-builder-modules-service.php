<?php
/**
 * Frontend Builder modules service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Manager;
use Developer_Starter\Modules\Module_Standards;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Frontend_Builder_Modules_Service {

    /**
     * @var Builder_Data_Service
     */
    private $builder_data_service;

    /**
     * @param Builder_Data_Service|null $builder_data_service Builder 数据服务。
     */
    public function __construct( $builder_data_service = null ) {
        $this->builder_data_service = $builder_data_service instanceof Builder_Data_Service
            ? $builder_data_service
            : new Builder_Data_Service();
    }

    /**
     * 构建模块 schema 响应载荷。
     *
     * @param string      $module_id 模块 ID。
     * @param object|null $module 模块实例。
     * @return array<string,mixed>
     */
    public function build_module_schema_payload( $module_id, $module ) {
        $fields = ( $module && method_exists( $module, 'get_fields' ) ) ? $module->get_fields() : array();
        if ( class_exists( '\Developer_Starter\Core\Module_Advanced_Style_Service' ) ) {
            $fields = array_merge(
                is_array( $fields ) ? $fields : array(),
                Module_Advanced_Style_Service::get_instance()->get_builder_fields( Module_Standards::get_design_capabilities( $module_id ) )
            );
        }
        if ( class_exists( '\Developer_Starter\Core\Module_Visual_Style_Service' ) ) {
            $fields = array_merge(
                is_array( $fields ) ? $fields : array(),
                Module_Visual_Style_Service::get_instance()->get_builder_fields( $module_id )
            );
        }

        $data_schema  = $this->builder_data_service->build_module_data_schema_map( $fields );
        $default_data = $this->builder_data_service->prepare_module_data_for_editor(
            $module_id,
            $this->build_default_data_from_fields( $fields ),
            $data_schema
        );
        $legacy_common_style_field_ids = $this->builder_data_service->get_declared_legacy_common_style_field_ids( $fields );

        $sanitized_fields = $this->sanitize_field_schema( $fields );
        $style_fields     = array();
        $module_fields    = array();
        $visual_fields    = array();
        $visibility_fields = array();
        foreach ( $sanitized_fields as $field ) {
            $field_id = isset( $field['id'] ) ? (string) $field['id'] : '';
            if ( '' !== $field_id && $this->builder_data_service->is_legacy_common_style_field_id( $field_id ) ) {
                continue;
            }
            if ( '_ds_style' === $field_id ) {
                $style_fields[] = $field;
            } elseif ( '_ds_visual' === $field_id ) {
                $visual_fields[] = $field;
            } elseif ( '_ds_visibility' === $field_id ) {
                $visibility_fields[] = $field;
            } else {
                $module_fields[] = $field;
            }
        }
        $content_fields = array();
        $other_fields   = array();
        foreach ( $module_fields as $field_index => $field ) {
            $field_id = isset( $field['id'] ) ? strtolower( (string) $field['id'] ) : '';
            $role     = '';
            $order    = 50;
            if ( preg_match( '/(^|_)subtitle($|_)/', $field_id ) ) {
                $role  = 'subtitle';
                $order = 20;
            } elseif ( preg_match( '/(^|_)title($|_)/', $field_id ) && ! preg_match( '/item|card|tab|group|column|slide|post|product|service|feature|team|faq|price/', $field_id ) ) {
                $role  = 'title';
                $order = 10;
            }

            if ( '' !== $role ) {
                if ( preg_match( '/(_size|font_size|size$)/', $field_id ) ) {
                    $order += 1;
                } elseif ( preg_match( '/(_color|color$)/', $field_id ) ) {
                    $order += 2;
                }
                $content_fields[] = array(
                    'field' => $field,
                    'order' => $order,
                    'index' => $field_index,
                );
            } else {
                $other_fields[] = $field;
            }
        }
        usort(
            $content_fields,
            static function ( $left, $right ) {
                if ( $left['order'] === $right['order'] ) {
                    return $left['index'] - $right['index'];
                }
                return $left['order'] - $right['order'];
            }
        );
        $content_fields = array_map(
            static function ( $item ) {
                return $item['field'];
            },
            $content_fields
        );
        $sanitized_fields = array_merge( $content_fields, $style_fields, $other_fields, $visual_fields, $visibility_fields );

        return array(
            'id'          => $module_id,
            'name'        => ( $module && method_exists( $module, 'get_name' ) ) ? $module->get_name() : $module_id,
            'fields'      => $sanitized_fields,
            'defaultData' => $default_data,
            'dataSchema'  => $data_schema,
            'dataSchemaVersion' => $this->builder_data_service->get_module_data_schema_version(),
            'builderProtocolVersion' => $this->builder_data_service->get_builder_protocol_version(),
            'reservedProtocolFieldIds' => $this->builder_data_service->get_reserved_protocol_field_ids(),
            'legacyCommonStyleFieldIds' => $legacy_common_style_field_ids,
        );
    }

    /**
     * 统一规范化待保存/预览的模块数组。
     *
     * @param array<int,mixed>    $modules 模块数组。
     * @param array<string,mixed> $args    附加参数。
     * @return array<int,array<string,mixed>>
     */
    public function normalize_modules_for_storage( $modules, $args = array() ) {
        if ( ! is_array( $modules ) ) {
            return array();
        }

        $is_shop_module_callback = isset( $args['is_shop_module_callback'] ) && is_callable( $args['is_shop_module_callback'] )
            ? $args['is_shop_module_callback']
            : null;
        $bootstrap_shop_modules_callback = isset( $args['bootstrap_shop_modules_callback'] ) && is_callable( $args['bootstrap_shop_modules_callback'] )
            ? $args['bootstrap_shop_modules_callback']
            : null;

        if ( $is_shop_module_callback && $bootstrap_shop_modules_callback ) {
            foreach ( $modules as $row ) {
                if ( ! is_array( $row ) || empty( $row['type'] ) ) {
                    continue;
                }
                $module_id = sanitize_key( (string) $row['type'] );
                if ( $module_id !== '' && call_user_func( $is_shop_module_callback, $module_id ) ) {
                    call_user_func( $bootstrap_shop_modules_callback );
                    break;
                }
            }
        }

        return $this->builder_data_service->normalize_modules_for_storage( $modules, $args );
    }

    /**
     * 预览场景下清洗单模块数据。
     *
     * @param array<string,mixed>               $data      模块数据。
     * @param array<string,array<string,mixed>> $schema    模块 schema。
     * @param string                            $module_id 模块 ID。
     * @return array<string,mixed>
     */
    public function sanitize_module_data_for_preview( $data, $schema = array(), $module_id = '' ) {
        $data = $this->builder_data_service->migrate_module_data( $module_id, $data, $schema );

        return $this->builder_data_service->sanitize_module_data( $data, $schema );
    }

    /**
     * 构建模块预览 HTML。
     *
     * @param int                 $post_id post id
     * @param string              $module_id module id
     * @param array<string,mixed> $module_data module data
     * @param int                 $index builder index
     * @return string
     */
    public function build_module_preview_html( $post_id, $module_id, $module_data, $index ) {
        $manager = Module_Manager::get_instance();
        if ( ! $manager->get_module( $module_id ) ) {
            return '';
        }

        $hero_modules = Module_Manager::get_hero_module_ids( 'builder_preview' );
        $enable_scroll_reveal = get_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', true );
        $scroll_attr = ( $enable_scroll_reveal === '1' ) ? ' data-aos="fade-up" data-aos-duration="800"' : '';

        $margin_top = '';
        $margin_bottom = '';
        if ( ! in_array( $module_id, $hero_modules, true ) ) {
            $margin_top = isset( $module_data['module_margin_top'] )
                ? Module_Manager::sanitize_spacing_value( $module_data['module_margin_top'] )
                : '';
            $margin_bottom = isset( $module_data['module_margin_bottom'] )
                ? Module_Manager::sanitize_spacing_value( $module_data['module_margin_bottom'] )
                : '';
        }

        $classes = 'module-wrapper qiling-builder-module';
        $wrapper_style = '';
        if ( $margin_top !== '' ) {
            $wrapper_style .= 'margin-top: ' . esc_attr( $margin_top ) . ';';
            $classes .= ' module-spacing-wrapper';
        }
        if ( $margin_bottom !== '' ) {
            $wrapper_style .= 'margin-bottom: ' . esc_attr( $margin_bottom ) . ';';
            if ( strpos( $classes, 'module-spacing-wrapper' ) === false ) {
                $classes .= ' module-spacing-wrapper';
            }
        }

        $classes = apply_filters( 'developer_starter_module_wrapper_class', $classes, $module_id, $module_data, $post_id );
        $wrapper_style = apply_filters( 'developer_starter_module_wrapper_style', $wrapper_style, $module_id, $module_data, $post_id );
        $scroll_attr = apply_filters( 'developer_starter_module_wrapper_attr', $scroll_attr, $module_id, $module_data, $post_id );
        $scroll_attr = Module_Manager::sanitize_wrapper_attr_fragment( $scroll_attr );

        ob_start();
        echo '<div class="' . esc_attr( $classes ) . '" style="' . esc_attr( $wrapper_style ) . '"' . $scroll_attr . ' data-builder-index="' . esc_attr( (string) $index ) . '" data-module-id="' . esc_attr( $module_id ) . '">';
        $manager->render_module( $module_id, $module_data );
        echo '</div>';

        return (string) ob_get_clean();
    }

    /**
     * @param array<int,mixed> $fields fields
     * @return array<int,array<string,mixed>>
     */
    private function sanitize_field_schema( $fields ) {
        $schema = array();
        if ( ! is_array( $fields ) ) {
            return $schema;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) ) {
                continue;
            }

            $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
            $id = isset( $field['id'] ) ? sanitize_key( (string) $field['id'] ) : '';
            $label = isset( $field['label'] ) ? wp_strip_all_tags( (string) $field['label'] ) : '';

            if ( '' === $id && ! in_array( $type, array( 'header', 'info' ), true ) ) {
                continue;
            }

            $item = array(
                'type'  => $type,
                'id'    => $id,
                'label' => $label,
            );

            if ( isset( $field['default'] ) && is_scalar( $field['default'] ) ) {
                $item['default'] = (string) $field['default'];
            }

            if ( isset( $field['description'] ) ) {
                $item['description'] = wp_kses_post( (string) $field['description'] );
            } elseif ( isset( $field['desc'] ) ) {
                $item['description'] = wp_kses_post( (string) $field['desc'] );
            }

            if ( 'advanced_style' === $type && isset( $field['capabilities'] ) && is_array( $field['capabilities'] ) ) {
                $item['capabilities'] = array_map( 'boolval', $field['capabilities'] );
            }

            if ( isset( $field['dependency'] ) && is_array( $field['dependency'] ) ) {
                $item['dependency'] = $field['dependency'];
            }

            foreach ( array( 'group', 'required', 'sanitize', 'max_items', 'maxItems', 'placeholder', 'rows', 'add_button', 'item_label', 'item_title' ) as $schema_key ) {
                if ( isset( $field[ $schema_key ] ) && is_scalar( $field[ $schema_key ] ) ) {
                    $item[ $schema_key ] = (string) $field[ $schema_key ];
                }
            }

            if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                $options = array();
                foreach ( $field['options'] as $opt_key => $opt_label ) {
                    $options[ (string) $opt_key ] = (string) $opt_label;
                }
                $item['options'] = $options;
            }

            if ( in_array( $type, array( 'range', 'number' ), true ) ) {
                if ( isset( $field['min'] ) && is_scalar( $field['min'] ) ) {
                    $item['min'] = (string) $field['min'];
                }
                if ( isset( $field['max'] ) && is_scalar( $field['max'] ) ) {
                    $item['max'] = (string) $field['max'];
                }
                if ( isset( $field['step'] ) && is_scalar( $field['step'] ) ) {
                    $item['step'] = (string) $field['step'];
                }
            }

            if ( 'repeater' === $type ) {
                $item['fields'] = isset( $field['fields'] ) && is_array( $field['fields'] ) ? $this->sanitize_field_schema( $field['fields'] ) : array();
                if ( isset( $field['default_items'] ) && is_array( $field['default_items'] ) ) {
                    $item['default_items'] = $field['default_items'];
                }
            }

            if ( 'module_visual_style' === $type && isset( $field['groups'] ) && is_array( $field['groups'] ) ) {
                $item['groups'] = $this->sanitize_module_visual_groups( $field['groups'] );
            }

            $schema[] = $item;
        }

        return $schema;
    }

    /**
     * @param array<string,mixed> $groups Module visual groups.
     * @return array<string,array<string,mixed>>
     */
    private function sanitize_module_visual_groups( $groups ) {
        $out = array();
        if ( ! is_array( $groups ) ) {
            return $out;
        }

        foreach ( $groups as $group_key => $group ) {
            $group_key = sanitize_key( (string) $group_key );
            if ( '' === $group_key || ! is_array( $group ) || empty( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
                continue;
            }

            $fields = array();
            foreach ( $group['fields'] as $field_key => $field ) {
                $field_key = sanitize_key( (string) $field_key );
                if ( '' === $field_key || ! is_array( $field ) ) {
                    continue;
                }

                $field_item = array(
                    'label'       => isset( $field['label'] ) && is_scalar( $field['label'] ) ? wp_strip_all_tags( (string) $field['label'] ) : $field_key,
                    'type'        => isset( $field['type'] ) && is_scalar( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'css',
                    'placeholder' => isset( $field['placeholder'] ) && is_scalar( $field['placeholder'] ) ? wp_strip_all_tags( (string) $field['placeholder'] ) : '',
                    'description' => isset( $field['description'] ) && is_scalar( $field['description'] ) ? wp_strip_all_tags( (string) $field['description'] ) : '',
                );

                if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                    $options = array();
                    foreach ( $field['options'] as $option_value => $option_label ) {
                        $options[ (string) $option_value ] = wp_strip_all_tags( (string) $option_label );
                    }
                    $field_item['options'] = $options;
                }

                $fields[ $field_key ] = $field_item;
            }

            if ( empty( $fields ) ) {
                continue;
            }

            $out[ $group_key ] = array(
                'label'       => isset( $group['label'] ) && is_scalar( $group['label'] ) ? wp_strip_all_tags( (string) $group['label'] ) : $group_key,
                'description' => isset( $group['description'] ) && is_scalar( $group['description'] ) ? wp_strip_all_tags( (string) $group['description'] ) : '',
                'fields'      => $fields,
            );
        }

        return $out;
    }

    /**
     * @param array<int,mixed> $fields fields
     * @return array<string,mixed>
     */
    private function build_default_data_from_fields( $fields ) {
        $defaults = array();
        if ( ! is_array( $fields ) ) {
            return $defaults;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }
            $field_id = sanitize_key( (string) $field['id'] );
            $type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';

            if ( '' === $field_id ) {
                continue;
            }

            if ( 'repeater' === $type ) {
                $defaults[ $field_id ] = ( isset( $field['default_items'] ) && is_array( $field['default_items'] ) )
                    ? $field['default_items']
                    : array();
                continue;
            }

            $defaults[ $field_id ] = isset( $field['default'] ) ? $field['default'] : '';
        }

        return $defaults;
    }
}
