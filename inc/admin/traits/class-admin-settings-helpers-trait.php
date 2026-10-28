<?php
/**
 * 主题后台设置辅助函数 Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Helpers_Trait {

    private function get_domain_scan_field_registry() {
        static $registry = null;

        if ( null !== $registry ) {
            return $registry;
        }

        $registry = array();
        $tabs     = $this->get_tabs();
        $configs  = $this->get_fields_config();

        foreach ( $configs as $tab_key => $fields ) {
            if ( ! is_array( $fields ) ) {
                continue;
            }

            $tab_label       = isset( $tabs[ $tab_key ] ) ? wp_strip_all_tags( (string) $tabs[ $tab_key ] ) : (string) $tab_key;
            $current_section = '';

            foreach ( $fields as $field ) {
                if ( ! is_array( $field ) ) {
                    continue;
                }

                $type = isset( $field['type'] ) ? (string) $field['type'] : '';
                if ( 'section' === $type ) {
                    $current_section = isset( $field['title'] ) ? wp_strip_all_tags( (string) $field['title'] ) : '';
                    continue;
                }

                if ( empty( $field['id'] ) ) {
                    continue;
                }

                $field_id = (string) $field['id'];
                $label    = isset( $field['label'] ) && '' !== trim( (string) $field['label'] )
                    ? wp_strip_all_tags( (string) $field['label'] )
                    : $field_id;

                $registry[ $field_id ] = array(
                    'tab_label'     => $tab_label,
                    'section_label' => $current_section,
                    'label'         => $label,
                    'type'          => $type,
                    'children'      => array(),
                );

                if ( 'repeater' === $type && ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
                    foreach ( $field['fields'] as $child_field ) {
                        if ( ! is_array( $child_field ) || empty( $child_field['id'] ) ) {
                            continue;
                        }

                        $child_id    = (string) $child_field['id'];
                        $child_label = isset( $child_field['label'] ) && '' !== trim( (string) $child_field['label'] )
                            ? wp_strip_all_tags( (string) $child_field['label'] )
                            : $child_id;

                        $registry[ $field_id ]['children'][ $child_id ] = $child_label;
                    }
                }
            }
        }

        return $registry;
    }

    private function parse_domain_scan_option_path( $path ) {
        $segments = array();
        $path     = (string) $path;

        if ( '' === $path ) {
            return $segments;
        }

        if ( preg_match_all( '/([^.\\[\\]]+)|\\[(\\d+)\\]/', $path, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                if ( isset( $match[2] ) && '' !== $match[2] ) {
                    $segments[] = (int) $match[2];
                } elseif ( isset( $match[1] ) && '' !== $match[1] ) {
                    $segments[] = (string) $match[1];
                }
            }
        }

        return $segments;
    }

    private function get_domain_scan_display_path( $path ) {
        $path     = (string) $path;
        $segments = $this->parse_domain_scan_option_path( $path );

        if ( empty( $segments ) ) {
            return array(
                'display' => $path,
                'raw'     => $path,
            );
        }

        $registry     = $this->get_domain_scan_field_registry();
        $top_level_id = (string) array_shift( $segments );

        if ( empty( $registry[ $top_level_id ] ) ) {
            return array(
                'display' => $path,
                'raw'     => $path,
            );
        }

        $field = $registry[ $top_level_id ];
        $parts = array();

        if ( ! empty( $field['tab_label'] ) ) {
            $parts[] = (string) $field['tab_label'];
        }
        if ( ! empty( $field['section_label'] ) ) {
            $parts[] = (string) $field['section_label'];
        }
        if ( ! empty( $field['label'] ) ) {
            $parts[] = (string) $field['label'];
        }

        foreach ( $segments as $segment ) {
            if ( is_int( $segment ) ) {
                $parts[] = sprintf( __( '第 %d 项', 'qiling' ), $segment + 1 );
                continue;
            }

            $segment = (string) $segment;
            if ( ! empty( $field['children'][ $segment ] ) ) {
                $parts[] = (string) $field['children'][ $segment ];
                continue;
            }

            $parts[] = $segment;
        }

        $parts = array_values( array_filter( array_unique( $parts ) ) );

        return array(
            'display' => implode( ' -> ', $parts ),
            'raw'     => $path,
        );
    }

    private function normalize_site_domain( $raw_domain ) {
        $domain = trim( (string) $raw_domain );
        if ( $domain === '' ) {
            return '';
        }

        $domain = preg_replace( '#^https?://#i', '', $domain );
        $domain = preg_replace( '#^//#', '', $domain );
        $domain = trim( $domain, " \t\n\r\0\x0B/" );

        if ( $domain === '' ) {
            return '';
        }

        if ( strpos( $domain, '/' ) !== false ) {
            $domain = strtok( $domain, '/' );
        }
        if ( strpos( $domain, '?' ) !== false ) {
            $domain = strtok( $domain, '?' );
        }
        if ( strpos( $domain, '#' ) !== false ) {
            $domain = strtok( $domain, '#' );
        }

        $parsed_host = wp_parse_url( 'https://' . $domain, PHP_URL_HOST );
        if ( is_string( $parsed_host ) && $parsed_host !== '' ) {
            $domain = $parsed_host;
        }

        return strtolower( trim( $domain, '.' ) );
    }

    private function is_domain_scan_requested() {
        $scan_flag = isset( $_GET['ds_run_domain_scan'] ) ? sanitize_text_field( wp_unslash( $_GET['ds_run_domain_scan'] ) ) : '';
        $old_base  = isset( $_GET['ds_compare_old_domain'] ) ? sanitize_text_field( wp_unslash( $_GET['ds_compare_old_domain'] ) ) : '';

        return '1' === $scan_flag || '' !== $old_base;
    }

        private function get_ecosystem_plugin_groups() {
        return array(
            array(
                'title'   => 'WordPress 启灵生态 · 重点更新',
                'badge'   => '重点研发与持续迭代 · 1 款',
                'desc'    => '启灵生态变现核心中枢，持续高频迭代。',
                'plugins' => array(
                    array(
                        'name'   => '启灵商城',
                        'meta'   => '重点核心插件 · 积分/商城/变现',
                        'fee'    => 'Github 开源',
                        'desc'   => 'WordPress 启灵生态变现核心中枢，支持积分充值、资源付费下载、VIP会员体系、实物与虚拟商品，无缝兼容微信小程序端，作为核心抓手持续高频迭代。已在 GitHub 100% 开源。',
                        'github' => 'https://github.com/wujiit/qilingshop',
                    ),
                ),
            ),
            array(
                'title'   => 'WordPress 启灵生态 · 生态更新（协同兼容）',
                'badge'   => '协同兼容 · 18 款',
                'desc'    => '随启灵生态保持持续更新与兼容维护，覆盖社区、音视频、小程序、AI与安全。',
                'plugins' => array(
                    array(
                        'name'   => '启灵GEO',
                        'meta'   => '生态更新 · 流量增长',
                        'fee'    => 'Github 开源',
                        'desc'   => '生成式引擎优化（GEO），抢占 Perplexity/ChatGPT/Kimi 时代搜索流量前沿，随启灵生态保持更新与兼容维护，代码已在 GitHub 100% 全开源。',
                        'github' => 'https://github.com/wujiit/qilinggeo',
                    ),
                    array(
                        'name'   => '启灵bbs',
                        'meta'   => '新插件 · 社区论坛',
                        'fee'    => 'Github 开源',
                        'desc'   => '现代化社区交流、圈子讨论、打赏与付费看帖，代码开源维护。',
                        'github' => 'https://github.com/wujiit/qilingbbs',
                    ),
                    array(
                        'name'   => '启灵游戏',
                        'meta'   => '新插件 · 虚拟游戏币 · 互动娱乐',
                        'fee'    => '免费',
                        'desc'   => '内置虚拟彩票、幸运大转盘等趣味互动游戏，纯娱乐闭环机制，不支持提现与现金兑付，游戏币仅限插件内消耗使用',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵营销',
                        'meta'   => '重构插件 · 裂变拓客',
                        'fee'    => '免费',
                        'desc'   => '用户抽奖大厅。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵软件库',
                        'meta'   => '新插件 · 下载中心',
                        'fee'    => '免费',
                        'desc'   => '版本迭代日志、高速分流下载、应用评分展示。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵导航',
                        'meta'   => '重构插件 · 网址精选',
                        'fee'    => '免费',
                        'desc'   => '自定义网址分类、热度统计、一键提交与收录。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵播放器',
                        'meta'   => '原免费升级 · 影音流媒体',
                        'fee'    => '免费',
                        'desc'   => 'HTML5 弹幕播放器、Artplayer 深度整合',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵微信小程序',
                        'meta'   => '小程序接口',
                        'fee'    => '免费开源',
                        'desc'   => 'WordPress 与微信小程序端数据无缝互联。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵消息推送',
                        'meta'   => '邮件/微信/钉钉',
                        'fee'    => '免费开源',
                        'desc'   => '多通道实时订单与系统事件通知推送。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵内容安全',
                        'meta'   => '文本/图片审查',
                        'fee'    => '免费开源',
                        'desc'   => '违禁词过滤、文本合规风险拦截。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵安全防护',
                        'meta'   => '基础安全防护',
                        'fee'    => 'Github 开源',
                        'desc'   => 'Wordpress基础安全优化、IP限制等，已在 GitHub 开源。',
                        'github' => 'https://github.com/wujiit/wp-security',
                    ),
                    array(
                        'name'   => '启灵AI多语言',
                        'meta'   => '全球化翻译',
                        'fee'    => '免费开源',
                        'desc'   => '全站自动化多语种翻译切换。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵AI站长助手',
                        'meta'   => '运维与SEO',
                        'fee'    => '免费开源',
                        'desc'   => '站长 SEO 诊断与站点自动化维护。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵AI外贸助手',
                        'meta'   => '新插件 · 跨境出海',
                        'fee'    => '¥29',
                        'desc'   => '外贸邮件撰写、多语种本地化翻译与多国家客户开发。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵悬赏任务',
                        'meta'   => '新插件 · 众包互助',
                        'fee'    => '¥59',
                        'desc'   => '用户发布任务、提交审核、赏金托管与提现结算。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵担保交易',
                        'meta'   => '新插件 · 中介安全',
                        'fee'    => '¥59',
                        'desc'   => '站内资金托管中介、买卖担保、安全验货与纠纷仲裁。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵AI图像',
                        'meta'   => '新插件 · 视觉生成',
                        'fee'    => '¥39',
                        'desc'   => 'AI图像生成、生图排版与海报生成。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵AI提示词',
                        'meta'   => '重构插件 · Prompt市场',
                        'fee'    => '¥19',
                        'desc'   => '优质提示词库与Prompt指令管理。',
                        'github' => '',
                    ),
                ),
            ),
            array(
                'title'   => 'WordPress 启灵生态 · 版本更新（保版本支持）',
                'badge'   => '保版本支持 · 20 款',
                'desc'    => '轻量实用工具与垂直业务扩展，保持长期版本兼容支持。',
                'plugins' => array(
                    array(
                        'name'   => '启灵AI文章创作',
                        'meta'   => '文章生成',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，自动化创作。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵数据统计',
                        'meta'   => '访问分析',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，轻量统计。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵投票',
                        'meta'   => '互动投票',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，活动投票。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵作品库',
                        'meta'   => '案例展示',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，相册展示。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵心愿单',
                        'meta'   => '愿望互动',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，互动体验。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵图床',
                        'meta'   => '外链存储',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，图片托管。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵快递',
                        'meta'   => '物流查询',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，全国单号。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵解析',
                        'meta'   => '视频解析',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，抖音平台。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵广告',
                        'meta'   => '广告位管理',
                        'fee'    => '免费',
                        'desc'   => '保版本支持，轮播排期。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵招聘',
                        'meta'   => '新插件 · 求职招工',
                        'fee'    => '免费',
                        'desc'   => '企业发布职位、简历投递、找工作与职位智能匹配。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵相亲',
                        'meta'   => '新插件 · 婚恋交友',
                        'fee'    => '免费',
                        'desc'   => '相亲实名认证、红娘牵线、动态广场与资料卡互换。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵小工具',
                        'meta'   => '基础组件',
                        'fee'    => '免费开源',
                        'desc'   => '全站常用小组件、侧边栏增强与便捷配置。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵AI写作',
                        'meta'   => '新插件 · 原创生成',
                        'fee'    => '免费',
                        'desc'   => '多模型批量智能文章撰写、SEO 友好的智能内容工厂。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵文档',
                        'meta'   => '新插件 · 知识库',
                        'fee'    => '免费',
                        'desc'   => '树形层级知识库、在线帮助中心、开发手册沉淀。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵文档解析',
                        'meta'   => '新插件 · 格式抽取',
                        'fee'    => '免费',
                        'desc'   => 'PDF/Word/Markdown 智能解析与内容抽取，保版本支持。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵文档转换',
                        'meta'   => '重构插件 · 格式互转',
                        'fee'    => '免费',
                        'desc'   => '多种文档格式批量互转与在线预览，保版本支持。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵表单',
                        'meta'   => '自定义采集',
                        'fee'    => '免费开源',
                        'desc'   => '可视化表单设计、线索收集与数据导出。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵云存储',
                        'meta'   => '阿里/腾讯OSS',
                        'fee'    => '免费开源',
                        'desc'   => '多云存储对接、静态资源 CDN 加速托管。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵微信管理',
                        'meta'   => '公众号运营',
                        'fee'    => '免费开源',
                        'desc'   => '微信公众号菜单、自动回复与粉丝互动管理。',
                        'github' => '',
                    ),
                    array(
                        'name'   => '启灵AI客服',
                        'meta'   => '7x24小时对话',
                        'fee'    => '免费开源',
                        'desc'   => '智能知识库在线问答客服。',
                        'github' => '',
                    ),
                ),
            ),
        );
    }

    private function get_standalone_open_source_plugins() {
        return array();
    }

    private function get_ecosystem_plugin_index() {
        $index = array();

        foreach ( $this->get_ecosystem_plugin_groups() as $group ) {
            if ( empty( $group['plugins'] ) || ! is_array( $group['plugins'] ) ) {
                continue;
            }

            foreach ( $group['plugins'] as $plugin ) {
                if ( empty( $plugin['slug'] ) ) {
                    continue;
                }

                $index[ (string) $plugin['slug'] ] = $plugin;
            }
        }

        return $index;
    }

    private function get_plugin_guide_plugin_index() {
        $index = $this->get_ecosystem_plugin_index();

        foreach ( $this->get_standalone_open_source_plugins() as $plugin ) {
            if ( empty( $plugin['slug'] ) || empty( $plugin['file'] ) ) {
                continue;
            }

            $index[ (string) $plugin['slug'] ] = $plugin;
        }

        return $index;
    }

    private function detect_ecosystem_plugin_statuses() {
        $active_plugins = get_option( 'active_plugins', array() );
        $active_plugins = is_array( $active_plugins ) ? array_map( 'plugin_basename', $active_plugins ) : array();
        $active_map     = array_fill_keys( $active_plugins, true );

        $network_active_map = array();
        if ( is_multisite() ) {
            $network_active = get_site_option( 'active_sitewide_plugins', array() );
            if ( is_array( $network_active ) ) {
                $network_active_map = array_fill_keys( array_map( 'plugin_basename', array_keys( $network_active ) ), true );
            }
        }

        $statuses = array();
        $summary  = array(
            'total'    => 0,
            'active'   => 0,
            'inactive' => 0,
            'missing'  => 0,
        );

        foreach ( $this->get_plugin_guide_plugin_index() as $slug => $plugin ) {
            $main_file = isset( $plugin['file'] ) ? plugin_basename( (string) $plugin['file'] ) : '';
            $installed = '' !== $main_file && defined( 'WP_PLUGIN_DIR' ) && file_exists( trailingslashit( WP_PLUGIN_DIR ) . $main_file );
            $active    = '' !== $main_file && ( isset( $active_map[ $main_file ] ) || isset( $network_active_map[ $main_file ] ) );
            $status    = $installed ? 'inactive' : 'missing';

            if ( $active ) {
                $status = 'active';
            }

            $summary['total']++;
            $summary[ $status ]++;

            $statuses[ $slug ] = array(
                'status' => $status,
                'label'  => $this->get_ecosystem_plugin_status_label( $status ),
                'file'   => $main_file,
            );
        }

        return array(
            'summary'  => $summary,
            'statuses' => $statuses,
        );
    }

    private function get_ecosystem_plugin_status_label( $status ) {
        switch ( (string) $status ) {
            case 'active':
                return __( '已启用', 'qiling' );
            case 'inactive':
                return __( '已安装未启用', 'qiling' );
            case 'missing':
                return __( '未安装', 'qiling' );
        }

        return __( '未检测', 'qiling' );
    }

    private function get_advanced_settings_url( $args = array() ) {
        return add_query_arg(
            array_merge(
                array(
                    'page' => 'qiling-settings',
                    'tab'  => 'advanced',
                ),
                is_array( $args ) ? $args : array()
            ),
            admin_url( 'admin.php' )
        );
    }
}
