<?php
/**
 * 官方模板中心后台页面。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Template_Center_Admin {

    /**
     * 后台页面 Slug 标识。
     *
     * @var string
     */
    private $page_slug = 'developer-starter-template-center';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_submenu_page' ), 18 );
    }

    /**
     * 在主题设置下注册向导子菜单项。
     *
     * @return void
     */
    public function register_submenu_page() {
        $hook = add_submenu_page(
            'qiling-settings',
            __( '模板中心', 'qiling' ),
            __( '模板中心', 'qiling' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'render_page' )
        );

        if ( is_string( $hook ) && '' !== $hook ) {
            add_action( 'load-' . $hook, array( $this, 'maybe_handle_create_request' ) );
        }
    }

    /**
     * 在后台页面开始渲染前处理页面创建请求。
     *
     * @return void
     */
    public function maybe_handle_create_request() {
        $request_method = isset( $_SERVER['REQUEST_METHOD'] ) && is_scalar( $_SERVER['REQUEST_METHOD'] )
            ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) )
            : '';

        if ( 'POST' !== $request_method ) {
            return;
        }

        $this->handle_create_request();
    }

    /**
     * 渲染官方模板中心后台界面。
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'qiling' ) );
        }

        $catalog = $this->get_template_catalog();
        $filters = $this->get_current_filters();
        $category_choices = $this->get_category_choices( $catalog );
        $industry_choices = $this->get_industry_choices( $catalog );
        $industry_coverage = $this->get_industry_coverage_data( $catalog );
        $stats = $this->get_catalog_stats( $catalog, $industry_coverage );
        $demo_data_count = class_exists( '\Developer_Starter\Admin\Template_Demo_Data_Service' )
            ? \Developer_Starter\Admin\Template_Demo_Data_Service::get_demo_data_count()
            : 0;

        $scenario_groups = $this->get_scenario_groups();
        $group_counts = array( 'all' => count( $catalog ) );
        foreach ( $catalog as $entry ) {
            $grp = isset( $entry['scenario_group'] ) ? (string) $entry['scenario_group'] : 'featured';
            if ( ! isset( $group_counts[ $grp ] ) ) {
                $group_counts[ $grp ] = 0;
            }
            $group_counts[ $grp ]++;
        }

        $active_scenario = ! empty( $filters['scenario_group'] ) && 'all' !== $filters['scenario_group']
            ? $filters['scenario_group']
            : ( ! empty( $filters['search'] ) || 'all' !== $filters['category'] || 'all' !== $filters['industry'] || 'all' !== $filters['usage'] ? 'all' : 'featured' );

        ?>
        <div class="wrap qiling-template-center">
            <!-- 头部标题与控制区 -->
            <div class="qtc-page-header">
                <div class="qtc-header-left">
                    <h1 class="qtc-page-title">
                        <?php esc_html_e( '模板中心', 'qiling' ); ?>
                        <span class="qtc-title-badge"><?php echo esc_html( sprintf( __( '共 %d 套行业与业务模板', 'qiling' ), count( $catalog ) ) ); ?></span>
                    </h1>
                    <p class="qtc-page-desc">
                        <?php esc_html_e( '开箱即用的模块化行业官网、DTC商城、SaaS及线索转化落地页。免写代码，一键导入官方预设即可直接上线或二次装修。', 'qiling' ); ?>
                    </p>
                </div>
                <div class="qtc-header-actions">
                    <?php if ( $demo_data_count > 0 ) : ?>
                        <div class="qtc-demo-badge-wrap">
                            <span class="qtc-demo-pill" title="<?php esc_attr_e( '全站当前存在的模版配套演示数据文章总数', 'qiling' ); ?>">
                                📦 <?php echo esc_html( sprintf( __( '全站演示数据：%d 篇', 'qiling' ), $demo_data_count ) ); ?>
                            </span>
                            <form method="post" class="qtc-clear-demo-form" onsubmit="return confirm('<?php echo esc_js( __( '确定要一键彻底删除全站所有行业模版演示数据吗？此操作不可逆。', 'qiling' ) ); ?>');">
                                <?php wp_nonce_field( 'developer_starter_template_center_clear_demo' ); ?>
                                <input type="hidden" name="qiling_template_center_action" value="clear_demo_data" />
                                <button type="submit" class="button qtc-clear-demo-btn">
                                    🗑️ <?php esc_html_e( '清空', 'qiling' ); ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <button type="button" id="qtc-toggle-dashboard" class="button qtc-toggle-dashboard-btn" aria-expanded="false">
                        <span class="dashicons dashicons-chart-pie"></span>
                        <span class="qtc-toggle-text"><?php esc_html_e( '行业覆盖全景与统计', 'qiling' ); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2 qtc-toggle-icon"></span>
                    </button>
                </div>
            </div>

            <?php $this->render_notice(); ?>
            <?php $this->render_styles(); ?>

            <!-- 统计与行业覆盖面板（默认折叠收纳，首屏免下拉干扰） -->
            <div id="qtc-admin-dashboard" class="qtc-admin-dashboard" style="display: none;">
                <?php $this->render_language_notice(); ?>

                <div class="qtc-summary-grid">
                    <div class="qtc-summary-card">
                        <strong><?php echo esc_html( number_format_i18n( $stats['total'] ) ); ?></strong>
                        <span><?php esc_html_e( '官方模板总数', 'qiling' ); ?></span>
                    </div>
                    <div class="qtc-summary-card">
                        <strong><?php echo esc_html( number_format_i18n( $stats['industry'] ) ); ?></strong>
                        <span><?php esc_html_e( '行业模板', 'qiling' ); ?></span>
                    </div>
                    <div class="qtc-summary-card">
                        <strong><?php echo esc_html( number_format_i18n( $stats['non_industry'] ) ); ?></strong>
                        <span><?php esc_html_e( '非行业模板', 'qiling' ); ?></span>
                    </div>
                    <div class="qtc-summary-card">
                        <strong><?php echo esc_html( sprintf( '%s/%s', number_format_i18n( $stats['covered_industries'] ), number_format_i18n( $stats['standard_industries'] ) ) ); ?></strong>
                        <span><?php esc_html_e( '行业覆盖', 'qiling' ); ?></span>
                    </div>
                    <div class="qtc-summary-card">
                        <strong><?php echo esc_html( number_format_i18n( $stats['missing_industries'] ) ); ?></strong>
                        <span><?php esc_html_e( '暂无模板行业', 'qiling' ); ?></span>
                    </div>
                    <div class="qtc-summary-card">
                        <strong><?php echo esc_html( number_format_i18n( $stats['created_pages'] ) ); ?></strong>
                        <span><?php esc_html_e( '使用中页面', 'qiling' ); ?></span>
                    </div>
                    <div class="qtc-summary-card">
                        <strong><?php echo esc_html( number_format_i18n( count( $category_choices ) - 1 ) ); ?></strong>
                        <span><?php esc_html_e( '分组数量', 'qiling' ); ?></span>
                    </div>
                    <div class="qtc-summary-card qtc-summary-demo-card" style="border-left: 3px solid <?php echo $demo_data_count > 0 ? '#f59e0b' : '#10b981'; ?>;">
                        <strong style="color: <?php echo $demo_data_count > 0 ? '#b45309' : '#059669'; ?>;"><?php echo esc_html( number_format_i18n( $demo_data_count ) ); ?></strong>
                        <span><?php esc_html_e( '全站演示数据', 'qiling' ); ?></span>
                    </div>
                </div>

                <div class="qtc-admin-note">
                    <strong><?php esc_html_e( '使用方式：', 'qiling' ); ?></strong>
                    <?php esc_html_e( '点击任意卡片的“创建页面”，系统将自动生成新页面并填充官方模块；“使用中页面”统计站点中已实际应用该模版的页面；可在卡片内展开查看或快速编辑。', 'qiling' ); ?>
                </div>

                <?php $this->render_industry_coverage_panel( $industry_coverage, $filters ); ?>
            </div>

            <!-- 一级场景导航选项卡（Pills Tabs） -->
            <div class="qtc-scenario-nav">
                <div class="qtc-scenario-tabs" role="tablist">
                    <?php foreach ( $scenario_groups as $group_key => $group_info ) :
                        $count = isset( $group_counts[ $group_key ] ) ? $group_counts[ $group_key ] : 0;
                        $is_active = $active_scenario === $group_key;
                    ?>
                        <button type="button"
                                class="qtc-tab-btn<?php echo $is_active ? ' is-active' : ''; ?>"
                                data-group="<?php echo esc_attr( $group_key ); ?>"
                                role="tab"
                                aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                            <span class="dashicons <?php echo esc_attr( $group_info['icon'] ); ?>"></span>
                            <span class="qtc-tab-label"><?php echo esc_html( $group_info['label'] ); ?></span>
                            <span class="qtc-tab-count"><?php echo esc_html( $count ); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 即时搜索与快捷状态工具条 -->
            <div class="qtc-toolbar">
                <div class="qtc-toolbar-search">
                    <span class="dashicons dashicons-search qtc-search-icon"></span>
                    <input type="search"
                           id="qtc-instant-search"
                           class="qtc-instant-search-input"
                           placeholder="<?php esc_attr_e( '输入关键词即时筛选（如：电竞、出海、牙科、独立站、预约、SaaS、机器人）...', 'qiling' ); ?>"
                           value="<?php echo esc_attr( $filters['search'] ); ?>"
                           autocomplete="off" />
                    <button type="button" id="qtc-clear-search" class="qtc-clear-search-btn" style="display: none;" title="<?php esc_attr_e( '清空搜索', 'qiling' ); ?>">&times;</button>
                </div>

                <div class="qtc-toolbar-filters">
                    <div class="qtc-status-filter-pills">
                        <button type="button" class="qtc-pill-btn is-active" data-status="all"><?php esc_html_e( '全部状态', 'qiling' ); ?></button>
                        <button type="button" class="qtc-pill-btn" data-status="used"><?php esc_html_e( '使用中', 'qiling' ); ?></button>
                        <button type="button" class="qtc-pill-btn" data-status="unused"><?php esc_html_e( '未使用', 'qiling' ); ?></button>
                    </div>

                    <div class="qtc-toolbar-counter">
                        <?php esc_html_e( '当前显示', 'qiling' ); ?>
                        <strong id="qtc-visible-count"><?php echo esc_html( count( $catalog ) ); ?></strong>
                        / <?php echo esc_html( sprintf( __( '共 %d 套', 'qiling' ), count( $catalog ) ) ); ?>
                    </div>
                </div>
            </div>

            <!-- 热门搜索推荐标签 -->
            <div class="qtc-hot-tags-bar">
                <span class="qtc-hot-tags-label"><?php esc_html_e( '热门场景直达：', 'qiling' ); ?></span>
                <button type="button" class="qtc-tag-pill" data-keyword="独立商城"># 独立商城</button>
                <button type="button" class="qtc-tag-pill" data-keyword="预约版"># 预约版</button>
                <button type="button" class="qtc-tag-pill" data-keyword="AI"># AI Agent</button>
                <button type="button" class="qtc-tag-pill" data-keyword="SaaS"># SaaS 官网</button>
                <button type="button" class="qtc-tag-pill" data-keyword="出海"># 外贸出海</button>
                <button type="button" class="qtc-tag-pill" data-keyword="机器人"># 工业机器人</button>
                <button type="button" class="qtc-tag-pill" data-keyword="电竞"># 电竞酒店/战队</button>
                <button type="button" class="qtc-tag-pill" data-keyword="诊所"># 口腔/诊所</button>
                <button type="button" class="qtc-tag-pill" data-keyword="家政"># 家政保洁</button>
                <button type="button" class="qtc-tag-pill" data-keyword="律所"># 律师事务所</button>
            </div>

            <!-- 模板卡片网格容器（包含所有卡片，由纯前端毫秒级过滤，免频繁页面刷新） -->
            <div class="qtc-template-grid" id="qtc-template-grid">
                <?php foreach ( $catalog as $template_index => $template ) : ?>
                    <?php
                    $is_initially_visible = ( 'all' === $active_scenario || ( isset( $template['scenario_group'] ) && $template['scenario_group'] === $active_scenario ) );
                    $this->render_template_card( $template, absint( $template_index ), $is_initially_visible );
                    ?>
                <?php endforeach; ?>
            </div>

            <!-- 空状态提示 -->
            <div id="qtc-no-results" class="qtc-no-results" style="display: none;">
                <div class="qtc-no-results-icon">🔍</div>
                <h3><?php esc_html_e( '未找到匹配的模板', 'qiling' ); ?></h3>
                <p><?php esc_html_e( '没有找到与当前筛选条件相符的行业模板，请尝试其他关键词或切换场景分类。', 'qiling' ); ?></p>
                <button type="button" class="button button-primary" id="qtc-reset-filters"><?php esc_html_e( '重置全部筛选', 'qiling' ); ?></button>
            </div>
        </div>
        <?php $this->render_preview_modal(); ?>
        <?php $this->render_scripts(); ?>
        <?php
    }

    /**
     * 处理模板页面创建业务请求。
     *
     * @return void
     */
    private function handle_create_request() {
        $action = sanitize_key( $this->get_request_value( $_POST, 'qiling_template_center_action' ) );

        if ( 'clear_demo_data' === $action ) {
            check_admin_referer( 'developer_starter_template_center_clear_demo' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( '权限不足', 'qiling' ) );
            }

            $cleared_count = 0;
            if ( class_exists( '\Developer_Starter\Admin\Template_Demo_Data_Service' ) ) {
                $cleared_count = \Developer_Starter\Admin\Template_Demo_Data_Service::clear_all_demo_data();
            }

            $redirect = add_query_arg(
                array(
                    'page'         => $this->page_slug,
                    'demo_cleared' => absint( $cleared_count ),
                ),
                admin_url( 'admin.php' )
            );
            wp_safe_redirect( $redirect );
            exit;
        }

        if ( 'create_page' !== $action ) {
            return;
        }

        check_admin_referer( 'developer_starter_template_center_create' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'qiling' ) );
        }

        $catalog = $this->get_template_catalog();

        $template = sanitize_text_field( $this->get_request_value( $_POST, 'template' ) );
        $template = function_exists( 'developer_starter_normalize_page_template_slug' )
            ? developer_starter_normalize_page_template_slug( $template )
            : str_replace( '\\', '/', $template );
        $template_variant = sanitize_key( $this->get_request_value( $_POST, 'template_variant' ) );

        if ( '' === $template ) {
            $this->redirect_with_error( __( '模板无效或不属于启灵官方模板中心。', 'qiling' ) );
        }

        $entry = null;
        $fallback_entry = null;
        foreach ( $catalog as $catalog_entry ) {
            $entry_template = isset( $catalog_entry['template'] ) ? (string) $catalog_entry['template'] : '';
            if ( $template !== $entry_template ) {
                continue;
            }

            if ( null === $fallback_entry ) {
                $fallback_entry = $catalog_entry;
            }

            $entry_variant = isset( $catalog_entry['template_variant'] ) ? sanitize_key( (string) $catalog_entry['template_variant'] ) : '';
            if ( $template_variant === $entry_variant ) {
                $entry = $catalog_entry;
                break;
            }
        }

        if ( ! is_array( $entry ) ) {
            if ( '' !== $template_variant ) {
                $this->redirect_with_error( __( '模板变体无效或不属于当前官方模板。', 'qiling' ) );
            }

            $entry = $fallback_entry;
        }

        if ( ! is_array( $entry ) ) {
            $this->redirect_with_error( __( '模板无效或不属于启灵官方模板中心。', 'qiling' ) );
        }

        $title = sanitize_text_field( $this->get_request_value( $_POST, 'page_title' ) );
        if ( '' === trim( $title ) ) {
            $title = isset( $entry['label'] ) ? (string) $entry['label'] : __( '启灵模板页面', 'qiling' );
        }

        $slug = sanitize_title( $this->get_request_value( $_POST, 'page_slug' ) );
        $set_as_frontpage = '1' === $this->get_request_value( $_POST, 'set_as_frontpage' );
        $status = sanitize_key( $this->get_request_value( $_POST, 'page_status' ) );
        if ( ! in_array( $status, array( 'draft', 'publish' ), true ) ) {
            $status = 'draft';
        }
        if ( $set_as_frontpage ) {
            $status = 'publish';
        }
        $target_language = sanitize_key( $this->get_request_value( $_POST, 'template_target_language' ) );
        $target_market = sanitize_text_field( $this->get_request_value( $_POST, 'template_target_market' ) );
        $localize_template = '1' === $this->get_request_value( $_POST, 'template_localization_enable' );
        if ( ! in_array( $target_language, array_keys( $this->get_template_localization_language_choices() ), true ) ) {
            $target_language = '';
        }

        $post_data = array(
            'post_type'    => 'page',
            'post_status'  => $status,
            'post_title'   => $title,
            'post_content' => '',
            'post_author'  => get_current_user_id() ?: 1,
        );

        if ( '' !== $slug ) {
            $post_data['post_name'] = $slug;
        }

        $post_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $post_id ) ) {
            $this->redirect_with_error( $post_id->get_error_message() );
        }

		update_post_meta( $post_id, '_wp_page_template', $template );
		update_post_meta( $post_id, '_qiling_template_center_source', 'official' );
		update_post_meta( $post_id, '_qiling_template_center_template', $template );
        if ( '' !== $target_language ) {
            update_post_meta( $post_id, '_qiling_template_center_target_language', $target_language );
        }
        if ( '' !== $target_market ) {
            update_post_meta( $post_id, '_qiling_template_center_target_market', $target_market );
        }
        if ( '' !== $template_variant ) {
            update_post_meta( $post_id, '_qiling_template_center_variant', $template_variant );
            do_action( 'developer_starter_template_center_apply_variant', $post_id, $template, $template_variant );
        }

		$filled = false;
		$official_service = $this->get_official_template_package_service();
		if ( $official_service && $official_service->has_package_for_template( $template ) ) {
			$filled = $official_service->apply_package_to_page( $post_id, $template );
			if ( is_wp_error( $filled ) ) {
				$this->redirect_with_error( $filled->get_error_message() );
			}
			$filled = (bool) $filled;
		} elseif ( function_exists( 'developer_starter_maybe_fill_default_modules_for_page_template' ) ) {
			$filled = developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template );
		}

        $localization_result = null;
        if ( $localize_template && '' !== $target_language ) {
            $localization_result = $this->localize_created_template_page( $post_id, $target_language, $target_market );
            update_post_meta( $post_id, '_qiling_template_center_ai_localization_status', is_wp_error( $localization_result ) ? 'failed' : 'completed' );
        }

        $generate_demo_data = '1' === $this->get_request_value( $_POST, 'generate_demo_data' );
        $demo_posts_count   = 0;
        if ( $generate_demo_data && class_exists( '\Developer_Starter\Admin\Template_Demo_Data_Service' ) ) {
            $demo_posts_count = \Developer_Starter\Admin\Template_Demo_Data_Service::generate_demo_data_for_template( $template, $post_id, $title );
        }

        if ( $set_as_frontpage ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', $post_id );
            wp_publish_post( $post_id );
        }

        $redirect = add_query_arg(
            array(
                'page'             => $this->page_slug,
                'template_created' => '1',
                'post_id'          => absint( $post_id ),
                'modules_filled'   => $filled ? '1' : '0',
                'is_frontpage'     => $set_as_frontpage ? '1' : '0',
                'demo_created'     => absint( $demo_posts_count ),
                'template_localized'=> is_array( $localization_result ) ? '1' : '0',
                'template_localization_error' => is_wp_error( $localization_result ) ? $localization_result->get_error_message() : '',
            ),
            admin_url( 'admin.php' )
        );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * 重定向后渲染操作提示消息。
     *
     * @return void
     */
    private function render_notice() {
        if ( '' !== $this->get_request_value( $_GET, 'demo_cleared' ) ) {
            $cleared = absint( $this->get_request_value( $_GET, 'demo_cleared' ) );
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong><?php esc_html_e( '🧹 演示数据清理成功：', 'qiling' ); ?></strong>
                    <?php echo esc_html( sprintf( __( '已彻底清除 %d 篇官方模版配套演示数据，数据库已恢复纯净状态。', 'qiling' ), $cleared ) ); ?>
                </p>
            </div>
            <?php
            return;
        }

        if ( '' !== $this->get_request_value( $_GET, 'template_error' ) ) {
            $message = sanitize_text_field( $this->get_request_value( $_GET, 'template_error' ) );
            ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
            <?php
            return;
        }

        if ( '' === $this->get_request_value( $_GET, 'template_created' ) || '' === $this->get_request_value( $_GET, 'post_id' ) ) {
            return;
        }

        $post_id = absint( $this->get_request_value( $_GET, 'post_id' ) );
        if ( $post_id <= 0 ) {
            return;
        }

        $edit_link = get_edit_post_link( $post_id, '' );
        $preview_link = function_exists( 'get_preview_post_link' ) ? get_preview_post_link( $post_id ) : get_permalink( $post_id );
        $builder_link = $preview_link ? add_query_arg( 'qiling_builder', '1', $preview_link ) : '';
        $filled = '1' === $this->get_request_value( $_GET, 'modules_filled' );
        $is_frontpage = '1' === $this->get_request_value( $_GET, 'is_frontpage' );
        $localized = '1' === $this->get_request_value( $_GET, 'template_localized' );
        $localization_error = sanitize_text_field( $this->get_request_value( $_GET, 'template_localization_error' ) );
        $demo_created = absint( $this->get_request_value( $_GET, 'demo_created' ) );
        $page_title = get_the_title( $post_id );
        if ( '' === trim( $page_title ) ) {
            $page_title = __( '模板页面', 'qiling' );
        }
        $site_home_url = home_url( '/' );
        ?>
        <div class="notice notice-success is-dismissible qtc-import-success-card" style="margin: 20px 0 24px; padding: 20px 24px; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border: 1px solid #86efac; border-left: 6px solid #16a34a; border-radius: 10px; box-shadow: 0 4px 16px rgba(22, 163, 74, 0.08);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; background: #dcfce7; color: #15803d; border-radius: 50%; font-size: 16px; font-weight: bold;">✓</span>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #166534;">
                            <?php echo esc_html( sprintf( __( '太棒了！「%s」页面已成功生成！', 'qiling' ), $page_title ) ); ?>
                        </h3>
                    </div>

                    <p style="margin: 0 0 10px 0; font-size: 14px; line-height: 1.6; color: #374151;">
                        <?php if ( $is_frontpage ) : ?>
                            <strong style="color: #047857; background: #dcfce7; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-bottom: 4px;">
                                🏠 <?php esc_html_e( '已自动设置为网站首页', 'qiling' ); ?>
                            </strong><br />
                            <?php esc_html_e( '您的网站主页现在已呈现该套行业官方完整设计，访客输入网站主域名即可直接访问！', 'qiling' ); ?>
                        <?php else : ?>
                            <?php echo esc_html( $filled ? __( '页面预设模块已全部填充完毕，随时可直接进行前台可视化微调与修改。', 'qiling' ) : __( '页面已创建，但没有填充到模块；请进入页面检查模板或手动装修。', 'qiling' ) ); ?>
                        <?php endif; ?>
                    </p>

                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 10px; font-size: 13px; color: #4b5563;">
                        <?php if ( $demo_created > 0 ) : ?>
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-weight: 500;">
                                📦 <?php echo esc_html( sprintf( __( '已同步生成 %d 篇配套行业演示数据', 'qiling' ), $demo_created ) ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $localized ) : ?>
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 6px; font-weight: 500;">
                                🌐 <?php esc_html_e( '已同步生成对应语言页面', 'qiling' ); ?>
                            </span>
                        <?php elseif ( '' !== $localization_error ) : ?>
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 6px;">
                                ⚠️ <?php echo esc_html( sprintf( __( '自动本地化未完成：%s', 'qiling' ), $localization_error ) ); ?>
                            </span>
                        <?php endif; ?>
                        <span style="display: inline-flex; align-items: center; gap: 4px; color: #6b7280; font-size: 12px;">
                            💡 <?php esc_html_e( '新手推荐：点击右侧【立即打开前台可视化装修】，鼠标悬停任意文字图片直接点击修改！', 'qiling' ); ?>
                        </span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; min-width: 230px;">
                    <?php if ( $builder_link ) : ?>
                        <a href="<?php echo esc_url( $builder_link ); ?>" target="_blank" class="button button-primary" style="background: #16a34a; border-color: #15803d; color: #ffffff; padding: 8px 16px; font-size: 14px; font-weight: 600; height: auto; text-align: center; border-radius: 8px; box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            🎨 <?php esc_html_e( '立即打开前台可视化装修', 'qiling' ); ?> &rarr;
                        </a>
                    <?php endif; ?>
                    <div style="display: flex; gap: 8px;">
                        <?php if ( $is_frontpage ) : ?>
                            <a href="<?php echo esc_url( $site_home_url ); ?>" target="_blank" class="button button-secondary" style="flex: 1; text-align: center; padding: 6px 10px; height: auto; border-radius: 6px; font-size: 13px;">
                                🏠 <?php esc_html_e( '查看网站首页', 'qiling' ); ?>
                            </a>
                        <?php elseif ( $preview_link ) : ?>
                            <a href="<?php echo esc_url( $preview_link ); ?>" target="_blank" class="button button-secondary" style="flex: 1; text-align: center; padding: 6px 10px; height: auto; border-radius: 6px; font-size: 13px;">
                                👁️ <?php esc_html_e( '预览页面', 'qiling' ); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ( $edit_link ) : ?>
                            <a href="<?php echo esc_url( $edit_link ); ?>" class="button button-secondary" style="flex: 1; text-align: center; padding: 6px 10px; height: auto; border-radius: 6px; font-size: 13px;">
                                ⚙️ <?php esc_html_e( '后台编辑', 'qiling' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 获取语言与适用场景提示信息。
     *
     * @return array{title:string,content:string}
     */
    private function get_language_notice_data() {
        $title   = __( '语言与适用场景提示', 'qiling' );
        $content = __( '主题内置的 100+ 套行业模板及向导推荐页面主要针对中文建站场景设计（演示数据、栏目结构及文案均为中文）。如果您正在搭建外贸站、出海或多语言站点，仍可自由使用主题全部排版能力，但建议跳过预设导入，根据业务自行创建页面或按需替换文案。', 'qiling' );

        if ( 0 !== strpos( get_user_locale(), 'zh' ) ) {
            if ( '语言与适用场景提示' === $title ) {
                $title = 'Language & Scenario Notice';
            }
            if ( 0 === strpos( $content, '主题内置' ) ) {
                $content = 'The 100+ built-in industry templates and wizard starter presets are tailored for Chinese websites (demo content, sample articles, and layouts are in Chinese). If you are building an English, international, or multilingual website, you can still fully utilize all theme features and blocks, but we recommend creating your pages manually or translating the starter content to suit your needs.';
            }
        }

        return array(
            'title'   => $title,
            'content' => $content,
        );
    }

    /**
     * 渲染语言与适用场景提示横幅。
     *
     * @return void
     */
    private function render_language_notice() {
        $notice = $this->get_language_notice_data();
        ?>
        <div class="qtc-language-notice">
            <div class="qtc-language-notice__icon" aria-hidden="true">💡</div>
            <div class="qtc-language-notice__body">
                <strong><?php echo esc_html( $notice['title'] ); ?></strong>
                <p><?php echo esc_html( $notice['content'] ); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * 携带错误提示信息重定向。
     *
     * @param string $message Error message.
     * @return void
     */
	private function redirect_with_error( $message ) {
		$redirect = add_query_arg(
			array(
				'page'           => $this->page_slug,
                'template_error' => (string) $message,
            ),
            admin_url( 'admin.php' )
        );
		wp_safe_redirect( $redirect );
		exit;
	}

    /**
     * 通过 AI Decorator 为新建模板页面进行本地化装饰。
     *
     * @param int    $post_id         Created page ID.
     * @param string $target_language Target language.
     * @param string $target_market   Target market.
     * @return array<string,mixed>|\WP_Error|null
     */
    private function localize_created_template_page( $post_id, $target_language, $target_market = '' ) {
        $post_id = absint( $post_id );
        $target_language = sanitize_key( (string) $target_language );
        if ( $post_id <= 0 || '' === $target_language ) {
            return null;
        }

        if ( ! class_exists( '\Developer_Starter\Core\AI_Decorator' ) ) {
            return new \WP_Error( 'ai_decorator_unavailable', __( 'AI 装修服务未加载，模板已创建但未自动本地化。', 'qiling' ) );
        }

        $decorator = \Developer_Starter\Core\AI_Decorator::get_instance();
        if ( ! $decorator->is_enabled() ) {
            return new \WP_Error( 'ai_decorator_disabled', __( 'AI 装修尚未启用，模板已创建但未自动本地化。', 'qiling' ) );
        }

        $defaults = $decorator->get_default_ai_connection_request_args();

        return $decorator->localize_page_package(
            array(
                'post_id'       => $post_id,
                'prompt'        => __( '本地化模板中心导入的页面包，并保留原模板结构。', 'qiling' ),
                'connection_id' => isset( $defaults['connection_id'] ) ? (string) $defaults['connection_id'] : '',
                'model'         => isset( $defaults['model'] ) ? (string) $defaults['model'] : '',
                'localization'  => array(
                    'scope'                => 'template_package',
                    'target_language'      => $target_language,
                    'target_market'        => $target_market,
                    'create_language_page' => true,
                    'sync_provider'        => true,
                ),
            )
        );
    }

    /**
     * 模板导入本地化语言选项列表。
     *
     * @return array<string,string>
     */
    private function get_template_localization_language_choices() {
        return array(
            'en' => __( '英文', 'qiling' ),
            'ja' => __( '日文', 'qiling' ),
            'ko' => __( '韩文', 'qiling' ),
            'fr' => __( '法文', 'qiling' ),
            'de' => __( '德文', 'qiling' ),
            'es' => __( '西班牙文', 'qiling' ),
        );
    }

	/**
	 * 获取官方 JSON 模板包服务实例。
	 *
	 * @return \Developer_Starter\Core\Official_Template_Package_Service|null
	 */
	private function get_official_template_package_service() {
		if ( ! class_exists( '\Developer_Starter\Core\Official_Template_Package_Service' ) ) {
			return null;
		}

		return new \Developer_Starter\Core\Official_Template_Package_Service();
	}

	/**
	 * 从 URL 查询参数中获取当前筛选条件。
	 *
	 * @return array<string,string>
     */
	private function get_current_filters() {
		return array(
			'category'       => sanitize_key( $this->get_request_value( $_GET, 'template_category', 'all' ) ),
			'industry'       => $this->normalize_industry_key( $this->get_request_value( $_GET, 'template_industry', 'all' ) ),
			'usage'          => sanitize_key( $this->get_request_value( $_GET, 'template_usage', 'all' ) ),
            'search'         => sanitize_text_field( $this->get_request_value( $_GET, 's' ) ),
            'scenario_group' => sanitize_key( $this->get_request_value( $_GET, 'scenario_group', 'all' ) ),
        );
    }

    /**
     * 构建官方模板目录数据。
     *
     * @return array<int,array<string,mixed>>
     */
	private function get_template_catalog() {
		$map = function_exists( 'developer_starter_get_page_template_default_modules_map' )
			? developer_starter_get_page_template_default_modules_map()
			: array();

        if ( ! is_array( $map ) || empty( $map ) ) {
            return array();
        }

		$page_templates = wp_get_theme()->get_page_templates( null, 'page' );
		$page_templates = is_array( $page_templates ) ? $page_templates : array();
		$usage_data = $this->get_template_usage_data( array_keys( $map ) );
		$official_service = $this->get_official_template_package_service();
		$catalog = array();

		foreach ( $map as $template => $config ) {
            $template = function_exists( 'developer_starter_normalize_page_template_slug' )
                ? developer_starter_normalize_page_template_slug( $template )
                : str_replace( '\\', '/', (string) $template );

            if ( '' === $template || ! $this->template_exists( $template, $page_templates ) ) {
                continue;
            }

			$label = $this->get_template_label( $template, $page_templates );
			$meta = $this->get_template_meta( $template, $label, is_array( $config ) ? $config : array() );
			if ( $official_service && $official_service->has_package_for_template( $template ) ) {
				$json_meta = $official_service->get_catalog_meta_for_template( $template );
				if ( ! is_wp_error( $json_meta ) && is_array( $json_meta ) ) {
					$meta = array_merge( $meta, $json_meta );
				}
			}

            $template_slug = sanitize_key( basename( $template, '.php' ) );
			$catalog[] = array_merge(
				array(
					'id'             => $template_slug,
                    'template'       => $template,
					'label'          => $label,
                    'scenario_group' => $this->get_template_scenario_group( $template_slug, $meta ),
                    'source'         => 'official',
                    'source_label'   => __( '启灵官方', 'qiling' ),
                    'modules_count'  => is_array( $config ) ? count( $config ) : 0,
                    'created_pages'  => isset( $usage_data[ $template ]['count'] ) ? absint( $usage_data[ $template ]['count'] ) : 0,
                    'usage_pages'    => isset( $usage_data[ $template ]['pages'] ) && is_array( $usage_data[ $template ]['pages'] ) ? $usage_data[ $template ]['pages'] : array(),
                ),
                $meta
            );
        }

        usort(
            $catalog,
            function( $a, $b ) {
                $order_a = isset( $a['order'] ) ? absint( $a['order'] ) : 999;
                $order_b = isset( $b['order'] ) ? absint( $b['order'] ) : 999;
                if ( $order_a === $order_b ) {
                    return strcmp( (string) $a['label'], (string) $b['label'] );
                }
                return $order_a < $order_b ? -1 : 1;
            }
        );

        /**
         * 过滤官方模板中心目录数据。
         *
         * 此钩子用于本地拓展，不涉及第三方外部接口，
         * 也不涉及第三方作者列表展示。
         *
         * @param array<int,array<string,mixed>> $catalog Template entries.
         */
        return apply_filters( 'developer_starter_template_center_catalog', $catalog );
    }

    /**
     * 获取高频业务场景大分类定义。
     *
     * @return array<string,array{label:string,icon:string}>
     */
    private function get_scenario_groups() {
        return array(
            'all'                  => array( 'label' => __( '全部', 'qiling' ), 'icon' => 'dashicons-grid-view' ),
            'featured'             => array( 'label' => __( '常用精选', 'qiling' ), 'icon' => 'dashicons-star-filled' ),
            'ai_tech'              => array( 'label' => __( 'AI与科技', 'qiling' ), 'icon' => 'dashicons-rest-api' ),
            'ecommerce_trade'      => array( 'label' => __( '出海与商城', 'qiling' ), 'icon' => 'dashicons-cart' ),
            'manufacturing_energy' => array( 'label' => __( '制造与新能源', 'qiling' ), 'icon' => 'dashicons-admin-tools' ),
            'local_store'          => array( 'label' => __( '本地生活与预约', 'qiling' ), 'icon' => 'dashicons-store' ),
            'health_care'          => array( 'label' => __( '医疗与康养', 'qiling' ), 'icon' => 'dashicons-heart' ),
            'professional_service' => array( 'label' => __( '专业服务与教育', 'qiling' ), 'icon' => 'dashicons-welcome-learn-more' ),
            'media_entertainment' => array( 'label' => __( '文娱传媒与生态', 'qiling' ), 'icon' => 'dashicons-video-alt3' ),
        );
    }

    /**
     * 根据模板 Slug 和元数据将模板精准收纳进场景大类。
     *
     * @param string              $slug Template slug without .php.
     * @param array<string,mixed> $meta Template metadata.
     * @return string
     */
    private function get_template_scenario_group( $slug, $meta = array() ) {
        $slug = str_replace( 'template-', '', $slug );

        // 常用精选 / 通用官网
        $featured_slugs = array(
            'home', 'solutions', 'products', 'cases', 'landing',
            'features-showcase', 'blog', 'news', 'topic', 'resources',
            'resource-search', 'data-showcase', 'resume', 'interactive-product-launch',
            'video-hero', 'app-download-landing',
        );
        if ( in_array( $slug, $featured_slugs, true ) ) {
            return 'featured';
        }

        // AI 与科技 / SaaS
        $ai_tech_slugs = array(
            'ai-agent-enterprise', 'ai-product-brand', 'developer-platform',
            'cybersecurity-brand', 'data-intelligence-bi', 'open-source-devtools',
            'saas-pricing', 'saas-home', 'software-home', 'software-intro',
            'hosting-saas-home', 'tech-company-integrated', 'enterprise-software-integrator',
            'qiling-cloud-canvas', 'qiling-doc-ocr-converter', 'qiling-image-studio',
            'qiling-ai-writing-studio', 'qiling-ai-multilingual-seo',
        );
        if ( in_array( $slug, $ai_tech_slugs, true ) || false !== strpos( $slug, 'ai' ) ) {
            return 'ai_tech';
        }

        // 跨境出海与独立商城
        $ecommerce_slugs = array(
            'brand-dtc-store', 'foreign-trade-store', 'foreign-trade-b2b',
            'cross-border-ecommerce-service', 'overseas-warehouse-supply-chain',
            'fashion-brand', 'intimate-wellness', 'ecommerce-promo',
            'luxury-watch', 'sneakers-footwear', 'leather-bags', 'fine-jewelry',
        );
        if ( in_array( $slug, $ecommerce_slugs, true ) || false !== strpos( $slug, 'trade' ) || false !== strpos( $slug, 'ecommerce' ) ) {
            return 'ecommerce_trade';
        }

        // 实体制造、先进工业与新能源环保
        $manufacturing_slugs = array(
            'industrial-automation-robotics', 'semiconductor-electronics', 'solar-storage-equipment',
            'ev-charging-station', 'water-treatment-environmental', 'energy-environment',
            'manufacturing-factory', 'industrial-park', 'lab-instrument', 'medical-device',
            'agriculture-food',
        );
        if ( in_array( $slug, $manufacturing_slugs, true ) ) {
            return 'manufacturing_energy';
        }

        // 本地生活、到店预约与线下门店
        $local_store_slugs = array(
            'beauty-salon', 'auto-service', 'homestay', 'restaurant', 'gym-fitness',
            'yoga-studio', 'pet', 'appliance-repair-service', 'qiling-housekeeping-official',
            'qiling-recycling-official', 'wedding-photography', 'chain-store-official',
            'local-service-official', 'real-estate-service', 'renovation-construction', 'travel',
        );
        if ( in_array( $slug, $local_store_slugs, true ) ) {
            return 'local_store';
        }

        // 医疗健康与民生康养
        $health_slugs = array(
            'dental-clinic', 'healthcare-clinic', 'medical-beauty', 'psychological-counseling',
            'senior-care-center', 'postpartum-care-center', 'wellness-center', 'health-supplements',
        );
        if ( in_array( $slug, $health_slugs, true ) ) {
            return 'health_care';
        }

        // 专业服务与教育咨询
        $professional_slugs = array(
            'law-firm', 'accounting-tax-service', 'intellectual-property-service',
            'finance-consulting', 'recruitment-hr-service', 'marketing-pr-agency',
            'mcn-live-commerce', 'course-enrollment', 'early-childhood-education',
            'vocational-training-school', 'study-abroad-immigration', 'architecture-design-studio',
            'interior-soft-decoration', 'landscape-garden-design', 'conference-event-service',
            'franchise-investment', 'property-management', 'logistics-supply-chain',
            'government-public-service', 'nonprofit-organization',
        );
        if ( in_array( $slug, $professional_slugs, true ) ) {
            return 'professional_service';
        }

        // 文娱传媒与启灵生态应用
        $media_slugs = array(
            'video-portal', 'video-ranking', 'game-studio', 'esports-team', 'esports-hotel',
            'qiling-wallpaper-gallery', 'qiling-bbs-support-community', 'qiling-escrow-platform',
            'qiling-freetask-platform', 'qiling-friends-matchmaking', 'qiling-cloud-storage-hosting',
            'qiling-security-ops', 'personal-ip-home', 'event-exhibition',
        );
        if ( in_array( $slug, $media_slugs, true ) ) {
            return 'media_entertainment';
        }

        return 'featured';
    }

    /**
     * 根据后台筛选条件过滤模板目录。
     *
     * @param array<int,array<string,mixed>> $catalog Catalog.
     * @param array<string,string>           $filters Filters.
     * @return array<int,array<string,mixed>>
     */
    private function filter_catalog( $catalog, $filters ) {
        $search = isset( $filters['search'] ) ? trim( (string) $filters['search'] ) : '';
        $category = isset( $filters['category'] ) ? sanitize_key( $filters['category'] ) : 'all';
        $industry = isset( $filters['industry'] ) ? sanitize_key( $filters['industry'] ) : 'all';
        $usage = isset( $filters['usage'] ) ? sanitize_key( $filters['usage'] ) : 'all';

        return array_values(
            array_filter(
                $catalog,
                function( $entry ) use ( $search, $category, $industry, $usage ) {
                    if ( 'all' !== $category && ( ! isset( $entry['category'] ) || $category !== $entry['category'] ) ) {
                        return false;
                    }

                    $entry_industry = isset( $entry['industry'] ) ? $this->normalize_industry_key( $entry['industry'] ) : '';
                    if ( 'all' !== $industry && $industry !== $entry_industry ) {
                        return false;
                    }

                    $usage_count = isset( $entry['created_pages'] ) ? absint( $entry['created_pages'] ) : 0;
                    if ( 'used' === $usage && $usage_count < 1 ) {
                        return false;
                    }

                    if ( 'unused' === $usage && $usage_count > 0 ) {
                        return false;
                    }

                    if ( '' === $search ) {
                        return true;
                    }

                    $haystack = implode(
                        ' ',
                        array_filter(
                            array(
                                isset( $entry['label'] ) ? (string) $entry['label'] : '',
                                isset( $entry['description'] ) ? (string) $entry['description'] : '',
                                isset( $entry['category_label'] ) ? (string) $entry['category_label'] : '',
                                isset( $entry['industry_label'] ) ? (string) $entry['industry_label'] : '',
                                isset( $entry['scenario'] ) ? (string) $entry['scenario'] : '',
                                isset( $entry['template'] ) ? (string) $entry['template'] : '',
                                ! empty( $entry['badges'] ) && is_array( $entry['badges'] ) ? implode( ' ', array_map( 'strval', $entry['badges'] ) ) : '',
                            )
                        )
                    );

                    return false !== stripos( $haystack, $search );
                }
            )
        );
    }

    /**
     * 渲染单个模板卡片组件。
     *
     * @param array<string,mixed> $template Template entry.
     * @param int                 $card_index Card index in the current result set.
     * @param bool                $is_visible Initial visibility state.
     * @return void
     */
	private function render_template_card( $template, $card_index = 0, $is_visible = true ) {
		$label = isset( $template['label'] ) ? (string) $template['label'] : '';
		$template_file = isset( $template['template'] ) ? (string) $template['template'] : '';
        $template_variant = isset( $template['template_variant'] ) ? sanitize_key( (string) $template['template_variant'] ) : '';
		$badges = isset( $template['badges'] ) && is_array( $template['badges'] ) ? $template['badges'] : array();
		$thumbnail = isset( $template['thumbnail'] ) ? esc_url( (string) $template['thumbnail'] ) : '';
		$source_label = isset( $template['source_label'] ) ? (string) $template['source_label'] : __( '启灵官方', 'qiling' );
		$category_label = isset( $template['category_label'] ) ? (string) $template['category_label'] : '';
		$description = isset( $template['description'] ) ? (string) $template['description'] : '';
		$industry_label = isset( $template['industry_label'] ) ? (string) $template['industry_label'] : __( '通用', 'qiling' );
		$scenario = isset( $template['scenario'] ) ? (string) $template['scenario'] : '';
		$usage_count = isset( $template['created_pages'] ) ? absint( $template['created_pages'] ) : 0;
		$usage_pages = isset( $template['usage_pages'] ) && is_array( $template['usage_pages'] ) ? $template['usage_pages'] : array();
		$modules_count = isset( $template['modules_count'] ) ? absint( $template['modules_count'] ) : 0;
		$defer_thumbnail = '' !== $thumbnail && $card_index >= 6;
		$thumbnail_src = $defer_thumbnail ? 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==' : $thumbnail;
		$thumbnail_class = $defer_thumbnail ? ' qtc-thumb-lazy' : ' is-loaded';
		$preview_url = \Developer_Starter\Core\Template_Preview_Service::get_preview_url( $template_file );

        $scenario_group = isset( $template['scenario_group'] ) ? (string) $template['scenario_group'] : 'featured';
        $category = isset( $template['category'] ) ? (string) $template['category'] : '';
        $industry = isset( $template['industry'] ) ? (string) $template['industry'] : '';
        $search_terms = mb_strtolower( $label . ' ' . $description . ' ' . $scenario . ' ' . $industry_label . ' ' . $category_label . ' ' . implode( ' ', $badges ) . ' ' . $template_file );
        $style_attr = $is_visible ? '' : ' style="display: none;"';
		?>
		<article class="qtc-template-card<?php echo $usage_count > 0 ? ' is-used' : ' is-unused'; ?>"
                 data-scenario-group="<?php echo esc_attr( $scenario_group ); ?>"
                 data-category="<?php echo esc_attr( $category ); ?>"
                 data-industry="<?php echo esc_attr( $industry ); ?>"
                 data-used="<?php echo $usage_count > 0 ? '1' : '0'; ?>"
                 data-title="<?php echo esc_attr( mb_strtolower( $label ) ); ?>"
                 data-search="<?php echo esc_attr( $search_terms ); ?>"<?php echo $style_attr; ?>>
			<div class="qtc-card-thumb<?php echo '' === $thumbnail ? ' qtc-card-thumb-placeholder' : ''; ?><?php echo $defer_thumbnail ? ' is-deferred' : ''; ?>">
				<?php if ( '' !== $thumbnail ) : ?>
					<img
                        class="qtc-thumb-image<?php echo esc_attr( $thumbnail_class ); ?>"
                        src="<?php echo esc_url( $thumbnail_src ); ?>"
                        <?php if ( $defer_thumbnail ) : ?>
                            data-qtc-src="<?php echo esc_url( $thumbnail ); ?>"
                            loading="lazy"
                            fetchpriority="low"
                        <?php else : ?>
                            loading="<?php echo 0 === $card_index ? 'eager' : 'lazy'; ?>"
                            fetchpriority="<?php echo 0 === $card_index ? 'high' : 'low'; ?>"
                        <?php endif; ?>
                        decoding="async"
                        width="800"
                        height="450"
                        alt="<?php echo esc_attr( $label ); ?>"
                    />
				<?php else : ?>
					<span><?php echo esc_html( '' !== $category_label ? $category_label : __( '官方模板', 'qiling' ) ); ?></span>
				<?php endif; ?>
				<button type="button" class="qtc-thumb-preview-overlay qtc-preview-trigger" data-preview-url="<?php echo esc_url( $preview_url ); ?>" data-title="<?php echo esc_attr( $label ); ?>" data-category="<?php echo esc_attr( $category_label ); ?>" data-template="<?php echo esc_attr( $template_file ); ?>">
					<span class="dashicons dashicons-visibility"></span> <span><?php esc_html_e( '实时预览', 'qiling' ); ?></span>
				</button>
			</div>
			<div class="qtc-card-head">
				<span class="qtc-source"><?php echo esc_html( $source_label ); ?></span>
				<span class="qtc-category"><?php echo esc_html( $category_label ); ?></span>
				<span class="qtc-usage-pill"><?php echo esc_html( $usage_count > 0 ? sprintf( __( '使用中 %s', 'qiling' ), number_format_i18n( $usage_count ) ) : __( '未使用', 'qiling' ) ); ?></span>
                <?php if ( $modules_count > 0 ) : ?>
                    <span class="qtc-modules-badge" style="margin-left: auto; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">🧩 <?php echo esc_html( sprintf( __( '%d 模块', 'qiling' ), $modules_count ) ); ?></span>
                <?php endif; ?>
            </div>
            <h2><?php echo esc_html( $label ); ?></h2>
            <p><?php echo esc_html( $description ); ?></p>
            <?php if ( ! empty( $badges ) ) : ?>
                <div class="qtc-badges">
                    <?php foreach ( $badges as $badge ) : ?>
                        <span><?php echo esc_html( (string) $badge ); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <dl class="qtc-meta">
                <div>
                    <dt><?php esc_html_e( '行业', 'qiling' ); ?></dt>
                    <dd><?php echo esc_html( $industry_label ); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e( '用途', 'qiling' ); ?></dt>
                    <dd><?php echo esc_html( $scenario ); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e( '使用中', 'qiling' ); ?></dt>
                    <dd><?php echo esc_html( number_format_i18n( $usage_count ) ); ?></dd>
                </div>
            </dl>
            <span class="qtc-template-file"><?php esc_html_e( '启灵官方页面模板', 'qiling' ); ?></span>
            <?php if ( ! empty( $usage_pages ) ) : ?>
                <details class="qtc-usage-details">
                    <summary><?php echo esc_html( sprintf( __( '查看 %s 个使用页面', 'qiling' ), number_format_i18n( $usage_count ) ) ); ?></summary>
                    <ul>
                        <?php foreach ( $usage_pages as $page ) : ?>
                            <?php
                            $page_title = isset( $page['title'] ) ? (string) $page['title'] : __( '(无标题)', 'qiling' );
                            $edit_url = isset( $page['edit_url'] ) ? (string) $page['edit_url'] : '';
                            $view_url = isset( $page['view_url'] ) ? (string) $page['view_url'] : '';
                            $status_label = isset( $page['status_label'] ) ? (string) $page['status_label'] : '';
                            ?>
                            <li>
                                <span class="qtc-usage-page-main">
                                    <?php if ( '' !== $edit_url ) : ?>
                                        <a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $page_title ); ?></a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $page_title ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( '' !== $status_label ) : ?>
                                        <em><?php echo esc_html( $status_label ); ?></em>
                                    <?php endif; ?>
                                </span>
                                <?php if ( '' !== $view_url ) : ?>
                                    <a class="qtc-usage-view-link" href="<?php echo esc_url( $view_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( '查看', 'qiling' ); ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
            <form method="post" class="qtc-create-form">
                <?php wp_nonce_field( 'developer_starter_template_center_create' ); ?>
                <input type="hidden" name="qiling_template_center_action" value="create_page" />
                <input type="hidden" name="template" value="<?php echo esc_attr( $template_file ); ?>" />
                <?php if ( '' !== $template_variant ) : ?>
                    <input type="hidden" name="template_variant" value="<?php echo esc_attr( $template_variant ); ?>" />
                <?php endif; ?>
                <label>
                    <span><?php esc_html_e( '页面标题', 'qiling' ); ?></span>
                    <input type="text" name="page_title" value="<?php echo esc_attr( $label ); ?>" />
                </label>
                <label>
                    <span><?php esc_html_e( '别名', 'qiling' ); ?></span>
                    <input type="text" name="page_slug" placeholder="<?php esc_attr_e( '可留空自动生成', 'qiling' ); ?>" />
                </label>
                <label>
                    <span><?php esc_html_e( '状态', 'qiling' ); ?></span>
                    <select name="page_status">
                        <option value="draft"><?php esc_html_e( '草稿', 'qiling' ); ?></option>
                        <option value="publish"><?php esc_html_e( '发布', 'qiling' ); ?></option>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e( '目标语言', 'qiling' ); ?></span>
                    <select name="template_target_language">
                        <option value=""><?php esc_html_e( '不自动本地化', 'qiling' ); ?></option>
                        <?php foreach ( $this->get_template_localization_language_choices() as $lang_code => $lang_label ) : ?>
                            <option value="<?php echo esc_attr( $lang_code ); ?>"><?php echo esc_html( $lang_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e( '目标市场', 'qiling' ); ?></span>
                    <input type="text" name="template_target_market" placeholder="<?php esc_attr_e( '例如：美国 / 日本 / 韩国 / 法国 / 德国 / 西班牙', 'qiling' ); ?>" />
                </label>
                <label class="qtc-checkbox">
                    <input type="checkbox" name="template_localization_enable" value="1" />
                    <span><?php esc_html_e( '创建后生成对应语言页面', 'qiling' ); ?></span>
                </label>
                <label class="qtc-checkbox" style="font-weight: 600; color: #1d4ed8; background: #eff6ff; padding: 6px 10px; border-radius: 6px; border: 1px dashed #93c5fd; margin: 4px 0 8px 0; display: flex; align-items: center; gap: 6px;">
                    <input type="checkbox" name="set_as_frontpage" value="1" checked />
                    <span><?php esc_html_e( '🏠 直接设为网站首页并立即发布 (推荐新手建站)', 'qiling' ); ?></span>
                </label>
                <label class="qtc-checkbox" style="font-weight: 500; color: #1d2327;">
                    <input type="checkbox" name="generate_demo_data" value="1" checked />
                    <span><?php esc_html_e( '📦 自动导入配套行业演示数据 (可在模版中心一键彻底清空)', 'qiling' ); ?></span>
                </label>
                <div class="qtc-card-form-actions">
                    <button type="button" class="button button-secondary qtc-card-preview-btn qtc-preview-trigger" data-preview-url="<?php echo esc_url( $preview_url ); ?>" data-title="<?php echo esc_attr( $label ); ?>" data-category="<?php echo esc_attr( $category_label ); ?>" data-template="<?php echo esc_attr( $template_file ); ?>">
                        <span class="dashicons dashicons-visibility"></span> <span><?php esc_html_e( '实时预览', 'qiling' ); ?></span>
                    </button>
                    <button type="submit" class="button button-primary"><?php esc_html_e( '创建页面', 'qiling' ); ?></button>
                </div>
            </form>
        </article>
        <?php
    }

    /**
     * 模板元数据定义。
     *
     * @param string              $template Template file.
     * @param string              $label Template label.
     * @param array<string,mixed> $config Fill config.
     * @return array<string,mixed>
     */
    private function get_template_meta( $template, $label, $config ) {
        $specific = $this->get_specific_template_meta();
        if ( isset( $specific[ $template ] ) ) {
            return $specific[ $template ];
        }

        $category = 'corporate';
        $industry = 'general';
        $scenario = __( '通用官网页面', 'qiling' );
        $badges = array( __( '模块化', 'qiling' ), __( '可装修', 'qiling' ) );

        if ( ! empty( $config['preset'] ) ) {
            $category = 'industry';
            $industry = $this->normalize_industry_key( $config['preset'] );
            $scenario = __( '行业预约/转化页', 'qiling' );
            $badges[] = __( '行业预设', 'qiling' );
        } elseif ( preg_match( '/blog|news|topic|resource|resources|latest/i', $template ) ) {
            $category = 'content';
            $scenario = __( '内容、栏目、杂志与资源页', 'qiling' );
        } elseif ( preg_match( '/software|product|saas|app|developer|ai|cybersecurity|data|open-source/i', $template ) ) {
            $category = 'product';
            $scenario = __( '产品、SaaS、技术平台官网', 'qiling' );
        } elseif ( preg_match( '/landing|ecommerce|interactive|course/i', $template ) ) {
            $category = 'conversion';
            $scenario = __( '落地页、活动页、销售转化页', 'qiling' );
        } elseif ( preg_match( '/resume|personal/i', $template ) ) {
            $category = 'personal';
            $scenario = __( '个人品牌与作品展示页', 'qiling' );
        }

        return array(
            'category'       => $category,
            'category_label' => $this->get_category_label( $category ),
            'industry'       => $industry,
            'industry_label' => $this->get_industry_label( $industry ),
            'scenario'       => $scenario,
            'description'    => sprintf(
                /* translators: %s: template label. */
                __( '%s 是启灵官方内置模板，创建后会自动填充对应模块，可继续用后台模块编辑或前台装修调整。', 'qiling' ),
                $label
            ),
            'badges'         => $badges,
            'order'          => $this->get_category_order( $category ) + 50,
        );
    }

    /**
     * 官方特定元数据定义。
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_specific_template_meta() {
        return array(
            'templates/template-home.php' => $this->meta( 'corporate', 'enterprise', __( '企业官网首页', 'qiling' ), __( '标准企业官网首页骨架，包含首屏、服务、优势、案例和转化入口。', 'qiling' ), array( __( '官网', 'qiling' ), __( '首页', 'qiling' ) ), 10 ),
            'templates/template-solutions.php' => $this->meta( 'corporate', 'enterprise', __( '解决方案页', 'qiling' ), __( '适合行业方案、服务方案和客户问题解决路径展示。', 'qiling' ), array( __( '服务', 'qiling' ), __( '方案', 'qiling' ) ), 20 ),
            'templates/template-products.php' => $this->meta( 'product', 'b2b', __( '产品中心', 'qiling' ), __( '用于产品列表、产品矩阵和产品卖点集中展示。', 'qiling' ), array( __( '产品', 'qiling' ), __( '列表', 'qiling' ) ), 30 ),
            'templates/template-cases.php' => $this->meta( 'corporate', 'enterprise', __( '案例展示', 'qiling' ), __( '适合沉淀客户案例、项目作品和行业实践。', 'qiling' ), array( __( '案例', 'qiling' ), __( '信任', 'qiling' ) ), 40 ),
            'templates/template-blog.php' => $this->meta( 'content', 'blog', __( '博客页面', 'qiling' ), __( '博客、专栏、知识库和内容营销入口。', 'qiling' ), array( __( '博客', 'qiling' ), __( '内容', 'qiling' ) ), 110 ),
            'templates/template-news.php' => $this->meta( 'content', 'media', __( '新闻中心', 'qiling' ), __( '适合资讯、公告、企业动态和行业新闻栏目。', 'qiling' ), array( __( '新闻', 'qiling' ), __( '杂志', 'qiling' ) ), 120 ),
            'templates/template-topic.php' => $this->meta( 'content', 'magazine', __( '专题页面', 'qiling' ), __( '用于专题策划、活动专题、聚合页和编辑型内容。', 'qiling' ), array( __( '专题', 'qiling' ), __( '聚合', 'qiling' ) ), 130 ),
            'templates/template-features-showcase.php' => $this->meta( 'product', 'technology', __( '功能展示页', 'qiling' ), __( '适合产品功能、能力矩阵和核心卖点集中呈现。', 'qiling' ), array( __( '功能', 'qiling' ), __( '产品', 'qiling' ) ), 140 ),
            'templates/template-resources.php' => $this->meta( 'content', 'media', __( '资源中心', 'qiling' ), __( '适合资料下载、内容资源、白皮书和知识库聚合。', 'qiling' ), array( __( '资源', 'qiling' ), __( '内容', 'qiling' ) ), 150 ),
            'templates/template-resource-search.php' => $this->meta( 'content', 'media', __( '资源搜索页', 'qiling' ), __( '适合资料库、文档中心和资源检索入口。', 'qiling' ), array( __( '搜索', 'qiling' ), __( '资源', 'qiling' ) ), 160 ),
            'templates/template-landing.php' => $this->meta( 'conversion', 'general', __( '落地页', 'qiling' ), __( '面向投放、获客、表单线索和单页转化。', 'qiling' ), array( __( '转化', 'qiling' ), __( '线索', 'qiling' ) ), 210 ),
            'templates/template-ecommerce-promo.php' => $this->meta( 'conversion', 'ecommerce', __( '电商大促会场页', 'qiling' ), __( '促销活动、爆款商品和专题会场页面。', 'qiling' ), array( __( '电商', 'qiling' ), __( '活动', 'qiling' ) ), 220 ),
            'templates/template-video-hero.php' => $this->meta( 'conversion', 'enterprise', __( '视频首屏页', 'qiling' ), __( '适合品牌大片、活动首屏和高视觉冲击的转化入口。', 'qiling' ), array( __( '视频', 'qiling' ), __( '首屏', 'qiling' ) ), 230 ),
            'templates/template-video-portal.php' => $this->meta( 'content', 'media', __( '影视门户首页', 'qiling' ), __( '适用于电影、电视剧、动漫、综艺等视频内容门户。', 'qiling' ), array( __( '娱乐', 'qiling' ), __( '影视', 'qiling' ), __( '门户', 'qiling' ) ), 125 ),
            'templates/template-video-ranking.php' => $this->meta( 'content', 'media', __( '影视排行榜', 'qiling' ), __( '影视站专用的深色全宽排行榜页面，适合总榜、电影榜、剧集榜与动漫榜。', 'qiling' ), array( __( '影视', 'qiling' ), __( '排行榜', 'qiling' ) ), 126 ),
            'templates/template-interactive-product-launch.php' => $this->meta( 'conversion', 'technology', __( '互动产品发布页', 'qiling' ), __( '适合新品发布、互动展示、发布会和产品上市活动。', 'qiling' ), array( __( '发布', 'qiling' ), __( '互动', 'qiling' ) ), 240 ),
            'templates/template-course-enrollment.php' => $this->meta( 'conversion', 'education', __( '课程报名页', 'qiling' ), __( '适合课程招生、训练营报名、公开课和教育转化。', 'qiling' ), array( __( '教育', 'qiling' ), __( '报名', 'qiling' ) ), 250 ),
            'templates/template-app-download-landing.php' => $this->meta( 'conversion', 'app', __( 'APP 下载页', 'qiling' ), __( '适合移动应用、工具 APP、客户端下载和预约体验。', 'qiling' ), array( __( 'APP', 'qiling' ), __( '下载', 'qiling' ) ), 260 ),
            'templates/template-ai-product-brand.php' => $this->meta( 'product', 'technology', __( 'AI 产品品牌官网', 'qiling' ), __( '适合 AI 产品、Agent 平台、智能工具和企业级 AI 服务。', 'qiling' ), array( __( 'AI', 'qiling' ), __( 'SaaS', 'qiling' ) ), 310 ),
            'templates/template-software-home.php' => $this->meta( 'product', 'software', __( '软件首页', 'qiling' ), __( '软件产品官网首页，突出能力、版本、案例和下载/咨询入口。', 'qiling' ), array( __( '软件', 'qiling' ), __( '产品', 'qiling' ) ), 320 ),
            'templates/template-saas-home.php' => $this->meta( 'product', 'saas', __( 'SaaS 行业官网', 'qiling' ), __( '适合订阅软件、协作工具、客户成功平台和企业级在线服务官网。', 'qiling' ), array( __( 'SaaS', 'qiling' ), __( '订阅', 'qiling' ) ), 325 ),
            'templates/template-hosting-saas-home.php' => $this->meta( 'product', 'cloud_storage', __( '云主机SaaS官网（一体式）', 'qiling' ), __( '适合云主机、虚拟主机、企业托管、CDN 加速和主机套餐转化的一体式官网。', 'qiling' ), array( __( '一体式视觉', 'qiling' ), __( 'Hosting', 'qiling' ), __( '云服务', 'qiling' ) ), 326 ),
            'templates/template-saas-pricing.php' => $this->meta( 'product', 'software', __( 'SaaS 价格对比页', 'qiling' ), __( '价格表、套餐对比、权益说明和购买咨询。', 'qiling' ), array( __( '价格', 'qiling' ), __( 'SaaS', 'qiling' ) ), 330 ),
            'templates/template-software-intro.php' => $this->meta( 'product', 'software', __( '软件介绍页', 'qiling' ), __( '适合软件能力说明、版本介绍、试用引导和咨询转化。', 'qiling' ), array( __( '软件', 'qiling' ), __( '介绍', 'qiling' ) ), 340 ),
            'templates/template-developer-platform.php' => $this->meta( 'product', 'software', __( '开发者平台官网', 'qiling' ), __( '适合 API 平台、开发者工具、文档入口和生态展示。', 'qiling' ), array( __( '开发者', 'qiling' ), __( '平台', 'qiling' ) ), 350 ),
            'templates/template-cybersecurity-brand.php' => $this->meta( 'product', 'technology', __( '网络安全品牌官网', 'qiling' ), __( '适合安全服务、风控平台、等保合规和企业安全方案。', 'qiling' ), array( __( '安全', 'qiling' ), __( '技术', 'qiling' ) ), 360 ),
            'templates/template-data-intelligence-bi.php' => $this->meta( 'product', 'software', __( '数据智能/BI 官网', 'qiling' ), __( '适合数据分析、BI 平台、经营看板和智能决策产品。', 'qiling' ), array( __( '数据', 'qiling' ), __( 'BI', 'qiling' ) ), 370 ),
            'templates/template-qiling-ai-writing-studio.php' => $this->meta( 'product', 'ai_writing', __( 'AI 写作工作台官网', 'qiling' ), __( '适合启灵 AI 写作、AI 助手、内容工具站和企业内容工作台官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( 'AI 写作', 'qiling' ) ), 375 ),
            'templates/template-qiling-ai-multilingual-seo.php' => $this->meta( 'product', 'multilingual_seo', __( 'AI 多语言出海官网', 'qiling' ), __( '适合启灵AI多语言、多语言 SEO、跨境内容站和外贸出海官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '多语言 SEO', 'qiling' ) ), 376 ),
            'templates/template-qiling-doc-ocr-converter.php' => $this->meta( 'product', 'document_ocr', __( '文档解析转换官网', 'qiling' ), __( '适合启灵文档解析、PDF 转 Office、文档转 PDF 和办公文件处理工具官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '文档解析', 'qiling' ) ), 377 ),
            'templates/template-qiling-image-studio.php' => $this->meta( 'product', 'ai_image', __( 'AI 图像处理官网', 'qiling' ), __( '适合启灵图像处理、启灵AI图像和创意图片工具站官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( 'AI 图像', 'qiling' ) ), 378 ),
            'templates/template-qiling-wallpaper-gallery.php' => $this->meta( 'content', 'photography', __( '图片素材壁纸站', 'qiling' ), __( '适合高清壁纸、摄影作品、设计配图和图片素材聚合站。', 'qiling' ), array( __( '图片素材', 'qiling' ), __( '高清壁纸', 'qiling' ) ), 155 ),
            'templates/template-qiling-cloud-storage-hosting.php' => $this->meta( 'product', 'cloud_storage', __( '云存储图床官网', 'qiling' ), __( '适合启灵云存储、启灵图床、对象存储迁移和图片直链工具官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '云存储', 'qiling' ) ), 379 ),
            'templates/template-qiling-cloud-canvas.php' => $this->meta( 'product', 'cloud_storage', __( '一体式官网', 'qiling' ), __( '适合云服务、SaaS、对象存储和技术产品官网，强调整页连续画布视觉。', 'qiling' ), array( __( '一体式视觉', 'qiling' ), __( '云服务', 'qiling' ) ), 380 ),
            'templates/template-tech-company-integrated.php' => $this->meta( 'product', 'technology', __( '科技公司官网（一体式）', 'qiling' ), __( '适合 AI、云计算、数据平台、研发服务和技术咨询公司官网，用连续画布组合首屏、能力、方案、流程、套餐和转化入口。', 'qiling' ), array( __( '一体式视觉', 'qiling' ), __( '科技公司', 'qiling' ) ), 386 ),
            'templates/template-qiling-security-ops.php' => $this->meta( 'product', 'security_ops', __( '安全防护运维官网', 'qiling' ), __( '适合启灵安全防护、启灵数据统计、启灵消息推送和 WordPress 安全运维服务官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '安全运维', 'qiling' ) ), 381 ),
            'templates/template-qiling-escrow-platform.php' => $this->meta( 'product', 'escrow_trading', __( '担保交易平台官网', 'qiling' ), __( '适合启灵担保交易、启灵积分商城和中介托管交易服务官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '担保交易', 'qiling' ) ), 382 ),
            'templates/template-qiling-freetask-platform.php' => $this->meta( 'product', 'freelance_task', __( '悬赏任务众包官网', 'qiling' ), __( '适合启灵悬赏任务、任务大厅、招标投标和积分托管众包平台官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '悬赏任务', 'qiling' ) ), 383 ),
            'templates/template-qiling-friends-matchmaking.php' => $this->meta( 'industry', 'matchmaking', __( '本地相亲婚恋官网', 'qiling' ), __( '适合启灵相亲、本地婚恋、资料审核、互赞匹配和受限私信服务官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '相亲婚恋', 'qiling' ) ), 384 ),
            'templates/template-qiling-bbs-support-community.php' => $this->meta( 'product', 'community_support', __( '社区工单官网', 'qiling' ), __( '适合启灵bbs、产品社区、用户问答、工单支持和客户成功入口。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '社区工单', 'qiling' ) ), 385 ),
            'templates/template-data-showcase.php' => $this->meta( 'product', 'technology', __( '数据展示页', 'qiling' ), __( '适合数据产品、指标大屏、可视化能力和案例展示。', 'qiling' ), array( __( '数据', 'qiling' ), __( '展示', 'qiling' ) ), 380 ),
            'templates/template-open-source-devtools.php' => $this->meta( 'product', 'software', __( '开源开发工具官网', 'qiling' ), __( '适合开源项目、开发工具、SDK 和技术社区入口。', 'qiling' ), array( __( '开源', 'qiling' ), __( '开发工具', 'qiling' ) ), 390 ),
            'templates/template-restaurant.php' => $this->meta( 'industry', 'restaurant', __( '餐饮官网', 'qiling' ), __( '餐厅、咖啡馆、品牌餐饮和预约菜单展示。', 'qiling' ), array( __( '餐饮', 'qiling' ), __( '预约', 'qiling' ) ), 410 ),
            'templates/template-dental-clinic.php' => $this->meta( 'industry', 'dental', __( '口腔诊所官网', 'qiling' ), __( '诊所介绍、医生团队、项目服务、预约转化。', 'qiling' ), array( __( '医疗', 'qiling' ), __( '预约', 'qiling' ) ), 420 ),
            'templates/template-law-firm.php' => $this->meta( 'industry', 'law', __( '律所官网', 'qiling' ), __( '律所品牌、律师团队、业务领域和咨询入口。', 'qiling' ), array( __( '法律', 'qiling' ), __( '咨询', 'qiling' ) ), 430 ),
            'templates/template-renovation-construction.php' => $this->meta( 'industry', 'renovation', __( '装修/工装官网', 'qiling' ), __( '装修公司、工装工程、案例展示和报价咨询。', 'qiling' ), array( __( '装修', 'qiling' ), __( '案例', 'qiling' ) ), 440 ),
            'templates/template-wedding-photography.php' => $this->meta( 'industry', 'wedding', __( '婚礼摄影官网', 'qiling' ), __( '婚礼摄影、婚礼策划、样片展示和档期咨询。', 'qiling' ), array( __( '婚礼', 'qiling' ), __( '摄影', 'qiling' ) ), 450 ),
            'templates/template-gym-fitness.php' => $this->meta( 'industry', 'fitness', __( '健身房官网', 'qiling' ), __( '健身房、私教工作室、团课课程和体验预约。', 'qiling' ), array( __( '健身', 'qiling' ), __( '预约', 'qiling' ) ), 460 ),
            'templates/template-yoga-studio.php' => $this->meta( 'industry', 'wellness', __( '瑜伽馆官网', 'qiling' ), __( '瑜伽、普拉提、身心疗愈课程和体验预约。', 'qiling' ), array( __( '康养', 'qiling' ), __( '课程', 'qiling' ) ), 470 ),
            'templates/template-beauty-salon.php' => $this->meta( 'industry', 'beauty', __( '美业门店官网', 'qiling' ), __( '美容、美发、美甲、美睫门店服务和预约转化。', 'qiling' ), array( __( '美业', 'qiling' ), __( '门店', 'qiling' ) ), 480 ),
            'templates/template-pet.php' => $this->meta( 'industry', 'pet', __( '宠物服务官网', 'qiling' ), __( '宠物医院、宠物店、寄养洗护和服务预约。', 'qiling' ), array( __( '宠物', 'qiling' ), __( '服务', 'qiling' ) ), 490 ),
            'templates/template-homestay.php' => $this->meta( 'industry', 'hospitality', __( '民宿官网', 'qiling' ), __( '民宿、客栈、精品酒店和度假空间展示预订。', 'qiling' ), array( __( '民宿', 'qiling' ), __( '预订', 'qiling' ) ), 500 ),
            'templates/template-travel.php' => $this->meta( 'industry', 'travel', __( '旅游线路官网', 'qiling' ), __( '旅行社、目的地线路、私家团和旅游咨询。', 'qiling' ), array( __( '旅游', 'qiling' ), __( '线路', 'qiling' ) ), 510 ),
            'templates/template-medical-beauty.php' => $this->meta( 'industry', 'medical_beauty', __( '医美机构官网', 'qiling' ), __( '医美机构、皮肤管理、项目介绍和到店咨询。', 'qiling' ), array( __( '医美', 'qiling' ), __( '咨询', 'qiling' ) ), 520 ),
            'templates/template-auto-service.php' => $this->meta( 'industry', 'automotive', __( '汽车服务官网', 'qiling' ), __( '汽修保养、汽车美容、门店服务和预约到店。', 'qiling' ), array( __( '汽车', 'qiling' ), __( '门店', 'qiling' ) ), 530 ),
            'templates/template-wellness-center.php' => $this->meta( 'industry', 'wellness', __( '康养中心官网', 'qiling' ), __( '康养中心、理疗馆、健康管理和体验预约。', 'qiling' ), array( __( '康养', 'qiling' ), __( '健康', 'qiling' ) ), 540 ),
            'templates/template-health-supplements.php' => $this->meta( 'industry', 'health_supplements', __( '健康保健用品官网', 'qiling' ), __( '营养补充、日常保健用品、合规信息和产品咨询。', 'qiling' ), array( __( '健康用品', 'qiling' ), __( '合规展示', 'qiling' ) ), 548 ),
            'templates/template-intimate-wellness.php' => $this->meta( 'industry', 'intimate_wellness', __( '情趣用品商城', 'qiling' ), __( '成人情趣用品、材质说明、隐私配送和清洁养护。', 'qiling' ), array( __( '18+ 成人适用', 'qiling' ), __( '隐私配送', 'qiling' ) ), 549 ),
            'templates/template-fashion-brand.php' => $this->meta( 'industry', 'fashion', __( '服装品牌官网', 'qiling' ), __( '服装品牌、当季系列、搭配画册、精选单品和品牌服务。', 'qiling' ), array( __( '服装时尚', 'qiling' ), __( '搭配画册', 'qiling' ) ), 550 ),
            'templates/template-chain-store-official.php' => $this->meta( 'industry', 'franchise', __( '连锁门店官网', 'qiling' ), __( '连锁品牌、门店矩阵、加盟政策和咨询留资。', 'qiling' ), array( __( '连锁', 'qiling' ), __( '加盟', 'qiling' ) ), 550 ),
            'templates/template-marketing-pr-agency.php' => $this->meta( 'industry', 'b2b', __( '营销公关机构官网', 'qiling' ), __( '营销策划、公关传播、品牌服务和案例展示。', 'qiling' ), array( __( '营销', 'qiling' ), __( 'B2B', 'qiling' ) ), 560 ),
            'templates/template-manufacturing-factory.php' => $this->meta( 'industry', 'manufacturing', __( '制造业工厂官网', 'qiling' ), __( '制造工厂、设备厂商、零部件供应商和工业品牌官网。', 'qiling' ), array( __( '制造业', 'qiling' ), __( '询盘', 'qiling' ) ), 570 ),
            'templates/template-foreign-trade-b2b.php' => $this->meta( 'industry', 'b2b', __( '外贸 B2B 出海官网', 'qiling' ), __( '外贸企业、出口工厂、跨境 B2B 和全球供应商官网。', 'qiling' ), array( __( '外贸', 'qiling' ), __( 'B2B', 'qiling' ) ), 580 ),
            'templates/template-finance-consulting.php' => $this->meta( 'industry', 'finance', __( '金融财税服务官网', 'qiling' ), __( '财税公司、金融服务、保险顾问和企业合规咨询官网。', 'qiling' ), array( __( '金融', 'qiling' ), __( '财税', 'qiling' ) ), 590 ),
            'templates/template-accounting-tax-service.php' => $this->meta( 'industry', 'accounting_tax', __( '代理记账/会计税务官网', 'qiling' ), __( '适合代理记账、纳税申报、工商注册、税务筹划和财税顾问服务官网。', 'qiling' ), array( __( '财税服务', 'qiling' ), __( '代理记账', 'qiling' ) ), 591 ),
            'templates/template-intellectual-property-service.php' => $this->meta( 'industry', 'intellectual_property', __( '知识产权/商标专利官网', 'qiling' ), __( '适合知识产权代理、商标注册、专利申请、版权登记和品牌维权服务官网。', 'qiling' ), array( __( '知识产权', 'qiling' ), __( '商标专利', 'qiling' ) ), 592 ),
            'templates/template-study-abroad-immigration.php' => $this->meta( 'industry', 'study_abroad', __( '留学移民服务官网', 'qiling' ), __( '适合留学申请、移民咨询、国际教育、签证服务和海外规划机构官网。', 'qiling' ), array( __( '留学移民', 'qiling' ), __( '预约评估', 'qiling' ) ), 593 ),
            'templates/template-early-childhood-education.php' => $this->meta( 'industry', 'early_childhood', __( '幼儿园/早教中心官网', 'qiling' ), __( '适合幼儿园、托育中心、早教中心、亲子成长馆和幼小衔接机构官网。', 'qiling' ), array( __( '幼儿早教', 'qiling' ), __( '预约参观', 'qiling' ) ), 594 ),
            'templates/template-vocational-training-school.php' => $this->meta( 'industry', 'vocational_training', __( '职业培训学校官网', 'qiling' ), __( '适合职业培训学校、技能实训、考证培训、就业辅导和企业内训机构官网。', 'qiling' ), array( __( '职业培训', 'qiling' ), __( '招生转化', 'qiling' ) ), 595 ),
            'templates/template-psychological-counseling.php' => $this->meta( 'industry', 'psychological_counseling', __( '心理咨询中心官网', 'qiling' ), __( '适合心理咨询中心、心理工作室、家庭咨询、青少年支持和职场心理服务机构官网。', 'qiling' ), array( __( '心理咨询', 'qiling' ), __( '预约初谈', 'qiling' ) ), 596 ),
            'templates/template-senior-care-center.php' => $this->meta( 'industry', 'senior_care', __( '养老院/康养机构官网', 'qiling' ), __( '适合养老院、护理院、康养中心、长者公寓和医养结合机构官网。', 'qiling' ), array( __( '养老康养', 'qiling' ), __( '预约参观', 'qiling' ) ), 597 ),
            'templates/template-postpartum-care-center.php' => $this->meta( 'industry', 'postpartum_care', __( '月子中心/产康中心官网', 'qiling' ), __( '适合月子中心、产康中心、母婴护理机构和产后修复门店官网。', 'qiling' ), array( __( '月子产康', 'qiling' ), __( '母婴照护', 'qiling' ) ), 598 ),
            'templates/template-architecture-design-studio.php' => $this->meta( 'industry', 'architecture_design', __( '建筑设计事务所官网', 'qiling' ), __( '适合建筑设计事务所、规划设计公司、城市更新团队、商业空间设计和建筑顾问机构官网。', 'qiling' ), array( __( '建筑设计', 'qiling' ), __( '方案作品', 'qiling' ) ), 599 ),
            'templates/template-interior-soft-decoration.php' => $this->meta( 'industry', 'interior_design', __( '室内设计/软装设计官网', 'qiling' ), __( '适合室内设计工作室、软装设计公司、全案设计品牌、私宅设计和商业空间设计官网。', 'qiling' ), array( __( '室内软装', 'qiling' ), __( '搭配画册', 'qiling' ) ), 601 ),
            'templates/template-landscape-garden-design.php' => $this->meta( 'industry', 'landscape_garden', __( '园林景观/庭院设计官网', 'qiling' ), __( '适合园林景观公司、庭院设计工作室、别墅花园营造、商业景观提升和屋顶花园项目官网。', 'qiling' ), array( __( '园林景观', 'qiling' ), __( '庭院设计', 'qiling' ) ), 602 ),
            'templates/template-appliance-repair-service.php' => $this->meta( 'industry', 'appliance_repair', __( '家电维修/水电维修官网', 'qiling' ), __( '适合家电维修、水电快修、家居安装、管道疏通和本地快修服务品牌官网。', 'qiling' ), array( __( '维修安装', 'qiling' ), __( '上门预约', 'qiling' ) ), 603 ),
            'templates/template-franchise-investment.php' => $this->meta( 'industry', 'franchise', __( '加盟招商官网', 'qiling' ), __( '适合连锁品牌、招商加盟项目、区域代理合作、城市合伙人招募和联营招商官网。', 'qiling' ), array( __( '招商加盟', 'qiling' ), __( '合作模式', 'qiling' ) ), 604 ),
            'templates/template-mcn-live-commerce.php' => $this->meta( 'industry', 'mcn_live_commerce', __( 'MCN/直播电商机构官网', 'qiling' ), __( '适合 MCN 机构、直播电商代运营、达人孵化、品牌内容营销和短视频投放团队官网。', 'qiling' ), array( __( 'MCN', 'qiling' ), __( '直播电商', 'qiling' ) ), 605 ),
            'templates/template-conference-event-service.php' => $this->meta( 'industry', 'conference_event_service', __( '会议会展/活动承办官网', 'qiling' ), __( '适合会议会展公司、活动策划执行、品牌发布会、路演巡展、展台搭建和企业年会服务商官网。', 'qiling' ), array( __( '会议会展', 'qiling' ), __( '活动承办', 'qiling' ) ), 606 ),
            'templates/template-real-estate-service.php' => $this->meta( 'industry', 'real_estate', __( '房地产服务官网', 'qiling' ), __( '楼盘项目、房产中介、商业空间和园区招商官网。', 'qiling' ), array( __( '房产', 'qiling' ), __( '看房', 'qiling' ) ), 600 ),
            'templates/template-local-service-official.php' => $this->meta( 'industry', 'local_service', __( '本地生活服务官网', 'qiling' ), __( '家政清洁、维修安装、到家服务和社区服务品牌官网。', 'qiling' ), array( __( '本地服务', 'qiling' ), __( '预约', 'qiling' ) ), 610 ),
            'templates/template-qiling-recycling-official.php' => $this->meta( 'industry', 'recycling', __( '回收服务官网', 'qiling' ), __( '数码、家电、奢品、企业闲置和旧物回收服务品牌官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '回收估价', 'qiling' ) ), 615 ),
            'templates/template-qiling-housekeeping-official.php' => $this->meta( 'industry', 'housekeeping', __( '家政服务官网', 'qiling' ), __( '家政清洁、收纳整理、家电清洗、保姆月嫂和本地上门服务官网。', 'qiling' ), array( __( '启灵生态', 'qiling' ), __( '家政预约', 'qiling' ) ), 616 ),
            'templates/template-healthcare-clinic.php' => $this->meta( 'industry', 'healthcare', __( '医疗健康机构官网', 'qiling' ), __( '综合门诊、专科机构、体检中心和健康管理机构官网。', 'qiling' ), array( __( '医疗健康', 'qiling' ), __( '预约', 'qiling' ) ), 620 ),
            'templates/template-logistics-supply-chain.php' => $this->meta( 'industry', 'logistics', __( '物流供应链官网', 'qiling' ), __( '物流公司、仓配服务、冷链运输和供应链服务官网。', 'qiling' ), array( __( '物流', 'qiling' ), __( '仓配', 'qiling' ) ), 630 ),
            'templates/template-recruitment-hr-service.php' => $this->meta( 'industry', 'human_resources', __( '人力资源招聘官网', 'qiling' ), __( '招聘公司、猎头顾问、灵活用工和人才咨询服务官网。', 'qiling' ), array( __( '人力资源', 'qiling' ), __( '招聘', 'qiling' ) ), 640 ),
            'templates/template-nonprofit-organization.php' => $this->meta( 'industry', 'nonprofit', __( '公益组织官网', 'qiling' ), __( '公益组织、基金会、社会服务机构和公益项目官网。', 'qiling' ), array( __( '公益', 'qiling' ), __( '透明公开', 'qiling' ) ), 650 ),
            'templates/template-government-public-service.php' => $this->meta( 'industry', 'government', __( '政务公共机构官网', 'qiling' ), __( '公共机构、园区服务中心、协会机构和政务服务类官网。', 'qiling' ), array( __( '公共服务', 'qiling' ), __( '政务', 'qiling' ) ), 660 ),
            'templates/template-agriculture-food.php' => $this->meta( 'industry', 'agriculture_food', __( '农业食品品牌官网', 'qiling' ), __( '农产品品牌、食品企业、农场基地和区域公用品牌官网。', 'qiling' ), array( __( '农业食品', 'qiling' ), __( '溯源', 'qiling' ) ), 670 ),
            'templates/template-energy-environment.php' => $this->meta( 'industry', 'energy_environment', __( '能源环保服务官网', 'qiling' ), __( '新能源、节能改造、环保工程和碳管理服务官网。', 'qiling' ), array( __( '能源环保', 'qiling' ), __( '方案', 'qiling' ) ), 680 ),
            'templates/template-event-exhibition.php' => $this->meta( 'industry', 'event', __( '会展活动平台招商页', 'qiling' ), __( '会展、峰会、发布会、行业大会和活动平台招商页面。', 'qiling' ), array( __( '会展', 'qiling' ), __( '报名招商', 'qiling' ) ), 690 ),
            'templates/template-industrial-park.php' => $this->meta( 'industry', 'industrial_park', __( '产业园区招商官网', 'qiling' ), __( '产业园区、科技园、孵化器和产业运营机构官网。', 'qiling' ), array( __( '园区', 'qiling' ), __( '招商', 'qiling' ) ), 700 ),
            'templates/template-property-management.php' => $this->meta( 'industry', 'property_management', __( '物业社区服务官网', 'qiling' ), __( '物业公司、社区服务、园区运营和商业物业官网。', 'qiling' ), array( __( '物业', 'qiling' ), __( '报修', 'qiling' ) ), 710 ),
            'templates/template-semiconductor-electronics.php' => $this->meta( 'industry', 'semiconductor_electronics', __( '半导体/电子元器件官网', 'qiling' ), __( '适合半导体厂商、电子元器件代理商、工业电子供应商和 IC 方案商官网。', 'qiling' ), array( __( '半导体', 'qiling' ), __( 'BOM 询盘', 'qiling' ) ), 720 ),
            'templates/template-industrial-automation-robotics.php' => $this->meta( 'industry', 'industrial_automation_robotics', __( '工业机器人/自动化官网', 'qiling' ), __( '适合工业机器人厂商、自动化设备商、系统集成商和智能工厂服务商官网。', 'qiling' ), array( __( '工业机器人', 'qiling' ), __( '自动化产线', 'qiling' ) ), 721 ),
            'templates/template-medical-device.php' => $this->meta( 'industry', 'medical_device', __( '医疗器械官网', 'qiling' ), __( '适合医疗器械生产企业、医用设备品牌、院内设备供应商和渠道经销官网。', 'qiling' ), array( __( '医疗器械', 'qiling' ), __( '合规资质', 'qiling' ) ), 722 ),
            'templates/template-lab-instrument.php' => $this->meta( 'industry', 'lab_instrument', __( '实验室设备/科研仪器官网', 'qiling' ), __( '适合实验室设备厂商、科研仪器品牌、检测设备供应商和高校/科研院所采购官网。', 'qiling' ), array( __( '实验室设备', 'qiling' ), __( '科研仪器', 'qiling' ) ), 723 ),
            'templates/template-solar-storage-equipment.php' => $this->meta( 'industry', 'solar_storage_equipment', __( '光伏储能设备官网', 'qiling' ), __( '适合光伏组件厂商、储能系统集成商、逆变器品牌和工商业能源项目服务商官网。', 'qiling' ), array( __( '光伏储能', 'qiling' ), __( '工商业项目', 'qiling' ) ), 724 ),
            'templates/template-water-treatment-environmental.php' => $this->meta( 'industry', 'water_treatment_environmental', __( '水处理/环保工程官网', 'qiling' ), __( '适合水处理设备商、环保工程公司、污水处理运营商和工业废水治理团队官网。', 'qiling' ), array( __( '水处理', 'qiling' ), __( '环保工程', 'qiling' ) ), 725 ),
            'templates/template-cross-border-ecommerce-service.php' => $this->meta( 'industry', 'cross_border_ecommerce_service', __( '跨境电商服务官网', 'qiling' ), __( '适合跨境电商代运营、平台入驻、独立站服务、海外广告投放和品牌出海咨询团队官网。', 'qiling' ), array( __( '跨境电商', 'qiling' ), __( '品牌出海', 'qiling' ) ), 726 ),
            'templates/template-overseas-warehouse-supply-chain.php' => $this->meta( 'industry', 'overseas_warehouse_supply_chain', __( '海外仓/跨境供应链官网', 'qiling' ), __( '适合海外仓服务商、跨境物流公司、FBA 头程团队、跨境 3PL 和退换货处理中心官网。', 'qiling' ), array( __( '海外仓', 'qiling' ), __( '跨境供应链', 'qiling' ) ), 727 ),
            'templates/template-enterprise-software-integrator.php' => $this->meta( 'industry', 'enterprise_software_integrator', __( '企业软件/SI 集成商官网', 'qiling' ), __( '适合企业软件公司、SI 系统集成商、数字化转型服务商、ERP/CRM/OA 实施团队和数据中台服务商官网。', 'qiling' ), array( __( '企业软件', 'qiling' ), __( '系统集成', 'qiling' ) ), 728 ),
            'templates/template-ai-agent-enterprise.php' => $this->meta( 'industry', 'ai_agent_enterprise', __( '企业 AI Agent 官网', 'qiling' ), __( '适合企业 AI Agent 平台、智能助手、知识库问答、流程自动化和私有化大模型应用官网。', 'qiling' ), array( __( 'AI Agent', 'qiling' ), __( '企业智能体', 'qiling' ) ), 729 ),
            'templates/template-personal-ip-home.php' => $this->meta( 'personal', 'personal', __( '个人 IP 主页', 'qiling' ), __( '适合创始人、顾问、讲师、博主和自由职业个人品牌。', 'qiling' ), array( __( '个人品牌', 'qiling' ), __( '作品', 'qiling' ) ), 510 ),
            'templates/template-brand-dtc-store.php' => $this->meta( 'conversion', 'ecommerce', __( '大品牌出海与DTC旗舰商城', 'qiling' ), __( '专为高端硬件、消费电子、大品牌与出海独立站打造的旗舰商城首页。融合品牌巨幕、机皇焦点展台、全系多分类商品矩阵、工艺深度展示与全球信任背书。', 'qiling' ), array( __( 'DTC', 'qiling' ), __( '大品牌', 'qiling' ), __( '出海商城', 'qiling' ) ), 120 ),
            'templates/template-foreign-trade-store.php' => $this->meta( 'conversion', 'trade', __( '外贸企业与跨境独立站官网', 'qiling' ), __( '专为外贸制造企业、工贸一体出海及跨境独立站打造的高端官网商城模板。融合国际商务巨幕、出海合规资质、热销出口商品多分类矩阵、制造产能硬实力与在线询盘留言通道。', 'qiling' ), array( __( 'B2B & DTC', 'qiling' ), __( '外贸出海', 'qiling' ), __( '全球商贸', 'qiling' ) ), 125 ),
        );
    }

    /**
     * 构建单项元数据结构。
     *
     * @param string            $category Category.
     * @param string            $industry Industry.
     * @param string            $scenario Scenario.
     * @param string            $description Description.
     * @param array<int,string> $badges Badges.
     * @param int               $order Sort order.
     * @return array<string,mixed>
     */
    private function meta( $category, $industry, $scenario, $description, $badges, $order ) {
        $industry = $this->normalize_industry_key( $industry );

        return array(
            'category'       => $category,
            'category_label' => $this->get_category_label( $category ),
            'industry'       => $industry,
            'industry_label' => $this->get_industry_label( $industry ),
            'scenario'       => $scenario,
            'description'    => $description,
            'badges'         => $badges,
            'order'          => $order,
        );
    }

    /**
     * 获取分类选项列表。
     *
     * @param array<int,array<string,mixed>> $catalog Catalog.
     * @return array<string,string>
     */
    private function get_category_choices( $catalog ) {
        $choices = array( 'all' => __( '全部分组', 'qiling' ) );
        foreach ( $catalog as $entry ) {
            if ( empty( $entry['category'] ) ) {
                continue;
            }
            $choices[ sanitize_key( (string) $entry['category'] ) ] = isset( $entry['category_label'] )
                ? (string) $entry['category_label']
                : $this->get_category_label( (string) $entry['category'] );
        }
        return $choices;
    }

    /**
     * 获取行业选项列表。
     *
     * @param array<int,array<string,mixed>> $catalog Catalog.
     * @return array<string,string>
     */
    private function get_industry_choices( $catalog ) {
        $choices = $this->get_standard_industry_choices();
        foreach ( $catalog as $entry ) {
            if ( empty( $entry['industry'] ) ) {
                continue;
            }
            $industry_key = $this->normalize_industry_key( $entry['industry'] );
            $choices[ $industry_key ] = isset( $entry['industry_label'] )
                ? (string) $entry['industry_label']
                : $this->get_industry_label( $industry_key );
        }
        asort( $choices );
        return array( 'all' => __( '全部行业', 'qiling' ) ) + $choices;
    }

    /**
     * 模板目录统计信息。
     *
     * @param array<int,array<string,mixed>> $catalog Catalog.
     * @param array<string,mixed>            $industry_coverage Industry coverage.
     * @return array<string,int>
     */
    private function get_catalog_stats( $catalog, $industry_coverage = array() ) {
        $coverage_total = isset( $industry_coverage['total'] ) ? absint( $industry_coverage['total'] ) : 0;
        $coverage_ready = isset( $industry_coverage['covered'] ) ? absint( $industry_coverage['covered'] ) : 0;
        $stats = array(
            'total'               => count( $catalog ),
            'industry'            => 0,
            'non_industry'        => 0,
            'created_pages'       => 0,
            'standard_industries' => $coverage_total,
            'covered_industries'  => $coverage_ready,
            'missing_industries'  => max( 0, $coverage_total - $coverage_ready ),
        );

        foreach ( $catalog as $entry ) {
            if ( isset( $entry['category'] ) && 'industry' === $entry['category'] ) {
                $stats['industry']++;
            }
            $stats['created_pages'] += isset( $entry['created_pages'] ) ? absint( $entry['created_pages'] ) : 0;
        }

        $stats['non_industry'] = max( 0, $stats['total'] - $stats['industry'] );

        return $stats;
    }

    /**
     * 根据官方目录构建行业覆盖率数据。
     *
     * @param array<int,array<string,mixed>> $catalog Catalog.
     * @return array<string,mixed>
     */
    private function get_industry_coverage_data( $catalog ) {
        $standards = $this->get_standard_industry_definitions();
        $items = array();

        foreach ( $standards as $key => $config ) {
            $key = $this->normalize_industry_key( $key );
            if ( 'general' === $key ) {
                continue;
            }

            $items[ $key ] = array(
                'key'            => $key,
                'label'          => isset( $config['label'] ) && is_scalar( $config['label'] ) ? (string) $config['label'] : $this->get_industry_label( $key ),
                'group'          => isset( $config['group'] ) && is_scalar( $config['group'] ) ? sanitize_key( (string) $config['group'] ) : 'other',
                'template_count' => 0,
                'created_pages'  => 0,
                'templates'      => array(),
            );
        }

        foreach ( $catalog as $entry ) {
            if ( empty( $entry['industry'] ) ) {
                continue;
            }

            $key = $this->normalize_industry_key( $entry['industry'] );
            if ( 'general' === $key ) {
                continue;
            }

            if ( ! isset( $items[ $key ] ) ) {
                $items[ $key ] = array(
                    'key'            => $key,
                    'label'          => $this->get_industry_label( $key ),
                    'group'          => 'other',
                    'template_count' => 0,
                    'created_pages'  => 0,
                    'templates'      => array(),
                );
            }

            $items[ $key ]['template_count']++;
            $items[ $key ]['created_pages'] += isset( $entry['created_pages'] ) ? absint( $entry['created_pages'] ) : 0;
            if ( count( $items[ $key ]['templates'] ) < 3 ) {
                $items[ $key ]['templates'][] = isset( $entry['label'] ) ? (string) $entry['label'] : $this->get_industry_label( $key );
            }
        }

        $covered = 0;
        foreach ( $items as $item ) {
            if ( ! empty( $item['template_count'] ) ) {
                $covered++;
            }
        }

        return array(
            'items'   => array_values( $items ),
            'total'   => count( $items ),
            'covered' => $covered,
            'missing' => max( 0, count( $items ) - $covered ),
            'percent' => count( $items ) > 0 ? (int) round( ( $covered / count( $items ) ) * 100 ) : 0,
        );
    }

    /**
     * 渲染标准行业覆盖度矩阵。
     *
     * @param array<string,mixed>  $coverage Coverage data.
     * @param array<string,string> $filters Current filters.
     * @return void
     */
    private function render_industry_coverage_panel( $coverage, $filters ) {
        $items = isset( $coverage['items'] ) && is_array( $coverage['items'] ) ? $coverage['items'] : array();
        if ( empty( $items ) ) {
            return;
        }

        $covered = isset( $coverage['covered'] ) ? absint( $coverage['covered'] ) : 0;
        $total = isset( $coverage['total'] ) ? absint( $coverage['total'] ) : count( $items );
        $missing = isset( $coverage['missing'] ) ? absint( $coverage['missing'] ) : max( 0, $total - $covered );
        $percent = isset( $coverage['percent'] ) ? max( 0, min( 100, absint( $coverage['percent'] ) ) ) : 0;
        $active_industry = isset( $filters['industry'] ) ? $this->normalize_industry_key( $filters['industry'] ) : 'all';
        ?>
        <section class="qtc-coverage-panel" aria-labelledby="qtc-coverage-title">
            <div class="qtc-section-heading">
                <div>
                    <h2 id="qtc-coverage-title"><?php esc_html_e( '行业模板覆盖', 'qiling' ); ?></h2>
                    <p><?php esc_html_e( '按行业查看官方模板覆盖情况。已有模板的行业可直接点击筛选，暂无模板的行业会清晰标注。', 'qiling' ); ?></p>
                </div>
                <div class="qtc-coverage-meter" aria-label="<?php esc_attr_e( '行业覆盖进度', 'qiling' ); ?>">
                    <strong><?php echo esc_html( sprintf( '%s%%', number_format_i18n( $percent ) ) ); ?></strong>
                    <span><?php echo esc_html( sprintf( __( '已覆盖 %1$s / %2$s，暂无模板 %3$s', 'qiling' ), number_format_i18n( $covered ), number_format_i18n( $total ), number_format_i18n( $missing ) ) ); ?></span>
                    <i><b style="width: <?php echo esc_attr( (string) $percent ); ?>%;"></b></i>
                </div>
            </div>
            <div class="qtc-coverage-grid">
                <?php foreach ( $items as $item ) : ?>
                    <?php
                    $key = isset( $item['key'] ) ? $this->normalize_industry_key( $item['key'] ) : '';
                    if ( '' === $key || 'general' === $key ) {
                        continue;
                    }

                    $count = isset( $item['template_count'] ) ? absint( $item['template_count'] ) : 0;
                    $created_pages = isset( $item['created_pages'] ) ? absint( $item['created_pages'] ) : 0;
                    $label = isset( $item['label'] ) ? (string) $item['label'] : $this->get_industry_label( $key );
                    $templates = isset( $item['templates'] ) && is_array( $item['templates'] ) ? array_filter( array_map( 'strval', $item['templates'] ) ) : array();
                    $url = add_query_arg(
                        array(
                            'page'              => $this->page_slug,
                            'template_industry' => $key,
                        ),
                        admin_url( 'admin.php' )
                    );
                    $class_names = array( 'qtc-coverage-item' );
                    $class_names[] = $count > 0 ? 'has-templates' : 'is-missing';
                    if ( $active_industry === $key ) {
                        $class_names[] = 'is-active';
                    }
                    ?>
                    <a class="<?php echo esc_attr( implode( ' ', $class_names ) ); ?>" href="<?php echo esc_url( $url ); ?>">
                        <span class="qtc-coverage-main">
                            <strong><?php echo esc_html( $label ); ?></strong>
                            <em><?php echo esc_html( $count > 0 ? sprintf( __( '%s 个模板', 'qiling' ), number_format_i18n( $count ) ) : __( '暂无模板', 'qiling' ) ); ?></em>
                        </span>
                        <?php if ( ! empty( $templates ) ) : ?>
                            <span class="qtc-coverage-sample"><?php echo esc_html( implode( ' / ', $templates ) ); ?></span>
                        <?php else : ?>
                            <span class="qtc-coverage-sample"><?php esc_html_e( '已建行业标准，尚未提供官方模板', 'qiling' ); ?></span>
                        <?php endif; ?>
                        <?php if ( $created_pages > 0 ) : ?>
                            <span class="qtc-coverage-usage"><?php echo esc_html( sprintf( __( '使用中 %s', 'qiling' ), number_format_i18n( $created_pages ) ) ); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /**
     * 获取已应用官方模板的页面列表。
     *
     * @param array<int,string> $templates Templates.
     * @return array<string,array{count:int,pages:array<int,array<string,mixed>>}>
     */
    private function get_template_usage_data( $templates ) {
        $templates = array_map(
            function( $template ) {
                $template = (string) $template;
                return function_exists( 'developer_starter_normalize_page_template_slug' )
                    ? developer_starter_normalize_page_template_slug( $template )
                    : str_replace( '\\', '/', $template );
            },
            $templates
        );
        $templates = array_values( array_unique( array_filter( array_map( 'strval', $templates ) ) ) );
        if ( empty( $templates ) ) {
            return array();
        }

        $posts = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'orderby'        => 'modified',
                'order'          => 'DESC',
                'meta_query'     => array(
                    array(
                        'key'     => '_wp_page_template',
                        'value'   => $templates,
                        'compare' => 'IN',
                    ),
                ),
            )
        );

        $usage = array();
        foreach ( $posts as $post_id ) {
            $post_id = absint( $post_id );
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
            if ( '' === $template ) {
                continue;
            }

            if ( ! isset( $usage[ $template ] ) ) {
                $usage[ $template ] = array(
                    'count' => 0,
                    'pages' => array(),
                );
            }

            $status = (string) get_post_status( $post_id );
            $status_object = get_post_status_object( $status );
            $status_label = $status_object && ! empty( $status_object->label ) ? (string) $status_object->label : $status;
            $title = get_the_title( $post_id );
            $edit_url = get_edit_post_link( $post_id, '' );
            $view_url = get_permalink( $post_id );

            $usage[ $template ]['count']++;
            $usage[ $template ]['pages'][] = array(
                'id'           => $post_id,
                'title'        => '' !== $title ? $title : __( '(无标题)', 'qiling' ),
                'status'       => $status,
                'status_label' => $status_label,
                'edit_url'     => $edit_url ? $edit_url : '',
                'view_url'     => $view_url ? $view_url : '',
            );
        }

        return $usage;
    }

    /**
     * 检查模板文件是否存在。
     *
     * @param string               $template Template file.
     * @param array<string,string> $page_templates WP theme page templates.
     * @return bool
     */
    private function template_exists( $template, $page_templates ) {
        if ( in_array( $template, array_values( $page_templates ), true ) ) {
            return true;
        }

        return file_exists( trailingslashit( get_template_directory() ) . ltrim( $template, '/' ) );
    }

    /**
     * 获取模板显示名称。
     *
     * @param string               $template Template file.
     * @param array<string,string> $page_templates WP theme page templates.
     * @return string
     */
    private function get_template_label( $template, $page_templates ) {
        $label = array_search( $template, $page_templates, true );
        if ( false !== $label ) {
            return (string) $label;
        }

        $header = $this->read_template_name_header( $template );
        if ( '' !== $header ) {
            return $header;
        }

        return basename( $template, '.php' );
    }

    /**
     * 从主题文件中读取 Template Name 文件头信息。
     *
     * @param string $template Template file.
     * @return string
     */
    private function read_template_name_header( $template ) {
        $path = trailingslashit( get_template_directory() ) . ltrim( $template, '/' );
        if ( ! is_readable( $path ) ) {
            return '';
        }

        $contents = file_get_contents( $path, false, null, 0, 4096 );
        if ( ! is_string( $contents ) || ! preg_match( '/Template Name:\s*(.+)/i', $contents, $matches ) ) {
            return '';
        }

        return sanitize_text_field( trim( (string) $matches[1] ) );
    }

    /**
     * 获取分类显示名称。
     *
     * @param string $category Category.
     * @return string
     */
    private function get_category_label( $category ) {
        $labels = array(
            'corporate'  => __( '官网通用', 'qiling' ),
            'content'    => __( '博客/杂志/内容', 'qiling' ),
            'resource'   => __( '数字资源', 'qiling' ),
            'product'    => __( '产品/SaaS/技术', 'qiling' ),
            'conversion' => __( '落地页/转化', 'qiling' ),
            'industry'   => __( '行业官网', 'qiling' ),
            'personal'   => __( '个人品牌', 'qiling' ),
        );

        return isset( $labels[ $category ] ) ? $labels[ $category ] : __( '其他', 'qiling' );
    }

    /**
     * 获取分类排序权重。
     *
     * @param string $category Category.
     * @return int
     */
    private function get_category_order( $category ) {
        $orders = array(
            'corporate'  => 100,
            'content'    => 200,
            'resource'   => 260,
            'product'    => 300,
            'conversion' => 400,
            'industry'   => 500,
            'personal'   => 600,
        );

        return isset( $orders[ $category ] ) ? $orders[ $category ] : 900;
    }

    /**
     * 标准行业类型定义。
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_standard_industry_definitions() {
        if ( class_exists( '\Developer_Starter\Modules\Module_Standards' ) ) {
            $standards = \Developer_Starter\Modules\Module_Standards::get_industry_standards();
            if ( is_array( $standards ) && ! empty( $standards ) ) {
                return $standards;
            }
        }

        return array(
            'general'       => array( 'label' => __( '通用', 'qiling' ), 'group' => 'general' ),
            'enterprise'    => array( 'label' => __( '企业官网', 'qiling' ), 'group' => 'business' ),
            'technology'    => array( 'label' => __( '科技', 'qiling' ), 'group' => 'technology' ),
            'software'      => array( 'label' => __( '软件', 'qiling' ), 'group' => 'technology' ),
            'ai_writing'    => array( 'label' => __( 'AI 写作', 'qiling' ), 'group' => 'technology' ),
            'multilingual_seo' => array( 'label' => __( '多语言 SEO', 'qiling' ), 'group' => 'technology' ),
            'document_ocr'  => array( 'label' => __( '文档 OCR', 'qiling' ), 'group' => 'technology' ),
            'ai_image'      => array( 'label' => __( 'AI 图像', 'qiling' ), 'group' => 'technology' ),
            'cloud_storage' => array( 'label' => __( '云存储图床', 'qiling' ), 'group' => 'technology' ),
            'security_ops'  => array( 'label' => __( '安全运维', 'qiling' ), 'group' => 'technology' ),
            'escrow_trading' => array( 'label' => __( '担保交易', 'qiling' ), 'group' => 'technology' ),
            'freelance_task' => array( 'label' => __( '悬赏众包', 'qiling' ), 'group' => 'technology' ),
            'matchmaking'   => array( 'label' => __( '相亲婚恋', 'qiling' ), 'group' => 'local' ),
            'community_support' => array( 'label' => __( '社区工单', 'qiling' ), 'group' => 'technology' ),
            'education'     => array( 'label' => __( '教育培训', 'qiling' ), 'group' => 'service' ),
            'study_abroad'   => array( 'label' => __( '留学移民', 'qiling' ), 'group' => 'education' ),
            'early_childhood' => array( 'label' => __( '幼儿早教', 'qiling' ), 'group' => 'education' ),
            'vocational_training' => array( 'label' => __( '职业培训', 'qiling' ), 'group' => 'education' ),
            'psychological_counseling' => array( 'label' => __( '心理咨询', 'qiling' ), 'group' => 'health' ),
            'senior_care'   => array( 'label' => __( '养老康养', 'qiling' ), 'group' => 'health' ),
            'postpartum_care' => array( 'label' => __( '月子产康', 'qiling' ), 'group' => 'health' ),
            'architecture_design' => array( 'label' => __( '建筑设计', 'qiling' ), 'group' => 'property' ),
            'interior_design' => array( 'label' => __( '室内软装', 'qiling' ), 'group' => 'property' ),
            'landscape_garden' => array( 'label' => __( '园林景观', 'qiling' ), 'group' => 'property' ),
            'appliance_repair' => array( 'label' => __( '维修安装', 'qiling' ), 'group' => 'local' ),
            'franchise'      => array( 'label' => __( '招商加盟', 'qiling' ), 'group' => 'business' ),
            'mcn_live_commerce' => array( 'label' => __( 'MCN/直播电商', 'qiling' ), 'group' => 'commerce' ),
            'conference_event_service' => array( 'label' => __( '会议会展/活动承办', 'qiling' ), 'group' => 'business' ),
            'semiconductor_electronics' => array( 'label' => __( '半导体/电子元器件', 'qiling' ), 'group' => 'business' ),
            'industrial_automation_robotics' => array( 'label' => __( '工业机器人/自动化', 'qiling' ), 'group' => 'business' ),
            'medical_device' => array( 'label' => __( '医疗器械', 'qiling' ), 'group' => 'health' ),
            'lab_instrument' => array( 'label' => __( '实验室设备/科研仪器', 'qiling' ), 'group' => 'business' ),
            'solar_storage_equipment' => array( 'label' => __( '光伏储能设备', 'qiling' ), 'group' => 'business' ),
            'water_treatment_environmental' => array( 'label' => __( '水处理/环保工程', 'qiling' ), 'group' => 'business' ),
            'cross_border_ecommerce_service' => array( 'label' => __( '跨境电商服务', 'qiling' ), 'group' => 'commerce' ),
            'overseas_warehouse_supply_chain' => array( 'label' => __( '海外仓/跨境供应链', 'qiling' ), 'group' => 'business' ),
            'enterprise_software_integrator' => array( 'label' => __( '企业软件/SI 集成商', 'qiling' ), 'group' => 'technology' ),
            'ai_agent_enterprise' => array( 'label' => __( '企业 AI Agent', 'qiling' ), 'group' => 'technology' ),
            'accounting_tax' => array( 'label' => __( '代理记账/会计税务', 'qiling' ), 'group' => 'professional' ),
            'intellectual_property' => array( 'label' => __( '知识产权', 'qiling' ), 'group' => 'professional' ),
            'recycling'     => array( 'label' => __( '回收服务', 'qiling' ), 'group' => 'local' ),
            'housekeeping'  => array( 'label' => __( '家政服务', 'qiling' ), 'group' => 'local' ),
            'local_service' => array( 'label' => __( '本地服务', 'qiling' ), 'group' => 'local' ),
        );
    }

    /**
     * 用于筛选的标准行业选项列表。
     *
     * @return array<string,string>
     */
    private function get_standard_industry_choices() {
        $choices = array();
        foreach ( $this->get_standard_industry_definitions() as $key => $config ) {
            $key = $this->normalize_industry_key( $key );
            $choices[ $key ] = isset( $config['label'] ) && is_scalar( $config['label'] )
                ? (string) $config['label']
                : $this->get_industry_label( $key );
        }

        return $choices;
    }

    /**
     * 获取行业显示名称。
     *
     * @param string $industry Industry.
     * @return string
     */
    private function get_industry_label( $industry ) {
        $industry = $this->normalize_industry_key( $industry );
        if ( class_exists( '\Developer_Starter\Modules\Module_Standards' ) ) {
            return \Developer_Starter\Modules\Module_Standards::get_industry_label( $industry );
        }

        return $this->humanize_key( $industry );
    }

    /**
     * 通过统一标准规范化行业键名。
     *
     * @param mixed $industry Industry key.
     * @return string
     */
    private function normalize_industry_key( $industry ) {
        $key = is_scalar( $industry ) ? sanitize_key( str_replace( '-', '_', (string) $industry ) ) : '';
        if ( '' === $key ) {
            return 'general';
        }

        if ( 'all' === $key ) {
            return 'all';
        }

        if ( class_exists( '\Developer_Starter\Modules\Module_Standards' ) ) {
            return \Developer_Starter\Modules\Module_Standards::normalize_industry_key( $key );
        }

        return $key;
    }

    /**
     * Humanize a key.
     *
     * @param string $key Key.
     * @return string
     */
    private function humanize_key( $key ) {
        $key = str_replace( array( '-', '_' ), ' ', (string) $key );
        return ucwords( $key );
    }

    /**
     * 安全读取标量请求参数。
     *
     * @param array<string,mixed> $source Request source.
     * @param string              $key Request key.
     * @param string              $default Default value.
     * @return string
     */
    private function get_request_value( $source, $key, $default = '' ) {
        if ( ! is_array( $source ) || ! isset( $source[ $key ] ) || is_array( $source[ $key ] ) ) {
            return $default;
        }

        return wp_unslash( (string) $source[ $key ] );
    }

    /**
     * 渲染局部作用域 CSS 样式。
     *
     * @return void
     */
    private function render_styles() {
        ?>
        <style>
            .wrap.qiling-template-center {
                max-width: 1600px;
                margin: 10px 20px 20px 2px;
                box-sizing: border-box;
            }
            .qiling-template-center *,
            .qiling-template-center *::before,
            .qiling-template-center *::after {
                box-sizing: border-box;
            }
            .qiling-template-center .description {
                max-width: 980px;
                line-height: 1.7;
            }
            .qtc-language-notice {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                margin: 16px 0 20px;
                padding: 14px 16px;
                border: 1px solid #fde68a;
                border-left: 4px solid #f59e0b;
                border-radius: 8px;
                background: #fffbeb;
                color: #92400e;
            }
            .qtc-language-notice__icon {
                font-size: 20px;
                line-height: 1.2;
                flex-shrink: 0;
            }
            .qtc-language-notice__body strong {
                display: block;
                margin-bottom: 4px;
                color: #78350f;
                font-size: 14px;
                font-weight: 700;
            }
            .qtc-language-notice__body p {
                margin: 0;
                color: #92400e;
                font-size: 13px;
                line-height: 1.6;
            }
            .qtc-summary-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
                margin: 18px 0;
            }
            .qtc-summary-card {
                padding: 16px 18px;
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            }
            .qtc-summary-card strong {
                display: block;
                font-size: 26px;
                line-height: 1.2;
                color: #1d2327;
            }
            .qtc-summary-card span {
                color: #646970;
            }
            /* 页面头部与操作区 */
            .qtc-page-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 16px;
                margin: 16px 0 20px;
                padding-bottom: 16px;
                border-bottom: 1px solid #e2e8f0;
            }
            .qtc-header-left {
                flex: 1 1 auto;
                min-width: 0;
            }
            .qtc-page-title {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
                margin: 0 0 6px !important;
                font-size: 24px !important;
                font-weight: 700 !important;
                color: #0f172a;
            }
            .qtc-title-badge {
                display: inline-flex;
                align-items: center;
                padding: 3px 10px;
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                color: #1d4ed8;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 600;
            }
            .qtc-page-desc {
                margin: 0;
                color: #64748b;
                font-size: 13px;
                line-height: 1.6;
            }
            .qtc-header-actions {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-shrink: 0;
                margin-left: auto;
            }
            .qtc-demo-badge-wrap {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 0 10px;
                height: 38px;
                background: #fffbeb;
                border: 1px solid #fde68a;
                border-radius: 8px;
                box-sizing: border-box;
            }
            .qtc-demo-pill {
                font-size: 12px;
                font-weight: 600;
                color: #b45309;
            }
            .qtc-clear-demo-form {
                margin: 0;
            }
            .qtc-clear-demo-btn {
                background: #fef2f2 !important;
                border-color: #fca5a5 !important;
                color: #dc2626 !important;
                font-size: 11px !important;
                padding: 1px 8px !important;
                line-height: 1.6 !important;
                height: auto !important;
                border-radius: 4px !important;
                cursor: pointer;
            }
            .wp-core-ui .qtc-toggle-dashboard-btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 8px !important;
                height: 38px !important;
                line-height: 1 !important;
                padding: 0 16px !important;
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                color: #1e293b !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                vertical-align: middle !important;
                box-sizing: border-box !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
            }
            .wp-core-ui .qtc-toggle-dashboard-btn:hover {
                background: #f8fafc !important;
                border-color: #94a3b8 !important;
                color: #0f172a !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
            }
            .wp-core-ui .qtc-toggle-dashboard-btn .dashicons {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 18px !important;
                height: 18px !important;
                font-size: 18px !important;
                line-height: 18px !important;
                margin: 0 !important;
                padding: 0 !important;
                vertical-align: middle !important;
                color: #2563eb !important;
            }
            .wp-core-ui .qtc-toggle-dashboard-btn .qtc-toggle-text {
                display: inline-block !important;
                line-height: 18px !important;
                height: 18px !important;
                vertical-align: middle !important;
                color: #1e293b !important;
            }
            .wp-core-ui .qtc-toggle-dashboard-btn .qtc-toggle-icon {
                width: 14px !important;
                height: 14px !important;
                font-size: 14px !important;
                line-height: 14px !important;
                margin-left: 2px !important;
                vertical-align: middle !important;
                color: #64748b !important;
                transition: transform 0.2s ease !important;
            }
            .wp-core-ui .qtc-toggle-dashboard-btn.is-expanded {
                background: #eff6ff !important;
                border-color: #93c5fd !important;
                color: #1d4ed8 !important;
            }
            .wp-core-ui .qtc-toggle-dashboard-btn.is-expanded .qtc-toggle-icon {
                transform: rotate(180deg) !important;
            }

            /* 折叠收纳仪表盘 */
            .qtc-admin-dashboard {
                margin-bottom: 24px;
                padding: 16px 20px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                animation: qtcFadeIn 0.25s ease;
            }
            @keyframes qtcFadeIn {
                from { opacity: 0; transform: translateY(-6px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* 场景一级导航 Tabs */
            .qtc-scenario-nav {
                margin: 0 0 16px;
            }
            .qtc-scenario-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            .qtc-tab-btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                height: 36px !important;
                line-height: 1 !important;
                padding: 0 14px !important;
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 20px !important;
                color: #334155 !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                cursor: pointer !important;
                outline: none !important;
                box-sizing: border-box !important;
                vertical-align: middle !important;
                transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
            }
            .qtc-tab-btn:hover {
                background: #f8fafc !important;
                border-color: #cbd5e1 !important;
                color: #0f172a !important;
                transform: translateY(-1px) !important;
            }
            .qtc-tab-btn .dashicons {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 16px !important;
                width: 16px !important;
                height: 16px !important;
                line-height: 16px !important;
                vertical-align: middle !important;
                color: #64748b !important;
                margin: 0 !important;
                padding: 0 !important;
                transition: color 0.18s ease !important;
            }
            .qtc-tab-btn:hover .dashicons {
                color: #2563eb !important;
            }
            .qtc-tab-btn .qtc-tab-label {
                display: inline-block !important;
                line-height: 16px !important;
                height: 16px !important;
                vertical-align: middle !important;
            }
            .qtc-tab-btn .qtc-tab-count {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 18px !important;
                height: 18px !important;
                padding: 0 6px !important;
                border-radius: 10px !important;
                background: #f1f5f9 !important;
                color: #64748b !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                line-height: 1 !important;
                vertical-align: middle !important;
            }
            .qtc-tab-btn.is-active {
                background: #2563eb !important;
                border-color: #2563eb !important;
                color: #ffffff !important;
                box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3) !important;
                transform: translateY(-1px) !important;
            }
            .qtc-tab-btn.is-active .dashicons {
                color: #ffffff !important;
            }
            .qtc-tab-btn.is-active .qtc-tab-count {
                background: rgba(255, 255, 255, 0.25) !important;
                color: #ffffff !important;
            }

            /* 即时搜索与过滤工具栏 */
            .qtc-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
                padding: 12px 16px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            }
            .qtc-toolbar-search {
                position: relative;
                flex: 1 1 360px;
                max-width: 580px;
            }
            .qtc-search-icon {
                position: absolute;
                left: 10px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 18px;
                pointer-events: none;
            }
            .qtc-instant-search-input {
                width: 100% !important;
                height: 38px !important;
                padding: 0 32px 0 34px !important;
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                font-size: 13px !important;
                color: #0f172a !important;
                transition: all 0.18s ease !important;
            }
            .qtc-instant-search-input:focus {
                background: #ffffff !important;
                border-color: #2563eb !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
                outline: none !important;
            }
            .qtc-clear-search-btn {
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
                border: none;
                background: #cbd5e1;
                color: #475569;
                border-radius: 50%;
                font-size: 14px;
                line-height: 1;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .qtc-clear-search-btn:hover {
                background: #94a3b8;
                color: #ffffff;
            }
            .qtc-toolbar-filters {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 14px;
            }
            .qtc-status-filter-pills {
                display: inline-flex;
                padding: 3px;
                background: #f1f5f9;
                border-radius: 8px;
                gap: 2px;
            }
            .qtc-pill-btn {
                border: none;
                background: transparent;
                padding: 5px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .qtc-pill-btn:hover {
                color: #0f172a;
            }
            .qtc-pill-btn.is-active {
                background: #ffffff;
                color: #0f172a;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            }
            .qtc-toolbar-counter {
                color: #64748b;
                font-size: 13px;
            }
            .qtc-toolbar-counter strong {
                color: #2563eb;
                font-weight: 700;
            }

            /* 热门场景快捷标签 */
            .qtc-hot-tags-bar {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
                margin: 12px 0 18px;
                padding: 0 4px;
            }
            .qtc-hot-tags-label {
                font-size: 12px;
                color: #64748b;
                font-weight: 600;
            }
            .qtc-tag-pill {
                border: 1px dashed #cbd5e1;
                background: #ffffff;
                color: #475569;
                font-size: 12px;
                padding: 3px 10px;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .qtc-tag-pill:hover {
                border-color: #2563eb;
                background: #eff6ff;
                color: #1d4ed8;
            }

            /* 空状态提示 */
            .qtc-no-results {
                padding: 60px 20px;
                text-align: center;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                margin: 20px 0;
            }
            .qtc-no-results-icon {
                font-size: 40px;
                margin-bottom: 12px;
            }
            .qtc-no-results h3 {
                margin: 0 0 8px;
                font-size: 18px;
                color: #0f172a;
            }
            .qtc-no-results p {
                margin: 0 0 18px;
                color: #64748b;
                font-size: 14px;
            }

            .qtc-create-form label {
                display: grid;
                gap: 5px;
                min-width: 0;
                color: #50575e;
                font-size: 12px;
            }
            .qtc-create-form input,
            .qtc-create-form select {
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }
            .qtc-admin-note {
                margin: 16px 0;
                padding: 12px 14px;
                background: #f0f6fc;
                border-left: 4px solid #2271b1;
            }
            .qtc-coverage-panel {
                margin: 18px 0 20px;
                padding: 18px;
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
            }
            .qtc-section-heading {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 18px;
                margin-bottom: 14px;
            }
            .qtc-section-heading h2 {
                margin: 0 0 6px;
                font-size: 18px;
                line-height: 1.35;
            }
            .qtc-section-heading p {
                margin: 0;
                max-width: 760px;
                color: #50575e;
                line-height: 1.65;
            }
            .qtc-coverage-meter {
                flex: 0 0 260px;
                display: grid;
                gap: 6px;
                min-width: 0;
                padding: 12px;
                background: #f6f7f7;
                border-radius: 8px;
            }
            .qtc-coverage-meter strong {
                color: #1d2327;
                font-size: 24px;
                line-height: 1.1;
            }
            .qtc-coverage-meter span {
                color: #50575e;
                font-size: 12px;
                line-height: 1.4;
            }
            .qtc-coverage-meter i {
                display: block;
                width: 100%;
                height: 8px;
                overflow: hidden;
                background: #dcdcde;
                border-radius: 999px;
            }
            .qtc-coverage-meter b {
                display: block;
                height: 100%;
                background: #008a20;
                border-radius: inherit;
            }
            .qtc-coverage-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
                gap: 10px;
            }
            .qtc-coverage-item {
                display: grid;
                gap: 7px;
                min-width: 0;
                min-height: 102px;
                padding: 12px;
                color: inherit;
                text-decoration: none;
                background: #fbfbfc;
                border: 1px solid #dcdcde;
                border-radius: 8px;
            }
            .qtc-coverage-item:hover,
            .qtc-coverage-item:focus {
                color: inherit;
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
                outline: none;
            }
            .qtc-coverage-item.is-active {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
            }
            .qtc-coverage-item.is-missing {
                background: #fff8e5;
                border-color: #f0c36d;
            }
            .qtc-coverage-main {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 10px;
                min-width: 0;
            }
            .qtc-coverage-main strong {
                min-width: 0;
                color: #1d2327;
                line-height: 1.35;
                overflow-wrap: anywhere;
            }
            .qtc-coverage-main em {
                flex: 0 0 auto;
                padding: 3px 6px;
                color: #50575e;
                font-size: 12px;
                font-style: normal;
                line-height: 1.2;
                background: #f0f0f1;
                border-radius: 6px;
            }
            .qtc-coverage-item.has-templates .qtc-coverage-main em {
                color: #008a20;
                background: #edfaef;
            }
            .qtc-coverage-item.is-missing .qtc-coverage-main em {
                color: #8a4b00;
                background: #fff3cd;
            }
            .qtc-coverage-sample,
            .qtc-coverage-usage {
                min-width: 0;
                color: #646970;
                font-size: 12px;
                line-height: 1.45;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .qtc-coverage-usage {
                color: #008a20;
                font-weight: 700;
            }
            .qtc-template-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
                gap: 20px;
                align-items: stretch;
                margin-top: 20px;
            }
			.qtc-template-card {
                position: relative;
				display: flex;
				flex-direction: column;
				gap: 12px;
                min-width: 0;
				min-height: 100%;
				padding: 18px;
				background: #fff;
				border: 1px solid #dcdcde;
				border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
			}
            .qtc-card-thumb {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
				overflow: hidden;
				margin: -18px -18px 4px;
				aspect-ratio: 16 / 9;
				background: #f6f7f7;
				border-radius: 8px 8px 0 0;
			}
            .qtc-card-thumb-placeholder {
                background:
                    linear-gradient(135deg, rgba(34, 113, 177, 0.12), rgba(0, 163, 143, 0.1)),
                    #f6f7f7;
                color: #135e96;
                font-weight: 700;
            }
            .qtc-card-thumb-placeholder span {
                max-width: calc(100% - 36px);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
			.qtc-card-thumb img {
				display: block;
				width: 100%;
				height: 100%;
				object-fit: cover;
			}
            .qtc-thumb-image {
                opacity: 1;
                transition: opacity 180ms ease;
            }
            .qtc-thumb-image[data-qtc-src] {
                opacity: 0;
            }
            .qtc-card-thumb.is-deferred::after {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(110deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0)),
                    linear-gradient(135deg, rgba(34, 113, 177, 0.12), rgba(0, 163, 143, 0.1));
                background-size: 220% 100%, 100% 100%;
                animation: qtcThumbShimmer 1.3s linear infinite;
                pointer-events: none;
            }
            .qtc-card-thumb.is-deferred.is-loaded::after,
            .qtc-card-thumb.is-deferred.is-error::after {
                display: none;
            }
            @keyframes qtcThumbShimmer {
                from {
                    background-position: 220% 0, 0 0;
                }
                to {
                    background-position: -220% 0, 0 0;
                }
            }
			.qtc-card-head,
			.qtc-badges {
				display: flex;
				flex-wrap: wrap;
				gap: 8px;
                min-width: 0;
            }
            .qtc-source,
            .qtc-category,
            .qtc-usage-pill,
            .qtc-badges span {
                display: inline-flex;
                align-items: center;
                max-width: 100%;
                min-width: 0;
                min-height: 24px;
                padding: 0 8px;
                border-radius: 6px;
                background: #f6f7f7;
                color: #50575e;
                font-size: 12px;
                line-height: 1.2;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .qtc-source {
                background: #e7f5ff;
                color: #135e96;
            }
            .qtc-template-card.is-used {
                border-color: #9ec2e6;
            }
            .qtc-template-card.is-used .qtc-usage-pill {
                background: #edfaef;
                color: #008a20;
                font-weight: 700;
            }
            .qtc-template-card.is-unused .qtc-usage-pill {
                background: #f6f7f7;
                color: #646970;
            }
            .qtc-template-card h2 {
                margin: 0;
                font-size: 18px;
                line-height: 1.35;
                overflow-wrap: anywhere;
            }
            .qtc-template-card p {
                margin: 0;
                color: #50575e;
                line-height: 1.65;
                display: -webkit-box;
                overflow: hidden;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
            }
            .qtc-meta {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
                margin: 0;
            }
            .qtc-meta div {
                min-width: 0;
                min-height: 62px;
                padding: 8px;
                background: #f6f7f7;
                border-radius: 6px;
                overflow: hidden;
            }
            .qtc-meta dt {
                color: #646970;
                font-size: 12px;
            }
            .qtc-meta dd {
                margin: 2px 0 0;
                color: #1d2327;
                font-weight: 600;
                line-height: 1.35;
                overflow-wrap: anywhere;
            }
            .qtc-template-file {
                display: block;
                max-width: 100%;
                max-height: 42px;
                padding: 8px 10px;
                color: #50575e;
                font-size: 12px;
                line-height: 1.45;
                white-space: normal;
                overflow: hidden;
                overflow-wrap: anywhere;
                background: #f6f7f7;
                border-radius: 6px;
            }
            .qtc-usage-details {
                border: 1px solid #dcdcde;
                border-radius: 6px;
                background: #fff;
                overflow: hidden;
            }
            .qtc-usage-details summary {
                display: flex;
                align-items: center;
                min-height: 34px;
                padding: 8px 10px;
                color: #135e96;
                cursor: pointer;
                font-weight: 700;
            }
            .qtc-usage-details ul {
                max-height: 172px;
                overflow: auto;
                margin: 0;
                padding: 0;
                border-top: 1px solid #e2e4e7;
            }
            .qtc-usage-details li {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                min-width: 0;
                margin: 0;
                padding: 9px 10px;
            }
            .qtc-usage-details li + li {
                border-top: 1px solid #f0f0f1;
            }
            .qtc-usage-page-main {
                display: grid;
                flex: 1 1 auto;
                gap: 3px;
                min-width: 0;
            }
            .qtc-usage-page-main a,
            .qtc-usage-page-main span {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .qtc-usage-page-main em {
                color: #646970;
                font-size: 12px;
                font-style: normal;
            }
            .qtc-usage-view-link {
                flex: 0 0 auto;
                font-size: 12px;
            }
            .qtc-create-form {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                gap: 10px;
                margin-top: auto;
                padding: 14px;
                margin-right: -18px;
                margin-bottom: -18px;
                margin-left: -18px;
                background: #fbfbfc;
                border-top: 1px solid #e2e4e7;
            }
            .qtc-create-form label:first-child {
                grid-column: 1 / -1;
            }
            .qtc-create-form .qtc-checkbox {
                grid-column: 1 / -1;
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border: 1px solid #dcdcde;
                border-radius: 6px;
                background: #fff;
            }
            .qtc-create-form .qtc-checkbox input {
                width: auto;
                margin: 0;
            }
            .qtc-create-form .button {
                grid-column: 1 / -1;
                width: 100%;
                min-height: 32px;
                align-self: end;
                justify-self: stretch;
            }
            @media (max-width: 782px) {
                .wrap.qiling-template-center {
                    margin-right: 12px;
                }
                .qtc-template-grid {
                    grid-template-columns: 1fr;
                }
                .qtc-section-heading {
                    display: grid;
                }
                .qtc-coverage-meter {
                    width: 100%;
                    flex-basis: auto;
                }
                .qtc-coverage-grid {
                    grid-template-columns: 1fr;
                }
                .qtc-meta,
                .qtc-create-form {
                    grid-template-columns: 1fr;
                }
                .qtc-filter-bar .qtc-search {
                    min-width: 0;
                }
                .qtc-filter-bar select,
                .qtc-filter-bar input[type="search"],
                .qtc-create-form input,
                .qtc-create-form select {
                    width: 100%;
                    min-width: 0;
                }
            }

            /* 模板中心卡片缩略图悬浮预览遮罩 */
            .qtc-card-thumb {
                position: relative;
                overflow: hidden;
            }
            .qtc-thumb-preview-overlay {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.76);
                backdrop-filter: blur(4px);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                font-size: 14px;
                font-weight: 700;
                opacity: 0;
                border: none;
                cursor: pointer;
                transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                z-index: 3;
            }
            .qtc-card-thumb:hover .qtc-thumb-preview-overlay {
                opacity: 1;
            }
            .wp-core-ui .qtc-card-form-actions {
                display: flex !important;
                flex-direction: row !important;
                gap: 8px !important;
                align-items: center !important;
                margin-top: 12px !important;
            }
            .wp-core-ui .qtc-card-form-actions .button {
                flex: 1 1 50% !important;
                min-width: 0 !important;
                display: inline-flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                height: 36px !important;
                line-height: 1 !important;
                padding: 0 8px !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                white-space: nowrap !important;
                vertical-align: middle !important;
                box-sizing: border-box !important;
            }
            .wp-core-ui .qtc-card-form-actions .button .dashicons {
                display: inline-block !important;
                width: 16px !important;
                height: 16px !important;
                font-size: 16px !important;
                line-height: 16px !important;
                vertical-align: middle !important;
                margin: 0 !important;
                padding: 0 !important;
                flex-shrink: 0 !important;
            }
            .wp-core-ui .qtc-card-form-actions .button span {
                display: inline-block !important;
                vertical-align: middle !important;
                line-height: 1 !important;
                white-space: nowrap !important;
            }
            .qtc-card-preview-btn {
                color: #1e40af !important;
                border-color: #bfdbfe !important;
                background: #eff6ff !important;
            }
            .qtc-card-preview-btn:hover {
                background: #dbeafe !important;
                border-color: #93c5fd !important;
                color: #1d4ed8 !important;
            }

            /* 全屏虚拟预览模态框 */
            .qtc-preview-modal {
                position: fixed;
                inset: 0;
                z-index: 100050;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .qtc-preview-backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.85);
                backdrop-filter: blur(12px);
            }
            .qtc-preview-dialog {
                position: relative;
                z-index: 1;
                width: calc(100vw - 48px);
                height: calc(100vh - 48px);
                max-width: 1720px;
                background: #0f172a;
                border-radius: 16px;
                box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.1);
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            .qtc-preview-header {
                height: 60px;
                background: #1e293b;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 20px;
                flex-shrink: 0;
            }
            .qtc-preview-header-meta {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .qtc-preview-badge {
                background: rgba(37, 99, 235, 0.25);
                color: #60a5fa;
                border: 1px solid rgba(96, 165, 250, 0.3);
                padding: 3px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
            }
            .qtc-preview-title {
                margin: 0;
                font-size: 16px;
                font-weight: 700;
                color: #ffffff;
            }
            .qtc-preview-category {
                font-size: 12px;
                color: #94a3b8;
                background: rgba(255, 255, 255, 0.06);
                padding: 2px 8px;
                border-radius: 4px;
            }
            .qtc-preview-device-switcher {
                display: flex;
                align-items: center;
                background: rgba(15, 23, 42, 0.6);
                padding: 3px;
                border-radius: 8px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                gap: 4px;
            }
            .qtc-device-btn {
                background: transparent;
                border: none;
                color: #94a3b8;
                padding: 5px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: all 0.15s ease;
            }
            .qtc-device-btn:hover {
                color: #ffffff;
                background: rgba(255, 255, 255, 0.06);
            }
            .qtc-device-btn.is-active {
                background: #2563eb;
                color: #ffffff;
                box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
            }
            .qtc-device-btn .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
            .qtc-preview-header-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .qtc-preview-close-btn {
                background: rgba(255, 255, 255, 0.08);
                border: none;
                color: #94a3b8;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 16px;
                transition: all 0.15s ease;
            }
            .qtc-preview-close-btn:hover {
                background: rgba(239, 68, 68, 0.2);
                color: #f87171;
            }
            .qtc-preview-body {
                flex: 1;
                position: relative;
                background: #090d16;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                padding: 16px;
            }
            .qtc-preview-loading {
                position: absolute;
                inset: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background: #090d16;
                z-index: 2;
                color: #94a3b8;
                gap: 16px;
            }
            .qtc-preview-spinner {
                width: 44px;
                height: 44px;
                border: 3px solid rgba(37, 99, 235, 0.2);
                border-top-color: #38bdf8;
                border-radius: 50%;
                animation: qtcSpin 0.8s linear infinite;
            }
            @keyframes qtcSpin {
                to { transform: rotate(360deg); }
            }
            .qtc-preview-viewport {
                height: 100%;
                background: #ffffff;
                transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-radius 0.3s ease, box-shadow 0.3s ease;
                overflow: hidden;
            }
            .qtc-preview-viewport.is-desktop {
                width: 100%;
                border-radius: 8px;
            }
            .qtc-preview-viewport.is-tablet {
                width: 768px;
                border-radius: 16px;
                box-shadow: 0 0 0 10px #1e293b, 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            }
            .qtc-preview-viewport.is-mobile {
                width: 375px;
                border-radius: 28px;
                box-shadow: 0 0 0 10px #1e293b, 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            }
            .qtc-preview-viewport iframe {
                width: 100%;
                height: 100%;
                border: none;
                display: block;
            }
        </style>
        <?php
    }

    /**
     * 渲染局部作用域交互脚本。
     *
     * @return void
     */
    /**
     * 渲染免存库模板实时预览模态框。
     *
     * @return void
     */
    private function render_preview_modal() {
        ?>
        <div id="qtc-preview-modal" class="qtc-preview-modal" style="display: none;" aria-hidden="true">
            <div class="qtc-preview-backdrop"></div>
            <div class="qtc-preview-dialog">
                <header class="qtc-preview-header">
                    <div class="qtc-preview-header-meta">
                        <span class="qtc-preview-badge">✨ <?php esc_html_e( '模板实时虚拟预览', 'qiling' ); ?></span>
                        <h3 id="qtc-preview-title" class="qtc-preview-title"></h3>
                        <span id="qtc-preview-category" class="qtc-preview-category"></span>
                    </div>

                    <div class="qtc-preview-device-switcher">
                        <button type="button" class="qtc-device-btn is-active" data-device="desktop" title="<?php esc_attr_e( '桌面宽幅视口', 'qiling' ); ?>">
                            <span class="dashicons dashicons-desktop"></span> <span><?php esc_html_e( '桌面端', 'qiling' ); ?></span>
                        </button>
                        <button type="button" class="qtc-device-btn" data-device="tablet" title="<?php esc_attr_e( '平板视口 (768px)', 'qiling' ); ?>">
                            <span class="dashicons dashicons-tablet"></span> <span><?php esc_html_e( '平板端', 'qiling' ); ?></span>
                        </button>
                        <button type="button" class="qtc-device-btn" data-device="mobile" title="<?php esc_attr_e( '手机视口 (375px)', 'qiling' ); ?>">
                            <span class="dashicons dashicons-smartphone"></span> <span><?php esc_html_e( '手机端', 'qiling' ); ?></span>
                        </button>
                    </div>

                    <div class="qtc-preview-header-actions">
                        <a id="qtc-preview-newtab" href="#" target="_blank" rel="noopener noreferrer" class="button button-secondary" title="<?php esc_attr_e( '在新标签页全屏打开', 'qiling' ); ?>">
                            <span class="dashicons dashicons-external"></span> <?php esc_html_e( '新窗口打开', 'qiling' ); ?>
                        </a>
                        <button type="button" id="qtc-preview-use-btn" class="button button-primary">
                            ✨ <?php esc_html_e( '使用此模板', 'qiling' ); ?>
                        </button>
                        <button type="button" id="qtc-preview-close" class="qtc-preview-close-btn" title="<?php esc_attr_e( '关闭 (Esc)', 'qiling' ); ?>">
                            ✕
                        </button>
                    </div>
                </header>

                <div class="qtc-preview-body">
                    <div id="qtc-preview-loading" class="qtc-preview-loading">
                        <div class="qtc-preview-spinner"></div>
                        <p><?php esc_html_e( '正在从官方页面包实时虚拟渲染，请稍候...', 'qiling' ); ?></p>
                    </div>
                    <div id="qtc-preview-viewport" class="qtc-preview-viewport is-desktop">
                        <iframe id="qtc-preview-iframe" src="" frameborder="0" title="Template Preview Frame"></iframe>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_scripts() {
        ?>
        <script>
        (function() {
            var images = Array.prototype.slice.call(document.querySelectorAll('.qiling-template-center img[data-qtc-src]'));
            if (!images.length) {
                return;
            }

            function loadImage(img) {
                var source = img.getAttribute('data-qtc-src');
                if (!source) {
                    return;
                }

                img.removeAttribute('data-qtc-src');
                img.addEventListener('load', function() {
                    img.classList.add('is-loaded');
                    if (img.parentElement) {
                        img.parentElement.classList.add('is-loaded');
                    }
                }, { once: true });
                img.addEventListener('error', function() {
                    if (img.parentElement) {
                        img.parentElement.classList.add('is-error');
                    }
                }, { once: true });
                img.src = source;
            }

            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }
                        observer.unobserve(entry.target);
                        loadImage(entry.target);
                    });
                }, { rootMargin: '360px 0px', threshold: 0.01 });

                images.forEach(function(img) {
                    observer.observe(img);
                });
                return;
            }

            images.slice(0, 8).forEach(loadImage);
            window.setTimeout(function() {
                images.forEach(loadImage);
            }, 1200);
        }());

        // 模板中心分类、即时检索与折叠面板控制器
        (function() {
            var tabs = document.querySelectorAll('.qtc-tab-btn');
            var statusPills = document.querySelectorAll('.qtc-status-filter-pills .qtc-pill-btn');
            var searchInput = document.getElementById('qtc-instant-search');
            var clearSearchBtn = document.getElementById('qtc-clear-search');
            var tagPills = document.querySelectorAll('.qtc-tag-pill');
            var cards = Array.prototype.slice.call(document.querySelectorAll('.qtc-template-card'));
            var countEl = document.getElementById('qtc-visible-count');
            var noResultsEl = document.getElementById('qtc-no-results');
            var resetBtn = document.getElementById('qtc-reset-filters');
            var toggleDashboardBtn = document.getElementById('qtc-toggle-dashboard');
            var dashboardEl = document.getElementById('qtc-admin-dashboard');

            var currentGroup = 'featured';
            var currentStatus = 'all';
            var currentKeyword = '';

            // 读取 URL 查询参数或默认值
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('scenario_group')) {
                currentGroup = urlParams.get('scenario_group');
            } else if (urlParams.get('s') || urlParams.get('template_category') || urlParams.get('template_industry')) {
                currentGroup = 'all';
            }

            if (urlParams.get('s')) {
                currentKeyword = urlParams.get('s').toLowerCase().trim();
                if (searchInput) searchInput.value = currentKeyword;
            }

            // 折叠面板切换逻辑
            if (toggleDashboardBtn && dashboardEl) {
                var isExpanded = false;
                try {
                    isExpanded = localStorage.getItem('qtc_dashboard_expanded') === '1';
                } catch(e) {}

                function setDashboardState(expanded) {
                    dashboardEl.style.display = expanded ? 'block' : 'none';
                    toggleDashboardBtn.classList.toggle('is-expanded', expanded);
                    toggleDashboardBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    var icon = toggleDashboardBtn.querySelector('.qtc-toggle-icon');
                    if (icon) {
                        icon.className = expanded ? 'dashicons dashicons-arrow-up-alt2 qtc-toggle-icon' : 'dashicons dashicons-arrow-down-alt2 qtc-toggle-icon';
                    }
                    try {
                        localStorage.setItem('qtc_dashboard_expanded', expanded ? '1' : '0');
                    } catch(e) {}
                }

                if (isExpanded) {
                    setDashboardState(true);
                }

                toggleDashboardBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var isOpen = dashboardEl.style.display !== 'none';
                    setDashboardState(!isOpen);
                });
            }

            // 核心卡片毫秒级过滤渲染
            function filterCards() {
                var visibleCount = 0;
                var kw = currentKeyword.toLowerCase().trim();

                if (clearSearchBtn) {
                    clearSearchBtn.style.display = kw ? 'inline-flex' : 'none';
                }

                cards.forEach(function(card) {
                    var cardGroup = card.getAttribute('data-scenario-group');
                    var cardUsed = card.getAttribute('data-used');
                    var cardSearch = (card.getAttribute('data-search') || '').toLowerCase();

                    var matchGroup = (currentGroup === 'all' || cardGroup === currentGroup);
                    var matchStatus = (currentStatus === 'all') ||
                                      (currentStatus === 'used' && cardUsed === '1') ||
                                      (currentStatus === 'unused' && cardUsed === '0');
                    var matchKeyword = true;

                    if (kw) {
                        // 有搜索词时在当前卡片中全文检索
                        matchKeyword = cardSearch.indexOf(kw) !== -1;
                    }

                    if (matchGroup && matchStatus && matchKeyword) {
                        card.style.display = '';
                        visibleCount++;

                        // 立即激活刚刚变为可见的延迟加载图片
                        var lazyImg = card.querySelector('img[data-qtc-src]');
                        if (lazyImg) {
                            var src = lazyImg.getAttribute('data-qtc-src');
                            if (src) {
                                lazyImg.removeAttribute('data-qtc-src');
                                lazyImg.src = src;
                                lazyImg.classList.add('is-loaded');
                            }
                        }
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (countEl) countEl.textContent = visibleCount;
                if (noResultsEl) noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            // Tabs 切换事件
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    tabs.forEach(function(t) { t.classList.remove('is-active'); t.setAttribute('aria-selected', 'false'); });
                    tab.classList.add('is-active');
                    tab.setAttribute('aria-selected', 'true');
                    currentGroup = tab.getAttribute('data-group') || 'all';
                    filterCards();
                });
            });

            // 使用状态筛选胶囊事件
            statusPills.forEach(function(pill) {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();
                    statusPills.forEach(function(p) { p.classList.remove('is-active'); });
                    pill.classList.add('is-active');
                    currentStatus = pill.getAttribute('data-status') || 'all';
                    filterCards();
                });
            });

            // 即时防抖搜索输入事件
            var searchTimer = null;
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        currentKeyword = searchInput.value;
                        // 若用户在特定分类下搜索未找到结果，自动切到全部分类以便全局匹配
                        filterCards();
                    }, 120);
                });

                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        searchInput.value = '';
                        currentKeyword = '';
                        filterCards();
                    }
                });
            }

            // 清空搜索按钮
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                    }
                    currentKeyword = '';
                    filterCards();
                });
            }

            // 热门推荐标签点击
            tagPills.forEach(function(tag) {
                tag.addEventListener('click', function(e) {
                    e.preventDefault();
                    var kw = tag.getAttribute('data-keyword') || '';
                    if (searchInput) {
                        searchInput.value = kw;
                    }
                    currentKeyword = kw;

                    // 自动切换至“全部”大类进行全局搜索
                    tabs.forEach(function(t) {
                        var isAll = t.getAttribute('data-group') === 'all';
                        t.classList.toggle('is-active', isAll);
                        t.setAttribute('aria-selected', isAll ? 'true' : 'false');
                    });
                    currentGroup = 'all';

                    filterCards();
                });
            });

            // 空状态重置按钮
            if (resetBtn) {
                resetBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentGroup = 'featured';
                    currentStatus = 'all';
                    currentKeyword = '';
                    if (searchInput) searchInput.value = '';

                    tabs.forEach(function(t) {
                        var isFeat = t.getAttribute('data-group') === 'featured';
                        t.classList.toggle('is-active', isFeat);
                        t.setAttribute('aria-selected', isFeat ? 'true' : 'false');
                    });

                    statusPills.forEach(function(p) {
                        p.classList.toggle('is-active', p.getAttribute('data-status') === 'all');
                    });

                    filterCards();
                });
            }

            // 初始状态同步与激活
            tabs.forEach(function(t) {
                var isMatch = t.getAttribute('data-group') === currentGroup;
                t.classList.toggle('is-active', isMatch);
                t.setAttribute('aria-selected', isMatch ? 'true' : 'false');
            });
            filterCards();
        })();

        // 模板中心免存库实时虚拟预览控制器
        (function() {
            var modal = document.getElementById('qtc-preview-modal');
            if (!modal) return;

            var iframe = document.getElementById('qtc-preview-iframe');
            var viewport = document.getElementById('qtc-preview-viewport');
            var loading = document.getElementById('qtc-preview-loading');
            var titleEl = document.getElementById('qtc-preview-title');
            var catEl = document.getElementById('qtc-preview-category');
            var newTabBtn = document.getElementById('qtc-preview-newtab');
            var useBtn = document.getElementById('qtc-preview-use-btn');
            var closeBtn = document.getElementById('qtc-preview-close');
            var backdrop = modal.querySelector('.qtc-preview-backdrop');
            var deviceBtns = modal.querySelectorAll('.qtc-device-btn');
            var currentTemplate = '';

            function openPreview(btn) {
                var url = btn.getAttribute('data-preview-url');
                var title = btn.getAttribute('data-title') || '';
                var category = btn.getAttribute('data-category') || '';
                currentTemplate = btn.getAttribute('data-template') || '';

                if (!url) return;

                titleEl.textContent = title;
                catEl.textContent = category;
                newTabBtn.href = url;

                // 呈现加载态
                loading.style.display = 'flex';

                var hideLoading = function() {
                    loading.style.display = 'none';
                };

                // 核心修复：必须在设置 src 之前绑定 load 与 error 事件
                iframe.onload = hideLoading;
                iframe.onerror = hideLoading;

                // 超时兜底：2.5 秒无论网络快慢必然隐藏遮罩，杜绝死锁转圈
                if (window.qtcPreviewTimer) {
                    clearTimeout(window.qtcPreviewTimer);
                }
                window.qtcPreviewTimer = setTimeout(hideLoading, 2500);

                iframe.src = url;

                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closePreview() {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                iframe.src = '';
                document.body.style.overflow = '';
            }

            document.addEventListener('click', function(e) {
                var trigger = e.target.closest('.qtc-preview-trigger');
                if (trigger) {
                    e.preventDefault();
                    e.stopPropagation();
                    openPreview(trigger);
                }
            });

            if (closeBtn) closeBtn.addEventListener('click', closePreview);
            if (backdrop) backdrop.addEventListener('click', closePreview);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display !== 'none') {
                    closePreview();
                }
            });

            deviceBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    deviceBtns.forEach(function(b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    var device = btn.getAttribute('data-device');
                    viewport.className = 'qtc-preview-viewport is-' + device;
                });
            });

            if (useBtn) {
                useBtn.addEventListener('click', function() {
                    closePreview();
                    if (!currentTemplate) return;
                    var inputEl = document.querySelector('input[name="template"][value="' + currentTemplate + '"]');
                    if (inputEl && inputEl.closest('form')) {
                        var form = inputEl.closest('form');
                        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        var titleInput = form.querySelector('input[name="page_title"]');
                        if (titleInput) {
                            titleInput.focus();
                            titleInput.select();
                        }
                    }
                });
            }
        })();
        </script>
        <?php
    }
}
