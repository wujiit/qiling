<?php
/**
 * Qi Ling 主题函数和定义
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 主题常量
 */
define( 'DEVELOPER_STARTER_VERSION', '1.0.5' );
define( 'DEVELOPER_STARTER_DIR', get_template_directory() );
define( 'DEVELOPER_STARTER_URI', get_template_directory_uri() );
define( 'DEVELOPER_STARTER_INC', DEVELOPER_STARTER_DIR . '/inc' );
define( 'DEVELOPER_STARTER_ASSETS', DEVELOPER_STARTER_URI . '/assets' );

/**
 * 根据主题设置切换前台显示语言
 * 必须在加载翻译文件之前执行
 */
function developer_starter_switch_locale( $locale ) {
    // 只在前台切换语言，后台保持WordPress设置
    if ( is_admin() ) {
        return $locale;
    }
    
    // 获取主题语言设置，默认为 zh_CN
    $options = get_option( 'developer_starter_options', array() );
    $theme_language = isset( $options['theme_language'] ) && ! empty( $options['theme_language'] ) 
        ? $options['theme_language'] 
        : 'zh_CN';
    
    return $theme_language;
}
// 优先级设为1，确保在其他操作之前执行
add_filter( 'locale', 'developer_starter_switch_locale', 1 );

/**
 * 加载主题翻译文件
 * 使用init钩子确保locale已经正确切换
 */
function developer_starter_load_textdomain() {
    // 先卸载可能已加载的（错误locale的）翻译
    unload_textdomain( 'developer-starter' );
    
    // 重新加载正确locale的翻译
    $locale = get_locale();
    $mo_file = DEVELOPER_STARTER_DIR . '/languages/developer-starter-' . $locale . '.mo';
    
    if ( file_exists( $mo_file ) ) {
        load_textdomain( 'developer-starter', $mo_file );
    }
}
add_action( 'init', 'developer_starter_load_textdomain', 1 );

/**
 * 核心类
 */
require_once DEVELOPER_STARTER_INC . '/core/class-theme-setup.php';
require_once DEVELOPER_STARTER_INC . '/core/class-assets.php';
require_once DEVELOPER_STARTER_INC . '/core/class-helpers.php';
require_once DEVELOPER_STARTER_INC . '/core/class-message-manager.php';
require_once DEVELOPER_STARTER_INC . '/core/class-smtp-manager.php';
require_once DEVELOPER_STARTER_INC . '/core/class-auth-manager.php';
require_once DEVELOPER_STARTER_INC . '/core/class-faq-manager.php';

/**
 * 后台管理类
 */
require_once DEVELOPER_STARTER_INC . '/admin/class-admin-settings.php';
require_once DEVELOPER_STARTER_INC . '/admin/class-meta-boxes.php';

/**
 * 模块系统
 */
require_once DEVELOPER_STARTER_INC . '/modules/class-module-base.php';
require_once DEVELOPER_STARTER_INC . '/modules/class-module-manager.php';

/**
 * 加载各个模块
 */
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-banner-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-services-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-features-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-clients-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-stats-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-cta-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-image-text-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-columns-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-timeline-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-faq-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-contact-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-news-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-products-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-cases-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-downloads-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-process-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-pricing-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-video-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-testimonials-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-countdown-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-multi-image-text-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-features-list-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-team-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-gallery-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-branches-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-tabs-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-accordion-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-comparison-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-blog-module.php';
require_once DEVELOPER_STARTER_INC . '/modules/modules/class-featured-posts-module.php';


/**
 * 中国特性功能
 */
require_once DEVELOPER_STARTER_INC . '/china/class-china-features.php';

/**
 * SEO功能
 */
require_once DEVELOPER_STARTER_INC . '/seo/class-seo-manager.php';

/**
 * 小工具
 */
require_once DEVELOPER_STARTER_INC . '/widgets/class-widget-contact.php';
require_once DEVELOPER_STARTER_INC . '/widgets/class-widget-social.php';

/**
 * 首页创建器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-homepage-creator.php';

/**
 * 解决方案页面创建器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-solutions-page-creator.php';

/**
 * 落地页创建器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-landing-page-creator.php';

/**
 * 功能清单展示页面创建器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-features-showcase-page-creator.php';

/**
 * 资源下载页面创建器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-resources-page-creator.php';

/**
 * 博客页面创建器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-blog-page-creator.php';

/**
 * 文章增强器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-post-enhancer.php';

/**
 * 菜单保护器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-menu-protector.php';

/**
 * 公告管理器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-announcement-manager.php';

/**
 * 招聘管理
 */
require_once DEVELOPER_STARTER_INC . '/core/class-careers-manager.php';

/**
 * 分类管理器
 */
require_once DEVELOPER_STARTER_INC . '/core/class-category-manager.php';

/**
 * 表单管理系统
 */
require_once DEVELOPER_STARTER_INC . '/forms/class-form-manager.php';
require_once DEVELOPER_STARTER_INC . '/forms/class-form-admin.php';
require_once DEVELOPER_STARTER_INC . '/forms/class-form-handler.php';

/**
 * 注册博客布局侧边栏
 */
