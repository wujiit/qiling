<?php
/**
 * 开源项目/开发工具官网创建器类
 *
 * 当用户选择"开源项目/开发工具官网"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.7
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Open_Source_DevTools_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-open-source-devtools.php';
    protected const AJAX_ACTION = 'fill_open_source_devtools_modules';
    protected const FILLED_META_KEY = '_open_source_devtools_modules_filled';

    /**
     * 获取页面默认模块。
     *
     * @param int $page_id 页面 ID。
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '开源项目/开发工具官网', 'developer-starter' );
        }

        $default_modules = array(
            array(
                'type' => 'dynamic_banner',
                'data' => array(
                    'db_height'          => '82vh',
                    'db_bg_type'         => 'gradient',
                    'db_bg_gradient'     => 'linear-gradient(135deg, #0b1220 0%, #1e3a8a 45%, #0ea5e9 100%)',
                    'db_title_prefix'    => $page_title,
                    'db_typing_mode'     => 'loop',
                    'db_typing_text'     => __( "现代化开发工具链\nCLI + SDK + 可视化工作台\n开源协作，持续迭代发布", 'developer-starter' ),
                    'db_highlight_color' => '#5eead4',
                    'db_title_color'     => '#e2e8f0',
                    'db_subtitle'        => __( '为开发者团队提供更高效的构建、调试与发布体验。', 'developer-starter' ),
                    'db_desc'            => __( '预设已包含快速开始、下载入口、版本历程、社区数据和反馈表单。', 'developer-starter' ),
                    'db_text_color'      => 'rgba(226,232,240,0.88)',
                    'db_buttons'         => array(
                        array(
                            'text'  => __( '查看快速开始', 'developer-starter' ),
                            'link'  => '#quickstart',
                            'style' => 'primary',
                            'icon'  => '🚀',
                        ),
                        array(
                            'text'  => __( '下载最新版本', 'developer-starter' ),
                            'link'  => '#downloads',
                            'style' => 'outline',
                            'icon'  => '⬇️',
                        ),
                    ),
                    'db_media_type'      => 'image',
                    'db_main_image'      => '',
                    'db_image_shadow'    => 'soft',
                    'db_floating_cards'  => array(
                        array(
                            'content_type' => 'badge',
                            'title'        => 'MIT License',
                            'pos_top'      => '8%',
                            'pos_right'    => '-2%',
                            'animation'    => 'float',
                            'delay'        => '0s',
                        ),
                        array(
                            'content_type' => 'badge',
                            'title'        => __( '每月稳定更新', 'developer-starter' ),
                            'pos_bottom'   => '10%',
                            'pos_left'     => '-4%',
                            'animation'    => 'pulse',
                            'delay'        => '0.4s',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'tabs',
                'data' => array(
                    'tabs_title'            => __( '快速开始', 'developer-starter' ),
                    'tabs_subtitle'         => __( '3 个步骤完成本地安装与首个项目初始化', 'developer-starter' ),
                    'tabs_style'            => 'boxed',
                    'tabs_align'            => 'center',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs_items'            => array(
                        array(
                            'title'   => __( '安装', 'developer-starter' ),
                            'icon'    => '📦',
                            'content' => '<p>' . esc_html__( '通过包管理器安装 CLI 与核心依赖。', 'developer-starter' ) . '</p><pre><code>npm i -g ql-devtools\n# or\nbrew install ql-devtools</code></pre>',
                        ),
                        array(
                            'title'   => __( '初始化项目', 'developer-starter' ),
                            'icon'    => '🧩',
                            'content' => '<p>' . esc_html__( '一键创建模板工程并完成基础配置。', 'developer-starter' ) . '</p><pre><code>ql init my-app\ncd my-app\nql dev</code></pre>',
                        ),
                        array(
                            'title'   => __( '发布部署', 'developer-starter' ),
                            'icon'    => '🚀',
                            'content' => '<p>' . esc_html__( '执行构建与发布命令，支持多环境部署。', 'developer-starter' ) . '</p><pre><code>ql build --prod\nql deploy --env=production</code></pre>',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'features_list',
                'data' => array(
                    'title'                 => __( '开发工具核心能力', 'developer-starter' ),
                    'subtitle'              => __( '围绕开发效率、质量保障与协作发布设计', 'developer-starter' ),
                    'columns'               => '3',
                    'module_bg_type'        => 'color',
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'tabs'                  => array(
                        array(
                            'tab_id'    => 'dev',
                            'tab_title' => __( '开发体验', 'developer-starter' ),
                            'tab_icon'  => '⚡',
                            'features'  => __( "🛠️|本地调试|热更新与断点调试能力完整\n📚|模板脚手架|内置多场景模板快速起步\n🔍|智能提示|命令提示与错误定位更清晰", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'quality',
                            'tab_title' => __( '质量保障', 'developer-starter' ),
                            'tab_icon'  => '✅',
                            'features'  => __( "🧪|自动测试|集成单测与冒烟测试流程\n🧹|代码规范|内置 lint/format 保障代码一致性\n🛡️|安全检查|依赖漏洞检测与修复建议", 'developer-starter' ),
                        ),
                        array(
                            'tab_id'    => 'release',
                            'tab_title' => __( '发布协作', 'developer-starter' ),
                            'tab_icon'  => '🤝',
                            'features'  => __( "🔄|CI/CD 集成|支持主流流水线平台接入\n📦|版本管理|语义化版本与变更日志自动生成\n🌍|多环境部署|测试、预发、生产环境统一管理", 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'downloads',
                'data' => array(
                    'downloads_title'       => __( '版本下载', 'developer-starter' ),
                    'downloads_subtitle'    => __( '按系统和语言选择对应安装包与 SDK', 'developer-starter' ),
                    'downloads_bg_color'    => '#ffffff',
                    'downloads_columns'     => '3',
                    'downloads_items'       => array(
                        array(
                            'title'       => 'CLI for macOS',
                            'size'        => '28 MB',
                            'file'        => '#',
                            'icon'        => '🍎',
                            'format'      => 'PKG',
                            'date'        => '2026-03-22',
                            'btn_text'    => __( '下载', 'developer-starter' ),
                            'description' => __( '适用于 Apple Silicon 与 Intel 芯片。', 'developer-starter' ),
                        ),
                        array(
                            'title'       => 'CLI for Windows',
                            'size'        => '31 MB',
                            'file'        => '#',
                            'icon'        => '🪟',
                            'format'      => 'EXE',
                            'date'        => '2026-03-22',
                            'btn_text'    => __( '下载', 'developer-starter' ),
                            'description' => __( '支持 Win10/Win11，一键安装运行环境。', 'developer-starter' ),
                        ),
                        array(
                            'title'       => 'JavaScript SDK',
                            'size'        => '2.3 MB',
                            'file'        => '#',
                            'icon'        => '🟨',
                            'format'      => 'ZIP',
                            'date'        => '2026-03-22',
                            'btn_text'    => __( '下载', 'developer-starter' ),
                            'description' => __( '适配浏览器和 Node.js 环境。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'github_activity',
                'data' => array(
                    'gha_title'          => __( 'GitHub 项目动态', 'developer-starter' ),
                    'gha_subtitle'       => __( '填入公开仓库地址后，自动展示 Star、Fork、最新 Release 与最近 Commit。', 'developer-starter' ),
                    'gha_enable'         => 'no',
                    'gha_repository_url' => '',
                    'gha_show_stats'     => 'yes',
                    'gha_show_release'   => 'yes',
                    'gha_show_commits'   => 'yes',
                    'gha_commit_count'   => '5',
                    'gha_cache_hours'    => '6',
                    'gha_bg_color'       => '#f8fafc',
                    'gha_padding_top'    => '80px',
                    'gha_padding_bottom' => '80px',
                ),
            ),
            array(
                'type' => 'resource_stats',
                'data' => array(
                    'rs_title'          => __( '社区与生态数据', 'developer-starter' ),
                    'rs_subtitle'       => __( '持续活跃的开发者社区与插件生态', 'developer-starter' ),
                    'rs_bg_color'       => '#f8fafc',
                    'rs_padding_top'    => '80px',
                    'rs_padding_bottom' => '80px',
                    'rs_stats_list'     => array(
                        array(
                            'stat_label'  => __( 'GitHub Stars', 'developer-starter' ),
                            'stat_icon'   => '⭐',
                            'data_source' => 'custom',
                            'source_id'   => '28000',
                            'virtual_num' => '0',
                            'show_plus'   => 'yes',
                        ),
                        array(
                            'stat_label'  => __( '月活开发者', 'developer-starter' ),
                            'stat_icon'   => '👨‍💻',
                            'data_source' => 'custom',
                            'source_id'   => '9600',
                            'virtual_num' => '0',
                            'show_plus'   => 'yes',
                        ),
                        array(
                            'stat_label'  => __( '插件数量', 'developer-starter' ),
                            'stat_icon'   => '🧩',
                            'data_source' => 'custom',
                            'source_id'   => '430',
                            'virtual_num' => '0',
                            'show_plus'   => 'yes',
                        ),
                        array(
                            'stat_label'  => __( '贡献者', 'developer-starter' ),
                            'stat_icon'   => '🤝',
                            'data_source' => 'custom',
                            'source_id'   => '540',
                            'virtual_num' => '0',
                            'show_plus'   => 'yes',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'timeline',
                'data' => array(
                    'timeline_title'          => __( '版本演进路线', 'developer-starter' ),
                    'timeline_items'          => array(
                        array(
                            'year'  => 'v1.0',
                            'title' => __( '核心 CLI 发布', 'developer-starter' ),
                            'desc'  => __( '上线项目初始化、构建、发布的基础命令能力。', 'developer-starter' ),
                        ),
                        array(
                            'year'  => 'v1.5',
                            'title' => __( '插件系统开放', 'developer-starter' ),
                            'desc'  => __( '支持第三方插件扩展和能力复用。', 'developer-starter' ),
                        ),
                        array(
                            'year'  => 'v2.0',
                            'title' => __( '可视化工作台', 'developer-starter' ),
                            'desc'  => __( '新增可视化配置和团队协作功能。', 'developer-starter' ),
                        ),
                        array(
                            'year'  => 'v2.2',
                            'title' => __( '企业级发布能力', 'developer-starter' ),
                            'desc'  => __( '支持多环境发布审批、回滚和审计记录。', 'developer-starter' ),
                        ),
                    ),
                    'module_bg_color'         => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),
            array(
                'type' => 'blog',
                'data' => array(
                    'blog_title'              => __( '更新日志与技术文章', 'developer-starter' ),
                    'blog_subtitle'           => __( '跟进版本更新、实践指南与最佳实践', 'developer-starter' ),
                    'blog_bg_color'           => '#f8fafc',
                    'blog_page_layout'        => 'full',
                    'blog_layout_style'       => 'card',
                    'blog_columns'            => '3',
                    'blog_data_source'        => 'latest',
                    'blog_count'              => '6',
                    'blog_orderby'            => 'modified',
                    'blog_show_image'         => 'yes',
                    'blog_show_excerpt'       => 'yes',
                    'blog_excerpt_length'     => '36',
                    'blog_show_date'          => 'yes',
                    'blog_show_category'      => 'yes',
                    'blog_enable_pagination'  => 'no',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '常见问题', 'developer-starter' ),
                    'faq_subtitle'          => __( '关于安装、兼容性和贡献流程的说明', 'developer-starter' ),
                    'module_bg_color'       => '#ffffff',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array(
                            'question' => __( '是否支持离线环境安装？', 'developer-starter' ),
                            'answer'   => __( '支持离线安装包和镜像源配置，适配受限网络场景。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '如何参与开源贡献？', 'developer-starter' ),
                            'answer'   => __( '可通过 Issue/PR 提交改进，遵循仓库的贡献指南。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '版本升级会破坏已有工程吗？', 'developer-starter' ),
                            'answer'   => __( '采用语义化版本策略，并提供迁移脚本和升级提示。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '是否有企业支持版本？', 'developer-starter' ),
                            'answer'   => __( '提供企业支持计划，包含 SLA 与专属技术支持。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'          => __( '社区反馈与合作', 'developer-starter' ),
                    'contact_subtitle'       => __( '提交问题、需求或合作意向，我们会尽快回复。', 'developer-starter' ),
                    'contact_show_form'      => '1',
                    'contact_form_id'        => '',
                    'module_bg_type'         => 'color',
                    'module_bg_color'        => '#f8fafc',
                    'module_padding_top'     => '80px',
                    'module_padding_bottom'  => '80px',
                ),
            ),
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '现在就开始构建你的开发工作流', 'developer-starter' ),
                    'cta_subtitle'          => __( '开源协作 + 工具链标准化，让团队交付更快更稳。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即下载', 'developer-starter' ),
                    'cta_button_url'        => '#downloads',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0b1220 0%, #1e3a8a 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
