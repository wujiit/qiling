<?php
/**
 * Site Notification Admin - 后台站内通知发送
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Site_Notification_Admin {

    /**
     * 页面 slug
     *
     * @var string
     */
    private $page_slug = 'developer-starter-site-notification-send';

    /**
     * 构造函数
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
        add_action( 'admin_post_developer_starter_send_site_notification', array( $this, 'handle_submit' ) );
    }

    /**
     * 注册后台菜单
     *
     * @return void
     */
    public function add_submenu_page() {
        add_submenu_page(
            'developer-starter-settings',
            __( '站内通知发送', 'developer-starter' ),
            __( '站内通知发送', 'developer-starter' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'render_page' )
        );
    }

    /**
     * 处理发送请求
     *
     * @return void
     */
    public function handle_submit() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足。', 'developer-starter' ) );
        }

        check_admin_referer( 'developer_starter_send_site_notification' );

        $recipient_mode = isset( $_POST['recipient_mode'] ) ? sanitize_key( wp_unslash( $_POST['recipient_mode'] ) ) : 'specific';
        if ( ! in_array( $recipient_mode, array( 'specific', 'role' ), true ) ) {
            $recipient_mode = 'specific';
        }

        $notice_title = isset( $_POST['notice_title'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_title'] ) ) : '';
        $notice_content = isset( $_POST['notice_content'] ) ? wp_kses_post( wp_unslash( $_POST['notice_content'] ) ) : '';
        $notice_type = isset( $_POST['notice_type'] ) ? sanitize_key( wp_unslash( $_POST['notice_type'] ) ) : 'info';
        $notice_link = isset( $_POST['notice_link'] ) ? esc_url_raw( wp_unslash( $_POST['notice_link'] ) ) : '';

        if ( ! in_array( $notice_type, array( 'info', 'success', 'warning', 'error' ), true ) ) {
            $notice_type = 'info';
        }

        if ( $notice_title === '' ) {
            $this->redirect_with_result( array(
                'notice_status'  => 'error',
                'notice_message' => __( '通知标题不能为空。', 'developer-starter' ),
            ) );
        }

        $recipient_user_ids = array();
        $invalid_identifiers = array();

        if ( $recipient_mode === 'specific' ) {
            $raw_identifiers = isset( $_POST['recipient_identifiers'] ) ? wp_unslash( $_POST['recipient_identifiers'] ) : '';
            $parsed = $this->parse_user_identifiers( $raw_identifiers );
            $recipient_user_ids = $parsed['user_ids'];
            $invalid_identifiers = $parsed['invalid'];

            if ( empty( $recipient_user_ids ) ) {
                $this->redirect_with_result( array(
                    'notice_status'  => 'error',
                    'notice_message' => __( '未匹配到有效用户，请检查用户ID/用户名/邮箱。', 'developer-starter' ),
                ) );
            }
        } else {
            $role = isset( $_POST['recipient_role'] ) ? sanitize_key( wp_unslash( $_POST['recipient_role'] ) ) : '';
            if ( $role === '' ) {
                $this->redirect_with_result( array(
                    'notice_status'  => 'error',
                    'notice_message' => __( '请选择用户角色。', 'developer-starter' ),
                ) );
            }

            $query = new \WP_User_Query(
                array(
                    'role'        => $role,
                    'fields'      => 'ID',
                    'number'      => 2000,
                    'count_total' => true,
                )
            );

            $recipient_user_ids = array_map( 'absint', (array) $query->get_results() );
            $recipient_user_ids = array_values( array_filter( array_unique( $recipient_user_ids ) ) );

            if ( empty( $recipient_user_ids ) ) {
                $this->redirect_with_result( array(
                    'notice_status'  => 'error',
                    'notice_message' => __( '该角色下暂无用户。', 'developer-starter' ),
                ) );
            }

            if ( (int) $query->get_total() > 2000 ) {
                $invalid_identifiers[] = __( '角色用户数超过 2000，仅发送给前 2000 位用户。', 'developer-starter' );
            }
        }

        $sender = wp_get_current_user();
        $sent_count = 0;

        foreach ( $recipient_user_ids as $target_user_id ) {
            $notice_id = 0;
            if ( function_exists( 'developer_starter_add_user_notification' ) ) {
                $notice_id = developer_starter_add_user_notification(
                    $target_user_id,
                    $notice_title,
                    $notice_content,
                    array(
                        'type'  => $notice_type,
                        'link'  => $notice_link,
                        'scene' => 'admin_manual',
                        'meta'  => array(
                            'scene'       => 'admin_manual',
                            'sender_id'   => (int) $sender->ID,
                            'sender_name' => (string) $sender->display_name,
                        ),
                    )
                );
            }

            if ( $notice_id > 0 ) {
                $sent_count++;
            }
        }

        if ( $sent_count <= 0 ) {
            $this->redirect_with_result( array(
                'notice_status'  => 'error',
                'notice_message' => __( '发送失败，请确认已启用站内通知。', 'developer-starter' ),
            ) );
        }

        $result_message = sprintf(
            /* translators: %d: sent user count */
            __( '发送完成，共发送给 %d 位用户。', 'developer-starter' ),
            $sent_count
        );

        if ( ! empty( $invalid_identifiers ) ) {
            $invalid_preview = implode( '、', array_slice( $invalid_identifiers, 0, 5 ) );
            if ( count( $invalid_identifiers ) > 5 ) {
                $invalid_preview .= '...';
            }
            $result_message .= ' ' . sprintf(
                /* translators: %s: invalid user identifiers */
                __( '未识别项：%s', 'developer-starter' ),
                $invalid_preview
            );
        }

        $this->redirect_with_result( array(
            'notice_status'  => 'success',
            'notice_message' => $result_message,
        ) );
    }

    /**
     * 渲染页面
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $status = isset( $_GET['notice_status'] ) ? sanitize_key( wp_unslash( $_GET['notice_status'] ) ) : '';
        $message = isset( $_GET['notice_message'] ) ? sanitize_text_field( wp_unslash( $_GET['notice_message'] ) ) : '';

        $roles = function_exists( 'wp_roles' ) && wp_roles() ? wp_roles()->roles : array();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '站内通知发送', 'developer-starter' ); ?></h1>
            <p><?php esc_html_e( '管理员可向指定用户或某个角色用户发送单向站内通知。', 'developer-starter' ); ?></p>

            <?php if ( function_exists( 'developer_starter_site_notify_enabled' ) && ! developer_starter_site_notify_enabled( '', true ) ) : ?>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( '当前“站内通知”总开关已关闭，发送将不会写入通知记录。请先在主题设置中开启。', 'developer-starter' ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( $status && $message ) : ?>
                <div class="notice <?php echo $status === 'success' ? 'notice-success' : 'notice-error'; ?> is-dismissible">
                    <p><?php echo esc_html( $message ); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width: 880px;">
                <input type="hidden" name="action" value="developer_starter_send_site_notification" />
                <?php wp_nonce_field( 'developer_starter_send_site_notification' ); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e( '接收方式', 'developer-starter' ); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="recipient_mode" value="specific" checked />
                                        <?php esc_html_e( '指定用户', 'developer-starter' ); ?>
                                    </label>
                                    <br />
                                    <label>
                                        <input type="radio" name="recipient_mode" value="role" />
                                        <?php esc_html_e( '按角色群发', 'developer-starter' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr data-recipient-row="specific">
                            <th scope="row">
                                <?php esc_html_e( '用户标识', 'developer-starter' ); ?>
                            </th>
                            <td>
                                <textarea name="recipient_identifiers" rows="6" class="large-text" placeholder="1\nadmin\nuser@example.com"></textarea>
                                <p class="description"><?php esc_html_e( '支持用户ID、用户名、邮箱；可用英文逗号或换行分隔。', 'developer-starter' ); ?></p>
                            </td>
                        </tr>
                        <tr data-recipient-row="role" style="display:none;">
                            <th scope="row"><?php esc_html_e( '用户角色', 'developer-starter' ); ?></th>
                            <td>
                                <select name="recipient_role">
                                    <option value=""><?php esc_html_e( '请选择角色', 'developer-starter' ); ?></option>
                                    <?php foreach ( $roles as $role_key => $role_data ) : ?>
                                        <option value="<?php echo esc_attr( $role_key ); ?>">
                                            <?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e( '单次最多发送给前 2000 位角色用户。', 'developer-starter' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( '通知标题', 'developer-starter' ); ?></th>
                            <td>
                                <input type="text" name="notice_title" class="regular-text" maxlength="200" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( '通知内容', 'developer-starter' ); ?></th>
                            <td>
                                <textarea name="notice_content" rows="8" class="large-text"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( '通知类型', 'developer-starter' ); ?></th>
                            <td>
                                <select name="notice_type">
                                    <option value="info"><?php esc_html_e( '信息', 'developer-starter' ); ?></option>
                                    <option value="success"><?php esc_html_e( '成功', 'developer-starter' ); ?></option>
                                    <option value="warning"><?php esc_html_e( '警告', 'developer-starter' ); ?></option>
                                    <option value="error"><?php esc_html_e( '错误', 'developer-starter' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( '跳转链接', 'developer-starter' ); ?></th>
                            <td>
                                <input type="url" name="notice_link" class="regular-text" placeholder="https://example.com/path" />
                                <p class="description"><?php esc_html_e( '可选。填写后用户可在通知中点击“查看详情”。', 'developer-starter' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button( __( '发送通知', 'developer-starter' ) ); ?>
            </form>
        </div>

        <script>
        (function() {
            var modeInputs = document.querySelectorAll('input[name="recipient_mode"]');
            var specificRows = document.querySelectorAll('[data-recipient-row="specific"]');
            var roleRows = document.querySelectorAll('[data-recipient-row="role"]');

            function toggleRecipientRows() {
                var mode = 'specific';
                for (var i = 0; i < modeInputs.length; i++) {
                    if (modeInputs[i].checked) {
                        mode = modeInputs[i].value;
                        break;
                    }
                }

                for (var j = 0; j < specificRows.length; j++) {
                    specificRows[j].style.display = mode === 'specific' ? '' : 'none';
                }
                for (var k = 0; k < roleRows.length; k++) {
                    roleRows[k].style.display = mode === 'role' ? '' : 'none';
                }
            }

            for (var idx = 0; idx < modeInputs.length; idx++) {
                modeInputs[idx].addEventListener('change', toggleRecipientRows);
            }

            toggleRecipientRows();
        })();
        </script>
        <?php
    }

    /**
     * 解析用户标识
     *
     * @param string $raw_identifiers 用户标识原文
     * @return array
     */
    private function parse_user_identifiers( $raw_identifiers ) {
        $raw_identifiers = trim( (string) $raw_identifiers );
        if ( $raw_identifiers === '' ) {
            return array(
                'user_ids' => array(),
                'invalid'  => array(),
            );
        }

        $tokens = preg_split( '/[\r\n,]+/', $raw_identifiers );
        $tokens = array_filter( array_map( 'trim', (array) $tokens ) );

        $user_ids = array();
        $invalid = array();

        foreach ( $tokens as $token ) {
            $user = false;

            if ( ctype_digit( $token ) ) {
                $user = get_user_by( 'id', absint( $token ) );
            } elseif ( strpos( $token, '@' ) !== false ) {
                $user = get_user_by( 'email', sanitize_email( $token ) );
            } else {
                $user = get_user_by( 'login', sanitize_user( $token, true ) );
            }

            if ( $user && isset( $user->ID ) ) {
                $user_ids[] = (int) $user->ID;
            } else {
                $invalid[] = $token;
            }
        }

        return array(
            'user_ids' => array_values( array_filter( array_unique( $user_ids ) ) ),
            'invalid'  => array_values( array_unique( $invalid ) ),
        );
    }

    /**
     * 跳转并携带结果
     *
     * @param array $args 参数
     * @return void
     */
    private function redirect_with_result( $args ) {
        $url = add_query_arg( array_merge( array( 'page' => $this->page_slug ), $args ), admin_url( 'admin.php' ) );
        wp_safe_redirect( $url );
        exit;
    }
}