function developer_starter_register_blog_sidebar() {
    register_sidebar( array(
        'name'          => __( '博客布局侧边栏', 'developer-starter' ),
        'id'            => 'blog-module-sidebar',
        'description'   => __( '用于博客布局模块的侧边栏小工具区域', 'developer-starter' ),
        'before_widget' => '<div id="%1$s" class="sidebar-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'developer_starter_register_blog_sidebar' );

/**
 * 初始化主题
 */
function developer_starter_init() {
    // 初始化核心类
    new Developer_Starter\Core\Theme_Setup();
    new Developer_Starter\Core\Assets();
    new Developer_Starter\Core\Message_Manager();
    new Developer_Starter\Core\SMTP_Manager();
    
    // 初始化后台管理
    if ( is_admin() ) {
        new Developer_Starter\Admin\Admin_Settings();
        new Developer_Starter\Admin\Meta_Boxes();
    }
    
    // 初始化模块管理器
    Developer_Starter\Modules\Module_Manager::get_instance();
    
    // 初始化中国特性功能
    new Developer_Starter\China\China_Features();
    
    // 初始化SEO
    new Developer_Starter\SEO\SEO_Manager();
    
    // 初始化首页创建器
    new Developer_Starter\Core\Homepage_Creator();
    
    // 初始化解决方案页面创建器
    new Developer_Starter\Core\Solutions_Page_Creator();
    
    // 初始化落地页创建器
    new Developer_Starter\Core\Landing_Page_Creator();
    
    // 初始化功能清单展示页面创建器
    new Developer_Starter\Core\Features_Showcase_Page_Creator();
    
    // 初始化资源下载页面创建器
    new Developer_Starter\Core\Resources_Page_Creator();
    
    // 初始化博客页面创建器
    new Developer_Starter\Core\Blog_Page_Creator();
    
    // 初始化文章增强器
    Developer_Starter\Core\Post_Enhancer::get_instance();
    
    // 初始化菜单保护器
    new Developer_Starter\Core\Menu_Protector();
    
    // 初始化公告管理器
    new Developer_Starter\Core\Announcement_Manager();
    
    // 初始化招聘管理
    new Developer_Starter\Core\Careers_Manager();
    
    // 初始化用户认证
    new Developer_Starter\Core\Auth_Manager();
    
    // 初始化FAQ管理
    new Developer_Starter\Core\FAQ_Manager();
    
    // 初始化分类管理器
    new Developer_Starter\Core\Category_Manager();
    
    // 初始化表单系统
    Developer_Starter\Forms\Form_Manager::get_instance();
    if ( is_admin() ) {
        new Developer_Starter\Forms\Form_Admin();
    }
    new Developer_Starter\Forms\Form_Handler();
}
add_action( 'after_setup_theme', 'developer_starter_init', 5 );

/**
 * 自定义评论回调函数
 * 提前定义以确保在 comments.php 加载前可用
 */
if ( ! function_exists( 'developer_starter_comment_callback' ) ) {
    function developer_starter_comment_callback( $comment, $args, $depth ) {
        $GLOBALS['comment'] = $comment;
        // 用户名脱敏由全局过滤器 get_comment_author 自动处理
        ?>
        <li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'comment-item' ); ?>>
            <article class="comment-body">
                <div class="comment-avatar">
                    <?php echo get_avatar( $comment, 48 ); ?>
                </div>
                <div class="comment-content">
                    <div class="comment-meta">
                        <span class="comment-author"><?php echo esc_html( get_comment_author() ); ?></span>
                        <span class="comment-date"><?php echo get_comment_date(); ?></span>
                        <?php if ( $comment->comment_approved == '0' ) : ?>
                            <span class="comment-awaiting"><?php esc_html_e( '待审核', 'developer-starter' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="comment-text">
                        <?php comment_text(); ?>
                    </div>
                    <div class="comment-actions">
                        <?php
                        comment_reply_link( array_merge( $args, array(
                            'depth'     => $depth,
                            'max_depth' => $args['max_depth'],
                        ) ) );
                        ?>
                    </div>
                </div>
            </article>
        <?php
    }
}

/**
 * 用户名脱敏处理
 */
if ( ! function_exists( 'developer_starter_mask_username' ) ) {
    function developer_starter_mask_username( $name ) {
        $name = trim( $name );
        if ( empty( $name ) ) {
            return $name;
        }
        
        // 获取字符串长度（支持中文）
        $len = mb_strlen( $name, 'UTF-8' );
        
        if ( $len <= 1 ) {
            return $name;
        }
        
        // 取第一个字符
        $first = mb_substr( $name, 0, 1, 'UTF-8' );
        
        // 其余用*代替
        $stars = str_repeat( '*', min( $len - 1, 3 ) );
        
        return $first . $stars;
    }
}

/**
 * 过滤评论作者名（全局脱敏）
 */
add_filter( 'get_comment_author', 'developer_starter_filter_comment_author', 10, 3 );
function developer_starter_filter_comment_author( $author, $comment_id, $comment ) {
    $privacy_enabled = developer_starter_get_option( 'comment_username_privacy', '' );
    if ( $privacy_enabled && ! empty( $author ) ) {
        return developer_starter_mask_username( $author );
    }
    return $author;
}

/**
 * 过滤评论回复链接中的作者名
 */
add_filter( 'comment_reply_link', 'developer_starter_filter_reply_link', 10, 4 );
function developer_starter_filter_reply_link( $link, $args, $comment, $post ) {
    $privacy_enabled = developer_starter_get_option( 'comment_username_privacy', '' );
    if ( $privacy_enabled ) {
        // 获取原始作者名并进行脱敏替换
        $original_author = get_comment_author( $comment );
        // 由于 get_comment_author 已经被过滤，这里不需要再次脱敏
        // 但需要确保回复链接文本中的作者名也被脱敏
    }
    return $link;
}

/**
 * 过滤评论回复标题中的作者名
 */
add_filter( 'comment_form_defaults', 'developer_starter_filter_reply_title', 10, 1 );
function developer_starter_filter_reply_title( $defaults ) {
    $privacy_enabled = developer_starter_get_option( 'comment_username_privacy', '' );
    if ( $privacy_enabled ) {
        // 修改回复标题格式，使用过滤后的作者名
        $defaults['title_reply_to'] = __( '回复 %s', 'developer-starter' );
    }
    return $defaults;
}

/**
 * 主题模板标签函数
 */
require_once DEVELOPER_STARTER_INC . '/template-tags.php';

/**
 * 自定义器扩展
 */
require_once DEVELOPER_STARTER_INC . '/customizer/class-customizer.php';

/**
 * 将国家代码转换为国旗 Emoji
 */
function developer_starter_country_to_flag( $country_code ) {
    $country_code = strtoupper( trim( $country_code ) );
    
    // 如果已经是 emoji（以字节判断）或包含 http，直接返回
    if ( strlen( $country_code ) > 10 || strpos( $country_code, 'HTTP' ) === 0 ) {
        return $country_code;
    }
    
    // 只处理2位国家代码
    if ( strlen( $country_code ) !== 2 ) {
        return $country_code;
    }
    
    // 将国家代码转换为区域指示符号
    // A = 0x1F1E6, B = 0x1F1E7, ... 
    $first = 0x1F1E6 + ord( $country_code[0] ) - ord( 'A' );
    $second = 0x1F1E6 + ord( $country_code[1] ) - ord( 'A' );
    
    return mb_convert_encoding( '&#' . $first . ';&#' . $second . ';', 'UTF-8', 'HTML-ENTITIES' );
}

/**
 * 输出语言切换弹窗到页面底部
 */
function developer_starter_output_translate_modal() {
    $translate_enable = developer_starter_get_option( 'translate_enable', '' );
    $translate_languages = developer_starter_get_option( 'translate_languages', array() );
    
    if ( ! $translate_enable || empty( $translate_languages ) ) {
        return;
    }
    ?>
    <!-- 语言切换弹窗 - Apple风格 -->
    <div class="translate-modal-overlay" id="translate-modal-overlay"></div>
    <div class="translate-modal" id="translate-modal">
        <div class="translate-modal-header">
            <h3>选择语言</h3>
            <button type="button" class="translate-modal-close" id="translate-modal-close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="translate-modal-body">
            <div class="translate-lang-grid">
                <?php foreach ( $translate_languages as $lang ) : 
                    if ( empty( $lang['name'] ) || empty( $lang['code'] ) ) continue;
                ?>
                    <a href="javascript:;" class="translate-lang-item" data-lang="<?php echo esc_attr( $lang['code'] ); ?>">
                        <?php if ( ! empty( $lang['icon'] ) ) : ?>
                            <?php if ( strpos( $lang['icon'], 'http' ) === 0 ) : ?>
                                <img src="<?php echo esc_url( $lang['icon'] ); ?>" alt="" class="lang-icon" />
                            <?php else : ?>
                                <span class="lang-icon-emoji"><?php echo developer_starter_country_to_flag( $lang['icon'] ); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <span class="lang-name"><?php echo esc_html( $lang['name'] ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'developer_starter_output_translate_modal' );

/**
 * 输出顶部登录弹窗到页面底部
 */
function developer_starter_output_login_modal() {
    $header_login_enable = developer_starter_get_option( 'header_login_enable', '' );
    
    // 只有启用了顶部登录按钮且用户未登录时才输出
    // 注意：登录用户访问文章页面时缓存已被禁用（见 functions.php 开头）
    if ( ! $header_login_enable || is_user_logged_in() ) {
        return;
    }
    
    $captcha_enable = developer_starter_get_option( 'auth_captcha_enable', '' );
    $register_page_id = developer_starter_get_option( 'register_page_id', '' );
    $forgot_page_id = developer_starter_get_option( 'forgot_password_page_id', '' );
    ?>
    <!-- 顶部登录弹窗 -->
    <div class="login-modal-overlay" id="login-modal-overlay"></div>
    <div class="login-modal" id="login-modal">
        <div class="login-modal-header">
            <h3>用户登录</h3>
            <button type="button" class="login-modal-close" id="login-modal-close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="login-modal-body">
            <form id="header-login-form" class="login-modal-form" novalidate>
                <div class="modal-form-group">
                    <input type="text" id="header-username" name="username" placeholder="用户名或邮箱" required autocomplete="username" />
                </div>
                <div class="modal-form-group">
                    <input type="password" id="header-password" name="password" placeholder="密码" required autocomplete="current-password" />
                </div>
                
                <?php if ( $captcha_enable ) : ?>
                <div class="modal-form-group">
                    <div class="slider-captcha modal-captcha" id="header-slider-captcha">
                        <div class="captcha-track">
                            <div class="captcha-slider">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                            <div class="captcha-progress"></div>
                            <span class="captcha-text">向右滑动验证</span>
                        </div>
                    </div>
                    <input type="hidden" name="captcha_verified" id="header-captcha-verified" value="false" />
                </div>
                <?php endif; ?>
                
                <div class="modal-form-message" id="header-form-message"></div>
                
                <button type="submit" class="login-modal-submit" id="header-login-submit">
                    <span class="btn-text">登 录</span>
                    <span class="btn-loading" style="display:none">
                        <svg class="spinner" viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                    </span>
                </button>
                
                <?php wp_nonce_field( 'developer_starter_auth', 'header_auth_nonce' ); ?>
            </form>
            
            <div class="login-modal-footer">
                <?php if ( $register_page_id && get_option( 'users_can_register' ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $register_page_id ) ); ?>">注册账号</a>
                <?php endif; ?>
                <?php if ( $forgot_page_id ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $forgot_page_id ) ); ?>">忘记密码？</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var loginBtn = document.getElementById('header-login-toggle');
        var loginModal = document.getElementById('login-modal');
        var loginOverlay = document.getElementById('login-modal-overlay');
        var loginClose = document.getElementById('login-modal-close');
        var loginForm = document.getElementById('header-login-form');
        
        if (!loginBtn || !loginModal) return;
        
        // 打开弹窗
        loginBtn.addEventListener('click', function() {
            loginModal.classList.add('active');
            loginOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        // 关闭弹窗
        function closeModal() {
            loginModal.classList.remove('active');
            loginOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        loginClose.addEventListener('click', closeModal);
        loginOverlay.addEventListener('click', closeModal);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
        
        // 用户菜单下拉
        var userToggle = document.getElementById('header-user-toggle');
        var userDropdown = document.getElementById('user-dropdown');
        if (userToggle && userDropdown) {
            userToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            document.addEventListener('click', function() {
                userDropdown.classList.remove('active');
            });
        }
        
        // 滑动验证码
        var captcha = document.getElementById('header-slider-captcha');
        if (captcha) {
            initHeaderCaptcha(captcha);
        }
        
        // 表单提交
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = document.getElementById('header-login-submit');
            var message = document.getElementById('header-form-message');
            
            var formData = new FormData(loginForm);
            formData.append('action', 'developer_starter_login');
            formData.append('nonce', document.querySelector('[name="header_auth_nonce"]').value);
            
            submitBtn.disabled = true;
            submitBtn.querySelector('.btn-text').style.display = 'none';
            submitBtn.querySelector('.btn-loading').style.display = 'inline-flex';
            
            fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    message.className = 'modal-form-message success';
                    message.textContent = data.data.message;
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    message.className = 'modal-form-message error';
                    message.textContent = data.data.message;
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.btn-text').style.display = 'inline';
                    submitBtn.querySelector('.btn-loading').style.display = 'none';
                }
            })
            .catch(function() {
                message.className = 'modal-form-message error';
                message.textContent = '网络错误，请稍后再试';
                submitBtn.disabled = false;
                submitBtn.querySelector('.btn-text').style.display = 'inline';
                submitBtn.querySelector('.btn-loading').style.display = 'none';
            });
        });
    });
    
    function initHeaderCaptcha(container) {
        var slider = container.querySelector('.captcha-slider');
        var progress = container.querySelector('.captcha-progress');
        var text = container.querySelector('.captcha-text');
        var track = container.querySelector('.captcha-track');
        var verified = document.getElementById('header-captcha-verified');
        var isDragging = false;
        var startX = 0;
        var sliderWidth = slider.offsetWidth;
        var trackWidth = track.offsetWidth - sliderWidth;
        
        function handleStart(e) {
            if (verified.value === 'true') return;
            isDragging = true;
            startX = (e.touches ? e.touches[0].clientX : e.clientX) - slider.offsetLeft;
            slider.style.transition = 'none';
            progress.style.transition = 'none';
        }
        
        function handleMove(e) {
            if (!isDragging) return;
            e.preventDefault();
            var x = (e.touches ? e.touches[0].clientX : e.clientX) - startX;
            x = Math.max(0, Math.min(x, trackWidth));
            slider.style.left = x + 'px';
            progress.style.width = (x + sliderWidth) + 'px';
        }
        
        function handleEnd() {
            if (!isDragging) return;
            isDragging = false;
            slider.style.transition = 'left 0.3s';
            progress.style.transition = 'width 0.3s';
            var x = parseInt(slider.style.left) || 0;
            if (x >= trackWidth - 5) {
                verified.value = 'true';
                container.classList.add('verified');
                text.textContent = '验证成功';
                slider.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
            } else {
                slider.style.left = '0';
                progress.style.width = sliderWidth + 'px';
            }
        }
        
        slider.addEventListener('mousedown', handleStart);
        document.addEventListener('mousemove', handleMove);
        document.addEventListener('mouseup', handleEnd);
        slider.addEventListener('touchstart', handleStart);
        document.addEventListener('touchmove', handleMove, { passive: false });
        document.addEventListener('touchend', handleEnd);
    }
    </script>
    <?php
}
add_action( 'wp_footer', 'developer_starter_output_login_modal' );

