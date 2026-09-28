<?php
/**
 * SEO 健康检查设置字段渲染类。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_SEO_Health_Trait {
    /**
     * 渲染首页 SEO 模式与生效状态指引面板。
     *
     * 当全站将某个页面设置为静态首页时，页面定制的 SEO 优先于主题全站设置；
     * 此面板向管理员清晰呈现当前首页模式、生效链路、各要素状态及快速编辑/回退操作。
     *
     * @param array<string,mixed> $options Theme options.
     * @return void
     */
    private function render_home_seo_status_notice_field( $options ) {
        unset( $options );

        $show_on_front = get_option( 'show_on_front', 'posts' );
        $page_on_front = absint( get_option( 'page_on_front', 0 ) );
        $is_static_home = ( 'page' === $show_on_front && $page_on_front > 0 );
        $front_page     = $is_static_home ? get_post( $page_on_front ) : null;
        $has_valid_front_page = ( $front_page instanceof \WP_Post && 'page' === $front_page->post_type );

        echo '<tr class="ds-home-seo-status-row"><th scope="row">' . esc_html__( '首页 SEO 生效状态', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-home-seo-status-card" style="background: #fff; border: 1px solid #dcdcde; border-left: 4px solid ' . ( $has_valid_front_page ? '#2271b1' : '#10b981' ) . '; border-radius: 6px; padding: 16px; max-width: 820px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">';

        if ( $has_valid_front_page ) {
            $page_title = get_the_title( $front_page );
            if ( '' === trim( $page_title ) ) {
                $page_title = sprintf( __( '无标题页面 #%d', 'developer-starter' ), $page_on_front );
            }
            $edit_link = get_edit_post_link( $page_on_front );
            $view_link = get_permalink( $page_on_front );

            $page_seo_title = get_post_meta( $page_on_front, '_developer_starter_seo_title', true );
            $page_seo_desc  = get_post_meta( $page_on_front, '_developer_starter_seo_description', true );
            $page_seo_kw    = get_post_meta( $page_on_front, '_developer_starter_seo_keywords', true );

            $has_custom_title = ! empty( trim( (string) $page_seo_title ) );
            $has_custom_desc  = ! empty( trim( (string) $page_seo_desc ) );
            $has_custom_kw    = ! empty( trim( (string) $page_seo_kw ) );

            echo '<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f1;">';
            echo '<div style="display: flex; align-items: center; gap: 8px;">';
            echo '<span class="dashicons dashicons-admin-home" style="font-size: 20px; width: 20px; height: 20px; color: #2271b1;"></span>';
            echo '<strong style="font-size: 14px; color: #1d2327;">' . esc_html__( '当前首页：静态页面定制模式', 'developer-starter' ) . '</strong>';
            echo '<span style="background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 600;">' . esc_html__( '页面独立定制优先', 'developer-starter' ) . '</span>';
            echo '</div>';
            echo '<div style="font-size: 12px; color: #646970;">' . sprintf( esc_html__( '首页页面 ID: %d', 'developer-starter' ), $page_on_front ) . '</div>';
            echo '</div>';

            echo '<div style="margin-bottom: 12px; font-size: 13px; line-height: 1.6; color: #3c434a;">';
            echo sprintf(
                __( '网站当前已将页面【<strong>%s</strong>】设为站点首页。启灵主题自带的 100+ 页面模版及自定义页面均支持<strong>专属的独立 SEO 设置</strong>，因此前台访问首页时将<strong>优先读取该页面中配置的 SEO 标题、描述与关键词</strong>。', 'developer-starter' ),
                esc_html( $page_title )
            );
            echo '</div>';

            echo '<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 14px;">';
            echo '<div style="font-weight: 600; font-size: 12px; color: #475569; margin-bottom: 8px;">' . esc_html__( '当前首页三大要素生效状态：', 'developer-starter' ) . '</div>';
            echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">';

            // Title status
            echo '<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; font-size: 12px;">';
            echo '<div style="color: #64748b; margin-bottom: 4px; font-weight: 500;">' . esc_html__( 'SEO 标题', 'developer-starter' ) . '</div>';
            if ( $has_custom_title ) {
                echo '<div style="color: #0369a1; font-weight: 600;">' . esc_html__( '页面已单独定制', 'developer-starter' ) . '</div>';
                echo '<div style="color: #64748b; font-size: 11px; margin-top: 2px; word-break: break-all;">' . esc_html( mb_substr( (string) $page_seo_title, 0, 30 ) ) . ( mb_strlen( (string) $page_seo_title ) > 30 ? '...' : '' ) . '</div>';
            } else {
                echo '<div style="color: #166534; font-weight: 600;">' . esc_html__( '生效下方全站默认标题', 'developer-starter' ) . '</div>';
                echo '<div style="color: #94a3b8; font-size: 11px; margin-top: 2px;">' . esc_html__( '（页面字段留空）', 'developer-starter' ) . '</div>';
            }
            echo '</div>';

            // Desc status
            echo '<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; font-size: 12px;">';
            echo '<div style="color: #64748b; margin-bottom: 4px; font-weight: 500;">' . esc_html__( 'SEO 描述', 'developer-starter' ) . '</div>';
            if ( $has_custom_desc ) {
                echo '<div style="color: #0369a1; font-weight: 600;">' . esc_html__( '页面已单独定制', 'developer-starter' ) . '</div>';
                echo '<div style="color: #64748b; font-size: 11px; margin-top: 2px; word-break: break-all;">' . esc_html( mb_substr( (string) $page_seo_desc, 0, 30 ) ) . ( mb_strlen( (string) $page_seo_desc ) > 30 ? '...' : '' ) . '</div>';
            } else {
                echo '<div style="color: #166534; font-weight: 600;">' . esc_html__( '生效下方全站默认描述', 'developer-starter' ) . '</div>';
                echo '<div style="color: #94a3b8; font-size: 11px; margin-top: 2px;">' . esc_html__( '（页面字段留空）', 'developer-starter' ) . '</div>';
            }
            echo '</div>';

            // Keywords status
            echo '<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; font-size: 12px;">';
            echo '<div style="color: #64748b; margin-bottom: 4px; font-weight: 500;">' . esc_html__( 'SEO 关键词', 'developer-starter' ) . '</div>';
            if ( $has_custom_kw ) {
                echo '<div style="color: #0369a1; font-weight: 600;">' . esc_html__( '页面已单独定制', 'developer-starter' ) . '</div>';
                echo '<div style="color: #64748b; font-size: 11px; margin-top: 2px; word-break: break-all;">' . esc_html( mb_substr( (string) $page_seo_kw, 0, 30 ) ) . ( mb_strlen( (string) $page_seo_kw ) > 30 ? '...' : '' ) . '</div>';
            } else {
                echo '<div style="color: #166534; font-weight: 600;">' . esc_html__( '生效下方全站默认关键词', 'developer-starter' ) . '</div>';
                echo '<div style="color: #94a3b8; font-size: 11px; margin-top: 2px;">' . esc_html__( '（页面字段留空）', 'developer-starter' ) . '</div>';
            }
            echo '</div>';

            echo '</div>';
            echo '</div>';

            echo '<div style="margin-bottom: 14px; font-size: 12px; color: #64748b; line-height: 1.6;">';
            echo '<strong>' . esc_html__( '如何选择配置方式？', 'developer-starter' ) . '</strong><br>';
            echo '• <strong>' . esc_html__( '个性化定制首页', 'developer-starter' ) . '</strong>：' . esc_html__( '点击下方按钮前往该页面编辑，在底部【SEO 设置】面板中直接填写您期望的首页标题、描述与关键词。', 'developer-starter' ) . '<br>';
            echo '• <strong>' . esc_html__( '统一采用全站设置', 'developer-starter' ) . '</strong>：' . esc_html__( '若您希望直接使用下方配置的内容，只需前往该页面编辑，将对应 SEO 字段清空留空保存即可，系统将自动无缝回退！', 'developer-starter' );
            echo '</div>';

            echo '<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">';
            if ( ! empty( $edit_link ) ) {
                echo '<a href="' . esc_url( $edit_link ) . '" class="button button-primary" target="_blank">' . esc_html__( '前往编辑首页专属 SEO', 'developer-starter' ) . ' &rarr;</a>';
            }
            if ( ! empty( $view_link ) ) {
                echo '<a href="' . esc_url( $view_link ) . '" class="button button-secondary" target="_blank">' . esc_html__( '预览全站首页', 'developer-starter' ) . '</a>';
            }
            echo '</div>';

        } else {
            // Latest posts mode
            echo '<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">';
            echo '<span class="dashicons dashicons-welcome-write-blog" style="font-size: 20px; width: 20px; height: 20px; color: #10b981;"></span>';
            echo '<strong style="font-size: 14px; color: #1d2327;">' . esc_html__( '当前首页：最新文章模式（全局控制）', 'developer-starter' ) . '</strong>';
            echo '<span style="background: #ecfdf5; color: #047857; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 600;">' . esc_html__( '全局 SEO 直接生效', 'developer-starter' ) . '</span>';
            echo '</div>';
            echo '<p style="margin: 0; font-size: 13px; line-height: 1.6; color: #475569;">' . esc_html__( '当前站点未指定静态首页页面（使用默认最新文章模式），下方配置的【默认标题】、【默认描述】与【默认关键词】将直接作为全站首页的完整 SEO 元标签对外输出。', 'developer-starter' ) . '</p>';
        }

        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * 渲染轻量级 SEO 健康度检查面板。
     *
     * @param array<string,mixed> $options Theme options.
     * @return void
     */
    private function render_seo_health_check_field( $options ) {
        unset( $options );

        if ( ! class_exists( '\Developer_Starter\SEO\SEO_Health_Check' ) ) {
            echo '<tr><th scope="row">' . esc_html__( 'SEO 健康检查', 'developer-starter' ) . '</th><td>';
            echo '<p class="description">' . esc_html__( 'SEO 健康检查服务暂未加载。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $snapshot = \Developer_Starter\SEO\SEO_Health_Check::get_snapshot();
        $sitemap  = \Developer_Starter\SEO\SEO_Health_Check::get_sitemap_diagnostics();
        $robots   = \Developer_Starter\SEO\SEO_Health_Check::get_robots_preview();
        $nonce    = wp_create_nonce( 'developer_starter_seo_health' );

        echo '<tr><th scope="row">' . esc_html__( 'SEO 健康检查', 'developer-starter' ) . '</th><td>';
        echo '<div class="ds-seo-health" data-ds-seo-health="1">';
        $this->render_seo_health_assets_once();

        echo '<div class="ds-seo-health__notice">';
        echo esc_html__( '轻量体检只在管理员手动点击时运行，结果作为临时 transient 快照保存；不建表、不写文章 meta、不保存历史日志，可随时清除。', 'developer-starter' );
        echo '</div>';

        echo '<div class="ds-seo-health__actions">';
        echo '<label>' . esc_html__( '扫描上限', 'developer-starter' ) . ' <input type="number" id="ds-seo-health-limit" class="small-text" min="20" max="300" value="200" /></label>';
        echo '<button type="button" class="button button-primary" id="ds-seo-health-scan">' . esc_html__( '开始体检', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button" id="ds-seo-health-clear">' . esc_html__( '清除体检结果', 'developer-starter' ) . '</button>';
        echo '<span class="description" id="ds-seo-health-message"></span>';
        echo '<input type="hidden" id="ds-seo-health-nonce" value="' . esc_attr( $nonce ) . '" />';
        echo '</div>';

        $this->render_seo_health_snapshot_summary( $snapshot );
        $this->render_seo_health_issue_lists( $snapshot );
        $this->render_seo_health_sitemap_panel( $sitemap );
        $this->render_seo_health_robots_panel( $robots );

        echo '</div>';
        echo '</td></tr>';
    }

    /**
     * 渲染紧凑的扫描汇总统计。
     *
     * @param array<string,mixed> $snapshot Snapshot.
     * @return void
     */
    private function render_seo_health_snapshot_summary( $snapshot ) {
        echo '<section class="ds-seo-health__section">';
        echo '<h4>' . esc_html__( '全站概览', 'developer-starter' ) . '</h4>';

        if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
            echo '<p class="description">' . esc_html__( '暂无体检结果。点击“开始体检”后会生成一次临时快照。', 'developer-starter' ) . '</p>';
            echo '</section>';
            return;
        }

        $summary = isset( $snapshot['summary'] ) && is_array( $snapshot['summary'] ) ? $snapshot['summary'] : array();
        $scanned = isset( $summary['scanned'] ) && is_array( $summary['scanned'] ) ? $summary['scanned'] : array();
        $counts  = isset( $summary['issue_counts'] ) && is_array( $summary['issue_counts'] ) ? $summary['issue_counts'] : array();

        echo '<div class="ds-seo-health__cards">';
        $this->render_seo_health_card( __( '扫描内容', 'developer-starter' ), sprintf( __( '%1$d 篇文章 / %2$d 个页面 / %3$d 个分类', 'developer-starter' ), absint( $scanned['posts'] ?? 0 ), absint( $scanned['pages'] ?? 0 ), absint( $scanned['categories'] ?? 0 ) ) );
        $this->render_seo_health_card( __( '严重', 'developer-starter' ), number_format_i18n( absint( $counts['critical'] ?? 0 ) ), 'critical' );
        $this->render_seo_health_card( __( '警告', 'developer-starter' ), number_format_i18n( absint( $counts['warning'] ?? 0 ) ), 'warning' );
        $this->render_seo_health_card( __( '提示', 'developer-starter' ), number_format_i18n( absint( $counts['info'] ?? 0 ) ), 'info' );
        echo '</div>';

        $generated_at = isset( $snapshot['generated_at'] ) ? (string) $snapshot['generated_at'] : '';
        $stored       = isset( $summary['stored_issues'] ) ? absint( $summary['stored_issues'] ) : 0;
        $total        = isset( $summary['total_issues'] ) ? absint( $summary['total_issues'] ) : 0;
        echo '<p class="description">';
        if ( '' !== $generated_at ) {
            echo esc_html( sprintf( __( '最近体检：%s。', 'developer-starter' ), $generated_at ) ) . ' ';
        }
        echo esc_html( sprintf( __( '当前仅保存 %1$d 条问题项用于展示，问题总数 %2$d 条；重新体检会覆盖旧结果。', 'developer-starter' ), $stored, $total ) );
        echo '</p>';
        echo '</section>';
    }

    /**
     * 渲染诊断问题清单。
     *
     * @param array<string,mixed> $snapshot Snapshot.
     * @return void
     */
    private function render_seo_health_issue_lists( $snapshot ) {
        if ( empty( $snapshot['issues'] ) || ! is_array( $snapshot['issues'] ) ) {
            return;
        }

        $groups = array(
            'critical' => __( '严重问题', 'developer-starter' ),
            'warning'  => __( '警告问题', 'developer-starter' ),
            'info'     => __( '提示项', 'developer-starter' ),
        );

        echo '<section class="ds-seo-health__section">';
        echo '<h4>' . esc_html__( '页面问题', 'developer-starter' ) . '</h4>';

        foreach ( $groups as $severity => $label ) {
            $items = isset( $snapshot['issues'][ $severity ] ) && is_array( $snapshot['issues'][ $severity ] ) ? $snapshot['issues'][ $severity ] : array();
            echo '<details class="ds-seo-health__details"' . ( 'critical' === $severity ? ' open' : '' ) . '>';
            echo '<summary>' . esc_html( $label ) . ' <span>' . esc_html( number_format_i18n( count( $items ) ) ) . '</span></summary>';

            if ( empty( $items ) ) {
                echo '<p class="description">' . esc_html__( '暂无记录。', 'developer-starter' ) . '</p>';
            } else {
                echo '<table class="widefat striped ds-seo-health__table"><thead><tr>';
                echo '<th>' . esc_html__( '类型', 'developer-starter' ) . '</th>';
                echo '<th>' . esc_html__( '内容', 'developer-starter' ) . '</th>';
                echo '<th>' . esc_html__( '问题', 'developer-starter' ) . '</th>';
                echo '<th>' . esc_html__( '操作', 'developer-starter' ) . '</th>';
                echo '</tr></thead><tbody>';
                foreach ( $items as $issue ) {
                    $this->render_seo_health_issue_row( is_array( $issue ) ? $issue : array() );
                }
                echo '</tbody></table>';
            }

            echo '</details>';
        }

        echo '</section>';
    }

    /**
     * 渲染 Sitemap 诊断信息。
     *
     * @param array<string,mixed> $sitemap Sitemap diagnostics.
     * @return void
     */
    private function render_seo_health_sitemap_panel( $sitemap ) {
        echo '<section class="ds-seo-health__section">';
        echo '<h4>' . esc_html__( 'Sitemap 状态', 'developer-starter' ) . '</h4>';
        echo '<div class="ds-seo-health__cards">';
        $this->render_seo_health_card( __( '原生 Sitemap', 'developer-starter' ), ! empty( $sitemap['enabled'] ) ? __( '已启用', 'developer-starter' ) : __( '未启用', 'developer-starter' ), ! empty( $sitemap['enabled'] ) ? 'pass' : 'critical' );
        $this->render_seo_health_card( __( 'Provider', 'developer-starter' ), number_format_i18n( absint( $sitemap['provider_count'] ?? 0 ) ), 'info' );
        $this->render_seo_health_card( __( 'noindex 样本', 'developer-starter' ), number_format_i18n( absint( $sitemap['noindex_sample_count'] ?? 0 ) ), absint( $sitemap['noindex_sample_count'] ?? 0 ) > 0 ? 'warning' : 'pass' );
        $this->render_seo_health_card( __( '多语言', 'developer-starter' ), ! empty( $sitemap['multilingual_provider'] ) ? __( '已检测', 'developer-starter' ) : __( '未启用', 'developer-starter' ), ! empty( $sitemap['multilingual_provider'] ) ? 'pass' : 'info' );
        echo '</div>';
        echo '<p><code>' . esc_html( isset( $sitemap['url'] ) ? (string) $sitemap['url'] : home_url( '/wp-sitemap.xml' ) ) . '</code></p>';
        if ( ! empty( $sitemap['providers'] ) && is_array( $sitemap['providers'] ) ) {
            echo '<p class="description">' . esc_html__( '当前 provider：', 'developer-starter' ) . esc_html( implode( ', ', array_map( 'strval', $sitemap['providers'] ) ) ) . '</p>';
        }
        echo '</section>';
    }

    /**
     * 渲染 robots.txt 规则预览。
     *
     * @param array<string,mixed> $robots Robots preview.
     * @return void
     */
    private function render_seo_health_robots_panel( $robots ) {
        echo '<section class="ds-seo-health__section">';
        echo '<h4>' . esc_html__( 'robots.txt 预览', 'developer-starter' ) . '</h4>';
        echo '<div class="ds-seo-health__cards">';
        $this->render_seo_health_card( __( '来源', 'developer-starter' ), 'file' === ( $robots['source'] ?? '' ) ? __( '实体文件', 'developer-starter' ) : __( 'WordPress 虚拟输出', 'developer-starter' ), 'info' );
        $this->render_seo_health_card( __( 'Sitemap 声明', 'developer-starter' ), ! empty( $robots['has_sitemap'] ) ? __( '已包含', 'developer-starter' ) : __( '未包含', 'developer-starter' ), ! empty( $robots['has_sitemap'] ) ? 'pass' : 'warning' );
        $this->render_seo_health_card( __( '全站抓取', 'developer-starter' ), ! empty( $robots['blocks_all_site'] ) ? __( '可能阻止', 'developer-starter' ) : __( '未阻止', 'developer-starter' ), ! empty( $robots['blocks_all_site'] ) ? 'critical' : 'pass' );
        echo '</div>';
        if ( empty( $robots['has_sitemap'] ) && ! empty( $robots['sitemap_url'] ) ) {
            echo '<p class="description">' . esc_html__( '建议 robots.txt 包含 Sitemap 地址：', 'developer-starter' ) . '<code>Sitemap: ' . esc_html( (string) $robots['sitemap_url'] ) . '</code></p>';
        }
        echo '<pre class="ds-seo-health__robots">' . esc_html( isset( $robots['content'] ) ? (string) $robots['content'] : '' ) . '</pre>';
        echo '</section>';
    }

    /**
     * 渲染单张汇总指标卡片。
     *
     * @param string $label Label.
     * @param string $value Value.
     * @param string $tone Tone.
     * @return void
     */
    private function render_seo_health_card( $label, $value, $tone = 'neutral' ) {
        echo '<div class="ds-seo-health__card is-' . esc_attr( sanitize_html_class( $tone ) ) . '">';
        echo '<span>' . esc_html( $label ) . '</span>';
        echo '<strong>' . esc_html( $value ) . '</strong>';
        echo '</div>';
    }

    /**
     * 渲染单行问题诊断数据。
     *
     * @param array<string,mixed> $issue Issue.
     * @return void
     */
    private function render_seo_health_issue_row( $issue ) {
        $edit_url = isset( $issue['edit_url'] ) ? (string) $issue['edit_url'] : '';
        echo '<tr>';
        echo '<td>' . esc_html( isset( $issue['object_label'] ) ? (string) $issue['object_label'] : '' ) . '</td>';
        echo '<td><strong>' . esc_html( isset( $issue['title'] ) ? (string) $issue['title'] : '' ) . '</strong></td>';
        echo '<td>' . esc_html( isset( $issue['message'] ) ? (string) $issue['message'] : '' ) . '</td>';
        echo '<td>' . ( '' !== $edit_url ? '<a class="button button-small" href="' . esc_url( $edit_url ) . '">' . esc_html__( '编辑', 'developer-starter' ) . '</a>' : '-' ) . '</td>';
        echo '</tr>';
    }

    /**
     * 渲染单例 CSS 与 JS 交互脚本。
     *
     * @return void
     */
    private function render_seo_health_assets_once() {
        static $rendered = false;
        if ( $rendered ) {
            return;
        }
        $rendered = true;
        ?>
        <style>
            .ds-seo-health{max-width:980px;display:grid;gap:16px}
            .ds-seo-health__notice{border:1px solid #bfdbfe;background:#eff6ff;color:#1e3a8a;border-radius:8px;padding:10px 12px}
            .ds-seo-health__actions{display:flex;flex-wrap:wrap;align-items:center;gap:10px}
            .ds-seo-health__section{border:1px solid #dcdcde;background:#fff;border-radius:8px;padding:14px}
            .ds-seo-health__section h4{margin:0 0 12px}
            .ds-seo-health__cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:10px}
            .ds-seo-health__card{border:1px solid #dcdcde;border-radius:8px;padding:10px;background:#f6f7f7}
            .ds-seo-health__card span{display:block;color:#646970;font-size:12px}
            .ds-seo-health__card strong{display:block;margin-top:4px;font-size:18px;color:#1d2327}
            .ds-seo-health__card.is-critical{border-color:#fecaca;background:#fef2f2}
            .ds-seo-health__card.is-warning{border-color:#fde68a;background:#fffbeb}
            .ds-seo-health__card.is-pass{border-color:#bbf7d0;background:#f0fdf4}
            .ds-seo-health__card.is-info{border-color:#bfdbfe;background:#eff6ff}
            .ds-seo-health__details{margin-top:10px}
            .ds-seo-health__details summary{cursor:pointer;font-weight:600}
            .ds-seo-health__details summary span{color:#646970;font-weight:400}
            .ds-seo-health__table{margin-top:10px}
            .ds-seo-health__robots{max-height:220px;overflow:auto;background:#1f2937;color:#f8fafc;padding:12px;border-radius:6px;white-space:pre-wrap}
        </style>
        <script>
            (function() {
                function ready(fn) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', fn);
                    } else {
                        fn();
                    }
                }
                ready(function() {
                    var root = document.querySelector('[data-ds-seo-health="1"]');
                    if (!root || typeof ajaxurl === 'undefined') return;
                    var scanBtn = document.getElementById('ds-seo-health-scan');
                    var clearBtn = document.getElementById('ds-seo-health-clear');
                    var nonceEl = document.getElementById('ds-seo-health-nonce');
                    var limitEl = document.getElementById('ds-seo-health-limit');
                    var msg = document.getElementById('ds-seo-health-message');

                    function setBusy(button, busy) {
                        if (button) button.disabled = !!busy;
                    }
                    function request(action, button, busyText) {
                        setBusy(button, true);
                        if (msg) msg.textContent = busyText;
                        var data = new FormData();
                        data.append('action', action);
                        data.append('nonce', nonceEl ? nonceEl.value : '');
                        if (limitEl) data.append('limit', limitEl.value || '200');
                        fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
                            .then(function(res) { return res.json(); })
                            .then(function(res) {
                                if (msg) {
                                    msg.textContent = res && res.data && res.data.message ? res.data.message : (res && res.success ? '操作完成' : '操作失败');
                                }
                                if (res && res.success) {
                                    window.setTimeout(function(){ window.location.reload(); }, 700);
                                }
                            })
                            .catch(function() {
                                if (msg) msg.textContent = '操作失败，请稍后再试';
                            })
                            .finally(function() {
                                setBusy(button, false);
                            });
                    }
                    if (scanBtn) {
                        scanBtn.addEventListener('click', function() {
                            request('developer_starter_seo_health_scan', scanBtn, '正在执行轻量体检...');
                        });
                    }
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function() {
                            request('developer_starter_seo_health_clear', clearBtn, '正在清除临时结果...');
                        });
                    }
                });
            })();
        </script>
        <?php
    }
}
