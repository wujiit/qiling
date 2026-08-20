<?php
/**
 * 页面数据包导入状态服务
 *
 * 负责导入锁、导入前快照、导入历史和回滚。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Import_State_Service {

    /**
     * 导入任务锁定时长（秒）。
     *
     * @var int
     */
    private $import_lock_ttl = 120;

    /**
     * 导入历史 option 键。
     *
     * @var string
     */
    private $import_history_option = 'developer_starter_site_package_import_history';

    /**
     * 最多保留多少条导入历史。
     *
     * @var int
     */
    private $import_history_max = 30;

    /**
     * 依赖回调。
     *
     * @var array<string,callable>
     */
    private $callbacks = array();

    /**
     * @param array<string,mixed> $args 配置参数。
     */
    public function __construct( $args = array() ) {
        $args = is_array( $args ) ? $args : array();

        if ( isset( $args['import_lock_ttl'] ) ) {
            $this->import_lock_ttl = max( 1, absint( $args['import_lock_ttl'] ) );
        }

        if ( ! empty( $args['import_history_option'] ) ) {
            $this->import_history_option = sanitize_key( (string) $args['import_history_option'] );
        }

        if ( isset( $args['import_history_max'] ) ) {
            $this->import_history_max = max( 1, absint( $args['import_history_max'] ) );
        }

        if ( isset( $args['callbacks'] ) && is_array( $args['callbacks'] ) ) {
            $this->callbacks = $args['callbacks'];
        }
    }

    /**
     * 获取导入历史。
     *
     * @param int $limit 记录数。
     * @return array<int,array<string,mixed>>
     */
    public function get_import_history( $limit = 20 ) {
        $records = $this->get_import_history_records();
        if ( $limit > 0 ) {
            $records = array_slice( $records, 0, absint( $limit ) );
        }

        return $records;
    }

    /**
     * 回滚指定导入记录。
     *
     * @param string $run_id 导入记录 ID。
     * @return array<string,mixed>|\WP_Error
     */
    public function rollback_import_history( $run_id ) {
        $run_id = sanitize_text_field( (string) $run_id );
        if ( '' === $run_id ) {
            return new \WP_Error( 'invalid_run_id', __( '回滚失败：缺少导入记录 ID。', 'developer-starter' ) );
        }

        $records = $this->get_import_history_records();
        $index   = -1;
        foreach ( $records as $i => $record ) {
            if ( isset( $record['run_id'] ) && (string) $record['run_id'] === $run_id ) {
                $index = (int) $i;
                break;
            }
        }

        if ( $index < 0 || ! isset( $records[ $index ] ) || ! is_array( $records[ $index ] ) ) {
            return new \WP_Error( 'run_not_found', __( '回滚失败：找不到对应的导入记录。', 'developer-starter' ) );
        }

        $record = $records[ $index ];
        if ( ! empty( $record['rolled_back_at'] ) ) {
            return new \WP_Error( 'already_rolled_back', __( '该导入记录已经执行过回滚。', 'developer-starter' ) );
        }

        $snapshot = isset( $record['snapshot'] ) && is_array( $record['snapshot'] ) ? $record['snapshot'] : array();
        if ( empty( $snapshot ) ) {
            return new \WP_Error( 'missing_snapshot', __( '回滚失败：该记录没有可用快照。', 'developer-starter' ) );
        }

        $restored_pages = 0;
        $deleted_pages  = 0;
        $errors         = array();

        $updated_pages = isset( $snapshot['updated_pages'] ) && is_array( $snapshot['updated_pages'] ) ? $snapshot['updated_pages'] : array();
        foreach ( $updated_pages as $page_snapshot ) {
            if ( ! is_array( $page_snapshot ) || empty( $page_snapshot['page_id'] ) ) {
                continue;
            }

            $page_id = absint( $page_snapshot['page_id'] );
            if ( $page_id <= 0 ) {
                continue;
            }

            $restore = $this->restore_page_from_snapshot( $page_snapshot );
            if ( is_wp_error( $restore ) ) {
                $errors[] = $restore->get_error_message();
                continue;
            }

            $restored_pages++;
        }

        $created_page_ids = isset( $snapshot['created_page_ids'] ) && is_array( $snapshot['created_page_ids'] ) ? $snapshot['created_page_ids'] : array();
        foreach ( $created_page_ids as $created_page_id ) {
            $created_page_id = absint( $created_page_id );
            if ( $created_page_id <= 0 ) {
                continue;
            }

            $post = get_post( $created_page_id );
            if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
                continue;
            }

            if ( wp_delete_post( $created_page_id, true ) ) {
                $deleted_pages++;
            } else {
                $errors[] = sprintf(
                    /* translators: %d: page id */
                    __( '页面 #%d 删除失败，请手动检查。', 'developer-starter' ),
                    $created_page_id
                );
            }
        }

        $site_options_before = isset( $snapshot['site_options_before'] ) && is_array( $snapshot['site_options_before'] ) ? $snapshot['site_options_before'] : array();
        if ( ! empty( $site_options_before ) ) {
            if ( isset( $site_options_before['show_on_front'] ) ) {
                update_option( 'show_on_front', sanitize_text_field( (string) $site_options_before['show_on_front'] ) );
            }
            if ( array_key_exists( 'page_on_front', $site_options_before ) ) {
                update_option( 'page_on_front', absint( $site_options_before['page_on_front'] ) );
            }
            if ( array_key_exists( 'page_for_posts', $site_options_before ) ) {
                update_option( 'page_for_posts', absint( $site_options_before['page_for_posts'] ) );
            }
            if ( array_key_exists( 'blogname', $site_options_before ) ) {
                update_option( 'blogname', sanitize_text_field( (string) $site_options_before['blogname'] ) );
            }
            if ( array_key_exists( 'blogdescription', $site_options_before ) ) {
                update_option( 'blogdescription', sanitize_text_field( (string) $site_options_before['blogdescription'] ) );
            }
            if ( array_key_exists( 'developer_starter_options', $site_options_before ) && is_array( $site_options_before['developer_starter_options'] ) ) {
                update_option( 'developer_starter_options', $site_options_before['developer_starter_options'] );
            }
            if ( array_key_exists( 'nav_menu_locations', $site_options_before ) && is_array( $site_options_before['nav_menu_locations'] ) ) {
                set_theme_mod( 'nav_menu_locations', $site_options_before['nav_menu_locations'] );
            }
        }

        $record['rolled_back_at']  = time();
        $record['rollback_result'] = array(
            'restored_pages' => $restored_pages,
            'deleted_pages'  => $deleted_pages,
            'error_count'    => count( $errors ),
            'errors'         => $errors,
        );

        $records[ $index ] = $record;
        $this->save_import_history_records( $records );

        return array(
            'run_id'         => $run_id,
            'restored_pages' => $restored_pages,
            'deleted_pages'  => $deleted_pages,
            'error_count'    => count( $errors ),
            'errors'         => $errors,
            'rolled_back_at' => $record['rolled_back_at'],
        );
    }

    /**
     * 获取导入任务锁。
     *
     * @param int $user_id 用户 ID。
     * @return bool
     */
    public function acquire_import_lock( $user_id = 0 ) {
        $lock_key = $this->get_import_lock_key( $user_id );
        if ( '' === $lock_key ) {
            return false;
        }

        if ( get_transient( $lock_key ) ) {
            return false;
        }

        set_transient( $lock_key, '1', $this->import_lock_ttl );
        return true;
    }

    /**
     * 释放导入任务锁。
     *
     * @param int $user_id 用户 ID。
     * @return void
     */
    public function release_import_lock( $user_id = 0 ) {
        $lock_key = $this->get_import_lock_key( $user_id );
        if ( '' === $lock_key ) {
            return;
        }

        delete_transient( $lock_key );
    }

    /**
     * 采集本次导入前快照。
     *
     * @param array<string,mixed> $prepared_package 预检后数据包。
     * @return array<string,mixed>
     */
    public function capture_import_snapshot( $prepared_package ) {
        $snapshot = array(
            'site_options_before' => array(
                'show_on_front'            => get_option( 'show_on_front', 'posts' ),
                'page_on_front'            => absint( get_option( 'page_on_front', 0 ) ),
                'page_for_posts'           => absint( get_option( 'page_for_posts', 0 ) ),
                'blogname'                 => get_option( 'blogname', '' ),
                'blogdescription'          => get_option( 'blogdescription', '' ),
                'developer_starter_options' => get_option( 'developer_starter_options', array() ),
                'nav_menu_locations'       => get_theme_mod( 'nav_menu_locations', array() ),
            ),
            'updated_pages'      => array(),
            'created_page_ids'   => array(),
        );

        if ( empty( $prepared_package['pages'] ) || ! is_array( $prepared_package['pages'] ) ) {
            return $snapshot;
        }

        foreach ( $prepared_package['pages'] as $page ) {
            if ( ! is_array( $page ) ) {
                continue;
            }

            $import_action = isset( $page['import_action'] ) ? sanitize_key( (string) $page['import_action'] ) : '';
            if ( 'update' !== $import_action ) {
                continue;
            }

            $existing_page_id = isset( $page['existing_page_id'] ) ? absint( $page['existing_page_id'] ) : 0;
            if ( $existing_page_id <= 0 ) {
                continue;
            }

            $page_snapshot = $this->capture_page_snapshot( $existing_page_id );
            if ( empty( $page_snapshot ) ) {
                continue;
            }

            $snapshot['updated_pages'][] = $page_snapshot;
        }

        return $snapshot;
    }

    /**
     * 写入一条导入历史。
     *
     * @param array<string,mixed> $record 导入记录。
     * @return void
     */
    public function append_import_history_record( $record ) {
        $records = $this->get_import_history_records();
        array_unshift( $records, $record );
        if ( count( $records ) > $this->import_history_max ) {
            $records = array_slice( $records, 0, $this->import_history_max );
        }
        $this->save_import_history_records( $records );
    }

    /**
     * 生成导入任务锁键名。
     *
     * @param int $user_id 用户 ID。
     * @return string
     */
    private function get_import_lock_key( $user_id ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            $user_id = get_current_user_id() ? absint( get_current_user_id() ) : 1;
        }

        return 'ds_site_package_import_lock_' . $user_id;
    }

    /**
     * 采集单个页面快照。
     *
     * @param int $page_id 页面 ID。
     * @return array<string,mixed>
     */
    private function capture_page_snapshot( $page_id ) {
        $page_id = absint( $page_id );
        if ( $page_id <= 0 ) {
            return array();
        }

        $post = get_post( $page_id );
        if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
            return array();
        }

        $template = get_post_meta( $page_id, '_wp_page_template', true );
        if ( ! is_string( $template ) || '' === trim( $template ) ) {
            $template = 'default';
        }

        $normalized_template = $this->maybe_call( 'normalize_page_template', $template );
        if ( ! is_string( $normalized_template ) || '' === trim( $normalized_template ) ) {
            $normalized_template = 'default';
        }

        return array(
            'page_id'  => $page_id,
            'title'    => (string) $post->post_title,
            'slug'     => (string) $post->post_name,
            'status'   => (string) $post->post_status,
            'template' => $normalized_template,
            'package_meta' => array(
                '_qiling_site_package_id'       => (string) get_post_meta( $page_id, '_qiling_site_package_id', true ),
                '_qiling_site_package_page_key' => (string) get_post_meta( $page_id, '_qiling_site_package_page_key', true ),
                '_qiling_site_package_version'  => (string) get_post_meta( $page_id, '_qiling_site_package_version', true ),
                '_qiling_site_package_title'    => (string) get_post_meta( $page_id, '_qiling_site_package_title', true ),
            ),
            'settings' => array(
                'hide_page_header'     => (bool) $this->maybe_call( 'normalize_bool_value', get_post_meta( $page_id, '_qiling_hide_page_header', true ), false ),
                'transparent_header'   => (bool) $this->maybe_call( 'normalize_bool_value', get_post_meta( $page_id, '_qiling_transparent_header', true ), false ),
                'enable_scroll_reveal' => (bool) $this->maybe_call( 'normalize_bool_value', get_post_meta( $page_id, '_developer_starter_enable_scroll_reveal', true ), false ),
                'page_design'          => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? Design_Tokens::get_page_design_overrides( $page_id, 'package' )
                    : array(),
            ),
            'modules'  => $this->get_page_modules_for_package( $page_id ),
        );
    }

    /**
     * 从快照恢复页面数据。
     *
     * @param array<string,mixed> $page_snapshot 页面快照。
     * @return true|\WP_Error
     */
    private function restore_page_from_snapshot( $page_snapshot ) {
        $page_id = isset( $page_snapshot['page_id'] ) ? absint( $page_snapshot['page_id'] ) : 0;
        if ( $page_id <= 0 ) {
            return new \WP_Error( 'invalid_snapshot_page', __( '页面快照无效，缺少页面 ID。', 'developer-starter' ) );
        }

        $post = get_post( $page_id );
        if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
            return new \WP_Error(
                'missing_snapshot_page',
                sprintf(
                    /* translators: %d: page id */
                    __( '回滚失败：页面 #%d 已不存在。', 'developer-starter' ),
                    $page_id
                )
            );
        }

        $update_result = wp_update_post(
            array(
                'ID'          => $page_id,
                'post_title'  => isset( $page_snapshot['title'] ) ? sanitize_text_field( (string) $page_snapshot['title'] ) : $post->post_title,
                'post_name'   => isset( $page_snapshot['slug'] ) ? sanitize_title( (string) $page_snapshot['slug'] ) : $post->post_name,
                'post_status' => isset( $page_snapshot['status'] ) ? sanitize_key( (string) $page_snapshot['status'] ) : $post->post_status,
            ),
            true
        );
        if ( is_wp_error( $update_result ) ) {
            return $update_result;
        }

        $template = isset( $page_snapshot['template'] ) ? (string) $page_snapshot['template'] : 'default';
        $template = $this->maybe_call( 'normalize_page_template', $template );
        if ( ! is_string( $template ) || '' === trim( $template ) ) {
            $template = 'default';
        }
        update_post_meta( $page_id, '_wp_page_template', $template );

        $settings = isset( $page_snapshot['settings'] ) && is_array( $page_snapshot['settings'] ) ? $page_snapshot['settings'] : array();
        $this->maybe_call( 'apply_page_settings', $page_id, $settings );

        $modules = isset( $page_snapshot['modules'] ) && is_array( $page_snapshot['modules'] ) ? $page_snapshot['modules'] : array();
        update_post_meta( $page_id, '_developer_starter_modules', $modules );

        $package_meta = isset( $page_snapshot['package_meta'] ) && is_array( $page_snapshot['package_meta'] ) ? $page_snapshot['package_meta'] : array();
        $package_meta_keys = array(
            '_qiling_site_package_id',
            '_qiling_site_package_page_key',
            '_qiling_site_package_version',
            '_qiling_site_package_title',
        );
        foreach ( $package_meta_keys as $meta_key ) {
            $meta_value = isset( $package_meta[ $meta_key ] ) ? (string) $package_meta[ $meta_key ] : '';
            if ( '' === $meta_value ) {
                delete_post_meta( $page_id, $meta_key );
            } else {
                update_post_meta( $page_id, $meta_key, sanitize_text_field( $meta_value ) );
            }
        }

        return true;
    }

    /**
     * 读取导入历史列表。
     *
     * @return array<int,array<string,mixed>>
     */
    private function get_import_history_records() {
        $records = get_option( $this->import_history_option, array() );
        if ( ! is_array( $records ) ) {
            return array();
        }

        $records = array_values(
            array_filter(
                $records,
                static function ( $record ) {
                    return is_array( $record ) && ! empty( $record['run_id'] );
                }
            )
        );

        usort(
            $records,
            static function ( $left, $right ) {
                $left_time  = isset( $left['created_at'] ) ? absint( $left['created_at'] ) : 0;
                $right_time = isset( $right['created_at'] ) ? absint( $right['created_at'] ) : 0;
                return $right_time <=> $left_time;
            }
        );

        return $records;
    }

    /**
     * 保存导入历史列表。
     *
     * @param array<int,array<string,mixed>> $records 导入记录。
     * @return void
     */
    private function save_import_history_records( $records ) {
        update_option( $this->import_history_option, array_values( $records ), false );
    }

    /**
     * 获取页面模块快照。
     *
     * @param int $page_id 页面 ID。
     * @return array<int,array<string,mixed>>
     */
    private function get_page_modules_for_package( $page_id ) {
        $modules = $this->maybe_call( 'get_page_modules_for_package', $page_id );
        return is_array( $modules ) ? $modules : array();
    }

    /**
     * 调用依赖回调。
     *
     * @param string $name 回调名。
     * @return mixed
     */
    private function maybe_call( $name ) {
        if ( empty( $this->callbacks[ $name ] ) || ! is_callable( $this->callbacks[ $name ] ) ) {
            return null;
        }

        $args = func_get_args();
        array_shift( $args );

        return call_user_func_array( $this->callbacks[ $name ], $args );
    }
}
