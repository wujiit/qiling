<?php
/**
 * 页面装修修订统一管理。
 *
 * 装修快照存放在页面 post meta 中，与 WordPress 文章修订不是同一种数据，
 * 因此使用独立管理页面，避免用户在数据库清理时混淆或误删。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

use Developer_Starter\Core\Frontend_Builder_Snapshot_Service;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Builder_Revision_Manager {

    const PAGE_SLUG = 'qiling-builder-revisions';
    const ACTION    = 'qiling_manage_builder_revisions';
    const NONCE     = 'qiling_manage_builder_revisions';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_page' ), 35 );
        add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_action' ) );
    }

    /**
     * 在主题设置菜单下增加独立管理入口。
     *
     * @return void
     */
    public function register_page() {
        add_submenu_page(
            'developer-starter-settings',
            __( '页面装修修订管理', 'developer-starter' ),
            __( '装修修订', 'developer-starter' ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    /**
     * 输出所有页面装修快照。
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足。', 'developer-starter' ) );
        }

        $paged = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
        $query = new \WP_Query(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => 20,
                'paged'          => $paged,
                'orderby'        => 'modified',
                'order'          => 'DESC',
                'meta_query'     => array(
                    array(
                        'key'     => Frontend_Builder_Snapshot_Service::META_KEY,
                        'compare' => 'EXISTS',
                    ),
                ),
            )
        );
        $service = new Frontend_Builder_Snapshot_Service();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '页面装修修订管理', 'developer-starter' ); ?></h1>
            <p><?php esc_html_e( '这里统一管理前台装修器在每次保存前自动创建的快照。每个页面最多保留 10 份，旧快照会自动滚动淘汰。', 'developer-starter' ); ?></p>
            <div class="notice notice-info inline"><p><?php esc_html_e( '装修修订保存在页面元数据 _qiling_frontend_builder_snapshots 中，不属于 WordPress 的文章修订。数据库清理里的“文章修订版本”不会管理这里的数据。', 'developer-starter' ); ?></p></div>

            <?php $this->render_notice(); ?>

            <?php if ( ! $query->have_posts() ) : ?>
                <p><?php esc_html_e( '当前没有页面装修修订。页面使用前台装修器保存后，这里会自动出现记录。', 'developer-starter' ); ?></p>
            <?php else : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( '确定清空所选页面的全部装修修订吗？此操作不可恢复。', 'developer-starter' ) ); ?>');">
                    <input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
                    <input type="hidden" name="revision_action" value="clear_selected_pages">
                    <?php wp_nonce_field( self::NONCE ); ?>
                    <table class="widefat striped">
                        <thead><tr>
                            <td class="check-column"><input type="checkbox" data-qiling-check-all-revisions></td>
                            <th><?php esc_html_e( '页面', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '装修修订', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '占用空间', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                        </tr></thead>
                        <tbody>
                        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                            <?php
                            $page_id   = get_the_ID();
                            $snapshots = array_reverse( $service->get_snapshots( $page_id ) );
                            $raw_value = get_post_meta( $page_id, Frontend_Builder_Snapshot_Service::META_KEY, true );
                            $bytes     = strlen( maybe_serialize( $raw_value ) );
                            ?>
                            <tr>
                                <th class="check-column"><input type="checkbox" name="page_ids[]" value="<?php echo esc_attr( (string) $page_id ); ?>" data-qiling-revision-page></th>
                                <td>
                                    <strong><?php echo esc_html( get_the_title() ); ?></strong><br>
                                    <span class="description">ID: <?php echo esc_html( (string) $page_id ); ?> · <?php $status_object = get_post_status_object( get_post_status() ); echo esc_html( is_object( $status_object ) && isset( $status_object->label ) ? $status_object->label : get_post_status() ); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( sprintf( __( '%d 份', 'developer-starter' ), count( $snapshots ) ) ); ?></strong>
                                    <?php foreach ( $snapshots as $snapshot ) : ?>
                                        <?php $this->render_snapshot_line( $page_id, $snapshot ); ?>
                                    <?php endforeach; ?>
                                </td>
                                <td><?php echo esc_html( size_format( $bytes ) ); ?></td>
                                <td>
                                    <a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'qiling_builder' => '1', 'page_id' => $page_id, 'qiling_builder_snapshots' => '1' ), get_permalink( $page_id ) ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( '进入装修器恢复', 'developer-starter' ); ?></a>
                                    <?php echo $this->get_clear_page_form( $page_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <p><button type="submit" class="button button-secondary"><?php esc_html_e( '清空所选页面修订', 'developer-starter' ); ?></button></p>
                </form>
                <?php
                echo wp_kses_post(
                    paginate_links(
                        array(
                            'base'    => add_query_arg( 'paged', '%#%', admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ),
                            'format'  => '',
                            'current' => $paged,
                            'total'   => max( 1, (int) $query->max_num_pages ),
                        )
                    )
                );
                ?>
                <script>
                (function() {
                    var toggle = document.querySelector('[data-qiling-check-all-revisions]');
                    if (!toggle) return;
                    toggle.addEventListener('change', function() {
                        document.querySelectorAll('[data-qiling-revision-page]').forEach(function(box) {
                            box.checked = toggle.checked;
                        });
                    });
                })();
                </script>
            <?php endif; ?>
        </div>
        <?php
        wp_reset_postdata();
    }

    /**
     * 输出一条快照摘要及删除按钮。
     *
     * @param int                 $page_id  页面 ID。
     * @param array<string,mixed> $snapshot 快照。
     * @return void
     */
    private function render_snapshot_line( $page_id, $snapshot ) {
        $snapshot_id = isset( $snapshot['id'] ) ? (string) $snapshot['id'] : '';
        if ( '' === $snapshot_id ) {
            return;
        }
        $user = ! empty( $snapshot['user_id'] ) ? get_userdata( absint( $snapshot['user_id'] ) ) : null;
        $user_name = $user instanceof \WP_User ? $user->display_name : __( '未知用户', 'developer-starter' );
        $created_at = isset( $snapshot['created_at'] ) ? (string) $snapshot['created_at'] : '';
        $module_count = isset( $snapshot['module_count'] ) ? absint( $snapshot['module_count'] ) : 0;
        $delete_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action'          => self::ACTION,
                    'revision_action' => 'delete_snapshot',
                    'page_id'         => $page_id,
                    'snapshot_id'     => rawurlencode( $snapshot_id ),
                ),
                admin_url( 'admin-post.php' )
            ),
            self::NONCE
        );
        ?>
        <div style="margin-top:6px;padding-top:6px;border-top:1px solid #e2e8f0;">
            <span><?php echo esc_html( $created_at ); ?></span>
            <span class="description"> · <?php echo esc_html( $user_name ); ?> · <?php echo esc_html( sprintf( __( '%d 个模块', 'developer-starter' ), $module_count ) ); ?></span>
            <a href="<?php echo esc_url( $delete_url ); ?>" style="margin-left:8px;color:#b32d2e;" onclick="return confirm('<?php echo esc_js( __( '确定删除这一条装修修订吗？', 'developer-starter' ) ); ?>');"><?php esc_html_e( '删除', 'developer-starter' ); ?></a>
        </div>
        <?php
    }

    /**
     * 构造单页清空表单。
     *
     * @param int $page_id 页面 ID。
     * @return string
     */
    private function get_clear_page_form( $page_id ) {
        $html  = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:8px;" onsubmit="return confirm(\'' . esc_js( __( '确定清空这个页面的全部装修修订吗？', 'developer-starter' ) ) . '\');">';
        $html .= '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
        $html .= '<input type="hidden" name="revision_action" value="clear_page">';
        $html .= '<input type="hidden" name="page_id" value="' . esc_attr( (string) $page_id ) . '">';
        $html .= wp_nonce_field( self::NONCE, '_wpnonce', true, false );
        $html .= '<button type="submit" class="button button-link-delete">' . esc_html__( '清空本页修订', 'developer-starter' ) . '</button></form>';
        return $html;
    }

    /**
     * 处理删除操作。
     *
     * @return void
     */
    public function handle_action() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足。', 'developer-starter' ) );
        }
        check_admin_referer( self::NONCE );

        $operation = isset( $_REQUEST['revision_action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['revision_action'] ) ) : '';
        $service   = new Frontend_Builder_Snapshot_Service();
        $affected  = 0;

        if ( 'delete_snapshot' === $operation ) {
            $page_id = isset( $_GET['page_id'] ) ? absint( wp_unslash( $_GET['page_id'] ) ) : 0;
            $snapshot_id = isset( $_GET['snapshot_id'] ) ? sanitize_text_field( wp_unslash( $_GET['snapshot_id'] ) ) : '';
            $result = $service->delete_snapshot( $page_id, $snapshot_id );
            $affected = is_wp_error( $result ) ? 0 : 1;
        } elseif ( 'clear_page' === $operation ) {
            $page_id = isset( $_POST['page_id'] ) ? absint( wp_unslash( $_POST['page_id'] ) ) : 0;
            $affected = $service->clear_page_snapshots( $page_id ) ? 1 : 0;
        } elseif ( 'clear_selected_pages' === $operation ) {
            $page_ids = isset( $_POST['page_ids'] ) && is_array( $_POST['page_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['page_ids'] ) ) : array();
            foreach ( array_unique( array_filter( $page_ids ) ) as $page_id ) {
                if ( $service->clear_page_snapshots( $page_id ) ) {
                    $affected++;
                }
            }
        }

        wp_safe_redirect( add_query_arg( 'qiling_revisions_changed', $affected, admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ) );
        exit;
    }

    /**
     * 显示操作结果。
     *
     * @return void
     */
    private function render_notice() {
        if ( ! isset( $_GET['qiling_revisions_changed'] ) ) {
            return;
        }
        $affected = absint( wp_unslash( $_GET['qiling_revisions_changed'] ) );
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( __( '装修修订管理操作完成，处理了 %d 项。', 'developer-starter' ), $affected ) ) . '</p></div>';
    }
}
