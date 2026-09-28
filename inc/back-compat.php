<?php
/**
 * 启灵主题 - 低版本 PHP 兼容性守护与自动回滚保护
 *
 * 当当前运行环境低于主题要求的 PHP 8.1.0 时加载此文件。
 * 用于防止因版本不兼容导致的后台卡死、白屏和致命错误（Fatal Error）。
 *
 * @package Developer_Starter
 * @since 2.6.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 在主题被激活时检测 PHP 版本，若不符合要求则自动切回上一主题或默认主题。
 *
 * @param string         $old_name  上一个主题名称。
 * @param WP_Theme|false $old_theme 上一个主题对象。
 * @return void
 */
function developer_starter_switch_theme_compat( $old_name, $old_theme = false ) {
    // 切换回先前的可用主题，如果先前主题不存在，则切换回系统默认主题
    $fallback_theme = ( $old_theme instanceof WP_Theme && $old_theme->exists() ) ? $old_theme->get_stylesheet() : WP_DEFAULT_THEME;
    switch_theme( $fallback_theme );

    // 清除激活成功标记，防止后台误报“主题已启用”
    unset( $_GET['activated'] );

    // 记录临时 transient 标记，确保重定向后依然能显示友好错误提示
    set_transient( 'developer_starter_php_compat_error', 1, 60 );
}
add_action( 'after_switch_theme', 'developer_starter_switch_theme_compat', 1, 2 );

/**
 * 在后台输出环境版本不兼容的友好提示通知。
 *
 * @return void
 */
function developer_starter_upgrade_notice() {
    $current_theme = wp_get_theme();
    $is_qiling     = ( 'qiling' === $current_theme->get_template() || 'qiling' === get_template() );

    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <strong><?php esc_html_e( '【启灵主题】运行环境不满足要求：', 'developer-starter' ); ?></strong>
            <?php
            printf(
                /* translators: 1: Required PHP version, 2: Current PHP version */
                esc_html__( '启灵主题需要 PHP %1$s 或更高版本才能正常运行（当前服务器运行的 PHP 版本为 %2$s）。', 'developer-starter' ),
                '8.1.0',
                PHP_VERSION
            );
            ?>
            <?php if ( $is_qiling ) : ?>
                <?php esc_html_e( '为防止网站后台及核心功能崩溃卡死，主题已自动安全停用并切回可用主题。', 'developer-starter' ); ?>
            <?php endif; ?>
            <?php esc_html_e( '请在主机控制面板（如宝塔面板、cPanel 等）中将 PHP 版本切换至 8.1 或以上后再启用本主题。', 'developer-starter' ); ?>
        </p>
    </div>
    <?php
}

/**
 * 挂载后台告警通知。
 *
 * @return void
 */
function developer_starter_maybe_show_upgrade_notice() {
    if ( get_transient( 'developer_starter_php_compat_error' ) ) {
        delete_transient( 'developer_starter_php_compat_error' );
        developer_starter_upgrade_notice();
        return;
    }

    $current_theme = wp_get_theme();
    if ( 'qiling' === $current_theme->get_template() || 'qiling' === get_template() ) {
        developer_starter_upgrade_notice();
    }
}
add_action( 'admin_notices', 'developer_starter_maybe_show_upgrade_notice' );

/**
 * 阻止低版本环境下的主题自定义器预览。
 *
 * @return void
 */
function developer_starter_customize_compat() {
    wp_die(
        sprintf(
            /* translators: 1: Required PHP version, 2: Current PHP version */
            esc_html__( '启灵主题需要 PHP %1$s 或更高版本。当前服务器版本为 %2$s，请升级 PHP 环境后再试。', 'developer-starter' ),
            '8.1.0',
            PHP_VERSION
        ),
        '',
        array( 'back_link' => true )
    );
}
add_action( 'load-customize.php', 'developer_starter_customize_compat' );

/**
 * 阻止低版本环境下前台直接渲染不完整主题导致报错。
 *
 * @return void
 */
function developer_starter_template_redirect_compat() {
    if ( is_admin() || wp_doing_ajax() ) {
        return;
    }

    $current_theme = wp_get_theme();
    if ( 'qiling' === $current_theme->get_template() || 'qiling' === get_template() ) {
        wp_die(
            sprintf(
                '<h2>%s</h2><p>%s</p>',
                esc_html__( '站点运行环境提示', 'developer-starter' ),
                sprintf(
                    /* translators: 1: Required PHP version, 2: Current PHP version */
                    esc_html__( '当前网站主题需要 PHP %1$s 或更高版本才能正常运行（当前服务器版本为 %2$s）。请网站管理员在主机控制面板中将 PHP 版本切换至 8.1 或以上。', 'developer-starter' ),
                    '8.1.0',
                    PHP_VERSION
                )
            ),
            esc_html__( 'PHP 版本不兼容', 'developer-starter' ),
            array( 'response' => 503 )
        );
    }
}
add_action( 'template_redirect', 'developer_starter_template_redirect_compat' );
