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
            $design_capabilities = Module_Standards::get_design_capabilities( $module_id );
            $design_capabilities = Module_Standards::enrich_design_capabilities_from_fields( $fields, $design_capabilities );
            if ( $this->module_has_native_button_controls( $fields ) ) {
                $design_capabilities['buttons'] = false;
            }
            $fields = array_merge(
                is_array( $fields ) ? $fields : array(),
                Module_Advanced_Style_Service::get_instance()->get_builder_fields( $design_capabilities )
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
        $sanitized_fields = $this->sort_builder_module_fields( $module_fields, $module_id );
        if ( class_exists( '\Developer_Starter\Modules\Module_Standards' ) && 'native' === Module_Standards::get_card_layout_strategy( $module_id ) ) {
            foreach ( $visual_fields as &$visual_field ) {
                if ( ! is_array( $visual_field ) || '_ds_visual' !== ( isset( $visual_field['id'] ) ? $visual_field['id'] : '' ) ) {
                    continue;
                }
                if ( isset( $visual_field['groups']['cards']['fields']['mode'] ) ) {
                    unset( $visual_field['groups']['cards']['fields']['mode'] );
                }
            }
            unset( $visual_field );
        }
        $sanitized_fields = array_merge( $sanitized_fields, $style_fields, $visual_fields, $visibility_fields );

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
     * Determine whether a module already exposes its own button controls.
     *
     * Native button fields must take precedence over the generic module button
     * typography control, otherwise the builder shows two competing settings.
     *
     * @param array<int,array<string,mixed>> $fields Module fields.
     * @return bool
     */
    private function module_has_native_button_controls( $fields ) {
        if ( ! is_array( $fields ) ) {
            return false;
        }

        $button_field_count = 0;
        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            $field_id = strtolower( (string) $field['id'] );
            if ( ! preg_match( '/(?:^|_)(?:button|buttons|btn)(?:_|$)/', $field_id ) ) {
                continue;
            }

            if ( 'repeater' === ( isset( $field['type'] ) ? $field['type'] : '' ) ) {
                $button_field_count += 2;
                continue;
            }

            if ( preg_match( '/(?:background|text|border|hover|style|color|url|link)/', $field_id ) ) {
                $button_field_count++;
            }
        }

        return $button_field_count >= 2;
    }

    /**
     * Group fields by the setting object they belong to. Typography and visual
     * properties stay beside their object's copy field in the builder UI.
     *
     * @param array<int,array<string,mixed>> $fields Module fields.
     * @param string                         $module_id Module ID.
     * @return array<int,array<string,mixed>>
     */
    private function sort_builder_module_fields( $fields, $module_id ) {
        $groups = array();
        $order  = array();
        foreach ( $fields as $index => $field ) {
            $field_id = isset( $field['id'] ) ? strtolower( (string) $field['id'] ) : '';
            $group    = $this->get_builder_field_group( $field_id, $field, $module_id );
            if ( ! isset( $groups[ $group ] ) ) {
                $groups[ $group ] = array();
                $order[] = $group;
            }
            $groups[ $group ][] = array( 'field' => $field, 'index' => $index );
        }

        $sorted = array();
        foreach ( $order as $group ) {
            foreach ( $groups[ $group ] as $item ) {
                $item['field']['builderGroup'] = $group;
                $item['field']['builderGroupLabel'] = $this->get_builder_field_group_label( $group );
                $sorted[] = $item['field'];
            }
        }
        return $sorted;
    }

    private function get_builder_field_group( $field_id, $field, $module_id ) {
        if ( '' === $field_id ) {
            return $module_id . ':other';
        }

        if ( 'dynamic_banner' === $module_id ) {
            if ( preg_match( '/^db_title_prefix$|^db_title_color$/', $field_id ) ) return 'dynamic_banner:main_title';
            if ( preg_match( '/^db_typing_|^db_highlight_color$/', $field_id ) ) return 'dynamic_banner:typing_title';
            if ( preg_match( '/^db_subtitle$|^db_text_color$/', $field_id ) ) return 'dynamic_banner:subtitle';
            if ( preg_match( '/^db_desc$|^db_desc_color$/', $field_id ) ) return 'dynamic_banner:description';
            if ( preg_match( '/^db_buttons$|^db_(primary|secondary)_btn_/', $field_id ) ) return 'dynamic_banner:buttons';
            if ( 'db_floating_cards' === $field_id ) return 'dynamic_banner:floating_cards';
            if ( preg_match( '/^db_(media_type|main_image|video_url|image_shadow)$/', $field_id ) ) return 'dynamic_banner:media';
            if ( preg_match( '/^db_bg_/', $field_id ) ) return 'dynamic_banner:background';
        }

        $field_id = preg_replace( '/_(?:size|font|color|effect|gradient|stroke|weight|line_height|letter_spacing|radius|shadow|border|hover|bg|background|overlay|padding|margin|width|height|opacity|animation|delay)$/', '', $field_id );
        if ( preg_match( '/(?:^|_)(?:bg|background|overlay|padding|margin|radius|shadow|border|width|height|opacity)$/', $field_id ) ) {
            return $module_id . ':background';
        }
        if ( preg_match( '/(?:^|_)(title|heading)$/', $field_id ) ) return $module_id . ':title';
        if ( preg_match( '/(?:^|_)subtitle$/', $field_id ) ) return $module_id . ':subtitle';
        if ( preg_match( '/(?:^|_)(desc|description|content|text|excerpt|intro|summary|note)$/', $field_id ) ) return $module_id . ':description';
        if ( preg_match( '/(?:^|_)(button|buttons|url|link|action|cta)$/', $field_id ) ) return $module_id . ':buttons';
        if ( 'repeater' === ( isset( $field['type'] ) ? $field['type'] : '' ) || preg_match( '/(?:^|_)(items|cards|tabs|data)$/', $field_id ) ) return $module_id . ':components';
        if ( preg_match( '/(?:^|_)(layout|mode|style|type|template|media|image|video|orientation|position|columns?)$/', $field_id ) ) return $module_id . ':layout';
        return $module_id . ':other';
    }

    private function get_builder_field_group_label( $group ) {
        $labels = array(
            ':main_title' => '主标题设置', ':typing_title' => '打字标题设置', ':title' => '标题设置',
            ':subtitle' => '副标题设置', ':description' => '描述文案设置', ':buttons' => '按钮设置',
            ':floating_cards' => '悬浮卡片设置', ':media' => '媒体设置', ':components' => '组件设置',
            ':layout' => '模板与布局设置', ':background' => '模块背景设置', ':other' => '其他设置',
        );
        foreach ( $labels as $suffix => $label ) {
            if ( substr( $group, -strlen( $suffix ) ) === $suffix ) return $label;
        }
        return '模块设置';
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

        if (
            class_exists( '\Developer_Starter\Core\Module_Advanced_Style_Service' )
            && Module_Advanced_Style_Service::get_instance()->module_is_hidden( $module_data )
        ) {
            $module = $manager->get_module( $module_id );
            $module_name = $module && method_exists( $module, 'get_name' ) ? $module->get_name() : $module_id;
            return '<div class="module-wrapper qiling-builder-module qfb-hidden-module-placeholder" data-builder-index="' . esc_attr( (string) $index ) . '" data-module-id="' . esc_attr( $module_id ) . '"><div class="qfb-hidden-module-placeholder__inner"><strong>' . esc_html( $module_name ) . '</strong><span>' . esc_html__( '当前模块已暂时隐藏，点击可编辑并恢复显示。', 'developer-starter' ) . '</span></div></div>';
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
