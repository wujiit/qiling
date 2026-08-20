<?php
/**
 * Admin settings international center field render trait.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_International_Center_Trait {

    private function render_international_center_overview_field( $options ) {
        $this->render_international_center_overview_assets_once();

        $cards = $this->build_international_center_cards( is_array( $options ) ? $options : array() );

        echo '<tr id="setting-row-international_center_overview" data-setting-id="international_center_overview" class="ds-i18n-center-row"><th scope="row">' . esc_html__( '国际化中心', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-i18n-center">';
        echo '<div class="ds-i18n-center__head">';
        echo '<div>';
        echo '<strong>' . esc_html__( '国际化中心', 'developer-starter' ) . '</strong>';
        echo '<p>' . esc_html__( '把 Cookie 合规、AI 本地化装修、多语言 SEO 和 Schema 结构化数据收拢到同一个入口；当前只读取现有配置，不新增数据表。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '<span>' . esc_html__( '阶段 1 MVP', 'developer-starter' ) . '</span>';
        echo '</div>';

        echo '<div class="ds-i18n-center__grid">';
        foreach ( $cards as $card ) {
            if ( ! is_array( $card ) ) {
                continue;
            }

            $status = isset( $card['status'] ) ? sanitize_key( (string) $card['status'] ) : 'not_configured';
            if ( ! in_array( $status, array( 'not_configured', 'partial', 'normal', 'risk' ), true ) ) {
                $status = 'not_configured';
            }

            $title = isset( $card['title'] ) ? (string) $card['title'] : '';
            $description = isset( $card['description'] ) ? (string) $card['description'] : '';
            $meta = isset( $card['meta'] ) ? (string) $card['meta'] : '';
            $href = isset( $card['href'] ) ? (string) $card['href'] : '#';
            $action = isset( $card['action'] ) ? (string) $card['action'] : __( '查看设置', 'developer-starter' );

            echo '<article class="ds-i18n-center-card is-' . esc_attr( str_replace( '_', '-', $status ) ) . '">';
            echo '<div class="ds-i18n-center-card__top">';
            echo '<h3>' . esc_html( $title ) . '</h3>';
            echo '<span class="ds-i18n-center-card__status">' . esc_html( $this->get_international_center_status_label( $status ) ) . '</span>';
            echo '</div>';
            if ( '' !== $description ) {
                echo '<p>' . esc_html( $description ) . '</p>';
            }
            if ( '' !== $meta ) {
                echo '<small>' . esc_html( $meta ) . '</small>';
            }
            echo '<a class="button button-secondary ds-i18n-center-card__link" href="' . esc_url( $href ) . '">' . esc_html( $action ) . '</a>';
            echo '</article>';
        }
        echo '</div>';
        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * Build card data for the international center.
     *
     * @param array<string,mixed> $options Theme options.
     * @return array<int,array<string,string>>
     */
    private function build_international_center_cards( $options ) {
        $readiness = $this->build_international_launch_readiness( $options );
        $readiness_summary = isset( $readiness['summary'] ) && is_array( $readiness['summary'] ) ? $readiness['summary'] : array();

        $seo = $this->build_international_seo_diagnostics( $options );
        $seo_summary = isset( $seo['summary'] ) && is_array( $seo['summary'] ) ? $seo['summary'] : array();

        $cookie_card = $this->build_international_center_cookie_card( $options, $readiness_summary );
        $ai_card = $this->build_international_center_ai_card( $options );
        $seo_card = $this->build_international_center_seo_card( $options, $seo_summary );
        $schema_card = $this->build_international_center_schema_card( $options );

        return array( $cookie_card, $ai_card, $seo_card, $schema_card );
    }

    /**
     * @param array<string,mixed> $options Theme options.
     * @param array<string,mixed> $readiness_summary Readiness summary.
     * @return array<string,string>
     */
    private function build_international_center_cookie_card( $options, $readiness_summary ) {
        $third_party_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_third_party_code_enable', '' );
        $cookie_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_cookie_notice_enable', '' );
        $policy_url = trim( (string) $this->get_international_option_value( $options, 'international_cookie_policy_url', '' ) );
        $code_output = isset( $readiness_summary['code_output'] ) ? (string) $readiness_summary['code_output'] : __( '未输出', 'developer-starter' );
        $region = $this->get_international_cookie_region_label( (string) $this->get_international_option_value( $options, 'international_cookie_region_preset', 'cross_border' ) );
        $version = trim( (string) $this->get_international_option_value( $options, 'international_cookie_consent_version', '2.0' ) );
        if ( '' === $version ) {
            $version = '2.0';
        }

        $configured_groups = 0;
        $active_consent_groups = 0;
        foreach ( $this->get_international_code_groups_for_readiness() as $group_id => $group ) {
            if ( ! is_array( $group ) ) {
                continue;
            }
            $enable_key = isset( $group['enable'] ) ? (string) $group['enable'] : '';
            $content_key = isset( $group['content'] ) ? (string) $group['content'] : '';
            $group_enabled = '' !== $enable_key && '1' === (string) $this->get_international_option_value( $options, $enable_key, '' );
            $has_content = '' !== $content_key && '' !== trim( (string) $this->get_international_option_value( $options, $content_key, '' ) );
            $category = $this->get_international_code_group_category( $options, (string) $group_id, $group );
            $requires_consent = $this->international_cookie_category_requires_consent( $category );

            if ( $has_content ) {
                $configured_groups++;
            }
            if ( $third_party_enabled && $group_enabled && $has_content && $requires_consent ) {
                $active_consent_groups++;
            }
        }

        if ( ! $cookie_enabled && $active_consent_groups > 0 ) {
            $status = 'risk';
            $description = __( '有非必要分类第三方代码会输出，但 Cookie 提示尚未开启。', 'developer-starter' );
        } elseif ( $cookie_enabled && '' === $policy_url ) {
            $status = 'partial';
            $description = __( 'Cookie 提示已开启，建议补充隐私政策链接。', 'developer-starter' );
        } elseif ( $cookie_enabled ) {
            $status = 'normal';
            $description = __( 'Cookie 提示已开启，可按分类控制第三方代码。', 'developer-starter' );
        } elseif ( $configured_groups > 0 ) {
            $status = 'partial';
            $description = __( '已有第三方代码配置，Cookie 提示尚未开启。', 'developer-starter' );
        } else {
            $status = 'not_configured';
            $description = __( '尚未启用 Cookie 提示或新增第三方代码。', 'developer-starter' );
        }

        return array(
            'title'       => __( 'Cookie 合规', 'developer-starter' ),
            'status'      => $status,
            'description' => $description,
            'meta'        => sprintf( __( '第三方代码：%1$s；地区：%2$s；版本：%3$s', 'developer-starter' ), $code_output, $region, $version ),
            'href'        => '#setting-row-international_cookie_notice_enable',
            'action'      => __( '配置 Cookie', 'developer-starter' ),
        );
    }

    /**
     * @param array<string,mixed> $options Theme options.
     * @return array<string,string>
     */
    private function build_international_center_ai_card( $options ) {
        $ai_enabled = '1' === (string) $this->get_international_option_value( $options, 'ai_builder_enable', '' );
        $enabled_connections = $this->count_international_center_enabled_ai_connections( $options );

        if ( $ai_enabled && $enabled_connections > 0 ) {
            $status = 'normal';
            $description = __( 'AI 装修已开启，并已有可用连接作为本地化模式底座。', 'developer-starter' );
        } elseif ( $ai_enabled ) {
            $status = 'partial';
            $description = __( 'AI 装修已开启，但还需要配置可用 AI 连接。', 'developer-starter' );
        } elseif ( $enabled_connections > 0 ) {
            $status = 'partial';
            $description = __( '已配置 AI 连接，AI 装修入口尚未开启。', 'developer-starter' );
        } else {
            $status = 'not_configured';
            $description = __( '尚未开启 AI 装修或配置 AI 连接。', 'developer-starter' );
        }

        return array(
            'title'       => __( 'AI 本地化装修', 'developer-starter' ),
            'status'      => $status,
            'description' => $description,
            'meta'        => sprintf( __( '可用连接：%d 个', 'developer-starter' ), $enabled_connections ),
            'href'        => admin_url( 'admin.php?page=developer-starter-settings&tab=ai#setting-row-ai_builder_enable' ),
            'action'      => __( '打开 AI 装修设置', 'developer-starter' ),
        );
    }

    /**
     * @param array<string,mixed> $options Theme options.
     * @param array<string,mixed> $seo_summary SEO summary.
     * @return array<string,string>
     */
    private function build_international_center_seo_card( $options, $seo_summary ) {
        $mode = (string) $this->get_international_option_value( $options, 'frontend_language_switch_mode', '' );
        $warning_count = isset( $seo_summary['warning_count'] ) ? absint( $seo_summary['warning_count'] ) : 0;
        $mode_label = isset( $seo_summary['mode_label'] ) ? (string) $seo_summary['mode_label'] : __( '关闭', 'developer-starter' );
        $has_aifanyi = ! empty( $seo_summary['has_aifanyi_provider'] ) || function_exists( 'xb_aifanyi_get_frontend_hreflang_map' );
        $has_hreflang_provider = $has_aifanyi || function_exists( 'pll_the_languages' ) || function_exists( 'icl_get_languages' );

        if ( $warning_count > 0 ) {
            $status = 'risk';
            $description = sprintf( __( 'SEO 基础检查有 %d 项建议复核。', 'developer-starter' ), $warning_count );
        } elseif ( 'multilingual_content' === $mode && ! empty( $seo_summary['has_aifanyi_provider'] ) ) {
            $status = 'normal';
            $description = __( '启灵AI多语言 provider 已接入，主题可读取多语言 SEO 诊断。', 'developer-starter' );
        } elseif ( 'multilingual_content' === $mode && $has_hreflang_provider ) {
            $status = 'normal';
            $description = __( '多语言模式和 hreflang 来源已就绪。', 'developer-starter' );
        } elseif ( 'multilingual_content' === $mode || $has_hreflang_provider ) {
            $status = 'partial';
            $description = __( '已检测到部分多语言 SEO 基础能力，建议继续核对诊断项。', 'developer-starter' );
        } else {
            $status = 'not_configured';
            $description = __( '尚未启用多语言内容模式或 hreflang provider。', 'developer-starter' );
        }

        return array(
            'title'       => __( '多语言 SEO', 'developer-starter' ),
            'status'      => $status,
            'description' => $description,
            'meta'        => ! empty( $seo_summary['has_aifanyi_provider'] )
                ? sprintf( __( 'Provider：%s', 'developer-starter' ), __( '启灵AI多语言', 'developer-starter' ) )
                : sprintf( __( '语言模式：%s', 'developer-starter' ), $mode_label ),
            'href'        => '#setting-row-international_seo_diagnostics',
            'action'      => __( '查看 SEO 检查', 'developer-starter' ),
        );
    }

    /**
     * @param array<string,mixed> $options Theme options.
     * @return array<string,string>
     */
    private function build_international_center_schema_card( $options ) {
        $schema_enabled = '1' === (string) $this->get_international_option_value( $options, 'schema_engine_enable', '1' );
        $currency = strtoupper( (string) $this->get_international_option_value( $options, 'schema_default_currency', 'CNY' ) );
        $currency = (string) preg_replace( '/[^A-Z]/', '', $currency );
        $company_name = trim( (string) $this->get_international_option_value( $options, 'company_name', '' ) );
        $site_logo = trim( (string) $this->get_international_option_value( $options, 'site_logo', '' ) );
        $site_logo_svg = trim( (string) $this->get_international_option_value( $options, 'site_logo_svg', '' ) );
        $has_logo = '' !== $site_logo || '' !== $site_logo_svg || ( function_exists( 'has_custom_logo' ) && has_custom_logo() );

        if ( ! $schema_enabled ) {
            $status = 'not_configured';
            $description = __( '行业 Schema 引擎尚未开启。', 'developer-starter' );
        } elseif ( 3 !== strlen( $currency ) ) {
            $status = 'risk';
            $description = __( 'Schema 默认币种不是三位 ISO 货币代码。', 'developer-starter' );
        } elseif ( '' === $company_name || ! $has_logo ) {
            $status = 'partial';
            $description = __( 'Schema 引擎已开启，建议补齐企业名称和 Logo。', 'developer-starter' );
        } else {
            $status = 'normal';
            $description = __( 'Schema 引擎已开启，站点级组织信息基础可用。', 'developer-starter' );
        }

        return array(
            'title'       => __( 'Schema 结构化数据', 'developer-starter' ),
            'status'      => $status,
            'description' => $description,
            'meta'        => sprintf( __( '默认币种：%s', 'developer-starter' ), '' !== $currency ? $currency : __( '未设置', 'developer-starter' ) ),
            'href'        => admin_url( 'admin.php?page=developer-starter-settings&tab=advanced#setting-row-schema_engine_enable' ),
            'action'      => __( '打开 Schema 设置', 'developer-starter' ),
        );
    }

    /**
     * Count enabled AI model connections that look usable.
     *
     * @param array<string,mixed> $options Theme options.
     * @return int
     */
    private function count_international_center_enabled_ai_connections( $options ) {
        $connections = isset( $options['ai_connections'] ) && is_array( $options['ai_connections'] )
            ? $options['ai_connections']
            : array();

        $count = 0;
        foreach ( $connections as $connection ) {
            if ( ! is_array( $connection ) ) {
                continue;
            }

            $enabled = ! empty( $connection['enabled'] ) && '1' === (string) $connection['enabled'];
            $endpoint = isset( $connection['endpoint'] ) ? trim( (string) $connection['endpoint'] ) : '';
            $model = isset( $connection['default_model'] ) ? trim( (string) $connection['default_model'] ) : '';
            $has_key = ! empty( $connection['api_key'] );

            if ( $enabled && '' !== $endpoint && '' !== $model && $has_key ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param string $status Status key.
     * @return string
     */
    private function get_international_center_status_label( $status ) {
        $labels = array(
            'not_configured' => __( '未配置', 'developer-starter' ),
            'partial'        => __( '部分配置', 'developer-starter' ),
            'normal'         => __( '正常', 'developer-starter' ),
            'risk'           => __( '存在风险', 'developer-starter' ),
        );

        return isset( $labels[ $status ] ) ? $labels[ $status ] : $labels['not_configured'];
    }

    /**
     * Render CSS for international center cards once.
     *
     * @return void
     */
    private function render_international_center_overview_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-i18n-center-row > th {
                padding-top: 4px;
            }
            .ds-i18n-center {
                max-width: 1180px;
                display: grid;
                gap: 14px;
            }
            .ds-i18n-center__head {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 16px;
                padding: 16px;
                border: 1px solid #d0d7de;
                border-radius: 8px;
                background: #fff;
            }
            .ds-i18n-center__head strong {
                display: block;
                color: #0f172a;
                font-size: 16px;
                line-height: 1.35;
            }
            .ds-i18n-center__head p {
                margin: 6px 0 0;
                max-width: 760px;
                color: #475569;
                line-height: 1.6;
            }
            .ds-i18n-center__head > span {
                flex: 0 0 auto;
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                padding: 0 10px;
                border: 1px solid #bfdbfe;
                border-radius: 999px;
                background: #eff6ff;
                color: #1d4ed8;
                font-weight: 600;
                font-size: 12px;
            }
            .ds-i18n-center__grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 12px;
            }
            .ds-i18n-center-card {
                display: flex;
                min-height: 188px;
                flex-direction: column;
                gap: 10px;
                padding: 14px;
                border: 1px solid #d0d7de;
                border-top: 4px solid #64748b;
                border-radius: 8px;
                background: #fff;
                box-sizing: border-box;
            }
            .ds-i18n-center-card.is-normal {
                border-top-color: #16a34a;
            }
            .ds-i18n-center-card.is-partial {
                border-top-color: #d97706;
            }
            .ds-i18n-center-card.is-risk {
                border-top-color: #dc2626;
            }
            .ds-i18n-center-card.is-not-configured {
                border-top-color: #64748b;
            }
            .ds-i18n-center-card__top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 10px;
            }
            .ds-i18n-center-card h3 {
                margin: 0;
                color: #0f172a;
                font-size: 14px;
                line-height: 1.4;
            }
            .ds-i18n-center-card__status {
                flex: 0 0 auto;
                padding: 3px 8px;
                border-radius: 999px;
                background: #f1f5f9;
                color: #334155;
                font-size: 12px;
                font-weight: 600;
                line-height: 1.5;
            }
            .ds-i18n-center-card.is-normal .ds-i18n-center-card__status {
                background: #dcfce7;
                color: #166534;
            }
            .ds-i18n-center-card.is-partial .ds-i18n-center-card__status {
                background: #fef3c7;
                color: #92400e;
            }
            .ds-i18n-center-card.is-risk .ds-i18n-center-card__status {
                background: #fee2e2;
                color: #991b1b;
            }
            .ds-i18n-center-card p {
                margin: 0;
                color: #475569;
                line-height: 1.55;
            }
            .ds-i18n-center-card small {
                display: block;
                color: #64748b;
                line-height: 1.5;
            }
            .ds-i18n-center-card__link {
                align-self: flex-start;
                margin-top: auto;
            }
            @media (max-width: 1280px) {
                .ds-i18n-center__grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (max-width: 782px) {
                .ds-i18n-center__head {
                    flex-direction: column;
                }
                .ds-i18n-center__grid {
                    grid-template-columns: 1fr;
                }
                .ds-i18n-center-card {
                    min-height: 0;
                }
            }
        </style>
        <?php
    }
}
