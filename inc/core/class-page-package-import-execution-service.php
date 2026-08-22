<?php
/**
 * 页面数据包导入执行服务
 *
 * 负责根据预检结果执行页面创建、更新、站点设置应用与历史记录写入。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Import_Execution_Service {

    /**
     * 页面辅助服务。
     *
     * @var Page_Package_Page_Service
     */
    private $page_service;

    /**
     * 导入状态服务。
     *
     * @var Page_Package_Import_State_Service
     */
    private $import_state_service;

    /**
     * 服务配置。
     *
     * @var array<string,mixed>
     */
    private $config = array();

    /**
     * 构造函数。
     *
     * @param Page_Package_Page_Service         $page_service         页面服务。
     * @param Page_Package_Import_State_Service $import_state_service 导入状态服务。
     * @param array<string,mixed>               $config               运行配置。
     */
    public function __construct( $page_service, $import_state_service, $config = array() ) {
        $this->page_service         = $page_service;
        $this->import_state_service = $import_state_service;
        $this->config               = wp_parse_args(
            is_array( $config ) ? $config : array(),
            array(
                'package_version'             => 1,
                'conflict_strategy_skip'      => 'skip_existing',
                'conflict_strategy_duplicate' => 'create_with_new_slug',
                'conflict_strategy_update'    => 'update_same_package',
                'reserved_page_definitions'   => array(),
                'callbacks'                   => array(),
            )
        );
    }

    /**
     * 执行多页面数据包导入。
     *
     * @param array<string,mixed> $prepared_package 经过预检的数据包。
     * @param array<string,mixed> $options          导入参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function import_site_package( $prepared_package, $options = array() ) {
        $options = $this->normalize_import_options( $options );

        if ( ! is_array( $prepared_package ) || empty( $prepared_package['pages'] ) || ! is_array( $prepared_package['pages'] ) ) {
            return new \WP_Error( 'invalid_package', __( '没有可导入的页面数据。', 'developer-starter' ) );
        }

        $meta              = isset( $prepared_package['meta'] ) && is_array( $prepared_package['meta'] ) ? $prepared_package['meta'] : array();
        $scope             = isset( $prepared_package['scope'] ) ? sanitize_key( (string) $prepared_package['scope'] ) : ( isset( $meta['scope'] ) ? sanitize_key( (string) $meta['scope'] ) : 'page' );
        $package_id        = isset( $meta['package_id'] ) ? sanitize_key( (string) $meta['package_id'] ) : '';
        $package_title     = isset( $meta['title'] ) ? sanitize_text_field( (string) $meta['title'] ) : '';
        $package_version   = isset( $meta['version'] ) ? absint( $meta['version'] ) : absint( $this->config['package_version'] );
        $conflict_strategy = $this->normalize_conflict_strategy(
            isset( $meta['conflict_strategy'] ) ? $meta['conflict_strategy'] : $this->config['conflict_strategy_skip']
        );

        if ( '' === $package_id ) {
            $package_id = 'site-package';
        }

        $import_run_id = function_exists( 'wp_generate_uuid4' )
            ? 'import-' . wp_generate_uuid4()
            : 'import-' . uniqid();
        $operator_id = get_current_user_id() ? absint( get_current_user_id() ) : 1;
        $started_at  = time();
        $snapshot    = $this->import_state_service->capture_import_snapshot( $prepared_package );
        if ( ! isset( $snapshot['created_page_ids'] ) || ! is_array( $snapshot['created_page_ids'] ) ) {
            $snapshot['created_page_ids'] = array();
        }

        $results         = array();
        $page_key_map    = array();
        $imported_pages  = array();
        $created_count   = 0;
        $duplicate_count = 0;
        $updated_count   = 0;
        $skipped_count   = 0;
        $error_count     = 0;

        $existing_pages = isset( $prepared_package['existing_pages'] ) && is_array( $prepared_package['existing_pages'] ) ? $prepared_package['existing_pages'] : array();
        foreach ( $existing_pages as $page_key => $page_target ) {
            if ( empty( $page_target['action'] ) || 'skip' !== $page_target['action'] ) {
                continue;
            }

            $existing_page_id = isset( $page_target['page_id'] ) ? absint( $page_target['page_id'] ) : 0;
            if ( $existing_page_id <= 0 ) {
                continue;
            }

            $existing_post = get_post( $existing_page_id );
            if ( ! $existing_post instanceof \WP_Post || $existing_post->post_type !== 'page' ) {
                continue;
            }

            $page_key_map[ sanitize_key( (string) $page_key ) ] = $existing_page_id;
            $skipped_count++;
            $results[] = array(
                'page_key' => sanitize_key( (string) $page_key ),
                'title'    => get_the_title( $existing_page_id ),
                'slug'     => $existing_post->post_name,
                'action'   => 'skip',
                'page_id'  => $existing_page_id,
                'message'  => $this->config['conflict_strategy_update'] === $conflict_strategy
                    ? __( 'URL 已存在，但不是当前数据包的历史页面，已按安全策略跳过。', 'developer-starter' )
                    : __( 'URL 已存在，已按当前策略跳过。', 'developer-starter' ),
            );
        }

        foreach ( $prepared_package['pages'] as $page ) {
            if ( ! is_array( $page ) || empty( $page['page_key'] ) ) {
                continue;
            }

            $page_key      = sanitize_key( (string) $page['page_key'] );
            $slug          = isset( $page['slug'] ) ? sanitize_title( (string) $page['slug'] ) : '';
            $target_slug   = isset( $page['target_slug'] ) ? sanitize_title( (string) $page['target_slug'] ) : $slug;
            $import_action = isset( $page['import_action'] ) ? sanitize_key( (string) $page['import_action'] ) : 'create';

            if ( '' === $page_key ) {
                continue;
            }

            $post_status = isset( $page['post_status'] ) ? sanitize_key( (string) $page['post_status'] ) : 'publish';
            if ( ! in_array( $post_status, array( 'publish', 'draft', 'private' ), true ) ) {
                $post_status = 'publish';
            }

            if ( in_array( $import_action, array( 'create', 'duplicate' ), true ) ) {
                if ( '' === $target_slug ) {
                    continue;
                }

                if ( 'duplicate' === $import_action ) {
                    $target_slug = $this->generate_unique_page_slug( $target_slug );
                }

                $post_id = wp_insert_post(
                    array(
                        'post_title'   => isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : __( '导入页面', 'developer-starter' ),
                        'post_name'    => $target_slug,
                        'post_status'  => $post_status,
                        'post_type'    => 'page',
                        'post_content' => '',
                        'post_author'  => get_current_user_id() ?: 1,
                    ),
                    true
                );
            } elseif ( 'update' === $import_action ) {
                $existing_page_id = isset( $page['existing_page_id'] ) ? absint( $page['existing_page_id'] ) : 0;
                if ( $existing_page_id <= 0 ) {
                    $error_count++;
                    $results[] = array(
                        'page_key' => $page_key,
                        'title'    => isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : __( '导入页面', 'developer-starter' ),
                        'slug'     => $slug,
                        'action'   => 'error',
                        'page_id'  => 0,
                        'message'  => __( '找不到需要更新的历史页面。', 'developer-starter' ),
                    );
                    continue;
                }

                $existing_post = get_post( $existing_page_id );
                if ( ! $existing_post instanceof \WP_Post || 'page' !== $existing_post->post_type ) {
                    $error_count++;
                    $results[] = array(
                        'page_key' => $page_key,
                        'title'    => isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : __( '导入页面', 'developer-starter' ),
                        'slug'     => $slug,
                        'action'   => 'error',
                        'page_id'  => 0,
                        'message'  => __( '历史页面不存在，无法执行更新。', 'developer-starter' ),
                    );
                    continue;
                }

                $target_slug = '' !== $target_slug ? $target_slug : $existing_post->post_name;
                $post_id     = wp_update_post(
                    array(
                        'ID'          => $existing_page_id,
                        'post_title'  => isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : get_the_title( $existing_page_id ),
                        'post_name'   => $target_slug,
                        'post_status' => $post_status,
                    ),
                    true
                );
            } else {
                continue;
            }

            if ( is_wp_error( $post_id ) ) {
                $error_count++;
                $results[] = array(
                    'page_key' => $page_key,
                    'title'    => isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : __( '导入页面', 'developer-starter' ),
                    'slug'     => '' !== $target_slug ? $target_slug : $slug,
                    'action'   => 'error',
                    'page_id'  => 0,
                    'message'  => $post_id->get_error_message(),
                );
                continue;
            }

            $post_id = absint( $post_id );

            $modules = isset( $page['modules'] ) && is_array( $page['modules'] ) ? $page['modules'] : array();
            update_post_meta( $post_id, '_developer_starter_modules', $modules );

            $template = isset( $page['template'] ) ? $this->page_service->normalize_page_template( $page['template'] ) : 'default';
            if ( '' === $template ) {
                $template = 'default';
            }
            update_post_meta( $post_id, '_wp_page_template', $template );

            $settings = isset( $page['settings'] ) && is_array( $page['settings'] ) ? $page['settings'] : array();
            $this->page_service->apply_page_settings( $post_id, $settings );

            update_post_meta( $post_id, '_qiling_site_package_id', $package_id );
            update_post_meta( $post_id, '_qiling_site_package_page_key', $page_key );
            update_post_meta( $post_id, '_qiling_site_package_version', (string) $package_version );
            if ( '' !== $package_title ) {
                update_post_meta( $post_id, '_qiling_site_package_title', $package_title );
            }

            $imported_pages[ $page_key ] = array(
                'post_id' => $post_id,
                'modules' => $modules,
            );

            $page_key_map[ $page_key ] = $post_id;

            if ( 'update' === $import_action ) {
                $updated_count++;
            } elseif ( 'duplicate' === $import_action ) {
                $duplicate_count++;
                $snapshot['created_page_ids'][] = $post_id;
            } else {
                $created_count++;
                $snapshot['created_page_ids'][] = $post_id;
            }

            $results[] = array(
                'page_key' => $page_key,
                'title'    => get_the_title( $post_id ),
                'slug'     => '' !== $target_slug ? $target_slug : $slug,
                'action'   => $import_action,
                'page_id'  => $post_id,
                'message'  => 'update' === $import_action
                    ? __( '页面更新成功。', 'developer-starter' )
                    : ( 'duplicate' === $import_action ? __( '页面副本创建成功。', 'developer-starter' ) : __( '页面创建成功。', 'developer-starter' ) ),
            );
        }

        $site_option_messages = array();
        if ( ! empty( $options['apply_site_options'] ) ) {
            $site_option_messages = $this->page_service->apply_site_options(
                isset( $prepared_package['site_options'] ) && is_array( $prepared_package['site_options'] ) ? $prepared_package['site_options'] : array(),
                $page_key_map
            );
        } elseif ( ! empty( $prepared_package['site_options'] ) && is_array( $prepared_package['site_options'] ) ) {
            $site_option_messages[] = __( '已按安全策略跳过站点设置应用（未勾选“应用站点设置”）。', 'developer-starter' );
        }

        $link_resolution_messages = $this->page_service->resolve_imported_page_links(
            $imported_pages,
            $page_key_map,
            $this->get_reserved_page_definitions()
        );
        if ( function_exists( 'developer_starter_resolve_imported_page_region_decoration' ) ) {
            foreach ( $imported_pages as $imported_page ) {
                if ( ! empty( $imported_page['post_id'] ) ) {
                    developer_starter_resolve_imported_page_region_decoration( $imported_page['post_id'], $page_key_map );
                }
            }
        }

        $site_options_for_history = isset( $prepared_package['site_options'] ) && is_array( $prepared_package['site_options'] )
            ? $prepared_package['site_options']
            : array();
        $history_record = array(
            'run_id'             => $import_run_id,
            'created_at'         => $started_at,
            'operator_id'        => $operator_id,
            'package_id'         => $package_id,
            'package_title'      => $package_title,
            'package_version'    => $package_version,
            'scope'              => $scope,
            'apply_site_options' => ! empty( $options['apply_site_options'] ),
            'front_page_key'     => isset( $site_options_for_history['front_page'] ) ? sanitize_key( (string) $site_options_for_history['front_page'] ) : '',
            'stats'              => array(
                'created_count'   => $created_count,
                'duplicate_count' => $duplicate_count,
                'updated_count'   => $updated_count,
                'skipped_count'   => $skipped_count,
                'error_count'     => $error_count,
            ),
            'results'            => $results,
            'snapshot'           => $snapshot,
            'rolled_back_at'     => 0,
            'rollback_result'    => array(),
        );
        $this->import_state_service->append_import_history_record( $history_record );

        return array(
            'results'                  => $results,
            'created_count'            => $created_count,
            'duplicate_count'          => $duplicate_count,
            'updated_count'            => $updated_count,
            'skipped_count'            => $skipped_count,
            'error_count'              => $error_count,
            'link_resolution_messages' => $link_resolution_messages,
            'site_option_messages'     => $site_option_messages,
            'import_run_id'            => $import_run_id,
            'scope'                    => $scope,
        );
    }

    /**
     * 规范化导入参数。
     *
     * @param array<string,mixed> $options 原始参数。
     * @return array<string,bool>
     */
    private function normalize_import_options( $options ) {
        return array(
            'apply_site_options' => ! empty( $options['apply_site_options'] ),
        );
    }

    /**
     * 规范化 URL 冲突策略。
     *
     * @param mixed $strategy 原始策略。
     * @return string
     */
    private function normalize_conflict_strategy( $strategy ) {
        $strategy = sanitize_key( is_scalar( $strategy ) ? (string) $strategy : '' );
        if ( ! in_array( $strategy, array( $this->config['conflict_strategy_skip'], $this->config['conflict_strategy_duplicate'], $this->config['conflict_strategy_update'] ), true ) ) {
            $strategy = (string) $this->config['conflict_strategy_skip'];
        }

        return $strategy;
    }

    /**
     * 生成唯一 slug。
     *
     * @param string $slug 原始 slug。
     * @return string
     */
    private function generate_unique_page_slug( $slug ) {
        $callback = $this->get_callback( 'generate_unique_page_slug' );
        if ( $callback ) {
            return (string) call_user_func( $callback, $slug );
        }

        return sanitize_title( (string) $slug );
    }

    /**
     * 获取系统保留页定义。
     *
     * @return array<int,array<string,mixed>>
     */
    private function get_reserved_page_definitions() {
        return isset( $this->config['reserved_page_definitions'] ) && is_array( $this->config['reserved_page_definitions'] )
            ? $this->config['reserved_page_definitions']
            : array();
    }

    /**
     * 获取回调。
     *
     * @param string $key 回调键名。
     * @return callable|null
     */
    private function get_callback( $key ) {
        if ( empty( $this->config['callbacks'][ $key ] ) || ! is_callable( $this->config['callbacks'][ $key ] ) ) {
            return null;
        }

        return $this->config['callbacks'][ $key ];
    }
}
