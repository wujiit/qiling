<?php
/**
 * Admin settings international diagnostics field render trait.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_International_Diagnostics_Trait {

    private function render_international_delivery_snapshot_field( $options ) {
        $this->render_international_delivery_snapshot_assets_once();

        $snapshot = $this->build_international_delivery_snapshot( is_array( $options ) ? $options : array() );
        $textarea_id = 'ds-international-delivery-snapshot';

        echo '<tr id="setting-row-international_delivery_snapshot" data-setting-id="international_delivery_snapshot"><th scope="row">' . esc_html__( '配置快照', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-i18n-snapshot-panel">';
        echo '<div class="ds-i18n-snapshot-panel__head">';
        echo '<div>';
        echo '<strong>' . esc_html__( '国际化交付摘要', 'developer-starter' ) . '</strong>';
        echo '<p>' . esc_html__( '摘要只包含配置状态和复核建议，不包含第三方代码正文、密钥或验证码配置。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '<button type="button" class="button button-secondary ds-i18n-snapshot-copy" data-ds-copy-target="' . esc_attr( $textarea_id ) . '">' . esc_html__( '复制摘要', 'developer-starter' ) . '</button>';
        echo '</div>';
        echo '<textarea id="' . esc_attr( $textarea_id ) . '" class="large-text code ds-i18n-snapshot-textarea" rows="18" readonly="readonly">' . esc_textarea( $snapshot ) . '</textarea>';
        echo '<p class="description">' . esc_html__( '此处提供后台只读快照，便于核对配置和排查问题；不会向前台输出任何新内容。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * Build a redacted international delivery snapshot.
     *
     * @param array<string,mixed> $options Theme options.
     * @return string
     */
    private function build_international_delivery_snapshot( $options ) {
        $readiness = $this->build_international_launch_readiness( $options );
        $readiness_summary = isset( $readiness['summary'] ) && is_array( $readiness['summary'] ) ? $readiness['summary'] : array();
        $readiness_items = isset( $readiness['items'] ) && is_array( $readiness['items'] ) ? $readiness['items'] : array();

        $seo = $this->build_international_seo_diagnostics( $options );
        $seo_summary = isset( $seo['summary'] ) && is_array( $seo['summary'] ) ? $seo['summary'] : array();
        $seo_items = isset( $seo['items'] ) && is_array( $seo['items'] ) ? $seo['items'] : array();
        $languages = isset( $seo['languages'] ) && is_array( $seo['languages'] ) ? $seo['languages'] : array();

        $third_party_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_third_party_code_enable', '' );
        $cookie_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_cookie_notice_enable', '' );
        $typography_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_typography_enable', '' );
        $typography_mode = (string) $this->get_international_option_value( $options, 'international_typography_mode', 'auto' );
        $policy_url = trim( (string) $this->get_international_option_value( $options, 'international_cookie_policy_url', '' ) );
        $cookie_region = $this->get_international_cookie_region_label( (string) $this->get_international_option_value( $options, 'international_cookie_region_preset', 'cross_border' ) );
        $cookie_version = trim( (string) $this->get_international_option_value( $options, 'international_cookie_consent_version', '2.0' ) );
        if ( '' === $cookie_version ) {
            $cookie_version = '2.0';
        }

        $lines = array();
        $lines[] = '启灵主题国际化配置快照';
        $lines[] = '生成时间：' . $this->get_international_snapshot_time();
        $lines[] = '站点名称：' . $this->get_international_snapshot_site_name();
        $lines[] = '站点地址：' . $this->get_international_snapshot_home_url();
        $lines[] = '说明：此摘要已脱敏，不包含第三方代码正文、密钥、验证码或旧功能配置。';
        $lines[] = '';

        $lines[] = '一、整体状态';
        $lines[] = '- 新增第三方代码总开关：' . ( $third_party_enabled ? '开启' : '关闭' );
        $lines[] = '- 新增第三方代码输出：' . $this->get_snapshot_summary_value( $readiness_summary, 'code_output', '未输出' );
        $lines[] = '- Cookie 控制：' . ( $cookie_enabled ? '开启' : '关闭' );
        $lines[] = '- Cookie 地区预设：' . $cookie_region;
        $lines[] = '- Cookie 同意版本号：' . $cookie_version;
        $lines[] = '- 隐私政策链接：' . ( '' !== $policy_url ? '已填写' : '未填写' );
        $lines[] = '- 国际排版增强：' . ( $typography_enabled ? '开启' : '关闭' ) . '（模式：' . $typography_mode . '）';
        $lines[] = '- 语言模式：' . $this->get_snapshot_summary_value( $readiness_summary, 'mode_label', '关闭' );
        $lines[] = '- 上线检查：' . $this->get_snapshot_counts_line( $readiness_summary, '处理' );
        $lines[] = '- SEO 基础检查：' . $this->get_snapshot_counts_line( $seo_summary, '复核' );
        $lines[] = '';

        $lines[] = '二、第三方代码组（仅状态，不含代码正文）';
        $code_groups = $this->get_international_code_groups_for_readiness();
        $group_labels = $this->get_international_code_group_labels();
        foreach ( $code_groups as $group_id => $group ) {
            if ( ! is_array( $group ) ) {
                continue;
            }

            $enable_key = isset( $group['enable'] ) ? (string) $group['enable'] : '';
            $content_key = isset( $group['content'] ) ? (string) $group['content'] : '';
            $position_key = isset( $group['position'] ) ? (string) $group['position'] : '';
            $default_position = isset( $group['default'] ) && 'head' === (string) $group['default'] ? 'head' : 'footer';

            $group_enabled = '' !== $enable_key && '1' === (string) $this->get_international_option_value( $options, $enable_key, '' );
            $content = '' !== $content_key ? trim( (string) $this->get_international_option_value( $options, $content_key, '' ) ) : '';
            $has_content = '' !== $content;
            $position = '' !== $position_key ? (string) $this->get_international_option_value( $options, $position_key, $default_position ) : $default_position;
            $position = 'head' === $position ? 'wp_head' : 'wp_footer';
            $category = $this->get_international_code_group_category( $options, (string) $group_id, $group );
            $requires_consent = $this->international_cookie_category_requires_consent( $category );
            $will_output = $third_party_enabled && $group_enabled && $has_content;
            $label = isset( $group_labels[ $group_id ] ) ? $group_labels[ $group_id ] : $group_id;

            $lines[] = sprintf(
                '- %1$s：%2$s；内容：%3$s；位置：%4$s；分类：%5$s；Cookie 同意：%6$s；当前结果：%7$s',
                $label,
                $group_enabled ? '启用' : '关闭',
                $has_content ? '已填写（已隐藏）' : '未填写',
                $position,
                $this->get_international_cookie_category_label( $category ),
                $requires_consent ? '需要' : '不需要',
                $will_output ? '会输出' : '不会输出'
            );
        }
        $lines[] = '';

        $lines[] = '三、语言与 SEO';
        if ( empty( $languages ) ) {
            $lines[] = '- 语言列表：未读取到语言配置';
        } else {
            foreach ( $languages as $language ) {
                if ( ! is_array( $language ) ) {
                    continue;
                }
                $name = isset( $language['name'] ) ? (string) $language['name'] : '';
                $code = isset( $language['code'] ) ? (string) $language['code'] : '';
                $locale = isset( $language['locale'] ) ? (string) $language['locale'] : '';
                $has_mo = ! empty( $language['has_mo'] );
                $requires_mo = ! empty( $language['requires_mo'] );
                $lines[] = sprintf(
                    '- 语言：%1$s（%2$s / %3$s），语言包：%4$s',
                    $name,
                    $code,
                    $locale,
                    $has_mo ? '已存在' : ( $requires_mo ? '缺少 .mo' : '未启用，仅参考' )
                );
            }
        }

        foreach ( $seo_items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $title = isset( $item['title'] ) ? (string) $item['title'] : '';
            $status = isset( $item['status'] ) ? (string) $item['status'] : 'info';
            $badge = isset( $item['badge'] ) ? (string) $item['badge'] : '';
            if ( '' === $title ) {
                continue;
            }
            $lines[] = sprintf( '- SEO：%1$s，状态：%2$s，标记：%3$s', $title, $this->get_international_snapshot_status_label( $status ), $badge );
        }
        $lines[] = '';

        $lines[] = '四、上线处理建议';
        foreach ( $readiness_items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $title = isset( $item['title'] ) ? (string) $item['title'] : '';
            $status = isset( $item['status'] ) ? (string) $item['status'] : 'info';
            $detail = isset( $item['detail'] ) ? (string) $item['detail'] : '';
            if ( '' === $title ) {
                continue;
            }
            $lines[] = sprintf( '- [%1$s] %2$s：%3$s', $this->get_international_snapshot_status_label( $status ), $title, $detail );
        }
        $lines[] = '';
        $lines[] = '五、边界';
        $lines[] = '- 此功能只生成后台只读摘要，不新增前台输出。';
        $lines[] = '- 现有中文站、百度统计、阿里云验证码、备案、旧隐私提示、语言切换和既有 SEO 输出不受影响。';

        return implode( PHP_EOL, $lines );
    }

    /**
     * Get a scalar summary value for snapshots.
     *
     * @param array<string,mixed> $summary Summary array.
     * @param string              $key Summary key.
     * @param string              $default Default value.
     * @return string
     */
    private function get_snapshot_summary_value( $summary, $key, $default ) {
        return isset( $summary[ $key ] ) ? (string) $summary[ $key ] : $default;
    }

    /**
     * Get a compact status count line.
     *
     * @param array<string,mixed> $summary Summary array.
     * @param string              $warning_label Warning label.
     * @return string
     */
    private function get_snapshot_counts_line( $summary, $warning_label ) {
        $ok = isset( $summary['ok_count'] ) ? absint( $summary['ok_count'] ) : 0;
        $info = isset( $summary['info_count'] ) ? absint( $summary['info_count'] ) : 0;
        $warning = isset( $summary['warning_count'] ) ? absint( $summary['warning_count'] ) : 0;

        return sprintf( '%1$d 正常 / %2$d 提示 / %3$d %4$s', $ok, $info, $warning, $warning_label );
    }

    /**
     * Get a readable status label for snapshots.
     *
     * @param string $status Status key.
     * @return string
     */
    private function get_international_snapshot_status_label( $status ) {
        if ( 'pass' === $status ) {
            return '正常';
        }
        if ( 'warning' === $status ) {
            return '需处理';
        }

        return '提示';
    }

    /**
     * Get snapshot generation time.
     *
     * @return string
     */
    private function get_international_snapshot_time() {
        return function_exists( 'current_time' ) ? (string) current_time( 'mysql' ) : gmdate( 'Y-m-d H:i:s' );
    }

    /**
     * Get site name for snapshot.
     *
     * @return string
     */
    private function get_international_snapshot_site_name() {
        return function_exists( 'get_bloginfo' ) ? (string) get_bloginfo( 'name' ) : '';
    }

    /**
     * Get home URL for snapshot.
     *
     * @return string
     */
    private function get_international_snapshot_home_url() {
        return function_exists( 'home_url' ) ? (string) home_url( '/' ) : '';
    }

    /**
     * Render snapshot CSS and copy helper once.
     *
     * @return void
     */
    private function render_international_delivery_snapshot_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-i18n-snapshot-panel {
                border: 1px solid #d0d7de;
                border-radius: 10px;
                background: #fff;
                padding: 16px;
            }
            .ds-i18n-snapshot-panel__head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 12px;
            }
            .ds-i18n-snapshot-panel__head strong {
                display: block;
                color: #0f172a;
                font-size: 15px;
            }
            .ds-i18n-snapshot-panel__head p {
                margin: 6px 0 0;
                color: #64748b;
            }
            .ds-i18n-snapshot-textarea {
                min-height: 320px;
                resize: vertical;
                white-space: pre;
            }
            @media (max-width: 960px) {
                .ds-i18n-snapshot-panel__head {
                    flex-direction: column;
                }
            }
        </style>
        <script>
        jQuery(function($){
            $(document).off('click.dsI18nSnapshot').on('click.dsI18nSnapshot', '[data-ds-copy-target]', function(){
                var $button = $(this);
                var targetId = $button.attr('data-ds-copy-target');
                var target = targetId ? document.getElementById(targetId) : null;
                if (!target) {
                    return;
                }
                target.focus();
                target.select();
                var done = function(){
                    var original = $button.text();
                    $button.text('<?php echo esc_js( __( '已复制', 'developer-starter' ) ); ?>');
                    window.setTimeout(function(){
                        $button.text(original);
                    }, 1600);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(target.value).then(done).catch(function(){
                        document.execCommand('copy');
                        done();
                    });
                    return;
                }
                document.execCommand('copy');
                done();
            });
        });
        </script>
        <?php
    }

    /**
     * Render read-only international launch readiness checklist.
     *
     * @param array<string,mixed> $options Theme options.
     * @return void
     */
    private function render_international_launch_readiness_field( $options ) {
        $this->render_international_launch_readiness_assets_once();

        $readiness = $this->build_international_launch_readiness( is_array( $options ) ? $options : array() );
        $summary = isset( $readiness['summary'] ) && is_array( $readiness['summary'] ) ? $readiness['summary'] : array();
        $items = isset( $readiness['items'] ) && is_array( $readiness['items'] ) ? $readiness['items'] : array();
        $groups = isset( $readiness['groups'] ) && is_array( $readiness['groups'] ) ? $readiness['groups'] : array();
        $blocked_groups = isset( $readiness['blocked_groups'] ) && is_array( $readiness['blocked_groups'] ) ? $readiness['blocked_groups'] : array();
        $unclassified_groups = isset( $readiness['unclassified_groups'] ) && is_array( $readiness['unclassified_groups'] ) ? $readiness['unclassified_groups'] : array();
        $high_risk_groups = isset( $readiness['high_risk_groups'] ) && is_array( $readiness['high_risk_groups'] ) ? $readiness['high_risk_groups'] : array();

        $warning_count = isset( $summary['warning_count'] ) ? absint( $summary['warning_count'] ) : 0;
        $ok_count = isset( $summary['ok_count'] ) ? absint( $summary['ok_count'] ) : 0;
        $info_count = isset( $summary['info_count'] ) ? absint( $summary['info_count'] ) : 0;

        echo '<tr id="setting-row-international_launch_readiness" data-setting-id="international_launch_readiness"><th scope="row">' . esc_html__( '上线检查清单', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-i18n-launch-panel">';
        echo '<div class="ds-i18n-launch-panel__head">';
        echo '<div>';
        echo '<strong>' . esc_html__( '国际化基础上线检查', 'developer-starter' ) . '</strong>';
        echo '<p>' . esc_html__( '这个清单只读取当前配置，不会改动前台、SEO、验证码、备案或既有中文区功能。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '<span class="ds-i18n-launch-score ' . esc_attr( $warning_count > 0 ? 'is-warning' : 'is-success' ) . '">';
        echo esc_html( $warning_count > 0 ? sprintf( __( '%d 项需要处理', 'developer-starter' ), $warning_count ) : __( '基础可上线', 'developer-starter' ) );
        echo '</span>';
        echo '</div>';

        echo '<div class="ds-i18n-launch-summary">';
        $summary_cards = array(
            array( 'label' => __( '新代码输出', 'developer-starter' ), 'value' => isset( $summary['code_output'] ) ? (string) $summary['code_output'] : __( '未输出', 'developer-starter' ) ),
            array( 'label' => __( 'Cookie 控制', 'developer-starter' ), 'value' => isset( $summary['cookie_state'] ) ? (string) $summary['cookie_state'] : __( '关闭', 'developer-starter' ) ),
            array( 'label' => __( '语言模式', 'developer-starter' ), 'value' => isset( $summary['mode_label'] ) ? (string) $summary['mode_label'] : __( '关闭', 'developer-starter' ) ),
            array( 'label' => __( '检查项', 'developer-starter' ), 'value' => sprintf( __( '%1$d 正常 / %2$d 提示 / %3$d 处理', 'developer-starter' ), $ok_count, $info_count, $warning_count ) ),
        );
        foreach ( $summary_cards as $card ) {
            echo '<div class="ds-i18n-launch-summary__card">';
            echo '<span>' . esc_html( $card['label'] ) . '</span>';
            echo '<strong>' . esc_html( $card['value'] ) . '</strong>';
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="ds-i18n-launch-list">';
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $status = isset( $item['status'] ) ? sanitize_key( (string) $item['status'] ) : 'info';
            if ( ! in_array( $status, array( 'pass', 'warning', 'info' ), true ) ) {
                $status = 'info';
            }
            $title = isset( $item['title'] ) ? (string) $item['title'] : '';
            $detail = isset( $item['detail'] ) ? (string) $item['detail'] : '';
            $badge = isset( $item['badge'] ) ? (string) $item['badge'] : '';

            echo '<article class="ds-i18n-launch-item is-' . esc_attr( $status ) . '">';
            echo '<div class="ds-i18n-launch-item__main">';
            echo '<strong>' . esc_html( $title ) . '</strong>';
            if ( '' !== $detail ) {
                echo '<p>' . esc_html( $detail ) . '</p>';
            }
            echo '</div>';
            echo '<span class="ds-i18n-launch-badge is-' . esc_attr( $status ) . '">' . esc_html( $badge ) . '</span>';
            echo '</article>';
        }
        echo '</div>';

        if ( ! empty( $groups ) ) {
            echo '<div class="ds-i18n-launch-groups">';
            echo '<strong>' . esc_html__( '第三方代码组状态', 'developer-starter' ) . '</strong>';
            echo '<div class="ds-i18n-launch-group-grid">';
            foreach ( $groups as $group ) {
                if ( ! is_array( $group ) ) {
                    continue;
                }
                $status = isset( $group['status'] ) ? sanitize_key( (string) $group['status'] ) : 'info';
                if ( ! in_array( $status, array( 'pass', 'warning', 'info' ), true ) ) {
                    $status = 'info';
                }
                $label = isset( $group['label'] ) ? (string) $group['label'] : '';
                $position = isset( $group['position'] ) ? (string) $group['position'] : 'footer';
                $state = isset( $group['state'] ) ? (string) $group['state'] : '';
                $consent = ! empty( $group['requires_consent'] ) ? __( '需要同意', 'developer-starter' ) : __( '不需要同意', 'developer-starter' );
                $category_label = isset( $group['category_label'] ) ? (string) $group['category_label'] : '';

                echo '<div class="ds-i18n-launch-group is-' . esc_attr( $status ) . '">';
                echo '<span>' . esc_html( $label ) . '</span>';
                echo '<strong>' . esc_html( $state ) . '</strong>';
                echo '<em>' . esc_html( sprintf( __( '%1$s / %2$s / %3$s', 'developer-starter' ), 'head' === $position ? 'wp_head' : 'wp_footer', '' !== $category_label ? $category_label : __( '未分类', 'developer-starter' ), $consent ) ) . '</em>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }

        echo '<div class="ds-i18n-cookie-diagnostics">';
        $this->render_international_cookie_diagnostic_list(
            __( '会被拦截的代码', 'developer-starter' ),
            $blocked_groups,
            __( '当前没有会被 Cookie 分类拦截的第三方代码。', 'developer-starter' )
        );
        $this->render_international_cookie_diagnostic_list(
            __( '无分类代码', 'developer-starter' ),
            $unclassified_groups,
            __( '当前没有未显式分类或分类异常的第三方代码。', 'developer-starter' )
        );
        $this->render_international_cookie_diagnostic_list(
            __( '高风险代码', 'developer-starter' ),
            $high_risk_groups,
            __( '当前没有检测到高风险第三方代码特征。', 'developer-starter' )
        );
        echo '</div>';

        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * Render a compact Cookie diagnostics list.
     *
     * @param string              $title Section title.
     * @param array<int,mixed>    $rows Diagnostic rows.
     * @param string              $empty Empty state.
     * @return void
     */
    private function render_international_cookie_diagnostic_list( $title, $rows, $empty ) {
        echo '<section class="ds-i18n-cookie-diagnostic-list">';
        echo '<strong>' . esc_html( $title ) . '</strong>';
        if ( empty( $rows ) ) {
            echo '<p>' . esc_html( $empty ) . '</p>';
            echo '</section>';
            return;
        }

        echo '<ul>';
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $label = isset( $row['label'] ) ? (string) $row['label'] : '';
            $detail = isset( $row['detail'] ) ? (string) $row['detail'] : '';
            if ( '' === $label && '' === $detail ) {
                continue;
            }
            echo '<li><b>' . esc_html( $label ) . '</b>';
            if ( '' !== $detail ) {
                echo '<span>' . esc_html( $detail ) . '</span>';
            }
            echo '</li>';
        }
        echo '</ul>';
        echo '</section>';
    }

    /**
     * Build read-only launch readiness data from international settings.
     *
     * @param array<string,mixed> $options Theme options.
     * @return array<string,mixed>
     */
    private function build_international_launch_readiness( $options ) {
        $third_party_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_third_party_code_enable', '' );
        $cookie_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_cookie_notice_enable', '' );
        $typography_enabled = '1' === (string) $this->get_international_option_value( $options, 'international_typography_enable', '' );
        $policy_url = trim( (string) $this->get_international_option_value( $options, 'international_cookie_policy_url', '' ) );
        $cookie_region = (string) $this->get_international_option_value( $options, 'international_cookie_region_preset', 'cross_border' );
        $cookie_version = trim( (string) $this->get_international_option_value( $options, 'international_cookie_consent_version', '2.0' ) );
        if ( '' === $cookie_version ) {
            $cookie_version = '2.0';
        }
        $mode = (string) $this->get_international_option_value( $options, 'frontend_language_switch_mode', '' );
        if ( ! in_array( $mode, array( '', 'translate_js', 'multilingual_content' ), true ) ) {
            $mode = '';
        }

        $mode_labels = array(
            ''                      => __( '关闭', 'developer-starter' ),
            'translate_js'          => __( 'translate.js 机翻模式', 'developer-starter' ),
            'multilingual_content'  => __( '多语言内容模式', 'developer-starter' ),
        );
        $mode_label = isset( $mode_labels[ $mode ] ) ? $mode_labels[ $mode ] : $mode_labels[''];

        $code_groups = $this->get_international_code_groups_for_readiness();
        $group_labels = $this->get_international_code_group_labels();
        $group_rows = array();
        $configured_groups = 0;
        $active_output_groups = 0;
        $consent_required_active = 0;
        $blocked_groups = array();
        $unclassified_groups = array();
        $high_risk_groups = array();

        foreach ( $code_groups as $group_id => $group ) {
            if ( ! is_array( $group ) ) {
                continue;
            }

            $enable_key = isset( $group['enable'] ) ? (string) $group['enable'] : '';
            $content_key = isset( $group['content'] ) ? (string) $group['content'] : '';
            $position_key = isset( $group['position'] ) ? (string) $group['position'] : '';
            $default_position = isset( $group['default'] ) && 'head' === (string) $group['default'] ? 'head' : 'footer';

            $group_enabled = '' !== $enable_key && '1' === (string) $this->get_international_option_value( $options, $enable_key, '' );
            $content = '' !== $content_key ? trim( (string) $this->get_international_option_value( $options, $content_key, '' ) ) : '';
            $has_content = '' !== $content;
            $position = '' !== $position_key ? (string) $this->get_international_option_value( $options, $position_key, $default_position ) : $default_position;
            $position = 'head' === $position ? 'head' : 'footer';
            $category_meta = $this->get_international_code_group_category_meta( $options, (string) $group_id, $group );
            $category = isset( $category_meta['category'] ) ? (string) $category_meta['category'] : 'custom';
            $requires_consent = $this->international_cookie_category_requires_consent( $category );
            $label = isset( $group_labels[ $group_id ] ) ? $group_labels[ $group_id ] : $group_id;

            if ( $has_content ) {
                $configured_groups++;
            }

            $will_output = $third_party_enabled && $group_enabled && $has_content;
            if ( $will_output ) {
                $active_output_groups++;
                if ( $requires_consent ) {
                    $consent_required_active++;
                }
            }

            if ( $will_output && $requires_consent && $cookie_enabled ) {
                $blocked_groups[] = array(
                    'label'  => $label,
                    'detail' => sprintf(
                        __( '%1$s，分类：%2$s，访客授权前会以延迟模板保存，不直接执行。', 'developer-starter' ),
                        'head' === $position ? 'wp_head' : 'wp_footer',
                        $this->get_international_cookie_category_label( $category )
                    ),
                );
            }

            if ( $has_content && ! empty( $category_meta['unclassified'] ) ) {
                $unclassified_groups[] = array(
                    'label'  => $label,
                    'detail' => isset( $category_meta['detail'] ) ? (string) $category_meta['detail'] : __( '未显式选择 Cookie 分类，当前使用默认分类。', 'developer-starter' ),
                );
            }

            $risk = $this->analyze_international_code_group_risk( (string) $group_id, $content, $category, $position );
            if ( $has_content && ! empty( $risk['high_risk'] ) ) {
                $high_risk_groups[] = array(
                    'label'  => $label,
                    'detail' => isset( $risk['detail'] ) ? (string) $risk['detail'] : __( '检测到需要复核的第三方代码特征。', 'developer-starter' ),
                );
            }

            if ( $will_output ) {
                if ( $requires_consent && $cookie_enabled ) {
                    $status = 'pass';
                    $state = __( '未授权前会被拦截', 'developer-starter' );
                } elseif ( $requires_consent && ! $cookie_enabled ) {
                    $status = 'warning';
                    $state = __( '未拦截，需开启 Cookie', 'developer-starter' );
                } else {
                    $status = 'pass';
                    $state = __( '直接输出', 'developer-starter' );
                }
            } elseif ( $has_content ) {
                $status = ! empty( $category_meta['unclassified'] ) ? 'warning' : 'info';
                $state = $third_party_enabled && ! $group_enabled ? __( '已配置，未启用', 'developer-starter' ) : __( '已配置，总开关关闭', 'developer-starter' );
            } elseif ( $group_enabled ) {
                $status = 'info';
                $state = __( '已启用，缺少代码', 'developer-starter' );
            } else {
                $status = 'info';
                $state = __( '未配置', 'developer-starter' );
            }
            if ( $has_content && ! empty( $risk['high_risk'] ) ) {
                $status = 'warning';
            }

            $group_rows[] = array(
                'label'             => $label,
                'position'          => $position,
                'requires_consent'  => $requires_consent,
                'category'          => $category,
                'category_label'    => $this->get_international_cookie_category_label( $category ),
                'status'            => $status,
                'state'             => $state,
            );
        }

        $seo_diagnostics = $this->build_international_seo_diagnostics( $options );
        $seo_summary = isset( $seo_diagnostics['summary'] ) && is_array( $seo_diagnostics['summary'] ) ? $seo_diagnostics['summary'] : array();
        $seo_warning_count = isset( $seo_summary['warning_count'] ) ? absint( $seo_summary['warning_count'] ) : 0;

        $items = array();
        $items[] = array(
            'status' => 'pass',
            'title'  => __( '现有功能隔离', 'developer-starter' ),
            'detail' => __( '国际化检查只读取和汇总相关配置，不会改动百度统计、阿里云验证码、备案、隐私提示、语言切换或现有 SEO 输出。', 'developer-starter' ),
            'badge'  => __( '已隔离', 'developer-starter' ),
        );
        $items[] = array(
            'status' => $cookie_enabled ? 'pass' : 'info',
            'title'  => __( '地区预设与同意版本', 'developer-starter' ),
            'detail' => sprintf(
                __( '当前地区预设为 %1$s，同意版本号为 %2$s。隐私政策更新后递增版本号，旧授权会重新弹出确认。', 'developer-starter' ),
                $this->get_international_cookie_region_label( $cookie_region ),
                $cookie_version
            ),
            'badge'  => $cookie_version,
        );

        if ( ! $third_party_enabled ) {
            $items[] = array(
                'status' => 'pass',
                'title'  => __( '第三方代码输出', 'developer-starter' ),
                'detail' => $configured_groups > 0
                    ? __( '已有代码内容保存，但总开关关闭，前台不会输出新增国际化代码。', 'developer-starter' )
                    : __( '总开关关闭且未配置代码组，前台不会输出新增国际化代码。', 'developer-starter' ),
                'badge'  => __( '未输出', 'developer-starter' ),
            );
        } elseif ( $active_output_groups > 0 ) {
            $items[] = array(
                'status' => 'pass',
                'title'  => __( '第三方代码输出', 'developer-starter' ),
                'detail' => sprintf( __( '总开关已开启，当前有 %d 个代码组会按配置输出。', 'developer-starter' ), $active_output_groups ),
                'badge'  => sprintf( __( '%d 组', 'developer-starter' ), $active_output_groups ),
            );
        } else {
            $items[] = array(
                'status' => 'info',
                'title'  => __( '第三方代码输出', 'developer-starter' ),
                'detail' => __( '总开关已开启，但没有同时满足“启用 + 有代码内容”的代码组。', 'developer-starter' ),
                'badge'  => __( '待配置', 'developer-starter' ),
            );
        }

        if ( $consent_required_active > 0 && ! $cookie_enabled ) {
            $items[] = array(
                'status' => 'warning',
                'title'  => __( 'Cookie 同意保护', 'developer-starter' ),
                'detail' => __( '已有会输出的代码组归入非必要 Cookie 分类，但 Cookie 提示关闭；当前不会拦截这些代码，建议开启 Cookie 提示或改为必要分类。', 'developer-starter' ),
                'badge'  => __( '需处理', 'developer-starter' ),
            );
        } elseif ( $consent_required_active > 0 ) {
            $items[] = array(
                'status' => 'pass',
                'title'  => __( 'Cookie 同意保护', 'developer-starter' ),
                'detail' => sprintf( __( 'Cookie 提示已开启，%d 个非必要分类代码组会在访客同意后再执行。', 'developer-starter' ), $consent_required_active ),
                'badge'  => __( '已保护', 'developer-starter' ),
            );
        } else {
            $items[] = array(
                'status' => 'info',
                'title'  => __( 'Cookie 同意保护', 'developer-starter' ),
                'detail' => __( '当前没有会输出且属于非必要 Cookie 分类的代码组；中文默认站点可保持关闭。', 'developer-starter' ),
                'badge'  => __( '参考', 'developer-starter' ),
            );
        }

        $items[] = array(
            'status' => $cookie_enabled && '' !== $policy_url ? 'pass' : 'info',
            'title'  => __( '隐私政策链接', 'developer-starter' ),
            'detail' => $cookie_enabled
                ? ( '' !== $policy_url ? __( 'Cookie 提示已配置隐私政策链接。', 'developer-starter' ) : __( 'Cookie 提示已开启但未配置隐私政策链接；基础功能可用，上线前可补充。', 'developer-starter' ) )
                : __( 'Cookie 提示关闭时不会显示新增隐私链接。', 'developer-starter' ),
            'badge'  => $cookie_enabled && '' !== $policy_url ? __( '已配置', 'developer-starter' ) : __( '可选', 'developer-starter' ),
        );

        $items[] = array(
            'status' => $typography_enabled ? 'pass' : 'info',
            'title'  => __( '国际排版增强', 'developer-starter' ),
            'detail' => $typography_enabled
                ? __( '国际排版增强已开启，会按配置加载轻量 CSS 和 body class。', 'developer-starter' )
                : __( '国际排版增强关闭，不改变现有中文样式。', 'developer-starter' ),
            'badge'  => $typography_enabled ? __( '已开启', 'developer-starter' ) : __( '关闭', 'developer-starter' ),
        );

        $items[] = array(
            'status' => 'multilingual_content' === $mode && $seo_warning_count > 0 ? 'warning' : ( $seo_warning_count > 0 ? 'info' : 'pass' ),
            'title'  => __( '多语言 SEO 复核', 'developer-starter' ),
            'detail' => $seo_warning_count > 0
                ? sprintf( __( 'SEO 基础检查中有 %d 项复核提示；未启用多语言内容模式时只作为参考。', 'developer-starter' ), $seo_warning_count )
                : __( 'SEO 基础检查暂无需要处理的复核项。', 'developer-starter' ),
            'badge'  => $seo_warning_count > 0 ? __( '有提示', 'developer-starter' ) : __( '正常', 'developer-starter' ),
        );

        $items[] = array(
            'status' => 'multilingual_content' === $mode ? 'pass' : 'info',
            'title'  => __( '语言模式', 'developer-starter' ),
            'detail' => 'multilingual_content' === $mode
                ? __( '当前使用多语言内容模式，适合真实海外多语言页面。', 'developer-starter' )
                : __( '当前不是多语言内容模式；只做中文站或基础海外投放时可以保持现状。', 'developer-starter' ),
            'badge'  => $mode_label,
        );

        $summary = array(
            'code_output'   => $active_output_groups > 0 ? sprintf( __( '%d 组会输出', 'developer-starter' ), $active_output_groups ) : __( '未输出', 'developer-starter' ),
            'cookie_state'  => $cookie_enabled ? __( '已开启', 'developer-starter' ) : __( '关闭', 'developer-starter' ),
            'mode_label'    => $mode_label,
            'warning_count' => 0,
            'ok_count'      => 0,
            'info_count'    => 0,
        );

        foreach ( $items as $item ) {
            $status = isset( $item['status'] ) ? (string) $item['status'] : 'info';
            if ( 'warning' === $status ) {
                $summary['warning_count']++;
            } elseif ( 'pass' === $status ) {
                $summary['ok_count']++;
            } else {
                $summary['info_count']++;
            }
        }

        return array(
            'summary'             => $summary,
            'items'               => $items,
            'groups'              => $group_rows,
            'blocked_groups'      => $blocked_groups,
            'unclassified_groups' => $unclassified_groups,
            'high_risk_groups'    => $high_risk_groups,
        );
    }

    /**
     * Get a theme option from the passed admin options array.
     *
     * @param array<string,mixed> $options Theme options.
     * @param string              $key Option key.
     * @param mixed               $default Default value.
     * @return mixed
     */
    private function get_international_option_value( $options, $key, $default = '' ) {
        return is_array( $options ) && array_key_exists( $key, $options ) ? $options[ $key ] : $default;
    }

    /**
     * Get code groups without making the admin renderer depend on frontend boot order.
     *
     * @return array<string,array<string,string>>
     */
    private function get_international_code_groups_for_readiness() {
        if ( class_exists( '\Developer_Starter\International\Third_Party_Code_Manager' ) ) {
            return \Developer_Starter\International\Third_Party_Code_Manager::get_code_groups();
        }

        return array(
            'head'      => array( 'enable' => 'international_code_head_enable', 'content' => 'international_code_head_content', 'position' => 'international_code_head_position', 'consent' => 'international_code_head_require_consent', 'category' => 'international_code_head_category', 'default' => 'head', 'default_category' => 'necessary' ),
            'footer'    => array( 'enable' => 'international_code_footer_enable', 'content' => 'international_code_footer_content', 'position' => 'international_code_footer_position', 'consent' => 'international_code_footer_require_consent', 'category' => 'international_code_footer_category', 'default' => 'footer', 'default_category' => 'custom' ),
            'analytics' => array( 'enable' => 'international_code_analytics_enable', 'content' => 'international_code_analytics_content', 'position' => 'international_code_analytics_position', 'consent' => 'international_code_analytics_require_consent', 'category' => 'international_code_analytics_category', 'default' => 'head', 'default_category' => 'statistics' ),
            'ads'       => array( 'enable' => 'international_code_ads_enable', 'content' => 'international_code_ads_content', 'position' => 'international_code_ads_position', 'consent' => 'international_code_ads_require_consent', 'category' => 'international_code_ads_category', 'default' => 'footer', 'default_category' => 'advertising' ),
            'custom'    => array( 'enable' => 'international_code_custom_enable', 'content' => 'international_code_custom_content', 'position' => 'international_code_custom_position', 'consent' => 'international_code_custom_require_consent', 'category' => 'international_code_custom_category', 'default' => 'footer', 'default_category' => 'custom' ),
        );
    }

    /**
     * Get admin labels for third-party code groups.
     *
     * @return array<string,string>
     */
    private function get_international_code_group_labels() {
        return array(
            'head'      => __( '头部代码', 'developer-starter' ),
            'footer'    => __( '底部代码', 'developer-starter' ),
            'analytics' => __( '统计代码', 'developer-starter' ),
            'ads'       => __( '广告转化代码', 'developer-starter' ),
            'custom'    => __( '自定义代码', 'developer-starter' ),
        );
    }

    /**
     * Resolve category plus explicit/default state for admin diagnostics.
     *
     * @param array<string,mixed> $options Theme options.
     * @param string              $group_id Group id.
     * @param array<string,mixed> $group Group config.
     * @return array<string,mixed>
     */
    private function get_international_code_group_category_meta( $options, $group_id, $group ) {
        $default = isset( $group['default_category'] ) ? (string) $group['default_category'] : 'custom';
        $category_key = isset( $group['category'] ) ? (string) $group['category'] : '';
        $raw = '' !== $category_key ? trim( (string) $this->get_international_option_value( $options, $category_key, '' ) ) : '';
        $allowed = array( 'necessary', 'statistics', 'marketing', 'advertising', 'custom' );
        $source = 'explicit';
        $detail = '';

        if ( '' === $raw && ! empty( $group['consent'] ) && '1' === (string) $this->get_international_option_value( $options, (string) $group['consent'], '' ) ) {
            $legacy_map = array(
                'analytics' => 'statistics',
                'ads'       => 'advertising',
                'custom'    => 'custom',
                'footer'    => 'custom',
                'head'      => 'custom',
            );
            $raw = isset( $legacy_map[ $group_id ] ) ? $legacy_map[ $group_id ] : $default;
            $source = 'legacy';
            $detail = __( '此代码尚未明确 Cookie 分类，建议重新确认。', 'developer-starter' );
        } elseif ( '' === $raw ) {
            $raw = $default;
            $source = 'default';
            $detail = sprintf(
                __( '未显式选择 Cookie 分类，当前使用默认分类：%s。', 'developer-starter' ),
                $this->get_international_cookie_category_label( $raw )
            );
        } elseif ( ! in_array( sanitize_key( $raw ), $allowed, true ) ) {
            $raw = $default;
            $source = 'invalid';
            $detail = sprintf(
                __( '保存的分类值无效，当前回退到默认分类：%s。', 'developer-starter' ),
                $this->get_international_cookie_category_label( $raw )
            );
        }

        $category = $this->normalize_international_cookie_category( $raw );

        return array(
            'category'     => $category,
            'source'       => $source,
            'unclassified' => in_array( $source, array( 'default', 'invalid', 'legacy' ), true ),
            'detail'       => $detail,
        );
    }

    /**
     * Resolve a third-party code group Cookie category for admin diagnostics.
     *
     * @param array<string,mixed> $options Theme options.
     * @param string              $group_id Group id.
     * @param array<string,mixed> $group Group config.
     * @return string
     */
    private function get_international_code_group_category( $options, $group_id, $group ) {
        $meta = $this->get_international_code_group_category_meta( $options, $group_id, $group );

        return isset( $meta['category'] ) ? (string) $meta['category'] : 'custom';
    }

    /**
     * Analyze risky third-party code patterns for admin diagnostics.
     *
     * @param string $group_id Group id.
     * @param string $code Snippet code.
     * @param string $category Cookie category.
     * @param string $position Hook position.
     * @return array<string,mixed>
     */
    private function analyze_international_code_group_risk( $group_id, $code, $category, $position ) {
        unset( $position );

        $code = strtolower( (string) $code );
        $category = $this->normalize_international_cookie_category( $category );
        $reasons = array();
        $tracking_patterns = array(
            'googletagmanager',
            'gtag(',
            'google-analytics',
            'googleadservices',
            'doubleclick',
            'fbevents',
            'facebook.net',
            'connect.facebook.net',
            'tiktok',
            'linkedin.com/insight',
            'snap.licdn.com',
            'hotjar',
            'clarity.ms',
            'pinterest',
        );

        $tracking_detected = false;
        foreach ( $tracking_patterns as $pattern ) {
            if ( false !== strpos( $code, $pattern ) ) {
                $tracking_detected = true;
                break;
            }
        }

        if ( $tracking_detected && 'necessary' === $category ) {
            $reasons[] = __( '疑似统计、广告或再营销代码被归入必要分类。', 'developer-starter' );
        }
        if ( $tracking_detected && 'custom' === $category ) {
            $reasons[] = __( '疑似统计、广告或再营销代码仍在自定义分类，建议明确归入统计、营销或广告。', 'developer-starter' );
        }
        if ( false !== strpos( $code, 'googletagmanager.com/gtm.js' ) || false !== strpos( $code, 'gtm-' ) ) {
            $reasons[] = __( '检测到 GTM，容器可能继续加载多个供应商标签。', 'developer-starter' );
        }
        if ( false !== strpos( $code, '<iframe' ) || false !== strpos( $code, '<noscript' ) ) {
            $reasons[] = __( '包含 iframe 或 noscript 像素，需确认是否受 Cookie 授权控制。', 'developer-starter' );
        }
        if ( false !== strpos( $code, 'document.write' ) || false !== strpos( $code, 'eval(' ) ) {
            $reasons[] = __( '包含动态执行脚本，建议上线前人工复核。', 'developer-starter' );
        }
        if ( 'head' === $group_id && $tracking_detected ) {
            $reasons[] = __( '头部代码检测到追踪供应商，建议确认是否必须首屏加载。', 'developer-starter' );
        }

        $reasons = array_values( array_unique( $reasons ) );

        return array(
            'high_risk' => ! empty( $reasons ),
            'detail'    => implode( '；', $reasons ),
        );
    }

    /**
     * Normalize a Cookie category key.
     *
     * @param string $category Category key.
     * @return string
     */
    private function normalize_international_cookie_category( $category ) {
        if ( class_exists( '\Developer_Starter\International\Cookie_Consent_Manager' ) && method_exists( '\Developer_Starter\International\Cookie_Consent_Manager', 'normalize_category' ) ) {
            return \Developer_Starter\International\Cookie_Consent_Manager::normalize_category( $category, 'custom' );
        }

        $category = sanitize_key( (string) $category );
        return in_array( $category, array( 'necessary', 'statistics', 'marketing', 'advertising', 'custom' ), true ) ? $category : 'custom';
    }

    /**
     * Whether a Cookie category requires visitor consent.
     *
     * @param string $category Category key.
     * @return bool
     */
    private function international_cookie_category_requires_consent( $category ) {
        return 'necessary' !== $this->normalize_international_cookie_category( $category );
    }

    /**
     * Get an admin label for a Cookie category.
     *
     * @param string $category Category key.
     * @return string
     */
    private function get_international_cookie_category_label( $category ) {
        $labels = array(
            'necessary'   => __( '必要', 'developer-starter' ),
            'statistics'  => __( '统计', 'developer-starter' ),
            'marketing'   => __( '营销', 'developer-starter' ),
            'advertising' => __( '广告', 'developer-starter' ),
            'custom'      => __( '自定义', 'developer-starter' ),
        );
        $category = $this->normalize_international_cookie_category( $category );

        return isset( $labels[ $category ] ) ? $labels[ $category ] : $labels['custom'];
    }

    /**
     * Get an admin label for a regional Cookie preset.
     *
     * @param string $preset Preset key.
     * @return string
     */
    private function get_international_cookie_region_label( $preset ) {
        $labels = array(
            'cn'           => __( '中国', 'developer-starter' ),
            'eu'           => __( '欧盟', 'developer-starter' ),
            'uk'           => __( '英国', 'developer-starter' ),
            'us'           => __( '美国', 'developer-starter' ),
            'cross_border' => __( '跨境站', 'developer-starter' ),
        );
        $preset = sanitize_key( (string) $preset );

        return isset( $labels[ $preset ] ) ? $labels[ $preset ] : $labels['cross_border'];
    }

    /**
     * Render CSS for the launch checklist once.
     *
     * @return void
     */
    private function render_international_launch_readiness_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-i18n-launch-panel {
                border: 1px solid #d0d7de;
                border-radius: 10px;
                background: #fff;
                padding: 16px;
            }
            .ds-i18n-launch-panel__head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                margin-bottom: 14px;
            }
            .ds-i18n-launch-panel__head strong {
                display: block;
                color: #0f172a;
                font-size: 15px;
            }
            .ds-i18n-launch-panel__head p {
                margin: 6px 0 0;
                color: #64748b;
            }
            .ds-i18n-launch-score,
            .ds-i18n-launch-badge {
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                padding: 0 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }
            .ds-i18n-launch-score.is-success,
            .ds-i18n-launch-badge.is-pass {
                background: #dcfce7;
                color: #166534;
            }
            .ds-i18n-launch-score.is-warning,
            .ds-i18n-launch-badge.is-warning {
                background: #fef3c7;
                color: #92400e;
            }
            .ds-i18n-launch-badge.is-info {
                background: #e0f2fe;
                color: #075985;
            }
            .ds-i18n-launch-summary {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 14px;
            }
            .ds-i18n-launch-summary__card,
            .ds-i18n-launch-group {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #f8fafc;
                padding: 10px 12px;
            }
            .ds-i18n-launch-summary__card span,
            .ds-i18n-launch-group span {
                display: block;
                color: #64748b;
                font-size: 12px;
                margin-bottom: 5px;
            }
            .ds-i18n-launch-summary__card strong,
            .ds-i18n-launch-group strong {
                color: #111827;
                font-size: 13px;
            }
            .ds-i18n-launch-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .ds-i18n-launch-item {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                border: 1px solid #e2e8f0;
                border-left-width: 4px;
                border-radius: 8px;
                background: #fff;
                padding: 12px;
            }
            .ds-i18n-launch-item.is-pass {
                border-left-color: #22c55e;
            }
            .ds-i18n-launch-item.is-warning {
                border-left-color: #f59e0b;
            }
            .ds-i18n-launch-item.is-info {
                border-left-color: #38bdf8;
            }
            .ds-i18n-launch-item__main strong {
                display: block;
                color: #0f172a;
                margin-bottom: 5px;
            }
            .ds-i18n-launch-item__main p {
                margin: 0;
                color: #64748b;
                line-height: 1.6;
            }
            .ds-i18n-launch-groups {
                margin-top: 16px;
            }
            .ds-i18n-launch-groups > strong {
                display: block;
                margin-bottom: 10px;
            }
            .ds-i18n-launch-group-grid {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 10px;
            }
            .ds-i18n-launch-group em {
                display: block;
                margin-top: 6px;
                color: #64748b;
                font-style: normal;
                font-size: 12px;
            }
            .ds-i18n-launch-group.is-pass {
                border-color: #bbf7d0;
                background: #f0fdf4;
            }
            .ds-i18n-launch-group.is-warning {
                border-color: #f59e0b;
                background: #fffbeb;
            }
            .ds-i18n-launch-group.is-info {
                border-color: #bae6fd;
                background: #f0f9ff;
            }
            .ds-i18n-cookie-diagnostics {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
                margin-top: 16px;
            }
            .ds-i18n-cookie-diagnostic-list {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #fff;
                padding: 12px;
            }
            .ds-i18n-cookie-diagnostic-list > strong {
                display: block;
                margin-bottom: 8px;
                color: #0f172a;
            }
            .ds-i18n-cookie-diagnostic-list p {
                margin: 0;
                color: #64748b;
                line-height: 1.6;
            }
            .ds-i18n-cookie-diagnostic-list ul {
                margin: 0;
                padding-left: 0;
                list-style: none;
            }
            .ds-i18n-cookie-diagnostic-list li {
                display: grid;
                gap: 4px;
                padding: 8px 0;
                border-top: 1px solid #f1f5f9;
            }
            .ds-i18n-cookie-diagnostic-list li:first-child {
                border-top: 0;
                padding-top: 0;
            }
            .ds-i18n-cookie-diagnostic-list li b {
                color: #111827;
            }
            .ds-i18n-cookie-diagnostic-list li span {
                color: #64748b;
                line-height: 1.55;
            }
            @media (max-width: 960px) {
                .ds-i18n-launch-panel__head,
                .ds-i18n-launch-item {
                    flex-direction: column;
                }
                .ds-i18n-launch-summary,
                .ds-i18n-launch-group-grid,
                .ds-i18n-cookie-diagnostics {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
    }

    /**
     * Render read-only international SEO diagnostics.
     *
     * @param array<string,mixed> $options Theme options.
     * @return void
     */
    private function render_international_seo_diagnostics_field( $options ) {
        $this->render_international_seo_diagnostics_assets_once();

        $diagnostics = $this->build_international_seo_diagnostics( is_array( $options ) ? $options : array() );
        $summary = isset( $diagnostics['summary'] ) && is_array( $diagnostics['summary'] ) ? $diagnostics['summary'] : array();
        $items = isset( $diagnostics['items'] ) && is_array( $diagnostics['items'] ) ? $diagnostics['items'] : array();
        $languages = isset( $diagnostics['languages'] ) && is_array( $diagnostics['languages'] ) ? $diagnostics['languages'] : array();

        $warning_count = isset( $summary['warning_count'] ) ? absint( $summary['warning_count'] ) : 0;
        $ok_count = isset( $summary['ok_count'] ) ? absint( $summary['ok_count'] ) : 0;
        $info_count = isset( $summary['info_count'] ) ? absint( $summary['info_count'] ) : 0;
        $mode_label = isset( $summary['mode_label'] ) ? (string) $summary['mode_label'] : '';
        $default_lang = isset( $summary['default_lang'] ) ? (string) $summary['default_lang'] : '';
        $currency = isset( $summary['currency'] ) ? (string) $summary['currency'] : 'CNY';
        $provider_label = isset( $summary['provider_label'] ) ? (string) $summary['provider_label'] : __( '主题配置', 'developer-starter' );

        echo '<tr id="setting-row-international_seo_diagnostics" data-setting-id="international_seo_diagnostics"><th scope="row">' . esc_html__( 'SEO 检查结果', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-i18n-seo-panel">';
        echo '<div class="ds-i18n-seo-panel__head">';
        echo '<div>';
        echo '<strong>' . esc_html__( '多语言 SEO 基础检查', 'developer-starter' ) . '</strong>';
        echo '<p>' . esc_html__( '这个面板只读取当前配置并给出提示，不会自动修改 URL、canonical、schema、sitemap 或 head 标签。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '<span class="ds-i18n-seo-score ' . esc_attr( $warning_count > 0 ? 'is-warning' : 'is-success' ) . '">';
        echo esc_html( $warning_count > 0 ? sprintf( __( '%d 项建议复核', 'developer-starter' ), $warning_count ) : __( '基础状态正常', 'developer-starter' ) );
        echo '</span>';
        echo '</div>';

        echo '<div class="ds-i18n-seo-summary">';
        $summary_cards = array(
            array( 'label' => __( '语言模式', 'developer-starter' ), 'value' => $mode_label ),
            array( 'label' => __( '默认语言', 'developer-starter' ), 'value' => $default_lang ),
            array( 'label' => __( 'SEO 数据来源', 'developer-starter' ), 'value' => $provider_label ),
            array( 'label' => __( 'Schema 币种', 'developer-starter' ), 'value' => $currency ),
            array( 'label' => __( '检查项', 'developer-starter' ), 'value' => sprintf( __( '%1$d 正常 / %2$d 提示 / %3$d 复核', 'developer-starter' ), $ok_count, $info_count, $warning_count ) ),
        );
        foreach ( $summary_cards as $card ) {
            echo '<div class="ds-i18n-seo-summary__card">';
            echo '<span>' . esc_html( $card['label'] ) . '</span>';
            echo '<strong>' . esc_html( $card['value'] ) . '</strong>';
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="ds-i18n-seo-list">';
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $status = isset( $item['status'] ) ? sanitize_key( (string) $item['status'] ) : 'info';
            if ( ! in_array( $status, array( 'pass', 'warning', 'info' ), true ) ) {
                $status = 'info';
            }
            $title = isset( $item['title'] ) ? (string) $item['title'] : '';
            $detail = isset( $item['detail'] ) ? (string) $item['detail'] : '';
            $badge = isset( $item['badge'] ) ? (string) $item['badge'] : '';

            echo '<article class="ds-i18n-seo-item is-' . esc_attr( $status ) . '">';
            echo '<div class="ds-i18n-seo-item__main">';
            echo '<strong>' . esc_html( $title ) . '</strong>';
            if ( '' !== $detail ) {
                echo '<p>' . esc_html( $detail ) . '</p>';
            }
            echo '</div>';
            echo '<span class="ds-i18n-seo-badge is-' . esc_attr( $status ) . '">' . esc_html( $badge ) . '</span>';
            echo '</article>';
        }
        echo '</div>';

        if ( ! empty( $languages ) ) {
            echo '<div class="ds-i18n-seo-languages">';
            echo '<strong>' . esc_html__( '语言包检查', 'developer-starter' ) . '</strong>';
            echo '<div class="ds-i18n-seo-language-grid">';
            foreach ( $languages as $language ) {
                if ( ! is_array( $language ) ) {
                    continue;
                }
                $status = ! empty( $language['has_mo'] ) ? 'pass' : ( ! empty( $language['requires_mo'] ) ? 'warning' : 'info' );
                $name = isset( $language['name'] ) ? (string) $language['name'] : '';
                $code = isset( $language['code'] ) ? (string) $language['code'] : '';
                $locale = isset( $language['locale'] ) ? (string) $language['locale'] : '';
                $label = ! empty( $language['has_mo'] ) ? __( '已存在', 'developer-starter' ) : ( ! empty( $language['requires_mo'] ) ? __( '缺少 .mo', 'developer-starter' ) : __( '未启用，仅参考', 'developer-starter' ) );

                echo '<div class="ds-i18n-seo-language is-' . esc_attr( $status ) . '">';
                echo '<span>' . esc_html( $name ) . '</span>';
                echo '<strong>' . esc_html( $code . ' / ' . $locale ) . '</strong>';
                echo '<em>' . esc_html( $label ) . '</em>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * Render full-site multilingual SEO scan powered by 启灵AI多语言.
     *
     * @param array<string,mixed> $options Theme options.
     * @return void
     */
    private function render_international_seo_site_scan_field( $options ) {
        $this->render_international_seo_diagnostics_assets_once();

        $scan = $this->get_aifanyi_multilingual_seo_site_scan();
        $summary = isset( $scan['summary'] ) && is_array( $scan['summary'] ) ? $scan['summary'] : array();
        $issues = isset( $scan['issues'] ) && is_array( $scan['issues'] ) ? $scan['issues'] : array();
        $available = ! empty( $scan['available'] );
        $issue_count = isset( $summary['issue_count'] ) ? absint( $summary['issue_count'] ) : count( $issues );
        $generated = isset( $_GET['i18n_seo_generated'] ) ? absint( wp_unslash( $_GET['i18n_seo_generated'] ) ) : null;
        $failed = isset( $_GET['i18n_seo_failed'] ) ? absint( wp_unslash( $_GET['i18n_seo_failed'] ) ) : 0;

        echo '<tr id="setting-row-international_seo_site_scan" data-setting-id="international_seo_site_scan"><th scope="row">' . esc_html__( '全站 SEO 扫描', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-i18n-seo-panel ds-i18n-seo-site-scan">';
        echo '<div class="ds-i18n-seo-panel__head">';
        echo '<div>';
        echo '<strong>' . esc_html__( '多语言 SEO 增强扫描', 'developer-starter' ) . '</strong>';
        echo '<p>' . esc_html__( '通过启灵AI多语言汇总各公开页面及语言的 SEO、OG、hreflang、x-default 与 sitemap 缺失项。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '<span class="ds-i18n-seo-score ' . esc_attr( $available && 0 === $issue_count ? 'is-success' : 'is-warning' ) . '">';
        echo esc_html( $available ? sprintf( __( '%d 个问题', 'developer-starter' ), $issue_count ) : __( '扫描服务不可用', 'developer-starter' ) );
        echo '</span>';
        echo '</div>';

        if ( null !== $generated ) {
            echo '<div class="notice notice-' . esc_attr( $failed > 0 ? 'warning' : 'success' ) . ' inline"><p>';
            echo esc_html( sprintf( __( '批量生成完成：更新 %1$d 组语言 SEO 字段，失败 %2$d 组。', 'developer-starter' ), $generated, $failed ) );
            echo '</p></div>';
        }

        if ( ! $available ) {
            echo '<p class="description">' . esc_html__( '启灵AI多语言的全站扫描功能当前不可用。基础检查仍可使用；增强扫描、批量生成和报告导出需要启用并更新启灵AI多语言插件。', 'developer-starter' ) . '</p>';
            echo '</div></td></tr>';
            return;
        }

        $summary_cards = array(
            array(
                'label' => __( '扫描内容', 'developer-starter' ),
                'value' => sprintf(
                    __( '%1$d 篇 / %2$d 语言', 'developer-starter' ),
                    isset( $summary['posts_scanned'] ) ? absint( $summary['posts_scanned'] ) : 0,
                    isset( $summary['language_count'] ) ? absint( $summary['language_count'] ) : 0
                ),
            ),
            array(
                'label' => __( 'SEO 缺口', 'developer-starter' ),
                'value' => sprintf(
                    __( '标题 %1$d / 描述 %2$d', 'developer-starter' ),
                    isset( $summary['seo_title_missing'] ) ? absint( $summary['seo_title_missing'] ) : 0,
                    isset( $summary['seo_description_missing'] ) ? absint( $summary['seo_description_missing'] ) : 0
                ),
            ),
            array(
                'label' => __( 'OG 缺口', 'developer-starter' ),
                'value' => sprintf(
                    __( '标题 %1$d / 描述 %2$d', 'developer-starter' ),
                    isset( $summary['og_title_missing'] ) ? absint( $summary['og_title_missing'] ) : 0,
                    isset( $summary['og_description_missing'] ) ? absint( $summary['og_description_missing'] ) : 0
                ),
            ),
            array(
                'label' => __( 'hreflang', 'developer-starter' ),
                'value' => sprintf(
                    __( '缺失 %1$d / 规则 %2$d', 'developer-starter' ),
                    isset( $summary['hreflang_missing'] ) ? absint( $summary['hreflang_missing'] ) : 0,
                    isset( $summary['hreflang_reciprocal_issues'] ) ? absint( $summary['hreflang_reciprocal_issues'] ) : 0
                ),
            ),
            array(
                'label' => __( 'x-default / sitemap', 'developer-starter' ),
                'value' => sprintf(
                    __( '%1$d / %2$d', 'developer-starter' ),
                    isset( $summary['x_default_issues'] ) ? absint( $summary['x_default_issues'] ) : 0,
                    isset( $summary['sitemap_missing_urls'] ) ? absint( $summary['sitemap_missing_urls'] ) : 0
                ),
            ),
        );

        echo '<div class="ds-i18n-seo-summary">';
        foreach ( $summary_cards as $card ) {
            echo '<div class="ds-i18n-seo-summary__card">';
            echo '<span>' . esc_html( $card['label'] ) . '</span>';
            echo '<strong>' . esc_html( $card['value'] ) . '</strong>';
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="ds-i18n-seo-actions">';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        wp_nonce_field( 'developer_starter_generate_i18n_seo_meta' );
        echo '<input type="hidden" name="action" value="developer_starter_generate_i18n_seo_meta">';
        echo '<button type="submit" class="button button-primary">' . esc_html__( '批量补齐 SEO 标题/描述', 'developer-starter' ) . '</button>';
        echo '</form>';
        echo '<a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=developer_starter_export_i18n_seo_report' ), 'developer_starter_export_i18n_seo_report' ) ) . '">' . esc_html__( '导出诊断报告 CSV', 'developer-starter' ) . '</a>';
        echo '</div>';

        if ( empty( $issues ) ) {
            echo '<p class="description">' . esc_html__( '当前扫描没有发现多语言 SEO 缺口。', 'developer-starter' ) . '</p>';
        } else {
            echo '<div class="ds-i18n-seo-table-wrap">';
            echo '<table class="widefat striped ds-i18n-seo-issue-table">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__( '页面', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '语言', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '问题', 'developer-starter' ) . '</th>';
            echo '<th>' . esc_html__( '操作', 'developer-starter' ) . '</th>';
            echo '</tr></thead><tbody>';

            foreach ( array_slice( $issues, 0, 20 ) as $issue ) {
                if ( ! is_array( $issue ) ) {
                    continue;
                }
                $post_title = isset( $issue['post_title'] ) ? (string) $issue['post_title'] : '';
                $post_id = isset( $issue['post_id'] ) ? absint( $issue['post_id'] ) : 0;
                $language_label = isset( $issue['language_label'] ) ? (string) $issue['language_label'] : '';
                $message = isset( $issue['message'] ) ? (string) $issue['message'] : '';
                $type = isset( $issue['type'] ) ? (string) $issue['type'] : '';
                $frontend_url = isset( $issue['frontend_url'] ) ? (string) $issue['frontend_url'] : '';
                $edit_url = isset( $issue['edit_url'] ) ? (string) $issue['edit_url'] : '';

                echo '<tr>';
                echo '<td><strong>' . esc_html( '' !== $post_title ? $post_title : '#' . $post_id ) . '</strong><br><span class="description">ID: ' . esc_html( (string) $post_id ) . '</span></td>';
                echo '<td>' . esc_html( $language_label ) . '</td>';
                echo '<td><span class="ds-i18n-seo-issue-type">' . esc_html( $type ) . '</span><br>' . esc_html( $message ) . '</td>';
                echo '<td class="ds-i18n-seo-row-actions">';
                if ( '' !== $edit_url ) {
                    echo '<a class="button button-small" href="' . esc_url( $edit_url ) . '">' . esc_html__( '编辑译文', 'developer-starter' ) . '</a> ';
                }
                if ( '' !== $frontend_url ) {
                    echo '<a class="button button-small" href="' . esc_url( $frontend_url ) . '" target="_blank" rel="noopener">' . esc_html__( '查看前台', 'developer-starter' ) . '</a>';
                }
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
            if ( count( $issues ) > 20 ) {
                echo '<p class="description">' . esc_html( sprintf( __( '仅显示前 20 个问题；完整结果请导出 CSV 报告，共 %d 个问题。', 'developer-starter' ), count( $issues ) ) ) . '</p>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * Export multilingual SEO report.
     *
     * @return void
     */
    public function handle_international_seo_report_export() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '当前用户无权导出多语言 SEO 报告。', 'developer-starter' ) );
        }
        check_admin_referer( 'developer_starter_export_i18n_seo_report' );

        if ( ! function_exists( 'xb_aifanyi_export_site_seo_diagnostics' ) ) {
            wp_die( esc_html__( '启灵AI多语言报告服务不可用。', 'developer-starter' ) );
        }

        $report = xb_aifanyi_export_site_seo_diagnostics( array(
            'post_type' => array( 'post', 'page' ),
            'limit'     => -1,
        ) );
        if ( ! is_array( $report ) || empty( $report['success'] ) ) {
            wp_die( esc_html__( '诊断报告生成失败。', 'developer-starter' ) );
        }

        $filename = ! empty( $report['filename'] ) ? sanitize_file_name( (string) $report['filename'] ) : 'qiling-multilingual-seo-report.csv';
        $content = isset( $report['content'] ) ? (string) $report['content'] : '';

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV download content is generated by provider.
        exit;
    }

    /**
     * Run batch SEO meta generation.
     *
     * @return void
     */
    public function handle_international_seo_meta_generation() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( '当前用户无权批量生成多语言 SEO 字段。', 'developer-starter' ) );
        }
        check_admin_referer( 'developer_starter_generate_i18n_seo_meta' );

        $result = array(
            'updated_count' => 0,
            'failed_count'  => 0,
        );
        if ( function_exists( 'xb_aifanyi_generate_site_seo_meta' ) ) {
            $result = xb_aifanyi_generate_site_seo_meta( array(
                'post_type'    => array( 'post', 'page' ),
                'limit'        => -1,
                'fields'       => array( 'title', 'description' ),
                'only_missing' => true,
            ) );
        }

        $redirect_url = add_query_arg(
            array(
                'page'               => 'developer-starter-settings',
                'tab'                => 'international',
                'i18n_seo_generated' => isset( $result['updated_count'] ) ? absint( $result['updated_count'] ) : 0,
                'i18n_seo_failed'    => isset( $result['failed_count'] ) ? absint( $result['failed_count'] ) : 0,
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $redirect_url . '#setting-row-international_seo_site_scan' );
        exit;
    }

    /**
     * Build read-only international SEO diagnostics.
     *
     * @param array<string,mixed> $options Theme options.
     * @return array<string,mixed>
     */
    private function build_international_seo_diagnostics( $options ) {
        $mode = isset( $options['frontend_language_switch_mode'] ) ? (string) $options['frontend_language_switch_mode'] : '';
        if ( ! in_array( $mode, array( '', 'translate_js', 'multilingual_content' ), true ) ) {
            $mode = '';
        }

        $mode_labels = array(
            ''                      => __( '关闭', 'developer-starter' ),
            'translate_js'          => __( 'translate.js 机翻模式', 'developer-starter' ),
            'multilingual_content'  => __( '多语言内容模式', 'developer-starter' ),
        );
        $mode_label = isset( $mode_labels[ $mode ] ) ? $mode_labels[ $mode ] : $mode_labels[''];

        $languages = $this->get_international_seo_languages( $options );
        $default_lang = $this->get_international_seo_default_lang( $options, $languages );
        $currency = isset( $options['schema_default_currency'] ) ? (string) preg_replace( '/[^A-Z]/', '', strtoupper( (string) $options['schema_default_currency'] ) ) : 'CNY';
        if ( 3 !== strlen( $currency ) ) {
            $currency = 'CNY';
        }

        $aifanyi_provider = $this->get_aifanyi_multilingual_seo_provider_diagnostics( 0 );
        $has_aifanyi_provider = ! empty( $aifanyi_provider['available'] );
        $has_aifanyi = $has_aifanyi_provider || function_exists( 'xb_aifanyi_get_frontend_hreflang_map' );
        $has_polylang = function_exists( 'pll_the_languages' );
        $has_wpml = function_exists( 'icl_get_languages' );
        $has_hreflang_provider = $has_aifanyi || $has_polylang || $has_wpml;
        $integration_names = array();
        if ( $has_aifanyi ) {
            $integration_names[] = 'AI 翻译 hreflang';
        }
        if ( $has_polylang ) {
            $integration_names[] = 'Polylang';
        }
        if ( $has_wpml ) {
            $integration_names[] = 'WPML';
        }

        $items = array();
        $items[] = array(
            'status' => 'multilingual_content' === $mode ? 'pass' : 'info',
            'title'  => __( '多语言模式', 'developer-starter' ),
            'detail' => 'multilingual_content' === $mode
                ? __( '当前启用了多语言内容模式，主题会按前台语言切换 locale。', 'developer-starter' )
                : __( '当前不是多语言内容模式；中文默认站点无需处理，海外多语言站建议使用多语言内容模式。', 'developer-starter' ),
            'badge'  => $mode_label,
        );

        $provider_label = $has_aifanyi_provider ? __( '启灵AI多语言', 'developer-starter' ) : __( '主题配置检查', 'developer-starter' );
        $items[] = array(
            'status' => $has_aifanyi_provider ? 'pass' : ( 'multilingual_content' === $mode ? 'warning' : 'info' ),
            'title'  => __( '启灵AI多语言协作状态', 'developer-starter' ),
            'detail' => $has_aifanyi_provider
                ? __( '已检测到启灵AI多语言，可读取译文、URL、hreflang、canonical 和 sitemap 状态；SEO 标签仍由主题统一输出。', 'developer-starter' )
                : __( '未检测到启灵AI多语言，当前仅检查主题自身的语言配置。', 'developer-starter' ),
            'badge'  => $has_aifanyi_provider ? __( '已连接', 'developer-starter' ) : __( '基础检查', 'developer-starter' ),
        );

        $missing_language_packs = 0;
        foreach ( $languages as $index => $language ) {
            $locale = isset( $language['locale'] ) ? (string) $language['locale'] : '';
            $mo_file = '' !== $locale ? DEVELOPER_STARTER_DIR . '/languages/developer-starter-' . $locale . '.mo' : '';
            $po_file = '' !== $locale ? DEVELOPER_STARTER_DIR . '/languages/developer-starter-' . $locale . '.po' : '';
            $languages[ $index ]['has_mo'] = '' !== $mo_file && is_readable( $mo_file );
            $languages[ $index ]['has_po'] = '' !== $po_file && is_readable( $po_file );
            $languages[ $index ]['requires_mo'] = 'multilingual_content' === $mode;
            if ( ! $languages[ $index ]['has_mo'] ) {
                $missing_language_packs++;
            }
        }

        $language_pack_status = ( 'multilingual_content' === $mode && $missing_language_packs > 0 ) ? 'warning' : 'info';
        if ( 'multilingual_content' === $mode && 0 === $missing_language_packs ) {
            $language_pack_status = 'pass';
        }
        $items[] = array(
            'status' => $language_pack_status,
            'title'  => __( '语言包文件', 'developer-starter' ),
            'detail' => 0 === $missing_language_packs
                ? __( '已配置语言都有对应 .mo 文件。', 'developer-starter' )
                : sprintf( __( '当前有 %d 个配置语言缺少对应 .mo 文件；未启用多语言内容模式时仅作参考。', 'developer-starter' ), $missing_language_packs ),
            'badge'  => sprintf( __( '%d 个语言', 'developer-starter' ), count( $languages ) ),
        );

        $items[] = array(
            'status' => $has_hreflang_provider ? 'pass' : ( 'multilingual_content' === $mode ? 'warning' : 'info' ),
            'title'  => __( 'hreflang 来源', 'developer-starter' ),
            'detail' => $has_hreflang_provider
                ? sprintf( __( '检测到可用 hreflang 来源：%s。', 'developer-starter' ), implode( ' / ', $integration_names ) )
                : __( '未检测到 AI 翻译 hreflang、Polylang 或 WPML。中文默认站点可忽略；多语言站建议确认 hreflang 来源。', 'developer-starter' ),
            'badge'  => $has_hreflang_provider ? __( '可输出', 'developer-starter' ) : __( '未检测到', 'developer-starter' ),
        );

        $items[] = array(
            'status' => $has_aifanyi ? 'pass' : ( $has_hreflang_provider ? 'warning' : 'info' ),
            'title'  => __( 'x-default', 'developer-starter' ),
            'detail' => $has_aifanyi
                ? __( '当前启灵AI多语言 hreflang map 支持主题输出 x-default。', 'developer-starter' )
                : __( '主题在 AI 翻译 hreflang map 存在时会输出 x-default；Polylang/WPML 场景建议人工复核。', 'developer-starter' ),
            'badge'  => $has_aifanyi ? __( '已覆盖', 'developer-starter' ) : __( '建议复核', 'developer-starter' ),
        );

        $has_overseas_language = $this->international_seo_has_overseas_language( $languages );
        $items[] = array(
            'status' => ( $has_overseas_language && 'CNY' === $currency ) ? 'warning' : 'pass',
            'title'  => __( 'Schema 默认币种', 'developer-starter' ),
            'detail' => ( $has_overseas_language && 'CNY' === $currency )
                ? __( '语言列表包含海外语言且默认币种仍为 CNY，如面向海外市场建议确认是否需要 USD/EUR/JPY/KRW。', 'developer-starter' )
                : __( '当前 schema 默认币种格式正常。', 'developer-starter' ),
            'badge'  => $currency,
        );

        $items[] = array(
            'status' => function_exists( 'xb_aifanyi_get_frontend_og_locale_data' ) ? 'pass' : 'info',
            'title'  => __( 'OG locale', 'developer-starter' ),
            'detail' => function_exists( 'xb_aifanyi_get_frontend_og_locale_data' )
                ? __( '检测到 OG locale 数据来源，主题可输出 og:locale 和 og:locale:alternate。', 'developer-starter' )
                : __( '未检测到专用 OG locale 数据来源；中文默认站点可忽略，多语言站建议复核社交分享语言。', 'developer-starter' ),
            'badge'  => function_exists( 'xb_aifanyi_get_frontend_og_locale_data' ) ? __( '可输出', 'developer-starter' ) : __( '参考', 'developer-starter' ),
        );

        $sitemap = $has_aifanyi_provider && isset( $aifanyi_provider['sitemap'] ) && is_array( $aifanyi_provider['sitemap'] )
            ? $aifanyi_provider['sitemap']
            : array();
        $sitemap_provider_registered = ! empty( $sitemap['provider_registered'] );
        $sitemap_provider_name = isset( $sitemap['provider_name'] ) ? (string) $sitemap['provider_name'] : '';
        $items[] = array(
            'status' => $has_aifanyi_provider ? ( $sitemap_provider_registered ? 'pass' : 'warning' ) : 'info',
            'title'  => __( 'Sitemap 覆盖', 'developer-starter' ),
            'detail' => $has_aifanyi_provider
                ? ( $sitemap_provider_registered
                    ? sprintf( __( '启灵AI多语言 sitemap 已接入：%s。', 'developer-starter' ), $sitemap_provider_name )
                    : __( '已检测到启灵AI多语言，但 sitemap 功能当前不可用或 WordPress 原生 sitemap 未启用。', 'developer-starter' ) )
                : __( '插件未启用时不读取插件 sitemap；当前只做主题配置级提示。', 'developer-starter' ),
            'badge'  => $has_aifanyi_provider ? ( $sitemap_provider_registered ? __( '已注册', 'developer-starter' ) : __( '待确认', 'developer-starter' ) ) : __( '降级', 'developer-starter' ),
        );

        $summary = array(
            'mode_label'           => $mode_label,
            'default_lang'         => $default_lang,
            'currency'             => $currency,
            'provider_label'       => $provider_label,
            'has_aifanyi_provider' => $has_aifanyi_provider,
            'warning_count'        => 0,
            'ok_count'             => 0,
            'info_count'           => 0,
        );
        foreach ( $items as $item ) {
            $status = isset( $item['status'] ) ? (string) $item['status'] : 'info';
            if ( 'warning' === $status ) {
                $summary['warning_count']++;
            } elseif ( 'pass' === $status ) {
                $summary['ok_count']++;
            } else {
                $summary['info_count']++;
            }
        }

        return array(
            'summary'          => $summary,
            'items'            => $items,
            'languages'        => $languages,
            'aifanyi_provider' => $aifanyi_provider,
        );
    }

    /**
     * Read the first multilingual SEO provider exposed by 启灵AI多语言.
     *
     * @param int $post_id Optional post id.
     * @return array<string,mixed>
     */
    private function get_aifanyi_multilingual_seo_provider_diagnostics( $post_id = 0 ) {
        $capabilities = function_exists( 'xb_aifanyi_get_theme_provider_capabilities' )
            ? xb_aifanyi_get_theme_provider_capabilities()
            : array();

        if ( ! function_exists( 'xb_aifanyi_get_post_seo_diagnostics' ) ) {
            return is_array( $capabilities ) && ! empty( $capabilities )
                ? $capabilities
                : array(
                    'available' => false,
                );
        }

        $diagnostics = xb_aifanyi_get_post_seo_diagnostics( absint( $post_id ) );
        if ( is_array( $diagnostics ) && is_array( $capabilities ) && ! empty( $capabilities ) ) {
            $diagnostics['capabilities'] = $capabilities;
            if ( empty( $diagnostics['plugin_version'] ) && ! empty( $capabilities['plugin_version'] ) ) {
                $diagnostics['plugin_version'] = $capabilities['plugin_version'];
            }
            if ( empty( $diagnostics['contract_version'] ) && ! empty( $capabilities['contract_version'] ) ) {
                $diagnostics['contract_version'] = $capabilities['contract_version'];
            }
        }

        return is_array( $diagnostics ) ? $diagnostics : array(
            'available' => false,
        );
    }

    /**
     * Read full-site multilingual SEO diagnostics from 启灵AI多语言 provider.
     *
     * @return array<string,mixed>
     */
    private function get_aifanyi_multilingual_seo_site_scan() {
        if ( ! function_exists( 'xb_aifanyi_scan_site_seo_diagnostics' ) ) {
            return array(
                'available' => false,
                'summary'   => array(),
                'issues'    => array(),
            );
        }

        $scan = xb_aifanyi_scan_site_seo_diagnostics( array(
            'post_type' => array( 'post', 'page' ),
            'limit'     => -1,
        ) );

        return is_array( $scan ) ? $scan : array(
            'available' => false,
            'summary'   => array(),
            'issues'    => array(),
        );
    }

    /**
     * Get configured multilingual languages for diagnostics.
     *
     * @param array<string,mixed> $options Theme options.
     * @return array<int,array<string,string>>
     */
    private function get_international_seo_languages( $options ) {
        if ( function_exists( 'developer_starter_get_multilingual_languages' ) ) {
            $languages = developer_starter_get_multilingual_languages();
        } else {
            $languages = isset( $options['multilingual_languages'] ) && is_array( $options['multilingual_languages'] )
                ? $options['multilingual_languages']
                : array();
        }

        if ( empty( $languages ) || ! is_array( $languages ) ) {
            $languages = array(
                array( 'name' => __( '简体中文', 'developer-starter' ), 'code' => 'zh', 'locale' => 'zh_CN' ),
                array( 'name' => __( '繁体中文', 'developer-starter' ), 'code' => 'zh-tw', 'locale' => 'zh_TW' ),
                array( 'name' => 'English', 'code' => 'en', 'locale' => 'en_US' ),
                array( 'name' => __( '日文', 'developer-starter' ), 'code' => 'jp', 'locale' => 'ja_JP' ),
                array( 'name' => __( '韩文', 'developer-starter' ), 'code' => 'ko', 'locale' => 'ko_KR' ),
                array( 'name' => __( '法文', 'developer-starter' ), 'code' => 'fr', 'locale' => 'fr_FR' ),
                array( 'name' => __( '德文', 'developer-starter' ), 'code' => 'de', 'locale' => 'de_DE' ),
                array( 'name' => __( '西班牙文', 'developer-starter' ), 'code' => 'es', 'locale' => 'es_ES' ),
            );
        }

        $normalized = array();
        $seen = array();
        $locale_map = array(
            'zh' => 'zh_CN',
            'cn' => 'zh_CN',
            'en' => 'en_US',
            'jp' => 'ja_JP',
            'ja' => 'ja_JP',
            'ko' => 'ko_KR',
            'fr' => 'fr_FR',
            'de' => 'de_DE',
            'es' => 'es_ES',
        );
        foreach ( $languages as $language ) {
            if ( ! is_array( $language ) ) {
                continue;
            }
            $name = isset( $language['name'] ) ? trim( (string) $language['name'] ) : '';
            $code = isset( $language['code'] ) ? sanitize_title( (string) $language['code'] ) : '';
            $locale = isset( $language['locale'] ) ? trim( (string) $language['locale'] ) : '';
            if ( 'ja' === $code ) {
                $code = 'jp';
            }
            if ( '' === $name || '' === $code || isset( $seen[ $code ] ) ) {
                continue;
            }
            if ( '' === $locale && function_exists( 'developer_starter_get_frontend_locale_by_lang' ) ) {
                $locale = developer_starter_get_frontend_locale_by_lang( $code );
            }
            if ( '' === $locale ) {
                $locale = isset( $locale_map[ $code ] ) ? $locale_map[ $code ] : '';
            }
            $seen[ $code ] = true;
            $normalized[] = array(
                'name'   => $name,
                'code'   => $code,
                'locale' => $locale,
            );
        }

        return $normalized;
    }

    /**
     * Resolve default language for diagnostics.
     *
     * @param array<string,mixed> $options Theme options.
     * @param array<int,array<string,string>> $languages Languages.
     * @return string
     */
    private function get_international_seo_default_lang( $options, $languages ) {
        if ( function_exists( 'developer_starter_get_multilingual_default_lang' ) ) {
            return (string) developer_starter_get_multilingual_default_lang();
        }

        $default = isset( $options['multilingual_default_lang'] ) ? sanitize_title( (string) $options['multilingual_default_lang'] ) : 'zh';
        if ( 'ja' === $default ) {
            $default = 'jp';
        }

        $codes = array();
        foreach ( $languages as $language ) {
            if ( isset( $language['code'] ) ) {
                $codes[] = (string) $language['code'];
            }
        }

        return in_array( $default, $codes, true ) ? $default : ( isset( $codes[0] ) ? $codes[0] : 'zh' );
    }

    /**
     * Whether configured languages include an overseas language.
     *
     * @param array<int,array<string,mixed>> $languages Languages.
     * @return bool
     */
    private function international_seo_has_overseas_language( $languages ) {
        foreach ( $languages as $language ) {
            $code = isset( $language['code'] ) ? sanitize_key( (string) $language['code'] ) : '';
            if ( '' !== $code && ! in_array( $code, array( 'zh', 'cn' ), true ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render CSS for the diagnostics panel once.
     *
     * @return void
     */
    private function render_international_seo_diagnostics_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-i18n-seo-panel {
                border: 1px solid #d0d7de;
                border-radius: 10px;
                background: #fff;
                padding: 16px;
            }
            .ds-i18n-seo-panel__head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                margin-bottom: 14px;
            }
            .ds-i18n-seo-panel__head strong {
                display: block;
                color: #0f172a;
                font-size: 15px;
            }
            .ds-i18n-seo-panel__head p {
                margin: 6px 0 0;
                color: #64748b;
            }
            .ds-i18n-seo-score,
            .ds-i18n-seo-badge {
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                padding: 0 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }
            .ds-i18n-seo-score.is-success,
            .ds-i18n-seo-badge.is-pass {
                background: #dcfce7;
                color: #166534;
            }
            .ds-i18n-seo-score.is-warning,
            .ds-i18n-seo-badge.is-warning {
                background: #fef3c7;
                color: #92400e;
            }
            .ds-i18n-seo-badge.is-info {
                background: #e0f2fe;
                color: #075985;
            }
            .ds-i18n-seo-summary {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 14px;
            }
            .ds-i18n-seo-summary__card,
            .ds-i18n-seo-language {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #f8fafc;
                padding: 10px 12px;
            }
            .ds-i18n-seo-summary__card span,
            .ds-i18n-seo-language span {
                display: block;
                color: #64748b;
                font-size: 12px;
                margin-bottom: 5px;
            }
            .ds-i18n-seo-summary__card strong,
            .ds-i18n-seo-language strong {
                color: #111827;
                font-size: 13px;
            }
            .ds-i18n-seo-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .ds-i18n-seo-item {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                border: 1px solid #e2e8f0;
                border-left-width: 4px;
                border-radius: 8px;
                background: #fff;
                padding: 12px;
            }
            .ds-i18n-seo-item.is-pass {
                border-left-color: #22c55e;
            }
            .ds-i18n-seo-item.is-warning {
                border-left-color: #f59e0b;
            }
            .ds-i18n-seo-item.is-info {
                border-left-color: #38bdf8;
            }
            .ds-i18n-seo-item__main strong {
                display: block;
                color: #0f172a;
                margin-bottom: 5px;
            }
            .ds-i18n-seo-item__main p {
                margin: 0;
                color: #64748b;
                line-height: 1.6;
            }
            .ds-i18n-seo-languages {
                margin-top: 16px;
            }
            .ds-i18n-seo-languages > strong {
                display: block;
                margin-bottom: 10px;
            }
            .ds-i18n-seo-language-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
            }
            .ds-i18n-seo-language em {
                display: block;
                margin-top: 6px;
                color: #64748b;
                font-style: normal;
                font-size: 12px;
            }
            .ds-i18n-seo-language.is-warning {
                border-color: #f59e0b;
                background: #fffbeb;
            }
            .ds-i18n-seo-language.is-pass {
                border-color: #bbf7d0;
                background: #f0fdf4;
            }
            .ds-i18n-seo-language.is-info {
                border-color: #bae6fd;
                background: #f0f9ff;
            }
            .ds-i18n-seo-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                align-items: center;
                margin: 0 0 14px;
            }
            .ds-i18n-seo-actions form {
                margin: 0;
            }
            .ds-i18n-seo-table-wrap {
                margin-top: 12px;
            }
            .ds-i18n-seo-issue-table td {
                vertical-align: top;
            }
            .ds-i18n-seo-issue-type {
                display: inline-block;
                margin-bottom: 4px;
                color: #92400e;
                font-size: 12px;
                font-weight: 700;
            }
            .ds-i18n-seo-row-actions {
                min-width: 150px;
                white-space: nowrap;
            }
            @media (max-width: 960px) {
                .ds-i18n-seo-panel__head,
                .ds-i18n-seo-item {
                    flex-direction: column;
                }
                .ds-i18n-seo-summary,
                .ds-i18n-seo-language-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
    }

    /**
     * Render read-only Schema.org JSON-LD preview.
     *
     * @param array<string,mixed> $options Theme options.
     * @return void
     */
    private function render_schema_preview_field( $options ) {
        unset( $options );

        $this->render_schema_preview_assets_once();

        if ( ! class_exists( '\Developer_Starter\SEO\Industry_Schema_Engine' ) ) {
            echo '<tr id="setting-row-schema_jsonld_preview" data-setting-id="schema_jsonld_preview"><th scope="row">' . esc_html__( 'JSON-LD 预览', 'developer-starter' ) . '</th><td>';
            echo '<div class="ds-schema-preview-panel is-warning"><p>' . esc_html__( '未检测到行业 Schema 引擎，当前无法生成预览。', 'developer-starter' ) . '</p></div>';
            echo '</td></tr>';
            return;
        }

        $preview = \Developer_Starter\SEO\Industry_Schema_Engine::get_instance()->get_preview_data( 0 );
        $node_status = isset( $preview['node_status'] ) && is_array( $preview['node_status'] ) ? $preview['node_status'] : array();
        $missing_required = isset( $preview['missing_required'] ) && is_array( $preview['missing_required'] ) ? $preview['missing_required'] : array();
        $diagnostics = isset( $preview['diagnostics'] ) && is_array( $preview['diagnostics'] ) ? $preview['diagnostics'] : array();
        $visual_issues = isset( $diagnostics['issues'] ) && is_array( $diagnostics['issues'] ) ? $diagnostics['issues'] : array();
        $json = isset( $preview['json_ld'] ) ? (string) $preview['json_ld'] : '';
        $json = $this->format_schema_preview_json( $json );
        $enabled = ! empty( $preview['enabled'] );
        $currency = isset( $preview['default_currency'] ) ? (string) $preview['default_currency'] : 'CNY';
        $industry = isset( $preview['industry'] ) ? (string) $preview['industry'] : '';
        $warning_count = count( $visual_issues );

        $node_defaults = array(
            'organization' => array( 'label' => 'Organization', 'empty' => __( '未生成组织实体', 'developer-starter' ) ),
            'website'      => array( 'label' => 'WebSite', 'empty' => __( '未生成站点实体', 'developer-starter' ) ),
            'breadcrumb'   => array( 'label' => 'Breadcrumb', 'empty' => __( '当前预览上下文未生成 BreadcrumbList', 'developer-starter' ) ),
            'primary'      => array( 'label' => __( '当前页面主类型', 'developer-starter' ), 'empty' => __( '未识别页面主类型', 'developer-starter' ) ),
        );

        echo '<tr id="setting-row-schema_jsonld_preview" data-setting-id="schema_jsonld_preview"><th scope="row">' . esc_html__( 'JSON-LD 预览', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-schema-preview-panel">';
        echo '<div class="ds-schema-preview-panel__head">';
        echo '<div>';
        echo '<strong>' . esc_html__( 'Schema 结构化数据预览', 'developer-starter' ) . '</strong>';
        echo '<p>' . esc_html__( '预览根据当前已保存的 Schema 配置生成，前台由主题统一输出。', 'developer-starter' ) . '</p>';
        echo '</div>';
        echo '<span class="ds-schema-preview-score ' . esc_attr( $warning_count > 0 || ! $enabled ? 'is-warning' : 'is-success' ) . '">';
        echo esc_html( ! $enabled ? __( '引擎未启用', 'developer-starter' ) : ( $warning_count > 0 ? sprintf( __( '%d 项需补齐', 'developer-starter' ), $warning_count ) : __( '基础字段完整', 'developer-starter' ) ) );
        echo '</span>';
        echo '</div>';

        echo '<div class="ds-schema-preview-meta">';
        echo '<div><span>' . esc_html__( '输出状态', 'developer-starter' ) . '</span><strong>' . esc_html( $enabled ? __( '已启用', 'developer-starter' ) : __( '未启用', 'developer-starter' ) ) . '</strong></div>';
        echo '<div><span>' . esc_html__( '行业类型', 'developer-starter' ) . '</span><strong>' . esc_html( '' !== $industry ? $industry : __( '自动识别', 'developer-starter' ) ) . '</strong></div>';
        echo '<div><span>' . esc_html__( '默认币种', 'developer-starter' ) . '</span><strong>' . esc_html( $currency ) . '</strong></div>';
        echo '</div>';

        echo '<div class="ds-schema-preview-nodes">';
        foreach ( $node_defaults as $key => $fallback ) {
            $node = isset( $node_status[ $key ] ) && is_array( $node_status[ $key ] ) ? $node_status[ $key ] : array();
            $present = ! empty( $node['present'] );
            $type = isset( $node['type'] ) ? trim( (string) $node['type'] ) : '';
            $status = $present ? 'pass' : ( 'breadcrumb' === $key ? 'info' : 'warning' );
            echo '<div class="ds-schema-preview-node is-' . esc_attr( $status ) . '">';
            echo '<span>' . esc_html( $fallback['label'] ) . '</span>';
            echo '<strong>' . esc_html( '' !== $type ? $type : $fallback['empty'] ) . '</strong>';
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="ds-schema-preview-missing">';
        echo '<strong>' . esc_html__( '必填字段缺失提示', 'developer-starter' ) . '</strong>';
        if ( empty( $missing_required ) ) {
            echo '<p class="is-success">' . esc_html__( '当前站点级必填字段已补齐。', 'developer-starter' ) . '</p>';
        } else {
            echo '<ul>';
            foreach ( $missing_required as $warning ) {
                if ( ! is_array( $warning ) ) {
                    continue;
                }
                $label = isset( $warning['label'] ) ? (string) $warning['label'] : '';
                $message = isset( $warning['message'] ) ? (string) $warning['message'] : '';
                echo '<li><strong>' . esc_html( $label ) . '</strong><span>' . esc_html( $message ) . '</span></li>';
            }
            echo '</ul>';
        }
        echo '</div>';

        echo '<div class="ds-schema-preview-issues">';
        echo '<strong>' . esc_html__( 'Schema 可视化诊断', 'developer-starter' ) . '</strong>';
        if ( empty( $visual_issues ) ) {
            echo '<p class="is-success">' . esc_html__( '当前未发现 Schema 冲突或关键缺失项。', 'developer-starter' ) . '</p>';
        } else {
            echo '<ul>';
            foreach ( $visual_issues as $issue ) {
                if ( ! is_array( $issue ) ) {
                    continue;
                }
                $severity = isset( $issue['severity'] ) ? sanitize_html_class( (string) $issue['severity'] ) : 'warning';
                $label = isset( $issue['label'] ) ? (string) $issue['label'] : __( '诊断项', 'developer-starter' );
                $message = isset( $issue['message'] ) ? (string) $issue['message'] : '';
                echo '<li class="is-' . esc_attr( $severity ) . '"><strong>' . esc_html( $label ) . '</strong><span>' . esc_html( $message ) . '</span></li>';
            }
            echo '</ul>';
        }
        echo '</div>';

        echo '<div class="ds-schema-preview-json">';
        echo '<div class="ds-schema-preview-json__head"><strong>' . esc_html__( 'JSON-LD 预览', 'developer-starter' ) . '</strong><span>' . esc_html__( '只读', 'developer-starter' ) . '</span></div>';
        if ( '' !== $json ) {
            echo '<pre><code>' . esc_html( $json ) . '</code></pre>';
        } else {
            echo '<p>' . esc_html__( '当前没有可预览的 JSON-LD。请确认引擎已启用并保存基础配置。', 'developer-starter' ) . '</p>';
        }
        echo '</div>';

        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * Pretty-print JSON-LD for the admin preview.
     *
     * @param string $json Minified JSON.
     * @return string
     */
    private function format_schema_preview_json( $json ) {
        if ( '' === $json ) {
            return '';
        }

        $decoded = json_decode( $json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return $json;
        }

        $pretty = wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

        return is_string( $pretty ) ? $pretty : $json;
    }

    /**
     * Render CSS for the Schema preview panel once.
     *
     * @return void
     */
    private function render_schema_preview_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-schema-preview-panel {
                border: 1px solid #d0d7de;
                border-radius: 10px;
                background: #fff;
                padding: 16px;
            }
            .ds-schema-preview-panel__head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                margin-bottom: 14px;
            }
            .ds-schema-preview-panel__head strong {
                display: block;
                color: #0f172a;
                font-size: 15px;
            }
            .ds-schema-preview-panel__head p {
                margin: 6px 0 0;
                color: #64748b;
            }
            .ds-schema-preview-score {
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                padding: 0 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }
            .ds-schema-preview-score.is-success {
                background: #dcfce7;
                color: #166534;
            }
            .ds-schema-preview-score.is-warning {
                background: #fef3c7;
                color: #92400e;
            }
            .ds-schema-preview-meta,
            .ds-schema-preview-nodes {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 14px;
            }
            .ds-schema-preview-meta > div,
            .ds-schema-preview-node,
            .ds-schema-preview-missing,
            .ds-schema-preview-issues {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #f8fafc;
                padding: 10px 12px;
            }
            .ds-schema-preview-meta span,
            .ds-schema-preview-node span {
                display: block;
                color: #64748b;
                font-size: 12px;
                margin-bottom: 5px;
            }
            .ds-schema-preview-meta strong,
            .ds-schema-preview-node strong {
                display: block;
                color: #111827;
                font-size: 13px;
                line-height: 1.4;
                overflow-wrap: anywhere;
            }
            .ds-schema-preview-node.is-pass {
                border-color: #bbf7d0;
                background: #f0fdf4;
            }
            .ds-schema-preview-node.is-warning {
                border-color: #f59e0b;
                background: #fffbeb;
            }
            .ds-schema-preview-node.is-info {
                border-color: #bae6fd;
                background: #f0f9ff;
            }
            .ds-schema-preview-missing,
            .ds-schema-preview-issues {
                margin-bottom: 14px;
            }
            .ds-schema-preview-missing > strong,
            .ds-schema-preview-issues > strong {
                display: block;
                color: #0f172a;
                margin-bottom: 8px;
            }
            .ds-schema-preview-missing p,
            .ds-schema-preview-issues p {
                margin: 0;
                color: #166534;
            }
            .ds-schema-preview-missing ul,
            .ds-schema-preview-issues ul {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin: 0;
            }
            .ds-schema-preview-missing li,
            .ds-schema-preview-issues li {
                display: flex;
                gap: 8px;
                margin: 0;
                color: #64748b;
            }
            .ds-schema-preview-missing li strong,
            .ds-schema-preview-issues li strong {
                min-width: 84px;
            }
            .ds-schema-preview-missing li strong,
            .ds-schema-preview-issues li.is-warning strong {
                color: #92400e;
            }
            .ds-schema-preview-issues li.is-error strong {
                color: #b91c1c;
            }
            .ds-schema-preview-json {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                overflow: hidden;
                background: #0f172a;
            }
            .ds-schema-preview-json__head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                padding: 10px 12px;
                background: #111827;
                color: #e5e7eb;
            }
            .ds-schema-preview-json__head span {
                color: #93c5fd;
                font-size: 12px;
            }
            .ds-schema-preview-json pre {
                max-height: 420px;
                overflow: auto;
                margin: 0;
                padding: 14px;
                color: #e5e7eb;
                font-size: 12px;
                line-height: 1.6;
                white-space: pre-wrap;
                overflow-wrap: anywhere;
            }
            .ds-schema-preview-json p {
                margin: 0;
                padding: 14px;
                color: #e5e7eb;
            }
            @media (max-width: 960px) {
                .ds-schema-preview-panel__head {
                    flex-direction: column;
                }
                .ds-schema-preview-meta,
                .ds-schema-preview-nodes {
                    grid-template-columns: 1fr;
                }
                .ds-schema-preview-missing li {
                    flex-direction: column;
                }
            }
        </style>
        <?php
    }
}
