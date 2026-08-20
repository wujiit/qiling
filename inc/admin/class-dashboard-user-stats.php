<?php
/**
 * Dashboard User Stats - 仪表盘用户统计组件
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dashboard_User_Stats {

    /**
     * 仪表盘组件 ID
     *
     * @var string
     */
    private $widget_id = 'developer_starter_dashboard_user_stats';

    /**
     * 构造函数
     */
    public function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
    }

    /**
     * 注册仪表盘组件
     *
     * @return void
     */
    public function register_widget() {
        if ( ! current_user_can( 'list_users' ) ) {
            return;
        }

        wp_add_dashboard_widget(
            $this->widget_id,
            __( '用户数据统计', 'developer-starter' ),
            array( $this, 'render_widget' )
        );
    }

    /**
     * 渲染组件内容
     *
     * @return void
     */
    public function render_widget() {
        $all_users      = count_users();
        $total_users    = isset( $all_users['total_users'] ) ? (int) $all_users['total_users'] : 0;
        $month_new      = $this->get_current_month_registered_users_count();
        $current_month  = function_exists( 'developer_starter_get_month_label' )
            ? developer_starter_get_month_label()
            : wp_date( 'Y年n月' );
        ?>
        <div class="developer-starter-dashboard-user-stats">
            <p>
                <strong><?php esc_html_e( '当前网站注册用户总数', 'developer-starter' ); ?>：</strong>
                <span><?php echo esc_html( number_format_i18n( $total_users ) ); ?></span>
            </p>
            <p>
                <strong><?php echo esc_html( sprintf( __( '%s新增注册用户', 'developer-starter' ), $current_month ) ); ?>：</strong>
                <span><?php echo esc_html( number_format_i18n( $month_new ) ); ?></span>
            </p>
            <p style="margin:8px 0 0;color:#646970;">
                <?php esc_html_e( '提示：可在右上角“显示选项”中隐藏该统计组件。', 'developer-starter' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * 获取本月新增注册用户数
     *
     * @return int
     */
    private function get_current_month_registered_users_count() {
        $timezone        = wp_timezone();
        $month_start     = new \DateTimeImmutable( 'first day of this month 00:00:00', $timezone );
        $month_start_utc = $month_start->setTimezone( new \DateTimeZone( 'UTC' ) );

        $query = new \WP_User_Query(
            array(
                'fields'      => 'ID',
                'number'      => 1,
                'count_total' => true,
                'date_query'  => array(
                    array(
                        'column'    => 'user_registered',
                        'after'     => $month_start_utc->format( 'Y-m-d H:i:s' ),
                        'inclusive' => true,
                    ),
                ),
            )
        );

        return (int) $query->get_total();
    }
}
