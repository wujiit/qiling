<?php

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Post_Interaction_Manager {
    const TABLE_VERSION = '1.0.0';
    const TABLE_VERSION_OPTION = 'developer_starter_post_interaction_table_version';
    const TABLE_MIGRATION_LOCK = 'developer_starter_post_interaction_table_migration_lock';

    private $table_name;

    /**
     * 构造函数。
     *
     * @param bool $register_hooks 是否注册运行时钩子。
     */
    public function __construct( $register_hooks = true ) {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'developer_starter_post_interactions';

        if ( ! $register_hooks ) {
            return;
        }

        add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );
        add_action( 'admin_init', array( $this, 'maybe_create_table' ) );
        add_action( 'wp_ajax_ds_toggle_post_interaction', array( $this, 'ajax_toggle_interaction' ) );
        add_action( 'wp_ajax_nopriv_ds_toggle_post_interaction', array( $this, 'ajax_toggle_interaction' ) );
    }

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'developer_starter_post_interactions';
    }

    public function install_table() {
        $this->run_table_migration( true );
    }

    /**
     * 运行带锁的表结构迁移，不注册 AJAX/后台运行时钩子。
     *
     * @param bool $force 是否强制执行安装期迁移。
     * @return void
     */
    public static function run_locked_migration( $force = false ) {
        $manager = new self( false );
        $manager->run_table_migration( $force );
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
                'version_option'     => self::TABLE_VERSION_OPTION,
                'target_version'     => self::TABLE_VERSION,
                'lock_option'        => self::TABLE_MIGRATION_LOCK,
                'force'              => $force,
                'migration_callback' => array( __CLASS__, 'create_table' ),
            )
        );
    }

    public static function create_table() {
        Database_Schema_Migration_Service::apply_schema( self::get_table_schema() );
    }

    private static function get_table_schema() {
        global $wpdb;
        $table_name = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            interaction_type VARCHAR(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post_type (user_id, post_id, interaction_type),
            KEY post_type (post_id, interaction_type),
            KEY user_id (user_id)
        ) $charset_collate;";
    }

    public static function get_count( $post_id, $type ) {
        global $wpdb;
        $table_name = self::table_name();
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d AND interaction_type = %s",
                $post_id,
                $type
            )
        );
    }

    public static function has_interaction( $post_id, $user_id, $type ) {
        global $wpdb;
        $table_name = self::table_name();
        $id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE post_id = %d AND user_id = %d AND interaction_type = %s",
                $post_id,
                $user_id,
                $type
            )
        );
        return ! empty( $id );
    }

    private function toggle_interaction( $post_id, $user_id, $type ) {
        global $wpdb;
        $existing_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE post_id = %d AND user_id = %d AND interaction_type = %s",
                $post_id,
                $user_id,
                $type
            )
        );

        if ( $existing_id ) {
            $wpdb->delete( $this->table_name, array( 'id' => $existing_id ), array( '%d' ) );
            return false;
        }

        $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
                'interaction_type' => $type,
            ),
            array( '%d', '%d', '%s' )
        );

        return true;
    }

    public function ajax_toggle_interaction() {
        check_ajax_referer( 'ds_post_interaction', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( '请先登录后再操作。', 'developer-starter' ) ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        $type = isset( $_POST['interaction_type'] ) ? sanitize_key( wp_unslash( $_POST['interaction_type'] ) ) : '';

        if ( ! $post_id || ! in_array( $type, array( 'like', 'favorite' ), true ) ) {
            wp_send_json_error( array( 'message' => __( '参数错误。', 'developer-starter' ) ) );
        }

        if ( get_post_status( $post_id ) !== 'publish' ) {
            wp_send_json_error( array( 'message' => __( '文章不存在或不可用。', 'developer-starter' ) ) );
        }

        $active = $this->toggle_interaction( $post_id, get_current_user_id(), $type );
        $count = self::get_count( $post_id, $type );
        if ( $type === 'like' ) {
            update_post_meta( $post_id, 'post_like_count', $count );
        } elseif ( $type === 'favorite' ) {
            update_post_meta( $post_id, 'post_favorite_count', $count );
        }

        wp_send_json_success( array(
            'active' => $active,
            'count' => number_format_i18n( $count ),
        ) );
    }

	/**
	 * 获取用户的互动记录
	 *
	 * @param int    $user_id  用户ID.
	 * @param string $type     互动类型：'all', 'like', 'favorite'.
	 * @param int    $per_page 每页数量.
	 * @param int    $paged    当前页码.
	 * @return array 包含 'items' 和 'total' 的数组.
	 */
	public static function get_user_interactions( $user_id, $type = 'all', $per_page = 12, $paged = 1 ) {
		global $wpdb;
		$table_name = self::table_name();

		// 构建 WHERE 条件.
		// 使用占位符防止 SQL 注入.
		$query_where = 'WHERE user_id = %d';
		$params      = array( $user_id );

		if ( in_array( $type, array( 'like', 'favorite' ), true ) ) {
			$query_where .= ' AND interaction_type = %s';
			$params[]     = $type;
		}

		// 获取总数.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $query_where is constructed securely.
		$total_sql = "SELECT COUNT(*) FROM {$table_name} {$query_where}";
		// phpcs:ignore WordPress.DB.PreparedSQL.Itermous -- using array expansion for prepare.
		$total = (int) $wpdb->get_var( $wpdb->prepare( $total_sql, $params ) );

		// 计算偏移量.
		$offset = ( $paged - 1 ) * $per_page;

		// 查询记录.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $query_where is constructed securely.
		$items_sql = "SELECT post_id, interaction_type, created_at 
					  FROM {$table_name} 
					  {$query_where} 
					  ORDER BY created_at DESC 
					  LIMIT %d OFFSET %d";
		
		// 添加 limit 和 offset 到参数数组.
		$params[] = $per_page;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.Itermous -- using array expansion for prepare.
		$results = $wpdb->get_results( $wpdb->prepare( $items_sql, $params ) );

		return array(
			'items' => $results,
			'total' => $total,
		);
	}
}
