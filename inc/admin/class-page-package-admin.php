<?php
/**
 * 多页面数据包后台入口
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

use Developer_Starter\Core\Page_Package_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Admin {

    /**
     * 页面数据包管理器。
     *
     * @var Page_Package_Manager
     */
    private $manager;

    /**
     * 后台菜单 slug。
     *
     * @var string
     */
    private $page_slug = 'developer-starter-site-packages';

    public function __construct() {
        $this->manager = new Page_Package_Manager();
        add_action( 'admin_menu', array( $this, 'register_submenu_page' ), 20 );
    }

    /**
     * 注册后台子菜单。
     *
     * @return void
     */
    public function register_submenu_page() {
        add_submenu_page(
            'developer-starter-settings',
            __( '页面包/整站包', 'developer-starter' ),
            __( '页面包/整站包', 'developer-starter' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'render_page' )
        );
    }

    /**
     * 渲染后台页面。
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '权限不足', 'developer-starter' ) );
        }

        $this->manager->cleanup_expired_preview_pages();

        $state = $this->get_default_state();
        $request_method = isset( $_SERVER['REQUEST_METHOD'] )
            ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) )
            : 'GET';
        if ( 'POST' === $request_method ) {
            $state = $this->handle_post_request();
        }

        $reserved_pages       = $this->manager->get_reserved_page_definitions();
        $conflict_strategies  = $this->manager->get_conflict_strategies();
        $package_scope_choices = $this->manager->get_package_scope_choices();
        $exportable_pages     = $this->manager->get_exportable_pages();
        $import_history       = $this->manager->get_import_history( 20 );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '页面包/整站包', 'developer-starter' ); ?></h1>
            <p class="description">
                <?php esc_html_e( '用于本地导入和导出启灵模块装修页、全局样式、内容模型、导航菜单和站点基础信息。', 'developer-starter' ); ?>
            </p>

            <style>
                .ds-package-panel {
                    margin-top: 18px;
                    padding: 20px 22px;
                    background: #fff;
                    border: 1px solid #dcdcde;
                    border-radius: 8px;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
                }
                .ds-package-panel h2 {
                    margin: 0 0 12px;
                    font-size: 18px;
                }
                .ds-package-panel h3 {
                    margin: 18px 0 10px;
                }
                .ds-package-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                    gap: 12px;
                    margin-top: 12px;
                }
                .ds-package-card {
                    padding: 12px 14px;
                    background: #f6f7f7;
                    border-radius: 6px;
                    border: 1px solid #e2e4e7;
                }
                .ds-package-card strong {
                    display: block;
                    margin-bottom: 6px;
                }
                .ds-package-upload textarea,
                .ds-package-export-json {
                    width: 100%;
                    min-height: 220px;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
                    font-size: 12px;
                    line-height: 1.6;
                }
                .ds-package-export-json {
                    min-height: 320px;
                }
                .ds-package-actions {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    gap: 10px;
                    margin-top: 14px;
                }
                .ds-package-note {
                    margin-top: 12px;
                    padding: 12px 14px;
                    background: #f0f6fc;
                    border-left: 4px solid #2271b1;
                }
                .ds-package-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 12px;
                }
                .ds-package-table th,
                .ds-package-table td {
                    padding: 10px 12px;
                    border: 1px solid #e2e4e7;
                    vertical-align: top;
                    text-align: left;
                }
                .ds-package-table th {
                    background: #f6f7f7;
                }
                .ds-status {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 999px;
                    font-size: 12px;
                    font-weight: 600;
                    line-height: 1.4;
                }
                .ds-status-create {
                    background: #e7f8ee;
                    color: #0a7a32;
                }
                .ds-status-update {
                    background: #e8f1ff;
                    color: #1859bd;
                }
                .ds-status-skip {
                    background: #fff3cd;
                    color: #8a5a00;
                }
                .ds-status-blocked,
                .ds-status-error {
                    background: #fde8e8;
                    color: #b42318;
                }
                .ds-status-ready {
                    background: #eef4ff;
                    color: #2459d6;
                }
                .ds-package-list {
                    margin: 0;
                    padding-left: 18px;
                }
                .ds-package-list li {
                    margin: 0 0 4px;
                }
                .ds-package-hidden {
                    display: none;
                }
                .ds-package-snippet {
                    padding: 14px 16px;
                    background: #111827;
                    color: #f9fafb;
                    border-radius: 8px;
                    overflow: auto;
                    font-size: 12px;
                    line-height: 1.6;
                }
                .ds-package-radio-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                    gap: 10px;
                    margin-top: 8px;
                }
                .ds-package-option {
                    display: block;
                    padding: 12px 14px;
                    border: 1px solid #dcdcde;
                    border-radius: 8px;
                    background: #fff;
                }
                .ds-package-option strong {
                    display: block;
                    margin: 4px 0 6px;
                }
                .ds-package-option input {
                    margin-right: 6px;
                }
                .ds-package-badges {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                    margin-top: 8px;
                }
                .ds-package-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 4px 10px;
                    background: #f0f6fc;
                    border: 1px solid #cfe2ff;
                    border-radius: 999px;
                    font-size: 12px;
                }
                .ds-package-cover {
                    max-width: 320px;
                    border-radius: 8px;
                    border: 1px solid #dcdcde;
                    margin-top: 10px;
                }
                .ds-package-page-picker {
                    max-height: 320px;
                    overflow: auto;
                    padding: 12px;
                    border: 1px solid #dcdcde;
                    border-radius: 8px;
                    background: #f6f7f7;
                }
                .ds-package-page-picker label {
                    display: flex;
                    align-items: flex-start;
                    gap: 8px;
                    padding: 8px 0;
                    border-bottom: 1px solid #e2e4e7;
                }
                .ds-package-page-picker label:last-child {
                    border-bottom: 0;
                }
                .ds-package-page-meta {
                    color: #50575e;
                    font-size: 12px;
                    line-height: 1.5;
                }
                .ds-package-download {
                    margin-top: 12px;
                }
                .ds-package-homepage-flag {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    margin-top: 6px;
                    padding: 3px 10px;
                    border-radius: 999px;
                    background: #fff3cd;
                    border: 1px solid #f7da8b;
                    color: #8a5a00;
                    font-size: 12px;
                    font-weight: 600;
                }
                .ds-package-inline-form {
                    display: inline;
                }
                .ds-package-run-id code {
                    font-size: 11px;
                    word-break: break-all;
                }
            </style>

            <?php if ( ! empty( $state['request_error'] ) ) : ?>
                <div class="notice notice-error"><p><?php echo esc_html( $state['request_error'] ); ?></p></div>
            <?php endif; ?>
            <?php if ( ! empty( $state['request_notice'] ) ) : ?>
                <div class="notice notice-success"><p><?php echo esc_html( $state['request_notice'] ); ?></p></div>
            <?php endif; ?>

            <?php if ( ! empty( $state['import_result'] ) && is_array( $state['import_result'] ) ) : ?>
                <?php $this->render_import_result( $state['import_result'] ); ?>
            <?php endif; ?>

            <?php if ( ! empty( $state['preview_result'] ) && is_array( $state['preview_result'] ) ) : ?>
                <?php $this->render_preview_result( $state['preview_result'] ); ?>
            <?php endif; ?>

            <?php if ( ! empty( $state['export_result'] ) && is_array( $state['export_result'] ) ) : ?>
                <?php $this->render_export_result( $state['export_result'] ); ?>
            <?php endif; ?>

            <div class="ds-package-panel ds-package-upload">
                <h2><?php esc_html_e( '导入页面包/整站包', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '可以上传 JSON 文件，也可以直接粘贴本地包内容。这里只处理启灵主题自己的模块装修页；导入会按“先预检 → 生成临时预览 → 执行导入”的流程进行，整站设置默认不会写入，必须在预检后手动勾选。', 'developer-starter' ); ?></p>

                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'developer_starter_site_package_action', 'developer_starter_site_package_nonce' ); ?>
                    <input type="hidden" name="developer_starter_site_package_action" value="preview" />

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-file"><?php esc_html_e( '上传 JSON 文件', 'developer-starter' ); ?></label></th>
                                <td>
                                    <input type="file" id="developer-starter-site-package-file" name="developer_starter_site_package_file" accept=".json,application/json" />
                                    <p class="description"><?php echo esc_html( sprintf( __( '单个数据包最大 %s。', 'developer-starter' ), size_format( Page_Package_Manager::MAX_PACKAGE_BYTES ) ) ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-json"><?php esc_html_e( '或直接粘贴 JSON', 'developer-starter' ); ?></label></th>
                                <td>
                                    <textarea id="developer-starter-site-package-json" name="developer_starter_site_package_json" placeholder="<?php esc_attr_e( '把页面包或整站包 JSON 粘贴到这里…', 'developer-starter' ); ?>"><?php echo esc_textarea( $state['raw_json'] ); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'URL 冲突策略', 'developer-starter' ); ?></th>
                                <td>
                                    <div class="ds-package-radio-grid">
                                        <?php foreach ( $conflict_strategies as $strategy_key => $strategy_config ) : ?>
                                            <label class="ds-package-option">
                                                <span>
                                                    <input type="radio" name="developer_starter_site_package_conflict_strategy" value="<?php echo esc_attr( $strategy_key ); ?>" <?php checked( $state['selected_conflict_strategy'], $strategy_key ); ?> />
                                                    <strong><?php echo esc_html( $strategy_config['label'] ); ?></strong>
                                                    <span class="description"><?php echo esc_html( $strategy_config['description'] ); ?></span>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="ds-package-actions">
                        <button type="submit" class="button button-primary"><?php esc_html_e( '预检数据包', 'developer-starter' ); ?></button>
                        <span class="description"><?php esc_html_e( '预检会检查模板、模块、系统保留页、URL 冲突、站点设置和占位链接是否可解析。', 'developer-starter' ); ?></span>
                    </div>
                </form>
            </div>

            <div class="ds-package-panel">
                <h2><?php esc_html_e( '本地包能力说明', 'developer-starter' ); ?></h2>
                <div class="ds-package-grid">
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '当前支持', 'developer-starter' ); ?></strong>
                        <div><?php esc_html_e( '页面包支持启灵模块装修页；整站包可额外携带全局样式、内容模型、菜单、首页/文章页映射和站点标题。', 'developer-starter' ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '当前不支持', 'developer-starter' ); ?></strong>
                        <div><?php esc_html_e( '登录、注册、找回密码、个人中心等系统保留页会被拦截；第三方插件页面是否可迁移取决于插件自身能力。', 'developer-starter' ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '冲突策略', 'developer-starter' ); ?></strong>
                        <div><?php esc_html_e( '支持跳过、自动生成新 URL、仅更新同一数据包历史页面 3 种策略。', 'developer-starter' ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '占位链接', 'developer-starter' ); ?></strong>
                        <div><?php esc_html_e( '支持 qiling://page/页面标识 和 qiling://system/login 这类引用；可解析的占位链接会自动转成真实链接，未解析项会保留原样并提示。', 'developer-starter' ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '包信息', 'developer-starter' ); ?></strong>
                        <div><?php esc_html_e( '支持作者、描述、封面、分类、标签、依赖插件等元信息预览。', 'developer-starter' ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '本地导出', 'developer-starter' ); ?></strong>
                        <div><?php esc_html_e( '支持从当前主题页面中勾选多个装修页，导出页面包；也可选择整站包并附带全局配置清单。', 'developer-starter' ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '明确不包含', 'developer-starter' ); ?></strong>
                        <div><?php esc_html_e( '不包含装修市场、远程上架、购买、在线分发或第三方模板源。', 'developer-starter' ); ?></div>
                    </div>
                </div>

                <h3><?php esc_html_e( '系统保留页', 'developer-starter' ); ?></h3>
                <table class="ds-package-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '页面用途', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '保留 URL', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '保留模板', 'developer-starter' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $reserved_pages as $reserved_page ) : ?>
                            <tr>
                                <td><?php echo esc_html( $reserved_page['label'] ); ?></td>
                                <td><code><?php echo esc_html( $reserved_page['slug'] ); ?></code></td>
                                <td><code><?php echo esc_html( $reserved_page['template'] ); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="ds-package-panel">
                <h2><?php esc_html_e( '导出页面包/整站包', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '导出启灵主题模块装修页；整站包可附带全局样式、内容模型、菜单和站点基础信息。', 'developer-starter' ); ?></p>

                <form method="post">
                    <?php wp_nonce_field( 'developer_starter_site_package_action', 'developer_starter_site_package_nonce' ); ?>
                    <input type="hidden" name="developer_starter_site_package_action" value="export" />

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-title"><?php esc_html_e( '数据包标题', 'developer-starter' ); ?></label></th>
                                <td><input type="text" class="regular-text" id="developer-starter-site-package-export-title" name="developer_starter_site_package_export_title" value="<?php echo esc_attr( $state['export_form']['title'] ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( '导出类型', 'developer-starter' ); ?></th>
                                <td>
                                    <div class="ds-package-radio-grid">
                                        <?php foreach ( $package_scope_choices as $scope_key => $scope_config ) : ?>
                                            <label class="ds-package-option">
                                                <span>
                                                    <input type="radio" name="developer_starter_site_package_export_scope" value="<?php echo esc_attr( $scope_key ); ?>" <?php checked( $state['export_form']['scope'], $scope_key ); ?> />
                                                    <strong><?php echo esc_html( $scope_config['label'] ); ?></strong>
                                                    <span class="description"><?php echo esc_html( $scope_config['description'] ); ?></span>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-id"><?php esc_html_e( '数据包 ID', 'developer-starter' ); ?></label></th>
                                <td><input type="text" class="regular-text" id="developer-starter-site-package-export-id" name="developer_starter_site_package_export_package_id" value="<?php echo esc_attr( $state['export_form']['package_id'] ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-min-version"><?php esc_html_e( '最低主题版本', 'developer-starter' ); ?></label></th>
                                <td><input type="text" class="regular-text" id="developer-starter-site-package-export-min-version" name="developer_starter_site_package_export_min_theme_version" value="<?php echo esc_attr( $state['export_form']['min_theme_version'] ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-author"><?php esc_html_e( '作者名称', 'developer-starter' ); ?></label></th>
                                <td><input type="text" class="regular-text" id="developer-starter-site-package-export-author" name="developer_starter_site_package_export_author_name" value="<?php echo esc_attr( $state['export_form']['author_name'] ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-author-url"><?php esc_html_e( '作者链接', 'developer-starter' ); ?></label></th>
                                <td><input type="url" class="regular-text" id="developer-starter-site-package-export-author-url" name="developer_starter_site_package_export_author_url" value="<?php echo esc_attr( $state['export_form']['author_url'] ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-cover"><?php esc_html_e( '封面图 URL', 'developer-starter' ); ?></label></th>
                                <td><input type="url" class="regular-text" id="developer-starter-site-package-export-cover" name="developer_starter_site_package_export_cover" value="<?php echo esc_attr( $state['export_form']['cover'] ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-categories"><?php esc_html_e( '分类标签', 'developer-starter' ); ?></label></th>
                                <td><input type="text" class="regular-text" id="developer-starter-site-package-export-categories" name="developer_starter_site_package_export_categories" value="<?php echo esc_attr( $state['export_form']['categories'] ); ?>" placeholder="<?php esc_attr_e( '用英文逗号分隔', 'developer-starter' ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-tags"><?php esc_html_e( '关键词标签', 'developer-starter' ); ?></label></th>
                                <td><input type="text" class="regular-text" id="developer-starter-site-package-export-tags" name="developer_starter_site_package_export_tags" value="<?php echo esc_attr( $state['export_form']['tags'] ); ?>" placeholder="<?php esc_attr_e( '用英文逗号分隔', 'developer-starter' ); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-dependencies"><?php esc_html_e( '依赖插件', 'developer-starter' ); ?></label></th>
                                <td>
                                    <textarea class="large-text" rows="4" id="developer-starter-site-package-export-dependencies" name="developer_starter_site_package_export_dependency_plugins" placeholder="<?php esc_attr_e( "每行一个插件标识，例如 qiling-forms/qiling-forms.php", 'developer-starter' ); ?>"><?php echo esc_textarea( $state['export_form']['dependency_plugins'] ); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="developer-starter-site-package-export-description"><?php esc_html_e( '数据包描述', 'developer-starter' ); ?></label></th>
                                <td><textarea class="large-text" rows="4" id="developer-starter-site-package-export-description" name="developer_starter_site_package_export_description"><?php echo esc_textarea( $state['export_form']['description'] ); ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( '整站包附加内容', 'developer-starter' ); ?></th>
                                <td>
                                    <label><input type="checkbox" name="developer_starter_site_package_export_include_design_system" value="1" <?php checked( ! empty( $state['export_form']['include_design_system'] ) ); ?> /> <?php esc_html_e( '全局样式', 'developer-starter' ); ?></label><br />
                                    <label><input type="checkbox" name="developer_starter_site_package_export_include_content_models" value="1" <?php checked( ! empty( $state['export_form']['include_content_models'] ) ); ?> /> <?php esc_html_e( '内容模型中心配置', 'developer-starter' ); ?></label><br />
                                    <label><input type="checkbox" name="developer_starter_site_package_export_include_navigation" value="1" <?php checked( ! empty( $state['export_form']['include_navigation'] ) ); ?> /> <?php esc_html_e( '当前菜单结构（仅导出所选页面和自定义链接）', 'developer-starter' ); ?></label><br />
                                    <label><input type="checkbox" name="developer_starter_site_package_export_include_site_identity" value="1" <?php checked( ! empty( $state['export_form']['include_site_identity'] ) ); ?> /> <?php esc_html_e( '站点标题与副标题', 'developer-starter' ); ?></label><br />
                                    <label><input type="checkbox" name="developer_starter_site_package_export_include_site_assets" value="1" <?php checked( ! empty( $state['export_form']['include_site_assets'] ) ); ?> /> <?php esc_html_e( '主题资源清单', 'developer-starter' ); ?></label>
                                    <p class="description"><?php esc_html_e( '这些内容只在导出类型为“整站包”时生效；站点设置仍需手动勾选后写入。', 'developer-starter' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( '选择页面', 'developer-starter' ); ?></th>
                                <td>
                                    <div class="ds-package-page-picker">
                                        <?php if ( empty( $exportable_pages ) ) : ?>
                                            <p><?php esc_html_e( '当前没有可导出的启灵模块装修页。', 'developer-starter' ); ?></p>
                                        <?php else : ?>
                                            <?php foreach ( $exportable_pages as $page_item ) : ?>
                                                <label>
                                                    <input type="checkbox" name="developer_starter_site_package_export_pages[]" value="<?php echo esc_attr( (string) $page_item['id'] ); ?>" <?php checked( in_array( (int) $page_item['id'], $state['export_form']['selected_pages'], true ) ); ?> />
                                                    <span>
                                                        <strong><?php echo esc_html( $page_item['title'] ); ?></strong>
                                                        <span class="ds-package-page-meta">
                                                            <code><?php echo esc_html( $page_item['slug'] ); ?></code>
                                                            · <?php echo esc_html( $page_item['template_label'] ); ?>
                                                            · <?php echo esc_html( $page_item['post_status'] ); ?>
                                                            · <?php echo esc_html( sprintf( __( '%d 个模块', 'developer-starter' ), isset( $page_item['module_count'] ) ? absint( $page_item['module_count'] ) : 0 ) ); ?>
                                                        </span>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="ds-package-actions">
                        <button type="submit" class="button button-secondary"><?php esc_html_e( '生成本地包 JSON', 'developer-starter' ); ?></button>
                        <span class="description"><?php esc_html_e( '导出时会自动过滤系统页和非模块页，并尽量保留已存在的数据包页面标识。', 'developer-starter' ); ?></span>
                    </div>
                </form>
            </div>

            <div class="ds-package-panel">
                <h2><?php esc_html_e( '推荐 JSON 结构', 'developer-starter' ); ?></h2>
                <div class="ds-package-snippet"><pre><?php echo esc_html( $this->get_example_json_snippet() ); ?></pre></div>
            </div>

            <?php if ( ! empty( $state['analysis'] ) && is_array( $state['analysis'] ) ) : ?>
                <?php $this->render_analysis_result( $state['analysis'], $state['raw_json'], $state['selected_conflict_strategy'], $state['preview_binding_token'] ); ?>
            <?php endif; ?>

            <?php $this->render_import_history_panel( $import_history ); ?>
        </div>
        <?php
    }

    /**
     * 获取后台状态默认值。
     *
     * @return array<string,mixed>
     */
    private function get_default_state() {
        return array(
            'raw_json'                   => '',
            'analysis'                   => null,
            'import_result'              => null,
            'preview_result'             => null,
            'export_result'              => null,
            'request_error'              => null,
            'request_notice'             => null,
            'preview_binding_token'      => '',
            'selected_conflict_strategy' => Page_Package_Manager::CONFLICT_STRATEGY_SKIP,
            'export_form'                => $this->get_default_export_form(),
        );
    }

    /**
     * 获取导出表单默认值。
     *
     * @return array<string,mixed>
     */
    private function get_default_export_form() {
        return array(
            'title'                  => __( '未命名页面包', 'developer-starter' ),
            'scope'                  => Page_Package_Manager::PACKAGE_SCOPE_PAGE,
            'package_id'             => '',
            'min_theme_version'      => defined( 'DEVELOPER_STARTER_VERSION' ) ? (string) DEVELOPER_STARTER_VERSION : '',
            'author_name'            => '',
            'author_url'             => '',
            'description'            => '',
            'cover'                  => '',
            'categories'             => '',
            'tags'                   => '',
            'dependency_plugins'     => '',
            'include_design_system'  => false,
            'include_content_models' => false,
            'include_navigation'     => false,
            'include_site_identity'  => false,
            'include_site_assets'    => false,
            'selected_pages'         => array(),
        );
    }

    /**
     * 处理后台表单提交。
     *
     * @return array<string,mixed>
     */
    private function handle_post_request() {
        $state = $this->get_default_state();

        check_admin_referer( 'developer_starter_site_package_action', 'developer_starter_site_package_nonce' );

        $action = isset( $_POST['developer_starter_site_package_action'] ) ? sanitize_key( wp_unslash( (string) $_POST['developer_starter_site_package_action'] ) ) : '';
        if ( ! in_array( $action, array( 'preview', 'import', 'create_preview', 'export', 'download_export', 'clear_preview', 'rollback_import' ), true ) ) {
            $state['request_error'] = __( '请求动作无效。', 'developer-starter' );
            return $state;
        }

        if ( 'download_export' === $action ) {
            $this->download_export_payload_from_request();
        }

        if ( 'clear_preview' === $action ) {
            $deleted_count = $this->manager->cleanup_user_preview_pages( get_current_user_id() );
            $state['request_notice'] = sprintf(
                /* translators: %d: preview pages count */
                __( '已清理 %d 个临时预览页面。', 'developer-starter' ),
                absint( $deleted_count )
            );
            return $state;
        }

        if ( 'rollback_import' === $action ) {
            $run_id = isset( $_POST['developer_starter_site_package_import_run_id'] )
                ? sanitize_text_field( wp_unslash( (string) $_POST['developer_starter_site_package_import_run_id'] ) )
                : '';

            if ( ! $this->manager->acquire_import_lock( get_current_user_id() ) ) {
                $state['request_error'] = __( '当前已有导入/回滚任务正在执行，请稍后再试。', 'developer-starter' );
                return $state;
            }

            $rollback_result = $this->manager->rollback_import_history( $run_id );
            $this->manager->release_import_lock( get_current_user_id() );
            if ( is_wp_error( $rollback_result ) ) {
                $state['request_error'] = $rollback_result->get_error_message();
                return $state;
            }

            $state['request_notice'] = sprintf(
                /* translators: 1: restored pages, 2: deleted pages */
                __( '回滚完成：已恢复 %1$d 个更新页面，已删除 %2$d 个新建页面。', 'developer-starter' ),
                isset( $rollback_result['restored_pages'] ) ? absint( $rollback_result['restored_pages'] ) : 0,
                isset( $rollback_result['deleted_pages'] ) ? absint( $rollback_result['deleted_pages'] ) : 0
            );
            return $state;
        }

        $state['selected_conflict_strategy'] = $this->get_conflict_strategy_from_request();
        $state['export_form']                = $this->get_export_form_from_request();

        if ( in_array( $action, array( 'preview', 'import', 'create_preview' ), true ) ) {
            $raw_json = $this->get_raw_package_json_from_request( 'preview' === $action );
            if ( is_wp_error( $raw_json ) ) {
                $state['request_error'] = $raw_json->get_error_message();
                return $state;
            }

            $state['raw_json'] = $raw_json;
            $analysis          = $this->manager->analyze_site_package(
                $raw_json,
                array(
                    'conflict_strategy' => $state['selected_conflict_strategy'],
                )
            );

            if ( is_wp_error( $analysis ) ) {
                $state['request_error'] = $analysis->get_error_message();
                return $state;
            }

            $state['analysis'] = $analysis;

            if ( 'preview' === $action ) {
                return $state;
            }

            if ( empty( $analysis['can_import'] ) || empty( $analysis['prepared_package'] ) ) {
                $state['request_error'] = __( '当前数据包未通过预检，暂时不能导入。', 'developer-starter' );
                return $state;
            }

            $selected_page_keys = $this->get_selected_import_page_keys_from_request( $analysis );
            if ( is_wp_error( $selected_page_keys ) ) {
                $state['request_error'] = $selected_page_keys->get_error_message();
                return $state;
            }

            $prepared_package = $this->filter_prepared_package_by_selected_pages( $analysis['prepared_package'], $selected_page_keys );
            if ( empty( $prepared_package['pages'] ) || ! is_array( $prepared_package['pages'] ) ) {
                $state['request_error'] = __( '请至少勾选一个要导入的页面。', 'developer-starter' );
                return $state;
            }

            $apply_site_options = $this->should_apply_site_options_from_request();
            $front_page_key = isset( $prepared_package['site_options']['front_page'] )
                ? sanitize_key( (string) $prepared_package['site_options']['front_page'] )
                : '';

            if ( 'import' === $action && $apply_site_options && '' !== $front_page_key && ! $this->has_confirmed_front_page_change_from_request() ) {
                $state['request_error'] = __( '你已勾选“应用站点设置”，请再勾选“我已确认替换当前首页”后再执行导入。', 'developer-starter' );
                return $state;
            }

            if ( 'create_preview' === $action ) {
                if ( ! $this->manager->acquire_import_lock( get_current_user_id() ) ) {
                    $state['request_error'] = __( '当前已有导入/预览任务正在执行，请稍后再试。', 'developer-starter' );
                    return $state;
                }

                $preview_result = $this->manager->create_site_package_preview(
                    $prepared_package,
                    array(
                        'preview_owner_id' => get_current_user_id(),
                    )
                );
                $this->manager->release_import_lock( get_current_user_id() );

                if ( is_wp_error( $preview_result ) ) {
                    $state['request_error'] = $preview_result->get_error_message();
                    return $state;
                }

                $state['preview_result'] = $preview_result;
                $binding_token = $this->create_preview_binding_token( $raw_json, $selected_page_keys, $state['selected_conflict_strategy'] );
                if ( '' !== $binding_token ) {
                    $state['preview_binding_token'] = $binding_token;
                    $state['request_notice'] = __( '已生成临时预览；本次预览页面选择已绑定到后续导入。', 'developer-starter' );
                }
                return $state;
            }

            $preview_binding_token = $this->get_preview_binding_token_from_request();
            $state['preview_binding_token'] = $preview_binding_token;
            $binding_check = $this->validate_preview_binding_token(
                $preview_binding_token,
                $raw_json,
                $selected_page_keys,
                $state['selected_conflict_strategy']
            );
            if ( is_wp_error( $binding_check ) ) {
                $state['request_error'] = $binding_check->get_error_message();
                return $state;
            }

            if ( ! $this->manager->acquire_import_lock( get_current_user_id() ) ) {
                $state['request_error'] = __( '当前已有导入任务正在执行，请稍后再试。', 'developer-starter' );
                return $state;
            }

            $import_result = $this->manager->import_site_package(
                $prepared_package,
                array(
                    'apply_site_options' => $apply_site_options,
                )
            );
            $this->manager->release_import_lock( get_current_user_id() );
            if ( is_wp_error( $import_result ) ) {
                $state['request_error'] = $import_result->get_error_message();
                return $state;
            }

            $this->consume_preview_binding_token( $preview_binding_token );

            $state['import_result'] = $import_result;
            // 导入成功后清空预检与原 JSON，避免误触二次导入。
            $state['analysis']       = null;
            $state['raw_json']       = '';
            $state['request_notice'] = __( '导入已完成，已自动清空预检缓存。若需再次导入，请重新上传或粘贴 JSON 后再预检。', 'developer-starter' );
            return $state;
        }

        if ( 'export' === $action ) {
            $export_result = $this->manager->export_site_package(
                $state['export_form']['selected_pages'],
                $this->build_export_options_from_form( $state['export_form'] )
            );

            if ( is_wp_error( $export_result ) ) {
                $state['request_error'] = $export_result->get_error_message();
                return $state;
            }

            $state['export_result'] = $export_result;
        }

        return $state;
    }

    /**
     * 从请求中读取原始 JSON。
     *
     * @param bool $allow_file_upload 当前动作是否允许读取上传文件。
     * @return string|\WP_Error
     */
    private function get_raw_package_json_from_request( $allow_file_upload ) {
        if ( $allow_file_upload && isset( $_FILES['developer_starter_site_package_file'] ) && is_array( $_FILES['developer_starter_site_package_file'] ) ) {
            $file = $_FILES['developer_starter_site_package_file'];
            if ( isset( $file['error'] ) && (int) $file['error'] !== UPLOAD_ERR_NO_FILE ) {
                if ( (int) $file['error'] !== UPLOAD_ERR_OK ) {
                    return new \WP_Error( 'upload_error', __( 'JSON 文件上传失败，请重新选择文件。', 'developer-starter' ) );
                }

                $filename = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
                if ( '' !== $filename && substr( strtolower( $filename ), -5 ) !== '.json' ) {
                    return new \WP_Error( 'invalid_file_type', __( '请上传 .json 格式的数据包文件。', 'developer-starter' ) );
                }

                $file_size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;
                if ( $file_size > Page_Package_Manager::MAX_PACKAGE_BYTES ) {
                    return new \WP_Error(
                        'upload_too_large',
                        sprintf(
                            /* translators: %s: max size */
                            __( '上传文件超过安全上限（最大 %s），请拆分数据包后再导入。', 'developer-starter' ),
                            size_format( Page_Package_Manager::MAX_PACKAGE_BYTES )
                        )
                    );
                }

                $tmp_name = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
                if ( '' === $tmp_name || ! file_exists( $tmp_name ) || ! is_uploaded_file( $tmp_name ) ) {
                    return new \WP_Error( 'missing_upload_file', __( '上传文件不存在，请重试。', 'developer-starter' ) );
                }

                if ( function_exists( 'wp_check_filetype_and_ext' ) ) {
                    $checked_type = wp_check_filetype_and_ext( $tmp_name, $filename );
                    $checked_ext  = isset( $checked_type['ext'] ) ? (string) $checked_type['ext'] : '';
                    if ( '' !== $checked_ext && 'json' !== strtolower( $checked_ext ) ) {
                        return new \WP_Error( 'invalid_file_type', __( '上传文件未通过 JSON 类型校验，请重新选择有效的数据包文件。', 'developer-starter' ) );
                    }
                }

                $raw_json = file_get_contents( $tmp_name );
                if ( ! is_string( $raw_json ) || '' === trim( $raw_json ) ) {
                    return new \WP_Error( 'empty_upload_file', __( '上传的 JSON 文件内容为空。', 'developer-starter' ) );
                }

                return $raw_json;
            }
        }

        $raw_json = isset( $_POST['developer_starter_site_package_json'] ) ? wp_unslash( (string) $_POST['developer_starter_site_package_json'] ) : '';
        $raw_json = trim( $raw_json );
        if ( '' === $raw_json ) {
            return new \WP_Error( 'missing_json', __( '请先上传 JSON 文件，或把数据包内容粘贴到文本框中。', 'developer-starter' ) );
        }

        return $raw_json;
    }

    /**
     * 从请求中获取冲突策略。
     *
     * @return string
     */
    private function get_conflict_strategy_from_request() {
        $strategy = isset( $_POST['developer_starter_site_package_conflict_strategy'] )
            ? sanitize_key( wp_unslash( (string) $_POST['developer_starter_site_package_conflict_strategy'] ) )
            : Page_Package_Manager::CONFLICT_STRATEGY_SKIP;

        $strategies = $this->manager->get_conflict_strategies();
        if ( ! isset( $strategies[ $strategy ] ) ) {
            $strategy = Page_Package_Manager::CONFLICT_STRATEGY_SKIP;
        }

        return $strategy;
    }

    /**
     * 获取预检结果里可执行导入的页面标识列表。
     *
     * @param array<string,mixed> $analysis 预检结果。
     * @return array<int,string>
     */
    private function get_selectable_import_page_keys_from_analysis( $analysis ) {
        if ( ! is_array( $analysis ) || empty( $analysis['pages'] ) || ! is_array( $analysis['pages'] ) ) {
            return array();
        }

        $selectable = array();
        foreach ( $analysis['pages'] as $page_report ) {
            if ( ! is_array( $page_report ) ) {
                continue;
            }

            $action   = isset( $page_report['action'] ) ? sanitize_key( (string) $page_report['action'] ) : '';
            $page_key = isset( $page_report['page_key'] ) ? sanitize_key( (string) $page_report['page_key'] ) : '';
            if ( '' === $page_key ) {
                continue;
            }

            if ( in_array( $action, array( 'create', 'duplicate', 'update' ), true ) ) {
                $selectable[] = $page_key;
            }
        }

        return array_values( array_unique( $selectable ) );
    }

    /**
     * 从导入请求中读取用户勾选的页面标识。
     *
     * @param array<string,mixed> $analysis 预检结果。
     * @return array<int,string>|\WP_Error
     */
    private function get_selected_import_page_keys_from_request( $analysis ) {
        $selectable_keys = $this->get_selectable_import_page_keys_from_analysis( $analysis );
        if ( empty( $selectable_keys ) ) {
            return array();
        }

        $selection_present = isset( $_POST['developer_starter_site_package_selection_present'] ) && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_selection_present'] );

        $selected_keys = isset( $_POST['developer_starter_site_package_selected_pages'] ) && is_array( $_POST['developer_starter_site_package_selected_pages'] )
            ? array_map(
                'sanitize_key',
                array_map(
                    'strval',
                    wp_unslash( $_POST['developer_starter_site_package_selected_pages'] )
                )
            )
            : array();

        $selected_keys = array_values( array_unique( array_intersect( $selected_keys, $selectable_keys ) ) );

        if ( $selection_present && empty( $selected_keys ) ) {
            return new \WP_Error( 'missing_selected_pages', __( '请至少勾选一个要导入的页面。', 'developer-starter' ) );
        }

        if ( empty( $selected_keys ) ) {
            return $selectable_keys;
        }

        return $selected_keys;
    }

    /**
     * 按勾选页面过滤准备导入的数据包。
     *
     * @param array<string,mixed> $prepared_package 预检后的数据包。
     * @param array<int,string>   $selected_page_keys 勾选页面 key。
     * @return array<string,mixed>
     */
    private function filter_prepared_package_by_selected_pages( $prepared_package, $selected_page_keys ) {
        if ( ! is_array( $prepared_package ) ) {
            return array();
        }

        $selected_map = array_fill_keys( array_map( 'sanitize_key', $selected_page_keys ), true );
        if ( empty( $selected_map ) ) {
            return $prepared_package;
        }

        if ( isset( $prepared_package['pages'] ) && is_array( $prepared_package['pages'] ) ) {
            $prepared_package['pages'] = array_values(
                array_filter(
                    $prepared_package['pages'],
                    static function ( $page ) use ( $selected_map ) {
                        if ( ! is_array( $page ) || empty( $page['page_key'] ) ) {
                            return false;
                        }
                        $page_key = sanitize_key( (string) $page['page_key'] );
                        return isset( $selected_map[ $page_key ] );
                    }
                )
            );
        }

        if ( isset( $prepared_package['existing_pages'] ) && is_array( $prepared_package['existing_pages'] ) ) {
            $prepared_package['existing_pages'] = array_intersect_key(
                $prepared_package['existing_pages'],
                $selected_map
            );
        }

        if ( isset( $prepared_package['site_options']['front_page'] ) ) {
            $front_page_key = sanitize_key( (string) $prepared_package['site_options']['front_page'] );
            if ( '' === $front_page_key || ! isset( $selected_map[ $front_page_key ] ) ) {
                unset( $prepared_package['site_options']['front_page'] );
            }
        }

        if ( isset( $prepared_package['site_options']['posts_page'] ) ) {
            $posts_page_key = sanitize_key( (string) $prepared_package['site_options']['posts_page'] );
            if ( '' === $posts_page_key || ! isset( $selected_map[ $posts_page_key ] ) ) {
                unset( $prepared_package['site_options']['posts_page'] );
            }
        }

        if ( ! empty( $prepared_package['site_options']['navigation']['menus'] ) && is_array( $prepared_package['site_options']['navigation']['menus'] ) ) {
            foreach ( $prepared_package['site_options']['navigation']['menus'] as $menu_index => $menu ) {
                if ( empty( $menu['items'] ) || ! is_array( $menu['items'] ) ) {
                    continue;
                }

                $prepared_package['site_options']['navigation']['menus'][ $menu_index ]['items'] = array_values(
                    array_filter(
                        $menu['items'],
                        static function ( $item ) use ( $selected_map ) {
                            if ( ! is_array( $item ) ) {
                                return false;
                            }
                            $page_key = isset( $item['page_key'] ) ? sanitize_key( (string) $item['page_key'] ) : '';
                            if ( '' !== $page_key ) {
                                return isset( $selected_map[ $page_key ] );
                            }
                            return ! empty( $item['url'] );
                        }
                    )
                );
            }
        }

        return $prepared_package;
    }

    /**
     * 当前请求是否允许应用站点设置（如首页映射）。
     *
     * @return bool
     */
    private function should_apply_site_options_from_request() {
        return isset( $_POST['developer_starter_site_package_apply_site_options'] )
            && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_apply_site_options'] );
    }

    /**
     * 是否已确认首页替换风险。
     *
     * @return bool
     */
    private function has_confirmed_front_page_change_from_request() {
        return isset( $_POST['developer_starter_site_package_confirm_front_page_change'] )
            && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_confirm_front_page_change'] );
    }

    /**
     * 从请求中读取预览绑定令牌。
     *
     * @return string
     */
    private function get_preview_binding_token_from_request() {
        $token = isset( $_POST['developer_starter_site_package_preview_binding_token'] )
            ? sanitize_key( wp_unslash( (string) $_POST['developer_starter_site_package_preview_binding_token'] ) )
            : '';
        return $token;
    }

    /**
     * 创建预览绑定令牌。
     *
     * @param string            $raw_json           当前 JSON。
     * @param array<int,string> $selected_page_keys 勾选页面。
     * @param string            $strategy           当前冲突策略。
     * @return string
     */
    private function create_preview_binding_token( $raw_json, $selected_page_keys, $strategy ) {
        $token = sanitize_key( wp_generate_password( 20, false, false ) );
        if ( '' === $token ) {
            return '';
        }

        $payload = array(
            'hash'          => $this->build_preview_binding_package_hash( $raw_json, $strategy, $selected_page_keys ),
            'selected_keys' => array_values( array_unique( array_map( 'sanitize_key', $selected_page_keys ) ) ),
            'created_at'    => time(),
            'user_id'       => get_current_user_id() ? absint( get_current_user_id() ) : 1,
        );

        set_transient( $this->get_preview_binding_transient_key( $token ), $payload, 2 * HOUR_IN_SECONDS );
        return $token;
    }

    /**
     * 校验预览绑定令牌。
     *
     * @param string            $token              绑定令牌。
     * @param string            $raw_json           当前 JSON。
     * @param array<int,string> $selected_page_keys 当前勾选页面。
     * @param string            $strategy           当前冲突策略。
     * @return true|\WP_Error
     */
    private function validate_preview_binding_token( $token, $raw_json, $selected_page_keys, $strategy ) {
        if ( '' === $token ) {
            return new \WP_Error( 'missing_preview_binding', __( '执行导入前请先点击“生成临时预览”，并保持同一批勾选页面。', 'developer-starter' ) );
        }

        $stored = get_transient( $this->get_preview_binding_transient_key( $token ) );
        if ( ! is_array( $stored ) || empty( $stored['hash'] ) ) {
            return new \WP_Error( 'expired_preview_binding', __( '预览绑定已过期，请重新生成临时预览后再导入。', 'developer-starter' ) );
        }

        $expected_hash = $this->build_preview_binding_package_hash( $raw_json, $strategy, $selected_page_keys );
        $stored_hash   = sanitize_text_field( (string) $stored['hash'] );
        if ( '' === $expected_hash || $stored_hash !== $expected_hash ) {
            return new \WP_Error( 'preview_binding_mismatch', __( '导入内容或勾选页面与预览不一致，请重新生成临时预览后再导入。', 'developer-starter' ) );
        }

        $stored_user_id  = isset( $stored['user_id'] ) ? absint( $stored['user_id'] ) : 0;
        $current_user_id = get_current_user_id() ? absint( get_current_user_id() ) : 1;
        if ( $stored_user_id > 0 && $stored_user_id !== $current_user_id ) {
            return new \WP_Error( 'preview_binding_owner_mismatch', __( '该预览绑定不属于当前登录用户，请重新生成临时预览。', 'developer-starter' ) );
        }

        return true;
    }

    /**
     * 消费（删除）预览绑定令牌。
     *
     * @param string $token 绑定令牌。
     * @return void
     */
    private function consume_preview_binding_token( $token ) {
        if ( '' === $token ) {
            return;
        }

        delete_transient( $this->get_preview_binding_transient_key( $token ) );
    }

    /**
     * 构建预览绑定 transient 键。
     *
     * @param string $token 绑定令牌。
     * @return string
     */
    private function get_preview_binding_transient_key( $token ) {
        return 'ds_site_pkg_preview_bind_' . sanitize_key( (string) $token );
    }

    /**
     * 生成预览绑定校验哈希。
     *
     * @param string            $raw_json           当前 JSON。
     * @param string            $strategy           当前冲突策略。
     * @param array<int,string> $selected_page_keys 当前勾选页面。
     * @return string
     */
    private function build_preview_binding_package_hash( $raw_json, $strategy, $selected_page_keys ) {
        $selected_page_keys = array_values( array_unique( array_map( 'sanitize_key', $selected_page_keys ) ) );
        sort( $selected_page_keys );

        $payload = array(
            'json'      => trim( (string) $raw_json ),
            'strategy'  => sanitize_key( (string) $strategy ),
            'selection' => $selected_page_keys,
        );

        return md5( wp_json_encode( $payload ) );
    }

    /**
     * 从请求中读取导出表单。
     *
     * @return array<string,mixed>
     */
    private function get_export_form_from_request() {
        $form = $this->get_default_export_form();

        $form['title']             = isset( $_POST['developer_starter_site_package_export_title'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['developer_starter_site_package_export_title'] ) ) : $form['title'];
        $form['scope']             = isset( $_POST['developer_starter_site_package_export_scope'] ) ? sanitize_key( wp_unslash( (string) $_POST['developer_starter_site_package_export_scope'] ) ) : Page_Package_Manager::PACKAGE_SCOPE_PAGE;
        if ( ! in_array( $form['scope'], array( Page_Package_Manager::PACKAGE_SCOPE_PAGE, Page_Package_Manager::PACKAGE_SCOPE_SITE ), true ) ) {
            $form['scope'] = Page_Package_Manager::PACKAGE_SCOPE_PAGE;
        }
        $form['package_id']        = isset( $_POST['developer_starter_site_package_export_package_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['developer_starter_site_package_export_package_id'] ) ) : '';
        $form['min_theme_version'] = isset( $_POST['developer_starter_site_package_export_min_theme_version'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['developer_starter_site_package_export_min_theme_version'] ) ) : $form['min_theme_version'];
        $form['author_name']       = isset( $_POST['developer_starter_site_package_export_author_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['developer_starter_site_package_export_author_name'] ) ) : '';
        $form['author_url']        = isset( $_POST['developer_starter_site_package_export_author_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['developer_starter_site_package_export_author_url'] ) ) : '';
        $form['description']       = isset( $_POST['developer_starter_site_package_export_description'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['developer_starter_site_package_export_description'] ) ) : '';
        $form['cover']             = isset( $_POST['developer_starter_site_package_export_cover'] ) ? esc_url_raw( wp_unslash( (string) $_POST['developer_starter_site_package_export_cover'] ) ) : '';
        $form['categories']        = isset( $_POST['developer_starter_site_package_export_categories'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['developer_starter_site_package_export_categories'] ) ) : '';
        $form['tags']              = isset( $_POST['developer_starter_site_package_export_tags'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['developer_starter_site_package_export_tags'] ) ) : '';
        $form['dependency_plugins']= isset( $_POST['developer_starter_site_package_export_dependency_plugins'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['developer_starter_site_package_export_dependency_plugins'] ) ) : '';
        $form['include_design_system']  = isset( $_POST['developer_starter_site_package_export_include_design_system'] ) && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_export_include_design_system'] );
        $form['include_content_models'] = isset( $_POST['developer_starter_site_package_export_include_content_models'] ) && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_export_include_content_models'] );
        $form['include_navigation']     = isset( $_POST['developer_starter_site_package_export_include_navigation'] ) && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_export_include_navigation'] );
        $form['include_site_identity']  = isset( $_POST['developer_starter_site_package_export_include_site_identity'] ) && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_export_include_site_identity'] );
        $form['include_site_assets']    = isset( $_POST['developer_starter_site_package_export_include_site_assets'] ) && '1' === (string) wp_unslash( $_POST['developer_starter_site_package_export_include_site_assets'] );
        $form['selected_pages']    = isset( $_POST['developer_starter_site_package_export_pages'] ) && is_array( $_POST['developer_starter_site_package_export_pages'] )
            ? array_values( array_unique( array_filter( array_map( 'absint', wp_unslash( $_POST['developer_starter_site_package_export_pages'] ) ) ) ) )
            : array();

        return $form;
    }

    /**
     * 生成导出参数。
     *
     * @param array<string,mixed> $form 导出表单。
     * @return array<string,mixed>
     */
    private function build_export_options_from_form( $form ) {
        $dependency_lines = preg_split( '/[\r\n,]+/', (string) $form['dependency_plugins'] );
        $dependency_lines = is_array( $dependency_lines ) ? array_values( array_filter( array_map( 'trim', $dependency_lines ) ) ) : array();

        return array(
            'title'             => $form['title'],
            'scope'             => $form['scope'],
            'package_id'        => $form['package_id'],
            'min_theme_version' => $form['min_theme_version'],
            'author'            => array(
                'name' => $form['author_name'],
                'url'  => $form['author_url'],
            ),
            'description'       => $form['description'],
            'cover'             => $form['cover'],
            'categories'        => $form['categories'],
            'tags'              => $form['tags'],
            'dependencies'      => array(
                'plugins' => $dependency_lines,
            ),
            'include_design_system'  => ! empty( $form['include_design_system'] ),
            'include_content_models' => ! empty( $form['include_content_models'] ),
            'include_navigation'     => ! empty( $form['include_navigation'] ),
            'include_site_identity'  => ! empty( $form['include_site_identity'] ),
            'include_site_assets'    => ! empty( $form['include_site_assets'] ),
        );
    }

    /**
     * 下载导出的 JSON 数据。
     *
     * @return void
     */
    private function download_export_payload_from_request() {
        $json = isset( $_POST['developer_starter_site_package_export_json'] ) ? wp_unslash( (string) $_POST['developer_starter_site_package_export_json'] ) : '';
        $json = trim( $json );
        if ( '' === $json ) {
            wp_die( esc_html__( '没有可下载的数据包内容。', 'developer-starter' ) );
        }

        $filename = isset( $_POST['developer_starter_site_package_export_filename'] ) ? sanitize_file_name( wp_unslash( (string) $_POST['developer_starter_site_package_export_filename'] ) ) : 'site-package.json';
        if ( '' === $filename ) {
            $filename = 'site-package.json';
        }

        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    /**
     * 渲染预检结果。
     *
     * @param array<string,mixed> $analysis                  预检结果。
     * @param string              $raw_json                  原始 JSON。
     * @param string              $selected_conflict_strategy 选中的冲突策略。
     * @param string              $preview_binding_token      预览绑定令牌。
     * @return void
     */
    private function render_analysis_result( $analysis, $raw_json, $selected_conflict_strategy, $preview_binding_token = '' ) {
        $stats            = isset( $analysis['stats'] ) && is_array( $analysis['stats'] ) ? $analysis['stats'] : array();
        $prepared_package = isset( $analysis['prepared_package'] ) && is_array( $analysis['prepared_package'] ) ? $analysis['prepared_package'] : array();
        $meta             = isset( $prepared_package['meta'] ) && is_array( $prepared_package['meta'] ) ? $prepared_package['meta'] : array();
        $site_options     = isset( $prepared_package['site_options'] ) && is_array( $prepared_package['site_options'] ) ? $prepared_package['site_options'] : array();
        $front_page_key   = isset( $site_options['front_page'] ) ? sanitize_key( (string) $site_options['front_page'] ) : '';
        $posts_page_key   = isset( $site_options['posts_page'] ) ? sanitize_key( (string) $site_options['posts_page'] ) : '';
        $has_site_options = $this->has_applicable_site_options( $site_options );
        $package_scope    = isset( $meta['scope'] ) ? sanitize_key( (string) $meta['scope'] ) : Page_Package_Manager::PACKAGE_SCOPE_PAGE;
        $selectable_keys  = $this->get_selectable_import_page_keys_from_analysis( $analysis );
        $conflict_map     = $this->manager->get_conflict_strategies();
        $strategy_label   = isset( $conflict_map[ $selected_conflict_strategy ]['label'] ) ? $conflict_map[ $selected_conflict_strategy ]['label'] : __( '未知策略', 'developer-starter' );
        ?>
        <div class="ds-package-panel">
            <h2><?php esc_html_e( '预检结果', 'developer-starter' ); ?></h2>

            <div class="ds-package-grid">
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '包类型', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( Page_Package_Manager::PACKAGE_SCOPE_SITE === $package_scope ? __( '整站包', 'developer-starter' ) : __( '页面包', 'developer-starter' ) ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '总页面数', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['total_pages'] ) ? (string) absint( $stats['total_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '可执行页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['ready_pages'] ) ? (string) absint( $stats['ready_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '新建页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['create_pages'] ) ? (string) absint( $stats['create_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '自动新 URL', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['duplicate_pages'] ) ? (string) absint( $stats['duplicate_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '更新页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['update_pages'] ) ? (string) absint( $stats['update_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '将跳过页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['skipped_pages'] ) ? (string) absint( $stats['skipped_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '已拦截页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['blocked_pages'] ) ? (string) absint( $stats['blocked_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '样式预警页数', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['style_warning_pages'] ) ? (string) absint( $stats['style_warning_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '样式预警总数', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['style_warning_count'] ) ? (string) absint( $stats['style_warning_count'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '安全预警页数', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['security_warning_pages'] ) ? (string) absint( $stats['security_warning_pages'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '安全预警总数', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['security_warning_count'] ) ? (string) absint( $stats['security_warning_count'] ) : '0' ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '当前策略', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( $strategy_label ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '整站设置组', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $stats['site_setting_groups'] ) ? (string) absint( $stats['site_setting_groups'] ) : '0' ); ?></div>
                </div>
            </div>

            <?php if ( ! empty( $meta ) ) : ?>
                <h3><?php esc_html_e( '数据包信息', 'developer-starter' ); ?></h3>
                <div class="ds-package-grid">
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '标题 / ID', 'developer-starter' ); ?></strong>
                        <div><?php echo esc_html( isset( $meta['title'] ) ? (string) $meta['title'] : '' ); ?></div>
                        <code><?php echo esc_html( isset( $meta['package_id'] ) ? (string) $meta['package_id'] : '' ); ?></code>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '主题要求', 'developer-starter' ); ?></strong>
                        <div><?php echo esc_html( isset( $meta['theme'] ) ? (string) $meta['theme'] : 'qiling' ); ?></div>
                        <div><?php echo esc_html( sprintf( __( '最低版本：%s', 'developer-starter' ), isset( $meta['min_theme_version'] ) && '' !== (string) $meta['min_theme_version'] ? (string) $meta['min_theme_version'] : __( '未声明', 'developer-starter' ) ) ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '作者', 'developer-starter' ); ?></strong>
                        <div><?php echo wp_kses_post( $this->format_author_html( isset( $meta['author'] ) ? $meta['author'] : array() ) ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '协议版本', 'developer-starter' ); ?></strong>
                        <div><?php echo esc_html( isset( $meta['version'] ) ? (string) absint( $meta['version'] ) : '1' ); ?></div>
                    </div>
                    <div class="ds-package-card">
                        <strong><?php esc_html_e( '导入作用域', 'developer-starter' ); ?></strong>
                        <div><?php echo esc_html( Page_Package_Manager::PACKAGE_SCOPE_SITE === $package_scope ? __( '整站包', 'developer-starter' ) : __( '页面包', 'developer-starter' ) ); ?></div>
                    </div>
                </div>

                <?php if ( ! empty( $meta['description'] ) ) : ?>
                    <div class="ds-package-note"><?php echo esc_html( (string) $meta['description'] ); ?></div>
                <?php endif; ?>

                <?php if ( ! empty( $meta['cover'] ) ) : ?>
                    <p><img class="ds-package-cover" src="<?php echo esc_url( (string) $meta['cover'] ); ?>" alt="" /></p>
                <?php endif; ?>

                <?php if ( ! empty( $meta['categories'] ) || ! empty( $meta['tags'] ) ) : ?>
                    <div class="ds-package-badges">
                        <?php foreach ( isset( $meta['categories'] ) && is_array( $meta['categories'] ) ? $meta['categories'] : array() as $category ) : ?>
                            <span class="ds-package-badge"><?php echo esc_html( sprintf( __( '分类：%s', 'developer-starter' ), $category ) ); ?></span>
                        <?php endforeach; ?>
                        <?php foreach ( isset( $meta['tags'] ) && is_array( $meta['tags'] ) ? $meta['tags'] : array() as $tag ) : ?>
                            <span class="ds-package-badge"><?php echo esc_html( sprintf( __( '标签：%s', 'developer-starter' ), $tag ) ); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $meta['dependencies'] ) ) : ?>
                    <?php $this->render_dependency_summary( $meta['dependencies'] ); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ( $has_site_options ) : ?>
                <h3><?php esc_html_e( '整站设置摘要', 'developer-starter' ); ?></h3>
                <?php $this->render_site_options_summary( $site_options ); ?>
            <?php endif; ?>

            <?php if ( ! empty( $analysis['errors'] ) && empty( $analysis['can_import'] ) ) : ?>
                <div class="notice notice-error inline"><p><?php esc_html_e( '当前数据包存在阻断问题，而且没有可执行的页面，暂时不能导入。', 'developer-starter' ); ?></p></div>
            <?php elseif ( ! empty( $analysis['errors'] ) ) : ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e( '当前数据包有部分页面被拦截，但其余通过预检的页面仍可导入。', 'developer-starter' ); ?></p></div>
            <?php elseif ( empty( $analysis['can_import'] ) ) : ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e( '当前数据包没有可执行的页面，暂时不能继续导入。', 'developer-starter' ); ?></p></div>
            <?php else : ?>
                <div class="notice notice-success inline"><p><?php esc_html_e( '预检通过：可继续生成临时预览，然后执行导入。', 'developer-starter' ); ?></p></div>
            <?php endif; ?>

            <?php if ( '' !== $front_page_key ) : ?>
                <div class="notice notice-warning inline">
                    <p>
                        <?php
                        echo esc_html(
                            sprintf(
                                /* translators: %s: page key */
                                __( '当前数据包声明了首页映射（front_page=%s）。勾选该首页映射页并启用“应用站点设置”时，会替换当前站点首页。', 'developer-starter' ),
                                $front_page_key
                            )
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>
            <?php if ( '' !== $posts_page_key ) : ?>
                <div class="notice notice-warning inline">
                    <p>
                        <?php
                        echo esc_html(
                            sprintf(
                                /* translators: %s: page key */
                                __( '当前数据包声明了文章列表页映射（posts_page=%s）。勾选该页面并启用“应用整站设置”时，会替换当前站点文章列表页。', 'developer-starter' ),
                                $posts_page_key
                            )
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $analysis['errors'] ) ) : ?>
                <h3><?php esc_html_e( '阻断问题', 'developer-starter' ); ?></h3>
                <ul class="ds-package-list">
                    <?php foreach ( $analysis['errors'] as $error_message ) : ?>
                        <li><?php echo esc_html( $error_message ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( ! empty( $analysis['warnings'] ) ) : ?>
                <h3><?php esc_html_e( '预警信息', 'developer-starter' ); ?></h3>
                <ul class="ds-package-list">
                    <?php foreach ( $analysis['warnings'] as $warning_message ) : ?>
                        <li><?php echo esc_html( $warning_message ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( ! empty( $analysis['style_warnings'] ) ) : ?>
                <h3><?php esc_html_e( '样式提醒', 'developer-starter' ); ?></h3>
                <div class="ds-package-note">
                    <?php esc_html_e( '这些提示不会阻断导入，但代表导入后建议重点复查页面视觉表现。', 'developer-starter' ); ?>
                </div>
                <ul class="ds-package-list">
                    <?php foreach ( $analysis['style_warnings'] as $style_warning_message ) : ?>
                        <li><?php echo esc_html( $style_warning_message ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( ! empty( $analysis['security_warnings'] ) ) : ?>
                <h3><?php esc_html_e( '安全风控预警', 'developer-starter' ); ?></h3>
                <div class="ds-package-note">
                    <?php esc_html_e( '这些提示不会直接阻断导入，但代表当前数据包含有高风险输入或体积偏大的内容，建议仅在可信来源和测试环境下继续操作。', 'developer-starter' ); ?>
                </div>
                <ul class="ds-package-list">
                    <?php foreach ( $analysis['security_warnings'] as $security_warning_message ) : ?>
                        <li><?php echo esc_html( $security_warning_message ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php
            $impact_groups = array(
                'create'    => array(),
                'duplicate' => array(),
                'update'    => array(),
                'skip'      => array(),
                'blocked'   => array(),
            );
            if ( ! empty( $analysis['pages'] ) && is_array( $analysis['pages'] ) ) {
                foreach ( $analysis['pages'] as $impact_page ) {
                    if ( ! is_array( $impact_page ) ) {
                        continue;
                    }
                    $impact_action = isset( $impact_page['action'] ) ? sanitize_key( (string) $impact_page['action'] ) : 'blocked';
                    if ( ! isset( $impact_groups[ $impact_action ] ) ) {
                        $impact_action = 'blocked';
                    }
                    $impact_groups[ $impact_action ][] = $impact_page;
                }
            }
            ?>
            <h3><?php esc_html_e( '导入影响清单（Diff）', 'developer-starter' ); ?></h3>
            <div class="ds-package-grid">
                <?php foreach ( array( 'create', 'duplicate', 'update', 'skip', 'blocked' ) as $impact_key ) : ?>
                    <?php $impact_rows = isset( $impact_groups[ $impact_key ] ) ? $impact_groups[ $impact_key ] : array(); ?>
                    <div class="ds-package-card">
                        <strong><?php echo esc_html( $this->get_analysis_status_display( $impact_key )['label'] ); ?></strong>
                        <div><?php echo esc_html( (string) count( $impact_rows ) ); ?></div>
                        <?php if ( ! empty( $impact_rows ) ) : ?>
                            <ul class="ds-package-list" style="margin-top:8px;">
                                <?php foreach ( $impact_rows as $impact_row ) : ?>
                                    <li>
                                        <?php echo esc_html( isset( $impact_row['title'] ) ? (string) $impact_row['title'] : '' ); ?>
                                        <code><?php echo esc_html( isset( $impact_row['slug'] ) ? (string) $impact_row['slug'] : '' ); ?></code>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3><?php esc_html_e( '页面检查明细', 'developer-starter' ); ?></h3>
            <table class="ds-package-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '页面标识', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '标题 / URL', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '模板', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '模块数', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '处理结果', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '说明', 'developer-starter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $analysis['pages'] as $page_report ) : ?>
                        <?php
                        $action         = isset( $page_report['action'] ) ? (string) $page_report['action'] : 'blocked';
                        $status_display = $this->get_analysis_status_display( $action );
                        ?>
                        <tr>
                            <td><code><?php echo esc_html( isset( $page_report['page_key'] ) ? (string) $page_report['page_key'] : '' ); ?></code></td>
                            <td>
                                <strong><?php echo esc_html( isset( $page_report['title'] ) ? (string) $page_report['title'] : '' ); ?></strong><br />
                                <code><?php echo esc_html( isset( $page_report['slug'] ) ? (string) $page_report['slug'] : '' ); ?></code>
                                <?php if ( '' !== $front_page_key && isset( $page_report['page_key'] ) && sanitize_key( (string) $page_report['page_key'] ) === $front_page_key ) : ?>
                                    <br />
                                    <span class="ds-package-homepage-flag"><?php esc_html_e( '首页映射页（可能替换当前首页）', 'developer-starter' ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $page_report['target_slug'] ) && $page_report['target_slug'] !== $page_report['slug'] ) : ?>
                                    <br />
                                    <span class="description"><?php echo esc_html( sprintf( __( '导入目标 URL：%s', 'developer-starter' ), (string) $page_report['target_slug'] ) ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $page_report['existing_page_id'] ) ) : ?>
                                    <br />
                                    <a href="<?php echo esc_url( get_edit_post_link( (int) $page_report['existing_page_id'], 'raw' ) ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( '查看已存在页面', 'developer-starter' ); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( isset( $page_report['template_label'] ) ? (string) $page_report['template_label'] : '' ); ?><br /><code><?php echo esc_html( isset( $page_report['template'] ) ? (string) $page_report['template'] : '' ); ?></code></td>
                            <td><?php echo esc_html( (string) absint( isset( $page_report['module_count'] ) ? $page_report['module_count'] : 0 ) ); ?></td>
                            <td><span class="ds-status <?php echo esc_attr( $status_display['class'] ); ?>"><?php echo esc_html( $status_display['label'] ); ?></span></td>
                            <td>
                                <?php if ( ! empty( $page_report['errors'] ) ) : ?>
                                    <ul class="ds-package-list">
                                        <?php foreach ( $page_report['errors'] as $error_message ) : ?>
                                            <li><?php echo esc_html( $error_message ); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <?php if ( ! empty( $page_report['warnings'] ) ) : ?>
                                    <ul class="ds-package-list">
                                        <?php foreach ( $page_report['warnings'] as $warning_message ) : ?>
                                            <li><?php echo esc_html( $warning_message ); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <?php if ( ! empty( $page_report['style_warnings'] ) ) : ?>
                                    <div class="description"><strong><?php esc_html_e( '样式提醒：', 'developer-starter' ); ?></strong></div>
                                    <ul class="ds-package-list">
                                        <?php foreach ( $page_report['style_warnings'] as $style_warning_message ) : ?>
                                            <li><?php echo esc_html( $style_warning_message ); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <?php if ( ! empty( $page_report['security_warnings'] ) ) : ?>
                                    <div class="description"><strong><?php esc_html_e( '安全风控：', 'developer-starter' ); ?></strong></div>
                                    <ul class="ds-package-list">
                                        <?php foreach ( $page_report['security_warnings'] as $security_warning_message ) : ?>
                                            <li><?php echo esc_html( $security_warning_message ); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ( ! empty( $analysis['can_import'] ) ) : ?>
                <div class="ds-package-note">
                    <?php echo esc_html( sprintf( __( '导入会按“预检 → 临时预览 → 导入”的方式执行，并使用当前选择的策略：%s。', 'developer-starter' ), $strategy_label ) ); ?>
                </div>

                <form method="post">
                    <?php wp_nonce_field( 'developer_starter_site_package_action', 'developer_starter_site_package_nonce' ); ?>
                    <input type="hidden" name="developer_starter_site_package_conflict_strategy" value="<?php echo esc_attr( $selected_conflict_strategy ); ?>" />
                    <input type="hidden" name="developer_starter_site_package_selection_present" value="1" />
                    <input type="hidden" name="developer_starter_site_package_preview_binding_token" value="<?php echo esc_attr( $preview_binding_token ); ?>" />
                    <textarea class="ds-package-hidden" name="developer_starter_site_package_json"><?php echo esc_textarea( $raw_json ); ?></textarea>

                    <h3><?php esc_html_e( '选择要导入的页面', 'developer-starter' ); ?></h3>
                    <div class="ds-package-page-picker">
                        <?php if ( empty( $selectable_keys ) ) : ?>
                            <p><?php esc_html_e( '当前没有可执行导入的页面。', 'developer-starter' ); ?></p>
                        <?php else : ?>
                            <?php foreach ( $analysis['pages'] as $page_report ) : ?>
                                <?php
                                $page_action = isset( $page_report['action'] ) ? sanitize_key( (string) $page_report['action'] ) : '';
                                $page_key    = isset( $page_report['page_key'] ) ? sanitize_key( (string) $page_report['page_key'] ) : '';
                                if ( '' === $page_key || ! in_array( $page_action, array( 'create', 'duplicate', 'update' ), true ) ) {
                                    continue;
                                }
                                ?>
                                <label>
                                    <input type="checkbox" name="developer_starter_site_package_selected_pages[]" value="<?php echo esc_attr( $page_key ); ?>" checked="checked" />
                                    <span>
                                        <strong><?php echo esc_html( isset( $page_report['title'] ) ? (string) $page_report['title'] : $page_key ); ?></strong>
                                        <span class="ds-package-page-meta">
                                            <code><?php echo esc_html( isset( $page_report['slug'] ) ? (string) $page_report['slug'] : '' ); ?></code>
                                            · <?php echo esc_html( sprintf( __( '%d 个模块', 'developer-starter' ), isset( $page_report['module_count'] ) ? absint( $page_report['module_count'] ) : 0 ) ); ?>
                                            · <?php echo esc_html( $this->get_analysis_status_display( $page_action )['label'] ); ?>
                                            <?php if ( '' !== $front_page_key && $page_key === $front_page_key ) : ?>
                                                · <?php esc_html_e( '首页映射页（可能替换当前首页）', 'developer-starter' ); ?>
                                            <?php endif; ?>
                                        </span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ( '' !== $preview_binding_token ) : ?>
                        <div class="notice notice-success inline" style="margin-top:12px;">
                            <p><?php esc_html_e( '已绑定当前临时预览。只要不改 JSON、冲突策略和勾选页面，就可以直接执行导入。', 'developer-starter' ); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="notice notice-warning inline" style="margin-top:12px;">
                            <p><?php esc_html_e( '为了防止误导入，请先点击“生成临时预览”，再执行正式导入。', 'developer-starter' ); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ( $has_site_options ) : ?>
                        <p style="margin-top:12px;">
                            <label>
                                <input type="checkbox" name="developer_starter_site_package_apply_site_options" value="1" />
                                <?php esc_html_e( '应用整站设置', 'developer-starter' ); ?>
                            </label>
                            <br />
                            <span class="description"><?php esc_html_e( '默认不应用整站设置，避免误改当前站点标题、首页、文章页、全局样式、内容模型或菜单。', 'developer-starter' ); ?></span>
                        </p>
                        <?php if ( '' !== $front_page_key ) : ?>
                            <p>
                                <label>
                                    <input type="checkbox" name="developer_starter_site_package_confirm_front_page_change" value="1" />
                                    <?php esc_html_e( '我已确认：导入后可能替换当前默认首页。', 'developer-starter' ); ?>
                                </label>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <p>
                        <button type="submit" name="developer_starter_site_package_action" value="create_preview" class="button">
                            <?php esc_html_e( '生成临时预览', 'developer-starter' ); ?>
                        </button>
                        <button type="submit" name="developer_starter_site_package_action" value="import" class="button button-primary">
                            <?php esc_html_e( '执行导入', 'developer-starter' ); ?>
                        </button>
                    </p>
                    <p class="description">
                        <?php esc_html_e( '临时预览会创建草稿页，仅用于预览效果；系统会自动覆盖你的旧预览并在到期后清理。', 'developer-starter' ); ?>
                    </p>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * 渲染临时预览结果。
     *
     * @param array<string,mixed> $preview_result 预览结果。
     * @return void
     */
    private function render_preview_result( $preview_result ) {
        $created_count = isset( $preview_result['created_count'] ) ? absint( $preview_result['created_count'] ) : 0;
        $error_count   = isset( $preview_result['error_count'] ) ? absint( $preview_result['error_count'] ) : 0;
        $expires_at    = isset( $preview_result['expires_at'] ) ? absint( $preview_result['expires_at'] ) : 0;
        ?>
        <div class="ds-package-panel">
            <h2><?php esc_html_e( '临时预览已生成', 'developer-starter' ); ?></h2>
            <div class="ds-package-grid">
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '预览页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) $created_count ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '错误数量', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) $error_count ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '到期时间', 'developer-starter' ); ?></strong>
                    <div>
                        <?php
                        echo esc_html(
                            $expires_at > 0
                                ? wp_date( 'Y-m-d H:i:s', $expires_at )
                                : __( '未设置', 'developer-starter' )
                        );
                        ?>
                    </div>
                </div>
            </div>

            <?php if ( ! empty( $preview_result['results'] ) && is_array( $preview_result['results'] ) ) : ?>
                <h3><?php esc_html_e( '预览页面链接', 'developer-starter' ); ?></h3>
                <table class="ds-package-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '页面标识', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '页面标题', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '打开预览', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '说明', 'developer-starter' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $preview_result['results'] as $row ) : ?>
                            <tr>
                                <td><code><?php echo esc_html( isset( $row['page_key'] ) ? (string) $row['page_key'] : '' ); ?></code></td>
                                <td><?php echo esc_html( isset( $row['title'] ) ? (string) $row['title'] : '' ); ?></td>
                                <td>
                                    <?php if ( ! empty( $row['url'] ) ) : ?>
                                        <a href="<?php echo esc_url( (string) $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( '打开预览', 'developer-starter' ); ?></a>
                                    <?php else : ?>
                                        <span class="description"><?php esc_html_e( '无可用链接', 'developer-starter' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( isset( $row['message'] ) ? (string) $row['message'] : '' ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <form method="post" style="margin-top:12px;">
                <?php wp_nonce_field( 'developer_starter_site_package_action', 'developer_starter_site_package_nonce' ); ?>
                <button type="submit" name="developer_starter_site_package_action" value="clear_preview" class="button">
                    <?php esc_html_e( '清理我的临时预览', 'developer-starter' ); ?>
                </button>
                <span class="description" style="margin-left:8px;"><?php esc_html_e( '会删除你当前生成的临时预览草稿页，不影响正式导入页面。', 'developer-starter' ); ?></span>
            </form>
        </div>
        <?php
    }

    /**
     * 渲染依赖信息摘要。
     *
     * @param array<string,mixed> $dependencies 依赖信息。
     * @return void
     */
    private function render_dependency_summary( $dependencies ) {
        $plugins = isset( $dependencies['plugins'] ) && is_array( $dependencies['plugins'] ) ? $dependencies['plugins'] : array();
        $notes   = isset( $dependencies['notes'] ) && is_array( $dependencies['notes'] ) ? $dependencies['notes'] : array();

        if ( empty( $plugins ) && empty( $notes ) ) {
            return;
        }
        ?>
        <h3><?php esc_html_e( '依赖声明', 'developer-starter' ); ?></h3>
        <div class="ds-package-badges">
            <?php foreach ( $plugins as $plugin ) : ?>
                <?php if ( is_array( $plugin ) && ! empty( $plugin['slug'] ) ) : ?>
                    <span class="ds-package-badge">
                        <?php echo esc_html( ! empty( $plugin['label'] ) ? (string) $plugin['label'] : (string) $plugin['slug'] ); ?>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php foreach ( $notes as $note ) : ?>
                <span class="ds-package-badge"><?php echo esc_html( (string) $note ); ?></span>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * 判断是否存在可应用的整站设置。
     *
     * @param array<string,mixed> $site_options 站点设置。
     * @return bool
     */
    private function has_applicable_site_options( $site_options ) {
        if ( ! is_array( $site_options ) || empty( $site_options ) ) {
            return false;
        }

        foreach ( array( 'front_page', 'posts_page', 'site_title', 'tagline', 'design_options', 'content_model_options', 'navigation' ) as $key ) {
            if ( ! empty( $site_options[ $key ] ) || ( 'tagline' === $key && array_key_exists( $key, $site_options ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 渲染整站设置摘要。
     *
     * @param array<string,mixed> $site_options 站点设置。
     * @return void
     */
    private function render_site_options_summary( $site_options ) {
        $items = array();
        if ( ! empty( $site_options['front_page'] ) ) {
            $items[] = sprintf(
                /* translators: %s: page key */
                __( '首页映射：%s', 'developer-starter' ),
                sanitize_key( (string) $site_options['front_page'] )
            );
        }
        if ( ! empty( $site_options['posts_page'] ) ) {
            $items[] = sprintf(
                /* translators: %s: page key */
                __( '文章列表页：%s', 'developer-starter' ),
                sanitize_key( (string) $site_options['posts_page'] )
            );
        }
        if ( ! empty( $site_options['site_title'] ) ) {
            $items[] = sprintf(
                /* translators: %s: site title */
                __( '站点标题：%s', 'developer-starter' ),
                sanitize_text_field( (string) $site_options['site_title'] )
            );
        }
        if ( array_key_exists( 'tagline', $site_options ) ) {
            $items[] = sprintf(
                /* translators: %s: tagline */
                __( '站点副标题：%s', 'developer-starter' ),
                sanitize_text_field( (string) $site_options['tagline'] )
            );
        }
        if ( ! empty( $site_options['design_options'] ) && is_array( $site_options['design_options'] ) ) {
            $items[] = sprintf(
                /* translators: %d: setting count */
                __( '全局样式：%d 个设置字段', 'developer-starter' ),
                count( $site_options['design_options'] )
            );
        }
        if ( ! empty( $site_options['content_model_options'] ) && is_array( $site_options['content_model_options'] ) ) {
            $items[] = sprintf(
                /* translators: %d: setting count */
                __( '内容模型中心：%d 个设置字段', 'developer-starter' ),
                count( $site_options['content_model_options'] )
            );
        }
        if ( ! empty( $site_options['navigation']['menus'] ) && is_array( $site_options['navigation']['menus'] ) ) {
            $items[] = sprintf(
                /* translators: %d: menu count */
                __( '导航菜单：%d 个菜单', 'developer-starter' ),
                count( $site_options['navigation']['menus'] )
            );
        }

        if ( empty( $items ) ) {
            return;
        }
        ?>
        <div class="ds-package-badges">
            <?php foreach ( $items as $item ) : ?>
                <span class="ds-package-badge"><?php echo esc_html( $item ); ?></span>
            <?php endforeach; ?>
        </div>
        <div class="ds-package-note"><?php esc_html_e( '整站设置只有在正式导入时勾选“应用整站设置”才会写入当前站点；临时预览不会修改这些设置。', 'developer-starter' ); ?></div>
        <?php
    }

    /**
     * 渲染导入结果。
     *
     * @param array<string,mixed> $import_result 导入结果。
     * @return void
     */
    private function render_import_result( $import_result ) {
        ?>
        <div class="ds-package-panel">
            <h2><?php esc_html_e( '导入完成', 'developer-starter' ); ?></h2>
            <div class="ds-package-grid">
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '新建页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) absint( isset( $import_result['created_count'] ) ? $import_result['created_count'] : 0 ) ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '副本页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) absint( isset( $import_result['duplicate_count'] ) ? $import_result['duplicate_count'] : 0 ) ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '更新页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) absint( isset( $import_result['updated_count'] ) ? $import_result['updated_count'] : 0 ) ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '跳过页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) absint( isset( $import_result['skipped_count'] ) ? $import_result['skipped_count'] : 0 ) ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '导入错误', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) absint( isset( $import_result['error_count'] ) ? $import_result['error_count'] : 0 ) ); ?></div>
                </div>
                <?php if ( ! empty( $import_result['import_run_id'] ) ) : ?>
                    <div class="ds-package-card ds-package-run-id">
                        <strong><?php esc_html_e( '导入批次 ID', 'developer-starter' ); ?></strong>
                        <div><code><?php echo esc_html( (string) $import_result['import_run_id'] ); ?></code></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $import_result['site_option_messages'] ) ) : ?>
                <h3><?php esc_html_e( '站点设置结果', 'developer-starter' ); ?></h3>
                <ul class="ds-package-list">
                    <?php foreach ( $import_result['site_option_messages'] as $site_option_message ) : ?>
                        <li><?php echo esc_html( $site_option_message ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( ! empty( $import_result['link_resolution_messages'] ) ) : ?>
                <h3><?php esc_html_e( '链接解析结果', 'developer-starter' ); ?></h3>
                <ul class="ds-package-list">
                    <?php foreach ( $import_result['link_resolution_messages'] as $link_resolution_message ) : ?>
                        <li><?php echo esc_html( $link_resolution_message ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h3><?php esc_html_e( '页面导入明细', 'developer-starter' ); ?></h3>
            <table class="ds-package-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '页面标识', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '标题 / URL', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '结果', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '说明', 'developer-starter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $import_result['results'] as $row ) : ?>
                        <?php $status_display = $this->get_import_status_display( isset( $row['action'] ) ? (string) $row['action'] : 'error' ); ?>
                        <tr>
                            <td><code><?php echo esc_html( isset( $row['page_key'] ) ? (string) $row['page_key'] : '' ); ?></code></td>
                            <td>
                                <strong><?php echo esc_html( isset( $row['title'] ) ? (string) $row['title'] : '' ); ?></strong><br />
                                <code><?php echo esc_html( isset( $row['slug'] ) ? (string) $row['slug'] : '' ); ?></code>
                                <?php if ( ! empty( $row['page_id'] ) ) : ?>
                                    <br />
                                    <a href="<?php echo esc_url( get_edit_post_link( (int) $row['page_id'], 'raw' ) ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( '编辑页面', 'developer-starter' ); ?>
                                    </a>
                                    |
                                    <a href="<?php echo esc_url( get_permalink( (int) $row['page_id'] ) ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( '查看页面', 'developer-starter' ); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><span class="ds-status <?php echo esc_attr( $status_display['class'] ); ?>"><?php echo esc_html( $status_display['label'] ); ?></span></td>
                            <td><?php echo esc_html( isset( $row['message'] ) ? (string) $row['message'] : '' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * 渲染导入历史面板（含回滚入口）。
     *
     * @param array<int,array<string,mixed>> $import_history 导入历史。
     * @return void
     */
    private function render_import_history_panel( $import_history ) {
        if ( ! is_array( $import_history ) ) {
            $import_history = array();
        }
        ?>
        <div class="ds-package-panel">
            <h2><?php esc_html_e( '导入历史中心', 'developer-starter' ); ?></h2>
            <p class="description"><?php esc_html_e( '保留最近导入记录，可查看每次导入影响，并回滚未回滚的记录。', 'developer-starter' ); ?></p>

            <?php if ( empty( $import_history ) ) : ?>
                <p><?php esc_html_e( '当前暂无导入历史。', 'developer-starter' ); ?></p>
            <?php else : ?>
                <table class="ds-package-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '执行时间', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '数据包', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '统计', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                            <th><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $import_history as $record ) : ?>
                            <?php
                            if ( ! is_array( $record ) ) {
                                continue;
                            }
                            $run_id          = isset( $record['run_id'] ) ? sanitize_text_field( (string) $record['run_id'] ) : '';
                            $created_at      = isset( $record['created_at'] ) ? absint( $record['created_at'] ) : 0;
                            $package_title   = isset( $record['package_title'] ) ? sanitize_text_field( (string) $record['package_title'] ) : '';
                            $package_id      = isset( $record['package_id'] ) ? sanitize_key( (string) $record['package_id'] ) : '';
                            $scope           = isset( $record['scope'] ) ? sanitize_key( (string) $record['scope'] ) : Page_Package_Manager::PACKAGE_SCOPE_PAGE;
                            $operator_id     = isset( $record['operator_id'] ) ? absint( $record['operator_id'] ) : 0;
                            $front_page_key  = isset( $record['front_page_key'] ) ? sanitize_key( (string) $record['front_page_key'] ) : '';
                            $apply_options   = ! empty( $record['apply_site_options'] );
                            $rolled_back_at  = isset( $record['rolled_back_at'] ) ? absint( $record['rolled_back_at'] ) : 0;
                            $stats           = isset( $record['stats'] ) && is_array( $record['stats'] ) ? $record['stats'] : array();
                            $rollback_result = isset( $record['rollback_result'] ) && is_array( $record['rollback_result'] ) ? $record['rollback_result'] : array();

                            $operator_name = '';
                            if ( $operator_id > 0 ) {
                                $operator = get_userdata( $operator_id );
                                if ( $operator instanceof \WP_User ) {
                                    $operator_name = (string) $operator->display_name;
                                }
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php
                                        echo esc_html(
                                            $created_at > 0
                                                ? wp_date( 'Y-m-d H:i:s', $created_at )
                                                : __( '未知', 'developer-starter' )
                                        );
                                        ?>
                                    </strong>
                                    <br />
                                    <code><?php echo esc_html( '' !== $run_id ? $run_id : __( '未记录', 'developer-starter' ) ); ?></code>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( '' !== $package_title ? $package_title : __( '未命名数据包', 'developer-starter' ) ); ?></strong>
                                    <br />
                                    <code><?php echo esc_html( '' !== $package_id ? $package_id : __( '未记录', 'developer-starter' ) ); ?></code>
                                    <br />
                                    <span class="description">
                                        <?php echo esc_html( Page_Package_Manager::PACKAGE_SCOPE_SITE === $scope ? __( '整站包', 'developer-starter' ) : __( '页面包', 'developer-starter' ) ); ?>
                                    </span>
                                    <?php if ( '' !== $front_page_key ) : ?>
                                        <br />
                                        <span class="ds-package-homepage-flag">
                                            <?php echo esc_html( sprintf( __( '首页映射：%s', 'developer-starter' ), $front_page_key ) ); ?>
                                        </span>
                                    <?php endif; ?>
                                    <br />
                                    <span class="description">
                                        <?php echo esc_html( $apply_options ? __( '已应用站点设置', 'developer-starter' ) : __( '未应用站点设置', 'developer-starter' ) ); ?>
                                    </span>
                                    <?php if ( '' !== $operator_name ) : ?>
                                        <br />
                                        <span class="description">
                                            <?php
                                            echo esc_html(
                                                sprintf(
                                                    /* translators: %s: user display name */
                                                    __( '执行人：%s', 'developer-starter' ),
                                                    $operator_name
                                                )
                                            );
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span><?php echo esc_html( sprintf( __( '新建 %d', 'developer-starter' ), absint( isset( $stats['created_count'] ) ? $stats['created_count'] : 0 ) ) ); ?></span>
                                    <br />
                                    <span><?php echo esc_html( sprintf( __( '副本 %d', 'developer-starter' ), absint( isset( $stats['duplicate_count'] ) ? $stats['duplicate_count'] : 0 ) ) ); ?></span>
                                    <br />
                                    <span><?php echo esc_html( sprintf( __( '更新 %d', 'developer-starter' ), absint( isset( $stats['updated_count'] ) ? $stats['updated_count'] : 0 ) ) ); ?></span>
                                    <br />
                                    <span><?php echo esc_html( sprintf( __( '跳过 %d', 'developer-starter' ), absint( isset( $stats['skipped_count'] ) ? $stats['skipped_count'] : 0 ) ) ); ?></span>
                                    <br />
                                    <span><?php echo esc_html( sprintf( __( '错误 %d', 'developer-starter' ), absint( isset( $stats['error_count'] ) ? $stats['error_count'] : 0 ) ) ); ?></span>
                                </td>
                                <td>
                                    <?php if ( $rolled_back_at > 0 ) : ?>
                                        <span class="ds-status ds-status-skip"><?php esc_html_e( '已回滚', 'developer-starter' ); ?></span>
                                        <br />
                                        <span class="description"><?php echo esc_html( wp_date( 'Y-m-d H:i:s', $rolled_back_at ) ); ?></span>
                                        <?php if ( ! empty( $rollback_result ) ) : ?>
                                            <br />
                                            <span class="description">
                                                <?php
                                                echo esc_html(
                                                    sprintf(
                                                        /* translators: 1: restored pages, 2: deleted pages */
                                                        __( '页面恢复 %1$d / 删除 %2$d', 'developer-starter' ),
                                                        absint( isset( $rollback_result['restored_pages'] ) ? $rollback_result['restored_pages'] : 0 ),
                                                        absint( isset( $rollback_result['deleted_pages'] ) ? $rollback_result['deleted_pages'] : 0 )
                                                    )
                                                );
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span class="ds-status ds-status-ready"><?php esc_html_e( '可回滚', 'developer-starter' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $rolled_back_at <= 0 && '' !== $run_id ) : ?>
                                        <form method="post" class="ds-package-inline-form">
                                            <?php wp_nonce_field( 'developer_starter_site_package_action', 'developer_starter_site_package_nonce' ); ?>
                                            <input type="hidden" name="developer_starter_site_package_action" value="rollback_import" />
                                            <input type="hidden" name="developer_starter_site_package_import_run_id" value="<?php echo esc_attr( $run_id ); ?>" />
                                            <button type="submit" class="button"><?php esc_html_e( '回滚本次导入', 'developer-starter' ); ?></button>
                                        </form>
                                    <?php else : ?>
                                        <span class="description"><?php esc_html_e( '无需操作', 'developer-starter' ); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * 渲染导出结果。
     *
     * @param array<string,mixed> $export_result 导出结果。
     * @return void
     */
    private function render_export_result( $export_result ) {
        ?>
        <div class="ds-package-panel">
            <h2><?php esc_html_e( '导出完成', 'developer-starter' ); ?></h2>
            <div class="ds-package-grid">
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '选中页面', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) absint( isset( $export_result['selected_count'] ) ? $export_result['selected_count'] : 0 ) ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '成功导出', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( (string) absint( isset( $export_result['exported_count'] ) ? $export_result['exported_count'] : 0 ) ); ?></div>
                </div>
                <div class="ds-package-card">
                    <strong><?php esc_html_e( '导出类型', 'developer-starter' ); ?></strong>
                    <div><?php echo esc_html( isset( $export_result['scope'] ) && Page_Package_Manager::PACKAGE_SCOPE_SITE === $export_result['scope'] ? __( '整站包', 'developer-starter' ) : __( '页面包', 'developer-starter' ) ); ?></div>
                </div>
            </div>

            <?php if ( ! empty( $export_result['warnings'] ) ) : ?>
                <h3><?php esc_html_e( '导出预警', 'developer-starter' ); ?></h3>
                <ul class="ds-package-list">
                    <?php foreach ( $export_result['warnings'] as $warning ) : ?>
                        <li><?php echo esc_html( $warning ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h3><?php esc_html_e( 'JSON 数据', 'developer-starter' ); ?></h3>
            <textarea class="ds-package-export-json" readonly><?php echo esc_textarea( isset( $export_result['json'] ) ? (string) $export_result['json'] : '' ); ?></textarea>

            <form method="post" class="ds-package-download">
                <?php wp_nonce_field( 'developer_starter_site_package_action', 'developer_starter_site_package_nonce' ); ?>
                <input type="hidden" name="developer_starter_site_package_action" value="download_export" />
                <textarea class="ds-package-hidden" name="developer_starter_site_package_export_json"><?php echo esc_textarea( isset( $export_result['json'] ) ? (string) $export_result['json'] : '' ); ?></textarea>
                <input type="hidden" name="developer_starter_site_package_export_filename" value="<?php echo esc_attr( isset( $export_result['filename'] ) ? (string) $export_result['filename'] : 'site-package.json' ); ?>" />
                <button type="submit" class="button button-secondary"><?php esc_html_e( '下载 JSON 文件', 'developer-starter' ); ?></button>
            </form>
        </div>
        <?php
    }

    /**
     * 获取预检状态展示。
     *
     * @param string $action 处理动作。
     * @return array<string,string>
     */
    private function get_analysis_status_display( $action ) {
        switch ( $action ) {
            case 'create':
                return array(
                    'class' => 'ds-status-ready',
                    'label' => __( '可新建', 'developer-starter' ),
                );
            case 'duplicate':
                return array(
                    'class' => 'ds-status-ready',
                    'label' => __( '将建副本', 'developer-starter' ),
                );
            case 'update':
                return array(
                    'class' => 'ds-status-update',
                    'label' => __( '可更新', 'developer-starter' ),
                );
            case 'skip':
                return array(
                    'class' => 'ds-status-skip',
                    'label' => __( '将跳过', 'developer-starter' ),
                );
            default:
                return array(
                    'class' => 'ds-status-blocked',
                    'label' => __( '已拦截', 'developer-starter' ),
                );
        }
    }

    /**
     * 获取导入结果状态展示。
     *
     * @param string $action 处理动作。
     * @return array<string,string>
     */
    private function get_import_status_display( $action ) {
        switch ( $action ) {
            case 'create':
                return array(
                    'class' => 'ds-status-create',
                    'label' => __( '已创建', 'developer-starter' ),
                );
            case 'duplicate':
                return array(
                    'class' => 'ds-status-create',
                    'label' => __( '已建副本', 'developer-starter' ),
                );
            case 'update':
                return array(
                    'class' => 'ds-status-update',
                    'label' => __( '已更新', 'developer-starter' ),
                );
            case 'skip':
                return array(
                    'class' => 'ds-status-skip',
                    'label' => __( '已跳过', 'developer-starter' ),
                );
            default:
                return array(
                    'class' => 'ds-status-error',
                    'label' => __( '失败', 'developer-starter' ),
                );
        }
    }

    /**
     * 格式化作者展示 HTML。
     *
     * @param mixed $author 作者信息。
     * @return string
     */
    private function format_author_html( $author ) {
        if ( ! is_array( $author ) ) {
            return '';
        }

        $name = isset( $author['name'] ) ? sanitize_text_field( (string) $author['name'] ) : '';
        $url  = isset( $author['url'] ) ? esc_url( (string) $author['url'] ) : '';

        if ( '' === $name ) {
            return __( '未填写', 'developer-starter' );
        }

        if ( '' === $url ) {
            return esc_html( $name );
        }

        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
            esc_url( $url ),
            esc_html( $name )
        );
    }

    /**
     * 示例 JSON。
     *
     * @return string
     */
    private function get_example_json_snippet() {
        return wp_json_encode(
            array(
                'package_type'      => 'developer_starter_site_package',
                'version'           => 1,
                'scope'             => 'site',
                'manifest'          => array(
                    'schema'       => 'qiling-site-package',
                    'kind'         => 'site_package',
                    'scope'        => 'site',
                    'features'     => array( 'pages', 'design_system', 'content_models', 'navigation' ),
                    'local_only'   => true,
                ),
                'package_id'        => 'demo-company-pages',
                'title'             => '企业展示站数据包',
                'theme'             => 'qiling',
                'min_theme_version' => '2.3.3',
                'author'            => array(
                    'name' => '本地模板团队',
                    'url'  => '',
                ),
                'description'       => '一套包含演示页和关于页的企业展示站数据包示例。',
                'categories'        => array( '企业站', '营销页' ),
                'tags'              => array( '品牌展示', '落地页' ),
                'dependencies'      => array(
                    'plugins' => array(
                        'qiling-forms/qiling-forms.php',
                    ),
                ),
                'pages'             => array(
                    array(
                        'page_key' => 'landing',
                        'title'    => '演示页',
                        'slug'     => 'demo-landing',
                        'template' => 'templates/template-fullscreen.php',
                        'settings' => array(
                            'hide_page_header'     => false,
                            'transparent_header'   => true,
                            'enable_scroll_reveal' => true,
                        ),
                        'modules'  => array(
                            array(
                                'type' => 'banner',
                                'data' => array(
                                    'banner_slides' => array(
                                        array(
                                            'title'    => '欢迎来到演示页',
                                            'btn_text' => '查看关于我们',
                                            'btn_url'  => 'qiling://page/about',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'page_key' => 'about',
                        'title'    => '关于我们',
                        'slug'     => 'about',
                        'template' => 'templates/template-fullscreen.php',
                        'modules'  => array(
                            array(
                                'type' => 'services',
                                'data' => array(
                                    'services_items' => array(
                                        array(
                                            'title' => '联系支持',
                                            'link'  => 'qiling://system/login',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'design_system'     => array(
                    'enabled' => true,
                    'preset'  => 'enterprise',
                    'options' => array(
                        'design_enable_global_tokens' => '1',
                        'design_preset'               => 'enterprise',
                    ),
                ),
                'content_models'    => array(
                    'enabled_model_ids' => array( 'service', 'case', 'post' ),
                    'options'           => array(
                        'content_model_center_enable'  => '1',
                        'content_model_enabled_models' => array( 'service', 'case', 'post' ),
                    ),
                ),
                'navigation'        => array(
                    'menus' => array(
                        array(
                            'menu_key' => 'primary',
                            'name'     => '主导航',
                            'location' => 'primary',
                            'items'    => array(
                                array( 'label' => '首页', 'page_key' => 'landing' ),
                                array( 'label' => '关于我们', 'page_key' => 'about' ),
                            ),
                        ),
                    ),
                ),
                'site_options'      => array(
                    'front_page' => 'landing',
                ),
            ),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }
}
