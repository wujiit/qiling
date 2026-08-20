<?php
/**
 * 资源下载页面创建器类
 *
 * 当用户选择"资源下载"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 资源下载页面创建器类
 */
class Resources_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-resources.php';
    protected const AJAX_ACTION = 'fill_resources_modules';
    protected const FILLED_META_KEY = '_resources_modules_filled';

    /**
     * 获取资源下载页面的默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        // 获取页面标题用于动态内容
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '资源下载中心', 'developer-starter' );
        }
        
        $default_modules = array(
            // 模块1：Banner - 资源下载页面顶部
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_title'    => $page_title,
                    'banner_subtitle' => __( '获取我们的APP、软件工具和企业资料', 'developer-starter' ),
                    'banner_btn_text' => __( '立即下载', 'developer-starter' ),
                    'banner_btn_url'  => '#app-downloads',
                    'banner_btn2_text' => __( '查看文档', 'developer-starter' ),
                    'banner_btn2_url'  => '#documents',
                    'banner_bg_image' => '',
                    'banner_bg_color' => 'linear-gradient(135deg, #0f172a 0%, #1e40af 50%, #059669 100%)',
                    'banner_height'   => '450',
                ),
            ),

            // 模块2：下载中心 - 移动端APP
            array(
                'type' => 'downloads',
                'data' => array(
                    'downloads_title'    => __( '📱 移动端 APP', 'developer-starter' ),
                    'downloads_subtitle' => __( '随时随地，便捷办公', 'developer-starter' ),
                    'downloads_columns'  => '2',
                    'downloads_items'    => array(
                        array(
                            'title'       => __( '企业移动APP (iOS)', 'developer-starter' ),
                            'size'        => '89.5 MB',
                            'file'        => '#',
                            'icon'        => '🍎',
                            'format'      => 'IPA',
                            'date'        => '2024-12-20',
                            'description' => __( '适用于 iPhone 和 iPad，需要 iOS 14.0 或更高版本', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '企业移动APP (Android)', 'developer-starter' ),
                            'size'        => '76.2 MB',
                            'file'        => '#',
                            'icon'        => '🤖',
                            'format'      => 'APK',
                            'date'        => '2024-12-20',
                            'description' => __( '适用于 Android 8.0 及以上版本', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '轻量版APP (iOS)', 'developer-starter' ),
                            'size'        => '45.8 MB',
                            'file'        => '#',
                            'icon'        => '📲',
                            'format'      => 'IPA',
                            'date'        => '2024-12-15',
                            'description' => __( '精简功能版本，占用空间更少', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '轻量版APP (Android)', 'developer-starter' ),
                            'size'        => '38.6 MB',
                            'file'        => '#',
                            'icon'        => '📲',
                            'format'      => 'APK',
                            'date'        => '2024-12-15',
                            'description' => __( '适合存储空间有限的设备', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块3：下载中心 - 桌面软件
            array(
                'type' => 'downloads',
                'data' => array(
                    'downloads_title'    => __( '💻 桌面客户端', 'developer-starter' ),
                    'downloads_subtitle' => __( '功能强大的桌面办公软件', 'developer-starter' ),
                    'downloads_columns'  => '2',
                    'downloads_items'    => array(
                        array(
                            'title'       => __( '企业管理系统 (Windows)', 'developer-starter' ),
                            'size'        => '156.8 MB',
                            'file'        => '#',
                            'icon'        => '🪟',
                            'format'      => 'EXE',
                            'date'        => '2024-12-18',
                            'description' => __( '支持 Windows 10/11 64位系统', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '企业管理系统 (macOS)', 'developer-starter' ),
                            'size'        => '142.3 MB',
                            'file'        => '#',
                            'icon'        => '🍏',
                            'format'      => 'DMG',
                            'date'        => '2024-12-18',
                            'description' => __( '支持 macOS 12.0 及以上版本，兼容 Apple Silicon', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '数据同步工具', 'developer-starter' ),
                            'size'        => '28.5 MB',
                            'file'        => '#',
                            'icon'        => '🔄',
                            'format'      => 'EXE',
                            'date'        => '2024-12-10',
                            'description' => __( '本地数据与云端同步工具，支持断点续传', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '报表生成器', 'developer-starter' ),
                            'size'        => '35.2 MB',
                            'file'        => '#',
                            'icon'        => '📊',
                            'format'      => 'EXE',
                            'date'        => '2024-12-08',
                            'description' => __( '快速生成各类业务报表，支持Excel/PDF导出', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块4：下载中心 - 企业文档
            array(
                'type' => 'downloads',
                'data' => array(
                    'downloads_title'    => __( '📚 企业资料与文档', 'developer-starter' ),
                    'downloads_subtitle' => __( '财务报告、技术文档与产品资料', 'developer-starter' ),
                    'downloads_columns'  => '3',
                    'downloads_items'    => array(
                        // 财务报告
                        array(
                            'title'       => __( '2024年度财务报告', 'developer-starter' ),
                            'size'        => '8.5 MB',
                            'file'        => '#',
                            'icon'        => '📈',
                            'format'      => 'PDF',
                            'date'        => '2024-12-28',
                            'description' => __( '公司年度财务报表及经营分析', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '2024年Q3季度报告', 'developer-starter' ),
                            'size'        => '4.2 MB',
                            'file'        => '#',
                            'icon'        => '📊',
                            'format'      => 'PDF',
                            'date'        => '2024-10-15',
                            'description' => __( '第三季度财务数据与业务概览', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '2024年Q2季度报告', 'developer-starter' ),
                            'size'        => '3.8 MB',
                            'file'        => '#',
                            'icon'        => '📊',
                            'format'      => 'PDF',
                            'date'        => '2024-07-12',
                            'description' => __( '第二季度财务数据与业务概览', 'developer-starter' ),
                        ),
                        // 技术文档
                        array(
                            'title'       => __( 'API接口文档', 'developer-starter' ),
                            'size'        => '2.1 MB',
                            'file'        => '#',
                            'icon'        => '🔧',
                            'format'      => 'PDF',
                            'date'        => '2024-12-01',
                            'description' => __( '开发者必备的API接口说明文档', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '系统部署指南', 'developer-starter' ),
                            'size'        => '5.6 MB',
                            'file'        => '#',
                            'icon'        => '📖',
                            'format'      => 'PDF',
                            'date'        => '2024-11-20',
                            'description' => __( '私有化部署的详细安装配置指南', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '技术白皮书', 'developer-starter' ),
                            'size'        => '3.2 MB',
                            'file'        => '#',
                            'icon'        => '📋',
                            'format'      => 'PDF',
                            'date'        => '2024-10-08',
                            'description' => __( '技术架构 design 及安全说明', 'developer-starter' ),
                        ),
                        // 产品资料
                        array(
                            'title'       => __( '产品手册', 'developer-starter' ),
                            'size'        => '12.8 MB',
                            'file'        => '#',
                            'icon'        => '📘',
                            'format'      => 'PDF',
                            'date'        => '2024-11-15',
                            'description' => __( '全面的产品功能介绍与操作指南', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '用户快速入门', 'developer-starter' ),
                            'size'        => '1.5 MB',
                            'file'        => '#',
                            'icon'        => '🚀',
                            'format'      => 'PDF',
                            'date'        => '2024-12-05',
                            'description' => __( '新用户快速上手指南', 'developer-starter' ),
                        ),
                        array(
                            'title'       => __( '企业宣传册', 'developer-starter' ),
                            'size'        => '18.6 MB',
                            'file'        => '#',
                            'icon'        => '🎨',
                            'format'      => 'PDF',
                            'date'        => '2024-09-20',
                            'description' => __( '公司介绍、产品服务及成功案例', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块5：FAQ - 下载相关常见问题
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title' => __( '下载常见问题', 'developer-starter' ),
                    'faq_items' => array(
                        array(
                            'question' => __( 'APP安装后无法打开怎么办？', 'developer-starter' ),
                            'answer'   => __( 'iOS用户请确保在"设置-通用-VPN与设备管理"中信任企业证书。Android用户请确保已开启"允许安装未知来源应用"选项。如仍有问题，请联系技术支持。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '下载的文件是否安全？', 'developer-starter' ),
                            'answer'   => __( '所有下载文件均经过严格安全检测，使用HTTPS加密传输。软件安装包均有数字签名，请放心下载使用。如发现可疑链接，请及时联系我们。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '如何获取历史版本的软件？', 'developer-starter' ),
                            'answer'   => __( '本页面仅提供最新稳定版本的下载。如需历史版本，请联系客服或技术支持团队，我们将根据您的需求提供相应版本。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '企业批量部署如何获取授权？', 'developer-starter' ),
                            'answer'   => __( '企业批量部署需要申请企业授权许可。请联系我们的销售团队，提供企业信息和部署规模，我们将为您提供定制化的授权方案。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '财务报告和技术文档需要权限才能下载吗？', 'developer-starter' ),
                            'answer'   => __( '部分内部文档可能需要登录企业账号才能下载。公开的财务报告和产品手册无需登录即可免费下载。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块6：CTA行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'    => __( '找不到需要的资源？', 'developer-starter' ),
                    'cta_subtitle' => __( '联系我们获取更多资料，或申请定制化解决方案', 'developer-starter' ),
                    'cta_btn_text' => __( '联系我们', 'developer-starter' ),
                    'cta_btn_url'  => '/contact/',
                    'cta_bg_color' => 'linear-gradient(135deg, #1e40af 0%, #059669 100%)',
                ),
            ),
        );

        return $default_modules;
    }
}
