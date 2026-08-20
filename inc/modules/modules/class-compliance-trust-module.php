<?php
/**
 * Compliance Trust Module - 安全合规模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Compliance_Trust_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'general';
        $this->icon        = 'dashicons-shield-alt';
        $this->description = __( '展示 SOC2 / ISO / GDPR 等安全合规资质，支持徽章、说明与报告链接', 'developer-starter' );
    }

    public function get_id() {
        return 'compliance_trust';
    }

    public function get_name() {
        return __( '安全合规', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'ct_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '安全与<span style="color:var(--color-primary)">合规</span>', 'Security & <span style="color:var(--color-primary)">Compliance</span>' ) : __( '安全与<span style="color:var(--color-primary)">合规</span>', 'developer-starter' ),
            ),
            array(
                'id'      => 'ct_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们持续通过权威安全认证与隐私合规审查，保障数据安全与业务连续性。', 'We continuously pass independent security and privacy audits to protect your data and business continuity.' ) : __( '我们持续通过权威安全认证与隐私合规审查，保障数据安全与业务连续性。', 'developer-starter' ),
            ),
            array(
                'id'      => 'ct_note',
                'type'    => 'textarea',
                'label'   => __( '补充说明', 'developer-starter' ),
                'rows'    => 3,
                'default' => '',
            ),
            array(
                'id'      => 'ct_layout',
                'type'    => 'select',
                'label'   => __( '布局模式', 'developer-starter' ),
                'options' => array(
                    'grid' => __( '网格布局', 'developer-starter' ),
                    'list' => __( '列表布局', 'developer-starter' ),
                ),
                'default' => 'grid',
            ),
            array(
                'id'      => 'ct_columns',
                'type'    => 'select',
                'label'   => __( '网格列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'ct_enable_filter',
                'type'    => 'select',
                'label'   => __( '启用分类筛选', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no'  => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'ct_card_style',
                'type'    => 'select',
                'label'   => __( '卡片风格', 'developer-starter' ),
                'options' => array(
                    'solid' => __( '实体卡片', 'developer-starter' ),
                    'glass' => __( '玻璃拟态', 'developer-starter' ),
                ),
                'default' => 'solid',
            ),
            array(
                'id'      => 'ct_accent_color',
                'type'    => 'color',
                'label'   => __( '主题强调色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'ct_status_chip_bg',
                'type'    => 'color',
                'label'   => __( '状态徽章背景色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'ct_status_chip_text',
                'type'    => 'color',
                'label'   => __( '状态徽章文字色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'ct_action_btn_bg',
                'type'    => 'text',
                'label'   => __( '操作按钮背景 (支持渐变)', 'developer-starter' ),
                'default' => 'var(--qiling-gradient-brand)',
            ),
            array(
                'id'      => 'ct_action_btn_text',
                'type'    => 'color',
                'label'   => __( '操作按钮文字色', 'developer-starter' ),
                'default' => '',
            ),
            $this->get_button_border_color_field( 'ct_action_btn_border', __( '操作按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'      => 'module_bg_type',
                'type'    => 'select',
                'label'   => __( '背景类型', 'developer-starter' ),
                'options' => array(
                    'color' => __( '纯色/渐变背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id'         => 'module_bg_color',
                'type'       => 'text',
                'label'      => __( '背景颜色/渐变', 'developer-starter' ),
                'default'    => 'var(--color-neutral-50)',
                'dependency' => array( 'module_bg_type', '==', 'color' ),
            ),
            array(
                'id'         => 'module_bg_image',
                'type'       => 'image',
                'label'      => __( '背景图片', 'developer-starter' ),
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
            array(
                'id'         => 'module_bg_overlay',
                'type'       => 'select',
                'label'      => __( '背景遮罩浓度', 'developer-starter' ),
                'options'    => array(
                    '0'   => __( '无遮罩', 'developer-starter' ),
                    '0.1' => '10%',
                    '0.2' => '20%',
                    '0.3' => '30%',
                    '0.4' => '40%',
                    '0.5' => '50%',
                    '0.6' => '60%',
                    '0.7' => '70%',
                    '0.8' => '80%',
                    '0.9' => '90%',
                ),
                'default'    => '0.35',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距 (如 80px)', 'developer-starter' ),
                'default' => '80px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距 (如 80px)', 'developer-starter' ),
                'default' => '80px',
            ),
            array(
                'id'         => 'ct_items',
                'type'       => 'repeater',
                'label'      => __( '合规条目', 'developer-starter' ),
                'add_button' => __( '添加条目', 'developer-starter' ),
                'fields'     => array(
                    array( 'id' => 'logo', 'type' => 'image', 'label' => __( '徽章/logo', 'developer-starter' ) ),
                    array( 'id' => 'icon', 'type' => 'text', 'label' => __( '图标 (emoji 或文本)', 'developer-starter' ) ),
                    array( 'id' => 'title', 'type' => 'text', 'label' => __( '名称', 'developer-starter' ) ),
                    array( 'id' => 'short_name', 'type' => 'text', 'label' => __( '简称 (如 SOC2)', 'developer-starter' ) ),
                    array( 'id' => 'category', 'type' => 'text', 'label' => __( '分类 (如 隐私/安全)', 'developer-starter' ) ),
                    array(
                        'id'      => 'status',
                        'type'    => 'select',
                        'label'   => __( '状态', 'developer-starter' ),
                        'options' => array(
                            'active'   => __( '已认证', 'developer-starter' ),
                            'progress' => __( '进行中', 'developer-starter' ),
                            'planned'  => __( '计划中', 'developer-starter' ),
                            'expired'  => __( '已过期', 'developer-starter' ),
                        ),
                    ),
                    array( 'id' => 'status_text', 'type' => 'text', 'label' => __( '自定义状态文案 (可选)', 'developer-starter' ) ),
                    array( 'id' => 'issuer', 'type' => 'text', 'label' => __( '审计/颁发机构', 'developer-starter' ) ),
                    array( 'id' => 'cert_no', 'type' => 'text', 'label' => __( '证书编号/报告号', 'developer-starter' ) ),
                    array( 'id' => 'scope', 'type' => 'text', 'label' => __( '适用范围', 'developer-starter' ) ),
                    array( 'id' => 'valid_until', 'type' => 'text', 'label' => __( '有效期', 'developer-starter' ) ),
                    array( 'id' => 'description', 'type' => 'textarea', 'label' => __( '说明', 'developer-starter' ), 'rows' => 3 ),
                    array( 'id' => 'checklist', 'type' => 'textarea', 'label' => __( '能力点 (每行一条)', 'developer-starter' ), 'rows' => 4 ),
                    array( 'id' => 'report_url', 'type' => 'text', 'label' => __( '详情/报告链接', 'developer-starter' ) ),
                    array( 'id' => 'report_text', 'type' => 'text', 'label' => __( '链接文案', 'developer-starter' ) ),
                    array(
                        'id'      => 'highlight',
                        'type'    => 'select',
                        'label'   => __( '高亮卡片', 'developer-starter' ),
                        'options' => array(
                            'no'  => __( '否', 'developer-starter' ),
                            'yes' => __( '是', 'developer-starter' ),
                        ),
                        'default' => 'no',
                    ),
                    array( 'id' => 'card_bg', 'type' => 'color', 'label' => __( '卡片背景色 (可选)', 'developer-starter' ) ),
                    array( 'id' => 'border_color', 'type' => 'color', 'label' => __( '卡片边框色 (可选)', 'developer-starter' ) ),
                ),
            ),
            array(
                'id'      => 'enable_staggered_animation',
                'type'    => 'select',
                'label'   => __( '开启列表逐个显示动画', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no'  => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
        );
    }

    /**
     * 解析换行文本为列表项。
     *
     * @param string $raw_text 原始文本。
     * @return array
     */
    private function parse_multiline_list( $raw_text ) {
        if ( ! is_string( $raw_text ) || trim( $raw_text ) === '' ) {
            return array();
        }

        $lines = preg_split( '/\r\n|\r|\n/', (string) $raw_text );
        if ( ! is_array( $lines ) ) {
            return array();
        }

        $items = array();
        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( $line !== '' ) {
                $items[] = $line;
            }
        }

        return $items;
    }

    /**
     * 兼容 ID/URL/数组结构，输出媒体 URL。
     *
     * @param mixed $value 媒体值。
     * @return string
     */
    private function resolve_media_url( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        if ( function_exists( 'developer_starter_get_media_url' ) ) {
            $resolved = developer_starter_get_media_url( $value );
            if ( ! empty( $resolved ) ) {
                return (string) $resolved;
            }
        }

        if ( is_numeric( $value ) ) {
            $url = wp_get_attachment_url( (int) $value );
            if ( $url ) {
                return (string) $url;
            }
        }

        if ( is_array( $value ) && ! empty( $value['url'] ) ) {
            return (string) $value['url'];
        }

        return (string) $value;
    }

    public function render( $data = array() ) {
        $title       = isset( $data['ct_title'] ) ? (string) $data['ct_title'] : '';
        $subtitle    = isset( $data['ct_subtitle'] ) ? (string) $data['ct_subtitle'] : '';
        $note        = isset( $data['ct_note'] ) ? (string) $data['ct_note'] : '';
        $layout      = isset( $data['ct_layout'] ) ? sanitize_key( (string) $data['ct_layout'] ) : 'grid';
        $columns     = isset( $data['ct_columns'] ) ? max( 2, min( 4, (int) $data['ct_columns'] ) ) : 3;
        $card_style  = isset( $data['ct_card_style'] ) ? sanitize_key( (string) $data['ct_card_style'] ) : 'solid';
        $enable_filter = isset( $data['ct_enable_filter'] ) ? sanitize_key( (string) $data['ct_enable_filter'] ) : 'yes';
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? sanitize_key( (string) $data['enable_staggered_animation'] ) : 'yes';

        if ( ! in_array( $layout, array( 'grid', 'list' ), true ) ) {
            $layout = 'grid';
        }

        if ( ! in_array( $card_style, array( 'solid', 'glass' ), true ) ) {
            $card_style = 'solid';
        }

        $accent_color   = isset( $data['ct_accent_color'] ) && $data['ct_accent_color'] !== '' ? (string) $data['ct_accent_color'] : 'var(--color-primary)';
        $has_custom_chip_bg = isset( $data['ct_status_chip_bg'] ) && $data['ct_status_chip_bg'] !== '';
        $has_custom_chip_text = isset( $data['ct_status_chip_text'] ) && $data['ct_status_chip_text'] !== '';
        $chip_bg        = $has_custom_chip_bg ? (string) $data['ct_status_chip_bg'] : 'var(--qiling-color-eff6ff)';
        $chip_text      = $has_custom_chip_text ? (string) $data['ct_status_chip_text'] : 'var(--qiling-color-1e3a8a)';
        $button_bg      = isset( $data['ct_action_btn_bg'] ) && $data['ct_action_btn_bg'] !== '' ? (string) $data['ct_action_btn_bg'] : 'var(--qiling-gradient-brand)';
        $button_text    = isset( $data['ct_action_btn_text'] ) && $data['ct_action_btn_text'] !== '' ? (string) $data['ct_action_btn_text'] : 'var(--color-neutral-0)';
        $button_border  = isset( $data['ct_action_btn_border'] ) && $data['ct_action_btn_border'] !== '' ? (string) $data['ct_action_btn_border'] : $button_bg;

        $bg_type        = isset( $data['module_bg_type'] ) ? sanitize_key( (string) $data['module_bg_type'] ) : 'color';
        $bg_color       = isset( $data['module_bg_color'] ) && $data['module_bg_color'] !== '' ? (string) $data['module_bg_color'] : 'var(--color-neutral-50)';
        $bg_image       = isset( $data['module_bg_image'] ) ? $this->resolve_media_url( $data['module_bg_image'] ) : '';
        $bg_overlay     = isset( $data['module_bg_overlay'] ) ? (string) $data['module_bg_overlay'] : '0.35';
        $pt             = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? (string) $data['module_padding_top'] : 'var(--qiling-space-80)';
        $pb             = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? (string) $data['module_padding_bottom'] : 'var(--qiling-space-80)';

        $items = isset( $data['ct_items'] ) && is_array( $data['ct_items'] ) ? $data['ct_items'] : array();
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'icon'        => '🛡️',
                    'title'       => 'SOC 2 Type II',
                    'short_name'  => 'SOC2',
                    'category'    => __( '安全控制', 'developer-starter' ),
                    'status'      => 'active',
                    'issuer'      => __( '独立第三方审计机构', 'developer-starter' ),
                    'cert_no'     => 'SOC2-2026-Q1',
                    'scope'       => __( '云平台与生产运营控制', 'developer-starter' ),
                    'valid_until' => __( '年度滚动审计', 'developer-starter' ),
                    'description' => __( '围绕安全性、可用性、保密性建立控制体系并持续审计。', 'developer-starter' ),
                    'checklist'   => __( "访问控制策略\n变更管理审计\n日志留存与告警", 'developer-starter' ),
                    'report_url'  => '#',
                    'report_text' => __( '查看审计说明', 'developer-starter' ),
                    'highlight'   => 'yes',
                ),
                array(
                    'icon'        => '🔐',
                    'title'       => 'ISO/IEC 27001',
                    'short_name'  => 'ISO27001',
                    'category'    => __( '信息安全', 'developer-starter' ),
                    'status'      => 'active',
                    'issuer'      => __( '国际认证机构', 'developer-starter' ),
                    'cert_no'     => 'ISO27001-2026-11',
                    'scope'       => __( '信息安全管理体系', 'developer-starter' ),
                    'valid_until' => '2028-11-30',
                    'description' => __( '基于风险评估持续改进信息安全管理流程。', 'developer-starter' ),
                    'checklist'   => __( "风险分级管控\n供应商安全评估\n员工安全培训", 'developer-starter' ),
                    'report_url'  => '#',
                    'report_text' => __( '查看认证范围', 'developer-starter' ),
                ),
                array(
                    'icon'        => '🌍',
                    'title'       => 'GDPR',
                    'short_name'  => 'GDPR',
                    'category'    => __( '隐私合规', 'developer-starter' ),
                    'status'      => 'active',
                    'issuer'      => __( '内部法务与外部顾问联合评估', 'developer-starter' ),
                    'scope'       => __( '欧盟用户数据处理流程', 'developer-starter' ),
                    'valid_until' => __( '持续合规监测', 'developer-starter' ),
                    'description' => __( '提供数据主体权利响应机制与跨境数据处理合规流程。', 'developer-starter' ),
                    'checklist'   => __( "数据处理记录\nDPA协议支持\n删除与导出流程", 'developer-starter' ),
                    'report_url'  => '#',
                    'report_text' => __( '查看隐私承诺', 'developer-starter' ),
                ),
                array(
                    'icon'        => '📋',
                    'title'       => __( '等保三级', 'developer-starter' ),
                    'short_name'  => __( '等保3级', 'developer-starter' ),
                    'category'    => __( '本地监管', 'developer-starter' ),
                    'status'      => 'progress',
                    'issuer'      => __( '测评机构进行中', 'developer-starter' ),
                    'scope'       => __( '核心业务系统安全防护', 'developer-starter' ),
                    'valid_until' => __( '预计 2026 Q4 完成', 'developer-starter' ),
                    'description' => __( '按等保要求完善技术与管理制度，推进正式测评。', 'developer-starter' ),
                    'checklist'   => __( "主机与网络加固\n制度与流程补齐\n测评整改闭环", 'developer-starter' ),
                    'report_url'  => '#',
                    'report_text' => __( '查看进展', 'developer-starter' ),
                ),
            );
        }

        $status_map = array(
            'active'   => array(
                'label' => __( '已认证', 'developer-starter' ),
                'class' => 'is-active',
            ),
            'progress' => array(
                'label' => __( '进行中', 'developer-starter' ),
                'class' => 'is-progress',
            ),
            'planned'  => array(
                'label' => __( '计划中', 'developer-starter' ),
                'class' => 'is-planned',
            ),
            'expired'  => array(
                'label' => __( '已过期', 'developer-starter' ),
                'class' => 'is-expired',
            ),
        );

        $categories = array();
        foreach ( $items as $item ) {
            $category = isset( $item['category'] ) ? trim( (string) $item['category'] ) : '';
            if ( $category !== '' && ! in_array( $category, $categories, true ) ) {
                $categories[] = $category;
            }
        }

        $module_id = 'compliance-trust-' . wp_rand( 1000, 999999 );

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        $section_style .= "--ct-accent: {$accent_color};";
        $section_style .= "--ct-chip-bg: {$chip_bg};";
        $section_style .= "--ct-chip-text: {$chip_text};";
        if ( $has_custom_chip_bg ) {
            $section_style .= "--ct-status-active-bg: {$chip_bg};";
            $section_style .= "--ct-status-progress-bg: {$chip_bg};";
            $section_style .= "--ct-status-planned-bg: {$chip_bg};";
            $section_style .= "--ct-status-expired-bg: {$chip_bg};";
        }
        if ( $has_custom_chip_text ) {
            $section_style .= "--ct-status-active-text: {$chip_text};";
            $section_style .= "--ct-status-progress-text: {$chip_text};";
            $section_style .= "--ct-status-planned-text: {$chip_text};";
            $section_style .= "--ct-status-expired-text: {$chip_text};";
        }
        $section_style .= "--ct-btn-bg: {$button_bg};";
        $section_style .= "--ct-btn-text: {$button_text};";
        $section_style .= "--ct-btn-border: {$button_border};";

        if ( 'image' === $bg_type && $bg_image !== '' ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "');";
            $section_style .= 'background-size: cover; background-position: center;';
        } else {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        ?>
        <section
            class="module module-compliance-trust"
            id="<?php echo esc_attr( $module_id ); ?>"
            data-layout="<?php echo esc_attr( $layout ); ?>"
            data-cols="<?php echo esc_attr( (string) $columns ); ?>"
            data-card-style="<?php echo esc_attr( $card_style ); ?>"
            style="<?php echo esc_attr( $section_style ); ?>"
        >
            <?php if ( 'image' === $bg_type && $bg_image !== '' ) : ?>
                <div class="ct-overlay" style="opacity: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
            <?php endif; ?>

            <div class="container ct-container">
                <div class="section-header text-center">
                    <?php if ( $title !== '' ) : ?>
                        <h2 class="section-title"><?php echo wp_kses_post( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $subtitle !== '' ) : ?>
                        <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                    <?php if ( $note !== '' ) : ?>
                        <div class="ct-note"><?php echo wp_kses_post( nl2br( esc_html( $note ) ) ); ?></div>
                    <?php endif; ?>
                </div>

                <?php if ( $enable_filter === 'yes' && count( $categories ) > 1 ) : ?>
                    <div class="ct-filters">
                        <button type="button" class="ct-filter-btn is-active" data-filter="all"><?php esc_html_e( '全部', 'developer-starter' ); ?></button>
                        <?php foreach ( $categories as $category ) : ?>
                            <button type="button" class="ct-filter-btn" data-filter="<?php echo esc_attr( sanitize_title( $category ) ); ?>">
                                <?php echo esc_html( $category ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="ct-grid">
                    <?php foreach ( $items as $index => $item ) : ?>
                        <?php
                        $item_logo       = $this->resolve_media_url( isset( $item['logo'] ) ? $item['logo'] : '' );
                        $item_icon       = isset( $item['icon'] ) ? trim( (string) $item['icon'] ) : '';
                        $item_title      = isset( $item['title'] ) ? (string) $item['title'] : '';
                        $item_short_name = isset( $item['short_name'] ) ? trim( (string) $item['short_name'] ) : '';
                        $item_category   = isset( $item['category'] ) ? trim( (string) $item['category'] ) : '';
                        $item_status     = isset( $item['status'] ) ? sanitize_key( (string) $item['status'] ) : 'active';
                        $status_text     = isset( $item['status_text'] ) ? trim( (string) $item['status_text'] ) : '';
                        $item_issuer     = isset( $item['issuer'] ) ? (string) $item['issuer'] : '';
                        $item_cert_no    = isset( $item['cert_no'] ) ? (string) $item['cert_no'] : '';
                        $item_scope      = isset( $item['scope'] ) ? (string) $item['scope'] : '';
                        $item_valid      = isset( $item['valid_until'] ) ? (string) $item['valid_until'] : '';
                        $item_desc       = isset( $item['description'] ) ? (string) $item['description'] : '';
                        $item_points     = $this->parse_multiline_list( isset( $item['checklist'] ) ? (string) $item['checklist'] : '' );
                        $item_report_url = isset( $item['report_url'] ) ? (string) $item['report_url'] : '';
                        $item_report_txt = isset( $item['report_text'] ) && trim( (string) $item['report_text'] ) !== '' ? (string) $item['report_text'] : __( '查看详情', 'developer-starter' );
                        $item_highlight  = isset( $item['highlight'] ) && sanitize_key( (string) $item['highlight'] ) === 'yes';
                        $item_card_bg    = isset( $item['card_bg'] ) ? trim( (string) $item['card_bg'] ) : '';
                        $item_border     = isset( $item['border_color'] ) ? trim( (string) $item['border_color'] ) : '';

                        $status_conf = isset( $status_map[ $item_status ] ) ? $status_map[ $item_status ] : $status_map['active'];
                        $status_label = $status_text !== '' ? $status_text : $status_conf['label'];
                        $status_class = $status_conf['class'];

                        $cat_slug = $item_category !== '' ? sanitize_title( $item_category ) : 'uncategorized';
                        $anim_attr = $enable_anim === 'yes' ? $this->get_staggered_animation_attr( $index, 80, 80 ) : '';

                        $card_vars = '';
                        if ( $item_card_bg !== '' ) {
                            $card_vars .= '--ct-card-bg:' . $item_card_bg . ';';
                        }
                        if ( $item_border !== '' ) {
                            $card_vars .= '--ct-card-border:' . $item_border . ';';
                        }
                        ?>
                        <article
                            class="ct-card <?php echo $item_highlight ? 'is-highlight' : ''; ?>"
                            data-category="<?php echo esc_attr( $cat_slug ); ?>"
                            style="<?php echo esc_attr( $card_vars ); ?>"
                            <?php echo $anim_attr; ?>
                        >
                            <div class="ct-card-head">
                                <div class="ct-seal">
                                    <?php if ( $item_logo !== '' ) : ?>
                                        <img src="<?php echo esc_url( $item_logo ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" loading="lazy">
                                    <?php elseif ( $item_icon !== '' ) : ?>
                                        <span class="ct-seal-fallback"><?php echo esc_html( $item_icon ); ?></span>
                                    <?php else : ?>
                                        <span class="ct-seal-fallback"><?php echo esc_html( $item_short_name !== '' ? $item_short_name : '✔' ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ct-head-text">
                                    <h3 class="ct-title"><?php echo esc_html( $item_title ); ?></h3>
                                    <?php if ( $item_short_name !== '' ) : ?>
                                        <div class="ct-short-name"><?php echo esc_html( $item_short_name ); ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="ct-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
                            </div>

                            <?php if ( $item_category !== '' ) : ?>
                                <div class="ct-category"><?php echo esc_html( $item_category ); ?></div>
                            <?php endif; ?>

                            <div class="ct-meta">
                                <?php if ( $item_issuer !== '' ) : ?>
                                    <div class="ct-meta-item"><span><?php esc_html_e( '机构', 'developer-starter' ); ?></span><strong><?php echo esc_html( $item_issuer ); ?></strong></div>
                                <?php endif; ?>
                                <?php if ( $item_cert_no !== '' ) : ?>
                                    <div class="ct-meta-item"><span><?php esc_html_e( '编号', 'developer-starter' ); ?></span><strong><?php echo esc_html( $item_cert_no ); ?></strong></div>
                                <?php endif; ?>
                                <?php if ( $item_scope !== '' ) : ?>
                                    <div class="ct-meta-item"><span><?php esc_html_e( '范围', 'developer-starter' ); ?></span><strong><?php echo esc_html( $item_scope ); ?></strong></div>
                                <?php endif; ?>
                                <?php if ( $item_valid !== '' ) : ?>
                                    <div class="ct-meta-item"><span><?php esc_html_e( '有效期', 'developer-starter' ); ?></span><strong><?php echo esc_html( $item_valid ); ?></strong></div>
                                <?php endif; ?>
                            </div>

                            <?php if ( $item_desc !== '' ) : ?>
                                <p class="ct-desc"><?php echo wp_kses_post( nl2br( esc_html( $item_desc ) ) ); ?></p>
                            <?php endif; ?>

                            <?php if ( ! empty( $item_points ) ) : ?>
                                <ul class="ct-points">
                                    <?php foreach ( $item_points as $point ) : ?>
                                        <li><?php echo esc_html( $point ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if ( $item_report_url !== '' ) : ?>
                                <a class="ct-link" href="<?php echo esc_url( $item_report_url ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html( $item_report_txt ); ?>
                                </a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <style>
                #<?php echo esc_html( $module_id ); ?> {
                    position: relative;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-container {
                    position: relative;
                    z-index: 2;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-overlay {
                    position: absolute;
                    inset: 0;
                    background: var(--color-neutral-900);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-note {
                    margin-top: var(--qiling-space-10);
                    color: var(--color-neutral-600);
                    font-size: var(--qiling-text-rem-0p95);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-filters {
                    display: flex;
                    flex-wrap: wrap;
                    gap: var(--qiling-space-10);
                    justify-content: center;
                    margin: var(--qiling-space-28) 0 var(--qiling-space-22);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-filter-btn {
                    border: 1px solid var(--qiling-color-dbeafe);
                    background: var(--color-neutral-0);
                    color: var(--qiling-color-1e3a8a);
                    border-radius: 999px;
                    padding: var(--qiling-space-8) var(--qiling-space-16);
                    cursor: pointer;
                    font-size: var(--qiling-text-rem-0p9);
                    transition: all .2s ease;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-filter-btn:hover,
                #<?php echo esc_html( $module_id ); ?> .ct-filter-btn.is-active {
                    background: var(--ct-accent);
                    border-color: var(--ct-accent);
                    color: var(--color-neutral-0);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-grid {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: var(--qiling-space-20);
                }
                #<?php echo esc_html( $module_id ); ?>[data-cols="2"] .ct-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                #<?php echo esc_html( $module_id ); ?>[data-cols="4"] .ct-grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
                #<?php echo esc_html( $module_id ); ?>[data-layout="list"] .ct-grid {
                    grid-template-columns: 1fr;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-card {
                    --ct-card-bg: var(--color-neutral-0);
                    --ct-card-border: var(--color-neutral-200);
                    background: var(--ct-card-bg);
                    border: 1px solid var(--ct-card-border);
                    border-radius: var(--qiling-space-16);
                    padding: var(--qiling-space-18);
                    box-shadow: 0 var(--qiling-space-10) var(--qiling-space-30) rgba(var(--qiling-rgb-15-23-42), 0.08);
                    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
                }
                #<?php echo esc_html( $module_id ); ?>[data-card-style="glass"] .ct-card {
                    background: rgba(var(--qiling-rgb-255-255-255), 0.75);
                    backdrop-filter: blur(12px);
                    -webkit-backdrop-filter: blur(12px);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 var(--qiling-space-16) var(--qiling-space-36) rgba(var(--qiling-rgb-15-23-42), 0.12);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-card.is-highlight {
                    border-color: var(--ct-accent);
                    box-shadow: 0 var(--qiling-space-18) var(--qiling-space-40) rgba(var(--color-primary-rgb), 0.2);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-card-head {
                    display: grid;
                    grid-template-columns: auto 1fr auto;
                    align-items: center;
                    gap: var(--qiling-space-12);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-seal {
                    width: var(--qiling-space-48);
                    height: var(--qiling-space-48);
                    border-radius: var(--qiling-space-12);
                    background: var(--qiling-color-eff6ff);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                    border: 1px solid var(--qiling-color-dbeafe);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-seal img {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                    background: var(--color-neutral-0);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-seal-fallback {
                    color: var(--qiling-color-1e40af);
                    font-size: var(--qiling-text-rem-0p9);
                    font-weight: 700;
                    line-height: 1;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-head-text {
                    min-width: 0;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-title {
                    margin: 0;
                    font-size: var(--qiling-text-rem-1p05);
                    color: var(--color-neutral-900);
                    line-height: 1.35;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-short-name {
                    margin-top: var(--qiling-space-4);
                    color: var(--color-neutral-500);
                    font-size: var(--qiling-text-rem-0p82);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-status {
                    display: inline-flex;
                    align-items: center;
                    border-radius: 999px;
                    padding: var(--qiling-space-5) var(--qiling-space-10);
                    font-size: var(--qiling-text-rem-0p78);
                    font-weight: 600;
                    background: var(--ct-chip-bg);
                    color: var(--ct-chip-text);
                    white-space: nowrap;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-status.is-active {
                    background: var(--ct-status-active-bg, var(--qiling-color-dcfce7));
                    color: var(--ct-status-active-text, var(--qiling-color-166534));
                }
                #<?php echo esc_html( $module_id ); ?> .ct-status.is-progress {
                    background: var(--ct-status-progress-bg, var(--qiling-color-fef9c3));
                    color: var(--ct-status-progress-text, var(--color-warning-dark));
                }
                #<?php echo esc_html( $module_id ); ?> .ct-status.is-planned {
                    background: var(--ct-status-planned-bg, var(--qiling-color-e0e7ff));
                    color: var(--ct-status-planned-text, var(--color-primary-dark));
                }
                #<?php echo esc_html( $module_id ); ?> .ct-status.is-expired {
                    background: var(--ct-status-expired-bg, var(--qiling-color-fee2e2));
                    color: var(--ct-status-expired-text, var(--qiling-color-991b1b));
                }
                #<?php echo esc_html( $module_id ); ?> .ct-category {
                    margin-top: var(--qiling-space-12);
                    font-size: var(--qiling-text-rem-0p82);
                    color: var(--ct-accent);
                    font-weight: 600;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-meta {
                    margin-top: var(--qiling-space-12);
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: var(--qiling-space-8);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-meta-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: baseline;
                    gap: var(--qiling-space-12);
                    font-size: var(--qiling-text-rem-0p85);
                }
                #<?php echo esc_html( $module_id ); ?> .ct-meta-item span {
                    color: var(--color-neutral-500);
                    flex-shrink: 0;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-meta-item strong {
                    color: var(--color-neutral-900);
                    font-weight: 500;
                    text-align: right;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-desc {
                    margin: var(--qiling-space-12) 0 0;
                    color: var(--color-neutral-700);
                    font-size: var(--qiling-text-rem-0p9);
                    line-height: 1.65;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-points {
                    margin: var(--qiling-space-10) 0 0;
                    padding-left: var(--qiling-space-18);
                    color: var(--color-neutral-900);
                    font-size: var(--qiling-text-rem-0p9);
                    line-height: 1.7;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-link {
                    margin-top: var(--qiling-space-14);
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: var(--qiling-space-10);
                    padding: var(--qiling-space-9) var(--qiling-space-14);
                    text-decoration: none;
                    background: var(--ct-btn-bg);
                    color: var(--ct-btn-text);
                    font-size: var(--qiling-text-rem-0p86);
                    font-weight: 600;
                    transition: opacity .2s ease, transform .2s ease;
                }
                #<?php echo esc_html( $module_id ); ?> .ct-link:hover {
                    opacity: .9;
                    transform: translateY(-1px);
                }
                @media (max-width: 1280px) {
                    #<?php echo esc_html( $module_id ); ?>[data-cols="4"] .ct-grid {
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                    }
                }
                @media (max-width: 1024px) {
                    #<?php echo esc_html( $module_id ); ?> .ct-grid,
                    #<?php echo esc_html( $module_id ); ?>[data-cols="2"] .ct-grid,
                    #<?php echo esc_html( $module_id ); ?>[data-cols="3"] .ct-grid,
                    #<?php echo esc_html( $module_id ); ?>[data-cols="4"] .ct-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }
                @media (max-width: 680px) {
                    #<?php echo esc_html( $module_id ); ?> .ct-grid,
                    #<?php echo esc_html( $module_id ); ?>[data-cols="2"] .ct-grid,
                    #<?php echo esc_html( $module_id ); ?>[data-cols="3"] .ct-grid,
                    #<?php echo esc_html( $module_id ); ?>[data-cols="4"] .ct-grid {
                        grid-template-columns: 1fr;
                    }
                    #<?php echo esc_html( $module_id ); ?> .ct-card-head {
                        grid-template-columns: auto 1fr;
                    }
                    #<?php echo esc_html( $module_id ); ?> .ct-status {
                        grid-column: 1 / -1;
                        justify-self: flex-start;
                    }
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-note {
                    color: var(--color-neutral-400);
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-filter-btn {
                    background: var(--color-neutral-900);
                    border-color: var(--color-neutral-800);
                    color: var(--color-neutral-300);
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-filter-btn:hover,
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-filter-btn.is-active {
                    background: var(--ct-accent);
                    border-color: var(--ct-accent);
                    color: var(--color-neutral-0);
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-card {
                    --ct-card-bg: rgba(var(--qiling-rgb-15-23-42), 0.82);
                    --ct-card-border: rgba(var(--qiling-rgb-51-65-85), 0.9);
                    box-shadow: 0 var(--qiling-space-12) var(--qiling-space-30) rgba(var(--qiling-rgb-2-6-23), 0.45);
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?>[data-card-style="glass"] .ct-card {
                    background: rgba(var(--qiling-rgb-15-23-42), 0.72);
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-seal {
                    background: rgba(var(--qiling-rgb-30-58-138), 0.25);
                    border-color: rgba(var(--qiling-rgb-59-130-246), 0.4);
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-title,
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-meta-item strong,
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-points {
                    color: var(--color-neutral-100);
                }
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-short-name,
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-meta-item span,
                html.dark-mode #<?php echo esc_html( $module_id ); ?> .ct-desc {
                    color: var(--color-neutral-400);
                }
            </style>

            <?php if ( $enable_filter === 'yes' && count( $categories ) > 1 ) : ?>
                <script>
                    (function () {
                        var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                        if (!root) return;
                        var wrap = root.querySelector('.ct-filters');
                        if (!wrap) return;

                        wrap.addEventListener('click', function (event) {
                            var btn = event.target.closest('.ct-filter-btn');
                            if (!btn) return;

                            var filter = btn.getAttribute('data-filter') || 'all';
                            var cards = root.querySelectorAll('.ct-card');
                            var btns = wrap.querySelectorAll('.ct-filter-btn');

                            btns.forEach(function (item) {
                                item.classList.remove('is-active');
                            });
                            btn.classList.add('is-active');

                            cards.forEach(function (card) {
                                var cat = card.getAttribute('data-category') || '';
                                var show = (filter === 'all') || (cat === filter);
                                card.style.display = show ? '' : 'none';
                            });
                        });
                    })();
                </script>
            <?php endif; ?>
        </section>
        <?php
    }
}
