<?php
/**
 * 公告管理器类
 *
 * 多功能公告系统，支持多种公告类型、条件显示、频率控制
 *
 * @package Developer_Starter
 * @since 1.0.2
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 公告管理器类
 */
class Announcement_Manager {

    /**
     * 构造函数
     */
    public function __construct() {
        // 前端加载公告
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_announcement' ) );
        
        // AJAX 处理
        add_action( 'wp_ajax_dismiss_announcement', array( $this, 'ajax_dismiss' ) );
        add_action( 'wp_ajax_nopriv_dismiss_announcement', array( $this, 'ajax_dismiss' ) );
    }

    /**
     * 检查是否应该在当前页面显示公告
     *
     * @return bool
     */
    public function should_display() {
        // 检查公告是否启用
        $enabled = developer_starter_get_option( 'announcement_enable', '' );
        if ( ! $enabled ) {
            return false;
        }
        
        // 检查公告内容是否为空
        $content = developer_starter_get_option( 'announcement_content', '' );
        $title = developer_starter_get_option( 'announcement_title', '' );
        if ( empty( $content ) && empty( $title ) ) {
            return false;
        }
        
        // 检查显示页面
        if ( ! $this->check_display_pages() ) {
            return false;
        }
        
        return true;
    }

    /**
     * 检查是否在允许显示的页面
     *
     * @return bool
     */
    private function check_display_pages() {
        $display_on = developer_starter_get_option( 'announcement_display_on', 'all' );
        
        switch ( $display_on ) {
            case 'all':
                return true;
                
            case 'homepage':
                return is_front_page();
                
            case 'pages':
                if ( ! is_page() ) {
                    return false;
                }
                $page_ids = developer_starter_get_option( 'announcement_page_ids', '' );
                if ( empty( $page_ids ) ) {
                    return false;
                }
                $ids = array_map( 'intval', array_filter( explode( ',', $page_ids ) ) );
                return in_array( get_the_ID(), $ids );
                
            case 'posts':
                if ( ! is_single() ) {
                    return false;
                }
                $post_ids = developer_starter_get_option( 'announcement_post_ids', '' );
                if ( empty( $post_ids ) ) {
                    return false;
                }
                $ids = array_map( 'intval', array_filter( explode( ',', $post_ids ) ) );
                return in_array( get_the_ID(), $ids );
                
            case 'categories':
                if ( ! is_category() && ! ( is_single() && has_category() ) ) {
                    return false;
                }
                $cat_ids = developer_starter_get_option( 'announcement_category_ids', array() );
                if ( empty( $cat_ids ) || ! is_array( $cat_ids ) ) {
                    return false;
                }
                if ( is_category() ) {
                    return in_array( get_queried_object_id(), $cat_ids );
                }
                // 文章页检查是否属于指定分类
                $post_cats = wp_get_post_categories( get_the_ID() );
                return ! empty( array_intersect( $post_cats, $cat_ids ) );
                
            default:
                return true;
        }
    }

    /**
     * 公告资源由页脚懒加载器按需注入，避免首屏立即请求。
     */
    public function maybe_enqueue_assets() {
        if ( ! $this->should_display() ) {
            return;
        }
    }

