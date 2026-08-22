<?php
/**
 * 课程培训招生页创建器类
 *
 * 当用户选择"课程培训招生页"模板创建页面时，自动填充预设模块内容
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
 * 课程培训招生页创建器类
 */
class Course_Enrollment_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-course-enrollment.php';
    protected const AJAX_ACTION = 'fill_course_enrollment_modules';
    protected const FILLED_META_KEY = '_course_enrollment_modules_filled';

    /**
     * 获取课程培训招生页默认模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $page_title = get_the_title( $page_id );
        if ( empty( $page_title ) ) {
            $page_title = __( '课程培训招生页', 'developer-starter' );
        }

        $default_modules = array(
            // 模块1：招生首屏
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout'       => 'slider',
                    'banner_height'       => 'large',
                    'banner_bg_color'     => 'linear-gradient(135deg, #0f172a 0%, #2563eb 100%)',
                    'banner_slides'       => array(
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => $page_title,
                            'subtitle'       => __( '系统课程 + 实战项目 + 导师辅导，帮助你从入门到可落地应用。', 'developer-starter' ),
                            'btn_text'       => __( '立即报名', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#1d4ed8',
                        ),
                        array(
                            'media_type'     => 'image',
                            'image'          => '',
                            'title'          => __( '小班教学，阶段测评', 'developer-starter' ),
                            'subtitle'       => __( '每期名额有限，按学习目标分班，确保高质量反馈。', 'developer-starter' ),
                            'btn_text'       => __( '查看课程大纲', 'developer-starter' ),
                            'btn_url'        => '#',
                            'btn_bg_color'   => '#ffffff',
                            'btn_text_color' => '#1d4ed8',
                        ),
                    ),
                    'show_stats_bar'      => '1',
                    'stats_data'          => array(
                        array(
                            'icon'   => '📚',
                            'number' => '48节',
                            'label'  => __( '系统课程', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '🧪',
                            'number' => '8个',
                            'label'  => __( '实战项目', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '👩‍🏫',
                            'number' => '1v1',
                            'label'  => __( '导师答疑', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                        array(
                            'icon'   => '🎯',
                            'number' => '92%',
                            'label'  => __( '完课率', 'developer-starter' ),
                            'color'  => '#ffffff',
                        ),
                    ),
                    'banner_wave_enable'  => '0',
                ),
            ),

            // 模块2：报名倒计时
            array(
                'type' => 'countdown',
                'data' => array(
                    'countdown_title'           => __( '本期招生倒计时', 'developer-starter' ),
                    'countdown_subtitle'        => __( '限额开班', 'developer-starter' ),
                    'countdown_desc'            => __( '名额满后即止，建议提前提交申请并完成测评。', 'developer-starter' ),
                    'countdown_bg_color'        => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)',
                    'countdown_title_color'     => '#ffffff',
                    'countdown_subtitle_color'  => 'rgba(255,255,255,0.85)',
                    'countdown_desc_color'      => 'rgba(255,255,255,0.82)',
                    'countdown_image'           => '',
                    'countdown_date'            => '2026-05-30 23:59:59',
                    'countdown_timer_bg'        => 'rgba(255,255,255,0.16)',
                    'countdown_timer_color'     => '#ffffff',
                    'countdown_btn_text'        => __( '提交报名申请', 'developer-starter' ),
                    'countdown_btn_link'        => '#',
                    'countdown_btn_bg'          => '#ffffff',
                    'countdown_btn_text_color'  => '#1d4ed8',
                    'module_padding_top'        => '80px',
                    'module_padding_bottom'     => '80px',
                ),
            ),

            // 模块3：课程大纲
            array(
                'type' => 'curriculum',
                'data' => array(
                    'curriculum_title'         => __( '课程大纲', 'developer-starter' ),
                    'curriculum_subtitle'      => __( '从底层认知到实战交付，逐步构建完整能力', 'developer-starter' ),
                    'curriculum_bg_color'      => '#ffffff',
                    'curriculum_primary_color' => '#2563eb',
                    'curriculum_items'         => array(
                        array(
                            'title'   => __( '阶段一：核心基础与工具链', 'developer-starter' ),
                            'meta'    => __( '第1-2周 | 8节', 'developer-starter' ),
                            'content' => __( '<p>掌握核心概念、开发环境、效率工具与学习路径设计。</p>', 'developer-starter' ),
                            'open'    => 'yes',
                        ),
                        array(
                            'title'   => __( '阶段二：案例拆解与模块化实践', 'developer-starter' ),
                            'meta'    => __( '第3-5周 | 16节', 'developer-starter' ),
                            'content' => __( '<p>通过真实案例完成模块搭建、页面编排与数据配置。</p>', 'developer-starter' ),
                            'open'    => 'no',
                        ),
                        array(
                            'title'   => __( '阶段三：项目实战与复盘优化', 'developer-starter' ),
                            'meta'    => __( '第6-8周 | 24节', 'developer-starter' ),
                            'content' => __( '<p>完成结课项目，进行导师评审、问题复盘与作品打磨。</p>', 'developer-starter' ),
                            'open'    => 'no',
                        ),
                    ),
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块4：导师团队
            array(
                'type' => 'team',
                'data' => array(
                    'team_title'               => __( '导师团队', 'developer-starter' ),
                    'team_subtitle'            => __( '一线实战导师带学，聚焦可落地能力培养', 'developer-starter' ),
                    'team_columns'             => '3',
                    'team_members'             => array(
                        array(
                            'avatar'   => '',
                            'name'     => __( '陈老师', 'developer-starter' ),
                            'position' => __( '课程主理人', 'developer-starter' ),
                            'desc'     => __( '10年教学与项目经验，擅长把复杂内容结构化讲清。', 'developer-starter' ),
                            'wechat'   => '',
                            'email'    => 'mentor1@example.com',
                            'phone'    => '',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '林老师', 'developer-starter' ),
                            'position' => __( '实战导师', 'developer-starter' ),
                            'desc'     => __( '长期负责企业项目交付，聚焦方法论到实操转化。', 'developer-starter' ),
                            'wechat'   => '',
                            'email'    => 'mentor2@example.com',
                            'phone'    => '',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '赵老师', 'developer-starter' ),
                            'position' => __( '学习顾问', 'developer-starter' ),
                            'desc'     => __( '跟进学习计划、作业反馈与阶段目标管理。', 'developer-starter' ),
                            'wechat'   => '',
                            'email'    => 'mentor3@example.com',
                            'phone'    => '',
                        ),
                    ),
                    'module_bg_type'           => 'color',
                    'module_bg_color'          => '#f8fafc',
                    'module_padding_top'       => '80px',
                    'module_padding_bottom'    => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块5：报名流程
            array(
                'type' => 'process',
                'data' => array(
                    'process_title'            => __( '报名流程', 'developer-starter' ),
                    'process_subtitle'         => __( '四步完成报名与入学准备', 'developer-starter' ),
                    'process_mode'             => 'standard',
                    'process_items'            => array(
                        array(
                            'icon'  => '1',
                            'title' => __( '提交报名信息', 'developer-starter' ),
                            'desc'  => __( '填写基础信息与学习目标，完成课程适配初筛。', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '2',
                            'title' => __( '顾问回访沟通', 'developer-starter' ),
                            'desc'  => __( '确认学习背景、时间安排和班型建议。', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '3',
                            'title' => __( '完成入学测评', 'developer-starter' ),
                            'desc'  => __( '根据测评结果生成个性化学习计划。', 'developer-starter' ),
                        ),
                        array(
                            'icon'  => '4',
                            'title' => __( '开班学习', 'developer-starter' ),
                            'desc'  => __( '进入班级群与学习平台，正式开始训练营。', 'developer-starter' ),
                        ),
                    ),
                    'module_bg_type'           => 'color',
                    'module_bg_color'          => '#ffffff',
                    'module_padding_top'       => '80px',
                    'module_padding_bottom'    => '80px',
                    'enable_staggered_animation' => 'yes',
                ),
            ),

            // 模块6：学员反馈
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'      => __( '学员反馈', 'developer-starter' ),
                    'testimonials_subtitle'   => __( '真实学习体验与成果复盘', 'developer-starter' ),
                    'testimonials_layout'     => 'grid',
                    'testimonials_columns'    => '3',
                    'show_rating_summary'     => 'yes',
                    'total_reviews'           => '2,600+',
                    'average_rating'          => '4.9',
                    'testimonials_bg_color'   => '#ffffff',
                    'module_padding_top'      => '80px',
                    'module_padding_bottom'   => '80px',
                    'testimonials_items'      => array(
                        array(
                            'avatar'   => '',
                            'name'     => __( '王同学', 'developer-starter' ),
                            'position' => __( '转岗学员', 'developer-starter' ),
                            'content'  => __( '课程节奏合理，作业反馈很及时，学习路径非常清晰。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'xiaohongshu',
                            'date'     => '2026-03-05',
                            'verified' => 'verified',
                            'card_bg'  => '#ffffff',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '刘同学', 'developer-starter' ),
                            'position' => __( '在职进修', 'developer-starter' ),
                            'content'  => __( '实战项目非常有帮助，结课后可直接应用到当前工作。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'weibo',
                            'date'     => '2026-03-12',
                            'verified' => 'vip',
                            'card_bg'  => '#ffffff',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => __( '张同学', 'developer-starter' ),
                            'position' => __( '应届学员', 'developer-starter' ),
                            'content'  => __( '导师答疑很专业，复盘模板实用，作品集提升明显。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'google',
                            'date'     => '2026-03-18',
                            'verified' => 'verified',
                            'card_bg'  => '#ffffff',
                        ),
                    ),
                ),
            ),

            // 模块7：常见问题
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'             => __( '招生常见问题', 'developer-starter' ),
                    'faq_subtitle'          => __( '关于开班、学费、学习方式与服务保障', 'developer-starter' ),
                    'module_bg_color'       => '#f8fafc',
                    'module_padding_top'    => '80px',
                    'module_padding_bottom' => '80px',
                    'faq_items'             => array(
                        array(
                            'question' => __( '零基础可以报名吗？', 'developer-starter' ),
                            'answer'   => __( '可以。课程包含基础模块与分层任务，顾问会帮助你匹配合适班型。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '课程是录播还是直播？', 'developer-starter' ),
                            'answer'   => __( '以直播+录播结合形式进行，支持课后回放与作业点评。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '学习周期多久？', 'developer-starter' ),
                            'answer'   => __( '标准周期为 8 周，具体会根据班型与学习安排调整。', 'developer-starter' ),
                        ),
                        array(
                            'question' => __( '报名后是否有学习服务？', 'developer-starter' ),
                            'answer'   => __( '包含班主任跟进、导师答疑、作业反馈与结课复盘。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),

            // 模块8：行动召唤
            array(
                'type' => 'cta',
                'data' => array(
                    'cta_title'             => __( '准备好加入下一期开班了吗？', 'developer-starter' ),
                    'cta_subtitle'          => __( '提交申请后，学习顾问会在 24 小时内联系你。', 'developer-starter' ),
                    'cta_button_text'       => __( '立即报名', 'developer-starter' ),
                    'cta_button_url'        => '#',
                    'cta_bg_type'           => 'color',
                    'cta_bg_color'          => 'linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%)',
                    'module_padding_top'    => '96px',
                    'module_padding_bottom' => '96px',
                ),
            ),
        );

        return $default_modules;
    }
}
