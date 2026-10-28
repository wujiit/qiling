<?php
/**
 * 简历页面创建器类
 *
 * 提供简历页面模板和预设模块数据
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
 * 简历页面创建器类
 */
class Resume_Page_Creator extends Page_Creator_Base {

    protected const TEMPLATE = 'templates/template-resume.php';
    protected const AJAX_ACTION = 'fill_resume_page_modules';
    protected const FILLED_META_KEY = '_resume_page_modules_filled';

    /**
     * 获取默认简历模块
     *
     * @param int $page_id 页面ID
     * @return array
     */
    protected function get_default_modules( $page_id ) {
        $default_modules = array(
            // 个人简历首屏
            array(
                'type' => 'resume_hero',
                'data' => array(
                    'rh_style_variant'=> 'classic',
                    'rh_layout'       => 'center',
                    'rh_height'       => '100vh',
                    'rh_bg_color'     => 'linear-gradient(135deg, #0f172a 0%, #1e3a8a 58%, #065f46 100%)',
                    'rh_bg_image'     => '',
                    'rh_overlay_color'=> '',
                    'rh_intro_badge'  => 'HELLO',
                    'rh_accent_color' => '#34d399',
                    'rh_avatar'       => '',
                    'rh_avatar_style' => 'circle',
                    'rh_avatar_size'  => '150px',
                    'rh_name'         => __( '张三', 'qiling' ),
                    'rh_name_color'   => '#ffffff',
                    'rh_titles'       => __( "前端开发工程师\n全栈开发者\nUI/UX设计师", 'qiling' ),
                    'rh_title_color'  => '',
                    'rh_typewriter'   => '1',
                    'rh_bio'          => __( '热爱技术，专注于Web前端开发和用户体验设计。5年以上开发经验，熟练掌握React、Vue等主流框架。', 'qiling' ),
                    'rh_bio_color'    => 'rgba(255,255,255,0.8)',
                    'rh_location'     => __( '北京市', 'qiling' ),
                    'rh_email'        => 'example@email.com',
                    'rh_phone'        => '+86 138 8888 8888',
                    'rh_website'      => '',
                    'rh_profile_facts'=> array(),
                    'rh_socials'      => array(
                        array( 'name' => 'GitHub', 'icon' => 'github', 'link' => '#' ),
                        array( 'name' => 'LinkedIn', 'icon' => 'linkedin', 'link' => '#' ),
                        array( 'name' => __( '微博', 'qiling' ), 'icon' => 'weibo', 'link' => '#' ),
                    ),
                    'rh_btn1_text'    => __( '下载简历', 'qiling' ),
                    'rh_btn1_link'    => '#',
                    'rh_btn1_style'   => 'solid',
                    'rh_btn2_text'    => __( '联系我', 'qiling' ),
                    'rh_btn2_link'    => '#contact',
                    'rh_btn2_style'   => 'outline',
                ),
            ),

            // 服务/我能做什么
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'    => __( '我能做什么', 'qiling' ),
                    'services_subtitle' => __( '专业技能服务', 'qiling' ),
                    'services_items'    => array(
                        array(
                            'icon'  => 'icon-laptop',
                            'title' => __( 'Web前端开发', 'qiling' ),
                            'desc'  => __( '精通HTML5、CSS3、JavaScript，熟练使用React、Vue等框架进行企业级应用开发', 'qiling' ),
                        ),
                        array(
                            'icon'  => 'icon-palette',
                            'title' => __( 'UI/UX设计', 'qiling' ),
                            'desc'  => __( '具备良好的审美和用户体验意识，能够使用Figma、Sketch进行界面设计', 'qiling' ),
                        ),
                        array(
                            'icon'  => '📱',
                            'title' => __( '响应式开发', 'qiling' ),
                            'desc'  => __( '构建适配各种设备的响应式网站，确保在手机、平板、电脑上都有良好体验', 'qiling' ),
                        ),
                        array(
                            'icon'  => 'icon-flash',
                            'title' => __( '性能优化', 'qiling' ),
                            'desc'  => __( '专注于Web性能优化，包括代码分割、懒加载、缓存策略等技术提升用户体验', 'qiling' ),
                        ),
                    ),
                ),
            ),

