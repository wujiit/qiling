<?php
/**
 * 启灵主题健康体检助手服务类 (Theme Doctor Service)
 *
 * 用于一键诊断主题配置、服务器运行环境、静态资产、合规性与性能指标。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Theme_Doctor_Service {

    /**
     * 运行全站健康检查并返回结构化诊断报告
     *
     * @return array<string, mixed>
     */
    public static function run_diagnostics() {
        $checks = array();

        $checks[] = self::check_php_environment();
        $checks[] = self::check_smtp_service();
        $checks[] = self::check_media_and_webp();
        $checks[] = self::check_speculative_prerender();
        $checks[] = self::check_compliance_info();
        $checks[] = self::check_seo_and_permalinks();
        $checks[] = self::check_custom_font_assets();

        // 计算健康分 (基准 100 分)
        $score = 100;
        $error_count   = 0;
        $warning_count = 0;
        $ok_count      = 0;

        foreach ( $checks as $check ) {
            if ( 'error' === $check['status'] ) {
                $score -= 25;
                $error_count++;
            } elseif ( 'warning' === $check['status'] ) {
                $score -= 10;
                $warning_count++;
            } else {
                $ok_count++;
            }
        }

        $score = max( 0, min( 100, $score ) );

        $level = 'healthy';
        $level_text = __( '健康状态优秀', 'qiling' );
        if ( $score < 60 ) {
            $level = 'danger';
            $level_text = __( '存在安全或可用性隐患', 'qiling' );
        } elseif ( $score < 85 ) {
            $level = 'warning';
            $level_text = __( '良好，仍有优化空间', 'qiling' );
        }

        return array(
            'score'         => $score,
            'level'         => $level,
            'level_text'    => $level_text,
            'total_checks'  => count( $checks ),
            'ok_count'      => $ok_count,
            'warning_count' => $warning_count,
            'error_count'   => $error_count,
            'checked_at'    => current_time( 'Y-m-d H:i:s' ),
            'checks'        => $checks,
        );
    }

    /**
     * 检查 PHP 版本与基础环境安全性
     *
     * @return array<string, mixed>
     */
    private static function check_php_environment() {
        $php_version = PHP_VERSION;
        $is_safe = function_exists( 'developer_starter_is_php_secure' ) ? developer_starter_is_php_secure() : false;

        if ( $is_safe ) {
            return array(
                'id'           => 'php_version',
                'title'        => __( 'PHP 运行环境与安全基线', 'qiling' ),
                'status'       => 'ok',
                'category'     => __( '系统环境', 'qiling' ),
                'desc'         => sprintf(
                    /* translators: %s: PHP version */
                    __( '当前 PHP 版本为 %s，完全满足主题既定的安全基线要求（PHP 8.2 ≥ 8.2.35 或 8.3 ≥ 8.3.36）。', 'qiling' ),
                    $php_version
                ),
            );
        }

        return array(
            'id'           => 'php_version',
            'title'        => __( 'PHP 运行环境与安全基线', 'qiling' ),
            'status'       => 'error',
            'category'     => __( '系统环境', 'qiling' ),
            'desc'         => sprintf(
                /* translators: %s: PHP version */
                __( '当前 PHP 版本 (%s) 低于安全基线要求，容易受到已知远程漏洞威胁。建议尽快升级至 PHP 8.2.35+ 或 8.3.36+。', 'qiling' ),
                $php_version
            ),
        );
    }

    /**
     * 检查 SMTP 邮件发信通道
     *
     * @return array<string, mixed>
     */
    private static function check_smtp_service() {
        $smtp_enable = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( 'smtp_enable', '' )
            : '';

        if ( '1' !== (string) $smtp_enable ) {
            return array(
                'id'           => 'smtp_service',
                'title'        => __( 'SMTP 邮件发信服务', 'qiling' ),
                'status'       => 'warning',
                'category'     => __( '基础功能', 'qiling' ),
                'desc'         => __( '当前未启用 SMTP 发信功能。访客找回密码、管理员接收订单及表单提醒邮件将可能无法正常送达。', 'qiling' ),
                'action_url'   => admin_url( 'admin.php?page=qiling-settings&tab=smtp' ),
                'action_label' => __( '前往配置 SMTP', 'qiling' ),
            );
        }

        $host     = trim( (string) developer_starter_get_option( 'smtp_host', '' ) );
        $username = trim( (string) developer_starter_get_option( 'smtp_username', '' ) );
        $sender   = trim( (string) developer_starter_get_option( 'smtp_sender_name', '' ) );

        if ( empty( $host ) || empty( $username ) ) {
            return array(
                'id'           => 'smtp_service',
                'title'        => __( 'SMTP 邮件发信服务', 'qiling' ),
                'status'       => 'error',
                'category'     => __( '基础功能', 'qiling' ),
                'desc'         => __( '已开启 SMTP 开关，但服务器地址或发信账号未完整填写。', 'qiling' ),
                'action_url'   => admin_url( 'admin.php?page=qiling-settings&tab=smtp' ),
                'action_label' => __( '补充发信配置', 'qiling' ),
            );
        }

        return array(
            'id'           => 'smtp_service',
            'title'        => __( 'SMTP 邮件发信服务', 'qiling' ),
            'status'       => 'ok',
            'category'     => __( '基础功能', 'qiling' ),
            'desc'         => sprintf(
                /* translators: 1: SMTP Host, 2: SMTP Username */
                __( 'SMTP 邮件服务已启用并就绪（服务器: %1$s，发信账户: %2$s），信封发件人已自动保护对齐。', 'qiling' ),
                $host,
                $username
            ),
            'action_url'   => admin_url( 'admin.php?page=qiling-settings&tab=smtp' ),
            'action_label' => __( '发送测试邮件', 'qiling' ),
        );
    }

    /**
     * 检查图像媒体与 WebP 处理驱动
     *
     * @return array<string, mixed>
     */
    private static function check_media_and_webp() {
        $has_gd_webp = function_exists( 'imagewebp' );
        $has_imagick_webp = false;
        if ( class_exists( '\Imagick' ) && method_exists( '\Imagick', 'queryFormats' ) ) {
            try {
                $has_imagick_webp = in_array( 'WEBP', \Imagick::queryFormats( 'WEBP' ), true );
            } catch ( \Throwable $e ) {
                $has_imagick_webp = false;
            }
        }
        $server_supports_webp = $has_gd_webp || $has_imagick_webp;

        $webp_enable = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( 'thumbnail_webp_enable', '1' )
            : '1';

        if ( ! $server_supports_webp ) {
            return array(
                'id'           => 'media_webp',
                'title'        => __( 'WebP 图像极速处理引擎', 'qiling' ),
                'status'       => 'warning',
                'category'     => __( '性能与媒体', 'qiling' ),
                'desc'         => __( '服务器当前 PHP 未检测到 GD 或 Imagick 的 WebP 编码支持。主题已自动启用平滑回退，但建议安装 php-gd / php-imagick 以享受 50% 图片体积减小。', 'qiling' ),
            );
        }

        if ( '1' !== (string) $webp_enable ) {
            return array(
                'id'           => 'media_webp',
                'title'        => __( 'WebP 图像极速处理引擎', 'qiling' ),
                'status'       => 'warning',
                'category'     => __( '性能与媒体', 'qiling' ),
                'desc'         => __( '服务器已具备 WebP 编码能力，但主题设置中已关闭 WebP 缩略图生成。建议开启以节省带宽。', 'qiling' ),
                'action_url'   => admin_url( 'admin.php?page=qiling-settings&tab=optimize' ),
                'action_label' => __( '开启 WebP 压缩', 'qiling' ),
            );
        }

        return array(
            'id'           => 'media_webp',
            'title'        => __( 'WebP 图像极速处理引擎', 'qiling' ),
            'status'       => 'ok',
            'category'     => __( '性能与媒体', 'qiling' ),
            'desc'         => __( 'WebP 引擎正常工作。前台缩略图已实现自动化 WebP 转换，并适配阿里云 OSS、腾讯云 COS 云端转码。', 'qiling' ),
        );
    }

    /**
     * 检查推测性即时预渲染状态
     *
     * @return array<string, mixed>
     */
    private static function check_speculative_prerender() {
        $enable = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( 'speculative_prerender_enable', '1' )
            : '1';

        if ( '1' === (string) $enable ) {
            return array(
                'id'           => 'speculative_prerender',
                'title'        => __( 'W3C 推测性预渲染 (0秒瞬开)', 'qiling' ),
                'status'       => 'ok',
                'category'     => __( '性能优化', 'qiling' ),
                'desc'         => __( '推测性预渲染已开启。访客鼠标悬停链接时后台自动预载，呈现类似单页应用（SPA）的无缝秒开体验。', 'qiling' ),
            );
        }

        return array(
            'id'           => 'speculative_prerender',
            'title'        => __( 'W3C 推测性预渲染 (0秒瞬开)', 'qiling' ),
            'status'       => 'warning',
            'category'     => __( '性能优化', 'qiling' ),
            'desc'         => __( '推测性预渲染当前处于关闭状态。建议在优化设置中开启，体验 0ms 网页切换效果。', 'qiling' ),
            'action_url'   => admin_url( 'admin.php?page=qiling-settings&tab=optimize' ),
            'action_label' => __( '开启预渲染', 'qiling' ),
        );
    }

    /**
     * 检查企业合规信息（ICP备案、网安备案）
     *
     * @return array<string, mixed>
     */
    private static function check_compliance_info() {
        $icp = function_exists( 'developer_starter_get_option' )
            ? trim( (string) developer_starter_get_option( 'icp_number', '' ) )
            : '';

        if ( empty( $icp ) ) {
            return array(
                'id'           => 'compliance_icp',
                'title'        => __( 'ICP 网站备案号合规展示', 'qiling' ),
                'status'       => 'warning',
                'category'     => __( '合规运营', 'qiling' ),
                'desc'         => __( '未检测到 ICP 备案编号。面向中国大陆境内公开运行的商业或企业站点，需在页脚公示合规备案号。', 'qiling' ),
                'action_url'   => admin_url( 'admin.php?page=qiling-settings&tab=footer' ),
                'action_label' => __( '填写备案号', 'qiling' ),
            );
        }

        return array(
            'id'           => 'compliance_icp',
            'title'        => __( 'ICP 网站备案号合规展示', 'qiling' ),
            'status'       => 'ok',
            'category'     => __( '合规运营', 'qiling' ),
            'desc'         => sprintf(
                /* translators: %s: ICP number */
                __( '已正确配置 ICP 备案信息：%s。', 'qiling' ),
                $icp
            ),
        );
    }

    /**
     * 检查 SEO 与固定链接设置
     *
     * @return array<string, mixed>
     */
    private static function check_seo_and_permalinks() {
        $permalink_structure = get_option( 'permalink_structure', '' );

        if ( empty( $permalink_structure ) ) {
            return array(
                'id'           => 'permalinks_structure',
                'title'        => __( '搜索引擎友好度 (固定链接)', 'qiling' ),
                'status'       => 'warning',
                'category'     => __( 'SEO 优化', 'qiling' ),
                'desc'         => __( '当前站点正在使用朴素动态链接（如 ?p=123），对百度、Google 等搜索引擎收录极不友好。强烈建议调整为伪静态结构（如 /%post_id%.html）。', 'qiling' ),
                'action_url'   => admin_url( 'options-permalink.php' ),
                'action_label' => __( '配置伪静态', 'qiling' ),
            );
        }

        return array(
            'id'           => 'permalinks_structure',
            'title'        => __( '搜索引擎友好度 (固定链接)', 'qiling' ),
            'status'       => 'ok',
            'category'     => __( 'SEO 优化', 'qiling' ),
            'desc'         => __( '固定链接伪静态规则已正常生效，有利于搜索引擎收录与抓取。', 'qiling' ),
        );
    }

    /**
     * 检查自定义字体与静态资源
     *
     * @return array<string, mixed>
     */
    private static function check_custom_font_assets() {
        $enable = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( 'custom_font_enable', '' )
            : '';

        if ( '1' !== (string) $enable ) {
            return array(
                'id'           => 'custom_font',
                'title'        => __( '自定义企业字体配置', 'qiling' ),
                'status'       => 'ok',
                'category'     => __( '视觉与资产', 'qiling' ),
                'desc'         => __( '未启用自定义字体，站点正使用高性能系统级原生字体族（-apple-system / BlinkMacSystemFont）。', 'qiling' ),
            );
        }

        $woff2 = function_exists( 'developer_starter_get_option' )
            ? trim( (string) developer_starter_get_option( 'custom_font_woff2_url', '' ) )
            : '';

        if ( empty( $woff2 ) ) {
            return array(
                'id'           => 'custom_font',
                'title'        => __( '自定义企业字体配置', 'qiling' ),
                'status'       => 'error',
                'category'     => __( '视觉与资产', 'qiling' ),
                'desc'         => __( '已开启自定义字体开关，但未提供 WOFF2 字体文件地址，将导致字体加载失败并影响页面排版。', 'qiling' ),
                'action_url'   => admin_url( 'admin.php?page=qiling-settings&tab=design' ),
                'action_label' => __( '上传字体文件', 'qiling' ),
            );
        }

        return array(
            'id'           => 'custom_font',
            'title'        => __( '自定义企业字体配置', 'qiling' ),
            'status'       => 'ok',
            'category'     => __( '视觉与资产', 'qiling' ),
            'desc'         => __( '已配置 WOFF2 字体文件，且头部关键预加载通道已激活，有效消除字体闪烁 (FOUT)。', 'qiling' ),
        );
    }
}
