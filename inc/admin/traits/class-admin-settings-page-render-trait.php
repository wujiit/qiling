<?php
/**
 * Admin Settings Page Render Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Developer_Starter\Core\Theme_License;

trait Admin_Settings_Page_Render_Trait {

    public function render_settings_page() {
        $tabs = $this->get_tabs();
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'header';
        if ( 'basic' === $current_tab ) {
            $current_tab = 'header';
        }
        $options = get_option( $this->option_name, array() );
        $settings_broken = function_exists( 'developer_starter_is_option_serialization_broken' )
            ? developer_starter_is_option_serialization_broken( $this->option_name )
            : false;
        $this->favorite_settings = $this->get_user_favorites();
        if ( class_exists( 'Developer_Starter\Core\Theme_License' ) ) {
            $license_status = Theme_License::get_status();
            if ( $license_status !== 'valid' ) {
                $tabs = array(
                    'license' => __( '授权', 'developer-starter' ),
                );
                $current_tab = 'license';
            }
        }

        if ( isset( $_GET['ds_modules_meta_repair'] ) && '1' === (string) wp_unslash( $_GET['ds_modules_meta_repair'] ) ) {
            $scanned  = isset( $_GET['ds_scanned'] ) ? absint( wp_unslash( $_GET['ds_scanned'] ) ) : 0;
            $repaired = isset( $_GET['ds_repaired'] ) ? absint( wp_unslash( $_GET['ds_repaired'] ) ) : 0;
            $failed   = isset( $_GET['ds_failed'] ) ? absint( wp_unslash( $_GET['ds_failed'] ) ) : 0;
            add_settings_error(
                'developer_starter_settings',
                'ds_modules_meta_repair',
                sprintf(
                    /* translators: 1: scanned, 2: repaired, 3: failed */
                    __( '模块数据修复完成：扫描 %1$d 条，修复成功 %2$d 条，失败 %3$d 条。', 'developer-starter' ),
                    $scanned,
                    $repaired,
                    $failed
                ),
                'updated'
            );
        }

        if ( isset( $_GET['ds_options_repair'] ) && '1' === (string) wp_unslash( $_GET['ds_options_repair'] ) ) {
            $result = isset( $_GET['ds_options_repair_result'] ) ? sanitize_key( wp_unslash( $_GET['ds_options_repair_result'] ) ) : '';
            $message = '';
            $type = 'updated';
            switch ( $result ) {
                case 'repaired':
                    $message = __( '主题设置修复成功，请检查设置项是否恢复完整。', 'developer-starter' );
                    break;
                case 'ok':
                    $message = __( '未发现主题设置序列化异常。', 'developer-starter' );
                    break;
                case 'missing':
                    $message = __( '未找到主题设置记录，无法修复。', 'developer-starter' );
                    $type = 'error';
                    break;
                case 'not_serialized':
                    $message = __( '主题设置不是序列化格式，无法自动修复。', 'developer-starter' );
                    $type = 'error';
                    break;
                case 'failed':
                default:
                    $message = __( '主题设置修复失败，建议从数据库备份恢复。', 'developer-starter' );
                    $type = 'error';
                    break;
            }
            add_settings_error(
                'developer_starter_settings',
                'ds_options_repair',
                $message,
                $type
            );
        }

        if ( $settings_broken ) {
            add_settings_error(
                'developer_starter_settings',
                'ds_options_broken',
                __( '检测到主题设置序列化损坏。为避免覆盖原始数据，已临时禁用保存按钮。请先执行安全域名替换或从数据库备份恢复后再保存。', 'developer-starter' ),
                'error'
            );
        }
        ?>
        <?php
        $search_tabs = array( 'design', 'header', 'footer', 'article', 'pages', 'account_style', 'content', 'models', 'announcement', 'submit', 'smtp', 'advanced', 'translate', 'international', 'optimize', 'auth' );
        $show_search = in_array( $current_tab, $search_tabs, true );
        $show_save_top = ( 'backup' !== $current_tab );
        $primary_save_label = ( 'ai' === $current_tab ) ? __( '保存 AI 配置', 'developer-starter' ) : __( '保存设置', 'developer-starter' );
        ?>
        <div class="wrap ds-settings-wrap" data-current-tab="<?php echo esc_attr( $current_tab ); ?>" data-search-enabled="<?php echo $show_search ? '1' : '0'; ?>" data-settings-locked="<?php echo $settings_broken ? '1' : '0'; ?>">
            <div class="ds-settings-head">
                <h1><?php esc_html_e( '企业主题设置', 'developer-starter' ); ?></h1>
                <button type="button" class="button ds-theme-toggle" aria-pressed="false" aria-label="<?php esc_attr_e( '切换暗黑模式', 'developer-starter' ); ?>" title="<?php esc_attr_e( '切换暗黑模式', 'developer-starter' ); ?>">
                    <span class="ds-theme-toggle-icon ds-theme-icon-sun" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"></circle>
                            <line x1="12" y1="2" x2="12" y2="5"></line>
                            <line x1="12" y1="19" x2="12" y2="22"></line>
                            <line x1="2" y1="12" x2="5" y2="12"></line>
                            <line x1="19" y1="12" x2="22" y2="12"></line>
                            <line x1="4.2" y1="4.2" x2="6.3" y2="6.3"></line>
                            <line x1="17.7" y1="17.7" x2="19.8" y2="19.8"></line>
                            <line x1="17.7" y1="6.3" x2="19.8" y2="4.2"></line>
                            <line x1="4.2" y1="19.8" x2="6.3" y2="17.7"></line>
                        </svg>
                    </span>
                    <span class="ds-theme-toggle-icon ds-theme-icon-moon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.8A8.5 8.5 0 0 1 11.2 3a7 7 0 1 0 9.8 9.8z"></path>
                        </svg>
                    </span>
                </button>
            </div>
            <?php settings_errors(); ?>
            <?php if ( $settings_broken ) : ?>
                <script>
                (function(){
                    function lockButtons() {
                        var root = document.querySelector('.ds-settings-wrap');
                        if (!root || root.getAttribute('data-settings-locked') !== '1') return;
                        var buttons = root.querySelectorAll('.ds-save-top, .submit .button-primary, #ds-ai-save-settings, form[action="options.php"] input[type="submit"]');
                        buttons.forEach(function(btn){
                            btn.setAttribute('disabled', 'disabled');
                            btn.style.opacity = '0.6';
                            btn.style.pointerEvents = 'none';
                        });
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', lockButtons);
                    } else {
                        lockButtons();
                    }
                })();
                </script>
            <?php endif; ?>
            
            <nav class="nav-tab-wrapper ds-nav-tabs">
                <?php foreach ( $tabs as $tab_id => $tab_name ) : ?>
                    <a href="?page=developer-starter-settings&tab=<?php echo $tab_id; ?>" 
                       class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab_name ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ( 'backup' === $current_tab ) : ?>
                <table class="form-table ds-form-table" role="presentation">
                    <?php $this->render_tab_fields( $current_tab, $options ); ?>
                </table>
            <?php else : ?>
                <form method="post" action="options.php" style="margin-top: 20px;">
                    <?php settings_fields( 'developer_starter_settings' ); ?>
                    <div class="ds-settings-toolbar">
                        <?php if ( $show_search ) : ?>
                            <div class="ds-settings-search">
                                <label class="screen-reader-text" for="ds-settings-search"><?php esc_html_e( '搜索设置', 'developer-starter' ); ?></label>
                                <input type="search" id="ds-settings-search" placeholder="<?php esc_attr_e( '试试搜：按钮颜色、页脚背景、菜单位置', 'developer-starter' ); ?>" autocomplete="off" spellcheck="false" />
                                <button type="button" class="button ds-search-clear"><?php esc_html_e( '清除', 'developer-starter' ); ?></button>
                            </div>
                        <?php endif; ?>
                        <div class="ds-settings-actions">
                            <?php if ( $show_search ) : ?>
                                <div class="ds-settings-meta">
                                    <span class="ds-search-count" aria-live="polite"></span>
                                    <span class="ds-search-hint"><?php esc_html_e( '支持关键词搜索，仅查当前选项卡', 'developer-starter' ); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ( $show_save_top ) : ?>
                                <button type="submit" class="button button-primary ds-save-top"><?php echo esc_html( $primary_save_label ); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="notice notice-info inline ds-settings-save-hint">
                        <p><?php esc_html_e( '调整完成后点击保存按钮生效；当前页面只保存本选项卡内的主题设置。', 'developer-starter' ); ?></p>
                    </div>
                    <?php if ( $show_search ) : ?>
                        <?php $this->render_settings_recommended_action( $current_tab, $options ); ?>
                        <?php $this->render_settings_context_summary( $current_tab, $options ); ?>
                        <?php $this->render_settings_quick_shortcuts( $current_tab ); ?>
                    <?php endif; ?>
                    
                    <table class="form-table ds-form-table" role="presentation">
                        <?php $this->render_tab_fields( $current_tab, $options ); ?>
                    </table>
                    
                    <?php submit_button( $primary_save_label ); ?>
                </form>
            <?php endif; ?>
            
            <hr style="margin: 40px 0 20px;" />
            <h2><?php esc_html_e( '恢复默认设置', 'developer-starter' ); ?></h2>
            <p class="description"><?php esc_html_e( '如果设置出现问题，可以一键恢复所有主题设置为默认值。', 'developer-starter' ); ?></p>
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field( 'ds_reset_action', 'ds_reset_nonce' ); ?>
                <button type="submit" name="ds_reset_settings" class="button button-secondary" 
                        onclick="return confirm('<?php esc_html_e( '确定要恢复所有主题设置为默认值吗？此操作不可撤销！', 'developer-starter' ); ?>');">
                    <?php esc_html_e( '恢复默认设置', 'developer-starter' ); ?>
                </button>
            </form>
        </div>
        <?php
    }

    /**
     * 渲染当前页最建议先处理的一步。
     *
     * @param string              $tab     当前选项卡。
     * @param array<string,mixed> $options 当前设置。
     * @return void
     */
    private function render_settings_recommended_action( $tab, $options ) {
        $recommendation = $this->get_settings_recommended_action( $tab, $options );
        if ( empty( $recommendation ) || ! is_array( $recommendation ) ) {
            return;
        }

        $tone        = isset( $recommendation['tone'] ) ? sanitize_html_class( (string) $recommendation['tone'] ) : 'info';
        $title       = isset( $recommendation['title'] ) ? (string) $recommendation['title'] : '';
        $description = isset( $recommendation['description'] ) ? (string) $recommendation['description'] : '';
        $action_label = isset( $recommendation['action_label'] ) ? (string) $recommendation['action_label'] : '';
        $action_query = isset( $recommendation['action_query'] ) ? (string) $recommendation['action_query'] : '';
        $action_target = isset( $recommendation['action_target'] ) ? (string) $recommendation['action_target'] : '';

        if ( '' === $title || '' === $action_label || '' === $action_target ) {
            return;
        }

        echo '<div class="ds-settings-recommend ds-settings-recommend--' . esc_attr( $tone ) . '">';
        echo '<div class="ds-settings-recommend__copy">';
        echo '<span class="ds-settings-recommend__eyebrow">' . esc_html__( '建议操作', 'developer-starter' ) . '</span>';
        echo '<strong>' . esc_html( $title ) . '</strong>';
        if ( '' !== $description ) {
            echo '<p>' . esc_html( $description ) . '</p>';
        }
        echo '</div>';
        echo '<button type="button" class="button button-primary ds-settings-shortcut ds-settings-recommend__action" data-query="' . esc_attr( $action_query ) . '" data-target="' . esc_attr( $action_target ) . '">' . esc_html( $action_label ) . '</button>';
        echo '</div>';
    }

    /**
     * 获取当前页推荐先做的一步。
     *
     * @param string              $tab     当前选项卡。
     * @param array<string,mixed> $options 当前设置。
     * @return array<string,string>
     */
    private function get_settings_recommended_action( $tab, $options ) {
        switch ( $tab ) {
            case 'design':
                if ( ! $this->is_settings_option_enabled( $options, 'design_enable_global_tokens', '1' ) ) {
                    return array(
                        'tone'          => 'warning',
                        'title'         => __( '开启全局设计', 'developer-starter' ),
                        'description'   => __( '开启后，按钮、卡片、页头、页脚可统一维护。', 'developer-starter' ),
                        'action_label'  => __( '定位到开关', 'developer-starter' ),
                        'action_query'  => __( '全局设计', 'developer-starter' ),
                        'action_target' => 'setting-row-design_enable_global_tokens',
                    );
                }
                return array(
                    'tone'          => 'info',
                    'title'         => __( '设置主题色或风格预设', 'developer-starter' ),
                    'description'   => __( '品牌主色和风格预设会影响整站基础视觉。', 'developer-starter' ),
                    'action_label'  => __( '定位到主题色', 'developer-starter' ),
                    'action_query'  => __( '主题色', 'developer-starter' ),
                    'action_target' => 'setting-row-design_primary_color',
                );

            case 'header':
                $header_variant = $this->get_settings_option_text( $options, 'header_style', '' );
                if ( '' !== $header_variant && 'default' !== $header_variant ) {
                    return array(
                        'tone'          => 'warning',
                        'title'         => __( '确认头部变体设置', 'developer-starter' ),
                        'description'   => __( '当前存在头部变体标识。无特殊需求时，可改回 default 或留空。', 'developer-starter' ),
                        'action_label'  => __( '定位到头部变体', 'developer-starter' ),
                        'action_query'  => __( '头部变体', 'developer-starter' ),
                        'action_target' => 'setting-row-header_style',
                    );
                }
                return array(
                    'tone'          => 'info',
                    'title'         => __( '设置菜单位置和透明头部', 'developer-starter' ),
                    'description'   => __( '页头这页主要改结构和开关，颜色样式继续去全局设计里改。', 'developer-starter' ),
                    'action_label'  => __( '定位到菜单位置', 'developer-starter' ),
                    'action_query'  => __( '菜单位置', 'developer-starter' ),
                    'action_target' => 'setting-row-header_menu_layout',
                );

            case 'footer':
                $footer_enabled  = $this->is_settings_option_enabled( $options, 'footer_builder_enable', '' );
                $footer_page_id  = $this->get_settings_option_text( $options, 'footer_builder_page_id', '' );
                $footer_region_page_ids = array_filter( array(
                    $this->get_settings_option_text( $options, 'footer_builder_main_page_id', '' ),
                    $this->get_settings_option_text( $options, 'footer_builder_friend_page_id', '' ),
                    $this->get_settings_option_text( $options, 'footer_builder_bottom_page_id', '' ),
                ) );
                $company_name    = $this->get_settings_option_text( $options, 'company_name', '' );
                $company_phone   = $this->get_settings_option_text( $options, 'company_phone', '' );

                if ( $footer_enabled && '' === $footer_page_id && empty( $footer_region_page_ids ) ) {
                    return array(
                        'tone'          => 'warning',
                        'title'         => __( '选择页脚装修页面', 'developer-starter' ),
                        'description'   => __( '页脚装修已开启，但尚未指定接管页面。', 'developer-starter' ),
                        'action_label'  => __( '定位到页面选择', 'developer-starter' ),
                        'action_query'  => __( '页脚页面', 'developer-starter' ),
                        'action_target' => 'setting-row-footer_builder_page_id',
                    );
                }
                if ( ! $footer_enabled && ( '' === $company_name || '' === $company_phone ) ) {
                    return array(
                        'tone'          => 'info',
                        'title'         => __( '补全默认页脚联系信息', 'developer-starter' ),
                        'description'   => __( '当前还是默认页脚内容在生效，企业名称和联系电话最好先补上。', 'developer-starter' ),
                        'action_label'  => '' === $company_name ? __( '定位到企业名称', 'developer-starter' ) : __( '定位到联系电话', 'developer-starter' ),
                        'action_query'  => '' === $company_name ? __( '公司名称', 'developer-starter' ) : __( '联系电话', 'developer-starter' ),
                        'action_target' => '' === $company_name ? 'setting-row-company_name' : 'setting-row-company_phone',
                    );
                }
                return array(
                    'tone'          => 'success',
                    'title'         => __( '确认页脚各区域接管方式', 'developer-starter' ),
                    'description'   => __( '关于联系、友情链接、版权备案可分别选择装修页面，未接管区域继续使用默认内容。', 'developer-starter' ),
                    'action_label'  => __( '定位到独立区域装修', 'developer-starter' ),
                    'action_query'  => __( '页脚独立区域装修', 'developer-starter' ),
                    'action_target' => 'setting-row-footer_builder_main_page_id',
                );

            case 'pages':
                $search_builder_enabled = $this->is_settings_option_enabled( $options, 'search_builder_enable', '' );
                $search_page_id         = $this->get_settings_option_text( $options, 'search_builder_page_id', '' );
                $error_builder_enabled  = $this->is_settings_option_enabled( $options, 'error_404_builder_enable', '' );
                $error_page_id          = $this->get_settings_option_text( $options, 'error_404_builder_page_id', '' );

                if ( $search_builder_enabled && '' === $search_page_id ) {
                    return array(
                        'tone'          => 'warning',
                        'title'         => __( '选择搜索页装修页面', 'developer-starter' ),
                        'description'   => __( '搜索页装修已经开启，但还没指定具体用哪一个装修页面。', 'developer-starter' ),
                        'action_label'  => __( '定位到页面选择', 'developer-starter' ),
                        'action_query'  => __( '搜索页装修页面', 'developer-starter' ),
                        'action_target' => 'setting-row-search_builder_page_id',
                    );
                }
                if ( $error_builder_enabled && '' === $error_page_id ) {
                    return array(
                        'tone'          => 'warning',
                        'title'         => __( '选择 404 完整装修页面', 'developer-starter' ),
                        'description'   => __( '404 完整装修页接管已经开启，但还没指定由哪一个页面来接管。', 'developer-starter' ),
                        'action_label'  => __( '定位到页面选择', 'developer-starter' ),
                        'action_query'  => __( '404 完整装修页面', 'developer-starter' ),
                        'action_target' => 'setting-row-error_404_builder_page_id',
                    );
                }
                return array(
                    'tone'          => 'info',
                    'title'         => __( '确认搜索页和 404 装修', 'developer-starter' ),
                    'description'   => __( '这页适合做结果页、错误页这类“特殊页面”的额外引导；404 可用简易装修，也可指定完整装修页面接管。', 'developer-starter' ),
                    'action_label'  => __( '定位到 404 简易装修', 'developer-starter' ),
                    'action_query'  => __( '404 简易装修', 'developer-starter' ),
                    'action_target' => 'setting-row-error_404_preset',
                );
        }

        return array();
    }

    /**
     * 渲染当前选项卡的状态摘要。
     *
     * @param string               $tab     当前选项卡。
     * @param array<string,mixed>  $options 当前设置。
     * @return void
     */
    private function render_settings_context_summary( $tab, $options ) {
        $cards = $this->get_settings_context_summary_cards( $tab, $options );
        if ( empty( $cards ) || ! is_array( $cards ) ) {
            return;
        }

        echo '<div class="ds-settings-context" aria-label="' . esc_attr__( '当前状态', 'developer-starter' ) . '">';
        echo '<div class="ds-settings-context__head">';
        echo '<strong>' . esc_html__( '当前状态', 'developer-starter' ) . '</strong>';
        echo '<span>' . esc_html__( '显示当前面板状态和关键入口。', 'developer-starter' ) . '</span>';
        echo '</div>';
        echo '<div class="ds-settings-context__grid">';
        foreach ( $cards as $card ) {
            $tone         = isset( $card['tone'] ) ? sanitize_html_class( (string) $card['tone'] ) : 'info';
            $title        = isset( $card['title'] ) ? (string) $card['title'] : '';
            $description  = isset( $card['description'] ) ? (string) $card['description'] : '';
            $action_label = isset( $card['action_label'] ) ? (string) $card['action_label'] : '';
            $action_query = isset( $card['action_query'] ) ? (string) $card['action_query'] : '';
            $action_target = isset( $card['action_target'] ) ? (string) $card['action_target'] : '';

            if ( '' === $title ) {
                continue;
            }

            echo '<div class="ds-settings-context__card ds-settings-context__card--' . esc_attr( $tone ) . '">';
            echo '<strong>' . esc_html( $title ) . '</strong>';
            if ( '' !== $description ) {
                echo '<p>' . esc_html( $description ) . '</p>';
            }
            if ( '' !== $action_label && '' !== $action_target ) {
                echo '<button type="button" class="button-link ds-settings-shortcut ds-settings-context__action" data-query="' . esc_attr( $action_query ) . '" data-target="' . esc_attr( $action_target ) . '">' . esc_html( $action_label ) . '</button>';
            }
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    /**
     * 获取当前选项卡的状态摘要卡片。
     *
     * @param string              $tab     当前选项卡。
     * @param array<string,mixed> $options 当前设置。
     * @return array<int,array<string,string>>
     */
    private function get_settings_context_summary_cards( $tab, $options ) {
        $cards = array();

        switch ( $tab ) {
            case 'design':
                $design_enabled = $this->is_settings_option_enabled( $options, 'design_enable_global_tokens', '1' );
                $preset_key     = isset( $options['design_preset'] ) && is_string( $options['design_preset'] ) && '' !== $options['design_preset']
                    ? $options['design_preset']
                    : 'default';
                $preset_choices = class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? \Developer_Starter\Core\Design_Tokens::get_preset_choices( $options )
                    : array();
                $preset_label   = isset( $preset_choices[ $preset_key ] ) ? (string) $preset_choices[ $preset_key ] : $preset_key;

                $cards[] = array(
                    'tone'         => $design_enabled ? 'success' : 'warning',
                    'title'        => $design_enabled ? __( '全局设计已启用', 'developer-starter' ) : __( '全局设计暂未启用', 'developer-starter' ),
                    'description'  => $design_enabled
                        ? __( '按钮、卡片、页头、页脚统一样式优先跟随这里。', 'developer-starter' )
                        : __( '建议开启全局设计，减少装修项分散维护。', 'developer-starter' ),
                    'action_label' => $design_enabled ? __( '定位到主题色', 'developer-starter' ) : __( '定位到开关', 'developer-starter' ),
                    'action_query' => $design_enabled ? __( '主题色', 'developer-starter' ) : __( '全局设计', 'developer-starter' ),
                    'action_target'=> 'setting-row-design_enable_global_tokens',
                );
                $cards[] = array(
                    'tone'         => 'info',
                    'title'        => sprintf( __( '当前预设：%s', 'developer-starter' ), $preset_label ),
                    'description'  => __( '风格预设可快速切换基础视觉方案。', 'developer-starter' ),
                    'action_label' => __( '定位到预设', 'developer-starter' ),
                    'action_query' => __( '风格预设', 'developer-starter' ),
                    'action_target'=> 'setting-row-design_preset',
                );
                break;

            case 'header':
                $header_variant = isset( $options['header_style'] ) && is_string( $options['header_style'] )
                    ? trim( $options['header_style'] )
                    : '';
                $sticky_enabled = $this->is_settings_option_enabled( $options, 'header_sticky', '1' );
                $transparent_enabled = $this->is_settings_option_enabled( $options, 'header_transparent_home', '' );
                $phone_hidden = $this->is_settings_option_enabled( $options, 'hide_phone_header', '' );

                if ( '' !== $header_variant && 'default' !== $header_variant ) {
                    $cards[] = array(
                        'tone'         => 'warning',
                        'title'        => __( '当前使用头部变体', 'developer-starter' ),
                        'description'  => __( '当前站点存在头部变体标识。无特殊需求时，可改回 default 或留空。', 'developer-starter' ),
                        'action_label' => __( '定位到头部变体', 'developer-starter' ),
                        'action_query' => __( '头部变体', 'developer-starter' ),
                        'action_target'=> 'setting-row-header_style',
                    );
                } else {
                    $cards[] = array(
                        'tone'         => 'success',
                        'title'        => __( '当前页头按新入口维护', 'developer-starter' ),
                        'description'  => __( '结构行为在这里调整，颜色样式在全局设计中维护。', 'developer-starter' ),
                        'action_label' => __( '定位到菜单位置', 'developer-starter' ),
                        'action_query' => __( '菜单位置', 'developer-starter' ),
                        'action_target'=> 'setting-row-header_menu_layout',
                    );
                }

                $header_state = array();
                $header_state[] = $sticky_enabled ? __( '固定头部已开', 'developer-starter' ) : __( '固定头部已关', 'developer-starter' );
                $header_state[] = $transparent_enabled ? __( '首页透明已开', 'developer-starter' ) : __( '首页透明已关', 'developer-starter' );
                $header_state[] = $phone_hidden ? __( '电话入口已隐藏', 'developer-starter' ) : __( '电话入口会显示', 'developer-starter' );

                $cards[] = array(
                    'tone'         => 'info',
                    'title'        => implode( ' / ', $header_state ),
                    'description'  => __( '这页更适合改结构和开关；页头背景、导航颜色、电话按钮颜色请去全局设计。', 'developer-starter' ),
                    'action_label' => __( '定位到固定头部', 'developer-starter' ),
                    'action_query' => __( '固定头部', 'developer-starter' ),
                    'action_target'=> 'setting-row-header_sticky',
                );
                break;

            case 'footer':
                $footer_enabled  = $this->is_settings_option_enabled( $options, 'footer_builder_enable', '' );
                $footer_position = isset( $options['footer_builder_position'] ) && is_string( $options['footer_builder_position'] ) && '' !== $options['footer_builder_position']
                    ? $options['footer_builder_position']
                    : 'replace_widgets';

                if ( ! $footer_enabled ) {
                    $cards[] = array(
                        'tone'         => 'info',
                        'title'        => __( '当前还是默认页脚内容', 'developer-starter' ),
                        'description'  => __( '企业信息、备案、联系方式这些字段会继续直接在这里生效。', 'developer-starter' ),
                        'action_label' => __( '定位到企业名称', 'developer-starter' ),
                        'action_query' => __( '公司名称', 'developer-starter' ),
                        'action_target'=> 'setting-row-company_name',
                    );
                } elseif ( 'replace_all' === $footer_position ) {
                    $cards[] = array(
                        'tone'         => 'success',
                        'title'        => __( '装修页已接管整个页脚', 'developer-starter' ),
                        'description'  => __( '默认页脚字段仍完整展示，方便维护备用内容和切换接管范围。', 'developer-starter' ),
                        'action_label' => __( '定位到接管范围', 'developer-starter' ),
                        'action_query' => __( '替换整个页脚', 'developer-starter' ),
                        'action_target'=> 'setting-row-footer_builder_position',
                    );
                } else {
                    $cards[] = array(
                        'tone'         => 'warning',
                        'title'        => __( '当前只接管了部分页脚区域', 'developer-starter' ),
                        'description'  => __( '说明装修页和默认页脚内容会一起出现，适合局部替换。', 'developer-starter' ),
                        'action_label' => __( '定位到接管范围', 'developer-starter' ),
                        'action_query' => __( '页脚接管范围', 'developer-starter' ),
                        'action_target'=> 'setting-row-footer_builder_position',
                    );
                }

                $cards[] = array(
                    'tone'         => 'info',
                    'title'        => __( '页脚颜色样式不在这里改', 'developer-starter' ),
                    'description'  => __( '页脚背景、标题、链接颜色已经统一收口到全局设计。这里主要放内容和接管关系。', 'developer-starter' ),
                    'action_label' => __( '定位到联系电话', 'developer-starter' ),
                    'action_query' => __( '联系电话', 'developer-starter' ),
                    'action_target'=> 'setting-row-company_phone',
                );
                break;

            case 'pages':
                $search_builder_enabled = $this->is_settings_option_enabled( $options, 'search_builder_enable', '' );
                $error_builder_enabled  = $this->is_settings_option_enabled( $options, 'error_404_builder_enable', '' );

                $cards[] = array(
                    'tone'         => $search_builder_enabled ? 'success' : 'info',
                    'title'        => $search_builder_enabled ? __( '搜索页装修已启用', 'developer-starter' ) : __( '搜索页装修还没启用', 'developer-starter' ),
                    'description'  => __( '适合给搜索结果页加 Banner、筛选提示或说明区块。原生结果列表会继续保留。', 'developer-starter' ),
                    'action_label' => __( '定位到搜索页', 'developer-starter' ),
                    'action_query' => __( '搜索页装修', 'developer-starter' ),
                    'action_target'=> 'setting-row-search_builder_enable',
                );
                $cards[] = array(
                    'tone'         => $error_builder_enabled ? 'success' : 'info',
                    'title'        => $error_builder_enabled ? __( '404 完整装修已启用', 'developer-starter' ) : __( '404 使用简易装修', 'developer-starter' ),
                    'description'  => __( '默认 404 可快速调整文案、配色、背景图和搜索入口；需要更复杂布局时再启用完整装修页面。', 'developer-starter' ),
                    'action_label' => __( '定位到 404', 'developer-starter' ),
                    'action_query' => __( '404 简易装修', 'developer-starter' ),
                    'action_target'=> 'setting-row-error_404_preset',
                );
                break;
        }

        return $cards;
    }

    /**
     * 判断某个后台开关是否处于开启状态。
     *
     * @param array<string,mixed> $options  当前设置。
     * @param string              $key      字段键名。
     * @param mixed               $default  默认值。
     * @return bool
     */
    private function is_settings_option_enabled( $options, $key, $default = '' ) {
        $value = array_key_exists( $key, $options ) ? $options[ $key ] : $default;
        return in_array( $value, array( true, 1, '1', 'yes', 'on', 'true' ), true );
    }

    /**
     * 获取某个设置项的文本值。
     *
     * @param array<string,mixed> $options 当前设置。
     * @param string              $key     字段键名。
     * @param string              $default 默认值。
     * @return string
     */
    private function get_settings_option_text( $options, $key, $default = '' ) {
        if ( ! array_key_exists( $key, $options ) ) {
            return $default;
        }

        if ( is_scalar( $options[ $key ] ) ) {
            return trim( (string) $options[ $key ] );
        }

        return $default;
    }

    /**
     * 渲染当前选项卡的常用快捷操作。
     *
     * @param string $tab 当前选项卡。
     * @return void
     */
    private function render_settings_quick_shortcuts( $tab ) {
        $shortcuts = $this->get_settings_quick_shortcuts( $tab );
        if ( empty( $shortcuts ) || ! is_array( $shortcuts ) ) {
            return;
        }

        echo '<div class="ds-settings-shortcuts" aria-label="' . esc_attr__( '常用操作', 'developer-starter' ) . '">';
        echo '<span class="ds-settings-shortcuts__label">' . esc_html__( '常用操作', 'developer-starter' ) . '</span>';
        echo '<div class="ds-settings-shortcuts__list">';
        echo '<button type="button" class="button-link ds-settings-shortcut ds-settings-shortcut--all" data-reset-search="1" title="' . esc_attr__( '显示全部设置', 'developer-starter' ) . '" aria-label="' . esc_attr__( '显示全部设置', 'developer-starter' ) . '">' . esc_html__( '全部', 'developer-starter' ) . '</button>';
        foreach ( $shortcuts as $shortcut ) {
            if ( empty( $shortcut['label'] ) ) {
                continue;
            }

            $query  = isset( $shortcut['query'] ) ? (string) $shortcut['query'] : '';
            $target = isset( $shortcut['target'] ) ? (string) $shortcut['target'] : '';
            $hint   = isset( $shortcut['hint'] ) ? (string) $shortcut['hint'] : '';

            echo '<button type="button" class="button-link ds-settings-shortcut" data-query="' . esc_attr( $query ) . '" data-target="' . esc_attr( $target ) . '"';
            if ( '' !== $hint ) {
                echo ' title="' . esc_attr( $hint ) . '" aria-label="' . esc_attr( $hint ) . '"';
            }
            echo '>' . esc_html( (string) $shortcut['label'] ) . '</button>';
        }
        echo '</div>';
        echo '</div>';
    }

    /**
     * 获取当前选项卡的常用快捷操作。
     *
     * @param string $tab 当前选项卡。
     * @return array<int,array<string,string>>
     */
    private function get_settings_quick_shortcuts( $tab ) {
        $map = array(
            'design' => array(
                array(
                    'label'  => __( '品牌主色', 'developer-starter' ),
                    'query'  => __( '主题色', 'developer-starter' ),
                    'target' => 'setting-row-design_primary_color',
                    'hint'   => __( '直接定位到品牌主色设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '按钮颜色', 'developer-starter' ),
                    'query'  => __( '按钮颜色', 'developer-starter' ),
                    'target' => 'setting-row-design_component_button_bg',
                    'hint'   => __( '直接定位到按钮背景设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '导航颜色', 'developer-starter' ),
                    'query'  => __( '导航颜色', 'developer-starter' ),
                    'target' => 'setting-row-design_component_nav_link',
                    'hint'   => __( '直接定位到桌面导航文字设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '页头背景', 'developer-starter' ),
                    'query'  => __( '页头背景', 'developer-starter' ),
                    'target' => 'setting-row-design_component_header_bg',
                    'hint'   => __( '直接定位到页头背景设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '页脚背景', 'developer-starter' ),
                    'query'  => __( '页脚背景', 'developer-starter' ),
                    'target' => 'setting-row-design_component_footer_bg',
                    'hint'   => __( '直接定位到页脚背景设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '标签颜色', 'developer-starter' ),
                    'query'  => __( '标签激活颜色', 'developer-starter' ),
                    'target' => 'setting-row-design_component_tabs_active_bg',
                    'hint'   => __( '直接定位到标签页激活背景设置。', 'developer-starter' ),
                ),
            ),
            'header' => array(
                array(
                    'label'  => __( '网站 Logo', 'developer-starter' ),
                    'query'  => __( '网站 Logo', 'developer-starter' ),
                    'target' => 'setting-row-site_logo',
                    'hint'   => __( '直接定位到网站 Logo 设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '菜单位置', 'developer-starter' ),
                    'query'  => __( '菜单位置', 'developer-starter' ),
                    'target' => 'setting-row-header_menu_layout',
                    'hint'   => __( '直接定位到桌面端菜单布局设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '固定头部', 'developer-starter' ),
                    'query'  => __( '固定头部', 'developer-starter' ),
                    'target' => 'setting-row-header_sticky',
                    'hint'   => __( '直接定位到固定头部开关。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '透明头部', 'developer-starter' ),
                    'query'  => __( '透明头部', 'developer-starter' ),
                    'target' => 'setting-row-header_transparent_home',
                    'hint'   => __( '直接定位到首页顶部透明开关。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '登录按钮', 'developer-starter' ),
                    'query'  => __( '登录按钮', 'developer-starter' ),
                    'target' => 'setting-row-header_login_enable',
                    'hint'   => __( '直接定位到登录按钮开关。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '头部变体', 'developer-starter' ),
                    'query'  => __( '头部变体', 'developer-starter' ),
                    'target' => 'setting-row-header_style',
                    'hint'   => __( '直接定位到头部变体标识。', 'developer-starter' ),
                ),
            ),
            'footer' => array(
                array(
                    'label'  => __( '启用页脚装修', 'developer-starter' ),
                    'query'  => __( '页脚装修', 'developer-starter' ),
                    'target' => 'setting-row-footer_builder_enable',
                    'hint'   => __( '直接定位到页脚装修开关。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '接管范围', 'developer-starter' ),
                    'query'  => __( '替换整个页脚', 'developer-starter' ),
                    'target' => 'setting-row-footer_builder_position',
                    'hint'   => __( '直接定位到页脚接管范围设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '企业名称', 'developer-starter' ),
                    'query'  => __( '公司名称', 'developer-starter' ),
                    'target' => 'setting-row-company_name',
                    'hint'   => __( '直接定位到企业名称字段。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '联系电话', 'developer-starter' ),
                    'query'  => __( '联系电话', 'developer-starter' ),
                    'target' => 'setting-row-company_phone',
                    'hint'   => __( '直接定位到联系电话字段。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '联系地址', 'developer-starter' ),
                    'query'  => __( '联系地址', 'developer-starter' ),
                    'target' => 'setting-row-company_address',
                    'hint'   => __( '直接定位到企业地址字段。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '工作时间', 'developer-starter' ),
                    'query'  => __( '营业时间', 'developer-starter' ),
                    'target' => 'setting-row-company_working_hours',
                    'hint'   => __( '直接定位到工作时间字段。', 'developer-starter' ),
                ),
            ),
            'pages' => array(
                array(
                    'label'  => __( '搜索页装修', 'developer-starter' ),
                    'query'  => __( '搜索页装修', 'developer-starter' ),
                    'target' => 'setting-row-search_builder_enable',
                    'hint'   => __( '直接定位到搜索页装修开关。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '404 简易装修', 'developer-starter' ),
                    'query'  => __( '404 简易装修', 'developer-starter' ),
                    'target' => 'setting-row-error_404_preset',
                    'hint'   => __( '直接定位到 404 默认页面的文案与风格设置。', 'developer-starter' ),
                ),
                array(
                    'label'  => __( '404 完整接管', 'developer-starter' ),
                    'query'  => __( '404 完整装修', 'developer-starter' ),
                    'target' => 'setting-row-error_404_builder_enable',
                    'hint'   => __( '直接定位到 404 完整装修页面开关。', 'developer-starter' ),
                ),
            ),
        );

        return isset( $map[ $tab ] ) ? $map[ $tab ] : array();
    }

    private function render_tab_fields( $tab, $options ) {
        // 遥测由后台页脚的异步 AJAX 发送，设置页渲染阶段不做阻塞式外网请求。

        // 首先尝试使用配置驱动渲染
        if ( $this->render_fields_from_config( $tab, $options ) ) {
            return;
        }
        
        // 如果配置不存在，直接返回（所有选项卡已迁移到配置驱动）
        return;
    }

    /**
     * 渲染分组标题
     * 
     * @param array $field 字段配置
     */
    private function render_section( $field, $row_attr = '' ) {
        $title = isset( $field['title'] ) ? $field['title'] : '';
        $desc = isset( $field['desc'] ) ? $field['desc'] : '';
        
        echo '<tr' . $row_attr . '><th colspan="2"><h2 class="ds-section-title">' . esc_html( $title ) . '</h2>';
        if ( $desc ) {
            echo '<p class="description">' . wp_kses_post( $desc ) . '</p>';
        }
        echo '</th></tr>';
    }

    /**
     * 根据配置渲染单个字段
     * 
     * @param array $field 字段配置
     * @param array $options 当前选项值
     */
    private function render_field_by_config( $field, $options ) {
        $field = $this->normalize_field_config_for_render( $field );
        $type = isset( $field['type'] ) ? $field['type'] : 'text';
        $row_attr = $this->get_row_attr( $field, $options );
        
        switch ( $type ) {
            case 'section':
                $this->render_section( $field, $row_attr );
                break;
            case 'text':
                $this->field_text( $field['id'], $field['label'], $options, $field['desc'] ?? '', $field['default'] ?? '', $field['input_type'] ?? 'text', $field['attrs'] ?? array(), $row_attr );
                break;
            case 'number':
                $this->field_number( $field['id'], $field['label'], $options, $field['desc'] ?? '', $field['default'] ?? '', $field['attrs'] ?? array(), $field['suffix'] ?? '', $row_attr );
                break;
            case 'textarea':
                $this->field_textarea( $field['id'], $field['label'], $options, $field['desc'] ?? '', $field['default'] ?? '', $field['attrs'] ?? array(), $row_attr );
                break;
            case 'image':
                $this->field_image( $field['id'], $field['label'], $options, $field['desc'] ?? '', $field['default'] ?? '', $field['preview_style'] ?? '', $row_attr );
                break;
            case 'file':
                $this->field_file( $field['id'], $field['label'], $options, $field['desc'] ?? '', $field['default'] ?? '', $field['attrs'] ?? array(), $field['button_label'] ?? '', $row_attr );
                break;
            case 'color':
                $this->field_color( $field['id'], $field['label'], $options, $field['default'] ?? '', $row_attr, $field['desc'] ?? '' );
                break;
            case 'password':
                $this->field_password( $field['id'], $field['label'], $options, $field['desc'] ?? '', $row_attr );
                break;
            case 'checkbox':
                $this->field_checkbox( $field['id'], $field['label'], $options, $field['desc'] ?? '', $field['default'] ?? null, $row_attr );
                break;
            case 'select':
                $this->field_select( $field['id'], $field['label'], $options, $field['choices'] ?? array(), $field['desc'] ?? '', $field['default'] ?? '', $field['attrs'] ?? array(), $row_attr );
                break;
            case 'repeater':
                $this->field_repeater( $field['id'], $field['label'], $options, $field['fields'] ?? array(), $field['desc'] ?? '', $field['default'] ?? array(), $row_attr );
                break;
            case 'checkbox_group':
                $this->field_checkbox_group( $field['id'], $field['label'], $options, $field['choices'] ?? array(), $field['desc'] ?? '', $field['args'] ?? array(), $row_attr, $field['default'] ?? array() );
                break;
            case 'page_id':
                $this->field_page_id( $field['id'], $field['label'], $options, $field['desc'] ?? '', $row_attr );
                break;
            case 'note':
                $this->field_note( $field['content'] ?? '', $field['style'] ?? '', $row_attr );
                break;
            case 'custom':
                // 调用自定义回调函数
                if ( isset( $field['callback'] ) && is_callable( $field['callback'] ) ) {
                    call_user_func( $field['callback'], $options );
                }
                break;
        }
    }

    /**
     * @param array<string,mixed> $field
     * @return array<string,mixed>
     */
    private function normalize_field_config_for_render( $field ) {
        if ( ! is_array( $field ) ) {
            return array();
        }

        $type = isset( $field['type'] ) ? (string) $field['type'] : 'text';
        if ( 'text' !== $type || empty( $field['id'] ) ) {
            return $field;
        }

        $placeholder = isset( $field['attrs']['placeholder'] ) ? trim( (string) $field['attrs']['placeholder'] ) : '';
        if ( '' === $placeholder || ! preg_match( '/^#(?:[0-9a-fA-F]{3,8})$/', $placeholder ) ) {
            return $field;
        }

        $id = sanitize_key( (string) $field['id'] );
        if ( 0 !== strpos( $id, 'design_' ) ) {
            return $field;
        }

        $field['type'] = 'color';
        if ( ! isset( $field['default'] ) ) {
            $field['default'] = '';
        }

        return $field;
    }
    
    /**
     * 根据配置渲染整个选项卡的字段
     * 
     * @param string $tab 选项卡ID
     * @param array $options 当前选项值
     * @return bool 是否成功渲染（配置存在）
     */
    private function render_fields_from_config( $tab, $options ) {
        $config = $this->get_fields_config();
        
        if ( ! isset( $config[ $tab ] ) ) {
            return false;
        }
        
        foreach ( $config[ $tab ] as $field ) {
            $this->render_field_by_config( $field, $options );
        }
        
        return true;
    }

    private function render_license_tab( $options ) {
        echo '<tr><th colspan="2"><h2>' . __( '正版授权', 'developer-starter' ) . '</h2><p class="description">' . __( '在此输入您的授权密钥以验证正版状态', 'developer-starter' ) . '</p></th></tr>';
        
        $status = \Developer_Starter\Core\Theme_License::get_status();
        $status_label = '';
        $status_color = '';
        $is_authorized = ( $status === 'valid' );
        
        switch ( $status ) {
            case 'valid':
                $status_label = __( '已授权 (正版)', 'developer-starter' );
                $status_color = '#10b981'; // Green
                break;
            case 'invalid':
                $status_label = __( '未授权 / 域名不匹配', 'developer-starter' );
                $status_color = '#ef4444'; // Red
                break;
            default:
                $status_label = __( '未验证', 'developer-starter' );
                $status_color = '#f59e0b'; // Amber
                break;
        }

        echo '<tr><th scope="row">' . __( '当前状态', 'developer-starter' ) . '</th>';
        echo '<td><span id="qiling-license-status" style="display:inline-block;padding:4px 12px;border-radius:99px;color:white;background-color:' . $status_color . ';font-weight:bold;">' . $status_label . '</span></td></tr>';

        // 授权密钥输入框 - 授权后变为只读
        $license_key = isset( $options['theme_license_key'] ) ? $options['theme_license_key'] : '';
        $readonly_attr = $is_authorized ? 'readonly' : '';
        $input_style = $is_authorized ? 'background-color:#f1f5f9;cursor:not-allowed;' : '';
        
        echo '<tr><th scope="row">' . __( '授权密钥', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<input type="text" id="qiling-license-key-input" name="' . $this->option_name . '[theme_license_key]" value="' . esc_attr( $license_key ) . '" class="regular-text" ' . $readonly_attr . ' style="' . $input_style . '" />';
        echo '<p class="description">' . __( '请前往官网绑定域名后获取密钥。绑定后，即使授权服务器无法连接，授权依然有效。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
        
        // 授权操作按钮区
        echo '<tr><th scope="row">' . __( '授权操作', 'developer-starter' ) . '</th>';
        echo '<td>';
        
        // 授权验证按钮
        $verify_disabled = $is_authorized ? 'disabled' : '';
        $verify_style = $is_authorized ? 'opacity:0.5;cursor:not-allowed;' : '';
        echo '<button type="button" id="qiling-verify-license-btn" class="button button-primary" ' . $verify_disabled . ' style="margin-right:10px;' . $verify_style . '">' . __( '授权验证', 'developer-starter' ) . '</button>';
        
        // 变更授权按钮 - 仅授权后显示
        if ( $is_authorized ) {
            echo '<button type="button" id="qiling-change-license-btn" class="button button-secondary">' . __( '变更授权', 'developer-starter' ) . '</button>';
        }
        
        echo '<span id="qiling-verify-status" style="margin-left:15px;"></span>';
        echo '</td></tr>';
        
        echo '<tr><td colspan="2"><hr/></td></tr>';
        echo '<tr><th scope="row">' . __( '获取帮助', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<p>' . __( '1. 即使授权服务器宕机，您的前台网站也不会受到任何影响。', 'developer-starter' ) . '</p>';
        echo '<p>' . __( '2. 如果更换了域名，请在官网解绑旧域名并绑定新域名。', 'developer-starter' ) . '</p>';
        echo '<p>' . __( '3. 如果授权成功了，但是后面授权掉了，可能是被取消授权，需要联系我处理。', 'developer-starter' ) . '</p>';
        echo '<p>' . __( '4. 主题代码与随包素材遵循 GPL v2 或更高版本；授权密钥仅用于获取更新与支持服务。', 'developer-starter' ) . '</p>';
        echo '</td></tr>';
        
        // 添加授权验证 JavaScript
        ?>
        <script>
        jQuery(document).ready(function($) {
            var $input = $('#qiling-license-key-input');
            var $verifyBtn = $('#qiling-verify-license-btn');
            var $changeBtn = $('#qiling-change-license-btn');
            var $status = $('#qiling-verify-status');
            var $statusBadge = $('#qiling-license-status');
            
            // 变更授权按钮点击 - 解锁输入框
            $changeBtn.on('click', function() {
                $input.prop('readonly', false).css({
                    'background-color': '',
                    'cursor': ''
                });
                $verifyBtn.prop('disabled', false).css({
                    'opacity': '',
                    'cursor': ''
                });
                $(this).hide();
                $status.html('<span style="color:#f59e0b;">请输入新密钥后点击"授权验证"</span>');
            });
            
            // 授权验证按钮点击
            $verifyBtn.on('click', function() {
                var key = $input.val().trim();
                if (!key) {
                    $status.html('<span style="color:#ef4444;">请先输入授权密钥</span>');
                    return;
                }
                
                $verifyBtn.prop('disabled', true);
                $status.html('<span style="color:#64748b;">验证中...</span>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qiling_manual_verify_license',
                        license_key: key,
                        _ajax_nonce: <?php echo wp_json_encode( wp_create_nonce( "qiling_verify_license_nonce" ) ); ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<span style="color:#10b981;">✓ ' + response.data.message + '</span>');
                            $statusBadge.css('background-color', '#10b981').text('<?php echo esc_js( __( "已授权 (正版)", "developer-starter" ) ); ?>');
                            // 锁定输入框
                            $input.prop('readonly', true).css({
                                'background-color': '#f1f5f9',
                                'cursor': 'not-allowed'
                            });
                            $verifyBtn.css({
                                'opacity': '0.5',
                                'cursor': 'not-allowed'
                            });
                            // 显示变更按钮
                            if ($changeBtn.length === 0) {
                                $verifyBtn.after('<button type="button" id="qiling-change-license-btn" class="button button-secondary" style="margin-left:10px;"><?php echo esc_js( __( "变更授权", "developer-starter" ) ); ?></button>');
                                // 重新绑定事件
                                $('#qiling-change-license-btn').on('click', function() {
                                    $input.prop('readonly', false).css({
                                        'background-color': '',
                                        'cursor': ''
                                    });
                                    $verifyBtn.prop('disabled', false).css({
                                        'opacity': '',
                                        'cursor': ''
                                    });
                                    $(this).hide();
                                    $status.html('<span style="color:#f59e0b;">请输入新密钥后点击"授权验证"</span>');
                                });
                            } else {
                                $changeBtn.show();
                            }
                        } else {
                            $status.html('<span style="color:#ef4444;">✗ ' + response.data.message + '</span>');
                            $statusBadge.css('background-color', '#ef4444').text('<?php echo esc_js( __( "未授权 / 域名不匹配", "developer-starter" ) ); ?>');
                            $verifyBtn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        $status.html('<span style="color:#ef4444;">网络错误，请重试</span>');
                        $verifyBtn.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }

    private function render_documentation_tab( $options = null ) {
        global $wpdb;
        $post_interactions_table_name = class_exists( 'Developer_Starter\\Core\\Post_Interaction_Manager' )
            ? \Developer_Starter\Core\Post_Interaction_Manager::table_name()
            : $wpdb->prefix . 'developer_starter_post_interactions';
        $tables = array(
            array(
                'key'  => 'messages',
                'name' => $wpdb->prefix . 'developer_starter_messages',
                'desc' => __( '主题内置留言表（留言系统）', 'developer-starter' ),
            ),
            array(
                'key'  => 'careers_positions',
                'name' => $wpdb->prefix . 'ds_careers_positions',
                'desc' => __( '招聘职位 - 存储招聘岗位信息', 'developer-starter' ),
            ),
            array(
                'key'  => 'careers_applications',
                'name' => $wpdb->prefix . 'ds_careers_applications',
                'desc' => __( '简历投递 - 存储应聘者投递的简历', 'developer-starter' ),
            ),
            array(
                'key'  => 'id_verifications',
                'name' => $wpdb->prefix . 'qiling_id_verifications',
                'desc' => __( '实名认证 - 存储实名认证记录', 'developer-starter' ),
            ),
            array(
                'key'  => 'account_deletion_requests',
                'name' => $wpdb->prefix . 'qiling_account_deletion_requests',
                'desc' => __( '注销申请 - 存储用户注销申请记录', 'developer-starter' ),
            ),
            array(
                'key'  => 'post_interactions',
                'name' => $post_interactions_table_name,
                'desc' => __( '文章互动 - 存储点赞、收藏、浏览记录', 'developer-starter' ),
            ),
        );
        $helper_functions = array(
            array(
                'name' => 'developer_starter_get_option( $key, $default )',
                'desc' => __( '获取主题设置选项值', 'developer-starter' ),
            ),
            array(
                'name' => 'developer_starter_render_page_modules()',
                'desc' => __( '渲染当前页面的模块', 'developer-starter' ),
            ),
            array(
                'name' => 'developer_starter_render_form( $form_id )',
                'desc' => __( '兼容调用：渲染指定ID的启灵表单', 'developer-starter' ),
            ),
            array(
                'name' => 'developer_starter_mask_username( $name )',
                'desc' => __( '用户名脱敏处理', 'developer-starter' ),
            ),
        );
        $filter_hooks = array(
            array(
                'name' => 'developer_starter_modules',
                'desc' => __( '扩展自定义模块类型', 'developer-starter' ),
            ),
            array(
                'name' => 'developer_starter_banner_html',
                'desc' => __( '修改 Banner 模块输出', 'developer-starter' ),
            ),
            array(
                'name' => 'get_comment_author',
                'desc' => __( '过滤评论作者名（用于隐私脱敏）', 'developer-starter' ),
            ),
        );
        $action_hooks = array(
            array(
                'name' => 'developer_starter_before_header',
                'desc' => __( '在页头之前输出内容', 'developer-starter' ),
            ),
            array(
                'name' => 'developer_starter_after_footer',
                'desc' => __( '在页脚之后输出内容', 'developer-starter' ),
            ),
            array(
                'name' => 'qiling_forms_submitted',
                'desc' => __( '启灵表单提交成功后触发，参数: $entry_id, $form_id, $form, $entry_data', 'developer-starter' ),
            ),
        );
        $post_meta_keys = array(
            array(
                'name' => '_developer_starter_modules',
                'desc' => __( '页面模块配置数据（数组）', 'developer-starter' ),
            ),
            array(
                'name' => '_seo_title',
                'desc' => __( '自定义 SEO 标题', 'developer-starter' ),
            ),
            array(
                'name' => '_seo_description',
                'desc' => __( '自定义 SEO 描述', 'developer-starter' ),
            ),
            array(
                'name' => '_seo_keywords',
                'desc' => __( '自定义 SEO 关键词', 'developer-starter' ),
            ),
            array(
                'name' => 'post_views',
                'desc' => __( '文章浏览量', 'developer-starter' ),
            ),
        );
        ?>
        <tr><td colspan="2" style="padding: 0;">
            <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">

                <!-- 缓存/CDN 说明 -->
                <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <?php esc_html_e( '⚡ 缓存/CDN 说明（会员网站）', 'developer-starter' ); ?>
                    </h2>
                    <p style="color: #64748b; margin: 0 0 12px;">
                        <?php esc_html_e( '主题默认允许全缓存，所以会出现登录用户会被游客缓存页面影响。', 'developer-starter' ); ?>
                    </p>
                    <div style="background: #fef3c7; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px;">
                        <p style="margin: 0 0 6px; color: #92400e; font-weight: 600;"><?php esc_html_e( '必须配置：', 'developer-starter' ); ?></p>
                        <ul style="margin: 0; padding-left: 18px; color: #92400e;">
                            <li><?php esc_html_e( 'Cookie 命中 wordpress_logged_in_ 时：CDN 直接 Bypass。', 'developer-starter' ); ?></li>
                            <li><?php esc_html_e( '缓存插件开启“Logged-in users 不缓存”。', 'developer-starter' ); ?></li>
                            <li><?php esc_html_e( '若 CDN 不支持 Cookie 规则：不要缓存 HTML，只缓存静态后缀（css/js/png/jpg/webp/svg/woff2）。', 'developer-starter' ); ?></li>
                        </ul>
                        <p style="margin: 6px 0 0; color: #92400e;"><?php esc_html_e( '若 CDN 强制缓存 HTML 并忽略 Cookie，主题侧无法修正登录状态。企业展示站可保持默认。', 'developer-starter' ); ?></p>
                    </div>
                    <div style="background: #eef2ff; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px;">
                        <p style="margin: 0 0 6px; color: #3730a3; font-weight: 600;"><?php esc_html_e( 'CDN 快速参考（名称可能略有差异）：', 'developer-starter' ); ?></p>
                        <ul style="margin: 0; padding-left: 18px; color: #4338ca;">
                            <li><?php esc_html_e( 'Cloudflare：Cache Rules → Cookie 包含 wordpress_logged_in_ → Cache Bypass。', 'developer-starter' ); ?></li>
                            <li><?php esc_html_e( '阿里云和阿里云普通CDN好像是不支持Cookie策略，所以不要缓存全站和HTML。', 'developer-starter' ); ?></li>
                        </ul>
                    </div>
                    <div style="background: #ecfeff; border-radius: 10px; padding: 12px 14px;">
                        <p style="margin: 0 0 6px; color: #0e7490; font-weight: 600;"><?php esc_html_e( '怎么判断是否命中缓存：', 'developer-starter' ); ?></p>
                        <ul style="margin: 0; padding-left: 18px; color: #155e75;">
                            <li><?php esc_html_e( '浏览器开发者工具 → Network → 主文档响应头。', 'developer-starter' ); ?></li>
                            <li><?php esc_html_e( '出现 HIT/Cache Hit/CF-Cache-Status: HIT/ Age>0 = 没回源。', 'developer-starter' ); ?></li>
                            <li><?php esc_html_e( '期望看到 Bypass/Miss/Dynamic，并包含 private/no-store。', 'developer-starter' ); ?></li>
                        </ul>
                    </div>
                </div>

                <!-- 数据表说明 -->
                <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <?php esc_html_e( '🗄️ 主题使用的数据表', 'developer-starter' ); ?>
                    </h2>
                    <p style="color: #64748b; margin: 0 0 16px;"><?php esc_html_e( '以下是本主题创建的自定义数据表。如果卸载主题后不再使用这些功能，可以手动删除对应的数据表清理数据。', 'developer-starter' ); ?></p>
                    <p style="color: #64748b; margin: 0 0 16px;"><?php esc_html_e( '启灵表单（qiling-forms）插件的数据表由插件自身维护，不在此处检测或创建。', 'developer-starter' ); ?></p>
                    <?php if ( isset( $_GET['ds_table_created'] ) ) : ?>
                        <div style="margin-bottom: 16px; padding: 10px 12px; background: #dcfce7; color: #166534; border-radius: 8px;">
                            <?php esc_html_e( '数据表已创建或已存在', 'developer-starter' ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <table class="widefat striped" style="margin-bottom: 16px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( '数据表名称', 'developer-starter' ); ?></th>
                                <th><?php esc_html_e( '功能用途', 'developer-starter' ); ?></th>
                                <th><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                                <th><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $tables as $table ) : ?>
                                <?php $exists = ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table['name'] ) ) === $table['name'] ); ?>
                                <tr>
                                    <td><?php echo esc_html( $table['name'] ); ?></td>
                                    <td><?php echo esc_html( $table['desc'] ); ?></td>
                                    <td>
                                        <?php if ( $exists ) : ?>
                                            <span style="color: #10b981; font-weight: 600;"><?php esc_html_e( '已创建', 'developer-starter' ); ?></span>
                                        <?php else : ?>
                                            <span style="color: #f59e0b; font-weight: 600;"><?php esc_html_e( '未创建', 'developer-starter' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( ! $exists ) : ?>
                                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                                <?php wp_nonce_field( 'ds_create_theme_table' ); ?>
                                                <input type="hidden" name="action" value="ds_create_theme_table" />
                                                <input type="hidden" name="table_key" value="<?php echo esc_attr( $table['key'] ); ?>" />
                                                <button type="submit" class="button button-small"><?php esc_html_e( '创建数据表', 'developer-starter' ); ?></button>
                                            </form>
                                        <?php else : ?>
                                            <span style="color: #64748b;"><?php esc_html_e( '无需操作', 'developer-starter' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="background: #fef3c7; border-radius: 8px; padding: 12px 16px; display: flex; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 1.2rem;">⚠️</span>
                        <div style="font-size: 0.9rem; color: #92400e;">
                            <strong><?php esc_html_e( '注意：', 'developer-starter' ); ?></strong><?php esc_html_e( '删除数据表将永久丢失所有相关数据，请谨慎操作。建议先导出备份后再删除。', 'developer-starter' ); ?>
                        </div>
                    </div>
                </div>

                <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <?php esc_html_e( '🔧 开发者钩子 (Hooks)', 'developer-starter' ); ?>
                    </h2>
                    <p style="color: #64748b; margin: 0 0 16px;"><?php esc_html_e( '如需基于本主题进行二次开发，可使用以下钩子和函数。', 'developer-starter' ); ?></p>

                    <h4 style="margin: 20px 0 10px; color: #334155;"><?php esc_html_e( '主要函数', 'developer-starter' ); ?></h4>
                    <table class="widefat striped">
                        <thead><tr><th><?php esc_html_e( '函数名', 'developer-starter' ); ?></th><th><?php esc_html_e( '说明', 'developer-starter' ); ?></th></tr></thead>
                        <tbody>
                            <?php foreach ( $helper_functions as $item ) : ?>
                                <tr>
                                    <td><code><?php echo esc_html( $item['name'] ); ?></code></td>
                                    <td><?php echo esc_html( $item['desc'] ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h4 style="margin: 20px 0 10px; color: #334155;"><?php esc_html_e( '过滤器 (Filter Hooks)', 'developer-starter' ); ?></h4>
                    <table class="widefat striped">
                        <thead><tr><th><?php esc_html_e( '钩子名', 'developer-starter' ); ?></th><th><?php esc_html_e( '说明', 'developer-starter' ); ?></th></tr></thead>
                        <tbody>
                            <?php foreach ( $filter_hooks as $item ) : ?>
                                <tr>
                                    <td><code><?php echo esc_html( $item['name'] ); ?></code></td>
                                    <td><?php echo esc_html( $item['desc'] ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h4 style="margin: 20px 0 10px; color: #334155;"><?php esc_html_e( '动作钩子 (Action Hooks)', 'developer-starter' ); ?></h4>
                    <table class="widefat striped">
                        <thead><tr><th><?php esc_html_e( '钩子名', 'developer-starter' ); ?></th><th><?php esc_html_e( '说明', 'developer-starter' ); ?></th></tr></thead>
                        <tbody>
                            <?php foreach ( $action_hooks as $item ) : ?>
                                <tr>
                                    <td><code><?php echo esc_html( $item['name'] ); ?></code></td>
                                    <td><?php echo esc_html( $item['desc'] ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h4 style="margin: 20px 0 10px; color: #334155;"><?php esc_html_e( 'Post Meta 键名', 'developer-starter' ); ?></h4>
                    <table class="widefat striped">
                        <thead><tr><th><?php esc_html_e( '键名', 'developer-starter' ); ?></th><th><?php esc_html_e( '说明', 'developer-starter' ); ?></th></tr></thead>
                        <tbody>
                            <?php foreach ( $post_meta_keys as $item ) : ?>
                                <tr>
                                    <td><code><?php echo esc_html( $item['name'] ); ?></code></td>
                                    <td><?php echo esc_html( $item['desc'] ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <?php esc_html_e( '🚀 性能优化建议', 'developer-starter' ); ?>
                    </h2>
                    <ul style="margin: 0; padding-left: 18px; color: #475569;">
                        <li><?php esc_html_e( '开启对象缓存（Redis/Memcached）可提升后台/接口性能。', 'developer-starter' ); ?></li>
                        <li><?php esc_html_e( '开启页面缓存（如 LiteSpeed/Redis Page Cache）提升前台访问速度。', 'developer-starter' ); ?></li>
                        <li><?php esc_html_e( '图片建议开启 WebP/压缩，启用主题内的图片懒加载。', 'developer-starter' ); ?></li>
                        <li><?php esc_html_e( '主题设置内含 CSS 拆分工具，减少首屏加载体积。', 'developer-starter' ); ?></li>
                    </ul>
                </div>

                <div style="padding: 24px;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <?php esc_html_e( '👨‍💻 关于作者', 'developer-starter' ); ?>
                    </h2>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; padding: 20px;">
                            <h4 style="margin: 0 0 12px; color: #0369a1;"><?php esc_html_e( '主题信息', 'developer-starter' ); ?></h4>
                            <p style="margin: 0; color: #334155; line-height: 1.8;">
                                <strong><?php esc_html_e( '主题名称:', 'developer-starter' ); ?></strong> 启灵 <br>
                                <strong><?php esc_html_e( '版本号:', 'developer-starter' ); ?></strong> <?php echo esc_html( DEVELOPER_STARTER_VERSION ); ?><br>
                                <strong><?php esc_html_e( '适用于:', 'developer-starter' ); ?></strong> WordPress 6.0+<br>
                                <strong><?php esc_html_e( 'PHP版本:', 'developer-starter' ); ?></strong> 7.4+
                            </p>
                        </div>

                        <div style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-radius: 12px; padding: 20px;">
                            <h4 style="margin: 0 0 12px; color: #7c3aed;"><?php esc_html_e( '联系方式', 'developer-starter' ); ?></h4>
                            <p style="margin: 0; color: #334155; line-height: 1.8;">
                                <strong><?php esc_html_e( '技术支持:', 'developer-starter' ); ?></strong> iticu@qq.com<br>
                                <strong><?php esc_html_e( '官方网站:', 'developer-starter' ); ?></strong> <a href="https://qiling.jingxialai.com" target="_blank" rel="noopener noreferrer">qiling.jingxialai.com</a><br>
                                <strong><?php esc_html_e( '授权说明:', 'developer-starter' ); ?></strong> <?php esc_html_e( '授权密钥仅用于更新与支持服务', 'developer-starter' ); ?><br>
                                <strong><?php esc_html_e( '协议详情:', 'developer-starter' ); ?></strong> <a href="https://www.jingxialai.com/docs/qiling" target="_blank" rel="noopener noreferrer">www.jingxialai.com/docs/qiling</a>
                            </p>
                        </div>

                        <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; padding: 20px;">
                            <h4 style="margin: 0 0 12px; color: #059669;"><?php esc_html_e( '反馈说明', 'developer-starter' ); ?></h4>
                            <p style="margin: 0; color: #334155; line-height: 1.8;">
                                <?php esc_html_e( 'QQ群：16966111', 'developer-starter' ); ?><br>
                                <?php esc_html_e( '一般问题可以直接进群里面说。', 'developer-starter' ); ?><br>
                                <?php esc_html_e( '社区版不提供任何免费服务，请自己解决或者付费联系服务。', 'developer-starter' ); ?><br>
                                <?php esc_html_e( '商业版永久免费更新，非主题本身 bug 请自行解决或付费服务。', 'developer-starter' ); ?><br>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </td></tr>
        <?php
    }

    private function render_plugins_tab( $options = null ) {
        unset( $options );

        $plugin_groups       = $this->get_ecosystem_plugin_groups();
        $open_source_plugins = $this->get_standalone_open_source_plugins();
        $ecosystem_total     = count( $this->get_ecosystem_plugin_index() );
        $open_source_total   = count( $open_source_plugins );
        $detect_nonce        = wp_create_nonce( 'developer_starter_ecosystem_plugins' );
        ?>
        <tr><td colspan="2" style="padding: 0;">
            <div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);overflow:hidden;">
                <div style="padding:24px;border-bottom:1px solid #e2e8f0;">
                    <div style="display:flex;justify-content:space-between;gap:20px;align-items:flex-start;flex-wrap:wrap;">
                        <div style="max-width:760px;">
                            <h2 style="margin:0 0 14px;font-size:1.25rem;color:#1e293b;"><?php esc_html_e( '启灵生态插件指南', 'developer-starter' ); ?></h2>
                            <p style="margin:0;color:#64748b;font-size:14px;line-height:1.7;">
                                <?php esc_html_e( '整理启灵生态插件的定位、适用场景与主题搭配关系，方便站点按业务需要选择。', 'developer-starter' ); ?>
                            </p>
                        </div>
                        <div style="min-width:240px;text-align:right;">
                            <button type="button" class="button button-primary" id="developer-starter-ecosystem-detect" data-nonce="<?php echo esc_attr( $detect_nonce ); ?>">
                                <?php esc_html_e( '检测插件状态', 'developer-starter' ); ?>
                            </button>
                            <p id="developer-starter-ecosystem-detect-result" style="margin:8px 0 0;color:#64748b;font-size:12px;line-height:1.5;">
                                <?php echo esc_html( sprintf( __( '共收录 %1$d 个生态插件，另列 %2$d 个开源免费插件。点击按钮可查看当前站点状态。', 'developer-starter' ), $ecosystem_total, $open_source_total ) ); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div style="padding:24px;">
                    <?php foreach ( $plugin_groups as $plugin_group_index => $plugin_group ) : ?>
                        <div style="margin-top:<?php echo 0 === (int) $plugin_group_index ? '0' : '28px'; ?>;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                                <h3 style="margin:0;font-size:1.08rem;color:#0f172a;"><?php echo esc_html( $plugin_group['title'] ); ?></h3>
                                <?php if ( ! empty( $plugin_group['badge'] ) ) : ?>
                                    <span style="display:inline-block;background:#ede9fe;color:#6d28d9;font-size:12px;padding:3px 10px;border-radius:999px;font-weight:600;"><?php echo esc_html( $plugin_group['badge'] ); ?></span>
                                <?php endif; ?>
                            </div>
                            <p style="margin:0 0 14px;color:#64748b;line-height:1.6;"><?php echo esc_html( $plugin_group['desc'] ); ?></p>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
                                <?php foreach ( $plugin_group['plugins'] as $plugin ) : ?>
                                    <?php $is_paid = isset( $plugin['fee'] ) && '收费' === $plugin['fee']; ?>
                                    <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px;background:#fff;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:10px;">
                                            <div>
                                                <h5 style="margin:0 0 6px;font-size:1rem;color:#1e293b;"><?php echo esc_html( $plugin['name'] ); ?></h5>
                                                <code style="display:inline-block;background:#fff;border:1px solid #e2e8f0;color:#475569;font-size:12px;padding:2px 6px;border-radius:6px;"><?php echo esc_html( $plugin['slug'] ); ?></code>
                                            </div>
                                            <span style="background:<?php echo esc_attr( $is_paid ? '#fee2e2' : '#dcfce7' ); ?>;color:<?php echo esc_attr( $is_paid ? '#991b1b' : '#166534' ); ?>;font-size:12px;padding:3px 10px;border-radius:999px;font-weight:700;white-space:nowrap;">
                                                <?php echo esc_html( $plugin['fee'] ); ?>
                                            </span>
                                        </div>
                                        <p style="margin:0 0 12px;color:#64748b;line-height:1.6;"><?php echo esc_html( $plugin['desc'] ); ?></p>
                                        <div style="display:grid;gap:8px;color:#475569;font-size:13px;line-height:1.55;">
                                            <div><strong style="color:#334155;"><?php esc_html_e( '适合：', 'developer-starter' ); ?></strong><?php echo esc_html( $plugin['fit'] ); ?></div>
                                            <div><strong style="color:#334155;"><?php esc_html_e( '主题关系：', 'developer-starter' ); ?></strong><?php echo esc_html( $plugin['relation'] ); ?></div>
                                        </div>
                                        <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center;gap:10px;">
                                            <span style="color:#64748b;font-size:12px;"><?php esc_html_e( '当前状态', 'developer-starter' ); ?></span>
                                            <span data-ecosystem-plugin-status="<?php echo esc_attr( $plugin['slug'] ); ?>" data-status="unknown" style="display:inline-block;background:#f1f5f9;color:#475569;font-size:12px;padding:4px 10px;border-radius:999px;font-weight:700;white-space:nowrap;">
                                                <?php esc_html_e( '未检测', 'developer-starter' ); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ( ! empty( $open_source_plugins ) ) : ?>
                        <div style="margin-top:32px;padding-top:24px;border-top:1px solid #e2e8f0;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                                <h3 style="margin:0;font-size:1.08rem;color:#0f172a;"><?php esc_html_e( '开源免费插件（不属于主题生态）', 'developer-starter' ); ?></h3>
                                <span style="display:inline-block;background:#dcfce7;color:#166534;font-size:12px;padding:3px 10px;border-radius:999px;font-weight:600;"><?php esc_html_e( '开源免费', 'developer-starter' ); ?></span>
                            </div>
                            <p style="margin:0 0 14px;color:#64748b;line-height:1.6;"><?php esc_html_e( '以下插件完全开源免费，不属于启灵主题生态，也不在启灵主题售后范围内。', 'developer-starter' ); ?></p>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
                                <?php foreach ( $open_source_plugins as $plugin ) : ?>
                                    <div style="border:1px solid #bbf7d0;border-radius:12px;padding:16px;background:#f8fff9;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:10px;">
                                            <div>
                                                <h5 style="margin:0 0 6px;font-size:1rem;color:#1e293b;"><?php echo esc_html( $plugin['name'] ); ?></h5>
                                                <code style="display:inline-block;background:#fff;border:1px solid #bbf7d0;color:#475569;font-size:12px;padding:2px 6px;border-radius:6px;"><?php echo esc_html( $plugin['slug'] ); ?></code>
                                            </div>
                                            <span style="background:#dcfce7;color:#166534;font-size:12px;padding:3px 10px;border-radius:999px;font-weight:700;white-space:nowrap;">
                                                <?php echo esc_html( $plugin['fee'] ); ?>
                                            </span>
                                        </div>
                                        <p style="margin:0 0 12px;color:#64748b;line-height:1.6;"><?php echo esc_html( $plugin['desc'] ); ?></p>
                                        <div style="display:grid;gap:8px;color:#475569;font-size:13px;line-height:1.55;">
                                            <div><strong style="color:#334155;"><?php esc_html_e( '适合：', 'developer-starter' ); ?></strong><?php echo esc_html( $plugin['fit'] ); ?></div>
                                            <div><strong style="color:#334155;"><?php esc_html_e( '说明：', 'developer-starter' ); ?></strong><?php echo esc_html( $plugin['relation'] ); ?></div>
                                        </div>
                                        <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center;gap:10px;">
                                            <span style="color:#64748b;font-size:12px;"><?php esc_html_e( '当前状态', 'developer-starter' ); ?></span>
                                            <span data-ecosystem-plugin-status="<?php echo esc_attr( $plugin['slug'] ); ?>" data-status="unknown" style="display:inline-block;background:#f1f5f9;color:#475569;font-size:12px;padding:4px 10px;border-radius:999px;font-weight:700;white-space:nowrap;">
                                                <?php esc_html_e( '未检测', 'developer-starter' ); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <script>
            (function() {
                var button = document.getElementById('developer-starter-ecosystem-detect');
                var result = document.getElementById('developer-starter-ecosystem-detect-result');
                if (!button || !result) {
                    return;
                }

                var originalText = button.textContent.trim();
                var labels = {
                    running: <?php echo wp_json_encode( __( '检测中...', 'developer-starter' ) ); ?>,
                    failed: <?php echo wp_json_encode( __( '检测失败，请稍后重试。', 'developer-starter' ) ); ?>,
                    done: <?php echo wp_json_encode( __( '检测完成：已启用 %active% 个，已安装未启用 %inactive% 个，未安装 %missing% 个。', 'developer-starter' ) ); ?>
                };
                var statusStyles = {
                    active: { background: '#dcfce7', color: '#166534' },
                    inactive: { background: '#fef3c7', color: '#92400e' },
                    missing: { background: '#f1f5f9', color: '#475569' },
                    unknown: { background: '#f1f5f9', color: '#475569' }
                };

                function setStatusStyle(element, status) {
                    var styles = statusStyles[status] || statusStyles.unknown;
                    element.style.background = styles.background;
                    element.style.color = styles.color;
                    element.setAttribute('data-status', status);
                }

                button.addEventListener('click', function() {
                    var body = new URLSearchParams();
                    body.set('action', 'developer_starter_detect_ecosystem_plugins');
                    body.set('nonce', button.getAttribute('data-nonce') || '');

                    button.disabled = true;
                    button.textContent = labels.running;
                    result.textContent = <?php echo wp_json_encode( __( '正在检测插件状态...', 'developer-starter' ) ); ?>;

                    fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                        body: body.toString()
                    }).then(function(response) {
                        return response.json();
                    }).then(function(payload) {
                        if (!payload || !payload.success || !payload.data) {
                            throw new Error('Invalid response');
                        }

                        var statuses = payload.data.statuses || {};
                        document.querySelectorAll('[data-ecosystem-plugin-status]').forEach(function(element) {
                            var slug = element.getAttribute('data-ecosystem-plugin-status');
                            var item = statuses[slug] || { status: 'unknown', label: <?php echo wp_json_encode( __( '未检测', 'developer-starter' ) ); ?> };
                            element.textContent = item.label || <?php echo wp_json_encode( __( '未检测', 'developer-starter' ) ); ?>;
                            setStatusStyle(element, item.status || 'unknown');
                        });

                        var summary = payload.data.summary || {};
                        result.textContent = labels.done
                            .replace('%active%', String(summary.active || 0))
                            .replace('%inactive%', String(summary.inactive || 0))
                            .replace('%missing%', String(summary.missing || 0));
                    }).catch(function() {
                        result.textContent = labels.failed;
                    }).finally(function() {
                        button.disabled = false;
                        button.textContent = originalText;
                    });
                });
            }());
            </script>
        </td></tr>
        <?php
    }
}