    /**
     * 渲染公告 HTML
     */
    public function render_announcement() {
        if ( ! $this->should_display() ) {
            return;
        }
        
        $type = developer_starter_get_option( 'announcement_type', 'normal' );
        $title = developer_starter_get_option( 'announcement_title', '' );
        $content = developer_starter_get_option( 'announcement_content', '' );
        $image = developer_starter_get_media_url( developer_starter_get_option( 'announcement_image', '' ) );
        $btn_text = developer_starter_get_option( 'announcement_btn_text', '' );
        $btn_url = developer_starter_get_option( 'announcement_btn_url', '' );
        $allow_dismiss = developer_starter_get_option( 'announcement_allow_dismiss', '1' );
        $frequency = developer_starter_get_option( 'announcement_frequency', 'always' );
        
        // 公告唯一ID
        $announcement_id = developer_starter_get_option( 'announcement_id', '' );
        if ( empty( $announcement_id ) ) {
            $announcement_id = 'ann_' . md5( $title . $content );
        }
        
        $type_class = 'announcement-' . esc_attr( $type );
        
        // 底部横幅类型使用不同的结构
        if ( $type === 'bottom_banner' ) {
            $this->render_bottom_banner( $announcement_id, $title, $content, $image, $btn_url, $allow_dismiss );
            $this->output_lazy_loader_script( $announcement_id, $frequency, $allow_dismiss );
            return;
        }
        ?>
        <div id="ds-announcement" class="ds-announcement <?php echo $type_class; ?>" data-id="<?php echo esc_attr( $announcement_id ); ?>" style="display: none;">
            <div class="announcement-overlay"></div>
            <div class="announcement-modal">
                <button type="button" class="announcement-close" aria-label="<?php esc_attr_e( '关闭', 'developer-starter' ); ?>">&times;</button>
                
                <?php if ( $type === 'image' && $image ) : ?>
                    <div class="announcement-image">
                        <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                    </div>
                <?php endif; ?>
                
                <div class="announcement-body">
                    <?php if ( $type === 'marketing' ) : ?>
                        <div class="announcement-badge"><?php esc_html_e( '限时活动', 'developer-starter' ); ?></div>
                    <?php endif; ?>
                    
                    <?php if ( $title ) : ?>
                        <h3 class="announcement-title"><?php echo esc_html( $title ); ?></h3>
                    <?php endif; ?>
                    
                    <?php if ( $type === 'image_text' && $image ) : ?>
                        <div class="announcement-inline-image">
                            <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $content ) : ?>
                        <div class="announcement-content">
                            <?php echo wp_kses_post( wpautop( $content ) ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $btn_text && $btn_url ) : ?>
                        <div class="announcement-action">
                            <a href="<?php echo esc_url( $btn_url ); ?>" class="announcement-btn">
                                <?php echo esc_html( $btn_text ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ( $allow_dismiss === '1' && $frequency === 'always' ) : ?>
                    <div class="announcement-dismiss">
                        <label>
                            <input type="checkbox" id="announcement-today-dismiss" />
                            <span><?php esc_html_e( '今日不再显示', 'developer-starter' ); ?></span>
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        // 输出动态样式
        $this->output_custom_styles( $type );
        $this->output_lazy_loader_script( $announcement_id, $frequency, $allow_dismiss );
    }
    
    /**
     * 渲染底部横幅公告
     */
    private function render_bottom_banner( $announcement_id, $title, $content, $image, $btn_url, $allow_dismiss ) {
        $is_clickable = ! empty( $btn_url );
        $tag = $is_clickable ? 'a' : 'div';
        $attrs = $is_clickable ? 'href="' . esc_url( $btn_url ) . '" target="_blank" rel="noopener"' : '';
        ?>
        <div id="ds-bottom-banner" class="ds-bottom-banner" data-id="<?php echo esc_attr( $announcement_id ); ?>" style="display: none;">
            <<?php echo $tag; ?> class="ds-bottom-banner-inner" <?php echo $attrs; ?>>
                <?php if ( $image ) : ?>
                    <div class="ds-bottom-banner-image">
                        <img src="<?php echo esc_url( $image ); ?>" alt="" />
                    </div>
                <?php endif; ?>
                
                <div class="ds-bottom-banner-content">
                    <?php if ( $content ) : ?>
                        <span class="ds-bottom-banner-text"><?php echo wp_kses_post( wp_strip_all_tags( $content ) ); ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ( $is_clickable ) : ?>
                    <span class="ds-bottom-banner-arrow">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                <?php endif; ?>
            </<?php echo $tag; ?>>
            
            <?php if ( $allow_dismiss === '1' ) : ?>
                <button type="button" class="ds-bottom-banner-close" aria-label="<?php esc_attr_e( '关闭', 'developer-starter' ); ?>">&times;</button>
            <?php endif; ?>
        </div>
        
        <style>
        /* 底部横幅样式 - 完全独立 */
        .ds-bottom-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: linear-gradient(135deg, #0ea5e9 0%, #10b981 100%);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
            animation: dsBottomBannerSlideUp 0.4s ease;
        }
        
        @keyframes dsBottomBannerSlideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        
        .ds-bottom-banner-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 16px 60px 16px 24px;
            min-height: 70px;
            color: #fff;
            text-decoration: none;
            cursor: <?php echo $is_clickable ? 'pointer' : 'default'; ?>;
            transition: background 0.3s;
        }
        
