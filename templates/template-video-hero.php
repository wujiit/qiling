<?php
/**
 * Template Name: 视频首屏页面
 * Template Post Type: page
 *
 * 视频首屏展示页面模板
 * 特点：全屏沉浸式视频头部，顶部菜单悬浮透明覆盖在视频之上
 *
 * @package Developer_Starter
 * @since 2.1.2
 */

get_header();

// 获取页面模块
$modules = function_exists( 'developer_starter_get_page_modules_data' )
    ? developer_starter_get_page_modules_data( get_the_ID() )
    : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
$has_modules = ! empty( $modules ) && is_array( $modules );

// 强制覆盖头部样式的 CSS
?>

<style>
/* --- 初始透明状态 (未滚动) --- */
/* 仅在未滚动时强制透明 */
body.page-template-template-video-hero #masthead:not(.video-hero-scrolled) {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100%;
    background: transparent !important;
    box-shadow: none !important;
    border-bottom: none !important;
    z-index: 100;
    transition: background-color 0.3s, box-shadow 0.3s; /* 平滑过渡 */
}

/* 适配 Admin Bar - 初始状态 */
body.admin-bar.page-template-template-video-hero #masthead:not(.video-hero-scrolled) {
    top: 32px !important;
}
@media screen and (max-width: 782px) {
    body.admin-bar.page-template-template-video-hero #masthead:not(.video-hero-scrolled) {
        top: 46px !important;
    }
}

/* 导航链接文字颜色 - 仅在未滚动时设为白色 */
body.page-template-template-video-hero #masthead:not(.video-hero-scrolled) .site-title-link,
body.page-template-template-video-hero #masthead:not(.video-hero-scrolled) .primary-navigation > ul > li > a,
body.page-template-template-video-hero #masthead:not(.video-hero-scrolled) .header-actions button,
body.page-template-template-video-hero #masthead:not(.video-hero-scrolled) .header-actions a {
    color: #ffffff !important;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* --- 滚动后状态 (.video-hero-scrolled) --- */
/* 滚动后恢复固定定位，但使用主题默认背景色逻辑 (通常不需要额外CSS，只需移除覆盖即可) */
/* 保持滚动后过渡稳定，固定使用浅色头部背景。 */
body.page-template-template-video-hero #masthead.video-hero-scrolled {
    position: fixed !important;
    top: 0;
    width: 100%;
    background: #ffffff !important; /* 滚动后变白 */
    color: #333333 !important; /* 文字变黑 */
    box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
    z-index: 100;
    animation: slideDown 0.3s ease-in-out;
}

/* 滚动后 Admin Bar 适配 */
body.admin-bar.page-template-template-video-hero #masthead.video-hero-scrolled {
    top: 32px !important;
}
@media screen and (max-width: 782px) {
    body.admin-bar.page-template-template-video-hero #masthead.video-hero-scrolled {
        top: 46px !important;
    }
}

/* 移除主要内容的顶部内边距，确保视频置顶 */
body.page-template-template-video-hero .site-main {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

/* 如果有页面标题区，隐藏它 */
body.page-template-template-video-hero .page-header {
    display: none !important;
}

@keyframes slideDown {
    from { transform: translateY(-100%); }
    to { transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var header = document.getElementById('masthead');
    if (!header) {
        header = document.querySelector('.site-header'); // Fallback
    }

    var scrolled = false;
    var threshold = 50; // Reduced threshold for quicker response

    function checkScroll() {
        if (!header) return;
        
        if (window.scrollY > threshold) {
            if (!scrolled) {
                header.classList.add('video-hero-scrolled');
                header.classList.add('header-scrolled');
                
                // Force inline styles to ensure visibility over any theme defaults
                header.style.setProperty('background-color', '#ffffff', 'important');
                header.style.setProperty('color', '#333333', 'important');
                header.style.setProperty('box-shadow', '0 2px 10px rgba(0,0,0,0.1)', 'important');
                
                // Handle links color if needed via a helper class on body or just relying on CSS. 
                // The CSS for .video-hero-scrolled should handle children, but we rely on CSS for children.
                
                scrolled = true;
            }
        } else {
            if (scrolled) {
                header.classList.remove('video-hero-scrolled');
                header.classList.remove('header-scrolled');
                
                // Remove inline overrides to let CSS (transparent) take over
                header.style.removeProperty('background-color');
                header.style.removeProperty('color');
                header.style.removeProperty('box-shadow');
                
                scrolled = false;
            }
        }
    }
    
    // Check initially
    checkScroll();
    
    // Add scroll listener
    window.addEventListener('scroll', checkScroll);
});
</script>

<div class="page-template template-video-hero">
    
    <!-- 模块内容区域 -->
    <div id="video-hero-content" class="page-content" style="width: 100%; padding: 0;">
        <?php if ( $has_modules ) : ?>
            <?php 
            // 使用主题自带的模块渲染函数
            if ( class_exists( 'Developer_Starter\Modules\Module_Manager' ) ) {
                $module_manager = Developer_Starter\Modules\Module_Manager::get_instance();
                $module_manager->render_page_modules();
            }
            ?>
        <?php else : ?>
            <!-- 没有模块时显示提示 -->
            <section class="section-padding" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #000; color: #fff;">
                <div class="container text-center">
                    <h2 class="glow-animation"><?php esc_html_e( '正在构建全屏体验...', 'developer-starter' ); ?></h2>
                    <p style="color: #ccc; margin-top: 20px;">
                        <?php esc_html_e( '请在后台保存页面以加载默认演示模块，或手动添加「全屏视频首屏」模块。', 'developer-starter' ); ?>
                    </p>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- 允许页面下方添加常规内容（如果需要） -->
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <?php if ( get_the_content() ) : ?>
            <section class="content-section section-padding">
                <div class="container">
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endwhile; endif; ?>
</div>

<?php
get_footer();
