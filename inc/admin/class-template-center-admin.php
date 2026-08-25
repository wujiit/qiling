<?php
/**
 * Official template center admin page.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Template_Center_Admin {

    /**
     * Admin page slug.
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
     * Register submenu under theme settings.
     *
     * @return void
     */
    public function register_submenu_page() {
        $hook = add_submenu_page(
            'developer-starter-settings',
            __( '模板中心', 'developer-starter' ),
            __( '模板中心', 'developer-starter' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'render_page' )
        );

        if ( is_string( $hook ) && '' !== $hook ) {
            add_action( 'load-' . $hook, array( $this, 'maybe_handle_create_request' ) );
        }
    }

    /**
     * Handle create request before the admin page starts rendering.
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
     * Render admin template center.
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'developer-starter' ) );
        }

        $catalog = $this->get_template_catalog();
        $filters = $this->get_current_filters();
        $filtered_catalog = $this->filter_catalog( $catalog, $filters );
        $category_choices = $this->get_category_choices( $catalog );
        $industry_choices = $this->get_industry_choices( $catalog );
        $industry_coverage = $this->get_industry_coverage_data( $catalog );
        $stats = $this->get_catalog_stats( $catalog, $industry_coverage );

        ?>
        <div class="wrap qiling-template-center">
            <h1><?php esc_html_e( '模板中心', 'developer-starter' ); ?></h1>
            <p class="description">
                <?php esc_html_e( '这里展示启灵官方模板：官网、博客、杂志、行业落地页、产品页和内容页。', 'developer-starter' ); ?>
            </p>

            <?php $this->render_notice(); ?>
            <?php $this->render_styles(); ?>

            <div class="qtc-summary-grid">
                <div class="qtc-summary-card">
                    <strong><?php echo esc_html( number_format_i18n( $stats['total'] ) ); ?></strong>
                    <span><?php esc_html_e( '官方模板总数', 'developer-starter' ); ?></span>
                </div>
                <div class="qtc-summary-card">
                    <strong><?php echo esc_html( number_format_i18n( $stats['industry'] ) ); ?></strong>
                    <span><?php esc_html_e( '行业模板', 'developer-starter' ); ?></span>
                </div>
                <div class="qtc-summary-card">
                    <strong><?php echo esc_html( number_format_i18n( $stats['non_industry'] ) ); ?></strong>
                    <span><?php esc_html_e( '非行业模板', 'developer-starter' ); ?></span>
                </div>
                <div class="qtc-summary-card">
                    <strong><?php echo esc_html( sprintf( '%s/%s', number_format_i18n( $stats['covered_industries'] ), number_format_i18n( $stats['standard_industries'] ) ) ); ?></strong>
                    <span><?php esc_html_e( '行业覆盖', 'developer-starter' ); ?></span>
                </div>
                <div class="qtc-summary-card">
                    <strong><?php echo esc_html( number_format_i18n( $stats['missing_industries'] ) ); ?></strong>
                    <span><?php esc_html_e( '暂无模板行业', 'developer-starter' ); ?></span>
                </div>
                <div class="qtc-summary-card">
                    <strong><?php echo esc_html( number_format_i18n( $stats['created_pages'] ) ); ?></strong>
                    <span><?php esc_html_e( '使用中页面', 'developer-starter' ); ?></span>
                </div>
                <div class="qtc-summary-card">
                    <strong><?php echo esc_html( number_format_i18n( count( $category_choices ) - 1 ) ); ?></strong>
                    <span><?php esc_html_e( '分组数量', 'developer-starter' ); ?></span>
                </div>
            </div>

            <form class="qtc-filter-bar" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
                <input type="hidden" name="page" value="<?php echo esc_attr( $this->page_slug ); ?>" />
                <label>
                    <span><?php esc_html_e( '分组', 'developer-starter' ); ?></span>
                    <select name="template_category">
                        <?php foreach ( $category_choices as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['category'], $key ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e( '行业', 'developer-starter' ); ?></span>
                    <select name="template_industry">
                        <?php foreach ( $industry_choices as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['industry'], $key ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e( '使用状态', 'developer-starter' ); ?></span>
                    <select name="template_usage">
                        <option value="all" <?php selected( $filters['usage'], 'all' ); ?>><?php esc_html_e( '全部状态', 'developer-starter' ); ?></option>
                        <option value="used" <?php selected( $filters['usage'], 'used' ); ?>><?php esc_html_e( '正在使用', 'developer-starter' ); ?></option>
                        <option value="unused" <?php selected( $filters['usage'], 'unused' ); ?>><?php esc_html_e( '尚未使用', 'developer-starter' ); ?></option>
                    </select>
                </label>
                <label class="qtc-search">
                    <span><?php esc_html_e( '搜索', 'developer-starter' ); ?></span>
                    <input type="search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( '模板名称、行业、用途', 'developer-starter' ); ?>" />
                </label>
                <button type="submit" class="button button-primary"><?php esc_html_e( '筛选模板', 'developer-starter' ); ?></button>
                <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->page_slug ) ); ?>"><?php esc_html_e( '重置', 'developer-starter' ); ?></a>
            </form>

            <div class="qtc-admin-note">
                <strong><?php esc_html_e( '使用方式', 'developer-starter' ); ?></strong>
                <?php esc_html_e( '点击“创建页面”后，系统会创建一个新页面、写入对应页面模板，并立即填充启灵官方预设模块。创建后可进入后台编辑器或前台装修继续改。', 'developer-starter' ); ?>
                <br />
                <?php esc_html_e( '“使用中页面”统计当前站点里正在使用这些官方模板的页面数量，包括通过模板中心创建和手动套用模板的页面；卡片内可展开查看具体页面。', 'developer-starter' ); ?>
            </div>

            <?php $this->render_industry_coverage_panel( $industry_coverage, $filters ); ?>

            <?php if ( empty( $filtered_catalog ) ) : ?>
                <div class="notice notice-info inline">
                    <p><?php esc_html_e( '没有找到匹配的官方模板，请调整筛选条件。', 'developer-starter' ); ?></p>
                </div>
            <?php else : ?>
                <div class="qtc-template-grid">
                    <?php foreach ( $filtered_catalog as $template_index => $template ) : ?>
                        <?php $this->render_template_card( $template, absint( $template_index ) ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php $this->render_scripts(); ?>
        <?php
    }

    /**
     * Handle page creation request.
     *
     * @return void
     */
    private function handle_create_request() {
        $action = sanitize_key( $this->get_request_value( $_POST, 'qiling_template_center_action' ) );
        if ( 'create_page' !== $action ) {
            return;
        }

        check_admin_referer( 'developer_starter_template_center_create' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'developer-starter' ) );
        }

        $catalog = $this->get_template_catalog();

        $template = sanitize_text_field( $this->get_request_value( $_POST, 'template' ) );
        $template = function_exists( 'developer_starter_normalize_page_template_slug' )
            ? developer_starter_normalize_page_template_slug( $template )
            : str_replace( '\\', '/', $template );
        $template_variant = sanitize_key( $this->get_request_value( $_POST, 'template_variant' ) );

        if ( '' === $template ) {
            $this->redirect_with_error( __( '模板无效或不属于启灵官方模板中心。', 'developer-starter' ) );
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
                $this->redirect_with_error( __( '模板变体无效或不属于当前官方模板。', 'developer-starter' ) );
            }

            $entry = $fallback_entry;
        }

        if ( ! is_array( $entry ) ) {
            $this->redirect_with_error( __( '模板无效或不属于启灵官方模板中心。', 'developer-starter' ) );
        }

        $title = sanitize_text_field( $this->get_request_value( $_POST, 'page_title' ) );
        if ( '' === trim( $title ) ) {
            $title = isset( $entry['label'] ) ? (string) $entry['label'] : __( '启灵模板页面', 'developer-starter' );
        }

        $slug = sanitize_title( $this->get_request_value( $_POST, 'page_slug' ) );
        $status = sanitize_key( $this->get_request_value( $_POST, 'page_status' ) );
        if ( ! in_array( $status, array( 'draft', 'publish' ), true ) ) {
            $status = 'draft';
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

        $redirect = add_query_arg(
            array(
                'page'             => $this->page_slug,
                'template_created' => '1',
                'post_id'          => absint( $post_id ),
                'modules_filled'   => $filled ? '1' : '0',
                'template_localized'=> is_array( $localization_result ) ? '1' : '0',
                'template_localization_error' => is_wp_error( $localization_result ) ? $localization_result->get_error_message() : '',
            ),
            admin_url( 'admin.php' )
        );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Render page notice after redirect.
     *
     * @return void
     */
    private function render_notice() {
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
        $localized = '1' === $this->get_request_value( $_GET, 'template_localized' );
        $localization_error = sanitize_text_field( $this->get_request_value( $_GET, 'template_localization_error' ) );
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php echo esc_html( $filled ? __( '页面已创建，官方预设模块已填充。', 'developer-starter' ) : __( '页面已创建，但没有填充到模块；请进入页面检查模板或手动装修。', 'developer-starter' ) ); ?>
                <?php if ( $localized ) : ?>
                    <?php esc_html_e( '已同步生成对应语言页面。', 'developer-starter' ); ?>
                <?php elseif ( '' !== $localization_error ) : ?>
                    <?php echo esc_html( sprintf( __( '自动本地化未完成：%s', 'developer-starter' ), $localization_error ) ); ?>
                <?php endif; ?>
                <?php if ( $edit_link ) : ?>
                    <a href="<?php echo esc_url( $edit_link ); ?>"><?php esc_html_e( '编辑页面', 'developer-starter' ); ?></a>
                <?php endif; ?>
                <?php if ( $builder_link ) : ?>
                    <a href="<?php echo esc_url( $builder_link ); ?>"><?php esc_html_e( '前台装修', 'developer-starter' ); ?></a>
                <?php endif; ?>
            </p>
        </div>
        <?php
    }

    /**
     * Redirect with error message.
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
     * Localize a newly created template page through AI Decorator.
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
            return new \WP_Error( 'ai_decorator_unavailable', __( 'AI 装修服务未加载，模板已创建但未自动本地化。', 'developer-starter' ) );
        }

        $decorator = \Developer_Starter\Core\AI_Decorator::get_instance();
        if ( ! $decorator->is_enabled() ) {
            return new \WP_Error( 'ai_decorator_disabled', __( 'AI 装修尚未启用，模板已创建但未自动本地化。', 'developer-starter' ) );
        }

        $defaults = $decorator->get_default_ai_connection_request_args();

        return $decorator->localize_page_package(
            array(
                'post_id'       => $post_id,
                'prompt'        => __( '本地化模板中心导入的页面包，并保留原模板结构。', 'developer-starter' ),
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
     * Template import localization languages.
     *
     * @return array<string,string>
     */
    private function get_template_localization_language_choices() {
        return array(
            'en' => __( '英文', 'developer-starter' ),
            'ja' => __( '日文', 'developer-starter' ),
            'ko' => __( '韩文', 'developer-starter' ),
            'fr' => __( '法文', 'developer-starter' ),
            'de' => __( '德文', 'developer-starter' ),
            'es' => __( '西班牙文', 'developer-starter' ),
        );
    }

	/**
	 * Get official JSON template package service.
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
	 * Get current filters from query string.
	 *
	 * @return array<string,string>
     */
	private function get_current_filters() {
		return array(
			'category' => sanitize_key( $this->get_request_value( $_GET, 'template_category', 'all' ) ),
			'industry' => $this->normalize_industry_key( $this->get_request_value( $_GET, 'template_industry', 'all' ) ),
			'usage'    => sanitize_key( $this->get_request_value( $_GET, 'template_usage', 'all' ) ),
            'search'   => sanitize_text_field( $this->get_request_value( $_GET, 's' ) ),
        );
    }

    /**
     * Build official template catalog.
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

			$catalog[] = array_merge(
				array(
					'id'             => sanitize_key( basename( $template, '.php' ) ),
                    'template'       => $template,
					'label'          => $label,
                    'source'         => 'official',
                    'source_label'   => __( '启灵官方', 'developer-starter' ),
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
         * Filter the official template center catalog.
         *
         * This hook is for first-party/local extension. It is not a marketplace
         * feed and does not imply third-party author listing.
         *
         * @param array<int,array<string,mixed>> $catalog Template entries.
         */
        return apply_filters( 'developer_starter_template_center_catalog', $catalog );
    }

    /**
     * Filter catalog by admin query.
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
     * Render one template card.
     *
     * @param array<string,mixed> $template Template entry.
     * @param int                 $card_index Card index in the current result set.
     * @return void
     */
	private function render_template_card( $template, $card_index = 0 ) {
		$label = isset( $template['label'] ) ? (string) $template['label'] : '';
		$template_file = isset( $template['template'] ) ? (string) $template['template'] : '';
        $template_variant = isset( $template['template_variant'] ) ? sanitize_key( (string) $template['template_variant'] ) : '';
		$badges = isset( $template['badges'] ) && is_array( $template['badges'] ) ? $template['badges'] : array();
		$thumbnail = isset( $template['thumbnail'] ) ? esc_url( (string) $template['thumbnail'] ) : '';
		$source_label = isset( $template['source_label'] ) ? (string) $template['source_label'] : __( '启灵官方', 'developer-starter' );
		$category_label = isset( $template['category_label'] ) ? (string) $template['category_label'] : '';
		$description = isset( $template['description'] ) ? (string) $template['description'] : '';
		$industry_label = isset( $template['industry_label'] ) ? (string) $template['industry_label'] : __( '通用', 'developer-starter' );
		$scenario = isset( $template['scenario'] ) ? (string) $template['scenario'] : '';
		$usage_count = isset( $template['created_pages'] ) ? absint( $template['created_pages'] ) : 0;
		$usage_pages = isset( $template['usage_pages'] ) && is_array( $template['usage_pages'] ) ? $template['usage_pages'] : array();
		$defer_thumbnail = '' !== $thumbnail && $card_index >= 6;
		$thumbnail_src = $defer_thumbnail ? 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==' : $thumbnail;
		$thumbnail_class = $defer_thumbnail ? ' qtc-thumb-lazy' : ' is-loaded';
		?>
		<article class="qtc-template-card<?php echo $usage_count > 0 ? ' is-used' : ' is-unused'; ?>">
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
					<span><?php echo esc_html( '' !== $category_label ? $category_label : __( '官方模板', 'developer-starter' ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="qtc-card-head">
				<span class="qtc-source"><?php echo esc_html( $source_label ); ?></span>
				<span class="qtc-category"><?php echo esc_html( $category_label ); ?></span>
				<span class="qtc-usage-pill"><?php echo esc_html( $usage_count > 0 ? sprintf( __( '使用中 %s', 'developer-starter' ), number_format_i18n( $usage_count ) ) : __( '未使用', 'developer-starter' ) ); ?></span>
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
                    <dt><?php esc_html_e( '行业', 'developer-starter' ); ?></dt>
                    <dd><?php echo esc_html( $industry_label ); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e( '用途', 'developer-starter' ); ?></dt>
                    <dd><?php echo esc_html( $scenario ); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e( '使用中', 'developer-starter' ); ?></dt>
                    <dd><?php echo esc_html( number_format_i18n( $usage_count ) ); ?></dd>
                </div>
            </dl>
            <span class="qtc-template-file"><?php esc_html_e( '启灵官方页面模板', 'developer-starter' ); ?></span>
            <?php if ( ! empty( $usage_pages ) ) : ?>
                <details class="qtc-usage-details">
                    <summary><?php echo esc_html( sprintf( __( '查看 %s 个使用页面', 'developer-starter' ), number_format_i18n( $usage_count ) ) ); ?></summary>
                    <ul>
                        <?php foreach ( $usage_pages as $page ) : ?>
                            <?php
                            $page_title = isset( $page['title'] ) ? (string) $page['title'] : __( '(无标题)', 'developer-starter' );
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
                                    <a class="qtc-usage-view-link" href="<?php echo esc_url( $view_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( '查看', 'developer-starter' ); ?></a>
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
                    <span><?php esc_html_e( '页面标题', 'developer-starter' ); ?></span>
                    <input type="text" name="page_title" value="<?php echo esc_attr( $label ); ?>" />
                </label>
                <label>
                    <span><?php esc_html_e( '别名', 'developer-starter' ); ?></span>
                    <input type="text" name="page_slug" placeholder="<?php esc_attr_e( '可留空自动生成', 'developer-starter' ); ?>" />
                </label>
                <label>
                    <span><?php esc_html_e( '状态', 'developer-starter' ); ?></span>
                    <select name="page_status">
                        <option value="draft"><?php esc_html_e( '草稿', 'developer-starter' ); ?></option>
                        <option value="publish"><?php esc_html_e( '发布', 'developer-starter' ); ?></option>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e( '目标语言', 'developer-starter' ); ?></span>
                    <select name="template_target_language">
                        <option value=""><?php esc_html_e( '不自动本地化', 'developer-starter' ); ?></option>
                        <?php foreach ( $this->get_template_localization_language_choices() as $lang_code => $lang_label ) : ?>
                            <option value="<?php echo esc_attr( $lang_code ); ?>"><?php echo esc_html( $lang_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e( '目标市场', 'developer-starter' ); ?></span>
                    <input type="text" name="template_target_market" placeholder="<?php esc_attr_e( '例如：美国 / 日本 / 韩国 / 法国 / 德国 / 西班牙', 'developer-starter' ); ?>" />
                </label>
                <label class="qtc-checkbox">
                    <input type="checkbox" name="template_localization_enable" value="1" />
                    <span><?php esc_html_e( '创建后生成对应语言页面', 'developer-starter' ); ?></span>
                </label>
                <button type="submit" class="button button-primary"><?php esc_html_e( '创建页面', 'developer-starter' ); ?></button>
            </form>
        </article>
        <?php
    }

    /**
     * Template metadata.
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
        $scenario = __( '通用官网页面', 'developer-starter' );
        $badges = array( __( '模块化', 'developer-starter' ), __( '可装修', 'developer-starter' ) );

        if ( ! empty( $config['preset'] ) ) {
            $category = 'industry';
            $industry = $this->normalize_industry_key( $config['preset'] );
            $scenario = __( '行业预约/转化页', 'developer-starter' );
            $badges[] = __( '行业预设', 'developer-starter' );
        } elseif ( preg_match( '/blog|news|topic|resource|resources|latest/i', $template ) ) {
            $category = 'content';
            $scenario = __( '内容、栏目、杂志与资源页', 'developer-starter' );
        } elseif ( preg_match( '/software|product|saas|app|developer|ai|cybersecurity|data|open-source/i', $template ) ) {
            $category = 'product';
            $scenario = __( '产品、SaaS、技术平台官网', 'developer-starter' );
        } elseif ( preg_match( '/landing|ecommerce|interactive|course/i', $template ) ) {
            $category = 'conversion';
            $scenario = __( '落地页、活动页、销售转化页', 'developer-starter' );
        } elseif ( preg_match( '/resume|personal/i', $template ) ) {
            $category = 'personal';
            $scenario = __( '个人品牌与作品展示页', 'developer-starter' );
        }

        return array(
            'category'       => $category,
            'category_label' => $this->get_category_label( $category ),
            'industry'       => $industry,
            'industry_label' => $this->get_industry_label( $industry ),
            'scenario'       => $scenario,
            'description'    => sprintf(
                /* translators: %s: template label. */
                __( '%s 是启灵官方内置模板，创建后会自动填充对应模块，可继续用后台模块编辑或前台装修调整。', 'developer-starter' ),
                $label
            ),
            'badges'         => $badges,
            'order'          => $this->get_category_order( $category ) + 50,
        );
    }

    /**
     * Specific first-party metadata.
     *
     * @return array<string,array<string,mixed>>
     */
    private function get_specific_template_meta() {
        return array(
            'templates/template-home.php' => $this->meta( 'corporate', 'enterprise', __( '企业官网首页', 'developer-starter' ), __( '标准企业官网首页骨架，包含首屏、服务、优势、案例和转化入口。', 'developer-starter' ), array( __( '官网', 'developer-starter' ), __( '首页', 'developer-starter' ) ), 10 ),
            'templates/template-solutions.php' => $this->meta( 'corporate', 'enterprise', __( '解决方案页', 'developer-starter' ), __( '适合行业方案、服务方案和客户问题解决路径展示。', 'developer-starter' ), array( __( '服务', 'developer-starter' ), __( '方案', 'developer-starter' ) ), 20 ),
            'templates/template-products.php' => $this->meta( 'product', 'b2b', __( '产品中心', 'developer-starter' ), __( '用于产品列表、产品矩阵和产品卖点集中展示。', 'developer-starter' ), array( __( '产品', 'developer-starter' ), __( '列表', 'developer-starter' ) ), 30 ),
            'templates/template-cases.php' => $this->meta( 'corporate', 'enterprise', __( '案例展示', 'developer-starter' ), __( '适合沉淀客户案例、项目作品和行业实践。', 'developer-starter' ), array( __( '案例', 'developer-starter' ), __( '信任', 'developer-starter' ) ), 40 ),
            'templates/template-blog.php' => $this->meta( 'content', 'blog', __( '博客页面', 'developer-starter' ), __( '博客、专栏、知识库和内容营销入口。', 'developer-starter' ), array( __( '博客', 'developer-starter' ), __( '内容', 'developer-starter' ) ), 110 ),
            'templates/template-news.php' => $this->meta( 'content', 'media', __( '新闻中心', 'developer-starter' ), __( '适合资讯、公告、企业动态和行业新闻栏目。', 'developer-starter' ), array( __( '新闻', 'developer-starter' ), __( '杂志', 'developer-starter' ) ), 120 ),
            'templates/template-topic.php' => $this->meta( 'content', 'magazine', __( '专题页面', 'developer-starter' ), __( '用于专题策划、活动专题、聚合页和编辑型内容。', 'developer-starter' ), array( __( '专题', 'developer-starter' ), __( '聚合', 'developer-starter' ) ), 130 ),
            'templates/template-features-showcase.php' => $this->meta( 'product', 'technology', __( '功能展示页', 'developer-starter' ), __( '适合产品功能、能力矩阵和核心卖点集中呈现。', 'developer-starter' ), array( __( '功能', 'developer-starter' ), __( '产品', 'developer-starter' ) ), 140 ),
            'templates/template-resources.php' => $this->meta( 'content', 'media', __( '资源中心', 'developer-starter' ), __( '适合资料下载、内容资源、白皮书和知识库聚合。', 'developer-starter' ), array( __( '资源', 'developer-starter' ), __( '内容', 'developer-starter' ) ), 150 ),
            'templates/template-resource-search.php' => $this->meta( 'content', 'media', __( '资源搜索页', 'developer-starter' ), __( '适合资料库、文档中心和资源检索入口。', 'developer-starter' ), array( __( '搜索', 'developer-starter' ), __( '资源', 'developer-starter' ) ), 160 ),
            'templates/template-landing.php' => $this->meta( 'conversion', 'general', __( '落地页', 'developer-starter' ), __( '面向投放、获客、表单线索和单页转化。', 'developer-starter' ), array( __( '转化', 'developer-starter' ), __( '线索', 'developer-starter' ) ), 210 ),
            'templates/template-ecommerce-promo.php' => $this->meta( 'conversion', 'ecommerce', __( '电商大促会场页', 'developer-starter' ), __( '促销活动、爆款商品和专题会场页面。', 'developer-starter' ), array( __( '电商', 'developer-starter' ), __( '活动', 'developer-starter' ) ), 220 ),
            'templates/template-video-hero.php' => $this->meta( 'conversion', 'enterprise', __( '视频首屏页', 'developer-starter' ), __( '适合品牌大片、活动首屏和高视觉冲击的转化入口。', 'developer-starter' ), array( __( '视频', 'developer-starter' ), __( '首屏', 'developer-starter' ) ), 230 ),
            'templates/template-video-portal.php' => $this->meta( 'content', 'media', __( '影视门户首页', 'developer-starter' ), __( '适用于电影、电视剧、动漫、综艺等视频内容门户。', 'developer-starter' ), array( __( '娱乐', 'developer-starter' ), __( '影视', 'developer-starter' ), __( '门户', 'developer-starter' ) ), 125 ),
            'templates/template-video-ranking.php' => $this->meta( 'content', 'media', __( '影视排行榜', 'developer-starter' ), __( '影视站专用的深色全宽排行榜页面，适合总榜、电影榜、剧集榜与动漫榜。', 'developer-starter' ), array( __( '影视', 'developer-starter' ), __( '排行榜', 'developer-starter' ) ), 126 ),
            'templates/template-interactive-product-launch.php' => $this->meta( 'conversion', 'technology', __( '互动产品发布页', 'developer-starter' ), __( '适合新品发布、互动展示、发布会和产品上市活动。', 'developer-starter' ), array( __( '发布', 'developer-starter' ), __( '互动', 'developer-starter' ) ), 240 ),
            'templates/template-course-enrollment.php' => $this->meta( 'conversion', 'education', __( '课程报名页', 'developer-starter' ), __( '适合课程招生、训练营报名、公开课和教育转化。', 'developer-starter' ), array( __( '教育', 'developer-starter' ), __( '报名', 'developer-starter' ) ), 250 ),
            'templates/template-app-download-landing.php' => $this->meta( 'conversion', 'app', __( 'APP 下载页', 'developer-starter' ), __( '适合移动应用、工具 APP、客户端下载和预约体验。', 'developer-starter' ), array( __( 'APP', 'developer-starter' ), __( '下载', 'developer-starter' ) ), 260 ),
            'templates/template-ai-product-brand.php' => $this->meta( 'product', 'technology', __( 'AI 产品品牌官网', 'developer-starter' ), __( '适合 AI 产品、Agent 平台、智能工具和企业级 AI 服务。', 'developer-starter' ), array( __( 'AI', 'developer-starter' ), __( 'SaaS', 'developer-starter' ) ), 310 ),
            'templates/template-software-home.php' => $this->meta( 'product', 'software', __( '软件首页', 'developer-starter' ), __( '软件产品官网首页，突出能力、版本、案例和下载/咨询入口。', 'developer-starter' ), array( __( '软件', 'developer-starter' ), __( '产品', 'developer-starter' ) ), 320 ),
            'templates/template-saas-home.php' => $this->meta( 'product', 'saas', __( 'SaaS 行业官网', 'developer-starter' ), __( '适合订阅软件、协作工具、客户成功平台和企业级在线服务官网。', 'developer-starter' ), array( __( 'SaaS', 'developer-starter' ), __( '订阅', 'developer-starter' ) ), 325 ),
            'templates/template-hosting-saas-home.php' => $this->meta( 'product', 'cloud_storage', __( '云主机SaaS官网（一体式）', 'developer-starter' ), __( '适合云主机、虚拟主机、企业托管、CDN 加速和主机套餐转化的一体式官网。', 'developer-starter' ), array( __( '一体式视觉', 'developer-starter' ), __( 'Hosting', 'developer-starter' ), __( '云服务', 'developer-starter' ) ), 326 ),
            'templates/template-saas-pricing.php' => $this->meta( 'product', 'software', __( 'SaaS 价格对比页', 'developer-starter' ), __( '价格表、套餐对比、权益说明和购买咨询。', 'developer-starter' ), array( __( '价格', 'developer-starter' ), __( 'SaaS', 'developer-starter' ) ), 330 ),
            'templates/template-software-intro.php' => $this->meta( 'product', 'software', __( '软件介绍页', 'developer-starter' ), __( '适合软件能力说明、版本介绍、试用引导和咨询转化。', 'developer-starter' ), array( __( '软件', 'developer-starter' ), __( '介绍', 'developer-starter' ) ), 340 ),
            'templates/template-developer-platform.php' => $this->meta( 'product', 'software', __( '开发者平台官网', 'developer-starter' ), __( '适合 API 平台、开发者工具、文档入口和生态展示。', 'developer-starter' ), array( __( '开发者', 'developer-starter' ), __( '平台', 'developer-starter' ) ), 350 ),
            'templates/template-cybersecurity-brand.php' => $this->meta( 'product', 'technology', __( '网络安全品牌官网', 'developer-starter' ), __( '适合安全服务、风控平台、等保合规和企业安全方案。', 'developer-starter' ), array( __( '安全', 'developer-starter' ), __( '技术', 'developer-starter' ) ), 360 ),
            'templates/template-data-intelligence-bi.php' => $this->meta( 'product', 'software', __( '数据智能/BI 官网', 'developer-starter' ), __( '适合数据分析、BI 平台、经营看板和智能决策产品。', 'developer-starter' ), array( __( '数据', 'developer-starter' ), __( 'BI', 'developer-starter' ) ), 370 ),
            'templates/template-qiling-ai-writing-studio.php' => $this->meta( 'product', 'ai_writing', __( 'AI 写作工作台官网', 'developer-starter' ), __( '适合启灵 AI 写作、AI 助手、内容工具站和企业内容工作台官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( 'AI 写作', 'developer-starter' ) ), 375 ),
            'templates/template-qiling-ai-multilingual-seo.php' => $this->meta( 'product', 'multilingual_seo', __( 'AI 多语言出海官网', 'developer-starter' ), __( '适合启灵AI多语言、多语言 SEO、跨境内容站和外贸出海官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '多语言 SEO', 'developer-starter' ) ), 376 ),
            'templates/template-qiling-doc-ocr-converter.php' => $this->meta( 'product', 'document_ocr', __( '文档解析转换官网', 'developer-starter' ), __( '适合启灵文档解析、PDF 转 Office、文档转 PDF 和办公文件处理工具官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '文档解析', 'developer-starter' ) ), 377 ),
            'templates/template-qiling-image-studio.php' => $this->meta( 'product', 'ai_image', __( 'AI 图像处理官网', 'developer-starter' ), __( '适合启灵图像处理、启灵AI图像和创意图片工具站官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( 'AI 图像', 'developer-starter' ) ), 378 ),
            'templates/template-qiling-wallpaper-gallery.php' => $this->meta( 'content', 'photography', __( '图片素材壁纸站', 'developer-starter' ), __( '适合高清壁纸、摄影作品、设计配图和图片素材聚合站。', 'developer-starter' ), array( __( '图片素材', 'developer-starter' ), __( '高清壁纸', 'developer-starter' ) ), 155 ),
            'templates/template-qiling-cloud-storage-hosting.php' => $this->meta( 'product', 'cloud_storage', __( '云存储图床官网', 'developer-starter' ), __( '适合启灵云存储、启灵图床、对象存储迁移和图片直链工具官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '云存储', 'developer-starter' ) ), 379 ),
            'templates/template-qiling-cloud-canvas.php' => $this->meta( 'product', 'cloud_storage', __( '一体式官网', 'developer-starter' ), __( '适合云服务、SaaS、对象存储和技术产品官网，强调整页连续画布视觉。', 'developer-starter' ), array( __( '一体式视觉', 'developer-starter' ), __( '云服务', 'developer-starter' ) ), 380 ),
            'templates/template-tech-company-integrated.php' => $this->meta( 'product', 'technology', __( '科技公司官网（一体式）', 'developer-starter' ), __( '适合 AI、云计算、数据平台、研发服务和技术咨询公司官网，用连续画布组合首屏、能力、方案、流程、套餐和转化入口。', 'developer-starter' ), array( __( '一体式视觉', 'developer-starter' ), __( '科技公司', 'developer-starter' ) ), 386 ),
            'templates/template-qiling-security-ops.php' => $this->meta( 'product', 'security_ops', __( '安全防护运维官网', 'developer-starter' ), __( '适合启灵安全防护、启灵数据统计、启灵消息推送和 WordPress 安全运维服务官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '安全运维', 'developer-starter' ) ), 381 ),
            'templates/template-qiling-escrow-platform.php' => $this->meta( 'product', 'escrow_trading', __( '担保交易平台官网', 'developer-starter' ), __( '适合启灵担保交易、启灵积分商城和中介托管交易服务官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '担保交易', 'developer-starter' ) ), 382 ),
            'templates/template-qiling-freetask-platform.php' => $this->meta( 'product', 'freelance_task', __( '悬赏任务众包官网', 'developer-starter' ), __( '适合启灵悬赏任务、任务大厅、招标投标和积分托管众包平台官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '悬赏任务', 'developer-starter' ) ), 383 ),
            'templates/template-qiling-friends-matchmaking.php' => $this->meta( 'industry', 'matchmaking', __( '本地相亲婚恋官网', 'developer-starter' ), __( '适合启灵相亲、本地婚恋、资料审核、互赞匹配和受限私信服务官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '相亲婚恋', 'developer-starter' ) ), 384 ),
            'templates/template-qiling-bbs-support-community.php' => $this->meta( 'product', 'community_support', __( '社区工单官网', 'developer-starter' ), __( '适合启灵bbs、产品社区、用户问答、工单支持和客户成功入口。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '社区工单', 'developer-starter' ) ), 385 ),
            'templates/template-data-showcase.php' => $this->meta( 'product', 'technology', __( '数据展示页', 'developer-starter' ), __( '适合数据产品、指标大屏、可视化能力和案例展示。', 'developer-starter' ), array( __( '数据', 'developer-starter' ), __( '展示', 'developer-starter' ) ), 380 ),
            'templates/template-open-source-devtools.php' => $this->meta( 'product', 'software', __( '开源开发工具官网', 'developer-starter' ), __( '适合开源项目、开发工具、SDK 和技术社区入口。', 'developer-starter' ), array( __( '开源', 'developer-starter' ), __( '开发工具', 'developer-starter' ) ), 390 ),
            'templates/template-restaurant.php' => $this->meta( 'industry', 'restaurant', __( '餐饮官网', 'developer-starter' ), __( '餐厅、咖啡馆、品牌餐饮和预约菜单展示。', 'developer-starter' ), array( __( '餐饮', 'developer-starter' ), __( '预约', 'developer-starter' ) ), 410 ),
            'templates/template-dental-clinic.php' => $this->meta( 'industry', 'dental', __( '口腔诊所官网', 'developer-starter' ), __( '诊所介绍、医生团队、项目服务、预约转化。', 'developer-starter' ), array( __( '医疗', 'developer-starter' ), __( '预约', 'developer-starter' ) ), 420 ),
            'templates/template-law-firm.php' => $this->meta( 'industry', 'law', __( '律所官网', 'developer-starter' ), __( '律所品牌、律师团队、业务领域和咨询入口。', 'developer-starter' ), array( __( '法律', 'developer-starter' ), __( '咨询', 'developer-starter' ) ), 430 ),
            'templates/template-renovation-construction.php' => $this->meta( 'industry', 'renovation', __( '装修/工装官网', 'developer-starter' ), __( '装修公司、工装工程、案例展示和报价咨询。', 'developer-starter' ), array( __( '装修', 'developer-starter' ), __( '案例', 'developer-starter' ) ), 440 ),
            'templates/template-wedding-photography.php' => $this->meta( 'industry', 'wedding', __( '婚礼摄影官网', 'developer-starter' ), __( '婚礼摄影、婚礼策划、样片展示和档期咨询。', 'developer-starter' ), array( __( '婚礼', 'developer-starter' ), __( '摄影', 'developer-starter' ) ), 450 ),
            'templates/template-gym-fitness.php' => $this->meta( 'industry', 'fitness', __( '健身房官网', 'developer-starter' ), __( '健身房、私教工作室、团课课程和体验预约。', 'developer-starter' ), array( __( '健身', 'developer-starter' ), __( '预约', 'developer-starter' ) ), 460 ),
            'templates/template-yoga-studio.php' => $this->meta( 'industry', 'wellness', __( '瑜伽馆官网', 'developer-starter' ), __( '瑜伽、普拉提、身心疗愈课程和体验预约。', 'developer-starter' ), array( __( '康养', 'developer-starter' ), __( '课程', 'developer-starter' ) ), 470 ),
            'templates/template-beauty-salon.php' => $this->meta( 'industry', 'beauty', __( '美业门店官网', 'developer-starter' ), __( '美容、美发、美甲、美睫门店服务和预约转化。', 'developer-starter' ), array( __( '美业', 'developer-starter' ), __( '门店', 'developer-starter' ) ), 480 ),
            'templates/template-pet.php' => $this->meta( 'industry', 'pet', __( '宠物服务官网', 'developer-starter' ), __( '宠物医院、宠物店、寄养洗护和服务预约。', 'developer-starter' ), array( __( '宠物', 'developer-starter' ), __( '服务', 'developer-starter' ) ), 490 ),
            'templates/template-homestay.php' => $this->meta( 'industry', 'hospitality', __( '民宿官网', 'developer-starter' ), __( '民宿、客栈、精品酒店和度假空间展示预订。', 'developer-starter' ), array( __( '民宿', 'developer-starter' ), __( '预订', 'developer-starter' ) ), 500 ),
            'templates/template-travel.php' => $this->meta( 'industry', 'travel', __( '旅游线路官网', 'developer-starter' ), __( '旅行社、目的地线路、私家团和旅游咨询。', 'developer-starter' ), array( __( '旅游', 'developer-starter' ), __( '线路', 'developer-starter' ) ), 510 ),
            'templates/template-medical-beauty.php' => $this->meta( 'industry', 'medical_beauty', __( '医美机构官网', 'developer-starter' ), __( '医美机构、皮肤管理、项目介绍和到店咨询。', 'developer-starter' ), array( __( '医美', 'developer-starter' ), __( '咨询', 'developer-starter' ) ), 520 ),
            'templates/template-auto-service.php' => $this->meta( 'industry', 'automotive', __( '汽车服务官网', 'developer-starter' ), __( '汽修保养、汽车美容、门店服务和预约到店。', 'developer-starter' ), array( __( '汽车', 'developer-starter' ), __( '门店', 'developer-starter' ) ), 530 ),
            'templates/template-wellness-center.php' => $this->meta( 'industry', 'wellness', __( '康养中心官网', 'developer-starter' ), __( '康养中心、理疗馆、健康管理和体验预约。', 'developer-starter' ), array( __( '康养', 'developer-starter' ), __( '健康', 'developer-starter' ) ), 540 ),
            'templates/template-health-supplements.php' => $this->meta( 'industry', 'health_supplements', __( '健康保健用品官网', 'developer-starter' ), __( '营养补充、日常保健用品、合规信息和产品咨询。', 'developer-starter' ), array( __( '健康用品', 'developer-starter' ), __( '合规展示', 'developer-starter' ) ), 548 ),
            'templates/template-intimate-wellness.php' => $this->meta( 'industry', 'intimate_wellness', __( '情趣用品商城', 'developer-starter' ), __( '成人情趣用品、材质说明、隐私配送和清洁养护。', 'developer-starter' ), array( __( '18+ 成人适用', 'developer-starter' ), __( '隐私配送', 'developer-starter' ) ), 549 ),
            'templates/template-fashion-brand.php' => $this->meta( 'industry', 'fashion', __( '服装品牌官网', 'developer-starter' ), __( '服装品牌、当季系列、搭配画册、精选单品和品牌服务。', 'developer-starter' ), array( __( '服装时尚', 'developer-starter' ), __( '搭配画册', 'developer-starter' ) ), 550 ),
            'templates/template-chain-store-official.php' => $this->meta( 'industry', 'franchise', __( '连锁门店官网', 'developer-starter' ), __( '连锁品牌、门店矩阵、加盟政策和咨询留资。', 'developer-starter' ), array( __( '连锁', 'developer-starter' ), __( '加盟', 'developer-starter' ) ), 550 ),
            'templates/template-marketing-pr-agency.php' => $this->meta( 'industry', 'b2b', __( '营销公关机构官网', 'developer-starter' ), __( '营销策划、公关传播、品牌服务和案例展示。', 'developer-starter' ), array( __( '营销', 'developer-starter' ), __( 'B2B', 'developer-starter' ) ), 560 ),
            'templates/template-manufacturing-factory.php' => $this->meta( 'industry', 'manufacturing', __( '制造业工厂官网', 'developer-starter' ), __( '制造工厂、设备厂商、零部件供应商和工业品牌官网。', 'developer-starter' ), array( __( '制造业', 'developer-starter' ), __( '询盘', 'developer-starter' ) ), 570 ),
            'templates/template-foreign-trade-b2b.php' => $this->meta( 'industry', 'b2b', __( '外贸 B2B 出海官网', 'developer-starter' ), __( '外贸企业、出口工厂、跨境 B2B 和全球供应商官网。', 'developer-starter' ), array( __( '外贸', 'developer-starter' ), __( 'B2B', 'developer-starter' ) ), 580 ),
            'templates/template-finance-consulting.php' => $this->meta( 'industry', 'finance', __( '金融财税服务官网', 'developer-starter' ), __( '财税公司、金融服务、保险顾问和企业合规咨询官网。', 'developer-starter' ), array( __( '金融', 'developer-starter' ), __( '财税', 'developer-starter' ) ), 590 ),
            'templates/template-accounting-tax-service.php' => $this->meta( 'industry', 'accounting_tax', __( '代理记账/会计税务官网', 'developer-starter' ), __( '适合代理记账、纳税申报、工商注册、税务筹划和财税顾问服务官网。', 'developer-starter' ), array( __( '财税服务', 'developer-starter' ), __( '代理记账', 'developer-starter' ) ), 591 ),
            'templates/template-intellectual-property-service.php' => $this->meta( 'industry', 'intellectual_property', __( '知识产权/商标专利官网', 'developer-starter' ), __( '适合知识产权代理、商标注册、专利申请、版权登记和品牌维权服务官网。', 'developer-starter' ), array( __( '知识产权', 'developer-starter' ), __( '商标专利', 'developer-starter' ) ), 592 ),
            'templates/template-study-abroad-immigration.php' => $this->meta( 'industry', 'study_abroad', __( '留学移民服务官网', 'developer-starter' ), __( '适合留学申请、移民咨询、国际教育、签证服务和海外规划机构官网。', 'developer-starter' ), array( __( '留学移民', 'developer-starter' ), __( '预约评估', 'developer-starter' ) ), 593 ),
            'templates/template-early-childhood-education.php' => $this->meta( 'industry', 'early_childhood', __( '幼儿园/早教中心官网', 'developer-starter' ), __( '适合幼儿园、托育中心、早教中心、亲子成长馆和幼小衔接机构官网。', 'developer-starter' ), array( __( '幼儿早教', 'developer-starter' ), __( '预约参观', 'developer-starter' ) ), 594 ),
            'templates/template-vocational-training-school.php' => $this->meta( 'industry', 'vocational_training', __( '职业培训学校官网', 'developer-starter' ), __( '适合职业培训学校、技能实训、考证培训、就业辅导和企业内训机构官网。', 'developer-starter' ), array( __( '职业培训', 'developer-starter' ), __( '招生转化', 'developer-starter' ) ), 595 ),
            'templates/template-psychological-counseling.php' => $this->meta( 'industry', 'psychological_counseling', __( '心理咨询中心官网', 'developer-starter' ), __( '适合心理咨询中心、心理工作室、家庭咨询、青少年支持和职场心理服务机构官网。', 'developer-starter' ), array( __( '心理咨询', 'developer-starter' ), __( '预约初谈', 'developer-starter' ) ), 596 ),
            'templates/template-senior-care-center.php' => $this->meta( 'industry', 'senior_care', __( '养老院/康养机构官网', 'developer-starter' ), __( '适合养老院、护理院、康养中心、长者公寓和医养结合机构官网。', 'developer-starter' ), array( __( '养老康养', 'developer-starter' ), __( '预约参观', 'developer-starter' ) ), 597 ),
            'templates/template-postpartum-care-center.php' => $this->meta( 'industry', 'postpartum_care', __( '月子中心/产康中心官网', 'developer-starter' ), __( '适合月子中心、产康中心、母婴护理机构和产后修复门店官网。', 'developer-starter' ), array( __( '月子产康', 'developer-starter' ), __( '母婴照护', 'developer-starter' ) ), 598 ),
            'templates/template-architecture-design-studio.php' => $this->meta( 'industry', 'architecture_design', __( '建筑设计事务所官网', 'developer-starter' ), __( '适合建筑设计事务所、规划设计公司、城市更新团队、商业空间设计和建筑顾问机构官网。', 'developer-starter' ), array( __( '建筑设计', 'developer-starter' ), __( '方案作品', 'developer-starter' ) ), 599 ),
            'templates/template-interior-soft-decoration.php' => $this->meta( 'industry', 'interior_design', __( '室内设计/软装设计官网', 'developer-starter' ), __( '适合室内设计工作室、软装设计公司、全案设计品牌、私宅设计和商业空间设计官网。', 'developer-starter' ), array( __( '室内软装', 'developer-starter' ), __( '搭配画册', 'developer-starter' ) ), 601 ),
            'templates/template-landscape-garden-design.php' => $this->meta( 'industry', 'landscape_garden', __( '园林景观/庭院设计官网', 'developer-starter' ), __( '适合园林景观公司、庭院设计工作室、别墅花园营造、商业景观提升和屋顶花园项目官网。', 'developer-starter' ), array( __( '园林景观', 'developer-starter' ), __( '庭院设计', 'developer-starter' ) ), 602 ),
            'templates/template-appliance-repair-service.php' => $this->meta( 'industry', 'appliance_repair', __( '家电维修/水电维修官网', 'developer-starter' ), __( '适合家电维修、水电快修、家居安装、管道疏通和本地快修服务品牌官网。', 'developer-starter' ), array( __( '维修安装', 'developer-starter' ), __( '上门预约', 'developer-starter' ) ), 603 ),
            'templates/template-franchise-investment.php' => $this->meta( 'industry', 'franchise', __( '加盟招商官网', 'developer-starter' ), __( '适合连锁品牌、招商加盟项目、区域代理合作、城市合伙人招募和联营招商官网。', 'developer-starter' ), array( __( '招商加盟', 'developer-starter' ), __( '合作模式', 'developer-starter' ) ), 604 ),
            'templates/template-mcn-live-commerce.php' => $this->meta( 'industry', 'mcn_live_commerce', __( 'MCN/直播电商机构官网', 'developer-starter' ), __( '适合 MCN 机构、直播电商代运营、达人孵化、品牌内容营销和短视频投放团队官网。', 'developer-starter' ), array( __( 'MCN', 'developer-starter' ), __( '直播电商', 'developer-starter' ) ), 605 ),
            'templates/template-conference-event-service.php' => $this->meta( 'industry', 'conference_event_service', __( '会议会展/活动承办官网', 'developer-starter' ), __( '适合会议会展公司、活动策划执行、品牌发布会、路演巡展、展台搭建和企业年会服务商官网。', 'developer-starter' ), array( __( '会议会展', 'developer-starter' ), __( '活动承办', 'developer-starter' ) ), 606 ),
            'templates/template-real-estate-service.php' => $this->meta( 'industry', 'real_estate', __( '房地产服务官网', 'developer-starter' ), __( '楼盘项目、房产中介、商业空间和园区招商官网。', 'developer-starter' ), array( __( '房产', 'developer-starter' ), __( '看房', 'developer-starter' ) ), 600 ),
            'templates/template-local-service-official.php' => $this->meta( 'industry', 'local_service', __( '本地生活服务官网', 'developer-starter' ), __( '家政清洁、维修安装、到家服务和社区服务品牌官网。', 'developer-starter' ), array( __( '本地服务', 'developer-starter' ), __( '预约', 'developer-starter' ) ), 610 ),
            'templates/template-qiling-recycling-official.php' => $this->meta( 'industry', 'recycling', __( '回收服务官网', 'developer-starter' ), __( '数码、家电、奢品、企业闲置和旧物回收服务品牌官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '回收估价', 'developer-starter' ) ), 615 ),
            'templates/template-qiling-housekeeping-official.php' => $this->meta( 'industry', 'housekeeping', __( '家政服务官网', 'developer-starter' ), __( '家政清洁、收纳整理、家电清洗、保姆月嫂和本地上门服务官网。', 'developer-starter' ), array( __( '启灵生态', 'developer-starter' ), __( '家政预约', 'developer-starter' ) ), 616 ),
            'templates/template-healthcare-clinic.php' => $this->meta( 'industry', 'healthcare', __( '医疗健康机构官网', 'developer-starter' ), __( '综合门诊、专科机构、体检中心和健康管理机构官网。', 'developer-starter' ), array( __( '医疗健康', 'developer-starter' ), __( '预约', 'developer-starter' ) ), 620 ),
            'templates/template-logistics-supply-chain.php' => $this->meta( 'industry', 'logistics', __( '物流供应链官网', 'developer-starter' ), __( '物流公司、仓配服务、冷链运输和供应链服务官网。', 'developer-starter' ), array( __( '物流', 'developer-starter' ), __( '仓配', 'developer-starter' ) ), 630 ),
            'templates/template-recruitment-hr-service.php' => $this->meta( 'industry', 'human_resources', __( '人力资源招聘官网', 'developer-starter' ), __( '招聘公司、猎头顾问、灵活用工和人才咨询服务官网。', 'developer-starter' ), array( __( '人力资源', 'developer-starter' ), __( '招聘', 'developer-starter' ) ), 640 ),
            'templates/template-nonprofit-organization.php' => $this->meta( 'industry', 'nonprofit', __( '公益组织官网', 'developer-starter' ), __( '公益组织、基金会、社会服务机构和公益项目官网。', 'developer-starter' ), array( __( '公益', 'developer-starter' ), __( '透明公开', 'developer-starter' ) ), 650 ),
            'templates/template-government-public-service.php' => $this->meta( 'industry', 'government', __( '政务公共机构官网', 'developer-starter' ), __( '公共机构、园区服务中心、协会机构和政务服务类官网。', 'developer-starter' ), array( __( '公共服务', 'developer-starter' ), __( '政务', 'developer-starter' ) ), 660 ),
            'templates/template-agriculture-food.php' => $this->meta( 'industry', 'agriculture_food', __( '农业食品品牌官网', 'developer-starter' ), __( '农产品品牌、食品企业、农场基地和区域公用品牌官网。', 'developer-starter' ), array( __( '农业食品', 'developer-starter' ), __( '溯源', 'developer-starter' ) ), 670 ),
            'templates/template-energy-environment.php' => $this->meta( 'industry', 'energy_environment', __( '能源环保服务官网', 'developer-starter' ), __( '新能源、节能改造、环保工程和碳管理服务官网。', 'developer-starter' ), array( __( '能源环保', 'developer-starter' ), __( '方案', 'developer-starter' ) ), 680 ),
            'templates/template-event-exhibition.php' => $this->meta( 'industry', 'event', __( '会展活动平台招商页', 'developer-starter' ), __( '会展、峰会、发布会、行业大会和活动平台招商页面。', 'developer-starter' ), array( __( '会展', 'developer-starter' ), __( '报名招商', 'developer-starter' ) ), 690 ),
            'templates/template-industrial-park.php' => $this->meta( 'industry', 'industrial_park', __( '产业园区招商官网', 'developer-starter' ), __( '产业园区、科技园、孵化器和产业运营机构官网。', 'developer-starter' ), array( __( '园区', 'developer-starter' ), __( '招商', 'developer-starter' ) ), 700 ),
            'templates/template-property-management.php' => $this->meta( 'industry', 'property_management', __( '物业社区服务官网', 'developer-starter' ), __( '物业公司、社区服务、园区运营和商业物业官网。', 'developer-starter' ), array( __( '物业', 'developer-starter' ), __( '报修', 'developer-starter' ) ), 710 ),
            'templates/template-semiconductor-electronics.php' => $this->meta( 'industry', 'semiconductor_electronics', __( '半导体/电子元器件官网', 'developer-starter' ), __( '适合半导体厂商、电子元器件代理商、工业电子供应商和 IC 方案商官网。', 'developer-starter' ), array( __( '半导体', 'developer-starter' ), __( 'BOM 询盘', 'developer-starter' ) ), 720 ),
            'templates/template-industrial-automation-robotics.php' => $this->meta( 'industry', 'industrial_automation_robotics', __( '工业机器人/自动化官网', 'developer-starter' ), __( '适合工业机器人厂商、自动化设备商、系统集成商和智能工厂服务商官网。', 'developer-starter' ), array( __( '工业机器人', 'developer-starter' ), __( '自动化产线', 'developer-starter' ) ), 721 ),
            'templates/template-medical-device.php' => $this->meta( 'industry', 'medical_device', __( '医疗器械官网', 'developer-starter' ), __( '适合医疗器械生产企业、医用设备品牌、院内设备供应商和渠道经销官网。', 'developer-starter' ), array( __( '医疗器械', 'developer-starter' ), __( '合规资质', 'developer-starter' ) ), 722 ),
            'templates/template-lab-instrument.php' => $this->meta( 'industry', 'lab_instrument', __( '实验室设备/科研仪器官网', 'developer-starter' ), __( '适合实验室设备厂商、科研仪器品牌、检测设备供应商和高校/科研院所采购官网。', 'developer-starter' ), array( __( '实验室设备', 'developer-starter' ), __( '科研仪器', 'developer-starter' ) ), 723 ),
            'templates/template-solar-storage-equipment.php' => $this->meta( 'industry', 'solar_storage_equipment', __( '光伏储能设备官网', 'developer-starter' ), __( '适合光伏组件厂商、储能系统集成商、逆变器品牌和工商业能源项目服务商官网。', 'developer-starter' ), array( __( '光伏储能', 'developer-starter' ), __( '工商业项目', 'developer-starter' ) ), 724 ),
            'templates/template-water-treatment-environmental.php' => $this->meta( 'industry', 'water_treatment_environmental', __( '水处理/环保工程官网', 'developer-starter' ), __( '适合水处理设备商、环保工程公司、污水处理运营商和工业废水治理团队官网。', 'developer-starter' ), array( __( '水处理', 'developer-starter' ), __( '环保工程', 'developer-starter' ) ), 725 ),
            'templates/template-cross-border-ecommerce-service.php' => $this->meta( 'industry', 'cross_border_ecommerce_service', __( '跨境电商服务官网', 'developer-starter' ), __( '适合跨境电商代运营、平台入驻、独立站服务、海外广告投放和品牌出海咨询团队官网。', 'developer-starter' ), array( __( '跨境电商', 'developer-starter' ), __( '品牌出海', 'developer-starter' ) ), 726 ),
            'templates/template-overseas-warehouse-supply-chain.php' => $this->meta( 'industry', 'overseas_warehouse_supply_chain', __( '海外仓/跨境供应链官网', 'developer-starter' ), __( '适合海外仓服务商、跨境物流公司、FBA 头程团队、跨境 3PL 和退换货处理中心官网。', 'developer-starter' ), array( __( '海外仓', 'developer-starter' ), __( '跨境供应链', 'developer-starter' ) ), 727 ),
            'templates/template-enterprise-software-integrator.php' => $this->meta( 'industry', 'enterprise_software_integrator', __( '企业软件/SI 集成商官网', 'developer-starter' ), __( '适合企业软件公司、SI 系统集成商、数字化转型服务商、ERP/CRM/OA 实施团队和数据中台服务商官网。', 'developer-starter' ), array( __( '企业软件', 'developer-starter' ), __( '系统集成', 'developer-starter' ) ), 728 ),
            'templates/template-ai-agent-enterprise.php' => $this->meta( 'industry', 'ai_agent_enterprise', __( '企业 AI Agent 官网', 'developer-starter' ), __( '适合企业 AI Agent 平台、智能助手、知识库问答、流程自动化和私有化大模型应用官网。', 'developer-starter' ), array( __( 'AI Agent', 'developer-starter' ), __( '企业智能体', 'developer-starter' ) ), 729 ),
            'templates/template-personal-ip-home.php' => $this->meta( 'personal', 'personal', __( '个人 IP 主页', 'developer-starter' ), __( '适合创始人、顾问、讲师、博主和自由职业个人品牌。', 'developer-starter' ), array( __( '个人品牌', 'developer-starter' ), __( '作品', 'developer-starter' ) ), 510 ),
        );
    }

    /**
     * Build metadata item.
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
     * Get category choices.
     *
     * @param array<int,array<string,mixed>> $catalog Catalog.
     * @return array<string,string>
     */
    private function get_category_choices( $catalog ) {
        $choices = array( 'all' => __( '全部分组', 'developer-starter' ) );
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
     * Get industry choices.
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
        return array( 'all' => __( '全部行业', 'developer-starter' ) ) + $choices;
    }

    /**
     * Catalog stats.
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
     * Build industry coverage from the official catalog.
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
     * Render the standard industry coverage matrix.
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
                    <h2 id="qtc-coverage-title"><?php esc_html_e( '行业模板覆盖', 'developer-starter' ); ?></h2>
                    <p><?php esc_html_e( '按行业查看官方模板覆盖情况。已有模板的行业可直接点击筛选，暂无模板的行业会清晰标注。', 'developer-starter' ); ?></p>
                </div>
                <div class="qtc-coverage-meter" aria-label="<?php esc_attr_e( '行业覆盖进度', 'developer-starter' ); ?>">
                    <strong><?php echo esc_html( sprintf( '%s%%', number_format_i18n( $percent ) ) ); ?></strong>
                    <span><?php echo esc_html( sprintf( __( '已覆盖 %1$s / %2$s，暂无模板 %3$s', 'developer-starter' ), number_format_i18n( $covered ), number_format_i18n( $total ), number_format_i18n( $missing ) ) ); ?></span>
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
                            <em><?php echo esc_html( $count > 0 ? sprintf( __( '%s 个模板', 'developer-starter' ), number_format_i18n( $count ) ) : __( '暂无模板', 'developer-starter' ) ); ?></em>
                        </span>
                        <?php if ( ! empty( $templates ) ) : ?>
                            <span class="qtc-coverage-sample"><?php echo esc_html( implode( ' / ', $templates ) ); ?></span>
                        <?php else : ?>
                            <span class="qtc-coverage-sample"><?php esc_html_e( '已建行业标准，尚未提供官方模板', 'developer-starter' ); ?></span>
                        <?php endif; ?>
                        <?php if ( $created_pages > 0 ) : ?>
                            <span class="qtc-coverage-usage"><?php echo esc_html( sprintf( __( '使用中 %s', 'developer-starter' ), number_format_i18n( $created_pages ) ) ); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Get pages already using official templates.
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
                'title'        => '' !== $title ? $title : __( '(无标题)', 'developer-starter' ),
                'status'       => $status,
                'status_label' => $status_label,
                'edit_url'     => $edit_url ? $edit_url : '',
                'view_url'     => $view_url ? $view_url : '',
            );
        }

        return $usage;
    }

    /**
     * Check template exists.
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
     * Get template label.
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
     * Read Template Name header from theme file.
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
     * Category label.
     *
     * @param string $category Category.
     * @return string
     */
    private function get_category_label( $category ) {
        $labels = array(
            'corporate'  => __( '官网通用', 'developer-starter' ),
            'content'    => __( '博客/杂志/内容', 'developer-starter' ),
            'resource'   => __( '数字资源', 'developer-starter' ),
            'product'    => __( '产品/SaaS/技术', 'developer-starter' ),
            'conversion' => __( '落地页/转化', 'developer-starter' ),
            'industry'   => __( '行业官网', 'developer-starter' ),
            'personal'   => __( '个人品牌', 'developer-starter' ),
        );

        return isset( $labels[ $category ] ) ? $labels[ $category ] : __( '其他', 'developer-starter' );
    }

    /**
     * Category order base.
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
     * Standard industry definitions.
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
            'general'       => array( 'label' => __( '通用', 'developer-starter' ), 'group' => 'general' ),
            'enterprise'    => array( 'label' => __( '企业官网', 'developer-starter' ), 'group' => 'business' ),
            'technology'    => array( 'label' => __( '科技', 'developer-starter' ), 'group' => 'technology' ),
            'software'      => array( 'label' => __( '软件', 'developer-starter' ), 'group' => 'technology' ),
            'ai_writing'    => array( 'label' => __( 'AI 写作', 'developer-starter' ), 'group' => 'technology' ),
            'multilingual_seo' => array( 'label' => __( '多语言 SEO', 'developer-starter' ), 'group' => 'technology' ),
            'document_ocr'  => array( 'label' => __( '文档 OCR', 'developer-starter' ), 'group' => 'technology' ),
            'ai_image'      => array( 'label' => __( 'AI 图像', 'developer-starter' ), 'group' => 'technology' ),
            'cloud_storage' => array( 'label' => __( '云存储图床', 'developer-starter' ), 'group' => 'technology' ),
            'security_ops'  => array( 'label' => __( '安全运维', 'developer-starter' ), 'group' => 'technology' ),
            'escrow_trading' => array( 'label' => __( '担保交易', 'developer-starter' ), 'group' => 'technology' ),
            'freelance_task' => array( 'label' => __( '悬赏众包', 'developer-starter' ), 'group' => 'technology' ),
            'matchmaking'   => array( 'label' => __( '相亲婚恋', 'developer-starter' ), 'group' => 'local' ),
            'community_support' => array( 'label' => __( '社区工单', 'developer-starter' ), 'group' => 'technology' ),
            'education'     => array( 'label' => __( '教育培训', 'developer-starter' ), 'group' => 'service' ),
            'study_abroad'   => array( 'label' => __( '留学移民', 'developer-starter' ), 'group' => 'education' ),
            'early_childhood' => array( 'label' => __( '幼儿早教', 'developer-starter' ), 'group' => 'education' ),
            'vocational_training' => array( 'label' => __( '职业培训', 'developer-starter' ), 'group' => 'education' ),
            'psychological_counseling' => array( 'label' => __( '心理咨询', 'developer-starter' ), 'group' => 'health' ),
            'senior_care'   => array( 'label' => __( '养老康养', 'developer-starter' ), 'group' => 'health' ),
            'postpartum_care' => array( 'label' => __( '月子产康', 'developer-starter' ), 'group' => 'health' ),
            'architecture_design' => array( 'label' => __( '建筑设计', 'developer-starter' ), 'group' => 'property' ),
            'interior_design' => array( 'label' => __( '室内软装', 'developer-starter' ), 'group' => 'property' ),
            'landscape_garden' => array( 'label' => __( '园林景观', 'developer-starter' ), 'group' => 'property' ),
            'appliance_repair' => array( 'label' => __( '维修安装', 'developer-starter' ), 'group' => 'local' ),
            'franchise'      => array( 'label' => __( '招商加盟', 'developer-starter' ), 'group' => 'business' ),
            'mcn_live_commerce' => array( 'label' => __( 'MCN/直播电商', 'developer-starter' ), 'group' => 'commerce' ),
            'conference_event_service' => array( 'label' => __( '会议会展/活动承办', 'developer-starter' ), 'group' => 'business' ),
            'semiconductor_electronics' => array( 'label' => __( '半导体/电子元器件', 'developer-starter' ), 'group' => 'business' ),
            'industrial_automation_robotics' => array( 'label' => __( '工业机器人/自动化', 'developer-starter' ), 'group' => 'business' ),
            'medical_device' => array( 'label' => __( '医疗器械', 'developer-starter' ), 'group' => 'health' ),
            'lab_instrument' => array( 'label' => __( '实验室设备/科研仪器', 'developer-starter' ), 'group' => 'business' ),
            'solar_storage_equipment' => array( 'label' => __( '光伏储能设备', 'developer-starter' ), 'group' => 'business' ),
            'water_treatment_environmental' => array( 'label' => __( '水处理/环保工程', 'developer-starter' ), 'group' => 'business' ),
            'cross_border_ecommerce_service' => array( 'label' => __( '跨境电商服务', 'developer-starter' ), 'group' => 'commerce' ),
            'overseas_warehouse_supply_chain' => array( 'label' => __( '海外仓/跨境供应链', 'developer-starter' ), 'group' => 'business' ),
            'enterprise_software_integrator' => array( 'label' => __( '企业软件/SI 集成商', 'developer-starter' ), 'group' => 'technology' ),
            'ai_agent_enterprise' => array( 'label' => __( '企业 AI Agent', 'developer-starter' ), 'group' => 'technology' ),
            'accounting_tax' => array( 'label' => __( '代理记账/会计税务', 'developer-starter' ), 'group' => 'professional' ),
            'intellectual_property' => array( 'label' => __( '知识产权', 'developer-starter' ), 'group' => 'professional' ),
            'recycling'     => array( 'label' => __( '回收服务', 'developer-starter' ), 'group' => 'local' ),
            'housekeeping'  => array( 'label' => __( '家政服务', 'developer-starter' ), 'group' => 'local' ),
            'local_service' => array( 'label' => __( '本地服务', 'developer-starter' ), 'group' => 'local' ),
        );
    }

    /**
     * Standard industry choices for filters.
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
     * Industry label.
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
     * Normalize an industry key through the shared standard.
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
     * Safely read a scalar request value.
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
     * Render scoped CSS.
     *
     * @return void
     */
    private function render_styles() {
        ?>
        <style>
            .wrap.qiling-template-center {
                max-width: 1600px;
                margin-right: 24px;
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
            .qtc-filter-bar {
                display: flex;
                flex-wrap: wrap;
                align-items: end;
                gap: 12px;
                margin: 16px 0;
                padding: 16px;
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
            }
            .qtc-filter-bar label,
            .qtc-create-form label {
                display: grid;
                gap: 5px;
                min-width: 0;
                color: #50575e;
                font-size: 12px;
            }
            .qtc-filter-bar label:not(.qtc-search) {
                flex: 0 1 190px;
            }
            .qtc-filter-bar .qtc-search {
                flex: 1 1 280px;
                min-width: 220px;
            }
            .qtc-filter-bar select,
            .qtc-filter-bar input[type="search"],
            .qtc-create-form input,
            .qtc-create-form select {
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }
            .qtc-filter-bar .button {
                min-height: 32px;
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
        </style>
        <?php
    }

    /**
     * Render scoped scripts.
     *
     * @return void
     */
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
        </script>
        <?php
    }
}