/**
 * WordPress 优化功能
 */
function developer_starter_optimizations() {
    // 禁用 Emoji 脚本
    if ( developer_starter_get_option( 'disable_emoji', '' ) ) {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        add_filter( 'tiny_mce_plugins', function( $plugins ) {
            return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
        } );
        add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
            if ( 'dns-prefetch' === $relation_type ) {
                $urls = array_filter( $urls, function( $url ) {
                    return strpos( $url, 'https://s.w.org/images/core/emoji/' ) === false;
                } );
            }
            return $urls;
        }, 10, 2 );
    }

    // 禁用 oEmbed
    if ( developer_starter_get_option( 'disable_embeds', '' ) ) {
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        add_filter( 'embed_oembed_discover', '__return_false' );
        add_filter( 'rewrite_rules_array', function( $rules ) {
            foreach ( $rules as $rule => $rewrite ) {
                if ( strpos( $rewrite, 'embed=true' ) !== false ) {
                    unset( $rules[ $rule ] );
                }
            }
            return $rules;
        } );
    }

    // 禁用 XML-RPC
    if ( developer_starter_get_option( 'disable_xmlrpc', '' ) ) {
        add_filter( 'xmlrpc_enabled', '__return_false' );
        add_filter( 'wp_headers', function( $headers ) {
            unset( $headers['X-Pingback'] );
            return $headers;
        } );
        remove_action( 'wp_head', 'rsd_link' );
    }

    // 隐藏 WordPress 版本号（仅移除HTML中的generator标签，不影响资源文件版本号）
    if ( developer_starter_get_option( 'remove_wp_version', '' ) ) {
        remove_action( 'wp_head', 'wp_generator' );
        add_filter( 'the_generator', '__return_empty_string' );
        // 注意：资源文件版本号的移除由独立选项 remove_assets_version 控制
    }

    // 限制 REST API 访问
    if ( developer_starter_get_option( 'disable_rest_api', '' ) ) {
        add_filter( 'rest_authentication_errors', function( $result ) {
            if ( ! empty( $result ) ) {
                return $result;
            }
            if ( ! is_user_logged_in() ) {
                return new WP_Error( 'rest_not_logged_in', '仅允许登录用户访问 REST API', array( 'status' => 401 ) );
            }
            return $result;
        } );
    }

    // 移除短链接
    if ( developer_starter_get_option( 'remove_shortlink', '' ) ) {
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
        remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
    }

    // 移除 RSD/WLW 链接
    if ( developer_starter_get_option( 'remove_rsd_wlw', '' ) ) {
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
    }

    // 禁用 Pingback/Trackback
    if ( developer_starter_get_option( 'disable_pingback', '' ) ) {
        // 禁用 pingback
        add_filter( 'xmlrpc_methods', function( $methods ) {
            unset( $methods['pingback.ping'] );
            unset( $methods['pingback.extensions.getPingbacks'] );
            return $methods;
        } );
        // 移除 X-Pingback header
        add_filter( 'wp_headers', function( $headers ) {
            unset( $headers['X-Pingback'] );
            return $headers;
        } );
        // 禁用 trackback
        add_filter( 'pings_open', '__return_false', 9999 );
        // 关闭文章的 ping 状态
        add_action( 'pre_ping', function( &$links ) {
            $links = array();
        } );
    }

    // 限制修订版本
    if ( developer_starter_get_option( 'disable_revisions', '' ) ) {
        if ( ! defined( 'WP_POST_REVISIONS' ) ) {
            define( 'WP_POST_REVISIONS', 3 );
        }
    }

    // 禁用 Gutenberg
    if ( developer_starter_get_option( 'disable_gutenberg', '' ) ) {
        add_filter( 'use_block_editor_for_post', '__return_false', 10 );
        add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );
        add_action( 'wp_enqueue_scripts', function() {
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-block-style' );
            wp_dequeue_style( 'global-styles' );
        }, 100 );
    }

    // 禁用区块小工具（恢复经典小工具界面）
    if ( developer_starter_get_option( 'disable_block_widgets', '' ) ) {
        add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
        add_filter( 'use_widgets_block_editor', '__return_false' );
    }

    // ===== 输出优化（Head 清理）=====
    
    // 移除相邻文章链接
    if ( developer_starter_get_option( 'remove_adjacent_posts', '' ) ) {
        remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
    }

    // 移除 Feed 链接
    if ( developer_starter_get_option( 'remove_feed_links', '' ) ) {
        remove_action( 'wp_head', 'feed_links_extra', 3 );
        remove_action( 'wp_head', 'feed_links', 2 );
    }

    // 移除 JSON API 链接
    if ( developer_starter_get_option( 'remove_json_api_link', '' ) ) {
        remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
        remove_action( 'template_redirect', 'rest_output_link_header', 11 );
    }

    // 移除 DNS 预取提示
    if ( developer_starter_get_option( 'remove_dns_prefetch_hints', '' ) ) {
        add_filter( 'wp_resource_hints', function( $hints, $relation_type ) {
            if ( 'dns-prefetch' === $relation_type ) {
                return array();
            }
            return $hints;
        }, 10, 2 );
    }

    // 移除 Gutenberg 样式
    if ( developer_starter_get_option( 'remove_gutenberg_css', '' ) ) {
        add_action( 'wp_enqueue_scripts', function() {
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'classic-theme-styles' );
        }, 999 );
    }

    // 移除全局样式
    if ( developer_starter_get_option( 'remove_global_styles', '' ) ) {
        remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
        remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
    }
}
add_action( 'init', 'developer_starter_optimizations', 1 );

