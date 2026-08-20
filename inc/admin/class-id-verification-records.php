<?php
/**
 * ID Verification Records Admin Page - 身份证验证记录管理
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ID_Verification_Records {

    /**
     * 数据表名
     */
    private $table_name;

    /**
     * 构造函数
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'qiling_id_verifications';

        add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
    }

    /**
     * 获取选项
     */
    private function get_option( $key, $default = '' ) {
        $options = get_option( 'developer_starter_options', array() );
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    /**
     * 添加子菜单页面
     */
    public function add_submenu_page() {
        // 只有启用功能时才显示菜单
        if ( $this->get_option( 'id_verification_enable', '' ) !== '1' ) {
            return;
        }

        add_submenu_page(
            'developer-starter-settings',  // 父菜单 slug
            '实名验证记录',
            '实名验证记录',
            'manage_options',
            'qiling-id-verification-records',
            array( $this, 'render_page' )
        );
    }

    /**
     * 渲染页面
     */
    public function render_page() {
        global $wpdb;

        $per_page = 20;
        $paged = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
        $offset = ( $paged - 1 ) * $per_page;

        $total_records = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
        $records = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY verification_time DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ) );
        $can_decrypt_pii = class_exists( '\Developer_Starter\Core\ID_Verification_Manager' )
            && method_exists( '\Developer_Starter\Core\ID_Verification_Manager', 'decrypt_pii_value' );

        // 运营商中文名称映射
        $operator_names = array(
            'cmcc' => '移动',
            'cucc' => '联通',
            'ctcc' => '电信',
        );
        ?>
        <div class="wrap">
            <h1>用户实名验证记录</h1>
            <p>总验证次数：<?php echo esc_html( $total_records ); ?></p>
            
            <?php if ( empty( $records ) ) : ?>
                <div class="notice notice-info">
                    <p>暂无验证记录</p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;">用户ID</th>
                            <th style="width: 100px;">用户名</th>
                            <th style="width: 80px;">姓名</th>
                            <th style="width: 120px;">手机号</th>
                            <th style="width: 160px;">身份证号</th>
                            <th style="width: 60px;">运营商</th>
                            <th style="width: 50px;">性别</th>
                            <th style="width: 100px;">生日</th>
                            <th>地址</th>
                            <th style="width: 120px;">IP地址</th>
                            <th style="width: 60px;">结果</th>
                            <th style="width: 150px;">验证时间</th>
                            <th style="width: 60px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $records as $record ) : 
                            $user = get_userdata( $record->user_id );
                            $username = $user ? $user->user_login : '未知';
                            $channel = isset( $operator_names[ $record->channel ] ) ? $operator_names[ $record->channel ] : $record->channel;
                            $name_value = (string) $record->name;
                            $mobile_value = (string) $record->mobile;
                            $idcard_value = (string) $record->idcard;
                            if ( $can_decrypt_pii ) {
                                $name_value = \Developer_Starter\Core\ID_Verification_Manager::decrypt_pii_value( $name_value );
                                $mobile_value = \Developer_Starter\Core\ID_Verification_Manager::get_record_mobile_value( $record );
                                $idcard_value = \Developer_Starter\Core\ID_Verification_Manager::decrypt_pii_value( $idcard_value );
                            }
                        ?>
                        <tr>
                            <td><?php echo esc_html( $record->user_id ); ?></td>
                            <td><?php echo esc_html( $username ); ?></td>
                            <td><?php echo esc_html( $name_value ); ?></td>
                            <td><?php echo esc_html( $mobile_value ); ?></td>
                            <td><?php echo esc_html( $idcard_value ); ?></td>
                            <td><?php echo esc_html( $channel ); ?></td>
                            <td><?php echo esc_html( $record->sex ); ?></td>
                            <td><?php echo esc_html( $record->birthday ); ?></td>
                            <td><?php echo esc_html( $record->address ); ?></td>
                            <td><?php echo esc_html( $record->ip_address ); ?></td>
                            <td>
                                <span class="<?php echo $record->result === '成功' ? 'dashicons dashicons-yes-alt' : 'dashicons dashicons-dismiss'; ?>" 
                                      style="color: <?php echo $record->result === '成功' ? '#10b981' : '#ef4444'; ?>;"></span>
                                <?php echo esc_html( $record->result ); ?>
                            </td>
                            <td><?php echo esc_html( $record->verification_time ); ?></td>
                            <td>
                                <button class="button button-small qiling-delete-record" data-id="<?php echo esc_attr( $record->id ); ?>">删除</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php
                $total_pages = ceil( $total_records / $per_page );
                if ( $total_pages > 1 ) {
                    echo '<div class="tablenav"><div class="tablenav-pages">';
                    echo paginate_links( array(
                        'base'    => add_query_arg( 'paged', '%#%' ),
                        'format'  => '',
                        'current' => $paged,
                        'total'   => $total_pages,
                    ) );
                    echo '</div></div>';
                }
                ?>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var confirmDeleteText = <?php echo wp_json_encode( __( '确定删除此记录？此操作不可恢复。', 'developer-starter' ) ); ?>;
            var deletingText = <?php echo wp_json_encode( __( '删除中...', 'developer-starter' ) ); ?>;
            var deleteFailedText = <?php echo wp_json_encode( __( '删除失败', 'developer-starter' ) ); ?>;
            var deleteFailedRetryText = <?php echo wp_json_encode( __( '删除失败，请重试', 'developer-starter' ) ); ?>;
            var deleteText = <?php echo wp_json_encode( __( '删除', 'developer-starter' ) ); ?>;
            $('.qiling-delete-record').click(function() {
                if (!confirm(confirmDeleteText)) return;
                
                var $btn = $(this);
                var id = $btn.data('id');
                
                $btn.prop('disabled', true).text(deletingText);
                
                $.ajax({
                    url: <?php echo wp_json_encode( esc_url_raw( rest_url( 'qiling/v1/id-verification/delete/' ) ) ); ?> + id,
                    method: 'DELETE',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>);
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.closest('tr').fadeOut(function() {
                                $(this).remove();
                            });
                        } else {
                            alert(deleteFailedText);
                            $btn.prop('disabled', false).text(deleteText);
                        }
                    },
                    error: function() {
                        alert(deleteFailedRetryText);
                        $btn.prop('disabled', false).text(deleteText);
                    }
                });
            });
        });
        </script>
        <?php
    }
}
