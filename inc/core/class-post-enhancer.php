<?php
/**
 * Post Enhancer - 文章增强器
 * 
 * 处理代码高亮、TOC生成、浏览量统计、阅读时长等功能
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Post_Enhancer {

    /**
     * 浏览量缓冲使用的对象缓存组。
     */
    private const POST_VIEW_BUFFER_GROUP = 'developer_starter_post_views';

    /**
     * 浏览量缓冲计数的保留时长。
     */
    private const POST_VIEW_BUFFER_TTL = 15 * MINUTE_IN_SECONDS;

    /**
     * 浏览量批量回写延迟。
     */
    private const POST_VIEW_FLUSH_DELAY = 5 * MINUTE_IN_SECONDS;

    /**
     * 文章海报游客写入保护使用的对象缓存组。
     */
    private const POST_POSTER_GUARD_GROUP = 'developer_starter_post_poster_guard';

    /**
     * 单例实例
     */
    private static $instance = null;

    /**
     * 当前请求待上报的文章浏览信息。
     *
     * @var array<string, mixed>|null
     */
    private $pending_post_view = null;

    /**
     * 获取单例
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct() {
        // 代码高亮资源加载
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_prism_assets' ) );
        
        // 浏览量统计（仅准备异步上报数据，避免在主页面请求里同步写入 postmeta）
        add_action( 'template_redirect', array( $this, 'track_post_views' ), 20 );
        add_action( 'wp_footer', array( $this, 'output_post_view_tracker' ), 1 );
        add_action( 'wp_ajax_developer_starter_track_post_view', array( $this, 'ajax_track_post_view' ) );
        add_action( 'wp_ajax_nopriv_developer_starter_track_post_view', array( $this, 'ajax_track_post_view' ) );
        add_action( 'developer_starter_flush_post_view_buffer', array( $this, 'flush_post_view_buffer' ), 10, 1 );
        
        // 用户社交字段
        add_filter( 'user_contactmethods', array( $this, 'add_user_social_fields' ) );

        // 文章海报缓存（读取/下载公开；写入由登录状态、设置开关和限流保护）
        add_action( 'wp_ajax_ds_get_post_poster_cache', array( $this, 'ajax_get_post_poster_cache' ) );
        add_action( 'wp_ajax_nopriv_ds_get_post_poster_cache', array( $this, 'ajax_get_post_poster_cache' ) );
        add_action( 'wp_ajax_ds_save_post_poster_cache', array( $this, 'ajax_save_post_poster_cache' ) );
        add_action( 'wp_ajax_nopriv_ds_save_post_poster_cache', array( $this, 'ajax_save_post_poster_cache' ) );
        add_action( 'wp_ajax_ds_download_post_poster', array( $this, 'ajax_download_post_poster' ) );
        add_action( 'wp_ajax_nopriv_ds_download_post_poster', array( $this, 'ajax_download_post_poster' ) );
        
        // 文章增强样式和脚本
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_article_enhance_assets' ) );
        add_action( 'wp_body_open', array( $this, 'render_reading_progress_bar' ) );
        
        // 注册代码短代码
        add_shortcode( 'code', array( $this, 'code_shortcode' ) );
        
        // 在 wpautop 之前保护 [code] 短代码内容，防止插入 <br /> 和 <p> 标签
        add_filter( 'the_content', array( $this, 'protect_code_shortcode' ), 6 );
        
        // 在 wpautop(优先级10) 之后、do_shortcode(优先级11) 之前恢复 [code] 短代码内容
        add_filter( 'the_content', array( $this, 'restore_code_shortcode' ), 10, 1 );
        
        // 为代码块添加语言类
        add_filter( 'the_content', array( $this, 'enhance_code_blocks' ), 5 );
        
        // 为经典编辑器添加代码高亮快捷按钮
        add_action( 'admin_init', array( $this, 'add_tinymce_code_button' ) );
    }

    /**
     * 为经典编辑器添加 TinyMCE 按钮
     */
    public function add_tinymce_code_button() {
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        }

        if ( 'true' == get_user_option( 'rich_editing' ) ) {
            add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_code_plugin' ) );
            add_filter( 'mce_buttons', array( $this, 'register_tinymce_code_button' ) );
            add_action( 'admin_head', array( $this, 'print_tinymce_code_i18n' ) );
        }
    }

    /**
     * 注册 TinyMCE 代码插件脚本
     */
    public function register_tinymce_code_plugin( $plugins ) {
        $plugins['developer_starter_code'] = DEVELOPER_STARTER_ASSETS . '/js/admin/code-highlight-editor.js';
        return $plugins;
    }

    /**
     * 注册 TinyMCE 代码按钮
     */
    public function register_tinymce_code_button( $buttons ) {
        array_push( $buttons, '|', 'developer_starter_code' );
        return $buttons;
    }

    /**
     * 为 TinyMCE 代码按钮输出本地化文案。
     *
     * @return void
     */
    public function print_tinymce_code_i18n() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <script>
        window.qilingCodeEditorI18n = <?php echo wp_json_encode(
            array(
                'insertHighlightedCodeBlock' => __( '插入高亮代码块', 'developer-starter' ),
                'insertHighlightedCode'      => __( '插入高亮代码', 'developer-starter' ),
                'languageLabel'              => __( '语言', 'developer-starter' ),
                'codeLabel'                  => __( '代码内容', 'developer-starter' ),
                'plainTextLabel'             => __( '纯文本', 'developer-starter' ),
            )
        ); ?>;
        </script>
        <?php
    }

    /**
     * 加载文章增强样式和脚本
     */
    public function enqueue_article_enhance_assets() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }
        
        // 加载文章增强样式
        wp_enqueue_style(
            'developer-starter-article-enhance',
            DEVELOPER_STARTER_ASSETS . '/css/article-enhance.css',
            array( 'developer-starter-main' ),
            developer_starter_get_assets_version()
        );

        $article_dynamic_css = self::get_single_post_dynamic_css();
        if ( '' !== $article_dynamic_css ) {
            wp_add_inline_style( 'developer-starter-article-enhance', $article_dynamic_css );
        }

        if ( self::is_reading_progress_enabled() ) {
            wp_enqueue_style(
                'developer-starter-reading-progress',
                DEVELOPER_STARTER_ASSETS . '/css/reading-progress.css',
                array( 'developer-starter-main' ),
                developer_starter_get_assets_version()
            );

            wp_enqueue_script(
                'developer-starter-reading-progress',
                DEVELOPER_STARTER_ASSETS . '/js/reading-progress.js',
                array(),
                developer_starter_get_assets_version(),
                true
            );

            wp_localize_script( 'developer-starter-reading-progress', 'qilingReadingProgressConfig', array(
                'targetSelector' => '.single-post .entry-content',
                'offset'         => 80,
                'label'          => __( '阅读进度', 'developer-starter' ),
            ) );
        }
        
        // 如果启用了TOC，加载TOC脚本
        $toc_enable = developer_starter_get_option( 'toc_enable', '' );
        if ( $toc_enable ) {
            wp_enqueue_script(
                'developer-starter-article-enhance',
                DEVELOPER_STARTER_ASSETS . '/js/article-enhance.js',
                array(),
                developer_starter_get_assets_version(),
                true
            );
            
            wp_localize_script( 'developer-starter-article-enhance', 'articleEnhanceConfig', array(
                'tocEnable' => $toc_enable,
                'tocPosition' => developer_starter_get_option( 'toc_position', 'sidebar' ),
                'tocCollapsible' => developer_starter_get_option( 'toc_collapsible', '' ),
                'tocHeadingLevels' => developer_starter_get_option( 'toc_heading_levels', 'h2h3' ),
                'copyButtonLabel' => __( '复制代码', 'developer-starter' ),
                'tocExpandLabel' => __( '展开目录', 'developer-starter' ),
                'tocCollapseLabel' => __( '收起目录', 'developer-starter' ),
                'tocMobileOpenLabel' => __( '打开目录', 'developer-starter' ),
                'tocMobileCloseLabel' => __( '关闭目录', 'developer-starter' ),
                'tocMobileButtonText' => __( '目录', 'developer-starter' ),
            ) );
        }

        $post_speech_enabled = self::is_post_speech_enabled();
        $comment_speech_enabled = self::is_comment_speech_enabled();
        $has_comment_speech_targets = $comment_speech_enabled && (int) get_comments_number() > 0;
        if ( $post_speech_enabled || $has_comment_speech_targets ) {
            wp_enqueue_style(
                'developer-starter-post-speech',
                DEVELOPER_STARTER_ASSETS . '/css/post-speech.css',
                array( 'developer-starter-article-enhance' ),
                developer_starter_get_assets_version()
            );

            wp_enqueue_script(
                'developer-starter-post-speech',
                DEVELOPER_STARTER_ASSETS . '/js/post-speech.js',
                array(),
                developer_starter_get_assets_version(),
                true
            );

            wp_localize_script( 'developer-starter-post-speech', 'qilingPostSpeechConfig', array(
                'postEnabled'       => $post_speech_enabled,
                'commentEnabled'    => $comment_speech_enabled,
                'language'          => self::get_speech_language(),
                'voicePreference'   => self::get_speech_voice_preference(),
                'voiceName'         => self::get_speech_voice_name(),
                'voiceURI'          => self::get_speech_voice_uri(),
                'rate'              => self::get_speech_rate(),
                'pitch'             => self::get_speech_pitch(),
                'volume'            => self::get_speech_volume(),
                'pauseOnHidden'     => self::get_speech_pause_on_hidden(),
                'storageKey'        => 'qiling-post-speech-preferences',
                'selectors'         => array(
                    'article'       => '.single-post .entry-content',
                    'articleWidget' => '.qiling-post-speech',
                    'commentButton' => '.qiling-comment-speech-trigger',
                    'commentText'   => '.comment-text',
                ),
                'i18n'              => array(
                    'unsupported'       => __( '当前浏览器不支持语音朗读', 'developer-starter' ),
                    'emptyText'         => __( '暂无可朗读内容', 'developer-starter' ),
                    'ready'             => __( '准备朗读', 'developer-starter' ),
                    'playing'           => __( '正在朗读', 'developer-starter' ),
                    'paused'            => __( '已暂停', 'developer-starter' ),
                    'stopped'           => __( '已停止', 'developer-starter' ),
                    'finished'          => __( '朗读完成', 'developer-starter' ),
                    'error'             => __( '朗读失败，请稍后重试', 'developer-starter' ),
                    'articleLabel'      => __( '文章正文', 'developer-starter' ),
                    'commentLabel'      => __( '评论', 'developer-starter' ),
                    'commentIdleLabel'  => __( '朗读', 'developer-starter' ),
                    'commentActiveLabel' => __( '朗读中', 'developer-starter' ),
                    'miniTitle'         => __( '语音播放器', 'developer-starter' ),
                    'rateLabel'         => __( '语速', 'developer-starter' ),
                    'volumeLabel'       => __( '音量', 'developer-starter' ),
                    'progressLabel'     => __( '进度', 'developer-starter' ),
                    'pauseButton'       => __( '暂停', 'developer-starter' ),
                    'resumeButton'      => __( '继续', 'developer-starter' ),
                    'stopButton'        => __( '停止', 'developer-starter' ),
                ),
            ) );
        }

        $post_poster_enable = developer_starter_get_option( 'post_poster_enable', '' );
        if ( $post_poster_enable ) {
            $jquery_asset = $this->get_frontend_jquery_asset();

            wp_enqueue_script(
                'developer-starter-post-poster',
                DEVELOPER_STARTER_ASSETS . '/js/post-poster.js',
                array(),
                developer_starter_get_assets_version(),
                true
            );

            wp_localize_script( 'developer-starter-post-poster', 'dsPostPosterConfig', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'canSaveCache' => is_user_logged_in() || $this->is_post_poster_guest_cache_enabled(),
                'actions' => array(
                    'getCache' => 'ds_get_post_poster_cache',
                    'saveCache' => 'ds_save_post_poster_cache',
                    'download' => 'ds_download_post_poster',
                ),
                'tips'  => array(
                    'loading'  => __( '正在生成海报...', 'developer-starter' ),
                    'cached'   => __( '已加载缓存海报', 'developer-starter' ),
                    'ready'    => __( '海报已生成，可下载保存', 'developer-starter' ),
                    'saved'    => __( '海报已缓存，下次可直接复用', 'developer-starter' ),
                    'fallback' => __( '海报已生成。若无法下载，请长按或截图保存。', 'developer-starter' ),
                    'error'    => __( '海报生成失败，请稍后重试', 'developer-starter' ),
                ),
                'labels' => array(
                    'scanToRead' => __( '扫码阅读全文', 'developer-starter' ),
                ),
                'assets' => array(
                    'jquery' => add_query_arg(
                        'ver',
                        rawurlencode( (string) $jquery_asset['ver'] ),
                        $jquery_asset['url']
                    ),
                    'qrcode' => add_query_arg(
                        'ver',
                        rawurlencode( (string) developer_starter_get_assets_version() ),
                        DEVELOPER_STARTER_ASSETS . '/js/vendor/jquery-qrcode.min.js'
                    ),
                ),
            ) );
        }

        $image_zoom_enable = developer_starter_get_option( 'post_image_zoom_enable', '' );
        if ( $image_zoom_enable ) {
            $zoom_script = "
                (function () {
                    if (!('HTMLDialogElement' in window)) {
                        return;
                    }

                    window.developerStarterInitImageZoom = function () {
                        var dialog = document.getElementById('ds-img-viewer');
                        var viewerImg = document.getElementById('ds-viewer-img');
                        var btnPrev = document.getElementById('ds-img-prev');
                        var btnNext = document.getElementById('ds-img-next');
                        var btnClose = document.getElementById('ds-img-close');
                        if (!dialog || !viewerImg) {
                            return;
                        }

                        var images = Array.prototype.slice.call(document.querySelectorAll('.entry-content img'));
                        if (!images.length) {
                            return;
                        }

                        var items = [];
                        images.forEach(function (img) {
                            if (img.closest('a')) {
                                return;
                            }
                            var fullSrc = img.getAttribute('data-full') || img.dataset.full || img.currentSrc || img.src;
                            if (!fullSrc) {
                                return;
                            }
                            items.push({
                                el: img,
                                src: fullSrc,
                                alt: img.getAttribute('alt') || ''
                            });
                        });

                        if (!items.length) {
                            return;
                        }

                        var currentIndex = 0;

                        function updateViewer(index) {
                            currentIndex = index;
                            var item = items[currentIndex];
                            viewerImg.src = item.src;
                            viewerImg.alt = item.alt;
                            if (btnPrev) {
                                btnPrev.disabled = items.length <= 1;
                            }
                            if (btnNext) {
                                btnNext.disabled = items.length <= 1;
                            }
                        }

                        function findIndexByElement(el) {
                            for (var i = 0; i < items.length; i++) {
                                if (items[i].el === el) {
                                    return i;
                                }
                            }
                            return 0;
                        }

                        function openAt(index) {
                            updateViewer(index);
                            dialog.showModal();
                        }

                        function move(delta) {
                            if (items.length <= 1) {
                                return;
                            }
                            var nextIndex = currentIndex + delta;
                            if (nextIndex < 0) {
                                nextIndex = items.length - 1;
                            } else if (nextIndex >= items.length) {
                                nextIndex = 0;
                            }
                            updateViewer(nextIndex);
                        }

                        items.forEach(function (item) {
                            var img = item.el;
                            if (img.dataset.zoomBound) {
                                return;
                            }
                            img.dataset.zoomBound = '1';
                            img.classList.add('ds-zoomable-image');

                            img.addEventListener('click', function (event) {
                                event.preventDefault();
                                var index = findIndexByElement(img);
                                openAt(index);
                            });
                        });

                        if (btnPrev) {
                            btnPrev.addEventListener('click', function (event) {
                                event.preventDefault();
                                move(-1);
                            });
                        }
                        if (btnNext) {
                            btnNext.addEventListener('click', function (event) {
                                event.preventDefault();
                                move(1);
                            });
                        }
                        if (btnClose) {
                            btnClose.addEventListener('click', function (event) {
                                event.preventDefault();
                                dialog.close();
                            });
                        }

                        dialog.addEventListener('click', function (event) {
                            if (event.target === dialog) {
                                dialog.close();
                            }
                        });

                        dialog.addEventListener('keydown', function (event) {
                            if (event.key === 'ArrowLeft') {
                                event.preventDefault();
                                move(-1);
                            }
                            if (event.key === 'ArrowRight') {
                                event.preventDefault();
                                move(1);
                            }
                        });
                    };

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', window.developerStarterInitImageZoom);
                    } else {
                        window.developerStarterInitImageZoom();
                    }
                })();
            ";
            wp_add_inline_script( 'developer-starter-main', $zoom_script );
        }
    }

    /**
     * 判断当前请求是否启用文章阅读进度条。
     *
     * @return bool
     */
    private static function is_reading_progress_enabled() {
        return is_singular( 'post' ) && '1' === (string) developer_starter_get_option( 'reading_progress_enable', '' );
    }

    /**
     * 判断当前文章页是否启用正文朗读。
     *
     * @return bool
     */
    public static function is_post_speech_enabled() {
        return is_singular( 'post' ) && '1' === (string) developer_starter_get_option( 'post_speech_enable', '' );
    }

    /**
     * 判断当前文章页是否启用评论朗读。
     *
     * @return bool
     */
    public static function is_comment_speech_enabled() {
        $comments_enabled = function_exists( 'developer_starter_comments_feature_enabled' ) ? developer_starter_comments_feature_enabled() : true;
        return is_singular( 'post' ) && $comments_enabled && '1' === (string) developer_starter_get_option( 'comment_speech_enable', '' );
    }

    /**
     * 获取前端朗读语言。
     *
     * @return string
     */
    private static function get_speech_language() {
        $language = (string) developer_starter_get_option( 'speech_language', 'zh-CN' );
        return in_array( $language, array( 'zh-CN', 'en-US' ), true ) ? $language : 'zh-CN';
    }

    /**
     * 获取前端声音偏好。
     *
     * @return string
     */
    private static function get_speech_voice_preference() {
        $preference = (string) developer_starter_get_option( 'speech_voice_preference', 'auto' );
        return in_array( $preference, array( 'auto', 'female', 'male' ), true ) ? $preference : 'auto';
    }

    /**
     * 获取指定语音名称。
     *
     * @return string
     */
    private static function get_speech_voice_name() {
        return trim( sanitize_text_field( (string) developer_starter_get_option( 'speech_voice_name', '' ) ) );
    }

    /**
     * 获取指定语音 URI。
     *
     * @return string
     */
    private static function get_speech_voice_uri() {
        return trim( sanitize_text_field( (string) developer_starter_get_option( 'speech_voice_uri', '' ) ) );
    }

    /**
     * 获取前端朗读语速。
     *
     * @return float
     */
    private static function get_speech_rate() {
        $rate = developer_starter_get_option( 'speech_rate', '1' );
        $rate = is_numeric( $rate ) ? (float) $rate : 1.0;
        if ( $rate < 0.6 ) {
            return 0.6;
        }
        if ( $rate > 1.4 ) {
            return 1.4;
        }
        return $rate;
    }

    /**
     * 获取前端朗读音调。
     *
     * @return float
     */
    private static function get_speech_pitch() {
        $pitch = developer_starter_get_option( 'speech_pitch', '1' );
        $pitch = is_numeric( $pitch ) ? (float) $pitch : 1.0;
        if ( $pitch < 0.6 ) {
            return 0.6;
        }
        if ( $pitch > 1.4 ) {
            return 1.4;
        }
        return $pitch;
    }

    /**
     * 获取前端朗读音量。
     *
     * @return float
     */
    private static function get_speech_volume() {
        $volume = developer_starter_get_option( 'speech_volume', '1' );
        $volume = is_numeric( $volume ) ? (float) $volume : 1.0;
        if ( $volume < 0 ) {
            return 0.0;
        }
        if ( $volume > 1 ) {
            return 1.0;
        }
        return $volume;
    }

    /**
     * 判断隐藏页面时是否自动暂停朗读。
     *
     * @return bool
     */
    private static function get_speech_pause_on_hidden() {
        return '1' === (string) developer_starter_get_option( 'speech_pause_on_hidden', '1' );
    }

    /**
     * 输出文章阅读进度条容器。
     *
     * @return void
     */
    public function render_reading_progress_bar() {
        if ( ! self::is_reading_progress_enabled() ) {
            return;
        }
        ?>
        <div id="qiling-reading-progress" class="qiling-reading-progress" role="progressbar" aria-label="<?php esc_attr_e( '阅读进度', 'developer-starter' ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="qiling-reading-progress__track" aria-hidden="true">
                <div class="qiling-reading-progress__fill"></div>
            </div>
            <span class="qiling-reading-progress__value" aria-hidden="true">0%</span>
        </div>
        <?php
    }

    /**
     * 获取前端懒加载使用的 jQuery 资源信息。
     *
     * @return array{url:string,ver:string}
     */
    private function get_frontend_jquery_asset() {
        $jquery_lazy_url = includes_url( 'js/jquery/jquery.min.js' );
        $jquery_lazy_ver = (string) get_bloginfo( 'version' );

        if ( function_exists( 'wp_scripts' ) ) {
            $wp_scripts = wp_scripts();
            if ( $wp_scripts && isset( $wp_scripts->registered['jquery-core'] ) ) {
                $jquery_core = $wp_scripts->registered['jquery-core'];
                if ( ! empty( $jquery_core->src ) ) {
                    $jquery_src = (string) $jquery_core->src;
                    if ( 0 === strpos( $jquery_src, '//' ) ) {
                        $jquery_src = is_ssl() ? 'https:' . $jquery_src : 'http:' . $jquery_src;
                    } elseif ( 0 === strpos( $jquery_src, '/' ) ) {
                        $jquery_src = home_url( $jquery_src );
                    } elseif ( ! preg_match( '#^https?://#i', $jquery_src ) ) {
                        $jquery_src = site_url( $jquery_src );
                    }
                    $jquery_lazy_url = $jquery_src;
                }

                if ( isset( $jquery_core->ver ) && '' !== (string) $jquery_core->ver ) {
                    $jquery_lazy_ver = (string) $jquery_core->ver;
                }
            }
        }

        return array(
            'url' => $jquery_lazy_url,
            'ver' => $jquery_lazy_ver,
        );
    }

    /**
     * 文章海报缓存文件路径
     */
    private function get_post_poster_cache_paths( $post_id, $cache_key ) {
        $uploads = wp_upload_dir();
        $subdir = 'qiling-posters';
        $safe_key = strtolower( preg_replace( '/[^a-f0-9]/', '', (string) $cache_key ) );
        $filename = 'post-' . absint( $post_id ) . '-' . $safe_key . '.png';
        $dir = trailingslashit( $uploads['basedir'] ) . $subdir;

        return array(
            'dir'  => $dir,
            'path' => trailingslashit( $dir ) . $filename,
            'url'  => trailingslashit( $uploads['baseurl'] ) . $subdir . '/' . $filename,
        );
    }

    /**
     * 是否允许未登录游客将生成的海报写入服务器缓存。
     */
    private function is_post_poster_guest_cache_enabled() {
        $enabled = function_exists( 'developer_starter_get_option' )
            ? (bool) developer_starter_get_option( 'post_poster_guest_cache_enable', '' )
            : false;

        return (bool) apply_filters( 'developer_starter_post_poster_guest_cache_enabled', $enabled );
    }

    /**
     * 当前请求是否允许写入文章海报缓存。
     */
    private function current_request_can_save_post_poster_cache() {
        if ( is_user_logged_in() ) {
            return true;
        }

        return $this->is_post_poster_guest_cache_enabled();
    }

    /**
     * 获取文章海报写入限额配置
     */
    private function get_post_poster_write_limit_config( $post_id ) {
        $window = function_exists( 'developer_starter_get_rate_limit_window' ) ? developer_starter_get_rate_limit_window() : 60;
        $rate_max = function_exists( 'developer_starter_get_option' )
            ? intval( developer_starter_get_option( 'request_rate_limit_poster_max', 12 ) )
            : 12;
        $daily_write_max = function_exists( 'developer_starter_get_option' )
            ? intval( developer_starter_get_option( 'post_poster_guest_daily_write_max', 60 ) )
            : 60;
        $daily_bytes_max_mb = function_exists( 'developer_starter_get_option' )
            ? intval( developer_starter_get_option( 'post_poster_guest_daily_bytes_max_mb', 96 ) )
            : 96;

        $config = array(
            'window'             => $window,
            'rate_max'           => $rate_max,
            'daily_write_max'    => $daily_write_max,
            'daily_bytes_max'    => $daily_bytes_max_mb * 1024 * 1024,
            'per_post_file_max'  => 48,
            'dir_file_max'       => 10000,
            'dir_bytes_max'      => 1536 * 1024 * 1024,
        );

        $config = apply_filters( 'developer_starter_post_poster_write_limit_config', $config, absint( $post_id ) );

        $config['window'] = max( 10, min( 3600, intval( $config['window'] ) ) );
        $config['rate_max'] = max( 0, min( 500, intval( $config['rate_max'] ) ) );
        $config['daily_write_max'] = max( 0, intval( $config['daily_write_max'] ) );
        $config['daily_bytes_max'] = max( 0, intval( $config['daily_bytes_max'] ) );
        $config['per_post_file_max'] = max( 0, intval( $config['per_post_file_max'] ) );
        $config['dir_file_max'] = max( 0, intval( $config['dir_file_max'] ) );
        $config['dir_bytes_max'] = max( 0, intval( $config['dir_bytes_max'] ) );

        return $config;
    }

    /**
     * 获取限流用客户端 IP
     */
    private function get_post_poster_limit_ip() {
        $ip = developer_starter_get_client_ip();
        $ip = trim( sanitize_text_field( (string) apply_filters( 'developer_starter_post_poster_limit_ip', $ip ) ) );
        if ( '' === $ip ) {
            $ip = '0.0.0.0';
        }

        return substr( $ip, 0, 64 );
    }

    /**
     * 获取每日配额计数 transient key
     */
    private function get_post_poster_daily_quota_key( $ip ) {
        $day = gmdate( 'Ymd', (int) current_time( 'timestamp', true ) );
        return 'ds_poster_quota_' . md5( (string) $ip . '|' . $day );
    }

    /**
     * 游客海报写入频率限制（对象缓存优先，transient 兜底）。
     */
    private function is_post_poster_guest_rate_limited( $max_requests, $window_seconds ) {
        $max_requests = max( 1, intval( $max_requests ) );
        $window_seconds = max( 10, intval( $window_seconds ) );
        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        $key = 'ds_poster_rl_' . md5( $this->get_post_poster_limit_ip() . '|' . substr( $ua, 0, 120 ) );

        $payload = wp_cache_get( $key, self::POST_POSTER_GUARD_GROUP );
        if ( ! is_array( $payload ) ) {
            $payload = get_transient( $key );
        }
        if ( ! is_array( $payload ) ) {
            $payload = array( 'count' => 0 );
        }

        $count = isset( $payload['count'] ) ? intval( $payload['count'] ) : 0;
        if ( $count >= $max_requests ) {
            return true;
        }

        $payload['count'] = $count + 1;
        wp_cache_set( $key, $payload, self::POST_POSTER_GUARD_GROUP, $window_seconds );
        set_transient( $key, $payload, $window_seconds );

        return false;
    }

    /**
     * 统计海报缓存目录信息
     */
    private function collect_post_poster_cache_metrics( $dir, $post_id ) {
        $metrics = array(
            'count'      => 0,
            'bytes'      => 0,
            'post_count' => 0,
        );

        if ( ! is_dir( $dir ) ) {
            return $metrics;
        }

        $post_prefix = 'post-' . absint( $post_id ) . '-';
        try {
            $iterator = new \FilesystemIterator( $dir, \FilesystemIterator::SKIP_DOTS );
            foreach ( $iterator as $item ) {
                if ( ! $item->isFile() ) {
                    continue;
                }

                $metrics['count']++;
                $metrics['bytes'] += max( 0, (int) $item->getSize() );

                $filename = $item->getFilename();
                if ( 0 === strpos( $filename, $post_prefix ) && '.png' === substr( $filename, -4 ) ) {
                    $metrics['post_count']++;
                }
            }
        } catch ( \Exception $e ) {
            // 目录读取异常时返回当前已统计值（默认 0）
        }

        return $metrics;
    }

    /**
     * 文章海报写入保护（频率 + 配额 + 目录空间）
     */
    private function guard_post_poster_cache_write( $post_id, $binary_size, $cache_dir ) {
        $post_id = absint( $post_id );
        $binary_size = max( 0, intval( $binary_size ) );
        $config = $this->get_post_poster_write_limit_config( $post_id );

        $daily_usage = null;
        $daily_key = '';
        $is_guest = ! is_user_logged_in();

        if ( $is_guest ) {
            if ( $config['rate_max'] > 0 && $this->is_post_poster_guest_rate_limited( $config['rate_max'], $config['window'] ) ) {
                return new \WP_Error( 'poster_rate_limited', __( '请求过于频繁，请稍后再试', 'developer-starter' ) );
            }

            $daily_key = $this->get_post_poster_daily_quota_key( $this->get_post_poster_limit_ip() );
            $daily_usage = get_transient( $daily_key );
            if ( ! is_array( $daily_usage ) ) {
                $daily_usage = array(
                    'writes' => 0,
                    'bytes'  => 0,
                );
            }

            $daily_writes = isset( $daily_usage['writes'] ) ? intval( $daily_usage['writes'] ) : 0;
            $daily_bytes = isset( $daily_usage['bytes'] ) ? intval( $daily_usage['bytes'] ) : 0;

            if ( $config['daily_write_max'] > 0 && ( $daily_writes + 1 ) > $config['daily_write_max'] ) {
                return new \WP_Error( 'poster_daily_write_quota', __( '今日海报生成次数已达上限，请明日再试', 'developer-starter' ) );
            }
            if ( $config['daily_bytes_max'] > 0 && ( $daily_bytes + $binary_size ) > $config['daily_bytes_max'] ) {
                return new \WP_Error( 'poster_daily_bytes_quota', __( '今日海报缓存写入流量已达上限，请稍后再试', 'developer-starter' ) );
            }
        }

        $need_metrics = $config['per_post_file_max'] > 0 || $config['dir_file_max'] > 0 || $config['dir_bytes_max'] > 0;
        if ( $need_metrics ) {
            $metrics = $this->collect_post_poster_cache_metrics( $cache_dir, $post_id );
            if ( $config['per_post_file_max'] > 0 && $metrics['post_count'] >= $config['per_post_file_max'] ) {
                return new \WP_Error( 'poster_post_cache_quota', __( '当前文章海报缓存版本过多，请稍后再试', 'developer-starter' ) );
            }

            if ( $config['dir_file_max'] > 0 && $metrics['count'] >= $config['dir_file_max'] ) {
                return new \WP_Error( 'poster_dir_file_quota', __( '海报缓存文件数已达上限，请先清理缓存', 'developer-starter' ) );
            }

            if ( $config['dir_bytes_max'] > 0 && ( $metrics['bytes'] + $binary_size ) > $config['dir_bytes_max'] ) {
                return new \WP_Error( 'poster_dir_size_quota', __( '海报缓存空间已达上限，请先清理缓存', 'developer-starter' ) );
            }
        }

        if ( $is_guest && is_array( $daily_usage ) ) {
            $daily_usage['writes'] = intval( $daily_usage['writes'] ) + 1;
            $daily_usage['bytes'] = intval( $daily_usage['bytes'] ) + $binary_size;
            set_transient( $daily_key, $daily_usage, DAY_IN_SECONDS + HOUR_IN_SECONDS );
        }

        return true;
    }

    /**
     * 海报写入保护错误映射为 HTTP 状态码
     */
    private function get_post_poster_limit_error_status( $error_code ) {
        $storage_error_codes = array(
            'poster_dir_file_quota',
            'poster_dir_size_quota',
        );

        if ( in_array( (string) $error_code, $storage_error_codes, true ) ) {
            return 507;
        }

        return 429;
    }

    /**
     * 获取公共 AJAX 限流配置里的窗口。
     */
    private function get_public_ajax_rate_limit_window() {
        return function_exists( 'developer_starter_get_rate_limit_window' ) ? developer_starter_get_rate_limit_window() : 60;
    }

    /**
     * 统一公共文章增强 AJAX 限流。
     *
     * @param string   $scope 作用域。
     * @param int      $max_requests 窗口请求数。
     * @param int|null $window_seconds 窗口秒数。
     * @param bool     $json_response 是否输出 JSON 响应。
     * @return void
     */
    private function guard_public_ajax_rate_limit( $scope, $max_requests, $window_seconds = null, $json_response = true ) {
        if (
            function_exists( 'developer_starter_is_public_ajax_rate_limited' )
            && developer_starter_is_public_ajax_rate_limited( $scope, $max_requests, $window_seconds )
        ) {
            if ( $json_response ) {
                if ( function_exists( 'developer_starter_send_public_ajax_rate_limited' ) ) {
                    developer_starter_send_public_ajax_rate_limited();
                }

                wp_send_json_error(
                    array(
                        'message' => __( '请求过于频繁，请稍后再试', 'developer-starter' ),
                        'code'    => 'rate_limited',
                    ),
                    429
                );
            }

            if ( function_exists( 'developer_starter_die_public_ajax_rate_limited' ) ) {
                developer_starter_die_public_ajax_rate_limited();
            }

            status_header( 429 );
            nocache_headers();
            wp_die( esc_html__( '请求过于频繁，请稍后再试', 'developer-starter' ), esc_html__( 'Too Many Requests', 'developer-starter' ), array( 'response' => 429 ) );
        }
    }

    /**
     * 校验海报缓存请求参数
     */
    private function validate_post_poster_request( $post_id, $cache_key, $nonce ) {
        if ( $post_id <= 0 ) {
            return new \WP_Error( 'invalid_post', __( '文章参数错误', 'developer-starter' ) );
        }

        if ( ! preg_match( '/^[a-f0-9]{16}$/', (string) $cache_key ) ) {
            return new \WP_Error( 'invalid_key', __( '缓存键无效', 'developer-starter' ) );
        }

        if ( ! wp_verify_nonce( (string) $nonce, 'ds_post_poster_' . $post_id ) ) {
            return new \WP_Error( 'invalid_nonce', __( '请求校验失败', 'developer-starter' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'post' || $post->post_status !== 'publish' ) {
            return new \WP_Error( 'invalid_post_status', __( '文章不可用', 'developer-starter' ) );
        }

        return $post;
    }

    /**
     * 查询文章海报缓存
     */
    public function ajax_get_post_poster_cache() {
        $this->guard_public_ajax_rate_limit( 'post_poster_get_cache', 120, 60 );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        $cache_key = isset( $_POST['cache_key'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) ) : '';
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

        $validated = $this->validate_post_poster_request( $post_id, $cache_key, $nonce );
        if ( is_wp_error( $validated ) ) {
            wp_send_json_error( array(
                'message' => $validated->get_error_message(),
                'code' => $validated->get_error_code(),
            ) );
        }

        $paths = $this->get_post_poster_cache_paths( $post_id, $cache_key );
        if ( file_exists( $paths['path'] ) ) {
            wp_send_json_success( array(
                'url' => esc_url_raw( $paths['url'] ),
            ) );
        }

        wp_send_json_error( array(
            'message' => __( '缓存不存在', 'developer-starter' ),
            'code' => 'cache_miss',
        ) );
    }

    /**
     * 保存文章海报缓存
     */
    public function ajax_save_post_poster_cache() {
        $poster_rate_max = function_exists( 'developer_starter_get_option' )
            ? intval( developer_starter_get_option( 'request_rate_limit_poster_max', 12 ) )
            : 12;
        $poster_rate_max = max( 1, min( 120, $poster_rate_max ) );
        $this->guard_public_ajax_rate_limit( 'post_poster_save_cache', $poster_rate_max, $this->get_public_ajax_rate_limit_window() );

        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        $cache_key = isset( $_POST['cache_key'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) ) : '';
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        $image_data = isset( $_POST['image_data'] ) ? (string) wp_unslash( $_POST['image_data'] ) : '';

        $validated = $this->validate_post_poster_request( $post_id, $cache_key, $nonce );
        if ( is_wp_error( $validated ) ) {
            wp_send_json_error( array(
                'message' => $validated->get_error_message(),
                'code' => $validated->get_error_code(),
            ) );
        }

        if ( ! $this->current_request_can_save_post_poster_cache() ) {
            wp_send_json_error(
                array(
                    'message' => __( '游客海报缓存未启用，请直接下载生成的海报。', 'developer-starter' ),
                    'code'    => 'poster_guest_cache_disabled',
                ),
                403
            );
        }

        $paths = $this->get_post_poster_cache_paths( $post_id, $cache_key );
        if ( file_exists( $paths['path'] ) ) {
            wp_send_json_success( array(
                'url' => esc_url_raw( $paths['url'] ),
                'cached' => true,
            ) );
        }

        if ( ! preg_match( '#^data:image/png;base64,#', $image_data ) ) {
            wp_send_json_error( array(
                'message' => __( '海报数据格式错误', 'developer-starter' ),
                'code' => 'invalid_image_format',
            ) );
        }

        $raw = substr( $image_data, strpos( $image_data, ',' ) + 1 );
        $binary = base64_decode( $raw, true );
        if ( false === $binary || '' === $binary ) {
            wp_send_json_error( array(
                'message' => __( '海报数据解码失败', 'developer-starter' ),
                'code' => 'decode_failed',
            ) );
        }

        $binary_size = strlen( $binary );
        if ( $binary_size > 8 * 1024 * 1024 ) {
            wp_send_json_error( array(
                'message' => __( '海报文件过大', 'developer-starter' ),
                'code' => 'image_too_large',
            ) );
        }

        $limit_guard = $this->guard_post_poster_cache_write( $post_id, $binary_size, $paths['dir'] );
        if ( is_wp_error( $limit_guard ) ) {
            wp_send_json_error(
                array(
                    'message' => $limit_guard->get_error_message(),
                    'code'    => $limit_guard->get_error_code(),
                ),
                $this->get_post_poster_limit_error_status( $limit_guard->get_error_code() )
            );
        }

        if ( ! function_exists( 'imagecreatefromstring' ) || ! function_exists( 'imagepng' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( '服务器图片处理组件不可用，无法生成海报缓存', 'developer-starter' ),
                    'code'    => 'image_library_unavailable',
                ),
                500
            );
        }

        if ( ! wp_mkdir_p( $paths['dir'] ) ) {
            wp_send_json_error( array(
                'message' => __( '缓存目录创建失败', 'developer-starter' ),
                'code' => 'mkdir_failed',
            ) );
        }

        $saved = false;
        if ( function_exists( 'imagecreatefromstring' ) && function_exists( 'imagepng' ) ) {
            $img = @imagecreatefromstring( $binary );
            if ( $img ) {
                $width = imagesx( $img );
                $height = imagesy( $img );
                if ( $width >= 200 && $height >= 200 && $width <= 2500 && $height <= 3500 ) {
                    imagesavealpha( $img, true );
                    ob_start();
                    $encoded = imagepng( $img, null, 6 );
                    $png_binary = ob_get_clean();
                    if ( $encoded && is_string( $png_binary ) && '' !== $png_binary ) {
                        $saved = developer_starter_filesystem_write_file(
                            $paths['path'],
                            $png_binary,
                            array(
                                'operation' => 'write_poster_cache',
                                'context'   => array( 'component' => 'post_poster_cache' ),
                            )
                        );
                    }
                }
                imagedestroy( $img );
            }
        }

        if ( ! $saved || ! file_exists( $paths['path'] ) ) {
            wp_send_json_error( array(
                'message' => __( '海报缓存写入失败', 'developer-starter' ),
                'code' => 'save_failed',
            ) );
        }

        wp_send_json_success( array(
            'url' => esc_url_raw( $paths['url'] ),
            'cached' => false,
        ) );
    }

    /**
     * 下载文章海报缓存（强制附件下载）
     */
    public function ajax_download_post_poster() {
        $this->guard_public_ajax_rate_limit( 'post_poster_download', 60, 60, false );

        $post_id = isset( $_REQUEST['post_id'] ) ? absint( wp_unslash( $_REQUEST['post_id'] ) ) : 0;
        $cache_key = isset( $_REQUEST['cache_key'] ) ? strtolower( sanitize_text_field( wp_unslash( $_REQUEST['cache_key'] ) ) ) : '';
        $nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';

        $validated = $this->validate_post_poster_request( $post_id, $cache_key, $nonce );
        if ( is_wp_error( $validated ) ) {
            status_header( 403 );
            wp_die( esc_html( $validated->get_error_message() ) );
        }

        $paths = $this->get_post_poster_cache_paths( $post_id, $cache_key );
        $binary = '';

        if ( file_exists( $paths['path'] ) ) {
            $binary = (string) @file_get_contents( $paths['path'] );
        } else {
            $response = wp_remote_get( $paths['url'], array(
                'timeout' => 12,
                'redirection' => 3,
            ) );
            if ( ! is_wp_error( $response ) ) {
                $code = (int) wp_remote_retrieve_response_code( $response );
                if ( 200 === $code ) {
                    $binary = (string) wp_remote_retrieve_body( $response );
                }
            }
        }

        if ( '' === $binary ) {
            status_header( 404 );
            wp_die( esc_html__( '海报不存在，请先点击“生成海报”', 'developer-starter' ) );
        }

        $title_slug = sanitize_title( get_the_title( $post_id ) );
        if ( '' === $title_slug ) {
            $title_slug = 'post';
        }
        $filename = $title_slug . '-poster.png';

        nocache_headers();
        header( 'Content-Type: image/png' );
        header( 'Content-Length: ' . strlen( $binary ) );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        echo $binary;
        exit;
    }

    /**
     * 智能加载 PrismJS 资源
     */
    public function enqueue_prism_assets() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }
        
        $code_highlight_enable = developer_starter_get_option( 'code_highlight_enable', '' );
        if ( ! $code_highlight_enable ) {
            return;
        }
        
        // 检测文章内容是否包含代码块
        global $post;
        $content = $post->post_content;
        
        // 检测 <pre> 或 <code> 标签
        if ( ! preg_match( '/<(pre|code)[^>]*>/i', $content ) ) {
            return;
        }
        
        // 获取 CDN 设置或使用本地资源，并通过统一解析器执行白名单和版本规则。
        $prism_css = function_exists( 'developer_starter_get_third_party_asset' )
            ? developer_starter_get_third_party_asset( 'prism_css' )
            : array(
                'url'     => DEVELOPER_STARTER_ASSETS . '/css/vendor/prism.css',
                'version' => '1.29.0',
            );
        $prism_js = function_exists( 'developer_starter_get_third_party_asset' )
            ? developer_starter_get_third_party_asset( 'prism_js' )
            : array(
                'url'     => DEVELOPER_STARTER_ASSETS . '/js/vendor/prism.js',
                'version' => '1.29.0',
            );
        
        // 加载 PrismJS CSS
        wp_enqueue_style( 'prismjs', $prism_css['url'], array(), $prism_css['version'] );
        
        // 加载 PrismJS JS
        wp_enqueue_script( 'prismjs', $prism_js['url'], array(), $prism_js['version'], true );
    }

    /**
     * 为代码块添加行号类 (保留但不默认使用)
     */
    public function add_line_numbers_class( $content ) {
        // 为所有 <pre> 标签添加 line-numbers 类
        $content = preg_replace( '/<pre([^>]*)>/i', '<pre$1 class="line-numbers">', $content );
        return $content;
    }

    /**
     * 统计文章浏览量
     */
    public function track_post_views() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }
        
        $post_views_enable = developer_starter_get_option( 'post_views_enable', '' );
        if ( ! $post_views_enable ) {
            return;
        }
        
        // 检查是否排除管理员
        $exclude_admin = developer_starter_get_option( 'post_views_exclude_admin', '' );
        if ( $exclude_admin && current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $post_id = (int) get_queried_object_id();
        if ( $post_id <= 0 ) {
            return;
        }

        $view_cookie_name = 'ds_post_viewed_' . $post_id;
        if ( isset( $_COOKIE[ $view_cookie_name ] ) ) {
            return;
        }

        $this->pending_post_view = array(
            'post_id'     => $post_id,
            'cookie_name' => $view_cookie_name,
            'signature'   => $this->get_post_view_signature( $post_id ),
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
        );
    }

    /**
     * 输出文章浏览异步上报脚本，避免主页面请求同步写库。
     */
    public function output_post_view_tracker() {
        if ( empty( $this->pending_post_view ) || ! is_array( $this->pending_post_view ) ) {
            return;
        }

        $config = array(
            'ajaxUrl'    => (string) $this->pending_post_view['ajax_url'],
            'action'     => 'developer_starter_track_post_view',
            'postId'     => (int) $this->pending_post_view['post_id'],
            'cookieName' => (string) $this->pending_post_view['cookie_name'],
            'signature'  => (string) $this->pending_post_view['signature'],
        );
        ?>
        <script>
        (function () {
            var config = <?php echo wp_json_encode( $config ); ?>;
            if (!config || !config.postId || !config.ajaxUrl || !config.cookieName) {
                return;
            }

            var escapedName = String(config.cookieName).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            if (document.cookie.match(new RegExp('(?:^|;\\s*)' + escapedName + '='))) {
                return;
            }

            var tracked = false;
            var pendingKey = '__ds_post_view_pending_' + String(config.postId);

            try {
                if (window.sessionStorage) {
                    var pendingAt = parseInt(sessionStorage.getItem(pendingKey) || '0', 10);
                    if (pendingAt && (Date.now() - pendingAt) < 30000) {
                        return;
                    }
                }
            } catch (err) {
                // 忽略 sessionStorage 异常
            }

            function sendTrackRequest() {
                if (tracked) {
                    return;
                }

                tracked = true;
                try {
                    if (window.sessionStorage) {
                        sessionStorage.setItem(pendingKey, String(Date.now()));
                    }
                } catch (err) {
                    // 忽略 sessionStorage 异常
                }

                var payload = 'action=' + encodeURIComponent(config.action)
                    + '&post_id=' + encodeURIComponent(String(config.postId))
                    + '&signature=' + encodeURIComponent(config.signature || '');

                if (navigator.sendBeacon) {
                    try {
                        var blob = new Blob([payload], {
                            type: 'application/x-www-form-urlencoded; charset=UTF-8'
                        });
                        if (navigator.sendBeacon(config.ajaxUrl, blob)) {
                            return;
                        }
                    } catch (err) {
                        // 回退到 fetch
                    }
                }

                if (window.fetch) {
                    fetch(config.ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        keepalive: true,
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: payload
                    }).catch(function () {
                        // 忽略失败，避免影响页面交互
                    });
                }
            }

            if (window.addEventListener) {
                var onPageHide = function () {
                    sendTrackRequest();
                    window.removeEventListener('pagehide', onPageHide);
                };
                window.addEventListener('pagehide', onPageHide);
            }

            if (window.requestIdleCallback) {
                window.requestIdleCallback(sendTrackRequest, { timeout: 1200 });
            } else {
                window.setTimeout(sendTrackRequest, 0);
            }
        })();
        </script>
        <?php
    }

    /**
     * 异步写入文章浏览量。
     */
    public function ajax_track_post_view() {
        $this->guard_public_ajax_rate_limit( 'post_view_track', 120, 60 );

        $post_id   = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        $signature = isset( $_POST['signature'] ) ? sanitize_text_field( wp_unslash( $_POST['signature'] ) ) : '';

        if ( $post_id <= 0 || ! hash_equals( $this->get_post_view_signature( $post_id ), $signature ) ) {
            wp_send_json_error( array( 'message' => 'invalid_post' ), 400 );
        }

        $post = get_post( $post_id );
        if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
            wp_send_json_error( array( 'message' => 'invalid_post' ), 404 );
        }

        $post_views_enable = developer_starter_get_option( 'post_views_enable', '' );
        if ( ! $post_views_enable ) {
            wp_send_json_success( array( 'tracked' => false, 'reason' => 'disabled' ) );
        }

        $exclude_admin = developer_starter_get_option( 'post_views_exclude_admin', '' );
        if ( $exclude_admin && current_user_can( 'manage_options' ) ) {
            wp_send_json_success( array( 'tracked' => false, 'reason' => 'excluded_admin' ) );
        }

        $view_cookie_name = 'ds_post_viewed_' . $post_id;
        if ( isset( $_COOKIE[ $view_cookie_name ] ) ) {
            wp_send_json_success( array( 'tracked' => false, 'reason' => 'already_tracked' ) );
        }

        $this->mark_post_view_cookie( $view_cookie_name );
        $buffered = $this->buffer_post_view_increment( $post_id );
        if ( ! $buffered ) {
            $this->increment_post_views_count( $post_id, 'ds_post_views_count' );
        }

        wp_send_json_success(
            array(
                'tracked'  => true,
                'buffered' => $buffered,
            )
        );
    }

    /**
     * 判断当前环境是否支持对象缓存缓冲浏览量。
     *
     * @return bool
     */
    private function can_buffer_post_views() {
        return function_exists( 'wp_using_ext_object_cache' )
            && wp_using_ext_object_cache()
            && function_exists( 'wp_cache_add' )
            && function_exists( 'wp_cache_decr' )
            && function_exists( 'wp_cache_get' )
            && function_exists( 'wp_cache_set' )
            && function_exists( 'wp_cache_delete' )
            && function_exists( 'wp_cache_incr' )
            && function_exists( 'wp_next_scheduled' )
            && function_exists( 'wp_schedule_single_event' );
    }

    /**
     * 获取浏览量缓冲计数键。
     *
     * @param int $post_id 文章 ID。
     * @return string
     */
    private function get_post_view_buffer_counter_key( $post_id ) {
        return 'pv_counter_' . (int) $post_id;
    }

    /**
     * 获取浏览量缓冲脏标记键。
     *
     * @param int $post_id 文章 ID。
     * @return string
     */
    private function get_post_view_buffer_dirty_key( $post_id ) {
        return 'pv_dirty_' . (int) $post_id;
    }

    /**
     * 获取浏览量缓冲回写锁键。
     *
     * @param int $post_id 文章 ID。
     * @return string
     */
    private function get_post_view_buffer_lock_key( $post_id ) {
        return 'pv_lock_' . (int) $post_id;
    }

    /**
     * 将浏览量计入对象缓存缓冲区。
     *
     * @param int $post_id 文章 ID。
     * @return bool
     */
    private function buffer_post_view_increment( $post_id ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 || ! $this->can_buffer_post_views() ) {
            return false;
        }

        $counter_key = $this->get_post_view_buffer_counter_key( $post_id );

        wp_cache_add( $counter_key, 0, self::POST_VIEW_BUFFER_GROUP, self::POST_VIEW_BUFFER_TTL );
        $incremented = wp_cache_incr( $counter_key, 1, self::POST_VIEW_BUFFER_GROUP );

        if ( false === $incremented ) {
            $current = wp_cache_get( $counter_key, self::POST_VIEW_BUFFER_GROUP );
            $current = is_numeric( $current ) ? (int) $current : 0;
            $incremented = $current + 1;
            wp_cache_set( $counter_key, $incremented, self::POST_VIEW_BUFFER_GROUP, self::POST_VIEW_BUFFER_TTL );
        }

        $this->mark_post_view_buffer_dirty( $post_id );

        return true;
    }

    /**
     * 为待回写文章打脏标并安排一次独立回写任务。
     *
     * @param int $post_id 文章 ID。
     * @return void
     */
    private function mark_post_view_buffer_dirty( $post_id ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 ) {
            return;
        }

        $dirty_key = $this->get_post_view_buffer_dirty_key( $post_id );
        wp_cache_set( $dirty_key, 1, self::POST_VIEW_BUFFER_GROUP, self::POST_VIEW_BUFFER_TTL );
        $this->schedule_post_view_buffer_flush( $post_id );
    }

    /**
     * 为指定文章安排一次浏览量缓冲回写任务。
     *
     * @param int $post_id 文章 ID。
     * @return void
     */
    private function schedule_post_view_buffer_flush( $post_id ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 ) {
            return;
        }

        $args = array( $post_id );
        $next = wp_next_scheduled( 'developer_starter_flush_post_view_buffer', $args );
        if ( false === $next ) {
            wp_schedule_single_event( time() + self::POST_VIEW_FLUSH_DELAY, 'developer_starter_flush_post_view_buffer', $args );
        }
    }

    /**
     * 将对象缓存中的浏览量回写到数据库。
     *
     * @param int $post_id 文章 ID。
     * @return void
     */
    public function flush_post_view_buffer( $post_id = 0 ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 || ! $this->can_buffer_post_views() ) {
            return;
        }

        $counter_key = $this->get_post_view_buffer_counter_key( $post_id );
        $dirty_key   = $this->get_post_view_buffer_dirty_key( $post_id );
        $lock_key    = $this->get_post_view_buffer_lock_key( $post_id );

        $locked = wp_cache_add( $lock_key, 1, self::POST_VIEW_BUFFER_GROUP, MINUTE_IN_SECONDS );
        if ( ! $locked ) {
            $this->schedule_post_view_buffer_flush( $post_id );
            return;
        }

        $count       = wp_cache_get( $counter_key, self::POST_VIEW_BUFFER_GROUP );
        $count       = is_numeric( $count ) ? (int) $count : 0;

        if ( $count <= 0 ) {
            wp_cache_delete( $dirty_key, self::POST_VIEW_BUFFER_GROUP );
            wp_cache_delete( $lock_key, self::POST_VIEW_BUFFER_GROUP );
            return;
        }

        $this->increment_post_views_count( $post_id, 'ds_post_views_count', $count );

        $remaining = wp_cache_decr( $counter_key, $count, self::POST_VIEW_BUFFER_GROUP );
        if ( false === $remaining ) {
            $remaining = wp_cache_get( $counter_key, self::POST_VIEW_BUFFER_GROUP );
        }

        $remaining = is_numeric( $remaining ) ? max( 0, (int) $remaining ) : 0;

        if ( $remaining > 0 ) {
            wp_cache_set( $dirty_key, 1, self::POST_VIEW_BUFFER_GROUP, self::POST_VIEW_BUFFER_TTL );
            $this->schedule_post_view_buffer_flush( $post_id );
        } else {
            wp_cache_delete( $dirty_key, self::POST_VIEW_BUFFER_GROUP );
        }

        wp_cache_delete( $lock_key, self::POST_VIEW_BUFFER_GROUP );
    }

    /**
     * 获取浏览量去重 Cookie 的有效期。
     *
     * @return int
     */
    private function get_post_view_cookie_ttl() {
        $ttl = (int) apply_filters( 'developer_starter_post_view_cookie_ttl', 6 * HOUR_IN_SECONDS );
        if ( $ttl < 60 ) {
            $ttl = HOUR_IN_SECONDS;
        }

        return $ttl;
    }

    /**
     * 获取浏览量去重 Cookie 的 path。
     *
     * @return string
     */
    private function get_post_view_cookie_path() {
        return defined( 'COOKIEPATH' ) && COOKIEPATH ? (string) COOKIEPATH : '/';
    }

    /**
     * 生成文章浏览量上报签名。
     *
     * @param int $post_id 文章 ID。
     * @return string
     */
    private function get_post_view_signature( $post_id ) {
        return (string) wp_hash( 'developer_starter_track_post_view|' . (int) $post_id );
    }

    /**
     * 标记浏览去重 Cookie
     */
    private function mark_post_view_cookie( $cookie_name ) {
        $ttl = $this->get_post_view_cookie_ttl();
        $expires = time() + $ttl;
        $path = $this->get_post_view_cookie_path();
        $domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
        $secure = is_ssl();
        $httponly = true;

        if ( ! headers_sent() ) {
            if ( PHP_VERSION_ID >= 70300 ) {
                setcookie( $cookie_name, '1', array(
                    'expires'  => $expires,
                    'path'     => $path,
                    'domain'   => $domain,
                    'secure'   => $secure,
                    'httponly' => $httponly,
                    'samesite' => 'Lax',
                ) );
            } else {
                setcookie( $cookie_name, '1', $expires, $path, $domain, $secure, $httponly );
            }
        }

        $_COOKIE[ $cookie_name ] = '1';
    }

    /**
     * 使用单条 SQL 自增浏览量，减少读写往返
     */
    private function increment_post_views_count( $post_id, $count_key, $amount = 1 ) {
        global $wpdb;

        $amount = max( 1, (int) $amount );

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + %d WHERE post_id = %d AND meta_key = %s",
                $amount,
                $post_id,
                $count_key
            )
        );

        if ( false === $updated ) {
            return;
        }

        if ( 0 === (int) $updated ) {
            $inserted = add_post_meta( $post_id, $count_key, $amount, true );
            if ( ! $inserted ) {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + %d WHERE post_id = %d AND meta_key = %s",
                        $amount,
                        $post_id,
                        $count_key
                    )
                );
            }
        }
    }

    /**
     * 获取文章浏览量
     */
    public static function get_post_views( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        $count_key = 'ds_post_views_count';
        $count = get_post_meta( $post_id, $count_key, true );
        return $count ? (int) $count : 0;
    }

    /**
     * 计算阅读时长
     */
    public static function get_reading_time( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $content = get_post_field( 'post_content', $post_id );
        $content = wp_strip_all_tags( $content );
        $word_count = mb_strlen( $content, 'UTF-8' );
        
        $reading_speed = developer_starter_get_option( 'reading_speed', 400 );
        $reading_speed = $reading_speed ? (int) $reading_speed : 400;
        
        $minutes = ceil( $word_count / $reading_speed );
        
        return max( 1, $minutes );
    }

    /**
     * 生成文章目录
     */
    public static function generate_toc( $content ) {
        $toc_enable = developer_starter_get_option( 'toc_enable', '' );
        if ( ! $toc_enable ) {
            return array( 'toc' => '', 'content' => $content );
        }
        
        $heading_levels = developer_starter_get_option( 'toc_heading_levels', 'h2h3' );
        $min_headings = developer_starter_get_option( 'toc_min_headings', 3 );
        $min_headings = $min_headings ? (int) $min_headings : 3;
        
        // 根据设置确定要匹配的标题
        switch ( $heading_levels ) {
            case 'h2':
                $pattern = '/<h2([^>]*)>(.*?)<\/h2>/is';
                break;
            case 'h2h3h4':
                $pattern = '/<h([2-4])([^>]*)>(.*?)<\/h\1>/is';
                break;
            case 'h2h3':
            default:
                $pattern = '/<h([23])([^>]*)>(.*?)<\/h\1>/is';
                break;
        }
        
        preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
        
        if ( count( $matches ) < $min_headings ) {
            return array( 'toc' => '', 'content' => $content );
        }
        
        $toc_items = array();
        $modified_content = $content;
        $offset_adjustment = 0;
        
        foreach ( $matches as $index => $match ) {
            $full_match = $match[0][0];
            $position = $match[0][1];
            
            // 获取标题级别和文本
            if ( $heading_levels === 'h2' ) {
                $level = 2;
                $attrs = $match[1][0];
                $title_text = wp_strip_all_tags( $match[2][0] );
            } else {
                $level = (int) $match[1][0];
                $attrs = $match[2][0];
                $title_text = wp_strip_all_tags( $match[3][0] );
            }
            
            $anchor_id = 'toc-' . $index;
            
            // 检查是否已有 id 属性
            if ( preg_match( '/id=["\']([^"\']+)["\']/i', $attrs, $id_match ) ) {
                $anchor_id = $id_match[1];
                $new_heading = $full_match;
            } else {
                // 添加 id 属性
                $new_heading = preg_replace( 
                    '/<h([2-4])([^>]*)>/i', 
                    '<h$1$2 id="' . $anchor_id . '">', 
                    $full_match 
                );
            }
            
            // 替换内容
            $modified_content = substr_replace( 
                $modified_content, 
                $new_heading, 
                $position + $offset_adjustment, 
                strlen( $full_match ) 
            );
            $offset_adjustment += strlen( $new_heading ) - strlen( $full_match );
            
            $toc_items[] = array(
                'level' => $level,
                'title' => $title_text,
                'anchor' => $anchor_id,
            );
        }
        
        // 生成目录 HTML
        $toc_html = '<nav class="article-toc article-toc--enhanced" id="article-toc" aria-label="' . esc_attr__( '文章目录', 'developer-starter' ) . '">';
        $toc_html .= '<div class="toc-header">';
        $toc_html .= '<span class="toc-title">' . esc_html__( '目录', 'developer-starter' ) . '</span>';
        
        $collapsible = developer_starter_get_option( 'toc_collapsible', '' );
        if ( $collapsible ) {
            $toc_html .= '<button type="button" class="toc-toggle" aria-label="' . esc_attr__( '收起目录', 'developer-starter' ) . '" aria-expanded="true" aria-controls="article-toc-list">';
            $toc_html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><polyline points="6 9 12 15 18 9"></polyline></svg>';
            $toc_html .= '</button>';
        }
        
        $toc_html .= '</div>';
        $toc_html .= '<ul class="toc-list" id="article-toc-list">';
        
        foreach ( $toc_items as $item ) {
            $indent_class = 'toc-level-' . $item['level'];
            $toc_html .= '<li class="toc-item ' . esc_attr( $indent_class ) . '" data-toc-level="' . esc_attr( (string) $item['level'] ) . '">';
            $toc_html .= '<a href="#' . esc_attr( $item['anchor'] ) . '" class="toc-link" data-toc-target="' . esc_attr( $item['anchor'] ) . '">' . esc_html( $item['title'] ) . '</a>';
            $toc_html .= '</li>';
        }
        
        $toc_html .= '</ul>';
        $toc_html .= '</nav>';
        
        return array( 'toc' => $toc_html, 'content' => $modified_content );
    }

    /**
     * 渲染作者信息卡片
     */
    public static function render_author_box() {
        $author_box_enable = developer_starter_get_option( 'author_box_enable', '' );
        if ( ! $author_box_enable ) {
            return '';
        }
        
        $author_id = get_the_author_meta( 'ID' );
        $show_avatar = developer_starter_get_option( 'author_show_avatar', '1' );
        $show_name = developer_starter_get_option( 'author_show_name', '1' );
        $show_bio = developer_starter_get_option( 'author_show_bio', '1' );
        $show_social = developer_starter_get_option( 'author_show_social', '' );
        
        ob_start();
        ?>
        <div class="author-box">
            <?php if ( $show_avatar ) : ?>
                <div class="author-avatar">
                    <?php echo get_avatar( $author_id, 80 ); ?>
                </div>
            <?php endif; ?>
            
            <div class="author-info">
                <?php if ( $show_name ) : ?>
                    <h4 class="author-name"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></h4>
                <?php endif; ?>
                
                <?php if ( $show_bio ) : 
                    $bio = get_the_author_meta( 'description', $author_id );
                    if ( $bio ) :
                ?>
                    <p class="author-bio"><?php echo esc_html( $bio ); ?></p>
                <?php endif; endif; ?>
                
                <?php if ( $show_social ) : ?>
                    <div class="author-social">
                        <?php echo self::render_social_links( $author_id ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 渲染社交链接
     */
    public static function render_social_links( $user_id ) {
        $social_config = array(
            'user_weibo' => array(
                'option' => 'user_social_weibo',
                'label' => '微博',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.739 5.443zM9.05 17.219c-.384.616-1.208.884-1.829.602-.612-.279-.793-.991-.406-1.593.379-.595 1.176-.861 1.793-.601.622.263.82.972.442 1.592zm1.27-1.627c-.141.237-.449.353-.689.253-.236-.09-.313-.361-.177-.586.138-.227.436-.346.672-.24.239.09.315.36.194.573zm.176-2.719c-1.893-.493-4.033.45-4.857 2.118-.836 1.704-.026 3.591 1.886 4.21 1.983.64 4.318-.341 5.132-2.179.8-1.793-.201-3.642-2.161-4.149zm7.563-1.224c-.346-.105-.577-.18-.405-.645.375-1.016.415-1.891.015-2.514-.75-1.167-2.799-1.105-5.089-.03l-.001.001c-.04.015-.08.021-.105.031-.405.15-.313-.195-.313-.195.6-2.266-.014-3.169-.999-3.345-1.995-.367-4.695 2.236-6.14 4.725l.001-.001c-2.109 3.63-.729 8.055 4.064 9.585 4.999 1.593 10.014-.944 10.015-5.016-.002-.824-.372-1.607-1.043-2.596z"/></svg>',
                'type' => 'link',
            ),
            'user_twitter' => array(
                'option' => 'user_social_twitter',
                'label' => 'X',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                'type' => 'link',
            ),
            'user_wechat' => array(
                'option' => 'user_social_wechat',
                'label' => '微信',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-6.656-6.088V8.89c-.135-.01-.27-.027-.406-.033zm-1.091 2.819c.535 0 .969.44.969.983a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.983.97-.983zm4.844 0c.535 0 .969.44.969.983a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.983.969-.983z"/></svg>',
                'type' => 'qrcode',
            ),
            'user_github' => array(
                'option' => 'user_social_github',
                'label' => 'GitHub',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>',
                'type' => 'link',
            ),
            'user_bilibili' => array(
                'option' => 'user_social_bilibili',
                'label' => 'B站',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 0 1-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 0 1 .16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373z"/></svg>',
                'type' => 'link',
            ),
            'user_zhihu' => array(
                'option' => 'user_social_zhihu',
                'label' => '知乎',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M5.721 0C2.251 0 0 2.25 0 5.719V18.28C0 21.751 2.252 24 5.721 24h12.56C21.751 24 24 21.75 24 18.281V5.72C24 2.249 21.75 0 18.281 0zm1.964 4.078c-.271.73-.5 1.434-.68 2.11h4.587c.545-.006.445 1.575.091 1.575h-4.826c-.062.195-.111.39-.16.586 3.019.259 4.436 1.755 4.436 3.705 0 2.467-1.814 3.473-4.181 3.473-1.271 0-2.222-.376-2.222-.376V13.15s.907.344 2.016.344c1.107 0 1.778-.536 1.778-1.341 0-.805-.671-1.453-1.778-1.453-.939 0-1.885.379-2.619 1.136-.291.299-.573.613-.811.932l-1.571-1.018c.251-.465.503-.912.755-1.341H3.93c-.545 0-.445-1.575-.091-1.575h3.544c.209-.597.473-1.32.791-2.109h-3.68c-.544 0-.445-1.575-.09-1.575h4.392l.01-.009c.544-.611 1.485-.611 1.485.271 0 .271-.045.524-.09.758h4.542c.545 0 .445 1.574.091 1.574H7.685zm11.117 11.237s.18-1.826-.09-1.826h-3.064c-.09 0-.18.091-.18.181v4.086c0 1.305-.36 1.755-1.126 1.755-.764 0-1.394-.543-1.394-.543l-.725 1.305s.995 1.033 2.585 1.033c1.622 0 2.841-1.033 2.841-3.064v-4.572h.856c.09 0 .18-.09.18-.181v-.727c0-.09-.09-.18-.18-.18h-1.531V9.509c0-.09-.09-.18-.18-.18h-1.531c-.09 0-.18.09-.18.18v3.337h-.855c-.09 0-.18.09-.18.18v.727c0 .09.09.181.18.181h2.574z"/></svg>',
                'type' => 'link',
            ),
            'user_website' => array(
                'option' => 'user_social_website',
                'label' => '网站',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
                'type' => 'link',
            ),
            'user_linkedin' => array(
                'option' => 'user_social_linkedin',
                'label' => 'LinkedIn',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
                'type' => 'link',
            ),
            'user_youtube' => array(
                'option' => 'user_social_youtube',
                'label' => 'YouTube',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33 2.78 2.78 0 0 0 1.94 2c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.33 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 8.75 15.5 11.75 9.75 14.75 9.75 8.75"></polygon></svg>',
                'type' => 'link',
            ),
            'user_instagram' => array(
                'option' => 'user_social_instagram',
                'label' => 'Instagram',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.5" y2="6.5"></line></svg>',
                'type' => 'link',
            ),
            'user_tiktok' => array(
                'option' => 'user_social_tiktok',
                'label' => 'TikTok',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>',
                'type' => 'link',
            ),
            'user_wechat_mp' => array(
                'option' => 'user_social_wechat_mp',
                'label' => '公众号',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18 0 .65-.52 1.178-1.162 1.178a1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-6.656-6.088V8.89c-.135-.01-.27-.027-.406-.033zm-1.091 2.819c.535 0 .969.44.969.983a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.983.97-.983zm4.844 0c.535 0 .969.44.969.983a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.983.969-.983z"/></svg>',
                'type' => 'qrcode',
            ),
            'user_qq' => array(
                'option' => 'user_social_qq',
                'label' => 'QQ',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
                'type' => 'link',
            ),
            'user_custom' => array(
                'option' => 'user_social_custom',
                'label' => '链接',
                'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 1 0-7l1-1a5 5 0 0 1 7 7l-1 1"></path><path d="M14 11a5 5 0 0 1 0 7l-1 1a5 5 0 0 1-7-7l1-1"></path></svg>',
                'type' => 'link',
            ),
        );
        
        $output = '';
        
        foreach ( $social_config as $meta_key => $config ) {
            // 检查后台是否启用该社交链接
            if ( ! developer_starter_get_option( $config['option'], '' ) ) {
                continue;
            }
            
            $value = get_user_meta( $user_id, $meta_key, true );
            if ( empty( $value ) ) {
                continue;
            }
            
            if ( $config['type'] === 'qrcode' ) {
                // 微信二维码悬停显示
                $output .= '<span class="social-link social-wechat-qr">';
                $output .= $config['icon'];
                $output .= '<span class="social-label">' . esc_html( $config['label'] ) . '</span>';
                $output .= '<span class="wechat-qr-popup"><img src="' . esc_url( $value ) . '" alt="WeChat QR"></span>';
                $output .= '</span>';
            } else {
                $link_url = $value;
                if ( $meta_key === 'user_qq' && preg_match( '/^[0-9]+$/', $value ) ) {
                    $link_url = 'https://wpa.qq.com/msgrd?v=3&uin=' . rawurlencode( $value ) . '&site=qq&menu=yes';
                }
                $output .= '<a href="' . esc_url( $link_url ) . '" class="social-link" target="_blank" rel="noopener">';
                $output .= $config['icon'];
                $output .= '<span class="social-label">' . esc_html( $config['label'] ) . '</span>';
                $output .= '</a>';
            }
        }
        
        return $output;
    }

    /**
     * 渲染版权信息
     */
    public static function render_copyright() {
        $copyright_enable = developer_starter_get_option( 'copyright_enable', '' );
        if ( ! $copyright_enable ) {
            return '';
        }
        
        $content = developer_starter_get_option( 'copyright_content', '' );
        $reprint_notice = developer_starter_get_option( 'copyright_reprint_notice', '' );
        
        // 替换变量
        $replacements = array(
            '{title}' => get_the_title(),
            '{url}' => get_permalink(),
            '{author}' => get_the_author(),
            '{date}' => get_the_date(),
            '{site}' => get_bloginfo( 'name' ),
        );
        
        $content = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
        
        ob_start();
        ?>
        <div class="post-copyright">
            <div class="copyright-icon">
                <span class="copyright-icon-mark" aria-hidden="true">&copy;</span>
            </div>
            <div class="copyright-content">
                <?php if ( $content ) : ?>
                    <p class="copyright-text"><?php echo wp_kses_post( $content ); ?></p>
                <?php else : ?>
                    <p class="copyright-text">
                        <strong><?php echo esc_html( get_the_title() ); ?></strong><br>
                        <?php echo esc_url( get_permalink() ); ?>
                    </p>
                <?php endif; ?>
                
                <?php if ( $reprint_notice ) : ?>
                    <p class="copyright-notice"><?php echo esc_html( $reprint_notice ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * 添加用户社交字段（根据后台开关）
     */
    public function add_user_social_fields( $methods ) {
        // 根据后台设置动态添加字段
        $fields = array(
            'user_social_weibo' => array( 'key' => 'user_weibo', 'label' => __( '微博链接', 'developer-starter' ) ),
            'user_social_twitter' => array( 'key' => 'user_twitter', 'label' => __( 'X (Twitter) 链接', 'developer-starter' ) ),
            'user_social_wechat' => array( 'key' => 'user_wechat', 'label' => __( '微信二维码图片URL', 'developer-starter' ) ),
            'user_social_github' => array( 'key' => 'user_github', 'label' => __( 'GitHub 链接', 'developer-starter' ) ),
            'user_social_bilibili' => array( 'key' => 'user_bilibili', 'label' => __( 'B站链接', 'developer-starter' ) ),
            'user_social_zhihu' => array( 'key' => 'user_zhihu', 'label' => __( '知乎链接', 'developer-starter' ) ),
            'user_social_website' => array( 'key' => 'user_website', 'label' => __( '个人网站', 'developer-starter' ) ),
            'user_social_linkedin' => array( 'key' => 'user_linkedin', 'label' => __( 'LinkedIn 链接', 'developer-starter' ) ),
            'user_social_youtube' => array( 'key' => 'user_youtube', 'label' => __( 'YouTube 链接', 'developer-starter' ) ),
            'user_social_instagram' => array( 'key' => 'user_instagram', 'label' => __( 'Instagram 链接', 'developer-starter' ) ),
            'user_social_tiktok' => array( 'key' => 'user_tiktok', 'label' => __( 'TikTok 链接', 'developer-starter' ) ),
            'user_social_wechat_mp' => array( 'key' => 'user_wechat_mp', 'label' => __( '公众号二维码图片URL', 'developer-starter' ) ),
            'user_social_qq' => array( 'key' => 'user_qq', 'label' => __( 'QQ 号码或链接', 'developer-starter' ) ),
            'user_social_custom' => array( 'key' => 'user_custom', 'label' => __( '自定义链接', 'developer-starter' ) ),
        );
        
        foreach ( $fields as $option_key => $field_data ) {
            if ( developer_starter_get_option( $option_key, '' ) ) {
                $methods[ $field_data['key'] ] = $field_data['label'];
            }
        }
        
        return $methods;
    }
    
    /**
     * 临时存储 [code] 短代码内容
     */
    private $code_shortcode_cache = array();

    /**
     * 在 wpautop 之前保护 [code] 短代码内容
     * 将 [code]...[/code] 替换为占位符，避免 wpautop 插入 <br /> 和 <p> 标签
     */
    public function protect_code_shortcode( $content ) {
        $this->code_shortcode_cache = array();
        
        $content = preg_replace_callback(
            '/\[code([^\]]*)\](.*?)\[\/code\]/is',
            function( $matches ) {
                $key = '<!--CODE_PLACEHOLDER_' . count( $this->code_shortcode_cache ) . '-->';
                $this->code_shortcode_cache[ $key ] = '[code' . $matches[1] . ']' . $matches[2] . '[/code]';
                return $key;
            },
            $content
        );
        
        return $content;
    }

    /**
     * 在 wpautop 之后恢复 [code] 短代码内容
     */
    public function restore_code_shortcode( $content ) {
        if ( ! empty( $this->code_shortcode_cache ) ) {
            $content = str_replace(
                array_keys( $this->code_shortcode_cache ),
                array_values( $this->code_shortcode_cache ),
                $content
            );
            $this->code_shortcode_cache = array();
        }
        return $content;
    }

    /**
     * 代码短代码
     * 用法: [code lang="php"]代码内容[/code]
     */
    public function code_shortcode( $atts, $content = null ) {
        $atts = shortcode_atts( array(
            'lang' => 'markup',
            'line' => '',
        ), $atts, 'code' );
        
        $lang = sanitize_text_field( $atts['lang'] );
        $line_attr = $atts['line'] ? ' data-line="' . esc_attr( $atts['line'] ) . '"' : '';
        
        // 移除 wpautop 可能插入的 <br />, <br>, <p>, </p> 标签
        $code = preg_replace( '/<br\s*\/?>/i', "\n", $content );
        $code = preg_replace( '/<\/?p>/i', '', $code );
        
        // 解码 HTML 实体
        $code = html_entity_decode( $code );
        $code = trim( $code );
        
        return '<pre class="language-' . esc_attr( $lang ) . ' line-numbers"' . $line_attr . '><code class="language-' . esc_attr( $lang ) . '">' . esc_html( $code ) . '</code></pre>';
    }
    
    /**
     * 增强代码块 - 为没有语言类的代码块添加默认类
     */
    public function enhance_code_blocks( $content ) {
        if ( ! is_singular( 'post' ) ) {
            return $content;
        }
        
        $code_highlight_enable = developer_starter_get_option( 'code_highlight_enable', '' );
        if ( ! $code_highlight_enable ) {
            return $content;
        }
        
        // 为没有 language- 类的 pre 标签添加默认类
        $content = preg_replace_callback(
            '/<pre([^>]*)><code([^>]*)>/i',
            function( $matches ) {
                $pre_attrs = $matches[1];
                $code_attrs = $matches[2];
                
                // 检查是否已有 language- 类
                if ( strpos( $code_attrs, 'language-' ) === false && strpos( $pre_attrs, 'language-' ) === false ) {
                    // Gutenberg 编辑器可能使用 wp-block-code 类和 lang-* 或 data-lang
                    if ( preg_match( '/class=["\'][^"\']*lang-(\w+)/', $pre_attrs . $code_attrs, $lang_match ) ) {
                        $lang = $lang_match[1];
                    } elseif ( preg_match( '/data-lang=["\']([\w+-]+)["\']/', $pre_attrs . $code_attrs, $lang_match ) ) {
                        $lang = $lang_match[1];
                    } else {
                        $lang = 'markup';
                    }
                    
                    // 添加 language 类
                    if ( strpos( $pre_attrs, 'class=' ) !== false ) {
                        $pre_attrs = preg_replace( '/class=["\']/', 'class="language-' . $lang . ' ', $pre_attrs );
                    } else {
                        $pre_attrs .= ' class="language-' . $lang . '"';
                    }
                    
                    if ( strpos( $code_attrs, 'class=' ) !== false ) {
                        $code_attrs = preg_replace( '/class=["\']/', 'class="language-' . $lang . ' ', $code_attrs );
                    } else {
                        $code_attrs .= ' class="language-' . $lang . '"';
                    }
                }
                
                return '<pre' . $pre_attrs . '><code' . $code_attrs . '>';
            },
            $content
        );
        
        return $content;
    }

    /**
     * 获取正文样式 CSS 变量
     */
    public static function get_content_style_vars() {
        $width_map = array(
            'narrow' => '680px',
            'standard' => '800px',
            'wide' => '960px',
        );
        
        $font_size_map = array(
            'small' => '16px',
            'medium' => '18px',
            'large' => '20px',
        );
        
        $line_height_map = array(
            'compact' => '1.6',
            'standard' => '1.8',
            'relaxed' => '2.0',
        );
        
        $paragraph_spacing_map = array(
            'small' => '1em',
            'medium' => '1.5em',
            'large' => '2em',
        );
        
        $width_key = developer_starter_get_option( 'post_content_width', 'standard' );
        $font_key = developer_starter_get_option( 'post_font_size', 'medium' );
        $line_key = developer_starter_get_option( 'post_line_height', 'standard' );
        $para_key = developer_starter_get_option( 'post_paragraph_spacing', 'medium' );
        $img_width = developer_starter_get_option( 'post_image_max_width', '100' );
        
        return array(
            '--post-content-width' => isset( $width_map[ $width_key ] ) ? $width_map[ $width_key ] : '800px',
            '--post-font-size' => isset( $font_size_map[ $font_key ] ) ? $font_size_map[ $font_key ] : '18px',
            '--post-line-height' => isset( $line_height_map[ $line_key ] ) ? $line_height_map[ $line_key ] : '1.8',
            '--post-paragraph-spacing' => isset( $paragraph_spacing_map[ $para_key ] ) ? $paragraph_spacing_map[ $para_key ] : '1.5em',
            '--post-image-max-width' => $img_width ? $img_width . '%' : '100%',
        );
    }

    /**
     * 获取单篇文章动态样式变量。
     *
     * @return string
     */
    private static function get_single_post_dynamic_css() {
        $variables = self::get_content_style_vars();
        $options = get_option( 'developer_starter_options', array() );
        $options = is_array( $options ) ? $options : array();

        $variables['--qiling-single-header-bg'] = self::sanitize_css_value(
            isset( $options['post_header_bg_color'] ) ? $options['post_header_bg_color'] : '',
            'var(--color-gray-900)'
        );
        $variables['--qiling-single-title-color'] = self::sanitize_css_value(
            isset( $options['post_header_title_color'] ) ? $options['post_header_title_color'] : '',
            '#fff'
        );
        $variables['--qiling-single-category-color'] = self::sanitize_css_value(
            isset( $options['post_header_category_color'] ) ? $options['post_header_category_color'] : '',
            'var(--color-primary-light)'
        );
        $variables['--qiling-single-meta-color'] = self::sanitize_css_value(
            isset( $options['post_header_meta_color'] ) ? $options['post_header_meta_color'] : '',
            'rgba(255,255,255,0.6)'
        );

        $css = "body.single-post {\n";
        foreach ( $variables as $name => $value ) {
            $css .= '    ' . $name . ': ' . self::sanitize_css_value( $value ) . ";\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * 清理用于 CSS 变量的值。
     *
     * @param mixed  $value 原始值。
     * @param string $fallback 兜底值。
     * @return string
     */
    private static function sanitize_css_value( $value, $fallback = '' ) {
        $value = trim( (string) $value );
        $value = preg_replace( '/[;{}<>\r\n]+/', '', $value );

        if ( ! is_string( $value ) || '' === $value ) {
            return (string) $fallback;
        }

        return $value;
    }
}