/**
 * 移除资源版本号
 */
function developer_starter_remove_version_query( $src ) {
    if ( strpos( $src, 'ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}

/**
 * 移除所有资源文件的版本号（独立选项）
 */
function developer_starter_remove_assets_version() {
    if ( developer_starter_get_option( 'remove_assets_version', '' ) ) {
        add_filter( 'style_loader_src', 'developer_starter_remove_version_query', 9999 );
        add_filter( 'script_loader_src', 'developer_starter_remove_version_query', 9999 );
    }
}
add_action( 'init', 'developer_starter_remove_assets_version', 1 );

/**
 * HTML 压缩功能
 */
function developer_starter_html_minify_start() {
    if ( ! developer_starter_get_option( 'html_minify', '' ) ) {
        return;
    }
    
    // 不在后台和 AJAX 请求中压缩
    if ( is_admin() || defined( 'DOING_AJAX' ) || defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) ) {
        return;
    }
    
    // 不压缩 feed
    if ( is_feed() ) {
        return;
    }
    
    ob_start( 'developer_starter_html_minify_callback' );
}
add_action( 'template_redirect', 'developer_starter_html_minify_start', 1 );

/**
 * HTML 压缩回调函数
 */
function developer_starter_html_minify_callback( $html ) {
    if ( empty( $html ) ) {
        return $html;
    }
    
    // 保护 script 和 style 标签内容
    $protected = array();
    $index = 0;
    
    // 保护 <script> 内容
    $html = preg_replace_callback( '/<script[^>]*>.*?<\/script>/is', function( $matches ) use ( &$protected, &$index ) {
        $key = '<!--PROTECTED_SCRIPT_' . $index . '-->';
        $protected[$key] = $matches[0];
        $index++;
        return $key;
    }, $html );
    
    // 保护 <style> 内容
    $html = preg_replace_callback( '/<style[^>]*>.*?<\/style>/is', function( $matches ) use ( &$protected, &$index ) {
        $key = '<!--PROTECTED_STYLE_' . $index . '-->';
        $protected[$key] = $matches[0];
        $index++;
        return $key;
    }, $html );
    
    // 保护 <pre> 和 <textarea> 内容
    $html = preg_replace_callback( '/<(pre|textarea)[^>]*>.*?<\/\1>/is', function( $matches ) use ( &$protected, &$index ) {
        $key = '<!--PROTECTED_PRE_' . $index . '-->';
        $protected[$key] = $matches[0];
        $index++;
        return $key;
    }, $html );
    
    // 移除 HTML 注释（保护条件注释）
    $html = preg_replace( '/<!--(?!\[|PROTECTED).*?-->/s', '', $html );
    
    // 移除多余空白（但保留单个空格）
    $html = preg_replace( '/\s+/', ' ', $html );
    
    // 移除标签间的空白
    $html = preg_replace( '/>\s+</', '><', $html );
    
    // 恢复受保护的内容
    foreach ( $protected as $key => $value ) {
        $html = str_replace( $key, $value, $html );
    }
    
    return $html;
}

/**
 * DNS 预解析和预连接
 */
function developer_starter_output_dns_prefetch() {
    // DNS 预解析
    $dns_prefetch = developer_starter_get_option( 'dns_prefetch', '' );
    if ( $dns_prefetch ) {
        $domains = array_filter( array_map( 'trim', explode( "\n", $dns_prefetch ) ) );
        foreach ( $domains as $domain ) {
            $domain = str_replace( array( 'http://', 'https://', '//' ), '', $domain );
            echo '<link rel="dns-prefetch" href="//' . esc_attr( $domain ) . '">' . "\n";
        }
    }
    
    // 预连接
    $preconnect = developer_starter_get_option( 'preconnect_urls', '' );
    if ( $preconnect ) {
        $domains = array_filter( array_map( 'trim', explode( "\n", $preconnect ) ) );
        foreach ( $domains as $domain ) {
            $domain = str_replace( array( 'http://', 'https://', '//' ), '', $domain );
            echo '<link rel="preconnect" href="https://' . esc_attr( $domain ) . '" crossorigin>' . "\n";
        }
    }
}
add_action( 'wp_head', 'developer_starter_output_dns_prefetch', 1 );

/**
 * 心跳控制
 */
function developer_starter_heartbeat_control() {
    $heartbeat = developer_starter_get_option( 'heartbeat_control', '' );
    
    if ( ! $heartbeat ) {
        return;
    }
    
    if ( $heartbeat === 'disable_all' ) {
        wp_deregister_script( 'heartbeat' );
        return;
    }
    
    if ( $heartbeat === 'disable_frontend' && ! is_admin() ) {
        wp_deregister_script( 'heartbeat' );
        return;
    }
    
    if ( is_numeric( $heartbeat ) ) {
        add_filter( 'heartbeat_settings', function( $settings ) use ( $heartbeat ) {
            $settings['interval'] = intval( $heartbeat );
            return $settings;
        } );
    }
}
add_action( 'init', 'developer_starter_heartbeat_control', 1 );

/**
 * 安全增强功能
 */
function developer_starter_security_enhancements() {
    // 禁用作者存档页
    if ( developer_starter_get_option( 'disable_author_archive', '' ) ) {
        add_action( 'template_redirect', function() {
            if ( is_author() ) {
                wp_redirect( home_url(), 301 );
                exit;
            }
        } );
        
        // 阻止 ?author=1 查询
        add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
            if ( preg_match( '/\?author=([0-9]*)/', $requested_url ) ) {
                return home_url();
            }
            return $redirect_url;
        }, 10, 2 );
    }
    
    // 禁用文件编辑器
    if ( developer_starter_get_option( 'disable_file_edit', '' ) ) {
        if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
            define( 'DISALLOW_FILE_EDIT', true );
        }
    }
    
    // 隐藏登录错误信息
    if ( developer_starter_get_option( 'login_error_hide', '' ) ) {
        add_filter( 'login_errors', function() {
            return __( '用户名或密码错误，请重试。', 'developer-starter' );
        } );
    }
}
add_action( 'init', 'developer_starter_security_enhancements', 1 );

