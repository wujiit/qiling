<?php
/**
 * Page Package Module Service
 *
 * 负责多页面数据包里的模块 schema 构建、白名单过滤与导入前清洗。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Module_Service {

    /**
     * 单页允许导入的最大模块数。
     *
     * @var int
     */
    private $max_modules_per_page = 60;

    /**
     * repeater 字段允许的最大子项数。
     *
     * @var int
     */
    private $max_repeater_items = 100;

    /**
     * 模块字段白名单缓存。
     *
     * @var array<string,array<string,mixed>>|null
     */
    private $module_schemas = null;

    /**
     * Builder 数据服务。
     *
     * @var Builder_Data_Service|null
     */
    private $builder_data_service = null;

    /**
     * @param array<string,mixed> $args 运行参数。
     */
    public function __construct( $args = array() ) {
        if ( isset( $args['max_modules_per_page'] ) ) {
            $this->max_modules_per_page = max( 1, absint( $args['max_modules_per_page'] ) );
        }

        if ( isset( $args['max_repeater_items'] ) ) {
            $this->max_repeater_items = max( 1, absint( $args['max_repeater_items'] ) );
        }
    }

    /**
     * 为模块数据做预检与白名单过滤。
     *
     * @param mixed  $modules    原始模块数组。
     * @param string $page_title 页面标题。
     * @return array<string,mixed>|\WP_Error
     */
    public function prepare_modules_for_import( $modules, $page_title ) {
        if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
            $modules = developer_starter_normalize_modules_meta_types( $modules );
        }

        if ( ! is_array( $modules ) || empty( $modules ) ) {
            return new \WP_Error(
                'empty_modules',
                sprintf(
                    /* translators: %s: page title */
                    __( '页面“%s”没有可导入的模块数据。', 'developer-starter' ),
                    $page_title
                )
            );
        }

        if ( count( $modules ) > $this->max_modules_per_page ) {
            return new \WP_Error(
                'too_many_modules',
                sprintf(
                    /* translators: 1: page title 2: module limit */
                    __( '页面“%1$s”包含的模块数量超过安全上限（最多 %2$d 个），当前数据包不会导入。', 'developer-starter' ),
                    $page_title,
                    $this->max_modules_per_page
                )
            );
        }

        $schemas           = $this->get_module_schemas();
        $normalized        = array();
        $warnings          = array();
        $style_warnings    = array();
        $security_warnings = array();
        $module_types      = array();

        foreach ( $modules as $module_index => $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                return new \WP_Error(
                    'invalid_module_structure',
                    sprintf(
                        /* translators: 1: page title 2: module index */
                        __( '页面“%1$s”的第 %2$d 个模块结构无效。', 'developer-starter' ),
                        $page_title,
                        $module_index + 1
                    )
                );
            }

            $type = sanitize_key( (string) $module['type'] );
            if ( ! isset( $schemas[ $type ] ) ) {
                return new \WP_Error(
                    'unknown_module_type',
                    sprintf(
                        /* translators: 1: page title 2: module type */
                        __( '页面“%1$s”包含未注册模块：%2$s。', 'developer-starter' ),
                        $page_title,
                        $type
                    )
                );
            }

            if ( isset( $module['data'] ) && is_array( $module['data'] ) ) {
                $module_data = $module['data'];
            } elseif ( isset( $module['settings'] ) && is_array( $module['settings'] ) ) {
                $module_data = $module['settings'];
            } else {
                $module_data = $module;
                unset( $module_data['type'] );
            }

            $from_schema_version = '';
            if ( isset( $module['schemaVersion'] ) && is_scalar( $module['schemaVersion'] ) ) {
                $from_schema_version = (string) $module['schemaVersion'];
            } elseif ( isset( $module['schema_version'] ) && is_scalar( $module['schema_version'] ) ) {
                $from_schema_version = (string) $module['schema_version'];
            }

            $module_data = $this->get_builder_data_service()->migrate_module_data(
                $type,
                is_array( $module_data ) ? $module_data : array(),
                $schemas[ $type ]['fields'],
                $from_schema_version
            );

            $module_warnings         = array();
            $module_style_warnings   = array();
            $module_security_warnings = array();
            $sanitized_data          = $this->sanitize_module_data_by_schema(
                is_array( $module_data ) ? $module_data : array(),
                $schemas[ $type ]['fields'],
                $module_warnings,
                $module_style_warnings,
                $module_security_warnings,
                $type
            );

            if ( ! empty( $module_warnings ) ) {
                foreach ( $module_warnings as $warning ) {
                    $warnings[] = sprintf(
                        /* translators: 1: module type 2: warning message */
                        __( '模块 %1$s：%2$s', 'developer-starter' ),
                        $schemas[ $type ]['name'],
                        $warning
                    );
                }
            }

            if ( ! empty( $module_style_warnings ) ) {
                foreach ( $module_style_warnings as $style_warning ) {
                    $style_warnings[] = sprintf(
                        /* translators: 1: module type 2: warning message */
                        __( '模块 %1$s：%2$s', 'developer-starter' ),
                        $schemas[ $type ]['name'],
                        $style_warning
                    );
                }
            }

            if ( ! empty( $module_security_warnings ) ) {
                foreach ( $module_security_warnings as $security_warning ) {
                    $security_warnings[] = sprintf(
                        /* translators: 1: module type 2: warning message */
                        __( '模块 %1$s：%2$s', 'developer-starter' ),
                        $schemas[ $type ]['name'],
                        $security_warning
                    );
                }
            }

            $normalized[]   = array(
                'type'          => $type,
                'data'          => $sanitized_data,
                'schemaVersion' => $this->get_builder_data_service()->get_module_data_schema_version(),
            );
            $module_types[] = $type;
        }

        return array(
            'modules'           => $normalized,
            'warnings'          => array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) ),
            'style_warnings'    => array_values( array_unique( array_filter( array_map( 'strval', $style_warnings ) ) ) ),
            'security_warnings' => array_values( array_unique( array_filter( array_map( 'strval', $security_warnings ) ) ) ),
            'module_types'      => array_values( array_unique( array_filter( array_map( 'sanitize_key', $module_types ) ) ) ),
        );
    }

    /**
     * 按模块字段定义清洗数据，避免未知字段扰乱样式。
     *
     * @param array<string,mixed>               $data              原始数据。
     * @param array<string,array<string,mixed>> $schema            字段白名单。
     * @param array<int,string>                 $warnings          通用警告列表。
     * @param array<int,string>                 $style_warnings    样式兼容预警。
     * @param array<int,string>                 $security_warnings 安全风控预警。
     * @param string                            $path              当前字段路径。
     * @return array<string,mixed>
     */
    private function sanitize_module_data_by_schema( $data, $schema, &$warnings, &$style_warnings, &$security_warnings, $path = '' ) {
        $sanitized = array();

        if ( ! is_array( $data ) ) {
            $warnings[] = __( '模块数据格式无效，已按空数据处理。', 'developer-starter' );
            return $sanitized;
        }

        $extra_schema = $this->get_builder_data_service()->get_builtin_module_data_schema_map();

        foreach ( $data as $key => $value ) {
            $key = is_string( $key ) ? $key : (string) $key;
            if ( isset( $schema[ $key ] ) ) {
                $field_schema = $schema[ $key ];
            } elseif ( isset( $extra_schema[ $key ] ) ) {
                $field_schema = $extra_schema[ $key ];
            } else {
                $warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 未在当前模块注册，已自动忽略。', 'developer-starter' ),
                    $path === '' ? $key : $path . '.' . $key
                );
                continue;
            }

            $field_type = isset( $field_schema['type'] ) ? sanitize_key( (string) $field_schema['type'] ) : 'text';

            if ( is_array( $value ) ) {
                if ( 'repeater' === $field_type ) {
                    if ( count( $value ) > $this->max_repeater_items ) {
                        $security_warnings[] = sprintf(
                            /* translators: 1: field path 2: item limit */
                            __( '字段 %1$s 的列表项数量超过安全上限，导入时仅保留前 %2$d 项。', 'developer-starter' ),
                            $path === '' ? $key : $path . '.' . $key,
                            $this->max_repeater_items
                        );
                        $value = array_slice( array_values( $value ), 0, $this->max_repeater_items );
                    }

                    $items = array();
                    foreach ( array_values( $value ) as $item_index => $item ) {
                        if ( ! is_array( $item ) ) {
                            $warnings[] = sprintf(
                                /* translators: %s: field path */
                                __( '字段 %s 的子项格式无效，已跳过。', 'developer-starter' ),
                                $path === '' ? $key . '[' . $item_index . ']' : $path . '.' . $key . '[' . $item_index . ']'
                            );
                            continue;
                        }

                        $child_warnings = array();
                        $items[]        = $this->sanitize_module_data_by_schema(
                            $item,
                            isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ? $field_schema['fields'] : array(),
                            $child_warnings,
                            $style_warnings,
                            $security_warnings,
                            $path === '' ? $key . '[' . $item_index . ']' : $path . '.' . $key . '[' . $item_index . ']'
                        );

                        if ( ! empty( $child_warnings ) ) {
                            foreach ( $child_warnings as $child_warning ) {
                                $warnings[] = $child_warning;
                            }
                        }
                    }

                    $sanitized[ $key ] = $items;
                    continue;
                }

                $field_path = $path === '' ? $key : $path . '.' . $key;
                $child_schema = isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ? $field_schema['fields'] : array();
                $allow_unknown_children = ! empty( $field_schema['allowUnknownChildren'] ) || ! empty( $field_schema['allow_unknown_children'] );

                if ( ! empty( $child_schema ) || $allow_unknown_children || in_array( $field_type, array( 'group', 'object' ), true ) ) {
                    if ( $allow_unknown_children ) {
                        $sanitized[ $key ] = $this->get_builder_data_service()->sanitize_module_data( $value, $child_schema );
                    } else {
                        $child_warnings   = array();
                        $sanitized[ $key ] = $this->sanitize_module_data_by_schema(
                            $value,
                            $child_schema,
                            $child_warnings,
                            $style_warnings,
                            $security_warnings,
                            $field_path
                        );

                        if ( ! empty( $child_warnings ) ) {
                            foreach ( $child_warnings as $child_warning ) {
                                $warnings[] = $child_warning;
                            }
                        }
                    }

                    continue;
                }

                $warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的结构无效，已忽略。', 'developer-starter' ),
                    $field_path
                );
                continue;
            }

            if ( 'repeater' === $field_type ) {
                $warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 不是列表结构，已忽略。', 'developer-starter' ),
                    $path === '' ? $key : $path . '.' . $key
                );
                continue;
            }

            $field_path = $path === '' ? $key : $path . '.' . $key;

            if ( 'select' === $field_type && isset( $field_schema['options'] ) && is_array( $field_schema['options'] ) ) {
                $value = $this->normalize_select_value( $value, $field_schema, $style_warnings, $field_path );
            } elseif ( 'spacing' === $field_type ) {
                $original_value = is_scalar( $value ) ? (string) $value : '';
                $value          = $this->sanitize_spacing_value( $value );
                if ( '' !== $original_value && $original_value !== $value ) {
                    $style_warnings[] = sprintf(
                        /* translators: %s: field path */
                        __( '字段 %s 的间距值已被自动规范化，导入后间距表现可能与原包略有差异。', 'developer-starter' ),
                        $field_path
                    );
                }
            } else {
                $value = $this->sanitize_module_scalar_by_key( $key, $value, $style_warnings, $security_warnings, $field_path, $field_type );
            }

            $sanitized[ $key ] = $value;
        }

        return $sanitized;
    }

    /**
     * 获取 Builder 数据服务。
     *
     * @return Builder_Data_Service
     */
    private function get_builder_data_service() {
        if ( ! $this->builder_data_service instanceof Builder_Data_Service ) {
            $this->builder_data_service = new Builder_Data_Service();
        }

        return $this->builder_data_service;
    }

    /**
     * 获取模块字段白名单。
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_module_schemas() {
        if ( null !== $this->module_schemas ) {
            return $this->module_schemas;
        }

        $this->module_schemas = array();

        if ( ! class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            return $this->module_schemas;
        }

        $module_manager = Module_Manager::get_instance();
        if ( method_exists( $module_manager, 'register_default_modules' ) ) {
            $module_manager->register_default_modules();
        }

        $modules = method_exists( $module_manager, 'get_all_modules' ) ? $module_manager->get_all_modules() : array();
        if ( ! is_array( $modules ) ) {
            return $this->module_schemas;
        }

        foreach ( $modules as $module_id => $module ) {
            if ( ! is_object( $module ) || ! method_exists( $module, 'get_fields' ) || ! method_exists( $module, 'get_name' ) ) {
                continue;
            }

            $this->module_schemas[ $module_id ] = array(
                'name'   => (string) $module->get_name(),
                'fields' => $this->get_builder_data_service()->build_module_data_schema_map( $module->get_fields() ),
            );
        }

        return $this->module_schemas;
    }

    /**
     * 把模块字段定义转换为字段白名单映射。
     *
     * @param array<int,array<string,mixed>> $fields 字段定义。
     * @return array<string,array<string,mixed>>
     */
    private function build_field_schema_map( $fields ) {
        $schema = array();
        if ( ! is_array( $fields ) ) {
            return $schema;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            $field_id   = (string) $field['id'];
            $field_type = isset( $field['type'] ) ? (string) $field['type'] : 'text';

            $schema_entry = array(
                'type' => $field_type,
            );

            if ( isset( $field['default'] ) ) {
                $schema_entry['default'] = $field['default'];
            }

            if ( 'select' === $field_type && isset( $field['options'] ) && is_array( $field['options'] ) ) {
                $schema_entry['options'] = array_map( 'strval', array_keys( $field['options'] ) );
            }

            if ( 'repeater' === $field_type && isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
                $schema_entry['fields'] = $this->build_field_schema_map( $field['fields'] );
            }

            $schema[ $field_id ] = $schema_entry;
        }

        return $schema;
    }

    /**
     * 规范化 select 字段，避免异常值扰乱样式。
     *
     * @param mixed               $value          原值。
     * @param array<string,mixed> $field_schema   字段定义。
     * @param array<int,string>   $style_warnings 样式兼容预警。
     * @param string              $field_path     当前字段路径。
     * @return string
     */
    private function normalize_select_value( $value, $field_schema, &$style_warnings, $field_path ) {
        $raw_value         = is_scalar( $value ) ? (string) $value : '';
        $value             = $raw_value;
        $options           = isset( $field_schema['options'] ) && is_array( $field_schema['options'] ) ? $field_schema['options'] : array();
        $normalized_options = array();
        $option_keys       = array_keys( $options );
        $is_assoc_options  = $option_keys !== range( 0, count( $options ) - 1 );

        if ( ! empty( $options ) ) {
            if ( $is_assoc_options ) {
                foreach ( $options as $option_key => $option_label ) {
                    $normalized_options[ (string) $option_key ] = is_scalar( $option_label ) ? (string) $option_label : '';
                }
            } else {
                foreach ( $options as $option_value ) {
                    if ( is_scalar( $option_value ) ) {
                        $normalized_options[] = (string) $option_value;
                    }
                }
            }
        }

        $bool_allowed_values = $is_assoc_options ? array_keys( $normalized_options ) : $normalized_options;
        if ( ! empty( $bool_allowed_values ) && ( in_array( 'yes', $bool_allowed_values, true ) || in_array( 'no', $bool_allowed_values, true ) ) ) {
            if ( '1' === $value ) {
                $value = 'yes';
            } elseif ( '0' === $value || '' === $value ) {
                $value = 'no';
            }
        }

        if ( $is_assoc_options ) {
            if ( array_key_exists( $value, $normalized_options ) ) {
                return $value;
            }

            $matched_key = array_search( $value, $normalized_options, true );
            if ( false !== $matched_key ) {
                return (string) $matched_key;
            }
        } elseif ( in_array( $value, $normalized_options, true ) ) {
            return $value;
        }

        if ( isset( $field_schema['default'] ) && is_scalar( $field_schema['default'] ) ) {
            $default_value = (string) $field_schema['default'];
            if ( $is_assoc_options ) {
                if ( array_key_exists( $default_value, $normalized_options ) ) {
                    if ( '' !== $raw_value ) {
                        $style_warnings[] = sprintf(
                            /* translators: %s: field path */
                            __( '字段 %s 的选项值不受当前主题支持，已回退到模块默认值。', 'developer-starter' ),
                            $field_path
                        );
                    }
                    return $default_value;
                }

                $matched_default_key = array_search( $default_value, $normalized_options, true );
                if ( false !== $matched_default_key ) {
                    if ( '' !== $raw_value ) {
                        $style_warnings[] = sprintf(
                            /* translators: %s: field path */
                            __( '字段 %s 的选项值不受当前主题支持，已回退到模块默认值。', 'developer-starter' ),
                            $field_path
                        );
                    }
                    return (string) $matched_default_key;
                }
            } elseif ( in_array( $default_value, $normalized_options, true ) ) {
                if ( '' !== $raw_value ) {
                    $style_warnings[] = sprintf(
                        /* translators: %s: field path */
                        __( '字段 %s 的选项值不受当前主题支持，已回退到模块默认值。', 'developer-starter' ),
                        $field_path
                    );
                }
                return $default_value;
            }
        }

        if ( ! empty( $normalized_options ) ) {
            if ( '' !== $raw_value ) {
                $style_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的选项值不受当前主题支持，已回退到第一个可用选项。', 'developer-starter' ),
                    $field_path
                );
            }

            if ( $is_assoc_options ) {
                reset( $normalized_options );
                $first_key = key( $normalized_options );
                if ( null !== $first_key ) {
                    return (string) $first_key;
                }
            }

            return (string) reset( $normalized_options );
        }

        return '';
    }

    /**
     * 按字段名复用现有模块保存时的清洗习惯。
     *
     * @param string            $key               字段名。
     * @param mixed             $value             原值。
     * @param array<int,string> $style_warnings    样式兼容预警。
     * @param array<int,string> $security_warnings 安全风控预警。
     * @param string            $field_path        当前字段路径。
     * @param string            $field_type        字段类型（来自模块 schema）。
     * @return string
     */
    private function sanitize_module_scalar_by_key( $key, $value, &$style_warnings, &$security_warnings, $field_path, $field_type = 'text' ) {
        if ( is_array( $value ) || is_object( $value ) ) {
            return '';
        }

        $original_value = (string) $value;
        $value          = $original_value;
        $field_type     = sanitize_key( (string) $field_type );

        $this->maybe_collect_scalar_security_warning( $key, $original_value, $security_warnings, $field_path );

        if (
            $key === 'module_margin_top'
            || $key === 'module_margin_bottom'
            || $key === 'module_padding_top'
            || $key === 'module_padding_bottom'
        ) {
            $sanitized_spacing = $this->sanitize_spacing_value( $value );
            if ( '' !== $original_value && $original_value !== $sanitized_spacing ) {
                $style_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的间距值已被自动规范化，导入后间距表现可能与原包略有差异。', 'developer-starter' ),
                    '' !== $field_path ? $field_path : $key
                );
            }
            return $sanitized_spacing;
        }

        if ( strpos( $key, 'content' ) !== false || strpos( $key, 'desc' ) !== false || strpos( $key, 'answer' ) !== false || strpos( $key, 'subtitle' ) !== false ) {
            return wp_kses_post( $value );
        }

        if ( strpos( $key, 'typing_text' ) !== false ) {
            return wp_kses_post( $value );
        }

        if ( $key === 'url' || $key === 'link' || preg_match( '/(_url|_link)$/', (string) $key ) ) {
            $placeholder_url = $this->sanitize_supported_package_placeholder_url( $value );
            if ( '' !== $placeholder_url ) {
                return $placeholder_url;
            }

            $sanitized_url = esc_url_raw( $value, array( 'http', 'https', 'mailto', 'tel' ) );
            if ( '' !== trim( $original_value ) && '' === $sanitized_url ) {
                $security_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 使用了不在白名单内的链接协议（仅支持 http/https/mailto/tel 与 qiling://page/*、qiling://system/*），导入时已自动清理。', 'developer-starter' ),
                    '' !== $field_path ? $field_path : $key
                );
                $style_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的链接已被清理，导入后相关按钮或跳转可能不可用。', 'developer-starter' ),
                    '' !== $field_path ? $field_path : $key
                );
            }
            return $sanitized_url;
        }

        if ( strpos( $key, 'show_' ) !== false || strpos( $key, '_show' ) !== false || strpos( $key, 'enable_' ) !== false || strpos( $key, '_enable' ) !== false ) {
            return sanitize_text_field( $value );
        }

        if ( preg_match( '/(_image|_logo|_file|_qrcode)$/', (string) $key ) || in_array( $key, array( 'image', 'logo', 'file', 'avatar' ), true ) ) {
            $sanitized_media = esc_url_raw( $value );
            if ( '' !== trim( $original_value ) && '' === $sanitized_media ) {
                $security_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 使用了不安全或无效的媒体地址，导入时已自动清理。', 'developer-starter' ),
                    '' !== $field_path ? $field_path : $key
                );
                $style_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的媒体地址无效，导入后相关图片或文件位可能不显示。', 'developer-starter' ),
                    '' !== $field_path ? $field_path : $key
                );
            }
            return $sanitized_media;
        }

        if ( $key === 'icon' || strpos( $key, '_icon' ) !== false || strpos( $key, 'icon_' ) !== false ) {
            if ( strpos( $value, '<svg' ) !== false ) {
                if ( function_exists( 'developer_starter_sanitize_svg' ) ) {
                    $sanitized_svg = developer_starter_sanitize_svg( $value );
                    if ( '' !== trim( $original_value ) && '' === trim( $sanitized_svg ) ) {
                        $style_warnings[] = sprintf(
                            /* translators: %s: field path */
                            __( '字段 %s 的 SVG 图标未通过安全过滤，导入后图标可能缺失。', 'developer-starter' ),
                            '' !== $field_path ? $field_path : $key
                        );
                    }
                    return $sanitized_svg;
                }

                $sanitized_svg = wp_kses_post( $value );
                if ( '' !== trim( $original_value ) && '' === trim( $sanitized_svg ) ) {
                    $style_warnings[] = sprintf(
                        /* translators: %s: field path */
                        __( '字段 %s 的 SVG 图标未通过安全过滤，导入后图标可能缺失。', 'developer-starter' ),
                        '' !== $field_path ? $field_path : $key
                    );
                }
                return $sanitized_svg;
            }

            if ( preg_match( '/<[^>]+>/', $value ) ) {
                return wp_kses(
                    $value,
                    array(
                        'i'    => array( 'class' => true, 'style' => true, 'aria-hidden' => true ),
                        'span' => array( 'class' => true, 'style' => true, 'aria-hidden' => true ),
                    )
                );
            }

            return sanitize_text_field( $value );
        }

        if ( $key === 'features' || $key === 'specs' || $key === 'rh_titles' || strpos( $key, 'titles' ) !== false || strpos( $key, '_bio' ) !== false || $key === 'bio' ) {
            return sanitize_textarea_field( $value );
        }

        if ( 'textarea' === $field_type ) {
            return sanitize_textarea_field( $value );
        }

        if ( 'editor' === $field_type ) {
            return wp_kses_post( $value );
        }

        $sanitized_value = sanitize_text_field( $value );
        $this->maybe_collect_text_length_style_warning( $key, $sanitized_value, $style_warnings, $field_path );

        return $sanitized_value;
    }

    /**
     * 允许页面数据包内部使用 qiling://page/* 和 qiling://system/* 占位链接。
     *
     * @param string $value 原始链接值。
     * @return string
     */
    private function sanitize_supported_package_placeholder_url( $value ) {
        if ( ! is_string( $value ) ) {
            return '';
        }

        $value = trim( $value );
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

    /**
     * 针对容易出现在卡片/标签/按钮中的短文本做长度预警。
     *
     * @param string            $key            字段名。
     * @param string            $value          已清洗的值。
     * @param array<int,string> $style_warnings 样式兼容预警。
     * @param string            $field_path     当前字段路径。
     * @return void
     */
    private function maybe_collect_text_length_style_warning( $key, $value, &$style_warnings, $field_path ) {
        $key = sanitize_key( (string) $key );
        if ( '' === $key || '' === trim( $value ) ) {
            return;
        }

        $is_short_text_field = (bool) preg_match(
            '/(^title$|_title$|title_|^subtitle$|_subtitle$|subtitle_|^label$|_label$|label_|^badge$|_badge$|badge_|btn_text|button_text|tab_title|card_title|item_title|^name$|_name$|name_)/',
            $key
        );
        if ( ! $is_short_text_field ) {
            return;
        }

        $text_length = function_exists( 'mb_strlen' )
            ? mb_strlen( wp_strip_all_tags( $value ), 'UTF-8' )
            : strlen( wp_strip_all_tags( $value ) );
        $threshold = ( false !== strpos( $key, 'btn' ) || false !== strpos( $key, 'button' ) || false !== strpos( $key, 'badge' ) || false !== strpos( $key, 'label' ) )
            ? 18
            : 36;

        if ( $text_length <= $threshold ) {
            return;
        }

        $style_warnings[] = sprintf(
            /* translators: %s: field path */
            __( '字段 %s 的文案较长，导入后可能出现换行、挤压卡片或按钮尺寸变化。', 'developer-starter' ),
            '' !== $field_path ? $field_path : $key
        );
    }

    /**
     * 对高风险 HTML、事件属性和超大字段做安全预警。
     *
     * @param string            $key               字段名。
     * @param string            $value             原始值。
     * @param array<int,string> $security_warnings 安全风控预警。
     * @param string            $field_path        当前字段路径。
     * @return void
     */
    private function maybe_collect_scalar_security_warning( $key, $value, &$security_warnings, $field_path ) {
        $key   = sanitize_key( (string) $key );
        $value = (string) $value;
        if ( '' === trim( $value ) ) {
            return;
        }

        if ( preg_match( '/<\s*(script|iframe|object|embed|form|style|link)\b/i', $value ) ) {
            $security_warnings[] = sprintf(
                /* translators: %s: field path */
                __( '字段 %s 包含高风险 HTML 标签，导入时会按主题安全规则自动过滤。', 'developer-starter' ),
                '' !== $field_path ? $field_path : $key
            );
        }

        if ( preg_match( '/\son[a-z]+\s*=/i', $value ) ) {
            $security_warnings[] = sprintf(
                /* translators: %s: field path */
                __( '字段 %s 包含事件属性写法，导入时会按主题安全规则自动过滤。', 'developer-starter' ),
                '' !== $field_path ? $field_path : $key
            );
        }

        if ( strlen( $value ) > 20000 ) {
            $security_warnings[] = sprintf(
                /* translators: %s: field path */
                __( '字段 %s 的内容体积较大，建议导入后关注页面性能与缓存命中情况。', 'developer-starter' ),
                '' !== $field_path ? $field_path : $key
            );
        }
    }

    /**
     * 间距字段白名单清洗。
     *
     * @param mixed $value 原始值。
     * @return string
     */
    private function sanitize_spacing_value( $value ) {
        if ( class_exists( '\Developer_Starter\Modules\Module_Manager' ) && method_exists( '\Developer_Starter\Modules\Module_Manager', 'sanitize_spacing_value' ) ) {
            return Module_Manager::sanitize_spacing_value( $value );
        }

        return sanitize_text_field( (string) $value );
    }
}
