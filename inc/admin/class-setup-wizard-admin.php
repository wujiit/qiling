<?php
/**
 * Setup wizard admin shell.
 *
 * Phase 7 adds safe cleanup for setup-wizard generated records.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

use Developer_Starter\Core\Setup_Wizard_Cleanup_Service;
use Developer_Starter\Core\Setup_Wizard_Plugin_Detector;
use Developer_Starter\Core\Setup_Wizard_Import_Service;
use Developer_Starter\Core\Setup_Wizard_Presets;
use Developer_Starter\Core\Setup_Wizard_Settings_Service;
use Developer_Starter\Core\Setup_Wizard_State;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_Admin {

    const PAGE_SLUG = 'developer-starter-setup-wizard';
    const NONCE_ACTION = 'developer_starter_setup_wizard';

    /**
     * @var string
     */
    private $hook_suffix = '';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_submenu_page' ), 12 );
        add_action( 'admin_init', array( $this, 'maybe_redirect_after_activation' ), 5 );
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_entry' ), 85 );
    }

    /**
     * Register submenu under theme settings.
     *
     * @return void
     */
    public function register_submenu_page() {
        $hook = add_submenu_page(
            'developer-starter-settings',
            __( '启灵建站向导', 'developer-starter' ),
            __( '建站向导', 'developer-starter' ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );

        if ( is_string( $hook ) && '' !== $hook ) {
            $this->hook_suffix = $hook;
            add_action( 'load-' . $hook, array( $this, 'maybe_handle_request' ) );
        }
    }

    /**
     * Add lightweight admin bar entry.
     *
     * @param \WP_Admin_Bar $wp_admin_bar Admin bar.
     * @return void
     */
    public function add_admin_bar_entry( $wp_admin_bar ) {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $wp_admin_bar->add_node(
            array(
                'id'     => 'developer-starter-setup-wizard',
                'title'  => '<span class="ab-icon dashicons dashicons-admin-home" style="margin-top:2px;"></span><span class="ab-label">' . esc_html__( '启灵建站', 'developer-starter' ) . '</span>',
                'href'   => admin_url( 'admin.php?page=' . self::PAGE_SLUG ),
                'parent' => 'developer-starter-settings',
            )
        );
    }

    /**
     * Redirect once after theme activation.
     *
     * @return void
     */
    public function maybe_redirect_after_activation() {
        if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $pagenow;
        if ( in_array( (string) $pagenow, array( 'admin-post.php', 'async-upload.php', 'customize.php' ), true ) ) {
            return;
        }

        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
        if ( self::PAGE_SLUG === $page ) {
            return;
        }

        $state_service = $this->get_state_service();
        if ( ! $state_service->has_activation_redirect_pending() ) {
            return;
        }

        $state = $state_service->get_state();
        if ( ! empty( $state['completed'] ) || ! empty( $state['skipped'] ) ) {
            $state_service->consume_activation_redirect_pending();
            return;
        }

        $state_service->consume_activation_redirect_pending();
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'            => self::PAGE_SLUG,
                    'from_activation' => '1',
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Handle POST actions before render.
     *
     * @return void
     */
    public function maybe_handle_request() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'developer-starter' ) );
        }

        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) ) : '';
        if ( 'POST' !== $method ) {
            return;
        }

        check_admin_referer( self::NONCE_ACTION );

        $action = isset( $_POST['developer_starter_setup_wizard_action'] )
            ? sanitize_key( wp_unslash( (string) $_POST['developer_starter_setup_wizard_action'] ) )
            : '';

        switch ( $action ) {
            case 'save_step':
                $this->handle_save_step();
                break;
            case 'generate_pages':
                $this->handle_generate_pages();
                break;
            case 'apply_basics':
                $this->handle_apply_basics();
                break;
            case 'skip':
                $this->handle_skip();
                break;
            case 'complete':
                $this->handle_complete();
                break;
            case 'cleanup_last_run':
                $this->handle_cleanup_last_run();
                break;
        }
    }

    /**
     * Render wizard page.
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'developer-starter' ) );
        }

        $step         = $this->get_current_step();
        $state        = $this->get_state_service()->get_state();
        $draft        = $this->get_state_service()->get_draft();
        $site_summary = $this->get_existing_site_summary();

        ?>
        <div class="wrap qiling-setup-wizard">
            <h1><?php esc_html_e( '启灵建站向导', 'developer-starter' ); ?></h1>
            <p class="description"><?php esc_html_e( '这个向导只做首次配置辅助，所有结果都会回到启灵主题设置、页面、菜单和模板系统里继续修改。', 'developer-starter' ); ?></p>

            <?php $this->render_notices(); ?>
            <?php $this->render_styles(); ?>

            <div class="qsw-shell">
                <aside class="qsw-sidebar">
                    <?php $this->render_progress( $step ); ?>
                    <?php $this->render_state_card( $state ); ?>
                </aside>

                <main class="qsw-main">
                    <?php
                    if ( 'type' === $step ) {
                        $this->render_type_step( $draft );
                    } elseif ( 'pages' === $step ) {
                        $this->render_pages_step( $draft, $state );
                    } elseif ( 'basics' === $step ) {
                        $this->render_basics_step( $draft, $state );
                    } elseif ( 'plugins' === $step ) {
                        $this->render_plugins_step();
                    } elseif ( 'finish' === $step ) {
                        $this->render_finish_step( $state, $draft );
                    } else {
                        $this->render_welcome_step( $site_summary );
                    }
                    ?>
                </main>
            </div>
        </div>
        <?php
    }

    /**
     * Handle draft save.
     *
     * @return void
     */
    private function handle_save_step() {
        $draft = $this->get_state_service()->get_draft();

        $current_step = isset( $_POST['current_step'] ) ? sanitize_key( wp_unslash( (string) $_POST['current_step'] ) ) : 'welcome';
        if ( 'type' === $current_step ) {
            $preset = $this->get_presets_service()->resolve(
                isset( $_POST['site_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['site_type'] ) ) : '',
                isset( $_POST['industry'] ) ? sanitize_key( wp_unslash( (string) $_POST['industry'] ) ) : ''
            );

            $draft['site_type'] = isset( $preset['site_type'] ) ? $preset['site_type'] : '';
            $draft['industry']  = isset( $preset['industry'] ) ? $preset['industry'] : '';
        } elseif ( 'pages' === $current_step ) {
            $draft = $this->populate_pages_draft_from_request( $draft );
        } elseif ( 'basics' === $current_step ) {
            $draft = $this->populate_basics_draft_from_request( $draft );
        }

        $draft['current_step'] = $current_step;
        $draft['updated_at']   = time();
        $this->get_state_service()->save_draft( $draft );

        $next_step = isset( $_POST['next_step'] ) ? sanitize_key( wp_unslash( (string) $_POST['next_step'] ) ) : 'type';
        $this->redirect_to_step( $next_step, array( 'saved' => '1' ) );
    }

    /**
     * Handle phase 3 page generation.
     *
     * @return void
     */
    private function handle_generate_pages() {
        $draft = $this->populate_pages_draft_from_request( $this->get_state_service()->get_draft() );
        $draft['current_step'] = 'pages';
        $draft['updated_at']   = time();
        $this->get_state_service()->save_draft( $draft );

        $result = $this->get_import_service()->generate_pages(
            array(
                'site_type'           => isset( $draft['site_type'] ) ? $draft['site_type'] : '',
                'industry'            => isset( $draft['industry'] ) ? $draft['industry'] : '',
                'template_id'         => isset( $draft['template_id'] ) ? $draft['template_id'] : '',
                'selected_pages'      => isset( $draft['selected_pages'] ) ? $draft['selected_pages'] : array(),
                'include_auth_pages'  => ! empty( $draft['options']['include_auth_pages'] ),
                'set_front_page'      => ! empty( $draft['options']['set_front_page'] ),
                'import_home_modules' => ! empty( $draft['options']['import_home_modules'] ),
            )
        );

        $this->redirect_to_step(
            'basics',
            array(
                'pages_generated' => '1',
                'created'         => isset( $result['created_pages'] ) && is_array( $result['created_pages'] ) ? count( $result['created_pages'] ) : 0,
                'reused'          => isset( $result['reused_pages'] ) && is_array( $result['reused_pages'] ) ? count( $result['reused_pages'] ) : 0,
                'errors'          => isset( $result['errors'] ) && is_array( $result['errors'] ) ? count( $result['errors'] ) : 0,
                'front_page'      => isset( $result['front_page']['status'] ) ? sanitize_key( (string) $result['front_page']['status'] ) : 'skipped',
                'modules_filled'  => ! empty( $result['modules_filled'] ) ? '1' : '0',
            )
        );
    }

    /**
     * Handle phase 4 menu and basic settings.
     *
     * @return void
     */
    private function handle_apply_basics() {
        $draft = $this->populate_basics_draft_from_request( $this->get_state_service()->get_draft() );
        $draft['current_step'] = 'basics';
        $draft['updated_at']   = time();
        $this->get_state_service()->save_draft( $draft );

        $result = $this->get_settings_service()->apply(
            array(
                'site_type'            => isset( $draft['site_type'] ) ? $draft['site_type'] : '',
                'industry'             => isset( $draft['industry'] ) ? $draft['industry'] : '',
                'selected_pages'       => isset( $draft['selected_pages'] ) ? $draft['selected_pages'] : array(),
                'brand'                => isset( $draft['brand'] ) ? $draft['brand'] : array(),
                'contact'              => isset( $draft['contact'] ) ? $draft['contact'] : array(),
                'seo'                  => isset( $draft['options']['seo'] ) ? $draft['options']['seo'] : array(),
                'create_primary_menu'  => ! empty( $draft['options']['create_primary_menu'] ),
                'overwrite_existing'   => ! empty( $draft['options']['overwrite_existing'] ),
            )
        );

        $settings = isset( $result['settings'] ) && is_array( $result['settings'] ) ? $result['settings'] : array();
        $menu     = isset( $result['menu'] ) && is_array( $result['menu'] ) ? $result['menu'] : array();

        $this->redirect_to_step(
            'plugins',
            array(
                'basics_applied' => '1',
                'settings'       => isset( $settings['updated'] ) && is_array( $settings['updated'] ) ? count( $settings['updated'] ) : 0,
                'settings_skip'  => isset( $settings['skipped'] ) && is_array( $settings['skipped'] ) ? count( $settings['skipped'] ) : 0,
                'menu_status'    => isset( $menu['status'] ) ? sanitize_key( (string) $menu['status'] ) : 'skipped',
                'menu_items'     => isset( $menu['items_added'] ) ? absint( $menu['items_added'] ) : 0,
                'errors'         => isset( $result['errors'] ) && is_array( $result['errors'] ) ? count( $result['errors'] ) : 0,
            )
        );
    }

    /**
     * Handle skip.
     *
     * @return void
     */
    private function handle_skip() {
        $this->get_state_service()->mark_skipped(
            array(
                'detected_plugins' => $this->get_plugin_detector()->get_status_snapshot(),
            )
        );

        $this->redirect_to_step( 'welcome', array( 'wizard_skipped' => '1' ) );
    }

    /**
     * Handle completion.
     *
     * @return void
     */
    private function handle_complete() {
        $draft = $this->get_state_service()->get_draft();
        $existing_state = $this->get_state_service()->get_state();
        $preset = $this->get_presets_service()->resolve(
            isset( $draft['site_type'] ) ? $draft['site_type'] : ( isset( $existing_state['site_type'] ) ? $existing_state['site_type'] : '' ),
            isset( $draft['industry'] ) ? $draft['industry'] : ( isset( $existing_state['industry'] ) ? $existing_state['industry'] : '' )
        );
        $template_id = ! empty( $draft['template_id'] ) ? sanitize_key( (string) $draft['template_id'] ) : ( isset( $existing_state['template_id'] ) ? sanitize_key( (string) $existing_state['template_id'] ) : '' );

        $this->get_state_service()->mark_completed(
            array(
                'site_type'        => isset( $preset['site_type'] ) ? $preset['site_type'] : '',
                'industry'         => isset( $preset['industry'] ) ? $preset['industry'] : '',
                'template_id'      => $template_id,
                'enabled_theme_models' => isset( $preset['content_model_keys'] ) ? $preset['content_model_keys'] : array(),
                'detected_plugins' => $this->get_plugin_detector()->get_status_snapshot(),
            )
        );

        $this->redirect_to_step( 'finish', array( 'wizard_completed' => '1' ) );
    }

    /**
     * Handle phase 7 safe cleanup.
     *
     * @return void
     */
    private function handle_cleanup_last_run() {
        $result = $this->get_cleanup_service()->cleanup(
            array(
                'confirm'        => ! empty( $_POST['cleanup_confirm'] ),
                'trash_pages'    => ! empty( $_POST['trash_pages'] ),
                'delete_menus'   => ! empty( $_POST['delete_menus'] ),
                'reset_tracking' => ! empty( $_POST['reset_tracking'] ),
                'delete_draft'   => ! empty( $_POST['delete_draft'] ),
            )
        );

        $this->redirect_to_step(
            'finish',
            array(
                'cleanup_done'  => '1',
                'trash_pages'   => isset( $result['trashed_pages'] ) ? absint( $result['trashed_pages'] ) : 0,
                'skip_pages'    => isset( $result['skipped_pages'] ) ? absint( $result['skipped_pages'] ) : 0,
                'delete_menus'  => isset( $result['deleted_menus'] ) ? absint( $result['deleted_menus'] ) : 0,
                'skip_menus'    => isset( $result['skipped_menus'] ) ? absint( $result['skipped_menus'] ) : 0,
                'draft_deleted' => ! empty( $result['draft_deleted'] ) ? '1' : '0',
                'records_reset' => ! empty( $result['records_reset'] ) ? '1' : '0',
                'errors'        => isset( $result['errors'] ) && is_array( $result['errors'] ) ? count( $result['errors'] ) : 0,
            )
        );
    }

    /**
     * Render welcome step.
     *
     * @param array<string,mixed> $site_summary Existing site summary.
     * @return void
     */
    private function render_welcome_step( $site_summary ) {
        ?>
        <section class="qsw-panel">
            <div class="qsw-panel-head">
                <span><?php esc_html_e( '步骤 1', 'developer-starter' ); ?></span>
                <h2><?php esc_html_e( '欢迎使用启灵建站向导', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '当前向导会根据站点类型和行业给出模板、页面、内容模型建议；只有点击生成页面时才会创建缺失页面，不会安装或配置第三方插件。', 'developer-starter' ); ?></p>
            </div>

            <div class="qsw-feature-grid">
                <div><strong><?php esc_html_e( '不锁死配置', 'developer-starter' ); ?></strong><span><?php esc_html_e( '后续所有设置都能回到主题设置、页面和菜单里单独修改。', 'developer-starter' ); ?></span></div>
                <div><strong><?php esc_html_e( '默认不覆盖', 'developer-starter' ); ?></strong><span><?php esc_html_e( '已有首页、页面和菜单会在后续阶段优先复用。', 'developer-starter' ); ?></span></div>
                <div><strong><?php esc_html_e( '插件只提示', 'developer-starter' ); ?></strong><span><?php esc_html_e( 'WooCommerce、积分商城、微信登录等只检测状态，不安装不配置。', 'developer-starter' ); ?></span></div>
            </div>

            <div class="qsw-existing">
                <h3><?php esc_html_e( '当前站点检测', 'developer-starter' ); ?></h3>
                <ul>
                    <li><?php echo esc_html( sprintf( __( '页面数量：%d', 'developer-starter' ), absint( $site_summary['pages'] ) ) ); ?></li>
                    <li><?php echo esc_html( sprintf( __( '菜单数量：%d', 'developer-starter' ), absint( $site_summary['menus'] ) ) ); ?></li>
                    <li><?php echo esc_html( ! empty( $site_summary['front_page_title'] ) ? sprintf( __( '当前首页：%s', 'developer-starter' ), $site_summary['front_page_title'] ) : __( '当前首页：尚未设置静态首页', 'developer-starter' ) ); ?></li>
                </ul>
                <?php if ( ! empty( $site_summary['has_content'] ) ) : ?>
                    <p class="qsw-warning"><?php esc_html_e( '已检测到站点存在内容。向导默认不会覆盖已有内容。', 'developer-starter' ); ?></p>
                <?php endif; ?>
            </div>

            <div class="qsw-actions">
                <?php $this->render_step_link( 'type', __( '开始配置', 'developer-starter' ), 'button button-primary button-hero' ); ?>
                <a class="button button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=developer-starter-template-center' ) ); ?>"><?php esc_html_e( '查看模板中心', 'developer-starter' ); ?></a>
                <?php $this->render_action_form( 'skip', __( '跳过向导', 'developer-starter' ), 'button-link qsw-danger-link' ); ?>
            </div>
        </section>
        <?php
    }

    /**
     * Render site type step.
     *
     * @param array<string,mixed> $draft Draft.
     * @return void
     */
    private function render_type_step( $draft ) {
        $preset = $this->get_presets_service()->resolve(
            isset( $draft['site_type'] ) ? $draft['site_type'] : '',
            isset( $draft['industry'] ) ? $draft['industry'] : ''
        );
        $site_type = isset( $preset['site_type'] ) ? sanitize_key( (string) $preset['site_type'] ) : 'corporate';
        $industry  = isset( $preset['industry'] ) ? sanitize_key( (string) $preset['industry'] ) : 'general';
        ?>
        <section class="qsw-panel">
            <div class="qsw-panel-head">
                <span><?php esc_html_e( '步骤 2', 'developer-starter' ); ?></span>
                <h2><?php esc_html_e( '选择站点类型', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '这里会先生成推荐预览：模板、页面、内容模型和主题能力都只是建议，不会立即写入站点。', 'developer-starter' ); ?></p>
            </div>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>">
                <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                <input type="hidden" name="developer_starter_setup_wizard_action" value="save_step" />
                <input type="hidden" name="current_step" value="type" />
                <input type="hidden" name="next_step" value="pages" />

                <h3><?php esc_html_e( '建站类型', 'developer-starter' ); ?></h3>
                <div class="qsw-choice-grid">
                    <?php foreach ( $this->get_site_type_choices() as $key => $label ) : ?>
                        <label class="qsw-choice">
                            <input type="radio" name="site_type" value="<?php echo esc_attr( $key ); ?>" <?php checked( $site_type, $key ); ?> />
                            <span><?php echo esc_html( $label ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h3><?php esc_html_e( '行业/场景', 'developer-starter' ); ?></h3>
                <div class="qsw-choice-grid">
                    <?php foreach ( $this->get_industry_choices() as $key => $label ) : ?>
                        <label class="qsw-choice">
                            <input type="radio" name="industry" value="<?php echo esc_attr( $key ); ?>" <?php checked( $industry, $key ); ?> />
                            <span><?php echo esc_html( $label ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?php $this->render_preset_preview( $preset ); ?>

                <div class="qsw-actions">
                    <?php $this->render_step_link( 'welcome', __( '返回', 'developer-starter' ), 'button button-hero' ); ?>
                    <button type="submit" class="button button-primary button-hero"><?php esc_html_e( '保存并继续', 'developer-starter' ); ?></button>
                </div>
            </form>
        </section>
        <?php
    }

    /**
     * Render page generation step.
     *
     * @param array<string,mixed> $draft Draft.
     * @param array<string,mixed> $state State.
     * @return void
     */
    private function render_pages_step( $draft, $state ) {
        $preset = $this->get_presets_service()->resolve(
            isset( $draft['site_type'] ) ? $draft['site_type'] : '',
            isset( $draft['industry'] ) ? $draft['industry'] : ''
        );
        $pages = isset( $preset['recommended_pages'] ) && is_array( $preset['recommended_pages'] ) ? $preset['recommended_pages'] : array();
        $templates = isset( $preset['recommended_templates'] ) && is_array( $preset['recommended_templates'] ) ? $preset['recommended_templates'] : array();
        $selected_pages = $this->get_selected_pages_for_render( $draft, $pages );
        $template_id = $this->get_selected_template_id_for_render( $draft, $templates );
        $options = isset( $draft['options'] ) && is_array( $draft['options'] ) ? $draft['options'] : array();
        $include_auth_pages = array_key_exists( 'include_auth_pages', $options ) ? ! empty( $options['include_auth_pages'] ) : true;
        $set_front_page = array_key_exists( 'set_front_page', $options ) ? ! empty( $options['set_front_page'] ) : true;
        $import_home_modules = array_key_exists( 'import_home_modules', $options ) ? ! empty( $options['import_home_modules'] ) : true;
        ?>
        <section class="qsw-panel">
            <div class="qsw-panel-head">
                <span><?php esc_html_e( '步骤 3', 'developer-starter' ); ?></span>
                <h2><?php esc_html_e( '生成页面结构', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '默认优先复用已有页面，同 slug、同标题或同模板页面不会重复创建；只有缺失的页面才会新建。', 'developer-starter' ); ?></p>
            </div>

            <?php $this->render_last_run_card( $state ); ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>">
                <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                <input type="hidden" name="developer_starter_setup_wizard_action" value="generate_pages" />
                <input type="hidden" name="current_step" value="pages" />

                <h3><?php esc_html_e( '首页模板', 'developer-starter' ); ?></h3>
                <div class="qsw-template-grid">
                    <?php foreach ( $templates as $template ) : ?>
                        <?php $id = isset( $template['id'] ) ? sanitize_key( (string) $template['id'] ) : ''; ?>
                        <?php if ( '' === $id ) : ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <label class="qsw-template-choice">
                            <input type="radio" name="template_id" value="<?php echo esc_attr( $id ); ?>" <?php checked( $template_id, $id ); ?> />
                            <strong><?php echo esc_html( isset( $template['label'] ) ? $template['label'] : $id ); ?></strong>
                            <?php if ( ! empty( $template['package'] ) ) : ?>
                                <span><?php echo esc_html( $template['package'] ); ?></span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h3><?php esc_html_e( '要创建或复用的页面', 'developer-starter' ); ?></h3>
                <div class="qsw-page-grid">
                    <?php foreach ( $pages as $page ) : ?>
                        <?php $id = isset( $page['id'] ) ? sanitize_key( (string) $page['id'] ) : ''; ?>
                        <?php if ( '' === $id ) : ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <label class="qsw-page-choice">
                            <input type="checkbox" name="selected_pages[]" value="<?php echo esc_attr( $id ); ?>" <?php checked( in_array( $id, $selected_pages, true ) ); ?> />
                            <span>
                                <strong><?php echo esc_html( isset( $page['label'] ) ? $page['label'] : $id ); ?></strong>
                                <?php if ( ! empty( $page['slug'] ) ) : ?>
                                    <em><?php echo esc_html( $page['slug'] ); ?></em>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="qsw-option-list">
                    <label>
                        <input type="checkbox" name="include_auth_pages" value="1" <?php checked( $include_auth_pages ); ?> />
                        <span><?php esc_html_e( '补齐登录、注册、个人中心页面', 'developer-starter' ); ?></span>
                    </label>
                    <label>
                        <input type="checkbox" name="set_front_page" value="1" <?php checked( $set_front_page ); ?> />
                        <span><?php esc_html_e( '如果当前没有静态首页，则把“首页”设为站点首页', 'developer-starter' ); ?></span>
                    </label>
                    <label>
                        <input type="checkbox" name="import_home_modules" value="1" <?php checked( $import_home_modules ); ?> />
                        <span><?php esc_html_e( '为新创建的首页导入官方模板模块', 'developer-starter' ); ?></span>
                    </label>
                </div>

                <div class="qsw-note">
                    <?php esc_html_e( '三阶段不会创建菜单、不会写入品牌/SEO/页脚设置，也不会覆盖已有页面内容。菜单和基础设置会放到下一阶段继续做。', 'developer-starter' ); ?>
                </div>

                <div class="qsw-actions">
                    <?php $this->render_step_link( 'type', __( '返回', 'developer-starter' ), 'button button-hero' ); ?>
                    <button type="submit" class="button button-primary button-hero"><?php esc_html_e( '生成页面并继续', 'developer-starter' ); ?></button>
                    <?php $this->render_step_link( 'basics', __( '暂不生成，继续设置', 'developer-starter' ), 'button button-hero' ); ?>
                </div>
            </form>
        </section>
        <?php
    }

    /**
     * Render menu and basic settings step.
     *
     * @param array<string,mixed> $draft Draft.
     * @param array<string,mixed> $state State.
     * @return void
     */
    private function render_basics_step( $draft, $state ) {
        $defaults = $this->get_basic_settings_defaults( $draft );
        $brand    = isset( $draft['brand'] ) && is_array( $draft['brand'] ) ? array_merge( $defaults['brand'], $draft['brand'] ) : $defaults['brand'];
        $contact  = isset( $draft['contact'] ) && is_array( $draft['contact'] ) ? array_merge( $defaults['contact'], $draft['contact'] ) : $defaults['contact'];
        $options  = isset( $draft['options'] ) && is_array( $draft['options'] ) ? $draft['options'] : array();
        $seo      = isset( $options['seo'] ) && is_array( $options['seo'] ) ? array_merge( $defaults['seo'], $options['seo'] ) : $defaults['seo'];
        $create_primary_menu = array_key_exists( 'create_primary_menu', $options ) ? ! empty( $options['create_primary_menu'] ) : true;
        $overwrite_existing  = ! empty( $options['overwrite_existing'] );
        ?>
        <section class="qsw-panel">
            <div class="qsw-panel-head">
                <span><?php esc_html_e( '步骤 4', 'developer-starter' ); ?></span>
                <h2><?php esc_html_e( '菜单与基础设置', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '四阶段会创建或绑定主菜单，并把品牌、联系、页脚和 SEO 基础信息写入主题已有设置；默认只填空项。', 'developer-starter' ); ?></p>
            </div>

            <?php $this->render_last_run_card( $state ); ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>">
                <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                <input type="hidden" name="developer_starter_setup_wizard_action" value="apply_basics" />
                <input type="hidden" name="current_step" value="basics" />

                <div class="qsw-form-grid">
                    <div class="qsw-field">
                        <label for="qsw-site-title"><?php esc_html_e( '站点名称', 'developer-starter' ); ?></label>
                        <input id="qsw-site-title" type="text" name="brand[site_title]" value="<?php echo esc_attr( isset( $brand['site_title'] ) ? $brand['site_title'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-tagline"><?php esc_html_e( '副标题', 'developer-starter' ); ?></label>
                        <input id="qsw-tagline" type="text" name="brand[tagline]" value="<?php echo esc_attr( isset( $brand['tagline'] ) ? $brand['tagline'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-logo"><?php esc_html_e( 'Logo 图片 URL', 'developer-starter' ); ?></label>
                        <input id="qsw-logo" type="url" name="brand[site_logo]" value="<?php echo esc_attr( isset( $brand['site_logo'] ) ? $brand['site_logo'] : '' ); ?>" placeholder="https://example.com/logo.png" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-mobile-logo"><?php esc_html_e( '移动端 Logo URL', 'developer-starter' ); ?></label>
                        <input id="qsw-mobile-logo" type="url" name="brand[mobile_logo]" value="<?php echo esc_attr( isset( $brand['mobile_logo'] ) ? $brand['mobile_logo'] : '' ); ?>" placeholder="https://example.com/mobile-logo.png" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-primary-color"><?php esc_html_e( '品牌主色', 'developer-starter' ); ?></label>
                        <input id="qsw-primary-color" type="text" name="brand[primary_color]" value="<?php echo esc_attr( isset( $brand['primary_color'] ) ? $brand['primary_color'] : '' ); ?>" placeholder="#2563eb" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-company-name"><?php esc_html_e( '企业/组织名称', 'developer-starter' ); ?></label>
                        <input id="qsw-company-name" type="text" name="contact[company_name]" value="<?php echo esc_attr( isset( $contact['company_name'] ) ? $contact['company_name'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-company-phone"><?php esc_html_e( '联系电话', 'developer-starter' ); ?></label>
                        <input id="qsw-company-phone" type="text" name="contact[company_phone]" value="<?php echo esc_attr( isset( $contact['company_phone'] ) ? $contact['company_phone'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-company-email"><?php esc_html_e( '联系邮箱', 'developer-starter' ); ?></label>
                        <input id="qsw-company-email" type="email" name="contact[company_email]" value="<?php echo esc_attr( isset( $contact['company_email'] ) ? $contact['company_email'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field qsw-field-wide">
                        <label for="qsw-company-address"><?php esc_html_e( '企业地址', 'developer-starter' ); ?></label>
                        <textarea id="qsw-company-address" name="contact[company_address]" rows="2"><?php echo esc_textarea( isset( $contact['company_address'] ) ? $contact['company_address'] : '' ); ?></textarea>
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-working-hours"><?php esc_html_e( '工作时间', 'developer-starter' ); ?></label>
                        <input id="qsw-working-hours" type="text" name="contact[company_working_hours]" value="<?php echo esc_attr( isset( $contact['company_working_hours'] ) ? $contact['company_working_hours'] : '' ); ?>" placeholder="<?php esc_attr_e( '周一至周五 9:00-18:00', 'developer-starter' ); ?>" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-icp"><?php esc_html_e( 'ICP 备案号', 'developer-starter' ); ?></label>
                        <input id="qsw-icp" type="text" name="contact[icp_number]" value="<?php echo esc_attr( isset( $contact['icp_number'] ) ? $contact['icp_number'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-police"><?php esc_html_e( '公安备案号', 'developer-starter' ); ?></label>
                        <input id="qsw-police" type="text" name="contact[police_number]" value="<?php echo esc_attr( isset( $contact['police_number'] ) ? $contact['police_number'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field qsw-field-wide">
                        <label for="qsw-company-brief"><?php esc_html_e( '页脚简介', 'developer-starter' ); ?></label>
                        <textarea id="qsw-company-brief" name="contact[company_brief]" rows="3"><?php echo esc_textarea( isset( $contact['company_brief'] ) ? $contact['company_brief'] : '' ); ?></textarea>
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-seo-title"><?php esc_html_e( 'SEO 默认标题', 'developer-starter' ); ?></label>
                        <input id="qsw-seo-title" type="text" name="seo[default_title]" value="<?php echo esc_attr( isset( $seo['default_title'] ) ? $seo['default_title'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field">
                        <label for="qsw-seo-keywords"><?php esc_html_e( 'SEO 默认关键词', 'developer-starter' ); ?></label>
                        <input id="qsw-seo-keywords" type="text" name="seo[default_keywords]" value="<?php echo esc_attr( isset( $seo['default_keywords'] ) ? $seo['default_keywords'] : '' ); ?>" />
                    </div>
                    <div class="qsw-field qsw-field-wide">
                        <label for="qsw-seo-description"><?php esc_html_e( 'SEO 默认描述', 'developer-starter' ); ?></label>
                        <textarea id="qsw-seo-description" name="seo[default_description]" rows="3"><?php echo esc_textarea( isset( $seo['default_description'] ) ? $seo['default_description'] : '' ); ?></textarea>
                    </div>
                </div>

                <div class="qsw-option-list">
                    <label>
                        <input type="checkbox" name="create_primary_menu" value="1" <?php checked( $create_primary_menu ); ?> />
                        <span><?php esc_html_e( '如果主导航位置为空，则创建并绑定“启灵主菜单”', 'developer-starter' ); ?></span>
                    </label>
                    <label>
                        <input type="checkbox" name="schema_engine_enable" value="1" <?php checked( ! empty( $seo['schema_engine_enable'] ) ); ?> />
                        <span><?php esc_html_e( '启用主题行业 Schema 引擎', 'developer-starter' ); ?></span>
                    </label>
                    <label>
                        <input type="checkbox" name="overwrite_existing" value="1" <?php checked( $overwrite_existing ); ?> />
                        <span><?php esc_html_e( '覆盖已有站点标题、主题设置和 SEO 基础字段', 'developer-starter' ); ?></span>
                    </label>
                </div>

                <div class="qsw-note">
                    <?php esc_html_e( '默认不会替换已有主菜单，也不会覆盖已有主题设置；Logo 字段沿用主题设置页的图片 URL 格式。', 'developer-starter' ); ?>
                </div>

                <div class="qsw-actions">
                    <?php $this->render_step_link( 'pages', __( '返回', 'developer-starter' ), 'button button-hero' ); ?>
                    <button type="submit" class="button button-primary button-hero"><?php esc_html_e( '应用设置并继续', 'developer-starter' ); ?></button>
                    <?php $this->render_step_link( 'plugins', __( '暂不应用，继续检测插件', 'developer-starter' ), 'button button-hero' ); ?>
                </div>
            </form>
        </section>
        <?php
    }

    /**
     * Render plugin detection step.
     *
     * @return void
     */
    private function render_plugins_step() {
        $context             = $this->get_plugin_detection_context();
        $preset              = isset( $context['preset'] ) && is_array( $context['preset'] ) ? $context['preset'] : array();
        $recommended_plugins = isset( $context['recommended_plugins'] ) && is_array( $context['recommended_plugins'] ) ? $context['recommended_plugins'] : array();
        $other_plugins       = isset( $context['other_plugins'] ) && is_array( $context['other_plugins'] ) ? $context['other_plugins'] : array();
        $counts              = isset( $context['counts'] ) && is_array( $context['counts'] ) ? $context['counts'] : array();
        ?>
        <section class="qsw-panel">
            <div class="qsw-panel-head">
                <span><?php esc_html_e( '步骤 5', 'developer-starter' ); ?></span>
                <h2><?php esc_html_e( '可选增强能力检测', 'developer-starter' ); ?></h2>
                <p>
                    <?php
                    echo esc_html(
                        sprintf(
                            __( '当前方案：%1$s / %2$s。这里只读检测第三方插件状态，不提供安装、启用或配置操作。', 'developer-starter' ),
                            isset( $preset['site_type_label'] ) ? $preset['site_type_label'] : __( '站点类型', 'developer-starter' ),
                            isset( $preset['industry_label'] ) ? $preset['industry_label'] : __( '行业场景', 'developer-starter' )
                        )
                    );
                    ?>
                </p>
            </div>

            <div class="qsw-summary-grid">
                <div><strong><?php esc_html_e( '推荐增强', 'developer-starter' ); ?></strong><span><?php echo esc_html( sprintf( __( '%d 项', 'developer-starter' ), isset( $counts['recommended'] ) ? absint( $counts['recommended'] ) : 0 ) ); ?></span></div>
                <div><strong><?php esc_html_e( '已可使用', 'developer-starter' ); ?></strong><span><?php echo esc_html( sprintf( __( '%d 项已启用', 'developer-starter' ), isset( $counts['active'] ) ? absint( $counts['active'] ) : 0 ) ); ?></span></div>
                <div><strong><?php esc_html_e( '待人工处理', 'developer-starter' ); ?></strong><span><?php echo esc_html( sprintf( __( '%d 项未安装或未启用', 'developer-starter' ), isset( $counts['needs_attention'] ) ? absint( $counts['needs_attention'] ) : 0 ) ); ?></span></div>
            </div>

            <div class="qsw-plugin-section">
                <h3><?php esc_html_e( '本方案推荐', 'developer-starter' ); ?></h3>
                <?php if ( empty( $recommended_plugins ) ) : ?>
                    <p class="qsw-muted"><?php esc_html_e( '这个方案不依赖额外插件，主题能力已经可以完成基础建站。', 'developer-starter' ); ?></p>
                <?php else : ?>
                    <div class="qsw-plugin-list">
                        <?php foreach ( $recommended_plugins as $plugin ) : ?>
                            <?php $this->render_plugin_detection_item( $plugin, true ); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $other_plugins ) ) : ?>
                <div class="qsw-plugin-section">
                    <h3><?php esc_html_e( '其他可选能力', 'developer-starter' ); ?></h3>
                    <p class="qsw-muted"><?php esc_html_e( '下面这些不是当前方案必需项，只显示安装状态，方便后续扩展时参考。', 'developer-starter' ); ?></p>
                    <div class="qsw-plugin-list qsw-plugin-list-compact">
                        <?php foreach ( $other_plugins as $plugin ) : ?>
                            <?php $this->render_plugin_detection_item( $plugin, false ); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="qsw-note">
                <?php esc_html_e( '检测只读取插件文件、启用状态和运行时标识；向导不会安装插件、启用插件、停用插件，也不会写入 WooCommerce、积分商城、微信登录或表单插件的配置。完成向导时只保存一份很小的状态快照，且不自动加载。', 'developer-starter' ); ?>
            </div>

            <div class="qsw-actions">
                <?php $this->render_step_link( 'basics', __( '返回', 'developer-starter' ), 'button button-hero' ); ?>
                <?php $this->render_step_link( 'finish', __( '继续到完成页', 'developer-starter' ), 'button button-primary button-hero' ); ?>
            </div>
        </section>
        <?php
    }

    /**
     * Render a single read-only plugin detection row.
     *
     * @param array<string,mixed> $plugin Plugin render data.
     * @param bool                $recommended Whether this plugin is recommended for the current preset.
     * @return void
     */
    private function render_plugin_detection_item( $plugin, $recommended = false ) {
        $status      = isset( $plugin['status'] ) ? sanitize_key( (string) $plugin['status'] ) : Setup_Wizard_Plugin_Detector::STATUS_UNKNOWN;
        $label       = isset( $plugin['label'] ) ? (string) $plugin['label'] : '';
        $description = '';

        if ( ! empty( $plugin['description'] ) ) {
            $description = (string) $plugin['description'];
        } elseif ( ! empty( $plugin['hint'] ) ) {
            $description = (string) $plugin['hint'];
        }

        ?>
        <div class="qsw-plugin-item is-<?php echo esc_attr( $status ); ?><?php echo $recommended ? ' is-recommended' : ''; ?>">
            <div>
                <strong>
                    <?php echo esc_html( $label ); ?>
                    <?php if ( $recommended ) : ?>
                        <mark><?php esc_html_e( '推荐', 'developer-starter' ); ?></mark>
                    <?php endif; ?>
                </strong>
                <?php if ( '' !== $description ) : ?>
                    <span><?php echo esc_html( $description ); ?></span>
                <?php endif; ?>
                <?php if ( ! empty( $plugin['basename'] ) ) : ?>
                    <small><?php echo esc_html( sprintf( __( '已检测到文件：%s', 'developer-starter' ), $plugin['basename'] ) ); ?></small>
                <?php endif; ?>
            </div>
            <em><?php echo esc_html( $this->get_plugin_status_label( $status ) ); ?></em>
        </div>
        <?php
    }

    /**
     * Build the read-only plugin detection context for phase 5.
     *
     * @return array<string,mixed>
     */
    private function get_plugin_detection_context() {
        $draft = $this->get_state_service()->get_draft();
        $state = $this->get_state_service()->get_state();

        $preset = $this->get_presets_service()->resolve(
            ! empty( $draft['site_type'] ) ? $draft['site_type'] : ( isset( $state['site_type'] ) ? $state['site_type'] : '' ),
            ! empty( $draft['industry'] ) ? $draft['industry'] : ( isset( $state['industry'] ) ? $state['industry'] : '' )
        );

        $detected          = $this->get_plugin_detector()->detect_all();
        $recommended_items = isset( $preset['optional_plugins'] ) && is_array( $preset['optional_plugins'] ) ? $preset['optional_plugins'] : array();
        $recommended_map   = array();
        $recommended       = array();

        foreach ( $recommended_items as $item ) {
            if ( ! is_array( $item ) || empty( $item['id'] ) ) {
                continue;
            }

            $key = sanitize_key( (string) $item['id'] );
            if ( '' === $key ) {
                continue;
            }

            $plugin = isset( $detected[ $key ] ) && is_array( $detected[ $key ] )
                ? $detected[ $key ]
                : array(
                    'key'      => $key,
                    'label'    => isset( $item['label'] ) ? (string) $item['label'] : $key,
                    'status'   => Setup_Wizard_Plugin_Detector::STATUS_UNKNOWN,
                    'basename' => '',
                    'hint'     => '',
                );

            if ( ! empty( $item['label'] ) ) {
                $plugin['label'] = (string) $item['label'];
            }
            if ( ! empty( $item['description'] ) ) {
                $plugin['description'] = (string) $item['description'];
            }

            $recommended_map[ $key ] = true;
            $recommended[] = $plugin;
        }

        $other = array();
        foreach ( $detected as $key => $plugin ) {
            $key = sanitize_key( (string) $key );
            if ( isset( $recommended_map[ $key ] ) || ! is_array( $plugin ) ) {
                continue;
            }
            $other[] = $plugin;
        }

        return array(
            'preset'              => $preset,
            'recommended_plugins' => $recommended,
            'other_plugins'       => $other,
            'counts'              => $this->get_plugin_detection_counts( $recommended ),
        );
    }

    /**
     * @param array<int,array<string,mixed>> $plugins Recommended plugin rows.
     * @return array<string,int>
     */
    private function get_plugin_detection_counts( $plugins ) {
        $counts = array(
            'recommended'     => 0,
            'active'          => 0,
            'needs_attention' => 0,
        );

        foreach ( $plugins as $plugin ) {
            $status = isset( $plugin['status'] ) ? sanitize_key( (string) $plugin['status'] ) : Setup_Wizard_Plugin_Detector::STATUS_UNKNOWN;
            $counts['recommended']++;

            if ( Setup_Wizard_Plugin_Detector::STATUS_ACTIVE === $status ) {
                $counts['active']++;
            } else {
                $counts['needs_attention']++;
            }
        }

        return $counts;
    }

    /**
     * Render finish step.
     *
     * @param array<string,mixed> $state State.
     * @param array<string,mixed> $draft Draft.
     * @return void
     */
    private function render_finish_step( $state, $draft ) {
        $site_type = ! empty( $draft['site_type'] ) ? $draft['site_type'] : ( isset( $state['site_type'] ) ? $state['site_type'] : '' );
        $industry  = ! empty( $draft['industry'] ) ? $draft['industry'] : ( isset( $state['industry'] ) ? $state['industry'] : '' );
        $preset    = $this->get_presets_service()->resolve( $site_type, $industry );
        $summary   = $this->get_finish_summary( $state, $draft, $preset );
        ?>
        <section class="qsw-panel">
            <div class="qsw-panel-head">
                <span><?php esc_html_e( '步骤 6', 'developer-starter' ); ?></span>
                <h2><?php esc_html_e( '基础建站已准备好', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '这里汇总本次向导的交付结果和后续入口。点击完成只记录向导状态和插件检测快照；第三方插件仍然只检测和提示，不会安装、启用或配置。', 'developer-starter' ); ?></p>
            </div>

            <div class="qsw-summary-grid">
                <div><strong><?php esc_html_e( '站点类型', 'developer-starter' ); ?></strong><span><?php echo esc_html( isset( $preset['site_type_label'] ) ? $preset['site_type_label'] : $this->get_choice_label( $this->get_site_type_choices(), $site_type ) ); ?></span></div>
                <div><strong><?php esc_html_e( '行业/场景', 'developer-starter' ); ?></strong><span><?php echo esc_html( isset( $preset['industry_label'] ) ? $preset['industry_label'] : $this->get_choice_label( $this->get_industry_choices(), $industry ) ); ?></span></div>
                <div><strong><?php esc_html_e( '状态', 'developer-starter' ); ?></strong><span><?php echo esc_html( ! empty( $state['completed'] ) ? __( '已完成', 'developer-starter' ) : __( '未完成', 'developer-starter' ) ); ?></span></div>
            </div>

            <?php $this->render_finish_delivery_checklist( isset( $summary['checklist'] ) ? $summary['checklist'] : array() ); ?>
            <?php $this->render_last_run_card( $state ); ?>
            <?php $this->render_cleanup_panel(); ?>
            <?php $this->render_finish_plugin_snapshot( isset( $summary['plugins'] ) ? $summary['plugins'] : array() ); ?>
            <?php $this->render_finish_next_steps( isset( $summary['next_steps'] ) ? $summary['next_steps'] : array() ); ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>">
                <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                <input type="hidden" name="developer_starter_setup_wizard_action" value="complete" />
                <div class="qsw-actions">
                    <?php $this->render_step_link( 'plugins', __( '返回', 'developer-starter' ), 'button button-hero' ); ?>
                    <button type="submit" class="button button-primary button-hero"><?php echo esc_html( ! empty( $state['completed'] ) ? __( '重新记录完成状态', 'developer-starter' ) : __( '标记向导完成', 'developer-starter' ) ); ?></button>
                    <a class="button button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=developer-starter-settings' ) ); ?>"><?php esc_html_e( '进入主题设置', 'developer-starter' ); ?></a>
                </div>
            </form>
        </section>
        <?php
    }

    /**
     * Render the final delivery checklist.
     *
     * @param array<int,array<string,string>> $items Checklist items.
     * @return void
     */
    private function render_finish_delivery_checklist( $items ) {
        ?>
        <div class="qsw-finish-card">
            <div class="qsw-preview-head">
                <h3><?php esc_html_e( '交付清单', 'developer-starter' ); ?></h3>
                <p><?php esc_html_e( '绿色表示已经具备，黄色表示可继续补充；这些状态只读展示，不会触发额外写入。', 'developer-starter' ); ?></p>
            </div>
            <div class="qsw-finish-grid">
                <?php foreach ( $items as $item ) : ?>
                    <?php $status = isset( $item['status'] ) ? sanitize_key( (string) $item['status'] ) : 'pending'; ?>
                    <div class="qsw-finish-item is-<?php echo esc_attr( $status ); ?>">
                        <strong><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></strong>
                        <span><?php echo esc_html( isset( $item['detail'] ) ? $item['detail'] : '' ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render the final plugin status snapshot.
     *
     * @param array<string,mixed> $plugins Plugin summary.
     * @return void
     */
    private function render_finish_plugin_snapshot( $plugins ) {
        $rows   = isset( $plugins['rows'] ) && is_array( $plugins['rows'] ) ? $plugins['rows'] : array();
        $counts = isset( $plugins['counts'] ) && is_array( $plugins['counts'] ) ? $plugins['counts'] : array();
        ?>
        <div class="qsw-finish-card">
            <div class="qsw-preview-head">
                <h3><?php esc_html_e( '可选插件检测快照', 'developer-starter' ); ?></h3>
                <p>
                    <?php
                    echo esc_html(
                        sprintf(
                            __( '已启用 %1$d 项，已安装未启用 %2$d 项，未安装 %3$d 项。这里只保存小型状态快照，不写第三方插件配置。', 'developer-starter' ),
                            isset( $counts['active'] ) ? absint( $counts['active'] ) : 0,
                            isset( $counts['inactive'] ) ? absint( $counts['inactive'] ) : 0,
                            isset( $counts['missing'] ) ? absint( $counts['missing'] ) : 0
                        )
                    );
                    ?>
                </p>
            </div>

            <?php if ( empty( $rows ) ) : ?>
                <p class="qsw-muted"><?php esc_html_e( '暂无插件检测记录。', 'developer-starter' ); ?></p>
            <?php else : ?>
                <div class="qsw-plugin-list qsw-plugin-list-compact">
                    <?php foreach ( $rows as $row ) : ?>
                        <div class="qsw-plugin-item is-<?php echo esc_attr( isset( $row['status'] ) ? $row['status'] : 'unknown' ); ?>">
                            <div>
                                <strong><?php echo esc_html( isset( $row['label'] ) ? $row['label'] : '' ); ?></strong>
                            </div>
                            <em><?php echo esc_html( $this->get_plugin_status_label( isset( $row['status'] ) ? $row['status'] : 'unknown' ) ); ?></em>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render post-wizard shortcuts.
     *
     * @param array<int,array<string,string>> $steps Next step links.
     * @return void
     */
    private function render_finish_next_steps( $steps ) {
        ?>
        <div class="qsw-finish-card">
            <div class="qsw-preview-head">
                <h3><?php esc_html_e( '后续入口', 'developer-starter' ); ?></h3>
                <p><?php esc_html_e( '完成向导后，通常从这些入口继续微调内容、菜单、样式和模板。', 'developer-starter' ); ?></p>
            </div>
            <div class="qsw-next-grid">
                <?php foreach ( $steps as $step ) : ?>
                    <?php
                    $url = isset( $step['url'] ) ? (string) $step['url'] : '';
                    if ( '' === $url ) {
                        continue;
                    }
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>">
                        <strong><?php echo esc_html( isset( $step['label'] ) ? $step['label'] : '' ); ?></strong>
                        <span><?php echo esc_html( isset( $step['detail'] ) ? $step['detail'] : '' ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render phase 7 cleanup panel.
     *
     * @return void
     */
    private function render_cleanup_panel() {
        $preview = $this->get_cleanup_service()->get_preview();
        $pages   = isset( $preview['pages'] ) && is_array( $preview['pages'] ) ? $preview['pages'] : array();
        $menus   = isset( $preview['menus'] ) && is_array( $preview['menus'] ) ? $preview['menus'] : array();
        $counts  = isset( $preview['counts'] ) && is_array( $preview['counts'] ) ? $preview['counts'] : array();
        $has_any = ! empty( $pages ) || ! empty( $menus ) || ! empty( $preview['draft_exists'] );
        ?>
        <div class="qsw-finish-card qsw-cleanup-card">
            <div class="qsw-preview-head">
                <h3><?php esc_html_e( '安全清理 / 重置', 'developer-starter' ); ?></h3>
                <p><?php esc_html_e( '这里只处理向导最近一次记录的候选内容。页面只会移入回收站；当前静态首页、缺少向导标记的页面和第三方插件配置都会跳过。', 'developer-starter' ); ?></p>
            </div>

            <?php if ( ! empty( $preview['run_id'] ) ) : ?>
                <p class="qsw-muted"><?php echo esc_html( sprintf( __( '最近批次：%s', 'developer-starter' ), $preview['run_id'] ) ); ?></p>
            <?php endif; ?>

            <div class="qsw-cleanup-summary">
                <span><?php echo esc_html( sprintf( __( '可清理页面：%d/%d', 'developer-starter' ), isset( $counts['eligible_pages'] ) ? absint( $counts['eligible_pages'] ) : 0, isset( $counts['pages'] ) ? absint( $counts['pages'] ) : 0 ) ); ?></span>
                <span><?php echo esc_html( sprintf( __( '可删除菜单：%d/%d', 'developer-starter' ), isset( $counts['eligible_menus'] ) ? absint( $counts['eligible_menus'] ) : 0, isset( $counts['menus'] ) ? absint( $counts['menus'] ) : 0 ) ); ?></span>
                <span><?php echo esc_html( ! empty( $preview['draft_exists'] ) ? __( '临时草稿：存在', 'developer-starter' ) : __( '临时草稿：无', 'developer-starter' ) ); ?></span>
            </div>

            <?php $this->render_cleanup_candidate_list( __( '页面候选', 'developer-starter' ), $pages, 'page' ); ?>
            <?php $this->render_cleanup_candidate_list( __( '菜单候选', 'developer-starter' ), $menus, 'menu' ); ?>

            <?php if ( ! $has_any ) : ?>
                <p class="qsw-muted"><?php esc_html_e( '当前没有可清理的向导记录。', 'developer-starter' ); ?></p>
            <?php else : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>" class="qsw-cleanup-form">
                    <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                    <input type="hidden" name="developer_starter_setup_wizard_action" value="cleanup_last_run" />

                    <div class="qsw-option-list">
                        <?php if ( ! empty( $preview['draft_exists'] ) ) : ?>
                            <label>
                                <input type="checkbox" name="delete_draft" value="1" />
                                <span><?php esc_html_e( '清理临时草稿', 'developer-starter' ); ?></span>
                            </label>
                        <?php endif; ?>

                        <label>
                            <input type="checkbox" name="reset_tracking" value="1" />
                            <span><?php esc_html_e( '只重置最近一次向导记录，不删除页面或菜单', 'developer-starter' ); ?></span>
                        </label>

                        <?php if ( ! empty( $counts['eligible_pages'] ) ) : ?>
                            <label>
                                <input type="checkbox" name="trash_pages" value="1" />
                                <span><?php esc_html_e( '将可清理页面移入回收站', 'developer-starter' ); ?></span>
                            </label>
                        <?php endif; ?>

                        <?php if ( ! empty( $counts['eligible_menus'] ) ) : ?>
                            <label>
                                <input type="checkbox" name="delete_menus" value="1" />
                                <span><?php esc_html_e( '删除可清理菜单，并解除对应菜单位置绑定', 'developer-starter' ); ?></span>
                            </label>
                        <?php endif; ?>

                        <label class="qsw-cleanup-confirm">
                            <input type="checkbox" name="cleanup_confirm" value="1" required="required" />
                            <span><?php esc_html_e( '我已确认只清理上方列出的向导记录。', 'developer-starter' ); ?></span>
                        </label>
                    </div>

                    <div class="qsw-actions">
                        <button type="submit" class="button button-hero qsw-danger-button" onclick="return confirm('<?php echo esc_js( __( '确定执行所选清理吗？页面会进入回收站，菜单删除后需要重新绑定。', 'developer-starter' ) ); ?>');"><?php esc_html_e( '执行所选清理', 'developer-starter' ); ?></button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param string                    $title List title.
     * @param array<int,array<string,mixed>> $items Candidate rows.
     * @param string                    $type Candidate type.
     * @return void
     */
    private function render_cleanup_candidate_list( $title, $items, $type ) {
        if ( empty( $items ) ) {
            return;
        }
        ?>
        <div class="qsw-cleanup-list is-<?php echo esc_attr( sanitize_key( (string) $type ) ); ?>">
            <h4><?php echo esc_html( $title ); ?></h4>
            <ul>
                <?php foreach ( $items as $item ) : ?>
                    <?php $eligible = ! empty( $item['eligible'] ); ?>
                    <li class="<?php echo esc_attr( $eligible ? 'is-eligible' : 'is-skipped' ); ?>">
                        <strong>
                            <?php
                            $label = isset( $item['title'] ) && '' !== (string) $item['title'] ? (string) $item['title'] : sprintf( __( '#%d', 'developer-starter' ), isset( $item['id'] ) ? absint( $item['id'] ) : 0 );
                            echo esc_html( $label );
                            ?>
                        </strong>
                        <span><?php echo esc_html( isset( $item['reason'] ) ? $item['reason'] : '' ); ?></span>
                        <?php if ( isset( $item['id'] ) && absint( $item['id'] ) > 0 ) : ?>
                            <em><?php echo esc_html( sprintf( __( 'ID: %d', 'developer-starter' ), absint( $item['id'] ) ) ); ?></em>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Build the read-only final summary.
     *
     * @param array<string,mixed> $state State.
     * @param array<string,mixed> $draft Draft.
     * @param array<string,mixed> $preset Resolved preset.
     * @return array<string,mixed>
     */
    private function get_finish_summary( $state, $draft, $preset ) {
        $front_page_id = absint( get_option( 'page_on_front', 0 ) );
        $front_page_title = $front_page_id > 0 ? get_the_title( $front_page_id ) : '';
        $menu_summary = $this->get_primary_menu_summary();
        $basics = $this->get_finish_basics_summary();
        $created_pages = isset( $state['last_run_created_pages'] ) && is_array( $state['last_run_created_pages'] ) ? $state['last_run_created_pages'] : array();
        if ( empty( $created_pages ) && isset( $state['created_pages'] ) && is_array( $state['created_pages'] ) ) {
            $created_pages = $state['created_pages'];
        }

        $checklist = array(
            array(
                'label'  => __( '站点方案', 'developer-starter' ),
                'detail' => sprintf(
                    __( '%1$s / %2$s', 'developer-starter' ),
                    isset( $preset['site_type_label'] ) ? $preset['site_type_label'] : __( '站点类型', 'developer-starter' ),
                    isset( $preset['industry_label'] ) ? $preset['industry_label'] : __( '行业场景', 'developer-starter' )
                ),
                'status' => 'done',
            ),
            array(
                'label'  => __( '页面结果', 'developer-starter' ),
                'detail' => ! empty( $created_pages )
                    ? sprintf( __( '向导记录了 %d 个页面。', 'developer-starter' ), count( $created_pages ) )
                    : __( '没有记录新建页面，可能使用了已有页面或跳过页面生成。', 'developer-starter' ),
                'status' => ! empty( $created_pages ) ? 'done' : 'pending',
            ),
            array(
                'label'  => __( '静态首页', 'developer-starter' ),
                'detail' => '' !== $front_page_title
                    ? sprintf( __( '当前首页：%s', 'developer-starter' ), $front_page_title )
                    : __( '尚未设置静态首页。', 'developer-starter' ),
                'status' => $front_page_id > 0 ? 'done' : 'pending',
            ),
            array(
                'label'  => __( '主导航', 'developer-starter' ),
                'detail' => ! empty( $menu_summary['title'] ) ? $menu_summary['title'] : __( '主导航位置尚未绑定菜单。', 'developer-starter' ),
                'status' => ! empty( $menu_summary['ready'] ) ? 'done' : 'pending',
            ),
            array(
                'label'  => __( '品牌与联系信息', 'developer-starter' ),
                'detail' => isset( $basics['contact_detail'] ) ? $basics['contact_detail'] : '',
                'status' => ! empty( $basics['contact_ready'] ) ? 'done' : 'pending',
            ),
            array(
                'label'  => __( 'SEO 基础信息', 'developer-starter' ),
                'detail' => isset( $basics['seo_detail'] ) ? $basics['seo_detail'] : '',
                'status' => ! empty( $basics['seo_ready'] ) ? 'done' : 'pending',
            ),
        );

        return array(
            'checklist'  => $checklist,
            'plugins'    => $this->get_finish_plugin_summary( $state ),
            'next_steps' => $this->get_finish_next_steps( $front_page_id ),
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function get_primary_menu_summary() {
        $locations = get_nav_menu_locations();
        $locations = is_array( $locations ) ? $locations : array();
        $menu_id = ! empty( $locations['primary'] ) ? absint( $locations['primary'] ) : 0;
        if ( $menu_id <= 0 ) {
            return array( 'ready' => false, 'title' => '' );
        }

        $menu = wp_get_nav_menu_object( $menu_id );
        if ( ! $menu || is_wp_error( $menu ) ) {
            return array( 'ready' => false, 'title' => '' );
        }

        $name = isset( $menu->name ) ? (string) $menu->name : '';
        return array(
            'ready' => true,
            'title' => '' !== $name ? sprintf( __( '已绑定：%s', 'developer-starter' ), $name ) : __( '已绑定主导航菜单。', 'developer-starter' ),
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function get_finish_basics_summary() {
        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        $site_title = trim( (string) get_option( 'blogname', '' ) );
        $company_name = isset( $options['company_name'] ) ? trim( (string) $options['company_name'] ) : '';
        $phone = isset( $options['company_phone'] ) ? trim( (string) $options['company_phone'] ) : '';
        $email = isset( $options['company_email'] ) ? trim( (string) $options['company_email'] ) : '';
        $seo_title = isset( $options['default_title'] ) ? trim( (string) $options['default_title'] ) : '';
        $seo_desc = isset( $options['default_description'] ) ? trim( (string) $options['default_description'] ) : '';

        $contact_bits = array();
        if ( '' !== $site_title ) {
            $contact_bits[] = __( '站点名称已设置', 'developer-starter' );
        }
        if ( '' !== $company_name ) {
            $contact_bits[] = __( '企业名称已设置', 'developer-starter' );
        }
        if ( '' !== $phone || '' !== $email ) {
            $contact_bits[] = __( '联系方式已设置', 'developer-starter' );
        }

        $seo_bits = array();
        if ( '' !== $seo_title ) {
            $seo_bits[] = __( '默认标题已设置', 'developer-starter' );
        }
        if ( '' !== $seo_desc ) {
            $seo_bits[] = __( '默认描述已设置', 'developer-starter' );
        }

        return array(
            'contact_ready'  => count( $contact_bits ) >= 2,
            'contact_detail' => ! empty( $contact_bits ) ? implode( '，', $contact_bits ) : __( '可在主题设置里继续补充品牌和联系方式。', 'developer-starter' ),
            'seo_ready'      => ! empty( $seo_bits ),
            'seo_detail'     => ! empty( $seo_bits ) ? implode( '，', $seo_bits ) : __( '可在主题设置里继续补充 SEO 默认标题和描述。', 'developer-starter' ),
        );
    }

    /**
     * @param array<string,mixed> $state State.
     * @return array<string,mixed>
     */
    private function get_finish_plugin_summary( $state ) {
        $snapshot = isset( $state['detected_plugins'] ) && is_array( $state['detected_plugins'] ) ? $state['detected_plugins'] : array();
        if ( empty( $snapshot ) ) {
            $snapshot = $this->get_plugin_detector()->get_status_snapshot();
        }

        $known = $this->get_plugin_detector()->get_known_plugins();
        $rows = array();
        $counts = array(
            'active'   => 0,
            'inactive' => 0,
            'missing'  => 0,
            'unknown'  => 0,
        );

        foreach ( $snapshot as $key => $status ) {
            $key = sanitize_key( (string) $key );
            $status = sanitize_key( (string) $status );
            if ( '' === $key ) {
                continue;
            }

            if ( ! isset( $counts[ $status ] ) ) {
                $status = Setup_Wizard_Plugin_Detector::STATUS_UNKNOWN;
            }

            $counts[ $status ]++;
            $rows[] = array(
                'label'  => isset( $known[ $key ]['label'] ) ? (string) $known[ $key ]['label'] : $key,
                'status' => $status,
            );
        }

        return array(
            'counts' => $counts,
            'rows'   => $rows,
        );
    }

    /**
     * @param int $front_page_id Front page id.
     * @return array<int,array<string,string>>
     */
    private function get_finish_next_steps( $front_page_id ) {
        $steps = array(
            array(
                'label'  => __( '预览网站', 'developer-starter' ),
                'detail' => __( '打开前台首页检查整体效果。', 'developer-starter' ),
                'url'    => home_url( '/' ),
            ),
            array(
                'label'  => __( '主题设置', 'developer-starter' ),
                'detail' => __( '继续调整品牌、页脚、SEO、性能和安全。', 'developer-starter' ),
                'url'    => admin_url( 'admin.php?page=developer-starter-settings' ),
            ),
            array(
                'label'  => __( '菜单位置', 'developer-starter' ),
                'detail' => __( '检查主导航、移动端菜单和底部菜单。', 'developer-starter' ),
                'url'    => admin_url( 'nav-menus.php?action=locations' ),
            ),
            array(
                'label'  => __( '页面列表', 'developer-starter' ),
                'detail' => __( '继续编辑向导创建或复用的页面。', 'developer-starter' ),
                'url'    => admin_url( 'edit.php?post_type=page' ),
            ),
            array(
                'label'  => __( '模板中心', 'developer-starter' ),
                'detail' => __( '后续可以继续导入或替换页面模板。', 'developer-starter' ),
                'url'    => admin_url( 'admin.php?page=developer-starter-template-center' ),
            ),
        );

        $front_page_id = absint( $front_page_id );
        if ( $front_page_id > 0 && get_post_status( $front_page_id ) ) {
            array_splice(
                $steps,
                1,
                0,
                array(
                    array(
                        'label'  => __( '编辑首页', 'developer-starter' ),
                        'detail' => __( '进入当前静态首页继续装修内容。', 'developer-starter' ),
                        'url'    => get_edit_post_link( $front_page_id, '' ),
                    ),
                )
            );
        }

        return $steps;
    }

    /**
     * Render preset recommendation preview.
     *
     * @param array<string,mixed> $preset Resolved preset.
     * @return void
     */
    private function render_preset_preview( $preset ) {
        $templates = isset( $preset['recommended_templates'] ) && is_array( $preset['recommended_templates'] ) ? $preset['recommended_templates'] : array();
        $pages     = isset( $preset['recommended_pages'] ) && is_array( $preset['recommended_pages'] ) ? $preset['recommended_pages'] : array();
        $models    = isset( $preset['content_models'] ) && is_array( $preset['content_models'] ) ? $preset['content_models'] : array();
        $features  = isset( $preset['features'] ) && is_array( $preset['features'] ) ? $preset['features'] : array();
        $plugins   = isset( $preset['optional_plugins'] ) && is_array( $preset['optional_plugins'] ) ? $preset['optional_plugins'] : array();
        ?>
        <div class="qsw-preset-preview">
            <div class="qsw-preview-head">
                <h3><?php esc_html_e( '推荐预览', 'developer-starter' ); ?></h3>
                <p>
                    <?php
                    echo esc_html(
                        sprintf(
                            __( '当前组合：%1$s / %2$s。这里仅展示推荐，不会写入数据库或访问第三方服务。', 'developer-starter' ),
                            isset( $preset['site_type_label'] ) ? $preset['site_type_label'] : __( '站点类型', 'developer-starter' ),
                            isset( $preset['industry_label'] ) ? $preset['industry_label'] : __( '行业场景', 'developer-starter' )
                        )
                    );
                    ?>
                </p>
            </div>

            <div class="qsw-preview-grid">
                <?php $this->render_preset_item_list( __( '推荐模板', 'developer-starter' ), $templates, 'package' ); ?>
                <?php $this->render_preset_item_list( __( '推荐页面', 'developer-starter' ), $pages, 'slug' ); ?>
                <?php $this->render_preset_chip_list( __( '内容模型', 'developer-starter' ), $models ); ?>
                <?php $this->render_preset_chip_list( __( '主题能力', 'developer-starter' ), $features ); ?>
            </div>

            <?php if ( ! empty( $plugins ) ) : ?>
                <div class="qsw-plugin-hints">
                    <h4><?php esc_html_e( '可选插件提示', 'developer-starter' ); ?></h4>
                    <p><?php esc_html_e( '这些插件只会在下一步做状态检测和提示，向导不会安装、启用或配置它们。', 'developer-starter' ); ?></p>
                    <div class="qsw-chip-row">
                        <?php foreach ( $plugins as $plugin ) : ?>
                            <span class="qsw-chip"><?php echo esc_html( isset( $plugin['label'] ) ? $plugin['label'] : '' ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param string              $title Title.
     * @param array<int,array<string,mixed>> $items Items.
     * @param string              $meta_key Meta field key.
     * @return void
     */
    private function render_preset_item_list( $title, $items, $meta_key = '' ) {
        ?>
        <div class="qsw-preview-card">
            <h4><?php echo esc_html( $title ); ?></h4>
            <?php if ( empty( $items ) ) : ?>
                <p class="qsw-muted"><?php esc_html_e( '暂无推荐', 'developer-starter' ); ?></p>
            <?php else : ?>
                <ul>
                    <?php foreach ( array_slice( $items, 0, 6 ) as $item ) : ?>
                        <li>
                            <strong><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></strong>
                            <?php if ( '' !== $meta_key && ! empty( $item[ $meta_key ] ) ) : ?>
                                <span><?php echo esc_html( $item[ $meta_key ] ); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param string              $title Title.
     * @param array<int,array<string,mixed>> $items Items.
     * @return void
     */
    private function render_preset_chip_list( $title, $items ) {
        ?>
        <div class="qsw-preview-card">
            <h4><?php echo esc_html( $title ); ?></h4>
            <?php if ( empty( $items ) ) : ?>
                <p class="qsw-muted"><?php esc_html_e( '暂无推荐', 'developer-starter' ); ?></p>
            <?php else : ?>
                <div class="qsw-chip-row">
                    <?php foreach ( array_slice( $items, 0, 8 ) as $item ) : ?>
                        <span class="qsw-chip"><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render progress nav.
     *
     * @param string $current Current step.
     * @return void
     */
    private function render_progress( $current ) {
        $steps = $this->get_steps();
        ?>
        <nav class="qsw-progress" aria-label="<?php esc_attr_e( '建站向导步骤', 'developer-starter' ); ?>">
            <?php foreach ( $steps as $key => $label ) : ?>
                <a class="<?php echo esc_attr( $current === $key ? 'is-active' : '' ); ?>" href="<?php echo esc_url( $this->get_step_url( $key ) ); ?>">
                    <span><?php echo esc_html( $label ); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    /**
     * Render current state card.
     *
     * @param array<string,mixed> $state State.
     * @return void
     */
    private function render_state_card( $state ) {
        ?>
        <div class="qsw-state-card">
            <h3><?php esc_html_e( '向导状态', 'developer-starter' ); ?></h3>
            <p><?php echo esc_html( ! empty( $state['completed'] ) ? __( '已完成', 'developer-starter' ) : ( ! empty( $state['skipped'] ) ? __( '已跳过', 'developer-starter' ) : __( '进行中 / 未完成', 'developer-starter' ) ) ); ?></p>
            <?php if ( ! empty( $state['completed_at'] ) ) : ?>
                <span><?php echo esc_html( sprintf( __( '完成时间：%s', 'developer-starter' ), date_i18n( 'Y-m-d H:i', absint( $state['completed_at'] ) ) ) ); ?></span>
            <?php elseif ( ! empty( $state['skipped_at'] ) ) : ?>
                <span><?php echo esc_html( sprintf( __( '跳过时间：%s', 'developer-starter' ), date_i18n( 'Y-m-d H:i', absint( $state['skipped_at'] ) ) ) ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render last page-generation run summary.
     *
     * @param array<string,mixed> $state State.
     * @return void
     */
    private function render_last_run_card( $state ) {
        $candidates = $this->get_state_service()->get_last_run_cleanup_candidates();
        $pages = isset( $candidates['pages'] ) && is_array( $candidates['pages'] ) ? $candidates['pages'] : array();
        if ( empty( $pages ) ) {
            return;
        }
        ?>
        <div class="qsw-last-run">
            <h3><?php esc_html_e( '最近一次向导创建的页面', 'developer-starter' ); ?></h3>
            <ul>
                <?php foreach ( $pages as $page_key => $page_id ) : ?>
                    <?php $page_id = absint( $page_id ); ?>
                    <?php if ( $page_id <= 0 || ! get_post_status( $page_id ) ) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <li>
                        <strong><?php echo esc_html( get_the_title( $page_id ) ); ?></strong>
                        <span><?php echo esc_html( sanitize_key( (string) $page_key ) ); ?></span>
                        <a href="<?php echo esc_url( get_edit_post_link( $page_id, '' ) ); ?>"><?php esc_html_e( '编辑', 'developer-starter' ); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Populate page-generation draft from POST.
     *
     * @param array<string,mixed> $draft Draft.
     * @return array<string,mixed>
     */
    private function populate_pages_draft_from_request( $draft ) {
        $draft = is_array( $draft ) ? $draft : array();

        $selected_pages = isset( $_POST['selected_pages'] ) && is_array( $_POST['selected_pages'] )
            ? wp_unslash( $_POST['selected_pages'] )
            : array();

        $draft['selected_pages'] = $this->sanitize_key_list( $selected_pages );
        $draft['template_id']    = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['template_id'] ) ) : '';
        $draft['options']        = array(
            'include_auth_pages'  => ! empty( $_POST['include_auth_pages'] ) ? '1' : '',
            'set_front_page'      => ! empty( $_POST['set_front_page'] ) ? '1' : '',
            'import_home_modules' => ! empty( $_POST['import_home_modules'] ) ? '1' : '',
        );

        return $draft;
    }

    /**
     * Populate menu/basic settings draft from POST.
     *
     * @param array<string,mixed> $draft Draft.
     * @return array<string,mixed>
     */
    private function populate_basics_draft_from_request( $draft ) {
        $draft = is_array( $draft ) ? $draft : array();

        $brand = isset( $_POST['brand'] ) && is_array( $_POST['brand'] ) ? wp_unslash( $_POST['brand'] ) : array();
        $contact = isset( $_POST['contact'] ) && is_array( $_POST['contact'] ) ? wp_unslash( $_POST['contact'] ) : array();
        $seo = isset( $_POST['seo'] ) && is_array( $_POST['seo'] ) ? wp_unslash( $_POST['seo'] ) : array();

        $draft['brand'] = array(
            'site_title'    => isset( $brand['site_title'] ) ? sanitize_text_field( (string) $brand['site_title'] ) : '',
            'tagline'       => isset( $brand['tagline'] ) ? sanitize_text_field( (string) $brand['tagline'] ) : '',
            'site_logo'     => isset( $brand['site_logo'] ) ? esc_url_raw( (string) $brand['site_logo'] ) : '',
            'mobile_logo'   => isset( $brand['mobile_logo'] ) ? esc_url_raw( (string) $brand['mobile_logo'] ) : '',
            'primary_color' => isset( $brand['primary_color'] ) && sanitize_hex_color( (string) $brand['primary_color'] ) ? sanitize_hex_color( (string) $brand['primary_color'] ) : '',
        );

        $draft['contact'] = array(
            'company_name'          => isset( $contact['company_name'] ) ? sanitize_text_field( (string) $contact['company_name'] ) : '',
            'company_phone'         => isset( $contact['company_phone'] ) ? sanitize_text_field( (string) $contact['company_phone'] ) : '',
            'company_email'         => isset( $contact['company_email'] ) ? sanitize_email( (string) $contact['company_email'] ) : '',
            'company_address'       => isset( $contact['company_address'] ) ? sanitize_textarea_field( (string) $contact['company_address'] ) : '',
            'company_working_hours' => isset( $contact['company_working_hours'] ) ? sanitize_text_field( (string) $contact['company_working_hours'] ) : '',
            'company_brief'         => isset( $contact['company_brief'] ) ? sanitize_textarea_field( (string) $contact['company_brief'] ) : '',
            'icp_number'            => isset( $contact['icp_number'] ) ? sanitize_text_field( (string) $contact['icp_number'] ) : '',
            'police_number'         => isset( $contact['police_number'] ) ? sanitize_text_field( (string) $contact['police_number'] ) : '',
        );

        $existing_options = isset( $draft['options'] ) && is_array( $draft['options'] ) ? $draft['options'] : array();
        $existing_options['create_primary_menu'] = ! empty( $_POST['create_primary_menu'] ) ? '1' : '';
        $existing_options['overwrite_existing']  = ! empty( $_POST['overwrite_existing'] ) ? '1' : '';
        $existing_options['seo'] = array(
            'default_title'        => isset( $seo['default_title'] ) ? sanitize_text_field( (string) $seo['default_title'] ) : '',
            'default_description'  => isset( $seo['default_description'] ) ? sanitize_textarea_field( (string) $seo['default_description'] ) : '',
            'default_keywords'     => isset( $seo['default_keywords'] ) ? sanitize_text_field( (string) $seo['default_keywords'] ) : '',
            'schema_engine_enable' => ! empty( $_POST['schema_engine_enable'] ) ? '1' : '',
        );
        $draft['options'] = $existing_options;

        return $draft;
    }

    /**
     * @param array<string,mixed> $draft Draft.
     * @return array<string,array<string,string>>
     */
    private function get_basic_settings_defaults( $draft ) {
        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        $site_title = (string) get_option( 'blogname', '' );
        $tagline    = (string) get_option( 'blogdescription', '' );
        $company_name = isset( $options['company_name'] ) ? (string) $options['company_name'] : '';
        if ( '' === trim( $company_name ) ) {
            $company_name = $site_title;
        }

        $seo_title = isset( $options['default_title'] ) ? (string) $options['default_title'] : '';
        if ( '' === trim( $seo_title ) ) {
            $seo_title = $site_title;
        }

        $seo_description = isset( $options['default_description'] ) ? (string) $options['default_description'] : '';
        if ( '' === trim( $seo_description ) ) {
            $seo_description = $tagline;
        }

        return array(
            'brand'   => array(
                'site_title'    => $site_title,
                'tagline'       => $tagline,
                'site_logo'     => isset( $options['site_logo'] ) ? (string) $options['site_logo'] : '',
                'mobile_logo'   => isset( $options['mobile_logo'] ) ? (string) $options['mobile_logo'] : '',
                'primary_color' => isset( $options['design_primary_color'] ) ? (string) $options['design_primary_color'] : ( isset( $options['primary_color'] ) ? (string) $options['primary_color'] : '#2563eb' ),
            ),
            'contact' => array(
                'company_name'          => $company_name,
                'company_phone'         => isset( $options['company_phone'] ) ? (string) $options['company_phone'] : '',
                'company_email'         => isset( $options['company_email'] ) ? (string) $options['company_email'] : (string) get_option( 'admin_email', '' ),
                'company_address'       => isset( $options['company_address'] ) ? (string) $options['company_address'] : '',
                'company_working_hours' => isset( $options['company_working_hours'] ) ? (string) $options['company_working_hours'] : '',
                'company_brief'         => isset( $options['company_brief'] ) ? (string) $options['company_brief'] : '',
                'icp_number'            => isset( $options['icp_number'] ) ? (string) $options['icp_number'] : '',
                'police_number'         => isset( $options['police_number'] ) ? (string) $options['police_number'] : '',
            ),
            'seo'     => array(
                'default_title'        => $seo_title,
                'default_description'  => $seo_description,
                'default_keywords'     => isset( $options['default_keywords'] ) ? (string) $options['default_keywords'] : '',
                'schema_engine_enable' => isset( $options['schema_engine_enable'] ) ? (string) $options['schema_engine_enable'] : '1',
            ),
        );
    }

    /**
     * @param array<string,mixed>              $draft Draft.
     * @param array<int,array<string,mixed>>   $pages Recommended pages.
     * @return array<int,string>
     */
    private function get_selected_pages_for_render( $draft, $pages ) {
        if ( ! empty( $draft['selected_pages'] ) && is_array( $draft['selected_pages'] ) ) {
            return $this->sanitize_key_list( $draft['selected_pages'] );
        }

        $keys = array();
        foreach ( $pages as $page ) {
            if ( is_array( $page ) && ! empty( $page['id'] ) ) {
                $keys[] = sanitize_key( (string) $page['id'] );
            }
        }

        return array_values( array_unique( array_filter( $keys ) ) );
    }

    /**
     * @param array<string,mixed>              $draft Draft.
     * @param array<int,array<string,mixed>>   $templates Recommended templates.
     * @return string
     */
    private function get_selected_template_id_for_render( $draft, $templates ) {
        $template_id = ! empty( $draft['template_id'] ) ? sanitize_key( (string) $draft['template_id'] ) : '';
        if ( '' !== $template_id ) {
            return $template_id;
        }

        foreach ( $templates as $template ) {
            if ( is_array( $template ) && ! empty( $template['id'] ) ) {
                return sanitize_key( (string) $template['id'] );
            }
        }

        return 'home';
    }

    /**
     * @param mixed $list Raw list.
     * @return array<int,string>
     */
    private function sanitize_key_list( $list ) {
        $clean = array();
        foreach ( (array) $list as $value ) {
            $value = sanitize_key( (string) $value );
            if ( '' !== $value ) {
                $clean[] = $value;
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * Render action form.
     *
     * @param string $action Action.
     * @param string $label Label.
     * @param string $class Button class.
     * @return void
     */
    private function render_action_form( $action, $label, $class = 'button' ) {
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>" class="qsw-inline-form">
            <?php wp_nonce_field( self::NONCE_ACTION ); ?>
            <input type="hidden" name="developer_starter_setup_wizard_action" value="<?php echo esc_attr( $action ); ?>" />
            <button type="submit" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></button>
        </form>
        <?php
    }

    /**
     * Render step link.
     *
     * @param string $step Step.
     * @param string $label Label.
     * @param string $class CSS class.
     * @return void
     */
    private function render_step_link( $step, $label, $class = 'button' ) {
        echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $this->get_step_url( $step ) ) . '">' . esc_html( $label ) . '</a>';
    }

    /**
     * Render notices.
     *
     * @return void
     */
    private function render_notices() {
        if ( ! empty( $_GET['from_activation'] ) ) {
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( '启灵主题已启用，你可以先通过建站向导完成基础配置；也可以跳过，之后随时重新进入。', 'developer-starter' ) . '</p></div>';
        }
        if ( ! empty( $_GET['saved'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '当前步骤已保存。', 'developer-starter' ) . '</p></div>';
        }
        if ( ! empty( $_GET['pages_generated'] ) ) {
            $created = isset( $_GET['created'] ) ? absint( $_GET['created'] ) : 0;
            $reused  = isset( $_GET['reused'] ) ? absint( $_GET['reused'] ) : 0;
            $errors  = isset( $_GET['errors'] ) ? absint( $_GET['errors'] ) : 0;
            echo '<div class="notice ' . esc_attr( $errors > 0 ? 'notice-warning' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html( sprintf( __( '页面生成完成：新建 %1$d 个，复用 %2$d 个，错误 %3$d 个。', 'developer-starter' ), $created, $reused, $errors ) ) . '</p></div>';
            if ( isset( $_GET['front_page'] ) && 'kept_existing' === sanitize_key( wp_unslash( (string) $_GET['front_page'] ) ) ) {
                echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( '已检测到现有静态首页，向导没有覆盖它。', 'developer-starter' ) . '</p></div>';
            }
            if ( ! empty( $_GET['modules_filled'] ) ) {
                echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( '新创建首页已导入官方模板模块。', 'developer-starter' ) . '</p></div>';
            }
        }
        if ( ! empty( $_GET['basics_applied'] ) ) {
            $settings = isset( $_GET['settings'] ) ? absint( $_GET['settings'] ) : 0;
            $skipped  = isset( $_GET['settings_skip'] ) ? absint( $_GET['settings_skip'] ) : 0;
            $menu_items = isset( $_GET['menu_items'] ) ? absint( $_GET['menu_items'] ) : 0;
            $menu_status = isset( $_GET['menu_status'] ) ? sanitize_key( wp_unslash( (string) $_GET['menu_status'] ) ) : 'skipped';
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( __( '菜单与基础设置已处理：更新 %1$d 项，保留已有 %2$d 项，菜单状态 %3$s，新增菜单项 %4$d 个。', 'developer-starter' ), $settings, $skipped, $menu_status, $menu_items ) ) . '</p></div>';
        }
        if ( ! empty( $_GET['wizard_skipped'] ) ) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( '已跳过建站向导。你可以稍后从“企业主题设置 -> 建站向导”重新进入。', 'developer-starter' ) . '</p></div>';
        }
        if ( ! empty( $_GET['wizard_completed'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '建站向导已标记完成。', 'developer-starter' ) . '</p></div>';
        }
        if ( ! empty( $_GET['cleanup_done'] ) ) {
            $trashed_pages = isset( $_GET['trash_pages'] ) ? absint( $_GET['trash_pages'] ) : 0;
            $skipped_pages = isset( $_GET['skip_pages'] ) ? absint( $_GET['skip_pages'] ) : 0;
            $deleted_menus = isset( $_GET['delete_menus'] ) ? absint( $_GET['delete_menus'] ) : 0;
            $skipped_menus = isset( $_GET['skip_menus'] ) ? absint( $_GET['skip_menus'] ) : 0;
            $errors        = isset( $_GET['errors'] ) ? absint( $_GET['errors'] ) : 0;
            $draft_text    = ! empty( $_GET['draft_deleted'] ) ? __( '临时草稿已清理。', 'developer-starter' ) : __( '临时草稿未清理。', 'developer-starter' );
            $record_text   = ! empty( $_GET['records_reset'] ) ? __( '向导记录已更新。', 'developer-starter' ) : __( '向导记录未变更。', 'developer-starter' );
            echo '<div class="notice ' . esc_attr( $errors > 0 ? 'notice-warning' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html( sprintf( __( '向导清理完成：页面移入回收站 %1$d 个，页面跳过 %2$d 个，删除菜单 %3$d 个，菜单跳过 %4$d 个，错误 %5$d 个。%6$s%7$s', 'developer-starter' ), $trashed_pages, $skipped_pages, $deleted_menus, $skipped_menus, $errors, $draft_text, $record_text ) ) . '</p></div>';
        }
    }

    /**
     * Redirect to a step.
     *
     * @param string              $step Step.
     * @param array<string,mixed> $args Extra query args.
     * @return void
     */
    private function redirect_to_step( $step, $args = array() ) {
        wp_safe_redirect( add_query_arg( array_merge( array( 'page' => self::PAGE_SLUG, 'step' => sanitize_key( $step ) ), $args ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * @return string
     */
    private function get_current_step() {
        $step = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( (string) $_GET['step'] ) ) : 'welcome';
        $steps = $this->get_steps();
        return isset( $steps[ $step ] ) ? $step : 'welcome';
    }

    /**
     * @param string $step Step.
     * @return string
     */
    private function get_step_url( $step ) {
        return add_query_arg(
            array(
                'page' => self::PAGE_SLUG,
                'step' => sanitize_key( $step ),
            ),
            admin_url( 'admin.php' )
        );
    }

    /**
     * @return array<string,string>
     */
    private function get_steps() {
        return array(
            'welcome' => __( '欢迎', 'developer-starter' ),
            'type'    => __( '站点类型', 'developer-starter' ),
            'pages'   => __( '页面生成', 'developer-starter' ),
            'basics'  => __( '菜单设置', 'developer-starter' ),
            'plugins' => __( '插件检测', 'developer-starter' ),
            'finish'  => __( '完成', 'developer-starter' ),
        );
    }

    /**
     * @return array<string,string>
     */
    private function get_site_type_choices() {
        return $this->get_presets_service()->get_site_type_choices();
    }

    /**
     * @return array<string,string>
     */
    private function get_industry_choices() {
        return $this->get_presets_service()->get_industry_choices();
    }

    /**
     * @param array<string,string> $choices Choices.
     * @param mixed                $key Key.
     * @return string
     */
    private function get_choice_label( $choices, $key ) {
        $key = sanitize_key( (string) $key );
        return isset( $choices[ $key ] ) ? $choices[ $key ] : __( '尚未选择', 'developer-starter' );
    }

    /**
     * @param string $status Plugin status.
     * @return string
     */
    private function get_plugin_status_label( $status ) {
        $labels = array(
            Setup_Wizard_Plugin_Detector::STATUS_ACTIVE   => __( '已启用', 'developer-starter' ),
            Setup_Wizard_Plugin_Detector::STATUS_INACTIVE => __( '已安装未启用', 'developer-starter' ),
            Setup_Wizard_Plugin_Detector::STATUS_MISSING  => __( '未安装', 'developer-starter' ),
            Setup_Wizard_Plugin_Detector::STATUS_UNKNOWN  => __( '未知', 'developer-starter' ),
        );

        return isset( $labels[ $status ] ) ? $labels[ $status ] : $labels[ Setup_Wizard_Plugin_Detector::STATUS_UNKNOWN ];
    }

    /**
     * @return array<string,mixed>
     */
    private function get_existing_site_summary() {
        $page_count = wp_count_posts( 'page' );
        $pages      = 0;
        if ( $page_count ) {
            foreach ( array( 'publish', 'draft', 'private' ) as $status ) {
                $pages += isset( $page_count->{$status} ) ? absint( $page_count->{$status} ) : 0;
            }
        }

        $menus = wp_get_nav_menus();
        $front_page_id = absint( get_option( 'page_on_front', 0 ) );

        return array(
            'pages'            => $pages,
            'menus'            => is_array( $menus ) ? count( $menus ) : 0,
            'front_page_id'    => $front_page_id,
            'front_page_title' => $front_page_id > 0 ? get_the_title( $front_page_id ) : '',
            'has_content'      => $pages > 0 || ( is_array( $menus ) && count( $menus ) > 0 ) || $front_page_id > 0,
        );
    }

    /**
     * @return Setup_Wizard_State
     */
    private function get_state_service() {
        if ( function_exists( 'developer_starter_get_setup_wizard_state_service' ) ) {
            return developer_starter_get_setup_wizard_state_service();
        }

        return Setup_Wizard_State::get_instance();
    }

    /**
     * @return Setup_Wizard_Plugin_Detector
     */
    private function get_plugin_detector() {
        if ( function_exists( 'developer_starter_get_setup_wizard_plugin_detector' ) ) {
            return developer_starter_get_setup_wizard_plugin_detector();
        }

        return new Setup_Wizard_Plugin_Detector();
    }

    /**
     * @return Setup_Wizard_Presets
     */
    private function get_presets_service() {
        if ( function_exists( 'developer_starter_get_setup_wizard_presets' ) ) {
            return developer_starter_get_setup_wizard_presets();
        }

        return Setup_Wizard_Presets::get_instance();
    }

    /**
     * @return Setup_Wizard_Import_Service
     */
    private function get_import_service() {
        if ( function_exists( 'developer_starter_get_setup_wizard_import_service' ) ) {
            return developer_starter_get_setup_wizard_import_service();
        }

        return new Setup_Wizard_Import_Service(
            $this->get_state_service(),
            function_exists( 'developer_starter_get_setup_wizard_reuse_service' ) ? developer_starter_get_setup_wizard_reuse_service() : null,
            $this->get_presets_service()
        );
    }

    /**
     * @return Setup_Wizard_Settings_Service
     */
    private function get_settings_service() {
        if ( function_exists( 'developer_starter_get_setup_wizard_settings_service' ) ) {
            return developer_starter_get_setup_wizard_settings_service();
        }

        return new Setup_Wizard_Settings_Service(
            $this->get_state_service(),
            function_exists( 'developer_starter_get_setup_wizard_reuse_service' ) ? developer_starter_get_setup_wizard_reuse_service() : null,
            $this->get_presets_service()
        );
    }

    /**
     * @return Setup_Wizard_Cleanup_Service
     */
    private function get_cleanup_service() {
        if ( function_exists( 'developer_starter_get_setup_wizard_cleanup_service' ) ) {
            return developer_starter_get_setup_wizard_cleanup_service();
        }

        return new Setup_Wizard_Cleanup_Service( $this->get_state_service() );
    }

    /**
     * Render scoped CSS.
     *
     * @return void
     */
    private function render_styles() {
        ?>
        <style>
            .qiling-setup-wizard { max-width: 1360px; }
            .qiling-setup-wizard * { box-sizing: border-box; }
            .qsw-shell { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 18px; margin-top: 18px; }
            .qsw-sidebar, .qsw-panel { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; box-shadow: 0 8px 22px rgba(15,23,42,.05); }
            .qsw-sidebar { display: grid; align-content: start; gap: 14px; padding: 14px; }
            .qsw-progress { display: grid; gap: 8px; }
            .qsw-progress a { display: flex; align-items: center; min-height: 38px; padding: 0 12px; color: #1d2327; text-decoration: none; border-radius: 6px; background: #f6f7f7; }
            .qsw-progress a.is-active { color: #fff; background: #2271b1; }
            .qsw-state-card { padding: 12px; border: 1px solid #dcdcde; border-radius: 6px; background: #fbfbfc; }
            .qsw-state-card h3, .qsw-state-card p { margin: 0 0 8px; }
            .qsw-state-card span { display: block; color: #646970; font-size: 12px; }
            .qsw-panel { padding: 22px; }
            .qsw-panel-head span { display: inline-flex; padding: 3px 8px; color: #135e96; background: #e7f5ff; border-radius: 6px; font-size: 12px; font-weight: 700; }
            .qsw-panel-head h2 { margin: 10px 0 8px; font-size: 24px; line-height: 1.25; }
            .qsw-panel-head p { max-width: 760px; margin: 0 0 18px; color: #50575e; line-height: 1.7; }
            .qsw-feature-grid, .qsw-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 18px 0; }
            .qsw-feature-grid div, .qsw-summary-grid div, .qsw-existing, .qsw-note { padding: 14px; background: #f6f7f7; border: 1px solid #e2e4e7; border-radius: 8px; }
            .qsw-feature-grid strong, .qsw-summary-grid strong { display: block; margin-bottom: 5px; color: #1d2327; }
            .qsw-feature-grid span, .qsw-summary-grid span { color: #50575e; line-height: 1.55; }
            .qsw-existing h3 { margin-top: 0; }
            .qsw-existing ul { margin: 0 0 10px 18px; }
            .qsw-warning { margin: 10px 0 0; color: #8a4b00; font-weight: 700; }
            .qsw-choice-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; margin: 10px 0 20px; }
            .qsw-choice { display: flex; align-items: center; gap: 8px; min-height: 42px; padding: 10px 12px; border: 1px solid #dcdcde; border-radius: 8px; background: #fbfbfc; }
            .qsw-template-grid, .qsw-page-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; margin: 10px 0 20px; }
            .qsw-template-choice, .qsw-page-choice { display: flex; align-items: flex-start; gap: 9px; min-height: 58px; padding: 12px; border: 1px solid #dcdcde; border-radius: 8px; background: #fbfbfc; }
            .qsw-template-choice strong, .qsw-template-choice span, .qsw-page-choice strong, .qsw-page-choice em { display: block; }
            .qsw-template-choice span, .qsw-page-choice em { margin-top: 4px; color: #646970; font-size: 12px; font-style: normal; word-break: break-all; }
            .qsw-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 14px 0; }
            .qsw-field { display: grid; gap: 6px; }
            .qsw-field label { font-weight: 600; color: #1d2327; }
            .qsw-field input, .qsw-field textarea { width: 100%; max-width: 100%; }
            .qsw-field-wide { grid-column: 1 / -1; }
            .qsw-option-list { display: grid; gap: 10px; margin: 14px 0; padding: 14px; border: 1px solid #e2e4e7; border-radius: 8px; background: #f6f7f7; }
            .qsw-option-list label { display: flex; align-items: center; gap: 8px; }
            .qsw-last-run { margin: 16px 0; padding: 14px; border: 1px solid #dcdcde; border-radius: 8px; background: #fcfcfd; }
            .qsw-last-run h3 { margin-top: 0; }
            .qsw-last-run ul { margin: 0; }
            .qsw-last-run li { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f0f0f1; }
            .qsw-last-run li:last-child { border-bottom: 0; }
            .qsw-last-run span { color: #646970; font-size: 12px; }
            .qsw-preset-preview { margin: 22px 0 0; padding: 16px; border: 1px solid #dcdcde; border-radius: 8px; background: #fcfcfd; }
            .qsw-preview-head h3 { margin: 0 0 6px; }
            .qsw-preview-head p { margin: 0 0 14px; color: #50575e; line-height: 1.6; }
            .qsw-preview-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .qsw-preview-card { padding: 12px; border: 1px solid #e2e4e7; border-radius: 8px; background: #fff; }
            .qsw-preview-card h4, .qsw-plugin-hints h4 { margin: 0 0 10px; }
            .qsw-preview-card ul { margin: 0; }
            .qsw-preview-card li { display: flex; justify-content: space-between; gap: 10px; margin: 0; padding: 7px 0; border-bottom: 1px solid #f0f0f1; }
            .qsw-preview-card li:last-child { border-bottom: 0; }
            .qsw-preview-card li span { color: #646970; font-size: 12px; text-align: right; word-break: break-all; }
            .qsw-chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
            .qsw-chip { display: inline-flex; align-items: center; min-height: 28px; padding: 4px 9px; border: 1px solid #dcdcde; border-radius: 999px; background: #f6f7f7; color: #1d2327; }
            .qsw-plugin-hints { margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e4e7; }
            .qsw-plugin-hints p, .qsw-muted { color: #646970; }
            .qsw-finish-card { margin: 18px 0; padding: 16px; border: 1px solid #dcdcde; border-radius: 8px; background: #fcfcfd; }
            .qsw-finish-grid, .qsw-next-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .qsw-finish-item { min-height: 82px; padding: 12px; border: 1px solid #e2e4e7; border-left: 4px solid #dcdcde; border-radius: 8px; background: #fff; }
            .qsw-finish-item strong, .qsw-finish-item span, .qsw-next-grid strong, .qsw-next-grid span { display: block; }
            .qsw-finish-item span, .qsw-next-grid span { margin-top: 5px; color: #646970; line-height: 1.55; }
            .qsw-finish-item.is-done { border-left-color: #00a32a; }
            .qsw-finish-item.is-pending { border-left-color: #dba617; }
            .qsw-next-grid a { min-height: 78px; padding: 12px; border: 1px solid #dcdcde; border-radius: 8px; background: #fff; color: #1d2327; text-decoration: none; }
            .qsw-next-grid a:hover { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }
            .qsw-cleanup-card { border-color: #f0c36d; background: #fffaf0; }
            .qsw-cleanup-summary { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0 14px; }
            .qsw-cleanup-summary span { display: inline-flex; min-height: 28px; align-items: center; padding: 4px 9px; border-radius: 999px; background: #fff; border: 1px solid #e2e4e7; color: #50575e; }
            .qsw-cleanup-list { margin: 12px 0; padding: 12px; border: 1px solid #e2e4e7; border-radius: 8px; background: #fff; }
            .qsw-cleanup-list h4 { margin: 0 0 8px; }
            .qsw-cleanup-list ul { margin: 0; }
            .qsw-cleanup-list li { display: grid; grid-template-columns: minmax(0, 1fr) minmax(160px, .9fr) auto; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f0f0f1; }
            .qsw-cleanup-list li:last-child { border-bottom: 0; }
            .qsw-cleanup-list li strong, .qsw-cleanup-list li span, .qsw-cleanup-list li em { display: block; }
            .qsw-cleanup-list li span { color: #646970; line-height: 1.45; }
            .qsw-cleanup-list li em { color: #8c8f94; font-style: normal; font-size: 12px; }
            .qsw-cleanup-list li.is-eligible strong { color: #1d2327; }
            .qsw-cleanup-list li.is-skipped strong { color: #8a4b00; }
            .qsw-cleanup-form .qsw-option-list { background: #fff; }
            .qsw-cleanup-confirm span { font-weight: 700; color: #8a4b00; }
            .qsw-danger-button { color: #b32d2e !important; border-color: #b32d2e !important; }
            .qsw-plugin-section { margin: 18px 0; }
            .qsw-plugin-section h3 { margin: 0 0 10px; }
            .qsw-plugin-list { display: grid; gap: 10px; margin: 18px 0; }
            .qsw-plugin-list-compact { margin-top: 10px; }
            .qsw-plugin-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border: 1px solid #dcdcde; border-radius: 8px; background: #fbfbfc; }
            .qsw-plugin-item strong, .qsw-plugin-item span { display: block; }
            .qsw-plugin-item strong mark { display: inline-flex; margin-left: 8px; padding: 2px 6px; color: #135e96; background: #e7f5ff; border-radius: 999px; font-size: 12px; font-weight: 700; }
            .qsw-plugin-item span { color: #646970; margin-top: 4px; }
            .qsw-plugin-item small { display: block; margin-top: 5px; color: #8c8f94; }
            .qsw-plugin-item em { flex: 0 0 auto; padding: 4px 8px; font-style: normal; border-radius: 999px; background: #f0f0f1; }
            .qsw-plugin-item.is-active em { color: #008a20; background: #edfaef; }
            .qsw-plugin-item.is-inactive em { color: #8a4b00; background: #fff3cd; }
            .qsw-plugin-item.is-missing em { color: #646970; background: #f0f0f1; }
            .qsw-plugin-item.is-recommended { border-color: #b6d7f2; background: #f6fbff; }
            .qsw-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 20px; }
            .qsw-inline-form { display: inline-flex; margin: 0; }
            .qsw-danger-link { color: #b32d2e; }
            @media (max-width: 900px) {
                .qsw-shell { grid-template-columns: 1fr; }
                .qsw-feature-grid, .qsw-summary-grid, .qsw-preview-grid, .qsw-form-grid, .qsw-finish-grid, .qsw-next-grid { grid-template-columns: 1fr; }
                .qsw-cleanup-list li { grid-template-columns: 1fr; }
            }
        </style>
        <?php
    }
}