/**
 * 内容保护（禁用右键/选择）
 */
function developer_starter_content_protection() {
    // 仅对未登录用户生效
    if ( is_user_logged_in() ) {
        return;
    }
    
    $disable_right_click = developer_starter_get_option( 'disable_right_click', '' );
    $disable_text_select = developer_starter_get_option( 'disable_text_select', '' );
    
    if ( ! $disable_right_click && ! $disable_text_select ) {
        return;
    }
    
    add_action( 'wp_footer', function() use ( $disable_right_click, $disable_text_select ) {
        echo '<script>';
        if ( $disable_right_click ) {
            echo 'document.addEventListener("contextmenu",function(e){e.preventDefault();});';
        }
        if ( $disable_text_select ) {
            echo 'document.addEventListener("selectstart",function(e){e.preventDefault();});';
            echo 'document.body.style.userSelect="none";document.body.style.webkitUserSelect="none";';
        }
        echo '</script>';
    }, 999 );
}
add_action( 'wp', 'developer_starter_content_protection' );

/**
 * 评论优化
 */
function developer_starter_comment_optimizations() {
    // 完全禁用评论
    if ( developer_starter_get_option( 'disable_comments', '' ) ) {
        // 关闭评论支持
        add_action( 'admin_init', function() {
            $post_types = get_post_types();
            foreach ( $post_types as $post_type ) {
                if ( post_type_supports( $post_type, 'comments' ) ) {
                    remove_post_type_support( $post_type, 'comments' );
                    remove_post_type_support( $post_type, 'trackbacks' );
                }
            }
        } );
        
        // 关闭评论计数
        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );
        
        // 隐藏评论菜单
        add_action( 'admin_menu', function() {
            remove_menu_page( 'edit-comments.php' );
        } );
        
        // 从管理栏移除评论
        add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
            $wp_admin_bar->remove_node( 'comments' );
        }, 999 );
    }
    
    // 评论蜜罐
    if ( developer_starter_get_option( 'comment_honeypot', '' ) ) {
        // 添加隐藏字段
        add_action( 'comment_form', function() {
            echo '<p style="display:none !important;"><label>Leave this empty: <input type="text" name="ds_hp_field" value="" autocomplete="off" /></label></p>';
        } );
        
        // 检查蜜罐字段
        add_filter( 'preprocess_comment', function( $commentdata ) {
            if ( ! empty( $_POST['ds_hp_field'] ) ) {
                wp_die( __( '垃圾评论检测：提交被阻止。', 'developer-starter' ), 403 );
            }
            return $commentdata;
        } );
    }
}
add_action( 'init', 'developer_starter_comment_optimizations', 1 );

