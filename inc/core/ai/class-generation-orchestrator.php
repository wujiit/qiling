<?php
/**
 * Generation orchestrator.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\AI;

use Developer_Starter\Core\AI_Decorator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Generation_Orchestrator {

    /**
     * @var object
     */
    private $decorator;

    /**
     * @var Connection_Manager
     */
    private $connection_manager;

    /**
     * @var object
     */
    private $prompt_builder;

    /**
     * @var Response_Parser
     */
    private $response_parser;

    /**
     * @param object            $decorator          装修服务门面。
     * @param Connection_Manager $connection_manager 连接管理器。
     * @param object             $prompt_builder     消息构建器。
     * @param Response_Parser    $response_parser    响应解析器。
     */
    public function __construct( AI_Decorator $decorator, Connection_Manager $connection_manager, Prompt_Builder $prompt_builder, Response_Parser $response_parser ) {
        $this->decorator = $decorator;
        $this->connection_manager = $connection_manager;
        $this->prompt_builder = $prompt_builder;
        $this->response_parser = $response_parser;
    }

    /**
     * 规划页面结构。
     *
     * @param array<string,mixed> $args 请求参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function plan_page_package( $args ) {
        $post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
        $prompt = isset( $args['prompt'] ) ? trim( (string) $args['prompt'] ) : '';
        $connection_id = isset( $args['connection_id'] ) ? sanitize_key( (string) $args['connection_id'] ) : '';
        $model = isset( $args['model'] ) ? sanitize_text_field( (string) $args['model'] ) : '';
        $module_ids = isset( $args['module_ids'] ) ? $this->decorator->normalize_module_ids_input( $args['module_ids'] ) : array();
        $max_modules = $this->decorator->get_default_max_modules();

        if ( '' === $prompt ) {
            return new \WP_Error( 'empty_prompt', __( '请先输入装修需求。', 'developer-starter' ) );
        }

        if ( $this->decorator->is_disallowed_site_generation_prompt( $prompt ) ) {
            return $this->decorator->get_disallowed_site_generation_error();
        }

        if ( empty( $module_ids ) ) {
            return new \WP_Error(
                'empty_modules',
                sprintf(
                    /* translators: %d: max module count */
                    __( '请先选择 1-%d 个候选功能模块。', 'developer-starter' ),
                    $max_modules
                )
            );
        }

        if ( count( $module_ids ) > $max_modules ) {
            return new \WP_Error(
                'too_many_modules',
                sprintf(
                    /* translators: %d: max module count */
                    __( '单次最多选择 %d 个候选模块。', 'developer-starter' ),
                    $max_modules
                )
            );
        }

        $connection = $this->connection_manager->get_connection( $connection_id );
        if ( ! $connection ) {
            return new \WP_Error( 'invalid_connection', __( 'AI 连接不存在或未启用，请先到主题设置中检查。', 'developer-starter' ) );
        }

        if ( '' === $model ) {
            $model = (string) $connection['default_model'];
        }
        if ( '' === $model ) {
            return new \WP_Error( 'empty_model', __( '当前连接未配置默认模型，请先在主题设置中填写模型名称。', 'developer-starter' ) );
        }

        $selected_modules = $this->decorator->get_selected_module_prompt_schemas( $module_ids );
        if ( empty( $selected_modules ) ) {
            return new \WP_Error( 'invalid_module_selection', __( '候选模块无效，请重新选择。', 'developer-starter' ) );
        }

        $messages = $this->prompt_builder->build_page_plan_messages( $post_id, $prompt, $selected_modules );
        $response = $this->connection_manager->request_chat_completion(
            $connection,
            $model,
            $messages,
            array(
                'request_type' => 'page_plan',
                'module_count' => count( $module_ids ),
                'timeout'      => max( $this->decorator->get_default_request_timeout(), 120 ),
            )
        );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $plan = $this->response_parser->extract_page_plan_from_response( $response['content'], array_keys( $selected_modules ) );
        if ( is_wp_error( $plan ) ) {
            $this->decorator->log_debug_message(
                'AI page plan parse failed',
                array(
                    'connection_id'    => $connection['id'],
                    'connection_name'  => $connection['name'],
                    'model'            => $model,
                    'module_ids'       => array_values( $module_ids ),
                    'error_message'    => $plan->get_error_message(),
                    'response_excerpt' => $this->decorator->truncate_text_for_log( $response['content'], 400 ),
                )
            );
            return $plan;
        }

        return array(
            'message'        => __( '页面规划已完成。', 'developer-starter' ),
            'connectionId'   => $connection['id'],
            'connectionName' => $connection['name'],
            'model'          => $model,
            'plan'           => $plan['plan'],
            'warnings'       => $plan['warnings'],
            'moduleCount'    => count( $plan['plan']['modules'] ),
        );
    }

    /**
     * 生成单个页面模块数据。
     *
     * @param array<string,mixed> $args 请求参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function generate_page_module( $args ) {
        $post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
        $prompt = isset( $args['prompt'] ) ? trim( (string) $args['prompt'] ) : '';
        $connection_id = isset( $args['connection_id'] ) ? sanitize_key( (string) $args['connection_id'] ) : '';
        $model = isset( $args['model'] ) ? sanitize_text_field( (string) $args['model'] ) : '';
        $module_ids = isset( $args['module_ids'] ) ? $this->decorator->normalize_module_ids_input( $args['module_ids'] ) : array();
        $current_module_type = isset( $args['current_module_type'] ) ? sanitize_key( (string) $args['current_module_type'] ) : '';
        $mode = isset( $args['mode'] ) ? sanitize_key( (string) $args['mode'] ) : '';
        $is_localization = AI_Decorator::AI_MODE_LOCALIZATION === $mode;
        $max_modules = $this->decorator->get_default_max_modules();

        if ( '' === $prompt && ! $is_localization ) {
            return new \WP_Error( 'empty_prompt', __( '请先输入装修需求。', 'developer-starter' ) );
        }
        if ( '' === $prompt && $is_localization ) {
            $prompt = __( '按目标语言和市场参数本地化当前模块文案。', 'developer-starter' );
        }

        if ( $this->decorator->is_disallowed_site_generation_prompt( $prompt ) ) {
            return $this->decorator->get_disallowed_site_generation_error();
        }

        if ( empty( $module_ids ) ) {
            return new \WP_Error( 'empty_modules', __( '请先选择候选模块。', 'developer-starter' ) );
        }

        if ( count( $module_ids ) > $max_modules ) {
            return new \WP_Error(
                'too_many_modules',
                sprintf(
                    /* translators: %d: max module count */
                    __( '单次最多选择 %d 个候选模块。', 'developer-starter' ),
                    $max_modules
                )
            );
        }

        if ( '' === $current_module_type ) {
            return new \WP_Error( 'empty_current_module', __( '当前模块参数无效。', 'developer-starter' ) );
        }

        $connection = $this->connection_manager->get_connection( $connection_id );
        if ( ! $connection ) {
            return new \WP_Error( 'invalid_connection', __( 'AI 连接不存在或未启用，请先到主题设置中检查。', 'developer-starter' ) );
        }

        if ( '' === $model ) {
            $model = (string) $connection['default_model'];
        }
        if ( '' === $model ) {
            return new \WP_Error( 'empty_model', __( '当前连接未配置默认模型，请先在主题设置中填写模型名称。', 'developer-starter' ) );
        }

        $plan = $this->response_parser->normalize_generation_plan_input(
            isset( $args['plan'] ) ? $args['plan'] : array(),
            $module_ids
        );
        if ( is_wp_error( $plan ) ) {
            return $plan;
        }

        $current_module_plan = $this->response_parser->find_plan_module_by_type( $plan['plan'], $current_module_type );
        if ( ! $current_module_plan ) {
            return new \WP_Error( 'module_not_in_plan', __( '当前模块不在页面规划结果中。', 'developer-starter' ) );
        }

        $current_module_schema_map = $this->decorator->get_selected_module_prompt_schemas( array( $current_module_type ) );
        if ( empty( $current_module_schema_map[ $current_module_type ] ) ) {
            return new \WP_Error( 'invalid_module_schema', __( '当前内容暂时无法生成，请刷新后重试。', 'developer-starter' ) );
        }
        $current_module_schema = $current_module_schema_map[ $current_module_type ];

        $completed_modules = $this->response_parser->normalize_completed_modules_input(
            isset( $args['completed_modules'] ) ? $args['completed_modules'] : array(),
            $plan['plan']
        );
        $current_module_data = $this->decorator->normalize_json_array_input(
            isset( $args['current_module_data'] ) ? $args['current_module_data'] : array()
        );

        if ( $is_localization ) {
            $localization = $this->decorator->normalize_ai_localization_request(
                isset( $args['localization'] ) ? $args['localization'] : array()
            );
            $text_only_schema = $this->decorator->get_module_text_only_field_schema_map( $current_module_type );
            if ( empty( $text_only_schema ) ) {
                return new \WP_Error( 'empty_localization_text_fields', __( '当前模块没有可本地化的文案字段。', 'developer-starter' ) );
            }

            $messages = $this->prompt_builder->build_module_localization_messages(
                $post_id,
                $prompt,
                $current_module_type,
                $current_module_schema,
                $current_module_data,
                $localization,
                $text_only_schema
            );

            $response = $this->connection_manager->request_chat_completion(
                $connection,
                $model,
                $messages,
                array(
                    'request_type' => 'module_localization',
                    'module_count' => 1,
                    'timeout'      => $this->decorator->get_single_module_request_timeout( count( $completed_modules ) + 1 ),
                )
            );
            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $module_result = $this->response_parser->extract_localized_module_from_response(
                $response['content'],
                $current_module_type,
                $current_module_data,
                $text_only_schema
            );
            if ( is_wp_error( $module_result ) ) {
                $this->decorator->log_debug_message(
                    'AI module localization parse failed',
                    array(
                        'connection_id'    => $connection['id'],
                        'connection_name'  => $connection['name'],
                        'model'            => $model,
                        'module_type'      => $current_module_type,
                        'target_language'  => isset( $localization['target_language'] ) ? $localization['target_language'] : '',
                        'error_message'    => $module_result->get_error_message(),
                        'response_excerpt' => $this->decorator->truncate_text_for_log( $response['content'], 400 ),
                    )
                );
                return $module_result;
            }

            $summary = $this->response_parser->summarize_generated_module_for_context(
                $module_result['module'],
                $current_module_plan,
                $current_module_schema
            );

            return array(
                'message'      => sprintf(
                    /* translators: 1: module name 2: target language */
                    __( '模块“%1$s”已本地化为%2$s。', 'developer-starter' ),
                    isset( $current_module_schema['name'] ) ? (string) $current_module_schema['name'] : $current_module_type,
                    isset( $localization['target_language_label'] ) ? (string) $localization['target_language_label'] : ''
                ),
                'module'       => $module_result['module'],
                'summary'      => $summary,
                'warnings'     => $module_result['warnings'],
                'moduleType'   => $current_module_type,
                'mode'         => AI_Decorator::AI_MODE_LOCALIZATION,
                'localization' => $localization,
            );
        }

        $messages = $this->prompt_builder->build_page_module_messages(
            $post_id,
            $prompt,
            $plan['plan'],
            $current_module_plan,
            $current_module_schema,
            $completed_modules,
            $current_module_data
        );

        $response = $this->connection_manager->request_chat_completion(
            $connection,
            $model,
            $messages,
            array(
                'request_type' => 'module_generate',
                'module_count' => 1,
                'timeout'      => $this->decorator->get_single_module_request_timeout( count( $completed_modules ) + 1 ),
            )
        );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $module_result = $this->response_parser->extract_single_module_from_response( $response['content'], $current_module_type );
        if ( is_wp_error( $module_result ) ) {
            $this->decorator->log_debug_message(
                'AI single module parse failed',
                array(
                    'connection_id'    => $connection['id'],
                    'connection_name'  => $connection['name'],
                    'model'            => $model,
                    'module_type'      => $current_module_type,
                    'error_message'    => $module_result->get_error_message(),
                    'response_excerpt' => $this->decorator->truncate_text_for_log( $response['content'], 400 ),
                )
            );
            return $module_result;
        }

        $summary = $this->response_parser->summarize_generated_module_for_context(
            $module_result['module'],
            $current_module_plan,
            $current_module_schema
        );

        return array(
            'message'    => sprintf(
                /* translators: %s: module name */
                __( '模块“%s”已生成。', 'developer-starter' ),
                isset( $current_module_schema['name'] ) ? (string) $current_module_schema['name'] : $current_module_type
            ),
            'module'     => $module_result['module'],
            'summary'    => $summary,
            'warnings'   => $module_result['warnings'],
            'moduleType' => $current_module_type,
        );
    }

    /**
     * 生成页面装修草稿。
     *
     * @param array<string,mixed> $args 请求参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function generate_page_package( $args ) {
        $post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
        $prompt = isset( $args['prompt'] ) ? trim( (string) $args['prompt'] ) : '';
        $connection_id = isset( $args['connection_id'] ) ? sanitize_key( (string) $args['connection_id'] ) : '';
        $model = isset( $args['model'] ) ? sanitize_text_field( (string) $args['model'] ) : '';
        $module_ids = isset( $args['module_ids'] ) ? $this->decorator->normalize_module_ids_input( $args['module_ids'] ) : array();
        $max_modules = $this->decorator->get_default_max_modules();

        if ( '' === $prompt ) {
            return new \WP_Error( 'empty_prompt', __( '请先输入装修需求。', 'developer-starter' ) );
        }

        if ( $this->decorator->is_disallowed_site_generation_prompt( $prompt ) ) {
            return $this->decorator->get_disallowed_site_generation_error();
        }

        if ( empty( $module_ids ) ) {
            return new \WP_Error(
                'empty_modules',
                sprintf(
                    /* translators: %d: max module count */
                    __( '请先选择 1-%d 个候选功能模块。', 'developer-starter' ),
                    $max_modules
                )
            );
        }

        if ( count( $module_ids ) > $max_modules ) {
            return new \WP_Error(
                'too_many_modules',
                sprintf(
                    /* translators: %d: max module count */
                    __( '单次最多选择 %d 个候选模块。', 'developer-starter' ),
                    $max_modules
                )
            );
        }

        $connection = $this->connection_manager->get_connection( $connection_id );
        if ( ! $connection ) {
            return new \WP_Error( 'invalid_connection', __( 'AI 连接不存在或未启用，请先到主题设置中检查。', 'developer-starter' ) );
        }

        if ( '' === $model ) {
            $model = (string) $connection['default_model'];
        }
        if ( '' === $model ) {
            return new \WP_Error( 'empty_model', __( '当前连接未配置默认模型，请先在主题设置中填写模型名称。', 'developer-starter' ) );
        }

        $selected_modules = $this->decorator->get_selected_module_prompt_schemas( $module_ids );
        if ( empty( $selected_modules ) ) {
            return new \WP_Error( 'invalid_module_selection', __( '候选模块无效，请重新选择。', 'developer-starter' ) );
        }

        $messages = $this->prompt_builder->build_request_messages( $post_id, $prompt, $selected_modules );
        $response = $this->connection_manager->request_chat_completion(
            $connection,
            $model,
            $messages,
            array(
                'request_type' => 'page_generate',
                'module_count' => count( $module_ids ),
                'timeout'      => $this->decorator->get_generation_request_timeout( count( $module_ids ) ),
            )
        );
        if ( is_wp_error( $response ) ) {
            if ( ! $this->should_fallback_page_package_generation( $response ) ) {
                return $response;
            }

            return $this->generate_page_package_via_plan( $args, $connection, $model, $response );
        }

        $package = $this->response_parser->extract_package_from_response( $response['content'], array_keys( $selected_modules ) );
        if ( is_wp_error( $package ) ) {
            $this->decorator->log_debug_message(
                'AI package parse failed',
                array(
                    'connection_id'    => $connection['id'],
                    'connection_name'  => $connection['name'],
                    'model'            => $model,
                    'module_ids'       => array_values( $module_ids ),
                    'error_message'    => $package->get_error_message(),
                    'response_excerpt' => $this->decorator->truncate_text_for_log( $response['content'], 400 ),
                )
            );
            if ( ! $this->should_fallback_page_package_generation( $package ) ) {
                return $package;
            }

            return $this->generate_page_package_via_plan( $args, $connection, $model, $package );
        }

        return $this->build_page_package_response(
            $connection,
            $model,
            $package['package'],
            isset( $package['warnings'] ) && is_array( $package['warnings'] ) ? $package['warnings'] : array(),
            __( 'AI 装修草稿已生成。', 'developer-starter' ),
            array(
                'generationStrategy' => 'page_generate',
            )
        );
    }

    /**
     * Localize an existing page package while preserving module structure.
     *
     * @param array<string,mixed> $args Request args.
     * @return array<string,mixed>|\WP_Error
     */
    public function localize_page_package( $args ) {
        $post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
        $prompt = isset( $args['prompt'] ) ? trim( (string) $args['prompt'] ) : '';
        if ( '' === $prompt ) {
            $prompt = __( '按目标语言和市场参数本地化当前页面文案。', 'developer-starter' );
        }

        $connection_id = isset( $args['connection_id'] ) ? sanitize_key( (string) $args['connection_id'] ) : '';
        if ( '' === $connection_id ) {
            $defaults = $this->decorator->get_default_ai_connection_request_args();
            $connection_id = isset( $defaults['connection_id'] ) ? sanitize_key( (string) $defaults['connection_id'] ) : '';
        }

        $model = isset( $args['model'] ) ? sanitize_text_field( (string) $args['model'] ) : '';
        $localization = $this->decorator->normalize_ai_localization_request(
            isset( $args['localization'] ) ? $args['localization'] : array()
        );
        $localization['scope'] = isset( $localization['scope'] ) && 'template_package' === $localization['scope']
            ? 'template_package'
            : AI_Decorator::AI_SCOPE_PAGE;

        $current_package = $this->decorator->normalize_ai_page_package_input(
            $post_id,
            isset( $args['current_package'] ) ? $args['current_package'] : array()
        );

        $modules = isset( $current_package['modules'] ) && is_array( $current_package['modules'] ) ? array_values( $current_package['modules'] ) : array();
        if ( empty( $modules ) ) {
            return new \WP_Error( 'empty_current_page_modules', __( '当前页面没有可本地化的模块。', 'developer-starter' ) );
        }

        $text_schema_map = $this->decorator->get_page_package_text_only_schema_map( $modules );
        $has_text_fields = false;
        foreach ( $text_schema_map as $schema_item ) {
            if ( is_array( $schema_item ) && ! empty( $schema_item['fields'] ) ) {
                $has_text_fields = true;
                break;
            }
        }
        if ( ! $has_text_fields && '' === trim( (string) $current_package['title'] ) ) {
            return new \WP_Error( 'empty_page_localization_text_fields', __( '当前页面没有可本地化的文案字段。', 'developer-starter' ) );
        }

        $connection = $this->connection_manager->get_connection( $connection_id );
        if ( ! $connection ) {
            return new \WP_Error( 'invalid_connection', __( 'AI 连接不存在或未启用，请先到主题设置中检查。', 'developer-starter' ) );
        }

        if ( '' === $model ) {
            $model = (string) $connection['default_model'];
        }
        if ( '' === $model ) {
            return new \WP_Error( 'empty_model', __( '当前连接未配置默认模型，请先在主题设置中填写模型名称。', 'developer-starter' ) );
        }

        $messages = $this->prompt_builder->build_page_localization_messages(
            $post_id,
            $prompt,
            $current_package,
            $localization,
            $text_schema_map
        );

        $response = $this->connection_manager->request_chat_completion(
            $connection,
            $model,
            $messages,
            array(
                'request_type' => 'page_localization',
                'module_count' => count( $modules ),
                'timeout'      => $this->decorator->get_generation_request_timeout( count( $modules ) ),
            )
        );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $localized = $this->response_parser->extract_localized_page_package_from_response(
            $response['content'],
            $current_package,
            $text_schema_map,
            $localization
        );
        if ( is_wp_error( $localized ) ) {
            $this->decorator->log_debug_message(
                'AI page localization parse failed',
                array(
                    'connection_id'    => $connection['id'],
                    'connection_name'  => $connection['name'],
                    'model'            => $model,
                    'target_language'  => isset( $localization['target_language'] ) ? $localization['target_language'] : '',
                    'error_message'    => $localized->get_error_message(),
                    'response_excerpt' => $this->decorator->truncate_text_for_log( $response['content'], 400 ),
                )
            );
            return $localized;
        }

        $provider_sync = null;
        if ( ! empty( $localization['sync_provider'] ) || ! empty( $localization['create_language_page'] ) ) {
            $provider_sync = $this->decorator->sync_localized_package_to_aifanyi(
                $post_id,
                isset( $localized['package'] ) && is_array( $localized['package'] ) ? $localized['package'] : array(),
                $localization
            );
        }

        return $this->build_page_package_response(
            $connection,
            $model,
            isset( $localized['package'] ) && is_array( $localized['package'] ) ? $localized['package'] : array(),
            isset( $localized['warnings'] ) && is_array( $localized['warnings'] ) ? $localized['warnings'] : array(),
            sprintf(
                /* translators: %s: target language label */
                __( '当前页面已本地化为%s。', 'developer-starter' ),
                isset( $localization['target_language_label'] ) ? (string) $localization['target_language_label'] : ''
            ),
            array(
                'generationStrategy' => 'page_localization',
                'mode'               => AI_Decorator::AI_MODE_LOCALIZATION,
                'localization'       => $localization,
                'localizationReview' => isset( $localized['review'] ) ? $localized['review'] : array(),
                'localizationScore'  => isset( $localized['score'] ) ? $localized['score'] : array(),
                'providerSync'       => $provider_sync,
            )
        );
    }

    /**
     * Batch localize existing pages/posts through the same page-package path.
     *
     * @param array<string,mixed> $args Request args.
     * @return array<string,mixed>|\WP_Error
     */
    public function batch_localize_content( $args ) {
        $localization = $this->decorator->normalize_ai_localization_request(
            isset( $args['localization'] ) ? $args['localization'] : array()
        );
        $localization['scope'] = 'batch';
        $localization['sync_provider'] = true;

        $post_ids = $this->normalize_batch_post_ids( isset( $args['post_ids'] ) ? $args['post_ids'] : array() );
        if ( empty( $post_ids ) ) {
            $post_ids = $this->query_batch_post_ids( $localization );
        }

        if ( empty( $post_ids ) ) {
            return new \WP_Error( 'empty_batch_posts', __( '没有找到可批量本地化的页面或文章。', 'developer-starter' ) );
        }

        $post_ids = array_slice( array_values( array_unique( array_map( 'absint', $post_ids ) ) ), 0, absint( $localization['batch_limit'] ) );
        $results = array();
        $success_count = 0;
        $failed_count = 0;

        foreach ( $post_ids as $post_id ) {
            if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
                $failed_count++;
                $results[] = array(
                    'post_id' => $post_id,
                    'success' => false,
                    'message' => __( '无权编辑该内容。', 'developer-starter' ),
                );
                continue;
            }

            $current_package = $this->decorator->normalize_ai_page_package_input( $post_id, array() );
            $has_modules = ! empty( $current_package['modules'] ) && is_array( $current_package['modules'] );
            $result = $has_modules
                ? $this->localize_page_package(
                    array(
                        'post_id'       => $post_id,
                        'prompt'        => isset( $args['prompt'] ) ? (string) $args['prompt'] : '',
                        'connection_id' => isset( $args['connection_id'] ) ? (string) $args['connection_id'] : '',
                        'model'         => isset( $args['model'] ) ? (string) $args['model'] : '',
                        'current_package' => $current_package,
                        'localization'  => $localization,
                    )
                )
                : $this->localize_content_record(
                    array(
                        'post_id'       => $post_id,
                        'prompt'        => isset( $args['prompt'] ) ? (string) $args['prompt'] : '',
                        'connection_id' => isset( $args['connection_id'] ) ? (string) $args['connection_id'] : '',
                        'model'         => isset( $args['model'] ) ? (string) $args['model'] : '',
                        'localization'  => $localization,
                    )
                );

            if ( is_wp_error( $result ) ) {
                $failed_count++;
                $results[] = array(
                    'post_id' => $post_id,
                    'success' => false,
                    'message' => $result->get_error_message(),
                );
                continue;
            }

            $success_count++;
            $results[] = array(
                'post_id' => $post_id,
                'success' => true,
                'message' => isset( $result['message'] ) ? (string) $result['message'] : '',
                'providerSync' => isset( $result['providerSync'] ) ? $result['providerSync'] : null,
                'localizationScore' => isset( $result['localizationScore'] ) ? $result['localizationScore'] : array(),
            );
        }

        return array(
            'message' => sprintf(
                /* translators: 1: success count 2: failed count */
                __( '批量本地化完成：成功 %1$d 个，失败 %2$d 个。', 'developer-starter' ),
                $success_count,
                $failed_count
            ),
            'mode' => AI_Decorator::AI_MODE_LOCALIZATION,
            'localization' => $localization,
            'successCount' => $success_count,
            'failedCount' => $failed_count,
            'results' => $results,
        );
    }

    /**
     * Localize a post/article/FAQ without Builder modules.
     *
     * @param array<string,mixed> $args Request args.
     * @return array<string,mixed>|\WP_Error
     */
    private function localize_content_record( $args ) {
        $post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
        $post = $post_id > 0 ? get_post( $post_id ) : null;
        if ( ! $post instanceof \WP_Post ) {
            return new \WP_Error( 'invalid_content_post', __( '内容不存在，无法本地化。', 'developer-starter' ) );
        }

        $prompt = isset( $args['prompt'] ) ? trim( (string) $args['prompt'] ) : '';
        if ( '' === $prompt ) {
            $prompt = __( '按目标语言和市场参数本地化当前内容。', 'developer-starter' );
        }

        $connection_id = isset( $args['connection_id'] ) ? sanitize_key( (string) $args['connection_id'] ) : '';
        if ( '' === $connection_id ) {
            $defaults = $this->decorator->get_default_ai_connection_request_args();
            $connection_id = isset( $defaults['connection_id'] ) ? sanitize_key( (string) $defaults['connection_id'] ) : '';
        }
        $model = isset( $args['model'] ) ? sanitize_text_field( (string) $args['model'] ) : '';
        $localization = $this->decorator->normalize_ai_localization_request(
            isset( $args['localization'] ) ? $args['localization'] : array()
        );

        $connection = $this->connection_manager->get_connection( $connection_id );
        if ( ! $connection ) {
            return new \WP_Error( 'invalid_connection', __( 'AI 连接不存在或未启用，请先到主题设置中检查。', 'developer-starter' ) );
        }

        if ( '' === $model ) {
            $model = (string) $connection['default_model'];
        }
        if ( '' === $model ) {
            return new \WP_Error( 'empty_model', __( '当前连接未配置默认模型，请先在主题设置中填写模型名称。', 'developer-starter' ) );
        }

        $source_payload = array(
            'post_id' => $post_id,
            'post_type' => (string) $post->post_type,
            'title' => (string) $post->post_title,
            'excerpt' => (string) $post->post_excerpt,
            'content' => (string) $post->post_content,
            'seo' => $this->decorator->get_post_seo_context( $post_id ),
        );

        $messages = $this->prompt_builder->build_content_localization_messages(
            $post_id,
            $prompt,
            $source_payload,
            $localization
        );

        $response = $this->connection_manager->request_chat_completion(
            $connection,
            $model,
            $messages,
            array(
                'request_type' => 'content_localization',
                'module_count' => 0,
                'timeout'      => max( $this->decorator->get_default_request_timeout(), 120 ),
            )
        );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $localized = $this->response_parser->extract_localized_content_from_response(
            $response['content'],
            $source_payload,
            $localization
        );
        if ( is_wp_error( $localized ) ) {
            return $localized;
        }

        $provider_sync = null;
        if ( ! empty( $localization['sync_provider'] ) || ! empty( $localization['create_language_page'] ) ) {
            $provider_sync = $this->decorator->sync_localized_content_to_aifanyi(
                $post_id,
                isset( $localized['content'] ) && is_array( $localized['content'] ) ? $localized['content'] : array(),
                $localization
            );
        }

        return array(
            'message' => sprintf(
                /* translators: %s: target language label */
                __( '当前内容已本地化为%s。', 'developer-starter' ),
                isset( $localization['target_language_label'] ) ? (string) $localization['target_language_label'] : ''
            ),
            'mode' => AI_Decorator::AI_MODE_LOCALIZATION,
            'content' => isset( $localized['content'] ) ? $localized['content'] : array(),
            'localization' => $localization,
            'localizationReview' => isset( $localized['review'] ) ? $localized['review'] : array(),
            'localizationScore' => isset( $localized['score'] ) ? $localized['score'] : array(),
            'warnings' => isset( $localized['warnings'] ) && is_array( $localized['warnings'] ) ? $localized['warnings'] : array(),
            'providerSync' => $provider_sync,
        );
    }

    /**
     * Normalize batch post IDs.
     *
     * @param mixed $value Raw value.
     * @return array<int,int>
     */
    private function normalize_batch_post_ids( $value ) {
        if ( is_string( $value ) ) {
            $decoded = json_decode( trim( $value ), true );
            $value = is_array( $decoded ) ? $decoded : preg_split( '/[\s,]+/', $value );
        }

        if ( ! is_array( $value ) ) {
            return array();
        }

        return array_values( array_filter( array_map( 'absint', $value ) ) );
    }

    /**
     * Query content IDs for batch localization.
     *
     * @param array<string,mixed> $localization Localization args.
     * @return array<int,int>
     */
    private function query_batch_post_ids( $localization ) {
        $post_types = array();
        $types = isset( $localization['batch_content_types'] ) && is_array( $localization['batch_content_types'] )
            ? $localization['batch_content_types']
            : array( 'page' );

        if ( in_array( 'page', $types, true ) ) {
            $post_types[] = 'page';
        }
        if ( in_array( 'post', $types, true ) ) {
            $post_types[] = 'post';
        }
        if ( in_array( 'faq', $types, true ) ) {
            foreach ( array( 'ds_faq', 'qiling_faq', 'faq' ) as $faq_post_type ) {
                if ( post_type_exists( $faq_post_type ) ) {
                    $post_types[] = $faq_post_type;
                }
            }
        }

        $post_types = array_values( array_unique( array_filter( $post_types, 'post_type_exists' ) ) );
        if ( empty( $post_types ) ) {
            return array();
        }

        return get_posts(
            array(
                'post_type'      => $post_types,
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => absint( $localization['batch_limit'] ),
                'fields'         => 'ids',
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );
    }

    /**
     * 当整页生成失败时，自动回退到“先规划、再逐模块生成”。
     *
     * @param array<string,mixed> $args 请求参数。
     * @param array<string,mixed> $connection 连接配置。
     * @param string              $model 连接目标名称。
     * @param \WP_Error|null      $primary_error 首次失败的错误。
     * @return array<string,mixed>|\WP_Error
     */
    private function generate_page_package_via_plan( $args, $connection, $model, $primary_error = null ) {
        $this->decorator->log_debug_message(
            'AI package fallback triggered',
            array(
                'connection_id'   => isset( $connection['id'] ) ? sanitize_key( (string) $connection['id'] ) : '',
                'connection_name' => isset( $connection['name'] ) ? sanitize_text_field( (string) $connection['name'] ) : '',
                'model'           => sanitize_text_field( (string) $model ),
                'post_id'         => isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0,
                'module_ids'      => isset( $args['module_ids'] ) ? array_values( $this->decorator->normalize_module_ids_input( $args['module_ids'] ) ) : array(),
                'error_code'      => is_wp_error( $primary_error ) ? $primary_error->get_error_code() : '',
                'error_message'   => is_wp_error( $primary_error ) ? $primary_error->get_error_message() : '',
            )
        );

        $plan_result = $this->plan_page_package( $args );
        if ( is_wp_error( $plan_result ) ) {
            return $this->wrap_page_package_fallback_error( $plan_result, $primary_error );
        }

        $plan = isset( $plan_result['plan'] ) && is_array( $plan_result['plan'] ) ? $plan_result['plan'] : array();
        $package = array(
            'title'                => isset( $plan['title'] ) ? (string) $plan['title'] : '',
            'page_template'        => isset( $plan['page_template'] ) ? (string) $plan['page_template'] : $this->decorator->get_default_page_template(),
            'hide_page_header'     => ! empty( $plan['hide_page_header'] ),
            'transparent_header'   => ! empty( $plan['transparent_header'] ),
            'enable_scroll_reveal' => ! empty( $plan['enable_scroll_reveal'] ),
            'seo'                  => isset( $plan['seo'] ) && is_array( $plan['seo'] ) ? $plan['seo'] : array(),
            'modules'              => array(),
        );

        $warnings = array();
        $fallback_warning = $this->get_page_package_fallback_warning( $primary_error );
        if ( '' !== $fallback_warning ) {
            $warnings[] = $fallback_warning;
        }

        if ( ! empty( $plan_result['warnings'] ) && is_array( $plan_result['warnings'] ) ) {
            $warnings = array_merge( $warnings, $plan_result['warnings'] );
        }

        $completed_modules = array();
        $plan_modules = isset( $plan['modules'] ) && is_array( $plan['modules'] ) ? $plan['modules'] : array();
        foreach ( $plan_modules as $module_plan ) {
            if ( ! is_array( $module_plan ) || empty( $module_plan['type'] ) ) {
                continue;
            }

            $current_module_type = sanitize_key( (string) $module_plan['type'] );
            if ( '' === $current_module_type ) {
                continue;
            }

            $module_result = $this->generate_page_module(
                array(
                    'post_id'             => isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0,
                    'prompt'              => isset( $args['prompt'] ) ? (string) $args['prompt'] : '',
                    'connection_id'       => isset( $args['connection_id'] ) ? sanitize_key( (string) $args['connection_id'] ) : '',
                    'model'               => isset( $args['model'] ) ? sanitize_text_field( (string) $args['model'] ) : '',
                    'module_ids'          => isset( $args['module_ids'] ) ? $args['module_ids'] : array(),
                    'plan'                => $plan,
                    'current_module_type' => $current_module_type,
                    'completed_modules'   => $completed_modules,
                )
            );

            if ( is_wp_error( $module_result ) ) {
                return $this->wrap_page_package_fallback_error( $module_result, $primary_error, $current_module_type );
            }

            if ( isset( $module_result['module'] ) && is_array( $module_result['module'] ) ) {
                $package['modules'][] = $module_result['module'];
            }

            if ( ! empty( $module_result['summary'] ) && is_scalar( $module_result['summary'] ) ) {
                $completed_modules[] = array(
                    'type'    => $current_module_type,
                    'summary' => sanitize_textarea_field( (string) $module_result['summary'] ),
                );
            }

            if ( ! empty( $module_result['warnings'] ) && is_array( $module_result['warnings'] ) ) {
                $warnings = array_merge( $warnings, $module_result['warnings'] );
            }
        }

        if ( empty( $package['modules'] ) ) {
            return $this->wrap_page_package_fallback_error(
                new \WP_Error( 'empty_fallback_modules', __( '自动回退后没有生成可用模块，请重试。', 'developer-starter' ) ),
                $primary_error
            );
        }

        return $this->build_page_package_response(
            $connection,
            $model,
            $package,
            $warnings,
            __( '整页生成失败，已自动切换为分模块生成并完成。', 'developer-starter' ),
            array(
                'generationStrategy' => 'planned_modules',
                'fallbackReason'     => is_wp_error( $primary_error ) ? $primary_error->get_error_code() : '',
            )
        );
    }

    /**
     * 判断整页生成失败后是否应该自动回退。
     *
     * @param \WP_Error $error 错误对象。
     * @return bool
     */
    private function should_fallback_page_package_generation( $error ) {
        if ( ! is_wp_error( $error ) ) {
            return false;
        }

        return in_array(
            $error->get_error_code(),
            array(
                'request_timeout',
                'invalid_response',
                'empty_response',
                'no_json_found',
                'invalid_json_response',
                'empty_package_modules',
                'no_valid_modules',
            ),
            true
        );
    }

    /**
     * 获取自动回退时的提示文案。
     *
     * @param \WP_Error|null $error 错误对象。
     * @return string
     */
    private function get_page_package_fallback_warning( $error = null ) {
        if ( ! is_wp_error( $error ) ) {
            return '';
        }

        if ( 'request_timeout' === $error->get_error_code() ) {
            return __( '整页生成超时，系统已自动切换为分模块生成。', 'developer-starter' );
        }

        return __( '整页生成结果不可直接解析，系统已自动切换为分模块生成。', 'developer-starter' );
    }

    /**
     * 统一组装页面包返回结构。
     *
     * @param array<string,mixed> $connection 连接配置。
     * @param string              $model 连接目标名称。
     * @param array<string,mixed> $package 页面包。
     * @param array<int,string>   $warnings 预警列表。
     * @param string              $message 返回消息。
     * @param array<string,mixed> $extra 额外字段。
     * @return array<string,mixed>|\WP_Error
     */
    private function build_page_package_response( $connection, $model, $package, $warnings = array(), $message = '', $extra = array() ) {
        $json = wp_json_encode(
            $package,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ( ! is_string( $json ) || '' === $json ) {
            return new \WP_Error( 'json_encode_failed', __( 'AI 结果已生成，但转成 JSON 失败。', 'developer-starter' ) );
        }

        $response = array(
            'message'        => '' !== $message ? $message : __( 'AI 装修草稿已生成。', 'developer-starter' ),
            'connectionId'   => isset( $connection['id'] ) ? (string) $connection['id'] : '',
            'connectionName' => isset( $connection['name'] ) ? (string) $connection['name'] : '',
            'model'          => (string) $model,
            'package'        => $package,
            'json'           => $json,
            'warnings'       => array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) ),
            'moduleCount'    => isset( $package['modules'] ) && is_array( $package['modules'] ) ? count( $package['modules'] ) : 0,
        );

        if ( is_array( $extra ) && ! empty( $extra ) ) {
            $response = array_merge( $response, $extra );
        }

        return $response;
    }

    /**
     * 包装自动回退后的错误信息。
     *
     * @param \WP_Error      $fallback_error 回退阶段错误。
     * @param \WP_Error|null $primary_error 首次失败错误。
     * @param string         $module_type 当前失败模块。
     * @return \WP_Error
     */
    private function wrap_page_package_fallback_error( $fallback_error, $primary_error = null, $module_type = '' ) {
        if ( ! is_wp_error( $fallback_error ) ) {
            return new \WP_Error( 'unknown_fallback_error', __( '自动回退生成失败。', 'developer-starter' ) );
        }

        if ( '' !== $module_type ) {
            $message = sprintf(
                /* translators: 1: module type 2: error message */
                __( '整页生成未成功，自动切换为分模块生成时，在模块 %1$s 处失败：%2$s', 'developer-starter' ),
                $module_type,
                $fallback_error->get_error_message()
            );
        } else {
            $message = sprintf(
                /* translators: %s: error message */
                __( '整页生成未成功，自动切换为分模块生成后仍失败：%s', 'developer-starter' ),
                $fallback_error->get_error_message()
            );
        }

        $this->decorator->log_debug_message(
            'AI package fallback failed',
            array(
                'primary_error_code'     => is_wp_error( $primary_error ) ? $primary_error->get_error_code() : '',
                'primary_error_message'  => is_wp_error( $primary_error ) ? $primary_error->get_error_message() : '',
                'fallback_error_code'    => $fallback_error->get_error_code(),
                'fallback_error_message' => $fallback_error->get_error_message(),
                'module_type'            => sanitize_key( (string) $module_type ),
            )
        );

        return new \WP_Error( $fallback_error->get_error_code(), $message );
    }
}
