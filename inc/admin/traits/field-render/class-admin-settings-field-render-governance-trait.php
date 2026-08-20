<?php
/**
 * Admin settings governance field render trait.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_Governance_Trait {

    private function render_header_settings_governance_field( $options ) {
        $this->render_settings_governance_assets_once();
        $design_base_url  = admin_url( 'admin.php?page=developer-starter-settings&tab=design' );
        $header_style_url = $design_base_url . '#setting-row-design_component_header_bg';
        $nav_style_url    = $design_base_url . '#setting-row-design_component_nav_link';
        $phone_style_url  = $design_base_url . '#setting-row-design_component_header_phone_bg';

        echo '<tr class="ds-settings-governance-row"><th scope="row">' . esc_html__( '页头设置指引', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-settings-governance ds-settings-governance--clean">';
        echo '<p class="ds-settings-governance-status"><strong>' . esc_html__( '页头外观按统一入口维护。', 'developer-starter' ) . '</strong></p>';
        echo '<p>' . wp_kses_post( __( '页头颜色、文字 Logo、桌面导航、电话按钮等视觉样式，在 <strong>全局样式 -> 全局组件样式中心</strong> 调整。单页差异在页面装修的 <strong>页面设置 -> 页面组件样式</strong> 调整。', 'developer-starter' ) ) . '</p>';
        echo '<p class="description">' . esc_html__( '本页用于设置 Logo、菜单位置、固定 / 透明头部和功能入口；颜色与字号在全局样式中统一调整。', 'developer-starter' ) . '</p>';
        echo '<div class="ds-settings-governance-actions">';
        echo '<a class="button button-secondary ds-settings-governance-link" href="' . esc_url( $header_style_url ) . '">' . esc_html__( '调整页头样式', 'developer-starter' ) . '</a>';
        echo '<a class="button button-secondary ds-settings-governance-link" href="' . esc_url( $nav_style_url ) . '">' . esc_html__( '调整导航样式', 'developer-starter' ) . '</a>';
        echo '<a class="button button-secondary ds-settings-governance-link" href="' . esc_url( $phone_style_url ) . '">' . esc_html__( '调整电话按钮', 'developer-starter' ) . '</a>';
        echo '</div>';
        echo '</div>';
        echo '</td></tr>';
    }

    private function render_footer_settings_governance_field( $options ) {
        $this->render_settings_governance_assets_once();
        $footer_base_url    = admin_url( 'admin.php?page=developer-starter-settings&tab=footer' );
        $footer_style_url   = $footer_base_url . '#setting-row-footer_visual_main_bg';
        $footer_heading_url = $footer_base_url . '#setting-row-footer_visual_main_heading';
        $footer_link_url    = $footer_base_url . '#setting-row-footer_visual_wave_enable';

        $builder_enable = ! empty( $options['footer_builder_enable'] ) && '1' === (string) $options['footer_builder_enable'];
        $builder_page   = isset( $options['footer_builder_page_id'] ) ? absint( $options['footer_builder_page_id'] ) : 0;
        $builder_region_count = count( array_filter( array_map( 'absint', array(
            isset( $options['footer_builder_main_page_id'] ) ? $options['footer_builder_main_page_id'] : 0,
            isset( $options['footer_builder_friend_page_id'] ) ? $options['footer_builder_friend_page_id'] : 0,
            isset( $options['footer_builder_bottom_page_id'] ) ? $options['footer_builder_bottom_page_id'] : 0,
        ) ) ) );
        $builder_title  = $builder_page ? get_the_title( $builder_page ) : '';
        $builder_mode   = isset( $options['footer_builder_position'] ) ? (string) $options['footer_builder_position'] : 'replace_widgets';
        $mode_label     = $this->get_footer_builder_mode_label( $builder_mode );

        if ( $builder_enable ) {
            if ( $builder_region_count > 0 ) {
                $builder_message = sprintf(
                    /* translators: %d: configured footer builder region count */
                    _n( '当前已独立装修 %d 个页脚区域，独立区域优先；其余区域继续使用默认内容或旧替换配置。', '当前已独立装修 %d 个页脚区域，独立区域优先；其余区域继续使用默认内容或旧替换配置。', $builder_region_count, 'developer-starter' ),
                    $builder_region_count
                );
            } elseif ( 'replace_all' === $builder_mode ) {
                $builder_message = $builder_title
                    ? sprintf(
                        /* translators: 1: replace mode label, 2: page title */
                        __( '当前已启用页脚装修，替换范围：%1$s。装修页面《%2$s》接管整段页脚内容，样式以装修页面为准。', 'developer-starter' ),
                        $mode_label,
                        $builder_title
                    )
                    : sprintf(
                        /* translators: %s: replace mode label */
                        __( '当前已启用页脚装修，替换范围：%s。整段页脚内容由装修页面接管，样式以装修页面为准。', 'developer-starter' ),
                        $mode_label
                    );
            } else {
                $builder_message = $builder_title
                    ? sprintf(
                        /* translators: 1: replace mode label, 2: page title */
                        __( '当前已启用页脚装修，替换范围：%1$s。装修页面《%2$s》只接管指定区域，其余区域仍使用本选项卡的内容字段。', 'developer-starter' ),
                        $mode_label,
                        $builder_title
                    )
                    : sprintf(
                        /* translators: %s: replace mode label */
                        __( '当前已启用页脚装修，替换范围：%s。装修页面只接管指定区域，其余区域仍使用本选项卡的内容字段。', 'developer-starter' ),
                        $mode_label
                    );
            }
        } else {
            $builder_message = __( '当前未启用页脚装修，页脚内容由本选项卡里的站点信息、默认文案、快速链接和备案信息等字段组合生成。', 'developer-starter' );
        }

        echo '<tr class="ds-settings-governance-row"><th scope="row">' . esc_html__( '页脚设置指引', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-settings-governance ds-settings-governance--clean">';
        echo '<p class="ds-settings-governance-status"><strong>' . esc_html__( '页脚内容和外观分开维护。', 'developer-starter' ) . '</strong></p>';
        echo '<p>' . esc_html__( '页脚内容在当前页维护；页脚背景、标题和文字样式在全局样式中统一调整。', 'developer-starter' ) . '</p>';
        echo '<p>' . wp_kses_post( __( '如需当前页单独覆盖，三段背景、文字颜色、顶部波浪和动画范围可在下方 <strong>页脚三段式视觉装修</strong> 中调整。留空时继续跟随全局组件样式。', 'developer-starter' ) ) . '</p>';
        echo '<p class="description">' . esc_html( $builder_message ) . '</p>';
        echo '<div class="ds-settings-governance-actions">';
        echo '<a class="button button-secondary ds-settings-governance-link" href="' . esc_url( $footer_style_url ) . '">' . esc_html__( '调整信息区背景', 'developer-starter' ) . '</a>';
        echo '<a class="button button-secondary ds-settings-governance-link" href="' . esc_url( $footer_heading_url ) . '">' . esc_html__( '调整页脚标题', 'developer-starter' ) . '</a>';
        echo '<a class="button button-secondary ds-settings-governance-link" href="' . esc_url( $footer_link_url ) . '">' . esc_html__( '开启顶部波浪', 'developer-starter' ) . '</a>';
        echo '</div>';
        echo '</div>';
        echo '</td></tr>';
    }

    private function render_design_quick_start_field( $options ) {
        $this->render_settings_governance_assets_once();

        $base_url = admin_url( 'admin.php?page=developer-starter-settings&tab=design' );
        $items = array(
            array(
                'title' => __( '整体风格', 'developer-starter' ),
                'desc'  => __( '查看当前预设、差异和工作台预览。', 'developer-starter' ),
                'href'  => $base_url . '#setting-row-design_tokens_preview',
            ),
            array(
                'title' => __( '改品牌颜色', 'developer-starter' ),
                'desc'  => __( '主色、辅助色、标题色这些最常用入口。', 'developer-starter' ),
                'href'  => $base_url . '#setting-row-design_primary_color',
            ),
            array(
                'title' => __( '改排版布局', 'developer-starter' ),
                'desc'  => __( '字体、容器宽度、区块间距都在这里。', 'developer-starter' ),
                'href'  => $base_url . '#setting-row-design_typography_system',
            ),
            array(
                'title' => __( '改按钮卡片表单', 'developer-starter' ),
                'desc'  => __( '普通站点最常改的基础组件外观。', 'developer-starter' ),
                'href'  => $base_url . '#setting-row-design_component_button_bg',
            ),
            array(
                'title' => __( '改页头导航', 'developer-starter' ),
                'desc'  => __( '页头背景、导航文字、电话按钮都在这里。', 'developer-starter' ),
                'href'  => $base_url . '#setting-row-design_component_header_bg',
            ),
            array(
                'title' => __( '改页脚样式', 'developer-starter' ),
                'desc'  => __( '页脚背景、标题、链接颜色从这里改。', 'developer-starter' ),
                'href'  => $base_url . '#setting-row-design_component_footer_bg',
            ),
        );

        echo '<tr id="setting-row-design_quick_start" data-setting-id="design_quick_start"><th scope="row">' . esc_html__( '常用入口', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-settings-quick-start">';
        echo '<div class="ds-settings-quick-start__head">';
        echo '<strong>' . esc_html__( '普通站点常改这几处', 'developer-starter' ) . '</strong>';
        echo '<p>' . esc_html__( '可从这里快速定位常用设置。单页差异请在页面装修的“页面设置”中维护。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '<div class="ds-settings-quick-start__grid">';
        foreach ( $items as $item ) {
            echo '<a class="ds-settings-quick-start__card" href="' . esc_url( $item['href'] ) . '">';
            echo '<strong>' . esc_html( $item['title'] ) . '</strong>';
            echo '<span>' . esc_html( $item['desc'] ) . '</span>';
            echo '</a>';
        }
        echo '</div>';
        echo '</div>';
        echo '</td></tr>';
    }

    private function render_settings_governance_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-settings-governance {
                border: 1px solid #d0d7de;
                border-radius: 10px;
                background: #fff;
                padding: 14px 16px;
            }
            .ds-settings-governance--warning {
                border-color: #f59e0b;
                background: #fffaf0;
            }
            .ds-settings-governance--clean {
                border-color: #10b981;
                background: #f0fdf4;
            }
            .ds-settings-governance > p {
                margin: 0 0 10px;
            }
            .ds-settings-governance > p:last-child {
                margin-bottom: 0;
            }
            .ds-settings-governance-status {
                font-size: 13px;
            }
            .ds-settings-governance-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin: 12px 0 8px;
            }
            .ds-settings-governance-tag {
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                padding: 0 10px;
                border-radius: 999px;
                background: rgba(15, 23, 42, 0.08);
                color: #0f172a;
                font-size: 12px;
                font-weight: 600;
            }
            .ds-settings-governance-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 12px;
            }
            .ds-settings-governance-link {
                text-decoration: none;
            }
            .ds-settings-quick-start {
                display: grid;
                gap: 14px;
                padding: 16px 18px;
                border: 1px solid #dbe3f0;
                border-radius: 16px;
                background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
            }
            .ds-settings-quick-start__head strong {
                display: block;
                font-size: 15px;
                color: #0f172a;
            }
            .ds-settings-quick-start__head p {
                margin: 6px 0 0;
                color: #64748b;
            }
            .ds-settings-quick-start__grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
            }
            .ds-settings-quick-start__card {
                display: grid;
                gap: 6px;
                min-height: 92px;
                padding: 14px 16px;
                border: 1px solid #dbe3f0;
                border-radius: 14px;
                background: #ffffff;
                color: #0f172a;
                text-decoration: none;
                transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
            }
            .ds-settings-quick-start__card strong {
                font-size: 14px;
                color: #0f172a;
            }
            .ds-settings-quick-start__card span {
                color: #64748b;
                line-height: 1.6;
            }
            .ds-settings-quick-start__card:hover,
            .ds-settings-quick-start__card:focus {
                border-color: #93c5fd;
                box-shadow: 0 12px 24px rgba(37, 99, 235, 0.1);
                transform: translateY(-1px);
                outline: none;
            }
        </style>
        <?php
    }

    /**
     * @param string $position Replace mode.
     * @return string
     */
    private function get_footer_builder_mode_label( $position ) {
        $labels = array(
            'replace_widgets'      => __( '替换上方主区域', 'developer-starter' ),
            'replace_friend_links' => __( '替换中间友情链接区域', 'developer-starter' ),
            'replace_bottom'       => __( '替换底部版权备案区域', 'developer-starter' ),
            'replace_all'          => __( '替换整个页脚', 'developer-starter' ),
        );

        return isset( $labels[ $position ] ) ? $labels[ $position ] : __( '替换上方主区域', 'developer-starter' );
    }

    /**
     * @param array<string,mixed> $options
     * @param array<string,mixed> $defaults
     * @return bool
     */
    private function has_settings_custom_values( $options, $defaults ) {
        foreach ( $defaults as $key => $default ) {
            if ( $this->settings_option_differs_from_default( $options, $key, $default ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,mixed> $options
     * @param string              $key
     * @param mixed               $default
     * @return bool
     */
    private function settings_option_differs_from_default( $options, $key, $default = '' ) {
        if ( ! array_key_exists( $key, $options ) ) {
            return false;
        }

        $value = $options[ $key ];
        if ( is_array( $value ) || is_array( $default ) ) {
            return wp_json_encode( $value ) !== wp_json_encode( $default );
        }

        if ( is_bool( $value ) ) {
            $value = $value ? '1' : '';
        }
        if ( is_bool( $default ) ) {
            $default = $default ? '1' : '';
        }

        if ( ! is_scalar( $value ) && null !== $value ) {
            return true;
        }

        if ( ! is_scalar( $default ) && null !== $default ) {
            $default = '';
        }

        return trim( (string) $value ) !== trim( (string) $default );
    }
}