/**
 * 数据库优化
 */
function developer_starter_database_optimizations() {
    // 自动清空回收站 7 天
    if ( developer_starter_get_option( 'auto_clean_trash', '' ) ) {
        if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
            define( 'EMPTY_TRASH_DAYS', 7 );
        }
    }
    
    // 自动清理旧修订版本（每周执行）
    if ( developer_starter_get_option( 'auto_clean_revisions', '' ) ) {
        if ( ! wp_next_scheduled( 'developer_starter_clean_revisions' ) ) {
            wp_schedule_event( time(), 'weekly', 'developer_starter_clean_revisions' );
        }
    } else {
        wp_clear_scheduled_hook( 'developer_starter_clean_revisions' );
    }
}
add_action( 'init', 'developer_starter_database_optimizations', 1 );

/**
 * 执行修订版本清理
 */
function developer_starter_do_clean_revisions() {
    global $wpdb;
    
    // 删除 30 天前的修订版本
    $date_threshold = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
    
    $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_modified < %s",
        $date_threshold
    ) );
    
    // 清理孤立的 postmeta
    $wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" );
}
add_action( 'developer_starter_clean_revisions', 'developer_starter_do_clean_revisions' );

/**
 * 图片优化功能
 */
function developer_starter_image_optimizations() {
    // 禁用大图压缩阈值（WordPress 5.3+ 默认会缩放大于 2560px 的图片）
    if ( developer_starter_get_option( 'disable_default_thumbnails', '' ) ) {
        add_filter( 'big_image_size_threshold', '__return_false' );
    }
    
    // 禁用多尺寸缩略图生成（节省服务器空间）
    if ( developer_starter_get_option( 'disable_image_sizes', '' ) ) {
        // 禁用所有中间尺寸
        add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
        add_filter( 'intermediate_image_sizes', '__return_empty_array' );
        
        // 移除默认图片尺寸
        add_action( 'init', function() {
            remove_image_size( 'thumbnail' );
            remove_image_size( 'medium' );
            remove_image_size( 'medium_large' );
            remove_image_size( 'large' );
            remove_image_size( '1536x1536' );
            remove_image_size( '2048x2048' );
        } );
    }
}
add_action( 'after_setup_theme', 'developer_starter_image_optimizations', 999 );

