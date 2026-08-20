<?php
/**
 * Notification Manager - 站内通知系统
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Notification_Manager {

    private static $instance = null;
    const TABLE_VERSION_OPTION = 'developer_starter_notification_table_version';
    const TABLE_MIGRATION_LOCK = 'developer_starter_notification_table_migration_lock';

    private $table_name;
    private $db_version = '1.0.0';

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'developer_starter_notifications';

        add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );
        add_action( 'admin_init', array( $this, 'maybe_create_table' ) );

        // Hook for third-party plugins
        add_action( 'developer_starter_add_notification', array( $this, 'handle_add_notification' ), 10, 4 );
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
                'version_option'     => self::TABLE_VERSION_OPTION,
                'target_version'     => $this->db_version,
                'lock_option'        => self::TABLE_MIGRATION_LOCK,
                'force'              => $force,
                'migration_callback' => array( $this, 'create_table' ),
            )
        );
    }

    public function create_table() {
        Database_Schema_Migration_Service::apply_schema( $this->get_table_schema() );
    }

    private function get_table_schema() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            title VARCHAR(200) NOT NULL,
            content TEXT DEFAULT NULL,
            link_url VARCHAR(255) DEFAULT NULL,
            notice_type VARCHAR(20) NOT NULL DEFAULT 'info',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME DEFAULT NULL,
            meta TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) {$charset_collate};";
    }

    public function handle_add_notification( $user_id, $title, $content = '', $args = array() ) {
        $this->add_notification( $user_id, $title, $content, $args );
    }

    /**
     * 添加通知
     *
     * @param int    $user_id
     * @param string $title
     * @param string $content
     * @param array  $args {type, link, meta, is_read}
     * @return int 新通知ID
     */
    public function add_notification( $user_id, $title, $content = '', $args = array() ) {
        $user_id = absint( $user_id );
        $title = trim( (string) $title );
        if ( $user_id <= 0 || $title === '' ) {
            return 0;
        }

        $scene = '';
        if ( isset( $args['scene'] ) ) {
            $scene = sanitize_key( (string) $args['scene'] );
        } elseif ( isset( $args['meta'] ) && is_array( $args['meta'] ) && isset( $args['meta']['scene'] ) ) {
            $scene = sanitize_key( (string) $args['meta']['scene'] );
        }

        if ( function_exists( 'developer_starter_site_notify_enabled' )
            && ! developer_starter_site_notify_enabled( $scene, true )
        ) {
            return 0;
        }

        $allowed_types = array( 'info', 'success', 'warning', 'error' );
        $type = isset( $args['type'] ) ? sanitize_key( $args['type'] ) : 'info';
        if ( ! in_array( $type, $allowed_types, true ) ) {
            $type = 'info';
        }

        $data = array(
            'user_id'    => $user_id,
            'title'      => sanitize_text_field( $title ),
            'content'    => $content !== '' ? wp_kses_post( $content ) : '',
            'link_url'   => isset( $args['link'] ) ? esc_url_raw( $args['link'] ) : '',
            'notice_type'=> $type,
            'is_read'    => ! empty( $args['is_read'] ) ? 1 : 0,
            'meta'       => isset( $args['meta'] ) ? wp_json_encode( $args['meta'] ) : '',
            'created_at' => current_time( 'mysql' ),
        );

        global $wpdb;
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array( '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
        );

        if ( $result === false ) {
            return 0;
        }

        $notice_id = (int) $wpdb->insert_id;

        /**
         * 通知创建完成后触发
         *
         * @param int   $notice_id
         * @param array $data
         */
        do_action( 'developer_starter_notification_created', $notice_id, $data );

        return $notice_id;
    }

    /**
     * 获取用户通知列表
     */
    public function get_user_notifications( $user_id, $args = array() ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return array();
        }

        global $wpdb;

        $defaults = array(
            'status' => 'all', // all | unread | read
            'limit'  => 10,
            'offset' => 0,
            'order'  => 'DESC',
        );
        $args = wp_parse_args( $args, $defaults );

        $status = sanitize_key( $args['status'] );
        if ( ! in_array( $status, array( 'all', 'unread', 'read' ), true ) ) {
            $status = 'all';
        }

        $where = $wpdb->prepare( 'user_id = %d', $user_id );
        if ( $status === 'unread' ) {
            $where .= ' AND is_read = 0';
        } elseif ( $status === 'read' ) {
            $where .= ' AND is_read = 1';
        }

        $limit = max( 1, (int) $args['limit'] );
        $offset = max( 0, (int) $args['offset'] );
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name}
             WHERE {$where}
             ORDER BY id {$order}
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * 获取用户通知数量
     */
    public function count_user_notifications( $user_id, $status = 'all' ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return 0;
        }

        $status = sanitize_key( $status );
        if ( ! in_array( $status, array( 'all', 'unread', 'read' ), true ) ) {
            $status = 'all';
        }

        global $wpdb;
        $where = $wpdb->prepare( 'user_id = %d', $user_id );
        if ( $status === 'unread' ) {
            $where .= ' AND is_read = 0';
        } elseif ( $status === 'read' ) {
            $where .= ' AND is_read = 1';
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}" );
    }

    /**
     * 标记已读
     */
    public function mark_read( $notice_id, $user_id = 0 ) {
        $notice_id = absint( $notice_id );
        if ( $notice_id <= 0 ) {
            return false;
        }
        $user_id = absint( $user_id );

        global $wpdb;
        $where = array( 'id' => $notice_id );
        $where_formats = array( '%d' );
        if ( $user_id > 0 ) {
            $where['user_id'] = $user_id;
            $where_formats[] = '%d';
        }

        return $wpdb->update(
            $this->table_name,
            array(
                'is_read' => 1,
                'read_at' => current_time( 'mysql' ),
            ),
            $where,
            array( '%d', '%s' ),
            $where_formats
        );
    }

    /**
     * 标记全部已读
     */
    public function mark_all_read( $user_id ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return false;
        }

        global $wpdb;
        return $wpdb->update(
            $this->table_name,
            array(
                'is_read' => 1,
                'read_at' => current_time( 'mysql' ),
            ),
            array( 'user_id' => $user_id, 'is_read' => 0 ),
            array( '%d', '%s' ),
            array( '%d', '%d' )
        );
    }

    /**
     * 清空用户全部通知
     *
     * @param int $user_id 用户ID
     * @return int|false 删除行数
     */
    public function clear_all( $user_id ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return false;
        }

        global $wpdb;
        return $wpdb->delete(
            $this->table_name,
            array( 'user_id' => $user_id ),
            array( '%d' )
        );
    }
}
