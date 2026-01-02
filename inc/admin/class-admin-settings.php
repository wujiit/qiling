<?php
/**
 * Admin Settings Class - 完整版
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin_Settings {

    private $option_name = 'developer_starter_options';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ), 10 );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'handle_reset' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_developer_starter_refresh_version', array( $this, 'ajax_refresh_version' ) );
        add_action( 'wp_ajax_developer_starter_db_cleanup', array( $this, 'ajax_db_cleanup' ) );
        add_action( 'wp_ajax_developer_starter_db_stats', array( $this, 'ajax_db_stats' ) );
    }

    public function enqueue_admin_scripts( $hook ) {
        if ( strpos( $hook, 'developer-starter' ) === false ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        
        add_action( 'admin_footer', array( $this, 'admin_footer_js' ) );
    }

    public function admin_footer_js() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.ds-color-picker').wpColorPicker();
            
            $('.ds-upload-image-btn').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var input = button.siblings('.ds-image-url');
                var preview = button.siblings('.ds-image-preview');
                
                var frame = wp.media({ title: '选择图片', multiple: false });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    input.val(attachment.url);
                    if (preview.length) {
                        preview.attr('src', attachment.url).show();
                    } else {
                        button.after('<img src="' + attachment.url + '" class="ds-image-preview" style="display:block;max-width:200px;margin-top:10px;"/>');
                    }
                });
                frame.open();
            });
            
            $('.ds-remove-image-btn').on('click', function(e) {
                e.preventDefault();
                $(this).siblings('.ds-image-url').val('');
                $(this).siblings('.ds-image-preview').attr('src', '').hide();
            });

            $(document).on('click', '.ds-repeater-add', function() {
                var $wrap = $(this).closest('.ds-repeater-wrap');
                var $list = $wrap.find('.ds-repeater-list');
                var $tpl = $wrap.find('.ds-repeater-tpl');
                var tpl = $tpl.attr('data-template');
                var idx = $list.children().length;
                tpl = tpl.replace(/__IDX__/g, idx);
                $list.append(tpl);
            });

            $(document).on('click', '.ds-repeater-remove', function(e) {
                e.preventDefault();
                $(this).closest('.ds-repeater-item').remove();
            });
            
            // 一键刷新版本号
            $('#refresh-assets-version').on('click', function() {
                var button = $(this);
                var result = $('#refresh-version-result');
                var versionInput = $('input[name="developer_starter_options[assets_version]"]');
                
                button.prop('disabled', true).text('刷新中...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_refresh_version',
                        nonce: '<?php echo wp_create_nonce( "refresh_version_nonce" ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            versionInput.val(response.data.version);
                            result.text('✓ 版本号已更新为: ' + response.data.version);
                            setTimeout(function() { result.text(''); }, 5000);
                        } else {
                            result.css('color', '#ef4444').text('刷新失败');
                        }
                        button.prop('disabled', false).text('一键刷新版本号');
                    },
                    error: function() {
                        result.css('color', '#ef4444').text('请求失败');
                        button.prop('disabled', false).text('一键刷新版本号');
                    }
                });
            });
            
            // 一键数据库清理
            $('#run-db-cleanup').on('click', function() {
                if (!confirm('确定要清理数据库吗？此操作不可逆，请确保已备份数据库！')) {
                    return;
                }
                
                var button = $(this);
                var result = $('#db-cleanup-result');
                
                // 收集选中的清理项
                var cleanItems = [];
                $('input[name^="db_clean_"]:checked').each(function() {
                    cleanItems.push($(this).attr('name').replace('db_clean_', ''));
                });
                
                if (cleanItems.length === 0) {
                    result.css('color', '#f59e0b').text('请至少选择一个清理项');
                    return;
                }
                
                button.prop('disabled', true).text('清理中...');
                result.css('color', '#64748b').text('正在清理数据库...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_db_cleanup',
                        nonce: '<?php echo wp_create_nonce( "db_cleanup_nonce" ); ?>',
                        items: cleanItems
                    },
                    success: function(response) {
                        if (response.success) {
                            result.css('color', '#10b981').text('✓ ' + response.data.message);
                        } else {
                            result.css('color', '#ef4444').text('清理失败: ' + response.data.message);
                        }
                        button.prop('disabled', false).text('🧹 一键清理数据库');
                    },
                    error: function() {
                        result.css('color', '#ef4444').text('请求失败');
                        button.prop('disabled', false).text('🧹 一键清理数据库');
                    }
                });
            });
            
            // 数据库统计加载
            function loadDbStats() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_db_stats',
                        nonce: '<?php echo wp_create_nonce( "db_stats_nonce" ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var stats = response.data;
                            updateStatDisplay('#stat-revisions', stats.revisions);
                            updateStatDisplay('#stat-drafts', stats.drafts);
                            updateStatDisplay('#stat-trash', stats.trash);
                            updateStatDisplay('#stat-spam', stats.spam);
                            updateStatDisplay('#stat-orphan-postmeta', stats.orphan_postmeta);
                            updateStatDisplay('#stat-orphan-commentmeta', stats.orphan_commentmeta);
                            updateStatDisplay('#stat-orphan-relationships', stats.orphan_relationships);
                            updateStatDisplay('#stat-pingbacks', stats.pingbacks);
                            updateStatDisplay('#stat-unused-tags', stats.unused_tags);
                            updateStatDisplay('#stat-transients', stats.transients);
                        }
                    }
                });
            }
            
            function updateStatDisplay(selector, count) {
                var $el = $(selector);
                $el.text(count + ' 条');
                if (count > 0) {
                    $el.css('color', '#f59e0b');
                } else {
                    $el.css('color', '#10b981');
                }
            }
            
            // 页面加载时获取统计
            if ($('#db-stats-container').length) {
                loadDbStats();
            }
            
            // 刷新统计按钮
            $('#refresh-db-stats').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('加载中...');
                $('#db-stats-grid .db-stat-item span:last-child').text('加载中...').css('color', '#64748b');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'developer_starter_db_stats',
                        nonce: '<?php echo wp_create_nonce( "db_stats_nonce" ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var stats = response.data;
                            updateStatDisplay('#stat-revisions', stats.revisions);
                            updateStatDisplay('#stat-drafts', stats.drafts);
                            updateStatDisplay('#stat-trash', stats.trash);
                            updateStatDisplay('#stat-spam', stats.spam);
                            updateStatDisplay('#stat-orphan-postmeta', stats.orphan_postmeta);
                            updateStatDisplay('#stat-orphan-commentmeta', stats.orphan_commentmeta);
                            updateStatDisplay('#stat-orphan-relationships', stats.orphan_relationships);
                            updateStatDisplay('#stat-pingbacks', stats.pingbacks);
                            updateStatDisplay('#stat-unused-tags', stats.unused_tags);
                            updateStatDisplay('#stat-transients', stats.transients);
                        }
                        button.prop('disabled', false).text('🔄 刷新统计');
                    },
                    error: function() {
                        button.prop('disabled', false).text('🔄 刷新统计');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX 刷新资源版本号
     */
    public function ajax_refresh_version() {
        check_ajax_referer( 'refresh_version_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        
        // 生成新版本号（时间戳）
        $new_version = date( 'ymd.His' );
        
        // 保存到选项
        $options = get_option( $this->option_name, array() );
        $options['assets_version'] = $new_version;
        update_option( $this->option_name, $options );
        
        wp_send_json_success( array( 'version' => $new_version ) );
    }
    
    /**
     * AJAX 数据库清理
     */
    public function ajax_db_cleanup() {
        check_ajax_referer( 'db_cleanup_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        global $wpdb;
        
        $items = isset( $_POST['items'] ) ? array_map( 'sanitize_text_field', $_POST['items'] ) : array();
        $cleaned = array();
        $total_deleted = 0;
        
        foreach ( $items as $item ) {
            $deleted = 0;
            
            switch ( $item ) {
                case 'revisions':
                    // 删除所有文章修订版本
                    $deleted = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'" );
                    if ( $deleted > 0 ) $cleaned[] = "修订版本({$deleted}条)";
                    break;
                    
                case 'drafts':
                    // 删除自动草稿
                    $deleted = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );
                    if ( $deleted > 0 ) $cleaned[] = "自动草稿({$deleted}条)";
                    break;
                    
                case 'trash':
                    // 删除回收站文章
                    $deleted = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_status = 'trash'" );
                    if ( $deleted > 0 ) $cleaned[] = "回收站文章({$deleted}条)";
                    break;
                    
                case 'spam':
                    // 删除垃圾评论
                    $deleted = $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
                    if ( $deleted > 0 ) $cleaned[] = "垃圾评论({$deleted}条)";
                    break;
                    
                case 'orphan_postmeta':
                    // 删除孤立的文章元数据
                    $deleted = $wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" );
                    if ( $deleted > 0 ) $cleaned[] = "孤立文章元数据({$deleted}条)";
                    break;
                    
                case 'orphan_commentmeta':
                    // 删除孤立的评论元数据
                    $deleted = $wpdb->query( "DELETE cm FROM {$wpdb->commentmeta} cm LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id WHERE c.comment_ID IS NULL" );
                    if ( $deleted > 0 ) $cleaned[] = "孤立评论元数据({$deleted}条)";
                    break;
                    
                case 'orphan_relationships':
                    // 删除孤立的关系数据
                    $deleted = $wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id WHERE p.ID IS NULL" );
                    if ( $deleted > 0 ) $cleaned[] = "孤立关系数据({$deleted}条)";
                    break;
                    
                case 'pingbacks':
                    // 删除 pingback/trackback 评论
                    $deleted = $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_type IN ('pingback', 'trackback')" );
                    if ( $deleted > 0 ) $cleaned[] = "Pingback/Trackback({$deleted}条)";
                    break;
                    
                case 'unused_tags':
                    // 删除未使用的标签
                    $deleted = $wpdb->query( "
                        DELETE t, tt FROM {$wpdb->terms} t
                        INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                        WHERE tt.taxonomy = 'post_tag' AND tt.count = 0
                    " );
                    if ( $deleted > 0 ) $cleaned[] = "未使用标签({$deleted}条)";
                    break;
                    
                case 'transients':
                    // 删除过期的 transients
                    $deleted = $wpdb->query( "
                        DELETE FROM {$wpdb->options} 
                        WHERE option_name LIKE '%_transient_timeout_%' 
                        AND option_value < " . time()
                    );
                    $deleted += $wpdb->query( "
                        DELETE FROM {$wpdb->options} 
                        WHERE option_name LIKE '%_transient_%' 
                        AND option_name NOT LIKE '%_transient_timeout_%'
                        AND option_name NOT IN (
                            SELECT CONCAT('_transient_', REPLACE(option_name, '_transient_timeout_', ''))
                            FROM (SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_%' AND option_value >= " . time() . ") as valid_transients
                        )
                    " );
                    if ( $deleted > 0 ) $cleaned[] = "过期缓存({$deleted}条)";
                    break;
            }
            
            $total_deleted += $deleted;
        }
        
        if ( empty( $cleaned ) ) {
            wp_send_json_success( array( 'message' => '数据库已经很干净，没有需要清理的数据' ) );
        } else {
            wp_send_json_success( array( 'message' => '已清理: ' . implode( '、', $cleaned ) ) );
        }
    }
    
    /**
     * AJAX 获取数据库统计
     */
    public function ajax_db_stats() {
        check_ajax_referer( 'db_stats_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        global $wpdb;
        
        $stats = array();
        
        // 文章修订版本
        $stats['revisions'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );
        
        // 自动草稿
        $stats['drafts'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );
        
        // 回收站文章
        $stats['trash'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" );
        
        // 垃圾评论
        $stats['spam'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
        
        // 孤立的文章元数据
        $stats['orphan_postmeta'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" );
        
        // 孤立的评论元数据
        $stats['orphan_commentmeta'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->commentmeta} cm LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id WHERE c.comment_ID IS NULL" );
        
        // 孤立的关系数据
        $stats['orphan_relationships'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id WHERE p.ID IS NULL" );
        
        // Pingback/Trackback
        $stats['pingbacks'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type IN ('pingback', 'trackback')" );
        
        // 未使用的标签
        $stats['unused_tags'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag' AND count = 0" );
        
        // 过期的 Transients
        $stats['transients'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_%' AND option_value < " . time() );
        
        wp_send_json_success( $stats );
    }

    public function add_menu_page() {
        add_menu_page( '企业主题设置', '企业主题设置', 'manage_options', 'developer-starter-settings',
            array( $this, 'render_settings_page' ), 'dashicons-building', 60 );
    }

    private function get_tabs() {
        return array(
            'basic'        => '基础设置',
            'header'       => '顶部导航',
            'footer'       => '页脚设置',
            'article'      => '文章设置',
            'pages'        => '页面模板',
            'content'      => '内容设置',
            'announcement' => '公告设置',
            'smtp'         => '邮件设置',
            'advanced'     => '高级设置',
            'translate'    => '语言切换',
            'optimize'     => '优化设置',
            'auth'         => '用户认证',
            'documentation' => '📖 主题说明',
        );
    }

    public function register_settings() {
        register_setting( 'developer_starter_settings', $this->option_name, array(
            'sanitize_callback' => array( $this, 'sanitize_options' ),
        ) );
    }

    public function handle_reset() {
        if ( isset( $_POST['ds_reset_settings'] ) && isset( $_POST['ds_reset_nonce'] ) ) {
            if ( wp_verify_nonce( $_POST['ds_reset_nonce'], 'ds_reset_action' ) && current_user_can( 'manage_options' ) ) {
                delete_option( $this->option_name );
                add_settings_error( 'developer_starter_settings', 'reset', '主题设置已恢复默认！', 'updated' );
            }
        }
    }

    public function render_settings_page() {
        $tabs = $this->get_tabs();
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'basic';
        $options = get_option( $this->option_name, array() );
        ?>
        <div class="wrap">
            <h1>企业主题设置</h1>
            <?php settings_errors(); ?>
            
            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $tab_id => $tab_name ) : ?>
                    <a href="?page=developer-starter-settings&tab=<?php echo $tab_id; ?>" 
                       class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab_name ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            
            <form method="post" action="options.php" style="margin-top: 20px;">
                <?php settings_fields( 'developer_starter_settings' ); ?>
                
                <table class="form-table" role="presentation">
                    <?php $this->render_tab_fields( $current_tab, $options ); ?>
                </table>
                
                <?php submit_button( '保存设置' ); ?>
            </form>
            
            <hr style="margin: 40px 0 20px;" />
            <h2>恢复默认设置</h2>
            <p class="description">如果设置出现问题，可以一键恢复所有主题设置为默认值。</p>
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field( 'ds_reset_action', 'ds_reset_nonce' ); ?>
                <button type="submit" name="ds_reset_settings" class="button button-secondary" 
                        onclick="return confirm('确定要恢复所有主题设置为默认值吗？此操作不可撤销！');">
                    恢复默认设置
                </button>
            </form>
        </div>
        <?php
    }

    private function render_tab_fields( $tab, $options ) {
        switch ( $tab ) {
            case 'basic': $this->render_basic_tab( $options ); break;
            case 'header': $this->render_header_tab( $options ); break;
            case 'footer': $this->render_footer_tab( $options ); break;
            case 'article': $this->render_article_tab( $options ); break;
            case 'pages': $this->render_pages_tab( $options ); break;
            case 'content': $this->render_content_tab( $options ); break;
            case 'smtp': $this->render_smtp_tab( $options ); break;
            case 'announcement': $this->render_announcement_tab( $options ); break;
            case 'advanced': $this->render_advanced_tab( $options ); break;
            case 'translate': $this->render_translate_tab( $options ); break;
            case 'optimize': $this->render_optimize_tab( $options ); break;
            case 'auth': $this->render_auth_tab( $options ); break;
            case 'documentation': $this->render_documentation_tab(); break;
        }
    }

    private function render_basic_tab( $options ) {
        echo '<tr><th colspan="2"><h2>网站信息</h2></th></tr>';
        $this->field_image( 'site_logo', '网站 Logo', $options, '推荐尺寸: 200x60 像素' );
        $this->field_text( 'company_name', '企业名称', $options );
        $this->field_text( 'company_phone', '联系电话', $options );
        $this->field_text( 'company_email', '联系邮箱', $options );
        $this->field_textarea( 'company_address', '企业地址', $options );
        $this->field_text( 'company_working_hours', '工作时间', $options, '如：周一至周五 9:00-18:00' );
        $this->field_textarea( 'company_brief', '公司简介', $options, '显示在页脚' );
        
        echo '<tr><th colspan="2"><h2>语言设置</h2></th></tr>';
        $this->field_select( 'theme_language', '前台显示语言', $options, array(
            'zh_CN' => '简体中文',
            'en_US' => 'English',
        ), '独立于WordPress后台语言' );
        
        echo '<tr><th colspan="2"><h2>备案信息</h2></th></tr>';
        $this->field_text( 'icp_number', 'ICP 备案号', $options );
        $this->field_text( 'police_number', '公安备案号', $options );
        $this->field_image( 'police_icon', '公安备案图标', $options );
        
        echo '<tr><th colspan="2"><h2>社交媒体</h2></th></tr>';
        $this->field_image( 'wechat_qrcode', '微信公众号二维码', $options );
        $this->field_text( 'wechat_qr_text', '微信二维码文字', $options, '如：扫码关注公众号' );
        $this->field_image( 'douyin_qrcode', '抖音二维码', $options );
        $this->field_text( 'douyin_qr_text', '抖音二维码文字', $options, '如：扫码关注抖音' );
        
        echo '<tr><th colspan="2"><h2>隐私政策提示（GDPR）</h2><p class="description">在网站底部显示数据收集声明，适用于欧盟等地区的隐私合规要求</p></th></tr>';
        $privacy_banner_enable = isset( $options['privacy_banner_enable'] ) ? $options['privacy_banner_enable'] : '';
        echo '<tr><th scope="row">启用隐私提示条</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[privacy_banner_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[privacy_banner_enable]" value="1"' . checked( $privacy_banner_enable, '1', false ) . ' /> ';
        echo '在网站底部显示隐私政策/Cookie提示条';
        echo '</label></td></tr>';
        $this->field_textarea( 'privacy_banner_text', '提示内容', $options, '如：本网站使用Cookie和类似技术来提升您的体验。继续使用本网站即表示您同意我们的隐私政策。' );
        $this->field_text( 'privacy_banner_link_text', '链接文字', $options, '如：了解更多' );
        $this->field_text( 'privacy_banner_link_url', '隐私政策链接', $options, '填写隐私政策页面URL，留空则不显示链接' );
        $this->field_text( 'privacy_banner_btn_text', '接受按钮文字', $options, '如：全部接受 或 我知道了' );
        $this->field_text( 'privacy_banner_decline_text', '拒绝按钮文字', $options, '如：仅必要Cookie 或 拒绝非必要，留空则不显示此按钮' );
        $this->field_color( 'privacy_banner_bg', '提示条背景色', $options, '#1e293b' );
        $this->field_color( 'privacy_banner_text_color', '提示条文字颜色', $options, '#ffffff' );
    }

    private function render_header_tab( $options ) {
        echo '<tr><th colspan="2"><h2>顶部导航设置</h2></th></tr>';
        $this->field_text( 'header_bg_color', '顶部背景色', $options, '支持渐变色，留空使用默认白色' );
        $this->field_color( 'header_text_color', '顶部文字颜色', $options, '#333333' );
        $this->field_checkbox( 'header_transparent_home', '首页顶部透明', $options, '首页首屏时顶部透明，滚动后显示背景色' );
        $this->field_checkbox( 'hide_search_button', '隐藏搜索按钮', $options, '取消勾选将在顶部导航显示搜索按钮' );
        $this->field_checkbox( 'hide_phone_header', '隐藏电话号码', $options, '取消勾选将在顶部导航显示联系电话' );
        
        echo '<tr><th colspan="2"><h2>Logo样式</h2><p class="description">自定义网站Logo的背景颜色</p></th></tr>';
        $this->field_text( 'logo_bg_color', 'Logo背景颜色', $options, '支持纯色（如 #2563eb）或渐变色（如 linear-gradient(135deg, #667eea 0%, #764ba2 100%)），留空则无背景' );
        
        echo '<tr><th colspan="2"><h2>菜单样式</h2><p class="description">自定义导航菜单的悬停和激活效果</p></th></tr>';
        $this->field_text( 'nav_hover_bg', '菜单Hover背景色', $options, '支持渐变色，如: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%)' );
        $this->field_color( 'nav_hover_text', '菜单Hover文字颜色', $options, '#ffffff' );
        
        echo '<tr><th colspan="2"><h2>电话按钮样式</h2><p class="description">自定义顶部导航电话按钮的颜色</p></th></tr>';
        $this->field_text( 'phone_bg_transparent', '透明模式-背景色', $options, '首页透明头部时的背景，如: rgba(255,255,255,0.2) 或渐变色' );
        $this->field_color( 'phone_text_transparent', '透明模式-文字颜色', $options, '#ffffff' );
        $this->field_text( 'phone_bg_normal', '常规模式-背景色', $options, '滚动后或普通页面的背景，支持渐变色' );
        $this->field_color( 'phone_text_normal', '常规模式-文字颜色', $options, '#ffffff' );
        
        echo '<tr><th colspan="2"><h2>滚动后菜单样式</h2><p class="description">首页透明头部滚动后，菜单文字的颜色设置</p></th></tr>';
        $this->field_color( 'scrolled_menu_text_color', '滚动后菜单文字颜色', $options, '#334155' );
        $this->field_color( 'scrolled_menu_hover_color', '滚动后菜单悬停文字颜色', $options, '#ffffff' );
        
        echo '<tr><th colspan="2"><h2>登录按钮</h2><p class="description">在顶部菜单栏显示登录按钮</p></th></tr>';
        $header_login_enable = isset( $options['header_login_enable'] ) ? $options['header_login_enable'] : '';
        echo '<tr><th scope="row">显示登录按钮</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[header_login_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[header_login_enable]" value="1"' . checked( $header_login_enable, '1', false ) . ' /> ';
        echo '在顶部菜单栏显示登录按钮（弹窗登录）';
        echo '<label></td></tr>';
        
        $this->field_text( 'header_login_text', '登录按钮文字', $options, '默认: 登录' );
        
        echo '<tr><th colspan="2"><h2>暗黑模式</h2><p class="description">允许用户切换网站的明/暗主题</p></th></tr>';
        $darkmode_enable = isset( $options['darkmode_enable'] ) ? $options['darkmode_enable'] : '';
        echo '<tr><th scope="row">启用暗黑模式</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[darkmode_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[darkmode_enable]" value="1"' . checked( $darkmode_enable, '1', false ) . ' /> ';
        echo '在顶部导航栏显示暗黑模式切换按钮';
        echo '</label></td></tr>';
    }

    private function render_footer_tab( $options ) {
        echo '<tr><th colspan="2"><h2>页脚文字设置</h2></th></tr>';
        $this->field_text( 'footer_about_title', '关于我们标题', $options, '默认: 关于我们' );
        $this->field_text( 'footer_links_title', '快速链接标题', $options, '默认: 快速链接' );
        $this->field_text( 'footer_contact_title', '联系方式标题', $options, '默认: 联系方式' );
        $this->field_text( 'footer_follow_title', '关注我们标题', $options, '默认: 关注我们' );
        $this->field_textarea( 'footer_copyright', '版权信息（支持HTML）', $options );
        
        echo '<tr><th colspan="2"><h2>快速链接（内部产品链接）</h2></th></tr>';
        $this->field_repeater( 'footer_quick_links', '链接列表', $options, array(
            array( 'id' => 'text', 'label' => '链接文字', 'type' => 'text' ),
            array( 'id' => 'url', 'label' => '链接地址', 'type' => 'text' ),
        ) );
        
        echo '<tr><th colspan="2"><h2>友情链接（仅首页显示）</h2></th></tr>';
        $this->field_checkbox( 'friend_links_enable', '启用友情链接', $options, '勾选后在首页底部显示友情链接' );
        $this->field_repeater( 'friend_links', '友情链接列表', $options, array(
            array( 'id' => 'text', 'label' => '链接文字', 'type' => 'text' ),
            array( 'id' => 'url', 'label' => '链接地址', 'type' => 'text' ),
        ) );
        
        echo '<tr><th colspan="2"><h2>页脚颜色设置</h2></th></tr>';
        $this->field_text( 'footer_widgets_bg', '页脚顶部背景', $options, '支持渐变色，默认: #1e293b' );
        $this->field_text( 'footer_bottom_bg', '页脚底部背景', $options, '支持渐变色，默认: #0f172a' );
        $this->field_color( 'footer_text_color', '页脚文字颜色', $options, '#ffffff' );
        
        echo '<tr><th colspan="2"><h2>页脚动画特效</h2></th></tr>';
        $this->field_checkbox( 'footer_effect_enable', '启用背景特效', $options, '在页脚显示动态背景效果' );
        $this->field_select( 'footer_effect_type', '特效类型', $options, array(
            'particles' => '粒子飘动',
            'lines' => '线条网络',
            'waves' => '波浪效果',
            'stars' => '星空闪烁',
            'bubbles' => '气泡上升',
            'snow' => '雪花飘落',
            'aurora' => '极光效果',
            'fireflies' => '萤火虫',
        ), '选择动画效果类型' );
    }

    private function render_article_tab( $options ) {
        // ========== 文章列表设置 ==========
        echo '<tr><th colspan="2"><h2>文章列表设置</h2></th></tr>';
        $this->field_number( 'article_thumb_height', '缩略图高度(px)', $options, '默认: 180' );
        $this->field_checkbox( 'hide_article_thumb', '隐藏缩略图', $options, '勾选后文章列表不显示缩略图' );
        $this->field_checkbox( 'hide_article_excerpt', '隐藏摘要', $options, '勾选后文章列表不显示摘要' );
        $this->field_checkbox( 'hide_article_date', '隐藏日期', $options, '勾选后文章列表不显示发布日期' );
        $this->field_checkbox( 'hide_article_category', '隐藏分类', $options, '勾选后文章列表不显示所属分类' );
        $this->field_checkbox( 'hide_article_author', '隐藏作者', $options, '勾选后文章列表不显示文章作者' );
        $this->field_number( 'article_excerpt_length', '摘要字数', $options, '默认: 80' );
        
        // ========== 文章详情页基础设置 ==========
        echo '<tr><th colspan="2"><h2>文章详情页设置</h2></th></tr>';
        $this->field_checkbox( 'hide_post_sidebar', '隐藏侧边栏', $options, '勾选后文章详情页不显示侧边栏（默认显示）' );
        
        // ========== 正文样式设置 ==========
        echo '<tr><th colspan="2"><h2>正文样式设置</h2><p class="description">自定义文章正文的显示样式</p></th></tr>';
        $this->field_select( 'post_content_width', '正文宽度', $options, array(
            'narrow' => '窄（680px）',
            'standard' => '标准（800px）',
            'wide' => '宽（960px）',
        ), '文章正文区域的最大宽度' );
        $this->field_select( 'post_font_size', '字体大小', $options, array(
            'small' => '小（16px）',
            'medium' => '中（18px）',
            'large' => '大（20px）',
        ), '文章正文的字体大小' );
        $this->field_select( 'post_line_height', '行距', $options, array(
            'compact' => '紧凑（1.6）',
            'standard' => '标准（1.8）',
            'relaxed' => '宽松（2.0）',
        ), '文章正文的行高' );
        $this->field_select( 'post_paragraph_spacing', '段落间距', $options, array(
            'small' => '小（1em）',
            'medium' => '中（1.5em）',
            'large' => '大（2em）',
        ), '段落之间的间距' );
        $this->field_select( 'post_image_max_width', '图片最大宽度', $options, array(
            '100' => '100%（撑满）',
            '90' => '90%',
            '80' => '80%',
        ), '文章内图片的最大宽度' );
        
        // ========== 代码高亮设置 ==========
        echo '<tr><th colspan="2"><h2>代码高亮设置</h2><p class="description">使用 PrismJS 为代码块添加语法高亮（仅在文章包含代码时加载）</p></th></tr>';
        $this->field_checkbox( 'code_highlight_enable', '启用代码高亮', $options, '开启后文章中的代码块将显示语法高亮' );
        $this->field_text( 'prism_css_cdn', 'PrismJS CSS CDN', $options, '留空使用本地文件，或填写自定义CDN地址' );
        $this->field_text( 'prism_js_cdn', 'PrismJS JS CDN', $options, '留空使用本地文件，或填写自定义CDN地址' );
        
        // ========== 评论设置 ==========
        echo '<tr><th colspan="2"><h2>评论设置</h2><p class="description">评论区相关功能设置</p></th></tr>';
        $this->field_checkbox( 'comment_username_privacy', '用户名隐私保护', $options, '开启后评论区用户名只显示首字，其余用*号代替（如：张** 或 J***）' );
        
        // ========== 作者信息设置 ==========
        echo '<tr><th colspan="2"><h2>作者信息卡片</h2><p class="description">在文章底部显示作者信息</p></th></tr>';
        $this->field_checkbox( 'author_box_enable', '显示作者信息', $options, '在文章底部显示作者信息卡片' );
        $this->field_checkbox( 'author_show_avatar', '显示头像', $options, '显示作者的头像' );
        $this->field_checkbox( 'author_show_name', '显示昵称', $options, '显示作者的显示名称' );
        $this->field_checkbox( 'author_show_bio', '显示简介', $options, '显示作者的个人简介' );
        $this->field_checkbox( 'author_show_social', '显示社交链接', $options, '显示作者的社交媒体链接（需在用户资料中设置）' );
        
        // ========== 社交链接字段设置 ==========
        echo '<tr><th colspan="2"><h2>用户社交链接设置</h2><p class="description">控制用户可以在个人资料中设置哪些社交链接</p></th></tr>';
        $this->field_checkbox( 'user_social_weibo', '启用微博', $options, '允许用户设置微博链接' );
        $this->field_checkbox( 'user_social_twitter', '启用 X (Twitter)', $options, '允许用户设置X/Twitter链接' );
        $this->field_checkbox( 'user_social_wechat', '启用微信', $options, '允许用户设置微信（二维码，悬停显示）' );
        $this->field_checkbox( 'user_social_github', '启用 GitHub', $options, '允许用户设置GitHub链接' );
        $this->field_checkbox( 'user_social_bilibili', '启用 B站', $options, '允许用户设置Bilibili链接' );
        $this->field_checkbox( 'user_social_zhihu', '启用知乎', $options, '允许用户设置知乎链接' );
        $this->field_checkbox( 'user_social_website', '启用个人网站', $options, '允许用户设置个人网站链接' );
        
        // ========== 文章目录设置 ==========
        echo '<tr><th colspan="2"><h2>文章目录（TOC）</h2><p class="description">自动生成文章标题目录，方便读者快速导航</p></th></tr>';
        $this->field_checkbox( 'toc_enable', '启用文章目录', $options, '根据文章中的H2/H3标题自动生成目录' );
        $this->field_select( 'toc_heading_levels', '解析标题层级', $options, array(
            'h2' => '仅 H2',
            'h2h3' => 'H2 和 H3',
            'h2h3h4' => 'H2、H3 和 H4',
        ), '选择要包含在目录中的标题层级' );
        $this->field_select( 'toc_position', '目录位置', $options, array(
            'sidebar' => '右侧悬浮',
            'before_content' => '正文开头',
        ), '目录显示的位置' );
        $this->field_checkbox( 'toc_collapsible', '可折叠目录', $options, '允许用户折叠/展开目录' );
        $this->field_number( 'toc_min_headings', '最少标题数', $options, '文章至少包含多少个标题才显示目录，默认: 3' );
        
        // ========== 版权信息设置 ==========
        echo '<tr><th colspan="2"><h2>版权信息</h2><p class="description">在文章底部显示版权声明</p></th></tr>';
        $this->field_checkbox( 'copyright_enable', '显示版权信息', $options, '在文章底部显示版权声明' );
        $this->field_textarea( 'copyright_content', '版权内容', $options, '支持变量: {title}=文章标题, {url}=文章链接, {author}=作者, {date}=发布日期, {site}=网站名称' );
        $this->field_text( 'copyright_reprint_notice', '转载须知', $options, '如：转载请注明出处' );
        
        // ========== 阅读统计设置 ==========
        echo '<tr><th colspan="2"><h2>阅读统计</h2><p class="description">文章浏览量和阅读时长统计</p></th></tr>';
        $this->field_checkbox( 'post_views_enable', '启用浏览量统计', $options, '统计并显示文章的浏览次数' );
        $this->field_checkbox( 'post_views_exclude_admin', '排除管理员', $options, '管理员访问不计入浏览量' );
        $this->field_checkbox( 'reading_time_enable', '显示阅读时长', $options, '根据文章字数估算阅读时间' );
        $this->field_number( 'reading_speed', '阅读速度(字/分钟)', $options, '默认: 400（中文平均阅读速度）' );
    }


    private function render_pages_tab( $options ) {
        $categories = get_categories( array( 'hide_empty' => false ) );
        $cat_options = array( '' => '全部分类' );
        foreach ( $categories as $cat ) {
            $cat_options[ $cat->slug ] = $cat->name;
        }
        
        echo '<tr><th colspan="2"><h2>产品中心设置</h2></th></tr>';
        $this->field_select( 'products_category', '调用分类', $options, $cat_options, '选择要显示的文章分类' );
        $this->field_number( 'products_per_page', '每页显示数量', $options, '默认: 12' );
        $this->field_select( 'products_layout', '布局样式', $options, array( 'grid' => '网格布局', 'list' => '列表布局' ) );
        $this->field_select( 'products_columns', '每行列数', $options, array( '2' => '2列', '3' => '3列', '4' => '4列' ) );
        $this->field_number( 'products_thumb_height', '缩略图高度(px)', $options, '默认: 200' );
        $this->field_checkbox( 'hide_products_title', '隐藏标题', $options );
        $this->field_checkbox( 'hide_products_date', '隐藏日期', $options );
        $this->field_checkbox( 'hide_products_excerpt', '隐藏摘要', $options );
        
        echo '<tr><th colspan="2"><h2>新闻中心设置</h2></th></tr>';
        $this->field_select( 'news_category', '调用分类', $options, $cat_options );
        $this->field_number( 'news_per_page', '每页显示数量', $options, '默认: 10' );
        $this->field_number( 'news_thumb_height', '缩略图高度(px)', $options, '默认: 150' );
        $this->field_checkbox( 'hide_news_title', '隐藏标题', $options );
        $this->field_checkbox( 'hide_news_date', '隐藏日期', $options );
        $this->field_checkbox( 'hide_news_excerpt', '隐藏摘要', $options );
        $this->field_checkbox( 'hide_news_thumb', '隐藏缩略图', $options );
        
        echo '<tr><th colspan="2"><h2>案例展示设置</h2></th></tr>';
        $this->field_select( 'cases_category', '调用分类', $options, $cat_options );
        $this->field_number( 'cases_per_page', '每页显示数量', $options, '默认: 9' );
        $this->field_select( 'cases_columns', '每行列数', $options, array( '2' => '2列', '3' => '3列', '4' => '4列' ) );
        $this->field_number( 'cases_thumb_height', '缩略图高度(px)', $options, '默认: 220' );
        $this->field_checkbox( 'hide_cases_title', '隐藏标题', $options );
        $this->field_checkbox( 'hide_cases_date', '隐藏日期', $options );
        
        echo '<tr><th colspan="2"><h2>关于我们设置</h2><p class="description">配置"关于我们"页面Tab栏显示的内容板块</p></th></tr>';
        $this->field_checkbox( 'about_show_timeline', '显示发展历程', $options );
        $this->field_checkbox( 'about_show_team', '显示团队成员', $options );
        $this->field_checkbox( 'about_show_certificates', '显示资质荣誉', $options, '展示企业资质证书、荣誉奖项等图片' );
        $this->field_checkbox( 'about_show_environment', '显示公司环境', $options, '展示办公环境、生产车间等照片' );
        $this->field_checkbox( 'about_show_culture', '显示企业文化', $options, '展示企业价值观、使命愿景等内容' );
        
        echo '<tr><th colspan="2"><h2>联系我们设置</h2></th></tr>';
        $this->field_checkbox( 'contact_show_form', '显示留言表单', $options, '在联系我们页面显示在线留言表单' );
        $this->field_checkbox( 'contact_show_info', '显示基础信息', $options, '显示企业名称、电话、邮箱、地址' );
        $this->field_image( 'contact_image', '右侧图片', $options, '留言表单关闭时显示的图片' );
    }

    private function render_content_tab( $options ) {
        echo '<tr><th colspan="2"><h2>发展历程</h2><p class="description">在"关于我们"页面显示（需开启显示发展历程）</p></th></tr>';
        $this->field_repeater( 'timeline_items', '时间节点', $options, array(
            array( 'id' => 'year', 'label' => '年份', 'type' => 'text' ),
            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
            array( 'id' => 'desc', 'label' => '描述', 'type' => 'textarea' ),
        ) );
        
        echo '<tr><th colspan="2"><h2>团队成员</h2><p class="description">在"关于我们"页面显示（需开启显示团队成员）</p></th></tr>';
        $this->field_repeater( 'team_members', '成员', $options, array(
            array( 'id' => 'name', 'label' => '姓名', 'type' => 'text' ),
            array( 'id' => 'position', 'label' => '职位', 'type' => 'text' ),
            array( 'id' => 'avatar', 'label' => '头像URL', 'type' => 'text' ),
            array( 'id' => 'desc', 'label' => '简介', 'type' => 'textarea' ),
        ) );
        
        echo '<tr><th colspan="2"><h2>资质荣誉</h2><p class="description">在"关于我们"页面显示（需开启显示资质荣誉）</p></th></tr>';
        $this->field_repeater( 'about_certificates', '证书/荣誉', $options, array(
            array( 'id' => 'image', 'label' => '证书图片URL', 'type' => 'text' ),
            array( 'id' => 'title', 'label' => '证书名称', 'type' => 'text' ),
        ) );
        
        echo '<tr><th colspan="2"><h2>公司环境</h2><p class="description">在"关于我们"页面显示（需开启显示公司环境）</p></th></tr>';
        $this->field_repeater( 'about_environment', '环境照片', $options, array(
            array( 'id' => 'image', 'label' => '照片URL', 'type' => 'text' ),
            array( 'id' => 'title', 'label' => '照片标题', 'type' => 'text' ),
        ) );
        
        echo '<tr><th colspan="2"><h2>企业文化</h2><p class="description">在"关于我们"页面显示（需开启显示企业文化）</p></th></tr>';
        $this->field_repeater( 'about_culture', '文化内容', $options, array(
            array( 'id' => 'icon', 'label' => '图标(emoji或iconfont)', 'type' => 'text' ),
            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
            array( 'id' => 'desc', 'label' => '描述', 'type' => 'textarea' ),
        ) );
        
        echo '<tr><th colspan="2"><h2>右侧浮动栏</h2></th></tr>';
        $this->field_checkbox( 'float_widget_enable', '启用浮动栏', $options, '开启后在前台显示右侧浮动栏' );
        $this->field_text( 'float_phone', '悬浮电话', $options );
        $this->field_text( 'float_qq', '悬浮QQ', $options );
        $this->field_image( 'float_wechat_qrcode', '悬浮微信二维码', $options );
        
        echo '<tr><th colspan="2"><h2>浮动栏自定义项目</h2><p class="description">添加自定义链接到浮动栏（如在线客服）</p></th></tr>';
        $this->field_repeater( 'float_custom_items', '自定义项目', $options, array(
            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
            array( 'id' => 'url', 'label' => '链接地址', 'type' => 'text' ),
            array( 'id' => 'icon', 'label' => '图标(emoji或iconfont类名，如: iconfont icon-weibo)', 'type' => 'text' ),
            array( 'id' => 'color', 'label' => '背景颜色', 'type' => 'text' ),
        ) );
    }

    private function render_smtp_tab( $options ) {
        echo '<tr><th colspan="2"><h2>SMTP 邮件设置</h2><p class="description">配置SMTP后可实现邮件发送功能</p></th></tr>';
        $this->field_text( 'smtp_host', 'SMTP 服务器', $options, '如: smtp.qq.com, smtp.163.com' );
        $this->field_number( 'smtp_port', 'SMTP 端口', $options, '常用: 465(SSL), 587(TLS), 25' );
        $this->field_select( 'smtp_secure', '加密协议', $options, array(
            'ssl' => 'SSL',
            'tls' => 'TLS',
            '' => '无加密',
        ) );
        $this->field_text( 'smtp_username', '邮箱账号', $options, '发件人邮箱地址' );
        $this->field_password( 'smtp_password', '邮箱密码/授权码', $options, 'QQ邮箱需使用授权码，密码将加密存储' );
        $this->field_text( 'smtp_sender_name', '发送者名称', $options, '邮件显示的发件人名称' );
        
        echo '<tr><th colspan="2"><h2>留言通知</h2></th></tr>';
        $this->field_checkbox( 'smtp_send_to_admin', '留言发送到邮箱', $options, '用户提交留言时发送邮件通知到管理员邮箱' );
    }

    private function render_advanced_tab( $options ) {
        echo '<tr><th colspan="2"><h2>主题样式</h2></th></tr>';
        $this->field_color( 'primary_color', '主色调', $options, '#2563eb' );
        
        echo '<tr><th colspan="2"><h2>SEO 设置</h2></th></tr>';
        $this->field_text( 'default_title', '默认标题', $options );
        $this->field_textarea( 'default_description', '默认描述', $options );
        $this->field_text( 'default_keywords', '默认关键词', $options );
        
        echo '<tr><th colspan="2"><h2>第三方资源</h2><p class="description">自定义CDN地址，留空使用默认CDN</p></th></tr>';
        $this->field_text( 'swiper_css_url', 'Swiper CSS 地址', $options, '默认: cdn.jsdelivr.net' );
        $this->field_text( 'swiper_js_url', 'Swiper JS 地址', $options, '默认: cdn.jsdelivr.net' );
        
        echo '<tr><th colspan="2"><h2>图标库</h2><p class="description">支持iconfont图标库（CSS方式），在浮动栏自定义项目中输入类名如 <code>iconfont icon-xxx</code></p></th></tr>';
        $this->field_text( 'iconfont_css_url', 'Iconfont CSS 地址', $options, '如: //at.alicdn.com/t/c/font_xxx.css' );
        
        echo '<tr><th colspan="2"><h2>代码设置</h2></th></tr>';
        $this->field_textarea( 'baidu_analytics', '百度统计代码/ID', $options );
        $this->field_textarea( 'custom_css', '自定义 CSS', $options );
        $this->field_textarea( 'custom_js', '自定义 JS', $options );
    }

    // ===== Field Renderers =====
    private function field_text( $id, $label, $options, $desc = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        echo '<tr><th scope="row"><label for="' . $id . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="text" id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" value="' . esc_attr( $value ) . '" class="regular-text" />';
        if ( $desc ) echo '<p class="description">' . esc_html( $desc ) . '</p>';
        echo '</td></tr>';
    }

    private function field_number( $id, $label, $options, $desc = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        echo '<tr><th scope="row"><label for="' . $id . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="number" id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" value="' . esc_attr( $value ) . '" class="small-text" />';
        if ( $desc ) echo '<p class="description">' . esc_html( $desc ) . '</p>';
        echo '</td></tr>';
    }

    private function field_textarea( $id, $label, $options, $desc = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        echo '<tr><th scope="row"><label for="' . $id . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><textarea id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
        if ( $desc ) echo '<p class="description">' . esc_html( $desc ) . '</p>';
        echo '</td></tr>';
    }

    private function field_image( $id, $label, $options, $desc = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
        echo '<div class="ds-image-field">';
        echo '<input type="text" name="' . $this->option_name . '[' . $id . ']" value="' . esc_attr( $value ) . '" class="ds-image-url regular-text" placeholder="输入图片URL或点击选择" />';
        echo '<button type="button" class="button ds-upload-image-btn">选择图片</button> ';
        echo '<button type="button" class="button ds-remove-image-btn">移除</button>';
        echo $value ? '<img src="' . esc_url( $value ) . '" class="ds-image-preview" style="display:block;max-width:200px;margin-top:10px;" />' : '<img class="ds-image-preview" style="display:none;max-width:200px;margin-top:10px;" />';
        echo '</div>';
        if ( $desc ) echo '<p class="description">' . esc_html( $desc ) . '</p>';
        echo '</td></tr>';
    }

    private function field_color( $id, $label, $options, $default = '#2563eb' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        echo '<tr><th scope="row"><label for="' . $id . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="text" id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" value="' . esc_attr( $value ) . '" class="ds-color-picker" data-default-color="' . esc_attr( $default ) . '" /></td></tr>';
    }

    private function field_password( $id, $label, $options, $desc = '' ) {
        // 密码字段显示时解密，但不显示实际值，只显示占位符
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        $has_value = ! empty( $value );
        $placeholder = $has_value ? '••••••••（已设置，留空保持不变）' : '请输入密码';
        echo '<tr><th scope="row"><label for="' . $id . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="password" id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" value="" class="regular-text" placeholder="' . esc_attr( $placeholder ) . '" autocomplete="new-password" />';
        if ( $has_value ) {
            echo '<input type="hidden" name="' . $this->option_name . '[' . $id . '_existing]" value="1" />';
        }
        if ( $desc ) echo '<p class="description">' . esc_html( $desc ) . '</p>';
        echo '</td></tr>';
    }

    private function field_checkbox( $id, $label, $options, $desc = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        echo '<tr><th scope="row">' . esc_html( $label ) . '</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[' . $id . ']" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[' . $id . ']" value="1"' . checked( $value, '1', false ) . ' /> ';
        if ( $desc ) echo esc_html( $desc );
        echo '</label></td></tr>';
    }

    private function field_select( $id, $label, $options, $choices, $desc = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        echo '<tr><th scope="row"><label for="' . $id . '">' . esc_html( $label ) . '</label></th><td>';
        echo '<select id="' . $id . '" name="' . $this->option_name . '[' . $id . ']">';
        foreach ( $choices as $k => $v ) {
            echo '<option value="' . esc_attr( $k ) . '"' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
        }
        echo '</select>';
        if ( $desc ) echo '<p class="description">' . esc_html( $desc ) . '</p>';
        echo '</td></tr>';
    }

    private function field_repeater( $id, $label, $options, $fields ) {
        $items = isset( $options[ $id ] ) && is_array( $options[ $id ] ) ? $options[ $id ] : array();
        echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
        echo '<div class="ds-repeater-wrap">';
        echo '<div class="ds-repeater-list" style="margin-bottom: 10px;">';
        
        foreach ( $items as $idx => $item ) {
            echo '<div class="ds-repeater-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; position: relative; border: 1px solid #ddd;">';
            echo '<a href="#" class="ds-repeater-remove" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;">删除</a>';
            foreach ( $fields as $f ) {
                $fval = isset( $item[ $f['id'] ] ) ? $item[ $f['id'] ] : '';
                $fname = $this->option_name . '[' . $id . '][' . $idx . '][' . $f['id'] . ']';
                echo '<div style="margin-bottom: 8px;"><label><strong>' . esc_html( $f['label'] ) . '</strong></label><br>';
                if ( $f['type'] === 'textarea' ) {
                    echo '<textarea name="' . esc_attr( $fname ) . '" rows="2" style="width:100%;">' . esc_textarea( $fval ) . '</textarea>';
                } else {
                    echo '<input type="text" name="' . esc_attr( $fname ) . '" value="' . esc_attr( $fval ) . '" style="width:100%;" />';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        
        echo '</div>';
        
        $tpl = '<div class="ds-repeater-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; position: relative; border: 1px solid #ddd;">';
        $tpl .= '<a href="#" class="ds-repeater-remove" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;">删除</a>';
        foreach ( $fields as $f ) {
            $fname = $this->option_name . '[' . $id . '][__IDX__][' . $f['id'] . ']';
            $tpl .= '<div style="margin-bottom: 8px;"><label><strong>' . esc_html( $f['label'] ) . '</strong></label><br>';
            if ( $f['type'] === 'textarea' ) {
                $tpl .= '<textarea name="' . esc_attr( $fname ) . '" rows="2" style="width:100%;"></textarea>';
            } else {
                $tpl .= '<input type="text" name="' . esc_attr( $fname ) . '" value="" style="width:100%;" />';
            }
            $tpl .= '</div>';
        }
        $tpl .= '</div>';
        
        echo '<div class="ds-repeater-tpl" data-template="' . esc_attr( $tpl ) . '" style="display:none;"></div>';
        echo '<button type="button" class="button ds-repeater-add">+ 添加</button>';
        echo '</div></td></tr>';
    }

    public function sanitize_options( $input ) {
        if ( ! is_array( $input ) ) return array();
        
        // 获取现有选项，确保其他选项卡的设置不会被清空
        $existing_options = get_option( $this->option_name, array() );
        if ( ! is_array( $existing_options ) ) {
            $existing_options = array();
        }
        
        // URL 类型的字段列表
        $url_fields = array(
            'site_logo', 'footer_logo', 'footer_bg_image', 
            'announcement_image', 'announcement_btn_url',
        );
        
        // 清理新提交的数据
        $sanitized = array();
        foreach ( $input as $key => $value ) {
            if ( is_array( $value ) ) {
                $sanitized[ $key ] = $this->sanitize_array_recursive( $value );
            } elseif ( in_array( $key, $url_fields ) ) {
                // URL 字段使用 esc_url_raw 保留完整URL
                $sanitized[ $key ] = esc_url_raw( $value );
            } else {
                $sanitized[ $key ] = wp_kses_post( $value );
            }
        }
        
        // 处理SMTP密码加密
        if ( isset( $sanitized['smtp_password'] ) ) {
            if ( empty( $sanitized['smtp_password'] ) && isset( $input['smtp_password_existing'] ) ) {
                // 密码留空但有旧密码，保留旧密码
                $sanitized[ 'smtp_password'] = isset( $existing_options['smtp_password'] ) ? $existing_options['smtp_password'] : '';
            } elseif ( ! empty( $sanitized['smtp_password'] ) ) {
                // 有新密码，加密存储
                $sanitized['smtp_password'] = \Developer_Starter\Core\SMTP_Manager::encrypt_password( $sanitized['smtp_password'] );
            }
        }
        // 移除临时字段
        unset( $sanitized['smtp_password_existing'] );
        
        // 合并：用新数据覆盖现有数据
        $merged = array_merge( $existing_options, $sanitized );
        
        return $merged;
    }

    private function sanitize_array_recursive( $arr ) {
        $result = array();
        foreach ( $arr as $k => $v ) {
            if ( is_array( $v ) ) {
                $result[ $k ] = $this->sanitize_array_recursive( $v );
            } else {
                // icon 字段允许 HTML 标签（如 <i class="iconfont icon-xxx"></i>）
                if ( $k === 'icon' ) {
                    $result[ $k ] = wp_kses_post( $v );
                } else {
                    $result[ $k ] = sanitize_text_field( $v );
                }
            }
        }
        return $result;
    }

    private function render_translate_tab( $options ) {
        echo '<tr><th colspan="2"><h2>前台语言切换</h2><p class="description">基于 translate.js 实现的前台多语言自动翻译功能</p></th></tr>';
        
        // 自定义复选框（添加隐藏字段以支持取消勾选）
        $translate_enable = isset( $options['translate_enable'] ) ? $options['translate_enable'] : '';
        echo '<tr><th scope="row">启用语言切换</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[translate_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[translate_enable]" value="1"' . checked( $translate_enable, '1', false ) . ' /> ';
        echo '开启后前台顶部导航栏显示语言切换按钮';
        echo '</label></td></tr>';
        
        $this->field_text( 'translate_js_url', 'translate.js 地址', $options, '留空使用本地 translate/translate.js，也可填写远程CDN地址' );
        
        echo '<tr><th colspan="2"><h2>语言列表</h2><p class="description">配置前台可切换的语言，语言简码参考 translate.js 文档</p></th></tr>';
        
        // 语言列表重复器
        $languages = isset( $options['translate_languages'] ) && is_array( $options['translate_languages'] ) ? $options['translate_languages'] : array();
        
        // 默认语言列表
        if ( empty( $languages ) ) {
            $languages = array(
                array( 'name' => '简体中文', 'code' => 'chinese_simplified', 'icon' => '' ),
                array( 'name' => '繁体中文', 'code' => 'chinese_traditional', 'icon' => '' ),
                array( 'name' => 'English', 'code' => 'english', 'icon' => '' ),
            );
        }
        
        echo '<tr><th>语言配置</th><td>';
        echo '<div id="translate-languages-container" style="margin-bottom: 15px;">';
        
        foreach ( $languages as $idx => $lang ) {
            echo '<div class="translate-lang-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ddd; position: relative; display: flex; gap: 15px; align-items: flex-start;">';
            echo '<a href="#" class="remove-translate-lang" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;">删除</a>';
            echo '<div style="flex: 1;">';
            echo '<label><strong>语言名称</strong></label><br>';
            echo '<input type="text" name="' . $this->option_name . '[translate_languages][' . $idx . '][name]" value="' . esc_attr( $lang['name'] ?? '' ) . '" style="width: 100%;" placeholder="如：简体中文" />';
            echo '</div>';
            echo '<div style="flex: 1;">';
            echo '<label><strong>语言简码</strong></label><br>';
            echo '<input type="text" name="' . $this->option_name . '[translate_languages][' . $idx . '][code]" value="' . esc_attr( $lang['code'] ?? '' ) . '" style="width: 100%;" placeholder="如：chinese_simplified" />';
            echo '</div>';
            echo '<div style="flex: 1;">';
            echo '<label><strong>图标（可选）</strong></label><br>';
            echo '<input type="text" name="' . $this->option_name . '[translate_languages][' . $idx . '][icon]" value="' . esc_attr( $lang['icon'] ?? '' ) . '" style="width: 100%;" placeholder="如：🇨🇳 或图片URL" />';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '<button type="button" id="add-translate-lang" class="button">+ 添加语言</button>';
        echo '<p class="description" style="margin-top: 10px;">常用语言简码：chinese_simplified（简体中文）、chinese_traditional（繁体中文）、english（英语）、korean（韩语）、japanese（日语）</p>';
        echo '</td></tr>';
        
        // JavaScript for dynamic language items
        ?>
        <script>
        jQuery(document).ready(function($) {
            var langIndex = <?php echo count( $languages ); ?>;
            
            $('#add-translate-lang').on('click', function() {
                var html = '<div class="translate-lang-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ddd; position: relative; display: flex; gap: 15px; align-items: flex-start;">' +
                    '<a href="#" class="remove-translate-lang" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;">删除</a>' +
                    '<div style="flex: 1;"><label><strong>语言名称</strong></label><br>' +
                    '<input type="text" name="<?php echo $this->option_name; ?>[translate_languages][' + langIndex + '][name]" style="width: 100%;" placeholder="如：简体中文" /></div>' +
                    '<div style="flex: 1;"><label><strong>语言简码</strong></label><br>' +
                    '<input type="text" name="<?php echo $this->option_name; ?>[translate_languages][' + langIndex + '][code]" style="width: 100%;" placeholder="如：chinese_simplified" /></div>' +
                    '<div style="flex: 1;"><label><strong>图标（可选）</strong></label><br>' +
                    '<input type="text" name="<?php echo $this->option_name; ?>[translate_languages][' + langIndex + '][icon]" style="width: 100%;" placeholder="如：🇨🇳 或图片URL" /></div>' +
                    '</div>';
                $('#translate-languages-container').append(html);
                langIndex++;
            });
            
            $(document).on('click', '.remove-translate-lang', function(e) {
                e.preventDefault();
                $(this).closest('.translate-lang-item').remove();
            });
        });
        </script>
        <?php
    }

    private function render_optimize_tab( $options ) {
        // 开发调试
        echo '<tr><th colspan="2"><h2>开发调试</h2><p class="description">临时调试功能，用于分析网站性能</p></th></tr>';
        
        $debug_mode = isset( $options['debug_mode'] ) ? $options['debug_mode'] : '';
        echo '<tr><th scope="row">启用调试模式</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[debug_mode]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[debug_mode]" value="1"' . checked( $debug_mode, '1', false ) . ' /> ';
        echo '在前台底部显示调试信息（SQL查询次数、页面加载时间、内存使用、缓存状态）';
        echo '</label>';
        echo '<p class="description" style="color: #ef4444;">⚠️ 开启后所有访客均可见！调试完毕后请立即关闭</p>';
        echo '</td></tr>';
        
        // 缓存管理
        echo '<tr><th colspan="2"><h2>缓存管理</h2><p class="description">管理主题资源文件的版本号，解决浏览器缓存问题</p></th></tr>';
        
        // 资源版本号
        $assets_version = isset( $options['assets_version'] ) ? $options['assets_version'] : '';
        echo '<tr><th scope="row">资源版本号</th>';
        echo '<td>';
        echo '<input type="text" name="' . $this->option_name . '[assets_version]" value="' . esc_attr( $assets_version ) . '" class="regular-text" placeholder="留空使用主题版本号" />';
        echo '<p class="description">自定义 CSS/JS 文件的版本号，修改后浏览器将重新加载资源文件。留空使用主题版本号 (' . DEVELOPER_STARTER_VERSION . ')</p>';
        echo '</td></tr>';
        
        // 一键刷新版本号按钮
        echo '<tr><th scope="row">刷新缓存</th>';
        echo '<td>';
        echo '<button type="button" class="button button-secondary" id="refresh-assets-version">一键刷新版本号</button>';
        echo '<span id="refresh-version-result" style="margin-left: 10px; color: #10b981;"></span>';
        echo '<p class="description">点击后将自动生成新的版本号，强制浏览器重新加载所有 CSS/JS 文件</p>';
        echo '</td></tr>';
        
        echo '<tr><th colspan="2"><h2>WordPress 优化设置</h2><p class="description">常用的 WordPress 性能和安全优化选项</p></th></tr>';
        
        // 禁用 Emoji 脚本
        $disable_emoji = isset( $options['disable_emoji'] ) ? $options['disable_emoji'] : '';
        echo '<tr><th scope="row">禁用 Emoji 脚本</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_emoji]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_emoji]" value="1"' . checked( $disable_emoji, '1', false ) . ' /> ';
        echo '移除 WordPress 自带的 Emoji 表情脚本，提升页面加载速度';
        echo '</label></td></tr>';
        
        // 禁用 Embeds
        $disable_embeds = isset( $options['disable_embeds'] ) ? $options['disable_embeds'] : '';
        echo '<tr><th scope="row">禁用 oEmbed</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_embeds]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_embeds]" value="1"' . checked( $disable_embeds, '1', false ) . ' /> ';
        echo '禁用 WordPress 自动嵌入功能，减少资源加载';
        echo '</label></td></tr>';
        
        // 禁用 XML-RPC
        $disable_xmlrpc = isset( $options['disable_xmlrpc'] ) ? $options['disable_xmlrpc'] : '';
        echo '<tr><th scope="row">禁用 XML-RPC</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_xmlrpc]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_xmlrpc]" value="1"' . checked( $disable_xmlrpc, '1', false ) . ' /> ';
        echo '禁用 XML-RPC 接口，防止暴力破解和 DDoS 攻击';
        echo '</label></td></tr>';
        
        // 移除 WordPress 版本号
        $remove_version = isset( $options['remove_wp_version'] ) ? $options['remove_wp_version'] : '';
        echo '<tr><th scope="row">隐藏 WP 版本号</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_wp_version]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_wp_version]" value="1"' . checked( $remove_version, '1', false ) . ' /> ';
        echo '从页面源码中移除 WordPress 版本信息，提升安全性';
        echo '</label></td></tr>';
        
        // 禁用 REST API 公开访问
        $disable_rest_api = isset( $options['disable_rest_api'] ) ? $options['disable_rest_api'] : '';
        echo '<tr><th scope="row">限制 REST API</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_rest_api]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_rest_api]" value="1"' . checked( $disable_rest_api, '1', false ) . ' /> ';
        echo '仅允许登录用户访问 REST API，防止用户信息泄露';
        echo '</label></td></tr>';
        
        // 移除 shortlink
        $remove_shortlink = isset( $options['remove_shortlink'] ) ? $options['remove_shortlink'] : '';
        echo '<tr><th scope="row">移除短链接</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_shortlink]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_shortlink]" value="1"' . checked( $remove_shortlink, '1', false ) . ' /> ';
        echo '从 head 中移除 shortlink 标签';
        echo '</label></td></tr>';
        
        // 移除 RSD/WLW 链接
        $remove_rsd_wlw = isset( $options['remove_rsd_wlw'] ) ? $options['remove_rsd_wlw'] : '';
        echo '<tr><th scope="row">移除 RSD/WLW</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_rsd_wlw]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_rsd_wlw]" value="1"' . checked( $remove_rsd_wlw, '1', false ) . ' /> ';
        echo '移除 RSD 和 Windows Live Writer 链接';
        echo '</label></td></tr>';
        
        // 禁用 Pingback/Trackback
        $disable_pingback = isset( $options['disable_pingback'] ) ? $options['disable_pingback'] : '';
        echo '<tr><th scope="row">禁用 Pingback/Trackback</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_pingback]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_pingback]" value="1"' . checked( $disable_pingback, '1', false ) . ' /> ';
        echo '禁用 Pingback 和 Trackback 功能，减少垃圾评论和 DDoS 攻击风险';
        echo '</label></td></tr>';
        
        // 禁用文章修订版本
        $disable_revisions = isset( $options['disable_revisions'] ) ? $options['disable_revisions'] : '';
        echo '<tr><th scope="row">限制修订版本</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_revisions]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_revisions]" value="1"' . checked( $disable_revisions, '1', false ) . ' /> ';
        echo '限制文章修订版本数量为 3 个，减少数据库占用';
        echo '</label></td></tr>';
        
        // 禁用 Gutenberg 编辑器
        $disable_gutenberg = isset( $options['disable_gutenberg'] ) ? $options['disable_gutenberg'] : '';
        echo '<tr><th scope="row">禁用 Gutenberg</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_gutenberg]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_gutenberg]" value="1"' . checked( $disable_gutenberg, '1', false ) . ' /> ';
        echo '使用经典编辑器替代 Gutenberg 块编辑器';
        echo '</label></td></tr>';
        
        // 禁用 Gutenberg 区块小工具
        $disable_block_widgets = isset( $options['disable_block_widgets'] ) ? $options['disable_block_widgets'] : '';
        echo '<tr><th scope="row">禁用区块小工具</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_block_widgets]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_block_widgets]" value="1"' . checked( $disable_block_widgets, '1', false ) . ' /> ';
        echo '使用经典小工具界面替代 Gutenberg 区块小工具';
        echo '</label></td></tr>';
        
        // 性能优化
        echo '<tr><th colspan="2"><h2>性能优化</h2><p class="description">前端资源加载优化</p></th></tr>';
        
        // 延迟加载
        $lazy_load_images = isset( $options['lazy_load_images'] ) ? $options['lazy_load_images'] : '';
        echo '<tr><th scope="row">图片延迟加载</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[lazy_load_images]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[lazy_load_images]" value="1"' . checked( $lazy_load_images, '1', false ) . ' /> ';
        echo '启用图片懒加载，图片进入视口时才加载（使用原生 loading="lazy"）';
        echo '</label></td></tr>';
        
        $lazy_load_iframes = isset( $options['lazy_load_iframes'] ) ? $options['lazy_load_iframes'] : '';
        echo '<tr><th scope="row">视频/iframe 延迟加载</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[lazy_load_iframes]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[lazy_load_iframes]" value="1"' . checked( $lazy_load_iframes, '1', false ) . ' /> ';
        echo '启用 iframe 和嵌入视频的懒加载';
        echo '</label></td></tr>';
        
        // WebP 支持
        echo '<tr><th colspan="2"><h2>WebP 图片转换</h2><p class="description">将图片自动转换为 WebP 格式以减少文件大小</p></th></tr>';
        
        $webp_enable = isset( $options['webp_enable'] ) ? $options['webp_enable'] : '';
        echo '<tr><th scope="row">启用 WebP 转换</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[webp_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[webp_enable]" value="1"' . checked( $webp_enable, '1', false ) . ' /> ';
        echo '上传图片时自动生成 WebP 格式副本';
        echo '</label>';
        
        // 检测 GD 库 WebP 支持
        $webp_supported = function_exists( 'imagewebp' );
        if ( $webp_supported ) {
            echo '<p class="description" style="color: #10b981;">✓ 服务器支持 WebP（GD 库已启用）</p>';
        } else {
            echo '<p class="description" style="color: #f59e0b;">⚠ 服务器不支持 WebP，请安装 GD 库的 WebP 模块</p>';
        }
        echo '</td></tr>';
        
        $webp_quality = isset( $options['webp_quality'] ) ? $options['webp_quality'] : '80';
        echo '<tr><th scope="row">WebP 质量</th>';
        echo '<td><input type="number" name="' . $this->option_name . '[webp_quality]" value="' . esc_attr( $webp_quality ) . '" min="1" max="100" class="small-text" /> %';
        echo '<p class="description">WebP 图片压缩质量（1-100），建议 75-85</p>';
        echo '</td></tr>';
        
        // DNS 预解析
        echo '<tr><th colspan="2"><h2>资源预加载</h2><p class="description">提前解析和连接外部资源，加速页面加载</p></th></tr>';
        
        $this->field_textarea( 'dns_prefetch', 'DNS 预解析域名', $options, '每行一个域名（不含 http://），如：fonts.googleapis.com、cdn.jsdelivr.net' );
        $this->field_textarea( 'preconnect_urls', '预连接域名', $options, '每行一个域名（不含 http://），如：fonts.gstatic.com。预连接比预解析更快但消耗更多资源' );
        
        // 心跳控制
        echo '<tr><th colspan="2"><h2>心跳控制</h2><p class="description">优化 WordPress Admin 后台心跳频率，减少服务器负载</p></th></tr>';
        
        $heartbeat_control = isset( $options['heartbeat_control'] ) ? $options['heartbeat_control'] : '';
        echo '<tr><th scope="row">心跳优化</th>';
        echo '<td><select name="' . $this->option_name . '[heartbeat_control]">';
        echo '<option value=""' . selected( $heartbeat_control, '', false ) . '>不修改（默认 15 秒）</option>';
        echo '<option value="30"' . selected( $heartbeat_control, '30', false ) . '>减慢至 30 秒</option>';
        echo '<option value="60"' . selected( $heartbeat_control, '60', false ) . '>减慢至 60 秒</option>';
        echo '<option value="120"' . selected( $heartbeat_control, '120', false ) . '>减慢至 120 秒</option>';
        echo '<option value="disable_frontend"' . selected( $heartbeat_control, 'disable_frontend', false ) . '>仅禁用前台</option>';
        echo '<option value="disable_all"' . selected( $heartbeat_control, 'disable_all', false ) . '>完全禁用（不推荐）</option>';
        echo '</select>';
        echo '<p class="description">心跳 API 用于自动保存和在线状态检测，频繁请求会增加服务器负担</p>';
        echo '</td></tr>';
        
        // 安全增强
        echo '<tr><th colspan="2"><h2>安全增强</h2><p class="description">增强网站安全性，防止常见攻击</p></th></tr>';
        
        // 禁用作者存档页
        $disable_author_archive = isset( $options['disable_author_archive'] ) ? $options['disable_author_archive'] : '';
        echo '<tr><th scope="row">禁用作者存档页</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_author_archive]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_author_archive]" value="1"' . checked( $disable_author_archive, '1', false ) . ' /> ';
        echo '禁用 ?author=1 等作者存档页面，防止用户名枚举';
        echo '</label></td></tr>';
        
        // 禁用文件编辑器
        $disable_file_edit = isset( $options['disable_file_edit'] ) ? $options['disable_file_edit'] : '';
        echo '<tr><th scope="row">禁用文件编辑器</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_file_edit]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_file_edit]" value="1"' . checked( $disable_file_edit, '1', false ) . ' /> ';
        echo '禁用后台主题和插件的文件编辑功能，防止误操作导致网站崩溃';
        echo '</label></td></tr>';
        
        // 登录安全
        $login_error_hide = isset( $options['login_error_hide'] ) ? $options['login_error_hide'] : '';
        echo '<tr><th scope="row">隐藏登录错误信息</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[login_error_hide]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[login_error_hide]" value="1"' . checked( $login_error_hide, '1', false ) . ' /> ';
        echo '登录失败时不提示具体原因（用户名或密码），防止暴力破解';
        echo '</label></td></tr>';
        
        // 禁止右键和复制
        echo '<tr><th colspan="2"><h2>内容保护</h2><p class="description">保护网站内容防止被轻易复制</p></th></tr>';
        
        $disable_right_click = isset( $options['disable_right_click'] ) ? $options['disable_right_click'] : '';
        echo '<tr><th scope="row">禁用右键菜单</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_right_click]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_right_click]" value="1"' . checked( $disable_right_click, '1', false ) . ' /> ';
        echo '禁止访客右键菜单（登录用户不受影响）';
        echo '</label></td></tr>';
        
        $disable_text_select = isset( $options['disable_text_select'] ) ? $options['disable_text_select'] : '';
        echo '<tr><th scope="row">禁止文本选择</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_text_select]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_text_select]" value="1"' . checked( $disable_text_select, '1', false ) . ' /> ';
        echo '禁止访客选择复制文本（登录用户不受影响）';
        echo '</label></td></tr>';
        
        // 评论优化
        echo '<tr><th colspan="2"><h2>评论优化</h2><p class="description">减少垃圾评论，优化评论功能</p></th></tr>';
        
        $disable_comments = isset( $options['disable_comments'] ) ? $options['disable_comments'] : '';
        echo '<tr><th scope="row">完全禁用评论</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_comments]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_comments]" value="1"' . checked( $disable_comments, '1', false ) . ' /> ';
        echo '禁用整个网站的评论功能（适合企业官网）';
        echo '</label></td></tr>';
        
        $comment_honeypot = isset( $options['comment_honeypot'] ) ? $options['comment_honeypot'] : '';
        echo '<tr><th scope="row">评论蜜罐陷阱</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[comment_honeypot]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[comment_honeypot]" value="1"' . checked( $comment_honeypot, '1', false ) . ' /> ';
        echo '添加隐藏字段检测机器人垃圾评论（无需验证码）';
        echo '</label></td></tr>';
        
        // 输出优化（Head 清理）
        echo '<tr><th colspan="2"><h2>输出优化（Head 清理）</h2><p class="description">移除 WordPress 在页面头部输出的多余信息，精简 HTML 代码</p></th></tr>';
        
        $remove_adjacent_posts = isset( $options['remove_adjacent_posts'] ) ? $options['remove_adjacent_posts'] : '';
        echo '<tr><th scope="row">移除相邻文章链接</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_adjacent_posts]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_adjacent_posts]" value="1"' . checked( $remove_adjacent_posts, '1', false ) . ' /> ';
        echo '移除 head 中的 prev/next 相邻文章链接标签';
        echo '</label></td></tr>';
        
        $remove_feed_links = isset( $options['remove_feed_links'] ) ? $options['remove_feed_links'] : '';
        echo '<tr><th scope="row">移除 Feed 链接</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_feed_links]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_feed_links]" value="1"' . checked( $remove_feed_links, '1', false ) . ' /> ';
        echo '移除 head 中的 RSS/Atom 订阅链接';
        echo '</label></td></tr>';
        
        $remove_json_api_link = isset( $options['remove_json_api_link'] ) ? $options['remove_json_api_link'] : '';
        echo '<tr><th scope="row">移除 JSON API 链接</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_json_api_link]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_json_api_link]" value="1"' . checked( $remove_json_api_link, '1', false ) . ' /> ';
        echo '移除 head 中的 REST API 发现链接';
        echo '</label></td></tr>';
        
        $remove_dns_prefetch_hints = isset( $options['remove_dns_prefetch_hints'] ) ? $options['remove_dns_prefetch_hints'] : '';
        echo '<tr><th scope="row">移除 DNS 预取提示</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_dns_prefetch_hints]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_dns_prefetch_hints]" value="1"' . checked( $remove_dns_prefetch_hints, '1', false ) . ' /> ';
        echo '移除 WordPress 自动添加的 DNS 预取提示（如 s.w.org）';
        echo '</label></td></tr>';
        
        $remove_gutenberg_css = isset( $options['remove_gutenberg_css'] ) ? $options['remove_gutenberg_css'] : '';
        echo '<tr><th scope="row">移除 Gutenberg 样式</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_gutenberg_css]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_gutenberg_css]" value="1"' . checked( $remove_gutenberg_css, '1', false ) . ' /> ';
        echo '移除前端加载的 Gutenberg 块编辑器样式（wp-block-library）';
        echo '</label></td></tr>';
        
        $remove_global_styles = isset( $options['remove_global_styles'] ) ? $options['remove_global_styles'] : '';
        echo '<tr><th scope="row">移除全局样式</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_global_styles]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_global_styles]" value="1"' . checked( $remove_global_styles, '1', false ) . ' /> ';
        echo '移除 WordPress 全局样式和 SVG 滤镜';
        echo '</label></td></tr>';
        
        // 图片优化
        echo '<tr><th colspan="2"><h2>图片尺寸优化</h2><p class="description">控制 WordPress 自动生成的图片缩略图，节省服务器空间</p></th></tr>';
        
        $disable_default_thumbnails = isset( $options['disable_default_thumbnails'] ) ? $options['disable_default_thumbnails'] : '';
        echo '<tr><th scope="row">禁用大图压缩</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_default_thumbnails]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_default_thumbnails]" value="1"' . checked( $disable_default_thumbnails, '1', false ) . ' /> ';
        echo '禁用 WordPress 自动缩放大于 2560px 的图片';
        echo '</label></td></tr>';
        
        $disable_image_sizes = isset( $options['disable_image_sizes'] ) ? $options['disable_image_sizes'] : '';
        echo '<tr><th scope="row">禁用多尺寸缩略图</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[disable_image_sizes]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[disable_image_sizes]" value="1"' . checked( $disable_image_sizes, '1', false ) . ' /> ';
        echo '禁止 WordPress 上传时自动生成多个尺寸的缩略图，节省服务器空间';
        echo '</label>';
        echo '<p class="description" style="color: #f59e0b;">⚠️ 启用后新上传的图片只保留原图，可能影响依赖特定尺寸的功能</p>';
        echo '</td></tr>';
        
        // 链接优化（SEO）
        echo '<tr><th colspan="2"><h2>链接优化（SEO）</h2><p class="description">优化网站链接结构，提升搜索引擎友好度</p></th></tr>';
        
        $remove_category_base = isset( $options['remove_category_base'] ) ? $options['remove_category_base'] : '';
        echo '<tr><th scope="row">分类去 category</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_category_base]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_category_base]" value="1"' . checked( $remove_category_base, '1', false ) . ' /> ';
        echo '分类链接去除 /category/ 前缀，如 /category/news/ 变为 /news/';
        echo '</label>';
        echo '<p class="description" style="color: #10b981;">✓ 启用后自动刷新固定链接规则，有利于 SEO 优化</p>';
        echo '</td></tr>';
        
        // 前端资源优化
        echo '<tr><th colspan="2"><h2>前端资源优化</h2><p class="description">优化前端资源加载，提升页面性能</p></th></tr>';
        
        $remove_assets_version = isset( $options['remove_assets_version'] ) ? $options['remove_assets_version'] : '';
        echo '<tr><th scope="row">移除资源版本号</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[remove_assets_version]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[remove_assets_version]" value="1"' . checked( $remove_assets_version, '1', false ) . ' /> ';
        echo '移除 CSS/JS 资源链接中的 ?ver= 参数';
        echo '</label>';
        echo '<p class="description">可提升浏览器缓存命中率，但更新后可能需要手动清除浏览器缓存</p>';
        echo '</td></tr>';
        
        $html_minify = isset( $options['html_minify'] ) ? $options['html_minify'] : '';
        echo '<tr><th scope="row">HTML 代码压缩</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[html_minify]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[html_minify]" value="1"' . checked( $html_minify, '1', false ) . ' /> ';
        echo '压缩 HTML 输出，移除多余空白和换行';
        echo '</label>';
        echo '<p class="description" style="color: #f59e0b;">⚠️ 实验性功能：可能影响内联 JS/CSS，如遇问题请关闭此选项</p>';
        echo '</td></tr>';
        
        // 数据库清理
        echo '<tr><th colspan="2"><h2>数据库优化</h2><p class="description">清理冗余数据，保持数据库精简</p></th></tr>';
        
        $auto_clean_revisions = isset( $options['auto_clean_revisions'] ) ? $options['auto_clean_revisions'] : '';
        echo '<tr><th scope="row">自动清理修订版本</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[auto_clean_revisions]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[auto_clean_revisions]" value="1"' . checked( $auto_clean_revisions, '1', false ) . ' /> ';
        echo '每周自动清理超过 30 天的文章修订版本';
        echo '</label></td></tr>';
        
        $auto_clean_trash = isset( $options['auto_clean_trash'] ) ? $options['auto_clean_trash'] : '';
        echo '<tr><th scope="row">自动清空回收站</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[auto_clean_trash]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[auto_clean_trash]" value="1"' . checked( $auto_clean_trash, '1', false ) . ' /> ';
        echo '设置回收站自动清空时间为 7 天（默认 30 天）';
        echo '</label></td></tr>';
        
        // 一键数据库清理
        echo '<tr><th colspan="2"><h3>一键数据库清理</h3><p class="description">手动清理数据库中的冗余数据，请先备份数据库</p></th></tr>';
        
        echo '<tr><th scope="row">数据统计</th>';
        echo '<td>';
        echo '<div id="db-stats-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px;">';
        echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;" id="db-stats-grid">';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>📝 文章修订版本</span><span id="stat-revisions" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>📋 自动草稿</span><span id="stat-drafts" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>🗑️ 回收站文章</span><span id="stat-trash" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>🚫 垃圾评论</span><span id="stat-spam" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>📎 孤立文章元数据</span><span id="stat-orphan-postmeta" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>💬 孤立评论元数据</span><span id="stat-orphan-commentmeta" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>🔗 孤立关系数据</span><span id="stat-orphan-relationships" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>🔔 Pingback/Trackback</span><span id="stat-pingbacks" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>🏷️ 未使用标签</span><span id="stat-unused-tags" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '<div class="db-stat-item" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px solid #e2e8f0;">';
        echo '<span>⏳ 过期 Transients</span><span id="stat-transients" style="font-weight: 600; color: #64748b;">加载中...</span></div>';
        echo '</div>';
        echo '<div style="margin-top: 12px; text-align: right;">';
        echo '<button type="button" class="button" id="refresh-db-stats">🔄 刷新统计</button>';
        echo '</div>';
        echo '</div>';
        echo '</td></tr>';
        
        echo '<tr><th scope="row">选择清理项</th>';
        echo '<td>';
        echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 15px;">';
        echo '<label><input type="checkbox" name="db_clean_revisions" value="1" checked /> 文章修订版本</label>';
        echo '<label><input type="checkbox" name="db_clean_drafts" value="1" checked /> 自动草稿</label>';
        echo '<label><input type="checkbox" name="db_clean_trash" value="1" checked /> 回收站文章</label>';
        echo '<label><input type="checkbox" name="db_clean_spam" value="1" checked /> 垃圾评论</label>';
        echo '<label><input type="checkbox" name="db_clean_orphan_postmeta" value="1" checked /> 孤立的文章元数据</label>';
        echo '<label><input type="checkbox" name="db_clean_orphan_commentmeta" value="1" checked /> 孤立的评论元数据</label>';
        echo '<label><input type="checkbox" name="db_clean_orphan_relationships" value="1" checked /> 孤立的关系数据</label>';
        echo '<label><input type="checkbox" name="db_clean_pingbacks" value="1" /> Pingback/Trackback 记录</label>';
        echo '<label><input type="checkbox" name="db_clean_unused_tags" value="1" /> 未使用的标签</label>';
        echo '<label><input type="checkbox" name="db_clean_transients" value="1" /> 过期的 Transients 缓存</label>';
        echo '</div>';
        echo '<button type="button" class="button button-secondary" id="run-db-cleanup" style="margin-right: 10px;">🧹 一键清理数据库</button>';
        echo '<span id="db-cleanup-result" style="color: #10b981;"></span>';
        echo '<p class="description" style="margin-top: 10px; color: #ef4444;">⚠️ 此操作不可逆，请确保已备份数据库！</p>';
        echo '</td></tr>';
    }

    private function render_auth_tab( $options ) {
        echo '<tr><th colspan="2"><h2>自定义登录注册</h2><p class="description">启用主题自带的现代化登录注册页面，替代 WordPress 默认页面</p></th></tr>';
        
        // 启用自定义认证
        $custom_auth_enable = isset( $options['custom_auth_enable'] ) ? $options['custom_auth_enable'] : '';
        echo '<tr><th scope="row">启用自定义页面</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[custom_auth_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[custom_auth_enable]" value="1"' . checked( $custom_auth_enable, '1', false ) . ' /> ';
        echo '使用主题自定义的登录、注册、找回密码页面';
        echo '</label></td></tr>';
        
        // 滑动验证码
        $auth_captcha_enable = isset( $options['auth_captcha_enable'] ) ? $options['auth_captcha_enable'] : '';
        echo '<tr><th scope="row">滑动验证码</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[auth_captcha_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[auth_captcha_enable]" value="1"' . checked( $auth_captcha_enable, '1', false ) . ' /> ';
        echo '在登录、注册、找回密码表单中启用滑动验证码';
        echo '</label></td></tr>';
        
        // 密码强度
        $password_strength = isset( $options['password_strength'] ) ? $options['password_strength'] : 'medium';
        echo '<tr><th scope="row">密码强度要求</th>';
        echo '<td><select name="' . $this->option_name . '[password_strength]">';
        echo '<option value="weak"' . selected( $password_strength, 'weak', false ) . '>弱（至少6位）</option>';
        echo '<option value="medium"' . selected( $password_strength, 'medium', false ) . '>中（至少8位，含字母和数字）</option>';
        echo '<option value="strong"' . selected( $password_strength, 'strong', false ) . '>强（至少10位，含大小写、数字、特殊字符）</option>';
        echo '</select>';
        echo '<p class="description">注册和重置密码时的密码强度要求</p>';
        echo '</td></tr>';
        
        echo '<tr><th colspan="2"><h2>跳转设置</h2></th></tr>';
        
        // 登录成功跳转
        $login_redirect = isset( $options['login_redirect_url'] ) ? $options['login_redirect_url'] : '';
        echo '<tr><th scope="row">登录成功跳转</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[login_redirect_url]" value="' . esc_attr( $login_redirect ) . '" class="regular-text" placeholder="留空默认跳转首页" />';
        echo '<p class="description">登录成功后跳转的URL地址</p>';
        echo '</td></tr>';
        
        // 注册成功跳转
        $register_redirect = isset( $options['register_redirect_url'] ) ? $options['register_redirect_url'] : '';
        echo '<tr><th scope="row">注册成功跳转</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[register_redirect_url]" value="' . esc_attr( $register_redirect ) . '" class="regular-text" placeholder="留空默认跳转首页" />';
        echo '<p class="description">注册成功后跳转的URL地址</p>';
        echo '</td></tr>';
        
        // 安全设置
        echo '<tr><th colspan="2"><h2>登录安全</h2><p class="description">防止暴力破解和恶意登录尝试</p></th></tr>';
        
        // 登录失败限制开关
        $login_limit_enable = isset( $options['login_limit_enable'] ) ? $options['login_limit_enable'] : '';
        echo '<tr><th scope="row">启用登录限制</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[login_limit_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[login_limit_enable]" value="1"' . checked( $login_limit_enable, '1', false ) . ' /> ';
        echo '限制登录失败次数，防止暴力破解';
        echo '</label></td></tr>';
        
        // 最大尝试次数
        $login_max_attempts = isset( $options['login_max_attempts'] ) ? $options['login_max_attempts'] : '5';
        echo '<tr><th scope="row">最大尝试次数</th>';
        echo '<td><input type="number" name="' . $this->option_name . '[login_max_attempts]" value="' . esc_attr( $login_max_attempts ) . '" min="1" max="20" class="small-text" /> 次';
        echo '<p class="description">密码错误达到此次数后将暂时锁定登录</p>';
        echo '</td></tr>';
        
        // 锁定时间
        $login_lockout_duration = isset( $options['login_lockout_duration'] ) ? $options['login_lockout_duration'] : '15';
        echo '<tr><th scope="row">锁定时间</th>';
        echo '<td><input type="number" name="' . $this->option_name . '[login_lockout_duration]" value="' . esc_attr( $login_lockout_duration ) . '" min="1" max="1440" class="small-text" /> 分钟';
        echo '<p class="description">登录被锁定后需要等待的时间</p>';
        echo '</td></tr>';
        
        // 登录失败通知
        $login_notify_admin = isset( $options['login_notify_admin'] ) ? $options['login_notify_admin'] : '';
        echo '<tr><th scope="row">失败通知管理员</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[login_notify_admin]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[login_notify_admin]" value="1"' . checked( $login_notify_admin, '1', false ) . ' /> ';
        echo '当账户被锁定时发送邮件通知管理员';
        echo '</label></td></tr>';
        
        // 显示剩余尝试次数
        $login_show_remaining = isset( $options['login_show_remaining'] ) ? $options['login_show_remaining'] : '1';
        echo '<tr><th scope="row">显示剩余次数</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[login_show_remaining]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[login_show_remaining]" value="1"' . checked( $login_show_remaining, '1', false ) . ' /> ';
        echo '登录失败时提示用户剩余尝试次数';
        echo '</label></td></tr>';
        
        // 注册协议设置
        echo '<tr><th colspan="2"><h2>注册协议</h2><p class="description">用户注册时需要同意的服务条款设置</p></th></tr>';
        
        // 启用注册协议复选框
        $register_agreement_enable = isset( $options['register_agreement_enable'] ) ? $options['register_agreement_enable'] : '';
        echo '<tr><th scope="row">启用注册协议</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[register_agreement_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[register_agreement_enable]" value="1"' . checked( $register_agreement_enable, '1', false ) . ' /> ';
        echo '用户注册时必须勾选同意协议复选框才能注册';
        echo '</label></td></tr>';
        
        // 协议文字
        $register_agreement_text = isset( $options['register_agreement_text'] ) ? $options['register_agreement_text'] : '我已阅读并同意';
        echo '<tr><th scope="row">协议前置文字</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[register_agreement_text]" value="' . esc_attr( $register_agreement_text ) . '" class="regular-text" placeholder="我已阅读并同意" />';
        echo '<p class="description">显示在复选框后面的文字，如：我已阅读并同意</p>';
        echo '</td></tr>';
        
        // 协议链接文字
        $register_agreement_link_text = isset( $options['register_agreement_link_text'] ) ? $options['register_agreement_link_text'] : '《用户服务协议》';
        echo '<tr><th scope="row">协议链接文字</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[register_agreement_link_text]" value="' . esc_attr( $register_agreement_link_text ) . '" class="regular-text" placeholder="《用户服务协议》" />';
        echo '<p class="description">可点击的协议链接文字</p>';
        echo '</td></tr>';
        
        // 协议链接URL
        $register_agreement_url = isset( $options['register_agreement_url'] ) ? $options['register_agreement_url'] : '';
        echo '<tr><th scope="row">协议页面链接</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[register_agreement_url]" value="' . esc_attr( $register_agreement_url ) . '" class="regular-text" placeholder="https://example.com/terms" />';
        echo '<p class="description">用户服务协议页面的完整URL地址</p>';
        echo '</td></tr>';
        
        echo '<tr><th colspan="2"><h2>页面ID</h2><p class="description">主题激活时自动创建，一般无需修改</p></th></tr>';
        
        // 登录页面ID
        $login_page_id = isset( $options['login_page_id'] ) ? $options['login_page_id'] : '';
        echo '<tr><th scope="row">登录页面</th>';
        echo '<td><input type="number" name="' . $this->option_name . '[login_page_id]" value="' . esc_attr( $login_page_id ) . '" class="small-text" />';
        if ( $login_page_id ) {
            echo ' <a href="' . get_permalink( $login_page_id ) . '" target="_blank">查看页面</a>';
        }
        echo '</td></tr>';
        
        // 注册页面ID
        $register_page_id = isset( $options['register_page_id'] ) ? $options['register_page_id'] : '';
        echo '<tr><th scope="row">注册页面</th>';
        echo '<td><input type="number" name="' . $this->option_name . '[register_page_id]" value="' . esc_attr( $register_page_id ) . '" class="small-text" />';
        if ( $register_page_id ) {
            echo ' <a href="' . get_permalink( $register_page_id ) . '" target="_blank">查看页面</a>';
        }
        echo '</td></tr>';
        
        // 找回密码页面ID
        $forgot_page_id = isset( $options['forgot_password_page_id'] ) ? $options['forgot_password_page_id'] : '';
        echo '<tr><th scope="row">找回密码页面</th>';
        echo '<td><input type="number" name="' . $this->option_name . '[forgot_password_page_id]" value="' . esc_attr( $forgot_page_id ) . '" class="small-text" />';
        if ( $forgot_page_id ) {
            echo ' <a href="' . get_permalink( $forgot_page_id ) . '" target="_blank">查看页面</a>';
        }
        echo '</td></tr>';
        
        // 用户头像设置
        echo '<tr><th colspan="2"><h2>用户头像设置</h2><p class="description">自定义所有用户的默认头像，替代WordPress默认的Gravatar头像服务</p></th></tr>';
        
        // 默认头像
        $default_avatar = isset( $options['default_avatar'] ) ? $options['default_avatar'] : '';
        echo '<tr><th scope="row">默认用户头像</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[default_avatar]" value="' . esc_attr( $default_avatar ) . '" class="regular-text ds-image-url" /> ';
        echo '<button type="button" class="button ds-upload-image-btn">选择图片</button> ';
        echo '<button type="button" class="button ds-remove-image-btn">移除</button>';
        if ( $default_avatar ) {
            echo '<br/><img src="' . esc_url( $default_avatar ) . '" class="ds-image-preview" style="max-width:100px;margin-top:8px;border-radius:50%;" />';
        }
        echo '<p class="description">设置后，所有未自定义头像的用户都将显示此头像，不再使用Gravatar头像服务</p>';
        echo '</td></tr>';
        
        // 允许用户上传头像
        $user_avatar_upload_enable = isset( $options['user_avatar_upload_enable'] ) ? $options['user_avatar_upload_enable'] : '';
        echo '<tr><th scope="row">允许用户上传头像</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[user_avatar_upload_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[user_avatar_upload_enable]" value="1"' . checked( $user_avatar_upload_enable, '1', false ) . ' /> ';
        echo '启用后，用户可以在个人中心上传自己的头像图片';
        echo '</label></td></tr>';
    }
    
    private function render_announcement_tab( $options ) {
        // 公告开关
        echo '<tr><th colspan="2"><h2>公告设置</h2><p class="description">配置全站公告弹窗，支持多种类型和显示条件</p></th></tr>';
        
        $enable = isset( $options['announcement_enable'] ) ? $options['announcement_enable'] : '';
        echo '<tr><th scope="row">启用公告</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[announcement_enable]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[announcement_enable]" value="1"' . checked( $enable, '1', false ) . ' /> ';
        echo '开启后前台将显示公告弹窗';
        echo '</label></td></tr>';
        
        // 公告类型
        $type = isset( $options['announcement_type'] ) ? $options['announcement_type'] : 'normal';
        echo '<tr><th scope="row">公告类型</th>';
        echo '<td><select name="' . $this->option_name . '[announcement_type]">';
        $types = array(
            'normal'     => '普通公告',
            'marketing'  => '营销活动',
            'image'      => '图片公告',
            'image_text' => '图文混排',
        );
        foreach ( $types as $k => $v ) {
            echo '<option value="' . esc_attr( $k ) . '"' . selected( $type, $k, false ) . '>' . esc_html( $v ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">不同类型有不同的样式风格</p>';
        echo '</td></tr>';
        
        // 公告内容
        echo '<tr><th colspan="2"><h2>公告内容</h2></th></tr>';
        
        $title = isset( $options['announcement_title'] ) ? $options['announcement_title'] : '';
        echo '<tr><th scope="row">公告标题</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_title]" value="' . esc_attr( $title ) . '" class="large-text" /></td></tr>';
        
        $content = isset( $options['announcement_content'] ) ? $options['announcement_content'] : '';
        echo '<tr><th scope="row">公告内容</th>';
        echo '<td><textarea name="' . $this->option_name . '[announcement_content]" rows="5" class="large-text">' . esc_textarea( $content ) . '</textarea>';
        echo '<p class="description">支持HTML标签</p></td></tr>';
        
        $image = isset( $options['announcement_image'] ) ? $options['announcement_image'] : '';
        echo '<tr><th scope="row">公告图片</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_image]" value="' . esc_attr( $image ) . '" class="regular-text ds-image-url" /> ';
        echo '<button type="button" class="button ds-upload-image-btn">选择图片</button>';
        if ( $image ) {
            echo '<br/><img src="' . esc_url( $image ) . '" class="ds-image-preview" style="max-width:200px;margin-top:8px;" />';
        }
        echo '<p class="description">图片公告和图文混排类型需要上传图片</p></td></tr>';
        
        $btn_text = isset( $options['announcement_btn_text'] ) ? $options['announcement_btn_text'] : '';
        echo '<tr><th scope="row">按钮文字</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_btn_text]" value="' . esc_attr( $btn_text ) . '" class="regular-text" placeholder="如：立即查看" /></td></tr>';
        
        $btn_url = isset( $options['announcement_btn_url'] ) ? $options['announcement_btn_url'] : '';
        echo '<tr><th scope="row">按钮链接</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_btn_url]" value="' . esc_attr( $btn_url ) . '" class="large-text" placeholder="https://" /></td></tr>';
        
        // 普通/图片/图文按钮样式设置
        echo '<tr><th colspan="2"><h2>普通/图片/图文公告按钮样式</h2><p class="description">自定义普通公告、图片公告、图文混排公告的按钮颜色，支持渐变色</p></th></tr>';
        
        $normal_btn_bg = isset( $options['announcement_normal_btn_bg'] ) ? $options['announcement_normal_btn_bg'] : '';
        echo '<tr><th scope="row">按钮背景色</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_normal_btn_bg]" value="' . esc_attr( $normal_btn_bg ) . '" class="regular-text" placeholder="如: #2563eb 或 linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />';
        echo '<p class="description">留空使用主题主色调，支持纯色（如 #2563eb）或渐变色（如 linear-gradient(135deg, #667eea 0%, #764ba2 100%)）</p></td></tr>';
        
        $normal_btn_color = isset( $options['announcement_normal_btn_color'] ) ? $options['announcement_normal_btn_color'] : '';
        echo '<tr><th scope="row">按钮文字颜色</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_normal_btn_color]" value="' . esc_attr( $normal_btn_color ) . '" class="regular-text" placeholder="如: #ffffff" />';
        echo '<p class="description">留空使用白色 #fff</p></td></tr>';
        
        $normal_btn_hover_bg = isset( $options['announcement_normal_btn_hover_bg'] ) ? $options['announcement_normal_btn_hover_bg'] : '';
        echo '<tr><th scope="row">按钮悬停背景色</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_normal_btn_hover_bg]" value="' . esc_attr( $normal_btn_hover_bg ) . '" class="regular-text" placeholder="如: #1d4ed8 或渐变色" />';
        echo '<p class="description">留空自动使用背景色的深色版本，支持纯色或渐变色</p></td></tr>';
        
        // 营销活动公告样式设置
        echo '<tr><th colspan="2"><h2>营销活动公告样式</h2><p class="description">自定义营销活动公告的窗口背景和按钮颜色，支持渐变色</p></th></tr>';
        
        $marketing_modal_bg = isset( $options['announcement_marketing_modal_bg'] ) ? $options['announcement_marketing_modal_bg'] : '';
        echo '<tr><th scope="row">窗口背景色</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_marketing_modal_bg]" value="' . esc_attr( $marketing_modal_bg ) . '" class="large-text" placeholder="如: linear-gradient(135deg, #ff416c 0%, #ff8a00 100%)" />';
        echo '<p class="description">留空使用默认橙红渐变，支持纯色或渐变色</p></td></tr>';
        
        $marketing_btn_bg = isset( $options['announcement_marketing_btn_bg'] ) ? $options['announcement_marketing_btn_bg'] : '';
        echo '<tr><th scope="row">按钮背景色</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_marketing_btn_bg]" value="' . esc_attr( $marketing_btn_bg ) . '" class="regular-text" placeholder="如: #ffffff" />';
        echo '<p class="description">留空使用白色 #fff</p></td></tr>';
        
        $marketing_btn_color = isset( $options['announcement_marketing_btn_color'] ) ? $options['announcement_marketing_btn_color'] : '';
        echo '<tr><th scope="row">按钮文字颜色</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_marketing_btn_color]" value="' . esc_attr( $marketing_btn_color ) . '" class="regular-text" placeholder="如: #764ba2" />';
        echo '<p class="description">留空使用紫色 #764ba2</p></td></tr>';
        
        $marketing_btn_hover_bg = isset( $options['announcement_marketing_btn_hover_bg'] ) ? $options['announcement_marketing_btn_hover_bg'] : '';
        echo '<tr><th scope="row">按钮悬停背景色</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_marketing_btn_hover_bg]" value="' . esc_attr( $marketing_btn_hover_bg ) . '" class="regular-text" placeholder="如: #f8fafc" />';
        echo '<p class="description">留空使用浅灰色 #f8fafc</p></td></tr>';
        
        // 显示设置
        echo '<tr><th colspan="2"><h2>显示设置</h2></th></tr>';
        
        $display_on = isset( $options['announcement_display_on'] ) ? $options['announcement_display_on'] : 'all';
        echo '<tr><th scope="row">显示页面</th>';
        echo '<td><select name="' . $this->option_name . '[announcement_display_on]" id="announcement_display_on">';
        $display_options = array(
            'all'        => '全站显示',
            'homepage'   => '仅首页',
            'pages'      => '指定页面',
            'posts'      => '指定文章',
            'categories' => '指定分类',
        );
        foreach ( $display_options as $k => $v ) {
            echo '<option value="' . esc_attr( $k ) . '"' . selected( $display_on, $k, false ) . '>' . esc_html( $v ) . '</option>';
        }
        echo '</select></td></tr>';
        
        // 指定页面ID
        $page_ids = isset( $options['announcement_page_ids'] ) ? $options['announcement_page_ids'] : '';
        echo '<tr class="ann-pages-row" style="' . ( $display_on !== 'pages' ? 'display:none;' : '' ) . '">';
        echo '<th scope="row">页面ID</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_page_ids]" value="' . esc_attr( $page_ids ) . '" class="regular-text" placeholder="多个ID用英文逗号分隔，如: 1,2,3" /></td></tr>';
        
        // 指定文章ID
        $post_ids = isset( $options['announcement_post_ids'] ) ? $options['announcement_post_ids'] : '';
        echo '<tr class="ann-posts-row" style="' . ( $display_on !== 'posts' ? 'display:none;' : '' ) . '">';
        echo '<th scope="row">文章ID</th>';
        echo '<td><input type="text" name="' . $this->option_name . '[announcement_post_ids]" value="' . esc_attr( $post_ids ) . '" class="regular-text" placeholder="多个ID用英文逗号分隔，如: 1,2,3" /></td></tr>';
        
        // 指定分类
        $cat_ids = isset( $options['announcement_category_ids'] ) && is_array( $options['announcement_category_ids'] ) ? $options['announcement_category_ids'] : array();
        $categories = get_categories( array( 'hide_empty' => false ) );
        echo '<tr class="ann-cats-row" style="' . ( $display_on !== 'categories' ? 'display:none;' : '' ) . '">';
        echo '<th scope="row">选择分类</th>';
        echo '<td>';
        foreach ( $categories as $cat ) {
            $checked = in_array( $cat->term_id, $cat_ids ) ? 'checked' : '';
            echo '<label style="display:inline-block;margin-right:15px;margin-bottom:5px;">';
            echo '<input type="checkbox" name="' . $this->option_name . '[announcement_category_ids][]" value="' . $cat->term_id . '" ' . $checked . ' /> ';
            echo esc_html( $cat->name );
            echo '</label>';
        }
        echo '</td></tr>';
        
        // 显示频率
        $frequency = isset( $options['announcement_frequency'] ) ? $options['announcement_frequency'] : 'always';
        echo '<tr><th scope="row">显示频率</th>';
        echo '<td><select name="' . $this->option_name . '[announcement_frequency]">';
        echo '<option value="always"' . selected( $frequency, 'always', false ) . '>每次访问都显示</option>';
        echo '<option value="once_day"' . selected( $frequency, 'once_day', false ) . '>每天只显示一次</option>';
        echo '</select></td></tr>';
        
        // 今日不再显示
        $allow_dismiss = isset( $options['announcement_allow_dismiss'] ) ? $options['announcement_allow_dismiss'] : '1';
        echo '<tr><th scope="row">“今日不再显示”选项</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[announcement_allow_dismiss]" value="" />';
        echo '<input type="checkbox" name="' . $this->option_name . '[announcement_allow_dismiss]" value="1"' . checked( $allow_dismiss, '1', false ) . ' /> ';
        echo '允许用户勾选“今日不再显示”（仅在“每次访问都显示”模式下有效）';
        echo '</label></td></tr>';
        
        // 显示/隐藏字段的JS
        echo '<script>
        jQuery(function($){
            $("#announcement_display_on").on("change", function(){
                var val = $(this).val();
                $(".ann-pages-row, .ann-posts-row, .ann-cats-row").hide();
                if(val === "pages") $(".ann-pages-row").show();
                if(val === "posts") $(".ann-posts-row").show();
                if(val === "categories") $(".ann-cats-row").show();
            });
        });
        </script>';
    }
    
    /**
     * 主题说明选项卡
     */
    private function render_documentation_tab() {
        global $wpdb;
        ?>
        <tr><td colspan="2" style="padding: 0;">
            <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                
                <!-- 数据表说明 -->
                <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        🗄️ 主题使用的数据表
                    </h2>
                    <p style="color: #64748b; margin: 0 0 16px;">以下是本主题创建的自定义数据表。如果卸载主题后不再使用这些功能，可以手动删除对应的数据表清理数据。</p>
                    
                    <table class="widefat striped" style="margin-bottom: 16px;">
                        <thead>
                            <tr>
                                <th>数据表名称</th>
                                <th>功能用途</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code><?php echo $wpdb->prefix; ?>developer_forms</code></td>
                                <td>表单管理 - 存储自定义表单配置</td>
                                <td><?php echo $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}developer_forms'") ? '<span style="color:#22c55e;">✓ 已创建</span>' : '<span style="color:#94a3b8;">未创建</span>'; ?></td>
                            </tr>
                            <tr>
                                <td><code><?php echo $wpdb->prefix; ?>developer_form_entries</code></td>
                                <td>表单提交记录 - 存储用户提交的表单数据</td>
                                <td><?php echo $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}developer_form_entries'") ? '<span style="color:#22c55e;">✓ 已创建</span>' : '<span style="color:#94a3b8;">未创建</span>'; ?></td>
                            </tr>
                            <tr>
                                <td><code><?php echo $wpdb->prefix; ?>developer_starter_messages</code></td>
                                <td>留言管理 - 存储联系页面的用户留言</td>
                                <td><?php echo $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}developer_starter_messages'") ? '<span style="color:#22c55e;">✓ 已创建</span>' : '<span style="color:#94a3b8;">未创建</span>'; ?></td>
                            </tr>
                            <tr>
                                <td><code><?php echo $wpdb->prefix; ?>ds_careers_positions</code></td>
                                <td>招聘职位 - 存储招聘岗位信息</td>
                                <td><?php echo $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ds_careers_positions'") ? '<span style="color:#22c55e;">✓ 已创建</span>' : '<span style="color:#94a3b8;">未创建</span>'; ?></td>
                            </tr>
                            <tr>
                                <td><code><?php echo $wpdb->prefix; ?>ds_careers_applications</code></td>
                                <td>简历投递 - 存储应聘者投递的简历</td>
                                <td><?php echo $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ds_careers_applications'") ? '<span style="color:#22c55e;">✓ 已创建</span>' : '<span style="color:#94a3b8;">未创建</span>'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style="background: #fef3c7; border-radius: 8px; padding: 12px 16px; display: flex; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 1.2rem;">⚠️</span>
                        <div style="font-size: 0.9rem; color: #92400e;">
                            <strong>注意：</strong>删除数据表将永久丢失所有相关数据，请谨慎操作。建议先导出备份后再删除。
                        </div>
                    </div>
                </div>
                
                <!-- 开发者钩子 -->
                <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        🔧 开发者钩子 (Hooks)
                    </h2>
                    <p style="color: #64748b; margin: 0 0 16px;">如需基于本主题进行二次开发，可使用以下钩子和函数。</p>
                    
                    <h4 style="margin: 20px 0 10px; color: #334155;">主要函数</h4>
                    <table class="widefat striped">
                        <thead><tr><th>函数名</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>developer_starter_get_option( $key, $default )</code></td><td>获取主题设置选项值</td></tr>
                            <tr><td><code>developer_starter_render_page_modules()</code></td><td>渲染当前页面的模块</td></tr>
                            <tr><td><code>developer_starter_render_form( $form_id )</code></td><td>渲染指定ID的表单</td></tr>
                            <tr><td><code>developer_starter_mask_username( $name )</code></td><td>用户名脱敏处理</td></tr>
                        </tbody>
                    </table>
                    
                    <h4 style="margin: 20px 0 10px; color: #334155;">过滤器 (Filter Hooks)</h4>
                    <table class="widefat striped">
                        <thead><tr><th>钩子名</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>developer_starter_modules</code></td><td>扩展自定义模块类型</td></tr>
                            <tr><td><code>developer_starter_banner_html</code></td><td>修改Banner模块输出</td></tr>
                            <tr><td><code>get_comment_author</code></td><td>过滤评论作者名（用于隐私脱敏）</td></tr>
                        </tbody>
                    </table>
                    
                    <h4 style="margin: 20px 0 10px; color: #334155;">动作钩子 (Action Hooks)</h4>
                    <table class="widefat striped">
                        <thead><tr><th>钩子名</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>developer_starter_before_header</code></td><td>在页头之前输出内容</td></tr>
                            <tr><td><code>developer_starter_after_footer</code></td><td>在页脚之后输出内容</td></tr>
                            <tr><td><code>developer_starter_form_submitted</code></td><td>表单提交成功后触发，参数: $form_id, $entry_data</td></tr>
                        </tbody>
                    </table>
                    
                    <h4 style="margin: 20px 0 10px; color: #334155;">Post Meta 键名</h4>
                    <table class="widefat striped">
                        <thead><tr><th>键名</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>_developer_starter_modules</code></td><td>页面模块配置数据（数组）</td></tr>
                            <tr><td><code>_seo_title</code></td><td>自定义SEO标题</td></tr>
                            <tr><td><code>_seo_description</code></td><td>自定义SEO描述</td></tr>
                            <tr><td><code>_seo_keywords</code></td><td>自定义SEO关键词</td></tr>
                            <tr><td><code>post_views</code></td><td>文章浏览量</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- 作者信息 -->
                <div style="padding: 24px;">
                    <h2 style="margin: 0 0 16px; font-size: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        👨‍💻 关于作者
                    </h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; padding: 20px;">
                            <h4 style="margin: 0 0 12px; color: #0369a1;">主题信息</h4>
                            <p style="margin: 0; color: #334155; line-height: 1.8;">
                                <strong>主题名称:</strong> Qi Ling (企灵)<br>
                                <strong>版本号:</strong> <?php echo DEVELOPER_STARTER_VERSION; ?><br>
                                <strong>适用于:</strong> WordPress 6.0+
                            </p>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-radius: 12px; padding: 20px;">
                            <h4 style="margin: 0 0 12px; color: #7c3aed;">联系方式</h4>
                            <p style="margin: 0; color: #334155; line-height: 1.8;">
                                <strong>技术支持:</strong> iticu@qq.com<br>
                                <strong>官方网站:</strong> <a href="https://www.wujiit.com" target="_blank">www.wujiit.com</a><br>
                                <strong>联系说明:</strong> 主题是开源免费的，不提供任何免费服务
                            </p>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; padding: 20px;">
                            <h4 style="margin: 0 0 12px; color: #059669;">反馈与贡献</h4>
                            <p style="margin: 0; color: #334155; line-height: 1.8;">
                                如有问题反馈或功能建议，<br>
                                欢迎通过上述方式联系我们。<br>
                                QQ群：16966111
                            </p>
                        </div>
                    </div>
                </div>
                
            </div>
        </td></tr>
        <?php
    }
}