/**
 * 博客页面分页支持
 * 只为使用博客模板的静态页面添加分页规则
 */
function developer_starter_blog_page_pagination_support() {
    // 查找所有使用博客模板的页面
    $blog_pages = get_posts( array(
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'templates/template-blog.php',
        'fields'         => 'ids',
    ) );
    
    // 为每个博客页面添加分页规则
    foreach ( $blog_pages as $page_id ) {
        $page = get_post( $page_id );
        if ( $page ) {
            $slug = $page->post_name;
            // 添加特定页面的分页规则
            add_rewrite_rule( 
                $slug . '/page/?([0-9]{1,})/?$', 
                'index.php?pagename=' . $slug . '&paged=$matches[1]', 
                'top' 
            );
        }
    }
}
add_action( 'init', 'developer_starter_blog_page_pagination_support', 1 );

/**
 * 当博客模板页面保存时刷新重写规则
 */
function developer_starter_flush_blog_page_rules( $post_id ) {
    if ( get_post_type( $post_id ) !== 'page' ) {
        return;
    }
    
    $template = get_post_meta( $post_id, '_wp_page_template', true );
    if ( $template === 'templates/template-blog.php' ) {
        // 标记需要刷新重写规则
        update_option( 'developer_starter_flush_rules', '1' );
    }
}
add_action( 'save_post', 'developer_starter_flush_blog_page_rules' );

/**
 * 延迟刷新重写规则
 */
function developer_starter_delayed_flush_rules() {
    if ( get_option( 'developer_starter_flush_rules' ) === '1' ) {
        flush_rewrite_rules();
        delete_option( 'developer_starter_flush_rules' );
    }
}
add_action( 'init', 'developer_starter_delayed_flush_rules', 999 );

/**
 * 分类链接去除 category 前缀
 * 基于 No Category Base (WPML) 插件实现
 */
function developer_starter_remove_category_base_init() {
    if ( ! developer_starter_get_option( 'remove_category_base', '' ) ) {
        return;
    }
    
    global $wp_rewrite;
    
    // 修改分类链接结构
    $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
}
add_action( 'init', 'developer_starter_remove_category_base_init', 1 );

/**
 * 分类重写规则 - 参考 No Category Base 插件
 */
