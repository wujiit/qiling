<?php
/**
 * 内容可见性限制功能
 * 
 * 提供登录可见、回复可见功能
 * 支持短代码和文章整体设置
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Developer_Starter_Content_Restriction {

    /**
     * 初始化
     */
    public function __construct() {
        // 短代码
        add_shortcode( 'login_to_view', array( $this, 'login_to_view_shortcode' ) );
        add_shortcode( 'reply_to_view', array( $this, 'reply_to_view_shortcode' ) );
        
        // 文章整体限制过滤器
        add_filter( 'the_content', array( $this, 'filter_post_content' ), 99 );
        
        // 编辑器元数据框
        add_action( 'add_meta_boxes', array( $this, 'add_restriction_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_restriction_meta' ) );
        
        // TinyMCE 按钮
        add_action( 'admin_init', array( $this, 'add_tinymce_buttons' ) );
        
        // 输出样式
        add_action( 'wp_head', array( $this, 'output_styles' ) );
    }

    /**
     * 登录可见短代码
     */
    public function login_to_view_shortcode( $atts, $content = null ) {
        if ( is_user_logged_in() ) {
            return do_shortcode( $content );
        }
        
        $login_url = wp_login_url( get_permalink() );
        $login_page_id = developer_starter_get_option( 'login_page_id', '' );
        if ( $login_page_id ) {
            $login_url = add_query_arg( 'redirect_to', get_permalink(), get_permalink( $login_page_id ) );
        }
        
        // 检查是否启用了顶部登录弹窗
        $header_login_enable = developer_starter_get_option( 'header_login_enable', '' );
        
        $html = '<div class="content-restriction-box login-required">';
        $html .= '<div class="restriction-icon">🔒</div>';
        $html .= '<div class="restriction-title">' . __( '登录后可见', 'developer-starter' ) . '</div>';
        $html .= '<div class="restriction-desc">' . __( '此部分内容需要登录后才能查看', 'developer-starter' ) . '</div>';
        
        if ( $header_login_enable ) {
            $html .= '<button type="button" class="restriction-btn trigger-login-modal">' . __( '立即登录', 'developer-starter' ) . '</button>';
        } else {
            $html .= '<a href="' . esc_url( $login_url ) . '" class="restriction-btn">' . __( '立即登录', 'developer-starter' ) . '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * 回复可见短代码
     */
    public function reply_to_view_shortcode( $atts, $content = null ) {
        global $post;
        
        if ( ! $post ) {
            return $content;
        }

        // 站点关闭评论时，自动放开“回复可见”限制，避免前台出现无效评论入口。
        if ( function_exists( 'developer_starter_comments_feature_enabled' ) && ! developer_starter_comments_feature_enabled() ) {
            return do_shortcode( $content );
        }
        
        // 检查用户是否已回复
        if ( $this->user_has_commented( $post->ID ) ) {
            return do_shortcode( $content );
        }
        
        $html = '<div class="content-restriction-box reply-required">';
        $html .= '<div class="restriction-icon">💬</div>';
        $html .= '<div class="restriction-title">' . __( '回复后可见', 'developer-starter' ) . '</div>';
        $html .= '<div class="restriction-desc">' . __( '请在下方评论区留言后查看隐藏内容', 'developer-starter' ) . '</div>';
        if ( ! is_user_logged_in() ) {
            $html .= '<div class="restriction-notice" style="font-size: 12px; opacity: 0.7; margin-bottom: 10px;">' . __( '(游客留言依赖浏览器缓存，清理缓存后需重新评论)', 'developer-starter' ) . '</div>';
        }
        $html .= '<a href="#respond" class="restriction-btn">' . __( '前往评论', 'developer-starter' ) . '</a>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * 检查用户是否已在文章下评论
     */
    private function user_has_commented( $post_id ) {
        // 管理员始终可见
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }
        
        // 文章作者始终可见
        $post = get_post( $post_id );
        if ( $post && is_user_logged_in() && get_current_user_id() == $post->post_author ) {
            return true;
        }
        
        // 已登录用户检查评论
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $comments = get_comments( array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'status'  => 'approve',
                'count'   => true,
            ) );
            return $comments > 0;
        }
        
        // 未登录用户通过邮箱检查（Cookie）
        $commenter = wp_get_current_commenter();
        if ( ! empty( $commenter['comment_author_email'] ) ) {
            $comments = get_comments( array(
                'post_id'      => $post_id,
                'author_email' => $commenter['comment_author_email'],
                'status'       => 'approve',
                'count'        => true,
            ) );
            return $comments > 0;
        }
        
        return false;
    }

    /**
     * 过滤文章整体内容（全文登录/回复可见）
     */
    public function filter_post_content( $content ) {
        // 只处理单篇文章页面，并且不在管理后台和feed中
        if ( ! is_singular( 'post' ) || is_admin() || is_feed() ) {
            return $content;
        }
        
        global $post;
        if ( ! $post ) {
            return $content;
        }
        
        // 防止在循环外部被调用
        if ( ! in_the_loop() ) {
            return $content;
        }
        
        // 管理员和作者始终可见
        if ( current_user_can( 'manage_options' ) ) {
            return $content;
        }
        if ( is_user_logged_in() && get_current_user_id() == $post->post_author ) {
            return $content;
        }
        
        $login_required = get_post_meta( $post->ID, '_content_login_required', true );
        $reply_required = get_post_meta( $post->ID, '_content_reply_required', true );
        
        // 全文登录可见 - 必须登录才能看全部内容
        if ( $login_required === '1' && ! is_user_logged_in() ) {
            $login_url = wp_login_url( get_permalink() );
            $login_page_id = developer_starter_get_option( 'login_page_id', '' );
            if ( $login_page_id ) {
                $login_url = add_query_arg( 'redirect_to', get_permalink(), get_permalink( $login_page_id ) );
            }
            
            $header_login_enable = developer_starter_get_option( 'header_login_enable', '' );
            
            $html = '<div class="content-restriction-box login-required full-post">';
            $html .= '<div class="restriction-icon">🔒</div>';
            $html .= '<div class="restriction-title">' . __( '登录后阅读全文', 'developer-starter' ) . '</div>';
            $html .= '<div class="restriction-desc">' . __( '请登录后查看完整文章内容', 'developer-starter' ) . '</div>';
            
            if ( $header_login_enable ) {
                $html .= '<button type="button" class="restriction-btn trigger-login-modal">' . __( '立即登录', 'developer-starter' ) . '</button>';
            } else {
                $html .= '<a href="' . esc_url( $login_url ) . '" class="restriction-btn">' . __( '立即登录', 'developer-starter' ) . '</a>';
            }
            
            $html .= '</div>';
            
            // 完全替换内容，不显示任何正文
            return $html;
        }
        
        // 全文回复可见 - 必须回复才能看全部内容
        if ( $reply_required === '1' && ! $this->user_has_commented( $post->ID ) ) {
            if ( function_exists( 'developer_starter_comments_feature_enabled' ) && ! developer_starter_comments_feature_enabled() ) {
                return $content;
            }

            $html = '<div class="content-restriction-box reply-required full-post">';
            $html .= '<div class="restriction-icon">💬</div>';
            $html .= '<div class="restriction-title">' . __( '回复后阅读全文', 'developer-starter' ) . '</div>';
            $html .= '<div class="restriction-desc">' . __( '请在评论区留言后查看完整文章内容', 'developer-starter' ) . '</div>';
            if ( ! is_user_logged_in() ) {
                $html .= '<div class="restriction-notice" style="font-size: 12px; opacity: 0.7; margin-bottom: 10px;">' . __( '(游客留言依赖浏览器缓存，清理缓存后需重新评论)', 'developer-starter' ) . '</div>';
            }
            $html .= '<a href="#respond" class="restriction-btn">' . __( '前往评论', 'developer-starter' ) . '</a>';
            $html .= '</div>';
            
            // 完全替换内容，不显示任何正文
            return $html;
        }
        
        return $content;
    }

    /**
     * 添加元数据框
     */
    public function add_restriction_meta_box() {
        add_meta_box(
            'content_restriction_settings',
            __( '内容可见性', 'developer-starter' ),
            array( $this, 'render_restriction_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    /**
     * 渲染元数据框
     */
    public function render_restriction_meta_box( $post ) {
        wp_nonce_field( 'content_restriction_nonce', 'restriction_nonce' );
        
        $login_required = get_post_meta( $post->ID, '_content_login_required', true );
        $reply_required = get_post_meta( $post->ID, '_content_reply_required', true );
        ?>
        <style>
            .restriction-option { 
                display: flex; 
                align-items: center; 
                justify-content: space-between;
                padding: 10px 0; 
                border-bottom: 1px solid #eee;
            }
            .restriction-option:last-child { border-bottom: none; }
            .restriction-label { font-size: 13px; color: #1e1e1e; }
            .restriction-toggle {
                position: relative;
                width: 36px;
                height: 20px;
            }
            .restriction-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .restriction-toggle .slider {
                position: absolute;
                cursor: pointer;
                top: 0; left: 0; right: 0; bottom: 0;
                background-color: #ccc;
                transition: .3s;
                border-radius: 20px;
            }
            .restriction-toggle .slider:before {
                position: absolute;
                content: "";
                height: 14px;
                width: 14px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .3s;
                border-radius: 50%;
            }
            .restriction-toggle input:checked + .slider {
                background-color: #2271b1;
            }
            .restriction-toggle input:checked + .slider:before {
                transform: translateX(16px);
            }
            .restriction-desc-text {
                font-size: 11px;
                color: #757575;
                margin-top: 6px;
            }
        </style>
        
        <div class="restriction-option">
            <div>
                <div class="restriction-label"><?php esc_html_e( '🔒 全文登录可见', 'developer-starter' ); ?></div>
            </div>
            <label class="restriction-toggle">
                <input type="checkbox" name="content_login_required" value="1" <?php checked( $login_required, '1' ); ?> />
                <span class="slider"></span>
            </label>
        </div>
        
        <div class="restriction-option">
            <div>
                <div class="restriction-label"><?php esc_html_e( '💬 全文回复可见', 'developer-starter' ); ?></div>
            </div>
            <label class="restriction-toggle">
                <input type="checkbox" name="content_reply_required" value="1" <?php checked( $reply_required, '1' ); ?> />
                <span class="slider"></span>
            </label>
        </div>
        
        <div class="restriction-desc-text">
            <?php esc_html_e( '开启后整篇文章需要登录或回复才能阅读全文。', 'developer-starter' ); ?><br>
            <?php esc_html_e( '如需部分内容隐藏，请使用编辑器工具栏的快捷按钮插入短代码。', 'developer-starter' ); ?>
        </div>
        <?php
    }

    /**
     * 保存元数据
     */
    public function save_restriction_meta( $post_id ) {
        $nonce = isset( $_POST['restriction_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['restriction_nonce'] ) ) : '';
        if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'content_restriction_nonce' ) ) {
            return;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // 保存登录可见设置
        $login_required = isset( $_POST['content_login_required'] ) ? '1' : '';
        update_post_meta( $post_id, '_content_login_required', $login_required );
        
        // 保存回复可见设置
        $reply_required = isset( $_POST['content_reply_required'] ) ? '1' : '';
        update_post_meta( $post_id, '_content_reply_required', $reply_required );
    }

    /**
     * 添加TinyMCE按钮
     */
    public function add_tinymce_buttons() {
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        }
        
        if ( get_user_option( 'rich_editing' ) !== 'true' ) {
            return;
        }

        add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
        add_filter( 'mce_buttons', array( $this, 'register_tinymce_buttons' ) );
        add_action( 'admin_head', array( $this, 'print_tinymce_i18n' ) );
    }

    /**
     * 注册TinyMCE插件脚本
     */
    public function add_tinymce_plugin( $plugins ) {
        $plugins['content_restriction'] = get_template_directory_uri() . '/assets/js/admin/content-restriction-editor.js';
        return $plugins;
    }

    /**
     * 注册TinyMCE按钮
     */
    public function register_tinymce_buttons( $buttons ) {
        array_push( $buttons, 'login_to_view_btn', 'reply_to_view_btn' );
        return $buttons;
    }

    /**
     * 为 TinyMCE 内容可见性按钮输出本地化文案。
     *
     * @return void
     */
    public function print_tinymce_i18n() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <script>
        window.qilingContentRestrictionI18n = <?php echo wp_json_encode(
            array(
                'loginVisible'      => __( '登录可见', 'developer-starter' ),
                'replyVisible'      => __( '回复可见', 'developer-starter' ),
                'loginVisibleText'  => __( '此处内容登录后可见', 'developer-starter' ),
                'replyVisibleText'  => __( '此处内容回复后可见', 'developer-starter' ),
            )
        ); ?>;
        </script>
        <?php
    }

    /**
     * 输出前端样式
     */
    public function output_styles() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }
        ?>
        <style>
        /* 内容可见性限制提示框 */
        .content-restriction-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            margin: 30px 0;
        }
        .content-restriction-box.full-post {
            margin-top: 0;
        }
        .restriction-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .restriction-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .restriction-desc {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }
        .restriction-btn {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, #0ea5e9 0%, #10b981 100%);
            color: #fff !important;
            text-decoration: none;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }
        .restriction-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
        }
        .post-excerpt-preview {
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        /* 暗黑模式适配 */
        html.dark-mode .content-restriction-box {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-color: #334155;
        }
        html.dark-mode .restriction-title {
            color: #e2e8f0;
        }
        html.dark-mode .restriction-desc {
            color: #94a3b8;
        }
        html.dark-mode .post-excerpt-preview {
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 弹窗登录按钮触发
            var triggerBtns = document.querySelectorAll('.trigger-login-modal');
            var loginBtn = document.getElementById('header-login-toggle');
            
            triggerBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (loginBtn) {
                        loginBtn.click();
                    } else {
                        // 如果没有顶部登录按钮，跳转到登录页
                        window.location.href = '<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>';
                    }
                });
            });
        });
        </script>
        <?php
    }
}

// 初始化
new Developer_Starter_Content_Restriction();