            // 技能进度条
            array(
                'type' => 'skills',
                'data' => array(
                    'skills_title'        => __( '专业技能', 'qiling' ),
                    'skills_subtitle'     => __( '技术栈与熟练程度', 'qiling' ),
                    'skills_layout'       => 'double',
                    'skills_style'        => 'bar',
                    'skills_bar_height'   => '10px',
                    'skills_bar_color'    => 'linear-gradient(135deg, #2563eb, #10b981)',
                    'skills_bar_bg'       => '#e2e8f0',
                    'skills_show_percent' => '1',
                    'skills_animate'      => '1',
                    'skills_group1_title' => __( '前端技术', 'qiling' ),
                    'skills_group1'       => array(
                        array( 'name' => 'HTML5 / CSS3', 'percent' => '95' ),
                        array( 'name' => 'JavaScript / TypeScript', 'percent' => '90' ),
                        array( 'name' => 'React / Vue', 'percent' => '85' ),
                        array( 'name' => 'Node.js', 'percent' => '75' ),
                    ),
                    'skills_group2_title' => __( '设计工具', 'qiling' ),
                    'skills_group2'       => array(
                        array( 'name' => 'Figma', 'percent' => '90' ),
                        array( 'name' => 'Photoshop', 'percent' => '80' ),
                        array( 'name' => 'Illustrator', 'percent' => '70' ),
                        array( 'name' => 'After Effects', 'percent' => '60' ),
                    ),
                ),
            ),

            // 经历时间线
            array(
                'type' => 'experience_timeline',
                'data' => array(
                    'exp_title'         => __( '教育与工作经历', 'qiling' ),
                    'exp_subtitle'      => '',
                    'exp_layout'        => 'double',
                    'exp_line_color'    => 'var(--color-primary, #3b82f6)',
                    'exp_dot_style'     => 'dot',
                    'exp_group1_title'  => __( '教育经历', 'qiling' ),
                    'exp_group1_icon'   => 'icon-education',
                    'exp_group1'        => array(
                        array(
                            'period'       => '2016 - 2020',
                            'title'        => __( '计算机科学与技术 学士', 'qiling' ),
                            'company'      => __( '清华大学', 'qiling' ),
                            'company_link' => '',
                            'desc'         => __( 'GPA 3.8/4.0，获得优秀毕业生称号，主修软件工程方向', 'qiling' ),
                            'tags'         => __( '数据结构,算法,软件工程', 'qiling' ),
                        ),
                        array(
                            'period'       => '2020 - 2022',
                            'title'        => __( '软件工程 硕士', 'qiling' ),
                            'company'      => __( '北京大学', 'qiling' ),
                            'company_link' => '',
                            'desc'         => __( '专注于Web前端技术研究，发表SCI论文2篇', 'qiling' ),
                            'tags'         => __( '前端架构,性能优化', 'qiling' ),
                        ),
                    ),
                    'exp_group2_title'  => __( '工作经验', 'qiling' ),
                    'exp_group2_icon'   => 'icon-briefcase',
                    'exp_group2'        => array(
                        array(
                            'period'       => '2022 - 至今',
                            'title'        => __( '高级前端工程师', 'qiling' ),
                            'company'      => __( '字节跳动', 'qiling' ),
                            'company_link' => 'https://bytedance.com',
                            'desc'         => __( '负责抖音Web端核心业务开发，带领5人前端团队，完成多个重点项目的技术攻关', 'qiling' ),
                            'tags'         => __( 'React,TypeScript,Node.js,微前端', 'qiling' ),
                        ),
                        array(
                            'period'       => '2020 - 2022',
                            'title'        => __( '前端开发工程师', 'qiling' ),
                            'company'      => __( '阿里巴巴', 'qiling' ),
                            'company_link' => 'https://alibaba.com',
                            'desc'         => __( '参与淘宝商家后台开发，优化页面性能，将首屏加载时间降低40%', 'qiling' ),
                            'tags'         => __( 'Vue,Webpack,小程序', 'qiling' ),
                        ),
                    ),
                ),
            ),

