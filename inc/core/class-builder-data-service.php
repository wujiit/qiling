<?php
/**
 * Builder Data Service
 *
 * 负责前后台装修共用的模块数据 schema 与清洗逻辑。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Builder_Data_Service {

    const MODULE_DATA_SCHEMA_VERSION = '2.0.0';
    const BUILDER_PROTOCOL_VERSION   = '2.0.0';
    const MAX_REPEATER_ITEMS = 100;

    /**
     * 将模块字段定义转换为用于保存清洗的 schema 映射。
     *
     * @param array<int,array<string,mixed>> $fields 字段定义。
     * @return array<string,array<string,mixed>>
     */
    public function build_module_data_schema_map( $fields ) {
        $schema = array();
        if ( ! is_array( $fields ) ) {
            return $schema;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            $field_id = (string) $field['id'];
            $field_type = isset( $field['type'] ) ? (string) $field['type'] : 'text';

            $entry = array(
                'id'   => $field_id,
                'type' => $this->normalize_field_type( $field_type ),
            );

            foreach ( array( 'label', 'desc', 'description', 'group', 'required', 'default', 'min', 'max', 'step', 'sanitize' ) as $schema_key ) {
                if ( array_key_exists( $schema_key, $field ) ) {
                    $entry[ $schema_key ] = $field[ $schema_key ];
                }
            }

            if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                $entry['options'] = $this->normalize_field_options( $field['options'], $field );
            }

            if ( isset( $field['max_items'] ) ) {
                $entry['maxItems'] = absint( $field['max_items'] );
            } elseif ( isset( $field['maxItems'] ) ) {
                $entry['maxItems'] = absint( $field['maxItems'] );
            }

            if ( isset( $field['dependency'] ) ) {
                $entry['dependency'] = $field['dependency'];
            }

            if ( 'repeater' === $field_type && isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
                $entry['fields'] = $this->build_module_data_schema_map( $field['fields'] );
            }

            $schema[ $field_id ] = $entry;
        }

        return $schema + $this->get_builtin_module_data_schema_map();
    }

    /**
     * 获取当前模块数据 schema 版本。
     *
     * @return string
     */
    public function get_module_data_schema_version() {
        if ( class_exists( '\Developer_Starter\Modules\Module_Standards' ) ) {
            return \Developer_Starter\Modules\Module_Standards::get_module_data_schema_version();
        }

        return self::MODULE_DATA_SCHEMA_VERSION;
    }

    /**
     * 获取当前 Builder 协议版本。
     *
     * @return string
     */
    public function get_builder_protocol_version() {
        return self::BUILDER_PROTOCOL_VERSION;
    }

    /**
     * 获取协议保留字段列表。
     *
     * @return array<int,string>
     */
    public function get_reserved_protocol_field_ids() {
        return array_keys( $this->get_reserved_protocol_schema_map() );
    }

    /**
     * 获取旧公共样式字段 ID 列表。
     *
     * @return array<int,string>
     */
    public function get_legacy_common_style_field_ids() {
        return array_keys( $this->get_legacy_common_style_schema_map() );
    }

    /**
     * 从模块原始字段定义里提取实际声明过的旧公共样式字段。
     *
     * @param array<int,array<string,mixed>> $fields 字段定义。
     * @return array<int,string>
     */
    public function get_declared_legacy_common_style_field_ids( $fields ) {
        if ( ! is_array( $fields ) ) {
            return array();
        }

        $declared = array();
        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            $field_id = (string) $field['id'];
            if ( $this->is_legacy_common_style_field_id( $field_id ) ) {
                $declared[] = $field_id;
            }
        }

        return array_values( array_unique( $declared ) );
    }

    /**
     * 当前字段是否属于旧公共样式兼容字段。
     *
     * @param string $field_id 字段 ID。
     * @return bool
     */
    public function is_legacy_common_style_field_id( $field_id ) {
        return in_array( (string) $field_id, $this->get_legacy_common_style_field_ids(), true );
    }

    /**
     * 为编辑器准备模块数据：
     * 先走标准迁移，再把兼容字段映射回统一高级样式入口，供 UI 单入口展示。
     *
     * @param string                            $module_id 模块 ID。
     * @param array<string,mixed>               $data 原始数据。
     * @param array<string,array<string,mixed>> $schema 模块 schema。
     * @param string                            $from_version 来源 schema 版本。
     * @return array<string,mixed>
     */
    public function prepare_module_data_for_editor( $module_id, $data, $schema = array(), $from_version = '' ) {
        $data = $this->migrate_module_data( $module_id, $data, $schema, $from_version );
        $data = $this->normalize_select_values_for_editor( $data, $schema );

        return $this->hydrate_legacy_common_style_fields_for_editor( $data );
    }

    /**
     * 统一清洗模块数据。
     *
     * @param array<string,mixed>               $data   模块数据。
     * @param array<string,array<string,mixed>> $schema 模块 schema。
     * @return array<string,mixed>
     */
    public function sanitize_module_data( $data, $schema = array() ) {
        $out = array();
        if ( ! is_array( $data ) ) {
            return $out;
        }

        $schema = is_array( $schema ) ? $schema + $this->get_builtin_module_data_schema_map() : $this->get_builtin_module_data_schema_map();

        foreach ( $data as $key => $value ) {
            $key = is_string( $key ) ? $key : (string) $key;
            if ( '' === $key ) {
                continue;
            }

            if ( '_ds_dynamic' === $key ) {
                $dynamic = $this->sanitize_dynamic_bindings_for_storage( $value );
                if ( ! empty( $dynamic ) ) {
                    $out[ $key ] = $dynamic;
                }
                continue;
            }

            $field_schema = ( isset( $schema[ $key ] ) && is_array( $schema[ $key ] ) ) ? $schema[ $key ] : array();
            $field_type = isset( $field_schema['type'] ) ? $this->normalize_field_type( (string) $field_schema['type'] ) : '';
            $field_type = $this->normalize_field_type_by_key( $key, $field_type );

            if ( is_array( $value ) ) {
                $child_schema = isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ? $field_schema['fields'] : array();

                if ( 'repeater' === $field_type ) {
                    $max_items = isset( $field_schema['maxItems'] ) ? absint( $field_schema['maxItems'] ) : self::MAX_REPEATER_ITEMS;
                    if ( $max_items <= 0 ) {
                        $max_items = self::MAX_REPEATER_ITEMS;
                    }

                    $items = array();
                    foreach ( array_slice( $value, 0, $max_items, true ) as $item_key => $item_value ) {
                        if ( is_array( $item_value ) ) {
                            $items[ $item_key ] = $this->sanitize_module_data( $item_value, $child_schema );
                        }
                    }
                    $out[ $key ] = $items;
                } else {
                    $out[ $key ] = $this->sanitize_module_data( $value, $child_schema );
                }

                continue;
            }

            $raw_value = is_scalar( $value ) ? (string) $value : '';

            if ( 'select' === $field_type ) {
                $clean = $this->sanitize_select_value( $raw_value, $field_schema );
            } elseif ( in_array( $field_type, array( 'checkbox', 'switch', 'toggle', 'boolean' ), true ) ) {
                $clean = $this->sanitize_boolean_value( $raw_value );
            } elseif ( in_array( $field_type, array( 'number', 'range' ), true ) ) {
                $clean = $this->sanitize_number_value( $raw_value, $field_schema );
            } elseif ( 'color' === $field_type ) {
                $clean = $this->sanitize_css_color_value( $raw_value );
            } elseif ( 'spacing' === $field_type ) {
                $clean = class_exists( '\Developer_Starter\Modules\Module_Manager' )
                    ? Module_Manager::sanitize_spacing_value( $raw_value )
                    : sanitize_text_field( $raw_value );
            } elseif ( in_array( $field_type, array( 'url', 'link' ), true ) ) {
                $clean = $this->sanitize_url_value( $raw_value );
            } elseif ( in_array( $field_type, array( 'image', 'file', 'media' ), true ) ) {
                $clean = esc_url_raw( $raw_value );
            } elseif ( 'textarea' === $field_type ) {
                $clean = sanitize_textarea_field( $raw_value );
            } elseif ( 'editor' === $field_type ) {
                $clean = wp_kses_post( $raw_value );
            } elseif (
                $key === 'module_margin_top'
                || $key === 'module_margin_bottom'
                || $key === 'module_padding_top'
                || $key === 'module_padding_bottom'
            ) {
                $clean = class_exists( '\Developer_Starter\Modules\Module_Manager' )
                    ? Module_Manager::sanitize_spacing_value( $raw_value )
                    : sanitize_text_field( $raw_value );
            } elseif (
                strpos( $key, 'content' ) !== false
                || strpos( $key, 'desc' ) !== false
                || strpos( $key, 'answer' ) !== false
                || strpos( $key, 'subtitle' ) !== false
                || strpos( $key, 'typing_text' ) !== false
            ) {
                $clean = wp_kses_post( $raw_value );
            } elseif ( $key === 'url' || $key === 'link' || preg_match( '/(_url|_link)$/', (string) $key ) ) {
                $placeholder_url = $this->sanitize_supported_placeholder_url( $raw_value );
                if ( '' !== $placeholder_url ) {
                    $clean = $placeholder_url;
                } else {
                    $clean = esc_url_raw( $raw_value, array( 'http', 'https', 'mailto', 'tel' ) );
                }
            } elseif (
                strpos( $key, 'show_' ) !== false
                || strpos( $key, '_show' ) !== false
                || strpos( $key, 'enable_' ) !== false
                || strpos( $key, '_enable' ) !== false
            ) {
                $clean = sanitize_text_field( $raw_value );
            } elseif (
                preg_match( '/(_image|_logo|_file|_qrcode)$/', (string) $key )
                || in_array( $key, array( 'image', 'logo', 'file', 'avatar' ), true )
            ) {
                $clean = esc_url_raw( $raw_value );
            } elseif ( $key === 'icon' || strpos( $key, '_icon' ) !== false || strpos( $key, 'icon_' ) !== false ) {
                if ( false !== strpos( $raw_value, '<svg' ) ) {
                    if ( function_exists( 'developer_starter_sanitize_svg' ) ) {
                        $clean = developer_starter_sanitize_svg( $raw_value );
                    } else {
                        $clean = wp_kses_post( $raw_value );
                    }
                } elseif ( preg_match( '/<[^>]+>/', $raw_value ) ) {
                    $allowed = array(
                        'i'    => array( 'class' => true, 'style' => true, 'aria-hidden' => true ),
                        'span' => array( 'class' => true, 'style' => true, 'aria-hidden' => true ),
                    );
                    $clean = wp_kses( $raw_value, $allowed );
                } else {
                    $clean = sanitize_text_field( $raw_value );
                }
            } elseif (
                $key === 'features'
                || $key === 'specs'
                || $key === 'rh_titles'
                || $key === 'comparison_features'
                || $key === 'values'
                || strpos( $key, 'titles' ) !== false
                || strpos( $key, '_bio' ) !== false
                || $key === 'bio'
            ) {
                // 某些旧链路/兼容链路可能拿不到 schema，这里为多行字段补一个兜底。
                $clean = sanitize_textarea_field( $raw_value );
            } else {
                $clean = sanitize_text_field( $raw_value );
            }

            $out[ $key ] = apply_filters( 'developer_starter_sanitize_module_data', $clean, $key, $raw_value, $data );
        }

        return $out;
    }

    /**
     * Normalize the reserved `_ds_dynamic` protocol group for storage.
     *
     * @param mixed $bindings Raw dynamic bindings.
     * @return array<string,array<string,string>>
     */
    private function sanitize_dynamic_bindings_for_storage( $bindings ) {
        $clean = array();
        if ( ! is_array( $bindings ) ) {
            return $clean;
        }

        foreach ( $bindings as $field_id => $binding ) {
            if ( ! is_scalar( $field_id ) ) {
                continue;
            }

            $field_id = trim( (string) $field_id );
            $field_id = preg_replace( '/[^A-Za-z0-9_\-\.]/', '', $field_id );
            if ( ! is_string( $field_id ) || '' === $field_id ) {
                continue;
            }

            $source = '';
            if ( is_array( $binding ) && isset( $binding['source'] ) && is_scalar( $binding['source'] ) ) {
                $source = (string) $binding['source'];
            } elseif ( is_scalar( $binding ) ) {
                $source = (string) $binding;
            }

            $source = strtolower( trim( $source ) );
            $source = preg_replace( '/[^a-z0-9_\.\-]/', '', $source );
            if ( ! is_string( $source ) || '' === $source ) {
                continue;
            }

            $clean[ $field_id ] = array(
                'source' => $source,
            );
        }

        return $clean;
    }

    /**
     * 统一规范化待保存/预览的模块数组。
     *
     * @param array<int,mixed>       $modules 模块数组。
     * @param array<string,mixed>    $args    附加参数。
     * @return array<int,array<string,mixed>>
     */
    public function normalize_modules_for_storage( $modules, $args = array() ) {
        if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
            $modules = developer_starter_normalize_modules_meta_types( $modules );
        }

        if ( ! is_array( $modules ) ) {
            return array();
        }

        $sanitize_data = ! isset( $args['sanitize_data'] ) || (bool) $args['sanitize_data'];
        $module_exists_callback = isset( $args['module_exists_callback'] ) && is_callable( $args['module_exists_callback'] )
            ? $args['module_exists_callback']
            : null;
        $schema_for_type_callback = isset( $args['schema_for_type_callback'] ) && is_callable( $args['schema_for_type_callback'] )
            ? $args['schema_for_type_callback']
            : null;

        $clean_modules = array();

        foreach ( $modules as $row ) {
            if ( ! is_array( $row ) || empty( $row['type'] ) ) {
                continue;
            }

            $module_id = sanitize_key( (string) $row['type'] );
            if ( '' === $module_id ) {
                continue;
            }

            if ( $module_exists_callback && ! call_user_func( $module_exists_callback, $module_id ) ) {
                continue;
            }

            $schema = array();
            if ( $schema_for_type_callback ) {
                $schema = call_user_func( $schema_for_type_callback, $module_id );
                $schema = is_array( $schema ) ? $schema : array();
            }

            if ( isset( $row['data'] ) && is_array( $row['data'] ) ) {
                $raw_data = $row['data'];
            } elseif ( isset( $row['settings'] ) && is_array( $row['settings'] ) ) {
                $raw_data = $row['settings'];
            } else {
                $raw_data = $row;
                unset( $raw_data['type'], $raw_data['schemaVersion'], $raw_data['schema_version'] );
            }

            if ( is_array( $raw_data ) ) {
                $from_schema_version = '';
                if ( isset( $row['schemaVersion'] ) && is_scalar( $row['schemaVersion'] ) ) {
                    $from_schema_version = (string) $row['schemaVersion'];
                } elseif ( isset( $row['schema_version'] ) && is_scalar( $row['schema_version'] ) ) {
                    $from_schema_version = (string) $row['schema_version'];
                }

                $module_data = $this->migrate_module_data( $module_id, $raw_data, $schema, $from_schema_version );
                $data = $sanitize_data ? $this->sanitize_module_data( $module_data, $schema ) : $module_data;
            } else {
                $data = array();
            }

            $clean_modules[] = array(
                'type'          => $module_id,
                'data'          => $data,
                'schemaVersion' => $this->get_module_data_schema_version(),
            );
        }

        return $clean_modules;
    }

    /**
     * 为模块数据迁移预留统一入口。
     *
     * @param string                            $module_id 模块 ID。
     * @param array<string,mixed>               $data 原始数据。
     * @param array<string,array<string,mixed>> $schema 模块 schema。
     * @param string                            $from_version 来源 schema 版本。
     * @return array<string,mixed>
     */
    public function migrate_module_data( $module_id, $data, $schema = array(), $from_version = '' ) {
        $data = is_array( $data ) ? $data : array();
        $module_id = sanitize_key( (string) $module_id );
        $from_version = is_scalar( $from_version ) ? trim( (string) $from_version ) : '';
        $to_version = $this->get_module_data_schema_version();

        $data = $this->migrate_legacy_common_style_fields_to_protocol( $data );

        if ( 'banner' === $module_id ) {
            $data = $this->migrate_banner_module_data( $data );
        }

        if ( 'magic_layout' === $module_id ) {
            $data = $this->migrate_magic_layout_module_data( $data );
        }

        if ( 'qiling_universal_recommend' === $module_id ) {
            $data = $this->migrate_universal_recommend_module_data( $data );
        }

        /**
         * Filters module data before schema sanitization.
         *
         * This is the future migration entry point for old module payloads. Return the
         * original data when no migration is needed.
         *
         * @param array<string,mixed>               $data 原始模块数据。
         * @param string                            $module_id 模块 ID。
         * @param string                            $from_version 来源版本。
         * @param string                            $to_version 目标版本。
         * @param array<string,array<string,mixed>> $schema 模块 schema。
         */
        $migrated = apply_filters( 'developer_starter_migrate_module_data', $data, $module_id, $from_version, $to_version, $schema );
        $data     = is_array( $migrated ) ? $migrated : $data;

        return $this->sync_protocol_compatibility_fields_to_legacy_common_fields( $module_id, $data );
    }

    /**
     * 将旧版通用查询字段收敛为普通文章分类 ID。
     */
    private function migrate_universal_recommend_module_data( $data ) {
        if ( ! is_array( $data ) || ! empty( $data['qur_category_ids'] ) ) {
            return is_array( $data ) ? $data : array();
        }

        $taxonomy = isset( $data['qur_auto_taxonomy'] ) ? sanitize_key( (string) $data['qur_auto_taxonomy'] ) : '';
        $terms_raw = isset( $data['qur_auto_terms'] ) && is_scalar( $data['qur_auto_terms'] ) ? trim( (string) $data['qur_auto_terms'] ) : '';
        if ( 'category' !== $taxonomy || '' === $terms_raw ) {
            return $data;
        }

        $category_ids = array();
        $slugs = array();
        foreach ( array_filter( array_map( 'trim', explode( ',', $terms_raw ) ) ) as $term ) {
            if ( ctype_digit( $term ) ) {
                $category_ids[] = absint( $term );
            } else {
                $slugs[] = sanitize_title( $term );
            }
        }

        if ( ! empty( $slugs ) ) {
            $terms = get_terms(
                array(
                    'taxonomy'   => 'category',
                    'hide_empty' => false,
                    'slug'       => array_values( array_unique( $slugs ) ),
                )
            );
            if ( ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    if ( $term instanceof \WP_Term ) {
                        $category_ids[] = (int) $term->term_id;
                    }
                }
            }
        }

        $category_ids = array_values( array_unique( array_filter( array_map( 'absint', $category_ids ) ) ) );
        if ( ! empty( $category_ids ) ) {
            $data['qur_category_ids'] = implode( ',', $category_ids );
        }

        return $data;
    }

    /**
     * Keep banner stats-bar data stable across legacy payloads and builder saves.
     *
     * @param array<string,mixed> $data Module data.
     * @return array<string,mixed>
     */
    private function migrate_banner_module_data( $data ) {
        if ( ! is_array( $data ) ) {
            return array();
        }

        if ( array_key_exists( 'show_stats_bar', $data ) ) {
            $data['show_stats_bar'] = $this->normalize_banner_boolean_flag( $data['show_stats_bar'] );
        }

        if ( isset( $data['stats_data'] ) && is_array( $data['stats_data'] ) && ! empty( $data['stats_data'] ) ) {
            $stats_data = $this->normalize_banner_stats_items( $data['stats_data'] );
            if ( ! empty( $stats_data ) ) {
                $data['stats_data'] = $stats_data;
                return $data;
            }
        }

        foreach ( array( 'stats_items', 'items' ) as $legacy_key ) {
            if ( empty( $data[ $legacy_key ] ) || ! is_array( $data[ $legacy_key ] ) ) {
                continue;
            }

            $legacy_stats_data = $this->normalize_banner_stats_items( $data[ $legacy_key ] );
            if ( ! empty( $legacy_stats_data ) ) {
                $data['stats_data'] = $legacy_stats_data;
                break;
            }
        }

        return $data;
    }

    /**
     * @param mixed $value Raw flag value.
     * @return string
     */
    private function normalize_banner_boolean_flag( $value ) {
        if ( is_bool( $value ) ) {
            return $value ? '1' : '0';
        }
        if ( ! is_scalar( $value ) ) {
            return '0';
        }

        $value = strtolower( trim( (string) $value ) );
        return in_array( $value, array( '1', 'yes', 'true', 'on' ), true ) ? '1' : '0';
    }

    /**
     * @param array<mixed,mixed> $items Raw stats items.
     * @return array<int,array<string,mixed>>
     */
    private function normalize_banner_stats_items( $items ) {
        $normalized = array();
        if ( ! is_array( $items ) ) {
            return $normalized;
        }

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $icon = $this->get_first_banner_stats_value( $item, array( 'icon', 'stat_icon' ) );
            $number = $this->get_first_banner_stats_value( $item, array( 'number', 'stat_number', 'stat_value', 'value' ) );
            $label = $this->get_first_banner_stats_value( $item, array( 'label', 'stat_label', 'text' ) );
            $color = $this->get_first_banner_stats_value( $item, array( 'color', 'stat_color' ) );

            if ( '' === trim( (string) $icon ) && '' === trim( (string) $number ) && '' === trim( (string) $label ) ) {
                continue;
            }

            $normalized[] = array(
                'icon'   => $icon,
                'number' => $number,
                'label'  => $label,
                'color'  => $color,
            );
        }

        return $normalized;
    }

    /**
     * @param array<string,mixed> $item Stats item.
     * @param array<int,string>   $keys Candidate keys.
     * @return mixed
     */
    private function get_first_banner_stats_value( $item, $keys ) {
        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $item ) && is_scalar( $item[ $key ] ) && '' !== (string) $item[ $key ] ) {
                return $item[ $key ];
            }
        }

        return '';
    }

    /**
     * 将旧版单区块魔方布局迁移到增强版容器层结构。
     *
     * 这是无损增量迁移：旧字段继续保留，仅在缺少新容器层字段时补齐。
     *
     * @param array<string,mixed> $data 模块数据。
     * @return array<string,mixed>
     */
    private function migrate_magic_layout_module_data( $data ) {
        if ( ! is_array( $data ) ) {
            return array();
        }

        $has_sections = ! empty( $data['magic_layout_sections'] ) && is_array( $data['magic_layout_sections'] );
        $legacy_keys  = array(
            'magic_layout_columns',
            'magic_layout_preset',
            'magic_layout_gap',
            'magic_layout_vertical_align',
            'magic_layout_container_width',
            'magic_layout_surface',
            'magic_layout_column_padding',
        );
        $has_legacy_config = false;

        foreach ( $legacy_keys as $legacy_key ) {
            if ( array_key_exists( $legacy_key, $data ) && '' !== (string) $data[ $legacy_key ] ) {
                $has_legacy_config = true;
                break;
            }
        }

        if ( ! $has_sections && $has_legacy_config ) {
            $data['magic_layout_sections'] = array(
                array(
                    'section_slot'            => '1',
                    'section_label'           => __( '主容器', 'developer-starter' ),
                    'section_columns'         => isset( $data['magic_layout_columns'] ) ? $data['magic_layout_columns'] : '2',
                    'section_preset'          => isset( $data['magic_layout_preset'] ) ? $data['magic_layout_preset'] : 'equal',
                    'section_gap'             => isset( $data['magic_layout_gap'] ) ? $data['magic_layout_gap'] : '28px',
                    'section_vertical_align'  => isset( $data['magic_layout_vertical_align'] ) ? $data['magic_layout_vertical_align'] : 'start',
                    'section_container_width' => isset( $data['magic_layout_container_width'] ) ? $data['magic_layout_container_width'] : 'default',
                    'section_surface'         => isset( $data['magic_layout_surface'] ) ? $data['magic_layout_surface'] : 'none',
                    'section_column_padding'  => isset( $data['magic_layout_column_padding'] ) ? $data['magic_layout_column_padding'] : '24px',
                    'section_background'      => '',
                    'section_shell_padding'   => '',
                    'section_radius'          => '',
                ),
            );
        }

        if ( ! isset( $data['magic_layout_section_gap'] ) || '' === (string) $data['magic_layout_section_gap'] ) {
            $data['magic_layout_section_gap'] = '48px';
        }

        if ( ! empty( $data['magic_layout_elements'] ) && is_array( $data['magic_layout_elements'] ) ) {
            foreach ( $data['magic_layout_elements'] as $index => $element ) {
                if ( ! is_array( $element ) ) {
                    continue;
                }
                if ( empty( $element['section_slot'] ) ) {
                    $element['section_slot'] = '1';
                }
                $data['magic_layout_elements'][ $index ] = $element;
            }
        }

        return $data;
    }

    /**
     * 主题级内置字段 schema，保证导入/预览链路对公共字段一致处理。
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_builtin_module_data_schema_map() {
        return $this->get_legacy_common_style_schema_map() + $this->get_reserved_protocol_schema_map();
    }

    /**
     * 获取旧公共样式字段 schema。
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_legacy_common_style_schema_map() {
        return array(
            'module_margin_top'    => array( 'id' => 'module_margin_top', 'type' => 'spacing' ),
            'module_margin_bottom' => array( 'id' => 'module_margin_bottom', 'type' => 'spacing' ),
            'module_padding_top'   => array( 'id' => 'module_padding_top', 'type' => 'spacing' ),
            'module_padding_bottom' => array( 'id' => 'module_padding_bottom', 'type' => 'spacing' ),
            'module_bg_color'      => array( 'id' => 'module_bg_color', 'type' => 'color' ),
            'module_bg_image'      => array( 'id' => 'module_bg_image', 'type' => 'image' ),
        );
    }

    /**
     * 获取旧公共样式字段与统一高级样式协议之间的映射。
     *
     * @return array<string,array<string,string>>
     */
    private function get_legacy_common_style_protocol_map() {
        return array(
            'module_margin_top' => array(
                'path'       => 'spacing.margin.top.desktop',
                'value_type' => 'spacing',
                'strip_mode' => 'always',
            ),
            'module_margin_bottom' => array(
                'path'       => 'spacing.margin.bottom.desktop',
                'value_type' => 'spacing',
                'strip_mode' => 'always',
            ),
            'module_padding_top' => array(
                'path'       => 'spacing.padding.top.desktop',
                'value_type' => 'spacing',
                'strip_mode' => 'always',
            ),
            'module_padding_bottom' => array(
                'path'       => 'spacing.padding.bottom.desktop',
                'value_type' => 'spacing',
                'strip_mode' => 'always',
            ),
            'module_bg_color' => array(
                'path'       => 'background.color',
                'value_type' => 'paint',
                'strip_mode' => 'always',
            ),
            'module_bg_image' => array(
                'path'       => 'background.image',
                'value_type' => 'image',
                'strip_mode' => 'background_image',
            ),
        );
    }

    /**
     * 获取 Builder 协议保留字段 schema。
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_reserved_protocol_schema_map() {
        return array(
            '_ds_style' => array(
                'id'                   => '_ds_style',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => $this->get_stage_one_style_schema_map(),
            ),
            '_ds_layout' => array(
                'id'                   => '_ds_layout',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(),
            ),
            '_ds_visibility' => array(
                'id'                   => '_ds_visibility',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => $this->get_stage_one_visibility_schema_map(),
            ),
            '_ds_visual' => array(
                'id'                   => '_ds_visual',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => $this->get_module_visual_schema_map(),
            ),
            '_ds_conditions' => array(
                'id'                   => '_ds_conditions',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(),
            ),
            '_ds_dynamic' => array(
                'id'                   => '_ds_dynamic',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(),
            ),
        );
    }

    /**
     * Module-level visual override schema.
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_module_visual_schema_map() {
        return array(
            'base' => array(
                'id'                   => 'base',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(
                    'mode'         => array( 'id' => 'mode', 'type' => 'select', 'options' => array( 'follow', 'light', 'dark', 'accent', 'custom' ) ),
                    'inherit_page' => array( 'id' => 'inherit_page', 'type' => 'select', 'options' => array( '0', '1' ) ),
                    'primary'      => array( 'id' => 'primary', 'type' => 'text' ),
                    'accent'       => array( 'id' => 'accent', 'type' => 'text' ),
                    'background'   => array( 'id' => 'background', 'type' => 'text' ),
                ),
            ),
            'content' => array(
                'id'                   => 'content',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(
                    'title' => array( 'id' => 'title', 'type' => 'text' ),
                    'subtitle' => array( 'id' => 'subtitle', 'type' => 'text' ),
                    'text'  => array( 'id' => 'text', 'type' => 'text' ),
                    'muted' => array( 'id' => 'muted', 'type' => 'text' ),
                ),
            ),
            'buttons' => array(
                'id'                   => 'buttons',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(
                    'background'       => array( 'id' => 'background', 'type' => 'text' ),
                    'text'             => array( 'id' => 'text', 'type' => 'text' ),
                    'hover_background' => array( 'id' => 'hover_background', 'type' => 'text' ),
                    'hover_text'       => array( 'id' => 'hover_text', 'type' => 'text' ),
                ),
            ),
            'cards' => array(
                'id'                   => 'cards',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(
                    'mode'       => array( 'id' => 'mode', 'type' => 'select', 'options' => array( 'native', 'connected', 'independent' ) ),
                    'title'      => array( 'id' => 'title', 'type' => 'text' ),
                    'text'       => array( 'id' => 'text', 'type' => 'text' ),
                    'background' => array( 'id' => 'background', 'type' => 'text' ),
                    'border'     => array( 'id' => 'border', 'type' => 'text' ),
                    'shadow'     => array( 'id' => 'shadow', 'type' => 'text' ),
                    'radius'     => array( 'id' => 'radius', 'type' => 'text' ),
                    'padding'    => array( 'id' => 'padding', 'type' => 'text' ),
                    'gap'        => array( 'id' => 'gap', 'type' => 'text' ),
                    'image_radius' => array( 'id' => 'image_radius', 'type' => 'text' ),
                ),
            ),
        );
    }

    /**
     * 阶段 1：统一高级控件协议 schema。
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_stage_one_style_schema_map() {
        return array(
            'legacy' => array(
                'id'                   => 'legacy',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => $this->get_legacy_common_style_schema_map(),
            ),
            'spacing' => array(
                'id'                   => 'spacing',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(
                    'margin'  => array(
                        'id'     => 'margin',
                        'type'   => 'group',
                        'fields' => $this->get_stage_one_responsive_direction_schema_map(),
                    ),
                    'padding' => array(
                        'id'     => 'padding',
                        'type'   => 'group',
                        'fields' => $this->get_stage_one_responsive_direction_schema_map(),
                    ),
                ),
            ),
            'typography' => array(
                'id'                   => 'typography',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(
                    'title' => array(
                        'id'     => 'title',
                        'type'   => 'group',
                        'fields' => $this->get_stage_one_typography_schema_map(),
                    ),
                    'subtitle' => array(
                        'id'     => 'subtitle',
                        'type'   => 'group',
                        'fields' => $this->get_stage_one_typography_schema_map(),
                    ),
                    'text'  => array(
                        'id'     => 'text',
                        'type'   => 'group',
                        'fields' => $this->get_stage_one_typography_schema_map(),
                    ),
                    'button' => array(
                        'id'     => 'button',
                        'type'   => 'group',
                        'fields' => $this->get_stage_one_typography_schema_map(),
                    ),
                ),
            ),
            'background' => array(
                'id'     => 'background',
                'type'   => 'group',
                'fields' => array(
                    'color'    => array( 'id' => 'color', 'type' => 'color' ),
                    'image'    => array( 'id' => 'image', 'type' => 'image' ),
                    'size'     => array(
                        'id'      => 'size',
                        'type'    => 'select',
                        'options' => array(
                            'cover'     => 'cover',
                            'contain'   => 'contain',
                            'auto'      => 'auto',
                            '100% 100%' => '100% 100%',
                        ),
                    ),
                    'position' => array(
                        'id'      => 'position',
                        'type'    => 'select',
                        'options' => array(
                            'center center' => 'center center',
                            'top center'    => 'top center',
                            'bottom center' => 'bottom center',
                            'center left'   => 'center left',
                            'center right'  => 'center right',
                        ),
                    ),
                    'repeat'   => array(
                        'id'      => 'repeat',
                        'type'    => 'select',
                        'options' => array(
                            'no-repeat' => 'no-repeat',
                            'repeat'    => 'repeat',
                            'repeat-x'  => 'repeat-x',
                            'repeat-y'  => 'repeat-y',
                        ),
                    ),
                ),
            ),
            'border' => array(
                'id'     => 'border',
                'type'   => 'group',
                'fields' => array(
                    'width' => array( 'id' => 'width', 'type' => 'spacing' ),
                    'style' => array(
                        'id'      => 'style',
                        'type'    => 'select',
                        'options' => array(
                            'solid'  => 'solid',
                            'dashed' => 'dashed',
                            'dotted' => 'dotted',
                            'double' => 'double',
                        ),
                    ),
                    'color' => array( 'id' => 'color', 'type' => 'color' ),
                ),
            ),
            'radius' => array(
                'id'     => 'radius',
                'type'   => 'group',
                'fields' => $this->get_stage_one_responsive_value_schema_map( 'spacing' ),
            ),
            'shadow' => array(
                'id'     => 'shadow',
                'type'   => 'group',
                'fields' => array(
                    'default' => array( 'id' => 'default', 'type' => 'text' ),
                    'hover'   => array( 'id' => 'hover', 'type' => 'text' ),
                ),
            ),
            'state' => array(
                'id'                   => 'state',
                'type'                 => 'group',
                'allowUnknownChildren' => true,
                'fields'               => array(
                    'hover' => array(
                        'id'     => 'hover',
                        'type'   => 'group',
                        'fields' => array(
                            'background_color' => array( 'id' => 'background_color', 'type' => 'color' ),
                            'border_color'     => array( 'id' => 'border_color', 'type' => 'color' ),
                            'title_color'      => array( 'id' => 'title_color', 'type' => 'color' ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @param string $leaf_type 叶子类型。
     * @return array<string,array<string,mixed>>
     */
    private function get_stage_one_responsive_value_schema_map( $leaf_type = 'spacing' ) {
        return array(
            'desktop' => array( 'id' => 'desktop', 'type' => $leaf_type ),
            'tablet'  => array( 'id' => 'tablet', 'type' => $leaf_type ),
            'mobile'  => array( 'id' => 'mobile', 'type' => $leaf_type ),
        );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_stage_one_responsive_direction_schema_map() {
        return array(
            'top'    => array(
                'id'     => 'top',
                'type'   => 'group',
                'fields' => $this->get_stage_one_responsive_value_schema_map( 'spacing' ),
            ),
            'right'  => array(
                'id'     => 'right',
                'type'   => 'group',
                'fields' => $this->get_stage_one_responsive_value_schema_map( 'spacing' ),
            ),
            'bottom' => array(
                'id'     => 'bottom',
                'type'   => 'group',
                'fields' => $this->get_stage_one_responsive_value_schema_map( 'spacing' ),
            ),
            'left'   => array(
                'id'     => 'left',
                'type'   => 'group',
                'fields' => $this->get_stage_one_responsive_value_schema_map( 'spacing' ),
            ),
        );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_stage_one_typography_schema_map() {
        return array(
            'color'       => array( 'id' => 'color', 'type' => 'color' ),
            'size'        => array(
                'id'     => 'size',
                'type'   => 'group',
                'fields' => $this->get_stage_one_responsive_value_schema_map( 'spacing' ),
            ),
            'weight'      => array(
                'id'      => 'weight',
                'type'    => 'select',
                'options' => array(
                    '300'    => '300',
                    '400'    => '400',
                    '500'    => '500',
                    '600'    => '600',
                    '700'    => '700',
                    '800'    => '800',
                    '900'    => '900',
                    'normal' => 'normal',
                    'bold'   => 'bold',
                ),
            ),
            'line_height' => array( 'id' => 'line_height', 'type' => 'text' ),
            'hover_color' => array( 'id' => 'hover_color', 'type' => 'color' ),
        );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_stage_one_visibility_schema_map() {
        return array(
            'status' => array(
                'id'      => 'status',
                'type'    => 'select',
                'options' => array(
                    ''       => '',
                    'show'   => 'show',
                    'hidden' => 'hidden',
                ),
            ),
            'desktop' => array(
                'id'      => 'desktop',
                'type'    => 'select',
                'options' => array(
                    ''  => '',
                    '1' => '1',
                    '0' => '0',
                ),
            ),
            'tablet'  => array(
                'id'      => 'tablet',
                'type'    => 'select',
                'options' => array(
                    ''  => '',
                    '1' => '1',
                    '0' => '0',
                ),
            ),
            'mobile'  => array(
                'id'      => 'mobile',
                'type'    => 'select',
                'options' => array(
                    ''  => '',
                    '1' => '1',
                    '0' => '0',
                ),
            ),
        );
    }

    /**
     * 把旧公共样式字段同步到协议保留字段，供后续统一样式层复用。
     *
     * 这里是增量迁移：旧字段继续保留，不影响现有渲染链路。
     *
     * @param array<string,mixed> $data 原始数据。
     * @return array<string,mixed>
     */
    private function migrate_legacy_common_style_fields_to_protocol( $data ) {
        if ( ! is_array( $data ) ) {
            return array();
        }

        $legacy_fields = array_keys( $this->get_legacy_common_style_schema_map() );
        $style_payload  = isset( $data['_ds_style'] ) && is_array( $data['_ds_style'] ) ? $data['_ds_style'] : array();
        $legacy_payload = isset( $style_payload['legacy'] ) && is_array( $style_payload['legacy'] ) ? $style_payload['legacy'] : array();
        $has_changes    = false;

        foreach ( $legacy_fields as $field_id ) {
            if ( ! array_key_exists( $field_id, $data ) || array_key_exists( $field_id, $legacy_payload ) ) {
                continue;
            }

            $legacy_payload[ $field_id ] = $data[ $field_id ];
            $has_changes = true;
        }

        if ( ! $has_changes ) {
            return $data;
        }

        $style_payload['legacy'] = $legacy_payload;
        $data['_ds_style']       = $style_payload;

        return $data;
    }

    /**
     * 编辑态下，把旧公共样式字段回填到统一高级样式协议，供单入口 UI 展示。
     *
     * 注意：这里只用于编辑器展示，不作为真实前台运行时真源。
     *
     * @param array<string,mixed> $data 模块数据。
     * @return array<string,mixed>
     */
    private function hydrate_legacy_common_style_fields_for_editor( $data ) {
        if ( ! is_array( $data ) ) {
            return array();
        }

        $style_payload  = isset( $data['_ds_style'] ) && is_array( $data['_ds_style'] ) ? $data['_ds_style'] : array();
        $legacy_payload = isset( $style_payload['legacy'] ) && is_array( $style_payload['legacy'] ) ? $style_payload['legacy'] : array();

        foreach ( $this->get_legacy_common_style_protocol_map() as $field_id => $mapping ) {
            $path = isset( $mapping['path'] ) ? (string) $mapping['path'] : '';
            if ( '' === $path || $this->has_nested_value( $style_payload, $path ) ) {
                continue;
            }

            $value = '';
            if ( array_key_exists( $field_id, $data ) ) {
                $value = $this->sanitize_protocol_compatibility_value(
                    $data[ $field_id ],
                    isset( $mapping['value_type'] ) ? (string) $mapping['value_type'] : 'text'
                );
            } elseif ( array_key_exists( $field_id, $legacy_payload ) ) {
                $value = $this->sanitize_protocol_compatibility_value(
                    $legacy_payload[ $field_id ],
                    isset( $mapping['value_type'] ) ? (string) $mapping['value_type'] : 'text'
                );
            }

            if ( '' === $value ) {
                continue;
            }

            $style_payload = $this->set_nested_value( $style_payload, $path, $value );
        }

        if ( ! empty( $style_payload ) ) {
            $data['_ds_style'] = $style_payload;
        }

        return $data;
    }

    /**
     * 保存态下，把统一高级样式里可兼容的基础样式回写到旧公共字段，
     * 并尽量从协议里折叠掉这些双写项，避免运行时出现双轨叠加。
     *
     * @param array<string,mixed> $data 模块数据。
     * @return array<string,mixed>
     */
    private function sync_protocol_compatibility_fields_to_legacy_common_fields( $module_id, $data ) {
        if ( ! is_array( $data ) ) {
            return array();
        }

        $style_payload  = isset( $data['_ds_style'] ) && is_array( $data['_ds_style'] ) ? $data['_ds_style'] : array();
        $legacy_payload = isset( $style_payload['legacy'] ) && is_array( $style_payload['legacy'] ) ? $style_payload['legacy'] : array();

        if ( empty( $style_payload ) ) {
            return $data;
        }

        foreach ( $this->get_legacy_common_style_protocol_map() as $field_id => $mapping ) {
            $path = isset( $mapping['path'] ) ? (string) $mapping['path'] : '';
            if ( '' === $path || ! $this->has_nested_value( $style_payload, $path ) ) {
                continue;
            }

            $value = $this->sanitize_protocol_compatibility_value(
                $this->get_nested_value( $style_payload, $path ),
                isset( $mapping['value_type'] ) ? (string) $mapping['value_type'] : 'text'
            );

            $mirror_to_legacy = $this->should_mirror_protocol_compatibility_field_to_legacy( $module_id, $field_id, $style_payload, $mapping );

            if ( $mirror_to_legacy ) {
                $data[ $field_id ]           = $value;
                $legacy_payload[ $field_id ] = $value;
            } else {
                unset( $data[ $field_id ], $legacy_payload[ $field_id ] );
            }

            if ( $this->should_strip_protocol_compatibility_path( $field_id, $mirror_to_legacy ) ) {
                $style_payload = $this->remove_nested_value( $style_payload, $path );
            }
        }

        $legacy_payload = $this->prune_empty_recursive( $legacy_payload );
        if ( ! empty( $legacy_payload ) ) {
            $style_payload['legacy'] = $legacy_payload;
        } else {
            unset( $style_payload['legacy'] );
        }

        $style_payload = $this->prune_empty_recursive( $style_payload );
        if ( ! empty( $style_payload ) ) {
            $data['_ds_style'] = $style_payload;
        } else {
            unset( $data['_ds_style'] );
        }

        return $data;
    }

    /**
     * @param string               $module_id    模块 ID。
     * @param string               $field_id     兼容字段 ID。
     * @param array<string,mixed>  $style_payload 样式 payload。
     * @param array<string,string> $mapping      映射配置。
     * @return bool
     */
    private function should_mirror_protocol_compatibility_field_to_legacy( $module_id, $field_id, $style_payload, $mapping ) {
        if ( 'module_bg_image' === $field_id && $this->style_payload_requires_background_image_wrapper( $style_payload ) ) {
            return false;
        }

        return $this->module_declares_legacy_common_style_field( $module_id, $field_id );
    }

    /**
     * @param string $field_id 兼容字段 ID。
     * @param bool   $mirror_to_legacy 是否镜像到旧字段。
     * @return bool
     */
    private function should_strip_protocol_compatibility_path( $field_id, $mirror_to_legacy ) {
        return $mirror_to_legacy && in_array(
            $field_id,
            array(
                'module_margin_top',
                'module_margin_bottom',
                'module_padding_top',
                'module_padding_bottom',
                'module_bg_color',
                'module_bg_image',
            ),
            true
        );
    }

    /**
     * 判断当前样式 payload 是否需要保留 wrapper 级背景图层。
     *
     * @param array<string,mixed> $style_payload 样式 payload。
     * @return bool
     */
    private function style_payload_requires_background_image_wrapper( $style_payload ) {
        if ( ! is_array( $style_payload ) ) {
            return false;
        }

        foreach ( array( 'background.size', 'background.position', 'background.repeat' ) as $path ) {
            if ( '' !== $this->sanitize_protocol_compatibility_value( $this->get_nested_value( $style_payload, $path ), 'text' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 兼容映射场景下统一清洗值。
     *
     * @param mixed  $value 原始值。
     * @param string $value_type 值类型。
     * @return string
     */
    private function sanitize_protocol_compatibility_value( $value, $value_type ) {
        $value_type = sanitize_key( (string) $value_type );

        switch ( $value_type ) {
            case 'spacing':
                return class_exists( '\Developer_Starter\Modules\Module_Manager' )
                    ? Module_Manager::sanitize_spacing_value( $value )
                    : sanitize_text_field( (string) $value );

            case 'paint':
                return $this->sanitize_css_color_value( $value );

            case 'image':
                return esc_url_raw( trim( (string) $value ) );

            default:
                return sanitize_text_field( (string) $value );
        }
    }

    /**
     * @param array<string,mixed> $payload payload。
     * @param string              $path    点路径。
     * @return bool
     */
    private function has_nested_value( $payload, $path ) {
        if ( ! is_array( $payload ) || '' === $path ) {
            return false;
        }

        $current = $payload;
        foreach ( explode( '.', $path ) as $segment ) {
            if ( ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
                return false;
            }
            $current = $current[ $segment ];
        }

        return true;
    }

    /**
     * @param array<string,mixed> $payload payload。
     * @param string              $path    点路径。
     * @return mixed
     */
    private function get_nested_value( $payload, $path ) {
        if ( ! is_array( $payload ) || '' === $path ) {
            return '';
        }

        $current = $payload;
        foreach ( explode( '.', $path ) as $segment ) {
            if ( ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
                return '';
            }
            $current = $current[ $segment ];
        }

        return $current;
    }

    /**
     * @param array<string,mixed> $payload payload。
     * @param string              $path    点路径。
     * @param string              $value   目标值。
     * @return array<string,mixed>
     */
    private function set_nested_value( $payload, $path, $value ) {
        if ( ! is_array( $payload ) ) {
            $payload = array();
        }
        if ( '' === $path ) {
            return $payload;
        }

        $segments = explode( '.', $path );
        $current  = &$payload;

        foreach ( $segments as $index => $segment ) {
            if ( $index === count( $segments ) - 1 ) {
                $current[ $segment ] = $value;
                break;
            }

            if ( ! isset( $current[ $segment ] ) || ! is_array( $current[ $segment ] ) ) {
                $current[ $segment ] = array();
            }

            $current = &$current[ $segment ];
        }

        return $payload;
    }

    /**
     * @param array<string,mixed> $payload payload。
     * @param string              $path    点路径。
     * @return array<string,mixed>
     */
    private function remove_nested_value( $payload, $path ) {
        if ( ! is_array( $payload ) || '' === $path ) {
            return is_array( $payload ) ? $payload : array();
        }

        $segments = explode( '.', $path );
        $payload  = $this->remove_nested_value_recursive( $payload, $segments );

        return is_array( $payload ) ? $payload : array();
    }

    /**
     * @param mixed             $payload  当前 payload。
     * @param array<int,string> $segments 路径片段。
     * @return mixed
     */
    private function remove_nested_value_recursive( $payload, $segments ) {
        if ( ! is_array( $payload ) || empty( $segments ) ) {
            return $payload;
        }

        $segment = array_shift( $segments );
        if ( null === $segment || ! array_key_exists( $segment, $payload ) ) {
            return $payload;
        }

        if ( empty( $segments ) ) {
            unset( $payload[ $segment ] );
        } else {
            $payload[ $segment ] = $this->remove_nested_value_recursive( $payload[ $segment ], $segments );
            if ( is_array( $payload[ $segment ] ) && empty( $payload[ $segment ] ) ) {
                unset( $payload[ $segment ] );
            }
        }

        return $payload;
    }

    /**
     * 递归清理空字符串/空数组，但保留 "0" 这类有效标量。
     *
     * @param mixed $value 任意值。
     * @return mixed
     */
    private function prune_empty_recursive( $value ) {
        if ( is_array( $value ) ) {
            $clean = array();
            foreach ( $value as $key => $item ) {
                $item = $this->prune_empty_recursive( $item );
                if ( is_array( $item ) && empty( $item ) ) {
                    continue;
                }
                if ( is_scalar( $item ) && '' === trim( (string) $item ) ) {
                    continue;
                }
                $clean[ $key ] = $item;
            }

            return $clean;
        }

        return $value;
    }

    /**
     * 判断模块自身是否声明了某个旧公共样式字段。
     *
     * @param string $module_id 模块 ID。
     * @param string $field_id  字段 ID。
     * @return bool
     */
    private function module_declares_legacy_common_style_field( $module_id, $field_id ) {
        $module_id = sanitize_key( (string) $module_id );
        $field_id  = (string) $field_id;
        if ( '' === $module_id || '' === $field_id ) {
            return false;
        }

        if ( ! class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            return false;
        }

        static $legacy_field_cache = array();

        if ( ! array_key_exists( $module_id, $legacy_field_cache ) ) {
            $module = Module_Manager::get_instance()->get_module( $module_id );
            $fields = ( $module && method_exists( $module, 'get_fields' ) ) ? $module->get_fields() : array();
            $legacy_field_cache[ $module_id ] = $this->get_declared_legacy_common_style_field_ids( $fields );
        }

        return in_array( $field_id, $legacy_field_cache[ $module_id ], true );
    }

    /**
     * 规范化字段类型别名。
     *
     * @param string $field_type 原始字段类型。
     * @return string
     */
    private function normalize_field_type( $field_type ) {
        $field_type = sanitize_key( (string) $field_type );
        $aliases = array(
            'radio'       => 'select',
            'dropdown'    => 'select',
            'media_url'   => 'media',
            'upload'      => 'file',
            'wysiwyg'     => 'editor',
            'richtext'    => 'editor',
            'bool'        => 'boolean',
            'true_false'  => 'boolean',
            'switcher'    => 'select',
            'css_length'  => 'spacing',
        );

        return isset( $aliases[ $field_type ] ) ? $aliases[ $field_type ] : $field_type;
    }

    /**
     * 根据公共字段命名规则收窄字段类型。
     *
     * @param string $key 字段键。
     * @param string $field_type 字段类型。
     * @return string
     */
    private function normalize_field_type_by_key( $key, $field_type ) {
        if ( in_array( $key, array( 'module_margin_top', 'module_margin_bottom', 'module_padding_top', 'module_padding_bottom' ), true ) ) {
            return 'spacing';
        }

        if ( preg_match( '/(^|_)(color|bg|background)(_color)?$/', $key ) && '' === $field_type ) {
            return 'color';
        }

        return $field_type;
    }

    /**
     * 仅在编辑器入场时修正 select 值，避免旧数据把“开启/关闭”等标签当成真实值。
     *
     * @param array<string,mixed>               $data 模块数据。
     * @param array<string,array<string,mixed>> $schema 模块 schema。
     * @return array<string,mixed>
     */
    private function normalize_select_values_for_editor( $data, $schema = array() ) {
        if ( ! is_array( $data ) ) {
            return array();
        }

        $schema = is_array( $schema ) ? $schema + $this->get_builtin_module_data_schema_map() : $this->get_builtin_module_data_schema_map();

        foreach ( $data as $key => $value ) {
            $key = is_string( $key ) ? $key : (string) $key;
            if ( '' === $key ) {
                continue;
            }

            $field_schema = ( isset( $schema[ $key ] ) && is_array( $schema[ $key ] ) ) ? $schema[ $key ] : array();
            $field_type = isset( $field_schema['type'] ) ? $this->normalize_field_type( (string) $field_schema['type'] ) : '';
            $field_type = $this->normalize_field_type_by_key( $key, $field_type );

            if ( is_array( $value ) ) {
                $child_schema = isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ? $field_schema['fields'] : array();
                if ( 'repeater' === $field_type ) {
                    foreach ( $value as $item_key => $item_value ) {
                        if ( is_array( $item_value ) ) {
                            $value[ $item_key ] = $this->normalize_select_values_for_editor( $item_value, $child_schema );
                        }
                    }
                    $data[ $key ] = $value;
                } elseif ( ! empty( $child_schema ) ) {
                    $data[ $key ] = $this->normalize_select_values_for_editor( $value, $child_schema );
                }

                continue;
            }

            if ( 'select' === $field_type && is_scalar( $value ) ) {
                $data[ $key ] = $this->sanitize_select_value( (string) $value, $field_schema );
            }
        }

        return $data;
    }

    /**
     * 规范化字段选项，保留键值关系。
     *
     * @param array<mixed,mixed>  $options 原始选项。
     * @param array<string,mixed> $field_schema 字段 schema。
     * @return array<string,string>
     */
    private function normalize_field_options( $options, $field_schema = array() ) {
        unset( $field_schema );

        $normalized = array();

        foreach ( $options as $option_key => $option_label ) {
            if ( ! is_scalar( $option_label ) ) {
                continue;
            }

            // Numeric-looking option keys like '0'/'1' become integers in PHP; keep keys as the saved values.
            if ( is_scalar( $option_key ) ) {
                $normalized[ (string) $option_key ] = wp_strip_all_tags( (string) $option_label );
            }
        }

        return $normalized;
    }

    /**
     * 清洗 select 字段，只允许已注册选项。
     *
     * @param string              $value 原始值。
     * @param array<string,mixed> $field_schema 字段 schema。
     * @return string
     */
    private function sanitize_select_value( $value, $field_schema ) {
        $value = (string) $value;
        $options = isset( $field_schema['options'] ) && is_array( $field_schema['options'] ) ? $field_schema['options'] : array();

        if ( empty( $options ) ) {
            return sanitize_text_field( $value );
        }

        $normalized_options = $this->normalize_field_options( $options, $field_schema );
        if ( array_key_exists( $value, $normalized_options ) ) {
            return $value;
        }

        $matched_key = array_search( $value, $normalized_options, true );
        if ( false !== $matched_key ) {
            return (string) $matched_key;
        }

        if ( isset( $field_schema['default'] ) && is_scalar( $field_schema['default'] ) ) {
            $default = (string) $field_schema['default'];
            if ( array_key_exists( $default, $normalized_options ) ) {
                return $default;
            }
        }

        return array_key_exists( '', $normalized_options ) ? '' : (string) key( $normalized_options );
    }

    /**
     * 清洗布尔类字段。
     *
     * @param string $value 原始值。
     * @return string
     */
    private function sanitize_boolean_value( $value ) {
        $value = strtolower( trim( (string) $value ) );
        return in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ? '1' : '0';
    }

    /**
     * 清洗数字字段。
     *
     * @param string              $value 原始值。
     * @param array<string,mixed> $field_schema 字段 schema。
     * @return string
     */
    private function sanitize_number_value( $value, $field_schema ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( ! is_numeric( $value ) ) {
            return isset( $field_schema['default'] ) && is_scalar( $field_schema['default'] ) ? sanitize_text_field( (string) $field_schema['default'] ) : '';
        }

        $number = (float) $value;
        if ( isset( $field_schema['min'] ) && is_numeric( $field_schema['min'] ) ) {
            $number = max( (float) $field_schema['min'], $number );
        }
        if ( isset( $field_schema['max'] ) && is_numeric( $field_schema['max'] ) ) {
            $number = min( (float) $field_schema['max'], $number );
        }

        return false === strpos( (string) $number, '.' ) ? (string) (int) $number : rtrim( rtrim( (string) $number, '0' ), '.' );
    }

    /**
     * 清洗 CSS 颜色/渐变值，阻断样式注入。
     *
     * @param string $value 原始值。
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
        } elseif ( preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ) {
            return $value;
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
     * 清洗 URL 字段，兼容 qiling:// 占位链接。
     *
     * @param string $value 原始值。
     * @return string
     */
    private function sanitize_url_value( $value ) {
        $placeholder_url = $this->sanitize_supported_placeholder_url( $value );
        if ( '' !== $placeholder_url ) {
            return $placeholder_url;
        }

        return esc_url_raw( $value, array( 'http', 'https', 'mailto', 'tel' ) );
    }

    /**
     * 支持 qiling:// 占位链接。
     *
     * @param string $value 原始值。
     * @return string
     */
    private function sanitize_supported_placeholder_url( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value || stripos( $value, 'qiling://' ) !== 0 ) {
            return '';
        }

        if ( preg_match( '/^qiling:\/\/page\/([a-z0-9_\-]+)$/i', $value, $matches ) ) {
            $page_key = sanitize_key( (string) $matches[1] );
            if ( '' !== $page_key ) {
                return 'qiling://page/' . $page_key;
            }
        }

        if ( preg_match( '/^qiling:\/\/system\/([a-z0-9_\-]+)$/i', $value, $matches ) ) {
            $target = sanitize_key( (string) $matches[1] );
            if ( '' !== $target ) {
                return 'qiling://system/' . $target;
            }
        }

        return '';
    }
}
