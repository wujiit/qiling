<?php
/**
 * 新手建站引导与模板中心强力指引管理类。
 *
 * 为新手站长提供清晰、醒目的动线引导，避免在庞大的主题设置中迷失。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Onboarding_Admin {

    const DISMISS_META_KEY = '_qiling_onboarding_notice_dismissed';
    const DISMISS_ACTION   = 'developer_starter_dismiss_onboard_notice';

    /**
     * 单例实例。
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * 获取单例。
     *
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 构造函数。
     */
    private function __construct() {
        add_action( 'admin_notices', array( $this, 'render_onboarding_notice' ), 5 );
        add_action( 'wp_ajax_' . self::DISMISS_ACTION, array( $this, 'handle_dismiss_ajax' ) );
        add_action( 'admin_init', array( $this, 'handle_dismiss_get' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
    }

    /**
     * 检查当前用户是否已关闭新手引导通知。
     *
     * @return bool
     */
    public function is_dismissed() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return true;
        }
        return '1' === (string) get_user_meta( $user_id, self::DISMISS_META_KEY, true );
    }

    /**
     * 处理 GET 形式的关闭通知请求。
     *
     * @return void
     */
    public function handle_dismiss_get() {
        if ( ! isset( $_GET['ds_dismiss_onboard'] ) || '1' !== (string) $_GET['ds_dismiss_onboard'] ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_admin_referer( 'ds_dismiss_onboard_nonce' );

        $user_id = get_current_user_id();
        if ( $user_id ) {
            update_user_meta( $user_id, self::DISMISS_META_KEY, '1' );
        }

        $redirect_url = remove_query_arg( array( 'ds_dismiss_onboard', '_wpnonce' ) );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * 处理 AJAX 形式的关闭通知请求。
     *
     * @return void
     */
    public function handle_dismiss_ajax() {
        check_ajax_referer( 'ds_dismiss_onboard_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( '权限不足', 'qiling' ) ) );
        }

        $user_id = get_current_user_id();
        if ( $user_id ) {
            update_user_meta( $user_id, self::DISMISS_META_KEY, '1' );
        }

        wp_send_json_success();
    }

    /**
     * 渲染全站高颜值新手引导横幅。
     *
     * @return void
     */
    public function render_onboarding_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( $this->is_dismissed() ) {
            return;
        }

        global $pagenow;
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';

        // 在模板中心和建站向导本页不重复显示引导横幅
        if ( 'developer-starter-template-center' === $page || 'developer-starter-setup-wizard' === $page ) {
            return;
        }

        $template_center_url = admin_url( 'admin.php?page=developer-starter-template-center' );
        $setup_wizard_url    = admin_url( 'admin.php?page=developer-starter-setup-wizard' );
        $dismiss_nonce       = wp_create_nonce( 'ds_dismiss_onboard_nonce' );
        $dismiss_url         = wp_nonce_url( add_query_arg( 'ds_dismiss_onboard', '1' ), 'ds_dismiss_onboard_nonce' );
        ?>
        <div class="notice notice-info qiling-onboard-notice" id="qiling-onboard-notice" style="position: relative; border-left-color: #2563eb; padding: 18px 24px; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 18px rgba(37, 99, 235, 0.08); margin: 20px 0;">
            <div style="display: flex; align-items: flex-start; gap: 18px;">
                <div style="width: 48px; height: 48px; min-width: 48px; border-radius: 12px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 24px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);">
                    🚀
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                            <?php esc_html_e( '欢迎使用启灵企业建站主题！推荐新手第 1 步：挑选并一键导入行业演示模板', 'qiling' ); ?>
                        </h3>
                        <a href="<?php echo esc_url( $dismiss_url ); ?>" class="qiling-onboard-dismiss-btn" data-nonce="<?php echo esc_attr( $dismiss_nonce ); ?>" style="color: #94a3b8; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;" title="<?php esc_attr_e( '不再提示', 'qiling' ); ?>">
                            <span>✕</span> <?php esc_html_e( '不再提示', 'qiling' ); ?>
                        </a>
                    </div>
                    <p style="margin: 0 0 14px; font-size: 13.5px; line-height: 1.6; color: #475569;">
                        <?php esc_html_e( '主题已为您内置 100+ 套覆盖外贸、珠宝轻奢、工业制造、科技互联网、律师咨询、餐饮美食等全行业的高品质演示模板。新手站长强烈建议先去挑选一套心仪模板一键导入，立享现成精美全套页面，立省 90% 建站时间！', 'qiling' ); ?>
                    </p>
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <a href="<?php echo esc_url( $template_center_url ); ?>" class="button button-primary" style="background: #2563eb; border-color: #2563eb; height: 36px; line-height: 34px; padding: 0 18px; font-size: 13px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);">
                            ✨ <?php esc_html_e( '前往官方模板中心挑选模板 (100+套)', 'qiling' ); ?> &rarr;
                        </a>
                        <a href="<?php echo esc_url( $setup_wizard_url ); ?>" class="button button-secondary" style="height: 36px; line-height: 34px; padding: 0 16px; font-size: 13px; border-radius: 6px;">
                            🧭 <?php esc_html_e( '启动 3 步建站向导', 'qiling' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <script>
            (function() {
                var btn = document.querySelector('.qiling-onboard-dismiss-btn');
                var notice = document.getElementById('qiling-onboard-notice');
                if (!btn || !notice) return;
                btn.addEventListener('click', function(e) {
                    if (window.fetch && window.FormData) {
                        e.preventDefault();
                        notice.style.transition = 'opacity 0.3s, transform 0.3s';
                        notice.style.opacity = '0';
                        notice.style.transform = 'translateY(-8px)';
                        setTimeout(function() { notice.remove(); }, 300);

                        var data = new FormData();
                        data.append('action', '<?php echo esc_js( self::DISMISS_ACTION ); ?>');
                        data.append('nonce', btn.getAttribute('data-nonce'));
                        fetch(ajaxurl, { method: 'POST', body: data });
                    }
                });
            })();
            </script>
        </div>
        <?php
    }

    /**
     * 在 WordPress 控制台仪表盘注册新手极速入门看板小工具。
     *
     * @return void
     */
    public function register_dashboard_widget() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_add_dashboard_widget(
            'qiling_onboarding_dashboard_widget',
            __( '🚀 启灵主题 · 新手建站极速指南', 'qiling' ),
            array( $this, 'render_dashboard_widget' )
        );
    }

    /**
     * 渲染仪表盘小工具内容。
     *
     * @return void
     */
    public function render_dashboard_widget() {
        $template_center_url = admin_url( 'admin.php?page=developer-starter-template-center' );
        $settings_url        = admin_url( 'admin.php?page=qiling-settings' );
        $pages_url           = admin_url( 'edit.php?post_type=page' );
        $home_url            = home_url( '/' );
        ?>
        <div class="qiling-dashboard-guide" style="font-size: 13px; line-height: 1.6; color: #334155;">
            <p style="margin-top: 0; font-size: 14px; font-weight: 600; color: #0f172a;">
                <?php esc_html_e( '新手站长建站推荐 3 步走路线：', 'qiling' ); ?>
            </p>
            <ol style="padding-left: 20px; margin: 0 0 16px;">
                <li style="margin-bottom: 8px;">
                    <strong><?php esc_html_e( '第一步：一键导入行业模板', 'qiling' ); ?></strong>
                    <span style="color: #64748b;"> - <?php esc_html_e( '挑选适合您行业的完整演示页面，一键设为首页。', 'qiling' ); ?></span>
                    <a href="<?php echo esc_url( $template_center_url ); ?>" style="font-weight: 600; margin-left: 4px;"><?php esc_html_e( '前往模板中心 &rarr;', 'qiling' ); ?></a>
                </li>
                <li style="margin-bottom: 8px;">
                    <strong><?php esc_html_e( '第二步：前台可视化替换图文', 'qiling' ); ?></strong>
                    <span style="color: #64748b;"> - <?php esc_html_e( '在前台页面点击“前台装修”，像修改 PPT 一样替换文字与图片。', 'qiling' ); ?></span>
                    <a href="<?php echo esc_url( $pages_url ); ?>" style="margin-left: 4px;"><?php esc_html_e( '查看我的页面 &rarr;', 'qiling' ); ?></a>
                </li>
                <li style="margin-bottom: 0;">
                    <strong><?php esc_html_e( '第三步：设置 Logo、电话与微信', 'qiling' ); ?></strong>
                    <span style="color: #64748b;"> - <?php esc_html_e( '在主题设置中配置公司 Logo、客服电话和手机抽屉微信客服。', 'qiling' ); ?></span>
                    <a href="<?php echo esc_url( $settings_url ); ?>" style="margin-left: 4px;"><?php esc_html_e( '进入主题设置 &rarr;', 'qiling' ); ?></a>
                </li>
            </ol>
            <div style="border-top: 1px solid #f1f5f9; padding-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                <a href="<?php echo esc_url( $template_center_url ); ?>" class="button button-primary" style="background: #2563eb; border-color: #2563eb;">
                    ✨ <?php esc_html_e( '去模板中心挑选模板', 'qiling' ); ?>
                </a>
                <a href="<?php echo esc_url( $home_url ); ?>" target="_blank" rel="noopener noreferrer" style="color: #64748b; text-decoration: none;">
                    <?php esc_html_e( '预览网站首页', 'qiling' ); ?> &nearr;
                </a>
            </div>
        </div>
        <?php
    }
}
