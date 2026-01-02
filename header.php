<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<?php
// 头部设置
$header_bg = developer_starter_get_option( 'header_bg_color', '' );
$header_text_color = developer_starter_get_option( 'header_text_color', '#333333' );
$transparent_home = developer_starter_get_option( 'header_transparent_home', '' );
$hide_search = developer_starter_get_option( 'hide_search_button', '' );
$hide_phone = developer_starter_get_option( 'hide_phone_header', '' );
$show_search = ! $hide_search;
$show_phone = ! $hide_phone;
$primary_color = developer_starter_get_option( 'primary_color', '#2563eb' );

// 电话按钮颜色设置
$phone_bg_transparent = developer_starter_get_option( 'phone_bg_transparent', '' );
$phone_text_transparent = developer_starter_get_option( 'phone_text_transparent', '#ffffff' );
$phone_bg_normal = developer_starter_get_option( 'phone_bg_normal', '' );
$phone_text_normal = developer_starter_get_option( 'phone_text_normal', '#ffffff' );

// 默认值处理
if ( empty( $phone_bg_transparent ) ) {
    $phone_bg_transparent = 'rgba(255,255,255,0.2)';
}
if ( empty( $phone_bg_normal ) ) {
    $phone_bg_normal = "linear-gradient(135deg, {$primary_color} 0%, #7c3aed 100%)";
}

// 确定头部CSS类
$header_classes = array( 'site-header' );
$is_home = is_front_page();

if ( $is_home && $transparent_home ) {
    $header_classes[] = 'header-transparent';
}

