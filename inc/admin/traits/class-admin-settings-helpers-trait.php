<?php
/**
 * Admin Settings Helpers Trait
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
                $parts[] = sprintf( __( '第 %d 项', 'developer-starter' ), $segment + 1 );
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
                'title'   => '主题联动增强',
                'desc'    => '这些插件可增强主题已有功能，但不是主题运行必需项。',
                'badge'   => '可联动',
                'plugins' => array(
                    array( 'slug' => 'xb-aifanyi-translator', 'file' => 'xb-aifanyi-translator/xb-aifanyi-translator.php', 'name' => '启灵AI多语言', 'desc' => '文章自动翻译、多语言内容同步和多语言 SEO 增强。', 'fit' => '外贸站、出海官网、多语言内容站', 'relation' => '增强主题语言切换、页面本地化和 SEO 检查。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingwebhook', 'file' => 'qilingwebhook/qilingwebhook.php', 'name' => '启灵消息推送', 'desc' => '飞书与钉钉自定义机器人 Webhook 推送。', 'fit' => '需要表单、评论、订单、清理任务通知的站点', 'relation' => '可作为主题通知、表单消息和运维提醒的推送通道。', 'fee' => '免费' ),
                    array( 'slug' => 'qiling-forms', 'file' => 'qiling-forms/qiling-forms.php', 'name' => '启灵表单', 'desc' => '多类型字段、预约时段、容量限制、邮件通知和消息推送。', 'fit' => '企业询盘、预约、报名、线索收集', 'relation' => '可与主题联系表单桥接和模板页表单入口联动。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingshop', 'file' => 'qilingshop/qilingshop.php', 'name' => '启灵积分商城', 'desc' => '积分、VIP、付费资源、实物商城等会员交易能力。', 'fit' => '资源下载、会员内容、积分消费、付费资料', 'relation' => '可增强主题下载盒子、会员入口和资源变现能力。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingapp', 'file' => 'qilingapp/qilingapp.php', 'name' => '启灵软件库', 'desc' => '软件、应用、工具下载管理。', 'fit' => '软件站、应用下载站、工具资源站', 'relation' => '主题内置软件归档模板和软件模块可适配该插件数据。', 'fee' => '收费' ),
                    array( 'slug' => 'qiling-weixin', 'file' => 'qiling-weixin/qiling-weixin.php', 'name' => '启灵微信管理', 'desc' => '公众号登录、关键词回复和菜单管理。', 'fit' => '微信生态站点、会员登录场景', 'relation' => '可增强主题微信登录、账号绑定和移动端用户体验。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingminiapp', 'file' => 'qilingminiapp/qilingminiapp.php', 'name' => '启灵微信小程序', 'desc' => '为启灵主题提供小程序接口与手机号绑定后端能力。', 'fit' => '需要小程序端访问内容或会员功能的站点', 'relation' => '可扩展主题账号体系到小程序端。', 'fee' => '免费' ),
                ),
            ),
            array(
                'title'   => 'AI 创作与内容工具',
                'desc'    => '独立 AI 工具插件，可按内容生产流程选择使用。',
                'badge'   => '独立工具',
                'plugins' => array(
                    array( 'slug' => 'qilingassistant', 'file' => 'qilingassistant/qilingassistant.php', 'name' => '启灵AI客服助手', 'desc' => '基于专属 FQA 与 GEO 数据的轻量在线问答客服。', 'fit' => '企业客服、售前问答、帮助中心', 'relation' => '独立插件，可放入主题页脚、侧边栏或页面入口。', 'fee' => '免费' ),
                    array( 'slug' => 'xb-aiwencre', 'file' => 'xb-aiwencre/xb-aiwencre-main.php', 'name' => '启灵AI文章创作', 'desc' => '面向 WordPress 的文章创作工具，支持多平台、多模型。', 'fit' => '博客、SEO 内容站、媒体站', 'relation' => '独立插件，可补充主题文章生产流程。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingtxt', 'file' => 'qilingtxt/qilingtxt.php', 'name' => '启灵AI写作', 'desc' => '文章与文案创作助手，支持历史记录、文案广场及公众号排版。', 'fit' => '内容运营、公众号排版、营销文案', 'relation' => '独立插件，可与主题内容页面搭配使用。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingprompt', 'file' => 'qilingprompt/xb-aitsc-plugin.php', 'name' => '启灵AI提示词', 'desc' => 'AI 提示词管理和展示插件。', 'fit' => '提示词库、AI 工具站、知识付费', 'relation' => '独立插件，适合用主题模板做展示页面。', 'fee' => '收费' ),
                    array( 'slug' => 'qiling-aijianli', 'file' => 'qiling-aijianli/xb-aijianli.php', 'name' => '启灵简历', 'desc' => '简历制作、岗位分析、简历优化、分段改写和面试模拟。', 'fit' => '招聘、求职、教育培训站点', 'relation' => '独立插件，可与招聘类页面模板搭配。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingforeigntrade', 'file' => 'qilingforeigntrade/qilingforeigntrade.php', 'name' => '启灵外贸助手', 'desc' => '开发信、询盘回复、报价跟进、多语邮件与封面图生成。', 'fit' => '外贸团队、B2B 出海业务', 'relation' => '独立插件，可与主题外贸模板搭配。', 'fee' => '收费' ),
                ),
            ),
            array(
                'title'   => '文档、媒体与素材处理',
                'desc'    => '面向图片、视频、文档、存储和播放器的独立工具。',
                'badge'   => '工具插件',
                'plugins' => array(
                    array( 'slug' => 'qi-pdftwpe', 'file' => 'qi-pdftwpe/xb-pdftwpe.php', 'name' => '启灵文档转换', 'desc' => 'PDF 转 Word/PPT/Excel 与文档转 PDF。', 'fit' => '文档工具站、办公效率站', 'relation' => '独立插件，可用主题模板承载落地页。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingdoc', 'file' => 'qilingdoc/qilingdoc.php', 'name' => '启灵文档', 'desc' => '专业文档中心、三栏文档布局、代码高亮与响应式适配。', 'fit' => '产品文档、开发文档、帮助中心', 'relation' => '独立插件，可与主题文档类页面形成统一视觉。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingdococr', 'file' => 'qilingdococr/qilingdococr.php', 'name' => '启灵文档解析', 'desc' => '基于 PaddleOCR 的文档解析，支持前台上传解析和使用次数限制。', 'fit' => 'OCR 工具、资料解析、文档自动化', 'relation' => '独立插件，适合工具型落地页。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingaiiimgpro', 'file' => 'qilingaiiimgpro/xb-aicytxgj.php', 'name' => '启灵AI图像', 'desc' => '文生图、图像编辑、预设风格、手办风格、海报风格等。', 'fit' => '图像生成站、设计工具、创意展示', 'relation' => '独立插件，可与主题图像模板搭配。', 'fee' => '收费' ),
                    array( 'slug' => 'xb-ai-image-generator', 'file' => 'xb-ai-image-generator/ai-image-generator.php', 'name' => '启灵AI绘画', 'desc' => 'WordPress 图片生成插件，可对接通义千问等接口。', 'fit' => '绘画、图片生成、内容配图', 'relation' => '独立插件，适合轻量图像生成场景。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingaivideopro', 'file' => 'qilingaivideopro/xb-aivideocre.php', 'name' => '启灵AI视频', 'desc' => '文生视频和图生视频工具。', 'fit' => '短视频创作、营销视频、产品展示', 'relation' => '独立插件，可与视频首屏模板搭配。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingimage', 'file' => 'qilingimage/qilingimage.php', 'name' => '启灵图像处理', 'desc' => '基于百度图像处理能力的老照片修复与增强。', 'fit' => '图像修复、照片增强、工具站', 'relation' => '独立插件。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingplayer', 'file' => 'qilingplayer/wp-artplayer.php', 'name' => '启灵播放器', 'desc' => '基于 Artplayer 的 MP4/M3U8 播放器，可设置广告和控件。', 'fit' => '视频站、课程站、媒体内容', 'relation' => '独立插件，可增强主题视频内容展示。', 'fee' => '收费' ),
                    array( 'slug' => 'qiling-image-uploader', 'file' => 'qiling-image-uploader/simple-image-hosting.php', 'name' => '启灵图床', 'desc' => '前台图片上传与快捷复制链接。', 'fit' => '图床、资源站、轻量素材管理', 'relation' => '独立插件。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingstorage', 'file' => 'qilingstorage/qilingstorage.php', 'name' => '启灵云存储', 'desc' => '阿里云 OSS 或腾讯云 COS 附件同步。', 'fit' => '图片站、下载站、媒体站、对象存储加速', 'relation' => '独立插件，可改善媒体资源存储。', 'fee' => '免费' ),
                ),
            ),
            array(
                'title'   => '业务系统与行业插件',
                'desc'    => '面向具体业务流程的独立插件，用户按业务需要选择。',
                'badge'   => '独立产品',
                'plugins' => array(
                    array( 'slug' => 'qibbs-community', 'file' => 'qibbs-community/qibbs-community.php', 'name' => '启灵bbs', 'desc' => '微社区和工单系统。', 'fit' => '用户社区、售后工单、产品支持', 'relation' => '独立插件，可与主题社区官网模板搭配。', 'fee' => '收费' ),
                    array( 'slug' => 'qiling-events', 'file' => 'qiling-events/qiling-events.php', 'name' => '启灵会务', 'desc' => '活动、报名、票务、签到与通知能力。', 'fit' => '会议、沙龙、展会、课程活动', 'relation' => '独立插件，可选调用积分商城支付。', 'fee' => '收费' ),
                    array( 'slug' => 'qiling-job-lite', 'file' => 'qiling-job-lite/qiling-job-lite.php', 'name' => '启灵轻招聘', 'desc' => '职位发布、求职简历、投递、面试流程和沟通通知。', 'fit' => '招聘站、企业招聘页、人力资源服务', 'relation' => '独立插件，可与主题招聘模板搭配。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingfreetask', 'file' => 'qilingfreetask/qilingfreetask.php', 'name' => '启灵悬赏任务', 'desc' => '任务审核、积分托管、方案投标、成果提交与奖励发放。', 'fit' => '众包平台、悬赏任务、服务撮合', 'relation' => '独立插件，可选调用积分商城支付能力。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingescrow', 'file' => 'qilingescrow/qilingescrow.php', 'name' => '启灵担保交易', 'desc' => '中介担保交易，托管支付，双方确认后放款。', 'fit' => '交易撮合、服务担保、虚拟资源交易', 'relation' => '独立插件，基于积分商城支付能力。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingfriends', 'file' => 'qilingfriends/qilingfriends.php', 'name' => '启灵相亲', 'desc' => '用户资料、匹配、私信和安全管理。', 'fit' => '本地相亲、会员资料、社交匹配', 'relation' => '独立插件，可与主题相亲模板搭配。', 'fee' => '收费' ),
                    array( 'slug' => 'qilinghousekeeping', 'file' => 'qilinghousekeeping/qilinghousekeeping.php', 'name' => '启灵家政服务', 'desc' => '多门店家政预约、服务上架、下单派单和履约状态。', 'fit' => '家政、本地生活、上门服务', 'relation' => '独立插件，可与主题家政模板搭配。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingrecycling', 'file' => 'qilingrecycling/qilingrecycling.php', 'name' => '启灵回收', 'desc' => '多品类回收估价、上门/到店/邮寄、订单管理和消息推送。', 'fit' => '本地回收、门店回收、估价预约', 'relation' => '独立插件，可与消息推送联动。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingwork', 'file' => 'qilingwork/qilingwork.php', 'name' => '启灵作品库', 'desc' => '作品数据层、详情增强、版权授权、出版物、EXIF 与音乐歌单。', 'fit' => '摄影、设计、作品展示、版权授权', 'relation' => '独立插件，可与主题作品模块搭配。', 'fee' => '免费' ),
                    array( 'slug' => 'qivoting', 'file' => 'qivoting/qivoting.php', 'name' => '启灵投票', 'desc' => '单选、多选、防刷票和实时结果展示。', 'fit' => '活动投票、用户互动、社区评选', 'relation' => '独立插件，兼容启灵主题和启灵 BBS。', 'fee' => '免费' ),
                ),
            ),
            array(
                'title'   => '运营、增长与运维',
                'desc'    => '偏运营、统计、安全、导航、优惠和站点工具类插件。',
                'badge'   => '按需使用',
                'plugins' => array(
                    array( 'slug' => 'qiling-adcustom-plugin', 'file' => 'qiling-adcustom-plugin/xb-adcustom-plugin.php', 'name' => '启灵广告', 'desc' => '多种广告类型、位置设置和数据统计。', 'fit' => '广告变现、资源站、内容站', 'relation' => '独立插件，可投放到主题页面区域。', 'fee' => '免费' ),
                    array( 'slug' => 'qiling-lottery', 'file' => 'qiling-lottery/xb_lottery.php', 'name' => '启灵营销', 'desc' => '大转盘、评论抽奖、活动抽奖和奖品管理。', 'fit' => '营销活动、用户增长、互动抽奖', 'relation' => '独立插件。', 'fee' => '收费' ),
                    array( 'slug' => 'qiling-nav', 'file' => 'qiling-nav/xb-custom-nav.php', 'name' => '启灵导航', 'desc' => '导航页面、分类管理、热榜统计、收藏和标签。', 'fit' => '导航站、工具目录、资源站', 'relation' => '独立插件，可与主题导航模板搭配。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingwishlist', 'file' => 'qilingwishlist/WishlistPlugin.php', 'name' => '启灵心愿单', 'desc' => '登录用户提交心愿单。', 'fit' => '需求收集、产品建议、用户反馈', 'relation' => '独立插件，可作为互动入口。', 'fee' => '免费' ),
                    array( 'slug' => 'qiling-stat', 'file' => 'qiling-stat/qiling-stat.php', 'name' => '启灵数据统计', 'desc' => '轻量级站内流量统计与可视化看板。', 'fit' => '低配服务器、内容站、企业站', 'relation' => '独立插件，不替代第三方统计。', 'fee' => '免费' ),
                    array( 'slug' => 'qiling-site-agent', 'file' => 'qiling-site-agent/qiling-site-agent.php', 'name' => '启灵AI站点助手', 'desc' => '站点体检、诊断和优化建议工具。', 'fit' => '站点维护、性能优化、安全巡检', 'relation' => '独立插件，启灵主题用户可有增强体验。', 'fee' => '免费' ),
                    array( 'slug' => 'qilinggeo', 'file' => 'qilinggeo/qilinggeo.php', 'name' => '启灵GEO', 'desc' => '多平台多模型分析文章、关键词、品牌资料和引用来源。', 'fit' => 'GEO 优化、品牌内容、SEO 团队', 'relation' => '独立插件，可补充主题 SEO 工作流。', 'fee' => '收费' ),
                    array( 'slug' => 'qilingcontentsecurity', 'file' => 'qilingcontentsecurity/qilingcontentsecurity.php', 'name' => '启灵内容安全', 'desc' => '文章和评论本地词库 + 百度文本审核。', 'fit' => '社区、投稿站、评论较多的内容站', 'relation' => '独立插件，可增强主题投稿和评论安全。', 'fee' => '免费' ),
                    array( 'slug' => 'qilingsecurity', 'file' => 'qilingsecurity/qilingsecurity.php', 'name' => '启灵安全防护', 'desc' => '恶意代码查杀、权限审计、敏感文件保护等。', 'fit' => '安全巡检、站点加固、运维排查', 'relation' => '独立插件，不影响主题运行。', 'fee' => '开源免费' ),
                    array( 'slug' => 'qilingtools', 'file' => 'qilingtools/qilingtools.php', 'name' => '启灵小工具', 'desc' => '侧边栏工具箱、多平台 API、独立工具项与原生 Widget 投放。', 'fit' => '工具集合、侧边栏增强、快捷入口', 'relation' => '独立插件，可投放在主题小工具区域。', 'fee' => '免费' ),
                ),
            ),
        );
    }

    private function get_ecosystem_plugin_index() {
        $index = array();

        foreach ( $this->get_ecosystem_plugin_groups() as $group ) {
            if ( empty( $group['plugins'] ) || ! is_array( $group['plugins'] ) ) {
                continue;
            }

            foreach ( $group['plugins'] as $plugin ) {
                if ( ! is_array( $plugin ) || empty( $plugin['slug'] ) ) {
                    continue;
                }

                $index[ (string) $plugin['slug'] ] = $plugin;
            }
        }

        return $index;
    }

    private function get_standalone_open_source_plugins() {
        return array(
            array( 'slug' => 'wp-ai-chat', 'file' => 'wp-ai-chat/wp-ai-chat.php', 'name' => '启灵AI助手', 'desc' => '站内问答、会话管理、提示词库、知识库、文章生成、总结和演示文稿生成。', 'fit' => '在线客服、内容生产、知识库问答', 'relation' => '完全开源免费，不属于启灵主题生态，也不在启灵主题售后范围内。', 'fee' => '开源免费' ),
            array( 'slug' => 'qilingcoupon', 'file' => 'qilingcoupon/qilingcoupon.php', 'name' => '启灵淘宝客', 'desc' => '淘宝、京东优惠推广和商品优惠券。', 'fit' => '导购站、优惠券站、联盟推广', 'relation' => '完全开源免费，不属于启灵主题生态，也不在启灵主题售后范围内。', 'fee' => '开源免费' ),
        );
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
                return __( '已启用', 'developer-starter' );
            case 'inactive':
                return __( '已安装未启用', 'developer-starter' );
            case 'missing':
                return __( '未安装', 'developer-starter' );
        }

        return __( '未检测', 'developer-starter' );
    }

    private function get_advanced_settings_url( $args = array() ) {
        return add_query_arg(
            array_merge(
                array(
                    'page' => 'developer-starter-settings',
                    'tab'  => 'advanced',
                ),
                is_array( $args ) ? $args : array()
            ),
            admin_url( 'admin.php' )
        );
    }
}
