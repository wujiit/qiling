<?php
/**
 * 页面数据包管理器
 *
 * 负责多页面 JSON 数据包的解析、预检与导入。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Manager {

    /**
     * 多页面数据包类型。
     */
    const PACKAGE_TYPE = 'developer_starter_site_package';

    /**
     * 当前支持的数据包协议版本。
     */
    const PACKAGE_VERSION = 1;

    /**
     * 页面包作用域。
     */
    const PACKAGE_SCOPE_PAGE = 'page';

    /**
     * 整站包作用域。
     */
    const PACKAGE_SCOPE_SITE = 'site';

    /**
     * 允许导入的最大 JSON 体积（字节）。
     */
    const MAX_PACKAGE_BYTES = 2097152;

    /**
     * 单个数据包允许包含的最大页面数。
     */
    const MAX_PACKAGE_PAGES = 50;

    /**
     * 单页允许导入的最大模块数。
     */
    const MAX_MODULES_PER_PAGE = 60;

    /**
     * repeater 字段允许的最大子项数。
     */
    const MAX_REPEATER_ITEMS = 100;

    /**
     * 预览页生存时长（秒）。
     */
    const PREVIEW_TTL = 7200;

    /**
     * 导入任务锁定时长（秒）。
     */
    const IMPORT_LOCK_TTL = 120;

    /**
     * 导入历史 option 键。
     */
    const IMPORT_HISTORY_OPTION = 'developer_starter_site_package_import_history';

    /**
     * 最多保留多少条导入历史。
     */
    const IMPORT_HISTORY_MAX = 30;

    /**
     * 冲突策略：跳过已存在页面。
     */
    const CONFLICT_STRATEGY_SKIP = 'skip_existing';

    /**
     * 冲突策略：自动生成新 URL 创建副本。
     */
    const CONFLICT_STRATEGY_DUPLICATE = 'create_with_new_slug';

    /**
     * 冲突策略：仅更新同一数据包历史页面。
     */
    const CONFLICT_STRATEGY_UPDATE = 'update_same_package';

    /**
     * 多页包模块导入服务。
     *
     * @var Page_Package_Module_Service|null
     */
    private $module_service = null;

    /**
     * 多页包页面辅助服务。
     *
     * @var Page_Package_Page_Service|null
     */
    private $page_service = null;

    /**
     * 多页包导入状态服务。
     *
     * @var Page_Package_Import_State_Service|null
     */
    private $import_state_service = null;

    /**
     * 多页包诊断服务。
     *
     * @var Page_Package_Diagnostics_Service|null
     */
    private $diagnostics_service = null;

    /**
     * 多页包分析服务。
     *
     * @var Page_Package_Analysis_Service|null
     */
    private $analysis_service = null;

    /**
     * 多页包预览服务。
     *
     * @var Page_Package_Preview_Service|null
     */
    private $preview_service = null;

    /**
     * 多页包导出服务。
     *
     * @var Page_Package_Export_Service|null
     */
    private $export_service = null;

    /**
     * 多页包导入执行服务。
     *
     * @var Page_Package_Import_Execution_Service|null
     */
    private $import_execution_service = null;

    /**
     * 获取模块导入服务。
     *
     * @return Page_Package_Module_Service
     */
    private function get_module_service() {
        if ( null === $this->module_service ) {
            $this->module_service = new Page_Package_Module_Service(
                array(
                    'max_modules_per_page' => self::MAX_MODULES_PER_PAGE,
                    'max_repeater_items'   => self::MAX_REPEATER_ITEMS,
                )
            );
        }

        return $this->module_service;
    }

    /**
     * 获取页面辅助服务。
     *
     * @return Page_Package_Page_Service
     */
    private function get_page_service() {
        if ( null === $this->page_service ) {
            $this->page_service = new Page_Package_Page_Service();
        }

        return $this->page_service;
    }

    /**
     * 获取导入状态服务。
     *
     * @return Page_Package_Import_State_Service
     */
    private function get_import_state_service() {
        if ( null === $this->import_state_service ) {
            $this->import_state_service = new Page_Package_Import_State_Service(
                array(
                    'import_lock_ttl'       => self::IMPORT_LOCK_TTL,
                    'import_history_option' => self::IMPORT_HISTORY_OPTION,
                    'import_history_max'    => self::IMPORT_HISTORY_MAX,
                    'callbacks'             => array(
                        'normalize_page_template' => function( $template ) {
                            return $this->normalize_page_template( $template );
                        },
                        'normalize_bool_value' => function( $value, $default = false ) {
                            return $this->normalize_bool_value( $value, $default );
                        },
                        'apply_page_settings' => function( $post_id, $settings ) {
                            return $this->apply_page_settings( $post_id, $settings );
                        },
                        'get_page_modules_for_package' => function( $page_id ) {
                            return $this->get_page_modules_for_package( $page_id );
                        },
                    ),
                )
            );
        }

        return $this->import_state_service;
    }

    /**
     * 获取包级诊断服务。
     *
     * @return Page_Package_Diagnostics_Service
     */
    private function get_diagnostics_service() {
        if ( null === $this->diagnostics_service ) {
            $this->diagnostics_service = new Page_Package_Diagnostics_Service();
        }

        return $this->diagnostics_service;
    }

    /**
     * 获取包解析与预检服务。
     *
     * @return Page_Package_Analysis_Service
     */
    private function get_analysis_service() {
        if ( null === $this->analysis_service ) {
            $this->analysis_service = new Page_Package_Analysis_Service(
                $this->get_module_service(),
                $this->get_page_service(),
                $this->get_diagnostics_service(),
                array(
                    'package_type'                => self::PACKAGE_TYPE,
                    'package_version'             => self::PACKAGE_VERSION,
                    'max_package_bytes'           => self::MAX_PACKAGE_BYTES,
                    'max_package_pages'           => self::MAX_PACKAGE_PAGES,
                    'conflict_strategy_skip'      => self::CONFLICT_STRATEGY_SKIP,
                    'conflict_strategy_duplicate' => self::CONFLICT_STRATEGY_DUPLICATE,
                    'conflict_strategy_update'    => self::CONFLICT_STRATEGY_UPDATE,
                    'reserved_page_definitions'   => $this->get_reserved_page_definitions(),
                )
            );
        }

        return $this->analysis_service;
    }

    /**
     * 获取包预览服务。
     *
     * @return Page_Package_Preview_Service
     */
    private function get_preview_service() {
        if ( null === $this->preview_service ) {
            $this->preview_service = new Page_Package_Preview_Service(
                array(
                    'preview_ttl' => self::PREVIEW_TTL,
                    'callbacks'   => array(
                        'normalize_page_template' => function( $template ) {
                            return $this->normalize_page_template( $template );
                        },
                        'apply_page_settings' => function( $post_id, $settings ) {
                            return $this->apply_page_settings( $post_id, $settings );
                        },
                        'generate_unique_page_slug' => function( $slug ) {
                            return $this->generate_unique_page_slug( $slug );
                        },
                    ),
                )
            );
        }

        return $this->preview_service;
    }

    /**
     * 获取包导出服务。
     *
     * @return Page_Package_Export_Service
     */
    private function get_export_service() {
        if ( null === $this->export_service ) {
            $this->export_service = new Page_Package_Export_Service(
                $this->get_page_service(),
                $this->get_diagnostics_service(),
                $this->get_preview_service(),
                array(
                    'package_type'              => self::PACKAGE_TYPE,
                    'package_version'           => self::PACKAGE_VERSION,
                    'theme_identifier'          => 'qiling',
                    'reserved_page_definitions' => $this->get_reserved_page_definitions(),
                    'callbacks'                 => array(
                        'get_page_modules_for_package' => function( $page_id ) {
                            return $this->get_page_modules_for_package( $page_id );
                        },
                    ),
                )
            );
        }

        return $this->export_service;
    }

    /**
     * 获取包导入执行服务。
     *
     * @return Page_Package_Import_Execution_Service
     */
    private function get_import_execution_service() {
        if ( null === $this->import_execution_service ) {
            $this->import_execution_service = new Page_Package_Import_Execution_Service(
                $this->get_page_service(),
                $this->get_import_state_service(),
                array(
                    'package_version'             => self::PACKAGE_VERSION,
                    'conflict_strategy_skip'      => self::CONFLICT_STRATEGY_SKIP,
                    'conflict_strategy_duplicate' => self::CONFLICT_STRATEGY_DUPLICATE,
                    'conflict_strategy_update'    => self::CONFLICT_STRATEGY_UPDATE,
                    'reserved_page_definitions'   => $this->get_reserved_page_definitions(),
                    'callbacks'                   => array(
                        'generate_unique_page_slug' => function( $slug ) {
                            return $this->generate_unique_page_slug( $slug );
                        },
                    ),
                )
            );
        }

        return $this->import_execution_service;
    }

    /**
     * 解析并预检多页面数据包。
     *
     * @param string $raw_json 原始 JSON。
     * @return array<string,mixed>|\WP_Error
     */
    public function analyze_site_package( $raw_json, $options = array() ) {
        $options = $this->normalize_analysis_options( $options );
        $package = $this->get_analysis_service()->parse_site_package( $raw_json );
        if ( is_wp_error( $package ) ) {
            return $package;
        }

        $reports                      = array();
        $prepared_pages               = array();
        $package_errors               = array();
        $package_warnings             = array();
        $package_style_warnings       = array();
        $package_security_warnings    = array();
        $page_key_index               = array();
        $page_slug_index              = array();
        $page_key_to_target           = array();
        $importable_page_count        = 0;
        $skipped_page_count           = 0;
        $blocked_page_count           = 0;
        $create_page_count            = 0;
        $duplicate_page_count         = 0;
        $update_page_count            = 0;
        $style_warning_page_count     = 0;
        $style_warning_total_count    = 0;
        $security_warning_page_count  = 0;
        $security_warning_total_count = 0;

        foreach ( $package['pages'] as $index => $page ) {
            $report = $this->get_analysis_service()->build_page_report(
                $page,
                $index,
                $page_key_index,
                $page_slug_index,
                array(
                    'package_id'        => $package['package_id'],
                    'conflict_strategy' => $options['conflict_strategy'],
                )
            );

            if ( ! empty( $report['prepared_page'] ) && is_array( $report['prepared_page'] ) ) {
                if ( in_array( $report['action'], array( 'create', 'duplicate', 'update' ), true ) ) {
                    $prepared_pages[] = $report['prepared_page'];
                    $importable_page_count++;

                    if ( 'create' === $report['action'] ) {
                        $create_page_count++;
                    } elseif ( 'duplicate' === $report['action'] ) {
                        $duplicate_page_count++;
                    } elseif ( 'update' === $report['action'] ) {
                        $update_page_count++;
                    }
                } elseif ( 'skip' === $report['action'] ) {
                    $skipped_page_count++;
                }

                if ( ! empty( $report['page_key'] ) ) {
                    $page_target = array(
                        'page_id' => isset( $report['existing_page_id'] ) ? absint( $report['existing_page_id'] ) : 0,
                        'action'  => $report['action'],
                    );

                    if ( ! empty( $report['target_slug'] ) ) {
                        $page_target['target_slug'] = sanitize_title( (string) $report['target_slug'] );
                    }

                    $page_key_to_target[ $report['page_key'] ] = $page_target;
                }
            } else {
                $blocked_page_count++;
            }

            if ( ! empty( $report['errors'] ) ) {
                foreach ( $report['errors'] as $message ) {
                    $package_errors[] = $message;
                }
            }

            if ( ! empty( $report['warnings'] ) ) {
                foreach ( $report['warnings'] as $message ) {
                    $package_warnings[] = $message;
                }
            }

            if ( ! empty( $report['style_warnings'] ) ) {
                $style_warning_page_count++;
                $style_warning_total_count += count( $report['style_warnings'] );
            }

            if ( ! empty( $report['security_warnings'] ) ) {
                $security_warning_page_count++;
                $security_warning_total_count += count( $report['security_warnings'] );
            }

            unset( $report['prepared_page'] );
            $reports[] = $report;
        }

        $raw_site_options = isset( $package['site_options'] ) && is_array( $package['site_options'] ) ? $package['site_options'] : array();
        if ( ! empty( $package['design_system_v2'] ) && is_array( $package['design_system_v2'] ) && empty( $raw_site_options['design_system_v2'] ) ) {
            $raw_site_options['design_system_v2'] = $package['design_system_v2'];
        }
        if ( ! empty( $package['design_system']['design_system_v2'] ) && is_array( $package['design_system']['design_system_v2'] ) && empty( $raw_site_options['design_system_v2'] ) ) {
            $raw_site_options['design_system_v2'] = $package['design_system']['design_system_v2'];
        }
        if ( ! empty( $package['design_system']['options'] ) && is_array( $package['design_system']['options'] ) && empty( $raw_site_options['design_options'] ) ) {
            $raw_site_options['design_options'] = $package['design_system']['options'];
        }
        if ( ! empty( $package['content_models']['options'] ) && is_array( $package['content_models']['options'] ) && empty( $raw_site_options['content_model_options'] ) ) {
            $raw_site_options['content_model_options'] = $package['content_models']['options'];
        }
        if ( ! empty( $package['navigation'] ) && is_array( $package['navigation'] ) && empty( $raw_site_options['navigation'] ) ) {
            $raw_site_options['navigation'] = $package['navigation'];
        }

        $site_options = $this->normalize_site_options( $raw_site_options );

        if ( ! empty( $site_options['front_page'] ) ) {
            $front_page_key = $site_options['front_page'];
            if ( ! isset( $page_key_to_target[ $front_page_key ] ) ) {
                $package_warnings[] = sprintf(
                    /* translators: %s: page key */
                    __( '站点设置里的首页引用 %s 在当前数据包中不存在，导入时将忽略首页设置。', 'developer-starter' ),
                    $front_page_key
                );
            }
        }

        if ( ! empty( $site_options['posts_page'] ) ) {
            $posts_page_key = $site_options['posts_page'];
            if ( ! isset( $page_key_to_target[ $posts_page_key ] ) ) {
                $package_warnings[] = sprintf(
                    /* translators: %s: page key */
                    __( '站点设置里的文章列表页引用 %s 在当前数据包中不存在，导入时将忽略文章列表页设置。', 'developer-starter' ),
                    $posts_page_key
                );
            }
        }

        $reference_warnings = $this->collect_package_reference_warnings( $prepared_pages, $page_key_to_target );
        if ( ! empty( $reference_warnings ) ) {
            foreach ( $reference_warnings as $reference_warning ) {
                $package_warnings[] = $reference_warning;
            }
        }

        $package_warnings = array_merge(
            $package_warnings,
            $this->get_diagnostics_service()->collect_package_dependency_warnings(
                isset( $package['dependencies'] ) && is_array( $package['dependencies'] ) ? $package['dependencies'] : array()
            )
        );
        $package_warnings = array_merge(
            $package_warnings,
            $this->get_diagnostics_service()->collect_package_manifest_warnings( $package, $site_options )
        );

        $package_style_warnings = $this->get_diagnostics_service()->collect_package_style_warnings( $package, $prepared_pages );
        $package_security_warnings = $this->get_diagnostics_service()->collect_package_security_warnings( $package, $prepared_pages );
        $package_warnings = array_values( array_unique( array_filter( array_map( 'strval', $package_warnings ) ) ) );
        $package_errors   = array_values( array_unique( array_filter( array_map( 'strval', $package_errors ) ) ) );
        $package_style_warnings = array_values( array_unique( array_filter( array_map( 'strval', $package_style_warnings ) ) ) );
        $package_security_warnings = array_values( array_unique( array_filter( array_map( 'strval', $package_security_warnings ) ) ) );

        return array(
            'package'          => $package,
            'prepared_package' => array(
                'scope'          => isset( $package['scope'] ) ? sanitize_key( (string) $package['scope'] ) : self::PACKAGE_SCOPE_PAGE,
                'manifest'       => isset( $package['manifest'] ) && is_array( $package['manifest'] ) ? $package['manifest'] : array(),
                'meta'           => array(
                    'title'             => $package['title'],
                    'package_id'        => $package['package_id'],
                    'version'           => $package['version'],
                    'scope'             => isset( $package['scope'] ) ? sanitize_key( (string) $package['scope'] ) : self::PACKAGE_SCOPE_PAGE,
                    'theme'             => $package['theme'],
                    'min_theme_version' => $package['min_theme_version'],
                    'author'            => $package['author'],
                    'description'       => $package['description'],
                    'cover'             => $package['cover'],
                    'categories'        => $package['categories'],
                    'tags'              => $package['tags'],
                    'dependencies'      => $package['dependencies'],
                    'conflict_strategy' => $options['conflict_strategy'],
                ),
                'pages'          => $prepared_pages,
                'existing_pages' => $page_key_to_target,
                'site_options'   => $site_options,
                'site_assets'    => isset( $package['site_assets'] ) && is_array( $package['site_assets'] ) ? $package['site_assets'] : array(),
            ),
            'pages'            => $reports,
            'errors'           => $package_errors,
            'warnings'         => $package_warnings,
            'style_warnings'   => $package_style_warnings,
            'security_warnings' => $package_security_warnings,
            'can_import'       => $importable_page_count > 0,
            'stats'            => array(
                'total_pages'      => count( $reports ),
                'importable_pages' => $importable_page_count,
                'ready_pages'      => $importable_page_count,
                'create_pages'     => $create_page_count,
                'duplicate_pages'  => $duplicate_page_count,
                'update_pages'     => $update_page_count,
                'skipped_pages'    => $skipped_page_count,
                'blocked_pages'    => $blocked_page_count,
                'style_warning_pages' => $style_warning_page_count,
                'style_warning_count' => $style_warning_total_count + count( $package_style_warnings ),
                'security_warning_pages' => $security_warning_page_count,
                'security_warning_count' => $security_warning_total_count + count( $package_security_warnings ),
                'site_setting_groups' => $this->count_site_setting_groups( $site_options ),
            ),
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
        return $this->get_import_execution_service()->import_site_package( $prepared_package, $options );
    }

    /**
     * 生成临时预览页面（草稿）。
     *
     * @param array<string,mixed> $prepared_package 经过预检的数据包。
     * @param array<string,mixed> $options          预览参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function create_site_package_preview( $prepared_package, $options = array() ) {
        return $this->get_preview_service()->create_site_package_preview( $prepared_package, $options );
    }

    /**
     * 获取可用的 URL 冲突处理策略。
     *
     * @return array<string,array<string,string>>
     */
    public function get_conflict_strategies() {
        return array(
            self::CONFLICT_STRATEGY_SKIP      => array(
                'label'       => __( '跳过已存在页面', 'developer-starter' ),
                'description' => __( '如果 URL 已存在，就跳过该页，不覆盖原页面。', 'developer-starter' ),
            ),
            self::CONFLICT_STRATEGY_DUPLICATE => array(
                'label'       => __( '自动生成新 URL', 'developer-starter' ),
                'description' => __( '如果 URL 已存在，就自动改成新 URL 创建副本。', 'developer-starter' ),
            ),
            self::CONFLICT_STRATEGY_UPDATE    => array(
                'label'       => __( '仅更新同一数据包历史页面', 'developer-starter' ),
                'description' => __( '只有匹配同一数据包 ID 和页面标识的旧页面才会更新，其余一律跳过。', 'developer-starter' ),
            ),
        );
    }

    /**
     * 获取本地包作用域选项。
     *
     * @return array<string,array<string,string>>
     */
    public function get_package_scope_choices() {
        return array(
            self::PACKAGE_SCOPE_PAGE => array(
                'label'       => __( '页面包', 'developer-starter' ),
                'description' => __( '用于单页或多页面装修复用，只导出页面模块、页面设置和页面互链。', 'developer-starter' ),
            ),
            self::PACKAGE_SCOPE_SITE => array(
                'label'       => __( '整站包', 'developer-starter' ),
                'description' => __( '用于本地整站迁移，可额外携带全局样式、内容模型、菜单和站点身份设置。', 'developer-starter' ),
            ),
        );
    }

    /**
     * 获取可导出的装修页面列表。
     *
     * @return array<int,array<string,mixed>>
     */
    public function get_exportable_pages() {
        return $this->get_export_service()->get_exportable_pages();
    }

    /**
     * 清理当前用户的临时预览页面。
     *
     * @param int    $user_id    用户 ID。
     * @param string $package_id 包 ID（可选，空表示清理该用户全部预览）。
     * @return int
     */
    public function cleanup_user_preview_pages( $user_id, $package_id = '' ) {
        return $this->get_preview_service()->cleanup_user_preview_pages( $user_id, $package_id );
    }

    /**
     * 清理过期临时预览页面。
     *
     * @return int
     */
    public function cleanup_expired_preview_pages() {
        return $this->get_preview_service()->cleanup_expired_preview_pages();
    }

    /**
     * 获取导入历史（最新在前）。
     *
     * @param int $limit 条数上限。
     * @return array<int,array<string,mixed>>
     */
    public function get_import_history( $limit = 20 ) {
        return $this->get_import_state_service()->get_import_history( $limit );
    }

    /**
     * 回滚指定导入记录。
     *
     * @param string $run_id 导入记录 ID。
     * @return array<string,mixed>|\WP_Error
     */
    public function rollback_import_history( $run_id ) {
        return $this->get_import_state_service()->rollback_import_history( $run_id );
    }

    /**
     * 获取导入任务锁。
     *
     * @param int $user_id 用户 ID。
     * @return bool
     */
    public function acquire_import_lock( $user_id = 0 ) {
        return $this->get_import_state_service()->acquire_import_lock( $user_id );
    }

    /**
     * 释放导入任务锁。
     *
     * @param int $user_id 用户 ID。
     * @return void
     */
    public function release_import_lock( $user_id = 0 ) {
        $this->get_import_state_service()->release_import_lock( $user_id );
    }

    /**
     * 导出多页面数据包。
     *
     * @param array<int,mixed>     $page_ids 页面 ID 列表。
     * @param array<string,mixed>  $options  导出配置。
     * @return array<string,mixed>|\WP_Error
     */
    public function export_site_package( $page_ids, $options = array() ) {
        return $this->get_export_service()->export_site_package( $page_ids, $options );
    }

    /**
     * 获取系统保留页面说明。
     *
     * @return array<int,array<string,string>>
     */
    public function get_reserved_page_definitions() {
        $definitions = array(
            array(
                'slug'     => 'login',
                'template' => 'templates/template-login.php',
                'label'    => __( '用户登录', 'developer-starter' ),
            ),
            array(
                'slug'     => 'register',
                'template' => 'templates/template-register.php',
                'label'    => __( '用户注册', 'developer-starter' ),
            ),
            array(
                'slug'     => 'forgot-password',
                'template' => 'templates/template-forgot-password.php',
                'label'    => __( '找回密码', 'developer-starter' ),
            ),
            array(
                'slug'     => 'account-center',
                'template' => 'templates/template-account.php',
                'label'    => __( '个人中心', 'developer-starter' ),
            ),
        );

        $option_map = array(
            'login_page_id'                => 'login',
            'register_page_id'             => 'register',
            'forgot_password_page_id'      => 'forgot-password',
            'developer_starter_account_page_id' => 'account-center',
        );

        $theme_options = get_option( 'developer_starter_options', array() );
        foreach ( $option_map as $option_key => $definition_slug ) {
            $page_id = 0;
            if ( $option_key === 'developer_starter_account_page_id' ) {
                $page_id = absint( get_option( $option_key, 0 ) );
            } elseif ( isset( $theme_options[ $option_key ] ) ) {
                $page_id = absint( $theme_options[ $option_key ] );
            }

            foreach ( $definitions as $index => $definition ) {
                if ( $definition['slug'] !== $definition_slug ) {
                    continue;
                }

                if ( $page_id <= 0 && ! empty( $definition['template'] ) ) {
                    $page_id = $this->get_page_service()->find_page_id_by_template( $definition['template'] );
                }

                if ( $page_id <= 0 ) {
                    break;
                }

                $post = get_post( $page_id );
                if ( ! $post instanceof \WP_Post || $post->post_type !== 'page' ) {
                    break;
                }

                $definitions[ $index ]['slug'] = $post->post_name;
                $definitions[ $index ]['page_id'] = (string) $post->ID;
                break;
            }
        }

        return $definitions;
    }

    /**
     * 规范化预检参数。
     *
     * @param array<string,mixed> $options 原始参数。
     * @return array<string,string>
     */
    private function normalize_analysis_options( $options ) {
        return array(
            'conflict_strategy' => $this->normalize_conflict_strategy(
                isset( $options['conflict_strategy'] ) ? $options['conflict_strategy'] : self::CONFLICT_STRATEGY_SKIP
            ),
        );
    }

    /**
     * 统计整站设置分组数量。
     *
     * @param array<string,mixed> $site_options 站点设置。
     * @return int
     */
    private function count_site_setting_groups( $site_options ) {
        if ( ! is_array( $site_options ) || empty( $site_options ) ) {
            return 0;
        }

        $count = 0;
        foreach ( array( 'front_page', 'posts_page', 'site_title', 'tagline', 'design_options', 'content_model_options', 'navigation' ) as $key ) {
            if ( ! empty( $site_options[ $key ] ) || ( 'tagline' === $key && array_key_exists( $key, $site_options ) ) ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 规范化 URL 冲突策略。
     *
     * @param mixed $strategy 原始策略。
     * @return string
     */
    private function normalize_conflict_strategy( $strategy ) {
        $strategy = sanitize_key( is_scalar( $strategy ) ? (string) $strategy : '' );
        if ( ! in_array( $strategy, array( self::CONFLICT_STRATEGY_SKIP, self::CONFLICT_STRATEGY_DUPLICATE, self::CONFLICT_STRATEGY_UPDATE ), true ) ) {
            $strategy = self::CONFLICT_STRATEGY_SKIP;
        }

        return $strategy;
    }

    /**
     * 获取页面的模块数据，仅接受启灵主题自己的装修模块。
     *
     * @param int $page_id 页面 ID。
     * @return array<int,array<string,mixed>>
     */
    private function get_page_modules_for_package( $page_id ) {
        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $page_id )
            : get_post_meta( $page_id, '_developer_starter_modules', true );

        if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
            $modules = developer_starter_normalize_modules_meta_types( $modules );
        }

        return is_array( $modules ) ? $modules : array();
    }

    /**
     * 为副本或预览页面生成安全且唯一的 slug。
     *
     * @param string $slug 原始 slug。
     * @return string
     */
    private function generate_unique_page_slug( $slug ) {
        $slug = sanitize_title( (string) $slug );
        if ( '' === $slug ) {
            return '';
        }

        if ( function_exists( 'wp_unique_post_slug' ) ) {
            return wp_unique_post_slug( $slug, 0, 'publish', 'page', 0 );
        }

        $unique_slug = $slug;
        $suffix      = 2;
        while ( get_page_by_path( $unique_slug, OBJECT, 'page' ) instanceof \WP_Post ) {
            $unique_slug = sanitize_title( $slug . '-' . $suffix );
            $suffix++;
        }

        return $unique_slug;
    }

    /**
     * 应用页面级设置。
     *
     * @param int                $post_id   页面 ID。
     * @param array<string,mixed> $settings 页面设置。
     * @return void
     */
    private function apply_page_settings( $post_id, $settings ) {
        $this->get_page_service()->apply_page_settings( $post_id, $settings );
    }

    /**
     * 规范化站点设置。
     *
     * @param array<string,mixed> $site_options 原始站点设置。
     * @return array<string,string>
     */
    private function normalize_site_options( $site_options ) {
        return $this->get_page_service()->normalize_site_options( $site_options );
    }

    /**
     * 规范化 bool 值。
     *
     * @param mixed $value   原值。
     * @param bool  $default 默认值。
     * @return bool
     */
    private function normalize_bool_value( $value, $default = false ) {
        return $this->get_page_service()->normalize_bool_value( $value, $default );
    }

    /**
     * 规范化模板值。
     *
     * @param mixed $template 原模板值。
     * @return string
     */
    private function normalize_page_template( $template ) {
        return $this->get_page_service()->normalize_page_template( $template );
    }

    /**
     * 收集多页包里的占位链接预警。
     *
     * @param array<int,array<string,mixed>> $prepared_pages     可导入页面列表。
     * @param array<string,array<string,mixed>> $page_key_to_target 当前可识别的页面 key 集合。
     * @return array<int,string>
     */
    private function collect_package_reference_warnings( $prepared_pages, $page_key_to_target ) {
        return $this->get_page_service()->collect_package_reference_warnings(
            $prepared_pages,
            $page_key_to_target,
            $this->get_reserved_page_definitions()
        );
    }

}
