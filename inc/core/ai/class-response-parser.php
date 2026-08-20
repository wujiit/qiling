<?php
/**
 * Response parser.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\AI;

use Developer_Starter\Core\AI_Decorator;
use Developer_Starter\Core\Builder_Data_Service;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Response_Parser {

    /**
     * @var object
     */
    private $decorator;

    /**
     * @var Builder_Data_Service|null
     */
    private $builder_data_service;

    /**
     * @param object $decorator 装修服务门面。
     */
    public function __construct( AI_Decorator $decorator ) {
        $this->decorator = $decorator;
    }

    /**
     * 从响应中提取页面规划。
     *
     * @param string            $content 响应文本。
     * @param array<int,string> $allowed_module_ids 允许模块。
     * @return array<string,mixed>|\WP_Error
     */
    public function extract_page_plan_from_response( $content, $allowed_module_ids ) {
        $json = $this->extract_json_string( $content );
        if ( '' === $json ) {
            return new \WP_Error( 'no_plan_json_found', __( 'AI 返回内容中未找到有效的页面规划 JSON。', 'developer-starter' ) );
        }

        $decoded = json_decode( $json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_plan_json', __( 'AI 返回的页面规划 JSON 格式无效，请重试。', 'developer-starter' ) );
        }

        return $this->sanitize_generation_plan( $decoded, $allowed_module_ids );
    }

    /**
     * 规范化页面规划输入。
     *
     * @param mixed             $plan 规划输入。
     * @param array<int,string> $allowed_module_ids 允许模块。
     * @return array<string,mixed>|\WP_Error
     */
    public function normalize_generation_plan_input( $plan, $allowed_module_ids ) {
        $plan = $this->decorator->normalize_json_array_input( $plan );
        if ( empty( $plan ) ) {
            return new \WP_Error( 'missing_plan', __( '缺少页面规划结果，请先执行页面规划。', 'developer-starter' ) );
        }

        return $this->sanitize_generation_plan( $plan, $allowed_module_ids );
    }

    /**
     * 清洗页面规划结构。
     *
     * @param array<string,mixed>|array<int,mixed> $decoded 规划数据。
     * @param array<int,string>                     $allowed_module_ids 允许模块。
     * @return array<string,mixed>|\WP_Error
     */
    public function sanitize_generation_plan( $decoded, $allowed_module_ids ) {
        $plan = array(
            'title'                => '',
            'page_template'        => $this->decorator->get_default_page_template(),
            'hide_page_header'     => false,
            'transparent_header'   => false,
            'enable_scroll_reveal' => false,
            'seo'                  => array(),
            'modules'              => array(),
        );

        $raw_modules = array();
        if ( $this->decorator->is_list_array( $decoded ) ) {
            $raw_modules = $decoded;
        } else {
            if ( isset( $decoded['title'] ) && is_scalar( $decoded['title'] ) ) {
                $plan['title'] = sanitize_text_field( (string) $decoded['title'] );
            }
            if ( isset( $decoded['page_template'] ) && is_scalar( $decoded['page_template'] ) ) {
                $plan['page_template'] = $this->decorator->normalize_page_template( (string) $decoded['page_template'] );
            }
            if ( isset( $decoded['hide_page_header'] ) ) {
                $plan['hide_page_header'] = $this->decorator->normalize_bool( $decoded['hide_page_header'], false );
            }
            if ( isset( $decoded['transparent_header'] ) ) {
                $plan['transparent_header'] = $this->decorator->normalize_bool( $decoded['transparent_header'], false );
            }
            if ( isset( $decoded['enable_scroll_reveal'] ) ) {
                $plan['enable_scroll_reveal'] = $this->decorator->normalize_bool( $decoded['enable_scroll_reveal'], false );
            }
            if ( isset( $decoded['seo'] ) && is_array( $decoded['seo'] ) ) {
                $plan['seo'] = $this->decorator->sanitize_ai_seo_payload( $decoded['seo'] );
            }

            if ( isset( $decoded['modules'] ) && is_array( $decoded['modules'] ) ) {
                $raw_modules = $decoded['modules'];
            } elseif ( isset( $decoded['page_plan']['modules'] ) && is_array( $decoded['page_plan']['modules'] ) ) {
                $raw_modules = $decoded['page_plan']['modules'];
            }
        }

        if ( empty( $raw_modules ) ) {
            return new \WP_Error( 'empty_plan_modules', __( '页面规划结果里没有模块信息，请重试。', 'developer-starter' ) );
        }

        $warnings = array();
        $seen_types = array();
        foreach ( $raw_modules as $index => $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                $warnings[] = sprintf(
                    /* translators: %d: module index */
                    __( '规划结果中的第 %d 个模块无效，已忽略。', 'developer-starter' ),
                    $index + 1
                );
                continue;
            }

            $type = sanitize_key( (string) $module['type'] );
            if ( '' === $type || ! in_array( $type, $allowed_module_ids, true ) ) {
                $warnings[] = sprintf(
                    /* translators: %s: module type */
                    __( '规划结果中的模块 %s 不在候选范围内，已忽略。', 'developer-starter' ),
                    $type
                );
                continue;
            }

            if ( in_array( $type, $seen_types, true ) ) {
                $warnings[] = sprintf(
                    /* translators: %s: module type */
                    __( '规划结果中的模块 %s 重复出现，已自动去重。', 'developer-starter' ),
                    $type
                );
                continue;
            }

            $goal = '';
            foreach ( array( 'goal', 'purpose', 'objective', 'description', 'desc', 'notes' ) as $goal_key ) {
                if ( isset( $module[ $goal_key ] ) && is_scalar( $module[ $goal_key ] ) ) {
                    $goal = sanitize_textarea_field( (string) $module[ $goal_key ] );
                    break;
                }
            }

            $plan['modules'][] = array(
                'type' => $type,
                'goal' => $goal,
            );
            $seen_types[] = $type;
        }

        if ( empty( $plan['modules'] ) ) {
            return new \WP_Error( 'empty_valid_plan_modules', __( '规划结果没有可用模块，请缩小候选范围后重试。', 'developer-starter' ) );
        }

        return array(
            'plan'     => $plan,
            'warnings' => array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) ),
        );
    }

    /**
     * 在规划中查找指定模块。
     *
     * @param array<string,mixed> $plan 规划结果。
     * @param string              $module_type 模块类型。
     * @return array<string,mixed>|null
     */
    public function find_plan_module_by_type( $plan, $module_type ) {
        $module_type = sanitize_key( (string) $module_type );
        if ( '' === $module_type || empty( $plan['modules'] ) || ! is_array( $plan['modules'] ) ) {
            return null;
        }

        foreach ( $plan['modules'] as $module ) {
            if ( ! is_array( $module ) ) {
                continue;
            }

            $type = isset( $module['type'] ) ? sanitize_key( (string) $module['type'] ) : '';
            if ( $type === $module_type ) {
                return $module;
            }
        }

        return null;
    }

    /**
     * 规范化已完成模块摘要输入。
     *
     * @param mixed               $completed_modules 输入。
     * @param array<string,mixed> $plan 规划结果。
     * @return array<int,array<string,string>>
     */
    public function normalize_completed_modules_input( $completed_modules, $plan ) {
        $completed_modules = $this->decorator->normalize_json_array_input( $completed_modules );
        if ( empty( $completed_modules ) || ! is_array( $completed_modules ) ) {
            return array();
        }

        $allowed_types = array();
        if ( isset( $plan['modules'] ) && is_array( $plan['modules'] ) ) {
            foreach ( $plan['modules'] as $module ) {
                if ( is_array( $module ) && ! empty( $module['type'] ) ) {
                    $allowed_types[] = sanitize_key( (string) $module['type'] );
                }
            }
        }

        $normalized = array();
        $seen_types = array();
        foreach ( $completed_modules as $item ) {
            if ( ! is_array( $item ) || empty( $item['type'] ) || empty( $item['summary'] ) ) {
                continue;
            }

            $type = sanitize_key( (string) $item['type'] );
            if ( '' === $type || ! in_array( $type, $allowed_types, true ) || in_array( $type, $seen_types, true ) ) {
                continue;
            }

            $summary = sanitize_textarea_field( (string) $item['summary'] );
            if ( '' === trim( $summary ) ) {
                continue;
            }

            $normalized[] = array(
                'type'    => $type,
                'summary' => $this->decorator->truncate_text_for_log( $summary, 220 ),
            );
            $seen_types[] = $type;
        }

        return $normalized;
    }

    /**
     * 从响应中提取单模块数据。
     *
     * @param string $content 响应文本。
     * @param string $module_type 目标模块类型。
     * @return array<string,mixed>|\WP_Error
     */
    public function extract_single_module_from_response( $content, $module_type ) {
        $json = $this->extract_json_string( $content );
        if ( '' === $json ) {
            return new \WP_Error( 'no_module_json_found', __( 'AI 返回内容中未找到有效的模块 JSON。', 'developer-starter' ) );
        }

        $decoded = json_decode( $json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_module_json', __( 'AI 返回的模块 JSON 格式无效，请重试。', 'developer-starter' ) );
        }

        $schemas = $this->decorator->get_module_schema_map( array( $module_type ) );
        if ( empty( $schemas[ $module_type ] ) ) {
            return new \WP_Error( 'missing_module_schema', __( '当前模块 schema 未找到，无法解析返回结果。', 'developer-starter' ) );
        }

        $module = $decoded;
        if ( isset( $decoded['module'] ) && is_array( $decoded['module'] ) ) {
            $module = $decoded['module'];
        } elseif ( isset( $decoded['modules'][0] ) && is_array( $decoded['modules'][0] ) ) {
            $module = $decoded['modules'][0];
        }

        $warnings = array();
        $returned_type = isset( $module['type'] ) ? sanitize_key( (string) $module['type'] ) : '';
        if ( '' !== $returned_type && $returned_type !== $module_type ) {
            $warnings[] = sprintf(
                /* translators: 1: returned type 2: expected type */
                __( 'AI 返回了模块 %1$s，已按当前目标模块 %2$s 处理。', 'developer-starter' ),
                $returned_type,
                $module_type
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

        $style_warnings = array();
        $security_warnings = array();
        $sanitized_data = $this->sanitize_module_data_by_schema(
            is_array( $module_data ) ? $module_data : array(),
            $schemas[ $module_type ]['field_map'],
            $warnings,
            $style_warnings,
            $security_warnings,
            $module_type
        );

        return array(
            'module' => array(
                'type'                  => $module_type,
                'data'                  => $sanitized_data,
                'schemaVersion'         => $this->get_builder_data_service()->get_module_data_schema_version(),
                'builderProtocolVersion' => $this->get_builder_data_service()->get_builder_protocol_version(),
            ),
            'warnings' => array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'strval',
                            array_merge( $warnings, $style_warnings, $security_warnings )
                        )
                    )
                )
            ),
        );
    }

    /**
     * Extract a localized single module and merge only whitelisted text fields.
     *
     * @param string                            $content AI response content.
     * @param string                            $module_type Target module type.
     * @param array<string,mixed>               $current_module_data Current module data.
     * @param array<string,array<string,mixed>> $text_only_schema Text-only field schema.
     * @return array<string,mixed>|\WP_Error
     */
    public function extract_localized_module_from_response( $content, $module_type, $current_module_data, $text_only_schema ) {
        $json = $this->extract_json_string( $content );
        if ( '' === $json ) {
            return new \WP_Error( 'no_module_localization_json_found', __( 'AI 返回内容中未找到有效的本地化模块 JSON。', 'developer-starter' ) );
        }

        $decoded = json_decode( $json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_module_localization_json', __( 'AI 返回的本地化 JSON 格式无效，请重试。', 'developer-starter' ) );
        }

        $schemas = $this->decorator->get_module_schema_map( array( $module_type ) );
        if ( empty( $schemas[ $module_type ] ) ) {
            return new \WP_Error( 'missing_module_schema', __( '当前模块 schema 未找到，无法解析返回结果。', 'developer-starter' ) );
        }

        $module = $decoded;
        if ( isset( $decoded['module'] ) && is_array( $decoded['module'] ) ) {
            $module = $decoded['module'];
        } elseif ( isset( $decoded['modules'][0] ) && is_array( $decoded['modules'][0] ) ) {
            $module = $decoded['modules'][0];
        }

        $warnings = array();
        $returned_type = isset( $module['type'] ) ? sanitize_key( (string) $module['type'] ) : '';
        if ( '' !== $returned_type && $returned_type !== $module_type ) {
            $warnings[] = sprintf(
                /* translators: 1: returned type 2: expected type */
                __( 'AI 返回了模块 %1$s，已按当前目标模块 %2$s 处理。', 'developer-starter' ),
                $returned_type,
                $module_type
            );
        }

        if ( isset( $module['data'] ) && is_array( $module['data'] ) ) {
            $localized_data = $module['data'];
        } elseif ( isset( $module['localized_data'] ) && is_array( $module['localized_data'] ) ) {
            $localized_data = $module['localized_data'];
        } elseif ( isset( $module['settings'] ) && is_array( $module['settings'] ) ) {
            $localized_data = $module['settings'];
        } else {
            $localized_data = $module;
            unset( $localized_data['type'] );
        }

        $style_warnings = array();
        $security_warnings = array();
        $current_module_data = is_array( $current_module_data ) ? $current_module_data : array();
        $merged_data = $this->merge_localized_text_fields(
            $current_module_data,
            is_array( $localized_data ) ? $localized_data : array(),
            is_array( $text_only_schema ) ? $text_only_schema : array(),
            $warnings,
            $style_warnings,
            $security_warnings,
            $module_type
        );

        return array(
            'module' => array(
                'type'                  => $module_type,
                'data'                  => $merged_data,
                'schemaVersion'         => $this->get_builder_data_service()->get_module_data_schema_version(),
                'builderProtocolVersion' => $this->get_builder_data_service()->get_builder_protocol_version(),
            ),
            'warnings' => array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'strval',
                            array_merge( $warnings, $style_warnings, $security_warnings )
                        )
                    )
                )
            ),
        );
    }

    /**
     * Extract a localized full-page package and merge only whitelisted text fields.
     *
     * @param string                          $content AI response content.
     * @param array<string,mixed>             $current_package Current page package.
     * @param array<int,array<string,mixed>>  $text_schema_map Text schema per module index.
     * @param array<string,mixed>             $localization Localization args.
     * @return array<string,mixed>|\WP_Error
     */
    public function extract_localized_page_package_from_response( $content, $current_package, $text_schema_map, $localization = array() ) {
        $json = $this->extract_json_string( $content );
        if ( '' === $json ) {
            return new \WP_Error( 'no_page_localization_json_found', __( 'AI 返回内容中未找到有效的整页本地化 JSON。', 'developer-starter' ) );
        }

        $decoded = json_decode( $json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_page_localization_json', __( 'AI 返回的整页本地化 JSON 格式无效，请重试。', 'developer-starter' ) );
        }

        $current_package = is_array( $current_package ) ? $current_package : array();
        $base_modules = isset( $current_package['modules'] ) && is_array( $current_package['modules'] ) ? array_values( $current_package['modules'] ) : array();
        if ( empty( $base_modules ) ) {
            return new \WP_Error( 'empty_page_localization_modules', __( '当前页面没有可合并的模块。', 'developer-starter' ) );
        }

        $raw_modules = array();
        if ( isset( $decoded['modules'] ) && is_array( $decoded['modules'] ) ) {
            $raw_modules = array_values( $decoded['modules'] );
        } elseif ( isset( $decoded['package']['modules'] ) && is_array( $decoded['package']['modules'] ) ) {
            $raw_modules = array_values( $decoded['package']['modules'] );
        }

        if ( empty( $raw_modules ) ) {
            return new \WP_Error( 'empty_page_localization_response_modules', __( 'AI 返回结果里没有整页模块数据。', 'developer-starter' ) );
        }

        $package = array(
            'title'                    => isset( $current_package['title'] ) ? sanitize_text_field( (string) $current_package['title'] ) : '',
            'page_template'            => isset( $current_package['page_template'] ) ? $this->decorator->normalize_page_template( $current_package['page_template'] ) : $this->decorator->get_default_page_template(),
            'hide_page_header'         => ! empty( $current_package['hide_page_header'] ),
            'transparent_header'       => ! empty( $current_package['transparent_header'] ),
            'enable_scroll_reveal'     => ! empty( $current_package['enable_scroll_reveal'] ),
            'seo'                      => isset( $current_package['seo'] ) && is_array( $current_package['seo'] ) ? $this->decorator->sanitize_ai_seo_payload( $current_package['seo'] ) : array(),
            'module_schema_version'    => $this->get_builder_data_service()->get_module_data_schema_version(),
            'builder_protocol_version' => $this->get_builder_data_service()->get_builder_protocol_version(),
            'modules'                  => array(),
        );

        if ( isset( $decoded['title'] ) && is_scalar( $decoded['title'] ) ) {
            $package['title'] = sanitize_text_field( (string) $decoded['title'] );
        } elseif ( isset( $decoded['package']['title'] ) && is_scalar( $decoded['package']['title'] ) ) {
            $package['title'] = sanitize_text_field( (string) $decoded['package']['title'] );
        }

        if ( isset( $decoded['seo'] ) && is_array( $decoded['seo'] ) ) {
            $package['seo'] = $this->decorator->sanitize_ai_seo_payload( $decoded['seo'] );
        } elseif ( isset( $decoded['package']['seo'] ) && is_array( $decoded['package']['seo'] ) ) {
            $package['seo'] = $this->decorator->sanitize_ai_seo_payload( $decoded['package']['seo'] );
        }

        $warnings = array();
        $style_warnings = array();
        $security_warnings = array();

        foreach ( $base_modules as $module_index => $base_module ) {
            $base_module = is_array( $base_module ) ? $base_module : array();
            $type = isset( $base_module['type'] ) ? sanitize_key( (string) $base_module['type'] ) : '';
            if ( '' === $type ) {
                continue;
            }

            $localized_module = isset( $raw_modules[ $module_index ] ) && is_array( $raw_modules[ $module_index ] )
                ? $raw_modules[ $module_index ]
                : array();

            $returned_type = isset( $localized_module['type'] ) ? sanitize_key( (string) $localized_module['type'] ) : '';
            if ( '' !== $returned_type && $returned_type !== $type ) {
                $warnings[] = sprintf(
                    /* translators: 1: returned type 2: expected type */
                    __( 'AI 返回的第 %1$d 个模块类型为 %2$s，已按原模块 %3$s 合并。', 'developer-starter' ),
                    $module_index + 1,
                    $returned_type,
                    $type
                );
            }

            if ( isset( $localized_module['data'] ) && is_array( $localized_module['data'] ) ) {
                $localized_data = $localized_module['data'];
            } elseif ( isset( $localized_module['localized_data'] ) && is_array( $localized_module['localized_data'] ) ) {
                $localized_data = $localized_module['localized_data'];
            } elseif ( isset( $localized_module['settings'] ) && is_array( $localized_module['settings'] ) ) {
                $localized_data = $localized_module['settings'];
            } else {
                $localized_data = $localized_module;
                unset( $localized_data['type'] );
            }

            $base_data = isset( $base_module['data'] ) && is_array( $base_module['data'] ) ? $base_module['data'] : array();
            $schema_item = isset( $text_schema_map[ $module_index ] ) && is_array( $text_schema_map[ $module_index ] ) ? $text_schema_map[ $module_index ] : array();
            $text_schema = isset( $schema_item['fields'] ) && is_array( $schema_item['fields'] ) ? $schema_item['fields'] : array();

            $merged_data = empty( $text_schema )
                ? $base_data
                : $this->merge_localized_text_fields(
                    $base_data,
                    is_array( $localized_data ) ? $localized_data : array(),
                    $text_schema,
                    $warnings,
                    $style_warnings,
                    $security_warnings,
                    $type
                );

            $package['modules'][] = array(
                'type'                   => $type,
                'data'                   => $merged_data,
                'schemaVersion'          => $this->get_builder_data_service()->get_module_data_schema_version(),
                'builderProtocolVersion' => $this->get_builder_data_service()->get_builder_protocol_version(),
            );
        }

        if ( count( $raw_modules ) !== count( $base_modules ) ) {
            $warnings[] = __( 'AI 返回的模块数量变化已被忽略，整页本地化会保留原页面结构。', 'developer-starter' );
        }

        $review = array();
        if ( isset( $decoded['localization_review'] ) && is_array( $decoded['localization_review'] ) ) {
            $review = $decoded['localization_review'];
        } elseif ( isset( $decoded['review'] ) && is_array( $decoded['review'] ) ) {
            $review = $decoded['review'];
        }
        $review = $this->normalize_localization_review( $review );
        $score = $this->build_localization_score( $review, $package, $localization );

        return array(
            'package' => $package,
            'warnings' => array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'strval',
                            array_merge( $warnings, $style_warnings, $security_warnings, isset( $score['warnings'] ) && is_array( $score['warnings'] ) ? $score['warnings'] : array() )
                        )
                    )
                )
            ),
            'review' => $review,
            'score'  => $score,
        );
    }

    /**
     * Extract localized post/article content.
     *
     * @param string              $content AI response content.
     * @param array<string,mixed> $source_payload Source content.
     * @param array<string,mixed> $localization Localization args.
     * @return array<string,mixed>|\WP_Error
     */
    public function extract_localized_content_from_response( $content, $source_payload, $localization = array() ) {
        $json = $this->extract_json_string( $content );
        if ( '' === $json ) {
            return new \WP_Error( 'no_content_localization_json_found', __( 'AI 返回内容中未找到有效的内容本地化 JSON。', 'developer-starter' ) );
        }

        $decoded = json_decode( $json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_content_localization_json', __( 'AI 返回的内容本地化 JSON 格式无效，请重试。', 'developer-starter' ) );
        }

        $source_payload = is_array( $source_payload ) ? $source_payload : array();
        $seo = array();
        if ( isset( $decoded['seo'] ) && is_array( $decoded['seo'] ) ) {
            $seo = $this->decorator->sanitize_ai_seo_payload( $decoded['seo'] );
        } elseif ( isset( $source_payload['seo'] ) && is_array( $source_payload['seo'] ) ) {
            $seo = $this->decorator->sanitize_ai_seo_payload( $source_payload['seo'] );
        }

        $localized = array(
            'title' => isset( $decoded['title'] ) && is_scalar( $decoded['title'] )
                ? sanitize_text_field( (string) $decoded['title'] )
                : ( isset( $source_payload['title'] ) ? sanitize_text_field( (string) $source_payload['title'] ) : '' ),
            'excerpt' => isset( $decoded['excerpt'] ) && is_scalar( $decoded['excerpt'] )
                ? sanitize_textarea_field( (string) $decoded['excerpt'] )
                : ( isset( $source_payload['excerpt'] ) ? sanitize_textarea_field( (string) $source_payload['excerpt'] ) : '' ),
            'content' => isset( $decoded['content'] ) && is_scalar( $decoded['content'] )
                ? wp_kses_post( (string) $decoded['content'] )
                : ( isset( $source_payload['content'] ) ? wp_kses_post( (string) $source_payload['content'] ) : '' ),
            'seo' => $seo,
        );

        $review = array();
        if ( isset( $decoded['localization_review'] ) && is_array( $decoded['localization_review'] ) ) {
            $review = $decoded['localization_review'];
        } elseif ( isset( $decoded['review'] ) && is_array( $decoded['review'] ) ) {
            $review = $decoded['review'];
        }
        $review = $this->normalize_localization_review( $review );
        $score = $this->build_localization_score(
            $review,
            array(
                'title' => $localized['title'],
                'seo' => $localized['seo'],
                'modules' => array(),
                'content' => $localized['content'],
            ),
            $localization
        );

        return array(
            'content' => $localized,
            'review'  => $review,
            'score'   => $score,
            'warnings'=> isset( $score['warnings'] ) && is_array( $score['warnings'] ) ? $score['warnings'] : array(),
        );
    }

    /**
     * 生成单模块摘要，供后续模块上下文使用。
     *
     * @param array<string,mixed> $module 已生成模块。
     * @param array<string,mixed> $current_module_plan 模块规划。
     * @param array<string,mixed> $current_module_schema 模块 schema。
     * @return string
     */
    public function summarize_generated_module_for_context( $module, $current_module_plan, $current_module_schema ) {
        $type = isset( $module['type'] ) ? sanitize_key( (string) $module['type'] ) : '';
        $name = isset( $current_module_schema['name'] ) ? sanitize_text_field( (string) $current_module_schema['name'] ) : $type;
        $goal = isset( $current_module_plan['goal'] ) ? sanitize_textarea_field( (string) $current_module_plan['goal'] ) : '';
        $data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array();

        $summary_parts = array();
        if ( '' !== $goal ) {
            $summary_parts[] = '作用：' . $goal;
        }

        $value_parts = $this->collect_module_summary_values( $data );
        if ( ! empty( $value_parts ) ) {
            $summary_parts[] = '内容：' . implode( '；', $value_parts );
        }

        $summary = $name;
        if ( '' !== $type ) {
            $summary .= '（' . $type . '）';
        }
        if ( ! empty( $summary_parts ) ) {
            $summary .= ' - ' . implode( '；', $summary_parts );
        }

        return $this->decorator->truncate_text_for_log( $summary, 220 );
    }

    /**
     * 从响应中提取页面包。
     *
     * @param string            $content 文本响应。
     * @param array<int,string> $allowed_module_ids 允许的模块列表。
     * @return array<string,mixed>|\WP_Error
     */
    public function extract_package_from_response( $content, $allowed_module_ids ) {
        $json = $this->extract_json_string( $content );
        if ( '' === $json ) {
            return new \WP_Error( 'no_json_found', __( 'AI 返回内容中未找到有效 JSON。', 'developer-starter' ) );
        }

        $decoded = json_decode( $json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_json_response', __( 'AI 返回的 JSON 格式无效，请重试。', 'developer-starter' ) );
        }

        return $this->sanitize_generated_package( $decoded, $allowed_module_ids );
    }

    /**
     * 清洗生成的页面包。
     *
     * @param array<string,mixed>|array<int,mixed> $decoded 返回结构。
     * @param array<int,string>                     $allowed_module_ids 允许模块。
     * @return array<string,mixed>|\WP_Error
     */
    public function sanitize_generated_package( $decoded, $allowed_module_ids ) {
        $package = array(
            'title'                => '',
            'page_template'        => $this->decorator->get_default_page_template(),
            'hide_page_header'     => false,
            'transparent_header'   => false,
            'enable_scroll_reveal' => false,
            'seo'                  => array(),
            'module_schema_version' => $this->get_builder_data_service()->get_module_data_schema_version(),
            'builder_protocol_version' => $this->get_builder_data_service()->get_builder_protocol_version(),
            'modules'              => array(),
        );

        $raw_modules = array();
        if ( $this->decorator->is_list_array( $decoded ) ) {
            $raw_modules = $decoded;
        } else {
            if ( isset( $decoded['title'] ) && is_scalar( $decoded['title'] ) ) {
                $package['title'] = sanitize_text_field( (string) $decoded['title'] );
            }
            if ( isset( $decoded['page_template'] ) && is_scalar( $decoded['page_template'] ) ) {
                $package['page_template'] = $this->decorator->normalize_page_template( (string) $decoded['page_template'] );
            }
            if ( isset( $decoded['hide_page_header'] ) ) {
                $package['hide_page_header'] = $this->decorator->normalize_bool( $decoded['hide_page_header'], false );
            }
            if ( isset( $decoded['transparent_header'] ) ) {
                $package['transparent_header'] = $this->decorator->normalize_bool( $decoded['transparent_header'], false );
            }
            if ( isset( $decoded['enable_scroll_reveal'] ) ) {
                $package['enable_scroll_reveal'] = $this->decorator->normalize_bool( $decoded['enable_scroll_reveal'], false );
            }
            if ( isset( $decoded['seo'] ) && is_array( $decoded['seo'] ) ) {
                $package['seo'] = $this->decorator->sanitize_ai_seo_payload( $decoded['seo'] );
            }
            if ( isset( $decoded['module_schema_version'] ) && is_scalar( $decoded['module_schema_version'] ) ) {
                $package['module_schema_version'] = sanitize_text_field( (string) $decoded['module_schema_version'] );
            }
            if ( isset( $decoded['builder_protocol_version'] ) && is_scalar( $decoded['builder_protocol_version'] ) ) {
                $package['builder_protocol_version'] = sanitize_text_field( (string) $decoded['builder_protocol_version'] );
            }

            if ( isset( $decoded['modules'] ) && is_array( $decoded['modules'] ) ) {
                $raw_modules = $decoded['modules'];
            } elseif ( isset( $decoded['page_modules'] ) && is_array( $decoded['page_modules'] ) ) {
                $raw_modules = $decoded['page_modules'];
            }
        }

        if ( empty( $raw_modules ) ) {
            return new \WP_Error( 'empty_package_modules', __( 'AI 返回结果里没有模块数据。', 'developer-starter' ) );
        }

        $schemas = $this->decorator->get_module_schema_map( $allowed_module_ids );
        $warnings = array();
        $style_warnings = array();
        $security_warnings = array();

        foreach ( $raw_modules as $module_index => $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                $warnings[] = sprintf(
                    /* translators: %d: module index */
                    __( '第 %d 个模块结构无效，已忽略。', 'developer-starter' ),
                    $module_index + 1
                );
                continue;
            }

            $type = sanitize_key( (string) $module['type'] );
            if ( '' === $type || ! isset( $schemas[ $type ] ) ) {
                $warnings[] = sprintf(
                    /* translators: %s: module type */
                    __( '模块 %s 不在当前允许列表中，已忽略。', 'developer-starter' ),
                    $type
                );
                continue;
            }

            $module_data = array();
            if ( isset( $module['data'] ) && is_array( $module['data'] ) ) {
                $module_data = $module['data'];
            } elseif ( isset( $module['settings'] ) && is_array( $module['settings'] ) ) {
                $module_data = $module['settings'];
            } else {
                $module_data = $module;
                unset( $module_data['type'] );
            }

            $sanitized_data = $this->sanitize_module_data_by_schema(
                $module_data,
                $schemas[ $type ]['field_map'],
                $warnings,
                $style_warnings,
                $security_warnings,
                $type
            );

            $package['modules'][] = array(
                'type'                  => $type,
                'data'                  => $sanitized_data,
                'schemaVersion'         => $this->get_builder_data_service()->get_module_data_schema_version(),
                'builderProtocolVersion' => $this->get_builder_data_service()->get_builder_protocol_version(),
            );
        }

        if ( empty( $package['modules'] ) ) {
            return new \WP_Error( 'no_valid_modules', __( 'AI 返回的模块都未通过校验，请重试或缩小候选模块范围。', 'developer-starter' ) );
        }

        return array(
            'package' => $package,
            'warnings' => array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'strval',
                            array_merge( $warnings, $style_warnings, $security_warnings )
                        )
                    )
                )
            ),
        );
    }

    /**
     * 按 schema 清洗模块数据。
     *
     * @param array<string,mixed>               $data 原始数据。
     * @param array<string,array<string,mixed>> $schema schema。
     * @param array<int,string>                 $warnings 警告。
     * @param array<int,string>                 $style_warnings 样式预警。
     * @param array<int,string>                 $security_warnings 安全预警。
     * @param string                            $path 路径。
     * @return array<string,mixed>
     */
    public function sanitize_module_data_by_schema( $data, $schema, &$warnings, &$style_warnings, &$security_warnings, $path = '' ) {
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
                    __( '字段 %s 未在当前模块 schema 中注册，已自动忽略。', 'developer-starter' ),
                    '' === $path ? $key : $path . '.' . $key
                );
                continue;
            }

            $field_type = isset( $field_schema['type'] ) ? sanitize_key( (string) $field_schema['type'] ) : 'text';
            if ( is_array( $value ) ) {
                if ( 'repeater' === $field_type ) {
                    $items = array();
                    $value = array_values( $value );
                    if ( count( $value ) > AI_Decorator::MAX_REPEATER_ITEMS ) {
                        $security_warnings[] = sprintf(
                            /* translators: 1: field path 2: item limit */
                            __( '字段 %1$s 的重复项过多，已仅保留前 %2$d 项。', 'developer-starter' ),
                            '' === $path ? $key : $path . '.' . $key,
                            AI_Decorator::MAX_REPEATER_ITEMS
                        );
                        $value = array_slice( $value, 0, AI_Decorator::MAX_REPEATER_ITEMS );
                    }

                    foreach ( $value as $item_index => $item ) {
                        if ( ! is_array( $item ) ) {
                            continue;
                        }

                        $items[] = $this->sanitize_module_data_by_schema(
                            $item,
                            isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ? $field_schema['fields'] : array(),
                            $warnings,
                            $style_warnings,
                            $security_warnings,
                            '' === $path ? $key . '[' . $item_index . ']' : $path . '.' . $key . '[' . $item_index . ']'
                        );
                    }

                    $sanitized[ $key ] = $items;
                    continue;
                }

                $field_path = '' === $path ? $key : $path . '.' . $key;
                $child_schema = isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ? $field_schema['fields'] : array();
                $allow_unknown_children = ! empty( $field_schema['allowUnknownChildren'] ) || ! empty( $field_schema['allow_unknown_children'] );

                if ( ! empty( $child_schema ) || $allow_unknown_children || in_array( $field_type, array( 'group', 'object' ), true ) ) {
                    if ( $allow_unknown_children ) {
                        $sanitized[ $key ] = $this->get_builder_data_service()->sanitize_module_data( $value, $child_schema );
                    } else {
                        $sanitized[ $key ] = $this->sanitize_module_data_by_schema(
                            $value,
                            $child_schema,
                            $warnings,
                            $style_warnings,
                            $security_warnings,
                            $field_path
                        );
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
                    '' === $path ? $key : $path . '.' . $key
                );
                continue;
            }

            $field_path = '' === $path ? $key : $path . '.' . $key;
            if ( 'select' === $field_type && isset( $field_schema['options'] ) && is_array( $field_schema['options'] ) ) {
                $sanitized[ $key ] = $this->normalize_select_value( $value, $field_schema, $style_warnings, $field_path );
                continue;
            }

            if ( 'spacing' === $field_type ) {
                $original_value = is_scalar( $value ) ? (string) $value : '';
                $sanitized_spacing = $this->decorator->sanitize_spacing_value( $value );
                if ( '' !== $original_value && $original_value !== $sanitized_spacing ) {
                    $style_warnings[] = sprintf(
                        /* translators: %s: field path */
                        __( '字段 %s 的间距值已被自动规范化。', 'developer-starter' ),
                        $field_path
                    );
                }
                $sanitized[ $key ] = $sanitized_spacing;
                continue;
            }

            $sanitized[ $key ] = $this->sanitize_module_scalar_by_key(
                $key,
                $value,
                $style_warnings,
                $security_warnings,
                $field_path,
                $field_type
            );
        }

        return $sanitized;
    }

    /**
     * 按字段名清洗标量。
     *
     * @param string            $key 字段名。
     * @param mixed             $value 值。
     * @param array<int,string> $style_warnings 样式预警。
     * @param array<int,string> $security_warnings 安全预警。
     * @param string            $field_path 字段路径。
     * @param string            $field_type 字段类型。
     * @return string
     */
    public function sanitize_module_scalar_by_key( $key, $value, &$style_warnings, &$security_warnings, $field_path, $field_type = 'text' ) {
        if ( is_array( $value ) || is_object( $value ) ) {
            return '';
        }

        $original_value = (string) $value;
        $value = $original_value;
        $field_type = sanitize_key( (string) $field_type );

        $this->decorator->maybe_collect_scalar_security_warning( $key, $original_value, $security_warnings, $field_path );

        if (
            $key === 'module_margin_top'
            || $key === 'module_margin_bottom'
            || $key === 'module_padding_top'
            || $key === 'module_padding_bottom'
        ) {
            $sanitized_spacing = $this->decorator->sanitize_spacing_value( $value );
            if ( '' !== $original_value && $original_value !== $sanitized_spacing ) {
                $style_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的间距值已被自动规范化。', 'developer-starter' ),
                    $field_path
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
            $placeholder_url = $this->decorator->sanitize_supported_placeholder_url( $value );
            if ( '' !== $placeholder_url ) {
                return $placeholder_url;
            }

            $sanitized_url = esc_url_raw( $value, array( 'http', 'https', 'mailto', 'tel' ) );
            if ( '' !== trim( $original_value ) && '' === $sanitized_url ) {
                $security_warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的链接协议不受支持，已自动清理。', 'developer-starter' ),
                    $field_path
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
                    __( '字段 %s 的媒体地址无效，已自动清理。', 'developer-starter' ),
                    $field_path
                );
            }
            return $sanitized_media;
        }

        if ( $key === 'icon' || strpos( $key, '_icon' ) !== false || strpos( $key, 'icon_' ) !== false ) {
            if ( strpos( $value, '<svg' ) !== false ) {
                if ( function_exists( 'developer_starter_sanitize_svg' ) ) {
                    return developer_starter_sanitize_svg( $value );
                }
                return wp_kses_post( $value );
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

        $sanitized_value = sanitize_text_field( $value );
        $this->decorator->maybe_collect_text_length_style_warning( $key, $sanitized_value, $style_warnings, $field_path );

        return $sanitized_value;
    }

    /**
     * Merge localized values into current data using only the text-only whitelist.
     *
     * @param array<string,mixed>               $base_data Current module data.
     * @param array<string,mixed>               $localized_data AI localized data.
     * @param array<string,array<string,mixed>> $text_schema Text-only schema.
     * @param array<int,string>                 $warnings Warnings.
     * @param array<int,string>                 $style_warnings Style warnings.
     * @param array<int,string>                 $security_warnings Security warnings.
     * @param string                            $path Field path.
     * @return array<string,mixed>
     */
    private function merge_localized_text_fields( $base_data, $localized_data, $text_schema, &$warnings, &$style_warnings, &$security_warnings, $path = '' ) {
        $base_data = is_array( $base_data ) ? $base_data : array();
        $localized_data = is_array( $localized_data ) ? $localized_data : array();
        $text_schema = is_array( $text_schema ) ? $text_schema : array();
        $merged = $base_data;

        foreach ( $localized_data as $raw_key => $raw_value ) {
            $raw_key = is_string( $raw_key ) ? $raw_key : (string) $raw_key;
            if ( '' !== $raw_key && ! isset( $text_schema[ $raw_key ] ) ) {
                $warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 不属于本地化文案白名单，已忽略。', 'developer-starter' ),
                    '' === $path ? $raw_key : $path . '.' . $raw_key
                );
            }
        }

        foreach ( $text_schema as $key => $field_schema ) {
            if ( ! is_array( $field_schema ) || ! array_key_exists( $key, $localized_data ) ) {
                continue;
            }

            $field_type = isset( $field_schema['type'] ) ? sanitize_key( (string) $field_schema['type'] ) : 'text';
            $field_path = '' === $path ? $key : $path . '.' . $key;
            $raw_value = $localized_data[ $key ];

            if ( 'repeater' === $field_type ) {
                $base_items = isset( $base_data[ $key ] ) && is_array( $base_data[ $key ] ) ? array_values( $base_data[ $key ] ) : array();
                $raw_items = is_array( $raw_value ) ? array_values( $raw_value ) : array();
                $child_schema = isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ? $field_schema['fields'] : array();
                $items = array();

                foreach ( $base_items as $item_index => $base_item ) {
                    $base_item = is_array( $base_item ) ? $base_item : array();
                    if ( isset( $raw_items[ $item_index ] ) && is_array( $raw_items[ $item_index ] ) ) {
                        $items[] = $this->merge_localized_text_fields(
                            $base_item,
                            $raw_items[ $item_index ],
                            $child_schema,
                            $warnings,
                            $style_warnings,
                            $security_warnings,
                            $field_path . '[' . $item_index . ']'
                        );
                    } else {
                        $items[] = $base_item;
                    }
                }

                if ( count( $raw_items ) !== count( $base_items ) ) {
                    $warnings[] = sprintf(
                        /* translators: %s: field path */
                        __( '字段 %s 的列表数量变化已被忽略，本地化会保留原列表结构。', 'developer-starter' ),
                        $field_path
                    );
                }

                $merged[ $key ] = $items;
                continue;
            }

            if ( isset( $field_schema['fields'] ) && is_array( $field_schema['fields'] ) ) {
                if ( ! is_array( $raw_value ) ) {
                    $warnings[] = sprintf(
                        /* translators: %s: field path */
                        __( '字段 %s 的本地化结构无效，已忽略。', 'developer-starter' ),
                        $field_path
                    );
                    continue;
                }

                $merged[ $key ] = $this->merge_localized_text_fields(
                    isset( $base_data[ $key ] ) && is_array( $base_data[ $key ] ) ? $base_data[ $key ] : array(),
                    $raw_value,
                    $field_schema['fields'],
                    $warnings,
                    $style_warnings,
                    $security_warnings,
                    $field_path
                );
                continue;
            }

            if ( is_array( $raw_value ) || is_object( $raw_value ) ) {
                $warnings[] = sprintf(
                    /* translators: %s: field path */
                    __( '字段 %s 的本地化内容不是文本，已忽略。', 'developer-starter' ),
                    $field_path
                );
                continue;
            }

            $merged[ $key ] = $this->sanitize_module_scalar_by_key(
                $key,
                $raw_value,
                $style_warnings,
                $security_warnings,
                $field_path,
                $field_type
            );
        }

        return $merged;
    }

    /**
     * Normalize AI self-review for localization scoring.
     *
     * @param mixed $review Raw review.
     * @return array<string,mixed>
     */
    private function normalize_localization_review( $review ) {
        $review = is_array( $review ) ? $review : array();
        $allowed_statuses = array( 'good', 'risk', 'bad' );

        $normalized = array(
            'literalness' => 'risk',
            'cta_market_fit' => 'risk',
            'seo_title_length' => 'risk',
            'warnings' => array(),
            'recommendations' => array(),
        );

        foreach ( array( 'literalness', 'cta_market_fit', 'seo_title_length' ) as $key ) {
            if ( isset( $review[ $key ] ) && is_scalar( $review[ $key ] ) ) {
                $status = sanitize_key( (string) $review[ $key ] );
                if ( in_array( $status, $allowed_statuses, true ) ) {
                    $normalized[ $key ] = $status;
                }
            }
        }

        foreach ( array( 'warnings', 'recommendations' ) as $list_key ) {
            if ( empty( $review[ $list_key ] ) || ! is_array( $review[ $list_key ] ) ) {
                continue;
            }

            foreach ( array_slice( $review[ $list_key ], 0, 5 ) as $item ) {
                if ( ! is_scalar( $item ) ) {
                    continue;
                }

                $text = $this->decorator->truncate_text_for_log( sanitize_text_field( (string) $item ), 140 );
                if ( '' !== $text ) {
                    $normalized[ $list_key ][] = $text;
                }
            }
        }

        return $normalized;
    }

    /**
     * Build a lightweight localization score from AI review and deterministic checks.
     *
     * @param array<string,mixed> $review AI review.
     * @param array<string,mixed> $package Localized package.
     * @param array<string,mixed> $localization Localization args.
     * @return array<string,mixed>
     */
    private function build_localization_score( $review, $package, $localization ) {
        $score = 100;
        $warnings = array();
        $checks = array();

        $status_weights = array(
            'good' => 0,
            'risk' => 12,
            'bad' => 25,
        );

        foreach ( array( 'literalness', 'cta_market_fit', 'seo_title_length' ) as $key ) {
            $status = isset( $review[ $key ] ) && is_scalar( $review[ $key ] ) ? sanitize_key( (string) $review[ $key ] ) : 'risk';
            if ( ! isset( $status_weights[ $status ] ) ) {
                $status = 'risk';
            }
            $checks[ $key ] = $status;
            $score -= $status_weights[ $status ];
        }

        $seo_title = '';
        if ( isset( $package['seo']['title'] ) && is_scalar( $package['seo']['title'] ) ) {
            $seo_title = wp_strip_all_tags( (string) $package['seo']['title'] );
        }
        $seo_title_length = function_exists( 'mb_strlen' ) ? mb_strlen( $seo_title, 'UTF-8' ) : strlen( $seo_title );
        if ( $seo_title_length > 68 ) {
            $score -= 10;
            $checks['seo_title_length'] = 'bad';
            $warnings[] = __( 'SEO 标题偏长，建议缩短后再发布。', 'developer-starter' );
        } elseif ( $seo_title_length > 60 ) {
            $score -= 5;
            if ( 'good' === $checks['seo_title_length'] ) {
                $checks['seo_title_length'] = 'risk';
            }
            $warnings[] = __( 'SEO 标题接近长度上限，建议检查搜索结果展示。', 'developer-starter' );
        }

        $encoded_package = wp_json_encode( $package, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        $plain_text = is_string( $encoded_package ) ? wp_strip_all_tags( $encoded_package ) : '';
        $forbidden_words = isset( $localization['forbidden_words'] ) && is_array( $localization['forbidden_words'] ) ? $localization['forbidden_words'] : array();
        foreach ( $forbidden_words as $word ) {
            $word = is_scalar( $word ) ? trim( (string) $word ) : '';
            if ( '' !== $word && false !== stripos( $plain_text, $word ) ) {
                $score -= 15;
                $checks['forbidden_words'] = 'bad';
                $warnings[] = sprintf(
                    /* translators: %s: forbidden word */
                    __( '本地化结果仍包含禁用词：%s', 'developer-starter' ),
                    $word
                );
            }
        }

        $score = max( 0, min( 100, $score ) );

        return array(
            'score' => $score,
            'grade' => $score >= 85 ? 'good' : ( $score >= 65 ? 'risk' : 'bad' ),
            'checks' => $checks,
            'warnings' => array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) ),
            'recommendations' => isset( $review['recommendations'] ) && is_array( $review['recommendations'] ) ? $review['recommendations'] : array(),
        );
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
     * 从文本里提取 JSON。
     *
     * @param string $content 文本。
     * @return string
     */
    public function extract_json_string( $content ) {
        $content = trim( (string) $content );
        if ( '' === $content ) {
            return '';
        }

        if ( ( '{' === substr( $content, 0, 1 ) && '}' === substr( $content, -1 ) ) || ( '[' === substr( $content, 0, 1 ) && ']' === substr( $content, -1 ) ) ) {
            return $content;
        }

        if ( preg_match( '/```(?:json)?\s*(\{[\s\S]*\}|\[[\s\S]*\])\s*```/i', $content, $matches ) ) {
            return trim( (string) $matches[1] );
        }

        $first_brace = strpos( $content, '{' );
        $last_brace = strrpos( $content, '}' );
        if ( false !== $first_brace && false !== $last_brace && $last_brace > $first_brace ) {
            return trim( substr( $content, $first_brace, $last_brace - $first_brace + 1 ) );
        }

        return '';
    }

    /**
     * 采集模块摘要字段。
     *
     * @param array<string,mixed> $data 模块数据。
     * @return array<int,string>
     */
    private function collect_module_summary_values( $data ) {
        $values = array();
        if ( ! is_array( $data ) ) {
            return $values;
        }

        foreach ( $data as $key => $value ) {
            if ( count( $values ) >= 3 ) {
                break;
            }

            $key = sanitize_key( (string) $key );
            if ( '' === $key ) {
                continue;
            }

            if ( preg_match( '/(url|link|image|logo|file|icon|color|margin|padding|style|class)/', $key ) ) {
                continue;
            }

            if ( is_array( $value ) ) {
                if ( $this->decorator->is_list_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) ) {
                    $nested_values = $this->collect_module_summary_values( $value[0] );
                    foreach ( $nested_values as $nested_value ) {
                        if ( count( $values ) >= 3 ) {
                            break;
                        }
                        $values[] = $nested_value;
                    }
                }
                continue;
            }

            if ( ! is_scalar( $value ) ) {
                continue;
            }

            $text = trim( wp_strip_all_tags( (string) $value ) );
            if ( '' === $text ) {
                continue;
            }

            $values[] = $key . ':' . $this->decorator->truncate_text_for_log( $text, 48 );
        }

        return $values;
    }

    /**
     * 规范化 select 字段值。
     *
     * @param mixed               $value 原值。
     * @param array<string,mixed> $field_schema schema。
     * @param array<int,string>   $style_warnings 样式预警。
     * @param string              $field_path 字段路径。
     * @return string
     */
    private function normalize_select_value( $value, $field_schema, &$style_warnings, $field_path ) {
        $raw_value = is_scalar( $value ) ? (string) $value : '';
        $value = $raw_value;
        $options = isset( $field_schema['options'] ) && is_array( $field_schema['options'] ) ? $field_schema['options'] : array();
        $normalized_options = array();
        $option_keys = array_keys( $options );
        $is_assoc_options = $option_keys !== range( 0, count( $options ) - 1 );

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
                            __( '字段 %s 的选项值不受支持，已回退到默认值。', 'developer-starter' ),
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
                            __( '字段 %s 的选项值不受支持，已回退到默认值。', 'developer-starter' ),
                            $field_path
                        );
                    }
                    return (string) $matched_default_key;
                }
            } elseif ( in_array( $default_value, $normalized_options, true ) ) {
                if ( '' !== $raw_value ) {
                    $style_warnings[] = sprintf(
                        /* translators: %s: field path */
                        __( '字段 %s 的选项值不受支持，已回退到默认值。', 'developer-starter' ),
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
                    __( '字段 %s 的选项值不受支持，已回退到首个可用选项。', 'developer-starter' ),
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
}
