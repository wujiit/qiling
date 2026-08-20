<?php
/**
 * Message Manager Class - 留言管理系统
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Message_Manager {

    const TABLE_VERSION = '1.1.0';
    const TABLE_VERSION_OPTION = 'developer_starter_message_table_version';
    const TABLE_MIGRATION_LOCK = 'developer_starter_message_table_migration_lock';

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'developer_starter_messages';
        
        add_action( 'after_switch_theme', array( $this, 'install_table' ), 10, 0 );
        add_action( 'admin_init', array( $this, 'maybe_create_table' ) );
        add_action( 'wp_ajax_ds_submit_message', array( $this, 'handle_message_submit' ) );
        add_action( 'wp_ajax_nopriv_ds_submit_message', array( $this, 'handle_message_submit' ) );
        add_action( 'admin_menu', array( $this, 'add_messages_menu' ), 20 ); // Priority 20 to load after main menu
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
                'target_version'     => self::TABLE_VERSION,
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
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(50) DEFAULT '',
            email VARCHAR(100) DEFAULT '',
            message TEXT NOT NULL,
            ip_address VARCHAR(45) DEFAULT '',
            user_agent VARCHAR(255) DEFAULT '',
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_read (is_read),
            KEY created_at (created_at),
            KEY ip_created (ip_address, created_at)
        ) $charset_collate;";
    }

    public function handle_message_submit() {
        $login_required = function_exists( 'developer_starter_get_option' ) && developer_starter_get_option( 'contact_message_login_required', '' ) === '1';
        if ( $login_required && ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message'   => __( '请先登录后再留言', 'developer-starter' ),
                'code'      => 'login_required',
                'login_url' => $this->get_login_url(),
            ) );
        }

        // Verify nonce
        $nonce = $this->get_post_value( 'nonce', 'nonce' );
        if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'ds_message_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( '安全验证失败', 'developer-starter' ) ) );
        }
	        
        // Rate limiting by IP
        $ip = developer_starter_get_client_ip();
        if ( $this->is_rate_limited( $ip ) ) {
            wp_send_json_error( array( 'message' => __( '提交过于频繁，请稍后再试', 'developer-starter' ) ) );
        }
	        
        // Sanitize inputs - prevent SQL injection
        $name = $this->get_post_value( 'name', 'text' );
        $phone = $this->get_post_value( 'phone', 'text' );
        $email = $this->get_post_value( 'email', 'email' );
        $message = $this->get_post_value( 'message', 'textarea' );
        
        // Validate required fields
        if ( empty( $name ) || empty( $message ) ) {
            wp_send_json_error( array( 'message' => __( '请填写必填项', 'developer-starter' ) ) );
        }
        
        if ( empty( $phone ) && empty( $email ) ) {
            wp_send_json_error( array( 'message' => __( '请填写联系电话或邮箱', 'developer-starter' ) ) );
        }
        
        // Insert into database
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'name'       => $name,
                'phone'      => $phone,
                'email'      => $email,
                'message'    => $message,
                'ip_address' => $ip,
                'user_agent' => $this->get_server_value( 'HTTP_USER_AGENT', 'text', '', 255 ),
                'is_read'    => 0,
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );
        
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => __( '提交失败，请稍后重试', 'developer-starter' ) ) );
        }
        
        // Send email notification
        $this->send_email_notification( $name, $phone, $email, $message );
        
        wp_send_json_success( array( 'message' => __( '留言提交成功，我们会尽快与您联系！', 'developer-starter' ) ) );
    }

    /**
     * 读取 POST 值并统一执行 wp_unslash + sanitize。
     *
     * @param string $key 字段名。
     * @param string $type 清理类型：nonce/text/textarea/email/key/absint/array/url。
     * @param mixed  $default 默认值。
     * @param int    $max_length 最大长度，0 表示不截断。
     * @return mixed
     */
    private function get_post_value( $key, $type = 'text', $default = '', $max_length = 0 ) {
        return $this->get_request_value( $_POST, $key, $type, $default, $max_length );
    }

    /**
     * 读取 GET 值并统一执行 wp_unslash + sanitize。
     *
     * @param string $key 字段名。
     * @param string $type 清理类型：nonce/text/textarea/email/key/absint/array/url。
     * @param mixed  $default 默认值。
     * @param int    $max_length 最大长度，0 表示不截断。
     * @return mixed
     */
    private function get_query_value( $key, $type = 'text', $default = '', $max_length = 0 ) {
        return $this->get_request_value( $_GET, $key, $type, $default, $max_length );
    }

    /**
     * 读取 SERVER 值并统一执行 wp_unslash + sanitize。
     *
     * @param string $key 字段名。
     * @param string $type 清理类型：nonce/text/textarea/email/key/absint/array/url。
     * @param mixed  $default 默认值。
     * @param int    $max_length 最大长度，0 表示不截断。
     * @return mixed
     */
    private function get_server_value( $key, $type = 'text', $default = '', $max_length = 0 ) {
        return $this->get_request_value( $_SERVER, $key, $type, $default, $max_length );
    }

    /**
     * 从请求数组中读取字段并按类型清理。
     *
     * @param array<string,mixed> $source 请求源。
     * @param string              $key 字段名。
     * @param string              $type 清理类型：nonce/text/textarea/email/key/absint/array/url。
     * @param mixed               $default 默认值。
     * @param int                 $max_length 最大长度，0 表示不截断。
     * @return mixed
     */
    private function get_request_value( $source, $key, $type = 'text', $default = '', $max_length = 0 ) {
        if ( ! is_array( $source ) || ! array_key_exists( $key, $source ) ) {
            return $default;
        }

        $value = wp_unslash( $source[ $key ] );
        $type = sanitize_key( (string) $type );

        if ( 'array' === $type ) {
            return is_array( $value ) ? $this->sanitize_request_array( $value ) : array();
        }

        if ( is_array( $value ) ) {
            return $default;
        }

        $value = trim( (string) $value );
        switch ( $type ) {
            case 'nonce':
            case 'text':
                $value = sanitize_text_field( $value );
                break;
            case 'textarea':
                $value = sanitize_textarea_field( $value );
                break;
            case 'email':
                $value = sanitize_email( $value );
                break;
            case 'key':
                $value = sanitize_key( $value );
                break;
            case 'absint':
                return absint( $value );
            case 'url':
                $value = esc_url_raw( $value );
                break;
            default:
                $value = sanitize_text_field( $value );
                break;
        }

        if ( $max_length > 0 && strlen( $value ) > $max_length ) {
            $value = substr( $value, 0, $max_length );
        }

        return $value;
    }

    /**
     * 递归清理请求数组。
     *
     * @param array<mixed> $value 请求数组。
     * @return array<mixed>
     */
    private function sanitize_request_array( $value ) {
        $clean = array();
        foreach ( $value as $raw_key => $raw_value ) {
            $clean_key = is_int( $raw_key ) ? $raw_key : sanitize_key( (string) $raw_key );
            if ( '' === (string) $clean_key ) {
                continue;
            }
            if ( is_array( $raw_value ) ) {
                $clean[ $clean_key ] = $this->sanitize_request_array( $raw_value );
            } else {
                $clean[ $clean_key ] = sanitize_text_field( (string) $raw_value );
            }
        }

        return $clean;
    }

    private function get_login_url() {
        $referer = wp_get_referer();
        $redirect = is_string( $referer ) && $referer !== '' ? $referer : home_url( '/' );
        $custom_login_page = function_exists( 'developer_starter_get_option' ) ? (int) developer_starter_get_option( 'login_page_id', '' ) : 0;

        if ( $custom_login_page > 0 ) {
            $custom_login_url = get_permalink( $custom_login_page );
            if ( $custom_login_url ) {
                return $custom_login_url;
            }
        }

        return wp_login_url( $redirect );
    }

    private function is_rate_limited( $ip ) {
        if ( function_exists( 'developer_starter_is_public_rate_limit_enabled' ) && developer_starter_is_public_rate_limit_enabled() ) {
            $window = function_exists( 'developer_starter_get_rate_limit_window' ) ? developer_starter_get_rate_limit_window() : 60;
            $max = function_exists( 'developer_starter_get_option' ) ? intval( developer_starter_get_option( 'request_rate_limit_message_max', 3 ) ) : 3;
            $max = max( 1, min( 50, $max ) );
            if ( function_exists( 'developer_starter_is_rate_limited' ) ) {
                return developer_starter_is_rate_limited( 'public_message', $max, $window );
            }
        }

        global $wpdb;
        $threshold = function_exists( 'wp_date' )
            ? wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - MINUTE_IN_SECONDS, wp_timezone() )
            : date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) - MINUTE_IN_SECONDS );
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE ip_address = %s AND created_at > %s",
            $ip,
            $threshold
        ) );
        return $count >= 3; // Max 3 submissions per minute
    }

    private function send_email_notification( $name, $phone, $email, $message ) {
        $mode = function_exists( 'developer_starter_get_notify_method' )
            ? developer_starter_get_notify_method( 'message', 'email' )
            : 'email';

        $should_send_email = function_exists( 'developer_starter_notify_method_has_email' )
            ? developer_starter_notify_method_has_email( $mode )
            : true;
        $should_send_push = function_exists( 'developer_starter_notify_method_has_push' )
            ? developer_starter_notify_method_has_push( $mode )
            : false;

        if ( ! $should_send_email && ! $should_send_push ) {
            return;
        }
        
        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );
        
        $subject = sprintf( __( '[%s] 新留言通知', 'developer-starter' ), $site_name );
        
        $body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); color: #fff; padding: 30px; border-radius: 10px 10px 0 0;">
                <h2 style="margin: 0;">' . esc_html__( '📬 新留言通知', 'developer-starter' ) . '</h2>
                <p style="margin: 10px 0 0; opacity: 0.9;">' . esc_html__( '您收到一条新的网站留言', 'developer-starter' ) . '</p>
            </div>
            <div style="background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; border-top: none;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 100px;">' . esc_html__( '姓名', 'developer-starter' ) . '</td>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">' . esc_html( $name ) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">' . esc_html__( '电话', 'developer-starter' ) . '</td>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">' . esc_html( $phone ) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">' . esc_html__( '邮箱', 'developer-starter' ) . '</td>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">' . esc_html( $email ) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold; vertical-align: top;">' . esc_html__( '留言', 'developer-starter' ) . '</td>
                        <td style="padding: 10px 0;">' . nl2br( esc_html( $message ) ) . '</td>
                    </tr>
                </table>
            </div>
            <div style="background: #1e293b; color: #94a3b8; padding: 20px; border-radius: 0 0 10px 10px; text-align: center; font-size: 12px;">
                <p style="margin: 0;">' . sprintf( esc_html__( '此邮件由 %s 自动发送', 'developer-starter' ), esc_html( $site_name ) ) . '</p>
            </div>
        </div>';
        
        if ( $should_send_email ) {
            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            wp_mail( $admin_email, $subject, $body, $headers );
        }

        if ( $should_send_push && function_exists( 'developer_starter_send_push_message' ) ) {
            $push_lines = array(
                __( '姓名', 'developer-starter' ) => $name,
                __( '电话', 'developer-starter' ) => $phone,
                __( '邮箱', 'developer-starter' ) => $email,
                __( '留言', 'developer-starter' ) => $message,
                __( '时间', 'developer-starter' ) => current_time( 'Y-m-d H:i:s' ),
            );
            developer_starter_send_push_message(
                'message',
                __( '新留言通知', 'developer-starter' ),
                $push_lines,
                array(
                    'args' => array(
                        'source' => 'qiling_theme_message',
                    ),
                )
            );
        }
    }

    public function add_messages_menu() {
        $unread_count = $this->get_unread_count();
        $menu_title = __( '留言管理', 'developer-starter' );
        if ( $unread_count > 0 ) {
            $menu_title .= ' <span class="awaiting-mod count-' . $unread_count . '"><span class="pending-count">' . $unread_count . '</span></span>';
        }
        
        add_submenu_page(
            'developer-starter-settings',
            __( '留言管理', 'developer-starter' ),
            $menu_title,
            'manage_options',
            'developer-starter-messages',
            array( $this, 'render_messages_page' )
        );
    }

    private function get_unread_count() {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$this->table_name}` WHERE is_read = %d", 0 ) );
    }

    public function render_messages_page() {
        global $wpdb;
        $paged = max( 1, (int) $this->get_query_value( 'paged', 'absint', 1 ) );
        $per_page = 50;
        $base_page_url = admin_url( 'admin.php?page=developer-starter-messages' );
	        
        // Handle actions
        $action = $this->get_query_value( 'action', 'key' );
        $nonce = $this->get_query_value( '_wpnonce', 'nonce' );
        if ( '' !== $action && '' !== $nonce ) {
            // 权限检查
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( '您没有权限执行此操作', 'developer-starter' ) );
            }
	
            if ( wp_verify_nonce( $nonce, 'ds_message_action' ) ) {
                if ( $action === 'delete_all' ) {
                    $wpdb->query( "TRUNCATE TABLE {$this->table_name}" );
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '所有留言已清空。', 'developer-starter' ) . '</p></div>';
                } else {
                    $id = (int) $this->get_query_value( 'id', 'absint', 0 );
                    if ( $id > 0 && $action === 'mark_read' ) {
                        $wpdb->update( $this->table_name, array( 'is_read' => 1 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );
                    } elseif ( $id > 0 && $action === 'delete' ) {
                        $wpdb->delete( $this->table_name, array( 'id' => $id ), array( '%d' ) );
                    }
                }
            }
        }
        
        // Get messages with real pagination
        $total_records = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$this->table_name}`" );
        $total_pages = max( 1, (int) ceil( $total_records / $per_page ) );
        if ( $paged > $total_pages ) {
            $paged = $total_pages;
        }
        $offset = ( $paged - 1 ) * $per_page;

        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$this->table_name}` ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        $delete_all_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'delete_all',
                    'paged'  => $paged,
                ),
                $base_page_url
            ),
            'ds_message_action'
        );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( '留言管理', 'developer-starter' ); ?></h1>
            <a href="<?php echo esc_url( $delete_all_url ); ?>" 
               class="page-title-action" 
               onclick="return confirm('<?php echo esc_js( __( '确定要清空所有留言吗？此操作不可恢复！', 'developer-starter' ) ); ?>');"
               style="color: #b32d2e; border-color: #b32d2e;">
                <?php esc_html_e( '清空所有留言', 'developer-starter' ); ?>
            </a>
            <hr class="wp-header-end">
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 100px;"><?php esc_html_e( '姓名', 'developer-starter' ); ?></th>
                        <th style="width: 120px;"><?php esc_html_e( '电话', 'developer-starter' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( '邮箱', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '留言内容', 'developer-starter' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( '时间', 'developer-starter' ); ?></th>
                        <th style="width: 60px;"><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                        <th style="width: 100px;"><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $messages ) ) : ?>
                        <tr><td colspan="8" style="text-align: center; padding: 40px;"><?php esc_html_e( '暂无留言', 'developer-starter' ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $messages as $msg ) : ?>
                            <tr style="<?php echo $msg->is_read ? '' : 'background: #fff9e6;'; ?>">
                                <td><?php echo esc_html( $msg->id ); ?></td>
                                <td><strong><?php echo esc_html( $msg->name ); ?></strong></td>
                                <td><?php echo esc_html( $msg->phone ); ?></td>
                                <td><?php echo esc_html( $msg->email ); ?></td>
                                <td><?php echo esc_html( wp_trim_words( $msg->message, 30 ) ); ?></td>
                                <td><?php echo esc_html( $msg->created_at ); ?></td>
                                <td>
                                    <?php if ( $msg->is_read ) : ?>
                                        <span style="color: #22c55e;"><?php esc_html_e( '已读', 'developer-starter' ); ?></span>
                                    <?php else : ?>
                                        <span style="color: #f59e0b; font-weight: bold;"><?php esc_html_e( '未读', 'developer-starter' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( ! $msg->is_read ) : ?>
                                        <?php
                                        $mark_read_url = wp_nonce_url(
                                            add_query_arg(
                                                array(
                                                    'action' => 'mark_read',
                                                    'id'     => (int) $msg->id,
                                                    'paged'  => $paged,
                                                ),
                                                $base_page_url
                                            ),
                                            'ds_message_action'
                                        );
                                        ?>
                                        <a href="<?php echo esc_url( $mark_read_url ); ?>"><?php esc_html_e( '标记已读', 'developer-starter' ); ?></a> |
                                    <?php endif; ?>
                                    <?php
                                    $delete_url = wp_nonce_url(
                                        add_query_arg(
                                            array(
                                                'action' => 'delete',
                                                'id'     => (int) $msg->id,
                                                'paged'  => $paged,
                                            ),
                                            $base_page_url
                                        ),
                                        'ds_message_action'
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" 
                                       onclick="return confirm('<?php echo esc_js( __( '确定删除此留言？', 'developer-starter' ) ); ?>');" style="color: #dc2626;"><?php esc_html_e( '删除', 'developer-starter' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo wp_kses_post(
                            paginate_links(
                                array(
                                    'base'    => add_query_arg( 'paged', '%#%', $base_page_url ),
                                    'format'  => '',
                                    'current' => $paged,
                                    'total'   => $total_pages,
                                )
                            )
                        );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