        a.ds-bottom-banner-inner:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #fff;
        }
        
        .ds-bottom-banner-image {
            flex-shrink: 0;
        }
        
        .ds-bottom-banner-image img {
            max-height: 50px;
            width: auto;
            border-radius: 8px;
        }
        
        .ds-bottom-banner-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .ds-bottom-banner-text {
            font-size: 15px;
            color: #fff;
            font-weight: 500;
        }
        
        .ds-bottom-banner-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: #fff;
            transition: all 0.3s;
        }
        
        a.ds-bottom-banner-inner:hover .ds-bottom-banner-arrow {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(4px);
        }
        
        .ds-bottom-banner-close {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            color: #fff;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .ds-bottom-banner-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* 深色模式 */
        html.dark-mode .ds-bottom-banner,
        body.dark-mode .ds-bottom-banner {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
        }
        
        /* 响应式 */
        @media (max-width: 768px) {
            .ds-bottom-banner-inner {
                flex-wrap: wrap;
                padding: 12px 50px 12px 16px;
                min-height: 60px;
                gap: 12px;
            }
            
            .ds-bottom-banner-image img {
                max-height: 36px;
            }
            
            .ds-bottom-banner-content {
                gap: 8px;
            }
            
            .ds-bottom-banner-text {
                font-size: 14px;
            }
            
            .ds-bottom-banner-close {
                right: 12px;
                width: 24px;
                height: 24px;
                font-size: 18px;
            }
        }
        </style>
        <?php
    }

    /**
     * AJAX 处理关闭公告
     */
    public function ajax_dismiss() {
        check_ajax_referer( 'announcement_nonce', 'nonce' );
        
        // 这里可以记录用户关闭行为，目前使用 cookie 在前端处理
        wp_send_json_success();
    }
    
    /**
     * 输出自定义样式
     * 
     * @param string $type 公告类型
     */
    private function output_custom_styles( $type ) {
        $styles = array();
        
        // 普通/图片/图文公告按钮样式
        if ( in_array( $type, array( 'normal', 'image', 'image_text' ) ) ) {
            $normal_btn_bg = developer_starter_get_option( 'announcement_normal_btn_bg', '' );
            $normal_btn_color = developer_starter_get_option( 'announcement_normal_btn_color', '' );
            $normal_btn_hover_bg = developer_starter_get_option( 'announcement_normal_btn_hover_bg', '' );
            
            if ( ! empty( $normal_btn_bg ) ) {
                $styles[] = '.announcement-normal .announcement-btn, .announcement-image .announcement-btn, .announcement-image_text .announcement-btn { background: ' . esc_attr( $normal_btn_bg ) . '; }';
            }
            
            if ( ! empty( $normal_btn_color ) ) {
                $styles[] = '.announcement-normal .announcement-btn, .announcement-image .announcement-btn, .announcement-image_text .announcement-btn { color: ' . esc_attr( $normal_btn_color ) . '; }';
            }
            
            if ( ! empty( $normal_btn_hover_bg ) ) {
                $styles[] = '.announcement-normal .announcement-btn:hover, .announcement-image .announcement-btn:hover, .announcement-image_text .announcement-btn:hover { background: ' . esc_attr( $normal_btn_hover_bg ) . '; }';
            }
        }
        
        // 营销活动公告样式
        if ( $type === 'marketing' ) {
            $marketing_modal_bg = developer_starter_get_option( 'announcement_marketing_modal_bg', '' );
            $marketing_btn_bg = developer_starter_get_option( 'announcement_marketing_btn_bg', '' );
            $marketing_btn_color = developer_starter_get_option( 'announcement_marketing_btn_color', '' );
            $marketing_btn_hover_bg = developer_starter_get_option( 'announcement_marketing_btn_hover_bg', '' );
            
            if ( ! empty( $marketing_modal_bg ) ) {
                $styles[] = '.announcement-marketing { --qiling-announcement-marketing-bg: ' . esc_attr( $marketing_modal_bg ) . '; }';
            }
            
            if ( ! empty( $marketing_btn_bg ) ) {
                $styles[] = '.announcement-marketing .announcement-btn { background: ' . esc_attr( $marketing_btn_bg ) . '; }';
            }
            
            if ( ! empty( $marketing_btn_color ) ) {
                $styles[] = '.announcement-marketing { --qiling-announcement-marketing-button-text: ' . esc_attr( $marketing_btn_color ) . '; }';
            }
            
            if ( ! empty( $marketing_btn_hover_bg ) ) {
                $styles[] = '.announcement-marketing .announcement-btn:hover { background: ' . esc_attr( $marketing_btn_hover_bg ) . '; }';
            }
        }
        
        // 输出样式
        if ( ! empty( $styles ) ) {
            echo '<style id="announcement-custom-styles">' . implode( ' ', $styles ) . '</style>';
        }
    }

    /**
     * 懒加载公告资源（CSS/JS），不阻塞首屏。
     *
     * 注意：
     * 1) 保留 announcement.js 原有逻辑，尤其“今日不再显示”与频率控制。
     * 2) 在加载前先做一次 cookie 判断，已当天关闭时直接跳过资源请求。
     */
    private function output_lazy_loader_script( $announcement_id, $frequency, $allow_dismiss ) {
        $config = array(
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( 'announcement_nonce' ),
            'frequency'      => (string) $frequency,
            'allowDismiss'   => $allow_dismiss === '1',
            'announcementId' => (string) $announcement_id,
        );

        $version = defined( 'DEVELOPER_STARTER_VERSION' ) ? (string) DEVELOPER_STARTER_VERSION : '1.0.0';
        $css_file = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/announcement.css';
        $js_file  = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/js/announcement.js';
        $css_mtime = file_exists( $css_file ) ? (int) filemtime( $css_file ) : 0;
        $js_mtime  = file_exists( $js_file ) ? (int) filemtime( $js_file ) : 0;
        $asset_mtime = max( $css_mtime, $js_mtime );
        if ( $asset_mtime > 0 ) {
            $version = (string) $asset_mtime;
        } elseif ( function_exists( 'developer_starter_get_assets_version' ) ) {
            $dynamic_version = (string) developer_starter_get_assets_version();
            if ( $dynamic_version !== '' ) {
                $version = $dynamic_version;
            }
        }

        $css_url = add_query_arg( 'ver', rawurlencode( $version ), DEVELOPER_STARTER_ASSETS . '/css/announcement.css' );
        $js_url  = add_query_arg( 'ver', rawurlencode( $version ), DEVELOPER_STARTER_ASSETS . '/js/announcement.js' );
        ?>
        <script id="ds-announcement-lazy-loader">
        (function () {
            var cfg = <?php echo wp_json_encode( $config ); ?>;
            window.dsAnnouncement = cfg;

            var root = document.getElementById('ds-announcement') || document.getElementById('ds-bottom-banner');
            if (!root) {
                return;
            }

            function getCookie(name) {
                var nameEQ = name + '=';
                var parts = document.cookie.split(';');
                for (var i = 0; i < parts.length; i++) {
                    var c = parts[i].trim();
                    if (c.indexOf(nameEQ) === 0) {
                        return decodeURIComponent(c.substring(nameEQ.length));
                    }
                }
                return null;
            }

            function shouldLoadAnnouncement() {
                var cookieName = 'ds_ann_' + (cfg.announcementId || 'default');
                var dismissed = getCookie(cookieName);
                if (!dismissed) {
                    return true;
                }

                var mode = cfg.frequency || 'always';
                if (mode === 'always' || mode === 'once_day') {
                    var today = new Date().toDateString();
                    return dismissed !== today;
                }

                return true;
            }

            if (!shouldLoadAnnouncement()) {
                return;
            }

            var cssUrl = <?php echo wp_json_encode( $css_url ); ?>;
            var jsUrl = <?php echo wp_json_encode( $js_url ); ?>;
            var styleId = 'developer-starter-announcement-lazy-style';
            var scriptId = 'developer-starter-announcement-lazy-script';
            var loaded = false;

            function loadScriptOnce() {
                if (loaded || document.getElementById(scriptId)) {
                    return;
                }
                loaded = true;
                var script = document.createElement('script');
                script.id = scriptId;
                script.src = jsUrl;
                script.defer = true;
                document.body.appendChild(script);
            }

            function loadAssets() {
                if (document.getElementById(styleId)) {
                    loadScriptOnce();
                    return;
                }

                var link = document.createElement('link');
                link.id = styleId;
                link.rel = 'stylesheet';
                link.href = cssUrl;

                var fallbackTimer = setTimeout(function () {
                    loadScriptOnce();
                }, 1200);

                link.onload = function () {
                    clearTimeout(fallbackTimer);
                    loadScriptOnce();
                };
                link.onerror = function () {
                    clearTimeout(fallbackTimer);
                    loadScriptOnce();
                };

                document.head.appendChild(link);
            }

            function scheduleLoad() {
                if ('requestIdleCallback' in window) {
                    requestIdleCallback(loadAssets, { timeout: 2000 });
                } else {
                    setTimeout(loadAssets, 700);
                }
            }

            if (document.readyState === 'complete') {
                scheduleLoad();
            } else {
                window.addEventListener('load', scheduleLoad, { once: true });
            }
        })();
        </script>
        <?php
    }
}
