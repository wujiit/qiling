<?php
/**
 * Meta Boxes - Page Modules Builder
 * 
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes {

    private $module_fields = array();

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        $this->init_module_fields();
    }

    private function init_module_fields() {
        $this->module_fields = array(
            'banner' => array(
                'title' => '首屏Banner',
                'fields' => array(
                    array( 'id' => 'banner_layout', 'label' => '布局', 'type' => 'select', 'options' => array( 'slider' => '轮播图', 'image_text' => '图文布局' ), 'default' => 'slider' ),
                    array( 'id' => 'banner_height', 'label' => '高度', 'type' => 'select', 'options' => array( 'full' => '全屏', 'large' => '80%', 'medium' => '60%' ), 'default' => 'full' ),
                    array( 'id' => 'banner_image_position', 'label' => '图片位置', 'type' => 'select', 'options' => array( 'right' => '右侧', 'left' => '左侧' ), 'default' => 'right' ),
                    array( 
                        'id' => 'banner_slides', 
                        'label' => '幻灯片', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'image', 'label' => '图片', 'type' => 'image' ),
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'subtitle', 'label' => '副标题', 'type' => 'text' ),
                            array( 'id' => 'btn_text', 'label' => '按钮文字', 'type' => 'text' ),
                            array( 'id' => 'btn_url', 'label' => '按钮链接', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'image' => '', 'title' => '专业企业解决方案', 'subtitle' => '助力企业数字化转型，提供一站式服务', 'btn_text' => '了解更多', 'btn_url' => '#' ),
                            array( 'image' => '', 'title' => '10年行业深耕', 'subtitle' => '服务超过500家企业客户', 'btn_text' => '查看案例', 'btn_url' => '#' ),
                        ),
                    ),
                ),
            ),
            'services' => array(
                'title' => '服务展示',
                'fields' => array(
                    array( 'id' => 'services_title', 'label' => '标题', 'type' => 'text', 'default' => '我们的服务' ),
                    array( 'id' => 'services_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '为企业提供全方位的专业服务' ),
                    array( 
                        'id' => 'services_items', 
                        'label' => '服务项目', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'icon', 'label' => '图标', 'type' => 'text' ),
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '描述', 'type' => 'textarea' ),
                            array( 'id' => 'link', 'label' => '链接', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'icon' => '01', 'title' => '产品研发', 'desc' => '提供专业的产品研发服务，从需求分析到产品上线全流程支持', 'link' => '#' ),
                            array( 'icon' => '02', 'title' => '解决方案', 'desc' => '针对不同行业提供定制化解决方案，满足企业个性化需求', 'link' => '#' ),
                            array( 'icon' => '03', 'title' => '技术支持', 'desc' => '7x24小时技术支持服务，快速响应解决技术问题', 'link' => '#' ),
                            array( 'icon' => '04', 'title' => '数据分析', 'desc' => '专业数据分析团队，助力企业数据驱动决策', 'link' => '#' ),
                        ),
                    ),
                ),
            ),
            'features' => array(
                'title' => '企业优势',
                'fields' => array(
                    array( 'id' => 'features_title', 'label' => '标题', 'type' => 'text', 'default' => '为什么选择我们' ),
                    array( 'id' => 'features_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '我们的核心竞争优势' ),
                    array( 
                        'id' => 'features_items', 
                        'label' => '优势项目', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'icon', 'label' => '图标', 'type' => 'text' ),
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '描述', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'icon' => '+', 'title' => '专业团队', 'desc' => '拥有10年行业经验的专业团队' ),
                            array( 'icon' => '+', 'title' => '优质服务', 'desc' => '7x24小时全天候服务支持' ),
                            array( 'icon' => '+', 'title' => '价格透明', 'desc' => '无隐形消费，明码标价' ),
                            array( 'icon' => '+', 'title' => '品质保障', 'desc' => 'ISO9001质量管理体系认证' ),
                        ),
                    ),
                ),
            ),
            'stats' => array(
                'title' => '数据统计',
                'fields' => array(
                    array( 'id' => 'stats_bg_image', 'label' => '背景图', 'type' => 'image' ),
                    array( 'id' => 'stats_text_align', 'label' => '文字位置', 'type' => 'select', 'options' => array( 'left' => '左对齐', 'center' => '居中', 'right' => '右对齐' ), 'default' => 'center' ),
                    array( 
                        'id' => 'stats_items', 
                        'label' => '统计数据', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'number', 'label' => '数字', 'type' => 'text' ),
                            array( 'id' => 'label', 'label' => '标签', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'number' => '500', 'label' => '服务客户' ),
                            array( 'number' => '10', 'label' => '年行业经验' ),
                            array( 'number' => '50', 'label' => '专业团队' ),
                            array( 'number' => '99', 'label' => '客户满意度' ),
                        ),
                    ),
                ),
            ),
            'cta' => array(
                'title' => 'CTA按钮',
                'fields' => array(
                    array( 'id' => 'cta_title', 'label' => '标题', 'type' => 'text', 'default' => '准备好开始了吗？' ),
                    array( 'id' => 'cta_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '立即联系我们，获取专业方案和报价' ),
                    array( 'id' => 'cta_button_text', 'label' => '按钮文字', 'type' => 'text', 'default' => '免费咨询' ),
                    array( 'id' => 'cta_button_url', 'label' => '按钮链接', 'type' => 'text', 'default' => '#contact' ),
                ),
            ),
            'clients' => array(
                'title' => '合作客户',
                'fields' => array(
                    array( 'id' => 'clients_title', 'label' => '标题', 'type' => 'text', 'default' => '合作客户' ),
                    array( 'id' => 'clients_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'clients_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'clients_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'clients_columns', 'label' => '每行列数', 'type' => 'select', 'options' => array( '4' => '4列', '5' => '5列', '6' => '6列', '8' => '8列' ), 'default' => '6' ),
                    array( 'id' => 'clients_auto_scroll', 'label' => '自动滚动', 'type' => 'select', 'options' => array( '' => '关闭', '1' => '开启' ), 'default' => '' ),
                    array( 'id' => 'clients_scroll_speed', 'label' => '滚动速度(秒)', 'type' => 'number', 'default' => '30' ),
                    array( 'id' => 'clients_logo_style', 'label' => 'Logo样式', 'type' => 'select', 'options' => array( 'normal' => '彩色', 'grayscale' => '灰度(悬停变彩)' ), 'default' => 'normal' ),
                    array( 'id' => 'clients_logo_height', 'label' => 'Logo高度', 'type' => 'text', 'default' => '50px' ),
                    array( 'id' => 'clients_card_bg', 'label' => '卡片背景色', 'type' => 'text', 'default' => '#ffffff' ),
                    array( 'id' => 'clients_show_name', 'label' => '显示名称', 'type' => 'select', 'options' => array( '' => '不显示', '1' => '显示' ), 'default' => '' ),
                    array( 
                        'id' => 'clients_items', 
                        'label' => '客户列表', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'logo', 'label' => 'Logo', 'type' => 'image' ),
                            array( 'id' => 'name', 'label' => '名称', 'type' => 'text' ),
                            array( 'id' => 'link', 'label' => '链接(可选)', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'logo' => '', 'name' => '华为' ),
                            array( 'logo' => '', 'name' => '阿里巴巴' ),
                            array( 'logo' => '', 'name' => '腾讯' ),
                            array( 'logo' => '', 'name' => '百度' ),
                            array( 'logo' => '', 'name' => '京东' ),
                            array( 'logo' => '', 'name' => '字节跳动' ),
                        ),
                    ),
                ),
            ),
            'image_text' => array(
                'title' => '图文模块',
                'fields' => array(
                    array( 'id' => 'image_text_layout', 'label' => '布局', 'type' => 'select', 'options' => array( 'left' => '图片在左', 'right' => '图片在右' ), 'default' => 'left' ),
                    array( 'id' => 'image_text_image', 'label' => '图片', 'type' => 'image' ),
                    array( 'id' => 'image_text_title', 'label' => '标题', 'type' => 'text', 'default' => '关于我们' ),
                    array( 'id' => 'image_text_content', 'label' => '内容', 'type' => 'editor', 'default' => '公司简介内容...' ),
                    array( 'id' => 'image_text_button', 'label' => '按钮文字', 'type' => 'text', 'default' => '了解更多' ),
                    array( 'id' => 'image_text_url', 'label' => '按钮链接', 'type' => 'text', 'default' => '#' ),
                ),
            ),
            'timeline' => array(
                'title' => '时间轴',
                'fields' => array(
                    array( 'id' => 'timeline_title', 'label' => '标题', 'type' => 'text', 'default' => '发展历程' ),
                    array( 
                        'id' => 'timeline_items', 
                        'label' => '时间节点', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'year', 'label' => '年份', 'type' => 'text' ),
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '描述', 'type' => 'textarea' ),
                        ),
                        'default_items' => array(
                            array( 'year' => '2020', 'title' => '公司成立', 'desc' => '正式成立，开始创业之旅' ),
                            array( 'year' => '2021', 'title' => '业务扩展', 'desc' => '团队规模扩大至50人' ),
                            array( 'year' => '2022', 'title' => '产品升级', 'desc' => '发布2.0版本产品' ),
                            array( 'year' => '2023', 'title' => '全国布局', 'desc' => '业务覆盖全国20个省市' ),
                        ),
                    ),
                ),
            ),
            'faq' => array(
                'title' => '常见问题',
                'fields' => array(
                    array( 'id' => 'faq_title', 'label' => '标题', 'type' => 'text', 'default' => '常见问题' ),
                    array( 
                        'id' => 'faq_items', 
                        'label' => '问答', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'question', 'label' => '问题', 'type' => 'text' ),
                            array( 'id' => 'answer', 'label' => '答案', 'type' => 'textarea' ),
                        ),
                        'default_items' => array(
                            array( 'question' => '你们的服务范围是什么？', 'answer' => '我们提供全国范围内的服务。' ),
                            array( 'question' => '如何与你们取得联系？', 'answer' => '您可以通过页面底部的联系方式联系我们。' ),
                        ),
                    ),
                ),
            ),
            'news' => array(
                'title' => '新闻列表',
                'fields' => array(
                    array( 'id' => 'news_title', 'label' => '标题', 'type' => 'text', 'default' => '新闻动态' ),
                    array( 'id' => 'news_count', 'label' => '数量', 'type' => 'number', 'default' => '6' ),
                    array( 'id' => 'news_columns', 'label' => '列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    array( 'id' => 'news_categories', 'label' => '分类ID', 'type' => 'text' ),
                    array( 'id' => 'news_show_image', 'label' => '显示图片', 'type' => 'select', 'options' => array( '1' => '是', '0' => '否' ), 'default' => '1' ),
                    array( 'id' => 'news_image_height', 'label' => '图片高度', 'type' => 'text', 'default' => '200px' ),
                    array( 'id' => 'news_show_excerpt', 'label' => '显示摘要', 'type' => 'select', 'options' => array( '1' => '是', '0' => '否' ), 'default' => '1' ),
                ),
            ),
            'products' => array(
                'title' => '产品列表',
                'fields' => array(
                    array( 'id' => 'products_title', 'label' => '标题', 'type' => 'text', 'default' => '产品中心' ),
                    array( 'id' => 'products_count', 'label' => '数量', 'type' => 'number', 'default' => '8' ),
                    array( 'id' => 'products_columns', 'label' => '列数', 'type' => 'select', 'options' => array( '3' => '3列', '4' => '4列' ), 'default' => '4' ),
                    array( 'id' => 'products_categories', 'label' => '分类ID', 'type' => 'text' ),
                    array( 'id' => 'products_show_image', 'label' => '显示图片', 'type' => 'select', 'options' => array( '1' => '是', '0' => '否' ), 'default' => '1' ),
                    array( 'id' => 'products_image_height', 'label' => '图片高度', 'type' => 'text', 'default' => '200px' ),
                ),
            ),
            'cases' => array(
                'title' => '案例展示',
                'fields' => array(
                    array( 'id' => 'cases_title', 'label' => '标题', 'type' => 'text', 'default' => '成功案例' ),
                    array( 'id' => 'cases_count', 'label' => '数量', 'type' => 'number', 'default' => '6' ),
                    array( 'id' => 'cases_columns', 'label' => '列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    array( 'id' => 'cases_categories', 'label' => '分类ID', 'type' => 'text' ),
                    array( 'id' => 'cases_show_image', 'label' => '显示图片', 'type' => 'select', 'options' => array( '1' => '是', '0' => '否' ), 'default' => '1' ),
                    array( 'id' => 'cases_image_height', 'label' => '图片高度', 'type' => 'text', 'default' => '200px' ),
                ),
            ),
            'contact' => array(
                'title' => '联系我们',
                'fields' => array(
                    array( 'id' => 'contact_title', 'label' => '标题', 'type' => 'text', 'default' => '联系我们' ),
                    array( 'id' => 'contact_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '有任何问题？请随时与我们联系' ),
                    array( 'id' => 'contact_show_form', 'label' => '显示表单', 'type' => 'select', 'options' => array( '1' => '是', '0' => '否' ), 'default' => '1' ),
                    array( 'id' => 'contact_image', 'label' => '右侧图片', 'type' => 'image', 'description' => '关闭表单时显示的图片' ),
                ),
            ),
            'columns' => array(
                'title' => '多列布局',
                'fields' => array(
                    array( 'id' => 'columns_count', 'label' => '列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    array( 
                        'id' => 'columns_items', 
                        'label' => '列内容', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'content', 'label' => '内容', 'type' => 'textarea' ),
                            array( 'id' => 'image', 'label' => '图片', 'type' => 'image' ),
                        ),
                        'default_items' => array(
                            array( 'title' => '第一列', 'content' => '内容描述', 'image' => '' ),
                            array( 'title' => '第二列', 'content' => '内容描述', 'image' => '' ),
                            array( 'title' => '第三列', 'content' => '内容描述', 'image' => '' ),
                        ),
                    ),
                ),
            ),
            'downloads' => array(
                'title' => '下载中心',
                'fields' => array(
                    array( 'id' => 'downloads_title', 'label' => '标题', 'type' => 'text', 'default' => '资料下载' ),
                    array( 'id' => 'downloads_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'downloads_columns', 'label' => '列数', 'type' => 'select', 'options' => array( '1' => '1列', '2' => '2列', '3' => '3列' ), 'default' => '1' ),
                    array( 
                        'id' => 'downloads_items', 
                        'label' => '下载项', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'title', 'label' => '文件名称', 'type' => 'text' ),
                            array( 'id' => 'size', 'label' => '文件大小', 'type' => 'text' ),
                            array( 'id' => 'file', 'label' => '文件链接(可填外部URL)', 'type' => 'text' ),
                            array( 'id' => 'icon', 'label' => '图标(emoji)', 'type' => 'text' ),
                            array( 'id' => 'format', 'label' => '文件格式(可选，如PDF)', 'type' => 'text' ),
                            array( 'id' => 'date', 'label' => '文件日期(可选)', 'type' => 'text' ),
                            array( 'id' => 'description', 'label' => '文件说明(可选)', 'type' => 'textarea' ),
                        ),
                        'default_items' => array(
                            array( 'title' => '产品手册', 'size' => '2.5MB', 'file' => '', 'icon' => '📄', 'format' => 'PDF', 'date' => '', 'description' => '' ),
                            array( 'title' => '技术白皮书', 'size' => '1.2MB', 'file' => '', 'icon' => '📋', 'format' => 'PDF', 'date' => '', 'description' => '' ),
                        ),
                    ),
                ),
            ),
            'process' => array(
                'title' => '合作流程',
                'fields' => array(
                    array( 'id' => 'process_title', 'label' => '标题', 'type' => 'text', 'default' => '合作流程' ),
                    array( 'id' => 'process_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '简单四步，开启合作之旅' ),
                    array( 'id' => 'process_bg_color', 'label' => '板块背景色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'process_title_color', 'label' => '标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'process_subtitle_color', 'label' => '副标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 
                        'id' => 'process_items', 
                        'label' => '流程步骤', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'icon', 'label' => '图标(数字/emoji/iconfont类名)', 'type' => 'text' ),
                            array( 'id' => 'title', 'label' => '步骤标题', 'type' => 'text' ),
                            array( 'id' => 'title_color', 'label' => '标题文字颜色', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '步骤描述', 'type' => 'textarea' ),
                            array( 'id' => 'icon_bg', 'label' => '图标背景色(支持渐变)', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'icon' => '01', 'title' => '需求沟通', 'desc' => '深入了解您的业务需求和目标', 'icon_bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ),
                            array( 'icon' => '02', 'title' => '方案设计', 'desc' => '根据需求制定专属解决方案', 'icon_bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' ),
                            array( 'icon' => '03', 'title' => '开发实施', 'desc' => '专业团队高效执行项目开发', 'icon_bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' ),
                            array( 'icon' => '04', 'title' => '交付上线', 'desc' => '严格测试后交付，持续技术支持', 'icon_bg' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' ),
                        ),
                    ),
                ),
            ),
            'pricing' => array(
                'title' => '价格方案',
                'fields' => array(
                    array( 'id' => 'pricing_title', 'label' => '标题', 'type' => 'text', 'default' => '价格方案' ),
                    array( 'id' => 'pricing_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '选择适合您的方案，开启高效之旅' ),
                    array( 'id' => 'pricing_bg_color', 'label' => '板块背景色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'pricing_title_color', 'label' => '标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'pricing_subtitle_color', 'label' => '副标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'pricing_columns', 'label' => '列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    array( 
                        'id' => 'pricing_items', 
                        'label' => '价格方案', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'name', 'label' => '方案名称', 'type' => 'text' ),
                            array( 'id' => 'name_color', 'label' => '方案名称颜色', 'type' => 'text' ),
                            array( 'id' => 'price', 'label' => '价格(如¥99)', 'type' => 'text' ),
                            array( 'id' => 'price_color', 'label' => '价格颜色(支持渐变)', 'type' => 'text' ),
                            array( 'id' => 'period', 'label' => '周期(如/月)', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '方案描述', 'type' => 'text' ),
                            array( 'id' => 'desc_color', 'label' => '描述文字颜色', 'type' => 'text' ),
                            array( 'id' => 'features', 'label' => '特性列表(每行一个，✓表示包含，✗表示不包含)', 'type' => 'textarea' ),
                            array( 'id' => 'btn_text', 'label' => '按钮文字', 'type' => 'text' ),
                            array( 'id' => 'btn_link', 'label' => '按钮链接', 'type' => 'text' ),
                            array( 'id' => 'btn_bg', 'label' => '按钮背景色(支持渐变)', 'type' => 'text' ),
                            array( 'id' => 'btn_text_color', 'label' => '按钮文字颜色', 'type' => 'text' ),
                            array( 'id' => 'card_bg', 'label' => '卡片背景色', 'type' => 'text' ),
                            array( 'id' => 'featured', 'label' => '是否推荐(1=是)', 'type' => 'text' ),
                            array( 'id' => 'featured_text', 'label' => '推荐标注文字', 'type' => 'text' ),
                            array( 'id' => 'featured_bg', 'label' => '推荐标注背景色', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'name' => '基础版', 'price' => '¥99', 'period' => '/月', 'desc' => '适合个人用户', 'features' => "✓ 基础功能支持\n✓ 5GB 存储空间\n✓ 邮件支持\n✗ 高级分析\n✗ API 接口", 'btn_text' => '立即购买', 'btn_link' => '#', 'card_bg' => '#ffffff', 'featured' => '', 'featured_text' => '', 'featured_bg' => '' ),
                            array( 'name' => '专业版', 'price' => '¥299', 'period' => '/月', 'desc' => '适合成长型企业', 'features' => "✓ 全部基础功能\n✓ 50GB 存储空间\n✓ 优先技术支持\n✓ 高级数据分析\n✓ API 接口", 'btn_text' => '立即购买', 'btn_link' => '#', 'card_bg' => '#ffffff', 'featured' => '1', 'featured_text' => '推荐', 'featured_bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ),
                            array( 'name' => '企业版', 'price' => '¥999', 'period' => '/月', 'desc' => '适合大型企业', 'features' => "✓ 全部专业功能\n✓ 无限存储空间\n✓ 7×24专属客服\n✓ 定制化开发\n✓ 专属客户经理", 'btn_text' => '联系我们', 'btn_link' => '#', 'card_bg' => '#ffffff', 'featured' => '', 'featured_text' => '', 'featured_bg' => '' ),
                        ),
                    ),
                ),
            ),
            'video' => array(
                'title' => '视频展示',
                'fields' => array(
                    array( 'id' => 'video_title', 'label' => '标题', 'type' => 'text', 'default' => '视频展示' ),
                    array( 'id' => 'video_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'video_bg_color', 'label' => '板块背景色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'video_title_color', 'label' => '标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'video_subtitle_color', 'label' => '副标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'video_url', 'label' => '视频链接', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'video_width', 'label' => '播放器宽度', 'type' => 'text', 'default' => '100%' ),
                    array( 'id' => 'video_height', 'label' => '播放器高度', 'type' => 'text', 'default' => '500px' ),
                    array( 'id' => 'video_poster', 'label' => '封面图(仅普通视频)', 'type' => 'image', 'default' => '' ),
                ),
            ),
            'testimonials' => array(
                'title' => '客户评价',
                'fields' => array(
                    array( 'id' => 'testimonials_title', 'label' => '标题', 'type' => 'text', 'default' => '客户评价' ),
                    array( 'id' => 'testimonials_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '听听客户怎么说' ),
                    array( 'id' => 'testimonials_bg_color', 'label' => '板块背景色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'testimonials_title_color', 'label' => '标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'testimonials_subtitle_color', 'label' => '副标题文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'testimonials_columns', 'label' => '列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    array( 
                        'id' => 'testimonials_items', 
                        'label' => '客户评价列表', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'avatar', 'label' => '头像图片', 'type' => 'image' ),
                            array( 'id' => 'name', 'label' => '客户姓名', 'type' => 'text' ),
                            array( 'id' => 'name_color', 'label' => '姓名颜色', 'type' => 'text' ),
                            array( 'id' => 'position', 'label' => '职位/公司', 'type' => 'text' ),
                            array( 'id' => 'content', 'label' => '评价内容', 'type' => 'textarea' ),
                            array( 'id' => 'content_color', 'label' => '评价内容颜色', 'type' => 'text' ),
                            array( 'id' => 'rating', 'label' => '星级评分(1-5)', 'type' => 'text' ),
                            array( 'id' => 'card_bg', 'label' => '卡片背景色', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'avatar' => '', 'name' => '张先生', 'position' => 'CEO · 某科技公司', 'content' => '非常专业的团队，项目交付准时，质量超出预期。推荐给所有需要高品质服务的企业！', 'rating' => '5', 'card_bg' => '#ffffff' ),
                            array( 'avatar' => '', 'name' => '李女士', 'position' => '市场总监 · 某传媒集团', 'content' => '合作非常愉快，沟通顺畅，设计方案很有创意，完美达成了我们的需求目标。', 'rating' => '5', 'card_bg' => '#ffffff' ),
                            array( 'avatar' => '', 'name' => '王总', 'position' => '创始人 · 某电商平台', 'content' => '从需求分析到最终交付，每个环节都很用心。技术实力强，值得长期合作！', 'rating' => '5', 'card_bg' => '#ffffff' ),
                        ),
                    ),
                ),
            ),
            'countdown' => array(
                'title' => '产品倒计时',
                'fields' => array(
                    array( 'id' => 'countdown_title', 'label' => '标题', 'type' => 'text', 'default' => '新品即将上线' ),
                    array( 'id' => 'countdown_subtitle', 'label' => '副标题标签', 'type' => 'text', 'default' => '敬请期待' ),
                    array( 'id' => 'countdown_desc', 'label' => '描述文字', 'type' => 'textarea', 'default' => '我们正在精心打造一款革命性的产品，即将与您见面！' ),
                    array( 'id' => 'countdown_image', 'label' => '产品图片', 'type' => 'image', 'default' => '' ),
                    array( 'id' => 'countdown_days', 'label' => '倒计时天数', 'type' => 'text', 'default' => '7' ),
                    array( 'id' => 'countdown_date', 'label' => '或指定目标日期', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'countdown_bg_color', 'label' => '板块背景色(支持渐变)', 'type' => 'text', 'default' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ),
                    array( 'id' => 'countdown_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '#ffffff' ),
                    array( 'id' => 'countdown_subtitle_color', 'label' => '副标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'countdown_desc_color', 'label' => '描述颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'countdown_timer_bg', 'label' => '计时器背景色', 'type' => 'text', 'default' => 'rgba(255,255,255,0.15)' ),
                    array( 'id' => 'countdown_timer_color', 'label' => '计时器文字颜色', 'type' => 'text', 'default' => '#ffffff' ),
                    array( 'id' => 'countdown_btn_text', 'label' => '按钮文字', 'type' => 'text', 'default' => '立即预约' ),
                    array( 'id' => 'countdown_btn_link', 'label' => '按钮链接', 'type' => 'text', 'default' => '#' ),
                    array( 'id' => 'countdown_btn_bg', 'label' => '按钮背景色', 'type' => 'text', 'default' => '#ffffff' ),
                    array( 'id' => 'countdown_btn_text_color', 'label' => '按钮文字颜色', 'type' => 'text', 'default' => '#667eea' ),
                ),
            ),
            'multi_image_text' => array(
                'title' => '多图文模块',
                'fields' => array(
                    array( 'id' => 'multi_image_text_title', 'label' => '模块标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'multi_image_text_subtitle', 'label' => '模块副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'multi_image_text_layout', 'label' => '图片位置', 'type' => 'select', 'options' => array( 'left' => '图片在左', 'right' => '图片在右' ), 'default' => 'left' ),
                    array( 'id' => 'multi_image_text_bg_color', 'label' => '板块背景色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'multi_image_text_title_color', 'label' => '模块标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'multi_image_text_subtitle_color', 'label' => '模块副标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'multi_image_text_item_title_size', 'label' => '项目标题文字大小', 'type' => 'text', 'default' => '1.25rem' ),
                    array( 
                        'id' => 'multi_image_text_items', 
                        'label' => '图文项目', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'icon', 'label' => '图标(emoji/iconfont类名/HTML)', 'type' => 'text' ),
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'title_color', 'label' => '标题颜色', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '描述', 'type' => 'textarea' ),
                            array( 'id' => 'desc_color', 'label' => '描述颜色', 'type' => 'text' ),
                            array( 'id' => 'image', 'label' => '对应图片', 'type' => 'image' ),
                            array( 'id' => 'link', 'label' => '链接(可选)', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'icon' => '🚀', 'title' => '快速部署', 'desc' => '采用自动化部署流程，5分钟即可完成系统上线，大幅降低运维成本和时间投入。', 'image' => '', 'link' => '' ),
                            array( 'icon' => '🛡️', 'title' => '安全可靠', 'desc' => '企业级安全架构，多层防护机制，数据加密存储，确保您的业务数据安全无虞。', 'image' => '', 'link' => '' ),
                            array( 'icon' => '📊', 'title' => '数据分析', 'desc' => '强大的数据分析引擎，实时监控业务指标，智能报表助力精准决策。', 'image' => '', 'link' => '' ),
                        ),
                    ),
                ),
            ),
            'features_list' => array(
                'title' => '功能清单列表',
                'fields' => array(
                    array( 'id' => 'title', 'label' => '标题', 'type' => 'text', 'default' => '产品功能' ),
                    array( 'id' => 'subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '全面的功能特性，满足您的各种需求' ),
                    array( 'id' => 'bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'text_color', 'label' => '文字颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'columns', 'label' => '每行卡片数', 'type' => 'select', 'options' => array( '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    array( 
                        'id' => 'tabs', 
                        'label' => '功能标签', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'tab_id', 'label' => '标签ID(唯一)', 'type' => 'text' ),
                            array( 'id' => 'tab_title', 'label' => '标签标题', 'type' => 'text' ),
                            array( 'id' => 'tab_icon', 'label' => '标签图标(emoji)', 'type' => 'text' ),
                            array( 
                                'id' => 'features', 
                                'label' => '功能清单(每行一个，格式: emoji|标题|描述)', 
                                'type' => 'textarea',
                                'description' => '每行一个功能，格式：🎨|模块化设计|支持20+内置模块'
                            ),
                        ),
                        'default_items' => array(
                            array( 
                                'tab_id' => 'core', 
                                'tab_title' => '核心功能', 
                                'tab_icon' => '⚡',
                                'features' => "🎨|模块化设计|支持20+内置模块，拖拽即可搭建页面\n🚀|性能优化|极致的加载速度，WebP图片自动转换\n📱|完美响应式|适配所有设备，移动端体验流畅" 
                            ),
                            array( 
                                'tab_id' => 'highlights', 
                                'tab_title' => '特色亮点', 
                                'tab_icon' => '✨',
                                'features' => "🌓|暗黑模式|支持明暗主题切换\n🌐|多语言切换|轻松实现国际化\n🎬|视频展示|支持直链和视频嵌入" 
                            ),
                        ),
                    ),
                ),
            ),
            'team' => array(
                'title' => '团队成员',
                'fields' => array(
                    array( 'id' => 'team_title', 'label' => '标题', 'type' => 'text', 'default' => '核心团队' ),
                    array( 'id' => 'team_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '专业团队，值得信赖' ),
                    array( 'id' => 'team_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'team_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'team_subtitle_color', 'label' => '副标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'team_columns', 'label' => '每行列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '4' ),
                    array( 
                        'id' => 'team_members', 
                        'label' => '团队成员', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'avatar', 'label' => '头像', 'type' => 'image' ),
                            array( 'id' => 'name', 'label' => '姓名', 'type' => 'text' ),
                            array( 'id' => 'position', 'label' => '职位', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '简介', 'type' => 'textarea' ),
                            array( 'id' => 'wechat', 'label' => '微信二维码', 'type' => 'image' ),
                            array( 'id' => 'email', 'label' => '邮箱', 'type' => 'text' ),
                            array( 'id' => 'phone', 'label' => '电话', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'avatar' => '', 'name' => '张明', 'position' => '首席执行官', 'desc' => '20年行业经验，曾任多家知名企业高管。' ),
                            array( 'avatar' => '', 'name' => '李华', 'position' => '技术总监', 'desc' => '资深技术专家，主导多个大型项目研发。' ),
                            array( 'avatar' => '', 'name' => '王芳', 'position' => '市场总监', 'desc' => '深耕市场营销领域15年，擅长品牌策略。' ),
                            array( 'avatar' => '', 'name' => '刘强', 'position' => '运营总监', 'desc' => '精细化运营专家，打造高效团队管理体系。' ),
                        ),
                    ),
                ),
            ),
            'gallery' => array(
                'title' => '画廊相册',
                'fields' => array(
                    array( 'id' => 'gallery_title', 'label' => '标题', 'type' => 'text', 'default' => '图片展示' ),
                    array( 'id' => 'gallery_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'gallery_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'gallery_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'gallery_columns', 'label' => '每行列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列', '5' => '5列' ), 'default' => '4' ),
                    array( 'id' => 'gallery_style', 'label' => '展示样式', 'type' => 'select', 'options' => array( 'grid' => '网格布局', 'masonry' => '瀑布流' ), 'default' => 'grid' ),
                    array( 'id' => 'gallery_gap', 'label' => '图片间距(px)', 'type' => 'number', 'default' => '15' ),
                    array( 'id' => 'gallery_lightbox', 'label' => '点击放大', 'type' => 'select', 'options' => array( '1' => '是', '0' => '否' ), 'default' => '1' ),
                    array( 
                        'id' => 'gallery_images', 
                        'label' => '图片列表', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'image', 'label' => '图片', 'type' => 'image' ),
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'desc', 'label' => '描述', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'image' => '', 'title' => '产品展示', 'desc' => '' ),
                            array( 'image' => '', 'title' => '办公环境', 'desc' => '' ),
                            array( 'image' => '', 'title' => '团队活动', 'desc' => '' ),
                            array( 'image' => '', 'title' => '荣誉资质', 'desc' => '' ),
                        ),
                    ),
                ),
            ),
            'branches' => array(
                'title' => '门店机构',
                'fields' => array(
                    array( 'id' => 'branches_title', 'label' => '标题', 'type' => 'text', 'default' => '全国分支机构' ),
                    array( 'id' => 'branches_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '覆盖全国主要城市，为您提供本地化服务' ),
                    array( 'id' => 'branches_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'branches_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'branches_columns', 'label' => '每行列数', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    array( 
                        'id' => 'branches_list', 
                        'label' => '分支机构列表', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'name', 'label' => '机构名称', 'type' => 'text' ),
                            array( 'id' => 'address', 'label' => '地址', 'type' => 'textarea' ),
                            array( 'id' => 'phone', 'label' => '电话', 'type' => 'text' ),
                            array( 'id' => 'email', 'label' => '邮箱', 'type' => 'text' ),
                            array( 'id' => 'hours', 'label' => '营业时间', 'type' => 'text' ),
                            array( 'id' => 'image', 'label' => '图片(可选)', 'type' => 'image' ),
                            array( 'id' => 'map_url', 'label' => '地图链接(可选)', 'type' => 'text' ),
                        ),
                        'default_items' => array(
                            array( 'name' => '北京总部', 'address' => '北京市朝阳区建国路88号SOHO现代城A座', 'phone' => '010-88888888', 'email' => 'beijing@example.com', 'hours' => '周一至周五 9:00-18:00' ),
                            array( 'name' => '上海分公司', 'address' => '上海市浦东新区陆家嘴环路1000号恒生银行大厦', 'phone' => '021-88888888', 'email' => 'shanghai@example.com', 'hours' => '周一至周五 9:00-18:00' ),
                            array( 'name' => '深圳分公司', 'address' => '深圳市南山区科技园南区高新南七道', 'phone' => '0755-88888888', 'email' => 'shenzhen@example.com', 'hours' => '周一至周五 9:00-18:00' ),
                        ),
                    ),
                ),
            ),
            'tabs' => array(
                'title' => '标签切换',
                'fields' => array(
                    array( 'id' => 'tabs_title', 'label' => '标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'tabs_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'tabs_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'tabs_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'tabs_style', 'label' => '标签样式', 'type' => 'select', 'options' => array( 'default' => '默认样式', 'pills' => '胶囊样式', 'underline' => '下划线样式', 'boxed' => '卡片样式' ), 'default' => 'default' ),
                    array( 'id' => 'tabs_align', 'label' => '标签对齐', 'type' => 'select', 'options' => array( 'left' => '左对齐', 'center' => '居中', 'right' => '右对齐' ), 'default' => 'center' ),
                    array( 
                        'id' => 'tabs_items', 
                        'label' => '标签页', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'title', 'label' => '标签标题', 'type' => 'text' ),
                            array( 'id' => 'icon', 'label' => '图标(emoji或留空)', 'type' => 'text' ),
                            array( 'id' => 'content', 'label' => '标签内容(支持HTML)', 'type' => 'textarea' ),
                        ),
                        'default_items' => array(
                            array( 'title' => '产品介绍', 'icon' => '📦', 'content' => '<p>这里是产品介绍的详细内容。</p>' ),
                            array( 'title' => '技术规格', 'icon' => '⚙️', 'content' => '<p>产品的技术参数和规格说明。</p>' ),
                            array( 'title' => '使用说明', 'icon' => '📖', 'content' => '<p>产品的使用步骤和注意事项。</p>' ),
                        ),
                    ),
                ),
            ),
            'accordion' => array(
                'title' => '手风琴',
                'fields' => array(
                    array( 'id' => 'accordion_title', 'label' => '标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'accordion_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'accordion_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'accordion_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'accordion_style', 'label' => '样式', 'type' => 'select', 'options' => array( 'default' => '默认(阴影)', 'bordered' => '边框', 'minimal' => '简约' ), 'default' => 'default' ),
                    array( 'id' => 'accordion_multiple', 'label' => '允许多个展开', 'type' => 'select', 'options' => array( '' => '否', '1' => '是' ), 'default' => '' ),
                    array( 'id' => 'accordion_first_open', 'label' => '默认展开第一项', 'type' => 'select', 'options' => array( '1' => '是', '' => '否' ), 'default' => '1' ),
                    array( 
                        'id' => 'accordion_items', 
                        'label' => '折叠项', 
                        'type' => 'repeater', 
                        'fields' => array(
                            array( 'id' => 'title', 'label' => '标题', 'type' => 'text' ),
                            array( 'id' => 'icon', 'label' => '图标(emoji)', 'type' => 'text' ),
                            array( 'id' => 'content', 'label' => '内容(支持HTML)', 'type' => 'textarea' ),
                        ),
                        'default_items' => array(
                            array( 'title' => '产品质量如何保证？', 'icon' => '🛡️', 'content' => '我们拥有完善的质量管理体系，每件产品都经过严格的质检流程。' ),
                            array( 'title' => '配送范围和时效？', 'icon' => '🚚', 'content' => '我们支持全国配送，一二线城市1-3天送达，其他地区3-7天送达。' ),
                            array( 'title' => '售后服务政策？', 'icon' => '💬', 'content' => '我们提供7x24小时在线客服支持，产品享有1年质保期。' ),
                        ),
                    ),
                ),
            ),
            'comparison' => array(
                'title' => '比较表格',
                'fields' => array(
                    array( 'id' => 'comparison_title', 'label' => '标题', 'type' => 'text', 'default' => '产品对比' ),
                    array( 'id' => 'comparison_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'comparison_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'comparison_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'comparison_highlight', 'label' => '高亮推荐列(从1开始)', 'type' => 'number', 'default' => '0' ),
                    array( 'id' => 'comparison_features', 'label' => '功能特性列表(每行一个)', 'type' => 'textarea', 'default' => "基础功能\n高级功能\n技术支持\nAPI接口\n数据导出\n自定义域名" ),
                    array( 
                        'id' => 'comparison_products', 
                        'label' => '对比产品/方案', 
                        'type' => 'repeater', 
                        'description' => '每个产品的值用换行分隔，与功能特性一一对应',
                        'fields' => array(
                            array( 'id' => 'name', 'label' => '产品名称', 'type' => 'text' ),
                            array( 'id' => 'values', 'label' => '对应值(每行一个，✓/✗或文字)', 'type' => 'textarea' ),
                        ),
                        'default_items' => array(
                            array( 'name' => '基础版', 'values' => "✓\n✗\n邮件支持\n✗\n✗\n✗" ),
                            array( 'name' => '专业版', 'values' => "✓\n✓\n在线客服\n✓\n✓\n✗" ),
                            array( 'name' => '企业版', 'values' => "✓\n✓\n7×24专属\n✓\n✓\n✓" ),
                        ),
                    ),
                ),
            ),
            'blog' => array(
                'title' => '博客布局',
                'fields' => array(
                    // 基础配置
                    array( 'id' => 'blog_title', 'label' => '模块标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'blog_subtitle', 'label' => '副标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'blog_bg_color', 'label' => '背景颜色(支持渐变)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'blog_title_color', 'label' => '标题颜色', 'type' => 'text', 'default' => '' ),
                    
                    // 页面布局
                    array( 'id' => 'blog_page_layout', 'label' => '页面布局', 'type' => 'select', 'options' => array( 
                        'full' => '单栏（无侧边栏）', 
                        'sidebar-right' => '双栏（侧边栏在右）', 
                        'sidebar-left' => '双栏（侧边栏在左）' 
                    ), 'default' => 'full' ),
                    
                    // 布局样式
                    array( 'id' => 'blog_layout_style', 'label' => '文章布局样式', 'type' => 'select', 'options' => array( 
                        'card' => '卡片式', 
                        'list' => '列表式', 
                        'grid' => '网格式',
                        'large' => '大图式'
                    ), 'default' => 'card' ),
                    array( 'id' => 'blog_columns', 'label' => '每行列数(卡片/网格)', 'type' => 'select', 'options' => array( '2' => '2列', '3' => '3列', '4' => '4列' ), 'default' => '3' ),
                    
                    // 数据来源
                    array( 'id' => 'blog_data_source', 'label' => '数据来源', 'type' => 'select', 'options' => array( 
                        'latest' => '最新文章', 
                        'category' => '指定分类', 
                        'tag' => '指定标签' 
                    ), 'default' => 'latest' ),
                    array( 'id' => 'blog_categories', 'label' => '分类ID(多个逗号分隔)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'blog_tags', 'label' => '标签ID或slug(多个逗号分隔)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'blog_count', 'label' => '显示数量', 'type' => 'number', 'default' => '6' ),
                    array( 'id' => 'blog_orderby', 'label' => '排序方式', 'type' => 'select', 'options' => array( 
                        'date' => '最新发布', 
                        'random' => '随机', 
                        'comment_count' => '评论数', 
                        'views' => '浏览量' 
                    ), 'default' => 'date' ),
                    
                    // 显示控制
                    array( 'id' => 'blog_show_image', 'label' => '显示缩略图', 'type' => 'select', 'options' => array( 'yes' => '是', 'no' => '否' ), 'default' => 'yes' ),
                    array( 'id' => 'blog_image_height', 'label' => '缩略图高度(卡片/网格/大图)', 'type' => 'text', 'default' => '200px' ),
                    array( 'id' => 'blog_show_excerpt', 'label' => '显示摘要', 'type' => 'select', 'options' => array( 'yes' => '是', 'no' => '否' ), 'default' => 'yes' ),
                    array( 'id' => 'blog_excerpt_length', 'label' => '摘要字数', 'type' => 'number', 'default' => '80' ),
                    array( 'id' => 'blog_show_author', 'label' => '显示作者', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'no' ),
                    array( 'id' => 'blog_show_date', 'label' => '显示日期', 'type' => 'select', 'options' => array( 'yes' => '是', 'no' => '否' ), 'default' => 'yes' ),
                    array( 'id' => 'blog_show_category', 'label' => '显示分类', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'yes' ),
                    array( 'id' => 'blog_show_tags', 'label' => '显示标签', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'no' ),
                    array( 'id' => 'blog_read_more_text', 'label' => '阅读更多按钮文字', 'type' => 'text', 'default' => '阅读全文' ),
                    
                    // 分页配置
                    array( 'id' => 'blog_enable_pagination', 'label' => '启用分页(博客页面模板)', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'yes' ),
                ),
            ),
            
            // 博客置顶推荐模块
            'featured_posts' => array(
                'title' => '博客置顶推荐',
                'fields' => array(
                    // 基础配置
                    array( 'id' => 'fp_title', 'label' => '模块标题', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'fp_bg_color', 'label' => '背景颜色', 'type' => 'text', 'default' => '' ),
                    
                    // 布局配置
                    array( 'id' => 'fp_layout', 'label' => '布局样式', 'type' => 'select', 'options' => array( 
                        'full' => '通栏轮播', 
                        'dual' => '双栏布局(左轮播+右列表)' 
                    ), 'default' => 'full' ),
                    array( 'id' => 'fp_slider_ratio', 'label' => '轮播区域占比%(双栏)', 'type' => 'number', 'default' => '65' ),
                    array( 'id' => 'fp_slider_height', 'label' => '轮播高度', 'type' => 'text', 'default' => '400px' ),
                    
                    // 轮播配置
                    array( 'id' => 'fp_autoplay', 'label' => '自动播放', 'type' => 'select', 'options' => array( 'yes' => '是', 'no' => '否' ), 'default' => 'yes' ),
                    array( 'id' => 'fp_interval', 'label' => '播放间隔(毫秒)', 'type' => 'number', 'default' => '5000' ),
                    array( 'id' => 'fp_effect', 'label' => '切换效果', 'type' => 'select', 'options' => array( 'fade' => '淡入淡出', 'slide' => '滑动' ), 'default' => 'fade' ),
                    array( 'id' => 'fp_show_arrows', 'label' => '显示箭头', 'type' => 'select', 'options' => array( 'yes' => '是', 'no' => '否' ), 'default' => 'yes' ),
                    array( 'id' => 'fp_show_dots', 'label' => '显示导航点', 'type' => 'select', 'options' => array( 'yes' => '是', 'no' => '否' ), 'default' => 'yes' ),
                    
                    // 轮播数据来源
                    array( 'id' => 'fp_slider_source', 'label' => '轮播数据来源', 'type' => 'select', 'options' => array( 
                        'latest' => '最新文章', 
                        'random' => '随机文章',
                        'popular' => '热门(按浏览量)',
                        'comment' => '热门(按评论数)',
                        'category' => '指定分类',
                        'manual' => '手动选择'
                    ), 'default' => 'latest' ),
                    array( 'id' => 'fp_slider_ids', 'label' => '轮播文章ID(逗号分隔)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'fp_slider_category', 'label' => '轮播分类ID(逗号分隔)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'fp_slider_count', 'label' => '轮播文章数量', 'type' => 'number', 'default' => '5' ),
                    
                    // 列表数据来源(双栏)
                    array( 'id' => 'fp_list_source', 'label' => '列表数据来源(双栏)', 'type' => 'select', 'options' => array( 
                        'latest' => '最新文章', 
                        'random' => '随机文章',
                        'popular' => '热门(按浏览量)',
                        'comment' => '热门(按评论数)',
                        'category' => '指定分类',
                        'manual' => '手动选择'
                    ), 'default' => 'latest' ),
                    array( 'id' => 'fp_list_ids', 'label' => '列表文章ID(逗号分隔)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'fp_list_category', 'label' => '列表分类ID(逗号分隔)', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'fp_list_count', 'label' => '列表文章数量', 'type' => 'number', 'default' => '4' ),
                    
                    // 角标配置
                    array( 'id' => 'fp_badge_type', 'label' => '角标类型', 'type' => 'select', 'options' => array( 
                        'none' => '不显示', 
                        'recommend' => '推荐',
                        'hot' => '热门',
                        'featured' => '精选',
                        'top' => '置顶',
                        'custom' => '自定义'
                    ), 'default' => 'none' ),
                    array( 'id' => 'fp_badge_text', 'label' => '自定义角标文字', 'type' => 'text', 'default' => '' ),
                    array( 'id' => 'fp_badge_position', 'label' => '角标位置', 'type' => 'select', 'options' => array( 'left' => '左侧', 'right' => '右侧' ), 'default' => 'left' ),
                    array( 'id' => 'fp_badge_color', 'label' => '角标颜色', 'type' => 'text', 'default' => '' ),
                    
                    // 显示控制
                    array( 'id' => 'fp_show_category', 'label' => '显示分类', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'yes' ),
                    array( 'id' => 'fp_show_author', 'label' => '显示作者', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'no' ),
                    array( 'id' => 'fp_show_date', 'label' => '显示日期', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'yes' ),
                    array( 'id' => 'fp_show_excerpt', 'label' => '显示摘要', 'type' => 'select', 'options' => array( 'no' => '否', 'yes' => '是' ), 'default' => 'no' ),
                ),
            ),
        );
        
        // 允许插件扩展模块字段
        $this->module_fields = apply_filters( 'developer_starter_module_fields', $this->module_fields );
    }

    public function enqueue_scripts( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-sortable' );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'developer_starter_modules',
            '页面模块配置',
            array( $this, 'render_modules_meta_box' ),
            'page',
            'normal',
            'high'
        );

        add_meta_box(
            'developer_starter_seo',
            'SEO设置',
            array( $this, 'render_seo_meta_box' ),
            array( 'post', 'page' ),
            'normal',
            'default'
        );
    }

    public function render_modules_meta_box( $post ) {
        wp_nonce_field( 'developer_starter_modules_nonce', 'modules_nonce' );
        
        $modules = get_post_meta( $post->ID, '_developer_starter_modules', true );
        $modules = is_array( $modules ) ? $modules : array();
        
        $module_count = count( $modules );
        ?>
        <style>
            #developer_starter_modules .inside { padding: 0; margin: 0; }
            .dsm-wrap { background: #f0f0f1; }
            .dsm-toolbar { 
                display: flex; 
                flex-wrap: wrap; 
                gap: 8px; 
                padding: 16px; 
                background: #2271b1; 
            }
            .dsm-add-btn { 
                padding: 10px 16px; 
                background: rgba(255,255,255,0.2); 
                color: #fff; 
                border: 1px solid rgba(255,255,255,0.3); 
                border-radius: 4px; 
                cursor: pointer; 
                font-size: 13px; 
                transition: all 0.2s;
            }
            .dsm-add-btn:hover { 
                background: rgba(255,255,255,0.3); 
            }
            .dsm-list { 
                min-height: 60px; 
                padding: 16px; 
            }
            .dsm-item { 
                background: #fff; 
                border: 1px solid #c3c4c7; 
                margin-bottom: 8px; 
                border-radius: 4px;
            }
            .dsm-item-header { 
                display: flex; 
                align-items: center; 
                padding: 12px 16px; 
                cursor: pointer; 
                background: #fafafa;
                border-bottom: 1px solid #eee;
            }
            .dsm-item-header:hover { background: #f0f0f1; }
            .dsm-handle { margin-right: 12px; color: #787c82; cursor: move; font-size: 14px; }
            .dsm-title { flex: 1; font-weight: 600; font-size: 14px; }
            .dsm-toggle { margin-right: 12px; color: #787c82; }
            .dsm-remove { color: #b32d2e; text-decoration: none; font-size: 16px; padding: 4px 8px; }
            .dsm-remove:hover { background: #fee; border-radius: 3px; }
            .dsm-content { padding: 16px; display: none; background: #fff; }
            .dsm-item.open .dsm-content { display: block; }
            .dsm-field { margin-bottom: 16px; }
            .dsm-field label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; }
            .dsm-field input[type=text], 
            .dsm-field input[type=url], 
            .dsm-field input[type=number], 
            .dsm-field select, 
            .dsm-field textarea { 
                width: 100%; 
                max-width: 500px; 
                padding: 8px 10px;
                border: 1px solid #8c8f94;
                border-radius: 4px;
            }
            .dsm-repeater-list { margin-bottom: 12px; }
            .dsm-repeater-item { 
                background: #f6f7f7; 
                border: 1px solid #c3c4c7; 
                padding: 12px; 
                margin-bottom: 8px; 
                border-radius: 4px;
                position: relative;
            }
            .dsm-repeater-remove { 
                position: absolute; 
                top: 8px; 
                right: 8px; 
                color: #b32d2e; 
                text-decoration: none; 
            }
            .dsm-img-preview { max-width: 100px; max-height: 80px; margin-top: 8px; display: block; border-radius: 4px; object-fit: cover; }
            .dsm-img-wrap { display: inline-block; position: relative; margin-top: 8px; }
            .dsm-img-wrap .dsm-img-preview { margin-top: 0; }
            .dsm-img-remove { position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; background: #dc3232; color: #fff; border: none; border-radius: 50%; cursor: pointer; font-size: 12px; line-height: 16px; text-align: center; padding: 0; }
            .dsm-btn-add { 
                background: #2271b1; 
                color: #fff; 
                border: none; 
                padding: 8px 14px; 
                border-radius: 4px; 
                cursor: pointer;
            }
            .dsm-btn-add:hover { background: #135e96; }
            .dsm-placeholder { 
                height: 50px; 
                background: #e8f0fe; 
                border: 2px dashed #2271b1; 
                margin-bottom: 8px;
                border-radius: 4px;
            }
            @media (max-width: 782px) {
                .dsm-toolbar { flex-direction: column; }
                .dsm-add-btn { width: 100%; text-align: center; }
            }
        </style>

        <div class="dsm-wrap">
            <div class="dsm-toolbar">
                <?php foreach ( $this->module_fields as $key => $config ) : ?>
                    <button type="button" class="dsm-add-btn" data-type="<?php echo esc_attr( $key ); ?>">
                        + <?php echo esc_html( $config['title'] ); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="dsm-list" id="dsm-list">
                <?php
                $idx = 0;
                foreach ( $modules as $module ) :
                    $type = isset( $module['type'] ) ? $module['type'] : '';
                    $data = isset( $module['data'] ) ? $module['data'] : array();
                    if ( isset( $this->module_fields[ $type ] ) ) :
                        $this->render_item( $idx, $type, $data, false );
                        $idx++;
                    endif;
                endforeach;
                ?>
            </div>
        </div>

        <div id="dsm-templates" style="display:none;">
            <?php foreach ( $this->module_fields as $key => $config ) : ?>
                <script type="text/template" data-type="<?php echo esc_attr( $key ); ?>">
                    <?php $this->render_item( '__IDX__', $key, array(), true ); ?>
                </script>
            <?php endforeach; ?>
        </div>

        <script>
        jQuery(document).ready(function($){
            var idx = <?php echo $module_count; ?>;

            // Add module
            $(document).on('click', '.dsm-add-btn', function(e){
                e.preventDefault();
                var type = $(this).data('type');
                var $tplScript = $('#dsm-templates script[data-type="' + type + '"]');
                if(!$tplScript.length) return;
                var tpl = $tplScript.html();
                if(!tpl) return;
                tpl = tpl.replace(/__IDX__/g, idx);
                var $item = $(tpl);
                $item.addClass('open');
                $('#dsm-list').append($item);
                idx++;
            });

            // Toggle module
            $(document).on('click', '.dsm-item-header', function(e){
                if($(e.target).closest('.dsm-remove').length) return;
                $(this).closest('.dsm-item').toggleClass('open');
            });

            // Remove module
            $(document).on('click', '.dsm-remove', function(e){
                e.preventDefault();
                e.stopPropagation();
                if(confirm('确定删除此模块吗？')){
                    $(this).closest('.dsm-item').remove();
                }
            });

            // Sortable
            if($.fn.sortable) {
                $('#dsm-list').sortable({
                    handle: '.dsm-handle',
                    placeholder: 'dsm-placeholder',
                    tolerance: 'pointer'
                });
            }

            // Image/File upload
            $(document).on('click', '.dsm-upload', function(e){
                e.preventDefault();
                var $btn = $(this);
                var $field = $btn.closest('.dsm-field');
                var $inp = $field.find('.dsm-img-input');
                var $wrap = $field.find('.dsm-img-wrap');
                
                if(typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                    alert('媒体库加载失败，请刷新页面重试');
                    return;
                }
                
                var frame = wp.media({
                    title: '选择文件',
                    multiple: false,
                    library: {type: 'image'}
                });
                
                frame.on('select', function(){
                    var att = frame.state().get('selection').first().toJSON();
                    $inp.val(att.url);
                    if($wrap.length){
                        $wrap.find('.dsm-img-preview').attr('src', att.url);
                        $wrap.show();
                    } else {
                        $btn.after('<span class="dsm-img-wrap"><img src="'+ att.url +'" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span>');
                    }
                });
                
                frame.open();
            });
            
            // Image remove
            $(document).on('click', '.dsm-img-remove', function(e){
                e.preventDefault();
                var $wrap = $(this).closest('.dsm-img-wrap');
                var $field = $(this).closest('.dsm-field');
                var $inp = $field.find('.dsm-img-input');
                $inp.val('');
                $wrap.remove();
            });

            // Add repeater item
            $(document).on('click', '.dsm-rep-add', function(){
                var $wrap = $(this).parent();
                var $list = $wrap.find('.dsm-repeater-list');
                var $tpl = $wrap.find('.dsm-rep-tpl');
                if(!$tpl.length) return;
                var tpl = $tpl.attr('data-template') || $tpl.data('template');
                if(!tpl) return;
                var ridx = $list.children().length;
                tpl = tpl.replace(/__RIDX__/g, ridx);
                $list.append(tpl);
            });

            // Remove repeater item
            $(document).on('click', '.dsm-repeater-remove', function(e){
                e.preventDefault();
                $(this).closest('.dsm-repeater-item').remove();
            });
        });
        </script>
        <?php
    }

    private function render_item( $idx, $type, $data, $use_defaults = false ) {
        if ( ! isset( $this->module_fields[ $type ] ) ) return;
        
        $config = $this->module_fields[ $type ];
        $fields = $config['fields'];
        $title = $config['title'];
        
        if ( $use_defaults && empty( $data ) ) {
            $data = $this->get_defaults( $type );
        }
        ?>
        <div class="dsm-item" data-type="<?php echo esc_attr( $type ); ?>">
            <div class="dsm-item-header">
                <span class="dsm-handle">::</span>
                <span class="dsm-title"><?php echo esc_html( $title ); ?></span>
                <span class="dsm-toggle">v</span>
                <a href="#" class="dsm-remove">x</a>
            </div>
            <div class="dsm-content">
                <input type="hidden" name="modules[<?php echo $idx; ?>][type]" value="<?php echo esc_attr( $type ); ?>"/>
                <?php foreach ( $fields as $field ) : ?>
                    <?php $this->render_field( $idx, $field, $data ); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function get_defaults( $type ) {
        $data = array();
        if ( ! isset( $this->module_fields[ $type ] ) ) return $data;
        
        foreach ( $this->module_fields[ $type ]['fields'] as $field ) {
            $fid = $field['id'];
            if ( $field['type'] === 'repeater' && isset( $field['default_items'] ) ) {
                $data[ $fid ] = $field['default_items'];
            } elseif ( isset( $field['default'] ) ) {
                $data[ $fid ] = $field['default'];
            }
        }
        return $data;
    }

    private function render_field( $idx, $field, $data ) {
        $fid = $field['id'];
        $def = isset( $field['default'] ) ? $field['default'] : '';
        $val = isset( $data[ $fid ] ) ? $data[ $fid ] : $def;
        $name = "modules[{$idx}][data][{$fid}]";
        ?>
        <div class="dsm-field">
            <label><?php echo esc_html( $field['label'] ); ?></label>
            <?php
            switch ( $field['type'] ) {
                case 'textarea':
                case 'editor':
                    echo '<textarea name="' . esc_attr( $name ) . '" rows="3">' . esc_textarea( $val ) . '</textarea>';
                    break;

                case 'select':
                    // 检查是否需要转换旧值到新值（'0'/'1'/'' -> 'no'/'yes'）
                    $options = $field['options'];
                    $has_yes_no = isset( $options['yes'] ) || isset( $options['no'] );
                    if ( $has_yes_no ) {
                        // 转换旧格式的值
                        if ( $val === '1' ) {
                            $val = 'yes';
                        } elseif ( $val === '0' || $val === '' ) {
                            $val = 'no';
                        }
                    }
                    // 添加 autocomplete="off" 防止浏览器缓存表单值
                    echo '<select name="' . esc_attr( $name ) . '" autocomplete="off">';
                    foreach ( $options as $ov => $ol ) {
                        echo '<option value="' . esc_attr( $ov ) . '"' . selected( $val, $ov, false ) . '>' . esc_html( $ol ) . '</option>';
                    }
                    echo '</select>';
                    break;

                case 'image':
                case 'file':
                    echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" class="dsm-img-input" placeholder="输入图片URL或点击选择" style="max-width:350px;"/>';
                    echo '<button type="button" class="button dsm-upload" style="margin-left:8px;">选择</button>';
                    if ( $val ) {
                        echo '<span class="dsm-img-wrap"><img src="' . esc_url( $val ) . '" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span>';
                    }
                    break;

                case 'number':
                    echo '<input type="number" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '"/>';
                    break;

                case 'repeater':
                    $items = is_array( $val ) ? $val : array();
                    $subs = isset( $field['fields'] ) ? $field['fields'] : array();
                    
                    // 修复：当 repeater 数据为空时，使用 default_items 初始化，确保演示数据在后台显示
                    if ( empty( $items ) && isset( $field['default_items'] ) && is_array( $field['default_items'] ) ) {
                        $items = $field['default_items'];
                    }
                    
                    echo '<div class="dsm-repeater-list">';
                    foreach ( $items as $ri => $item ) {
                        echo '<div class="dsm-repeater-item">';
                        echo '<a href="#" class="dsm-repeater-remove">x</a>';
                        foreach ( $subs as $sf ) {
                            $sv = isset( $item[ $sf['id'] ] ) ? $item[ $sf['id'] ] : '';
                            $sn = "modules[{$idx}][data][{$fid}][{$ri}][{$sf['id']}]";
                            echo '<div class="dsm-field"><label>' . esc_html( $sf['label'] ) . '</label>';
                            if ( $sf['type'] === 'image' || $sf['type'] === 'file' ) {
                                echo '<input type="text" name="' . esc_attr( $sn ) . '" value="' . esc_attr( $sv ) . '" class="dsm-img-input" placeholder="输入图片URL或点击选择" style="max-width:250px;"/>';
                                echo '<button type="button" class="button dsm-upload" style="margin-left:8px;">选择</button>';
                                if ( $sv ) echo '<span class="dsm-img-wrap"><img src="' . esc_url( $sv ) . '" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span>';
                            } elseif ( $sf['type'] === 'textarea' ) {
                                echo '<textarea name="' . esc_attr( $sn ) . '" rows="2">' . esc_textarea( $sv ) . '</textarea>';
                            } else {
                                echo '<input type="text" name="' . esc_attr( $sn ) . '" value="' . esc_attr( $sv ) . '"/>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    
                    // Use data attribute instead of nested script tag to avoid parsing issues
                    $tpl_html = '<div class="dsm-repeater-item"><a href="#" class="dsm-repeater-remove">x</a>';
                    foreach ( $subs as $sf ) {
                        $sn = "modules[{$idx}][data][{$fid}][__RIDX__][{$sf['id']}]";
                        $tpl_html .= '<div class="dsm-field"><label>' . esc_html( $sf['label'] ) . '</label>';
                        if ( $sf['type'] === 'image' || $sf['type'] === 'file' ) {
                            $tpl_html .= '<input type="text" name="' . esc_attr( $sn ) . '" value="" class="dsm-img-input" placeholder="输入图片URL或点击选择" style="max-width:250px;"/>';
                            $tpl_html .= '<button type="button" class="button dsm-upload" style="margin-left:8px;">选择</button>';
                        } elseif ( $sf['type'] === 'textarea' ) {
                            $tpl_html .= '<textarea name="' . esc_attr( $sn ) . '" rows="2"></textarea>';
                        } else {
                            $tpl_html .= '<input type="text" name="' . esc_attr( $sn ) . '" value=""/>';
                        }
                        $tpl_html .= '</div>';
                    }
                    $tpl_html .= '</div>';
                    echo '<div class="dsm-rep-tpl" data-template="' . esc_attr( $tpl_html ) . '" style="display:none;"></div>';
                    echo '<button type="button" class="dsm-btn-add dsm-rep-add">+ 添加项目</button>';
                    break;

                default:
                    // Always use text type to avoid HTML5 validation issues in templates
                    echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '"/>';
            }
            ?>
        </div>
        <?php
    }

    public function render_seo_meta_box( $post ) {
        wp_nonce_field( 'developer_starter_seo_nonce', 'seo_nonce' );
        $t = get_post_meta( $post->ID, '_developer_starter_seo_title', true );
        $d = get_post_meta( $post->ID, '_developer_starter_seo_description', true );
        $k = get_post_meta( $post->ID, '_developer_starter_seo_keywords', true );
        ?>
        <p><label><strong>SEO标题</strong></label><br><input type="text" name="seo_title" value="<?php echo esc_attr( $t ); ?>" class="large-text"/></p>
        <p><label><strong>SEO描述</strong></label><br><textarea name="seo_description" rows="2" class="large-text"><?php echo esc_textarea( $d ); ?></textarea></p>
        <p><label><strong>SEO关键词</strong></label><br><input type="text" name="seo_keywords" value="<?php echo esc_attr( $k ); ?>" class="large-text"/></p>
        <?php
    }

    public function save_meta_boxes( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['modules_nonce'] ) && wp_verify_nonce( $_POST['modules_nonce'], 'developer_starter_modules_nonce' ) ) {
            $modules = array();
            if ( isset( $_POST['modules'] ) && is_array( $_POST['modules'] ) ) {
                foreach ( $_POST['modules'] as $m ) {
                    $modules[] = array(
                        'type' => isset( $m['type'] ) ? sanitize_text_field( $m['type'] ) : '',
                        'data' => isset( $m['data'] ) ? $this->sanitize_data( $m['data'] ) : array(),
                    );
                }
            }
            
            // 如果模块为空，检查是否是解决方案/落地页/功能清单展示模板且尚未填充过默认模块
            // 这样可以给对应的 Page_Creator 机会填充默认模块
            if ( empty( $modules ) ) {
                $template = get_post_meta( $post_id, '_wp_page_template', true );
                $solutions_filled = get_post_meta( $post_id, '_solutions_modules_filled', true );
                $landing_filled = get_post_meta( $post_id, '_landing_modules_filled', true );
                $features_showcase_filled = get_post_meta( $post_id, '_features_showcase_modules_filled', true );
                
                // 如果是解决方案模板且尚未填充，跳过保存空模块
                if ( $template === 'templates/template-solutions.php' && ! $solutions_filled ) {
                    // 不保存空模块，允许默认模块被填充
                } elseif ( $template === 'templates/template-landing.php' && ! $landing_filled ) {
                    // 不保存空模块，允许默认模块被填充
                } elseif ( $template === 'templates/template-features-showcase.php' && ! $features_showcase_filled ) {
                    // 不保存空模块，允许默认模块被填充
                } else {
                    update_post_meta( $post_id, '_developer_starter_modules', $modules );
                }
            } else {
                update_post_meta( $post_id, '_developer_starter_modules', $modules );
            }
        }

        if ( isset( $_POST['seo_nonce'] ) && wp_verify_nonce( $_POST['seo_nonce'], 'developer_starter_seo_nonce' ) ) {
            $seo_title = isset( $_POST['seo_title'] ) ? sanitize_text_field( $_POST['seo_title'] ) : '';
            $seo_desc = isset( $_POST['seo_description'] ) ? sanitize_textarea_field( $_POST['seo_description'] ) : '';
            $seo_keywords = isset( $_POST['seo_keywords'] ) ? sanitize_text_field( $_POST['seo_keywords'] ) : '';
            update_post_meta( $post_id, '_developer_starter_seo_title', $seo_title );
            update_post_meta( $post_id, '_developer_starter_seo_description', $seo_desc );
            update_post_meta( $post_id, '_developer_starter_seo_keywords', $seo_keywords );
        }
    }

    private function sanitize_data( $data ) {
        $out = array();
        if ( ! is_array( $data ) ) return $out;
        foreach ( $data as $k => $v ) {
            if ( is_array( $v ) ) {
                $out[ $k ] = $this->sanitize_data( $v );
            } else {
                // 判断字段类型时使用更精确的匹配
                // 检查是否是内容/描述类字段
                if ( strpos( $k, 'content' ) !== false || strpos( $k, 'desc' ) !== false || strpos( $k, 'answer' ) !== false ) {
                    $out[ $k ] = wp_kses_post( $v );
                // 排除 show_image 等布尔类型字段（它们不是图片URL）
                } elseif ( strpos( $k, 'show_' ) !== false || strpos( $k, '_show' ) !== false || strpos( $k, 'enable_' ) !== false || strpos( $k, '_enable' ) !== false ) {
                    $out[ $k ] = sanitize_text_field( $v );
                // 检查是否是纯图片字段（字段名以_image结尾或等于image/logo/file）
                } elseif ( preg_match( '/(_image|_logo|_file|_qrcode)$/', $k ) || $k === 'image' || $k === 'logo' || $k === 'file' || $k === 'avatar' ) {
                    $out[ $k ] = esc_url_raw( $v );
                // 检查是否是图标字段 - 允许iconfont/FontAwesome等图标HTML
                } elseif ( $k === 'icon' ) {
                    // 检测是否包含HTML标签
                    if ( preg_match( '/<[^>]+>/', $v ) ) {
                        // 允许 <i>, <span>, <svg>, <path> 等图标相关标签
                        $allowed = array(
                            'i' => array( 
                                'class' => true, 
                                'style' => true,
                                'aria-hidden' => true,
                            ),
                            'span' => array( 
                                'class' => true, 
                                'style' => true,
                            ),
                            'svg' => array( 
                                'class' => true, 
                                'width' => true, 
                                'height' => true, 
                                'viewBox' => true,
                                'viewbox' => true, 
                                'fill' => true, 
                                'xmlns' => true,
                            ),
                            'path' => array( 
                                'd' => true, 
                                'fill' => true,
                            ),
                            'use' => array( 
                                'xlink:href' => true, 
                                'href' => true,
                            ),
                        );
                        $out[ $k ] = wp_kses( $v, $allowed );
                    } else {
                        // 非HTML内容直接保存（比如emoji或纯class名）
                        $out[ $k ] = sanitize_text_field( $v );
                    }
                // 检查是否是需要保留换行的多行文本字段
                } elseif ( $k === 'features' ) {
                    $out[ $k ] = sanitize_textarea_field( $v );
                // 其他所有字段都作为普通文本处理
                } else {
                    $out[ $k ] = sanitize_text_field( $v );
                }
            }
        }
        return $out;
    }
}
