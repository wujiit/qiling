<?php
/**
 * Admin Settings Domain Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Domain_Trait {

    public function render_domain_compare_field( $options ) {
        $current_base = function_exists( 'developer_starter_get_raw_home_base_url' )
            ? untrailingslashit( developer_starter_get_raw_home_base_url() )
            : untrailingslashit( home_url( '/' ) );
        $stored_base = function_exists( 'developer_starter_normalize_home_base_candidate' )
            ? developer_starter_normalize_home_base_candidate( (string) get_option( 'developer_starter_last_known_home_base_url', '' ), $current_base )
            : '';
        $selected_old_base = isset( $_GET['ds_compare_old_domain'] ) ? sanitize_text_field( wp_unslash( $_GET['ds_compare_old_domain'] ) ) : '';
        if ( function_exists( 'developer_starter_normalize_home_base_candidate' ) ) {
            $selected_old_base = developer_starter_normalize_home_base_candidate( $selected_old_base, $current_base );
        }
        $scan_url = method_exists( $this, 'get_advanced_settings_url' )
            ? $this->get_advanced_settings_url( array( 'ds_run_domain_scan' => '1' ) )
            : add_query_arg(
                array(
                    'page'               => 'developer-starter-settings',
                    'tab'                => 'advanced',
                    'ds_run_domain_scan' => '1',
                ),
                admin_url( 'admin.php' )
            );
        $should_scan = method_exists( $this, 'is_domain_scan_requested' )
            ? $this->is_domain_scan_requested()
            : ( '' !== $selected_old_base );
        $scan_button_label = $should_scan
            ? __( '重新检测域名设置', 'developer-starter' )
            : __( '开始域名检查', 'developer-starter' );

        echo '<tr><th scope="row">' . esc_html__( '域名设置检查', 'developer-starter' ) . '</th><td>';
        echo '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">';
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;margin-bottom:16px;">';
        echo '<div style="padding:12px;border-radius:10px;background:#f8fafc;"><strong>' . esc_html__( '当前域名', 'developer-starter' ) . '</strong><div style="margin-top:6px;color:#1f2937;">' . esc_html( $current_base ) . '</div></div>';
        echo '<div style="padding:12px;border-radius:10px;background:#f8fafc;"><strong>' . esc_html__( '上次记录域名', 'developer-starter' ) . '</strong><div style="margin-top:6px;color:#1f2937;">' . ( $stored_base ? esc_html( $stored_base ) : '—' ) . '</div></div>';
        echo '</div>';
        echo '<div style="padding:10px 12px;border-radius:10px;background:#fffbeb;color:#92400e;margin-bottom:16px;">';
        echo esc_html__( '说明：域名设置检查已改为手动触发，不会在后台常驻扫描。点击按钮后，才会递归检查主题设置里的旧域名差异和当前域名依赖项。', 'developer-starter' );
        echo '</div>';
        echo '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">';
        echo '<a class="button button-primary" href="' . esc_url( $scan_url ) . '">' . esc_html( $scan_button_label ) . '</a>';
        if ( '' !== $selected_old_base ) {
            echo '<a class="button button-secondary" href="' . esc_url( $scan_url ) . '">' . esc_html__( '清除旧域名对比', 'developer-starter' ) . '</a>';
        }
        echo '</div>';

        if ( ! $should_scan ) {
            echo '<div style="padding:10px 12px;border-radius:10px;background:#eff6ff;color:#1d4ed8;">' . esc_html__( '当前未执行域名扫描。为了降低后台页面开销，只有点击上方按钮时才会运行检测。', 'developer-starter' ) . '</div>';
            echo '</div>';
            echo '</td></tr>';
            return;
        }

        $candidates = function_exists( 'developer_starter_get_theme_option_domain_candidates' )
            ? developer_starter_get_theme_option_domain_candidates( $options )
            : array();
        $whitelist_hosts = function_exists( 'developer_starter_get_domain_scan_whitelist_hosts' )
            ? developer_starter_get_domain_scan_whitelist_hosts( $options )
            : array();
        $risk_rows = function_exists( 'developer_starter_get_theme_option_domain_risk_rows' )
            ? developer_starter_get_theme_option_domain_risk_rows( $options )
            : array();
        $risk_stats = array(
            'total'    => count( $risk_rows ),
            'direct'   => 0,
            'embedded' => 0,
        );

        foreach ( $risk_rows as $risk_row ) {
            $risk_type = isset( $risk_row['risk_type'] ) ? (string) $risk_row['risk_type'] : '';
            if ( $risk_type === __( '站内绝对地址', 'developer-starter' ) ) {
                $risk_stats['direct']++;
            } elseif ( $risk_type === __( '文本/HTML 内嵌站内绝对地址', 'developer-starter' ) ) {
                $risk_stats['embedded']++;
            }
        }

        $risk_path_labels = array();
        foreach ( $risk_rows as $index => $risk_row ) {
            $display = $this->get_domain_scan_display_path( isset( $risk_row['path'] ) ? $risk_row['path'] : '' );
            $risk_rows[ $index ]['display_path'] = $display['display'];
            $risk_rows[ $index ]['raw_path']     = $display['raw'];
            $risk_path_labels[ $display['display'] ] = true;
        }

        $issue_rows        = array();
        $issue_path_labels = array();
        $auto_old_bases    = array();

        if ( '' !== $stored_base && $stored_base !== $current_base ) {
            $auto_old_bases[] = $stored_base;
        }
        foreach ( $candidates as $candidate ) {
            $candidate = (string) $candidate;
            if ( '' !== $candidate && $candidate !== $current_base ) {
                $auto_old_bases[] = $candidate;
            }
        }
        $auto_old_bases = array_values( array_unique( $auto_old_bases ) );

        if ( function_exists( 'developer_starter_get_theme_option_domain_compare_rows' ) ) {
            $issue_row_map = array();
            foreach ( $auto_old_bases as $auto_old_base ) {
                $candidate_rows = developer_starter_get_theme_option_domain_compare_rows( $options, $auto_old_base, $current_base );
                foreach ( $candidate_rows as $candidate_row ) {
                    $candidate_key = md5(
                        wp_json_encode(
                            array(
                                isset( $candidate_row['path'] ) ? $candidate_row['path'] : '',
                                isset( $candidate_row['current'] ) ? $candidate_row['current'] : '',
                                isset( $candidate_row['suggested'] ) ? $candidate_row['suggested'] : '',
                            )
                        )
                    );
                    $issue_row_map[ $candidate_key ] = $candidate_row;
                }
            }
            $issue_rows = array_values( $issue_row_map );
        }

        foreach ( $issue_rows as $index => $issue_row ) {
            $display = $this->get_domain_scan_display_path( isset( $issue_row['path'] ) ? $issue_row['path'] : '' );
            $issue_rows[ $index ]['display_path'] = $display['display'];
            $issue_rows[ $index ]['raw_path']     = $display['raw'];
            $issue_path_labels[ $display['display'] ] = true;
        }

        $compare_rows = array();
        $compare_note = '';
        if ( '' !== $selected_old_base ) {
            if (
                function_exists( 'developer_starter_is_domain_scan_base_whitelisted' )
                && developer_starter_is_domain_scan_base_whitelisted( $selected_old_base, $whitelist_hosts )
            ) {
                $compare_note = __( '这个域名已在检测白名单中，已跳过手动对比。', 'developer-starter' );
            } elseif ( $selected_old_base === $current_base ) {
                $compare_note = __( '输入的旧域名与当前域名一致，无需对比。', 'developer-starter' );
            } elseif ( function_exists( 'developer_starter_get_theme_option_domain_compare_rows' ) ) {
                $compare_rows = developer_starter_get_theme_option_domain_compare_rows( $options, $selected_old_base, $current_base );
                if ( empty( $compare_rows ) ) {
                    $compare_note = __( '未检测到这个旧域名在主题设置中的残留差异。', 'developer-starter' );
                }
            }
        }

        foreach ( $compare_rows as $index => $compare_row ) {
            $display = $this->get_domain_scan_display_path( isset( $compare_row['path'] ) ? $compare_row['path'] : '' );
            $compare_rows[ $index ]['display_path'] = $display['display'];
            $compare_rows[ $index ]['raw_path']     = $display['raw'];
        }

        if ( ! empty( $whitelist_hosts ) ) {
            echo '<div style="padding:10px 12px;border-radius:10px;background:#eff6ff;color:#1d4ed8;margin-bottom:16px;">';
            echo '<strong>' . esc_html__( '检测白名单', 'developer-starter' ) . '</strong>';
            echo '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">';
            foreach ( $whitelist_hosts as $whitelist_host ) {
                echo '<span style="display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#ffffff;color:#1d4ed8;border:1px solid #93c5fd;">' . esc_html( $whitelist_host ) . '</span>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:16px;">';
        echo '<div style="padding:12px;border-radius:10px;background:#f8fafc;"><strong>' . esc_html__( '当前异常数', 'developer-starter' ) . '</strong><div style="margin-top:6px;color:#b91c1c;font-size:18px;font-weight:700;">' . esc_html( (string) count( $issue_rows ) ) . '</div></div>';
        echo '<div style="padding:12px;border-radius:10px;background:#f8fafc;"><strong>' . esc_html__( '域名依赖项', 'developer-starter' ) . '</strong><div style="margin-top:6px;color:#92400e;font-size:18px;font-weight:700;">' . esc_html( (string) $risk_stats['total'] ) . '</div></div>';
        echo '<div style="padding:12px;border-radius:10px;background:#f8fafc;"><strong>' . esc_html__( '站内绝对地址', 'developer-starter' ) . '</strong><div style="margin-top:6px;color:#92400e;font-size:18px;font-weight:700;">' . esc_html( (string) $risk_stats['direct'] ) . '</div></div>';
        echo '<div style="padding:12px;border-radius:10px;background:#f8fafc;"><strong>' . esc_html__( 'HTML/文本内嵌地址', 'developer-starter' ) . '</strong><div style="margin-top:6px;color:#1d4ed8;font-size:18px;font-weight:700;">' . esc_html( (string) $risk_stats['embedded'] ) . '</div></div>';
        echo '</div>';

        if ( ! empty( $issue_rows ) ) {
            echo '<div style="margin-bottom:12px;"><strong>' . esc_html__( '当前异常位置', 'developer-starter' ) . '</strong><div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">';
            foreach ( array_keys( $issue_path_labels ) as $display_path ) {
                echo '<span style="display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;">' . esc_html( $display_path ) . '</span>';
            }
            echo '</div></div>';
            echo '<div style="margin-bottom:10px;font-weight:600;">' . sprintf( esc_html__( '检测到 %d 处当前异常', 'developer-starter' ), count( $issue_rows ) ) . '</div>';
            echo '<div style="border:1px solid #e5e7eb;border-radius:10px;background:#fff;">';
            foreach ( $issue_rows as $index => $row ) {
                $row_style = 0 === $index ? '' : 'border-top:1px solid #e5e7eb;';
                echo '<div style="padding:14px 16px;' . esc_attr( $row_style ) . '">';
                echo '<div style="font-weight:600;color:#111827;margin-bottom:6px;">' . esc_html( $row['display_path'] ) . '</div>';
                echo '<div style="margin-bottom:12px;color:#6b7280;"><code>' . esc_html( $row['raw_path'] ) . '</code></div>';
                echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">';
                echo '<div><div style="font-size:12px;color:#6b7280;margin-bottom:4px;">' . esc_html__( '当前保存值', 'developer-starter' ) . '</div><div style="word-break:break-all;">' . esc_html( $row['current'] ) . '</div></div>';
                echo '<div><div style="font-size:12px;color:#6b7280;margin-bottom:4px;">' . esc_html__( '建议修复值', 'developer-starter' ) . '</div><div style="word-break:break-all;color:#065f46;">' . esc_html( $row['suggested'] ) . '</div></div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div style="padding:10px 12px;border-radius:10px;background:#ecfdf5;color:#065f46;margin-bottom:16px;">' . esc_html__( '未检测到旧域名残留差异。当前主题设置没有明显的域名异常。', 'developer-starter' ) . '</div>';
        }

        if ( ! empty( $risk_rows ) ) {
            echo '<div style="margin:18px 0 12px;font-weight:600;">' . sprintf( esc_html__( '域名依赖项（%d 项）', 'developer-starter' ), count( $risk_rows ) ) . '</div>';
            echo '<div style="margin-bottom:12px;"><strong>' . esc_html__( '依赖位置', 'developer-starter' ) . '</strong><div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">';
            foreach ( array_keys( $risk_path_labels ) as $display_path ) {
                echo '<span style="display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#fff7ed;color:#9a3412;border:1px solid #fdba74;">' . esc_html( $display_path ) . '</span>';
            }
            echo '</div></div>';
            echo '<div style="border:1px solid #e5e7eb;border-radius:10px;background:#fff;">';
            foreach ( $risk_rows as $index => $row ) {
                $row_style = 0 === $index ? '' : 'border-top:1px solid #e5e7eb;';
                echo '<div style="padding:14px 16px;' . esc_attr( $row_style ) . '">';
                echo '<div style="font-weight:600;color:#111827;margin-bottom:6px;">' . esc_html( $row['display_path'] ) . '</div>';
                echo '<div style="margin-bottom:12px;color:#6b7280;"><code>' . esc_html( $row['raw_path'] ) . '</code></div>';
                echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">';
                echo '<div><div style="font-size:12px;color:#6b7280;margin-bottom:4px;">' . esc_html__( '当前保存值', 'developer-starter' ) . '</div><div style="word-break:break-all;">' . esc_html( $row['current'] ) . '</div></div>';
                echo '<div><div style="font-size:12px;color:#6b7280;margin-bottom:4px;">' . esc_html__( '如果修复将变为', 'developer-starter' ) . '</div><div style="word-break:break-all;color:#065f46;">' . esc_html( $row['suggested'] ) . '</div></div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div style="padding:10px 12px;border-radius:10px;background:#f8fafc;color:#6b7280;">' . esc_html__( '未检测到明显的域名依赖项。', 'developer-starter' ) . '</div>';
        }

        if ( ! empty( $auto_old_bases ) ) {
            echo '<div style="margin:14px 0 8px;font-weight:600;">' . esc_html__( '快速对比候选旧域名', 'developer-starter' ) . '</div>';
            echo '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">';
            foreach ( $auto_old_bases as $candidate ) {
                $candidate_url = method_exists( $this, 'get_advanced_settings_url' )
                    ? $this->get_advanced_settings_url(
                        array(
                            'ds_run_domain_scan'    => '1',
                            'ds_compare_old_domain' => $candidate,
                        )
                    )
                    : add_query_arg(
                        array(
                            'page'                  => 'developer-starter-settings',
                            'tab'                   => 'advanced',
                            'ds_run_domain_scan'    => '1',
                            'ds_compare_old_domain' => $candidate,
                        ),
                        admin_url( 'admin.php' )
                    );
                echo '<a class="button button-small" href="' . esc_url( $candidate_url ) . '">' . esc_html( $candidate ) . '</a>';
            }
            echo '</div>';
        }

        if ( '' !== $compare_note ) {
            echo '<div style="padding:10px 12px;border-radius:10px;background:#eff6ff;color:#1d4ed8;margin-bottom:12px;">' . esc_html( $compare_note ) . '</div>';
        }

        if ( ! empty( $compare_rows ) ) {
            echo '<div style="margin-bottom:10px;font-weight:600;">' . sprintf( esc_html__( '检测到 %d 处旧域名差异', 'developer-starter' ), count( $compare_rows ) ) . '</div>';
            echo '<div style="border:1px solid #e5e7eb;border-radius:10px;background:#fff;">';
            foreach ( $compare_rows as $index => $row ) {
                $row_style = 0 === $index ? '' : 'border-top:1px solid #e5e7eb;';
                echo '<div style="padding:14px 16px;' . esc_attr( $row_style ) . '">';
                echo '<div style="font-weight:600;color:#111827;margin-bottom:6px;">' . esc_html( $row['display_path'] ) . '</div>';
                echo '<div style="margin-bottom:12px;color:#6b7280;"><code>' . esc_html( $row['raw_path'] ) . '</code></div>';
                echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">';
                echo '<div><div style="font-size:12px;color:#6b7280;margin-bottom:4px;">' . esc_html__( '当前保存值', 'developer-starter' ) . '</div><div style="word-break:break-all;">' . esc_html( $row['current'] ) . '</div></div>';
                echo '<div><div style="font-size:12px;color:#6b7280;margin-bottom:4px;">' . esc_html__( '如果修复将变为', 'developer-starter' ) . '</div><div style="word-break:break-all;color:#065f46;">' . esc_html( $row['suggested'] ) . '</div></div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</td></tr>';
    }
}
