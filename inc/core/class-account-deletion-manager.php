<?php
/**
 * Account Deletion Request Manager - 用户账号注销申请管理
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Account_Deletion_Manager {

    const PAGE_SLUG  = 'developer-starter-account-deletion-requests';
    const DB_VERSION = '1.0.0';
    const DB_VERSION_OPTION = 'qiling_account_deletion_request_db_version';
    const DB_MIGRATION_LOCK = 'qiling_account_deletion_request_db_migration_lock';

    private static $table_exists_cache = null;
    private $table_name;

    public function __construct() {
        $this->table_name = self::table_name();

        add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );
        add_action( 'admin_init', array( $this, 'maybe_create_table' ) );
        add_action( 'template_redirect', array( $this, 'maybe_handle_account_submission' ), 0 );

        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 28 );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        }
    }

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'qiling_account_deletion_requests';
    }

    public static function table_exists( $refresh = false ) {
        if ( ! $refresh && self::$table_exists_cache !== null ) {
            return self::$table_exists_cache;
        }

        global $wpdb;
        $table_name = self::table_name();
        $exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
        self::$table_exists_cache = ( $exists === $table_name );

        return self::$table_exists_cache;
    }

    private function get_option( $key, $default = '' ) {
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return developer_starter_get_option( $key, $default );
        }
        return $default;
    }

    public function is_enabled() {
        return $this->get_option( 'account_deletion_request_enable', '' ) === '1';
    }

    public function get_agreement_content() {
        $agreement = (string) $this->get_option( 'account_deletion_request_agreement', '' );
        if ( trim( $agreement ) === '' ) {
            $agreement = __( '提交注销申请后，账号不会立即删除。管理员将在后台审核后人工处理删除。请确认已备份个人数据。', 'developer-starter' );
        }
        return $agreement;
    }

    public function install_table() {
        $this->run_table_migration( true );
    }

    public function maybe_create_table() {
        if ( ! Database_Schema_Migration_Service::can_run_admin_migration() ) {
            return;
        }

        $this->run_table_migration();
    }

    private function run_table_migration( $force = false ) {
        Database_Schema_Migration_Service::run(
            array(
                'version_option'              => self::DB_VERSION_OPTION,
                'target_version'              => self::DB_VERSION,
                'lock_option'                 => self::DB_MIGRATION_LOCK,
                'force'                       => $force,
                'migration_callback'          => array( __CLASS__, 'create_table' ),
                'can_update_version_callback' => function () {
                    return self::table_exists( true );
                },
            )
        );
    }

    public static function create_table() {
        Database_Schema_Migration_Service::apply_schema( self::get_table_schema() );
        self::$table_exists_cache = null;
    }

    private static function get_table_schema() {
        global $wpdb;
        $table_name       = self::table_name();
        $charset_collate  = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            username_snapshot VARCHAR(60) NOT NULL DEFAULT '',
            email_snapshot VARCHAR(190) NOT NULL DEFAULT '',
            display_name_snapshot VARCHAR(120) NOT NULL DEFAULT '',
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            agreement_snapshot LONGTEXT NULL,
            request_ip VARCHAR(45) DEFAULT '',
            user_agent VARCHAR(255) DEFAULT '',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            reviewed_by BIGINT(20) UNSIGNED DEFAULT 0,
            reviewed_note TEXT NULL,
            reviewed_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";
    }

    private function redirect_to_security_tab( $result_code ) {
        $page_id = get_queried_object_id();
        $url     = $page_id ? get_permalink( $page_id ) : home_url( '/' );
        $url     = add_query_arg(
            array(
                'tab'            => 'security',
                'account_delete' => sanitize_key( (string) $result_code ),
            ),
            $url
        );
        wp_safe_redirect( $url );
        exit;
    }

    public function maybe_handle_account_submission() {
        if ( is_admin() || ! is_user_logged_in() ) {
            return;
        }

        if ( ! is_page_template( 'templates/template-account.php' ) ) {
            return;
        }

        $request_method = isset( $_SERVER['REQUEST_METHOD'] )
            ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) )
            : 'GET';
        if ( 'POST' !== $request_method ) {
            return;
        }

        $action = isset( $_POST['account_action'] ) ? sanitize_key( wp_unslash( (string) $_POST['account_action'] ) ) : '';
        if ( $action !== 'request_account_deletion' ) {
            return;
        }

        $nonce = isset( $_POST['account_nonce'] ) ? wp_unslash( (string) $_POST['account_nonce'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'developer_starter_account' ) ) {
            $this->redirect_to_security_tab( 'nonce' );
        }

        if ( ! $this->is_enabled() ) {
            $this->redirect_to_security_tab( 'disabled' );
        }

        $agree = isset( $_POST['account_delete_agree'] ) ? wp_unslash( (string) $_POST['account_delete_agree'] ) : '';
        if ( $agree !== '1' ) {
            $this->redirect_to_security_tab( 'agreement' );
        }

        $user_id = get_current_user_id();
        if ( self::has_pending_request( $user_id ) ) {
            $this->redirect_to_security_tab( 'exists' );
        }

        if ( ! self::table_exists() ) {
            self::create_table();
        }
        if ( ! self::table_exists( true ) ) {
            $this->redirect_to_security_tab( 'error' );
        }

        $current_user = wp_get_current_user();
        global $wpdb;

        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'user_id'               => $user_id,
                'username_snapshot'     => (string) $current_user->user_login,
                'email_snapshot'        => (string) $current_user->user_email,
                'display_name_snapshot' => (string) $current_user->display_name,
                'status'                => 'pending',
                'agreement_snapshot'    => $this->get_agreement_content(),
                'request_ip'            => developer_starter_get_client_ip(),
                'user_agent'            => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '',
                'created_at'            => current_time( 'mysql' ),
                'updated_at'            => current_time( 'mysql' ),
                'reviewed_by'           => 0,
                'reviewed_note'         => '',
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        if ( $inserted === false ) {
            $this->redirect_to_security_tab( 'error' );
        }

        /**
         * Fires after a user account deletion request has been stored.
         *
         * @param int      $request_id Request row ID.
         * @param int      $user_id    User ID.
         * @param \WP_User $user       Current user snapshot.
         * @param string   $ip_address Request IP address.
         */
        do_action( 'qiling_account_deletion_requested', (int) $wpdb->insert_id, $user_id, $current_user, developer_starter_get_client_ip() );

        $this->redirect_to_security_tab( 'success' );
    }

    public static function has_pending_request( $user_id ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return false;
        }

        if ( ! self::table_exists() ) {
            return false;
        }

        global $wpdb;
        $table_name = self::table_name();
        $count      = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND status IN ('pending','approved')",
                $user_id
            )
        );

        return $count > 0;
    }

    public static function get_latest_request_for_user( $user_id ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return null;
        }

        if ( ! self::table_exists() ) {
            return null;
        }

        global $wpdb;
        $table_name = self::table_name();
        $record     = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY id DESC LIMIT 1",
                $user_id
            )
        );

        return $record;
    }

    public static function get_status_label( $status ) {
        $status = sanitize_key( (string) $status );
        $map    = array(
            'pending'   => __( '待审核', 'developer-starter' ),
            'approved'  => __( '审核通过', 'developer-starter' ),
            'rejected'  => __( '已驳回', 'developer-starter' ),
            'processed' => __( '已处理', 'developer-starter' ),
        );

        return isset( $map[ $status ] ) ? $map[ $status ] : __( '未知状态', 'developer-starter' );
    }

    public static function count_requests_by_status( $status = '' ) {
        if ( ! self::table_exists() ) {
            return 0;
        }

        global $wpdb;
        $table_name = self::table_name();
        $status     = sanitize_key( (string) $status );

        if ( $status === '' ) {
            return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE status = %s",
                $status
            )
        );
    }

    public function add_admin_menu() {
        if ( ! $this->is_enabled() && self::count_requests_by_status() <= 0 ) {
            return;
        }

        $pending_count = self::count_requests_by_status( 'pending' );
        $menu_title    = __( '账号注销申请', 'developer-starter' );
        if ( $pending_count > 0 ) {
            $menu_title .= ' <span class="awaiting-mod count-' . $pending_count . '"><span class="pending-count">' . $pending_count . '</span></span>';
        }

        add_submenu_page(
            'developer-starter-settings',
            __( '账号注销申请', 'developer-starter' ),
            $menu_title,
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_admin_page' )
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( (string) $hook, self::PAGE_SLUG ) === false ) {
            return;
        }

        $version = defined( 'DEVELOPER_STARTER_VERSION' ) ? DEVELOPER_STARTER_VERSION : '1.0.0';
        if ( function_exists( 'developer_starter_get_assets_version' ) ) {
            $version = (string) developer_starter_get_assets_version();
        }

        wp_enqueue_style(
            'developer-starter-account-deletion-admin',
            DEVELOPER_STARTER_ASSETS . '/css/admin-account-deletion.css',
            array( 'developer-starter-admin' ),
            $version
        );
    }

    private function maybe_handle_admin_actions() {
        $action = isset( $_GET['ds_account_deletion_action'] ) ? sanitize_key( wp_unslash( (string) $_GET['ds_account_deletion_action'] ) ) : '';
        $id     = isset( $_GET['rid'] ) ? absint( wp_unslash( $_GET['rid'] ) ) : 0;
        if ( $action === '' || $id <= 0 ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '您没有权限执行该操作。', 'developer-starter' ) );
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? wp_unslash( (string) $_GET['_wpnonce'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'ds_account_deletion_action_' . $id ) ) {
            $this->redirect_admin_with_notice( 'nonce_error', false );
        }

        global $wpdb;
        $ok = false;

        if ( $action === 'delete' ) {
            $ok = $wpdb->delete( $this->table_name, array( 'id' => $id ), array( '%d' ) ) !== false;
            $this->redirect_admin_with_notice( 'deleted', $ok );
        }

        $reviewed_by = get_current_user_id();
        $now         = current_time( 'mysql' );

        if ( $action === 'mark_processed' ) {
            $ok = $wpdb->update(
                $this->table_name,
                array(
                    'status'      => 'processed',
                    'updated_at'  => $now,
                    'reviewed_by' => $reviewed_by,
                    'reviewed_at' => $now,
                ),
                array( 'id' => $id ),
                array( '%s', '%s', '%d', '%s' ),
                array( '%d' )
            ) !== false;
            $this->redirect_admin_with_notice( 'updated', $ok );
        }

        if ( $action === 'mark_rejected' ) {
            $ok = $wpdb->update(
                $this->table_name,
                array(
                    'status'      => 'rejected',
                    'updated_at'  => $now,
                    'reviewed_by' => $reviewed_by,
                    'reviewed_at' => $now,
                ),
                array( 'id' => $id ),
                array( '%s', '%s', '%d', '%s' ),
                array( '%d' )
            ) !== false;
            $this->redirect_admin_with_notice( 'updated', $ok );
        }

        if ( $action === 'mark_pending' ) {
            $ok = $wpdb->update(
                $this->table_name,
                array(
                    'status'        => 'pending',
                    'updated_at'    => $now,
                    'reviewed_by'   => 0,
                    'reviewed_note' => '',
                ),
                array( 'id' => $id ),
                array( '%s', '%s', '%d', '%s' ),
                array( '%d' )
            ) !== false;
            if ( $ok ) {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$this->table_name} SET reviewed_at = NULL WHERE id = %d",
                        $id
                    )
                );
            }
            $this->redirect_admin_with_notice( 'updated', $ok );
        }
    }

    private function redirect_admin_with_notice( $notice, $success = true ) {
        $redirect_args = array(
            'page'                      => self::PAGE_SLUG,
            'ds_account_deletion_notice' => sanitize_key( (string) $notice ),
            'ds_account_deletion_result' => $success ? '1' : '0',
        );

        if ( isset( $_GET['status'] ) && '' !== (string) wp_unslash( $_GET['status'] ) ) {
            $redirect_args['status'] = sanitize_key( wp_unslash( (string) $_GET['status'] ) );
        }
        if ( isset( $_GET['s'] ) && '' !== (string) wp_unslash( $_GET['s'] ) ) {
            $redirect_args['s'] = sanitize_text_field( wp_unslash( (string) $_GET['s'] ) );
        }
        if ( isset( $_GET['paged'] ) && '' !== (string) wp_unslash( $_GET['paged'] ) ) {
            $redirect_args['paged'] = max( 1, absint( wp_unslash( $_GET['paged'] ) ) );
        }

        wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
        exit;
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '您没有权限访问该页面。', 'developer-starter' ) );
        }

        $this->maybe_handle_admin_actions();

        global $wpdb;
        $status_whitelist = array( 'pending', 'approved', 'rejected', 'processed' );
        $status_filter    = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( (string) $_GET['status'] ) ) : '';
        if ( ! in_array( $status_filter, $status_whitelist, true ) ) {
            $status_filter = '';
        }
        $keyword = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['s'] ) ) : '';

        $per_page = 20;
        $paged    = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
        $offset   = ( $paged - 1 ) * $per_page;

        $where_clauses = array( '1=1' );
        $params        = array();
        if ( $status_filter !== '' ) {
            $where_clauses[] = 'status = %s';
            $params[]        = $status_filter;
        }
        if ( $keyword !== '' ) {
            $like            = '%' . $wpdb->esc_like( $keyword ) . '%';
            $where_clauses[] = '(username_snapshot LIKE %s OR email_snapshot LIKE %s OR CAST(user_id AS CHAR) LIKE %s)';
            $params[]        = $like;
            $params[]        = $like;
            $params[]        = $like;
        }
        $where_sql = implode( ' AND ', $where_clauses );

        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_sql}";
        if ( ! empty( $params ) ) {
            $total_records = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );
        } else {
            $total_records = (int) $wpdb->get_var( $count_sql );
        }

        $query_sql   = "SELECT * FROM {$this->table_name} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_params = $params;
        $query_params[] = $per_page;
        $query_params[] = $offset;
        $records = $wpdb->get_results( $wpdb->prepare( $query_sql, $query_params ) );

        $all_count       = self::count_requests_by_status();
        $pending_count   = self::count_requests_by_status( 'pending' );
        $processed_count = self::count_requests_by_status( 'processed' );
        $rejected_count  = self::count_requests_by_status( 'rejected' );

        $notice_code = isset( $_GET['ds_account_deletion_notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['ds_account_deletion_notice'] ) ) : '';
        $notice_ok   = isset( $_GET['ds_account_deletion_result'] ) && wp_unslash( (string) $_GET['ds_account_deletion_result'] ) === '1';
        ?>
        <div class="wrap qiling-account-deletion-admin">
            <h1><?php esc_html_e( '账号注销申请', 'developer-starter' ); ?></h1>

            <?php if ( $notice_code ) : ?>
                <div class="notice <?php echo $notice_ok ? 'notice-success' : 'notice-error'; ?> is-dismissible">
                    <p>
                        <?php
                        if ( $notice_ok ) {
                            if ( $notice_code === 'deleted' ) {
                                esc_html_e( '记录已删除。', 'developer-starter' );
                            } elseif ( $notice_code === 'updated' ) {
                                esc_html_e( '状态已更新。', 'developer-starter' );
                            } else {
                                esc_html_e( '操作完成。', 'developer-starter' );
                            }
                        } elseif ( $notice_code === 'nonce_error' ) {
                            esc_html_e( '安全校验失败，请重试。', 'developer-starter' );
                        } else {
                            esc_html_e( '操作失败，请稍后再试。', 'developer-starter' );
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="qiling-account-deletion-admin__stats">
                <span class="stat-item"><?php echo esc_html( sprintf( __( '总申请：%d', 'developer-starter' ), $all_count ) ); ?></span>
                <span class="stat-item stat-pending"><?php echo esc_html( sprintf( __( '待审核：%d', 'developer-starter' ), $pending_count ) ); ?></span>
                <span class="stat-item stat-processed"><?php echo esc_html( sprintf( __( '已处理：%d', 'developer-starter' ), $processed_count ) ); ?></span>
                <span class="stat-item stat-rejected"><?php echo esc_html( sprintf( __( '已驳回：%d', 'developer-starter' ), $rejected_count ) ); ?></span>
            </div>

            <form method="get" class="qiling-account-deletion-admin__filters">
                <input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>" />
                <label for="qiling-account-deletion-status" class="screen-reader-text"><?php esc_html_e( '状态筛选', 'developer-starter' ); ?></label>
                <select id="qiling-account-deletion-status" name="status">
                    <option value=""><?php esc_html_e( '全部状态', 'developer-starter' ); ?></option>
                    <option value="pending" <?php selected( $status_filter, 'pending' ); ?>><?php esc_html_e( '待审核', 'developer-starter' ); ?></option>
                    <option value="processed" <?php selected( $status_filter, 'processed' ); ?>><?php esc_html_e( '已处理', 'developer-starter' ); ?></option>
                    <option value="rejected" <?php selected( $status_filter, 'rejected' ); ?>><?php esc_html_e( '已驳回', 'developer-starter' ); ?></option>
                    <option value="approved" <?php selected( $status_filter, 'approved' ); ?>><?php esc_html_e( '审核通过', 'developer-starter' ); ?></option>
                </select>
                <label for="qiling-account-deletion-search" class="screen-reader-text"><?php esc_html_e( '搜索', 'developer-starter' ); ?></label>
                <input id="qiling-account-deletion-search" type="search" name="s" value="<?php echo esc_attr( $keyword ); ?>" placeholder="<?php esc_attr_e( '搜索用户ID/用户名/邮箱', 'developer-starter' ); ?>" />
                <button type="submit" class="button button-primary"><?php esc_html_e( '筛选', 'developer-starter' ); ?></button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>" class="button"><?php esc_html_e( '重置', 'developer-starter' ); ?></a>
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:64px;"><?php esc_html_e( 'ID', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '用户', 'developer-starter' ); ?></th>
                        <th style="width:120px;"><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                        <th style="width:170px;"><?php esc_html_e( '申请时间', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '协议快照', 'developer-starter' ); ?></th>
                        <th style="width:130px;"><?php esc_html_e( '处理人', 'developer-starter' ); ?></th>
                        <th style="width:170px;"><?php esc_html_e( '处理时间', 'developer-starter' ); ?></th>
                        <th style="width:220px;"><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $records ) ) : ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;"><?php esc_html_e( '暂无注销申请记录。', 'developer-starter' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $records as $record ) : ?>
                            <?php
                            $record_id    = (int) $record->id;
                            $status       = sanitize_key( (string) $record->status );
                            $status_label = self::get_status_label( $status );
                            $user         = get_userdata( (int) $record->user_id );
                            $reviewer     = ! empty( $record->reviewed_by ) ? get_userdata( (int) $record->reviewed_by ) : null;
                            $base_args    = array( 'page' => self::PAGE_SLUG );
                            if ( $status_filter !== '' ) {
                                $base_args['status'] = $status_filter;
                            }
                            if ( $keyword !== '' ) {
                                $base_args['s'] = $keyword;
                            }
                            if ( $paged > 1 ) {
                                $base_args['paged'] = $paged;
                            }
                            $action_base_url = admin_url( 'admin.php' );
                            ?>
                            <tr>
                                <td><?php echo esc_html( $record_id ); ?></td>
                                <td>
                                    <div><strong><?php echo esc_html( $record->display_name_snapshot ?: $record->username_snapshot ); ?></strong></div>
                                    <div class="muted">ID: <?php echo esc_html( (int) $record->user_id ); ?></div>
                                    <div class="muted"><?php echo esc_html( $record->email_snapshot ); ?></div>
                                    <?php if ( $user ) : ?>
                                        <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $record->user_id ) ); ?>" target="_blank"><?php esc_html_e( '查看用户', 'developer-starter' ); ?></a>
                                    <?php else : ?>
                                        <span class="muted is-danger"><?php esc_html_e( '用户已不存在', 'developer-starter' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span>
                                </td>
                                <td><?php echo esc_html( (string) $record->created_at ); ?></td>
                                <td>
                                    <?php
                                    $agreement_text = wp_strip_all_tags( (string) $record->agreement_snapshot );
                                    if ( $agreement_text === '' ) {
                                        $agreement_text = __( '无', 'developer-starter' );
                                    }
                                    echo esc_html( wp_trim_words( $agreement_text, 24, '…' ) );
                                    ?>
                                </td>
                                <td>
                                    <?php echo $reviewer ? esc_html( $reviewer->display_name ) : '-'; ?>
                                </td>
                                <td><?php echo esc_html( (string) ( $record->reviewed_at ?: '-' ) ); ?></td>
                                <td>
                                    <?php if ( in_array( $status, array( 'pending', 'approved' ), true ) ) : ?>
                                        <?php
                                        $processed_url = add_query_arg(
                                            array_merge(
                                                $base_args,
                                                array(
                                                    'ds_account_deletion_action' => 'mark_processed',
                                                    'rid'                        => $record_id,
                                                )
                                            ),
                                            $action_base_url
                                        );
                                        $processed_url = wp_nonce_url( $processed_url, 'ds_account_deletion_action_' . $record_id );

                                        $rejected_url = add_query_arg(
                                            array_merge(
                                                $base_args,
                                                array(
                                                    'ds_account_deletion_action' => 'mark_rejected',
                                                    'rid'                        => $record_id,
                                                )
                                            ),
                                            $action_base_url
                                        );
                                        $rejected_url = wp_nonce_url( $rejected_url, 'ds_account_deletion_action_' . $record_id );
                                        ?>
                                        <a href="<?php echo esc_url( $processed_url ); ?>"><?php esc_html_e( '标记已处理', 'developer-starter' ); ?></a>
                                        |
                                        <a href="<?php echo esc_url( $rejected_url ); ?>"><?php esc_html_e( '驳回', 'developer-starter' ); ?></a>
                                        |
                                    <?php else : ?>
                                        <?php
                                        $pending_url = add_query_arg(
                                            array_merge(
                                                $base_args,
                                                array(
                                                    'ds_account_deletion_action' => 'mark_pending',
                                                    'rid'                        => $record_id,
                                                )
                                            ),
                                            $action_base_url
                                        );
                                        $pending_url = wp_nonce_url( $pending_url, 'ds_account_deletion_action_' . $record_id );
                                        ?>
                                        <a href="<?php echo esc_url( $pending_url ); ?>"><?php esc_html_e( '设为待审核', 'developer-starter' ); ?></a>
                                        |
                                    <?php endif; ?>
                                    <?php
                                    $delete_url = add_query_arg(
                                        array_merge(
                                            $base_args,
                                            array(
                                                'ds_account_deletion_action' => 'delete',
                                                'rid'                        => $record_id,
                                            )
                                        ),
                                        $action_base_url
                                    );
                                    $delete_url = wp_nonce_url( $delete_url, 'ds_account_deletion_action_' . $record_id );
                                    ?>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" class="text-danger" onclick="return confirm('<?php echo esc_js( __( '确定删除这条申请记录吗？', 'developer-starter' ) ); ?>');"><?php esc_html_e( '删除', 'developer-starter' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            $total_pages = (int) ceil( $total_records / $per_page );
            if ( $total_pages > 1 ) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo paginate_links(
                    array(
                        'base'    => add_query_arg( 'paged', '%#%' ),
                        'format'  => '',
                        'current' => $paged,
                        'total'   => $total_pages,
                    )
                );
                echo '</div></div>';
            }
            ?>
        </div>
        <?php
    }
}