// 头部内联样式
$header_style = '';
if ( $header_bg && ! ( $is_home && $transparent_home ) ) {
    if ( strpos( $header_bg, 'gradient' ) !== false ) {
        $header_style = "background: {$header_bg};";
    } else {
        $header_style = "background-color: {$header_bg};";
    }
}
?>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

    <header id="masthead" class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>" style="<?php echo esc_attr( $header_style ); ?>">
        <div class="header-inner">
            <div class="container header-flex">
                <div class="site-branding">
                    <?php 
                    $site_logo = developer_starter_get_option( 'site_logo', '' );
                    if ( $site_logo ) :
                    ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link">
                            <img src="<?php echo esc_url( $site_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo" />
                        </a>
                    <?php elseif ( has_custom_logo() ) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title-link">
                            <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <nav id="site-navigation" class="primary-navigation">
                    <?php
                    if ( has_nav_menu( 'primary' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'primary',
                            'menu_id' => 'primary-menu',
                            'container' => false,
                        ) );
                    }
                    ?>
                </nav>

                <div class="header-actions">
                    <?php if ( $show_search ) : ?>
                        <div class="header-search">
                            <button type="button" class="search-toggle" id="search-toggle" title="<?php esc_attr_e( '搜索', 'developer-starter' ); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    $phone = developer_starter_get_option( 'company_phone', '' );
                    if ( $phone && $show_phone ) : 
                        // 根据透明模式决定初始样式
                        $initial_bg = ( $is_home && $transparent_home ) ? $phone_bg_transparent : $phone_bg_normal;
                        $initial_text = ( $is_home && $transparent_home ) ? $phone_text_transparent : $phone_text_normal;
                        
                        // 构建背景样式
                        $bg_style = strpos( $initial_bg, 'gradient' ) !== false ? "background: {$initial_bg};" : "background: {$initial_bg};";
                        // 清理电话号码用于tel链接
                        $phone_clean = preg_replace( '/[^0-9+]/', '', $phone );
                    ?>
                        <a href="tel:<?php echo esc_attr( $phone_clean ); ?>" class="header-phone" style="<?php echo esc_attr( $bg_style ); ?> color: <?php echo esc_attr( $initial_text ); ?>;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            <span><?php echo esc_html( $phone ); ?></span>
                        </a>
                    <?php endif; ?>

                    <?php 
                    // 顶部登录按钮
                    // 注意：登录用户访问文章页面时缓存已被禁用（见 functions.php）
                    // 因此可以直接使用标准的 is_user_logged_in() 判断
                    $header_login_enable = developer_starter_get_option( 'header_login_enable', '' );
                    if ( $header_login_enable && ! is_user_logged_in() ) :
                        $login_text = developer_starter_get_option( 'header_login_text', '' );
                        $login_text = ! empty( $login_text ) ? $login_text : '登录';
                    ?>
                        <div class="header-login">
                            <button type="button" class="header-login-btn" id="header-login-toggle" title="<?php echo esc_attr( $login_text ); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <span><?php echo esc_html( $login_text ); ?></span>
                            </button>
                        </div>
                    <?php elseif ( $header_login_enable && is_user_logged_in() ) : 
                        $current_user = wp_get_current_user();
                        // 使用 transient 缓存个人中心页面URL避免重复查询
                        $account_url = get_transient( 'developer_starter_account_url' );
                        if ( false === $account_url ) {
                            $account_page = get_pages( array(
                                'meta_key' => '_wp_page_template',
                                'meta_value' => 'templates/template-account.php',
                                'number' => 1,
                            ) );
                            $account_url = ! empty( $account_page ) ? get_permalink( $account_page[0]->ID ) : admin_url( 'profile.php' );
                            set_transient( 'developer_starter_account_url', $account_url, DAY_IN_SECONDS );
                        }
                    ?>
                        <div class="header-user-menu">
                            <a href="<?php echo esc_url( $account_url ); ?>" class="header-user-btn" id="header-user-toggle" title="个人中心">
                                <?php echo get_avatar( $current_user->ID, 32 ); ?>
                            </a>
                            <div class="user-dropdown" id="user-dropdown">
                                <div class="dropdown-header">
                                    <?php echo get_avatar( $current_user->ID, 48 ); ?>
                                    <div class="dropdown-user-info">
                                        <strong><?php echo esc_html( $current_user->display_name ); ?></strong>
                                        <span><?php echo esc_html( $current_user->user_email ); ?></span>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo esc_url( $account_url ); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    个人中心
                                </a>
                                <?php if ( current_user_can( 'read' ) ) : ?>
                                <a href="<?php echo esc_url( admin_url() ); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                    管理后台
                                </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="logout-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    退出登录
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>


                    <?php 
                    // 语言切换功能
                    $translate_enable = developer_starter_get_option( 'translate_enable', '' );
                    $translate_languages = developer_starter_get_option( 'translate_languages', array() );
                    
                    if ( $translate_enable && ! empty( $translate_languages ) ) : 
                    ?>
                        <div class="header-translate">
                            <button type="button" class="translate-toggle" id="translate-toggle" title="语言切换">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="2" y1="12" x2="22" y2="12"/>
                                    <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php 
                    // 暗黑模式切换
                    $darkmode_enable = developer_starter_get_option( 'darkmode_enable', '' );
                    if ( $darkmode_enable ) : 
                    ?>
                        <div class="header-darkmode">
                            <button type="button" class="darkmode-toggle" id="darkmode-toggle" title="切换暗黑模式">
                                <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5"/>
                                    <line x1="12" y1="1" x2="12" y2="3"/>
                                    <line x1="12" y1="21" x2="12" y2="23"/>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                    <line x1="1" y1="12" x2="3" y2="12"/>
                                    <line x1="21" y1="12" x2="23" y2="12"/>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                </svg>
                                <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>

                    <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="<?php esc_attr_e( '菜单', 'developer-starter' ); ?>">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="search-overlay" id="search-overlay">
            <div class="search-overlay-inner">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input type="search" name="s" placeholder="<?php esc_attr_e( '请输入关键词搜索...', 'developer-starter' ); ?>" value="<?php echo get_search_query(); ?>" />
                    <button type="submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </button>
                </form>
                <button type="button" class="search-close" id="search-close">&times;</button>
            </div>
        </div>
        
        <div class="mobile-menu" id="mobile-menu">
            <div class="mobile-menu-header">
                <div class="mobile-menu-logo">
                    <?php 
                    $site_logo = developer_starter_get_option( 'site_logo', '' );
                    if ( $site_logo ) :
                    ?>
                        <img src="<?php echo esc_url( $site_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
                    <?php elseif ( has_custom_logo() ) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <span class="site-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
                    <?php endif; ?>
                </div>
                <button class="mobile-menu-close" id="mobile-menu-close" aria-label="<?php esc_attr_e( '关闭菜单', 'developer-starter' ); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <nav class="mobile-menu-nav">
                <?php
                // 优先使用移动端菜单，如果没有设置则使用主导航菜单
                $mobile_menu_location = has_nav_menu( 'mobile' ) ? 'mobile' : 'primary';
                
                if ( has_nav_menu( $mobile_menu_location ) ) {
                    wp_nav_menu( array(
                        'theme_location' => $mobile_menu_location,
                        'menu_id'        => 'mobile-nav-menu',
                        'container'      => false,
                        'depth'          => 3,
                    ) );
                }
                ?>
            </nav>
            <?php 
            // 移动端底部操作按钮
            $phone = developer_starter_get_option( 'company_phone', '' );
            ?>
            <div class="mobile-menu-footer">
                <?php if ( $phone ) : ?>
                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="mobile-phone-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                        <?php echo esc_html( $phone ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
    </header>

    <main id="primary" class="site-main">

<?php
// 添加透明头部的滚动行为JS
if ( $is_home && $transparent_home ) :
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var header = document.getElementById('masthead');
    var headerPhone = header.querySelector('.header-phone');
    var scrolled = false;
    
    // 颜色配置
    var phoneColors = {
        transparent: {
            bg: '<?php echo esc_js( $phone_bg_transparent ); ?>',
            text: '<?php echo esc_js( $phone_text_transparent ); ?>'
        },
        normal: {
            bg: '<?php echo esc_js( $phone_bg_normal ); ?>',
            text: '<?php echo esc_js( $phone_text_normal ); ?>'
        }
    };
    
    function checkScroll() {
        if (window.scrollY > 100) {
            if (!scrolled) {
                header.classList.add('header-scrolled');
                // 切换到常规模式颜色
                if (headerPhone) {
                    headerPhone.style.background = phoneColors.normal.bg;
                    headerPhone.style.color = phoneColors.normal.text;
                }
                scrolled = true;
            }
        } else {
            if (scrolled) {
                header.classList.remove('header-scrolled');
                // 切换到透明模式颜色
                if (headerPhone) {
                    headerPhone.style.background = phoneColors.transparent.bg;
                    headerPhone.style.color = phoneColors.transparent.text;
                }
                scrolled = false;
            }
        }
    }
    
    window.addEventListener('scroll', checkScroll);
    checkScroll();
});
</script>
<?php endif; ?>
