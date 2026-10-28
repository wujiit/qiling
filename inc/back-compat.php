<?php
/**
 * 启灵主题 - 低版本与存在已知安全漏洞 PHP 兼容性守护与自动回滚保护
 *
 * 当当前运行环境低于主题要求的安全基线（未修补 PHP2Shell 核心高危漏洞 CVE-2026-109618 / CVE-2026-109619）时加载此文件。
 * 用于防止因版本不兼容导致的后台卡死、白屏以及服务器面临未授权远程代码执行（RCE）被黑风险。
 *
 * @package Developer_Starter
 * @since 2.6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 根据当前运行环境的 PHP 版本，获取建议升级的目标安全补丁版本描述。
 *
 * @return string
 */
function developer_starter_get_required_php_version_desc() {
    $version = PHP_VERSION;
    if ( version_compare( $version, '8.2.0', '<' ) ) {
        return '8.2.35+（或 8.3.36+ / 8.4.27+）';
    }
    if ( version_compare( $version, '8.3.0', '<' ) ) {
        return '8.2.35+';
    }
    if ( version_compare( $version, '8.4.0', '<' ) ) {
        return '8.3.36+';
    }
    if ( version_compare( $version, '8.5.0', '<' ) ) {
        return '8.4.27+';
    }
    if ( version_compare( $version, '8.6.0', '<' ) ) {
        return '8.5.12+';
    }
    return '8.2.35+';
}

/**
 * 在主题被激活时检测 PHP 版本，若不符合安全要求则自动切回上一主题或默认主题。
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
 * 在后台输出环境版本不兼容或存在高危核心漏洞的友好提示通知。
 *
 * @return void
 */
function developer_starter_upgrade_notice() {
    $current_theme = wp_get_theme();
    $is_qiling     = ( 'qiling' === $current_theme->get_template() || 'qiling' === get_template() );
    $required_ver  = developer_starter_get_required_php_version_desc();

    ?>
    <div class="notice notice-error is-dismissible" style="border-left-color: #dc2626;">
        <p>
            <strong style="color: #dc2626;"><?php esc_html_e( '【启灵主题】运行环境不满足核心安全要求：', 'qiling' ); ?></strong>
            <?php
            printf(
                /* translators: 1: Current PHP version, 2: Required secure PHP version */
                esc_html__( '检测到当前服务器 PHP 版本为 %1$s，未达到防范 PHP 核心高危漏洞（PHP2Shell: CVE-2026-109618 / CVE-2026-109619，CVSS 9.2~9.8 远程代码执行）的最低安全补丁版本要求。启灵主题要求最低运行版本为 PHP %2$s。', 'qiling' ),
                PHP_VERSION,
                $required_ver
            );
            ?>
            <?php if ( $is_qiling ) : ?>
                <?php esc_html_e( '为防止网站后台瘫痪及服务器面临严重被黑风险，主题已自动安全停用并切回可用主题。', 'qiling' ); ?>
            <?php endif; ?>
            <?php esc_html_e( '请立即在主机控制面板（如宝塔面板、1Panel、cPanel 等）中将 PHP 升级至 8.2.35 / 8.3.36 或以上安全补丁版本后再启用本主题。', 'qiling' ); ?>
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
 * 阻止低版本或漏洞环境下的主题自定义器预览。
 *
 * @return void
 */
function developer_starter_customize_compat() {
    $required_ver = developer_starter_get_required_php_version_desc();
    wp_die(
        sprintf(
            /* translators: 1: Required PHP version, 2: Current PHP version */
            esc_html__( '启灵主题需要 PHP %1$s 安全版本（防范 PHP2Shell 核心 RCE 高危漏洞）。当前服务器版本为 %2$s，请升级 PHP 环境后再试。', 'qiling' ),
            $required_ver,
            PHP_VERSION
        ),
        '',
        array( 'back_link' => true )
    );
}
add_action( 'load-customize.php', 'developer_starter_customize_compat' );

/**
 * 阻止低版本或漏洞环境下前台直接渲染不完整主题导致报错。
 *
 * @return void
 */
function developer_starter_template_redirect_compat() {
    if ( is_admin() || wp_doing_ajax() ) {
        return;
    }

    $current_theme = wp_get_theme();
    if ( 'qiling' === $current_theme->get_template() || 'qiling' === get_template() ) {
        $required_ver = developer_starter_get_required_php_version_desc();
        wp_die(
            sprintf(
                '<h2>%s</h2><p>%s</p><p style="color:#64748b;font-size:13px;line-height:1.6;margin-top:12px;">%s</p>',
                esc_html__( '站点核心安全防护拦截', 'qiling' ),
                sprintf(
                    /* translators: 1: Required PHP version, 2: Current PHP version */
                    esc_html__( '当前网站主题需要 PHP %1$s 安全版本才能运行（当前服务器版本为 %2$s）。请网站管理员在主机控制面板中将 PHP 升级至最新安全补丁版本（PHP 8.2.35+ / 8.3.36+）。', 'qiling' ),
                    $required_ver,
                    PHP_VERSION
                ),
                esc_html__( '安全背景说明：针对近期披露的 PHP 核心高危漏洞（PHP2Shell: CVE-2026-109618 / CVE-2026-109619，CVSS 9.2~9.8 严重未授权远程代码执行），启灵主题已开启主动安全守护，防止未打补丁的服务器被黑客提权入侵挂马。', 'qiling' )
            ),
            esc_html__( 'PHP 核心安全补丁版本不兼容', 'qiling' ),
            array( 'response' => 503 )
        );
    }
}
add_action( 'template_redirect', 'developer_starter_template_redirect_compat' );
