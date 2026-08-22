<?php
/**
 * 个人IP主页创建器类
 *
 * 当用户选择"个人IP主页"模板创建页面时，自动填充预设模块内容
 *
 * @package Developer_Starter
 * @since 1.0.5
 */

namespace Developer_Starter\Core;

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 个人IP主页创建器类
 */
class Personal_IP_Home_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-personal-ip-home.php';
    protected const AJAX_ACTION = 'fill_personal_ip_home_modules';
    protected const FILLED_META_KEY = '_personal_ip_home_modules_filled';

    /**
     * 获取个人IP主页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '个人IP主页', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：个人名片
            array(
                'type' => 'about_me_card',
                'data' => array(
                    'about_title'         => __( '个人IP名片', 'developer-starter' ),
                    'about_subtitle'      => __( '让访客在 10 秒内知道你是谁、做什么、能提供什么价值', 'developer-starter' ),
                    'about_avatar'        => '',
                    'about_name'          => $page_title,
                    'about_role'          => __( '创作者 / 顾问 / 产品实践者', 'developer-starter' ),
                    'about_intro'         => __( '专注内容与产品增长，持续分享可落地的方法论、案例拆解和实战经验。', 'developer-starter' ),
                    'about_now'           => __( '最近在打磨一套可复用的网站搭建与内容增长流程。', 'developer-starter' ),
                    'about_show_now'      => 'yes',
                    'about_location'      => __( '上海', 'developer-starter' ),
                    'about_website'       => home_url( '/' ),
                    'about_email'         => 'hello@example.com',
                    'about_socials'       => array(
                        array(
                            'label' => __( '微信公众号', 'developer-starter' ),
                            'url'   => '#',
                            'icon'  => '📰',
                        ),
                        array(
                            'label' => 'GitHub',
                            'url'   => 'https://github.com/',
                            'icon'  => '🐙',
                        ),
                        array(
                            'label' => 'X',
                            'url'   => 'https://x.com/',
                            'icon'  => '𝕏',
                        ),
                    ),
                    'about_card_bg'       => '#ffffff',
                    'about_bg_color'      => '#f8fafc',
                    'about_text_color'    => '#0f172a',
                    'about_accent_color'  => '#2563eb',
                    'module_padding_top'  => '64px',
                    'module_padding_bottom' => '64px',
                ),
            ),

            // 模块2：知识点卡
            array(
                'type' => 'knowledge_cards',
                'data' => array(
                    'kc_title'              => __( '方法论与能力地图', 'developer-starter' ),
                    'kc_subtitle'           => __( '把你最擅长的领域拆成可理解、可复用的知识资产', 'developer-starter' ),
                    'kc_columns'            => '3',
                    'kc_show_number'        => 'yes',
                    'kc_show_example'       => 'yes',
                    'kc_show_mistake'       => 'yes',
                    'kc_show_link'          => 'yes',
                    'kc_card_bg'            => '#ffffff',
                    'kc_bg_color'           => '#ffffff',
                    'kc_accent_color'       => '#2563eb',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'kc_items'              => array(
                        array(
                            'term'       => __( '内容定位', 'developer-starter' ),
                            'definition' => __( '先明确你服务的人群和场景，再决定表达形式。', 'developer-starter' ),
                            'importance' => 'high',
                            'example'    => __( '将“做网站”细分为“帮创作者搭建高转化内容站”。', 'developer-starter' ),
                            'mistake'    => __( '定位过宽，导致内容没有记忆点。', 'developer-starter' ),
                            'link_text'  => __( '查看定位清单', 'developer-starter' ),
                            'link_url'   => '#',
                        ),
                        array(
                            'term'       => __( '内容资产化', 'developer-starter' ),
                            'definition' => __( '让每篇内容都能沉淀为可复用的模板或流程。', 'developer-starter' ),
                            'importance' => 'high',
                            'example'    => __( '将实战复盘提炼成 SOP，后续项目直接复用。', 'developer-starter' ),
                            'mistake'    => __( '只追热点，不做体系化沉淀。', 'developer-starter' ),
                            'link_text'  => __( '查看资产化模板', 'developer-starter' ),
                            'link_url'   => '#',
                        ),
                        array(
                            'term'       => __( '转化路径设计', 'developer-starter' ),
                            'definition' => __( '每个页面都要明确下一步行动与承接方式。', 'developer-starter' ),
                            'importance' => 'medium',
                            'example'    => __( '文章结尾设置咨询入口与下载资源，承接潜在客户。', 'developer-starter' ),
                            'mistake'    => __( '有流量但没有明确 CTA。', 'developer-starter' ),
                            'link_text'  => __( '查看转化路径示例', 'developer-starter' ),
                            'link_url'   => '#',
                        ),
                    ),
                ),
            ),

            // 模块3：博客内容
            array(
                'type' => 'blog',
                'data' => array(
                    'blog_title'          => __( '最新内容更新', 'developer-starter' ),
                    'blog_subtitle'       => __( '通过持续输出文章，建立专业认知与信任', 'developer-starter' ),
                    'blog_bg_color'       => '#f8fafc',
                    'blog_title_color'    => '#0f172a',
                    'blog_page_layout'    => 'full',
                    'padding_top'         => 'default',
                    'padding_bottom'      => 'default',
                    'blog_layout_style'   => 'card',
                    'blog_columns'        => '3',
                    'blog_data_source'    => 'latest',
                    'blog_count'          => '6',
                    'blog_orderby'        => 'date',
                    'blog_ignore_sticky'  => 'yes',
                    'blog_show_image'     => 'yes',
                    'blog_image_height'   => '220px',
                    'blog_show_excerpt'   => 'yes',
                    'blog_excerpt_length' => '80',
                    'blog_show_author'    => 'no',
                    'blog_show_date'      => 'yes',
                    'blog_show_category'  => 'yes',
                    'blog_show_tags'      => 'no',
                    'blog_show_views'     => 'yes',
                    'blog_show_reading_time' => 'yes',
                    'blog_show_comments'  => 'no',
                    'blog_read_more_text' => __( '阅读全文', 'developer-starter' ),
                ),
            ),

            // 模块4：读书观影清单
            array(
                'type' => 'media_list',
                'data' => array(
                    'ml_title'              => __( '最近在看 / 在听 / 在学', 'developer-starter' ),
                    'ml_subtitle'           => __( '展示你的内容输入来源，让个人IP更立体', 'developer-starter' ),
                    'ml_filter'             => 'all',
                    'ml_columns'            => '3',
                    'ml_show_note'          => 'yes',
                    'ml_show_link'          => 'yes',
                    'ml_card_bg'            => '#ffffff',
                    'ml_bg_color'           => '#ffffff',
                    'ml_accent_color'       => '#2563eb',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'ml_items'              => array(
                        array(
                            'type'     => 'book',
                            'cover'    => '',
                            'title'    => __( '影响力', 'developer-starter' ),
                            'creator'  => __( 'Robert Cialdini', 'developer-starter' ),
                            'status'   => 'done',
                            'score'    => '9',
                            'progress' => __( '已读完并整理读书笔记', 'developer-starter' ),
                            'link'     => '#',
                            'note'     => __( '对内容选题和转化文案很有启发。', 'developer-starter' ),
                        ),
                        array(
                            'type'     => 'movie',
                            'cover'    => '',
                            'title'    => __( '社交网络', 'developer-starter' ),
                            'creator'  => __( 'David Fincher', 'developer-starter' ),
                            'status'   => 'doing',
                            'score'    => '8',
                            'progress' => __( '二刷中', 'developer-starter' ),
                            'link'     => '#',
                            'note'     => __( '创业叙事与产品决策冲突值得反复看。', 'developer-starter' ),
                        ),
                        array(
                            'type'     => 'music',
                            'cover'    => '',
                            'title'    => 'Discovery',
                            'creator'  => 'Daft Punk',
                            'status'   => 'wish',
                            'score'    => '9',
                            'progress' => __( '准备重听', 'developer-starter' ),
                            'link'     => '#',
                            'note'     => __( '高专注工作时常驻歌单。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块5：时间流更新
            array(
                'type' => 'micro_journal_stream',
                'data' => array(
                    'mjs_title'             => __( '最近动态', 'developer-starter' ),
                    'mjs_subtitle'          => __( '用轻量时间流记录你的持续行动与阶段成果', 'developer-starter' ),
                    'mjs_limit'             => '6',
                    'mjs_show_image'        => 'no',
                    'mjs_card_bg'           => '#ffffff',
                    'mjs_bg_color'          => '#f8fafc',
                    'mjs_line_color'        => '#2563eb',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'mjs_items'             => array(
                        array(
                            'date'     => '2026-03-20',
                            'time'     => '21:15',
                            'content'  => __( '完成个人IP主页模板迭代，新增知识卡与时间流模块组合。', 'developer-starter' ),
                            'mood'     => '🚀',
                            'location' => __( '工作室', 'developer-starter' ),
                            'image'    => '',
                            'link'     => '#',
                        ),
                        array(
                            'date'     => '2026-03-18',
                            'time'     => '10:30',
                            'content'  => __( '发布一篇关于“内容资产化”的实战文章，收到多条读者反馈。', 'developer-starter' ),
                            'mood'     => '📝',
                            'location' => __( '上海', 'developer-starter' ),
                            'image'    => '',
                            'link'     => '#',
                        ),
                        array(
                            'date'     => '2026-03-15',
                            'time'     => '16:05',
                            'content'  => __( '和两位创作者做了共创直播，整理出下一期选题清单。', 'developer-starter' ),
                            'mood'     => '🎙️',
                            'location' => __( '线上会议', 'developer-starter' ),
                            'image'    => '',
                            'link'     => '#',
                        ),
                    ),
                ),
            ),

            // 模块6：合作评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '合作伙伴怎么说', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '来自客户与读者的真实反馈', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '120+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'      => '',
                            'name'        => __( '陈雨', 'developer-starter' ),
                            'position'    => __( '品牌主理人', 'developer-starter' ),
                            'content'     => __( '主页结构非常清晰，访客对我业务的理解速度明显提升。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'xiaohongshu',
                            'date'        => '2026-02-26',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '徐晨', 'developer-starter' ),
                            'position'    => __( '内容运营负责人', 'developer-starter' ),
                            'content'     => __( '从人设到转化路径都能一键搭起来，后续改文案也很方便。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'weibo',
                            'date'        => '2026-03-08',
                            'verified'    => 'vip',
                            'card_bg'     => '#ffffff',
                        ),
                        array(
                            'avatar'      => '',
                            'name'        => __( '赵珂', 'developer-starter' ),
                            'position'    => __( '独立开发者', 'developer-starter' ),
                            'content'     => __( '模板不是死板页面，模块都可替换编辑，效率很高。', 'developer-starter' ),
                            'rating'      => '5',
                            'source'      => 'google',
                            'date'        => '2026-03-14',
                            'verified'    => 'verified',
                            'card_bg'     => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块7：友链推荐
            array(
                'type' => 'friendly_links',
                'data' => array(
                    'fl_title'              => __( '友链与推荐', 'developer-starter' ),
                    'fl_subtitle'           => __( '展示你的合作伙伴、内容同路人和高质量参考站点', 'developer-starter' ),
                    'fl_columns'            => '3',
                    'fl_show_desc'          => 'yes',
                    'fl_show_domain'        => 'yes',
                    'fl_card_bg'            => '#ffffff',
                    'fl_bg_color'           => '#f8fafc',
                    'fl_accent_color'       => '#2563eb',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'fl_items'              => array(
                        array(
                            'name'   => __( '启灵主题文档', 'developer-starter' ),
                            'url'    => '#',
                            'logo'   => '',
                            'desc'   => __( '模板与模块使用指南', 'developer-starter' ),
                            'tag'    => __( '文档', 'developer-starter' ),
                            'target' => '_blank',
                        ),
                        array(
                            'name'   => __( '设计灵感站', 'developer-starter' ),
                            'url'    => '#',
                            'logo'   => '',
                            'desc'   => __( '高质量视觉与交互灵感收集', 'developer-starter' ),
                            'tag'    => __( '设计', 'developer-starter' ),
                            'target' => '_blank',
                        ),
                        array(
                            'name'   => __( '独立开发手册', 'developer-starter' ),
                            'url'    => '#',
                            'logo'   => '',
                            'desc'   => __( '产品增长与运营实践笔记', 'developer-starter' ),
                            'tag'    => __( '增长', 'developer-starter' ),
                            'target' => '_blank',
                        ),
                    ),
                ),
            ),

            // 模块8：CTA
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'            => __( '想一起共创一个更有辨识度的个人品牌站吗？', 'developer-starter' ),
                    'cta_subtitle'         => __( '欢迎发起合作咨询，我会基于你的定位给出页面结构与内容建议。', 'developer-starter' ),
                    'cta_button_text'      => __( '发起咨询', 'developer-starter' ),
                    'cta_button_url'       => '/contact/',
                    'cta_bg_type'          => 'color',
                    'cta_bg_color'         => 'linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%)',
                    'module_padding_top'   => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
