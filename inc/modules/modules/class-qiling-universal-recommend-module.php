<?php
/**
 * Qiling Universal Recommend Module - 通用推荐/专题模块
 *
 * 支持自动/手动/混合数据来源，适用于推荐位与专题入口。
 *
 * @package Developer_Starter
 * @since 2.1.3
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Qiling_Universal_Recommend_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-star-filled';
        $this->description = __( '通用推荐/专题模块（支持手动与自动内容）', 'developer-starter' );
    }

    public function get_id() {
        return 'qiling_universal_recommend';
    }

    public function get_name() {
        return __( '通用推荐/专题', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'qur_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => __( '推荐与专题', 'developer-starter' ) ),
            array( 'id' => 'qur_subtitle', 'type' => 'text', 'label' => __( '模块副标题', 'developer-starter' ), 'default' => __( '精选内容与专题入口', 'developer-starter' ) ),

            array(
                'id' => 'qur_show_topics',
                'type' => 'select',
                'label' => __( '显示专题入口', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'no',
            ),
            array(
                'id' => 'qur_topics',
                'type' => 'repeater',
                'label' => __( '专题入口列表', 'developer-starter' ),
                'dependency' => array( 'id' => 'qur_show_topics', 'value' => 'yes' ),
                'fields' => array(
                    array( 'id' => 'title', 'type' => 'text', 'label' => __( '专题标题', 'developer-starter' ) ),
                    array( 'id' => 'desc', 'type' => 'textarea', 'label' => __( '专题描述', 'developer-starter' ) ),
                    array( 'id' => 'image', 'type' => 'image', 'label' => __( '专题封面', 'developer-starter' ) ),
                    array( 'id' => 'badge', 'type' => 'text', 'label' => __( '专题角标', 'developer-starter' ) ),
                    array( 'id' => 'url', 'type' => 'text', 'label' => __( '跳转链接', 'developer-starter' ) ),
                    array(
                        'id' => 'target',
                        'type' => 'select',
                        'label' => __( '打开方式', 'developer-starter' ),
                        'options' => array(
                            '_self' => __( '当前窗口', 'developer-starter' ),
                            '_blank' => __( '新窗口', 'developer-starter' ),
                        ),
                        'default' => '_self',
                    ),
                ),
            ),

            array(
                'id' => 'qur_source_mode',
                'type' => 'select',
                'label' => __( '内容来源模式', 'developer-starter' ),
                'options' => array(
                    'auto' => __( '自动获取', 'developer-starter' ),
                    'manual' => __( '手动添加', 'developer-starter' ),
                    'mixed' => __( '混合模式（手动优先 + 自动补位）', 'developer-starter' ),
                ),
                'default' => 'auto',
            ),
            array( 'id' => 'qur_total_count', 'type' => 'number', 'label' => __( '显示数量', 'developer-starter' ), 'default' => '9' ),

            array(
                'id' => 'qur_category_ids',
                'type' => 'text',
                'label' => __( '文章分类 ID', 'developer-starter' ),
                'default' => '',
                'description' => __( '填写要推荐的文章分类 ID；多个 ID 用英文逗号分隔。留空时显示全部分类的最新文章。', 'developer-starter' ),
                'dependency' => array( 'id' => 'qur_source_mode', 'value' => array( 'auto', 'mixed' ) ),
            ),

            array(
                'id' => 'qur_manual_items',
                'type' => 'repeater',
                'label' => __( '手动推荐内容', 'developer-starter' ),
                'dependency' => array( 'id' => 'qur_source_mode', 'value' => array( 'manual', 'mixed' ) ),
                'default_items' => array(
                    array(
                        'item_type' => 'custom',
                        'title' => __( '专题精选', 'developer-starter' ),
                        'excerpt' => __( '用于展示重点内容与推荐位的通用卡片。', 'developer-starter' ),
                        'badge' => __( '推荐', 'developer-starter' ),
                        'target' => '_self',
                    ),
                    array(
                        'item_type' => 'custom',
                        'title' => __( '行业案例合集', 'developer-starter' ),
                        'excerpt' => __( '支持手动与自动混合，适配不同业务场景。', 'developer-starter' ),
                        'badge' => __( '专题', 'developer-starter' ),
                        'target' => '_self',
                    ),
                    array(
                        'item_type' => 'custom',
                        'title' => __( '产品更新速览', 'developer-starter' ),
                        'excerpt' => __( '可关联站内文章或外部链接。', 'developer-starter' ),
                        'badge' => __( '最新', 'developer-starter' ),
                        'target' => '_self',
                    ),
                ),
                'fields' => array(
                    array(
                        'id' => 'item_type',
                        'type' => 'select',
                        'label' => __( '内容类型', 'developer-starter' ),
                        'options' => array(
                            'content' => __( '站内内容（文章/页面/自定义类型）', 'developer-starter' ),
                            'custom' => __( '自定义卡片', 'developer-starter' ),
                            'external' => __( '外部链接', 'developer-starter' ),
                        ),
                        'default' => 'content',
                    ),
                    array(
                        'id' => 'post_id',
                        'type' => 'text',
                        'label' => __( '内容ID', 'developer-starter' ),
                        'description' => __( '填写文章/页面/自定义内容ID', 'developer-starter' ),
                        'dependency' => array( 'id' => 'item_type', 'value' => 'content' ),
                    ),
                    array( 'id' => 'title', 'type' => 'text', 'label' => __( '标题（可覆盖）', 'developer-starter' ) ),
                    array( 'id' => 'excerpt', 'type' => 'textarea', 'label' => __( '摘要（可覆盖）', 'developer-starter' ) ),
                    array( 'id' => 'image', 'type' => 'image', 'label' => __( '封面图片', 'developer-starter' ) ),
                    array( 'id' => 'url', 'type' => 'text', 'label' => __( '跳转链接（可覆盖）', 'developer-starter' ) ),
                    array( 'id' => 'badge', 'type' => 'text', 'label' => __( '角标文本', 'developer-starter' ) ),
                    array( 'id' => 'meta', 'type' => 'text', 'label' => __( '辅助信息（可选）', 'developer-starter' ) ),
                    array(
                        'id' => 'target',
                        'type' => 'select',
                        'label' => __( '打开方式', 'developer-starter' ),
                        'options' => array(
                            '_self' => __( '当前窗口', 'developer-starter' ),
                            '_blank' => __( '新窗口', 'developer-starter' ),
                        ),
                        'default' => '_self',
                    ),
                ),
            ),

            array(
                'id' => 'qur_layout',
                'type' => 'select',
                'label' => __( '展示布局', 'developer-starter' ),
                'options' => array(
                    'grid' => __( '网格', 'developer-starter' ),
                    'list' => __( '列表', 'developer-starter' ),
                    'ranking' => __( '排行榜（前三突出）', 'developer-starter' ),
                ),
                'default' => 'grid',
            ),
            array(
                'id' => 'qur_columns',
                'type' => 'select',
                'label' => __( '网格列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
                'dependency' => array( 'id' => 'qur_layout', 'value' => 'grid' ),
            ),
            array( 'id' => 'qur_gap', 'type' => 'text', 'label' => __( '卡片间距', 'developer-starter' ), 'default' => '24px' ),

            array(
                'id' => 'qur_image_ratio',
                'type' => 'select',
                'label' => __( '封面比例', 'developer-starter' ),
                'options' => array(
                    '16:9' => '16:9',
                    '4:3' => '4:3',
                    '1:1' => '1:1',
                    '3:4' => '3:4',
                    'custom' => __( '自定义高度', 'developer-starter' ),
                ),
                'default' => '16:9',
            ),
            array(
                'id' => 'qur_image_height',
                'type' => 'text',
                'label' => __( '自定义封面高度', 'developer-starter' ),
                'default' => '220px',
                'dependency' => array( 'id' => 'qur_image_ratio', 'value' => 'custom' ),
            ),
            array(
                'id' => 'qur_show_image',
                'type' => 'select',
                'label' => __( '显示封面', 'developer-starter' ),
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'yes',
            ),
            array(
                'id' => 'qur_show_excerpt',
                'type' => 'select',
                'label' => __( '显示摘要', 'developer-starter' ),
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'yes',
            ),
            array(
                'id' => 'qur_excerpt_length',
                'type' => 'number',
                'label' => __( '摘要长度', 'developer-starter' ),
                'default' => '28',
                'dependency' => array( 'id' => 'qur_show_excerpt', 'value' => 'yes' ),
            ),
            array(
                'id' => 'qur_show_date',
                'type' => 'select',
                'label' => __( '显示日期', 'developer-starter' ),
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'yes',
            ),
            array(
                'id' => 'qur_show_category',
                'type' => 'select',
                'label' => __( '显示分类', 'developer-starter' ),
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'no',
            ),
            array(
                'id' => 'qur_show_badge',
                'type' => 'select',
                'label' => __( '显示角标', 'developer-starter' ),
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'yes',
            ),

            array( 'id' => 'qur_bg_color', 'type' => 'text', 'label' => __( '背景颜色', 'developer-starter' ), 'default' => '' ),
            array(
                'id'          => 'qur_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '控制专题角标与推荐卡片角标背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            array( 'id' => 'qur_accent_color', 'type' => 'text', 'label' => __( '强调色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'qur_card_bg', 'type' => 'text', 'label' => __( '卡片背景色', 'developer-starter' ), 'default' => 'var(--color-neutral-0)' ),
            array( 'id' => 'qur_card_radius', 'type' => 'text', 'label' => __( '卡片圆角', 'developer-starter' ), 'default' => '16px' ),
            array( 'id' => 'qur_card_border', 'type' => 'text', 'label' => __( '卡片边框颜色', 'developer-starter' ), 'default' => 'var(--color-neutral-200)' ),
            array(
                'id' => 'qur_card_shadow',
                'type' => 'select',
                'label' => __( '卡片阴影', 'developer-starter' ),
                'options' => array(
                    'soft' => __( '柔和', 'developer-starter' ),
                    'strong' => __( '明显', 'developer-starter' ),
                    'none' => __( '无', 'developer-starter' ),
                ),
                'default' => 'soft',
            ),
            array( 'id' => 'qur_title_color', 'type' => 'text', 'label' => __( '标题颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'qur_text_color', 'type' => 'text', 'label' => __( '正文颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'qur_meta_color', 'type' => 'text', 'label' => __( '辅助信息颜色', 'developer-starter' ), 'default' => '' ),

            array( 'id' => 'qur_padding_top', 'type' => 'text', 'label' => __( '上边距', 'developer-starter' ), 'default' => '60px' ),
            array( 'id' => 'qur_padding_bottom', 'type' => 'text', 'label' => __( '下边距', 'developer-starter' ), 'default' => '60px' ),

            array(
                'id' => 'qur_more_text',
                'type' => 'text',
                'label' => __( '底部按钮文字', 'developer-starter' ),
                'default' => __( '查看更多', 'developer-starter' ),
            ),
            array(
                'id' => 'qur_more_url',
                'type' => 'text',
                'label' => __( '底部按钮链接', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id' => 'qur_more_target',
                'type' => 'select',
                'label' => __( '底部按钮打开方式', 'developer-starter' ),
                'options' => array(
                    '_self' => __( '当前窗口', 'developer-starter' ),
                    '_blank' => __( '新窗口', 'developer-starter' ),
                ),
                'default' => '_self',
            ),
            array(
                'id' => 'qur_more_btn_bg_color',
                'type' => 'color',
                'label' => __( '底部按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id' => 'qur_more_btn_text_color',
                'type' => 'color',
                'label' => __( '底部按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
                'default' => '',
            ),
            $this->get_button_border_color_field( 'qur_more_btn_border_color', __( '底部按钮边框颜色', 'developer-starter' ) ),
            array(
                'id' => 'qur_more_btn_hover_bg_color',
                'type' => 'color',
                'label' => __( '底部按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id' => 'qur_more_btn_hover_text_color',
                'type' => 'color',
                'label' => __( '底部按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
                'default' => '',
            ),
            $this->get_button_border_color_field( 'qur_more_btn_hover_border_color', __( '底部按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随底部按钮悬停背景颜色。', 'developer-starter' ) ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['qur_title'] ) ? $data['qur_title'] : '';
        $subtitle = isset( $data['qur_subtitle'] ) ? $data['qur_subtitle'] : '';

        $show_topics = isset( $data['qur_show_topics'] ) ? $data['qur_show_topics'] : 'no';
        $topics = isset( $data['qur_topics'] ) && is_array( $data['qur_topics'] ) ? $data['qur_topics'] : array();

        $source_mode = isset( $data['qur_source_mode'] ) ? $data['qur_source_mode'] : 'auto';
        $total_count = isset( $data['qur_total_count'] ) && $data['qur_total_count'] !== '' ? intval( $data['qur_total_count'] ) : 9;

        $layout = isset( $data['qur_layout'] ) ? $data['qur_layout'] : 'grid';
        $columns = isset( $data['qur_columns'] ) ? $data['qur_columns'] : '3';
        $gap = isset( $data['qur_gap'] ) && $data['qur_gap'] !== '' ? $data['qur_gap'] : '24px';

        $image_ratio = isset( $data['qur_image_ratio'] ) ? $data['qur_image_ratio'] : '16:9';
        $image_height = isset( $data['qur_image_height'] ) ? $data['qur_image_height'] : '220px';
        $show_image = ! isset( $data['qur_show_image'] ) || $data['qur_show_image'] !== 'no';
        $show_excerpt = ! isset( $data['qur_show_excerpt'] ) || $data['qur_show_excerpt'] !== 'no';
        $excerpt_length = isset( $data['qur_excerpt_length'] ) ? intval( $data['qur_excerpt_length'] ) : 28;
        $show_date = ! isset( $data['qur_show_date'] ) || $data['qur_show_date'] !== 'no';
        $show_category = isset( $data['qur_show_category'] ) && $data['qur_show_category'] === 'yes';
        $show_badge = ! isset( $data['qur_show_badge'] ) || $data['qur_show_badge'] !== 'no';

        $bg_color = isset( $data['qur_bg_color'] ) ? trim( $data['qur_bg_color'] ) : '';
        $accent_color = isset( $data['qur_accent_color'] ) ? trim( $data['qur_accent_color'] ) : '';
        $card_bg = isset( $data['qur_card_bg'] ) ? trim( $data['qur_card_bg'] ) : 'var(--color-neutral-0)';
        $card_radius = isset( $data['qur_card_radius'] ) ? trim( $data['qur_card_radius'] ) : '16px';
        $card_border = isset( $data['qur_card_border'] ) ? trim( $data['qur_card_border'] ) : 'var(--color-neutral-200)';
        $card_shadow = isset( $data['qur_card_shadow'] ) ? $data['qur_card_shadow'] : 'soft';
        $title_color = isset( $data['qur_title_color'] ) ? trim( $data['qur_title_color'] ) : '';
        $text_color = isset( $data['qur_text_color'] ) ? trim( $data['qur_text_color'] ) : '';
        $meta_color = isset( $data['qur_meta_color'] ) ? trim( $data['qur_meta_color'] ) : '';

        $pt = isset( $data['qur_padding_top'] ) && $data['qur_padding_top'] !== '' ? $data['qur_padding_top'] : '60px';
        $pb = isset( $data['qur_padding_bottom'] ) && $data['qur_padding_bottom'] !== '' ? $data['qur_padding_bottom'] : '60px';

        $more_text = isset( $data['qur_more_text'] ) ? $data['qur_more_text'] : '';
        $more_url = isset( $data['qur_more_url'] ) ? $data['qur_more_url'] : '';
        $more_target = isset( $data['qur_more_target'] ) ? $data['qur_more_target'] : '_self';
        $clean_css_value = static function( $value ) {
            $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
            if ( '' === $value ) {
                return '';
            }

            $hex = sanitize_hex_color( $value );
            if ( $hex ) {
                return $hex;
            }

            if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9\.\s,%]+\s*\)$/i', $value ) ) {
                return $value;
            }

            if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
                return $value;
            }

            return '';
        };
        $more_btn_bg_color = isset( $data['qur_more_btn_bg_color'] ) ? $clean_css_value( $data['qur_more_btn_bg_color'] ) : '';
        $more_btn_text_color = isset( $data['qur_more_btn_text_color'] ) ? $clean_css_value( $data['qur_more_btn_text_color'] ) : '';
        $more_btn_border_color = isset( $data['qur_more_btn_border_color'] ) ? $clean_css_value( $data['qur_more_btn_border_color'] ) : '';
        $more_btn_hover_bg_color = isset( $data['qur_more_btn_hover_bg_color'] ) ? $clean_css_value( $data['qur_more_btn_hover_bg_color'] ) : '';
        $more_btn_hover_text_color = isset( $data['qur_more_btn_hover_text_color'] ) ? $clean_css_value( $data['qur_more_btn_hover_text_color'] ) : '';
        $more_btn_hover_border_color = isset( $data['qur_more_btn_hover_border_color'] ) ? $clean_css_value( $data['qur_more_btn_hover_border_color'] ) : '';
        $badge_bg = isset( $data['qur_badge_bg'] ) ? $clean_css_value( $data['qur_badge_bg'] ) : '';

        $module_id = 'qur-' . uniqid();

        $shadow_value = '0 10px 30px var(--qiling-color-rgba-0-0-0-008)';
        if ( $card_shadow === 'strong' ) {
            $shadow_value = '0 18px 50px var(--qiling-color-rgba-0-0-0-012)';
        } elseif ( $card_shadow === 'none' ) {
            $shadow_value = 'none';
        }

        $style_parts = array();
        $style_parts[] = 'padding-top:' . esc_attr( $pt ) . ';';
        $style_parts[] = 'padding-bottom:' . esc_attr( $pb ) . ';';
        if ( $bg_color ) {
            $style_parts[] = ( strpos( $bg_color, 'gradient' ) !== false ) ? 'background:' . esc_attr( $bg_color ) . ';' : 'background-color:' . esc_attr( $bg_color ) . ';';
        }
        $style_parts[] = '--qur-gap:' . esc_attr( $gap ) . ';';
        $style_parts[] = '--qur-card-radius:' . esc_attr( $card_radius ) . ';';
        $style_parts[] = '--qur-card-bg:' . esc_attr( $card_bg ) . ';';
        $style_parts[] = '--qur-card-border:' . esc_attr( $card_border ) . ';';
        $style_parts[] = '--qur-card-shadow:' . esc_attr( $shadow_value ) . ';';
        if ( $accent_color ) {
            $style_parts[] = '--qur-accent:' . esc_attr( $accent_color ) . ';';
        }
        if ( $badge_bg ) {
            $style_parts[] = '--qiling-component-badge-bg:' . esc_attr( $badge_bg ) . ';';
        }
        if ( $title_color ) {
            $style_parts[] = '--qur-title-color:' . esc_attr( $title_color ) . ';';
        }
        if ( $text_color ) {
            $style_parts[] = '--qur-text-color:' . esc_attr( $text_color ) . ';';
        }
        if ( $meta_color ) {
            $style_parts[] = '--qur-meta-color:' . esc_attr( $meta_color ) . ';';
        }
        if ( $more_btn_bg_color ) {
            $style_parts[] = '--qur-more-btn-bg:' . esc_attr( $more_btn_bg_color ) . ';';
            $style_parts[] = '--qur-more-btn-border:' . esc_attr( $more_btn_bg_color ) . ';';
        }
        if ( $more_btn_text_color ) {
            $style_parts[] = '--qur-more-btn-text:' . esc_attr( $more_btn_text_color ) . ';';
        }
        if ( $more_btn_border_color ) {
            $style_parts[] = '--qur-more-btn-border:' . esc_attr( $more_btn_border_color ) . ';';
        }
        if ( $more_btn_hover_bg_color ) {
            $style_parts[] = '--qur-more-btn-hover-bg:' . esc_attr( $more_btn_hover_bg_color ) . ';';
            $style_parts[] = '--qur-more-btn-hover-border:' . esc_attr( $more_btn_hover_bg_color ) . ';';
        }
        if ( $more_btn_hover_text_color ) {
            $style_parts[] = '--qur-more-btn-hover-text:' . esc_attr( $more_btn_hover_text_color ) . ';';
        }
        if ( $more_btn_hover_border_color ) {
            $style_parts[] = '--qur-more-btn-hover-border:' . esc_attr( $more_btn_hover_border_color ) . ';';
        }

        $section_style = implode( '', $style_parts );

        $items = $this->build_items( $data, $source_mode, $total_count );
        ?>
        <section class="module module-qiling-universal-recommend" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container ql-ur-container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="ql-ur-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="ql-ur-title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="ql-ur-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ( $show_topics === 'yes' && ! empty( $topics ) ) : ?>
                    <div class="ql-ur-topics">
                        <?php foreach ( $topics as $topic ) :
                            $topic_title = isset( $topic['title'] ) ? $topic['title'] : '';
                            $topic_desc = isset( $topic['desc'] ) ? $topic['desc'] : '';
                            $topic_image = isset( $topic['image'] ) ? $topic['image'] : '';
                            $topic_badge = isset( $topic['badge'] ) ? $topic['badge'] : '';
                            $topic_url = isset( $topic['url'] ) ? $topic['url'] : '';
                            $topic_target = isset( $topic['target'] ) ? $topic['target'] : '_self';
                            $topic_link = $topic_url ? $topic_url : '#';
                        ?>
                            <a class="ql-ur-topic-card" href="<?php echo esc_url( $topic_link ); ?>" target="<?php echo esc_attr( $topic_target ); ?>">
                                <?php if ( $topic_image ) : ?>
                                    <div class="ql-ur-topic-media" style="background-image:url('<?php echo esc_url( $topic_image ); ?>');"></div>
                                <?php else : ?>
                                    <div class="ql-ur-topic-media ql-ur-topic-placeholder"></div>
                                <?php endif; ?>
                                <?php if ( $topic_badge ) : ?>
                                    <span class="ql-ur-badge"><?php echo esc_html( $topic_badge ); ?></span>
                                <?php endif; ?>
                                <div class="ql-ur-topic-content">
                                    <?php if ( $topic_title ) : ?>
                                        <h3><?php echo esc_html( $topic_title ); ?></h3>
                                    <?php endif; ?>
                                    <?php if ( $topic_desc ) : ?>
                                        <p><?php echo esc_html( $topic_desc ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $items ) ) : ?>
                    <?php
                    $media_style = '';
                    if ( $image_ratio === 'custom' ) {
                        $media_style = 'height:' . esc_attr( $image_height ) . ';';
                    } else {
                        $ratio_parts = explode( ':', $image_ratio );
                        if ( count( $ratio_parts ) === 2 ) {
                            $media_style = 'aspect-ratio:' . esc_attr( trim( $ratio_parts[0] ) ) . ' / ' . esc_attr( trim( $ratio_parts[1] ) ) . ';';
                        }
                    }
                    ?>
                    <?php if ( $layout === 'ranking' ) : ?>
                        <?php
                        $top_items = array_slice( $items, 0, 3 );
                        $rest_items = array_slice( $items, 3 );
                        ?>
                        <div class="ql-ur-ranking<?php echo ! $show_image ? ' no-image' : ''; ?>">
                            <?php if ( ! empty( $top_items ) ) : ?>
                                <div class="ql-ur-ranking-top">
                                    <?php foreach ( $top_items as $index => $item ) :
                                        $rank = $index + 1;
                                        $item_title = isset( $item['title'] ) ? $item['title'] : '';
                                        $item_excerpt = isset( $item['excerpt'] ) ? $item['excerpt'] : '';
                                        $item_image = isset( $item['image'] ) ? $item['image'] : '';
                                        $item_url = isset( $item['url'] ) ? $item['url'] : '#';
                                        $item_target = isset( $item['target'] ) ? $item['target'] : '_self';
                                        $item_badge = isset( $item['badge'] ) ? $item['badge'] : '';
                                        $item_date = isset( $item['date'] ) ? $item['date'] : '';
                                        $item_category = isset( $item['category'] ) ? $item['category'] : '';
                                        $item_meta = isset( $item['meta'] ) ? $item['meta'] : '';

                                        $meta_parts = array();
                                        if ( $show_date && $item_date ) {
                                            $meta_parts[] = $item_date;
                                        }
                                        if ( $show_category && $item_category ) {
                                            $meta_parts[] = $item_category;
                                        }
                                        if ( $item_meta ) {
                                            $meta_parts[] = $item_meta;
                                        }
                                        $meta_line = implode( ' · ', $meta_parts );
                                    ?>
                                        <article class="ql-ur-rank-card rank-<?php echo esc_attr( $rank ); ?>">
                                            <span class="ql-ur-rank-number"><?php echo esc_html( $rank ); ?></span>
                                            <?php if ( $show_image ) : ?>
                                                <a class="ql-ur-media" href="<?php echo esc_url( $item_url ); ?>" target="<?php echo esc_attr( $item_target ); ?>" style="<?php echo esc_attr( $media_style ); ?>">
                                                    <?php if ( $item_image ) : ?>
                                                        <img src="<?php echo esc_url( $item_image ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" loading="lazy" />
                                                    <?php else : ?>
                                                        <div class="ql-ur-media-placeholder"></div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endif; ?>
                                            <div class="ql-ur-content">
                                                <?php if ( $show_badge && $item_badge ) : ?>
                                                    <span class="ql-ur-badge badge-inline"><?php echo esc_html( $item_badge ); ?></span>
                                                <?php endif; ?>
                                                <?php if ( $meta_line ) : ?>
                                                    <div class="ql-ur-meta"><?php echo esc_html( $meta_line ); ?></div>
                                                <?php endif; ?>
                                                <?php if ( $item_title ) : ?>
                                                    <h3 class="ql-ur-card-title">
                                                        <a href="<?php echo esc_url( $item_url ); ?>" target="<?php echo esc_attr( $item_target ); ?>">
                                                            <?php echo esc_html( $item_title ); ?>
                                                        </a>
                                                    </h3>
                                                <?php endif; ?>
                                                <?php if ( $show_excerpt && $item_excerpt ) : ?>
                                                    <p class="ql-ur-excerpt"><?php echo esc_html( $item_excerpt ); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $rest_items ) ) : ?>
                                <div class="ql-ur-ranking-list">
                                    <?php foreach ( $rest_items as $index => $item ) :
                                        $rank = $index + 4;
                                        $item_title = isset( $item['title'] ) ? $item['title'] : '';
                                        $item_url = isset( $item['url'] ) ? $item['url'] : '#';
                                        $item_target = isset( $item['target'] ) ? $item['target'] : '_self';
                                        $item_badge = isset( $item['badge'] ) ? $item['badge'] : '';
                                        $item_date = isset( $item['date'] ) ? $item['date'] : '';
                                        $item_category = isset( $item['category'] ) ? $item['category'] : '';
                                        $item_meta = isset( $item['meta'] ) ? $item['meta'] : '';

                                        $meta_parts = array();
                                        if ( $show_date && $item_date ) {
                                            $meta_parts[] = $item_date;
                                        }
                                        if ( $show_category && $item_category ) {
                                            $meta_parts[] = $item_category;
                                        }
                                        if ( $item_meta ) {
                                            $meta_parts[] = $item_meta;
                                        }
                                        $meta_line = implode( ' · ', $meta_parts );
                                    ?>
                                        <div class="ql-ur-rank-row">
                                            <span class="ql-ur-rank-number"><?php echo esc_html( $rank ); ?></span>
                                            <div class="ql-ur-rank-info">
                                                <?php if ( $item_title ) : ?>
                                                    <a class="ql-ur-rank-title" href="<?php echo esc_url( $item_url ); ?>" target="<?php echo esc_attr( $item_target ); ?>">
                                                        <?php echo esc_html( $item_title ); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ( $meta_line ) : ?>
                                                    <div class="ql-ur-meta"><?php echo esc_html( $meta_line ); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ( $show_badge && $item_badge ) : ?>
                                                <span class="ql-ur-badge badge-inline"><?php echo esc_html( $item_badge ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <?php
                        $list_classes = 'ql-ur-list layout-' . $layout . ' cols-' . $columns;
                        if ( ! $show_image ) {
                            $list_classes .= ' no-image';
                        }
                        ?>
                        <div class="<?php echo esc_attr( $list_classes ); ?>">
                            <?php foreach ( $items as $item ) :
                                $item_title = isset( $item['title'] ) ? $item['title'] : '';
                                $item_excerpt = isset( $item['excerpt'] ) ? $item['excerpt'] : '';
                                $item_image = isset( $item['image'] ) ? $item['image'] : '';
                                $item_url = isset( $item['url'] ) ? $item['url'] : '#';
                                $item_target = isset( $item['target'] ) ? $item['target'] : '_self';
                                $item_badge = isset( $item['badge'] ) ? $item['badge'] : '';
                                $item_date = isset( $item['date'] ) ? $item['date'] : '';
                                $item_category = isset( $item['category'] ) ? $item['category'] : '';
                                $item_meta = isset( $item['meta'] ) ? $item['meta'] : '';

                                $meta_parts = array();
                                if ( $show_date && $item_date ) {
                                    $meta_parts[] = $item_date;
                                }
                                if ( $show_category && $item_category ) {
                                    $meta_parts[] = $item_category;
                                }
                                if ( $item_meta ) {
                                    $meta_parts[] = $item_meta;
                                }
                                $meta_line = implode( ' · ', $meta_parts );
                            ?>
                                <article class="ql-ur-card">
                                    <?php if ( $show_image ) : ?>
                                        <a class="ql-ur-media" href="<?php echo esc_url( $item_url ); ?>" target="<?php echo esc_attr( $item_target ); ?>" style="<?php echo esc_attr( $media_style ); ?>">
                                            <?php if ( $item_image ) : ?>
                                                <img src="<?php echo esc_url( $item_image ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" loading="lazy" />
                                            <?php else : ?>
                                                <div class="ql-ur-media-placeholder"></div>
                                            <?php endif; ?>
                                            <?php if ( $show_badge && $item_badge ) : ?>
                                                <span class="ql-ur-badge"><?php echo esc_html( $item_badge ); ?></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>

                                    <div class="ql-ur-content">
                                        <?php if ( ! $show_image && $show_badge && $item_badge ) : ?>
                                            <span class="ql-ur-badge badge-inline"><?php echo esc_html( $item_badge ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $meta_line ) : ?>
                                            <div class="ql-ur-meta"><?php echo esc_html( $meta_line ); ?></div>
                                        <?php endif; ?>
                                        <?php if ( $item_title ) : ?>
                                            <h3 class="ql-ur-card-title">
                                                <a href="<?php echo esc_url( $item_url ); ?>" target="<?php echo esc_attr( $item_target ); ?>">
                                                    <?php echo esc_html( $item_title ); ?>
                                                </a>
                                            </h3>
                                        <?php endif; ?>
                                        <?php if ( $show_excerpt && $item_excerpt ) : ?>
                                            <p class="ql-ur-excerpt"><?php echo esc_html( $item_excerpt ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="ql-ur-empty">
                        <?php esc_html_e( '暂无推荐内容，请在模块设置中添加。', 'developer-starter' ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( $more_url ) : ?>
                    <div class="ql-ur-more">
                        <a class="ql-ur-more-btn" href="<?php echo esc_url( $more_url ); ?>" target="<?php echo esc_attr( $more_target ); ?>">
                            <?php echo esc_html( $more_text ? $more_text : __( '查看更多', 'developer-starter' ) ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    private function build_items( $data, $source_mode, $total_count ) {
        $items = array();
        $manual_items = isset( $data['qur_manual_items'] ) && is_array( $data['qur_manual_items'] ) ? $data['qur_manual_items'] : array();
        $manual_normalized = $this->normalize_manual_items( $manual_items, $data );

        if ( $source_mode === 'manual' ) {
            $items = $manual_normalized;
        } elseif ( $source_mode === 'auto' ) {
            $items = $this->query_auto_items( $data, $total_count, array() );
        } else {
            $items = $manual_normalized;
            $manual_ids = array();
            foreach ( $manual_normalized as $manual_item ) {
                if ( ! empty( $manual_item['post_id'] ) ) {
                    $manual_ids[] = intval( $manual_item['post_id'] );
                }
            }
            $need = max( 0, $total_count - count( $items ) );
            if ( $need > 0 ) {
                $items = array_merge( $items, $this->query_auto_items( $data, $need, $manual_ids ) );
            }
        }

        if ( $total_count > 0 ) {
            $items = array_slice( $items, 0, $total_count );
        }

        return $items;
    }

    private function normalize_manual_items( $manual_items, $data ) {
        $items = array();
        $excerpt_length = isset( $data['qur_excerpt_length'] ) ? intval( $data['qur_excerpt_length'] ) : 28;

        foreach ( $manual_items as $item ) {
            $item_type = isset( $item['item_type'] ) ? $item['item_type'] : 'content';
            $post_id = isset( $item['post_id'] ) ? intval( $item['post_id'] ) : 0;
            $post = $post_id ? get_post( $post_id ) : null;

            $title = isset( $item['title'] ) && $item['title'] !== '' ? $item['title'] : '';
            $excerpt = isset( $item['excerpt'] ) && $item['excerpt'] !== '' ? $item['excerpt'] : '';
            $image = isset( $item['image'] ) ? $item['image'] : '';
            $url = isset( $item['url'] ) ? $item['url'] : '';
            $badge = isset( $item['badge'] ) ? $item['badge'] : '';
            $meta = isset( $item['meta'] ) ? $item['meta'] : '';
            $target = isset( $item['target'] ) ? $item['target'] : '_self';
            $date = '';
            $category = '';

            if ( $item_type === 'content' && $post ) {
                if ( ! $title ) {
                    $title = get_the_title( $post );
                }
                if ( ! $excerpt ) {
                    $excerpt = wp_trim_words( get_the_excerpt( $post ), $excerpt_length );
                }
                if ( ! $url ) {
                    $url = get_permalink( $post );
                }
                if ( ! $image ) {
                    $image = $this->get_post_image( $post->ID, 'large' );
                }
                $date = get_the_date( '', $post );
                $category = $this->get_post_primary_term( $post->ID, $data );
            }

            if ( ! $url ) {
                $url = '#';
            }

            if ( ! $title && ! $image && ! $excerpt ) {
                continue;
            }

            $items[] = array(
                'post_id' => $post_id,
                'title' => $title,
                'excerpt' => $excerpt,
                'image' => $image,
                'url' => $url,
                'badge' => $badge,
                'meta' => $meta,
                'target' => $target,
                'date' => $date,
                'category' => $category,
            );
        }

        return $items;
    }

    private function query_auto_items( $data, $count, $exclude_ids ) {
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $count,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $exclude_ids = array_filter( array_map( 'intval', $exclude_ids ) );
        if ( ! empty( $exclude_ids ) ) {
            $args['post__not_in'] = $exclude_ids;
        }

        $category_ids_raw = isset( $data['qur_category_ids'] ) ? trim( (string) $data['qur_category_ids'] ) : '';
        if ( '' === $category_ids_raw && isset( $data['qur_auto_taxonomy'], $data['qur_auto_terms'] ) && 'category' === trim( (string) $data['qur_auto_taxonomy'] ) ) {
            $category_ids_raw = trim( (string) $data['qur_auto_terms'] );
        }
        $category_ids = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', $category_ids_raw ) ) ) ) );
        if ( ! empty( $category_ids ) ) {
            $args['category__in'] = $category_ids;
        }

        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query(
                $args,
                'module_qiling_universal_recommend',
                array(
                    'needs_pagination' => false,
                )
            );
        } else {
            $query = new \WP_Query( $args );
        }
        $items = array();
        $excerpt_length = isset( $data['qur_excerpt_length'] ) ? intval( $data['qur_excerpt_length'] ) : 28;

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $items[] = array(
                    'post_id' => $post_id,
                    'title' => get_the_title(),
                    'excerpt' => wp_trim_words( get_the_excerpt(), $excerpt_length ),
                    'image' => $this->get_post_image( $post_id, 'large' ),
                    'url' => get_permalink(),
                    'badge' => $this->get_post_primary_term( $post_id, $data ),
                    'meta' => '',
                    'target' => '_self',
                    'date' => get_the_date(),
                    'category' => $this->get_post_primary_term( $post_id, $data ),
                );
            }
            wp_reset_postdata();
        }

        return $items;
    }

    private function get_post_image( $post_id, $size = 'large' ) {
        $image_url = '';
        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
            $image_url = developer_starter_get_thumbnail_url( $post_id, $size );
        } elseif ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
            $image_url = developer_starter_get_featured_image_url( $post_id, $size );
        } elseif ( has_post_thumbnail( $post_id ) ) {
            $image_url = get_the_post_thumbnail_url( $post_id, $size );
        }
        if ( empty( $image_url ) && function_exists( 'developer_starter_get_first_image' ) ) {
            $image_url = developer_starter_get_first_image( $post_id );
        }
        return $image_url;
    }

    private function get_post_primary_term( $post_id, $data ) {
        $categories = get_the_category( $post_id );
        if ( ! empty( $categories ) ) {
            return $categories[0]->name;
        }

        return '';
    }
}