function developer_starter_category_rewrite_rules( $category_rewrite ) {
    if ( ! developer_starter_get_option( 'remove_category_base', '' ) ) {
        return $category_rewrite;
    }
    
    global $wp_rewrite;
    $category_rewrite = array();
    
    $categories = get_categories( array( 'hide_empty' => false ) );
    
    foreach ( $categories as $category ) {
        $category_nicename = $category->slug;
        
        // 防止无限循环
        if ( $category->parent == $category->cat_ID ) {
            $category->parent = 0;
        } elseif ( $category->parent != 0 ) {
            $category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;
        }
        
        // 添加重写规则（顺序很重要）
        $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
        $category_rewrite['(' . $category_nicename . ')/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
        $category_rewrite['(' . $category_nicename . ')/?$'] = 'index.php?category_name=$matches[1]';
    }
    
    // 重定向旧的 category 链接（301 重定向）
    $old_category_base = get_option( 'category_base' ) ? get_option( 'category_base' ) : 'category';
    $old_category_base = trim( $old_category_base, '/' );
    $category_rewrite[ $old_category_base . '/(.*)$' ] = 'index.php?category_redirect=$matches[1]';
    
    return $category_rewrite;
}
add_filter( 'category_rewrite_rules', 'developer_starter_category_rewrite_rules' );

/**
 * 添加 category_redirect 查询变量
 */
function developer_starter_category_query_vars( $public_query_vars ) {
    if ( developer_starter_get_option( 'remove_category_base', '' ) ) {
        $public_query_vars[] = 'category_redirect';
    }
    return $public_query_vars;
}
add_filter( 'query_vars', 'developer_starter_category_query_vars' );

/**
 * 处理旧 category 链接的 301 重定向
 */
function developer_starter_category_redirect( $query_vars ) {
    if ( isset( $query_vars['category_redirect'] ) ) {
        $catlink = trailingslashit( get_option( 'home' ) ) . user_trailingslashit( $query_vars['category_redirect'], 'category' );
        status_header( 301 );
        header( 'Location: ' . $catlink );
        exit();
    }
    return $query_vars;
}
add_filter( 'request', 'developer_starter_category_redirect' );

/**
 * 分类创建/编辑/删除时刷新规则
 */
function developer_starter_refresh_category_rules() {
    if ( developer_starter_get_option( 'remove_category_base', '' ) ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
add_action( 'created_category', 'developer_starter_refresh_category_rules' );
add_action( 'delete_category', 'developer_starter_refresh_category_rules' );
add_action( 'edited_category', 'developer_starter_refresh_category_rules' );

/**
 * 保存选项时刷新固定链接规则
 */
function developer_starter_flush_rewrite_on_save( $old_value, $new_value ) {
    // 检查分类链接设置是否改变
    $old_cat = isset( $old_value['remove_category_base'] ) ? $old_value['remove_category_base'] : '';
    $new_cat = isset( $new_value['remove_category_base'] ) ? $new_value['remove_category_base'] : '';
    
    if ( $old_cat !== $new_cat ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
add_action( 'update_option_developer_starter_options', 'developer_starter_flush_rewrite_on_save', 10, 2 );

/**
 * 为非关键JS添加defer属性，加速首屏渲染
 */
function developer_starter_defer_scripts( $tag, $handle, $src ) {
    // 仅在前端生效
    if ( is_admin() ) {
        return $tag;
    }
    
    // 需要defer的脚本
    $defer_scripts = array(
        'developer-starter-footer-effects',
        'translate-js',
        'comment-reply',
    );
    
    if ( in_array( $handle, $defer_scripts, true ) ) {
        $tag = str_replace( ' src=', ' defer src=', $tag );
    }
    
    return $tag;
}
add_filter( 'script_loader_tag', 'developer_starter_defer_scripts', 10, 3 );

/**
 * 页面/选项更新时清除相关缓存
 */
function developer_starter_clear_theme_cache( $post_id = null ) {
    // 清除账户页面URL缓存
    delete_transient( 'developer_starter_account_url' );
}
// 页面更新时清除
add_action( 'save_post_page', 'developer_starter_clear_theme_cache' );
// 主题选项更新时清除
add_action( 'update_option_developer_starter_options', 'developer_starter_clear_theme_cache' );

/**
 * 清除选项缓存（当选项更新时重置静态变量）
 */
function developer_starter_reset_options_cache() {
    // 由于使用静态变量，需要在同一请求中手动处理
    // 此钩子主要用于确保下次请求时获取新值
}
add_action( 'update_option_developer_starter_options', 'developer_starter_reset_options_cache' );

/**
 * 开发调试模式 - 在前台底部显示调试信息（仅管理员可见）
 */
function developer_starter_debug_output() {
    // 检查是否启用调试模式
    if ( ! developer_starter_get_option( 'debug_mode', '' ) ) {
        return;
    }
    
    // 不在后台显示
    if ( is_admin() ) {
        return;
    }
    
    global $wpdb;
    
    // 计算页面加载时间
    $load_time = timer_stop( 0, 4 );
    
    // 数据库查询次数
    $query_count = get_num_queries();
    
    // 内存使用
    $memory_usage = size_format( memory_get_peak_usage( true ) );
    
    // 检测对象缓存
    $object_cache = wp_using_ext_object_cache() ? '✅ 已启用' : '❌ 未启用';
    $cache_type = wp_using_ext_object_cache() ? '（Redis/Memcached）' : '（使用数据库）';
    
    // 检测页面缓存
    $page_cache = defined( 'WP_CACHE' ) && WP_CACHE ? '✅ 已启用' : '❌ 未启用';
    
    // PHP版本
    $php_version = phpversion();
    
    // WordPress版本
    $wp_version = get_bloginfo( 'version' );
    
    // 主题版本
    $theme_version = DEVELOPER_STARTER_VERSION;
    
    ?>
    <div id="developer-debug-bar" style="
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #e2e8f0;
        font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
        font-size: 12px;
        z-index: 99999;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
    ">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 20px;">
            <div style="display: flex; align-items: center; gap: 25px; flex-wrap: wrap;">
                <span style="color: #f59e0b; font-weight: 600;">🛠️ 调试模式</span>
                
                <span title="SQL查询次数">
                    <span style="color: #64748b;">SQL</span>
                    <span style="color: <?php echo $query_count > 50 ? '#ef4444' : ($query_count > 20 ? '#f59e0b' : '#10b981'); ?>; font-weight: 600; margin-left: 5px;">
                        <?php echo $query_count; ?>
                    </span>
                </span>
                
                <span title="页面加载时间">
                    <span style="color: #64748b;">加载</span>
                    <span style="color: <?php echo $load_time > 1 ? '#ef4444' : ($load_time > 0.5 ? '#f59e0b' : '#10b981'); ?>; font-weight: 600; margin-left: 5px;">
                        <?php echo $load_time; ?>s
                    </span>
                </span>
                
                <span title="内存峰值">
                    <span style="color: #64748b;">内存</span>
                    <span style="color: #8b5cf6; font-weight: 600; margin-left: 5px;"><?php echo $memory_usage; ?></span>
                </span>
                
                <span title="对象缓存状态">
                    <span style="color: #64748b;">对象缓存</span>
                    <span style="margin-left: 5px;"><?php echo $object_cache; ?></span>
                    <span style="color: #64748b; font-size: 10px;"><?php echo $cache_type; ?></span>
                </span>
                
                <span title="页面缓存状态">
                    <span style="color: #64748b;">页面缓存</span>
                    <span style="margin-left: 5px;"><?php echo $page_cache; ?></span>
                </span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: #64748b; font-size: 10px;">
                    PHP <?php echo $php_version; ?> | WP <?php echo $wp_version; ?> | 主题 <?php echo $theme_version; ?>
                </span>
                <button onclick="this.parentElement.parentElement.parentElement.style.display='none'" style="
                    background: #334155;
                    border: none;
                    color: #94a3b8;
                    padding: 4px 10px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 11px;
                ">关闭</button>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'developer_starter_debug_output', 999 );

/**
 * 自定义用户头像系统
 * 替代WordPress默认的Gravatar头像服务
 * 
 * 优先级：用户上传的头像 > 后台设置的默认头像 > WordPress默认
 */
function developer_starter_custom_avatar_url( $url, $id_or_email, $args ) {
    // 获取用户ID
    $user_id = 0;
    if ( is_numeric( $id_or_email ) ) {
        $user_id = (int) $id_or_email;
    } elseif ( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->user_id ) ) {
            $user_id = (int) $id_or_email->user_id;
        }
    } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        if ( $user ) {
            $user_id = $user->ID;
        }
    }
    
    // 1. 检查用户是否有自定义头像
    if ( $user_id > 0 ) {
        $custom_avatar = get_user_meta( $user_id, 'custom_avatar', true );
        if ( ! empty( $custom_avatar ) ) {
            return $custom_avatar;
        }
    }
    
    // 2. 检查后台是否设置了默认头像
    $default_avatar = developer_starter_get_option( 'default_avatar', '' );
    if ( ! empty( $default_avatar ) ) {
        return $default_avatar;
    }
    
    // 3. 回退到默认
    return $url;
}
add_filter( 'get_avatar_url', 'developer_starter_custom_avatar_url', 10, 3 );

/**
 * 获取用户自定义头像URL
 * 用于直接获取头像地址
 */
function developer_starter_get_user_avatar_url( $user_id, $size = 96 ) {
    // 检查用户自定义头像
    $custom_avatar = get_user_meta( $user_id, 'custom_avatar', true );
    if ( ! empty( $custom_avatar ) ) {
        return $custom_avatar;
    }
    
    // 检查默认头像设置
    $default_avatar = developer_starter_get_option( 'default_avatar', '' );
    if ( ! empty( $default_avatar ) ) {
        return $default_avatar;
    }
    
    // 回退到WordPress默认
    return get_avatar_url( $user_id, array( 'size' => $size ) );
}