            // 数字统计
            array(
                'type' => 'stats',
                'data' => array(
                    'stats_title'    => '',
                    'stats_bg_color' => 'linear-gradient(135deg, #1d4ed8 0%, #059669 100%)',
                    'stats_items'    => array(
                        array( 'number' => '5+', 'label' => __( '年工作经验', 'qiling' ) ),
                        array( 'number' => '50+', 'label' => __( '完成项目', 'qiling' ) ),
                        array( 'number' => '20+', 'label' => __( '合作客户', 'qiling' ) ),
                        array( 'number' => '99%', 'label' => __( '客户满意度', 'qiling' ) ),
                    ),
                ),
            ),

            // 作品集
            array(
                'type' => 'gallery',
                'data' => array(
                    'gallery_title'    => __( '精选作品', 'qiling' ),
                    'gallery_subtitle' => __( '部分代表性项目展示', 'qiling' ),
                    'gallery_columns'  => '3',
                    'gallery_items'    => array(
                        array(
                            'image'   => '',
                            'title'   => __( '电商平台设计', 'qiling' ),
                            'desc'    => 'React + Node.js',
                            'url'     => '#',
                        ),
                        array(
                            'image'   => '',
                            'title'   => __( '企业官网开发', 'qiling' ),
                            'desc'    => 'Vue + WordPress',
                            'url'     => '#',
                        ),
                        array(
                            'image'   => '',
                            'title'   => __( '移动端App设计', 'qiling' ),
                            'desc'    => 'React Native',
                            'url'     => '#',
                        ),
                    ),
                ),
            ),

            // 客户评价
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'    => __( '客户评价', 'qiling' ),
                    'testimonials_subtitle' => __( '合作伙伴的真实反馈', 'qiling' ),
                    'testimonials_items'    => array(
                        array(
                            'avatar'  => '',
                            'name'    => __( '李经理', 'qiling' ),
                            'title'   => __( '某科技公司 产品总监', 'qiling' ),
                            'content' => __( '张三是我合作过的最专业的前端开发者之一，代码质量高，交付准时，沟通顺畅。', 'qiling' ),
                            'rating'  => '5',
                        ),
                        array(
                            'avatar'  => '',
                            'name'    => __( '王总', 'qiling' ),
                            'title'   => __( '某电商平台 CEO', 'qiling' ),
                            'content' => __( '项目完成得非常出色，页面性能优化后用户留存率提升了30%，强烈推荐！', 'qiling' ),
                            'rating'  => '5',
                        ),
                        array(
                            'avatar'  => '',
                            'name'    => __( '刘设计', 'qiling' ),
                            'title'   => __( '某设计工作室 创始人', 'qiling' ),
                            'content' => __( '对UI设计有独到的见解，能够很好地将设计稿还原，并提出有价值的改进建议。', 'qiling' ),
                            'rating'  => '5',
                        ),
                    ),
                ),
            ),

            // 联系表单
            array(
                'type' => 'contact',
                'data' => array(
                    'contact_title'      => __( '联系我', 'qiling' ),
                    'contact_subtitle'   => __( '有项目合作或工作机会？欢迎联系', 'qiling' ),
                    'contact_show_form'  => 'yes',
                    'contact_show_info'  => 'yes',
                    'contact_show_map'   => 'no',
                    'contact_email'      => 'example@email.com',
                    'contact_phone'      => '+86 138 8888 8888',
                    'contact_address'    => __( '北京市朝阳区xxx街道xxx号', 'qiling' ),
                    'contact_bg_color'   => '#f8fafc',
                ),
            ),
        );

        return $default_modules;
    }
}
